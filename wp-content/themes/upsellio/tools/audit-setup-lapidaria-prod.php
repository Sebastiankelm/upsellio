<?php
/**
 * Lapidaria (#966) — mapowanie kont Google + pełny sync na prod.
 * wp eval-file wp-content/themes/upsellio/tools/audit-setup-lapidaria-prod.php
 */

if (!defined("ABSPATH")) {
    exit(1);
}

$client_id = 966;
$gacc_data = 962;
$gacc_agency = 963;
$ga4_property = "538163091";
$gsc_site = "https://lapidaria.pl/";

echo "=== Client #{$client_id} resources (before) ===\n";
foreach (ups_audit_get_client_resources($client_id) as $res) {
    if (!($res instanceof WP_Post)) {
        continue;
    }
    $rid = (int) $res->ID;
    echo "#{$rid} " . get_post_meta($rid, "_ups_resource_type", true)
        . " gacc=" . get_post_meta($rid, "_ups_resource_google_account_id", true)
        . " ext=" . get_post_meta($rid, "_ups_resource_external_id", true) . "\n";
}

echo "\n=== GA4/GSC on gacc #{$gacc_data} ===\n";
foreach (ups_audit_fetch_ga4_resources($gacc_data) as $acc) {
    if (!is_array($acc)) {
        continue;
    }
    foreach ((array) ($acc["properties"] ?? []) as $prop) {
        if (!is_array($prop)) {
            continue;
        }
        if ((string) ($prop["id"] ?? "") === $ga4_property) {
            echo "GA4 OK on #{$gacc_data}: " . ($prop["display_name"] ?? "") . "\n";
        }
    }
}
foreach (ups_audit_fetch_gsc_resources($gacc_data) as $site) {
    if (!is_array($site)) {
        continue;
    }
    if ((string) ($site["site_url"] ?? "") === $gsc_site) {
        echo "GSC on #{$gacc_data}: " . ($site["site_url"] ?? "") . " " . ($site["permission_level"] ?? "") . "\n";
    }
}

echo "\n=== GA4/GSC on gacc #{$gacc_agency} ===\n";
foreach (ups_audit_fetch_ga4_resources($gacc_agency) as $acc) {
    if (!is_array($acc)) {
        continue;
    }
    foreach ((array) ($acc["properties"] ?? []) as $prop) {
        if (!is_array($prop)) {
            continue;
        }
        if ((string) ($prop["id"] ?? "") === $ga4_property) {
            echo "GA4 OK on #{$gacc_agency}: " . ($prop["display_name"] ?? "") . "\n";
        }
    }
}
foreach (ups_audit_fetch_gsc_resources($gacc_agency) as $site) {
    if (!is_array($site)) {
        continue;
    }
    if (stripos((string) ($site["site_url"] ?? ""), "lapidaria") !== false) {
        echo "GSC on #{$gacc_agency}: " . ($site["site_url"] ?? "") . " verified=" . (!empty($site["is_verified"]) ? "yes" : "no") . " " . ($site["permission_level"] ?? "") . "\n";
    }
}

$ga4_gacc = 0;
$gsc_gacc = 0;
foreach ([$gacc_data, $gacc_agency] as $try_gacc) {
    foreach (ups_audit_fetch_ga4_resources($try_gacc) as $acc) {
        if (!is_array($acc)) {
            continue;
        }
        foreach ((array) ($acc["properties"] ?? []) as $prop) {
            if (is_array($prop) && (string) ($prop["id"] ?? "") === $ga4_property) {
                $ga4_gacc = (int) $try_gacc;
            }
        }
    }
}
foreach ([$gacc_data, $gacc_agency] as $try_gacc) {
    foreach (ups_audit_fetch_gsc_resources($try_gacc) as $site) {
        if (!is_array($site)) {
            continue;
        }
        $url = (string) ($site["site_url"] ?? "");
        if ($url === $gsc_site && !empty($site["is_verified"])) {
            $gsc_gacc = (int) $try_gacc;
        }
    }
}
if ($gsc_gacc === 0) {
    foreach ([$gacc_data, $gacc_agency] as $try_gacc) {
        foreach (ups_audit_fetch_gsc_resources($try_gacc) as $site) {
            if (!is_array($site)) {
                continue;
            }
            if ((string) ($site["site_url"] ?? "") === $gsc_site) {
                $gsc_gacc = (int) $try_gacc;
                break 2;
            }
        }
    }
}

echo "\n=== Auto-pick: GA4 gacc={$ga4_gacc} GSC gacc={$gsc_gacc} ===\n";

$ads_customer = "";
if (function_exists("ups_audit_fetch_ads_resources")) {
    foreach ([$gacc_agency, $gacc_data] as $try_gacc) {
        $ads = ups_audit_fetch_ads_resources($try_gacc);
        foreach ((array) $ads as $a) {
            if (!is_array($a)) {
                continue;
            }
            $name = strtolower((string) ($a["name"] ?? ""));
            $cid = (string) ($a["customer_id"] ?? "");
            if ($cid === "" || $cid === "1333330388") {
                continue;
            }
            if (stripos($name, "lapidaria") !== false) {
                $ads_customer = $cid;
                echo "Ads customer for lapidaria: {$cid} (gacc {$try_gacc})\n";
                break 2;
            }
        }
    }
}

foreach (ups_audit_get_client_resources($client_id) as $res) {
    if (!($res instanceof WP_Post)) {
        continue;
    }
    $rid = (int) $res->ID;
    $type = (string) get_post_meta($rid, "_ups_resource_type", true);

    if ($type === "ga4" && $ga4_gacc > 0) {
        update_post_meta($rid, "_ups_resource_google_account_id", $ga4_gacc);
        update_post_meta($rid, "_ups_resource_external_id", $ga4_property);
        wp_update_post(["ID" => $rid, "post_title" => "Lapidaria"]);
        echo "GA4 #{$rid} -> gacc {$ga4_gacc}\n";
        ups_audit_sync_ga4_resource($rid, 30, false);
        $c = get_post_meta($rid, "_ups_resource_data_cache", true);
        echo "  sessions=" . (is_array($c) ? (int) ($c["summary"]["sessions"] ?? 0) : 0)
            . " err=" . (is_array($c) ? ($c["error"] ?? "") : "?") . "\n";
    }

    if ($type === "gsc" && $gsc_gacc > 0) {
        update_post_meta($rid, "_ups_resource_google_account_id", $gsc_gacc);
        update_post_meta($rid, "_ups_resource_external_id", $gsc_site);
        wp_update_post(["ID" => $rid, "post_title" => $gsc_site]);
        echo "GSC #{$rid} -> gacc {$gsc_gacc}\n";
        ups_audit_sync_gsc_resource($rid, 30, false);
        $c = get_post_meta($rid, "_ups_resource_data_cache", true);
        echo "  clicks=" . (is_array($c) ? (int) ($c["summary"]["clicks"] ?? 0) : 0)
            . " err=" . (is_array($c) ? ($c["error"] ?? "") : "?") . "\n";
    }

    if ($type === "ads" && $ads_customer !== "") {
        update_post_meta($rid, "_ups_resource_google_account_id", $gacc_agency);
        update_post_meta($rid, "_ups_resource_external_id", $ads_customer);
        echo "Ads #{$rid} -> gacc {$gacc_agency} customer {$ads_customer}\n";
        ups_audit_sync_ads_resource($rid, 30, false);
        $c = get_post_meta($rid, "_ups_resource_data_cache", true);
        echo "  cost=" . (is_array($c) ? round((float) ($c["summary"]["cost"] ?? 0), 2) : 0)
            . " err=" . (is_array($c) ? ($c["error"] ?? "") : "?") . "\n";
    }
}

if ($ads_customer === "") {
    echo "No Ads customer matched 'lapidaria' — skip Ads or map manually.\n";
}

$clarity = get_posts([
    "post_type" => "crm_audit_resource",
    "posts_per_page" => -1,
    "post_status" => "publish",
    "meta_query" => [
        ["key" => "_ups_resource_client_id", "value" => $client_id, "type" => "NUMERIC"],
        ["key" => "_ups_resource_type", "value" => "clarity"],
    ],
]);
foreach ($clarity as $cres) {
    if (!($cres instanceof WP_Post)) {
        continue;
    }
    $crid = (int) $cres->ID;
    echo "Clarity sync #{$crid}...\n";
    ups_audit_sync_clarity_resource($crid);
}

echo "\n=== Aggregate ===\n";
$agg = ups_audit_aggregate_client_data($client_id, 30, 0, false);
echo "sessions=" . (int) ($agg["ga4_sessions"] ?? 0)
    . " gsc=" . (int) ($agg["gsc_clicks"] ?? 0)
    . " ads=" . round((float) ($agg["ads_cost"] ?? 0), 2)
    . " health=" . (int) ($agg["health_score"] ?? 0) . "\n";
