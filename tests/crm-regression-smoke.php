<?php
// Lightweight PHP smoke tests. No WordPress DB or external API required.
define("ABSPATH", __DIR__);
define("DAY_IN_SECONDS", 86400);
define("HOUR_IN_SECONDS", 3600);

function add_action(...$args) {}
function add_filter(...$args) {}
function sanitize_key($v) { return preg_replace('/[^a-z0-9_-]/', '', strtolower($v)); }
function wp_timezone() { return new DateTimeZone("Europe/Warsaw"); }
function wp_unslash($value) { return $value; }
function post_type_exists($type) { return $type === "crm_lead"; }
function is_user_logged_in() { return $GLOBALS["logged_in"]; }
function current_user_can($cap, ...$args) {
    if ($cap === "edit_post") {
        return (int) $args[0] !== 250;
    }
    return in_array($cap, $GLOBALS["caps"], true);
}
function get_current_user_id() { return 42; }
function get_post_type_object($type) {
    return post_type_exists($type) ? (object) ["cap" => (object) ["edit_others_posts" => "edit_others_posts"]] : null;
}
function get_posts($args) {
    $GLOBALS["batches"][] = $args;
    $offset = ($args["paged"] - 1) * $args["posts_per_page"];
    return array_slice($GLOBALS["records"], $offset, $args["posts_per_page"]);
}
class WP_Query {
    public $posts;
    public $max_num_pages;
    public $found_posts;
    public function __construct($args) {
        $GLOBALS["last_query"] = $args;
        $this->found_posts = count($GLOBALS["records"]);
        $this->max_num_pages = (int) ceil($this->found_posts / $args["posts_per_page"]);
        $this->posts = array_slice($GLOBALS["records"], ($args["paged"] - 1) * $args["posts_per_page"], $args["posts_per_page"]);
    }
}
function check($condition, $description) {
    if (!$condition) {
        fwrite(STDERR, "FAIL: " . $description . PHP_EOL);
        exit(1);
    }
    echo "PASS: " . $description . PHP_EOL;
}

$root = dirname(__DIR__) . "/wp-content/themes/upsellio/inc/crm-app/";
require $root . "core.php";
require $root . "dashboard-data.php";

$GLOBALS["logged_in"] = true;
$GLOBALS["caps"] = ["edit_posts"];
check(!upsellio_crm_app_user_can_access(), "ordinary author is denied CRM access");
$GLOBALS["caps"] = ["upsellio_access_crm", "edit_others_posts"];
check(upsellio_crm_app_user_can_access(), "explicit CRM capability permits access");
$GLOBALS["caps"] = ["manage_options"];
check(upsellio_crm_app_user_can_access(), "administrator retains access");
$GLOBALS["logged_in"] = false;
check(!upsellio_crm_app_user_can_access(), "anonymous visitor is denied access");
$GLOBALS["logged_in"] = true;
$GLOBALS["caps"] = ["upsellio_access_crm", "edit_others_posts"];

$GLOBALS["records"] = array_map(static function ($id) { return (object) ["ID" => $id]; }, range(1, 601));
$GLOBALS["batches"] = [];
$posts = upsellio_crm_app_collect_metric_posts("crm_lead");
check(count($posts) === 600, "metric input includes beyond 500 and excludes forbidden post");
check(count($GLOBALS["batches"]) === 3, "metric collection uses three bounded batches");
check($posts[599]->ID === 601, "last record included");
check(upsellio_crm_app_collect_metric_posts("missing") === [], "missing post type handled");
$_GET["crm_page"] = "999";
$page = upsellio_crm_app_list_page("crm_lead");
check($page["page"] === 13 && $page["pages"] === 13 && count($page["posts"]) === 1, "out-of-range page clamps to last page");
$_GET["crm_page"] = "2";
$page = upsellio_crm_app_list_page("crm_lead");
check(count($page["posts"]) === 50 && $page["posts"][0]->ID === 51, "second page has correct records");
$GLOBALS["caps"] = ["upsellio_access_crm"];
upsellio_crm_app_list_page("crm_lead");
check($GLOBALS["last_query"]["author"] === 42, "users without edit-others capability are scoped to own records");
date_default_timezone_set("Europe/Warsaw");
check(upsellio_crm_app_dashboard_gmt_timestamp("2026-11-25 12:00:00") === strtotime("2026-11-25T12:00:00Z"), "GMT parse is independent of process timezone");
check(upsellio_crm_app_dashboard_gmt_timestamp("0000-00-00 00:00:00") === false, "invalid zero timestamp ignored");
$bounds = upsellio_crm_app_dashboard_period_bounds("30d");
check($bounds["prev_end"] < $bounds["start"] && $bounds["start"] < $bounds["end"], "comparison periods do not overlap");
echo "CRM smoke checks complete." . PHP_EOL;
