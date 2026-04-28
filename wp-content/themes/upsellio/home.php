<?php
if (!defined("ABSPATH")) {
    exit;
}

$paged = max(1, (int) get_query_var("paged"), (int) get_query_var("page"));
$selected_category = isset($_GET["category"]) ? sanitize_title(wp_unslash($_GET["category"])) : "";
$selected_tags = [];
if (isset($_GET["tags"])) {
    $selected_tags = upsellio_parse_tag_filters(wp_unslash($_GET["tags"]));
} elseif (isset($_GET["tag"])) {
    // Legacy fallback for existing links.
    $selected_tags = upsellio_parse_tag_filters(wp_unslash($_GET["tag"]));
}
$search_term = isset($_GET["s"]) ? sanitize_text_field(wp_unslash($_GET["s"])) : "";
$blog_index_url = upsellio_get_blog_index_url();
$categories = get_categories(["hide_empty" => true]);
$tags = get_tags(["hide_empty" => true]);
$blog_content_categories = [
    ["name" => "Meta Ads", "slug" => "meta-ads", "desc" => "Kampanie Meta Ads, strategie, lejki, kreacje i remarketing dla firm.", "keywords" => "Meta Ads dla firm, Facebook Ads B2B, lejek Meta Ads, remarketing Facebook"],
    ["name" => "Google Ads", "slug" => "google-ads", "desc" => "Kampanie Search, Performance Max, słowa kluczowe, struktura i optymalizacja.", "keywords" => "Google Ads dla firm, kampanie Search, słowa kluczowe z intencją zakupową"],
    ["name" => "Konwersja i strony WWW", "slug" => "konwersja-strony", "desc" => "Landing pages, optymalizacja konwersji, copywriting, CTA i UX pod sprzedaż.", "keywords" => "konwersja strony internetowej, landing page firma, CTA na stronie"],
    ["name" => "Pozyskiwanie klientów", "slug" => "pozyskiwanie-klientow", "desc" => "Lejki sprzedażowe, CPL, jakość zapytań i system marketingowy.", "keywords" => "pozyskiwanie klientów B2B, koszt pozyskania leada, CPL optymalizacja"],
    ["name" => "Analityka i mierzenie", "slug" => "analityka", "desc" => "GA4, śledzenie konwersji, Tag Manager, atrybucja i raportowanie.", "keywords" => "śledzenie konwersji Google Ads, GA4 konfiguracja, atrybucja kampanii"],
];
$blog_content_plan = [
    "Miesiąc 1 — Meta Ads i lejek" => ["Dlaczego reklamy Meta Ads nie sprzedają — 7 błędów, które blokują wynik", "Lejek Meta Ads od podstaw — ToF, MoF, BoF i remarketing w jednej kampanii", "Remarketing Meta Ads — jak odzyskać osoby, które nie zostawiły kontaktu", "Jak mierzyć jakość leadów z Facebook Ads — i dlaczego CPL to za mało"],
    "Miesiąc 2 — Google Ads i intencja zakupowa" => ["Google Ads dla firm B2B — od czego zacząć, żeby nie przepalić budżetu", "Słowa kluczowe z intencją zakupową — jak je dobierać do kampanii Search", "Search Ads vs Performance Max — który typ kampanii wybrać dla swojej firmy", "Audyt kampanii Google Ads — 12 rzeczy, które warto sprawdzić co miesiąc"],
    "Miesiąc 3 — Strony i konwersja" => ["Landing page pod Google Ads — co musi zawierać, żeby konwertować", "Dlaczego strona firmowa nie generuje zapytań — 6 przyczyn i jak je naprawić", "CTA na stronie internetowej — jak pisać wezwania do działania, które klikają", "Copywriting dla firm B2B — jak pisać treści strony, które przekonują"],
    "Miesiąc 4 — Lead generation i sprzedaż" => ["System pozyskiwania klientów online — czym różni się od kampanii ad hoc", "CPL — co to jest, jak liczyć i jak go obniżać bez cięcia budżetu", "Jak odróżnić dobry lead od złego — i dlaczego to ważniejsze niż liczba kontaktów", "Meta Ads czy Google Ads — co wybrać dla firmy B2B i dlaczego to zależy"],
    "Miesiąc 5 — Analityka i mierzenie" => ["Śledzenie konwersji Google Ads — jak skonfigurować i co naprawdę mierzyć", "Piksel Meta — co to jest, jak działa i dlaczego bez niego kampanie są mniej skuteczne", "Google Analytics 4 dla firm B2B — co warto śledzić i jakie raporty są ważne", "ROAS vs CPL — która metryka jest ważniejsza i kiedy patrzeć na którą"],
    "Miesiąc 6 — Strategie i planowanie" => ["Jak zbudować plan marketingowy dla małej firmy", "Budżet reklamowy dla firm B2B — ile wydawać i jak to wyliczyć", "Dlaczego marketing nie działa — 5 systemowych przyczyn", "Core Web Vitals 2025 — jak szybkość ładowania wpływa na SEO i konwersję"],
];

add_filter("pre_get_document_title", static function ($title) {
    return is_home() || is_page_template("page-blog.php")
        ? "Blog o marketingu B2B | Meta Ads, Google Ads, strony | Upsellio"
        : $title;
});

add_action("wp_head", static function () use ($blog_index_url, $paged) {
    if (!(is_home() || is_page_template("page-blog.php"))) return;

    echo '<meta name="description" content="Blog o Meta Ads, Google Ads, tworzeniu stron i pozyskiwaniu klientów dla firm B2B. Konkrety zamiast teorii — artykuły pisane przez praktyka.">' . "\n";
    echo '<meta property="og:title" content="Blog o marketingu B2B | Meta Ads, Google Ads, strony | Upsellio">' . "\n";
    echo '<meta property="og:description" content="Artykuły o Meta Ads, Google Ads, konwersji stron i pozyskiwaniu klientów dla firm B2B. Konkretne poradniki zamiast marketingowego szumu.">' . "\n";
    echo '<meta property="og:type" content="website">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($blog_index_url) . '">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<link rel="canonical" href="' . esc_url($paged > 1 ? add_query_arg("paged", $paged, $blog_index_url) : $blog_index_url) . '">' . "\n";
    if ($paged > 1) {
        $prev_url = $paged === 2 ? $blog_index_url : add_query_arg("paged", $paged - 1, $blog_index_url);
        echo '<link rel="prev" href="' . esc_url($prev_url) . '">' . "\n";
    }
    echo '<link rel="next" href="' . esc_url(add_query_arg("paged", $paged + 1, $blog_index_url)) . '">' . "\n";
}, 1);

get_header();
?>
<style>
  .ups-blog {
    border-bottom: 1px solid var(--border);
    background: var(--bg);
  }
  .ups-blog-hero {
    position: relative;
    overflow: hidden;
    border-bottom: 1px solid var(--border);
    background: var(--bg-soft);
  }
  .ups-blog-hero::before {
    content: "";
    position: absolute;
    top: -120px;
    right: -140px;
    width: 520px;
    height: 520px;
    border-radius: 999px;
    pointer-events: none;
    background: radial-gradient(circle, rgba(20, 184, 166, 0.13), transparent 68%);
  }
  .ups-blog-hero-topline {
    position: absolute;
    inset: 0 0 auto;
    height: 3px;
    background: var(--teal);
  }
  .ups-blog-title {
    font-family: var(--font-display);
    font-size: clamp(32px, 9vw, 64px);
    font-weight: 800;
    line-height: 0.98;
    letter-spacing: -2px;
  }
  .ups-blog-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 20px;
    border: 1px solid var(--border);
    border-radius: var(--r-pill);
    background: color-mix(in srgb, var(--surface) 94%, transparent);
    backdrop-filter: blur(6px);
    padding: 7px 14px 7px 10px;
    font-size: 12px;
    font-weight: 500;
    color: var(--text-2);
  }
  .ups-blog-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: var(--teal);
    animation: upsBlogDotPulse 1.8s ease infinite;
  }
  @keyframes upsBlogDotPulse {
    0%, 100% {
      opacity: 1;
      transform: scale(1);
    }
    50% {
      opacity: 0.45;
      transform: scale(0.88);
    }
  }
  .ups-blog-lead {
    margin-top: var(--sp-3);
    max-width: 780px;
    font-size: 17px;
    line-height: 1.72;
    color: var(--text-2);
  }
  .ups-blog-search {
    margin-top: var(--sp-5);
    display: grid;
    gap: 12px;
    grid-template-columns: 1fr;
    max-width: 980px;
  }
  .ups-blog-search-field,
  .ups-blog-search-note {
    border: 1px solid var(--border);
    border-radius: 16px;
    background: var(--surface);
    padding: 12px 16px;
    box-shadow: var(--shadow-sm);
  }
  .ups-blog-search-field {
    display: flex;
    align-items: center;
    gap: 10px;
    transition: border-color 0.18s ease, box-shadow 0.18s ease;
  }
  .ups-blog-search-field:focus-within {
    border-color: var(--teal);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--teal) 20%, transparent);
  }
  .ups-blog-search-field input {
    width: 100%;
    border: none;
    outline: none;
    background: transparent;
    color: var(--text);
    min-height: 46px;
    font-size: 16px;
  }
  .ups-blog-search-note {
    color: var(--text-2);
    font-size: 14px;
    border-style: dashed;
  }
  .ups-blog-categories {
    border-bottom: 1px solid var(--border);
    padding: 28px 0;
  }
  .ups-blog-category-list {
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    scrollbar-width: none;
    gap: 10px;
  }
  .ups-blog-category-list::-webkit-scrollbar {
    display: none;
  }
  .ups-blog-tag-filter-wrap {
    margin-top: 12px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
  }
  .ups-blog-tag-filter-label {
    font-size: 12px;
    font-weight: 700;
    color: var(--text-3);
    letter-spacing: 0.04em;
    text-transform: uppercase;
  }
  .ups-blog-tag-filter-list {
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    scrollbar-width: none;
    gap: 8px;
  }
  .ups-blog-tag-filter-list::-webkit-scrollbar {
    display: none;
  }
  .ups-blog-filter-tools {
    margin-top: 14px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
  }
  .ups-blog-active-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }
  .ups-blog-active-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid var(--teal-line);
    border-radius: var(--r-pill);
    background: var(--teal-soft);
    padding: 6px 10px;
    color: var(--teal-dark);
    font-size: 12px;
    font-weight: 600;
  }
  .ups-blog-active-remove {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 999px;
    border: 1px solid var(--teal-line);
    background: rgba(255, 255, 255, 0.65);
    color: var(--teal-dark);
    font-size: 13px;
    line-height: 1;
    cursor: pointer;
  }
  .ups-blog-clear-filters {
    border: 1px solid var(--border);
    border-radius: var(--r-pill);
    background: var(--surface);
    padding: 7px 12px;
    color: var(--text-2);
    font-size: 12px;
    font-weight: 600;
    transition: 0.18s ease;
  }
  .ups-blog-clear-filters:hover {
    border-color: var(--teal);
    color: var(--teal);
  }
  .ups-blog-filter-note {
    margin-top: 8px;
    color: var(--text-3);
    font-size: 12px;
  }
  .ups-blog-filter-note.error {
    color: #c14b4b;
  }
  .ups-blog-category {
    border: 1px solid var(--border);
    border-radius: var(--r-pill);
    padding: 10px 14px;
    min-height: 42px;
    font-size: 13px;
    font-weight: 500;
    color: var(--text-2);
    background: var(--surface);
    transition: 0.18s ease;
  }
  .ups-blog-category:hover {
    border-color: var(--teal);
    color: var(--teal);
  }
  .ups-blog-category.active {
    border-color: var(--teal);
    background: var(--teal-soft);
    color: var(--teal-dark);
  }
  .ups-blog-featured-wrap {
    border-bottom: 1px solid var(--border);
    background: var(--bg-soft);
    padding: 48px 0 64px;
  }
  .ups-blog-featured-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
  }
  .ups-blog-featured-card {
    overflow: hidden;
    border: 1px solid var(--border);
    border-radius: var(--r-xl);
    background: var(--surface);
    box-shadow: var(--shadow-sm);
    transition: 0.2s ease;
  }
  .ups-blog-featured-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
  }
  .ups-blog-featured-main {
    display: grid;
    grid-template-columns: 1fr;
  }
  .ups-blog-featured-cover {
    position: relative;
    min-height: 320px;
    background: linear-gradient(135deg, #dff5ee, #f7faf9);
    padding: 24px;
  }
  .ups-blog-featured-cover::after {
    content: "";
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at top right, rgba(20, 184, 166, 0.13), transparent 40%);
  }
  .ups-blog-featured-content {
    position: relative;
    z-index: 1;
    display: flex;
    height: 100%;
    flex-direction: column;
    justify-content: space-between;
  }
  .ups-blog-featured-label {
    width: fit-content;
    border: 1px solid var(--teal);
    border-radius: var(--r-pill);
    background: rgba(255, 255, 255, 0.8);
    padding: 5px 12px;
    font-size: 12px;
    font-weight: 700;
    color: var(--teal-dark);
  }
  .ups-blog-featured-title-shell {
    border: 1px solid rgba(255, 255, 255, 0.7);
    border-radius: 22px;
    background: rgba(255, 255, 255, 0.9);
    padding: 24px;
  }
  .ups-blog-featured-category {
    margin-bottom: 10px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--text-3);
  }
  .ups-blog-featured-title {
    font-family: var(--font-display);
    font-size: clamp(28px, 2.2vw, 38px);
    line-height: 1.1;
    letter-spacing: -1px;
  }
  .ups-blog-featured-text {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 24px;
  }
  .ups-blog-featured-meta {
    margin-bottom: 12px;
    font-size: 12px;
    color: var(--text-3);
  }
  .ups-blog-featured-excerpt {
    font-size: 16px;
    line-height: 1.8;
    color: var(--text-2);
  }
  .ups-blog-actions {
    margin-top: var(--sp-4);
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
  }
  .ups-blog-actions a {
    width: 100%;
  }
  .ups-blog-btn-primary,
  .ups-blog-btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--r-md);
    min-height: 46px;
    font-size: 14px;
    transition: 0.18s ease;
  }
  .ups-blog-btn-primary {
    background: var(--teal);
    color: #fff;
    padding: 12px 20px;
    font-weight: 600;
  }
  .ups-blog-btn-primary:hover {
    background: var(--teal-dark);
  }
  .ups-blog-btn-secondary {
    border: 1px solid var(--border);
    color: var(--text-2);
    padding: 12px 20px;
    font-weight: 500;
  }
  .ups-blog-btn-secondary:hover {
    border-color: var(--teal);
    color: var(--teal);
  }
  .ups-blog-side {
    display: flex;
    flex-direction: column;
    gap: 18px;
  }
  .ups-blog-panel {
    border: 1px solid var(--border);
    border-radius: 24px;
    background: var(--surface);
    padding: 24px;
    box-shadow: var(--shadow-sm);
  }
  .ups-blog-panel-title {
    margin-top: 10px;
    font-family: var(--font-display);
    font-size: 30px;
    line-height: 1.1;
    letter-spacing: -1px;
  }
  .ups-blog-panel-text {
    margin-top: 14px;
    font-size: 15px;
    line-height: 1.75;
    color: var(--text-2);
  }
  .ups-blog-newsletter {
    margin-top: 16px;
    border: 1px solid var(--border-strong);
    border-radius: 16px;
    background: var(--surface);
    padding: 14px;
  }
  .ups-blog-newsletter input {
    width: 100%;
    min-height: 46px;
    border: 1px solid var(--border-strong);
    border-radius: 12px;
    background: var(--surface);
    color: var(--text);
    padding: 13px 15px;
    outline: none;
    transition: border-color 0.18s ease, box-shadow 0.18s ease;
  }
  .ups-blog-newsletter input:focus {
    border-color: var(--teal);
    box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.13);
  }
  .ups-blog-newsletter button {
    margin-top: 8px;
    width: 100%;
    border: none;
    border-radius: 12px;
    min-height: 46px;
    background: var(--teal);
    color: #fff;
    padding: 12px 16px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: background-color 0.18s ease, transform 0.18s ease;
  }
  .ups-blog-newsletter button:hover {
    background: var(--teal-hover);
    transform: translateY(-1px);
  }
  .ups-blog-tags {
    margin-top: 14px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }
  .ups-blog-tag {
    border: 1px solid var(--border);
    border-radius: var(--r-pill);
    background: var(--bg-soft);
    padding: 6px 11px;
    font-size: 12px;
    color: var(--text-2);
  }
  .ups-blog-list-wrap {
    padding: 64px 0 72px;
  }
  .ups-blog-list-head {
    margin-bottom: 32px;
    display: flex;
    justify-content: space-between;
    align-items: end;
    gap: 16px;
  }
  .ups-blog-list-title {
    margin-top: 10px;
    font-family: var(--font-display);
    font-size: clamp(34px, 3.2vw, 50px);
    line-height: 1.02;
    letter-spacing: -1px;
  }
  .ups-blog-list-meta {
    font-size: 14px;
    color: var(--text-3);
  }
  .ups-blog-grid {
    display: grid;
    gap: 16px;
    grid-template-columns: 1fr;
  }
  .ups-blog-card {
    display: flex;
    flex-direction: column;
    border: 1px solid var(--border);
    border-radius: 24px;
    background: var(--surface);
    padding: 20px;
    box-shadow: var(--shadow-sm);
    transition: 0.2s ease;
  }
  .ups-blog-card:hover {
    transform: translateY(-4px);
    border-color: var(--teal);
    box-shadow: var(--shadow-md);
  }
  .ups-blog-card-category {
    width: fit-content;
    margin-bottom: 14px;
    border: 1px solid var(--border);
    border-radius: var(--r-pill);
    background: var(--bg-soft);
    padding: 4px 10px;
    font-size: 11px;
    font-weight: 700;
    color: var(--text-2);
  }
  .ups-blog-card-title {
    font-family: var(--font-display);
    font-size: 26px;
    line-height: 1.1;
    letter-spacing: -0.8px;
  }
  .ups-blog-card-excerpt {
    margin-top: 14px;
    font-size: 15px;
    line-height: 1.75;
    color: var(--text-2);
  }
  .ups-blog-card-footer {
    margin-top: auto;
    padding-top: 24px;
  }
  .ups-blog-card-meta {
    margin-bottom: 10px;
    font-size: 12px;
    color: var(--text-3);
  }
  .ups-blog-card-link {
    display: inline-flex;
    align-items: center;
    min-height: 44px;
    color: var(--teal);
    font-size: 14px;
    font-weight: 700;
  }
  .ups-blog-pagination {
    margin-top: var(--sp-5);
    display: flex;
    justify-content: center;
    gap: 8px;
    flex-wrap: wrap;
  }
  .ups-blog-page-link {
    border: 1px solid var(--border);
    border-radius: var(--r-md);
    padding: 10px 14px;
    font-size: 14px;
    color: var(--text-2);
    transition: 0.18s ease;
  }
  .ups-blog-page-link:hover {
    border-color: var(--teal);
    color: var(--teal);
  }
  .ups-blog-page-link.current {
    border-color: var(--teal);
    background: var(--teal-soft);
    color: var(--teal-dark);
    font-weight: 700;
  }
  .ups-blog-cta {
    border-top: 1px solid var(--border);
    background: var(--bg-soft);
    padding: 72px 0;
  }
  .ups-blog-newsletter-band {
    border-top: 1px solid var(--teal-line);
    border-bottom: 1px solid var(--teal-line);
    background: var(--teal-soft);
    padding: 64px 0;
  }
  .ups-blog-newsletter-inner {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
    align-items: center;
  }
  .ups-blog-newsletter-title {
    margin-top: 8px;
    color: var(--teal-dark);
    font-family: var(--font-display);
    font-size: clamp(28px, 3.2vw, 40px);
    line-height: 1.08;
    letter-spacing: -0.9px;
    max-width: 680px;
  }
  .ups-blog-newsletter-text {
    margin-top: 12px;
    max-width: 620px;
    color: color-mix(in srgb, var(--teal-dark) 82%, white);
    font-size: 16px;
    line-height: 1.78;
  }
  .ups-blog-newsletter-shell {
    border: 1px solid var(--border-strong);
    border-radius: 18px;
    background: var(--surface);
    box-shadow: var(--shadow-sm);
    padding: 8px;
  }
  .ups-blog-newsletter-row {
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .ups-blog-newsletter-row input {
    min-width: 0;
    flex: 1;
    border: 1px solid var(--border-strong);
    border-radius: 12px;
    outline: none;
    background: var(--surface);
    color: var(--text);
    min-height: 46px;
    padding: 13px 15px;
    font-size: 15px;
    transition: border-color 0.18s ease, box-shadow 0.18s ease;
  }
  .ups-blog-newsletter-row input:focus {
    border-color: var(--teal);
    box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.13);
  }
  .ups-blog-newsletter-row button {
    border: none;
    border-radius: 12px;
    background: var(--teal);
    color: #fff;
    min-height: 46px;
    padding: 12px 16px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: background-color 0.18s ease, transform 0.18s ease;
    white-space: nowrap;
  }
  .ups-blog-newsletter-row button:hover {
    background: var(--teal-hover);
    transform: translateY(-1px);
  }
  .ups-blog-newsletter-note {
    margin-top: 12px;
    color: color-mix(in srgb, var(--teal-dark) 70%, white);
    font-size: 12px;
  }
  .ups-blog-topics {
    border-top: 1px solid var(--border);
    padding: 64px 0;
  }
  .ups-blog-topics-head {
    max-width: 760px;
  }
  .ups-blog-topics-title {
    margin-top: 8px;
    font-family: var(--font-display);
    font-size: clamp(28px, 3vw, 40px);
    line-height: 1.1;
    letter-spacing: -0.8px;
  }
  .ups-blog-topics-grid {
    margin-top: 28px;
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
  }
  .ups-blog-topic-card {
    display: block;
    border: 1px solid var(--border);
    border-radius: 16px;
    background: var(--bg-soft);
    padding: 16px 12px;
    text-align: center;
    transition: 0.18s ease;
  }
  .ups-blog-topic-card:hover {
    border-color: var(--teal);
    transform: translateY(-2px);
    background: var(--teal-soft);
  }
  .ups-blog-topic-icon {
    width: 44px;
    height: 44px;
    margin: 0 auto 10px;
    border-radius: 12px;
    border: 1px solid var(--teal-line);
    background: var(--teal-soft);
    color: var(--teal-dark);
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
  .ups-blog-topic-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
  }
  .ups-blog-topic-count {
    margin-top: 4px;
    font-size: 12px;
    color: var(--text-3);
  }
  .ups-blog-cta-shell {
    border: 1px solid var(--teal-line);
    border-radius: var(--r-xl);
    background: var(--teal-soft);
    padding: 30px;
  }
  .ups-blog-cta-title {
    margin-top: 8px;
    max-width: 860px;
    font-family: var(--font-display);
    font-size: clamp(34px, 3.2vw, 50px);
    line-height: 1.05;
    letter-spacing: -1px;
    color: var(--teal-dark);
  }
  .ups-blog-cta-text {
    margin-top: 12px;
    max-width: 850px;
    color: color-mix(in srgb, var(--teal-dark) 82%, white);
    font-size: 15px;
    line-height: 1.78;
  }
  .ups-blog-cta-actions {
    margin-top: var(--sp-3);
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
  }
  .ups-blog-cta-actions a {
    width: 100%;
  }
  .ups-blog-empty {
    border: 1px solid var(--border);
    border-radius: var(--r-lg);
    padding: 22px;
    color: var(--text-2);
    background: var(--surface);
  }
  .js-ups-blog-dynamic {
    position: relative;
  }
  .js-ups-blog-dynamic.is-loading {
    pointer-events: none;
  }
  .ups-blog-skeleton {
    display: none;
    margin-top: 28px;
  }
  .js-ups-blog-dynamic.is-loading + .ups-blog-skeleton {
    display: block;
  }
  .ups-blog-skeleton-grid {
    display: grid;
    gap: 16px;
    grid-template-columns: 1fr;
  }
  .ups-blog-skeleton-card {
    border: 1px solid var(--border);
    border-radius: 24px;
    background: var(--surface);
    padding: 24px;
  }
  .ups-blog-skeleton-bar {
    border-radius: 999px;
    background: linear-gradient(90deg, #ecece8 25%, #f7f7f5 50%, #ecece8 75%);
    background-size: 200% 100%;
    animation: upsBlogSkeleton 1.3s linear infinite;
  }
  @keyframes upsBlogSkeleton {
    from { background-position: 200% 0; }
    to { background-position: -200% 0; }
  }
  .ups-blog-list-meta {
    display: none;
  }
  @media (min-width: 761px) {
    .ups-blog-lead {
      font-size: 18px;
      line-height: 1.8;
    }
    .ups-blog-search {
      grid-template-columns: minmax(0, 1fr) 320px;
    }
    .ups-blog-search-field input {
      font-size: 15px;
    }
    .ups-blog-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .ups-blog-skeleton-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .ups-blog-list-meta {
      display: block;
    }
    .ups-blog-actions a,
    .ups-blog-cta-actions a {
      width: auto;
    }
    .ups-blog-newsletter-inner {
      grid-template-columns: 1fr 420px;
      gap: 40px;
    }
    .ups-blog-topics-grid {
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 14px;
    }
  }
  @media (min-width: 1051px) {
    .ups-blog-featured-grid {
      grid-template-columns: 1.25fr 0.75fr;
    }
    .ups-blog-featured-main {
      grid-template-columns: 1.05fr 0.95fr;
    }
    .ups-blog-grid,
    .ups-blog-skeleton-grid {
      grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    .ups-blog-featured-cover,
    .ups-blog-featured-text {
      padding: 32px;
    }
    .ups-blog-cta-shell {
      padding: 34px;
    }
    .ups-blog-topics-grid {
      grid-template-columns: repeat(5, minmax(0, 1fr));
    }
  }
  /* Blog layout refresh */
  .ups-blog-categories {
    position: sticky;
    top: 72px;
    z-index: 40;
    background: color-mix(in srgb, var(--bg) 94%, transparent);
    backdrop-filter: blur(10px);
    border-top: 1px solid var(--border);
    padding: 14px 0 18px;
  }
  .ups-blog-category {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border-radius: var(--r-pill);
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.01em;
    min-height: 38px;
    padding: 8px 13px;
  }
  .ups-blog-category-count {
    border-radius: var(--r-pill);
    background: color-mix(in srgb, var(--bg-soft) 92%, transparent);
    border: 1px solid var(--border);
    color: var(--text-3);
    font-size: 10px;
    font-weight: 700;
    line-height: 1;
    padding: 3px 6px;
  }
  .ups-blog-category.active .ups-blog-category-count {
    border-color: color-mix(in srgb, var(--teal) 20%, transparent);
    background: rgba(255, 255, 255, 0.48);
    color: var(--teal-dark);
  }
  .ups-blog-card {
    border-radius: 20px;
    padding: 18px;
  }
  .ups-blog-card.is-wide {
    grid-column: span 2;
  }
  .ups-blog-card-top {
    margin-bottom: 14px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
  }
  .ups-blog-card-time {
    white-space: nowrap;
    font-size: 11px;
    color: var(--text-3);
  }
  .ups-blog-card-category {
    margin-bottom: 0;
    border-color: var(--teal-line);
    color: var(--teal-dark);
    background: var(--teal-soft);
  }
  .ups-blog-card-title {
    font-size: clamp(21px, 1.8vw, 27px);
    letter-spacing: -0.6px;
  }
  .ups-blog-card-excerpt {
    margin-top: 12px;
    font-size: 14px;
    line-height: 1.7;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
  .ups-blog-card-footer {
    margin-top: 18px;
    padding-top: 12px;
    border-top: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
  }
  .ups-blog-card-meta {
    margin-bottom: 0;
  }
  .ups-blog-card-link {
    min-height: 0;
    white-space: nowrap;
  }
  .ups-blog-seo-copy {
    margin-top: 24px;
    max-width: 960px;
    display: grid;
    gap: 14px;
  }
  .ups-blog-seo-copy p {
    color: var(--text-2);
    line-height: 1.78;
  }
  .ups-blog-newsletter-points,
  .ups-blog-plan-list {
    margin: 14px 0 0;
    padding: 0;
    list-style: none;
    display: grid;
    gap: 8px;
  }
  .ups-blog-newsletter-points li,
  .ups-blog-plan-list li {
    position: relative;
    padding-left: 24px;
    color: var(--text-2);
    font-size: 14px;
    line-height: 1.6;
  }
  .ups-blog-newsletter-points li::before,
  .ups-blog-plan-list li::before {
    content: "✓";
    position: absolute;
    left: 0;
    color: var(--teal);
    font-weight: 900;
  }
  .ups-blog-category-strategy {
    border-top: 1px solid var(--border);
    background: var(--surface);
    padding: 64px 0;
  }
  .ups-blog-category-strategy-grid,
  .ups-blog-plan-grid {
    margin-top: 28px;
    display: grid;
    grid-template-columns: 1fr;
    gap: 14px;
  }
  .ups-blog-category-strategy-card,
  .ups-blog-plan-card {
    border: 1px solid var(--border);
    border-radius: 22px;
    background: var(--bg-soft);
    padding: 22px;
    box-shadow: var(--shadow-sm);
  }
  .ups-blog-category-strategy-card strong,
  .ups-blog-plan-card strong {
    display: block;
    margin-bottom: 8px;
    color: var(--text);
    font-size: 17px;
  }
  .ups-blog-category-strategy-card p {
    color: var(--text-2);
    font-size: 14px;
    line-height: 1.7;
  }
  .ups-blog-category-strategy-card small {
    display: block;
    margin-top: 10px;
    color: var(--text-3);
    line-height: 1.55;
  }
  .ups-blog-content-plan {
    border-top: 1px solid var(--border);
    background: var(--bg);
    padding: 64px 0;
  }
  @media (max-width: 1050px) {
    .ups-blog-categories {
      position: static;
      backdrop-filter: none;
      background: var(--bg);
    }
    .ups-blog-card.is-wide {
      grid-column: span 1;
    }
    .ups-blog-newsletter-row {
      flex-direction: column;
      align-items: stretch;
    }
    .ups-blog-list-wrap {
      padding: 52px 0 58px;
    }
    .ups-blog-newsletter-band,
    .ups-blog-topics,
    .ups-blog-cta {
      padding: 52px 0;
    }
    .ups-blog-cta-shell {
      padding: 24px;
    }
  }
  @media (min-width: 761px) {
    .ups-blog-category-strategy-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .ups-blog-plan-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }
  @media (min-width: 1051px) {
    .ups-blog-category-strategy-grid {
      grid-template-columns: repeat(5, minmax(0, 1fr));
    }
    .ups-blog-plan-grid {
      grid-template-columns: repeat(3, minmax(0, 1fr));
    }
  }
  /* Mobile-first UX correction layer */
  .ups-blog-title { font-size:clamp(34px,10vw,40px); line-height:1.09; letter-spacing:-1px; }
  .ups-blog-hero .wrap { padding-top:48px !important; padding-bottom:56px !important; }
  .ups-blog-lead { margin-top:16px; font-size:17px; line-height:1.65; }
  .ups-blog-seo-copy { margin-top:16px; gap:10px; }
  .ups-blog-seo-copy p { line-height:1.72; }
  .ups-blog-categories { position:static; padding:12px 0 16px; }
  .ups-blog-featured-wrap,.ups-blog-newsletter-band,.ups-blog-topics,.ups-blog-category-strategy,.ups-blog-content-plan,.ups-blog-cta { padding:48px 0; }
  .ups-blog-list-wrap { padding:48px 0 56px; }
  .ups-blog-list-title,.ups-blog-newsletter-title,.ups-blog-topics-title,.ups-blog-cta-title { font-size:clamp(28px,8vw,34px); line-height:1.12; letter-spacing:-.8px; }
  .ups-blog-card,.ups-blog-panel,.ups-blog-category-strategy-card,.ups-blog-plan-card,.ups-blog-cta-shell { border-radius:20px; padding:20px; }
  .ups-blog-featured-title { font-size:clamp(24px,7vw,30px); line-height:1.12; }
  .ups-blog-newsletter-row { flex-direction:column; align-items:stretch; }
  @media (min-width: 761px) {
    .ups-blog-title { font-size:clamp(44px,6vw,58px); line-height:1.04; }
    .ups-blog-hero .wrap { padding-top:70px !important; padding-bottom:76px !important; }
    .ups-blog-featured-wrap,.ups-blog-newsletter-band,.ups-blog-topics,.ups-blog-category-strategy,.ups-blog-content-plan,.ups-blog-cta { padding:72px 0; }
    .ups-blog-list-wrap { padding:64px 0 72px; }
    .ups-blog-list-title,.ups-blog-newsletter-title,.ups-blog-topics-title,.ups-blog-cta-title { font-size:clamp(34px,4vw,46px); }
    .ups-blog-newsletter-row { flex-direction:row; align-items:center; }
  }
  @media (min-width: 1051px) {
    .ups-blog-title { font-size:64px; }
    .ups-blog-list-title,.ups-blog-cta-title { font-size:50px; }
  }
</style>

<main class="ups-blog js-ups-blog-root" data-current-category="<?php echo esc_attr($selected_category); ?>" data-current-tags="<?php echo esc_attr(implode(",", $selected_tags)); ?>" data-current-page="<?php echo esc_attr((string) $paged); ?>">
  <section class="ups-blog-hero">
    <div class="ups-blog-hero-topline"></div>
    <div class="wrap" style="padding: 64px 0 92px;">
      <div style="max-width: 920px;">
        <div class="ups-blog-badge">
          <span class="ups-blog-dot"></span>
          Artykuły o reklamach i stronach pisane przez praktyka — nie teoria z podręcznika
        </div>
        <h1 class="ups-blog-title">
          Blog Upsellio — marketing B2B bez owijania w bawełnę.<br />
          <span style="color: var(--teal);">Meta Ads, Google Ads, konwersja i sprzedaż.</span>
        </h1>
        <p class="ups-blog-lead">
          Ten blog powstał z jednego powodu: zbyt wiele firm wydaje pieniądze na marketing, nie rozumiejąc, dlaczego wyniki są takie, jakie są. Znajdziesz tu konkretne artykuły o Meta Ads, Google Ads, stronach, landing pages, pozyskiwaniu klientów i mierzeniu jakości zapytań.
        </p>
        <div class="ups-blog-seo-copy">
          <p>Większość artykułów o marketingu internetowym jest pisana albo zbyt ogólnie, bez liczb i decyzji, albo zbyt technicznie, bez kontekstu biznesowego. Tutaj chodzi o treści, które pomagają właścicielom firm i managerom podejmować lepsze decyzje.</p>
          <p>Jeśli zastanawiasz się, kiedy inwestować w Google Ads zamiast Meta Ads, co blokuje konwersję strony, jak działa remarketing albo jak mierzyć jakość leadów, a nie tylko ich liczbę, jesteś w dobrym miejscu.</p>
        </div>
      </div>

      <form class="ups-blog-search js-ups-blog-search-form" method="get" action="<?php echo esc_url($blog_index_url); ?>">
        <div class="ups-blog-search-field">
          <span aria-hidden="true" style="display:inline-flex;color:var(--text-3);">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
              <circle cx="6.5" cy="6.5" r="5" stroke="currentColor" stroke-width="1.4"></circle>
              <path d="M10.5 10.5L14 14" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"></path>
            </svg>
          </span>
          <input class="js-ups-blog-search-input" type="text" name="s" placeholder="Szukaj artykułu..." value="<?php echo esc_attr($search_term); ?>" />
          <?php if ($selected_category !== "" && $selected_category !== "all") : ?>
            <input type="hidden" name="category" value="<?php echo esc_attr($selected_category); ?>" />
          <?php endif; ?>
          <?php if (!empty($selected_tags)) : ?>
            <input type="hidden" name="tags" value="<?php echo esc_attr(implode(",", $selected_tags)); ?>" />
          <?php endif; ?>
        </div>
        <div class="ups-blog-search-note">Najnowsze wpisy, checklisty, analizy kampanii i odpowiedzi na pytania firm B2B</div>
      </form>
    </div>
  </section>

  <section class="ups-blog-categories">
    <div class="wrap">
      <div class="ups-blog-category-list">
        <?php
        $all_posts_count = (int) wp_count_posts("post")->publish;
        $all_url = remove_query_arg("category");
        if ($search_term !== "") {
            $all_url = add_query_arg("s", $search_term, $all_url);
        }
        if (!empty($selected_tags)) {
            $all_url = add_query_arg("tags", implode(",", $selected_tags), $all_url);
        }
        ?>
        <a
          href="<?php echo esc_url($all_url); ?>"
          data-category=""
          class="ups-blog-category js-ups-blog-category <?php echo $selected_category === "" || $selected_category === "all" ? "active" : ""; ?>"
        >
          Wszystkie
          <span class="ups-blog-category-count"><?php echo esc_html((string) $all_posts_count); ?></span>
        </a>
        <?php foreach ($categories as $category) : ?>
          <?php
          $category_url = add_query_arg(
              [
                  "category" => $category->slug,
                  "tags" => implode(",", $selected_tags),
                  "s" => $search_term,
              ],
              $blog_index_url
          );
          ?>
          <a
            href="<?php echo esc_url($category_url); ?>"
            data-category="<?php echo esc_attr($category->slug); ?>"
            class="ups-blog-category js-ups-blog-category <?php echo $selected_category === $category->slug ? "active" : ""; ?>"
          >
            <?php echo esc_html($category->name); ?>
            <span class="ups-blog-category-count"><?php echo esc_html((string) $category->count); ?></span>
          </a>
        <?php endforeach; ?>
      </div>
      <div class="ups-blog-tag-filter-wrap">
        <div class="ups-blog-tag-filter-label">Tagi:</div>
        <div class="ups-blog-tag-filter-list">
          <?php
          $all_tags_url = remove_query_arg(["tag", "tags"]);
          if ($search_term !== "") {
              $all_tags_url = add_query_arg("s", $search_term, $all_tags_url);
          }
          if ($selected_category !== "") {
              $all_tags_url = add_query_arg("category", $selected_category, $all_tags_url);
          }
          ?>
          <a
            href="<?php echo esc_url($all_tags_url); ?>"
            data-tag=""
            class="ups-blog-category js-ups-blog-tag <?php echo empty($selected_tags) ? "active" : ""; ?>"
          >
            Wszystkie tagi
          </a>
          <?php foreach (array_slice($tags, 0, 12) as $tag) : ?>
            <?php
            $tag_url = add_query_arg(
                [
                    "category" => $selected_category,
                    "tags" => $tag->slug,
                    "s" => $search_term,
                ],
                $blog_index_url
            );
            ?>
            <a
              href="<?php echo esc_url($tag_url); ?>"
              data-tag="<?php echo esc_attr($tag->slug); ?>"
              class="ups-blog-category js-ups-blog-tag <?php echo in_array($tag->slug, $selected_tags, true) ? "active" : ""; ?>"
            >
              #<?php echo esc_html($tag->name); ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="ups-blog-filter-tools">
        <div class="ups-blog-active-filters js-ups-blog-active-filters">
          <?php if ($selected_category !== "") : ?>
            <?php
            $selected_category_obj = get_category_by_slug($selected_category);
            $selected_category_name = $selected_category_obj ? $selected_category_obj->name : $selected_category;
            ?>
            <span class="ups-blog-active-badge">
              Kategoria: <?php echo esc_html($selected_category_name); ?>
              <button type="button" class="ups-blog-active-remove js-ups-blog-remove-category" aria-label="Usuń filtr kategorii">×</button>
            </span>
          <?php endif; ?>
          <?php if (!empty($selected_tags)) : ?>
            <?php foreach ($selected_tags as $selected_tag_slug) : ?>
              <?php
              $selected_tag_obj = get_term_by("slug", $selected_tag_slug, "post_tag");
              $selected_tag_name = $selected_tag_obj ? $selected_tag_obj->name : $selected_tag_slug;
              ?>
              <span class="ups-blog-active-badge">
                Tag: #<?php echo esc_html($selected_tag_name); ?>
                <button type="button" data-tag="<?php echo esc_attr($selected_tag_slug); ?>" class="ups-blog-active-remove js-ups-blog-remove-tag" aria-label="Usuń filtr tagu">×</button>
              </span>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <button type="button" class="ups-blog-clear-filters js-ups-blog-clear-filters">Wyczyść wszystko</button>
      </div>
      <div class="ups-blog-filter-note js-ups-blog-filter-note">
        Możesz wybrać maksymalnie 3 tagi jednocześnie.
      </div>
    </div>
  </section>

  <div class="js-ups-blog-dynamic">
    <?php echo upsellio_render_blog_dynamic_content($selected_category, $selected_tags, $search_term, $paged); ?>
  </div>
  <div class="ups-blog-skeleton js-ups-blog-skeleton" aria-hidden="true">
    <div class="wrap ups-blog-skeleton-grid">
      <?php for ($index = 0; $index < 6; $index++) : ?>
        <div class="ups-blog-skeleton-card">
          <div class="ups-blog-skeleton-bar" style="height: 24px; width: 36%;"></div>
          <div class="ups-blog-skeleton-bar" style="height: 18px; width: 100%; margin-top: 16px;"></div>
          <div class="ups-blog-skeleton-bar" style="height: 18px; width: 85%; margin-top: 10px;"></div>
          <div class="ups-blog-skeleton-bar" style="height: 12px; width: 60%; margin-top: 26px;"></div>
          <div class="ups-blog-skeleton-bar" style="height: 14px; width: 42%; margin-top: 14px;"></div>
        </div>
      <?php endfor; ?>
    </div>
  </div>

  <section class="ups-blog-newsletter-band">
    <div class="wrap">
      <div class="ups-blog-newsletter-inner">
        <div>
          <div class="eyebrow" style="color: var(--teal-dark); margin-bottom: 0;">Newsletter</div>
          <h2 class="ups-blog-newsletter-title">Konkretne materiały o reklamach i sprzedaży — bez spamu, bez ogólników.</h2>
          <p class="ups-blog-newsletter-text">
            Raz na jakiś czas wysyłam materiały, które faktycznie pomagają podejmować lepsze decyzje marketingowe: checklisty, analizy kampanii, wnioski z prowadzonych projektów i odpowiedzi na pytania, które zadają firmy przed inwestycją w reklamy lub nową stronę.
          </p>
          <ul class="ups-blog-newsletter-points">
            <li>Materiały pisane przez praktyka z ponad 10-letnim doświadczeniem w marketingu B2B.</li>
            <li>Bez cotygodniowych maili na siłę — tylko gdy jest coś wartego Twojego czasu.</li>
            <li>Łatwy wypis jednym kliknięciem, w dowolnym momencie.</li>
          </ul>
        </div>
        <div>
          <form class="ups-blog-newsletter-shell" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" method="post" data-upsellio-lead-form="1">
            <input type="hidden" name="action" value="upsellio_submit_lead" />
            <input type="hidden" name="redirect_url" value="<?php echo esc_url($blog_index_url); ?>" />
            <input type="hidden" name="lead_form_origin" value="newsletter-band" />
            <input type="hidden" name="lead_source" value="newsletter-band" />
            <input type="hidden" name="lead_name" value="Newsletter" />
            <input type="hidden" name="lead_message" value="Nowa subskrypcja newslettera (band)." />
            <input type="hidden" name="lead_consent" value="1" />
            <input type="hidden" name="utm_source" data-ups-utm="source" value="" />
            <input type="hidden" name="utm_medium" data-ups-utm="medium" value="" />
            <input type="hidden" name="utm_campaign" data-ups-utm="campaign" value="" />
            <input type="hidden" name="landing_url" data-ups-context="landing" value="" />
            <input type="hidden" name="referrer" data-ups-context="referrer" value="" />
            <input type="text" name="lead_website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;" />
            <?php wp_nonce_field("upsellio_unified_lead_form", "upsellio_lead_form_nonce"); ?>
            <div class="ups-blog-newsletter-row">
              <label class="screen-reader-text" for="ups-blog-newsletter-band-email">Twój adres e-mail</label>
              <input type="email" id="ups-blog-newsletter-band-email" name="lead_email" placeholder="Twój adres e-mail" required />
              <button type="submit">Dołącz do czytelników</button>
            </div>
          </form>
          <div class="ups-blog-newsletter-note">Dołączaj do grona firm, które wolą wiedzę od szumu. Wypis jednym kliknięciem.</div>
        </div>
      </div>
    </div>
  </section>

  <section class="ups-blog-topics">
    <div class="wrap">
      <div class="ups-blog-topics-head">
        <div class="eyebrow" style="margin-bottom: 0;">Przeglądaj tematy</div>
        <h2 class="ups-blog-topics-title">Przeglądaj tematami — znajdź artykuły o tym, co teraz interesuje Cię najbardziej.</h2>
      </div>
      <div class="ups-blog-topics-grid">
        <?php foreach (array_slice($categories, 0, 5) as $topic_category) : ?>
          <?php
          $topic_category_url = add_query_arg(
              [
                  "category" => $topic_category->slug,
                  "tags" => implode(",", $selected_tags),
                  "s" => $search_term,
              ],
              $blog_index_url
          );
          ?>
          <a href="<?php echo esc_url($topic_category_url); ?>" data-category="<?php echo esc_attr($topic_category->slug); ?>" class="ups-blog-topic-card js-ups-blog-category">
            <span class="ups-blog-topic-icon" aria-hidden="true">
              <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                <rect x="3" y="11" width="3.5" height="6" rx="1.75" fill="currentColor"></rect>
                <rect x="8.2" y="7.5" width="3.5" height="9.5" rx="1.75" fill="currentColor"></rect>
                <rect x="13.4" y="4" width="3.5" height="13" rx="1.75" fill="currentColor"></rect>
              </svg>
            </span>
            <div class="ups-blog-topic-name"><?php echo esc_html($topic_category->name); ?></div>
            <div class="ups-blog-topic-count"><?php echo esc_html((string) $topic_category->count); ?> artykułów</div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <?php if (current_user_can("manage_options")) : ?>
  <section class="ups-blog-category-strategy">
    <div class="wrap">
      <div class="ups-blog-topics-head">
        <div class="eyebrow" style="margin-bottom: 0;">Strategia kategorii</div>
        <h2 class="ups-blog-topics-title">Tematy, które porządkują blog i budują kontekst SEO dla całego serwisu.</h2>
      </div>
      <div class="ups-blog-category-strategy-grid">
        <?php foreach ($blog_content_categories as $content_category) : ?>
          <article class="ups-blog-category-strategy-card">
            <strong><?php echo esc_html((string) $content_category["name"]); ?></strong>
            <p><?php echo esc_html((string) $content_category["desc"]); ?></p>
            <small><?php echo esc_html((string) $content_category["keywords"]); ?></small>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="ups-blog-content-plan">
    <div class="wrap">
      <div class="ups-blog-topics-head">
        <div class="eyebrow" style="margin-bottom: 0;">Plan treści</div>
        <h2 class="ups-blog-topics-title">24 tematy artykułów na 6 miesięcy regularnej, eksperckiej komunikacji.</h2>
        <p class="ups-blog-panel-text">Plan równoważy frazy z intencją wyszukiwania, pytania klientów przed współpracą i wpisy wspierające strony usług: Meta Ads, Google Ads, tworzenie stron i pełną ofertę.</p>
      </div>
      <div class="ups-blog-plan-grid">
        <?php foreach ($blog_content_plan as $month_title => $month_topics) : ?>
          <article class="ups-blog-plan-card">
            <strong><?php echo esc_html((string) $month_title); ?></strong>
            <ul class="ups-blog-plan-list">
              <?php foreach ($month_topics as $topic_title) : ?>
                <li><?php echo esc_html((string) $topic_title); ?></li>
              <?php endforeach; ?>
            </ul>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <section class="ups-blog-cta">
    <div class="wrap">
      <div class="ups-blog-cta-shell">
        <div class="eyebrow" style="color: var(--teal-dark); margin-bottom: 0;">CTA pod blogiem</div>
        <h2 class="ups-blog-cta-title">Wolisz, żeby ktoś spojrzał na Twoje kampanie, a nie czytać kolejny artykuł?</h2>
        <p class="ups-blog-cta-text">
          Artykuły dają kontekst i wiedzę. Ale jeśli masz wrażenie, że Twoje kampanie Meta Ads lub Google Ads coś robią, a nie masz pewności czy dobrze, potrzebujesz spojrzeć na konkretne dane, kampanie i ofertę. Bezpłatna rozmowa lub audyt to 30-45 minut, po których wiesz, co blokuje wyniki i który element warto naprawić najpierw.
        </p>
        <div class="ups-blog-cta-actions">
          <a class="ups-blog-btn-primary" href="<?php echo esc_url(home_url("/#kontakt")); ?>">Umów bezpłatną rozmowę</a>
          <a class="ups-blog-btn-secondary" href="<?php echo esc_url(home_url("/marketing-google-ads/")); ?>">Konsultacja Google Ads</a>
          <a class="ups-blog-btn-secondary" href="<?php echo esc_url(home_url("/marketing-meta-ads/")); ?>">Audyt Meta Ads</a>
        </div>
      </div>
    </div>
  </section>
</main>
<?php
$blog_schema_posts = get_posts([
    "post_type" => "post",
    "post_status" => "publish",
    "numberposts" => 10,
    "orderby" => "date",
    "order" => "DESC",
]);
if (!empty($blog_schema_posts)) :
?>
<script type="application/ld+json">
<?php
echo wp_json_encode([
    "@context" => "https://schema.org",
    "@type" => "Blog",
    "name" => "Blog Upsellio",
    "url" => $blog_index_url,
    "description" => "Blog o marketingu B2B, Meta Ads, Google Ads, tworzeniu stron i pozyskiwaniu klientów.",
    "blogPost" => array_map(static function ($schema_post) {
        return [
            "@type" => "BlogPosting",
            "headline" => (string) get_the_title($schema_post),
            "description" => (string) get_the_excerpt($schema_post),
            "url" => (string) get_permalink($schema_post),
            "datePublished" => (string) get_the_date("c", $schema_post),
            "dateModified" => (string) get_the_modified_date("c", $schema_post),
            "author" => [
                "@type" => "Person",
                "name" => (string) get_the_author_meta("display_name", (int) $schema_post->post_author),
            ],
        ];
    }, $blog_schema_posts),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
?>
</script>
<?php endif; ?>
<?php
get_footer();
?>
