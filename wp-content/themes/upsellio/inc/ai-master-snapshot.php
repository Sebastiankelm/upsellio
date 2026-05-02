<?php

if (!defined("ABSPATH")) {
    exit;
}

/**
 * Centralny snapshot AI — budowany raz dziennie przez WP-Cron.
 * Przechowywany w ups_ai_master_snapshot (+ ups_ai_master_snapshot_built).
 *
 * Filozofia: bloki odpowiadają na pytanie „co z tego wynika dla biznesu”, nie surowe liczby.
 */

/**
 * @return array<string,mixed>
 */
function upsellio_ai_master_build(): array
{
    $snapshot = [
        "built" => current_time("mysql"),
        "sales" => upsellio_ai_snap_sales(),
        "clients" => upsellio_ai_snap_clients(),
        "blog" => upsellio_ai_snap_blog(),
        "channels" => upsellio_ai_snap_channels(),
        "leads" => upsellio_ai_snap_leads(),
    ];
    update_option("ups_ai_master_snapshot", $snapshot, false);
    update_option("ups_ai_master_snapshot_built", current_time("mysql"), false);

    return $snapshot;
}

/**
 * @return array<string,mixed>
 */
function upsellio_ai_master_get($force = false)
{
    $force = (bool) $force;
    if (!$force) {
        $built = (string) get_option("ups_ai_master_snapshot_built", "");
        if ($built !== "") {
            $ts = strtotime($built);
            if ($ts !== false && (time() - $ts) < DAY_IN_SECONDS) {
                $cached = get_option("ups_ai_master_snapshot", []);
                if (is_array($cached) && isset($cached["sales"]) && isset($cached["leads"])) {
                    return $cached;
                }
            }
        }
    }

    return upsellio_ai_master_build();
}

/**
 * @return array<string,mixed>
 */
function upsellio_ai_snap_sales(): array
{
    $offers = get_posts([
        "post_type" => "crm_offer",
        "post_status" => ["publish", "private"],
        "posts_per_page" => 200,
        "fields" => "ids",
    ]);

    $won = 0;
    $lost = 0;
    $open = 0;
    $won_values = [];
    $ttc_days = [];
    $win_reasons = [];
    $loss_reasons = [];
    $won_by_industry = [];
    $won_by_scope = [];

    foreach ($offers as $oid) {
        $oid = (int) $oid;
        $status = (string) get_post_meta($oid, "_ups_offer_status", true);
        $value = (float) get_post_meta($oid, "_ups_offer_won_value", true);
        $cid = (int) get_post_meta($oid, "_ups_offer_client_id", true);
        $ind = $cid > 0 ? (string) get_post_meta($cid, "_ups_client_industry", true) : "";

        if ($status === "won") {
            $won++;
            if ($value > 0) {
                $won_values[] = $value;
            }

            $accepted_s = (string) get_post_meta($oid, "_ups_offer_accepted_at", true);
            $accepted = strtotime($accepted_s);
            $created_meta = strtotime((string) get_post_meta($oid, "_ups_offer_created_at", true));
            $created = $created_meta > 0 ? $created_meta : (int) get_post_time("U", true, $oid);
            if ($accepted && $created && $accepted > $created) {
                $ttc_days[] = (int) round(($accepted - $created) / DAY_IN_SECONDS);
            }

            $wr = (string) get_post_meta($oid, "_ups_offer_win_reason", true);
            if ($wr !== "") {
                $win_reasons[$wr] = ($win_reasons[$wr] ?? 0) + 1;
            }
            if ($ind !== "") {
                $won_by_industry[$ind] = ($won_by_industry[$ind] ?? 0) + 1;
            }

            $scope_parts = [];
            if ((string) get_post_meta($oid, "_ups_offer_has_google", true) !== "0") {
                $scope_parts[] = "google";
            }
            if ((string) get_post_meta($oid, "_ups_offer_has_meta", true) !== "0") {
                $scope_parts[] = "meta";
            }
            if ((string) get_post_meta($oid, "_ups_offer_has_web", true) === "1") {
                $scope_parts[] = "web";
            }
            $scope = implode("+", $scope_parts);
            if ($scope !== "") {
                $won_by_scope[$scope] = ($won_by_scope[$scope] ?? 0) + 1;
            }
        } elseif ($status === "lost") {
            $lost++;
            $lr = (string) get_post_meta($oid, "_ups_offer_loss_reason", true);
            if ($lr !== "") {
                $loss_reasons[$lr] = ($loss_reasons[$lr] ?? 0) + 1;
            }
        } elseif ($status === "open") {
            $open++;
        }
    }

    arsort($win_reasons);
    arsort($loss_reasons);
    arsort($won_by_industry);
    arsort($won_by_scope);

    return [
        "won" => $won,
        "lost" => $lost,
        "open" => $open,
        "win_rate_pct" => ($won + $lost) > 0 ? (int) round($won / ($won + $lost) * 100) : 0,
        "avg_won_value_pln" => $won_values !== [] ? (int) round(array_sum($won_values) / count($won_values)) : 0,
        "avg_ttc_days" => $ttc_days !== [] ? (int) round(array_sum($ttc_days) / count($ttc_days)) : 0,
        "top_win_reasons" => array_slice($win_reasons, 0, 3, true),
        "top_loss_reasons" => array_slice($loss_reasons, 0, 3, true),
        "top_industries" => array_slice($won_by_industry, 0, 3, true),
        "top_scopes" => array_slice($won_by_scope, 0, 3, true),
    ];
}

/**
 * @return array<string,mixed>
 */
function upsellio_ai_snap_clients(): array
{
    $clients = get_posts([
        "post_type" => "crm_client",
        "post_status" => "publish",
        "posts_per_page" => 200,
        "fields" => "ids",
    ]);

    $active_mrr = 0.0;
    $active_count = 0;
    $cancelled_90d = 0;
    $paused = 0;
    $mrr_by_industry = [];
    $avg_ltv_data = [];

    $threshold_90 = time() - 90 * DAY_IN_SECONDS;

    foreach ($clients as $cid) {
        $cid = (int) $cid;
        $sub = (string) get_post_meta($cid, "_ups_client_subscription_status", true);
        $mrr = (float) get_post_meta($cid, "_ups_client_monthly_value", true);
        $ind = (string) get_post_meta($cid, "_ups_client_industry", true);

        $start_s = (string) get_post_meta($cid, "_ups_client_billing_start", true);
        $cancel_s = (string) get_post_meta($cid, "_ups_client_cancellation_date", true);
        $start = strtotime($start_s);
        $cancel = strtotime($cancel_s);

        if ($sub === "active") {
            $active_count++;
            $active_mrr += $mrr;
            if ($ind !== "" && $mrr > 0) {
                $mrr_by_industry[$ind] = ($mrr_by_industry[$ind] ?? 0.0) + $mrr;
            }
            if ($start && $mrr > 0) {
                $months = (int) round((time() - $start) / (30 * DAY_IN_SECONDS));
                if ($months > 0) {
                    $avg_ltv_data[] = $mrr * $months;
                }
            }
        } elseif ($sub === "cancelled") {
            if ($cancel && $cancel > $threshold_90) {
                $cancelled_90d++;
            }
            if ($start && $cancel && $mrr > 0) {
                $months = (int) round(($cancel - $start) / (30 * DAY_IN_SECONDS));
                if ($months > 0) {
                    $avg_ltv_data[] = $mrr * $months;
                }
            }
        } elseif ($sub === "paused") {
            $paused++;
        }
    }

    arsort($mrr_by_industry);
    $base_churn = max(1, $active_count + $cancelled_90d);

    return [
        "active_count" => $active_count,
        "active_mrr_pln" => (int) round($active_mrr),
        "paused_count" => $paused,
        "churn_90d" => $cancelled_90d,
        "churn_rate_pct" => round(($cancelled_90d / $base_churn) * 100, 1),
        "avg_ltv_pln" => $avg_ltv_data !== [] ? (int) round(array_sum($avg_ltv_data) / count($avg_ltv_data)) : 0,
        "top_mrr_industries" => array_slice($mrr_by_industry, 0, 3, true),
    ];
}

/**
 * Agreguje źródło UTM do kubełka (jak dashboard CRM).
 *
 * @param string $haystack
 * @return string
 */
function upsellio_ai_snap_lead_source_bucket($haystack)
{
    if (function_exists("upsellio_crm_app_dashboard_source_bucket")) {
        return upsellio_crm_app_dashboard_source_bucket($haystack);
    }
    $h = strtolower((string) $haystack);
    if ($h === "") {
        return "nieznane";
    }
    if (strpos($h, "google") !== false || (strpos($h, "ads") !== false && strpos($h, "meta") === false)) {
        return "Google Ads";
    }
    if (strpos($h, "meta") !== false || strpos($h, "facebook") !== false) {
        return "Meta Ads";
    }
    if (strpos($h, "seo") !== false || strpos($h, "organic") !== false) {
        return "SEO";
    }
    if (strpos($h, "direct") !== false || strpos($h, "(direct)") !== false) {
        return "Direct";
    }
    if (strpos($h, "referral") !== false || strpos($h, "ref") !== false) {
        return "Referral";
    }

    return function_exists("mb_substr") ? trim(mb_substr($haystack, 0, 32, "UTF-8")) : trim(substr($haystack, 0, 32));
}

/**
 * @return array<string,mixed>
 */
function upsellio_ai_snap_blog(): array
{
    $posts = get_posts([
        "post_type" => "post",
        "post_status" => "publish",
        "posts_per_page" => 50,
        "orderby" => "date",
        "order" => "DESC",
        "fields" => "ids",
    ]);

    $kw_opt = get_option("upsellio_keyword_metrics_rows", []);
    $kw_rows = is_array($kw_opt) ? $kw_opt : [];

    $gsc_by_path = [];
    foreach ($kw_rows as $row) {
        $url = (string) ($row["url"] ?? "");
        $cl = (int) ($row["clicks"] ?? 0);
        $path = (string) wp_parse_url($url, PHP_URL_PATH);
        if ($path === "") {
            continue;
        }
        $gsc_by_path[$path] = ($gsc_by_path[$path] ?? 0) + $cl;
    }

    $converting = [];
    $no_traffic = [];
    $high_traffic_no_leads = [];
    $bot_posts = 0;
    $total_views_30d = 0;
    $total_leads = 0;

    $dates_30d = [];
    for ($i = 29; $i >= 0; $i--) {
        $dates_30d[] = wp_date("Y-m-d", strtotime("-{$i} days"));
    }
    $from_30d = $dates_30d[0];

    $dv_opt = get_option("upsellio_daily_views", []);
    $daily_views = is_array($dv_opt) ? $dv_opt : [];

    foreach ($posts as $pid) {
        $pid = (int) $pid;
        $url = (string) get_permalink($pid);
        $title = get_the_title($pid);
        $is_bot = (string) get_post_meta($pid, "_ups_blog_bot_keyword", true) !== "";
        if ($is_bot) {
            $bot_posts++;
        }

        $views_30d = 0;
        foreach ($dates_30d as $d) {
            $views_30d += (int) ($daily_views[$d][$pid] ?? 0);
        }
        $total_views_30d += $views_30d;

        $leads_count = 0;
        if (function_exists("upsellio_get_leads_for_post_url")) {
            $leads_count = (int) upsellio_get_leads_for_post_url($url, $from_30d);
        }
        $total_leads += $leads_count;

        $path = (string) wp_parse_url($url, PHP_URL_PATH);
        $gsc_clicks = $path !== "" ? (int) ($gsc_by_path[$path] ?? 0) : 0;

        if ($leads_count > 0) {
            $converting[] = [
                "title" => $title,
                "leads" => $leads_count,
                "views" => $views_30d,
                "gsc_clicks" => $gsc_clicks,
            ];
        } elseif ($gsc_clicks > 20 && $leads_count === 0) {
            $high_traffic_no_leads[] = [
                "title" => $title,
                "gsc_clicks" => $gsc_clicks,
                "views" => $views_30d,
            ];
        } elseif ($views_30d < 10 && $gsc_clicks < 5) {
            $no_traffic[] = $title;
        }
    }

    usort($converting, function ($a, $b) {
        return (int) ($b["leads"] ?? 0) <=> (int) ($a["leads"] ?? 0);
    });
    usort($high_traffic_no_leads, function ($a, $b) {
        return (int) ($b["gsc_clicks"] ?? 0) <=> (int) ($a["gsc_clicks"] ?? 0);
    });

    return [
        "total_posts" => count($posts),
        "bot_generated_posts" => $bot_posts,
        "total_views_30d" => $total_views_30d,
        "total_leads_30d" => $total_leads,
        "blog_conversion_rate" => $total_views_30d > 0 ? round(($total_leads / $total_views_30d) * 100, 2) : 0,
        "converting_posts" => array_slice($converting, 0, 5),
        "high_traffic_no_leads" => array_slice($high_traffic_no_leads, 0, 5),
        "no_traffic_count" => count($no_traffic),
    ];
}

/**
 * @return array<string,mixed>
 */
function upsellio_ai_snap_channels(): array
{
    $scores = get_option("ups_automation_channel_quality_scores", []);
    if (!is_array($scores) || $scores === []) {
        return ["status" => "no_ga4_data"];
    }

    $top = [];
    $worst = [];
    $arr = array_values($scores);
    usort($arr, function ($a, $b) {
        return (int) ($b["score"] ?? 0) <=> (int) ($a["score"] ?? 0);
    });

    foreach (array_slice($arr, 0, 3) as $ch) {
        $top[] = [
            "source" => (string) ($ch["source"] ?? ""),
            "campaign" => (string) ($ch["campaign"] ?? ""),
            "score" => (int) ($ch["score"] ?? 0),
            "sessions" => (int) ($ch["sessions"] ?? 0),
            "conversions" => (int) ($ch["conversions"] ?? 0),
        ];
    }

    $tail = array_slice($arr, -3);
    foreach ($tail as $ch) {
        if ((int) ($ch["score"] ?? 0) < 30) {
            $worst[] = [
                "source" => (string) ($ch["source"] ?? ""),
                "score" => (int) ($ch["score"] ?? 0),
            ];
        }
    }

    return [
        "top_channels" => $top,
        "underperforming" => $worst,
        "total_channels" => count($arr),
    ];
}

/**
 * @param int   $lead_id
 * @param array $acc   by ref counters etc.
 * @param array $scores
 * @param array $response_times_h
 * @param array $lead_sources
 * @return void
 */
function upsellio_ai_snap_lead_aggregate_one($lead_id, array &$acc, array &$scores, array &$response_times_h, array &$lead_sources)
{
    $lead_id = (int) $lead_id;
    $ptype = get_post_type($lead_id);
    $created = (int) get_post_time("U", true, $lead_id);
    $first_contact = 0;
    $score = 0;
    $src_parts = [];

    if ($ptype === "crm_lead") {
        $st = (string) get_post_meta($lead_id, "_ups_lead_qualification_status", true);
        if ($st === "new" || $st === "") {
            $acc["new"]++;
        } elseif ($st === "qualified") {
            $acc["qualified"]++;
        } elseif ($st === "converted") {
            $acc["converted"]++;
        } elseif ($st === "rejected") {
            $acc["rejected"]++;
        } else {
            $acc["new"]++;
        }
        $score = (int) get_post_meta($lead_id, "_ups_lead_score_0_100", true);
        if ($score <= 0) {
            $score = (int) get_post_meta($lead_id, "_upsellio_lead_score", true);
        }
        $src_parts[] = (string) get_post_meta($lead_id, "_ups_lead_utm_source", true);
        $src_parts[] = (string) get_post_meta($lead_id, "_ups_lead_source", true);
    } else {
        $terms = wp_get_object_terms($lead_id, "lead_status", ["fields" => "slugs"]);
        $slug = "";
        if (is_array($terms) && $terms !== [] && !is_wp_error($terms)) {
            $slug = (string) $terms[0];
        }
        if ($slug === "new" || $slug === "") {
            $acc["new"]++;
        } elseif ($slug === "qualified" || $slug === "proposal") {
            $acc["qualified"]++;
        } elseif ($slug === "won") {
            $acc["converted"]++;
        } elseif ($slug === "lost") {
            $acc["rejected"]++;
        } elseif ($slug === "contacted") {
            $acc["contacted"]++;
        } else {
            $acc["new"]++;
        }
        $score = (int) get_post_meta($lead_id, "_upsellio_lead_score", true);
        $src_parts[] = (string) get_post_meta($lead_id, "_upsellio_lead_utm_source", true);
        $src_parts[] = (string) get_post_meta($lead_id, "_upsellio_lead_utm_medium", true);
    }

    if ($score > 0) {
        $scores[] = $score;
    }

    $fc_s = (string) get_post_meta($lead_id, "_upsellio_first_contact_at", true);
    $first_contact = strtotime($fc_s);
    if ($first_contact && $created && $first_contact > $created) {
        $response_times_h[] = round(($first_contact - $created) / 3600, 1);
    }

    $hay = strtolower(trim(implode(" ", array_filter($src_parts))));
    $bucket = upsellio_ai_snap_lead_source_bucket($hay !== "" ? $hay : implode(" ", $src_parts));
    $lead_sources[$bucket] = ($lead_sources[$bucket] ?? 0) + 1;
}

/**
 * @return array<string,mixed>
 */
function upsellio_ai_snap_leads(): array
{
    $acc = [
        "new" => 0,
        "qualified" => 0,
        "converted" => 0,
        "rejected" => 0,
        "contacted" => 0,
    ];
    $scores = [];
    $response_times_h = [];
    $lead_sources = [];

    $lead_types = ["lead"];
    if (post_type_exists("crm_lead")) {
        $lead_types[] = "crm_lead";
    }

    $lead_posts = get_posts([
        "post_type" => $lead_types,
        "post_status" => "publish",
        "posts_per_page" => 200,
        "orderby" => "date",
        "order" => "DESC",
        "fields" => "ids",
    ]);

    foreach ($lead_posts as $lid) {
        upsellio_ai_snap_lead_aggregate_one((int) $lid, $acc, $scores, $response_times_h, $lead_sources);
    }

    arsort($lead_sources);
    $total = count($lead_posts);
    $conv_den = max(1, $acc["converted"] + $acc["rejected"]);

    return [
        "total" => $total,
        "new_uncontacted" => $acc["new"],
        "contacted" => $acc["contacted"],
        "qualified" => $acc["qualified"],
        "converted" => $acc["converted"],
        "rejected" => $acc["rejected"],
        "conversion_rate_pct" => (int) round(($acc["converted"] / $conv_den) * 100),
        "avg_ai_score" => $scores !== [] ? (int) round(array_sum($scores) / count($scores)) : 0,
        "avg_response_time_h" => $response_times_h !== [] ? round(array_sum($response_times_h) / count($response_times_h), 1) : null,
        "top_sources" => array_slice($lead_sources, 0, 4, true),
    ];
}

/**
 * Tekst do promptu wg zakresu.
 *
 * @param string $scope scoring|blog|offer|full
 * @return string
 */
function upsellio_ai_master_context($scope = "scoring")
{
    $scope = (string) $scope;
    $s = upsellio_ai_master_get();
    if ($s === [] || !is_array($s)) {
        return "";
    }

    $lines = [];

    if (in_array($scope, ["scoring", "full"], true)) {
        $sal = isset($s["sales"]) && is_array($s["sales"]) ? $s["sales"] : [];
        if (!empty($sal["won"]) || !empty($sal["lost"])) {
            $lines[] = "TWOJE WYNIKI SPRZEDAŻOWE:";
            $lines[] = "- Wygrane oferty: {$sal['won']}, win rate: {$sal['win_rate_pct']}%, śr. wartość wygranych: {$sal['avg_won_value_pln']} PLN";
            $lines[] = "- Śr. czas od draftu do akceptacji (oferty won): {$sal['avg_ttc_days']} dni";
            if (!empty($sal["top_industries"])) {
                $lines[] = "- Najczęstsze branże wygranych: " . implode(", ", array_keys($sal["top_industries"]));
            }
            if (!empty($sal["top_win_reasons"])) {
                $lines[] = "- Główne powody wygranych: " . implode(", ", array_keys($sal["top_win_reasons"]));
            }
            if (!empty($sal["top_loss_reasons"])) {
                $lines[] = "- Najczęstsze powody przegranych: " . implode(", ", array_keys($sal["top_loss_reasons"]));
            }
            if (!empty($sal["top_scopes"]) && ($scope === "full" || $scope === "offer")) {
                $lines[] = "- Typowe zakresy (Google/Meta/Web) w wygranych: " . implode(", ", array_keys($sal["top_scopes"]));
            }
        }

        $ld = isset($s["leads"]) && is_array($s["leads"]) ? $s["leads"] : [];
        if (!empty($ld["total"])) {
            $lines[] = "LEADY (formularz + CRM, ostatnie 200 wpisów):";
            $lines[] = "- Śr. AI score (gdzie jest): {$ld['avg_ai_score']}/100, udział „skutecznie zamkniętych” vs odrzuconych (converted vs rejected): {$ld['conversion_rate_pct']}%";
            if ($ld["avg_response_time_h"] !== null) {
                $lines[] = "- Śr. czas do pierwszego kontaktu: {$ld['avg_response_time_h']}h";
            }
            if (!empty($ld["top_sources"])) {
                $src_bits = [];
                foreach ($ld["top_sources"] as $k => $v) {
                    $src_bits[] = $k . " (" . (int) $v . ")";
                }
                $lines[] = "- Top źródła (kubełki): " . implode(", ", $src_bits);
            }
        }
    }

    if (in_array($scope, ["blog", "full"], true)) {
        $bl = isset($s["blog"]) && is_array($s["blog"]) ? $s["blog"] : [];
        if ((int) ($bl["total_posts"] ?? 0) > 0) {
            $lines[] = "BLOG (ostatnie 50 wpisów, leady z path = artykuł, " . ($bl["blog_conversion_rate"] ?? 0) . "% lead/view 30 dni):";
        }
        if (!empty($bl["converting_posts"])) {
            $lines[] = "BLOG — CO GENERUJE LEADY (ostatnie 30 dni):";
            foreach (array_slice($bl["converting_posts"], 0, 3) as $bp) {
                $lines[] = "- \"" . ($bp["title"] ?? "") . "\" → " . (int) ($bp["leads"] ?? 0) . " leadów";
            }
        }
        if (!empty($bl["high_traffic_no_leads"])) {
            $lines[] = "BLOG — RUCH (GSC) BEZ LEADÓW (kandydaci do CTA):";
            foreach (array_slice($bl["high_traffic_no_leads"], 0, 3) as $bp) {
                $lines[] = "- \"" . ($bp["title"] ?? "") . "\" → " . (int) ($bp["gsc_clicks"] ?? 0) . " kliknięć GSC, 0 leadów z URL wpisu";
            }
        }
        if (isset($bl["no_traffic_count"]) && (int) $bl["no_traffic_count"] > 0) {
            $lines[] = "- Wpisy praktycznie bez ruchu (niski GSC + widoki 30d): ok. {$bl['no_traffic_count']} szt.";
        }
    }

    if (in_array($scope, ["offer", "full"], true)) {
        $ch = isset($s["channels"]) && is_array($s["channels"]) ? $s["channels"] : [];
        if (!empty($ch["top_channels"])) {
            $lines[] = "NAJLEPSZE KANAŁY (GA4 — automation scores):";
            foreach ($ch["top_channels"] as $c) {
                $lines[] = "- " . ($c["source"] ?? "") . "/" . ($c["campaign"] ?? "") . ": score " . (int) ($c["score"] ?? 0) . "/100, konwersje GA4: " . (int) ($c["conversions"] ?? 0);
            }
        }

        $cl = isset($s["clients"]) && is_array($s["clients"]) ? $s["clients"] : [];
        if (!empty($cl["active_count"])) {
            $lines[] = "KLIENCI: MRR {$cl['active_mrr_pln']} PLN ({$cl['active_count']} aktywnych), churn 90d (proxy): {$cl['churn_rate_pct']}%, szac. śr. LTV: {$cl['avg_ltv_pln']} PLN";
        }

        $sal2 = isset($s["sales"]) && is_array($s["sales"]) ? $s["sales"] : [];
        if (!empty($sal2["top_scopes"]) && $scope === "offer") {
            $lines[] = "WYGRANE — częste kombinacje zakresu: " . implode(", ", array_keys($sal2["top_scopes"]));
        }
    }

    return implode("\n", array_filter($lines, static function ($ln) {
        return $ln !== "";
    }));
}

add_action("upsellio_ai_master_daily_build", "upsellio_ai_master_build");

add_action("init", function () {
    if (!wp_next_scheduled("upsellio_ai_master_daily_build")) {
        $t = strtotime("tomorrow 05:00:00");
        if ($t === false) {
            $t = time() + HOUR_IN_SECONDS;
        }
        wp_schedule_event($t, "daily", "upsellio_ai_master_daily_build");
    }
}, 30);
