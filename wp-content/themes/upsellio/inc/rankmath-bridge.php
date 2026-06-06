<?php

if (!defined("ABSPATH")) {
    exit;
}

/**
 * Most Rank Math Analytics → dane Upsellio (GSC, GA4, KPI jak w panelu RM).
 * Używany gdy brak natywnego refresh tokena Upsellio lub jako uzupełnienie cache RM.
 */

function upsellio_rankmath_is_active(): bool
{
    return defined("RANK_MATH_VERSION") || function_exists("rank_math");
}

function upsellio_rankmath_connection_status(): array
{
    $out = [
        "plugin" => upsellio_rankmath_is_active(),
        "authorized" => false,
        "gsc" => false,
        "ga4" => false,
        "profile" => "",
        "property_id" => "",
    ];
    if (!upsellio_rankmath_is_active()) {
        return $out;
    }
    if (class_exists("\RankMath\Google\Authentication")) {
        $out["authorized"] = \RankMath\Google\Authentication::is_authorized();
    }
    if (class_exists("\RankMath\Google\Console")) {
        $out["gsc"] = \RankMath\Google\Console::is_console_connected();
    }
    if (class_exists("\RankMath\Google\Analytics")) {
        $out["ga4"] = \RankMath\Google\Analytics::is_analytics_connected();
    }
    $stored_profile = get_option("rank_math_google_analytic_profile", []);
    if (is_array($stored_profile)) {
        $out["profile"] = trim((string) ($stored_profile["profile"] ?? ""));
    }
    $rm_opts = get_option("rank_math_google_analytic_options", []);
    if (is_array($rm_opts)) {
        $out["property_id"] = preg_replace("/\D+/", "", (string) ($rm_opts["property_id"] ?? ""));
    }

    return $out;
}

function upsellio_rankmath_gsc_profile(): string
{
    $status = upsellio_rankmath_connection_status();
    if ($status["profile"] !== "") {
        return $status["profile"];
    }
    $host = preg_replace("/^www\./", "", (string) wp_parse_url(home_url(), PHP_URL_HOST));

    return "sc-domain:" . $host;
}

function upsellio_rankmath_table_exists(string $suffix): bool
{
    global $wpdb;
    $table = $wpdb->prefix . $suffix;

    return $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) === $table;
}

/**
 * @return array<int, array<string, mixed>>|\WP_Error
 */
function upsellio_rankmath_fetch_gsc_keyword_rows(int $days = 30)
{
    if (!class_exists("\RankMath\Google\Console") || !class_exists("\RankMath\Google\Authentication")) {
        return new WP_Error("rankmath_missing", "Rank Math Analytics nie jest aktywny.");
    }
    if (!\RankMath\Google\Authentication::is_authorized() || !\RankMath\Google\Console::is_console_connected()) {
        return new WP_Error("rankmath_gsc", "Rank Math: brak połączenia Search Console.");
    }

    $days = max(2, min(90, $days));
    $end = wp_date("Y-m-d");
    $start = wp_date("Y-m-d", strtotime("-" . $days . " days"));
    $console = new \RankMath\Google\Console();
    $batch = $console->get_search_analytics([
        "profile" => upsellio_rankmath_gsc_profile(),
        "start_date" => $start,
        "end_date" => $end,
        "dimensions" => ["query", "page", "date"],
        "row_limit" => 25000,
    ]);

    if (is_wp_error($batch)) {
        return $batch;
    }
    if ($batch === false || !is_array($batch)) {
        return new WP_Error("rankmath_gsc_empty", "Rank Math GSC: brak wierszy.");
    }

    $rows = [];
    foreach ($batch as $row) {
        if (!is_array($row)) {
            continue;
        }
        $keys = isset($row["keys"]) && is_array($row["keys"]) ? $row["keys"] : [];
        $keyword = sanitize_text_field((string) ($keys[0] ?? ""));
        $page_url = esc_url_raw((string) ($keys[1] ?? ""));
        $date_key = sanitize_text_field((string) ($keys[2] ?? ""));
        if ($keyword === "" || $page_url === "" || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $date_key)) {
            continue;
        }
        $rows[] = [
            "keyword" => $keyword,
            "url" => $page_url,
            "position" => max(1, round((float) ($row["position"] ?? 0), 2)),
            "impressions" => max(0, (int) round((float) ($row["impressions"] ?? 0))),
            "clicks" => max(0, (int) round((float) ($row["clicks"] ?? 0))),
            "ctr" => round(max(0, (float) ($row["ctr"] ?? 0)) * 100, 2),
            "date" => $date_key,
        ];
    }

    return array_slice($rows, 0, 100000);
}

/**
 * @return array<int, array<string, mixed>>|\WP_Error
 */
function upsellio_rankmath_fetch_ga4_channel_rows(int $days = 30)
{
    if (!class_exists("\RankMath\Google\Analytics") || !class_exists("\RankMath\Google\Authentication")) {
        return new WP_Error("rankmath_missing", "Rank Math Analytics nie jest aktywny.");
    }
    if (!\RankMath\Google\Authentication::is_authorized() || !\RankMath\Google\Analytics::is_analytics_connected()) {
        return new WP_Error("rankmath_ga4", "Rank Math: brak połączenia GA4.");
    }

    $rel = $days <= 7 ? "7daysAgo" : ($days <= 14 ? "14daysAgo" : "30daysAgo");
    $batch = \RankMath\Google\Analytics::get_analytics([
        "start_date" => $rel,
        "end_date" => "yesterday",
        "dimensions" => [
            ["name" => "sessionSource"],
            ["name" => "sessionMedium"],
            ["name" => "sessionCampaignName"],
        ],
        "metrics" => [
            ["name" => "sessions"],
            ["name" => "engagedSessions"],
            ["name" => "conversions"],
            ["name" => "totalRevenue"],
        ],
    ]);

    if (is_wp_error($batch)) {
        $batch = \RankMath\Google\Analytics::get_analytics([
            "start_date" => $rel,
            "end_date" => "yesterday",
            "dimensions" => [
                ["name" => "sessionSource"],
                ["name" => "sessionMedium"],
                ["name" => "sessionCampaignName"],
            ],
            "metrics" => [
                ["name" => "sessions"],
                ["name" => "engagedSessions"],
            ],
        ]);
    }
    if (is_wp_error($batch)) {
        return $batch;
    }
    if ($batch === false || !is_array($batch)) {
        return new WP_Error("rankmath_ga4_empty", "Rank Math GA4: brak wierszy.");
    }

    $sync_date = wp_date("Y-m-d");
    $out = [];
    foreach ($batch as $item) {
        if (!is_array($item)) {
            continue;
        }
        $source = sanitize_text_field((string) ($item["sessionSource"] ?? ""));
        $medium = sanitize_text_field((string) ($item["sessionMedium"] ?? ""));
        $campaign = sanitize_text_field((string) ($item["sessionCampaignName"] ?? ""));
        if ($source === "" && $campaign === "") {
            continue;
        }
        $key = strtolower(trim($source . "|" . $campaign));
        if ($key === "|") {
            continue;
        }
        $out[$key] = [
            "date" => $sync_date,
            "source" => $source !== "" ? $source : "(direct)",
            "medium" => $medium,
            "campaign" => $campaign !== "" ? $campaign : "(not set)",
            "sessions" => max(0, (int) ($item["sessions"] ?? 0)),
            "engaged_sessions" => max(0, (int) ($item["engagedSessions"] ?? 0)),
            "conversions" => max(0, (int) ($item["conversions"] ?? 0)),
            "revenue" => max(0.0, (float) ($item["totalRevenue"] ?? 0)),
        ];
    }

    return array_values($out);
}

/**
 * @return array<int, array<string, mixed>>|\WP_Error
 */
function upsellio_rankmath_fetch_ga4_direct(int $days = 30)
{
    if (!class_exists("\RankMath\Google\Api")) {
        return new WP_Error("rankmath_missing", "Rank Math Api nie jest dostępny.");
    }

    $status = upsellio_rankmath_connection_status();
    $property_id = $status["property_id"];
    if ($property_id === "") {
        return new WP_Error("rankmath_ga4_property", "Brak property_id w Rank Math.");
    }

    $rel = $days <= 7 ? "7daysAgo" : ($days <= 14 ? "14daysAgo" : "30daysAgo");
    $body = [
        "dateRanges" => [["startDate" => $rel, "endDate" => "yesterday"]],
        "dimensions" => [
            ["name" => "sessionSource"],
            ["name" => "sessionMedium"],
            ["name" => "sessionCampaignName"],
        ],
        "metrics" => [
            ["name" => "sessions"],
            ["name" => "engagedSessions"],
        ],
        "limit" => 250000,
    ];

    $api = \RankMath\Google\Api::get();
    $api->set_workflow("analytics");
    $response = $api->http_post(
        "https://analyticsdata.googleapis.com/v1beta/properties/" . $property_id . ":runReport",
        $body,
        45
    );

    if (is_wp_error($response)) {
        return $response;
    }
    if (!$api->is_success() || !is_array($response) || empty($response["rows"])) {
        return new WP_Error("rankmath_ga4_direct", "GA4 Data API (Rank Math token): brak danych.");
    }

    $dimensions = isset($response["dimensionHeaders"]) && is_array($response["dimensionHeaders"])
        ? array_column($response["dimensionHeaders"], "name")
        : [];
    $metrics = isset($response["metricHeaders"]) && is_array($response["metricHeaders"])
        ? array_column($response["metricHeaders"], "name")
        : [];

    $sync_date = wp_date("Y-m-d");
    $out = [];
    foreach ($response["rows"] as $row) {
        if (!is_array($row)) {
            continue;
        }
        $item = [];
        if (isset($row["dimensionValues"]) && is_array($row["dimensionValues"])) {
            foreach ($row["dimensionValues"] as $i => $dim) {
                $item[(string) ($dimensions[$i] ?? $i)] = (string) ($dim["value"] ?? "");
            }
        }
        if (isset($row["metricValues"]) && is_array($row["metricValues"])) {
            foreach ($row["metricValues"] as $i => $met) {
                $item[(string) ($metrics[$i] ?? $i)] = (int) ($met["value"] ?? 0);
            }
        }
        $source = sanitize_text_field((string) ($item["sessionSource"] ?? ""));
        $campaign = sanitize_text_field((string) ($item["sessionCampaignName"] ?? ""));
        if ($source === "" && $campaign === "") {
            continue;
        }
        $key = strtolower(trim($source . "|" . $campaign));
        if ($key === "|") {
            continue;
        }
        $out[$key] = [
            "date" => $sync_date,
            "source" => $source !== "" ? $source : "(direct)",
            "medium" => sanitize_text_field((string) ($item["sessionMedium"] ?? "")),
            "campaign" => $campaign !== "" ? $campaign : "(not set)",
            "sessions" => max(0, (int) ($item["sessions"] ?? 0)),
            "engaged_sessions" => max(0, (int) ($item["engagedSessions"] ?? 0)),
            "conversions" => 0,
            "revenue" => 0.0,
        ];
    }

    return array_values($out);
}

function upsellio_rankmath_normalize_metric_block($block): array
{
    if (is_object($block)) {
        $block = (array) $block;
    }
    if (!is_array($block)) {
        return ["total" => 0, "previous" => 0, "difference" => 0];
    }
    $total = $block["total"] ?? 0;
    $previous = $block["previous"] ?? 0;
    $diff = $block["difference"] ?? null;
    if ($total === "n/a" || $previous === "n/a") {
        return ["total" => 0, "previous" => 0, "difference" => 0, "na" => true];
    }

    return [
        "total" => is_numeric($total) ? (float) $total : 0,
        "previous" => is_numeric($previous) ? (float) $previous : 0,
        "difference" => is_numeric($diff) ? (float) $diff : (is_numeric($total) && is_numeric($previous) && (float) $previous > 0
            ? round((((float) $total - (float) $previous) / (float) $previous) * 100, 1)
            : 0),
    ];
}

/**
 * KPI jak widget Rank Math (kliknięcia, wyświetlenia, pozycja, słowa kluczowe).
 *
 * @return array<string, array<string, mixed>>
 */
function upsellio_rankmath_get_dashboard_summary(int $days = 30): array
{
    $days = in_array($days, [7, 14, 30, 60, 90], true) ? $days : 30;
    $empty = [
        "clicks" => ["total" => 0, "previous" => 0, "difference" => 0],
        "impressions" => ["total" => 0, "previous" => 0, "difference" => 0],
        "position" => ["total" => 0, "previous" => 0, "difference" => 0],
        "keywords" => ["total" => 0, "previous" => 0, "difference" => 0],
        "source" => "computed",
    ];

    if (upsellio_rankmath_is_active() && class_exists("\RankMath\Analytics\Stats") && class_exists("\RankMath\Analytics\Summary")) {
        try {
            \RankMath\Analytics\Stats::get()->set_date_range($days);
            $widget = (new \RankMath\Analytics\Summary())->get_widget();
            if (is_object($widget)) {
                return [
                    "clicks" => upsellio_rankmath_normalize_metric_block($widget->clicks ?? null),
                    "impressions" => upsellio_rankmath_normalize_metric_block($widget->impressions ?? null),
                    "position" => upsellio_rankmath_normalize_metric_block($widget->position ?? null),
                    "keywords" => upsellio_rankmath_normalize_metric_block($widget->keywords ?? null),
                    "source" => "rank_math_widget",
                ];
            }
        } catch (Throwable $e) {
            if (function_exists("upsellio_gsc_log")) {
                upsellio_gsc_log("rankmath.widget.error", ["msg" => $e->getMessage()]);
            }
        }
    }

    $series = upsellio_rankmath_gsc_daily_series($days);
    $cur_dates = array_slice(array_keys($series), -$days);
    $prev_dates = array_slice(array_keys($series), 0, max(0, count($series) - $days));
    $sum = static function (array $dates, array $series, string $key): float {
        $t = 0.0;
        foreach ($dates as $d) {
            $t += (float) (($series[$d][$key] ?? 0));
        }

        return $t;
    };
    $kw_rows = (array) get_option("upsellio_keyword_metrics_rows", []);
    $kw_unique = [];
    foreach ($kw_rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $k = strtolower(trim((string) ($row["keyword"] ?? "")));
        if ($k !== "") {
            $kw_unique[$k] = true;
        }
    }

    $cur_clicks = $sum($cur_dates, $series, "clicks");
    $prev_clicks = $sum($prev_dates, $series, "clicks");
    $cur_impr = $sum($cur_dates, $series, "impressions");
    $prev_impr = $sum($prev_dates, $series, "impressions");

    $pos_weight = 0.0;
    $pos_sum = 0.0;
    foreach ($kw_rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $imp = (int) ($row["impressions"] ?? 0);
        $pos_sum += (float) ($row["position"] ?? 0) * $imp;
        $pos_weight += $imp;
    }
    $avg_pos = $pos_weight > 0 ? round($pos_sum / $pos_weight, 2) : 0;

    $pct = static function (float $c, float $p): float {
        return $p > 0 ? round((($c - $p) / $p) * 100, 1) : ($c > 0 ? 100.0 : 0.0);
    };

    return [
        "clicks" => ["total" => $cur_clicks, "previous" => $prev_clicks, "difference" => $pct($cur_clicks, $prev_clicks)],
        "impressions" => ["total" => $cur_impr, "previous" => $prev_impr, "difference" => $pct($cur_impr, $prev_impr)],
        "position" => ["total" => $avg_pos, "previous" => $avg_pos, "difference" => 0],
        "keywords" => ["total" => (float) count($kw_unique), "previous" => 0, "difference" => 0],
        "source" => $series !== [] ? "upsellio_series" : "empty",
    ];
}

/**
 * Serie dzienne GSC (najpierw tabela RM, potem agregacja z opcji Upsellio).
 *
 * @return array<string, array{clicks:int,impressions:int,position:float}>
 */
function upsellio_rankmath_gsc_daily_series(int $days = 30): array
{
    $days = max(7, min(90, $days));
    $end_ts = strtotime(wp_date("Y-m-d") . " 23:59:59");
    $start_ts = strtotime("-" . ($days * 2) . " days", $end_ts);
    $series = [];

    if (upsellio_rankmath_table_exists("rank_math_analytics_gsc")) {
        global $wpdb;
        $table = $wpdb->prefix . "rank_math_analytics_gsc";
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT created, clicks, impressions, position FROM {$table} WHERE created >= %s AND created <= %s ORDER BY created ASC",
                gmdate("Y-m-d H:i:s", $start_ts),
                gmdate("Y-m-d H:i:s", $end_ts)
            ),
            ARRAY_A
        );
        if (is_array($rows)) {
            foreach ($rows as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $d = substr((string) ($row["created"] ?? ""), 0, 10);
                if ($d === "") {
                    continue;
                }
                if (!isset($series[$d])) {
                    $series[$d] = ["clicks" => 0, "impressions" => 0, "position_weight" => 0.0, "position_imp" => 0];
                }
                $series[$d]["clicks"] += (int) ($row["clicks"] ?? 0);
                $series[$d]["impressions"] += (int) ($row["impressions"] ?? 0);
                $imp = (int) ($row["impressions"] ?? 0);
                $series[$d]["position_weight"] += (float) ($row["position"] ?? 0) * $imp;
                $series[$d]["position_imp"] += $imp;
            }
        }
    }

    if ($series === []) {
        $keyword_rows = (array) get_option("upsellio_keyword_metrics_rows", []);
        if (function_exists("upsellio_get_daily_keyword_series")) {
            $dates = [];
            for ($i = ($days * 2) - 1; $i >= 0; $i--) {
                $dates[] = wp_date("Y-m-d", strtotime("-{$i} days"));
            }
            $kw_series = upsellio_get_daily_keyword_series($keyword_rows, $dates);
            foreach ($kw_series as $d => $vals) {
                if (!is_array($vals)) {
                    continue;
                }
                $series[$d] = [
                    "clicks" => (int) ($vals["clicks"] ?? 0),
                    "impressions" => (int) ($vals["impressions"] ?? 0),
                    "position" => 0.0,
                ];
            }
        }
    }

    foreach ($series as $d => $vals) {
        $imp = (int) ($vals["position_imp"] ?? 0);
        $series[$d]["position"] = $imp > 0
            ? round((float) ($vals["position_weight"] ?? 0) / $imp, 2)
            : 0.0;
        unset($series[$d]["position_weight"], $series[$d]["position_imp"]);
    }
    ksort($series);

    return $series;
}

/**
 * @return array<int, array<string, mixed>>
 */
function upsellio_rankmath_top_pages(int $limit = 15, int $days = 30): array
{
    $limit = max(1, min(50, $limit));
    $cutoff = wp_date("Y-m-d", strtotime("-" . max(7, $days) . " days"));
    $agg = [];

    if (upsellio_rankmath_table_exists("rank_math_analytics_objects")) {
        global $wpdb;
        $table = $wpdb->prefix . "rank_math_analytics_objects";
        $rows = $wpdb->get_results(
            "SELECT page, clicks, impressions, position, created FROM {$table} ORDER BY clicks DESC LIMIT 500",
            ARRAY_A
        );
        if (is_array($rows)) {
            foreach ($rows as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $url = (string) ($row["page"] ?? "");
                if ($url === "") {
                    continue;
                }
                if (!isset($agg[$url])) {
                    $agg[$url] = ["url" => $url, "clicks" => 0, "impressions" => 0, "position_weight" => 0.0, "position_imp" => 0];
                }
                $agg[$url]["clicks"] += (int) ($row["clicks"] ?? 0);
                $imp = (int) ($row["impressions"] ?? 0);
                $agg[$url]["impressions"] += $imp;
                $agg[$url]["position_weight"] += (float) ($row["position"] ?? 0) * $imp;
                $agg[$url]["position_imp"] += $imp;
            }
        }
    }

    if ($agg === []) {
        foreach ((array) get_option("upsellio_keyword_metrics_rows", []) as $row) {
            if (!is_array($row)) {
                continue;
            }
            $date = (string) ($row["date"] ?? "");
            if ($date !== "" && $date < $cutoff) {
                continue;
            }
            $url = (string) ($row["url"] ?? "");
            if ($url === "") {
                continue;
            }
            if (!isset($agg[$url])) {
                $agg[$url] = ["url" => $url, "clicks" => 0, "impressions" => 0, "position_weight" => 0.0, "position_imp" => 0];
            }
            $agg[$url]["clicks"] += (int) ($row["clicks"] ?? 0);
            $imp = (int) ($row["impressions"] ?? 0);
            $agg[$url]["impressions"] += $imp;
            $agg[$url]["position_weight"] += (float) ($row["position"] ?? 0) * $imp;
            $agg[$url]["position_imp"] += $imp;
        }
    }

    foreach ($agg as $url => $vals) {
        $imp = (int) ($vals["position_imp"] ?? 0);
        $agg[$url]["position"] = $imp > 0 ? round((float) $vals["position_weight"] / $imp, 2) : 0;
        $agg[$url]["ctr"] = $agg[$url]["impressions"] > 0
            ? round(($agg[$url]["clicks"] / $agg[$url]["impressions"]) * 100, 2)
            : 0;
        unset($agg[$url]["position_weight"], $agg[$url]["position_imp"]);
    }

    uasort($agg, static function ($a, $b) {
        return ((int) ($b["clicks"] ?? 0)) <=> ((int) ($a["clicks"] ?? 0));
    });

    return array_slice(array_values($agg), 0, $limit);
}

/**
 * Szybkie wygrane — frazy na poz. 4–20 z dużą liczbą wyświetleń (jak „Striking distance” w RM).
 *
 * @return array<int, array<string, mixed>>
 */
function upsellio_rankmath_quick_win_keywords(int $limit = 20, int $days = 30): array
{
    $limit = max(1, min(50, $limit));
    $cutoff = wp_date("Y-m-d", strtotime("-" . max(7, $days) . " days"));
    $in_range = [];
    foreach ((array) get_option("upsellio_keyword_metrics_rows", []) as $row) {
        if (!is_array($row)) {
            continue;
        }
        $date = (string) ($row["date"] ?? "");
        if ($date !== "" && $date < $cutoff) {
            continue;
        }
        $in_range[] = $row;
    }

    $aggregated = function_exists("upsellio_gsc_aggregate_keywords")
        ? upsellio_gsc_aggregate_keywords($in_range)
        : [];
    $kw = [];
    foreach ($aggregated as $row) {
        $pos = (float) ($row["position"] ?? 99);
        if ($pos < 4 || $pos > 20) {
            continue;
        }
        $kw[] = [
            "keyword" => (string) ($row["keyword"] ?? ""),
            "url" => (string) ($row["url"] ?? ""),
            "position" => $pos,
            "impressions" => (int) ($row["impressions"] ?? 0),
            "clicks" => (int) ($row["clicks"] ?? 0),
        ];
    }

    usort($kw, static function ($a, $b) {
        return ((int) ($b["impressions"] ?? 0)) <=> ((int) ($a["impressions"] ?? 0));
    });

    return array_slice($kw, 0, $limit);
}

function upsellio_rankmath_bootstrap_ga4_property_from_rm(): void
{
    $status = upsellio_rankmath_connection_status();
    if ($status["property_id"] === "" || !function_exists("upsellio_get_ga4_property_id") || !function_exists("upsellio_save_ga4_property_id")) {
        return;
    }
    if (upsellio_get_ga4_property_id() === "") {
        upsellio_save_ga4_property_id($status["property_id"]);
    }
}

function upsellio_rankmath_sync_gsc_into_upsellio(int $days = 30): array
{
    $creds = function_exists("upsellio_get_gsc_credentials") ? upsellio_get_gsc_credentials() : [];
    $has_native = is_array($creds) && trim((string) ($creds["refresh_token"] ?? "")) !== "";

    if ($has_native && function_exists("upsellio_gsc_fetch_rows")) {
        $trace = "rm_bridge_" . uniqid("", true);
        $rows = upsellio_gsc_fetch_rows($creds, $days, $trace);
        $source = "gsc_live";
    } else {
        $rows = upsellio_rankmath_fetch_gsc_keyword_rows($days);
        $source = "gsc_rankmath_bridge";
    }

    if (is_wp_error($rows)) {
        return ["ok" => false, "source" => $source, "error" => $rows->get_error_message(), "count" => 0];
    }
    if ($rows === []) {
        return ["ok" => false, "source" => $source, "error" => "Brak wierszy GSC.", "count" => 0];
    }

    update_option("upsellio_keyword_metrics_rows", array_values($rows), false);
    update_option("upsellio_keyword_metrics_source", $source, false);
    update_option("upsellio_keyword_metrics_last_sync", wp_date("Y-m-d H:i:s"), false);

    return ["ok" => true, "source" => $source, "error" => "", "count" => count($rows)];
}

function upsellio_rankmath_sync_ga4_into_upsellio(int $days = 30): array
{
    upsellio_rankmath_bootstrap_ga4_property_from_rm();
    $creds = function_exists("upsellio_get_gsc_credentials") ? upsellio_get_gsc_credentials() : [];
    $has_native = is_array($creds) && trim((string) ($creds["refresh_token"] ?? "")) !== "";
    $pid = function_exists("upsellio_get_ga4_property_id") ? upsellio_get_ga4_property_id() : "";

    if ($has_native && $pid !== "" && function_exists("upsellio_ga4_data_api_fetch_aggregates") && function_exists("upsellio_ga4_apply_aggregates_to_crm")) {
        $trace = "rm_ga4_" . uniqid("", true);
        $rows = upsellio_ga4_data_api_fetch_aggregates($pid, $days, $trace);
        if (!is_wp_error($rows) && $rows !== []) {
            upsellio_ga4_apply_aggregates_to_crm($rows);
            update_option("ups_automation_ga4_source", "native", false);

            return ["ok" => true, "source" => "native", "error" => "", "count" => count($rows)];
        }
    }

    $rows = upsellio_rankmath_fetch_ga4_channel_rows($days);
    if (is_wp_error($rows) || $rows === []) {
        if (is_wp_error($rows)) {
            $err = $rows->get_error_message();
        } else {
            $err = "";
        }
        $rows = upsellio_rankmath_fetch_ga4_direct($days);
        if (is_wp_error($rows)) {
            return ["ok" => false, "source" => "rankmath_bridge", "error" => is_wp_error($rows) ? $rows->get_error_message() : $err, "count" => 0];
        }
    }

    if ($rows === [] || !function_exists("upsellio_ga4_apply_aggregates_to_crm")) {
        return ["ok" => false, "source" => "rankmath_bridge", "error" => "Brak wierszy GA4.", "count" => 0];
    }

    upsellio_ga4_apply_aggregates_to_crm($rows);
    update_option("ups_automation_ga4_source", "rankmath_bridge", false);

    return ["ok" => true, "source" => "rankmath_bridge", "error" => "", "count" => count($rows)];
}

/**
 * Pełna synchronizacja Google (PHP) — odpowiednik tools/run-google-sync.php.
 *
 * @return array<string, mixed>
 */
function upsellio_google_unified_sync(int $days = 0): array
{
    $days = $days > 0 ? $days : (int) get_option("upsellio_gsc_sync_days_last", 30);
    $days = in_array($days, [7, 14, 30, 60, 90], true) ? $days : 30;
    update_option("ups_automation_ga4_sync_enabled", "1", false);

    $log = [
        "days" => $days,
        "gsc" => upsellio_rankmath_sync_gsc_into_upsellio($days),
        "ga4" => upsellio_rankmath_sync_ga4_into_upsellio($days),
        "at" => wp_date("Y-m-d H:i:s"),
    ];

    $creds = function_exists("upsellio_get_gsc_credentials") ? upsellio_get_gsc_credentials() : [];
    if (is_array($creds) && trim((string) ($creds["refresh_token"] ?? "")) !== "" && function_exists("upsellio_google_ads_sync_campaigns")) {
        try {
            upsellio_google_ads_sync_campaigns();
            $log["ads"] = ["ok" => true];
        } catch (Throwable $e) {
            $log["ads"] = ["ok" => false, "error" => $e->getMessage()];
        }
    }

    if (function_exists("ups_audit_sync_all_mapped_resources")) {
        $log["audit"] = ups_audit_sync_all_mapped_resources($days);
    }

    update_option("upsellio_google_unified_sync_last", $log, false);

    if (function_exists("upsellio_gsc_log")) {
        upsellio_gsc_log("google.unified_sync", $log);
    }

    return $log;
}

function upsellio_rankmath_clear_widget_cache(): void
{
    if (!class_exists("\RankMath\Analytics\Stats")) {
        return;
    }
    try {
        $key = \RankMath\Analytics\Stats::get()->get_cache_key("dashboard_stats_widget");
        delete_transient($key);
    } catch (Throwable $e) {
        // ignore
    }
}

add_action("upsellio_google_bridge_sync", static function (): void {
    if (function_exists("upsellio_google_unified_sync")) {
        upsellio_google_unified_sync(0);
    }
}, 5);

add_action("wp_ajax_upsellio_google_sync_now", static function (): void {
    check_ajax_referer("ups_crm_app_action", "nonce");
    if (!current_user_can("edit_posts")) {
        wp_send_json_error(["message" => "forbidden"], 403);
    }
    $days = isset($_POST["days"]) ? (int) $_POST["days"] : 0;
    $log = upsellio_google_unified_sync($days);
    upsellio_rankmath_clear_widget_cache();
    wp_send_json_success(["log" => $log]);
});
