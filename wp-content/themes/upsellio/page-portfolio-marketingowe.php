<?php
if (!defined("ABSPATH")) {
    exit;
}

if (function_exists("upsellio_register_template_seo_head")) {
    upsellio_register_template_seo_head("portfolio_marketingowe");
}

get_header();

$items = function_exists("upsellio_get_marketing_portfolio_list") ? upsellio_get_marketing_portfolio_list(200) : [];
$featured = null;
$categories = [
    "meta" => "Meta",
    "google" => "Google",
    "strona" => "Strona",
    "ecom" => "Ecom",
];

foreach ($items as $item) {
    if ($featured === null && !empty($item["is_featured"])) {
        $featured = $item;
    }
}
if ($featured === null && !empty($items)) {
    $featured = $items[0];
}

$summary_projects = count($items);
$extract_metric_value = static function ($value): ?float {
    $text = trim((string) $value);
    if ($text === "") {
        return null;
    }

    if (!preg_match("/-?\d+(?:[.,]\d+)?/", $text, $matches)) {
        return null;
    }

    return (float) str_replace(",", ".", (string) $matches[0]);
};

$dashboard_index = [];
foreach ($categories as $slug => $label) {
    $dashboard_index[$slug] = [
        "slug" => $slug,
        "label" => $label,
        "projects" => 0,
        "metric_total" => 0.0,
        "metric_count" => 0,
        "roas_total" => 0.0,
        "roas_count" => 0,
        "cpl_total" => 0.0,
        "cpl_count" => 0,
        "conversion_total" => 0.0,
        "conversion_count" => 0,
    ];
}

foreach ($items as $item) {
    $category_slug = (string) ($item["category_slug"] ?? "meta");
    $category_label = (string) ($item["category"] ?? ucfirst($category_slug));
    if (!isset($dashboard_index[$category_slug])) {
        $dashboard_index[$category_slug] = [
            "slug" => $category_slug,
            "label" => $category_label,
            "projects" => 0,
            "metric_total" => 0.0,
            "metric_count" => 0,
            "roas_total" => 0.0,
            "roas_count" => 0,
            "cpl_total" => 0.0,
            "cpl_count" => 0,
            "conversion_total" => 0.0,
            "conversion_count" => 0,
        ];
    }

    $dashboard_index[$category_slug]["projects"]++;
    foreach ((array) ($item["kpis"] ?? []) as $kpi_item) {
        $metric_source = (string) (($kpi_item["change"] ?? "") !== "" ? $kpi_item["change"] : ($kpi_item["after"] ?? ""));
        $metric_value = $extract_metric_value($metric_source);
        if ($metric_value === null) {
            continue;
        }

        $dashboard_index[$category_slug]["metric_total"] += abs($metric_value);
        $dashboard_index[$category_slug]["metric_count"]++;

        $kpi_label = strtolower(trim((string) ($kpi_item["label"] ?? "")));
        if ($kpi_label !== "") {
            if (strpos($kpi_label, "roas") !== false) {
                $dashboard_index[$category_slug]["roas_total"] += abs($metric_value);
                $dashboard_index[$category_slug]["roas_count"]++;
            }
            if (strpos($kpi_label, "cpl") !== false || strpos($kpi_label, "cost per lead") !== false) {
                $dashboard_index[$category_slug]["cpl_total"] += abs($metric_value);
                $dashboard_index[$category_slug]["cpl_count"]++;
            }
            if (strpos($kpi_label, "konwers") !== false || strpos($kpi_label, "conversion") !== false || strpos($kpi_label, "cr") !== false) {
                $dashboard_index[$category_slug]["conversion_total"] += abs($metric_value);
                $dashboard_index[$category_slug]["conversion_count"]++;
            }
        }
    }
}

$dashboard_rows = [];
foreach ($dashboard_index as $slug => $row) {
    $projects = (int) ($row["projects"] ?? 0);
    $metric_count = (int) ($row["metric_count"] ?? 0);
    $metric_avg = $metric_count > 0 ? ((float) $row["metric_total"]) / $metric_count : 0.0;
    $roas_avg = ((int) ($row["roas_count"] ?? 0)) > 0 ? ((float) $row["roas_total"]) / ((int) $row["roas_count"]) : 0.0;
    $cpl_avg = ((int) ($row["cpl_count"] ?? 0)) > 0 ? ((float) $row["cpl_total"]) / ((int) $row["cpl_count"]) : 0.0;
    $conversion_avg = ((int) ($row["conversion_count"] ?? 0)) > 0 ? ((float) $row["conversion_total"]) / ((int) $row["conversion_count"]) : 0.0;
    $performance_index = min(96, max(34, (int) round(36 + ($projects * 12) + ($metric_avg * 0.18))));
    $roas_index = min(96, max(28, (int) round(32 + ($roas_avg * 0.44) + ($projects * 3))));
    $cpl_index = min(96, max(28, (int) round(34 + ($cpl_avg * 0.5) + ($projects * 2))));
    $conversion_index = min(96, max(28, (int) round(30 + ($conversion_avg * 0.48) + ($projects * 3))));

    $dashboard_rows[] = [
        "slug" => (string) $slug,
        "label" => (string) ($row["label"] ?? ucfirst((string) $slug)),
        "projects" => $projects,
        "metric_avg" => round($metric_avg, 1),
        "index_performance" => $performance_index,
        "index_roas" => $roas_index,
        "index_cpl" => $cpl_index,
        "index_conversion" => $conversion_index,
    ];
}

$dashboard_max = 0;
foreach ($dashboard_rows as $row) {
    $dashboard_max = max($dashboard_max, (int) ($row["index_performance"] ?? 0));
}
if ($dashboard_max <= 0) {
    $dashboard_max = 100;
}

$schema_item_list = [
    "@context" => "https://schema.org",
    "@type" => "ItemList",
    "name" => "Portfolio marketingowe Upsellio",
    "description" => "Case studies marketingowe: meta, google, strona i ecom.",
    "itemListOrder" => "https://schema.org/ItemListOrderAscending",
    "numberOfItems" => $summary_projects,
    "itemListElement" => [],
];
foreach ($items as $index => $item) {
    $schema_item_list["itemListElement"][] = [
        "@type" => "ListItem",
        "position" => $index + 1,
        "url" => (string) ($item["url"] ?? ""),
        "name" => (string) ($item["title"] ?? ""),
        "description" => wp_trim_words((string) ($item["excerpt"] ?? ""), 28, ""),
    ];
}
?>
<style>
  .mport{background:#fff;color:#111110}
  .mwrap{max-width:1080px;margin:0 auto;padding:0 40px}
  .mhero{padding:88px 0 60px;border-bottom:1px solid #e5e5e1;background:linear-gradient(180deg,#f8f8f6 0%,#fff 72%)}
  .mpill{display:inline-flex;align-items:center;gap:8px;background:#f8f8f6;border:1px solid #c8c8c2;border-radius:100px;padding:6px 14px 6px 8px;margin-bottom:32px}
  .mpill-dot{width:8px;height:8px;border-radius:50%;background:#1D9E75}
  .mh1{font-family:"Syne",sans-serif;font-size:clamp(34px,5vw,54px);line-height:1.06;letter-spacing:-2px;margin:0 0 18px}
  .mlead{font-size:15px;line-height:1.72;color:#3d3d38;max-width:650px;margin:0}
  .mhero-actions{display:flex;gap:16px;flex-wrap:wrap;margin-top:28px}
  .mbtn{display:inline-flex;align-items:center;gap:8px;border-radius:12px;padding:12px 20px;font-size:14px;font-weight:600}
  .mbtn-primary{background:#1D9E75;color:#fff}
  .mbtn-ghost{border:1px solid #c8c8c2;color:#111110}
  .mstats{display:flex;gap:32px;flex-wrap:wrap;margin-top:34px;padding-top:26px;border-top:1px solid #e5e5e1}
  .mstats strong{display:block;font-family:"Syne",sans-serif;font-size:24px;color:#1D9E75;line-height:1}
  .mstats span{font-size:12px;color:#3d3d38;line-height:1.4}
  .mfilters{position:sticky;top:72px;z-index:20;background:#fff;border-bottom:1px solid #e5e5e1}
  .mfilters-inner{display:flex;gap:8px;padding:14px 0;overflow:auto}
  .mfilter{border:1px solid #e5e5e1;border-radius:100px;background:#fff;color:#3d3d38;padding:7px 14px;font-size:13px;font-weight:500;white-space:nowrap;cursor:pointer}
  .mfilter.active{background:#1D9E75;color:#fff;border-color:#1D9E75}
  .minsight{padding:42px 0 12px}
  .minsight-card{--metric-accent:#7df7cd;--metric-accent-soft:#24d09d;background:linear-gradient(135deg,#0d372f,#145445);border:1px solid #0d5d4c;border-radius:28px;padding:30px;color:#f2fff8;box-shadow:0 14px 34px rgba(0,0,0,.12);position:relative;overflow:hidden}
  .minsight-card::after{content:"";position:absolute;inset:-80% -10% auto -10%;height:220px;background:linear-gradient(90deg,rgba(125,247,205,0),rgba(125,247,205,.25),rgba(125,247,205,0));transform:rotate(-8deg);animation:minsweep 7s linear infinite;pointer-events:none}
  @keyframes minsweep{0%{transform:translateX(-45%) rotate(-8deg)}100%{transform:translateX(48%) rotate(-8deg)}}
  .minsight-head{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;flex-wrap:wrap;margin-bottom:20px}
  .minsight-head h2{font-family:"Syne",sans-serif;font-size:28px;line-height:1.08;margin:0}
  .minsight-head p{margin:0;font-size:13px;line-height:1.6;color:rgba(242,255,248,.76)}
  .minsight-switch{display:flex;gap:8px;flex-wrap:wrap}
  .minsight-switch button{border:1px solid rgba(255,255,255,.28);background:rgba(255,255,255,.08);color:#e8fff7;border-radius:999px;padding:8px 12px;font-size:12px;cursor:pointer;transition:all .2s ease}
  .minsight-switch button.active{background:var(--metric-accent);color:#074636;border-color:var(--metric-accent);font-weight:600}
  .minsight-grid{display:grid;grid-template-columns:1.2fr 1fr;gap:24px}
  .minsight-bars{display:flex;flex-direction:column;gap:10px}
  .minsight-row{display:grid;grid-template-columns:96px 1fr auto;align-items:center;gap:10px;font-size:13px}
  .minsight-track{height:12px;background:rgba(255,255,255,.16);border-radius:99px;overflow:hidden}
  .minsight-fill{display:block;height:100%;background:linear-gradient(90deg,var(--metric-accent),var(--metric-accent-soft));width:0;border-radius:99px;transition:width .45s cubic-bezier(.22,.61,.36,1),background .28s ease}
  .minsight-row strong{font-family:"Syne",sans-serif;font-size:18px;line-height:1}
  .minsight-row.is-dim{opacity:.45}
  .minsight-row.is-active{opacity:1}
  .minsight-line{border:1px solid rgba(255,255,255,.22);border-radius:18px;padding:14px;background:rgba(10,44,37,.35)}
  .minsight-line-wrap{position:relative}
  .minsight-line svg{display:block;width:100%;height:150px;overflow:visible}
  .minsight-dot{fill:var(--metric-accent);stroke:#0d372f;stroke-width:2;cursor:pointer;transition:r .16s ease,stroke-width .16s ease}
  .minsight-dot:hover{r:7}
  .minsight-dot.is-pinned{stroke:#fff;stroke-width:3}
  .minsight-legend{display:flex;gap:10px;flex-wrap:wrap;margin-top:10px}
  .minsight-legend-item{display:inline-flex;align-items:center;gap:7px;font-size:11px;color:rgba(242,255,248,.8);padding:4px 9px;border-radius:999px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.14)}
  .minsight-legend-swatch{width:12px;height:12px;border-radius:999px;display:inline-block}
  .minsight-legend-swatch.is-line{background:var(--metric-accent)}
  .minsight-legend-swatch.is-bars{background:linear-gradient(90deg,var(--metric-accent),var(--metric-accent-soft))}
  .minsight-tooltip{position:absolute;z-index:5;background:#0b2f27;color:#eafff5;border:1px solid rgba(125,247,205,.45);border-radius:10px;padding:6px 10px;font-size:12px;line-height:1.3;box-shadow:0 10px 28px rgba(0,0,0,.22);transform:translate(-50%,-120%);pointer-events:none;opacity:0;transition:opacity .18s ease,transform .18s ease}
  .minsight-tooltip.show{opacity:1}
  .minsight-tooltip.is-pinned{opacity:1;transform:translate(-50%,-132%)}
  .minsight-line p{margin:8px 0 0;font-size:12px;color:rgba(242,255,248,.72)}
  .msection{padding:64px 0 80px}
  .msummary{display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;background:#e8f8f2;border:1px solid #c3eddd;border-radius:28px;padding:24px 32px;margin-bottom:40px}
  .msum-item strong{font-family:"Syne",sans-serif;font-size:26px;line-height:1;color:#085041}
  .msum-item span{font-size:12px;color:#17885f}
  .mhead{display:flex;align-items:flex-end;justify-content:space-between;gap:24px;margin-bottom:24px}
  .mhead h2{font-family:"Syne",sans-serif;font-size:30px;line-height:1.15;letter-spacing:-.5px;margin:0}
  .mgrid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
  .mcard{border:1px solid #e5e5e1;border-radius:28px;overflow:hidden;display:flex;flex-direction:column;background:#fff;box-shadow:0 1px 4px rgba(0,0,0,.06)}
  .mcard.hide{display:none}
  .mcard.is-visible{animation:mcardIn .35s ease}
  @keyframes mcardIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
  .mvisual{height:190px;padding:24px 32px;position:relative;display:flex;align-items:flex-end}
  .mnum{position:absolute;right:16px;bottom:-10px;font-family:"Syne",sans-serif;font-size:86px;line-height:1;color:rgba(255,255,255,.12)}
  .mkpis{display:flex;gap:10px;position:relative;z-index:1}
  .mkpi{background:rgba(255,255,255,.9);border-radius:12px;padding:8px 10px}
  .mkpi b{display:block;font-family:"Syne",sans-serif;font-size:17px;line-height:1}
  .mkpi span{font-size:11px;color:#3d3d38}
  .mcard-body{padding:24px 24px 20px;display:flex;flex-direction:column;flex:1}
  .mmeta{display:flex;gap:6px;align-items:center;font-size:11px;color:#7c7c74;margin-bottom:10px;flex-wrap:wrap}
  .mcard h3{font-family:"Syne",sans-serif;font-size:18px;line-height:1.24;margin:0 0 10px}
  .mcard p{font-size:13px;line-height:1.6;color:#3d3d38;margin:0;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
  .mtags{display:flex;gap:6px;flex-wrap:wrap;margin-top:14px}
  .mtag{font-size:11px;background:#f1f1ee;border-radius:8px;padding:3px 8px;color:#3d3d38}
  .mlink{margin-top:auto;padding-top:18px;font-size:14px;font-weight:600;color:#1D9E75}
  .mspark{position:absolute;left:26px;right:26px;top:20px;display:flex;gap:5px;align-items:flex-end;z-index:1;opacity:.95}
  .mspark span{flex:1;border-radius:7px 7px 3px 3px;background:rgba(255,255,255,.28);min-height:18px}
  .vis-meta{background:linear-gradient(135deg,#1a3a5c,#2563a8)} .vis-google{background:linear-gradient(135deg,#0d5c2e,#1D9E75)}
  .vis-ecom{background:linear-gradient(135deg,#4a1b7a,#7c3aed)} .vis-landing{background:linear-gradient(135deg,#7a2d00,#c2440a)}
  .vis-b2b{background:linear-gradient(135deg,#1a2a4a,#2d5182)} .vis-social{background:linear-gradient(135deg,#3d1a5c,#9333ea)}
  .mno{display:none;text-align:center;color:#7c7c74;padding:30px 0}
  .mtesti{margin-top:60px;padding:50px 0;border-top:1px solid #e5e5e1;border-bottom:1px solid #e5e5e1;background:#f8f8f6}
  .mtesti-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
  .mtesti-card{border:1px solid #e5e5e1;border-radius:22px;padding:24px;background:#fff}
  .mtesti-card p{font-size:14px;line-height:1.72;color:#3d3d38}
  .mcta{padding:80px 0;background:#f8f8f6;border-top:1px solid #e5e5e1}
  .mcta-box{background:#e8f8f2;border:1.5px solid #c3eddd;border-radius:28px;padding:56px;display:grid;grid-template-columns:1fr auto;gap:48px;align-items:center}
  .mcta-box h2{font-family:"Syne",sans-serif;font-size:34px;line-height:1.08;color:#085041;margin:0 0 12px}
  .mcta-box p{margin:0;color:#17885f;font-size:15px;line-height:1.75;max-width:560px}
  [data-reveal]{opacity:0;transform:translateY(22px) scale(.985);filter:blur(5px);transition:opacity .72s cubic-bezier(.22,.61,.36,1),transform .72s cubic-bezier(.22,.61,.36,1),filter .72s cubic-bezier(.22,.61,.36,1);transition-delay:var(--reveal-delay,0ms);will-change:opacity,transform,filter}
  [data-reveal].is-revealed{opacity:1;transform:translateY(0) scale(1);filter:blur(0)}
  @media(max-width:900px){.minsight-grid{grid-template-columns:1fr}.mgrid{grid-template-columns:1fr 1fr}.mtesti-grid{grid-template-columns:1fr 1fr}.mcta-box{grid-template-columns:1fr;padding:36px}}
  @media(max-width:640px){.mwrap{padding:0 24px}.mgrid,.mtesti-grid{grid-template-columns:1fr}.mhero{padding:64px 0 46px}}
</style>

<main class="mport">
  <section class="mhero" data-reveal style="--reveal-delay:30ms;">
    <div class="mwrap">
      <div class="mpill"><span class="mpill-dot"></span><span>Kampanie, strony i sklepy z mierzalnymi wynikami</span></div>
      <h1 class="mh1">Portfolio marketingowe.<br><span style="color:#1D9E75;">Liczby, nie obietnice.</span></h1>
      <p class="mlead">Case studies Meta Ads, Google Ads, landing page i e-commerce przygotowane pod SEO i lead generation. Każdy wpis pokazuje problem, wdrożenie, metryki oraz następny krok dla firmy.</p>
      <div class="mhero-actions">
        <a href="#projekty" class="mbtn mbtn-primary">Zobacz projekty →</a>
        <a href="<?php echo esc_url(home_url("/#kontakt")); ?>" class="mbtn mbtn-ghost">Omów swój projekt</a>
      </div>
      <div class="mstats">
        <div><strong><?php echo esc_html((string) max(8, $summary_projects)); ?>+</strong><span>projektów marketingowych</span></div>
        <div><strong>3×</strong><span>wzrost jakości leadów</span></div>
        <div><strong>-40%</strong><span>średni spadek CPL</span></div>
      </div>
    </div>
  </section>

  <section class="mfilters" data-reveal style="--reveal-delay:80ms;">
    <div class="mwrap mfilters-inner" id="mfilters">
      <button class="mfilter active" data-filter="all">Wszystkie</button>
      <?php foreach ($categories as $slug => $name) : ?>
        <button class="mfilter" data-filter="<?php echo esc_attr($slug); ?>"><?php echo esc_html($name); ?></button>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="minsight" data-reveal style="--reveal-delay:120ms;">
    <div class="mwrap">
      <div class="minsight-card">
        <div class="minsight-head">
          <h2>Interaktywny pulpit wynikow</h2>
          <p id="minsightText">Filtruj kategorie i porownuj ich aktualny indeks efektywnosci.</p>
          <div class="minsight-switch" id="minsightSwitch">
            <button type="button" class="active" data-metric="performance">Indeks</button>
            <button type="button" data-metric="roas">ROAS</button>
            <button type="button" data-metric="cpl">CPL</button>
            <button type="button" data-metric="conversion">Konwersja</button>
          </div>
        </div>
        <div class="minsight-grid">
          <div class="minsight-bars" id="minsightBars">
            <?php foreach ($dashboard_rows as $row) : ?>
              <?php
              $index_value = (int) ($row["index_performance"] ?? 0);
              $roas_value = (int) ($row["index_roas"] ?? 0);
              $cpl_value = (int) ($row["index_cpl"] ?? 0);
              $conversion_value = (int) ($row["index_conversion"] ?? 0);
              $width_percent = max(8, min(100, (int) round(($index_value / $dashboard_max) * 100)));
              ?>
              <div class="minsight-row" data-slug="<?php echo esc_attr((string) $row["slug"]); ?>" data-performance="<?php echo esc_attr((string) $index_value); ?>" data-roas="<?php echo esc_attr((string) $roas_value); ?>" data-cpl="<?php echo esc_attr((string) $cpl_value); ?>" data-conversion="<?php echo esc_attr((string) $conversion_value); ?>">
                <span><?php echo esc_html((string) $row["label"]); ?></span>
                <div class="minsight-track"><i class="minsight-fill" style="width:<?php echo esc_attr((string) $width_percent); ?>%;"></i></div>
                <strong><?php echo esc_html((string) $index_value); ?></strong>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="minsight-line">
            <div class="minsight-line-wrap" id="minsightLineWrap">
              <svg id="minsightSvg" viewBox="0 0 340 150" preserveAspectRatio="none" role="img" aria-label="Trend efektywnosci case studies">
                <polyline id="minsightLine" points="0,120 68,110 136,95 204,100 272,90 340,84" fill="none" stroke="#7df7cd" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"></polyline>
                <g id="minsightDots"></g>
              </svg>
              <div class="minsight-tooltip" id="minsightTooltip"></div>
            </div>
            <div class="minsight-legend" id="minsightLegend">
              <span class="minsight-legend-item"><i class="minsight-legend-swatch is-line"></i><span id="minsightLegendLine">Linia trendu: Indeks</span></span>
              <span class="minsight-legend-item"><i class="minsight-legend-swatch is-bars"></i><span id="minsightLegendBars">Paski kategorii: Indeks</span></span>
            </div>
            <p id="minsightCaption">Trend wyliczany dynamicznie na podstawie widocznych case studies.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="msection" id="projekty" data-reveal style="--reveal-delay:170ms;">
    <div class="mwrap">
      <div class="msummary">
        <div class="msum-item"><strong><?php echo esc_html((string) $summary_projects); ?></strong><span>projektów w portfolio</span></div>
        <div class="msum-item"><strong>+187%</strong><span>maks. wzrost ROAS</span></div>
        <div class="msum-item"><strong>-52%</strong><span>maks. spadek CPL</span></div>
        <div class="msum-item"><strong>+340%</strong><span>maks. wzrost konwersji</span></div>
      </div>
      <div class="mhead">
        <h2>Wszystkie case studies</h2>
        <div id="mcount"><?php echo esc_html((string) $summary_projects); ?> projektów</div>
      </div>
      <div class="mgrid" id="mgrid">
        <?php foreach ($items as $index => $item) : ?>
          <?php
          $theme = (string) ($item["theme"] ?: "vis-meta");
          $kpis = (array) ($item["kpis"] ?? []);
          $kpi_1 = $kpis[0]["change"] ?? ($kpis[0]["after"] ?? "");
          $kpi_1_label = $kpis[0]["label"] ?? "Wynik";
          $kpi_2 = $kpis[1]["change"] ?? ($kpis[1]["after"] ?? "");
          $kpi_2_label = $kpis[1]["label"] ?? "Efekt";
          $score_total = 0.0;
          $score_count = 0;
          $score_roas = null;
          $score_cpl = null;
          $score_conversion = null;
          foreach ($kpis as $kpi_item) {
              $score_source = (string) (($kpi_item["change"] ?? "") !== "" ? $kpi_item["change"] : ($kpi_item["after"] ?? ""));
              $score_value = $extract_metric_value($score_source);
              if ($score_value === null) {
                  continue;
              }
              $score_total += abs($score_value);
              $score_count++;

              $label_text = strtolower(trim((string) ($kpi_item["label"] ?? "")));
              $value_points = max(20, min(96, (int) round(28 + (abs($score_value) * 0.7))));
              if ($label_text !== "") {
                  if ($score_roas === null && strpos($label_text, "roas") !== false) {
                      $score_roas = $value_points;
                  }
                  if ($score_cpl === null && (strpos($label_text, "cpl") !== false || strpos($label_text, "cost per lead") !== false)) {
                      $score_cpl = $value_points;
                  }
                  if ($score_conversion === null && (strpos($label_text, "konwers") !== false || strpos($label_text, "conversion") !== false || strpos($label_text, "cr") !== false)) {
                      $score_conversion = $value_points;
                  }
              }
          }
          $score_raw = $score_count > 0 ? $score_total / $score_count : (38 + (($index + 1) * 6));
          $score = max(28, min(96, (int) round(30 + ($score_raw * 0.35))));
          $score_roas = $score_roas === null ? $score : $score_roas;
          $score_cpl = $score_cpl === null ? $score : $score_cpl;
          $score_conversion = $score_conversion === null ? $score : $score_conversion;
          $spark_1 = max(20, min(92, $score - 18));
          $spark_2 = max(22, min(96, $score - 8));
          $spark_3 = max(24, min(99, $score + 6));
          ?>
          <a href="<?php echo esc_url((string) $item["url"]); ?>" class="mcard is-visible" data-reveal style="--reveal-delay:<?php echo esc_attr((string) (($index % 6) * 55)); ?>ms;" data-filter="<?php echo esc_attr((string) $item["category_slug"]); ?>" data-score-performance="<?php echo esc_attr((string) $score); ?>" data-score-roas="<?php echo esc_attr((string) $score_roas); ?>" data-score-cpl="<?php echo esc_attr((string) $score_cpl); ?>" data-score-conversion="<?php echo esc_attr((string) $score_conversion); ?>">
            <div class="mvisual <?php echo esc_attr($theme); ?>">
              <div class="mspark" aria-hidden="true"><span style="height:<?php echo esc_attr((string) $spark_1); ?>%"></span><span style="height:<?php echo esc_attr((string) $spark_2); ?>%"></span><span style="height:<?php echo esc_attr((string) $spark_3); ?>%"></span></div>
              <div class="mnum"><?php echo esc_html(str_pad((string) ($index + 1), 2, "0", STR_PAD_LEFT)); ?></div>
              <div class="mkpis">
                <div class="mkpi"><b><?php echo esc_html((string) $kpi_1); ?></b><span><?php echo esc_html((string) $kpi_1_label); ?></span></div>
                <div class="mkpi"><b><?php echo esc_html((string) $kpi_2); ?></b><span><?php echo esc_html((string) $kpi_2_label); ?></span></div>
              </div>
            </div>
            <div class="mcard-body">
              <div class="mmeta">
                <span><?php echo esc_html((string) $item["category"]); ?></span><span>•</span><span><?php echo esc_html((string) ($item["date"] ?: "2024")); ?></span>
              </div>
              <h3><?php echo esc_html((string) $item["title"]); ?></h3>
              <p><?php echo esc_html((string) $item["excerpt"]); ?></p>
              <div class="mtags">
                <?php foreach (array_slice((array) ($item["tags"] ?? []), 0, 3) as $tag) : ?>
                  <span class="mtag"><?php echo esc_html((string) $tag); ?></span>
                <?php endforeach; ?>
              </div>
              <span class="mlink"><?php echo esc_html((string) ($item["cta"] ?: "Zobacz case study")); ?> ↗</span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
      <div id="mno" class="mno">Brak projektów w tej kategorii.</div>
      <div class="mtesti" data-reveal style="--reveal-delay:200ms;">
        <div class="mtesti-grid">
          <div class="mtesti-card"><p>„Po 3 miesiącach CPL spadł o połowę, a handlowcy dostają leady, z którymi da się rozmawiać o sprzedaży.”</p></div>
          <div class="mtesti-card"><p>„To nie było tylko ustawienie kampanii. Dostaliśmy nowy sposób myślenia o całym lejku.”</p></div>
          <div class="mtesti-card"><p>„Strona zaczęła generować zapytania już w pierwszym miesiącu po wdrożeniu i SEO.”</p></div>
        </div>
      </div>
    </div>
  </section>

  <section class="mcta" data-reveal style="--reveal-delay:240ms;">
    <div class="mwrap">
      <div class="mcta-box">
        <div>
          <h2>Chcesz podobnych wyników?</h2>
          <p>Zacznij od 30-minutowej rozmowy. Dostaniesz konkretną diagnozę: co blokuje wzrost i jakie działania marketingowe dają największy efekt najszybciej.</p>
        </div>
        <div>
          <a class="mbtn mbtn-primary" href="<?php echo esc_url(home_url("/#kontakt")); ?>">Umów bezpłatną rozmowę →</a>
        </div>
      </div>
    </div>
  </section>
</main>

<script>
(() => {
  const grid = document.getElementById("mgrid");
  const count = document.getElementById("mcount");
  const noResults = document.getElementById("mno");
  const filters = Array.from(document.querySelectorAll(".mfilter"));
  const insightRows = Array.from(document.querySelectorAll(".minsight-row"));
  const insightSwitch = Array.from(document.querySelectorAll("#minsightSwitch button"));
  const insightLine = document.getElementById("minsightLine");
  const insightDots = document.getElementById("minsightDots");
  const insightTooltip = document.getElementById("minsightTooltip");
  const insightLineWrap = document.getElementById("minsightLineWrap");
  const insightText = document.getElementById("minsightText");
  const insightLegendLine = document.getElementById("minsightLegendLine");
  const insightLegendBars = document.getElementById("minsightLegendBars");
  const insightCard = document.querySelector(".minsight-card");
  const insightCaption = document.getElementById("minsightCaption");
  if (!grid || !count || !noResults || !filters.length) return;

  const cards = Array.from(grid.querySelectorAll(".mcard"));
  let activeMetric = "performance";
  let activeFilter = "all";
  let currentLinePoints = [];
  let pinnedDotKey = null;
  let morphFrame = null;
  const pluralize = (value) => {
    if (value === 1) return "projekt";
    if (value < 5) return "projekty";
    return "projektów";
  };

  const metricConfig = {
    performance: { label: "Indeks", suffix: "/100", lineColor: "#7df7cd", barColor: "#24d09d" },
    roas: { label: "ROAS", suffix: " pkt", lineColor: "#75b9ff", barColor: "#3f82eb" },
    cpl: { label: "CPL", suffix: " pkt", lineColor: "#ffb478", barColor: "#f07a2a" },
    conversion: { label: "Konwersja", suffix: " pkt", lineColor: "#d8a3ff", barColor: "#9e59ea" },
  };

  const getMetricAttribute = (metric) => `data-score-${metric}`;
  const getMetricLabel = (metric) => (metricConfig[metric] ? metricConfig[metric].label : metricConfig.performance.label);
  const getMetricSuffix = (metric) => (metricConfig[metric] ? metricConfig[metric].suffix : metricConfig.performance.suffix);
  const toPointString = (points) => points.map((point) => `${Math.round(point.x)},${Math.round(point.y)}`).join(" ");

  const getDotKey = (point) => `${activeMetric}-${activeFilter}-${point.index}`;

  const syncThemeByMetric = () => {
    const config = metricConfig[activeMetric] || metricConfig.performance;
    if (insightCard) {
      insightCard.style.setProperty("--metric-accent", config.lineColor);
      insightCard.style.setProperty("--metric-accent-soft", config.barColor);
    }
    if (insightLine) {
      insightLine.setAttribute("stroke", config.lineColor);
    }
    if (insightLegendLine) {
      insightLegendLine.textContent = `Linia trendu: ${config.label}`;
    }
    if (insightLegendBars) {
      insightLegendBars.textContent = `Paski kategorii: ${config.label}`;
    }
  };

  const setTooltipPositionFromDot = (dot) => {
    if (!dot || !insightLineWrap || !insightTooltip) return;
    const wrapperRect = insightLineWrap.getBoundingClientRect();
    const dotRect = dot.getBoundingClientRect();
    const left = dotRect.left - wrapperRect.left + (dotRect.width / 2);
    const top = dotRect.top - wrapperRect.top;
    insightTooltip.style.left = `${left}px`;
    insightTooltip.style.top = `${top}px`;
  };

  const showTooltip = (dot, isPinned = false) => {
    if (!dot || !insightTooltip) return;
    const value = dot.getAttribute("data-value") || "0";
    const pointNumber = dot.getAttribute("data-index") || "1";
    insightTooltip.textContent = `Punkt ${pointNumber}: ${value}${getMetricSuffix(activeMetric)}`;
    setTooltipPositionFromDot(dot);
    insightTooltip.classList.add("show");
    insightTooltip.classList.toggle("is-pinned", isPinned);
  };

  const hideTooltip = (force = false) => {
    if (!insightTooltip) return;
    if (pinnedDotKey && !force) return;
    insightTooltip.classList.remove("show");
    insightTooltip.classList.remove("is-pinned");
  };

  const renderDots = (points) => {
    if (!insightDots) return;
    insightDots.innerHTML = points
      .map((point) => {
        const dotKey = getDotKey(point);
        const pinnedClass = pinnedDotKey === dotKey ? " is-pinned" : "";
        return `<circle class="minsight-dot${pinnedClass}" cx="${Math.round(point.x)}" cy="${Math.round(point.y)}" r="5" data-key="${dotKey}" data-value="${point.value}" data-index="${point.index}" />`;
      })
      .join("");

    if (pinnedDotKey) {
      const pinnedDot = insightDots.querySelector(`[data-key="${pinnedDotKey}"]`);
      if (pinnedDot) {
        showTooltip(pinnedDot, true);
      } else {
        pinnedDotKey = null;
        hideTooltip(true);
      }
    }
  };

  const alignPointLength = (points, length) => {
    if (!points.length) return [];
    if (points.length === length) return points.slice();
    if (length <= 1) return [points[0]];
    return Array.from({ length }, (_, index) => {
      const sourceIndex = Math.round((index * (points.length - 1)) / (length - 1));
      return points[sourceIndex];
    });
  };

  const animateLineMorph = (targetPoints) => {
    if (!insightLine) return;
    if (!currentLinePoints.length) {
      currentLinePoints = targetPoints.slice();
      insightLine.setAttribute("points", toPointString(currentLinePoints));
      renderDots(currentLinePoints);
      return;
    }

    if (morphFrame) {
      cancelAnimationFrame(morphFrame);
    }

    const duration = 360;
    const startPoints = alignPointLength(currentLinePoints, targetPoints.length);
    const endPoints = alignPointLength(targetPoints, targetPoints.length);
    const startTime = performance.now();

    const ease = (t) => 1 - Math.pow(1 - t, 3);
    const tick = (now) => {
      const progress = Math.min(1, (now - startTime) / duration);
      const eased = ease(progress);
      const mixed = endPoints.map((endPoint, index) => {
        const startPoint = startPoints[index] || startPoints[startPoints.length - 1];
        return {
          x: startPoint.x + ((endPoint.x - startPoint.x) * eased),
          y: startPoint.y + ((endPoint.y - startPoint.y) * eased),
        };
      });
      insightLine.setAttribute("points", toPointString(mixed));

      if (progress < 1) {
        morphFrame = requestAnimationFrame(tick);
        return;
      }

      currentLinePoints = targetPoints.slice();
      renderDots(currentLinePoints);
    };

    morphFrame = requestAnimationFrame(tick);
  };

  const renderTrendLine = (visibleCards) => {
    if (!insightLine) return;
    const metricAttribute = getMetricAttribute(activeMetric);
    const sourceScores = visibleCards
      .slice(0, 6)
      .map((card) => Number(card.getAttribute(metricAttribute) || 0))
      .filter((value) => value > 0);

    const points = sourceScores.length ? sourceScores : [42, 50, 54, 60, 64, 68];
    const max = Math.max(...points);
    const min = Math.min(...points);
    const spread = Math.max(1, max - min);
    const step = points.length > 1 ? 340 / (points.length - 1) : 340;
    const pointObjects = points
      .map((value, index) => {
        const normalized = (value - min) / spread;
        return {
          x: Math.round(index * step),
          y: Math.round(132 - normalized * 90),
          value: Math.round(value),
          index: index + 1,
        };
      });

    animateLineMorph(pointObjects);
  };

  const updateCategoryBars = () => {
    if (!insightRows.length) return;

    const config = metricConfig[activeMetric] || metricConfig.performance;
    const metricDataKey = activeMetric === "performance" ? "data-performance" : `data-${activeMetric}`;
    const values = insightRows
      .map((row) => Number(row.getAttribute(metricDataKey) || 0))
      .filter((value) => value > 0);
    const max = values.length ? Math.max(...values) : 1;

    insightRows.forEach((row) => {
      const current = Number(row.getAttribute(metricDataKey) || 0);
      const width = Math.max(8, Math.round((current / max) * 100));
      const fill = row.querySelector(".minsight-fill");
      const number = row.querySelector("strong");
      if (fill) fill.style.width = `${width}%`;
      if (fill) fill.style.background = `linear-gradient(90deg, ${config.lineColor}, ${config.barColor})`;
      if (number) number.textContent = `${current}`;
    });
  };

  const updateInsights = (target, visibleCards) => {
    if (!insightRows.length) return;
    updateCategoryBars();

    insightRows.forEach((row) => {
      const rowSlug = row.getAttribute("data-slug") || "";
      const isActive = target === "all" || rowSlug === target;
      row.classList.toggle("is-active", isActive);
      row.classList.toggle("is-dim", !isActive);
    });

    if (insightText) {
      insightText.textContent =
        target === "all"
          ? `Aktywna metryka: ${getMetricLabel(activeMetric)}. Filtruj i porownuj kategorie.`
          : `Aktywna metryka: ${getMetricLabel(activeMetric)}. Widok skupiony na jednej kategorii.`;
    }

    if (insightCaption) {
      const metricAttribute = getMetricAttribute(activeMetric);
      const scores = visibleCards
        .map((card) => Number(card.getAttribute(metricAttribute) || 0))
        .filter((value) => value > 0);
      const average = scores.length
        ? Math.round(scores.reduce((sum, value) => sum + value, 0) / scores.length)
        : 0;
      insightCaption.textContent = scores.length
        ? `Srednia dla metryki ${getMetricLabel(activeMetric)}: ${average}${getMetricSuffix(activeMetric)}.`
        : "Brak danych dla wybranego filtra.";
    }

    renderTrendLine(visibleCards);
  };

  const applyFilter = (target) => {
      activeFilter = target;
      filters.forEach((button) => button.classList.remove("active"));
      const activeButton = filters.find((button) => (button.getAttribute("data-filter") || "all") === target);
      if (activeButton) activeButton.classList.add("active");

      let visible = 0;
      const visibleCards = [];
      cards.forEach((card) => {
        const match = target === "all" || (card.getAttribute("data-filter") || "") === target;
        card.classList.toggle("hide", !match);
        card.classList.toggle("is-visible", match);
        if (match) visible++;
        if (match) visibleCards.push(card);
      });

      count.textContent = `${visible} ${pluralize(visible)}`;
      noResults.style.display = visible === 0 ? "block" : "none";
      updateInsights(target, visibleCards);
  };

  filters.forEach((filterButton) => {
    filterButton.addEventListener("click", () => {
      const target = filterButton.getAttribute("data-filter") || "all";
      applyFilter(target);
    });
  });

  insightSwitch.forEach((switchButton) => {
    switchButton.addEventListener("click", () => {
      const metric = switchButton.getAttribute("data-metric") || "performance";
      activeMetric = metric;
      insightSwitch.forEach((button) => button.classList.remove("active"));
      switchButton.classList.add("active");
      syncThemeByMetric();
      applyFilter(activeFilter);
    });
  });

  if (insightDots && insightTooltip && insightLineWrap) {
    insightDots.addEventListener("mouseover", (event) => {
      if (pinnedDotKey) return;
      const dot = event.target.closest(".minsight-dot");
      if (!dot) return;
      showTooltip(dot);
    });
    insightDots.addEventListener("mousemove", (event) => {
      if (pinnedDotKey) return;
      const dot = event.target.closest(".minsight-dot");
      if (!dot) return;
      setTooltipPositionFromDot(dot);
    });
    insightDots.addEventListener("click", (event) => {
      const dot = event.target.closest(".minsight-dot");
      if (!dot) return;
      const key = dot.getAttribute("data-key") || "";
      if (pinnedDotKey === key) {
        pinnedDotKey = null;
        renderDots(currentLinePoints);
        hideTooltip(true);
        return;
      }
      pinnedDotKey = key;
      renderDots(currentLinePoints);
      showTooltip(dot, true);
    });
    insightDots.addEventListener("mouseleave", () => hideTooltip(false));
    insightLineWrap.addEventListener("mouseleave", () => hideTooltip(false));
    insightLineWrap.addEventListener("click", (event) => {
      if (event.target.closest(".minsight-dot")) return;
      pinnedDotKey = null;
      renderDots(currentLinePoints);
      hideTooltip(true);
    });
  }

  const revealNodes = Array.from(document.querySelectorAll("[data-reveal]"));
  if ("IntersectionObserver" in window) {
    const revealObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add("is-revealed");
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.18, rootMargin: "0px 0px -40px 0px" });

    revealNodes.forEach((node) => revealObserver.observe(node));
  } else {
    revealNodes.forEach((node) => node.classList.add("is-revealed"));
  }

  syncThemeByMetric();
  applyFilter("all");
})();
</script>
<script type="application/ld+json">
<?php echo wp_json_encode($schema_item_list, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
</script>
<?php
get_footer();
