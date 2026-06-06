<?php

if (!defined("ABSPATH")) {
    exit;
}

function ups_audit_ajax_guard($cap = "edit_posts")
{
    check_ajax_referer("ups_crm_app_action", "nonce");
    if (!current_user_can($cap)) {
        wp_send_json_error(["msg" => "forbidden"], 403);
    }
}

add_action("wp_ajax_ups_audit_oauth_start", function () {
    ups_audit_ajax_guard("edit_posts");
    $label = isset($_POST["label"]) ? sanitize_text_field(wp_unslash($_POST["label"])) : "";
    $include_ads = !isset($_POST["include_ads"]) || (string) wp_unslash($_POST["include_ads"]) !== "0";
    $url = ups_audit_start_oauth_connect($label, $include_ads);
    $info = function_exists("ups_audit_oauth_connection_info") ? ups_audit_oauth_connection_info() : [];
    wp_send_json_success([
        "redirect_url" => $url,
        "mode" => (string) ($info["mode"] ?? ""),
        "redirect_uri" => (string) ($info["redirect_uri"] ?? ""),
    ]);
});

add_action("wp_ajax_ups_audit_account_disconnect", function () {
    ups_audit_ajax_guard("manage_options");
    $account_id = isset($_POST["account_id"]) ? (int) $_POST["account_id"] : 0;
    if ($account_id <= 0 || get_post_type($account_id) !== "crm_google_account") {
        wp_send_json_error(["msg" => "invalid_account"], 400);
    }
    $resources = get_posts([
        "post_type" => "crm_audit_resource",
        "posts_per_page" => -1,
        "post_status" => ["publish", "draft"],
        "meta_query" => [[
            "key" => "_ups_resource_google_account_id",
            "value" => $account_id,
            "compare" => "=",
        ]],
    ]);
    foreach ($resources as $r) {
        if ($r instanceof WP_Post) {
            wp_delete_post((int) $r->ID, true);
        }
    }
    wp_delete_post($account_id, true);
    wp_send_json_success(["ok" => true]);
});

add_action("wp_ajax_ups_audit_account_refresh_resources", function () {
    ups_audit_ajax_guard("edit_posts");
    $account_id = isset($_POST["account_id"]) ? (int) $_POST["account_id"] : 0;
    if ($account_id <= 0) {
        wp_send_json_error(["msg" => "invalid_account"], 400);
    }
    $cache = [
        "ga4" => ups_audit_fetch_ga4_resources($account_id),
        "gsc" => ups_audit_fetch_gsc_resources($account_id),
        "ads" => ups_audit_fetch_ads_resources($account_id),
    ];
    update_post_meta($account_id, "_ups_gacc_resources_cache", $cache);
    update_post_meta($account_id, "_ups_gacc_last_sync_at", current_time("mysql"));
    wp_send_json_success(["cache" => $cache]);
});

add_action("wp_ajax_ups_audit_resource_import", function () {
    ups_audit_ajax_guard("edit_posts");
    $google_account_id = isset($_POST["google_account_id"]) ? (int) $_POST["google_account_id"] : 0;
    $type = isset($_POST["type"]) ? sanitize_key(wp_unslash($_POST["type"])) : "";
    $external_id = isset($_POST["external_id"]) ? sanitize_text_field(wp_unslash($_POST["external_id"])) : "";
    $display_name = isset($_POST["display_name"]) ? sanitize_text_field(wp_unslash($_POST["display_name"])) : "";
    $parent_account_id = isset($_POST["parent_account_id"]) ? sanitize_text_field(wp_unslash($_POST["parent_account_id"])) : "";
    if ($google_account_id <= 0 || !in_array($type, ["ga4", "gsc", "ads"], true) || $external_id === "") {
        wp_send_json_error(["msg" => "invalid_payload"], 400);
    }
    $existing = get_posts([
        "post_type" => "crm_audit_resource",
        "posts_per_page" => 1,
        "post_status" => ["publish", "draft"],
        "meta_query" => [
            "relation" => "AND",
            ["key" => "_ups_resource_google_account_id", "value" => $google_account_id],
            ["key" => "_ups_resource_type", "value" => $type],
            ["key" => "_ups_resource_external_id", "value" => $external_id],
        ],
    ]);
    if (!empty($existing) && $existing[0] instanceof WP_Post) {
        wp_send_json_success([
            "resource_id" => (int) $existing[0]->ID,
            "already_exists" => true,
            "message" => __("Zasób był już zaimportowany.", "upsellio"),
        ]);
    }
    $resource_id = wp_insert_post([
        "post_type" => "crm_audit_resource",
        "post_title" => $display_name !== "" ? $display_name : $external_id,
        "post_status" => "publish",
    ]);
    if ($resource_id <= 0) {
        wp_send_json_error(["msg" => "create_failed"], 500);
    }
    update_post_meta($resource_id, "_ups_resource_type", $type);
    update_post_meta($resource_id, "_ups_resource_external_id", $external_id);
    update_post_meta($resource_id, "_ups_resource_display_name", $display_name);
    update_post_meta($resource_id, "_ups_resource_parent_account_id", $parent_account_id);
    update_post_meta($resource_id, "_ups_resource_google_account_id", $google_account_id);
    update_post_meta($resource_id, "_ups_resource_client_id", 0);
    update_post_meta($resource_id, "_ups_resource_imported_at", current_time("mysql"));
    wp_schedule_single_event(time() + 5, "ups_audit_sync_resource_action", [(int) $resource_id]);
    wp_send_json_success([
        "resource_id" => (int) $resource_id,
        "already_exists" => false,
        "message" => __("Zasób zaimportowany.", "upsellio"),
    ]);
});

add_action("wp_ajax_ups_audit_resource_remove", function () {
    ups_audit_ajax_guard("edit_posts");
    $resource_id = isset($_POST["resource_id"]) ? (int) $_POST["resource_id"] : 0;
    if ($resource_id <= 0 || get_post_type($resource_id) !== "crm_audit_resource") {
        wp_send_json_error(["msg" => "invalid_resource"], 400);
    }
    wp_delete_post($resource_id, true);
    wp_send_json_success(["ok" => true]);
});

add_action("wp_ajax_ups_audit_clarity_test", function () {
    ups_audit_ajax_guard("edit_posts");
    $token = isset($_POST["api_token"]) ? sanitize_text_field(wp_unslash($_POST["api_token"])) : "";
    $resource_id = isset($_POST["resource_id"]) ? (int) $_POST["resource_id"] : 0;
    if ($token === "" && $resource_id > 0 && function_exists("ups_audit_clarity_get_token")) {
        $token = ups_audit_clarity_get_token($resource_id);
    }
    if ($token === "" || !function_exists("ups_audit_clarity_fetch_live_insights")) {
        wp_send_json_error(["msg" => __("Brak tokena API Clarity.", "upsellio")], 400);
    }
    $raw = ups_audit_clarity_fetch_live_insights($token, 1, "Device");
    if (is_wp_error($raw)) {
        wp_send_json_error(["msg" => $raw->get_error_message()], 400);
    }
    if ($resource_id > 0) {
        ups_audit_clarity_track_request($resource_id);
    }
    $summary = function_exists("ups_audit_clarity_parse_insights")
        ? ups_audit_clarity_parse_insights($raw, "Device", true, "by_device")
        : [];
    wp_send_json_success([
        "sessions" => (int) ($summary["sessions"] ?? 0),
        "users" => (int) ($summary["users"] ?? 0),
        "dead_clicks" => (int) ($summary["dead_clicks"] ?? 0),
        "message" => __("Połączenie z Clarity API działa.", "upsellio"),
    ]);
});

add_action("wp_ajax_ups_audit_clarity_import", function () {
    ups_audit_ajax_guard("edit_posts");
    $name = isset($_POST["project_name"]) ? sanitize_text_field(wp_unslash($_POST["project_name"])) : "";
    $slug = isset($_POST["project_slug"]) ? sanitize_key(wp_unslash($_POST["project_slug"])) : "";
    $token = isset($_POST["api_token"]) ? sanitize_text_field(wp_unslash($_POST["api_token"])) : "";
    if (!function_exists("ups_audit_clarity_import_resource")) {
        wp_send_json_error(["msg" => "not_available"], 500);
    }
    $result = ups_audit_clarity_import_resource($name, $token, $slug);
    if (is_wp_error($result)) {
        wp_send_json_error(["msg" => $result->get_error_message()], 400);
    }
    $resource_id = (int) $result;
    if (function_exists("ups_audit_sync_clarity_resource")) {
        ups_audit_sync_clarity_resource($resource_id, 30, false);
    }
    wp_send_json_success([
        "resource_id" => $resource_id,
        "message" => __("Projekt Clarity dodany. Zmapuj go do profilu klienta.", "upsellio"),
    ]);
});

add_action("wp_ajax_ups_audit_create_client_profile", function () {
    ups_audit_ajax_guard("edit_posts");
    $title = isset($_POST["title"]) ? sanitize_text_field(wp_unslash($_POST["title"])) : "";
    $website = isset($_POST["website"]) ? esc_url_raw(wp_unslash($_POST["website"])) : "";
    if (!function_exists("ups_audit_create_audit_profile")) {
        wp_send_json_error(["msg" => "not_available"], 500);
    }
    $result = ups_audit_create_audit_profile($title, $website);
    if (is_wp_error($result)) {
        wp_send_json_error(["msg" => $result->get_error_message()], 400);
    }
    wp_send_json_success([
        "client_id" => (int) $result,
        "dashboard_url" => function_exists("upsellio_crm_url")
            ? upsellio_crm_url("ca-dashboard", ["cid" => (int) $result])
            : add_query_arg(["view" => "ca-dashboard", "cid" => (int) $result], home_url("/crm-app/")),
    ]);
});

add_action("wp_ajax_ups_audit_map_to_client", function () {
    ups_audit_ajax_guard("edit_posts");
    $client_id = isset($_POST["client_id"]) ? (int) $_POST["client_id"] : 0;
    $google_account_id = isset($_POST["google_account_id"]) ? (int) $_POST["google_account_id"] : 0;
    $resources_json = isset($_POST["resources"]) ? wp_unslash($_POST["resources"]) : "[]";
    $resources = json_decode((string) $resources_json, true);
    if ($client_id <= 0 || get_post_type($client_id) !== "crm_client" || !is_array($resources)) {
        wp_send_json_error(["msg" => "invalid_payload"], 400);
    }
    $selected_ids = [];
    foreach ($resources as $row) {
        if (!is_array($row)) {
            continue;
        }
        $rid = (int) ($row["resource_id"] ?? 0);
        if ($rid > 0 && get_post_type($rid) === "crm_audit_resource") {
            $selected_ids[$rid] = true;
        }
    }
    $selected_ids = array_keys($selected_ids);

    if ($google_account_id > 0) {
        $account_resources = function_exists("ups_audit_get_google_account_resources")
            ? ups_audit_get_google_account_resources($google_account_id)
            : [];
        foreach ($account_resources as $res) {
            if (!($res instanceof WP_Post)) {
                continue;
            }
            $rid = (int) $res->ID;
            if (in_array($rid, $selected_ids, true)) {
                update_post_meta($rid, "_ups_resource_client_id", $client_id);
            } elseif ((int) get_post_meta($rid, "_ups_resource_client_id", true) === $client_id) {
                update_post_meta($rid, "_ups_resource_client_id", 0);
            }
        }
    } else {
        if (function_exists("ups_audit_get_client_resources")) {
            foreach (ups_audit_get_client_resources($client_id) as $res) {
                if (!($res instanceof WP_Post)) {
                    continue;
                }
                $rid = (int) $res->ID;
                if (!in_array($rid, $selected_ids, true)) {
                    update_post_meta($rid, "_ups_resource_client_id", 0);
                }
            }
        }
        foreach ($selected_ids as $rid) {
            update_post_meta((int) $rid, "_ups_resource_client_id", $client_id);
        }
    }
    foreach ($selected_ids as $rid) {
        $rid = (int) $rid;
        if ($rid <= 0 || (string) get_post_meta($rid, "_ups_resource_type", true) !== "clarity") {
            continue;
        }
        if (function_exists("ups_audit_sync_clarity_resource")) {
            ups_audit_sync_clarity_resource($rid, 30, false);
        }
    }
    wp_send_json_success(["ok" => true, "mapped" => count($selected_ids)]);
});

add_action("wp_ajax_ups_audit_unmap_from_client", function () {
    ups_audit_ajax_guard("edit_posts");
    $resource_id = isset($_POST["resource_id"]) ? (int) $_POST["resource_id"] : 0;
    if ($resource_id <= 0 || get_post_type($resource_id) !== "crm_audit_resource") {
        wp_send_json_error(["msg" => "invalid_resource"], 400);
    }
    update_post_meta($resource_id, "_ups_resource_client_id", 0);
    wp_send_json_success(["ok" => true]);
});

add_action("wp_ajax_ups_audit_resource_sync", function () {
    ups_audit_ajax_guard("edit_posts");
    $resource_id = isset($_POST["resource_id"]) ? (int) $_POST["resource_id"] : 0;
    if ($resource_id <= 0 || get_post_type($resource_id) !== "crm_audit_resource") {
        wp_send_json_error(["msg" => "invalid_resource"], 400);
    }
    ups_audit_sync_resource_action($resource_id);
    wp_send_json_success(["ok" => true]);
});

add_action("wp_ajax_ups_audit_generate_report", function () {
    ups_audit_ajax_guard("edit_posts");
    $client_id = isset($_POST["client_id"]) ? (int) $_POST["client_id"] : 0;
    $type = isset($_POST["report_type"]) ? sanitize_key(wp_unslash($_POST["report_type"])) : "monthly";
    if (!in_array($type, ["monthly", "audit", "plan", "comparison", "brief", "seo_roadmap", "ux_audit"], true)) {
        $type = "monthly";
    }
    $result = function_exists("ups_audit_generate_report_by_type")
        ? ups_audit_generate_report_by_type($client_id, $type)
        : ups_audit_generate_monthly_report($client_id);
    if ((int) ($result["id"] ?? 0) <= 0) {
        wp_send_json_error(["msg" => "report_failed"], 500);
    }
    wp_send_json_success($result);
});

add_action("wp_ajax_ups_audit_send_report_email", function () {
    ups_audit_ajax_guard("edit_posts");
    $report_id = isset($_POST["report_id"]) ? (int) $_POST["report_id"] : 0;
    $client_id = (int) get_post_meta($report_id, "_ups_report_client_id", true);
    $email_in = isset($_POST["email"]) ? sanitize_email(wp_unslash($_POST["email"])) : "";
    $email = is_email($email_in) ? $email_in : sanitize_email((string) get_post_meta($client_id, "_ups_client_email", true));
    $report = get_post($report_id);
    if ($report_id <= 0 || !($report instanceof WP_Post) || !is_email($email)) {
        wp_send_json_error(["msg" => "invalid_email_or_report"], 400);
    }
    $sent = wp_mail($email, "Raport AI - " . get_the_title($report_id), (string) $report->post_content);
    wp_send_json_success(["sent" => (bool) $sent]);
});

add_action("wp_ajax_ups_audit_export_pdf", function () {
    ups_audit_ajax_guard("edit_posts");
    $report_id = isset($_POST["report_id"]) ? (int) $_POST["report_id"] : 0;
    $report = get_post($report_id);
    if ($report_id <= 0 || !($report instanceof WP_Post)) {
        wp_send_json_error(["msg" => "invalid_report"], 400);
    }
    if (!function_exists("ups_audit_render_html_to_pdf")) {
        wp_send_json_error(["msg" => "pdf_feature_unavailable"], 500);
    }
    $binary = ups_audit_render_html_to_pdf((string) $report->post_content);
    if (is_wp_error($binary)) {
        wp_send_json_error(["msg" => $binary->get_error_message()], 500);
    }
    $upload = wp_upload_dir();
    $file = "audit-report-" . $report_id . "-" . wp_date("Ymd-His") . ".pdf";
    $path = trailingslashit((string) $upload["path"]) . $file;
    file_put_contents($path, $binary, LOCK_EX);
    wp_send_json_success(["url" => trailingslashit((string) $upload["url"]) . $file, "filename" => $file]);
});

add_action("wp_ajax_ups_audit_dashboard_data", function () {
    ups_audit_ajax_guard("edit_posts");
    $client_id = isset($_POST["client_id"]) ? (int) $_POST["client_id"] : 0;
    $window = isset($_POST["window"]) ? (int) $_POST["window"] : 30;
    $window = in_array($window, [7, 14, 30, 60, 90], true) ? $window : 30;
    $current = ups_audit_aggregate_client_data($client_id, $window, 0, false);
    if ($current !== [] && function_exists("ups_audit_attach_dashboard_extras")) {
        $current = ups_audit_attach_dashboard_extras($current, $client_id, $window);
    }
    $previous = function_exists("ups_audit_aggregate_previous_slice")
        ? ups_audit_aggregate_previous_slice($current)
        : ups_audit_aggregate_client_data($client_id, $window, $window);
    wp_send_json_success([
        "current" => $current,
        "previous" => $previous,
        "recommendations" => (array) ($current["recommendations"] ?? []),
        "intelligence" => (array) ($current["intelligence"] ?? []),
        "alerts" => (array) (($current["intelligence"]["alerts"] ?? [])),
        "health_score" => (int) ($current["health_score"] ?? 0),
    ]);
});

add_action("wp_ajax_ups_agency_marketing_snapshot", function () {
    ups_audit_ajax_guard("edit_posts");
    $window = isset($_POST["window"]) ? (int) $_POST["window"] : 30;
    $window = in_array($window, [7, 14, 30, 60, 90], true) ? $window : 30;
    $metrics = function_exists("upsellio_agency_marketing_metrics")
        ? upsellio_agency_marketing_metrics($window)
        : [];
    wp_send_json_success(["metrics" => $metrics]);
});

add_action("wp_ajax_ups_audit_sync_all", function () {
    ups_audit_ajax_guard("edit_posts");
    $days = isset($_POST["days"]) ? (int) $_POST["days"] : 0;
    $google_account_id = isset($_POST["google_account_id"]) ? (int) $_POST["google_account_id"] : 0;
    $client_id = isset($_POST["client_id"]) ? (int) $_POST["client_id"] : 0;
    if ($google_account_id > 0 && get_post_type($google_account_id) === "crm_google_account") {
        $result = function_exists("ups_audit_sync_google_account_resources")
            ? ups_audit_sync_google_account_resources($google_account_id, $days)
            : ["ok" => 0, "fail" => 0, "total" => 0];
        $result["google_account_id"] = $google_account_id;
        wp_send_json_success($result);
    }
    if ($client_id > 0) {
        $resources = ups_audit_get_client_resources($client_id);
        $ok = 0;
        $fail = 0;
        foreach ($resources as $r) {
            if (!($r instanceof WP_Post)) {
                continue;
            }
            ups_audit_sync_resource_action((int) $r->ID, $days);
            $cache = get_post_meta((int) $r->ID, "_ups_resource_data_cache", true);
            $err = is_array($cache) ? trim((string) ($cache["error"] ?? "")) : "";
            if ($err !== "") {
                $fail++;
            } else {
                $ok++;
            }
        }
        if (function_exists("ups_audit_dispatch_high_priority_alerts")) {
            $agg = ups_audit_aggregate_client_data($client_id, $days > 0 ? $days : (int) get_option("ups_audit_default_compare_window", 30), 0);
            ups_audit_dispatch_high_priority_alerts($client_id, (array) ($agg["recommendations"] ?? []));
        }
        wp_send_json_success(["ok" => $ok, "fail" => $fail, "total" => $ok + $fail, "client_id" => $client_id]);
    }
    $result = function_exists("ups_audit_sync_all_google_account_resources")
        ? ups_audit_sync_all_google_account_resources($days)
        : ups_audit_sync_all_mapped_resources($days);
    wp_send_json_success($result);
});

add_action("wp_ajax_ups_audit_portfolio", function () {
    ups_audit_ajax_guard("edit_posts");
    $rows = function_exists("ups_audit_get_portfolio_rows") ? ups_audit_get_portfolio_rows() : [];
    wp_send_json_success(["rows" => $rows]);
});

add_action("wp_ajax_ups_audit_refresh_cwv", function () {
    ups_audit_ajax_guard("edit_posts");
    $client_id = isset($_POST["client_id"]) ? (int) $_POST["client_id"] : 0;
    if ($client_id <= 0 || get_post_type($client_id) !== "crm_client") {
        wp_send_json_error(["msg" => "invalid_client"], 400);
    }
    $website = trim((string) get_post_meta($client_id, "_ups_client_website", true));
    if ($website === "") {
        $website = trim((string) get_post_meta($client_id, "_ups_client_url", true));
    }
    if ($website !== "" && !preg_match("#^https?://#i", $website)) {
        $website = "https://" . $website;
    }
    if ($website === "" || !function_exists("ups_audit_fetch_pagespeed_cwv")) {
        wp_send_json_error(["msg" => __("Brak URL klienta.", "upsellio")], 400);
    }
    $cwv = ups_audit_fetch_pagespeed_cwv($website, true, true);
    wp_send_json_success(["cwv" => $cwv]);
});

add_action("wp_ajax_ups_audit_command_center", function () {
    ups_audit_ajax_guard("edit_posts");
    $window = isset($_POST["window"]) ? (int) $_POST["window"] : 30;
    $data = function_exists("ups_audit_build_command_center")
        ? ups_audit_build_command_center($window)
        : ["clients" => [], "top_alerts" => [], "summary" => []];
    wp_send_json_success($data);
});

add_action("wp_ajax_ups_audit_client_resources", function () {
    ups_audit_ajax_guard("edit_posts");
    $client_id = isset($_POST["client_id"]) ? (int) $_POST["client_id"] : 0;
    if ($client_id <= 0 || get_post_type($client_id) !== "crm_client") {
        wp_send_json_error(["msg" => "invalid_client"], 400);
    }
    $resources = ups_audit_get_client_resources($client_id);
    $out = [];
    foreach ($resources as $r) {
        if (!($r instanceof WP_Post)) {
            continue;
        }
        $rid = (int) $r->ID;
        $cache = get_post_meta($rid, "_ups_resource_data_cache", true);
        $out[] = [
            "id" => $rid,
            "title" => (string) $r->post_title,
            "type" => (string) get_post_meta($rid, "_ups_resource_type", true),
            "external_id" => (string) get_post_meta($rid, "_ups_resource_external_id", true),
            "health" => function_exists("ups_audit_resource_health") ? ups_audit_resource_health($rid) : [],
            "summary" => function_exists("ups_audit_cache_summary") ? ups_audit_cache_summary($cache) : [],
            "last_sync" => (string) get_post_meta($rid, "_ups_resource_last_data_sync", true),
        ];
    }
    $setup = function_exists("ups_audit_client_setup_status") ? ups_audit_client_setup_status($client_id) : [];
    wp_send_json_success(["resources" => $out, "setup" => $setup]);
});

add_action("wp_ajax_ups_audit_export_dashboard_pdf", function () {
    ups_audit_ajax_guard("edit_posts");
    if (function_exists("set_time_limit")) {
        @set_time_limit(120);
    }
    $client_id = isset($_POST["client_id"]) ? (int) $_POST["client_id"] : 0;
    $window = isset($_POST["window"]) ? (int) $_POST["window"] : 30;
    if (!function_exists("ups_audit_export_dashboard_pdf")) {
        wp_send_json_error(["msg" => __("Moduł PDF niedostępny.", "upsellio")], 500);
    }
    try {
        $result = ups_audit_export_dashboard_pdf($client_id, $window);
        if (is_wp_error($result)) {
            wp_send_json_error([
                "msg" => $result->get_error_message(),
                "code" => $result->get_error_code(),
            ], 500);
        }
        wp_send_json_success($result);
    } catch (Throwable $e) {
        wp_send_json_error([
            "msg" => __("PDF: ", "upsellio") . $e->getMessage(),
            "code" => "exception",
        ], 500);
    }
});

add_action("wp_ajax_ups_audit_meta_account_disconnect", function () {
    ups_audit_ajax_guard("manage_options");
    $account_id = isset($_POST["account_id"]) ? (int) $_POST["account_id"] : 0;
    if ($account_id <= 0 || get_post_type($account_id) !== "crm_meta_account") {
        wp_send_json_error(["msg" => "invalid_account"], 400);
    }
    $resources = get_posts([
        "post_type" => "crm_audit_resource",
        "posts_per_page" => -1,
        "post_status" => ["publish", "draft"],
        "meta_query" => [[
            "key" => "_ups_resource_meta_account_id",
            "value" => $account_id,
            "compare" => "=",
        ]],
    ]);
    foreach ($resources as $r) {
        if ($r instanceof WP_Post) {
            wp_delete_post((int) $r->ID, true);
        }
    }
    wp_delete_post($account_id, true);
    wp_send_json_success(["ok" => true]);
});

add_action("wp_ajax_ups_audit_meta_account_refresh_resources", function () {
    ups_audit_ajax_guard("edit_posts");
    $account_id = isset($_POST["account_id"]) ? (int) $_POST["account_id"] : 0;
    if ($account_id <= 0 || get_post_type($account_id) !== "crm_meta_account") {
        wp_send_json_error(["msg" => "invalid_account"], 400);
    }
    $cache = ["ad_accounts" => ups_audit_fetch_meta_ad_accounts($account_id)];
    update_post_meta($account_id, "_ups_macc_resources_cache", $cache);
    update_post_meta($account_id, "_ups_macc_last_sync_at", current_time("mysql"));
    wp_send_json_success(["cache" => $cache]);
});

add_action("wp_ajax_ups_audit_meta_resource_import", function () {
    ups_audit_ajax_guard("edit_posts");
    $meta_account_id = isset($_POST["meta_account_id"]) ? (int) $_POST["meta_account_id"] : 0;
    $external_id = isset($_POST["external_id"]) ? sanitize_text_field(wp_unslash($_POST["external_id"])) : "";
    $display_name = isset($_POST["display_name"]) ? sanitize_text_field(wp_unslash($_POST["display_name"])) : "";
    $external_id = upsellio_meta_ads_normalize_ad_account_id($external_id);
    if ($meta_account_id <= 0 || $external_id === "" || get_post_type($meta_account_id) !== "crm_meta_account") {
        wp_send_json_error(["msg" => "invalid_payload"], 400);
    }
    $existing = ups_audit_find_imported_meta_resource_id($meta_account_id, $external_id);
    if ($existing > 0) {
        wp_send_json_success([
            "resource_id" => $existing,
            "already_exists" => true,
            "message" => __("Zasób Meta był już zaimportowany.", "upsellio"),
        ]);
    }
    $resource_id = wp_insert_post([
        "post_type" => "crm_audit_resource",
        "post_title" => $display_name !== "" ? $display_name : $external_id,
        "post_status" => "publish",
    ]);
    if ($resource_id <= 0) {
        wp_send_json_error(["msg" => "create_failed"], 500);
    }
    update_post_meta($resource_id, "_ups_resource_type", "meta");
    update_post_meta($resource_id, "_ups_resource_external_id", $external_id);
    update_post_meta($resource_id, "_ups_resource_display_name", $display_name);
    update_post_meta($resource_id, "_ups_resource_meta_account_id", $meta_account_id);
    update_post_meta($resource_id, "_ups_resource_google_account_id", 0);
    update_post_meta($resource_id, "_ups_resource_client_id", 0);
    update_post_meta($resource_id, "_ups_resource_imported_at", current_time("mysql"));
    wp_schedule_single_event(time() + 5, "ups_audit_sync_resource_action", [(int) $resource_id]);
    wp_send_json_success([
        "resource_id" => (int) $resource_id,
        "already_exists" => false,
        "message" => __("Konto reklamowe Meta zaimportowane.", "upsellio"),
    ]);
});

add_action("wp_ajax_ups_audit_refresh_all_meta_accounts", function () {
    ups_audit_ajax_guard("edit_posts");
    $accounts = get_posts([
        "post_type" => "crm_meta_account",
        "posts_per_page" => -1,
        "post_status" => ["publish", "draft"],
    ]);
    $done = 0;
    foreach ($accounts as $acc) {
        if (!($acc instanceof WP_Post)) {
            continue;
        }
        $aid = (int) $acc->ID;
        $cache = ["ad_accounts" => ups_audit_fetch_meta_ad_accounts($aid)];
        update_post_meta($aid, "_ups_macc_resources_cache", $cache);
        update_post_meta($aid, "_ups_macc_last_sync_at", current_time("mysql"));
        $done++;
    }
    wp_send_json_success(["refreshed" => $done]);
});

add_action("wp_ajax_ups_audit_refresh_all_accounts", function () {
    ups_audit_ajax_guard("edit_posts");
    $accounts = get_posts([
        "post_type" => "crm_google_account",
        "posts_per_page" => -1,
        "post_status" => ["publish", "draft"],
    ]);
    $done = 0;
    foreach ($accounts as $acc) {
        if (!($acc instanceof WP_Post)) {
            continue;
        }
        $aid = (int) $acc->ID;
        $cache = [
            "ga4" => ups_audit_fetch_ga4_resources($aid),
            "gsc" => ups_audit_fetch_gsc_resources($aid),
            "ads" => ups_audit_fetch_ads_resources($aid),
        ];
        update_post_meta($aid, "_ups_gacc_resources_cache", $cache);
        update_post_meta($aid, "_ups_gacc_last_sync_at", current_time("mysql"));
        $done++;
    }
    wp_send_json_success(["refreshed" => $done]);
});

