<?php
if (!defined("ABSPATH")) {
    exit(1);
}
$agg = ups_audit_aggregate_client_data(965, 30, 0, true);
$ts = (array) (($agg["timeseries"]["ga4_sessions"] ?? []));
echo "ga4 ts days: " . count($ts) . "\n";
echo "json bytes: " . strlen(wp_json_encode($ts)) . "\n";
