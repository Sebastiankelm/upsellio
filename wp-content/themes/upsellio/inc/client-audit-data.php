<?php

if (!defined("ABSPATH")) {
    exit;
}

/**
 * Ile dni historii pobrać z API (pełna historia vs sync inkrementalny).
 */
function ups_audit_api_fetch_days(string $type, bool $full_history = false): int
{
    $type = sanitize_key($type);
    if ($full_history) {
        if ($type === "gsc") {
            return 480;
        }
        if ($type === "ga4") {
            return 365;
        }
        if ($type === "ads") {
            return 90;
        }
    }

    return 90;
}

/**
 * Spójny klucz kanału GA4 (source|medium) do łączenia z leadami CRM.
 */
function ups_audit_normalize_channel_key(string $source, string $medium = ""): string
{
    $source = strtolower(trim($source));
    $medium = strtolower(trim($medium));
    if ($source === "" || $source === "(not set)") {
        $source = "(direct)";
    }
    if ($medium === "" || $medium === "(not set)") {
        $medium = "(none)";
    }

    return $source . "|" . $medium;
}

/**
 * Czy lead należy do profilu klienta (domena landing / formularza).
 */
function ups_audit_lead_matches_client(int $client_id, int $lead_id): bool
{
    $client_id = (int) $client_id;
    $lead_id = (int) $lead_id;
    if ($client_id <= 0 || $lead_id <= 0) {
        return false;
    }
    $website = trim((string) get_post_meta($client_id, "_ups_client_website", true));
    if ($website === "") {
        $website = trim((string) get_post_meta($client_id, "_ups_client_url", true));
    }
    if ($website !== "" && !preg_match("#^https?://#i", $website)) {
        $website = "https://" . $website;
    }
    $host = $website !== "" ? strtolower((string) wp_parse_url($website, PHP_URL_HOST)) : "";
    if ($host === "") {
        return true;
    }
    $host = preg_replace("/^www\./i", "", $host);
    foreach (["_upsellio_lead_landing_url", "_upsellio_lead_form_origin"] as $meta_key) {
        $url = trim((string) get_post_meta($lead_id, $meta_key, true));
        if ($url === "") {
            continue;
        }
        $lead_host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));
        $lead_host = preg_replace("/^www\./i", "", $lead_host);
        if ($lead_host !== "" && ($lead_host === $host || str_ends_with($lead_host, "." . $host) || str_ends_with($host, "." . $lead_host))) {
            return true;
        }
    }

    return false;
}

function ups_audit_period_windows(int $days = 30): array
{
    $days = max(7, min(90, $days));
    $tz = wp_timezone();
    $end = new DateTimeImmutable("now", $tz);
    $cur_start = $end->modify("-" . $days . " days");
    $prev_end = $cur_start->modify("-1 day");
    $prev_start = $prev_end->modify("-" . $days . " days");

    return [
        "days" => $days,
        "current" => [$cur_start->format("Y-m-d"), $end->format("Y-m-d")],
        "previous" => [$prev_start->format("Y-m-d"), $prev_end->format("Y-m-d")],
    ];
}

function ups_audit_normalize_date_key(string $date_key): string
{
    $date_key = trim($date_key);
    if (preg_match("/^\d{8}$/", $date_key)) {
        return substr($date_key, 0, 4) . "-" . substr($date_key, 4, 2) . "-" . substr($date_key, 6, 2);
    }

    return $date_key;
}

function ups_audit_date_in_range(string $date_key, array $range): bool
{
    $date_key = ups_audit_normalize_date_key($date_key);
    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $date_key)) {
        return false;
    }
    $start = (string) ($range[0] ?? "");
    $end = (string) ($range[1] ?? "");

    return $date_key >= $start && $date_key <= $end;
}

function ups_audit_delta_pct(float $current, float $previous): float
{
    if ($previous == 0.0) {
        return $current > 0 ? 100.0 : 0.0;
    }

    return round((($current - $previous) / $previous) * 100, 1);
}

function ups_audit_format_delta(float $pct): string
{
    return ($pct > 0 ? "+" : "") . rtrim(rtrim(number_format($pct, 1, ",", " "), "0"), ",") . "%";
}

/**
 * Porównuje pozycje fraz GSC: bieżący okres vs poprzedni (z cache sync).
 *
 * @param array<string, array<string, mixed>> $current_by_kw
 * @param array<string, array<string, mixed>> $previous_by_kw
 *
 * @return list<array<string, mixed>>
 */
function ups_audit_gsc_keyword_position_changes(array $current_by_kw, array $previous_by_kw, int $min_impressions = 50): array
{
    $changes = [];
    foreach ($current_by_kw as $keyword => $cur) {
        if (!is_array($cur) || !isset($previous_by_kw[$keyword]) || !is_array($previous_by_kw[$keyword])) {
            continue;
        }
        $prev = $previous_by_kw[$keyword];
        $cur_pos = (float) ($cur["position"] ?? 0);
        $prev_pos = (float) ($prev["position"] ?? 0);
        $impr = (int) ($cur["impressions"] ?? 0);
        if ($cur_pos <= 0 || $prev_pos <= 0 || $impr < $min_impressions) {
            continue;
        }
        $delta = round($cur_pos - $prev_pos, 1);
        if ($delta < 3) {
            continue;
        }
        $changes[] = [
            "keyword" => (string) $keyword,
            "position" => $cur_pos,
            "position_prev" => $prev_pos,
            "position_delta" => $delta,
            "impressions" => $impr,
            "impressions_prev" => (int) ($prev["impressions"] ?? 0),
            "clicks" => (int) ($cur["clicks"] ?? 0),
            "clicks_prev" => (int) ($prev["clicks"] ?? 0),
        ];
    }

    usort($changes, static function ($a, $b) {
        return ((float) ($b["position_delta"] ?? 0)) <=> ((float) ($a["position_delta"] ?? 0));
    });

    return array_slice($changes, 0, 20);
}

/**
 * Liczba eventów GA4 z breakdown (macro/engagement/micro).
 *
 * @param array<string, mixed> $agg
 * @param list<string> $names
 */
function ups_audit_ga4_event_count(array $agg, array $names): int
{
    $events = (array) ($agg["ga4_events_breakdown"] ?? []);
    $want = array_map(static fn($n) => strtolower(trim($n)), $names);
    $total = 0;
    foreach (["macro", "engagement", "micro"] as $bucket) {
        foreach ((array) ($events[$bucket] ?? []) as $ev) {
            if (!is_array($ev)) {
                continue;
            }
            $name = strtolower((string) ($ev["event"] ?? ""));
            if ($name === "" || !in_array($name, $want, true)) {
                continue;
            }
            $total += (int) ($ev["count"] ?? 0);
        }
    }

    return $total;
}

/**
 * Sesje z kanału paid (google/cpc, google/ads itd.).
 *
 * @param array<string, mixed> $agg
 */
function ups_audit_ga4_paid_sessions(array $agg): int
{
    $total = 0;
    foreach ((array) ($agg["channels"] ?? []) as $ch) {
        if (!is_array($ch)) {
            continue;
        }
        $source = strtolower(trim((string) ($ch["source"] ?? "")));
        $medium = strtolower(trim((string) ($ch["medium"] ?? "")));
        if ($source === "google" && in_array($medium, ["cpc", "ppc", "paid", "ads"], true)) {
            $total += (int) ($ch["sessions"] ?? 0);
        }
    }

    return $total;
}

/**
 * Okres poprzedni z pól *_prev (nowy format agregacji).
 *
 * @param array<string, mixed> $agg
 * @return array<string, mixed>
 */
function ups_audit_aggregate_previous_slice(?array $agg): array
{
    if (!is_array($agg)) {
        return [
            "ga4_sessions" => 0,
            "ga4_conversions" => 0,
            "ga4_revenue" => 0.0,
            "gsc_clicks" => 0,
            "gsc_impressions" => 0,
            "ads_cost" => 0.0,
            "ads_clicks" => 0,
            "ads_conversions" => 0.0,
            "roas" => 0.0,
        ];
    }

    return [
        "ga4_sessions" => (int) ($agg["ga4_sessions_prev"] ?? 0),
        "ga4_conversions" => (int) ($agg["ga4_conversions_prev"] ?? 0),
        "ga4_revenue" => (float) ($agg["ga4_revenue_prev"] ?? 0),
        "gsc_clicks" => (int) ($agg["gsc_clicks_prev"] ?? 0),
        "gsc_impressions" => (int) ($agg["gsc_impressions_prev"] ?? 0),
        "ads_cost" => (float) ($agg["ads_cost_prev"] ?? 0),
        "ads_clicks" => (int) ($agg["ads_clicks_prev"] ?? 0),
        "ads_conversions" => (float) ($agg["ads_conversions_prev"] ?? 0),
        "roas" => (float) ($agg["roas_prev"] ?? 0),
    ];
}

/**
 * @param mixed $cache
 * @return array<string, mixed>
 */
function ups_audit_cache_summary($cache): array
{
    if (!is_array($cache)) {
        return [];
    }
    if (isset($cache["summary"]) && is_array($cache["summary"])) {
        return $cache["summary"];
    }

    return $cache;
}

/**
 * @param mixed $cache
 * @return array<string, array<string, float|int>>
 */
function ups_audit_cache_timeseries($cache): array
{
    if (!is_array($cache) || !isset($cache["timeseries"]) || !is_array($cache["timeseries"])) {
        return [];
    }

    return $cache["timeseries"];
}

/**
 * Czy trwa agregacja pod benchmark (blokuje rekurencję intelligence/benchmark).
 */
function ups_audit_benchmark_aggregation_active(?bool $set = null): bool
{
    static $active = false;
    if ($set !== null) {
        $active = $set;
    }

    return $active;
}

/**
 * Sumuje metryki dzienne z cache dla bieżącego i poprzedniego okna.
 *
 * @param array<string, mixed> $cache
 * @param array<string, mixed> $windows
 *
 * @return array{current: array<string, float>, previous: array<string, float>}
 */
function ups_audit_sum_cache_window_metrics(array $cache, array $windows): array
{
    $ts = ups_audit_cache_timeseries($cache);
    $current = [];
    $previous = [];
    if ($ts === []) {
        return ["current" => $current, "previous" => $previous];
    }

    $cur_range = (array) ($windows["current"] ?? []);
    $prev_range = (array) ($windows["previous"] ?? []);
    foreach ($ts as $date_key => $row) {
        if (!is_array($row)) {
            continue;
        }
        $date_key = (string) $date_key;
        if (ups_audit_date_in_range($date_key, $cur_range)) {
            foreach ($row as $metric => $value) {
                if (!is_numeric($value)) {
                    continue;
                }
                $current[$metric] = (float) ($current[$metric] ?? 0) + (float) $value;
            }
        } elseif (ups_audit_date_in_range($date_key, $prev_range)) {
            foreach ($row as $metric => $value) {
                if (!is_numeric($value)) {
                    continue;
                }
                $previous[$metric] = (float) ($previous[$metric] ?? 0) + (float) $value;
            }
        }
    }

    return ["current" => $current, "previous" => $previous];
}

/**
 * Czy cache ma dane dzienne do przeliczenia okna (60/90 dni bez ponownego sync).
 */
function ups_audit_cache_supports_window_slice(array $cache): bool
{
    if (ups_audit_cache_timeseries($cache) !== []) {
        return true;
    }

    return !empty($cache["channel_daily"])
        || !empty($cache["keyword_daily"])
        || !empty($cache["campaign_daily"])
        || !empty($cache["search_term_daily"]);
}

/**
 * Kanały GA4 dla wybranego okna (z channel_daily lub fallback summary).
 *
 * @return list<array<string, mixed>>
 */
function ups_audit_ga4_channels_for_window(array $cache, array $windows): array
{
    $daily = (array) ($cache["channel_daily"] ?? []);
    $range = (array) ($windows["current"] ?? []);
    if ($daily !== [] && $range !== []) {
        $channels = [];
        foreach ($daily as $row) {
            if (!is_array($row)) {
                continue;
            }
            $d = ups_audit_normalize_date_key((string) ($row["d"] ?? ""));
            if ($d === "" || !ups_audit_date_in_range($d, $range)) {
                continue;
            }
            $source = (string) ($row["src"] ?? "(direct)");
            $medium = (string) ($row["med"] ?? "");
            $ch_key = function_exists("ups_audit_normalize_channel_key")
                ? ups_audit_normalize_channel_key($source, $medium)
                : strtolower($source . "|" . $medium);
            if (!isset($channels[$ch_key])) {
                $channels[$ch_key] = [
                    "source" => $source,
                    "medium" => $medium,
                    "sessions" => 0,
                    "conversions" => 0,
                    "revenue" => 0.0,
                ];
            }
            $channels[$ch_key]["sessions"] += (int) ($row["sess"] ?? 0);
            $channels[$ch_key]["conversions"] += (int) ($row["conv"] ?? 0);
            $channels[$ch_key]["revenue"] += (float) ($row["rev"] ?? 0);
        }
        uasort($channels, static function ($a, $b) {
            return ((int) ($b["sessions"] ?? 0)) <=> ((int) ($a["sessions"] ?? 0));
        });

        return array_slice(array_values($channels), 0, 20);
    }

    $cache_days = (int) ($cache["period_days"] ?? 0);
    $want_days = (int) ($windows["days"] ?? 0);
    if ($cache_days > 0 && $want_days > 0 && $cache_days === $want_days) {
        return (array) ($cache["channels"] ?? []);
    }

    return [];
}

/**
 * @return array{not_set_sessions:int, not_set_pct:float}
 */
function ups_audit_ga4_attribution_for_window(array $cache, array $windows): array
{
    $daily = (array) ($cache["channel_daily"] ?? []);
    $range = (array) ($windows["current"] ?? []);
    if ($daily === [] || $range === []) {
        $attr = is_array($cache["attribution"] ?? null) ? $cache["attribution"] : [];

        return [
            "not_set_sessions" => (int) ($attr["not_set_sessions"] ?? 0),
            "not_set_pct" => (float) ($attr["not_set_pct"] ?? 0),
        ];
    }

    $sessions = 0;
    $not_set = 0;
    foreach ($daily as $row) {
        if (!is_array($row)) {
            continue;
        }
        $d = ups_audit_normalize_date_key((string) ($row["d"] ?? ""));
        if ($d === "" || !ups_audit_date_in_range($d, $range)) {
            continue;
        }
        $sess = (int) ($row["sess"] ?? 0);
        $sessions += $sess;
        $src = (string) ($row["src"] ?? "");
        $med = (string) ($row["med"] ?? "");
        if (function_exists("ups_audit_ga4_is_not_set_source") && ups_audit_ga4_is_not_set_source($src, $med)) {
            $not_set += $sess;
        }
    }

    return [
        "not_set_sessions" => $not_set,
        "not_set_pct" => $sessions > 0 ? round(($not_set / $sessions) * 100, 1) : 0.0,
    ];
}

/**
 * Top frazy GSC dla okna current lub previous.
 *
 * @return array<string, array<string, mixed>>
 */
function ups_audit_gsc_keywords_for_window(array $cache, array $windows, string $which = "current"): array
{
    $daily = (array) ($cache["keyword_daily"] ?? []);
    $range = (array) ($windows[$which] ?? []);
    if ($daily !== [] && $range !== []) {
        $agg = [];
        foreach ($daily as $row) {
            if (!is_array($row)) {
                continue;
            }
            $d = ups_audit_normalize_date_key((string) ($row["d"] ?? ""));
            if ($d === "" || !ups_audit_date_in_range($d, $range)) {
                continue;
            }
            $keyword = strtolower(trim((string) ($row["kw"] ?? "")));
            if ($keyword === "") {
                continue;
            }
            if (!isset($agg[$keyword])) {
                $agg[$keyword] = [
                    "keyword" => $keyword,
                    "clicks" => 0,
                    "impressions" => 0,
                    "position_weighted" => 0.0,
                    "imp" => 0,
                ];
            }
            $imp = max(1, (int) ($row["imp"] ?? 0));
            $pos = (float) ($row["pos"] ?? 0);
            $agg[$keyword]["clicks"] += (int) ($row["clk"] ?? 0);
            $agg[$keyword]["impressions"] += (int) ($row["imp"] ?? 0);
            $agg[$keyword]["position_weighted"] += $pos * $imp;
            $agg[$keyword]["imp"] += $imp;
        }
        $out = [];
        foreach ($agg as $k) {
            $out[(string) $k["keyword"]] = [
                "keyword" => (string) $k["keyword"],
                "clicks" => (int) $k["clicks"],
                "impressions" => (int) $k["impressions"],
                "position" => (int) $k["imp"] > 0
                    ? round((float) $k["position_weighted"] / (int) $k["imp"], 2)
                    : 0.0,
            ];
        }

        return $out;
    }

    $cache_days = (int) ($cache["period_days"] ?? 0);
    $want_days = (int) ($windows["days"] ?? 0);
    if ($cache_days > 0 && $want_days > 0 && $cache_days === $want_days) {
        if ($which === "previous") {
            $rows = (array) ($cache["previous_top_keywords"] ?? []);
        } else {
            $rows = (array) ($cache["top_keywords"] ?? []);
        }
        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $kw = strtolower(trim((string) ($row["keyword"] ?? "")));
            if ($kw !== "") {
                $out[$kw] = $row;
            }
        }

        return $out;
    }

    return [];
}

/**
 * Kampanie Ads dla bieżącego okna.
 *
 * @return list<array<string, mixed>>
 */
function ups_audit_ads_campaigns_for_window(array $cache, array $windows): array
{
    $daily = (array) ($cache["campaign_daily"] ?? []);
    $range = (array) ($windows["current"] ?? []);
    if ($daily !== [] && $range !== []) {
        $agg = [];
        foreach ($daily as $row) {
            if (!is_array($row)) {
                continue;
            }
            $d = ups_audit_normalize_date_key((string) ($row["d"] ?? ""));
            if ($d === "" || !ups_audit_date_in_range($d, $range)) {
                continue;
            }
            $id = (string) ($row["id"] ?? "");
            if ($id === "") {
                continue;
            }
            if (!isset($agg[$id])) {
                $agg[$id] = [
                    "id" => $id,
                    "name" => (string) ($row["name"] ?? $id),
                    "type" => (string) ($row["type"] ?? ""),
                    "cost" => 0.0,
                    "clicks" => 0,
                    "impressions" => 0,
                    "conversions" => 0.0,
                ];
            }
            $agg[$id]["cost"] += (float) ($row["cost"] ?? 0);
            $agg[$id]["clicks"] += (int) ($row["clk"] ?? 0);
            $agg[$id]["impressions"] += (int) ($row["imp"] ?? 0);
            $agg[$id]["conversions"] += (float) ($row["conv"] ?? 0);
        }
        foreach ($agg as $id => $camp) {
            $conv = (float) ($camp["conversions"] ?? 0);
            $cost = (float) ($camp["cost"] ?? 0);
            $agg[$id]["cpa"] = $conv > 0 ? round($cost / $conv, 2) : 0.0;
        }
        uasort($agg, static function ($a, $b) {
            return ((float) ($b["cost"] ?? 0)) <=> ((float) ($a["cost"] ?? 0));
        });

        return array_slice(array_values($agg), 0, 30);
    }

    $cache_days = (int) ($cache["period_days"] ?? 0);
    $want_days = (int) ($windows["days"] ?? 0);
    if ($cache_days > 0 && $want_days > 0 && $cache_days === $want_days) {
        return (array) ($cache["campaigns"] ?? []);
    }

    return [];
}

/**
 * Search terms Ads dla bieżącego okna.
 *
 * @return list<array<string, mixed>>
 */
function ups_audit_ads_search_terms_for_window(array $cache, array $windows): array
{
    $daily = (array) ($cache["search_term_daily"] ?? []);
    $range = (array) ($windows["current"] ?? []);
    if ($daily !== [] && $range !== []) {
        $agg = [];
        foreach ($daily as $row) {
            if (!is_array($row)) {
                continue;
            }
            $d = ups_audit_normalize_date_key((string) ($row["d"] ?? ""));
            if ($d === "" || !ups_audit_date_in_range($d, $range)) {
                continue;
            }
            $term = strtolower(trim((string) ($row["term"] ?? "")));
            if ($term === "") {
                continue;
            }
            if (!isset($agg[$term])) {
                $agg[$term] = [
                    "search_term" => $term,
                    "campaign_name" => (string) ($row["camp"] ?? ""),
                    "ad_group_name" => (string) ($row["ag"] ?? ""),
                    "cost_pln" => 0.0,
                    "clicks" => 0,
                    "impressions" => 0,
                    "conversions" => 0.0,
                ];
            }
            $agg[$term]["cost_pln"] += (float) ($row["cost"] ?? 0);
            $agg[$term]["clicks"] += (int) ($row["clk"] ?? 0);
            $agg[$term]["impressions"] += (int) ($row["imp"] ?? 0);
            $agg[$term]["conversions"] += (float) ($row["conv"] ?? 0);
        }
        uasort($agg, static function ($a, $b) {
            return ((float) ($b["cost_pln"] ?? 0)) <=> ((float) ($a["cost_pln"] ?? 0));
        });

        return array_slice(array_values($agg), 0, 250);
    }

    $cache_days = (int) ($cache["period_days"] ?? 0);
    $want_days = (int) ($windows["days"] ?? 0);
    if ($cache_days > 0 && $want_days > 0 && $cache_days === $want_days) {
        return (array) ($cache["search_terms"] ?? []);
    }

    return [];
}

/**
 * Klucz klastra SEO (warianty pisowni / kolejność słów).
 */
function ups_audit_seo_cluster_key(string $keyword): string
{
    $kw = mb_strtolower(trim($keyword));
    $kw = preg_replace("/\b(strech|strecz|strech)\b/u", "stretch", $kw);
    $kw = preg_replace("/\s+(poznań|warszawa|kraków|wrocław|gdańsk|łódź|katowice)$/u", "", $kw);
    $kw = preg_replace("/\s+/u", " ", $kw);
    $tokens = array_values(array_filter(explode(" ", $kw)));
    sort($tokens);

    return implode(" ", $tokens);
}

/**
 * Etykieta klastra do wyświetlenia (title case pierwszych tokenów).
 */
function ups_audit_seo_cluster_label(string $cluster_key): string
{
    $parts = explode(" ", trim($cluster_key));
    $parts = array_map(static function ($p) {
        return mb_strtoupper(mb_substr($p, 0, 1)) . mb_substr($p, 1);
    }, $parts);

    return implode(" ", $parts);
}

/**
 * Grupuje frazy SEO w klastry tematyczne.
 *
 * @param list<array<string, mixed>> $rows
 *
 * @return list<array<string, mixed>>
 */
function ups_audit_cluster_seo_keywords(array $rows): array
{
    $clusters = [];
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $keyword = trim((string) ($row["keyword"] ?? ""));
        if ($keyword === "") {
            continue;
        }
        $key = ups_audit_seo_cluster_key($keyword);
        if (!isset($clusters[$key])) {
            $clusters[$key] = [
                "label" => ups_audit_seo_cluster_label($key),
                "cluster_key" => $key,
                "keywords" => [],
                "impressions" => 0,
                "clicks" => 0,
                "potential_clicks" => 0,
            ];
        }
        $potential = (int) ($row["potential_clicks"] ?? 0);
        if ($potential <= 0) {
            $impr = (int) ($row["impressions"] ?? 0);
            $potential = (int) max(0, round($impr * 0.02));
        }
        $kw_norm = mb_strtolower($keyword);
        if (!isset($clusters[$key]["_kw_seen"][$kw_norm])) {
            $clusters[$key]["_kw_seen"][$kw_norm] = $keyword;
        }
        $clusters[$key]["impressions"] += (int) ($row["impressions"] ?? 0);
        $clusters[$key]["clicks"] += (int) ($row["clicks"] ?? 0);
        $clusters[$key]["potential_clicks"] += $potential;
    }

    foreach ($clusters as $ck => $cluster) {
        $seen = (array) ($cluster["_kw_seen"] ?? []);
        $clusters[$ck]["keywords"] = array_values($seen);
        unset($clusters[$ck]["_kw_seen"]);
    }

    $out = array_values($clusters);
    usort($out, static function ($a, $b) {
        return ((int) ($b["potential_clicks"] ?? 0)) <=> ((int) ($a["potential_clicks"] ?? 0))
            ?: ((int) ($b["impressions"] ?? 0)) <=> ((int) ($a["impressions"] ?? 0));
    });

    return $out;
}

/**
 * Klasyfikacja frazy Ads: scale | watch | exclude
 *
 * Wykluczenie dopiero po ≥30 dni obserwacji, ≥100 PLN kosztu i 0 konwersji.
 */
function ups_audit_classify_search_term(string $term, float $cost, float $conversions, int $clicks, int $observation_days = 0): string
{
    $term_l = mb_strtolower(trim($term));
    if ($conversions > 0) {
        return "scale";
    }

    $product_signals = [
        "taśm", "tasem", "nadruk", "logo", "pakow", "stretch", "folia", "folie",
        "klej", "samoprzylep", "custom", "z nadrukiem", "b2b", "hurtow", "zamów",
        "personaliz", "etykiet", "opakow",
    ];
    $info_signals = [
        "jak ", "co to", "darmow", "praca", "oferta pracy", "opinie", "recenzj",
        "wikipedia", "forum", "youtube", "poradnik", "definicja", "czy ",
    ];

    $has_product = false;
    foreach ($product_signals as $sig) {
        if ($sig !== "" && strpos($term_l, $sig) !== false) {
            $has_product = true;
            break;
        }
    }
    $has_info = false;
    foreach ($info_signals as $sig) {
        if ($sig !== "" && strpos($term_l, $sig) !== false) {
            $has_info = true;
            break;
        }
    }

    $exclude_eligible = $observation_days >= 30 && $cost >= 100;
    if ($exclude_eligible) {
        if ($has_info) {
            return "exclude";
        }
        if ($cost >= 50 && $clicks >= 10 && !$has_product) {
            return "exclude";
        }
    }

    if ($has_product && !$has_info) {
        return "watch";
    }
    if ($cost >= 15 && $clicks >= 2) {
        return "watch";
    }

    return "watch";
}

function ups_audit_search_term_action_label(string $action): string
{
    $map = [
        "scale" => "Skaluj",
        "watch" => "Obserwuj",
        "exclude" => "Wyklucz",
    ];

    return $map[$action] ?? "Obserwuj";
}

function ups_audit_opportunity_score_label(int $score): string
{
    if ($score >= 96) {
        return "wyjątkowy";
    }
    if ($score >= 81) {
        return "bardzo wysoki";
    }
    if ($score >= 61) {
        return "wysoki";
    }
    if ($score >= 31) {
        return "średni";
    }

    return "niski";
}

function ups_audit_with_account_oauth(int $google_account_id, callable $callback)
{
    $oauth = ups_audit_get_oauth_for_account($google_account_id);
    $backup = upsellio_get_gsc_credentials();
    $property = (string) ($backup["property"] ?? "");
    upsellio_save_gsc_credentials(
        (string) ($oauth["client_id"] ?? ""),
        (string) ($oauth["client_secret"] ?? ""),
        (string) ($oauth["refresh_token"] ?? ""),
        $property
    );
    try {
        return $callback($oauth);
    } finally {
        upsellio_save_gsc_credentials(
            (string) ($backup["client_id"] ?? ""),
            (string) ($backup["client_secret"] ?? ""),
            (string) ($backup["refresh_token"] ?? ""),
            $property
        );
    }
}

function ups_audit_ads_api_configured(): bool
{
    if (!function_exists("upsellio_google_ads_get_settings")) {
        return false;
    }
    $cfg = upsellio_google_ads_get_settings();

    return trim((string) ($cfg["developer_token"] ?? "")) !== "";
}

function ups_audit_resource_health(int $resource_id): array
{
    $resource_id = (int) $resource_id;
    $type = (string) get_post_meta($resource_id, "_ups_resource_type", true);
    $cache = get_post_meta($resource_id, "_ups_resource_data_cache", true);
    $last = strtotime((string) get_post_meta($resource_id, "_ups_resource_last_data_sync", true));
    $err = is_array($cache) ? trim((string) ($cache["error"] ?? "")) : "";

    if ($type === "meta") {
        $account_id = (int) get_post_meta($resource_id, "_ups_resource_meta_account_id", true);
        $oauth = function_exists("ups_audit_get_oauth_for_meta_account")
            ? ups_audit_get_oauth_for_meta_account($account_id)
            : [];
        $has_token = trim((string) ($oauth["access_token"] ?? "")) !== "";
        if (!$has_token) {
            return ["status" => "error", "label" => __("Brak tokena Meta OAuth", "upsellio")];
        }
        if ($err !== "") {
            return ["status" => "error", "label" => $err];
        }
        if ($last <= 0) {
            return ["status" => "warn", "label" => __("Nigdy nie sync", "upsellio")];
        }
        $age_h = (time() - $last) / 3600;
        if ($age_h > 48) {
            return ["status" => "warn", "label" => __("Sync >48h", "upsellio")];
        }

        return ["status" => "ok", "label" => __("OK", "upsellio")];
    }

    if ($type === "clarity") {
        $has_clarity = function_exists("ups_audit_clarity_get_token")
            && ups_audit_clarity_get_token($resource_id) !== "";
        if (!$has_clarity) {
            return ["status" => "error", "label" => __("Brak tokena API Clarity", "upsellio")];
        }
        if ($err !== "" && stripos($err, "cache") === false) {
            return ["status" => "error", "label" => $err];
        }
        if ($last <= 0) {
            return ["status" => "warn", "label" => __("Nigdy nie sync", "upsellio")];
        }
        $usage = function_exists("ups_audit_clarity_daily_usage")
            ? ups_audit_clarity_daily_usage($resource_id)
            : 0;
        if ($usage >= 8) {
            return ["status" => "warn", "label" => sprintf(
                /* translators: %d: API calls used today */
                __("Limit API: %d/10 dziś", "upsellio"),
                $usage
            )];
        }

        return ["status" => "ok", "label" => __("OK (3 dni)", "upsellio")];
    }

    $account_id = (int) get_post_meta($resource_id, "_ups_resource_google_account_id", true);
    $oauth = ups_audit_get_oauth_for_account($account_id);
    $has_token = trim((string) ($oauth["refresh_token"] ?? "")) !== "";

    if (!$has_token) {
        return ["status" => "error", "label" => __("Brak tokena OAuth", "upsellio")];
    }
    if ($err !== "") {
        return ["status" => "error", "label" => $err];
    }
    if ($last <= 0) {
        return ["status" => "warn", "label" => __("Nigdy nie sync", "upsellio")];
    }
    $age_h = (time() - $last) / 3600;
    if ($age_h > 48) {
        return ["status" => "warn", "label" => __("Sync >48h", "upsellio")];
    }

    return ["status" => "ok", "label" => __("OK", "upsellio")];
}

/**
 * ID klientów CRM będących profilami audytu (flaga lub zmapowane zasoby).
 *
 * @return int[]
 */
function ups_audit_collect_profile_client_ids(): array
{
    $ids = [];
    $flagged = get_posts([
        "post_type" => "crm_client",
        "posts_per_page" => -1,
        "post_status" => ["publish", "draft"],
        "fields" => "ids",
        "meta_query" => [[
            "key" => "_ups_audit_profile",
            "value" => "1",
            "compare" => "=",
        ]],
    ]);
    foreach ($flagged as $cid) {
        $ids[(int) $cid] = true;
    }
    $resources = get_posts([
        "post_type" => "crm_audit_resource",
        "posts_per_page" => -1,
        "post_status" => ["publish", "draft"],
        "fields" => "ids",
        "meta_query" => [[
            "key" => "_ups_resource_client_id",
            "value" => 0,
            "compare" => ">",
            "type" => "NUMERIC",
        ]],
    ]);
    foreach ($resources as $rid) {
        $cid = (int) get_post_meta((int) $rid, "_ups_resource_client_id", true);
        if ($cid > 0) {
            $ids[$cid] = true;
        }
    }

    return array_map("intval", array_keys($ids));
}

/**
 * Utwórz profil klienta pod audyt (crm_client + meta).
 */
function ups_audit_create_audit_profile(string $title, string $website = "")
{
    $title = sanitize_text_field($title);
    if ($title === "") {
        return new WP_Error("ups_audit_empty_title", __("Podaj nazwę profilu.", "upsellio"));
    }
    if (!post_type_exists("crm_client")) {
        return new WP_Error("ups_audit_no_cpt", __("Brak typu crm_client.", "upsellio"));
    }
    $client_id = (int) wp_insert_post([
        "post_type" => "crm_client",
        "post_status" => "publish",
        "post_title" => $title,
    ]);
    if ($client_id <= 0) {
        return new WP_Error("ups_audit_create_failed", __("Nie udało się utworzyć profilu.", "upsellio"));
    }
    update_post_meta($client_id, "_ups_audit_profile", "1");
    update_post_meta($client_id, "_ups_client_lifecycle_status", "active");
    $website = esc_url_raw(trim($website));
    if ($website !== "") {
        update_post_meta($client_id, "_ups_client_website", $website);
    }

    return $client_id;
}

/**
 * Domyślny profil do panelu (pierwszy z danymi, inaczej pierwszy z listy).
 */
function ups_audit_default_profile_client_id(): int
{
    $rows = function_exists("ups_audit_get_client_profile_rows")
        ? ups_audit_get_client_profile_rows()
        : [];
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        if (!empty($row["setup"]["is_ready"])) {
            return (int) ($row["id"] ?? 0);
        }
    }
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $id = (int) ($row["id"] ?? 0);
        if ($id > 0) {
            return $id;
        }
    }

    return 0;
}

/**
 * Modal mapowania zasobów (jedna instancja na stronę CRM audytu).
 */
function upsellio_crm_render_audit_map_modal(): void
{
    static $rendered = false;
    if ($rendered) {
        return;
    }
    $rendered = true;
    $ca_crm_clients = get_posts([
        "post_type" => "crm_client",
        "posts_per_page" => -1,
        "post_status" => ["publish", "draft"],
        "orderby" => "title",
        "order" => "ASC",
    ]);
    $ca_gacc_labels = [];
    foreach (get_posts([
        "post_type" => "crm_google_account",
        "posts_per_page" => -1,
        "post_status" => ["publish", "draft"],
    ]) as $ca_ga) {
        if (!($ca_ga instanceof WP_Post)) {
            continue;
        }
        $gid = (int) $ca_ga->ID;
        $em = (string) get_post_meta($gid, "_ups_gacc_email", true);
        $ca_gacc_labels[$gid] = $em !== "" ? $em : (string) $ca_ga->post_title;
    }
    $ca_macc_labels = [];
    foreach (get_posts([
        "post_type" => "crm_meta_account",
        "posts_per_page" => -1,
        "post_status" => ["publish", "draft"],
    ]) as $ca_ma) {
        if (!($ca_ma instanceof WP_Post)) {
            continue;
        }
        $mid = (int) $ca_ma->ID;
        $em = (string) get_post_meta($mid, "_ups_macc_email", true);
        $ca_macc_labels[$mid] = $em !== "" ? $em : (string) $ca_ma->post_title;
    }
    $ca_all_resources = get_posts([
        "post_type" => "crm_audit_resource",
        "posts_per_page" => 500,
        "post_status" => ["publish", "draft"],
        "orderby" => "date",
        "order" => "DESC",
    ]);
    require get_template_directory() . "/inc/crm-app/partials/crm-audit-map-modal.php";
}

function ups_audit_client_setup_status(int $client_id): array
{
    $client_id = (int) $client_id;
    $ga4 = count(ups_audit_get_client_resources($client_id, "ga4"));
    $gsc = count(ups_audit_get_client_resources($client_id, "gsc"));
    $ads = count(ups_audit_get_client_resources($client_id, "ads"));
    $meta = count(ups_audit_get_client_resources($client_id, "meta"));
    $clarity = count(ups_audit_get_client_resources($client_id, "clarity"));
    $complete = $ga4 > 0 && $gsc > 0;
    $steps = [
        ["key" => "ga4", "done" => $ga4 > 0, "label" => "GA4 (" . $ga4 . ")"],
        ["key" => "gsc", "done" => $gsc > 0, "label" => "GSC (" . $gsc . ")"],
        ["key" => "ads", "done" => $ads > 0, "label" => "Google Ads (" . $ads . ")"],
        ["key" => "meta", "done" => $meta > 0, "label" => "Meta Ads (" . $meta . ")"],
        ["key" => "clarity", "done" => $clarity > 0, "label" => "Clarity (" . $clarity . ")"],
    ];

    return [
        "ga4" => $ga4,
        "gsc" => $gsc,
        "ads" => $ads,
        "meta" => $meta,
        "clarity" => $clarity,
        "is_ready" => $complete,
        "steps" => $steps,
    ];
}
