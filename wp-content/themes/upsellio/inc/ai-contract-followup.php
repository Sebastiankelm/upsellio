<?php
if (!defined("ABSPATH")) {
    exit;
}

function upsellio_ai_classify_contract_engagement(array $events): string
{
    $opens = 0;
    $deep_reads = 0;
    $signed = false;
    $pdf_dl = false;
    foreach ($events as $e) {
        if (!is_array($e)) {
            continue;
        }
        if (($e["event"] ?? "") === "contract_opened") {
            $opens++;
        }
        if (($e["event"] ?? "") === "contract_read_90pct") {
            $deep_reads++;
        }
        if (($e["event"] ?? "") === "contract_pdf_downloaded") {
            $pdf_dl = true;
        }
        if (($e["event"] ?? "") === "contract_action" && strpos((string) ($e["detail"] ?? ""), "sign") !== false) {
            $signed = true;
        }
    }
    if ($opens === 0) {
        return "never_opened";
    }
    if ($signed) {
        return "signed";
    }
    if ($deep_reads > 0 && $pdf_dl) {
        return "almost_signed";
    }
    if ($opens >= 2) {
        return "stuck_after_open";
    }
    return "ghosted_after_read";
}

function upsellio_ai_draft_contract_followup(int $contract_id): ?string
{
    if (function_exists("upsellio_ai_can_call") && !upsellio_ai_can_call("contract_followup", 0.05)) {
        return null;
    }
    $events = get_post_meta($contract_id, "_ups_contract_track_events", true);
    if (!is_array($events)) {
        $events = [];
    }
    $engagement = upsellio_ai_classify_contract_engagement($events);
    $system = <<<'EOT'
Jesteś asystentem sprzedaży B2B (Sebastian, Upsellio). Generujesz krótki follow-up do klienta którego kontrakt utknął.
Format JSON: {"subject": "...", "body": "krótka, max 80 słów, polski, bez korpomowy"}
EOT;
    $offer_id = (int) get_post_meta($contract_id, "_ups_contract_offer_id", true);
    $offer_title = $offer_id ? get_the_title($offer_id) : "Współpraca";
    $client_name = (string) get_post_meta($contract_id, "_ups_contract_client_name", true);
    $user = sprintf(
        "Klient: %s\nKontrakt: %s\nEngagement: %s\nEvents:\n%s",
        $client_name,
        $offer_title,
        $engagement,
        wp_json_encode($events, JSON_UNESCAPED_UNICODE)
    );
    $GLOBALS["upsellio_ai_current_task"] = "contract_followup";
    $cache_split = [
        "cached" => $system . "\n\nZASADY STALE:\n- krotko i konkretnie\n- 1 jasne CTA\n- ton pomocny, bez presji",
        "dynamic" => $user,
    ];
    $resp = upsellio_anthropic_crm_send_user_prompt(
        "",
        500,
        18,
        function_exists("upsellio_ai_model_for") ? upsellio_ai_model_for("contract_followup") : null,
        $cache_split
    );
    if ($resp) {
        $json = json_decode(function_exists("upsellio_extract_json") ? upsellio_extract_json($resp) : $resp, true);
        if (is_array($json)) {
            update_post_meta($contract_id, "_ups_contract_ai_fu_draft", $json);
            update_post_meta($contract_id, "_ups_contract_ai_fu_at", time());
            update_post_meta($contract_id, "_ups_contract_ai_fu_engagement", $engagement);
        }
    }
    return $resp;
}

function upsellio_ai_contract_followup_check(): void
{
    $contracts = get_posts([
        "post_type" => "crm_contract",
        "posts_per_page" => 30,
        "meta_query" => [
            ["key" => "_ups_contract_status", "value" => ["sent", "opened", "reading"], "compare" => "IN"],
            ["key" => "_ups_contract_ai_fu_at", "compare" => "NOT EXISTS"],
        ],
    ]);
    foreach ($contracts as $c) {
        if (!($c instanceof WP_Post)) {
            continue;
        }
        $sent_at = (int) get_post_meta($c->ID, "_ups_contract_sent_at", true);
        if ($sent_at && (time() - $sent_at) < 3 * DAY_IN_SECONDS) {
            continue;
        }
        upsellio_ai_draft_contract_followup($c->ID);
    }
}

add_action("upsellio_ai_contract_followup_cron", "upsellio_ai_contract_followup_check");
add_action("init", function () {
    if (!wp_next_scheduled("upsellio_ai_contract_followup_cron")) {
        wp_schedule_event(current_time("timestamp") + HOUR_IN_SECONDS, "twicedaily", "upsellio_ai_contract_followup_cron");
    }
});
