<?php
/**
 * Konfiguracja Google Ads (MCC + wtapes) + import/mapowanie/sync.
 * wp eval-file wp-content/themes/upsellio/tools/audit-config-ads-wtapes.php
 */

if (!defined("ABSPATH")) {
    exit(1);
}

$google_account_id = 963;
$client_id = 965;
$ads_customer_id = "5195787252";
$login_customer_id = "1333330388";
$developer_token = trim((string) (getenv("UPS_GOOGLE_ADS_DEV_TOKEN") ?: ""));
if ($developer_token === "" && function_exists("upsellio_google_ads_get_settings")) {
    $developer_token = trim((string) (upsellio_google_ads_get_settings()["developer_token"] ?? ""));
}
if ($developer_token === "") {
    echo "Set UPS_GOOGLE_ADS_DEV_TOKEN or save developer token in Analityka SEO first.\n";
    exit(1);
}

$cfg_key = function_exists("upsellio_google_ads_config_option_key")
    ? upsellio_google_ads_config_option_key()
    : "upsellio_google_ads_config";

$cfg = [
    "developer_token" => $developer_token,
    "login_customer_id" => $login_customer_id,
    "customer_id" => $ads_customer_id,
];
update_option($cfg_key, $cfg, false);
update_option("upsellio_google_ads_include_scope", "1", false);

echo "Ads config saved (MCC {$login_customer_id}, customer {$ads_customer_id})\n";
echo "API configured: " . (function_exists("ups_audit_ads_api_configured") && ups_audit_ads_api_configured() ? "yes" : "no") . "\n";

if (function_exists("ups_audit_fetch_ads_resources")) {
    $list = ups_audit_fetch_ads_resources($google_account_id);
    if (is_wp_error($list)) {
        echo "Fetch ads list ERROR: " . $list->get_error_message() . "\n";
    } else {
        echo "Accessible customers: " . count((array) $list) . "\n";
        foreach ((array) $list as $row) {
            if (!is_array($row)) {
                continue;
            }
            echo "  - " . ($row["id"] ?? $row["external_id"] ?? "?") . " " . ($row["display_name"] ?? "") . "\n";
        }
    }
}

$resource_id = 0;
if (function_exists("ups_audit_find_imported_resource_id")) {
    $resource_id = ups_audit_find_imported_resource_id($google_account_id, "ads", $ads_customer_id);
}
if ($resource_id <= 0) {
    $resource_id = (int) wp_insert_post([
        "post_type" => "crm_audit_resource",
        "post_title" => "wtapes Google Ads",
        "post_status" => "publish",
    ]);
    if ($resource_id > 0) {
        update_post_meta($resource_id, "_ups_resource_type", "ads");
        update_post_meta($resource_id, "_ups_resource_external_id", $ads_customer_id);
        update_post_meta($resource_id, "_ups_resource_display_name", "wtapes Google Ads");
        update_post_meta($resource_id, "_ups_resource_google_account_id", $google_account_id);
        update_post_meta($resource_id, "_ups_resource_imported_at", current_time("mysql"));
    }
}

if ($resource_id <= 0) {
    echo "FAILED to create ads resource\n";
    exit(1);
}

update_post_meta($resource_id, "_ups_resource_client_id", $client_id);
echo "Ads resource #{$resource_id} mapped to client #{$client_id}\n";

if (function_exists("ups_audit_sync_ads_resource")) {
    ups_audit_sync_ads_resource($resource_id, 30, false);
    $cache = get_post_meta($resource_id, "_ups_resource_data_cache", true);
    $err = is_array($cache) ? (string) ($cache["error"] ?? "") : "";
    $sum = is_array($cache) ? ($cache["summary"] ?? []) : [];
    echo "Sync done. Error: " . ($err !== "" ? $err : "(none)") . "\n";
    if (is_array($sum)) {
        echo "Summary: clicks=" . (int) ($sum["clicks"] ?? 0)
            . " cost=" . (float) ($sum["cost"] ?? 0)
            . " impressions=" . (int) ($sum["impressions"] ?? 0) . "\n";
    }
}
