<?php
/**
 * Render CRM ca-accounts view as admin (catch fatals).
 * wp eval-file wp-content/themes/upsellio/tools/audit-crm-view-repro.php
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

$user = get_user_by("login", "vxiuqc") ?: (get_users(["role" => "administrator", "number" => 1])[0] ?? null);
if (!$user instanceof WP_User) {
    echo "FAIL no user\n";
    exit(1);
}
wp_set_current_user((int) $user->ID);

global $wp_query;
$wp_query->is_page = true;
$wp_query->is_singular = true;
$_GET["view"] = "ca-accounts";
$_REQUEST["view"] = "ca-accounts";

$theme_file = get_template_directory() . "/inc/crm-app/views/client-audit-accounts.php";
if (!is_readable($theme_file)) {
    echo "FAIL missing view file\n";
    exit(1);
}

ob_start();
try {
    include $theme_file;
} catch (Throwable $t) {
    echo "THROW: " . $t->getMessage() . " @ " . $t->getFile() . ":" . $t->getLine() . "\n";
}
$html = ob_get_clean();

if (stripos($html, "krytyczny") !== false) {
    echo "FAIL critical text in output\n";
    exit(1);
}

echo "OK view bytes=" . strlen($html) . "\n";
