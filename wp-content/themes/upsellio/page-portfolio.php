<?php
/*
Template Name: Upsellio - Portfolio
Template Post Type: page
*/
if (!defined("ABSPATH")) {
    exit;
}

if (function_exists("upsellio_register_template_seo_head")) {
    upsellio_register_template_seo_head("portfolio");
}

get_header();

$portfolio_items = function_exists("upsellio_get_portfolio_list") ? upsellio_get_portfolio_list(90) : [];
$featured = null;
$categories = [];

foreach ($portfolio_items as $item) {
    if ($featured === null && !empty($item["is_featured"])) {
        $featured = $item;
    }
    $category_slug = (string) ($item["category_slug"] ?? "");
    $category_name = (string) ($item["category"] ?? "");
    if ($category_slug !== "" && $category_name !== "") {
        $categories[$category_slug] = $category_name;
    }
}

if ($featured === null && !empty($portfolio_items)) {
    $featured = $portfolio_items[0];
}

$schema_items = [];
foreach ($portfolio_items as $index => $item) {
    $schema_items[] = [
        "@type" => "ListItem",
        "position" => $index + 1,
        "url" => (string) ($item["url"] ?? ""),
        "name" => (string) ($item["title"] ?? ""),
    ];
}
?>
<style>
  .pr-art{font-family:"DM Sans",system-ui,sans-serif;color:#0a1410;background:#fafaf7;line-height:1.65}
  .pr-art *,.pr-art *::before,.pr-art *::after{box-sizing:border-box}
  .pr-wrap{width:min(1180px,100% - 64px);margin-inline:auto}
  .pr-eyebrow{display:inline-flex;align-items:center;gap:10px;font-size:11px;font-weight:700;letter-spacing:1.6px;text-transform:uppercase;color:#0d9488;margin-bottom:14px}
  .pr-eyebrow::before{content:"";width:26px;height:2px;background:#0d9488;border-radius:99px}
  .pr-eyebrow-light{color:#5eead4}.pr-eyebrow-light::before{background:#5eead4}
  .pr-h1{font-family:"Syne",sans-serif;font-weight:700;font-size:clamp(40px,4.6vw,64px);line-height:1.02;letter-spacing:-2px;margin:0 0 20px;max-width:18ch}
  .pr-h2{font-family:"Syne",sans-serif;font-weight:700;font-size:clamp(28px,3.2vw,42px);line-height:1.05;letter-spacing:-1.4px;margin:0;max-width:22ch}
  .pr-h2-light{color:#fff}.pr-h3{font-family:"Syne",sans-serif;font-weight:700;font-size:21px;line-height:1.18;letter-spacing:-.5px;margin:0 0 10px}
  .pr-lead{font-size:18px;line-height:1.6;color:#3d3d38;max-width:60ch;margin:0 0 32px}.pr-section{padding:96px 0 128px}
  .pr-hero{padding:96px 0 56px;background:radial-gradient(circle at 90% 0%,rgba(13,148,136,.1),transparent 40%)}
  .pr-filters{display:flex;flex-wrap:wrap;gap:8px}
  .pr-filters button{padding:10px 18px;border-radius:999px;font-size:13px;font-weight:600;color:#3d3d38;border:1px solid #e7e7e1;background:#fff;cursor:pointer}
  .pr-filters button:hover{color:#0d9488;border-color:#99f6e4}.pr-filters button.is-active{background:#0a1410;color:#fff;border-color:#0a1410}
  .pr-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
  .pr-card{background:#fff;border:1px solid #e7e7e1;border-radius:20px;overflow:hidden;display:flex;flex-direction:column;transition:.25s ease}
  .pr-card:hover{transform:translateY(-3px);border-color:#99f6e4;box-shadow:0 18px 40px rgba(15,23,42,.06)}
  .pr-card.pr-hidden{display:none}
  .pr-thumb{position:relative;aspect-ratio:1.4;background:#dff8f4;border-bottom:1px solid #e7e7e1;overflow:hidden}
  .pr-thumb img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
  .pr-thumb-stripes{position:absolute;inset:0;background-image:repeating-linear-gradient(135deg,rgba(13,148,136,.12) 0 12px,transparent 12px 24px)}
  .pr-thumb-label{position:absolute;inset:0;display:grid;place-items:center;font-family:ui-monospace,monospace;color:#0f766e;font-size:12px;letter-spacing:.5px;padding:0 14px;text-align:center}
  .pr-card-body{padding:24px;display:flex;flex-direction:column;flex:1}
  .pr-card-tag{font-size:11px;letter-spacing:1.4px;text-transform:uppercase;color:#7c7c74;font-weight:700;margin-bottom:12px}
  .pr-card p{margin:0 0 18px;color:#3d3d38;font-size:14.5px;line-height:1.6;flex:1}
  .pr-card-foot{display:flex;justify-content:space-between;align-items:center;padding-top:16px;border-top:1px solid #e7e7e1}
  .pr-card-foot strong{font-family:"Syne",sans-serif;font-size:14px;color:#0d9488;letter-spacing:-.2px}
  .pr-card-foot a{color:#0a1410;font-weight:700;font-size:13px;text-decoration:none}.pr-card-foot a:hover{color:#0d9488}
  .pr-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border-radius:999px;padding:15px 24px;font-weight:700;font-size:15px;text-decoration:none;border:1px solid transparent}
  .pr-btn-primary{background:#0d9488;color:#fff}.pr-cta{background:#0a1410;color:#fff;padding:80px 0;position:relative;overflow:hidden}
  .pr-cta::before{content:"";position:absolute;width:600px;height:600px;border-radius:50%;background:radial-gradient(circle,rgba(20,184,166,.2),transparent 65%);right:-200px;top:-300px;pointer-events:none}
  .pr-cta-inner{position:relative;display:flex;justify-content:space-between;align-items:center;gap:32px;flex-wrap:wrap}
  @media(max-width:960px){.pr-grid{grid-template-columns:1fr 1fr}}
  @media(max-width:700px){.pr-wrap{width:min(1180px,100% - 32px)}.pr-grid{grid-template-columns:1fr}}
</style>

<main class="pr-art">
  <section class="pr-hero">
    <div class="pr-wrap">
      <div class="pr-eyebrow">Portfolio · strony WWW</div>
      <h1 class="pr-h1">Strony, które działają jako narzędzia sprzedaży.</h1>
      <p class="pr-lead">Wybór realizacji stron firmowych i landing page'y. Każdy projekt ma jasny cel: generować zapytania, pokazać kompetencje i zbudować zaufanie B2B.</p>
      <div class="pr-filters" id="pr-filters">
        <button class="is-active" type="button" data-cat="all">Wszystkie</button>
        <?php foreach ($categories as $slug => $name) : ?>
          <button type="button" data-cat="<?php echo esc_attr((string) $slug); ?>"><?php echo esc_html((string) $name); ?></button>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="pr-section">
    <div class="pr-wrap">
      <?php if (!empty($portfolio_items)) : ?>
        <div class="pr-grid" id="pr-grid">
          <?php foreach ($portfolio_items as $item) : ?>
            <?php
            $thumb = !empty($item["thumbnail"]) ? (string) $item["thumbnail"] : "";
            $metric = !empty($item["meta"]) ? (string) $item["meta"] : (string) ($item["type"] ?? "Case study");
            $tag = trim((string) (($item["type"] ?? "Strona firmowa") . " · " . ($item["category"] ?? "Projekt")));
            ?>
            <article class="pr-card" data-cat="<?php echo esc_attr((string) ($item["category_slug"] ?? "")); ?>">
              <a class="pr-thumb" href="<?php echo esc_url((string) ($item["url"] ?? "#")); ?>" aria-hidden="true" tabindex="-1">
                <?php if ($thumb !== "") : ?>
                  <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr((string) ($item["title"] ?? "")); ?>" loading="lazy" decoding="async" />
                <?php else : ?>
                  <div class="pr-thumb-stripes"></div>
                  <div class="pr-thumb-label">[ screenshot — <?php echo esc_html((string) ($item["title"] ?? "Projekt")); ?> ]</div>
                <?php endif; ?>
              </a>
              <div class="pr-card-body">
                <div class="pr-card-tag"><?php echo esc_html($tag); ?></div>
                <h3 class="pr-h3"><?php echo esc_html((string) ($item["title"] ?? "")); ?></h3>
                <p><?php echo esc_html((string) ($item["excerpt"] ?? "")); ?></p>
                <div class="pr-card-foot">
                  <strong><?php echo esc_html($metric); ?></strong>
                  <a href="<?php echo esc_url((string) ($item["url"] ?? "#")); ?>">Zobacz case →</a>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else : ?>
        <p>Brak projektów do wyświetlenia.</p>
      <?php endif; ?>
    </div>
  </section>

  <section class="pr-cta">
    <div class="pr-wrap pr-cta-inner">
      <div><div class="pr-eyebrow pr-eyebrow-light">Następny projekt</div><h2 class="pr-h2 pr-h2-light">Twoja strona też może realnie sprzedawać.</h2></div>
      <a class="pr-btn pr-btn-primary" href="<?php echo esc_url(home_url("/#kontakt")); ?>">Porozmawiajmy o stronie →</a>
    </div>
  </section>
</main>

<?php if (!empty($schema_items)) : ?>
<script type="application/ld+json">
<?php
echo wp_json_encode([
    "@context" => "https://schema.org",
    "@type" => "ItemList",
    "name" => "Portfolio Upsellio",
    "description" => "Katalog realizacji stron internetowych, sklepów i aplikacji webowych tworzonych z myślą o sprzedaży i wygodzie użytkownika.",
    "itemListElement" => $schema_items,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
?>
</script>
<?php endif; ?>

<script>
  (function () {
    const grid = document.getElementById("pr-grid");
    const filters = Array.from(document.querySelectorAll("#pr-filters button"));
    if (!grid || !filters.length) return;
    const cards = Array.from(grid.querySelectorAll(".pr-card"));
    filters.forEach((button) => {
      button.addEventListener("click", function () {
        const category = this.getAttribute("data-cat") || "all";
        filters.forEach((item) => item.classList.remove("is-active"));
        this.classList.add("is-active");
        cards.forEach((card) => {
          const cardCategory = card.getAttribute("data-cat") || "";
          card.classList.toggle("pr-hidden", !(category === "all" || cardCategory === category));
        });
      });
    });
  })();
</script>
<?php
get_footer();
