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
$per_page = 9;
$paged = max(1, (int) get_query_var("paged"), (int) get_query_var("page"), isset($_GET["paged"]) ? (int) $_GET["paged"] : 1);
$total_items = count($items);
$summary_projects = $total_items;
$max_pages = max(1, (int) ceil($total_items / $per_page));
if ($paged > $max_pages) {
    $paged = $max_pages;
}
$offset = ($paged - 1) * $per_page;
$items_page = array_slice($items, $offset, $per_page);
$categories = [];
foreach ($items as $item) {
    $category_slug = (string) ($item["category_slug"] ?? "");
    $category_name = (string) ($item["category"] ?? "");
    if ($category_slug !== "" && $category_name !== "") {
        $categories[$category_slug] = $category_name;
    }
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
  .mp-h2{font-family:"Syne",sans-serif;font-weight:700;font-size:clamp(28px,3.2vw,42px);line-height:1.05;letter-spacing:-1.4px;margin:0;max-width:22ch}
  .mp-h2-light{color:#fff}.mp-h3{font-family:"Syne",sans-serif;font-weight:700;font-size:21px;line-height:1.18;letter-spacing:-.5px;margin:0 0 10px}
  .mp-lead{font-size:18px;line-height:1.6;color:#3d3d38;max-width:60ch;margin:0 0 32px}.mp-section{padding:96px 0 128px}
  .mp-hero{padding:96px 0 56px;background:radial-gradient(circle at 90% 0%,rgba(13,148,136,.1),transparent 40%)}
  .mp-filters{display:flex;flex-wrap:wrap;gap:8px}
  .mp-filters button{padding:10px 18px;border-radius:999px;font-size:13px;font-weight:600;color:#3d3d38;border:1px solid #e7e7e1;background:#fff;cursor:pointer}
  .mp-filters button:hover{color:#0d9488;border-color:#99f6e4}.mp-filters button.is-active{background:#0a1410;color:#fff;border-color:#0a1410}
  .mp-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
  .mp-card{background:#fff;border:1px solid #e7e7e1;border-radius:20px;overflow:hidden;display:flex;flex-direction:column;transition:.25s ease}
  .mp-card:hover{transform:translateY(-3px);border-color:#99f6e4;box-shadow:0 18px 40px rgba(15,23,42,.06)}
  .mp-card.mp-hidden{display:none}
  .mp-thumb{position:relative;aspect-ratio:1.4;background:#dff8f4;border-bottom:1px solid #e7e7e1;overflow:hidden}
  .mp-thumb img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
  .mp-thumb-stripes{position:absolute;inset:0;background-image:repeating-linear-gradient(135deg,rgba(13,148,136,.12) 0 12px,transparent 12px 24px)}
  .mp-thumb-label{position:absolute;inset:0;display:grid;place-items:center;font-family:ui-monospace,monospace;color:#0f766e;font-size:12px;letter-spacing:.5px;padding:0 14px;text-align:center}
  .mp-card-body{padding:24px;display:flex;flex-direction:column;flex:1}
  .mp-card-tag{font-size:11px;letter-spacing:1.4px;text-transform:uppercase;color:#7c7c74;font-weight:700;margin-bottom:12px}
  .mp-card p{margin:0 0 18px;color:#3d3d38;font-size:14.5px;line-height:1.6;flex:1}
  .mp-card-foot{display:flex;justify-content:space-between;align-items:center;padding-top:16px;border-top:1px solid #e7e7e1}
  .mp-card-foot strong{font-family:"Syne",sans-serif;font-size:14px;color:#0d9488;letter-spacing:-.2px}
  .mp-card-foot a{color:#0a1410;font-weight:700;font-size:13px;text-decoration:none}.mp-card-foot a:hover{color:#0d9488}
  .mp-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border-radius:999px;padding:15px 24px;font-weight:700;font-size:15px;text-decoration:none;border:1px solid transparent}
  .mp-btn-primary{background:#0d9488;color:#fff}.mp-cta{background:#0a1410;color:#fff;padding:80px 0;position:relative;overflow:hidden}
  .mp-cta::before{content:"";position:absolute;width:600px;height:600px;border-radius:50%;background:radial-gradient(circle,rgba(20,184,166,.2),transparent 65%);right:-200px;top:-300px;pointer-events:none}
  .mp-cta-inner{position:relative;display:flex;justify-content:space-between;align-items:center;gap:32px;flex-wrap:wrap}
  .mp-pager{display:flex;justify-content:center;align-items:center;gap:8px;flex-wrap:wrap;margin-top:34px}
  .mp-pager a,.mp-pager span{min-width:38px;height:38px;padding:0 12px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;border:1px solid #e7e7e1;background:#fff;font-size:13px;color:#0a1410;text-decoration:none;font-weight:700}
  .mp-pager .is-current{background:#0a1410;color:#fff;border-color:#0a1410}
  .mp-pager .is-disabled{opacity:.45;pointer-events:none}
  @media(max-width:960px){.mp-grid{grid-template-columns:1fr 1fr}}
  @media(max-width:700px){.mp-wrap{width:min(1180px,100% - 32px)}.mp-grid{grid-template-columns:1fr}}
</style>

<main class="mp-art">
  <section class="mp-hero">
    <div class="mp-wrap">
      <div class="mp-eyebrow">Portfolio · marketing</div>
      <h1 class="mp-h1">Kampanie, które dowożą wynik i jakość leadów.</h1>
      <p class="mp-lead">Wybór realizacji Google Ads, Meta Ads i lejków sprzedażowych. Każdy case pokazuje konkretny punkt wyjścia, wdrożenie i efekt.</p>
      <div class="mp-filters" id="mp-filters">
        <button class="is-active" type="button" data-cat="all">Wszystkie</button>
        <?php foreach ($categories as $slug => $name) : ?>
          <button type="button" data-cat="<?php echo esc_attr((string) $slug); ?>"><?php echo esc_html((string) $name); ?></button>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="mp-section">
    <div class="mp-wrap">
      <?php if (!empty($items_page)) : ?>
        <div class="mp-grid" id="mp-grid">
          <?php foreach ($items_page as $item) : ?>
            <?php
            $post_id = (int) ($item["id"] ?? 0);
            $thumb = (string) ($item["image"] ?? "");
            if ($thumb === "" && function_exists("upsellio_resolve_post_image_url")) {
                $thumb = upsellio_resolve_post_image_url($post_id, "_ups_mport_image", "medium_large");
            }
            if ($thumb === "" && $post_id > 0 && has_post_thumbnail($post_id)) {
                $thumb = (string) get_the_post_thumbnail_url($post_id, "medium_large");
            }
            $kpis = (array) ($item["kpis"] ?? []);
            $first_kpi = (array) ($kpis[0] ?? []);
            $metric = (string) (($first_kpi["change"] ?? "") !== "" ? ($first_kpi["change"] ?? "") : ($first_kpi["after"] ?? ""));
            if ($metric === "") {
                $metric = (string) ($item["meta"] ?? "Case study");
            }
            $tag = trim((string) (($item["type"] ?? "Kampania") . " · " . ($item["category"] ?? "Marketing")));
            ?>
            <article class="mp-card" data-cat="<?php echo esc_attr((string) ($item["category_slug"] ?? "")); ?>">
              <a class="mp-thumb" href="<?php echo esc_url((string) ($item["url"] ?? "#")); ?>" aria-hidden="true" tabindex="-1">
                <?php if ($thumb !== "") : ?>
                  <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr((string) ($item["title"] ?? "")); ?>" loading="lazy" decoding="async" />
                <?php else : ?>
                  <div class="mp-thumb-stripes"></div>
                  <div class="mp-thumb-label">[ screenshot — <?php echo esc_html((string) ($item["title"] ?? "Case study")); ?> ]</div>
                <?php endif; ?>
              </a>
              <div class="mp-card-body">
                <div class="mp-card-tag"><?php echo esc_html($tag); ?></div>
                <h3 class="mp-h3"><?php echo esc_html((string) ($item["title"] ?? "")); ?></h3>
                <p><?php echo esc_html((string) ($item["excerpt"] ?? "")); ?></p>
                <div class="mp-card-foot">
                  <strong><?php echo esc_html($metric); ?></strong>
                  <a href="<?php echo esc_url((string) ($item["url"] ?? "#")); ?>">Zobacz case →</a>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
        <?php if ($max_pages > 1) : ?>
          <nav class="mp-pager" aria-label="Paginacja portfolio marketingowego">
            <?php
            $base_url = function_exists("upsellio_get_marketing_portfolio_page_url") ? (string) upsellio_get_marketing_portfolio_page_url() : get_permalink();
            $prev_url = $paged > 2 ? add_query_arg("paged", $paged - 1, $base_url) : $base_url;
            $next_url = add_query_arg("paged", $paged + 1, $base_url);
            ?>
            <?php if ($paged > 1) : ?>
              <a href="<?php echo esc_url($prev_url); ?>" aria-label="Poprzednia strona">‹</a>
            <?php else : ?>
              <span class="is-disabled" aria-hidden="true">‹</span>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $max_pages; $i++) : ?>
              <?php $page_url = $i > 1 ? add_query_arg("paged", $i, $base_url) : $base_url; ?>
              <?php if ($i === $paged) : ?>
                <span class="is-current"><?php echo esc_html((string) $i); ?></span>
              <?php else : ?>
                <a href="<?php echo esc_url($page_url); ?>"><?php echo esc_html((string) $i); ?></a>
              <?php endif; ?>
            <?php endfor; ?>

            <?php if ($paged < $max_pages) : ?>
              <a href="<?php echo esc_url($next_url); ?>" aria-label="Następna strona">›</a>
            <?php else : ?>
              <span class="is-disabled" aria-hidden="true">›</span>
            <?php endif; ?>
          </nav>
        <?php endif; ?>
      <?php else : ?>
        <p>Brak projektów do wyświetlenia.</p>
      <?php endif; ?>
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
  const grid = document.getElementById("mp-grid");
  const filters = Array.from(document.querySelectorAll("#mp-filters button"));
  if (!grid || !filters.length) return;
  const cards = Array.from(grid.querySelectorAll(".mp-card"));
  filters.forEach((button) => {
    button.addEventListener("click", function () {
      const category = this.getAttribute("data-cat") || "all";
      filters.forEach((item) => item.classList.remove("is-active"));
      this.classList.add("is-active");
      cards.forEach((card) => {
        const cardCategory = card.getAttribute("data-cat") || "";
        card.classList.toggle("mp-hidden", !(category === "all" || cardCategory === category));
      });
    });
  });
})();
</script>
<script type="application/ld+json">
<?php echo wp_json_encode($schema_item_list, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
</script>
<?php
get_footer();
