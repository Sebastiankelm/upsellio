<?php
/**
 * wtapes: GA4/GSC → konto Google #962, Ads → #963.
 * wp eval-file wp-content/themes/upsellio/tools/audit-fix-wtapes-gacc962.php
 */

if (!defined("ABSPATH")) {
    exit(1);
}

$client = 965;
$gacc_ga4_gsc = 962;
$gacc_ads = 963;
$ga4_property = "484708285";
$gsc_site = "https://wtapes.pl/";

echo "=== Remap wtapes GA4/GSC to gacc #{$gacc_ga4_gsc} ===\n";

foreach (ups_audit_get_client_resources($client) as $res) {
    if (!($res instanceof WP_Post)) {
        continue;
    }
    $rid = (int) $res->ID;
    $type = (string) get_post_meta($rid, "_ups_resource_type", true);

    if ($type === "ga4") {
        update_post_meta($rid, "_ups_resource_google_account_id", $gacc_ga4_gsc);
        update_post_meta($rid, "_ups_resource_external_id", $ga4_property);
        wp_update_post(["ID" => $rid, "post_title" => "GA Wtapes"]);
        echo "GA4 #{$rid} -> gacc {$gacc_ga4_gsc}, property {$ga4_property}\n";
        ups_audit_sync_ga4_resource($rid, 30, false);
        $cache = get_post_meta($rid, "_ups_resource_data_cache", true);
        echo "  error: " . (is_array($cache) ? ($cache["error"] ?? "") : "?") . "\n";
        echo "  sessions: " . (is_array($cache) ? (int) ($cache["summary"]["sessions"] ?? 0) : 0) . "\n";
    }

    if ($type === "gsc") {
        update_post_meta($rid, "_ups_resource_google_account_id", $gacc_ga4_gsc);
        update_post_meta($rid, "_ups_resource_external_id", $gsc_site);
        wp_update_post(["ID" => $rid, "post_title" => $gsc_site]);
        echo "GSC #{$rid} -> gacc {$gacc_ga4_gsc}, site {$gsc_site}\n";
        ups_audit_sync_gsc_resource($rid, 30, false);
        $cache = get_post_meta($rid, "_ups_resource_data_cache", true);
        echo "  error: " . (is_array($cache) ? ($cache["error"] ?? "") : "?") . "\n";
        echo "  clicks: " . (is_array($cache) ? (int) ($cache["summary"]["clicks"] ?? 0) : 0) . "\n";
    }

    if ($type === "ads") {
        $cur = (int) get_post_meta($rid, "_ups_resource_google_account_id", true);
        if ($cur !== $gacc_ads) {
            update_post_meta($rid, "_ups_resource_google_account_id", $gacc_ads);
            echo "Ads #{$rid} -> gacc {$gacc_ads}\n";
        } else {
            echo "Ads #{$rid} already on gacc {$gacc_ads}\n";
        }
    }
}

echo "\n=== Aggregate ===\n";
$agg = ups_audit_aggregate_client_data($client, 30, 0, false);
echo "ga4_sessions=" . (int) ($agg["ga4_sessions"] ?? 0)
    . " gsc_clicks=" . (int) ($agg["gsc_clicks"] ?? 0)
    . " ads_cost=" . round((float) ($agg["ads_cost"] ?? 0), 2)
    . " health=" . (int) ($agg["health_score"] ?? 0) . "\n";
