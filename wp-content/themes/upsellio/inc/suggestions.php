<?php

if (!defined("ABSPATH")) {
    exit;
}

/** Cache TTL sugestii SEO/Blog/Ads/Keywords (sekundy). */
function upsellio_suggestions_cache_ttl(): int
{
    return (int) apply_filters("upsellio_suggestions_cache_ttl", DAY_IN_SECONDS);
}

function upsellio_suggestions_clusters_cache_ttl(): int
{
    return (int) apply_filters("upsellio_suggestions_clusters_cache_ttl", 2 * DAY_IN_SECONDS);
}

function upsellio_suggestions_research_cache_ttl(): int
{
    return (int) apply_filters("upsellio_suggestions_research_cache_ttl", 7 * DAY_IN_SECONDS);
}

function upsellio_suggestions_option_key(string $type): string
{
    $map = [
        "seo" => "ups_suggestions_seo",
        "blog" => "ups_suggestions_blog",
        "ads" => "ups_suggestions_ads",
        "keywords" => "ups_suggestions_keywords",
        "clusters" => "ups_suggestions_clusters",
    ];

    return $map[$type] ?? "ups_suggestions_unknown";
}

/**
 * @return array<string, mixed>
 */
function upsellio_suggestions_get(string $type): array
{
    $key = upsellio_suggestions_option_key($type);
    $raw = get_option($key, []);
    if (!is_array($raw)) {
        return [];
    }

    return $raw;
}

/**
 * @param array<string, mixed> $payload
 */
function upsellio_suggestions_save(string $type, array $payload): void
{
    $key = upsellio_suggestions_option_key($type);
    update_option($key, $payload, false);
}

function upsellio_suggestions_estimate_tokens(string $text): int
{
    $len = function_exists("mb_strlen") ? (int) mb_strlen($text, "UTF-8") : strlen($text);

    return max(1, (int) round($len / 4));
}

/**
 * @return list<string>
 */
function upsellio_suggestions_crm_service_titles(int $limit = 40): array
{
    if (!post_type_exists("crm_service")) {
        return [];
    }
    $posts = get_posts([
        "post_type" => "crm_service",
        "post_status" => ["publish", "private"],
        "posts_per_page" => max(5, min(80, $limit)),
        "orderby" => "title",
        "order" => "ASC",
        "fields" => "ids",
    ]);
    $out = [];
    foreach ($posts as $pid) {
        $t = get_the_title((int) $pid);
        if ($t !== "") {
            $out[] = sanitize_text_field($t);
        }
    }

    return $out;
}

function upsellio_suggestions_company_context(): string
{
    $ctx = "";
    if (function_exists("upsellio_anthropic_crm_get_specialized_company_context")) {
        $ctx = upsellio_anthropic_crm_get_specialized_company_context("blog");
    }
    if ($ctx === "") {
        $ctx = trim((string) get_option("ups_ai_company_context", ""));
    }
    if ($ctx === "") {
        $ctx = trim((string) get_option("ups_anthropic_company_context", ""));
    }

    return $ctx;
}

function upsellio_suggestions_default_model(): string
{
    if (function_exists("upsellio_ai_model_for")) {
        return upsellio_ai_model_for("suggestions");
    }
    $m = trim((string) get_option("ups_blog_bot_model", ""));
    if ($m === "") {
        $m = trim((string) get_option("ups_anthropic_model", ""));
    }
    if ($m === "") {
        $m = "claude-haiku-4-5-20251001";
    }

    return $m;
}

/**
 * Tekst agregatu GSC do promptów (ograniczony rozmiar).
 * Jedno źródło prawdy: upsellio_gsc_analyze_full() (cache jak analiza PHP).
 */
function upsellio_suggestions_gsc_aggregate_text(int $max_lines = 90): string
{
    if (function_exists("upsellio_gsc_analyze_full")) {
        $analysis = upsellio_gsc_analyze_full();
        $agg = isset($analysis["aggregated"]) && is_array($analysis["aggregated"]) ? $analysis["aggregated"] : [];
        if ($agg !== []) {
            usort($agg, static function ($a, $b) {
                return (int) ($b["impressions"] ?? 0) <=> (int) ($a["impressions"] ?? 0);
            });
            $lines = [];
            $n = 0;
            foreach ($agg as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $kw = sanitize_text_field((string) ($row["keyword"] ?? ""));
                if ($kw === "") {
                    continue;
                }
                $url_raw = (string) ($row["url"] ?? "");
                $url = $url_raw !== "" ? esc_url_raw($url_raw) : "—";
                $lines[] = sprintf(
                    "- \"%s\" | poz. %.1f | wyśw. %d | klik %d | CTR %.2f%% | URL %s",
                    $kw,
                    (float) ($row["position"] ?? 0),
                    (int) ($row["impressions"] ?? 0),
                    (int) ($row["clicks"] ?? 0),
                    (float) ($row["ctr"] ?? 0),
                    $url
                );
                $n++;
                if ($n >= $max_lines) {
                    break;
                }
            }

            return implode("\n", $lines);
        }

        return "Brak danych GSC (zaimportuj CSV lub połącz synchronizację w Analityce).";
    }

    $rows = get_option("upsellio_keyword_metrics_rows", []);
    if (!is_array($rows) || $rows === []) {
        return "Brak danych GSC (zaimportuj CSV lub połącz synchronizację w Analityce).";
    }

    $lines = [];
    $n = 0;
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $kw = sanitize_text_field((string) ($row["keyword"] ?? ""));
        if ($kw === "") {
            continue;
        }
        $url_raw = (string) ($row["url"] ?? "");
        $url = $url_raw !== "" ? esc_url_raw($url_raw) : "—";
        $lines[] = sprintf(
            "- \"%s\" | poz. %.1f | wyśw. %d | klik %d | CTR %.2f%% | URL %s",
            $kw,
            (float) ($row["position"] ?? 0),
            (int) ($row["impressions"] ?? 0),
            (int) ($row["clicks"] ?? 0),
            (float) ($row["ctr"] ?? 0),
            $url
        );
        $n++;
        if ($n >= $max_lines) {
            break;
        }
    }

    return implode("\n", $lines);
}

function upsellio_suggestions_existing_posts_lines(int $limit = 18): string
{
    if (!function_exists("upsellio_topicgen_get_existing_posts")) {
        return "";
    }
    $titles = upsellio_topicgen_get_existing_posts($limit);
    if ($titles === []) {
        return "Brak opublikowanych wpisów.";
    }

    return implode("\n", array_map(static function ($t) {
        return "- " . $t;
    }, $titles));
}

function upsellio_suggestions_ga4_channel_is_paid_like(string $haystack): bool
{
    $h = strtolower($haystack);

    return strpos($h, "cpc") !== false
        || strpos($h, "google") !== false
        || strpos($h, "ads") !== false
        || strpos($h, "paid") !== false;
}

function upsellio_suggestions_ga4_cpc_sessions(): int
{
    $scores = get_option("ups_automation_channel_quality_scores", []);
    if (!is_array($scores)) {
        return 0;
    }
    $sum = 0;
    foreach ($scores as $key => $row) {
        if (!is_array($row)) {
            continue;
        }
        $src = strtolower((string) ($row["source"] ?? ""));
        $camp = strtolower((string) ($row["campaign"] ?? ""));
        $hay = $src . " " . $camp . " " . strtolower((string) $key);
        if (upsellio_suggestions_ga4_channel_is_paid_like($hay)) {
            $sum += (int) ($row["sessions"] ?? 0);
        }
    }

    return $sum;
}

/**
 * Szczegóły wierszy GA4 (Źródło / Kampania) pasujących do CPC / Google Ads — do promptu Ads.
 */
function upsellio_suggestions_ga4_cpc_detail_block(int $max_lines = 14): string
{
    $scores = get_option("ups_automation_channel_quality_scores", []);
    if (!is_array($scores) || $scores === []) {
        return "Brak danych GA4 kanałów (Analityka → pobierz quality scores).\n";
    }

    $lines = [];
    foreach ($scores as $key => $row) {
        if (!is_array($row)) {
            continue;
        }
        $src = (string) ($row["source"] ?? "");
        $camp = (string) ($row["campaign"] ?? "");
        $hay = strtolower($src . " " . $camp . " " . (string) $key);
        if (!upsellio_suggestions_ga4_channel_is_paid_like($hay)) {
            continue;
        }
        $sess = (int) ($row["sessions"] ?? 0);
        $conv = (int) ($row["conversions"] ?? $row["conversion_events"] ?? $row["conv"] ?? 0);
        $lines[] = sprintf(
            "  %s / %s | sesje: %d | konwersje (jeśli dostępne): %d",
            $src !== "" ? $src : "—",
            $camp !== "" ? $camp : "—",
            $sess,
            $conv
        );
        if (count($lines) >= $max_lines) {
            break;
        }
    }

    return $lines !== []
        ? implode("\n", $lines) . "\n"
        : "Brak wierszy GA4 dopasowanych do CPC/Google/paid (sprawdź nazewnictwo źródeł).\n";
}

/**
 * Podsumowanie leadów CRM z UTM sugerującym płatny ruch Google.
 */
function upsellio_suggestions_crm_paid_leads_block(): string
{
    if (!post_type_exists("crm_lead")) {
        return "Brak CPT crm_lead.\n";
    }

    $leads = get_posts([
        "post_type" => "crm_lead",
        "post_status" => ["publish", "draft", "pending", "private"],
        "posts_per_page" => 600,
        "fields" => "ids",
    ]);

    $paid_like = 0;
    $samples = [];
    foreach ($leads as $lid) {
        $lid = (int) $lid;
        $utm_s = strtolower((string) get_post_meta($lid, "_ups_lead_utm_source", true));
        $utm_m = strtolower((string) get_post_meta($lid, "_ups_lead_utm_medium", true));
        $hay = $utm_s . " " . $utm_m;
        $match = upsellio_suggestions_ga4_channel_is_paid_like($hay)
            || strpos($hay, "ppc") !== false
            || strpos($hay, "sem") !== false;
        if (!$match) {
            continue;
        }
        $paid_like++;
        if (count($samples) < 8) {
            $samples[] = sprintf(
                "lead #%d: utm_source=%s utm_medium=%s",
                $lid,
                $utm_s !== "" ? $utm_s : "—",
                $utm_m !== "" ? $utm_m : "—"
            );
        }
    }

    $out = "Leady z UTM sugerującym Google Ads / CPC (szacunek): {$paid_like} (z max. " . count($leads) . " pobranych).\n";
    if ($samples !== []) {
        $out .= "Przykłady:\n  - " . implode("\n  - ", $samples) . "\n";
    }

    return $out;
}

/**
 * Surowe koszty kampanii z opcji (źródło|kampania → PLN).
 */
function upsellio_suggestions_campaign_costs_raw_block(int $max = 18): string
{
    if (!function_exists("upsellio_sales_engine_get_campaign_costs")) {
        return "";
    }
    $costs = upsellio_sales_engine_get_campaign_costs();
    if (!is_array($costs) || $costs === []) {
        return "Brak wpisu kosztów kampanii (Silnik sprzedaży / ups_sales_campaign_costs).\n";
    }

    $lines = [];
    $n = 0;
    foreach ($costs as $key => $amount) {
        $lines[] = sprintf("  %s → %.2f PLN", (string) $key, (float) $amount);
        $n++;
        if ($n >= $max) {
            break;
        }
    }

    return "Zapisane koszty kampanii (fragment):\n" . implode("\n", $lines) . "\n";
}

function upsellio_suggestions_seo_prompt(): string
{
    $company = upsellio_suggestions_company_context();
    $gsc = upsellio_suggestions_gsc_aggregate_text(100);
    $posts = upsellio_suggestions_existing_posts_lines(20);
    $services = upsellio_suggestions_crm_service_titles(35);
    $svc_txt = $services !== []
        ? implode(", ", $services)
        : "(brak wpisów CPT crm_service — uzupełnij katalog usług w CRM.)";

    $parts = [];
    $parts[] = "Jesteś ekspertem SEO dla polskiej agencji marketingowej B2B.";
    $parts[] = "Na podstawie danych GSC wygeneruj dokładnie 6 priorytetowych sugestii SEO.";
    if ($company !== "") {
        $parts[] = "KONTEKST FIRMY:\n" . $company;
    }
    $parts[] = "USŁUGI / OFERTA (CRM):\n" . $svc_txt;
    $parts[] = "ISTNIEJĄCE WPISY BLOGA:\n" . $posts;
    $parts[] = "DANE GSC (fraza, pozycja, wyświetlenia, kliknięcia, CTR, URL):\n" . $gsc;
    $parts[] = "Każda sugestia musi zawierać pola:\n"
        . "- priority: high | medium | low\n"
        . "- category: quick_win | treść | techniczne | lokalne | linki\n"
        . "- title: konkretna nazwa działania\n"
        . "- description: dlaczego to ważne (liczby jeśli możliwe)\n"
        . "- action: kroki do wykonania\n"
        . "- where: gdzie w systemie (np. CRM → Ustawienia → AI / Blog Bot — wpisz frazę X)\n"
        . "- metric: obecna wartość → cel (np. poz. 28 → top 10)\n"
        . "- effort: low | medium | high\n"
        . "- expected_impact: low | medium | high";
    $parts[] = "Odpowiedz WYŁĄCZNIE jednym obiektem JSON (bez markdown, bez ```):\n"
        . '{"suggestions":[{...},...]}';

    return implode("\n\n", $parts);
}

function upsellio_suggestions_blog_prompt(): string
{
    $company = upsellio_suggestions_company_context();
    $gsc = upsellio_suggestions_gsc_aggregate_text(85);
    $posts = upsellio_suggestions_existing_posts_lines(18);

    $lead_hint = "";
    if (function_exists("upsellio_topicgen_get_crm_lead_insights")) {
        $li = upsellio_topicgen_get_crm_lead_insights(25);
        if (!empty($li["services"])) {
            $lead_hint = "Leady — najczęstsze usługi:\n";
            foreach (array_slice($li["services"], 0, 8, true) as $svc => $cnt) {
                $lead_hint .= "  - {$svc} ({$cnt})\n";
            }
        }
    }

    $parts = [];
    $parts[] = "Jesteś strategiem content & SEO dla polskiej agencji marketingowej B2B.";
    $parts[] = "Na podstawie GSC i bloga zbuduj listę działań blogowych.";
    if ($company !== "") {
        $parts[] = "KONTEKST FIRMY:\n" . $company;
    }
    $parts[] = "WPISY NA BLOGU (nie duplikuj):\n" . $posts;
    if ($lead_hint !== "") {
        $parts[] = $lead_hint;
    }
    $parts[] = "DANE GSC:\n" . $gsc;
    $parts[] = "Wygeneruj strukturę:\n"
        . "1) quick_wins: 3–5 pozycji — frazy z widocznością GSC, brak lub słaby artykuł, realny zysk.\n"
        . "2) new_topics: 2–4 pozycje — tematy z pytań leadów / luk (bez danych GSC OK).\n"
        . "Każdy element tablicy musi mieć:\n"
        . "- title, primary_query, gsc_position (number|null), gsc_impressions (number|null),\n"
        . "- opportunity: quick_win | new_content | satellite | sales,\n"
        . "- estimated_difficulty: low | medium | high,\n"
        . "- rationale: jedno zdanie,\n"
        . "- priority: high | medium | low";
    $parts[] = "Odpowiedz WYŁĄCZNIE JSON:\n"
        . '{"quick_wins":[...],"new_topics":[...]}';

    return implode("\n\n", $parts);
}

function upsellio_suggestions_ads_prompt(): string
{
    $company = upsellio_suggestions_company_context();
    $roas_summary = "";
    if (function_exists("upsellio_sales_engine_build_roas_report_rows")) {
        foreach (array_slice(upsellio_sales_engine_build_roas_report_rows(), 0, 12) as $r) {
            if (!is_array($r)) {
                continue;
            }
            $roas_summary .= sprintf(
                "  %s / %s: wydano %.0f PLN | %d leadów | %d wygranych | przychód %.0f PLN | ROAS %s\n",
                (string) ($r["source"] ?? ""),
                (string) ($r["campaign"] ?? ""),
                (float) ($r["spend"] ?? 0),
                (int) ($r["leads"] ?? 0),
                (int) ($r["won"] ?? 0),
                (float) ($r["revenue"] ?? 0),
                (string) ($r["roas"] ?? "0")
            );
        }
    }
    if ($roas_summary === "") {
        $roas_summary = "Brak wierszy ROAS — dodaj koszty kampanii w Silniku sprzedaży / automatyzacjach.\n";
    }

    $gsc_lines = "";
    $rows = get_option("upsellio_keyword_metrics_rows", []);
    if (is_array($rows)) {
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $pos = (float) ($row["position"] ?? 99);
            if ($pos > 30) {
                continue;
            }
            $kw = sanitize_text_field((string) ($row["keyword"] ?? ""));
            if ($kw === "") {
                continue;
            }
            $gsc_lines .= sprintf(
                "  \"%s\" | poz. %.1f | %d wyśw. | %d klik.\n",
                $kw,
                $pos,
                (int) ($row["impressions"] ?? 0),
                (int) ($row["clicks"] ?? 0)
            );
        }
    }
    if ($gsc_lines === "") {
        $gsc_lines = "Brak fraz w top 30 z GSC.\n";
    }

    $cpc_sess = upsellio_suggestions_ga4_cpc_sessions();
    $ga4_detail = upsellio_suggestions_ga4_cpc_detail_block(16);
    $costs_raw = upsellio_suggestions_campaign_costs_raw_block(20);
    $leads_ctx = upsellio_suggestions_crm_paid_leads_block();

    $parts = [];
    $parts[] = "Jesteś ekspertem Google Ads dla polskiej agencji marketingowej B2B.";
    if ($company !== "") {
        $parts[] = "KONTEKST FIRMY:\n" . $company;
    }
    $parts[] = "DANE ROAS (kampanie → leady → przychód):\n" . trim($roas_summary);
    if ($costs_raw !== "") {
        $parts[] = trim($costs_raw);
    }
    $parts[] = trim($leads_ctx);
    $parts[] = "SESJE (suma wierszy GA4 dopasowanych do CPC/Google/paid): " . (string) $cpc_sess;
    $parts[] = "SZCZEGÓŁY KANAŁÓW GA4 (dopasowanie CPC/Google):\n" . trim($ga4_detail);
    $parts[] = "FRAZY Z GSC (pozycja ≤30) — kandydaci do testów w Ads:\n" . $gsc_lines;
    $parts[] = "Wygeneruj dokładnie 6 sugestii optymalizacji Google Ads (Quality Score, landing, słowa kluczowe, budżet, śledzenie, struktura).\n"
        . "Każda sugestia: priority, category (quality_score|landing|keywords|budget|tracking|structure), "
        . "title, description, action, where (Google Ads UI / CRM / GA4), metric, effort (low|medium|high), expected_impact (low|medium|high).";
    $parts[] = "Odpowiedz WYŁĄCZNIE JSON:\n" . '{"suggestions":[{...},...]}';

    return implode("\n\n", $parts);
}

function upsellio_suggestions_keywords_ai_prompt(): string
{
    $company = upsellio_suggestions_company_context();
    $gsc = upsellio_suggestions_gsc_aggregate_text(70);
    $parts = [];
    $parts[] = "Jesteś architektem SEO dla agencji marketingowej. Masz dane GSC.";
    if ($company !== "") {
        $parts[] = "KONTEKST FIRMY:\n" . $company;
    }
    $parts[] = "DANE:\n" . $gsc;
    $parts[] = "Wygeneruj 5–8 krótkich rekomendacji strategicznych (pole insights: tablica obiektów z polami: title, detail, priority).\n"
        . "Skup się na priorytetyzacji fraz, kanibalizacji, luk treści vs usługi.";
    $parts[] = "Odpowiedz WYŁĄCZNIE JSON:\n" . '{"insights":[{"title":"","detail":"","priority":"high|medium|low"},...]}';

    return implode("\n\n", $parts);
}

/**
 * Heurystyczna klasyfikacja fraz do tabeli (bez AI).
 *
 * @return list<array<string, mixed>>
 */
function upsellio_suggestions_keywords_table_rows(int $limit = 80): array
{
    $rows = get_option("upsellio_keyword_metrics_rows", []);
    if (!is_array($rows) || $rows === []) {
        return [];
    }

    $out = [];
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $kw = sanitize_text_field((string) ($row["keyword"] ?? ""));
        if ($kw === "") {
            continue;
        }
        $pos = (float) ($row["position"] ?? 99);
        $imp = (int) ($row["impressions"] ?? 0);
        $clk = (int) ($row["clicks"] ?? 0);
        $ctr = (float) ($row["ctr"] ?? 0);

        $cat = "monitor";
        $reco = "Monitoruj pozycję i CTR.";
        if ($pos >= 11 && $pos <= 30 && $imp > 0) {
            $cat = "quick_win";
            $reco = "Blog Bot / landing — dopracuj tytuł i treść pod frazę.";
        } elseif ($ctr < 2 && $pos > 0 && $pos < 20 && $imp > 10) {
            $cat = "meta_ctr";
            $reco = "Popraw meta description i nagłówek SERP.";
        } elseif ($pos > 50 && $imp > 0) {
            $cat = "weak";
            $reco = "Nowy artykuł + linki wewnętrzne.";
        } elseif ($clk === 0 && $imp > 5) {
            $cat = "intent";
            $reco = "Sprawdź intencję — treść może nie odpowiadać zapytaniu.";
        }

        $cat_display = [
            "quick_win" => __("🏆 Quick win", "upsellio"),
            "meta_ctr" => __("📉 Meta / CTR", "upsellio"),
            "weak" => __("🔴 Słaba pozycja", "upsellio"),
            "intent" => __("❓ Intencja treści", "upsellio"),
            "monitor" => __("👁 Monitoruj", "upsellio"),
        ];
        $out[] = [
            "keyword" => $kw,
            "position" => $pos,
            "impressions" => $imp,
            "clicks" => $clk,
            "ctr" => $ctr,
            "category" => $cat,
            "category_display" => $cat_display[$cat] ?? $cat,
            "recommendation" => $reco,
        ];
        if (count($out) >= $limit) {
            break;
        }
    }

    usort($out, static function ($a, $b) {
        return (float) ($a["position"] ?? 99) <=> (float) ($b["position"] ?? 99);
    });

    return $out;
}

/**
 * @return array{ok: bool, message?: string}
 */
function upsellio_suggestions_generate(string $type): array
{
    if (!function_exists("upsellio_anthropic_crm_send_user_prompt") || !function_exists("upsellio_anthropic_crm_parse_json_object")) {
        return ["ok" => false, "message" => "Brak integracji Anthropic."];
    }
    if (!function_exists("upsellio_anthropic_crm_api_key") || upsellio_anthropic_crm_api_key() === "") {
        return ["ok" => false, "message" => "Brak klucza API (UPSELLIO_ANTHROPIC_API_KEY / ustawienia CRM)."];
    }

    $prompt = "";
    $max_out = 1800;
    switch ($type) {
        case "seo":
            $prompt = upsellio_suggestions_seo_prompt();
            $max_out = 2200;
            break;
        case "blog":
            $prompt = upsellio_suggestions_blog_prompt();
            $max_out = 2600;
            break;
        case "ads":
            $prompt = upsellio_suggestions_ads_prompt();
            $max_out = 2200;
            break;
        case "keywords":
            $prompt = upsellio_suggestions_keywords_ai_prompt();
            $max_out = 1600;
            break;
        default:
            return ["ok" => false, "message" => "Nieznany typ sugestii."];
    }

    $model = upsellio_suggestions_default_model();
    $in_tok = upsellio_suggestions_estimate_tokens($prompt);
    $raw = upsellio_anthropic_crm_send_user_prompt($prompt, $max_out, 90, $model);
    if ($raw === null) {
        $err = function_exists("upsellio_anthropic_crm_get_last_send_error")
            ? upsellio_anthropic_crm_get_last_send_error()
            : "";

        return ["ok" => false, "message" => $err !== "" ? $err : "Pusta odpowiedź API."];
    }

    $parsed = upsellio_anthropic_crm_parse_json_object($raw);
    if (!is_array($parsed)) {
        return ["ok" => false, "message" => "Niepoprawny JSON z modelu."];
    }

    $out_tok = upsellio_suggestions_estimate_tokens((string) $raw);
    $payload = [
        "generated_at" => current_time("mysql"),
        "ttl" => upsellio_suggestions_cache_ttl(),
        "model" => $model,
        "approx_input_tokens" => $in_tok,
        "approx_output_tokens" => $out_tok,
        "data" => $parsed,
    ];
    upsellio_suggestions_save($type, $payload);

    return ["ok" => true, "message" => "Zapisano sugestie (" . $type . ")."];
}

function upsellio_suggestions_generate_clusters(): array
{
    if (!function_exists("upsellio_anthropic_crm_send_user_prompt") || !function_exists("upsellio_anthropic_crm_parse_json_object")) {
        return ["ok" => false, "message" => "Brak integracji Anthropic."];
    }
    if (!function_exists("upsellio_anthropic_crm_api_key") || upsellio_anthropic_crm_api_key() === "") {
        return ["ok" => false, "message" => "Brak klucza API."];
    }

    $rows = get_option("upsellio_keyword_metrics_rows", []);
    if (!is_array($rows) || $rows === []) {
        return ["ok" => false, "message" => "Brak danych GSC."];
    }

    $keywords_list = [];
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $kw = sanitize_text_field((string) ($row["keyword"] ?? ""));
        if ($kw === "") {
            continue;
        }
        $url = (string) ($row["url"] ?? "");
        $pos = (float) ($row["position"] ?? 99);
        $keywords_list[] = "\"{$kw}\" | poz. {$pos} | url: {$url}";
        if (count($keywords_list) >= 120) {
            break;
        }
    }

    $prompt = "Masz listę fraz z Google Search Console dla polskiej agencji marketingowej.\n\n"
        . "FRAZY:\n" . implode("\n", $keywords_list) . "\n\n"
        . "Zadania:\n"
        . "1. KLASTRY: pogrupuj w max 6 klastrów.\n"
        . "2. KANIBALIZACJA: wskaż przypadki wielu fraz na ten sam URL jeśli widoczne.\n"
        . "3. LUKI: tematy które warto dodać.\n"
        . "4. PRIORYTETY: kolejność klastrów.\n\n"
        . "Odpowiedz WYŁĄCZNIE JSON:\n"
        . '{"clusters":[{"name":"","keywords":[],"frazy":[],"main_url":"","action":""}],'
        . '"cannibalization":[{"keyword":"","fraza":"","urls":[],"problem":"","fix":""}],'
        . '"gaps":[""],'
        . '"priority_order":[""]}';

    $model = function_exists("upsellio_ai_model_for")
        ? upsellio_ai_model_for("suggestions_clusters")
        : upsellio_suggestions_default_model();
    $in_tok = upsellio_suggestions_estimate_tokens($prompt);
    $raw = upsellio_anthropic_crm_send_user_prompt($prompt, 2400, 90, $model);
    if ($raw === null) {
        return ["ok" => false, "message" => "Brak odpowiedzi API."];
    }
    $parsed = upsellio_anthropic_crm_parse_json_object($raw);
    if (!is_array($parsed)) {
        return ["ok" => false, "message" => "Niepoprawny JSON z modelu."];
    }

    $payload = [
        "generated_at" => current_time("mysql"),
        "ttl" => upsellio_suggestions_clusters_cache_ttl(),
        "model" => $model,
        "approx_input_tokens" => $in_tok,
        "approx_output_tokens" => upsellio_suggestions_estimate_tokens((string) $raw),
        "data" => $parsed,
    ];
    upsellio_suggestions_save("clusters", $payload);

    return ["ok" => true, "message" => "Zapisano analizę klastrów."];
}

function upsellio_blog_format_related_keywords(array $related): string
{
    if ($related === []) {
        return "(brak powiązanych fraz w GSC dla tej frazy)";
    }
    $lines = [];
    foreach (array_slice($related, 0, 25) as $r) {
        if (!is_array($r)) {
            continue;
        }
        $lines[] = sprintf(
            "- %s | poz. %.1f | wyśw. %d | klik %d | CTR %.2f%%",
            (string) ($r["keyword"] ?? ""),
            (float) ($r["position"] ?? 0),
            (int) ($r["impressions"] ?? 0),
            (int) ($r["clicks"] ?? 0),
            (float) ($r["ctr"] ?? 0)
        );
    }

    return implode("\n", $lines);
}

/**
 * Research frazy przed Blog Botem (cache 7 dni).
 *
 * @return array<string, mixed>
 */
function upsellio_blog_keyword_research(string $seed): array
{
    $seed = trim($seed);
    if ($seed === "") {
        return ["error" => "Pusta fraza."];
    }

    $cache_key = "ups_blog_kw_research_" . md5(strtolower($seed));
    $cached = get_option($cache_key, null);
    if (is_array($cached) && isset($cached["expires"], $cached["data"]) && (int) $cached["expires"] > time()) {
        return is_array($cached["data"]) ? $cached["data"] : ["error" => "Zły cache."];
    }

    $gsc_rows = get_option("upsellio_keyword_metrics_rows", []);
    if (!is_array($gsc_rows)) {
        $gsc_rows = [];
    }

    $seed_words = preg_split("/\s+/u", strtolower($seed)) ?: [];
    $seed_words = array_values(array_filter(array_map("trim", $seed_words)));

    $related = [];
    foreach ($gsc_rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $kw = sanitize_text_field((string) ($row["keyword"] ?? ""));
        if ($kw === "" || strtolower($kw) === strtolower($seed)) {
            continue;
        }
        $kw_words = preg_split("/\s+/u", strtolower($kw)) ?: [];
        $common = array_intersect($seed_words, $kw_words);
        if (count($common) >= 1) {
            $related[] = [
                "keyword" => $kw,
                "position" => (float) ($row["position"] ?? 99),
                "impressions" => (int) ($row["impressions"] ?? 0),
                "clicks" => (int) ($row["clicks"] ?? 0),
                "ctr" => (float) ($row["ctr"] ?? 0),
            ];
        }
    }

    usort($related, static function ($a, $b) {
        return (int) ($b["impressions"] ?? 0) <=> (int) ($a["impressions"] ?? 0);
    });
    $related = array_slice($related, 0, 40);

    $existing = function_exists("upsellio_blog_bot_get_posts_context")
        ? upsellio_blog_bot_get_posts_context(20)
        : (function_exists("upsellio_topicgen_get_existing_posts")
            ? implode("\n", array_map(static function ($t) {
                return "- " . $t;
            }, upsellio_topicgen_get_existing_posts(20)))
            : "");

    $prompt = "Fraza główna do artykułu: \"{$seed}\"\n\n"
        . "Powiązane frazy z GSC:\n"
        . upsellio_blog_format_related_keywords($related)
        . "\n\nIstniejące artykuły (nie duplikuj):\n{$existing}\n\n"
        . "Zadanie:\n"
        . "1. Oceń: ok | risky | duplicate\n"
        . "2. query_cluster: 5–10 fraz\n"
        . "3. user_questions: 3 pytania\n"
        . "4. search_intent: informational|comparative|transactional|local\n"
        . "5. suggested_title: 45–60 znaków z główną frazą\n"
        . "6. primary_query, difficulty (low|medium|high), verdict_reason\n\n"
        . "Odpowiedz WYŁĄCZNIE JSON:\n"
        . '{"verdict":"ok|risky|duplicate","verdict_reason":"","suggested_title":"","query_cluster":[],"user_questions":[],"search_intent":"","primary_query":"","difficulty":""}';

    if (!function_exists("upsellio_anthropic_crm_send_user_prompt")
        || !function_exists("upsellio_anthropic_crm_api_key")
        || upsellio_anthropic_crm_api_key() === "") {
        return ["error" => "Brak klucza API."];
    }

    $model = function_exists("upsellio_ai_model_for")
        ? upsellio_ai_model_for("blog_keyword_research")
        : upsellio_suggestions_default_model();
    $raw = upsellio_anthropic_crm_send_user_prompt($prompt, 900, 45, $model);
    if ($raw === null) {
        return ["error" => "Brak odpowiedzi AI."];
    }

    $parsed = upsellio_anthropic_crm_parse_json_object($raw);
    if (!is_array($parsed)) {
        return ["error" => "Niepoprawny JSON z modelu."];
    }

    update_option(
        $cache_key,
        [
            "expires" => time() + upsellio_suggestions_research_cache_ttl(),
            "data" => $parsed,
        ],
        false
    );

    return $parsed;
}

function upsellio_suggestions_cache_is_fresh(array $entry): bool
{
    $at = isset($entry["generated_at"]) ? strtotime((string) $entry["generated_at"]) : false;
    if ($at === false) {
        return false;
    }
    $ttl = isset($entry["ttl"]) ? (int) $entry["ttl"] : upsellio_suggestions_cache_ttl();

    return (time() - (int) $at) < $ttl;
}

/**
 * Z aktualnego cache sugestii blogowych — unikalne frazy/tematy do kolejki.
 *
 * @return list<string>
 */
function upsellio_suggestions_collect_blog_queue_keywords(): array
{
    $blog = upsellio_suggestions_get("blog");
    $data = isset($blog["data"]) && is_array($blog["data"]) ? $blog["data"] : [];
    $out = [];
    foreach (["quick_wins", "new_topics"] as $sec) {
        $arr = isset($data[$sec]) && is_array($data[$sec]) ? $data[$sec] : [];
        foreach ($arr as $it) {
            if (!is_array($it)) {
                continue;
            }
            $pq = trim((string) ($it["primary_query"] ?? ""));
            $t = trim((string) ($it["title"] ?? ""));
            $line = $pq !== "" ? $pq : $t;
            if ($line !== "") {
                $out[] = $line;
            }
        }
    }

    return array_values(array_unique($out));
}

/**
 * Etykieta kategorii sugestii (SEO / Ads) do nagłówka karty.
 */
function upsellio_suggestions_category_label(string $tab, string $raw): string
{
    $raw = strtolower(trim($raw));
    if ($raw === "") {
        return "";
    }

    if ($tab === "seo") {
        $map = [
            "quick_win" => "✨ Quick win",
            "treść" => "✍️ Treść",
            "techniczne" => "⚙️ Techniczne",
            "lokalne" => "📍 Lokalne",
            "linki" => "🔗 Linki",
        ];

        return $map[$raw] ?? $raw;
    }

    if ($tab === "ads") {
        $map = [
            "quality_score" => "⭐ Quality Score",
            "landing" => "🎯 Landing",
            "keywords" => "🔑 Słowa kluczowe",
            "budget" => "💰 Budżet",
            "tracking" => "📈 Śledzenie",
            "structure" => "🗂 Struktura",
        ];

        return $map[$raw] ?? $raw;
    }

    return $raw;
}

/**
 * @param array<string, mixed> $item
 */
function upsellio_suggestions_render_card(array $item, string $tab = "seo"): void
{
    $pri = strtoupper(sanitize_key((string) ($item["priority"] ?? "medium")));
    $title = sanitize_text_field((string) ($item["title"] ?? ""));
    $desc = (string) ($item["description"] ?? "");
    $metric = sanitize_text_field((string) ($item["metric"] ?? ""));
    $effort = sanitize_text_field((string) ($item["effort"] ?? ""));
    $impact = sanitize_text_field((string) ($item["expected_impact"] ?? ""));
    $action = (string) ($item["action"] ?? "");
    $where = sanitize_text_field((string) ($item["where"] ?? ""));
    $cat_raw = sanitize_text_field((string) ($item["category"] ?? ""));
    $cat = upsellio_suggestions_category_label($tab, $cat_raw);
    ?>
    <article class="ups-sug-card" style="border:1px solid var(--border);border-radius:12px;padding:14px 16px;margin-bottom:12px;background:var(--bg)">
      <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:8px">
        <strong style="font-size:15px"><?php echo esc_html($title); ?></strong>
        <span style="font-size:11px;font-weight:700;color:var(--text-3)"><?php echo esc_html($pri); ?><?php echo $cat !== "" ? " · " . esc_html($cat) : ""; ?></span>
      </div>
      <?php if ($desc !== "") : ?>
        <p style="margin:0 0 10px;font-size:13px;line-height:1.55;color:var(--text-2)"><?php echo nl2br(esc_html($desc)); ?></p>
      <?php endif; ?>
      <div style="font-size:12px;color:var(--text-3);margin-bottom:8px">
        <?php if ($metric !== "") : ?><span><?php echo esc_html($metric); ?></span><?php endif; ?>
        <?php if ($effort !== "") : ?><?php echo $metric !== "" ? " · " : ""; ?><span><?php esc_html_e("Nakład:", "upsellio"); ?> <?php echo esc_html($effort); ?></span><?php endif; ?>
        <?php if ($impact !== "") : ?><?php echo ($metric !== "" || $effort !== "") ? " · " : ""; ?><span><?php esc_html_e("Wpływ:", "upsellio"); ?> <?php echo esc_html($impact); ?></span><?php endif; ?>
      </div>
      <?php if ($action !== "") : ?>
        <p style="margin:0 0 6px;font-size:13px"><strong><?php esc_html_e("Akcja:", "upsellio"); ?></strong></p>
        <div style="font-size:13px;line-height:1.5"><?php echo nl2br(esc_html($action)); ?></div>
      <?php endif; ?>
      <?php if ($where !== "") : ?>
        <p style="margin:10px 0 0;font-size:12px;color:var(--text-3)"><strong><?php esc_html_e("Gdzie:", "upsellio"); ?></strong> <?php echo esc_html($where); ?></p>
      <?php endif; ?>
    </article>
    <?php
}

function upsellio_crm_render_suggestions_page(string $suggestions_tab): void
{
    $ajax_url = admin_url("admin-ajax.php");
    $nonce = wp_create_nonce("upsellio_suggestions_nonce");
    $settings_ai = esc_url(add_query_arg(["view" => "settings", "settings_tab" => "ai"], home_url("/crm-app/")));

    $tab_urls = [
        "seo" => add_query_arg(["view" => "suggestions", "suggestions_tab" => "seo"], home_url("/crm-app/")),
        "blog" => add_query_arg(["view" => "suggestions", "suggestions_tab" => "blog"], home_url("/crm-app/")),
        "ads" => add_query_arg(["view" => "suggestions", "suggestions_tab" => "ads"], home_url("/crm-app/")),
        "keywords" => add_query_arg(["view" => "suggestions", "suggestions_tab" => "keywords"], home_url("/crm-app/")),
    ];

    $seo = upsellio_suggestions_get("seo");
    $blog = upsellio_suggestions_get("blog");
    $ads = upsellio_suggestions_get("ads");
    $kw_ai = upsellio_suggestions_get("keywords");
    $clusters = upsellio_suggestions_get("clusters");

    ?>
    <section class="card">
      <h2 style="margin-top:0"><?php esc_html_e("Sugestie AI", "upsellio"); ?></h2>
      <p class="muted" style="margin:0 0 12px;font-size:13px;line-height:1.55"><?php esc_html_e("Interpretacja danych GSC, GA4 i CRM — konkretne działania. Cache sugestii 24 h (klaster 48 h).", "upsellio"); ?> <a href="<?php echo esc_url($settings_ai); ?>"><?php esc_html_e("Klucz API / model", "upsellio"); ?></a>.</p>
      <?php if (!apply_filters("upsellio_suggestions_cron_enabled", false)) : ?>
      <div style="margin:0 0 14px;padding:10px 12px;border-radius:8px;background:rgba(245,158,11,0.12);border:1px solid rgba(245,158,11,0.35);font-size:12px;line-height:1.5;color:var(--text-2)">
        <?php esc_html_e("Automatyczne odświeżanie sugestii (WP-Cron) jest wyłączone domyślnie — sugestie aktualizują się po kliknięciu „Odśwież AI”. Aby włączyć cron w kodzie:", "upsellio"); ?>
        <code style="display:block;margin-top:6px;font-size:11px;white-space:pre-wrap">add_filter( &quot;upsellio_suggestions_cron_enabled&quot;, &quot;__return_true&quot; );</code>
      </div>
      <?php endif; ?>
      <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px">
        <a class="btn <?php echo $suggestions_tab === "seo" ? "" : "alt"; ?>" href="<?php echo esc_url($tab_urls["seo"]); ?>"><?php esc_html_e("SEO", "upsellio"); ?></a>
        <a class="btn <?php echo $suggestions_tab === "blog" ? "" : "alt"; ?>" href="<?php echo esc_url($tab_urls["blog"]); ?>"><?php esc_html_e("Blog", "upsellio"); ?></a>
        <a class="btn <?php echo $suggestions_tab === "ads" ? "" : "alt"; ?>" href="<?php echo esc_url($tab_urls["ads"]); ?>"><?php esc_html_e("Google Ads", "upsellio"); ?></a>
        <a class="btn <?php echo $suggestions_tab === "keywords" ? "" : "alt"; ?>" href="<?php echo esc_url($tab_urls["keywords"]); ?>"><?php esc_html_e("Słowa kluczowe", "upsellio"); ?></a>
      </div>
      <?php
        $tok_sum = 0;
      foreach (["seo", "blog", "ads", "keywords"] as $tk) {
          $ent = upsellio_suggestions_get($tk);
          $tok_sum += (int) ($ent["approx_input_tokens"] ?? 0) + (int) ($ent["approx_output_tokens"] ?? 0);
      }
      if ($tok_sum > 0) :
          ?>
      <p class="muted" style="margin:0 0 14px;font-size:12px;line-height:1.45"><?php esc_html_e("Szacowany koszt ostatnich generacji (tokeny we/wy sumarycznie):", "upsellio"); ?> ~<?php echo (int) $tok_sum; ?></p>
      <?php endif; ?>
    </section>

    <?php if ($suggestions_tab === "seo") : ?>
      <section class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:12px">
          <h2 style="margin:0"><?php esc_html_e("SEO", "upsellio"); ?></h2>
          <button type="button" class="btn ups-sug-refresh" data-type="seo"><?php esc_html_e("Odśwież AI", "upsellio"); ?></button>
        </div>
        <?php
        $fresh = upsellio_suggestions_cache_is_fresh($seo);
        if (!empty($seo["generated_at"])) :
            $tok_note = "";
            if (!empty($seo["approx_input_tokens"])) {
                $tok_note = " · ~" . (int) $seo["approx_input_tokens"] . " / ~" . (int) ($seo["approx_output_tokens"] ?? 0) . " tok.";
            }
            ?>
          <p class="muted" style="font-size:12px;margin:0 0 12px"><?php echo esc_html($fresh ? __("Cache aktualny · ", "upsellio") : __("Cache przeterminowany · ", "upsellio")); ?><?php echo esc_html((string) ($seo["generated_at"] ?? "")); ?><?php echo esc_html($tok_note); ?></p>
        <?php endif; ?>
        <div id="ups-sug-seo-list">
          <?php
            $items = isset($seo["data"]["suggestions"]) && is_array($seo["data"]["suggestions"]) ? $seo["data"]["suggestions"] : [];
          foreach ($items as $it) {
              if (is_array($it)) {
                  upsellio_suggestions_render_card($it, "seo");
              }
          }
            if ($items === []) {
                echo '<p class="muted">' . esc_html__("Brak zapisanych sugestii — kliknij Odśwież AI.", "upsellio") . "</p>";
            }
            ?>
        </div>
        <p id="ups-sug-seo-msg" class="muted" style="font-size:13px;margin-top:10px"></p>
      </section>
    <?php endif; ?>

    <?php if ($suggestions_tab === "blog") : ?>
      <section class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:12px">
          <h2 style="margin:0"><?php esc_html_e("Blog — tematy vs GSC", "upsellio"); ?></h2>
          <div style="display:flex;flex-wrap:wrap;gap:8px">
            <button type="button" class="btn alt ups-sug-queue-all"<?php echo upsellio_suggestions_collect_blog_queue_keywords() === [] ? ' disabled="disabled" title="' . esc_attr__("Najpierw wygeneruj sugestie (Odśwież AI).", "upsellio") . '"' : ""; ?>><?php esc_html_e("Dodaj wszystkie do kolejki", "upsellio"); ?></button>
            <button type="button" class="btn ups-sug-refresh" data-type="blog"><?php esc_html_e("Odśwież AI", "upsellio"); ?></button>
          </div>
        </div>
        <?php
        $fresh_b = upsellio_suggestions_cache_is_fresh($blog);
        if (!empty($blog["generated_at"])) :
            $tok_b = "";
          if (!empty($blog["approx_input_tokens"])) {
              $tok_b = " · ~" . (int) $blog["approx_input_tokens"] . " / ~" . (int) ($blog["approx_output_tokens"] ?? 0) . " tok.";
          }
            ?>
          <p class="muted" style="font-size:12px;margin:0 0 12px"><?php echo esc_html($fresh_b ? __("Cache aktualny · ", "upsellio") : __("Cache przeterminowany · ", "upsellio")); ?><?php echo esc_html((string) ($blog["generated_at"] ?? "")); ?><?php echo esc_html($tok_b); ?></p>
        <?php endif; ?>
        <div id="ups-sug-blog-wrap">
          <?php
            $data = isset($blog["data"]) && is_array($blog["data"]) ? $blog["data"] : [];
            $qw = isset($data["quick_wins"]) && is_array($data["quick_wins"]) ? $data["quick_wins"] : [];
            $nt = isset($data["new_topics"]) && is_array($data["new_topics"]) ? $data["new_topics"] : [];
          if ($qw !== [] || $nt !== []) :
              ?>
            <h3 style="font-size:14px;margin:0 0 8px"><?php esc_html_e("Quick wins", "upsellio"); ?></h3>
            <?php
            foreach ($qw as $it) {
                if (!is_array($it)) {
                    continue;
                }
                $title = sanitize_text_field((string) ($it["title"] ?? ""));
                $pq = sanitize_text_field((string) ($it["primary_query"] ?? ""));
                $gp = $it["gsc_position"];
                $gi = $it["gsc_impressions"];
                $rat = sanitize_text_field((string) ($it["rationale"] ?? ""));
                $diff = sanitize_text_field((string) ($it["estimated_difficulty"] ?? ""));
                $seed_line = $pq !== "" ? $pq : $title;
                $blog_settings = esc_url(add_query_arg([
                    "view" => "settings",
                    "settings_tab" => "ai",
                    "blog_focus" => "1",
                    "seed" => $seed_line,
                ], home_url("/crm-app/"))) . "#ups-blog-bot-panel";
                ?>
              <article class="ups-sug-card" style="border:1px solid var(--border);border-radius:12px;padding:12px 14px;margin-bottom:10px;background:var(--bg)">
                <strong><?php echo esc_html($title); ?></strong>
                <p class="muted" style="font-size:12px;margin:6px 0"><?php echo esc_html($pq); ?> · poz. <?php echo esc_html(is_numeric($gp) ? (string) $gp : "—"); ?> · wyśw. <?php echo esc_html(is_numeric($gi) ? (string) $gi : "—"); ?></p>
                <p style="font-size:13px;margin:0 0 8px"><?php echo esc_html($rat); ?></p>
                <p style="font-size:12px;margin:0 0 8px"><?php esc_html_e("Trudność:", "upsellio"); ?> <?php echo esc_html($diff); ?></p>
                <a class="btn alt" href="<?php echo esc_url($blog_settings); ?>"><?php esc_html_e("→ Generuj w Blog Bocie", "upsellio"); ?></a>
                <button type="button" class="btn ups-sug-queue" data-keyword="<?php echo esc_attr($pq !== "" ? $pq : $title); ?>" style="margin-left:8px"><?php esc_html_e("Dodaj frazę do kolejki", "upsellio"); ?></button>
              </article>
                <?php
            }
              ?>
            <h3 style="font-size:14px;margin:16px 0 8px"><?php esc_html_e("Nowe tematy", "upsellio"); ?></h3>
            <?php
            foreach ($nt as $it) {
                if (!is_array($it)) {
                    continue;
                }
                $title = sanitize_text_field((string) ($it["title"] ?? ""));
                $pq = sanitize_text_field((string) ($it["primary_query"] ?? ""));
                $rat = sanitize_text_field((string) ($it["rationale"] ?? ""));
                $seed_nt = $pq !== "" ? $pq : $title;
                $blog_settings = esc_url(add_query_arg([
                    "view" => "settings",
                    "settings_tab" => "ai",
                    "blog_focus" => "1",
                    "seed" => $seed_nt,
                ], home_url("/crm-app/"))) . "#ups-blog-bot-panel";
                ?>
              <article style="border:1px solid var(--border);border-radius:12px;padding:12px;margin-bottom:10px;background:var(--bg)">
                <strong><?php echo esc_html($title); ?></strong>
                <p style="font-size:13px;margin:6px 0"><?php echo esc_html($rat); ?></p>
                <a class="btn alt" href="<?php echo esc_url($blog_settings); ?>"><?php esc_html_e("→ Generuj w Blog Bocie", "upsellio"); ?></a>
                <button type="button" class="btn ups-sug-queue" data-keyword="<?php echo esc_attr($pq !== "" ? $pq : $title); ?>"><?php esc_html_e("Dodaj frazę do kolejki", "upsellio"); ?></button>
              </article>
                <?php
            }
          else :
              echo '<p class="muted">' . esc_html__("Brak danych — Odśwież AI.", "upsellio") . "</p>";
          endif;
            ?>
        </div>
        <p id="ups-sug-blog-msg" class="muted" style="font-size:13px;margin-top:10px"></p>
      </section>
    <?php endif; ?>

    <?php if ($suggestions_tab === "ads") : ?>
      <section class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:12px">
          <h2 style="margin:0"><?php esc_html_e("Google Ads", "upsellio"); ?></h2>
          <button type="button" class="btn ups-sug-refresh" data-type="ads"><?php esc_html_e("Odśwież AI", "upsellio"); ?></button>
        </div>
        <?php
        $fresh_a = upsellio_suggestions_cache_is_fresh($ads);
        if (!empty($ads["generated_at"])) :
            $tok_a = "";
          if (!empty($ads["approx_input_tokens"])) {
              $tok_a = " · ~" . (int) $ads["approx_input_tokens"] . " / ~" . (int) ($ads["approx_output_tokens"] ?? 0) . " tok.";
          }
            ?>
          <p class="muted" style="font-size:12px;margin:0 0 12px"><?php echo esc_html($fresh_a ? __("Cache aktualny · ", "upsellio") : __("Cache przeterminowany · ", "upsellio")); ?><?php echo esc_html((string) $ads["generated_at"]); ?><?php echo esc_html($tok_a); ?></p>
        <?php endif; ?>
        <div id="ups-sug-ads-list">
          <?php
            $items = isset($ads["data"]["suggestions"]) && is_array($ads["data"]["suggestions"]) ? $ads["data"]["suggestions"] : [];
          foreach ($items as $it) {
              if (is_array($it)) {
                  upsellio_suggestions_render_card($it, "ads");
              }
          }
            if ($items === []) {
                echo '<p class="muted">' . esc_html__("Brak sugestii — Odśwież AI.", "upsellio") . "</p>";
            }
            ?>
        </div>
        <p id="ups-sug-ads-msg" class="muted" style="font-size:13px;margin-top:10px"></p>
      </section>
    <?php endif; ?>

    <?php if ($suggestions_tab === "keywords") : ?>
      <section class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:12px">
          <h2 style="margin:0"><?php esc_html_e("Słowa kluczowe — tabela", "upsellio"); ?></h2>
          <div style="display:flex;gap:8px;flex-wrap:wrap">
            <?php if (function_exists("upsellio_site_analytics_admin_url")) : ?>
              <a class="btn alt" href="<?php echo esc_url(upsellio_site_analytics_admin_url()); ?>"><?php esc_html_e("Importuj z GSC", "upsellio"); ?></a>
            <?php endif; ?>
            <button type="button" class="btn alt ups-sug-refresh" data-type="keywords"><?php esc_html_e("Odśwież wgląd AI", "upsellio"); ?></button>
            <button type="button" class="btn ups-sug-clusters"><?php esc_html_e("Analizuj klastry AI", "upsellio"); ?></button>
          </div>
        </div>
        <p class="muted" style="font-size:12px;margin:0 0 10px"><?php esc_html_e("Tabela z heurystyki GSC; sekcja „Insights” z osobnego wywołania AI.", "upsellio"); ?></p>
        <?php
        if (function_exists("upsellio_crm_render_gsc_analysis_panel")) {
            upsellio_crm_render_gsc_analysis_panel($ajax_url, $nonce);
        }
        ?>
        <div style="overflow:auto">
          <table style="width:100%;border-collapse:collapse;font-size:13px">
            <thead><tr><th><?php esc_html_e("Fraza", "upsellio"); ?></th><th><?php esc_html_e("Poz.", "upsellio"); ?></th><th><?php esc_html_e("Wyśw.", "upsellio"); ?></th><th><?php esc_html_e("Klik", "upsellio"); ?></th><th><?php esc_html_e("Kategoria", "upsellio"); ?></th><th><?php esc_html_e("Rekomendacja", "upsellio"); ?></th></tr></thead>
            <tbody>
              <?php foreach (upsellio_suggestions_keywords_table_rows(100) as $kr) : ?>
                <tr style="border-top:1px solid var(--border)">
                  <td><?php echo esc_html((string) ($kr["keyword"] ?? "")); ?></td>
                  <td><?php echo esc_html(number_format((float) ($kr["position"] ?? 0), 1, ",", "")); ?></td>
                  <td><?php echo esc_html((string) (int) ($kr["impressions"] ?? 0)); ?></td>
                  <td><?php echo esc_html((string) (int) ($kr["clicks"] ?? 0)); ?></td>
                  <td><?php echo esc_html((string) ($kr["category_display"] ?? $kr["category"] ?? "")); ?></td>
                  <td><?php echo esc_html((string) ($kr["recommendation"] ?? "")); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php if (isset($kw_ai["data"]["insights"]) && is_array($kw_ai["data"]["insights"]) && $kw_ai["data"]["insights"] !== []) : ?>
          <h3 style="margin-top:18px;font-size:15px"><?php esc_html_e("Insights AI", "upsellio"); ?></h3>
          <?php foreach ($kw_ai["data"]["insights"] as $ins) : ?>
            <?php if (!is_array($ins)) { continue; } ?>
            <article style="border:1px solid var(--border);border-radius:10px;padding:10px 12px;margin-bottom:8px;background:var(--bg)">
              <strong><?php echo esc_html((string) ($ins["title"] ?? "")); ?></strong>
              <p style="margin:6px 0 0;font-size:13px"><?php echo esc_html((string) ($ins["detail"] ?? "")); ?></p>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
        <div id="ups-sug-kw-msg" style="margin-top:10px;font-size:13px" class="muted"></div>

        <h3 style="margin-top:22px;font-size:15px"><?php esc_html_e("Klastry i kanibalizacja", "upsellio"); ?></h3>
        <?php
        $cf = isset($clusters["generated_at"]) ? upsellio_suggestions_cache_is_fresh(array_merge($clusters, ["ttl" => upsellio_suggestions_clusters_cache_ttl()])) : false;
        if (!empty($clusters["generated_at"])) :
            ?>
          <p class="muted" style="font-size:12px"><?php echo esc_html($cf ? __("Analiza w cache.", "upsellio") : __("Wygasły cache — uruchom ponownie.", "upsellio")); ?> <?php echo esc_html((string) $clusters["generated_at"]); ?></p>
        <?php endif; ?>
        <div id="ups-sug-cluster-wrap">
          <?php
            $cd = isset($clusters["data"]) && is_array($clusters["data"]) ? $clusters["data"] : [];
            $cl_list = isset($cd["clusters"]) && is_array($cd["clusters"]) ? $cd["clusters"] : [];
          foreach ($cl_list as $cl) {
              if (!is_array($cl)) {
                  continue;
              }
              $nm = sanitize_text_field((string) ($cl["name"] ?? ""));
              $fu = sanitize_text_field((string) ($cl["main_url"] ?? ""));
              $act = sanitize_text_field((string) ($cl["action"] ?? ""));
              $kws = [];
              if (isset($cl["keywords"]) && is_array($cl["keywords"])) {
                  $kws = array_merge($kws, $cl["keywords"]);
              }
              if (isset($cl["frazy"]) && is_array($cl["frazy"])) {
                  $kws = array_merge($kws, $cl["frazy"]);
              }
              $kws = array_values(array_unique(array_map("strval", $kws)));
              echo '<article style="border:1px solid var(--border);border-radius:10px;padding:12px;margin-bottom:10px;background:var(--bg)">';
              echo "<strong>" . esc_html($nm) . "</strong>";
              if ($fu !== "") {
                  echo '<p class="muted" style="font-size:12px;margin:6px 0">URL: ' . esc_html($fu) . "</p>";
              }
              if ($kws !== []) {
                  echo "<p style=\"font-size:13px;margin:6px 0\">" . esc_html(implode(", ", array_map("strval", $kws))) . "</p>";
              }
              if ($act !== "") {
                  echo '<p style="font-size:13px;margin:0">' . esc_html($act) . "</p>";
              }
              echo "</article>";
          }
          if ($cl_list === []) {
              echo '<p class="muted">' . esc_html__("Brak analizy — kliknij „Analizuj klastry AI”.", "upsellio") . "</p>";
          }
            $can = isset($cd["cannibalization"]) && is_array($cd["cannibalization"]) ? $cd["cannibalization"] : [];
          if ($can !== []) :
              ?>
            <h4 style="font-size:13px;margin:12px 0 6px"><?php esc_html_e("Kanibalizacja", "upsellio"); ?></h4>
            <?php
            foreach ($can as $c) {
                if (!is_array($c)) {
                    continue;
                }
                $fk = (string) ($c["keyword"] ?? $c["fraza"] ?? "");
                $line = $fk !== "" ? $fk . " — " : "";
                $line .= (string) ($c["problem"] ?? "");
                echo '<p style="font-size:13px;margin:0 0 6px">' . esc_html($line) . "</p>";
            }
          endif;
            $gaps = isset($cd["gaps"]) && is_array($cd["gaps"]) ? $cd["gaps"] : [];
          if ($gaps !== []) :
              ?>
            <h4 style="font-size:13px;margin:12px 0 6px"><?php esc_html_e("Luki", "upsellio"); ?></h4>
            <ul style="margin:0;padding-left:18px;font-size:13px">
              <?php foreach ($gaps as $g) : ?>
                <li><?php echo esc_html((string) $g); ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </section>
    <?php endif; ?>

    <script>
    (function(){
      var ajaxUrl = <?php echo wp_json_encode($ajax_url); ?>;
      var nonce = <?php echo wp_json_encode($nonce); ?>;
      function post(action, extra, cb) {
        var body = new URLSearchParams();
        body.set("action", action);
        body.set("nonce", nonce);
        if (extra) { Object.keys(extra).forEach(function(k){ body.set(k, extra[k]); }); }
        fetch(ajaxUrl, { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: body, credentials: "same-origin" })
          .then(function(r){ return r.json(); })
          .then(cb)
          .catch(function(){ cb({ success: false, data: { message: "Sieć" } }); });
      }
      document.querySelectorAll(".ups-sug-refresh").forEach(function(btn){
        btn.addEventListener("click", function(){
          var type = btn.getAttribute("data-type");
          var mid = type === "keywords" ? "ups-sug-kw-msg" : ("ups-sug-" + type + "-msg");
          var msg = document.getElementById(mid);
          if (msg) { msg.textContent = <?php echo wp_json_encode(__("Generowanie…", "upsellio")); ?>; }
          btn.disabled = true;
          post("upsellio_suggestions_refresh", { suggestion_type: type }, function(res){
            btn.disabled = false;
            if (msg) {
              msg.textContent = res.success ? (res.data && res.data.message ? res.data.message : "OK") : (res.data && res.data.message ? res.data.message : "Błąd");
            }
            if (res.success) { window.location.reload(); }
          });
        });
      });
      var clBtn = document.querySelector(".ups-sug-clusters");
      if (clBtn) {
        clBtn.addEventListener("click", function(){
          var msg = document.getElementById("ups-sug-kw-msg");
          if (msg) { msg.textContent = <?php echo wp_json_encode(__("Analiza klastrów…", "upsellio")); ?>; }
          clBtn.disabled = true;
          post("upsellio_keywords_clusters", {}, function(res){
            clBtn.disabled = false;
            if (msg) {
              msg.textContent = res.success ? (res.data && res.data.message ? res.data.message : "OK") : (res.data && res.data.message ? res.data.message : "Błąd");
            }
            if (res.success) { window.location.reload(); }
          });
        });
      }
      document.querySelectorAll(".ups-sug-queue").forEach(function(bt){
        bt.addEventListener("click", function(){
          var kw = bt.getAttribute("data-keyword") || "";
          post("upsellio_suggestions_queue_keyword", { keyword: kw }, function(res){
            alert(res.success ? <?php echo wp_json_encode(__("Dodano do kolejki Blog Bota.", "upsellio")); ?> : (res.data && res.data.message ? res.data.message : "Błąd"));
          });
        });
      });
      var qAll = document.querySelector(".ups-sug-queue-all");
      if (qAll && !qAll.hasAttribute("disabled")) {
        qAll.addEventListener("click", function(){
          qAll.disabled = true;
          post("upsellio_suggestions_queue_all_blog", {}, function(res){
            qAll.disabled = false;
            alert(res.success ? <?php echo wp_json_encode(__("Dopisano wszystkie sugestie do kolejki Blog Bota.", "upsellio")); ?> : (res.data && res.data.message ? res.data.message : "Błąd"));
            if (res.success && res.data && res.data.keywords_queue) {
              var ta = document.getElementById("ups-blog-bot-keywords-queue");
              if (ta) { ta.value = res.data.keywords_queue; }
            }
          });
        });
      }
    })();
    </script>
    <?php
}

function upsellio_crm_render_blog_keyword_research_panel(): void
{
    if (!current_user_can("manage_options")) {
        return;
    }
    $nonce = wp_create_nonce("upsellio_suggestions_nonce");
    $ajax_url = admin_url("admin-ajax.php");
    ?>
    <section class="card" style="margin-top:16px" id="ups-blog-kw-research">
      <h2 style="margin-top:0;font-size:17px"><?php esc_html_e("Research frazy przed Blog Botem", "upsellio"); ?></h2>
      <p class="muted" style="margin:0 0 12px;font-size:13px;line-height:1.55"><?php esc_html_e("Sprawdź duplikaty, intencję i klaster fraz (GSC + AI). Wynik możesz wykorzystać przy ręcznym wpisywaniu meta SEO lub kolejki.", "upsellio"); ?></p>
      <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;margin-bottom:12px">
        <label style="display:flex;flex-direction:column;gap:4px;font-weight:700;font-size:13px;flex:1;min-width:220px">
          <?php esc_html_e("Fraza seed", "upsellio"); ?>
          <input type="text" id="ups-blog-research-seed" placeholder="<?php esc_attr_e("np. kampanie google ads brzeg", "upsellio"); ?>" style="width:100%;border:1px solid var(--border);border-radius:8px;padding:8px 10px;background:var(--bg)" />
        </label>
        <button type="button" class="btn" id="ups-blog-research-btn"><?php esc_html_e("Sprawdź →", "upsellio"); ?></button>
      </div>
      <div id="ups-blog-research-out" style="display:none;font-size:13px;line-height:1.55;border:1px solid var(--border);border-radius:10px;padding:12px 14px;background:var(--bg-muted, rgba(0,0,0,.03))"></div>
      <div style="margin-top:12px;display:flex;flex-wrap:wrap;gap:8px">
        <button type="button" class="btn alt" id="ups-blog-research-apply" style="display:none"><?php esc_html_e("Użyj danych — uzupełnij kolejkę (fraza główna)", "upsellio"); ?></button>
      </div>
      <script>
      (function(){
        var ajaxUrl = <?php echo wp_json_encode($ajax_url); ?>;
        var nonce = <?php echo wp_json_encode($nonce); ?>;
        var btn = document.getElementById("ups-blog-research-btn");
        var applyBtn = document.getElementById("ups-blog-research-apply");
        var out = document.getElementById("ups-blog-research-out");
        var seedEl = document.getElementById("ups-blog-research-seed");
        var lastPrimary = "";
        if (!btn || !out) return;
        btn.addEventListener("click", function(){
          var seed = seedEl && seedEl.value ? seedEl.value.trim() : "";
          if (!seed) { alert("Podaj frazę."); return; }
          btn.disabled = true;
          out.style.display = "block";
          out.textContent = <?php echo wp_json_encode(__("Analiza…", "upsellio")); ?>;
          var body = new URLSearchParams();
          body.set("action", "upsellio_blog_keyword_research");
          body.set("nonce", nonce);
          body.set("seed", seed);
          fetch(ajaxUrl, { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: body, credentials: "same-origin" })
            .then(function(r){ return r.json(); })
            .then(function(res){
              btn.disabled = false;
              if (!res.success) {
                out.textContent = res.data && res.data.message ? res.data.message : "Błąd";
                if (applyBtn) {
                  applyBtn.style.display = "none";
                }
                return;
              }
              var d = res.data || {};
              lastPrimary = d.primary_query || seed;
              var html = "<strong>" + (d.verdict || "") + "</strong>";
              if (d.verdict_reason) html += "<p style=\"margin:8px 0\">" + esc(d.verdict_reason) + "</p>";
              if (d.suggested_title) html += "<p><strong>Tytuł:</strong> " + esc(d.suggested_title) + "</p>";
              if (d.search_intent) html += "<p><strong>Intencja:</strong> " + esc(d.search_intent) + "</p>";
              if (d.query_cluster && d.query_cluster.length) html += "<p><strong>Query cluster:</strong> " + esc(d.query_cluster.join(", ")) + "</p>";
              if (d.user_questions && d.user_questions.length) html += "<ul style=\"margin:6px 0 0 18px\">" + d.user_questions.map(function(q){ return "<li>" + esc(q) + "</li>"; }).join("") + "</ul>";
              out.innerHTML = html;
              if (applyBtn) {
                applyBtn.style.display = "inline-block";
              }
            })
            .catch(function(){ btn.disabled = false; out.textContent = "Sieć"; });
        });
        function esc(s){ var d=document.createElement("div"); d.textContent=s; return d.innerHTML; }
        if (applyBtn) {
          applyBtn.addEventListener("click", function(){
            var kw = lastPrimary || (seedEl ? seedEl.value.trim() : "");
            if (!kw) return;
            var body = new URLSearchParams();
            body.set("action", "upsellio_suggestions_queue_keyword");
            body.set("nonce", nonce);
            body.set("keyword", kw);
            fetch(ajaxUrl, { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: body, credentials: "same-origin" })
              .then(function(r){ return r.json(); })
              .then(function(res){
                alert(res.success ? <?php echo wp_json_encode(__("Dopisano frazę do kolejki Blog Bota.", "upsellio")); ?> : (res.data && res.data.message ? res.data.message : "Błąd"));
                var ta = document.getElementById("ups-blog-bot-keywords-queue");
                if (ta && res.success && res.data && res.data.keywords_queue) { ta.value = res.data.keywords_queue; }
              });
          });
        }
      })();
      </script>
    </section>
    <?php
}

/**
 * Cron WP: odświeża przeterminowany cache 4 zakładek (SEO, Blog, Ads, Keywords).
 * Domyślnie wyłączone — unika nieoczekiwanych kosztów API.
 *
 * Włączenie w motywie potomnym lub pluginie:
 * add_filter( 'upsellio_suggestions_cron_enabled', '__return_true' );
 */
function upsellio_suggestions_cron_daily_refresh(): void
{
    if (!apply_filters("upsellio_suggestions_cron_enabled", false)) {
        return;
    }
    if (!function_exists("upsellio_suggestions_generate")) {
        return;
    }
    foreach (["seo", "blog", "ads", "keywords"] as $t) {
        $e = upsellio_suggestions_get($t);
        if (!upsellio_suggestions_cache_is_fresh($e)) {
            upsellio_suggestions_generate($t);
        }
    }
}

add_action("upsellio_suggestions_daily_refresh", "upsellio_suggestions_cron_daily_refresh");

add_action("init", static function (): void {
    if (defined("WP_INSTALLING") && WP_INSTALLING) {
        return;
    }
    if (!wp_next_scheduled("upsellio_suggestions_daily_refresh")) {
        wp_schedule_event(time() + HOUR_IN_SECONDS, "daily", "upsellio_suggestions_daily_refresh");
    }
}, 20);
