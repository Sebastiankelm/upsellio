<?php
/*
Template Name: Upsellio - Blog v4 (Core)
Template Post Type: page
*/

if (!defined("ABSPATH")) {
    exit;
}

/* ============================================================
   Helper: teaser excerptow (max $words slow)
   ============================================================ */
if (!function_exists("upsellio_blog_teaser")) {
    function upsellio_blog_teaser(int $post_id, int $words = 20): string
    {
        $raw = get_the_excerpt($post_id);
        if (!$raw) {
            $raw = wp_strip_all_tags((string) get_post_field("post_content", $post_id));
        }
        $raw = preg_replace("/\s+/", " ", trim(wp_strip_all_tags($raw)));
        return wp_trim_words($raw, $words, "…");
    }
}

/* ============================================================
   Dane strony
   ============================================================ */
$blog_index_url     = function_exists("upsellio_get_blog_index_url") ? upsellio_get_blog_index_url() : get_permalink();
$selected_category  = isset($_GET["category"]) ? sanitize_title(wp_unslash($_GET["category"])) : "";
$paged              = max(
    1,
    (int) get_query_var("paged"),
    (int) get_query_var("page"),
    isset($_GET["paged"]) ? (int) $_GET["paged"] : 1
);

$query_args = [
    "post_type"      => "post",
    "post_status"    => "publish",
    "posts_per_page" => 10,
    "paged"          => $paged,
];
if ($selected_category !== "") {
    $query_args["category_name"] = $selected_category;
}

$blog_query = new WP_Query($query_args);
$categories = get_categories(["hide_empty" => true]);

/* ============================================================
   Linki pomocnicze
   ============================================================ */
$contact_url   = function_exists("upsellio_get_contact_page_url")  ? upsellio_get_contact_page_url()  : home_url("/kontakt/");
$lead_mag_url  = home_url("/lead-magnety/");
$admin_post    = admin_url("admin-post.php");
$upsellio_blog_variant = isset($upsellio_blog_variant) ? (string) $upsellio_blog_variant : "default";

get_header();
?>
<style>
/* ============================================================
   BLOG v4 — design system zgodny z home + kontakt v4
   Prefiks: .bg-  (blog)
   Paleta: jasny minimal — tło #fbfbfd, karty #fff, turkus #0bb39c
   Buttony primary: dark (#0a0e14) zamiast turkusowych
   Fonty: Bricolage Grotesque (display, 800) + DM Sans (body)
   ============================================================ */

:root{
  --bg-ink:#0f1115;
  --bg-ink2:#3a3d44;
  --bg-muted:#6e727b;
  --bg-faint:#9ea2aa;
  --bg-bg:#fbfbfd;
  --bg-surface:#ffffff;
  --bg-soft:#f4f5f7;
  --bg-border:#e6e8ec;
  --bg-border2:#d8dde3;
  --bg-line:#eef0f3;
  --bg-teal:#0bb39c;
  --bg-tealh:#089a86;
  --bg-teald:#06745f;
  --bg-tealx:#3dd8c3;
  --bg-teals:#e6fbf6;
  --bg-tealss:#f3fdfa;
  --bg-tealb:#d0f7ed;
  --bg-dark:#0a0e14;
  --bg-r:10px;
  --bg-rl:16px;
  --bg-rxl:22px;
  --bg-rxxl:28px;
  --bg-fd:'Bricolage Grotesque','Syne',sans-serif;
  --bg-fb:'DM Sans',-apple-system,sans-serif;
}

/* === RESET === */
.bg-art{font-family:var(--bg-fb);background:var(--bg-bg);color:var(--bg-ink);line-height:1.6;-webkit-font-smoothing:antialiased;font-feature-settings:"ss01","cv01"}
.bg-art *,.bg-art *::before,.bg-art *::after{box-sizing:border-box}
.bg-art a{color:inherit;text-decoration:none}
.bg-wrap{max-width:1180px;margin:0 auto;padding:0 24px}

/* === EYEBROW — wzorzec z home v4 === */
.bg-eyebrow{display:inline-flex;align-items:center;gap:10px;margin-bottom:20px;color:var(--bg-tealh);font-size:12px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;font-feature-settings:"ss01"}
.bg-eyebrow::before{content:"";width:20px;height:1px;background:var(--bg-teal)}

/* === HERO === */
.bg-hero{padding:64px 0 48px;background:var(--bg-bg);border-bottom:1px solid var(--bg-line);position:relative;overflow:hidden}
.bg-hero::before{content:"";position:absolute;top:-200px;right:-300px;width:700px;height:700px;border-radius:50%;background:radial-gradient(circle,rgba(11,179,156,.07),transparent 60%);pointer-events:none}
.bg-hero-inner{display:grid;grid-template-columns:1fr auto;gap:40px;align-items:end;position:relative}
.bg-hero h1{font-family:var(--bg-fd);font-weight:800;font-size:clamp(36px,4.8vw,58px);line-height:1;letter-spacing:-.035em;color:var(--bg-ink);margin:0 0 16px}
.bg-hero-lead{font-size:17px;line-height:1.6;color:var(--bg-muted);max-width:56ch;margin:0}
.bg-hero-cta{display:flex;flex-direction:column;gap:12px;align-items:flex-end;flex-shrink:0}
.bg-hero-btn{display:inline-flex;align-items:center;gap:8px;padding:13px 24px;background:var(--bg-dark);color:#fff;border-radius:99px;font-family:var(--bg-fb);font-size:14px;font-weight:700;white-space:nowrap;letter-spacing:-.01em;transition:background .15s}
.bg-hero-btn:hover{background:var(--bg-teald);color:#fff}

/* === NEWSLETTER INLINE === */
.bg-newsletter{background:var(--bg-surface);border:1px solid var(--bg-border);border-radius:var(--bg-rl);padding:16px 18px;min-width:340px}
.bg-newsletter p{margin:0 0 8px;font-size:12.5px;color:var(--bg-muted);line-height:1.5}
.bg-newsletter-form{display:flex;gap:8px}
.bg-newsletter-form input[type="email"]{flex:1;min-height:40px;border:1.5px solid var(--bg-border);border-radius:var(--bg-r);padding:0 12px;font-family:var(--bg-fb);font-size:14px;color:var(--bg-ink);background:var(--bg-bg);transition:border-color .15s}
.bg-newsletter-form input[type="email"]:focus{border-color:var(--bg-teal);outline:none;box-shadow:0 0 0 3px rgba(11,179,156,.1)}
.bg-newsletter-form button{min-height:40px;padding:0 14px;border:0;border-radius:99px;background:var(--bg-teal);color:#fff;font-family:var(--bg-fb);font-size:13.5px;font-weight:700;cursor:pointer;white-space:nowrap;transition:background .15s}
.bg-newsletter-form button:hover{background:var(--bg-tealh)}

/* === FILTER BAR === */
.bg-filter-bar{background:var(--bg-bg);border-bottom:1px solid var(--bg-line);padding:14px 0}
.bg-filters{display:flex;flex-wrap:wrap;gap:8px}
.bg-filter{padding:8px 16px;border-radius:99px;font-size:12.5px;font-weight:700;text-decoration:none;border:1.5px solid var(--bg-border);background:transparent;color:var(--bg-muted);transition:all .15s;letter-spacing:-.005em}
.bg-filter:hover{border-color:var(--bg-ink2);color:var(--bg-ink)}
.bg-filter.is-active{background:var(--bg-dark);color:#fff;border-color:var(--bg-dark)}

/* === FEATURED POST === */
.bg-featured-wrap{padding:40px 0 0}
.bg-featured{display:grid;grid-template-columns:1fr 1fr;gap:0;background:var(--bg-surface);border:1px solid var(--bg-border);border-radius:var(--bg-rxl);overflow:hidden;box-shadow:0 1px 0 rgba(15,17,21,.04),0 4px 12px rgba(15,17,21,.04)}
.bg-feat-thumb{position:relative;background:var(--bg-soft);min-height:300px}
.bg-feat-thumb a{display:block;height:100%;position:absolute;inset:0}
.bg-feat-thumb img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.bg-feat-thumb-placeholder{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background-image:repeating-linear-gradient(135deg,rgba(11,179,156,.04) 0 10px,transparent 10px 20px)}
.bg-feat-badge{position:absolute;top:14px;left:14px;z-index:2;background:var(--bg-tealss);border:1px solid var(--bg-tealb);color:var(--bg-tealh);font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;padding:4px 10px;border-radius:99px;font-feature-settings:"ss01"}
.bg-feat-body{padding:36px;display:flex;flex-direction:column;justify-content:center}
.bg-post-meta{display:flex;align-items:center;gap:8px;margin-bottom:14px;flex-wrap:wrap}
.bg-cat{display:inline-flex;font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;padding:3px 9px;border-radius:99px;background:var(--bg-soft);border:1px solid var(--bg-border);color:var(--bg-ink2)}
.bg-date{font-size:12px;color:var(--bg-faint)}
.bg-read{font-size:12px;color:var(--bg-faint)}
.bg-feat-body h2{font-family:var(--bg-fd);font-weight:800;font-size:clamp(22px,2.6vw,30px);line-height:1.15;letter-spacing:-.025em;color:var(--bg-ink);margin:0 0 12px}
.bg-feat-body h2 a{color:inherit}
.bg-feat-body h2 a:hover{color:var(--bg-tealh)}
.bg-feat-excerpt{font-size:15px;line-height:1.6;color:var(--bg-muted);margin:0 0 22px}
.bg-read-more{display:inline-flex;align-items:center;gap:6px;font-size:14px;font-weight:700;color:var(--bg-ink);border-bottom:1.5px solid var(--bg-teal);padding-bottom:2px;align-self:flex-start;letter-spacing:-.005em}
.bg-read-more:hover{color:var(--bg-tealh)}

/* === AUTHOR BAR === */
.bg-author{margin:32px 0 0;padding:18px 22px;background:var(--bg-soft);border:1px solid var(--bg-border);border-radius:var(--bg-rl);display:flex;align-items:center;gap:16px}
.bg-author-avatar{width:44px;height:44px;border-radius:50%;background:var(--bg-tealss);border:1px solid var(--bg-tealb);color:var(--bg-tealh);display:flex;align-items:center;justify-content:center;font-family:var(--bg-fd);font-weight:800;font-size:15px;flex-shrink:0}
.bg-author-name{font-family:var(--bg-fd);font-weight:800;font-size:14px;color:var(--bg-ink);letter-spacing:-.01em;display:block;margin-bottom:2px}
.bg-author-bio{font-size:13px;color:var(--bg-muted);line-height:1.45}

/* === SECTION DIVIDER === */
.bg-section-label{display:flex;align-items:center;justify-content:space-between;padding:32px 0 16px}
.bg-section-label span{font-family:var(--bg-fd);font-size:12px;font-weight:700;color:var(--bg-muted);text-transform:uppercase;letter-spacing:.05em;font-feature-settings:"ss01"}
.bg-section-label a{font-size:13px;color:var(--bg-tealh);font-weight:700;border-bottom:1px solid var(--bg-tealb);padding-bottom:1px}
.bg-section-label a:hover{color:var(--bg-teald)}

/* === POST GRID (3 kolumny) === */
.bg-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
.bg-card{background:var(--bg-surface);border:1px solid var(--bg-border);border-radius:var(--bg-rl);overflow:hidden;display:flex;flex-direction:column;transition:border-color .2s,transform .2s;box-shadow:0 1px 0 rgba(15,17,21,.03)}
.bg-card:hover{border-color:var(--bg-ink);transform:translateY(-2px)}
.bg-card-thumb{background:var(--bg-soft);aspect-ratio:1.7;position:relative;border-bottom:1px solid var(--bg-border)}
.bg-card-thumb a{display:block;position:absolute;inset:0}
.bg-card-thumb img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.bg-card-thumb-ph{position:absolute;inset:0;background-image:repeating-linear-gradient(135deg,rgba(15,17,21,.04) 0 8px,transparent 8px 16px)}
.bg-card-body{padding:18px;display:flex;flex-direction:column;flex:1;gap:8px}
.bg-card-body h3{font-family:var(--bg-fd);font-weight:800;font-size:16px;line-height:1.25;letter-spacing:-.02em;color:var(--bg-ink)}
.bg-card-body h3 a{color:inherit}
.bg-card-body h3 a:hover{color:var(--bg-tealh)}
.bg-card-excerpt{font-size:13.5px;line-height:1.55;color:var(--bg-muted);flex:1}
.bg-card-foot{display:flex;align-items:center;justify-content:space-between;padding-top:12px;border-top:1px solid var(--bg-line);margin-top:4px}
.bg-card-foot-meta{font-size:12px;color:var(--bg-faint);display:flex;align-items:center;gap:6px}
.bg-card-link{font-size:13px;font-weight:700;color:var(--bg-ink);border-bottom:1.5px solid var(--bg-teal);padding-bottom:1px}
.bg-card-link:hover{color:var(--bg-tealh)}

/* === CTA STRIP (zamiast inline-cta pomaranczowego) === */
.bg-cta-strip{margin:20px 0;background:var(--bg-surface);border:1px solid var(--bg-border);border-left:3px solid var(--bg-teal);border-radius:var(--bg-rl);padding:20px 24px;display:flex;align-items:center;justify-content:space-between;gap:24px}
.bg-cta-strip-text strong{display:block;font-family:var(--bg-fd);font-weight:800;font-size:16px;color:var(--bg-ink);letter-spacing:-.015em;margin-bottom:4px}
.bg-cta-strip-text span{font-size:13.5px;color:var(--bg-muted)}
.bg-cta-strip-btn{white-space:nowrap;padding:11px 22px;background:var(--bg-dark);color:#fff;border-radius:99px;font-family:var(--bg-fb);font-size:13.5px;font-weight:700;flex-shrink:0;transition:background .15s}
a.bg-cta-strip-btn,.bg-art a.bg-cta-strip-btn{color:#fff}
.bg-cta-strip-btn:hover{background:var(--bg-teald);color:#fff}

/* === PAGINACJA === */
.bg-pager{display:flex;justify-content:center;align-items:center;margin-top:40px;padding-top:32px;border-top:1px solid var(--bg-line);gap:8px;flex-wrap:wrap}
.bg-pager a,.bg-pager span{min-width:38px;height:38px;padding:0 12px;border-radius:99px;border:1.5px solid var(--bg-border);background:var(--bg-surface);font-size:13px;font-weight:700;color:var(--bg-ink);text-decoration:none;display:inline-flex;align-items:center;justify-content:center;transition:border-color .15s}
.bg-pager a:hover{border-color:var(--bg-ink2)}
.bg-pager .is-current{background:var(--bg-dark);color:#fff;border-color:var(--bg-dark)}
.bg-pager .is-disabled{opacity:.35;pointer-events:none}

/* === FINAL CTA — jasna karta, nie dark band === */
.bg-final{padding:80px 0 96px;background:var(--bg-bg);border-top:1px solid var(--bg-line)}
.bg-final-card{background:var(--bg-surface);border:1px solid var(--bg-border);border-left:3px solid var(--bg-teal);border-radius:var(--bg-rxl);padding:52px 56px;display:grid;grid-template-columns:1.2fr 1fr;gap:48px;align-items:center;box-shadow:0 1px 0 rgba(15,17,21,.04),0 4px 12px rgba(15,17,21,.04)}
.bg-final-eyebrow{display:inline-flex;align-items:center;gap:10px;margin-bottom:16px;color:var(--bg-tealh);font-size:12px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;font-feature-settings:"ss01"}
.bg-final-eyebrow::before{content:"";width:20px;height:1px;background:var(--bg-teal)}
.bg-final-card h2{font-family:var(--bg-fd);font-weight:800;font-size:clamp(26px,3.2vw,38px);line-height:1.04;letter-spacing:-.03em;color:var(--bg-ink);margin:0 0 14px}
.bg-final-card p{font-size:16px;line-height:1.6;color:var(--bg-muted);margin:0 0 24px}
.bg-final-btn{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;background:var(--bg-dark);color:#fff;border-radius:99px;font-family:var(--bg-fb);font-weight:700;font-size:15px;letter-spacing:-.01em;transition:background .15s}
a.bg-final-btn,.bg-art a.bg-final-btn{color:#fff}
.bg-final-btn:hover{background:var(--bg-teald);color:#fff}
.bg-final-side{display:flex;flex-direction:column;gap:18px;padding-left:32px;border-left:1px solid var(--bg-line)}
.bg-final-side-label{font-family:var(--bg-fd);font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--bg-muted);margin-bottom:4px;display:block;font-feature-settings:"ss01"}
.bg-final-side a{font-family:var(--bg-fd);font-weight:800;font-size:18px;color:var(--bg-ink);border-bottom:1.5px solid var(--bg-teal);padding-bottom:1px;display:inline-block;letter-spacing:-.01em}
.bg-final-side a:hover{color:var(--bg-tealh)}
.bg-final-side-note{font-size:13px;color:var(--bg-muted);line-height:1.5;margin-top:8px}

/* === ANIMACJE === */
[data-animate]{opacity:0;transition:opacity .7s ease,transform .7s ease}
[data-animate="fade-up"]{transform:translateY(16px)}
[data-animate].is-visible{opacity:1;transform:none}
[data-delay="1"]{transition-delay:.08s}
[data-delay="2"]{transition-delay:.16s}
[data-delay="3"]{transition-delay:.24s}
@media (prefers-reduced-motion:reduce){[data-animate]{opacity:1;transform:none;transition:none}}

/* === RESPONSIVE === */
@media (max-width:1024px){
  .bg-hero-inner{grid-template-columns:1fr}
  .bg-hero-cta{align-items:flex-start}
  .bg-newsletter{min-width:0;width:100%;max-width:480px}
  .bg-featured{grid-template-columns:1fr}
  .bg-feat-thumb{min-height:240px}
  .bg-final-card{grid-template-columns:1fr;gap:32px;padding:40px 36px}
  .bg-final-side{padding-left:0;padding-top:28px;border-left:0;border-top:1px solid var(--bg-line)}
}
@media (max-width:720px){
  .bg-hero{padding:40px 0 32px}
  .bg-grid{grid-template-columns:1fr}
  .bg-final{padding:56px 0 64px}
  .bg-final-card{padding:28px 24px;border-radius:var(--bg-rl)}
  .bg-cta-strip{flex-direction:column;align-items:flex-start}
}
@media (max-width:880px){
  .bg-grid{grid-template-columns:1fr 1fr}
}
</style>
<?php if ($upsellio_blog_variant === "alt") : ?>
<style>
/* Alternate variant for selectable Blog Alt template */
.bg-art{
  background:#f7faf9;
}
.bg-hero{
  background:linear-gradient(180deg,#f7faf9 0%,#eef8f5 100%);
}
.bg-hero-btn,
.bg-final-btn,
.bg-cta-strip-btn{
  background:var(--bg-teal);
}
.bg-hero-btn:hover,
.bg-final-btn:hover,
.bg-cta-strip-btn:hover{
  background:var(--bg-tealh);
}
.bg-filter.is-active{
  background:var(--bg-teal);
  border-color:var(--bg-teal);
}
.bg-featured,
.bg-card,
.bg-final-card{
  border-color:#d9ece7;
  box-shadow:0 1px 0 rgba(6,116,95,.05),0 6px 16px rgba(6,116,95,.06);
}
</style>
<?php endif; ?>

<main class="bg-art" id="blog">

  <!-- ============================================================
       SEKCJA 01 — HERO
       H1 blogowy + lead + newsletter inline + CTA przycisk
       ============================================================ -->
  <section class="bg-hero" aria-labelledby="bg-blog-h1">
    <div class="bg-wrap">
      <div class="bg-hero-inner" data-animate="fade-up">
        <div>
          <div class="bg-eyebrow">Marketing B2B · wiedza z praktyki</div>
          <h1 id="bg-blog-h1">Blog Upsellio</h1>
          <p class="bg-hero-lead">
            Kampanie, strony i lejki B2B — opisuję konkretne problemy
            i co naprawdę pomaga. Z perspektywy handlowca, nie agencji.
          </p>
        </div>
        <div class="bg-hero-cta">
          <a href="<?php echo esc_url($contact_url); ?>" class="bg-hero-btn">
            Masz problem? Napisz →
          </a>
          <div class="bg-newsletter">
            <p>Co 2 tygodnie: jeden praktyczny artykuł. Bez clickbaitu.</p>
            <form method="post" action="<?php echo esc_url($admin_post); ?>" class="bg-newsletter-form">
              <input type="hidden" name="action" value="upsellio_lead_form_submit">
              <input type="hidden" name="lead_form_origin" value="blog-newsletter">
              <input type="email" name="email" placeholder="twoj@email.pl" required>
              <button type="submit">Subskrybuj →</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================================================
       SEKCJA 02 — FILTER BAR
       Kategorie jako pill-linki. Aktywna = dark (jak v4 design system)
       ============================================================ -->
  <section class="bg-filter-bar" aria-label="Filtry kategorii">
    <div class="bg-wrap">
      <div class="bg-filters">
        <a
          class="bg-filter <?php echo $selected_category === "" ? "is-active" : ""; ?>"
          href="<?php echo esc_url($blog_index_url); ?>"
        >Wszystkie</a>
        <?php foreach ($categories as $category) : ?>
          <a
            class="bg-filter <?php echo $selected_category === $category->slug ? "is-active" : ""; ?>"
            href="<?php echo esc_url(add_query_arg("category", $category->slug, $blog_index_url)); ?>"
          ><?php echo esc_html($category->name); ?></a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ============================================================
       SEKCJA 03 — WPISY BLOGA
       Układ: 1 featured (2-kol karta) + author bar + grid 3-kol
       ============================================================ -->
  <section aria-label="Wpisy blogowe">
    <div class="bg-wrap">

      <?php
      $posts        = $blog_query->posts;
      $featured_post = !empty($posts) ? array_shift($posts) : null;
      ?>

      <?php if ($featured_post) :
        $featured_id       = (int) $featured_post->ID;
        $featured_cat      = get_the_category($featured_id);
        $featured_cat_name = !empty($featured_cat) ? $featured_cat[0]->name : "Blog";
        $has_featured_img  = has_post_thumbnail($featured_id);
        $featured_ext_img  = !$has_featured_img
            ? (string) get_post_meta($featured_id, "_upsellio_featured_image_url", true)
            : "";
      ?>

      <!-- FEATURED POST — 2-kolumnowy card -->
      <div class="bg-featured-wrap">
        <article class="bg-featured" data-animate="fade-up">

          <!-- Miniaturka / placeholder -->
          <div class="bg-feat-thumb">
            <span class="bg-feat-badge">Najnowszy</span>
            <?php if ($has_featured_img) : ?>
              <a href="<?php echo esc_url(get_permalink($featured_id)); ?>" aria-hidden="true" tabindex="-1">
                <?php echo get_the_post_thumbnail($featured_id, "large", ["loading" => "eager", "decoding" => "async", "alt" => esc_attr(get_the_title($featured_id))]); ?>
              </a>
            <?php elseif ($featured_ext_img !== "") : ?>
              <a href="<?php echo esc_url(get_permalink($featured_id)); ?>" aria-hidden="true" tabindex="-1">
                <img src="<?php echo esc_url($featured_ext_img); ?>" alt="<?php echo esc_attr(get_the_title($featured_id)); ?>" loading="eager" decoding="async">
              </a>
            <?php else : ?>
              <div class="bg-feat-thumb-placeholder" aria-hidden="true"></div>
            <?php endif; ?>
          </div>

          <!-- Treść -->
          <div class="bg-feat-body">
            <div class="bg-post-meta">
              <span class="bg-cat"><?php echo esc_html($featured_cat_name); ?></span>
              <time class="bg-date" datetime="<?php echo esc_attr(get_the_date("c", $featured_id)); ?>">
                <?php echo esc_html(get_the_date("j.n.Y", $featured_id)); ?>
              </time>
              <?php if (function_exists("upsellio_estimated_read_time")) : ?>
                <span class="bg-read">· <?php echo esc_html(upsellio_estimated_read_time($featured_id)); ?></span>
              <?php endif; ?>
            </div>

            <h2>
              <a href="<?php echo esc_url(get_permalink($featured_id)); ?>">
                <?php echo esc_html(get_the_title($featured_id)); ?>
              </a>
            </h2>

            <p class="bg-feat-excerpt">
              <?php echo esc_html(upsellio_blog_teaser($featured_id, 30)); ?>
            </p>

            <a class="bg-read-more" href="<?php echo esc_url(get_permalink($featured_id)); ?>">
              Czytaj artykuł →
            </a>
          </div>

        </article>
      </div>

      <?php endif; ?>

      <!-- AUTHOR BAR — kim pisze, buduje E-E-A-T -->
      <div class="bg-author" data-animate="fade-up">
        <div class="bg-author-avatar" aria-hidden="true">SK</div>
        <div>
          <strong class="bg-author-name">Sebastian Kelm</strong>
          <span class="bg-author-bio">
            10 lat sprzedaży B2B · Google Ads, Meta Ads, strony WWW dla firm ·
            pisze co działa w praktyce, nie co powinno działać w teorii
          </span>
        </div>
      </div>

      <!-- SECTION LABEL — "Pozostałe artykuły" -->
      <div class="bg-section-label">
        <span>Pozostałe artykuły</span>
      </div>

      <!-- POST GRID — 3 kolumny -->
      <div class="bg-grid">
        <?php foreach ($posts as $post_index => $post_item) :
          $post_item_id       = (int) $post_item->ID;
          $post_item_cat      = get_the_category($post_item_id);
          $post_item_cat_name = !empty($post_item_cat) ? $post_item_cat[0]->name : "Blog";
          $has_card_img       = has_post_thumbnail($post_item_id);
          $card_ext_img       = !$has_card_img
              ? (string) get_post_meta($post_item_id, "_upsellio_featured_image_url", true)
              : "";
        ?>
          <article
            class="bg-card"
            data-animate="fade-up"
            <?php echo $post_index > 0 && $post_index < 4 ? 'data-delay="' . (int) min(3, $post_index) . '"' : ""; ?>
          >
            <!-- Miniaturka -->
            <div class="bg-card-thumb">
              <?php if ($has_card_img) : ?>
                <a href="<?php echo esc_url(get_permalink($post_item_id)); ?>" aria-hidden="true" tabindex="-1">
                  <?php echo get_the_post_thumbnail($post_item_id, "medium_large", ["loading" => "lazy", "decoding" => "async", "alt" => esc_attr(get_the_title($post_item_id))]); ?>
                </a>
              <?php elseif ($card_ext_img !== "") : ?>
                <a href="<?php echo esc_url(get_permalink($post_item_id)); ?>" aria-hidden="true" tabindex="-1">
                  <img src="<?php echo esc_url($card_ext_img); ?>" alt="<?php echo esc_attr(get_the_title($post_item_id)); ?>" loading="lazy" decoding="async">
                </a>
              <?php else : ?>
                <div class="bg-card-thumb-ph" aria-hidden="true"></div>
              <?php endif; ?>
            </div>

            <!-- Treść karty -->
            <div class="bg-card-body">
              <div class="bg-post-meta">
                <span class="bg-cat"><?php echo esc_html($post_item_cat_name); ?></span>
                <time class="bg-date" datetime="<?php echo esc_attr(get_the_date("c", $post_item_id)); ?>">
                  <?php echo esc_html(get_the_date("j.n.Y", $post_item_id)); ?>
                </time>
              </div>

              <h3>
                <a href="<?php echo esc_url(get_permalink($post_item_id)); ?>">
                  <?php echo esc_html(get_the_title($post_item_id)); ?>
                </a>
              </h3>

              <p class="bg-card-excerpt">
                <?php echo esc_html(upsellio_blog_teaser($post_item_id, 20)); ?>
              </p>

              <div class="bg-card-foot">
                <div class="bg-card-foot-meta">
                  <?php if (function_exists("upsellio_estimated_read_time")) : ?>
                    <span><?php echo esc_html(upsellio_estimated_read_time($post_item_id)); ?></span>
                  <?php endif; ?>
                </div>
                <a class="bg-card-link" href="<?php echo esc_url(get_permalink($post_item_id)); ?>">Czytaj →</a>
              </div>
            </div>
          </article>

          <!-- CTA STRIP po 3. karcie (index 2 = 3. wpis) -->
          <?php if ($post_index === 2) : ?>
            <div class="bg-cta-strip" style="grid-column:1/-1">
              <div class="bg-cta-strip-text">
                <strong>Masz kampanię Google Ads lub Meta Ads?</strong>
                <span>Sprawdzę ją bezpłatnie i powiem co konkretnie generuje koszty bez efektów.</span>
              </div>
              <a class="bg-cta-strip-btn" href="<?php echo esc_url($contact_url); ?>">Umów diagnozę →</a>
            </div>
          <?php endif; ?>

          <!-- CTA STRIP po 6. karcie (index 5 = 6. wpis) -->
          <?php if ($post_index === 5) : ?>
            <div class="bg-cta-strip" style="grid-column:1/-1">
              <div class="bg-cta-strip-text">
                <strong>Pobierz checklistę — 27 punktów przed startem kampanii</strong>
                <span>Lista którą przechodzę z każdym klientem. Jeśli strona nie odpowiada na 5 z 27 pytań, budżet się pali.</span>
              </div>
              <a class="bg-cta-strip-btn" href="<?php echo esc_url($lead_mag_url); ?>">Pobierz PDF →</a>
            </div>
          <?php endif; ?>

        <?php endforeach; ?>
      </div>

      <!-- PAGINACJA -->
      <?php $blog_max_pages = max(1, (int) $blog_query->max_num_pages); ?>
      <?php if ($blog_max_pages > 1) : ?>
        <nav class="bg-pager" aria-label="Paginacja bloga">
          <?php
          $prev_params = array_filter(["paged" => $paged - 1, "category" => $selected_category]);
          $next_params = array_filter(["paged" => $paged + 1, "category" => $selected_category]);
          ?>
          <?php if ($paged > 1) : ?>
            <a href="<?php echo esc_url(add_query_arg($prev_params, $blog_index_url)); ?>" aria-label="Poprzednia strona">← Poprzednia</a>
          <?php else : ?>
            <span class="is-disabled" aria-hidden="true">← Poprzednia</span>
          <?php endif; ?>

          <?php for ($i = 1; $i <= $blog_max_pages; $i++) :
            $page_params = array_filter(["paged" => $i > 1 ? $i : null, "category" => $selected_category]);
            $page_url    = add_query_arg($page_params, $blog_index_url);
          ?>
            <?php if ($i === $paged) : ?>
              <span class="is-current"><?php echo esc_html((string) $i); ?></span>
            <?php else : ?>
              <a href="<?php echo esc_url($page_url); ?>"><?php echo esc_html((string) $i); ?></a>
            <?php endif; ?>
          <?php endfor; ?>

          <?php if ($paged < $blog_max_pages) : ?>
            <a href="<?php echo esc_url(add_query_arg($next_params, $blog_index_url)); ?>" aria-label="Następna strona">Następna →</a>
          <?php else : ?>
            <span class="is-disabled" aria-hidden="true">Następna →</span>
          <?php endif; ?>
        </nav>
      <?php endif; ?>

    </div>
  </section>

  <!-- ============================================================
       SEKCJA 04 — FINAL CTA
       Jasna karta z lewym akcentem turkusowym + dane kontaktowe
       (nie ciemny band — nie zlewa się ze stopką)
       ============================================================ -->
  <section class="bg-final" aria-label="Kontakt">
    <div class="bg-wrap">
      <div class="bg-final-card" data-animate="fade-up">
        <div>
          <div class="bg-final-eyebrow">Bezpłatna diagnoza</div>
          <h2>Czytasz blog, ale dalej nie wiesz od czego zacząć?</h2>
          <p>
            30 minut rozmowy wystarczy żeby ustalić — czy inwestować w Google Ads,
            Meta Ads, czy najpierw naprawić stronę. Bez prezentacji, sam konkret.
          </p>
          <a class="bg-final-btn" href="<?php echo esc_url($contact_url); ?>">
            Umów bezpłatną rozmowę →
          </a>
        </div>
        <aside class="bg-final-side">
          <?php
          $contact_phone = function_exists("upsellio_get_contact_phone")
              ? upsellio_get_contact_phone()
              : "+48 575 522 595";
          $contact_email = function_exists("upsellio_get_contact_email")
              ? upsellio_get_contact_email()
              : "kontakt@upsellio.pl";
          $phone_href = "tel:" . preg_replace("/[^+\d]/", "", $contact_phone);
          ?>
          <div>
            <span class="bg-final-side-label">Albo bezpośrednio</span>
            <a href="<?php echo esc_attr($phone_href); ?>"><?php echo esc_html($contact_phone); ?></a>
          </div>
          <div>
            <span class="bg-final-side-label">E-mailem</span>
            <a href="mailto:<?php echo esc_attr($contact_email); ?>"><?php echo esc_html($contact_email); ?></a>
          </div>
          <p class="bg-final-side-note">
            Odpowiadam osobiście pn–pt 9–17.<br>
            Jeśli wolisz zadzwonić — oddzwaniam tego samego dnia.
          </p>
        </aside>
      </div>
    </div>
  </section>

</main>

<?php
wp_reset_postdata();
get_footer();
?>
