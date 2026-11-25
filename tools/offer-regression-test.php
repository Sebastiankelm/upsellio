<?php
// Isolated regression harness. No WordPress database, mail or real customer data.
define("ABSPATH", __DIR__ . "/");
$GLOBALS["meta"] = [];
$GLOBALS["post_status"] = "publish";
$GLOBALS["status_hooks"] = 0;
function get_template_directory() { return dirname(__DIR__) . "/wp-content/themes/upsellio"; }
function add_action(...$args) {}
function add_filter(...$args) {}
function do_action($name, ...$args) { if ($name === "upsellio_offer_status_changed") { $GLOBALS["status_hooks"]++; } }
function get_post_type($id) { return $id === 42 ? "crm_offer" : ""; }
function get_post_status($id) { return $GLOBALS["post_status"]; }
function get_post_meta($id, $key, $single = false) { return $GLOBALS["meta"][$key] ?? ""; }
function update_post_meta($id, $key, $value, $prev = null) {
    if ($prev !== null && get_post_meta($id, $key, true) !== $prev) { return false; }
    $GLOBALS["meta"][$key] = $value;
    return true;
}
function add_post_meta($id, $key, $value, $unique = false) {
    if ($unique && array_key_exists($key, $GLOBALS["meta"])) { return false; }
    $GLOBALS["meta"][$key] = $value;
    return true;
}
function get_option($name, $default = false) { return $default; }
function sanitize_key($value) { return preg_replace("/[^a-z0-9_-]/", "", strtolower((string) $value)); }
function sanitize_text_field($value) { return trim(strip_tags((string) $value)); }
function wp_unslash($value) { return $value; }
function current_time($type) { return "2026-11-25 12:00:00"; }
function wp_date($format, $time) { return gmdate($format, $time); }
require get_template_directory() . "/inc/offers.php";
function expect($value, $message) {
    if (!$value) { fwrite(STDERR, "FAIL: " . $message . PHP_EOL); exit(1); }
    echo "PASS: " . $message . PHP_EOL;
}
function event($name, $section = "", $seconds = 0, $session = "session-a", $ts = "2026-11-25 12:00:00") {
    return ["event" => $name, "section_id" => $section, "seconds" => $seconds, "session_id" => $session, "ts" => $ts];
}
$GLOBALS["meta"] = ["_ups_offer_status" => "sent", "_ups_offer_public_slug" => "sample-private-slug", "_ups_offer_timeline" => "7 dni"];
$summary = upsellio_offer_build_analytics_summary(42);
expect($summary["score"] === 0 && !$summary["is_hot"], "empty history has no engagement");
$GLOBALS["meta"]["_ups_offer_events"] = [
    event("offer_view"), event("offer_view"), event("offer_section_view", "pricing"),
    event("offer_section_view", "pricing"), event("offer_engagement_tick", "pricing", 10),
    event("offer_engagement_tick", "pricing", 4), event("offer_cta_click", "pricing_question_modal"),
    event("offer_view", "", 0, "session-b"),
];
$summary = upsellio_offer_build_analytics_summary(42);
expect($summary["views"] === 2 && $summary["identified_views"] === 2, "duplicate views in one session count once");
expect($summary["pricing_seconds"] === 14 && $summary["section_views"]["pricing"] === 1, "correct elapsed tick and unique section view");
expect($summary["accept_clicks"] === 0 && !$summary["is_hot"], "question CTA is not acceptance");
expect(upsellio_offer_detect_stage($summary) !== "decision", "generic activity alone does not imply decision");
$report = upsellio_offer_build_full_analytics(42);
expect($report["session_count"] === 2 && $report["active_seconds"] === 14, "simultaneous sessions stay distinct");
$GLOBALS["meta"]["_ups_offer_events"][] = event("offer_accept_started", "pricing");
$GLOBALS["meta"]["_ups_offer_events"][] = event("offer_accept_step", "step_1");
$GLOBALS["meta"]["_ups_offer_events"][] = event("offer_accept_step", "step_1");
$GLOBALS["meta"]["_ups_offer_events"][] = event("offer_commit_selected", "variant-a");
$summary = upsellio_offer_build_analytics_summary(42);
expect($summary["accept_started_sessions"] === 1 && $summary["accept_steps"]["step_1"] === 1, "acceptance funnel counts distinct sessions");
expect($summary["score"] <= 100 && upsellio_offer_detect_stage($summary) === "decision", "score bounded; acceptance is a decision signal");
upsellio_offer_add_timeline_event(42, "test", "test event");
expect(upsellio_offer_get_commercial_timeline(42) === "7 dni", "event history cannot overwrite commercial timeline");
expect(count(upsellio_offer_get_timeline_events(42)) === 1, "new event history stored separately");
$_POST["offer_token"] = "wrong";
expect(!upsellio_offer_public_request_valid(42), "invalid public token rejected");
$_POST["offer_token"] = "sample-private-slug";
expect(upsellio_offer_public_request_valid(42), "valid published offer token accepted");
$GLOBALS["post_status"] = "draft";
expect(!upsellio_offer_public_request_valid(42), "draft token rejected");
$GLOBALS["post_status"] = "publish";
$GLOBALS["meta"]["_ups_offer_expires_at"] = time() - 60;
expect(!upsellio_offer_public_request_valid(42), "expired token rejected");
unset($GLOBALS["meta"]["_ups_offer_expires_at"]);
expect(upsellio_offer_apply_public_accept(42, ["contact_email" => "test@example.invalid"], ["version" => 1]), "first acceptance succeeds");
expect(!upsellio_offer_apply_public_accept(42), "second acceptance rejected");
expect($GLOBALS["status_hooks"] === 1 && get_post_meta(42, "_ups_offer_status", true) === "won", "status hook fired once");
expect(!upsellio_offer_build_analytics_summary(42)["is_hot"], "closed offer is never HOT");
expect(upsellio_offer_event_section_valid("offer_engagement_tick", "pricing"), "valid pricing measurement");
expect(!upsellio_offer_event_section_valid("offer_engagement_tick", "fake_section"), "unknown section rejected");
expect(!upsellio_offer_event_section_valid("offer_accept_step", "step_9"), "unknown acceptance step rejected");
expect(!upsellio_offer_event_section_valid("offer_commit_selected", "fake", ["real"]), "unknown variant rejected");
expect(upsellio_offer_event_section_valid("offer_commit_selected", "real", ["real"]), "defined variant accepted");
expect(upsellio_offer_event_section_valid("offer_cta_click", "mobile_accept_ajax"), "mobile CTA recognized");
expect(!upsellio_offer_event_section_valid("offer_view", "pricing"), "view event cannot carry arbitrary section");
echo "All isolated regression checks passed." . PHP_EOL;
