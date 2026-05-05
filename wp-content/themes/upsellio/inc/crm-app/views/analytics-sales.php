<?php
if (!defined("ABSPATH")) {
    exit;
}
$range_days = isset($_GET["range"]) ? (int) wp_unslash($_GET["range"]) : 30;
$decision_layer = function_exists("upsellio_sales_engine_build_decision_layer_analytics")
    ? upsellio_sales_engine_build_decision_layer_analytics($range_days)
    : ["win_rate" => 0, "mrr" => 0, "forecast_weighted" => 0, "time_to_close_days" => ["avg" => 0]];
?>
<section class="card">
  <h3>Sprzedaż</h3>
  <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;">
    <div class="kpi"><span class="muted">Win rate</span><b><?php echo esc_html(number_format((float) ($decision_layer["win_rate"] ?? 0), 1)); ?>%</b></div>
    <div class="kpi"><span class="muted">Active MRR</span><b><?php echo esc_html(number_format((float) ($decision_layer["mrr"] ?? 0), 0, ",", " ")); ?> zł</b></div>
    <div class="kpi"><span class="muted">Prognoza ważona</span><b><?php echo esc_html(number_format((float) ($decision_layer["forecast_weighted"] ?? 0), 0, ",", " ")); ?> zł</b></div>
    <div class="kpi"><span class="muted">Time to close</span><b><?php echo esc_html(number_format((float) (($decision_layer["time_to_close_days"]["avg"] ?? 0)), 1)); ?> dni</b></div>
  </div>
</section>
