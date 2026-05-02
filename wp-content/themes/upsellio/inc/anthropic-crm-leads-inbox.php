<?php

if (!defined("ABSPATH")) {
    exit;
}

if (!defined("UPSELLIO_ANTHROPIC_DEFAULT_MODEL")) {
    define("UPSELLIO_ANTHROPIC_DEFAULT_MODEL", "claude-haiku-4-5-20251001");
}

function upsellio_anthropic_crm_default_prompt_lead_score()
{
    return "Jesteś analitykiem B2B. Na podstawie treści leada z formularza strony oceń szanse sprzedaży i sugerowany etap lejka.\n"
        . "Odpowiedz WYŁĄCZNIE jednym obiektem JSON (bez markdown, bez komentarzy), dokładnie w formacie:\n"
        . "{\"lead_score\": <liczba całkowita 0-100>, \"lead_status\": \"<jeden z: {lead_status_list}>\"}\n\n"
        . "lead_score: 0 = spam / brak zainteresowania, 100 = bardzo gorący lead gotowy do rozmowy handlowej.\n"
        . "lead_status: new = świeży, contacted = warto szybki kontakt, qualified = jasna potrzeba i budżet, proposal = gotowość na ofertę.\n\n"
        . "Dane leada:\n{lead_blob}";
}

function upsellio_anthropic_crm_default_prompt_inbox_draft()
{
    return "Piszesz jako konsultant B2B agencji marketingowej (Polska). Na podstawie wątku e-mail z klientem przygotuj SZKIC odpowiedzi — profesjonalny, konkretny, bez HTML.\n"
        . "Odpowiedz WYŁĄCZNIE JSON: {\"reply_body\": \"...\", \"reply_subject\": \"...\" }\n"
        . "reply_subject: krótki temat (może być pusty \"\" jeśli wystarczy Re: z kontekstu).\n"
        . "reply_body: 2–8 zdań, ton partnerski, jedna jasna propozycja następnego kroku.\n\n"
        . "Etap deala (CRM): {offer_stage}\nTytuł oferty/dealu: {offer_title}\n\n"
        . "{hint_section}"
        . "Transkrypt wątku:\n{thread}";
}

function upsellio_anthropic_crm_default_prompt_inbox_followup()
{
    return "Jako konsultant B2B agencji marketingowej (PL) piszesz JEDNĄ krótką wiadomość follow-up.\n"
        . "Klient NIE otrzymał naszej odpowiedzi od około {hours_silence} godzin mimo swojej ostatniej wiadomości — ton uprzejmy, bez presji, bez oskarżeń, 3–6 zdań, bez HTML (tylko tekst).\n"
        . "Odpowiedz WYŁĄCZNIE JSON: {\"reply_body\": \"...\", \"reply_subject\": \"...\" }\n"
        . "reply_subject: opcjonalnie krótki temat (może \"\" — wtedy system użyje Re: tytułu oferty).\n"
        . "reply_body: sam tekst wiadomości.\n\n"
        . "Etap deala: {offer_stage}\nTytuł oferty: {offer_title}\n\n{channel_context}Transkrypt:\n{thread}";
}

function upsellio_anthropic_crm_default_prompt_offer_description()
{
    return "Na podstawie danych przygotuj zwięzły, sprzedażowy opis propozycji w języku polskim (2–5 zdań, konkret, bez HTML).\n"
        . "Odpowiedz WYŁĄCZNIE JSON: {\"description\": \"...\" }\n\n"
        . "Tytuł oferty: {offer_title}\nKlient: {client_name}\nKontekst / cena / notatki: {offer_context}";
}

/**
 * @param string $which lead_score|inbox_draft|inbox_followup|offer_description
 */
function upsellio_anthropic_crm_get_prompt_template($which)
{
    $which = sanitize_key((string) $which);
    $chains = [
        "lead_score" => ["ups_ai_prompt_lead_scoring", "ups_anthropic_prompt_lead_score"],
        "inbox_draft" => ["ups_ai_prompt_inbox_draft", "ups_anthropic_prompt_inbox_draft"],
        "inbox_followup" => ["ups_ai_prompt_followup", "ups_anthropic_prompt_inbox_followup"],
        "offer_description" => ["ups_anthropic_prompt_offer_description"],
    ];
    if (!isset($chains[$which])) {
        return "";
    }
    $stored = "";
    foreach ($chains[$which] as $opt_key) {
        $stored = trim((string) get_option($opt_key, ""));
        if ($stored !== "") {
            break;
        }
    }
    if ($stored === "") {
        if ($which === "lead_score") {
            $stored = upsellio_anthropic_crm_default_prompt_lead_score();
        } elseif ($which === "inbox_draft") {
            $stored = upsellio_anthropic_crm_default_prompt_inbox_draft();
        } elseif ($which === "inbox_followup") {
            $stored = upsellio_anthropic_crm_default_prompt_inbox_followup();
        } else {
            $stored = upsellio_anthropic_crm_default_prompt_offer_description();
        }
    }

    return (string) apply_filters("upsellio_anthropic_crm_prompt_" . $which, $stored);
}

/**
 * Podmienia placeholdery {klucz} w szablonie. Wszystkie wartości muszą być stringami.
 *
 * @param array<string, string> $vars
 */
function upsellio_anthropic_crm_apply_placeholders($template, array $vars)
{
    $template = (string) $template;
    $repl = [];
    foreach ($vars as $k => $v) {
        $k = sanitize_key((string) $k);
        if ($k === "") {
            continue;
        }
        $repl["{" . $k . "}"] = (string) $v;
    }
    if ($repl === []) {
        return $template;
    }
    uksort($repl, static function ($a, $b) {
        return strlen((string) $b) <=> strlen((string) $a);
    });

    return strtr($template, $repl);
}

/**
 * Kontekst firmy (stały prefiks) + treść zadania — wygodne pod cache po stronie Anthropic.
 *
 * @param string $which lead_score|inbox_draft|inbox_followup|offer_description
 * @param array<string, string> $vars
 */
function upsellio_anthropic_crm_compose_api_prompt($which, array $vars)
{
    $body = upsellio_anthropic_crm_apply_placeholders(upsellio_anthropic_crm_get_prompt_template($which), $vars);
    $company = trim((string) get_option("ups_ai_company_context", ""));
    if ($company === "") {
        $company = trim((string) get_option("ups_anthropic_company_context", ""));
    }
    if ($company !== "") {
        return $company . "\n\n--- Zadanie ---\n\n" . $body;
    }

    return $body;
}

function upsellio_anthropic_crm_api_key()
{
    if (defined("UPSELLIO_ANTHROPIC_API_KEY") && (string) UPSELLIO_ANTHROPIC_API_KEY !== "") {
        return (string) UPSELLIO_ANTHROPIC_API_KEY;
    }

    return trim((string) get_option("ups_anthropic_api_key", ""));
}

function upsellio_anthropic_crm_resolve_model()
{
    $model = trim((string) get_option("ups_anthropic_model", ""));
    if ($model === "") {
        $model = (string) UPSELLIO_ANTHROPIC_DEFAULT_MODEL;
    }

    return (string) apply_filters("upsellio_anthropic_inbound_model", $model);
}

function upsellio_anthropic_crm_extract_text_from_response_body($raw)
{
    if (!is_array($raw) || empty($raw["content"]) || !is_array($raw["content"])) {
        return "";
    }
    $piece = "";
    foreach ($raw["content"] as $block) {
        if (is_array($block) && ($block["type"] ?? "") === "text" && isset($block["text"])) {
            $piece .= " " . (string) $block["text"];
        }
    }

    return trim($piece);
}

/**
 * @param string|null $model_override Pusty string = domyślny model z ustawień CRM.
 * @param array<string, string>|null $cache_split Jeśli tablica z kluczami "cached" i "dynamic" (oba niepuste) — Prompt Caching (ephemeral) dla bloku "cached".
 *
 * @return string|null Assistant text or null on failure
 */
function upsellio_anthropic_crm_send_user_prompt($prompt, $max_tokens = 768, $timeout = 28, $model_override = null, $cache_split = null)
{
    $api_key = upsellio_anthropic_crm_api_key();
    if ($api_key === "") {
        return null;
    }
    $model = $model_override !== null && trim((string) $model_override) !== ""
        ? trim((string) $model_override)
        : upsellio_anthropic_crm_resolve_model();
    $prompt = (string) $prompt;
    if (function_exists("mb_substr")) {
        $prompt = mb_substr($prompt, 0, 48000, "UTF-8");
    } else {
        $prompt = substr($prompt, 0, 48000);
    }

    $use_cache = is_array($cache_split)
        && trim((string) ($cache_split["cached"] ?? "")) !== ""
        && trim((string) ($cache_split["dynamic"] ?? "")) !== "";
    $cached_raw = $use_cache ? trim((string) $cache_split["cached"]) : "";
    $dynamic_raw = $use_cache ? trim((string) $cache_split["dynamic"]) : "";
    if ($use_cache) {
        if (function_exists("mb_substr")) {
            $cached_raw = mb_substr($cached_raw, 0, 20000, "UTF-8");
            $dynamic_raw = mb_substr($dynamic_raw, 0, 28000, "UTF-8");
        } else {
            $cached_raw = substr($cached_raw, 0, 20000);
            $dynamic_raw = substr($dynamic_raw, 0, 28000);
        }
    }

    $headers = [
        "x-api-key" => $api_key,
        "anthropic-version" => "2023-06-01",
        "content-type" => "application/json",
    ];
    if ($use_cache) {
        $headers["anthropic-beta"] = "prompt-caching-2024-07-31";
    }

    $user_content = $use_cache
        ? [
            [
                "type" => "text",
                "text" => $cached_raw,
                "cache_control" => ["type" => "ephemeral"],
            ],
            [
                "type" => "text",
                "text" => $dynamic_raw,
            ],
        ]
        : $prompt;

    $response = wp_remote_post(
        "https://api.anthropic.com/v1/messages",
        [
            "timeout" => $timeout,
            "headers" => $headers,
            "body" => wp_json_encode([
                "model" => $model,
                "max_tokens" => max(64, min(4096, (int) $max_tokens)),
                "messages" => [
                    ["role" => "user", "content" => $user_content],
                ],
            ]),
        ]
    );
    if (is_wp_error($response)) {
        return null;
    }
    $code = (int) wp_remote_retrieve_response_code($response);
    if ($code < 200 || $code >= 300) {
        return null;
    }
    $raw = json_decode((string) wp_remote_retrieve_body($response), true);
    if (!is_array($raw)) {
        return null;
    }
    $text = upsellio_anthropic_crm_extract_text_from_response_body($raw);

    return $text !== "" ? $text : null;
}

function upsellio_anthropic_crm_parse_json_object($text)
{
    $text = trim((string) $text);
    if ($text === "") {
        return null;
    }
    if (preg_match("/\{[\s\S]*\}/", $text, $m)) {
        $j = json_decode($m[0], true);
        if (is_array($j)) {
            return $j;
        }
    }

    return null;
}

/**
 * @param array<int, mixed> $thread
 */
function upsellio_anthropic_crm_inbox_thread_transcript(array $thread, $max_msgs = 35, $body_chars = 3500)
{
    $max_msgs = max(1, min(80, (int) $max_msgs));
    $body_chars = max(200, min(8000, (int) $body_chars));
    $slice = array_slice($thread, -$max_msgs);
    $lines = [];
    foreach ($slice as $msg) {
        if (!is_array($msg)) {
            continue;
        }
        $dir = ($msg["direction"] ?? "") === "out" ? "TY (agencja)" : "KLIENT";
        $sub = (string) ($msg["subject"] ?? "");
        $body = (string) ($msg["body_plain"] ?? "");
        if (function_exists("mb_substr")) {
            $body = mb_substr($body, 0, $body_chars, "UTF-8");
        } else {
            $body = substr($body, 0, $body_chars);
        }
        $lines[] = $dir . " | " . $sub . "\n" . $body;
    }

    return implode("\n\n---\n\n", $lines);
}

/**
 * @param array<int, mixed> $thread
 * @return array<string, mixed>|null
 */
function upsellio_anthropic_crm_inbox_thread_last_inbound(array $thread)
{
    for ($i = count($thread) - 1; $i >= 0; $i--) {
        if (!is_array($thread[$i])) {
            continue;
        }
        if (($thread[$i]["direction"] ?? "") === "in") {
            return $thread[$i];
        }
    }

    return null;
}

function upsellio_crm_maybe_schedule_wp_lead_ai_classification($lead_id)
{
    $lead_id = (int) $lead_id;
    if ($lead_id <= 0) {
        return;
    }
    if ((string) get_option("ups_anthropic_wp_lead_form_enabled", "0") !== "1") {
        return;
    }
    if (upsellio_anthropic_crm_api_key() === "") {
        return;
    }
    if (get_post_type($lead_id) !== "lead") {
        return;
    }

    wp_schedule_single_event(time() + 1, "upsellio_crm_run_ai_wp_lead_classification", [$lead_id]);
    if (function_exists("spawn_cron")) {
        spawn_cron();
    }
}

add_action("upsellio_crm_contact_lead_created", "upsellio_crm_maybe_schedule_wp_lead_ai_classification", 10, 1);

function upsellio_crm_run_ai_wp_lead_classification($lead_id)
{
    $lead_id = (int) $lead_id;
    if ($lead_id <= 0 || get_post_type($lead_id) !== "lead") {
        return;
    }
    if ((string) get_option("ups_anthropic_wp_lead_form_enabled", "0") !== "1") {
        return;
    }
    if (upsellio_anthropic_crm_api_key() === "") {
        return;
    }

    $post = get_post($lead_id);
    if (!($post instanceof WP_Post)) {
        return;
    }

    $allowed_status = ["new", "contacted", "qualified", "proposal"];
    $status_list = implode(", ", $allowed_status);

    $name = (string) $post->post_title;
    $email = (string) get_post_meta($lead_id, "_upsellio_lead_email", true);
    $phone = (string) get_post_meta($lead_id, "_upsellio_lead_phone", true);
    $company = (string) get_post_meta($lead_id, "_upsellio_lead_company", true);
    $service = (string) get_post_meta($lead_id, "_upsellio_lead_service", true);
    $budget = (string) get_post_meta($lead_id, "_upsellio_lead_budget", true);
    $goal = (string) get_post_meta($lead_id, "_upsellio_lead_goal", true);
    $message = (string) $post->post_content;
    $origin = (string) get_post_meta($lead_id, "_upsellio_lead_form_origin", true);

    $blob = "Tytul: {$name}\nEmail: {$email}\nTel: {$phone}\nFirma: {$company}\nUsluga: {$service}\nBudzet: {$budget}\nCel: {$goal}\nZrodlo formularza: {$origin}\n\nWiadomosc:\n{$message}";

    $utm_src = sanitize_text_field((string) get_post_meta($lead_id, "_upsellio_lead_utm_source", true));
    $utm_cmp = sanitize_text_field((string) get_post_meta($lead_id, "_upsellio_lead_utm_campaign", true));
    if (function_exists("upsellio_automation_format_ga4_channel_for_ai")) {
        $ch_line = upsellio_automation_format_ga4_channel_for_ai($utm_src, $utm_cmp);
        if ($ch_line !== "") {
            $blob .= "\n\nDane kanału marketingowego:\n" . $ch_line;
        }
    }

    $prompt = upsellio_anthropic_crm_compose_api_prompt("lead_score", [
        "lead_status_list" => $status_list,
        "lead_blob" => $blob,
        "lead_data" => $blob,
        "lead_name" => $name,
        "lead_email" => $email,
        "lead_phone" => $phone,
        "lead_company" => $company,
        "lead_service" => $service,
        "lead_budget" => $budget,
        "lead_goal" => $goal,
        "lead_message" => $message,
        "lead_form_origin" => $origin,
    ]);

    $raw = upsellio_anthropic_crm_send_user_prompt($prompt, 128, 22);
    if ($raw === null) {
        if (function_exists("upsellio_crm_add_timeline_event")) {
            upsellio_crm_add_timeline_event($lead_id, "ai_score", "Klasyfikacja AI: brak odpowiedzi API.");
        }

        return;
    }

    $data = upsellio_anthropic_crm_parse_json_object($raw);
    if (!is_array($data)) {
        if (function_exists("upsellio_crm_add_timeline_event")) {
            upsellio_crm_add_timeline_event($lead_id, "ai_score", "Klasyfikacja AI: niepoprawny format odpowiedzi.");
        }

        return;
    }

    $score = isset($data["lead_score"]) ? (int) $data["lead_score"] : -1;
    $status_slug = isset($data["lead_status"]) ? sanitize_key((string) $data["lead_status"]) : "";
    if ($score < 0 || $score > 100) {
        if (function_exists("upsellio_crm_add_timeline_event")) {
            upsellio_crm_add_timeline_event($lead_id, "ai_score", "Klasyfikacja AI: poza zakresem score.");
        }

        return;
    }
    if (!in_array($status_slug, $allowed_status, true)) {
        $status_slug = "new";
    }

    update_post_meta($lead_id, "_upsellio_lead_score", $score);

    if (function_exists("upsellio_crm_get_term_id_by_slug")) {
        $term_id = upsellio_crm_get_term_id_by_slug("lead_status", $status_slug);
        if ($term_id > 0) {
            wp_set_object_terms($lead_id, [$term_id], "lead_status", false);
        }
    }

    if (function_exists("upsellio_crm_add_timeline_event")) {
        upsellio_crm_add_timeline_event(
            $lead_id,
            "ai_score",
            "AI: score " . $score . ", sugerowany status: " . $status_slug . "."
        );
    }
}

add_action("upsellio_crm_run_ai_wp_lead_classification", "upsellio_crm_run_ai_wp_lead_classification", 10, 1);

function upsellio_crm_inbox_ai_draft_reply_ajax()
{
    if (!function_exists("upsellio_crm_app_user_can_access") || !upsellio_crm_app_user_can_access()) {
        wp_send_json_error(["message" => "forbidden"], 403);
    }
    $nonce = isset($_POST["nonce"]) ? sanitize_text_field(wp_unslash($_POST["nonce"])) : "";
    if (!wp_verify_nonce($nonce, "ups_crm_app_action")) {
        wp_send_json_error(["message" => "bad_nonce"], 403);
    }
    if ((string) get_option("ups_anthropic_inbox_draft_enabled", "0") !== "1") {
        wp_send_json_error(["message" => "disabled"], 400);
    }
    if (upsellio_anthropic_crm_api_key() === "") {
        wp_send_json_error(["message" => "no_key"], 400);
    }

    $offer_id = isset($_POST["offer_id"]) ? (int) wp_unslash($_POST["offer_id"]) : 0;
    if ($offer_id <= 0 || get_post_type($offer_id) !== "crm_offer") {
        wp_send_json_error(["message" => "bad_offer"], 400);
    }
    if (!current_user_can("edit_post", $offer_id)) {
        wp_send_json_error(["message" => "cap"], 403);
    }

    $thread = get_post_meta($offer_id, "_ups_offer_inbox_thread", true);
    if (!is_array($thread)) {
        $thread = [];
    }

    $transcript = upsellio_anthropic_crm_inbox_thread_transcript($thread, 35, 3500);
    if ($transcript === "") {
        wp_send_json_error(["message" => "empty_thread"], 400);
    }

    $hint = isset($_POST["hint"]) ? sanitize_textarea_field(wp_unslash($_POST["hint"])) : "";
    if (function_exists("mb_substr")) {
        $hint = mb_substr($hint, 0, 1200, "UTF-8");
    } else {
        $hint = substr($hint, 0, 1200);
    }

    $title = get_the_title($offer_id);
    $stage = (string) get_post_meta($offer_id, "_ups_offer_stage", true);
    $stage_disp = $stage !== "" ? $stage : "nieznany";
    $hint_section = $hint !== "" ? "Notatki handlowca (opcjonalnie):\n" . $hint . "\n\n" : "";

    $prompt = upsellio_anthropic_crm_compose_api_prompt("inbox_draft", [
        "offer_title" => $title,
        "offer_stage" => $stage_disp,
        "stage" => $stage_disp,
        "thread" => $transcript,
        "hint_section" => $hint_section,
        "hint_block" => $hint_section,
        "hint" => $hint,
    ]);

    $raw = upsellio_anthropic_crm_send_user_prompt($prompt, 900, 35);
    if ($raw === null) {
        wp_send_json_error(["message" => "api"], 502);
    }
    $data = upsellio_anthropic_crm_parse_json_object($raw);
    if (!is_array($data) || !isset($data["reply_body"])) {
        wp_send_json_error(["message" => "parse"], 502);
    }
    $body = sanitize_textarea_field((string) $data["reply_body"]);
    $subject = isset($data["reply_subject"]) ? sanitize_text_field((string) $data["reply_subject"]) : "";
    if ($body === "") {
        wp_send_json_error(["message" => "empty_body"], 502);
    }

    wp_send_json_success([
        "reply_body" => $body,
        "reply_subject" => $subject,
    ]);
}

add_action("wp_ajax_upsellio_inbox_ai_draft", "upsellio_crm_inbox_ai_draft_reply_ajax");

function upsellio_crm_ai_register_inbox_followup_cron()
{
    if ((string) get_option("ups_anthropic_inbox_auto_followup_enabled", "0") !== "1") {
        return;
    }
    if (!wp_next_scheduled("upsellio_crm_ai_inbox_followup_hourly")) {
        wp_schedule_event(time() + 120, "hourly", "upsellio_crm_ai_inbox_followup_hourly");
    }
}

add_action("init", "upsellio_crm_ai_register_inbox_followup_cron", 35);

function upsellio_crm_ai_inbox_followup_hourly_run()
{
    if ((string) get_option("ups_anthropic_inbox_auto_followup_enabled", "0") !== "1") {
        return;
    }
    if (upsellio_anthropic_crm_api_key() === "") {
        return;
    }
    if (!function_exists("upsellio_followup_send_html_mail") || !function_exists("upsellio_inbox_append_message")) {
        return;
    }

    $hours = (int) get_option("ups_anthropic_inbox_auto_followup_hours", 24);
    if ($hours <= 0) {
        $hours = 24;
    }
    $hours = max(6, min(168, $hours));
    $threshold = time() - ($hours * HOUR_IN_SECONDS);
    $dry_run = (string) get_option("ups_anthropic_inbox_auto_followup_dry_run", "0") === "1";

    $max_run = (int) apply_filters("upsellio_crm_ai_followup_max_offers_per_hour", 8);
    $max_run = max(1, min(25, $max_run));

    $offers = get_posts([
        "post_type" => "crm_offer",
        "post_status" => ["publish", "private"],
        "posts_per_page" => 40,
        "orderby" => "modified",
        "order" => "ASC",
        "meta_query" => [
            [
                "key" => "_ups_offer_inbox_last_direction",
                "value" => "in",
            ],
        ],
    ]);

    $sent_count = 0;
    foreach ($offers as $post) {
        if ($sent_count >= $max_run) {
            break;
        }
        $offer_id = (int) $post->ID;
        $deal_status = (string) get_post_meta($offer_id, "_ups_offer_status", true);
        if ($deal_status !== "" && $deal_status !== "open") {
            continue;
        }

        $thread = get_post_meta($offer_id, "_ups_offer_inbox_thread", true);
        if (!is_array($thread) || $thread === []) {
            continue;
        }
        $last_in = upsellio_anthropic_crm_inbox_thread_last_inbound($thread);
        if ($last_in === null || !is_array($last_in)) {
            continue;
        }
        $msg_id = (string) ($last_in["id"] ?? "");
        if ($msg_id === "") {
            $msg_id = "legacy_" . md5((string) ($last_in["ts"] ?? "") . (string) ($last_in["body_plain"] ?? ""));
        }
        $sent_for = (string) get_post_meta($offer_id, "_ups_offer_ai_fu_sent_msg_id", true);
        if ($sent_for === $msg_id) {
            continue;
        }
        if ($dry_run && (string) get_post_meta($offer_id, "_ups_offer_ai_fu_dry_msg_id", true) === $msg_id) {
            continue;
        }

        $in_ts = strtotime((string) ($last_in["ts"] ?? ""));
        if ($in_ts === false || $in_ts > $threshold) {
            continue;
        }

        $prefill = function_exists("upsellio_inbox_reply_prefill") ? upsellio_inbox_reply_prefill($offer_id) : ["to" => "", "cc" => ""];
        $to_field = (string) ($prefill["to"] ?? "");
        $cc_field = (string) ($prefill["cc"] ?? "");
        $to_emails = function_exists("upsellio_inbox_parse_email_field") ? upsellio_inbox_parse_email_field($to_field) : [];
        if ($to_emails === []) {
            continue;
        }
        $cc_emails = function_exists("upsellio_inbox_parse_email_field") ? upsellio_inbox_parse_email_field($cc_field) : [];

        $title = get_the_title($offer_id);
        $stage = (string) get_post_meta($offer_id, "_ups_offer_stage", true);
        $transcript = upsellio_anthropic_crm_inbox_thread_transcript($thread, 38, 4000);
        if ($transcript === "") {
            continue;
        }

        $stage_disp = $stage !== "" ? $stage : "nieznany";
        $client_id = (int) get_post_meta($offer_id, "_ups_offer_client_id", true);
        $client_name = $client_id > 0 ? get_the_title($client_id) : "";
        $last_msg = (string) ($last_in["body_plain"] ?? "");
        if (function_exists("mb_substr")) {
            $last_msg = mb_substr($last_msg, 0, 2000, "UTF-8");
        } else {
            $last_msg = substr($last_msg, 0, 2000);
        }
        $days_silent = (string) max(1, (int) ceil($hours / 24));
        $utm_src = sanitize_text_field((string) get_post_meta($offer_id, "_ups_offer_utm_source", true));
        $utm_cmp = sanitize_text_field((string) get_post_meta($offer_id, "_ups_offer_utm_campaign", true));
        $ch_ctx = function_exists("upsellio_automation_format_ga4_channel_for_ai")
            ? upsellio_automation_format_ga4_channel_for_ai($utm_src, $utm_cmp)
            : "";
        $channel_context = $ch_ctx !== ""
            ? "Kontekst kanału marketingowego (GA4 / CRM):\n" . $ch_ctx . "\n\n"
            : "";

        $prompt = upsellio_anthropic_crm_compose_api_prompt("inbox_followup", [
            "offer_title" => $title,
            "offer_stage" => $stage_disp,
            "stage" => $stage_disp,
            "thread" => $transcript,
            "hours_silence" => (string) (int) $hours,
            "client_name" => $client_name,
            "last_message" => $last_msg,
            "days_silent" => $days_silent,
            "channel_context" => $channel_context,
        ]);

        $raw = upsellio_anthropic_crm_send_user_prompt($prompt, 640, 32);
        if ($raw === null) {
            continue;
        }
        $data = upsellio_anthropic_crm_parse_json_object($raw);
        if (!is_array($data) || !isset($data["reply_body"])) {
            continue;
        }
        $body_plain = sanitize_textarea_field((string) $data["reply_body"]);
        $subject_in = isset($data["reply_subject"]) ? sanitize_text_field((string) $data["reply_subject"]) : "";
        if ($body_plain === "") {
            continue;
        }

        $subject = $subject_in !== "" ? $subject_in : ("Re: " . $title);
        if ($dry_run) {
            $preview = wp_trim_words($body_plain, 120, "…");
            if (function_exists("upsellio_offer_add_timeline_event")) {
                upsellio_offer_add_timeline_event(
                    $offer_id,
                    "ai_followup_dry_run",
                    "DRY RUN — wiadomość NIE została wysłana. Temat: " . $subject . "\n\n" . $preview
                );
            }
            if (function_exists("upsellio_mailbox_log")) {
                upsellio_mailbox_log(
                    "mail",
                    "info",
                    "AI follow-up DRY RUN (bez wysyłki) — oferta #" . $offer_id,
                    "Do: " . implode(", ", $to_emails) . "\nTemat: " . $subject . "\n\n" . $body_plain
                );
            }
            update_post_meta($offer_id, "_ups_offer_ai_fu_dry_msg_id", $msg_id);
            $sent_count++;

            continue;
        }

        $html_core =
            "<html><head><meta charset=\"utf-8\"></head><body>" .
            nl2br(esc_html($body_plain)) .
            "</body></html>";

        $settings = upsellio_followup_get_sender_settings();
        $mail_args = [
            "crm_smtp" => true,
            "to" => $to_emails,
            "cc" => $cc_emails,
            "bcc" => [],
        ];

        $primary_to = $to_emails[0];
        $sent = upsellio_followup_send_html_mail($primary_to, $subject, $html_core, $mail_args);
        if (!$sent) {
            if (function_exists("upsellio_mailbox_log")) {
                upsellio_mailbox_log("mail", "warning", "AI follow-up: wysylka nieudana dla oferty #" . $offer_id, "");
            }
            continue;
        }

        $html_for_meta = function_exists("upsellio_followup_finalize_crm_html")
            ? upsellio_followup_finalize_crm_html($html_core, $mail_args)
            : $html_core;

        upsellio_inbox_append_message($offer_id, [
            "direction" => "out",
            "from" => (string) ($settings["from_email"] ?? ""),
            "to" => implode(", ", $to_emails),
            "cc" => implode(", ", $cc_emails),
            "bcc" => "",
            "subject" => $subject,
            "body_plain" => $body_plain,
            "body_html" => $html_for_meta,
            "source" => "ai_followup_auto",
            "read" => true,
        ]);

        update_post_meta($offer_id, "_ups_offer_ai_fu_sent_msg_id", $msg_id);
        delete_post_meta($offer_id, "_ups_offer_ai_fu_dry_msg_id");
        update_post_meta(
            $offer_id,
            "_ups_offer_followup_snooze_until",
            gmdate("Y-m-d H:i:s", time() + (48 * HOUR_IN_SECONDS))
        );
        if (function_exists("upsellio_inbox_sync_last_direction_from_thread")) {
            upsellio_inbox_sync_last_direction_from_thread($offer_id);
        }
        if (function_exists("upsellio_offer_add_timeline_event")) {
            upsellio_offer_add_timeline_event(
                $offer_id,
                "ai_followup_sent",
                "Wysłano automatyczny follow-up AI (Claude) dla ciszy >" . $hours . "h."
            );
        }
        if (function_exists("upsellio_mailbox_log")) {
            upsellio_mailbox_log(
                "mail",
                "info",
                "AI follow-up wysłany dla oferty #" . $offer_id,
                "Do: " . implode(", ", $to_emails)
            );
        }
        $sent_count++;
    }
}

/**
 * Pełny prompt „user” do Messages API — opis oferty (do podpięcia w edycji oferty / automatach).
 *
 * @param array<string, string> $vars Klucze: offer_title, client_name, offer_context
 */
function upsellio_anthropic_crm_build_offer_description_prompt(array $vars)
{
    return upsellio_anthropic_crm_compose_api_prompt("offer_description", [
        "offer_title" => (string) ($vars["offer_title"] ?? ""),
        "client_name" => (string) ($vars["client_name"] ?? ""),
        "offer_context" => (string) ($vars["offer_context"] ?? ""),
    ]);
}

add_action("upsellio_crm_ai_inbox_followup_hourly", "upsellio_crm_ai_inbox_followup_hourly_run");
