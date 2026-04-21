<?php
if (!defined("ABSPATH")) {
    exit;
}

function upsellio_estimated_read_time($post_id)
{
    $content = wp_strip_all_tags(get_post_field("post_content", $post_id));
    $word_count = str_word_count($content);
    $minutes = max(1, (int) ceil($word_count / 220));

    return sprintf(__("%d min czytania", "upsellio"), $minutes);
}

$paged = max(1, (int) get_query_var("paged"), (int) get_query_var("page"));
$selected_category = isset($_GET["category"]) ? sanitize_title(wp_unslash($_GET["category"])) : "";
$search_term = isset($_GET["s"]) ? sanitize_text_field(wp_unslash($_GET["s"])) : "";
$blog_page_id = (int) get_option("page_for_posts");
$blog_index_url = $blog_page_id ? get_permalink($blog_page_id) : home_url("/");
if (!$blog_index_url) {
    $blog_index_url = home_url("/");
}

$query_args = [
    "post_type" => "post",
    "post_status" => "publish",
    "posts_per_page" => 7,
    "paged" => $paged,
];

if ($selected_category !== "" && $selected_category !== "all") {
    $query_args["category_name"] = $selected_category;
}

if ($search_term !== "") {
    $query_args["s"] = $search_term;
}

$blog_query = new WP_Query($query_args);
$posts = $blog_query->posts;
$featured_post = $posts ? $posts[0] : null;
$regular_posts = count($posts) > 1 ? array_slice($posts, 1) : [];
$categories = get_categories(["hide_empty" => true]);

get_header();
?>
<style>
  .ups-blog {
    border-bottom: 1px solid var(--border);
    background: var(--bg);
  }
  .ups-blog-hero {
    border-bottom: 1px solid var(--border);
    background: linear-gradient(180deg, rgba(29, 158, 117, 0.06), rgba(255, 255, 255, 0) 52%);
  }
  .ups-blog-title {
    font-family: var(--font-display);
    font-size: clamp(38px, 5vw, 64px);
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
    background: var(--surface);
    padding: 8px 14px;
    font-size: 12px;
    font-weight: 500;
    color: var(--text-2);
  }
  .ups-blog-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: var(--teal);
  }
  .ups-blog-lead {
    margin-top: var(--sp-3);
    max-width: 780px;
    font-size: 18px;
    line-height: 1.8;
    color: var(--text-2);
  }
  .ups-blog-search {
    margin-top: var(--sp-5);
    display: grid;
    gap: 12px;
    grid-template-columns: minmax(0, 1fr) 320px;
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
  }
  .ups-blog-search-field input {
    width: 100%;
    border: none;
    outline: none;
    background: transparent;
    color: var(--text);
    font-size: 15px;
  }
  .ups-blog-search-note {
    color: var(--text-2);
    font-size: 14px;
  }
  .ups-blog-categories {
    border-bottom: 1px solid var(--border);
    padding: 28px 0;
  }
  .ups-blog-category-list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
  }
  .ups-blog-category {
    border: 1px solid var(--border);
    border-radius: var(--r-pill);
    padding: 8px 14px;
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
    grid-template-columns: 1.25fr 0.75fr;
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
    grid-template-columns: 1.05fr 0.95fr;
  }
  .ups-blog-featured-cover {
    position: relative;
    min-height: 320px;
    background: linear-gradient(135deg, #dff5ee, #f7faf9);
    padding: 32px;
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
    padding: 32px;
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
  .ups-blog-btn-primary,
  .ups-blog-btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--r-md);
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
    border: 1px solid var(--border);
    border-radius: 16px;
    background: var(--bg-soft);
    padding: 12px;
  }
  .ups-blog-newsletter input {
    width: 100%;
    border: 1px solid var(--border);
    border-radius: 12px;
    background: var(--surface);
    padding: 12px;
    outline: none;
  }
  .ups-blog-newsletter button {
    margin-top: 8px;
    width: 100%;
    border: none;
    border-radius: 12px;
    background: var(--text);
    color: #fff;
    padding: 12px;
    font-size: 14px;
    cursor: pointer;
    transition: 0.18s ease;
  }
  .ups-blog-newsletter button:hover {
    background: var(--teal);
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
    padding: 52px 0 64px;
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
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
  .ups-blog-card {
    display: flex;
    flex-direction: column;
    border: 1px solid var(--border);
    border-radius: 24px;
    background: var(--surface);
    padding: 24px;
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
    padding: var(--sp-8) 0;
  }
  .ups-blog-cta-shell {
    border: 1px solid var(--teal-line);
    border-radius: var(--r-xl);
    background: var(--teal-soft);
    padding: 34px;
  }
  .ups-blog-cta-title {
    margin-top: 10px;
    max-width: 860px;
    font-family: var(--font-display);
    font-size: clamp(34px, 3.2vw, 50px);
    line-height: 1.05;
    letter-spacing: -1px;
    color: var(--teal-dark);
  }
  .ups-blog-cta-text {
    margin-top: 14px;
    max-width: 850px;
    color: color-mix(in srgb, var(--teal-dark) 82%, white);
    font-size: 16px;
    line-height: 1.8;
  }
  .ups-blog-cta-actions {
    margin-top: var(--sp-3);
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
  }
  .ups-blog-empty {
    border: 1px solid var(--border);
    border-radius: var(--r-lg);
    padding: 22px;
    color: var(--text-2);
    background: var(--surface);
  }
  @media (max-width: 1050px) {
    .ups-blog-featured-grid,
    .ups-blog-featured-main {
      grid-template-columns: 1fr;
    }
    .ups-blog-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }
  @media (max-width: 760px) {
    .ups-blog-search {
      grid-template-columns: 1fr;
    }
    .ups-blog-grid {
      grid-template-columns: 1fr;
    }
    .ups-blog-list-meta {
      display: none;
    }
    .ups-blog-featured-cover,
    .ups-blog-featured-text {
      padding: 24px;
    }
    .ups-blog-cta-shell {
      padding: 24px;
    }
  }
</style>

<main class="ups-blog">
  <section class="ups-blog-hero">
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

      <form class="ups-blog-search" method="get" action="<?php echo esc_url($blog_index_url); ?>">
        <div class="ups-blog-search-field">
          <span aria-hidden="true" style="font-size: 14px; color: var(--text-3);">🔎</span>
          <input type="text" name="s" placeholder="Szukaj artykułu..." value="<?php echo esc_attr($search_term); ?>" />
          <?php if ($selected_category !== "" && $selected_category !== "all") : ?>
            <input type="hidden" name="category" value="<?php echo esc_attr($selected_category); ?>" />
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
        $all_url = remove_query_arg("category");
        $all_url = add_query_arg("s", $search_term, $all_url);
        ?>
        <a href="<?php echo esc_url($all_url); ?>" class="ups-blog-category <?php echo $selected_category === "" || $selected_category === "all" ? "active" : ""; ?>">
          Wszystkie
        </a>
        <?php foreach ($categories as $category) : ?>
          <?php
          $category_url = add_query_arg(
              [
                  "category" => $category->slug,
                  "s" => $search_term,
              ],
              $blog_index_url
          );
          ?>
          <a
            href="<?php echo esc_url($category_url); ?>"
            class="ups-blog-category <?php echo $selected_category === $category->slug ? "active" : ""; ?>"
          >
            <?php echo esc_html($category->name); ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="ups-blog-featured-wrap">
    <div class="wrap ups-blog-featured-grid">
      <?php if ($featured_post) : ?>
        <?php
        $featured_categories = get_the_category($featured_post->ID);
        $featured_category = !empty($featured_categories) ? $featured_categories[0] : null;
        ?>
        <article class="ups-blog-featured-card">
          <div class="ups-blog-featured-main">
            <div class="ups-blog-featured-cover">
              <div class="ups-blog-featured-content">
                <div class="ups-blog-featured-label">Wyróżniony wpis</div>
                <div class="ups-blog-featured-title-shell">
                  <?php if ($featured_category) : ?>
                    <div class="ups-blog-featured-category"><?php echo esc_html($featured_category->name); ?></div>
                  <?php endif; ?>
                  <h2 class="ups-blog-featured-title"><?php echo esc_html(get_the_title($featured_post)); ?></h2>
                </div>
              </div>
            </div>
            <div class="ups-blog-featured-text">
              <div>
                <div class="ups-blog-featured-meta">
                  <?php echo esc_html(get_the_date("j F Y", $featured_post)); ?> · <?php echo esc_html(upsellio_estimated_read_time($featured_post->ID)); ?>
                </div>
                <p class="ups-blog-featured-excerpt"><?php echo esc_html(get_the_excerpt($featured_post)); ?></p>
              </div>
              <div class="ups-blog-actions">
                <a href="<?php echo esc_url(get_permalink($featured_post)); ?>" class="ups-blog-btn-primary">Czytaj artykuł →</a>
                <?php if ($featured_category) : ?>
                  <a href="<?php echo esc_url(get_category_link($featured_category)); ?>" class="ups-blog-btn-secondary">
                    Zobacz wszystkie <?php echo esc_html($featured_category->name); ?>
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </article>
      <?php else : ?>
        <div class="ups-blog-empty">
          Brak wpisów do wyświetlenia. Dodaj pierwszy artykuł, a sekcja wyróżnionego wpisu pojawi się automatycznie.
        </div>
      <?php endif; ?>

      <aside class="ups-blog-side">
        <div class="ups-blog-panel">
          <div class="eyebrow" style="margin-bottom: 0;">Newsletter / lead magnet</div>
          <h3 class="ups-blog-panel-title">Chcesz praktyczne materiały o reklamach i sprzedaży?</h3>
          <p class="ups-blog-panel-text">
            Raz na jakiś czas wyślę Ci konkretny materiał: checklistę, analizę albo wpis, który pomaga podejmować lepsze decyzje marketingowe.
          </p>
          <form class="ups-blog-newsletter" action="#" method="post">
            <input type="email" placeholder="Twój e-mail" />
            <button type="submit">Zapisz mnie</button>
          </form>
        </div>

        <div class="ups-blog-panel">
          <div class="eyebrow" style="margin-bottom: 0;">Popularne tematy</div>
          <div class="ups-blog-tags">
            <?php foreach (array_slice($categories, 0, 8) as $tag_category) : ?>
              <span class="ups-blog-tag"><?php echo esc_html($tag_category->name); ?></span>
            <?php endforeach; ?>
          </div>
        </div>
      </aside>
    </div>
  </section>

  <section>
    <div class="wrap ups-blog-list-wrap">
      <div class="ups-blog-list-head">
        <div>
          <div class="eyebrow" style="margin-bottom: 0;">Najnowsze wpisy</div>
          <h2 class="ups-blog-list-title">Wszystkie artykuły</h2>
        </div>
        <div class="ups-blog-list-meta">
          <?php echo esc_html((string) $blog_query->found_posts); ?> wpisów · sortowanie: najnowsze
        </div>
      </div>

      <?php if (!empty($regular_posts)) : ?>
        <div class="ups-blog-grid">
          <?php foreach ($regular_posts as $post_item) : ?>
            <?php
            $post_categories = get_the_category($post_item->ID);
            $post_category_name = !empty($post_categories) ? $post_categories[0]->name : "Artykuł";
            ?>
            <article class="ups-blog-card">
              <div class="ups-blog-card-category"><?php echo esc_html($post_category_name); ?></div>
              <h3 class="ups-blog-card-title"><?php echo esc_html(get_the_title($post_item)); ?></h3>
              <p class="ups-blog-card-excerpt"><?php echo esc_html(get_the_excerpt($post_item)); ?></p>
              <div class="ups-blog-card-footer">
                <div class="ups-blog-card-meta">
                  <?php echo esc_html(upsellio_estimated_read_time($post_item->ID)); ?> · <?php echo esc_html(get_the_date("j F Y", $post_item)); ?>
                </div>
                <a class="ups-blog-card-link" href="<?php echo esc_url(get_permalink($post_item)); ?>">Czytaj dalej →</a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php elseif (!$featured_post) : ?>
        <div class="ups-blog-empty">Nie znaleziono wpisów pasujących do aktualnego filtrowania.</div>
      <?php endif; ?>

      <?php
      $pagination = paginate_links([
          "base" => str_replace(999999999, "%#%", esc_url(get_pagenum_link(999999999))),
          "format" => "?paged=%#%",
          "current" => $paged,
          "total" => $blog_query->max_num_pages,
          "type" => "array",
          "prev_text" => "← Poprzednia",
          "next_text" => "Następna →",
      ]);
      ?>
      <?php if (!empty($pagination)) : ?>
        <div class="ups-blog-pagination">
          <?php foreach ($pagination as $page_link) : ?>
            <?php
            $is_current = strpos($page_link, "current") !== false;
            $class_name = $is_current ? "ups-blog-page-link current" : "ups-blog-page-link";
            ?>
            <span class="<?php echo esc_attr($class_name); ?>"><?php echo wp_kses_post($page_link); ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
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
wp_reset_postdata();
get_footer();
?>
