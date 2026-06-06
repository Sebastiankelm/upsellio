<?php
/**
 * Naprawa zasobów GA4/GSC dla wtapes (#965) — właściwe property z konta #963.
 * wp eval-file wp-content/themes/upsellio/tools/audit-fix-wtapes-ga4-gsc.php
 */

if (!defined("ABSPATH")) {
    exit(1);
}

$gacc = 963;
$client = 965;

echo "=== GSC sites (gacc #{$gacc}) ===\n";
$gsc_list = ups_audit_fetch_gsc_resources($gacc);
foreach ((array) $gsc_list as $site) {
    if (!is_array($site)) {
        continue;
    }
    echo "  " . ($site["site_url"] ?? "?") . " verified=" . (($site["is_verified"] ?? false) ? "yes" : "no") . "\n";
}

echo "\n=== GA4 properties (gacc #{$gacc}) ===\n";
$ga4_tree = ups_audit_fetch_ga4_resources($gacc);
foreach ((array) $ga4_tree as $acc) {
    if (!is_array($acc)) {
        continue;
    }
    echo "Account: " . ($acc["account_name"] ?? "") . " (" . ($acc["account_id"] ?? "") . ")\n";
    foreach ((array) ($acc["properties"] ?? []) as $prop) {
        if (!is_array($prop)) {
            continue;
        }
        echo "  property " . ($prop["id"] ?? "?") . " — " . ($prop["display_name"] ?? "") . "\n";
    }
}

$gsc_pick = "";
foreach ((array) $gsc_list as $site) {
    if (!is_array($site)) {
        continue;
    }
    $url = (string) ($site["site_url"] ?? "");
    if ($url === "") {
        continue;
    }
    if (stripos($url, "wtapes") !== false) {
        $gsc_pick = $url;
        break;
    }
}
if ($gsc_pick === "" && !empty($gsc_list[0]["site_url"])) {
    $gsc_pick = (string) $gsc_list[0]["site_url"];
}

$ga4_pick = "";
$ga4_name = "";
foreach ((array) $ga4_tree as $acc) {
    if (!is_array($acc)) {
        continue;
    }
    foreach ((array) ($acc["properties"] ?? []) as $prop) {
        if (!is_array($prop)) {
            continue;
        }
        $name = (string) ($prop["display_name"] ?? "");
        $id = (string) ($prop["id"] ?? "");
        if ($id === "") {
            continue;
        }
        if (stripos($name, "wtapes") !== false || stripos($name, "tape") !== false) {
            $ga4_pick = $id;
            $ga4_name = $name;
            break 2;
        }
    }
}

echo "\n=== Auto-pick ===\n";
echo "GSC: " . ($gsc_pick !== "" ? $gsc_pick : "(brak)") . "\n";
echo "GA4: " . ($ga4_pick !== "" ? $ga4_pick . " " . $ga4_name : "(brak)") . "\n";

$resources = ups_audit_get_client_resources($client);
foreach ($resources as $res) {
    if (!($res instanceof WP_Post)) {
        continue;
    }
    $rid = (int) $res->ID;
    $type = (string) get_post_meta($rid, "_ups_resource_type", true);
    if ($type === "gsc" && $gsc_pick !== "") {
        update_post_meta($rid, "_ups_resource_external_id", $gsc_pick);
        wp_update_post(["ID" => $rid, "post_title" => $gsc_pick]);
        echo "Updated GSC resource #{$rid} -> {$gsc_pick}\n";
        ups_audit_sync_gsc_resource($rid, 30, false);
        $cache = get_post_meta($rid, "_ups_resource_data_cache", true);
        echo "  GSC sync error: " . (is_array($cache) ? ($cache["error"] ?? "") : "?") . "\n";
        if (is_array($cache) && is_array($cache["summary"] ?? null)) {
            echo "  GSC clicks: " . (int) ($cache["summary"]["clicks"] ?? 0) . "\n";
        }
    }
    if ($type === "ga4" && $ga4_pick !== "") {
        update_post_meta($rid, "_ups_resource_external_id", $ga4_pick);
        wp_update_post(["ID" => $rid, "post_title" => $ga4_name !== "" ? $ga4_name : "GA Wtapes"]);
        echo "Updated GA4 resource #{$rid} -> {$ga4_pick}\n";
        ups_audit_sync_ga4_resource($rid, 30, false);
        $cache = get_post_meta($rid, "_ups_resource_data_cache", true);
        echo "  GA4 sync error: " . (is_array($cache) ? ($cache["error"] ?? "") : "?") . "\n";
        if (is_array($cache) && is_array($cache["summary"] ?? null)) {
            echo "  GA4 sessions: " . (int) ($cache["summary"]["sessions"] ?? 0) . "\n";
        }
    }
}

echo "\n=== Aggregate ===\n";
$agg = ups_audit_aggregate_client_data($client, 30, 0, false);
echo "ga4_sessions=" . (int) ($agg["ga4_sessions"] ?? 0)
    . " gsc_clicks=" . (int) ($agg["gsc_clicks"] ?? 0)
    . " ads_cost=" . round((float) ($agg["ads_cost"] ?? 0), 2) . "\n";
