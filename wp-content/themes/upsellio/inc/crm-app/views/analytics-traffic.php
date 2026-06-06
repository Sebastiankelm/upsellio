<?php
if (!defined("ABSPATH")) { exit; }

$range_days = isset($_GET["range"]) ? (int) wp_unslash($_GET["range"]) : 30;
$kpi_cards  = function_exists("upsellio_analytics_kpi_cards")     ? upsellio_analytics_kpi_cards($range_days)     : [];
$charts     = function_exists("upsellio_analytics_charts_series") ? upsellio_analytics_charts_series($range_days) : [];

function _ups_tc_vals(array $s): array { return array_map(fn($p) => (float)($p[1] ?? 0), $s); }
function _ups_tc_lbls(array $s): array { return array_map(fn($p) => (string)($p[0] ?? ""), $s); }

$views_cur  = _ups_tc_vals($charts["views"]["current"]        ?? []);
$views_prev = _ups_tc_vals($charts["views"]["previous"]       ?? []);
$leads_cur  = _ups_tc_vals($charts["leads"]["current"]        ?? []);
$leads_prev = _ups_tc_vals($charts["leads"]["previous"]       ?? []);
$impr_cur   = _ups_tc_vals($charts["impressions"]["current"]  ?? []);
$impr_prev  = _ups_tc_vals($charts["impressions"]["previous"] ?? []);
$clk_cur    = _ups_tc_vals($charts["clicks"]["current"]       ?? []);
$clk_prev   = _ups_tc_vals($charts["clicks"]["previous"]      ?? []);
$labels     = _ups_tc_lbls($charts["views"]["current"]        ?? []);

$has_data = !empty($views_cur) && (array_sum($views_cur) + array_sum($impr_cur) + array_sum($leads_cur)) > 0;

// Dodatkowe statystyki
$total_views   = array_sum($views_cur);
$total_leads   = array_sum($leads_cur);
$total_impr    = array_sum($impr_cur);
$total_clicks  = array_sum($clk_cur);
$conv_rate     = $total_views > 0 ? round($total_leads / $total_views * 100, 2) : 0;
$avg_ctr       = $total_impr > 0  ? round($total_clicks / $total_impr * 100, 2) : 0;
$prev_views    = array_sum($views_prev);
$prev_leads    = array_sum($leads_prev);
$delta_views   = $prev_views > 0  ? round(($total_views - $prev_views) / $prev_views * 100, 1) : 0;
$delta_leads   = $prev_leads > 0  ? round(($total_leads - $prev_leads) / $prev_leads * 100, 1) : 0;

// Win rate i MRR z kpi_cards
$win_rate = 0; $mrr = 0; $forecast = 0; $ttc = 0;
foreach ($kpi_cards as $k) {
    if (($k["label"] ?? "") === "Win rate")          { $win_rate = (float)($k["value"] ?? 0); }
    if (($k["label"] ?? "") === "Active MRR")        { $mrr      = (float)($k["value"] ?? 0); }
    if (($k["label"] ?? "") === "Prognoza ważona")   { $forecast = (float)($k["value"] ?? 0); }
    if (($k["label"] ?? "") === "Śr. time-to-close") { $ttc      = (float)($k["value"] ?? 0); }
}
?>
<section class="card">

  <!-- ── SPRZEDAŻ KPI ────────────────────────────────── -->
  <h3 style="margin:0 0 12px;font-size:15px;font-weight:700">Sprzedaż</h3>
  <div class="crm-stat-grid" style="margin-bottom:20px">
    <?php
    $sales_kpis = [
      ["Win rate",      number_format($win_rate,1) . "%", $win_rate >= 30 ? "var(--success)" : ($win_rate > 0 ? "var(--warn)" : "var(--text-3)"), "ti-trophy"],
      ["Active MRR",   number_format($mrr,0,","," ") . " zł", $mrr > 0 ? "var(--teal)" : "var(--text-3)", "ti-cash"],
      ["Prognoza",     number_format($forecast,0,","," ") . " zł", $forecast > 0 ? "var(--success)" : "var(--text-3)", "ti-chart-line"],
      ["Time-to-close", number_format($ttc,1) . " dni", "var(--text-main)", "ti-clock"],
    ];
    foreach ($sales_kpis as [$lbl, $val, $col, $ico]): ?>
    <div class="crm-stat-card" style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r-md);padding:14px;position:relative">
      <i class="ti <?php echo esc_attr($ico); ?>" style="position:absolute;top:12px;right:12px;font-size:18px;color:var(--border)"></i>
      <div class="muted" style="font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;margin-bottom:4px"><?php echo esc_html($lbl); ?></div>
      <div style="font-size:22px;font-weight:800;color:<?php echo esc_attr($col); ?>"><?php echo esc_html($val); ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- ── RUCH KPI ───────────────────────────────────── -->
  <h3 style="margin:0 0 12px;font-size:15px;font-weight:700">Ruch organiczny</h3>
  <?php if (!$has_data): ?>
    <div style="padding:24px;text-align:center;background:var(--bg);border-radius:var(--r-md);border:1px dashed var(--border);margin-bottom:20px">
      <div style="font-size:28px;margin-bottom:8px">📊</div>
      <p class="muted" style="margin:0">Brak danych — uruchom sync GSC lub poczekaj na cron dzienny.</p>
    </div>
  <?php else: ?>

  <!-- Metryki w jednym wierszu z deltą -->
  <div style="display:grid;grid-template-columns:repeat(3,1fr) repeat(2,1fr);gap:10px;margin-bottom:20px">
    <?php
    $traffic_kpis = [
      ["Wyświetlenia",    number_format($total_views), $delta_views, "ti-eye"],
      ["Leady z ruchu",   number_format($total_leads), $delta_leads, "ti-user-check"],
      ["Konwersja",       $conv_rate . "%", null, "ti-percentage"],
      ["Impressions GSC", number_format($total_impr),  null, "ti-search"],
      ["CTR GSC",         $avg_ctr . "%",   null, "ti-cursor-text"],
    ];
    foreach ($traffic_kpis as [$lbl, $val, $delta, $ico]):
      $has_delta = $delta !== null && ($views_prev || $leads_prev);
      $up = $delta >= 0;
    ?>
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r-md);padding:12px;position:relative">
      <i class="ti <?php echo esc_attr($ico); ?>" style="position:absolute;top:10px;right:10px;font-size:16px;color:var(--border)"></i>
      <div class="muted" style="font-size:10px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;margin-bottom:4px"><?php echo esc_html($lbl); ?></div>
      <div style="font-size:20px;font-weight:800;color:var(--text-main)"><?php echo esc_html($val); ?></div>
      <?php if ($has_delta): ?>
      <div style="font-size:11px;font-weight:600;color:<?php echo $up ? "var(--success)" : "var(--danger)"; ?>;margin-top:2px">
        <?php echo ($up ? "↑ +" : "↓ ") . $delta; ?>% vs poprzedni okres
      </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Wykresy 2×2 -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px">
    <?php
    $chart_defs = [
      ["id"=>"ups-tc-v", "label"=>"Wyświetlenia strony",  "color"=>"#0d9488", "cur"=>$views_cur,  "prev"=>$views_prev,  "sum"=>$total_views],
      ["id"=>"ups-tc-l", "label"=>"Leady",                "color"=>"#2271b1", "cur"=>$leads_cur,  "prev"=>$leads_prev,  "sum"=>$total_leads],
      ["id"=>"ups-tc-i", "label"=>"Impressions GSC",      "color"=>"#8b5cf6", "cur"=>$impr_cur,   "prev"=>$impr_prev,   "sum"=>$total_impr],
      ["id"=>"ups-tc-c", "label"=>"Kliknięcia GSC",       "color"=>"#f59e0b", "cur"=>$clk_cur,    "prev"=>$clk_prev,    "sum"=>$total_clicks],
    ];
    foreach ($chart_defs as $d):
      $s = $d["sum"]; $p = array_sum($d["prev"]);
      $delta = $p > 0 ? round(($s - $p) / $p * 100, 1) : null;
      $pos = $delta !== null && $delta >= 0;
    ?>
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r-md);padding:14px">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
        <div>
          <div style="font-size:10px;font-weight:700;letter-spacing:.6px;color:var(--text-3);text-transform:uppercase"><?php echo esc_html($d["label"]); ?></div>
          <div style="font-size:20px;font-weight:800;color:var(--text-main)"><?php echo number_format($s); ?></div>
        </div>
        <?php if ($delta !== null): ?>
        <div style="font-size:11px;font-weight:700;color:<?php echo $pos ? "var(--success)" : "var(--danger)"; ?>;padding:3px 8px;background:<?php echo $pos ? "#e8f5e9" : "#fce8e8"; ?>;border-radius:999px;white-space:nowrap">
          <?php echo ($pos ? "+" : "") . $delta; ?>%
        </div>
        <?php endif; ?>
      </div>
      <div class="ups-crm-chart-box"><canvas id="<?php echo esc_attr($d["id"]); ?>"></canvas></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Insight bar -->
  <?php if ($total_views > 0 && $total_leads === 0): ?>
  <div style="background:#fff8e1;border-left:4px solid #f59e0b;border-radius:0 var(--r-sm) var(--r-sm) 0;padding:10px 14px;font-size:13px;margin-bottom:12px">
    ⚠ Masz <?php echo number_format($total_views); ?> wyświetleń ale 0 leadów — sprawdź formularz kontaktowy i CTA na stronie.
  </div>
  <?php elseif ($conv_rate > 0 && $conv_rate < 0.5): ?>
  <div style="background:#e8f5e9;border-left:4px solid var(--success);border-radius:0 var(--r-sm) var(--r-sm) 0;padding:10px 14px;font-size:13px;margin-bottom:12px">
    💡 Konwersja <?php echo $conv_rate; ?>% — dla B2B norma to 1–3%. Rozważ A/B test formularza lub landing page.
  </div>
  <?php endif; ?>

  <script>
  (function () {
    var labels = <?php echo wp_json_encode($labels); ?>;
    var charts = [
      { id: "ups-tc-v", label: "Wyświetlenia", color: "#0d9488", cur: <?php echo wp_json_encode($views_cur); ?>, prev: <?php echo wp_json_encode($views_prev); ?> },
      { id: "ups-tc-l", label: "Leady", color: "#2271b1", cur: <?php echo wp_json_encode($leads_cur); ?>, prev: <?php echo wp_json_encode($leads_prev); ?> },
      { id: "ups-tc-i", label: "Impressions GSC", color: "#8b5cf6", cur: <?php echo wp_json_encode($impr_cur); ?>, prev: <?php echo wp_json_encode($impr_prev); ?> },
      { id: "ups-tc-c", label: "Kliknięcia GSC", color: "#f59e0b", cur: <?php echo wp_json_encode($clk_cur); ?>, prev: <?php echo wp_json_encode($clk_prev); ?> },
    ];
    function init() {
      if (!window.upsCrmChart) return;
      charts.forEach(function (c) {
        window.upsCrmChart.lineCompare(c.id, labels, c.cur, c.prev, c.label, c.color);
      });
    }
    if (typeof window.upsCrmScheduleChartInit === "function") {
      window.upsCrmScheduleChartInit(init);
    } else if (window.upsCrmChart) {
      window.upsCrmChart.whenReady(init);
    } else {
      document.addEventListener("DOMContentLoaded", function () {
        var n = 0;
        var t = setInterval(function () {
          n++;
          if (typeof window.upsCrmScheduleChartInit === "function") {
            clearInterval(t);
            window.upsCrmScheduleChartInit(init);
          } else if (window.upsCrmChart) {
            clearInterval(t);
            window.upsCrmChart.whenReady(init);
          } else if (n > 100) {
            clearInterval(t);
          }
        }, 40);
      });
    }
  })();
  </script>

  <?php endif; // has_data ?>
</section>
