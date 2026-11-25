<?php
/**
 * Plugin Name: Upsellio SEO Fixed Release
 * Description: Administrator activation of four pinned, reviewed SEO theme files. Exact baseline verification, rollback and read-only diagnostics.
 * Version: 1.0.0
 * Requires PHP: 7.4
 */
defined('ABSPATH') || exit;
final class Upsellio_SEO_Fixed_Release {
    private const REVISION = '92884db3e2e374ea5652aad09a021a0fed451612';
    private const BACKUP = 'upsellio_seo_fixed_release_backup_v1';
    private const LOCK = 'upsellio_seo_fixed_release_lock_v1';
    private const FILES = [
        'inc/technical-seo.php' => ['50661271421d2dbe24b3567227bdf14e85d1c6fd', 'b4c641920d86e1a850d236fb44ef577265635867'],
        'inc/seo-indexing-fixes.php' => ['855f5e2d9edf77a5d58939db3bdc67ed0a858bb2', '33aff52f4d38834b5bebbb7d0da3717328e073de'],
        'inc/cities-seed.php' => ['959af554fb377efdc7d70ed441c29ef80a7379a9', '7bca54259d5b8b61b9338d913b6201739a674642'],
        'inc/definitions-seed.php' => ['47868664ffef0260172f8a071204845851dcab1d', '993c3ca6d3c870eebb8d91ff047e0cd3d2d0cc83'],
    ];
    private static function digest(string $bytes): string {
        return sha1('blob ' . strlen($bytes) . "\0" . $bytes);
    }
    private static function root(): string {
        $root = realpath(get_template_directory());
        if (untrailingslashit(home_url()) !== 'https://upsellio.pl' || !$root || basename($root) !== 'upsellio') {
            throw new RuntimeException('Unexpected site or active theme.');
        }
        return $root;
    }
    private static function read(string $path): string {
        $bytes = file_get_contents($path);
        if ($bytes === false) { throw new RuntimeException('Read failed.'); }
        return $bytes;
    }
    private static function baseline(string $bytes, string $expected): bool {
        if (hash_equals($expected, self::digest($bytes))) { return true; }
        $lf = str_replace("\r\n", "\n", $bytes);
        return $bytes === str_replace("\n", "\r\n", $lf) && hash_equals($expected, self::digest($lf));
    }
    private static function target(string $root, string $relative): string {
        $p = $root . '/' . $relative;
        if (!is_file($p) || is_link($p) || realpath($p) !== $p) { throw new RuntimeException('Unsafe target: ' . $relative); }
        return $p;
    }
    private static function atomic(string $target, string $bytes): void {
        $mode = fileperms($target) & 0777;
        $tmp = tempnam(dirname($target), '.ups-seo-');
        if ($tmp === false) { throw new RuntimeException('Temporary file failed.'); }
        try {
            if (file_put_contents($tmp, $bytes, LOCK_EX) !== strlen($bytes) || !chmod($tmp, $mode) ||
                self::read($tmp) !== $bytes || !rename($tmp, $target)) { throw new RuntimeException('Atomic write failed.'); }
            if (function_exists('opcache_invalidate')) { @opcache_invalidate($target, true); }
        } finally { if (is_file($tmp)) { @unlink($tmp); } }
    }
    private static function lint(string $path): void {
        if (!function_exists('exec')) { throw new RuntimeException('PHP lint unavailable; no files changed.'); }
        $candidates = [PHP_BINDIR . '/php', '/usr/bin/php', '/usr/local/bin/php'];
        $binary = '';
        foreach ($candidates as $candidate) { if (is_file($candidate) && is_executable($candidate)) { $binary = $candidate; break; } }
        if ($binary === '') { throw new RuntimeException('PHP CLI unavailable; no files changed.'); }
        $out = []; $status = 1;
        exec(escapeshellarg($binary) . ' -n -l ' . escapeshellarg($path) . ' 2>&1', $out, $status);
        if ($status !== 0) { throw new RuntimeException('PHP syntax check failed.'); }
    }
    public static function activate(): void {
        if (!current_user_can('install_plugins') || (defined('DISALLOW_FILE_MODS') && DISALLOW_FILE_MODS)) {
            wp_die('Administrator file modification permission required.');
        }
        if (!add_option(self::LOCK, time(), '', false)) { wp_die('SEO release locked; inspect before retrying.'); }
        $changed = []; $plan = []; $recovery = false;
        try {
            $root = self::root();
            foreach (self::FILES as $relative => $hashes) {
                $target = self::target($root, $relative);
                $source = __DIR__ . '/payload/' . $relative;
                $new = self::read($source); $old = self::read($target);
                if (!hash_equals($hashes[1], self::digest($new))) { throw new RuntimeException('Release integrity failed: ' . $relative); }
                if (hash_equals($hashes[1], self::digest($old))) { continue; }
                if (!self::baseline($old, $hashes[0])) { throw new RuntimeException('Production drift: ' . $relative . '. No automatic overwrite.'); }
                self::lint($source);
                $plan[$relative] = ['target'=>$target, 'old'=>$old, 'new'=>$new];
            }
            if ($plan) {
                if (get_option(self::BACKUP, false) !== false) { throw new RuntimeException('Existing backup must be reviewed.'); }
                $backup = [];
                foreach ($plan as $relative => $entry) { $backup[$relative] = base64_encode($entry['old']); }
                if (!add_option(self::BACKUP, $backup, '', false) || get_option(self::BACKUP) !== $backup) {
                    throw new RuntimeException('Backup storage failed.');
                }
                foreach ($plan as $relative => $entry) {
                    if (self::read($entry['target']) !== $entry['old']) { throw new RuntimeException('Concurrent modification: ' . $relative); }
                    $changed[$relative] = $entry;
                    self::atomic($entry['target'], $entry['new']);
                    if (self::read($entry['target']) !== $entry['new']) { throw new RuntimeException('Readback failed: ' . $relative); }
                }
                wp_cache_flush();
            }
        } catch (Throwable $e) {
            foreach (array_reverse($changed, true) as $relative => $entry) {
                try {
                    $current = self::read($entry['target']);
                    if ($current === $entry['old']) { continue; }
                    if ($current !== $entry['new']) { throw new RuntimeException('Concurrent edit'); }
                    self::atomic($entry['target'], $entry['old']);
                } catch (Throwable $ignored) { $recovery = true; }
            }
            if (!$recovery) { delete_option(self::LOCK); }
            wp_die(esc_html($e->getMessage() . ($recovery ? ' Recovery required.' : ' No release applied.')));
        }
        delete_option(self::LOCK);
    }
    public static function status(): array {
        $out = ['revision'=>self::REVISION, 'files'=>[], 'complete'=>false];
        try {
            $root = self::root();
            foreach (self::FILES as $relative => $hashes) {
                $out['files'][$relative] = hash_equals($hashes[1], self::digest(self::read(self::target($root, $relative))));
            }
            $out['complete'] = !in_array(false, $out['files'], true);
        } catch (Throwable $e) { $out['error'] = 'Readback failed'; }
        return $out;
    }
    public static function register(): void {
        register_rest_field('page', 'upsellio_seo_release_status', [
            'get_callback' => static function () { return current_user_can('manage_options') ? self::status() : null; },
            'schema' => ['description'=>'Read-only pinned SEO release status', 'type'=>'object', 'context'=>['edit'], 'readonly'=>true],
        ]);
    }
    public static function admin_menu(): void {
        add_management_page('Upsellio SEO Release', 'Upsellio SEO Release', 'manage_options', 'upsellio-seo-fixed-release', [self::class, 'admin_page']);
    }
    public static function admin_page(): void {
        if (!current_user_can('manage_options')) { return; }
        echo '<div class="wrap"><h1>Upsellio SEO Release</h1><pre>' . esc_html(wp_json_encode(self::status(), JSON_PRETTY_PRINT)) . '</pre>';
        if (get_option(self::BACKUP, false) !== false) {
            echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="upsellio_seo_fixed_rollback">';
            wp_nonce_field('upsellio_seo_fixed_rollback');
            submit_button('Przywróć poprzednie pliki SEO');
            echo '</form>';
        }
        echo '</div>';
    }
    public static function rollback(): void {
        if (!current_user_can('manage_options') || !current_user_can('install_plugins') ||
            (defined('DISALLOW_FILE_MODS') && DISALLOW_FILE_MODS)) { wp_die('Forbidden'); }
        check_admin_referer('upsellio_seo_fixed_rollback');
        if (!add_option(self::LOCK, time(), '', false)) { wp_die('Release locked.'); }
        $changed = []; $plan = []; $recovery = false;
        try {
            $root = self::root(); $backup = get_option(self::BACKUP, []);
            if (!is_array($backup) || !$backup) { throw new RuntimeException('No backup.'); }
            foreach ($backup as $relative => $encoded) {
                if (!isset(self::FILES[$relative])) { throw new RuntimeException('Unknown backup path.'); }
                $target = self::target($root, $relative);
                $current = self::read($target); $old = base64_decode($encoded, true);
                if (!is_string($old) || !self::baseline($old, self::FILES[$relative][0]) ||
                    !hash_equals(self::FILES[$relative][1], self::digest($current))) { throw new RuntimeException('Rollback preflight failed: ' . $relative); }
                $plan[$relative] = ['target'=>$target,'old'=>$old,'current'=>$current];
            }
            foreach ($plan as $relative => $entry) {
                if (self::read($entry['target']) !== $entry['current']) { throw new RuntimeException('Concurrent edit'); }
                $changed[$relative] = $entry;
                self::atomic($entry['target'], $entry['old']);
            }
            wp_cache_flush(); delete_option(self::BACKUP);
        } catch (Throwable $e) {
            foreach (array_reverse($changed, true) as $entry) {
                try {
                    $bytes = self::read($entry['target']);
                    if ($bytes === $entry['current']) { continue; }
                    if ($bytes !== $entry['old']) { throw new RuntimeException('Concurrent edit'); }
                    self::atomic($entry['target'], $entry['current']);
                } catch (Throwable $ignored) { $recovery = true; }
            }
            if (!$recovery) { delete_option(self::LOCK); }
            wp_die(esc_html($e->getMessage() . ($recovery ? ' Recovery required.' : ' Rollback not applied.')));
        }
        delete_option(self::LOCK);
        wp_safe_redirect(admin_url('tools.php?page=upsellio-seo-fixed-release')); exit;
    }
}
register_activation_hook(__FILE__, [Upsellio_SEO_Fixed_Release::class, 'activate']);
add_action('rest_api_init', [Upsellio_SEO_Fixed_Release::class, 'register']);
add_action('admin_menu', [Upsellio_SEO_Fixed_Release::class, 'admin_menu']);
add_action('admin_post_upsellio_seo_fixed_rollback', [Upsellio_SEO_Fixed_Release::class, 'rollback']);
