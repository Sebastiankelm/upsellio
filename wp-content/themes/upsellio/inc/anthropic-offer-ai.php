<?php

if (!defined("ABSPATH")) {
    exit;
}

/**
 * Agregacja surowego logu `_ups_offer_events` do meta używanych przez AI / raporty.
 */
function upsellio_offer_ai_aggregate_events(int $offer_id): void
{
    $offer_id = (int) $offer_id;
    if ($offer_id <= 0 || get_post_type($offer_id) !== "crm_offer" || !function_exists("upsellio_offer_build_analytics_summary")) {
        return;
    }
    $log = get_post_meta($offer_id, "_ups_offer_events", true);
    if (!is_array($log) || $log === []) {
        return;
    }

    $summary = upsellio_offer_build_analytics_summary($offer_id);
    $views = (int) ($summary["views"] ?? 0);
    $pricing_sec = (int) ($summary["pricing_seconds"] ?? 0);
    $sections = is_array($summary["section_views"] ?? null) ? $summary["section_views"] : [];

    $cta = "";
    $commit = "";
    foreach (array_reverse($log) as $ev) {
        if (!is_array($ev)) {
            continue;
        }
        $name = sanitize_key((string) ($ev["event"] ?? ""));
        $sid = sanitize_key((string) ($ev["section_id"] ?? ""));
        if ($name === "offer_commit_selected" && $commit === "" && $sid !== "") {
            $commit = $sid;
        }
        if ($name === "offer_cta_click" && $cta === "" && $sid !== "") {
            $cta = $sid;
        }
    }

    if ($views > 0) {
        update_post_meta($offer_id, "_ups_offer_view_count", $views);
    }
    if ($pricing_sec > 0) {
        update_post_meta($offer_id, "_ups_offer_pricing_engagement_seconds", $pricing_sec);
    }
    if ($cta !== "") {
        update_post_meta($offer_id, "_ups_offer_cta_clicks", $cta);
    }
    if ($commit !== "") {
        update_post_meta($offer_id, "_ups_offer_commit_selected", $commit);
    }
    if ($sections !== []) {
        update_post_meta($offer_id, "_ups_offer_section_views", $sections);
    }
}

/**
 * Krótki opis zachowania na publicznej stronie oferty (dla draftu / follow-upu AI).
 */
function upsellio_offer_ai_behavior_context(int $offer_id): string
{
    $offer_id = (int) $offer_id;
    if ($offer_id <= 0 || !function_exists("upsellio_offer_build_analytics_summary")) {
        return "";
    }

    if (function_exists("upsellio_offer_ai_aggregate_events")) {
        upsellio_offer_ai_aggregate_events($offer_id);
    }

    $summary = upsellio_offer_build_analytics_summary($offer_id);
    $views = (int) ($summary["views"] ?? 0);
    $last_seen = (string) get_post_meta($offer_id, "_ups_offer_last_seen", true);
    $pricing_sec = (int) ($summary["pricing_seconds"] ?? 0);
    $cta_clicks = (int) ($summary["cta_clicks"] ?? 0);
    $commit = (string) get_post_meta($offer_id, "_ups_offer_commit_selected", true);
    $cta_label = (string) get_post_meta($offer_id, "_ups_offer_cta_clicks", true);
    $sections = get_post_meta($offer_id, "_ups_offer_section_views", true);
    if (!is_array($sections)) {
        $sections = is_array($summary["section_views"] ?? null) ? $summary["section_views"] : [];
    }

    $intent = (int) get_post_meta($offer_id, "_ups_offer_intent_score", true);
    $hot = (string) get_post_meta($offer_id, "_ups_offer_hot_offer", true) === "1";
    $action_rec = trim((string) get_post_meta($offer_id, "_ups_offer_action_recommendation", true));

    if ($views === 0 && $last_seen === "" && $pricing_sec <= 0 && $cta_clicks === 0 && $commit === "") {
        return "";
    }

    $lines = [];
    if ($views > 0) {
        $lines[] = "Oferta otwarta: {$views}×" . ($last_seen !== "" ? " (ostatnia aktywność: {$last_seen})" : "");
    } elseif ($last_seen !== "") {
        $lines[] = "Ostatnia aktywność na stronie oferty: {$last_seen}";
    }
    if ($pricing_sec > 30) {
        $lines[] = "Czas na sekcji cennik (szac.): {$pricing_sec}s — klient analizował wycenę";
    }
    if ($commit !== "") {
        $lines[] = "Wybrana opcja / pakiet (radio): {$commit}";
    }
    if ($cta_label !== "") {
        $lines[] = "Kliknięty CTA / etykieta: {$cta_label}";
    } elseif ($cta_clicks > 0) {
        $lines[] = "Kliknięcia CTA: {$cta_clicks}";
    }
    if (is_array($sections) && $sections !== []) {
        arsort($sections);
        $top = array_slice(array_keys($sections), 0, 3);
        if ($top !== []) {
            $lines[] = "Najczęściej oglądane sekcje: " . implode(", ", $top);
        }
    }
    if ($hot) {
        $lines[] = "Oferta oznaczona jako HOT (intent ok. {$intent}/100)";
    } elseif ($intent > 0) {
        $lines[] = "Intent score: {$intent}/100";
    }
    if ($action_rec !== "") {
        $lines[] = "Rekomendacja systemu: {$action_rec}";
    }

    return $lines !== [] ? implode(" · ", $lines) : "";
}

/**
 * Skrócone wzorce z ostatnich wygranych ofert (prompt autofill).
 */
function upsellio_offer_ai_won_patterns(int $limit = 3): string
{
    $limit = max(1, min(8, $limit));
    if (!post_type_exists("crm_offer")) {
        return "";
    }
    $won = get_posts([
        "post_type" => "crm_offer",
        "post_status" => ["publish", "private"],
        "posts_per_page" => $limit,
        "orderby" => "modified",
        "order" => "DESC",
        "fields" => "ids",
        "meta_query" => [
            [
                "key" => "_ups_offer_status",
                "value" => "won",
            ],
        ],
    ]);
    if ($won === []) {
        return "";
    }
    $lines = [];
    foreach ($won as $wid) {
        $wid = (int) $wid;
        $title = get_the_title($wid);
        $price = (string) get_post_meta($wid, "_ups_offer_price", true);
        $won_val = (float) get_post_meta($wid, "_ups_offer_won_value", true);
        $win_reason = (string) get_post_meta($wid, "_ups_offer_win_reason", true);
        $inc = (string) get_post_meta($wid, "_ups_offer_include_lines", true);
        if (function_exists("mb_substr")) {
            $inc = mb_substr($inc, 0, 140, "UTF-8");
        } else {
            $inc = substr($inc, 0, 140);
        }
        $price_disp = $won_val > 0 ? (string) round($won_val) . " PLN (wygrana)" : ($price !== "" ? $price . " PLN" : "");
        $lines[] = "- " . $title . ($price_disp !== "" ? " — " . $price_disp : "")
            . ($win_reason !== "" ? " — powód: " . $win_reason : "")
            . ($inc !== "" ? " — fragment zakresu: " . $inc : "");
    }

    return implode("\n", $lines);
}

/**
 * Snapshot wygranych dla scoringu leadów (cache w opcjach, max. raz / 24h pełny przegląd).
 *
 * @return array<string, mixed>
 */
function upsellio_ai_build_wins_snapshot(): array
{
    $cached = get_option("ups_ai_wins_snapshot", []);
    if (!is_array($cached)) {
        $cached = [];
    }
    $built = (string) get_option("ups_ai_wins_snapshot_built", "");
    $built_ts = $built !== "" ? strtotime($built) : 0;
    if ($built_ts > 0 && (time() - $built_ts) < DAY_IN_SECONDS) {
        return $cached;
    }

    if (!post_type_exists("crm_offer")) {
        return [];
    }

    $offers = get_posts([
        "post_type" => "crm_offer",
        "post_status" => ["publish", "private"],
        "posts_per_page" => 40,
        "fields" => "ids",
        "meta_query" => [
            [
                "key" => "_ups_offer_status",
                "value" => "won",
            ],
        ],
    ]);

    $wins = [];
    $win_reasons = [];
    $price_points = [];
    $industries = [];

    foreach ($offers as $oid) {
        $oid = (int) $oid;
        $price = (float) get_post_meta($oid, "_ups_offer_won_value", true);
        if ($price <= 0) {
            $raw = (string) get_post_meta($oid, "_ups_offer_price", true);
            $raw = preg_replace("/[^\d.,\-]/u", "", $raw);
            $raw = str_replace(",", ".", $raw);
            $price = (float) $raw;
        }
        $reason = trim((string) get_post_meta($oid, "_ups_offer_win_reason", true));
        $cid = (int) get_post_meta($oid, "_ups_offer_client_id", true);
        $ind = $cid > 0 ? trim((string) get_post_meta($cid, "_ups_client_industry", true)) : "";

        $bits = [];
        if ((string) get_post_meta($oid, "_ups_offer_has_google", true) !== "0") {
            $bits[] = "Google Ads";
        }
        if ((string) get_post_meta($oid, "_ups_offer_has_meta", true) !== "0") {
            $bits[] = "Meta Ads";
        }
        if ((string) get_post_meta($oid, "_ups_offer_has_web", true) === "1") {
            $bits[] = "WWW";
        }
        $scope = $bits !== [] ? implode(" + ", $bits) : "";

        $wins[] = [
            "price" => $price,
            "reason" => $reason,
            "industry" => $ind,
            "scope" => $scope,
        ];
        if ($reason !== "") {
            $win_reasons[$reason] = ($win_reasons[$reason] ?? 0) + 1;
        }
        if ($price > 0) {
            $price_points[] = $price;
        }
        if ($ind !== "") {
            $industries[$ind] = ($industries[$ind] ?? 0) + 1;
        }
    }

    arsort($win_reasons);
    arsort($industries);
    $avg_price = $price_points !== [] ? (int) round(array_sum($price_points) / count($price_points)) : 0;

    $snapshot = [
        "total_won" => count($wins),
        "avg_price_pln" => $avg_price,
        "top_win_reasons" => array_slice($win_reasons, 0, 3, true),
        "top_industries" => array_slice($industries, 0, 3, true),
        "recent_wins" => array_slice($wins, 0, 5),
        "built" => current_time("mysql"),
    ];
    update_option("ups_ai_wins_snapshot", $snapshot, false);
    update_option("ups_ai_wins_snapshot_built", current_time("mysql"), false);

    return $snapshot;
}

function upsellio_offer_ai_fill_ajax(): void
{
    if (!function_exists("upsellio_crm_app_user_can_access") || !upsellio_crm_app_user_can_access()) {
        wp_send_json_error(["message" => "forbidden"], 403);
    }
    check_ajax_referer("ups_crm_app_action", "nonce");

    $offer_id = isset($_POST["offer_id"]) ? (int) wp_unslash($_POST["offer_id"]) : 0;
    $client_id = isset($_POST["client_id"]) ? (int) wp_unslash($_POST["client_id"]) : 0;

    if ($offer_id > 0 && get_post_type($offer_id) === "crm_offer" && !current_user_can("edit_post", $offer_id)) {
        wp_send_json_error(["message" => "cap"], 403);
    }
    if ($offer_id > 0 && get_post_type($offer_id) === "crm_offer") {
        $cid_from_offer = (int) get_post_meta($offer_id, "_ups_offer_client_id", true);
        if ($cid_from_offer > 0) {
            $client_id = $cid_from_offer;
        }
    }
    if ($client_id <= 0 || get_post_type($client_id) !== "crm_client") {
        wp_send_json_error(["message" => "Wybierz klienta przed wypełnieniem AI."], 400);
    }
    if (!current_user_can("edit_post", $client_id)) {
        wp_send_json_error(["message" => "cap_client"], 403);
    }
    if (!function_exists("upsellio_anthropic_crm_api_key") || upsellio_anthropic_crm_api_key() === "") {
        wp_send_json_error(["message" => "no_key"], 400);
    }

    $client_name = get_the_title($client_id);
    $client_company = (string) get_post_meta($client_id, "_ups_client_company", true);
    $client_industry = (string) get_post_meta($client_id, "_ups_client_industry", true);
    $client_budget = (string) get_post_meta($client_id, "_ups_client_budget_range", true);
    $client_notes = (string) get_post_meta($client_id, "_ups_client_notes", true);
    $client_last_call = (string) get_post_meta($client_id, "_ups_client_last_call_notes", true);

    $won_patterns = function_exists("upsellio_offer_ai_won_patterns") ? upsellio_offer_ai_won_patterns(4) : "";

    $offer_title = $offer_id > 0 ? get_the_title($offer_id) : "";
    $offer_stage = $offer_id > 0 ? (string) get_post_meta($offer_id, "_ups_offer_stage", true) : "";

    $thread_ctx = "";
    if ($offer_id > 0) {
        $thread = get_post_meta($offer_id, "_ups_offer_inbox_thread", true);
        if (is_array($thread) && $thread !== []) {
            $slice = array_slice($thread, -4);
            $lines = [];
            foreach ($slice as $msg) {
                if (!is_array($msg)) {
                    continue;
                }
                $dir = ($msg["direction"] ?? "") === "out" ? "TY" : "KLIENT";
                $body = (string) ($msg["body_plain"] ?? "");
                if (function_exists("mb_substr")) {
                    $body = mb_substr($body, 0, 320, "UTF-8");
                } else {
                    $body = substr($body, 0, 320);
                }
                $lines[] = $dir . ": " . $body;
            }
            $thread_ctx = implode(" --- ", $lines);
        }
    }

    $context = "";
    if (function_exists("upsellio_anthropic_crm_get_specialized_company_context")) {
        $context = upsellio_anthropic_crm_get_specialized_company_context("offer_fill");
        if ($context === "") {
            $context = upsellio_anthropic_crm_get_specialized_company_context("inbox_draft");
        }
    }

    $notes_snip = $client_notes;
    if (function_exists("mb_substr")) {
        $notes_snip = mb_substr($notes_snip, 0, 400, "UTF-8");
    } else {
        $notes_snip = substr($notes_snip, 0, 400);
    }

    $master_ctx = function_exists("upsellio_ai_master_context") ? upsellio_ai_master_context("offer") : "";

    $task = "Na podstawie danych klienta przygotuj wypełnienie formularza oferty B2B (CRM budowniczek). "
        . "Odpowiedz WYŁĄCZNIE jednym obiektem JSON (bez markdown, bez komentarzy). "
        . "Nie wymyślaj fikcyjnych kwot — jeśli w danych jest budżet lub widełki, możesz je odzwierciedlić w polu price jako krótki tekst; "
        . "jeśli brak danych o cenie, ustaw price na pusty string \"\".\n\n"
        . "Klient: {$client_name} | Firma: {$client_company} | Branża: {$client_industry} | Budżet (orientacyjnie): {$client_budget}\n"
        . ($client_last_call !== "" ? "Notatka z ostatniej rozmowy: {$client_last_call}\n" : "")
        . ($notes_snip !== "" ? "Notatki o kliencie: {$notes_snip}\n" : "")
        . ($thread_ctx !== "" ? "Ostatnia korespondencja (skrót): {$thread_ctx}\n" : "")
        . ($won_patterns !== "" ? "Przykłady wygranych ofert:\n{$won_patterns}\n" : "")
        . ($offer_title !== "" ? "Tytuł bieżącej oferty (jeśli edycja): {$offer_title} | Etap: {$offer_stage}\n" : "")
        . "\nPola JSON — uzupełnij sensownie wszystkie, które da się wywieść z kontekstu; brak info → pusty string \"\" lub false przy boolean:\n"
        . "- title: krótki tytuł oferty (np. „Usługi marketingowe — Nazwa firmy”)\n"
        . "- price: jedna linia — proponowane brzmienie ceny lub widełki TYLKO jeśli wynikają z danych klienta, inaczej puste\n"
        . "- timeline: start / harmonogram (np. „Start po akceptacji, tygodnie 1–2: audyt”)\n"
        . "- duration: pole „czas trwania” na karcie (np. „3 miesiące optymalizacji”)\n"
        . "- billing: model rozliczenia (np. „Miesięczny retainer + budżet media przekazywany do Google”)\n"
        . "- decision_date: YYYY-MM-DD sugerowana data decyzji klienta jeśli da się oszacować z rozmowy, inaczej \"\"\n"
        . "- lead: 2–4 zdania intro pod tytułem (problem, dla kogo, jeden konkret)\n"
        . "- include_lines: punkty „co zawiera pakiet”, każdy w osobnej linii (bez myślników na początku)\n"
        . "- option_lines: opcje dodatkowe (linie)\n"
        . "- questions_raw: pytania do klienta: Pytanie?|Krótkie uzasadnienie — jedna para na linię\n"
        . "- cta_text: przycisk akceptacji, max 8 słów\n"
        . "- price_note: notka pod ceną (VAT, warunki)\n"
        . "- proof_lines: jedna linia = jeden badge „logo/branża” lub krótka nazwa segmentu\n"
        . "- services_json: tablica JSON obiektów [{\"key\":\"...\",\"label\":\"...\",\"price_hint\":\"...\"}] — 2–4 warianty zainteresowania; musi być poprawnym JSON\n"
        . "- scope_extra_html: krótki HTML (lista <ul><li>) doprecyzowanie zakresu poza checkboxami\n"
        . "- content: treść sekcji szczegóły — HTML uproszczony (<p>, <ul>, <li>, <strong>), 3–8 akapitów max.\n"
        . "- deal_notes: krótka notatka deala (wewnętrzna)\n"
        . "- internal_notes: krótka notatka oferty (wewnętrzna)\n"
        . "- has_google / has_meta / has_web: boolean — zakres checkboxów\n\n"
        . 'Minimalny szkielet (uzupełnij wszystkie klucze): '
        . '{"title":"","price":"","timeline":"","duration":"","billing":"","decision_date":"","lead":"","include_lines":"","option_lines":"","questions_raw":"","cta_text":"","price_note":"","proof_lines":"","services_json":[],"scope_extra_html":"","content":"","deal_notes":"","internal_notes":"","has_google":true,"has_meta":true,"has_web":false}';

    if ($master_ctx !== "") {
        $task .= "\n\n---\nKontekst agregatowy (GA4, klienci, typowe zakresy wygranych):\n" . $master_ctx;
    }

    $prompt = ($context !== "" ? $context . "\n\n--- Zadanie ---\n\n" : "") . $task;

    $model = defined("UPSELLIO_ANTHROPIC_DEFAULT_MODEL") ? (string) UPSELLIO_ANTHROPIC_DEFAULT_MODEL : "claude-haiku-4-5-20251001";
    $raw = function_exists("upsellio_anthropic_crm_send_user_prompt")
        ? upsellio_anthropic_crm_send_user_prompt($prompt, 4096, 42, $model)
        : null;
    if ($raw === null) {
        $err = function_exists("upsellio_anthropic_crm_get_last_send_error") ? upsellio_anthropic_crm_get_last_send_error() : "";
        wp_send_json_error(["message" => "Błąd API" . ($err !== "" ? ": " . $err : "")], 502);
    }

    $data = function_exists("upsellio_anthropic_crm_parse_json_object")
        ? upsellio_anthropic_crm_parse_json_object($raw)
        : null;
    if (!is_array($data)) {
        wp_send_json_error(["message" => "Niepoprawny JSON z modelu."], 502);
    }

    $services_json_str = "";
    $sj = $data["services_json"] ?? null;
    if (is_array($sj)) {
        $enc = wp_json_encode($sj);
        $services_json_str = is_string($enc) ? $enc : "";
    } elseif (is_string($sj) && $sj !== "") {
        $dec = json_decode($sj, true);
        if (is_array($dec)) {
            $enc = wp_json_encode($dec);
            $services_json_str = is_string($enc) ? $enc : "";
        }
    }

    $decision_date = (string) ($data["decision_date"] ?? "");
    if ($decision_date !== "" && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $decision_date)) {
        $decision_date = "";
    }

    wp_send_json_success([
        "title" => sanitize_text_field((string) ($data["title"] ?? "")),
        "price" => sanitize_text_field((string) ($data["price"] ?? "")),
        "timeline" => sanitize_text_field((string) ($data["timeline"] ?? "")),
        "duration" => sanitize_text_field((string) ($data["duration"] ?? "")),
        "billing" => sanitize_text_field((string) ($data["billing"] ?? "")),
        "decision_date" => $decision_date,
        "lead" => sanitize_textarea_field((string) ($data["lead"] ?? "")),
        "include_lines" => sanitize_textarea_field((string) ($data["include_lines"] ?? "")),
        "option_lines" => sanitize_textarea_field((string) ($data["option_lines"] ?? "")),
        "questions_raw" => sanitize_textarea_field((string) ($data["questions_raw"] ?? "")),
        "cta_text" => sanitize_text_field((string) ($data["cta_text"] ?? "")),
        "price_note" => sanitize_text_field((string) ($data["price_note"] ?? "")),
        "proof_lines" => sanitize_textarea_field((string) ($data["proof_lines"] ?? "")),
        "services_json" => $services_json_str,
        "scope_extra_html" => wp_kses_post((string) ($data["scope_extra_html"] ?? "")),
        "content" => wp_kses_post((string) ($data["content"] ?? "")),
        "deal_notes" => sanitize_textarea_field((string) ($data["deal_notes"] ?? "")),
        "internal_notes" => sanitize_textarea_field((string) ($data["internal_notes"] ?? "")),
        "has_google" => !empty($data["has_google"]),
        "has_meta" => !empty($data["has_meta"]),
        "has_web" => !empty($data["has_web"]),
    ]);
}

add_action("wp_ajax_upsellio_offer_ai_fill", "upsellio_offer_ai_fill_ajax");

function upsellio_offer_ai_on_event_tracked($offer_id, $event_name = "", $summary = [], $stage = ""): void
{
    upsellio_offer_ai_aggregate_events((int) $offer_id);
}

add_action("upsellio_offer_event_tracked", "upsellio_offer_ai_on_event_tracked", 25, 4);

function upsellio_offer_ai_daily_rebuild(): void
{
    if (function_exists("upsellio_ai_build_wins_snapshot")) {
        upsellio_ai_build_wins_snapshot();
    }
}

add_action("upsellio_offer_ai_daily_rebuild", "upsellio_offer_ai_daily_rebuild");

function upsellio_offer_ai_schedule_cron(): void
{
    if (!wp_next_scheduled("upsellio_offer_ai_daily_rebuild")) {
        wp_schedule_event(time() + HOUR_IN_SECONDS, "daily", "upsellio_offer_ai_daily_rebuild");
    }
}

add_action("init", "upsellio_offer_ai_schedule_cron", 40);
