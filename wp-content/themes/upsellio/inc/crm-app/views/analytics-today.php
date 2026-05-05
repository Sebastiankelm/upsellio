<?php
if (!defined("ABSPATH")) {
    exit;
}
$brief_data = function_exists("upsellio_get_latest_weekly_brief") ? upsellio_get_latest_weekly_brief() : ["html" => "", "at" => 0];
$anomalies = (array) get_option("ups_ai_anomaly_explanations", []);
?>
<section class="card">
  <h3>Dziś</h3>
  <?php if (!empty($brief_data["html"])) : ?>
    <div style="padding:12px;background:#fff7ed;border-left:4px solid #f97316;margin-bottom:12px;"><?php echo wp_kses_post((string) $brief_data["html"]); ?></div>
  <?php endif; ?>
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
    <p class="muted">Brak aktywnych anomalii.</p>
  <?php else : ?>
    <?php foreach (array_slice(array_values($anomalies), 0, 5) as $a) : ?>
      <?php if (!is_array($a)) { continue; } ?>
      <div style="padding:8px 0;border-top:1px solid var(--border)"><strong><?php echo esc_html((string) ($a["channel"] ?? "")); ?></strong> — <?php echo esc_html((string) ($a["action"] ?? "")); ?></div>
    <?php endforeach; ?>
  <?php endif; ?>
</section>
