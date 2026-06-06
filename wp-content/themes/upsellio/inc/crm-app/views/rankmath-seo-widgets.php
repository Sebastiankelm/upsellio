<?php

if (!defined("ABSPATH")) {
    exit;
}

$rm_days = isset($range_days) ? (int) $range_days : 30;
$rm_status = function_exists("upsellio_rankmath_connection_status") ? upsellio_rankmath_connection_status() : [];
$rm_summary = function_exists("upsellio_rankmath_get_dashboard_summary") ? upsellio_rankmath_get_dashboard_summary($rm_days) : [];
$rm_top_pages = function_exists("upsellio_rankmath_top_pages") ? upsellio_rankmath_top_pages(12, $rm_days) : [];
$rm_quick = function_exists("upsellio_rankmath_quick_win_keywords") ? upsellio_rankmath_quick_win_keywords(15, $rm_days) : [];
$gsc_source = (string) get_option("upsellio_keyword_metrics_source", "");
$ga4_source = (string) get_option("ups_automation_ga4_source", "");
$gsc_last = (string) get_option("upsellio_keyword_metrics_last_sync", "");
$ga4_last = (string) get_option("ups_automation_ga4_last_sync", "");
$sync_nonce = wp_create_nonce("ups_crm_app_action");

function ups_rm_fmt_delta(float $diff): string
{
    if (abs($diff) < 0.05) {
        return "0%";
    }

    return ($diff > 0 ? "+" : "") . rtrim(rtrim(number_format($diff, 1, ",", " "), "0"), ",") . "%";
}

function ups_rm_delta_color(float $diff, bool $invert = false): string
{
    $up = $diff > 0;
    if ($invert) {
        $up = !$up;
    }

    return $up ? "var(--success,#16a34a)" : ($diff < 0 ? "var(--danger,#dc2626)" : "var(--text-3)");
}
?>
<section class="card" id="ups-rm-seo-panel">
  <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:14px;">
    <div>
      <h3 style="margin:0 0 4px;font-size:15px;">Search Console — jak w Rank Math</h3>
      <p class="muted" style="margin:0;font-size:12px;">
        <?php if (!empty($rm_status["gsc"])) : ?>
          Połączone przez Rank Math
          <?php if (!empty($rm_status["profile"])) : ?> · <?php echo esc_html($rm_status["profile"]); ?><?php endif; ?>
        <?php else : ?>
          Brak GSC w Rank Math — użyj OAuth Upsellio lub połącz Rank Math → Analytics.
        <?php endif; ?>
        · GSC: <?php echo esc_html($gsc_last !== "" ? $gsc_last : "brak sync"); ?> (<?php echo esc_html($gsc_source !== "" ? $gsc_source : "—"); ?>)
        · GA4: <?php echo esc_html($ga4_last !== "" ? $ga4_last : "brak"); ?> (<?php echo esc_html($ga4_source !== "" ? $ga4_source : "—"); ?>)
      </p>
    </div>
    <button type="button" class="btn alt" id="ups-rm-sync-btn" style="font-size:12px;padding:6px 14px;"
            data-nonce="<?php echo esc_attr($sync_nonce); ?>"
            data-days="<?php echo (int) $rm_days; ?>">
      <i class="ti ti-refresh" aria-hidden="true"></i> Sync Google (RM + Upsellio)
    </button>
  </div>
  <div id="ups-rm-sync-msg" style="display:none;margin-bottom:12px;padding:8px 12px;border-radius:var(--r-sm);font-size:12px;"></div>

  <?php if (!empty($rm_summary)) : ?>
  <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:16px;">
    <?php
    $blocks = [
        ["label" => "Kliknięcia", "key" => "clicks", "invert" => false, "fmt" => "int"],
        ["label" => "Wyświetlenia", "key" => "impressions", "invert" => false, "fmt" => "int"],
        ["label" => "Śr. pozycja", "key" => "position", "invert" => true, "fmt" => "pos"],
        ["label" => "Frazy (unikalne)", "key" => "keywords", "invert" => false, "fmt" => "int"],
    ];
    foreach ($blocks as $b) :
        $m = (array) ($rm_summary[$b["key"]] ?? []);
        $total = (float) ($m["total"] ?? 0);
        $diff = (float) ($m["difference"] ?? 0);
        $val = $b["fmt"] === "pos" ? number_format($total, 1, ",", " ") : number_format($total, 0, ",", " ");
        ?>
    <div style="background:var(--bg);border:1px solid var(--border);border-radius:var(--r-md);padding:12px;text-align:center;">
      <div style="font-size:10px;font-weight:700;letter-spacing:.5px;color:var(--text-3);text-transform:uppercase;"><?php echo esc_html($b["label"]); ?></div>
      <div style="font-size:22px;font-weight:800;margin:4px 0;"><?php echo esc_html($val); ?></div>
      <div style="font-size:11px;font-weight:600;color:<?php echo esc_attr(ups_rm_delta_color($diff, $b["invert"])); ?>"><?php echo esc_html(ups_rm_fmt_delta($diff)); ?> vs poprz.</div>
    </div>
    <?php endforeach; ?>
  </div>
  <p class="muted" style="margin:-8px 0 14px;font-size:11px;">Źródło KPI: <?php echo esc_html((string) ($rm_summary["source"] ?? "—")); ?></p>
  <?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
    <div style="border:1px solid var(--border);border-radius:var(--r-md);padding:14px;">
      <div style="font-weight:700;font-size:13px;margin-bottom:8px;">Szybkie wygrane (poz. 4–20)</div>
      <p class="muted" style="margin:0 0 8px;font-size:11px;">Frazy z dużą liczbą wyświetleń — kandydaci do rozbudowy treści (odpowiednik Striking Distance).</p>
      <?php if (empty($rm_quick)) : ?>
        <p class="muted" style="margin:0;font-size:12px;">Brak danych — uruchom sync.</p>
      <?php else : ?>
        <table style="width:100%;border-collapse:collapse;font-size:11px;">
          <thead><tr style="border-bottom:1px solid var(--border)">
            <th style="text-align:left;padding:4px 6px">Fraza</th>
            <th style="text-align:center;padding:4px 6px">Poz.</th>
            <th style="text-align:right;padding:4px 6px">Wyśw.</th>
          </tr></thead>
          <tbody>
          <?php foreach ($rm_quick as $q) : ?>
            <tr style="border-bottom:1px solid var(--border)">
              <td style="padding:4px 6px;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?php echo esc_attr((string) ($q["keyword"] ?? "")); ?>">
                <?php echo esc_html((string) ($q["keyword"] ?? "")); ?>
              </td>
              <td style="text-align:center;padding:4px 6px;"><?php echo esc_html(number_format((float) ($q["position"] ?? 0), 1, ",", " ")); ?></td>
              <td style="text-align:right;padding:4px 6px;"><?php echo (int) ($q["impressions"] ?? 0); ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
    <div style="border:1px solid var(--border);border-radius:var(--r-md);padding:14px;">
      <div style="font-weight:700;font-size:13px;margin-bottom:8px;">Top strony (GSC)</div>
      <?php if (empty($rm_top_pages)) : ?>
        <p class="muted" style="margin:0;font-size:12px;">Brak danych — sync lub cache Rank Math (<code>rank_math_analytics_objects</code>).</p>
      <?php else : ?>
        <table style="width:100%;border-collapse:collapse;font-size:11px;">
          <thead><tr style="border-bottom:1px solid var(--border)">
            <th style="text-align:left;padding:4px 6px">URL</th>
            <th style="text-align:right;padding:4px 6px">Klik.</th>
            <th style="text-align:right;padding:4px 6px">CTR</th>
          </tr></thead>
          <tbody>
          <?php foreach ($rm_top_pages as $p) : ?>
            <?php $path = wp_parse_url((string) ($p["url"] ?? ""), PHP_URL_PATH); ?>
            <tr style="border-bottom:1px solid var(--border)">
              <td style="padding:4px 6px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                <a href="<?php echo esc_url((string) ($p["url"] ?? "")); ?>" target="_blank" rel="noopener noreferrer" style="color:var(--teal)"><?php echo esc_html($path ?: (string) ($p["url"] ?? "")); ?></a>
              </td>
              <td style="text-align:right;padding:4px 6px;"><?php echo (int) ($p["clicks"] ?? 0); ?></td>
              <td style="text-align:right;padding:4px 6px;"><?php echo esc_html(number_format((float) ($p["ctr"] ?? 0), 2, ",", " ")); ?>%</td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>

  <div style="margin-top:14px;">
    <div style="font-weight:700;font-size:13px;margin-bottom:6px;">Trend GSC (<?php echo (int) $rm_days; ?> dni)</div>
    <div class="ups-crm-chart-box" style="height:160px;"><canvas id="ups-rm-gsc-trend"></canvas></div>
  </div>
</section>
<script>
(function () {
  var btn = document.getElementById("ups-rm-sync-btn");
  var msg = document.getElementById("ups-rm-sync-msg");
  if (btn) {
    btn.addEventListener("click", function () {
      btn.disabled = true;
      if (msg) { msg.style.display = "block"; msg.style.background = "var(--bg)"; msg.textContent = "Synchronizacja…"; }
      var fd = new FormData();
      fd.append("action", "upsellio_google_sync_now");
      fd.append("nonce", btn.getAttribute("data-nonce") || "");
      fd.append("days", btn.getAttribute("data-days") || "30");
      fetch(window.CRM_AJAX_URL || "<?php echo esc_js(admin_url('admin-ajax.php')); ?>", { method: "POST", body: fd, credentials: "same-origin" })
        .then(function (r) { return r.json(); })
        .then(function (d) {
          btn.disabled = false;
          if (msg) {
            msg.style.display = "block";
            if (d && d.success) {
              msg.style.background = "#e8f5e9";
              msg.style.color = "var(--success)";
              var g = d.data && d.data.log && d.data.log.gsc ? d.data.log.gsc : {};
              var a = d.data && d.data.log && d.data.log.ga4 ? d.data.log.ga4 : {};
              msg.textContent = "OK — GSC: " + (g.count || 0) + " wierszy (" + (g.source || "") + "), GA4: " + (a.count || 0) + " (" + (a.source || "") + ")";
              setTimeout(function () { window.location.reload(); }, 2000);
            } else {
              msg.style.background = "#fef2f2";
              msg.style.color = "var(--danger)";
              msg.textContent = "Błąd sync.";
            }
          }
        })
        .catch(function () {
          btn.disabled = false;
          if (msg) { msg.textContent = "Błąd sieci"; msg.style.background = "#fef2f2"; }
        });
    });
  }
  function mountRmGscTrend() {
  var canvas = document.getElementById("ups-rm-gsc-trend");
  if (!canvas || !window.Chart) return;
  var series = <?php
    $series = function_exists("upsellio_rankmath_gsc_daily_series") ? upsellio_rankmath_gsc_daily_series($rm_days) : [];
    $labels = array_keys($series);
    $clicks = [];
    $impr = [];
    foreach ($labels as $lb) {
        $clicks[] = (int) (($series[$lb]["clicks"] ?? 0));
        $impr[] = (int) (($series[$lb]["impressions"] ?? 0));
    }
    echo wp_json_encode(["labels" => $labels, "clicks" => $clicks, "impressions" => $impr]);
    ?>;
  if (!series.labels || !series.labels.length) return;
  new Chart(canvas, {
    type: "line",
    data: {
      labels: series.labels,
      datasets: [
        { label: "Kliknięcia", data: series.clicks, borderColor: "#0ABFA3", tension: 0.25, yAxisID: "y" },
        { label: "Wyświetlenia", data: series.impressions, borderColor: "#8b5cf6", tension: 0.25, yAxisID: "y1" }
      ]
    },
    options: {
      responsive: true,
      interaction: { mode: "index", intersect: false },
      plugins: { legend: { position: "bottom", labels: { boxWidth: 10, font: { size: 10 } } } },
      scales: {
        y: { type: "linear", position: "left", ticks: { maxTicksLimit: 5 } },
        y1: { type: "linear", position: "right", grid: { drawOnChartArea: false }, ticks: { maxTicksLimit: 5 } }
      }
    }
  });
  }
  if (typeof window.upsCrmScheduleChartInit === "function") {
    window.upsCrmScheduleChartInit(mountRmGscTrend);
  } else if (window.upsCrmChart) {
    window.upsCrmChart.whenReady(mountRmGscTrend);
  } else if (window.Chart) {
    mountRmGscTrend();
  }
})();
</script>
