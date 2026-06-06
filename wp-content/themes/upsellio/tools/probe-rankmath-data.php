<?php
define("WP_USE_THEMES", false);
require dirname(__DIR__, 4) . "/wp-load.php";

global $wpdb;
$prefix = $wpdb->prefix;

$tables = [
    "rank_math_analytics_gsc",
    "rank_math_analytics_objects",
    "rank_math_analytics_inspections",
];

foreach ($tables as $t) {
    $full = $prefix . $t;
    $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $full)) === $full;
    if (!$exists) {
        echo "{$t}: brak tabeli\n";
        continue;
    }
    $cnt = (int) $wpdb->get_var("SELECT COUNT(*) FROM `{$full}`");
    $max = $wpdb->get_var("SELECT MAX(created) FROM `{$full}`");
    echo "{$t}: {$cnt} wierszy, ostatni created={$max}\n";
}

if (class_exists("\RankMath\Google\Authentication")) {
    echo "RM authorized: " . (\RankMath\Google\Authentication::is_authorized() ? "yes" : "no") . "\n";
    echo "RM GSC connected: " . (\RankMath\Google\Console::is_console_connected() ? "yes" : "no") . "\n";
    echo "RM GA4 connected: " . (\RankMath\Google\Analytics::is_analytics_connected() ? "yes" : "no") . "\n";
}

if (class_exists("\RankMath\Analytics\Stats")) {
    \RankMath\Analytics\Stats::get()->set_date_range(30);
    $w = (new \RankMath\Analytics\Summary())->get_widget();
    echo "RM widget clicks: " . json_encode($w->clicks ?? null) . "\n";
    echo "RM widget impressions: " . json_encode($w->impressions ?? null) . "\n";
}

$ups_kw = count((array) get_option("upsellio_keyword_metrics_rows", []));
echo "Upsellio keyword rows option: {$ups_kw}\n";
echo "Upsellio GSC last sync: " . (string) get_option("upsellio_keyword_metrics_last_sync", "-") . "\n";
