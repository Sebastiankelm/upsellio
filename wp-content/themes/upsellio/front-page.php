<?php
/*
Template Name: Upsellio - Strona Glowna
Template Post Type: page
*/
if (!defined("ABSPATH")) {
    exit;
}

$front_page_sections = function_exists("upsellio_get_front_page_content_config")
    ? upsellio_get_front_page_content_config()
    : [];
$front_page_issues = function_exists("upsellio_get_front_page_data_issues")
    ? upsellio_get_front_page_data_issues()
    : [];
$front_nav_links = function_exists("upsellio_get_primary_navigation_links")
    ? upsellio_get_primary_navigation_links()
    : [];
$hero_section = isset($front_page_sections["hero"]) && is_array($front_page_sections["hero"])
    ? $front_page_sections["hero"]
    : [];
$faq_items = isset($front_page_sections["faq_items"]) && is_array($front_page_sections["faq_items"])
    ? $front_page_sections["faq_items"]
    : [];
$why_section = isset($front_page_sections["why"]) && is_array($front_page_sections["why"])
    ? $front_page_sections["why"]
    : [];
$services_section = isset($front_page_sections["services"]) && is_array($front_page_sections["services"])
    ? $front_page_sections["services"]
    : [];
$results_section = isset($front_page_sections["results"]) && is_array($front_page_sections["results"])
    ? $front_page_sections["results"]
    : [];
$fit_section = isset($front_page_sections["fit"]) && is_array($front_page_sections["fit"])
    ? $front_page_sections["fit"]
    : [];
$problem_section = isset($front_page_sections["problem"]) && is_array($front_page_sections["problem"])
    ? $front_page_sections["problem"]
    : [];
$process_section = isset($front_page_sections["process"]) && is_array($front_page_sections["process"])
    ? $front_page_sections["process"]
    : [];
$cta_band_section = isset($front_page_sections["cta_band"]) && is_array($front_page_sections["cta_band"])
    ? $front_page_sections["cta_band"]
    : [];
$seo_section = isset($front_page_sections["seo"]) && is_array($front_page_sections["seo"])
    ? $front_page_sections["seo"]
    : [];
$contact_service_options = isset($front_page_sections["contact_service_options"]) && is_array($front_page_sections["contact_service_options"])
    ? $front_page_sections["contact_service_options"]
    : [];
$contact_phone = function_exists("upsellio_get_contact_phone")
    ? upsellio_get_contact_phone()
    : trim((string) ($front_page_sections["contact_phone"] ?? ""));
$contact_email = trim((string) ($front_page_sections["contact_email"] ?? ""));
$contact_email = $contact_email !== "" ? $contact_email : "kontakt@upsellio.pl";
$brand_logo_url = get_template_directory_uri() . "/assets/images/upsellio-logo.png";
$contact_phone_href = preg_replace("/\s+/", "", (string) $contact_phone);

$seo_title = trim((string) ($seo_section["title"] ?? ""));
$seo_description = trim((string) ($seo_section["description"] ?? ""));
if ($seo_title === "" || $seo_title === "Marketing B2B i strony WWW, które sprzedają | Upsellio") {
    $seo_title = "Marketing B2B, Google Ads i Meta Ads | Upsellio";
}
if ($seo_description === "" || $seo_description === "Kampanie Meta Ads, Google Ads i strony internetowe dla firm B2B. Ponad 10 lat praktyki. Bezpłatna analiza strony i konsultacja bez zobowiązań.") {
    $seo_description = "Prowadzę kampanie Google Ads, Meta Ads i tworzę strony WWW nastawione na leady. Marketing B2B, CRO i sprzedaż w jednym procesie.";
}
if (function_exists("upsellio_limit_meta_description")) {
    $seo_description = upsellio_limit_meta_description($seo_description, 160);
}
$seo_og_title = trim((string) ($seo_section["og_title"] ?? ""));
$seo_og_description = trim((string) ($seo_section["og_description"] ?? ""));
if ($seo_og_title === "" || $seo_og_title === "Marketing B2B i strony WWW, które sprzedają | Upsellio") {
    $seo_og_title = $seo_title;
}
if ($seo_og_description === "" || $seo_og_description === "Kampanie Meta Ads, Google Ads i strony internetowe dla firm B2B. Ponad 10 lat praktyki. Bezpłatna analiza strony i konsultacja bez zobowiązań.") {
    $seo_og_description = $seo_description;
}
$seo_og_type = trim((string) ($seo_section["og_type"] ?? "website"));
$seo_og_url = trim((string) ($seo_section["og_url"] ?? "/"));
$seo_twitter_card = trim((string) ($seo_section["twitter_card"] ?? "summary_large_image"));
$seo_og_image = function_exists("upsellio_get_default_og_image_url") ? upsellio_get_default_og_image_url() : get_template_directory_uri() . "/assets/images/upsellio-logo.png";
$seo_site_name = "Upsellio";
$seo_schema = [
    "@context" => "https://schema.org",
    "@type" => trim((string) ($seo_section["schema_type"] ?? "ProfessionalService")),
    "name" => trim((string) ($seo_section["schema_name"] ?? get_bloginfo("name"))),
    "url" => home_url(trim((string) ($seo_section["schema_url"] ?? "/"))),
    "email" => trim((string) ($seo_section["schema_email"] ?? "")),
    "description" => trim((string) ($seo_section["schema_description"] ?? "")),
    "founder" => [
        "@type" => "Person",
        "name" => trim((string) ($seo_section["schema_founder_name"] ?? "")),
    ],
];
$upsellio_css_path = get_template_directory() . "/assets/css/upsellio.css";
$upsellio_css_version = file_exists($upsellio_css_path) ? (string) filemtime($upsellio_css_path) : "1.0.0";
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo("charset"); ?>" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <?php if ($seo_title !== "") : ?><title><?php echo esc_html($seo_title); ?></title><?php endif; ?>
  <?php if ($seo_description !== "") : ?><meta name="description" content="<?php echo esc_attr($seo_description); ?>" /><?php endif; ?>
  <?php if ($seo_og_title !== "") : ?><meta property="og:title" content="<?php echo esc_attr($seo_og_title); ?>" /><?php endif; ?>
  <?php if ($seo_og_description !== "") : ?><meta property="og:description" content="<?php echo esc_attr($seo_og_description); ?>" /><?php endif; ?>
  <meta property="og:type" content="<?php echo esc_attr($seo_og_type !== "" ? $seo_og_type : "website"); ?>" />
  <meta property="og:url" content="<?php echo esc_url(home_url($seo_og_url !== "" ? $seo_og_url : "/")); ?>" />
  <meta property="og:site_name" content="<?php echo esc_attr($seo_site_name); ?>" />
  <meta property="og:image" content="<?php echo esc_url($seo_og_image); ?>" />
  <meta name="twitter:card" content="<?php echo esc_attr($seo_twitter_card !== "" ? $seo_twitter_card : "summary_large_image"); ?>" />
  <meta name="twitter:title" content="<?php echo esc_attr($seo_og_title !== "" ? $seo_og_title : $seo_title); ?>" />
  <meta name="twitter:description" content="<?php echo esc_attr($seo_og_description !== "" ? $seo_og_description : $seo_description); ?>" />
  <meta name="twitter:image" content="<?php echo esc_url($seo_og_image); ?>" />

  <script type="application/ld+json"><?php echo wp_json_encode($seo_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
  <?php
  $front_faq_schema_items = !empty($faq_items) ? $faq_items : [
      [
          "question" => "Co konkretnie zmienia się na stronie po takiej współpracy?",
          "answer" => "Najczęściej porządkuję przekaz, doprecyzowuję ofertę, wzmacniam CTA, dodaję potrzebne elementy zaufania i upraszczam drogę do kontaktu.",
      ],
      [
          "question" => "Czy sama reklama wystarczy, żeby poprawić wyniki?",
          "answer" => "Nie zawsze. Jeśli strona ma słaby przekaz albo nie buduje zaufania, to nawet dobry ruch będzie przeciekał.",
      ],
  ];
  if (function_exists("upsellio_render_faq_schema")) {
      upsellio_render_faq_schema($front_faq_schema_items);
  }
  ?>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'" />
  <noscript><link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet" /></noscript>
  <link rel="stylesheet" href="<?php echo esc_url(get_template_directory_uri() . "/assets/css/upsellio.css?ver=" . rawurlencode($upsellio_css_version)); ?>" />
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
  <?php wp_body_open(); ?>
  <a class="skip-link" href="#main-content">Przejdź do treści</a>
  <header class="nav">
    <div class="nav-topbar" aria-label="Szybki kontakt">
      <div class="wrap nav-topbar-inner">
        <a href="<?php echo esc_url("tel:" . $contact_phone_href); ?>"><?php echo esc_html((string) $contact_phone); ?></a>
        <a href="<?php echo esc_url("mailto:" . $contact_email); ?>"><?php echo esc_html($contact_email); ?></a>
        <span>10 lat praktyki w sprzedaży B2B</span>
      </div>
    </div>
    <div class="wrap nav-inner">
      <a href="<?php echo esc_url(home_url("/#start")); ?>" class="brand" aria-label="Upsellio — strona główna">
        <img src="<?php echo esc_url($brand_logo_url); ?>" alt="Upsellio — kampanie Google Ads i Meta Ads dla firm B2B" class="brand-logo" decoding="async" />
      </a>

      <ul class="nav-links">
        <?php foreach ($front_nav_links as $front_nav_link) : ?>
          <?php
          $nav_title = trim((string) ($front_nav_link["title"] ?? ""));
          $nav_url = trim((string) ($front_nav_link["url"] ?? ""));
          if ($nav_title === "" || $nav_url === "") {
              continue;
          }
          ?>
          <li><a href="<?php echo esc_url($nav_url); ?>"><?php echo esc_html($nav_title); ?></a></li>
        <?php endforeach; ?>
        <li class="nav-dropdown">
          <button type="button" class="nav-dropdown-toggle" aria-expanded="false">Usługi</button>
          <div class="nav-dropdown-menu">
            <a href="<?php echo esc_url(home_url("/marketing-google-ads/")); ?>">Google Ads</a>
            <a href="<?php echo esc_url(home_url("/marketing-meta-ads/")); ?>">Meta Ads</a>
            <a href="<?php echo esc_url(home_url("/tworzenie-stron-internetowych/")); ?>">Strony WWW</a>
            <a href="<?php echo esc_url(home_url("/oferta/")); ?>">Doradztwo</a>
          </div>
        </li>
      </ul>

      <a href="<?php echo esc_url(home_url("/#hero-analiza")); ?>" class="btn btn-primary btn-sm nav-cta" aria-label="Bezpłatna analiza marketingu">
        <span class="nav-cta-long">Bezpłatna analiza</span>
        <span class="nav-cta-short">Analiza</span>
      </a>

      <button class="hamburger" id="hamburger" aria-label="Otwórz menu" aria-controls="mobile-menu" aria-expanded="false" type="button">
        <span></span><span></span><span></span>
      </button>
    </div>

    <div class="mobile-menu" id="mobile-menu" role="navigation" aria-label="Menu mobilne">
      <div class="wrap">
        <?php foreach ($front_nav_links as $front_nav_link) : ?>
          <?php
          $nav_title = trim((string) ($front_nav_link["title"] ?? ""));
          $nav_url = trim((string) ($front_nav_link["url"] ?? ""));
          if ($nav_title === "" || $nav_url === "") {
              continue;
          }
          ?>
          <a href="<?php echo esc_url($nav_url); ?>"><?php echo esc_html($nav_title); ?></a>
        <?php endforeach; ?>
        <a href="<?php echo esc_url(home_url("/marketing-google-ads/")); ?>">Google Ads</a>
        <a href="<?php echo esc_url(home_url("/marketing-meta-ads/")); ?>">Meta Ads</a>
        <a href="<?php echo esc_url(home_url("/tworzenie-stron-internetowych/")); ?>">Strony WWW</a>
        <a href="<?php echo esc_url(home_url("/oferta/")); ?>">Doradztwo</a>
        <a href="<?php echo esc_url(home_url("/#co-sprawdze")); ?>">Zobacz, co sprawdzę →</a>
        <a href="<?php echo esc_url(home_url("/#hero-analiza")); ?>" class="mobile-menu-cta">Analiza ↓</a>
      </div>
    </div>
  </header>
  <a href="<?php echo esc_url(home_url("/#hero-analiza")); ?>" class="mobile-sticky-cta">Umów bezpłatną konsultację →</a>
  <nav class="home-sticky-nav" id="home-sticky-nav" aria-label="Nawigacja sekcji strony głównej">
    <div class="wrap home-sticky-nav-inner">
      <a href="#system">Usługi</a>
      <a href="#case-study">Wyniki</a>
      <a href="#jak-dzialam">Proces</a>
      <a href="#ceny">Cennik</a>
      <a href="#faq">FAQ</a>
    </div>
  </nav>
  <div id="main-content" tabindex="-1"></div>

  <main id="main" class="home-semrush-flow" data-home-curated="1">
    <?php
    get_template_part("template-parts/home/hero");
    get_template_part("template-parts/home/conversation-check");
    get_template_part("template-parts/home/services-pillars");
    get_template_part("template-parts/home/services-differentiator");
    get_template_part("template-parts/home/services-about");
    get_template_part("template-parts/home/services-pricing");
    get_template_part("template-parts/home/services-cta-band");
    get_template_part("template-parts/home/results");
    get_template_part("template-parts/home/process");
    get_template_part("template-parts/home/blog-highlights");
    get_template_part("template-parts/home/lead-magnet-embed");
    get_template_part("template-parts/home/faq");
    get_template_part("template-parts/home/contact", null, [
        "contact_phone" => $contact_phone,
        "contact_service_options" => $contact_service_options,
    ]);
    ?>
  </main>

  <?php
  echo function_exists("upsellio_render_unified_footer")
      ? upsellio_render_unified_footer(["contact_email" => $contact_email])
      : "";
  ?>

  <button class="scroll-top" id="scroll-top" aria-label="Wróć na górę">↑</button>
  <?php wp_footer(); ?>
</body>
</html>

