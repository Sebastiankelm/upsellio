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
        $hero_img_meta = $featured_image_id ? wp_get_attachment_metadata($featured_image_id) : [];
        $hero_w = (int) ($hero_img_meta["width"] ?? 1200);
        $hero_h = (int) ($hero_img_meta["height"] ?? 630);
        if ($hero_w <= 0) {
            $hero_w = 1200;
        }
        if ($hero_h <= 0) {
            $hero_h = 630;
        }

        $raw_post_content = (string) get_post_field("post_content", $post_id);
        $content_without_inline_toc = (string) preg_replace(
            '/<div[^>]*class="ups-article-toc"[^>]*>.*?<\/div>/is',
            "",
            $raw_post_content
        );
        /* Jeden wspólny formularz z sekcji #kontakt — nie duplikuj w treści (shortcode tylko znacznik w edytorze / SEO) */
        $content_for_the_content = (string) preg_replace(
            '/<!--\s*wp:shortcode\s*-->\s*\[\s*upsellio_contact_form(?:\s[^\]]*)?\]\s*<!--\s*\/wp:shortcode\s*-->/is',
            "",
            $content_without_inline_toc
        );
        $content_for_the_content = (string) preg_replace(
            '/\[\s*upsellio_contact_form(?:\s[^\]]*)?\]/i',
            "",
            $content_for_the_content
        );
        $raw_content = apply_filters("the_content", $content_for_the_content);
        $prepared_content = upsellio_prepare_toc_content($raw_content);
        $toc_items = $prepared_content["toc"];
        $content_html = $prepared_content["content"];
        $has_upsellio_content_shortcodes = strpos($raw_post_content, "[upsellio_internal_links]") !== false;
        $lead_magnet = function_exists("upsellio_get_primary_lead_magnet") ? upsellio_get_primary_lead_magnet() : [];
        $offer_url = function_exists("upsellio_get_offer_page_url") ? (string) upsellio_get_offer_page_url() : "";
        $offer_form_url = $offer_url !== "" ? trailingslashit($offer_url) . "#formularz-oferta" : "";
        $contact_phone = function_exists("upsellio_get_contact_phone") ? (string) upsellio_get_contact_phone() : "+48 575 522 595";
        $contact_email = "kontakt@upsellio.pl";

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
          .sp-art{font-family:"DM Sans",system-ui,sans-serif;color:var(--text,#111816);background:var(--bg,#f7f6f1);line-height:1.7}
          .sp-art *,.sp-art *::before,.sp-art *::after{box-sizing:border-box}
          .sp-wrap{width:min(1180px,100% - 64px);margin-inline:auto}
          .sp-wrap-narrow{width:min(880px,100% - 64px);margin-inline:auto}
          .sp-eyebrow{display:inline-flex;align-items:center;gap:10px;font-size:11px;font-weight:700;letter-spacing:1.6px;text-transform:uppercase;color:var(--text-muted,#555f5b);margin-bottom:14px}
          .sp-eyebrow::before{content:"";width:26px;height:2px;background:var(--brand,#0d9488);border-radius:99px}
          .sp-h1{font-family:"Syne",sans-serif;font-weight:700;font-size:clamp(36px,4.2vw,56px);line-height:1.05;letter-spacing:-1.6px;margin:0 0 20px;max-width:24ch}
          .sp-h2{font-family:"Syne",sans-serif;font-weight:700;font-size:clamp(24px,2.6vw,32px);line-height:1.15;letter-spacing:-1px;margin:48px 0 16px}
          .sp-lead{font-size:19px;line-height:1.6;color:var(--text-muted,#555f5b);max-width:60ch;margin:0 0 24px}
          .sp-divider{height:1px;background:var(--border,#dedbd2);margin:32px 0 48px}
          .sp-sec-head{max-width:780px}
          .sp-crumbs{padding:32px 0 0;font-size:13px;color:#7c7c74}
          .sp-crumbs a{color:#7c7c74;text-decoration:none;margin-right:8px}
          .sp-crumbs a:hover{color:var(--brand,#0d9488)}
          .sp-crumbs span{margin-right:8px;color:#c4c4bd}
          .sp-head{padding:32px 0 48px}
          .sp-author{display:flex;align-items:center;gap:14px;margin-top:24px;padding:18px 0;border-top:1px solid var(--border,#dedbd2);border-bottom:1px solid var(--border,#dedbd2)}
          .sp-avatar{width:40px;height:40px;border-radius:50%;background:var(--surface-soft,#f0eee6);border:1px solid var(--border,#dedbd2);display:grid;place-items:center;font-family:"Syne",sans-serif;color:var(--text,#111816);font-weight:800;font-size:14px}
          .sp-avatar.lg{width:56px;height:56px;font-size:18px;flex:0 0 56px}
          .sp-author strong{display:block;font-family:"Syne",sans-serif;font-size:15px;font-weight:700}
          .sp-author .sp-meta-line{display:block;font-size:12.5px;color:#7c7c74;margin-top:1px}
          .sp-share{margin-left:auto;display:flex;gap:8px}
          .sp-share a{width:34px;height:34px;border-radius:50%;border:1px solid var(--border,#dedbd2);background:var(--surface,#fff);display:grid;place-items:center;color:var(--text,#111816);text-decoration:none;font-size:13px;font-weight:700}
          .sp-cover{padding-bottom:64px}
          .sp-cover-img{position:relative;aspect-ratio:2.4;background:var(--surface-soft,#f0eee6);border-radius:24px;overflow:hidden}
          .sp-cover-img img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
          .sp-thumb-stripes{position:absolute;inset:0;background-image:repeating-linear-gradient(135deg,rgba(17,24,22,.06) 0 14px,transparent 14px 28px)}
          .sp-thumb-label{position:absolute;inset:0;display:grid;place-items:center;font-family:ui-monospace,monospace;color:var(--text-soft,#7a827e);font-size:13px;letter-spacing:1px}
          .sp-body{padding:0 0 96px}
          .sp-grid{display:grid;grid-template-columns:240px 1fr;gap:64px;align-items:start;width:min(1100px,100% - 64px)}
          .sp-toc{position:sticky;top:32px;align-self:start}
          .sp-toc-head{font-size:11px;font-weight:700;letter-spacing:1.4px;text-transform:uppercase;color:#7c7c74;margin-bottom:14px}
          .sp-toc ol{list-style:none;padding:0;margin:0;display:grid;gap:6px;border-left:1px solid var(--border,#dedbd2);padding-left:14px}
          .sp-toc ol li a{display:block;padding:6px 0;color:#3d3d38;text-decoration:none;font-size:13.5px;line-height:1.45}
          .sp-toc ol li a:hover{color:var(--brand,#0d9488)}
          .sp-toc ol li.is-active{margin-left:-15px;padding-left:14px;border-left:2px solid var(--brand,#0d9488)}
          .sp-toc ol li.is-active a{color:var(--text,#111816);font-weight:600}
          .sp-toc-cta{margin-top:24px;padding:18px;background:var(--dark,#071311);color:#fff;border-radius:14px}
          .sp-toc-cta strong{display:block;font-family:"Syne",sans-serif;font-size:15px;font-weight:700}
          .sp-toc-cta p{margin:6px 0 12px;font-size:13px;color:rgba(255,255,255,.7);line-height:1.45}
          .sp-toc-cta a{color:#5eead4;font-weight:700;font-size:13px;text-decoration:none}
          .sp-prose p{margin:0 0 18px;font-size:16.5px;color:#262625;line-height:1.75}
          .sp-prose ul,.sp-prose ol{margin:0 0 24px;padding-left:24px}
          .sp-prose li{font-size:16px;color:#262625;line-height:1.7;margin-bottom:6px}
          .sp-prose ul li::marker{color:var(--text-muted,#555f5b)}
          .sp-prose ol li::marker{color:var(--text-muted,#555f5b);font-weight:700}
          .sp-prose blockquote{margin:32px 0;padding:24px 28px;background:var(--surface,#fff);border-left:3px solid var(--dark,#071311);border-radius:0 14px 14px 0;font-family:"Syne",sans-serif;font-size:19px;line-height:1.4;color:var(--text,#111816);letter-spacing:-.3px}
          .sp-prose h2,.sp-prose h3{font-family:"Syne",sans-serif}
          .sp-end-cta{display:flex;justify-content:space-between;align-items:center;gap:24px;background:var(--surface-soft,#f0eee6);border:1.5px dashed var(--border,#dedbd2);border-radius:18px;padding:24px 28px;margin:48px 0 0;flex-wrap:wrap}
          .sp-end-cta strong{display:block;font-family:"Syne",sans-serif;font-size:18px;letter-spacing:-.4px;font-weight:700;max-width:38ch}
          .sp-btn{display:inline-flex;align-items:center;gap:8px;background:var(--brand,#0d9488);color:#fff;padding:13px 22px;border-radius:999px;font-weight:700;font-size:14px;text-decoration:none}
          .sp-tags{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-top:32px;padding-top:24px;border-top:1px solid var(--border,#dedbd2)}
          .sp-tags>span{font-size:12px;color:#7c7c74;font-weight:700;letter-spacing:.6px;text-transform:uppercase;margin-right:6px}
          .sp-tags a{padding:6px 12px;border-radius:999px;background:var(--surface-soft,#f0eee6);border:1px solid var(--border,#dedbd2);font-size:12.5px;color:var(--text,#111816);text-decoration:none;font-weight:600}
          .sp-author-box{display:flex;gap:18px;align-items:flex-start;margin-top:32px;padding:24px;background:var(--surface,#fff);border:1px solid var(--border,#dedbd2);border-radius:18px}
          .sp-author-box strong{display:block;font-family:"Syne",sans-serif;font-size:17px;font-weight:700;margin-bottom:4px}
          .sp-author-box p{margin:0 0 10px;font-size:14px;color:#3d3d38;line-height:1.6}
          .sp-author-box a{color:var(--brand,#0d9488);font-weight:700;font-size:13px;text-decoration:none}
          .sp-related{padding:0 0 128px}
          .sp-rel-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
          .sp-rel-card{background:var(--surface,#fff);border:1px solid var(--border,#dedbd2);border-radius:16px;overflow:hidden;text-decoration:none;color:inherit;display:block;transition:.2s ease}
          .sp-rel-card:hover{transform:translateY(-3px);border-color:var(--border-strong,#c4c0b4)}
          .sp-rel-thumb{position:relative;aspect-ratio:1.7;background:var(--surface-soft,#f0eee6)}
          .sp-rel-thumb img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
          .sp-rel-meta{display:flex;gap:8px;padding:18px 18px 0;font-size:11.5px;color:#7c7c74}
          .sp-rel-meta>span:first-child{color:#0f766e;font-weight:700;letter-spacing:1px;text-transform:uppercase}
          .sp-rel-card h3{margin:8px 0 18px;padding:0 18px;font-family:"Syne",sans-serif;font-size:16px;letter-spacing:-.3px;line-height:1.3;font-weight:700}
          @media (max-width:1050px){.sp-grid{grid-template-columns:1fr;gap:36px;width:min(880px,100% - 64px)}.sp-toc{position:static}.sp-rel-grid{grid-template-columns:1fr 1fr}}
          @media (max-width:760px){.sp-wrap,.sp-wrap-narrow,.sp-grid{width:min(1180px,100% - 24px)}.sp-rel-grid{grid-template-columns:1fr}.sp-author{flex-wrap:wrap}.sp-share{margin-left:0}.hr-contact-grid{grid-template-columns:1fr}}
          <?php if (!empty($has_upsellio_content_shortcodes)) : ?>
          /* [upsellio_contact_form] / [upsellio_internal_links] — inline fallback gdy pełny upsellio.css jest obcięty (critical CSS, cache) */
          .sp-prose .ups-inline-contact{clear:both}
          .ups-inline-contact{margin:40px 0 0;padding:clamp(22px,3vw,28px);background:#fff;border:1px solid #e7e7e1;border-radius:18px;box-sizing:border-box}
          .ups-inline-contact-head{margin-bottom:20px}
          .ups-inline-label{display:inline-flex;align-items:center;gap:10px;font-size:11px;font-weight:700;letter-spacing:1.6px;text-transform:uppercase;color:var(--text-muted,#555f5b);margin-bottom:12px}
          .ups-inline-label::before{content:"";width:26px;height:2px;background:var(--brand,#0d9488);border-radius:99px}
          .ups-inline-contact-head h3{margin:0 0 10px;font-family:Syne,sans-serif;font-size:clamp(20px,2.2vw,26px);line-height:1.15;letter-spacing:-.5px;font-weight:700;color:#0a1410}
          .ups-inline-contact-head p{margin:0;font-size:15px;line-height:1.65;color:#3d3d38;max-width:62ch}
          .ups-inline-success,.ups-inline-error{margin-bottom:18px;padding:14px 16px;border-radius:12px;font-size:14px;line-height:1.5;font-weight:600}
          .ups-inline-success{background:rgba(13,148,136,.1);border:1px solid rgba(13,148,136,.28);color:#0f766e}
          .ups-inline-error{background:rgba(217,76,76,.08);border:1px solid rgba(217,76,76,.25);color:#8b2f2f}
          .ups-inline-form{display:grid;gap:14px;margin:0}
          .ups-inline-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
          .ups-inline-form>label{display:grid;gap:6px;font-size:12px;font-weight:700;color:#3d3d38}
          .ups-inline-form input[type="text"],.ups-inline-form input[type="email"],.ups-inline-form textarea{width:100%;border:1.5px solid #e7e7e1;border-radius:11px;min-height:44px;padding:10px 12px;font:inherit;font-size:15px;color:#0a1410;background:#fff;outline:none;transition:border-color .18s ease,box-shadow .18s ease;box-sizing:border-box}
          .ups-inline-form textarea{min-height:120px;resize:vertical;line-height:1.6}
          .ups-inline-form input:focus,.ups-inline-form textarea:focus{border-color:var(--brand,#0d9488);box-shadow:0 0 0 3px var(--brand-soft,#ccfbf1)}
          .ups-inline-consent{display:flex!important;gap:10px;align-items:flex-start;font-size:13px;font-weight:500;color:#3d3d38;line-height:1.5}
          .ups-inline-consent input{margin-top:4px;width:auto;min-height:auto;flex-shrink:0}
          .ups-inline-form button[type="submit"]{margin-top:4px;justify-self:start;display:inline-flex;align-items:center;justify-content:center;gap:8px;background:var(--brand,#0d9488);color:#fff;padding:13px 22px;border-radius:999px;font-weight:700;font-size:14px;border:none;cursor:pointer;font-family:"DM Sans",system-ui,sans-serif;transition:background .18s ease,transform .15s ease}
          .ups-inline-form button[type="submit"]:hover{background:var(--brand-dark,#0f766e)}
          .ups-inline-form button[type="submit"]:active{transform:scale(.98)}
          @media (max-width:640px){.ups-inline-grid{grid-template-columns:1fr}.ups-inline-form button[type="submit"]{width:100%;justify-self:stretch}}
          .ups-inline-links{margin:32px 0;padding:20px 22px;background:#fff;border:1px solid #e7e7e1;border-radius:18px;box-sizing:border-box}
          .ups-inline-links h3{margin:0 0 12px;font-family:Syne,sans-serif;font-size:18px;font-weight:700;letter-spacing:-.3px;color:#0a1410}
          .ups-inline-links ul{margin:0;padding-left:22px}
          .ups-inline-links li{margin-bottom:6px;font-size:15px;line-height:1.55;color:#3d3d38}
          .ups-inline-links li::marker{color:var(--text-muted,#555f5b);font-weight:700}
          .ups-inline-links a{color:var(--brand,#0d9488);font-weight:600;text-decoration:none}
          .ups-inline-links a:hover{color:var(--brand-dark,#0f766e);text-decoration:underline}
          <?php endif; ?>
        </style>

        <main class="sp-art">
          <?php
          $pill_categories = wp_list_pluck($post_categories, "name");
          $category_label = !empty($pill_categories) ? $pill_categories[0] : "Blog";
          $read_time = (string) upsellio_estimated_read_time($post_id);
          $author_name = (string) get_the_author_meta("display_name", (int) get_post_field("post_author", $post_id));
          $category_url = $primary_category instanceof WP_Term ? get_category_link((int) $primary_category->term_id) : $blog_index_url;
          if ($author_name === "") {
              $author_name = "Upsellio";
          }
          ?>
          <nav class="sp-crumbs">
            <div class="sp-wrap-narrow">
              <a href="<?php echo esc_url(home_url("/")); ?>">Strona główna</a>
              <span>›</span>
              <a href="<?php echo esc_url($blog_index_url); ?>">Blog</a>
              <span>›</span>
              <a href="<?php echo esc_url($category_url); ?>"><?php echo esc_html($category_label); ?></a>
            </div>
          </nav>

          <header class="sp-head">
            <div class="sp-wrap-narrow">
              <div class="sp-eyebrow" data-animate="fade-up"><?php echo esc_html($category_label . " · " . $read_time); ?></div>
              <h1 class="sp-h1" data-animate="fade-up" data-delay="1"><?php echo esc_html(get_the_title($post_id)); ?></h1>
              <?php if (has_excerpt()) : ?>
                <p class="sp-lead" data-animate="fade-up" data-delay="2"><?php echo esc_html(get_the_excerpt($post_id)); ?></p>
              <?php endif; ?>
              <div class="sp-author" data-animate="fade-up" data-delay="2">
                <div class="sp-avatar">SK</div>
                <div>
                  <strong><?php echo esc_html($author_name); ?></strong>
                  <span class="sp-meta-line"><?php echo esc_html(get_the_date("j F Y", $post_id)); ?> · Upsellio</span>
                </div>
                <div class="sp-share">
                  <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo rawurlencode(get_permalink($post_id)); ?>" target="_blank" rel="noopener">in</a>
                  <a href="https://twitter.com/intent/tweet?url=<?php echo rawurlencode(get_permalink($post_id)); ?>" target="_blank" rel="noopener">𝕏</a>
                  <a href="<?php echo esc_url(get_permalink($post_id)); ?>">⎘</a>
                </div>
              </div>
            </div>
          </header>

          <div class="sp-cover">
            <div class="sp-wrap">
              <div class="sp-cover-img">
                <?php if ($hero_image) : ?>
                  <img
                    src="<?php echo esc_url($hero_image); ?>"
                    alt="<?php echo esc_attr(get_the_title($post_id)); ?>"
                    width="<?php echo (int) $hero_w; ?>"
                    height="<?php echo (int) $hero_h; ?>"
                    fetchpriority="high"
                    decoding="async"
                    <?php if ($hero_image_srcset !== "") : ?>srcset="<?php echo esc_attr($hero_image_srcset); ?>" sizes="<?php echo esc_attr($hero_image_sizes); ?>"<?php endif; ?>
                  />
                <?php else : ?>
                  <div class="sp-thumb-stripes"></div>
                  <div class="sp-thumb-label">[ artwork — okładka artykułu ]</div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <section class="sp-body">
            <div class="sp-wrap-narrow sp-grid">
              <aside class="sp-toc" data-animate="fade-up" data-delay="1">
                <div class="sp-toc-head">Spis treści</div>
                <?php if (!empty($toc_items)) : ?>
                  <ol>
                    <?php foreach ($toc_items as $toc_index => $toc_item) : ?>
                      <li class="<?php echo $toc_index === 0 ? "is-active" : ""; ?>">
                        <a href="#<?php echo esc_attr($toc_item["id"]); ?>"><?php echo esc_html($toc_item["title"]); ?></a>
                      </li>
                    <?php endforeach; ?>
                  </ol>
                <?php endif; ?>
                <div class="sp-toc-cta">
                  <strong>Bezpłatna diagnoza</strong>
                  <p>15 min rozmowy + konkretny kierunek.</p>
                  <?php if ($offer_form_url !== "") : ?><a href="<?php echo esc_url($offer_form_url); ?>">Umów rozmowę →</a><?php endif; ?>
                </div>
              </aside>

              <article class="sp-prose">
                <?php echo wp_kses_post($content_html); ?>

                <div class="sp-end-cta">
                  <div>
                    <div class="sp-eyebrow">Bezpłatna diagnoza</div>
                    <strong>Sprawdźmy, który element lejka blokuje sprzedaż u Ciebie.</strong>
                  </div>
                  <?php if ($offer_form_url !== "") : ?><a class="sp-btn post-cta" href="<?php echo esc_url($offer_form_url); ?>">Umów rozmowę →</a><?php endif; ?>
                </div>

                <?php if (!empty($post_categories)) : ?>
                  <div class="sp-tags">
                    <span>Tagi:</span>
                    <?php foreach ($post_categories as $post_category) : ?>
                      <a href="<?php echo esc_url(get_category_link($post_category->term_id)); ?>"><?php echo esc_html($post_category->name); ?></a>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>

                <div class="sp-author-box">
                  <div class="sp-avatar lg">SK</div>
                  <div>
                    <strong><?php echo esc_html($author_name); ?></strong>
                    <p>Marketing i sprzedaż B2B. W Upsellio łączę procesy sprzedaży z reklamą i stronami WWW.</p>
                    <a href="<?php echo esc_url($blog_index_url); ?>">Zobacz inne wpisy →</a>
                  </div>
                </div>

                <section class="hr-contact-shell" id="kontakt">
                  <?php
                  echo upsellio_render_lead_form([
                      "origin" => "single-post-contact-form",
                      "variant" => "compact",
                      "heading" => "Chcesz, żebym przeanalizował Twoją sytuację marketingową?",
                      "subheading" => "Wyślij krótką wiadomość. Otrzymasz konkretną odpowiedź.",
                      "submit_label" => "Wyślij wiadomość",
                      "redirect_url" => get_permalink($post_id) . "#kontakt",
                      "hidden_service" => "Bezpłatna diagnoza marketingu",
                  ]);
                  ?>
                  <p class="sp-contact-note">Możesz też napisać: <a href="<?php echo esc_url("mailto:" . $contact_email); ?>"><?php echo esc_html($contact_email); ?></a> lub zadzwonić: <a href="<?php echo esc_url("tel:" . preg_replace("/\s+/", "", $contact_phone)); ?>"><?php echo esc_html($contact_phone); ?></a></p>
                </section>
              </article>
            </div>
          </section>

          <?php if (!empty($related_posts)) : ?>
            <section class="sp-related">
              <div class="sp-wrap-narrow">
                <header class="sp-sec-head">
                  <div class="sp-eyebrow">Czytaj też</div>
                  <h2 class="sp-h2">Powiązane artykuły.</h2>
                </header>
                <div class="sp-divider"></div>
                <div class="sp-rel-grid">
                  <?php foreach ($related_posts as $rel_index => $related_post) : ?>
                    <?php
                    $related_post_id = (int) $related_post->ID;
                    $related_cats = get_the_category($related_post_id);
                    $related_cat_name = !empty($related_cats) ? $related_cats[0]->name : "Artykuł";
                    $related_img = get_the_post_thumbnail_url($related_post_id, "large");
                    if (!$related_img) {
                        $related_img = get_post_meta($related_post_id, "_upsellio_featured_image_url", true);
                    }
                    ?>
                    <a class="sp-rel-card" href="<?php echo esc_url(get_permalink($related_post_id)); ?>" data-animate="fade-up"<?php echo $rel_index > 0 ? ' data-delay="' . (int) min(4, $rel_index) . '"' : ""; ?>>
                      <div class="sp-rel-thumb">
                        <?php if ($related_img) : ?>
                          <img src="<?php echo esc_url($related_img); ?>" alt="<?php echo esc_attr(get_the_title($related_post_id)); ?>" loading="lazy" decoding="async" />
                        <?php else : ?>
                          <div class="sp-thumb-stripes"></div>
                        <?php endif; ?>
                      </div>
                      <div class="sp-rel-meta">
                        <span><?php echo esc_html($related_cat_name); ?></span>
                        <span>·</span>
                        <span><?php echo esc_html(upsellio_estimated_read_time($related_post_id)); ?></span>
                      </div>
                      <h3><?php echo esc_html(get_the_title($related_post_id)); ?></h3>
                    </a>
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
