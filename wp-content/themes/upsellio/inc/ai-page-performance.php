<?php
if (!defined("ABSPATH")) {
    exit;
}

function upsellio_ai_classify_post_action(int $post_id, array $stats): ?array
{
    $post = get_post($post_id);
    if (!$post) {
        return null;
    }
    $gsc_rows = get_option("upsellio_keyword_metrics_rows", []);
    if (!is_array($gsc_rows)) {
        $gsc_rows = [];
    }
    $matching = array_filter($gsc_rows, static function ($r) use ($post_id) {
        return is_array($r) && strpos((string) ($r["page"] ?? $r["url"] ?? ""), (string) get_permalink($post_id)) !== false;
    });
    usort($matching, static function ($a, $b) {
        return ((int) ($b["clicks"] ?? 0)) <=> ((int) ($a["clicks"] ?? 0));
    });
    $top_queries = array_slice($matching, 0, 5);

    $age_days = floor((time() - get_post_time("U", false, $post_id)) / DAY_IN_SECONDS);
    $traffic = (int) ($stats["gsc_clicks"] ?? 0);
    $views = (int) ($stats["views"] ?? 0);

    if ($traffic === 0 && $age_days > 180) {
        $classification = "retire";
    } elseif ($traffic > 0 && $views > 50 && ((int) ($stats["leads"] ?? 0) === 0)) {
        $classification = "refresh_cta";
    } elseif ($traffic > 0 && ((float) ($matching[0]["position"] ?? 50)) > 5 && ((float) ($matching[0]["position"] ?? 50)) < 20) {
        $classification = "deepen";
    } else {
        $classification = "monitor";
    }
    if ($classification === "monitor") {
        return null;
    }

    $system = <<<'EOT'
Jesteś specjalistą SEO/CRO dla bloga B2B. Klasyfikujesz post i generujesz konkretne sugestie poprawy. Format JSON:
{"classification": "refresh_cta|deepen|retire", "summary": "1 zdanie", "actions": ["akcja 1", "akcja 2", "akcja 3"]}
EOT;

    $user = sprintf(
        "POST: %s\nLEN: %d znaków\nWIEK: %d dni\nGSC clicks: %d\nVIEWS: %d\nLEADS: %d\nTOP QUERIES:\n%s\n\nPrzewidywana klasyfikacja: %s\nWygeneruj 3 konkretne akcje.",
        $post->post_title,
        mb_strlen((string) $post->post_content),
        $age_days,
        $traffic,
        $views,
        (int) ($stats["leads"] ?? 0),
        wp_json_encode($top_queries, JSON_UNESCAPED_UNICODE),
        $classification
    );
    $GLOBALS["upsellio_ai_current_task"] = "page_performance";
    $model = $classification === "retire"
        ? (function_exists("upsellio_ai_model_for") ? upsellio_ai_model_for("page_perf_simple") : null)
        : (function_exists("upsellio_ai_model_for") ? upsellio_ai_model_for("page_perf_deep") : null);
    $cache_split = [
        "cached" => $system . "\n\nZASADY STALE:\n- konkretne rekomendacje\n- max 3 akcje\n- uwzglednij intent query i etap lejka",
        "dynamic" => $user,
    ];
    $resp = upsellio_anthropic_crm_send_user_prompt("", 600, 20, $model, $cache_split);
    if (!$resp) {
        return null;
    }
    $json = json_decode(function_exists("upsellio_extract_json") ? upsellio_extract_json($resp) : $resp, true);
    return is_array($json) ? array_merge($json, ["post_id" => $post_id, "title" => $post->post_title]) : null;
}

function upsellio_ai_optimize_page_performance(): void
{
    if (function_exists("upsellio_ai_can_call") && !upsellio_ai_can_call("page_performance", 0.50)) {
        return;
    }
    $snapshot = get_option("ups_ai_master_snapshot", []);
    if (!is_array($snapshot)) {
        return;
    }
    $high_traffic_no_leads = $snapshot["blog"]["high_traffic_no_leads"] ?? [];
    if (empty($high_traffic_no_leads) || !is_array($high_traffic_no_leads)) {
        return;
    }
    $suggestions = [];
    foreach (array_slice($high_traffic_no_leads, 0, 8) as $post_data) {
        if (!is_array($post_data)) {
            continue;
        }
        $post_id = (int) ($post_data["id"] ?? 0);
        if (!$post_id) {
            continue;
        }
        $sug = upsellio_ai_classify_post_action($post_id, $post_data);
        if ($sug) {
            $suggestions[$post_id] = $sug;
        }
    }
    update_option("ups_ai_page_perf_suggestions", $suggestions, false);
    update_option("ups_ai_page_perf_at", time(), false);
}

add_action("upsellio_ai_page_perf_cron", "upsellio_ai_optimize_page_performance");
add_action("init", function () {
    if (!wp_next_scheduled("upsellio_ai_page_perf_cron")) {
        wp_schedule_event(current_time("timestamp") + DAY_IN_SECONDS, "weekly", "upsellio_ai_page_perf_cron");
    }
});

add_action("add_meta_boxes", function () {
    add_meta_box("ups_ai_post_perf", "🎯 AI: Co zrobić z tym postem", function ($post) {
        $sug = get_option("ups_ai_page_perf_suggestions", []);
        if (!is_array($sug) || !isset($sug[$post->ID])) {
            echo "<p>Brak rekomendacji. Cron tygodniowy jeszcze nie objął tego posta.</p>";
            return;
        }
        $s = $sug[$post->ID];
        $colors = ["retire" => "#d94c4c", "refresh_cta" => "#f97316", "deepen" => "#15803d"];
        echo '<div style="padding:12px;background:#f8fafc;border-left:4px solid ' . ($colors[$s["classification"]] ?? "#666") . ';">';
        echo "<strong>" . strtoupper(esc_html($s["classification"])) . "</strong><br>";
        echo "<p>" . esc_html($s["summary"] ?? "") . "</p><ol>";
        foreach (($s["actions"] ?? []) as $a) {
            echo "<li>" . esc_html($a) . "</li>";
        }
        echo "</ol></div>";
    }, ["post", "page"], "side");
});
