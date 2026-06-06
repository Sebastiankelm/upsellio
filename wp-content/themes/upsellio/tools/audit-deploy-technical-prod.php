<?php
/**
 * Prod: sync GSC (indeksacja) + podgląd technical dla wszystkich profili audytu.
 * wp eval-file wp-content/themes/upsellio/tools/audit-deploy-technical-prod.php
 */

if (!defined("ABSPATH")) {
    exit(1);
}

echo "=== Upsellio audit technical deploy check ===\n";
echo "indexation_sync=" . (function_exists("ups_audit_gsc_resolve_indexation_for_sync") ? "yes" : "no") . "\n";
echo "pagespeed_limit=" . (function_exists("ups_audit_pagespeed_daily_remaining") ? ups_audit_pagespeed_daily_remaining() : "?") . "\n";

$client_ids = function_exists("ups_audit_collect_profile_client_ids")
    ? ups_audit_collect_profile_client_ids()
    : [];

foreach ($client_ids as $cid) {
    $cid = (int) $cid;
    $client = get_post($cid);
    if (!($client instanceof WP_Post)) {
        continue;
    }
    echo "\n--- {$client->post_title} (#{$cid}) ---\n";

    foreach (ups_audit_get_client_resources($cid) as $res) {
        if (!($res instanceof WP_Post)) {
            continue;
        }
        $rid = (int) $res->ID;
        $type = (string) get_post_meta($rid, "_ups_resource_type", true);
        if ($type !== "gsc") {
            continue;
        }
        echo "GSC sync #{$rid} " . get_post_meta($rid, "_ups_resource_external_id", true) . "...\n";
        ups_audit_sync_gsc_resource($rid, 30, false);
        $cache = get_post_meta($rid, "_ups_resource_data_cache", true);
        if (is_array($cache) && !empty($cache["indexation"])) {
            $idx = $cache["indexation"];
            echo "  indexation: " . (int) ($idx["indexed"] ?? 0) . "/" . (int) ($idx["submitted"] ?? 0)
                . " ratio=" . (int) ($idx["ratio"] ?? 0) . "% source=" . ($idx["source"] ?? "") . "\n";
        } else {
            echo "  indexation: brak\n";
        }
    }

    $tech = ups_audit_client_technical_signals($cid, false);
    $idx = (array) ($tech["indexation"] ?? []);
    $cwv = (array) ($tech["cwv"] ?? []);
    echo "  technical idx: " . (int) ($idx["indexed"] ?? 0) . "/" . (int) ($idx["submitted"] ?? 0)
        . " | cwv=" . ($cwv["status"] ?? "?") . "\n";
    echo "  dashboard: " . (function_exists("upsellio_crm_url") ? upsellio_crm_url("ca-dashboard", ["cid" => $cid]) : "") . "\n";
}

echo "\n=== Command Center ===\n";
if (function_exists("ups_audit_build_command_center")) {
    $cc = ups_audit_build_command_center(30);
    echo "clients=" . count((array) ($cc["clients"] ?? [])) . " red=" . (int) (($cc["summary"]["red"] ?? 0)) . "\n";
    echo "url: " . (function_exists("upsellio_crm_url") ? upsellio_crm_url("ca-command-center") : "") . "\n";
}

echo "\nDone.\n";
