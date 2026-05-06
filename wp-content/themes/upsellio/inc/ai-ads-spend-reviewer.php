<?php
if (!defined("ABSPATH")) {
    exit;
}

function upsellio_ai_join_ads_with_leads(array $campaigns): array
{
    $out = [];
    foreach ($campaigns as $c) {
        if (!is_array($c)) {
            continue;
        }
        $cname = (string) ($c["name"] ?? "");
        $leads = get_posts([
            "post_type" => "lead",
            "posts_per_page" => 200,
            "meta_query" => [["key" => "_upsellio_lead_utm_campaign", "value" => $cname]],
            "fields" => "ids",
        ]);
        if (!empty($leads)) {
            update_meta_cache("post", $leads);
        }
        $won = 0;
        $value = 0.0;
        foreach ($leads as $lid) {
            $statuses = wp_get_object_terms($lid, "lead_status", ["fields" => "slugs"]);
            if (in_array("won", $statuses, true)) {
                $won++;
                $value += (float) get_post_meta($lid, "_upsellio_lead_close_value", true);
            }
        }
        $cost = (float) ($c["cost"] ?? 0);
        $out[] = array_merge($c, [
            "leads_total" => count($leads),
            "won" => $won,
            "revenue" => $value,
            "cac" => $won > 0 ? round($cost / $won, 2) : null,
            "roas" => $cost > 0 ? round($value / $cost, 2) : null,
        ]);
    }
    return $out;
}

function upsellio_ai_review_ads_spend(): ?string
{
    if (function_exists("upsellio_ai_can_call") && !upsellio_ai_can_call("ads_spend_reviewer", 0.15)) {
        return null;
    }
    $campaigns = get_option("ups_ads_campaigns_data", []);
    $search_terms = get_option("ups_ads_search_terms_data", []);
    $auctions = get_option("ups_ads_auction_data", []);
    if (empty($campaigns) || !is_array($campaigns)) {
        return null;
    }
    $campaign_perf = upsellio_ai_join_ads_with_leads($campaigns);
    $gsc = get_option("upsellio_keyword_metrics_rows", []);
    $organic_queries = array_slice(array_column(is_array($gsc) ? $gsc : [], "query"), 0, 50);

    $system = <<<'EOT'
Jesteś senior performance marketer. Robisz weekly review wydatków Google Ads dla agencji B2B.
Format JSON:
{"summary":"...","top_5_negatives_to_add":[],"top_3_keywords_to_boost":[],"top_3_keywords_to_pause":[],"organic_overlap_warnings":[],"creative_suggestions":[]}
EOT;
    $user = "CAMPAIGNS:\n" . wp_json_encode(array_slice($campaign_perf, 0, 20), JSON_UNESCAPED_UNICODE) . "\n\n";
    $user .= "SEARCH TERMS:\n" . wp_json_encode(array_slice(is_array($search_terms) ? $search_terms : [], 0, 50), JSON_UNESCAPED_UNICODE) . "\n\n";
    $user .= "AUCTION:\n" . wp_json_encode(array_slice(is_array($auctions) ? $auctions : [], 0, 10), JSON_UNESCAPED_UNICODE) . "\n\n";
    $user .= "ORGANIC QUERIES:\n" . wp_json_encode($organic_queries, JSON_UNESCAPED_UNICODE);

    $GLOBALS["upsellio_ai_current_task"] = "ads_spend_reviewer";
    $cache_split = [
        "cached" => $system . "\n\nRAMY STALE:\n- priorytet CAC/ROAS\n- redukuj marnotrawstwo\n- wskaz overlap z organic",
        "dynamic" => $user,
    ];
    $resp = upsellio_anthropic_crm_send_user_prompt(
        "",
        2500,
        60,
        function_exists("upsellio_ai_model_for") ? upsellio_ai_model_for("ads_spend_reviewer") : null,
        $cache_split
    );
    if ($resp) {
        $json = json_decode(function_exists("upsellio_extract_json") ? upsellio_extract_json($resp) : $resp, true);
        if (is_array($json)) {
            update_option("ups_ai_ads_review", $json, false);
            update_option("ups_ai_ads_review_at", time(), false);
        }
    }
    return $resp;
}

add_action("upsellio_ai_ads_review_cron", "upsellio_ai_review_ads_spend");
add_action("init", function () {
    if (!wp_next_scheduled("upsellio_ai_ads_review_cron")) {
        $next = strtotime("next monday 08:00:00", current_time("timestamp"));
        wp_schedule_event($next, "weekly", "upsellio_ai_ads_review_cron");
    }
});
