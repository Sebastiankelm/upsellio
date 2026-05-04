<?php
/*
Template Name: Upsellio - Blog
Template Post Type: page
*/

if (!defined("ABSPATH")) {
    exit;
}

if (!function_exists("upsellio_blog_teaser")) {
    function upsellio_blog_teaser(int $post_id, int $words = 24): string
    {
        $raw = get_the_excerpt($post_id);

        if (!$raw) {
            $raw = wp_strip_all_tags((string) get_post_field("post_content", $post_id));
        }

        $raw = preg_replace("/\s+/", " ", trim(wp_strip_all_tags($raw)));

        return wp_trim_words($raw, $words, "...");
    }
}

$blog_index_url = function_exists("upsellio_get_blog_index_url") ? upsellio_get_blog_index_url() : get_permalink();
$selected_category = isset($_GET["category"]) ? sanitize_title(wp_unslash($_GET["category"])) : "";
$paged = max(1, (int) get_query_var("paged"), (int) get_query_var("page"), isset($_GET["paged"]) ? (int) $_GET["paged"] : 1);

$query_args = [
    "post_type" => "post",
    "post_status" => "publish",
    "posts_per_page" => 10,
    "paged" => $paged,
];

if ($selected_category !== "") {
    $query_args["category_name"] = $selected_category;
}

$blog_query = new WP_Query($query_args);
$categories = get_categories(["hide_empty" => true]);

get_header();
?>
<style>
  .bl-art{font-family:"DM Sans",system-ui,sans-serif;color:var(--text,#0f172a);background:var(--bg,#f8fafc);line-height:1.65}
  .bl-art *,.bl-art *::before,.bl-art *::after{box-sizing:border-box}
  .bl-wrap{width:min(1180px,100% - 64px);margin-inline:auto}
  .bl-eyebrow{display:inline-flex;align-items:center;gap:10px;font-size:11px;font-weight:700;letter-spacing:1.6px;text-transform:uppercase;color:var(--text-muted,#475569);margin-bottom:14px}
  .bl-eyebrow::before{content:"";width:26px;height:2px;background:var(--brand,#0d9488);border-radius:99px}
  .bl-eyebrow-light{color:#5eead4}
  .bl-eyebrow-light::before{background:#5eead4}
  .bl-h1{font-family:"Syne",sans-serif;font-weight:700;font-size:clamp(40px,4.4vw,60px);line-height:1.02;letter-spacing:-1.8px;margin:0 0 20px;max-width:18ch}
  .bl-h2{font-family:"Syne",sans-serif;font-weight:700;font-size:clamp(26px,3vw,40px);line-height:1.05;letter-spacing:-1.4px;margin:0 0 14px;max-width:24ch}
  .bl-h2-light{color:#fff}
  .bl-h3{font-family:"Syne",sans-serif;font-weight:700;font-size:20px;line-height:1.2;letter-spacing:-.5px;margin:0 0 10px}
  .bl-lead{font-size:18px;line-height:1.6;color:var(--text-muted,#475569);max-width:60ch;margin:0 0 28px}
  .bl-lead-light{color:rgba(255,255,255,.7)}
  .bl-divider{height:1px;background:var(--border,#dbe7ea);margin:48px 0 32px}
  .bl-section{padding:48px 0 128px;background:var(--bg,#f8fafc)}
  .bl-filters{display:flex;flex-wrap:wrap;gap:8px}
  .bl-filters a{padding:10px 18px;border-radius:999px;font-size:13px;font-weight:600;color:var(--text-muted,#475569);text-decoration:none;border:1px solid var(--border,#dbe7ea);background:var(--surface,#fff)}
  .bl-filters a.is-active{background:var(--surface-soft,#f1f5f9);color:var(--text,#0f172a);border:1px solid var(--border,#dbe7ea);font-weight:700}
  .bl-filters a:hover{color:var(--brand,#0d9488)}
  .bl-meta{display:flex;align-items:center;gap:8px;font-size:12px;color:var(--text-soft,#64748b);margin-bottom:10px;flex-wrap:wrap}
  .bl-cat{display:inline-flex;font-size:11px;letter-spacing:1.2px;text-transform:uppercase;color:var(--text,#0f172a);background:var(--surface-soft,#f1f5f9);border:1px solid var(--border,#dbe7ea);padding:3px 10px;border-radius:999px;font-weight:700}
  .bl-thumb{position:relative;aspect-ratio:1.6;background:var(--surface-soft,#f1f5f9);overflow:hidden;border-radius:14px}
  .bl-thumb img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
  .bl-thumb-stripes{position:absolute;inset:0;background-image:repeating-linear-gradient(135deg,rgba(17,24,22,.06) 0 12px,transparent 12px 24px)}
  .bl-thumb-label{position:absolute;inset:0;display:grid;place-items:center;font-family:ui-monospace,monospace;color:var(--text-soft,#64748b);font-size:12px;letter-spacing:1px}
  .bl-feat{display:grid;grid-template-columns:1.1fr 1fr;gap:48px;align-items:center;background:var(--surface,#fff);border:1px solid var(--border,#dbe7ea);border-radius:24px;overflow:hidden}
  .bl-feat-thumb{position:relative;aspect-ratio:1.4;background:var(--surface-soft,#f1f5f9)}
  .bl-feat-thumb img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
  .bl-feat-thumb .bl-thumb-stripes{background-image:repeating-linear-gradient(135deg,rgba(17,24,22,.08) 0 14px,transparent 14px 28px)}
  .bl-feat-thumb .bl-thumb-label{font-size:13px}
  .bl-feat-body{padding:40px 40px 40px 0}
  .bl-feat-body p{margin:0 0 22px;font-size:16px;color:var(--text-muted,#475569);line-height:1.65}
  .bl-link{color:var(--brand,#0d9488);font-weight:700;font-size:15px;text-decoration:none}
  .bl-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
  .bl-card{background:var(--surface,#fff);border:1px solid var(--border,#dbe7ea);border-radius:var(--radius,18px);overflow:hidden;display:flex;flex-direction:column;box-shadow:var(--shadow-soft,0 10px 30px rgba(15,23,42,.06))}
  .bl-card .bl-thumb{border-radius:0;border-bottom:1px solid var(--border,#dbe7ea)}
  .bl-card-body{padding:22px;display:flex;flex-direction:column;flex:1}
  .bl-card p{margin:0 0 18px;font-size:14px;color:var(--text-muted,#475569);line-height:1.6;flex:1}
  .bl-card-foot{display:flex;justify-content:space-between;align-items:center;padding-top:14px;border-top:1px solid var(--border,#dbe7ea);font-size:12.5px;color:var(--text-soft,#64748b)}
  .bl-card-foot a{color:var(--brand,#0d9488);font-weight:700;text-decoration:none;font-size:13px}
  .bl-pager{display:flex;justify-content:center;align-items:center;margin-top:48px;padding-top:32px;border-top:1px solid var(--border,#dbe7ea);gap:8px;flex-wrap:wrap}
  .bl-pager a,.bl-pager span{min-width:38px;height:38px;padding:0 12px;border-radius:999px;border:1px solid var(--border,#dbe7ea);background:var(--surface,#fff);font-size:13px;font-weight:700;color:var(--text,#0f172a);text-decoration:none;display:inline-flex;align-items:center;justify-content:center}
  .bl-pager .is-current{background:var(--dark,#0f172a);color:#fff;border-color:var(--dark,#0f172a)}
  .bl-pager .is-disabled{opacity:.45;pointer-events:none}
  @media (max-width:1050px){.bl-feat{grid-template-columns:1fr}.bl-feat-body{padding:24px}.bl-grid{grid-template-columns:1fr 1fr}}
  @media (max-width:760px){.bl-wrap{width:min(1180px,100% - 24px)}.bl-grid{grid-template-columns:1fr}}

  /* BLOG FIX — stable layout */

  .bl-h1 {
    max-width: 12ch;
  }

  .bl-lead {
    max-width: 760px;
  }

  .bl-top {
    padding: 32px 0 18px;
  }

  .bl-top-row {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
  }

  .bl-top h1 {
    margin: 0;
    font-size: 42px;
    font-family: "Syne", sans-serif;
    letter-spacing: -1px;
    font-weight: 700;
    line-height: 1.1;
    color: var(--text, #0f172a);
  }

  .bl-top p {
    margin: 6px 0 0;
    font-size: 15px;
    color: var(--text-muted, #475569);
    max-width: 52ch;
  }

  .bl-top-cta {
    background: var(--brand, #0d9488);
    color: #fff;
    padding: 10px 16px;
    border-radius: 999px;
    text-decoration: none;
    font-weight: 700;
    font-size: 14px;
    white-space: nowrap;
    flex-shrink: 0;
    transition: background 0.15s ease;
  }

  .bl-top-cta:hover {
    background: var(--brand-dark, #0f766e);
    color: #fff;
  }

  .bl-filter-bar {
    position: sticky;
    top: 0;
    z-index: 30;
    padding: 14px 0;
    border-top: 1px solid var(--border, #dbe7ea);
    border-bottom: 1px solid var(--border, #dbe7ea);
    background: var(--bg, #f8fafc);
    box-shadow: 0 1px 0 rgba(15, 23, 42, 0.06);
  }

  .bl-seo {
    padding: 80px 0;
    border-top: 1px solid var(--border, #dbe7ea);
    background: var(--bg-alt, #eef6f7);
  }

  .bl-seo h2 {
    margin: 0 0 16px;
    font-family: "Syne", sans-serif;
    font-size: 32px;
    font-weight: 700;
    letter-spacing: -0.6px;
    color: var(--text, #0f172a);
  }

  .bl-seo p {
    max-width: 760px;
    margin: 0 0 14px;
    color: var(--text-muted, #475569);
    line-height: 1.7;
    font-size: 16px;
  }

  .bl-seo p:last-child {
    margin-bottom: 0;
  }

  .bl-section {
    padding: 42px 0 110px;
    background: var(--bg, #f8fafc);
  }

  .bl-feat {
    display: grid;
    grid-template-columns: 420px minmax(0, 1fr);
    gap: 0;
    align-items: stretch;
    background: var(--surface, #fff);
    border: 1px solid var(--border, #dbe7ea);
    border-radius: 24px;
    overflow: hidden;
    max-height: none;
    box-shadow: var(--shadow-soft, 0 10px 30px rgba(15, 23, 42, 0.06));
  }

  .bl-feat-thumb {
    display: block;
    position: relative;
    min-height: 340px;
    aspect-ratio: auto;
    background: var(--surface-soft, #f1f5f9);
    overflow: hidden;
  }

  .bl-feat-thumb img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .bl-feat-body {
    padding: 42px;
    min-width: 0;
  }

  .bl-feat-body .bl-h2 {
    margin-bottom: 14px;
  }

  .bl-feat-body .bl-h2 a,
  .bl-h3 a {
    color: inherit;
    text-decoration: none;
  }

  .bl-feat-body p {
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .bl-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 22px;
  }

  .bl-card {
    min-width: 0;
  }

  .bl-card .bl-thumb {
    aspect-ratio: 1.45;
  }

  .bl-card p {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 67px;
  }

  @media (max-width: 1050px) {
    .bl-feat {
      grid-template-columns: 1fr;
    }

    .bl-feat-thumb {
      min-height: 280px;
    }

    .bl-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }

  @media (max-width: 760px) {
    .bl-top-row {
      flex-direction: column;
      align-items: stretch;
    }

    .bl-top h1 {
      font-size: clamp(28px, 8vw, 42px);
    }

    .bl-top-cta {
      text-align: center;
      justify-content: center;
      display: inline-flex;
      align-self: flex-start;
    }

    .bl-feat-body {
      padding: 24px;
    }

    .bl-grid {
      grid-template-columns: 1fr;
    }

    .bl-filter-bar {
      overflow-x: auto;
    }

    .bl-filters {
      flex-wrap: nowrap;
      width: max-content;
      padding-bottom: 2px;
    }

    .bl-seo {
      padding: 56px 0;
    }

    .bl-seo h2 {
      font-size: 26px;
    }
  }
</style>

<main class="bl-art">
  <section class="bl-top" aria-label="Blog — nagłówek" data-animate="fade-up">
    <div class="bl-wrap bl-top-row">
      <div>
        <h1>Marketing bez zgadywania</h1>
        <p>
          Google Ads, Meta Ads, SEO i strony, które mają dowozić leady — nie wyglądać.
        </p>
      </div>

      <a href="<?php echo esc_url(home_url("/kontakt/")); ?>" class="bl-top-cta">
        Masz problem? Napisz →
      </a>
    </div>
  </section>

  <section class="bl-filter-bar" aria-label="Filtry kategorii" data-animate="fade">
    <div class="bl-wrap">
      <div class="bl-filters">
        <a class="<?php echo $selected_category === "" ? "is-active" : ""; ?>" href="<?php echo esc_url($blog_index_url); ?>">Wszystkie</a>
        <?php foreach ($categories as $category) : ?>
          <a class="<?php echo $selected_category === $category->slug ? "is-active" : ""; ?>" href="<?php echo esc_url(add_query_arg("category", $category->slug, $blog_index_url)); ?>">
            <?php echo esc_html($category->name); ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="bl-section">
    <div class="bl-wrap">
      <?php
      $posts = $blog_query->posts;
      $featured_post = !empty($posts) ? array_shift($posts) : null;
      ?>
      <?php if ($featured_post) : ?>
        <?php
        $featured_id = (int) $featured_post->ID;
        $featured_cat = get_the_category($featured_id);
        $featured_cat_name = !empty($featured_cat) ? $featured_cat[0]->name : "Blog";
        $featured_img = get_the_post_thumbnail_url($featured_id, "large");
        if (!$featured_img) {
            $featured_img = (string) get_post_meta($featured_id, "_upsellio_featured_image_url", true);
        }
        ?>
        <article class="bl-feat" data-animate="fade-up">
          <a class="bl-feat-thumb" href="<?php echo esc_url(get_permalink($featured_id)); ?>">
            <?php if ($featured_img) : ?>
              <img src="<?php echo esc_url($featured_img); ?>" alt="<?php echo esc_attr(get_the_title($featured_id)); ?>" loading="lazy" decoding="async" />
            <?php else : ?>
              <div class="bl-thumb-stripes"></div>
            <?php endif; ?>
          </a>

          <div class="bl-feat-body">
            <div class="bl-meta">
              <span class="bl-cat"><?php echo esc_html($featured_cat_name); ?></span>
              <span>·</span>
              <time datetime="<?php echo esc_attr(get_the_date("c", $featured_id)); ?>"><?php echo esc_html(get_the_date("j F Y", $featured_id)); ?></time>
              <span>·</span>
              <span><?php echo esc_html(upsellio_estimated_read_time($featured_id)); ?></span>
            </div>

            <h2 class="bl-h2">
              <a href="<?php echo esc_url(get_permalink($featured_id)); ?>">
                <?php echo esc_html(get_the_title($featured_id)); ?>
              </a>
            </h2>

            <p><?php echo esc_html(upsellio_blog_teaser($featured_id, 30)); ?></p>

            <a class="bl-link" href="<?php echo esc_url(get_permalink($featured_id)); ?>">Czytaj artykuł →</a>
          </div>
        </article>
      <?php endif; ?>

      <div class="bl-divider"></div>

      <div class="bl-grid">
        <?php foreach ($posts as $post_index => $post_item) : ?>
          <?php
          $post_item_id = (int) $post_item->ID;
          $post_item_cat = get_the_category($post_item_id);
          $post_item_cat_name = !empty($post_item_cat) ? $post_item_cat[0]->name : "Blog";
          $post_item_img = get_the_post_thumbnail_url($post_item_id, "medium_large");
          if (!$post_item_img) {
              $post_item_img = (string) get_post_meta($post_item_id, "_upsellio_featured_image_url", true);
          }
          ?>
          <article class="bl-card" data-animate="fade-up"<?php echo $post_index > 0 ? ' data-delay="' . (int) min(4, $post_index) . '"' : ""; ?>>
            <div class="bl-thumb">
              <?php if ($post_item_img) : ?>
                <img src="<?php echo esc_url($post_item_img); ?>" alt="<?php echo esc_attr(get_the_title($post_item_id)); ?>" loading="lazy" decoding="async" />
              <?php else : ?>
                <div class="bl-thumb-stripes"></div>
              <?php endif; ?>
            </div>
            <div class="bl-card-body">
              <div class="bl-meta">
                <span class="bl-cat"><?php echo esc_html($post_item_cat_name); ?></span>
                <span>·</span>
                <span><?php echo esc_html(upsellio_estimated_read_time($post_item_id)); ?></span>
              </div>
              <h3 class="bl-h3"><?php echo esc_html(get_the_title($post_item_id)); ?></h3>
              <p><?php echo esc_html(upsellio_blog_teaser($post_item_id, 20)); ?></p>
              <div class="bl-card-foot">
                <time datetime="<?php echo esc_attr(get_the_date("c", $post_item_id)); ?>"><?php echo esc_html(get_the_date("j F Y", $post_item_id)); ?></time>
                <a href="<?php echo esc_url(get_permalink($post_item_id)); ?>">Czytaj →</a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <?php $blog_max_pages = max(1, (int) $blog_query->max_num_pages); ?>
      <?php if ($blog_max_pages > 1) : ?>
        <nav class="bl-pager" aria-label="Paginacja bloga">
          <?php
          $prev_params = array_filter(["paged" => $paged - 1, "category" => $selected_category]);
          $next_params = array_filter(["paged" => $paged + 1, "category" => $selected_category]);
          ?>
          <?php if ($paged > 1) : ?>
            <a href="<?php echo esc_url(add_query_arg($prev_params, $blog_index_url)); ?>" aria-label="Poprzednia strona">‹</a>
          <?php else : ?>
            <span class="is-disabled" aria-hidden="true">‹</span>
          <?php endif; ?>

          <?php for ($i = 1; $i <= $blog_max_pages; $i++) : ?>
            <?php
            $page_params = array_filter(["paged" => $i > 1 ? $i : null, "category" => $selected_category]);
            $page_url = add_query_arg($page_params, $blog_index_url);
            ?>
            <?php if ($i === $paged) : ?>
              <span class="is-current"><?php echo esc_html((string) $i); ?></span>
            <?php else : ?>
              <a href="<?php echo esc_url($page_url); ?>"><?php echo esc_html((string) $i); ?></a>
            <?php endif; ?>
          <?php endfor; ?>

          <?php if ($paged < $blog_max_pages) : ?>
            <a href="<?php echo esc_url(add_query_arg($next_params, $blog_index_url)); ?>" aria-label="Następna strona">›</a>
          <?php else : ?>
            <span class="is-disabled" aria-hidden="true">›</span>
          <?php endif; ?>
        </nav>
      <?php endif; ?>
    </div>
  </section>

  <section class="bl-seo">
    <div class="bl-wrap">
      <h2>O czym jest ten blog?</h2>

      <p>
        Ten blog powstał z jednego powodu: zbyt wiele firm wydaje pieniądze na marketing,
        nie rozumiejąc, dlaczego wyniki są takie, jakie są.
      </p>

      <p>
        Znajdziesz tu konkretne artykuły o Google Ads, Meta Ads, stronach i konwersji —
        pisane z perspektywy sprzedaży, nie teorii.
      </p>
    </div>
  </section>

</main>

<?php
wp_reset_postdata();
get_footer();
