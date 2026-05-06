<?php
if (!defined("ABSPATH")) {
    exit;
}
$range_days = isset($_GET["range"]) ? (int) wp_unslash($_GET["range"]) : 30;
$kpi_cards = function_exists("upsellio_analytics_kpi_cards") ? upsellio_analytics_kpi_cards($range_days) : [];
$charts = function_exists("upsellio_analytics_charts_series") ? upsellio_analytics_charts_series($range_days) : [];
?>
<section class="card">
  <h3>Ruch</h3>
  <?php if (empty($kpi_cards)) : ?>
    <p class="muted">Brak danych — włącz sync i poczekaj na cron.</p>
  <?php endif; ?>
  <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;">
    <?php foreach ($kpi_cards as $kpi) : ?>
      <div class="kpi"><span class="muted"><?php echo esc_html((string) ($kpi["label"] ?? "")); ?></span><b><?php echo esc_html(number_format((float) ($kpi["value"] ?? 0), 0, ",", " ")); ?><?php echo esc_html((string) ($kpi["suffix"] ?? "")); ?></b></div>
    <?php endforeach; ?>
  </div>
  <p class="muted" style="margin-top:10px;">Wykresy ApexCharts korzystają z serii: views/leads/impressions/clicks.</p>
  <pre style="font-size:11px;max-height:220px;overflow:auto;"><?php echo esc_html(wp_json_encode($charts)); ?></pre>
</section>
