<?php
define("WP_USE_THEMES", false);
require dirname(__DIR__, 4) . "/wp-load.php";

$page_id = (int) get_option("upsellio_crm_app_page_id", 663);
$user = get_user_by("login", "vxiuqc");
if ($user) {
    wp_set_current_user((int) $user->ID);
}

global $wp_query, $post;
$post = get_post($page_id);
$wp_query = new WP_Query(["page_id" => $page_id]);
$wp_query->is_page = true;
$wp_query->is_singular = true;
$wp_query->queried_object = $post;
$wp_query->queried_object_id = $page_id;

$_GET["view"] = "dashboard";
$_GET["dash_period"] = "30d";

ini_set("display_errors", "1");
error_reporting(E_ALL);
register_shutdown_function(static function () {
    $e = error_get_last();
    if ($e && in_array($e["type"], [E_ERROR, E_PARSE, E_COMPILE_ERROR, E_USER_ERROR], true)) {
        fwrite(STDERR, "FATAL: " . $e["message"] . " in " . $e["file"] . ":" . $e["line"] . "\n");
    }
});
ob_start();
upsellio_crm_app_template_redirect();
$html = ob_get_clean();

$checks = [
    "crm-kpi-row" => strpos($html, "crm-kpi-row") !== false,
    "crm-kpi-val" => strpos($html, "crm-kpi-val") !== false,
    "crm-mid-row" => strpos($html, "crm-mid-row") !== false,
    "layout flex fix" => strpos($html, "display:flex!important") !== false,
    "Podsumowanie dnia" => strpos($html, "Podsumowanie dnia") !== false,
];

foreach ($checks as $k => $v) {
    echo $k . "=" . ($v ? "yes" : "NO") . "\n";
}
echo "html_bytes=" . strlen($html) . "\n";
