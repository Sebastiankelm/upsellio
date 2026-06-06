<?php
/**
 * Symulacja admin-post OAuth jako zalogowany użytkownik.
 * wp eval-file wp-content/themes/upsellio/tools/test-admin-post-oauth.php
 */

$user = get_user_by("login", "vxiuqc");
if (!$user instanceof WP_User) {
    echo "user not found\n";
    exit(1);
}

wp_set_current_user((int) $user->ID);

$_GET["include_ads"] = "1";
$_GET["label"] = "wtapes-google-ads";

try {
    ob_start();
    ups_audit_oauth_admin_post_connect_google();
    echo "no exit (unexpected)\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getFile() . ":" . $e->getLine() . "\n";
}
