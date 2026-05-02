<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_automation_register_prospect_post_type()
{
    register_post_type("crm_prospect", [
        "labels" => [
            "name" => "Prospecting",
            "singular_name" => "Prospect",
        ],
        "public" => false,
        "show_ui" => true,
        "show_in_menu" => false,
        "supports" => ["title"],
    ]);
}
add_action("init", "upsellio_automation_register_prospect_post_type");

function upsellio_automation_register_lead_contact_service_post_types()
{
    register_post_type("crm_lead", [
        "labels" => [
            "name" => "Leady",
            "singular_name" => "Lead",
        ],
        "public" => false,
        "show_ui" => true,
        "show_in_menu" => false,
        "supports" => ["title"],
    ]);
    register_post_type("crm_contact", [
        "labels" => [
            "name" => "Kontakty B2B",
            "singular_name" => "Kontakt",
        ],
        "public" => false,
        "show_ui" => true,
        "show_in_menu" => false,
        "supports" => ["title"],
    ]);
    register_post_type("crm_service", [
        "labels" => [
            "name" => "Usługi i pakiety",
            "singular_name" => "Usługa",
        ],
        "public" => false,
        "show_ui" => true,
        "show_in_menu" => false,
        "supports" => ["title", "editor"],
    ]);
}
add_action("init", "upsellio_automation_register_lead_contact_service_post_types");

function upsellio_automation_defaults()
{
    $defaults = [
        "ups_automation_sla_consideration_days" => 7,
        "ups_automation_alert_drop_win_rate_pct" => 10,
        "ups_automation_alert_lost_spike_pct" => 20,
        "ups_automation_cold_followup_days" => 3,
        "ups_automation_ab_min_sample" => 20,
        "ups_automation_ab_min_lift_pct" => 5,
        "ups_automation_ga4_sync_enabled" => "1",
        "ups_crm_lifecycle_default" => "new_lead",
        "ups_crm_pipeline_sla_config" => [
            "new_lead" => ["hours" => 24, "action" => "Pierwszy kontakt z leadem", "escalation" => 1],
            "qualification" => ["hours" => 48, "action" => "Decyzja kwalifikacji", "escalation" => 2],
            "offer" => ["hours" => 72, "action" => "Follow-up ofertowy", "escalation" => 2],
            "negotiation" => ["hours" => 168, "action" => "Domknięcie lub eskalacja", "escalation" => 3],
        ],
    ];
    foreach ($defaults as $key => $value) {
        if (get_option($key, null) === null) {
            update_option($key, $value, false);
        }
    }
}
add_action("init", "upsellio_automation_defaults", 6);

/**
 * Definicje SLA deala (Decision CRM): limity per etap decyzyjny.
 *
 * @return array<string, array{hours:int, action:string, escalation:int}>
 */
function upsellio_automation_get_pipeline_sla_definitions()
{
    $cfg = get_option("ups_crm_pipeline_sla_config", []);
    if (!is_array($cfg) || empty($cfg)) {
        return [
            "new_lead" => ["hours" => 24, "action" => "Pierwszy kontakt z leadem", "escalation" => 1],
            "qualification" => ["hours" => 48, "action" => "Decyzja kwalifikacji", "escalation" => 2],
            "offer" => ["hours" => 72, "action" => "Follow-up ofertowy", "escalation" => 2],
            "negotiation" => ["hours" => 168, "action" => "Domknięcie lub eskalacja", "escalation" => 3],
        ];
    }
    return $cfg;
}

function upsellio_automation_bootstrap_offer_pipeline_sla($post_id, $post, $update)
{
    $post_id = (int) $post_id;
    if ($post_id <= 0 || $update || wp_is_post_revision($post_id)) {
        return;
    }
    if (get_post_type($post_id) !== "crm_offer") {
        return;
    }
    if ((int) get_post_meta($post_id, "_ups_offer_pipeline_sla_entered_ts", true) > 0) {
        return;
    }
    update_post_meta($post_id, "_ups_offer_pipeline_sla_stage", "new_lead");
    update_post_meta($post_id, "_ups_offer_pipeline_sla_entered_ts", time());
    update_post_meta($post_id, "_ups_offer_pipeline_sla_breached_map", []);
}
add_action("save_post_crm_offer", "upsellio_automation_bootstrap_offer_pipeline_sla", 5, 3);

/**
 * Po ręcznym przeciągnięciu lejka marketingowego — synchronizuj etap SLA.
 *
 * @param string $marketing_stage awareness|consideration|decision
 */
function upsellio_automation_sync_offer_pipeline_sla_from_marketing_stage($offer_id, $marketing_stage)
{
    $offer_id = (int) $offer_id;
    $marketing_stage = sanitize_key((string) $marketing_stage);
    if ($offer_id <= 0) {
        return;
    }
    $map = [
        "awareness" => "new_lead",
        "consideration" => "offer",
        "decision" => "negotiation",
    ];
    if (!isset($map[$marketing_stage])) {
        return;
    }
    $sla_stage = (string) $map[$marketing_stage];
    update_post_meta($offer_id, "_ups_offer_pipeline_sla_stage", $sla_stage);
    update_post_meta($offer_id, "_ups_offer_pipeline_sla_entered_ts", time());
    update_post_meta($offer_id, "_ups_offer_pipeline_sla_breached_map", []);
    delete_post_meta($offer_id, "_ups_offer_sla_active_alert");
}

function upsellio_automation_task_deadline_pressure_0_100($due_ts)
{
    $due_ts = (int) $due_ts;
    if ($due_ts <= 0) {
        return 28;
    }
    $now = time();
    if ($due_ts <= $now) {
        return 100;
    }
    $days = ($due_ts - $now) / DAY_IN_SECONDS;
    if ($days >= 14) {
        return 22;
    }
    return (int) max(22, min(99, round(100 - ($days / 14) * 78)));
}

function upsellio_automation_refresh_task_priority_meta($task_id)
{
    $task_id = (int) $task_id;
    if ($task_id <= 0 || get_post_type($task_id) !== "lead_task") {
        return;
    }
    $impact = (int) get_post_meta($task_id, "_upsellio_task_impact_score", true);
    $probability = (int) get_post_meta($task_id, "_upsellio_task_close_probability", true);
    if ($impact <= 0) {
        $impact = 50;
    }
    if ($probability <= 0) {
        $probability = 50;
    }
    $impact = max(1, min(100, $impact));
    $probability = max(1, min(100, $probability));
    $due_raw = get_post_meta($task_id, "_upsellio_task_due_at", true);
    $due_ts = 0;
    if (is_numeric($due_raw)) {
        $due_ts = (int) $due_raw;
    } elseif (is_string($due_raw) && $due_raw !== "") {
        $parsed = strtotime($due_raw);
        $due_ts = $parsed !== false ? (int) $parsed : 0;
    }
    update_post_meta($task_id, "_upsellio_task_deadline_ts", $due_ts);
    $pressure = upsellio_automation_task_deadline_pressure_0_100($due_ts);
    $priority = (int) round(($impact * 0.4) + ($probability * 0.4) + ($pressure * 0.2));
    $priority = max(1, min(100, $priority));
    update_post_meta($task_id, "_upsellio_task_priority_score", $priority);
}

function upsellio_automation_process_pipeline_sla_deals()
{
    $defs = upsellio_automation_get_pipeline_sla_definitions();
    $offers = get_posts([
        "post_type" => "crm_offer",
        "post_status" => ["publish", "draft", "pending", "private"],
        "posts_per_page" => 400,
    ]);
    foreach ($offers as $offer) {
        $offer_id = (int) $offer->ID;
        $status = (string) get_post_meta($offer_id, "_ups_offer_status", true);
        if ($status === "won" || $status === "lost") {
            continue;
        }
        $entered = (int) get_post_meta($offer_id, "_ups_offer_pipeline_sla_entered_ts", true);
        if ($entered <= 0) {
            update_post_meta($offer_id, "_ups_offer_pipeline_sla_stage", "new_lead");
            update_post_meta($offer_id, "_ups_offer_pipeline_sla_entered_ts", strtotime((string) $offer->post_date_gmt) ?: time());
            $entered = (int) get_post_meta($offer_id, "_ups_offer_pipeline_sla_entered_ts", true);
        }
        $sla_stage = (string) get_post_meta($offer_id, "_ups_offer_pipeline_sla_stage", true);
        if ($sla_stage === "" || !isset($defs[$sla_stage])) {
            $sla_stage = "new_lead";
            update_post_meta($offer_id, "_ups_offer_pipeline_sla_stage", $sla_stage);
        }
        $marketing_stage = (string) get_post_meta($offer_id, "_ups_offer_stage", true);
        if ($marketing_stage === "") {
            $marketing_stage = "awareness";
        }
        $limit_h = max(1, (int) ($defs[$sla_stage]["hours"] ?? 24));
        $limit_sec = $limit_h * HOUR_IN_SECONDS;
        $elapsed = time() - $entered;
        if ($elapsed < $limit_sec) {
            continue;
        }
        $breached_map = get_post_meta($offer_id, "_ups_offer_pipeline_sla_breached_map", true);
        if (!is_array($breached_map)) {
            $breached_map = [];
        }
        if (!empty($breached_map[$sla_stage])) {
            continue;
        }
        $breached_map[$sla_stage] = current_time("mysql");
        update_post_meta($offer_id, "_ups_offer_pipeline_sla_breached_map", $breached_map);
        update_post_meta($offer_id, "_ups_offer_sla_active_alert", "1");
        update_post_meta($offer_id, "_ups_offer_priority", "high");
        $esc = (int) ($defs[$sla_stage]["escalation"] ?? 1);
        $action_label = (string) ($defs[$sla_stage]["action"] ?? "SLA");
        $task_title = "SLA: " . $action_label . " (E" . $esc . ")";
        $task_note = "Przekroczono limit " . $limit_h . "h w etapie " . $sla_stage . ". Wymagane działanie.";
        upsellio_automation_create_task($offer_id, $task_title, $task_note, [
            "impact_score" => min(100, 60 + ($esc * 10)),
            "close_probability" => 70,
        ]);
        $template_id = upsellio_automation_pick_followup_template($marketing_stage === "decision" ? "decision" : ($marketing_stage === "consideration" ? "consideration" : "awareness"));
        if ($template_id > 0 && function_exists("upsellio_followup_queue_message")) {
            upsellio_followup_queue_message($offer_id, $template_id, $marketing_stage);
        }
        if (function_exists("upsellio_offer_add_timeline_event")) {
            upsellio_offer_add_timeline_event($offer_id, "pipeline_sla_breach", "SLA: przekroczono etap {$sla_stage} (" . $limit_h . "h). " . $task_note);
        }
        $log = get_post_meta($offer_id, "_ups_offer_sla_breach_log", true);
        if (!is_array($log)) {
            $log = [];
        }
        $log[] = [
            "ts" => current_time("mysql"),
            "stage" => $sla_stage,
            "hours_limit" => $limit_h,
            "escalation" => $esc,
        ];
        if (count($log) > 80) {
            $log = array_slice($log, -80);
        }
        update_post_meta($offer_id, "_ups_offer_sla_breach_log", $log);

        if ($sla_stage === "new_lead" && $marketing_stage === "awareness") {
            update_post_meta($offer_id, "_ups_offer_pipeline_sla_stage", "qualification");
            update_post_meta($offer_id, "_ups_offer_pipeline_sla_entered_ts", time());
            $clean = [];
            update_post_meta($offer_id, "_ups_offer_pipeline_sla_breached_map", $clean);
        }
    }
}

function upsellio_automation_cron_schedules($schedules)
{
    $schedules["upsellio_fifteen_minutes"] = [
        "interval" => 900,
        "display" => "Every 15 minutes",
    ];
    return $schedules;
}
add_filter("cron_schedules", "upsellio_automation_cron_schedules");

function upsellio_automation_schedule_crons()
{
    if (!wp_next_scheduled("upsellio_automation_hourly")) {
        wp_schedule_event(time() + 300, "hourly", "upsellio_automation_hourly");
    }
    if (!wp_next_scheduled("upsellio_automation_daily")) {
        wp_schedule_event(time() + 600, "daily", "upsellio_automation_daily");
    }
    if (!wp_next_scheduled("upsellio_prospecting_queue")) {
        wp_schedule_event(time() + 120, "upsellio_fifteen_minutes", "upsellio_prospecting_queue");
    }
    if (!wp_next_scheduled("upsellio_automation_sla_quarter")) {
        wp_schedule_event(time() + 180, "upsellio_fifteen_minutes", "upsellio_automation_sla_quarter");
    }
}
add_action("init", "upsellio_automation_schedule_crons");

function upsellio_automation_register_crm_roles()
{
    $roles = [
        "ups_sales_manager" => "Sales Manager",
        "ups_sales_rep" => "Handlowiec",
        "ups_marketing" => "Marketing",
        "ups_support_manager" => "Support/Account Manager",
        "ups_readonly" => "Readonly",
        "ups_finance_legal" => "Finance/Legal",
    ];
    foreach ($roles as $slug => $label) {
        if (!get_role($slug)) {
            add_role($slug, $label, ["read" => true]);
        }
    }
}
add_action("init", "upsellio_automation_register_crm_roles", 7);

function upsellio_automation_register_rest_routes()
{
    register_rest_route("upsellio/v1", "/ga4-aggregate", [
        "methods" => "POST",
        "callback" => "upsellio_automation_receive_ga4_aggregate",
        "permission_callback" => "__return_true",
    ]);
    register_rest_route("upsellio/v1", "/gsc-keywords", [
        "methods" => "POST",
        "callback" => "upsellio_automation_receive_gsc_keywords",
        "permission_callback" => "__return_true",
    ]);
    register_rest_route("upsellio/v1", "/lead-capture", [
        "methods" => "POST",
        "callback" => "upsellio_automation_capture_lead_from_form",
        "permission_callback" => "__return_true",
    ]);
}
add_action("rest_api_init", "upsellio_automation_register_rest_routes");

function upsellio_automation_receive_ga4_aggregate($request)
{
    $secret = (string) get_option("ups_followup_inbound_secret", "");
    $header_secret = (string) $request->get_header("x-upsellio-secret");
    if ($secret === "" || !hash_equals($secret, $header_secret)) {
        return new WP_REST_Response(["ok" => false, "message" => "forbidden"], 403);
    }
    $payload = $request->get_json_params();
    $rows = isset($payload["rows"]) && is_array($payload["rows"]) ? $payload["rows"] : [];
    $normalized = [];
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $source = sanitize_text_field((string) ($row["source"] ?? ""));
        $campaign = sanitize_text_field((string) ($row["campaign"] ?? ""));
        $key = strtolower(trim($source . "|" . $campaign));
        if ($key === "|") {
            continue;
        }
        $normalized[$key] = [
            "date" => sanitize_text_field((string) ($row["date"] ?? current_time("Y-m-d"))),
            "source" => $source,
            "medium" => sanitize_text_field((string) ($row["medium"] ?? "")),
            "campaign" => $campaign,
            "sessions" => max(0, (int) ($row["sessions"] ?? 0)),
            "engaged_sessions" => max(0, (int) ($row["engaged_sessions"] ?? 0)),
            "conversions" => max(0, (int) ($row["conversions"] ?? 0)),
            "revenue" => max(0, (float) ($row["revenue"] ?? 0)),
        ];
    }
    update_option("ups_automation_ga4_daily_aggregates", array_values($normalized), false);
    update_option("ups_automation_ga4_last_sync", current_time("mysql"), false);
    upsellio_automation_sync_ga4_channel_quality();

    return new WP_REST_Response(["ok" => true, "rows" => count($normalized)], 200);
}

/**
 * Tekst do promptów AI (lead / follow-up) na podstawie UTM i agregatów GA4 w CRM.
 */
function upsellio_automation_format_ga4_channel_for_ai(string $utm_source, string $utm_campaign): string
{
    $utm_source = trim($utm_source);
    $utm_campaign = trim($utm_campaign);
    if ($utm_source === "" && $utm_campaign === "") {
        return "";
    }
    $scores = get_option("ups_automation_channel_quality_scores", []);
    if (!is_array($scores)) {
        $scores = [];
    }
    $ch_key = strtolower(trim($utm_source . "|" . $utm_campaign));
    if (isset($scores[$ch_key]) && is_array($scores[$ch_key])) {
        $sc = $scores[$ch_key];
        $s = (int) ($sc["score"] ?? 0);
        $sess = (int) ($sc["sessions"] ?? 0);
        $conv = (int) ($sc["conversions"] ?? 0);

        return "Kanał: {$utm_source} / kampania: {$utm_campaign} / jakość kanału (GA4): {$s}/100. Sesje: {$sess}, konwersje: {$conv}.";
    }

    return "Kanał: {$utm_source} / kampania: {$utm_campaign} (brak dopasowania do agregatów GA4 w CRM).";
}

function upsellio_automation_receive_gsc_keywords($request)
{
    $secret = (string) get_option("ups_followup_inbound_secret", "");
    $header_secret = (string) $request->get_header("x-upsellio-secret");
    if ($secret === "" || !hash_equals($secret, $header_secret)) {
        return new WP_REST_Response(["ok" => false, "message" => "forbidden"], 403);
    }
    $payload = $request->get_json_params();
    $rows = isset($payload["rows"]) && is_array($payload["rows"]) ? $payload["rows"] : [];
    $normalized = [];
    $date_re = "/^\d{4}-\d{2}-\d{2}$/";
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $keyword = sanitize_text_field((string) ($row["keyword"] ?? ""));
        $url = esc_url_raw((string) ($row["url"] ?? ""));
        $date = sanitize_text_field((string) ($row["date"] ?? ""));
        if ($keyword === "" || $url === "") {
            continue;
        }
        if ($date !== "" && !preg_match($date_re, $date)) {
            continue;
        }
        $normalized[] = [
            "keyword" => $keyword,
            "url" => $url,
            "date" => $date,
            "position" => round((float) ($row["position"] ?? 0), 2),
            "impressions" => max(0, (int) ($row["impressions"] ?? 0)),
            "clicks" => max(0, (int) ($row["clicks"] ?? 0)),
            "ctr" => round((float) ($row["ctr"] ?? 0), 2),
        ];
        if (count($normalized) >= 25000) {
            break;
        }
    }
    update_option("upsellio_keyword_metrics_rows", $normalized, false);
    update_option("upsellio_keyword_metrics_source", "gsc_service_account", false);
    update_option("upsellio_keyword_metrics_last_sync", current_time("mysql"), false);

    return new WP_REST_Response(["ok" => true, "rows" => count($normalized)], 200);
}

function upsellio_automation_capture_lead_from_form($request)
{
    $secret = (string) get_option("ups_followup_inbound_secret", "");
    $header_secret = (string) $request->get_header("x-upsellio-secret");
    if ($secret === "" || !hash_equals($secret, $header_secret)) {
        return new WP_REST_Response(["ok" => false, "message" => "forbidden"], 403);
    }
    if (!post_type_exists("crm_lead")) {
        return new WP_REST_Response(["ok" => false, "message" => "lead_module_missing"], 500);
    }
    $payload = $request->get_json_params();
    $name = sanitize_text_field((string) ($payload["name"] ?? "Lead formularza"));
    $lead_id = (int) wp_insert_post([
        "post_type" => "crm_lead",
        "post_status" => "publish",
        "post_title" => $name,
    ]);
    if ($lead_id <= 0) {
        return new WP_REST_Response(["ok" => false, "message" => "insert_failed"], 500);
    }
    update_post_meta($lead_id, "_ups_lead_email", sanitize_email((string) ($payload["email"] ?? "")));
    update_post_meta($lead_id, "_ups_lead_phone", sanitize_text_field((string) ($payload["phone"] ?? "")));
    update_post_meta($lead_id, "_ups_lead_source", sanitize_text_field((string) ($payload["source"] ?? "form")));
    update_post_meta($lead_id, "_ups_lead_type", sanitize_key((string) ($payload["type"] ?? "inbound")));
    update_post_meta($lead_id, "_ups_lead_qualification_status", "new");
    update_post_meta($lead_id, "_ups_lead_utm_source", sanitize_text_field((string) ($payload["utm_source"] ?? "")));
    update_post_meta($lead_id, "_ups_lead_utm_campaign", sanitize_text_field((string) ($payload["utm_campaign"] ?? "")));
    update_post_meta($lead_id, "_ups_lead_gclid", sanitize_text_field((string) ($payload["gclid"] ?? "")));
    if (function_exists("upsellio_sales_engine_refresh_lead_hybrid_scores")) {
        upsellio_sales_engine_refresh_lead_hybrid_scores($lead_id);
    }
    return new WP_REST_Response(["ok" => true, "lead_id" => $lead_id], 200);
}

function upsellio_automation_create_task($offer_id, $title, $note = "", $args = [])
{
    $offer_id = (int) $offer_id;
    if ($offer_id <= 0 || !post_type_exists("lead_task")) {
        return 0;
    }
    if (!is_array($args)) {
        $args = [];
    }
    $owner_id = (int) get_post_meta($offer_id, "_ups_offer_owner_id", true);
    if ($owner_id <= 0) {
        $owner_id = function_exists("upsellio_crm_get_default_owner_id") ? (int) upsellio_crm_get_default_owner_id() : 1;
    }
    $task_id = wp_insert_post([
        "post_type" => "lead_task",
        "post_status" => "publish",
        "post_author" => $owner_id,
        "post_title" => sanitize_text_field($title),
    ], true);
    if (is_wp_error($task_id) || (int) $task_id <= 0) {
        return 0;
    }
    $tid = (int) $task_id;
    update_post_meta($tid, "_upsellio_task_offer_id", $offer_id);
    update_post_meta($tid, "_upsellio_task_note", sanitize_text_field($note));
    update_post_meta($tid, "_upsellio_task_status", "open");
    $impact = isset($args["impact_score"]) ? (int) $args["impact_score"] : 0;
    $prob = isset($args["close_probability"]) ? (int) $args["close_probability"] : 0;
    if ($impact > 0) {
        update_post_meta($tid, "_upsellio_task_impact_score", max(1, min(100, $impact)));
    }
    if ($prob > 0) {
        update_post_meta($tid, "_upsellio_task_close_probability", max(1, min(100, $prob)));
    }
    if (!empty($args["due_ts"])) {
        update_post_meta($tid, "_upsellio_task_due_at", (int) $args["due_ts"]);
    }
    upsellio_automation_refresh_task_priority_meta($tid);
    return $tid;
}

function upsellio_automation_pick_followup_template($stage)
{
    if (!function_exists("upsellio_followup_find_matching_templates")) {
        return 0;
    }
    $templates = upsellio_followup_find_matching_templates("any", sanitize_key((string) $stage));
    if (!is_array($templates) || empty($templates)) {
        $templates = upsellio_followup_find_matching_templates("any", "any");
    }
    return (!empty($templates)) ? (int) $templates[0] : 0;
}

function upsellio_automation_handle_sla_rules()
{
    upsellio_automation_process_pipeline_sla_deals();
    $offers = get_posts([
        "post_type" => "crm_offer",
        "post_status" => ["publish", "draft", "pending", "private"],
        "posts_per_page" => 300,
    ]);
    $sla_days = max(1, (int) get_option("ups_automation_sla_consideration_days", 7));
    foreach ($offers as $offer) {
        $offer_id = (int) $offer->ID;
        $status = (string) get_post_meta($offer_id, "_ups_offer_status", true);
        $stage = (string) get_post_meta($offer_id, "_ups_offer_stage", true);
        if ($status === "won" || $status === "lost" || $stage !== "consideration") {
            continue;
        }
        $sla_stage = (string) get_post_meta($offer_id, "_ups_offer_pipeline_sla_stage", true);
        if ($sla_stage === "offer") {
            continue;
        }
        $stage_history = get_post_meta($offer_id, "_ups_offer_stage_history", true);
        $entered_ts = false;
        if (is_array($stage_history) && !empty($stage_history)) {
            $last = end($stage_history);
            if (is_array($last) && isset($last["stage"]) && (string) $last["stage"] === "consideration" && !empty($last["ts"])) {
                $entered_ts = strtotime((string) $last["ts"]);
            }
        }
        if ($entered_ts === false) {
            $entered_ts = strtotime((string) $offer->post_modified_gmt);
        }
        if ($entered_ts === false) {
            continue;
        }
        $days_in_stage = (int) floor((time() - $entered_ts) / DAY_IN_SECONDS);
        if ($days_in_stage < $sla_days) {
            continue;
        }
        if ((string) get_post_meta($offer_id, "_ups_offer_sla_escalated", true) === "1") {
            continue;
        }
        update_post_meta($offer_id, "_ups_offer_priority", "high");
        update_post_meta($offer_id, "_ups_offer_sla_escalated", "1");
        upsellio_automation_create_task(
            $offer_id,
            "SLA (legacy): deal w consideration > {$sla_days} dni",
            "Przekroczony dodatkowy SLA dniowy consideration. Wykonaj follow-up domykający.",
            ["impact_score" => 75, "close_probability" => 55]
        );
        $template_id = upsellio_automation_pick_followup_template("consideration");
        if ($template_id > 0 && function_exists("upsellio_followup_queue_message")) {
            upsellio_followup_queue_message($offer_id, $template_id, "consideration");
        }
        if (function_exists("upsellio_offer_add_timeline_event")) {
            upsellio_offer_add_timeline_event($offer_id, "sla_escalation", "SLA legacy: >{$sla_days} dni w consideration. Auto task + priorytet + follow-up.");
        }
    }
}

function upsellio_automation_on_offer_event($offer_id, $event_name, $summary, $stage)
{
    $offer_id = (int) $offer_id;
    $event_name = sanitize_key((string) $event_name);
    if ($offer_id <= 0) {
        return;
    }
    if ($event_name === "offer_view") {
        $variant = (string) get_post_meta($offer_id, "_ups_offer_ab_variant", true);
        if ($variant === "") {
            $variant = wp_rand(0, 1) === 1 ? "B" : "A";
            update_post_meta($offer_id, "_ups_offer_ab_variant", $variant);
        }
        $stats = get_option("ups_ab_offer_stats", []);
        if (!is_array($stats)) {
            $stats = [];
        }
        if (!isset($stats[$variant])) {
            $stats[$variant] = ["views" => 0, "clicks" => 0];
        }
        $stats[$variant]["views"] = (int) ($stats[$variant]["views"] ?? 0) + 1;
        update_option("ups_ab_offer_stats", $stats, false);

        $last_task_ts = (int) get_post_meta($offer_id, "_ups_offer_next_step_offer_view_ts", true);
        if ($last_task_ts <= 0 || (time() - $last_task_ts) > DAY_IN_SECONDS) {
            upsellio_automation_create_task($offer_id, "Next step po offer_view", "Klient ogląda ofertę. Wyślij krótkie podsumowanie + CTA.");
            update_post_meta($offer_id, "_ups_offer_next_step_offer_view_ts", time());
        }
    } elseif ($event_name === "offer_cta_click") {
        $variant = (string) get_post_meta($offer_id, "_ups_offer_ab_variant", true);
        if ($variant === "") {
            $variant = wp_rand(0, 1) === 1 ? "B" : "A";
            update_post_meta($offer_id, "_ups_offer_ab_variant", $variant);
        }
        $stats = get_option("ups_ab_offer_stats", []);
        if (!is_array($stats)) {
            $stats = [];
        }
        if (!isset($stats[$variant])) {
            $stats[$variant] = ["views" => 0, "clicks" => 0];
        }
        $stats[$variant]["clicks"] = (int) ($stats[$variant]["clicks"] ?? 0) + 1;
        update_option("ups_ab_offer_stats", $stats, false);
    }
}
add_action("upsellio_offer_event_tracked", "upsellio_automation_on_offer_event", 20, 4);

function upsellio_automation_on_inbound_classified($offer_id, $classification, $stage)
{
    $offer_id = (int) $offer_id;
    $classification = sanitize_key((string) $classification);
    if ($offer_id <= 0) {
        return;
    }
    if ($classification === "positive") {
        upsellio_automation_create_task($offer_id, "Next step po inbound_positive", "Klient odpowiedział pozytywnie. Zadzwoń i domknij deal.");
        $last_tpl = (int) get_post_meta($offer_id, "_ups_offer_last_followup_template_id", true);
        if ($last_tpl > 0) {
            $stats = get_option("ups_ab_followup_stats", []);
            if (!is_array($stats)) {
                $stats = [];
            }
            if (!isset($stats[$last_tpl]) || !is_array($stats[$last_tpl])) {
                $stats[$last_tpl] = ["sent" => 0, "conversions" => 0];
            }
            $stats[$last_tpl]["conversions"] = (int) ($stats[$last_tpl]["conversions"] ?? 0) + 1;
            update_option("ups_ab_followup_stats", $stats, false);
        }
    }
}
add_action("upsellio_inbound_classified", "upsellio_automation_on_inbound_classified", 30, 3);

function upsellio_automation_track_followup_delivery($offer_id, $template_id, $status)
{
    $template_id = (int) $template_id;
    $status = sanitize_key((string) $status);
    if ($template_id <= 0 || !in_array($status, ["sent", "failed"], true)) {
        return;
    }
    $stats = get_option("ups_ab_followup_stats", []);
    if (!is_array($stats)) {
        $stats = [];
    }
    if (!isset($stats[$template_id]) || !is_array($stats[$template_id])) {
        $stats[$template_id] = ["sent" => 0, "conversions" => 0, "failed" => 0];
    }
    if ($status === "sent") {
        $stats[$template_id]["sent"] = (int) ($stats[$template_id]["sent"] ?? 0) + 1;
    } else {
        $stats[$template_id]["failed"] = (int) ($stats[$template_id]["failed"] ?? 0) + 1;
    }
    update_option("ups_ab_followup_stats", $stats, false);
}
add_action("upsellio_followup_delivery_status", "upsellio_automation_track_followup_delivery", 20, 3);

function upsellio_automation_on_contract_event($contract_id, $event_key, $entry)
{
    $contract_id = (int) $contract_id;
    $event_key = sanitize_key((string) $event_key);
    if ($contract_id <= 0 || $event_key !== "opened") {
        return;
    }
    $offer_id = (int) get_post_meta($contract_id, "_ups_contract_offer_id", true);
    if ($offer_id <= 0) {
        return;
    }
    upsellio_automation_create_task($offer_id, "Next step po contract_opened", "Umowa została otwarta. Wykonaj follow-up finalizujący.");
}
add_action("upsellio_contract_event_logged", "upsellio_automation_on_contract_event", 10, 3);

function upsellio_automation_assign_offer_owner_if_missing($post_id, $post, $update)
{
    $post_id = (int) $post_id;
    if ($post_id <= 0 || get_post_type($post_id) !== "crm_offer") {
        return;
    }
    $owner_id = (int) get_post_meta($post_id, "_ups_offer_owner_id", true);
    if ($owner_id > 0) {
        return;
    }
    $users = get_users(["role__in" => ["administrator", "editor"], "fields" => ["ID"]]);
    if (empty($users)) {
        return;
    }
    $selected_owner = 0;
    $lowest_load = PHP_INT_MAX;
    foreach ($users as $user) {
        $uid = isset($user->ID) ? (int) $user->ID : 0;
        if ($uid <= 0) {
            continue;
        }
        $count_open = (int) count(get_posts([
            "post_type" => "crm_offer",
            "post_status" => ["publish", "draft", "pending", "private"],
            "posts_per_page" => 300,
            "fields" => "ids",
            "meta_query" => [[
                "key" => "_ups_offer_owner_id",
                "value" => (string) $uid,
            ], [
                "key" => "_ups_offer_status",
                "value" => ["won", "lost"],
                "compare" => "NOT IN",
            ]],
        ]));
        if ($count_open < $lowest_load) {
            $lowest_load = $count_open;
            $selected_owner = $uid;
        }
    }
    if ($selected_owner > 0) {
        update_post_meta($post_id, "_ups_offer_owner_id", $selected_owner);
    }
}
add_action("save_post_crm_offer", "upsellio_automation_assign_offer_owner_if_missing", 20, 3);

function upsellio_automation_data_hygiene()
{
    $clients = get_posts([
        "post_type" => "crm_client",
        "post_status" => ["publish", "draft", "pending", "private"],
        "posts_per_page" => 500,
    ]);
    $seen = [];
    foreach ($clients as $client) {
        $cid = (int) $client->ID;
        $email = strtolower(trim((string) get_post_meta($cid, "_ups_client_email", true)));
        if ($email === "") {
            continue;
        }
        if (!isset($seen[$email])) {
            $seen[$email] = $cid;
            if ((float) get_post_meta($cid, "_ups_client_budget_range", true) <= 0) {
                $industry = strtolower((string) get_post_meta($cid, "_ups_client_industry", true));
                $defaults = [
                    "saas" => 8000,
                    "ecommerce" => 6000,
                    "uslugi" => 4000,
                ];
                foreach ($defaults as $needle => $budget) {
                    if ($industry !== "" && strpos($industry, $needle) !== false) {
                        update_post_meta($cid, "_ups_client_budget_range", (string) $budget);
                        break;
                    }
                }
            }
            continue;
        }
        update_post_meta($cid, "_ups_client_duplicate_of", (int) $seen[$email]);
    }

    $offers = get_posts([
        "post_type" => "crm_offer",
        "post_status" => ["publish", "draft", "pending", "private"],
        "posts_per_page" => 500,
        "fields" => "ids",
    ]);
    foreach ($offers as $offer_id) {
        $offer_id = (int) $offer_id;
        $new_key = (string) get_post_meta($offer_id, "_ups_offer_last_inbound_classification", true);
        $legacy_key = (string) get_post_meta($offer_id, "_ups_offer_last_inbound_class", true);
        if ($new_key === "" && $legacy_key !== "") {
            update_post_meta($offer_id, "_ups_offer_last_inbound_classification", $legacy_key);
        } elseif ($legacy_key === "" && $new_key !== "") {
            update_post_meta($offer_id, "_ups_offer_last_inbound_class", $new_key);
        }
    }
}

function upsellio_automation_ab_promote_winner()
{
    $stats = get_option("ups_ab_offer_stats", []);
    if (!is_array($stats) || empty($stats)) {
        return;
    }
    $a = isset($stats["A"]) && is_array($stats["A"]) ? $stats["A"] : ["views" => 0, "clicks" => 0];
    $b = isset($stats["B"]) && is_array($stats["B"]) ? $stats["B"] : ["views" => 0, "clicks" => 0];
    $min_sample = max(5, (int) get_option("ups_automation_ab_min_sample", 20));
    if ((int) $a["views"] < $min_sample || (int) $b["views"] < $min_sample) {
        return;
    }
    $cr_a = (int) $a["views"] > 0 ? ((float) $a["clicks"] / (float) $a["views"]) : 0.0;
    $cr_b = (int) $b["views"] > 0 ? ((float) $b["clicks"] / (float) $b["views"]) : 0.0;
    $winner = $cr_b > $cr_a ? "B" : "A";
    $lift_pct = $cr_a > 0 ? ((($cr_b - $cr_a) / $cr_a) * 100.0) : 0.0;
    if ($winner === "A" && $cr_b > 0) {
        $lift_pct = ((($cr_a - $cr_b) / $cr_b) * 100.0);
    }
    $min_lift = (float) get_option("ups_automation_ab_min_lift_pct", 5);
    if ($lift_pct < $min_lift) {
        return;
    }
    update_option("ups_ab_offer_winner", $winner, false);
    if ($winner === "A") {
        $html = (string) get_option("ups_offer_template_html_a", "");
        $css = (string) get_option("ups_offer_template_css_a", "");
    } else {
        $html = (string) get_option("ups_offer_template_html_b", "");
        $css = (string) get_option("ups_offer_template_css_b", "");
    }
    if ($html !== "") {
        update_option("ups_offer_template_html", $html, false);
    }
    if ($css !== "") {
        update_option("ups_offer_template_css", $css, false);
    }
}

function upsellio_automation_promote_followup_winners()
{
    $templates = get_posts([
        "post_type" => "ups_followup_template",
        "post_status" => "publish",
        "posts_per_page" => 300,
    ]);
    $groups = [];
    foreach ($templates as $tpl) {
        $tid = (int) $tpl->ID;
        $group = sanitize_key((string) get_post_meta($tid, "_ups_followup_ab_group", true));
        if ($group === "") {
            continue;
        }
        if (!isset($groups[$group])) {
            $groups[$group] = [];
        }
        $groups[$group][] = $tid;
    }
    $stats = get_option("ups_ab_followup_stats", []);
    if (!is_array($stats) || empty($groups)) {
        return;
    }
    $min_sample = max(5, (int) get_option("ups_automation_ab_min_sample", 20));
    foreach ($groups as $group => $template_ids) {
        $winner_id = 0;
        $winner_rate = -1;
        foreach ($template_ids as $tid) {
            $s = isset($stats[$tid]) && is_array($stats[$tid]) ? $stats[$tid] : ["sent" => 0, "conversions" => 0];
            $sent = (int) ($s["sent"] ?? 0);
            $conv = (int) ($s["conversions"] ?? 0);
            if ($sent < $min_sample) {
                continue;
            }
            $rate = $sent > 0 ? ($conv / $sent) : 0;
            if ($rate > $winner_rate) {
                $winner_rate = $rate;
                $winner_id = (int) $tid;
            }
        }
        if ($winner_id <= 0) {
            continue;
        }
        foreach ($template_ids as $tid) {
            update_post_meta((int) $tid, "_ups_followup_is_primary", ((int) $tid === $winner_id) ? "1" : "0");
        }
    }
}

function upsellio_automation_sync_ga4_channel_quality()
{
    if ((string) get_option("ups_automation_ga4_sync_enabled", "1") !== "1") {
        return;
    }
    $rows = get_option("ups_automation_ga4_daily_aggregates", []);
    if (!is_array($rows) || empty($rows)) {
        return;
    }
    $scores = [];
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $source = sanitize_text_field((string) ($row["source"] ?? ""));
        $campaign = sanitize_text_field((string) ($row["campaign"] ?? ""));
        $sessions = max(0, (int) ($row["sessions"] ?? 0));
        $engaged = max(0, (int) ($row["engaged_sessions"] ?? 0));
        $conversions = max(0, (int) ($row["conversions"] ?? 0));
        $engagement_rate = $sessions > 0 ? ($engaged / $sessions) : 0;
        $conversion_rate = $sessions > 0 ? ($conversions / $sessions) : 0;
        $score = (int) round(($engagement_rate * 40) + ($conversion_rate * 60), 2);
        $key = strtolower(trim($source . "|" . $campaign));
        $scores[$key] = [
            "source" => $source,
            "campaign" => $campaign,
            "score" => max(0, min(100, $score)),
            "sessions" => $sessions,
            "conversions" => $conversions,
        ];
    }
    update_option("ups_automation_channel_quality_scores", $scores, false);
    $offers = get_posts([
        "post_type" => "crm_offer",
        "post_status" => ["publish", "draft", "pending", "private"],
        "posts_per_page" => 500,
        "fields" => "ids",
    ]);
    foreach ($offers as $offer_id) {
        $offer_id = (int) $offer_id;
        $source = sanitize_text_field((string) get_post_meta($offer_id, "_ups_offer_utm_source", true));
        $campaign = sanitize_text_field((string) get_post_meta($offer_id, "_ups_offer_utm_campaign", true));
        $key = strtolower(trim($source . "|" . $campaign));
        if (isset($scores[$key])) {
            update_post_meta($offer_id, "_ups_offer_channel_quality_score", (int) $scores[$key]["score"]);
        }
    }
}

function upsellio_automation_monthly_revenue_ops_and_alerts()
{
    $offers = get_posts([
        "post_type" => "crm_offer",
        "post_status" => ["publish", "draft", "pending", "private"],
        "posts_per_page" => 500,
    ]);
    $won = 0;
    $lost = 0;
    $open = 0;
    $send_failed = 0;
    foreach ($offers as $offer) {
        $oid = (int) $offer->ID;
        $status = (string) get_post_meta($oid, "_ups_offer_status", true);
        if ($status === "won") {
            $won++;
        } elseif ($status === "lost") {
            $lost++;
        } else {
            $open++;
        }
        $queue = get_post_meta($oid, "_ups_offer_followup_queue", true);
        if (is_array($queue)) {
            foreach ($queue as $item) {
                if ((string) ($item["status"] ?? "") === "failed") {
                    $send_failed++;
                }
            }
        }
    }
    $win_rate = ($won + $lost) > 0 ? (($won / ($won + $lost)) * 100.0) : 0.0;
    $prev = get_option("ups_automation_prev_win_rate", null);
    if ($prev !== null) {
        $drop = (float) $prev - $win_rate;
        if ($drop >= (float) get_option("ups_automation_alert_drop_win_rate_pct", 10)) {
            wp_mail(get_option("admin_email"), "Alert CRM: spadek win-rate", "Win-rate spadł o " . number_format($drop, 2) . " p.p.");
        }
    }
    update_option("ups_automation_prev_win_rate", $win_rate, false);
    $lost_rate = ($won + $lost + $open) > 0 ? (($lost / ($won + $lost + $open)) * 100.0) : 0.0;
    if ($lost_rate >= (float) get_option("ups_automation_alert_lost_spike_pct", 20)) {
        wp_mail(get_option("admin_email"), "Alert CRM: wysoki lost-rate", "Lost-rate osiągnął " . number_format($lost_rate, 2) . "%.");
    }
    if ($send_failed >= 10) {
        wp_mail(get_option("admin_email"), "Alert CRM: dużo send_failed", "Wykryto " . $send_failed . " failed wiadomości follow-up.");
    }
}

function upsellio_automation_daily_owner_digest()
{
    $users = get_users(["role__in" => ["administrator", "editor"]]);
    foreach ($users as $user) {
        $uid = isset($user->ID) ? (int) $user->ID : 0;
        if ($uid <= 0) {
            continue;
        }
        $email = sanitize_email((string) ($user->user_email ?? ""));
        if (!is_email($email)) {
            continue;
        }
        $offers = get_posts([
            "post_type" => "crm_offer",
            "post_status" => ["publish", "draft", "pending", "private"],
            "posts_per_page" => 300,
            "fields" => "ids",
            "meta_query" => [[
                "key" => "_ups_offer_owner_id",
                "value" => (string) $uid,
            ], [
                "key" => "_ups_offer_status",
                "value" => ["won", "lost"],
                "compare" => "NOT IN",
            ]],
        ]);
        $tasks = get_posts([
            "post_type" => "lead_task",
            "post_status" => ["publish", "draft", "pending", "private"],
            "posts_per_page" => 300,
            "author" => $uid,
            "fields" => "ids",
            "meta_query" => [[
                "key" => "_upsellio_task_status",
                "value" => "open",
            ]],
        ]);
        wp_mail(
            $email,
            "Daily CRM reminder",
            "Otwarte deale: " . count($offers) . "\nOtwarte zadania: " . count($tasks) . "\nPanel: " . home_url("/crm-app/")
        );
    }
}

function upsellio_automation_prospecting_queue_process()
{
    $prospects = get_posts([
        "post_type" => "crm_prospect",
        "post_status" => ["publish", "draft", "pending", "private"],
        "posts_per_page" => 200,
    ]);
    $delay_days = max(1, (int) get_option("ups_automation_cold_followup_days", 3));
    foreach ($prospects as $prospect) {
        $pid = (int) $prospect->ID;
        $status = (string) get_post_meta($pid, "_ups_prospect_status", true);
        if (in_array($status, ["replied", "paused", "converted", "bounced"], true)) {
            continue;
        }
        $email = sanitize_email((string) get_post_meta($pid, "_ups_prospect_email", true));
        if (!is_email($email)) {
            continue;
        }
        $step = max(1, (int) get_post_meta($pid, "_ups_prospect_step", true));
        $next_at = (string) get_post_meta($pid, "_ups_prospect_next_at", true);
        $next_ts = $next_at !== "" ? strtotime($next_at) : false;
        if ($next_ts !== false && $next_ts > time()) {
            continue;
        }
        $subject = (string) get_option("ups_prospect_subject_step_" . $step, "Krótka propozycja współpracy");
        $body_tpl = (string) get_option("ups_prospect_body_step_" . $step, "Cześć {{name}},\n\nCzy mogę podesłać 2 pomysły na zwiększenie leadów B2B?");
        $name = (string) get_post_meta($pid, "_ups_prospect_name", true);
        $company = (string) get_post_meta($pid, "_ups_prospect_company", true);
        $body = strtr($body_tpl, [
            "{{name}}" => $name !== "" ? $name : "tam",
            "{{company}}" => $company,
            "{{today}}" => current_time("Y-m-d"),
        ]);
        $sent = function_exists("upsellio_followup_send_html_mail")
            ? upsellio_followup_send_html_mail($email, $subject, nl2br(esc_html($body)))
            : false;
        if ($sent) {
            update_post_meta($pid, "_ups_prospect_last_sent_at", current_time("mysql"));
            update_post_meta($pid, "_ups_prospect_status", "active");
            if ($step >= 5) {
                update_post_meta($pid, "_ups_prospect_status", "paused");
            } else {
                update_post_meta($pid, "_ups_prospect_step", $step + 1);
                update_post_meta($pid, "_ups_prospect_next_at", gmdate("Y-m-d H:i:s", time() + ($delay_days * DAY_IN_SECONDS)));
            }
        }
    }
}
add_action("upsellio_prospecting_queue", "upsellio_automation_prospecting_queue_process");

function upsellio_automation_mark_prospect_reply($offer_id, $subject, $body, $from_email)
{
    $from_email = sanitize_email((string) $from_email);
    if (!is_email($from_email)) {
        return;
    }
    $prospects = get_posts([
        "post_type" => "crm_prospect",
        "post_status" => ["publish", "draft", "pending", "private"],
        "posts_per_page" => 1,
        "meta_query" => [[
            "key" => "_ups_prospect_email",
            "value" => $from_email,
        ]],
    ]);
    if (empty($prospects)) {
        return;
    }
    $pid = (int) $prospects[0]->ID;
    update_post_meta($pid, "_ups_prospect_status", "replied");
    $text = strtolower((string) $subject . " " . (string) $body);
    $classification = "other";
    if (preg_match("/\b(yes|tak|ok|dzialamy|rozmawiajmy)\b/u", $text)) {
        $classification = "positive";
    } elseif (preg_match("/\b(not now|pozniej|nie teraz)\b/u", $text)) {
        $classification = "timing_objection";
    } elseif (preg_match("/\b(price|cena|koszt)\b/u", $text)) {
        $classification = "price_objection";
    }
    update_post_meta($pid, "_ups_prospect_reply_class", $classification);
    $stage = "awareness";
    if ($classification === "positive") {
        $stage = "decision";
    } elseif (in_array($classification, ["price_objection", "timing_objection"], true)) {
        $stage = "consideration";
    }
    update_post_meta($pid, "_ups_prospect_target_stage", $stage);
    $client_id = 0;
    $existing_clients = get_posts([
        "post_type" => "crm_client",
        "post_status" => ["publish", "draft", "pending", "private"],
        "posts_per_page" => 1,
        "meta_query" => [[
            "key" => "_ups_client_email",
            "value" => $from_email,
        ]],
    ]);
    if (!empty($existing_clients)) {
        $client_id = (int) $existing_clients[0]->ID;
    } elseif ($classification === "positive") {
        $client_id = (int) wp_insert_post([
            "post_type" => "crm_client",
            "post_status" => "publish",
            "post_title" => (string) get_post_meta($pid, "_ups_prospect_company", true) ?: (string) get_post_meta($pid, "_ups_prospect_name", true),
        ]);
        if ($client_id > 0) {
            update_post_meta($client_id, "_ups_client_email", $from_email);
            update_post_meta($client_id, "_ups_client_company", (string) get_post_meta($pid, "_ups_prospect_company", true));
        }
    }
    if ($client_id > 0) {
        $offer_id = (int) wp_insert_post([
            "post_type" => "crm_offer",
            "post_status" => "publish",
            "post_title" => "Prospecting: " . (string) get_post_meta($pid, "_ups_prospect_company", true),
        ]);
        if ($offer_id > 0) {
            update_post_meta($offer_id, "_ups_offer_client_id", $client_id);
            update_post_meta($offer_id, "_ups_offer_status", "open");
            update_post_meta($offer_id, "_ups_offer_stage", $stage);
            update_post_meta($offer_id, "_ups_offer_source", "cold_outbound");
            update_post_meta($pid, "_ups_prospect_offer_id", $offer_id);
        }
    }
}
add_action("upsellio_followup_inbound_received", "upsellio_automation_mark_prospect_reply", 30, 4);

function upsellio_automation_hourly_runner()
{
    upsellio_automation_handle_sla_rules();
}
add_action("upsellio_automation_hourly", "upsellio_automation_hourly_runner");

function upsellio_automation_sla_quarter_runner()
{
    upsellio_automation_process_pipeline_sla_deals();
}
add_action("upsellio_automation_sla_quarter", "upsellio_automation_sla_quarter_runner");

function upsellio_automation_daily_runner()
{
    upsellio_automation_data_hygiene();
    upsellio_automation_ab_promote_winner();
    upsellio_automation_promote_followup_winners();
    upsellio_automation_sync_ga4_channel_quality();
    upsellio_automation_monthly_revenue_ops_and_alerts();
    upsellio_automation_daily_owner_digest();
    if (post_type_exists("lead_task")) {
        $task_ids = get_posts([
            "post_type" => "lead_task",
            "post_status" => ["publish", "draft", "pending", "private"],
            "posts_per_page" => 500,
            "fields" => "ids",
        ]);
        foreach ($task_ids as $tid) {
            upsellio_automation_refresh_task_priority_meta((int) $tid);
        }
    }
}
add_action("upsellio_automation_daily", "upsellio_automation_daily_runner");

function upsellio_automation_sync_client_lifecycle_from_offer($offer_id, $new_status, $old_status)
{
    $offer_id = (int) $offer_id;
    $new_status = sanitize_key((string) $new_status);
    if ($offer_id <= 0) {
        return;
    }
    $client_id = (int) get_post_meta($offer_id, "_ups_offer_client_id", true);
    if ($client_id <= 0) {
        return;
    }
    if ($new_status === "won") {
        update_post_meta($client_id, "_ups_client_lifecycle_status", "active_client");
    } elseif ($new_status === "lost") {
        update_post_meta($client_id, "_ups_client_lifecycle_status", "at_risk");
    } else {
        update_post_meta($client_id, "_ups_client_lifecycle_status", "active_deal");
    }
}
add_action("upsellio_offer_status_changed", "upsellio_automation_sync_client_lifecycle_from_offer", 40, 3);

function upsellio_automation_clear_sla_on_offer_closed($offer_id, $new_status, $old_status)
{
    $offer_id = (int) $offer_id;
    $new_status = sanitize_key((string) $new_status);
    if ($offer_id <= 0) {
        return;
    }
    if (in_array($new_status, ["won", "lost"], true)) {
        delete_post_meta($offer_id, "_ups_offer_sla_active_alert");
    }
}
add_action("upsellio_offer_status_changed", "upsellio_automation_clear_sla_on_offer_closed", 5, 3);
