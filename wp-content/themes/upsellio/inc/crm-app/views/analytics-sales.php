<?php
if (!defined("ABSPATH")) { exit; }
// The legacy decision-layer engine is a historical snapshot, not a date-filtered report.
$decision_layer = function_exists("upsellio_sales_engine_build_decision_layer_analytics")
    ? upsellio_sales_engine_build_decision_layer_analytics() : null;
$owners = is_array($decision_layer) ? (array) ($decision_layer["owners"] ?? []) : [];
$won = 0; $lost = 0; $deals = 0; $revenue = 0.0;
foreach ($owners as $row) {
    if (!is_array($row)) { continue; }
    $won += (int) ($row["won"] ?? 0);
    $lost += (int) ($row["lost"] ?? 0);
    $deals += (int) ($row["deals"] ?? 0);
    $revenue += (float) ($row["revenue"] ?? 0);
}
$closed = $won + $lost;
$fmt = static function ($n, $dec = 0, $suffix = "") {
    return is_numeric($n) ? number_format((float) $n, $dec, ",", " ") . $suffix : "—";
};
$cards = [
    ["Oferty w zestawieniu", is_array($decision_layer) ? $fmt($deals) : "—"],
    ["Wygrane", is_array($decision_layer) ? $fmt($won) : "—"],
    ["Przegrane", is_array($decision_layer) ? $fmt($lost) : "—"],
    ["Win rate (zamknięte)", $closed > 0 ? $fmt(100 * $won / $closed, 1, "%") : "—"],
    ["Wartość wygranych ofert", $deals > 0 ? $fmt($revenue, 2, " zł") : "—"],
    ["Prognoza ważona", $fmt($decision_layer["forecast_weighted"] ?? null, 2, " zł")],
    ["Średni czas domknięcia", !empty($decision_layer["time_to_close_days"]["count"]) ? $fmt($decision_layer["time_to_close_days"]["avg"] ?? null, 1, " dni") : "—"],
    ["Active MRR", $fmt($decision_layer["mrr"] ?? null, 2, " zł")],
];
?>
<section class="card crm-sales-report">
  <h2>Sprzedaż — zestawienie historyczne</h2>
  <p class="muted">Silnik obejmuje maksymalnie 2000 pobranych ofert. Przełącznik dni nie filtruje tego zestawienia. MRR nie jest szacowany z jednorazowych ofert.</p>
  <div class="crm-report-kpis">
    <?php foreach ($cards as [$label, $value]) : ?>
    <div class="kpi"><span class="muted"><?php echo esc_html($label); ?></span><b><?php echo esc_html($value); ?></b></div>
    <?php endforeach; ?>
  </div>
</section>
<section class="card">
  <h2>Wyniki według opiekuna</h2>
  <?php if (!$owners) : ?><p class="muted">Brak dostępnego zestawienia ofert.</p>
  <?php else : ?>
  <div style="overflow-x:auto"><table><thead><tr><th>Opiekun</th><th>Oferty</th><th>Wygrane</th><th>Przegrane</th><th>Win rate</th><th>Wartość wygranych ofert</th></tr></thead><tbody>
  <?php foreach ($owners as $owner => $row) :
    if (!is_array($row)) { continue; }
    $w = (int) ($row["won"] ?? 0); $l = (int) ($row["lost"] ?? 0);
  ?>
  <tr><td><?php echo esc_html((string) $owner); ?></td><td><?php echo esc_html($fmt($row["deals"] ?? null)); ?></td><td><?php echo esc_html($fmt($w)); ?></td><td><?php echo esc_html($fmt($l)); ?></td><td><?php echo esc_html($w + $l > 0 ? $fmt(100 * $w / ($w + $l), 1, "%") : "—"); ?></td><td><?php echo esc_html($fmt($row["revenue"] ?? null, 2, " zł")); ?></td></tr>
  <?php endforeach; ?>
  </tbody></table></div>
  <?php endif; ?>
</section>
