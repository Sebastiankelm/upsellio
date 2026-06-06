<?php

if (!defined("ABSPATH")) {
    exit;
}

$cc_window = isset($_GET["window"]) ? (int) wp_unslash($_GET["window"]) : (int) get_option("ups_audit_default_compare_window", 30);
$cc_window = in_array($cc_window, [7, 14, 30, 60, 90], true) ? $cc_window : 30;
$cc_data = function_exists("ups_audit_build_command_center")
    ? ups_audit_build_command_center($cc_window)
    : ["clients" => [], "top_alerts" => [], "summary" => []];
$cc_clients = (array) ($cc_data["clients"] ?? []);
$cc_alerts = (array) ($cc_data["top_alerts"] ?? []);
$cc_summary = (array) ($cc_data["summary"] ?? []);
$status_icons = ["green" => "🟢", "yellow" => "🟡", "red" => "🔴"];
$crm_base = function_exists("upsellio_crm_url") ? upsellio_crm_url("ca-command-center") : home_url("/crm-app/?view=ca-command-center");

?>
<section class="card">
  <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
    <div>
      <h2 style="margin:0;"><?php esc_html_e("Agency Command Center", "upsellio"); ?></h2>
      <p class="muted" style="margin:8px 0 0;max-width:640px;"><?php esc_html_e("Poranny przegląd portfolio: status klientów, alerty, co wymaga uwagi.", "upsellio"); ?></p>
    </div>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
      <select id="ups-cc-window" class="input" style="min-width:120px;" onchange="location.href='<?php echo esc_js(add_query_arg("window", "__W__", $crm_base)); ?>'.replace('__W__', this.value)">
        <?php foreach ([7, 14, 30, 60, 90] as $w) : ?>
          <option value="<?php echo (int) $w; ?>" <?php selected($cc_window, $w); ?>><?php echo (int) $w; ?> dni</option>
        <?php endforeach; ?>
      </select>
      <button type="button" class="btn alt" id="ups-audit-sync-all-btn"><i class="ti ti-refresh"></i> <?php esc_html_e("Sync wszystkich", "upsellio"); ?></button>
      <a class="btn" href="<?php echo esc_url(function_exists("upsellio_crm_url") ? upsellio_crm_url("ca-clients") : home_url("/crm-app/?view=ca-clients")); ?>"><?php esc_html_e("Profile klientów", "upsellio"); ?></a>
    </div>
  </div>
  <div style="display:flex;gap:16px;margin-top:14px;flex-wrap:wrap;font-size:13px;">
    <span><?php esc_html_e("Klienci:", "upsellio"); ?> <strong><?php echo (int) ($cc_summary["total"] ?? 0); ?></strong></span>
    <span><?php echo esc_html($status_icons["green"] ?? ""); ?> <?php echo (int) ($cc_summary["green"] ?? 0); ?></span>
    <span><?php echo esc_html($status_icons["yellow"] ?? ""); ?> <?php echo (int) ($cc_summary["yellow"] ?? 0); ?></span>
    <span><?php echo esc_html($status_icons["red"] ?? ""); ?> <?php echo (int) ($cc_summary["red"] ?? 0); ?></span>
  </div>
</section>

<?php if ($cc_alerts !== []) : ?>
<section class="card">
  <h3 style="margin:0 0 10px;font-size:14px;"><?php esc_html_e("Wymaga uwagi (top alerty)", "upsellio"); ?></h3>
  <div style="display:flex;flex-direction:column;gap:6px;">
    <?php foreach ($cc_alerts as $al) : ?>
      <?php if (!is_array($al)) { continue; } ?>
      <div style="display:flex;gap:10px;align-items:flex-start;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;flex-wrap:wrap;">
        <span><?php echo esc_html($status_icons[(string) (($al["severity"] ?? "") === "critical" ? "red" : "yellow")] ?? "🟡"); ?></span>
        <strong style="min-width:100px;"><?php echo esc_html((string) ($al["client_name"] ?? "")); ?></strong>
        <span><?php echo esc_html((string) ($al["title"] ?? "")); ?> — <?php echo esc_html((string) ($al["message"] ?? "")); ?></span>
        <?php if (!empty($al["client_id"])) : ?>
          <a href="<?php echo esc_url(function_exists("upsellio_crm_url") ? upsellio_crm_url("ca-dashboard", ["cid" => (int) $al["client_id"]]) : "#"); ?>" style="margin-left:auto;font-size:11px;"><?php esc_html_e("Dashboard →", "upsellio"); ?></a>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="card" style="overflow-x:auto;">
  <?php if ($cc_clients === []) : ?>
    <p class="muted"><?php esc_html_e("Brak profili audytu. Utwórz je w Profile klientów.", "upsellio"); ?></p>
  <?php else : ?>
  <table style="width:100%;min-width:900px;font-size:12px;border-collapse:collapse;">
    <thead>
      <tr style="background:var(--bg);">
        <th style="text-align:left;padding:8px;"><?php esc_html_e("Status", "upsellio"); ?></th>
        <th style="text-align:left;padding:8px;"><?php esc_html_e("Klient", "upsellio"); ?></th>
        <th>Health</th>
        <th><?php esc_html_e("Alerty", "upsellio"); ?></th>
        <th>GA4</th>
        <th>GSC</th>
        <th>Ads PLN</th>
        <th style="text-align:left;padding:8px;"><?php esc_html_e("Uwagi", "upsellio"); ?></th>
        <th></th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($cc_clients as $row) : ?>
      <?php if (!is_array($row)) { continue; } ?>
      <?php
        $st = (string) ($row["status"] ?? "green");
        $deltas = (array) ($row["deltas"] ?? []);
        ?>
      <tr style="border-bottom:1px solid var(--border);">
        <td style="padding:8px;font-size:16px;"><?php echo esc_html($status_icons[$st] ?? "⚪"); ?></td>
        <td style="padding:8px;font-weight:700;"><?php echo esc_html((string) ($row["title"] ?? "")); ?></td>
        <td style="text-align:center;"><?php echo (int) ($row["health_score"] ?? 0); ?></td>
        <td style="text-align:center;font-weight:600;color:<?php echo (int) ($row["alert_count"] ?? 0) > 0 ? "var(--danger,#dc2626)" : "inherit"; ?>;"><?php echo (int) ($row["alert_count"] ?? 0); ?></td>
        <td style="text-align:center;"><?php echo (int) ($row["ga4_sessions"] ?? 0); ?>
          <?php if (isset($deltas["ga4_sessions"])) : ?><div class="muted" style="font-size:10px;"><?php echo esc_html(ups_audit_format_delta((float) $deltas["ga4_sessions"])); ?></div><?php endif; ?>
        </td>
        <td style="text-align:center;"><?php echo (int) ($row["gsc_clicks"] ?? 0); ?></td>
        <td style="text-align:center;"><?php echo esc_html(number_format((float) ($row["ads_cost"] ?? 0), 0, ",", " ")); ?></td>
        <td style="padding:8px;max-width:220px;" class="muted"><?php echo esc_html(implode(" · ", (array) ($row["attention"] ?? []))); ?></td>
        <td style="padding:8px;"><a href="<?php echo esc_url((string) ($row["dashboard_url"] ?? "#")); ?>"><?php esc_html_e("Otwórz", "upsellio"); ?></a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</section>
