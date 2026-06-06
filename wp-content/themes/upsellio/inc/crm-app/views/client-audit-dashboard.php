<?php

if (!defined("ABSPATH")) {
    exit;
}

/** @var int $ca_client_id */
/** @var WP_Post $ca_client */
/** @var int $compare_window */
/** @var array<string, mixed> $current */

if (!is_array($current ?? null)) {
    $current = [];
}

$prev = function_exists("ups_audit_aggregate_previous_slice")
    ? ups_audit_aggregate_previous_slice($current)
    : [];
$deltas = (array) ($current["deltas"] ?? []);
$derived = (array) ($current["derived"] ?? []);
$ts_gsc = (array) (($current["timeseries"]["gsc_clicks"] ?? []));
$ts_ga4 = (array) (($current["timeseries"]["ga4_sessions"] ?? []));
$ts_ads = (array) (($current["timeseries"]["ads_cost"] ?? []));
$ts_ads_clicks = (array) (($current["timeseries"]["ads_clicks"] ?? []));
$ts_meta = (array) (($current["timeseries"]["meta_cost"] ?? []));
$has_ga4_data = (int) ($current["ga4_sessions"] ?? 0) > 0 || !empty($ts_ga4);
$has_gsc_data = (int) ($current["gsc_clicks"] ?? 0) > 0 || !empty($ts_gsc);
$has_ads_data = (float) ($current["ads_cost"] ?? 0) > 0 || !empty($ts_ads);
$benchmark = (array) ($current["benchmark"] ?? []);
$technical = (array) ($current["technical"] ?? []);
$channel_ltv = (array) (($current["channel_ltv"]["rows"] ?? []));
$health_score = (int) ($current["health_score"] ?? 0);
$health_trend = (array) ($current["health_trend"] ?? []);
$health_delta = (int) ($health_trend["delta"] ?? 0);
$attr_confidence = (array) ($current["attribution_confidence"] ?? []);
$revenue_quality = (array) ($current["revenue_quality"] ?? []);
$hs_color = $health_score >= 75 ? "var(--ok,#16a34a)" : ($health_score >= 50 ? "#d97706" : "var(--danger,#dc2626)");
$audit_setup = function_exists("ups_audit_client_setup_status")
    ? ups_audit_client_setup_status((int) $ca_client_id)
    : [];
$meta_mapped = (int) ($audit_setup["meta"] ?? 0);
$clarity_mapped = (int) ($current["clarity_mapped"] ?? $audit_setup["clarity"] ?? 0);
$clarity_errors = (array) ($current["clarity_errors"] ?? []);
$clarity_resources = (array) ($current["clarity_resources"] ?? []);
$show_clarity_block = $clarity_mapped > 0
    || (int) ($current["clarity_sessions"] ?? 0) > 0
    || !empty($current["clarity_top_pages"]);
$clarity_dash_url = "";
foreach ($clarity_resources as $cr) {
    if (!is_array($cr)) {
        continue;
    }
    $slug = (string) get_post_meta((int) ($cr["id"] ?? 0), "_ups_resource_external_id", true);
    if ($slug !== "" && function_exists("ups_audit_clarity_project_dashboard_url")) {
        $clarity_dash_url = ups_audit_clarity_project_dashboard_url($slug);
        break;
    }
}

$access_blockers = [];
foreach ((array) ($current["resources"] ?? []) as $res_blk) {
    if (!is_array($res_blk)) {
        continue;
    }
    $h = (array) ($res_blk["health"] ?? []);
    if ((string) ($h["status"] ?? "") !== "error") {
        continue;
    }
    $type = strtoupper((string) ($res_blk["type"] ?? ""));
    if (!in_array($type, ["GA4", "GSC"], true)) {
        continue;
    }
    $access_blockers[] = [
        "type" => $type,
        "title" => (string) ($res_blk["title"] ?? ""),
        "message" => (string) ($h["label"] ?? ""),
    ];
}
$ca_accounts_url = function_exists("upsellio_crm_url") ? upsellio_crm_url("ca-accounts") : "";
$ca_oauth_reconnect = function_exists("ups_audit_oauth_connect_url")
    ? ups_audit_oauth_connect_url(true, "wtapes GA4 GSC")
    : "";

$dash_channels_chart = ["labels" => [], "sessions" => [], "colors" => []];
$channel_palette = ["#0d9488", "#2563eb", "#8b5cf6", "#d97706", "#16a34a", "#ec4899", "#64748b", "#ef4444"];
foreach (array_slice((array) ($current["channels"] ?? []), 0, 8) as $ci => $ch_row) {
    if (!is_array($ch_row)) {
        continue;
    }
    $src = (string) ($ch_row["source"] ?? "");
    $med = (string) ($ch_row["medium"] ?? "");
    $lbl = $src . " / " . $med;
    if (function_exists("mb_substr")) {
        $lbl = mb_substr($lbl, 0, 24);
    } else {
        $lbl = substr($lbl, 0, 24);
    }
    $dash_channels_chart["labels"][] = $lbl;
    $dash_channels_chart["sessions"][] = (int) ($ch_row["sessions"] ?? 0);
    $dash_channels_chart["colors"][] = $channel_palette[$ci % count($channel_palette)];
}

$dash_keywords_chart = ["labels" => [], "clicks" => []];
foreach (array_slice((array) ($current["top_keywords"] ?? []), 0, 10) as $kw_row) {
    if (!is_array($kw_row)) {
        continue;
    }
    $kw = (string) ($kw_row["keyword"] ?? "");
    if ($kw === "") {
        continue;
    }
    $dash_keywords_chart["labels"][] = function_exists("mb_substr") ? mb_substr($kw, 0, 32) : substr($kw, 0, 32);
    $dash_keywords_chart["clicks"][] = (int) ($kw_row["clicks"] ?? 0);
}

$dash_campaigns_cpa = ["labels" => [], "cost" => [], "cpa" => []];
foreach (array_slice((array) ($current["campaigns"] ?? []), 0, 8) as $camp_row) {
    if (!is_array($camp_row)) {
        continue;
    }
    $cname = (string) ($camp_row["name"] ?? "");
    if ($cname === "") {
        continue;
    }
    $ccost = (float) ($camp_row["cost"] ?? $camp_row["cost_pln"] ?? 0);
    $cconv = (float) ($camp_row["conversions"] ?? 0);
    $ccpa = (float) ($camp_row["cpa"] ?? ($cconv > 0 ? $ccost / $cconv : 0));
    $dash_campaigns_cpa["labels"][] = function_exists("mb_substr") ? mb_substr($cname, 0, 28) : substr($cname, 0, 28);
    $dash_campaigns_cpa["cost"][] = $ccost;
    $dash_campaigns_cpa["cpa"][] = $ccpa;
}

$dash_ltv_chart = ["labels" => [], "ltv" => [], "leads" => []];
foreach (array_slice($channel_ltv, 0, 8) as $ltv_row) {
    if (!is_array($ltv_row)) {
        continue;
    }
    $dash_ltv_chart["labels"][] = (string) ($ltv_row["channel"] ?? "");
    $dash_ltv_chart["ltv"][] = (float) ($ltv_row["ltv_per_session"] ?? 0);
    $dash_ltv_chart["leads"][] = (int) ($ltv_row["leads"] ?? 0);
}

$funnel_max = 1;
if ($has_gsc_data || $has_ga4_data) {
    $funnel_max = max(
        1,
        (int) ($current["gsc_clicks"] ?? 0),
        (int) ($current["ga4_sessions"] ?? 0),
        (int) ($current["ga4_conversions"] ?? 0),
        (float) ($current["ga4_revenue"] ?? 0)
    );
} elseif ($has_ads_data) {
    $funnel_max = max(
        1,
        (int) ($current["ads_clicks"] ?? 0),
        (float) ($current["ads_conversions"] ?? 0),
        (int) ($current["clarity_sessions"] ?? 0),
        (float) ($current["ads_cost"] ?? 0)
    );
}

?>
<script>window.UPS_AUDIT_CLIENT_ID = <?php echo (int) $ca_client_id; ?>;</script>
<div id="ups-audit-dash-root" data-client-id="<?php echo (int) $ca_client_id; ?>" data-window="<?php echo (int) $compare_window; ?>">
  <section class="card">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
      <div style="flex:1;min-width:200px;">
        <h2 style="margin:0;"><?php echo esc_html((string) $ca_client->post_title); ?></h2>
        <p class="muted" style="margin:4px 0 0;"><?php echo (int) $compare_window; ?> dni vs poprzedni okres · <?php esc_html_e("statystyki ze zmapowanych zasobów", "upsellio"); ?></p>
        <?php
        $ca_website = trim((string) get_post_meta((int) $ca_client_id, "_ups_client_website", true));
        if ($ca_website !== "") :
            ?>
          <p class="muted" style="margin:4px 0 0;font-size:12px;"><?php echo esc_html($ca_website); ?></p>
        <?php endif; ?>
        <?php
        $intel_hdr = (array) ($current["intelligence"] ?? []);
        $opp_hdr = (array) ($intel_hdr["opportunity"] ?? []);
        $opp_score_hdr = (int) ($opp_hdr["score"] ?? 0);
        $exec_hdr = (array) ($intel_hdr["executive_summary"] ?? []);
        ?>
        <?php if ($health_score > 0 || $opp_score_hdr > 0) : ?>
          <div style="margin-top:8px;font-size:12px;display:flex;gap:12px;flex-wrap:wrap;">
            <?php if ($health_score > 0) : ?>
              <span>Health: <strong style="color:<?php echo esc_attr($hs_color); ?>"><?php echo (int) $health_score; ?>/100</strong>
                <?php if ($health_delta !== 0) : ?>
                  <span style="color:<?php echo $health_delta > 0 ? "var(--ok,#16a34a)" : "var(--danger,#dc2626)"; ?>;font-weight:600;">
                    <?php echo $health_delta > 0 ? "↑ +" . (int) $health_delta : "↓ " . (int) $health_delta; ?>
                  </span>
                <?php endif; ?>
              </span>
            <?php endif; ?>
            <?php if ((int) ($attr_confidence["score"] ?? 0) > 0) : ?>
              <span><?php esc_html_e("Attribution", "upsellio"); ?>: <strong><?php echo (int) $attr_confidence["score"]; ?>%</strong></span>
            <?php endif; ?>
            <?php if ($opp_score_hdr > 0) : ?>
              <span>Opportunity: <strong style="color:#2563eb;"><?php echo (int) $opp_score_hdr; ?>/100</strong></span>
            <?php endif; ?>
          </div>
        <?php endif; ?>
        <?php if (!empty($exec_hdr["text"])) : ?>
          <p style="margin:10px 0 0;font-size:13px;line-height:1.5;font-weight:600;"><?php echo esc_html((string) $exec_hdr["text"]); ?></p>
        <?php endif; ?>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
        <select id="ups-audit-window-select" class="input" style="min-width:120px;">
          <?php foreach ([7, 14, 30, 60, 90] as $w) : ?>
            <option value="<?php echo (int) $w; ?>" <?php selected($compare_window, $w); ?>><?php echo (int) $w; ?> dni</option>
          <?php endforeach; ?>
        </select>
        <button type="button" class="btn alt" id="ups-audit-sync-client-btn"><i class="ti ti-refresh" aria-hidden="true"></i> Sync danych</button>
        <button type="button" class="btn" onclick="upsAuditGenReport(<?php echo (int) $ca_client_id; ?>,'monthly')"><i class="ti ti-sparkles" aria-hidden="true"></i> Raport AI</button>
        <button type="button" class="btn alt" id="ups-audit-export-dash-pdf"><i class="ti ti-file-type-pdf" aria-hidden="true"></i> PDF dashboard</button>
        <button type="button" class="btn alt" data-ups-audit-open-map data-client-id="<?php echo (int) $ca_client_id; ?>" data-client-name="<?php echo esc_attr((string) $ca_client->post_title); ?>"><i class="ti ti-link" aria-hidden="true"></i> <?php esc_html_e("Mapuj zasoby", "upsellio"); ?></button>
      </div>
    </div>
    <?php if (!empty($benchmark["clients"])) : ?>
    <p class="muted" style="margin:10px 0 0;font-size:12px;">
      Benchmark portfolio (<?php echo (int) $benchmark["clients"]; ?> klientów):
      śr. <?php echo esc_html(number_format((int) ($benchmark["ga4_sessions"] ?? 0), 0, ",", " ")); ?> sesji GA4,
      <?php echo esc_html(number_format((int) ($benchmark["gsc_clicks"] ?? 0), 0, ",", " ")); ?> klik. GSC,
      health <?php echo (int) ($benchmark["health_score"] ?? 0); ?>/100
    </p>
    <?php endif; ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;margin-top:14px;">
      <?php
      $kpis = [
          ["label" => "Sesje GA4", "key" => "ga4_sessions", "cur" => (int) ($current["ga4_sessions"] ?? 0), "delta" => "ga4_sessions", "fmt" => "int"],
          ["label" => "Zakupy GA4 (purchase)", "key" => "ga4_conv", "cur" => (int) ($current["ga4_purchase_count"] ?? $current["ga4_conversions"] ?? 0), "delta" => "ga4_conversions", "fmt" => "int"],
          ["label" => "CR sesji", "key" => "cr", "cur" => (float) ($derived["ga4_conversion_rate"] ?? 0), "delta" => "", "fmt" => "pct"],
          ["label" => "Klik. GSC", "key" => "gsc_clicks", "cur" => (int) ($current["gsc_clicks"] ?? 0), "delta" => "gsc_clicks", "fmt" => "int"],
          ["label" => "CTR GSC", "key" => "ctr", "cur" => (float) ($derived["gsc_ctr"] ?? 0), "delta" => "", "fmt" => "pct"],
          ["label" => "Śr. poz. GSC", "key" => "pos", "cur" => (float) ($derived["gsc_avg_position"] ?? 0), "delta" => "", "fmt" => "pos"],
          ["label" => "Wydatek Ads", "key" => "ads_cost", "cur" => (float) ($current["ads_cost"] ?? 0), "delta" => "ads_cost", "fmt" => "money"],
          ["label" => "Klik. Ads", "key" => "ads_clicks", "cur" => (int) ($current["ads_clicks"] ?? 0), "delta" => "ads_clicks", "fmt" => "int"],
          ["label" => "Konw. Ads", "key" => "ads_conv", "cur" => (float) ($current["ads_conversions"] ?? 0), "delta" => "ads_conversions", "fmt" => "float"],
          ["label" => "CPC Ads", "key" => "cpc", "cur" => (float) ($derived["ads_cpc"] ?? 0), "delta" => "", "fmt" => "money_small"],
          ["label" => "CPA Ads", "key" => "cpa", "cur" => (float) ($derived["ads_cpa"] ?? 0), "delta" => "", "fmt" => "money_small"],
          ["label" => "ROAS (purchaseRevenue)", "key" => "roas", "cur" => (float) ($current["roas"] ?? 0), "delta" => "roas", "fmt" => "roas"],
      ];
      if ($meta_mapped > 0 || (float) ($current["meta_cost"] ?? 0) > 0) {
          $kpis[] = ["label" => "Wydatek Meta", "key" => "meta_cost", "cur" => (float) ($current["meta_cost"] ?? 0), "delta" => "meta_cost", "fmt" => "money"];
          $kpis[] = ["label" => "CPC Meta", "key" => "meta_cpc", "cur" => (float) ($derived["meta_cpc"] ?? 0), "delta" => "", "fmt" => "money_small"];
          $kpis[] = ["label" => "ROAS Meta", "key" => "meta_roas", "cur" => (float) ($current["meta_roas"] ?? 0), "delta" => "meta_roas", "fmt" => "roas"];
      }
      if ((float) ($current["paid_cost"] ?? 0) > 0) {
          $kpis[] = ["label" => "Łączny paid", "key" => "paid_cost", "cur" => (float) ($current["paid_cost"] ?? 0), "delta" => "paid_cost", "fmt" => "money"];
      }
      if ($clarity_mapped > 0 || (int) ($current["clarity_sessions"] ?? 0) > 0) {
          $kpis[] = ["label" => "Sesje Clarity (API " . (int) ($current["clarity_window_days"] ?? 3) . "d)", "key" => "clarity_sess", "cur" => (int) ($current["clarity_sessions"] ?? 0), "delta" => "", "fmt" => "int"];
          $kpis[] = ["label" => "Użytk. Clarity", "key" => "clarity_users", "cur" => (int) ($current["clarity_users"] ?? 0), "delta" => "", "fmt" => "int"];
          $kpis[] = ["label" => "Dead clicks", "key" => "clarity_dead", "cur" => (int) ($current["clarity_dead_clicks"] ?? 0), "delta" => "", "fmt" => "int"];
          $kpis[] = ["label" => "Rage clicks", "key" => "clarity_rage", "cur" => (int) ($current["clarity_rage_clicks"] ?? 0), "delta" => "", "fmt" => "int"];
      }
      foreach ($kpis as $kpi) :
          $d = (float) ($deltas[$kpi["delta"]] ?? 0);
          $d_cls = $d > 0 ? "color:var(--ok,#16a34a)" : ($d < 0 ? "color:var(--danger,#dc2626)" : "color:var(--text-2)");
          $val = $kpi["fmt"] === "money"
              ? number_format((float) $kpi["cur"], 0, ",", " ") . " PLN"
              : ($kpi["fmt"] === "money_small"
              ? number_format((float) $kpi["cur"], 2, ",", " ") . " PLN"
              : ($kpi["fmt"] === "pct"
              ? number_format((float) $kpi["cur"], 2, ",", " ") . "%"
              : ($kpi["fmt"] === "pos"
              ? number_format((float) $kpi["cur"], 1, ",", " ")
              : ($kpi["fmt"] === "roas"
              ? number_format((float) $kpi["cur"], 2, ",", " ") . "x"
              : ($kpi["fmt"] === "float"
              ? number_format((float) $kpi["cur"], 1, ",", " ")
              : number_format((int) $kpi["cur"], 0, ",", " "))))));
          if ($kpi["delta"] === "") {
              $d_cls = "color:var(--text-3)";
              $d = null;
          }
          $bench_val = null;
          $bench_lbl = "";
          if (!empty($benchmark["clients"]) && !empty($benchmark["has_benchmark"])) {
              if ($kpi["key"] === "ga4_sessions") {
                  $bench_val = (float) ($benchmark["ga4_sessions"] ?? 0);
                  $bench_lbl = "sesje";
              } elseif ($kpi["key"] === "gsc_clicks") {
                  $bench_val = (float) ($benchmark["gsc_clicks"] ?? 0);
                  $bench_lbl = "klik.";
              } elseif ($kpi["key"] === "ads_cost") {
                  $bench_val = (float) ($benchmark["ads_cost"] ?? 0);
                  $bench_lbl = "PLN";
              } elseif ($kpi["key"] === "roas") {
                  $bench_val = (float) ($benchmark["roas"] ?? 0);
                  $bench_lbl = "x";
              }
          }
          $vs_bench = ($bench_val !== null && function_exists("ups_audit_vs_benchmark_pct"))
              ? ups_audit_vs_benchmark_pct((float) $kpi["cur"], $bench_val)
              : null;
          ?>
        <?php
          $kpi_untrusted = ($kpi["key"] === "roas" && !empty($revenue_quality["warning"]));
          ?>
        <div class="kpi" style="padding:12px;border:1px solid var(--border);border-radius:10px;<?php echo $kpi_untrusted ? "opacity:0.55;border-color:#fca5a5;" : ""; ?>">
          <span class="muted" style="font-size:12px;"><?php echo esc_html($kpi["label"]); ?><?php if ($kpi_untrusted) : ?> <span style="color:#b91c1c;">⚠</span><?php endif; ?></span>
          <b style="display:block;font-size:20px;margin:4px 0;"><?php echo $kpi_untrusted ? "—" : esc_html($val); ?></b>
          <?php if ($d !== null) : ?>
          <span style="font-size:12px;<?php echo esc_attr($d_cls); ?>"><?php echo esc_html(function_exists("ups_audit_format_delta") ? ups_audit_format_delta($d) : ""); ?></span>
          <?php endif; ?>
          <?php if ($vs_bench !== null && $bench_val !== null) : ?>
          <div class="muted" style="font-size:10px;margin-top:4px;">vs portfel: <?php echo esc_html(number_format($bench_val, $kpi["fmt"] === "roas" ? 2 : 0, ",", " ")); ?> <?php echo esc_html($bench_lbl); ?> (<?php echo esc_html(($vs_bench > 0 ? "+" : "") . (string) $vs_bench); ?>%)</div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <?php
  $ga4_conv_all = (int) ($current["ga4_conversions_all"] ?? 0);
  $ga4_conv_macro = (int) ($current["ga4_conversions"] ?? 0);
  $ga4_not_set_pct = (float) ($current["ga4_not_set_pct"] ?? 0);
  $quality_notes = (array) ($current["data_quality_notes"] ?? []);
  $events_breakdown = (array) ($current["ga4_events_breakdown"] ?? []);
  $events_diagnostics = (array) ($current["ga4_events_diagnostics"] ?? $events_breakdown);
  $ga4_rev_session = (float) ($current["ga4_revenue_session_total"] ?? 0);
  $ga4_purchase_cnt = (int) ($current["ga4_purchase_count"] ?? 0);
  $ga4_rev_source = (string) ($current["ga4_revenue_source"] ?? "");
  $roas_val = (float) ($current["roas"] ?? 0);
  $ads_cost_val = (float) ($current["ads_cost"] ?? 0);
  $revenue_suspect = !empty($revenue_quality["warning"])
      || ($roas_val > 15 || ($ga4_purchase_cnt > 0 && (int) ($current["ga4_sessions"] ?? 0) > 0
      && ($ga4_purchase_cnt / (int) $current["ga4_sessions"]) > 0.08)
      || ($ga4_rev_session > 0 && (float) ($current["ga4_revenue"] ?? 0) > 0 && $ga4_rev_session > (float) $current["ga4_revenue"] * 1.25));
  ?>

  <?php if ($has_ga4_data && $events_diagnostics !== []) : ?>
  <section class="card" style="border-color:<?php echo $revenue_suspect ? "#fca5a5" : "#93c5fd"; ?>;background:<?php echo $revenue_suspect ? "#fef2f2" : "#eff6ff"; ?>;">
    <h3 style="margin:0 0 8px;font-size:14px;color:<?php echo $revenue_suspect ? "#b91c1c" : "#1e40af"; ?>;">
      <?php echo $revenue_suspect ? esc_html__("Revenue Quality Warning — diagnostyka GA4", "upsellio") : esc_html__("Diagnostyka GA4 — eventy i przychód", "upsellio"); ?>
    </h3>
    <p style="margin:0 0 10px;font-size:12px;line-height:1.5;">
      KPI przychodu: <strong><?php echo esc_html(number_format((float) ($current["ga4_revenue"] ?? 0), 0, ",", " ")); ?> PLN</strong>
      <?php if ($ga4_rev_source === "purchaseRevenue") : ?>
        <span class="muted">(purchaseRevenue · <?php echo (int) $ga4_purchase_cnt; ?> zakupów)</span>
      <?php endif; ?>
      <?php if ($ga4_rev_session > 0) : ?>
        · sesje totalRevenue: <strong><?php echo esc_html(number_format($ga4_rev_session, 0, ",", " ")); ?> PLN</strong>
      <?php endif; ?>
      <?php if ($ads_cost_val > 0) : ?>
        · ROAS: <strong><?php echo esc_html(number_format($roas_val, 2, ",", " ")); ?>x</strong> (koszt Ads <?php echo esc_html(number_format($ads_cost_val, 0, ",", " ")); ?> PLN)
      <?php endif; ?>
    </p>
    <?php if ($revenue_suspect) : ?>
      <p style="margin:0 0 10px;font-size:12px;color:#b91c1c;font-weight:600;">
        Podejrzane wartości — sprawdź duplikaty <code>purchase</code> na thank-you oraz eventy engagement z revenue &gt; 0.
      </p>
    <?php endif; ?>
    <table style="width:100%;font-size:12px;border-collapse:collapse;">
      <thead>
        <tr style="background:var(--bg);">
          <th style="text-align:left;padding:6px 8px;">Event</th>
          <th style="text-align:right;padding:6px 8px;">Count</th>
          <th style="text-align:right;padding:6px 8px;">Revenue (eventValue)</th>
          <th style="text-align:left;padding:6px 8px;">Typ</th>
        </tr>
      </thead>
      <tbody>
      <?php
      $diag_show = ["purchase", "begin_checkout", "add_to_cart", "form_submit", "form_start", "view_item", "generate_lead", "lead"];
      $diag_rows = [];
      foreach ($events_diagnostics as $ev) {
          if (!is_array($ev)) {
              continue;
          }
          $diag_rows[strtolower((string) ($ev["event"] ?? ""))] = $ev;
      }
      foreach ($diag_show as $dname) {
          if (!isset($diag_rows[$dname])) {
              continue;
          }
          $ev = $diag_rows[$dname];
          ?>
        <tr style="border-bottom:1px solid var(--border);">
          <td style="padding:6px 8px;font-weight:600;"><?php echo esc_html($dname); ?></td>
          <td style="padding:6px 8px;text-align:right;"><?php echo (int) ($ev["count"] ?? 0); ?></td>
          <td style="padding:6px 8px;text-align:right;<?php echo (float) ($ev["revenue"] ?? 0) > 0 && in_array($dname, ["form_submit", "form_start", "view_item", "add_to_cart"], true) ? "color:#b91c1c;font-weight:700;" : ""; ?>">
            <?php echo (float) ($ev["revenue"] ?? 0) > 0 ? esc_html(number_format((float) $ev["revenue"], 0, ",", " ")) . " PLN" : "—"; ?>
          </td>
          <td style="padding:6px 8px;" class="muted"><?php echo esc_html((string) ($ev["kind"] ?? "")); ?></td>
        </tr>
          <?php
      }
      ?>
      </tbody>
    </table>
    <p class="muted" style="margin:8px 0 0;font-size:11px;">
      Porównaj z GA4 → Monetyzacja → Zakupy e-commerce. Po sync danych KPI używa <code>purchaseRevenue</code>, nie sumy <code>totalRevenue</code> z sesji.
    </p>
  </section>
  <?php endif; ?>

  <?php
  $window_slice_notes = (array) ($current["window_slice_notes"] ?? []);
  ?>
  <?php if ($window_slice_notes !== []) : ?>
  <section class="card" style="border-color:#fcd34d;background:#fffbeb;">
    <h3 style="margin:0 0 8px;font-size:14px;color:#92400e;">Okres <?php echo (int) $compare_window; ?> dni — wymagany sync</h3>
    <p class="muted" style="margin:0 0 8px;font-size:12px;line-height:1.5;">
      Część metryk (frazy GSC, kanały GA4, kampanie/search terms Ads) wymaga ponownego <strong>Sync danych</strong> z wybranym okresem w liście powyżej.
      KPI dzienne (wykresy, sesje, koszty) przeliczane są z cache do <?php echo (int) $compare_window; ?> dni.
    </p>
    <ul style="margin:0;padding-left:18px;font-size:12px;line-height:1.5;">
      <?php foreach (array_slice($window_slice_notes, 0, 6) as $wn) : ?>
        <li><?php echo esc_html((string) $wn); ?></li>
      <?php endforeach; ?>
    </ul>
  </section>
  <?php endif; ?>

  <?php if ($quality_notes !== [] || $ga4_conv_all > $ga4_conv_macro * 2 || $ga4_not_set_pct >= 20) : ?>
  <section class="card" style="border-color:#93c5fd;background:#eff6ff;">
    <h3 style="margin:0 0 8px;font-size:14px;color:#1e40af;">Jakość pomiaru — przeczytaj przed wnioskami</h3>
    <?php if ($ga4_conv_all > 0 && $ga4_conv_macro > 0 && $ga4_conv_all > $ga4_conv_macro * 2) : ?>
      <p style="margin:0 0 8px;font-size:12px;">GA4: KPI konwersji <strong><?php echo (int) $ga4_conv_macro; ?></strong> · zdarzenia oznaczone w GA4 jako konwersje <strong><?php echo (int) $ga4_conv_all; ?></strong> · zaangażowanie (formularz, koszyk) <strong><?php echo (int) ($current["ga4_conversions_engagement"] ?? 0); ?></strong>. CR oparte na KPI (purchase/lead lub fallback GA4 conversions).</p>
    <?php endif; ?>
    <?php if ($ga4_not_set_pct >= 20) : ?>
      <p style="margin:0 0 8px;font-size:12px;color:#b45309;">(not set) / brak atrybucji: <strong><?php echo esc_html(number_format($ga4_not_set_pct, 1, ",", " ")); ?>%</strong> sesji (<?php echo (int) ($current["ga4_not_set_sessions"] ?? 0); ?> / <?php echo (int) ($current["ga4_sessions"] ?? 0); ?>). Napraw UTM i GTM.</p>
    <?php endif; ?>
    <?php if ((int) ($current["ads_conversions"] ?? 0) > 0 && $ga4_conv_macro > 0) : ?>
      <p style="margin:0 0 8px;font-size:12px;">Ads: <?php echo (int) ($current["ads_conversions"] ?? 0); ?> konw. · GA4 makro: <?php echo (int) $ga4_conv_macro; ?> — różnice wynikają z atrybucji i definicji konwersji (B2B: telefon/wycena poza sklepem).</p>
    <?php endif; ?>
    <?php foreach ($quality_notes as $qn) : ?>
      <p style="margin:0 0 6px;font-size:12px;"><?php echo esc_html((string) $qn); ?></p>
    <?php endforeach; ?>
    <?php if ($events_breakdown !== []) : ?>
    <details style="margin-top:8px;font-size:12px;">
      <summary style="cursor:pointer;">Rozkład eventów GA4 (top)</summary>
      <table style="width:100%;margin-top:8px;font-size:11px;">
        <thead><tr><th>Event</th><th>Liczba</th><th>Typ</th></tr></thead>
        <tbody>
        <?php foreach (array_slice($events_breakdown, 0, 12) as $ev) : ?>
          <?php if (!is_array($ev)) { continue; } ?>
          <tr>
            <td><?php echo esc_html((string) ($ev["event"] ?? "")); ?></td>
            <td><?php echo (int) ($ev["count"] ?? 0); ?></td>
            <td><?php echo esc_html((string) ($ev["kind"] ?? "")); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </details>
    <?php endif; ?>
  </section>
  <?php endif; ?>

  <?php if ($access_blockers !== []) : ?>
  <section class="card" style="border-color:#fdba74;background:#fff7ed;">
    <h3 style="margin:0 0 8px;font-size:14px;color:#9a3412;">Brak dostępu Google — część KPI nie może się policzyć</h3>
    <p class="muted" style="margin:0 0 10px;font-size:12px;line-height:1.5;">
      Masz zwykle <strong>2 konta Google</strong> w Upsellio (np. agencja + klient). GA4/GSC wtapes muszą być zmapowane na konto,
      które widzi property <code>484708285</code> i GSC <code>https://wtapes.pl/</code> — Ads może zostać na osobnym koncie (MCC).
    </p>
    <ol style="margin:0 0 12px;padding-left:18px;font-size:12px;line-height:1.55;">
      <li><a href="<?php echo esc_url($ca_accounts_url); ?>" style="color:var(--teal);font-weight:600;">Połączenia Google</a> — sprawdź, które konto ma wtapes na liście GA4/GSC.</li>
      <li><strong>Mapuj zasoby</strong> — GA4 + GSC → konto klienta; Google Ads → konto z MCC/Ads.</li>
      <li>Sync danych po zmianie mapowania.</li>
    </ol>
    <?php foreach ($access_blockers as $blk) : ?>
      <div style="font-size:12px;margin-bottom:6px;"><strong><?php echo esc_html($blk["type"] . " · " . $blk["title"]); ?>:</strong> <?php echo esc_html($blk["message"]); ?></div>
    <?php endforeach; ?>
    <?php if ($ca_oauth_reconnect !== "") : ?>
      <a class="btn" href="<?php echo esc_url($ca_oauth_reconnect); ?>" style="margin-top:8px;"><i class="ti ti-brand-google" aria-hidden="true"></i> Połącz konto Google (GA4/GSC)</a>
    <?php endif; ?>
  </section>
  <?php endif; ?>

  <?php if ($technical !== []) : ?>
  <section class="card" id="ups-audit-technical-block">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:10px;">
      <h3 style="margin:0;font-size:14px;">Techniczne — indeksacja &amp; Core Web Vitals</h3>
      <div style="display:flex;gap:6px;flex-wrap:wrap;">
        <button type="button" class="btn alt" id="ups-audit-refresh-cwv-btn" data-client-id="<?php echo (int) $ca_client_id; ?>" style="font-size:11px;"><i class="ti ti-gauge"></i> <?php esc_html_e("Odśwież CWV", "upsellio"); ?></button>
      </div>
    </div>
    <?php
    $idx = (array) ($technical["indexation"] ?? []);
    $cwv = (array) ($technical["cwv"] ?? []);
    $cwv_status = (string) ($cwv["status"] ?? "");
    ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:10px;font-size:12px;">
      <div style="padding:12px;border:1px solid var(--border);border-radius:10px;">
        <div class="muted">Indeksacja GSC</div>
        <div style="font-size:18px;font-weight:800;margin:6px 0;"><?php echo isset($idx["ratio"]) && $idx["ratio"] !== null ? (int) $idx["ratio"] . "%" : "—"; ?></div>
        <div><?php echo (int) ($idx["indexed"] ?? 0); ?> / <?php echo (int) ($idx["submitted"] ?? 0); ?> URL</div>
        <?php if (!empty($idx["pages_sampled"])) : ?>
          <div class="muted"><?php printf(esc_html__("Próbka inspekcji: %d URL", "upsellio"), (int) $idx["pages_sampled"]); ?></div>
        <?php endif; ?>
        <div class="muted" style="margin-top:6px;"><?php echo esc_html((string) ($idx["note"] ?? "")); ?></div>
        <?php if ((int) ($idx["submitted"] ?? 0) <= 0) : ?>
          <p class="muted" style="margin:8px 0 0;font-size:11px;"><?php esc_html_e("Uruchom „Sync danych” — indeksacja pobierana z GSC Sitemap API.", "upsellio"); ?></p>
        <?php endif; ?>
      </div>
      <div style="padding:12px;border:1px solid var(--border);border-radius:10px;" id="ups-audit-cwv-panel">
        <div class="muted">PageSpeed (mobile)</div>
        <?php if ($cwv_status === "ok") : ?>
          <div style="font-size:18px;font-weight:800;margin:6px 0;"><?php echo (int) ($cwv["performance_score"] ?? 0); ?>/100</div>
          <div>LCP <?php echo $cwv["lcp_ms"] !== null ? esc_html((string) $cwv["lcp_ms"] . " ms") : "—"; ?></div>
          <div>CLS <?php echo $cwv["cls"] !== null ? esc_html((string) $cwv["cls"]) : "—"; ?></div>
          <div>INP <?php echo $cwv["inp_ms"] !== null ? esc_html((string) $cwv["inp_ms"] . " ms") : "—"; ?></div>
          <?php if (!empty($cwv["fetched_at"])) : ?>
            <div class="muted" style="margin-top:6px;font-size:11px;"><?php echo esc_html((string) $cwv["fetched_at"]); ?><?php echo !empty($cwv["stale"]) ? " · cache" : ""; ?></div>
          <?php endif; ?>
          <?php if (!empty($cwv["quota_note"])) : ?>
            <p class="muted" style="margin:6px 0 0;font-size:11px;"><?php echo esc_html((string) $cwv["quota_note"]); ?></p>
          <?php endif; ?>
        <?php else : ?>
          <p class="muted" style="margin:8px 0 0;"><?php echo esc_html((string) ($cwv["message"] ?? __("Brak danych CWV — użyj „Odśwież CWV” (max 5/dzień).", "upsellio"))); ?></p>
        <?php endif; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php if (!empty($current["resources"])) : ?>
  <section class="card">
    <h3 style="margin:0 0 10px;font-size:14px;"><?php esc_html_e("Źródła danych (sync cache)", "upsellio"); ?></h3>
    <table style="width:100%;font-size:12px;">
      <thead><tr><th style="text-align:left;"><?php esc_html_e("Zasób", "upsellio"); ?></th><th><?php esc_html_e("Typ", "upsellio"); ?></th><th><?php esc_html_e("Status", "upsellio"); ?></th><th><?php esc_html_e("Ostatni sync", "upsellio"); ?></th></tr></thead>
      <tbody>
      <?php foreach ((array) $current["resources"] as $res_row) : ?>
        <?php if (!is_array($res_row)) { continue; } ?>
        <?php
        $rh = (array) ($res_row["health"] ?? []);
        $rh_status = (string) ($rh["status"] ?? "");
        $rh_color = $rh_status === "ok" ? "var(--ok,#16a34a)" : ($rh_status === "warn" ? "#d97706" : "var(--danger,#dc2626)");
        ?>
        <tr>
          <td><?php echo esc_html((string) ($res_row["title"] ?? "")); ?></td>
          <td><code><?php echo esc_html((string) ($res_row["type"] ?? "")); ?></code></td>
          <td style="color:<?php echo esc_attr($rh_color); ?>;"><?php echo esc_html((string) ($rh["label"] ?? "—")); ?></td>
          <td class="muted"><?php echo esc_html((string) ($res_row["last_sync"] ?? "—")); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </section>
  <?php endif; ?>

  <?php if ($show_clarity_block) : ?>
  <section class="card" style="border-color:#c4b5fd;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
      <h3 style="margin:0;font-size:14px;">Microsoft Clarity — UX (ostatnie <?php echo (int) ($current["clarity_window_days"] ?? 3); ?> dni API)</h3>
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <?php if ($clarity_dash_url !== "") : ?>
          <a href="<?php echo esc_url($clarity_dash_url); ?>" target="_blank" rel="noopener noreferrer" class="btn alt" style="font-size:12px;padding:6px 10px;text-decoration:none;"><?php esc_html_e("Otwórz w Clarity", "upsellio"); ?></a>
        <?php endif; ?>
        <button type="button" class="btn alt" style="font-size:12px;padding:6px 10px;" onclick="window.upsAuditSyncClient && window.upsAuditSyncClient(<?php echo (int) $ca_client_id; ?>)"><i class="ti ti-refresh" aria-hidden="true"></i> <?php esc_html_e("Sync Clarity", "upsellio"); ?></button>
      </div>
    </div>
    <?php if (!empty($clarity_errors)) : ?>
      <div style="margin:0 0 12px;padding:10px 12px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;font-size:12px;color:#b91c1c;">
        <?php foreach ($clarity_errors as $cerr) : ?>
          <div><?php echo esc_html((string) $cerr); ?></div>
        <?php endforeach; ?>
        <p style="margin:8px 0 0;font-size:11px;color:#7f1d1d;"><?php esc_html_e("Token: Clarity → Settings → Data Export (admin projektu). Następnie Test połączenia w Profile klientów.", "upsellio"); ?></p>
      </div>
    <?php elseif ((int) ($current["clarity_sessions"] ?? 0) === 0) : ?>
      <p class="muted" style="margin:0 0 12px;font-size:12px;"><?php esc_html_e("Clarity zmapowany, ale brak sesji w cache — kliknij „Sync Clarity” lub sprawdź skrypt Clarity na stronie klienta.", "upsellio"); ?></p>
    <?php endif; ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;font-size:12px;">
      <div style="padding:12px;border:1px solid var(--border);border-radius:10px;">
        <div class="muted">Sesje</div>
        <div style="font-size:20px;font-weight:800;"><?php echo (int) ($current["clarity_sessions"] ?? 0); ?></div>
      </div>
      <div style="padding:12px;border:1px solid var(--border);border-radius:10px;">
        <div class="muted">Użytkownicy</div>
        <div style="font-size:20px;font-weight:800;"><?php echo (int) ($current["clarity_users"] ?? 0); ?></div>
      </div>
      <div style="padding:12px;border:1px solid var(--border);border-radius:10px;">
        <div class="muted">Dead clicks</div>
        <div style="font-size:20px;font-weight:800;color:var(--danger,#dc2626);"><?php echo (int) ($current["clarity_dead_clicks"] ?? 0); ?></div>
      </div>
      <div style="padding:12px;border:1px solid var(--border);border-radius:10px;">
        <div class="muted">Rage clicks</div>
        <div style="font-size:20px;font-weight:800;color:#d97706;"><?php echo (int) ($current["clarity_rage_clicks"] ?? 0); ?></div>
      </div>
      <?php if ((float) ($current["clarity_engagement_sec"] ?? 0) > 0) : ?>
      <div style="padding:12px;border:1px solid var(--border);border-radius:10px;">
        <div class="muted">Czas zaangażowania</div>
        <div style="font-size:20px;font-weight:800;"><?php echo esc_html(number_format((float) $current["clarity_engagement_sec"], 1, ",", " ")); ?> s</div>
      </div>
      <?php endif; ?>
      <?php if ((float) ($current["clarity_scroll_depth"] ?? 0) > 0) : ?>
      <div style="padding:12px;border:1px solid var(--border);border-radius:10px;">
        <div class="muted">Scroll depth</div>
        <div style="font-size:20px;font-weight:800;"><?php echo esc_html(number_format((float) $current["clarity_scroll_depth"], 1, ",", " ")); ?>%</div>
      </div>
      <?php endif; ?>
      <?php if ((int) ($current["clarity_bot_sessions"] ?? 0) > 0) : ?>
      <div style="padding:12px;border:1px solid var(--border);border-radius:10px;">
        <div class="muted">Sesje botów</div>
        <div style="font-size:20px;font-weight:800;"><?php echo (int) ($current["clarity_bot_sessions"] ?? 0); ?></div>
      </div>
      <?php endif; ?>
      <?php if ((float) ($current["clarity_pages_per_session"] ?? 0) > 0) : ?>
      <div style="padding:12px;border:1px solid var(--border);border-radius:10px;">
        <div class="muted">Strony / sesja</div>
        <div style="font-size:20px;font-weight:800;"><?php echo esc_html(number_format((float) $current["clarity_pages_per_session"], 2, ",", " ")); ?></div>
      </div>
      <?php endif; ?>
    </div>
    <?php if (!empty($current["clarity_by_device"])) : ?>
    <table style="width:100%;margin-top:12px;font-size:12px;">
      <thead><tr><th style="text-align:left;">Urządzenie</th><th>Sesje</th><th>Dead</th><th>Rage</th></tr></thead>
      <tbody>
      <?php foreach ((array) $current["clarity_by_device"] as $dev) : ?>
        <?php if (!is_array($dev)) { continue; } ?>
        <tr>
          <td><?php echo esc_html((string) ($dev["label"] ?? "")); ?></td>
          <td><?php echo (int) ($dev["sessions"] ?? 0); ?></td>
          <td><?php echo (int) ($dev["dead_clicks"] ?? 0); ?></td>
          <td><?php echo (int) ($dev["rage_clicks"] ?? 0); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
    <?php if (!empty($current["clarity_top_pages"])) : ?>
    <h4 style="margin:16px 0 8px;font-size:13px;"><?php esc_html_e("Top strony (URL)", "upsellio"); ?></h4>
    <table style="width:100%;font-size:12px;">
      <thead><tr><th style="text-align:left;">URL</th><th>Sesje</th><th>Użytk.</th></tr></thead>
      <tbody>
      <?php foreach (ups_audit_clarity_filter_ranked_rows((array) $current["clarity_top_pages"], 10) as $pg) : ?>
        <?php if (!is_array($pg)) { continue; } ?>
        <tr>
          <td style="max-width:320px;overflow:hidden;text-overflow:ellipsis;"><?php echo esc_html((string) ($pg["label"] ?? "")); ?></td>
          <td><?php echo (int) ($pg["sessions"] ?? 0); ?></td>
          <td><?php echo (int) ($pg["users"] ?? 0); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
    <?php if (!empty($current["clarity_by_channel"])) : ?>
    <h4 style="margin:16px 0 8px;font-size:13px;"><?php esc_html_e("Kanał (Clarity)", "upsellio"); ?></h4>
    <table style="width:100%;font-size:12px;">
      <thead><tr><th style="text-align:left;">Kanał</th><th>Sesje</th><th>Dead</th><th>Rage</th></tr></thead>
      <tbody>
      <?php foreach (array_slice((array) $current["clarity_by_channel"], 0, 8) as $ch) : ?>
        <?php if (!is_array($ch)) { continue; } ?>
        <tr>
          <td><?php echo esc_html((string) ($ch["label"] ?? "")); ?></td>
          <td><?php echo (int) ($ch["sessions"] ?? 0); ?></td>
          <td><?php echo (int) ($ch["dead_clicks"] ?? 0); ?></td>
          <td><?php echo (int) ($ch["rage_clicks"] ?? 0); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
    <?php if (!empty($current["clarity_by_source"])) : ?>
    <h4 style="margin:16px 0 8px;font-size:13px;"><?php esc_html_e("Źródło ruchu", "upsellio"); ?></h4>
    <table style="width:100%;font-size:12px;">
      <thead><tr><th style="text-align:left;">Źródło</th><th>Sesje</th></tr></thead>
      <tbody>
      <?php foreach (array_slice((array) $current["clarity_by_source"], 0, 8) as $src) : ?>
        <?php if (!is_array($src)) { continue; } ?>
        <tr><td><?php echo esc_html((string) ($src["label"] ?? "")); ?></td><td><?php echo (int) ($src["sessions"] ?? 0); ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
    <?php if (!empty($current["clarity_by_country"])) : ?>
    <h4 style="margin:16px 0 8px;font-size:13px;"><?php esc_html_e("Kraj / region", "upsellio"); ?></h4>
    <table style="width:100%;font-size:12px;">
      <thead><tr><th style="text-align:left;">Region</th><th>Sesje</th></tr></thead>
      <tbody>
      <?php foreach (ups_audit_clarity_filter_ranked_rows((array) $current["clarity_by_country"], 8) as $ct) : ?>
        <?php if (!is_array($ct)) { continue; } ?>
        <tr><td><?php echo esc_html((string) ($ct["label"] ?? "")); ?></td><td><?php echo (int) ($ct["sessions"] ?? 0); ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
    <?php if ((int) ($current["clarity_quickback"] ?? 0) > 0 || (int) ($current["clarity_script_errors"] ?? 0) > 0) : ?>
    <div style="display:flex;gap:12px;flex-wrap:wrap;font-size:12px;margin-top:8px;">
      <?php if ((int) ($current["clarity_quickback"] ?? 0) > 0) : ?>
        <span class="muted">Quickback: <strong><?php echo (int) ($current["clarity_quickback"] ?? 0); ?></strong></span>
      <?php endif; ?>
      <?php if ((int) ($current["clarity_script_errors"] ?? 0) > 0) : ?>
        <span class="muted">Błędy JS: <strong style="color:var(--danger,#dc2626);"><?php echo (int) ($current["clarity_script_errors"] ?? 0); ?></strong></span>
      <?php endif; ?>
    </div>
    <?php endif; ?>
    <p class="muted" style="margin:10px 0 0;font-size:11px;"><?php esc_html_e("Sync pobiera do 5 wymiarów (Device+Browser, URL, Source+Medium, kraj, kanał) w limicie 10 zapytań API/dzień. Historia max. 3 dni — nie da się jak GA4 (30/90).", "upsellio"); ?></p>
  </section>
  <?php endif; ?>

  <?php require __DIR__ . "/../partials/audit-intelligence.php"; ?>

  <?php
  $ups_audit_recommendations = (array) ($current["recommendations"] ?? []);
  require __DIR__ . "/partials/audit-recommendations.php";
  ?>

  <?php if (!empty($current["opportunities"])) : ?>
  <section class="card">
    <h3 style="margin:0 0 10px;font-size:14px;">Szanse SEO (quick wins)</h3>
    <div style="display:flex;flex-wrap:wrap;gap:8px;">
      <?php foreach ((array) $current["opportunities"] as $opp) : ?>
        <?php if (!is_array($opp)) { continue; } ?>
        <span style="font-size:12px;padding:6px 10px;background:var(--bg);border:1px solid var(--border);border-radius:999px;">
          <?php echo esc_html((string) ($opp["keyword"] ?? "")); ?>
          <span class="muted">~poz. <?php echo esc_html(number_format((float) ($opp["position"] ?? 0), 1)); ?></span>
        </span>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <section class="card">
    <?php if ($has_gsc_data || $has_ga4_data) : ?>
    <h3 style="margin:0 0 10px;font-size:14px;">Lejek: organic → sesje → konwersje → przychód</h3>
    <?php elseif ($has_ads_data) : ?>
    <h3 style="margin:0 0 10px;font-size:14px;">Lejek paid (Ads + Clarity) <span class="muted" style="font-size:11px;font-weight:400;">— GA4/GSC wymagają uprawnień</span></h3>
    <?php else : ?>
    <h3 style="margin:0 0 10px;font-size:14px;">Lejek konwersji</h3>
    <?php endif; ?>
    <div class="ups-audit-viz-grid ups-audit-viz-grid--2">
      <div class="ups-audit-funnel-viz">
        <?php
        if ($has_gsc_data || $has_ga4_data) {
            $funnel_steps = [
                ["GSC klik.", (int) ($current["gsc_clicks"] ?? 0), "gsc_clicks"],
                ["GA4 sesje", (int) ($current["ga4_sessions"] ?? 0), "ga4_sessions"],
                ["GA4 konw.", (int) ($current["ga4_conversions"] ?? 0), "ga4_conversions"],
                ["Przychód PLN", (float) ($current["ga4_revenue"] ?? 0), ""],
            ];
        } elseif ($has_ads_data) {
            $funnel_steps = [
                ["Klik. Ads", (int) ($current["ads_clicks"] ?? 0), "ads_clicks"],
                ["Konw. Ads", (float) ($current["ads_conversions"] ?? 0), "ads_conversions"],
                ["Sesje Clarity", (int) ($current["clarity_sessions"] ?? 0), ""],
                ["Koszt Ads", (float) ($current["ads_cost"] ?? 0), "ads_cost"],
            ];
        } else {
            $funnel_steps = [
                ["GSC klik.", (int) ($current["gsc_clicks"] ?? 0), "gsc_clicks"],
                ["GA4 sesje", (int) ($current["ga4_sessions"] ?? 0), "ga4_sessions"],
                ["GA4 konw.", (int) ($current["ga4_conversions"] ?? 0), "ga4_conversions"],
                ["Przychód PLN", (float) ($current["ga4_revenue"] ?? 0), ""],
            ];
        }
        foreach ($funnel_steps as $fi => $fstep) :
            $fval = is_numeric($fstep[1]) ? (float) $fstep[1] : 0;
            $fpct = $funnel_max > 0 ? min(100, round(($fval / $funnel_max) * 100)) : 0;
            $fd = $fstep[2] !== "" ? (float) ($deltas[$fstep[2]] ?? 0) : null;
            $fdisplay = is_numeric($fstep[1]) && $fstep[0] !== "Przychód PLN" && $fstep[0] !== "Koszt Ads"
                ? number_format((float) $fstep[1], $fstep[0] === "Konw. Ads" ? 1 : 0, ",", " ")
                : (is_numeric($fstep[1]) ? number_format((float) $fstep[1], 0, ",", " ") . " PLN" : (string) $fstep[1]);
            ?>
        <div class="ups-audit-funnel-step">
          <span class="muted"><?php echo esc_html($fstep[0]); ?></span>
          <div class="ups-audit-funnel-step__bar-wrap">
            <div class="ups-audit-funnel-step__bar <?php echo $fi <= 1 ? "ups-audit-funnel-step__bar--ads" : ""; ?>" style="width:<?php echo (int) max(4, $fpct); ?>%;"></div>
          </div>
          <div>
            <div class="ups-audit-funnel-step__val"><?php echo esc_html($fdisplay); ?></div>
            <?php if ($fd !== null) : ?>
              <div class="ups-audit-funnel-step__rate"><?php echo esc_html(ups_audit_format_delta($fd)); ?></div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="ups-audit-chart-panel">
        <p class="ups-audit-chart-panel__title"><?php esc_html_e("Wykres lejka", "upsellio"); ?></p>
        <div class="ups-crm-chart-box"><canvas id="ups-audit-chart-funnel"></canvas></div>
      </div>
    </div>
  </section>

  <div class="ups-audit-dash-charts-main">
    <section class="card">
      <h3 style="margin:0 0 8px;font-size:14px;"><?php echo $has_gsc_data ? "Kliknięcia GSC" : "Kliknięcia GSC"; ?> <span class="muted" style="font-size:11px;font-weight:400;"><?php echo $has_gsc_data ? "— najedź na wykres" : "— brak dostępu OAuth"; ?></span></h3>
      <div class="ups-crm-chart-box"><canvas id="ups-audit-chart-gsc"></canvas></div>
    </section>
    <section class="card">
      <h3 style="margin:0 0 8px;font-size:14px;"><?php echo $has_ga4_data ? "Sesje GA4" : ($has_ads_data ? "Kliknięcia Ads — dzienne" : "Sesje GA4"); ?></h3>
      <div class="ups-crm-chart-box"><canvas id="ups-audit-chart-ga4"></canvas></div>
    </section>
    <section class="card">
      <h3 style="margin:0 0 8px;font-size:14px;">Koszt Google Ads — dzienny</h3>
      <div class="ups-crm-chart-box"><canvas id="ups-audit-chart-ads"></canvas></div>
    </section>
    <?php if ($meta_mapped > 0 || !empty($ts_meta)) : ?>
    <section class="card">
      <h3 style="margin:0 0 8px;font-size:14px;">Koszt Meta Ads — dzienny</h3>
      <div class="ups-crm-chart-box"><canvas id="ups-audit-chart-meta"></canvas></div>
    </section>
    <?php endif; ?>
  </div>

  <?php if ($channel_ltv !== []) : ?>
  <section class="card">
    <div class="ups-audit-section-head">
      <h3><?php esc_html_e("Kanały GA4 × leady CRM (LTV / sesja)", "upsellio"); ?></h3>
    </div>
    <div class="ups-audit-viz-grid ups-audit-viz-grid--2" style="margin-bottom:12px;">
      <div class="ups-audit-chart-panel">
        <p class="ups-audit-chart-panel__title"><?php esc_html_e("LTV na sesję", "upsellio"); ?></p>
        <div class="ups-crm-chart-box"><canvas id="ups-audit-chart-ltv"></canvas></div>
      </div>
      <table style="width:100%;font-size:12px;border-collapse:collapse;align-self:start;">
      <thead><tr style="background:var(--bg);"><th style="text-align:left;padding:6px 8px;">Kanał</th><th>Sesje</th><th>Leady</th><th>Wygrane</th><th>LTV/sesja</th></tr></thead>
      <tbody>
      <?php foreach ($channel_ltv as $ltv_row) : ?>
        <?php if (!is_array($ltv_row)) { continue; } ?>
        <tr style="border-bottom:1px solid var(--border);">
          <td style="padding:6px 8px;"><?php echo esc_html((string) ($ltv_row["channel"] ?? "")); ?></td>
          <td style="text-align:center;"><?php echo (int) ($ltv_row["sessions"] ?? 0); ?></td>
          <td style="text-align:center;font-weight:600;"><?php echo (int) ($ltv_row["leads"] ?? 0); ?></td>
          <td style="text-align:center;"><?php echo (int) ($ltv_row["won"] ?? 0); ?></td>
          <td style="text-align:right;font-weight:700;"><?php echo esc_html(number_format((float) ($ltv_row["ltv_per_session"] ?? 0), 2, ",", " ")); ?> zł</td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </section>
  <?php endif; ?>

  <div class="ups-audit-viz-grid ups-audit-viz-grid--3">
    <section class="card">
      <div class="ups-audit-section-head"><h3><?php esc_html_e("Top frazy GSC", "upsellio"); ?></h3></div>
      <div class="ups-crm-chart-box" style="height:200px;margin-bottom:10px;"><canvas id="ups-audit-chart-keywords"></canvas></div>
      <details style="font-size:11px;">
        <summary style="cursor:pointer;" class="muted"><?php esc_html_e("Tabela szczegółów", "upsellio"); ?></summary>
        <table style="width:100%;font-size:11px;margin-top:8px;">
          <thead><tr><th>Fraza</th><th>Klik.</th><th>Poz.</th></tr></thead>
          <tbody>
          <?php foreach (array_slice((array) ($current["top_keywords"] ?? []), 0, 6) as $kw) : ?>
            <?php if (!is_array($kw)) { continue; } ?>
            <tr>
              <td><?php echo esc_html((string) ($kw["keyword"] ?? "")); ?></td>
              <td><?php echo (int) ($kw["clicks"] ?? 0); ?></td>
              <td><?php echo esc_html(number_format((float) ($kw["position"] ?? 0), 1, ",", " ")); ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </details>
    </section>
    <section class="card">
      <div class="ups-audit-section-head"><h3><?php esc_html_e("Kanały GA4", "upsellio"); ?></h3></div>
      <div class="ups-crm-chart-box" style="height:200px;margin-bottom:10px;"><canvas id="ups-audit-chart-channels"></canvas></div>
      <details style="font-size:11px;">
        <summary style="cursor:pointer;" class="muted"><?php esc_html_e("Tabela szczegółów", "upsellio"); ?></summary>
        <table style="width:100%;font-size:11px;margin-top:8px;">
          <thead><tr><th>Źródło</th><th>Sesje</th><th>Konw.</th></tr></thead>
          <tbody>
          <?php foreach (array_slice((array) ($current["channels"] ?? []), 0, 6) as $ch) : ?>
            <?php if (!is_array($ch)) { continue; } ?>
            <?php
              $ch_src = (string) ($ch["source"] ?? "");
              $ch_med = (string) ($ch["medium"] ?? "");
              $ch_bad = function_exists("ups_audit_ga4_is_not_set_source")
                  && ups_audit_ga4_is_not_set_source($ch_src, $ch_med);
              ?>
            <tr<?php echo $ch_bad ? ' style="background:#fef2f2;"' : ""; ?>>
              <td><?php echo esc_html($ch_src . " / " . $ch_med); ?></td>
              <td><?php echo (int) ($ch["sessions"] ?? 0); ?></td>
              <td><?php echo (int) ($ch["conversions"] ?? 0); ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </details>
    </section>
    <section class="card">
      <div class="ups-audit-section-head"><h3><?php esc_html_e("Kampanie Google Ads", "upsellio"); ?></h3></div>
      <div class="ups-crm-chart-box" style="height:200px;margin-bottom:10px;"><canvas id="ups-audit-chart-campaigns"></canvas></div>
      <details style="font-size:11px;">
        <summary style="cursor:pointer;" class="muted"><?php esc_html_e("Tabela szczegółów", "upsellio"); ?></summary>
        <table style="width:100%;font-size:11px;margin-top:8px;">
          <thead><tr><th>Kampania</th><th>Koszt</th><th>CPA</th></tr></thead>
          <tbody>
          <?php foreach (array_slice((array) ($current["campaigns"] ?? []), 0, 6) as $camp) : ?>
            <?php if (!is_array($camp)) { continue; } ?>
            <?php
            $camp_cost = (float) ($camp["cost"] ?? $camp["cost_pln"] ?? 0);
            $camp_conv = (float) ($camp["conversions"] ?? 0);
            $camp_cpa = (float) ($camp["cpa"] ?? ($camp_conv > 0 ? $camp_cost / $camp_conv : 0));
            ?>
            <tr>
              <td><?php echo esc_html((string) ($camp["name"] ?? "")); ?></td>
              <td><?php echo esc_html(number_format($camp_cost, 0, ",", " ")); ?></td>
              <td><?php echo $camp_cpa > 0 ? esc_html(number_format($camp_cpa, 0, ",", " ") . " zł") : "—"; ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </details>
    </section>
    <?php if ($meta_mapped > 0 || !empty($current["meta_campaigns"])) : ?>
    <section class="card">
      <h3 style="margin:0 0 8px;font-size:14px;">Kampanie Meta Ads</h3>
      <table style="width:100%;font-size:12px;">
        <thead><tr><th>Kampania</th><th>Koszt</th><th>Klik.</th></tr></thead>
        <tbody>
        <?php foreach ((array) ($current["meta_campaigns"] ?? []) as $camp) : ?>
          <?php if (!is_array($camp)) { continue; } ?>
          <tr>
            <td><?php echo esc_html((string) ($camp["name"] ?? "")); ?></td>
            <td><?php echo esc_html(number_format((float) ($camp["cost"] ?? 0), 0, ",", " ")); ?></td>
            <td><?php echo (int) ($camp["clicks"] ?? 0); ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($current["meta_campaigns"])) : ?><tr><td colspan="3" class="muted">Brak danych — zmapuj konto Meta i uruchom sync.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </section>
    <?php endif; ?>
  </div>

  <section class="card">
    <h3 style="margin:0 0 8px;font-size:14px;">Zasoby i status sync</h3>
    <table>
      <thead><tr><th>Zasób</th><th>Typ</th><th>Status</th><th>Ostatni sync</th><th></th></tr></thead>
      <tbody>
      <?php foreach ((array) ($current["resources"] ?? []) as $res) : ?>
        <?php if (!is_array($res)) { continue; } ?>
        <?php
        $h = (array) ($res["health"] ?? []);
        $st = (string) ($h["status"] ?? "unknown");
        $st_label = $st === "ok" ? "OK" : ($st === "warn" ? "Uwaga" : ($st === "error" ? "Błąd" : "—"));
        ?>
        <tr>
          <td><?php echo esc_html((string) ($res["title"] ?? "")); ?></td>
          <td><?php echo esc_html(strtoupper((string) ($res["type"] ?? ""))); ?></td>
          <td><?php echo esc_html($st_label); ?><?php if (!empty($h["label"])) : ?> <span class="muted">— <?php echo esc_html((string) $h["label"]); ?></span><?php endif; ?></td>
          <td><?php echo esc_html((string) ($res["last_sync"] ?? "")); ?></td>
          <td><button type="button" class="btn alt" style="padding:4px 8px;font-size:12px;" data-ups-audit-res-sync="<?php echo (int) ($res["id"] ?? 0); ?>">Sync</button></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($current["resources"])) : ?><tr><td colspan="5" class="muted">Brak zmapowanych zasobów — użyj „Mapuj zasoby” na liście klientów.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </section>
</div>
<script type="application/json" id="ups-audit-dash-ts-gsc"><?php echo wp_json_encode($ts_gsc); ?></script>
<script type="application/json" id="ups-audit-dash-ts-ga4"><?php echo wp_json_encode($ts_ga4); ?></script>
<script type="application/json" id="ups-audit-dash-ts-ads"><?php echo wp_json_encode($ts_ads); ?></script>
<script type="application/json" id="ups-audit-dash-ts-ads-clicks"><?php echo wp_json_encode($ts_ads_clicks); ?></script>
<script type="application/json" id="ups-audit-dash-ts-meta"><?php echo wp_json_encode($ts_meta); ?></script>
<script type="application/json" id="ups-audit-dash-chart-mode"><?php echo wp_json_encode([
    "has_gsc" => $has_gsc_data,
    "has_ga4" => $has_ga4_data,
    "has_ads" => $has_ads_data,
]); ?></script>
<script type="application/json" id="ups-audit-dash-funnel"><?php echo wp_json_encode(
    ($has_gsc_data || $has_ga4_data)
        ? [
            "gsc_clicks" => (int) ($current["gsc_clicks"] ?? 0),
            "ga4_sessions" => (int) ($current["ga4_sessions"] ?? 0),
            "ga4_conversions" => (int) ($current["ga4_conversions"] ?? 0),
        ]
        : [
            "ads_clicks" => (int) ($current["ads_clicks"] ?? 0),
            "ads_conversions" => (float) ($current["ads_conversions"] ?? 0),
            "ads_cost" => (float) ($current["ads_cost"] ?? 0),
            "clarity_sessions" => (int) ($current["clarity_sessions"] ?? 0),
        ]
); ?></script>
<script type="application/json" id="ups-audit-dash-campaigns"><?php echo wp_json_encode(array_slice((array) ($current["campaigns"] ?? []), 0, 8)); ?></script>
<script type="application/json" id="ups-audit-dash-channels"><?php echo wp_json_encode($dash_channels_chart); ?></script>
<script type="application/json" id="ups-audit-dash-keywords"><?php echo wp_json_encode($dash_keywords_chart); ?></script>
<script type="application/json" id="ups-audit-dash-campaigns-cpa"><?php echo wp_json_encode($dash_campaigns_cpa); ?></script>
<script type="application/json" id="ups-audit-dash-ltv"><?php echo wp_json_encode($dash_ltv_chart); ?></script>
