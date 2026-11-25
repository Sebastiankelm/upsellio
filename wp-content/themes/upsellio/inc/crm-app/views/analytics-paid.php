<?php
if (!defined("ABSPATH")) {
    exit;
}
$range_days = isset($range_days) && in_array($range_days, [7, 14, 30, 60, 90, 365], true) ? $range_days : 30;
$campaigns_raw = get_option("ups_ads_campaigns_data", null);
$campaigns = is_array($campaigns_raw) ? array_values(array_filter($campaigns_raw, "is_array")) : [];
$fmt_paid = static fn($n, $d = 0, $suffix = "") => is_numeric($n) ? number_format((float) $n, $d, ",", " ") . $suffix : "—";
$ai_review   = get_option("ups_ai_ads_review", []);
$channel_ltv = function_exists("upsellio_analytics_channel_ltv")
    ? upsellio_analytics_channel_ltv($range_days)
    : ["rows" => []];

$ga4_last = (string) get_option("ups_automation_ga4_last_sync", "");
?>
<section class="card">
  <h3 style="margin:0 0 8px">Reklamy</h3>
  <p class="muted">Kampanie z ostatniej synchronizacji Google Ads; zakres cache może różnić się od wybranego okresu analizy CRM.</p>

  <?php if (!empty($ai_review) && is_array($ai_review) && !empty($ai_review["summary"])) : ?>
  <div style="padding:12px 16px;background:#fff7ed;border-left:4px solid #f97316;border-radius:0 var(--r-sm) var(--r-sm) 0;margin-bottom:14px;font-size:13px">
    <?php echo esc_html((string) ($ai_review["summary"] ?? "")); ?>
  </div>
  <?php endif; ?>

  <div class="crm-report-kpis">
    <div style="background:var(--bg);border-radius:var(--r-sm);padding:10px">
      <div style="font-size:10px;font-weight:700;letter-spacing:.6px;color:var(--text-3);text-transform:uppercase">Kampanie z API</div>
      <div style="font-size:20px;font-weight:800;color:var(--text-main)"><?php echo esc_html(is_array($campaigns_raw) ? (string) count($campaigns) : "—"); ?></div>
    </div>
    <div style="background:var(--bg);border-radius:var(--r-sm);padding:10px">
      <div style="font-size:10px;font-weight:700;letter-spacing:.6px;color:var(--text-3);text-transform:uppercase">Ostatni sync GA4</div>
      <div style="font-size:13px;font-weight:600;color:var(--text-main);margin-top:4px"><?php echo esc_html($ga4_last !== "" ? $ga4_last : "Brak"); ?></div>
    </div>
  </div>

  <?php if ($campaigns) : ?>
  <h4>Zapisane kampanie Google Ads</h4>
  <div style="overflow-x:auto"><table><thead><tr><th>Kampania</th><th>Koszt (PLN)</th><th>Kliknięcia</th><th>Wyświetlenia</th><th>Konwersje</th></tr></thead><tbody>
  <?php foreach ($campaigns as $campaign) : ?>
  <tr><td><?php echo esc_html((string) ($campaign["name"] ?? "—")); ?></td><td><?php echo esc_html($fmt_paid($campaign["cost_pln"] ?? null, 2, " zł")); ?></td><td><?php echo esc_html($fmt_paid($campaign["clicks"] ?? null)); ?></td><td><?php echo esc_html($fmt_paid($campaign["impressions"] ?? null)); ?></td><td><?php echo esc_html($fmt_paid($campaign["conversions"] ?? null, 2)); ?></td></tr>
  <?php endforeach; ?>
  </tbody></table></div>
  <?php else : ?><p class="muted">Brak zapisanych kampanii w cache. Nie oznacza to braku aktywnych kampanii na koncie reklamowym.</p><?php endif; ?>
  <?php if (empty($channel_ltv["rows"])) : ?>
    <div style="padding:24px;text-align:center;background:var(--bg);border-radius:var(--r-md);border:1px dashed var(--border)">
      <p class="muted" style="margin:0">Brak dostępnych danych atrybucji kanałów CRM i GA4 w wybranym okresie.</p>
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
        if (!is_array($r)) { continue; }
        $sessions = $r["sessions"] ?? null;
        $has_sessions = is_numeric($sessions) && (float) $sessions > 0;
        $cr = $has_sessions ? ($r["cr"] ?? null) : null;
        $ltv = $has_sessions ? ($r["ltv_per_session"] ?? null) : null;
        $leads = (int) ($r["leads"] ?? 0);
        ?>
    <tr style="border-bottom:1px solid var(--border)">
      <td style="padding:6px 10px;font-weight:<?php echo $leads > 0 ? "600" : "400"; ?>"><?php echo esc_html((string) ($r["channel"] ?? "")); ?></td>
      <td style="text-align:right;padding:6px 8px;color:var(--text-3)"><?php echo esc_html($fmt_paid($sessions)); ?></td>
      <td style="text-align:right;padding:6px 8px;font-weight:<?php echo $leads > 0 ? "700" : "400"; ?>;color:<?php echo $leads > 0 ? "var(--teal)" : "inherit"; ?>"><?php echo esc_html((string) $leads); ?></td>
      <td style="text-align:right;padding:6px 8px"><?php echo esc_html((string) ((int) ($r["won"] ?? 0))); ?></td>
      <td style="text-align:right;padding:6px 8px;color:var(--text-3)"><?php echo esc_html($fmt_paid($cr, 1, "%")); ?></td>
      <td style="text-align:right;padding:6px 8px;font-weight:600"><?php echo esc_html($fmt_paid($ltv, 2, " zł")); ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?php endif; ?>
</section>
