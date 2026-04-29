<?php
/*
Template Name: Upsellio - Strona Glowna
Template Post Type: page
*/
if (!defined("ABSPATH")) {
    exit;
}

$cfg = function_exists("upsellio_get_front_page_content_config") ? upsellio_get_front_page_content_config() : [];
$seo = is_array($cfg["seo"] ?? null) ? $cfg["seo"] : [];
$contact_phone = function_exists("upsellio_get_contact_phone") ? upsellio_get_contact_phone() : "+48 575 522 595";
$contact_phone_href = preg_replace("/\s+/", "", (string) $contact_phone);
$contact_email = trim((string) ($cfg["contact_email"] ?? "kontakt@upsellio.pl"));
$linkedin_url = "https://www.linkedin.com/in/kelm-sebastian/";
$site_url = home_url("/");
$offer_url = function_exists("upsellio_get_offer_page_url") ? (string) upsellio_get_offer_page_url() : "";
$google_ads_url = function_exists("upsellio_get_google_ads_page_url") ? (string) upsellio_get_google_ads_page_url() : "";
$meta_ads_url = function_exists("upsellio_get_meta_ads_page_url") ? (string) upsellio_get_meta_ads_page_url() : "";
$websites_url = function_exists("upsellio_get_websites_page_url") ? (string) upsellio_get_websites_page_url() : "";
$marketing_portfolio_url = function_exists("upsellio_get_marketing_portfolio_page_url") ? (string) upsellio_get_marketing_portfolio_page_url() : "";
$blog_index_url = function_exists("upsellio_get_blog_index_url") ? (string) upsellio_get_blog_index_url() : "";

$seo_title = trim((string) ($seo["title"] ?? "Upsellio — Marketing B2B, Google Ads, Meta Ads | Sebastian Kelm"));
$seo_description = trim((string) ($seo["description"] ?? "Marketing B2B nastawiony na leady i sprzedaż. Google Ads, Meta Ads i strony internetowe dla firm. Sebastian Kelm — praktyk sprzedaży i marketingu B2B."));
$seo_og_title = trim((string) ($seo["og_title"] ?? "Upsellio — marketing, który generuje klientów, nie kliknięcia"));
$seo_og_description = trim((string) ($seo["og_description"] ?? "Google Ads, Meta Ads, SEO i strony internetowe dla firm B2B. System marketingowy, który prowadzi od kliknięcia do zapytania."));
$seo_og_image = function_exists("upsellio_get_default_og_image_url") ? upsellio_get_default_og_image_url() : (get_template_directory_uri() . "/assets/images/upsellio-logo.png");

$brand_logo_assets = function_exists("upsellio_get_generated_logo_assets") ? upsellio_get_generated_logo_assets() : [];
$brand_logo_url = (string) ($brand_logo_assets["png"] ?? "");
$brand_logo_webp_320_url = (string) ($brand_logo_assets["webp_320"] ?? "");
$brand_logo_webp_640_url = (string) ($brand_logo_assets["webp_640"] ?? "");
if ($brand_logo_url === "") {
    $brand_logo_url = get_template_directory_uri() . "/assets/images/upsellio-logo.png";
}

$menu_links = function_exists("upsellio_get_primary_navigation_links") ? upsellio_get_primary_navigation_links() : [];
$top_level_links = array_values(array_filter($menu_links, static function ($item) {
    return (int) ($item["parent"] ?? 0) === 0;
}));

$hero_photo = function_exists("upsellio_render_home_media_image")
    ? upsellio_render_home_media_image("hero_portrait", [
        "class" => "home-hero-image",
        "size" => "medium_large",
        "sizes" => "(max-width: 980px) 92vw, 44vw",
        "loading" => "eager",
        "fetchpriority" => "high",
    ])
    : (function_exists("upsellio_render_template_asset_image")
        ? upsellio_render_template_asset_image("home_hero_photo", ["class" => "home-hero-image", "size" => "medium_large", "loading" => "eager"])
        : "");
$about_photo = function_exists("upsellio_render_home_media_image")
    ? upsellio_render_home_media_image("about_portrait", ["class" => "home-about-image", "size" => "large"])
    : "";
if ($about_photo === "" && function_exists("upsellio_render_template_asset_image")) {
    $about_photo = upsellio_render_template_asset_image("home_about_photo", ["class" => "home-about-image", "size" => "large"]);
}

$marquee_items = isset($cfg["industry_marquee"]) && is_array($cfg["industry_marquee"]) ? $cfg["industry_marquee"] : [
    "TCM SERVICE",
    "Upsellio",
    "Google Ads",
    "Meta Ads",
    "SEO",
    "B2B Sales",
    "E-commerce",
    "Lead generation",
    "CRO",
    "Landing pages",
];
$marquee_items = array_values(array_filter(array_map("trim", $marquee_items), static function ($item) {
    return $item !== "";
}));
if (empty($marquee_items)) {
    $marquee_items = ["Google Ads", "Meta Ads", "SEO"];
}

$lead_magnet = function_exists("upsellio_get_primary_lead_magnet") ? upsellio_get_primary_lead_magnet() : [];
$lead_magnet_id = (int) ($lead_magnet["id"] ?? 0);
$lead_magnet_title = trim((string) ($lead_magnet["title"] ?? "Mini-audyt sprzedaży z marketingu"));
$lead_magnet_pdf_url = $lead_magnet_id > 0 ? trim((string) get_post_meta($lead_magnet_id, "_ups_lm_pdf_url", true)) : "";
$lead_magnet_bullets = $lead_magnet_id > 0 && function_exists("upsellio_parse_textarea_lines")
    ? upsellio_parse_textarea_lines((string) get_post_meta($lead_magnet_id, "_ups_lm_bullets", true), 4)
    : ["strona i oferta", "Google Ads / Meta Ads", "formularze i CTA"];

$blog_query = new WP_Query([
    "post_type" => "post",
    "post_status" => "publish",
    "posts_per_page" => 3,
    "ignore_sticky_posts" => true,
]);

$cities_query = new WP_Query([
    "post_type" => "miasto",
    "post_status" => "publish",
    "posts_per_page" => 40,
    "orderby" => "title",
    "order" => "ASC",
    "no_found_rows" => true,
]);
$defs_query = new WP_Query([
    "post_type" => "definicja",
    "post_status" => "publish",
    "posts_per_page" => 40,
    "orderby" => "title",
    "order" => "ASC",
    "no_found_rows" => true,
]);

$schema = [
    "@context" => "https://schema.org",
    "@type" => "ProfessionalService",
    "name" => "Upsellio",
    "alternateName" => "Upsellio by Sebastian Kelm",
    "url" => $site_url,
    "email" => $contact_email,
    "telephone" => $contact_phone,
    "areaServed" => "Polska",
    "description" => "Marketing internetowy B2B, kampanie Meta Ads, Google Ads, SEO oraz strony internetowe dla firm.",
    "founder" => [
        "@type" => "Person",
        "name" => "Sebastian Kelm",
        "sameAs" => $linkedin_url,
    ],
    "sameAs" => [$linkedin_url],
];
add_filter("pre_get_document_title", static function ($title) use ($seo_title) {
    return is_front_page() && $seo_title !== "" ? $seo_title : $title;
});
add_action("wp_head", static function () use ($seo_description, $seo_og_title, $seo_og_description, $site_url, $seo_og_image, $schema) {
    if (!is_front_page()) {
        return;
    }
    echo '<meta name="description" content="' . esc_attr($seo_description) . '">' . "\n";
    echo '<link rel="canonical" href="' . esc_url($site_url) . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($seo_og_title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($seo_og_description) . '">' . "\n";
    echo '<meta property="og:type" content="website">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($site_url) . '">' . "\n";
    echo '<meta property="og:image" content="' . esc_url($seo_og_image) . '">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:image" content="' . esc_url($seo_og_image) . '">' . "\n";
    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
}, 1);

get_header();
?>
<style>
*{box-sizing:border-box}body{margin:0;font-family:"DM Sans",system-ui,sans-serif;color:#0a1410;background:#fafaf7;line-height:1.65}.hr-wrap{width:min(1180px,100% - 64px);margin-inline:auto}.hr-eyebrow{display:inline-flex;align-items:center;gap:10px;font-size:11px;font-weight:700;letter-spacing:1.6px;text-transform:uppercase;color:#0d9488;margin-bottom:14px}.hr-eyebrow::before{content:"";width:26px;height:2px;background:#0d9488;border-radius:99px}.hr-eyebrow-light{color:#5eead4}.hr-eyebrow-light::before{background:#5eead4}.hr-h1{font-family:"Syne",sans-serif;font-weight:700;font-size:clamp(48px,5.6vw,76px);line-height:.98;letter-spacing:-2.4px;margin:0 0 22px}.hr-h1 em{font-style:normal;color:#0d9488}.hr-h2{font-family:"Syne",sans-serif;font-weight:700;font-size:clamp(32px,3.6vw,52px);line-height:1.04;letter-spacing:-1.6px;margin:0 0 16px;max-width:24ch}.hr-h2-light{color:#fff}.hr-h3{font-family:"Syne",sans-serif;font-weight:700;font-size:21px;line-height:1.18;letter-spacing:-.4px;margin:0 0 10px}.hr-lead{font-size:18px;line-height:1.6;color:#3d3d38;max-width:60ch;margin:0}.hr-lead-light{color:rgba(255,255,255,.72)}.hr-divider{height:1px;background:#e7e7e1;margin:36px 0 56px}.hr-divider-light{background:rgba(255,255,255,.12)}.hr-sec-head{max-width:780px}.hr-section{padding:128px 0}.hr-section-soft{background:#f1f1ec}.hr-section-dark{background:#0a1410;color:#fff;position:relative;overflow:hidden}.hr-section-dark::before{content:"";position:absolute;width:560px;height:560px;border-radius:50%;background:radial-gradient(circle,rgba(20,184,166,.18),transparent 67%);right:-220px;top:-220px;pointer-events:none}.hr-section-dark .hr-wrap{position:relative;z-index:2}.hr-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border-radius:999px;padding:15px 24px;font-weight:700;font-size:15px;border:1px solid transparent;transition:.2s ease;text-decoration:none;cursor:pointer;font-family:inherit}.hr-btn-primary{background:#0d9488;color:#fff;box-shadow:0 12px 28px rgba(13,148,136,.22)}.hr-btn-ghost{background:#fff;border-color:#e7e7e1;color:#0a1410}.hr-btn-ghost-light{background:transparent;border-color:rgba(255,255,255,.24);color:#fff}.hr-btn-block{width:100%}.hr-nav{position:sticky;top:0;z-index:50;background:rgba(250,250,247,.92);backdrop-filter:blur(16px);border-bottom:1px solid rgba(231,231,225,.7)}.hr-nav-inner{height:74px;display:flex;align-items:center;justify-content:space-between;gap:24px}.hr-logo{display:flex;align-items:center;gap:10px}.hr-logo-mark{width:34px;height:34px;border-radius:10px;background:linear-gradient(180deg,#21ab82 0%,#0f766e 100%);color:#fff;display:grid;place-items:center;font-family:"Syne",sans-serif;font-weight:800;font-size:17px}.hr-logo-name{font-family:"Syne",sans-serif;font-size:20px;font-weight:800;letter-spacing:-.5px}.hr-nav-links{display:flex;gap:24px}.hr-nav-links a{font-size:14px;font-weight:600;color:#3d3d38;text-decoration:none}.hr-nav-cta{display:flex;align-items:center;gap:14px}.hr-link-phone{font-size:13px;font-weight:700;color:#0a1410;text-decoration:none}.hr-hero{padding:96px 0 0;background:radial-gradient(circle at 88% 8%,rgba(13,148,136,.12),transparent 36%)}.hr-hero-grid{display:grid;grid-template-columns:1.05fr .95fr;gap:64px;align-items:center}.hr-actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:30px}.hr-micro{list-style:none;padding:0;margin:36px 0 0;display:grid;grid-template-columns:repeat(3,1fr);gap:14px}.hr-micro li{display:flex;align-items:flex-start;gap:8px;font-size:13px;color:#3d3d38}.hr-check{flex:0 0 22px;width:22px;height:22px;border-radius:50%;display:grid;place-items:center;background:#ccfbf1;color:#0f766e;font-weight:900;font-size:11px}.hr-hero-side{position:relative}.hr-hero-photo{position:relative;aspect-ratio:.82;border-radius:28px;overflow:hidden;background:#dff8f4;border:1px solid #99f6e4}.hr-photo-stripes{position:absolute;inset:0;background-image:repeating-linear-gradient(135deg,rgba(13,148,136,.12) 0 12px,transparent 12px 24px)}.hr-photo-label{position:absolute;inset:0;display:grid;place-items:center;font-family:ui-monospace,monospace;color:#0f766e;font-size:13px;letter-spacing:1px}.home-hero-image{width:100%;height:100%;object-fit:cover}.home-about-image{width:100%;height:100%;object-fit:cover}.hr-hero-stat{position:absolute;background:#fff;border:1px solid #e7e7e1;border-radius:18px;padding:14px 18px;box-shadow:0 12px 28px rgba(15,23,42,.06)}.hr-hero-stat b{display:block;font-family:"Syne",sans-serif;font-size:24px;color:#0d9488;line-height:1}.hr-hero-stat span{display:block;font-size:12px;color:#7c7c74;margin-top:3px}.hr-hero-stat-tl{left:-16px;top:36px}.hr-hero-stat-br{right:-16px;bottom:48px}.hr-proof{margin-top:96px;display:grid;grid-template-columns:repeat(3,1fr);border-top:1px solid #e7e7e1;border-bottom:1px solid #e7e7e1}.hr-proof-cell{padding:32px 24px;text-align:center;border-right:1px solid #e7e7e1}.hr-proof-cell:last-child{border-right:0}.hr-proof-cell b{display:block;font-family:"Syne",sans-serif;font-size:42px;color:#0d9488;letter-spacing:-1.4px;line-height:1;font-weight:700}.hr-proof-cell span{display:block;color:#3d3d38;font-size:14px;margin-top:8px;max-width:32ch;margin-inline:auto}.hr-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}.hr-card{background:#fff;border:1px solid #e7e7e1;border-radius:20px;padding:32px}.hr-card-num{font-family:ui-monospace,monospace;font-size:12px;color:#0d9488;letter-spacing:1.4px;margin-bottom:24px}.hr-card-body{color:#3d3d38;font-size:15px;line-height:1.6}.hr-list{list-style:none;padding:0;margin:18px 0;display:grid;gap:9px}.hr-list li{display:flex;gap:8px;color:#3d3d38;font-size:14px}.hr-list li::before{content:"✓";color:#0d9488;font-weight:900}.hr-list-2col{grid-template-columns:1fr 1fr;gap:9px 24px}.hr-card-link{display:inline-flex;color:#0d9488;font-weight:700;font-size:14px;text-decoration:none;margin-top:8px}.hr-cases{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}.hr-case{background:rgba(255,255,255,.045);border:1px solid rgba(255,255,255,.12);border-radius:20px;padding:32px}.hr-case-tag{font-size:11px;letter-spacing:1.4px;text-transform:uppercase;color:rgba(255,255,255,.5);margin-bottom:24px}.hr-case-num{font-family:"Syne",sans-serif;font-size:46px;color:#5eead4;letter-spacing:-1.6px;line-height:1;font-weight:700}.hr-case-label{color:#fff;margin:14px 0 8px;font-size:14px}.hr-case p{color:rgba(255,255,255,.65);font-size:14px;margin:0 0 18px}.hr-case a{color:#5eead4;font-weight:700;font-size:14px;text-decoration:none}.hr-process{list-style:none;padding:0;margin:0;display:grid;grid-template-columns:repeat(4,1fr);gap:14px}.hr-step{background:#fff;border:1px solid #e7e7e1;border-radius:20px;padding:28px;position:relative}.hr-step-num{width:38px;height:38px;border-radius:50%;background:#ccfbf1;color:#0f766e;font-family:"Syne",sans-serif;font-weight:800;display:grid;place-items:center;margin-bottom:18px}.hr-step p{color:#3d3d38;font-size:14px;margin:0}.hr-leadbox{background:#fff;border:1px solid #e7e7e1;border-radius:28px;padding:48px;display:grid;grid-template-columns:.85fr 1.15fr 1fr;gap:42px;align-items:center;box-shadow:0 24px 60px rgba(15,23,42,.06)}.hr-leadbox-cover{display:grid;place-items:center}.hr-book{width:200px;aspect-ratio:.72;background:linear-gradient(165deg,#0f766e 0%,#0a1410 100%);border-radius:6px 14px 14px 6px;padding:24px 22px;color:#fff;display:flex;flex-direction:column;justify-content:space-between}.hr-book-tag{font-size:10px;letter-spacing:1.4px;text-transform:uppercase;color:#5eead4}.hr-book-title{font-family:"Syne",sans-serif;font-size:22px;line-height:1.05;letter-spacing:-.6px;font-weight:700}.hr-book-foot{font-size:11px;letter-spacing:1.2px;text-transform:uppercase;color:rgba(255,255,255,.5)}.hr-leadbox-form{display:grid;gap:10px}.hr-leadbox-form label{font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:#7c7c74;display:block}.hr-leadbox-form input[type=text],.hr-leadbox-form input[type=email]{display:block;width:100%;border:1.5px solid #e7e7e1;background:#fafaf7;border-radius:12px;padding:12px 14px;margin-top:6px;font:inherit;outline:none}.hr-consent{display:flex !important;gap:8px;align-items:flex-start;text-transform:none !important;letter-spacing:0 !important;font-size:12px !important;color:#7c7c74 !important;font-weight:400 !important;line-height:1.5;margin-top:6px}.hr-fineprint{font-size:12px;color:#7c7c74;margin:0;text-align:center}.hr-split{display:grid;grid-template-columns:.85fr 1.15fr;gap:64px;align-items:center}.hr-about-photo{position:relative;aspect-ratio:.85;border-radius:28px;overflow:hidden;background:#ccfbf1;border:1px solid #99f6e4}.hr-mini-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin:32px 0}.hr-mini-grid>div{background:#fff;border:1px solid #e7e7e1;border-radius:16px;padding:18px}.hr-mini-grid b{display:block;font-family:"Syne",sans-serif;font-size:28px;color:#0d9488;line-height:1;font-weight:700}.hr-mini-grid span{display:block;font-size:13px;color:#7c7c74;margin-top:4px}.hr-blog-grid{display:grid;gap:18px;grid-template-columns:repeat(3,1fr)}.hr-post{background:#fff;border:1px solid #e7e7e1;border-radius:20px;overflow:hidden}.hr-thumb{height:180px;background:#e7f8f5}.hr-thumb img{width:100%;height:100%;object-fit:cover}.hr-post-body{padding:20px}.hr-post-body small{font-size:12px;color:#7c7c74}.hr-post-body h3{margin:8px 0 12px;font-family:"Syne",sans-serif;font-size:20px;line-height:1.2}.hr-post-body a{font-size:14px;color:#0d9488;font-weight:700;text-decoration:none}.hr-cta-band{background:#0a1410;color:#fff;padding:80px 0;position:relative;overflow:hidden}.hr-cta-band::before{content:"";position:absolute;width:600px;height:600px;border-radius:50%;background:radial-gradient(circle,rgba(20,184,166,.2),transparent 65%);left:-200px;bottom:-300px;pointer-events:none}.hr-cta-inner{position:relative;display:grid;grid-template-columns:1.4fr 1fr;gap:48px;align-items:center}.hr-cta-actions{display:flex;gap:12px;flex-wrap:wrap;justify-content:flex-end}@media(max-width:1060px){.hr-nav-links,.hr-nav-cta{display:none}.hr-hero-grid,.hr-split,.hr-cta-inner,.hr-leadbox{grid-template-columns:1fr}.hr-cards,.hr-cases,.hr-process,.hr-blog-grid{grid-template-columns:1fr 1fr}.hr-proof{grid-template-columns:1fr}}@media(max-width:760px){.hr-wrap{width:min(1180px,100% - 32px)}.hr-cards,.hr-cases,.hr-process,.hr-blog-grid,.hr-micro,.hr-list-2col,.hr-mini-grid{grid-template-columns:1fr}.hr-proof-cell{border-right:0;border-bottom:1px solid #e7e7e1}.hr-proof-cell:last-child{border-bottom:0}}
</style>
<div class="hr-art">
  <section class="hr-hero">
    <div class="hr-wrap hr-hero-grid">
      <div class="hr-hero-copy">
        <div class="hr-eyebrow">Marketing B2B · Google Ads · Meta Ads · WWW</div>
        <h1 class="hr-h1">Reklamy klikają,<br />ale <em>nie sprzedają?</em></h1>
        <p class="hr-lead">Pomagam firmom i e-commerce poukładać Google Ads, Meta Ads i stronę w jeden system, który generuje wartościowe zapytania — nie tylko ruch i raporty.</p>
        <div class="hr-actions">
          <a class="hr-btn hr-btn-primary" href="#kontakt">Sprawdź, co blokuje sprzedaż →</a>
          <a class="hr-btn hr-btn-ghost" href="#wyniki">Zobacz wyniki klientów</a>
        </div>
        <ul class="hr-micro">
          <li><span class="hr-check">✓</span>Bezpłatna diagnoza, bez zobowiązań</li>
          <li><span class="hr-check">✓</span>Bez pośredników — rozmawiasz ze mną</li>
          <li><span class="hr-check">✓</span>Strategia oparta na sprzedaży, nie domysłach</li>
        </ul>
      </div>
      <aside class="hr-hero-side">
        <div class="hr-hero-photo" aria-hidden="true">
          <?php echo $hero_photo !== "" ? $hero_photo : '<div class="hr-photo-stripes"></div><div class="hr-photo-label">[ portrait — Sebastian Kelm ]</div>'; ?>
        </div>
        <div class="hr-hero-stat hr-hero-stat-tl"><b>10+</b><span>lat sprzedaży B2B</span></div>
        <div class="hr-hero-stat hr-hero-stat-br"><b>3×</b><span>wyższa marża e-com</span></div>
      </aside>
    </div>
    <div class="hr-wrap hr-proof" id="wyniki">
      <div class="hr-proof-cell"><b>1 mln zł</b><span>miesięcznej sprzedaży B2B w kanale tradycyjnym</span></div>
      <div class="hr-proof-cell"><b>500 tys. zł</b><span>miesięcznie w nowym kanale e-commerce</span></div>
      <div class="hr-proof-cell"><b>15 osób</b><span>w prowadzonym zespole sprzedaży</span></div>
    </div>
  </section>

  <section class="hr-section">
    <div class="hr-wrap">
      <header class="hr-sec-head">
        <div class="hr-eyebrow">Usługi</div>
        <h2 class="hr-h2">Nie sprzedaję „kampanii”. Buduję system, który dowozi zapytania.</h2>
        <p class="hr-lead">Największy problem firm nie leży w pojedynczej reklamie. Najczęściej brakuje spójności: oferta → komunikat → ruch → landing → formularz → sprzedaż.</p>
      </header>
      <div class="hr-divider"></div>
      <div class="hr-cards">
        <article class="hr-card"><div class="hr-card-num">01</div><h3 class="hr-h3">Google Ads nastawione na sprzedaż</h3><p class="hr-card-body">Search i Performance Max pod zapytania z realną intencją zakupową.</p><ul class="hr-list"><li>analiza intencji i słów</li><li>struktura kampanii pod CPL</li><li>landing pages pod konwersję</li><li>optymalizacja jakości leadów</li></ul><?php if ($google_ads_url !== "") : ?><a class="hr-card-link" href="<?php echo esc_url($google_ads_url); ?>">Zobacz szczegóły →</a><?php endif; ?></article>
        <article class="hr-card"><div class="hr-card-num">02</div><h3 class="hr-h3">Meta Ads, które budują popyt</h3><p class="hr-card-body">Lejki na Facebooku i Instagramie: od pierwszego kontaktu po remarketing.</p><ul class="hr-list"><li>kreacje ToF / MoF / BoF</li><li>testy komunikatów i ofert</li><li>remarketing do zaangażowanych</li><li>kampanie sprzedażowe i leadowe</li></ul><?php if ($meta_ads_url !== "") : ?><a class="hr-card-link" href="<?php echo esc_url($meta_ads_url); ?>">Zobacz szczegóły →</a><?php endif; ?></article>
        <article class="hr-card"><div class="hr-card-num">03</div><h3 class="hr-h3">Strony, które zamieniają ruch w leady</h3><p class="hr-card-body">Strony firmowe i landing pages jako narzędzia sprzedażowe — nie wizytówki.</p><ul class="hr-list"><li>copywriting pod decyzję klienta</li><li>sekcje zaufania i obiekcji</li><li>SEO-ready struktura</li><li>CTA i formularze pod leady</li></ul><?php if ($websites_url !== "") : ?><a class="hr-card-link" href="<?php echo esc_url($websites_url); ?>">Zobacz szczegóły →</a><?php endif; ?></article>
      </div>
    </div>
  </section>

  <section class="hr-section hr-section-dark">
    <div class="hr-wrap">
      <header class="hr-sec-head">
        <div class="hr-eyebrow hr-eyebrow-light">Wyniki</div>
        <h2 class="hr-h2 hr-h2-light">Najpierw diagnoza. Potem lejek. Skalowanie budżetu na końcu.</h2>
        <p class="hr-lead hr-lead-light">Wynik nie bierze się z jednej reklamy — bierze się z połączenia strategii, kreacji, landing page'a, analityki i procesu sprzedaży.</p>
      </header>
      <div class="hr-divider hr-divider-light"></div>
      <div class="hr-cases">
        <article class="hr-case"><div class="hr-case-tag">B2B / sprzedaż tradycyjna</div><div class="hr-case-num">1 mln zł</div><div class="hr-case-label">miesięcznego przychodu</div><p>Ułożenie procesu sprzedaży, pracy zespołu i analizy wyników w tradycyjnym kanale B2B.</p><?php if ($marketing_portfolio_url !== "") : ?><a href="<?php echo esc_url($marketing_portfolio_url); ?>">Zobacz case →</a><?php endif; ?></article>
        <article class="hr-case"><div class="hr-case-tag">E-commerce / nowy kanał</div><div class="hr-case-num">500k zł</div><div class="hr-case-label">miesięcznie od zera</div><p>Budowa nowego kanału sprzedaży online z wyższą marżą niż sprzedaż tradycyjna.</p><?php if ($marketing_portfolio_url !== "") : ?><a href="<?php echo esc_url($marketing_portfolio_url); ?>">Zobacz case →</a><?php endif; ?></article>
        <article class="hr-case"><div class="hr-case-tag">Lead generation</div><div class="hr-case-num">−30%</div><div class="hr-case-label">spadek CPL</div><p>Najczęściej osiągany przez poprawę komunikatu, formularza, landingu i remarketingu.</p><a href="#kontakt">Chcę diagnozę →</a></article>
      </div>
    </div>
  </section>

  <section class="hr-section">
    <div class="hr-wrap">
      <header class="hr-sec-head"><div class="hr-eyebrow">Jak pracuję</div><h2 class="hr-h2">Zaczynam od pytania: dlaczego klient miałby kupić właśnie od Ciebie?</h2></header>
      <div class="hr-divider"></div>
      <ol class="hr-process">
        <li class="hr-step"><div class="hr-step-num">1</div><h3 class="hr-h3">Diagnoza</h3><p>Sprawdzam stronę, ofertę, kampanie, komunikat i drogę klienta.</p></li>
        <li class="hr-step"><div class="hr-step-num">2</div><h3 class="hr-h3">Plan</h3><p>Układam priorytety: co poprawić najpierw, by zwiększyć liczbę zapytań.</p></li>
        <li class="hr-step"><div class="hr-step-num">3</div><h3 class="hr-h3">Wdrożenie</h3><p>Poprawiam kampanie, strony, landing pages, formularze, komunikaty.</p></li>
        <li class="hr-step"><div class="hr-step-num">4</div><h3 class="hr-h3">Optymalizacja</h3><p>Analizuję dane, testuję wersje i skaluję to, co dowozi wynik.</p></li>
      </ol>
    </div>
  </section>

  <section class="hr-section hr-section-soft">
    <div class="hr-wrap">
      <div class="hr-leadbox">
        <div class="hr-leadbox-cover"><div class="hr-book"><span class="hr-book-tag">PDF · 12 stron</span><span class="hr-book-title"><?php echo esc_html($lead_magnet_title); ?></span><span class="hr-book-foot">Upsellio · 2026</span></div></div>
        <div class="hr-leadbox-copy">
          <div class="hr-eyebrow">Darmowy materiał</div>
          <h2 class="hr-h2">Sprawdź, co blokuje sprzedaż z Twojej strony lub reklam.</h2>
          <p class="hr-lead">Dostaniesz krótką listę 12 błędów, które najczęściej blokują leady w Google Ads, Meta Ads i na stronach B2B.</p>
          <ul class="hr-list hr-list-2col"><?php foreach ($lead_magnet_bullets as $bullet) : ?><li><?php echo esc_html((string) $bullet); ?></li><?php endforeach; ?></ul>
        </div>
        <form class="hr-leadbox-form" id="mini-audit-form" method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" data-upsellio-server-form="1">
          <input type="hidden" name="action" value="upsellio_submit_lead" /><input type="hidden" name="redirect_url" value="<?php echo esc_url(home_url("/#mini-audit-form")); ?>" /><input type="hidden" name="lead_form_origin" value="home-lead-magnet" /><input type="hidden" name="lead_source" value="home-lead-magnet" /><input type="hidden" name="lead_service" value="<?php echo esc_attr($lead_magnet_title); ?>" /><input type="hidden" name="lead_message" value="<?php echo esc_attr("Pobranie materiału: " . $lead_magnet_title); ?>" /><input type="hidden" name="lead_magnet_name" value="<?php echo esc_attr($lead_magnet_title); ?>" /><input type="hidden" name="landing_url" value="" data-ups-context="landing" /><input type="hidden" name="referrer" value="" data-ups-context="referrer" /><input type="text" name="lead_website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;" /><?php wp_nonce_field("upsellio_unified_lead_form", "upsellio_lead_form_nonce"); ?>
          <label>Imię<input type="text" name="lead_name" placeholder="Sebastian" required /></label>
          <label>E-mail firmowy<input type="email" name="lead_email" placeholder="kontakt@firma.pl" required /></label>
          <label class="hr-consent"><input type="checkbox" name="lead_consent" value="1" required />Wyrażam zgodę na kontakt w sprawie pobranego materiału.</label>
          <button class="hr-btn hr-btn-primary hr-btn-block" type="submit">Pobierz mini-audyt →</button>
          <p class="hr-fineprint">Po zapisaniu PDF pobierze się automatycznie.</p>
          <div class="form-feedback" data-form-feedback></div>
          <input type="hidden" id="lead-magnet-pdf-url" value="<?php echo esc_url($lead_magnet_pdf_url); ?>">
        </form>
      </div>
    </div>
  </section>

  <section class="hr-section">
    <div class="hr-wrap hr-split">
      <div class="hr-about-photo"><?php echo $about_photo !== "" ? $about_photo : '<div class="hr-photo-stripes"></div><div class="hr-photo-label">[ environmental portrait ]</div>'; ?></div>
      <div><div class="hr-eyebrow">O mnie</div><h2 class="hr-h2">Nie agencja od ładnych raportów. Praktyk sprzedaży.</h2><p class="hr-lead">Łączę marketing, sprzedaż B2B, e-commerce i tworzenie stron, bo w praktyce te rzeczy muszą działać razem. Ruch bez konwersji nie ma sensu. Leady bez jakości też nie.</p><div class="hr-mini-grid"><div><b>10+</b><span>lat doświadczenia B2B</span></div><div><b>15</b><span>osób w zespole sprzedaży</span></div><div><b>SEO</b><span>content + struktura strony</span></div><div><b>CRO</b><span>strony pod konwersję</span></div></div><a class="hr-btn hr-btn-primary" href="#kontakt">Porozmawiajmy o Twoim marketingu →</a></div>
    </div>
  </section>

  <section class="hr-section" id="blog">
    <div class="hr-wrap">
      <header class="hr-sec-head"><div class="hr-eyebrow">Wiedza</div><h2 class="hr-h2">Praktyczne artykuły dla firm, które chcą więcej leadów.</h2></header>
      <div class="hr-divider"></div>
      <div class="hr-blog-grid">
        <?php if ($blog_query->have_posts()) : while ($blog_query->have_posts()) : $blog_query->the_post(); ?>
          <article class="hr-post"><div class="hr-thumb"><?php if (has_post_thumbnail()) { the_post_thumbnail("medium_large", ["loading" => "lazy", "decoding" => "async"]); } ?></div><div class="hr-post-body"><?php $cats = get_the_category(); ?><small><?php echo !empty($cats) ? esc_html((string) $cats[0]->name) : "Blog"; ?></small><h3><?php echo esc_html(get_the_title()); ?></h3><a href="<?php echo esc_url(get_permalink()); ?>">Czytaj więcej →</a></div></article>
        <?php endwhile; wp_reset_postdata(); endif; ?>
      </div>
    </div>
  </section>

  <section class="hr-cta-band">
    <div class="hr-wrap hr-cta-inner">
      <div><div class="hr-eyebrow hr-eyebrow-light">Bezpłatna diagnoza</div><h2 class="hr-h2 hr-h2-light">15 minut rozmowy. Konkretny kierunek, gdzie tracisz leady.</h2></div>
      <div class="hr-cta-actions"><a class="hr-btn hr-btn-primary" href="<?php echo esc_url("mailto:" . $contact_email); ?>">Umów rozmowę →</a><a class="hr-btn hr-btn-ghost-light" href="<?php echo esc_url("tel:" . $contact_phone_href); ?>"><?php echo esc_html($contact_phone); ?></a></div>
    </div>
  </section>

  <section class="hr-section hr-section-soft" id="kontakt">
    <style>
      .hr-contact-shell{background:#fff;border:1px solid #e7e7e1;border-radius:28px;padding:clamp(24px,4vw,44px);box-shadow:0 18px 44px rgba(15,23,42,.06)}
      .hr-contact-head{max-width:780px;margin:0 auto 24px;text-align:center}
      .hr-contact-head .hr-h2{margin-inline:auto}
      .hr-contact-form{max-width:920px;margin:0 auto}
      .hr-contact-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
      .hr-contact-field{display:grid;gap:6px}
      .hr-contact-field.full{grid-column:1 / -1}
      .hr-contact-field label{font-size:12px;font-weight:700;color:#3d3d38}
      .hr-contact-field input,.hr-contact-field textarea,.hr-contact-field select{width:100%;border:1.5px solid #e7e7e1;border-radius:12px;min-height:46px;padding:12px 14px;font:inherit;color:#0a1410;background:#fff;outline:none;transition:border-color .18s ease,box-shadow .18s ease}
      .hr-contact-field textarea{min-height:120px;resize:vertical;line-height:1.6}
      .hr-contact-field input:focus,.hr-contact-field textarea:focus,.hr-contact-field select:focus{border-color:#0d9488;box-shadow:0 0 0 3px rgba(13,148,136,.13)}
      .hr-contact-consent{display:flex !important;gap:8px;align-items:flex-start}
      .hr-contact-consent input{margin-top:3px;width:auto;min-height:auto}
      .hr-contact-submit{width:100%;justify-content:center;margin-top:10px}
      .hr-contact-note{margin-top:10px;color:#7c7c74;font-size:12px;text-align:center}
      @media(max-width:760px){.hr-contact-grid{grid-template-columns:1fr}}
    </style>
    <div class="hr-wrap">
      <div class="hr-contact-shell">
        <div class="hr-contact-head">
          <div class="hr-eyebrow">Sprawdź, co blokuje sprzedaż</div>
          <h2 class="hr-h2">Napisz, co dziś nie działa — wrócę z konkretną rekomendacją pierwszego kroku.</h2>
          <p class="hr-lead">Bezpłatna diagnoza dotyczy kampanii, strony i procesu pozyskiwania leadów. Bez presji sprzedażowej, z naciskiem na szybkie wskazanie największej dźwigni wzrostu.</p>
        </div>
        <form class="hr-contact-form" method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" data-upsellio-lead-form="1" data-upsellio-server-form="1">
          <input type="hidden" name="action" value="upsellio_submit_lead" />
          <input type="hidden" name="redirect_url" value="<?php echo esc_url(home_url("/#kontakt")); ?>" />
          <input type="hidden" name="lead_form_origin" value="home-contact-form" />
          <input type="hidden" name="lead_source" value="home-contact-form" />
          <input type="hidden" name="lead_service" value="Bezpłatna diagnoza marketingu" />
          <input type="hidden" name="utm_source" data-ups-utm="source" value="" />
          <input type="hidden" name="utm_medium" data-ups-utm="medium" value="" />
          <input type="hidden" name="utm_campaign" data-ups-utm="campaign" value="" />
          <input type="hidden" name="landing_url" data-ups-context="landing" value="" />
          <input type="hidden" name="referrer" data-ups-context="referrer" value="" />
          <input type="text" name="lead_website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;" />
          <?php wp_nonce_field("upsellio_unified_lead_form", "upsellio_lead_form_nonce"); ?>
          <div class="hr-contact-grid">
            <div class="hr-contact-field">
              <label for="hr-contact-name">Imię i firma *</label>
              <input id="hr-contact-name" name="lead_name" type="text" autocomplete="name organization" required />
            </div>
            <div class="hr-contact-field">
              <label for="hr-contact-email">E-mail służbowy *</label>
              <input id="hr-contact-email" name="lead_email" type="email" autocomplete="email" required />
            </div>
            <div class="hr-contact-field">
              <label for="hr-contact-phone">Telefon (opcjonalnie)</label>
              <input id="hr-contact-phone" name="lead_phone" type="tel" autocomplete="tel" />
            </div>
            <div class="hr-contact-field">
              <label for="hr-contact-scope">Zakres wsparcia</label>
              <select id="hr-contact-scope" name="lead_message_context">
                <option value="Nie wiem, co blokuje wynik">Nie wiem, co blokuje wynik</option>
                <option value="Google Ads">Google Ads</option>
                <option value="Meta Ads">Meta Ads</option>
                <option value="Strona WWW / landing page">Strona WWW / landing page</option>
                <option value="Lejek i jakość leadów">Lejek i jakość leadów</option>
              </select>
            </div>
            <div class="hr-contact-field full">
              <label for="hr-contact-message">Co dokładnie dziś nie działa? *</label>
              <textarea id="hr-contact-message" name="lead_message" required placeholder="Np. mamy ruch z reklam, ale mało zapytań; leady są słabej jakości; strona nie konwertuje."></textarea>
            </div>
            <div class="hr-contact-field full">
              <label class="hr-contact-consent">
                <input type="checkbox" name="lead_consent" value="1" required />
                <span>Wyrażam zgodę na kontakt w sprawie mojego zapytania.</span>
              </label>
            </div>
          </div>
          <button class="hr-btn hr-btn-primary hr-contact-submit" type="submit">Sprawdź, co blokuje sprzedaż →</button>
          <p class="hr-contact-note">Możesz też napisać bezpośrednio: <a href="<?php echo esc_url("mailto:" . $contact_email); ?>"><?php echo esc_html($contact_email); ?></a></p>
        </form>
      </div>
    </div>
  </section>
</div>
<script>
document.querySelectorAll('[data-ups-context="landing"]').forEach((f)=>{f.value=window.location.href});document.querySelectorAll('[data-ups-context="referrer"]').forEach((f)=>{f.value=document.referrer||''});
function ajaxLeadForm(form, onSuccess){const submit=form.querySelector('button[type="submit"]');const feedback=form.querySelector('[data-form-feedback]');const defaultText=submit?submit.textContent:'';if(submit){submit.disabled=true;submit.textContent='Wysyłanie...';}if(feedback){feedback.classList.remove('is-success','is-error');feedback.style.display='none';feedback.textContent='';}fetch(form.action,{method:form.method||'POST',body:new FormData(form),credentials:'same-origin',redirect:'follow'}).then((response)=>{if(!response.ok||(response.url&&response.url.includes('ups_lead_status=error')))throw new Error('Nie udało się wysłać formularza.');if(feedback){feedback.textContent='Dziękuję! Formularz został wysłany.';feedback.classList.add('is-success');}if(typeof onSuccess==='function')onSuccess();form.reset();}).catch((error)=>{if(feedback){feedback.textContent=error.message||'Błąd wysyłki.';feedback.classList.add('is-error');}}).finally(()=>{if(submit){submit.disabled=false;submit.textContent=defaultText;}});}
const miniForm=document.getElementById('mini-audit-form');if(miniForm){miniForm.addEventListener('submit',(e)=>{e.preventDefault();ajaxLeadForm(miniForm,()=>{const pdf=document.getElementById('lead-magnet-pdf-url');const url=pdf?pdf.value:'';if(url){window.open(url,'_blank','noopener');}});});}
</script>
<?php get_footer(); ?>
