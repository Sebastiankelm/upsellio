<?php
if (!defined("ABSPATH")) {
    exit;
}

if (function_exists("upsellio_register_template_seo_head")) {
    upsellio_register_template_seo_head("definicje_archive");
}

get_header();

$definitions = get_posts([
    "post_type" => "definicja",
    "post_status" => "publish",
    "numberposts" => -1,
    "orderby" => "title",
    "order" => "ASC",
]);

$definition_schema_items = [];
foreach ($definitions as $index => $definition) {
    $term_name = get_post_meta($definition->ID, "_upsellio_definition_term", true) ?: get_the_title($definition->ID);
    $definition_schema_items[] = [
        "@type" => "ListItem",
        "position" => $index + 1,
        "url" => get_permalink($definition->ID),
        "name" => (string) $term_name,
    ];
}
?>
<?php if (!empty($definition_schema_items)) : ?>
<script type="application/ld+json">
<?php
echo wp_json_encode([
    "@context" => "https://schema.org",
    "@type" => "DefinedTermSet",
    "name" => "Slownik pojec marketingowych i SEO",
    "url" => get_post_type_archive_link("definicja"),
    "hasDefinedTerm" => array_map(static function ($item) {
        return [
            "@type" => "DefinedTerm",
            "name" => (string) ($item["name"] ?? ""),
            "url" => (string) ($item["url"] ?? ""),
        ];
    }, $definition_schema_items),
    "mainEntity" => [
        "@type" => "ItemList",
        "itemListElement" => $definition_schema_items,
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
</script>
<?php endif; ?>
<style>
  .defs-wrap{width:min(1140px,calc(100% - 32px));margin:0 auto}
  .defs-hero{position:relative;overflow:hidden;padding:72px 0 36px;border-bottom:1px solid #e2e8f0;background:radial-gradient(circle at top right, rgba(20,184,166,0.16), transparent 40%), linear-gradient(180deg,#ecfeff,#f1f5f9)}
  .defs-hero::before{content:"ABC";position:absolute;top:-30px;right:-20px;font-family:Syne,sans-serif;font-weight:800;font-size:clamp(160px,28vw,320px);line-height:.85;letter-spacing:-.08em;color:rgba(15,118,110,.06);pointer-events:none;user-select:none}
  .defs-hero > .defs-wrap{position:relative}
  .defs-pill{display:inline-flex;align-items:center;gap:8px;margin-bottom:14px;padding:6px 12px;border-radius:999px;background:#fff;border:1px solid #99f6e4;color:#0f766e;font-size:12px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}
  .defs-pill::before{content:"";width:6px;height:6px;border-radius:50%;background:#0d9488}
  .defs-title{font-family:Syne,sans-serif;font-size:clamp(34px,5vw,56px);line-height:1.05;letter-spacing:-1px}
  .defs-lead{margin-top:16px;max-width:860px;font-size:18px;line-height:1.75;color:#334155}
  .defs-stats{display:flex;flex-wrap:wrap;gap:18px;margin-top:18px;font-size:13px;color:#475569}
  .defs-stats strong{font-family:Syne,sans-serif;font-size:22px;color:#0f766e;letter-spacing:-.02em;display:block}
  .defs-controls{margin-top:24px;display:flex;flex-wrap:wrap;gap:12px;flex-direction:column}
  .defs-search{flex:1 1 300px}
  .defs-search input{width:100%;border:1px solid #c9c9c3;border-radius:10px;padding:12px 14px}
  .defs-filter{display:flex;flex-wrap:wrap;gap:8px}
  .defs-chip{border:1px solid #cbd5e1;border-radius:999px;padding:8px 12px;font-size:12px;color:#334155;cursor:pointer;background:#fff}
  .defs-chip.is-active,.defs-chip:hover{border-color:#0d9488;color:#0d9488}
  .defs-main{padding:42px 0 56px}
  .defs-headline{display:flex;justify-content:space-between;align-items:center;gap:14px;margin-bottom:16px}
  .defs-count{font-size:13px;color:#6f6f67}
  .defs-grid{display:grid;grid-template-columns:1fr;gap:12px 16px}
  .defs-card{display:block;padding:16px;border:1px solid #e6e6e1;border-radius:14px;background:#fff;transition:.2s ease}
  .defs-card:hover{border-color:#0d9488;transform:translateY(-2px);box-shadow:0 14px 30px rgba(15,23,42,.06)}
  .defs-card-title{font-weight:700;font-size:16px;line-height:1.32;color:#071426}
  .defs-card-excerpt{margin-top:8px;font-size:13px;line-height:1.55;color:#475569;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
  .defs-card-meta{margin-top:10px;font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:#0f766e;font-weight:700}
  .defs-empty{display:none;padding:24px;border:1px dashed #c9c9c3;border-radius:12px;color:#6f6f67}
  @media(min-width:681px){.defs-controls{flex-direction:row}}
  @media(min-width:981px){.defs-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
</style>

<section class="defs-hero">
  <div class="defs-wrap">
    <span class="defs-pill">Słownik Upsellio</span>
    <h1 class="defs-title">Definicje SEO i marketingu</h1>
    <p class="defs-lead">
      Sekcja wiedzy z praktycznymi wyjaśnieniami pojęć SEO, SEM, analityki i optymalizacji konwersji.
      Każda definicja zawiera unikalny opis, FAQ oraz linki do powiązanych zagadnień.
    </p>
    <div class="defs-stats" aria-hidden="true">
      <div><strong><?php echo esc_html(count($definitions)); ?></strong>definicji w bazie</div>
      <div><strong>4</strong>kategorie tematyczne</div>
      <div><strong>3</strong>poziomy zaawansowania</div>
    </div>
    <div class="defs-controls">
      <div class="defs-search">
        <input id="defs-search-input" type="text" placeholder="Szukaj definicji..." aria-label="Szukaj definicji">
      </div>
      <div class="defs-filter" id="defs-filter">
        <button class="defs-chip is-active" data-letter="all" type="button">Wszystko</button>
        <button class="defs-chip" data-letter="a-f" type="button">A-F</button>
        <button class="defs-chip" data-letter="g-l" type="button">G-L</button>
        <button class="defs-chip" data-letter="m-r" type="button">M-R</button>
        <button class="defs-chip" data-letter="s-z" type="button">S-Z</button>
      </div>
    </div>
  </div>
</section>

<main class="defs-main defs-wrap">
  <div class="defs-headline">
    <h2 style="font-family:Syne,sans-serif;font-size:30px;line-height:1.2;">Słownik wiedzy</h2>
    <div class="defs-count" id="defs-count"><?php echo esc_html(count($definitions)); ?> definicji</div>
  </div>

  <div class="defs-grid" id="defs-grid">
    <?php foreach ($definitions as $definition) :
        $term = get_post_meta($definition->ID, "_upsellio_definition_term", true) ?: get_the_title($definition->ID);
        $category = get_post_meta($definition->ID, "_upsellio_definition_category", true) ?: "marketing";
        $difficulty = get_post_meta($definition->ID, "_upsellio_definition_difficulty", true) ?: "sredni";
        $excerpt_raw = (string) get_post_meta($definition->ID, "_upsellio_definition_short", true);
        if ($excerpt_raw === "") {
            $excerpt_raw = (string) $definition->post_excerpt;
        }
        if ($excerpt_raw === "") {
            $excerpt_raw = wp_strip_all_tags((string) $definition->post_content);
        }
        $excerpt = wp_trim_words($excerpt_raw, 18, "…");
        $letter = strtolower(substr(remove_accents($term), 0, 1));
        ?>
      <a
        class="defs-card"
        href="<?php echo esc_url(get_permalink($definition->ID)); ?>"
        data-title="<?php echo esc_attr(strtolower(remove_accents($term))); ?>"
        data-letter="<?php echo esc_attr($letter); ?>"
      >
        <div class="defs-card-title"><?php echo esc_html($term); ?></div>
        <?php if ($excerpt !== "") : ?>
          <div class="defs-card-excerpt"><?php echo esc_html($excerpt); ?></div>
        <?php endif; ?>
        <div class="defs-card-meta"><?php echo esc_html($category . " · " . $difficulty); ?></div>
      </a>
    <?php endforeach; ?>
  </div>

  <div class="defs-empty" id="defs-empty">Brak wyników dla wybranego filtra. Spróbuj innej frazy lub zakresu liter.</div>
</main>

<script>
  (function () {
    const searchInput = document.getElementById("defs-search-input");
    const filterRoot = document.getElementById("defs-filter");
    const cards = Array.from(document.querySelectorAll(".defs-card"));
    const countNode = document.getElementById("defs-count");
    const emptyNode = document.getElementById("defs-empty");
    let activeLetter = "all";

    function inLetterRange(letter, range) {
      if (range === "all") return true;
      const map = {
        "a-f": ["a","b","c","d","e","f"],
        "g-l": ["g","h","i","j","k","l"],
        "m-r": ["m","n","o","p","q","r"],
        "s-z": ["s","t","u","v","w","x","y","z"]
      };
      return (map[range] || []).includes(letter);
    }

    function filterCards() {
      const query = (searchInput.value || "").trim().toLowerCase();
      let visible = 0;
      cards.forEach((card) => {
        const title = card.dataset.title || "";
        const letter = card.dataset.letter || "";
        const isMatchSearch = query === "" || title.includes(query);
        const isMatchLetter = inLetterRange(letter, activeLetter);
        const shouldShow = isMatchSearch && isMatchLetter;
        card.style.display = shouldShow ? "" : "none";
        if (shouldShow) visible++;
      });
      countNode.textContent = visible + " definicji";
      emptyNode.style.display = visible === 0 ? "block" : "none";
    }

    searchInput.addEventListener("input", filterCards);
    filterRoot.addEventListener("click", function (event) {
      const chip = event.target.closest(".defs-chip");
      if (!chip) return;
      activeLetter = chip.dataset.letter || "all";
      document.querySelectorAll(".defs-chip").forEach((item) => item.classList.remove("is-active"));
      chip.classList.add("is-active");
      filterCards();
    });
  })();
</script>
<?php
get_footer();

