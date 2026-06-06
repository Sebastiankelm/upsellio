<?php

if (!defined("ABSPATH")) {
    exit;
}

function ups_audit_save_resource_cache(int $resource_id, array $payload): void
{
    $resource_id = (int) $resource_id;
    $prev = get_post_meta($resource_id, "_ups_resource_data_cache", true);
    if (is_array($prev) && !empty($prev)) {
        update_post_meta($resource_id, "_ups_resource_data_cache_previous", $prev);
    }
    $payload["synced_at"] = current_time("mysql");
    update_post_meta($resource_id, "_ups_resource_data_cache", $payload);
    update_post_meta($resource_id, "_ups_resource_last_data_sync", current_time("mysql"));
}

function ups_audit_sync_gsc_resource(int $resource_id, int $days = 30, bool $full_history = false): void
{
    $resource_id = (int) $resource_id;
    $site_url = (string) get_post_meta($resource_id, "_ups_resource_external_id", true);
    $account_id = (int) get_post_meta($resource_id, "_ups_resource_google_account_id", true);
    $windows = ups_audit_period_windows($days);
    $fetch_days = function_exists("ups_audit_api_fetch_days")
        ? ups_audit_api_fetch_days("gsc", $full_history)
        : min(90, $days * 2 + 7);

    $result = ups_audit_with_account_oauth($account_id, static function ($oauth) use ($site_url, $fetch_days) {
        if (!function_exists("upsellio_gsc_fetch_rows")) {
            return new WP_Error("gsc_missing", "Brak modułu GSC.");
        }
        $creds = array_merge((array) $oauth, ["property" => $site_url]);

        return upsellio_gsc_fetch_rows($creds, $fetch_days);
    });

    if (is_wp_error($result)) {
        ups_audit_save_resource_cache($resource_id, [
            "period_days" => $days,
            "error" => $result->get_error_message(),
            "summary" => ["clicks" => 0, "impressions" => 0, "avg_position" => 0, "ctr" => 0],
            "previous_summary" => ["clicks" => 0, "impressions" => 0, "avg_position" => 0, "ctr" => 0],
            "timeseries" => [],
            "top_keywords" => [],
        ]);

        return;
    }

    $rows = is_array($result) ? $result : [];
    $cur = $windows["current"];
    $prev = $windows["previous"];
    $summary_cur = ["clicks" => 0, "impressions" => 0, "position_weighted" => 0.0, "imp_for_pos" => 0];
    $summary_prev = ["clicks" => 0, "impressions" => 0, "position_weighted" => 0.0, "imp_for_pos" => 0];
    $timeseries = [];
    $kw_agg = [];
    $kw_agg_prev = [];
    $keyword_daily = [];

    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $date_key = (string) ($row["date"] ?? "");
        $clicks = (int) ($row["clicks"] ?? 0);
        $impressions = (int) ($row["impressions"] ?? 0);
        $position = (float) ($row["position"] ?? 0);
        $keyword = strtolower(trim((string) ($row["keyword"] ?? "")));

        if ($date_key !== "") {
            if (!isset($timeseries[$date_key])) {
                $timeseries[$date_key] = ["clicks" => 0, "impressions" => 0];
            }
            $timeseries[$date_key]["clicks"] += $clicks;
            $timeseries[$date_key]["impressions"] += $impressions;
        }

        if (ups_audit_date_in_range($date_key, $cur)) {
            $summary_cur["clicks"] += $clicks;
            $summary_cur["impressions"] += $impressions;
            $summary_cur["position_weighted"] += $position * max(1, $impressions);
            $summary_cur["imp_for_pos"] += max(1, $impressions);
        } elseif (ups_audit_date_in_range($date_key, $prev)) {
            $summary_prev["clicks"] += $clicks;
            $summary_prev["impressions"] += $impressions;
            $summary_prev["position_weighted"] += $position * max(1, $impressions);
            $summary_prev["imp_for_pos"] += max(1, $impressions);
        }

        if ($keyword !== "" && $date_key !== "") {
            $keyword_daily[] = [
                "d" => $date_key,
                "kw" => $keyword,
                "clk" => $clicks,
                "imp" => $impressions,
                "pos" => $position,
            ];
        }

        if ($keyword !== "" && ups_audit_date_in_range($date_key, $cur)) {
            if (!isset($kw_agg[$keyword])) {
                $kw_agg[$keyword] = ["keyword" => $keyword, "clicks" => 0, "impressions" => 0, "position_weighted" => 0.0, "imp" => 0];
            }
            $kw_agg[$keyword]["clicks"] += $clicks;
            $kw_agg[$keyword]["impressions"] += $impressions;
            $kw_agg[$keyword]["position_weighted"] += $position * max(1, $impressions);
            $kw_agg[$keyword]["imp"] += max(1, $impressions);
        } elseif ($keyword !== "" && ups_audit_date_in_range($date_key, $prev)) {
            if (!isset($kw_agg_prev[$keyword])) {
                $kw_agg_prev[$keyword] = ["keyword" => $keyword, "clicks" => 0, "impressions" => 0, "position_weighted" => 0.0, "imp" => 0];
            }
            $kw_agg_prev[$keyword]["clicks"] += $clicks;
            $kw_agg_prev[$keyword]["impressions"] += $impressions;
            $kw_agg_prev[$keyword]["position_weighted"] += $position * max(1, $impressions);
            $kw_agg_prev[$keyword]["imp"] += max(1, $impressions);
        }
    }

    $fmt = static function ($s): array {
        $s = is_array($s) ? $s : [];
        $imp = (int) ($s["impressions"] ?? 0);
        $clk = (int) ($s["clicks"] ?? 0);
        $pos = (int) ($s["imp_for_pos"] ?? 0) > 0
            ? round((float) $s["position_weighted"] / (int) $s["imp_for_pos"], 2)
            : 0.0;

        return [
            "clicks" => $clk,
            "impressions" => $imp,
            "avg_position" => $pos,
            "ctr" => $imp > 0 ? round(($clk / $imp) * 100, 2) : 0.0,
        ];
    };

    $build_top_keywords = static function (array $agg_map, int $limit = 25): array {
        uasort($agg_map, static function ($a, $b) {
            return ((int) ($b["impressions"] ?? 0)) <=> ((int) ($a["impressions"] ?? 0));
        });
        $out = [];
        foreach (array_slice(array_values($agg_map), 0, $limit) as $k) {
            $out[] = [
                "keyword" => (string) ($k["keyword"] ?? ""),
                "clicks" => (int) ($k["clicks"] ?? 0),
                "impressions" => (int) ($k["impressions"] ?? 0),
                "position" => (int) ($k["imp"] ?? 0) > 0
                    ? round((float) $k["position_weighted"] / (int) $k["imp"], 2)
                    : 0.0,
            ];
        }

        return $out;
    };

    $top_keywords = $build_top_keywords($kw_agg, 25);
    $previous_top_keywords = $build_top_keywords($kw_agg_prev, 50);

    ksort($timeseries);

    $indexation = null;
    if (function_exists("ups_audit_gsc_resolve_indexation_for_sync")) {
        $client_website = "";
        $client_id_for_idx = (int) get_post_meta($resource_id, "_ups_resource_client_id", true);
        if ($client_id_for_idx > 0) {
            $client_website = trim((string) get_post_meta($client_id_for_idx, "_ups_client_website", true));
            if ($client_website === "") {
                $client_website = trim((string) get_post_meta($client_id_for_idx, "_ups_client_url", true));
            }
        }
        $indexation = ups_audit_gsc_resolve_indexation_for_sync($account_id, $site_url, $client_website);
    }

    $cache_payload = [
        "period_days" => $days,
        "error" => "",
        "summary" => $fmt($summary_cur),
        "previous_summary" => $fmt($summary_prev),
        "timeseries" => $timeseries,
        "keyword_daily" => $keyword_daily,
        "top_keywords" => $top_keywords,
        "previous_top_keywords" => $previous_top_keywords,
    ];
    if (is_array($indexation)) {
        $cache_payload["indexation"] = $indexation;
        $client_id = (int) get_post_meta($resource_id, "_ups_resource_client_id", true);
        if ($client_id > 0) {
            update_post_meta($client_id, "_ups_client_indexation_cache", $indexation);
        }
    }
    ups_audit_save_resource_cache($resource_id, $cache_payload);
}

function ups_audit_sync_ga4_resource(int $resource_id, int $days = 30, bool $full_history = false): void
{
    $resource_id = (int) $resource_id;
    $property_id = preg_replace("/\D+/", "", (string) get_post_meta($resource_id, "_ups_resource_external_id", true));
    $account_id = (int) get_post_meta($resource_id, "_ups_resource_google_account_id", true);
    $windows = ups_audit_period_windows($days);
    $fetch_days = function_exists("ups_audit_api_fetch_days")
        ? ups_audit_api_fetch_days("ga4", $full_history)
        : min(90, $days * 2 + 7);

    $client_id = (int) get_post_meta($resource_id, "_ups_resource_client_id", true);

    $prev_range = (array) ($windows["previous"] ?? []);
    $raw = ups_audit_with_account_oauth($account_id, static function ($oauth) use ($property_id, $fetch_days, $days, $resource_id, $client_id, $prev_range) {
        $ecom_prev = null;
        if (count($prev_range) === 2) {
            $ecom_prev = ups_audit_ga4_fetch_ecommerce_kpi(
                $property_id,
                $oauth,
                $days,
                (string) $prev_range[0],
                (string) $prev_range[1]
            );
        }

        return [
            "channels" => ups_audit_ga4_fetch($property_id, $oauth, $fetch_days),
            "events" => ups_audit_ga4_fetch_events($property_id, $oauth, $fetch_days, $resource_id, $client_id),
            "ecommerce_cur" => ups_audit_ga4_fetch_ecommerce_kpi($property_id, $oauth, $days),
            "ecommerce_prev" => $ecom_prev,
        ];
    });

    if (is_wp_error($raw)) {
        ups_audit_save_resource_cache($resource_id, [
            "period_days" => $days,
            "error" => $raw->get_error_message(),
            "summary" => ["sessions" => 0, "conversions" => 0, "conversions_all" => 0, "conversions_micro" => 0, "revenue" => 0.0],
            "previous_summary" => ["sessions" => 0, "conversions" => 0, "conversions_all" => 0, "revenue" => 0.0],
            "timeseries" => [],
            "channels" => [],
        ]);

        return;
    }

    $channel_raw = is_array($raw) && isset($raw["channels"]) ? $raw["channels"] : $raw;
    $events_raw = is_array($raw) && isset($raw["events"]) ? $raw["events"] : null;
    $ecommerce_cur_raw = is_array($raw) && isset($raw["ecommerce_cur"]) ? $raw["ecommerce_cur"] : null;
    $ecommerce_prev_raw = is_array($raw) && isset($raw["ecommerce_prev"]) ? $raw["ecommerce_prev"] : null;

    if (is_wp_error($channel_raw)) {
        ups_audit_save_resource_cache($resource_id, [
            "period_days" => $days,
            "error" => $channel_raw->get_error_message(),
            "summary" => ["sessions" => 0, "conversions" => 0, "conversions_all" => 0, "conversions_micro" => 0, "revenue" => 0.0],
            "previous_summary" => ["sessions" => 0, "conversions" => 0, "conversions_all" => 0, "revenue" => 0.0],
            "timeseries" => [],
            "channels" => [],
        ]);

        return;
    }

    if (is_array($channel_raw) && isset($channel_raw["error"]) && is_array($channel_raw["error"])) {
        $msg = sanitize_text_field((string) ($channel_raw["error"]["message"] ?? "Błąd GA4 Data API."));
        ups_audit_save_resource_cache($resource_id, [
            "period_days" => $days,
            "error" => $msg,
            "summary" => ["sessions" => 0, "conversions" => 0, "conversions_all" => 0, "conversions_micro" => 0, "revenue" => 0.0],
            "previous_summary" => ["sessions" => 0, "conversions" => 0, "conversions_all" => 0, "revenue" => 0.0],
            "timeseries" => [],
            "channels" => [],
        ]);

        return;
    }

    $api_rows = is_array($channel_raw) && isset($channel_raw["rows"]) && is_array($channel_raw["rows"]) ? $channel_raw["rows"] : [];
    $summary_cur = ["sessions" => 0, "conversions" => 0, "conversions_all" => 0, "conversions_micro" => 0, "conversions_engagement" => 0, "revenue" => 0.0];
    $summary_prev = ["sessions" => 0, "conversions" => 0, "conversions_all" => 0, "revenue" => 0.0];
    $timeseries = [];
    $channels = [];
    $channel_daily = [];
    $not_set_sessions = 0;

    foreach ($api_rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $dims = (array) ($row["dimensionValues"] ?? []);
        $mets = (array) ($row["metricValues"] ?? []);
        $source = sanitize_text_field((string) ($dims[0]["value"] ?? "(direct)"));
        $medium = sanitize_text_field((string) ($dims[1]["value"] ?? ""));
        $date_key = function_exists("ups_audit_normalize_date_key")
            ? ups_audit_normalize_date_key((string) ($dims[2]["value"] ?? ""))
            : sanitize_text_field((string) ($dims[2]["value"] ?? ""));
        $sessions = (int) round((float) ($mets[0]["value"] ?? 0));
        $conversions = (int) round((float) ($mets[1]["value"] ?? 0));
        $revenue = (float) ($mets[2]["value"] ?? 0);

        if ($date_key !== "") {
            if (!isset($timeseries[$date_key])) {
                $timeseries[$date_key] = ["sessions" => 0, "conversions" => 0, "revenue" => 0.0];
            }
            $timeseries[$date_key]["sessions"] += $sessions;
            $timeseries[$date_key]["conversions"] += $conversions;
            $timeseries[$date_key]["revenue"] += $revenue;
            $channel_daily[] = [
                "d" => $date_key,
                "src" => $source,
                "med" => $medium,
                "sess" => $sessions,
                "conv" => $conversions,
                "rev" => $revenue,
            ];
        }

        if (ups_audit_date_in_range($date_key, $windows["current"])) {
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
            $channels[$ch_key]["sessions"] += $sessions;
            $channels[$ch_key]["conversions"] += $conversions;
            $channels[$ch_key]["revenue"] += $revenue;
            $summary_cur["sessions"] += $sessions;
            $summary_cur["conversions_all"] += $conversions;
            $summary_cur["revenue"] += $revenue;
            if (function_exists("ups_audit_ga4_is_not_set_source") && ups_audit_ga4_is_not_set_source($source, $medium)) {
                $not_set_sessions += $sessions;
            }
        } elseif (ups_audit_date_in_range($date_key, $windows["previous"])) {
            $summary_prev["sessions"] += $sessions;
            $summary_prev["conversions_all"] += $conversions;
            $summary_prev["revenue"] += $revenue;
        }
    }

    $events_breakdown = [];
    $macro_total = 0;
    $micro_total = 0;
    $engagement_total = 0;
    if (is_array($events_raw) && !is_wp_error($events_raw) && !isset($events_raw["error"])) {
        $macro_total = (int) ($events_raw["macro_total"] ?? 0);
        $micro_total = (int) ($events_raw["micro_total"] ?? 0);
        $engagement_total = (int) ($events_raw["engagement_total"] ?? 0);
        $events_breakdown = (array) ($events_raw["breakdown"] ?? []);
    }

    $session_revenue_cur = (float) $summary_cur["revenue"];
    $session_revenue_prev = (float) $summary_prev["revenue"];
    $ecommerce_kpi = ["purchase_revenue" => 0.0, "purchase_count" => 0, "total_revenue" => 0.0];
    if (is_array($ecommerce_cur_raw) && !is_wp_error($ecommerce_cur_raw) && !isset($ecommerce_cur_raw["error"])) {
        $ecommerce_kpi = [
            "purchase_revenue" => (float) ($ecommerce_cur_raw["purchase_revenue"] ?? 0),
            "purchase_count" => (int) ($ecommerce_cur_raw["purchase_count"] ?? 0),
            "total_revenue" => (float) ($ecommerce_cur_raw["total_revenue"] ?? 0),
        ];
    }

    $kpi_conversions = function_exists("ups_audit_ga4_resolve_kpi_conversions")
        ? ups_audit_ga4_resolve_kpi_conversions($events_breakdown, $ecommerce_kpi)
        : 0;
    if ($kpi_conversions > 0) {
        $summary_cur["conversions"] = $kpi_conversions;
    } elseif ($macro_total > 0) {
        $summary_cur["conversions"] = $macro_total;
    } else {
        $summary_cur["conversions"] = (int) $summary_cur["conversions_all"];
    }

    $summary_cur["conversions_macro_legacy"] = $macro_total;
    $summary_cur["conversions_micro"] = $micro_total;
    $summary_cur["conversions_engagement"] = $engagement_total;
    $summary_cur["revenue_session_total"] = $session_revenue_cur;
    if ((float) ($ecommerce_kpi["purchase_revenue"] ?? 0) > 0) {
        $summary_cur["revenue"] = (float) $ecommerce_kpi["purchase_revenue"];
        $summary_cur["revenue_source"] = "purchaseRevenue";
        $summary_cur["purchase_count"] = (int) ($ecommerce_kpi["purchase_count"] ?? 0);
    } else {
        $summary_cur["revenue_source"] = "sessionTotalRevenue";
    }

    if (is_array($ecommerce_prev_raw) && !is_wp_error($ecommerce_prev_raw) && !isset($ecommerce_prev_raw["error"])) {
        $prev_purchase_rev = (float) ($ecommerce_prev_raw["purchase_revenue"] ?? 0);
        if ($prev_purchase_rev > 0) {
            $summary_prev["revenue"] = $prev_purchase_rev;
            $summary_prev["revenue_source"] = "purchaseRevenue";
            $summary_prev["purchase_count"] = (int) ($ecommerce_prev_raw["purchase_count"] ?? 0);
        }
    }
    $summary_prev["revenue_session_total"] = $session_revenue_prev;
    $summary_prev["conversions"] = (int) $summary_prev["conversions_all"];

    $not_set_pct = $summary_cur["sessions"] > 0
        ? round(($not_set_sessions / $summary_cur["sessions"]) * 100, 1)
        : 0.0;

    uasort($channels, static function ($a, $b) {
        return ((int) ($b["sessions"] ?? 0)) <=> ((int) ($a["sessions"] ?? 0));
    });
    ksort($timeseries);

    $quality_notes = [];
    if ($macro_total > 0 && $kpi_conversions > 0 && $macro_total > $kpi_conversions * 1.5) {
        $quality_notes[] = sprintf(
            __("KPI konwersji: %d (purchase/lead) — nie sumujemy %d wszystkich makro-eventów.", "upsellio"),
            $kpi_conversions,
            $macro_total
        );
    }
    if ($macro_total > 0 && (int) $summary_cur["conversions_all"] > $macro_total * 2) {
        $quality_notes[] = __("GA4: metryka „conversions” (sesje) zawiera zdarzenia oznaczone jako konwersje w GA4.", "upsellio");
    }
    if (function_exists("ups_audit_ga4_build_revenue_quality_notes")) {
        foreach (ups_audit_ga4_build_revenue_quality_notes(
            $events_breakdown,
            $ecommerce_kpi,
            (int) $summary_cur["sessions"],
            (int) $summary_cur["conversions"],
            $session_revenue_cur
        ) as $rnote) {
            if (!in_array($rnote, $quality_notes, true)) {
                $quality_notes[] = $rnote;
            }
        }
    }
    if ($not_set_pct >= 25) {
        $quality_notes[] = sprintf(
            /* translators: %s: percent */
            __("Atrybucja: %.1f%% sesji ma (not set) — napraw UTM/GTM.", "upsellio"),
            $not_set_pct
        );
    }

    ups_audit_save_resource_cache($resource_id, [
        "period_days" => $days,
        "error" => "",
        "summary" => $summary_cur,
        "previous_summary" => $summary_prev,
        "timeseries" => $timeseries,
        "channel_daily" => $channel_daily,
        "channels" => array_slice(array_values($channels), 0, 20),
        "events_breakdown" => $events_breakdown,
        "ecommerce_kpi" => $ecommerce_kpi,
        "attribution" => [
            "not_set_sessions" => $not_set_sessions,
            "not_set_pct" => $not_set_pct,
        ],
        "data_quality_notes" => $quality_notes,
    ]);
}

function ups_audit_sync_ads_resource(int $resource_id, int $days = 30, bool $full_history = false): void
{
    $resource_id = (int) $resource_id;
    $customer_id = preg_replace("/\D+/", "", (string) get_post_meta($resource_id, "_ups_resource_external_id", true));
    $account_id = (int) get_post_meta($resource_id, "_ups_resource_google_account_id", true);

    if (!ups_audit_ads_api_configured()) {
        ups_audit_save_resource_cache($resource_id, [
            "period_days" => $days,
            "error" => __("Uzupełnij Developer token Google Ads w Analityce SEO.", "upsellio"),
            "summary" => ["cost" => 0.0, "clicks" => 0, "conversions" => 0.0, "impressions" => 0],
            "previous_summary" => ["cost" => 0.0, "clicks" => 0, "conversions" => 0.0, "impressions" => 0],
            "timeseries" => [],
            "campaigns" => [],
        ]);

        return;
    }

    $cfg = function_exists("upsellio_google_ads_get_settings") ? upsellio_google_ads_get_settings() : [];
    if ($customer_id !== "" && trim((string) ($cfg["customer_id"] ?? "")) === "") {
        $cfg["customer_id"] = $customer_id;
        update_option(
            function_exists("upsellio_google_ads_config_option_key") ? upsellio_google_ads_config_option_key() : "upsellio_google_ads_config",
            $cfg,
            false
        );
    }

    $windows = ups_audit_period_windows($days);
    $fetch_days = function_exists("ups_audit_api_fetch_days")
        ? ups_audit_api_fetch_days("ads", $full_history)
        : min(90, $days * 2 + 7);

    $fetch = ups_audit_with_account_oauth($account_id, static function () use ($days, $fetch_days, $customer_id) {
        if (!function_exists("upsellio_google_ads_fetch_campaigns")) {
            return new WP_Error("ads_missing", "Brak modułu Google Ads.");
        }
        $cfg_key = function_exists("upsellio_google_ads_config_option_key")
            ? upsellio_google_ads_config_option_key()
            : "upsellio_google_ads_config";
        $cfg = upsellio_google_ads_get_settings();
        if ($customer_id !== "") {
            $cfg["customer_id"] = $customer_id;
            update_option($cfg_key, $cfg, false);
        }
        $out = [
            "campaigns" => upsellio_google_ads_fetch_campaigns($fetch_days),
            "daily" => function_exists("upsellio_google_ads_fetch_daily_metrics")
                ? upsellio_google_ads_fetch_daily_metrics($fetch_days)
                : [],
            "search_terms" => function_exists("upsellio_google_ads_fetch_search_terms")
                ? upsellio_google_ads_fetch_search_terms($fetch_days)
                : [],
            "campaign_daily" => function_exists("upsellio_google_ads_fetch_campaign_daily_rows")
                ? upsellio_google_ads_fetch_campaign_daily_rows($fetch_days)
                : [],
            "search_term_daily" => function_exists("upsellio_google_ads_fetch_search_term_daily_rows")
                ? upsellio_google_ads_fetch_search_term_daily_rows($fetch_days)
                : [],
        ];

        return $out;
    });

    if (is_wp_error($fetch)) {
        ups_audit_save_resource_cache($resource_id, [
            "period_days" => $days,
            "error" => $fetch->get_error_message(),
            "summary" => ["cost" => 0.0, "clicks" => 0, "conversions" => 0.0, "impressions" => 0],
            "previous_summary" => ["cost" => 0.0, "clicks" => 0, "conversions" => 0.0, "impressions" => 0],
            "timeseries" => [],
            "campaigns" => [],
        ]);

        return;
    }

    $campaigns = is_array($fetch) && isset($fetch["campaigns"]) ? $fetch["campaigns"] : [];
    $daily = is_array($fetch) && isset($fetch["daily"]) ? $fetch["daily"] : [];
    $search_terms = is_array($fetch) && isset($fetch["search_terms"]) ? $fetch["search_terms"] : [];
    $campaign_daily = is_array($fetch) && isset($fetch["campaign_daily"]) ? $fetch["campaign_daily"] : [];
    $search_term_daily = is_array($fetch) && isset($fetch["search_term_daily"]) ? $fetch["search_term_daily"] : [];
    if (is_wp_error($campaigns)) {
        ups_audit_save_resource_cache($resource_id, [
            "period_days" => $days,
            "error" => $campaigns->get_error_message(),
            "summary" => ["cost" => 0.0, "clicks" => 0, "conversions" => 0.0, "impressions" => 0],
            "previous_summary" => ["cost" => 0.0, "clicks" => 0, "conversions" => 0.0, "impressions" => 0],
            "timeseries" => [],
            "campaigns" => [],
        ]);

        return;
    }
    if (is_wp_error($daily)) {
        $daily = [];
    }
    if (is_wp_error($search_terms)) {
        $search_terms = [];
    }

    $summary_cur = ["cost" => 0.0, "clicks" => 0, "conversions" => 0.0, "impressions" => 0];
    $summary_prev = ["cost" => 0.0, "clicks" => 0, "conversions" => 0.0, "impressions" => 0];
    $timeseries = [];
    $camp_agg = [];

    foreach ((array) $daily as $date_key => $day) {
        if (!is_array($day)) {
            continue;
        }
        $cost = (float) ($day["cost"] ?? 0);
        $clicks = (int) ($day["clicks"] ?? 0);
        $impressions = (int) ($day["impressions"] ?? 0);
        $conversions = (float) ($day["conversions"] ?? 0);
        $timeseries[$date_key] = [
            "cost" => $cost,
            "clicks" => $clicks,
            "impressions" => $impressions,
            "conversions" => $conversions,
        ];
        if (ups_audit_date_in_range((string) $date_key, $windows["current"])) {
            $summary_cur["cost"] += $cost;
            $summary_cur["clicks"] += $clicks;
            $summary_cur["impressions"] += $impressions;
            $summary_cur["conversions"] += $conversions;
        } elseif (ups_audit_date_in_range((string) $date_key, $windows["previous"])) {
            $summary_prev["cost"] += $cost;
            $summary_prev["clicks"] += $clicks;
            $summary_prev["impressions"] += $impressions;
            $summary_prev["conversions"] += $conversions;
        }
    }

    foreach ((array) $campaigns as $c) {
        if (!is_array($c)) {
            continue;
        }
        $id = (string) ($c["id"] ?? "");
        $name = (string) ($c["name"] ?? $id);
        if ($id === "") {
            continue;
        }
        if (!isset($camp_agg[$id])) {
            $cost = (float) ($c["cost_pln"] ?? $c["cost"] ?? 0);
            $conv = (float) ($c["conversions"] ?? 0);
            $camp_agg[$id] = [
                "id" => $id,
                "name" => $name,
                "type" => (string) ($c["type"] ?? ""),
                "cost" => $cost,
                "clicks" => (int) ($c["clicks"] ?? 0),
                "impressions" => (int) ($c["impressions"] ?? 0),
                "conversions" => $conv,
                "cpa" => (float) ($c["cpa_pln"] ?? ($conv > 0 ? round($cost / $conv, 2) : 0)),
            ];
        }
    }

    if ($summary_cur["cost"] <= 0 && !empty($camp_agg)) {
        foreach ($camp_agg as $camp) {
            $summary_cur["cost"] += (float) ($camp["cost"] ?? 0);
            $summary_cur["clicks"] += (int) ($camp["clicks"] ?? 0);
            $summary_cur["impressions"] += (int) ($camp["impressions"] ?? 0);
            $summary_cur["conversions"] += (float) ($camp["conversions"] ?? 0);
        }
    }

    uasort($camp_agg, static function ($a, $b) {
        return ((float) ($b["cost"] ?? 0)) <=> ((float) ($a["cost"] ?? 0));
    });
    ksort($timeseries);

    if (is_wp_error($campaign_daily)) {
        $campaign_daily = [];
    }
    if (is_wp_error($search_term_daily)) {
        $search_term_daily = [];
    }

    $st_rows = is_array($search_terms) ? array_slice($search_terms, 0, 250) : [];
    $client_id = (int) get_post_meta($resource_id, "_ups_resource_client_id", true);
    if ($client_id > 0 && $st_rows !== [] && function_exists("ups_audit_update_search_term_aging")) {
        ups_audit_update_search_term_aging($client_id, $st_rows);
    }

    ups_audit_save_resource_cache($resource_id, [
        "period_days" => $days,
        "error" => "",
        "summary" => $summary_cur,
        "previous_summary" => $summary_prev,
        "timeseries" => $timeseries,
        "campaign_daily" => is_array($campaign_daily) ? $campaign_daily : [],
        "search_term_daily" => is_array($search_term_daily) ? $search_term_daily : [],
        "campaigns" => array_slice(array_values($camp_agg), 0, 30),
        "search_terms" => $st_rows,
        "fetch_days" => $fetch_days,
    ]);
}

function ups_audit_sync_meta_resource(int $resource_id, int $days = 30, bool $full_history = false): void
{
    $resource_id = (int) $resource_id;
    $ad_account_id = upsellio_meta_ads_normalize_ad_account_id(
        (string) get_post_meta($resource_id, "_ups_resource_external_id", true)
    );
    $account_id = (int) get_post_meta($resource_id, "_ups_resource_meta_account_id", true);

    if (!ups_audit_meta_api_configured()) {
        ups_audit_save_resource_cache($resource_id, [
            "period_days" => $days,
            "error" => __("Uzupełnij App ID i App Secret Meta w CRM → Konta Meta.", "upsellio"),
            "summary" => ["cost" => 0.0, "clicks" => 0, "conversions" => 0.0, "impressions" => 0],
            "previous_summary" => ["cost" => 0.0, "clicks" => 0, "conversions" => 0.0, "impressions" => 0],
            "timeseries" => [],
            "campaigns" => [],
        ]);

        return;
    }

    $windows = ups_audit_period_windows($days);
    $fetch_days = function_exists("ups_audit_api_fetch_days")
        ? ups_audit_api_fetch_days("meta", $full_history)
        : min(90, $days * 2 + 7);
    $until = gmdate("Y-m-d", strtotime("-1 day"));
    $since = gmdate("Y-m-d", strtotime("-" . (int) $fetch_days . " days"));

    $fetch = ups_audit_with_meta_account_oauth($account_id, static function ($oauth) use ($ad_account_id, $since, $until) {
        $token = trim((string) ($oauth["access_token"] ?? ""));
        if ($token === "") {
            return new WP_Error("meta_token", __("Brak tokena Meta.", "upsellio"));
        }

        return [
            "daily" => upsellio_meta_ads_fetch_daily_insights($ad_account_id, $token, $since, $until),
            "campaigns" => upsellio_meta_ads_fetch_campaign_insights($ad_account_id, $token, $since, $until),
        ];
    });

    if (is_wp_error($fetch)) {
        ups_audit_save_resource_cache($resource_id, [
            "period_days" => $days,
            "error" => $fetch->get_error_message(),
            "summary" => ["cost" => 0.0, "clicks" => 0, "conversions" => 0.0, "impressions" => 0],
            "previous_summary" => ["cost" => 0.0, "clicks" => 0, "conversions" => 0.0, "impressions" => 0],
            "timeseries" => [],
            "campaigns" => [],
        ]);

        return;
    }

    $daily = is_array($fetch) && isset($fetch["daily"]) ? $fetch["daily"] : [];
    $campaigns = is_array($fetch) && isset($fetch["campaigns"]) ? $fetch["campaigns"] : [];
    if (is_wp_error($daily)) {
        ups_audit_save_resource_cache($resource_id, [
            "period_days" => $days,
            "error" => $daily->get_error_message(),
            "summary" => ["cost" => 0.0, "clicks" => 0, "conversions" => 0.0, "impressions" => 0],
            "previous_summary" => ["cost" => 0.0, "clicks" => 0, "conversions" => 0.0, "impressions" => 0],
            "timeseries" => [],
            "campaigns" => [],
        ]);

        return;
    }
    if (is_wp_error($campaigns)) {
        $campaigns = [];
    }

    $summary_cur = ["cost" => 0.0, "clicks" => 0, "conversions" => 0.0, "impressions" => 0];
    $summary_prev = ["cost" => 0.0, "clicks" => 0, "conversions" => 0.0, "impressions" => 0];
    $timeseries = [];
    $camp_agg = [];

    foreach ((array) $daily as $date_key => $day) {
        if (!is_array($day)) {
            continue;
        }
        $cost = (float) ($day["cost"] ?? 0);
        $clicks = (int) ($day["clicks"] ?? 0);
        $impressions = (int) ($day["impressions"] ?? 0);
        $conversions = (float) ($day["conversions"] ?? 0);
        $timeseries[$date_key] = [
            "cost" => $cost,
            "clicks" => $clicks,
            "impressions" => $impressions,
            "conversions" => $conversions,
        ];
        if (ups_audit_date_in_range((string) $date_key, $windows["current"])) {
            $summary_cur["cost"] += $cost;
            $summary_cur["clicks"] += $clicks;
            $summary_cur["impressions"] += $impressions;
            $summary_cur["conversions"] += $conversions;
        } elseif (ups_audit_date_in_range((string) $date_key, $windows["previous"])) {
            $summary_prev["cost"] += $cost;
            $summary_prev["clicks"] += $clicks;
            $summary_prev["impressions"] += $impressions;
            $summary_prev["conversions"] += $conversions;
        }
    }

    foreach ((array) $campaigns as $c) {
        if (!is_array($c)) {
            continue;
        }
        $id = (string) ($c["id"] ?? "");
        if ($id === "") {
            continue;
        }
        $camp_agg[$id] = [
            "id" => $id,
            "name" => (string) ($c["name"] ?? $id),
            "cost" => (float) ($c["cost"] ?? 0),
            "clicks" => (int) ($c["clicks"] ?? 0),
            "impressions" => (int) ($c["impressions"] ?? 0),
            "conversions" => (float) ($c["conversions"] ?? 0),
        ];
    }

    if ($summary_cur["cost"] <= 0 && !empty($camp_agg)) {
        foreach ($camp_agg as $camp) {
            $summary_cur["cost"] += (float) ($camp["cost"] ?? 0);
            $summary_cur["clicks"] += (int) ($camp["clicks"] ?? 0);
            $summary_cur["impressions"] += (int) ($camp["impressions"] ?? 0);
            $summary_cur["conversions"] += (float) ($camp["conversions"] ?? 0);
        }
    }

    uasort($camp_agg, static function ($a, $b) {
        return ((float) ($b["cost"] ?? 0)) <=> ((float) ($a["cost"] ?? 0));
    });
    ksort($timeseries);

    ups_audit_save_resource_cache($resource_id, [
        "period_days" => $days,
        "error" => "",
        "summary" => $summary_cur,
        "previous_summary" => $summary_prev,
        "timeseries" => $timeseries,
        "campaigns" => array_slice(array_values($camp_agg), 0, 30),
    ]);
}

function ups_audit_sync_clarity_resource(int $resource_id, int $days = 30, bool $full_history = false): void
{
    $resource_id = (int) $resource_id;
    unset($days, $full_history);

    if (!function_exists("ups_audit_clarity_get_token") || !function_exists("ups_audit_clarity_fetch_live_insights")) {
        ups_audit_save_resource_cache($resource_id, [
            "period_days" => 3,
            "error" => __("Moduł Clarity niedostępny.", "upsellio"),
            "summary" => [],
            "previous_summary" => [],
            "by_dimension" => [],
        ]);

        return;
    }

    $token = ups_audit_clarity_get_token($resource_id);
    if ($token === "") {
        ups_audit_save_resource_cache($resource_id, [
            "period_days" => 3,
            "error" => __("Brak tokena API Clarity — dodaj przy imporcie projektu.", "upsellio"),
            "summary" => [],
            "previous_summary" => [],
            "by_dimension" => [],
        ]);

        return;
    }

    if (!ups_audit_clarity_can_request($resource_id)) {
        $prev = get_post_meta($resource_id, "_ups_resource_data_cache", true);
        if (!is_array($prev) || empty($prev["summary"])) {
            $prev = get_post_meta($resource_id, "_ups_resource_data_cache_previous", true);
        }
        $has_sessions = is_array($prev)
            && is_array($prev["summary"] ?? null)
            && (int) ($prev["summary"]["sessions"] ?? 0) > 0;
        if ($has_sessions) {
            $prev["error"] = __("Clarity: dzienny limit API (10/dzień) — użyto cache.", "upsellio");
            ups_audit_save_resource_cache($resource_id, $prev);

            return;
        }
        ups_audit_save_resource_cache($resource_id, [
            "period_days" => 3,
            "error" => __("Clarity: limit 10 zapytań API na dzień.", "upsellio"),
            "summary" => [],
            "previous_summary" => [],
            "by_dimension" => [],
        ]);

        return;
    }

    $prev_cache = get_post_meta($resource_id, "_ups_resource_data_cache", true);
    $prev_summary = is_array($prev_cache) && is_array($prev_cache["summary"] ?? null)
        ? $prev_cache["summary"]
        : [];

    $merged = function_exists("ups_audit_clarity_fetch_full_snapshot")
        ? ups_audit_clarity_fetch_full_snapshot($token, $resource_id, 5)
        : null;

    if (is_wp_error($merged)) {
        ups_audit_save_resource_cache($resource_id, [
            "period_days" => 3,
            "error" => $merged->get_error_message(),
            "summary" => $prev_summary,
            "previous_summary" => [],
            "by_dimension" => is_array($prev_summary["by_dimension"] ?? null) ? $prev_summary["by_dimension"] : [],
        ]);

        return;
    }

    if (!is_array($merged)) {
        $raw = ups_audit_clarity_fetch_live_insights($token, 3, "Device");
        if (!is_wp_error($raw)) {
            ups_audit_clarity_track_request($resource_id);
        }
        if (is_wp_error($raw)) {
            ups_audit_save_resource_cache($resource_id, [
                "period_days" => 3,
                "error" => $raw->get_error_message(),
                "summary" => $prev_summary,
                "previous_summary" => [],
                "by_dimension" => [],
            ]);

            return;
        }
        $summary = ups_audit_clarity_parse_insights($raw, "Device");
        $api_requests = 1;
    } else {
        $summary = (array) ($merged["summary"] ?? []);
        $api_requests = (int) ($merged["requests"] ?? 0);
    }

    $has_data = (int) ($summary["sessions"] ?? 0) > 0
        || (int) ($summary["users"] ?? 0) > 0
        || (int) ($summary["dead_clicks"] ?? 0) > 0
        || !empty($summary["top_pages"])
        || !empty($summary["by_source"]);

    ups_audit_save_resource_cache($resource_id, [
        "period_days" => 3,
        "error" => $has_data ? "" : __("Clarity: brak sesji w ostatnich 3 dniach API (sprawdź skrypt na stronie lub token).", "upsellio"),
        "summary" => $summary,
        "previous_summary" => $has_data ? $prev_summary : [],
        "by_dimension" => array_values((array) ($summary["by_dimension"] ?? [])),
        "top_pages" => (array) ($summary["top_pages"] ?? []),
        "top_referrers" => (array) ($summary["top_referrers"] ?? []),
        "by_source" => (array) ($summary["by_source"] ?? []),
        "by_country" => (array) ($summary["by_country"] ?? []),
        "by_channel" => (array) ($summary["by_channel"] ?? []),
        "by_os" => (array) ($summary["by_os"] ?? []),
        "api_num_days" => 3,
        "api_requests" => $api_requests,
        "api_plan" => is_array($merged) ? (array) ($merged["plan"] ?? []) : [],
        "api_usage_today" => ups_audit_clarity_daily_usage($resource_id),
        "fetched_at" => current_time("mysql"),
    ]);
}

function ups_audit_sync_resource_action(int $resource_id, int $days = 0, bool $full_history = false): void
{
    $resource_id = (int) $resource_id;
    if ($days <= 0) {
        $days = (int) get_option("ups_audit_default_compare_window", 30);
    }
    $days = in_array($days, [7, 14, 30, 60, 90], true) ? $days : 30;
    $type = (string) get_post_meta($resource_id, "_ups_resource_type", true);
    if ($type === "ga4") {
        ups_audit_sync_ga4_resource($resource_id, $days, $full_history);
    } elseif ($type === "gsc") {
        ups_audit_sync_gsc_resource($resource_id, $days, $full_history);
    } elseif ($type === "ads") {
        ups_audit_sync_ads_resource($resource_id, $days, $full_history);
    } elseif ($type === "meta") {
        ups_audit_sync_meta_resource($resource_id, $days, $full_history);
    } elseif ($type === "clarity") {
        ups_audit_sync_clarity_resource($resource_id, $days, $full_history);
    }
}

function ups_audit_sync_resources_list(array $resources, int $days = 0, bool $full_history = false): array
{
    $ok = 0;
    $fail = 0;
    foreach ($resources as $r) {
        if (!($r instanceof WP_Post)) {
            continue;
        }
        try {
            ups_audit_sync_resource_action((int) $r->ID, $days, $full_history);
            $cache = get_post_meta((int) $r->ID, "_ups_resource_data_cache", true);
            $err = is_array($cache) ? trim((string) ($cache["error"] ?? "")) : "";
            if ($err !== "") {
                $fail++;
            } else {
                $ok++;
            }
        } catch (Throwable $e) {
            $fail++;
            if (function_exists("upsellio_gsc_log")) {
                upsellio_gsc_log("audit.sync.error", ["resource_id" => (int) $r->ID, "msg" => $e->getMessage()]);
            }
        }
        if (!$full_history) {
            sleep(1);
        } else {
            usleep(500000);
        }
    }

    return ["ok" => $ok, "fail" => $fail, "total" => $ok + $fail];
}

function ups_audit_sync_google_account_resources(int $google_account_id, int $days = 0, bool $full_history = false): array
{
    $google_account_id = (int) $google_account_id;
    if ($google_account_id <= 0) {
        return ["ok" => 0, "fail" => 0, "total" => 0];
    }

    return ups_audit_sync_resources_list(
        function_exists("ups_audit_get_google_account_resources")
            ? ups_audit_get_google_account_resources($google_account_id)
            : [],
        $days,
        $full_history
    );
}

function ups_audit_sync_all_google_account_resources(int $days = 0, bool $full_history = false): array
{
    $accounts = get_posts([
        "post_type" => "crm_google_account",
        "posts_per_page" => -1,
        "post_status" => ["publish", "draft"],
    ]);
    $ok = 0;
    $fail = 0;
    foreach ($accounts as $acc) {
        if (!($acc instanceof WP_Post)) {
            continue;
        }
        $batch = ups_audit_sync_google_account_resources((int) $acc->ID, $days, $full_history);
        $ok += (int) ($batch["ok"] ?? 0);
        $fail += (int) ($batch["fail"] ?? 0);
    }

    return ["ok" => $ok, "fail" => $fail, "total" => $ok + $fail];
}

function ups_audit_sync_all_mapped_resources(int $days = 0, bool $full_history = false): array
{
    $resources = get_posts([
        "post_type" => "crm_audit_resource",
        "posts_per_page" => -1,
        "post_status" => ["publish", "draft"],
        "meta_query" => [[
            "key" => "_ups_resource_client_id",
            "value" => 0,
            "compare" => ">",
            "type" => "NUMERIC",
        ]],
    ]);

    return ups_audit_sync_resources_list($resources, $days, $full_history);
}

/**
 * Agregacja KPI z listy zasobów audytowych (cache).
 *
 * @param array<int, WP_Post> $resources
 * @param array<string, mixed> $setup
 * @return array<string, mixed>
 */
function ups_audit_aggregate_resources_data(array $resources, int $days = 30, int $offset = 0, bool $with_extras = true, array $setup = [], int $extras_client_id = 0): array
{
    $agg = [
        "ga4_sessions" => 0,
        "ga4_sessions_prev" => 0,
        "ga4_conversions" => 0,
        "ga4_conversions_prev" => 0,
        "ga4_conversions_all" => 0,
        "ga4_conversions_micro" => 0,
        "ga4_conversions_engagement" => 0,
        "ga4_not_set_sessions" => 0,
        "ga4_not_set_pct" => 0.0,
        "ga4_events_breakdown" => [],
        "ga4_events_diagnostics" => [],
        "ga4_purchase_count" => 0,
        "ga4_revenue_session_total" => 0.0,
        "ga4_revenue_source" => "",
        "data_quality_notes" => [],
        "ga4_revenue" => 0.0,
        "ga4_revenue_prev" => 0.0,
        "gsc_clicks" => 0,
        "gsc_clicks_prev" => 0,
        "gsc_impressions" => 0,
        "gsc_impressions_prev" => 0,
        "gsc_avg_position" => 0.0,
        "gsc_ctr" => 0.0,
        "ads_cost" => 0.0,
        "ads_cost_prev" => 0.0,
        "ads_clicks" => 0,
        "ads_clicks_prev" => 0,
        "ads_conversions" => 0.0,
        "ads_conversions_prev" => 0.0,
        "meta_cost" => 0.0,
        "meta_cost_prev" => 0.0,
        "meta_clicks" => 0,
        "meta_clicks_prev" => 0,
        "meta_conversions" => 0.0,
        "meta_conversions_prev" => 0.0,
        "meta_roas" => 0.0,
        "meta_roas_prev" => 0.0,
        "paid_cost" => 0.0,
        "paid_cost_prev" => 0.0,
        "roas" => 0.0,
        "roas_prev" => 0.0,
        "resources" => [],
        "top_keywords" => [],
        "previous_top_keywords" => [],
        "keyword_position_changes" => [],
        "channels" => [],
        "campaigns" => [],
        "search_terms" => [],
        "period_days" => $days,
        "timeseries" => ["gsc_clicks" => [], "ga4_sessions" => [], "ads_cost" => [], "ads_clicks" => [], "meta_cost" => []],
        "meta_campaigns" => [],
        "clarity_sessions" => 0,
        "clarity_users" => 0,
        "clarity_dead_clicks" => 0,
        "clarity_rage_clicks" => 0,
        "clarity_quickback" => 0,
        "clarity_script_errors" => 0,
        "clarity_engagement_sec" => 0.0,
        "clarity_scroll_depth" => 0.0,
        "clarity_pages_per_session" => 0.0,
        "clarity_bot_sessions" => 0,
        "clarity_by_device" => [],
        "clarity_top_pages" => [],
        "clarity_top_referrers" => [],
        "clarity_by_source" => [],
        "clarity_by_country" => [],
        "clarity_by_channel" => [],
        "clarity_window_days" => 3,
        "clarity_mapped" => (int) ($setup["clarity"] ?? 0),
        "clarity_errors" => [],
        "clarity_resources" => [],
        "deltas" => [],
        "window_slice_notes" => [],
    ];
    $gsc_pos_weight = 0;
    $kw_merge = [];
    $kw_prev_merge = [];
    $ch_merge = [];
    $camp_merge = [];
    $meta_camp_merge = [];
    $st_merge = [];
    $ts_gsc = [];
    $ts_ga4 = [];
    $ts_ads = [];
    $ts_ads_clicks = [];
    $ts_meta = [];
    $chart_windows = ups_audit_period_windows($days);

    foreach ($resources as $r) {
        if (!($r instanceof WP_Post)) {
            continue;
        }
        $rid = (int) $r->ID;
        $type = (string) get_post_meta($rid, "_ups_resource_type", true);
        $cache = get_post_meta($rid, "_ups_resource_data_cache", true);
        if (!is_array($cache)) {
            continue;
        }
        $summary = ups_audit_cache_summary($cache);
        $prev_summary = is_array($cache["previous_summary"] ?? null) ? $cache["previous_summary"] : [];
        $cache_period_days = (int) ($cache["period_days"] ?? 0);
        $window_totals = function_exists("ups_audit_sum_cache_window_metrics")
            ? ups_audit_sum_cache_window_metrics($cache, $chart_windows)
            : ["current" => [], "previous" => []];
        $use_ts_totals = ups_audit_cache_timeseries($cache) !== [];
        $slice_ok = function_exists("ups_audit_cache_supports_window_slice")
            && ups_audit_cache_supports_window_slice($cache);
        if ($cache_period_days !== $days && !$slice_ok) {
            $agg["window_slice_notes"][] = sprintf(
                "%s: sync był na %d dni — uruchom Sync danych dla okresu %d dni.",
                (string) ($r->post_title ?? $type),
                $cache_period_days > 0 ? $cache_period_days : 30,
                $days
            );
        }
        $health = ups_audit_resource_health($rid);
        $agg["resources"][] = [
            "id" => $rid,
            "title" => (string) $r->post_title,
            "type" => $type,
            "external_id" => (string) get_post_meta($rid, "_ups_resource_external_id", true),
            "health" => $health,
            "last_sync" => (string) get_post_meta($rid, "_ups_resource_last_data_sync", true),
        ];

        if ($type === "ga4") {
            $purchase_rev_base = (float) ($summary["revenue"] ?? 0);
            $session_rev_base = (float) ($summary["revenue_session_total"] ?? $purchase_rev_base);
            $rev_source = (string) ($summary["revenue_source"] ?? "");
            $purchase_ratio = ($rev_source === "purchaseRevenue" && $session_rev_base > 0 && $purchase_rev_base > 0)
                ? min(1.0, $purchase_rev_base / $session_rev_base)
                : 1.0;

            if ($use_ts_totals) {
                $cur_ts = (array) ($window_totals["current"] ?? []);
                $prev_ts = (array) ($window_totals["previous"] ?? []);
                $agg["ga4_sessions"] += (int) round((float) ($cur_ts["sessions"] ?? 0));
                $agg["ga4_sessions_prev"] += (int) round((float) ($prev_ts["sessions"] ?? 0));
                $conv_cur = (int) round((float) ($cur_ts["conversions"] ?? 0));
                $conv_prev = (int) round((float) ($prev_ts["conversions"] ?? 0));
                $agg["ga4_conversions_all"] += $conv_cur;
                $ts_rev_cur = (float) ($cur_ts["revenue"] ?? 0);
                $ts_rev_prev = (float) ($prev_ts["revenue"] ?? 0);
                if ($cache_period_days === $days && (int) ($summary["conversions"] ?? 0) > 0) {
                    $agg["ga4_conversions"] += (int) ($summary["conversions"] ?? 0);
                    $agg["ga4_conversions_prev"] += (int) ($prev_summary["conversions"] ?? 0);
                    $agg["ga4_revenue"] += $purchase_rev_base;
                    $agg["ga4_revenue_prev"] += (float) ($prev_summary["revenue"] ?? 0);
                } else {
                    $agg["ga4_conversions"] += $conv_cur;
                    $agg["ga4_conversions_prev"] += $conv_prev;
                    $agg["ga4_revenue"] += round($ts_rev_cur * $purchase_ratio, 2);
                    $agg["ga4_revenue_prev"] += round($ts_rev_prev * $purchase_ratio, 2);
                }
            } else {
                $agg["ga4_sessions"] += (int) ($summary["sessions"] ?? 0);
                $agg["ga4_sessions_prev"] += (int) ($prev_summary["sessions"] ?? 0);
                $agg["ga4_conversions"] += (int) ($summary["conversions"] ?? 0);
                $agg["ga4_conversions_prev"] += (int) ($prev_summary["conversions"] ?? 0);
                $agg["ga4_conversions_all"] += (int) ($summary["conversions_all"] ?? $summary["conversions"] ?? 0);
                $agg["ga4_revenue"] += (float) ($summary["revenue"] ?? 0);
                $agg["ga4_revenue_prev"] += (float) ($prev_summary["revenue"] ?? 0);
            }
            $agg["ga4_purchase_count"] += (int) ($summary["purchase_count"] ?? 0);
            $agg["ga4_revenue_session_total"] += $session_rev_base;
            if ($rev_source !== "" && $agg["ga4_revenue_source"] === "") {
                $agg["ga4_revenue_source"] = $rev_source;
            }
            $agg["ga4_conversions_micro"] += (int) ($summary["conversions_micro"] ?? 0);
            $agg["ga4_conversions_engagement"] += (int) ($summary["conversions_engagement"] ?? 0);
            $attr = function_exists("ups_audit_ga4_attribution_for_window")
                ? ups_audit_ga4_attribution_for_window($cache, $chart_windows)
                : (is_array($cache["attribution"] ?? null) ? $cache["attribution"] : []);
            $agg["ga4_not_set_sessions"] += (int) ($attr["not_set_sessions"] ?? 0);
            if (!empty($cache["events_breakdown"])) {
                $agg["ga4_events_breakdown"] = (array) $cache["events_breakdown"];
                $diag_priority = ["purchase", "begin_checkout", "add_to_cart", "form_submit", "form_start", "view_item", "generate_lead", "lead"];
                $diag_map = [];
                foreach ((array) $cache["events_breakdown"] as $evrow) {
                    if (!is_array($evrow)) {
                        continue;
                    }
                    $ename = strtolower((string) ($evrow["event"] ?? ""));
                    if ($ename !== "") {
                        $diag_map[$ename] = $evrow;
                    }
                }
                foreach ($diag_priority as $dp) {
                    if (isset($diag_map[$dp])) {
                        $agg["ga4_events_diagnostics"][] = $diag_map[$dp];
                        unset($diag_map[$dp]);
                    }
                }
                foreach (array_slice(array_values($diag_map), 0, 12) as $extra) {
                    $agg["ga4_events_diagnostics"][] = $extra;
                }
            }
            foreach ((array) ($cache["data_quality_notes"] ?? []) as $note) {
                if (is_string($note) && $note !== "" && !in_array($note, $agg["data_quality_notes"], true)) {
                    $agg["data_quality_notes"][] = $note;
                }
            }
            $ga4_channels = function_exists("ups_audit_ga4_channels_for_window")
                ? ups_audit_ga4_channels_for_window($cache, $chart_windows)
                : (array) ($cache["channels"] ?? []);
            foreach ($ga4_channels as $ch) {
                if (!is_array($ch)) {
                    continue;
                }
                $k = strtolower((string) ($ch["source"] ?? "") . "|" . (string) ($ch["medium"] ?? ""));
                if (!isset($ch_merge[$k])) {
                    $ch_merge[$k] = $ch;
                } else {
                    $ch_merge[$k]["sessions"] = (int) ($ch_merge[$k]["sessions"] ?? 0) + (int) ($ch["sessions"] ?? 0);
                    $ch_merge[$k]["conversions"] = (int) ($ch_merge[$k]["conversions"] ?? 0) + (int) ($ch["conversions"] ?? 0);
                    $ch_merge[$k]["revenue"] = (float) ($ch_merge[$k]["revenue"] ?? 0) + (float) ($ch["revenue"] ?? 0);
                }
            }
            foreach (ups_audit_cache_timeseries($cache) as $d => $v) {
                if (!is_array($v)) {
                    continue;
                }
                if (!ups_audit_date_in_range((string) $d, $chart_windows["current"])) {
                    continue;
                }
                $ts_ga4[$d] = (int) ($ts_ga4[$d] ?? 0) + (int) ($v["sessions"] ?? 0);
            }
        } elseif ($type === "gsc") {
            if ($use_ts_totals) {
                $cur_ts = (array) ($window_totals["current"] ?? []);
                $prev_ts = (array) ($window_totals["previous"] ?? []);
                $imp = (int) round((float) ($cur_ts["impressions"] ?? 0));
                $agg["gsc_clicks"] += (int) round((float) ($cur_ts["clicks"] ?? 0));
                $agg["gsc_clicks_prev"] += (int) round((float) ($prev_ts["clicks"] ?? 0));
                $agg["gsc_impressions"] += $imp;
                $agg["gsc_impressions_prev"] += (int) round((float) ($prev_ts["impressions"] ?? 0));
            } else {
                $imp = (int) ($summary["impressions"] ?? 0);
                $agg["gsc_clicks"] += (int) ($summary["clicks"] ?? 0);
                $agg["gsc_clicks_prev"] += (int) ($prev_summary["clicks"] ?? 0);
                $agg["gsc_impressions"] += $imp;
                $agg["gsc_impressions_prev"] += (int) ($prev_summary["impressions"] ?? 0);
            }
            $gsc_kw_cur = function_exists("ups_audit_gsc_keywords_for_window")
                ? ups_audit_gsc_keywords_for_window($cache, $chart_windows, "current")
                : [];
            $gsc_kw_prev = function_exists("ups_audit_gsc_keywords_for_window")
                ? ups_audit_gsc_keywords_for_window($cache, $chart_windows, "previous")
                : [];
            if ($gsc_kw_cur !== []) {
                foreach ($gsc_kw_cur as $k => $kw) {
                    if (!is_array($kw)) {
                        continue;
                    }
                    $kw_merge[$k] = $kw;
                    $kw_merge[$k]["keyword"] = $k;
                    $imp_kw = (int) ($kw["impressions"] ?? 0);
                    $pos_kw = (float) ($kw["position"] ?? 0);
                    $agg["gsc_avg_position"] += $pos_kw * max(1, $imp_kw);
                    $gsc_pos_weight += max(1, $imp_kw);
                }
            } else {
                $agg["gsc_avg_position"] += ((float) ($summary["avg_position"] ?? 0)) * $imp;
                $gsc_pos_weight += $imp;
                foreach ((array) ($cache["top_keywords"] ?? []) as $kw) {
                    if (!is_array($kw)) {
                        continue;
                    }
                    $k = strtolower(trim((string) ($kw["keyword"] ?? "")));
                    if ($k === "") {
                        continue;
                    }
                    if (!isset($kw_merge[$k])) {
                        $kw_merge[$k] = $kw;
                        $kw_merge[$k]["keyword"] = $k;
                    } else {
                        $kw_merge[$k]["clicks"] = (int) ($kw_merge[$k]["clicks"] ?? 0) + (int) ($kw["clicks"] ?? 0);
                        $kw_merge[$k]["impressions"] = (int) ($kw_merge[$k]["impressions"] ?? 0) + (int) ($kw["impressions"] ?? 0);
                    }
                }
            }
            if ($gsc_kw_prev !== []) {
                foreach ($gsc_kw_prev as $k => $kw) {
                    if (!is_array($kw)) {
                        continue;
                    }
                    $kw_prev_merge[$k] = $kw;
                    $kw_prev_merge[$k]["keyword"] = $k;
                }
            } else {
                foreach ((array) ($cache["previous_top_keywords"] ?? []) as $kw) {
                    if (!is_array($kw)) {
                        continue;
                    }
                    $k = strtolower(trim((string) ($kw["keyword"] ?? "")));
                    if ($k === "") {
                        continue;
                    }
                    if (!isset($kw_prev_merge[$k])) {
                        $kw_prev_merge[$k] = $kw;
                        $kw_prev_merge[$k]["keyword"] = $k;
                    } else {
                        $kw_prev_merge[$k]["clicks"] = (int) ($kw_prev_merge[$k]["clicks"] ?? 0) + (int) ($kw["clicks"] ?? 0);
                        $kw_prev_merge[$k]["impressions"] = (int) ($kw_prev_merge[$k]["impressions"] ?? 0) + (int) ($kw["impressions"] ?? 0);
                    }
                }
            }
            foreach (ups_audit_cache_timeseries($cache) as $d => $v) {
                if (!is_array($v)) {
                    continue;
                }
                if (!ups_audit_date_in_range((string) $d, $chart_windows["current"])) {
                    continue;
                }
                $ts_gsc[$d] = (int) ($ts_gsc[$d] ?? 0) + (int) ($v["clicks"] ?? 0);
            }
        } elseif ($type === "ads") {
            if ($use_ts_totals) {
                $cur_ts = (array) ($window_totals["current"] ?? []);
                $prev_ts = (array) ($window_totals["previous"] ?? []);
                $agg["ads_cost"] += (float) ($cur_ts["cost"] ?? 0);
                $agg["ads_cost_prev"] += (float) ($prev_ts["cost"] ?? 0);
                $agg["ads_clicks"] += (int) round((float) ($cur_ts["clicks"] ?? 0));
                $agg["ads_clicks_prev"] += (int) round((float) ($prev_ts["clicks"] ?? 0));
                $agg["ads_conversions"] += (float) ($cur_ts["conversions"] ?? 0);
                $agg["ads_conversions_prev"] += (float) ($prev_ts["conversions"] ?? 0);
            } else {
                $agg["ads_cost"] += (float) ($summary["cost"] ?? 0);
                $agg["ads_cost_prev"] += (float) ($prev_summary["cost"] ?? 0);
                $agg["ads_clicks"] += (int) ($summary["clicks"] ?? 0);
                $agg["ads_clicks_prev"] += (int) ($prev_summary["clicks"] ?? 0);
                $agg["ads_conversions"] += (float) ($summary["conversions"] ?? 0);
                $agg["ads_conversions_prev"] += (float) ($prev_summary["conversions"] ?? 0);
            }
            $ads_terms = function_exists("ups_audit_ads_search_terms_for_window")
                ? ups_audit_ads_search_terms_for_window($cache, $chart_windows)
                : (array) ($cache["search_terms"] ?? []);
            foreach ($ads_terms as $st) {
                if (!is_array($st)) {
                    continue;
                }
                $term = strtolower(trim((string) ($st["search_term"] ?? "")));
                if ($term === "") {
                    continue;
                }
                if (!isset($st_merge[$term])) {
                    $st_merge[$term] = $st;
                } else {
                    $st_merge[$term]["cost_pln"] = (float) ($st_merge[$term]["cost_pln"] ?? 0) + (float) ($st["cost_pln"] ?? 0);
                    $st_merge[$term]["clicks"] = (int) ($st_merge[$term]["clicks"] ?? 0) + (int) ($st["clicks"] ?? 0);
                    $st_merge[$term]["impressions"] = (int) ($st_merge[$term]["impressions"] ?? 0) + (int) ($st["impressions"] ?? 0);
                    $st_merge[$term]["conversions"] = (float) ($st_merge[$term]["conversions"] ?? 0) + (float) ($st["conversions"] ?? 0);
                }
            }
            $ads_camps = function_exists("ups_audit_ads_campaigns_for_window")
                ? ups_audit_ads_campaigns_for_window($cache, $chart_windows)
                : (array) ($cache["campaigns"] ?? []);
            foreach ($ads_camps as $camp) {
                if (!is_array($camp)) {
                    continue;
                }
                $cid = (string) ($camp["id"] ?? "");
                if ($cid === "") {
                    continue;
                }
                if (!isset($camp_merge[$cid])) {
                    $camp_row = $camp;
                    if (!isset($camp_row["cost"]) && isset($camp_row["cost_pln"])) {
                        $camp_row["cost"] = (float) $camp_row["cost_pln"];
                    }
                    $camp_merge[$cid] = $camp_row;
                } else {
                    $camp_merge[$cid]["cost"] = (float) ($camp_merge[$cid]["cost"] ?? 0)
                        + (float) ($camp["cost"] ?? $camp["cost_pln"] ?? 0);
                    $camp_merge[$cid]["clicks"] = (int) ($camp_merge[$cid]["clicks"] ?? 0) + (int) ($camp["clicks"] ?? 0);
                    $camp_merge[$cid]["conversions"] = (float) ($camp_merge[$cid]["conversions"] ?? 0) + (float) ($camp["conversions"] ?? 0);
                }
            }
            foreach (ups_audit_cache_timeseries($cache) as $d => $v) {
                if (!is_array($v)) {
                    continue;
                }
                if (!ups_audit_date_in_range((string) $d, $chart_windows["current"])) {
                    continue;
                }
                $ts_ads[$d] = (float) ($ts_ads[$d] ?? 0) + (float) ($v["cost"] ?? 0);
                $ts_ads_clicks[$d] = (int) ($ts_ads_clicks[$d] ?? 0) + (int) ($v["clicks"] ?? 0);
            }
        } elseif ($type === "meta") {
            if ($use_ts_totals) {
                $cur_ts = (array) ($window_totals["current"] ?? []);
                $prev_ts = (array) ($window_totals["previous"] ?? []);
                $agg["meta_cost"] += (float) ($cur_ts["cost"] ?? 0);
                $agg["meta_cost_prev"] += (float) ($prev_ts["cost"] ?? 0);
                $agg["meta_clicks"] += (int) round((float) ($cur_ts["clicks"] ?? 0));
                $agg["meta_clicks_prev"] += (int) round((float) ($prev_ts["clicks"] ?? 0));
                $agg["meta_conversions"] += (float) ($cur_ts["conversions"] ?? 0);
                $agg["meta_conversions_prev"] += (float) ($prev_ts["conversions"] ?? 0);
            } else {
                $agg["meta_cost"] += (float) ($summary["cost"] ?? 0);
                $agg["meta_cost_prev"] += (float) ($prev_summary["cost"] ?? 0);
                $agg["meta_clicks"] += (int) ($summary["clicks"] ?? 0);
                $agg["meta_clicks_prev"] += (int) ($prev_summary["clicks"] ?? 0);
                $agg["meta_conversions"] += (float) ($summary["conversions"] ?? 0);
                $agg["meta_conversions_prev"] += (float) ($prev_summary["conversions"] ?? 0);
            }
            foreach ((array) ($cache["campaigns"] ?? []) as $camp) {
                if (!is_array($camp)) {
                    continue;
                }
                $cid = (string) ($camp["id"] ?? "");
                if ($cid === "") {
                    continue;
                }
                if (!isset($meta_camp_merge[$cid])) {
                    $meta_camp_merge[$cid] = $camp;
                } else {
                    $meta_camp_merge[$cid]["cost"] = (float) ($meta_camp_merge[$cid]["cost"] ?? 0) + (float) ($camp["cost"] ?? 0);
                    $meta_camp_merge[$cid]["clicks"] = (int) ($meta_camp_merge[$cid]["clicks"] ?? 0) + (int) ($camp["clicks"] ?? 0);
                    $meta_camp_merge[$cid]["conversions"] = (float) ($meta_camp_merge[$cid]["conversions"] ?? 0) + (float) ($camp["conversions"] ?? 0);
                }
            }
            foreach (ups_audit_cache_timeseries($cache) as $d => $v) {
                if (!is_array($v)) {
                    continue;
                }
                if (!ups_audit_date_in_range((string) $d, $chart_windows["current"])) {
                    continue;
                }
                $ts_meta[$d] = (float) ($ts_meta[$d] ?? 0) + (float) ($v["cost"] ?? 0);
            }
        } elseif ($type === "clarity") {
            $cache_err = trim((string) ($cache["error"] ?? ""));
            if ($cache_err !== "" && stripos($cache_err, "cache") === false) {
                $agg["clarity_errors"][] = (string) $r->post_title . ": " . $cache_err;
            }
            $agg["clarity_resources"][] = [
                "id" => $rid,
                "title" => (string) $r->post_title,
                "health" => $health,
                "sessions" => (int) ($summary["sessions"] ?? 0),
                "error" => $cache_err,
                "last_sync" => (string) get_post_meta($rid, "_ups_resource_last_data_sync", true),
            ];
            $agg["clarity_sessions"] += (int) ($summary["sessions"] ?? 0);
            $agg["clarity_users"] += (int) ($summary["users"] ?? 0);
            $agg["clarity_bot_sessions"] += (int) ($summary["bot_sessions"] ?? 0);
            $agg["clarity_dead_clicks"] += (int) ($summary["dead_clicks"] ?? 0);
            $agg["clarity_rage_clicks"] += (int) ($summary["rage_clicks"] ?? 0);
            $agg["clarity_quickback"] += (int) ($summary["quickback_clicks"] ?? 0);
            $agg["clarity_script_errors"] = (int) ($agg["clarity_script_errors"] ?? 0) + (int) ($summary["script_errors"] ?? 0);
            $pps = (float) ($summary["pages_per_session"] ?? 0);
            if ($pps > $agg["clarity_pages_per_session"]) {
                $agg["clarity_pages_per_session"] = $pps;
            }
            $et = (float) ($summary["engagement_time_sec"] ?? 0);
            if ($et > $agg["clarity_engagement_sec"]) {
                $agg["clarity_engagement_sec"] = $et;
            }
            $sd = (float) ($summary["scroll_depth"] ?? 0);
            if ($sd > $agg["clarity_scroll_depth"]) {
                $agg["clarity_scroll_depth"] = $sd;
            }
            $agg["clarity_top_pages"] = function_exists("ups_audit_clarity_merge_ranked_lists")
                ? ups_audit_clarity_merge_ranked_lists(
                    (array) $agg["clarity_top_pages"],
                    (array) ($summary["top_pages"] ?? $cache["top_pages"] ?? [])
                )
                : (array) ($summary["top_pages"] ?? []);
            $agg["clarity_top_referrers"] = function_exists("ups_audit_clarity_merge_ranked_lists")
                ? ups_audit_clarity_merge_ranked_lists((array) $agg["clarity_top_referrers"], (array) ($summary["top_referrers"] ?? $cache["top_referrers"] ?? []))
                : [];
            $agg["clarity_by_source"] = function_exists("ups_audit_clarity_merge_ranked_lists")
                ? ups_audit_clarity_merge_ranked_lists((array) $agg["clarity_by_source"], (array) ($summary["by_source"] ?? $cache["by_source"] ?? []))
                : [];
            $agg["clarity_by_country"] = function_exists("ups_audit_clarity_merge_ranked_lists")
                ? ups_audit_clarity_merge_ranked_lists((array) $agg["clarity_by_country"], (array) ($summary["by_country"] ?? $cache["by_country"] ?? []))
                : [];
            $agg["clarity_by_channel"] = function_exists("ups_audit_clarity_merge_ranked_lists")
                ? ups_audit_clarity_merge_ranked_lists((array) $agg["clarity_by_channel"], (array) ($summary["by_channel"] ?? $cache["by_channel"] ?? []))
                : [];
            $agg["clarity_window_days"] = max(
                (int) $agg["clarity_window_days"],
                (int) ($cache["api_num_days"] ?? 3)
            );
            $device_labels = ["mobile", "pc", "tablet", "other", "desktop"];
            foreach ((array) ($summary["by_dimension"] ?? $cache["by_dimension"] ?? []) as $dim_row) {
                if (!is_array($dim_row)) {
                    continue;
                }
                $lbl = (string) ($dim_row["label"] ?? "");
                if ($lbl === "" || strtolower($lbl) === "razem") {
                    continue;
                }
                if (!in_array(strtolower($lbl), $device_labels, true)) {
                    continue;
                }
                if (!isset($agg["clarity_by_device"][$lbl])) {
                    $agg["clarity_by_device"][$lbl] = $dim_row;
                } else {
                    $agg["clarity_by_device"][$lbl]["sessions"] = (int) ($agg["clarity_by_device"][$lbl]["sessions"] ?? 0)
                        + (int) ($dim_row["sessions"] ?? 0);
                    $agg["clarity_by_device"][$lbl]["dead_clicks"] = (int) ($agg["clarity_by_device"][$lbl]["dead_clicks"] ?? 0)
                        + (int) ($dim_row["dead_clicks"] ?? 0);
                    $agg["clarity_by_device"][$lbl]["rage_clicks"] = (int) ($agg["clarity_by_device"][$lbl]["rage_clicks"] ?? 0)
                        + (int) ($dim_row["rage_clicks"] ?? 0);
                }
            }
        }
    }

    if ($agg["ga4_sessions"] > 0 && $agg["ga4_not_set_sessions"] > 0) {
        $agg["ga4_not_set_pct"] = round(($agg["ga4_not_set_sessions"] / $agg["ga4_sessions"]) * 100, 1);
    }

    foreach ((array) ($agg["window_slice_notes"] ?? []) as $wnote) {
        if (is_string($wnote) && $wnote !== "" && !in_array($wnote, $agg["data_quality_notes"], true)) {
            $agg["data_quality_notes"][] = $wnote;
        }
    }

    $agg["gsc_avg_position"] = $gsc_pos_weight > 0 ? round($agg["gsc_avg_position"] / $gsc_pos_weight, 2) : 0.0;
    $agg["gsc_ctr"] = $agg["gsc_impressions"] > 0
        ? round(($agg["gsc_clicks"] / $agg["gsc_impressions"]) * 100, 2)
        : 0.0;
    $agg["paid_cost"] = (float) $agg["ads_cost"] + (float) $agg["meta_cost"];
    $agg["paid_cost_prev"] = (float) $agg["ads_cost_prev"] + (float) $agg["meta_cost_prev"];
    $agg["roas"] = $agg["ads_cost"] > 0 ? round($agg["ga4_revenue"] / $agg["ads_cost"], 2) : 0.0;
    $agg["roas_prev"] = $agg["ads_cost_prev"] > 0 ? round($agg["ga4_revenue_prev"] / $agg["ads_cost_prev"], 2) : 0.0;
    $agg["meta_roas"] = $agg["meta_cost"] > 0 ? round($agg["ga4_revenue"] / $agg["meta_cost"], 2) : 0.0;
    $agg["meta_roas_prev"] = $agg["meta_cost_prev"] > 0 ? round($agg["ga4_revenue_prev"] / $agg["meta_cost_prev"], 2) : 0.0;

    uasort($kw_merge, static function ($a, $b) {
        return ((int) ($b["clicks"] ?? 0)) <=> ((int) ($a["clicks"] ?? 0));
    });
    uasort($ch_merge, static function ($a, $b) {
        return ((int) ($b["sessions"] ?? 0)) <=> ((int) ($a["sessions"] ?? 0));
    });
    uasort($camp_merge, static function ($a, $b) {
        return ((float) ($b["cost"] ?? 0)) <=> ((float) ($a["cost"] ?? 0));
    });
    uasort($meta_camp_merge, static function ($a, $b) {
        return ((float) ($b["cost"] ?? 0)) <=> ((float) ($a["cost"] ?? 0));
    });
    ksort($ts_gsc);
    ksort($ts_ga4);
    ksort($ts_ads);
    ksort($ts_ads_clicks);
    ksort($ts_meta);

    $agg["top_keywords"] = array_slice(array_values($kw_merge), 0, 15);
    foreach ($agg["top_keywords"] as $idx => $kw_row) {
        if (!is_array($kw_row)) {
            continue;
        }
        $k = strtolower(trim((string) ($kw_row["keyword"] ?? "")));
        if ($k === "" || !isset($kw_prev_merge[$k])) {
            continue;
        }
        $prev_pos = (float) ($kw_prev_merge[$k]["position"] ?? 0);
        $cur_pos = (float) ($kw_row["position"] ?? 0);
        if ($prev_pos > 0 && $cur_pos > 0) {
            $agg["top_keywords"][$idx]["position_prev"] = $prev_pos;
            $agg["top_keywords"][$idx]["position_delta"] = round($cur_pos - $prev_pos, 1);
        }
    }
    $agg["keyword_position_changes"] = function_exists("ups_audit_gsc_keyword_position_changes")
        ? ups_audit_gsc_keyword_position_changes($kw_merge, $kw_prev_merge)
        : [];
    $agg["channels"] = array_slice(array_values($ch_merge), 0, 12);
    $agg["campaigns"] = array_slice(array_values($camp_merge), 0, 15);
    uasort($st_merge, static function ($a, $b) {
        return ((float) ($b["cost_pln"] ?? 0)) <=> ((float) ($a["cost_pln"] ?? 0));
    });
    $agg["search_terms"] = array_slice(array_values($st_merge), 0, 250);
    $agg["meta_campaigns"] = array_slice(array_values($meta_camp_merge), 0, 15);
    $agg["timeseries"]["gsc_clicks"] = $ts_gsc;
    $agg["timeseries"]["ga4_sessions"] = $ts_ga4;
    $agg["timeseries"]["ads_cost"] = $ts_ads;
    $agg["timeseries"]["ads_clicks"] = $ts_ads_clicks;
    $agg["timeseries"]["meta_cost"] = $ts_meta;
    if (function_exists("ups_audit_clarity_finalize_breakdown") && $agg["clarity_by_device"] !== []) {
        $agg["clarity_by_device"] = ups_audit_clarity_finalize_breakdown(
            array_values($agg["clarity_by_device"]),
            (int) $agg["clarity_sessions"],
            (int) $agg["clarity_dead_clicks"],
            (int) $agg["clarity_rage_clicks"]
        );
    } else {
        $agg["clarity_by_device"] = array_values($agg["clarity_by_device"]);
    }
    $agg["deltas"] = [
        "ga4_sessions" => ups_audit_delta_pct((float) $agg["ga4_sessions"], (float) $agg["ga4_sessions_prev"]),
        "ga4_conversions" => ups_audit_delta_pct((float) $agg["ga4_conversions"], (float) $agg["ga4_conversions_prev"]),
        "gsc_clicks" => ups_audit_delta_pct((float) $agg["gsc_clicks"], (float) $agg["gsc_clicks_prev"]),
        "gsc_impressions" => ups_audit_delta_pct((float) $agg["gsc_impressions"], (float) $agg["gsc_impressions_prev"]),
        "ads_cost" => ups_audit_delta_pct((float) $agg["ads_cost"], (float) $agg["ads_cost_prev"]),
        "ads_clicks" => ups_audit_delta_pct((float) $agg["ads_clicks"], (float) $agg["ads_clicks_prev"]),
        "ads_conversions" => ups_audit_delta_pct((float) $agg["ads_conversions"], (float) $agg["ads_conversions_prev"]),
        "meta_cost" => ups_audit_delta_pct((float) $agg["meta_cost"], (float) $agg["meta_cost_prev"]),
        "meta_clicks" => ups_audit_delta_pct((float) $agg["meta_clicks"], (float) $agg["meta_clicks_prev"]),
        "meta_roas" => ups_audit_delta_pct((float) $agg["meta_roas"], (float) $agg["meta_roas_prev"]),
        "paid_cost" => ups_audit_delta_pct((float) $agg["paid_cost"], (float) $agg["paid_cost_prev"]),
        "roas" => ups_audit_delta_pct((float) $agg["roas"], (float) $agg["roas_prev"]),
    ];

    $agg["derived"] = function_exists("ups_audit_compute_derived_metrics")
        ? ups_audit_compute_derived_metrics($agg)
        : [];
    if ($agg["clarity_users"] > 0) {
        $agg["derived"]["clarity_sessions_per_user"] = round($agg["clarity_sessions"] / $agg["clarity_users"], 2);
    }
    $agg["opportunities"] = function_exists("ups_audit_find_opportunities")
        ? ups_audit_find_opportunities($agg)
        : [];
    $agg["health_score"] = function_exists("ups_audit_health_score")
        ? ups_audit_health_score($agg, $setup)
        : 0;

    if (function_exists("ups_audit_health_trend")) {
        $agg["health_trend"] = ups_audit_health_trend($agg, $setup, (int) $extras_client_id);
    }
    if ($extras_client_id > 0 && function_exists("ups_audit_record_health_snapshot")) {
        ups_audit_record_health_snapshot((int) $extras_client_id, (int) ($agg["health_score"] ?? 0), $days);
    }
    if (function_exists("ups_audit_revenue_quality")) {
        $agg["revenue_quality"] = ups_audit_revenue_quality($agg);
    }

    if (
        function_exists("ups_audit_attach_intelligence")
        && !(function_exists("ups_audit_benchmark_aggregation_active") && ups_audit_benchmark_aggregation_active())
    ) {
        $agg = ups_audit_attach_intelligence($agg, $extras_client_id, $setup);
    }

    $agg["recommendations"] = function_exists("ups_audit_build_recommendations")
        ? ups_audit_build_recommendations($agg, $setup)
        : [];

    $extras_client_id = (int) $extras_client_id;
    if ($with_extras && $extras_client_id > 0) {
        if (function_exists("ups_audit_compute_portfolio_benchmark_cached")) {
            $agg["benchmark"] = ups_audit_compute_portfolio_benchmark_cached($days, $extras_client_id);
        } elseif (function_exists("ups_audit_compute_portfolio_benchmark")) {
            $agg["benchmark"] = ups_audit_compute_portfolio_benchmark($days, $extras_client_id);
        }
        if (function_exists("ups_audit_client_technical_signals")) {
            $agg["technical"] = ups_audit_client_technical_signals($extras_client_id, $with_extras);
        }
        if (function_exists("upsellio_analytics_channel_ltv_for_client")) {
            $agg["channel_ltv"] = upsellio_analytics_channel_ltv_for_client($extras_client_id, $days, $agg);
        }
    }

    return $agg;
}

/**
 * Agregacja KPI klienta CRM (zmapowane zasoby).
 *
 * @return array<string, mixed>
 */
function ups_audit_aggregate_client_data(int $client_id, int $days = 30, int $offset = 0, bool $with_extras = true): array
{
    $client_id = (int) $client_id;
    $setup = ups_audit_client_setup_status($client_id);

    return ups_audit_aggregate_resources_data(
        ups_audit_get_client_resources($client_id),
        $days,
        $offset,
        $with_extras,
        $setup,
        $client_id
    );
}

/**
 * Agregacja KPI konta Google (wszystkie zaimportowane zasoby konta).
 *
 * @return array<string, mixed>
 */
function ups_audit_aggregate_google_account_data(int $google_account_id, int $days = 30, int $offset = 0, bool $with_extras = false): array
{
    $google_account_id = (int) $google_account_id;
    $setup = function_exists("ups_audit_google_account_setup_status")
        ? ups_audit_google_account_setup_status($google_account_id)
        : ["ga4" => 0, "gsc" => 0, "ads" => 0, "is_ready" => false];
    $mapped_client = function_exists("ups_audit_google_account_primary_client_id")
        ? ups_audit_google_account_primary_client_id($google_account_id)
        : 0;

    return ups_audit_aggregate_resources_data(
        function_exists("ups_audit_get_google_account_resources")
            ? ups_audit_get_google_account_resources($google_account_id)
            : [],
        $days,
        $offset,
        $with_extras,
        $setup,
        $mapped_client
    );
}

/**
 * @return array<int, array<string, mixed>>
 */
function ups_audit_get_portfolio_rows(): array
{
    $accounts = get_posts([
        "post_type" => "crm_google_account",
        "posts_per_page" => -1,
        "post_status" => ["publish", "draft"],
        "orderby" => "title",
        "order" => "ASC",
    ]);
    $rows = [];
    $window = (int) get_option("ups_audit_default_compare_window", 30);

    foreach ($accounts as $acc) {
        if (!($acc instanceof WP_Post)) {
            continue;
        }
        $aid = (int) $acc->ID;
        $email = trim((string) get_post_meta($aid, "_ups_gacc_email", true));
        $label = trim((string) get_post_meta($aid, "_ups_gacc_label", true));
        $title = $email !== "" ? $email : (string) $acc->post_title;
        $setup = function_exists("ups_audit_google_account_setup_status")
            ? ups_audit_google_account_setup_status($aid)
            : ["ga4" => 0, "gsc" => 0, "ads" => 0, "imported" => 0, "is_ready" => false];
        $data = function_exists("ups_audit_aggregate_google_account_data")
            ? ups_audit_aggregate_google_account_data($aid, $window, 0, false)
            : [];
        $last = function_exists("ups_audit_get_google_account_last_sync")
            ? (int) ups_audit_get_google_account_last_sync($aid)
            : 0;
        $mapped_client_id = function_exists("ups_audit_google_account_primary_client_id")
            ? ups_audit_google_account_primary_client_id($aid)
            : 0;
        $mapped_client_title = $mapped_client_id > 0 ? (string) get_the_title($mapped_client_id) : "";

        $health = "ok";
        foreach ((array) ($data["resources"] ?? []) as $res) {
            if (!is_array($res)) {
                continue;
            }
            $h = (string) (($res["health"]["status"] ?? ""));
            if ($h === "error") {
                $health = "error";
                break;
            }
            if ($h === "warn" && $health !== "error") {
                $health = "warn";
            }
        }
        if ((int) ($setup["imported"] ?? 0) <= 0) {
            $health = "warn";
        } elseif (!$setup["is_ready"]) {
            $health = $health === "ok" ? "warn" : $health;
        }

        $recs = (array) ($data["recommendations"] ?? []);
        $high_alerts = count(array_filter($recs, static function ($r) {
            return is_array($r) && ($r["priority"] ?? "") === "high";
        }));
        if ((int) ($setup["imported"] ?? 0) <= 0 && $high_alerts < 1) {
            $high_alerts = 1;
        }

        $rows[] = [
            "id" => $aid,
            "row_type" => "google_account",
            "google_account_id" => $aid,
            "title" => $title,
            "label" => $label,
            "mapped_client_id" => $mapped_client_id,
            "mapped_client_title" => $mapped_client_title,
            "setup" => $setup,
            "health" => $health,
            "health_score" => (int) ($data["health_score"] ?? 0),
            "alert_count" => $high_alerts,
            "top_insight" => isset($recs[0]["title"])
                ? (string) $recs[0]["title"]
                : ((int) ($setup["imported"] ?? 0) <= 0
                    ? __("Zaimportuj zasoby GA4/GSC z tego konta", "upsellio")
                    : __("Uzupełnij mapowanie do klienta CRM (opcjonalnie)", "upsellio")),
            "last_sync" => $last,
            "ga4_sessions" => (int) ($data["ga4_sessions"] ?? 0),
            "gsc_clicks" => (int) ($data["gsc_clicks"] ?? 0),
            "ads_cost" => (float) ($data["ads_cost"] ?? 0),
            "roas" => (float) ($data["roas"] ?? 0),
            "deltas" => (array) ($data["deltas"] ?? []),
        ];
    }

    return $rows;
}

/**
 * Wiersze tabeli profili klientów (agregat zmapowanych zasobów).
 *
 * @return array<int, array<string, mixed>>
 */
function ups_audit_get_client_profile_rows(int $window = 0): array
{
    if (!function_exists("ups_audit_collect_profile_client_ids")) {
        return [];
    }
    $window = $window > 0 ? $window : (int) get_option("ups_audit_default_compare_window", 30);
    if (!in_array($window, [7, 14, 30, 60, 90], true)) {
        $window = 30;
    }
    $rows = [];
    $client_ids = ups_audit_collect_profile_client_ids();
    sort($client_ids);

    foreach ($client_ids as $cid) {
        $client = get_post((int) $cid);
        if (!($client instanceof WP_Post) || $client->post_type !== "crm_client") {
            continue;
        }
        $setup = function_exists("ups_audit_client_setup_status")
            ? ups_audit_client_setup_status((int) $cid)
            : ["ga4" => 0, "gsc" => 0, "ads" => 0, "is_ready" => false];
        $data = function_exists("ups_audit_aggregate_client_data")
            ? ups_audit_aggregate_client_data((int) $cid, $window, 0, false)
            : [];
        $last = function_exists("ups_audit_get_client_last_sync")
            ? (int) ups_audit_get_client_last_sync((int) $cid)
            : 0;
        $resource_count = count(function_exists("ups_audit_get_client_resources")
            ? ups_audit_get_client_resources((int) $cid)
            : []);

        $health = "ok";
        foreach ((array) ($data["resources"] ?? []) as $res) {
            if (!is_array($res)) {
                continue;
            }
            $h = (string) (($res["health"]["status"] ?? ""));
            if ($h === "error") {
                $health = "error";
                break;
            }
            if ($h === "warn" && $health !== "error") {
                $health = "warn";
            }
        }
        if ($resource_count <= 0) {
            $health = "warn";
        } elseif (!$setup["is_ready"]) {
            $health = $health === "ok" ? "warn" : $health;
        }

        $intel_alerts = (int) ($data["alert_count"] ?? 0);
        if ($intel_alerts <= 0) {
            $recs = (array) ($data["recommendations"] ?? []);
            $intel_alerts = count(array_filter($recs, static function ($r) {
                return is_array($r) && ($r["priority"] ?? "") === "high";
            }));
        }
        $high_alerts = $intel_alerts;

        $website = trim((string) get_post_meta((int) $cid, "_ups_client_website", true));

        $rows[] = [
            "id" => (int) $cid,
            "row_type" => "client_profile",
            "title" => (string) $client->post_title,
            "website" => $website,
            "resource_count" => $resource_count,
            "setup" => $setup,
            "health" => $health,
            "health_score" => (int) ($data["health_score"] ?? 0),
            "alert_count" => $high_alerts,
            "last_sync" => $last,
            "ga4_sessions" => (int) ($data["ga4_sessions"] ?? 0),
            "gsc_clicks" => (int) ($data["gsc_clicks"] ?? 0),
            "ads_cost" => (float) ($data["ads_cost"] ?? 0),
            "meta_cost" => (float) ($data["meta_cost"] ?? 0),
            "paid_cost" => (float) ($data["paid_cost"] ?? 0),
            "roas" => (float) ($data["roas"] ?? 0),
            "meta_roas" => (float) ($data["meta_roas"] ?? 0),
            "clarity_sessions" => (int) ($data["clarity_sessions"] ?? 0),
            "clarity_dead_clicks" => (int) ($data["clarity_dead_clicks"] ?? 0),
            "deltas" => (array) ($data["deltas"] ?? []),
        ];
    }

    usort($rows, static function ($a, $b) {
        return strcasecmp((string) ($a["title"] ?? ""), (string) ($b["title"] ?? ""));
    });

    return $rows;
}
