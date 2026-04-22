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
    background: radial-gradient(circle, rgba(29, 158, 117, 0.12), transparent 68%);
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
    background: radial-gradient(circle at top right, rgba(29, 158, 117, 0.12), transparent 40%);
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
    box-shadow: 0 0 0 3px rgba(29, 158, 117, 0.13);
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
    box-shadow: 0 0 0 3px rgba(29, 158, 117, 0.13);
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
</style>

<main class="ups-blog js-ups-blog-root" data-current-category="<?php echo esc_attr($selected_category); ?>" data-current-tags="<?php echo esc_attr(implode(",", $selected_tags)); ?>" data-current-page="<?php echo esc_attr((string) $paged); ?>">
  <section class="ups-blog-hero">
    <div class="ups-blog-hero-topline"></div>
    <div class="wrap" style="padding: 64px 0 92px;">
      <div style="max-width: 920px;">
        <div class="ups-blog-badge">
          <span class="ups-blog-dot"></span>
          Blog o reklamach, sprzedaży i stronach, które mają dowozić wynik
        </div>
        <h1 class="ups-blog-title">
          Blog Upsellio.<br />
          <span style="color: var(--teal);">Konkrety zamiast marketingowego szumu.</span>
        </h1>
        <p class="ups-blog-lead">
          Artykuły o Meta Ads, lead generation, skalowaniu budżetu, landing page'ach i miejscach,
          w których firmy najczęściej tracą wynik. Pisane prosto, ale na poziomie decyzyjnym.
        </p>
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
        <div class="ups-blog-search-note">Najnowsze wpisy, checklisty i analizy praktyczne</div>
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
          <h2 class="ups-blog-newsletter-title">Praktyczne materiały o reklamach i sprzedaży</h2>
          <p class="ups-blog-newsletter-text">
            Raz na jakiś czas — konkretny materiał: checklista, analiza albo artykuł, który pomaga podejmować lepsze decyzje marketingowe. Bez spamu.
          </p>
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
              <input type="email" name="lead_email" placeholder="Twój adres e-mail" required />
              <button type="submit">Zapisz mnie</button>
            </div>
          </form>
          <div class="ups-blog-newsletter-note">Dołącz do grona czytelników. Wypis jednym kliknięciem.</div>
        </div>
      </div>
    </div>
  </section>

  <section class="ups-blog-topics">
    <div class="wrap">
      <div class="ups-blog-topics-head">
        <div class="eyebrow" style="margin-bottom: 0;">Przeglądaj tematy</div>
        <h2 class="ups-blog-topics-title">Kategorie</h2>
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

  <section class="ups-blog-cta">
    <div class="wrap">
      <div class="ups-blog-cta-shell">
        <div class="eyebrow" style="color: var(--teal-dark); margin-bottom: 0;">CTA pod blogiem</div>
        <h2 class="ups-blog-cta-title">Chcesz, żebym zamiast kolejnego artykułu spojrzał na Twoje reklamy?</h2>
        <p class="ups-blog-cta-text">
          Jeśli masz wrażenie, że kampanie „coś robią”, ale nie jesteś pewien czy dobrze — zacznij od krótkiej rozmowy albo darmowego audytu wyników reklam Meta.
        </p>
        <div class="ups-blog-cta-actions">
          <a class="ups-blog-btn-primary" href="<?php echo esc_url(home_url("/#kontakt")); ?>">Umów bezpłatną rozmowę</a>
          <a class="ups-blog-btn-secondary" href="<?php echo esc_url(home_url("/audyt-meta")); ?>">Zobacz audyt Meta Ads</a>
        </div>
      </div>
    </div>
  </section>
</main>
<?php
get_footer();
?>
