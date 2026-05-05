<?php
if (!defined("ABSPATH")) {
    exit;
}

function upsellio_ai_generate_ad_copy(): ?string
{
    if (function_exists("upsellio_ai_can_call") && !upsellio_ai_can_call("ad_copy_generator", 0.10)) {
        return null;
    }
    $icp = (string) get_option("ups_ai_icp_report", "");
    $wins = get_option("ups_ai_wins_snapshot", []);
    $gsc_cache = get_option("ups_gsc_analysis_cache", []);

    $system = <<<'EOT'
Jesteś senior copywriter Google Ads + Meta Ads B2B. Generujesz reklamy na bazie ICP profili i high-converting GSC queries.
Format JSON:
{"google_ads":[{"headline_1":"","headline_2":"","headline_3":"","description_1":"","description_2":"","target_keyword":"","target_segment":""}],"meta_ads":[{"primary_text":"","headline":"","description":"","cta":"Learn More|Sign Up|Contact Us","target_audience":"","creative_brief":""}]}
Wygeneruj 5 reklam Google + 3 reklamy Meta. Po polsku.
EOT;

    $user = "ICP REPORT:\n" . mb_substr($icp, 0, 8000) . "\n\n";
    $user .= "WINS PATTERNS:\n" . wp_json_encode(array_slice($wins["offers"] ?? [], 0, 5), JSON_UNESCAPED_UNICODE) . "\n\n";
    $user .= "GSC QUICK WINS:\n" . wp_json_encode(array_slice($gsc_cache["quick_wins"] ?? [], 0, 10), JSON_UNESCAPED_UNICODE);

    $GLOBALS["upsellio_ai_current_task"] = "ad_copy_generator";
    $cache_split = [
        "cached" => $system . "\n\nICP REPORT (STALY KONTEKST):\n" . mb_substr($icp, 0, 7000),
        "dynamic" => $user,
    ];
    $resp = upsellio_anthropic_crm_send_user_prompt(
        "",
        2500,
        60,
        function_exists("upsellio_ai_model_for") ? upsellio_ai_model_for("ad_copy_generator") : null,
        $cache_split
    );
    if ($resp) {
        update_option("ups_ai_ad_copy", $resp, false);
        update_option("ups_ai_ad_copy_at", time(), false);
    }
    return $resp;
}

add_filter("cron_schedules", function ($s) {
    if (!isset($s["monthly"])) {
        $s["monthly"] = ["interval" => 30 * DAY_IN_SECONDS, "display" => "Co 30 dni"];
    }
    return $s;
});
add_action("upsellio_ai_ad_copy_cron", "upsellio_ai_generate_ad_copy");
add_action("init", function () {
    if (!wp_next_scheduled("upsellio_ai_ad_copy_cron")) {
        $next = strtotime("first day of next month 09:00:00", current_time("timestamp"));
        wp_schedule_event($next, "monthly", "upsellio_ai_ad_copy_cron");
    }
});
