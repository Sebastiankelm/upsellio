<?php
if (!defined("ABSPATH")) {
    exit;
}

function upsellio_analytics_pareto(array $rows, string $metric = "value"): array
{
    usort($rows, static function ($a, $b) use ($metric) {
        return ((float) ($b[$metric] ?? 0)) <=> ((float) ($a[$metric] ?? 0));
    });
    $total = array_sum(array_map(static fn($r) => (float) ($r[$metric] ?? 0), $rows));
    $cum = 0.0;
    $count_for_80pct = 0;
    foreach ($rows as $i => $row) {
        $cum += (float) ($row[$metric] ?? 0);
        if ($count_for_80pct === 0 && $total > 0 && ($cum / $total) >= 0.8) {
            $count_for_80pct = $i + 1;
        }
    }
    return ["count_for_80pct" => $count_for_80pct, "total" => $total];
}
