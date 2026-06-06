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

foreach (["LAST_30_DAYS", "LAST_60_DAYS", "LAST_90_DAYS"] as $range) {
    echo "=== campaigns {$range} ===\n";
    $c = upsellio_google_ads_fetch_campaigns($range);
    echo is_wp_error($c) ? "ERR: " . $c->get_error_message() . "\n" : "count=" . count((array) $c) . "\n";

    echo "=== daily {$range} ===\n";
    $d = upsellio_google_ads_fetch_daily_metrics($range);
    if (is_wp_error($d)) {
        echo "ERR: " . $d->get_error_message() . "\n";
    } else {
        echo "days=" . count((array) $d) . "\n";
        $sum_cost = 0.0;
        foreach ((array) $d as $day) {
            if (is_array($day)) {
                $sum_cost += (float) ($day["cost"] ?? 0);
            }
        }
        echo "total_cost=" . round($sum_cost, 2) . "\n";
    }
}

upsellio_save_gsc_credentials(
    (string) ($backup["client_id"] ?? ""),
    (string) ($backup["client_secret"] ?? ""),
    (string) ($backup["refresh_token"] ?? ""),
    (string) ($backup["property"] ?? "")
);
