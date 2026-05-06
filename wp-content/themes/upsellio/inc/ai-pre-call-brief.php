<?php
if (!defined("ABSPATH")) {
    exit;
}

function upsellio_ai_find_similar_leads(array $lead, int $limit = 3): array
{
    $args = [
        "post_type" => "lead",
        "posts_per_page" => 30,
        "meta_query" => ["relation" => "AND"],
    ];
    if (!empty($lead["quiz_industry"])) {
        $args["meta_query"][] = ["key" => "_upsellio_lead_quiz_industry", "value" => $lead["quiz_industry"]];
    }
    $candidates = get_posts($args);
    $out = [];
    foreach ($candidates as $c) {
        if (!($c instanceof WP_Post)) {
            continue;
        }
        $statuses = wp_get_object_terms($c->ID, "lead_status", ["fields" => "slugs"]);
        $value = (float) get_post_meta($c->ID, "_upsellio_lead_close_value", true);
        $out[] = [
            "company" => get_post_meta($c->ID, "_upsellio_lead_company", true),
            "industry" => get_post_meta($c->ID, "_upsellio_lead_quiz_industry", true),
            "problem" => get_post_meta($c->ID, "_upsellio_lead_quiz_problem", true),
            "status" => $statuses[0] ?? "new",
            "close_value" => $value,
            "message_excerpt" => mb_substr((string) get_post_field("post_content", $c->ID), 0, 120),
        ];
        if (count($out) >= $limit) {
            break;
        }
    }
    return $out;
}

function upsellio_ai_generate_precall_brief(int $lead_id): ?string
{
    if (get_post_type($lead_id) !== "lead") {
        return null;
    }
    $existing = get_post_meta($lead_id, "_upsellio_pre_call_brief", true);
    $existing_at = (int) get_post_meta($lead_id, "_upsellio_pre_call_brief_at", true);
    if ($existing && (time() - $existing_at) < DAY_IN_SECONDS) {
        return (string) $existing;
    }
    if (function_exists("upsellio_ai_can_call_strict_global") && !upsellio_ai_can_call_strict_global("pre_call_brief", 0.10)) {
        return null;
    }
    if (function_exists("upsellio_ai_can_call") && !upsellio_ai_can_call("pre_call_brief", 0.10)) {
        return null;
    }

    $lead = [
        "name" => get_the_title($lead_id),
        "email" => get_post_meta($lead_id, "_upsellio_lead_email", true),
        "phone" => get_post_meta($lead_id, "_upsellio_lead_phone", true),
        "company" => get_post_meta($lead_id, "_upsellio_lead_company", true),
        "service" => get_post_meta($lead_id, "_upsellio_lead_service", true),
        "budget" => get_post_meta($lead_id, "_upsellio_lead_budget", true),
        "message" => get_post_field("post_content", $lead_id),
        "quiz_problem" => get_post_meta($lead_id, "_upsellio_lead_quiz_problem", true),
        "quiz_industry" => get_post_meta($lead_id, "_upsellio_lead_quiz_industry", true),
        "quiz_budget" => get_post_meta($lead_id, "_upsellio_lead_quiz_budget", true),
        "utm_source" => get_post_meta($lead_id, "_upsellio_lead_utm_source", true),
        "utm_campaign" => get_post_meta($lead_id, "_upsellio_lead_utm_campaign", true),
        "gclid" => get_post_meta($lead_id, "_upsellio_lead_gclid", true),
        "gsc_query" => get_post_meta($lead_id, "_upsellio_lead_gsc_likely_query", true),
        "gsc_top_queries" => get_post_meta($lead_id, "_upsellio_lead_gsc_top_queries", true),
        "landing_url" => get_post_meta($lead_id, "_upsellio_lead_landing_url", true),
        "form_origin" => get_post_meta($lead_id, "_upsellio_lead_form_origin", true),
        "form_variant" => get_post_meta($lead_id, "_upsellio_lead_form_variant", true),
        "score" => (int) get_post_meta($lead_id, "_upsellio_lead_score", true),
        "score_reason" => get_post_meta($lead_id, "_upsellio_lead_score_reason", true),
        "timeline" => get_post_meta($lead_id, "_upsellio_lead_timeline", true),
    ];
    $similar = upsellio_ai_find_similar_leads($lead, 3);
    $wins = get_option("ups_ai_wins_snapshot", []);
    $master_ctx = function_exists("upsellio_ai_master_context") ? upsellio_ai_master_context("full") : "";

    $system = <<<'EOT'
Jesteś asystentem 1:1 dla właściciela agencji B2B (Sebastian Kelm, Upsellio). Twoje zadanie: wygenerować 1-stronicowy brief PRZED 30-min calldiagnozy z leadem.

Format outputu — czysty HTML (do osadzenia w admin), bez <html>, sekcje:
<section class="brief-summary"><h3>Profil w 30 sekundach</h3><p>2-3 zdania kim jest klient, skąd przyszedł, co go boli</p></section>
<section class="brief-context"><h3>Kontekst dotarcia</h3><ul><li>Kanał: ...</li><li>Intencja: ...</li><li>Behavior: ...</li></ul></section>
<section class="brief-similar"><h3>Podobne leady w bazie</h3><p>Tabela: 3 podobne (firma, problem, status, wartość, krótki wniosek)</p></section>
<section class="brief-questions"><h3>3 pytania, które sugeruję zadać</h3><ol><li>...</li></ol></section>
<section class="brief-objections"><h3>Spodziewane obiekcje + odpowiedź</h3><ul><li><strong>Obiekcja:</strong> ... <br><strong>Co odpowiedzieć:</strong> ...</li></ul></section>
<section class="brief-recommendation"><h3>Moja rekomendacja</h3><p>1 zdanie: pakiet/audyt/odmowa + uzasadnienie + sugerowany cennik</p></section>
Pisz w stylu Sebastiana: konkretnie, bez korpomowy.
EOT;

    $user = "DANE LEADA:\n" . wp_json_encode($lead, JSON_UNESCAPED_UNICODE) . "\n\n";
    $user .= "PODOBNI W BAZIE:\n" . wp_json_encode($similar, JSON_UNESCAPED_UNICODE) . "\n\n";
    if (!empty($wins["offers"])) {
        $user .= "WINS SNAPSHOT:\n" . wp_json_encode(array_slice($wins["offers"], 0, 5), JSON_UNESCAPED_UNICODE) . "\n\n";
    }
    $cache_split = ["cached" => $system . "\n\n" . $master_ctx, "dynamic" => $user];
    $GLOBALS["upsellio_ai_current_task"] = "pre_call_brief";
    $model = function_exists("upsellio_ai_model_for") ? upsellio_ai_model_for("pre_call_brief") : null;
    $brief = upsellio_anthropic_crm_send_user_prompt("", 1500, 60, $model, $cache_split);
    if (!$brief) {
        return null;
    }
    update_post_meta($lead_id, "_upsellio_pre_call_brief", wp_kses_post($brief));
    update_post_meta($lead_id, "_upsellio_pre_call_brief_at", time());
    return $brief;
}

add_action("upsellio_ai_precall_cron", function () {
    $leads = get_posts([
        "post_type" => "lead",
        "posts_per_page" => 5,
        "tax_query" => [["taxonomy" => "lead_status", "field" => "slug", "terms" => ["qualified", "new"]]],
        "meta_query" => [
            ["key" => "_upsellio_lead_score", "value" => 60, "compare" => ">="],
            ["key" => "_upsellio_pre_call_brief", "compare" => "NOT EXISTS"],
        ],
        "fields" => "ids",
    ]);
    foreach ($leads as $lid) {
        upsellio_ai_generate_precall_brief((int) $lid);
    }
});

add_filter("cron_schedules", function ($s) {
    $s["every_fifteen_minutes"] = ["interval" => 900, "display" => "15 minut"];
    return $s;
});
add_action("init", function () {
    if (!wp_next_scheduled("upsellio_ai_precall_cron")) {
        wp_schedule_event(current_time("timestamp") + 60, "every_fifteen_minutes", "upsellio_ai_precall_cron");
    }
});

add_action("add_meta_boxes", function () {
    add_meta_box("ups_pre_call_brief", "📋 Brief AI przed callem", function ($post) {
        $brief = get_post_meta($post->ID, "_upsellio_pre_call_brief", true);
        $at = (int) get_post_meta($post->ID, "_upsellio_pre_call_brief_at", true);
        if ($brief) {
            echo "<small>Wygenerowano: " . esc_html(date("Y-m-d H:i", $at)) . "</small>";
            echo '<div style="max-height:600px;overflow:auto;">' . wp_kses_post($brief) . "</div>";
        } else {
            echo "<p>Brief jeszcze niegenerowany.</p>";
        }
        ?>
        <button type="button" class="button" onclick="
            this.disabled=true;this.textContent='Generuję...';
            fetch('<?php echo esc_url(admin_url("admin-ajax.php")); ?>', {
                method:'POST',
                headers:{'Content-Type':'application/x-www-form-urlencoded'},
                body:'action=ups_pre_call_brief_gen&lead_id=<?php echo (int) $post->ID; ?>&_wpnonce=<?php echo esc_attr(wp_create_nonce("ups_brief")); ?>'
            }).then(r=>r.json()).then(()=>location.reload());
        ">Wygeneruj teraz</button>
        <?php
    }, "lead", "normal", "high");
});

add_action("wp_ajax_ups_pre_call_brief_gen", function () {
    check_ajax_referer("ups_brief");
    $id = (int) ($_POST["lead_id"] ?? 0);
    if (!current_user_can("edit_post", $id)) {
        wp_send_json_error();
    }
    delete_post_meta($id, "_upsellio_pre_call_brief");
    $brief = upsellio_ai_generate_precall_brief($id);
    wp_send_json_success(["brief" => $brief]);
});
