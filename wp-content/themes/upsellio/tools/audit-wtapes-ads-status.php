<?php
/**
 * Status Ads dla profilu wtapes.
 * wp eval-file wp-content/themes/upsellio/tools/audit-wtapes-ads-status.php
 */

if (!defined("ABSPATH")) {
    exit(1);
}

$profiles = get_posts([
    "post_type" => "crm_client",
    "posts_per_page" => -1,
    "post_status" => ["publish", "draft"],
    "s" => "wtapes",
]);
foreach ($profiles as $p) {
    if (!($p instanceof WP_Post)) {
        continue;
    }
    $cid = (int) $p->ID;
    echo "CLIENT #{$cid} " . $p->post_title . "\n";
}

$accounts = get_posts([
    "post_type" => "crm_google_account",
    "posts_per_page" => -1,
    "post_status" => ["publish", "draft"],
]);
foreach ($accounts as $acc) {
    if (!($acc instanceof WP_Post)) {
        continue;
    }
    $aid = (int) $acc->ID;
    $email = (string) get_post_meta($aid, "_ups_gacc_email", true);
    $scopes = get_post_meta($aid, "_ups_gacc_scopes", true);
    $has_ads = function_exists("ups_audit_account_has_oauth_scope")
        ? (ups_audit_account_has_oauth_scope($aid, "adwords") ? "yes" : "no")
        : "?";
    echo "GACC #{$aid} {$email} adwords_scope={$has_ads} scopes=" . wp_json_encode($scopes) . "\n";
}

$resources = get_posts([
    "post_type" => "crm_audit_resource",
    "posts_per_page" => -1,
    "post_status" => ["publish", "draft"],
    "meta_query" => [["key" => "_ups_resource_type", "value" => "ads"]],
]);
foreach ($resources as $r) {
    if (!($r instanceof WP_Post)) {
        continue;
    }
    $rid = (int) $r->ID;
    $cid = (int) get_post_meta($rid, "_ups_resource_client_id", true);
    $gacc = (int) get_post_meta($rid, "_ups_resource_google_account_id", true);
    $ext = (string) get_post_meta($rid, "_ups_resource_external_id", true);
    $health = function_exists("ups_audit_resource_health") ? ups_audit_resource_health($rid) : [];
    echo "ADS_RES #{$rid} client={$cid} gacc={$gacc} cid={$ext} status=" . ($health["label"] ?? "?") . "\n";
    $cache = get_post_meta($rid, "_ups_resource_data_cache", true);
    if (is_array($cache) && !empty($cache["summary"])) {
        echo "  summary=" . wp_json_encode($cache["summary"]) . "\n";
    }
}

$cfg = function_exists("upsellio_google_ads_get_settings") ? upsellio_google_ads_get_settings() : [];
echo "WP Ads cfg: dev_len=" . strlen((string) ($cfg["developer_token"] ?? ""))
    . " login=" . ($cfg["login_customer_id"] ?? "")
    . " customer=" . ($cfg["customer_id"] ?? "") . "\n";
echo "api_ready=" . (function_exists("upsellio_google_ads_api_ready") && upsellio_google_ads_api_ready() ? "yes" : "no") . "\n";
