<?php
if (!defined("ABSPATH")) {
    exit;
}

function upsellio_ai_compute_wow_deltas(array $now, array $prev): array
{
    $deltas = [];
    foreach (["sales", "leads", "blog", "channels"] as $section) {
        if (!isset($now[$section])) {
            continue;
        }
        foreach ((array) $now[$section] as $key => $val) {
            if (!is_numeric($val)) {
                continue;
            }
            $prev_val = (float) ($prev[$section][$key] ?? 0);
            $delta = $val - $prev_val;
            $pct = $prev_val > 0 ? round(($delta / $prev_val) * 100, 1) : null;
            $deltas[$section][$key] = ["now" => $val, "prev" => $prev_val, "pct" => $pct];
        }
    }
    return $deltas;
}

function upsellio_ai_generate_weekly_brief(): ?string
{
    if (function_exists("upsellio_ai_can_call") && !upsellio_ai_can_call("weekly_brief", 0.10)) {
        return null;
    }
    $snapshot = get_option("ups_ai_master_snapshot", []);
    if (empty($snapshot) || !is_array($snapshot)) {
        return null;
    }
    $previous = get_option("ups_ai_master_snapshot_prev_week", []);
    $deltas = upsellio_ai_compute_wow_deltas($snapshot, is_array($previous) ? $previous : []);

    $stalled_leads = get_posts([
        "post_type" => "lead",
        "posts_per_page" => 5,
        "tax_query" => [["taxonomy" => "lead_status", "field" => "slug", "terms" => ["qualified", "proposal"]]],
        "meta_query" => [["key" => "_upsellio_lead_score", "value" => 60, "compare" => ">="]],
        "orderby" => "modified",
        "order" => "ASC",
        "fields" => "ids",
    ]);
    $stalled = [];
    foreach ($stalled_leads as $lid) {
        $stalled[] = [
            "name" => get_the_title($lid),
            "company" => get_post_meta($lid, "_upsellio_lead_company", true),
            "days_silent" => floor((time() - get_post_modified_time("U", false, $lid)) / DAY_IN_SECONDS),
            "score" => (int) get_post_meta($lid, "_upsellio_lead_score", true),
            "edit_url" => admin_url("post.php?post=" . (int) $lid . "&action=edit"),
        ];
    }

    $system = <<<'EOT'
Jesteś asystentem strategicznym dla solo-operatora agencji B2B premium. Generujesz email "Poniedziałkowy Brief Sprzedażowy" w stylu Sebastiana Kelma — konkretny, bez korpomowy, oparty na liczbach.
Format: HTML email-ready:
<h2>Najważniejszy insight tygodnia</h2>
<h2>3 leady wymagające twojej akcji DZIŚ</h2>
<h2>3 oferty do dopilnowania</h2>
<h2>2 anomalie marketingowe do sprawdzenia</h2>
<h2>1 wygrana — co powtórzyć</h2>
<h2>Plan tygodnia (max 3 priorytety)</h2>
Ton: handlowiec mówi do handlowca. Tylko fakty.
EOT;

    $user = "MASTER SNAPSHOT (dziś):\n" . wp_json_encode($snapshot, JSON_UNESCAPED_UNICODE) . "\n\n";
    $user .= "DELTY WoW:\n" . wp_json_encode($deltas, JSON_UNESCAPED_UNICODE) . "\n\n";
    $user .= "STALLED LEADS:\n" . wp_json_encode($stalled, JSON_UNESCAPED_UNICODE) . "\n\n";
    $cache_split = ["cached" => $system, "dynamic" => $user];

    $GLOBALS["upsellio_ai_current_task"] = "weekly_brief";
    $brief = upsellio_anthropic_crm_send_user_prompt(
        "",
        2000,
        60,
        function_exists("upsellio_ai_model_for") ? upsellio_ai_model_for("weekly_brief") : null,
        $cache_split
    );
    if ($brief) {
        update_option("ups_ai_weekly_brief_" . current_time("Y-m-d"), $brief, false);
        update_option("ups_ai_master_snapshot_prev_week", $snapshot, false);
        $owner = get_user_by("id", (int) get_option("ups_ai_brief_recipient", 1));
        if ($owner) {
            wp_mail($owner->user_email, "🎯 Brief Sprzedażowy — " . current_time("Y-m-d"), $brief, ["Content-Type: text/html; charset=UTF-8"]);
        }
    }
    return $brief;
}

add_action("upsellio_ai_weekly_brief_cron", "upsellio_ai_generate_weekly_brief");
add_action("init", function () {
    if (!wp_next_scheduled("upsellio_ai_weekly_brief_cron")) {
        $next_monday = strtotime("next monday 07:00:00", current_time("timestamp"));
        wp_schedule_event($next_monday, "weekly", "upsellio_ai_weekly_brief_cron");
    }
});
