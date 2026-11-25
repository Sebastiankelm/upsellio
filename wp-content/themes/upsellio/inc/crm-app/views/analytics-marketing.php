<?php

if (!defined("ABSPATH")) {
    exit;
}

$range_days = isset($range_days) && in_array($range_days, [7, 14, 30, 60, 90, 365], true) ? $range_days : 30;
$m = function_exists("upsellio_agency_marketing_metrics")
    ? upsellio_agency_marketing_metrics($range_days)
    : [];
$charts = (array) ($m["charts"] ?? []);
$deltas = (array) ($m["deltas"] ?? []);
$derived = (array) ($m["derived"] ?? []);
$recs = (array) ($m["recommendations"] ?? []);
$sources = (array) ($m["sources"] ?? []);

function _ups_mkt_pairs(array $series): array
{
    return array_map(static fn($p) => (float) ($p[1] ?? 0), $series);
}

function _ups_mkt_lbls(array $series): array
{
    return array_map(static fn($p) => (string) ($p[0] ?? ""), $series);
}

$views_cur = _ups_mkt_pairs((array) (($charts["views"]["current"] ?? [])));
$leads_cur = _ups_mkt_pairs((array) (($charts["leads"]["current"] ?? [])));
$impr_cur = _ups_mkt_pairs((array) (($charts["impressions"]["current"] ?? [])));
$clk_cur = _ups_mkt_pairs((array) (($charts["clicks"]["current"] ?? [])));
$labels = [];
foreach (["views", "leads", "impressions", "clicks"] as $metric) {
    $labels[$metric] = _ups_mkt_lbls((array) ($charts[$metric]["current"] ?? []));
}
$health = (int) ($m["health_score"] ?? 0);
?>
<section class="card">
  <h3 style="margin:0 0 8px;">Marketing 360° — Upsellio.pl</h3>
  <p class="muted" style="margin:0 0 12px;font-size:13px;">
    Połączone dane: <strong>GA4</strong> (<?php echo esc_html((string) ($sources["ga4"] ?? "—")); ?>),
    <strong>GSC</strong> (<?php echo esc_html((string) ($sources["gsc"] ?? "—")); ?>),
    <strong>Ads</strong> (<?php echo esc_html((string) ($sources["ads"] ?? "—")); ?>).
    Health score: <strong style="color:<?php echo $health >= 75 ? "var(--success)" : ($health >= 50 ? "var(--warn)" : "var(--danger)"); ?>"><?php echo isset($m["health_score"]) && is_numeric($m["health_score"]) ? (int) $health . "/100" : "—"; ?></strong>
  </p>
  <?php if (!$m) : ?><p class="muted">Brak dostępnych danych z integracji marketingowej. Nie oznacza to zerowej aktywności.</p><?php endif; ?>
  <div class="crm-report-kpis">
    <?php
    $kpis = [
        ["Sesje GA4", $m["ga4_sessions"] ?? null, "ga4_sessions", 0, ""],
        ["Konwersje GA4", $m["ga4_conversions"] ?? null, "ga4_conversions", 2, ""],
        ["CR%", $derived["ga4_conversion_rate"] ?? null, "", 2, "%"],
        ["GSC klik.", $m["gsc_clicks"] ?? null, "gsc_clicks", 0, ""],
        ["GSC CTR", $derived["gsc_ctr"] ?? null, "", 2, "%"],
        ["Ads koszt", $m["ads_cost"] ?? null, "ads_cost", 2, " PLN"],
        ["ROAS", is_numeric($m["ads_cost"] ?? null) && (float) $m["ads_cost"] > 0 ? ($m["roas"] ?? null) : null, "roas", 2, "x"],
    ];
    foreach ($kpis as $k) :
        $dkey = $k[2];
        $d = is_numeric($k[1]) && $dkey !== "" && isset($deltas[$dkey]) && is_numeric($deltas[$dkey]) ? (float) $deltas[$dkey] : null;
        $val = is_numeric($k[1]) ? number_format((float) $k[1], $k[3], ",", " ") . $k[4] : "—";
        ?>
      <div style="padding:12px;border:1px solid var(--border);border-radius:var(--r-md);">
        <div class="muted" style="font-size:10px;font-weight:700;text-transform:uppercase;"><?php echo esc_html($k[0]); ?></div>
        <div style="font-size:20px;font-weight:800;"><?php echo esc_html($val); ?></div>
        <?php if ($d !== null && function_exists("ups_audit_format_delta")) : ?>
          <div style="font-size:11px;"><?php echo esc_html(ups_audit_format_delta($d)); ?></div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?php
$ups_audit_recommendations = $recs;
require __DIR__ . "/partials/audit-recommendations.php";
?>

<div class="crm-report-charts">
  <section class="card">
    <h4 style="margin:0 0 8px;font-size:13px;">Ruch na stronie</h4>
    <?php if ($views_cur !== []) : ?><div class="ups-crm-chart-box"><canvas id="ups-mkt-chart-views" aria-label="Wykres views"></canvas></div><?php else : ?><p class="muted">Brak serii danych w tym okresie.</p><?php endif; ?>
  </section>
  <section class="card">
    <h4 style="margin:0 0 8px;font-size:13px;">Leady</h4>
    <?php if ($leads_cur !== []) : ?><div class="ups-crm-chart-box"><canvas id="ups-mkt-chart-leads" aria-label="Wykres leads"></canvas></div><?php else : ?><p class="muted">Brak serii danych w tym okresie.</p><?php endif; ?>
  </section>
  <section class="card">
    <h4 style="margin:0 0 8px;font-size:13px;">GSC — wyświetlenia</h4>
    <?php if ($impr_cur !== []) : ?><div class="ups-crm-chart-box"><canvas id="ups-mkt-chart-impr" aria-label="Wykres impr"></canvas></div><?php else : ?><p class="muted">Brak serii danych w tym okresie.</p><?php endif; ?>
  </section>
  <section class="card">
    <h4 style="margin:0 0 8px;font-size:13px;">GSC — kliknięcia</h4>
    <?php if ($clk_cur !== []) : ?><div class="ups-crm-chart-box"><canvas id="ups-mkt-chart-clicks" aria-label="Wykres clicks"></canvas></div><?php else : ?><p class="muted">Brak serii danych w tym okresie.</p><?php endif; ?>
  </section>
</div>

<?php if (!empty($m["campaigns"])) : ?>
<section class="card">
  <h4 style="margin:0 0 8px;font-size:13px;">Kampanie Google Ads (cache)</h4>
  <table style="width:100%;font-size:13px;">
    <thead><tr><th>Kampania</th><th style="text-align:right">Koszt</th><th style="text-align:right">Klik.</th></tr></thead>
    <tbody>
    <?php foreach ((array) $m["campaigns"] as $c) : ?>
      <?php if (!is_array($c)) { continue; } ?>
      <tr>
        <td><?php echo esc_html((string) ($c["name"] ?? "")); ?></td>
        <td style="text-align:right"><?php echo esc_html(number_format((float) ($c["cost_pln"] ?? $c["cost"] ?? 0), 0, ",", " ")); ?></td>
        <td style="text-align:right"><?php echo (int) ($c["clicks"] ?? 0); ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</section>
<?php endif; ?>

<script type="application/json" id="ups-mkt-labels"><?php echo wp_json_encode($labels, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
<script type="application/json" id="ups-mkt-views"><?php echo wp_json_encode($views_cur, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
<script type="application/json" id="ups-mkt-leads"><?php echo wp_json_encode($leads_cur, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
<script type="application/json" id="ups-mkt-impr"><?php echo wp_json_encode($impr_cur, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
<script type="application/json" id="ups-mkt-clicks"><?php echo wp_json_encode($clk_cur, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
<script>
(function () {
  function parse(id) {
    var el = document.getElementById(id);
    if (!el) return [];
    try { return JSON.parse(el.textContent); } catch (e) { return []; }
  }
  function init() {
    if (!window.upsCrmChart) return;
    var labels = parse("ups-mkt-labels");
    var specs = [
      ["ups-mkt-chart-views", labels.views, parse("ups-mkt-views"), "Wyświetlenia", "#0d9488"],
      ["ups-mkt-chart-leads", labels.leads, parse("ups-mkt-leads"), "Leady", "#2271b1"],
      ["ups-mkt-chart-impr", labels.impressions, parse("ups-mkt-impr"), "Impressions GSC", "#8b5cf6"],
      ["ups-mkt-chart-clicks", labels.clicks, parse("ups-mkt-clicks"), "Kliknięcia GSC", "#f59e0b"],
    ];
    specs.forEach(function (s) {
      if (!document.getElementById(s[0]) || !Array.isArray(s[1]) || !s[1].length || s[1].length !== s[2].length) return;
      window.upsCrmChart.line(s[0], s[1], [{ label: s[3], data: s[2], color: s[4], fill: true }], { legend: false });
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
        n += 1;
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
