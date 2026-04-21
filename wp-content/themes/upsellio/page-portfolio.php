<?php
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
  .port-page { background:#f6f7f5; color:#101413; }
  .port-wrap { width:min(1180px, calc(100% - 32px)); margin:0 auto; }
  .port-hero { border-bottom:1px solid #e8e8e3; background:linear-gradient(180deg, rgba(29,158,117,0.08), rgba(255,255,255,0) 58%); }
  .port-hero-inner { padding:64px 0 52px; }
  .port-pill { display:inline-flex; align-items:center; gap:10px; border:1px solid #e8e8e3; background:#fff; color:#646a65; font-size:12px; font-weight:600; border-radius:999px; padding:9px 14px; }
  .port-pill-dot { width:8px; height:8px; border-radius:50%; background:#1d9e75; }
  .port-h1 { margin:18px 0 16px; max-width:920px; font-family:"Syne",sans-serif; font-size:clamp(38px, 6vw, 66px); line-height:.96; letter-spacing:-0.06em; }
  .port-accent { color:#1d9e75; }
  .port-lead { margin:0; max-width:890px; font-size:19px; line-height:1.72; color:#5b615c; }
  .port-search { margin-top:30px; display:grid; gap:12px; grid-template-columns:1fr; }
  .port-search-input { width:100%; border:1px solid #e8e8e3; border-radius:16px; background:#fff; padding:13px 16px; font-size:15px; outline:none; }
  .port-search-input:focus { border-color:#1d9e75; box-shadow:0 0 0 3px rgba(29,158,117,.14); }
  .port-btn { display:inline-flex; align-items:center; justify-content:center; min-height:46px; border-radius:12px; background:#1d9e75; color:#fff; font-size:14px; font-weight:700; padding:10px 18px; }
  .port-btn:hover { background:#17885f; }
  .port-cats-wrap { background:#fff; border-bottom:1px solid #e8e8e3; }
  .port-cats { padding:18px 0; display:flex; flex-wrap:wrap; gap:10px; }
  .port-cat-btn { border:1px solid #e8e8e3; background:#fff; color:#5c615c; border-radius:999px; padding:8px 14px; font-size:13px; font-weight:600; cursor:pointer; }
  .port-cat-btn.is-active, .port-cat-btn:hover { border-color:#1d9e75; background:#e8f8f2; color:#085041; }
  .port-featured-section { border-bottom:1px solid #e8e8e3; background:#f8f8f6; }
  .port-featured-grid { display:grid; grid-template-columns:1fr; gap:18px; padding:46px 0; }
  .port-featured-card { overflow:hidden; border:1px solid #e8e8e3; border-radius:28px; background:#fff; display:grid; grid-template-columns:1fr; }
  .port-featured-visual { min-height:270px; position:relative; background:#d8dfdc; }
  .port-featured-visual img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
  .port-featured-overlay { position:absolute; inset:0; background:linear-gradient(180deg, rgba(0,0,0,.05), rgba(0,0,0,.32)); }
  .port-featured-badge { position:absolute; left:20px; top:20px; border:1px solid rgba(255,255,255,.28); border-radius:999px; background:rgba(255,255,255,.16); color:#fff; padding:6px 12px; font-size:12px; font-weight:700; backdrop-filter:blur(6px); }
  .port-featured-body { padding:28px; }
  .port-featured-category { display:inline-flex; border-radius:999px; border:1px solid #e8e8e3; background:#f8f8f6; color:#5e645f; font-size:11px; font-weight:700; padding:5px 11px; }
  .port-featured-title { margin:14px 0 12px; font-family:"Syne",sans-serif; font-size:clamp(30px, 3.5vw, 44px); line-height:1.01; letter-spacing:-.045em; }
  .port-featured-excerpt { margin:0; color:#595f5a; line-height:1.78; }
  .port-metrics { margin-top:16px; display:flex; flex-wrap:wrap; gap:8px; }
  .port-metric { border:1px solid #e8e8e3; background:#f8f8f6; color:#545b55; border-radius:999px; font-size:12px; padding:6px 10px; }
  .port-featured-actions { margin-top:18px; display:flex; flex-wrap:wrap; gap:10px; }
  .port-featured-link { display:inline-flex; align-items:center; justify-content:center; min-height:44px; border-radius:12px; font-size:14px; font-weight:700; padding:10px 16px; }
  .port-featured-link.primary { background:#1d9e75; color:#fff; }
  .port-featured-link.secondary { border:1px solid #e8e8e3; background:#fff; color:#525853; }
  .port-featured-link.primary:hover { background:#17885f; }
  .port-featured-link.secondary:hover { border-color:#1d9e75; color:#1d9e75; }
  .port-side { display:flex; flex-direction:column; gap:12px; }
  .port-side-card { border:1px solid #e8e8e3; border-radius:22px; background:#fff; padding:22px; }
  .port-side-eyebrow { font-size:11px; letter-spacing:.18em; text-transform:uppercase; font-weight:700; color:#707670; }
  .port-side-title { margin:10px 0 8px; font-family:"Syne",sans-serif; font-size:28px; line-height:1.03; letter-spacing:-.04em; }
  .port-side-copy { margin:0; color:#5f645f; line-height:1.76; }
  .port-side-list { margin:14px 0 0; padding:0; list-style:none; display:grid; gap:10px; }
  .port-side-list li { display:flex; gap:8px; color:#5b615b; line-height:1.68; }
  .port-side-list span { color:#1d9e75; font-weight:700; }
  .port-grid-section { background:#fff; }
  .port-grid-head { display:flex; justify-content:space-between; align-items:end; gap:16px; flex-wrap:wrap; padding:42px 0 14px; }
  .port-eyebrow { font-size:11px; letter-spacing:.18em; text-transform:uppercase; font-weight:700; color:#727872; }
  .port-h2 { margin:10px 0 0; font-family:"Syne",sans-serif; font-size:clamp(32px, 4vw, 46px); line-height:1.05; letter-spacing:-.045em; }
  .port-grid-meta { color:#7a817a; font-size:14px; }
  .port-grid { display:grid; gap:14px; padding:16px 0 54px; grid-template-columns:1fr; }
  .port-card { border:1px solid #e8e8e3; border-radius:24px; background:#fff; padding:22px; display:flex; flex-direction:column; transition:.2s ease; min-height:100%; }
  .port-card:hover { border-color:#1d9e75; transform:translateY(-2px); box-shadow:0 12px 25px rgba(12,23,18,.08); }
  .port-card-top { display:flex; justify-content:space-between; align-items:flex-start; gap:10px; }
  .port-card-category { font-size:11px; letter-spacing:.14em; text-transform:uppercase; color:#6d736d; font-weight:700; }
  .port-card-type { font-size:12px; color:#596059; margin-top:5px; }
  .port-card-title { margin:14px 0 8px; font-family:"Syne",sans-serif; font-size:26px; line-height:1.06; letter-spacing:-.04em; }
  .port-card-excerpt { margin:0; color:#5a605a; line-height:1.73; font-size:15px; }
  .port-card-meta { margin-top:10px; font-size:13px; color:#768076; }
  .port-card-link { margin-top:auto; padding-top:18px; color:#1d9e75; font-size:14px; font-weight:700; }
  .port-empty { padding:18px; border:1px dashed #d8d8d3; border-radius:14px; color:#676d67; }
  .port-hidden { display:none !important; }
  .port-seo { border-top:1px solid #e8e8e3; background:#f8f8f6; }
  .port-seo-grid { display:grid; grid-template-columns:1fr; gap:14px; padding:46px 0; }
  .port-seo-card { border:1px solid #e8e8e3; border-radius:22px; background:#fff; padding:22px; }
  .port-seo-card h3 { margin:8px 0 10px; font-family:"Syne",sans-serif; font-size:28px; line-height:1.06; letter-spacing:-.03em; }
  .port-seo-card p { margin:0; color:#5f645f; line-height:1.75; }
  .port-seo-list { margin:14px 0 0; padding-left:17px; color:#555c55; line-height:1.8; }
  @media (min-width:760px) {
    .port-wrap { width:min(1180px, calc(100% - 48px)); }
    .port-search { grid-template-columns:1fr 220px; }
  }
  @media (min-width:982px) {
    .port-featured-grid { grid-template-columns:1.25fr .75fr; }
    .port-featured-card { grid-template-columns:1.02fr .98fr; }
    .port-grid { grid-template-columns:repeat(3, minmax(0, 1fr)); }
    .port-seo-grid { grid-template-columns:1.04fr .96fr; }
  }
</style>

<main class="port-page">
  <section class="port-hero">
    <div class="port-wrap port-hero-inner">
      <div class="port-pill"><span class="port-pill-dot"></span>Portfolio stron, sklepów i aplikacji tworzonych pod wynik, proces i realne użycie</div>
      <h1 class="port-h1">Portfolio Upsellio.<br /><span class="port-accent">Strony i aplikacje, które mają działać — nie tylko wyglądać.</span></h1>
      <p class="port-lead">Zebrane realizacje stron internetowych, sklepów i aplikacji webowych tworzonych z myślą o sprzedaży, procesach i użyteczności. Każdy projekt prowadzi do własnej podstrony case study z celem, zakresem i efektem biznesowym oraz interaktywnym podglądem wdrożonej strony.</p>
      <div class="port-search">
        <input id="port-search" class="port-search-input" type="search" placeholder="Szukaj projektu po nazwie, kategorii albo opisie..." aria-label="Szukaj projektu portfolio" />
        <a href="<?php echo esc_url(home_url("/#kontakt")); ?>" class="port-btn">Bezpłatna rozmowa</a>
      </div>
    </div>
  </section>

  <section class="port-cats-wrap">
    <div class="port-wrap">
      <div class="port-cats" id="port-cats">
        <button type="button" class="port-cat-btn is-active" data-cat="all">Wszystkie</button>
        <?php foreach ($categories as $slug => $name) : ?>
          <button type="button" class="port-cat-btn" data-cat="<?php echo esc_attr($slug); ?>"><?php echo esc_html($name); ?></button>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <?php if (!empty($featured)) : ?>
    <section class="port-featured-section">
      <div class="port-wrap port-featured-grid">
        <article class="port-featured-card">
          <div class="port-featured-visual">
            <?php if (!empty($featured["image"])) : ?>
              <img src="<?php echo esc_url((string) $featured["image"]); ?>" alt="<?php echo esc_attr((string) $featured["title"]); ?>" loading="lazy" />
            <?php endif; ?>
            <div class="port-featured-overlay"></div>
            <?php if (!empty($featured["badge"])) : ?><div class="port-featured-badge"><?php echo esc_html((string) $featured["badge"]); ?></div><?php endif; ?>
          </div>
          <div class="port-featured-body">
            <div class="port-featured-category"><?php echo esc_html((string) $featured["category"]); ?></div>
            <h2 class="port-featured-title"><?php echo esc_html((string) $featured["title"]); ?></h2>
            <p class="port-featured-excerpt"><?php echo esc_html((string) $featured["excerpt"]); ?></p>
            <?php if (!empty($featured["metrics"])) : ?>
              <div class="port-metrics">
                <?php foreach ((array) $featured["metrics"] as $metric) : ?>
                  <span class="port-metric"><?php echo esc_html((string) $metric); ?></span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
            <div class="port-featured-actions">
              <a class="port-featured-link primary" href="<?php echo esc_url((string) $featured["url"]); ?>"><?php echo esc_html(!empty($featured["cta"]) ? (string) $featured["cta"] : "Wejdź do projektu"); ?></a>
              <a class="port-featured-link secondary" href="<?php echo esc_url(home_url("/#kontakt")); ?>">Chcę podobny projekt</a>
            </div>
          </div>
        </article>

        <aside class="port-side">
          <article class="port-side-card">
            <div class="port-side-eyebrow">Jak czytać to portfolio</div>
            <h3 class="port-side-title">Nie pokazuję tu tylko wyglądu. Pokazuję sens projektu.</h3>
            <p class="port-side-copy">Każdy case study opisuje kontekst biznesowy, problem klienta, zakres wdrożenia, zastosowane rozwiązania i finalny efekt. To podejście wspiera SEO, buduje zaufanie i skraca drogę do kontaktu.</p>
            <ul class="port-side-list">
              <li><span>✓</span>Realizacje pod lead generation i sprzedaż.</li>
              <li><span>✓</span>Podstrony zoptymalizowane pod frazy transakcyjne i informacyjne.</li>
              <li><span>✓</span>CTA osadzone kontekstowo na każdym etapie strony.</li>
            </ul>
          </article>

          <article class="port-side-card" style="background:#e8f8f2;border-color:#c3eddd;">
            <div class="port-side-eyebrow" style="color:#085041;">Micro CTA</div>
            <h3 class="port-side-title" style="color:#085041;">Chcesz podobny projekt dla swojej firmy?</h3>
            <p class="port-side-copy" style="color:#0f5e4e;">Podczas krótkiej rozmowy ustalimy, czy potrzebujesz strony pod leady, sklepu pod sprzedaż czy aplikacji pod procesy.</p>
            <a class="port-btn" style="margin-top:14px;" href="<?php echo esc_url(home_url("/#kontakt")); ?>">Umów rozmowę</a>
          </article>
        </aside>
      </div>
    </section>
  <?php endif; ?>

  <section class="port-grid-section">
    <div class="port-wrap">
      <div class="port-grid-head">
        <div>
          <div class="port-eyebrow">Katalog projektów</div>
          <h2 class="port-h2">Wszystkie realizacje</h2>
        </div>
        <div class="port-grid-meta"><?php echo esc_html((string) count($portfolio_items)); ?> projektów · każda realizacja prowadzi do własnej podstrony case study</div>
      </div>
      <?php if (!empty($portfolio_items)) : ?>
        <div class="port-grid" id="port-grid">
          <?php foreach ($portfolio_items as $item) : ?>
            <?php
            $search_text = implode(" ", [
                (string) ($item["title"] ?? ""),
                (string) ($item["excerpt"] ?? ""),
                (string) ($item["category"] ?? ""),
                (string) ($item["meta"] ?? ""),
                (string) ($item["type"] ?? ""),
            ]);
            $search_text = function_exists("mb_strtolower") ? mb_strtolower($search_text) : strtolower($search_text);
            ?>
            <article class="port-card" data-cat="<?php echo esc_attr((string) $item["category_slug"]); ?>" data-search="<?php echo esc_attr($search_text); ?>">
              <div class="port-card-top">
                <div>
                  <div class="port-card-category"><?php echo esc_html((string) $item["category"]); ?></div>
                  <div class="port-card-type"><?php echo esc_html(!empty($item["type"]) ? (string) $item["type"] : "Projekt webowy"); ?></div>
                </div>
              </div>
              <h3 class="port-card-title"><?php echo esc_html((string) $item["title"]); ?></h3>
              <p class="port-card-excerpt"><?php echo esc_html((string) $item["excerpt"]); ?></p>
              <?php if (!empty($item["meta"])) : ?><div class="port-card-meta"><?php echo esc_html((string) $item["meta"]); ?></div><?php endif; ?>
              <a class="port-card-link" href="<?php echo esc_url((string) $item["url"]); ?>">Wejdź na podstronę projektu →</a>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else : ?>
        <div class="port-empty">Nie masz jeszcze dodanych projektów. Wejdź do panelu admina: <strong>Portfolio → Dodaj projekt</strong>.</div>
      <?php endif; ?>
    </div>
  </section>

  <section class="port-seo">
    <div class="port-wrap port-seo-grid">
      <article class="port-seo-card">
        <div class="port-eyebrow">SEO + Lead generation</div>
        <h3>Każdy projekt działa jak osobny landing sprzedażowy.</h3>
        <p>Podstrony realizacji budują topical authority i odpowiadają na pytania klientów na etapie decyzji. Dzięki temu portfolio nie tylko „wygląda”, ale realnie wspiera pozycjonowanie i generowanie zapytań ofertowych.</p>
      </article>
      <article class="port-seo-card">
        <div class="port-eyebrow">Rekomendowana struktura case study</div>
        <ul class="port-seo-list">
          <li>Hero z nazwą projektu i celem biznesowym.</li>
          <li>Sekcja: problem / kontekst / punkt wyjścia.</li>
          <li>Zakres prac, wdrożenia i użyte technologie.</li>
          <li>Wynik biznesowy i metryki.</li>
          <li>CTA: „chcesz podobny efekt?” + formularz kontaktowy.</li>
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
    "name" => "Portfolio Upsellio",
    "description" => "Katalog realizacji stron internetowych, sklepów i aplikacji webowych tworzonych pod lead generation i sprzedaż.",
    "itemListElement" => $schema_items,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
?>
</script>
<?php endif; ?>

<script>
  (function () {
    const grid = document.getElementById("port-grid");
    const searchInput = document.getElementById("port-search");
    const categoryButtons = Array.from(document.querySelectorAll(".port-cat-btn"));
    if (!grid || !searchInput || !categoryButtons.length) return;

    let activeCategory = "all";

    function normalize(value) {
      return String(value || "").toLowerCase().trim();
    }

    function updateGrid() {
      const searchValue = normalize(searchInput.value);
      const cards = Array.from(grid.querySelectorAll(".port-card"));
      cards.forEach((card) => {
        const cardCategory = card.getAttribute("data-cat") || "";
        const searchableText = card.getAttribute("data-search") || "";
        const isCategoryMatch = activeCategory === "all" || cardCategory === activeCategory;
        const isSearchMatch = searchValue === "" || searchableText.includes(searchValue);
        card.classList.toggle("port-hidden", !(isCategoryMatch && isSearchMatch));
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
