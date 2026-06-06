<?php

if (!defined("ABSPATH")) {
    exit;
}

/**
 * System decyzyjny audytu — Search Terms, Content Potential, Tracking Health,
 * Alert Engine, SEO Roadmap, UX Audit, Customer Journey, Product Analytics,
 * Profit Dashboard, Benchmarking, Competition (premium stub).
 */

/**
 * @param array<string, mixed> $agg
 * @return array<string, mixed>
 */
function ups_audit_attach_intelligence(array $agg, int $client_id = 0, array $setup = []): array
{
    $client_id = (int) $client_id;
    if ($client_id > 0) {
        $agg["client_id"] = $client_id;
    }
    $derived = (array) ($agg["derived"] ?? []);
    if ($derived === [] && function_exists("ups_audit_compute_derived_metrics")) {
        $derived = ups_audit_compute_derived_metrics($agg);
        $agg["derived"] = $derived;
    }

    $benchmark = (array) ($agg["benchmark"] ?? []);

    $tracking = ups_audit_intel_tracking_health($agg, $setup);
    $content = ups_audit_intel_content_potential($agg, $benchmark);
    $search_terms = ups_audit_intel_search_terms($agg, $client_id);

    $period_days = (int) ($agg["period_days"] ?? 30);
    $crm_revenue = function_exists("ups_audit_intel_crm_revenue_attribution")
        ? ups_audit_intel_crm_revenue_attribution($client_id, $agg, $period_days)
        : ["has_data" => false, "rows" => []];

    $crm_quality = function_exists("ups_audit_crm_quality_score")
        ? ups_audit_crm_quality_score($crm_revenue)
        : ["score" => 0, "has_data" => false, "label" => ""];
    $crm_revenue["quality"] = $crm_quality;

    $revenue_quality = (array) ($agg["revenue_quality"] ?? []);
    if ($revenue_quality === [] && function_exists("ups_audit_revenue_quality")) {
        $revenue_quality = ups_audit_revenue_quality($agg);
    }

    $intel = [
        "executive_summary" => ups_audit_intel_executive_summary($agg, $setup, $tracking, $content, $search_terms),
        "opportunity" => ups_audit_intel_opportunity_score($agg, $tracking, $content, $search_terms),
        "search_terms" => $search_terms,
        "content_potential" => $content,
        "tracking_health" => $tracking,
        "alerts" => ups_audit_intel_build_alerts($agg, $setup),
        "seo_roadmap" => ups_audit_intel_seo_roadmap($agg, $setup),
        "seo_clusters" => ups_audit_intel_seo_clusters($agg, $content),
        "ux_audit" => ups_audit_intel_ux_audit($agg),
        "customer_journey" => ups_audit_intel_customer_journey($agg, $client_id, $period_days),
        "crm_revenue" => $crm_revenue,
        "crm_quality" => $crm_quality,
        "revenue_quality" => $revenue_quality,
        "health_trend" => (array) ($agg["health_trend"] ?? []),
        "product_analytics" => ups_audit_intel_product_analytics($agg),
        "profit" => ups_audit_intel_profit_dashboard($agg, $client_id, $crm_revenue, $revenue_quality),
        "competition" => ups_audit_intel_competition($agg, $client_id),
        "benchmark_intel" => ups_audit_intel_benchmark_compare($agg, $benchmark, $derived),
        "ads_channel_cpa" => ups_audit_intel_ads_channel_cpa($agg),
    ];

    $agg["intelligence"] = $intel;

    if ($client_id > 0 && function_exists("ups_audit_attribution_confidence")) {
        $attr_conf = ups_audit_attribution_confidence($agg, $client_id);
        $intel["attribution_confidence"] = $attr_conf;
        $agg["attribution_confidence"] = $attr_conf;
        $agg["intelligence"]["attribution_confidence"] = $attr_conf;
    }

    if (function_exists("ups_audit_revenue_confidence")) {
        $rev_conf = ups_audit_revenue_confidence($agg, $client_id);
        $intel["revenue_confidence"] = $rev_conf;
        $agg["revenue_confidence"] = $rev_conf;
        $agg["intelligence"]["revenue_confidence"] = $rev_conf;
    }

    if (function_exists("ups_audit_data_quality_panel")) {
        $dq = ups_audit_data_quality_panel($agg, $client_id, $setup);
        $intel["data_quality"] = $dq;
        $agg["data_quality"] = $dq;
        $agg["intelligence"]["data_quality"] = $dq;
    }

    $agg["alert_count"] = count(array_filter($intel["alerts"], static function ($a) {
        return is_array($a) && in_array((string) ($a["severity"] ?? ""), ["critical", "warning"], true);
    }));

    return $agg;
}

/**
 * @param array<string, mixed> $agg
 * @return array<string, mixed>
 */
function ups_audit_intel_search_terms(array $agg, int $client_id = 0): array
{
    $terms = (array) ($agg["search_terms"] ?? []);
    $waste = [];
    $converting = [];
    $scale = [];
    $watch = [];
    $exclude = [];
    $total_cost = 0.0;
    $waste_cost = 0.0;

    $aging = [];
    if ($client_id > 0) {
        $stored = get_post_meta($client_id, "_ups_audit_search_term_aging", true);
        $aging = is_array($stored) ? $stored : [];
    }

    foreach ($terms as $row) {
        if (!is_array($row)) {
            continue;
        }
        $cost = (float) ($row["cost_pln"] ?? $row["cost"] ?? 0);
        $conv = (float) ($row["conversions"] ?? 0);
        $clicks = (int) ($row["clicks"] ?? 0);
        $term = (string) ($row["search_term"] ?? "");
        $total_cost += $cost;

        $obs_days = function_exists("ups_audit_search_term_observation_days")
            ? ups_audit_search_term_observation_days($term, $aging)
            : 0;

        $action = function_exists("ups_audit_classify_search_term")
            ? ups_audit_classify_search_term($term, $cost, $conv, $clicks, $obs_days)
            : ($conv > 0 ? "scale" : "watch");
        $action_label = function_exists("ups_audit_search_term_action_label")
            ? ups_audit_search_term_action_label($action)
            : $action;
        if ($action === "watch" && $obs_days > 0) {
            $action_label = sprintf(__("Obserwacja: %d dni", "upsellio"), $obs_days);
        } elseif ($action === "exclude" && $obs_days > 0) {
            $action_label = sprintf(__("Wyklucz · %d dni", "upsellio"), $obs_days);
        }

        $item = [
            "term" => $term,
            "campaign" => (string) ($row["campaign_name"] ?? ""),
            "ad_group" => (string) ($row["ad_group_name"] ?? ""),
            "cost" => $cost,
            "clicks" => $clicks,
            "impressions" => (int) ($row["impressions"] ?? 0),
            "conversions" => $conv,
            "cpa" => $conv > 0 ? round($cost / $conv, 2) : null,
            "action" => $action,
            "action_label" => $action_label,
            "observation_days" => $obs_days,
        ];

        if ($action === "scale") {
            $converting[] = $item;
            $scale[] = $item;
        } elseif ($action === "exclude") {
            $waste[] = $item;
            $waste_cost += $cost;
            $exclude[] = $item;
        } elseif ($cost >= 15 && $clicks >= 2) {
            $waste[] = $item;
            $waste_cost += $cost;
            $watch[] = $item;
        }
    }

    usort($waste, static fn($a, $b) => ((float) ($b["cost"] ?? 0)) <=> ((float) ($a["cost"] ?? 0)));
    usort($converting, static fn($a, $b) => ((float) ($b["conversions"] ?? 0)) <=> ((float) ($a["conversions"] ?? 0)));
    usort($watch, static fn($a, $b) => ((float) ($b["cost"] ?? 0)) <=> ((float) ($a["cost"] ?? 0)));
    usort($exclude, static fn($a, $b) => ((float) ($b["cost"] ?? 0)) <=> ((float) ($a["cost"] ?? 0)));
    usort($scale, static fn($a, $b) => ((float) ($b["conversions"] ?? 0)) <=> ((float) ($a["conversions"] ?? 0)));

    $waste_pct = $total_cost > 0 ? round(($waste_cost / $total_cost) * 100, 1) : 0.0;
    $exclude_cost = array_sum(array_map(static fn($r) => (float) ($r["cost"] ?? 0), $exclude));

    return [
        "has_data" => $terms !== [],
        "total_terms" => count($terms),
        "total_cost" => round($total_cost, 2),
        "waste_cost" => round($waste_cost, 2),
        "waste_pct" => $waste_pct,
        "waste" => array_slice($waste, 0, 15),
        "converting" => array_slice($converting, 0, 15),
        "scale" => array_slice($scale, 0, 12),
        "watch" => array_slice($watch, 0, 12),
        "exclude_candidates" => array_slice($exclude, 0, 12),
        "exclude_cost" => round($exclude_cost, 2),
        "summary" => $terms === []
            ? __("Brak search terms — zmapuj konto Ads i uruchom sync.", "upsellio")
            : sprintf(
                "%.0f PLN (%.0f%% budżetu) na frazy bez konwersji — %.0f PLN kandydatów do wykluczenia (≥30 dni, ≥100 PLN), reszta w obserwacji.",
                $waste_cost,
                $waste_pct,
                $exclude_cost
            ),
    ];
}

/**
 * @param array<string, mixed> $agg
 * @param array<string, mixed> $benchmark
 * @return array<string, mixed>
 */
function ups_audit_intel_content_potential(array $agg, array $benchmark = []): array
{
    $target_ctr = 2.5;
    $portfolio_ctr = 0.0;
    if (!empty($benchmark["gsc_ctr_avg"]) && (float) $benchmark["gsc_ctr_avg"] > 0) {
        $target_ctr = max(2.0, (float) $benchmark["gsc_ctr_avg"]);
        $portfolio_ctr = (float) $benchmark["gsc_ctr_avg"];
    }

    $rows = [];
    $total_potential = 0;

    foreach ((array) ($agg["top_keywords"] ?? []) as $kw) {
        if (!is_array($kw)) {
            continue;
        }
        $impr = (int) ($kw["impressions"] ?? 0);
        $clicks = (int) ($kw["clicks"] ?? 0);
        $pos = (float) ($kw["position"] ?? 0);
        if ($impr < 30) {
            continue;
        }
        $ctr = $impr > 0 ? round(($clicks / $impr) * 100, 2) : 0.0;
        if ($ctr >= $target_ctr) {
            continue;
        }
        $gain_clicks = (int) round($impr * (($target_ctr - $ctr) / 100));
        if ($gain_clicks <= 0) {
            continue;
        }
        $total_potential += $gain_clicks;
        $action = $pos <= 10
            ? __("Popraw title i meta description (CTR poniżej średniej).", "upsellio")
            : __("Rozbuduj treść + linkowanie wewnętrzne (pozycja 4–20).", "upsellio");

        $rows[] = [
            "keyword" => (string) ($kw["keyword"] ?? ""),
            "impressions" => $impr,
            "clicks" => $clicks,
            "ctr" => $ctr,
            "position" => $pos,
            "target_ctr" => $target_ctr,
            "potential_clicks" => $gain_clicks,
            "action" => $action,
            "score" => $gain_clicks * (1 / max(1, $pos)),
        ];
    }

    usort($rows, static fn($a, $b) => ((int) ($b["potential_clicks"] ?? 0)) <=> ((int) ($a["potential_clicks"] ?? 0)));

    return [
        "target_ctr" => $target_ctr,
        "portfolio_ctr" => $portfolio_ctr,
        "total_potential_clicks" => $total_potential,
        "rows" => array_slice($rows, 0, 12),
        "summary" => $total_potential > 0
            ? sprintf(
                /* translators: 1: click count, 2: target CTR */
                __("Zwiększenie CTR do %.1f%% może dać +%d kliknięć miesięcznie.", "upsellio"),
                $target_ctr,
                $total_potential
            )
            : __("Brak wystarczających danych GSC do estymacji potencjału.", "upsellio"),
    ];
}

/**
 * @param array<string, mixed> $agg
 * @return array<string, mixed>
 */
function ups_audit_intel_tracking_health(array $agg, array $setup = []): array
{
    $checks = [];
    $score = 100;
    $events = (array) ($agg["ga4_events_breakdown"] ?? []);
    $macro = (array) ($events["macro"] ?? []);
    $has_purchase = false;
    $has_lead = false;

    foreach ($macro as $ev) {
        if (!is_array($ev)) {
            continue;
        }
        $name = strtolower((string) ($ev["event"] ?? ""));
        $count = (int) ($ev["count"] ?? 0);
        if ($count <= 0) {
            continue;
        }
        if (in_array($name, ["purchase", "ecommerce_purchase"], true)) {
            $has_purchase = true;
        }
        if (in_array($name, ["generate_lead", "lead", "form_submit"], true) && $name !== "form_submit") {
            $has_lead = true;
        }
        if ($name === "purchase") {
            $has_purchase = true;
        }
        if (in_array($name, ["generate_lead", "lead"], true)) {
            $has_lead = true;
        }
    }

    $conv_macro = (int) ($agg["ga4_conversions"] ?? 0);
    if ($conv_macro > 0) {
        $has_lead = $has_lead || $has_purchase;
    }

    $not_set = (float) ($agg["ga4_not_set_pct"] ?? 0);
    $sessions = (int) ($agg["ga4_sessions"] ?? 0);

    if ((int) ($setup["ga4"] ?? 0) <= 0) {
        $checks[] = ["id" => "ga4_missing", "ok" => false, "label" => "GA4", "detail" => __("Brak zmapowanego GA4.", "upsellio")];
        $score -= 30;
    } else {
        $checks[] = ["id" => "ga4_ok", "ok" => $sessions > 0, "label" => "GA4 sesje", "detail" => $sessions > 0 ? "{$sessions} sesji" : __("Brak sesji — sprawdź property.", "upsellio")];
        if ($sessions <= 0) {
            $score -= 20;
        }
    }

    $checks[] = [
        "id" => "purchase",
        "ok" => $has_purchase || $conv_macro > 0,
        "label" => "Purchase / konwersja makro",
        "detail" => $has_purchase
            ? __("Event purchase lub makro konwersje wykryte.", "upsellio")
            : ($conv_macro > 0 ? "{$conv_macro} konwersji makro" : __("Brak purchase/lead — sprawdź GTM.", "upsellio")),
    ];
    if (!$has_purchase && $conv_macro <= 0 && $sessions >= 50) {
        $score -= 25;
    }

    $checks[] = [
        "id" => "lead",
        "ok" => $has_lead,
        "label" => "Lead tracking",
        "detail" => $has_lead ? __("generate_lead / lead aktywne.", "upsellio") : __("Brak eventów lead — formularze mogą być niewidoczne.", "upsellio"),
    ];
    if (!$has_lead && $sessions >= 100) {
        $score -= 10;
    }

    $utm_ok = $not_set < 20;
    $checks[] = [
        "id" => "utm",
        "ok" => $utm_ok,
        "label" => "Atrybucja (not set)",
        "detail" => $not_set > 0
            ? sprintf("%.0f%% sesji bez źródła — popraw UTM.", $not_set)
            : __("Atrybucja wygląda poprawnie.", "upsellio"),
    ];
    if ($not_set >= 45) {
        $score -= 25;
    } elseif ($not_set >= 25) {
        $score -= 15;
    } elseif ($not_set >= 10) {
        $score -= 5;
    }

    $conv_all = (int) ($agg["ga4_conversions_all"] ?? 0);
    if ($conv_all > 0 && $conv_macro > 0 && $conv_all > $conv_macro * 3) {
        $checks[] = [
            "id" => "micro_inflation",
            "ok" => false,
            "label" => "Jakość konwersji",
            "detail" => sprintf("Makro %d vs wszystkie %d — mikro-eventy zawyżają CR.", $conv_macro, $conv_all),
        ];
        $score -= 15;
    }

    if ((int) ($setup["ads"] ?? 0) > 0 && (float) ($agg["ads_cost"] ?? 0) > 0) {
        $ads_conv = (float) ($agg["ads_conversions"] ?? 0);
        $ga4_conv = (int) ($agg["ga4_conversions"] ?? 0);
        $mismatch = $ads_conv > 0 && $ga4_conv > 0 && abs($ads_conv - $ga4_conv) / max(1, $ga4_conv) > 0.5;
        $checks[] = [
            "id" => "ads_ga4_match",
            "ok" => !$mismatch,
            "label" => "Ads ↔ GA4 konwersje",
            "detail" => $mismatch
                ? sprintf("Ads %.0f vs GA4 %d — sprawdź import konwersji.", $ads_conv, $ga4_conv)
                : __("Spójność w granicach normy.", "upsellio"),
        ];
        if ($mismatch) {
            $score -= 10;
        }
    }

    $score = max(0, min(100, $score));
    $status = $score >= 75 ? "good" : ($score >= 50 ? "warn" : "critical");

    return [
        "score" => $score,
        "status" => $status,
        "checks" => $checks,
        "ready_for_journey" => $score >= 60 && $sessions >= 50,
        "summary" => $status === "good"
            ? __("Tracking gotowy do analizy lejka.", "upsellio")
            : ($status === "warn"
                ? __("Tracking wymaga poprawek przed Customer Journey.", "upsellio")
                : __("Krytyczne braki w pomiarze — napraw przed optymalizacją.", "upsellio")),
    ];
}

/**
 * @param array<string, mixed> $agg
 * @return array<int, array<string, mixed>>
 */
function ups_audit_intel_build_alerts(array $agg, array $setup = []): array
{
    $alerts = [];
    $deltas = (array) ($agg["deltas"] ?? []);
    $derived = (array) ($agg["derived"] ?? []);

    $cpa = (float) ($derived["ads_cpa"] ?? 0);
    $cpa_prev = 0.0;
    $ads_cost_prev = (float) ($agg["ads_cost_prev"] ?? 0);
    $ads_conv_prev = (float) ($agg["ads_conversions_prev"] ?? 0);
    if ($ads_conv_prev > 0) {
        $cpa_prev = round($ads_cost_prev / $ads_conv_prev, 2);
    }
    if ($cpa > 0 && $cpa_prev > 0) {
        $cpa_delta = round((($cpa - $cpa_prev) / $cpa_prev) * 100, 1);
        if ($cpa_delta >= 25) {
            $alerts[] = [
                "id" => "cpa_spike",
                "severity" => $cpa_delta >= 37 ? "critical" : "warning",
                "category" => "ads",
                "title" => __("CPA wzrósł", "upsellio"),
                "message" => sprintf("CPA wzrósł o %.0f%% (z %.0f na %.0f PLN).", $cpa_delta, $cpa_prev, $cpa),
                "action" => __("Sprawdź search terms, wyklucz słabe frazy, popraw landing.", "upsellio"),
                "metric" => "cpa",
                "delta_pct" => $cpa_delta,
            ];
        }
    }

    $gsc_delta = (float) ($deltas["gsc_clicks"] ?? 0);
    if ($gsc_delta <= -20) {
        $alerts[] = [
            "id" => "gsc_drop",
            "severity" => $gsc_delta <= -35 ? "critical" : "warning",
            "category" => "seo",
            "title" => __("Spadek kliknięć organicznych", "upsellio"),
            "message" => sprintf("Kliknięcia GSC spadły o %.0f%%.", abs($gsc_delta)),
            "action" => __("Sprawdź indeksację i pozycje kluczowych fraz.", "upsellio"),
            "metric" => "gsc_clicks",
            "delta_pct" => $gsc_delta,
        ];
    }

    $seo_alert_count = 0;
    foreach ((array) ($agg["keyword_position_changes"] ?? []) as $chg) {
        if (!is_array($chg)) {
            continue;
        }
        $keyword = (string) ($chg["keyword"] ?? "");
        $prev_pos = (float) ($chg["position_prev"] ?? 0);
        $cur_pos = (float) ($chg["position"] ?? 0);
        $delta = (float) ($chg["position_delta"] ?? 0);
        $impr = (int) ($chg["impressions"] ?? 0);
        if ($keyword === "" || $delta < 4 || $prev_pos <= 0 || $cur_pos <= 0 || $impr < 50) {
            continue;
        }
        if ($prev_pos > 20) {
            continue;
        }
        $severity = ($delta >= 6 || ($prev_pos <= 10 && $cur_pos >= 15)) ? "critical" : "warning";
        $alerts[] = [
            "id" => "kw_drop_" . md5($keyword),
            "severity" => $severity,
            "category" => "seo",
            "title" => sprintf('Spadek pozycji: „%s”', $keyword),
            "message" => sprintf(
                "Fraza spadła z poz. %.0f na %.0f (Δ+%.0f) przy %d wyśw.",
                $prev_pos,
                $cur_pos,
                $delta,
                $impr
            ),
            "action" => __("Sprawdź konkurencję, treść i linkowanie — odśwież sekcję pod tę frazę.", "upsellio"),
            "metric" => "position_drop",
            "delta_pct" => $delta,
        ];
        $seo_alert_count++;
        if ($seo_alert_count >= 5) {
            break;
        }
    }

    if ($seo_alert_count < 3) {
        foreach ((array) ($agg["top_keywords"] ?? []) as $kw) {
            if (!is_array($kw)) {
                continue;
            }
            $pos = (float) ($kw["position"] ?? 0);
            $impr = (int) ($kw["impressions"] ?? 0);
            $keyword = (string) ($kw["keyword"] ?? "");
            $delta = (float) ($kw["position_delta"] ?? 0);
            if ($keyword === "" || $impr < 50 || $delta >= 4) {
                continue;
            }
            if ($pos >= 11 && $pos <= 16 && $impr >= 100) {
                $alerts[] = [
                    "id" => "kw_position_" . md5($keyword),
                    "severity" => "warning",
                    "category" => "seo",
                    "title" => sprintf('Fraza „%s” na granicy TOP 10', $keyword),
                    "message" => sprintf("Pozycja ~%.0f przy %d wyświetleniach.", $pos, $impr),
                    "action" => __("Rozbuduj treść i linkowanie wewnętrzne na tej frazie.", "upsellio"),
                    "metric" => "position",
                    "delta_pct" => null,
                ];
                $seo_alert_count++;
                if ($seo_alert_count >= 5) {
                    break;
                }
            }
        }
    }

    $clarity_sess = (int) ($agg["clarity_sessions"] ?? 0);
    $clarity_rage = (int) ($agg["clarity_rage_clicks"] ?? 0);
    if ($clarity_sess > 20 && $clarity_rage > 0) {
        $rage_ratio = round(($clarity_rage / max(1, $clarity_sess)) * 100, 1);
        if ($rage_ratio >= 5) {
            $alerts[] = [
                "id" => "rage_clicks",
                "severity" => $rage_ratio >= 10 ? "critical" : "warning",
                "category" => "ux",
                "title" => __("Wysoki wskaźnik rage clicks", "upsellio"),
                "message" => sprintf("Rage clicks: %d (%.1f%% sesji Clarity).", $clarity_rage, $rage_ratio),
                "action" => __("Sprawdź CTA, formularze i elementy interaktywne na top stronach.", "upsellio"),
                "metric" => "rage_clicks",
                "delta_pct" => null,
            ];
        }
    }

    $top_ch = (array) ($agg["channels"] ?? []);
    if ($top_ch !== [] && is_array($top_ch[0])) {
        $first = $top_ch[0];
        $source = (string) ($first["source"] ?? "");
        $medium = (string) ($first["medium"] ?? "");
        if (function_exists("ups_audit_ga4_is_not_set_source")
            && ups_audit_ga4_is_not_set_source($source, $medium)
            && (int) ($first["sessions"] ?? 0) >= 50) {
            $total = (int) ($agg["ga4_sessions"] ?? 0);
            $share = $total > 0 ? round(((int) $first["sessions"] / $total) * 100, 1) : 0;
            $alerts[] = [
                "id" => "ga4_not_set_dominant",
                "severity" => $share >= 40 ? "critical" : "warning",
                "category" => "tracking",
                "title" => __("Dominujący kanał: (not set)", "upsellio"),
                "message" => sprintf(
                    "%d sesji (%.0f%%) bez atrybucji source/medium.",
                    (int) ($first["sessions"] ?? 0),
                    $share
                ),
                "action" => __("Napraw GTM, UTM i consent mode — bez tego dane Ads/SEO są bezużyteczne.", "upsellio"),
                "metric" => "ga4_not_set",
                "delta_pct" => null,
            ];
        }
    }

    $conv_delta = (float) ($deltas["ga4_conversions"] ?? 0);
    if ($conv_delta <= -30 && (int) ($agg["ga4_sessions"] ?? 0) >= 50) {
        $alerts[] = [
            "id" => "leads_drop",
            "severity" => $conv_delta <= -42 ? "critical" : "warning",
            "category" => "leads",
            "title" => __("Spadek konwersji / leadów", "upsellio"),
            "message" => sprintf("Konwersje GA4 spadły o %.0f%%.", abs($conv_delta)),
            "action" => __("Sprawdź formularze, tracking i zmiany na landingach.", "upsellio"),
            "metric" => "ga4_conversions",
            "delta_pct" => $conv_delta,
        ];
    }

    $tracking = ups_audit_intel_tracking_health($agg, $setup);
    if ((int) ($tracking["score"] ?? 100) < 45) {
        $alerts[] = [
            "id" => "tracking_critical",
            "severity" => "critical",
            "category" => "tracking",
            "title" => __("Krytyczny Tracking Health Score", "upsellio"),
            "message" => sprintf("Wynik pomiaru: %d/100.", (int) $tracking["score"]),
            "action" => __("Napraw GTM/GA4 zanim optymalizujesz kampanie.", "upsellio"),
            "metric" => "tracking_health",
            "delta_pct" => null,
        ];
    }

    $st_intel = ups_audit_intel_search_terms($agg);
    if ((float) ($st_intel["waste_pct"] ?? 0) >= 25 && (float) ($st_intel["waste_cost"] ?? 0) >= 100) {
        $alerts[] = [
            "id" => "ads_waste",
            "severity" => "warning",
            "category" => "ads",
            "title" => __("Budżet na frazy bez konwersji", "upsellio"),
            "message" => (string) ($st_intel["summary"] ?? ""),
            "action" => __("Dodaj wykluczenia negatywne w Google Ads.", "upsellio"),
            "metric" => "search_terms_waste",
            "delta_pct" => (float) ($st_intel["waste_pct"] ?? 0),
        ];
    }

    $prio = ["critical" => 0, "warning" => 1, "info" => 2];
    usort($alerts, static function ($a, $b) use ($prio) {
        $pa = $prio[$a["severity"] ?? "info"] ?? 9;
        $pb = $prio[$b["severity"] ?? "info"] ?? 9;

        return $pa <=> $pb;
    });

    return array_slice($alerts, 0, 15);
}

/**
 * @param array<string, mixed> $agg
 * @return array<int, array<string, mixed>>
 */
function ups_audit_intel_seo_clusters(array $agg, array $content): array
{
    $rows = ups_audit_intel_seo_cluster_input_rows($agg, $content);

    return function_exists("ups_audit_cluster_seo_keywords")
        ? ups_audit_cluster_seo_keywords($rows)
        : [];
}

/**
 * Jedna fraza = jeden rekord (bez duplikatów z content + opportunities).
 *
 * @return list<array<string, mixed>>
 */
function ups_audit_intel_seo_cluster_input_rows(array $agg, array $content): array
{
    $map = [];
    foreach (array_merge((array) ($content["rows"] ?? []), (array) ($agg["opportunities"] ?? [])) as $row) {
        if (!is_array($row)) {
            continue;
        }
        $keyword = trim((string) ($row["keyword"] ?? ""));
        if ($keyword === "") {
            continue;
        }
        $key = mb_strtolower($keyword);
        if (!isset($map[$key])) {
            $map[$key] = $row;
            continue;
        }
        foreach (["impressions", "clicks", "potential_clicks"] as $metric) {
            $map[$key][$metric] = max((int) ($map[$key][$metric] ?? 0), (int) ($row[$metric] ?? 0));
        }
        $pos = (float) ($row["position"] ?? 0);
        if ($pos > 0 && ((float) ($map[$key]["position"] ?? 0) <= 0 || $pos < (float) $map[$key]["position"])) {
            $map[$key]["position"] = $pos;
        }
    }

    return array_values($map);
}

function ups_audit_intel_seo_roadmap(array $agg, array $setup = []): array
{
    $tasks = [];
    $content = ups_audit_intel_content_potential($agg, (array) ($agg["benchmark"] ?? []));
    $clusters = ups_audit_intel_seo_clusters($agg, $content);
    foreach (array_slice($clusters, 0, 4) as $i => $cluster) {
        if (!is_array($cluster)) {
            continue;
        }
        $variants = (array) ($cluster["keywords"] ?? []);
        $tasks[] = [
            "priority" => $i < 2 ? "high" : "medium",
            "category" => "seo_cluster",
            "title" => sprintf('Cluster: %s', (string) ($cluster["label"] ?? "")),
            "detail" => sprintf(
                "+%d klik. potencjału · %d wyśw. · %d wariantów",
                (int) ($cluster["potential_clicks"] ?? 0),
                (int) ($cluster["impressions"] ?? 0),
                count($variants)
            ),
            "effort" => "medium",
            "impact" => (int) ($cluster["potential_clicks"] ?? 0) >= 50 ? "high" : "medium",
            "action" => count($variants) > 1
                ? __("Jedna strona hub + warianty w H2/H3 zamiast osobnych landingów.", "upsellio")
                : (string) (($content["rows"][0]["action"] ?? "") ?: __("Rozbuduj treść i CTR.", "upsellio")),
            "variants" => array_slice($variants, 0, 6),
        ];
    }

    foreach (array_slice((array) ($agg["keyword_position_changes"] ?? []), 0, 4) as $chg) {
        if (!is_array($chg)) {
            continue;
        }
        $delta = (float) ($chg["position_delta"] ?? 0);
        if ($delta < 4) {
            continue;
        }
        $tasks[] = [
            "priority" => $delta >= 6 ? "high" : "medium",
            "category" => "seo_position_drop",
            "title" => sprintf('Spadek: „%s”', (string) ($chg["keyword"] ?? "")),
            "detail" => sprintf(
                "Z poz. %.0f na %.0f (Δ+%.0f) · %d wyśw.",
                (float) ($chg["position_prev"] ?? 0),
                (float) ($chg["position"] ?? 0),
                $delta,
                (int) ($chg["impressions"] ?? 0)
            ),
            "effort" => "medium",
            "impact" => $delta >= 6 ? "high" : "medium",
            "action" => __("Audyt treści i linków dla tej frazy — przywróć pozycję z poprzedniego okresu.", "upsellio"),
        ];
    }

    $tech = (array) ($agg["technical"] ?? []);
    $idx = (array) ($tech["indexation"] ?? []);
    if (isset($idx["ratio"]) && $idx["ratio"] !== null && (int) $idx["ratio"] < 70 && (int) ($idx["pages_sampled"] ?? 0) > 0) {
        $tasks[] = [
            "priority" => "high",
            "category" => "technical",
            "title" => __("Indeksacja GSC", "upsellio"),
            "detail" => sprintf("Tylko %d%% URL w indeksie (%d próbek).", (int) $idx["ratio"], (int) ($idx["pages_sampled"] ?? 0)),
            "effort" => "medium",
            "impact" => "high",
            "action" => __("Napraw noindex, canonical i sitemap.", "upsellio"),
        ];
    }

    $prio_order = ["high" => 0, "medium" => 1, "low" => 2];
    usort($tasks, static function ($a, $b) use ($prio_order) {
        return ($prio_order[$a["priority"] ?? "low"] ?? 9) <=> ($prio_order[$b["priority"] ?? "low"] ?? 9);
    });

    return array_slice($tasks, 0, 10);
}

/**
 * @param array<string, mixed> $agg
 * @return array<string, mixed>
 */
function ups_audit_intel_ux_audit(array $agg): array
{
    $issues = [];
    $sess = (int) ($agg["clarity_sessions"] ?? 0);
    $dead = (int) ($agg["clarity_dead_clicks"] ?? 0);
    $rage = (int) ($agg["clarity_rage_clicks"] ?? 0);
    $scroll = (float) ($agg["clarity_scroll_depth"] ?? 0);
    $eng = (float) ($agg["clarity_engagement_sec"] ?? 0);

    if ($sess > 0) {
        if ($dead > 0 && ($dead / $sess) >= 0.5) {
            $issues[] = [
                "severity" => "high",
                "title" => __("Dead clicks na interaktywnych elementach", "upsellio"),
                "detail" => sprintf("%d dead clicks / %d sesji — przyciski mogą nie działać.", $dead, $sess),
                "fix" => __("Sprawdź overlay, z-index i stany disabled na CTA.", "upsellio"),
            ];
        }
        if ($rage > 0 && ($rage / $sess) >= 0.05) {
            $issues[] = [
                "severity" => "high",
                "title" => __("Rage clicks", "upsellio"),
                "detail" => sprintf("%d rage clicks — frustracja użytkowników.", $rage),
                "fix" => __("Zweryfikuj formularze, ładowanie i responsywność.", "upsellio"),
            ];
        }
        if ($scroll > 0 && $scroll < 40) {
            $issues[] = [
                "severity" => "medium",
                "title" => __("Niski scroll depth", "upsellio"),
                "detail" => sprintf("Śr. scroll %.0f%% — treść poniżej foldu nie jest czytana.", $scroll),
                "fix" => __("Przenieś kluczowy CTA wyżej, skróć intro.", "upsellio"),
            ];
        }
        if ($eng > 0 && $eng < 30) {
            $issues[] = [
                "severity" => "medium",
                "title" => __("Krótki czas zaangażowania", "upsellio"),
                "detail" => sprintf("Śr. %.0f s na sesję.", $eng),
                "fix" => __("Popraw dopasowanie landing ↔ intencja reklamy.", "upsellio"),
            ];
        }
    }

    $top_pages = [];
    foreach ((array) ($agg["clarity_top_pages"] ?? []) as $pg) {
        if (!is_array($pg)) {
            continue;
        }
        $top_pages[] = [
            "url" => (string) ($pg["url"] ?? $pg["page"] ?? ""),
            "sessions" => (int) ($pg["sessions"] ?? $pg["count"] ?? 0),
            "dead_clicks" => (int) ($pg["dead_clicks"] ?? 0),
        ];
        if (count($top_pages) >= 5) {
            break;
        }
    }

    $clarity_conf = function_exists("ups_audit_clarity_confidence")
        ? ups_audit_clarity_confidence($agg)
        : ["score" => 0, "band" => "none", "label" => ""];
    $clarity_low = (string) ($clarity_conf["band"] ?? "") === "low";

    $score = $clarity_low ? 50 : 80;
    foreach ($issues as $iss) {
        $penalty = (string) ($iss["severity"] ?? "") === "high" ? 15 : 8;
        $score -= $clarity_low ? (int) round($penalty * 0.6) : $penalty;
    }
    if ($clarity_low) {
        $score = min($score, 45);
    }
    $score = max(0, min(100, $score));

    return [
        "has_data" => $sess > 0,
        "score" => $score,
        "clarity_confidence" => $clarity_conf,
        "clarity_low_confidence" => $clarity_low,
        "issues" => $issues,
        "top_pages" => $top_pages,
        "summary" => $sess <= 0
            ? __("Podłącz Microsoft Clarity dla audytu UX.", "upsellio")
            : ($clarity_low
                ? sprintf(
                    "%s — %s",
                    (string) ($clarity_conf["label"] ?? "Confidence: Low"),
                    __("UX Score ma obniżoną wagę do weryfikacji API.", "upsellio")
                )
                : ($issues === []
                    ? __("UX bez krytycznych problemów w ostatnim oknie Clarity.", "upsellio")
                    : sprintf("%d problemów UX do naprawy.", count($issues)))),
    ];
}

/**
 * Jednozdaniowe podsumowanie dla właściciela (reguły, bez czekania na AI).
 *
 * @param array<string, mixed> $agg
 * @param array<string, mixed> $tracking
 * @param array<string, mixed> $content
 * @param array<string, mixed> $search_terms
 *
 * @return array<string, mixed>
 */
function ups_audit_intel_executive_summary(array $agg, array $setup, array $tracking, array $content, array $search_terms): array
{
    $client_name = "";
    $segments = [];
    $sessions = (int) ($agg["ga4_sessions"] ?? 0);
    $not_set = (float) ($agg["ga4_not_set_pct"] ?? 0);
    $gsc_clicks = (int) ($agg["gsc_clicks"] ?? 0);
    $gsc_delta = (float) (($agg["deltas"] ?? [])["gsc_clicks"] ?? 0);

    if ($sessions >= 500) {
        $segments[] = "generuje wystarczający ruch";
    } elseif ($sessions >= 100) {
        $segments[] = "ma umiarkowany ruch";
    } else {
        $segments[] = "ma ograniczony ruch w GA4";
    }

    $problems = [];
    if ($not_set >= 30) {
        $problems[] = sprintf("problemy z atrybucją (%.0f%% not set)", $not_set);
    }
    if ((int) ($tracking["score"] ?? 100) < 55) {
        $problems[] = "słaby Tracking Health";
    }
    if ($gsc_delta <= -20 || $gsc_clicks < 40) {
        $problems[] = "słaba widoczność SEO";
    }
    $ads_cpa = ups_audit_intel_ads_channel_cpa($agg);
    if (!empty($ads_cpa["has_data"]) && (float) ($ads_cpa["pmax_cpa"] ?? 0) > 0
        && (float) ($ads_cpa["search_cpa"] ?? 0) > 0
        && (float) $ads_cpa["pmax_cpa"] > (float) $ads_cpa["search_cpa"] * 1.12) {
        $problems[] = "niższa efektywność PMax względem Search";
    }
    if ((float) ($search_terms["waste_pct"] ?? 0) >= 25) {
        $problems[] = "przepalanie budżetu na frazy bez konwersji";
    }
    $revenue_q = function_exists("ups_audit_revenue_quality") ? ups_audit_revenue_quality($agg) : ["trusted" => true];
    if (empty($revenue_q["trusted"])) {
        $problems[] = "zawyżony przychód GA4 — nie ufaj ROAS e-commerce";
    }

    $potential = [];
    if ((int) ($content["total_potential_clicks"] ?? 0) >= 50) {
        $potential[] = "rozbudowie stron SEO blisko TOP10";
    }
    if ($not_set >= 25) {
        $potential[] = "naprawie pomiaru GTM/UTM";
    }
    if ((float) ($search_terms["waste_pct"] ?? 0) >= 20) {
        $potential[] = "wykluczeniach słabych search terms";
    }

    $text = "";
    if ($problems === [] && $potential === []) {
        $text = "Profil utrzymuje stabilne KPI — kontynuuj optymalizację SEO i kampanii paid.";
    } else {
        $lead = $segments !== [] ? ucfirst($segments[0]) : "Profil";
        $text = $lead;
        if ($problems !== []) {
            $text .= ", ale " . implode(", ", $problems) . " ograniczają wzrost.";
        } else {
            $text .= " — bez krytycznych alertów w tym oknie.";
        }
        if ($potential !== []) {
            $text .= " Największy potencjał: " . implode(" oraz ", array_slice($potential, 0, 2)) . ".";
        }
    }

    return [
        "text" => $text,
        "problems" => $problems,
        "potential" => $potential,
    ];
}

/**
 * @param array<string, mixed> $agg
 * @param array<string, mixed> $tracking
 * @param array<string, mixed> $content
 * @param array<string, mixed> $search_terms
 *
 * @return array<string, mixed>
 */
function ups_audit_intel_opportunity_score(array $agg, array $tracking, array $content, array $search_terms): array
{
    $score = 30;
    $drivers = [];

    $potential = (int) ($content["total_potential_clicks"] ?? 0);
    if ($potential > 0) {
        $seo_pts = min(22, (int) round($potential / 12));
        $score += $seo_pts;
        $drivers[] = "+{$seo_pts} SEO (+{$potential} klik. potencjału)";
    }

    $not_set = (float) ($agg["ga4_not_set_pct"] ?? 0);
    if ($not_set >= 25) {
        $score += 14;
        $drivers[] = "+14 naprawa atrybucji (duży upside po fixie)";
    }

    if ((float) ($search_terms["waste_pct"] ?? 0) >= 20) {
        $score += 10;
        $drivers[] = "+10 odzysk budżetu Ads (waste " . (float) $search_terms["waste_pct"] . "%)";
    }

    if (count((array) ($agg["keyword_position_changes"] ?? [])) > 0) {
        $score += 8;
        $drivers[] = "+8 spadki pozycji do odzyskania";
    }

    $health = (int) ($tracking["score"] ?? 100);
    if ($health < 60 && (int) ($agg["ga4_sessions"] ?? 0) >= 200) {
        $score += 12;
        $drivers[] = "+12 duży ruch przy słabym trackingu";
    }

    if ((int) ($agg["gsc_clicks"] ?? 0) < 60 && (int) ($content["total_potential_clicks"] ?? 0) >= 80) {
        $score += 10;
        $drivers[] = "+10 niski SEO vs wysoki potencjał treści";
    }

    $health_score = (int) ($agg["health_score"] ?? 0);
    $health_penalty = max(0, (50 - $health_score) * 0.45);
    $tracking_penalty = max(0, ($not_set - 15) * 0.35);
    $score = (int) round($score - $health_penalty - $tracking_penalty);
    $score = max(0, min(95, $score));
    if ($health_score > 0 && $health_score < 50) {
        $score = min($score, 88);
    }
    if ($not_set >= 40) {
        $score = min($score, 90);
    }
    $label = function_exists("ups_audit_opportunity_score_label")
        ? ups_audit_opportunity_score_label($score)
        : ($score >= 61 ? "wysoki" : "średni");

    return [
        "score" => $score,
        "label" => $label,
        "drivers" => $drivers,
        "summary" => $score >= 81
            ? "Bardzo wysoki potencjał — ale najpierw napraw tracking, inaczej ROI będzie zafałszowany."
            : ($score >= 61
                ? "Wysoki potencjał wzrostu — priorytet: szybkie wygrane (SEO + tracking + waste Ads)."
                : "Umiarkowany potencjał — najpierw ustabilizuj pomiar i główne KPI."),
    ];
}

/**
 * Search vs PMax — CPA z kampanii Ads.
 *
 * @param array<string, mixed> $agg
 *
 * @return array<string, mixed>
 */
function ups_audit_intel_ads_channel_cpa(array $agg): array
{
    $search = ["cost" => 0.0, "conv" => 0.0, "clicks" => 0];
    $pmax = ["cost" => 0.0, "conv" => 0.0, "clicks" => 0];

    foreach ((array) ($agg["campaigns"] ?? []) as $camp) {
        if (!is_array($camp)) {
            continue;
        }
        $type = strtoupper((string) ($camp["type"] ?? ""));
        $name = strtolower((string) ($camp["name"] ?? ""));
        $cost = (float) ($camp["cost"] ?? $camp["cost_pln"] ?? 0);
        $conv = (float) ($camp["conversions"] ?? 0);
        $clicks = (int) ($camp["clicks"] ?? 0);
        $is_pmax = strpos($type, "PERFORMANCE") !== false
            || strpos($name, "pmax") !== false
            || strpos($name, "performance max") !== false;
        if (!$is_pmax && strpos($type, "SEARCH") === false && strpos($name, "search") === false) {
            continue;
        }
        if ($is_pmax) {
            $pmax["cost"] += $cost;
            $pmax["conv"] += $conv;
            $pmax["clicks"] += $clicks;
        } else {
            $search["cost"] += $cost;
            $search["conv"] += $conv;
            $search["clicks"] += $clicks;
        }
    }

    $search_cpa = $search["conv"] > 0 ? round($search["cost"] / $search["conv"], 0) : 0.0;
    $pmax_cpa = $pmax["conv"] > 0 ? round($pmax["cost"] / $pmax["conv"], 0) : 0.0;

    return [
        "has_data" => $search["cost"] > 0 || $pmax["cost"] > 0,
        "search_cpa" => $search_cpa,
        "pmax_cpa" => $pmax_cpa,
        "search_cost" => round($search["cost"], 0),
        "pmax_cost" => round($pmax["cost"], 0),
        "search_conv" => round($search["conv"], 1),
        "pmax_conv" => round($pmax["conv"], 1),
        "summary" => $search_cpa > 0 && $pmax_cpa > 0
            ? sprintf(
                "Search CPA %s zł vs PMax CPA %s zł.",
                number_format($search_cpa, 0, ",", " "),
                number_format($pmax_cpa, 0, ",", " ")
            )
            : "",
    ];
}

/**
 * @param array<string, mixed> $agg
 * @return array<string, mixed>
 */
function ups_audit_intel_customer_journey(array $agg, int $client_id = 0, int $days = 30): array
{
    $sessions = (int) ($agg["ga4_sessions"] ?? 0);
    $revenue = (float) ($agg["ga4_revenue"] ?? 0);
    $paid_clicks = (int) ($agg["ads_clicks"] ?? 0);
    $paid_sessions = function_exists("ups_audit_ga4_paid_sessions")
        ? ups_audit_ga4_paid_sessions($agg)
        : 0;
    $landing = $paid_sessions > 0 ? $paid_sessions : $sessions;

    $form_start = function_exists("ups_audit_ga4_event_count")
        ? ups_audit_ga4_event_count($agg, ["form_start", "form_begin"])
        : 0;
    $form_submit = function_exists("ups_audit_ga4_event_count")
        ? ups_audit_ga4_event_count($agg, ["form_submit", "submit_form", "contact_form"])
        : 0;
    $phone = function_exists("ups_audit_ga4_event_count")
        ? ups_audit_ga4_event_count($agg, ["phone_click", "click_phone", "tel_click", "phone", "call_click"])
        : 0;

    $crm = function_exists("ups_audit_collect_client_crm_funnel")
        ? ups_audit_collect_client_crm_funnel($client_id, $days)
        : ["has_data" => false, "leads" => 0, "offers_sent" => 0, "won" => 0, "revenue" => 0.0];
    $crm_leads = (int) ($crm["leads"] ?? 0);
    $crm_offers = (int) ($crm["offers_sent"] ?? 0);
    $crm_won = (int) ($crm["won"] ?? 0);
    $crm_revenue = (float) ($crm["revenue"] ?? 0);

    $rate = static function (int $from, int $to): ?float {
        if ($from <= 0 || $to <= 0) {
            return null;
        }

        return round(min(100, ($to / $from) * 100), 1);
    };

    $stages = [];
    if ($paid_clicks > 0) {
        $stages[] = ["name" => "Klik. Ads", "value" => $paid_clicks, "rate_to_next" => $rate($paid_clicks, $landing), "source" => "ads"];
    }
    $stages[] = [
        "name" => "Landing (sesje paid)",
        "value" => $landing,
        "rate_to_next" => $rate($landing, max($form_start, $form_submit, $phone, $crm_leads)),
        "source" => "ga4",
    ];
    if ($form_start > 0) {
        $stages[] = ["name" => "Form Start", "value" => $form_start, "rate_to_next" => $rate($form_start, $form_submit > 0 ? $form_submit : $crm_leads), "source" => "ga4"];
    }
    if ($form_submit > 0) {
        $stages[] = ["name" => "Form Submit", "value" => $form_submit, "rate_to_next" => $crm_leads > 0 ? $rate($form_submit, $crm_leads) : null, "source" => "ga4"];
    }
    if ($phone > 0) {
        $stages[] = ["name" => "Klik telefon", "value" => $phone, "rate_to_next" => $crm_leads > 0 ? $rate($phone, $crm_leads) : null, "source" => "ga4"];
    }
    if ($crm_leads > 0) {
        $stages[] = ["name" => "Lead (CRM)", "value" => $crm_leads, "rate_to_next" => $crm_offers > 0 ? $rate($crm_leads, $crm_offers) : null, "source" => "crm"];
    }
    if ($crm_offers > 0) {
        $stages[] = ["name" => "Oferta (CRM)", "value" => $crm_offers, "rate_to_next" => $crm_won > 0 ? $rate($crm_offers, $crm_won) : null, "source" => "crm"];
    }
    if ($crm_won > 0) {
        $stages[] = ["name" => "Wygrana (CRM)", "value" => $crm_won, "rate_to_next" => $crm_revenue > 0 ? round($crm_revenue / max(1, $crm_won), 0) : null, "source" => "crm"];
    }
    if ($crm_revenue > 0) {
        $stages[] = ["name" => "Przychód CRM (PLN)", "value" => round($crm_revenue, 0), "rate_to_next" => null, "source" => "crm"];
    } elseif ($revenue > 0) {
        $purchase = function_exists("ups_audit_ga4_event_count")
            ? ups_audit_ga4_event_count($agg, ["purchase", "ecommerce_purchase"])
            : 0;
        if ($purchase > 0) {
            $stages[] = ["name" => "Zamówienie e-com", "value" => $purchase, "rate_to_next" => round($revenue / max(1, $purchase), 0), "source" => "ga4"];
        }
        $stages[] = ["name" => "Przychód GA4 (PLN)", "value" => round($revenue, 0), "rate_to_next" => null, "source" => "ga4"];
    }

    $tracking = ups_audit_intel_tracking_health($agg, []);
    $channels = [];
    foreach (array_slice((array) ($agg["channels"] ?? []), 0, 6) as $ch) {
        if (!is_array($ch)) {
            continue;
        }
        $ch_sess = (int) ($ch["sessions"] ?? 0);
        $channels[] = [
            "channel" => trim((string) ($ch["source"] ?? "") . " / " . (string) ($ch["medium"] ?? "")),
            "sessions" => $ch_sess,
            "conversions" => (int) ($ch["conversions"] ?? 0),
            "cr" => $ch_sess > 0 ? round(((int) ($ch["conversions"] ?? 0) / $ch_sess) * 100, 2) : 0,
        ];
    }

    $missing = [];
    if ($phone <= 0) {
        $missing[] = "phone_click";
    }
    if ($form_start <= 0 && $form_submit <= 0) {
        $missing[] = "form_start / form_submit";
    }
    if ($crm_leads <= 0) {
        $missing[] = "leady CRM w okresie";
    }

    $offline_note = !empty($crm["has_data"])
        ? __("Dolna część lejka (Lead → Oferta → Wygrana → Przychód) z CRM Upsellio. Góra lejka z GA4/Ads.", "upsellio")
        : __("Lejek górny z GA4 — podłącz leady CRM (UTM), aby zobaczyć pełny B2B funnel.", "upsellio");

    return [
        "stages" => $stages,
        "channels" => $channels,
        "reliable" => !empty($tracking["ready_for_journey"]) || !empty($crm["has_data"]),
        "has_crm" => !empty($crm["has_data"]),
        "offline_note" => $offline_note,
        "missing_events" => $missing,
        "note" => !empty($tracking["ready_for_journey"])
            ? ($missing !== [] ? "Brak eventów: " . implode(", ", $missing) . " — rozważ tagowanie w GTM." : "")
            : (string) ($tracking["summary"] ?? ""),
    ];
}

/**
 * @param array<string, mixed> $agg
 * @return array<string, mixed>
 */
function ups_audit_intel_product_analytics(array $agg): array
{
    $events = (array) ($agg["ga4_events_diagnostics"] ?? $agg["ga4_events_breakdown"] ?? []);
    $ecom_events = ["view_item", "add_to_cart", "begin_checkout", "purchase", "add_to_wishlist"];
    $rows = [];

    foreach ((array) $events as $ev) {
        if (!is_array($ev)) {
            continue;
        }
        $name = strtolower((string) ($ev["event"] ?? ""));
        $kind = (string) ($ev["kind"] ?? "");
        if (!in_array($name, $ecom_events, true) && $kind !== "macro") {
            continue;
        }
        if ($name === "" || (int) ($ev["count"] ?? 0) <= 0) {
            continue;
        }
        $rows[$name] = [
            "event" => $name,
            "count" => (int) ($ev["count"] ?? 0),
            "bucket" => $kind,
        ];
    }

    $view = (int) ($rows["view_item"]["count"] ?? 0);
    $cart = (int) ($rows["add_to_cart"]["count"] ?? 0);
    $checkout = (int) ($rows["begin_checkout"]["count"] ?? 0);
    $purchase = (int) ($rows["purchase"]["count"] ?? 0);

    $funnel = [];
    if ($view > 0) {
        $funnel[] = ["step" => "view_item", "count" => $view, "rate" => 100];
        if ($cart > 0) {
            $funnel[] = ["step" => "add_to_cart", "count" => $cart, "rate" => round(($cart / $view) * 100, 1)];
        }
        if ($checkout > 0) {
            $funnel[] = ["step" => "begin_checkout", "count" => $checkout, "rate" => round(($checkout / $view) * 100, 1)];
        }
        if ($purchase > 0) {
            $funnel[] = ["step" => "purchase", "count" => $purchase, "rate" => round(($purchase / $view) * 100, 1)];
        }
    }

    return [
        "has_ecommerce" => $view > 0 || $purchase > 0,
        "events" => array_values($rows),
        "funnel" => $funnel,
        "revenue" => (float) ($agg["ga4_revenue"] ?? 0),
        "summary" => $purchase > 0
            ? sprintf(
                "%d zakupów (ecommercePurchases) · purchaseRevenue %.0f PLN.",
                (int) ($agg["ga4_purchase_count"] ?? $purchase),
                (float) ($agg["ga4_revenue"] ?? 0)
            )
            : ($view > 0
                ? __("Lejek e-commerce częściowy — brak purchase w okresie.", "upsellio")
                : __("Brak eventów e-commerce — sprawdź GA4 enhanced ecommerce.", "upsellio")),
    ];
}

/**
 * @param array<string, mixed> $agg
 * @return array<string, mixed>
 */
function ups_audit_intel_profit_dashboard(array $agg, int $client_id, array $crm_revenue = [], array $revenue_quality = []): array
{
    $client_id = (int) $client_id;
    $margin_pct = (float) get_post_meta($client_id, "_ups_client_profit_margin_pct", true);
    if ($margin_pct <= 0 || $margin_pct > 100) {
        $margin_pct = 30.0;
    }

    if ($revenue_quality === [] && function_exists("ups_audit_revenue_quality")) {
        $revenue_quality = ups_audit_revenue_quality($agg);
    }
    $ga4_untrusted = empty($revenue_quality["trusted"]);
    $crm_funnel = (array) ($crm_revenue["funnel_totals"] ?? []);
    $crm_rev = (float) ($crm_funnel["revenue"] ?? 0);

    $revenue = $ga4_untrusted ? $crm_rev : (float) ($agg["ga4_revenue"] ?? 0);
    $revenue_source = $ga4_untrusted ? "crm" : "ga4_purchase";
    $ads_cost = (float) ($agg["paid_cost"] ?? ($agg["ads_cost"] ?? 0) + ($agg["meta_cost"] ?? 0));
    $gross_profit = round($revenue * ($margin_pct / 100), 2);
    $net_profit = round($gross_profit - $ads_cost, 2);
    $roas_profit = $ads_cost > 0 && !$ga4_untrusted ? round($gross_profit / $ads_cost, 2) : 0.0;
    $crm_roas = $ads_cost > 0 && $crm_rev > 0 ? round($crm_rev / $ads_cost, 2) : 0.0;
    $conv = (int) ($agg["ga4_conversions"] ?? 0);
    $profit_per_conv = $conv > 0 && !$ga4_untrusted ? round($net_profit / $conv, 2) : 0.0;

    $warning = "";
    if ($ga4_untrusted) {
        $warning = (string) ($revenue_quality["message"] ?? "Revenue Quality Warning — używaj ROAS CRM zamiast GA4.");
    } elseif ($net_profit < 0) {
        $warning = "Ujemny zysk w GA4 nie oznacza straty firmy — większość konwersji B2B może nie trafiać do purchase.";
    }

    return [
        "phase" => 3,
        "margin_pct" => $margin_pct,
        "revenue" => $revenue,
        "revenue_source" => $revenue_source,
        "crm_revenue" => $crm_rev,
        "crm_roas" => $crm_roas,
        "ga4_untrusted" => $ga4_untrusted,
        "gross_profit" => $gross_profit,
        "ads_cost" => $ads_cost,
        "net_profit" => $net_profit,
        "roas_profit" => $roas_profit,
        "profit_per_conversion" => $profit_per_conv,
        "configurable" => true,
        "reliability" => $ga4_untrusted ? "crm_preferred" : "low",
        "disclaimer" => $ga4_untrusted
            ? "Przychód GA4 wyłączony z wniosków — poniżej szacunek na bazie CRM (wygrane + wartość ofert)."
            : "Szacunek e-commerce — nie uwzględnia sprzedaży offline, telefonów ani zamówień B2B z opóźnieniem.",
        "warning" => $warning,
        "note" => $ga4_untrusted
            ? "ROAS CRM jest metryką decyzyjną dla B2B. GA4 purchase pozostaje w diagnostyce technicznej."
            : "Tylko przychód z eventu purchase w GA4. Dla WTapes i podobnych: traktuj jako orientacyjny, nie decyzja inwestycyjna.",
        "summary" => $ga4_untrusted && $crm_rev > 0
            ? sprintf(
                "ROAS CRM %.1fx (przychód %.0f PLN / koszt paid %.0f PLN) — GA4 revenue wyłączony z AI.",
                $crm_roas,
                $crm_rev,
                $ads_cost
            )
            : ($revenue > 0
                ? sprintf(
                    "Szac. zysk netto e-com %.0f PLN (marża %.0f%%, koszt paid %.0f PLN) — bez offline.",
                    $net_profit,
                    $margin_pct,
                    $ads_cost
                )
                : "Brak purchase w GA4 — Profit Dashboard nie obejmuje leadów telefonicznych ani ofert."),
    ];
}

/**
 * @param array<string, mixed> $agg
 * @return array<string, mixed>
 */
function ups_audit_intel_competition(array $agg, int $client_id): array
{
    $enabled = (bool) get_option("ups_audit_competition_premium_enabled", false);
    $auction = get_option("ups_ads_auction_data", []);
    $rows = [];
    if (is_array($auction)) {
        foreach (array_slice($auction, 0, 8) as $row) {
            if (!is_array($row)) {
                continue;
            }
            $rows[] = [
                "competitor" => (string) ($row["competitor"] ?? $row["domain"] ?? ""),
                "impression_share" => (float) ($row["impression_share"] ?? 0),
                "overlap_rate" => (float) ($row["overlap_rate"] ?? 0),
            ];
        }
    }

    return [
        "premium" => true,
        "enabled" => $enabled,
        "auction_insights" => $rows,
        "note" => $enabled
            ? __("Dane auction insights z globalnego sync Ads.", "upsellio")
            : __("Konkurencja (Semrush/Ahrefs) — premium addon. Włącz ups_audit_competition_premium_enabled lub podłącz API.", "upsellio"),
    ];
}

/**
 * @param array<string, mixed> $agg
 * @param array<string, mixed> $benchmark
 * @param array<string, mixed> $derived
 * @return array<string, mixed>
 */
function ups_audit_intel_benchmark_compare(array $agg, array $benchmark, array $derived): array
{
    if (empty($benchmark["has_benchmark"])) {
        return ["has_data" => false, "comparisons" => [], "note" => __("Benchmark wymaga ≥2 klientów w portfolio.", "upsellio")];
    }

    $tracking = ups_audit_intel_tracking_health($agg, []);
    $comparisons = [];
    $pairs = [
        ["label" => "Health", "client" => (float) ($agg["health_score"] ?? 0), "bench" => (float) ($benchmark["health_score"] ?? 0), "unit" => "/100", "higher_better" => true, "fmt" => "int"],
        ["label" => "Tracking Health", "client" => (float) ($tracking["score"] ?? 0), "bench" => null, "unit" => "/100", "higher_better" => true, "fmt" => "int"],
        ["label" => "CPA Ads", "client" => (float) ($derived["ads_cpa"] ?? 0), "bench" => (float) ($benchmark["ads_cpa_avg"] ?? 0), "unit" => " zł", "higher_better" => false, "fmt" => "money"],
        ["label" => "SEO kliknięcia", "client" => (float) ($agg["gsc_clicks"] ?? 0), "bench" => (float) ($benchmark["gsc_clicks"] ?? 0), "unit" => "", "higher_better" => true, "fmt" => "int"],
        ["label" => "CR sesji", "client" => (float) ($derived["ga4_conversion_rate"] ?? 0), "bench" => (float) ($benchmark["ga4_cr_avg"] ?? 0), "unit" => "%", "higher_better" => true, "fmt" => "pct"],
        ["label" => "Sesje GA4", "client" => (float) ($agg["ga4_sessions"] ?? 0), "bench" => (float) ($benchmark["ga4_sessions"] ?? 0), "unit" => "", "higher_better" => true, "fmt" => "int"],
        ["label" => "CTR GSC", "client" => (float) ($derived["gsc_ctr"] ?? 0), "bench" => (float) ($benchmark["gsc_ctr_avg"] ?? 0), "unit" => "%", "higher_better" => true, "fmt" => "pct"],
    ];

    foreach ($pairs as $p) {
        $client_val = (float) $p["client"];
        $bench_raw = $p["bench"] ?? null;
        $bench_val = $bench_raw === null ? null : (float) $bench_raw;
        if ($client_val <= 0 && ($bench_val === null || $bench_val <= 0)) {
            continue;
        }
        $vs = $bench_val !== null && function_exists("ups_audit_vs_benchmark_pct")
            ? ups_audit_vs_benchmark_pct($client_val, $bench_val)
            : null;
        $better = null;
        if ($vs !== null) {
            $better = (bool) $p["higher_better"] ? $vs >= 0 : $vs <= 0;
        }
        $comparisons[] = [
            "label" => (string) $p["label"],
            "client" => $client_val,
            "benchmark" => $bench_val,
            "unit" => (string) $p["unit"],
            "fmt" => (string) ($p["fmt"] ?? "decimal"),
            "vs_pct" => $vs,
            "better" => $better,
        ];
    }

    return [
        "has_data" => $comparisons !== [],
        "clients_in_sample" => (int) ($benchmark["clients"] ?? 0),
        "comparisons" => $comparisons,
        "table_ready" => true,
    ];
}

/**
 * Agency Command Center — rollup wszystkich profili.
 *
 * @return array<string, mixed>
 */
function ups_audit_build_command_center(int $window = 30): array
{
    $window = in_array($window, [7, 14, 30, 60, 90], true) ? $window : 30;
    $rows = [];
    $all_alerts = [];

    foreach (function_exists("ups_audit_collect_profile_client_ids") ? ups_audit_collect_profile_client_ids() : [] as $cid) {
        $cid = (int) $cid;
        $client = get_post($cid);
        if (!($client instanceof WP_Post)) {
            continue;
        }
        $agg = ups_audit_aggregate_client_data($cid, $window, 0, false);
        $intel = (array) ($agg["intelligence"] ?? []);
        $alerts = (array) ($intel["alerts"] ?? []);
        $critical = count(array_filter($alerts, static fn($a) => is_array($a) && ($a["severity"] ?? "") === "critical"));
        $warning = count(array_filter($alerts, static fn($a) => is_array($a) && ($a["severity"] ?? "") === "warning"));

        $status = "green";
        if ($critical > 0) {
            $status = "red";
        } elseif ($warning > 0 || (int) ($agg["health_score"] ?? 0) < 50) {
            $status = "yellow";
        }

        $attention = [];
        foreach (array_slice($alerts, 0, 2) as $al) {
            if (!is_array($al)) {
                continue;
            }
            $attention[] = (string) ($al["title"] ?? "");
            $all_alerts[] = array_merge($al, ["client_id" => $cid, "client_name" => $client->post_title]);
        }

        $rows[] = [
            "id" => $cid,
            "title" => (string) $client->post_title,
            "status" => $status,
            "health_score" => (int) ($agg["health_score"] ?? 0),
            "ga4_sessions" => (int) ($agg["ga4_sessions"] ?? 0),
            "gsc_clicks" => (int) ($agg["gsc_clicks"] ?? 0),
            "ads_cost" => (float) ($agg["ads_cost"] ?? 0),
            "alert_count" => $critical + $warning,
            "attention" => $attention,
            "deltas" => (array) ($agg["deltas"] ?? []),
            "dashboard_url" => function_exists("upsellio_crm_url")
                ? upsellio_crm_url("ca-dashboard", ["cid" => $cid, "window" => $window])
                : "",
        ];
    }

    $prio = ["critical" => 0, "warning" => 1];
    usort($all_alerts, static function ($a, $b) use ($prio) {
        return ($prio[$a["severity"] ?? "info"] ?? 9) <=> ($prio[$b["severity"] ?? "info"] ?? 9);
    });

    usort($rows, static function ($a, $b) {
        $order = ["red" => 0, "yellow" => 1, "green" => 2];
        $sa = $order[$a["status"] ?? "green"] ?? 9;
        $sb = $order[$b["status"] ?? "green"] ?? 9;
        if ($sa !== $sb) {
            return $sa <=> $sb;
        }

        return ((int) ($b["alert_count"] ?? 0)) <=> ((int) ($a["alert_count"] ?? 0));
    });

    return [
        "window_days" => $window,
        "clients" => $rows,
        "top_alerts" => array_slice($all_alerts, 0, 12),
        "summary" => [
            "total" => count($rows),
            "red" => count(array_filter($rows, static fn($r) => ($r["status"] ?? "") === "red")),
            "yellow" => count(array_filter($rows, static fn($r) => ($r["status"] ?? "") === "yellow")),
            "green" => count(array_filter($rows, static fn($r) => ($r["status"] ?? "") === "green")),
        ],
    ];
}

function ups_audit_generate_seo_roadmap_ai(int $client_id): array
{
    $client_id = (int) $client_id;
    $client = get_post($client_id);
    if (!($client instanceof WP_Post) || $client->post_type !== "crm_client") {
        return ["id" => 0, "html" => ""];
    }
    $cur = ups_audit_aggregate_client_data($client_id, 30, 0);
    $roadmap = (array) (($cur["intelligence"]["seo_roadmap"] ?? []));
    $content = (array) (($cur["intelligence"]["content_potential"]["rows"] ?? []));
    $prompt = "Jesteś strategiem SEO agencji. Klient: " . $client->post_title . ".\n\n"
        . "Dane GSC i roadmapa regułowa:\n" . wp_json_encode(["roadmap" => $roadmap, "content_potential" => $content, "keywords" => $cur["top_keywords"] ?? []], JSON_UNESCAPED_UNICODE) . "\n\n"
        . "Wygeneruj HTML: SEO Roadmap na 30 dni z konkretnymi zadaniami (strona X, +500 słów, 5 linków wewn., poprawa title). "
        . "Każde zadanie: priorytet, szacowany impact (+X klik), effort. Bez markdown.";
    $html = function_exists("ups_audit_generate_with_ai")
        ? ups_audit_generate_with_ai($prompt, 2800, 75, "ups_audit_anthropic_model_reports", "<h2>SEO Roadmap</h2><p>Brak odpowiedzi AI.</p>")
        : "<h2>SEO Roadmap</h2>";
    $report_id = function_exists("ups_audit_create_report_post")
        ? ups_audit_create_report_post($client_id, "seo_roadmap", "SEO Roadmap AI - " . wp_date("Y-m-d") . " - " . $client->post_title, $html, ["current" => $cur])
        : 0;

    return ["id" => (int) $report_id, "html" => $html];
}

function ups_audit_generate_ux_audit_ai(int $client_id): array
{
    $client_id = (int) $client_id;
    $client = get_post($client_id);
    if (!($client instanceof WP_Post) || $client->post_type !== "crm_client") {
        return ["id" => 0, "html" => ""];
    }
    $cur = ups_audit_aggregate_client_data($client_id, 30, 0);
    $ux = (array) (($cur["intelligence"]["ux_audit"] ?? []));
    $prompt = "Jesteś UX auditor. Klient: " . $client->post_title . ".\n\nDane Clarity i UX:\n"
        . wp_json_encode($ux, JSON_UNESCAPED_UNICODE) . "\n\n"
        . "Wygeneruj HTML: audyt UX z listą problemów (severity), hipotezą, fix, szacowany wpływ na CR. Bez markdown.";
    $html = function_exists("ups_audit_generate_with_ai")
        ? ups_audit_generate_with_ai($prompt, 2200, 70, "ups_audit_anthropic_model_audits", "<h2>UX Audit AI</h2><p>Brak odpowiedzi AI.</p>")
        : "<h2>UX Audit AI</h2>";
    $report_id = function_exists("ups_audit_create_report_post")
        ? ups_audit_create_report_post($client_id, "ux_audit", "UX Audit AI - " . wp_date("Y-m-d") . " - " . $client->post_title, $html, ["current" => $cur])
        : 0;

    return ["id" => (int) $report_id, "html" => $html];
}

function ups_audit_dispatch_intelligence_alerts(int $client_id, array $alerts): void
{
    $client_id = (int) $client_id;
    if ($client_id <= 0 || $alerts === []) {
        return;
    }
    $critical = array_values(array_filter($alerts, static function ($a) {
        return is_array($a) && in_array((string) ($a["severity"] ?? ""), ["critical", "warning"], true);
    }));
    if ($critical === []) {
        return;
    }
    $recs = [];
    foreach ($critical as $al) {
        $recs[] = [
            "priority" => (string) ($al["severity"] ?? "") === "critical" ? "high" : "medium",
            "category" => (string) ($al["category"] ?? "general"),
            "title" => (string) ($al["title"] ?? ""),
            "detail" => (string) ($al["message"] ?? "") . " " . (string) ($al["action"] ?? ""),
            "metric" => (string) ($al["metric"] ?? ""),
        ];
    }
    if (function_exists("ups_audit_dispatch_high_priority_alerts")) {
        ups_audit_dispatch_high_priority_alerts($client_id, $recs);
    }
}

