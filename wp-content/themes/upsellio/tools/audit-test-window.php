<?php
/**
 * wp eval-file wp-content/themes/upsellio/tools/audit-test-window.php [days]
 */

if (!defined("ABSPATH")) {
    exit(1);
}

$cid = 965;
$days = isset($args[0]) ? (int) $args[0] : 90;
$agg = ups_audit_aggregate_client_data($cid, $days, 0, false);
echo "days={$days}\n";
echo "gsc_clicks=" . (int) ($agg["gsc_clicks"] ?? 0) . "\n";
echo "ga4_sessions=" . (int) ($agg["ga4_sessions"] ?? 0) . "\n";
echo "ts_gsc=" . count((array) ($agg["timeseries"]["gsc_clicks"] ?? [])) . "\n";
echo "ts_ga4=" . count((array) ($agg["timeseries"]["ga4_sessions"] ?? [])) . "\n";
echo "ts_ads=" . count((array) ($agg["timeseries"]["ads_cost"] ?? [])) . "\n";
echo "gsc_prev=" . (int) ($agg["gsc_clicks_prev"] ?? 0) . "\n";
echo "ga4_prev=" . (int) ($agg["ga4_sessions_prev"] ?? 0) . "\n";
$cache = get_post_meta(964, "_ups_resource_data_cache", true);
echo "gsc_cache_period=" . (int) (is_array($cache) ? ($cache["period_days"] ?? 0) : 0) . "\n";
echo "gsc_cache_ts=" . count((array) (is_array($cache) ? ($cache["timeseries"] ?? []) : [])) . "\n";
