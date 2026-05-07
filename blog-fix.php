<?php
/**
 * BLOG BOT FIX + DIAGNOSTYKA
 * Wgraj do: public_html/blog-fix.php
 * Wejdź na: https://upsellio.pl/blog-fix.php (zalogowany do WP)
 * USUŃ po użyciu.
 */
define("ABSPATH_SELF", true);
$wp_load_candidates = [
    __DIR__ . "/wp-load.php",
    dirname(__DIR__) . "/wp-load.php",
];
$wp_loaded = false;
foreach ($wp_load_candidates as $wp_load_path) {
    if (file_exists($wp_load_path)) {
        require_once $wp_load_path;
        $wp_loaded = true;
        break;
    }
}
if (!$wp_loaded) {
    die("Nie znaleziono wp-load.php");
}

if (!current_user_can("manage_options")) {
    die("Zaloguj się do WP Admin najpierw.");
}
header("Content-Type: text/plain; charset=utf-8");

echo "=== BLOG BOT DIAGNOSTYKA I NAPRAWA ===\n";
echo date("Y-m-d H:i:s") . "\n\n";

// 1. Wersja pliku na serwerze
$bb_file = get_template_directory() . "/inc/anthropic-blog-bot.php";
$at_file = get_template_directory() . "/inc/anthropic-ai-tests.php";
echo "1. PLIKI NA SERWERZE:\n";
echo "   anthropic-blog-bot.php: " . (file_exists($bb_file) ? date("Y-m-d H:i:s", filemtime($bb_file)) . " (" . number_format(filesize($bb_file)) . " b)" : "BRAK") . "\n";
echo "   anthropic-ai-tests.php: " . (file_exists($at_file) ? date("Y-m-d H:i:s", filemtime($at_file)) . " (" . number_format(filesize($at_file)) . " b)" : "BRAK") . "\n\n";

// 2. Co jest w logu
$log_file = WP_CONTENT_DIR . "/ups-blog-bot-debug.log";
echo "2. LOG FILE: $log_file\n";
echo "   Istnieje: " . (file_exists($log_file) ? "TAK" : "NIE") . "\n";
if (file_exists($log_file)) {
    $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    echo "   Ostatnie 5 linii:\n";
    foreach (array_slice($lines, -5) as $l) {
        echo "   $l\n";
    }
}
echo "\n";

// 3. Stan opcji
echo "3. OPCJE WORDPRESS:\n";
echo "   ups_blog_bot_enabled:    " . get_option("ups_blog_bot_enabled", "(brak)") . "\n";
echo "   ups_blog_bot_keywords_queue: " . substr((string) get_option("ups_blog_bot_keywords_queue", "(brak)"), 0, 80) . "...\n";
echo "   ups_anthropic_api_key:   " . (get_option("ups_anthropic_api_key", "") ? "ustawiony" : "BRAK!") . "\n";

$lock = get_transient("ups_blog_bot_running");
echo "   Transient lock:          " . ($lock ? "AKTYWNY — BLOKUJĘ" : "brak") . "\n";
echo "   ups_blog_bot_last_error: " . json_encode(get_option("ups_blog_bot_last_error", null), JSON_UNESCAPED_UNICODE) . "\n\n";

// 4. Usuń lock
if ($lock) {
    delete_transient("ups_blog_bot_running");
    echo "4. ✓ LOCK USUNIĘTY\n\n";
} else {
    echo "4. Lock nie istniał\n\n";
}

// 5. Sprawdź czy funkcja istnieje
echo "5. FUNKCJE:\n";
echo "   upsellio_blog_bot_generate_and_save: " . (function_exists("upsellio_blog_bot_generate_and_save") ? "TAK" : "BRAK!") . "\n";
echo "   upsellio_blog_bot_peek_keyword: " . (function_exists("upsellio_blog_bot_peek_keyword") ? "TAK" : "BRAK!") . "\n";
$kw = function_exists("upsellio_blog_bot_peek_keyword") ? upsellio_blog_bot_peek_keyword() : "";
echo "   Następna fraza: " . ($kw ?: "(pusta kolejka!)") . "\n\n";

// 6. PHP environment
echo "6. ŚRODOWISKO PHP:\n";
echo "   max_execution_time: " . ini_get("max_execution_time") . "s\n";
echo "   SAPI: " . php_sapi_name() . "\n";
echo "   fastcgi_finish_request: " . (function_exists("fastcgi_finish_request") ? "TAK" : "NIE") . "\n\n";

// 7. URUCHOM BEZPOŚREDNIO
echo "7. URUCHAMIAM generate_and_save() BEZPOŚREDNIO...\n";
echo "   (jeśli strona wisie przez 60-90 sek, API działa poprawnie)\n\n";
flush();

if (!function_exists("upsellio_blog_bot_generate_and_save")) {
    echo "BŁĄD: funkcja nie istnieje — plik blog-bot nie jest załadowany!\n";
} else {
    $before_id = (int) get_option("ups_blog_bot_last_draft_id", 0);
    $t0 = microtime(true);
    upsellio_blog_bot_generate_and_save();
    $elapsed = round(microtime(true) - $t0, 2);
    $after_id = (int) get_option("ups_blog_bot_last_draft_id", 0);
    $err = get_option("ups_blog_bot_last_error", null);

    echo "   Czas: {$elapsed}s\n";
    echo "   Błąd: " . ($err ? json_encode($err, JSON_UNESCAPED_UNICODE) : "brak") . "\n";
    echo "   Draft przed: $before_id | po: $after_id\n";

    if ($after_id && $after_id !== $before_id) {
        echo "\n   ✓ SUKCES! Draft ID: $after_id\n";
        echo "   Edytuj: " . get_edit_post_link($after_id, "raw") . "\n";
    } elseif ($elapsed < 2) {
        echo "\n   ⚠ Funkcja wróciła za szybko ({$elapsed}s)!\n";
        echo "   Sprawdź błąd powyżej.\n";

        // Extra debug: sprawdź co blokuje
        echo "\n   DODATKOWA DIAGNOSTYKA:\n";
        echo "   ups_blog_bot_enabled: " . get_option("ups_blog_bot_enabled") . " (musi być '1')\n";
        $api_ok = function_exists("upsellio_anthropic_crm_api_key") ? (bool) upsellio_anthropic_crm_api_key() : ((string) get_option("ups_anthropic_api_key", "") !== "");
        echo "   API key: " . ($api_ok ? "OK" : "BRAK") . "\n";
        $lock2 = get_transient("ups_blog_bot_running");
        echo "   Lock po wywołaniu: " . ($lock2 ? "NADAL AKTYWNY" : "brak") . "\n";
    } else {
        echo "\n   Czas {$elapsed}s ale brak nowego draftu — sprawdź błąd.\n";
    }
}

echo "\n=== KONIEC ===\n";
echo "USUŃ TEN PLIK po diagnozie!\n";
