<?php
/**
 * Test OAuth callback handler (logged in).
 * wp eval-file wp-content/themes/upsellio/tools/test-oauth-callback.php
 */

$user = get_user_by("login", "vxiuqc");
if (!$user instanceof WP_User) {
    echo "user missing\n";
    exit(1);
}
wp_set_current_user((int) $user->ID);

$state = "caf1b41a2b6c9269ebf424c909e7b2fa";
set_transient(
    upsellio_google_oauth_transient_key((int) $user->ID),
    [
        "state" => $state,
        "conn_type" => "audit",
        "label" => "wtapes-google-ads",
        "include_ads" => "1",
    ],
    15 * MINUTE_IN_SECONDS
);

$_GET["view"] = "ca-accounts";
$_GET["state"] = $state;
$_GET["code"] = "invalid-test-code";
$_GET["iss"] = "https://accounts.google.com";
$_GET["scope"] = "https://www.googleapis.com/auth/adwords";

echo "user_id=" . get_current_user_id() . "\n";

try {
    ups_audit_oauth_handle_crm_callback();
    echo "callback returned (no redirect)\n";
} catch (Throwable $e) {
    echo "FATAL: " . $e->getMessage() . "\n" . $e->getFile() . ":" . $e->getLine() . "\n";
}
