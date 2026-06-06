<?php
/**
 * Pełna synchronizacja Google (GSC + GA4 + Ads + audyt klientów).
 * Logika w inc/rankmath-bridge.php — ten skrypt tylko uruchamia ją z CLI.
 *
 * Uruchomienie: php wp-content/themes/upsellio/tools/run-google-sync.php
 */

define("WP_USE_THEMES", false);

$root = dirname(__DIR__, 4);
if (!is_file($root . "/wp-load.php")) {
    fwrite(STDERR, "wp-load.php not found\n");
    exit(1);
}

require $root . "/wp-load.php";

if (!function_exists("upsellio_google_unified_sync")) {
    fwrite(STDERR, "upsellio_google_unified_sync missing — załaduj motyw Upsellio.\n");
    exit(1);
}

function ups_google_sync_log(string $message): void
{
    $line = "[" . gmdate("Y-m-d H:i:s") . " UTC] " . $message . PHP_EOL;
    fwrite(STDOUT, $line);
    $upload = wp_upload_dir();
    $dir = is_array($upload) && !empty($upload["basedir"]) ? (string) $upload["basedir"] : "";
    if ($dir !== "") {
        @file_put_contents($dir . "/ups-google-sync.log", $line, FILE_APPEND | LOCK_EX);
    }
}

ups_google_sync_log("=== Start (rankmath-bridge) ===");
$log = upsellio_google_unified_sync(0);
ups_google_sync_log("GSC: " . json_encode($log["gsc"] ?? [], JSON_UNESCAPED_UNICODE));
ups_google_sync_log("GA4: " . json_encode($log["ga4"] ?? [], JSON_UNESCAPED_UNICODE));
if (isset($log["audit"])) {
    ups_google_sync_log("Audit: " . json_encode($log["audit"], JSON_UNESCAPED_UNICODE));
}
ups_google_sync_log("=== Koniec ===");

exit(!empty($log["gsc"]["ok"]) || !empty($log["ga4"]["ok"]) ? 0 : 1);
