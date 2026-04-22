<?php
if (!defined("ABSPATH")) {
    exit;
}

get_header();

if (have_posts()) :
    while (have_posts()) :
        the_post();
        $post_id = get_the_ID();
        $blog_index_url = upsellio_get_blog_index_url();
        $post_categories = get_the_category($post_id);
        $primary_category = !empty($post_categories) ? $post_categories[0] : null;
        $featured_image = get_the_post_thumbnail_url($post_id, "full");
        $featured_image_id = get_post_thumbnail_id($post_id);
        $fallback_image = get_post_meta($post_id, "_upsellio_featured_image_url", true);
        $hero_image = $featured_image ?: $fallback_image;
        $hero_image_srcset = $featured_image_id ? wp_get_attachment_image_srcset($featured_image_id, "full") : "";
        $hero_image_sizes = $featured_image_id ? "(max-width: 760px) 100vw, (max-width: 1200px) 92vw, 1200px" : "";

        $raw_content = apply_filters("the_content", (string) get_post_field("post_content", $post_id));
        $prepared_content = upsellio_prepare_toc_content($raw_content);
        $toc_items = $prepared_content["toc"];
        $content_html = $prepared_content["content"];
        $raw_post_content = (string) get_post_field("post_content", $post_id);
        $has_inline_contact_shortcode = strpos($raw_post_content, "[upsellio_contact_form]") !== false;
        $lead_magnet = function_exists("upsellio_get_primary_lead_magnet") ? upsellio_get_primary_lead_magnet() : [];

        $related_ids = upsellio_get_related_post_ids($post_id, 3);
        $related_posts = [];
        if (!empty($related_ids)) {
            $related_posts = get_posts([
                "post_type" => "post",
                "post_status" => "publish",
                "post__in" => $related_ids,
                "orderby" => "post__in",
                "posts_per_page" => 3,
            ]);
        }
        ?>
        <style>
          .ups-post {
            min-height: 100vh;
            background: #f6f7f5;
            color: #121514;
          }
          .ups-post-hero {
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid var(--border);
            background: #080d0c;
          }
          .ups-post-hero-overlay {
            position: absolute;
            inset: 0;
            background:
              radial-gradient(circle at top right, rgba(29, 158, 117, 0.24), transparent 32%),
              linear-gradient(180deg, rgba(7, 10, 10, 0.6), rgba(7, 10, 10, 0.82));
          }
          .ups-post-hero-inner {
            position: relative;
            width: min(1200px, calc(100% - 32px));
            margin: 0 auto;
            padding: 64px 0 90px;
            color: #fff;
          }
          .ups-post-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 22px;
            color: rgba(255, 255, 255, 0.72);
            font-size: 14px;
            transition: 0.18s ease;
          }
          .ups-post-back:hover {
            color: #fff;
          }
          .ups-post-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            padding: 7px 12px;
            font-size: 12px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(8px);
          }
          .ups-post-pill-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: var(--teal);
          }
          .ups-post-title {
            max-width: 1020px;
            font-family: var(--font-display);
            font-size: clamp(34px, 10vw, 84px);
            line-height: 0.95;
            letter-spacing: -0.06em;
          }
          .ups-post-title-accent {
            color: var(--teal);
          }
          .ups-post-lead {
            margin-top: 22px;
            max-width: 860px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 18px;
            line-height: 1.7;
          }
          .ups-post-author-row {
            margin-top: 34px;
            border-top: 1px solid rgba(255, 255, 255, 0.12);
            padding-top: 22px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 20px;
            align-items: center;
          }
          .ups-post-author {
            display: flex;
            align-items: center;
            gap: 12px;
          }
          .ups-post-avatar {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.14);
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 15px;
            font-weight: 700;
          }
          .ups-post-author-name {
            font-size: 15px;
            font-weight: 600;
          }
          .ups-post-author-sub {
            margin-top: 2px;
            color: rgba(255, 255, 255, 0.62);
            font-size: 13px;
          }
          .ups-post-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            color: rgba(255, 255, 255, 0.62);
            font-size: 13px;
          }
          .ups-post-main {
            border-bottom: 1px solid var(--border);
            background: #fff;
          }
          .ups-post-main-inner {
            width: min(1200px, calc(100% - 32px));
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr;
            gap: 34px;
            padding: 56px 0 72px;
          }
          .ups-post-sidebar {
            position: static;
            top: 92px;
            display: grid;
            gap: 18px;
            height: fit-content;
          }
          .ups-post-panel {
            border: 1px solid var(--border);
            border-radius: 24px;
            background: #f8f8f6;
            padding: 18px;
            box-shadow: var(--shadow-sm);
          }
          .ups-post-panel-title {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--text-3);
          }
          .ups-post-toc {
            margin-top: 12px;
            display: grid;
            gap: 8px;
          }
          .ups-post-toc a {
            color: var(--text-2);
            font-size: 14px;
            line-height: 1.55;
          }
          .ups-post-toc a:hover {
            color: var(--teal);
          }
          .ups-post-toc a.lvl-h3 {
            padding-left: 10px;
            font-size: 13px;
            color: #7b7b73;
          }
          .ups-post-lead-magnet {
            border-color: var(--teal-line);
            background: var(--teal-soft);
          }
          .ups-post-lead-magnet h3 {
            margin-top: 8px;
            font-family: var(--font-display);
            font-size: 24px;
            line-height: 1.08;
            letter-spacing: -0.04em;
            color: var(--teal-dark);
          }
          .ups-post-lead-magnet p {
            margin-top: 10px;
            color: color-mix(in srgb, var(--teal-dark) 84%, white);
            font-size: 14px;
            line-height: 1.7;
          }
          .ups-post-lead-magnet a {
            margin-top: 12px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 12px;
            background: var(--teal);
            color: #fff;
            padding: 10px 14px;
            font-size: 14px;
            font-weight: 600;
          }
          .ups-post-content-shell {
            min-width: 0;
          }
          .ups-post-highlight {
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 28px;
            background: #fff;
            box-shadow: var(--shadow-sm);
          }
          .ups-post-highlight img {
            width: 100%;
            height: 320px;
            object-fit: cover;
          }
          .ups-post-highlight-copy {
            padding: 26px;
            background: linear-gradient(135deg, #dff5ee, #f7faf9);
          }
          .ups-post-highlight-copy small {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--teal-dark);
          }
          .ups-post-highlight-copy p {
            margin-top: 10px;
            max-width: 760px;
            font-family: var(--font-display);
            font-size: clamp(30px, 2.2vw, 42px);
            line-height: 1.1;
            letter-spacing: -0.05em;
          }
          .ups-post-article {
            margin-top: 22px;
            display: grid;
            gap: 18px;
          }
          .ups-post-section {
            border: 1px solid var(--border);
            border-radius: 28px;
            background: #fff;
            padding: 22px;
            box-shadow: var(--shadow-sm);
          }
          .ups-post-section .ups-inline-links,
          .ups-post-section .ups-inline-contact {
            margin-top: 26px;
          }
          .ups-post-section h2,
          .ups-post-section h3 {
            font-family: var(--font-display);
            color: #121212;
            line-height: 1.06;
            letter-spacing: -0.05em;
          }
          .ups-post-section h2 {
            font-size: clamp(34px, 2.8vw, 50px);
          }
          .ups-post-section h3 {
            margin-top: 20px;
            font-size: clamp(26px, 2.1vw, 36px);
          }
          .ups-post-section p,
          .ups-post-section li {
            margin-top: 14px;
            color: #444740;
            font-size: 17px;
            line-height: 1.8;
          }
          .ups-post-section ul,
          .ups-post-section ol {
            padding-left: 20px;
          }
          .ups-post-section blockquote {
            margin-top: 20px;
            border: 1px solid var(--teal-line);
            border-left: 3px solid var(--teal);
            border-radius: 18px;
            background: var(--teal-soft);
            padding: 18px 20px;
            color: var(--teal-dark);
            font-size: 18px;
            font-weight: 600;
            line-height: 1.7;
          }
          .ups-post-section a {
            color: var(--teal-dark);
            text-decoration: underline;
            text-underline-offset: 3px;
          }
          .ups-post-section img {
            width: 100%;
            height: auto;
            margin-top: 20px;
            border-radius: 20px;
            border: 1px solid var(--border);
          }
          .ups-inline-links {
            margin-top: 18px;
            border: 1px solid var(--border);
            border-radius: 20px;
            background: #f8f8f6;
            padding: 18px;
          }
          .ups-inline-links h3 {
            margin: 0;
            font-size: 20px;
            letter-spacing: -0.03em;
          }
          .ups-inline-links ul {
            margin: 10px 0 0;
            padding: 0;
            list-style: none;
            display: grid;
            gap: 8px;
          }
          .ups-inline-links li {
            margin: 0;
          }
          .ups-inline-links a {
            color: var(--teal-dark);
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
          }
          .ups-inline-contact {
            margin-top: 22px;
            border: 1px solid var(--border);
            border-radius: 22px;
            background: #f8f8f6;
            padding: 22px;
          }
          .ups-inline-contact-head h3 {
            margin: 6px 0 0;
            font-size: 30px;
          }
          .ups-inline-contact-head p {
            margin-top: 8px;
            font-size: 15px;
            line-height: 1.8;
            color: var(--text-2);
          }
          .ups-inline-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--text-3);
          }
          .ups-inline-form {
            margin-top: 16px;
            display: grid;
            gap: 12px;
          }
          .ups-inline-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
          }
          .ups-inline-form label {
            display: grid;
            gap: 6px;
            color: #262a26;
            font-size: 13px;
            font-weight: 600;
          }
          .ups-inline-form input,
          .ups-inline-form textarea {
            border: 1px solid var(--border);
            border-radius: 14px;
            background: #fff;
            padding: 11px 12px;
            min-height: 46px;
            font-size: 16px;
          }
          .ups-inline-form button {
            width: 100%;
            min-height: 46px;
            border: none;
            border-radius: 12px;
            background: #0f0f0f;
            color: #fff;
            padding: 12px 18px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.18s ease;
          }
          .ups-inline-form button:hover {
            background: var(--teal);
          }
          .ups-inline-success,
          .ups-inline-error {
            margin-top: 12px;
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 13px;
            font-weight: 600;
          }
          .ups-inline-success {
            border: 1px solid var(--teal-line);
            background: var(--teal-soft);
            color: var(--teal-dark);
          }
          .ups-inline-error {
            border: 1px solid #edcccc;
            background: #fff2f2;
            color: #b13a3a;
          }
          .ups-post-related {
            border-top: 1px solid var(--border);
            background: #fff;
            padding: 56px 0 72px;
          }
          .ups-post-related-inner {
            width: min(1200px, calc(100% - 32px));
            margin: 0 auto;
          }
          .ups-post-related-grid {
            margin-top: 20px;
            display: grid;
            gap: 16px;
            grid-template-columns: 1fr;
          }
          .ups-post-related-card {
            border: 1px solid var(--border);
            border-radius: 24px;
            overflow: hidden;
            background: #fff;
            box-shadow: var(--shadow-sm);
            transition: 0.2s ease;
          }
          .ups-post-related-card:hover {
            transform: translateY(-4px);
            border-color: var(--teal);
            box-shadow: var(--shadow-md);
          }
          .ups-post-related-card img {
            width: 100%;
            height: 190px;
            object-fit: cover;
          }
          .ups-post-related-copy {
            padding: 20px;
          }
          .ups-post-related-badge {
            display: inline-flex;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: #f8f8f6;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-2);
          }
          .ups-post-related-title {
            margin-top: 10px;
            font-family: var(--font-display);
            font-size: 28px;
            line-height: 1.06;
            letter-spacing: -0.04em;
          }
          .ups-post-related-link {
            margin-top: 12px;
            display: inline-flex;
            align-items: center;
            min-height: 44px;
            color: var(--teal);
            font-size: 14px;
            font-weight: 700;
          }
          @media (max-width: 760px) {
            .ups-post-hero-inner {
              padding: 46px 0 56px;
            }
            .ups-post-title {
              font-size: clamp(30px, 11vw, 44px);
              line-height: 1.02;
              letter-spacing: -0.045em;
            }
            .ups-post-lead {
              margin-top: 16px;
              font-size: 16px;
              line-height: 1.65;
            }
            .ups-post-author-row {
              margin-top: 26px;
              padding-top: 16px;
              grid-template-columns: 1fr;
              gap: 12px;
            }
            .ups-post-main-inner {
              padding: 38px 0 54px;
              gap: 24px;
            }
            .ups-post-panel,
            .ups-post-highlight-copy,
            .ups-post-section,
            .ups-inline-links,
            .ups-inline-contact,
            .ups-post-related-copy {
              padding: 16px;
            }
            .ups-post-highlight img {
              height: 220px;
            }
            .ups-post-highlight-copy p {
              font-size: clamp(24px, 7.2vw, 32px);
              line-height: 1.12;
            }
            .ups-post-section h2 {
              font-size: clamp(26px, 8vw, 34px);
              line-height: 1.12;
            }
            .ups-post-section h3 {
              font-size: clamp(22px, 6.8vw, 28px);
              line-height: 1.14;
            }
            .ups-post-section p,
            .ups-post-section li {
              font-size: 16px;
              line-height: 1.72;
            }
            .ups-post-section blockquote {
              padding: 14px 16px;
              font-size: 16px;
              line-height: 1.65;
            }
            .ups-inline-contact-head h3 {
              font-size: clamp(24px, 8vw, 30px);
            }
            .ups-post-related {
              padding: 42px 0 56px;
            }
            .ups-post-related-title {
              font-size: clamp(24px, 7.5vw, 32px);
            }
          }
          @media (min-width: 761px) {
            .ups-post-hero-inner,
            .ups-post-main-inner,
            .ups-post-related-inner {
              width: min(1200px, calc(100% - 48px));
            }
            .ups-inline-form input,
            .ups-inline-form textarea {
              font-size: 14px;
            }
            .ups-inline-form button {
              width: fit-content;
            }
          }
          @media (min-width: 981px) {
            .ups-post-main-inner {
              grid-template-columns: 250px minmax(0, 1fr);
            }
            .ups-post-sidebar {
              position: sticky;
            }
            .ups-inline-grid {
              grid-template-columns: 1fr 1fr;
            }
            .ups-post-related-grid {
              grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            .ups-post-section {
              padding: 34px;
            }
            .ups-post-author-row {
              grid-template-columns: 1fr auto;
            }
          }
        </style>

        <main class="ups-post">
          <section class="ups-post-hero">
            <div class="ups-post-hero-overlay"></div>
            <div class="ups-post-hero-inner">
              <a class="ups-post-back" href="<?php echo esc_url($blog_index_url); ?>">← Wróć do bloga</a>
              <div class="ups-post-pill">
                <span class="ups-post-pill-dot"></span>
                <?php
                $pill_categories = wp_list_pluck($post_categories, "name");
                echo esc_html(!empty($pill_categories) ? implode(" · ", array_slice($pill_categories, 0, 3)) : "Blog Upsellio");
                ?>
              </div>

              <h1 class="ups-post-title">
                <?php echo esc_html(get_the_title($post_id)); ?>
              </h1>
              <?php if (has_excerpt()) : ?>
                <p class="ups-post-lead"><?php echo esc_html(get_the_excerpt($post_id)); ?></p>
              <?php endif; ?>

              <div class="ups-post-author-row">
                <div class="ups-post-author">
                  <div class="ups-post-avatar">SK</div>
                  <div>
                    <div class="ups-post-author-name">Sebastian Kelm</div>
                    <div class="ups-post-author-sub">Upsellio · praktyk sprzedaży i marketingu</div>
                  </div>
                </div>
                <div class="ups-post-meta">
                  <span><?php echo esc_html(get_the_date("j F Y", $post_id)); ?></span>
                  <span>•</span>
                  <span><?php echo esc_html(upsellio_estimated_read_time($post_id)); ?></span>
                  <span>•</span>
                  <span>Aktualizacja: <?php echo esc_html(get_the_modified_date("j F Y", $post_id)); ?></span>
                </div>
              </div>
            </div>
          </section>

          <section class="ups-post-main">
            <div class="ups-post-main-inner">
              <aside class="ups-post-sidebar">
                <?php if (!empty($toc_items)) : ?>
                  <div class="ups-post-panel">
                    <div class="ups-post-panel-title">Spis treści</div>
                    <nav class="ups-post-toc">
                      <?php foreach ($toc_items as $toc_item) : ?>
                        <a href="#<?php echo esc_attr($toc_item["id"]); ?>" class="<?php echo $toc_item["level"] === "h3" ? "lvl-h3" : ""; ?>">
                          <?php echo esc_html($toc_item["title"]); ?>
                        </a>
                      <?php endforeach; ?>
                    </nav>
                  </div>
                <?php endif; ?>

                <?php if (!empty($lead_magnet["url"])) : ?>
                  <div class="ups-post-panel ups-post-lead-magnet">
                    <div class="ups-post-panel-title" style="color:var(--teal-dark);">Polecany materiał</div>
                    <h3><?php echo esc_html((string) ($lead_magnet["title"] ?? "")); ?></h3>
                    <p><?php echo esc_html((string) ($lead_magnet["excerpt"] ?? "")); ?></p>
                    <a href="<?php echo esc_url((string) $lead_magnet["url"]); ?>">Przejdź do materiału →</a>
                  </div>
                <?php elseif (current_user_can("manage_options")) : ?>
                  <div class="ups-post-panel ups-post-lead-magnet">
                    <div class="ups-post-panel-title" style="color:#b13a3a;">Brak lead magnetu</div>
                    <p>Skonfiguruj i opublikuj co najmniej jeden material typu lead_magnet.</p>
                  </div>
                <?php endif; ?>
              </aside>

              <div class="ups-post-content-shell">
                <?php if ($hero_image) : ?>
                  <div class="ups-post-highlight">
                    <img
                      src="<?php echo esc_url($hero_image); ?>"
                      alt="<?php echo esc_attr(get_the_title($post_id)); ?>"
                      width="1600"
                      height="920"
                      decoding="async"
                      <?php if ($hero_image_srcset !== "") : ?>srcset="<?php echo esc_attr($hero_image_srcset); ?>" sizes="<?php echo esc_attr($hero_image_sizes); ?>"<?php endif; ?>
                    />
                    <div class="ups-post-highlight-copy">
                      <small>Kluczowa myśl artykułu</small>
                      <p>Dobre reklamy nie wystarczą, jeśli reszta procesu sprzedaży nie jest gotowa dowieźć wyniku.</p>
                    </div>
                  </div>
                <?php elseif (current_user_can("manage_options")) : ?>
                  <div class="ups-post-highlight" style="display:block;padding:16px;border:1px solid #edcccc;background:#fff2f2;">
                    Brak obrazu wyrozniajacego i _upsellio_featured_image_url dla tego wpisu.
                  </div>
                <?php endif; ?>

                <article class="ups-post-article">
                  <section class="ups-post-section">
                    <?php echo wp_kses_post($content_html); ?>
                    <?php if (!$has_inline_contact_shortcode) : ?>
                      <?php echo do_shortcode("[upsellio_contact_form]"); ?>
                    <?php endif; ?>
                  </section>
                </article>
              </div>
            </div>
          </section>

          <?php if (!empty($related_posts)) : ?>
            <section class="ups-post-related">
              <div class="ups-post-related-inner">
                <div class="ups-post-panel-title">Czytaj dalej</div>
                <h2 class="ups-post-related-title" style="margin-top:10px;">Powiązane artykuły</h2>
                <div class="ups-post-related-grid">
                  <?php foreach ($related_posts as $related_post) : ?>
                    <?php
                    $related_post_id = (int) $related_post->ID;
                    $related_cats = get_the_category($related_post_id);
                    $related_cat_name = !empty($related_cats) ? $related_cats[0]->name : "Artykuł";
                    $related_img = get_the_post_thumbnail_url($related_post_id, "large");
                    if (!$related_img) {
                        $related_img = get_post_meta($related_post_id, "_upsellio_featured_image_url", true);
                    }
                    if (!$related_img) {
                        $related_img = "https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1200&q=80";
                    }
                    ?>
                    <article class="ups-post-related-card">
                      <img src="<?php echo esc_url($related_img); ?>" alt="<?php echo esc_attr(get_the_title($related_post_id)); ?>" width="1200" height="760" decoding="async" loading="lazy" />
                      <div class="ups-post-related-copy">
                        <div class="ups-post-related-badge"><?php echo esc_html($related_cat_name); ?></div>
                        <h3 class="ups-post-related-title"><?php echo esc_html(get_the_title($related_post_id)); ?></h3>
                        <a class="ups-post-related-link" href="<?php echo esc_url(get_permalink($related_post_id)); ?>">Czytaj dalej →</a>
                      </div>
                    </article>
                  <?php endforeach; ?>
                </div>
              </div>
            </section>
          <?php endif; ?>
        </main>
        <?php
    endwhile;
endif;

get_footer();
