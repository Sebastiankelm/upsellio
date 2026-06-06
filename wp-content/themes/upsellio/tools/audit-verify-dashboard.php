<?php

if (!defined("ABSPATH")) {
    exit(1);
}

$titles = ["wtapes", "lapidaria", "upsellio"];
foreach ($titles as $title) {
    $cid = 0;
    foreach (get_posts(["post_type" => "crm_client", "posts_per_page" => -1, "post_status" => ["publish", "draft"]]) as $c) {
        if ($c instanceof WP_Post && (string) $c->post_title === $title) {
            $cid = (int) $c->ID;
            break;
        }
    }
    if ($cid <= 0) {
        echo "{$title}: MISSING\n";
        continue;
    }
    $resources = function_exists("ups_audit_get_client_resources") ? ups_audit_get_client_resources($cid) : [];
    $data = function_exists("ups_audit_aggregate_client_data")
        ? ups_audit_aggregate_client_data($cid, 30, 0, false)
        : [];
    $url = function_exists("upsellio_crm_url")
        ? upsellio_crm_url("ca-dashboard", ["cid" => $cid])
        : home_url("/crm-app/?view=ca-dashboard&cid=" . $cid);
    echo "{$title} (#{$cid}) resources=" . count($resources)
        . " gsc_clicks=" . (int) ($data["gsc_clicks"] ?? 0)
        . " health=" . (int) ($data["health_score"] ?? 0) . "\n";
    echo "  dashboard: {$url}\n";
    foreach ($resources as $r) {
        if (!($r instanceof WP_Post)) {
            continue;
        }
        $cache = get_post_meta((int) $r->ID, "_ups_resource_data_cache", true);
        $err = is_array($cache) ? (string) ($cache["error"] ?? "") : "";
        $sum = is_array($cache) && is_array($cache["summary"] ?? null) ? $cache["summary"] : [];
        echo "  - #{$r->ID} " . get_post_meta((int) $r->ID, "_ups_resource_external_id", true)
            . " clicks=" . (int) ($sum["clicks"] ?? 0)
            . ($err !== "" ? " ERR:{$err}" : "") . "\n";
    }
}
