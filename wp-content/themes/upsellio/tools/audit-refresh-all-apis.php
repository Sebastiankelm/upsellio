<?php
/**
 * Odśwież cache GA4/GSC/Ads, import + mapowanie GA4 do profili, sync.
 * wp eval-file wp-content/themes/upsellio/tools/audit-refresh-all-apis.php
 */

if (!defined("ABSPATH")) {
    exit(1);
}

$profiles = [
    "wtapes" => [
        "client_id" => 965,
        "hosts" => ["wtapes.pl"],
        "gsc" => [["google_account_id" => 963, "external_id" => "https://wtapes.pl/"]],
        "ads" => [["google_account_id" => 963, "external_id" => "5195787252"]],
    ],
    "lapidaria" => [
        "client_id" => 966,
        "hosts" => ["lapidaria.pl"],
        "gsc" => [["google_account_id" => 963, "external_id" => "https://lapidaria.pl/"]],
    ],
    "upsellio" => [
        "client_id" => 968,
        "hosts" => ["upsellio.pl"],
        "gsc" => [["google_account_id" => 962, "external_id" => "https://upsellio.pl/"]],
    ],
];

function audit_refresh_import_ga4_for_host(int $account_id, string $host): int
{
    $host = strtolower(trim($host));
    $cache = get_post_meta($account_id, "_ups_gacc_resources_cache", true);
    if (!is_array($cache)) {
        return 0;
    }
    foreach ((array) ($cache["ga4"] ?? []) as $acc_node) {
        if (!is_array($acc_node)) {
            continue;
        }
        foreach ((array) ($acc_node["properties"] ?? []) as $prop) {
            if (!is_array($prop)) {
                continue;
            }
            $ext = (string) ($prop["id"] ?? "");
            $name = strtolower((string) ($prop["display_name"] ?? ""));
            $stream = strtolower((string) ($prop["default_uri"] ?? ""));
            $hay = $name . " " . $stream . " " . $ext;
            if ($host !== "" && strpos($hay, $host) === false) {
                continue;
            }
            $display = (string) ($prop["display_name"] ?? $ext);
            $rid = function_exists("ups_audit_find_imported_resource_id")
                ? ups_audit_find_imported_resource_id($account_id, "ga4", $ext)
                : 0;
            if ($rid <= 0) {
                    $rid = (int) wp_insert_post([
                        "post_type" => "crm_audit_resource",
                        "post_title" => $display,
                        "post_status" => "publish",
                    ]);
                    if ($rid > 0) {
                        update_post_meta($rid, "_ups_resource_type", "ga4");
                        update_post_meta($rid, "_ups_resource_external_id", $ext);
                        update_post_meta($rid, "_ups_resource_display_name", $display);
                        update_post_meta($rid, "_ups_resource_parent_account_id", (string) ($acc_node["account_id"] ?? ""));
                        update_post_meta($rid, "_ups_resource_google_account_id", $account_id);
                        update_post_meta($rid, "_ups_resource_client_id", 0);
                        update_post_meta($rid, "_ups_resource_imported_at", current_time("mysql"));
                        echo "  imported GA4 {$display} ({$ext}) acc#{$account_id}\n";
                    }
            } elseif ($rid > 0) {
                echo "  GA4 already #{$rid} {$display}\n";
            }
            return $rid > 0 ? $rid : 0;
        }
    }

    return 0;
}

echo "=== Odświeżanie kont Google ===\n";
$accounts = get_posts([
    "post_type" => "crm_google_account",
    "posts_per_page" => -1,
    "post_status" => ["publish", "draft"],
    "fields" => "ids",
]);
foreach ($accounts as $aid) {
    $aid = (int) $aid;
    echo "Konto #{$aid}...\n";
    $ga4 = function_exists("ups_audit_fetch_ga4_resources") ? ups_audit_fetch_ga4_resources($aid) : [];
    $gsc = function_exists("ups_audit_fetch_gsc_resources") ? ups_audit_fetch_gsc_resources($aid) : [];
    $ads = function_exists("ups_audit_fetch_ads_resources") ? ups_audit_fetch_ads_resources($aid) : [];
    $ga4_err = get_post_meta($aid, "_ups_gacc_ga4_fetch_error", true);
    $ads_err = get_post_meta($aid, "_ups_gacc_ads_fetch_error", true);
    $ga4_props = 0;
    foreach ((array) $ga4 as $node) {
        if (is_array($node)) {
            $ga4_props += count((array) ($node["properties"] ?? []));
        }
    }
    echo "  GA4: {$ga4_props} props" . ($ga4_err ? " ERR:{$ga4_err}" : "") . "\n";
    echo "  GSC: " . (is_array($gsc) ? count($gsc) : 0) . "\n";
    echo "  Ads: " . (is_array($ads) ? count($ads) : 0) . ($ads_err ? " ERR:{$ads_err}" : "") . "\n";
    $cache = ["ga4" => $ga4, "gsc" => $gsc, "ads" => $ads];
    if (is_wp_error($cache["ga4"])) {
        update_post_meta($aid, "_ups_gacc_ga4_fetch_error", $cache["ga4"]->get_error_message());
        $cache["ga4"] = [];
    } else {
        delete_post_meta($aid, "_ups_gacc_ga4_fetch_error");
    }
    if (is_wp_error($cache["gsc"])) {
        $cache["gsc"] = [];
    }
    if (is_wp_error($cache["ads"])) {
        update_post_meta($aid, "_ups_gacc_ads_fetch_error", $cache["ads"]->get_error_message());
        $cache["ads"] = [];
    } else {
        delete_post_meta($aid, "_ups_gacc_ads_fetch_error");
    }
    update_post_meta($aid, "_ups_gacc_resources_cache", $cache);
    update_post_meta($aid, "_ups_gacc_last_sync_at", current_time("mysql"));
}

echo "\n=== Import + mapowanie GA4/Ads do profili ===\n";
foreach ($profiles as $slug => $cfg) {
    $client_id = (int) ($cfg["client_id"] ?? 0);
    echo "{$slug} (client #{$client_id}):\n";
    foreach ((array) ($cfg["hosts"] ?? []) as $host) {
        foreach ($accounts as $aid) {
            $rid = audit_refresh_import_ga4_for_host((int) $aid, $host);
            if ($rid > 0 && $client_id > 0) {
                update_post_meta($rid, "_ups_resource_client_id", $client_id);
                echo "  mapped GA4 #{$rid} -> #{$client_id}\n";
            }
        }
    }
    foreach ((array) ($cfg["ads"] ?? []) as $row) {
        $gacc = (int) ($row["google_account_id"] ?? 0);
        $ext = preg_replace("/\D+/", "", (string) ($row["external_id"] ?? ""));
        if ($gacc <= 0 || $ext === "") {
            continue;
        }
        $rid = function_exists("ups_audit_find_imported_resource_id")
            ? ups_audit_find_imported_resource_id($gacc, "ads", $ext)
            : 0;
        if ($rid > 0 && $client_id > 0) {
            update_post_meta($rid, "_ups_resource_client_id", $client_id);
            echo "  mapped Ads #{$rid} ({$ext}) -> #{$client_id}\n";
        }
    }
}

echo "\n=== Sync wszystkich zmapowanych (30d) ===\n";
if (function_exists("ups_audit_sync_all_mapped_resources")) {
    $r = ups_audit_sync_all_mapped_resources(30);
    echo "ok=" . (int) ($r["ok"] ?? 0) . " fail=" . (int) ($r["fail"] ?? 0) . "\n";
}

echo "\n=== KPI ===\n";
foreach ($profiles as $slug => $cfg) {
    $cid = (int) ($cfg["client_id"] ?? 0);
    if ($cid <= 0) {
        continue;
    }
    $data = function_exists("ups_audit_aggregate_client_data")
        ? ups_audit_aggregate_client_data($cid, 30, 0, false)
        : [];
    $setup = function_exists("ups_audit_client_setup_status") ? ups_audit_client_setup_status($cid) : [];
    echo "{$slug}: GA4=" . (int) ($setup["ga4"] ?? 0)
        . " GSC=" . (int) ($setup["gsc"] ?? 0)
        . " sesje=" . (int) ($data["ga4_sessions"] ?? 0)
        . " gsc_klik=" . (int) ($data["gsc_clicks"] ?? 0)
        . " ads=" . (int) ($setup["ads"] ?? 0)
        . " koszt=" . (float) ($data["ads_cost"] ?? 0) . "\n";
}

echo "\nDone.\n";
