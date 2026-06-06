<?php

if (!defined("ABSPATH")) {
    exit;
}

/**
 * 5-poziomowa skala confidence (0–100%).
 *
 * @return array{band:string, label:string}
 */
function ups_audit_confidence_band(int $score): array
{
    $score = max(0, min(100, $score));

    if ($score >= 80) {
        return ["band" => "high", "label" => __("wysokie", "upsellio")];
    }
    if ($score >= 60) {
        return ["band" => "good", "label" => __("dobre", "upsellio")];
    }
    if ($score >= 40) {
        return ["band" => "medium", "label" => __("średnie", "upsellio")];
    }
    if ($score >= 20) {
        return ["band" => "very_low", "label" => __("bardzo niskie", "upsellio")];
    }

    return ["band" => "critical", "label" => __("krytyczne", "upsellio")];
}

/**
 * Miękkie minimum 5–12% gdy są dane, ale surowy wynik spadł do 0.
 * Unika mylącego „0%” przy katastrofalnym, lecz zmierzonym pomiarze.
 */
function ups_audit_confidence_finalize(int $raw_score, bool $has_signal, int $factor_count = 0): int
{
    $raw = max(0, min(100, $raw_score));
    if (!$has_signal || $raw >= 20) {
        return $raw;
    }
    if ($raw >= 5) {
        return $raw;
    }

    $floor = max(5, min(12, 12 - (int) floor($factor_count / 2)));

    return $raw > 0 ? max($floor, $raw) : $floor;
}

/**
 * @param array<string, mixed> $band
 */
function ups_audit_confidence_result(int $score, int $raw_score, array $band, array $factors, string $context_label): array
{
    $label = (string) ($band["label"] ?? "");
    if ($raw_score < 5 && $score !== $raw_score && $score >= 5) {
        $factors[] = sprintf(
            __("wyświetlany wynik %d%% (surowy %d%%) — %s", "upsellio"),
            $score,
            $raw_score,
            $label
        );
    }

    return [
        "score" => $score,
        "raw_score" => $raw_score,
        "band" => (string) ($band["band"] ?? "critical"),
        "label" => $context_label !== ""
            ? sprintf("%s — %s", $label, $context_label)
            : $label,
        "band_label" => $label,
        "factors" => $factors,
        "summary" => sprintf("%d%% — %s", $score, $label),
    ];
}

/**
 * Wiarygodność przychodu GA4 — blokuje wnioski AI przy zawyżonych metrykach.
 *
 * @param array<string, mixed> $agg
 * @return array<string, mixed>
 */
function ups_audit_revenue_quality(array $agg): array
{
    $kpi_rev = (float) ($agg["ga4_revenue"] ?? 0);
    $session_rev = (float) ($agg["ga4_revenue_session_total"] ?? 0);
    $roas = (float) ($agg["roas"] ?? 0);
    $purchase_cnt = (int) ($agg["ga4_purchase_count"] ?? 0);
    $sessions = (int) ($agg["ga4_sessions"] ?? 0);
    $rev_source = (string) ($agg["ga4_revenue_source"] ?? "");
    $crm_rev = (float) (($agg["intelligence"]["crm_revenue"]["funnel_totals"]["revenue"] ?? 0)
        ?: ($agg["crm_revenue_totals"]["revenue"] ?? 0));

    $reasons = [];
    if ($roas > 15 && (float) ($agg["ads_cost"] ?? 0) > 500) {
        $reasons[] = sprintf("ROAS %.1fx przy koszcie Ads — nierealistyczne dla B2B.", $roas);
    }
    if ($session_rev > 0 && $kpi_rev > 0 && $session_rev > $kpi_rev * 1.25 && $session_rev > 50000) {
        $reasons[] = sprintf(
            "totalRevenue sesji (%.0f PLN) >> purchaseRevenue (%.0f PLN).",
            $session_rev,
            $kpi_rev
        );
    }
    if ($purchase_cnt > 0 && $sessions > 0 && ($purchase_cnt / $sessions) > 0.08) {
        $reasons[] = sprintf(
            "CR zakupów %.1f%% (%d / %d sesji) — podejrzenie duplikatów purchase.",
            ($purchase_cnt / $sessions) * 100,
            $purchase_cnt,
            $sessions
        );
    }
    if ($kpi_rev > 200000 && $purchase_cnt > 0 && ($kpi_rev / max(1, $purchase_cnt)) > 5000) {
        $reasons[] = "Średnia wartość zamówienia > 5000 PLN — zweryfikuj w GA4 Monetyzacja → Zakupy.";
    }

    $rev_client_id = (int) ($agg["client_id"] ?? 0);
    $rev_conf = function_exists("ups_audit_revenue_confidence")
        ? ups_audit_revenue_confidence($agg, $rev_client_id)
        : ["score" => 100, "band" => "high"];
    if ((int) ($rev_conf["score"] ?? 100) < 50) {
        $reasons[] = sprintf(
            "Revenue Confidence %d%% — eventy WooCommerce/GTM zawyżają eventValue.",
            (int) $rev_conf["score"]
        );
    }

    $untrusted = $reasons !== [] || (int) ($rev_conf["score"] ?? 100) < 45;
    $label = $untrusted ? "ostrzeżenie" : ($rev_source === "purchaseRevenue" ? "purchaseRevenue" : "ok");

    return [
        "trusted" => !$untrusted,
        "revenue_confidence" => $rev_conf,
        "warning" => $untrusted,
        "label" => $label,
        "reasons" => $reasons,
        "revenue_display" => $untrusted ? 0.0 : $kpi_rev,
        "revenue_crm_fallback" => $crm_rev,
        "use_for_ai" => !$untrusted,
        "use_crm_for_roas" => $untrusted && $crm_rev > 0,
        "message" => $untrusted
            ? __("Revenue Quality Warning — nie używaj przychodu GA4 do wniosków biznesowych do czasu naprawy pomiaru.", "upsellio")
            : "",
    ];
}

/**
 * Attribution Confidence 0–100.
 *
 * @param array<string, mixed> $agg
 */
function ups_audit_attribution_confidence(array $agg, int $client_id = 0): array
{
    $score = 100;
    $factors = [];

    $not_set = (float) ($agg["ga4_not_set_pct"] ?? 0);
    if ($not_set > 0) {
        $pen = (int) min(35, round($not_set * 1.2));
        $score -= $pen;
        $factors[] = sprintf("%.0f%% (not set) (−%d)", $not_set, $pen);
    }

    $ads_conv = (float) ($agg["ads_conversions"] ?? 0);
    $ga4_conv = (int) ($agg["ga4_conversions"] ?? 0);
    if ($ads_conv > 0 && $ga4_conv > 0) {
        $diff = abs($ads_conv - $ga4_conv) / max(1, $ga4_conv);
        if ($diff > 0.5) {
            $pen = (int) min(18, round($diff * 12));
            $score -= $pen;
            $factors[] = sprintf("Ads %.0f vs GA4 %d (−%d)", $ads_conv, $ga4_conv, $pen);
        }
    }

    $crm_leads = 0;
    $leads_no_utm = 0;
    if ($client_id > 0) {
        $crm_stats = ups_audit_crm_lead_attribution_stats($client_id, (int) ($agg["period_days"] ?? 30));
        $crm_leads = (int) ($crm_stats["leads"] ?? 0);
        $leads_no_utm = (int) ($crm_stats["leads_no_utm"] ?? 0);
        if ($leads_no_utm > 0 && $crm_leads > 0) {
            $pct = ($leads_no_utm / $crm_leads) * 100;
            $pen = (int) min(25, round($pct * 0.35));
            $score -= $pen;
            $factors[] = sprintf("%d/%d leadów bez UTM (−%d)", $leads_no_utm, $crm_leads, $pen);
        }
        if ($ads_conv > 0 && $crm_leads === 0) {
            $score -= 20;
            $factors[] = __("Ads konw. bez leadów CRM (−20)", "upsellio");
        }
    }

    $tracking = (int) (($agg["intelligence"]["tracking_health"]["score"] ?? 0) ?: 0);
    if ($tracking > 0 && $tracking < 60) {
        $score -= 10;
        $factors[] = sprintf("Tracking %d/100 (−10)", $tracking);
    }

    $raw_score = max(0, min(100, $score));
    $has_signal = (int) ($agg["ga4_sessions"] ?? 0) > 0 || (float) ($agg["ga4_not_set_pct"] ?? 0) > 0;
    $final_score = ups_audit_confidence_finalize($raw_score, $has_signal, count($factors));
    $band = ups_audit_confidence_band($final_score);

    return ups_audit_confidence_result(
        $final_score,
        $raw_score,
        $band,
        $factors,
        __("wiarygodność atrybucji", "upsellio")
    );
}

/**
 * @return array{leads:int, leads_no_utm:int}
 */
function ups_audit_crm_lead_attribution_stats(int $client_id, int $days = 30): array
{
    $client_id = (int) $client_id;
    $days = max(7, min(90, $days));
    if ($client_id <= 0) {
        return ["leads" => 0, "leads_no_utm" => 0];
    }

    $after = gmdate("Y-m-d H:i:s", strtotime("-" . $days . " days"));
    $leads = 0;
    $no_utm = 0;
    $lead_types = array_values(array_filter(["crm_lead", "lead"], "post_type_exists"));

    foreach ($lead_types as $pt) {
        $posts = get_posts([
            "post_type" => $pt,
            "posts_per_page" => 500,
            "post_status" => ["publish", "draft", "pending", "private"],
            "date_query" => [["after" => $after]],
        ]);
        foreach ($posts as $lead) {
            if (!($lead instanceof WP_Post)) {
                continue;
            }
            if (function_exists("ups_audit_lead_matches_client") && !ups_audit_lead_matches_client($client_id, (int) $lead->ID)) {
                continue;
            }
            $leads++;
            $src = strtolower(trim((string) get_post_meta((int) $lead->ID, "_upsellio_lead_utm_source", true)));
            $med = strtolower(trim((string) get_post_meta((int) $lead->ID, "_upsellio_lead_utm_medium", true)));
            if ($src === "" || $src === "(not set)" || $med === "" || $med === "(not set)") {
                $no_utm++;
            }
        }
    }

    return ["leads" => $leads, "leads_no_utm" => $no_utm];
}

/**
 * CRM Quality Score — jakość lejka, nie tylko wolumen.
 *
 * @param array<string, mixed> $crm_revenue
 */
function ups_audit_crm_quality_score(array $crm_revenue): array
{
    $rows = (array) ($crm_revenue["rows"] ?? []);
    $funnel = (array) ($crm_revenue["funnel_totals"] ?? []);
    $leads = (int) ($funnel["leads"] ?? 0);
    $offers = (int) ($funnel["offers"] ?? 0);
    $won = (int) ($funnel["won"] ?? 0);
    $revenue = (float) ($funnel["revenue"] ?? 0);

    $lead_to_offer = $leads > 0 ? round(($offers / $leads) * 100, 1) : 0.0;
    $offer_to_won = $offers > 0 ? round(($won / $offers) * 100, 1) : 0.0;
    $avg_won = $won > 0 ? round($revenue / $won, 0) : 0.0;

    $score = 40;
    if ($lead_to_offer >= 40) {
        $score += 25;
    } elseif ($lead_to_offer >= 20) {
        $score += 15;
    } elseif ($lead_to_offer >= 10) {
        $score += 8;
    }
    if ($offer_to_won >= 40) {
        $score += 25;
    } elseif ($offer_to_won >= 20) {
        $score += 15;
    } elseif ($offer_to_won >= 10) {
        $score += 8;
    }
    if ($avg_won >= 5000) {
        $score += 10;
    } elseif ($avg_won >= 2000) {
        $score += 5;
    }

    $score = max(0, min(100, $score));
    $label = $score >= 75 ? __("wysoka jakość lejka", "upsellio")
        : ($score >= 50 ? __("średnia jakość lejka", "upsellio") : __("słaba konwersja lead→wygrana", "upsellio"));

    $channel_rows = [];
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $ch_leads = (int) ($row["leads"] ?? 0);
        $ch_offers = (int) ($row["offers"] ?? 0);
        $ch_won = (int) ($row["won"] ?? 0);
        $ch_rev = (float) ($row["revenue"] ?? 0);
        $ch_l2o = $ch_leads > 0 ? round(($ch_offers / $ch_leads) * 100, 1) : null;
        $ch_o2w = $ch_offers > 0 ? round(($ch_won / $ch_offers) * 100, 1) : null;
        $ch_score = 50;
        if ($ch_l2o !== null && $ch_l2o >= 30) {
            $ch_score += 15;
        }
        if ($ch_o2w !== null && $ch_o2w >= 25) {
            $ch_score += 20;
        }
        if ($ch_won > 0 && $ch_rev > 0) {
            $ch_score += 10;
        }
        $channel_rows[] = array_merge($row, [
            "lead_to_offer_pct" => $ch_l2o,
            "offer_to_won_pct" => $ch_o2w,
            "avg_won_value" => $ch_won > 0 ? round($ch_rev / $ch_won, 0) : null,
            "quality_score" => min(100, $ch_score),
        ]);
    }

    usort($channel_rows, static function ($a, $b) {
        return ((int) ($b["quality_score"] ?? 0)) <=> ((int) ($a["quality_score"] ?? 0));
    });

    return [
        "score" => $score,
        "label" => $label,
        "has_data" => $leads > 0 || $offers > 0,
        "lead_to_offer_pct" => $lead_to_offer,
        "offer_to_won_pct" => $offer_to_won,
        "avg_won_value" => $avg_won,
        "channels" => array_slice($channel_rows, 0, 8),
        "summary" => $leads > 0
            ? sprintf(
                "Lead→oferta %.1f%% · oferta→wygrana %.1f%% · śr. wygrana %s PLN",
                $lead_to_offer,
                $offer_to_won,
                $avg_won > 0 ? number_format($avg_won, 0, ",", " ") : "—"
            )
            : __("Brak leadów CRM w okresie — jakość lejka oceniana po pierwszych danych.", "upsellio"),
    ];
}

/**
 * Revenue Confidence 0–100 — osobno od Attribution Confidence.
 *
 * @param array<string, mixed> $agg
 */
function ups_audit_revenue_confidence(array $agg, int $client_id = 0): array
{
    $score = 100;
    $factors = [];
    $kpi_rev = (float) ($agg["ga4_revenue"] ?? 0);
    $events = (array) ($agg["ga4_events_diagnostics"] ?? $agg["ga4_events_breakdown"] ?? []);

    $non_purchase_rev = 0.0;
    $purchase_event_rev = 0.0;
    $suspicious = [];

    foreach ($events as $ev) {
        if (!is_array($ev)) {
            continue;
        }
        $name = strtolower(trim((string) ($ev["event"] ?? "")));
        $rev = (float) ($ev["revenue"] ?? 0);
        if ($rev <= 0) {
            continue;
        }
        if ($name === "purchase") {
            $purchase_event_rev = max($purchase_event_rev, $rev);
        } elseif (in_array($name, ["view_item", "add_to_cart", "begin_checkout", "form_submit", "form_start", "select_item"], true)) {
            $non_purchase_rev += $rev;
            if ($rev >= 500) {
                $suspicious[] = $name;
            }
        }
    }

    if ($non_purchase_rev >= 1000) {
        $pen = (int) min(55, round(log10(max(10, $non_purchase_rev)) * 9));
        $score -= $pen;
        $factors[] = sprintf(
            "eventy bez purchase: %.0f PLN eventValue (−%d)",
            $non_purchase_rev,
            $pen
        );
    }
    if ($suspicious !== []) {
        $factors[] = "podejrzane: " . implode(", ", array_unique($suspicious));
    }
    if ($purchase_event_rev > 0 && $kpi_rev > 0 && $purchase_event_rev > $kpi_rev * 3) {
        $pen = (int) min(35, round(($purchase_event_rev / max(1, $kpi_rev)) * 2));
        $score -= $pen;
        $factors[] = sprintf(
            "purchase eventValue %.0f >> KPI purchaseRevenue %.0f (−%d)",
            $purchase_event_rev,
            $kpi_rev,
            $pen
        );
    } elseif ($purchase_event_rev > 50000 && $kpi_rev < 10000) {
        $score -= 30;
        $factors[] = __("purchase eventValue zawyżony vs KPI (−30)", "upsellio");
    }

    $crm_rev = (float) (($agg["intelligence"]["crm_revenue"]["funnel_totals"]["revenue"] ?? 0));
    if ($client_id > 0) {
        $crm_stats = ups_audit_crm_lead_attribution_stats($client_id, (int) ($agg["period_days"] ?? 30));
        $crm_leads = (int) ($crm_stats["leads"] ?? 0);
        if ($crm_leads === 0 && (float) ($agg["ads_conversions"] ?? 0) > 0) {
            $score -= 18;
            $factors[] = __("Ads konw. bez leadów CRM (−18)", "upsellio");
        }
        if ($kpi_rev > 5000 && $crm_rev === 0.0 && $crm_leads === 0) {
            $score -= 12;
            $factors[] = __("brak atrybucji CRM do przychodu (−12)", "upsellio");
        }
    }

    $raw_score = max(0, min(100, $score));
    $has_signal = (int) ($agg["ga4_sessions"] ?? 0) > 0
        || $kpi_rev > 0
        || $non_purchase_rev > 0
        || $purchase_event_rev > 0;
    $final_score = ups_audit_confidence_finalize($raw_score, $has_signal, count($factors));
    $band = ups_audit_confidence_band($final_score);
    $result = ups_audit_confidence_result(
        $final_score,
        $raw_score,
        $band,
        $factors,
        __("wiarygodność przychodu", "upsellio")
    );
    $result["suspicious_events"] = array_values(array_unique($suspicious));

    return $result;
}

/**
 * Clarity API — niska wiarygodność dopóki nie zweryfikowano wielu projektów.
 *
 * @param array<string, mixed> $agg
 */
function ups_audit_clarity_confidence(array $agg): array
{
    $sess = (int) ($agg["clarity_sessions"] ?? 0);
    $dead = (int) ($agg["clarity_dead_clicks"] ?? 0);
    $rage = (int) ($agg["clarity_rage_clicks"] ?? 0);
    $window = (int) ($agg["clarity_window_days"] ?? 3);
    $factors = [];
    $score = 55;

    if ($sess <= 0) {
        return [
            "score" => 0,
            "band" => "none",
            "label" => __("brak danych Clarity", "upsellio"),
            "factors" => [],
            "summary" => __("Brak sesji Clarity w API", "upsellio"),
        ];
    }

    if ($window <= 3) {
        $score -= 12;
        $factors[] = __("API Clarity: max 3 dni (−12)", "upsellio");
    }
    if ($dead > $sess) {
        $score -= 28;
        $factors[] = sprintf("dead clicks %d > sesji %d (−28)", $dead, $sess);
    }
    if ($rage > $sess) {
        $score -= 20;
        $factors[] = sprintf("rage clicks %d > sesji %d (−20)", $rage, $sess);
    }
    foreach ((array) ($agg["resources"] ?? []) as $res) {
        if (!is_array($res) || (string) ($res["type"] ?? "") !== "clarity") {
            continue;
        }
        $err = (string) (($res["health"]["label"] ?? ""));
        if (strpos($err, "limit") !== false || strpos($err, "cache") !== false) {
            $score -= 10;
            $factors[] = __("limit API / cache (−10)", "upsellio");
        }
    }

    $score = max(0, min(100, $score));
    $band = $score >= 65 ? "medium" : "low";
    $band_label = $score >= 65
        ? __("Confidence: Medium", "upsellio")
        : __("Confidence: Low", "upsellio");

    return [
        "score" => $score,
        "band" => $band,
        "label" => $band_label,
        "factors" => $factors,
        "summary" => sprintf("%d/100 — %s", $score, $band_label),
    ];
}

/**
 * Zapis miesięcznego snapshotu Health (do 6 miesięcy).
 */
function ups_audit_record_health_snapshot(int $client_id, int $health_score, int $period_days = 30): void
{
    $client_id = (int) $client_id;
    if ($client_id <= 0) {
        return;
    }

    $history = get_post_meta($client_id, "_ups_audit_health_history", true);
    if (!is_array($history)) {
        $history = [];
    }

    $month_key = gmdate("Y-m");
    $history[$month_key] = [
        "month" => $month_key,
        "label" => wp_date("M Y", strtotime($month_key . "-01")),
        "score" => max(0, min(100, (int) $health_score)),
        "period_days" => max(7, (int) $period_days),
        "recorded_at" => gmdate("c"),
    ];

    ksort($history);
    if (count($history) > 6) {
        $history = array_slice($history, -6, 6, true);
    }

    update_post_meta($client_id, "_ups_audit_health_history", $history);
}

/**
 * @return list<array<string, mixed>>
 */
function ups_audit_health_history(int $client_id): array
{
    $client_id = (int) $client_id;
    if ($client_id <= 0) {
        return [];
    }

    $history = get_post_meta($client_id, "_ups_audit_health_history", true);
    if (!is_array($history)) {
        return [];
    }

    $out = array_values($history);
    usort($out, static function ($a, $b) {
        return strcmp((string) ($a["month"] ?? ""), (string) ($b["month"] ?? ""));
    });

    return $out;
}

/**
 * Panel Data Quality — osobna warstwa profesjonalizująca raport.
 *
 * @param array<string, mixed> $agg
 */
function ups_audit_data_quality_panel(array $agg, int $client_id = 0, array $setup = []): array
{
    $client_id = (int) $client_id;
    $tracking = (array) (($agg["intelligence"]["tracking_health"] ?? []));
    if ($tracking === [] && function_exists("ups_audit_intel_tracking_health")) {
        $tracking = ups_audit_intel_tracking_health($agg, $setup);
    }

    $attr = (array) ($agg["attribution_confidence"] ?? []);
    if ($attr === [] && $client_id > 0) {
        $attr = ups_audit_attribution_confidence($agg, $client_id);
    }

    $rev_conf = function_exists("ups_audit_revenue_confidence")
        ? ups_audit_revenue_confidence($agg, $client_id)
        : ["score" => 0, "label" => ""];
    $clarity_conf = ups_audit_clarity_confidence($agg);
    $health_trend = (array) ($agg["health_trend"] ?? []);
    $health_history = $client_id > 0 ? ups_audit_health_history($client_id) : [];

    $items = [
        [
            "key" => "attribution",
            "label" => __("Attribution Confidence", "upsellio"),
            "score" => (int) ($attr["score"] ?? 0),
            "unit" => "%",
            "band" => (string) ($attr["band"] ?? ""),
            "detail" => (string) ($attr["label"] ?? ""),
        ],
        [
            "key" => "revenue",
            "label" => __("Revenue Confidence", "upsellio"),
            "score" => (int) ($rev_conf["score"] ?? 0),
            "unit" => "%",
            "band" => (string) ($rev_conf["band"] ?? ""),
            "detail" => (string) ($rev_conf["label"] ?? ""),
        ],
        [
            "key" => "tracking",
            "label" => __("Tracking Health", "upsellio"),
            "score" => (int) ($tracking["score"] ?? 0),
            "unit" => "/100",
            "band" => ((int) ($tracking["score"] ?? 0)) >= 75 ? "high" : (((int) ($tracking["score"] ?? 0)) >= 50 ? "medium" : "low"),
            "detail" => (string) ($tracking["summary"] ?? ""),
        ],
    ];

    if ((int) ($agg["clarity_sessions"] ?? 0) > 0 || (int) ($setup["clarity"] ?? 0) > 0) {
        $items[] = [
            "key" => "clarity",
            "label" => __("Clarity UX", "upsellio"),
            "score" => (int) ($clarity_conf["score"] ?? 0),
            "unit" => "/100",
            "band" => (string) ($clarity_conf["band"] ?? "low"),
            "detail" => (string) ($clarity_conf["label"] ?? ""),
        ];
    }

    $warnings = [];
    if (in_array((string) ($attr["band"] ?? ""), ["critical", "very_low"], true)) {
        $warnings[] = __("Atrybucja kanałowa niewiarygodna — nie optymalizuj ROAS/CPA na GA4.", "upsellio");
    }
    if (in_array((string) ($rev_conf["band"] ?? ""), ["critical", "very_low"], true)) {
        $warnings[] = __("Przychód GA4 zawyżony przez WooCommerce/GTM — ignoruj eventValue, używaj purchaseRevenue i CRM.", "upsellio");
    }
    if ((string) ($clarity_conf["band"] ?? "") === "low") {
        $warnings[] = __("Clarity: Confidence Low — UX Score ma obniżoną wagę.", "upsellio");
    }

    $source_ratings = function_exists("ups_audit_source_quality_ratings")
        ? ups_audit_source_quality_ratings($agg, $attr, $rev_conf, $clarity_conf, $client_id)
        : [];

    return [
        "has_data" => true,
        "attribution_confidence" => $attr,
        "revenue_confidence" => $rev_conf,
        "tracking_health" => $tracking,
        "clarity_confidence" => $clarity_conf,
        "health_trend" => $health_trend,
        "health_history" => $health_history,
        "items" => $items,
        "source_ratings" => $source_ratings,
        "warnings" => $warnings,
        "summary" => $warnings !== []
            ? implode(" ", array_slice($warnings, 0, 2))
            : __("Jakość danych w normie dla tego okna.", "upsellio"),
    ];
}

/**
 * Ocena wiarygodności per źródło (skala 0–10) — do sekcji Data Quality.
 *
 * @param array<string, mixed> $agg
 * @param array<string, mixed> $attr
 * @param array<string, mixed> $rev_conf
 * @param array<string, mixed> $clarity_conf
 *
 * @return array{sources:list<array<string,mixed>>, average:float, average_label:string}
 */
function ups_audit_source_quality_ratings(array $agg, array $attr, array $rev_conf, array $clarity_conf, int $client_id = 0): array
{
    $sources = [];

    $gsc_clicks = (int) ($agg["gsc_clicks"] ?? 0);
    $gsc_score = 0.0;
    if ($gsc_clicks > 0) {
        $gsc_score += 5.0;
    }
    if ((int) ($agg["gsc_impressions"] ?? 0) > 50) {
        $gsc_score += 2.0;
    }
    if (count((array) ($agg["top_keywords"] ?? [])) >= 5) {
        $gsc_score += 1.5;
    }
    $sources[] = [
        "key" => "gsc",
        "label" => __("SEO (GSC)", "upsellio"),
        "score" => min(10.0, round($gsc_score, 1)),
        "note" => $gsc_clicks > 0 ? sprintf(__("%d klik.", "upsellio"), $gsc_clicks) : __("brak kliknięć", "upsellio"),
    ];

    $ads_cost = (float) ($agg["ads_cost"] ?? 0);
    $ads_conv = (float) ($agg["ads_conversions"] ?? 0);
    $ads_clicks = (int) ($agg["ads_clicks"] ?? 0);
    $ads_score = 0.0;
    if ($ads_cost > 0 && $ads_clicks > 0) {
        $ads_score += 5.5;
        $cpa = $ads_conv > 0 ? $ads_cost / $ads_conv : 0;
        if ($cpa >= 20 && $cpa <= 500) {
            $ads_score += 2.5;
        } elseif ($cpa > 0) {
            $ads_score += 1.0;
        }
        if ($ads_clicks >= 50) {
            $ads_score += 0.5;
        }
    }
    $sources[] = [
        "key" => "ads",
        "label" => __("Google Ads", "upsellio"),
        "score" => min(10.0, round($ads_score, 1)),
        "note" => $ads_cost > 0
            ? sprintf("%.0f zł · %d konw.", $ads_cost, (int) $ads_conv)
            : __("brak kosztu", "upsellio"),
    ];

    $st_count = count((array) ($agg["search_terms"] ?? []));
    $st_score = $st_count >= 20 ? 9.0 : ($st_count >= 5 ? 7.0 : ($st_count > 0 ? 5.0 : 0.0));
    $sources[] = [
        "key" => "search_terms",
        "label" => __("Search Terms", "upsellio"),
        "score" => $st_score,
        "note" => $st_count > 0 ? sprintf(__("%d fraz", "upsellio"), $st_count) : "",
    ];

    $ga4_sess = (int) ($agg["ga4_sessions"] ?? 0);
    $ga4_traffic = $ga4_sess >= 500 ? 7.5 : ($ga4_sess >= 100 ? 7.0 : ($ga4_sess > 0 ? 5.0 : 0.0));
    $sources[] = [
        "key" => "ga4_traffic",
        "label" => __("GA4 ruch", "upsellio"),
        "score" => $ga4_traffic,
        "note" => $ga4_sess > 0 ? sprintf(__("%d sesji", "upsellio"), $ga4_sess) : "",
    ];

    $rev_pct = (int) ($rev_conf["score"] ?? 0);
    $rev_rating = min(10.0, max(0.0, round($rev_pct / 3.33, 1)));
    $sources[] = [
        "key" => "ga4_revenue",
        "label" => __("GA4 przychód", "upsellio"),
        "score" => $rev_rating,
        "note" => sprintf(__("Revenue Confidence %d%%", "upsellio"), $rev_pct),
    ];

    $clarity_sess = (int) ($agg["clarity_sessions"] ?? 0);
    $clarity_rating = $clarity_sess <= 0
        ? 0.0
        : min(10.0, max(0.0, round(((int) ($clarity_conf["score"] ?? 0)) / 10, 1)));
    if ($clarity_sess > 0) {
        $sources[] = [
            "key" => "clarity",
            "label" => __("Clarity", "upsellio"),
            "score" => $clarity_rating,
            "note" => (string) ($clarity_conf["label"] ?? ""),
        ];
    }

    $attr_pct = (int) ($attr["score"] ?? 0);
    $sources[] = [
        "key" => "attribution",
        "label" => __("Attribution", "upsellio"),
        "score" => min(10.0, max(0.0, round($attr_pct / 10, 1))),
        "note" => sprintf(__("Attribution Confidence %d%%", "upsellio"), $attr_pct),
    ];

    $crm_leads = 0;
    if ($client_id > 0) {
        $crm_stats = ups_audit_crm_lead_attribution_stats($client_id, (int) ($agg["period_days"] ?? 30));
        $crm_leads = (int) ($crm_stats["leads"] ?? 0);
    }
    $crm_rating = $crm_leads > 0 ? min(10.0, round(((int) (($agg["intelligence"]["crm_quality"]["score"] ?? 0))) / 10, 1)) : 0.0;
    $sources[] = [
        "key" => "crm",
        "label" => __("CRM Attribution", "upsellio"),
        "score" => $crm_rating,
        "note" => $crm_leads > 0
            ? sprintf(__("%d leadów", "upsellio"), $crm_leads)
            : __("brak leadów w okresie", "upsellio"),
    ];

    $rated = array_values(array_filter($sources, static fn($s) => (float) ($s["score"] ?? 0) > 0 || (string) ($s["key"] ?? "") === "crm"));
    $sum = 0.0;
    foreach ($rated as $row) {
        $sum += (float) ($row["score"] ?? 0);
    }
    $avg = $rated !== [] ? round($sum / count($rated), 1) : 0.0;

    return [
        "sources" => $sources,
        "average" => $avg,
        "average_label" => sprintf(__("Średnia jakość danych: %.1f/10", "upsellio"), $avg),
    ];
}

/**
 * Health trend vs poprzedni okres.
 *
 * @param array<string, mixed> $agg
 */
function ups_audit_health_trend(array $agg, array $setup = [], int $client_id = 0): array
{
    $current = (int) ($agg["health_score"] ?? 0);
    $prev_agg = [
        "ga4_sessions" => (int) ($agg["ga4_sessions_prev"] ?? 0),
        "ga4_conversions" => (int) ($agg["ga4_conversions_prev"] ?? 0),
        "ga4_conversions_all" => (int) ($agg["ga4_conversions_prev"] ?? 0),
        "gsc_clicks" => (int) ($agg["gsc_clicks_prev"] ?? 0),
        "gsc_impressions" => (int) ($agg["gsc_impressions_prev"] ?? 0),
        "ads_cost" => (float) ($agg["ads_cost_prev"] ?? 0),
        "ads_conversions" => (float) ($agg["ads_conversions_prev"] ?? 0),
        "ga4_not_set_pct" => (float) ($agg["ga4_not_set_pct"] ?? 0),
        "clarity_sessions" => (int) ($agg["clarity_sessions"] ?? 0),
        "clarity_dead_clicks" => (int) ($agg["clarity_dead_clicks"] ?? 0),
        "top_keywords" => (array) ($agg["top_keywords"] ?? []),
        "opportunities" => (array) ($agg["opportunities"] ?? []),
        "resources" => (array) ($agg["resources"] ?? []),
    ];
    if (function_exists("ups_audit_compute_derived_metrics")) {
        $prev_agg["derived"] = ups_audit_compute_derived_metrics(array_merge($agg, $prev_agg));
    }
    $previous = function_exists("ups_audit_health_score")
        ? ups_audit_health_score($prev_agg, $setup)
        : 0;
    $delta = $current - $previous;

    $history = $client_id > 0 ? ups_audit_health_history($client_id) : [];
    $month_delta = 0;
    $prev_month_score = null;
    if (count($history) >= 2) {
        $last = (int) ($history[count($history) - 1]["score"] ?? 0);
        $prev_month_score = (int) ($history[count($history) - 2]["score"] ?? 0);
        $month_delta = $last - $prev_month_score;
    }

    return [
        "current" => $current,
        "previous" => $previous,
        "delta" => $delta,
        "direction" => $delta > 0 ? "up" : ($delta < 0 ? "down" : "flat"),
        "label" => $delta > 0
            ? sprintf("↑ +%d vs poprzedni okres", $delta)
            : ($delta < 0 ? sprintf("↓ %d vs poprzedni okres", $delta) : __("bez zmiany", "upsellio")),
        "month_delta" => $month_delta,
        "prev_month_score" => $prev_month_score,
        "month_label" => $month_delta > 0
            ? sprintf("↑ +%d vs poprzedni miesiąc", $month_delta)
            : ($month_delta < 0 ? sprintf("↓ %d vs poprzedni miesiąc", $month_delta) : ""),
        "history" => $history,
    ];
}

/**
 * Search term aging — klucz w cache klienta.
 */
function ups_audit_search_term_aging_key(string $term): string
{
    return md5(mb_strtolower(trim($term)));
}

/**
 * @param list<array<string, mixed>> $terms
 * @return array<string, array<string, mixed>>
 */
function ups_audit_update_search_term_aging(int $client_id, array $terms): array
{
    $client_id = (int) $client_id;
    if ($client_id <= 0) {
        return [];
    }

    $aging = get_post_meta($client_id, "_ups_audit_search_term_aging", true);
    if (!is_array($aging)) {
        $aging = [];
    }
    $today = gmdate("Y-m-d");

    foreach ($terms as $row) {
        if (!is_array($row)) {
            continue;
        }
        $term = trim((string) ($row["search_term"] ?? ""));
        if ($term === "") {
            continue;
        }
        $conv = (float) ($row["conversions"] ?? 0);
        $cost = (float) ($row["cost_pln"] ?? $row["cost"] ?? 0);
        $key = ups_audit_search_term_aging_key($term);

        if ($conv > 0) {
            unset($aging[$key]);
            continue;
        }

        if (!isset($aging[$key]) || !is_array($aging[$key])) {
            $aging[$key] = [
                "term" => $term,
                "first_seen" => $today,
                "last_seen" => $today,
                "zero_conv_cost" => $cost,
            ];
        } else {
            $aging[$key]["last_seen"] = $today;
            $aging[$key]["zero_conv_cost"] = max((float) ($aging[$key]["zero_conv_cost"] ?? 0), $cost);
        }
    }

    $cutoff = strtotime("-120 days");
    foreach ($aging as $k => $entry) {
        if (!is_array($entry)) {
            unset($aging[$k]);
            continue;
        }
        $last = strtotime((string) ($entry["last_seen"] ?? $entry["first_seen"] ?? ""));
        if ($last > 0 && $last < $cutoff) {
            unset($aging[$k]);
        }
    }

    update_post_meta($client_id, "_ups_audit_search_term_aging", $aging);

    return $aging;
}

/**
 * Dni obserwacji frazy bez konwersji.
 *
 * @param array<string, array<string, mixed>> $aging
 */
function ups_audit_search_term_observation_days(string $term, array $aging): int
{
    $key = ups_audit_search_term_aging_key($term);
    if (!isset($aging[$key]) || !is_array($aging[$key])) {
        return 0;
    }
    $first = strtotime((string) ($aging[$key]["first_seen"] ?? ""));
    if ($first <= 0) {
        return 0;
    }

    return max(0, (int) floor((time() - $first) / DAY_IN_SECONDS));
}
