<?php
/**
 * Plugin Name: Upsellio Offer Release 2.0
 * Description: One-time, pinned release of seven CRM theme files. Administrator-only deployment and rollback; no remote write endpoint.
 * Version: 2.0.3
 * Requires PHP: 8.1
 */
if (!defined('ABSPATH')) { exit; }

final class Upsellio_Offer_Release_200 {
    private const PATHS = [
        'inc/anthropic-offer-ai.php', 'inc/crm-app/actions.php', 'inc/crm-app/render.php',
        'inc/followups.php', 'inc/offer-landing.php', 'inc/offers.php', 'inc/sales-engine.php',
    ];
    private const RELEASE = '5e7e74de0a671c73d84825f9aca976740902c3ef';
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

    private static function preserve_existing_offers(): void {
        // Store only identifiers, never invent or write commercial terms to offer metadata.
        global $wpdb;
        $ids = $wpdb->get_col($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status IN (%s,%s) ORDER BY ID",
            'crm_offer', 'publish', 'private'
        ));
        if (!is_array($ids) || !empty($wpdb->last_error)) {
            throw new RuntimeException('Nie można odczytać listy dotychczasowych ofert.');
        }
        $legacy = [];
        foreach ($ids as $id) {
            if ((int)$id <= 0) { throw new RuntimeException('Nieprawidłowy identyfikator oferty.'); }
            $legacy[(int)$id] = true;
        }
        $key = 'upsellio_offer_legacy_payload_ids_v2';
        update_option($key, $legacy, false);
        if (get_option($key, null) !== $legacy) {
            throw new RuntimeException('Nie zapisano listy zgodności starszych ofert. Nie zmieniono motywu.');
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
                if (!$rollback) { self::preserve_existing_offers(); }
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

    // Activation intentionally has no filesystem writes to the theme.
    public static function activate(bool $network_wide = false): void {
        if ($network_wide) { wp_die('Nie obsługuje aktywacji sieciowej.'); }
    }

    public static function menu(): void {
        add_management_page('Upsellio Offer Release', 'Upsellio Offer Release', 'update_themes', 'upsellio-offer-release', [self::class,'page']);
    }

    private static function inspect(bool $include_source = false): array {
        self::authorize();
        $root = realpath(get_template_directory());
        if (!$root || !is_dir($root)) { throw new RuntimeException('Nie znaleziono motywu.'); }
        $result = [];
        foreach (self::manifest() as $rel => $row) {
            $path = $root . '/' . $rel;
            if (realpath($path) !== $path || is_link($path) || !is_file($path) || !is_readable($path)) {
                $result[$rel] = ['state'=>'unavailable'];
                continue;
            }
            $data = file_get_contents($path);
            if (!is_string($data)) { $result[$rel] = ['state'=>'unavailable']; continue; }
            $actual = hash('sha256', $data);
            $state = hash_equals($row['new']['sha256'],$actual) ? 'release' : (hash_equals($row['old']['sha256'],$actual) ? 'base' : 'mismatch');
            $item = ['state'=>$state,'sha256'=>$actual,'bytes'=>strlen($data),'base_sha256'=>$row['old']['sha256'],'release_sha256'=>$row['new']['sha256']];
            if ($state === 'mismatch') {
                $normalize = static function(string $bytes): string { return str_replace("\r\n", "\n", $bytes); };
                $item['matches_base_with_normalized_line_endings'] = $normalize($data) === $normalize(self::payload($row['old']['file'],$row['old']['sha256']));
                if ($include_source) { $item['source_base64'] = base64_encode($data); }
            }
            $result[$rel] = $item;
        }
        return $result;
    }

    public static function page(): void {
        try {
            $files = self::inspect();
            $base = 0; $release = 0;
            echo '<div class="wrap"><h1>Upsellio Offer Release 2.0.3</h1><p>Release: <code>' . esc_html(self::RELEASE) . '</code></p><table class="widefat striped"><thead><tr><th>Plik</th><th>Stan</th><th>SHA-256 produkcji</th></tr></thead><tbody>';
            $labels=['release'=>'WDROŻONY','base'=>'ZGODNY Z BAZĄ','mismatch'=>'NIEZGODNY','unavailable'=>'NIEDOSTĘPNY'];
            foreach ($files as $rel => $item) {
                $state = $item['state'];
                $base += (int)($state === 'base');
                $release += (int)($state === 'release');
                echo '<tr><td>' . esc_html($rel) . '</td><td>' . esc_html($labels[$state]) . '</td><td><code>' . esc_html($item['sha256'] ?? '') . '</code></td></tr>';
            }
            echo '</tbody></table><p>Raport obejmuje wyłącznie siedem plików motywu, nie zawiera bazy CRM, konfiguracji WordPressa ani danych ofert. Kod niezgodnych plików jest pobierany tylko przez uprawnionego administratora. Traktuj raport jako poufny.</p>';
            self::form('diagnostics','Pobierz raport zgodności (JSON)',false);
            if ($base === count(self::PATHS)) { echo '<p>Dotychczasowe opublikowane i prywatne oferty zachowają poprzedni sposób wyświetlania warunków. Nie uzupełniamy żadnych pól CRM. Nowe oferty nie dziedziczą nieuzgodnionych warunków.</p>'; self::form('deploy','Wdróż sprawdzone wydanie',true); }
            elseif ($release === count(self::PATHS)) { self::form('rollback','Przywróć poprzednią wersję',false); }
            else { echo '<p><strong>Wdrożenie zablokowane:</strong> pobierz raport i porównaj niezgodne pliki. Nie nadpisano motywu.</p>'; }
            echo '</div>';
        } catch (Throwable $e) { wp_die(esc_html($e->getMessage()), 'Upsellio: kontrola wstrzymana', ['response'=>409,'back_link'=>true]); }
    }

    private static function form(string $operation, string $label, bool $primary): void {
        echo '<form style="margin-top:12px" method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        wp_nonce_field('upsellio_offer_release_' . $operation);
        echo '<input type="hidden" name="action" value="' . esc_attr('upsellio_offer_release_' . $operation) . '"><button class="button' . ($primary ? ' button-primary' : '') . '" type="submit">' . esc_html($label) . '</button></form>';
    }

    public static function diagnostics(): void {
        check_admin_referer('upsellio_offer_release_diagnostics');
        try {
            $files = self::inspect(true);
            $report = wp_json_encode(['format'=>'upsellio-offer-release-diagnostics-v1','release'=>self::RELEASE,'base'=>self::BASE,'generated_at'=>gmdate('c'),'files'=>$files], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            if (!is_string($report)) { throw new RuntimeException('Nie można przygotować raportu.'); }
            nocache_headers();
            header('Content-Type: application/json; charset=UTF-8');
            header('Content-Disposition: attachment; filename="upsellio-offer-production-diagnostics.json"');
            header('X-Content-Type-Options: nosniff');
            echo $report; exit;
        } catch (Throwable $e) { wp_die(esc_html($e->getMessage()), 'Upsellio: eksport wstrzymany', ['response'=>409,'back_link'=>true]); }
    }

    public static function deploy(): void {
        check_admin_referer('upsellio_offer_release_deploy');
        try {
            $state = self::apply(false);
            update_option('upsellio_offer_release_200', ['state'=>$state,'sha'=>self::RELEASE,'at'=>gmdate('c')], false);
            wp_safe_redirect(admin_url('tools.php?page=upsellio-offer-release')); exit;
        } catch (Throwable $e) { wp_die(esc_html($e->getMessage()), 'Upsellio: wdrożenie wstrzymane', ['response'=>409,'back_link'=>true]); }
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
add_action('admin_post_upsellio_offer_release_diagnostics', [Upsellio_Offer_Release_200::class,'diagnostics']);
add_action('admin_post_upsellio_offer_release_deploy', [Upsellio_Offer_Release_200::class,'deploy']);
