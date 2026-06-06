<?php
/**
 * wp eval-file wp-content/themes/upsellio/tools/audit-debug-gsc-sitemap.php
 */

if (!defined("ABSPATH")) {
    exit(1);
}

$client_id = isset($argv[1]) ? (int) $argv[1] : 965;
if ($client_id <= 0) {
    $client_id = 965;
}

foreach (ups_audit_get_client_resources($client_id) as $res) {
    if (!($res instanceof WP_Post)) {
        continue;
    }
    if ((string) get_post_meta($res->ID, "_ups_resource_type", true) !== "gsc") {
        continue;
    }
    $property = (string) get_post_meta($res->ID, "_ups_resource_external_id", true);
    $account_id = (int) get_post_meta($res->ID, "_ups_resource_google_account_id", true);
    echo "Resource #{$res->ID} property={$property} gacc={$account_id}\n";
    $raw = ups_audit_gsc_fetch_sitemap_indexation($account_id, $property);
    if (is_wp_error($raw)) {
        echo "  ERROR: " . $raw->get_error_message() . "\n";
        continue;
    }
    echo "  sitemaps: " . count((array) ($raw["sitemap"] ?? [])) . "\n";
    echo "  raw: " . substr(json_encode($raw), 0, 800) . "\n";
    $parsed = ups_audit_parse_gsc_sitemap_indexation(is_array($raw) ? $raw : [], $property);
    echo "  parsed: " . json_encode($parsed) . "\n";
}

echo "\n=== Try all GSC properties on gacc 962 ===\n";
foreach (ups_audit_fetch_gsc_resources(962) as $site) {
    if (!is_array($site)) {
        continue;
    }
    $url = (string) ($site["site_url"] ?? "");
    if (stripos($url, "wtapes") === false) {
        continue;
    }
    echo "Property: {$url}\n";
    $raw = ups_audit_gsc_fetch_sitemap_indexation(962, $url);
    if (is_wp_error($raw)) {
        echo "  ERROR: " . $raw->get_error_message() . "\n";
    } else {
        echo "  sitemaps: " . count((array) ($raw["sitemap"] ?? [])) . "\n";
        $parsed = ups_audit_parse_gsc_sitemap_indexation(is_array($raw) ? $raw : [], $url);
        echo "  parsed: " . json_encode($parsed) . "\n";
    }
}

echo "\nGlobal indexation pages for wtapes.pl:\n";
$n = 0;
foreach ((array) get_option("ups_gsc_indexation_pages", []) as $row) {
    if (!is_array($row)) {
        continue;
    }
    $u = (string) ($row["url"] ?? "");
    if (stripos($u, "wtapes") === false) {
        continue;
    }
    $n++;
    if ($n <= 3) {
        echo "  {$u} verdict=" . ($row["verdict"] ?? "") . "\n";
    }
}
echo "  total wtapes rows: {$n}\n";
echo "  last sync: " . get_option("ups_gsc_indexation_last_sync", "") . "\n";
