<?php
/**
 * Diagnostyka sync Ads wtapes (konto #963, zasób #975).
 * wp eval-file wp-content/themes/upsellio/tools/audit-ads-sync-wtapes.php
 */

if (!defined("ABSPATH")) {
    exit(1);
}

$gacc = 963;
$resource = 975;
$client = 965;

echo "=== OAuth #{$gacc} ===\n";
echo "adwords: " . (ups_audit_account_has_oauth_scope($gacc, "adwords") ? "yes" : "no") . "\n";

$oauth = ups_audit_get_oauth_for_account($gacc);
$backup = upsellio_get_gsc_credentials();
upsellio_save_gsc_credentials(
    (string) ($oauth["client_id"] ?? ""),
    (string) ($oauth["client_secret"] ?? ""),
    (string) ($oauth["refresh_token"] ?? ""),
    (string) ($backup["property"] ?? "")
);

echo "api_ready: " . (upsellio_google_ads_api_ready() ? "yes" : "no") . "\n";
$cfg = upsellio_google_ads_get_settings();
echo "customer_id: " . ($cfg["customer_id"] ?? "") . " login: " . ($cfg["login_customer_id"] ?? "") . "\n";

$camps = upsellio_google_ads_fetch_campaigns("LAST_30_DAYS");
if (is_wp_error($camps)) {
    echo "campaigns ERROR: " . $camps->get_error_message() . "\n";
} else {
    echo "campaigns: " . count((array) $camps) . "\n";
    foreach (array_slice((array) $camps, 0, 5) as $row) {
        if (!is_array($row)) {
            continue;
        }
        echo "  - " . ($row["name"] ?? "?") . " cost=" . ($row["cost"] ?? $row["cost_micros"] ?? "?") . "\n";
    }
}

upsellio_save_gsc_credentials(
    (string) ($backup["client_id"] ?? ""),
    (string) ($backup["client_secret"] ?? ""),
    (string) ($backup["refresh_token"] ?? ""),
    (string) ($backup["property"] ?? "")
);

if (function_exists("ups_audit_fetch_ads_resources")) {
    $list = ups_audit_fetch_ads_resources($gacc);
    echo "fetch_ads_resources: " . (is_wp_error($list) ? $list->get_error_message() : count((array) $list)) . "\n";
}

echo "=== Sync resource #{$resource} ===\n";
ups_audit_sync_ads_resource($resource, 30, false);
$cache = get_post_meta($resource, "_ups_resource_data_cache", true);
$health = ups_audit_resource_health($resource);
echo "health: " . ($health["label"] ?? "?") . "\n";
if (is_array($cache)) {
    echo "error: " . ($cache["error"] ?? "") . "\n";
    echo "summary: " . wp_json_encode($cache["summary"] ?? []) . "\n";
    echo "campaigns_count: " . count((array) ($cache["campaigns"] ?? [])) . "\n";
}
