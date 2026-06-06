<?php

if (!defined("ABSPATH")) {
    exit;
}

/** Clarity Data Export API — max 10 żądań / projekt / dzień, dane z ostatnich 1–3 dni. */
define("UPS_AUDIT_CLARITY_API_URL", "https://www.clarity.ms/export-data/api/v1/project-live-insights");
define("UPS_AUDIT_CLARITY_DAILY_LIMIT", 10);

/**
 * @return string
 */
function ups_audit_clarity_get_token(int $resource_id): string
{
    $resource_id = (int) $resource_id;
    if ($resource_id <= 0 || !function_exists("ups_audit_decrypt")) {
        return "";
    }
    $enc = (string) get_post_meta($resource_id, "_ups_clarity_api_token", true);

    return ups_audit_decrypt($enc);
}

function ups_audit_clarity_save_token(int $resource_id, string $token): void
{
    $resource_id = (int) $resource_id;
    $token = trim($token);
    if ($resource_id <= 0) {
        return;
    }
    if ($token === "") {
        delete_post_meta($resource_id, "_ups_clarity_api_token");
        return;
    }
    update_post_meta(
        $resource_id,
        "_ups_clarity_api_token",
        function_exists("ups_audit_encrypt") ? ups_audit_encrypt($token) : $token
    );
}

function ups_audit_clarity_daily_usage(int $resource_id): int
{
    $key = "ups_clarity_req_" . (int) $resource_id . "_" . gmdate("Y-m-d");

    return (int) get_transient($key);
}

function ups_audit_clarity_can_request(int $resource_id): bool
{
    return ups_audit_clarity_daily_usage($resource_id) < UPS_AUDIT_CLARITY_DAILY_LIMIT;
}

function ups_audit_clarity_track_request(int $resource_id): void
{
    $resource_id = (int) $resource_id;
    $key = "ups_clarity_req_" . $resource_id . "_" . gmdate("Y-m-d");
    $count = ups_audit_clarity_daily_usage($resource_id) + 1;
    set_transient($key, $count, DAY_IN_SECONDS);
}

/**
 * @return array|\WP_Error
 */
function ups_audit_clarity_allowed_dimensions(): array
{
    return ["Browser", "Device", "Country/Region", "OS", "Source", "Medium", "Campaign", "Channel", "URL"];
}

function ups_audit_clarity_sanitize_dimension(string $dimension): string
{
    $dimension = trim($dimension);
    $allowed = ups_audit_clarity_allowed_dimensions();

    return in_array($dimension, $allowed, true) ? $dimension : "Device";
}

/**
 * @return array<int, array{days: int, d1: string, d2: string, d3: string, totals: bool, store: string}>
 */
function ups_audit_clarity_sync_fetch_plan(int $max_requests = 5): array
{
    $plan = [
        ["days" => 3, "d1" => "Device", "d2" => "Browser", "d3" => "", "totals" => true, "store" => "by_device"],
        ["days" => 3, "d1" => "URL", "d2" => "", "d3" => "", "totals" => false, "store" => "top_pages"],
        ["days" => 3, "d1" => "Source", "d2" => "Medium", "d3" => "", "totals" => false, "store" => "by_source"],
        ["days" => 3, "d1" => "Country/Region", "d2" => "", "d3" => "", "totals" => false, "store" => "by_country"],
        ["days" => 3, "d1" => "Channel", "d2" => "", "d3" => "", "totals" => false, "store" => "by_channel"],
        ["days" => 3, "d1" => "OS", "d2" => "", "d3" => "", "totals" => false, "store" => "by_os"],
    ];
    $max_requests = max(1, min(9, (int) $max_requests));

    return array_slice($plan, 0, $max_requests);
}

function ups_audit_clarity_project_dashboard_url(string $project_slug): string
{
    $project_slug = sanitize_key($project_slug);
    if ($project_slug === "") {
        return "https://clarity.microsoft.com/";
    }

    return "https://clarity.microsoft.com/projects/view/" . rawurlencode($project_slug) . "/dashboard";
}

function ups_audit_clarity_fetch_live_insights(
    string $api_token,
    int $num_days = 3,
    string $dimension1 = "Device",
    string $dimension2 = "",
    string $dimension3 = ""
) {
    $api_token = trim($api_token);
    if ($api_token === "") {
        return new WP_Error("clarity_no_token", __("Brak tokena API Clarity.", "upsellio"));
    }
    $num_days = in_array($num_days, [1, 2, 3], true) ? $num_days : 3;
    $dimension1 = ups_audit_clarity_sanitize_dimension($dimension1);
    $dimension2 = $dimension2 !== "" ? ups_audit_clarity_sanitize_dimension($dimension2) : "";
    $dimension3 = $dimension3 !== "" ? ups_audit_clarity_sanitize_dimension($dimension3) : "";

    $query = [
        "numOfDays" => (string) $num_days,
        "dimension1" => $dimension1,
    ];
    if ($dimension2 !== "" && $dimension2 !== $dimension1) {
        $query["dimension2"] = $dimension2;
    }
    if ($dimension3 !== "" && $dimension3 !== $dimension1 && $dimension3 !== $dimension2) {
        $query["dimension3"] = $dimension3;
    }

    $url = add_query_arg($query, UPS_AUDIT_CLARITY_API_URL);

    $resp = wp_remote_get($url, [
        "timeout" => 45,
        "sslverify" => true,
        "headers" => [
            "Authorization" => "Bearer " . $api_token,
            "Content-Type" => "application/json",
        ],
    ]);

    if (is_wp_error($resp)) {
        return $resp;
    }

    $code = (int) wp_remote_retrieve_response_code($resp);
    $body_raw = (string) wp_remote_retrieve_body($resp);
    $body = json_decode($body_raw, true);

    if ($code === 401) {
        return new WP_Error("clarity_unauthorized", __("Clarity: nieprawidłowy lub wygasły token API (401).", "upsellio"));
    }
    if ($code === 403) {
        $detail = is_array($body) ? trim((string) ($body["message"] ?? $body["error"] ?? "")) : "";
        $hint = __("Clarity: brak uprawnień (403). Użyj tokena z Settings → Data Export (nie Clarity ID ze skryptu). Konto musi być administratorem projektu.", "upsellio");
        if ($detail !== "") {
            $hint .= " " . $detail;
        }

        return new WP_Error("clarity_forbidden", $hint);
    }
    if ($code === 429) {
        return new WP_Error("clarity_quota", __("Clarity: limit 10 zapytań API na dzień dla tego projektu.", "upsellio"));
    }
    if ($code >= 400) {
        $msg = is_array($body) && isset($body["message"])
            ? (string) $body["message"]
            : __("Clarity API HTTP ", "upsellio") . $code;

        return new WP_Error("clarity_http", $msg);
    }

    if (!is_array($body)) {
        return new WP_Error("clarity_parse", __("Clarity: nieprawidłowa odpowiedź JSON.", "upsellio"));
    }

    return $body;
}

/**
 * Normalizuje odpowiedź project-live-insights do listy bloków metryk.
 *
 * @param mixed $body
 * @return array<int, array<string, mixed>>
 */
function ups_audit_clarity_normalize_blocks($body): array
{
    if (!is_array($body)) {
        return [];
    }
    if (isset($body["metricName"]) && is_string($body["metricName"])) {
        return [$body];
    }
    if (isset($body[0]) && is_array($body[0]) && isset($body[0]["metricName"])) {
        return $body;
    }
    foreach (["data", "insights", "metrics", "projectLiveInsights", "result"] as $key) {
        if (isset($body[$key]) && is_array($body[$key])) {
            return ups_audit_clarity_normalize_blocks($body[$key]);
        }
    }

    return $body;
}

/**
 * @param array<string, mixed> $a
 * @param array<string, mixed> $b
 * @return array<string, mixed>
 */
function ups_audit_clarity_empty_summary(): array
{
    return [
        "sessions" => 0,
        "bot_sessions" => 0,
        "users" => 0,
        "pages_per_session" => 0.0,
        "dead_clicks" => 0,
        "rage_clicks" => 0,
        "quickback_clicks" => 0,
        "script_errors" => 0,
        "error_clicks" => 0,
        "excessive_scroll" => 0,
        "engagement_time_sec" => 0.0,
        "scroll_depth" => 0.0,
        "by_dimension" => [],
        "top_pages" => [],
        "top_referrers" => [],
        "by_source" => [],
        "by_country" => [],
        "by_channel" => [],
        "by_os" => [],
    ];
}

/**
 * @param array<string, mixed> $a
 * @param array<string, mixed> $b
 */
function ups_audit_clarity_merge_summaries(array $a, array $b, bool $merge_totals = false): array
{
    $out = $a !== [] ? $a : ups_audit_clarity_empty_summary();
    if ($merge_totals) {
        foreach (["sessions", "bot_sessions", "users", "dead_clicks", "rage_clicks", "quickback_clicks", "script_errors", "error_clicks", "excessive_scroll"] as $int_key) {
            $out[$int_key] = max((int) ($out[$int_key] ?? 0), (int) ($b[$int_key] ?? 0));
        }
        foreach (["pages_per_session", "engagement_time_sec", "scroll_depth"] as $float_key) {
            $out[$float_key] = max((float) ($out[$float_key] ?? 0), (float) ($b[$float_key] ?? 0));
        }
    } else {
        foreach (["dead_clicks", "rage_clicks", "quickback_clicks", "script_errors", "error_clicks", "excessive_scroll"] as $int_key) {
            $out[$int_key] = max((int) ($out[$int_key] ?? 0), (int) ($b[$int_key] ?? 0));
        }
        foreach (["engagement_time_sec", "scroll_depth", "pages_per_session"] as $float_key) {
            $out[$float_key] = max((float) ($out[$float_key] ?? 0), (float) ($b[$float_key] ?? 0));
        }
    }
    foreach ((array) ($b["by_dimension"] ?? []) as $lbl => $row) {
        if (!is_array($row)) {
            continue;
        }
        $delta = [];
        foreach (["sessions", "users", "dead_clicks", "rage_clicks", "quickback_clicks"] as $metric_key) {
            if (isset($row[$metric_key]) && is_numeric($row[$metric_key])) {
                $delta[$metric_key] = (int) $row[$metric_key];
            }
        }
        if ($delta !== []) {
            ups_audit_clarity_push_dim_row($out["by_dimension"], (string) ($row["label"] ?? $lbl), $delta);
        }
    }
    foreach (["top_pages", "top_referrers", "by_source", "by_country", "by_channel", "by_os"] as $list_key) {
        $out[$list_key] = ups_audit_clarity_merge_ranked_lists(
            (array) ($out[$list_key] ?? []),
            (array) ($b[$list_key] ?? [])
        );
    }

    return $out;
}

/**
 * @param array<int, array<string, mixed>> $a
 * @param array<int, array<string, mixed>> $b
 * @return array<int, array<string, mixed>>
 */
function ups_audit_clarity_filter_ranked_rows(array $rows, int $limit = 10): array
{
    $out = [];
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $lbl = strtolower(trim((string) ($row["label"] ?? $row["url"] ?? "")));
        if ($lbl === "" || $lbl === "razem" || $lbl === "total") {
            continue;
        }
        $out[] = $row;
        if (count($out) >= $limit) {
            break;
        }
    }

    return $out;
}

function ups_audit_clarity_merge_ranked_lists(array $a, array $b, int $limit = 15): array
{
    $map = [];
    foreach (array_merge($a, $b) as $row) {
        if (!is_array($row)) {
            continue;
        }
        $key = strtolower(trim((string) ($row["label"] ?? $row["url"] ?? "")));
        if ($key === "") {
            continue;
        }
        if (!isset($map[$key])) {
            $map[$key] = $row;
            continue;
        }
        foreach (["sessions", "users"] as $k) {
            $map[$key][$k] = max((int) ($map[$key][$k] ?? 0), (int) ($row[$k] ?? 0));
        }
        foreach (["dead_clicks", "rage_clicks"] as $k) {
            $map[$key][$k] = (int) ($map[$key][$k] ?? 0) + (int) ($row[$k] ?? 0);
        }
    }
    $list = array_values($map);
    usort($list, static function ($x, $y) {
        return ((int) ($y["sessions"] ?? 0)) <=> ((int) ($x["sessions"] ?? 0));
    });

    return array_slice($list, 0, $limit);
}

/**
 * Pobiera i scala insights (wiele wymiarów = więcej wywołań API).
 *
 * @return array{summary: array<string, mixed>, blocks: array<int, array<string, mixed>>, requests: int}|\WP_Error
 */
function ups_audit_clarity_fetch_merged_insights(string $api_token, int $num_days = 3, array $dimensions = ["Device", "Browser", "Source"], int $resource_id = 0)
{
    unset($num_days, $dimensions);

    return ups_audit_clarity_fetch_full_snapshot($api_token, $resource_id, 3);
}

/**
 * Pełny snapshot: wiele wymiarów/metryk w ramach limitu 10 zapytań/dzień (domyślnie do 5 na sync).
 *
 * @return array{summary: array<string, mixed>, blocks: array<int, array<string, mixed>>, requests: int, plan: array<int, string>}|\WP_Error
 */
function ups_audit_clarity_fetch_full_snapshot(string $api_token, int $resource_id = 0, int $max_requests = 5)
{
    $api_token = trim($api_token);
    $resource_id = (int) $resource_id;
    $summary = ups_audit_clarity_empty_summary();
    $all_blocks = [];
    $requests = 0;
    $plan_log = [];
    $last_error = null;
    $remaining = $max_requests;
    if ($resource_id > 0) {
        $remaining = min($remaining, UPS_AUDIT_CLARITY_DAILY_LIMIT - ups_audit_clarity_daily_usage($resource_id));
    }

    foreach (ups_audit_clarity_sync_fetch_plan($remaining) as $step) {
        if ($resource_id > 0 && !ups_audit_clarity_can_request($resource_id)) {
            break;
        }
        $raw = ups_audit_clarity_fetch_live_insights(
            $api_token,
            (int) $step["days"],
            (string) $step["d1"],
            (string) $step["d2"],
            (string) $step["d3"]
        );
        if (is_wp_error($raw)) {
            $last_error = $raw;
            if ($requests === 0) {
                return $raw;
            }
            break;
        }
        if ($resource_id > 0) {
            ups_audit_clarity_track_request($resource_id);
        }
        $requests++;
        $dim_key = (string) $step["d1"];
        $blocks = ups_audit_clarity_normalize_blocks($raw);
        $parsed = ups_audit_clarity_parse_insights(
            $blocks,
            $dim_key,
            !empty($step["totals"]),
            (string) $step["store"]
        );
        $summary = ups_audit_clarity_merge_summaries($summary, $parsed, !empty($step["totals"]));
        $all_blocks = array_merge($all_blocks, $blocks);
        $plan_log[] = (string) $step["store"];
    }

    if ($requests === 0 && $last_error instanceof WP_Error) {
        return $last_error;
    }

    $summary["by_dimension"] = ups_audit_clarity_finalize_breakdown(
        array_values((array) ($summary["by_dimension"] ?? [])),
        (int) ($summary["sessions"] ?? 0),
        (int) ($summary["dead_clicks"] ?? 0),
        (int) ($summary["rage_clicks"] ?? 0)
    );

    return [
        "summary" => $summary,
        "blocks" => $all_blocks,
        "requests" => $requests,
        "plan" => $plan_log,
    ];
}

/**
 * @param array<int, mixed> $blocks
 * @return array<string, mixed>
 */
function ups_audit_clarity_parse_insights(
    array $blocks,
    string $dimension_key = "Device",
    bool $include_in_totals = true,
    string $store_as = "by_dimension"
): array {
    $blocks = ups_audit_clarity_normalize_blocks($blocks);
    $allowed_dims = ups_audit_clarity_allowed_dimensions();
    $summary = ups_audit_clarity_empty_summary();
    $rank_target = in_array($store_as, ["top_pages", "top_referrers", "by_source", "by_country", "by_channel", "by_os", "by_device"], true)
        ? $store_as
        : "by_dimension";

    foreach ($blocks as $block) {
        if (!is_array($block)) {
            continue;
        }
        $metric_name = strtolower((string) ($block["metricName"] ?? ""));
        $rows = (array) ($block["information"] ?? []);

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $dim_val = (string) ($row[$dimension_key] ?? "");
            if ($dim_val === "") {
                foreach ($allowed_dims as $dim_key) {
                    if (isset($row[$dim_key]) && (string) $row[$dim_key] !== "") {
                        $dim_val = (string) $row[$dim_key];
                        break;
                    }
                }
            }
            if ($dim_val === "") {
                $dim_val = (string) ($row["Name"] ?? $row["name"] ?? __("Razem", "upsellio"));
            }

            if (strpos($metric_name, "popular") !== false && strpos($metric_name, "page") !== false) {
                $sess = ups_audit_clarity_row_number($row, ["totalSessionCount", "session", "count"]);
                $url = (string) ($row["URL"] ?? $row["Page"] ?? $row["Page Title"] ?? $dim_val);
                ups_audit_clarity_push_ranked_row($summary["top_pages"], $url !== "" ? $url : $dim_val, [
                    "sessions" => $sess,
                    "users" => ups_audit_clarity_row_number($row, ["distantUserCount", "user"]),
                ]);
            } elseif (strpos($metric_name, "referrer") !== false) {
                $sess = ups_audit_clarity_row_number($row, ["totalSessionCount", "session", "count"]);
                ups_audit_clarity_push_ranked_row($summary["top_referrers"], $dim_val, ["sessions" => $sess]);
            } elseif (strpos($metric_name, "traffic") !== false || $metric_name === "sessions" || strpos($metric_name, "session") !== false) {
                $sess = ups_audit_clarity_metric_from_row($row, "session");
                if ($sess <= 0) {
                    $sess = ups_audit_clarity_row_number($row, ["totalsessioncount", "sessioncount"], ["dead", "rage", "click"]);
                }
                $bots = ups_audit_clarity_row_number($row, ["totalBotSessionCount", "bot"]);
                $users = ups_audit_clarity_row_number($row, ["distantUserCount", "user"]);
                $pps = ups_audit_clarity_row_float($row, ["PagesPerSessionPercentage", "pagesPerSession"]);
                if ($include_in_totals) {
                    $summary["sessions"] += $sess;
                    $summary["bot_sessions"] += $bots;
                    $summary["users"] += $users;
                    if ($pps > 0) {
                        $summary["pages_per_session"] = max($summary["pages_per_session"], $pps);
                    }
                }
                $row_data = ["sessions" => $sess, "users" => $users];
                if ($rank_target === "top_pages" || $dimension_key === "URL") {
                    ups_audit_clarity_push_ranked_row($summary["top_pages"], $dim_val, $row_data);
                } elseif ($rank_target === "by_source" || $dimension_key === "Source" || $dimension_key === "Medium") {
                    $src_lbl = $dim_val;
                    if ($dimension_key === "Medium" && isset($row["Source"])) {
                        $src_lbl = (string) $row["Source"] . " / " . $dim_val;
                    }
                    ups_audit_clarity_push_ranked_row($summary["by_source"], $src_lbl, $row_data);
                } elseif ($rank_target === "by_country" || $dimension_key === "Country/Region") {
                    ups_audit_clarity_push_ranked_row($summary["by_country"], $dim_val, $row_data);
                } elseif ($rank_target === "by_channel" || $dimension_key === "Channel" || $dimension_key === "Campaign") {
                    ups_audit_clarity_push_ranked_row($summary["by_channel"], $dim_val, $row_data);
                } elseif ($rank_target === "by_os" || $dimension_key === "OS") {
                    ups_audit_clarity_push_ranked_row($summary["by_os"], $dim_val, $row_data);
                } else {
                    ups_audit_clarity_push_dim_row($summary["by_dimension"], $dim_val, $row_data);
                }
            } elseif (strpos($metric_name, "dead") !== false && strpos($metric_name, "click") !== false) {
                $n = ups_audit_clarity_metric_from_row($row, "dead");
                if ($n <= 0) {
                    $n = ups_audit_clarity_row_number($row, ["deadclickcount", "deadclick"], ["session", "user", "page", "rage"]);
                }
                if ($include_in_totals) {
                    $summary["dead_clicks"] += $n;
                }
                if ($rank_target === "by_device" || $dimension_key === "Device") {
                    ups_audit_clarity_push_dim_row($summary["by_dimension"], $dim_val, ["dead_clicks" => $n]);
                } elseif (in_array($rank_target, ["top_pages", "top_referrers", "by_source", "by_country", "by_channel", "by_os"], true)) {
                    ups_audit_clarity_push_ranked_row($summary[$rank_target], $dim_val, ["dead_clicks" => $n]);
                }
            } elseif (strpos($metric_name, "rage") !== false && strpos($metric_name, "click") !== false) {
                $n = ups_audit_clarity_metric_from_row($row, "rage");
                if ($n <= 0) {
                    $n = ups_audit_clarity_row_number($row, ["rageclickcount", "rageclick"], ["session", "user", "page", "dead"]);
                }
                if ($include_in_totals) {
                    $summary["rage_clicks"] += $n;
                }
                if ($rank_target === "by_device" || $dimension_key === "Device") {
                    ups_audit_clarity_push_dim_row($summary["by_dimension"], $dim_val, ["rage_clicks" => $n]);
                } elseif (in_array($rank_target, ["top_pages", "top_referrers", "by_source", "by_country", "by_channel", "by_os"], true)) {
                    ups_audit_clarity_push_ranked_row($summary[$rank_target], $dim_val, ["rage_clicks" => $n]);
                }
            } elseif (strpos($metric_name, "quickback") !== false) {
                $summary["quickback_clicks"] += ups_audit_clarity_row_number($row, ["count", "quick"]);
            } elseif (strpos($metric_name, "script error") !== false) {
                $summary["script_errors"] += ups_audit_clarity_row_number($row, ["count", "error"]);
            } elseif (strpos($metric_name, "error click") !== false) {
                $summary["error_clicks"] += ups_audit_clarity_row_number($row, ["count", "error"]);
            } elseif (strpos($metric_name, "excessive scroll") !== false) {
                $summary["excessive_scroll"] += ups_audit_clarity_row_number($row, ["count", "scroll"]);
            } elseif (strpos($metric_name, "engagement") !== false) {
                $et = ups_audit_clarity_row_float($row, ["engagement", "time", "average", "sec"]);
                if ($et > $summary["engagement_time_sec"]) {
                    $summary["engagement_time_sec"] = $et;
                }
            } elseif (strpos($metric_name, "scroll depth") !== false) {
                $sd = ups_audit_clarity_row_float($row, ["scroll", "depth", "average"]);
                if ($sd > $summary["scroll_depth"]) {
                    $summary["scroll_depth"] = $sd;
                }
            }
        }
    }

    foreach (["by_dimension", "top_pages", "top_referrers", "by_source", "by_country", "by_channel", "by_os"] as $list_key) {
        if (!isset($summary[$list_key]) || !is_array($summary[$list_key])) {
            continue;
        }
        if ($list_key === "by_dimension") {
            uasort($summary[$list_key], static function ($a, $b) {
                return ((int) ($b["sessions"] ?? 0)) <=> ((int) ($a["sessions"] ?? 0));
            });
            $summary[$list_key] = array_slice($summary[$list_key], 0, 12, true);
            $summary[$list_key] = array_values($summary[$list_key]);
        } else {
            $summary[$list_key] = ups_audit_clarity_merge_ranked_lists([], $summary[$list_key], 15);
        }
    }

    return $summary;
}

/**
 * @param array<int, array<string, mixed>> $list
 * @param array<string, int|float> $add
 */
function ups_audit_clarity_push_ranked_row(array &$list, string $label, array $add): void
{
    $label = trim($label);
    if ($label === "") {
        return;
    }
    $found = false;
    foreach ($list as $idx => $row) {
        if (!is_array($row) || strtolower((string) ($row["label"] ?? "")) !== strtolower($label)) {
            continue;
        }
        foreach ($add as $k => $v) {
            if (is_numeric($v)) {
                $list[$idx][$k] = (int) ($list[$idx][$k] ?? 0) + (int) $v;
            }
        }
        $found = true;
        break;
    }
    if (!$found) {
        $list[] = array_merge(["label" => $label], $add);
    }
}

/**
 * Jawne mapowanie pól Clarity API (unika mylenia sesji z dead/rage clicks).
 *
 * @param array<string, mixed> $row
 */
function ups_audit_clarity_metric_from_row(array $row, string $type): int
{
    $maps = [
        "session" => ["totalSessionCount", "TotalSessionCount", "sessions", "sessionCount", "Sessions"],
        "dead" => ["deadClickCount", "DeadClickCount", "deadClicks", "dead_click_count", "DeadClicks"],
        "rage" => ["rageClickCount", "RageClickCount", "rageClicks", "rage_click_count", "RageClicks"],
    ];
    foreach ($maps[$type] ?? [] as $wanted) {
        foreach ($row as $key => $val) {
            if (strcasecmp((string) $key, $wanted) !== 0) {
                continue;
            }
            if (is_numeric($val)) {
                return (int) round((float) $val);
            }
            $digits = preg_replace("/\D+/", "", (string) $val);

            return $digits !== "" ? (int) $digits : 0;
        }
    }

    return 0;
}

/**
 * Korekta wierszy urządzeń: gdy dead/rage = sesje (błąd parsera), rozdziel wg udziału sesji.
 *
 * @param array<int, array<string, mixed>> $rows
 *
 * @return array<int, array<string, mixed>>
 */
function ups_audit_clarity_finalize_breakdown(array $rows, int $total_sessions, int $total_dead, int $total_rage): array
{
    if ($rows === [] || $total_sessions <= 0) {
        return $rows;
    }

    $suspicious_dead = 0;
    $suspicious_rage = 0;
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $sess = (int) ($row["sessions"] ?? 0);
        $dead = (int) ($row["dead_clicks"] ?? 0);
        $rage = (int) ($row["rage_clicks"] ?? 0);
        if ($sess > 0 && $dead === $sess) {
            $suspicious_dead++;
        }
        if ($sess > 0 && $rage === $sess) {
            $suspicious_rage++;
        }
    }

    $redistribute = static function (array $list, int $total, int $suspicious_count) use ($total_sessions): array {
        if ($total <= 0 || $suspicious_count < count($list)) {
            return $list;
        }
        $out = [];
        $allocated = 0;
        $last_idx = count($list) - 1;
        foreach ($list as $idx => $row) {
            if (!is_array($row)) {
                continue;
            }
            $sess = (int) ($row["sessions"] ?? 0);
            if ($idx === $last_idx) {
                $row["dead_clicks"] = max(0, $total - $allocated);
            } else {
                $share = $sess > 0 ? (int) round($total * ($sess / max(1, $total_sessions))) : 0;
                $row["dead_clicks"] = $share;
                $allocated += $share;
            }
            $out[] = $row;
        }

        return $out;
    };

    if ($suspicious_dead === count($rows) && $total_dead > 0) {
        $rows = $redistribute($rows, $total_dead, $suspicious_dead);
    }
    if ($suspicious_rage === count($rows) && $total_rage > 0) {
        $allocated_rage = 0;
        $last_idx = count($rows) - 1;
        foreach ($rows as $idx => &$row) {
            if (!is_array($row)) {
                continue;
            }
            $sess = (int) ($row["sessions"] ?? 0);
            if ($idx === $last_idx) {
                $row["rage_clicks"] = max(0, $total_rage - $allocated_rage);
            } else {
                $share = $sess > 0 ? (int) round($total_rage * ($sess / max(1, $total_sessions))) : 0;
                $row["rage_clicks"] = $share;
                $allocated_rage += $share;
            }
        }
        unset($row);
    }

    foreach ($rows as &$row) {
        if (!is_array($row)) {
            continue;
        }
        $sess = (int) ($row["sessions"] ?? 0);
        if ($sess > 0) {
            $row["dead_clicks"] = min((int) ($row["dead_clicks"] ?? 0), $sess * 3);
            $row["rage_clicks"] = min((int) ($row["rage_clicks"] ?? 0), $sess * 2);
        }
        if ((int) ($row["dead_clicks"] ?? 0) === $sess && $sess > 0 && $total_dead > 0 && $total_dead !== $sess) {
            $row["dead_clicks"] = (int) round($total_dead * ($sess / $total_sessions));
        }
        if ((int) ($row["rage_clicks"] ?? 0) === $sess && $sess > 0 && $total_rage > 0 && $total_rage !== $sess) {
            $row["rage_clicks"] = (int) round($total_rage * ($sess / $total_sessions));
        }
    }
    unset($row);

    return $rows;
}

/**
 * @param array<string, mixed> $row
 */
function ups_audit_clarity_row_number(array $row, array $needles, array $exclude_fragments = []): int
{
    foreach ($row as $key => $val) {
        $k = strtolower((string) $key);
        $skip = false;
        foreach ($exclude_fragments as $ex) {
            if ($ex !== "" && stripos($k, strtolower($ex)) !== false) {
                $skip = true;
                break;
            }
        }
        if ($skip) {
            continue;
        }
        foreach ($needles as $needle) {
            if (stripos($k, strtolower($needle)) === false) {
                continue;
            }
            if (is_numeric($val)) {
                return (int) round((float) $val);
            }
            $digits = preg_replace("/\D+/", "", (string) $val);

            return $digits !== "" ? (int) $digits : 0;
        }
    }

    return 0;
}

/**
 * @param array<string, mixed> $row
 */
function ups_audit_clarity_row_float(array $row, array $needles): float
{
    foreach ($row as $key => $val) {
        $k = strtolower((string) $key);
        foreach ($needles as $needle) {
            if (stripos($k, strtolower($needle)) === false) {
                continue;
            }
            if (is_numeric($val)) {
                return round((float) $val, 4);
            }
        }
    }

    return 0.0;
}

/**
 * @param array<string, array<string, int|float>> $bucket
 * @param array<string, int|float> $add
 */
function ups_audit_clarity_push_dim_row(array &$bucket, string $label, array $add): void
{
    $label = $label !== "" ? $label : __("Inne", "upsellio");
    if (!isset($bucket[$label])) {
        $bucket[$label] = ["label" => $label, "sessions" => 0, "users" => 0, "dead_clicks" => 0, "rage_clicks" => 0];
    }
    foreach ($add as $k => $v) {
        if (is_numeric($v)) {
            $bucket[$label][$k] = (int) ($bucket[$label][$k] ?? 0) + (int) $v;
        }
    }
}

/**
 * Import zasobu Clarity (bez konta Google).
 *
 * @return int|\WP_Error
 */
function ups_audit_clarity_import_resource(string $project_name, string $api_token, string $project_slug = "")
{
    $project_name = sanitize_text_field($project_name);
    $api_token = trim($api_token);
    $project_slug = sanitize_key($project_slug);
    if ($project_slug === "") {
        $project_slug = sanitize_title($project_name);
    }
    if ($project_name === "" || strlen($project_slug) < 2) {
        return new WP_Error("clarity_invalid", __("Podaj nazwę projektu Clarity.", "upsellio"));
    }
    if ($api_token === "" || strlen($api_token) < 8) {
        return new WP_Error("clarity_token", __("Wklej token API z Clarity (Settings → Data Export).", "upsellio"));
    }

    $existing = get_posts([
        "post_type" => "crm_audit_resource",
        "posts_per_page" => 1,
        "post_status" => ["publish", "draft"],
        "meta_query" => [
            ["key" => "_ups_resource_type", "value" => "clarity"],
            ["key" => "_ups_resource_external_id", "value" => $project_slug],
        ],
    ]);
    if (!empty($existing) && $existing[0] instanceof WP_Post) {
        $rid = (int) $existing[0]->ID;
        ups_audit_clarity_save_token($rid, $api_token);
        wp_update_post(["ID" => $rid, "post_title" => $project_name]);

        return $rid;
    }

    $resource_id = wp_insert_post([
        "post_type" => "crm_audit_resource",
        "post_title" => $project_name,
        "post_status" => "publish",
    ]);
    if ($resource_id <= 0) {
        return new WP_Error("clarity_create", __("Nie udało się utworzyć zasobu Clarity.", "upsellio"));
    }

    update_post_meta($resource_id, "_ups_resource_type", "clarity");
    update_post_meta($resource_id, "_ups_resource_external_id", $project_slug);
    update_post_meta($resource_id, "_ups_resource_display_name", $project_name);
    update_post_meta($resource_id, "_ups_resource_google_account_id", 0);
    update_post_meta($resource_id, "_ups_resource_client_id", 0);
    update_post_meta($resource_id, "_ups_resource_imported_at", current_time("mysql"));
    ups_audit_clarity_save_token($resource_id, $api_token);

    return $resource_id;
}

/**
 * @return array<int, array<string, mixed>>
 */
function ups_audit_clarity_list_resources(): array
{
    $posts = get_posts([
        "post_type" => "crm_audit_resource",
        "posts_per_page" => 100,
        "post_status" => ["publish", "draft"],
        "meta_query" => [[
            "key" => "_ups_resource_type",
            "value" => "clarity",
        ]],
        "orderby" => "title",
        "order" => "ASC",
    ]);
    $rows = [];
    foreach ($posts as $p) {
        if (!($p instanceof WP_Post)) {
            continue;
        }
        $rid = (int) $p->ID;
        $rows[] = [
            "id" => $rid,
            "title" => (string) $p->post_title,
            "slug" => (string) get_post_meta($rid, "_ups_resource_external_id", true),
            "client_id" => (int) get_post_meta($rid, "_ups_resource_client_id", true),
            "has_token" => ups_audit_clarity_get_token($rid) !== "",
            "api_usage_today" => ups_audit_clarity_daily_usage($rid),
        ];
    }

    return $rows;
}
