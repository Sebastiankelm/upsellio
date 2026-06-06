<?php
/**
 * Symulacja login_init z redirect_to OAuth.
 * wp eval-file wp-content/themes/upsellio/tools/test-login-init-oauth.php
 */

$_REQUEST["redirect_to"] = "https://upsellio.pl/crm-app/?view=ca-accounts&ups_audit_connect=1&include_ads=1&label=wtapes";
$_GET["redirect_to"] = $_REQUEST["redirect_to"];

$user = get_user_by("login", "vxiuqc");
if ($user instanceof WP_User) {
    wp_set_current_user((int) $user->ID);
    echo "logged_in=yes\n";
}

try {
    ups_audit_oauth_capture_login_intent();
    echo "capture_done_no_exit\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getFile() . ":" . $e->getLine() . "\n";
}
