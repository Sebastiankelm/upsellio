<?php

if (!defined("ABSPATH")) {
    exit;
}

/** @var array<int, array<string, string>> $ups_audit_recommendations */
$ups_audit_recommendations = isset($ups_audit_recommendations) && is_array($ups_audit_recommendations)
    ? $ups_audit_recommendations
    : [];
$prio_colors = [
    "high" => ["bg" => "#fef2f2", "border" => "#fecaca", "badge" => "var(--danger,#dc2626)"],
    "medium" => ["bg" => "#fffbeb", "border" => "#fde68a", "badge" => "#d97706"],
    "low" => ["bg" => "#f0fdf4", "border" => "#bbf7d0", "badge" => "var(--ok,#16a34a)"],
];
$cat_labels = [
    "setup" => "Konfiguracja",
    "sync" => "Sync",
    "gsc" => "GSC",
    "ga4" => "GA4",
    "ads" => "Ads",
    "seo" => "SEO",
    "general" => "Ogólne",
];
?>
<section class="card" id="ups-audit-recommendations-panel">
  <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:12px;">
    <h3 style="margin:0;font-size:15px;">Rekomendacje i alerty</h3>
    <span class="muted" style="font-size:12px;">Reguły na podstawie GSC, GA4 i Google Ads (okres vs poprzedni)</span>
  </div>
  <?php if (empty($ups_audit_recommendations)) : ?>
    <p class="muted" style="margin:0;">Brak rekomendacji — uruchom sync i upewnij się, że zasoby są zmapowane.</p>
  <?php else : ?>
    <div style="display:flex;flex-direction:column;gap:10px;">
      <?php foreach ($ups_audit_recommendations as $tip) : ?>
        <?php
        if (!is_array($tip)) {
            continue;
        }
        $prio = (string) ($tip["priority"] ?? "low");
        $col = $prio_colors[$prio] ?? $prio_colors["low"];
        $cat = (string) ($tip["category"] ?? "general");
        ?>
        <div style="padding:12px 14px;border-radius:10px;background:<?php echo esc_attr($col["bg"]); ?>;border:1px solid <?php echo esc_attr($col["border"]); ?>;">
          <div style="display:flex;gap:8px;align-items:flex-start;flex-wrap:wrap;">
            <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:<?php echo esc_attr($col["badge"]); ?>;padding:2px 8px;border:1px solid <?php echo esc_attr($col["border"]); ?>;border-radius:999px;">
              <?php echo esc_html($prio === "high" ? "Pilne" : ($prio === "medium" ? "Średnie" : "Info")); ?>
            </span>
            <span class="muted" style="font-size:10px;font-weight:600;text-transform:uppercase;"><?php echo esc_html($cat_labels[$cat] ?? $cat); ?></span>
          </div>
          <div style="font-weight:700;font-size:13px;margin:6px 0 4px;"><?php echo esc_html((string) ($tip["title"] ?? "")); ?></div>
          <p style="margin:0;font-size:12px;line-height:1.5;color:var(--text-2,#444);"><?php echo esc_html((string) ($tip["detail"] ?? "")); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
