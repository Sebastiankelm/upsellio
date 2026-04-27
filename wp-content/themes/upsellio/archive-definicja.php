<?php
if (!defined("ABSPATH")) {
    exit;
}

get_header();

$definitions = get_posts([
    "post_type" => "definicja",
    "post_status" => "publish",
    "numberposts" => -1,
    "orderby" => "title",
    "order" => "ASC",
]);
?>
<style>
  .defs-wrap{width:min(1140px,calc(100% - 32px));margin:0 auto}
  .defs-hero{padding:72px 0 36px;border-bottom:1px solid #e2e8f0;background:#f1f5f9}
  .defs-title{font-family:Syne,sans-serif;font-size:clamp(34px,5vw,56px);line-height:1.05;letter-spacing:-1px}
  .defs-lead{margin-top:16px;max-width:860px;font-size:18px;line-height:1.75;color:#334155}
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
  .defs-card{display:block;padding:14px;border:1px solid #e6e6e1;border-radius:12px;background:#fff;transition:.2s ease}
  .defs-card:hover{border-color:#0d9488;transform:translateY(-1px)}
  .defs-card-title{font-weight:600;line-height:1.4;color:#071426}
  .defs-card-meta{margin-top:8px;font-size:12px;color:#6f6f67}
  .defs-empty{display:none;padding:24px;border:1px dashed #c9c9c3;border-radius:12px;color:#6f6f67}
  @media(min-width:681px){.defs-controls{flex-direction:row}}
  @media(min-width:981px){.defs-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
</style>

<section class="defs-hero">
  <div class="defs-wrap">
    <h1 class="defs-title">Definicje SEO i marketingu</h1>
    <p class="defs-lead">
      Sekcja wiedzy z praktycznymi wyjasnieniami pojec SEO, SEM, analityki i optymalizacji konwersji.
      Kazda definicja zawiera unikalny opis, FAQ oraz linki do powiazanych zagadnien.
    </p>
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
    <h2 style="font-family:Syne,sans-serif;font-size:30px;line-height:1.2;">Slownik wiedzy</h2>
    <div class="defs-count" id="defs-count"><?php echo esc_html(count($definitions)); ?> definicji</div>
  </div>

  <div class="defs-grid" id="defs-grid">
    <?php foreach ($definitions as $definition) :
        $term = get_post_meta($definition->ID, "_upsellio_definition_term", true) ?: get_the_title($definition->ID);
        $category = get_post_meta($definition->ID, "_upsellio_definition_category", true) ?: "marketing";
        $difficulty = get_post_meta($definition->ID, "_upsellio_definition_difficulty", true) ?: "sredni";
        $letter = strtolower(substr(remove_accents($term), 0, 1));
        ?>
      <a
        class="defs-card"
        href="<?php echo esc_url(get_permalink($definition->ID)); ?>"
        data-title="<?php echo esc_attr(strtolower(remove_accents($term))); ?>"
        data-letter="<?php echo esc_attr($letter); ?>"
      >
        <div class="defs-card-title"><?php echo esc_html($term); ?></div>
        <div class="defs-card-meta"><?php echo esc_html($category . " • " . $difficulty); ?></div>
      </a>
    <?php endforeach; ?>
  </div>

  <div class="defs-empty" id="defs-empty">Brak wynikow dla wybranego filtra. Sprobuj innej frazy lub zakresu liter.</div>
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

