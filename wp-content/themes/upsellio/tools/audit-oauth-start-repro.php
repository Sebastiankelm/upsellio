<?php
/**
 * Repro admin-post OAuth start as logged-in user.
 * wp eval-file wp-content/themes/upsellio/tools/audit-oauth-start-repro.php
 */

if (!defined("ABSPATH")) {
    exit(1);
}

register_shutdown_function(static function (): void {
    $e = error_get_last();
    if ($e && in_array((int) ($e["type"] ?? 0), [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        echo "FATAL: " . $e["message"] . " in " . $e["file"] . ":" . $e["line"] . "\n";
    }
});

$user = get_user_by("login", "vxiuqc");
if (!$user instanceof WP_User) {
    $admins = get_users(["role" => "administrator", "number" => 1]);
    $user = $admins[0] ?? null;
}
if (!$user instanceof WP_User) {
    echo "FAIL no admin user\n";
    exit(1);
}

wp_set_current_user((int) $user->ID);
$_GET["op"] = "start";
$_GET["include_ads"] = "1";
$_REQUEST = array_merge($_REQUEST, $_GET);

echo "user=" . $user->user_login . " id=" . $user->ID . "\n";

if (!function_exists("ups_audit_oauth_handle_start")) {
    echo "FAIL missing ups_audit_oauth_handle_start\n";
    exit(1);
}

ob_start();
try {
    ups_audit_oauth_handle_start();
    echo "WARN handle_start returned without exit\n";
} catch (Throwable $t) {
    echo "THROW: " . $t->getMessage() . "\n";
}
$buf = ob_get_clean();
if ($buf !== "") {
    echo "output: " . substr($buf, 0, 200) . "\n";
}
