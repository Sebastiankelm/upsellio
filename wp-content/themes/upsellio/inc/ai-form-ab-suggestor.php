<?php
if (!defined("ABSPATH")) {
    exit;
}

function upsellio_ai_compute_form_metrics(array $ga4): array
{
    $by_page = [];
    foreach ($ga4 as $row) {
        if (!is_array($row)) {
            continue;
        }
        if (!in_array((string) ($row["event"] ?? ""), ["form_start", "form_field_complete", "form_abandon", "generate_lead"], true)) {
            continue;
        }
        $page = (string) ($row["page_path"] ?? "");
        $by_page[$page][$row["event"]] = ($by_page[$page][$row["event"]] ?? 0) + (int) ($row["count"] ?? 0);
    }
    $metrics = [];
    foreach ($by_page as $page => $events) {
        $start = (int) ($events["form_start"] ?? 0);
        if ($start < 10) {
            continue;
        }
        $abandon_rate = $start > 0 ? ((int) ($events["form_abandon"] ?? 0) / $start) * 100 : 0;
        $completion_rate = $start > 0 ? ((int) ($events["generate_lead"] ?? 0) / $start) * 100 : 0;
        $metrics[] = [
            "page" => $page,
            "starts" => $start,
            "completions" => (int) ($events["generate_lead"] ?? 0),
            "abandons" => (int) ($events["form_abandon"] ?? 0),
            "completion_rate_pct" => round($completion_rate, 1),
            "abandon_rate_pct" => round($abandon_rate, 1),
        ];
    }
    usort($metrics, static function ($a, $b) {
        return $b["abandon_rate_pct"] <=> $a["abandon_rate_pct"];
    });
    return array_slice($metrics, 0, 10);
}

function upsellio_ai_form_ab_suggest(): void
{
    if (function_exists("upsellio_ai_can_call") && !upsellio_ai_can_call("form_ab_suggestor", 0.10)) {
        return;
    }
    $ga4 = get_option("ups_automation_ga4_daily_aggregates", []);
    $form_metrics = upsellio_ai_compute_form_metrics(is_array($ga4) ? $ga4 : []);
    if (empty($form_metrics)) {
        return;
    }
    $system = <<<'EOT'
Jesteś specjalistą CRO dla formularzy B2B. Dostajesz metryki funnel formularza per landing page. Sugeruj konkretne fixy.
Format JSON:
{"suggestions":[{"landing":"/oferta/","issue":"...","suggested_test":"...","expected_impact":"high|medium|low"}]}
Zasady: max 5 sugestii, konkretnie, z liczbami.
EOT;
    $user = "METRYKI FORMULARZY:\n" . wp_json_encode($form_metrics, JSON_UNESCAPED_UNICODE);
    $GLOBALS["upsellio_ai_current_task"] = "form_ab_suggestor";
    $cache_split = [
        "cached" => $system . "\n\nSTALE:\n- priorytetyzuj po impact vs effort\n- skup sie na tarciach formularza",
        "dynamic" => $user,
    ];
    $resp = upsellio_anthropic_crm_send_user_prompt(
        "",
        1800,
        45,
        function_exists("upsellio_ai_model_for") ? upsellio_ai_model_for("form_ab_suggestor") : null,
        $cache_split
    );
    if ($resp) {
        $json = json_decode(function_exists("upsellio_extract_json") ? upsellio_extract_json($resp) : $resp, true);
        if (is_array($json)) {
            update_option("ups_ai_form_ab_suggestions", $json, false);
            update_option("ups_ai_form_ab_at", time(), false);
        }
    }
}

add_action("upsellio_ai_form_ab_cron", "upsellio_ai_form_ab_suggest");
add_action("init", function () {
    if (!wp_next_scheduled("upsellio_ai_form_ab_cron")) {
        wp_schedule_event(current_time("timestamp") + DAY_IN_SECONDS, "weekly", "upsellio_ai_form_ab_cron");
    }
});
