<?php

if (!defined("ABSPATH")) {
    exit(1);
}

ups_audit_sync_ga4_resource(971, 30, false);
ups_audit_sync_ads_resource(975, 30, false);

$ga4 = get_post_meta(971, "_ups_resource_data_cache", true);
echo "GA4 summary: " . json_encode($ga4["summary"] ?? [], JSON_UNESCAPED_UNICODE) . "\n";
echo "Attribution: " . json_encode($ga4["attribution"] ?? [], JSON_UNESCAPED_UNICODE) . "\n";
echo "Top events:\n";
foreach (array_slice((array) ($ga4["events_breakdown"] ?? []), 0, 8) as $ev) {
    if (!is_array($ev)) {
        continue;
    }
    echo "  " . ($ev["event"] ?? "?") . " " . ($ev["count"] ?? 0) . " [" . ($ev["kind"] ?? "") . "]\n";
}

$agg = ups_audit_aggregate_client_data(965, 30, 0, false);
echo "\nAggregate: sessions=" . (int) ($agg["ga4_sessions"] ?? 0)
    . " conv_macro=" . (int) ($agg["ga4_conversions"] ?? 0)
    . " conv_all=" . (int) ($agg["ga4_conversions_all"] ?? 0)
    . " not_set=" . (float) ($agg["ga4_not_set_pct"] ?? 0) . "%"
    . " health=" . (int) ($agg["health_score"] ?? 0) . "\n";
foreach ((array) ($agg["campaigns"] ?? []) as $c) {
    if (!is_array($c)) {
        continue;
    }
    echo "  camp: " . ($c["name"] ?? "?")
        . " cost=" . ($c["cost"] ?? 0)
        . " conv=" . ($c["conversions"] ?? 0)
        . " cpa=" . ($c["cpa"] ?? 0) . "\n";
}
