<?php
if (!defined("ABSPATH")) {
    exit;
}
$weekly = function_exists("upsellio_get_latest_weekly_brief") ? upsellio_get_latest_weekly_brief() : ["html" => "", "at" => 0];
$icp = (string) get_option("ups_ai_icp_report", "");
$page_perf = (array) get_option("ups_ai_page_perf_suggestions", []);
$form_ab = (array) get_option("ups_ai_form_ab_suggestions", []);
$ad_copy = (string) get_option("ups_ai_ad_copy", "");
$anomalies = (array) get_option("ups_ai_anomaly_explanations", []);
?>
<section class="card">
  <h2>✨ Insights AI</h2>
  <?php if (!empty($weekly["html"])) : ?>
    <div style="padding:16px;background:#fff7ed;border-left:4px solid #f97316;margin-bottom:10px;"><?php echo wp_kses_post((string) $weekly["html"]); ?></div>
  <?php endif; ?>
  <?php if ($icp !== "") : ?>
    <h3>ICP</h3><div style="max-height:220px;overflow:auto"><?php echo wp_kses_post($icp); ?></div>
  <?php endif; ?>
  <?php if (!empty($anomalies)) : ?>
    <h3>Anomalie</h3>
    <ul><?php foreach (array_slice(array_values($anomalies), 0, 5) as $a) { if (!is_array($a)) { continue; } echo "<li>" . esc_html((string) (($a["metric"] ?? "") . ": " . ($a["action"] ?? ""))) . "</li>"; } ?></ul>
  <?php endif; ?>
  <?php if (!empty($page_perf)) : ?>
    <h3>Pages to optimize</h3>
    <ul><?php foreach (array_slice($page_perf, 0, 6) as $p) { if (!is_array($p)) { continue; } echo "<li>" . esc_html((string) ($p["title"] ?? "—")) . "</li>"; } ?></ul>
  <?php endif; ?>
  <?php if (!empty($form_ab["suggestions"])) : ?>
    <h3>Form AB</h3>
    <ul><?php foreach (array_slice((array) $form_ab["suggestions"], 0, 4) as $f) { if (!is_array($f)) { continue; } echo "<li>" . esc_html((string) ($f["suggested_test"] ?? "")) . "</li>"; } ?></ul>
  <?php endif; ?>
  <?php if ($ad_copy !== "") : ?>
    <h3>Ad copy</h3>
    <pre style="max-height:260px;overflow:auto"><?php echo esc_html($ad_copy); ?></pre>
  <?php endif; ?>
</section>
