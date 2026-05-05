<?php
if (!defined("ABSPATH")) {
    exit;
}

function upsellio_analytics_kpi_cards(int $range_days = 30): array
{
    $kpi = function_exists("upsellio_sales_engine_build_decision_layer_analytics")
        ? upsellio_sales_engine_build_decision_layer_analytics($range_days)
        : [];
    return [
        ["label" => "Win rate", "value" => (float) ($kpi["win_rate"] ?? 0), "suffix" => "%"],
        ["label" => "Active MRR", "value" => (float) ($kpi["mrr"] ?? 0), "suffix" => " zł"],
        ["label" => "Prognoza ważona", "value" => (float) ($kpi["forecast_weighted"] ?? 0), "suffix" => " zł"],
        ["label" => "Śr. time-to-close", "value" => (float) (($kpi["time_to_close_days"]["avg"] ?? 0)), "suffix" => " dni"],
    ];
}
