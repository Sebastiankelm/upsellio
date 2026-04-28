<?php
/*
Template Name: Upsellio - Portfolio Marketingowe
Template Post Type: page
*/
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
  .mp-art{font-family:"DM Sans",system-ui,sans-serif;color:#0a1410;background:#fafaf7;line-height:1.65}
  .mp-art *,.mp-art *::before,.mp-art *::after{box-sizing:border-box}
  .mp-wrap{width:min(1180px,100% - 64px);margin-inline:auto}
  .mp-eyebrow{display:inline-flex;align-items:center;gap:10px;font-size:11px;font-weight:700;letter-spacing:1.6px;text-transform:uppercase;color:#0d9488;margin-bottom:14px}
  .mp-eyebrow::before{content:"";width:26px;height:2px;background:#0d9488;border-radius:99px}
  .mp-eyebrow-light{color:#5eead4}.mp-eyebrow-light::before{background:#5eead4}
  .mp-h1{font-family:"Syne",sans-serif;font-weight:700;font-size:clamp(40px,4.6vw,64px);line-height:1.02;letter-spacing:-2px;margin:0 0 20px;max-width:18ch}
  .mp-h2{font-family:"Syne",sans-serif;font-weight:700;font-size:clamp(28px,3.2vw,42px);line-height:1.05;letter-spacing:-1.4px;margin:0 0 14px;max-width:22ch}
  .mp-h2-light{color:#fff}.mp-h3{font-family:"Syne",sans-serif;font-weight:700;font-size:24px;line-height:1.15;letter-spacing:-.6px;margin:0 0 22px}
  .mp-lead{font-size:18px;line-height:1.6;color:#3d3d38;max-width:60ch;margin:0 0 36px}
  .mp-divider{height:1px;background:#e7e7e1;margin:36px 0 56px}.mp-section{padding:128px 0}
  .mp-hero{padding:96px 0;background:radial-gradient(circle at 90% 0%,rgba(13,148,136,.1),transparent 40%)}.mp-sec-head{max-width:780px}
  .mp-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-top:32px;padding:24px 0;border-top:1px solid #e7e7e1;border-bottom:1px solid #e7e7e1}
  .mp-stats>div{display:flex;flex-direction:column;gap:4px}.mp-stats b{font-family:"Syne",sans-serif;font-size:32px;color:#0d9488;letter-spacing:-1px;line-height:1;font-weight:700}.mp-stats span{font-size:12.5px;color:#7c7c74}
  .mp-cases{display:grid;gap:18px}.mp-case{background:#fff;border:1px solid #e7e7e1;border-radius:20px;padding:36px}
  .mp-case.mp-hidden{display:none}
  .mp-case-channel{display:inline-flex;font-size:11px;letter-spacing:1.4px;text-transform:uppercase;color:#0f766e;background:#ccfbf1;border:1px solid #99f6e4;padding:5px 12px;border-radius:999px;margin-bottom:18px;font-weight:700}
  .mp-case-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:32px;margin-top:8px}.mp-case-h{font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:#7c7c74;margin-bottom:8px}
  .mp-case-grid p{margin:0;font-size:14.5px;color:#3d3d38;line-height:1.6}.mp-case-grid ul{list-style:none;padding:0;margin:0;display:grid;gap:6px}.mp-case-grid ul li{font-family:"Syne",sans-serif;font-size:18px;color:#0d9488;font-weight:700;letter-spacing:-.4px}
  .mp-case-link{display:inline-flex;margin-top:22px;color:#0d9488;font-weight:700;font-size:14px;text-decoration:none}
  .mp-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border-radius:999px;padding:15px 24px;font-weight:700;font-size:15px;text-decoration:none}
  .mp-btn-primary{background:#0d9488;color:#fff}.mp-cta{background:#0a1410;color:#fff;padding:80px 0;position:relative;overflow:hidden}
  .mp-cta::before{content:"";position:absolute;width:600px;height:600px;border-radius:50%;background:radial-gradient(circle,rgba(20,184,166,.2),transparent 65%);right:-200px;top:-300px;pointer-events:none}
  .mp-cta-inner{position:relative;display:flex;justify-content:space-between;align-items:center;gap:32px;flex-wrap:wrap}
  @media(max-width:980px){.mp-stats{grid-template-columns:1fr 1fr}.mp-case-grid{grid-template-columns:1fr}}
  @media(max-width:700px){.mp-wrap{width:min(1180px,100% - 32px)}.mp-stats{grid-template-columns:1fr}}
</style>

<main class="mp-art">
  <section class="mp-hero">
    <div class="mp-wrap">
      <div class="mp-eyebrow">Portfolio · marketing</div>
      <h1 class="mp-h1">Wyniki kampanii i procesów sprzedażowych.</h1>
      <p class="mp-lead">Wybór realizacji marketingowych: Google Ads, Meta Ads, lejki sprzedażowe, e-commerce. Liczby pochodzą z realnych projektów — nie ze stocku.</p>
      <div class="mp-stats">
        <div><b>1 mln zł</b><span>miesięcznie · sprzedaż B2B</span></div>
        <div><b>500k zł</b><span>miesięcznie · e-commerce od zera</span></div>
        <div><b>−30%</b><span>średnio · spadek CPL po optymalizacji</span></div>
        <div><b>15 osób</b><span>w prowadzonym zespole sprzedaży</span></div>
      </div>
    </div>
  </section>

  <section class="mp-section">
    <div class="mp-wrap">
      <header class="mp-sec-head">
        <div class="mp-eyebrow">Case studies</div>
        <h2 class="mp-h2">Wybrane projekty marketingowe.</h2>
      </header>
      <div class="mp-divider"></div>
      <div class="mp-cases" id="mp-cases">
        <?php foreach ($items as $item) : ?>
          <?php
          $kpis = array_slice((array) ($item["kpis"] ?? []), 0, 3);
          $firstKpi = (array) ($kpis[0] ?? []);
          $secondKpi = (array) ($kpis[1] ?? []);
          ?>
          <article class="mp-case" data-cat="<?php echo esc_attr((string) ($item["category_slug"] ?? "")); ?>">
            <div class="mp-case-channel"><?php echo esc_html((string) ($item["category"] ?? "Marketing")); ?></div>
            <h3 class="mp-h3"><?php echo esc_html((string) ($item["title"] ?? "")); ?></h3>
            <div class="mp-case-grid">
              <div>
                <div class="mp-case-h">Problem</div>
                <p><?php echo esc_html((string) ($item["problem"] ?? "Wysoki koszt pozyskania i niska jakość leadów.")); ?></p>
              </div>
              <div>
                <div class="mp-case-h">Co zrobiłem</div>
                <p><?php echo esc_html((string) ($item["solution"] ?? "Restrukturyzacja kampanii, lejek i optymalizacja procesu.")); ?></p>
              </div>
              <div>
                <div class="mp-case-h">Wynik</div>
                <ul>
                  <?php if (!empty($kpis)) : ?>
                    <?php foreach ($kpis as $kpi) : ?>
                      <li><?php echo esc_html((string) (($kpi["change"] ?? "") !== "" ? $kpi["change"] : ($kpi["after"] ?? "-"))); ?></li>
                    <?php endforeach; ?>
                  <?php else : ?>
                    <li><?php echo esc_html((string) (($firstKpi["change"] ?? "") !== "" ? $firstKpi["change"] : "−30% CPL")); ?></li>
                    <li><?php echo esc_html((string) (($secondKpi["change"] ?? "") !== "" ? $secondKpi["change"] : "+40% konwersji")); ?></li>
                    <li><?php echo esc_html((string) ($item["result"] ?? "Stabilny wzrost leadów")); ?></li>
                  <?php endif; ?>
                </ul>
              </div>
            </div>
            <a class="mp-case-link" href="<?php echo esc_url((string) ($item["url"] ?? "#")); ?>">Zobacz pełny case →</a>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="mp-cta">
    <div class="mp-wrap mp-cta-inner">
      <div>
        <div class="mp-eyebrow mp-eyebrow-light">Kolejny case</div>
        <h2 class="mp-h2 mp-h2-light">Sprawdźmy, co da się poprawić u Ciebie.</h2>
      </div>
      <a class="mp-btn mp-btn-primary" href="<?php echo esc_url(home_url("/#kontakt")); ?>">Bezpłatna diagnoza →</a>
    </div>
  </section>
</main>

<script>
(() => {
  const cases = document.querySelectorAll(".mp-case");
  if (!cases.length) return;
  // Keep only first 3 cases to match 1:1 layout.
  cases.forEach((card, index) => {
    if (index > 2) card.classList.add("mp-hidden");
  });
})();
</script>
<script type="application/ld+json">
<?php echo wp_json_encode($schema_item_list, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
</script>
<?php
get_footer();
