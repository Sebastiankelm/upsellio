<?php

if (!defined("ABSPATH")) {
    exit(1);
}

$map = [
    965 => ["account_id" => 963, "ext" => "484708285", "title" => "GA Wtapes"],
    966 => ["account_id" => 963, "ext" => "538163091", "title" => "Lapidaria"],
];

foreach ($map as $client_id => $row) {
    $aid = (int) $row["account_id"];
    $ext = (string) $row["ext"];
    $title = (string) $row["title"];
    $rid = function_exists("ups_audit_find_imported_resource_id")
        ? ups_audit_find_imported_resource_id($aid, "ga4", $ext)
        : 0;
    if ($rid <= 0) {
        $rid = (int) wp_insert_post([
            "post_type" => "crm_audit_resource",
            "post_title" => $title,
            "post_status" => "publish",
        ]);
        update_post_meta($rid, "_ups_resource_type", "ga4");
        update_post_meta($rid, "_ups_resource_external_id", $ext);
        update_post_meta($rid, "_ups_resource_display_name", $title);
        update_post_meta($rid, "_ups_resource_google_account_id", $aid);
        update_post_meta($rid, "_ups_resource_imported_at", current_time("mysql"));
        echo "created GA4 #{$rid}\n";
    }
    update_post_meta($rid, "_ups_resource_client_id", (int) $client_id);
    echo "client #{$client_id} <- GA4 #{$rid} ({$ext})\n";
    if (function_exists("ups_audit_sync_ga4_resource")) {
        ups_audit_sync_ga4_resource((int) $rid, 30);
        $cache = get_post_meta($rid, "_ups_resource_data_cache", true);
        $sum = is_array($cache) && is_array($cache["summary"] ?? null) ? $cache["summary"] : [];
        echo "  sessions=" . (int) ($sum["sessions"] ?? 0) . " err=" . (string) ($cache["error"] ?? "") . "\n";
    }
}

// upsellio GA4
$rid = 970;
if (get_post_type($rid) === "crm_audit_resource" && function_exists("ups_audit_sync_ga4_resource")) {
    ups_audit_sync_ga4_resource($rid, 30);
    $cache = get_post_meta($rid, "_ups_resource_data_cache", true);
    $sum = is_array($cache) && is_array($cache["summary"] ?? null) ? $cache["summary"] : [];
    echo "upsellio GA4 #{$rid} sessions=" . (int) ($sum["sessions"] ?? 0) . " err=" . (string) ($cache["error"] ?? "") . "\n";
}

foreach ([965, 966, 968] as $cid) {
    $d = ups_audit_aggregate_client_data($cid, 30, 0, false);
    echo "KPI #{$cid}: sesje=" . (int) ($d["ga4_sessions"] ?? 0) . " gsc=" . (int) ($d["gsc_clicks"] ?? 0) . "\n";
}
