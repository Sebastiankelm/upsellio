<?php
if (!defined("ABSPATH")) {
    exit(1);
}

$admin_id = 1;
$user = get_user_by("id", $admin_id);
if (!$user instanceof WP_User) {
    echo "Admin user #{$admin_id} not found\n";
    exit(1);
}
wp_set_current_user($admin_id);

update_option("upsellio_google_ads_include_scope", "1", false);
$url = function_exists("ups_audit_oauth_start_direct")
    ? ups_audit_oauth_start_direct("wtapes + Google Ads", true)
    : "";
if ($url === "" || strpos($url, "http") !== 0) {
    echo "Could not build OAuth URL: " . $url . "\n";
    exit(1);
}
echo "Reconnect Google (with Ads scope):\n" . $url . "\n";
echo "After consent, run: wp eval-file wp-content/themes/upsellio/tools/audit-config-ads-wtapes.php\n";
