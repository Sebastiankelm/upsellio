<?php
if (!defined("ABSPATH")) {
    exit;
}
// $roas_rows nie jest przekazywane przez include — pobieramy sami
$roas_rows = function_exists("upsellio_sales_engine_build_roas_report_rows")
    ? upsellio_sales_engine_build_roas_report_rows()
    : [];
?>
<section class="card">
  <h3 style="margin:0 0 16px">Źródła i ROAS</h3>

  <?php if (empty($roas_rows)) : ?>
    <div style="padding:32px;text-align:center;background:var(--bg);border-radius:var(--r-md);border:1px dashed var(--border)">
      <div style="font-size:32px;margin-bottom:8px">📈</div>
      <p class="muted" style="margin:0">Brak danych ROAS — potrzebne koszty kampanii (CSV w Ustawienia → Płatne) oraz leady z UTM.</p>
    </div>
  <?php else : ?>
    <div style="overflow-x:auto">
    <table style="width:100%;border-collapse:collapse;font-size:13px">
      <thead>
        <tr style="background:var(--bg);border-bottom:2px solid var(--border)">
          <th style="text-align:left;padding:8px 10px">Źródło</th>
          <th style="text-align:left;padding:8px 10px">Kampania</th>
          <th style="text-align:right;padding:8px 10px">Koszt</th>
          <th style="text-align:right;padding:8px 10px">Leady</th>
          <th style="text-align:right;padding:8px 10px">Wygrane</th>
          <th style="text-align:right;padding:8px 10px">Przychód</th>
          <th style="text-align:right;padding:8px 10px">ROAS</th>
          <th style="text-align:right;padding:8px 10px">ROI%</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($roas_rows as $rr) :
          if (!is_array($rr)) {
              continue;
          }
          $roas = (float) ($rr["roas"] ?? 0);
          $roas_color = $roas >= 3 ? "var(--success)" : ($roas >= 1 ? "var(--warn)" : "var(--danger)");
          ?>
      <tr style="border-bottom:1px solid var(--border)">
        <td style="padding:7px 10px"><?php echo esc_html((string) ($rr["source"] ?? "")); ?></td>
        <td style="padding:7px 10px;color:var(--text-3)"><?php echo esc_html((string) ($rr["campaign"] ?? "")); ?></td>
        <td style="text-align:right;padding:7px 10px"><?php echo esc_html(number_format((float) ($rr["spend"] ?? 0), 0, ",", " ")); ?> zł</td>
        <td style="text-align:right;padding:7px 10px"><?php echo (int) ($rr["leads"] ?? 0); ?></td>
        <td style="text-align:right;padding:7px 10px"><?php echo (int) ($rr["won"] ?? 0); ?></td>
        <td style="text-align:right;padding:7px 10px;font-weight:600"><?php echo esc_html(number_format((float) ($rr["revenue"] ?? 0), 0, ",", " ")); ?> zł</td>
        <td style="text-align:right;padding:7px 10px;font-weight:700;color:<?php echo esc_attr($roas_color); ?>"><?php echo esc_html((string) ($rr["roas"] ?? 0)); ?>x</td>
        <td style="text-align:right;padding:7px 10px;color:<?php echo esc_attr($roas_color); ?>"><?php echo esc_html((string) ($rr["roi_pct"] ?? 0)); ?>%</td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</section>
