<?php

if (!defined("ABSPATH")) {
    exit(1);
}

ups_audit_sync_ga4_resource(971, 30, false);

foreach ([975 => "ads", 971 => "ga4", 964 => "gsc"] as $rid => $label) {
    $cache = get_post_meta($rid, "_ups_resource_data_cache", true);
    echo "=== #{$rid} {$label} ===\n";
    if (!is_array($cache)) {
        echo "no cache\n\n";
        continue;
    }
    echo "error: " . ($cache["error"] ?? "") . "\n";
    echo "summary: " . json_encode($cache["summary"] ?? []) . "\n";
    if ($label === "ads" && !empty($cache["campaigns"])) {
        foreach ((array) $cache["campaigns"] as $camp) {
            if (!is_array($camp)) {
                continue;
            }
            echo "  camp: " . ($camp["name"] ?? "?")
                . " cost=" . ($camp["cost_pln"] ?? $camp["cost"] ?? 0)
                . " clicks=" . ($camp["clicks"] ?? 0) . "\n";
        }
    }
    echo "\n";
}

$cache975 = get_post_meta(975, "_ups_resource_data_cache", true);
$ts = is_array($cache975) ? (array) ($cache975["timeseries"] ?? []) : [];
echo "ads timeseries days=" . count($ts) . "\n";
if (!empty($ts)) {
    $keys = array_keys($ts);
    echo "  first=" . reset($keys) . " last=" . end($keys) . "\n";
}

$agg = ups_audit_aggregate_client_data(965, 30, 0, false);
$tsAds = (array) (($agg["timeseries"]["ads_cost"] ?? []));
echo "aggregate: ga4=" . (int) ($agg["ga4_sessions"] ?? 0)
    . " gsc=" . (int) ($agg["gsc_clicks"] ?? 0)
    . " ads=" . round((float) ($agg["ads_cost"] ?? 0), 2)
    . " ts_ads_days=" . count($tsAds)
    . " ads_conv=" . (float) ($agg["ads_conversions"] ?? 0) . "\n";
