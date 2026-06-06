<?php
/**
 * Przywraca cache Clarity z _previous gdy limit API wyczerpał sync.
 * wp eval-file wp-content/themes/upsellio/tools/audit-restore-clarity-cache.php
 */

if (!defined("ABSPATH")) {
    exit(1);
}

$rid = 974;
$client_id = 965;
$prev = get_post_meta($rid, "_ups_resource_data_cache_previous", true);
if (!is_array($prev) || empty($prev["summary"])) {
    echo "no_previous_cache\n";
    exit(1);
}

$prev["error"] = "Clarity: cache przywrócony — limit API (sync jutro).";
update_post_meta($rid, "_ups_resource_data_cache", $prev);
update_post_meta($rid, "_ups_resource_last_data_sync", current_time("mysql"));

$agg = ups_audit_aggregate_client_data($client_id, 30, 0, true);
echo "clarity_sessions=" . (int) ($agg["clarity_sessions"] ?? 0) . "\n";
echo "clarity_dead=" . (int) ($agg["clarity_dead_clicks"] ?? 0) . "\n";
foreach (array_slice((array) ($agg["clarity_by_device"] ?? []), 0, 5) as $d) {
    if (!is_array($d)) {
        continue;
    }
    echo "device " . ($d["label"] ?? "?")
        . ": s=" . (int) ($d["sessions"] ?? 0)
        . " d=" . (int) ($d["dead_clicks"] ?? 0)
        . " r=" . (int) ($d["rage_clicks"] ?? 0) . "\n";
}
echo "restored_ok\n";
