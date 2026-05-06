<?php
if (!defined("ABSPATH")) {
    exit;
}
$range_days  = isset($_GET["range"]) ? (int) wp_unslash($_GET["range"]) : 30;
$campaigns   = (array) get_option("ups_ads_campaigns_data", []);
$ai_review   = get_option("ups_ai_ads_review", []);
$channel_ltv = function_exists("upsellio_analytics_channel_ltv")
    ? upsellio_analytics_channel_ltv($range_days)
    : ["rows" => []];

$ga4_last = (string) get_option("ups_automation_ga4_last_sync", "");
?>
<section class="card">
  <h3 style="margin:0 0 16px">Płatne</h3>

  <?php if (!empty($ai_review) && is_array($ai_review) && !empty($ai_review["summary"])) : ?>
  <div style="padding:12px 16px;background:#fff7ed;border-left:4px solid #f97316;border-radius:0 var(--r-sm) var(--r-sm) 0;margin-bottom:14px;font-size:13px">
    <?php echo esc_html((string) ($ai_review["summary"] ?? "")); ?>
  </div>
  <?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px">
    <div style="background:var(--bg);border-radius:var(--r-sm);padding:10px">
      <div style="font-size:10px;font-weight:700;letter-spacing:.6px;color:var(--text-3);text-transform:uppercase">Kampanie z API</div>
      <div style="font-size:20px;font-weight:800;color:var(--text-main)"><?php echo count($campaigns); ?></div>
    </div>
    <div style="background:var(--bg);border-radius:var(--r-sm);padding:10px">
      <div style="font-size:10px;font-weight:700;letter-spacing:.6px;color:var(--text-3);text-transform:uppercase">Ostatni sync GA4</div>
      <div style="font-size:13px;font-weight:600;color:var(--text-main);margin-top:4px"><?php echo esc_html($ga4_last !== "" ? $ga4_last : "Brak"); ?></div>
    </div>
  </div>

  <?php if (empty($channel_ltv["rows"])) : ?>
    <div style="padding:24px;text-align:center;background:var(--bg);border-radius:var(--r-md);border:1px dashed var(--border)">
      <p class="muted" style="margin:0">Brak danych — włącz sync GA4 i poczekaj na cron.</p>
    </div>
  <?php else : ?>
  <h4 style="margin:0 0 8px;font-size:13px">Kanały — źródła leadów</h4>
  <div style="overflow-x:auto">
  <table style="width:100%;border-collapse:collapse;font-size:13px">
    <thead>
      <tr style="background:var(--bg);border-bottom:2px solid var(--border)">
        <th style="text-align:left;padding:7px 10px">Source/Medium</th>
        <th style="text-align:right;padding:7px 8px">Sessions</th>
        <th style="text-align:right;padding:7px 8px">Leady</th>
        <th style="text-align:right;padding:7px 8px">Wygrane</th>
        <th style="text-align:right;padding:7px 8px">CR%</th>
        <th style="text-align:right;padding:7px 8px">LTV/sesja</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ((array) ($channel_ltv["rows"] ?? []) as $r) :
        $cr    = (float) ($r["cr"] ?? 0);
        $ltv   = (float) ($r["ltv_per_session"] ?? 0);
        $leads = (int) ($r["leads"] ?? 0);
        ?>
    <tr style="border-bottom:1px solid var(--border)">
      <td style="padding:6px 10px;font-weight:<?php echo $leads > 0 ? "600" : "400"; ?>"><?php echo esc_html((string) ($r["channel"] ?? "")); ?></td>
      <td style="text-align:right;padding:6px 8px;color:var(--text-3)"><?php echo esc_html(number_format((int) ($r["sessions"] ?? 0))); ?></td>
      <td style="text-align:right;padding:6px 8px;font-weight:<?php echo $leads > 0 ? "700" : "400"; ?>;color:<?php echo $leads > 0 ? "var(--teal)" : "inherit"; ?>"><?php echo esc_html((string) $leads); ?></td>
      <td style="text-align:right;padding:6px 8px"><?php echo esc_html((string) ((int) ($r["won"] ?? 0))); ?></td>
      <td style="text-align:right;padding:6px 8px;color:var(--text-3)"><?php echo esc_html(number_format($cr, 1)); ?>%</td>
      <td style="text-align:right;padding:6px 8px;font-weight:600"><?php echo esc_html(number_format($ltv, 2, ",", " ")); ?> zł</td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?php endif; ?>
</section>
