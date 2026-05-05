<?php
if (!defined("ABSPATH")) {
    exit;
}

function upsellio_analytics_charts_series(int $range_days = 30): array
{
    $daily = (array) get_option("upsellio_gsc_daily_aggregates", []);
    $current = array_slice($daily, -$range_days);
    $previous = array_slice($daily, -($range_days * 2), $range_days);
    $map = static function (array $rows, string $key): array {
        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $out[] = [(string) ($row["date"] ?? ""), (float) ($row[$key] ?? 0)];
        }
        return $out;
    };
    return [
        "views" => ["current" => $map($current, "views"), "previous" => $map($previous, "views")],
        "leads" => ["current" => $map($current, "leads"), "previous" => $map($previous, "leads")],
        "impressions" => ["current" => $map($current, "impressions"), "previous" => $map($previous, "impressions")],
        "clicks" => ["current" => $map($current, "clicks"), "previous" => $map($previous, "clicks")],
    ];
}
