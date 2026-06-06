<?php

if (!defined("ABSPATH")) {
    exit;
}

/**
 * Średnie KPI portfolio (benchmark) — bez bieżącego klienta w próbie.
 *
 * @return array<string, mixed>
 */
function ups_audit_compute_portfolio_benchmark_cached(int $days = 30, int $exclude_client_id = 0): array
{
    static $in_progress = false;

    $days = in_array($days, [7, 14, 30, 60, 90], true) ? $days : 30;
    $exclude_client_id = (int) $exclude_client_id;
    $cache_key = "ups_audit_bench_{$days}_{$exclude_client_id}";
    $cached = get_transient($cache_key);
    if (is_array($cached)) {
        return $cached;
    }
    if ($in_progress) {
        return [
            "clients" => 0,
            "has_benchmark" => false,
            "ga4_sessions" => 0,
            "gsc_clicks" => 0,
            "gsc_ctr_avg" => 0.0,
            "ads_cost" => 0,
            "ads_cpa_avg" => 0.0,
            "roas" => 0.0,
            "health_score" => 0,
            "ga4_cr_avg" => 0.0,
            "window_days" => $days,
        ];
    }

    $in_progress = true;
    try {
        $result = ups_audit_compute_portfolio_benchmark($days, $exclude_client_id);
        set_transient($cache_key, $result, 15 * MINUTE_IN_SECONDS);

        return $result;
    } finally {
        $in_progress = false;
    }
}

/**
 * Lekkie dodatki do dashboardu (bez zewnętrznych API przy pierwszym renderze).
 *
 * @param array<string, mixed> $agg
 * @return array<string, mixed>
 */
function ups_audit_attach_dashboard_extras(array $agg, int $client_id, int $days = 30): array
{
    $client_id = (int) $client_id;
    $days = in_array($days, [7, 14, 30, 60, 90], true) ? $days : 30;
    if ($client_id <= 0) {
        return $agg;
    }

    if (function_exists("ups_audit_compute_portfolio_benchmark_cached")) {
        $agg["benchmark"] = ups_audit_compute_portfolio_benchmark_cached($days, $client_id);
    }
    if (function_exists("ups_audit_client_technical_signals")) {
        $agg["technical"] = ups_audit_client_technical_signals($client_id, false);
    }
    if (function_exists("upsellio_analytics_channel_ltv_for_client")) {
        $agg["channel_ltv"] = upsellio_analytics_channel_ltv_for_client($client_id, $days, $agg);
    }

    return $agg;
}

function ups_audit_compute_portfolio_benchmark(int $days = 30, int $exclude_client_id = 0): array
{
    $days = in_array($days, [7, 14, 30, 60, 90], true) ? $days : 30;
    $exclude_client_id = (int) $exclude_client_id;
    $sums = [
        "count" => 0,
        "ga4_sessions" => 0.0,
        "gsc_clicks" => 0.0,
        "gsc_ctr" => 0.0,
        "ads_cost" => 0.0,
        "ads_cpa" => 0.0,
        "roas" => 0.0,
        "health_score" => 0.0,
        "ga4_cr" => 0.0,
    ];

    $clients = get_posts([
        "post_type" => "crm_client",
        "posts_per_page" => -1,
        "post_status" => ["publish", "draft"],
        "fields" => "ids",
    ]);

    if (function_exists("ups_audit_benchmark_aggregation_active")) {
        ups_audit_benchmark_aggregation_active(true);
    }
    try {
        foreach ($clients as $cid) {
            $cid = (int) $cid;
            if ($cid <= 0 || $cid === $exclude_client_id) {
                continue;
            }
            $setup = ups_audit_client_setup_status($cid);
            if (empty($setup["is_ready"])) {
                continue;
            }
            $data = ups_audit_aggregate_client_data($cid, $days, 0, false);
            if ((int) ($data["ga4_sessions"] ?? 0) <= 0 && (int) ($data["gsc_clicks"] ?? 0) <= 0) {
                continue;
            }
            $derived = (array) ($data["derived"] ?? []);
            $sums["count"]++;
            $sums["ga4_sessions"] += (float) ($data["ga4_sessions"] ?? 0);
            $sums["gsc_clicks"] += (float) ($data["gsc_clicks"] ?? 0);
            $sums["gsc_ctr"] += (float) ($derived["gsc_ctr"] ?? 0);
            $sums["ads_cost"] += (float) ($data["ads_cost"] ?? 0);
            if ((float) ($derived["ads_cpa"] ?? 0) > 0) {
                $sums["ads_cpa"] += (float) $derived["ads_cpa"];
            }
            $sums["roas"] += (float) ($data["roas"] ?? 0);
            $sums["health_score"] += (float) ($data["health_score"] ?? 0);
            $sums["ga4_cr"] += (float) ($derived["ga4_conversion_rate"] ?? 0);
        }
    } finally {
        if (function_exists("ups_audit_benchmark_aggregation_active")) {
            ups_audit_benchmark_aggregation_active(false);
        }
    }

    $n = max(1, (int) $sums["count"]);

    return [
        "clients" => (int) $sums["count"],
        "has_benchmark" => (int) $sums["count"] >= 2,
        "ga4_sessions" => round($sums["ga4_sessions"] / $n, 0),
        "gsc_clicks" => round($sums["gsc_clicks"] / $n, 0),
        "gsc_ctr_avg" => round($sums["gsc_ctr"] / $n, 2),
        "ads_cost" => round($sums["ads_cost"] / $n, 0),
        "ads_cpa_avg" => round($sums["ads_cpa"] / $n, 2),
        "roas" => round($sums["roas"] / $n, 2),
        "health_score" => round($sums["health_score"] / $n, 0),
        "ga4_cr_avg" => round($sums["ga4_cr"] / $n, 2),
        "window_days" => $days,
    ];
}

/**
 * Porównanie wartości klienta vs benchmark (%).
 */
function ups_audit_vs_benchmark_pct(float $client_val, float $bench_val): ?float
{
    if ($bench_val <= 0) {
        return null;
    }

    return round((($client_val - $bench_val) / $bench_val) * 100, 1);
}

/**
 * Normalizuje host do porównań (wtapes.pl = www.wtapes.pl).
 */
function ups_audit_normalize_host(string $host): string
{
    $host = strtolower(trim($host));
    if (str_starts_with($host, "www.")) {
        $host = substr($host, 4);
    }

    return $host;
}

/**
 * Czy URL należy do domeny klienta.
 */
function ups_audit_url_matches_client_host(string $url, string $client_host): bool
{
    $client_host = ups_audit_normalize_host($client_host);
    if ($client_host === "") {
        return false;
    }
    $page_host = ups_audit_normalize_host((string) wp_parse_url($url, PHP_URL_HOST));

    return $page_host !== "" && ($page_host === $client_host || str_ends_with($page_host, "." . $client_host));
}

/**
 * Parsuje odpowiedź GSC Sitemaps API do struktury indeksacji.
 *
 * @param array<string, mixed> $payload
 * @return array<string, mixed>|null
 */
function ups_audit_parse_gsc_sitemap_indexation(array $payload, string $property_label = ""): ?array
{
    $entries = isset($payload["sitemap"]) && is_array($payload["sitemap"]) ? $payload["sitemap"] : [];
    $submitted = 0;
    $indexed = 0;
    $sitemap_count = 0;
    foreach ($entries as $sm) {
        if (!is_array($sm)) {
            continue;
        }
        $sitemap_count++;
        foreach ((array) ($sm["contents"] ?? []) as $content) {
            if (!is_array($content)) {
                continue;
            }
            $submitted += (int) ($content["submitted"] ?? 0);
            $indexed += (int) ($content["indexed"] ?? 0);
        }
    }
    if ($submitted <= 0 && $sitemap_count <= 0) {
        return null;
    }

    return [
        "submitted" => $submitted,
        "indexed" => $indexed,
        "ratio" => $submitted > 0 ? (int) round($indexed / $submitted * 100) : null,
        "pages_sampled" => 0,
        "sitemap_count" => $sitemap_count,
        "source" => "gsc_sitemap",
        "synced_at" => current_time("mysql"),
        "note" => $property_label !== ""
            ? sprintf("Sitemap GSC (%s) — %d map witryn.", $property_label, $sitemap_count)
            : sprintf("Sitemap GSC — %d map witryn.", $sitemap_count),
    ];
}

/**
 * URL-e klienta do inspekcji (homepage + sitemap.xml).
 *
 * @return list<string>
 */
function ups_audit_discover_client_inspection_urls(string $website, int $limit = 5): array
{
    $website = esc_url_raw(trim($website));
    if ($website === "") {
        return [];
    }
    $urls = [rtrim($website, "/") . "/"];
    $fetch_locs = static function (string $sitemap_url) use (&$fetch_locs): array {
        $out = [];
        $resp = wp_remote_get($sitemap_url, ["timeout" => 12, "sslverify" => true]);
        if (is_wp_error($resp) || (int) wp_remote_retrieve_response_code($resp) >= 400) {
            return $out;
        }
        $body = (string) wp_remote_retrieve_body($resp);
        if (!preg_match_all("#<loc>([^<]+)</loc>#i", $body, $m)) {
            return $out;
        }
        foreach ($m[1] as $loc) {
            $loc = esc_url_raw(trim(html_entity_decode((string) $loc, ENT_QUOTES, "UTF-8")));
            if ($loc === "") {
                continue;
            }
            if (preg_match("#-sitemap\.xml$#i", $loc)) {
                foreach ($fetch_locs($loc) as $child) {
                    $out[] = $child;
                }
                continue;
            }
            $out[] = $loc;
        }

        return $out;
    };
    foreach ($fetch_locs(rtrim($website, "/") . "/sitemap.xml") as $loc) {
        $urls[] = $loc;
        if (count($urls) >= $limit * 4) {
            break;
        }
    }
    $urls = array_values(array_unique($urls));

    return array_slice($urls, 0, max(1, $limit));
}

/**
 * URL Inspection API — próbka stron klienta (max 5).
 *
 * @param list<string> $urls
 * @return array<string, mixed>|null
 */
function ups_audit_gsc_inspect_client_urls(int $account_id, string $property, array $urls, string $host_label = ""): ?array
{
    $account_id = (int) $account_id;
    $property = trim($property);
    $urls = array_values(array_filter(array_map("esc_url_raw", $urls)));
    if ($account_id <= 0 || $property === "" || $urls === []) {
        return null;
    }
    $urls = array_slice($urls, 0, 5);
    $result = ups_audit_with_account_oauth($account_id, static function ($oauth) use ($property, $urls) {
        if (!function_exists("upsellio_gsc_get_access_token")) {
            return new WP_Error("gsc_missing", "Brak modułu GSC.");
        }
        $access = upsellio_gsc_get_access_token((array) $oauth);
        if (is_wp_error($access) || !is_string($access) || $access === "") {
            return $access instanceof WP_Error ? $access : new WP_Error("gsc_token", "Brak tokena GSC.");
        }
        $rows = [];
        foreach ($urls as $i => $url) {
            if ($i > 0) {
                sleep(1);
            }
            $resp = wp_remote_post(
                "https://searchconsole.googleapis.com/v1/urlInspection/index:inspect",
                [
                    "timeout" => 20,
                    "headers" => [
                        "Authorization" => "Bearer " . $access,
                        "Content-Type" => "application/json",
                    ],
                    "body" => wp_json_encode([
                        "inspectionUrl" => $url,
                        "siteUrl" => $property,
                        "languageCode" => "pl-PL",
                    ]),
                ]
            );
            if (is_wp_error($resp) || (int) wp_remote_retrieve_response_code($resp) >= 400) {
                continue;
            }
            $payload = json_decode((string) wp_remote_retrieve_body($resp), true);
            $index_r = is_array($payload) ? (array) (($payload["inspectionResult"]["indexStatusResult"] ?? [])) : [];
            $verdict = (string) ($index_r["verdict"] ?? "");
            $rows[] = [
                "url" => $url,
                "verdict" => $verdict,
                "is_indexed" => $verdict === "PASS" || stripos($verdict, "INDEX") !== false,
            ];
        }

        return $rows;
    });
    if (is_wp_error($result) || !is_array($result) || $result === []) {
        return null;
    }
    $submitted = count($result);
    $indexed = 0;
    foreach ($result as $row) {
        if (!empty($row["is_indexed"])) {
            $indexed++;
        }
    }

    return [
        "submitted" => $submitted,
        "indexed" => $indexed,
        "ratio" => $submitted > 0 ? (int) round($indexed / $submitted * 100) : null,
        "pages_sampled" => $submitted,
        "source" => "url_inspection_live",
        "synced_at" => current_time("mysql"),
        "pages" => $result,
        "note" => sprintf(
            "Inspekcja URL (live) — próbka %d stron z %s.",
            $submitted,
            $host_label !== "" ? $host_label : $property
        ),
    ];
}

/**
 * Pobiera indeksację z GSC Sitemaps API (OAuth konta + property).
 *
 * @return array<string, mixed>|\WP_Error|null
 */
function ups_audit_gsc_fetch_sitemap_indexation(int $account_id, string $property)
{
    $account_id = (int) $account_id;
    $property = trim($property);
    if ($account_id <= 0 || $property === "" || !function_exists("ups_audit_with_account_oauth")) {
        return null;
    }

    return ups_audit_with_account_oauth($account_id, static function ($oauth) use ($property) {
        if (!function_exists("upsellio_gsc_get_access_token")) {
            return new WP_Error("gsc_missing", "Brak modułu GSC.");
        }
        $access = upsellio_gsc_get_access_token((array) $oauth);
        if (is_wp_error($access) || !is_string($access) || $access === "") {
            return $access instanceof WP_Error ? $access : new WP_Error("gsc_token", "Brak tokena GSC.");
        }
        $endpoint = "https://searchconsole.googleapis.com/webmasters/v3/sites/" . rawurlencode($property) . "/sitemaps";
        $resp = wp_remote_get($endpoint, [
            "timeout" => 25,
            "headers" => ["Authorization" => "Bearer " . $access],
        ]);
        if (is_wp_error($resp)) {
            return $resp;
        }
        $code = (int) wp_remote_retrieve_response_code($resp);
        if ($code >= 400) {
            return new WP_Error("gsc_sitemap", "GSC sitemap HTTP " . $code);
        }
        $payload = json_decode((string) wp_remote_retrieve_body($resp), true);
        if (!is_array($payload)) {
            return [];
        }
        if ($payload === [] || !isset($payload["sitemap"])) {
            return ["sitemap" => []];
        }

        return $payload;
    });
}

/**
 * Indeksacja przy sync GSC: sitemap API, potem inspekcja URL (fallback).
 *
 * @return array<string, mixed>|null
 */
function ups_audit_gsc_resolve_indexation_for_sync(int $account_id, string $property, string $client_website = ""): ?array
{
    $raw = ups_audit_gsc_fetch_sitemap_indexation($account_id, $property);
    if (!is_wp_error($raw) && is_array($raw)) {
        $idx = ups_audit_parse_gsc_sitemap_indexation($raw, $property);
        if (is_array($idx) && (int) ($idx["submitted"] ?? 0) > 0) {
            return $idx;
        }
    }
    $host = $client_website !== "" ? ups_audit_normalize_host((string) wp_parse_url($client_website, PHP_URL_HOST)) : "";
    $urls = ups_audit_discover_client_inspection_urls($client_website !== "" ? $client_website : $property, 5);

    return ups_audit_gsc_inspect_client_urls($account_id, $property, $urls, $host);
}

/**
 * Indeksacja z cache zasobu GSC lub inspekcji URL (globalna opcja WP).
 *
 * @return array<string, mixed>|null
 */
function ups_audit_get_client_indexation_from_resources(int $client_id, string $client_host = ""): ?array
{
    $client_id = (int) $client_id;
    if ($client_id <= 0 || !function_exists("ups_audit_get_client_resources")) {
        return null;
    }
    foreach (ups_audit_get_client_resources($client_id) as $resource) {
        if (!($resource instanceof WP_Post)) {
            continue;
        }
        $rid = (int) $resource->ID;
        if ((string) get_post_meta($rid, "_ups_resource_type", true) !== "gsc") {
            continue;
        }
        $cache = get_post_meta($rid, "_ups_resource_data_cache", true);
        if (is_array($cache) && !empty($cache["indexation"]) && is_array($cache["indexation"])) {
            $idx = $cache["indexation"];
            if ((int) ($idx["submitted"] ?? 0) > 0 || (int) ($idx["sitemap_count"] ?? 0) > 0) {
                return $idx;
            }
        }
    }

    $client_host = ups_audit_normalize_host($client_host);
    if ($client_host === "") {
        return null;
    }

    $pages = (array) get_option("ups_gsc_indexation_pages", []);
    $submitted = 0;
    $indexed = 0;
    $sampled = 0;
    foreach ($pages as $row) {
        if (!is_array($row)) {
            continue;
        }
        $url = (string) ($row["url"] ?? "");
        if (!ups_audit_url_matches_client_host($url, $client_host)) {
            continue;
        }
        $sampled++;
        $submitted++;
        $verdict = (string) ($row["verdict"] ?? "");
        if ($verdict === "PASS" || stripos($verdict, "INDEX") !== false) {
            $indexed++;
        }
    }
    if ($sampled <= 0) {
        return null;
    }

    return [
        "submitted" => $submitted,
        "indexed" => $indexed,
        "ratio" => $submitted > 0 ? (int) round($indexed / $submitted * 100) : 0,
        "pages_sampled" => $sampled,
        "source" => "url_inspection",
        "note" => sprintf("Inspekcja URL — próbka %d stron z %s.", $sampled, $client_host),
    ];
}

/**
 * Indeksacja GSC (domena klienta) + Core Web Vitals (PageSpeed).
 *
 * @return array<string, mixed>
 */
function ups_audit_client_technical_signals(int $client_id, bool $allow_remote = true): array
{
    $client_id = (int) $client_id;
    $website = trim((string) get_post_meta($client_id, "_ups_client_website", true));
    if ($website === "") {
        $website = trim((string) get_post_meta($client_id, "_ups_client_url", true));
    }
    if ($website !== "" && !preg_match("#^https?://#i", $website)) {
        $website = "https://" . $website;
    }

    $host = $website !== "" ? ups_audit_normalize_host((string) wp_parse_url($website, PHP_URL_HOST)) : "";
    $indexation = [
        "submitted" => 0,
        "indexed" => 0,
        "ratio" => null,
        "pages_sampled" => 0,
        "note" => __("Brak URL strony klienta.", "upsellio"),
    ];

    if ($host !== "") {
        $cached_idx = ups_audit_get_client_indexation_from_resources($client_id, $host);
        if (is_array($cached_idx)) {
            $indexation = $cached_idx;
        } elseif ($allow_remote) {
            $live = ups_audit_fetch_client_gsc_sitemap_indexation($client_id);
            if (is_array($live)) {
                $indexation = $live;
                update_post_meta($client_id, "_ups_client_indexation_cache", $live);
            } else {
                $indexation = [
                    "submitted" => 0,
                    "indexed" => 0,
                    "ratio" => null,
                    "pages_sampled" => 0,
                    "note" => "Brak danych indeksacji dla {$host}. Uruchom „Sync danych” — indeksacja pobierana przy sync GSC.",
                ];
            }
        } else {
            $stored = get_post_meta($client_id, "_ups_client_indexation_cache", true);
            if (is_array($stored) && (int) ($stored["submitted"] ?? 0) > 0) {
                $indexation = $stored;
            } else {
                $indexation = [
                    "submitted" => 0,
                    "indexed" => 0,
                    "ratio" => null,
                    "pages_sampled" => 0,
                    "note" => "Indeksacja GSC ({$host}) — sync zasobu GSC uzupełni dane z sitemap.",
                ];
            }
        }
    }

    $cwv = ups_audit_fetch_pagespeed_cwv($website, $allow_remote);

    return [
        "website" => $website,
        "indexation" => $indexation,
        "cwv" => $cwv,
    ];
}

/**
 * Indeksacja z mapy witryn GSC przypisanej do profilu (live API).
 *
 * @return array<string, mixed>|null
 */
function ups_audit_fetch_client_gsc_sitemap_indexation(int $client_id): ?array
{
    $client_id = (int) $client_id;
    if ($client_id <= 0 || !function_exists("ups_audit_get_client_resources")) {
        return null;
    }
    foreach (ups_audit_get_client_resources($client_id) as $resource) {
        if (!($resource instanceof WP_Post)) {
            continue;
        }
        $rid = (int) $resource->ID;
        if ((string) get_post_meta($rid, "_ups_resource_type", true) !== "gsc") {
            continue;
        }
        $property = trim((string) get_post_meta($rid, "_ups_resource_external_id", true));
        $account_id = (int) get_post_meta($rid, "_ups_resource_google_account_id", true);
        if ($property === "" || $account_id <= 0) {
            continue;
        }
        $parsed = ups_audit_gsc_fetch_sitemap_indexation($account_id, $property);
        if (is_wp_error($parsed) || !is_array($parsed)) {
            continue;
        }
        $idx = ups_audit_parse_gsc_sitemap_indexation($parsed, $property);
        if (is_array($idx)) {
            return $idx;
        }
    }

    return null;
}

/**
 * @return array<string, mixed>
 */
function ups_audit_pagespeed_daily_remaining(): int
{
    $limit = max(1, (int) get_option("ups_audit_pagespeed_daily_limit", 5));
    $today = wp_date("Y-m-d");
    $data = get_option("ups_audit_pagespeed_daily_count", []);
    if (!is_array($data) || (string) ($data["date"] ?? "") !== $today) {
        return $limit;
    }

    return max(0, $limit - (int) ($data["count"] ?? 0));
}

function ups_audit_pagespeed_bump_daily_count(): void
{
    $today = wp_date("Y-m-d");
    $data = get_option("ups_audit_pagespeed_daily_count", []);
    if (!is_array($data) || (string) ($data["date"] ?? "") !== $today) {
        $data = ["date" => $today, "count" => 0];
    }
    $data["count"] = (int) ($data["count"] ?? 0) + 1;
    update_option("ups_audit_pagespeed_daily_count", $data, false);
}

function ups_audit_pagespeed_friendly_error(string $raw): string
{
    if (stripos($raw, "Quota exceeded") !== false || stripos($raw, "quota") !== false) {
        return __("Limit PageSpeed API na dziś wyczerpany. Wynik z cache (jeśli jest) lub odśwież jutro / dodaj klucz API w Cloud Console.", "upsellio");
    }

    return $raw;
}

function ups_audit_fetch_pagespeed_cwv(string $url, bool $allow_remote = true, bool $force_refresh = false): array
{
    $url = esc_url_raw(trim($url));
    if ($url === "") {
        return ["status" => "skip", "message" => __("Brak URL", "upsellio")];
    }

    $cache_key = "ups_audit_cwv_" . md5($url);
    $error_key = "ups_audit_cwv_err_" . md5($url);
    $cached = $force_refresh ? false : get_transient($cache_key);
    if (is_array($cached) && ($cached["status"] ?? "") === "ok") {
        $cached["from_cache"] = true;

        return $cached;
    }

    if (!$allow_remote && !$force_refresh) {
        if (is_array($cached)) {
            $cached["from_cache"] = true;

            return $cached;
        }
        $err_cached = get_transient($error_key);
        if (is_array($err_cached)) {
            return $err_cached;
        }

        return [
            "status" => "pending",
            "message" => __("CWV z cache — użyj „Odśwież CWV” (limit 5 zapytań/dzień).", "upsellio"),
        ];
    }

    if (!$force_refresh) {
        $err_cached = get_transient($error_key);
        if (is_array($err_cached)) {
            if (is_array($cached) && ($cached["status"] ?? "") === "ok") {
                $cached["stale"] = true;
                $cached["quota_note"] = (string) ($err_cached["message"] ?? "");

                return $cached;
            }

            return $err_cached;
        }
    }

    if (ups_audit_pagespeed_daily_remaining() <= 0) {
        if (is_array($cached) && ($cached["status"] ?? "") === "ok") {
            $cached["stale"] = true;
            $cached["quota_note"] = __("Dzienny limit PageSpeed w Upsellio wyczerpany — pokazano ostatni cache.", "upsellio");

            return $cached;
        }

        return [
            "status" => "quota",
            "message" => __("Dzienny limit PageSpeed (5/dzień) wyczerpany. Spróbuj jutro.", "upsellio"),
        ];
    }

    $api_key = trim((string) get_option("ups_pagespeed_api_key", ""));
    $api = "https://www.googleapis.com/pagespeedonline/v5/runPagespeed?strategy=mobile&category=performance&url="
        . rawurlencode($url);
    if ($api_key !== "") {
        $api .= "&key=" . rawurlencode($api_key);
    }
    ups_audit_pagespeed_bump_daily_count();
    $resp = wp_remote_get($api, ["timeout" => 60, "sslverify" => true]);
    if (is_wp_error($resp)) {
        $msg = ups_audit_pagespeed_friendly_error($resp->get_error_message());
        $err = ["status" => "error", "message" => $msg];
        set_transient($error_key, $err, 12 * HOUR_IN_SECONDS);
        if (is_array($cached) && ($cached["status"] ?? "") === "ok") {
            $cached["stale"] = true;
            $cached["quota_note"] = $msg;

            return $cached;
        }

        return $err;
    }

    $body = json_decode((string) wp_remote_retrieve_body($resp), true);
    if (!is_array($body)) {
        return ["status" => "error", "message" => __("Nieprawidłowa odpowiedź PageSpeed.", "upsellio")];
    }
    if (isset($body["error"]["message"])) {
        $msg = ups_audit_pagespeed_friendly_error((string) $body["error"]["message"]);
        $err = ["status" => stripos($msg, "limit") !== false ? "quota" : "error", "message" => $msg];
        set_transient($error_key, $err, 12 * HOUR_IN_SECONDS);
        if (is_array($cached) && ($cached["status"] ?? "") === "ok") {
            $cached["stale"] = true;
            $cached["quota_note"] = $msg;

            return $cached;
        }

        return $err;
    }

    $audits = (array) (($body["lighthouseResult"]["audits"] ?? []));
    $pick = static function (string $key) use ($audits): ?float {
        if (!isset($audits[$key]) || !is_array($audits[$key])) {
            return null;
        }
        $v = $audits[$key]["numericValue"] ?? $audits[$key]["displayValue"] ?? null;
        if ($v === null || $v === "") {
            return null;
        }

        return is_numeric($v) ? round((float) $v, 2) : null;
    };

    $perf_score = (int) round((float) (($body["lighthouseResult"]["categories"]["performance"]["score"] ?? 0) * 100));
    $lcp = $pick("largest-contentful-paint");
    $cls = $pick("cumulative-layout-shift");
    $inp = $pick("interaction-to-next-paint") ?? $pick("experimental-interaction-to-next-paint");
    if ($perf_score <= 0 && $lcp === null && $cls === null) {
        $err = [
            "status" => "error",
            "message" => __("PageSpeed nie zwrócił wyników. Dodaj klucz API (ups_pagespeed_api_key) lub spróbuj później.", "upsellio"),
        ];
        set_transient($error_key, $err, 12 * HOUR_IN_SECONDS);

        return $err;
    }

    $result = [
        "status" => "ok",
        "fetched_at" => current_time("mysql"),
        "performance_score" => $perf_score,
        "lcp_ms" => $lcp,
        "cls" => $cls,
        "inp_ms" => $inp,
        "fcp_ms" => $pick("first-contentful-paint"),
        "from_cache" => false,
    ];
    delete_transient($error_key);
    set_transient($cache_key, $result, 7 * DAY_IN_SECONDS);

    return $result;
}

/**
 * Ładuje Dompdf (Composer w katalogu motywu).
 */
function ups_audit_dompdf_available(): bool
{
    $autoload = get_template_directory() . "/vendor/autoload.php";

    return file_exists($autoload);
}

/**
 * Renderuje HTML do binarnego PDF (Dompdf 3).
 *
 * @return string|\WP_Error
 */
function ups_audit_render_html_to_pdf(string $html)
{
    if (!ups_audit_dompdf_available()) {
        return new WP_Error(
            "dompdf_missing",
            __("Brak biblioteki Dompdf. Na serwerze uruchom: cd wp-content/themes/upsellio && composer install", "upsellio")
        );
    }
    try {
        require_once get_template_directory() . "/vendor/autoload.php";
    } catch (Throwable $e) {
        return new WP_Error("dompdf_autoload", __("Dompdf autoload: ", "upsellio") . $e->getMessage());
    }
    if (!class_exists("\\Dompdf\\Dompdf")) {
        return new WP_Error("dompdf_class", __("Nie można załadować klasy Dompdf.", "upsellio"));
    }

    try {
        $options = new \Dompdf\Options();
        $options->set("defaultFont", "DejaVu Sans");
        $options->set("isRemoteEnabled", false);
        $options->set("isHtml5ParserEnabled", true);

        $pdf = new \Dompdf\Dompdf($options);
        $pdf->loadHtml($html, "UTF-8");
        $pdf->setPaper("A4", "portrait");
        $pdf->render();

        return $pdf->output();
    } catch (Throwable $e) {
        return new WP_Error("dompdf_render", __("Render PDF: ", "upsellio") . $e->getMessage());
    }
}

/**
 * Agregacja pod eksport PDF — bez zewnętrznego PageSpeed (unika timeoutu HTTP 500).
 *
 * @return array<string, mixed>
 */
function ups_audit_prepare_dashboard_export_data(int $client_id, int $window_days = 30): array
{
    $client_id = (int) $client_id;
    $window_days = in_array($window_days, [7, 14, 30, 60, 90], true) ? $window_days : 30;

    $agg = ups_audit_aggregate_client_data($client_id, $window_days, 0, false);
    if (function_exists("ups_audit_compute_portfolio_benchmark_cached")) {
        $agg["benchmark"] = ups_audit_compute_portfolio_benchmark_cached($window_days, $client_id);
    }
    if (function_exists("ups_audit_client_technical_signals")) {
        $agg["technical"] = ups_audit_client_technical_signals($client_id, false);
    }
    if (function_exists("upsellio_analytics_channel_ltv_for_client")) {
        try {
            $agg["channel_ltv"] = upsellio_analytics_channel_ltv_for_client($client_id, $window_days, $agg);
        } catch (Throwable $e) {
            $agg["channel_ltv"] = ["rows" => [], "error" => $e->getMessage()];
        }
    }
    if (function_exists("ups_audit_attach_intelligence")) {
        $setup = function_exists("ups_audit_client_setup_status")
            ? ups_audit_client_setup_status($client_id)
            : [];
        $agg = ups_audit_attach_intelligence($agg, $client_id, $setup);
    }

    return $agg;
}

/**
 * HTML do PDF dashboardu (pełny layout, kompatybilny z Dompdf).
 */
function ups_audit_build_dashboard_pdf_html(int $client_id, array $agg, int $window_days = 30): string
{
    $client = get_post($client_id);
    $title = $client instanceof WP_Post ? $client->post_title : "Klient";
    $website = trim((string) get_post_meta($client_id, "_ups_client_website", true));
    $brand = (string) get_option("ups_audit_pdf_brand_color", "#0ABFA3");
    $bench = (array) ($agg["benchmark"] ?? []);
    $recs = (array) ($agg["recommendations"] ?? []);
    $deltas = (array) ($agg["deltas"] ?? []);
    $derived = (array) ($agg["derived"] ?? []);
    $channels = array_slice((array) ($agg["channels"] ?? []), 0, 10);
    $keywords = array_slice((array) ($agg["top_keywords"] ?? []), 0, 10);
    $resources = (array) ($agg["resources"] ?? []);
    $tech = (array) ($agg["technical"] ?? []);
    $idx = (array) ($tech["indexation"] ?? []);
    $cwv = (array) ($tech["cwv"] ?? []);
    $channel_ltv = (array) ($agg["channel_ltv"] ?? []);
    $ltv_rows = array_slice((array) ($channel_ltv["rows"] ?? []), 0, 8);
    $intel = (array) ($agg["intelligence"] ?? []);
    $exec_summary = (array) ($intel["executive_summary"] ?? []);
    $opportunity = (array) ($intel["opportunity"] ?? []);
    $crm_revenue = (array) ($intel["crm_revenue"] ?? []);
    $crm_rows = array_slice((array) ($crm_revenue["rows"] ?? []), 0, 8);
    $has_bench = !empty($bench["has_benchmark"]);
    $clarity_pages = array_slice((array) ($agg["clarity_top_pages"] ?? []), 0, 8);
    $clarity_sources = array_slice((array) ($agg["clarity_by_source"] ?? []), 0, 6);

    $fmt_int = static function ($n): string {
        return number_format((int) $n, 0, ",", " ");
    };
    $fmt_money = static function ($n): string {
        return number_format((float) $n, 0, ",", " ") . " PLN";
    };
    $fmt_delta = static function (string $key) use ($deltas): string {
        if (!isset($deltas[$key]) || !function_exists("ups_audit_format_delta")) {
            return "—";
        }

        return ups_audit_format_delta((float) $deltas[$key]);
    };

    $kpi_cards = [
        ["Sesje GA4", $fmt_int($agg["ga4_sessions"] ?? 0), $fmt_delta("ga4_sessions"), $has_bench ? $fmt_int($bench["ga4_sessions"] ?? 0) : ""],
        ["Konwersje GA4", $fmt_int($agg["ga4_conversions"] ?? 0), $fmt_delta("ga4_conversions"), ""],
        ["CR sesji", number_format((float) ($derived["ga4_conversion_rate"] ?? 0), 2, ",", " ") . "%", "", ""],
        ["Klik. GSC", $fmt_int($agg["gsc_clicks"] ?? 0), $fmt_delta("gsc_clicks"), $has_bench ? $fmt_int($bench["gsc_clicks"] ?? 0) : ""],
        ["CTR GSC", number_format((float) ($derived["gsc_ctr"] ?? ($agg["gsc_ctr"] ?? 0)), 2, ",", " ") . "%", "", ""],
        ["Śr. poz. GSC", number_format((float) ($derived["gsc_avg_position"] ?? ($agg["gsc_avg_position"] ?? 0)), 1, ",", " "), "", ""],
        ["Wydatek Ads", $fmt_money($agg["ads_cost"] ?? 0), $fmt_delta("ads_cost"), ""],
        ["ROAS", number_format((float) ($agg["roas"] ?? 0), 2, ",", " ") . "x", $fmt_delta("roas"), ""],
    ];

    ob_start();
    ?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="utf-8">
<style>
@page { margin: 18mm 14mm 20mm 14mm; }
* { box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #0f172a; line-height: 1.4; margin: 0; }
.header { border-bottom: 3px solid <?php echo esc_attr($brand); ?>; padding-bottom: 10px; margin-bottom: 14px; }
.header h1 { font-size: 18px; color: <?php echo esc_attr($brand); ?>; margin: 0 0 4px; }
.header .sub { color: #64748b; font-size: 9px; }
.header .meta { margin-top: 8px; }
.header .meta span { display: inline-block; margin-right: 14px; font-size: 9px; }
.pill { display: inline-block; background: #ecfdf5; color: #065f46; padding: 3px 10px; border-radius: 12px; font-weight: 700; font-size: 9px; }
h2 { font-size: 12px; color: #0f172a; margin: 16px 0 8px; padding-bottom: 4px; border-bottom: 1px solid #e2e8f0; }
.kpi-grid { width: 100%; border-collapse: separate; border-spacing: 6px; margin: 0 0 4px; }
.kpi-grid td { width: 25%; vertical-align: top; padding: 0; }
.kpi { border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; background: #f8fafc; }
.kpi .lbl { font-size: 8px; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; }
.kpi .val { font-size: 14px; font-weight: 700; margin: 4px 0 2px; color: #0f172a; }
.kpi .delta { font-size: 8px; color: #475569; }
.kpi .bench { font-size: 7px; color: #94a3b8; margin-top: 2px; }
table.data { width: 100%; border-collapse: collapse; margin: 6px 0 10px; }
table.data th { background: #f1f5f9; color: #334155; font-size: 8px; text-transform: uppercase; padding: 6px 8px; border: 1px solid #e2e8f0; text-align: left; }
table.data td { padding: 5px 8px; border: 1px solid #e2e8f0; font-size: 9px; }
table.data tr:nth-child(even) td { background: #fafafa; }
.badge { display: inline-block; padding: 2px 7px; border-radius: 10px; font-size: 7px; font-weight: 700; margin-right: 4px; }
.badge-high { background: #fee2e2; color: #991b1b; }
.badge-medium { background: #ffedd5; color: #9a3412; }
.badge-low { background: #ecfdf5; color: #065f46; }
.rec { margin: 0 0 8px; padding: 8px 10px; border-left: 3px solid #e2e8f0; background: #f8fafc; }
.rec strong { display: block; margin-bottom: 2px; font-size: 10px; }
.rec p { margin: 0; color: #475569; font-size: 9px; }
.two-col { width: 100%; }
.two-col td { width: 50%; vertical-align: top; padding-right: 8px; }
.muted { color: #64748b; }
.footer { margin-top: 16px; padding-top: 8px; border-top: 1px solid #e2e8f0; font-size: 8px; color: #94a3b8; text-align: center; }
</style>
</head>
<body>
<div class="header">
  <h1><?php echo esc_html($title); ?></h1>
  <div class="sub">Dashboard audytu marketingowego · Upsellio CRM</div>
  <div class="meta">
    <span><strong>Okres:</strong> <?php echo (int) $window_days; ?> dni (vs poprzedni okres)</span>
    <span><strong>Wygenerowano:</strong> <?php echo esc_html(wp_date("d.m.Y H:i")); ?></span>
    <?php if ($website !== "") : ?>
      <span><strong>WWW:</strong> <?php echo esc_html($website); ?></span>
    <?php endif; ?>
    <span class="pill">Health <?php echo (int) ($agg["health_score"] ?? 0); ?>/100</span>
  </div>
</div>

<h2>KPI — podsumowanie</h2>
<table class="kpi-grid"><tr>
<?php
    $i = 0;
    foreach ($kpi_cards as $card) :
        if ($i > 0 && $i % 4 === 0) {
            echo '</tr><tr>';
        }
        ?>
  <td><div class="kpi">
    <div class="lbl"><?php echo esc_html($card[0]); ?></div>
    <div class="val"><?php echo esc_html($card[1]); ?></div>
    <?php if ($card[2] !== "") : ?><div class="delta"><?php echo esc_html($card[2]); ?> vs poprz. okres</div><?php endif; ?>
    <?php if ($card[3] !== "") : ?><div class="bench">Śr. portfel: <?php echo esc_html($card[3]); ?></div><?php endif; ?>
  </div></td>
        <?php
        $i++;
    endforeach;
    while ($i % 4 !== 0) {
        echo '<td></td>';
        $i++;
    }
    ?>
</tr></table>

<?php if (!empty($exec_summary["text"])) : ?>
<h2>Executive Summary</h2>
<p style="font-size:13px;font-weight:600;line-height:1.5;"><?php echo esc_html((string) $exec_summary["text"]); ?></p>
<?php if ((int) ($opportunity["score"] ?? 0) > 0) : ?>
<p class="muted" style="font-size:11px;">Opportunity Score: <?php echo (int) $opportunity["score"]; ?>/100 (<?php echo esc_html((string) ($opportunity["label"] ?? "")); ?>)</p>
<?php endif; ?>
<?php endif; ?>

<?php if ($crm_rows !== []) : ?>
<h2>CRM Revenue Attribution</h2>
<?php if (!empty($crm_revenue["summary"])) : ?>
<p style="font-size:12px;font-weight:600;"><?php echo esc_html((string) $crm_revenue["summary"]); ?></p>
<?php endif; ?>
<table class="data">
<tr><th>Kanał</th><th>Koszt</th><th>Leady</th><th>Oferty</th><th>Wygrane</th><th>Przychód</th><th>ROAS</th></tr>
<?php foreach ($crm_rows as $row) :
    if (!is_array($row)) {
        continue;
    }
    ?>
<tr>
  <td><?php echo esc_html((string) ($row["channel"] ?? "")); ?></td>
  <td><?php echo $fmt_money($row["cost"] ?? 0); ?></td>
  <td><?php echo $fmt_int($row["leads"] ?? 0); ?></td>
  <td><?php echo $fmt_int($row["offers"] ?? 0); ?></td>
  <td><?php echo $fmt_int($row["won"] ?? 0); ?></td>
  <td><?php echo $fmt_money($row["revenue"] ?? 0); ?></td>
  <td><?php echo (float) ($row["roas"] ?? 0) > 0 ? esc_html(number_format((float) $row["roas"], 2, ",", " ")) . "x" : "—"; ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<table class="two-col"><tr><td>
<?php if ($channels !== []) : ?>
<h2>Top kanały GA4</h2>
<table class="data">
<tr><th>Kanał</th><th>Sesje</th><th>Konw.</th></tr>
<?php foreach ($channels as $ch) :
    if (!is_array($ch)) {
        continue;
    }
    ?>
<tr>
  <td><?php echo esc_html(trim((string) ($ch["source"] ?? "") . " / " . (string) ($ch["medium"] ?? ""))); ?></td>
  <td><?php echo $fmt_int($ch["sessions"] ?? 0); ?></td>
  <td><?php echo $fmt_int($ch["conversions"] ?? 0); ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
</td><td>
<?php if ($keywords !== []) : ?>
<h2>Top frazy GSC</h2>
<table class="data">
<tr><th>Fraza</th><th>Klik.</th><th>Poz.</th></tr>
<?php foreach ($keywords as $kw) :
    if (!is_array($kw)) {
        continue;
    }
    ?>
<tr>
  <td><?php echo esc_html((string) ($kw["keyword"] ?? "")); ?></td>
  <td><?php echo $fmt_int($kw["clicks"] ?? 0); ?></td>
  <td><?php echo number_format((float) ($kw["position"] ?? 0), 1, ",", " "); ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
</td></tr></table>

<?php if ($ltv_rows !== []) : ?>
<h2>Kanały × leady CRM (LTV / sesja)</h2>
<table class="data">
<tr><th>Kanał</th><th>Sesje</th><th>Leady</th><th>Wygrane</th><th>LTV/sesja</th></tr>
<?php foreach ($ltv_rows as $row) :
    if (!is_array($row)) {
        continue;
    }
    ?>
<tr>
  <td><?php echo esc_html((string) ($row["channel"] ?? "")); ?></td>
  <td><?php echo $fmt_int($row["sessions"] ?? 0); ?></td>
  <td><?php echo $fmt_int($row["leads"] ?? 0); ?></td>
  <td><?php echo $fmt_int($row["won"] ?? 0); ?></td>
  <td><?php echo esc_html(number_format((float) ($row["ltv_per_session"] ?? 0), 2, ",", " ")); ?> zł</td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<h2>Techniczne</h2>
<table class="data">
<tr><th>Obszar</th><th>Wynik</th><th>Szczegóły</th></tr>
<tr>
  <td>Indeksacja GSC</td>
  <td><?php
    $ratio = $idx["ratio"] ?? null;
    echo $ratio !== null ? (int) $ratio . "%" : "—";
    ?></td>
  <td><?php echo esc_html((int) ($idx["indexed"] ?? 0) . " / " . (int) ($idx["submitted"] ?? 0) . " URL"); ?>
    — <?php echo esc_html((string) ($idx["note"] ?? "")); ?></td>
</tr>
<tr>
  <td>PageSpeed (mobile)</td>
  <td><?php
    if (($cwv["status"] ?? "") === "ok") {
        echo (int) ($cwv["performance_score"] ?? 0) . "/100";
    } else {
        echo "—";
    }
    ?></td>
  <td><?php
    if (($cwv["status"] ?? "") === "ok") {
        echo "LCP " . ($cwv["lcp_ms"] !== null ? (int) $cwv["lcp_ms"] . " ms" : "—");
        echo " · CLS " . ($cwv["cls"] !== null ? (string) $cwv["cls"] : "—");
        echo " · INP " . ($cwv["inp_ms"] !== null ? (int) $cwv["inp_ms"] . " ms" : "—");
    } else {
        echo esc_html((string) ($cwv["message"] ?? __("Brak danych CWV.", "upsellio")));
    }
    ?></td>
</tr>
</table>

<?php if ((int) ($agg["clarity_sessions"] ?? 0) > 0 || $clarity_pages !== []) : ?>
<h2>Microsoft Clarity (ostatnie <?php echo (int) ($agg["clarity_window_days"] ?? 3); ?> dni API)</h2>
<table class="data">
<tr><th>Metryka</th><th>Wartość</th></tr>
<tr><td>Sesje</td><td><?php echo $fmt_int($agg["clarity_sessions"] ?? 0); ?></td></tr>
<tr><td>Użytkownicy</td><td><?php echo $fmt_int($agg["clarity_users"] ?? 0); ?></td></tr>
<tr><td>Dead clicks</td><td><?php echo $fmt_int($agg["clarity_dead_clicks"] ?? 0); ?></td></tr>
<tr><td>Rage clicks</td><td><?php echo $fmt_int($agg["clarity_rage_clicks"] ?? 0); ?></td></tr>
<?php if ((float) ($agg["clarity_engagement_sec"] ?? 0) > 0) : ?>
<tr><td>Czas zaangażowania</td><td><?php echo esc_html(number_format((float) $agg["clarity_engagement_sec"], 1, ",", " ")); ?> s</td></tr>
<?php endif; ?>
</table>
<?php if ($clarity_pages !== []) : ?>
<table class="data" style="margin-top:6px;">
<tr><th>Top URL</th><th>Sesje</th></tr>
<?php foreach ($clarity_pages as $pg) :
    if (!is_array($pg)) {
        continue;
    }
    ?>
<tr>
  <td><?php
    $url_lbl = (string) ($pg["label"] ?? "");
    echo esc_html(function_exists("mb_substr") ? mb_substr($url_lbl, 0, 80) : substr($url_lbl, 0, 80));
    ?></td>
  <td><?php echo $fmt_int($pg["sessions"] ?? 0); ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
<?php if ($clarity_sources !== []) : ?>
<table class="data" style="margin-top:6px;">
<tr><th>Źródło</th><th>Sesje</th></tr>
<?php foreach ($clarity_sources as $src) :
    if (!is_array($src)) {
        continue;
    }
    ?>
<tr><td><?php echo esc_html((string) ($src["label"] ?? "")); ?></td><td><?php echo $fmt_int($src["sessions"] ?? 0); ?></td></tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
<?php endif; ?>

<?php if ($resources !== []) : ?>
<h2>Zasoby i sync</h2>
<table class="data">
<tr><th>Zasób</th><th>Typ</th><th>Status</th><th>Ostatni sync</th></tr>
<?php foreach ($resources as $res) :
    if (!is_array($res)) {
        continue;
    }
    $health = (array) ($res["health"] ?? []);
    ?>
<tr>
  <td><?php echo esc_html((string) ($res["title"] ?? "")); ?></td>
  <td><?php echo esc_html(strtoupper((string) ($res["type"] ?? ""))); ?></td>
  <td><?php echo esc_html((string) ($health["label"] ?? $health["status"] ?? "—")); ?></td>
  <td><?php echo esc_html((string) ($res["last_sync"] ?? "")); ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<?php if ($recs !== []) : ?>
<h2>Rekomendacje</h2>
<?php foreach (array_slice($recs, 0, 8) as $tip) :
    if (!is_array($tip)) {
        continue;
    }
    $pr = (string) ($tip["priority"] ?? "low");
    ?>
<div class="rec">
  <span class="badge badge-<?php echo esc_attr($pr); ?>"><?php echo esc_html(strtoupper($pr)); ?></span>
  <strong><?php echo esc_html((string) ($tip["title"] ?? "")); ?></strong>
  <p><?php echo esc_html((string) ($tip["detail"] ?? "")); ?></p>
</div>
<?php endforeach; ?>
<?php endif; ?>

<div class="footer">Upsellio · Raport wygenerowany automatycznie z danych zmapowanych w CRM Audyt</div>
</body></html>
    <?php
    return (string) ob_get_clean();
}

/**
 * @return array{url:string,path:string,filename:string}|\WP_Error
 */
function ups_audit_export_dashboard_pdf(int $client_id, int $window_days = 30)
{
    $client_id = (int) $client_id;
    if ($client_id <= 0 || get_post_type($client_id) !== "crm_client") {
        return new WP_Error("invalid_client", __("Nieprawidłowy klient.", "upsellio"));
    }
    $window_days = in_array($window_days, [7, 14, 30, 60, 90], true) ? $window_days : 30;

    if (function_exists("set_time_limit")) {
        @set_time_limit(120);
    }

    try {
        $agg = function_exists("ups_audit_prepare_dashboard_export_data")
            ? ups_audit_prepare_dashboard_export_data($client_id, $window_days)
            : ups_audit_aggregate_client_data($client_id, $window_days, 0, false);
        $html = ups_audit_build_dashboard_pdf_html($client_id, $agg, $window_days);
        $binary = ups_audit_render_html_to_pdf($html);
        if (is_wp_error($binary)) {
            return $binary;
        }

        $upload = wp_upload_dir();
        if (!empty($upload["error"])) {
            return new WP_Error("upload_dir", (string) $upload["error"]);
        }
        $slug = sanitize_title(get_the_title($client_id));
        $file = "audit-dashboard-" . ($slug !== "" ? $slug . "-" : "") . $client_id . "-" . wp_date("Ymd-His") . ".pdf";
        $path = trailingslashit((string) $upload["path"]) . $file;
        if (file_put_contents($path, $binary, LOCK_EX) === false) {
            return new WP_Error("write_failed", __("Nie udało się zapisać pliku PDF.", "upsellio"));
        }

        return [
            "url" => trailingslashit((string) $upload["url"]) . $file,
            "path" => $path,
            "filename" => $file,
        ];
    } catch (Throwable $e) {
        return new WP_Error("pdf_exception", __("Eksport PDF: ", "upsellio") . $e->getMessage());
    }
}

/**
 * Wysyła alerty e-mail / Slack dla rekomendacji priority=high.
 */
function ups_audit_dispatch_high_priority_alerts(int $client_id, array $recommendations): void
{
    $client_id = (int) $client_id;
    if ($client_id <= 0) {
        return;
    }

    $high = array_values(array_filter($recommendations, static function ($r) {
        return is_array($r) && (string) ($r["priority"] ?? "") === "high";
    }));
    if ($high === []) {
        return;
    }

    $dedupe_key = "ups_audit_alert_sent_" . $client_id . "_" . wp_date("Y-m-d");
    if (get_transient($dedupe_key)) {
        return;
    }

    $client = get_post($client_id);
    $name = $client instanceof WP_Post ? $client->post_title : "Klient #" . $client_id;
    $lines = [];
    foreach ($high as $tip) {
        $lines[] = "• " . (string) ($tip["title"] ?? "") . " — " . (string) ($tip["detail"] ?? "");
    }
    $body = "Upsellio CRM — alerty wysokiego priorytetu\nKlient: {$name}\n\n" . implode("\n", $lines);

    $email = sanitize_email((string) get_option("ups_audit_alert_email", ""));
    if ($email === "" || !is_email($email)) {
        $email = sanitize_email((string) get_option("admin_email"));
    }
    if (is_email($email)) {
        wp_mail($email, "[Upsellio] Alerty HIGH — " . $name, $body);
    }

    $webhook = esc_url_raw((string) get_option("ups_audit_slack_webhook_url", ""));
    if ($webhook !== "") {
        wp_remote_post($webhook, [
            "timeout" => 12,
            "headers" => ["Content-Type" => "application/json"],
            "body" => wp_json_encode([
                "text" => "*Upsellio CRM — HIGH*\n*{$name}*\n" . implode("\n", $lines),
            ]),
        ]);
    }

    set_transient($dedupe_key, 1, DAY_IN_SECONDS);
}

function ups_audit_scan_portfolio_high_alerts(): void
{
    $clients = get_posts([
        "post_type" => "crm_client",
        "posts_per_page" => -1,
        "post_status" => ["publish", "draft"],
        "fields" => "ids",
    ]);
    $window = (int) get_option("ups_audit_default_compare_window", 30);
    foreach ($clients as $cid) {
        $cid = (int) $cid;
        if ($cid <= 0) {
            continue;
        }
        $agg = ups_audit_aggregate_client_data($cid, $window, 0);
        $intel_alerts = (array) (($agg["intelligence"]["alerts"] ?? []));
        if ($intel_alerts !== [] && function_exists("ups_audit_dispatch_intelligence_alerts")) {
            ups_audit_dispatch_intelligence_alerts($cid, $intel_alerts);
        } else {
            ups_audit_dispatch_high_priority_alerts($cid, (array) ($agg["recommendations"] ?? []));
        }
    }
}

add_action("ups_audit_daily_sync", "ups_audit_scan_portfolio_high_alerts", 40);

function ups_audit_maybe_save_alert_settings(): void
{
    if (!function_exists("upsellio_crm_app_is_crm_app_view") || !upsellio_crm_app_is_crm_app_view()) {
        return;
    }
    if (!is_user_logged_in() || !current_user_can("manage_options")) {
        return;
    }
    if (!isset($_POST["ups_audit_alert_settings_save"])) {
        return;
    }
    check_admin_referer("ups_audit_alert_settings", "ups_audit_alert_settings_nonce");
    $email = isset($_POST["ups_audit_alert_email"]) ? sanitize_email(wp_unslash($_POST["ups_audit_alert_email"])) : "";
    $slack = isset($_POST["ups_audit_slack_webhook_url"]) ? esc_url_raw(wp_unslash($_POST["ups_audit_slack_webhook_url"])) : "";
    update_option("ups_audit_alert_email", $email, false);
    update_option("ups_audit_slack_webhook_url", $slack, false);
    $redirect = function_exists("upsellio_crm_url")
        ? upsellio_crm_url("ca-accounts", ["ups_audit_alerts_saved" => "1"])
        : home_url("/crm-app/?view=ca-accounts&ups_audit_alerts_saved=1");
    wp_safe_redirect($redirect);
    exit;
}
add_action("template_redirect", "ups_audit_maybe_save_alert_settings", 5);
