<?php

if (!defined("ABSPATH")) {
    exit;
}

/**
 * Metryki pochodne + rekomendacje (GSC, GA4, Ads) — klient audytu i agencja (Upsellio).
 */

/**
 * @param array<string, mixed> $agg
 * @return array<string, mixed>
 */
function ups_audit_compute_derived_metrics(array $agg): array
{
    $sessions = (int) ($agg["ga4_sessions"] ?? 0);
    $conversions = (int) ($agg["ga4_conversions"] ?? 0);
    $revenue = (float) ($agg["ga4_revenue"] ?? 0);
    $gsc_clicks = (int) ($agg["gsc_clicks"] ?? 0);
    $gsc_impr = (int) ($agg["gsc_impressions"] ?? 0);
    $ads_cost = (float) ($agg["ads_cost"] ?? 0);
    $ads_clicks = (int) ($agg["ads_clicks"] ?? 0);
    $ads_conv = (float) ($agg["ads_conversions"] ?? 0);
    $meta_cost = (float) ($agg["meta_cost"] ?? 0);
    $meta_clicks = (int) ($agg["meta_clicks"] ?? 0);
    $meta_conv = (float) ($agg["meta_conversions"] ?? 0);
    $paid_cost = (float) ($agg["paid_cost"] ?? ($ads_cost + $meta_cost));
    $paid_clicks = $ads_clicks + $meta_clicks;

    $ga4_cr = $sessions > 0 ? round(($conversions / $sessions) * 100, 2) : 0.0;
    $gsc_ctr = $gsc_impr > 0 ? round(($gsc_clicks / $gsc_impr) * 100, 2) : (float) ($agg["gsc_ctr"] ?? 0);
    $ads_cpc = $ads_clicks > 0 ? round($ads_cost / $ads_clicks, 2) : 0.0;
    $ads_cpa = $ads_conv > 0 ? round($ads_cost / $ads_conv, 2) : 0.0;
    $meta_cpc = $meta_clicks > 0 ? round($meta_cost / $meta_clicks, 2) : 0.0;
    $meta_cpa = $meta_conv > 0 ? round($meta_cost / $meta_conv, 2) : 0.0;
    $paid_share = $sessions > 0 && $paid_clicks > 0
        ? round(min(100, ($paid_clicks / max(1, $sessions)) * 100), 1)
        : 0.0;

    $organic_est = max(0, $sessions - (int) round($paid_clicks * 0.35));
    $blended_cpa = $conversions > 0 ? round($paid_cost / $conversions, 2) : 0.0;

    return [
        "ga4_conversion_rate" => $ga4_cr,
        "gsc_ctr" => $gsc_ctr,
        "gsc_avg_position" => (float) ($agg["gsc_avg_position"] ?? 0),
        "ads_cpc" => $ads_cpc,
        "ads_cpa" => $ads_cpa,
        "meta_cpc" => $meta_cpc,
        "meta_cpa" => $meta_cpa,
        "blended_cpa" => $blended_cpa,
        "paid_click_share_pct" => $paid_share,
        "organic_sessions_est" => $organic_est,
        "roas" => (float) ($agg["roas"] ?? 0),
        "meta_roas" => (float) ($agg["meta_roas"] ?? 0),
        "paid_cost" => $paid_cost,
    ];
}

/**
 * Szybkie szanse SEO (pozycje 4–20, wyświetlenia).
 *
 * @param array<string, mixed> $agg
 * @return array<int, array<string, mixed>>
 */
function ups_audit_find_opportunities(array $agg): array
{
    $out = [];
    foreach ((array) ($agg["top_keywords"] ?? []) as $kw) {
        if (!is_array($kw)) {
            continue;
        }
        $pos = (float) ($kw["position"] ?? 0);
        $impr = (int) ($kw["impressions"] ?? 0);
        $clicks = (int) ($kw["clicks"] ?? 0);
        if ($pos >= 4 && $pos <= 20 && $impr >= 30) {
            $out[] = [
                "keyword" => (string) ($kw["keyword"] ?? ""),
                "position" => $pos,
                "impressions" => $impr,
                "clicks" => $clicks,
                "type" => "seo_quick_win",
                "score" => (int) round($impr / max(1, $pos)),
            ];
        }
    }
    usort($out, static function ($a, $b) {
        return ((int) ($b["score"] ?? 0)) <=> ((int) ($a["score"] ?? 0));
    });

    return array_slice($out, 0, 8);
}

/**
 * @param array<string, mixed> $agg
 * @param array<string, mixed> $setup
 * @return array<int, array{priority:string,category:string,title:string,detail:string,metric:string}>
 */
function ups_audit_build_recommendations(array $agg, array $setup = []): array
{
    $tips = [];
    $derived = ups_audit_compute_derived_metrics($agg);
    $deltas = (array) ($agg["deltas"] ?? []);
    $has_ga4 = (int) ($setup["ga4"] ?? 0) > 0;
    $has_gsc = (int) ($setup["gsc"] ?? 0) > 0;
    $has_ads = (int) ($setup["ads"] ?? 0) > 0;

    if (!$has_ga4 || !$has_gsc) {
        $tips[] = [
            "priority" => "high",
            "category" => "setup",
            "title" => "Uzupełnij mapowanie zasobów",
            "detail" => "Dashboard wymaga co najmniej GA4 + Search Console. Połącz konto Google, zaimportuj zasoby i przypisz je do klienta.",
            "metric" => "",
        ];
    }

    foreach ((array) ($agg["resources"] ?? []) as $res) {
        if (!is_array($res)) {
            continue;
        }
        $st = (string) (($res["health"]["status"] ?? ""));
        if ($st === "error") {
            $tips[] = [
                "priority" => "high",
                "category" => "sync",
                "title" => "Błąd sync: " . (string) ($res["title"] ?? "zasób"),
                "detail" => (string) (($res["health"]["label"] ?? "Sprawdź OAuth i uruchom sync ponownie.")),
                "metric" => strtoupper((string) ($res["type"] ?? "")),
            ];
        } elseif ($st === "warn") {
            $tips[] = [
                "priority" => "medium",
                "category" => "sync",
                "title" => "Przestarzałe dane: " . (string) ($res["title"] ?? ""),
                "detail" => "Uruchom sync, aby KPI i wykresy były aktualne (zalecane <48h).",
                "metric" => "",
            ];
        }
    }

    if ($has_gsc) {
        $gsc_delta = (float) ($deltas["gsc_clicks"] ?? 0);
        if ($gsc_delta <= -15) {
            $tips[] = [
                "priority" => "high",
                "category" => "gsc",
                "title" => "Spadek kliknięć organicznych",
                "detail" => "Kliknięcia GSC spadły o " . ups_audit_format_delta($gsc_delta) . ". Sprawdź indeksację, kanibalizację fraz i konkurencję w TOP 10.",
                "metric" => "gsc_clicks",
            ];
        } elseif ($gsc_delta >= 20) {
            $tips[] = [
                "priority" => "low",
                "category" => "gsc",
                "title" => "Wzrost ruchu z wyszukiwarki",
                "detail" => "Kliknięcia GSC rosną (" . ups_audit_format_delta($gsc_delta) . "). Rozważ skalowanie treści na frazy, które już trafiają w TOP 20.",
                "metric" => "gsc_clicks",
            ];
        }

        $ctr = (float) ($derived["gsc_ctr"] ?? 0);
        $pos = (float) ($derived["gsc_avg_position"] ?? 0);
        if ($pos > 0 && $pos <= 8 && $ctr < 1.5 && (int) ($agg["gsc_impressions"] ?? 0) >= 200) {
            $tips[] = [
                "priority" => "high",
                "category" => "gsc",
                "title" => "Niski CTR przy dobrych pozycjach",
                "detail" => "Śr. pozycja " . number_format($pos, 1, ",", " ") . ", CTR " . number_format($ctr, 2, ",", " ") . "%. Popraw title i meta description na stronach z największą liczbą wyświetleń.",
                "metric" => "ctr",
            ];
        }

        foreach (ups_audit_find_opportunities($agg) as $opp) {
            $tips[] = [
                "priority" => "medium",
                "category" => "seo",
                "title" => "Quick win: " . (string) ($opp["keyword"] ?? ""),
                "detail" => "Pozycja ~" . number_format((float) ($opp["position"] ?? 0), 1, ",", " ")
                    . ", " . (int) ($opp["impressions"] ?? 0) . " wyśw. — rozbuduj sekcję H2/H3 i linkowanie wewnętrzne.",
                "metric" => "position",
            ];
            if (count(array_filter($tips, static fn($t) => ($t["category"] ?? "") === "seo")) >= 4) {
                break;
            }
        }
    }

    if ($has_ga4) {
        $sess_delta = (float) ($deltas["ga4_sessions"] ?? 0);
        if ($sess_delta <= -12) {
            $tips[] = [
                "priority" => "high",
                "category" => "ga4",
                "title" => "Spadek sesji w GA4",
                "detail" => "Sesje spadły o " . ups_audit_format_delta($sess_delta) . ". Porównaj kanały — czy to organic, paid czy direct; sprawdź zmiany na stronie i sezonowość.",
                "metric" => "ga4_sessions",
            ];
        }

        $cr = (float) ($derived["ga4_conversion_rate"] ?? 0);
        if ((int) ($agg["ga4_sessions"] ?? 0) >= 100 && $cr < 0.8) {
            $tips[] = [
                "priority" => "high",
                "category" => "ga4",
                "title" => "Niska konwersja na sesję",
                "detail" => "CR sesja→konwersja: " . number_format($cr, 2, ",", " ") . "%. Wzmocnij CTA, skróć formularz, dodaj dowód społeczny na landingach z ruchem.",
                "metric" => "ga4_cr",
            ];
        }

        $top_ch = (array) ($agg["channels"] ?? []);
        if ($top_ch !== []) {
            $first = $top_ch[0];
            if (is_array($first) && (int) ($first["sessions"] ?? 0) >= 50) {
                $source = (string) ($first["source"] ?? "");
                $medium = (string) ($first["medium"] ?? "");
                $ch_sessions = (int) ($first["sessions"] ?? 0);
                $total_sessions = (int) ($agg["ga4_sessions"] ?? 0);
                $ch_share = $total_sessions > 0 ? round(($ch_sessions / $total_sessions) * 100, 1) : 0.0;
                $is_unattributed = function_exists("ups_audit_ga4_is_not_set_source")
                    && ups_audit_ga4_is_not_set_source($source, $medium);

                if ($is_unattributed) {
                    $tips[] = [
                        "priority" => "high",
                        "category" => "tracking",
                        "title" => "Błąd atrybucji GA4 — dominuje (not set)",
                        "detail" => $ch_sessions . " sesji ("
                            . number_format($ch_share, 1, ",", " ")
                            . "% ruchu) bez poprawnego source/medium. GA4 nie wie skąd przychodzą użytkownicy — sprawdź GTM, UTM w Ads/Meta, consent mode, cross-domain i tag GA4 na wszystkich landingach.",
                        "metric" => "ga4_not_set",
                    ];
                } else {
                    $src = trim($source . " / " . $medium);
                    $tips[] = [
                        "priority" => "low",
                        "category" => "ga4",
                        "title" => "Dominujący kanał: " . $src,
                        "detail" => $ch_sessions . " sesji ("
                            . number_format($ch_share, 1, ",", " ")
                            . "%) w okresie. Upewnij się, że landing i śledzenie konwersji są spójne dla tego źródła.",
                        "metric" => "channel",
                    ];
                }
            }
        }

        $not_set_pct = (float) ($agg["ga4_not_set_pct"] ?? 0);
        if ($not_set_pct >= 35 && (int) ($agg["ga4_sessions"] ?? 0) >= 100) {
            $tips[] = [
                "priority" => "high",
                "category" => "tracking",
                "title" => "Krytyczny udział (not set) w GA4",
                "detail" => number_format($not_set_pct, 1, ",", " ")
                    . "% sesji ("
                    . (int) ($agg["ga4_not_set_sessions"] ?? 0)
                    . " / "
                    . (int) ($agg["ga4_sessions"] ?? 0)
                    . ") bez atrybucji. Raporty kanałowe i ROAS są zawyżone/zaniżone — napraw tracking przed optymalizacją kampanii.",
                "metric" => "ga4_not_set",
            ];
        }
    }

    $sess_delta = (float) ($deltas["ga4_sessions"] ?? 0);

    if ($has_ads) {
        if (!function_exists("ups_audit_ads_api_configured") || !ups_audit_ads_api_configured()) {
            $tips[] = [
                "priority" => "medium",
                "category" => "ads",
                "title" => "Google Ads — brak Developer Token",
                "detail" => "Konto Ads jest zmapowane, ale API nie jest skonfigurowane. Uzupełnij token w Analityce SEO, aby pobierać koszty i kampanie.",
                "metric" => "",
            ];
        }

        $cost_delta = (float) ($deltas["ads_cost"] ?? 0);
        $revenue_q = (array) ($agg["revenue_quality"] ?? []);
        if ($revenue_q === [] && function_exists("ups_audit_revenue_quality")) {
            $revenue_q = ups_audit_revenue_quality($agg);
        }
        $ga4_roas_trusted = !empty($revenue_q["trusted"]);
        $roas = (float) ($derived["roas"] ?? 0);
        $crm_roas = 0.0;
        $crm_rev = (float) (($agg["intelligence"]["crm_revenue"]["funnel_totals"]["revenue"] ?? 0));
        $ads_cost_val = (float) ($agg["ads_cost"] ?? 0);
        if ($crm_rev > 0 && $ads_cost_val > 0) {
            $crm_roas = round($crm_rev / $ads_cost_val, 2);
        }

        if (!$ga4_roas_trusted) {
            $tips[] = [
                "priority" => "high",
                "category" => "tracking",
                "title" => "Revenue Quality Warning — nie ufaj ROAS GA4",
                "detail" => (string) ($revenue_q["message"] ?? "Przychód GA4 jest zawyżony lub niespójny. Decyzje opieraj na ROAS CRM i lejku lead→wygrana.")
                    . ($crm_roas > 0 ? " ROAS CRM: " . number_format($crm_roas, 1, ",", " ") . "×." : ""),
                "metric" => "revenue_quality",
            ];
        } elseif ($roas > 0 && $roas < 1 && $ads_cost_val > 100) {
            $tips[] = [
                "priority" => "high",
                "category" => "ads",
                "title" => "ROAS poniżej 1×",
                "detail" => "Przychód GA4 / koszt Ads = " . number_format($roas, 2, ",", " ") . "×. Zatrzymaj słabe kampanie, popraw śledzenie purchase/lead, przetestuj landing.",
                "metric" => "roas",
            ];
        } elseif ($roas >= 3) {
            $tips[] = [
                "priority" => "low",
                "category" => "ads",
                "title" => "Silny ROAS — możliwość skalowania",
                "detail" => "ROAS " . number_format($roas, 2, ",", " ") . "×. Rozważ kontrolowane zwiększenie budżetu na kampanie z najwyższym udziałem konwersji.",
                "metric" => "roas",
            ];
        } elseif ($crm_roas >= 3 && $crm_rev > 0) {
            $tips[] = [
                "priority" => "low",
                "category" => "crm",
                "title" => "Silny ROAS CRM — skaluj kanały z najlepszą jakością lejka",
                "detail" => "ROAS CRM " . number_format($crm_roas, 1, ",", " ") . "× (przychód wygranych / koszt Ads). Porównaj lead→oferta i oferta→wygrana per kanał.",
                "metric" => "crm_roas",
            ];
        }

        if ($cost_delta >= 25 && $sess_delta <= 5) {
            $tips[] = [
                "priority" => "medium",
                "category" => "ads",
                "title" => "Koszt Ads rośnie szybciej niż ruch",
                "detail" => "Wydatek " . ups_audit_format_delta($cost_delta) . " przy stabilnych sesjach — sprawdź CPC, jakość reklam i search terms.",
                "metric" => "ads_cost",
            ];
        }

        $cpa = (float) ($derived["ads_cpa"] ?? 0);
        if ($cpa > 0 && $cpa > 250) {
            $tips[] = [
                "priority" => "medium",
                "category" => "ads",
                "title" => "Wysoki CPA w Ads",
                "detail" => "Szac. CPA: " . number_format($cpa, 0, ",", " ") . " PLN. Segmentuj kampanie, wyklucz słabe placementy i uruchom test kreacji.",
                "metric" => "cpa",
            ];
        }
    } elseif ($has_ga4 && (int) ($agg["ga4_sessions"] ?? 0) >= 200) {
        $tips[] = [
            "priority" => "low",
            "category" => "ads",
            "title" => "Brak danych Google Ads",
            "detail" => "Duży ruch organic/direct bez widoczności paid. Jeśli klient prowadzi Ads — zmapuj konto i Developer Token.",
            "metric" => "",
        ];
    }

    if (empty($tips)) {
        $tips[] = [
            "priority" => "low",
            "category" => "general",
            "title" => "Stabilny okres",
            "detail" => "Brak krytycznych alertów. Kontynuuj sync co tydzień i generuj raport AI przed spotkaniem z klientem.",
            "metric" => "",
        ];
    }

    $prio_order = ["high" => 0, "medium" => 1, "low" => 2];
    usort($tips, static function ($a, $b) use ($prio_order) {
        $pa = $prio_order[$a["priority"] ?? "low"] ?? 9;
        $pb = $prio_order[$b["priority"] ?? "low"] ?? 9;

        return $pa <=> $pb;
    });

    return array_slice($tips, 0, 12);
}

/**
 * Wynik 0–100 dla portfolio.
 *
 * @param array<string, mixed> $agg
 */
function ups_audit_health_score(array $agg, array $setup = []): int
{
    $seo = (int) ($agg["gsc_clicks"] ?? 0);
    $seo_score = $seo >= 200 ? 75 : ($seo >= 80 ? 60 : ($seo >= 30 ? 45 : 25));

    $ads_cpa = (float) ($agg["derived"]["ads_cpa"] ?? 0);
    $ads_cost = (float) ($agg["ads_cost"] ?? 0);
    $ads_score = 50;
    if ($ads_cost > 0) {
        $ads_conv = (float) ($agg["ads_conversions"] ?? 0);
        $ads_score = $ads_conv <= 0 ? 35 : ($ads_cpa > 0 && $ads_cpa <= 120 ? 75 : ($ads_cpa <= 200 ? 65 : 55));
    }

    $not_set_pct = (float) ($agg["ga4_not_set_pct"] ?? 0);
    $tracking_score = $not_set_pct >= 45 ? 35 : ($not_set_pct >= 25 ? 50 : ($not_set_pct >= 10 ? 65 : 80));

    $conv_all = (int) ($agg["ga4_conversions_all"] ?? 0);
    $conv_macro = (int) ($agg["ga4_conversions"] ?? 0);
    $sessions = (int) ($agg["ga4_sessions"] ?? 0);
    if ($sessions > 0 && $conv_all > 0 && $conv_macro > 0 && $conv_all > $conv_macro * 3) {
        $tracking_score = max(25, $tracking_score - 15);
    }

    $ux_score = 70;
    $clarity_sess = (int) ($agg["clarity_sessions"] ?? 0);
    $clarity_dead = (int) ($agg["clarity_dead_clicks"] ?? 0);
    if ($clarity_sess > 0 && $clarity_dead > 0) {
        $dead_ratio = $clarity_dead / max(1, $clarity_sess);
        $ux_score = $dead_ratio >= 1.5 ? 45 : ($dead_ratio >= 0.8 ? 55 : ($dead_ratio >= 0.4 ? 65 : 75));
    }

    $content_score = 60;
    $kw_count = count((array) ($agg["top_keywords"] ?? []));
    if ($kw_count >= 8) {
        $content_score = 65;
    }
    if (!empty($agg["opportunities"])) {
        $content_score += 5;
    }

    $score = (int) round(($seo_score + $ads_score + $tracking_score + $ux_score + $content_score) / 5);

    foreach ((array) ($agg["resources"] ?? []) as $res) {
        if (!is_array($res)) {
            continue;
        }
        $st = (string) (($res["health"]["status"] ?? ""));
        if ($st === "error") {
            $score -= 12;
        } elseif ($st === "warn") {
            $score -= 4;
        }
    }
    if (empty($setup["is_ready"])) {
        $score -= 10;
    }

    return max(0, min(100, $score));
}

/**
 * Metryki marketingowe Upsellio (własna strona) z opcji WP + CRM.
 *
 * @return array<string, mixed>
 */
function upsellio_agency_marketing_metrics(int $range_days = 30): array
{
    $range_days = max(7, min(90, $range_days));
    $charts = function_exists("upsellio_analytics_charts_series")
        ? upsellio_analytics_charts_series($range_days)
        : [];

    $sum_series = static function (array $series): float {
        $t = 0.0;
        foreach ($series as $p) {
            if (is_array($p) && isset($p[1])) {
                $t += (float) $p[1];
            }
        }

        return $t;
    };

    $views = $sum_series((array) (($charts["views"]["current"] ?? [])));
    $views_prev = $sum_series((array) (($charts["views"]["previous"] ?? [])));
    $leads = $sum_series((array) (($charts["leads"]["current"] ?? [])));
    $leads_prev = $sum_series((array) (($charts["leads"]["previous"] ?? [])));
    $impr = $sum_series((array) (($charts["impressions"]["current"] ?? [])));
    $impr_prev = $sum_series((array) (($charts["impressions"]["previous"] ?? [])));
    $clicks = $sum_series((array) (($charts["clicks"]["current"] ?? [])));
    $clicks_prev = $sum_series((array) (($charts["clicks"]["previous"] ?? [])));

    $campaigns = (array) get_option("ups_ads_campaigns_data", []);
    $ads_cost = 0.0;
    $ads_clicks = 0;
    foreach ($campaigns as $c) {
        if (!is_array($c)) {
            continue;
        }
        $ads_cost += (float) ($c["cost_pln"] ?? 0);
        $ads_clicks += (int) ($c["clicks"] ?? 0);
    }

    $ga4_id = function_exists("upsellio_get_ga4_property_id") ? trim((string) upsellio_get_ga4_property_id()) : "";
    $gsc_prop = function_exists("upsellio_get_gsc_credentials") ? trim((string) (upsellio_get_gsc_credentials()["property"] ?? "")) : "";

    $agg = [
        "ga4_sessions" => (int) round($views),
        "ga4_sessions_prev" => (int) round($views_prev),
        "ga4_conversions" => (int) round($leads),
        "ga4_conversions_prev" => (int) round($leads_prev),
        "ga4_revenue" => 0.0,
        "ga4_revenue_prev" => 0.0,
        "gsc_clicks" => (int) round($clicks),
        "gsc_clicks_prev" => (int) round($clicks_prev),
        "gsc_impressions" => (int) round($impr),
        "gsc_impressions_prev" => (int) round($impr_prev),
        "gsc_avg_position" => 0.0,
        "ads_cost" => $ads_cost,
        "ads_cost_prev" => 0.0,
        "ads_clicks" => $ads_clicks,
        "ads_clicks_prev" => 0,
        "ads_conversions" => 0.0,
        "roas" => 0.0,
        "top_keywords" => [],
        "channels" => [],
        "campaigns" => array_slice($campaigns, 0, 15),
        "resources" => [],
        "deltas" => [
            "ga4_sessions" => ups_audit_delta_pct($views, $views_prev),
            "gsc_clicks" => ups_audit_delta_pct($clicks, $clicks_prev),
            "gsc_impressions" => ups_audit_delta_pct($impr, $impr_prev),
            "ads_cost" => 0.0,
            "roas" => 0.0,
        ],
        "timeseries" => [
            "gsc_clicks" => [],
            "ga4_sessions" => [],
            "ads_cost" => [],
        ],
    ];

    $kw_raw = (array) get_option("upsellio_keyword_metrics_rows", []);
    $kw_agg_audit = function_exists("upsellio_gsc_aggregate_keywords")
        ? upsellio_gsc_aggregate_keywords($kw_raw)
        : [];
    usort($kw_agg_audit, static function ($a, $b) {
        return ((int) ($b["impressions"] ?? 0)) <=> ((int) ($a["impressions"] ?? 0));
    });
    foreach (array_slice($kw_agg_audit, 0, 15) as $row) {
        $agg["top_keywords"][] = [
            "keyword" => (string) ($row["keyword"] ?? ""),
            "clicks" => (int) ($row["clicks"] ?? 0),
            "impressions" => (int) ($row["impressions"] ?? 0),
            "position" => (float) ($row["position"] ?? 0),
        ];
    }

    $agg["derived"] = ups_audit_compute_derived_metrics($agg);
    $agg["opportunities"] = ups_audit_find_opportunities($agg);
    $agg["recommendations"] = upsellio_agency_build_recommendations($range_days, $agg);
    $agg["health_score"] = ups_audit_health_score($agg, ["ga4" => $ga4_id !== "" ? 1 : 0, "gsc" => $gsc_prop !== "" ? 1 : 0, "ads" => count($campaigns) > 0 ? 1 : 0, "is_ready" => $ga4_id !== "" && $gsc_prop !== ""]);
    $agg["charts"] = $charts;
    $agg["sources"] = [
        "ga4" => $ga4_id !== "" ? "connected" : "views_tracker",
        "gsc" => $gsc_prop !== "" ? $gsc_prop : "keywords_option",
        "ads" => count($campaigns) > 0 ? "ups_ads_campaigns_data" : "none",
    ];

    return $agg;
}

/**
 * @param array<string, mixed>|null $prefill
 * @return array<int, array<string, string>>
 */
function upsellio_agency_build_recommendations(int $range_days = 30, ?array $prefill = null): array
{
    $metrics = is_array($prefill) ? $prefill : upsellio_agency_marketing_metrics($range_days);
    $setup = [
        "ga4" => 1,
        "gsc" => 1,
        "ads" => !empty($metrics["campaigns"]) ? 1 : 0,
        "is_ready" => true,
    ];
    $tips = ups_audit_build_recommendations($metrics, $setup);

    $has_refresh = function_exists("upsellio_get_gsc_credentials")
        && trim((string) (upsellio_get_gsc_credentials()["refresh_token"] ?? "")) !== "";
    if (!$has_refresh) {
        array_unshift($tips, [
            "priority" => "high",
            "category" => "setup",
            "title" => "Połącz Google (Upsellio Connect)",
            "detail" => "Brak refresh tokena w Analityce SEO — uruchom „Zaloguj przez Google”, aby odświeżać GSC i GA4 automatycznie.",
            "metric" => "",
        ]);
    }

    $ai_review = get_option("ups_ai_ads_review", []);
    if (is_array($ai_review) && !empty($ai_review["summary"])) {
        $tips[] = [
            "priority" => "medium",
            "category" => "ads",
            "title" => "AI: przegląd kampanii Ads",
            "detail" => (string) $ai_review["summary"],
            "metric" => "ai_ads",
        ];
    }

    return array_slice($tips, 0, 12);
}
