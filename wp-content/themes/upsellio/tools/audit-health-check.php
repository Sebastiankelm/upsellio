<?php
/**
 * wp eval-file wp-content/themes/upsellio/tools/audit-health-check.php
 */

if (!defined("ABSPATH")) {
    exit(1);
}

echo "WP OK\n";
$cid = 965;
try {
    $agg = ups_audit_aggregate_client_data($cid, 30, 0, true);
    echo "aggregate_ok sessions=" . (int) ($agg["ga4_sessions"] ?? 0) . "\n";
    $tech = ups_audit_client_technical_signals($cid, false);
    echo "technical_ok\n";
    $cc = ups_audit_build_command_center(30);
    echo "command_center_ok clients=" . count((array) ($cc["clients"] ?? [])) . "\n";
} catch (Throwable $e) {
    echo "FATAL: " . $e->getMessage() . "\n";
    echo $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
echo "all_ok\n";
