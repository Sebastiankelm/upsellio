<?php
/**
 * Plugin Name: Upsellio Offer Release 2.0
 * Description: One-time, pinned release of seven CRM theme files. Administrator-only deployment and rollback; no remote write endpoint.
 * Version: 2.0.0
 * Requires PHP: 8.1
 */
if (!defined('ABSPATH')) { exit; }

final class Upsellio_Offer_Release_200 {
    private const PATHS = [
        'inc/anthropic-offer-ai.php', 'inc/crm-app/actions.php', 'inc/crm-app/render.php',
        'inc/followups.php', 'inc/offer-landing.php', 'inc/offers.php', 'inc/sales-engine.php',
    ];
    private const RELEASE = '10ad9e5daf5fb0a338b758f5776f8ccb474fa9db';
    private const BASE = 'cfe6ef80746a0f8a51277b45cdebebf49045430f';

    public static function authorize(): void {
        if (!current_user_can('manage_options') || !current_user_can('update_themes') ||
            !wp_is_file_mod_allowed('upsellio_offer_release')) {
            throw new RuntimeException('Brak uprawnień do autoryzowanej aktualizacji motywu.');
        }
        if (is_multisite() || strtolower((string)parse_url(home_url(), PHP_URL_HOST)) !== 'upsellio.pl' ||
            get_template() !== 'upsellio' || PHP_VERSION_ID < 80100) {
            throw new RuntimeException('Wersja przeznaczona wyłącznie dla produkcyjnego upsellio.pl i motywu upsellio (PHP 8.1+).');
        }
    }

    private static function manifest(): array {
        $m = require __DIR__ . '/manifest.php';
        if (($m['release'] ?? '') !== self::RELEASE || ($m['base'] ?? '') !== self::BASE ||
            array_keys($m['files'] ?? []) !== self::PATHS) {
            throw new RuntimeException('Niezgodny manifest paczki.');
        }
        return $m['files'];
    }

    private static function payload(string $name, string $expected): string {
        if (!preg_match('/^[a-f0-9]{64}$/D', $expected) || !preg_match('/^[a-z0-9_-]+\.php$/D', $name)) {
            throw new RuntimeException('Nieprawidłowy identyfikator paczki.');
        }
        $raw = file_get_contents(__DIR__ . '/payload/' . $name);
        $prefix = '<?php exit; __halt_compiler();';
        if (!is_string($raw) || !str_starts_with($raw, $prefix)) { throw new RuntimeException('Nieprawidłowa zawartość paczki.'); }
        $bytes = base64_decode(substr($raw, strlen($prefix)), true);
        if (!is_string($bytes) || !hash_equals($expected, hash('sha256', $bytes))) {
            throw new RuntimeException('Błąd sumy kontrolnej paczki.');
        }
        return $bytes;
    }

    private static function review_existing_offers(): void {
        // Do not silently remove legacy terms that were shown via template defaults.
        $ids = get_posts([
            'post_type' => 'crm_offer', 'post_status' => 'publish', 'fields' => 'ids',
            'numberposts' => -1, 'suppress_filters' => false,
            'meta_query' => [[ 'key'=>'_ups_offer_status', 'value'=>['open','sent'], 'compare'=>'IN' ]],
        ]);
        $required = ['duration','billing','price_note','proof_lines','questions_raw','include_lines','option_lines','has_google','has_meta','has_web'];
        $missing = 0;
        foreach ($ids as $id) {
            $expiry = (int)get_post_meta($id, '_ups_offer_expires_at', true);
            if ($expiry > 0 && $expiry <= time()) { continue; }
            foreach ($required as $field) {
                if (!metadata_exists('post', $id, '_ups_offer_' . $field)) { $missing++; break; }
            }
        }
        if ($missing) {
            throw new RuntimeException('Wdrożenie wstrzymane: ' . $missing . ' aktywnych ofert może korzystać z niezapisanych warunków szablonu. Zweryfikuj je w CRM; nie uzupełniaj automatycznie.');
        }
    }

    private static function stage(string $path, string $data): string {
        $dir = dirname($path);
        $tmp = tempnam($dir, '.ups-release-');
        if (!$tmp || dirname($tmp) !== $dir) { if ($tmp) { @unlink($tmp); } throw new RuntimeException('Brak zapisu w katalogu motywu.'); }
        try {
            if (file_put_contents($tmp, $data, LOCK_EX) !== strlen($data) ||
                !hash_equals(hash('sha256', $data), (string)hash_file('sha256', $tmp)) ||
                !chmod($tmp, fileperms($path) & 0777)) { throw new RuntimeException('Nie udało się przygotować pliku.'); }
            return $tmp;
        } catch (Throwable $e) { @unlink($tmp); throw $e; }
    }

    public static function apply(bool $rollback = false): string {
        self::authorize();
        global $wpdb;
        $lock = 'ups_offer_release_' . md5($wpdb->prefix);
        if ((string)$wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 0)', $lock)) !== '1') {
            throw new RuntimeException('Brak dostępnej blokady wdrożeniowej MariaDB.');
        }
        $maintenance = ABSPATH . '.maintenance';
        $owned_maintenance = false;
        $pending = []; $restore = []; $changed = [];
        try {
            $root = realpath(get_template_directory());
            if (!$root || !is_dir($root)) { throw new RuntimeException('Nie znaleziono motywu.'); }
            $manifest = self::manifest();
            $old = $rollback ? 'new' : 'old';
            $new = $rollback ? 'old' : 'new';
            $current_count = 0;
            foreach ($manifest as $rel => $row) {
                $path = $root . '/' . $rel;
                if (realpath($path) !== $path || is_link($path) || !is_file($path) ||
                    !is_writable($path) || !is_writable(dirname($path))) {
                    throw new RuntimeException('Brak bezpiecznego dostępu do pliku: ' . $rel);
                }
                $current = hash_file('sha256', $path);
                if (hash_equals($row[$new]['sha256'], $current)) { $current_count++; }
                elseif (!hash_equals($row[$old]['sha256'], $current)) {
                    throw new RuntimeException('Niezgodność pliku produkcyjnego z bazą wydania: ' . $rel . '. Nie nadpisano zmian.');
                }
                // Verify both target and original bytes before changing anything.
                $restore[$path] = self::payload($row[$old]['file'], $row[$old]['sha256']);
                $pending[$path] = self::payload($row[$new]['file'], $row[$new]['sha256']);
            }
            if ($current_count === count(self::PATHS)) { return $rollback ? 'already_rolled_back' : 'already_deployed'; }
            if ($current_count !== 0) { throw new RuntimeException('Mieszane wersje plików. Wymagana ręczna kontrola; nic nie zmieniono.'); }
            if (!$rollback) { self::review_existing_offers(); }
            if (file_exists($maintenance)) { throw new RuntimeException('Inna aktualizacja jest w toku.'); }
            $h = @fopen($maintenance, 'x');
            if (!$h) { throw new RuntimeException('Nie można uruchomić krótkiego trybu konserwacji.'); }
            $owned_maintenance = true;
            $maintenance_bytes = '<?php $upgrading = ' . time() . ';';
            if (fwrite($h, $maintenance_bytes) !== strlen($maintenance_bytes)) {
                fclose($h); throw new RuntimeException('Nie zapisano trybu konserwacji.');
            }
            fclose($h);
            $staged = [];
            try {
                foreach ($pending as $path => $bytes) { $staged[$path] = self::stage($path, $bytes); }
                foreach ($staged as $path => $temp) {
                    if (!hash_equals(hash('sha256', $restore[$path]), (string)hash_file('sha256', $path))) { throw new RuntimeException('Plik zmienił się w trakcie wdrożenia.'); }
                    if (!rename($temp, $path)) { throw new RuntimeException('Nie można podmienić pliku: ' . basename($path)); }
                    $changed[] = $path;
                    if (function_exists('opcache_invalidate')) { @opcache_invalidate($path, true); }
                }
                foreach ($pending as $path => $bytes) {
                    if (!hash_equals(hash('sha256', $bytes), (string)hash_file('sha256', $path))) { throw new RuntimeException('Błąd weryfikacji wdrożenia.'); }
                }
            } catch (Throwable $e) {
                $failures = [];
                foreach (array_reverse($changed) as $path) {
                    try {
                        $tmp = self::stage($path, $restore[$path]);
                        if (!rename($tmp, $path) || !hash_equals(hash('sha256', $restore[$path]), (string)hash_file('sha256', $path))) {
                            @unlink($tmp); throw new RuntimeException('Restore failed');
                        }
                        if (function_exists('opcache_invalidate')) { @opcache_invalidate($path, true); }
                    } catch (Throwable $ignored) { $failures[] = basename($path); }
                }
                if ($failures) { throw new RuntimeException('AWARIA ROLLBACKU: ' . implode(', ', $failures) . '. Wymagane przywrócenie na hostingu.'); }
                throw $e;
            } finally {
                foreach ($staged as $temp) { if (is_file($temp)) { @unlink($temp); } }
            }
            return $rollback ? 'rolled_back' : 'deployed';
        } finally {
            if ($owned_maintenance) { @unlink($maintenance); }
            $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock));
        }
    }

    public static function activate(bool $network_wide = false): void {
        try {
            if ($network_wide) { throw new RuntimeException('Nie obsługuje aktywacji sieciowej.'); }
            $state = self::apply(false);
            update_option('upsellio_offer_release_200', ['state'=>$state,'sha'=>self::RELEASE,'at'=>gmdate('c')], false);
        } catch (Throwable $e) { wp_die(esc_html($e->getMessage()), 'Upsellio: wdrożenie wstrzymane', ['response'=>409,'back_link'=>true]); }
    }

    public static function menu(): void {
        add_management_page('Upsellio Offer Release', 'Upsellio Offer Release', 'update_themes', 'upsellio-offer-release', [self::class,'page']);
    }

    public static function page(): void {
        self::authorize();
        $m = self::manifest();
        $root = realpath(get_template_directory());
        echo '<div class="wrap"><h1>Upsellio Offer Release 2.0</h1><p>Release: <code>' . esc_html(self::RELEASE) . '</code></p><ul>';
        foreach ($m as $rel => $row) {
            $p = $root . '/' . $rel;
            $actual = is_file($p) ? hash_file('sha256',$p) : '';
            $state = hash_equals($row['new']['sha256'],$actual) ? 'WDROŻONY' : (hash_equals($row['old']['sha256'],$actual) ? 'POPRZEDNI' : 'NIEZGODNY');
            echo '<li>' . esc_html($rel . ': ' . $state) . '</li>';
        }
        echo '</ul><p>Rollback przywraca wyłącznie poprzednie pliki; nie cofa zapisów CRM ani nie wysyła wiadomości.</p><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        wp_nonce_field('upsellio_offer_release_rollback');
        echo '<input type="hidden" name="action" value="upsellio_offer_release_rollback"><button class="button" type="submit">Przywróć poprzednią wersję</button></form></div>';
    }

    public static function rollback(): void {
        check_admin_referer('upsellio_offer_release_rollback');
        try {
            $state = self::apply(true);
            update_option('upsellio_offer_release_200', ['state'=>$state,'sha'=>self::BASE,'at'=>gmdate('c')], false);
            wp_safe_redirect(admin_url('tools.php?page=upsellio-offer-release')); exit;
        } catch (Throwable $e) { wp_die(esc_html($e->getMessage()), 'Upsellio: rollback wstrzymany', ['response'=>409,'back_link'=>true]); }
    }
}
register_activation_hook(__FILE__, [Upsellio_Offer_Release_200::class,'activate']);
add_action('admin_menu', [Upsellio_Offer_Release_200::class,'menu']);
add_action('admin_post_upsellio_offer_release_rollback', [Upsellio_Offer_Release_200::class,'rollback']);
