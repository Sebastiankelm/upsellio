<?php
/**
 * wtapes — sync GSC (indeksacja sitemap) + podgląd technical signals.
 * wp eval-file wp-content/themes/upsellio/tools/audit-sync-wtapes-technical.php
 */

if (!defined("ABSPATH")) {
    exit(1);
}

$client_id = 965;
echo "=== Sync GSC resources for client #{$client_id} ===\n";
foreach (ups_audit_get_client_resources($client_id) as $res) {
    if (!($res instanceof WP_Post)) {
        continue;
    }
    $rid = (int) $res->ID;
    $type = get_post_meta($rid, "_ups_resource_type", true);
    if ($type !== "gsc") {
        continue;
    }
    echo "GSC #{$rid} " . get_post_meta($rid, "_ups_resource_external_id", true) . "\n";
    ups_audit_sync_gsc_resource($rid, 30, false);
    $cache = get_post_meta($rid, "_ups_resource_data_cache", true);
    if (is_array($cache) && !empty($cache["indexation"])) {
        echo "  indexation: " . json_encode($cache["indexation"]) . "\n";
    } else {
        echo "  indexation: brak (sitemap pusta lub błąd API)\n";
        echo "  error: " . ($cache["error"] ?? "") . "\n";
    }
}

echo "\n=== Technical signals ===\n";
$tech = ups_audit_client_technical_signals($client_id, false);
echo json_encode($tech, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
