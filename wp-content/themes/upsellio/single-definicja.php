<?php
if (!defined("ABSPATH")) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();
    $postId = get_the_ID();
    $term = get_post_meta($postId, "_upsellio_definition_term", true) ?: get_the_title($postId);
    $slug = get_post_meta($postId, "_upsellio_definition_slug", true) ?: $post->post_name;
    $mainKeyword = get_post_meta($postId, "_upsellio_definition_main_keyword", true);
    $category = get_post_meta($postId, "_upsellio_definition_category", true) ?: "marketing";
    $difficulty = get_post_meta($postId, "_upsellio_definition_difficulty", true) ?: "sredni";
    $related = upsellio_get_definition_related_links($slug, 6);
    $adjacent = upsellio_get_definition_adjacent_links($slug);
    $faq = get_post_meta($postId, "_upsellio_definition_faq", true);
    if (!is_array($faq)) {
        $faq = [];
    }
    $serviceLinks = get_post_meta($postId, "_upsellio_definition_service_links", true);
    if (!is_array($serviceLinks)) {
        $serviceLinks = [];
    }
    ?>
    <style>
      .definition-wrap{width:min(1140px,calc(100% - 32px));margin:0 auto}
      .definition-hero{padding:72px 0 34px;border-bottom:1px solid #e6e6e1;background:#f8f8f6}
      .definition-breadcrumbs{font-size:12px;color:#6f6f67;margin-bottom:14px}
      .definition-title{font-family:Syne,sans-serif;font-size:clamp(34px,5vw,56px);line-height:1.05;letter-spacing:-1px}
      .definition-lead{margin-top:14px;max-width:860px;font-size:18px;line-height:1.75;color:#3d3d38}
      .definition-pills{display:flex;flex-wrap:wrap;gap:10px;margin-top:20px}
      .definition-pill{font-size:12px;border:1px solid #c9c9c3;border-radius:999px;background:#fff;padding:7px 12px}
      .definition-main{padding:46px 0 60px;display:grid;grid-template-columns:1fr;gap:34px}
      .definition-content{line-height:1.8;color:#262624}
      .definition-content h2,.definition-content h3{font-family:Syne,sans-serif;color:#111110;line-height:1.2}
      .definition-content h2{font-size:33px;margin:0 0 14px}
      .definition-content h3{font-size:22px;margin:24px 0 8px}
      .definition-content p{margin:0 0 14px}
      .definition-content ul{margin:0 0 16px 20px}
      .definition-content li{margin:0 0 8px}
      .definition-content a{color:#1d9e75}
      .definition-content a:hover{text-decoration:underline}
      .definition-side{position:static;display:grid;gap:16px;height:max-content}
      .definition-card{border:1px solid #e6e6e1;border-radius:14px;background:#fff;padding:18px}
      .definition-card-title{font-family:Syne,sans-serif;font-size:22px;margin-bottom:10px}
      .definition-list{display:grid;gap:8px}
      .definition-list a{font-size:14px;color:#5f5f58}
      .definition-list a:hover{color:#1d9e75}
      .definition-adjacent{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:26px;padding-top:18px;border-top:1px solid #e6e6e1}
      .definition-adjacent a{display:block;border:1px solid #e6e6e1;border-radius:12px;padding:12px;min-width:220px;color:#111110}
      .definition-adjacent small{display:block;font-size:12px;color:#6f6f67;margin-bottom:6px}
      .definition-faq{margin-top:28px;padding-top:24px;border-top:1px solid #e6e6e1}
      .definition-faq-item + .definition-faq-item{margin-top:14px}
      @media(min-width:981px){.definition-wrap{width:min(1140px,calc(100% - 40px))}.definition-main{grid-template-columns:minmax(0,1fr) 320px}.definition-side{position:sticky}}
    </style>

    <section class="definition-hero">
      <div class="definition-wrap">
        <div class="definition-breadcrumbs">
          <a href="<?php echo esc_url(home_url("/")); ?>">Strona glowna</a> /
          <a href="<?php echo esc_url(home_url("/definicje/")); ?>">Definicje</a> /
          <span><?php echo esc_html($term); ?></span>
        </div>
        <h1 class="definition-title"><?php echo esc_html($term); ?></h1>
        <p class="definition-lead">
          Wyjasnienie pojecia <?php echo esc_html($term); ?> wraz z praktycznym zastosowaniem w SEO, kampaniach reklamowych i optymalizacji konwersji.
        </p>
        <div class="definition-pills">
          <span class="definition-pill">Kategoria: <?php echo esc_html($category); ?></span>
          <span class="definition-pill">Poziom: <?php echo esc_html($difficulty); ?></span>
          <?php if ($mainKeyword) : ?>
            <span class="definition-pill">Fraza: <?php echo esc_html($mainKeyword); ?></span>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <section class="definition-main definition-wrap">
      <article class="definition-content">
        <?php the_content(); ?>

        <?php if (!empty($faq)) : ?>
          <div class="definition-faq">
            <h2>Dodatkowe FAQ</h2>
            <?php foreach ($faq as $item) : ?>
              <div class="definition-faq-item">
                <h3><?php echo esc_html($item["q"]); ?></h3>
                <p><?php echo esc_html($item["a"]); ?></p>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div class="definition-adjacent">
          <?php if (!empty($adjacent["prev"])) : ?>
            <a href="<?php echo esc_url($adjacent["prev"]["url"]); ?>">
              <small>Poprzednia definicja</small>
              <?php echo esc_html($adjacent["prev"]["name"]); ?>
            </a>
          <?php endif; ?>
          <?php if (!empty($adjacent["next"])) : ?>
            <a href="<?php echo esc_url($adjacent["next"]["url"]); ?>">
              <small>Nastepna definicja</small>
              <?php echo esc_html($adjacent["next"]["name"]); ?>
            </a>
          <?php endif; ?>
        </div>
      </article>

      <aside class="definition-side">
        <div class="definition-card">
          <div class="definition-card-title">Powiazane definicje</div>
          <div class="definition-list">
            <?php foreach ($related as $relatedItem) : ?>
              <a href="<?php echo esc_url($relatedItem["url"]); ?>"><?php echo esc_html($relatedItem["name"]); ?></a>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="definition-card">
          <div class="definition-card-title">Dalsze kroki</div>
          <div class="definition-list">
            <?php foreach ($serviceLinks as $relative) :
                $url = home_url($relative);
                $label = $relative === "/#kontakt" ? "Umow rozmowe" : ($relative === "/#uslugi" ? "Zobacz uslugi" : "Sprawdz miasta obslugi");
                ?>
              <a href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a>
            <?php endforeach; ?>
            <a href="<?php echo esc_url(home_url("/definicje/")); ?>">Powrot do wszystkich definicji</a>
          </div>
        </div>
      </aside>
    </section>
    <?php
endwhile;

get_footer();

