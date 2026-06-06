<?php
if (!defined("ABSPATH")) {
    exit;
}

function upsellio_analytics_query_lead_value(int $range_days = 30, int $limit = 50): array
{
    $cache_key = "ups_analytics_qlv_{$range_days}_{$limit}";
    $cached = get_transient($cache_key);
    if ($cached !== false && is_array($cached)) {
        return $cached;
    }
    $keyword_rows = (array) get_option("upsellio_keyword_metrics_rows", []);
    $keyword_index = [];
    foreach ($keyword_rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $query_raw = (string) ($row["keyword"] ?? "");
        $query = mb_strtolower(trim($query_raw));
        if ($query === "") {
            continue;
        }
        if (!isset($keyword_index[$query])) {
            $keyword_index[$query] = ["query" => $query_raw, "impressions" => 0, "clicks" => 0];
        }
        $keyword_index[$query]["impressions"] += (int) ($row["impressions"] ?? 0);
        $keyword_index[$query]["clicks"] += (int) ($row["clicks"] ?? 0);
    }
    $lead_query = new WP_Query([
        "post_type" => "lead",
        "posts_per_page" => 500,
        "date_query" => [["after" => "-{$range_days} days"]],
        "fields" => "ids",
        "no_found_rows" => true,
    ]);
    $lead_ids = is_array($lead_query->posts) ? $lead_query->posts : [];
    if (!empty($lead_ids)) {
        update_meta_cache("post", $lead_ids);
        $term_assignments = wp_get_object_terms($lead_ids, "lead_status", ["fields" => "all_with_object_id"]);
    } else {
        $term_assignments = [];
    }
    $lead_won_map = [];
    foreach ((array) $term_assignments as $term) {
        if (isset($term->slug, $term->object_id) && (string) $term->slug === "won") {
            $lead_won_map[(int) $term->object_id] = true;
        }
    }
    $totals = [];
    foreach ($lead_ids as $lead_id) {
        $likely_query = mb_strtolower(trim((string) get_post_meta((int) $lead_id, "_upsellio_lead_gsc_likely_query", true)));
        if ($likely_query === "" || !isset($keyword_index[$likely_query])) {
            continue;
        }
        $value = (float) get_post_meta((int) $lead_id, "_upsellio_lead_close_value", true);
        if (!isset($totals[$likely_query])) {
            $totals[$likely_query] = [
                "query" => $keyword_index[$likely_query]["query"],
                "impressions" => $keyword_index[$likely_query]["impressions"],
                "clicks" => $keyword_index[$likely_query]["clicks"],
                "leads" => 0,
                "won" => 0,
                "value" => 0.0,
            ];
        }
        $totals[$likely_query]["leads"]++;
        if (isset($lead_won_map[(int) $lead_id])) {
            $totals[$likely_query]["won"]++;
            $totals[$likely_query]["value"] += $value;
        }
    }
    foreach ($totals as &$row) {
        $row["rpm"] = ((int) $row["impressions"] > 0) ? ((float) $row["value"] / (int) $row["impressions"] * 1000) : 0.0;
    }
    unset($row);
    usort($totals, static function ($a, $b) {
        return ((float) ($b["value"] ?? 0)) <=> ((float) ($a["value"] ?? 0));
    });
    $rows = array_slice(array_values($totals), 0, $limit);
    $result = ["rows" => $rows, "total_value" => array_sum(array_column($rows, "value")), "computed_at" => time()];
    set_transient($cache_key, $result, 15 * MINUTE_IN_SECONDS);
    return $result;
}

function upsellio_analytics_channel_ltv(int $range_days = 30): array
{
    $summary = function_exists("upsellio_sales_engine_build_decision_layer_analytics")
        ? upsellio_sales_engine_build_decision_layer_analytics($range_days)
        : ["sources" => []];
    $rows = [];
    foreach ((array) ($summary["sources"] ?? []) as $channel => $stat) {
        if (!is_array($stat)) {
            continue;
        }
        $sessions = (int) ($stat["sessions"] ?? 0);
        $revenue = (float) ($stat["revenue"] ?? 0);
        $leads = (int) ($stat["deals"] ?? 0);
        $won = (int) ($stat["won"] ?? 0);
        $rows[] = [
            "channel" => (string) $channel,
            "sessions" => $sessions,
            "leads" => $leads,
            "won" => $won,
            "cr" => $sessions > 0 ? ($leads / $sessions) * 100 : 0,
            "ltv_per_session" => $sessions > 0 ? $revenue / $sessions : 0,
        ];
    }
    return ["rows" => $rows, "computed_at" => time()];
}

/**
 * LTV kanałów GA4 powiązanych z leadami/ofertami danego klienta CRM.
 *
 * @return array{rows: list<array<string,mixed>>, computed_at: int}
 */
function upsellio_analytics_channel_ltv_for_client(int $client_id, int $range_days = 30, ?array $agg = null): array
{
    $client_id = (int) $client_id;
    if ($client_id <= 0) {
        return ["rows" => [], "computed_at" => time()];
    }

    if (!is_array($agg) || $agg === []) {
        $agg = function_exists("ups_audit_aggregate_client_data")
            ? ups_audit_aggregate_client_data($client_id, $range_days, 0, false)
            : [];
    }
    $channels = [];
    foreach ((array) ($agg["channels"] ?? []) as $ch) {
        if (!is_array($ch)) {
            continue;
        }
        $key = function_exists("ups_audit_normalize_channel_key")
            ? ups_audit_normalize_channel_key((string) ($ch["source"] ?? ""), (string) ($ch["medium"] ?? ""))
            : strtolower(trim((string) ($ch["source"] ?? "")) . "|" . trim((string) ($ch["medium"] ?? "")));
        $channels[$key] = [
            "channel" => trim((string) ($ch["source"] ?? "") . " / " . (string) ($ch["medium"] ?? "")),
            "sessions" => (int) ($ch["sessions"] ?? 0),
            "conversions" => (int) ($ch["conversions"] ?? 0),
            "revenue" => (float) ($ch["revenue"] ?? 0),
            "leads" => 0,
            "won" => 0,
            "won_value" => 0.0,
        ];
    }

    $after = gmdate("Y-m-d H:i:s", strtotime("-" . max(1, $range_days) . " days"));
    $lead_types = array_values(array_filter(["crm_lead", "lead"], "post_type_exists"));
    foreach ($lead_types as $pt) {
        $leads = get_posts([
            "post_type" => $pt,
            "posts_per_page" => 500,
            "post_status" => ["publish", "draft", "pending", "private"],
            "date_query" => [["after" => $after]],
        ]);
        foreach ($leads as $lead) {
            if (!($lead instanceof WP_Post)) {
                continue;
            }
            $lid = (int) $lead->ID;
            if (function_exists("ups_audit_lead_matches_client") && !ups_audit_lead_matches_client($client_id, $lid)) {
                continue;
            }
            $src = (string) get_post_meta($lid, "_upsellio_lead_utm_source", true);
            $med = (string) get_post_meta($lid, "_upsellio_lead_utm_medium", true);
            $key = function_exists("ups_audit_normalize_channel_key")
                ? ups_audit_normalize_channel_key($src, $med)
                : strtolower(trim($src) . "|" . trim($med));
            $src = strtolower(trim($src));
            $med = strtolower(trim($med));
            if ($src === "") {
                $src = "(direct)";
            }
            if ($med === "") {
                $med = "(none)";
            }
            if (!isset($channels[$key])) {
                $channels[$key] = [
                    "channel" => $src . " / " . $med,
                    "sessions" => 0,
                    "conversions" => 0,
                    "revenue" => 0.0,
                    "leads" => 0,
                    "won" => 0,
                    "won_value" => 0.0,
                ];
            }
            $channels[$key]["leads"]++;
            $val = (float) get_post_meta($lid, "_upsellio_lead_close_value", true);
            if ($val > 0) {
                $channels[$key]["won"]++;
                $channels[$key]["won_value"] += $val;
            }
        }
    }

    $offers = get_posts([
        "post_type" => "crm_offer",
        "posts_per_page" => 500,
        "post_status" => ["publish", "draft", "pending", "private"],
        "date_query" => [["after" => $after]],
        "meta_query" => [[
            "key" => "_ups_offer_client_id",
            "value" => $client_id,
            "compare" => "=",
        ]],
    ]);
    foreach ($offers as $offer) {
        if (!($offer instanceof WP_Post)) {
            continue;
        }
        $oid = (int) $offer->ID;
        $src_raw = (string) get_post_meta($oid, "_ups_offer_utm_source", true);
        $med_raw = (string) get_post_meta($oid, "_ups_offer_utm_medium", true);
        if (trim($src_raw) === "") {
            continue;
        }
        $key = function_exists("ups_audit_normalize_channel_key")
            ? ups_audit_normalize_channel_key($src_raw, $med_raw)
            : strtolower(trim($src_raw) . "|" . trim($med_raw));
        $src = strtolower(trim($src_raw));
        $med = strtolower(trim($med_raw));
        if (!isset($channels[$key])) {
            $channels[$key] = [
                "channel" => $src . " / " . $med,
                "sessions" => 0,
                "conversions" => 0,
                "revenue" => 0.0,
                "leads" => 0,
                "won" => 0,
                "won_value" => 0.0,
            ];
        }
        $channels[$key]["leads"]++;
        if ((string) get_post_meta($oid, "_ups_offer_status", true) === "won") {
            $channels[$key]["won"]++;
            $channels[$key]["won_value"] += (float) get_post_meta($oid, "_ups_offer_won_value", true);
        }
    }

    $rows = [];
    foreach ($channels as $row) {
        $sessions = (int) ($row["sessions"] ?? 0);
        $leads = (int) ($row["leads"] ?? 0);
        $won_value = (float) ($row["won_value"] ?? 0);
        $rows[] = [
            "channel" => (string) ($row["channel"] ?? ""),
            "sessions" => $sessions,
            "leads" => $leads,
            "won" => (int) ($row["won"] ?? 0),
            "cr" => $sessions > 0 ? round(($leads / $sessions) * 100, 2) : 0.0,
            "ltv_per_session" => $sessions > 0 ? round($won_value / $sessions, 2) : 0.0,
            "won_value" => $won_value,
        ];
    }

    usort($rows, static function ($a, $b) {
        return ((float) ($b["won_value"] ?? 0)) <=> ((float) ($a["won_value"] ?? 0));
    });

    return ["rows" => array_slice($rows, 0, 15), "computed_at" => time()];
}
