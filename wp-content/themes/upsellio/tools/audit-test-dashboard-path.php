<?php
/**
 * wp eval-file wp-content/themes/upsellio/tools/audit-test-dashboard-path.php [days]
 */

if (!defined("ABSPATH")) {
    exit(1);
}

$cid = 965;
$days = isset($args[0]) ? (int) $args[0] : 60;

echo "path test days={$days}\n";

$current = ups_audit_aggregate_client_data($cid, $days, 0, false);
echo "aggregate_ok=" . (is_array($current) && $current !== [] ? "1" : "0") . "\n";
echo "ga4_sessions=" . (int) ($current["ga4_sessions"] ?? 0) . "\n";

if ($current !== [] && function_exists("ups_audit_attach_dashboard_extras")) {
    $current = ups_audit_attach_dashboard_extras($current, $cid, $days);
    echo "extras_ok=1\n";
    echo "benchmark_clients=" . (int) (($current["benchmark"]["clients"] ?? 0)) . "\n";
}

echo "intelligence=" . (isset($current["intelligence"]) ? "1" : "0") . "\n";
echo "health=" . (int) ($current["health_score"] ?? 0) . "\n";
