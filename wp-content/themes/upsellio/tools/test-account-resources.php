<?php
/**
 * Diagnostyka zasobów konta Google (GA4/GSC/Ads).
 * php wp-content/themes/upsellio/tools/test-account-resources.php [account_id]
 */
define("WP_USE_THEMES", false);
$root = dirname(__DIR__, 4);
require $root . "/wp-load.php";

$aid = isset($argv[1]) ? (int) $argv[1] : 0;
if ($aid <= 0) {
    $ids = get_posts(["post_type" => "crm_google_account", "posts_per_page" => 1, "fields" => "ids", "post_status" => "any"]);
    $aid = !empty($ids) ? (int) $ids[0] : 0;
}
if ($aid <= 0) {
    echo "Brak kont crm_google_account\n";
    exit(1);
}

header("Content-Type: text/plain; charset=utf-8");
echo "account_id: {$aid}\n";
echo "title: " . get_the_title($aid) . "\n";
echo "email: " . get_post_meta($aid, "_ups_gacc_email", true) . "\n";
echo "scopes: " . wp_json_encode(get_post_meta($aid, "_ups_gacc_scopes", true)) . "\n\n";

$oauth = function_exists("ups_audit_get_oauth_for_account") ? ups_audit_get_oauth_for_account($aid) : [];
$access = function_exists("upsellio_gsc_get_access_token") ? upsellio_gsc_get_access_token($oauth) : "";
echo "access_token: " . (is_string($access) && $access !== "" ? "OK (" . strlen($access) . " chars)" : "FAIL") . "\n\n";

if (is_string($access) && $access !== "") {
    $ga4_r = wp_remote_get("https://analyticsadmin.googleapis.com/v1beta/accounts", [
        "timeout" => 30,
        "headers" => ["Authorization" => "Bearer " . $access],
    ]);
    $ga4_code = is_wp_error($ga4_r) ? 0 : (int) wp_remote_retrieve_response_code($ga4_r);
    $ga4_body = is_wp_error($ga4_r) ? $ga4_r->get_error_message() : (string) wp_remote_retrieve_body($ga4_r);
    echo "GA4 Admin API HTTP: {$ga4_code}\n";
    echo substr($ga4_body, 0, 800) . "\n\n";

    $gsc_n = count(function_exists("ups_audit_fetch_gsc_resources") ? ups_audit_fetch_gsc_resources($aid) : []);
    $ga4_n = count(function_exists("ups_audit_fetch_ga4_resources") ? ups_audit_fetch_ga4_resources($aid) : []);
    echo "fetch_gsc count: {$gsc_n}\n";
    echo "fetch_ga4 count (accounts tree): {$ga4_n}\n";
}

$gads = function_exists("upsellio_google_ads_get_settings") ? upsellio_google_ads_get_settings() : [];
echo "\nGoogle Ads config:\n";
echo "  developer_token: " . (trim((string) ($gads["developer_token"] ?? "")) !== "" ? "set" : "EMPTY") . "\n";
echo "  login_customer_id: " . (trim((string) ($gads["login_customer_id"] ?? "")) !== "" ? ($gads["login_customer_id"] ?? "") : "EMPTY") . "\n";
echo "  customer_id: " . (trim((string) ($gads["customer_id"] ?? "")) !== "" ? ($gads["customer_id"] ?? "") : "EMPTY") . "\n";

$scopes_meta = get_post_meta($aid, "_ups_gacc_scopes", true);
$has_analytics = is_array($scopes_meta) && in_array("analytics.readonly", $scopes_meta, true);
$has_adwords = is_array($scopes_meta) && in_array("adwords", $scopes_meta, true);
echo "\nScope cache: analytics.readonly=" . ($has_analytics ? "yes" : "no") . " adwords=" . ($has_adwords ? "yes" : "no") . "\n";

$ads_n = count(function_exists("ups_audit_fetch_ads_resources") ? ups_audit_fetch_ads_resources($aid) : []);
echo "fetch_ads count: {$ads_n}\n";
