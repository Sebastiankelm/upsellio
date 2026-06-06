<?php
/**
 * Pełna synchronizacja historii dla wszystkich zmapowanych zasobów profili audytu.
 * GSC ~480 dni, GA4 ~365 dni, Ads ~90 dni (gdy skonfigurowany token).
 *
 * wp eval-file wp-content/themes/upsellio/tools/audit-sync-full-history.php
 */

if (!defined("ABSPATH")) {
    exit(1);
}

@set_time_limit(0);

$profile_ids = function_exists("ups_audit_collect_profile_client_ids")
    ? ups_audit_collect_profile_client_ids()
    : [965, 966, 968];

echo "=== Audit: pełna historia (profile + zmapowane zasoby) ===\n";
echo "Profile client_id: " . implode(", ", array_map("intval", $profile_ids)) . "\n";

if (!function_exists("ups_audit_api_fetch_days")) {
    echo "Brak ups_audit_api_fetch_days — wgraj najnowszy motyw.\n";
    exit(1);
}

echo "Okna API: GSC=" . ups_audit_api_fetch_days("gsc", true)
    . "d, GA4=" . ups_audit_api_fetch_days("ga4", true)
    . "d, Ads=" . ups_audit_api_fetch_days("ads", true) . "d\n\n";

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
    "orderby" => "ID",
    "order" => "ASC",
]);

if (empty($resources)) {
    echo "Brak zmapowanych zasobów.\n";
    exit(0);
}

$started = microtime(true);
$ok = 0;
$fail = 0;

foreach ($resources as $r) {
    if (!($r instanceof WP_Post)) {
        continue;
    }
    $rid = (int) $r->ID;
    $type = (string) get_post_meta($rid, "_ups_resource_type", true);
    $cid = (int) get_post_meta($rid, "_ups_resource_client_id", true);
    $ext = (string) get_post_meta($rid, "_ups_resource_external_id", true);
    $client_title = $cid > 0 ? (string) get_the_title($cid) : "?";

    echo sprintf(
        "#%d [%s] client=%d (%s) %s ... ",
        $rid,
        $type,
        $cid,
        $client_title,
        $ext !== "" ? substr($ext, 0, 48) : ""
    );

    $t0 = microtime(true);
    try {
        if (function_exists("ups_audit_sync_resource_action")) {
            ups_audit_sync_resource_action($rid, 30, true);
        }
        $cache = get_post_meta($rid, "_ups_resource_data_cache", true);
        $err = is_array($cache) ? trim((string) ($cache["error"] ?? "")) : "brak cache";
        $ts_count = is_array($cache) && is_array($cache["timeseries"] ?? null)
            ? count($cache["timeseries"])
            : 0;
        $elapsed = round(microtime(true) - $t0, 1);

        if ($err !== "") {
            $fail++;
            echo "FAIL ({$elapsed}s): {$err}\n";
        } else {
            $ok++;
            $summary = function_exists("ups_audit_cache_summary")
                ? ups_audit_cache_summary($cache)
                : [];
            $hint = "";
            if ($type === "ga4") {
                $hint = "sessions=" . (int) ($summary["sessions"] ?? 0);
            } elseif ($type === "gsc") {
                $hint = "clicks=" . (int) ($summary["clicks"] ?? 0);
            } elseif ($type === "ads") {
                $hint = "cost=" . round((float) ($summary["cost"] ?? 0), 2);
            }
            echo "OK ({$elapsed}s, ts_days={$ts_count}" . ($hint !== "" ? ", {$hint}" : "") . ")\n";
        }
    } catch (Throwable $e) {
        $fail++;
        echo "EXCEPTION: " . $e->getMessage() . "\n";
    }

    usleep(500000);
}

$total = $ok + $fail;
$dur = round(microtime(true) - $started, 1);
echo "\n=== Podsumowanie: ok={$ok}, fail={$fail}, total={$total}, czas={$dur}s ===\n";

foreach ($profile_ids as $cid) {
    $cid = (int) $cid;
    if ($cid <= 0 || !function_exists("ups_audit_aggregate_client_data")) {
        continue;
    }
    $data = ups_audit_aggregate_client_data($cid, 30, 0, false);
    $ts_ga4 = count((array) (($data["timeseries"]["ga4_sessions"] ?? [])));
    $ts_gsc = count((array) (($data["timeseries"]["gsc_clicks"] ?? [])));
    echo get_the_title($cid) . " (#{$cid}): GA4 ts={$ts_ga4}, GSC ts={$ts_gsc}, "
        . "sessions=" . (int) ($data["ga4_sessions"] ?? 0)
        . ", gsc_clicks=" . (int) ($data["gsc_clicks"] ?? 0) . "\n";
}

exit($fail > 0 ? 1 : 0);
