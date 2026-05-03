<?php

if (!defined("ABSPATH")) {
    exit;
}

/**
 * Opportunity score: wolumen vs CPC i konkurencja (0–100).
 */
function upsellio_kw_opportunity_score(int $volume, string $competition, float $cpc): int
{
    if ($volume <= 0) {
        return 0;
    }
    $comp_factor = 2.0;
    if ($competition === "LOW") {
        $comp_factor = 1.0;
    } elseif ($competition === "MEDIUM") {
        $comp_factor = 1.8;
    } elseif ($competition === "HIGH") {
        $comp_factor = 3.5;
    }
    $den = max(0.1, $cpc * $comp_factor);
    $score = ($volume / $den) / 100;

    return (int) min(100, max(0, round($score)));
}

/**
 * @param string[] $seed_keywords  Max 20 fraz seed
 * @param string   $language_constant Resource ID (np. 1030 = polski)
 * @param string[] $geo_targets    ID geo (np. 2616 = Polska)
 * @return array<int, array<string, mixed>>|\WP_Error
 */
function upsellio_keyword_planner_get_ideas(
    array $seed_keywords,
    string $language_constant = "1030",
    array $geo_targets = ["2616"]
) {
    if (!function_exists("upsellio_google_ads_api_ready") || !upsellio_google_ads_api_ready()) {
        return new WP_Error("ads_not_ready", __("Google Ads API nie jest skonfigurowane. Sprawdź Developer token i Customer ID.", "upsellio"));
    }

    $cfg = upsellio_google_ads_get_settings();
    $creds = upsellio_get_gsc_credentials();
    $token = upsellio_gsc_get_access_token($creds);
    if (is_wp_error($token)) {
        return $token;
    }

    $customer_id = $cfg["customer_id"];
    $url = upsellio_google_ads_rest_base_url()
        . "/customers/{$customer_id}:generateKeywordIdeas";

    $seeds = array_values(array_filter(array_map("trim", $seed_keywords)));
    $seeds = array_slice($seeds, 0, 20);
    if ($seeds === []) {
        return new WP_Error("no_seeds", __("Brak fraz seed.", "upsellio"));
    }

    $geo_list = array_map(
        static function ($g) {
            return "geoTargetConstants/" . preg_replace("/\D+/", "", (string) $g);
        },
        $geo_targets !== [] ? $geo_targets : ["2616"]
    );

    $payload = [
        "language" => "languageConstants/" . preg_replace("/\D+/", "", $language_constant),
        "geoTargetConstants" => $geo_list,
        "includeAdultKeywords" => false,
        "keywordSeed" => ["keywords" => $seeds],
        "keywordPlanNetwork" => "GOOGLE_SEARCH",
    ];

    $response = wp_remote_post($url, [
        "timeout" => 35,
        "headers" => array_merge(
            upsellio_google_ads_request_headers((string) $token),
            ["Content-Type" => "application/json"]
        ),
        "body" => wp_json_encode($payload),
    ]);

    if (is_wp_error($response)) {
        return $response;
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    $body = json_decode((string) wp_remote_retrieve_body($response), true);

    if ($code >= 400) {
        $msg = upsellio_gsc_extract_error_message(is_array($body) ? $body : [], "Planner API HTTP {$code}");

        return new WP_Error("planner_error", $msg);
    }

    $results = [];
    foreach ((array) ($body["results"] ?? []) as $item) {
        if (!is_array($item)) {
            continue;
        }
        $kw = (string) ($item["text"] ?? "");
        $metrics = (array) ($item["keywordIdeaMetrics"] ?? []);

        $avg_monthly = (int) ($metrics["avgMonthlySearches"] ?? 0);
        $competition = (string) ($metrics["competition"] ?? "UNKNOWN");
        $cpc_micros = (int) ($metrics["averageCpcMicros"] ?? 0);
        $cpc_pln = round($cpc_micros / 1000000, 2);

        $monthly_searches = [];
        foreach ((array) ($metrics["monthlySearchVolumes"] ?? []) as $mv) {
            if (!is_array($mv)) {
                continue;
            }
            $monthly_searches[] = [
                "month" => (int) ($mv["month"] ?? 0),
                "year" => (int) ($mv["year"] ?? 0),
                "volume" => (int) ($mv["monthlySearches"] ?? 0),
            ];
        }

        $results[] = [
            "keyword" => $kw,
            "avg_monthly" => $avg_monthly,
            "competition" => $competition,
            "cpc_pln" => $cpc_pln,
            "monthly_searches" => $monthly_searches,
            "opportunity" => upsellio_kw_opportunity_score($avg_monthly, $competition, $cpc_pln),
        ];
    }

    usort(
        $results,
        static function ($a, $b) {
            return ($b["opportunity"] ?? 0) <=> ($a["opportunity"] ?? 0);
        }
    );

    return $results;
}

/**
 * @return array|\WP_Error
 */
function upsellio_keyword_planner_cached(array $seeds, string $cache_key_extra = "")
{
    $norm = implode("|", array_map("strtolower", array_map("trim", $seeds)));
    $cache_key = $cache_key_extra !== ""
        ? $cache_key_extra
        : "ups_kw_planner_" . md5($norm);

    $cached = get_transient($cache_key);
    if ($cached !== false && is_array($cached)) {
        return $cached;
    }

    $geo = apply_filters("upsellio_keyword_planner_geo_ids", ["2616"]);
    $lang = apply_filters("upsellio_keyword_planner_language_id", "1030");

    $result = upsellio_keyword_planner_get_ideas($seeds, (string) $lang, is_array($geo) ? $geo : ["2616"]);
    if (!is_wp_error($result)) {
        set_transient($cache_key, $result, DAY_IN_SECONDS);
    }

    return $result;
}

/**
 * @param array<int, array<string, mixed>> $planner_results
 * @return array<int, array<string, mixed>>
 */
function upsellio_keyword_merge_gsc_planner(array $planner_results): array
{
    $gsc_rows = get_option("upsellio_keyword_metrics_rows", []);
    if (!is_array($gsc_rows)) {
        $gsc_rows = [];
    }

    $gsc_map = [];
    foreach ($gsc_rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $kw = strtolower(trim((string) ($row["keyword"] ?? "")));
        if ($kw === "") {
            continue;
        }
        $imp = (int) ($row["impressions"] ?? 0);
        if (!isset($gsc_map[$kw]) || $imp > (int) ($gsc_map[$kw]["impressions"] ?? 0)) {
            $gsc_map[$kw] = $row;
        }
    }

    foreach ($planner_results as &$item) {
        $kw = strtolower(trim((string) ($item["keyword"] ?? "")));
        $gsc = isset($gsc_map[$kw]) ? $gsc_map[$kw] : null;

        $item["gsc_position"] = $gsc ? (float) ($gsc["position"] ?? 0) : null;
        $item["gsc_impressions"] = $gsc ? (int) ($gsc["impressions"] ?? 0) : null;
        $item["gsc_clicks"] = $gsc ? (int) ($gsc["clicks"] ?? 0) : null;
        $item["in_gsc"] = $gsc !== null;
        $pos = $gsc ? (float) ($gsc["position"] ?? 99) : 99;
        $item["ads_quick_win"] = $gsc !== null && $pos > 0 && $pos <= 30;
    }
    unset($item);

    return $planner_results;
}

/**
 * @return array|\WP_Error
 */
function upsellio_keyword_ai_cluster(array $keywords)
{
    if (!function_exists("upsellio_anthropic_crm_send_user_prompt")) {
        return new WP_Error("no_ai", __("AI nie jest skonfigurowane.", "upsellio"));
    }

    $kw_lines = [];
    foreach (array_slice($keywords, 0, 60) as $kw) {
        if (!is_array($kw)) {
            continue;
        }
        $gsc_hint = "";
        if (!empty($kw["in_gsc"]) && isset($kw["gsc_position"])) {
            $gsc_hint = sprintf(" | GSC poz: %.0f", (float) $kw["gsc_position"]);
        }
        $kw_lines[] = sprintf(
            '"%s" | vol: %d | CPC: %.2f PLN | konkur: %s%s',
            (string) ($kw["keyword"] ?? ""),
            (int) ($kw["avg_monthly"] ?? 0),
            (float) ($kw["cpc_pln"] ?? 0),
            (string) ($kw["competition"] ?? ""),
            $gsc_hint
        );
    }

    $prompt = "Analizujesz frazy kluczowe dla polskiej agencji marketingowej B2B (Google Ads + SEO).\n\n"
        . "FRAZY:\n" . implode("\n", $kw_lines) . "\n\n"
        . "Zadania:\n"
        . "1. KLASTRY: Pogrupuj frazy w max 6 klastrów tematycznych. Każdy klaster = osobna kampania Ads lub kategoria treści SEO.\n"
        . "2. PRIORYTET: Dla każdego klastra oceń czy lepiej: ads / seo / oba.\n"
        . "3. LUKI: Wskaż frazy których nie ma w GSC (kolumna in_gsc=false) a mają wysoki wolumen — to luki SEO.\n"
        . "4. SZYBKIE WINY ADS: Frazy z GSC poz 5-30 warto testować w Ads dla podwójnej widoczności.\n\n"
        . "Odpowiedz WYŁĄCZNIE JSON:\n"
        . '{"clusters":[{"name":"...","keywords":["..."],"channel":"ads|seo|both","priority":"high|medium|low","rationale":"..."}],'
        . '"seo_gaps":["fraza1","fraza2"],"ads_quick_wins":["fraza1","fraza2"],"summary":"2-3 zdania co zrobić najpierw"}';

    $km = function_exists("upsellio_ai_model_for") ? upsellio_ai_model_for("keyword_cluster") : null;
    $raw = upsellio_anthropic_crm_send_user_prompt($prompt, 800, 45, $km);
    if (function_exists("upsellio_anthropic_crm_parse_json_object")) {
        $parsed = upsellio_anthropic_crm_parse_json_object((string) $raw);
    } else {
        $parsed = json_decode((string) $raw, true);
    }

    return is_array($parsed) ? $parsed : new WP_Error("parse_error", __("Błąd parsowania AI.", "upsellio"));
}

/**
 * @param array<int, array<string, mixed>> $competitors
 * @param array<int, array<string, mixed>> $gsc_keywords
 */
function upsellio_competitor_ai_analysis(array $competitors, array $gsc_keywords): string
{
    if (!function_exists("upsellio_anthropic_crm_send_user_prompt")) {
        return "";
    }

    $comp_lines = [];
    foreach (array_slice($competitors, 0, 10) as $c) {
        if (!is_array($c)) {
            continue;
        }
        $comp_lines[] = sprintf(
            "%s: IS=%s%%, overlap=%s%%, above=%s%%",
            (string) ($c["domain"] ?? ""),
            (string) ($c["impression_share"] ?? ""),
            (string) ($c["overlap_rate"] ?? ""),
            (string) ($c["position_above_rate"] ?? "")
        );
    }

    $kw_sample = [];
    foreach (array_slice($gsc_keywords, 0, 15) as $r) {
        if (!is_array($r)) {
            continue;
        }
        $kw_sample[] = sprintf(
            '"%s" poz.%s (%s wyśw.)',
            (string) ($r["keyword"] ?? ""),
            (string) ($r["position"] ?? ""),
            (string) ($r["impressions"] ?? "")
        );
    }

    $prompt = "Analizujesz konkurencję dla polskiej agencji marketingowej B2B.\n\n"
        . "AUCTION INSIGHTS (konkurenci w tych samych aukcjach Google Ads):\n"
        . implode("\n", $comp_lines) . "\n\n"
        . "NASZE FRAZY W GSC (co rankujemy organicznie):\n"
        . implode("\n", $kw_sample) . "\n\n"
        . "Na podstawie tych danych:\n"
        . "1. Wskaż 3 głównych konkurentów i co robią lepiej (impression share > nasze)\n"
        . "2. Sugestie słów kluczowych które oni targetują a my nie\n"
        . "3. Rekomendacje: gdzie zwiększyć budżet, gdzie nie warto walczyć\n"
        . "4. Quick wins: gdzie mamy overlap > 70% — to drogie i możemy przegrywać\n\n"
        . "Odpowiedz po polsku, konkretnie, max 400 słów.";

    $cm = function_exists("upsellio_ai_model_for") ? upsellio_ai_model_for("competitor_analysis") : null;

    return (string) upsellio_anthropic_crm_send_user_prompt($prompt, 600, 45, $cm);
}

function upsellio_ajax_keyword_research(): void
{
    check_ajax_referer("ups_crm_app_action", "nonce");
    if (!current_user_can("edit_posts")) {
        wp_send_json_error("forbidden");
    }

    $raw_seeds = $_POST["seeds"] ?? [];
    if (is_string($raw_seeds)) {
        $decoded = json_decode(wp_unslash($raw_seeds), true);
        $seeds = is_array($decoded) ? $decoded : [];
    } else {
        $seeds = (array) $raw_seeds;
    }

    $seeds = array_filter(
        array_map(
            static function ($s) {
                return sanitize_text_field((string) $s);
            },
            $seeds
        ),
        static function ($s) {
            return strlen($s) > 1;
        }
    );

    if ($seeds === []) {
        wp_send_json_error(__("Podaj minimum jedną frazę.", "upsellio"));
    }

    $force = !empty($_POST["force_refresh"]);
    if ($force) {
        $cache_key = "ups_kw_planner_" . md5(implode("|", array_map("strtolower", $seeds)));
        delete_transient($cache_key);
    }

    $geo_id = isset($_POST["geo"]) ? preg_replace("/\D+/", "", (string) wp_unslash($_POST["geo"])) : "";
    $geo = $geo_id !== "" ? [$geo_id] : (array) apply_filters("upsellio_keyword_planner_geo_ids", ["2616"]);

    $result = upsellio_keyword_planner_get_ideas($seeds, "1030", $geo);
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }

    $merged = upsellio_keyword_merge_gsc_planner($result);
    wp_send_json_success(["keywords" => $merged, "count" => count($merged)]);
}
add_action("wp_ajax_upsellio_keyword_research", "upsellio_ajax_keyword_research");

function upsellio_ajax_keyword_ai_cluster(): void
{
    check_ajax_referer("ups_crm_app_action", "nonce");
    if (!current_user_can("edit_posts")) {
        wp_send_json_error("forbidden");
    }

    $raw = isset($_POST["keywords"]) ? wp_unslash((string) $_POST["keywords"]) : "";
    $keywords = json_decode($raw, true);
    if (!is_array($keywords) || $keywords === []) {
        wp_send_json_error(__("Brak fraz do analizy.", "upsellio"));
    }

    $result = upsellio_keyword_ai_cluster($keywords);
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }

    wp_send_json_success($result);
}
add_action("wp_ajax_upsellio_keyword_ai_cluster", "upsellio_ajax_keyword_ai_cluster");

function upsellio_ajax_ads_auction_insights(): void
{
    check_ajax_referer("ups_crm_app_action", "nonce");
    if (!current_user_can("edit_posts")) {
        wp_send_json_error("forbidden");
    }

    if (!function_exists("upsellio_google_ads_fetch_auction_insights")) {
        wp_send_json_error(__("Moduł Ads nie załadowany.", "upsellio"));
    }

    $result = upsellio_google_ads_fetch_auction_insights();
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }

    wp_send_json_success(["competitors" => $result]);
}
add_action("wp_ajax_upsellio_ads_auction_insights", "upsellio_ajax_ads_auction_insights");

function upsellio_ajax_competitor_ai_analysis(): void
{
    check_ajax_referer("ups_crm_app_action", "nonce");
    if (!current_user_can("edit_posts")) {
        wp_send_json_error("forbidden");
    }

    $raw = isset($_POST["competitors"]) ? wp_unslash((string) $_POST["competitors"]) : "";
    $competitors = json_decode($raw, true);
    $gsc_rows = get_option("upsellio_keyword_metrics_rows", []);

    $analysis = upsellio_competitor_ai_analysis(
        is_array($competitors) ? $competitors : [],
        is_array($gsc_rows) ? $gsc_rows : []
    );

    wp_send_json_success(["analysis" => $analysis]);
}
add_action("wp_ajax_upsellio_competitor_ai_analysis", "upsellio_ajax_competitor_ai_analysis");

function upsellio_ajax_keyword_client_plan(): void
{
    check_ajax_referer("ups_crm_app_action", "nonce");
    if (!current_user_can("edit_posts")) {
        wp_send_json_error("forbidden");
    }

    $client_id = isset($_POST["client_id"]) ? (int) wp_unslash($_POST["client_id"]) : 0;
    $budget = isset($_POST["budget"]) ? max(0, (int) wp_unslash($_POST["budget"])) : 3000;
    $seeds_raw = sanitize_textarea_field((string) wp_unslash($_POST["seeds"] ?? ""));

    if ($client_id <= 0) {
        wp_send_json_error(__("Brak klienta.", "upsellio"));
    }

    $client_name = get_the_title($client_id);
    $industry = (string) get_post_meta($client_id, "_ups_client_industry", true);
    $website = (string) get_post_meta($client_id, "_ups_client_website", true);
    $budget_range = (string) get_post_meta($client_id, "_ups_client_budget_range", true);

    $seeds = array_filter(array_map("trim", preg_split("/\R/", $seeds_raw)));

    $planner_data = [];
    if ($seeds !== [] && function_exists("upsellio_google_ads_api_ready") && upsellio_google_ads_api_ready()) {
        // Ten sam klucz cache co research ogólny (seed hash) — unikamy podwójnego wywołania Planner dla tych samych fraz.
        $result = upsellio_keyword_planner_cached($seeds, "");
        if (!is_wp_error($result)) {
            $planner_data = upsellio_keyword_merge_gsc_planner($result);
        }
    }

    $kw_context = "";
    if ($planner_data !== []) {
        $kw_lines = [];
        foreach (array_slice($planner_data, 0, 30) as $kw) {
            if (!is_array($kw)) {
                continue;
            }
            $kw_lines[] = sprintf(
                '"%s": vol=%d CPC=%.2f PLN konkur=%s%s',
                (string) ($kw["keyword"] ?? ""),
                (int) ($kw["avg_monthly"] ?? 0),
                (float) ($kw["cpc_pln"] ?? 0),
                (string) ($kw["competition"] ?? ""),
                !empty($kw["in_gsc"]) ? " GSC-poz=" . (string) ($kw["gsc_position"] ?? "") : ""
            );
        }
        $kw_context = "DANE Z KEYWORD PLANNER:\n" . implode("\n", $kw_lines) . "\n\n";
    }

    $company_context = (string) get_option("ups_ai_company_context", "");

    if (!function_exists("upsellio_anthropic_crm_send_user_prompt")) {
        wp_send_json_error(__("AI nie jest skonfigurowane.", "upsellio"));
    }

    $prompt = "Tworzysz plan słów kluczowych dla klienta agencji marketingowej.\n\n"
        . "KLIENT: {$client_name}\n"
        . "Branża: {$industry}\n"
        . ($website !== "" ? "Strona: {$website}\n" : "")
        . "Budżet Ads: {$budget} PLN/mies.\n"
        . ($budget_range !== "" ? "Deklarowany budżet: {$budget_range}\n" : "")
        . "\n"
        . $kw_context
        . "AGENCJA (kontekst):\n{$company_context}\n\n"
        . "Zadanie: Stwórz konkretny plan działań keyword'owych dla tego klienta:\n"
        . "1. TOP 10 fraz do kampanii Google Ads (z uzasadnieniem i estym. kosztem miesięcznym)\n"
        . "2. TOP 5 fraz SEO (długoterminowe pozycjonowanie)\n"
        . "3. Frazy wykluczające (negative keywords) — co blokuje przepalanie budżetu\n"
        . "4. Rekomendacja struktury kampanii (ile grup reklam, jak podzielić budżet {$budget} PLN)\n"
        . "5. Estymowany koszt pozyskania leada przy tym budżecie (szacunek na podstawie CPC)\n\n"
        . "Pisz konkretnie, po polsku, dla osoby która będzie to wdrażać.";

    $pm = function_exists("upsellio_ai_model_for") ? upsellio_ai_model_for("client_plan") : null;
    $plan = upsellio_anthropic_crm_send_user_prompt($prompt, 1800, 90, $pm);

    wp_send_json_success(["plan" => (string) $plan]);
}
add_action("wp_ajax_upsellio_keyword_client_plan", "upsellio_ajax_keyword_client_plan");
