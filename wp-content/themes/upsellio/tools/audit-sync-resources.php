<?php

if (!defined("ABSPATH")) {
    exit(1);
}

$ids = isset($GLOBALS["audit_sync_ids"]) && is_array($GLOBALS["audit_sync_ids"])
    ? $GLOBALS["audit_sync_ids"]
    : [964, 967, 969];

foreach ($ids as $rid) {
    $rid = (int) $rid;
    if ($rid <= 0 || get_post_type($rid) !== "crm_audit_resource") {
        continue;
    }
    $type = (string) get_post_meta($rid, "_ups_resource_type", true);
    echo "Sync #{$rid} ({$type})... ";
    if ($type === "gsc" && function_exists("ups_audit_sync_gsc_resource")) {
        ups_audit_sync_gsc_resource($rid, 30);
    } elseif ($type === "ga4" && function_exists("ups_audit_sync_ga4_resource")) {
        ups_audit_sync_ga4_resource($rid, 30);
    } elseif ($type === "ads" && function_exists("ups_audit_sync_ads_resource")) {
        ups_audit_sync_ads_resource($rid, 30);
    }
    $cache = get_post_meta($rid, "_ups_resource_data_cache", true);
    $summary = is_array($cache) && function_exists("ups_audit_cache_summary")
        ? ups_audit_cache_summary($cache)
        : [];
    $clicks = (int) ($summary["gsc_clicks"] ?? $summary["clicks"] ?? 0);
    echo "clicks={$clicks}\n";
}
