<?php
/**
 * wp eval-file wp-content/themes/upsellio/tools/audit-debug-lapidaria.php
 */

if (!defined("ABSPATH")) {
    exit(1);
}

echo "=== Ads customers gacc 963 ===\n";
foreach (ups_audit_fetch_ads_resources(963) as $a) {
    if (!is_array($a)) {
        continue;
    }
    $cid = (string) ($a["customer_id"] ?? "");
    if ($cid === "" || $cid === "1333330388") {
        continue;
    }
    echo ($a["name"] ?? "?") . " | " . $cid . "\n";
}

echo "\n=== Client 966 resources ===\n";
foreach (ups_audit_get_client_resources(966) as $res) {
    if (!($res instanceof WP_Post)) {
        continue;
    }
    $rid = (int) $res->ID;
    $type = get_post_meta($rid, "_ups_resource_type", true);
    $cache = get_post_meta($rid, "_ups_resource_data_cache", true);
    echo "#{$rid} {$type} gacc=" . get_post_meta($rid, "_ups_resource_google_account_id", true) . "\n";
    if (is_array($cache)) {
        echo "  error=" . ($cache["error"] ?? "") . "\n";
        echo "  summary=" . json_encode($cache["summary"] ?? []) . "\n";
        if ($type === "ga4" && !empty($cache["events"])) {
            echo "  events=" . json_encode($cache["events"]) . "\n";
        }
    }
}

echo "\n=== GSC raw sync test #967 ===\n";
ups_audit_sync_gsc_resource(967, 30, false);
$gsc = get_post_meta(967, "_ups_resource_data_cache", true);
if (is_array($gsc)) {
    echo "error=" . ($gsc["error"] ?? "") . "\n";
    echo "summary=" . json_encode($gsc["summary"] ?? []) . "\n";
    echo "daily_rows=" . count($gsc["daily"] ?? []) . "\n";
    echo "top_queries=" . count($gsc["top_queries"] ?? []) . "\n";
}
