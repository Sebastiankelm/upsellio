<?php
if (!defined("ABSPATH")) {
    exit;
}
$range_days = isset($_GET["range"]) ? (int) wp_unslash($_GET["range"]) : 30;
$query_lead = function_exists("upsellio_analytics_query_lead_value") ? upsellio_analytics_query_lead_value($range_days, 50) : ["rows" => [], "total_value" => 0];
$pareto = function_exists("upsellio_analytics_pareto") ? upsellio_analytics_pareto((array) ($query_lead["rows"] ?? []), "value") : ["count_for_80pct" => 0];
$pages_to_optimize = (array) get_option("ups_ai_page_perf_suggestions", []);
?>
<section class="card">
  <h3>SEO</h3>
  <p class="muted">Łączny przychód z zapytań: <?php echo esc_html(number_format((float) ($query_lead["total_value"] ?? 0), 0, ",", " ")); ?> zł</p>
  <p class="muted">Pareto 80/20: <?php echo esc_html((string) ((int) ($pareto["count_for_80pct"] ?? 0))); ?> słów.</p>
  <table>
    <thead><tr><th>Query</th><th>Leady</th><th>Wygrane</th><th>Wartość</th></tr></thead>
    <tbody>
    <?php foreach (array_slice((array) ($query_lead["rows"] ?? []), 0, 20) as $row) : ?>
      <tr>
        <td><?php echo esc_html((string) ($row["query"] ?? "")); ?></td>
        <td><?php echo esc_html((string) ((int) ($row["leads"] ?? 0))); ?></td>
        <td><?php echo esc_html((string) ((int) ($row["won"] ?? 0))); ?></td>
        <td><?php echo esc_html(number_format((float) ($row["value"] ?? 0), 0, ",", " ")); ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <h4 style="margin-top:12px;">Pages to optimize (AI)</h4>
  <ul><?php foreach (array_slice($pages_to_optimize, 0, 8) as $p) { if (!is_array($p)) { continue; } echo "<li>" . esc_html((string) ($p["title"] ?? "—")) . "</li>"; } ?></ul>
</section>
