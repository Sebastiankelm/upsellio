<?php
/**
 * Sync GSC + Ads wtapes, weryfikacja search terms i spadków pozycji.
 * wp eval-file wp-content/themes/upsellio/tools/audit-sync-wtapes-prod.php
 */

if (!defined("ABSPATH")) {
    exit(1);
}

$client_id = 965;
$gsc_resource = 964;
$ads_resource = 975;

echo "=== Sync GSC #{$gsc_resource} ===\n";
ups_audit_sync_gsc_resource($gsc_resource, 30, false);
$gsc_cache = get_post_meta($gsc_resource, "_ups_resource_data_cache", true);
if (is_array($gsc_cache)) {
    echo "gsc_error: " . ($gsc_cache["error"] ?? "") . "\n";
    echo "gsc_keywords_cur: " . count((array) ($gsc_cache["top_keywords"] ?? [])) . "\n";
    echo "gsc_keywords_prev: " . count((array) ($gsc_cache["previous_top_keywords"] ?? [])) . "\n";
    $sample_kw = array_slice((array) ($gsc_cache["top_keywords"] ?? []), 0, 3);
    foreach ($sample_kw as $kw) {
        if (!is_array($kw)) {
            continue;
        }
        echo "  kw: " . ($kw["keyword"] ?? "?") . " pos=" . ($kw["position"] ?? "?") . " impr=" . ($kw["impressions"] ?? 0) . "\n";
    }
}

echo "=== Sync Ads #{$ads_resource} ===\n";
ups_audit_sync_ads_resource($ads_resource, 30, false);
$ads_cache = get_post_meta($ads_resource, "_ups_resource_data_cache", true);
if (is_array($ads_cache)) {
    echo "ads_error: " . ($ads_cache["error"] ?? "") . "\n";
    echo "ads_summary: " . wp_json_encode($ads_cache["summary"] ?? [], JSON_UNESCAPED_UNICODE) . "\n";
    $terms = (array) ($ads_cache["search_terms"] ?? []);
    echo "search_terms_count: " . count($terms) . "\n";
    $top_terms = array_slice($terms, 0, 8);
    foreach ($top_terms as $st) {
        if (!is_array($st)) {
            continue;
        }
        echo "  term: " . ($st["search_term"] ?? "?")
            . " cost=" . round((float) ($st["cost_pln"] ?? $st["cost"] ?? 0), 2)
            . " conv=" . ($st["conversions"] ?? 0)
            . " clicks=" . ($st["clicks"] ?? 0) . "\n";
    }
}

echo "=== Aggregate client #{$client_id} ===\n";
$agg = ups_audit_aggregate_client_data($client_id, 30, 0, true);

echo "sessions=" . (int) ($agg["ga4_sessions"] ?? 0) . "\n";
echo "ads_cost=" . round((float) ($agg["ads_cost"] ?? 0), 2) . "\n";
echo "search_terms_agg=" . count((array) ($agg["search_terms"] ?? [])) . "\n";

$st_intel = (array) (($agg["intelligence"]["search_terms"] ?? []));
echo "st_has_data=" . (!empty($st_intel["has_data"]) ? "yes" : "no") . "\n";
echo "st_total_cost=" . ($st_intel["total_cost"] ?? 0) . " waste_pct=" . ($st_intel["waste_pct"] ?? 0) . "\n";
echo "st_waste_rows=" . count((array) ($st_intel["waste"] ?? [])) . "\n";
echo "st_converting_rows=" . count((array) ($st_intel["converting"] ?? [])) . "\n";

$drops = (array) ($agg["keyword_position_changes"] ?? []);
echo "position_drops=" . count($drops) . "\n";
foreach (array_slice($drops, 0, 5) as $d) {
    if (!is_array($d)) {
        continue;
    }
    echo "  drop: " . ($d["keyword"] ?? "?")
        . " " . ($d["position_prev"] ?? "?") . " -> " . ($d["position"] ?? "?")
        . " (Δ+" . ($d["position_delta"] ?? "?") . ")\n";
}

$alerts = (array) (($agg["intelligence"]["alerts"] ?? []));
$seo_alerts = array_filter($alerts, static fn($a) => is_array($a) && ($a["category"] ?? "") === "seo");
echo "seo_alerts=" . count($seo_alerts) . "\n";
foreach (array_slice(array_values($seo_alerts), 0, 5) as $a) {
    echo "  [" . ($a["severity"] ?? "?") . "] " . ($a["title"] ?? "") . " — " . ($a["message"] ?? "") . "\n";
}

echo "done_ok\n";
