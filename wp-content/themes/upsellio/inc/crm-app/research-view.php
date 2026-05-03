<?php
if (!defined("ABSPATH")) {
    exit;
}
$rv_research_tab = isset($research_tab) && is_string($research_tab) ? $research_tab : "keywords";
$rv_ads_ready = function_exists("upsellio_google_ads_api_ready") && upsellio_google_ads_api_ready();
$rv_campaigns = get_option("ups_ads_campaigns_data", []);
if (!is_array($rv_campaigns)) {
    $rv_campaigns = [];
}
$rv_synced_at = (string) get_option("ups_ads_campaigns_synced", "");
$rv_nonce = wp_create_nonce("ups_crm_app_action");
$rv_admin_url = function_exists("upsellio_site_analytics_admin_url") ? upsellio_site_analytics_admin_url() : admin_url("admin.php");
?>
            <section class="card" style="padding:0;overflow:hidden">
              <div style="padding:18px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:16px;flex-wrap:wrap">
                <div>
                  <h2 style="margin:0;font-size:18px"><?php esc_html_e("Research Centrum", "upsellio"); ?></h2>
                  <div style="font-size:12px;color:var(--text-3);margin-top:2px">
                    <?php esc_html_e("Keyword Planner · Kampanie Google Ads · Analiza konkurencji", "upsellio"); ?>
                  </div>
                </div>
                <?php if (!$rv_ads_ready) : ?>
                <div style="margin-left:auto;padding:8px 14px;background:#fef3c7;border:1px solid #fde68a;border-radius:8px;font-size:12px;color:#92400e;display:flex;align-items:center;gap:8px">
                  <?php esc_html_e("Google Ads API nie skonfigurowane —", "upsellio"); ?>
                  <a href="<?php echo esc_url($rv_admin_url); ?>" style="font-weight:700;color:#92400e"><?php esc_html_e("Konfiguruj →", "upsellio"); ?></a>
                </div>
                <?php endif; ?>
              </div>

              <div style="display:flex;border-bottom:1px solid var(--border);background:var(--bg)">
                <?php
                $rv_tabs = [
                    "keywords" => "🔑 " . __("Keyword Research", "upsellio"),
                    "campaigns" => "📊 " . __("Kampanie Ads", "upsellio"),
                    "competition" => "⚔ " . __("Konkurencja", "upsellio"),
                    "client_plan" => "👤 " . __("Plan dla klienta", "upsellio"),
                ];
                foreach ($rv_tabs as $rv_k => $rv_lab) :
                    $rv_active = $rv_research_tab === $rv_k;
                    $rv_tab_url = add_query_arg(["view" => "research", "research_tab" => $rv_k], home_url("/crm-app/"));
                    ?>
                <a href="<?php echo esc_url($rv_tab_url); ?>"
                   style="padding:12px 20px;font-size:13px;font-weight:<?php echo $rv_active ? "700" : "500"; ?>;
                          color:<?php echo $rv_active ? "var(--teal)" : "var(--text-3)"; ?>;
                          border-bottom:2px solid <?php echo $rv_active ? "var(--teal)" : "transparent"; ?>;
                          text-decoration:none;white-space:nowrap;transition:all .14s">
                  <?php echo esc_html($rv_lab); ?>
                </a>
                <?php endforeach; ?>
              </div>

              <?php if ($rv_research_tab === "keywords") : ?>
              <div style="padding:20px 24px">
                <div style="background:#f8f9f4;border:1px solid var(--border);border-radius:12px;padding:18px;margin-bottom:20px">
                  <div style="font-size:13px;font-weight:700;margin-bottom:12px"><?php esc_html_e("Wpisz frazy seed — Keyword Planner + dane GSC", "upsellio"); ?></div>
                  <div style="display:grid;grid-template-columns:1fr auto auto;gap:10px;align-items:start">
                    <div>
                      <textarea id="kw-seeds-input" style="width:100%;padding:10px;border:1px solid var(--border);border-radius:8px;font:inherit;font-size:13px;min-height:80px;resize:vertical" placeholder="google ads agencja&#10;kampanie google ads"></textarea>
                      <div style="font-size:11px;color:var(--text-3);margin-top:4px"><?php esc_html_e("Każda linia = fraza (max 20).", "upsellio"); ?></div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:8px">
                      <select id="kw-geo" style="padding:8px;border:1px solid var(--border);border-radius:8px;font:inherit;font-size:12px">
                        <option value="2616"><?php esc_html_e("Polska", "upsellio"); ?></option>
                        <option value="1009580">Warszawa</option>
                        <option value="1009773">Kraków</option>
                      </select>
                      <label style="font-size:11px;color:var(--text-3);display:flex;align-items:center;gap:6px;cursor:pointer">
                        <input type="checkbox" id="kw-force-refresh" /> <?php esc_html_e("Odśwież cache", "upsellio"); ?>
                      </label>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px">
                      <button type="button" id="kw-research-btn" class="btn" style="white-space:nowrap" <?php echo !$rv_ads_ready ? 'disabled title="' . esc_attr__("Skonfiguruj Google Ads API", "upsellio") . '"' : ""; ?>><?php esc_html_e("🔍 Szukaj fraz", "upsellio"); ?></button>
                      <button type="button" id="kw-cluster-btn" class="btn alt" style="white-space:nowrap;display:none"><?php esc_html_e("✨ Klastruj AI", "upsellio"); ?></button>
                    </div>
                  </div>
                </div>
                <div id="kw-status" style="display:none;padding:12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;font-size:12px;color:#16a34a;margin-bottom:16px"></div>
                <div id="kw-error" style="display:none;padding:12px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;font-size:12px;color:#dc2626;margin-bottom:16px"></div>
                <div id="kw-results" style="display:none">
                  <div style="display:flex;gap:10px;align-items:center;margin-bottom:12px;flex-wrap:wrap">
                    <div style="font-size:12px;color:var(--text-3)"><?php esc_html_e("Filtruj:", "upsellio"); ?></div>
                    <button type="button" class="kw-filter-btn btn alt is-active" data-filter="all"><?php esc_html_e("Wszystkie", "upsellio"); ?></button>
                    <button type="button" class="kw-filter-btn btn alt" data-filter="gsc"><?php esc_html_e("Tylko w GSC", "upsellio"); ?></button>
                    <button type="button" class="kw-filter-btn btn alt" data-filter="gaps"><?php esc_html_e("Luki SEO", "upsellio"); ?></button>
                    <button type="button" class="kw-filter-btn btn alt" data-filter="quick_win"><?php esc_html_e("Quick wins Ads", "upsellio"); ?></button>
                    <button type="button" class="kw-filter-btn btn alt" data-filter="low_cpc"><?php esc_html_e("Niski CPC", "upsellio"); ?></button>
                    <div style="margin-left:auto;font-size:11px;color:var(--text-3)" id="kw-count"></div>
                  </div>
                  <div style="overflow-x:auto">
                    <table style="width:100%;border-collapse:collapse;font-size:12px" id="kw-table">
                      <thead>
                        <tr style="border-bottom:2px solid var(--border);background:#f8f9f4">
                          <th style="text-align:left;padding:8px 12px"><?php esc_html_e("Fraza", "upsellio"); ?></th>
                          <th style="text-align:right;padding:8px"><?php esc_html_e("Wolumen/mies.", "upsellio"); ?></th>
                          <th style="text-align:right;padding:8px"><?php esc_html_e("CPC (PLN)", "upsellio"); ?></th>
                          <th style="text-align:center;padding:8px"><?php esc_html_e("Konkur.", "upsellio"); ?></th>
                          <th style="text-align:right;padding:8px"><?php esc_html_e("Poz. GSC", "upsellio"); ?></th>
                          <th style="text-align:right;padding:8px"><?php esc_html_e("Wyśw. GSC", "upsellio"); ?></th>
                          <th style="text-align:center;padding:8px"><?php esc_html_e("Opportunity", "upsellio"); ?></th>
                          <th style="text-align:center;padding:8px"><?php esc_html_e("Kanał", "upsellio"); ?></th>
                          <th style="text-align:center;padding:8px"><?php esc_html_e("Trend", "upsellio"); ?></th>
                        </tr>
                      </thead>
                      <tbody id="kw-tbody"></tbody>
                    </table>
                  </div>
                  <div id="kw-clusters" style="display:none;margin-top:24px">
                    <div style="font-size:14px;font-weight:700;margin-bottom:12px"><?php esc_html_e("Klastry tematyczne (AI)", "upsellio"); ?></div>
                    <div id="kw-clusters-content"></div>
                  </div>
                </div>
              </div>

              <?php elseif ($rv_research_tab === "campaigns") : ?>
              <div style="padding:20px 24px">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;flex-wrap:wrap">
                  <div style="font-size:12px;color:var(--text-3)">
                    <?php if ($rv_synced_at !== "") : ?>
                      <?php esc_html_e("Ostatnia synchronizacja:", "upsellio"); ?> <strong><?php echo esc_html(wp_date("d.m.Y H:i", strtotime($rv_synced_at))); ?></strong>
                      · <?php echo count($rv_campaigns); ?> <?php esc_html_e("kampanii", "upsellio"); ?>
                    <?php else : ?>
                      <?php esc_html_e("Brak danych — zsynchronizuj lub poczekaj na cron.", "upsellio"); ?>
                    <?php endif; ?>
                  </div>
                  <button type="button" id="ads-sync-btn" class="btn" data-nonce="<?php echo esc_attr($rv_nonce); ?>"
                    <?php echo !$rv_ads_ready ? 'disabled' : ""; ?>><?php esc_html_e("↻ Synchronizuj teraz", "upsellio"); ?></button>
                  <div id="ads-sync-status" style="font-size:12px;color:var(--text-3)"></div>
                </div>
                <?php if (!empty($rv_campaigns)) : ?>
                <?php
                $rv_total_cost = 0.0;
                $rv_total_clicks = 0;
                $rv_total_conv = 0.0;
                foreach ($rv_campaigns as $rv_c) {
                    $rv_total_cost += (float) ($rv_c["cost_pln"] ?? 0);
                    $rv_total_clicks += (int) ($rv_c["clicks"] ?? 0);
                    $rv_total_conv += (float) ($rv_c["conversions"] ?? 0);
                }
                $rv_avg_cpc = $rv_total_clicks > 0 ? round($rv_total_cost / $rv_total_clicks, 2) : 0;
                $rv_avg_cpa = $rv_total_conv > 0 ? round($rv_total_cost / $rv_total_conv, 2) : 0;
                $rv_kpis = [
                    [__("Łączny koszt", "upsellio"), number_format($rv_total_cost, 0, ",", " ") . " PLN", $rv_total_cost > 0 ? "#0f766e" : ""],
                    [__("Kliknięcia", "upsellio"), number_format($rv_total_clicks, 0, ",", " "), ""],
                    [__("Konwersje", "upsellio"), number_format($rv_total_conv, 1, ",", " "), $rv_total_conv > 0 ? "#0f766e" : ""],
                    [__("Śr. CPC", "upsellio"), $rv_avg_cpc . " PLN", ""],
                    [__("CPA", "upsellio"), $rv_avg_cpa > 0 ? $rv_avg_cpa . " PLN" : "—", $rv_avg_cpa > 0 && $rv_avg_cpa < 500 ? "#0f766e" : ""],
                ];
                ?>
                <div style="display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin-bottom:20px">
                  <?php foreach ($rv_kpis as $rv_k) : ?>
                  <div style="background:#f8f9f4;border:1px solid var(--border);border-radius:10px;padding:12px 16px">
                    <div style="font-size:18px;font-weight:800;letter-spacing:-.5px;color:<?php echo $rv_k[2] !== "" ? esc_attr($rv_k[2]) : "var(--text)"; ?>">
                      <?php echo esc_html($rv_k[1]); ?>
                    </div>
                    <div style="font-size:10px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.4px;margin-top:2px"><?php echo esc_html($rv_k[0]); ?></div>
                  </div>
                  <?php endforeach; ?>
                </div>
                <table style="width:100%;border-collapse:collapse;font-size:12px">
                  <thead>
                    <tr style="border-bottom:2px solid var(--border);background:#f8f9f4">
                      <th style="text-align:left;padding:8px 12px"><?php esc_html_e("Kampania", "upsellio"); ?></th>
                      <th style="text-align:right;padding:8px"><?php esc_html_e("Koszt (PLN)", "upsellio"); ?></th>
                      <th style="text-align:right;padding:8px"><?php esc_html_e("Kliknięcia", "upsellio"); ?></th>
                      <th style="text-align:right;padding:8px"><?php esc_html_e("Konwersje", "upsellio"); ?></th>
                      <th style="text-align:right;padding:8px"><?php esc_html_e("CTR", "upsellio"); ?></th>
                      <th style="text-align:right;padding:8px"><?php esc_html_e("CPC (PLN)", "upsellio"); ?></th>
                      <th style="text-align:right;padding:8px"><?php esc_html_e("CPA (PLN)", "upsellio"); ?></th>
                      <th style="text-align:center;padding:8px"><?php esc_html_e("Typ", "upsellio"); ?></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($rv_campaigns as $camp) : ?>
                      <?php
                      $cpa = ($camp["conversions"] ?? 0) > 0 ? round((float) $camp["cost_pln"] / (float) $camp["conversions"], 0) : 0;
                      $is_good = ($camp["conversions"] ?? 0) > 0 && $cpa < 1000;
                      ?>
                    <tr style="border-bottom:1px solid var(--border);<?php echo $is_good ? "background:#f0fdf4;" : ""; ?>">
                      <td style="padding:10px 12px;font-weight:600"><?php echo esc_html((string) ($camp["name"] ?? "")); ?></td>
                      <td style="padding:10px 8px;text-align:right"><?php echo esc_html(number_format((float) ($camp["cost_pln"] ?? 0), 0, ",", " ")); ?></td>
                      <td style="padding:10px 8px;text-align:right"><?php echo esc_html(number_format((int) ($camp["clicks"] ?? 0), 0, ",", " ")); ?></td>
                      <td style="padding:10px 8px;text-align:right"><?php echo ($camp["conversions"] ?? 0) > 0 ? esc_html(number_format((float) $camp["conversions"], 1, ",", " ")) : "—"; ?></td>
                      <td style="padding:10px 8px;text-align:right"><?php echo esc_html((string) ($camp["ctr"] ?? "0")); ?>%</td>
                      <td style="padding:10px 8px;text-align:right"><?php echo esc_html((string) ($camp["avg_cpc_pln"] ?? "0")); ?></td>
                      <td style="padding:10px 8px;text-align:right"><?php echo $cpa > 0 ? esc_html((string) $cpa) . " PLN" : "—"; ?></td>
                      <td style="padding:10px 8px;text-align:center"><span style="font-size:10px;padding:2px 7px;border-radius:99px;background:#e0e7ff;color:#3730a3;font-weight:700"><?php echo esc_html((string) ($camp["type"] ?? "")); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
                <?php else : ?>
                <div style="text-align:center;padding:48px;color:var(--text-3)">
                  <div style="font-size:14px;font-weight:600;margin-bottom:6px"><?php esc_html_e("Brak danych kampanii", "upsellio"); ?></div>
                  <div style="font-size:12px"><?php esc_html_e('Kliknij „Synchronizuj teraz” lub poczekaj na cron dzienny.', "upsellio"); ?></div>
                </div>
                <?php endif; ?>
              </div>

              <?php elseif ($rv_research_tab === "competition") : ?>
              <div style="padding:20px 24px">
                <div style="display:flex;gap:12px;align-items:center;margin-bottom:20px;flex-wrap:wrap">
                  <div>
                    <div style="font-size:13px;font-weight:700;margin-bottom:4px"><?php esc_html_e("Auction Insights (Google Ads API)", "upsellio"); ?></div>
                    <div style="font-size:12px;color:var(--text-3)"><?php esc_html_e("Wymaga dostępu do metryk auction w API (może być niedostępne dla części kont).", "upsellio"); ?></div>
                  </div>
                  <div style="margin-left:auto;display:flex;gap:8px">
                    <button type="button" id="comp-load-btn" class="btn" data-nonce="<?php echo esc_attr($rv_nonce); ?>" <?php echo !$rv_ads_ready ? "disabled" : ""; ?>><?php esc_html_e("Pobierz Auction Insights", "upsellio"); ?></button>
                    <button type="button" id="comp-ai-btn" class="btn alt" style="display:none"><?php esc_html_e("✨ Analiza AI", "upsellio"); ?></button>
                  </div>
                </div>
                <div id="comp-status" style="display:none;padding:10px;background:#f0fdf4;border-radius:8px;font-size:12px;color:#16a34a;margin-bottom:16px"></div>
                <div id="comp-error" style="display:none;padding:10px;background:#fef2f2;border-radius:8px;font-size:12px;color:#dc2626;margin-bottom:16px"></div>
                <div id="comp-table-wrap" style="display:none;overflow-x:auto;margin-bottom:24px">
                  <table style="width:100%;border-collapse:collapse;font-size:12px" id="comp-table">
                    <thead>
                      <tr style="border-bottom:2px solid var(--border);background:#f8f9f4">
                        <th style="text-align:left;padding:8px 12px"><?php esc_html_e("Konkurent", "upsellio"); ?></th>
                        <th style="text-align:right;padding:8px">IS %</th>
                        <th style="text-align:right;padding:8px"><?php esc_html_e("Overlap %", "upsellio"); ?></th>
                        <th style="text-align:right;padding:8px"><?php esc_html_e("Wyżej %", "upsellio"); ?></th>
                        <th style="text-align:right;padding:8px"><?php esc_html_e("Top %", "upsellio"); ?></th>
                        <th style="text-align:center;padding:8px"><?php esc_html_e("Zagrożenie", "upsellio"); ?></th>
                      </tr>
                    </thead>
                    <tbody id="comp-tbody"></tbody>
                  </table>
                </div>
                <div id="comp-ai-result" style="display:none">
                  <div style="font-size:14px;font-weight:700;margin-bottom:10px"><?php esc_html_e("Analiza AI", "upsellio"); ?></div>
                  <div id="comp-ai-text" style="background:#f8f9f4;border:1px solid var(--border);border-radius:10px;padding:16px;font-size:13px;line-height:1.65;white-space:pre-wrap;color:var(--text-2)"></div>
                </div>
              </div>

              <?php elseif ($rv_research_tab === "client_plan") : ?>
              <div style="padding:20px 24px">
                <div style="font-size:13px;font-weight:700;margin-bottom:16px"><?php esc_html_e("Plan słów kluczowych per klient", "upsellio"); ?></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
                  <div>
                    <label style="font-size:12px;font-weight:700;display:block;margin-bottom:6px"><?php esc_html_e("Klient", "upsellio"); ?></label>
                    <select id="cp-client-select" style="width:100%;padding:9px;border:1px solid var(--border);border-radius:8px;font:inherit;font-size:13px">
                      <option value="">— <?php esc_html_e("wybierz klienta", "upsellio"); ?> —</option>
                      <?php foreach ($clients as $cp_c) : if (!($cp_c instanceof WP_Post)) { continue; } ?>
                      <option value="<?php echo (int) $cp_c->ID; ?>"><?php echo esc_html((string) $cp_c->post_title); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div>
                    <label style="font-size:12px;font-weight:700;display:block;margin-bottom:6px"><?php esc_html_e("Budżet Ads (PLN/mies.)", "upsellio"); ?></label>
                    <input type="number" id="cp-budget" placeholder="3000" style="width:100%;padding:9px;border:1px solid var(--border);border-radius:8px;font:inherit;font-size:13px" />
                  </div>
                </div>
                <label style="font-size:12px;font-weight:700;display:block;margin-bottom:6px"><?php esc_html_e("Frazy seed (opcjonalnie)", "upsellio"); ?></label>
                <textarea id="cp-seeds" rows="4" style="width:100%;padding:10px;border:1px solid var(--border);border-radius:8px;font:inherit;font-size:13px;resize:vertical"></textarea>
                <p style="margin-top:14px">
                  <button type="button" id="cp-generate-btn" class="btn" data-nonce="<?php echo esc_attr($rv_nonce); ?>" <?php echo !$rv_ads_ready ? "disabled" : ""; ?>><?php esc_html_e("✨ Generuj plan", "upsellio"); ?></button>
                </p>
                <div id="cp-status" style="display:none;margin-top:12px;padding:10px;border-radius:8px;font-size:12px"></div>
                <div id="cp-result" style="display:none;margin-top:20px"></div>
              </div>
              <?php endif; ?>
            </section>

            <script>
            (function () {
              var nonce = <?php echo wp_json_encode($rv_nonce); ?>;
              var ajaxUrl = <?php echo wp_json_encode(admin_url("admin-ajax.php")); ?>;

              function post(action, data) {
                var fd = new FormData();
                fd.append("action", action);
                fd.append("nonce", nonce);
                Object.keys(data || {}).forEach(function (k) {
                  fd.append(k, data[k]);
                });
                return fetch(ajaxUrl, { method: "POST", body: fd, credentials: "same-origin" }).then(function (r) {
                  return r.json();
                });
              }

              function wpAjaxMessage(j) {
                if (j && j.success) return "";
                if (!j || typeof j.data === "undefined") return "Błąd.";
                if (typeof j.data === "string") return j.data;
                if (j.data && j.data.message) return j.data.message;
                return "Błąd.";
              }

              var kwBtn = document.getElementById("kw-research-btn");
              var kwCluster = document.getElementById("kw-cluster-btn");
              var kwStatus = document.getElementById("kw-status");
              var kwError = document.getElementById("kw-error");
              var kwResults = document.getElementById("kw-results");
              var kwTbody = document.getElementById("kw-tbody");
              var kwCount = document.getElementById("kw-count");
              var kwData = [];

              function showKwStatus(el, msg, isErr) {
                if (!el) return;
                el.style.display = "block";
                el.style.background = isErr ? "#fef2f2" : "#f0fdf4";
                el.style.border = "1px solid " + (isErr ? "#fecaca" : "#bbf7d0");
                el.style.color = isErr ? "#dc2626" : "#16a34a";
                el.textContent = msg;
              }

              function setLoading(btn, loading) {
                if (!btn) return;
                btn.disabled = !!loading;
                if (loading) {
                  btn.setAttribute("data-orig", btn.textContent);
                  btn.textContent = "⏳ …";
                } else if (btn.getAttribute("data-orig")) {
                  btn.textContent = btn.getAttribute("data-orig");
                }
              }

              function renderKwTable(rows) {
                if (!kwTbody) return;
                var compColors = { LOW: "#16a34a", MEDIUM: "#d97706", HIGH: "#dc2626" };
                kwTbody.innerHTML = rows.map(function (kw) {
                  var posCell = kw.gsc_position !== null && kw.gsc_position !== undefined
                    ? "<span style=\"font-weight:700;color:" + (kw.gsc_position <= 10 ? "#0f766e" : kw.gsc_position <= 30 ? "#d97706" : "var(--text-3)") + "\">" + Number(kw.gsc_position).toFixed(0) + "</span>"
                    : "<span style=\"color:var(--text-3)\">—</span>";
                  var oppBar = "<div style=\"display:flex;align-items:center;gap:6px\"><div style=\"width:40px;height:6px;background:#f4f5f0;border-radius:3px;overflow:hidden\"><div style=\"width:" + (kw.opportunity || 0) + "%;height:100%;background:" + ((kw.opportunity || 0) >= 70 ? "#0d9488" : (kw.opportunity || 0) >= 40 ? "#d97706" : "#e2e5de") + ";border-radius:3px\"></div></div><span style=\"font-size:11px;font-weight:700\">" + (kw.opportunity || 0) + "</span></div>";
                  var channelBadge = kw.ads_quick_win
                    ? "<span style=\"background:#e0e7ff;color:#3730a3;padding:2px 7px;border-radius:99px;font-size:10px;font-weight:700\">SEO+ADS</span>"
                    : kw.in_gsc
                      ? "<span style=\"background:#f0fdf4;color:#16a34a;padding:2px 7px;border-radius:99px;font-size:10px;font-weight:700\">SEO</span>"
                      : "<span style=\"background:#fef3c7;color:#92400e;padding:2px 7px;border-radius:99px;font-size:10px;font-weight:700\">ADS</span>";
                  var trend = (kw.monthly_searches || []).slice(-6);
                  var maxV = Math.max.apply(null, trend.map(function (t) { return t.volume || 0; }).concat([1]));
                  var spark = trend.map(function (t) {
                    var h = Math.round((t.volume / maxV) * 20);
                    return "<div style=\"width:4px;height:" + h + "px;background:#0d9488;border-radius:1px\"></div>";
                  }).join("");
                  var trendHtml = "<div style=\"display:flex;align-items:flex-end;gap:2px;height:20px\">" + spark + "</div>";
                  var kwEsc = String(kw.keyword || "").replace(/</g, "&lt;");
                  return "<tr style=\"border-bottom:1px solid var(--border)\" data-in-gsc=\"" + (kw.in_gsc ? "1" : "0") + "\" data-quick-win=\"" + (kw.ads_quick_win ? "1" : "0") + "\" data-cpc=\"" + (kw.cpc_pln || 0) + "\"><td style=\"padding:9px 12px;font-weight:600\">" + kwEsc + "</td><td style=\"padding:9px 8px;text-align:right;font-weight:700\">" + Number(kw.avg_monthly || 0).toLocaleString("pl") + "</td><td style=\"padding:9px 8px;text-align:right\">" + Number(kw.cpc_pln || 0).toFixed(2) + "</td><td style=\"padding:9px 8px;text-align:center\"><span style=\"color:" + (compColors[kw.competition] || "var(--text-3)") + ";font-weight:700;font-size:11px\">" + (kw.competition || "") + "</span></td><td style=\"padding:9px 8px;text-align:right\">" + posCell + "</td><td style=\"padding:9px 8px;text-align:right;color:var(--text-3)\">" + (kw.gsc_impressions != null ? Number(kw.gsc_impressions).toLocaleString("pl") : "—") + "</td><td style=\"padding:9px 8px\">" + oppBar + "</td><td style=\"padding:9px 8px;text-align:center\">" + channelBadge + "</td><td style=\"padding:9px 8px\">" + trendHtml + "</td></tr>";
                }).join("");
              }

              if (kwBtn) {
                kwBtn.addEventListener("click", function () {
                  var seeds = document.getElementById("kw-seeds-input");
                  var txt = seeds && seeds.value ? seeds.value.trim() : "";
                  if (!txt) { alert("<?php echo esc_js(__("Wpisz minimum jedną frazę.", "upsellio")); ?>"); return; }
                  var seedArr = txt.split(/\n/).map(function (s) { return s.trim(); }).filter(Boolean);
                  if (seedArr.length > 20) { alert("Max 20."); return; }
                  if (kwStatus) kwStatus.style.display = "none";
                  if (kwError) kwError.style.display = "none";
                  setLoading(kwBtn, true);
                  var geoEl = document.getElementById("kw-geo");
                  post("upsellio_keyword_research", {
                    seeds: JSON.stringify(seedArr),
                    force_refresh: document.getElementById("kw-force-refresh") && document.getElementById("kw-force-refresh").checked ? "1" : "",
                    geo: geoEl ? geoEl.value : "2616"
                  }).then(function (j) {
                    setLoading(kwBtn, false);
                    if (!j.success) {
                      showKwStatus(kwError, wpAjaxMessage(j), true);
                      if (kwError) kwError.style.display = "block";
                      return;
                    }
                    kwData = (j.data && j.data.keywords) ? j.data.keywords : [];
                    renderKwTable(kwData);
                    showKwStatus(kwStatus, "<?php echo esc_js(__("Pobrano frazy.", "upsellio")); ?> " + kwData.length, false);
                    if (kwStatus) kwStatus.style.display = "block";
                    if (kwResults) kwResults.style.display = "block";
                    if (kwCluster) kwCluster.style.display = "inline-flex";
                    if (kwCount) kwCount.textContent = kwData.length + " fraz";
                  });
                });
              }

              document.querySelectorAll(".kw-filter-btn").forEach(function (btn) {
                btn.addEventListener("click", function () {
                  document.querySelectorAll(".kw-filter-btn").forEach(function (b) { b.classList.remove("is-active"); });
                  btn.classList.add("is-active");
                  var f = btn.getAttribute("data-filter");
                  var filtered = kwData.slice();
                  if (f === "gsc") filtered = kwData.filter(function (k) { return k.in_gsc; });
                  if (f === "gaps") filtered = kwData.filter(function (k) { return !k.in_gsc; });
                  if (f === "quick_win") filtered = kwData.filter(function (k) { return k.ads_quick_win; });
                  if (f === "low_cpc") filtered = kwData.filter(function (k) { return (k.cpc_pln || 0) < 2; });
                  renderKwTable(filtered);
                  if (kwCount) kwCount.textContent = filtered.length + " fraz";
                });
              });

              if (kwCluster) {
                kwCluster.addEventListener("click", function () {
                  if (!kwData.length) return;
                  setLoading(kwCluster, true);
                  post("upsellio_keyword_ai_cluster", { keywords: JSON.stringify(kwData) }).then(function (j) {
                    setLoading(kwCluster, false);
                    if (!j.success) {
                      showKwStatus(kwError, wpAjaxMessage(j), true);
                      if (kwError) kwError.style.display = "block";
                      return;
                    }
                    var d = j.data || {};
                    var clusters = d.clusters || [];
                    var cEl = document.getElementById("kw-clusters");
                    var cContent = document.getElementById("kw-clusters-content");
                    var chanColors = { ads: ["#e0e7ff", "#3730a3"], seo: ["#f0fdf4", "#16a34a"], both: ["#fef3c7", "#92400e"] };
                    cContent.innerHTML = clusters.map(function (cl) {
                      var cc = chanColors[cl.channel] || ["#f4f5f0", "var(--text-3)"];
                      var kws = (cl.keywords || []).map(function (k) {
                        return "<span style=\"background:#fff;border:1px solid var(--border);padding:2px 8px;border-radius:99px;font-size:11px\">" + String(k).replace(/</g, "&lt;") + "</span>";
                      }).join("");
                      return "<div style=\"background:#f8f9f4;border:1px solid var(--border);border-radius:10px;padding:14px;margin-bottom:8px\"><div style=\"display:flex;align-items:center;gap:10px;margin-bottom:8px\"><strong>" + String(cl.name || "") + "</strong><span style=\"background:" + cc[0] + ";color:" + cc[1] + ";padding:2px 8px;border-radius:99px;font-size:10px;font-weight:800\">" + String(cl.channel || "").toUpperCase() + "</span></div><div style=\"font-size:11px;color:var(--text-3);margin-bottom:8px\">" + String(cl.rationale || "") + "</div><div style=\"display:flex;gap:4px;flex-wrap:wrap\">" + kws + "</div></div>";
                    }).join("");
                    if (d.summary) {
                      cContent.innerHTML += "<div style=\"padding:12px;background:#fff;border:1px solid #0d9488;border-radius:10px;margin-top:8px;font-size:13px\">" + String(d.summary).replace(/</g, "&lt;") + "</div>";
                    }
                    if (cEl) cEl.style.display = "block";
                  });
                });
              }

              var syncBtn = document.getElementById("ads-sync-btn");
              if (syncBtn) {
                syncBtn.addEventListener("click", function () {
                  setLoading(syncBtn, true);
                  var st = document.getElementById("ads-sync-status");
                  if (st) st.textContent = "…";
                  post("upsellio_ads_sync_campaigns", {}).then(function (j) {
                    setLoading(syncBtn, false);
                    if (st) {
                      st.textContent = j.success
                        ? "<?php echo esc_js(__("Zsynchronizowano.", "upsellio")); ?> " + ((j.data && j.data.count) || 0)
                        : wpAjaxMessage(j);
                    }
                    if (j.success) setTimeout(function () { location.reload(); }, 1200);
                  });
                });
              }

              function compColor(val, t0, t1) {
                if (val >= t1) return "#dc2626";
                if (val >= t0) return "#d97706";
                return "#16a34a";
              }

              var compBtn = document.getElementById("comp-load-btn");
              var compAiBtn = document.getElementById("comp-ai-btn");
              var compData = [];

              if (compBtn) {
                compBtn.addEventListener("click", function () {
                  setLoading(compBtn, true);
                  var st = document.getElementById("comp-status");
                  var er = document.getElementById("comp-error");
                  if (st) st.style.display = "none";
                  if (er) er.style.display = "none";
                  post("upsellio_ads_auction_insights", {}).then(function (j) {
                    setLoading(compBtn, false);
                    if (!j.success) {
                      if (er) { er.style.display = "block"; er.textContent = wpAjaxMessage(j); }
                      return;
                    }
                    compData = (j.data && j.data.competitors) ? j.data.competitors : [];
                    var tbody = document.getElementById("comp-tbody");
                    var wrap = document.getElementById("comp-table-wrap");
                    if (tbody) {
                      tbody.innerHTML = compData.map(function (c) {
                        var threat = c.impression_share >= 60 ? "<?php echo esc_js(__("Wysokie", "upsellio")); ?>" : c.impression_share >= 30 ? "<?php echo esc_js(__("Średnie", "upsellio")); ?>" : "<?php echo esc_js(__("Niskie", "upsellio")); ?>";
                        var tColor = c.impression_share >= 60 ? "#dc2626" : c.impression_share >= 30 ? "#d97706" : "#16a34a";
                        return "<tr style=\"border-bottom:1px solid var(--border)\"><td style=\"padding:9px 12px;font-weight:700\">" + String(c.domain || "").replace(/</g, "&lt;") + "</td><td style=\"padding:9px 8px;text-align:right;font-weight:700;color:" + compColor(c.impression_share, 30, 60) + "\">" + c.impression_share + "%</td><td style=\"padding:9px 8px;text-align:right\">" + c.overlap_rate + "%</td><td style=\"padding:9px 8px;text-align:right;color:" + compColor(c.position_above_rate, 30, 60) + "\">" + c.position_above_rate + "%</td><td style=\"padding:9px 8px;text-align:right\">" + c.top_share + "%</td><td style=\"padding:9px 8px;text-align:center\"><span style=\"color:" + tColor + ";font-weight:700;font-size:11px\">" + threat + "</span></td></tr>";
                      }).join("");
                    }
                    if (wrap) wrap.style.display = "block";
                    if (compAiBtn) compAiBtn.style.display = "inline-flex";
                    if (st) { st.style.display = "block"; st.textContent = "<?php echo esc_js(__("Znaleziono domen:", "upsellio")); ?> " + compData.length; }
                  });
                });
              }

              if (compAiBtn) {
                compAiBtn.addEventListener("click", function () {
                  if (!compData.length) return;
                  setLoading(compAiBtn, true);
                  post("upsellio_competitor_ai_analysis", { competitors: JSON.stringify(compData) }).then(function (j) {
                    setLoading(compAiBtn, false);
                    var aiEl = document.getElementById("comp-ai-result");
                    var aiTxt = document.getElementById("comp-ai-text");
                    var txt = "";
                    if (j.success && j.data && j.data.analysis) txt = j.data.analysis;
                    else txt = wpAjaxMessage(j);
                    if (aiTxt) aiTxt.textContent = txt;
                    if (aiEl) aiEl.style.display = "block";
                  });
                });
              }

              var cpBtn = document.getElementById("cp-generate-btn");
              if (cpBtn) {
                cpBtn.addEventListener("click", function () {
                  var clientId = document.getElementById("cp-client-select") && document.getElementById("cp-client-select").value;
                  var budget = document.getElementById("cp-budget") && document.getElementById("cp-budget").value;
                  var seeds = document.getElementById("cp-seeds") && document.getElementById("cp-seeds").value;
                  var st = document.getElementById("cp-status");
                  var resEl = document.getElementById("cp-result");
                  if (!clientId) { alert("<?php echo esc_js(__("Wybierz klienta.", "upsellio")); ?>"); return; }
                  setLoading(cpBtn, true);
                  if (st) { st.style.display = "block"; st.style.background = "#f0fdf4"; st.style.color = "#16a34a"; st.textContent = "…"; }
                  if (resEl) resEl.style.display = "none";
                  post("upsellio_keyword_client_plan", {
                    client_id: clientId,
                    budget: budget || "3000",
                    seeds: seeds || ""
                  }).then(function (j) {
                    setLoading(cpBtn, false);
                    if (!j.success) {
                      if (st) { st.style.background = "#fef2f2"; st.style.color = "#dc2626"; st.textContent = wpAjaxMessage(j); }
                      return;
                    }
                    if (st) st.style.display = "none";
                    if (resEl) {
                      resEl.style.display = "block";
                      var plan = (j.data && j.data.plan) ? j.data.plan : "";
                      resEl.innerHTML = "<div style=\"background:#f8f9f4;border:1px solid var(--border);border-radius:12px;padding:20px\"><div style=\"font-size:14px;font-weight:700;margin-bottom:12px\"><?php echo esc_js(__("Plan", "upsellio")); ?></div><div style=\"font-size:13px;line-height:1.7;white-space:pre-wrap;color:var(--text-2)\"></div></div>";
                      var inner = resEl.querySelector("div div:last-child");
                      if (inner) inner.textContent = plan;
                    }
                  });
                });
              }
            })();
            </script>
