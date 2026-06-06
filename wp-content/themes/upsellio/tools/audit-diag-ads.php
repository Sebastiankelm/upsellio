<?php
if (!defined("ABSPATH")) {
    exit(1);
}

$aid = 963;
$oauth = ups_audit_get_oauth_for_account($aid);
$access = upsellio_gsc_get_access_token([
    "client_id" => (string) ($oauth["client_id"] ?? ""),
    "client_secret" => (string) ($oauth["client_secret"] ?? ""),
    "refresh_token" => (string) ($oauth["refresh_token"] ?? ""),
]);
if (is_string($access) && $access !== "") {
    $scopes = ups_audit_fetch_scopes($access);
    update_post_meta($aid, "_ups_gacc_scopes", $scopes);
    echo "scopes refreshed: " . wp_json_encode($scopes) . "\n";
}
echo "api_version: " . upsellio_google_ads_api_version() . "\n";
echo "ads_api_configured: " . (ups_audit_ads_api_configured() ? "yes" : "no") . "\n";
echo "oauth adwords scope: " . (ups_audit_account_has_oauth_scope($aid, "adwords") ? "yes" : "no") . "\n";
$cfg = upsellio_google_ads_get_settings();
echo "dev_token len: " . strlen($cfg["developer_token"]) . " login_cid: " . $cfg["login_customer_id"] . " customer: " . $cfg["customer_id"] . "\n";
$err = get_post_meta($aid, "_ups_gacc_fetch_errors", true);
echo "fetch_errors: " . wp_json_encode($err) . "\n";

$backup = upsellio_get_gsc_credentials();
upsellio_save_gsc_credentials(
    (string) ($oauth["client_id"] ?? ""),
    (string) ($oauth["client_secret"] ?? ""),
    (string) ($oauth["refresh_token"] ?? ""),
    (string) ($backup["property"] ?? "")
);
$list = upsellio_google_ads_list_accessible_customers("diag");
upsellio_save_gsc_credentials(
    (string) ($backup["client_id"] ?? ""),
    (string) ($backup["client_secret"] ?? ""),
    (string) ($backup["refresh_token"] ?? ""),
    (string) ($backup["property"] ?? "")
);
if (is_wp_error($list)) {
    echo "listAccessibleCustomers ERROR: " . $list->get_error_message() . "\n";
} else {
    echo "listAccessibleCustomers: " . wp_json_encode($list) . "\n";
}

$camps = upsellio_google_ads_fetch_campaigns("LAST_30_DAYS");
if (is_wp_error($camps)) {
    echo "campaigns ERROR: " . $camps->get_error_message() . "\n";
} else {
    echo "campaigns count: " . count((array) $camps) . "\n";
}
