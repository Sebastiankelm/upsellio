<?php
/**
 * Jednorazowa konfiguracja profili audytu + import GSC + mapowanie + sync.
 * Uruchom: wp eval-file wp-content/themes/upsellio/tools/audit-setup-profiles.php
 */

if (!defined("ABSPATH")) {
    exit(1);
}

$plans = [
    "wtapes" => [
        "title" => "wtapes",
        "website" => "https://wtapes.pl/",
        "resources" => [
            ["google_account_id" => 962, "type" => "gsc", "external_id" => "https://wtapes.pl/", "display_name" => "https://wtapes.pl/"],
            ["google_account_id" => 962, "type" => "ga4", "external_id" => "484708285", "display_name" => "GA Wtapes"],
            ["google_account_id" => 963, "type" => "ads", "external_id" => "5195787252", "display_name" => "wtapes Google Ads"],
        ],
    ],
    "lapidaria" => [
        "title" => "Lapidaria",
        "website" => "https://lapidaria.pl/",
        "resources" => [
            ["google_account_id" => 963, "type" => "gsc", "external_id" => "https://lapidaria.pl/", "display_name" => "https://lapidaria.pl/"],
        ],
    ],
    "upsellio" => [
        "title" => "Upsellio",
        "website" => "https://upsellio.pl/",
        "resources" => [
            ["google_account_id" => 962, "type" => "gsc", "external_id" => "https://upsellio.pl/", "display_name" => "https://upsellio.pl/"],
        ],
    ],
];

function ups_audit_setup_import_resource(array $row): int
{
    $google_account_id = (int) ($row["google_account_id"] ?? 0);
    $type = sanitize_key((string) ($row["type"] ?? ""));
    $external_id = sanitize_text_field((string) ($row["external_id"] ?? ""));
    $display_name = sanitize_text_field((string) ($row["display_name"] ?? $external_id));
    if ($google_account_id <= 0 || $type === "" || $external_id === "") {
        return 0;
    }
    if (function_exists("ups_audit_find_imported_resource_id")) {
        $existing = ups_audit_find_imported_resource_id($google_account_id, $type, $external_id);
        if ($existing > 0) {
            return $existing;
        }
    }
    $resource_id = (int) wp_insert_post([
        "post_type" => "crm_audit_resource",
        "post_title" => $display_name !== "" ? $display_name : $external_id,
        "post_status" => "publish",
    ]);
    if ($resource_id <= 0) {
        return 0;
    }
    update_post_meta($resource_id, "_ups_resource_type", $type);
    update_post_meta($resource_id, "_ups_resource_external_id", $external_id);
    update_post_meta($resource_id, "_ups_resource_display_name", $display_name);
    update_post_meta($resource_id, "_ups_resource_parent_account_id", "");
    update_post_meta($resource_id, "_ups_resource_google_account_id", $google_account_id);
    update_post_meta($resource_id, "_ups_resource_client_id", 0);
    update_post_meta($resource_id, "_ups_resource_imported_at", current_time("mysql"));

    return $resource_id;
}

function ups_audit_setup_find_or_create_profile(string $slug, array $plan): int
{
    $title = (string) ($plan["title"] ?? $slug);
    $website = (string) ($plan["website"] ?? "");
    $cid = 0;
    foreach (get_posts([
        "post_type" => "crm_client",
        "posts_per_page" => -1,
        "post_status" => ["publish", "draft"],
    ]) as $c) {
        if ($c instanceof WP_Post && (string) $c->post_title === $title) {
            $cid = (int) $c->ID;
            break;
        }
    }
    if ($cid > 0) {
        update_post_meta($cid, "_ups_audit_profile", "1");
        if ($website !== "") {
            update_post_meta($cid, "_ups_client_website", esc_url_raw($website));
        }
        echo "Profile exists: {$title} (#{$cid})\n";
        return $cid;
    }
    if (!function_exists("ups_audit_create_audit_profile")) {
        echo "ERROR: ups_audit_create_audit_profile missing\n";
        return 0;
    }
    $cid = ups_audit_create_audit_profile($title, $website);
    if (is_wp_error($cid)) {
        echo "ERROR create {$title}: " . $cid->get_error_message() . "\n";
        return 0;
    }
    echo "Created profile: {$title} (#{$cid})\n";
    return (int) $cid;
}

$all_resource_ids = [];
foreach ($plans as $slug => $plan) {
    echo "\n=== {$slug} ===\n";
    $client_id = ups_audit_setup_find_or_create_profile($slug, $plan);
    if ($client_id <= 0) {
        continue;
    }
    $selected = [];
    foreach ((array) ($plan["resources"] ?? []) as $row) {
        if (!is_array($row)) {
            continue;
        }
        $rid = ups_audit_setup_import_resource($row);
        if ($rid > 0) {
            update_post_meta($rid, "_ups_resource_client_id", $client_id);
            $selected[] = $rid;
            $all_resource_ids[] = $rid;
            echo "  mapped resource #{$rid} -> client #{$client_id}\n";
        }
    }
    if (function_exists("ups_audit_get_client_resources")) {
        foreach (ups_audit_get_client_resources($client_id) as $res) {
            if (!($res instanceof WP_Post)) {
                continue;
            }
            $rid = (int) $res->ID;
            if (!in_array($rid, $selected, true)) {
                update_post_meta($rid, "_ups_resource_client_id", 0);
            }
        }
    }
}

echo "\n=== SYNC (30 dni) ===\n";
if (function_exists("ups_audit_sync_all_mapped_resources")) {
    $result = ups_audit_sync_all_mapped_resources(30);
    echo "Sync ok=" . (int) ($result["ok"] ?? 0) . " fail=" . (int) ($result["fail"] ?? 0) . "\n";
} else {
    foreach (array_unique($all_resource_ids) as $rid) {
        if (function_exists("ups_audit_sync_resource_action")) {
            ups_audit_sync_resource_action((int) $rid, 30);
            echo "  synced #{$rid}\n";
        }
    }
}

echo "\n=== DASHBOARD KPI ===\n";
foreach ($plans as $slug => $plan) {
    $title = (string) ($plan["title"] ?? $slug);
    $cid = 0;
    foreach (get_posts([
        "post_type" => "crm_client",
        "posts_per_page" => -1,
        "post_status" => ["publish", "draft"],
    ]) as $c) {
        if ($c instanceof WP_Post && (string) $c->post_title === $title) {
            $cid = (int) $c->ID;
            break;
        }
    }
    if ($cid <= 0) {
        continue;
    }
    $data = function_exists("ups_audit_aggregate_client_data")
        ? ups_audit_aggregate_client_data($cid, 30, 0, false)
        : [];
    echo "{$title} (#{$cid}): GSC clicks=" . (int) ($data["gsc_clicks"] ?? 0)
        . " GA4 sessions=" . (int) ($data["ga4_sessions"] ?? 0)
        . " resources=" . count((array) ($data["resources"] ?? [])) . "\n";
}

echo "\nDone.\n";
