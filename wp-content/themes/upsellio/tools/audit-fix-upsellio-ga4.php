<?php

if (!defined("ABSPATH")) {
    exit(1);
}

$candidates = [
    ["ext" => "535285648", "title" => "upsellio.pl - GA4"],
    ["ext" => "535308058", "title" => "Upsellio"],
];
$best_rid = 0;
$best_sessions = -1;
foreach ($candidates as $c) {
    $rid = function_exists("ups_audit_find_imported_resource_id")
        ? ups_audit_find_imported_resource_id(962, "ga4", $c["ext"])
        : 0;
    if ($rid <= 0) {
        $rid = (int) wp_insert_post([
            "post_type" => "crm_audit_resource",
            "post_title" => $c["title"],
            "post_status" => "publish",
        ]);
        update_post_meta($rid, "_ups_resource_type", "ga4");
        update_post_meta($rid, "_ups_resource_external_id", $c["ext"]);
        update_post_meta($rid, "_ups_resource_google_account_id", 962);
    }
    update_post_meta($rid, "_ups_resource_client_id", 968);
    ups_audit_sync_ga4_resource((int) $rid, 30);
    $cache = get_post_meta($rid, "_ups_resource_data_cache", true);
    $sessions = (int) (($cache["summary"]["sessions"] ?? 0));
    echo "#{$rid} {$c['ext']} sessions={$sessions}\n";
    if ($sessions > $best_sessions) {
        $best_sessions = $sessions;
        $best_rid = (int) $rid;
    }
}
// Unmap weaker duplicate from client if two mapped
foreach ($candidates as $c) {
    $rid = ups_audit_find_imported_resource_id(962, "ga4", $c["ext"]);
    if ($rid > 0 && $rid !== $best_rid) {
        update_post_meta($rid, "_ups_resource_client_id", 0);
        echo "unmapped duplicate #{$rid}\n";
    }
}
update_post_meta($best_rid, "_ups_resource_client_id", 968);
$d = ups_audit_aggregate_client_data(968, 30, 0, false);
echo "upsellio KPI sessions=" . (int) ($d["ga4_sessions"] ?? 0) . " gsc=" . (int) ($d["gsc_clicks"] ?? 0) . "\n";
