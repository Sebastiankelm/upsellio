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
function get_post_type($id) { return in_array((int) $id, [10, 250, 251], true) ? "crm_client" : "crm_offer"; }
function post_type_exists($type) { return $type === "crm_lead"; }
function is_user_logged_in() { return $GLOBALS["logged_in"]; }
function current_user_can($cap, ...$args) {
    if ($cap === "read_post") { return (int) $args[0] !== 251; }
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
$filtered = array_values(array_filter($GLOBALS["records"], static function ($post) { return $post->ID % 2 === 0; }));
$page = upsellio_crm_app_slice_list_page($filtered, 2);
check($page["total"] === 300 && $page["pages"] === 6 && count($page["posts"]) === 50 && $page["posts"][0]->ID === 102, "task pagination preserves filtered order and totals");
$page = upsellio_crm_app_slice_list_page($filtered, 999);
check($page["page"] === 6 && $page["posts"][49]->ID === 600, "task pagination clamps oversized page number");
$page = upsellio_crm_app_slice_list_page([], 3);
check($page["page"] === 1 && $page["pages"] === 0 && $page["posts"] === [], "empty task list handles pagination");
require $root . "nav-config.php";
check(upsellio_crm_nav_is_active("ca-dashboard", "ca-dashboard"), "audit dashboard navigation is active");
check(!upsellio_crm_nav_is_active("ca-clients", "ca-dashboard"), "audit profile is not simultaneously active");
check(!upsellio_crm_nav_is_active("ca-clients", "ca-command-center"), "command center has only its own active navigation");
check(upsellio_crm_nav_is_active("clients", "client-edit"), "customer edit belongs to client navigation");
$nav_views = array_column(upsellio_crm_sidebar_items(), "view");
check(in_array("calendar", $nav_views, true) && in_array("contact-queue", $nav_views, true) && in_array("prospecting", $nav_views, true), "reference navigation includes calendar, contact queue and prospecting");
check(upsellio_crm_app_pipeline_bucket("won", "decision") === "won", "won deal appears only in terminal column");
check(upsellio_crm_app_pipeline_bucket("lost", "awareness") === "lost", "lost deal appears only in terminal column");
check(upsellio_crm_app_pipeline_bucket("sent", "consideration") === "offer_sent", "sent offer has its own column");
check(upsellio_crm_app_pipeline_bucket("open", "decision") === "decision", "open deal follows saved stage");
check(upsellio_crm_app_pipeline_bucket("open", "invalid") === "awareness", "unknown stage falls back to first column");
check(upsellio_crm_app_parse_local_datetime("2026-11-25T11:00") === strtotime("2026-11-25T10:00:00Z"), "calendar local time converts to UTC in winter");
check(upsellio_crm_app_parse_local_datetime("2026-07-25 11:00:00") === strtotime("2026-07-25T09:00:00Z"), "calendar local time converts to UTC in summer");
check(upsellio_crm_app_parse_local_datetime("2026-02-30T11:00") === false, "invalid date is rejected rather than normalized");
check(upsellio_crm_app_parse_local_datetime("2026-03-29T02:30") === false, "nonexistent DST hour is rejected");
check(upsellio_crm_app_parse_local_datetime("tomorrow") === false, "relative dates are rejected");
check(upsellio_crm_app_calendar_week(0)->format("N H:i") === "1 00:00", "calendar begins on Monday at local midnight");
$work_hours = upsellio_crm_app_calendar_work_hours();
check($work_hours === ["start" => 8, "end" => 16], "calendar uses shared business hours");
check(count(range($work_hours["start"], $work_hours["end"] - 1)) === 8, "calendar has eight hourly slots");
$week = new DateTimeImmutable("2026-11-23 00:00:00", wp_timezone());
$outside = upsellio_crm_app_calendar_outside_hours([
    "2026-11-22" => [["id" => 1, "hour" => 7, "time" => "07:00"]],
    "2026-11-23" => [
        ["id" => 2, "hour" => 7, "time" => "07:59"],
        ["id" => 3, "hour" => 8, "time" => "08:00"],
        ["id" => 4, "hour" => 15, "time" => "15:59"],
        ["id" => 5, "hour" => 16, "time" => "16:00"],
    ],
    "2026-11-29" => [["id" => 6, "hour" => 18, "time" => "18:00"]],
    "2026-11-30" => [["id" => 7, "hour" => 18, "time" => "18:00"]],
], $week);
check(array_column($outside, "id") === [2, 5, 6], "outside hours keep before 08 and from 16 in selected week only");
check($outside[0]["date"] === "2026-11-23", "outside hours preserve original date");
check(upsellio_crm_app_calendar_outside_hours([], $week) === [], "empty calendar has no outside-hours appointments");
$GLOBALS["caps"] = ["upsellio_access_crm"];
check(upsellio_crm_app_user_can_view("ca-dashboard"), "CRM staff retain client dashboard access");
check(!upsellio_crm_app_user_can_view("settings") && !upsellio_crm_app_user_can_view("ca-library"), "CRM staff cannot read global settings");
check(!upsellio_crm_app_user_can_view("ca-accounts") && !upsellio_crm_app_user_can_view("ca-meta-accounts"), "global account configuration is administrator only");
check(upsellio_crm_app_can_access_audit_client(10), "authorized client profile allowed");
check(!upsellio_crm_app_can_access_audit_client(250), "client edit permission is required");
check(!upsellio_crm_app_can_access_audit_client(251), "client read permission is required");
check(!upsellio_crm_app_can_access_audit_client(11) && !upsellio_crm_app_can_access_audit_client(0), "non-client and missing record denied");
$GLOBALS["caps"] = ["manage_options"];
check(upsellio_crm_app_user_can_view("settings") && upsellio_crm_app_user_can_view("ca-accounts"), "administrator retains configuration access");
$local_slot = static function (string $value): int { return (new DateTimeImmutable($value, wp_timezone()))->getTimestamp(); };
check(upsellio_crm_app_calendar_slot_fits($local_slot("2026-11-25 08:00"), 480), "full workday fits exactly");
check(upsellio_crm_app_calendar_slot_fits($local_slot("2026-11-25 15:30"), 30), "appointment ending exactly at 16 fits");
check(!upsellio_crm_app_calendar_slot_fits($local_slot("2026-11-25 15:30"), 60), "appointment exceeding 16 rejected");
check(!upsellio_crm_app_calendar_slot_fits($local_slot("2026-11-25 07:59"), 15), "before workday rejected");
check(!upsellio_crm_app_calendar_slot_fits($local_slot("2026-11-25 16:00"), 15), "at workday end rejected");
check(!upsellio_crm_app_calendar_slot_fits($local_slot("2026-11-25 08:00"), 14), "duration below minimum rejected");
check(!upsellio_crm_app_calendar_slot_fits($local_slot("2026-11-25 08:00"), 481), "duration exceeding workday rejected");
check(upsellio_crm_app_calendar_slot_fits($local_slot("2026-07-01 08:00"), 60), "calendar checks summer local time");
// Exercise the actual AJAX handler on read-only and no-op paths.
// No database writes should be needed: the fixture deliberately has no write stubs.
function sanitize_text_field($v) { return trim((string) $v); }
function wp_verify_nonce($nonce, $action) { return $nonce === "fixture" && $action === "ups_crm_app_action"; }
function get_post_meta($id, $key, $single = false) { return $GLOBALS["pipeline_fixture_meta"][$id][$key] ?? ""; }
class PipelineFixtureResponse extends RuntimeException {
    public $success;
    public $payload;
    public $status;
    public function __construct($success, $payload, $status) {
        parent::__construct("AJAX response");
        $this->success = $success;
        $this->payload = $payload;
        $this->status = $status;
    }
}
function wp_send_json_success($data = null, $status = 200) { throw new PipelineFixtureResponse(true, $data, $status); }
function wp_send_json_error($data = null, $status = 200) { throw new PipelineFixtureResponse(false, $data, $status); }
require $root . "actions.php";
$pipeline_request = static function (array $post): PipelineFixtureResponse {
    $_POST = $post + ["nonce" => "fixture", "offer_id" => 11];
    try {
        upsellio_crm_app_ajax_move_offer_pipeline();
    } catch (PipelineFixtureResponse $response) {
        return $response;
    }
    throw new RuntimeException("Expected JSON response");
};
$GLOBALS["caps"] = ["upsellio_access_crm", "edit_others_posts"];
$GLOBALS["pipeline_fixture_meta"] = [11 => ["_ups_offer_status" => "sent", "_ups_offer_stage" => "consideration"]];
$response = $pipeline_request(["stage" => "offer_sent"]);
check($response->success && $response->payload["unchanged"] === true, "same Kanban column is a no-op for sent offer");
$GLOBALS["pipeline_fixture_meta"][11] = ["_ups_offer_status" => "won", "_ups_offer_stage" => "decision"];
$response = $pipeline_request(["stage" => "won"]);
check($response->success && $response->payload["unchanged"] === true, "won offer is not processed twice");
$response = $pipeline_request(["stage" => "invalid"]);
check(!$response->success && $response->status === 400, "invalid pipeline stage is rejected");
$response = $pipeline_request(["stage" => "lost"]);
check(!$response->success && $response->payload["message"] === "loss_reason_required", "lost offer needs an explicit loss reason");
$response = $pipeline_request(["stage" => "decision", "nonce" => "invalid"]);
check(!$response->success && $response->status === 403, "pipeline move requires valid nonce");
$response = $pipeline_request(["stage" => "decision", "offer_id" => 10]);
check(!$response->success && $response->status === 400, "pipeline move rejects a client record");
$_POST = [];
echo "CRM smoke checks complete." . PHP_EOL;
