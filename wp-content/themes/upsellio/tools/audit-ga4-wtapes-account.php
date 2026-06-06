<?php
/**
 * Diagnostyka GA4 dla wtapes — konto 351402842, zasób #971, OAuth #963.
 * wp eval-file wp-content/themes/upsellio/tools/audit-ga4-wtapes-account.php
 */

if (!defined("ABSPATH")) {
    exit(1);
}

$gacc = 963;
$target_account = "351402842";
$client = 965;
$resource_id = 971;
$old_property = "484708285";

echo "=== GA4 tree (OAuth #{$gacc}) ===\n";
$tree = ups_audit_fetch_ga4_resources($gacc);
$pick_id = "";
$pick_name = "";
$found_account = false;

foreach ((array) $tree as $acc) {
    if (!is_array($acc)) {
        continue;
    }
    $aid = (string) ($acc["account_id"] ?? "");
    $aname = (string) ($acc["account_name"] ?? "");
    echo "Account {$aid} — {$aname}\n";
    if ($aid === $target_account) {
        $found_account = true;
    }
    foreach ((array) ($acc["properties"] ?? []) as $prop) {
        if (!is_array($prop)) {
            continue;
        }
        $pid = (string) ($prop["id"] ?? "");
        $pname = (string) ($prop["display_name"] ?? "");
        echo "  property {$pid} — {$pname}\n";
        if ($aid === $target_account && $pick_id === "") {
            $pick_id = $pid;
            $pick_name = $pname;
        }
        if (stripos($pname, "wtapes") !== false || stripos($pname, "tape") !== false) {
            $pick_id = $pid;
            $pick_name = $pname;
        }
    }
}

echo "\nAccount {$target_account} visible: " . ($found_account ? "yes" : "no") . "\n";
echo "Pick property: " . ($pick_id !== "" ? "{$pick_id} ({$pick_name})" : "(brak)") . "\n";

foreach ([$target_account, $old_property, $pick_id] as $test_pid) {
    if ($test_pid === "") {
        continue;
    }
    echo "\n=== Test Data API property {$test_pid} ===\n";
    $raw = ups_audit_with_account_oauth($gacc, static function ($oauth) use ($test_pid) {
        return ups_audit_ga4_fetch($test_pid, $oauth, 30);
    });
    if (is_wp_error($raw)) {
        echo "WP_Error: " . $raw->get_error_message() . "\n";
        continue;
    }
    if (isset($raw["error"])) {
        echo "API error: " . json_encode($raw["error"]) . "\n";
        continue;
    }
    $rows = is_array($raw["rows"] ?? null) ? $raw["rows"] : [];
    echo "rows=" . count($rows) . " OK\n";
}

if ($pick_id !== "" && $pick_id !== $old_property) {
    update_post_meta($resource_id, "_ups_resource_external_id", $pick_id);
    wp_update_post([
        "ID" => $resource_id,
        "post_title" => $pick_name !== "" ? $pick_name : "GA Wtapes",
    ]);
    echo "\nUpdated resource #{$resource_id} -> {$pick_id}\n";
    ups_audit_sync_ga4_resource($resource_id, 30, false);
    $cache = get_post_meta($resource_id, "_ups_resource_data_cache", true);
    echo "sync error: " . (is_array($cache) ? ($cache["error"] ?? "") : "?") . "\n";
    if (is_array($cache)) {
        echo "sessions: " . (int) ($cache["summary"]["sessions"] ?? 0) . "\n";
    }
}

$agg = ups_audit_aggregate_client_data($client, 30, 0, false);
echo "\nAggregate ga4_sessions=" . (int) ($agg["ga4_sessions"] ?? 0) . "\n";
