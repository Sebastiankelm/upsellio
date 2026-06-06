<?php

if (!defined("ABSPATH")) {
    exit(1);
}

$property_id = "484708285";
$account_id = 963;
$raw = ups_audit_with_account_oauth($account_id, static function ($oauth) use ($property_id) {
    return ups_audit_ga4_fetch($property_id, $oauth, 30);
});
if (is_wp_error($raw)) {
    echo "WP_Error: " . $raw->get_error_message() . "\n";
    exit;
}
$rows = is_array($raw) && isset($raw["rows"]) ? $raw["rows"] : [];
echo "rows=" . count($rows) . "\n";
if (!empty($rows[0])) {
    echo json_encode($rows[0], JSON_PRETTY_PRINT) . "\n";
}
if (isset($raw["error"])) {
    echo "api error: " . json_encode($raw["error"]) . "\n";
}
