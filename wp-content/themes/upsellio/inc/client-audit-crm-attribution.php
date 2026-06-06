<?php

if (!defined("ABSPATH")) {
    exit;
}

/**
 * Mapuje UTM na bucket kanału do raportu przychodu CRM.
 */
function ups_audit_map_utm_channel_bucket(string $source, string $medium, string $campaign = ""): string
{
    $source = strtolower(trim($source));
    $medium = strtolower(trim($medium));
    $campaign = strtolower(trim($campaign));

    if ($source === "" || $source === "(not set)") {
        return "Bez atrybucji";
    }
    if (in_array($medium, ["cpc", "ppc", "paid", "ads"], true)) {
        if ($source === "google" || $source === "googleads") {
            if (
                strpos($campaign, "pmax") !== false
                || strpos($campaign, "performance") !== false
                || strpos($campaign, "performance max") !== false
            ) {
                return "Google PMax";
            }

            return "Google Search";
        }
        if ($source === "facebook" || $source === "fb" || $source === "instagram" || $source === "meta") {
            return "Meta Ads";
        }

        return ucfirst($source) . " Paid";
    }
    if ($medium === "organic" || ($source === "google" && $medium === "organic")) {
        return "Google Organic";
    }
    if ($medium === "referral") {
        return "Referral";
    }
    if ($source === "(direct)" || $medium === "(none)" || $medium === "direct") {
        return "Direct";
    }

    return trim($source . " / " . $medium);
}

/**
 * @return array<string, mixed>
 */
function ups_audit_collect_client_crm_funnel(int $client_id, int $days = 30): array
{
    $client_id = (int) $client_id;
    $days = max(7, min(90, $days));
    if ($client_id <= 0) {
        return [
            "leads" => 0,
            "offers" => 0,
            "offers_sent" => 0,
            "won" => 0,
            "revenue" => 0.0,
            "has_data" => false,
        ];
    }

    $after = gmdate("Y-m-d H:i:s", strtotime("-" . $days . " days"));
    $leads = 0;
    $offers = 0;
    $offers_sent = 0;
    $won = 0;
    $revenue = 0.0;

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
            $lid = (int) $lead->ID;
            if (function_exists("ups_audit_lead_matches_client") && !ups_audit_lead_matches_client($client_id, $lid)) {
                continue;
            }
            $leads++;
            $close_val = (float) get_post_meta($lid, "_upsellio_lead_close_value", true);
            $status_slugs = wp_get_object_terms($lid, "lead_status", ["fields" => "slugs"]);
            if (is_array($status_slugs) && in_array("won", $status_slugs, true)) {
                $won++;
                $revenue += $close_val;
            } elseif ($close_val > 0) {
                $won++;
                $revenue += $close_val;
            }
        }
    }

    $offer_posts = get_posts([
        "post_type" => "crm_offer",
        "posts_per_page" => 500,
        "post_status" => ["publish", "draft", "pending", "private"],
        "date_query" => [["after" => $after]],
        "meta_query" => [[
            "key" => "_ups_offer_client_id",
            "value" => $client_id,
            "compare" => "=",
        ]],
    ]);
    foreach ($offer_posts as $offer) {
        if (!($offer instanceof WP_Post)) {
            continue;
        }
        $oid = (int) $offer->ID;
        $offers++;
        $status = (string) get_post_meta($oid, "_ups_offer_status", true);
        if (in_array($status, ["sent", "won", "open"], true)) {
            $offers_sent++;
        }
        if ($status === "won") {
            $won++;
            $revenue += (float) get_post_meta($oid, "_ups_offer_won_value", true);
        }
    }

    return [
        "leads" => $leads,
        "offers" => $offers,
        "offers_sent" => $offers_sent,
        "won" => $won,
        "revenue" => round($revenue, 2),
        "has_data" => $leads > 0 || $offers > 0,
    ];
}

/**
 * Koszt kampanii Ads wg typu (Search / PMax / inne).
 *
 * @param array<string, mixed> $agg
 *
 * @return array<string, float>
 */
function ups_audit_ads_cost_by_bucket(array $agg): array
{
    $buckets = [
        "Google Search" => 0.0,
        "Google PMax" => 0.0,
        "Meta Ads" => 0.0,
    ];

    foreach ((array) ($agg["campaigns"] ?? []) as $camp) {
        if (!is_array($camp)) {
            continue;
        }
        $type = strtoupper((string) ($camp["type"] ?? ""));
        $name = strtolower((string) ($camp["name"] ?? ""));
        $cost = (float) ($camp["cost"] ?? $camp["cost_pln"] ?? 0);
        $is_pmax = strpos($type, "PERFORMANCE") !== false
            || strpos($name, "pmax") !== false
            || strpos($name, "performance max") !== false;
        $is_search = strpos($type, "SEARCH") !== false || strpos($name, "search") !== false;

        if ($is_pmax) {
            $buckets["Google PMax"] += $cost;
        } elseif ($is_search) {
            $buckets["Google Search"] += $cost;
        }
    }

    $buckets["Meta Ads"] = (float) ($agg["meta_cost"] ?? 0);

    return $buckets;
}

/**
 * CRM Revenue Attribution — koszt → lead → oferta → wygrana → przychód.
 *
 * @param array<string, mixed> $agg
 *
 * @return array<string, mixed>
 */
function ups_audit_intel_crm_revenue_attribution(int $client_id, array $agg, int $days = 30): array
{
    $client_id = (int) $client_id;
    $days = max(7, min(90, $days));
    $after = gmdate("Y-m-d H:i:s", strtotime("-" . $days . " days"));

    $rows = [];
    $init_row = static function (string $label): array {
        return [
            "channel" => $label,
            "cost" => 0.0,
            "leads" => 0,
            "offers" => 0,
            "won" => 0,
            "revenue" => 0.0,
            "roas" => 0.0,
            "cpa_lead" => null,
        ];
    };

    foreach (ups_audit_ads_cost_by_bucket($agg) as $bucket => $cost) {
        if ($cost > 0) {
            $rows[$bucket] = $init_row($bucket);
            $rows[$bucket]["cost"] = round($cost, 2);
        }
    }

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
            $lid = (int) $lead->ID;
            if (function_exists("ups_audit_lead_matches_client") && !ups_audit_lead_matches_client($client_id, $lid)) {
                continue;
            }
            $bucket = ups_audit_map_utm_channel_bucket(
                (string) get_post_meta($lid, "_upsellio_lead_utm_source", true),
                (string) get_post_meta($lid, "_upsellio_lead_utm_medium", true),
                (string) get_post_meta($lid, "_upsellio_lead_utm_campaign", true)
            );
            if (!isset($rows[$bucket])) {
                $rows[$bucket] = $init_row($bucket);
            }
            $rows[$bucket]["leads"]++;
            $close_val = (float) get_post_meta($lid, "_upsellio_lead_close_value", true);
            $status_slugs = wp_get_object_terms($lid, "lead_status", ["fields" => "slugs"]);
            $is_won = is_array($status_slugs) && in_array("won", $status_slugs, true);
            if ($is_won || $close_val > 0) {
                $rows[$bucket]["won"]++;
                $rows[$bucket]["revenue"] += $close_val;
            }
        }
    }

    $offer_posts = get_posts([
        "post_type" => "crm_offer",
        "posts_per_page" => 500,
        "post_status" => ["publish", "draft", "pending", "private"],
        "date_query" => [["after" => $after]],
        "meta_query" => [[
            "key" => "_ups_offer_client_id",
            "value" => $client_id,
            "compare" => "=",
        ]],
    ]);
    foreach ($offer_posts as $offer) {
        if (!($offer instanceof WP_Post)) {
            continue;
        }
        $oid = (int) $offer->ID;
        $src = (string) get_post_meta($oid, "_ups_offer_utm_source", true);
        $med = (string) get_post_meta($oid, "_ups_offer_utm_medium", true);
        $camp = (string) get_post_meta($oid, "_ups_offer_utm_campaign", true);
        if (trim($src) === "") {
            $lead_id = (int) get_post_meta($oid, "_ups_offer_lead_id", true);
            if ($lead_id > 0) {
                $src = (string) get_post_meta($lead_id, "_upsellio_lead_utm_source", true);
                $med = (string) get_post_meta($lead_id, "_upsellio_lead_utm_medium", true);
                $camp = (string) get_post_meta($lead_id, "_upsellio_lead_utm_campaign", true);
            }
        }
        $bucket = ups_audit_map_utm_channel_bucket($src, $med, $camp);
        if (!isset($rows[$bucket])) {
            $rows[$bucket] = $init_row($bucket);
        }
        $rows[$bucket]["offers"]++;
        $status = (string) get_post_meta($oid, "_ups_offer_status", true);
        if ($status === "won") {
            $won_val = (float) get_post_meta($oid, "_ups_offer_won_value", true);
            if ($won_val > 0) {
                $rows[$bucket]["revenue"] += $won_val;
            }
        }
    }

    $out = [];
    foreach ($rows as $row) {
        $cost = (float) ($row["cost"] ?? 0);
        $leads = (int) ($row["leads"] ?? 0);
        $rev = round((float) ($row["revenue"] ?? 0), 2);
        $row["revenue"] = $rev;
        $row["roas"] = $cost > 0 ? round($rev / $cost, 2) : 0.0;
        $row["cpa_lead"] = $leads > 0 && $cost > 0 ? round($cost / $leads, 0) : null;
        $out[] = $row;
    }

    usort($out, static function ($a, $b) {
        return ((float) ($b["revenue"] ?? 0)) <=> ((float) ($a["revenue"] ?? 0))
            ?: ((int) ($b["leads"] ?? 0)) <=> ((int) ($a["leads"] ?? 0))
            ?: ((float) ($b["cost"] ?? 0)) <=> ((float) ($a["cost"] ?? 0));
    });

    $funnel = ups_audit_collect_client_crm_funnel($client_id, $days);
    $top = $out[0] ?? null;
    $summary = "";
    if (is_array($top) && ((int) ($top["leads"] ?? 0) > 0 || (float) ($top["cost"] ?? 0) > 0)) {
        $summary = sprintf(
            "%s: koszt %s zł · %d leadów · %d ofert · %s zł przychodu CRM.",
            (string) ($top["channel"] ?? ""),
            number_format((float) ($top["cost"] ?? 0), 0, ",", " "),
            (int) ($top["leads"] ?? 0),
            (int) ($top["offers"] ?? 0),
            number_format((float) ($top["revenue"] ?? 0), 0, ",", " ")
        );
    }

    return [
        "has_data" => $out !== [] || !empty($funnel["has_data"]),
        "rows" => array_slice($out, 0, 10),
        "funnel_totals" => $funnel,
        "summary" => $summary,
        "window_days" => $days,
        "note" => __("Przychód z CRM (wygrane oferty i leady). Koszt z Google/Meta Ads w okresie raportu.", "upsellio"),
    ];
}
