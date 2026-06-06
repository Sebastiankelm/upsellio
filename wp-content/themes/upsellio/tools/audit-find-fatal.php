<?php
if (!defined("ABSPATH")) {
    exit(1);
}

register_shutdown_function(static function (): void {
    $e = error_get_last();
    if ($e && in_array((int) ($e["type"] ?? 0), [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        echo "FATAL: " . $e["message"] . " in " . $e["file"] . ":" . $e["line"] . "\n";
    }
});

$tests = [
    "crm_accounts" => function (): void {
        $_GET["view"] = "ca-accounts";
        $_REQUEST["view"] = "ca-accounts";
        if (function_exists("upsellio_crm_app_template_redirect")) {
            upsellio_crm_app_template_redirect();
        }
    },
    "oauth_init" => function (): void {
        $_GET["view"] = "ca-accounts";
        $_GET["state"] = "eafdb7ef28b7cba04660c4efe9d4a04d";
        $_GET["code"] = "4/0TEST";
        $_SERVER["QUERY_STRING"] = "view=ca-accounts&state=eafdb7ef28b7cba04660c4efe9d4a04d&code=4/0TEST";
        if (function_exists("ups_audit_oauth_handle_crm_callback")) {
            ups_audit_oauth_handle_crm_callback();
        }
    },
];

$user = get_user_by("login", "vxiuqc");
if ($user instanceof WP_User) {
    wp_set_current_user((int) $user->ID);
}

foreach ($tests as $name => $fn) {
    ob_start();
    try {
        $fn();
        echo "[$name] ok no exit\n";
    } catch (Throwable $t) {
        echo "[$name] THROW: " . $t->getMessage() . " @ " . $t->getFile() . ":" . $t->getLine() . "\n";
    }
    $out = ob_get_clean();
    if (stripos($out, "krytyczny") !== false) {
        echo "[$name] has critical text\n";
    }
    if (strlen($out) < 400) {
        echo $out;
    }
}
