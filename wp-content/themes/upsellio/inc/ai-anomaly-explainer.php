<?php
if (!defined("ABSPATH")) {
    exit;
}

if (!function_exists("upsellio_extract_json")) {
    function upsellio_extract_json(string $response): string
    {
        if (preg_match('/\{.*\}/s', $response, $m)) {
            return $m[0];
        }
        return $response;
    }
}

function upsellio_ai_collect_anomalies(): array
{
    $ga4 = get_option("ups_automation_ga4_daily_aggregates", []);
    if (!is_array($ga4) || count($ga4) < 14) {
        return [];
    }

    $this_week = [];
    $prev_week = [];
    foreach ($ga4 as $row) {
        if (!is_array($row)) {
            continue;
        }
        $date_raw = (string) ($row["date"] ?? "now");
        $date_ts = strtotime($date_raw);
        if ($date_ts === false) {
            $date_ts = time();
        }
        $days_ago = (time() - $date_ts) / DAY_IN_SECONDS;
        $bucket = $days_ago <= 7 ? "this" : ($days_ago <= 14 ? "prev" : null);
        if (!$bucket) {
            continue;
        }
        $channel = (string) ($row["channel"] ?? "unknown");
        ${$bucket . "_week"}[$channel]["sessions"] = (${$bucket . "_week"}[$channel]["sessions"] ?? 0) + (int) ($row["sessions"] ?? 0);
        ${$bucket . "_week"}[$channel]["conversions"] = (${$bucket . "_week"}[$channel]["conversions"] ?? 0) + (int) ($row["conversions"] ?? 0);
    }

    $anomalies = [];
    foreach ($this_week as $channel => $now) {
        $prev = $prev_week[$channel] ?? ["sessions" => 0, "conversions" => 0];
        foreach (["sessions", "conversions"] as $metric) {
            $pct = $prev[$metric] > 0 ? (($now[$metric] - $prev[$metric]) / $prev[$metric]) * 100 : 0;
            if (abs($pct) >= 25) {
                $anomalies[] = [
                    "channel" => $channel,
                    "metric" => $metric,
                    "now" => $now[$metric],
                    "prev" => $prev[$metric],
                    "pct" => round($pct, 1),
                    "detected_at" => current_time("mysql"),
                ];
            }
        }
    }
    return $anomalies;
}

function upsellio_ai_anomaly_context(array $anomaly): array
{
    $context = [];
    if (function_exists("upsellio_gsc_quick_wins_extended")) {
        $cache = get_option("ups_gsc_analysis_cache", []);
        if (is_array($cache)) {
            $context["gsc_top_movers"] = array_slice($cache["quick_wins"] ?? [], 0, 5);
        }
    }
    $context["anomaly"] = $anomaly;
    return $context;
}

function upsellio_ai_explain_anomalies(): void
{
    if (function_exists("upsellio_ai_can_call") && !upsellio_ai_can_call("anomaly_explainer", 0.10)) {
        return;
    }

    $anomalies = upsellio_ai_collect_anomalies();
    if (empty($anomalies)) {
        return;
    }

    $explanations = [];
    foreach (array_slice($anomalies, 0, 5) as $a) {
        $context = upsellio_ai_anomaly_context($a);

        $system = <<<'EOT'
Jesteś analitykiem marketingu B2B. Dostajesz wykrytą anomalię (zmianę WoW lub MoM) + pełen kontekst danych. Zadanie:
1. Wyjaśnij CO się stało (1 zdanie)
2. Najprawdopodobniejsza przyczyna (1 zdanie)
3. Jedna konkretna akcja do podjęcia (1 zdanie)

Format JSON: {"what": "...", "why": "...", "action": "..."}
Po polsku, bez korpomowy, język operacyjny (CPL, CPM, CTR, konwersja).
EOT;

        $user = "ANOMALIA:\n" . wp_json_encode($a, JSON_UNESCAPED_UNICODE) . "\n\nKONTEKST:\n" . wp_json_encode($context, JSON_UNESCAPED_UNICODE);

        $GLOBALS["upsellio_ai_current_task"] = "anomaly_explainer";
        $cache_split = [
            "cached" => $system . "\n\nSTALE RAMY ANALITYCZNE:\n- oceniaj trend WoW\n- uwzgledniaj sezonowosc\n- podawaj 1 akcje operacyjna",
            "dynamic" => $user,
        ];
        $resp = upsellio_anthropic_crm_send_user_prompt(
            "",
            500,
            20,
            function_exists("upsellio_ai_model_for") ? upsellio_ai_model_for("anomaly_explainer") : null,
            $cache_split
        );
        if ($resp) {
            $json = json_decode(upsellio_extract_json($resp), true);
            if (is_array($json)) {
                $explanations[md5(serialize($a))] = array_merge($a, $json);
            }
        }
    }

    update_option("ups_ai_anomaly_explanations", $explanations, false);
    update_option("ups_ai_anomaly_explanations_at", time(), false);
}

add_action("upsellio_ai_anomaly_cron", "upsellio_ai_explain_anomalies");
add_action("init", function () {
    if (!wp_next_scheduled("upsellio_ai_anomaly_cron")) {
        $start = strtotime("tomorrow 06:00:00", current_time("timestamp"));
        wp_schedule_event($start, "daily", "upsellio_ai_anomaly_cron");
    }
});

add_action("wp_ajax_ups_explain_anomaly", function () {
    check_ajax_referer("ups_explain_anomaly_action");
    if (!current_user_can("edit_posts")) {
        wp_send_json_error(["message" => "forbidden"], 403);
    }
    upsellio_ai_explain_anomalies();
    wp_send_json_success(["ok" => true]);
});
