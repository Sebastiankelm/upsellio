<?php
if (!defined("ABSPATH")) {
    exit;
}
$range_days = isset($_GET["range"]) ? (int) wp_unslash($_GET["range"]) : 30;
$query_lead = function_exists("upsellio_analytics_query_lead_value")
    ? upsellio_analytics_query_lead_value($range_days, 50)
    : ["rows" => [], "total_value" => 0];
$pareto = function_exists("upsellio_analytics_pareto")
    ? upsellio_analytics_pareto((array) ($query_lead["rows"] ?? []), "value")
    : ["count_for_80pct" => 0];
$pages_to_optimize = (array) get_option("ups_ai_page_perf_suggestions", []);

// --- Indeksacja GSC ---
$idx_summary = (array) get_option("ups_gsc_indexation_summary", []);
$idx_pages = (array) get_option("ups_gsc_indexation_pages", []);
$idx_last = (string) get_option("ups_gsc_indexation_last_sync", "");
$idx_submitted = (int) ($idx_summary["submitted"] ?? 0);
$idx_indexed = (int) ($idx_summary["indexed"] ?? 0);
$idx_ratio = $idx_submitted > 0 ? (int) round($idx_indexed / $idx_submitted * 100) : 0;
$idx_color = $idx_ratio >= 90 ? "var(--success)" : ($idx_ratio >= 70 ? "var(--warn)" : "var(--danger)");
$idx_problems = count(array_filter($idx_pages, static function ($r) {
    return is_array($r) && (string) ($r["verdict"] ?? "") !== "PASS" && (string) ($r["verdict"] ?? "") !== "";
}));

// --- Keyword visibility ---
$kw_raw = (array) get_option("upsellio_keyword_metrics_rows", []);
$kw_vis = function_exists("upsellio_gsc_visibility_stats")
    ? upsellio_gsc_visibility_stats($kw_raw)
    : ["total" => 0, "top3" => 0, "top10" => 0, "top50" => 0, "aggregated" => []];
$kw_agg = (array) ($kw_vis["aggregated"] ?? []);
$kw_best = [];
foreach ($kw_agg as $r) {
    if (!is_array($r)) {
        continue;
    }
    $p = (float) ($r["position"] ?? 0);
    if ($p <= 0) {
        continue;
    }
    $kw_best[] = $p;
}
$kw_total = (int) ($kw_vis["total"] ?? 0);
$kw_top3 = (int) ($kw_vis["top3"] ?? 0);
$kw_top10 = (int) ($kw_vis["top10"] ?? 0);
$kw_top50 = (int) ($kw_vis["top50"] ?? 0);
$kw_last = (string) get_option("upsellio_keyword_metrics_last_sync", "");
$kw_nonce = wp_create_nonce("upsellio_gsc_keywords_full_nonce");
$idx_nonce_val = wp_create_nonce("upsellio_gsc_indexation_nonce");
$can_refresh_idx = current_user_can("manage_options");
?>
<?php require __DIR__ . "/rankmath-seo-widgets.php"; ?>
<section class="card">
  <h3 style="margin:0 0 16px">SEO — szczegóły</h3>

  <!-- ── WIDOCZNOŚĆ KEYWORDS ──────────────────────────────────────────── -->
  <div style="border:1px solid var(--border);border-radius:var(--r-md);padding:16px;margin-bottom:16px">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px">
      <div>
        <div style="font-weight:700;font-size:14px">Widoczność GSC</div>
        <div class="muted" style="font-size:12px">
          <?php echo $kw_last ? esc_html($kw_last) : "Brak danych — włącz sync i poczekaj na cron."; ?>
          <?php if ($kw_total > 0) : ?> · <?php echo esc_html(number_format($kw_total)); ?> fraz<?php endif; ?>
          · Pozycja = średnia GSC (nie ręczny SERP). * = &lt;10 wyświetleń
        </div>
      </div>
      <?php if ($kw_total > 0) : ?>
      <button type="button" id="ups-seo-kw-btn" class="btn alt"
              style="font-size:12px;padding:6px 12px"
              data-nonce="<?php echo esc_attr($kw_nonce); ?>"
              data-ajaxurl="<?php echo esc_attr(admin_url("admin-ajax.php")); ?>">
        ⬇ Pokaż pełną listę fraz
      </button>
      <?php endif; ?>
    </div>

    <?php if ($kw_total === 0) : ?>
      <p class="muted" style="margin:0">Brak danych keywordów. Podłącz GSC OAuth lub wgraj CSV.</p>
    <?php else : ?>
      <!-- KPI row -->
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:12px">
        <?php
        $kw_kpis = [
            ["Wszystkich", $kw_total, "#111"],
            ["TOP 3", $kw_top3, "#15803d"],
            ["TOP 10", $kw_top10, "#0369a1"],
            ["TOP 50", $kw_top50, "#9333ea"],
        ];
        foreach ($kw_kpis as $kw_kpi) :
            list($lbl, $val, $col) = $kw_kpi;
            ?>
        <div style="background:var(--bg);border-radius:var(--r-sm);padding:10px;text-align:center">
          <div style="font-size:10px;font-weight:700;letter-spacing:.6px;color:var(--text-3);text-transform:uppercase"><?php echo esc_html($lbl); ?></div>
          <div style="font-size:20px;font-weight:800;color:<?php echo esc_attr($col); ?>"><?php echo esc_html(number_format($val)); ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Pasek rozkładu -->
      <?php
      $segs = [
          [$kw_top3, "#15803d", "TOP 1-3"],
          [$kw_top10 - $kw_top3, "#0369a1", "4-10"],
          [count(array_filter($kw_agg, static function ($r) {
              $p = (float) ($r["position"] ?? 99);
              return $p > 10 && $p <= 20;
          })), "#d97706", "11-20"],
          [count(array_filter($kw_agg, static function ($r) {
              $p = (float) ($r["position"] ?? 99);
              return $p > 20 && $p <= 50;
          })), "#9333ea", "21-50"],
          [$kw_total - $kw_top50, "#9ca3af", "51+"],
      ];
      ?>
      <div style="display:flex;height:14px;border-radius:999px;overflow:hidden;gap:1px;margin-bottom:8px">
        <?php foreach ($segs as $seg) :
            list($cnt, $col, $lbl) = $seg;
            if ($cnt <= 0) {
                continue;
            }
            $pct = round($cnt / $kw_total * 100, 1);
            ?>
        <div style="background:<?php echo esc_attr($col); ?>;width:<?php echo esc_attr((string) $pct); ?>%;min-width:2px"
             title="<?php echo esc_attr($lbl . ": " . $cnt . " fraz (" . $pct . "%)"); ?>"></div>
        <?php endforeach; ?>
      </div>
      <div style="display:flex;flex-wrap:wrap;gap:10px">
        <?php foreach ($segs as $seg) :
            list($cnt, $col, $lbl) = $seg;
            if ($cnt <= 0) {
                continue;
            }
            $pct = round($cnt / $kw_total * 100, 1);
            ?>
        <div style="display:flex;align-items:center;gap:4px;font-size:11px;color:var(--text-2)">
          <span style="width:8px;height:8px;border-radius:2px;background:<?php echo esc_attr($col); ?>;flex-shrink:0"></span>
          <strong><?php echo esc_html($lbl); ?></strong>: <?php echo esc_html(number_format($cnt)); ?> (<?php echo esc_html((string) $pct); ?>%)
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Tabela fraz (lazy) -->
    <div id="ups-seo-kw-loading" style="display:none;margin-top:12px;padding:10px;background:var(--bg);border-radius:var(--r-sm);font-size:12px;color:var(--teal)">⏳ Ładuję…</div>
    <div id="ups-seo-kw-error" style="display:none;margin-top:12px;padding:10px;background:#fef2f2;border-radius:var(--r-sm);font-size:12px;color:var(--danger)"></div>
    <div id="ups-seo-kw-panel" style="display:none;margin-top:14px">
      <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px;align-items:center">
        <input type="search" id="ups-seo-kw-s" placeholder="Szukaj frazy lub URL…"
               style="padding:6px 10px;border:1px solid var(--border);border-radius:var(--r-sm);font-size:12px;flex:1;min-width:180px">
        <select id="ups-seo-kw-g" style="padding:6px 10px;border:1px solid var(--border);border-radius:var(--r-sm);font-size:12px">
          <option value="">Wszystkie pozycje</option>
          <option value="top3">TOP 1-3</option>
          <option value="top10">TOP 4-10</option>
          <option value="top20">TOP 11-20</option>
          <option value="top50">TOP 21-50</option>
          <option value="rest">51+</option>
        </select>
        <select id="ups-seo-kw-sort" style="padding:6px 10px;border:1px solid var(--border);border-radius:var(--r-sm);font-size:12px">
          <option value="pos">Pozycja ↑</option>
          <option value="impr">Wyświetlenia ↓</option>
          <option value="clk">Kliknięcia ↓</option>
          <option value="az">Fraza A-Z</option>
        </select>
        <span id="ups-seo-kw-cnt" style="font-size:11px;color:var(--text-3)"></span>
        <button type="button" id="ups-seo-kw-csv" class="btn alt" style="font-size:11px;padding:5px 10px;margin-left:auto">⬇ CSV</button>
      </div>
      <div style="overflow-x:auto">
      <table style="width:100%;border-collapse:collapse;font-size:12px">
        <thead>
          <tr style="background:var(--bg);border-bottom:2px solid var(--border)">
            <th style="text-align:left;padding:7px 10px">Fraza</th>
            <th style="text-align:left;padding:7px 8px">URL</th>
            <th style="text-align:center;padding:7px 8px">
              Pozycja
              <span class="ups-tip" tabindex="0" data-tip="Średnia pozycja z GSC (ważona wyświetleniami), nie to samo co ręczne wyszukiwanie w Google — wynik zależy od kraju, urządzenia i personalizacji. Przy małej liczbie wyświetleń pozycja bywa niestabilna.">?</span>
            </th>
            <th style="text-align:right;padding:7px 8px">Wyświetl.</th>
            <th style="text-align:right;padding:7px 8px">Klikn.</th>
            <th style="text-align:right;padding:7px 8px">CTR%</th>
          </tr>
        </thead>
        <tbody id="ups-seo-kw-tbody"></tbody>
      </table>
      </div>
      <div style="display:flex;gap:8px;align-items:center;padding:10px 0">
        <button type="button" id="ups-seo-kw-prev" class="btn alt" style="font-size:11px;padding:5px 10px" disabled>← Poprz.</button>
        <span id="ups-seo-kw-pi" style="font-size:11px;color:var(--text-3)"></span>
        <button type="button" id="ups-seo-kw-next" class="btn alt" style="font-size:11px;padding:5px 10px">Nast. →</button>
      </div>
    </div>
  </div>

  <!-- ── INDEKSACJA GSC ────────────────────────────────────────────────── -->
  <div style="border:1px solid var(--border);border-radius:var(--r-md);padding:16px;margin-bottom:16px">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px">
      <div>
        <div style="font-weight:700;font-size:14px">Status indeksacji GSC</div>
        <div class="muted" style="font-size:12px">Sync: <?php echo $idx_last !== "" ? esc_html($idx_last) : "brak — uruchom cron lub odśwież"; ?></div>
      </div>
      <?php if ($can_refresh_idx) : ?>
      <button type="button" id="ups-seo-idx-btn" class="btn alt"
              style="font-size:12px;padding:6px 12px"
              data-nonce="<?php echo esc_attr($idx_nonce_val); ?>"
              data-ajaxurl="<?php echo esc_attr(admin_url("admin-ajax.php")); ?>">
        ↻ Odśwież (batch 50)
      </button>
      <?php else : ?>
      <span class="muted" style="font-size:11px;max-width:200px;text-align:right">Pełne odświeżenie wymaga roli administratora WP.</span>
      <?php endif; ?>
    </div>
    <div id="ups-seo-idx-msg" style="display:none;margin-bottom:10px;padding:8px 12px;border-radius:var(--r-sm);font-size:12px"></div>

    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px">
      <?php
      $idx_kpis = [
          ["Przesłanych", $idx_submitted > 0 ? number_format($idx_submitted) : "—", "var(--text-main)"],
          ["Zaindeksowanych", $idx_indexed > 0 ? number_format($idx_indexed) : "—", $idx_color],
          ["Wskaźnik", $idx_submitted > 0 ? $idx_ratio . "%" : "—", $idx_color],
          ["Problemy", (string) $idx_problems, $idx_problems > 0 ? "var(--danger)" : "var(--success)"],
      ];
      foreach ($idx_kpis as $idx_kpi) :
          list($lbl, $val, $col) = $idx_kpi;
          ?>
      <div style="background:var(--bg);border-radius:var(--r-sm);padding:10px;text-align:center">
        <div style="font-size:10px;font-weight:700;letter-spacing:.6px;color:var(--text-3);text-transform:uppercase;margin-bottom:4px"><?php echo esc_html($lbl); ?></div>
        <div style="font-size:20px;font-weight:800;color:<?php echo esc_attr($col); ?>"><?php echo esc_html((string) $val); ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <?php if ($idx_submitted > 0) : ?>
    <div style="background:var(--border);border-radius:999px;height:8px;margin-top:12px">
      <div style="background:<?php echo esc_attr($idx_color); ?>;border-radius:999px;height:8px;width:<?php echo esc_attr((string) $idx_ratio); ?>%;transition:.3s"></div>
    </div>
    <?php endif; ?>

    <?php
    $problems = array_filter($idx_pages, static function ($r) {
        return is_array($r) && (string) ($r["verdict"] ?? "") !== "PASS" && (string) ($r["verdict"] ?? "") !== "";
    });
    usort($problems, static function ($a, $b) {
        return strcmp((string) ($b["verdict"] ?? ""), (string) ($a["verdict"] ?? ""));
    });
    $problems = array_slice(array_values($problems), 0, 20);
    if (!empty($problems)) :
        ?>
    <div style="margin-top:14px">
      <div style="font-size:12px;font-weight:700;color:var(--danger);margin-bottom:8px">⚠ Strony niezaindeksowane (top <?php echo count($problems); ?>)</div>
      <table style="width:100%;border-collapse:collapse;font-size:11px">
        <thead><tr style="background:var(--bg);border-bottom:1px solid var(--border)">
          <th style="text-align:left;padding:5px 8px">URL</th>
          <th style="text-align:left;padding:5px 8px">Typ</th>
          <th style="text-align:left;padding:5px 8px">Problem</th>
          <th style="padding:5px 8px"></th>
        </tr></thead>
        <tbody>
        <?php foreach ($problems as $pr) :
            $urlp = wp_parse_url((string) ($pr["url"] ?? ""), PHP_URL_PATH);
            $vdict = (string) ($pr["verdict"] ?? "");
            $vc = $vdict === "FAIL" ? "var(--danger)" : "var(--warn)";
            $edit = (int) ($pr["post_id"] ?? 0) > 0 ? get_edit_post_link((int) $pr["post_id"], "raw") : "";
            ?>
        <tr style="border-bottom:1px solid var(--border)">
          <td style="padding:5px 8px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
            <a href="<?php echo esc_url((string) ($pr["url"] ?? "")); ?>" target="_blank" rel="noopener noreferrer" style="color:var(--teal)">
              <?php echo esc_html($urlp ?: (string) ($pr["url"] ?? "")); ?>
            </a>
          </td>
          <td style="padding:5px 8px;color:var(--text-3)"><?php echo esc_html((string) ($pr["post_type"] ?? "")); ?></td>
          <td style="padding:5px 8px">
            <span style="color:<?php echo esc_attr($vc); ?>;font-weight:600">
              <?php echo esc_html((string) ($pr["problem"] ?? $vdict)); ?>
            </span>
          </td>
          <td style="padding:5px 8px">
            <?php if ($edit !== "") : ?>
            <a href="<?php echo esc_url($edit); ?>" target="_blank" rel="noopener noreferrer" class="btn alt" style="font-size:10px;padding:3px 8px">Edytuj</a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <!-- ── QUERY → LEADY ────────────────────────────────────────────────── -->
  <div style="border:1px solid var(--border);border-radius:var(--r-md);padding:16px">
    <div style="font-weight:700;font-size:14px;margin-bottom:4px">Query → Leady → Wartość</div>
    <div class="muted" style="font-size:12px;margin-bottom:10px">
      Przychód z zapytań: <?php echo esc_html(number_format((float) ($query_lead["total_value"] ?? 0), 0, ",", " ")); ?> zł
      · Pareto 80/20: <?php echo esc_html((string) ((int) ($pareto["count_for_80pct"] ?? 0))); ?> słów
    </div>
    <?php if (empty($query_lead["rows"])) : ?>
      <p class="muted" style="margin:0">Brak powiązanych danych — potrzebne GSC sync + leady z meta _upsellio_lead_gsc_likely_query.</p>
    <?php else : ?>
    <table style="width:100%;border-collapse:collapse;font-size:12px">
      <thead><tr style="background:var(--bg);border-bottom:2px solid var(--border)">
        <th style="text-align:left;padding:6px 8px">Query</th>
        <th style="text-align:right;padding:6px 8px">Leady</th>
        <th style="text-align:right;padding:6px 8px">Wygrane</th>
        <th style="text-align:right;padding:6px 8px">Wartość</th>
      </tr></thead>
      <tbody>
      <?php foreach (array_slice((array) ($query_lead["rows"] ?? []), 0, 15) as $row) : ?>
      <tr style="border-bottom:1px solid var(--border)">
        <td style="padding:5px 8px"><?php echo esc_html((string) ($row["query"] ?? "")); ?></td>
        <td style="text-align:right;padding:5px 8px"><?php echo (int) ($row["leads"] ?? 0); ?></td>
        <td style="text-align:right;padding:5px 8px"><?php echo (int) ($row["won"] ?? 0); ?></td>
        <td style="text-align:right;padding:5px 8px;font-weight:600"><?php echo esc_html(number_format((float) ($row["value"] ?? 0), 0, ",", " ")); ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>

    <?php if (!empty($pages_to_optimize)) : ?>
    <div style="margin-top:14px">
      <div style="font-size:12px;font-weight:700;margin-bottom:6px">Pages to optimize (AI)</div>
      <ul style="margin:0;padding-left:16px">
        <?php foreach (array_slice($pages_to_optimize, 0, 6) as $p) : ?>
          <?php if (!is_array($p)) {
              continue;
          } ?>
        <li style="font-size:12px;margin-bottom:3px"><?php echo esc_html((string) ($p["title"] ?? "—")); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>
  </div>
</section>

<script>
(function(){
  "use strict";
  var kwBtn    = document.getElementById("ups-seo-kw-btn");
  var kwLoad   = document.getElementById("ups-seo-kw-loading");
  var kwErr    = document.getElementById("ups-seo-kw-error");
  var kwPanel  = document.getElementById("ups-seo-kw-panel");
  var kwTbody  = document.getElementById("ups-seo-kw-tbody");
  var kwCnt    = document.getElementById("ups-seo-kw-cnt");
  var kwPi     = document.getElementById("ups-seo-kw-pi");
  var kwPrev   = document.getElementById("ups-seo-kw-prev");
  var kwNext   = document.getElementById("ups-seo-kw-next");
  var kwCsv    = document.getElementById("ups-seo-kw-csv");
  var all = [], filtered = [], cur = 1, PG = 80;

  function posC(p){return p<=3?"#15803d":p<=10?"#0369a1":p<=20?"#d97706":p<=50?"#9333ea":"#9ca3af";}
  function esc(s){return String(s).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;");}

  function render(){
    var s=(cur-1)*PG, pg=filtered.slice(s,s+PG), h="";
    pg.forEach(function(r){
      h+="<tr style='border-bottom:1px solid var(--border)'>"
       +"<td style='padding:5px 10px;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap' title='"+esc(r.keyword)+"'>"+esc(r.keyword)+"</td>"
       +"<td style='padding:5px 8px;font-size:11px;color:var(--text-3);max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap'>"
       +"<a href='"+esc(r.url)+"' target='_blank' rel='noopener noreferrer' style='color:var(--teal)'>"+esc(r.url_path||r.url)+"</a></td>"
       +"<td style='padding:5px 8px;text-align:center'><span style='padding:2px 8px;border-radius:999px;font-weight:700;font-size:11px;"
       +"background:"+posC(r.position)+"20;color:"+posC(r.position)+"' title='"+(r.low_sample?"Mała próba (<10 wyśw.) — pozycja może odbiegać od ręcznego sprawdzenia":"Średnia GSC (ważona wyświetleniami)")+"'>"
       +Number(r.position).toFixed(1)+(r.low_sample?"*":"")+"</span></td>"
       +"<td style='text-align:right;padding:5px 8px'>"+r.impressions.toLocaleString("pl-PL")+"</td>"
       +"<td style='text-align:right;padding:5px 8px;font-weight:600'>"+r.clicks.toLocaleString("pl-PL")+"</td>"
       +"<td style='text-align:right;padding:5px 8px'>"+Number(r.ctr).toFixed(2)+"%</td>"
       +"</tr>";
    });
    kwTbody.innerHTML=h||"<tr><td colspan='6' style='padding:16px;text-align:center;color:var(--text-3)'>Brak wyników.</td></tr>";
    var tp=Math.max(1,Math.ceil(filtered.length/PG));
    kwPi.textContent="Strona "+cur+" z "+tp+" · "+filtered.length.toLocaleString("pl-PL")+" fraz";
    kwPrev.disabled=cur<=1; kwNext.disabled=cur>=tp;
  }

  function applyF(){
    var s=document.getElementById("ups-seo-kw-s").value.toLowerCase().trim();
    var g=document.getElementById("ups-seo-kw-g").value;
    var so=document.getElementById("ups-seo-kw-sort").value;
    filtered=all.filter(function(r){
      return(s===""||r.keyword.toLowerCase().indexOf(s)!==-1||String(r.url_path||"").toLowerCase().indexOf(s)!==-1||String(r.url||"").toLowerCase().indexOf(s)!==-1)
        &&(g===""||r.pos_group===g);
    });
    if(so==="impr") filtered.sort(function(a,b){return b.impressions-a.impressions;});
    else if(so==="clk") filtered.sort(function(a,b){return b.clicks-a.clicks;});
    else if(so==="az")  filtered.sort(function(a,b){return String(a.keyword).localeCompare(String(b.keyword),"pl");});
    else filtered.sort(function(a,b){return a.position-b.position;});
    kwCnt.textContent=filtered.length.toLocaleString("pl-PL")+" fraz";
    cur=1; render();
  }

  if(kwBtn){
    kwBtn.addEventListener("click",function(){
      kwBtn.disabled=true; kwLoad.style.display="block"; kwErr.style.display="none";
      var fd=new FormData();
      fd.append("action","upsellio_gsc_keywords_full");
      fd.append("nonce",kwBtn.getAttribute("data-nonce")||"");
      fetch(kwBtn.getAttribute("data-ajaxurl")||"",{method:"POST",body:fd,credentials:"same-origin"})
        .then(function(r){return r.json();})
        .then(function(d){
          kwLoad.style.display="none";
          if(!d.success){kwErr.textContent="✗ "+((d.data&&d.data.message)?d.data.message:"Błąd");kwErr.style.display="block";kwBtn.disabled=false;return;}
          all=d.data.rows||[];
          kwBtn.textContent="✓ "+all.length.toLocaleString("pl-PL")+" fraz";
          kwPanel.style.display=""; applyF();
        })
        .catch(function(){kwLoad.style.display="none";kwErr.textContent="✗ Błąd sieci";kwErr.style.display="block";kwBtn.disabled=false;});
    });
    var sEl=document.getElementById("ups-seo-kw-s");
    var gEl=document.getElementById("ups-seo-kw-g");
    var soEl=document.getElementById("ups-seo-kw-sort");
    if(sEl){ sEl.addEventListener("input",applyF); }
    if(gEl){ gEl.addEventListener("change",applyF); }
    if(soEl){ soEl.addEventListener("change",applyF); }
    if(kwPrev) kwPrev.addEventListener("click",function(){if(cur>1){cur--;render();}});
    if(kwNext) kwNext.addEventListener("click",function(){if(cur<Math.ceil(filtered.length/PG)){cur++;render();}});
    if(kwCsv)  kwCsv.addEventListener("click",function(){
      if(!filtered.length) return;
      var csv="Fraza,URL,Pozycja,Wyswietlenia,Klikniecia,CTR%,Grupa\n";
      filtered.forEach(function(r){csv+='"'+String(r.keyword).replace(/"/g,'""')+'","'+String(r.url).replace(/"/g,'""')+'",'+Number(r.position).toFixed(1)+","+r.impressions+","+r.clicks+","+Number(r.ctr).toFixed(2)+","+String(r.pos_group||"")+"\n";});
      var a=document.createElement("a");
      a.href=URL.createObjectURL(new Blob(["\uFEFF"+csv],{type:"text/csv;charset=utf-8"}));
      a.download="gsc-frazy-"+new Date().toISOString().slice(0,10)+".csv";
      a.click();
    });
  }

  var idxBtn = document.getElementById("ups-seo-idx-btn");
  var idxMsg = document.getElementById("ups-seo-idx-msg");
  if(idxBtn){
    idxBtn.addEventListener("click",function(){
      idxBtn.disabled=true; idxBtn.textContent="Sprawdzam…"; idxMsg.style.display="none";
      var fd=new FormData();
      fd.append("action","upsellio_gsc_refresh_indexation");
      fd.append("nonce",idxBtn.getAttribute("data-nonce")||"");
      fetch(idxBtn.getAttribute("data-ajaxurl")||"",{method:"POST",body:fd,credentials:"same-origin"})
        .then(function(r){return r.json();})
        .then(function(d){
          idxBtn.disabled=false; idxBtn.textContent="↻ Odśwież (batch 50)";
          idxMsg.style.display="block";
          if(d.success){
            idxMsg.style.cssText="display:block;padding:8px 12px;border-radius:var(--r-sm);font-size:12px;background:#e8f5e9;color:var(--success)";
            idxMsg.textContent="✓ "+(d.data&&d.data.message?d.data.message:"OK");
            setTimeout(function(){window.location.reload();},2500);
          } else {
            idxMsg.style.cssText="display:block;padding:8px 12px;border-radius:var(--r-sm);font-size:12px;background:#fce8e8;color:var(--danger)";
            idxMsg.textContent="✗ "+((d.data&&d.data.message)?d.data.message:"Błąd");
          }
        })
        .catch(function(){
          idxBtn.disabled=false; idxBtn.textContent="↻ Odśwież (batch 50)";
          idxMsg.style.cssText="display:block;padding:8px 12px;border-radius:var(--r-sm);font-size:12px;background:#fce8e8;color:var(--danger)";
          idxMsg.textContent="✗ Błąd sieci";
        });
    });
  }
})();
</script>
