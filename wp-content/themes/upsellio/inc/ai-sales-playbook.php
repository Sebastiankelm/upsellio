<?php
if (!defined("ABSPATH")) {
    exit;
}

function upsellio_ai_generate_sales_playbook(int $lead_id): ?array
{
    if (function_exists("upsellio_ai_can_call") && !upsellio_ai_can_call("sales_playbook", 0.20)) {
        return null;
    }
    $score = (int) get_post_meta($lead_id, "_upsellio_lead_score", true);
    if ($score < 60) {
        return null;
    }
    $existing = get_post_meta($lead_id, "_upsellio_sales_playbook", true);
    if (is_array($existing)) {
        return $existing;
    }
    $lead = [
        "company" => get_post_meta($lead_id, "_upsellio_lead_company", true),
        "industry" => get_post_meta($lead_id, "_upsellio_lead_quiz_industry", true),
        "problem" => get_post_meta($lead_id, "_upsellio_lead_quiz_problem", true),
        "budget" => get_post_meta($lead_id, "_upsellio_lead_quiz_budget", true),
        "message" => get_post_field("post_content", $lead_id),
        "gsc_query" => get_post_meta($lead_id, "_upsellio_lead_gsc_likely_query", true),
        "utm" => [
            "source" => get_post_meta($lead_id, "_upsellio_lead_utm_source", true),
            "campaign" => get_post_meta($lead_id, "_upsellio_lead_utm_campaign", true),
        ],
    ];
    $wins = get_option("ups_ai_wins_snapshot", []);
    $system = <<<'EOT'
Jesteś senior B2B sales coach. Generujesz 30-dniowy sales playbook dla konkretnego leada.
Format JSON:
{"summary":"...","messages":[{"day":1,"channel":"email|phone|linkedin","subject":"...","body":"...","purpose":"..."}],"case_study_to_share":"...","decision_questions":["..."]}
EOT;
    $user = "LEAD:\n" . wp_json_encode($lead, JSON_UNESCAPED_UNICODE) . "\n\n";
    $user .= "WINS PATTERNS:\n" . wp_json_encode(array_slice($wins["offers"] ?? [], 0, 5), JSON_UNESCAPED_UNICODE);

    $GLOBALS["upsellio_ai_current_task"] = "sales_playbook";
    $cache_split = [
        "cached" => $system . "\n\nRAMY STALE:\n- sekwencja 30 dni\n- miks kanalow\n- nacisk na next best action",
        "dynamic" => $user,
    ];
    $resp = upsellio_anthropic_crm_send_user_prompt(
        "",
        2500,
        60,
        function_exists("upsellio_ai_model_for") ? upsellio_ai_model_for("sales_playbook") : null,
        $cache_split
    );
    if (!$resp) {
        return null;
    }
    $json = json_decode(function_exists("upsellio_extract_json") ? upsellio_extract_json($resp) : $resp, true);
    if (is_array($json)) {
        update_post_meta($lead_id, "_upsellio_sales_playbook", $json);
        update_post_meta($lead_id, "_upsellio_sales_playbook_at", time());
    }
    return is_array($json) ? $json : null;
}

add_action("add_meta_boxes", function () {
    add_meta_box("ups_sales_playbook", "🎯 Playbook 30-dniowy", function ($post) {
        $playbook = get_post_meta($post->ID, "_upsellio_sales_playbook", true);
        if (!is_array($playbook)) {
            echo '<button type="button" class="button button-primary" onclick="
                this.disabled=true;
                fetch(\'' . esc_url(admin_url("admin-ajax.php")) . '\',{
                  method:\'POST\',
                  headers:{\'Content-Type\':\'application/x-www-form-urlencoded\'},
                  body:\'action=ups_gen_playbook&lead_id=' . (int) $post->ID . '&_wpnonce=' . esc_attr(wp_create_nonce("ups_pb")) . '\'
                }).then(()=>location.reload());
            ">Wygeneruj playbook AI</button>';
            return;
        }
        echo "<p><strong>Strategia:</strong> " . esc_html($playbook["summary"] ?? "") . "</p><ol>";
        foreach (($playbook["messages"] ?? []) as $m) {
            echo "<li><strong>D+" . (int) ($m["day"] ?? 0) . " (" . esc_html($m["channel"] ?? "") . "):</strong><br>";
            echo "<em>" . esc_html($m["subject"] ?? "") . "</em><br>";
            echo '<div style="background:#f8f8f8;padding:8px;margin:4px 0;">' . nl2br(esc_html($m["body"] ?? "")) . "</div>";
            echo "<small>Cel: " . esc_html($m["purpose"] ?? "") . "</small></li>";
        }
        echo "</ol>";
    }, "lead", "normal", "default");
});

add_action("wp_ajax_ups_gen_playbook", function () {
    check_ajax_referer("ups_pb");
    upsellio_ai_generate_sales_playbook((int) ($_POST["lead_id"] ?? 0));
    wp_send_json_success();
});
