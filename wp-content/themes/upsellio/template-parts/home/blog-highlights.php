<?php

if (!defined("ABSPATH")) {
    exit;
}

$home_blog_posts = new WP_Query([
    "post_type" => "post",
    "posts_per_page" => 3,
    "post_status" => "publish",
    "ignore_sticky_posts" => true,
]);
?>
<section class="section section-border bg-soft" id="blog-highlights">
  <div class="wrap">
    <div class="section-head section-head-wide">
      <div class="eyebrow reveal">Blog</div>
      <h2 class="h2 reveal d1">Najnowsze artykuły o kampaniach i stronach WWW</h2>
    </div>
    <div class="home-post-grid section-grid-gap-lg">
      <?php if ($home_blog_posts->have_posts()) : ?>
        <?php while ($home_blog_posts->have_posts()) : $home_blog_posts->the_post(); ?>
          <article class="home-post-card reveal">
            <a class="home-post-cover" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr(get_the_title()); ?>">
              <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail("large", ["loading" => "lazy", "decoding" => "async"]); ?>
              <?php else : ?>
                <div class="home-post-fallback"><?php echo esc_html((string) get_the_category_list(" · ")); ?></div>
              <?php endif; ?>
            </a>
            <h3 class="h3"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
            <p class="body"><?php echo esc_html(wp_trim_words((string) get_the_excerpt(), 20, "...")); ?></p>
          </article>
        <?php endwhile; ?>
      <?php else : ?>
        <article class="home-post-card reveal">
          <h3 class="h3">Nowe wpisy pojawią się wkrótce</h3>
          <p class="body">Publikujemy praktyczne analizy kampanii Google Ads, Meta Ads i stron pod konwersję.</p>
        </article>
      <?php endif; ?>
      <?php wp_reset_postdata(); ?>
    </div>
    <div class="section-cta-row reveal d2">
      <a href="<?php echo esc_url(home_url("/blog/")); ?>" class="btn btn-primary btn-sm">Zobacz wszystkie wpisy</a>
    </div>
  </div>
</section>
