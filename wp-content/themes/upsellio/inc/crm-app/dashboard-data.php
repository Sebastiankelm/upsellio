<?php

if (!defined("ABSPATH")) {
    exit;
}

/**
 * Wartość deala w PLN (szacunek pod KPI i snapshot).
 */
function upsellio_crm_app_offer_estimated_value_pln($offer_id)
{
    $offer_id = (int) $offer_id;
    if ($offer_id <= 0) {
        return 0.0;
    }
    $won = (float) get_post_meta($offer_id, "_ups_offer_won_value", true);
    if ($won > 0) {
        return $won;
    }
    $raw = (string) get_post_meta($offer_id, "_ups_offer_price", true);
    $raw = preg_replace("/[^\d.,\-]/u", "", $raw);
    $raw = str_replace(",", ".", $raw);
    $v = (float) $raw;

    return max(0.0, $v);
}

/**
 * Granice czasu dla filtrów pulpitu (bieżący vs poprzedni okres tej samej długości).
 *
 * @return array{start:int,end:int,prev_start:int,prev_end:int,len_days:int}
 */
function upsellio_crm_app_dashboard_period_bounds($period_key)
{
    $period_key = sanitize_key((string) $period_key);
    $allowed = ["today", "7d", "30d", "month", "quarter"];
    if (!in_array($period_key, $allowed, true)) {
        $period_key = "30d";
    }
    $tz = wp_timezone();
    $now = new DateTimeImmutable("now", $tz);
    $end = $now->getTimestamp();
    if ($period_key === "today") {
        $start_dt = $now->setTime(0, 0, 0);
        $start = $start_dt->getTimestamp();
        $len_sec = max(1, $end - $start);
        $prev_end = $start - 1;
        $prev_start = $prev_end - $len_sec;
    } elseif ($period_key === "7d") {
        $start_dt = $now->modify("-7 days");
        $start = $start_dt->getTimestamp();
        $len_sec = max(1, $end - $start);
        $prev_end = $start - 1;
        $prev_start = $prev_end - $len_sec;
    } elseif ($period_key === "30d") {
        $start_dt = $now->modify("-30 days");
        $start = $start_dt->getTimestamp();
        $len_sec = max(1, $end - $start);
        $prev_end = $start - 1;
        $prev_start = $prev_end - $len_sec;
    } elseif ($period_key === "month") {
        $start_dt = $now->modify("first day of this month")->setTime(0, 0, 0);
        $start = $start_dt->getTimestamp();
        $len_sec = max(1, $end - $start);
        $prev_end = $start - 1;
        $prev_month_last = $start_dt->modify("-1 day");
        $prev_start = $prev_month_last->modify("first day of this month")->setTime(0, 0, 0)->getTimestamp();
    } else {
        $quarter = (int) ceil($now->format("n") / 3);
        $q_start_month = ($quarter - 1) * 3 + 1;
        $start_dt = $now->setDate((int) $now->format("Y"), $q_start_month, 1)->setTime(0, 0, 0);
        $start = $start_dt->getTimestamp();
        $len_sec = max(1, $end - $start);
        $prev_end = $start - 1;
        $prev_q_start = $start_dt->modify("-3 months");
        $prev_start = $prev_q_start->getTimestamp();
    }
    $len_days = max(1, (int) ceil($len_sec / DAY_IN_SECONDS));

    return [
        "start" => $start,
        "end" => $end,
        "prev_start" => $prev_start,
        "prev_end" => $prev_end,
        "len_days" => $len_days,
    ];
}

function upsellio_crm_app_dashboard_source_bucket($haystack)
{
    $h = strtolower(trim((string) $haystack));
    if ($h === "") {
        return __("nieznane", "upsellio");
    }
    $paid = preg_match('/\b(cpc|ppc|paid|ads|gads|adwords)\b/', $h) === 1;
    if (strpos($h, "seo") !== false || strpos($h, "organic") !== false) {
        return "SEO";
    }
    if ($paid && preg_match('/\b(google|gads|adwords)\b/', $h)) {
        return "Google Ads";
    }
    if ($paid && preg_match('/\b(meta|facebook|instagram|fb|ig)\b/', $h)) {
        return "Meta Ads";
    }
    if (strpos($h, "direct") !== false || strpos($h, "(direct)") !== false) {
        return "Direct";
    }
    if (strpos($h, "referral") !== false || strpos($h, "ref") !== false) {
        return "Referral";
    }

    return trim(mb_substr($haystack, 0, 32));
}

/**
 * @param string $filter all|seo|google|meta|direct|referral|paid
 */
function upsellio_crm_app_dashboard_lead_passes_source_filter($lead_id, $filter)
{
    $filter = sanitize_key((string) $filter);
    if ($filter === "" || $filter === "all") {
        return true;
    }
    $utm = strtolower((string) get_post_meta((int) $lead_id, "_ups_lead_utm_source", true));
    $utm_m = strtolower((string) get_post_meta((int) $lead_id, "_ups_lead_utm_medium", true));
    $src = strtolower((string) get_post_meta((int) $lead_id, "_ups_lead_source", true));
    $hay = $utm . " " . $utm_m . " " . $src;
    $bucket = upsellio_crm_app_dashboard_source_bucket($hay);
    $buckets = ["seo" => "SEO", "google" => "Google Ads", "meta" => "Meta Ads", "direct" => "Direct", "referral" => "Referral"];
    if (isset($buckets[$filter])) {
        return $bucket === $buckets[$filter];
    }
    if ($filter === "paid") {
        return in_array($bucket, ["Google Ads", "Meta Ads"], true) || (preg_match('/\b(cpc|ppc|paid|ads)\b/', $hay) === 1 && $bucket !== "SEO");
    }

    return true;
}

function upsellio_crm_app_dashboard_lead_passes_service_filter($lead_id, $needle)
{
    $needle = trim(mb_strtolower((string) $needle));
    if ($needle === "" || $needle === "all") {
        return true;
    }
    $need = mb_strtolower((string) get_post_meta((int) $lead_id, "_ups_lead_need", true));
    $title = mb_strtolower((string) get_the_title((int) $lead_id));

    return strpos($need, $needle) !== false || strpos($title, $needle) !== false;
}

/**
 * Agregacja pulpitu operacyjnego.
 *
 * @param array<int,WP_Post> $leads
 * @param array<int,WP_Post> $offers
 * @param array<int,WP_Post> $tasks
 * @param array{period:string,source:string,service:string,trend_days:int} $args
 * @return array<string,mixed>
 */
function upsellio_crm_app_dashboard_gmt_timestamp($raw)
{
    $raw = trim((string) $raw);
    if ($raw === "" || $raw === "0000-00-00 00:00:00") {
        return false;
    }
    return strtotime($raw . " UTC");
}

function upsellio_crm_app_build_dashboard_payload(array $leads, array $offers, array $tasks, array $args)
{
    $period_key = isset($args["period"]) ? sanitize_key((string) $args["period"]) : "30d";
    $src_filter = isset($args["source"]) ? sanitize_key((string) $args["source"]) : "all";
    $svc_filter = isset($args["service"]) ? sanitize_text_field((string) $args["service"]) : "all";
    $trend_days = isset($args["trend_days"]) ? max(7, min(120, (int) $args["trend_days"])) : 30;

    $b = upsellio_crm_app_dashboard_period_bounds($period_key);
    $start = (int) $b["start"];
    $end = (int) $b["end"];
    $ps = (int) $b["prev_start"];
    $pe = (int) $b["prev_end"];

    $leads_f = [];
    foreach ($leads as $lp) {
        $lid = (int) $lp->ID;
        if (!upsellio_crm_app_dashboard_lead_passes_source_filter($lid, $src_filter)) {
            continue;
        }
        if (!upsellio_crm_app_dashboard_lead_passes_service_filter($lid, $svc_filter)) {
            continue;
        }
        $leads_f[] = $lp;
    }

    $count_leads_in_range = static function ($posts, $t0, $t1) {
        $n = 0;
        foreach ($posts as $p) {
            $ts = upsellio_crm_app_dashboard_gmt_timestamp($p->post_date_gmt);
            if ($ts !== false && $ts >= $t0 && $ts <= $t1) {
                $n++;
            }
        }

        return $n;
    };

    $new_leads_cur = $count_leads_in_range($leads_f, $start, $end);
    $new_leads_prev = $count_leads_in_range($leads_f, $ps, $pe);
    $lead_delta_pct = $new_leads_prev > 0 ? (int) round((($new_leads_cur - $new_leads_prev) / $new_leads_prev) * 100) : null;

    $need_contact = 0;
    $need_contact_overdue = 0;
    $now_ts = time();
    foreach ($leads_f as $lp) {
        $lid = (int) $lp->ID;
        $st = (string) get_post_meta($lid, "_ups_lead_qualification_status", true);
        if (in_array($st, ["qualified", "rejected", "converted", "nurturing"], true)) {
            continue;
        }
        if ($st !== "new" && $st !== "") {
            continue;
        }
        $need_contact++;
        $created = upsellio_crm_app_dashboard_gmt_timestamp($lp->post_date_gmt);
        if ($created !== false && ($now_ts - $created) > 48 * HOUR_IN_SECONDS) {
            $need_contact_overdue++;
        }
    }

    $offers_followup = 0;
    $offers_followup_stale = 0;
    foreach ($offers as $op) {
        $oid = (int) $op->ID;
        $ost = (string) get_post_meta($oid, "_ups_offer_status", true);
        if (!in_array($ost, ["open", "sent"], true)) {
            continue;
        }
        $offers_followup++;
        $mod = upsellio_crm_app_dashboard_gmt_timestamp($op->post_modified_gmt);
        if ($mod !== false && ($now_ts - $mod) > 3 * DAY_IN_SECONDS) {
            $offers_followup_stale++;
        }
    }

    $pipeline_open_value = 0.0;
    $nearest_win_value = 0.0;
    $nearest_win_id = 0;
    $pipeline_weighted_value = 0.0;
    foreach ($offers as $op) {
        $oid = (int) $op->ID;
        $ost = (string) get_post_meta($oid, "_ups_offer_status", true);
        if (in_array($ost, ["won", "lost"], true)) {
            continue;
        }
        $v = upsellio_crm_app_offer_estimated_value_pln($oid);
        $pipeline_open_value += $v;
        $prob = (int) get_post_meta($oid, "_ups_offer_deal_probability_0_100", true);
        $weighted = $v * max(0, min(100, $prob)) / 100;
        $pipeline_weighted_value += $weighted;
        if ($weighted > $nearest_win_value) {
            $nearest_win_value = $weighted;
            $nearest_win_id = $oid;
        }
    }

    $kpi_leads_tone = "neutral";
    if ($lead_delta_pct !== null && $lead_delta_pct >= 10) {
        $kpi_leads_tone = "up";
    } elseif ($lead_delta_pct !== null && $lead_delta_pct <= -10) {
        $kpi_leads_tone = "down";
    }

    $priorities = [];
    foreach ($leads_f as $lp) {
        $lid = (int) $lp->ID;
        $st = (string) get_post_meta($lid, "_ups_lead_qualification_status", true);
        if (in_array($st, ["converted", "rejected"], true)) {
            continue;
        }
        $score = (int) get_post_meta($lid, "_ups_lead_score_0_100", true);
        $created = upsellio_crm_app_dashboard_gmt_timestamp($lp->post_date_gmt);
        $age_h = $created !== false ? max(0, ($now_ts - $created) / 3600) : 0;
        $surfaced = ($st === "new" || $st === "" || $st === "nurturing") && ($score >= 55 || $age_h <= 36 || $score >= 70);
        if (!$surfaced) {
            continue;
        }
        $pri = (int) round($score * 0.6 + min(40, $age_h));
        if ($score >= 70 && ($st === "new" || $st === "")) {
            $pri += 50;
        }
        $src_label = upsellio_crm_app_dashboard_source_bucket(
            (string) get_post_meta($lid, "_ups_lead_utm_source", true) . " " . (string) get_post_meta($lid, "_ups_lead_utm_medium", true) . " " . (string) get_post_meta($lid, "_ups_lead_source", true)
        );
        $priorities[] = [
            "priority" => min(999, $pri),
            "type" => __("Lead", "upsellio"),
            "title" => (string) $lp->post_title,
            "subtitle" => $src_label . " · score " . $score,
            "href" => add_query_arg(["view" => "leads", "lead_tab" => "new"], home_url("/crm-app/")),
            "cta" => __("Skontaktuj się", "upsellio"),
            "value_pln" => (float) get_post_meta($lid, "_ups_lead_budget", true),
            "age" => $created !== false ? human_time_diff($created, $now_ts) : "",
        ];
    }

    foreach ($offers as $op) {
        $oid = (int) $op->ID;
        $ost = (string) get_post_meta($oid, "_ups_offer_status", true);
        if ($ost !== "sent") {
            continue;
        }
        $mod = upsellio_crm_app_dashboard_gmt_timestamp($op->post_modified_gmt);
        if ($mod === false || ($now_ts - $mod) < 3 * DAY_IN_SECONDS) {
            continue;
        }
        $cid = (int) get_post_meta($oid, "_ups_offer_client_id", true);
        $cname = $cid > 0 ? get_the_title($cid) : $op->post_title;
        $v = upsellio_crm_app_offer_estimated_value_pln($oid);
        $priorities[] = [
            "priority" => 200 + min(100, (int) (($now_ts - $mod) / DAY_IN_SECONDS)),
            "type" => __("Oferta", "upsellio"),
            "title" => $cname,
            "subtitle" => __("Bez odpowiedzi od ", "upsellio") . human_time_diff($mod, $now_ts),
            "href" => add_query_arg(["view" => "pipeline"], home_url("/crm-app/")),
            "cta" => __("Pipeline", "upsellio"),
            "value_pln" => $v,
            "age" => human_time_diff($mod, $now_ts),
        ];
    }

    foreach ($tasks as $tk) {
        $tid = (int) $tk->ID;
        $tst = (string) get_post_meta($tid, "_upsellio_task_status", true);
        if (in_array($tst, ["done", "cancelled"], true)) {
            continue;
        }
        $due = (int) get_post_meta($tid, "_upsellio_task_due_at", true);
        if ($due <= 0 || $due >= $now_ts) {
            continue;
        }
        $oid = (int) get_post_meta($tid, "_upsellio_task_offer_id", true);
        $priorities[] = [
            "priority" => 300 + min(200, (int) (($now_ts - $due) / HOUR_IN_SECONDS)),
            "type" => __("Zadanie", "upsellio"),
            "title" => (string) $tk->post_title,
            "subtitle" => $oid > 0 ? get_the_title($oid) : "",
            "href" => add_query_arg(["view" => "tasks"], home_url("/crm-app/")),
            "cta" => __("Zadania", "upsellio"),
            "value_pln" => 0.0,
            "age" => human_time_diff($due, $now_ts),
        ];
    }

    usort($priorities, static function ($a, $b) {
        return ($b["priority"] ?? 0) <=> ($a["priority"] ?? 0);
    });
    $priorities = array_slice($priorities, 0, 25);

    $stage_labels = [
        "awareness" => __("Świadomość", "upsellio"),
        "consideration" => __("Rozważanie", "upsellio"),
        "decision" => __("Decyzja", "upsellio"),
        "offer_sent" => __("Oferta wysłana", "upsellio"),
    ];
    $snapshot = [];
    foreach ($stage_labels as $sk => $slab) {
        $snapshot[$sk] = ["label" => $slab, "count" => 0, "value_pln" => 0.0, "alert" => ""];
    }
    foreach ($offers as $op) {
        $oid = (int) $op->ID;
        $ost = (string) get_post_meta($oid, "_ups_offer_status", true);
        if (in_array($ost, ["won", "lost"], true)) {
            continue;
        }
        $v = upsellio_crm_app_offer_estimated_value_pln($oid);
        if ($ost === "sent") {
            $snapshot["offer_sent"]["count"]++;
            $snapshot["offer_sent"]["value_pln"] += $v;
            $mod = upsellio_crm_app_dashboard_gmt_timestamp($op->post_modified_gmt);
            if ($mod !== false && ($now_ts - $mod) > 3 * DAY_IN_SECONDS) {
                $snapshot["offer_sent"]["alert"] = __("Część ofert bez odzewu > 3 dni", "upsellio");
            }
        } else {
            $stage = (string) get_post_meta($oid, "_ups_offer_stage", true);
            if (!isset($snapshot[$stage]) || $stage === "offer_sent") {
                $stage = "awareness";
            }
            if (isset($snapshot[$stage])) {
                $snapshot[$stage]["count"]++;
                $snapshot[$stage]["value_pln"] += $v;
            }
        }
    }

    $snap_total_pln = 0.0;
    foreach ($snapshot as $snap_row) {
        $snap_total_pln += (float) ($snap_row["value_pln"] ?? 0);
    }
    foreach ($snapshot as $sk => $snap_row) {
        $snapshot[$sk]["share_pct"] = $snap_total_pln > 0
            ? (int) round(((float) ($snap_row["value_pln"] ?? 0) / $snap_total_pln) * 100)
            : 0;
    }

    $sources_rows = [];
    foreach ($leads_f as $lp) {
        $lid = (int) $lp->ID;
        $bucket = upsellio_crm_app_dashboard_source_bucket(
            (string) get_post_meta($lid, "_ups_lead_utm_source", true) . " " . (string) get_post_meta($lid, "_ups_lead_utm_medium", true) . " " . (string) get_post_meta($lid, "_ups_lead_source", true)
        );
        if (!isset($sources_rows[$bucket])) {
            $sources_rows[$bucket] = [
                "label" => $bucket,
                "leads" => 0,
                "qualified" => 0,
                "offers_won" => 0,
                "offers_any" => 0,
            ];
        }
        $sources_rows[$bucket]["leads"]++;
        $st = (string) get_post_meta($lid, "_ups_lead_qualification_status", true);
        if ($st === "qualified") {
            $sources_rows[$bucket]["qualified"]++;
        }
    }
    foreach ($offers as $op) {
        $oid = (int) $op->ID;
        $hay = strtolower((string) get_post_meta($oid, "_ups_offer_utm_source", true) . " " . (string) get_post_meta($oid, "_ups_offer_source", true));
        $bucket = upsellio_crm_app_dashboard_source_bucket($hay);
        if (!isset($sources_rows[$bucket])) {
            $sources_rows[$bucket] = [
                "label" => $bucket,
                "leads" => 0,
                "qualified" => 0,
                "offers_won" => 0,
                "offers_any" => 0,
            ];
        }
        $sources_rows[$bucket]["offers_any"]++;
        if ((string) get_post_meta($oid, "_ups_offer_status", true) === "won") {
            $sources_rows[$bucket]["offers_won"]++;
        }
    }

    $lost_reasons = [];
    foreach ($offers as $op) {
        $oid = (int) $op->ID;
        if ((string) get_post_meta($oid, "_ups_offer_status", true) !== "lost") {
            continue;
        }
        $r = (string) get_post_meta($oid, "_ups_offer_loss_reason", true);
        if ($r === "") {
            $r = "unknown";
        }
        if (!isset($lost_reasons[$r])) {
            $lost_reasons[$r] = 0;
        }
        $lost_reasons[$r]++;
    }
    arsort($lost_reasons);

    $tz = wp_timezone();
    $trend_labels = [];
    $series_leads = [];
    $series_qualified = [];
    $series_sent = [];
    $series_won = [];
    // Aggregate once per record instead of rescanning all posts for every day.
    $days_by_key = [];
    $today = new DateTimeImmutable("today", $tz);
    for ($i = $trend_days - 1; $i >= 0; $i--) {
        $day = $today->modify("-" . $i . " days");
        $key = $day->format("Y-m-d");
        $days_by_key[$key] = ["leads" => 0, "qualified" => 0, "sent" => 0, "won" => 0];
        $trend_labels[] = $day->format("d.m");
    }
    $key_for_date = static function ($raw, DateTimeZone $source_tz) use ($tz): string {
        $raw = trim((string) $raw);
        if ($raw === "" || $raw === "0000-00-00 00:00:00") {
            return "";
        }
        try {
            return (new DateTimeImmutable($raw, $source_tz))->setTimezone($tz)->format("Y-m-d");
        } catch (Exception $e) {
            return "";
        }
    };
    $utc = new DateTimeZone("UTC");
    foreach ($leads_f as $lp) {
        $key = $key_for_date($lp->post_date_gmt, $utc);
        if (!isset($days_by_key[$key])) {
            continue;
        }
        $days_by_key[$key]["leads"]++;
        if ((string) get_post_meta($lp->ID, "_ups_lead_qualification_status", true) === "qualified") {
            $days_by_key[$key]["qualified"]++;
        }
    }
    foreach ($offers as $op) {
        $oid = (int) $op->ID;
        $status = (string) get_post_meta($oid, "_ups_offer_status", true);
        $sent_at = (string) get_post_meta($oid, "_ups_offer_first_sent_at", true);
        $sent_key = $sent_at !== "" ? $key_for_date($sent_at, $tz) : "";
        if (isset($days_by_key[$sent_key])) {
            $days_by_key[$sent_key]["sent"]++;
        }
        if ($status === "won") {
            $closed_at = (string) get_post_meta($oid, "_ups_offer_closed_at", true);
            $won_key = $closed_at !== "" ? $key_for_date($closed_at, $tz) : "";
            if (isset($days_by_key[$won_key])) {
                $days_by_key[$won_key]["won"]++;
            }
        }
    }
    foreach ($days_by_key as $counts) {
        $series_leads[] = $counts["leads"];
        $series_qualified[] = $counts["qualified"];
        $series_sent[] = $counts["sent"];
        $series_won[] = $counts["won"];
    }

    $diag_parts = [];
    if ($new_leads_prev === 0 && $new_leads_cur > 0) {
        $diag_parts[] = __("Pojawiły się nowe leady; brak dodatniej bazy do porównania procentowego.", "upsellio");
    } elseif ($new_leads_cur > $new_leads_prev * 1.2) {
        $diag_parts[] = __("Leady rosną vs poprzedni okres.", "upsellio");
    } elseif ($new_leads_cur < $new_leads_prev * 0.8 && $new_leads_prev > 0) {
        $diag_parts[] = __("Spadek liczby leadów vs poprzedni okres — sprawdź źródła.", "upsellio");
    }
    $diag = $diag_parts !== [] ? implode(" ", $diag_parts) : __("Obserwuj trend i jakość kwalifikacji per źródło.", "upsellio");

    $alerts = [];
    if ($need_contact_overdue > 0) {
        $alerts[] = sprintf(
            /* translators: %d: count */
            __("Masz %d leadów „do kontaktu” z opóźnieniem (>48h).", "upsellio"),
            $need_contact_overdue
        );
    }
    if ($offers_followup_stale > 0) {
        $alerts[] = sprintf(__("%d ofert bez aktywności > 3 dni.", "upsellio"), $offers_followup_stale);
    }

    $hot_leads = [];
    foreach ($leads_f as $lp) {
        $lid = (int) $lp->ID;
        $score = (int) get_post_meta($lid, "_ups_lead_score_0_100", true);
        $st = (string) get_post_meta($lid, "_ups_lead_qualification_status", true);
        if ($score < 60 || in_array($st, ["converted", "rejected"], true)) {
            continue;
        }
        $created = upsellio_crm_app_dashboard_gmt_timestamp($lp->post_date_gmt);
        $hot_leads[] = [
            "post" => $lp,
            "score" => $score,
            "age_h" => $created !== false ? ($now_ts - $created) / 3600 : 0,
        ];
    }
    usort($hot_leads, static function ($a, $b) {
        return ($b["score"] ?? 0) <=> ($a["score"] ?? 0);
    });
    $hot_leads = array_slice($hot_leads, 0, 8);

    $end_today = (new DateTimeImmutable("tomorrow", $tz))->getTimestamp();
    $actions_today = (int) count(array_filter($tasks, static function ($t) use ($end_today) {
        $st = (string) get_post_meta((int) $t->ID, "_upsellio_task_status", true);
        $due = (int) get_post_meta((int) $t->ID, "_upsellio_task_due_at", true);
        return !in_array($st, ["done", "cancelled"], true) && $due > 0 && $due < $end_today;
    }));

    return [
        "bounds" => $b,
        "kpi" => [
            "new_leads" => $new_leads_cur,
            "new_leads_prev" => $new_leads_prev,
            "new_leads_delta_pct" => $lead_delta_pct,
            "new_leads_tone" => $kpi_leads_tone,
            "need_contact" => $need_contact,
            "need_contact_overdue" => $need_contact_overdue,
            "offers_followup" => $offers_followup,
            "offers_followup_stale" => $offers_followup_stale,
            "pipeline_open_value" => $pipeline_open_value,
            "nearest_win_value" => $nearest_win_value,
            "pipeline_weighted_value" => $pipeline_weighted_value,
            "nearest_win_id" => $nearest_win_id,
        ],
        "priorities" => $priorities,
        "snapshot" => $snapshot,
        "sources_rows" => array_values($sources_rows),
        "lost_reasons" => $lost_reasons,
        "trend" => [
            "labels" => $trend_labels,
            "leads" => $series_leads,
            "qualified" => $series_qualified,
            "sent" => $series_sent,
            "won" => $series_won,
        ],
        "diagnosis" => $diag,
        "alerts" => $alerts,
        "hot_leads" => $hot_leads,
        "actions_today_hint" => max($need_contact + $offers_followup_stale, $actions_today),
        "tasks_due_today_or_overdue" => $actions_today,
    ];
}
