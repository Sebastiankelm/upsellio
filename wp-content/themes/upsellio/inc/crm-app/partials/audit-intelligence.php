<?php

if (!defined("ABSPATH")) {
    exit;
}

/** @var array<string, mixed> $current */
/** @var int $ca_client_id */
/** @var WP_Post|null $ca_client */

$intel = (array) ($current["intelligence"] ?? []);
if ($intel === []) {
    return;
}

$executive = (array) ($intel["executive_summary"] ?? []);
$opportunity = (array) ($intel["opportunity"] ?? []);
$ads_cpa = (array) ($intel["ads_channel_cpa"] ?? []);
$st = (array) ($intel["search_terms"] ?? []);
$content = (array) ($intel["content_potential"] ?? []);
$tracking = (array) ($intel["tracking_health"] ?? []);
$alerts = (array) ($intel["alerts"] ?? []);
$roadmap = (array) ($intel["seo_roadmap"] ?? []);
$ux = (array) ($intel["ux_audit"] ?? []);
$journey = (array) ($intel["customer_journey"] ?? []);
$products = (array) ($intel["product_analytics"] ?? []);
$profit = (array) ($intel["profit"] ?? []);
$bench_intel = (array) ($intel["benchmark_intel"] ?? []);
$competition = (array) ($intel["competition"] ?? []);
$crm_revenue = (array) ($intel["crm_revenue"] ?? []);
$crm_quality = (array) ($intel["crm_quality"] ?? ($crm_revenue["quality"] ?? []));
$attr_confidence = (array) ($intel["attribution_confidence"] ?? ($current["attribution_confidence"] ?? []));
$rev_confidence = (array) ($intel["revenue_confidence"] ?? ($current["revenue_confidence"] ?? []));
$data_quality = (array) ($intel["data_quality"] ?? ($current["data_quality"] ?? []));
$health_trend = (array) ($intel["health_trend"] ?? ($current["health_trend"] ?? []));
$health_history = (array) ($health_trend["history"] ?? ($data_quality["health_history"] ?? []));
$revenue_quality = (array) ($intel["revenue_quality"] ?? ($current["revenue_quality"] ?? []));
$seo_clusters = (array) ($intel["seo_clusters"] ?? []);

$severity_colors = [
    "critical" => "var(--danger,#dc2626)",
    "warning" => "#d97706",
    "info" => "var(--muted,#64748b)",
];

$bench_fmt = static function ($val, string $fmt): string {
    if (!is_numeric($val)) {
        return "—";
    }
    $v = (float) $val;
    if ($fmt === "int") {
        return number_format($v, 0, ",", " ");
    }
    if ($fmt === "money") {
        return number_format($v, 0, ",", " ") . " zł";
    }
    if ($fmt === "pct") {
        return number_format($v, 2, ",", " ") . "%";
    }

    return number_format($v, 1, ",", " ");
};

$th_score = (int) ($tracking["score"] ?? 0);
$opp_score = (int) ($opportunity["score"] ?? 0);
$dash_health = (int) ($current["health_score"] ?? 0);
$attr_score = (int) ($attr_confidence["score"] ?? 0);
$rev_conf_score = (int) ($rev_confidence["score"] ?? 0);
$crm_q_score = (int) ($crm_quality["score"] ?? 0);
$health_delta = (int) ($health_trend["delta"] ?? 0);
$health_month_delta = (int) ($health_trend["month_delta"] ?? 0);
$health_trend_dir = (string) ($health_trend["direction"] ?? "flat");
$not_set_pct = (float) ($current["ga4_not_set_pct"] ?? 0);
$rev_warning = !empty($revenue_quality["warning"]);

$st_top_waste = ["labels" => [], "values" => []];
foreach (array_slice((array) ($st["waste"] ?? []), 0, 8) as $wrow) {
    if (!is_array($wrow)) {
        continue;
    }
    $term = (string) ($wrow["term"] ?? "");
    if ($term === "") {
        continue;
    }
    $st_top_waste["labels"][] = function_exists("mb_substr") ? mb_substr($term, 0, 32) : substr($term, 0, 32);
    $st_top_waste["values"][] = (float) ($wrow["cost"] ?? 0);
}

$crm_chart = ["labels" => [], "cost" => [], "revenue" => []];
foreach (array_slice((array) ($crm_revenue["rows"] ?? []), 0, 6) as $crow) {
    if (!is_array($crow)) {
        continue;
    }
    $crm_chart["labels"][] = (string) ($crow["channel"] ?? "");
    $crm_chart["cost"][] = (float) ($crow["cost"] ?? 0);
    $crm_chart["revenue"][] = (float) ($crow["revenue"] ?? 0);
}

$cluster_chart = ["labels" => [], "values" => [], "colors" => []];
$cluster_colors = ["#0d9488", "#2563eb", "#8b5cf6", "#d97706", "#16a34a", "#ec4899"];
foreach (array_slice($seo_clusters, 0, 6) as $ci => $cl) {
    if (!is_array($cl)) {
        continue;
    }
    $cluster_chart["labels"][] = (string) ($cl["label"] ?? "");
    $cluster_chart["values"][] = (int) ($cl["potential_clicks"] ?? 0);
    $cluster_chart["colors"][] = $cluster_colors[$ci % count($cluster_colors)];
}

$bench_chart = ["labels" => [], "client" => [], "avg" => []];
foreach (array_slice((array) ($bench_intel["comparisons"] ?? []), 0, 6) as $cmp) {
    if (!is_array($cmp) || !is_numeric($cmp["client"] ?? null)) {
        continue;
    }
    $bench_chart["labels"][] = (string) ($cmp["label"] ?? "");
    $bench_chart["client"][] = (float) ($cmp["client"] ?? 0);
    $bench_chart["avg"][] = is_numeric($cmp["benchmark"] ?? null) ? (float) $cmp["benchmark"] : 0;
}

$journey_max = 1;
foreach ((array) ($journey["stages"] ?? []) as $js) {
    if (!is_array($js)) {
        continue;
    }
    $journey_max = max($journey_max, (float) ($js["value"] ?? 0));
}

$content_chart = ["labels" => [], "values" => []];
foreach (array_slice((array) ($content["rows"] ?? []), 0, 8) as $crow) {
    if (!is_array($crow)) {
        continue;
    }
    $kw = (string) ($crow["keyword"] ?? "");
    if ($kw === "") {
        continue;
    }
    $content_chart["labels"][] = function_exists("mb_substr") ? mb_substr($kw, 0, 28) : substr($kw, 0, 28);
    $content_chart["values"][] = (int) ($crow["potential_clicks"] ?? 0);
}

$intel_charts_payload = [
    "scores" => [
        "health" => $dash_health,
        "tracking" => $th_score,
        "opportunity" => $opp_score,
        "attribution" => $attr_score,
        "revenue_confidence" => $rev_conf_score,
        "crm_quality" => $crm_q_score,
    ],
    "health_history" => [
        "labels" => array_map(static fn($h) => (string) ($h["label"] ?? $h["month"] ?? ""), $health_history),
        "values" => array_map(static fn($h) => (int) ($h["score"] ?? 0), $health_history),
    ],
    "ads_cpa" => [
        "has_data" => !empty($ads_cpa["has_data"]),
        "search_cpa" => (float) ($ads_cpa["search_cpa"] ?? 0),
        "pmax_cpa" => (float) ($ads_cpa["pmax_cpa"] ?? 0),
    ],
    "search_terms" => [
        "top_waste" => $st_top_waste,
        "actions" => [
            "scale" => count((array) ($st["scale"] ?? $st["converting"] ?? [])),
            "watch" => count((array) ($st["watch"] ?? [])),
            "exclude" => count((array) ($st["exclude_candidates"] ?? [])),
            "other" => max(0, (int) ($st["total_terms"] ?? 0) - count((array) ($st["waste"] ?? []))),
        ],
    ],
    "crm_revenue" => $crm_chart,
    "seo_clusters" => $cluster_chart,
    "benchmark" => $bench_chart,
    "attribution" => [
        "labels" => [__("Znane", "upsellio"), __("(not set)", "upsellio")],
        "values" => [
            max(0, 100 - $not_set_pct),
            $not_set_pct,
        ],
        "colors" => ["#0d9488", "#ef4444"],
    ],
    "content_potential" => $content_chart,
];
?>

<section class="card" id="ups-audit-intel-overview">
  <div class="ups-audit-section-head">
    <div>
      <h3><?php esc_html_e("Command Center — wizualizacja", "upsellio"); ?></h3>
      <?php if (!empty($executive["text"])) : ?>
        <p style="margin:6px 0 0;font-size:13px;line-height:1.5;font-weight:600;max-width:720px;"><?php echo esc_html((string) $executive["text"]); ?></p>
      <?php endif; ?>
      <?php if (!empty($ads_cpa["summary"])) : ?>
        <p class="muted" style="margin:6px 0 0;font-size:12px;"><?php echo esc_html((string) $ads_cpa["summary"]); ?></p>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($rev_warning) : ?>
  <div class="ups-audit-alert-compact" style="border-left:3px solid var(--danger,#dc2626);margin-bottom:12px;background:#fef2f2;padding:10px 12px;border-radius:8px;">
    <strong style="font-size:12px;color:#b91c1c;"><?php esc_html_e("Revenue Quality Warning", "upsellio"); ?></strong>
    <p style="margin:4px 0 0;font-size:11px;line-height:1.5;"><?php echo esc_html((string) ($revenue_quality["message"] ?? "")); ?></p>
    <?php if (!empty($revenue_quality["reasons"])) : ?>
      <ul style="margin:6px 0 0;padding-left:18px;font-size:11px;">
        <?php foreach ((array) $revenue_quality["reasons"] as $rq_reason) : ?>
          <li><?php echo esc_html((string) $rq_reason); ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <div class="ups-audit-score-row">
    <div class="ups-audit-score-card">
      <div class="ups-audit-score-card__label">Health</div>
      <div class="ups-audit-score-card__gauge">
        <canvas id="ups-intel-gauge-health"></canvas>
        <div class="ups-audit-score-card__value"><?php echo (int) $dash_health; ?></div>
      </div>
      <div class="ups-audit-score-card__sub">
        /100
        <?php if ($health_delta !== 0) : ?>
          <span style="color:<?php echo $health_delta > 0 ? "var(--ok,#16a34a)" : "var(--danger,#dc2626)"; ?>;font-weight:600;">
            <?php echo $health_delta > 0 ? "↑ +" . (int) $health_delta : "↓ " . (int) $health_delta; ?>
          </span>
        <?php endif; ?>
        <?php if ($health_month_delta !== 0) : ?>
          <div class="muted" style="font-size:10px;margin-top:2px;"><?php echo esc_html((string) ($health_trend["month_label"] ?? "")); ?></div>
        <?php endif; ?>
      </div>
    </div>
    <div class="ups-audit-score-card">
      <div class="ups-audit-score-card__label">Tracking</div>
      <div class="ups-audit-score-card__gauge">
        <canvas id="ups-intel-gauge-tracking"></canvas>
        <div class="ups-audit-score-card__value"><?php echo (int) $th_score; ?></div>
      </div>
      <div class="ups-audit-score-card__sub">/100</div>
    </div>
    <div class="ups-audit-score-card">
      <div class="ups-audit-score-card__label">Opportunity</div>
      <div class="ups-audit-score-card__gauge">
        <canvas id="ups-intel-gauge-opportunity"></canvas>
        <div class="ups-audit-score-card__value"><?php echo (int) $opp_score; ?></div>
      </div>
      <div class="ups-audit-score-card__sub"><?php echo esc_html((string) ($opportunity["label"] ?? "")); ?></div>
    </div>
    <?php if ($rev_conf_score > 0 || !empty($rev_confidence["factors"])) : ?>
    <div class="ups-audit-score-card">
      <div class="ups-audit-score-card__label"><?php esc_html_e("Revenue Confidence", "upsellio"); ?></div>
      <div class="ups-audit-score-card__gauge">
        <canvas id="ups-intel-gauge-revenue-conf"></canvas>
        <div class="ups-audit-score-card__value"><?php echo (int) $rev_conf_score; ?>%</div>
      </div>
      <div class="ups-audit-score-card__sub"><?php echo esc_html((string) ($rev_confidence["label"] ?? "")); ?></div>
    </div>
    <?php endif; ?>
    <?php if ($attr_score > 0) : ?>
    <div class="ups-audit-score-card">
      <div class="ups-audit-score-card__label"><?php esc_html_e("Attribution Confidence", "upsellio"); ?></div>
      <div class="ups-audit-score-card__gauge">
        <canvas id="ups-intel-gauge-attribution"></canvas>
        <div class="ups-audit-score-card__value"><?php echo (int) $attr_score; ?>%</div>
      </div>
      <div class="ups-audit-score-card__sub"><?php echo esc_html((string) ($attr_confidence["label"] ?? "")); ?></div>
    </div>
    <?php endif; ?>
    <?php if (!empty($crm_quality["has_data"]) || $crm_q_score > 0) : ?>
    <div class="ups-audit-score-card">
      <div class="ups-audit-score-card__label"><?php esc_html_e("CRM Quality", "upsellio"); ?></div>
      <div class="ups-audit-score-card__gauge">
        <canvas id="ups-intel-gauge-crm-quality"></canvas>
        <div class="ups-audit-score-card__value"><?php echo (int) $crm_q_score; ?></div>
      </div>
      <div class="ups-audit-score-card__sub"><?php echo esc_html((string) ($crm_quality["label"] ?? "")); ?></div>
    </div>
    <?php endif; ?>
    <?php if ($not_set_pct > 0) : ?>
    <div class="ups-audit-chart-panel" style="flex:1;min-width:200px;">
      <p class="ups-audit-chart-panel__title"><?php esc_html_e("Atrybucja GA4", "upsellio"); ?></p>
      <p class="ups-audit-chart-panel__sub"><?php echo esc_html(number_format($not_set_pct, 1, ",", " ")); ?>% (not set)</p>
      <div class="ups-crm-chart-box" style="height:100px;">
        <canvas id="ups-intel-chart-attribution"></canvas>
      </div>
    </div>
    <?php endif; ?>
    <?php if (!empty($ads_cpa["has_data"])) : ?>
    <div class="ups-audit-chart-panel" style="flex:1;min-width:200px;">
      <p class="ups-audit-chart-panel__title"><?php esc_html_e("Search vs PMax", "upsellio"); ?></p>
      <p class="ups-audit-chart-panel__sub">CPA z kampanii Ads</p>
      <div class="ups-crm-chart-box" style="height:120px;">
        <canvas id="ups-intel-chart-cpa"></canvas>
      </div>
    </div>
    <?php endif; ?>
  </div>
  <?php if ($health_history !== []) : ?>
  <div class="ups-audit-chart-panel ups-audit-chart-panel--wide" style="margin-top:12px;">
    <p class="ups-audit-chart-panel__title"><?php esc_html_e("Health — trend 6 miesięcy", "upsellio"); ?></p>
    <p class="ups-audit-chart-panel__sub"><?php esc_html_e("Snapshot przy każdym sync / odświeżeniu dashboardu", "upsellio"); ?></p>
    <div class="ups-crm-chart-box" style="height:120px;"><canvas id="ups-intel-chart-health-history"></canvas></div>
  </div>
  <?php endif; ?>
</section>

<?php if (!empty($data_quality["has_data"])) : ?>
<section class="card" id="ups-audit-intel-data-quality" style="border-left:4px solid #6366f1;">
  <div class="ups-audit-section-head">
    <div>
      <h3><?php esc_html_e("Data Quality", "upsellio"); ?></h3>
      <p class="muted" style="margin:4px 0 0;font-size:12px;"><?php echo esc_html((string) ($data_quality["summary"] ?? "")); ?></p>
    </div>
  </div>
  <div class="ups-audit-kpi-strip" style="margin-bottom:12px;">
    <?php foreach ((array) ($data_quality["items"] ?? []) as $dq_item) : ?>
      <?php if (!is_array($dq_item)) { continue; } ?>
      <?php
        $dq_band = (string) ($dq_item["band"] ?? "");
        $dq_colors = [
            "high" => "var(--ok,#16a34a)",
            "good" => "#0d9488",
            "medium" => "#d97706",
            "very_low" => "#ea580c",
            "critical" => "var(--danger,#dc2626)",
            "low" => "var(--danger,#dc2626)",
        ];
        $dq_color = $dq_colors[$dq_band] ?? "var(--muted,#64748b)";
        ?>
      <div class="ups-audit-kpi-tile">
        <div class="ups-audit-kpi-tile__lbl"><?php echo esc_html((string) ($dq_item["label"] ?? "")); ?></div>
        <div class="ups-audit-kpi-tile__val" style="color:<?php echo esc_attr($dq_color); ?>;">
          <?php echo (int) ($dq_item["score"] ?? 0); ?><?php echo esc_html((string) ($dq_item["unit"] ?? "")); ?>
        </div>
        <div class="ups-audit-kpi-tile__delta muted" style="font-size:10px;"><?php echo esc_html((string) ($dq_item["detail"] ?? "")); ?></div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php $source_ratings = (array) ($data_quality["source_ratings"] ?? []); ?>
  <?php if (!empty($source_ratings["sources"])) : ?>
  <div style="margin-bottom:12px;">
    <p class="muted" style="margin:0 0 8px;font-size:11px;font-weight:600;">
      <?php echo esc_html((string) ($source_ratings["average_label"] ?? __("Ocena źródeł (0–10)", "upsellio"))); ?>
    </p>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:8px;">
      <?php foreach ((array) $source_ratings["sources"] as $src_row) : ?>
        <?php if (!is_array($src_row)) { continue; } ?>
        <?php
          $src_score = (float) ($src_row["score"] ?? 0);
          $src_color = $src_score >= 8 ? "var(--ok,#16a34a)" : ($src_score >= 6 ? "#0d9488" : ($src_score >= 4 ? "#d97706" : "var(--danger,#dc2626)"));
          ?>
        <div style="padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:11px;">
          <div class="muted" style="font-size:10px;"><?php echo esc_html((string) ($src_row["label"] ?? "")); ?></div>
          <div style="font-weight:700;color:<?php echo esc_attr($src_color); ?>;"><?php echo esc_html(number_format($src_score, 1, ",", " ")); ?>/10</div>
          <?php if (!empty($src_row["note"])) : ?>
            <div class="muted" style="margin-top:2px;font-size:10px;"><?php echo esc_html((string) $src_row["note"]); ?></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
  <?php if (!empty($data_quality["warnings"])) : ?>
  <ul style="margin:0;padding-left:18px;font-size:11px;line-height:1.5;color:#b91c1c;">
    <?php foreach ((array) $data_quality["warnings"] as $dq_warn) : ?>
      <li><?php echo esc_html((string) $dq_warn); ?></li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
  <?php if (!empty($rev_confidence["factors"])) : ?>
  <p class="muted" style="margin:10px 0 4px;font-size:11px;font-weight:600;"><?php esc_html_e("Revenue Confidence — czynniki:", "upsellio"); ?></p>
  <ul style="margin:0;padding-left:18px;font-size:11px;">
    <?php foreach ((array) $rev_confidence["factors"] as $rf) : ?>
      <li><?php echo esc_html((string) $rf); ?></li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
</section>
<?php endif; ?>

<?php if ($alerts !== []) : ?>
<section class="card" id="ups-audit-intel-alerts">
  <div class="ups-audit-section-head">
    <h3><?php esc_html_e("Alert Engine", "upsellio"); ?></h3>
    <span class="muted" style="font-size:11px;"><?php echo count($alerts); ?> <?php esc_html_e("alertów", "upsellio"); ?></span>
  </div>
  <div class="ups-audit-viz-grid ups-audit-viz-grid--2">
    <?php foreach (array_slice($alerts, 0, 6) as $al) : ?>
      <?php if (!is_array($al)) { continue; } ?>
      <?php $sev = (string) ($al["severity"] ?? "info"); ?>
      <div class="ups-audit-alert-compact" style="border-left:3px solid <?php echo esc_attr($severity_colors[$sev] ?? $severity_colors["info"]); ?>;">
        <span style="font-size:10px;font-weight:700;text-transform:uppercase;color:<?php echo esc_attr($severity_colors[$sev] ?? ""); ?>;"><?php echo esc_html($sev); ?></span>
        <div>
          <strong style="font-size:12px;"><?php echo esc_html((string) ($al["title"] ?? "")); ?></strong>
          <p style="margin:4px 0 0;font-size:11px;"><?php echo esc_html((string) ($al["message"] ?? "")); ?></p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($st["has_data"]) || !empty($st["total_terms"])) : ?>
<section class="card" id="ups-audit-intel-search-terms">
  <div class="ups-audit-section-head">
    <div>
      <h3><?php esc_html_e("Search Terms Intelligence", "upsellio"); ?></h3>
      <p class="muted" style="margin:4px 0 0;font-size:12px;"><?php echo esc_html((string) ($st["summary"] ?? "")); ?></p>
    </div>
    <div class="ups-audit-legend-pills">
      <span class="ups-audit-legend-pill ups-audit-legend-pill--scale"><?php esc_html_e("Skaluj", "upsellio"); ?></span>
      <span class="ups-audit-legend-pill ups-audit-legend-pill--watch"><?php esc_html_e("Obserwuj", "upsellio"); ?></span>
      <span class="ups-audit-legend-pill ups-audit-legend-pill--exclude"><?php esc_html_e("Wyklucz", "upsellio"); ?></span>
    </div>
  </div>
  <div class="ups-audit-viz-grid ups-audit-viz-grid--2" style="margin-bottom:14px;">
    <div class="ups-audit-chart-panel ups-audit-chart-panel--wide">
      <p class="ups-audit-chart-panel__title"><?php esc_html_e("Top waste — koszt PLN", "upsellio"); ?></p>
      <p class="ups-audit-chart-panel__sub"><?php esc_html_e("Frazy bez konwersji", "upsellio"); ?></p>
      <div class="ups-crm-chart-box"><canvas id="ups-intel-chart-st-waste"></canvas></div>
    </div>
    <div class="ups-audit-chart-panel">
      <p class="ups-audit-chart-panel__title"><?php esc_html_e("Klasyfikacja fraz", "upsellio"); ?></p>
      <p class="ups-audit-chart-panel__sub"><?php echo (int) ($st["total_terms"] ?? 0); ?> <?php esc_html_e("fraz w raporcie", "upsellio"); ?></p>
      <div class="ups-crm-chart-box"><canvas id="ups-intel-chart-st-actions"></canvas></div>
    </div>
  </div>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px;">
    <div>
      <h4 style="margin:0 0 8px;font-size:12px;color:var(--danger,#dc2626);"><?php esc_html_e("Przepalanie budżetu (0 konw.)", "upsellio"); ?></h4>
      <table style="width:100%;font-size:11px;border-collapse:collapse;">
        <thead><tr style="background:var(--bg);"><th style="text-align:left;padding:4px 6px;">Fraza</th><th>Koszt</th><th>Klik.</th></tr></thead>
        <tbody>
        <?php foreach (array_slice((array) ($st["waste"] ?? []), 0, 8) as $row) : ?>
          <?php if (!is_array($row)) { continue; } ?>
          <tr style="border-bottom:1px solid var(--border);">
            <td style="padding:4px 6px;max-width:160px;overflow:hidden;text-overflow:ellipsis;"><?php echo esc_html((string) ($row["term"] ?? "")); ?></td>
            <td style="text-align:right;"><?php echo esc_html(number_format((float) ($row["cost"] ?? 0), 0, ",", " ")); ?></td>
            <td style="text-align:center;"><?php echo (int) ($row["clicks"] ?? 0); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div>
      <h4 style="margin:0 0 8px;font-size:12px;color:var(--ok,#16a34a);"><?php esc_html_e("Sprzedaje (konwersje)", "upsellio"); ?></h4>
      <table style="width:100%;font-size:11px;border-collapse:collapse;">
        <thead><tr style="background:var(--bg);"><th style="text-align:left;padding:4px 6px;">Fraza</th><th>Konw.</th><th>CPA</th></tr></thead>
        <tbody>
        <?php foreach (array_slice((array) ($st["converting"] ?? []), 0, 8) as $row) : ?>
          <?php if (!is_array($row)) { continue; } ?>
          <tr style="border-bottom:1px solid var(--border);">
            <td style="padding:4px 6px;"><?php echo esc_html((string) ($row["term"] ?? "")); ?></td>
            <td style="text-align:center;font-weight:600;"><?php echo esc_html(number_format((float) ($row["conversions"] ?? 0), 1, ",", " ")); ?></td>
            <td style="text-align:right;"><?php echo $row["cpa"] !== null ? esc_html(number_format((float) $row["cpa"], 0, ",", " ")) . " zł" : "—"; ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div>
      <h4 style="margin:0 0 8px;font-size:12px;color:#2563eb;"><?php esc_html_e("Obserwuj", "upsellio"); ?></h4>
      <ul style="margin:0;padding-left:18px;font-size:11px;">
        <?php foreach (array_slice((array) ($st["watch"] ?? []), 0, 6) as $row) : ?>
          <?php if (!is_array($row)) { continue; } ?>
          <li><?php echo esc_html((string) ($row["term"] ?? "")); ?>
            <span class="muted">(<?php echo esc_html(number_format((float) ($row["cost"] ?? 0), 0, ",", " ")); ?> PLN<?php if ((int) ($row["observation_days"] ?? 0) > 0) : ?> · <?php echo esc_html(sprintf(__("obserwacja %d dni", "upsellio"), (int) $row["observation_days"])); ?><?php endif; ?>)</span>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div>
      <h4 style="margin:0 0 8px;font-size:12px;color:var(--danger,#dc2626);"><?php esc_html_e("Kandydaci do wykluczenia", "upsellio"); ?></h4>
      <p class="muted" style="margin:0 0 6px;font-size:10px;">≥30 dni · ≥100 PLN · 0 konw.</p>
      <ul style="margin:0;padding-left:18px;font-size:11px;">
        <?php foreach (array_slice((array) ($st["exclude_candidates"] ?? []), 0, 6) as $row) : ?>
          <?php if (!is_array($row)) { continue; } ?>
          <li><?php echo esc_html((string) ($row["term"] ?? "")); ?>
            <span class="muted">(<?php echo esc_html(number_format((float) ($row["cost"] ?? 0), 0, ",", " ")); ?> PLN<?php if ((int) ($row["observation_days"] ?? 0) > 0) : ?> · <?php echo (int) $row["observation_days"]; ?> dni<?php endif; ?>)</span>
          </li>
        <?php endforeach; ?>
        <?php if (empty($st["exclude_candidates"])) : ?>
          <li class="muted"><?php esc_html_e("Brak twardych kandydatów do wykluczenia.", "upsellio"); ?></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</section>
<?php elseif ((int) ($audit_setup["ads"] ?? 0) > 0) : ?>
<section class="card">
  <h3 style="margin:0 0 6px;font-size:14px;"><?php esc_html_e("Search Terms", "upsellio"); ?></h3>
  <p class="muted" style="margin:0;font-size:12px;"><?php esc_html_e("Uruchom sync Ads — search terms pobierane przy każdym sync zasobu.", "upsellio"); ?></p>
</section>
<?php endif; ?>

<section class="card" id="ups-audit-intel-tracking">
  <div class="ups-audit-section-head">
    <div>
      <h3><?php esc_html_e("Tracking Health", "upsellio"); ?></h3>
      <p class="muted" style="margin:4px 0 0;font-size:12px;"><?php echo esc_html((string) ($tracking["summary"] ?? "")); ?></p>
    </div>
  </div>
  <div class="ups-audit-kpi-strip" style="margin-bottom:12px;">
    <?php foreach ((array) ($tracking["checks"] ?? []) as $chk) : ?>
      <?php if (!is_array($chk)) { continue; } ?>
      <div class="ups-audit-kpi-tile">
        <div class="ups-audit-kpi-tile__lbl"><?php echo esc_html((string) ($chk["label"] ?? "")); ?></div>
        <div class="ups-audit-kpi-tile__val" style="color:<?php echo !empty($chk["ok"]) ? "var(--ok,#16a34a)" : "var(--danger,#dc2626)"; ?>;">
          <?php echo !empty($chk["ok"]) ? "✓ OK" : "✗"; ?>
        </div>
        <div class="ups-audit-kpi-tile__delta muted"><?php echo esc_html((string) ($chk["detail"] ?? "")); ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?php if (!empty($content["rows"])) : ?>
<section class="card" id="ups-audit-intel-content">
  <div class="ups-audit-section-head">
    <div>
      <h3><?php esc_html_e("Content Potential", "upsellio"); ?></h3>
      <p class="muted" style="margin:4px 0 0;font-size:12px;"><?php echo esc_html((string) ($content["summary"] ?? "")); ?></p>
    </div>
    <button type="button" class="btn alt" style="font-size:11px;" onclick="upsAuditGenReport(<?php echo (int) $ca_client_id; ?>,'seo_roadmap')"><i class="ti ti-sparkles"></i> <?php esc_html_e("AI SEO Roadmap", "upsellio"); ?></button>
  </div>
  <?php if ($content_chart["labels"] !== []) : ?>
  <div class="ups-audit-chart-panel ups-audit-chart-panel--wide" style="margin-bottom:14px;">
    <p class="ups-audit-chart-panel__title"><?php esc_html_e("Potencjał kliknięć (+CTR)", "upsellio"); ?></p>
    <p class="ups-audit-chart-panel__sub"><?php esc_html_e("Top frazy GSC z niskim CTR", "upsellio"); ?></p>
    <div class="ups-crm-chart-box"><canvas id="ups-intel-chart-content"></canvas></div>
  </div>
  <?php endif; ?>
  <table style="width:100%;font-size:11px;border-collapse:collapse;">
    <thead><tr style="background:var(--bg);"><th style="text-align:left;padding:6px 8px;">Fraza</th><th>Wyśw.</th><th>CTR</th><th>Cel CTR</th><th>+Klik.</th><th>Akcja</th></tr></thead>
    <tbody>
    <?php foreach ((array) $content["rows"] as $row) : ?>
      <?php if (!is_array($row)) { continue; } ?>
      <tr style="border-bottom:1px solid var(--border);">
        <td style="padding:6px 8px;"><?php echo esc_html((string) ($row["keyword"] ?? "")); ?></td>
        <td style="text-align:center;"><?php echo (int) ($row["impressions"] ?? 0); ?></td>
        <td style="text-align:center;"><?php echo esc_html(number_format((float) ($row["ctr"] ?? 0), 1, ",", " ")); ?>%</td>
        <td style="text-align:center;"><?php echo esc_html(number_format((float) ($row["target_ctr"] ?? 0), 1, ",", " ")); ?>%</td>
        <td style="text-align:center;font-weight:700;color:var(--ok,#16a34a);">+<?php echo (int) ($row["potential_clicks"] ?? 0); ?></td>
        <td style="padding:6px 8px;max-width:200px;" class="muted"><?php echo esc_html((string) ($row["action"] ?? "")); ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</section>
<?php endif; ?>

<?php if (!empty($crm_revenue["has_data"]) && !empty($crm_revenue["rows"])) : ?>
<section class="card" id="ups-audit-intel-crm-revenue" style="border-left:4px solid #0d9488;">
  <div class="ups-audit-section-head">
    <div>
      <h3><?php esc_html_e("CRM Revenue Attribution", "upsellio"); ?></h3>
      <?php if (!empty($crm_revenue["summary"])) : ?>
        <p style="margin:4px 0 0;font-size:13px;font-weight:600;"><?php echo esc_html((string) $crm_revenue["summary"]); ?></p>
      <?php endif; ?>
      <?php if (!empty($crm_quality["summary"])) : ?>
        <p class="muted" style="margin:4px 0 0;font-size:12px;"><?php echo esc_html((string) $crm_quality["summary"]); ?></p>
      <?php endif; ?>
      <p class="muted" style="margin:4px 0 0;font-size:11px;"><?php echo esc_html((string) ($crm_revenue["note"] ?? "")); ?></p>
    </div>
  </div>
  <div class="ups-audit-viz-grid ups-audit-viz-grid--2" style="margin-bottom:14px;">
    <div class="ups-audit-chart-panel ups-audit-chart-panel--wide">
      <p class="ups-audit-chart-panel__title"><?php esc_html_e("Koszt vs przychód CRM", "upsellio"); ?></p>
      <p class="ups-audit-chart-panel__sub"><?php esc_html_e("Per kanał atrybucji", "upsellio"); ?></p>
      <div class="ups-crm-chart-box"><canvas id="ups-intel-chart-crm"></canvas></div>
    </div>
    <div class="ups-audit-kpi-strip" style="align-content:start;">
      <?php foreach ((array) $crm_revenue["rows"] as $row) : ?>
        <?php if (!is_array($row)) { continue; } ?>
        <div class="ups-audit-kpi-tile">
          <div class="ups-audit-kpi-tile__lbl"><?php echo esc_html((string) ($row["channel"] ?? "")); ?></div>
          <div class="ups-audit-kpi-tile__val" style="font-size:15px;"><?php echo (float) ($row["roas"] ?? 0) > 0 ? esc_html(number_format((float) $row["roas"], 2, ",", " ")) . "x ROAS" : "—"; ?></div>
          <div class="ups-audit-kpi-tile__delta muted"><?php echo (int) ($row["leads"] ?? 0); ?> lead · <?php echo (int) ($row["won"] ?? 0); ?> wygr.</div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <table style="width:100%;font-size:12px;border-collapse:collapse;">
    <thead>
      <tr style="background:var(--bg);">
        <th style="text-align:left;padding:8px;"><?php esc_html_e("Kanał", "upsellio"); ?></th>
        <th style="text-align:right;padding:8px;"><?php esc_html_e("Koszt", "upsellio"); ?></th>
        <th style="text-align:center;padding:8px;"><?php esc_html_e("Leady", "upsellio"); ?></th>
        <th style="text-align:center;padding:8px;"><?php esc_html_e("Oferty", "upsellio"); ?></th>
        <th style="text-align:center;padding:8px;"><?php esc_html_e("Wygrane", "upsellio"); ?></th>
        <th style="text-align:right;padding:8px;"><?php esc_html_e("Przychód", "upsellio"); ?></th>
        <th style="text-align:center;padding:8px;">L→O%</th>
        <th style="text-align:center;padding:8px;">O→W%</th>
        <th style="text-align:right;padding:8px;">ROAS</th>
      </tr>
    </thead>
    <tbody>
    <?php
      $quality_channels = (array) ($crm_quality["channels"] ?? []);
      $quality_by_channel = [];
      foreach ($quality_channels as $qc) {
          if (!is_array($qc)) {
              continue;
          }
          $quality_by_channel[(string) ($qc["channel"] ?? "")] = $qc;
      }
    ?>
    <?php foreach ((array) $crm_revenue["rows"] as $row) : ?>
      <?php if (!is_array($row)) { continue; } ?>
      <?php $qch = (array) ($quality_by_channel[(string) ($row["channel"] ?? "")] ?? []); ?>
      <tr style="border-bottom:1px solid var(--border);">
        <td style="padding:8px;font-weight:600;"><?php echo esc_html((string) ($row["channel"] ?? "")); ?></td>
        <td style="padding:8px;text-align:right;"><?php echo esc_html(number_format((float) ($row["cost"] ?? 0), 0, ",", " ")); ?> zł</td>
        <td style="padding:8px;text-align:center;"><?php echo (int) ($row["leads"] ?? 0); ?></td>
        <td style="padding:8px;text-align:center;"><?php echo (int) ($row["offers"] ?? 0); ?></td>
        <td style="padding:8px;text-align:center;"><?php echo (int) ($row["won"] ?? 0); ?></td>
        <td style="padding:8px;text-align:right;font-weight:700;"><?php echo esc_html(number_format((float) ($row["revenue"] ?? 0), 0, ",", " ")); ?> zł</td>
        <td style="padding:8px;text-align:center;"><?php echo isset($qch["lead_to_offer_pct"]) ? esc_html(number_format((float) $qch["lead_to_offer_pct"], 1, ",", " ")) . "%" : "—"; ?></td>
        <td style="padding:8px;text-align:center;"><?php echo isset($qch["offer_to_won_pct"]) ? esc_html(number_format((float) $qch["offer_to_won_pct"], 1, ",", " ")) . "%" : "—"; ?></td>
        <td style="padding:8px;text-align:right;"><?php echo (float) ($row["roas"] ?? 0) > 0 ? esc_html(number_format((float) $row["roas"], 2, ",", " ")) . "x" : "—"; ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</section>
<?php endif; ?>

<?php if ($roadmap !== []) : ?>
<section class="card" id="ups-audit-intel-roadmap">
  <div class="ups-audit-section-head">
    <h3><?php esc_html_e("SEO Roadmap (reguły)", "upsellio"); ?></h3>
  </div>
  <?php if ($seo_clusters !== []) : ?>
  <div class="ups-audit-viz-grid ups-audit-viz-grid--2" style="margin-bottom:14px;">
    <div class="ups-audit-chart-panel ups-audit-chart-panel--wide">
      <p class="ups-audit-chart-panel__title"><?php esc_html_e("Klastry SEO — potencjał kliknięć", "upsellio"); ?></p>
      <p class="ups-audit-chart-panel__sub"><?php echo count($seo_clusters); ?> <?php esc_html_e("klastrów", "upsellio"); ?></p>
      <div class="ups-crm-chart-box"><canvas id="ups-intel-chart-seo"></canvas></div>
    </div>
    <div style="display:flex;flex-direction:column;gap:8px;">
    <?php foreach (array_slice($seo_clusters, 0, 4) as $cluster) : ?>
      <?php if (!is_array($cluster)) { continue; } ?>
      <div style="padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:12px;">
        <strong><?php echo esc_html(sprintf(__("Cluster: %s", "upsellio"), (string) ($cluster["label"] ?? ""))); ?></strong>
        <span class="muted"> — +<?php echo (int) ($cluster["potential_clicks"] ?? 0); ?> <?php esc_html_e("klik.", "upsellio"); ?> · <?php echo count((array) ($cluster["keywords"] ?? [])); ?> <?php esc_html_e("wariantów", "upsellio"); ?></span>
        <?php if (!empty($cluster["keywords"])) : ?>
          <div class="muted" style="margin-top:4px;font-size:11px;"><?php echo esc_html(implode(" · ", array_slice((array) $cluster["keywords"], 0, 5))); ?></div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
  <ol style="margin:0;padding-left:20px;font-size:12px;display:flex;flex-direction:column;gap:8px;">
    <?php foreach ($roadmap as $task) : ?>
      <?php if (!is_array($task)) { continue; } ?>
      <li>
        <span style="font-size:10px;padding:2px 6px;background:var(--bg);border-radius:4px;margin-right:6px;"><?php echo esc_html(strtoupper((string) ($task["priority"] ?? ""))); ?></span>
        <strong><?php echo esc_html((string) ($task["title"] ?? "")); ?></strong>
        <span class="muted"> — <?php echo esc_html((string) ($task["detail"] ?? "")); ?></span>
        <?php if (!empty($task["variants"])) : ?>
          <div class="muted" style="margin-top:2px;font-size:11px;"><?php echo esc_html(implode(" · ", (array) $task["variants"])); ?></div>
        <?php endif; ?>
        <?php if (!empty($task["action"])) : ?>
          <div class="muted" style="margin-top:2px;">→ <?php echo esc_html((string) $task["action"]); ?></div>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ol>
</section>
<?php endif; ?>

<?php if (!empty($bench_intel["has_data"])) : ?>
<section class="card" id="ups-audit-intel-benchmark">
  <div class="ups-audit-section-head">
    <h3><?php esc_html_e("Benchmark portfolio", "upsellio"); ?> <span class="muted" style="font-weight:400;">(<?php echo (int) ($bench_intel["clients_in_sample"] ?? 0); ?> klientów)</span></h3>
  </div>
  <?php if ($bench_chart["labels"] !== []) : ?>
  <div class="ups-audit-chart-panel ups-audit-chart-panel--wide" style="margin-bottom:14px;">
    <p class="ups-audit-chart-panel__title"><?php esc_html_e("Klient vs średnia portfela", "upsellio"); ?></p>
    <div class="ups-crm-chart-box"><canvas id="ups-intel-chart-bench"></canvas></div>
  </div>
  <?php endif; ?>
  <table style="width:100%;font-size:12px;border-collapse:collapse;">
    <thead>
      <tr style="background:var(--bg);">
        <th style="text-align:left;padding:8px;">KPI</th>
        <th style="text-align:right;padding:8px;"><?php echo esc_html(($ca_client instanceof WP_Post) ? (string) $ca_client->post_title : __("Klient", "upsellio")); ?></th>
        <th style="text-align:right;padding:8px;">Średnia</th>
        <th style="text-align:right;padding:8px;">vs śr.</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach (array_slice((array) ($bench_intel["comparisons"] ?? []), 0, 8) as $cmp) : ?>
      <?php if (!is_array($cmp)) { continue; } ?>
      <?php
        $better = $cmp["better"] ?? null;
        $color = $better === true ? "var(--ok,#16a34a)" : ($better === false ? "var(--danger,#dc2626)" : "inherit");
        $fmt = (string) ($cmp["fmt"] ?? "decimal");
        $bench_val = $cmp["benchmark"] ?? null;
        ?>
      <tr style="border-bottom:1px solid var(--border);">
        <td style="padding:8px;"><?php echo esc_html((string) ($cmp["label"] ?? "")); ?></td>
        <td style="padding:8px;text-align:right;font-weight:700;color:<?php echo esc_attr($color); ?>;"><?php echo esc_html($bench_fmt($cmp["client"] ?? null, $fmt) . (string) ($cmp["unit"] ?? "")); ?></td>
        <td style="padding:8px;text-align:right;" class="muted"><?php echo $bench_val !== null ? esc_html($bench_fmt($bench_val, $fmt) . (string) ($cmp["unit"] ?? "")) : "—"; ?></td>
        <td style="padding:8px;text-align:right;" class="muted"><?php echo $cmp["vs_pct"] !== null ? esc_html(($cmp["vs_pct"] >= 0 ? "+" : "") . number_format((float) $cmp["vs_pct"], 0, ",", " ") . "%") : "—"; ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</section>
<?php endif; ?>

<?php if (!empty($ux["has_data"]) || !empty($ux["issues"])) : ?>
<?php
  $ux_clarity_conf = (array) ($ux["clarity_confidence"] ?? []);
  $ux_clarity_low = !empty($ux["clarity_low_confidence"]) || (string) ($ux_clarity_conf["band"] ?? "") === "low";
?>
<section class="card" id="ups-audit-intel-ux">
  <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;">
    <h3 style="margin:0;font-size:14px;"><?php esc_html_e("AI UX Audit", "upsellio"); ?>
      <span class="muted" style="font-weight:400;">(<?php echo (int) ($ux["score"] ?? 0); ?>/100<?php echo $ux_clarity_low ? ", " . esc_html__("obniżona waga", "upsellio") : ""; ?>)</span>
      <?php if ($ux_clarity_low) : ?>
        <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:4px;background:#fef2f2;color:#b91c1c;margin-left:6px;"><?php echo esc_html((string) ($ux_clarity_conf["label"] ?? __("Confidence: Low", "upsellio"))); ?></span>
      <?php endif; ?>
    </h3>
    <button type="button" class="btn alt" style="font-size:11px;" onclick="upsAuditGenReport(<?php echo (int) $ca_client_id; ?>,'ux_audit')"><i class="ti ti-sparkles"></i> <?php esc_html_e("Pełny raport AI", "upsellio"); ?></button>
  </div>
  <p class="muted" style="margin:6px 0 10px;font-size:12px;"><?php echo esc_html((string) ($ux["summary"] ?? "")); ?></p>
  <?php foreach ((array) ($ux["issues"] ?? []) as $iss) : ?>
    <?php if (!is_array($iss)) { continue; } ?>
    <div style="padding:8px 10px;border:1px solid var(--border);border-radius:8px;margin-bottom:6px;font-size:12px;">
      <strong><?php echo esc_html((string) ($iss["title"] ?? "")); ?></strong>
      <p style="margin:4px 0 0;"><?php echo esc_html((string) ($iss["detail"] ?? "")); ?></p>
      <p class="muted" style="margin:4px 0 0;">Fix: <?php echo esc_html((string) ($iss["fix"] ?? "")); ?></p>
    </div>
  <?php endforeach; ?>
</section>
<?php endif; ?>

<?php if (!empty($journey["stages"])) : ?>
<section class="card" id="ups-audit-intel-journey">
  <div class="ups-audit-section-head">
    <div>
      <h3><?php esc_html_e("Customer Journey (B2B)", "upsellio"); ?></h3>
      <?php if (!empty($journey["offline_note"])) : ?>
        <p class="muted" style="margin:4px 0 0;font-size:11px;"><?php echo esc_html((string) $journey["offline_note"]); ?></p>
      <?php endif; ?>
      <?php if (!empty($journey["note"])) : ?>
        <p class="muted" style="margin:4px 0 0;font-size:11px;"><?php echo esc_html((string) $journey["note"]); ?></p>
      <?php endif; ?>
    </div>
  </div>
  <div class="ups-audit-funnel-viz">
    <?php foreach ((array) $journey["stages"] as $i => $stage) : ?>
      <?php if (!is_array($stage)) { continue; } ?>
      <?php
        $jval = is_numeric($stage["value"] ?? null) ? (float) $stage["value"] : 0;
        $jpct = $journey_max > 0 ? min(100, round(($jval / $journey_max) * 100)) : 0;
        $jbar_cls = $i >= 4 ? "ups-audit-funnel-step__bar--crm" : ($i <= 1 ? "ups-audit-funnel-step__bar--ads" : "");
        ?>
      <div class="ups-audit-funnel-step">
        <span class="muted"><?php echo esc_html((string) ($stage["name"] ?? "")); ?></span>
        <div class="ups-audit-funnel-step__bar-wrap">
          <div class="ups-audit-funnel-step__bar <?php echo esc_attr($jbar_cls); ?>" style="width:<?php echo (int) max(4, $jpct); ?>%;"></div>
        </div>
        <div>
          <div class="ups-audit-funnel-step__val"><?php echo esc_html(is_numeric($stage["value"] ?? null) ? number_format($jval, 0, ",", " ") : (string) ($stage["value"] ?? "")); ?></div>
          <?php if ($stage["rate_to_next"] !== null) : ?>
            <div class="ups-audit-funnel-step__rate">→ <?php echo esc_html(number_format((float) $stage["rate_to_next"], 1, ",", " ")); ?><?php echo is_numeric($stage["rate_to_next"]) && (float) $stage["rate_to_next"] < 10 ? "% CR" : ""; ?></div>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($products["has_ecommerce"]) || !empty($products["events"])) : ?>
<section class="card" id="ups-audit-intel-products">
  <h3 style="margin:0 0 6px;font-size:14px;"><?php esc_html_e("Product Analytics", "upsellio"); ?></h3>
  <p class="muted" style="margin:0 0 10px;font-size:12px;"><?php echo esc_html((string) ($products["summary"] ?? "")); ?></p>
  <?php if (!empty($products["funnel"])) : ?>
  <div style="display:flex;gap:8px;flex-wrap:wrap;">
    <?php foreach ((array) $products["funnel"] as $step) : ?>
      <?php if (!is_array($step)) { continue; } ?>
      <div style="padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:11px;text-align:center;">
        <div class="muted"><?php echo esc_html((string) ($step["step"] ?? "")); ?></div>
        <div style="font-weight:800;"><?php echo (int) ($step["count"] ?? 0); ?></div>
        <div><?php echo esc_html(number_format((float) ($step["rate"] ?? 0), 1, ",", " ")); ?>%</div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>
<?php endif; ?>

<section class="card" id="ups-audit-intel-profit" style="border:1px dashed #fbbf24;background:#fffbeb;">
  <h3 style="margin:0 0 6px;font-size:14px;"><?php esc_html_e("Profit Dashboard", "upsellio"); ?> <span style="font-size:10px;padding:2px 8px;background:#fef3c7;border-radius:4px;color:#92400e;font-weight:700;">SZACUNEK E-COMMERCE</span></h3>
  <p style="margin:0 0 10px;font-size:12px;color:#92400e;font-weight:600;"><?php echo esc_html((string) ($profit["disclaimer"] ?? "Nie uwzględnia sprzedaży offline.")); ?></p>
  <?php if (!empty($profit["warning"])) : ?>
    <p style="margin:0 0 10px;font-size:12px;color:#b45309;"><?php echo esc_html((string) $profit["warning"]); ?></p>
  <?php endif; ?>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:8px;font-size:12px;">
    <div style="padding:10px;border:1px solid #fde68a;border-radius:8px;background:#fff;"><div class="muted">Przychód GA4</div><div style="font-weight:800;"><?php echo esc_html(number_format((float) ($profit["revenue"] ?? 0), 0, ",", " ")); ?> PLN</div></div>
    <div style="padding:10px;border:1px solid #fde68a;border-radius:8px;background:#fff;"><div class="muted">Marża <?php echo (int) ($profit["margin_pct"] ?? 30); ?>%</div><div style="font-weight:800;"><?php echo esc_html(number_format((float) ($profit["gross_profit"] ?? 0), 0, ",", " ")); ?> PLN</div></div>
    <div style="padding:10px;border:1px solid #fde68a;border-radius:8px;background:#fff;"><div class="muted">Koszt paid</div><div style="font-weight:800;"><?php echo esc_html(number_format((float) ($profit["ads_cost"] ?? 0), 0, ",", " ")); ?> PLN</div></div>
    <div style="padding:10px;border:1px solid #fde68a;border-radius:8px;background:#fff;"><div class="muted">Zysk netto (e-com)</div><div style="font-weight:800;color:<?php echo (float) ($profit["net_profit"] ?? 0) >= 0 ? "var(--ok,#16a34a)" : "var(--danger,#dc2626)"; ?>;"><?php echo esc_html(number_format((float) ($profit["net_profit"] ?? 0), 0, ",", " ")); ?> PLN</div></div>
  </div>
  <p class="muted" style="margin:8px 0 0;font-size:11px;"><?php echo esc_html((string) ($profit["summary"] ?? "")); ?></p>
</section>

<section class="card" id="ups-audit-intel-competition" style="opacity:0.85;">
  <h3 style="margin:0 0 6px;font-size:14px;"><?php esc_html_e("Konkurencja", "upsellio"); ?> <span class="muted" style="font-size:11px;">Premium</span></h3>
  <p class="muted" style="margin:0;font-size:12px;"><?php echo esc_html((string) ($competition["note"] ?? "")); ?></p>
  <?php if (!empty($competition["auction_insights"])) : ?>
  <table style="width:100%;font-size:11px;margin-top:10px;border-collapse:collapse;">
    <thead><tr style="background:var(--bg);"><th style="text-align:left;padding:4px 6px;">Konkurent</th><th>Share</th></tr></thead>
    <tbody>
    <?php foreach ((array) $competition["auction_insights"] as $row) : ?>
      <?php if (!is_array($row)) { continue; } ?>
      <tr><td style="padding:4px 6px;"><?php echo esc_html((string) ($row["competitor"] ?? "")); ?></td><td><?php echo esc_html(number_format((float) ($row["impression_share"] ?? 0), 1, ",", " ")); ?>%</td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</section>

<script type="application/json" id="ups-audit-intel-charts"><?php echo wp_json_encode($intel_charts_payload); ?></script>
