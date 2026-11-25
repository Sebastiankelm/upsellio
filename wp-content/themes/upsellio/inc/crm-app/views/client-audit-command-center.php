<?php

if (!defined("ABSPATH")) {
    exit;
}

$cc_window = isset($_GET["window"]) ? (int) wp_unslash($_GET["window"]) : (int) get_option("ups_audit_default_compare_window", 30);
$cc_window = in_array($cc_window, [7, 14, 30, 60, 90], true) ? $cc_window : 30;
$cc_data = function_exists("ups_audit_build_command_center")
    ? ups_audit_build_command_center($cc_window, true)
    : ["clients" => [], "top_alerts" => [], "summary" => []];
$cc_clients = (array) ($cc_data["clients"] ?? []);
$cc_alerts = (array) ($cc_data["top_alerts"] ?? []);
$cc_summary = (array) ($cc_data["summary"] ?? []);
$status_labels = ["green" => "Stabilny", "yellow" => "Wymaga uwagi", "red" => "Krytyczny", "unknown" => "Brak danych"];
$cc_filter = sanitize_key(wp_unslash($_GET["health"] ?? ""));
$cc_visible = isset($status_labels[$cc_filter]) ? array_values(array_filter($cc_clients, static function ($row) use ($cc_filter) { return ($row["status"] ?? "") === $cc_filter; })) : $cc_clients;
$crm_base = function_exists("upsellio_crm_url") ? upsellio_crm_url("ca-command-center") : home_url("/crm-app/?view=ca-command-center");

?>
<section class="card crm-cc-toolbar">
  <div class="crm-view-heading">
    <div>
      <h2 style="margin:0;"><?php esc_html_e("Przegląd portfolio", "upsellio"); ?></h2>
      <p class="muted" style="margin:8px 0 0;max-width:640px;"><?php esc_html_e("Poranny przegląd portfolio: status klientów, alerty, co wymaga uwagi.", "upsellio"); ?></p>
    </div>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
      <select id="ups-cc-window" class="input" style="min-width:120px;" onchange="location.href='<?php echo esc_js(add_query_arg("window", "__W__", $crm_base)); ?>'.replace('__W__', this.value)">
        <?php foreach ([7, 14, 30, 60, 90] as $w) : ?>
          <option value="<?php echo (int) $w; ?>" <?php selected($cc_window, $w); ?>><?php echo (int) $w; ?> dni</option>
        <?php endforeach; ?>
      </select>
      <?php if (current_user_can("manage_options")) : ?><button type="button" class="btn alt" id="ups-audit-sync-all-btn"><i class="ti ti-refresh" aria-hidden="true"></i> Synchronizuj wszystkie</button><?php endif; ?>
      <a class="btn" href="<?php echo esc_url(function_exists("upsellio_crm_url") ? upsellio_crm_url("ca-clients") : home_url("/crm-app/?view=ca-clients")); ?>"><?php esc_html_e("Profile klientów", "upsellio"); ?></a>
    </div>
  </div>
  <nav class="crm-audit-summary-grid" aria-label="Filtruj klientów według kondycji">
    <?php foreach (["" => "Wszyscy klienci"] + $status_labels as $key => $label) : ?>
      <a class="crm-audit-summary-card<?php echo $cc_filter === $key ? " is-active" : ""; ?>" href="<?php echo esc_url(upsellio_crm_url("ca-command-center", ["window" => $cc_window, "health" => $key])); ?>">
        <span class="crm-audit-status crm-audit-status--<?php echo esc_attr($key ?: "all"); ?>"><?php echo esc_html($label); ?></span>
        <strong><?php echo (int) ($cc_summary[$key ?: "total"] ?? 0); ?></strong>
      </a>
    <?php endforeach; ?>
  </nav>
</section>

<?php if ($cc_alerts !== []) : ?>
<section class="card">
  <h3>Priorytetowe alerty portfolio</h3>
  <div style="display:flex;flex-direction:column;gap:6px;">
    <?php foreach ($cc_alerts as $al) : ?>
      <?php if (!is_array($al)) { continue; } ?>
      <div class="crm-audit-alert-row">
        <span class="crm-audit-status crm-audit-status--<?php echo ($al["severity"] ?? "") === "critical" ? "red" : "yellow"; ?>"><?php echo ($al["severity"] ?? "") === "critical" ? "Krytyczny" : "Uwaga"; ?></span>
        <strong style="min-width:100px;"><?php echo esc_html((string) ($al["client_name"] ?? "")); ?></strong>
        <span><?php echo esc_html((string) ($al["title"] ?? "")); ?> — <?php echo esc_html((string) ($al["message"] ?? "")); ?></span>
        <?php if (!empty($al["client_id"])) : ?>
          <a href="<?php echo esc_url(function_exists("upsellio_crm_url") ? upsellio_crm_url("ca-dashboard", ["cid" => (int) $al["client_id"], "window" => $cc_window]) : "#"); ?>" style="margin-left:auto;font-size:11px;"><?php esc_html_e("Dashboard →", "upsellio"); ?></a>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="card" style="overflow-x:auto;">
  <h2>Portfolio klientów</h2>
  <?php if ($cc_visible === []) : ?>
    <p class="muted"><?php echo esc_html($cc_clients ? "Brak klientów w wybranej kategorii." : "Brak dostępnych profili audytu."); ?></p>
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
    <?php foreach ($cc_visible as $row) : ?>
      <?php if (!is_array($row)) { continue; } ?>
      <?php
        $st = (string) ($row["status"] ?? "green");
        $deltas = (array) ($row["deltas"] ?? []);
        ?>
      <tr style="border-bottom:1px solid var(--border);">
        <td><span class="crm-audit-status crm-audit-status--<?php echo esc_attr($st); ?>"><?php echo esc_html($status_labels[$st] ?? "Brak danych"); ?></span></td>
        <td style="padding:8px;font-weight:700;"><?php echo esc_html((string) ($row["title"] ?? "")); ?></td>
        <td style="text-align:center;"><?php echo isset($row["health_score"]) ? (int) $row["health_score"] . "/100" : "—"; ?></td>
        <td style="text-align:center;font-weight:600;color:<?php echo (int) ($row["alert_count"] ?? 0) > 0 ? "var(--danger,#dc2626)" : "inherit"; ?>;"><?php echo (int) ($row["alert_count"] ?? 0); ?></td>
        <td style="text-align:center;"><?php echo isset($row["ga4_sessions"]) ? (int) $row["ga4_sessions"] : "—"; ?>
          <?php if (isset($deltas["ga4_sessions"])) : ?><div class="muted" style="font-size:10px;"><?php echo esc_html(ups_audit_format_delta((float) $deltas["ga4_sessions"])); ?></div><?php endif; ?>
        </td>
        <td style="text-align:center;"><?php echo isset($row["gsc_clicks"]) ? (int) $row["gsc_clicks"] : "—"; ?></td>
        <td style="text-align:center;"><?php echo isset($row["ads_cost"]) ? esc_html(number_format((float) $row["ads_cost"], 0, ",", " ")) : "—"; ?></td>
        <td style="padding:8px;max-width:220px;" class="muted"><?php echo esc_html(implode(" · ", (array) ($row["attention"] ?? []))); ?></td>
        <td style="padding:8px;"><a href="<?php echo esc_url((string) ($row["dashboard_url"] ?? "#")); ?>"><?php esc_html_e("Otwórz", "upsellio"); ?></a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</section>
