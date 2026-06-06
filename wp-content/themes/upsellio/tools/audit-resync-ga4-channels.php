<?php
if (!defined("ABSPATH")) {
    exit(1);
}

foreach ([964, 967, 969, 971, 972, 973] as $rid) {
    if (get_post_type($rid) !== "crm_audit_resource") {
        continue;
    }
    $type = (string) get_post_meta($rid, "_ups_resource_type", true);
    if ($type === "ga4" && function_exists("ups_audit_sync_ga4_resource")) {
        ups_audit_sync_ga4_resource((int) $rid, 30, false);
        echo "GA4 #{$rid} OK\n";
    }
}

$cid = 965;
$site = trim((string) get_post_meta($cid, "_ups_client_website", true));
if ($site !== "" && !preg_match("#^https?://#i", $site)) {
    $site = "https://" . $site;
}
delete_transient("ups_audit_cwv_" . md5($site));

if (function_exists("ups_audit_client_technical_signals")) {
    $t = ups_audit_client_technical_signals($cid);
    echo "Indexation: " . wp_json_encode($t["indexation"] ?? []) . "\n";
    echo "CWV: " . wp_json_encode($t["cwv"] ?? []) . "\n";
}

if (function_exists("upsellio_analytics_channel_ltv_for_client")) {
    $ltv = upsellio_analytics_channel_ltv_for_client($cid, 30);
    echo "LTV rows: " . count($ltv["rows"] ?? []) . "\n";
    foreach (array_slice($ltv["rows"] ?? [], 0, 5) as $r) {
        echo "  " . ($r["channel"] ?? "") . " ses=" . ($r["sessions"] ?? 0) . " leads=" . ($r["leads"] ?? 0) . "\n";
    }
}

if (function_exists("ups_audit_aggregate_client_data")) {
    $a = ups_audit_aggregate_client_data($cid, 30, 0, false);
    $ch_sum = 0;
    foreach ((array) ($a["channels"] ?? []) as $c) {
        $ch_sum += (int) ($c["sessions"] ?? 0);
    }
    echo "Website meta: " . get_post_meta($cid, "_ups_client_website", true) . "\n";
    echo "KPI sessions=" . (int) ($a["ga4_sessions"] ?? 0) . " channel_sum={$ch_sum}\n";
}
