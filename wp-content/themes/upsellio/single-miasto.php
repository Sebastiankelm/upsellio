<?php
if (!defined("ABSPATH")) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();
    $postId = get_the_ID();
    $cityName = get_post_meta($postId, "_upsellio_city_name", true) ?: get_the_title();
    $voivodeship = get_post_meta($postId, "_upsellio_city_voivodeship", true) ?: "polska";
    $marketAngle = get_post_meta($postId, "_upsellio_city_market_angle", true) ?: "lokalne firmy";
    $serviceFocus = get_post_meta($postId, "_upsellio_city_service_focus", true) ?: "marketing i strony WWW";
    $cta = get_post_meta($postId, "_upsellio_city_cta", true);
    $faq = get_post_meta($postId, "_upsellio_city_faq", true);
    if (!is_array($faq)) {
        $faq = [];
    }
    $related = upsellio_get_city_nearby_links(get_post_meta($postId, "_upsellio_city_slug", true), 6);
    ?>
    <style>
      .city-wrap{width:min(1180px,calc(100% - 48px));margin:0 auto}
      .city-hero{padding:72px 0 48px;border-bottom:1px solid var(--border,#e6e6e1);background:radial-gradient(circle at top right,rgba(29,158,117,.08),transparent 32%),var(--bg-soft,#f8f8f6)}
      .city-breadcrumbs{font-size:12px;color:var(--text-3,#7c7c74);margin-bottom:14px}
      .city-h1{font-family:var(--font-display, "Syne", sans-serif);font-weight:800;font-size:clamp(36px,5vw,62px);line-height:1.02;letter-spacing:-1.5px}
      .city-lead{margin-top:18px;font-size:18px;line-height:1.8;color:var(--text-2,#3d3d38);max-width:860px}
      .city-meta{display:flex;gap:10px;flex-wrap:wrap;margin-top:22px}
      .city-pill{font-size:12px;border:1px solid var(--border-strong,#c9c9c3);border-radius:999px;padding:6px 12px;background:var(--surface,#fff)}
      .city-main{padding:56px 0 72px;display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:34px}
      .city-content{line-height:1.8;color:#262624;padding:26px;border:1px solid var(--border,#e6e6e1);border-radius:18px;background:var(--surface,#fff)}
      .city-content h2,.city-content h3{font-family:var(--font-display, "Syne", sans-serif);line-height:1.2;color:#111110}
      .city-content h2{font-size:32px;margin:0 0 16px}
      .city-content h3{font-size:23px;margin:28px 0 10px}
      .city-content p{margin:0 0 14px}
      .city-content ul{margin:0 0 16px 20px}
      .city-content li{margin:0 0 8px}
      .city-side-card{border:1px solid var(--border,#e6e6e1);border-radius:18px;padding:22px;background:var(--surface,#fff);position:sticky;top:96px}
      .city-side-title{font-family:var(--font-display, "Syne", sans-serif);font-size:22px;margin-bottom:10px}
      .city-side-list{display:grid;gap:8px;margin-top:14px}
      .city-side-link{font-size:14px;color:#5f5f58}
      .city-side-link:hover{color:var(--teal,#1d9e75)}
      .city-cta{margin-top:22px;padding:16px;border-radius:12px;background:var(--teal-soft,#e8f8f2);border:1px solid var(--teal-line,#c3eddd)}
      .city-cta strong{display:block;margin-bottom:8px}
      .city-btn{display:inline-flex;margin-top:12px;background:var(--teal,#1d9e75);color:#fff;padding:11px 16px;border-radius:10px}
      .city-btn:hover{background:var(--teal-hover,#17885f)}
      .city-faq{margin-top:42px;border-top:1px solid var(--border,#e6e6e1);padding-top:28px}
      .city-faq-item + .city-faq-item{margin-top:16px}
      .city-band{margin-top:26px;padding:24px;border-radius:16px;background:var(--teal-soft,#e8f8f2);border:1px solid var(--teal-line,#c3eddd)}
      .city-band h2{font-size:26px;margin:0 0 8px}
      .city-band p{margin:0;color:#085041}
      @media(max-width:960px){.city-main{grid-template-columns:1fr}.city-side-card{position:static}}
      @media(max-width:760px){.city-wrap{width:min(1180px,calc(100% - 32px))}}
    </style>

    <main>
      <section class="city-hero">
        <div class="city-wrap">
          <nav class="city-breadcrumbs" aria-label="Breadcrumb">
            <a href="<?php echo esc_url(home_url("/")); ?>">Strona główna</a> /
            <a href="<?php echo esc_url(home_url("/miasta/")); ?>">Miasta</a> /
            <span><?php echo esc_html($cityName); ?></span>
          </nav>
          <h1 class="city-h1">Marketing i strony WWW <?php echo esc_html($cityName); ?></h1>
          <p class="city-lead">
            Skuteczne pozyskiwanie klientów w mieście <?php echo esc_html($cityName); ?>:
            kampanie Meta i Google Ads, strony pod konwersję oraz wsparcie sprzedaży B2B i usług.
          </p>
          <div class="city-meta">
            <span class="city-pill">Województwo: <?php echo esc_html($voivodeship); ?></span>
            <span class="city-pill">Specjalizacja: <?php echo esc_html($marketAngle); ?></span>
            <span class="city-pill">Model: <?php echo esc_html($serviceFocus); ?></span>
          </div>
        </div>
      </section>

      <section class="city-main city-wrap">
        <article class="city-content">
          <?php the_content(); ?>

          <div class="city-band">
            <h2>Potrzebujesz planu działań dla <?php echo esc_html($cityName); ?>?</h2>
            <p>W 30 minut pokażę, co warto poprawić najpierw, żeby szybciej podnieść jakość leadów i skuteczność sprzedaży.</p>
          </div>

          <?php if (!empty($faq)) : ?>
            <div class="city-faq">
              <h2>Lokalne FAQ - <?php echo esc_html($cityName); ?></h2>
              <?php foreach ($faq as $item) : ?>
                <div class="city-faq-item">
                  <h3><?php echo esc_html($item["q"]); ?></h3>
                  <p><?php echo esc_html($item["a"]); ?></p>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </article>

        <aside class="city-side-card">
          <div class="city-side-title">Obsługiwane też w pobliżu</div>
          <div class="city-side-list">
            <?php foreach ($related as $item) : ?>
              <a class="city-side-link" href="<?php echo esc_url($item["url"]); ?>">
                <?php echo esc_html("Marketing i strony WWW " . $item["name"]); ?>
              </a>
            <?php endforeach; ?>
          </div>
          <div class="city-cta">
            <strong><?php echo esc_html($cta ?: ("Umów rozmowę dla " . $cityName)); ?></strong>
            <a class="city-btn" href="<?php echo esc_url(home_url("/#kontakt")); ?>">Umów bezpłatną rozmowę</a>
          </div>
        </aside>
      </section>
    </main>
    <?php
endwhile;

get_footer();

