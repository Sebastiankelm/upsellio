<?php

if (!defined("ABSPATH")) {
    exit;
}

/**
 * Kontekst marketingowy dla AI (oferta / inbox): UTM deala, ROAS z Silnika sprzedaży,
 * skrót GSC z jednego cache (upsellio_gsc_analyze_full).
 */

/**
 * Blok tekstu dla promptów AI powiązanych z ofertą (crm_offer).
 */
function upsellio_crm_data_context_for_offer(int $offer_id): string
{
    $offer_id = (int) $offer_id;
    if ($offer_id <= 0 || get_post_type($offer_id) !== "crm_offer") {
        return "";
    }

    $parts = [];

    $utm_s = trim((string) get_post_meta($offer_id, "_ups_offer_utm_source", true));
    $utm_m = trim((string) get_post_meta($offer_id, "_ups_offer_utm_medium", true));
    $utm_c = trim((string) get_post_meta($offer_id, "_ups_offer_utm_campaign", true));
    if ($utm_s !== "" || $utm_m !== "" || $utm_c !== "") {
        $parts[] = "UTM deala (oferta): "
            . "source=" . ($utm_s !== "" ? $utm_s : "—")
            . ", medium=" . ($utm_m !== "" ? $utm_m : "—")
            . ", campaign=" . ($utm_c !== "" ? $utm_c : "—")
            . " — zwykle zsynchronizowane z leadem przy konwersji.";
    }

    $lead_ids = upsellio_crm_data_context_find_leads_for_offer($offer_id);
    foreach (array_slice($lead_ids, 0, 2) as $lid) {
        $ls = trim((string) get_post_meta($lid, "_ups_lead_utm_source", true));
        $lm = trim((string) get_post_meta($lid, "_ups_lead_utm_medium", true));
        $lc = trim((string) get_post_meta($lid, "_ups_lead_utm_campaign", true));
        if ($ls !== "" || $lm !== "" || $lc !== "") {
            $parts[] = "UTM pierwotnego leada #{$lid}: source={$ls}, medium={$lm}, campaign={$lc}";
        }
    }

    $cid = (int) get_post_meta($offer_id, "_ups_offer_client_id", true);
    if ($cid > 0 && get_post_type($cid) === "crm_client") {
        $parts[] = "Klient CRM: " . sanitize_text_field(get_the_title($cid));
    }

    $roas = upsellio_crm_data_context_roas_block($utm_s, $utm_c);
    if ($roas !== "") {
        $parts[] = $roas;
    }

    $gsc = upsellio_crm_data_context_gsc_aggregate_lines(16);
    if ($gsc !== "") {
        $parts[] = "GSC (witryna — skrót z cache analizy PHP):\n" . $gsc;
    }

    return implode("\n", array_filter(array_map("trim", $parts)));
}

/**
 * Szuka leadów powiązanych z ofertą (email klienta / kontekst konwersji).
 *
 * @return list<int>
 */
function upsellio_crm_data_context_find_leads_for_offer(int $offer_id): array
{
    $offer_id = (int) $offer_id;
    $client_id = (int) get_post_meta($offer_id, "_ups_offer_client_id", true);
    $email = "";
    if ($client_id > 0) {
        $email = strtolower(trim((string) get_post_meta($client_id, "_ups_client_email", true)));
    }
    if ($email === "" || !post_type_exists("crm_lead")) {
        return [];
    }

    $q = new WP_Query([
        "post_type" => "crm_lead",
        "post_status" => "any",
        "posts_per_page" => 5,
        "orderby" => "modified",
        "order" => "DESC",
        "meta_query" => [
            [
                "key" => "_ups_lead_email",
                "value" => $email,
                "compare" => "=",
            ],
        ],
        "fields" => "ids",
        "no_found_rows" => true,
    ]);

    $ids = [];
    foreach ($q->posts as $pid) {
        $ids[] = (int) $pid;
    }

    return $ids;
}

/**
 * Dopasowanie wiersza ROAS do UTM (rozluźnione — źródła bywają zapisane inaczej niż w CRM).
 */
function upsellio_crm_data_context_roas_block(string $utm_source, string $utm_campaign): string
{
    if (!function_exists("upsellio_sales_engine_build_roas_report_rows")) {
        return "";
    }
    $utm_source = strtolower(trim($utm_source));
    $utm_campaign = strtolower(trim($utm_campaign));
    if ($utm_source === "" && $utm_campaign === "") {
        return "";
    }

    $rows = upsellio_sales_engine_build_roas_report_rows();
    if (!is_array($rows) || $rows === []) {
        return "ROAS: brak wierszy w Silniku sprzedaży (dodaj koszty kampanii).";
    }

    $best = null;
    $best_score = 0;
    foreach ($rows as $r) {
        if (!is_array($r)) {
            continue;
        }
        $src = strtolower(trim((string) ($r["source"] ?? "")));
        $camp = strtolower(trim((string) ($r["campaign"] ?? "")));
        $score = 0;
        if ($utm_source !== "" && ($src === $utm_source || strpos($src, $utm_source) !== false || strpos($utm_source, $src) !== false)) {
            $score += 3;
        }
        if ($utm_campaign !== "" && ($camp === $utm_campaign || strpos($camp, $utm_campaign) !== false || strpos($utm_campaign, $camp) !== false)) {
            $score += 3;
        }
        if ($utm_source !== "" && $utm_campaign !== "" && $src !== "" && $camp !== "") {
            $nk = $src . "|" . $camp;
            $needle = $utm_source . "|" . $utm_campaign;
            if (strpos($nk, $needle) !== false || strpos($needle, $nk) !== false) {
                $score += 2;
            }
        }
        if ($score > $best_score) {
            $best_score = $score;
            $best = $r;
        }
    }

    if ($best === null || $best_score < 2) {
        return "ROAS: brak bezpośredniego dopasowania UTM do wiersza kosztów — sprawdź Silnik sprzedaży (źródło/kampania).";
    }

    return sprintf(
        "ROAS (dopasowanie do UTM): %s / %s — wydano %.0f PLN | %d leadów | %d wygranych | przychód %.0f PLN | ROAS %s",
        (string) ($best["source"] ?? ""),
        (string) ($best["campaign"] ?? ""),
        (float) ($best["spend"] ?? 0),
        (int) ($best["leads"] ?? 0),
        (int) ($best["won"] ?? 0),
        (float) ($best["revenue"] ?? 0),
        (string) ($best["roas"] ?? "0")
    );
}

/**
 * Linie GSC z tego samego cache co analiza PHP (nie duplikuje surowego CSV osobno).
 */
function upsellio_crm_data_context_gsc_aggregate_lines(int $max_lines = 18): string
{
    $agg = [];
    if (function_exists("upsellio_gsc_analyze_full")) {
        $analysis = upsellio_gsc_analyze_full();
        $agg = isset($analysis["aggregated"]) && is_array($analysis["aggregated"]) ? $analysis["aggregated"] : [];
    }
    if ($agg === [] && function_exists("upsellio_gsc_aggregate_keywords")) {
        $raw = get_option("upsellio_keyword_metrics_rows", []);
        if (is_array($raw) && $raw !== []) {
            $agg = upsellio_gsc_aggregate_keywords($raw);
        }
    }
    if ($agg === []) {
        return "";
    }

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
        $url_disp = $url_raw !== "" ? esc_url_raw($url_raw) : "—";
        $lines[] = sprintf(
            "- \"%s\" | poz. %.1f | wyśw. %d | klik %d | CTR %.2f%% | URL %s",
            $kw,
            (float) ($row["position"] ?? 0),
            (int) ($row["impressions"] ?? 0),
            (int) ($row["clicks"] ?? 0),
            (float) ($row["ctr"] ?? 0),
            $url_disp
        );
        $n++;
        if ($n >= $max_lines) {
            break;
        }
    }

    return implode("\n", $lines);
}

/**
 * Krótki kontekst dla leada (UTM z meta).
 */
function upsellio_crm_data_context_for_lead(int $lead_id): string
{
    $lead_id = (int) $lead_id;
    if ($lead_id <= 0) {
        return "";
    }
    $t = get_post_type($lead_id);
    if (!in_array($t, ["crm_lead", "lead"], true)) {
        return "";
    }

    $parts = [];
    $ls = trim((string) get_post_meta($lead_id, "_ups_lead_utm_source", true));
    $lm = trim((string) get_post_meta($lead_id, "_ups_lead_utm_medium", true));
    $lc = trim((string) get_post_meta($lead_id, "_ups_lead_utm_campaign", true));
    if ($ls !== "" || $lm !== "" || $lc !== "") {
        $parts[] = "UTM leada: source={$ls}, medium={$lm}, campaign={$lc}";
    }
    $roas = upsellio_crm_data_context_roas_block($ls, $lc);
    if ($roas !== "") {
        $parts[] = $roas;
    }

    return implode("\n", array_filter(array_map("trim", $parts)));
}
