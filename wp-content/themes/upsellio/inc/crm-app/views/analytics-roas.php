<?php
if (!defined("ABSPATH")) {
    exit;
}
$roas_rows = isset($roas_rows) && is_array($roas_rows) ? $roas_rows : [];
?>
<section class="card">
  <h3>Źródła i ROAS</h3>
  <?php if (empty($roas_rows)) : ?>
    <p class="muted">Brak danych — włącz sync i poczekaj na cron.</p>
  <?php else : ?>
    <table>
      <thead><tr><th>Źródło</th><th>Kampania</th><th>Koszt</th><th>Leady</th><th>Won</th><th>Przychód</th><th>ROAS</th><th>ROI %</th></tr></thead>
      <tbody>
      <?php foreach ($roas_rows as $rr) : ?>
        <?php if (!is_array($rr)) { continue; } ?>
        <tr>
          <td><?php echo esc_html((string) ($rr["source"] ?? "")); ?></td>
          <td><?php echo esc_html((string) ($rr["campaign"] ?? "")); ?></td>
          <td><?php echo esc_html(number_format((float) ($rr["spend"] ?? 0), 0, ",", " ")); ?></td>
          <td><?php echo esc_html((string) (int) ($rr["leads"] ?? 0)); ?></td>
          <td><?php echo esc_html((string) (int) ($rr["won"] ?? 0)); ?></td>
          <td><?php echo esc_html(number_format((float) ($rr["revenue"] ?? 0), 0, ",", " ")); ?></td>
          <td><?php echo esc_html((string) ($rr["roas"] ?? 0)); ?></td>
          <td><?php echo esc_html((string) ($rr["roi_pct"] ?? 0)); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</section>
