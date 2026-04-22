<?php
/*
Template Name: Upsellio - Lead Magnety
Template Post Type: page
*/
if (!defined("ABSPATH")) {
    exit;
}

if (function_exists("upsellio_register_template_seo_head")) {
    upsellio_register_template_seo_head("lead_magnety");
}

get_header();

$lead_magnets = function_exists("upsellio_get_lead_magnet_list") ? upsellio_get_lead_magnet_list(60) : [];
$featured = null;
$categories = [];

foreach ($lead_magnets as $item) {
    if ($featured === null && !empty($item["is_featured"])) {
        $featured = $item;
    }

    $category_slug = (string) ($item["category_slug"] ?? "");
    $category_name = (string) ($item["category"] ?? "");
    if ($category_slug !== "" && $category_name !== "") {
        $categories[$category_slug] = $category_name;
    }
}

if ($featured === null && !empty($lead_magnets)) {
    $featured = $lead_magnets[0];
}

$schema_items = [];
foreach ($lead_magnets as $index => $item) {
    $schema_items[] = [
        "@type" => "ListItem",
        "position" => $index + 1,
        "url" => (string) ($item["url"] ?? ""),
        "name" => (string) ($item["title"] ?? ""),
    ];
}
?>
<style>
  .lm-page { background:#f6f7f5; color:#0f1110; }
  .lm-wrap { width:min(1180px, calc(100% - 32px)); margin:0 auto; }
  .lm-hero { border-bottom:1px solid #e6e6e1; background:linear-gradient(180deg, rgba(29,158,117,0.08), rgba(255,255,255,0)); }
  .lm-hero-inner { padding:64px 0 52px; }
  .lm-pill { display:inline-flex; align-items:center; gap:10px; border:1px solid #e6e6e1; background:#fff; color:#5f635f; font-size:12px; font-weight:600; border-radius:999px; padding:9px 14px; }
  .lm-pill-dot { width:8px; height:8px; border-radius:50%; background:#1d9e75; }
  .lm-h1 { margin:18px 0 16px; max-width:920px; font-family:"Syne",sans-serif; font-size:clamp(36px, 6vw, 64px); line-height:0.97; letter-spacing:-0.05em; }
  .lm-accent { color:#1d9e75; }
  .lm-lead { margin:0; max-width:860px; font-size:19px; line-height:1.72; color:#525652; }
  .lm-search { margin-top:30px; display:grid; gap:12px; grid-template-columns:1fr; }
  .lm-search-input { border:1px solid #e6e6e1; background:#fff; border-radius:16px; padding:13px 16px; width:100%; font-size:15px; outline:none; }
  .lm-search-input:focus { border-color:#1d9e75; box-shadow:0 0 0 3px rgba(29,158,117,.14); }
  .lm-category-bar { background:#fff; border-bottom:1px solid #e6e6e1; }
  .lm-categories { padding:18px 0; display:flex; flex-wrap:wrap; gap:10px; }
  .lm-cat-btn { border:1px solid #e6e6e1; background:#fff; color:#575c57; border-radius:999px; padding:8px 14px; font-size:13px; font-weight:600; cursor:pointer; }
  .lm-cat-btn.is-active, .lm-cat-btn:hover { border-color:#1d9e75; background:#e8f8f2; color:#085041; }
  .lm-featured { padding:46px 0; border-bottom:1px solid #e6e6e1; }
  .lm-featured-card { overflow:hidden; border:1px solid #e6e6e1; border-radius:28px; background:#fff; display:grid; grid-template-columns:1fr; }
  .lm-featured-visual { min-height:260px; position:relative; background:#dce3df; }
  .lm-featured-visual img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
  .lm-featured-body { padding:28px; }
  .lm-badge { display:inline-flex; border-radius:999px; border:1px solid #d6ebe3; background:#e8f8f2; color:#085041; font-size:12px; font-weight:700; padding:5px 11px; }
  .lm-featured-title { margin:14px 0 12px; font-family:"Syne",sans-serif; font-size:clamp(30px, 3.6vw, 44px); line-height:1.02; letter-spacing:-0.04em; }
  .lm-featured-excerpt { margin:0; color:#595d59; line-height:1.78; }
  .lm-btn { margin-top:18px; display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:44px; border-radius:12px; background:#1d9e75; color:#fff; font-size:14px; font-weight:700; padding:10px 18px; }
  .lm-grid-section { background:#fff; }
  .lm-grid-head { padding:42px 0 14px; display:flex; justify-content:space-between; align-items:end; gap:16px; flex-wrap:wrap; }
  .lm-eyebrow { font-size:11px; letter-spacing:.18em; text-transform:uppercase; font-weight:700; color:#6f746f; }
  .lm-h2 { margin:10px 0 0; font-family:"Syne",sans-serif; font-size:clamp(31px, 4vw, 44px); line-height:1.06; letter-spacing:-0.04em; }
  .lm-grid { display:grid; gap:14px; padding:16px 0 54px; grid-template-columns:1fr; }
  .lm-card { border:1px solid #e6e6e1; border-radius:24px; padding:22px; background:#fff; display:flex; flex-direction:column; min-height:100%; transition:.2s ease; }
  .lm-card:hover { border-color:#1d9e75; transform:translateY(-2px); box-shadow:0 12px 25px rgba(12,23,18,.08); }
  .lm-card-top { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; }
  .lm-card-category { font-size:11px; letter-spacing:.14em; text-transform:uppercase; color:#6a6f6a; font-weight:700; }
  .lm-card-type { font-size:12px; color:#596059; margin-top:5px; }
  .lm-card-title { margin:14px 0 8px; font-family:"Syne",sans-serif; font-size:26px; line-height:1.06; letter-spacing:-.04em; }
  .lm-card-excerpt { margin:0; color:#5a5f5a; line-height:1.74; font-size:15px; }
  .lm-card-meta { margin-top:10px; font-size:13px; color:#737973; }
  .lm-card-link { margin-top:auto; padding-top:18px; color:#1d9e75; font-size:14px; font-weight:700; }
  .lm-empty { padding:18px; border:1px dashed #d8d8d3; border-radius:14px; color:#676d67; }
  .lm-extra { border-top:1px solid #e6e6e1; background:#f8f8f6; padding:48px 0; }
  .lm-extra-grid { display:grid; grid-template-columns:1fr; gap:16px; }
  .lm-extra-card { border:1px solid #e6e6e1; border-radius:22px; background:#fff; padding:22px; }
  .lm-extra-card h3 { margin:8px 0 10px; font-family:"Syne",sans-serif; font-size:28px; line-height:1.06; letter-spacing:-.03em; }
  .lm-extra-card p { margin:0; color:#5f635f; line-height:1.75; }
  .lm-extra-list { margin:14px 0 0; padding-left:17px; color:#535953; line-height:1.8; }
  .lm-hidden { display:none !important; }
  @media (min-width:760px){ .lm-wrap{width:min(1180px,calc(100% - 48px));} .lm-search{grid-template-columns:1fr 220px;} }
  @media (min-width:982px){
    .lm-featured-card{grid-template-columns:1.02fr .98fr;}
    .lm-grid{grid-template-columns:repeat(3,minmax(0,1fr));}
    .lm-extra-grid{grid-template-columns:1.05fr .95fr;}
  }
</style>

<main class="lm-page">
  <section class="lm-hero">
    <div class="lm-wrap lm-hero-inner">
      <div class="lm-pill"><span class="lm-pill-dot"></span>Materiały do pobrania dla firm B2B: checklisty, audyty, szablony i raporty</div>
      <h1 class="lm-h1">Biblioteka materiałów Upsellio.<br /><span class="lm-accent">Praktyczne materiały marketingowe i sprzedażowe, które pomagają podjąć lepszą decyzję.</span></h1>
      <p class="lm-lead">Znajdziesz tu konkretne materiały: checklisty Meta Ads, mini audyty, szablony landing page i raporty, które pomagają zwiększyć liczbę zapytań i poprawić jakość leadów bez zgadywania.</p>
      <div class="lm-search">
        <input id="lm-search" class="lm-search-input" type="search" placeholder="Szukaj materiału po nazwie lub opisie..." aria-label="Szukaj materiału" />
        <a href="<?php echo esc_url(home_url("/#kontakt")); ?>" class="lm-btn">Umów rozmowę</a>
      </div>
    </div>
  </section>

  <section class="lm-category-bar">
    <div class="lm-wrap">
      <div class="lm-categories" id="lm-categories">
        <button type="button" class="lm-cat-btn is-active" data-cat="all">Wszystkie</button>
        <?php foreach ($categories as $slug => $name) : ?>
          <button type="button" class="lm-cat-btn" data-cat="<?php echo esc_attr($slug); ?>"><?php echo esc_html($name); ?></button>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <?php if (!empty($featured)) : ?>
    <section class="lm-featured">
      <div class="lm-wrap">
        <article class="lm-featured-card">
          <div class="lm-featured-visual">
            <?php if (!empty($featured["image"])) : ?>
              <img src="<?php echo esc_url((string) $featured["image"]); ?>" alt="<?php echo esc_attr((string) $featured["title"]); ?>" loading="lazy" />
            <?php endif; ?>
          </div>
          <div class="lm-featured-body">
            <?php if (!empty($featured["badge"])) : ?><span class="lm-badge"><?php echo esc_html((string) $featured["badge"]); ?></span><?php endif; ?>
            <h2 class="lm-featured-title"><?php echo esc_html((string) $featured["title"]); ?></h2>
            <p class="lm-featured-excerpt"><?php echo esc_html((string) $featured["excerpt"]); ?></p>
            <a class="lm-btn" href="<?php echo esc_url((string) $featured["url"]); ?>"><?php echo esc_html(!empty($featured["cta"]) ? (string) $featured["cta"] : "Zobacz materiał"); ?></a>
          </div>
        </article>
      </div>
    </section>
  <?php endif; ?>

  <section class="lm-grid-section">
    <div class="lm-wrap">
      <div class="lm-grid-head">
        <div>
          <div class="lm-eyebrow">Katalog materiałów</div>
          <h2 class="lm-h2">Wszystkie materiały</h2>
        </div>
      </div>
      <?php if (!empty($lead_magnets)) : ?>
        <div class="lm-grid" id="lm-grid">
          <?php foreach ($lead_magnets as $item) : ?>
            <?php
            $search_text = (string) $item["title"] . " " . (string) $item["excerpt"];
            $search_text = function_exists("mb_strtolower") ? mb_strtolower($search_text) : strtolower($search_text);
            ?>
            <article class="lm-card" data-cat="<?php echo esc_attr((string) $item["category_slug"]); ?>" data-search="<?php echo esc_attr($search_text); ?>">
              <div class="lm-card-top">
                <div>
                  <div class="lm-card-category"><?php echo esc_html((string) $item["category"]); ?></div>
                  <div class="lm-card-type"><?php echo esc_html(!empty($item["type"]) ? (string) $item["type"] : "Materiał do pobrania"); ?></div>
                </div>
              </div>
              <h3 class="lm-card-title"><?php echo esc_html((string) $item["title"]); ?></h3>
              <p class="lm-card-excerpt"><?php echo esc_html((string) $item["excerpt"]); ?></p>
              <?php if (!empty($item["meta"])) : ?><div class="lm-card-meta"><?php echo esc_html((string) $item["meta"]); ?></div><?php endif; ?>
              <a class="lm-card-link" href="<?php echo esc_url((string) $item["url"]); ?>">Przejdź do materiału →</a>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else : ?>
        <div class="lm-empty">Nie masz jeszcze dodanych materiałów. Wejdź do panelu admina: <strong>Materiały → Dodaj nowy</strong>.</div>
      <?php endif; ?>
    </div>
  </section>

  <section class="lm-extra">
    <div class="lm-wrap lm-extra-grid">
      <article class="lm-extra-card">
        <div class="lm-eyebrow">Dlaczego ta sekcja wspiera SEO i sprzedaż</div>
        <h3>Treści, które odpowiadają na realne zapytania klientów.</h3>
        <p>Każdy materiał to osobna podstrona z unikalnym tytułem, opisem i treścią. Dzięki temu budujesz topical authority wokół fraz takich jak „checklista Meta Ads”, „audyt kampanii reklamowych”, „szablon landing page B2B” czy „jak poprawić jakość leadów”.</p>
      </article>
      <article class="lm-extra-card">
        <div class="lm-eyebrow">Rekomendowany schemat podstrony</div>
        <ul class="lm-extra-list">
          <li>Hero z jasną obietnicą i korzyścią dla odbiorcy.</li>
          <li>Co zawiera materiał i dla kogo jest przeznaczony.</li>
          <li>Formularz pobrania lub CTA do rozmowy doradczej.</li>
          <li>Sekcja FAQ pod frazy long-tail i intencję informacyjną.</li>
          <li>Linkowanie wewnętrzne do usług i case studies.</li>
        </ul>
      </article>
    </div>
  </section>
</main>

<?php if (!empty($schema_items)) : ?>
<script type="application/ld+json">
<?php
echo wp_json_encode([
    "@context" => "https://schema.org",
    "@type" => "ItemList",
    "name" => "Biblioteka materiałów Upsellio",
    "description" => "Katalog materiałów do pobrania: checklisty, audyty i szablony marketingowe.",
    "itemListElement" => $schema_items,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
?>
</script>
<?php endif; ?>

<script>
  (function () {
    const grid = document.getElementById("lm-grid");
    const searchInput = document.getElementById("lm-search");
    const categoryButtons = Array.from(document.querySelectorAll(".lm-cat-btn"));
    if (!grid || !searchInput || !categoryButtons.length) return;

    let activeCategory = "all";

    function normalize(value) {
      return String(value || "").toLowerCase().trim();
    }

    function updateGrid() {
      const searchValue = normalize(searchInput.value);
      const cards = Array.from(grid.querySelectorAll(".lm-card"));

      cards.forEach((card) => {
        const cardCategory = card.getAttribute("data-cat") || "";
        const searchableText = card.getAttribute("data-search") || "";
        const isCategoryMatch = activeCategory === "all" || cardCategory === activeCategory;
        const isSearchMatch = searchValue === "" || searchableText.includes(searchValue);
        card.classList.toggle("lm-hidden", !(isCategoryMatch && isSearchMatch));
      });
    }

    categoryButtons.forEach((button) => {
      button.addEventListener("click", function () {
        activeCategory = this.getAttribute("data-cat") || "all";
        categoryButtons.forEach((item) => item.classList.remove("is-active"));
        this.classList.add("is-active");
        updateGrid();
      });
    });

    searchInput.addEventListener("input", updateGrid);
  })();
</script>
<?php
get_footer();
