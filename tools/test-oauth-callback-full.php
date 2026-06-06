<?php
/**
 * Full OAuth callback simulation (logged in, audit transient).
 */
$user = get_user_by("login", "vxiuqc");
if (!$user instanceof WP_User) {
    echo "no user\n";
    exit(1);
}
wp_set_current_user((int) $user->ID);

$state = "085fba8eb907eac8f0acea12a562c1a2";
set_transient(
    upsellio_google_oauth_transient_key((int) $user->ID),
    [
        "state" => $state,
        "conn_type" => "audit",
        "label" => "wtapes-google-ads",
        "gsc_property" => "",
        "ga4_property_id" => "",
    ],
    20 * MINUTE_IN_SECONDS
);

$_GET["view"] = "ca-accounts";
$_GET["state"] = $state;
$_GET["code"] = "fake-code-for-test";
$_REQUEST = array_merge($_REQUEST, $_GET);

echo "functions: gsc=" . (function_exists("upsellio_get_gsc_credentials") ? "yes" : "no");
echo " transient_key=" . (function_exists("upsellio_google_oauth_transient_key") ? "yes" : "no") . "\n";

try {
    ups_audit_oauth_handle_crm_callback();
    echo "done without exit\n";
} catch (Throwable $e) {
    echo "THROW: " . $e->getMessage() . "\n" . $e->getFile() . ":" . $e->getLine() . "\n";
}
