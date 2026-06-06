<?php
/**
 * Sync zasobów Clarity (mapowanych do klienta lub wszystkich).
 *
 * php wp-content/themes/upsellio/tools/audit-sync-clarity.php [client_id]
 * Przykład: php .../audit-sync-clarity.php 965
 */

define("WP_USE_THEMES", false);
require dirname(__DIR__, 4) . "/wp-load.php";

$client_id = isset($argv[1]) ? (int) $argv[1] : 0;

if (!function_exists("ups_audit_sync_clarity_resource")) {
    fwrite(STDERR, "Brak modułu client-audit-clarity.php\n");
    exit(1);
}

$resources = [];
if ($client_id > 0) {
    $resources = function_exists("ups_audit_get_client_resources")
        ? ups_audit_get_client_resources($client_id, "clarity")
        : [];
    echo "Klient #{$client_id}: " . count($resources) . " zasobów Clarity\n";
} else {
    $resources = get_posts([
        "post_type" => "crm_audit_resource",
        "posts_per_page" => -1,
        "post_status" => ["publish", "draft"],
        "meta_query" => [
            ["key" => "_ups_resource_type", "value" => "clarity"],
            ["key" => "_ups_resource_client_id", "value" => 0, "compare" => ">", "type" => "NUMERIC"],
        ],
    ]);
    echo "Wszystkie zmapowane Clarity: " . count($resources) . "\n";
}

if ($resources === []) {
    echo "Brak zasobów do sync.\n";
    exit(0);
}

foreach ($resources as $p) {
    if (!($p instanceof WP_Post)) {
        continue;
    }
    $rid = (int) $p->ID;
    $cid = (int) get_post_meta($rid, "_ups_resource_client_id", true);
    echo "\n--- #{$rid} " . $p->post_title . " (client {$cid}) ---\n";
    ups_audit_sync_clarity_resource($rid, 30, false);
    $cache = get_post_meta($rid, "_ups_resource_data_cache", true);
    if (!is_array($cache)) {
        echo "Brak cache.\n";
        continue;
    }
    $err = trim((string) ($cache["error"] ?? ""));
    $sum = is_array($cache["summary"] ?? null) ? $cache["summary"] : [];
    echo "error: " . ($err !== "" ? $err : "(brak)") . "\n";
    echo "sessions: " . (int) ($sum["sessions"] ?? 0) . ", users: " . (int) ($sum["users"] ?? 0);
    echo ", dead: " . (int) ($sum["dead_clicks"] ?? 0) . ", api today: " . (int) ($cache["api_usage_today"] ?? 0) . "/10\n";
}

if ($client_id > 0 && function_exists("ups_audit_aggregate_client_data")) {
    $agg = ups_audit_aggregate_client_data($client_id, 30, 0, false);
    echo "\nAgregacja klienta #{$client_id}: clarity_sessions=" . (int) ($agg["clarity_sessions"] ?? 0) . "\n";
    if (!empty($agg["clarity_errors"])) {
        foreach ((array) $agg["clarity_errors"] as $e) {
            echo "  ! " . $e . "\n";
        }
    }
}

echo "\nDone.\n";
