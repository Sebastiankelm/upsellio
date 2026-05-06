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
    $url = ups_audit_start_oauth_connect($label);
    wp_send_json_success(["redirect_url" => $url]);
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
        wp_send_json_success(["resource_id" => (int) $existing[0]->ID, "already_exists" => true]);
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
    wp_send_json_success(["resource_id" => (int) $resource_id]);
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

add_action("wp_ajax_ups_audit_map_to_client", function () {
    ups_audit_ajax_guard("edit_posts");
    $client_id = isset($_POST["client_id"]) ? (int) $_POST["client_id"] : 0;
    $resources_json = isset($_POST["resources"]) ? wp_unslash($_POST["resources"]) : "[]";
    $resources = json_decode((string) $resources_json, true);
    if ($client_id <= 0 || get_post_type($client_id) !== "crm_client" || !is_array($resources)) {
        wp_send_json_error(["msg" => "invalid_payload"], 400);
    }
    foreach ($resources as $row) {
        if (!is_array($row)) {
            continue;
        }
        $rid = (int) ($row["resource_id"] ?? 0);
        if ($rid > 0 && get_post_type($rid) === "crm_audit_resource") {
            update_post_meta($rid, "_ups_resource_client_id", $client_id);
        }
    }
    wp_send_json_success(["ok" => true]);
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
    if (!in_array($type, ["monthly", "audit", "plan", "comparison", "brief"], true)) {
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
    $autoload = get_template_directory() . "/vendor/autoload.php";
    if (!file_exists($autoload)) {
        wp_send_json_error(["msg" => "dompdf_not_installed"], 500);
    }
    require_once $autoload;
    if (!class_exists("\\Dompdf\\Dompdf")) {
        wp_send_json_error(["msg" => "dompdf_missing_class"], 500);
    }
    $pdf = new \Dompdf\Dompdf();
    $pdf->loadHtml((string) $report->post_content);
    $pdf->setPaper("A4", "portrait");
    $pdf->render();
    $upload = wp_upload_dir();
    $file = "audit-report-" . $report_id . ".pdf";
    $path = trailingslashit((string) $upload["path"]) . $file;
    file_put_contents($path, $pdf->output(), LOCK_EX);
    wp_send_json_success(["url" => trailingslashit((string) $upload["url"]) . $file]);
});

add_action("wp_ajax_ups_audit_dashboard_data", function () {
    ups_audit_ajax_guard("edit_posts");
    $client_id = isset($_POST["client_id"]) ? (int) $_POST["client_id"] : 0;
    $window = isset($_POST["window"]) ? (int) $_POST["window"] : 30;
    $window = in_array($window, [7, 14, 30, 60, 90], true) ? $window : 30;
    $current = ups_audit_aggregate_client_data($client_id, $window, 0);
    $previous = ups_audit_aggregate_client_data($client_id, $window, $window);
    wp_send_json_success(["current" => $current, "previous" => $previous]);
});

