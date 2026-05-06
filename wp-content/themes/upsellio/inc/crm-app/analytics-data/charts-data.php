<?php
if (!defined("ABSPATH")) {
    exit;
}

function upsellio_analytics_charts_series(int $range_days = 30): array
{
    $range_days = max(1, $range_days);

    // Buduj zakres dat (jak site-analytics.php / upsellio_get_analytics_dates)
    $dates = [];
    for ($i = $range_days - 1; $i >= 0; $i--) {
        $dates[] = wp_date("Y-m-d", strtotime("-{$i} days"));
    }
    $prev_dates = [];
    for ($i = ($range_days * 2) - 1; $i >= $range_days; $i--) {
        $prev_dates[] = wp_date("Y-m-d", strtotime("-{$i} days"));
    }

    // Views z WP custom tracker (upsellio_daily_views)
    $views_series = function_exists("upsellio_get_daily_views_series")
        ? upsellio_get_daily_views_series($dates)
        : array_fill_keys($dates, 0);
    $prev_views = function_exists("upsellio_get_daily_views_series")
        ? upsellio_get_daily_views_series($prev_dates)
        : array_fill_keys($prev_dates, 0);

    // Leady z WP posts
    $leads_series = function_exists("upsellio_get_daily_leads_series")
        ? upsellio_get_daily_leads_series($dates)
        : array_fill_keys($dates, 0);
    $prev_leads = function_exists("upsellio_get_daily_leads_series")
        ? upsellio_get_daily_leads_series($prev_dates)
        : array_fill_keys($prev_dates, 0);

    // Impressions + clicks z GSC (upsellio_keyword_metrics_rows — wiersze z polem date)
    $keyword_rows = (array) get_option("upsellio_keyword_metrics_rows", []);
    $kw_series = function_exists("upsellio_get_daily_keyword_series")
        ? upsellio_get_daily_keyword_series($keyword_rows, $dates)
        : [];
    $prev_kw = function_exists("upsellio_get_daily_keyword_series")
        ? upsellio_get_daily_keyword_series($keyword_rows, $prev_dates)
        : [];

    // Konwertuj do formatu [[date, value], ...]
    $to_pairs = function (array $series): array {
        $out = [];
        foreach ($series as $date => $value) {
            $out[] = [(string) $date, (float) $value];
        }
        return $out;
    };

    $impr = function (array $kw_series): array {
        $out = [];
        foreach ($kw_series as $date => $row) {
            $out[] = [(string) $date, (float) ($row["impressions"] ?? 0)];
        }
        return $out;
    };

    $clks = function (array $kw_series): array {
        $out = [];
        foreach ($kw_series as $date => $row) {
            $out[] = [(string) $date, (float) ($row["clicks"] ?? 0)];
        }
        return $out;
    };

    return [
        "views"       => ["current" => $to_pairs($views_series), "previous" => $to_pairs($prev_views)],
        "leads"       => ["current" => $to_pairs($leads_series), "previous" => $to_pairs($prev_leads)],
        "impressions" => ["current" => $impr($kw_series), "previous" => $impr($prev_kw)],
        "clicks"      => ["current" => $clks($kw_series), "previous" => $clks($prev_kw)],
    ];
}
