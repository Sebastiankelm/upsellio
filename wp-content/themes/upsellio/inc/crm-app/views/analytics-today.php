<?php
if (!defined("ABSPATH")) {
    exit;
}
$today_brief = (string) get_option("ups_ai_today_brief", "Kliknij Odśwież aby wygenerować.");
$anomalies = (array) get_option("ups_ai_anomaly_explanations", []);

// Indeksacja — summary
$idx_summary = (array) get_option("ups_gsc_indexation_summary", []);
$idx_submitted = (int) ($idx_summary["submitted"] ?? 0);
$idx_indexed = (int) ($idx_summary["indexed"] ?? 0);
$idx_ratio = $idx_submitted > 0 ? (int) round($idx_indexed / $idx_submitted * 100) : 0;
$idx_problems = count(array_filter(
    (array) get_option("ups_gsc_indexation_pages", []),
    static function ($r) {
        return is_array($r) && (string) ($r["verdict"] ?? "") !== "PASS" && (string) ($r["verdict"] ?? "") !== "";
    }
));
$idx_last = (string) get_option("ups_gsc_indexation_last_sync", "");
$idx_color = $idx_ratio >= 90 ? "var(--success)" : ($idx_ratio >= 70 ? "var(--warn)" : "var(--danger)");

// Keyword summary
$kw_raw = (array) get_option("upsellio_keyword_metrics_rows", []);
$kw_vis_today = function_exists("upsellio_gsc_visibility_stats")
    ? upsellio_gsc_visibility_stats($kw_raw)
    : ["total" => 0, "top3" => 0, "top10" => 0];
$kw_total = (int) ($kw_vis_today["total"] ?? 0);
$kw_top3 = (int) ($kw_vis_today["top3"] ?? 0);
$kw_top10 = (int) ($kw_vis_today["top10"] ?? 0);
$rm_summary_today = function_exists("upsellio_rankmath_get_dashboard_summary") ? upsellio_rankmath_get_dashboard_summary(30) : [];
$gsc_src_label = (string) get_option("upsellio_keyword_metrics_source", "");
?>
<section class="card">
  <h3 style="margin:0 0 14px">Dziś</h3>

  <!-- AI brief -->
  <div class="crm-brief-card" style="margin-bottom:14px">
    <div class="crm-brief-badge"><i class="ti ti-sparkles" aria-hidden="true"></i>AI Podsumowanie dnia</div>
    <div class="crm-brief-title" id="crm-today-brief-text"><?php echo esc_html($today_brief); ?></div>
    <div class="crm-brief-actions">
      <button class="crm-brief-btn white" type="button" onclick="crmRefreshTodayBrief()">
        <i class="ti ti-refresh" aria-hidden="true"></i>Odśwież brief
      </button>
      <button class="crm-brief-btn ghost" type="button"
              onclick="crmAI('Co powinienem zrobić teraz na podstawie danych z dziś CRM Upsellio')">
        <i class="ti ti-list-check" aria-hidden="true"></i>Co zrobić teraz
      </button>
    </div>
  </div>

  <!-- Hot leady -->
  <h4 style="margin:0 0 8px;font-size:13px">Top 3 hot leady</h4>
  <?php
    $hot_leads = get_posts([
        "post_type" => "lead",
        "posts_per_page" => 3,
        "meta_query" => [["key" => "_upsellio_lead_score", "value" => 60, "compare" => ">="]],
        "orderby" => "meta_value_num",
        "meta_key" => "_upsellio_lead_score",
        "order" => "DESC",
    ]);
    if (empty($hot_leads)) :
        ?>
    <p class="muted" style="margin:0 0 14px">Brak leadów ze score ≥60.</p>
  <?php else : ?>
    <ul style="margin:0 0 14px;padding-left:16px">
      <?php foreach ($hot_leads as $lead) :
          $score = (int) get_post_meta((int) $lead->ID, "_upsellio_lead_score", true);
          $url = add_query_arg(["view" => "leads", "lead_id" => $lead->ID], home_url("/crm-app/"));
          ?>
      <li style="font-size:13px;margin-bottom:4px">
        <a href="<?php echo esc_url($url); ?>" style="color:var(--teal);font-weight:600">
          <?php echo esc_html(get_the_title((int) $lead->ID)); ?>
        </a>
        <span style="color:var(--text-3)"> — <?php echo esc_html((string) $score); ?>/100</span>
      </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <!-- Anomalie -->
  <h4 style="margin:0 0 8px;font-size:13px">Anomalie</h4>
  <?php if (empty($anomalies)) : ?>
    <p class="muted" style="margin:0 0 14px">Brak danych — włącz sync i poczekaj na cron.</p>
  <?php else : ?>
    <?php foreach (array_slice(array_values($anomalies), 0, 4) as $a) : ?>
      <?php if (!is_array($a)) {
          continue;
      } ?>
      <?php $pct = (float) ($a["pct"] ?? 0); ?>
      <?php $cls = $pct >= 10 ? "gr" : ($pct <= -10 ? "rd" : "am"); ?>
      <div class="crm-anomaly-row" style="margin-bottom:8px">
        <div class="crm-an-indicator <?php echo esc_attr($cls); ?>"></div>
        <div class="crm-an-body">
          <div class="crm-an-channel"><?php echo esc_html((string) ($a["channel"] ?? "")); ?></div>
          <div class="crm-an-metric"><?php echo esc_html((string) ($a["metric"] ?? "")); ?> <?php echo $pct >= 0 ? "+" : ""; ?><?php echo esc_html((string) $pct); ?>%</div>
          <div class="crm-an-why"><?php echo esc_html((string) ($a["why"] ?? "")); ?></div>
          <div class="crm-an-action"><i class="ti ti-arrow-right" aria-hidden="true"></i><?php echo esc_html((string) ($a["action"] ?? "")); ?></div>
        </div>
      </div>
    <?php endforeach; ?>
    <div style="margin-bottom:14px"></div>
  <?php endif; ?>

  <!-- Indeksacja GSC -->
  <h4 style="margin:0 0 8px;font-size:13px">Indeksacja Google (GSC)</h4>
  <?php if ($idx_submitted === 0) : ?>
    <p class="muted" style="margin:0 0 14px">Brak zagregowanych danych z Search Console (sitemap). Po pierwszym cronie lub po odświeżeniu w zakładce SEO pojawią się liczniki.</p>
  <?php else : ?>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:14px">
      <?php
        $today_idx_kpis = [
            [$idx_indexed . " / " . $idx_submitted, "Zaindeksowanych", $idx_color],
            [$idx_ratio . "%", "Wskaźnik", $idx_color],
            [$idx_problems > 0 ? "⚠ " . $idx_problems : "✓ 0", "Problemy", $idx_problems > 0 ? "var(--danger)" : "var(--success)"],
        ];
        foreach ($today_idx_kpis as $tk) :
            list($val, $lbl, $col) = $tk;
            ?>
      <div style="background:var(--bg);border-radius:var(--r-sm);padding:8px;text-align:center">
        <div style="font-size:15px;font-weight:800;color:<?php echo esc_attr($col); ?>"><?php echo esc_html($val); ?></div>
        <div style="font-size:10px;color:var(--text-3);margin-top:2px"><?php echo esc_html($lbl); ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="background:var(--border);border-radius:999px;height:6px;margin-bottom:14px">
      <div style="background:<?php echo esc_attr($idx_color); ?>;border-radius:999px;height:6px;width:<?php echo esc_attr((string) $idx_ratio); ?>%"></div>
    </div>
  <?php endif; ?>

  <?php if (!empty($rm_summary_today["clicks"])) : ?>
  <h4 style="margin:0 0 8px;font-size:13px">GSC (jak Rank Math, 30 dni)</h4>
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:14px">
    <?php
    foreach ([
        ["Kliknięcia", "clicks", false],
        ["Wyświetlenia", "impressions", false],
        ["Pozycja", "position", true],
        ["Frazy", "keywords", false],
    ] as $rmk) :
        $m = (array) ($rm_summary_today[$rmk[1]] ?? []);
        $diff = (float) ($m["difference"] ?? 0);
        $col = $diff >= 0 && !$rmk[2] ? "var(--success)" : ($diff < 0 && !$rmk[2] ? "var(--danger)" : "var(--text-main)");
        if ($rmk[2]) {
            $col = $diff <= 0 ? "var(--success)" : "var(--danger)";
        }
        ?>
    <div style="background:var(--bg);border-radius:var(--r-sm);padding:8px;text-align:center">
      <div style="font-size:10px;color:var(--text-3);text-transform:uppercase"><?php echo esc_html($rmk[0]); ?></div>
      <div style="font-size:16px;font-weight:800;"><?php echo esc_html(number_format((float) ($m["total"] ?? 0), $rmk[1] === "position" ? 1 : 0, ",", " ")); ?></div>
      <div style="font-size:10px;color:<?php echo esc_attr($col); ?>"><?php echo ($diff > 0 ? "+" : "") . esc_html((string) $diff); ?>%</div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php if ($gsc_src_label !== "") : ?>
    <p style="margin:0 0 14px;font-size:11px;color:var(--text-3)">Źródło danych: <?php echo esc_html($gsc_src_label); ?></p>
  <?php endif; ?>
  <?php endif; ?>

  <!-- Widoczność keywords -->
  <h4 style="margin:0 0 8px;font-size:13px">Widoczność GSC</h4>
  <?php if ($kw_total === 0) : ?>
    <p class="muted" style="margin:0">Brak danych keywordów — podłącz GSC OAuth lub wgraj CSV.</p>
  <?php else : ?>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px">
      <?php
        $today_kw_kpis = [
            [number_format($kw_total), "Wszystkich fraz", "var(--text-main)"],
            [number_format($kw_top3), "W TOP 3", "#15803d"],
            [number_format($kw_top10), "W TOP 10", "#0369a1"],
        ];
        foreach ($today_kw_kpis as $tk) :
            list($val, $lbl, $col) = $tk;
            ?>
      <div style="background:var(--bg);border-radius:var(--r-sm);padding:8px;text-align:center">
        <div style="font-size:15px;font-weight:800;color:<?php echo esc_attr($col); ?>"><?php echo esc_html($val); ?></div>
        <div style="font-size:10px;color:var(--text-3);margin-top:2px"><?php echo esc_html($lbl); ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <p style="margin:6px 0 0;font-size:11px;color:var(--text-3)">
      Szczegóły → <a href="<?php echo esc_url(add_query_arg(["view" => "analytics", "atab" => "seo"], home_url("/crm-app/"))); ?>" style="color:var(--teal)">zakładka SEO</a>
    </p>
  <?php endif; ?>

</section>
