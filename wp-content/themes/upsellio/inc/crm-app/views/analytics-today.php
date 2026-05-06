<?php
if (!defined("ABSPATH")) {
    exit;
}
$today_brief = (string) get_option("ups_ai_today_brief", "Kliknij Odśwież aby wygenerować.");
$anomalies = (array) get_option("ups_ai_anomaly_explanations", []);
?>
<section class="card">
  <h3>Dziś</h3>
  <div class="crm-brief-card" style="margin-bottom:12px">
    <div class="crm-brief-badge"><i class="ti ti-sparkles" aria-hidden="true"></i>AI Podsumowanie dnia</div>
    <div class="crm-brief-title" id="crm-today-brief-text"><?php echo esc_html($today_brief); ?></div>
    <div class="crm-brief-actions">
      <button class="crm-brief-btn white" type="button" onclick="crmRefreshTodayBrief()"><i class="ti ti-refresh" aria-hidden="true"></i>Odśwież brief</button>
      <button class="crm-brief-btn ghost" type="button" onclick="crmAI('Co powinienem zrobić teraz na podstawie danych z dziś CRM Upsellio')"><i class="ti ti-list-check" aria-hidden="true"></i>Co zrobić teraz</button>
    </div>
  </div>
  <h4>Top 3 hot leady</h4>
  <?php
  $hot_leads = get_posts([
      "post_type" => "lead",
      "posts_per_page" => 3,
      "meta_query" => [["key" => "_upsellio_lead_score", "value" => 60, "compare" => ">="]],
      "orderby" => "meta_value_num",
      "meta_key" => "_upsellio_lead_score",
      "order" => "DESC",
  ]);
  if (empty($hot_leads)) {
      echo '<p class="muted">Brak leadów ze score ≥60.</p>';
  } else {
      echo "<ul>";
      foreach ($hot_leads as $lead) {
          $score = (int) get_post_meta((int) $lead->ID, "_upsellio_lead_score", true);
          echo "<li>" . esc_html(get_the_title((int) $lead->ID)) . " — " . esc_html((string) $score) . "/100</li>";
      }
      echo "</ul>";
  }
  ?>
  <h4>Anomalie</h4>
  <?php if (empty($anomalies)) : ?>
    <p class="muted">Brak danych — włącz sync i poczekaj na cron.</p>
  <?php else : ?>
    <?php foreach (array_slice(array_values($anomalies), 0, 5) as $a) : ?>
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

  <?php if (function_exists("upsellio_render_gsc_indexation_teaser")) : ?>
    <?php upsellio_render_gsc_indexation_teaser(); ?>
  <?php endif; ?>
</section>
