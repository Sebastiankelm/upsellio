<?php
if (!defined("ABSPATH")) {
    exit(1);
}

$oauth = ups_audit_get_oauth_for_account(963);
$backup = upsellio_get_gsc_credentials();
upsellio_save_gsc_credentials(
    (string) ($oauth["client_id"] ?? ""),
    (string) ($oauth["client_secret"] ?? ""),
    (string) ($oauth["refresh_token"] ?? ""),
    (string) ($backup["property"] ?? "")
);

$queries = [
    "simple" => "SELECT campaign.id, campaign.name FROM campaign LIMIT 5",
    "metrics" => "SELECT campaign.id, campaign.name, metrics.impressions FROM campaign WHERE segments.date DURING LAST_30_DAYS LIMIT 5",
];

$cfg_key = upsellio_google_ads_config_option_key();
$base_cfg = upsellio_google_ads_get_settings();
$token = upsellio_gsc_get_access_token(upsellio_get_gsc_credentials());

foreach (["5195787252", "1333330388"] as $cid) {
    $cfg = $base_cfg;
    $cfg["customer_id"] = $cid;
    update_option($cfg_key, $cfg, false);

    foreach ($queries as $label => $query) {
        echo "=== customer {$cid} / {$label} ===\n";
        $r = upsellio_google_ads_gaql_search_stream($query);
        if (is_wp_error($r)) {
            echo "ERROR: " . $r->get_error_message() . "\n";
        } else {
            echo "OK rows=" . count((array) $r) . "\n";
        }
    }
}

update_option($cfg_key, $base_cfg, false);

function ups_audit_ads_gaql_raw(string $customer_id, string $query, string $access_token): void
{
    $cfg = upsellio_google_ads_get_settings();
    $url = upsellio_google_ads_rest_base_url() . "/customers/{$customer_id}/googleAds:searchStream";
    $response = wp_remote_post($url, [
        "timeout" => 45,
        "sslverify" => true,
        "headers" => array_merge(
            upsellio_google_ads_request_headers($access_token),
            ["Content-Type" => "application/json"]
        ),
        "body" => wp_json_encode(["query" => $query]),
    ]);
    $code = (int) wp_remote_retrieve_response_code($response);
    $body_raw = (string) wp_remote_retrieve_body($response);
    echo "RAW HTTP {$code}: " . substr($body_raw, 0, 800) . "\n";
}

if (is_string($token) && $token !== "") {
    ups_audit_ads_gaql_raw("5195787252", $queries["simple"], $token);
}

upsellio_save_gsc_credentials(
    (string) ($backup["client_id"] ?? ""),
    (string) ($backup["client_secret"] ?? ""),
    (string) ($backup["refresh_token"] ?? ""),
    (string) ($backup["property"] ?? "")
);
