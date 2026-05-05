<?php
if (!defined("ABSPATH")) {
    exit;
}
$range_days = isset($_GET["range"]) ? (int) wp_unslash($_GET["range"]) : 30;
$campaigns = (array) get_option("ups_ads_campaigns_data", []);
$ai_review = get_option("ups_ai_ads_review", []);
$channel_ltv = function_exists("upsellio_analytics_channel_ltv") ? upsellio_analytics_channel_ltv($range_days) : ["rows" => []];
?>
<section class="card">
  <h3>Płatne</h3>
  <?php if (!empty($ai_review) && is_array($ai_review)) : ?>
    <div style="padding:12px;background:#fff7ed;border-left:4px solid #f97316;margin-bottom:10px;"><?php echo esc_html((string) ($ai_review["summary"] ?? "")); ?></div>
  <?php endif; ?>
  <p class="muted">Kampanie z API: <?php echo esc_html((string) count($campaigns)); ?></p>
  <table>
    <thead><tr><th>Source/Medium</th><th>Sessions</th><th>Leady</th><th>Wygrane</th><th>LTV/sesja</th></tr></thead>
    <tbody>
    <?php foreach ((array) ($channel_ltv["rows"] ?? []) as $r) : ?>
      <tr>
        <td><?php echo esc_html((string) ($r["channel"] ?? "")); ?></td>
        <td><?php echo esc_html((string) ((int) ($r["sessions"] ?? 0))); ?></td>
        <td><?php echo esc_html((string) ((int) ($r["leads"] ?? 0))); ?></td>
        <td><?php echo esc_html((string) ((int) ($r["won"] ?? 0))); ?></td>
        <td><?php echo esc_html(number_format((float) ($r["ltv_per_session"] ?? 0), 2, ",", " ")); ?> zł</td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</section>
