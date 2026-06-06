<?php
/**
 * Reproduce OAuth callback fatal (set REQUEST_URI before bootstrap via wp eval-file after load).
 */
$state = "eafdb7ef28b7cba04660c4efe9d4a04d";
$code = "4/0AdkVLPxrfXlNBlocmCWfT8fMPanZjPWf8D_jR-yfz5Wf1JypiJSnRQ6QsAX06OjRBnQe8g";

$_SERVER["REQUEST_URI"] = "/crm-app/?view=ca-accounts&state={$state}&code=" . rawurlencode($code);
$_SERVER["QUERY_STRING"] = "view=ca-accounts&state={$state}&code=" . rawurlencode($code);
$_GET["view"] = "ca-accounts";
$_GET["state"] = $state;
$_GET["code"] = $code;
$_REQUEST = array_merge($_REQUEST, $_GET);

$user = get_user_by("login", "vxiuqc");
if (!$user instanceof WP_User) {
    $users = get_users(["role" => "administrator", "number" => 1]);
    $user = $users[0] ?? null;
}
if (!$user instanceof WP_User) {
    echo "no user\n";
    exit(1);
}
wp_set_current_user((int) $user->ID);

$pending = [
    "state" => $state,
    "conn_type" => "audit",
    "label" => "wtapes-google-ads",
    "gsc_property" => "",
    "ga4_property_id" => "",
    "wp_user_id" => (int) $user->ID,
];
set_transient(upsellio_google_oauth_transient_key((int) $user->ID), $pending, 1800);
if (function_exists("ups_audit_oauth_mirror_pending_by_state")) {
    ups_audit_oauth_mirror_pending_by_state($state, $pending, 1800);
}

echo "parsed_code=" . (function_exists("ups_audit_oauth_request_code") ? ups_audit_oauth_request_code() : "n/a") . "\n";

register_shutdown_function(static function (): void {
    $err = error_get_last();
    if ($err !== null && in_array((int) ($err["type"] ?? 0), [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        echo "SHUTDOWN_FATAL: " . ($err["message"] ?? "") . " in " . ($err["file"] ?? "") . ":" . ($err["line"] ?? 0) . "\n";
    }
});

ob_start();
try {
    ups_audit_oauth_handle_crm_callback();
    echo "handler returned (no exit)\n";
} catch (Throwable $e) {
    echo "THROW: " . $e->getMessage() . " @ " . $e->getFile() . ":" . $e->getLine() . "\n";
}
$out = ob_get_clean();
echo "out_len=" . strlen($out) . " critical=" . (stripos($out, "krytyczny") !== false ? "yes" : "no") . "\n";
if (strlen($out) < 800) {
    echo $out . "\n";
}
