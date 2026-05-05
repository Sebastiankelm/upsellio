<?php
if (!defined("ABSPATH")) {
    exit;
}

function upsellio_ai_refine_icp(): ?string
{
    if (function_exists("upsellio_ai_can_call") && !upsellio_ai_can_call("icp_refiner", 0.30)) {
        return null;
    }
    $won = get_posts([
        "post_type" => "lead",
        "posts_per_page" => 100,
        "tax_query" => [["taxonomy" => "lead_status", "field" => "slug", "terms" => ["won"]]],
    ]);
    $lost = get_posts([
        "post_type" => "lead",
        "posts_per_page" => 50,
        "tax_query" => [["taxonomy" => "lead_status", "field" => "slug", "terms" => ["lost"]]],
    ]);

    $build_profile = static function ($posts) {
        $out = [];
        foreach ($posts as $p) {
            if (!($p instanceof WP_Post)) {
                continue;
            }
            $out[] = [
                "industry" => get_post_meta($p->ID, "_upsellio_lead_quiz_industry", true),
                "problem" => get_post_meta($p->ID, "_upsellio_lead_quiz_problem", true),
                "budget" => get_post_meta($p->ID, "_upsellio_lead_quiz_budget", true),
                "service" => get_post_meta($p->ID, "_upsellio_lead_service", true),
                "utm_source" => get_post_meta($p->ID, "_upsellio_lead_utm_source", true),
                "gsc_query" => get_post_meta($p->ID, "_upsellio_lead_gsc_likely_query", true),
                "close_value" => (float) get_post_meta($p->ID, "_upsellio_lead_close_value", true),
                "days_to_close" => (strtotime($p->post_modified) - strtotime($p->post_date)) / DAY_IN_SECONDS,
                "message_excerpt" => mb_substr((string) $p->post_content, 0, 200),
            ];
        }
        return $out;
    };
    $won_data = $build_profile($won);
    $lost_data = $build_profile($lost);

    $system = <<<'EOT'
Jesteś analitykiem strategicznym B2B. Dostajesz dane wygranych i przegranych leadów. Zadanie: wyciągnij wzorce ICP i wygeneruj raport HTML.
Sekcje:
1. <h2>Top 3 segmenty wygrywające</h2>
2. <h2>3 segmenty tracone nieproporcjonalnie</h2>
3. <h2>5 sygnałów HIGH-CONVERT</h2>
4. <h2>3 sygnały NO-GO</h2>
5. <h2>Sugerowane zmiany w copy strony oferty</h2>
EOT;
    $user = "WYGRANE (n=" . count($won_data) . "):\n" . wp_json_encode($won_data, JSON_UNESCAPED_UNICODE) . "\n\n";
    $user .= "PRZEGRANE (n=" . count($lost_data) . "):\n" . wp_json_encode($lost_data, JSON_UNESCAPED_UNICODE);

    $GLOBALS["upsellio_ai_current_task"] = "icp_refiner";
    $cache_split = [
        "cached" => $system . "\n\nKRYTERIA STALE:\n- oddziel sygnaly wygrane/przegrane\n- podaj rekomendacje copy dla oferty",
        "dynamic" => $user,
    ];
    $report = upsellio_anthropic_crm_send_user_prompt(
        "",
        4000,
        90,
        function_exists("upsellio_ai_model_for") ? upsellio_ai_model_for("icp_refiner") : null,
        $cache_split
    );
    if ($report) {
        update_option("ups_ai_icp_report", wp_kses_post($report), false);
        update_option("ups_ai_icp_report_at", time(), false);
    }
    return $report;
}

add_filter("cron_schedules", function ($s) {
    $s["monthly"] = ["interval" => 30 * DAY_IN_SECONDS, "display" => "Co 30 dni"];
    return $s;
});
add_action("upsellio_ai_icp_cron", "upsellio_ai_refine_icp");
add_action("init", function () {
    if (!wp_next_scheduled("upsellio_ai_icp_cron")) {
        $first = strtotime("first day of next month 06:00:00", current_time("timestamp"));
        wp_schedule_event($first, "monthly", "upsellio_ai_icp_cron");
    }
});

add_action("admin_menu", function () {
    add_submenu_page("upsellio-site-analytics", "ICP Report", "ICP Report", "edit_posts", "ups-icp-report", function () {
        $report = get_option("ups_ai_icp_report", "");
        $at = (int) get_option("ups_ai_icp_report_at", 0);
        echo '<div class="wrap"><h1>ICP Report — ' . ($at ? date("Y-m-d", $at) : "brak") . "</h1>";
        if (isset($_POST["regen"]) && check_admin_referer("ups_icp_regen")) {
            upsellio_ai_refine_icp();
            echo '<div class="notice notice-success"><p>Wygenerowano.</p></div>';
            $report = get_option("ups_ai_icp_report", "");
        }
        echo '<form method="post">';
        wp_nonce_field("ups_icp_regen");
        echo '<button name="regen" class="button button-primary">Wygeneruj ponownie</button></form>';
        echo '<div style="max-width:900px;background:#fff;padding:30px;margin-top:20px;">';
        echo wp_kses_post((string) $report);
        echo "</div></div>";
    });
});
