<?php

if (!defined("ABSPATH")) {
    exit;
}

$range_days = isset($_GET["range"]) ? (int) wp_unslash($_GET["range"]) : 30;
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
$labels = _ups_mkt_lbls((array) (($charts["views"]["current"] ?? [])));
$health = (int) ($m["health_score"] ?? 0);
?>
<section class="card">
  <h3 style="margin:0 0 8px;">Marketing 360° — Upsellio.pl</h3>
  <p class="muted" style="margin:0 0 12px;font-size:13px;">
    Połączone dane: <strong>GA4</strong> (<?php echo esc_html((string) ($sources["ga4"] ?? "—")); ?>),
    <strong>GSC</strong> (<?php echo esc_html((string) ($sources["gsc"] ?? "—")); ?>),
    <strong>Ads</strong> (<?php echo esc_html((string) ($sources["ads"] ?? "—")); ?>).
    Health score: <strong style="color:<?php echo $health >= 75 ? "var(--success)" : ($health >= 50 ? "var(--warn)" : "var(--danger)"); ?>"><?php echo (int) $health; ?>/100</strong>
  </p>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:10px;">
    <?php
    $kpis = [
        ["Wyświetlenia / sesje", (int) ($m["ga4_sessions"] ?? 0), "ga4_sessions"],
        ["Leady / konwersje", (int) ($m["ga4_conversions"] ?? 0), "ga4_conversions"],
        ["CR%", (float) ($derived["ga4_conversion_rate"] ?? 0), ""],
        ["GSC klik.", (int) ($m["gsc_clicks"] ?? 0), "gsc_clicks"],
        ["GSC CTR", (float) ($derived["gsc_ctr"] ?? 0), ""],
        ["Ads koszt", (float) ($m["ads_cost"] ?? 0), "ads_cost"],
        ["ROAS", (float) ($m["roas"] ?? 0), "roas"],
    ];
    foreach ($kpis as $k) :
        $dkey = $k[2];
        $d = $dkey !== "" ? (float) ($deltas[$dkey] ?? 0) : null;
        $val = is_float($k[1]) && $k[0] !== "GSC klik." && strpos($k[0], "koszt") === false && $k[0] !== "Leady / konwersje" && strpos($k[0], "Wyśw") === false
            ? number_format($k[1], 2, ",", " ") . (strpos($k[0], "%") !== false || $k[0] === "CR%" || $k[0] === "GSC CTR" || $k[0] === "ROAS" ? (strpos($k[0], "ROAS") !== false ? "x" : "%") : "")
            : (strpos($k[0], "koszt") !== false
                ? number_format($k[1], 0, ",", " ") . " PLN"
                : number_format((int) $k[1], 0, ",", " "));
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

<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin-bottom:14px">
  <section class="card">
    <h4 style="margin:0 0 8px;font-size:13px;">Ruch na stronie</h4>
    <div class="ups-crm-chart-box"><canvas id="ups-mkt-chart-views"></canvas></div>
  </section>
  <section class="card">
    <h4 style="margin:0 0 8px;font-size:13px;">Leady</h4>
    <div class="ups-crm-chart-box"><canvas id="ups-mkt-chart-leads"></canvas></div>
  </section>
  <section class="card">
    <h4 style="margin:0 0 8px;font-size:13px;">GSC — wyświetlenia</h4>
    <div class="ups-crm-chart-box"><canvas id="ups-mkt-chart-impr"></canvas></div>
  </section>
  <section class="card">
    <h4 style="margin:0 0 8px;font-size:13px;">GSC — kliknięcia</h4>
    <div class="ups-crm-chart-box"><canvas id="ups-mkt-chart-clicks"></canvas></div>
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

<script type="application/json" id="ups-mkt-labels"><?php echo wp_json_encode($labels); ?></script>
<script type="application/json" id="ups-mkt-views"><?php echo wp_json_encode($views_cur); ?></script>
<script type="application/json" id="ups-mkt-leads"><?php echo wp_json_encode($leads_cur); ?></script>
<script type="application/json" id="ups-mkt-impr"><?php echo wp_json_encode($impr_cur); ?></script>
<script type="application/json" id="ups-mkt-clicks"><?php echo wp_json_encode($clk_cur); ?></script>
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
      ["ups-mkt-chart-views", parse("ups-mkt-views"), "Wyświetlenia", "#0d9488"],
      ["ups-mkt-chart-leads", parse("ups-mkt-leads"), "Leady", "#2271b1"],
      ["ups-mkt-chart-impr", parse("ups-mkt-impr"), "Impressions GSC", "#8b5cf6"],
      ["ups-mkt-chart-clicks", parse("ups-mkt-clicks"), "Kliknięcia GSC", "#f59e0b"],
    ];
    specs.forEach(function (s) {
      window.upsCrmChart.line(s[0], labels, [{ label: s[2], data: s[1], color: s[3], fill: true }], { legend: false });
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
