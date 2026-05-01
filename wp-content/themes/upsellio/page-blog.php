<?php
/*
Template Name: Upsellio - Blog
Template Post Type: page
*/

if (!defined("ABSPATH")) {
    exit;
}

$blog_index_url = function_exists("upsellio_get_blog_index_url") ? upsellio_get_blog_index_url() : get_permalink();
$selected_category = isset($_GET["category"]) ? sanitize_title(wp_unslash($_GET["category"])) : "";
$paged = max(1, (int) get_query_var("paged"), (int) get_query_var("page"), isset($_GET["paged"]) ? (int) $_GET["paged"] : 1);

$query_args = [
    "post_type" => "post",
    "post_status" => "publish",
    "posts_per_page" => 7,
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
  .bl-art{font-family:"DM Sans",system-ui,sans-serif;color:#0a1410;background:#fafaf7;line-height:1.65}
  .bl-art *,.bl-art *::before,.bl-art *::after{box-sizing:border-box}
  .bl-wrap{width:min(1180px,100% - 64px);margin-inline:auto}
  .bl-eyebrow{display:inline-flex;align-items:center;gap:10px;font-size:11px;font-weight:700;letter-spacing:1.6px;text-transform:uppercase;color:#0d9488;margin-bottom:14px}
  .bl-eyebrow::before{content:"";width:26px;height:2px;background:#0d9488;border-radius:99px}
  .bl-eyebrow-light{color:#5eead4}
  .bl-eyebrow-light::before{background:#5eead4}
  .bl-h1{font-family:"Syne",sans-serif;font-weight:700;font-size:clamp(40px,4.4vw,60px);line-height:1.02;letter-spacing:-1.8px;margin:0 0 20px;max-width:18ch}
  .bl-h2{font-family:"Syne",sans-serif;font-weight:700;font-size:clamp(26px,3vw,40px);line-height:1.05;letter-spacing:-1.4px;margin:0 0 14px;max-width:24ch}
  .bl-h2-light{color:#fff}
  .bl-h3{font-family:"Syne",sans-serif;font-weight:700;font-size:20px;line-height:1.2;letter-spacing:-.5px;margin:0 0 10px}
  .bl-lead{font-size:18px;line-height:1.6;color:#3d3d38;max-width:60ch;margin:0 0 28px}
  .bl-lead-light{color:rgba(255,255,255,.7)}
  .bl-divider{height:1px;background:#e7e7e1;margin:48px 0 32px}
  .bl-hero{padding:96px 0 48px;background:radial-gradient(circle at 90% 0%,rgba(13,148,136,.1),transparent 40%)}
  .bl-section{padding:48px 0 128px}
  .bl-filters{display:flex;flex-wrap:wrap;gap:8px}
  .bl-filters a{padding:10px 18px;border-radius:999px;font-size:13px;font-weight:600;color:#3d3d38;text-decoration:none;border:1px solid #e7e7e1;background:#fff}
  .bl-filters a.is-active{background:#0a1410;color:#fff;border-color:#0a1410}
  .bl-meta{display:flex;align-items:center;gap:8px;font-size:12px;color:#7c7c74;margin-bottom:10px;flex-wrap:wrap}
  .bl-cat{display:inline-flex;font-size:11px;letter-spacing:1.2px;text-transform:uppercase;color:#0f766e;background:#ccfbf1;padding:3px 10px;border-radius:999px;font-weight:700}
  .bl-thumb{position:relative;aspect-ratio:1.6;background:#dff8f4;overflow:hidden;border-radius:14px}
  .bl-thumb img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
  .bl-thumb-stripes{position:absolute;inset:0;background-image:repeating-linear-gradient(135deg,rgba(13,148,136,.12) 0 12px,transparent 12px 24px)}
  .bl-thumb-label{position:absolute;inset:0;display:grid;place-items:center;font-family:ui-monospace,monospace;color:#0f766e;font-size:12px;letter-spacing:1px}
  .bl-feat{display:grid;grid-template-columns:1.1fr 1fr;gap:48px;align-items:center;background:#fff;border:1px solid #e7e7e1;border-radius:24px;overflow:hidden}
  .bl-feat-thumb{position:relative;aspect-ratio:1.4;background:#dff8f4}
  .bl-feat-thumb img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
  .bl-feat-thumb .bl-thumb-stripes{background-image:repeating-linear-gradient(135deg,rgba(13,148,136,.14) 0 14px,transparent 14px 28px)}
  .bl-feat-thumb .bl-thumb-label{font-size:13px}
  .bl-feat-body{padding:40px 40px 40px 0}
  .bl-feat-body p{margin:0 0 22px;font-size:16px;color:#3d3d38;line-height:1.65}
  .bl-link{color:#0d9488;font-weight:700;font-size:15px;text-decoration:none}
  .bl-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
  .bl-card{background:#fff;border:1px solid #e7e7e1;border-radius:18px;overflow:hidden;display:flex;flex-direction:column;transition:.25s ease}
  .bl-card:hover{transform:translateY(-3px);border-color:#99f6e4;box-shadow:0 18px 40px rgba(15,23,42,.06)}
  .bl-card .bl-thumb{border-radius:0;border-bottom:1px solid #e7e7e1}
  .bl-card-body{padding:22px;display:flex;flex-direction:column;flex:1}
  .bl-card p{margin:0 0 18px;font-size:14px;color:#3d3d38;line-height:1.6;flex:1}
  .bl-card-foot{display:flex;justify-content:space-between;align-items:center;padding-top:14px;border-top:1px solid #e7e7e1;font-size:12.5px;color:#7c7c74}
  .bl-card-foot a{color:#0d9488;font-weight:700;text-decoration:none;font-size:13px}
  .bl-pager{display:flex;justify-content:center;align-items:center;margin-top:48px;padding-top:32px;border-top:1px solid #e7e7e1;gap:8px;flex-wrap:wrap}
  .bl-pager a,.bl-pager span{min-width:38px;height:38px;padding:0 12px;border-radius:999px;border:1px solid #e7e7e1;background:#fff;font-size:13px;font-weight:700;color:#0a1410;text-decoration:none;display:inline-flex;align-items:center;justify-content:center}
  .bl-pager .is-current{background:#0a1410;color:#fff;border-color:#0a1410}
  .bl-pager .is-disabled{opacity:.45;pointer-events:none}
  @media (max-width:1050px){.bl-feat{grid-template-columns:1fr}.bl-feat-body{padding:24px}.bl-grid{grid-template-columns:1fr 1fr}}
  @media (max-width:760px){.bl-wrap{width:min(1180px,100% - 24px)}.bl-grid{grid-template-columns:1fr}}
</style>

<main class="bl-art">
  <section class="bl-hero">
    <div class="bl-wrap">
      <div class="bl-eyebrow">Blog</div>
      <h1 class="bl-h1">Praktyczna wiedza dla firm, które chcą więcej leadów.</h1>
      <p class="bl-lead">Artykuły o Google Ads, Meta Ads, sprzedaży B2B, optymalizacji stron i konwersji. Bez teorii — same wnioski z realnych projektów.</p>
      <div class="bl-filters">
        <a class="<?php echo $selected_category === "" ? "is-active" : ""; ?>" href="<?php echo esc_url($blog_index_url); ?>">Wszystkie</a>
        <?php foreach ($categories as $category) : ?>
          <a class="<?php echo $selected_category === $category->slug ? "is-active" : ""; ?>" href="<?php echo esc_url(add_query_arg("category", $category->slug, $blog_index_url)); ?>"><?php echo esc_html($category->name); ?></a>
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
        <article class="bl-feat">
          <div class="bl-feat-thumb">
            <?php if ($featured_img) : ?>
              <img src="<?php echo esc_url($featured_img); ?>" alt="<?php echo esc_attr(get_the_title($featured_id)); ?>" loading="lazy" decoding="async" />
            <?php else : ?>
              <div class="bl-thumb-stripes"></div>
            <?php endif; ?>
          </div>
          <div class="bl-feat-body">
            <div class="bl-meta">
              <span class="bl-cat"><?php echo esc_html($featured_cat_name); ?></span>
              <span>·</span>
              <time><?php echo esc_html(get_the_date("j F Y", $featured_id)); ?></time>
              <span>·</span>
              <span><?php echo esc_html(upsellio_estimated_read_time($featured_id)); ?></span>
            </div>
            <h2 class="bl-h2"><?php echo esc_html(get_the_title($featured_id)); ?></h2>
            <p><?php echo esc_html(get_the_excerpt($featured_id)); ?></p>
            <a class="bl-link" href="<?php echo esc_url(get_permalink($featured_id)); ?>">Czytaj artykuł →</a>
          </div>
        </article>
      <?php endif; ?>

      <div class="bl-divider"></div>

      <div class="bl-grid">
        <?php foreach ($posts as $post_item) : ?>
          <?php
          $post_item_id = (int) $post_item->ID;
          $post_item_cat = get_the_category($post_item_id);
          $post_item_cat_name = !empty($post_item_cat) ? $post_item_cat[0]->name : "Blog";
          $post_item_img = get_the_post_thumbnail_url($post_item_id, "medium_large");
          if (!$post_item_img) {
              $post_item_img = (string) get_post_meta($post_item_id, "_upsellio_featured_image_url", true);
          }
          ?>
          <article class="bl-card">
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
              <p><?php echo esc_html(get_the_excerpt($post_item_id)); ?></p>
              <div class="bl-card-foot">
                <time><?php echo esc_html(get_the_date("j F Y", $post_item_id)); ?></time>
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

</main>

<?php
wp_reset_postdata();
get_footer();
