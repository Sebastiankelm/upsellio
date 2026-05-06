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
    <div class="crm-brief-card" style="margin-bottom:12px">
      <div class="crm-brief-badge"><i class="ti ti-sparkles" aria-hidden="true"></i>Brief tygodniowy AI <?php if (!empty($weekly["at"])) { echo "· " . esc_html(wp_date("j M H:i", (int) $weekly["at"])); } ?></div>
      <div class="crm-brief-title"><?php echo wp_kses_post((string) $weekly["html"]); ?></div>
      <div class="crm-brief-actions">
        <button type="button" class="crm-brief-btn white" onclick="crmRefreshWeeklyBrief()">Odśwież brief AI</button>
        <button type="button" class="crm-brief-btn ghost" onclick="crmAI('Co powinienem priorytetyzować w tym tygodniu CRM Upsellio')">Priorytety tygodnia</button>
      </div>
    </div>
  <?php else : ?>
    <p class="muted">Brak danych — włącz sync i poczekaj na cron.</p>
  <?php endif; ?>
  <?php if ($icp !== "") : ?>
    <h3>ICP</h3><div style="max-height:220px;overflow:auto"><?php echo wp_kses_post($icp); ?></div>
  <?php endif; ?>
  <?php if (!empty($anomalies)) : ?>
    <h3>Anomalie</h3>
    <?php foreach (array_slice(array_values($anomalies), 0, 4) as $a) : ?>
      <?php if (!is_array($a)) { continue; } ?>
      <?php $pct = (float) ($a["pct"] ?? 0); ?>
      <?php $cls = $pct >= 10 ? "gr" : ($pct <= -10 ? "rd" : "am"); ?>
      <div class="crm-anomaly-row">
        <div class="crm-an-indicator <?php echo esc_attr($cls); ?>"></div>
        <div class="crm-an-body">
          <div class="crm-an-channel"><?php echo esc_html((string) ($a["channel"] ?? "")); ?></div>
          <div class="crm-an-metric"><?php echo esc_html((string) ($a["metric"] ?? "")); ?> <?php echo $pct >= 0 ? "+" : ""; ?><?php echo esc_html((string) $pct); ?>%</div>
          <div class="crm-an-why"><?php echo esc_html((string) ($a["why"] ?? "")); ?></div>
          <div class="crm-an-action"><i class="ti ti-arrow-right" aria-hidden="true"></i><?php echo esc_html((string) ($a["action"] ?? "")); ?></div>
        </div>
      </div>
    <?php endforeach; ?>
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
