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
