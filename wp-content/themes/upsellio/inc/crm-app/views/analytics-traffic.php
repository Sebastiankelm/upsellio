<?php
if (!defined("ABSPATH")) {
    exit;
}
$range_days = isset($_GET["range"]) ? (int) wp_unslash($_GET["range"]) : 30;
$kpi_cards = function_exists("upsellio_analytics_kpi_cards") ? upsellio_analytics_kpi_cards($range_days) : [];
$charts = function_exists("upsellio_analytics_charts_series") ? upsellio_analytics_charts_series($range_days) : [];

$tc_vals = function (array $s): array {
    return array_map(function ($p) {
        return (float) ($p[1] ?? 0);
    }, $s);
};

$views_cur = $tc_vals($charts["views"]["current"] ?? []);
$views_prev = $tc_vals($charts["views"]["previous"] ?? []);
$leads_cur = $tc_vals($charts["leads"]["current"] ?? []);
$leads_prev = $tc_vals($charts["leads"]["previous"] ?? []);
$impr_cur = $tc_vals($charts["impressions"]["current"] ?? []);
$impr_prev = $tc_vals($charts["impressions"]["previous"] ?? []);
$clk_cur = $tc_vals($charts["clicks"]["current"] ?? []);
$clk_prev = $tc_vals($charts["clicks"]["previous"] ?? []);

$has_data = !empty($views_cur)
    && (array_sum($views_cur) + array_sum($impr_cur) + array_sum($leads_cur) + array_sum($clk_cur)) > 0;
?>
<section class="card">
  <h3 style="margin:0 0 16px">Ruch</h3>

  <?php if (empty($kpi_cards)) : ?>
    <p class="muted">Brak danych — włącz sync i poczekaj na cron.</p>
  <?php else : ?>
  <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:20px">
    <?php foreach ($kpi_cards as $kpi) : ?>
    <div class="kpi">
      <span class="muted"><?php echo esc_html((string) ($kpi["label"] ?? "")); ?></span>
      <b><?php echo esc_html(number_format((float) ($kpi["value"] ?? 0), 0, ",", " ")); ?><?php echo esc_html((string) ($kpi["suffix"] ?? "")); ?></b>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if (!$has_data) : ?>
    <div style="padding:32px;text-align:center;background:var(--bg);border-radius:var(--r-md);border:1px dashed var(--border)">
      <div style="font-size:32px;margin-bottom:8px">📊</div>
      <p class="muted" style="margin:0">Brak danych — uruchom sync GSC lub poczekaj na cron dzienny.</p>
    </div>
  <?php else : ?>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
  <?php
    $defs = [
        ["id" => "ups-tc-v", "label" => "Wyświetlenia", "color" => "#0d9488", "cur" => $views_cur, "prev" => $views_prev],
        ["id" => "ups-tc-l", "label" => "Leady", "color" => "#2271b1", "cur" => $leads_cur, "prev" => $leads_prev],
        ["id" => "ups-tc-i", "label" => "Impressions GSC", "color" => "#8b5cf6", "cur" => $impr_cur, "prev" => $impr_prev],
        ["id" => "ups-tc-c", "label" => "Kliknięcia GSC", "color" => "#f59e0b", "cur" => $clk_cur, "prev" => $clk_prev],
    ];
    foreach ($defs as $d) :
        $s = array_sum($d["cur"]);
        $p = array_sum($d["prev"]);
        $delta = $p > 0 ? round(($s - $p) / $p * 100, 1) : 0;
        $pos = $delta >= 0;
        ?>
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r-md);padding:16px">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
        <div>
          <div style="font-size:10px;font-weight:700;letter-spacing:.6px;color:var(--text-3);text-transform:uppercase"><?php echo esc_html($d["label"]); ?></div>
          <div style="font-size:22px;font-weight:800;color:var(--text-main)"><?php echo esc_html(number_format($s)); ?></div>
        </div>
        <?php if ($p > 0) : ?>
        <div style="font-size:12px;font-weight:700;color:<?php echo $pos ? "var(--success)" : "var(--danger)"; ?>;padding:4px 10px;background:<?php echo $pos ? "#e8f5e9" : "#fce8e8"; ?>;border-radius:999px">
          <?php echo ($pos ? "+" : "") . esc_html((string) $delta); ?>%
        </div>
        <?php endif; ?>
      </div>
      <canvas id="<?php echo esc_attr($d["id"]); ?>" height="70" style="width:100%;display:block"></canvas>
    </div>
  <?php endforeach; ?>
  </div>

  <script>
  (function(){
    var D=[
      {id:"ups-tc-v",color:"#0d9488",cur:<?php echo wp_json_encode($views_cur); ?>,prev:<?php echo wp_json_encode($views_prev); ?>},
      {id:"ups-tc-l",color:"#2271b1",cur:<?php echo wp_json_encode($leads_cur); ?>,prev:<?php echo wp_json_encode($leads_prev); ?>},
      {id:"ups-tc-i",color:"#8b5cf6",cur:<?php echo wp_json_encode($impr_cur); ?>,prev:<?php echo wp_json_encode($impr_prev); ?>},
      {id:"ups-tc-c",color:"#f59e0b",cur:<?php echo wp_json_encode($clk_cur); ?>,prev:<?php echo wp_json_encode($clk_prev); ?>},
    ];
    function h2r(h){var r=parseInt(h.slice(1,3),16),g=parseInt(h.slice(3,5),16),b=parseInt(h.slice(5,7),16);return r+","+g+","+b;}
    function draw(cfg){
      var el=document.getElementById(cfg.id);
      if(!el) return;
      var W=el.parentElement.offsetWidth-32||300;
      el.width=W; el.height=70;
      var ctx=el.getContext("2d");
      var cur=cfg.cur||[],prev=cfg.prev||[];
      var all=cur.concat(prev),maxV=all.length?Math.max.apply(null,all):1;
      if(!maxV)maxV=1;
      var n=cur.length; if(!n)return;
      var pad=6,cW=W-pad*2,cH=70-pad*2,step=cW/n;
      if(prev.length){
        ctx.beginPath();
        prev.forEach(function(v,i){var x=pad+(i+.5)*step,y=pad+cH-(v/maxV)*cH;i?ctx.lineTo(x,y):ctx.moveTo(x,y);});
        ctx.strokeStyle="rgba(150,150,150,.3)";ctx.lineWidth=1.5;ctx.stroke();
      }
      ctx.beginPath();
      cur.forEach(function(v,i){var x=pad+(i+.5)*step,y=pad+cH-(v/maxV)*cH;i?ctx.lineTo(x,y):ctx.moveTo(x,y);});
      var lastX=pad+(n-.5)*step;
      ctx.lineTo(lastX,pad+cH);ctx.lineTo(pad+.5*step,pad+cH);ctx.closePath();
      var g=ctx.createLinearGradient(0,pad,0,pad+cH);
      g.addColorStop(0,"rgba("+h2r(cfg.color)+",.2)");g.addColorStop(1,"rgba("+h2r(cfg.color)+",.01)");
      ctx.fillStyle=g;ctx.fill();
      ctx.beginPath();
      cur.forEach(function(v,i){var x=pad+(i+.5)*step,y=pad+cH-(v/maxV)*cH;i?ctx.lineTo(x,y):ctx.moveTo(x,y);});
      ctx.strokeStyle=cfg.color;ctx.lineWidth=2;ctx.stroke();
    }
    if(document.readyState==="loading"){
      document.addEventListener("DOMContentLoaded",function(){D.forEach(draw);});
    } else {
      setTimeout(function(){D.forEach(draw);},50);
    }
  })();
  </script>

  <?php endif; ?>
</section>
