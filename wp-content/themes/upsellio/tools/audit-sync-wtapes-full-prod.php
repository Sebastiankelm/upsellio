<?php
/**
 * Pełny sync produkcyjny WTapes (#965): GA4, GSC, Ads, Clarity + agregacja.
 * wp eval-file wp-content/themes/upsellio/tools/audit-sync-wtapes-full-prod.php
 */

if (!defined("ABSPATH")) {
    exit(1);
}

$client_id = 965;
$ga4 = 971;
$gsc = 964;
$ads = 975;

echo "=== WTapes full sync (client #{$client_id}) ===\n";

echo "\n--- GA4 #{$ga4} (full history) ---\n";
ups_audit_sync_ga4_resource($ga4, 30, true);
$ga4_cache = get_post_meta($ga4, "_ups_resource_data_cache", true);
if (is_array($ga4_cache)) {
    echo "error: " . ($ga4_cache["error"] ?? "") . "\n";
    echo "summary: " . wp_json_encode($ga4_cache["summary"] ?? [], JSON_UNESCAPED_UNICODE) . "\n";
    echo "not_set: " . wp_json_encode($ga4_cache["attribution"] ?? [], JSON_UNESCAPED_UNICODE) . "\n";
}

echo "\n--- GSC #{$gsc} (full history) ---\n";
ups_audit_sync_gsc_resource($gsc, 30, true);
$gsc_cache = get_post_meta($gsc, "_ups_resource_data_cache", true);
if (is_array($gsc_cache)) {
    echo "error: " . ($gsc_cache["error"] ?? "") . "\n";
    echo "keywords_cur: " . count((array) ($gsc_cache["top_keywords"] ?? []));
    echo " keywords_prev: " . count((array) ($gsc_cache["previous_top_keywords"] ?? [])) . "\n";
}

echo "\n--- Ads #{$ads} (90d fetch) ---\n";
ups_audit_sync_ads_resource($ads, 30, true);
$ads_cache = get_post_meta($ads, "_ups_resource_data_cache", true);
if (is_array($ads_cache)) {
    echo "error: " . ($ads_cache["error"] ?? "") . "\n";
    echo "summary: " . wp_json_encode($ads_cache["summary"] ?? [], JSON_UNESCAPED_UNICODE) . "\n";
    echo "search_terms: " . count((array) ($ads_cache["search_terms"] ?? [])) . "\n";
}

echo "\n--- Clarity (client #{$client_id}) ---\n";
$clarity_ids = [];
if (function_exists("ups_audit_get_client_resources")) {
    foreach (ups_audit_get_client_resources($client_id, "clarity") as $p) {
        if ($p instanceof WP_Post) {
            $clarity_ids[] = (int) $p->ID;
        }
    }
}
if ($clarity_ids === []) {
    echo "no mapped clarity resources\n";
} else {
    foreach ($clarity_ids as $crid) {
        echo "sync clarity #{$crid}\n";
        ups_audit_sync_clarity_resource($crid, 30, false);
        $cc = get_post_meta($crid, "_ups_resource_data_cache", true);
        if (!is_array($cc)) {
            continue;
        }
        $sum = is_array($cc["summary"] ?? null) ? $cc["summary"] : [];
        echo "  error: " . ($cc["error"] ?? "") . "\n";
        echo "  sessions=" . (int) ($sum["sessions"] ?? 0)
            . " dead=" . (int) ($sum["dead_clicks"] ?? 0)
            . " rage=" . (int) ($sum["rage_clicks"] ?? 0) . "\n";
        foreach (array_slice((array) ($sum["by_dimension"] ?? []), 0, 4) as $dev) {
            if (!is_array($dev)) {
                continue;
            }
            echo "  device " . ($dev["label"] ?? "?")
                . ": sess=" . (int) ($dev["sessions"] ?? 0)
                . " dead=" . (int) ($dev["dead_clicks"] ?? 0)
                . " rage=" . (int) ($dev["rage_clicks"] ?? 0) . "\n";
        }
    }
}

echo "\n--- Aggregate + intelligence ---\n";
$agg = ups_audit_aggregate_client_data($client_id, 30, 0, true);
$intel = (array) ($agg["intelligence"] ?? []);

echo "ga4_sessions=" . (int) ($agg["ga4_sessions"] ?? 0);
echo " gsc_clicks=" . (int) ($agg["gsc_clicks"] ?? 0);
echo " ads_cost=" . round((float) ($agg["ads_cost"] ?? 0), 0) . "\n";
echo "health=" . (int) ($agg["health_score"] ?? 0);
$ht = (array) ($agg["health_trend"] ?? []);
if ($ht !== []) {
    echo " (delta=" . (int) ($ht["delta"] ?? 0) . ")";
}
echo " tracking=" . (int) (($intel["tracking_health"]["score"] ?? 0));
echo " opportunity=" . (int) (($intel["opportunity"]["score"] ?? 0)) . "\n";

$attr = (array) ($agg["attribution_confidence"] ?? ($intel["attribution_confidence"] ?? []));
if ($attr !== []) {
    echo "attribution_confidence=" . (int) ($attr["score"] ?? 0) . "% (" . (string) ($attr["label"] ?? "") . ")\n";
}
$rev_conf = (array) ($agg["revenue_confidence"] ?? ($intel["revenue_confidence"] ?? []));
if ($rev_conf !== []) {
    echo "revenue_confidence=" . (int) ($rev_conf["score"] ?? 0) . "% (" . (string) ($rev_conf["label"] ?? "") . ")\n";
    foreach ((array) ($rev_conf["factors"] ?? []) as $rf) {
        echo "  rev_factor: {$rf}\n";
    }
}
$dq = (array) ($agg["data_quality"] ?? ($intel["data_quality"] ?? []));
if ($dq !== []) {
    echo "data_quality: " . (string) ($dq["summary"] ?? "") . "\n";
    foreach ((array) ($dq["warnings"] ?? []) as $dw) {
        echo "  dq_warn: {$dw}\n";
    }
}
$hh = function_exists("ups_audit_health_history") ? ups_audit_health_history($client_id) : [];
echo "health_history_months=" . count($hh) . "\n";
$crm_q = (array) ($intel["crm_quality"] ?? []);
if ($crm_q !== []) {
    echo "crm_quality=" . (int) ($crm_q["score"] ?? 0) . " " . (string) ($crm_q["summary"] ?? "") . "\n";
}
$rev_q = (array) ($agg["revenue_quality"] ?? ($intel["revenue_quality"] ?? []));
if ($rev_q !== []) {
    echo "revenue_trusted=" . (!empty($rev_q["trusted"]) ? "yes" : "NO") . "\n";
    if (!empty($rev_q["reasons"])) {
        foreach ((array) $rev_q["reasons"] as $rq) {
            echo "  rev_warn: {$rq}\n";
        }
    }
}
echo "ga4_revenue=" . round((float) ($agg["ga4_revenue"] ?? 0), 0);
echo " roas=" . round((float) ($agg["roas"] ?? 0), 2) . "x\n";
echo "purchase_count=" . (int) ($agg["ga4_purchase_count"] ?? 0);
echo " not_set=" . round((float) ($agg["ga4_not_set_pct"] ?? 0), 1) . "%\n";

$exec = (array) ($intel["executive_summary"] ?? []);
if (!empty($exec["text"])) {
    echo "executive: " . $exec["text"] . "\n";
}

$cpa = (array) ($intel["ads_channel_cpa"] ?? []);
if (!empty($cpa["summary"])) {
    echo "ads_cpa: " . $cpa["summary"] . "\n";
}

$st = (array) ($intel["search_terms"] ?? []);
echo "search_terms: " . count((array) ($agg["search_terms"] ?? []));
echo " waste_pct=" . ($st["waste_pct"] ?? 0) . "%\n";

echo "clarity_agg: sessions=" . (int) ($agg["clarity_sessions"] ?? 0);
echo " dead=" . (int) ($agg["clarity_dead_clicks"] ?? 0);
echo " rage=" . (int) ($agg["clarity_rage_clicks"] ?? 0) . "\n";
foreach (array_slice((array) ($agg["clarity_by_device"] ?? []), 0, 4) as $dev) {
    if (!is_array($dev)) {
        continue;
    }
    echo "  agg_device " . ($dev["label"] ?? "?")
        . ": sess=" . (int) ($dev["sessions"] ?? 0)
        . " dead=" . (int) ($dev["dead_clicks"] ?? 0)
        . " rage=" . (int) ($dev["rage_clicks"] ?? 0) . "\n";
}

$bench = (array) ($intel["benchmark_intel"] ?? []);
if (!empty($bench["has_data"])) {
    echo "benchmark (" . (int) ($bench["clients_in_sample"] ?? 0) . " clients):\n";
    foreach (array_slice((array) ($bench["comparisons"] ?? []), 0, 5) as $row) {
        if (!is_array($row)) {
            continue;
        }
        echo "  " . ($row["label"] ?? "?") . ": " . ($row["client"] ?? "?") . " vs " . ($row["benchmark"] ?? "—") . "\n";
    }
}

$journey = (array) ($intel["customer_journey"] ?? []);
foreach ((array) ($journey["stages"] ?? []) as $stage) {
    if (!is_array($stage)) {
        continue;
    }
    echo "journey: " . ($stage["name"] ?? "?") . " = " . ($stage["value"] ?? 0) . "\n";
}

echo "\n--- Okna 30 / 60 / 90 dni ---\n";
foreach ([30, 60, 90] as $win) {
    $wagg = ups_audit_aggregate_client_data($client_id, $win, 0, false);
    echo "window {$win}d: gsc=" . (int) ($wagg["gsc_clicks"] ?? 0)
        . " ga4=" . (int) ($wagg["ga4_sessions"] ?? 0)
        . " ads=" . round((float) ($wagg["ads_cost"] ?? 0), 0)
        . " ts_gsc=" . count((array) (($wagg["timeseries"]["gsc_clicks"] ?? [])))
        . " ts_ga4=" . count((array) (($wagg["timeseries"]["ga4_sessions"] ?? [])))
        . "\n";
}

echo "\ndone_ok\n";
