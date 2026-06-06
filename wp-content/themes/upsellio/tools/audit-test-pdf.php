<?php
define("WP_USE_THEMES", false);
require dirname(__DIR__, 4) . "/wp-load.php";

$cid = isset($argv[1]) ? (int) $argv[1] : 965;
$result = ups_audit_export_dashboard_pdf($cid, 30);
if (is_wp_error($result)) {
    echo "ERROR: " . $result->get_error_message() . "\n";
    exit(1);
}
echo "OK: " . ($result["url"] ?? "") . "\n";
echo "Size: " . (file_exists($result["path"] ?? "") ? filesize($result["path"]) : 0) . " bytes\n";
