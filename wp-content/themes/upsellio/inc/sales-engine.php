<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_sales_engine_ensure_defaults()
{
    $defaults = [
        "ups_sales_intent_weight" => 60,
        "ups_sales_fit_weight" => 40,
        "ups_sales_hot_index_threshold" => 72,
        "ups_sales_playbook_awareness_delay_h" => 24,
        "ups_sales_playbook_consideration_delay_h" => 48,
        "ups_sales_playbook_decision_delay_h" => 7,
        "ups_sales_channel_email_enabled" => "1",
        "ups_sales_spf_ok" => "0",
        "ups_sales_dkim_ok" => "0",
        "ups_sales_dmarc_ok" => "0",
        "ups_sales_warmup_notes" => "",
        "ups_hybrid_weight_source" => 15,
        "ups_hybrid_weight_fit" => 25,
        "ups_hybrid_weight_intent" => 30,
        "ups_hybrid_weight_timing" => 15,
        "ups_hybrid_weight_value" => 15,
    ];
    foreach ($defaults as $key => $value) {
        if (get_option($key, null) === null) {
            update_option($key, $value, false);
        }
    }
}
add_action("init", "upsellio_sales_engine_ensure_defaults", 5);

function upsellio_sales_engine_get_client_person_id($client_id)
{
    $client_id = (int) $client_id;
    if ($client_id <= 0) {
        return "";
    }
    $existing = (string) get_post_meta($client_id, "_ups_client_person_id", true);
    if ($existing !== "") {
        return $existing;
    }
    $person_id = "psn_" . strtolower(wp_generate_password(16, false, false));
    update_post_meta($client_id, "_ups_client_person_id", $person_id);
    return $person_id;
}

function upsellio_sales_engine_compute_fit_score($offer_id)
{
    $offer_id = (int) $offer_id;
    $client_id = (int) get_post_meta($offer_id, "_ups_offer_client_id", true);
    if ($client_id <= 0) {
        return 20;
    }
    $company = (string) get_post_meta($client_id, "_ups_client_company", true);
    $email = (string) get_post_meta($client_id, "_ups_client_email", true);
    $phone = (string) get_post_meta($client_id, "_ups_client_phone", true);
    $industry = (string) get_post_meta($client_id, "_ups_client_industry", true);
    $company_size = (string) get_post_meta($client_id, "_ups_client_company_size", true);
    $budget_range = (string) get_post_meta($client_id, "_ups_client_budget_range", true);

    $score = 0;
    if ($company !== "") {
        $score += 10;
    }
    if (is_email($email)) {
        $score += 15;
    }
    if ($phone !== "") {
        $score += 10;
    }
    if ($industry !== "") {
        $score += 15;
    }
    if ($company_size !== "") {
        $score += 15;
    }
    if ($budget_range !== "") {
        $score += 20;
    }
    if ($score <= 0) {
        $score = 20;
    }
    return min(100, $score);
}

function upsellio_sales_engine_refresh_hot_index($offer_id, $summary, $stage)
{
    $offer_id = (int) $offer_id;
    $intent_score = (int) ($summary["score"] ?? 0);
    $fit_score = upsellio_sales_engine_compute_fit_score($offer_id);
    $intent_weight = max(1, (int) get_option("ups_sales_intent_weight", 60));
    $fit_weight = max(1, (int) get_option("ups_sales_fit_weight", 40));
    $total_weight = $intent_weight + $fit_weight;
    $hot_index = (int) round((($intent_score * $intent_weight) + ($fit_score * $fit_weight)) / $total_weight);
    $threshold = (int) get_option("ups_sales_hot_index_threshold", 72);
    $is_hot = $hot_index >= $threshold;

    update_post_meta($offer_id, "_ups_offer_intent_score", $intent_score);
    update_post_meta($offer_id, "_ups_offer_fit_score", $fit_score);
    update_post_meta($offer_id, "_ups_offer_hot_index", $hot_index);
    update_post_meta($offer_id, "_ups_offer_hot_offer", $is_hot ? "1" : "0");
    update_post_meta($offer_id, "_ups_offer_stage", sanitize_key((string) $stage));
    $action = "Niski priorytet: utrzymuj lekki follow-up edukacyjny.";
    if ($fit_score >= 75 && $intent_score < 35) {
        $action = "Wysoki fit / niski intent: wyslij krotki email z case study i 1 CTA.";
    } elseif ($fit_score < 45 && $intent_score >= 70) {
        $action = "Wysoki intent / niski fit: zweryfikuj budget i zakres przed dalszym domykaniem.";
    } elseif ($fit_score >= 70 && $intent_score >= 70) {
        $action = "Priorytet A: natychmiastowy follow-up domykajacy.";
    }
    update_post_meta($offer_id, "_ups_offer_action_recommendation", $action);

    $client_id = (int) get_post_meta($offer_id, "_ups_offer_client_id", true);
    if ($client_id > 0) {
        update_post_meta($offer_id, "_ups_offer_person_id", upsellio_sales_engine_get_client_person_id($client_id));
    }

    upsellio_sales_engine_refresh_hybrid_deal_scores($offer_id, $intent_score, $fit_score, (string) $stage);

    if ($is_hot) {
        upsellio_sales_engine_enqueue_playbook_tasks($offer_id, (string) $stage);
    }
}
add_action("upsellio_offer_scores_refreshed", "upsellio_sales_engine_refresh_hot_index", 10, 3);

function upsellio_sales_engine_hybrid_weights()
{
    $w = [
        "source" => max(1, (int) get_option("ups_hybrid_weight_source", 15)),
        "fit" => max(1, (int) get_option("ups_hybrid_weight_fit", 25)),
        "intent" => max(1, (int) get_option("ups_hybrid_weight_intent", 30)),
        "timing" => max(1, (int) get_option("ups_hybrid_weight_timing", 15)),
        "value" => max(1, (int) get_option("ups_hybrid_weight_value", 15)),
    ];
    $sum = array_sum($w);
    if ($sum <= 0) {
        return ["source" => 20, "fit" => 20, "intent" => 20, "timing" => 20, "value" => 20];
    }
    return $w;
}

function upsellio_sales_engine_score_source_quality_0_100($source_key)
{
    $source_key = strtolower(trim((string) $source_key));
    if ($source_key === "") {
        return 35;
    }
    $tiers = [
        "referral" => 95,
        "partner" => 90,
        "inbound" => 82,
        "organic" => 78,
        "direct" => 70,
        "form" => 68,
        "csv" => 45,
        "cold" => 38,
        "unknown" => 40,
    ];
    foreach ($tiers as $needle => $score) {
        if (strpos($source_key, $needle) !== false) {
            return $score;
        }
    }
    return 55;
}

function upsellio_sales_engine_score_timing_0_100($decision_date_str, $qual_status = "")
{
    $qual_status = sanitize_key((string) $qual_status);
    if ($qual_status === "qualified") {
        return 88;
    }
    if ($decision_date_str === "") {
        return 42;
    }
    $ts = strtotime((string) $decision_date_str);
    if ($ts === false) {
        return 42;
    }
    $days = ($ts - time()) / DAY_IN_SECONDS;
    if ($days < 0) {
        return 95;
    }
    if ($days <= 14) {
        return 85;
    }
    if ($days <= 45) {
        return 62;
    }
    return 40;
}

function upsellio_sales_engine_score_value_potential_0_100($budget_or_won, $potential_key = "")
{
    $potential_key = sanitize_key((string) $potential_key);
    $bonus = $potential_key === "high" ? 15 : ($potential_key === "low" ? -12 : 0);
    $amount = 0.0;
    if (is_numeric($budget_or_won)) {
        $amount = (float) $budget_or_won;
    } elseif (is_string($budget_or_won)) {
        $amount = upsellio_sales_engine_parse_amount($budget_or_won);
    }
    if ($amount >= 100000) {
        return min(100, 92 + $bonus);
    }
    if ($amount >= 30000) {
        return min(100, 78 + $bonus);
    }
    if ($amount >= 10000) {
        return min(100, 65 + $bonus);
    }
    if ($amount >= 3000) {
        return min(100, 52 + $bonus);
    }
    if ($amount > 0) {
        return min(100, 40 + $bonus);
    }
    return max(0, min(100, 35 + $bonus));
}

function upsellio_sales_engine_refresh_hybrid_deal_scores($offer_id, $intent_score, $fit_score, $stage)
{
    $offer_id = (int) $offer_id;
    if ($offer_id <= 0) {
        return;
    }
    $w = upsellio_sales_engine_hybrid_weights();
    $total_w = array_sum($w);
    $source_raw = (string) get_post_meta($offer_id, "_ups_offer_utm_source", true);
    if ($source_raw === "") {
        $source_raw = (string) get_post_meta($offer_id, "_ups_offer_last_utm_source", true);
    }
    $source_q = upsellio_sales_engine_score_source_quality_0_100($source_raw !== "" ? $source_raw : "unknown");
    $ch = (int) get_post_meta($offer_id, "_ups_offer_channel_quality_score", true);
    if ($ch > 0) {
        $source_q = (int) round(($source_q * 0.65) + ($ch * 0.35));
    }
    $timing = 50;
    $client_id = (int) get_post_meta($offer_id, "_ups_offer_client_id", true);
    if ($client_id > 0) {
        $timing = upsellio_sales_engine_score_timing_0_100("", "");
    }
    $stage = sanitize_key((string) $stage);
    if ($stage === "decision") {
        $timing = min(100, $timing + 25);
    } elseif ($stage === "consideration") {
        $timing = min(100, $timing + 12);
    }
    $price_raw = (string) get_post_meta($offer_id, "_ups_offer_price", true);
    $value_s = upsellio_sales_engine_score_value_potential_0_100($price_raw, "");
    $intent_score = max(0, min(100, (int) $intent_score));
    $fit_score = max(0, min(100, (int) $fit_score));
    $lead_score = (
        ($source_q * $w["source"]) +
        ($fit_score * $w["fit"]) +
        ($intent_score * $w["intent"]) +
        ($timing * $w["timing"]) +
        ($value_s * $w["value"])
    ) / $total_w;
    $lead_score = (int) round(max(0, min(100, $lead_score)));
    $deal_probability = (int) round(max(0, min(100, ($lead_score * 0.55) + ($intent_score * 0.35) + ($fit_score * 0.1))));
    $temp = "cold";
    if ($lead_score >= 75 || $deal_probability >= 72) {
        $temp = "hot";
    } elseif ($lead_score >= 50 || $deal_probability >= 48) {
        $temp = "warm";
    }
    update_post_meta($offer_id, "_ups_offer_source_quality_score", $source_q);
    update_post_meta($offer_id, "_ups_offer_timing_score", $timing);
    update_post_meta($offer_id, "_ups_offer_value_score", $value_s);
    update_post_meta($offer_id, "_ups_offer_lead_score_0_100", $lead_score);
    update_post_meta($offer_id, "_ups_offer_deal_probability_0_100", $deal_probability);
    update_post_meta($offer_id, "_ups_offer_temperature", $temp);
}

function upsellio_sales_engine_refresh_lead_hybrid_scores($lead_id)
{
    $lead_id = (int) $lead_id;
    if ($lead_id <= 0 || get_post_type($lead_id) !== "crm_lead") {
        return;
    }
    $w = upsellio_sales_engine_hybrid_weights();
    $total_w = array_sum($w);
    $source_raw = (string) get_post_meta($lead_id, "_ups_lead_source", true);
    if ($source_raw === "") {
        $source_raw = (string) get_post_meta($lead_id, "_ups_lead_utm_source", true);
    }
    $source_q = upsellio_sales_engine_score_source_quality_0_100($source_raw);
    $fit = 40;
    $budget = (float) get_post_meta($lead_id, "_ups_lead_budget", true);
    if ($budget > 0) {
        $fit += 25;
    }
    $need = (string) get_post_meta($lead_id, "_ups_lead_need", true);
    if (strlen($need) > 20) {
        $fit += 15;
    }
    $fit = max(0, min(100, $fit));
    $intent = 35;
    $qual = (string) get_post_meta($lead_id, "_ups_lead_qualification_status", true);
    if ($qual === "qualified") {
        $intent += 35;
    } elseif ($qual === "nurturing") {
        $intent += 15;
    }
    $intent = max(0, min(100, $intent));
    $timing = upsellio_sales_engine_score_timing_0_100((string) get_post_meta($lead_id, "_ups_lead_decision_date", true), $qual);
    $potential = (string) get_post_meta($lead_id, "_ups_lead_potential", true);
    $value_s = upsellio_sales_engine_score_value_potential_0_100($budget, $potential);
    $lead_score = (
        ($source_q * $w["source"]) +
        ($fit * $w["fit"]) +
        ($intent * $w["intent"]) +
        ($timing * $w["timing"]) +
        ($value_s * $w["value"])
    ) / $total_w;
    $lead_score = (int) round(max(0, min(100, $lead_score)));
    $deal_probability = (int) round(max(0, min(100, ($lead_score * 0.5) + ($intent * 0.35) + ($fit * 0.15))));
    $temp = "cold";
    if ($lead_score >= 72) {
        $temp = "hot";
    } elseif ($lead_score >= 48) {
        $temp = "warm";
    }
    update_post_meta($lead_id, "_ups_lead_score_0_100", $lead_score);
    update_post_meta($lead_id, "_ups_lead_deal_probability_0_100", $deal_probability);
    update_post_meta($lead_id, "_ups_lead_temperature", $temp);
}

/**
 * Raport marketing -> revenue (ROAS / ROI) per klucz źródło|kampania.
 *
 * @return list<array{key:string, source:string, campaign:string, spend:float, leads:int, won:int, revenue:float, roas:float, roi_pct:float}>
 */
function upsellio_sales_engine_build_roas_report_rows()
{
    $costs = upsellio_sales_engine_get_campaign_costs();
    if (!is_array($costs)) {
        $costs = [];
    }
    $normalize = static function ($source, $campaign) {
        return strtolower(trim((string) $source) . "|" . trim((string) $campaign));
    };
    $rows = [];
    foreach ($costs as $key => $amount) {
        $raw_key = (string) $key;
        if (trim($raw_key) === "") {
            continue;
        }
        $parts = array_map("trim", explode("|", str_replace(" | ", "|", $raw_key), 2));
        $src = isset($parts[0]) ? (string) $parts[0] : "unknown";
        $camp = isset($parts[1]) ? (string) $parts[1] : "";
        $nk = $normalize($src, $camp);
        if (!isset($rows[$nk])) {
            $rows[$nk] = [
                "key" => $nk,
                "source" => $src,
                "campaign" => $camp,
                "spend" => 0.0,
                "leads" => 0,
                "won" => 0,
                "revenue" => 0.0,
            ];
        }
        $rows[$nk]["spend"] += max(0.0, (float) $amount);
    }
    if (post_type_exists("crm_lead")) {
        $leads = get_posts([
            "post_type" => "crm_lead",
            "post_status" => ["publish", "draft", "pending", "private"],
            "posts_per_page" => 2000,
            "fields" => "ids",
        ]);
        foreach ($leads as $lid) {
            $lid = (int) $lid;
            $s = (string) get_post_meta($lid, "_ups_lead_utm_source", true);
            if ($s === "") {
                $s = (string) get_post_meta($lid, "_ups_lead_source", true);
            }
            $c = (string) get_post_meta($lid, "_ups_lead_utm_campaign", true);
            $nk = $normalize($s !== "" ? $s : "unknown", $c);
            if (!isset($rows[$nk])) {
                $rows[$nk] = [
                    "key" => $nk,
                    "source" => $s !== "" ? $s : "unknown",
                    "campaign" => $c,
                    "spend" => 0.0,
                    "leads" => 0,
                    "won" => 0,
                    "revenue" => 0.0,
                ];
            }
            $rows[$nk]["leads"]++;
        }
    }
    $offers = get_posts([
        "post_type" => "crm_offer",
        "post_status" => ["publish", "draft", "pending", "private"],
        "posts_per_page" => 2000,
    ]);
    foreach ($offers as $offer) {
        $oid = (int) $offer->ID;
        if ((string) get_post_meta($oid, "_ups_offer_status", true) !== "won") {
            continue;
        }
        $s = (string) get_post_meta($oid, "_ups_offer_utm_source", true);
        $c = (string) get_post_meta($oid, "_ups_offer_utm_campaign", true);
        $nk = $normalize($s !== "" ? $s : "unknown", $c);
        if (!isset($rows[$nk])) {
            $rows[$nk] = [
                "key" => $nk,
                "source" => $s !== "" ? $s : "unknown",
                "campaign" => $c,
                "spend" => 0.0,
                "leads" => 0,
                "won" => 0,
                "revenue" => 0.0,
            ];
        }
        $rows[$nk]["won"]++;
        $rows[$nk]["revenue"] += (float) get_post_meta($oid, "_ups_offer_won_value", true);
    }
    $out = [];
    foreach ($rows as $r) {
        $spend = max(0.0001, (float) $r["spend"]);
        $rev = (float) $r["revenue"];
        $r["roas"] = round($rev / $spend, 2);
        $r["roi_pct"] = $spend > 0 ? round((($rev - (float) $r["spend"]) / $spend) * 100, 1) : 0.0;
        $out[] = $r;
    }
    usort($out, static function ($a, $b) {
        return ((float) ($b["revenue"] ?? 0) <=> (float) ($a["revenue"] ?? 0));
    });
    return $out;
}

/**
 * @return array{owners: array, sources: array, price_bands: array, time_to_close_days: array{avg:float,count:int}, forecast_weighted: float}
 */
function upsellio_sales_engine_build_decision_layer_analytics()
{
    $owners = [];
    $sources = [];
    $price_bands = ["0-5k" => ["won" => 0, "revenue" => 0.0], "5-20k" => ["won" => 0, "revenue" => 0.0], "20k+" => ["won" => 0, "revenue" => 0.0]];
    $ttc_sum = 0;
    $ttc_n = 0;
    $forecast = 0.0;
    $offers = get_posts([
        "post_type" => "crm_offer",
        "post_status" => ["publish", "draft", "pending", "private"],
        "posts_per_page" => 2000,
    ]);
    foreach ($offers as $offer) {
        $oid = (int) $offer->ID;
        $status = (string) get_post_meta($oid, "_ups_offer_status", true);
        $owner = (int) get_post_meta($oid, "_ups_offer_owner_id", true);
        $owner_label = $owner > 0 ? (string) get_the_author_meta("display_name", $owner) : "nieprzypisany";
        if (!isset($owners[$owner_label])) {
            $owners[$owner_label] = ["deals" => 0, "won" => 0, "revenue" => 0.0, "lost" => 0];
        }
        $owners[$owner_label]["deals"]++;
        $src = (string) get_post_meta($oid, "_ups_offer_utm_source", true);
        if ($src === "") {
            $src = "unknown";
        }
        if (!isset($sources[$src])) {
            $sources[$src] = ["deals" => 0, "won" => 0, "revenue" => 0.0];
        }
        $sources[$src]["deals"]++;
        $price_raw = (string) get_post_meta($oid, "_ups_offer_price", true);
        $price_val = upsellio_sales_engine_parse_amount($price_raw);
        $band = "0-5k";
        if ($price_val >= 20000) {
            $band = "20k+";
        } elseif ($price_val >= 5000) {
            $band = "5-20k";
        }
        $prob = (int) get_post_meta($oid, "_ups_offer_deal_probability_0_100", true);
        if ($prob <= 0) {
            $prob = (int) get_post_meta($oid, "_ups_offer_hot_index", true);
        }
        if ($prob <= 0) {
            $prob = 35;
        }
        $deal_val = $price_val > 0 ? $price_val : (float) get_post_meta($oid, "_ups_offer_won_value", true);
        if ($status !== "won" && $status !== "lost") {
            $forecast += $deal_val * ($prob / 100);
        }
        if ($status === "won") {
            $owners[$owner_label]["won"]++;
            $won_rev = (float) get_post_meta($oid, "_ups_offer_won_value", true);
            $owners[$owner_label]["revenue"] += $won_rev;
            $sources[$src]["won"]++;
            $sources[$src]["revenue"] += $won_rev;
            $price_bands[$band]["won"]++;
            $price_bands[$band]["revenue"] += $won_rev;
            $closed = (string) get_post_meta($oid, "_ups_offer_closed_at", true);
            $start = strtotime((string) $offer->post_date_gmt);
            $end = $closed !== "" ? strtotime($closed) : false;
            if ($start && $end && $end > $start) {
                $ttc_sum += ($end - $start) / DAY_IN_SECONDS;
                $ttc_n++;
            }
        } elseif ($status === "lost") {
            $owners[$owner_label]["lost"]++;
        }
    }
    return [
        "owners" => $owners,
        "sources" => $sources,
        "price_bands" => $price_bands,
        "time_to_close_days" => [
            "avg" => $ttc_n > 0 ? round($ttc_sum / $ttc_n, 1) : 0.0,
            "count" => $ttc_n,
        ],
        "forecast_weighted" => round($forecast, 2),
    ];
}

function upsellio_sales_engine_enqueue_playbook_tasks($offer_id, $stage)
{
    $offer_id = (int) $offer_id;
    $stage = sanitize_key((string) $stage);
    if ($offer_id <= 0) {
        return;
    }
    $existing = (string) get_post_meta($offer_id, "_ups_offer_playbook_stage", true);
    if ($existing === $stage) {
        return;
    }
    $owner_id = (int) get_post_meta($offer_id, "_ups_offer_owner_id", true);
    if ($owner_id <= 0) {
        $owner_id = function_exists("upsellio_crm_get_default_owner_id") ? (int) upsellio_crm_get_default_owner_id() : 1;
        $owner_email = $owner_id > 0 ? sanitize_email((string) get_the_author_meta("user_email", $owner_id)) : "";
        if (is_email($owner_email)) {
            wp_mail(
                $owner_email,
                "Przypisano fallback ownera oferty",
                "Oferta #" . $offer_id . " (" . (string) get_the_title($offer_id) . ") nie ma ownera. Zadanie playbook zostalo przypisane fallback ownerowi."
            );
        }
    }
    $hours = $stage === "decision"
        ? (int) get_option("ups_sales_playbook_decision_delay_h", 7)
        : ($stage === "consideration" ? (int) get_option("ups_sales_playbook_consideration_delay_h", 48) : (int) get_option("ups_sales_playbook_awareness_delay_h", 24));
    if ((string) get_option("ups_sales_channel_email_enabled", "1") === "1") {
        $task_id = wp_insert_post([
            "post_type" => "lead_task",
            "post_status" => "publish",
            "post_author" => $owner_id,
            "post_title" => "Playbook email: " . (string) get_the_title($offer_id),
        ], true);
        if (!is_wp_error($task_id) && (int) $task_id > 0) {
            $tid = (int) $task_id;
            update_post_meta($tid, "_upsellio_task_status", "open");
            update_post_meta($tid, "_upsellio_task_due_at", time() + max(1, $hours) * HOUR_IN_SECONDS);
            update_post_meta($tid, "_upsellio_task_offer_id", $offer_id);
            update_post_meta($tid, "_upsellio_task_note", "Automatyczny playbook {$stage} (email).");
            if (function_exists("upsellio_automation_refresh_task_priority_meta")) {
                upsellio_automation_refresh_task_priority_meta($tid);
            }
        }
    }
    update_post_meta($offer_id, "_ups_offer_playbook_stage", $stage);
}

function upsellio_sales_engine_pause_followups_after_inbound($offer_id, $subject, $body, $from_email)
{
    $offer_id = (int) $offer_id;
    if ($offer_id <= 0) {
        return;
    }
    $queue = get_post_meta($offer_id, "_ups_offer_followup_queue", true);
    if (!is_array($queue) || empty($queue)) {
        return;
    }
    $updated = false;
    foreach ($queue as $idx => $item) {
        if ((string) ($item["status"] ?? "") === "queued") {
            $queue[$idx]["status"] = "paused_inbound_reply";
            $queue[$idx]["paused_at"] = current_time("mysql");
            $updated = true;
        }
    }
    if ($updated) {
        update_post_meta($offer_id, "_ups_offer_followup_queue", $queue);
        if (function_exists("upsellio_offer_add_timeline_event")) {
            upsellio_offer_add_timeline_event($offer_id, "followup_paused", "Pauza follow-up: klient odpowiedzial na maila.");
        }
    }
}
add_action("upsellio_followup_inbound_received", "upsellio_sales_engine_pause_followups_after_inbound", 20, 4);

function upsellio_sales_engine_sync_offer_status_to_crm($offer_id, $new_status, $old_status)
{
    $offer_id = (int) $offer_id;
    $new_status = sanitize_key((string) $new_status);
    if ($offer_id <= 0 || !in_array($new_status, ["won", "lost", "open"], true)) {
        return;
    }
    $client_id = (int) get_post_meta($offer_id, "_ups_offer_client_id", true);
    if ($client_id <= 0) {
        return;
    }
    $client_email = sanitize_email((string) get_post_meta($client_id, "_ups_client_email", true));
    if (!is_email($client_email)) {
        return;
    }
    $lead_ids = get_posts([
        "post_type" => "lead",
        "post_status" => "publish",
        "posts_per_page" => 1,
        "fields" => "ids",
        "meta_query" => [[
            "key" => "_upsellio_lead_email",
            "value" => $client_email,
        ]],
        "orderby" => "date",
        "order" => "DESC",
    ]);
    $lead_id = !empty($lead_ids) ? (int) $lead_ids[0] : 0;
    if ($lead_id <= 0) {
        return;
    }
    if (function_exists("upsellio_crm_get_term_id_by_slug")) {
        $target_slug = $new_status === "won" ? "won" : ($new_status === "lost" ? "lost" : "proposal");
        $term_id = (int) upsellio_crm_get_term_id_by_slug("lead_status", $target_slug);
        if ($term_id > 0) {
            wp_set_object_terms($lead_id, [$term_id], "lead_status", false);
        }
    }
    if (function_exists("upsellio_crm_add_timeline_event")) {
        upsellio_crm_add_timeline_event($lead_id, "offer_status_sync", "Status oferty #" . $offer_id . " -> " . $new_status);
    }
    if (function_exists("upsellio_offer_add_timeline_event")) {
        upsellio_offer_add_timeline_event($offer_id, "status_sync", "Zsynchronizowano status oferty z CRM: " . $new_status);
    }
}
add_action("upsellio_offer_status_changed", "upsellio_sales_engine_sync_offer_status_to_crm", 10, 3);

function upsellio_sales_engine_apply_inbound_actions($offer_id, $classification, $stage)
{
    $offer_id = (int) $offer_id;
    $classification = sanitize_key((string) $classification);
    if ($offer_id <= 0 || $classification === "" || $classification === "other") {
        return;
    }
    if ($classification === "positive") {
        update_post_meta($offer_id, "_ups_offer_stage", "decision");
        update_post_meta($offer_id, "_ups_offer_hot_offer", "1");
        update_post_meta($offer_id, "_ups_offer_priority", "high");
        upsellio_sales_engine_enqueue_playbook_tasks($offer_id, "decision");
        if (function_exists("upsellio_offer_add_timeline_event")) {
            upsellio_offer_add_timeline_event($offer_id, "inbound_action", "Pozytywna odpowiedz: priorytet HIGH + playbook decision.");
        }
        return;
    }
    if ($classification === "price_objection") {
        update_post_meta($offer_id, "_ups_offer_priority", "high");
        upsellio_sales_engine_enqueue_playbook_tasks($offer_id, "consideration");
        if (function_exists("upsellio_offer_add_timeline_event")) {
            upsellio_offer_add_timeline_event($offer_id, "inbound_action", "Obiekcja ceny: playbook consideration + priorytet HIGH.");
        }
        return;
    }
    if ($classification === "timing_objection" || $classification === "no_priority") {
        $snooze_until = gmdate("Y-m-d H:i:s", time() + (30 * DAY_IN_SECONDS));
        update_post_meta($offer_id, "_ups_offer_followup_snooze_until", $snooze_until);
        if ($classification === "no_priority") {
            update_post_meta($offer_id, "_ups_offer_stage", "awareness");
        }
        if (function_exists("upsellio_offer_add_timeline_event")) {
            upsellio_offer_add_timeline_event($offer_id, "inbound_action", "Wstrzymano follow-up do: " . $snooze_until);
        }
    }
}
add_action("upsellio_inbound_classified", "upsellio_sales_engine_apply_inbound_actions", 20, 3);

function upsellio_sales_engine_parse_amount($raw_value)
{
    $raw = trim((string) $raw_value);
    if ($raw === "") {
        return 0.0;
    }
    $normalized = preg_replace("/[^0-9,\\.]/", "", $raw);
    if ($normalized === null || $normalized === "") {
        return 0.0;
    }
    $has_comma = strpos($normalized, ",") !== false;
    $has_dot = strpos($normalized, ".") !== false;
    if ($has_comma && $has_dot) {
        $last_comma = strrpos($normalized, ",");
        $last_dot = strrpos($normalized, ".");
        if ($last_comma !== false && $last_dot !== false) {
            if ($last_comma > $last_dot) {
                $normalized = str_replace(".", "", $normalized);
                $normalized = str_replace(",", ".", $normalized);
            } else {
                $normalized = str_replace(",", "", $normalized);
            }
        }
    } elseif ($has_comma) {
        $normalized = str_replace(",", ".", $normalized);
    }
    return (float) $normalized;
}

function upsellio_sales_engine_handle_contract_signed($contract_id, $new_status, $old_status, $context)
{
    $contract_id = (int) $contract_id;
    if ($contract_id <= 0 || $new_status !== "signed") {
        return;
    }
    $client_id = (int) get_post_meta($contract_id, "_ups_contract_client_id", true);
    if ($client_id <= 0) {
        return;
    }
    $is_recurring = (string) get_post_meta($client_id, "_ups_client_is_recurring", true) === "1";
    update_post_meta($client_id, "_ups_client_subscription_status", "active");
    $billing_start = (string) get_post_meta($client_id, "_ups_client_billing_start", true);
    if ($billing_start === "") {
        update_post_meta($client_id, "_ups_client_billing_start", current_time("Y-m-d"));
    }
    if (!$is_recurring) {
        update_post_meta($client_id, "_ups_client_is_recurring", "1");
    }
    $offer_id = (int) get_post_meta($contract_id, "_ups_contract_offer_id", true);
    if ($offer_id > 0) {
        update_post_meta($offer_id, "_ups_offer_status", "won");
        $price_raw = (string) get_post_meta($offer_id, "_ups_offer_price", true);
        $won_value = upsellio_sales_engine_parse_amount($price_raw);
        if ($won_value > 0) {
            update_post_meta($offer_id, "_ups_offer_won_value", $won_value);
            $monthly_value = (float) get_post_meta($client_id, "_ups_client_monthly_value", true);
            $is_recurring = (string) get_post_meta($client_id, "_ups_client_is_recurring", true) === "1";
            if ($monthly_value <= 0 && $is_recurring && function_exists("upsellio_offer_add_timeline_event")) {
                upsellio_offer_add_timeline_event($offer_id, "mrr_missing", "Umowa podpisana: brak monthly_value klienta, wymagane uzupelnienie reczne.");
            }
        }
        if (function_exists("upsellio_offer_add_timeline_event")) {
            upsellio_offer_add_timeline_event($offer_id, "contract_signed", "Umowa podpisana: automatycznie oznaczono oferte jako won.");
        }
    }
}
add_action("upsellio_contract_status_changed", "upsellio_sales_engine_handle_contract_signed", 10, 4);

function upsellio_sales_engine_classify_inbound($offer_id, $subject, $body, $from_email)
{
    $text = strtolower(trim((string) $subject . " " . (string) $body));
    $classification = "other";
    if (preg_match("/\b(cena|drogo|koszt|budzet)\b/u", $text)) {
        $classification = "price_objection";
    } elseif (preg_match("/\b(czas|termin|pozniej|wr[oó]cimy)\b/u", $text)) {
        $classification = "timing_objection";
    } elseif (preg_match("/\b(nie teraz|brak priorytetu|wstrzym)\b/u", $text)) {
        $classification = "no_priority";
    } elseif (preg_match("/\b(tak|akcept|dzialamy|start|ok)\b/u", $text)) {
        $classification = "positive";
    }
    update_post_meta((int) $offer_id, "_ups_offer_last_inbound_classification", $classification);
    update_post_meta((int) $offer_id, "_ups_offer_last_inbound_class", $classification);
    $stage = (string) get_post_meta((int) $offer_id, "_ups_offer_stage", true);
    if ($stage === "") {
        $stage = "awareness";
    }
    if (function_exists("upsellio_offer_add_timeline_event")) {
        upsellio_offer_add_timeline_event((int) $offer_id, "inbound_classification", "Klasyfikacja odpowiedzi: " . $classification);
    }
    if ($classification === "positive") {
        $stage = "decision";
    }
    do_action("upsellio_inbound_classified", (int) $offer_id, (string) $classification, (string) $stage);
}
add_action("upsellio_followup_inbound_received", "upsellio_sales_engine_classify_inbound", 10, 4);

function upsellio_sales_engine_add_admin_pages()
{
    add_submenu_page(
        "edit.php?post_type=crm_offer",
        "Sales Engine",
        "Sales Engine",
        "manage_options",
        "upsellio-sales-engine",
        "upsellio_sales_engine_render_admin_page"
    );
}
add_action("admin_menu", "upsellio_sales_engine_add_admin_pages");

function upsellio_sales_engine_get_campaign_costs()
{
    $costs = get_option("ups_sales_campaign_costs", []);
    return is_array($costs) ? $costs : [];
}

function upsellio_sales_engine_save_campaign_costs($costs)
{
    if (!is_array($costs)) {
        $costs = [];
    }
    update_option("ups_sales_campaign_costs", $costs, false);
}

function upsellio_sales_engine_parse_campaign_cost_csv_from_text($raw)
{
    $result = [];
    $raw = (string) $raw;
    if ($raw === "") {
        return $result;
    }
    $lines = preg_split("/\R/u", $raw);
    if (!is_array($lines)) {
        return $result;
    }
    $row_index = 0;
    foreach ($lines as $line) {
        $line = trim((string) $line);
        if ($line === "") {
            continue;
        }
        $row_index++;
        $row = str_getcsv($line);
        if ($row_index === 1 && isset($row[0]) && stripos((string) $row[0], "source") !== false) {
            continue;
        }
        $source = isset($row[0]) ? sanitize_text_field((string) $row[0]) : "";
        $campaign = isset($row[1]) ? sanitize_text_field((string) $row[1]) : "";
        $cost_raw = isset($row[2]) ? str_replace(",", ".", (string) $row[2]) : "0";
        $cost = (float) $cost_raw;
        $key = ($source !== "" ? $source : "unknown") . " | " . ($campaign !== "" ? $campaign : "unknown");
        if (!isset($result[$key])) {
            $result[$key] = 0.0;
        }
        $result[$key] += max(0.0, $cost);
    }
    return $result;
}

function upsellio_sales_engine_parse_campaign_cost_csv($tmp_name)
{
    $result = [];
    $handle = fopen($tmp_name, "r");
    if (!$handle) {
        return $result;
    }
    $row_index = 0;
    while (($row = fgetcsv($handle, 0, ",")) !== false) {
        $row_index++;
        if ($row_index === 1 && isset($row[0]) && stripos((string) $row[0], "source") !== false) {
            continue;
        }
        $source = isset($row[0]) ? sanitize_text_field((string) $row[0]) : "";
        $campaign = isset($row[1]) ? sanitize_text_field((string) $row[1]) : "";
        $cost_raw = isset($row[2]) ? str_replace(",", ".", (string) $row[2]) : "0";
        $cost = (float) $cost_raw;
        $key = ($source !== "" ? $source : "unknown") . " | " . ($campaign !== "" ? $campaign : "unknown");
        if (!isset($result[$key])) {
            $result[$key] = 0.0;
        }
        $result[$key] += max(0.0, $cost);
    }
    fclose($handle);
    return $result;
}

function upsellio_sales_engine_render_admin_page()
{
    if (isset($_POST["ups_sales_engine_nonce"]) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST["ups_sales_engine_nonce"])), "ups_sales_engine_save")) {
        $keys = [
            "ups_sales_intent_weight", "ups_sales_fit_weight", "ups_sales_hot_index_threshold",
            "ups_sales_playbook_awareness_delay_h", "ups_sales_playbook_consideration_delay_h", "ups_sales_playbook_decision_delay_h",
            "ups_sales_channel_email_enabled",
            "ups_sales_spf_ok", "ups_sales_dkim_ok", "ups_sales_dmarc_ok", "ups_sales_warmup_notes",
        ];
        foreach ($keys as $key) {
            $value = isset($_POST[$key]) ? wp_unslash($_POST[$key]) : "";
            if (strpos($key, "_enabled") !== false || strpos($key, "_ok") !== false) {
                update_option($key, $value ? "1" : "0");
            } elseif (strpos($key, "_weight") !== false || strpos($key, "_threshold") !== false || strpos($key, "_delay_h") !== false) {
                update_option($key, max(0, (int) $value));
            } else {
                update_option($key, sanitize_textarea_field((string) $value));
            }
        }
        if (!empty($_FILES["ups_sales_cost_csv"]["tmp_name"]) && is_uploaded_file($_FILES["ups_sales_cost_csv"]["tmp_name"])) {
            $parsed_costs = upsellio_sales_engine_parse_campaign_cost_csv($_FILES["ups_sales_cost_csv"]["tmp_name"]);
            if (!empty($parsed_costs)) {
                $existing_costs = upsellio_sales_engine_get_campaign_costs();
                foreach ($parsed_costs as $cost_key => $cost_value) {
                    $existing_costs[$cost_key] = (float) $cost_value;
                }
                upsellio_sales_engine_save_campaign_costs($existing_costs);
            }
        }
        echo '<div class="notice notice-success"><p>Zapisano ustawienia Sales Engine.</p></div>';
    }

    $offers = get_posts(["post_type" => "crm_offer", "post_status" => ["publish", "draft", "pending", "private"], "posts_per_page" => 300]);
    $attribution = [];
    $campaign_costs = upsellio_sales_engine_get_campaign_costs();
    $deliverability_failures = 0;
    $clients = get_posts(["post_type" => "crm_client", "post_status" => ["publish", "draft", "pending", "private"], "posts_per_page" => 500]);
    $active_recurring_clients = 0;
    $active_mrr = 0.0;
    $cancelled_this_month = 0;
    $month_prefix = gmdate("Y-m");
    $clients_start_of_month_active = 0;
    $start_of_month = gmdate("Y-m-01");
    foreach ($clients as $client) {
        $client_id = (int) $client->ID;
        $is_recurring = (string) get_post_meta($client_id, "_ups_client_is_recurring", true) === "1";
        if (!$is_recurring) {
            continue;
        }
        $status = (string) get_post_meta($client_id, "_ups_client_subscription_status", true);
        if ($status === "") {
            $status = "active";
        }
        $monthly_value = (float) get_post_meta($client_id, "_ups_client_monthly_value", true);
        $cancellation_date = (string) get_post_meta($client_id, "_ups_client_cancellation_date", true);
        $billing_start = (string) get_post_meta($client_id, "_ups_client_billing_start", true);
        if ($status === "active") {
            $active_recurring_clients++;
            $active_mrr += $monthly_value;
        }
        if ($cancellation_date !== "" && strpos($cancellation_date, $month_prefix) === 0) {
            $cancelled_this_month++;
        }
        $was_active_at_month_start = true;
        if ($billing_start !== "" && $billing_start > $start_of_month) {
            $was_active_at_month_start = false;
        }
        if ($status === "cancelled" && $cancellation_date !== "" && $cancellation_date < $start_of_month) {
            $was_active_at_month_start = false;
        }
        if ($was_active_at_month_start) {
            $clients_start_of_month_active++;
        }
    }
    $churn_rate = $clients_start_of_month_active > 0
        ? (($cancelled_this_month / $clients_start_of_month_active) * 100.0)
        : 0.0;
    foreach ($offers as $offer) {
        $offer_id = (int) $offer->ID;
        $source = (string) get_post_meta($offer_id, "_ups_offer_utm_source", true);
        $campaign = (string) get_post_meta($offer_id, "_ups_offer_utm_campaign", true);
        $won_value = (float) get_post_meta($offer_id, "_ups_offer_won_value", true);
        $status = (string) get_post_meta($offer_id, "_ups_offer_status", true);
        $key = ($source !== "" ? $source : "unknown") . " | " . ($campaign !== "" ? $campaign : "unknown");
        if (!isset($attribution[$key])) {
            $attribution[$key] = ["offers" => 0, "won" => 0, "revenue" => 0.0, "cost" => 0.0];
        }
        $attribution[$key]["offers"]++;
        if ($status === "won") {
            $attribution[$key]["won"]++;
            $attribution[$key]["revenue"] += $won_value;
        }
        if ((string) get_post_meta($offer_id, "_ups_offer_email_last_status", true) === "failed") {
            $deliverability_failures++;
        }
    }
    foreach ($campaign_costs as $channel_key => $channel_cost) {
        if (!isset($attribution[$channel_key])) {
            $attribution[$channel_key] = ["offers" => 0, "won" => 0, "revenue" => 0.0, "cost" => 0.0];
        }
        $attribution[$channel_key]["cost"] = (float) $channel_cost;
    }
    ?>
    <div class="wrap">
      <h1>Sales Engine</h1>
      <form method="post" enctype="multipart/form-data">
        <?php wp_nonce_field("ups_sales_engine_save", "ups_sales_engine_nonce"); ?>
        <h2>Scoring 2-warstwowy</h2>
        <table class="form-table">
          <tr><th>Intent weight</th><td><input type="number" class="small-text" name="ups_sales_intent_weight" value="<?php echo esc_attr((string) get_option("ups_sales_intent_weight", 60)); ?>" /></td></tr>
          <tr><th>Fit weight</th><td><input type="number" class="small-text" name="ups_sales_fit_weight" value="<?php echo esc_attr((string) get_option("ups_sales_fit_weight", 40)); ?>" /></td></tr>
          <tr><th>Hot index threshold</th><td><input type="number" class="small-text" name="ups_sales_hot_index_threshold" value="<?php echo esc_attr((string) get_option("ups_sales_hot_index_threshold", 72)); ?>" /></td></tr>
        </table>
        <h2>Playbooki 24h / 48h / 7 dni</h2>
        <table class="form-table">
          <tr><th>Awareness delay (h)</th><td><input type="number" class="small-text" name="ups_sales_playbook_awareness_delay_h" value="<?php echo esc_attr((string) get_option("ups_sales_playbook_awareness_delay_h", 24)); ?>" /></td></tr>
          <tr><th>Consideration delay (h)</th><td><input type="number" class="small-text" name="ups_sales_playbook_consideration_delay_h" value="<?php echo esc_attr((string) get_option("ups_sales_playbook_consideration_delay_h", 48)); ?>" /></td></tr>
          <tr><th>Decision delay (h)</th><td><input type="number" class="small-text" name="ups_sales_playbook_decision_delay_h" value="<?php echo esc_attr((string) get_option("ups_sales_playbook_decision_delay_h", 7)); ?>" /></td></tr>
        </table>
        <h2>Kanały domykania</h2>
        <p><label><input type="checkbox" name="ups_sales_channel_email_enabled" value="1" <?php checked((string) get_option("ups_sales_channel_email_enabled", "1"), "1"); ?> /> Email</label></p>
        <h2>Guardrails deliverability</h2>
        <p><label><input type="checkbox" name="ups_sales_spf_ok" value="1" <?php checked((string) get_option("ups_sales_spf_ok", "0"), "1"); ?> /> SPF skonfigurowany</label></p>
        <p><label><input type="checkbox" name="ups_sales_dkim_ok" value="1" <?php checked((string) get_option("ups_sales_dkim_ok", "0"), "1"); ?> /> DKIM skonfigurowany</label></p>
        <p><label><input type="checkbox" name="ups_sales_dmarc_ok" value="1" <?php checked((string) get_option("ups_sales_dmarc_ok", "0"), "1"); ?> /> DMARC skonfigurowany</label></p>
        <p><label>Warm-up / monitoring notes</label><br /><textarea class="large-text" rows="3" name="ups_sales_warmup_notes"><?php echo esc_textarea((string) get_option("ups_sales_warmup_notes", "")); ?></textarea></p>
        <h2>Koszty kampanii (CSV)</h2>
        <p>Format: <code>source,campaign,cost</code> (np. <code>google,brand-search,2500.00</code>)</p>
        <p><input type="file" name="ups_sales_cost_csv" accept=".csv,text/csv" /></p>
        <p><button class="button button-primary" type="submit">Zapisz Sales Engine</button></p>
      </form>

      <h2>Revenue Attribution</h2>
      <table class="widefat striped">
        <thead><tr><th>Źródło | Kampania</th><th>Oferty</th><th>Wygrane</th><th>Revenue</th><th>Cost</th><th>Profit</th><th>ROI</th></tr></thead>
        <tbody>
        <?php foreach ($attribution as $channel => $row) : ?>
          <?php
          $revenue = (float) ($row["revenue"] ?? 0.0);
          $cost = (float) ($row["cost"] ?? 0.0);
          $profit = $revenue - $cost;
          $roi = $cost > 0 ? (($profit / $cost) * 100.0) : 0.0;
          ?>
          <tr>
            <td><?php echo esc_html((string) $channel); ?></td>
            <td><?php echo esc_html((string) ((int) $row["offers"])); ?></td>
            <td><?php echo esc_html((string) ((int) $row["won"])); ?></td>
            <td><?php echo esc_html(number_format($revenue, 2, ",", " ")); ?> PLN</td>
            <td><?php echo esc_html(number_format($cost, 2, ",", " ")); ?> PLN</td>
            <td><?php echo esc_html(number_format($profit, 2, ",", " ")); ?> PLN</td>
            <td><?php echo esc_html(number_format($roi, 2, ",", " ")); ?>%</td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <p><strong>Deliverability monitor:</strong> Nieudane wysyłki ofert: <?php echo esc_html((string) $deliverability_failures); ?></p>
      <h2>Subskrypcje / odnowienia miesieczne</h2>
      <table class="widefat striped">
        <thead><tr><th>Aktywni klienci recurring</th><th>Aktywne MRR</th><th>Rezygnacje w tym miesiacu</th><th>Churn (miesieczny)</th></tr></thead>
        <tbody>
          <tr>
            <td><?php echo esc_html((string) $active_recurring_clients); ?></td>
            <td><?php echo esc_html(number_format($active_mrr, 2, ",", " ")); ?> PLN</td>
            <td><?php echo esc_html((string) $cancelled_this_month); ?></td>
            <td><?php echo esc_html(number_format($churn_rate, 2, ",", " ")); ?>%</td>
          </tr>
        </tbody>
      </table>
    </div>
    <?php
}

function upsellio_sales_engine_handle_client_cancellation($client_id)
{
    $client_id = (int) $client_id;
    if ($client_id <= 0) {
        return;
    }
    $offers = get_posts([
        "post_type" => "crm_offer",
        "post_status" => ["publish", "draft", "pending", "private"],
        "posts_per_page" => 200,
        "fields" => "ids",
        "meta_query" => [[
            "key" => "_ups_offer_client_id",
            "value" => (string) $client_id,
        ]],
    ]);
    foreach ($offers as $offer_id) {
        $offer_id = (int) $offer_id;
        update_post_meta($offer_id, "_ups_offer_status", "lost");
        $queue = get_post_meta($offer_id, "_ups_offer_followup_queue", true);
        if (is_array($queue) && !empty($queue)) {
            $updated = false;
            foreach ($queue as $idx => $item) {
                if ((string) ($item["status"] ?? "") === "queued") {
                    $queue[$idx]["status"] = "cancelled_client_churn";
                    $queue[$idx]["cancelled_at"] = current_time("mysql");
                    $updated = true;
                }
            }
            if ($updated) {
                update_post_meta($offer_id, "_ups_offer_followup_queue", $queue);
            }
        }
        if (function_exists("upsellio_offer_add_timeline_event")) {
            upsellio_offer_add_timeline_event($offer_id, "client_churn", "Klient zrezygnowal z uslugi odnawialnej.");
        }
    }
}
add_action("upsellio_client_subscription_cancelled", "upsellio_sales_engine_handle_client_cancellation", 10, 1);
