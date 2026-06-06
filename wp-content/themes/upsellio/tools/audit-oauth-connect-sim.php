<?php
/**
 * Simulate admin-post connect + CRM direct connect (logged in).
 */
$user = get_user_by("login", "vxiuqc");
if (!$user instanceof WP_User) {
    $users = get_users(["role" => "administrator", "number" => 1, "orderby" => "ID"]);
    $user = $users[0] ?? null;
}
if (!$user instanceof WP_User) {
    echo "no user\n";
    exit(1);
}
wp_set_current_user((int) $user->ID);
echo "user=" . $user->user_login . " can_edit=" . (current_user_can("edit_posts") ? "yes" : "no") . "\n";

ob_start();
try {
    ups_audit_oauth_admin_post_connect_google();
    echo "connect: no exit\n";
} catch (Throwable $e) {
    echo "connect THROW: " . $e->getMessage() . " @ " . $e->getFile() . ":" . $e->getLine() . "\n";
}
$out = ob_get_clean();
if ($out !== "") {
    echo "connect out len=" . strlen($out) . " has_fatal=" . (stripos($out, "błąd krytyczny") !== false ? "yes" : "no") . "\n";
    if (strlen($out) < 600) {
        echo $out . "\n";
    }
}

$_GET = [
    "view" => "ca-accounts",
    "ups_audit_connect" => "1",
    "include_ads" => "1",
    "label" => "wtapes-google-ads",
];
$_REQUEST = $_GET;

ob_start();
try {
    ups_audit_oauth_handle_direct_connect_link();
    echo "direct: no exit\n";
} catch (Throwable $e) {
    echo "direct THROW: " . $e->getMessage() . " @ " . $e->getFile() . ":" . $e->getLine() . "\n";
}
$out2 = ob_get_clean();
if ($out2 !== "") {
    echo "direct out len=" . strlen($out2) . " has_fatal=" . (stripos($out2, "błąd krytyczny") !== false ? "yes" : "no") . "\n";
    if (strlen($out2) < 600) {
        echo $out2 . "\n";
    }
}

$url = ups_audit_start_oauth_connect("wtapes-google-ads", true);
echo "start_url google=" . (strpos((string) $url, "accounts.google.com") !== false ? "yes" : "no") . "\n";
echo substr((string) $url, 0, 120) . "\n";
