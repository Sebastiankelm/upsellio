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
$brand_logo_url = get_template_directory_uri() . "/assets/images/upsellio-logo.png";

$seo_title = trim((string) ($seo_section["title"] ?? ""));
$seo_description = trim((string) ($seo_section["description"] ?? ""));
if ($seo_title === "") {
    $seo_title = "Marketing B2B | Meta Ads, Google Ads i strony internetowe - Upsellio";
}
if ($seo_description === "") {
    $seo_description = "Kampanie Meta Ads i Google Ads oraz strony internetowe dla firm B2B. Zwieksz sprzedaz i pozyskuj klientow dzieki sprawdzonemu systemowi marketingowemu.";
}
if (function_exists("upsellio_limit_meta_description")) {
    $seo_description = upsellio_limit_meta_description($seo_description, 130);
}
$seo_og_title = trim((string) ($seo_section["og_title"] ?? ""));
$seo_og_description = trim((string) ($seo_section["og_description"] ?? ""));
if ($seo_og_title === "") {
    $seo_og_title = $seo_title;
}
if ($seo_og_description === "") {
    $seo_og_description = $seo_description;
}
$seo_og_type = trim((string) ($seo_section["og_type"] ?? "website"));
$seo_og_url = trim((string) ($seo_section["og_url"] ?? "/"));
$seo_twitter_card = trim((string) ($seo_section["twitter_card"] ?? "summary_large_image"));
$seo_og_image = get_template_directory_uri() . "/assets/images/upsellio-logo.png";
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

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;700&display=swap"
    rel="stylesheet"
  />
  <link rel="stylesheet" href="<?php echo esc_url(get_template_directory_uri() . "/assets/css/upsellio.css?ver=" . rawurlencode($upsellio_css_version)); ?>" />

  <?php if (true) : ?><style>
    :root {
      --bg: #ffffff;
      --bg-soft: #f8f8f6;
      --bg-muted: #f1f1ee;
      --surface: #ffffff;

      --text: #111110;
      --text-2: #3d3d38;
      --text-3: #7c7c74;

      --border: #e6e6e1;
      --border-strong: #c9c9c3;

      --teal: #1d9e75;
      --teal-hover: #17885f;
      --teal-dark: #085041;
      --teal-soft: #e8f8f2;
      --teal-line: #c3eddd;

      --danger: #d94c4c;

      --shadow-sm: 0 1px 4px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.03);
      --shadow-md: 0 8px 24px rgba(0, 0, 0, 0.07), 0 2px 6px rgba(0, 0, 0, 0.04);

      --r-sm: 8px;
      --r-md: 12px;
      --r-lg: 18px;
      --r-xl: 28px;
      --r-pill: 999px;

      --sp-1: 8px;
      --sp-2: 16px;
      --sp-3: 24px;
      --sp-4: 32px;
      --sp-5: 40px;
      --sp-6: 48px;
      --sp-7: 56px;
      --sp-8: 64px;
      --sp-10: 80px;
      --sp-12: 96px;
      --sp-14: 120px;

      --container: 1180px;
      --content: 760px;

      --font-display: "Syne", sans-serif;
      --font-body: "DM Sans", sans-serif;
      color-scheme: light;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      font-family: var(--font-body);
      background: var(--bg);
      color: var(--text);
      line-height: 1.65;
      -webkit-font-smoothing: antialiased;
      text-size-adjust: 100%;
      overflow-x: hidden;
    }
    body.is-mobile-menu-open {
      overflow: hidden;
    }

    img {
      display: block;
      max-width: 100%;
    }

    a {
      color: inherit;
      text-decoration: none;
    }

    button,
    input,
    textarea,
    select {
      font: inherit;
    }

    .wrap {
      width: min(var(--container), calc(100% - 48px));
      margin: 0 auto;
    }

    .content {
      width: min(var(--content), 100%);
    }

    .section {
      padding: var(--sp-12) 0;
    }

    .section-sm {
      padding: var(--sp-8) 0;
    }

    .section-border {
      border-bottom: 1px solid var(--border);
    }

    .bg-soft {
      background: var(--bg-soft);
    }

    .eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 1.8px;
      text-transform: uppercase;
      color: var(--text-3);
      margin-bottom: var(--sp-3);
    }

    .eyebrow::before {
      content: "";
      width: 22px;
      height: 2px;
      border-radius: 2px;
      background: var(--teal);
    }

    .h1 {
      font-family: var(--font-display);
      font-weight: 800;
      font-size: clamp(38px, 5vw, 64px);
      line-height: 1.02;
      letter-spacing: -2px;
    }

    .h2 {
      font-family: var(--font-display);
      font-weight: 700;
      font-size: clamp(28px, 3.4vw, 42px);
      line-height: 1.08;
      letter-spacing: -1px;
    }

    .h3 {
      font-family: var(--font-display);
      font-weight: 700;
      font-size: 20px;
      line-height: 1.2;
    }

    .lead {
      font-size: 18px;
      line-height: 1.8;
      color: var(--text-2);
    }

    .body {
      font-size: 15px;
      line-height: 1.75;
      color: var(--text-2);
    }

    .muted {
      color: var(--text-3);
    }

    .accent {
      color: var(--teal);
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      min-height: 46px;
      border-radius: var(--r-md);
      border: none;
      cursor: pointer;
      transition: 0.18s ease;
      white-space: nowrap;
    }

    .btn-primary {
      background: var(--teal);
      color: #fff;
      padding: 15px 28px;
      font-size: 15px;
      font-weight: 600;
      box-shadow: 0 0 0 0 rgba(29, 158, 117, 0.38);
      animation: pulse 2.8s ease 2.5s infinite;
    }

    .btn-primary:hover {
      background: var(--teal-hover);
      transform: translateY(-2px);
      box-shadow: 0 8px 22px rgba(29, 158, 117, 0.28);
      animation: none;
    }

    .btn-secondary {
      background: transparent;
      color: var(--text);
      padding: 15px 24px;
      font-size: 15px;
      font-weight: 500;
      border: 1px solid var(--border-strong);
    }

    .btn-secondary:hover {
      border-color: var(--teal);
      color: var(--teal);
      transform: translateY(-2px);
    }

    @keyframes pulse {
      0% { box-shadow: 0 0 0 0 rgba(29, 158, 117, 0.36); }
      70% { box-shadow: 0 0 0 12px rgba(29, 158, 117, 0); }
      100% { box-shadow: 0 0 0 0 rgba(29, 158, 117, 0); }
    }

    .nav {
      position: sticky;
      top: 0;
      z-index: 100;
      background: color-mix(in srgb, var(--bg) 90%, transparent);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid var(--border);
    }

    .nav-inner {
      height: 84px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: var(--sp-3);
    }

    .brand {
      display: flex;
      align-items: center;
      min-height: 44px;
    }

    .brand-logo {
      display: block;
      height: 62px;
      width: auto;
      max-width: min(72vw, 420px);
      mix-blend-mode: screen;
      isolation: isolate;
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 34px;
      list-style: none;
    }

    .nav-links a {
      font-size: 14px;
      color: var(--text-2);
      border-bottom: 2px solid transparent;
      padding: 4px 0;
      transition: 0.18s ease;
    }

    .nav-links a.active,
    .nav-links a[aria-current="page"],
    .nav-links a:hover {
      color: var(--text);
      border-bottom-color: var(--teal);
    }

    .nav-actions {
      display: flex;
      align-items: center;
      gap: var(--sp-2);
    }

    .nav-cta {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 44px;
      background: var(--teal);
      color: #fff;
      padding: 10px 18px;
      border-radius: var(--r-md);
      font-size: 14px;
      font-weight: 600;
      transition: 0.18s ease;
    }

    .nav-cta:hover {
      background: var(--teal-hover);
      transform: translateY(-1px);
    }

    .hamburger {
      display: none;
      background: none;
      border: none;
      cursor: pointer;
      padding: 4px;
      flex-direction: column;
      gap: 5px;
    }

    .hamburger span {
      width: 22px;
      height: 2px;
      background: var(--text);
      border-radius: 2px;
      transition: 0.25s ease;
    }

    .hamburger.open span:nth-child(1) {
      transform: translateY(7px) rotate(45deg);
    }

    .hamburger.open span:nth-child(2) {
      opacity: 0;
    }

    .hamburger.open span:nth-child(3) {
      transform: translateY(-7px) rotate(-45deg);
    }

    .mobile-menu {
      display: none;
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.35s ease;
      border-top: 1px solid var(--border);
      background: var(--bg);
    }

    .mobile-menu.open {
      max-height: calc(100vh - 84px);
      overflow-y: auto;
      -webkit-overflow-scrolling: touch;
    }

    .mobile-menu a {
      display: flex;
      align-items: center;
      min-height: 48px;
      padding: 15px 0;
      border-bottom: 1px solid var(--border);
      color: var(--text-2);
      font-size: 15px;
    }

    .mobile-menu a:last-child {
      color: var(--teal);
      font-weight: 600;
    }

    .hero {
      position: relative;
      overflow: hidden;
      border-bottom: 1px solid var(--border);
    }

    .hero::before {
      content: "";
      position: absolute;
      inset: 0 0 auto 0;
      height: 3px;
      background: var(--teal);
      transform-origin: left;
      animation: grow 0.9s cubic-bezier(.16,1,.3,1) .2s both;
    }

    @keyframes grow {
      from { transform: scaleX(0); }
      to { transform: scaleX(1); }
    }

    .hero-grid {
      display: grid;
      grid-template-columns: minmax(0, 1fr) 360px;
      gap: var(--sp-10);
      align-items: start;
    }

    .hero-pill {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      background: var(--bg-soft);
      border: 1px solid var(--border-strong);
      border-radius: var(--r-pill);
      padding: 8px 16px 8px 8px;
      margin-bottom: var(--sp-4);
    }

    .hero-pill-dot {
      width: 28px;
      height: 28px;
      border-radius: 50%;
      display: grid;
      place-items: center;
      background: var(--teal-soft);
      border: 1px solid var(--teal-line);
      color: var(--teal);
      font-size: 13px;
      font-weight: 700;
      flex-shrink: 0;
    }

    .hero-pill span {
      font-size: 13px;
      color: var(--text-2);
    }

    .hero-copy {
      max-width: 720px;
    }

    .hero-copy .h1 {
      margin-bottom: var(--sp-4);
    }

    .hero-copy .lead {
      max-width: 640px;
      margin-bottom: var(--sp-5);
    }

    .hero-actions {
      display: flex;
      flex-wrap: wrap;
      gap: var(--sp-2);
      margin-bottom: var(--sp-2);
    }

    .hero-actions .btn {
      width: 100%;
    }

    .hero-micro {
      font-size: 12px;
      color: var(--text-3);
    }

    .hero-trust {
      margin-top: var(--sp-4);
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      align-items: center;
      color: var(--text-2);
      font-size: 13px;
    }

    .hero-trust-item {
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .hero-trust-dot {
      width: 18px;
      height: 18px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: var(--teal-soft);
      border: 1px solid var(--teal-line);
      color: var(--teal);
      font-size: 11px;
      font-weight: 700;
    }

    .hero-aside {
      background: linear-gradient(180deg, #fbfcfa 0%, var(--surface) 100%);
      border: 1px solid var(--border);
      border-radius: var(--r-xl);
      padding: var(--sp-4);
      box-shadow: var(--shadow-md);
      position: relative;
      overflow: hidden;
      align-self: start;
    }

    .hero-aside::before {
      content: "";
      position: absolute;
      inset: 0;
      background:
        radial-gradient(circle at 84% 14%, rgba(29, 158, 117, 0.13), transparent 34%),
        radial-gradient(circle at 10% 86%, rgba(29, 158, 117, 0.08), transparent 42%);
      pointer-events: none;
    }

    .hero-aside-label {
      position: relative;
      z-index: 2;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 1.6px;
      text-transform: uppercase;
      color: var(--text-3);
      margin-bottom: 12px;
    }

    .hero-system {
      position: relative;
      z-index: 2;
      display: grid;
      gap: 10px;
    }

    .hero-system-head {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 8px;
      margin-bottom: 2px;
    }

    .hero-system-side-title {
      text-align: center;
      font-family: var(--font-display);
      font-size: 24px;
      line-height: 1.02;
      letter-spacing: -0.02em;
    }

    .hero-system-side-sub {
      margin-top: 4px;
      text-align: center;
      font-size: 10px;
      text-transform: uppercase;
      letter-spacing: .8px;
      color: var(--text-3);
      font-weight: 700;
    }

    .hero-system-top {
      display: grid;
      grid-template-columns: 1fr 1.7fr 1fr;
      gap: 10px;
      align-items: stretch;
    }

    .hero-channel-stack {
      display: grid;
      gap: 8px;
      position: relative;
    }

    .hero-channel-stack::before {
      content: "";
      position: absolute;
      inset: -4px -8px -4px -8px;
      background:
        radial-gradient(circle at 12% 18%, rgba(17,17,17,.12) 0 1px, transparent 1.4px),
        radial-gradient(circle at 88% 34%, rgba(17,17,17,.1) 0 1px, transparent 1.5px),
        radial-gradient(circle at 24% 84%, rgba(17,17,17,.08) 0 1px, transparent 1.3px);
      pointer-events: none;
      opacity: .55;
    }

    .hero-channel-card {
      border: 1px solid var(--border);
      background: #fff;
      border-radius: 12px;
      padding: 10px;
      display: grid;
      gap: 4px;
      box-shadow: var(--shadow-sm);
      transition: transform .25s ease, border-color .25s ease;
      animation: heroFloat 5s ease-in-out infinite;
    }

    .hero-channel-card:nth-child(2) { animation-delay: .4s; }
    .hero-channel-card:nth-child(3) { animation-delay: .8s; }
    .hero-channel-card:nth-child(4) { animation-delay: 1.2s; }
    .hero-channel-card:hover { transform: translateY(-3px); border-color: var(--teal-line); }

    .hero-channel-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      font-size: 11px;
      font-weight: 700;
      color: var(--text-2);
    }

    .hero-channel-metric {
      font-size: 10px;
      color: var(--text-3);
      line-height: 1.35;
    }

    .hero-spark {
      height: 22px;
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      align-items: end;
      gap: 3px;
    }

    .hero-spark span {
      background: linear-gradient(180deg, #3db991 0%, #18785d 100%);
      border-radius: 999px;
      min-height: 4px;
      opacity: .82;
      transition: height .85s ease;
    }

    .hero-system-core {
      border: 1px solid var(--border);
      background: #fff;
      border-radius: 14px;
      padding: 12px;
      box-shadow: var(--shadow-sm);
      display: grid;
      gap: 10px;
      position: relative;
    }

    .hero-core-nav {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      font-size: 9px;
      color: var(--text-3);
      text-transform: uppercase;
      letter-spacing: .8px;
    }

    .hero-core-nav span {
      padding: 2px 0;
      border-bottom: 1px solid transparent;
    }

    .hero-core-nav span.is-active {
      color: var(--text);
      border-bottom-color: var(--teal-line);
    }

    .hero-core-main {
      display: grid;
      grid-template-columns: 1.2fr .9fr;
      gap: 8px;
      align-items: start;
    }

    .hero-core-title {
      font-family: var(--font-display);
      font-size: 34px;
      line-height: .93;
      letter-spacing: -0.03em;
      max-width: 11ch;
    }

    .hero-core-title .accent {
      color: #1d8666;
    }

    .hero-core-lead {
      margin-top: 8px;
      font-size: 11px;
      color: var(--text-2);
      line-height: 1.5;
      max-width: 31ch;
    }

    .hero-core-btn {
      display: inline-flex;
      width: fit-content;
      align-items: center;
      justify-content: center;
      min-height: 30px;
      padding: 0 11px;
      border-radius: 999px;
      background: var(--teal);
      color: #fff;
      font-size: 11px;
      font-weight: 700;
      margin-top: 8px;
    }

    .hero-core-form {
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 8px;
      display: grid;
      gap: 5px;
      background: #fcfcfb;
    }

    .hero-core-field {
      height: 18px;
      border: 1px solid var(--border);
      border-radius: 6px;
      background: #fff;
      position: relative;
      overflow: hidden;
    }

    .hero-core-field::after {
      content: "";
      position: absolute;
      left: 4px;
      top: 50%;
      width: 58%;
      height: 2px;
      border-radius: 999px;
      transform: translateY(-50%);
      background: #d8ddd7;
    }

    .hero-core-submit {
      margin-top: 2px;
      height: 20px;
      border-radius: 6px;
      background: linear-gradient(160deg, #1fa67d, #176c54);
    }

    .hero-core-grid {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 6px;
    }

    .hero-core-grid div {
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 7px 6px;
      font-size: 9px;
      color: var(--text-3);
      line-height: 1.35;
      background: #fcfcfb;
      text-align: center;
    }

    .hero-kpi-stack {
      display: grid;
      gap: 8px;
    }

    .hero-kpi-block {
      border: 1px solid var(--border);
      background: #fff;
      border-radius: 12px;
      padding: 10px;
      box-shadow: var(--shadow-sm);
      display: grid;
      gap: 7px;
    }

    .hero-kpi-mini-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 6px;
    }

    .hero-kpi-mini-card {
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 7px;
      background: #fcfcfb;
    }

    .hero-kpi-label {
      font-size: 9px;
      color: var(--text-3);
      text-transform: uppercase;
      letter-spacing: .8px;
    }

    .hero-kpi-row {
      display: flex;
      align-items: baseline;
      justify-content: space-between;
      margin-top: 3px;
      gap: 6px;
    }

    .hero-kpi-value {
      font-family: var(--font-display);
      font-size: 23px;
      line-height: 1;
      color: var(--text);
    }

    .hero-kpi-change {
      font-size: 10px;
      font-weight: 700;
      color: var(--teal);
      white-space: nowrap;
    }

    .hero-kpi-progress {
      margin-top: 6px;
      height: 5px;
      border-radius: 999px;
      background: #edf0eb;
      overflow: hidden;
    }

    .hero-kpi-progress i {
      display: block;
      height: 100%;
      width: 45%;
      background: linear-gradient(90deg, #3db991, #1d9e75);
      border-radius: inherit;
      transition: width .85s ease;
    }

    .hero-pipeline-box {
      border: 1px solid var(--border);
      border-radius: 10px;
      background: #fff;
      padding: 8px;
      display: grid;
      gap: 5px;
    }

    .hero-pipeline-title {
      font-size: 9px;
      text-transform: uppercase;
      letter-spacing: .8px;
      color: var(--text-3);
      font-weight: 700;
    }

    .hero-pipeline-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 8px;
      border: 1px solid var(--border);
      border-radius: 7px;
      padding: 5px 7px;
      font-size: 9px;
      color: var(--text-2);
      background: #fcfcfb;
    }

    .hero-pipeline-row b {
      color: #1f5a46;
      font-size: 10px;
    }

    .hero-system-bottom {
      display: grid;
      grid-template-columns: 1.45fr auto 1fr;
      gap: 8px;
      align-items: center;
    }

    .hero-chaos-note {
      border: 1px solid var(--border);
      border-radius: 12px;
      background: #fff;
      padding: 9px;
      display: grid;
      gap: 6px;
    }

    .hero-chaos-note strong {
      font-size: 9px;
      line-height: 1.3;
      text-transform: uppercase;
      letter-spacing: .8px;
      color: #2e322f;
    }

    .hero-chaos-note-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 6px;
    }

    .hero-chaos-note-grid span {
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 6px;
      font-size: 9px;
      line-height: 1.35;
      color: var(--text-3);
      background: #fcfcfb;
      transition: transform .85s ease, opacity .85s ease;
    }

    .hero-analytics-strip {
      border: 1px solid var(--border);
      border-radius: 12px;
      background: #fff;
      padding: 9px;
      display: grid;
      gap: 7px;
      box-shadow: var(--shadow-sm);
    }

    .hero-analytics-title {
      font-size: 9px;
      letter-spacing: .8px;
      text-transform: uppercase;
      color: var(--text-3);
      font-weight: 700;
    }

    .hero-analytics-grid {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 6px;
    }

    .hero-analytics-cell {
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 6px;
      background: #fcfcfb;
    }

    .hero-analytics-cell .k {
      font-size: 9px;
      color: var(--text-3);
      line-height: 1.2;
    }

    .hero-analytics-cell .v {
      margin-top: 2px;
      font-size: 15px;
      line-height: 1;
      font-family: var(--font-display);
    }

    .hero-analytics-cell .d {
      margin-top: 3px;
      font-size: 9px;
      color: var(--teal);
      font-weight: 700;
    }

    .hero-optimization-node {
      width: 76px;
      height: 76px;
      border-radius: 50%;
      border: 1px solid var(--teal-line);
      background: radial-gradient(circle at 40% 30%, #fff, #eef8f3);
      box-shadow: var(--shadow-sm);
      display: grid;
      place-items: center;
      text-align: center;
      font-size: 9px;
      line-height: 1.25;
      color: var(--text-2);
      padding: 8px;
      animation: heroPulse 4.8s ease-in-out infinite;
    }

    .hero-growth-panel {
      border: 1px solid var(--border);
      border-radius: 12px;
      background: #fff;
      padding: 10px;
      box-shadow: var(--shadow-sm);
      display: grid;
      gap: 8px;
    }

    .hero-growth-meta {
      display: flex;
      justify-content: space-between;
      align-items: end;
      gap: 8px;
    }

    .hero-growth-meta .k {
      font-size: 9px;
      color: var(--text-3);
      text-transform: uppercase;
      letter-spacing: .8px;
      font-weight: 700;
    }

    .hero-growth-meta .v {
      font-family: var(--font-display);
      font-size: 28px;
      line-height: 1;
      color: #1f5a46;
    }

    .hero-growth-chart {
      position: relative;
      height: 56px;
      border-radius: 8px;
      background: linear-gradient(180deg, #fbfdfb 0%, #f4f9f6 100%);
      overflow: hidden;
    }

    .hero-growth-line {
      position: absolute;
      inset: auto 0 6px 0;
      height: 46px;
      display: flex;
      align-items: end;
      gap: 5px;
      padding: 0 6px;
    }

    .hero-growth-line span {
      flex: 1;
      border-radius: 999px;
      min-height: 5px;
      background: linear-gradient(180deg, #2ca983, #186f56);
      transition: height .85s ease;
      opacity: .92;
    }

    .hero-system-pipe {
      display: grid;
      grid-template-columns: repeat(5, minmax(0, 1fr));
      gap: 8px;
      margin-top: 2px;
    }

    .hero-pipe-step {
      border: 1px solid var(--border);
      border-radius: 10px;
      background: #fff;
      text-align: center;
      padding: 8px 6px;
      font-size: 10px;
      line-height: 1.3;
      color: var(--text-3);
      transition: border-color .25s ease, color .25s ease, background .25s ease;
    }

    .hero-pipe-step.is-active {
      border-color: var(--teal-line);
      color: #fff;
      background: linear-gradient(160deg, #1fa67d, #176c54);
    }

    @keyframes heroFloat {
      0%,100% { transform: translateY(0); }
      50% { transform: translateY(-2px); }
    }

    @keyframes heroPulse {
      0%,100% { box-shadow: var(--shadow-sm); transform: scale(1); }
      50% { box-shadow: 0 10px 24px rgba(29,158,117,.18); transform: scale(1.015); }
    }

    .hero-wrap {
      padding: var(--sp-10) 0;
      display: grid;
      grid-template-columns: 1fr;
      gap: var(--sp-8);
      align-items: start;
    }

    .hero-h1 {
      margin-bottom: var(--sp-4);
    }

    .hero-lead {
      max-width: 640px;
      margin-bottom: var(--sp-5);
    }

    .hero-micro {
      font-size: 13px;
      color: var(--text-3);
      margin-top: 10px;
    }

    .aside-label {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 1.2px;
      text-transform: uppercase;
      color: var(--text-3);
      margin-bottom: 14px;
    }

    .aside-stats {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 8px;
      margin-bottom: 14px;
    }

    .stat-block {
      background: var(--bg-soft);
      border: 1px solid var(--border);
      border-radius: var(--r-md);
      padding: 14px;
    }

    .stat-num {
      font-family: var(--font-display);
      font-weight: 700;
      font-size: 24px;
      line-height: 1.1;
      margin-bottom: 6px;
      color: var(--text);
    }

    .stat-num.teal {
      color: var(--teal);
    }

    .stat-lbl {
      font-size: 12px;
      color: var(--text-3);
      line-height: 1.45;
    }

    .pipeline {
      border: 1px solid var(--border);
      border-radius: var(--r-md);
      background: var(--bg-soft);
      padding: 10px 12px;
    }

    .pipeline-title {
      font-size: 10px;
      font-weight: 700;
      letter-spacing: .8px;
      text-transform: uppercase;
      color: var(--text-3);
      margin-bottom: 8px;
    }

    .pipeline-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 5px 0;
      border-bottom: 1px solid var(--border);
      font-size: 13px;
      color: var(--text-2);
    }

    .pipeline-row:last-child {
      border-bottom: none;
    }

    .pipeline-row b {
      color: var(--teal-dark);
      font-weight: 700;
    }

    .why-nums {
      background: linear-gradient(145deg,#1a2420,#0e1a15);
      border-radius: var(--r-xl);
      padding: var(--sp-5);
      color: #fff;
      box-shadow: var(--shadow-md);
      max-width: 440px;
    }

    .why-nums-title {
      font-family: var(--font-display);
      font-size: 18px;
      margin-bottom: var(--sp-3);
    }

    .why-stat {
      display: grid;
      grid-template-columns: 92px minmax(0, 1fr);
      gap: 12px;
      padding: 14px 0;
      border-bottom: 1px solid rgba(255,255,255,.08);
    }

    .why-stat:last-child {
      border-bottom: none;
      padding-bottom: 0;
    }

    .why-stat-num {
      font-family: var(--font-display);
      font-size: 30px;
      color: var(--teal);
      line-height: 1;
    }

    .why-stat-text {
      font-size: 14px;
      color: rgba(255,255,255,.72);
      line-height: 1.6;
    }

    .service-badge {
      display: inline-flex;
      align-items: center;
      border-radius: var(--r-pill);
      padding: 4px 12px;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .3px;
      margin-bottom: var(--sp-3);
    }

    .check-icon {
      width: 18px;
      height: 18px;
      border-radius: 50%;
      flex-shrink: 0;
      display: grid;
      place-items: center;
      margin-top: 1px;
      background: var(--teal-soft);
      border: 1px solid var(--teal-line);
      color: var(--teal);
      font-size: 10px;
      font-weight: 700;
    }

    .case-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: var(--sp-8);
      margin-top: var(--sp-5);
    }

    .chart-panel {
      background: linear-gradient(180deg, #ffffff 0%, #f8fbf9 100%);
      border: 1px solid var(--border);
      border-radius: 20px;
      padding: clamp(16px, 2.2vw, 24px);
      box-shadow: var(--shadow-sm);
    }

    .cp-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      padding-bottom: 12px;
      margin-bottom: 14px;
      border-bottom: 1px solid var(--border);
    }

    .cp-head span:first-child {
      font-size: 13px;
      font-weight: 800;
      letter-spacing: .45px;
      text-transform: uppercase;
      color: var(--text-2);
    }

    .chart-panel .live-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      background: var(--teal);
      box-shadow: 0 0 0 0 rgba(29, 158, 117, .35);
      animation: cpPulse 2.1s ease infinite;
      flex-shrink: 0;
    }

    .cp-kpis {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 10px;
    }

    .cp-kpi {
      border: 1px solid var(--border);
      border-radius: 14px;
      background: #fff;
      padding: 12px;
      display: grid;
      gap: 6px;
      min-height: 96px;
      align-content: start;
      box-shadow: 0 6px 16px rgba(17, 17, 16, 0.04);
      transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
    }

    .cp-kpi:hover {
      transform: translateY(-2px);
      border-color: var(--teal-line);
      box-shadow: 0 10px 20px rgba(17, 17, 16, 0.08);
    }

    .cp-kpi-num {
      font-family: var(--font-display);
      font-size: clamp(24px, 4vw, 32px);
      line-height: 1;
      letter-spacing: -.5px;
      color: var(--text);
    }

    .cp-kpi-lbl {
      font-size: 12px;
      line-height: 1.4;
      color: var(--text-3);
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .35px;
    }

    .cp-kpi:nth-child(1) .cp-kpi-num,
    .cp-kpi:nth-child(3) .cp-kpi-num,
    .cp-kpi:nth-child(4) .cp-kpi-num {
      color: var(--teal-dark);
    }

    .cp-kpi:nth-child(2) .cp-kpi-num {
      color: var(--danger);
    }

    .results-table {
      width: 100%;
      border-collapse: collapse;
      margin: var(--sp-4) 0;
    }

    .results-table th,
    .results-table td {
      padding: 12px 14px;
      font-size: 14px;
      border-bottom: 1px solid var(--border);
      text-align: left;
    }

    .results-table th {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .4px;
      text-transform: uppercase;
      color: var(--text-3);
      background: var(--bg-soft);
    }

    .results-table .col-after {
      font-weight: 700;
      color: var(--teal);
    }

    .badge-pos,
    .badge-neg {
      font-size: 12px;
      font-weight: 700;
      padding: 2px 7px;
      border-radius: 4px;
    }

    .badge-pos {
      background: var(--teal-soft);
      color: var(--teal-dark);
    }

    .badge-neg {
      background: #fce8e8;
      color: var(--danger);
    }

    .metrics-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: var(--sp-3);
      margin-top: var(--sp-5);
    }

    .metric-card {
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 18px;
      padding: clamp(16px, 2vw, 22px);
      box-shadow: var(--shadow-sm);
    }

    .metric-card.dark {
      background: #0f1815;
      border-color: #1d3b31;
      color: #eaf5f0;
    }

    .mc-label {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .35px;
      text-transform: uppercase;
      color: var(--text-3);
      margin-bottom: 10px;
    }

    .metric-card.dark .mc-label {
      color: #b7d7ca;
    }

    .mc-num {
      font-family: var(--font-display);
      font-size: clamp(30px, 4.8vw, 44px);
      line-height: 1;
      letter-spacing: -1px;
      color: var(--text);
    }

    .mc-num.teal {
      color: var(--teal-dark);
    }

    .mc-num.red {
      color: var(--danger);
    }

    .mc-change {
      display: inline-flex;
      align-items: center;
      margin-top: 10px;
      padding: 4px 10px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 700;
    }

    .mc-change.up {
      background: var(--teal-soft);
      color: var(--teal-dark);
    }

    .mc-change.dn {
      background: #fdecec;
      color: var(--danger);
    }

    .mc-sub {
      margin-top: 9px;
      font-size: 13px;
      line-height: 1.55;
      color: var(--text-3);
    }

    .funnel {
      display: grid;
      gap: 10px;
      margin-top: 6px;
    }

    .funnel-row {
      display: grid;
      grid-template-columns: minmax(68px, 84px) minmax(0, 1fr) auto;
      align-items: center;
      gap: 10px;
    }

    .f-lbl {
      font-size: 12px;
      font-weight: 700;
      color: #d7eee4;
    }

    .f-track {
      position: relative;
      height: 8px;
      border-radius: 999px;
      overflow: hidden;
      background: rgba(156, 211, 188, 0.2);
    }

    .f-fill {
      position: absolute;
      inset: 0 auto 0 0;
      transform-origin: left center;
      background: linear-gradient(90deg, #7fd2b2, #24a574);
      border-radius: 999px;
      width: 100%;
      transition: transform .45s ease;
    }

    .f-val {
      font-size: 13px;
      font-weight: 700;
      color: #effbf5;
      white-space: nowrap;
    }

    .fit-items {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    @media (min-width: 980px) {
      .hero-wrap {
        grid-template-columns: minmax(0, 1fr) 400px;
      }
    }

    @media (min-width: 900px) {
      .split {
        grid-template-columns: 380px minmax(0, 1fr);
        gap: var(--sp-8);
      }
    }

    @media (min-width: 880px) {
      .case-grid {
        grid-template-columns: 1fr 1fr;
        align-items: start;
      }

      .chart-panel {
        position: sticky;
        top: 110px;
      }
    }

    @media (min-width: 900px) {
      .metrics-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        align-items: stretch;
      }

      .metrics-grid .metric-card.dark {
        grid-column: span 1;
      }
    }

    @media (max-width: 420px) {
      .cp-kpis {
        grid-template-columns: 1fr;
      }
    }

    @keyframes cpPulse {
      0% {
        box-shadow: 0 0 0 0 rgba(29, 158, 117, .35);
      }
      70% {
        box-shadow: 0 0 0 10px rgba(29, 158, 117, 0);
      }
      100% {
        box-shadow: 0 0 0 0 rgba(29, 158, 117, 0);
      }
    }

    .split {
      display: grid;
      grid-template-columns: 320px minmax(0, 1fr);
      gap: var(--sp-10);
      align-items: start;
    }

    .stack-cards {
      display: grid;
      gap: var(--sp-3);
    }

    .feature-row {
      display: grid;
      grid-template-columns: 44px minmax(0, 1fr);
      gap: var(--sp-3);
      padding: var(--sp-4);
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--r-lg);
      box-shadow: var(--shadow-sm);
      transition: 0.2s ease;
    }

    .feature-row:hover {
      transform: translateX(4px);
      border-color: var(--teal-line);
      box-shadow: var(--shadow-md);
    }

    .feature-icon {
      width: 44px;
      height: 44px;
      border-radius: 12px;
      display: grid;
      place-items: center;
      background: var(--teal-soft);
      border: 1px solid var(--teal-line);
      color: var(--teal);
      flex-shrink: 0;
    }

    .feature-title {
      font-size: 15px;
      font-weight: 600;
      margin-bottom: 5px;
    }

    .feature-desc {
      font-size: 14px;
      color: var(--text-2);
      line-height: 1.7;
    }

    .why-trust-visual {
      border: 1px solid var(--border);
      border-radius: 16px;
      background: linear-gradient(180deg, #fcfdfc 0%, #f6faf7 100%);
      padding: 10px;
      display: grid;
      gap: 8px;
      box-shadow: var(--shadow-sm);
    }

    .why-intro-block {
      margin-bottom: var(--sp-4);
      max-width: 980px;
    }

    .why-horizontal-block {
      border: 1px solid #deebe5;
      border-radius: 14px;
      background: #fff;
      padding: 10px;
      display: grid;
      gap: 8px;
    }

    .why-contact-block + .why-results-block {
      margin-top: 6px;
    }

    .why-trust-layout {
      display: grid;
      grid-template-columns: 1fr 1.1fr 1fr;
      gap: 8px;
    }

    .why-trust-card {
      border: 1px solid var(--border);
      border-radius: 12px;
      background: #fff;
      padding: 9px;
      display: grid;
      gap: 7px;
      box-shadow: var(--shadow-sm);
    }

    .why-trust-title {
      font-family: var(--font-display);
      font-size: 32px;
      line-height: .92;
      letter-spacing: -0.03em;
      color: #21312b;
      max-width: 14ch;
    }

    .why-trust-sub {
      font-size: 10px;
      line-height: 1.4;
      color: var(--text-3);
      max-width: 35ch;
    }

    .why-notebook {
      border: 1px solid #dbe5df;
      border-radius: 10px;
      padding: 10px 9px;
      background: linear-gradient(170deg, #ffffff 0%, #f4f8f6 100%);
      transform: rotate(-4deg);
      box-shadow: 0 10px 18px rgba(20, 40, 30, 0.08);
    }

    .why-notebook-head {
      font-family: var(--font-display);
      font-size: 20px;
      margin-bottom: 6px;
      color: #2c3631;
    }

    .why-notebook-list {
      display: grid;
      gap: 3px;
      font-size: 10px;
      color: #3d4e46;
      line-height: 1.4;
    }

    .why-notebook-list span::before {
      content: "✓";
      margin-right: 5px;
      color: #1d8666;
      font-weight: 700;
    }

    .why-facts {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 6px;
    }

    .why-fact {
      border: 1px solid var(--border);
      border-radius: 9px;
      background: #fcfcfb;
      padding: 6px;
      text-align: center;
    }

    .why-fact b {
      display: block;
      font-family: var(--font-display);
      font-size: 20px;
      line-height: 1;
      color: #1f5a46;
    }

    .why-fact span {
      margin-top: 2px;
      display: block;
      font-size: 9px;
      line-height: 1.3;
      color: var(--text-3);
    }

    .why-process-list {
      display: grid;
      gap: 6px;
    }

    .why-process-step {
      border: 1px solid var(--border);
      border-radius: 9px;
      background: #fcfcfb;
      padding: 7px;
      display: grid;
      gap: 2px;
      transition: border-color .25s ease, background .25s ease, color .25s ease;
    }

    .why-process-step strong {
      font-size: 10px;
      color: var(--text);
      line-height: 1.3;
    }

    .why-process-step span {
      font-size: 9px;
      color: var(--text-3);
      line-height: 1.3;
    }

    .why-process-step.is-active {
      border-color: var(--teal-line);
      background: linear-gradient(160deg, #1fa67d, #176c54);
    }

    .why-process-step.is-active strong,
    .why-process-step.is-active span {
      color: #fff;
    }

    .why-process-ops {
      border: 1px solid var(--border);
      border-radius: 9px;
      background: #f7faf8;
      padding: 6px;
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 5px;
    }

    .why-process-ops div {
      border: 1px solid #dfe8e3;
      border-radius: 8px;
      background: #fff;
      text-align: center;
      padding: 5px;
      font-size: 9px;
      color: #2c4b3f;
      line-height: 1.25;
    }

    .why-revenue-card {
      border: 1px solid var(--border);
      border-radius: 10px;
      background: linear-gradient(135deg, #1e2f39, #1a3e37);
      color: #e3f1eb;
      padding: 8px;
      display: grid;
      gap: 6px;
    }

    .why-revenue-card strong {
      font-size: 10px;
      text-transform: uppercase;
      letter-spacing: .7px;
    }

    .why-revenue-growth {
      width: fit-content;
      padding: 2px 7px;
      border-radius: 999px;
      background: rgba(70, 197, 145, 0.18);
      border: 1px solid rgba(70, 197, 145, 0.38);
      color: #b7f2d7;
      font-size: 10px;
      font-weight: 700;
    }

    .why-revenue-bars {
      height: 46px;
      display: flex;
      align-items: end;
      gap: 3px;
    }

    .why-revenue-bars i {
      flex: 1;
      min-height: 5px;
      border-radius: 999px;
      background: linear-gradient(180deg, #7fd7b5, #34a17d);
      transition: height .45s ease;
    }

    .why-result-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 6px;
    }

    .why-result-card {
      border: 1px solid var(--border);
      border-radius: 9px;
      background: #fcfcfb;
      padding: 6px;
    }

    .why-result-card .k {
      font-size: 9px;
      color: var(--text-3);
    }

    .why-result-card .v {
      margin-top: 2px;
      font-family: var(--font-display);
      font-size: 27px;
      line-height: 1;
      color: #1f5a46;
    }

    .why-result-card .d {
      margin-top: 2px;
      font-size: 9px;
      color: var(--teal);
      font-weight: 700;
    }

    .why-result-line {
      margin-top: 4px;
      height: 16px;
      display: flex;
      align-items: end;
      gap: 2px;
    }

    .why-result-line i {
      flex: 1;
      min-height: 4px;
      border-radius: 999px;
      background: linear-gradient(180deg, #6bc1a4, #2c9072);
      transition: height .45s ease;
    }

    .why-margin-card {
      border: 1px solid var(--border);
      border-radius: 9px;
      background: #fcfcfb;
      padding: 6px;
      display: grid;
      gap: 6px;
      place-items: center;
      text-align: center;
    }

    .why-margin-ring {
      --why-margin: 32;
      width: 88px;
      height: 88px;
      border-radius: 50%;
      background: conic-gradient(#1d8f6e calc(var(--why-margin) * 1%), #e3ece8 0);
      display: grid;
      place-items: center;
      transition: background .35s ease;
      position: relative;
    }

    .why-margin-ring::after {
      content: "";
      position: absolute;
      inset: 10px;
      border-radius: 50%;
      background: #fff;
      border: 1px solid #d7e2dd;
    }

    .why-margin-value {
      position: relative;
      z-index: 1;
      font-family: var(--font-display);
      font-size: 24px;
      line-height: 1;
      color: #1f5a46;
    }

    .why-principles {
      border: 1px solid #dce7e1;
      border-radius: 10px;
      background: #f3f8f5;
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 6px;
      padding: 7px;
    }

    .why-principle-item {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 5px 6px;
      border-radius: 8px;
      border: 1px solid transparent;
      font-size: 9px;
      line-height: 1.3;
      color: #305447;
      transition: border-color .25s ease, background .25s ease;
    }

    .why-principle-item i {
      width: 16px;
      height: 16px;
      border-radius: 5px;
      border: 1px solid #b6d4ca;
      background: #fff;
      flex-shrink: 0;
    }

    .why-principle-item.is-active {
      border-color: #a8d3c5;
      background: #e8f5ef;
    }

    .why-one-heading {
      text-align: center;
      font-family: var(--font-display);
      font-size: 45px;
      line-height: .95;
      letter-spacing: -0.03em;
      color: #22332c;
      margin: 2px 0 0;
    }

    .why-one-heading .accent {
      color: #1f7f62;
    }

    .why-one-sub {
      text-align: center;
      font-size: 12px;
      color: var(--text-3);
      line-height: 1.5;
      margin-top: -1px;
    }

    .why-one-process {
      display: grid;
      grid-template-columns: 1fr;
      gap: 8px;
      align-items: stretch;
    }

    .why-one-step {
      border: 1px solid var(--border);
      border-radius: 12px;
      background: #fff;
      padding: 9px;
      display: grid;
      gap: 6px;
      transition: border-color .25s ease, transform .25s ease, box-shadow .25s ease;
    }

    .why-one-step.is-active {
      border-color: var(--teal-line);
      transform: translateY(-1px);
      box-shadow: var(--shadow-sm);
    }

    .why-one-step-num {
      font-family: var(--font-display);
      font-size: 16px;
      line-height: 1;
      color: #1f5a46;
    }

    .why-one-step-title {
      font-size: 13px;
      line-height: 1.3;
      color: var(--text);
      font-weight: 700;
    }

    .why-one-step-text {
      font-size: 11px;
      line-height: 1.45;
      color: var(--text-3);
    }

    .why-one-step-list {
      display: grid;
      gap: 2px;
      font-size: 10px;
      color: #345247;
      line-height: 1.45;
    }

    .why-one-step-list span::before {
      content: "•";
      margin-right: 4px;
      color: #1d8666;
      font-weight: 700;
    }

    .why-one-center {
      border: 1px dashed #cedbd4;
      border-radius: 14px;
      background: linear-gradient(180deg, #fcfdfc 0%, #f4f8f6 100%);
      display: grid;
      place-items: center;
      padding: 9px;
      min-height: 168px;
    }

    .why-one-avatar {
      width: 90px;
      height: 90px;
      border-radius: 50%;
      border: 1px solid #c6ddd3;
      background: #fff;
      display: grid;
      place-items: center;
      box-shadow: var(--shadow-sm);
      text-align: center;
      padding: 8px;
    }

    .why-one-avatar b {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      background: linear-gradient(160deg, #1fa67d, #176c54);
      color: #fff;
      display: grid;
      place-items: center;
      font-size: 20px;
      font-family: var(--font-display);
      margin: 0 auto 4px;
    }

    .why-one-avatar strong {
      display: block;
      font-size: 11px;
      line-height: 1.2;
      color: var(--text);
    }

    .why-one-avatar span {
      display: block;
      margin-top: 1px;
      font-size: 9px;
      color: var(--text-3);
      line-height: 1.2;
    }

    .why-one-guarantees {
      border: 1px solid #dce7e1;
      border-radius: 10px;
      background: #f3f8f5;
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 6px;
      padding: 7px;
    }

    .why-one-guarantee {
      display: flex;
      align-items: center;
      gap: 6px;
      border: 1px solid transparent;
      border-radius: 8px;
      background: #f8fbf9;
      padding: 5px 6px;
      font-size: 10px;
      line-height: 1.4;
      color: #2f5245;
      transition: border-color .25s ease, background .25s ease;
    }

    .why-one-guarantee i {
      width: 15px;
      height: 15px;
      border-radius: 50%;
      border: 1px solid #afcfc3;
      background: #fff;
      flex-shrink: 0;
    }

    .why-one-guarantee.is-active {
      border-color: #add4c6;
      background: #e7f4ee;
    }

    .why-one-results {
      display: grid;
      grid-template-columns: 1.05fr 1.4fr 1.3fr;
      gap: 8px;
    }

    .why-one-panel {
      border: 1px solid var(--border);
      border-radius: 12px;
      background: #fff;
      padding: 8px;
      display: grid;
      gap: 6px;
      box-shadow: var(--shadow-sm);
    }

    .why-one-panel-title {
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: .8px;
      color: var(--text-3);
      font-weight: 700;
      padding-bottom: 5px;
      border-bottom: 1px solid #edf0eb;
    }

    .why-one-funnel-row {
      display: grid;
      grid-template-columns: 1fr auto;
      align-items: center;
      gap: 6px;
      font-size: 10px;
      color: var(--text-3);
    }

    .why-one-funnel-bar {
      height: 9px;
      border-radius: 999px;
      background: #e8efec;
      overflow: hidden;
    }

    .why-one-funnel-bar i {
      display: block;
      height: 100%;
      width: 50%;
      background: linear-gradient(90deg, #98d1bf, #3b9e80);
      transition: width .4s ease;
    }

    .why-one-funnel-row b {
      font-size: 10px;
      color: #1f5a46;
    }

    .why-one-kpi-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 6px;
    }

    .why-one-kpi {
      border: 1px solid var(--border);
      border-radius: 8px;
      background: #fcfcfb;
      padding: 6px;
    }

    .why-one-kpi .k {
      font-size: 10px;
      color: var(--text-3);
    }

    .why-one-kpi .v {
      margin-top: 2px;
      font-family: var(--font-display);
      font-size: 23px;
      line-height: 1;
      color: #1f5a46;
    }

    .why-one-kpi .d {
      margin-top: 2px;
      font-size: 10px;
      color: var(--teal);
      font-weight: 700;
    }

    .why-one-line {
      margin-top: 4px;
      height: 16px;
      display: flex;
      align-items: end;
      gap: 2px;
    }

    .why-one-line i {
      flex: 1;
      min-height: 4px;
      border-radius: 999px;
      background: linear-gradient(180deg, #6ac0a4, #2b8f72);
      transition: height .45s ease;
    }

    .why-one-revenue-chart {
      height: 94px;
      border: 1px solid var(--border);
      border-radius: 9px;
      background: linear-gradient(180deg, #f9fcfa 0%, #f2f8f4 100%);
      padding: 7px;
      display: flex;
      align-items: end;
      gap: 6px;
    }

    .why-one-revenue-chart i {
      flex: 1;
      min-height: 8px;
      border-radius: 6px 6px 3px 3px;
      background: linear-gradient(180deg, #5cb896, #1d7f62);
      transition: height .45s ease;
    }

    .why-one-growth-badge {
      width: fit-content;
      padding: 2px 7px;
      border-radius: 999px;
      background: rgba(29, 134, 102, 0.12);
      border: 1px solid rgba(29, 134, 102, 0.32);
      color: #1b6d54;
      font-size: 10px;
      font-weight: 700;
    }

    .why-one-impact-list {
      display: grid;
      gap: 3px;
      font-size: 10px;
      line-height: 1.45;
      color: #305447;
    }

    .why-one-impact-list span::before {
      content: "✓";
      margin-right: 5px;
      color: #1d8666;
      font-weight: 700;
    }

    .why-one-principles {
      border: 1px solid #dce7e1;
      border-radius: 10px;
      background: #f3f8f5;
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 6px;
      padding: 7px;
    }

    .why-one-principle {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 5px 6px;
      border-radius: 8px;
      border: 1px solid transparent;
      font-size: 10px;
      line-height: 1.4;
      color: #305447;
      transition: border-color .25s ease, background .25s ease;
    }

    .why-one-principle i {
      width: 16px;
      height: 16px;
      border-radius: 5px;
      border: 1px solid #b6d4ca;
      background: #fff;
      flex-shrink: 0;
    }

    .why-one-principle.is-active {
      border-color: #a8d3c5;
      background: #e8f5ef;
    }

    .problem-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: var(--sp-3);
      margin-top: var(--sp-5);
    }

    .problem-card {
      padding: var(--sp-4);
      background: var(--bg-soft);
      border: 1px solid var(--border);
      border-left: 3px solid var(--border-strong);
      border-radius: var(--r-lg);
      font-size: 15px;
      line-height: 1.65;
      color: var(--text-2);
      transition: 0.2s ease;
    }

    .problem-card:hover {
      border-left-color: var(--teal);
      transform: translateX(5px);
      color: var(--text);
    }

    .service-hero {
      display: grid;
      grid-template-columns: 1.2fr 0.8fr;
      gap: var(--sp-6);
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--r-xl);
      padding: var(--sp-6);
      box-shadow: var(--shadow-sm);
      transition: 0.2s ease;
    }

    .service-hero:hover {
      border-color: var(--teal-line);
      box-shadow: var(--shadow-md);
    }

    .service-offer-layout {
      display: grid;
      grid-template-columns: 1fr;
      gap: 10px;
    }

    .service-seo-block {
      border: 1px solid var(--border);
      border-radius: 12px;
      background: linear-gradient(180deg, #fbfdfc 0%, #f5faf7 100%);
      padding: 10px;
      display: grid;
      gap: 7px;
    }

    .service-seo-title {
      font-size: 11px;
      letter-spacing: .8px;
      text-transform: uppercase;
      color: var(--text-3);
      font-weight: 700;
    }

    .service-seo-item {
      border: 1px solid #dbe7e2;
      border-radius: 9px;
      background: #fff;
      padding: 7px 8px;
    }

    .service-seo-item summary {
      cursor: pointer;
      font-size: 11px;
      font-weight: 700;
      color: var(--text);
      line-height: 1.35;
    }

    .service-seo-item p {
      margin-top: 6px;
      font-size: 11px;
      line-height: 1.5;
      color: var(--text-2);
    }

    .service-what-you-get {
      border: 1px solid #dce8e3;
      border-radius: 12px;
      background: #f4faf7;
      padding: 10px;
      display: grid;
      gap: 8px;
    }

    .service-what-title {
      font-size: 12px;
      font-weight: 800;
      color: #1f5a46;
      letter-spacing: .2px;
    }

    .service-what-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 6px;
    }

    .service-what-item {
      border: 1px solid #d8e5df;
      border-radius: 9px;
      background: #fff;
      padding: 8px;
      font-size: 11px;
      line-height: 1.45;
      color: #2e4e42;
    }

    .service-meta-visual {
      border: 1px solid var(--border);
      border-radius: 16px;
      background: linear-gradient(180deg, #fcfdfc 0%, #f7faf8 100%);
      padding: 10px;
      display: grid;
      gap: 8px;
      box-shadow: var(--shadow-sm);
    }

    .service-meta-layout {
      display: grid;
      grid-template-columns: .95fr 2fr 1fr;
      gap: 8px;
    }

    .service-meta-panel {
      border: 1px solid var(--border);
      border-radius: 12px;
      background: #fff;
      padding: 9px;
      display: grid;
      gap: 7px;
      box-shadow: var(--shadow-sm);
    }

    .service-meta-title {
      font-size: 10px;
      font-weight: 700;
      letter-spacing: .8px;
      text-transform: uppercase;
      color: var(--text-3);
      padding-bottom: 6px;
      border-bottom: 1px solid #edf0eb;
    }

    .web-flow-list {
      display: grid;
      gap: 6px;
    }

    .web-flow-item {
      display: grid;
      grid-template-columns: 32px 1fr;
      gap: 7px;
      align-items: start;
      border: 1px solid var(--border);
      border-radius: 9px;
      padding: 6px;
      background: #fcfcfb;
      transition: border-color .25s ease, transform .25s ease;
    }

    .web-flow-item.is-active {
      border-color: var(--teal-line);
      transform: translateX(2px);
      background: #eff8f4;
    }

    .web-flow-number {
      font-family: var(--font-display);
      font-size: 16px;
      line-height: 1;
      color: #184f3d;
      margin-top: 1px;
    }

    .web-flow-label strong {
      display: block;
      font-size: 10px;
      color: var(--text);
      margin-bottom: 2px;
    }

    .web-flow-label span {
      display: block;
      font-size: 9px;
      line-height: 1.3;
      color: var(--text-3);
    }

    .web-page-shell {
      border: 1px solid var(--border);
      border-radius: 12px;
      background: #fff;
      padding: 8px;
      display: grid;
      gap: 8px;
      box-shadow: var(--shadow-sm);
    }

    .web-page-topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 6px;
      padding: 5px 7px;
      border: 1px solid var(--border);
      border-radius: 8px;
      background: #fcfcfb;
    }

    .web-page-logo {
      width: 16px;
      height: 16px;
      border-radius: 4px;
      border: 1px solid var(--teal-line);
      background: var(--teal-soft);
    }

    .web-page-nav {
      display: flex;
      gap: 8px;
      font-size: 9px;
      color: var(--text-3);
    }

    .web-page-cta-pill {
      height: 20px;
      border-radius: 999px;
      background: linear-gradient(160deg, #1fa67d, #176c54);
      color: #fff;
      border: 0;
      font-size: 10px;
      font-weight: 700;
      padding: 0 10px;
    }

    .web-page-main {
      display: grid;
      grid-template-columns: 1.2fr .9fr;
      gap: 8px;
      align-items: stretch;
    }

    .web-page-copy {
      border: 1px solid var(--border);
      border-radius: 8px;
      background: linear-gradient(160deg, #fefefe 0%, #f6f9f7 100%);
      padding: 9px;
      display: grid;
      gap: 6px;
    }

    .web-page-eyebrow {
      font-size: 9px;
      text-transform: uppercase;
      letter-spacing: .8px;
      color: var(--text-3);
      font-weight: 700;
    }

    .web-page-headline {
      font-family: var(--font-display);
      font-size: 37px;
      line-height: .92;
      letter-spacing: -0.03em;
      color: var(--text);
      max-width: 10ch;
    }

    .web-page-headline em {
      font-style: normal;
      color: #1d8666;
    }

    .web-page-lead {
      font-size: 10px;
      color: var(--text-2);
      line-height: 1.5;
      max-width: 40ch;
    }

    .web-page-actions {
      display: flex;
      gap: 5px;
      flex-wrap: wrap;
    }

    .web-page-actions button {
      height: 22px;
      border-radius: 999px;
      border: 1px solid var(--border);
      padding: 0 10px;
      font-size: 9px;
      font-weight: 700;
      background: #fff;
      color: #2c3a35;
    }

    .web-page-actions button.is-primary {
      border-color: #1f8f6f;
      background: #1f8f6f;
      color: #fff;
    }

    .web-page-form {
      border: 1px solid var(--border);
      border-radius: 8px;
      background: #fcfcfb;
      padding: 7px;
      display: grid;
      gap: 5px;
    }

    .web-page-form i {
      display: block;
      height: 18px;
      border: 1px solid #dfe5e1;
      border-radius: 6px;
      background: #fff;
    }

    .web-page-form i:last-child {
      height: 22px;
      border-color: #2c8f70;
      background: linear-gradient(160deg, #1fa67d, #176c54);
    }

    .web-service-grid {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 6px;
    }

    .web-service-card {
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 7px 6px;
      background: #fcfcfb;
      font-size: 9px;
      line-height: 1.25;
      color: var(--text-3);
    }

    .web-service-card strong {
      display: block;
      margin-bottom: 2px;
      font-size: 10px;
      color: #20352d;
    }

    .web-logo-row {
      border: 1px solid var(--border);
      border-radius: 9px;
      background: #fcfcfb;
      padding: 6px;
      display: grid;
      grid-template-columns: repeat(5, minmax(0, 1fr));
      gap: 5px;
      align-items: center;
    }

    .web-logo-row span {
      height: 14px;
      border-radius: 999px;
      border: 1px solid #d7e0db;
      background: #fff;
    }

    .web-results-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 6px;
    }

    .web-result-card {
      border: 1px solid var(--border);
      border-radius: 9px;
      background: #fcfcfb;
      padding: 6px;
      display: grid;
      gap: 4px;
    }

    .web-result-label {
      font-size: 9px;
      color: var(--text-3);
    }

    .web-result-value {
      font-family: var(--font-display);
      font-size: 25px;
      line-height: 1;
      color: #1f5a46;
    }

    .web-result-delta {
      font-size: 9px;
      font-weight: 700;
      color: var(--teal);
    }

    .web-result-line {
      height: 20px;
      display: flex;
      align-items: end;
      gap: 2px;
    }

    .web-result-line i {
      flex: 1;
      min-height: 4px;
      border-radius: 999px;
      background: linear-gradient(180deg, #64be9e, #248a6c);
      transition: height .45s ease;
    }

    .web-bottom-cta {
      border: 1px solid var(--border);
      border-radius: 10px;
      background: #eef8f3;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 8px;
      padding: 8px;
    }

    .web-bottom-cta span {
      font-size: 10px;
      color: #24503f;
      font-weight: 600;
    }

    .web-bottom-cta button {
      height: 24px;
      border-radius: 999px;
      border: 0;
      background: linear-gradient(160deg, #1fa67d, #176c54);
      color: #fff;
      font-size: 10px;
      font-weight: 700;
      padding: 0 12px;
    }

    .web-side-rail {
      display: grid;
      gap: 8px;
    }

    .web-side-card {
      border: 1px solid var(--border);
      border-radius: 10px;
      background: #fff;
      padding: 8px;
      display: grid;
      gap: 6px;
      box-shadow: var(--shadow-sm);
    }

    .web-side-head {
      font-size: 9px;
      text-transform: uppercase;
      letter-spacing: .8px;
      color: var(--text-3);
      font-weight: 700;
      padding-bottom: 5px;
      border-bottom: 1px solid #edf0eb;
    }

    .web-side-kpi-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 6px;
    }

    .web-side-kpi {
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 5px;
      background: #fcfcfb;
    }

    .web-side-kpi .k {
      font-size: 9px;
      color: var(--text-3);
    }

    .web-side-kpi .v {
      margin-top: 2px;
      font-family: var(--font-display);
      font-size: 24px;
      line-height: 1;
      color: var(--text);
    }

    .web-side-kpi .d {
      margin-top: 2px;
      font-size: 9px;
      color: var(--teal);
      font-weight: 700;
    }

    .web-side-line {
      margin-top: 3px;
      height: 16px;
      display: flex;
      align-items: end;
      gap: 2px;
    }

    .web-side-line i {
      flex: 1;
      min-height: 4px;
      border-radius: 999px;
      background: linear-gradient(180deg, #64be9e, #248a6c);
      transition: height .45s ease;
    }

    .web-conversion-funnel {
      border: 1px solid var(--border);
      border-radius: 8px;
      background: #fcfcfb;
      padding: 7px;
      display: grid;
      gap: 5px;
    }

    .web-funnel-row {
      display: grid;
      grid-template-columns: 1fr auto;
      gap: 7px;
      align-items: center;
      font-size: 9px;
      color: var(--text-3);
    }

    .web-funnel-bar {
      height: 9px;
      border-radius: 999px;
      background: #e7efeb;
      overflow: hidden;
    }

    .web-funnel-bar i {
      display: block;
      height: 100%;
      width: 40%;
      background: linear-gradient(90deg, #98d1bf, #3b9e80);
      transition: width .4s ease;
    }

    .web-funnel-row b {
      color: #1f5a46;
      font-size: 10px;
    }

    .web-ux-list {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .web-ux-item {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 9px;
      line-height: 1.25;
      color: var(--text-2);
      padding: 4px 0;
    }

    .web-ux-item i {
      width: 13px;
      height: 13px;
      border-radius: 4px;
      border: 1px solid #b8d4ca;
      background: #fff;
      flex-shrink: 0;
    }

    .web-ux-item.is-active {
      color: #1f5a46;
      font-weight: 600;
    }

    .web-ux-item.is-active i {
      background: #edf7f2;
      border-color: var(--teal-line);
    }

    .web-badge-strip {
      border: 1px solid #dce7e1;
      border-radius: 10px;
      background: #f3f8f5;
      display: grid;
      grid-template-columns: repeat(5, minmax(0, 1fr));
      gap: 6px;
      padding: 7px;
    }

    .web-badge-item {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 9px;
      color: #28523f;
    }

    .web-badge-item i {
      width: 18px;
      height: 18px;
      border-radius: 6px;
      border: 1px solid #b6d4ca;
      background: #fff;
      flex-shrink: 0;
    }

    .service-case-snapshot {
      border: 1px solid #d8e6e0;
      border-radius: 12px;
      background: linear-gradient(180deg, #fbfdfc 0%, #f3f8f5 100%);
      padding: 10px;
      display: grid;
      gap: 8px;
    }

    .service-case-head {
      display: flex;
      justify-content: space-between;
      gap: 8px;
      flex-wrap: wrap;
      align-items: baseline;
    }

    .service-case-head strong {
      font-size: 12px;
      color: #204d3e;
    }

    .service-case-head span {
      font-size: 10px;
      color: var(--text-3);
    }

    .service-case-metrics {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 6px;
    }

    .service-case-metric {
      border: 1px solid #d9e6e0;
      border-radius: 8px;
      background: #fff;
      padding: 7px;
      display: grid;
      gap: 2px;
    }

    .service-case-metric span {
      font-size: 10px;
      color: var(--text-3);
    }

    .service-case-metric b {
      font-family: var(--font-display);
      font-size: 22px;
      line-height: 1;
      color: #1f5a46;
    }

    .service-case-lines {
      display: grid;
      gap: 6px;
    }

    .service-case-line {
      height: 10px;
      border-radius: 999px;
      background: #e4eee9;
      overflow: hidden;
    }

    .service-case-line i {
      display: block;
      height: 100%;
      width: 60%;
      background: linear-gradient(90deg, #58bd9a, #1f8d6d);
      border-radius: inherit;
      transition: width .85s ease;
    }

    .service-check-title {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 1.3px;
      text-transform: uppercase;
      color: var(--text-3);
      margin-bottom: var(--sp-2);
    }

    .service-checklist {
      display: grid;
      gap: 10px;
    }

    .service-check {
      display: flex;
      gap: 9px;
      align-items: flex-start;
      color: var(--text-2);
      font-size: 14px;
      line-height: 1.6;
    }

    .service-check-icon {
      width: 18px;
      height: 18px;
      border-radius: 50%;
      flex-shrink: 0;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-top: 1px;
      background: var(--teal-soft);
      border: 1px solid var(--teal-line);
      color: var(--teal);
      font-size: 11px;
      font-weight: 700;
    }

    .service-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: var(--sp-4);
      margin-top: var(--sp-4);
    }

    .service-card {
      padding: var(--sp-5);
      background: var(--bg-soft);
      border: 1px solid var(--border);
      border-radius: var(--r-lg);
      transition: 0.2s ease;
    }

    .service-card:hover {
      border-color: var(--teal-line);
      transform: translateY(-2px);
    }

    .service-top {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: var(--sp-2);
      margin-bottom: var(--sp-2);
    }

    .badge {
      display: inline-flex;
      align-items: center;
      border-radius: var(--r-pill);
      padding: 4px 12px;
      white-space: nowrap;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.3px;
    }

    .badge-green {
      background: var(--teal-soft);
      color: var(--teal-dark);
    }

    .badge-gray {
      background: var(--bg-muted);
      color: var(--text-2);
    }

    .chips {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-top: var(--sp-3);
    }

    .chip {
      display: inline-flex;
      align-items: center;
      font-size: 12px;
      color: var(--text-2);
      border: 1px solid var(--border-strong);
      border-radius: var(--r-sm);
      padding: 4px 10px;
      transition: 0.18s ease;
    }

    .chip:hover {
      border-color: var(--teal);
      color: var(--teal-dark);
    }

    .bonus {
      margin-top: var(--sp-4);
      padding: var(--sp-6);
      background: var(--teal-soft);
      border: 1.5px solid var(--teal-line);
      border-radius: var(--r-xl);
      transition: 0.2s ease;
    }

    .bonus:hover {
      border-color: var(--teal);
      box-shadow: var(--shadow-md);
    }

    .bonus-head {
      display: flex;
      align-items: center;
      gap: var(--sp-2);
      flex-wrap: wrap;
      margin-bottom: var(--sp-3);
    }

    .bonus-icon {
      width: 40px;
      height: 40px;
      border-radius: 12px;
      display: grid;
      place-items: center;
      background: var(--teal);
      color: #fff;
      flex-shrink: 0;
    }

    .bonus-title {
      font-family: var(--font-display);
      font-size: 20px;
      font-weight: 700;
      color: var(--teal-dark);
      flex: 1;
    }

    .bonus-tag {
      font-size: 11px;
      font-weight: 700;
      color: #fff;
      background: var(--teal);
      border-radius: var(--r-pill);
      padding: 4px 12px;
    }

    .bonus-body {
      font-size: 15px;
      line-height: 1.8;
      color: var(--teal-dark);
    }

    .bonus-chips {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-top: var(--sp-3);
    }

    .bonus-chip {
      background: rgba(255,255,255,0.58);
      border: 1px solid rgba(29,158,117,0.14);
      border-radius: var(--r-sm);
      padding: 4px 10px;
      font-size: 12px;
      color: var(--teal-dark);
    }

    .cta-band {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: var(--sp-5);
      flex-wrap: wrap;
      padding: var(--sp-6);
      background: var(--teal-soft);
      border: 1px solid var(--teal-line);
      border-radius: var(--r-xl);
    }

    .cta-band h3 {
      font-family: var(--font-display);
      font-size: 24px;
      line-height: 1.1;
      color: var(--teal-dark);
      margin-bottom: 6px;
    }

    .cta-band p {
      font-size: 15px;
      color: var(--teal-dark);
      line-height: 1.7;
      max-width: 640px;
    }

    .cta-dark {
      background: linear-gradient(145deg, #1a2420, #0e1a15);
      padding: var(--sp-12) 0;
    }

    .cta-dark-inner {
      display: grid;
      grid-template-columns: 1fr;
      gap: var(--sp-5);
    }

    .cta-dark h2 {
      font-family: var(--font-display);
      font-weight: 700;
      font-size: clamp(28px, 4vw, 42px);
      line-height: 1.1;
      color: #fff;
      margin-bottom: 12px;
    }

    .cta-dark p {
      font-size: 16px;
      color: rgba(255,255,255,0.55);
      line-height: 1.7;
    }

    .btn-white {
      display: inline-flex;
      align-items: center;
      gap: 9px;
      background: #fff;
      color: var(--teal-dark);
      font-size: 15px;
      font-weight: 700;
      padding: 15px 30px;
      border-radius: var(--r-md);
      white-space: nowrap;
      transition: 0.2s ease;
    }

    .btn-white:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 32px rgba(0,0,0,0.25);
    }

    .btn-white svg {
      transition: transform 0.2s;
    }

    .btn-white:hover svg {
      transform: translateX(4px);
    }

    @media (min-width: 860px) {
      .cta-dark-inner {
        grid-template-columns: 1fr auto;
        align-items: center;
      }
    }

    .industry-strip {
      border: 1px solid #d8e7e1;
      border-radius: 999px;
      background: #f5faf7;
      overflow: hidden;
      padding: 10px 0;
      position: relative;
    }

    .industry-strip-track {
      display: flex;
      gap: 26px;
      width: max-content;
      white-space: nowrap;
      align-items: center;
      animation: industryScroll 26s linear infinite;
    }

    .industry-strip-track span {
      font-size: 12px;
      color: #2d4d41;
      border: 1px solid #d2e1db;
      border-radius: 999px;
      background: #fff;
      padding: 6px 12px;
    }

    @keyframes industryScroll {
      from { transform: translateX(0); }
      to { transform: translateX(-50%); }
    }

    @media (prefers-reduced-motion: reduce) {
      .industry-strip-track {
        animation: none;
      }
    }

    .steps {
      max-width: 900px;
    }

    .step {
      display: grid;
      grid-template-columns: 56px minmax(0, 1fr);
      gap: var(--sp-4);
      padding: var(--sp-4) 0;
      border-bottom: 1px solid var(--border);
      transition: 0.2s ease;
    }

    .step:last-child {
      border-bottom: none;
    }

    .step:hover {
      transform: translateX(8px);
    }

    .step-num {
      font-family: var(--font-display);
      font-weight: 800;
      font-size: 36px;
      line-height: 1;
      color: var(--border-strong);
    }

    .step:hover .step-num {
      color: var(--teal);
    }

    .step-title {
      font-size: 17px;
      font-weight: 600;
      margin-bottom: 6px;
    }

    .step-desc {
      font-size: 14px;
      line-height: 1.75;
      color: var(--text-2);
      max-width: 760px;
    }

    .process-visual {
      border: 1px solid var(--border);
      border-radius: 16px;
      background: linear-gradient(180deg, #fcfdfc 0%, #f6faf7 100%);
      padding: 10px;
      display: grid;
      gap: 9px;
      box-shadow: var(--shadow-sm);
    }

    .process-track {
      display: grid;
      grid-template-columns: 1fr;
      gap: 8px;
      align-items: stretch;
    }

    .process-side {
      display: none;
      border: 1px dashed #d2dfd8;
      border-radius: 12px;
      background: #fbfdfb;
      text-align: center;
      place-items: center;
      padding: 9px;
      color: #355247;
      font-size: 10px;
      line-height: 1.35;
    }

    .process-side i {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      border: 1px solid #bfd8ce;
      background: #fff;
      margin: 0 auto 6px;
      display: grid;
      place-items: center;
      font-style: normal;
      font-size: 18px;
      color: #1e7f63;
      font-weight: 700;
    }

    .process-card {
      border: 1px solid var(--border);
      border-radius: 12px;
      background: #fff;
      padding: 9px;
      display: grid;
      gap: 7px;
      box-shadow: var(--shadow-sm);
      transition: border-color .25s ease, transform .25s ease, box-shadow .25s ease;
    }

    .process-card.is-active {
      border-color: var(--teal-line);
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
    }

    .process-step-dot {
      width: 30px;
      height: 30px;
      border-radius: 50%;
      background: #1f7f62;
      color: #fff;
      display: grid;
      place-items: center;
      font-size: 14px;
      font-weight: 700;
      font-family: var(--font-display);
    }

    .process-card-title {
      font-size: 14px;
      line-height: 1.3;
      font-weight: 700;
      color: var(--text);
      text-transform: uppercase;
      letter-spacing: .4px;
    }

    .process-card-sub {
      font-size: 10px;
      line-height: 1.35;
      color: var(--text-3);
      margin-top: -1px;
    }

    .process-card-list {
      display: grid;
      gap: 3px;
      font-size: 9px;
      color: #365549;
      line-height: 1.35;
      padding-top: 4px;
      border-top: 1px solid #edf1ee;
    }

    .process-card-list span::before {
      content: "✓";
      margin-right: 5px;
      color: #1d8666;
      font-weight: 700;
    }

    .process-card-effect {
      margin-top: 2px;
      border: 1px solid #e3ebe6;
      border-radius: 8px;
      background: #f8fbf9;
      padding: 7px;
      font-size: 9px;
      line-height: 1.3;
      color: #3a5549;
    }

    .process-card-effect strong {
      display: block;
      margin-bottom: 2px;
      color: #2b4a3e;
    }

    .process-impact {
      border: 1px solid #dce7e1;
      border-radius: 10px;
      background: #f3f8f5;
      padding: 7px;
      display: grid;
      grid-template-columns: 1fr;
      gap: 6px;
    }

    .process-impact-head {
      font-size: 10px;
      text-transform: uppercase;
      letter-spacing: .8px;
      color: var(--text-3);
      font-weight: 700;
    }

    .process-impact-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 6px;
    }

    .process-impact-card {
      border: 1px solid #dbe7e1;
      border-radius: 9px;
      background: #fff;
      padding: 6px;
      display: grid;
      gap: 4px;
    }

    .process-impact-card .k {
      font-size: 9px;
      line-height: 1.3;
      color: var(--text-3);
    }

    .process-impact-card .v {
      font-family: var(--font-display);
      font-size: 28px;
      line-height: 1;
      color: #1f5a46;
    }

    .process-impact-line {
      height: 24px;
      display: flex;
      align-items: end;
      gap: 2px;
    }

    .process-impact-line i {
      flex: 1;
      min-height: 7px;
      border-radius: 999px;
      background: linear-gradient(180deg, #67bf9f, #278b6d);
      transition: height .45s ease;
    }

    .case-portfolio-visual {
      border: 1px solid var(--border);
      border-radius: 16px;
      background: linear-gradient(180deg, #fcfdfc 0%, #f6faf7 100%);
      padding: 10px;
      display: grid;
      gap: 8px;
      box-shadow: var(--shadow-sm);
    }

    .case-portfolio-layout {
      display: grid;
      grid-template-columns: 1fr;
      gap: 8px;
    }

    .case-panel {
      border: 1px solid var(--border);
      border-radius: 12px;
      background: #fff;
      padding: 9px;
      display: grid;
      gap: 7px;
      box-shadow: var(--shadow-sm);
    }

    .case-kicker {
      font-size: 10px;
      text-transform: uppercase;
      letter-spacing: .8px;
      color: var(--text-3);
      font-weight: 700;
    }

    .case-main-title {
      font-family: var(--font-display);
      font-size: 41px;
      line-height: .92;
      letter-spacing: -0.03em;
      color: #22332c;
      max-width: 15ch;
    }

    .case-desc {
      font-size: 11px;
      line-height: 1.55;
      color: var(--text-2);
      max-width: 46ch;
    }

    .case-meta-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 6px;
    }

    .case-meta-card {
      border: 1px solid var(--border);
      border-radius: 9px;
      background: #fcfcfb;
      padding: 6px;
      font-size: 9px;
      line-height: 1.35;
      color: var(--text-3);
    }

    .case-meta-card strong {
      display: block;
      font-size: 10px;
      color: var(--text);
      margin-bottom: 2px;
    }

    .case-score-grid {
      border: 1px solid var(--border);
      border-radius: 9px;
      background: #fcfcfb;
      padding: 7px;
      display: grid;
      gap: 4px;
    }

    .case-score-row {
      display: grid;
      grid-template-columns: 1fr;
      gap: 5px;
      align-items: center;
      font-size: 9px;
      color: var(--text-3);
    }

    .case-score-row .after {
      color: #1f5a46;
      font-weight: 700;
    }

    .case-score-row .delta {
      font-size: 9px;
      font-weight: 700;
      color: var(--teal);
    }

    .case-done-grid {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 6px;
    }

    .case-done-item {
      border: 1px solid var(--border);
      border-radius: 8px;
      background: #fcfcfb;
      padding: 6px;
      font-size: 9px;
      line-height: 1.3;
      color: var(--text-3);
      text-align: center;
    }

    .case-done-item strong {
      display: block;
      margin-bottom: 2px;
      font-size: 10px;
      color: #1f5a46;
    }

    .case-impact-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 6px;
    }

    .case-impact-list {
      border: 1px solid var(--border);
      border-radius: 9px;
      background: #fcfcfb;
      padding: 7px;
      display: grid;
      gap: 3px;
      font-size: 9px;
      line-height: 1.35;
      color: #355247;
    }

    .case-impact-list span::before {
      content: "✓";
      margin-right: 5px;
      color: #1d8666;
      font-weight: 700;
    }

    .case-impact-bars {
      border: 1px solid var(--border);
      border-radius: 9px;
      background: #fff;
      padding: 7px;
      display: grid;
      gap: 6px;
    }

    .case-impact-bar-row {
      display: grid;
      grid-template-columns: 1fr auto;
      gap: 6px;
      align-items: center;
      font-size: 9px;
      color: var(--text-3);
    }

    .case-impact-bar {
      height: 14px;
      border-radius: 999px;
      background: #e8efec;
      overflow: hidden;
    }

    .case-impact-bar i {
      display: block;
      height: 100%;
      width: 50%;
      background: linear-gradient(90deg, #99d2c0, #3c9f81);
      transition: width .4s ease;
    }

    .case-impact-bar-row b {
      font-size: 10px;
      color: #1f5a46;
    }

    .case-quote {
      border: 1px solid #dce7e1;
      border-radius: 10px;
      background: #f3f8f5;
      padding: 8px;
      font-size: 10px;
      line-height: 1.4;
      color: #355247;
    }

    .case-quote strong {
      display: block;
      margin-top: 4px;
      font-size: 10px;
      color: #27493d;
    }

    .portfolio-topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 6px;
      padding: 6px 7px;
      border: 1px solid var(--border);
      border-radius: 8px;
      background: #fcfcfb;
      font-size: 9px;
      color: var(--text-3);
    }

    .portfolio-screen {
      border: 1px solid var(--border);
      border-radius: 10px;
      background: #fff;
      padding: 7px;
      display: grid;
      gap: 7px;
    }

    .portfolio-hero {
      border: 1px solid var(--border);
      border-radius: 8px;
      background: linear-gradient(145deg, #f7f9f8 0%, #edf4f1 100%);
      padding: 8px;
      display: grid;
      grid-template-columns: 1fr;
      gap: 6px;
      align-items: center;
    }

    .portfolio-preview-shell {
      display: grid;
      grid-template-columns: 1fr;
      gap: 6px;
      align-items: start;
    }

    .portfolio-hero-copy strong {
      display: block;
      font-family: var(--font-display);
      font-size: 19px;
      line-height: .95;
      color: #273530;
      margin-bottom: 4px;
      max-width: 15ch;
    }

    .portfolio-hero-copy span {
      display: block;
      font-size: 9px;
      color: var(--text-3);
      line-height: 1.35;
      margin-bottom: 4px;
    }

    .portfolio-hero-cta {
      height: 20px;
      border-radius: 999px;
      border: 0;
      background: linear-gradient(160deg, #1fa67d, #176c54);
      color: #fff;
      font-size: 9px;
      font-weight: 700;
      padding: 0 10px;
    }

    .portfolio-hero-preview {
      height: 86px;
      border: 1px solid #d8e3de;
      border-radius: 8px;
      background: linear-gradient(135deg, #dfe7e3, #eef3f1);
      position: relative;
      overflow: hidden;
    }

    .portfolio-hero-preview::before {
      content: "";
      position: absolute;
      inset: 20px 16px;
      border: 1px solid #b9c9c2;
      border-radius: 4px;
    }

    .portfolio-mini-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 6px;
    }

    .portfolio-mini-card {
      border: 1px solid var(--border);
      border-radius: 8px;
      background: #fcfcfb;
      padding: 6px;
      font-size: 9px;
      color: var(--text-3);
      line-height: 1.3;
    }

    .portfolio-mini-card .line {
      margin-top: 4px;
      height: 24px;
      display: flex;
      align-items: end;
      gap: 2px;
    }

    .portfolio-mini-card .line i {
      flex: 1;
      min-height: 6px;
      border-radius: 999px;
      background: linear-gradient(180deg, #67bf9f, #278b6d);
      transition: height .45s ease;
    }

    .portfolio-mobile {
      border: 1px solid var(--border);
      border-radius: 12px;
      background: #fff;
      padding: 6px;
      width: 78px;
      justify-self: start;
    }

    .portfolio-mobile-screen {
      border: 1px solid #d8e3de;
      border-radius: 9px;
      height: 154px;
      background: linear-gradient(180deg, #f9fbfa 0%, #edf4f1 100%);
    }

    .portfolio-realizations {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 6px;
    }

    .portfolio-card {
      border: 1px solid var(--border);
      border-radius: 8px;
      background: #fcfcfb;
      padding: 6px;
      display: grid;
      gap: 4px;
    }

    .portfolio-thumb {
      height: 62px;
      border-radius: 7px;
      border: 1px solid #d4dfda;
      background: linear-gradient(135deg, #dce5e0, #eef3f1);
      position: relative;
      overflow: hidden;
    }

    .portfolio-thumb::before {
      content: "";
      position: absolute;
      inset: 12px 10px;
      border: 1px solid #b6c7c0;
      border-radius: 3px;
    }

    .portfolio-card-title {
      font-size: 9px;
      color: #355247;
      line-height: 1.3;
      font-weight: 600;
    }

    .portfolio-kpi-strip {
      border: 1px solid #dce7e1;
      border-radius: 10px;
      background: #f3f8f5;
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 6px;
      padding: 7px;
    }

    .portfolio-kpi-item {
      border: 1px solid transparent;
      border-radius: 8px;
      background: #f9fcfa;
      padding: 5px 6px;
      text-align: center;
      transition: border-color .25s ease, background .25s ease;
    }

    .portfolio-kpi-item strong {
      display: block;
      font-family: var(--font-display);
      font-size: 24px;
      line-height: 1;
      color: #1f5a46;
      margin-bottom: 2px;
    }

    .portfolio-kpi-item span {
      font-size: 9px;
      color: #355247;
      line-height: 1.3;
    }

    .portfolio-kpi-item.is-active {
      border-color: #add4c6;
      background: #e8f5ef;
    }

    .process-impact-line i,
    .contact-metric-line i,
    .portfolio-mini-card .line i {
      animation: uiSparkPulse 2.4s ease-in-out infinite;
    }

    .process-impact-line i:nth-child(2),
    .contact-metric-line i:nth-child(2),
    .portfolio-mini-card .line i:nth-child(2) { animation-delay: .2s; }
    .process-impact-line i:nth-child(3),
    .contact-metric-line i:nth-child(3),
    .portfolio-mini-card .line i:nth-child(3) { animation-delay: .4s; }
    .process-impact-line i:nth-child(4),
    .contact-metric-line i:nth-child(4),
    .portfolio-mini-card .line i:nth-child(4) { animation-delay: .6s; }

    @keyframes uiSparkPulse {
      0%, 100% { transform: translateY(0); opacity: .82; }
      50% { transform: translateY(-2px); opacity: 1; }
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: var(--sp-4);
      margin-top: var(--sp-5);
    }

    .stat-card {
      padding: var(--sp-4);
      background: var(--bg-soft);
      border: 1px solid var(--border);
      border-radius: var(--r-lg);
      transition: 0.2s ease;
    }

    .stat-card:hover {
      border-color: var(--teal-line);
      transform: translateY(-3px);
    }

    .stat-num {
      font-family: var(--font-display);
      font-size: 28px;
      font-weight: 700;
      line-height: 1;
      color: var(--teal);
    }

    .stat-text {
      margin-top: 7px;
      font-size: 13px;
      line-height: 1.5;
      color: var(--text-2);
    }

    .cases {
      display: grid;
      gap: var(--sp-3);
      margin-top: var(--sp-5);
    }

    .case {
      padding: var(--sp-5);
      background: var(--bg-soft);
      border: 1px solid var(--border);
      border-left: 3px solid var(--teal);
      border-radius: var(--r-lg);
      transition: 0.2s ease;
    }

    .case:hover {
      transform: translateX(5px);
      box-shadow: var(--shadow-sm);
    }

    .case-tag {
      display: inline-flex;
      margin-bottom: var(--sp-2);
      font-size: 11px;
      font-weight: 700;
      color: var(--teal-dark);
      background: var(--teal-soft);
      border-radius: 4px;
      padding: 4px 9px;
    }

    .case-title {
      font-size: 17px;
      font-weight: 600;
      margin-bottom: 8px;
    }

    .case-body {
      font-size: 14px;
      line-height: 1.75;
      color: var(--text-2);
    }

    .case-result {
      display: inline-flex;
      margin-top: var(--sp-3);
      font-size: 13px;
      font-weight: 600;
      color: var(--teal-dark);
      background: rgba(29,158,117,0.12);
      border-radius: var(--r-sm);
      padding: 6px 12px;
    }

    .fit-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: var(--sp-4);
    }

    .fit-card {
      padding: var(--sp-5);
      border-radius: var(--r-lg);
    }

    .fit-card.yes {
      background: var(--teal-soft);
      border: 1px solid var(--teal-line);
    }

    .fit-card.no {
      background: var(--bg-soft);
      border: 1px solid var(--border);
    }

    .fit-label {
      font-family: var(--font-display);
      font-size: 15px;
      font-weight: 700;
      margin-bottom: var(--sp-3);
    }

    .fit-card.yes .fit-label {
      color: var(--teal-dark);
    }

    .fit-card.no .fit-label {
      color: var(--text-2);
    }

    .fit-list {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .fit-item {
      display: flex;
      gap: 9px;
      align-items: flex-start;
      font-size: 14px;
      line-height: 1.6;
    }

    .fit-card.yes .fit-item {
      color: var(--teal-dark);
    }

    .fit-card .btn {
      margin-top: var(--sp-4);
    }

    .fit-card.no .fit-item {
      color: var(--text-2);
    }

    .fit-icon {
      flex-shrink: 0;
      margin-top: 1px;
      font-style: normal;
    }

    .faq {
      max-width: 900px;
    }

    .faq-item {
      border-bottom: 1px solid var(--border);
    }

    .faq-item:last-child {
      border-bottom: none;
    }

    .faq-q {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: var(--sp-3);
      padding: var(--sp-3) 0;
      font-size: 15px;
      font-weight: 600;
      width: 100%;
      border: 0;
      background: transparent;
      text-align: left;
      cursor: pointer;
      transition: 0.18s ease;
      user-select: none;
    }

    .faq-q:hover {
      color: var(--teal);
    }

    .faq-icon {
      width: 28px;
      height: 28px;
      border: 1px solid var(--border-strong);
      border-radius: 50%;
      display: grid;
      place-items: center;
      flex-shrink: 0;
      font-size: 16px;
      color: var(--text-3);
      transition: 0.25s ease;
    }

    .faq-item.open .faq-icon {
      transform: rotate(45deg);
      background: var(--teal-soft);
      border-color: var(--teal-line);
      color: var(--teal);
    }

    .faq-a {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.35s ease, padding 0.25s ease;
      font-size: 14px;
      line-height: 1.8;
      color: var(--text-2);
    }

    .faq-item.open .faq-a {
      max-height: 320px;
      padding-bottom: var(--sp-3);
    }

    .form-shell {
      padding: var(--sp-6);
      background: linear-gradient(180deg, var(--bg-soft), var(--surface));
      border: 1px solid var(--border);
      border-top: 3px solid var(--teal);
      border-radius: var(--r-xl);
      box-shadow: var(--shadow-md);
    }

    .form-head {
      max-width: 720px;
      margin-bottom: var(--sp-5);
    }

    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: var(--sp-3);
    }

    .field {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .field.full {
      grid-column: 1 / -1;
    }

    .field label {
      font-size: 12px;
      font-weight: 600;
      color: var(--text-2);
    }

    .input,
    .textarea,
    .select {
      width: 100%;
      min-height: 46px;
      border: 1px solid var(--border-strong);
      background: var(--surface);
      color: var(--text);
      border-radius: var(--r-md);
      padding: 13px 15px;
      font-size: 16px;
      outline: none;
      transition: 0.18s ease;
    }

    .textarea {
      min-height: 110px;
      resize: vertical;
      line-height: 1.6;
    }

    .input::placeholder,
    .textarea::placeholder {
      color: var(--text-3);
    }

    .input:focus,
    .textarea:focus,
    .select:focus {
      border-color: var(--teal);
      box-shadow: 0 0 0 3px rgba(29,158,117,0.13);
    }

    .input.error,
    .textarea.error {
      border-color: var(--danger);
    }

    .field-error {
      display: none;
      font-size: 12px;
      color: var(--danger);
    }

    .field-error.show {
      display: block;
    }

    .submit {
      margin-top: var(--sp-3);
      width: 100%;
      justify-content: center;
    }

    .form-note {
      margin-top: var(--sp-2);
      font-size: 12px;
      color: var(--text-3);
      text-align: center;
    }

    .form-alt {
      display: flex;
      gap: var(--sp-4);
      flex-wrap: wrap;
      margin-top: var(--sp-4);
      padding-top: var(--sp-4);
      border-top: 1px solid var(--border);
    }

    .form-alt-item {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 13px;
      color: var(--text-2);
    }

    .form-alt-item a {
      color: var(--teal);
      font-weight: 600;
    }

    .contact-strategy-layout {
      display: grid;
      grid-template-columns: 1.2fr .9fr;
      gap: 10px;
      align-items: start;
    }

    .contact-intro-bar {
      border: 1px solid var(--border);
      border-radius: 14px;
      background: linear-gradient(180deg, #fcfdfc 0%, #f6faf7 100%);
      padding: 12px;
      margin-bottom: 10px;
      box-shadow: var(--shadow-sm);
    }

    .contact-intro-bar .contact-strategy-title {
      max-width: none;
      font-size: clamp(30px, 6vw, 46px);
    }

    .contact-intro-bar .contact-strategy-lead {
      max-width: 72ch;
    }

    .contact-strategy-info {
      border: 1px solid var(--border);
      border-radius: 14px;
      background: linear-gradient(180deg, #fcfdfc 0%, #f6faf7 100%);
      padding: 10px;
      display: grid;
      gap: 9px;
      box-shadow: var(--shadow-sm);
    }

    .contact-strategy-top {
      display: grid;
      gap: 6px;
    }

    .contact-strategy-title {
      font-family: var(--font-display);
      font-size: 46px;
      line-height: .9;
      letter-spacing: -0.03em;
      color: #22332c;
      max-width: 15ch;
    }

    .contact-strategy-title .accent {
      color: #1f7f62;
    }

    .contact-strategy-lead {
      font-size: 12px;
      color: var(--text-2);
      line-height: 1.55;
      max-width: 46ch;
    }

    .contact-strategy-flow {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 6px;
    }

    .contact-flow-step {
      border: 1px solid var(--border);
      border-radius: 9px;
      background: #fff;
      padding: 7px 6px;
      text-align: center;
      transition: border-color .25s ease, transform .25s ease, background .25s ease;
    }

    .contact-flow-step i {
      width: 22px;
      height: 22px;
      border-radius: 50%;
      border: 1px solid #c7dad1;
      background: #f4f8f6;
      margin: 0 auto 4px;
      display: grid;
      place-items: center;
      font-style: normal;
      font-size: 12px;
      color: #1f7f62;
    }

    .contact-flow-step strong {
      display: block;
      font-size: 10px;
      line-height: 1.3;
      color: var(--text);
      margin-bottom: 2px;
    }

    .contact-flow-step span {
      display: block;
      font-size: 9px;
      line-height: 1.3;
      color: var(--text-3);
    }

    .contact-flow-step.is-active {
      border-color: var(--teal-line);
      transform: translateY(-1px);
      background: #edf8f3;
    }

    .contact-strategy-panels {
      display: grid;
      grid-template-columns: 1.15fr 1fr 1fr;
      gap: 6px;
    }

    .contact-panel {
      border: 1px solid var(--border);
      border-radius: 9px;
      background: #fff;
      padding: 7px;
      display: grid;
      gap: 5px;
    }

    .contact-panel-title {
      font-size: 9px;
      text-transform: uppercase;
      letter-spacing: .8px;
      color: var(--text-3);
      font-weight: 700;
      padding-bottom: 4px;
      border-bottom: 1px solid #edf0eb;
    }

    .contact-list {
      display: grid;
      gap: 3px;
      font-size: 9px;
      color: #355247;
      line-height: 1.35;
    }

    .contact-list span::before {
      content: "✓";
      margin-right: 5px;
      color: #1d8666;
      font-weight: 700;
    }

    .contact-metric-row {
      display: grid;
      grid-template-columns: 1fr auto;
      gap: 6px;
      align-items: center;
      font-size: 9px;
      color: var(--text-3);
    }

    .contact-metric-line {
      height: 22px;
      display: flex;
      align-items: end;
      gap: 2px;
    }

    .contact-metric-line i {
      flex: 1;
      min-height: 6px;
      border-radius: 999px;
      background: linear-gradient(180deg, #67bf9f, #278b6d);
      transition: height .45s ease;
    }

    .contact-mini-note {
      border: 1px solid #dce7e1;
      border-radius: 8px;
      background: #f5f9f7;
      padding: 6px;
      font-size: 9px;
      color: #38554a;
      line-height: 1.35;
    }

    .contact-proofs {
      border: 1px solid #dce7e1;
      border-radius: 10px;
      background: #f3f8f5;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 6px;
      padding: 7px;
    }

    .contact-proof-item {
      display: flex;
      align-items: center;
      gap: 6px;
      border: 1px solid transparent;
      border-radius: 8px;
      background: #f9fcfa;
      padding: 5px 6px;
      font-size: 9px;
      color: #305447;
      line-height: 1.3;
      transition: border-color .25s ease, background .25s ease;
    }

    .contact-proof-item i {
      width: 16px;
      height: 16px;
      border-radius: 5px;
      border: 1px solid #b6d4ca;
      background: #fff;
      flex-shrink: 0;
    }

    .contact-proof-item.is-active {
      border-color: #a8d3c5;
      background: #e8f5ef;
    }

    .contact-strategy-form {
      border: 1px solid var(--border);
      border-radius: 14px;
      background: #fff;
      padding: 10px;
      box-shadow: var(--shadow-md);
      display: grid;
      gap: 8px;
    }

    .footer {
      padding: var(--sp-8) 0 var(--sp-5);
      border-top: 1px solid var(--border);
    }

    .footer-inner {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: var(--sp-5);
      flex-wrap: wrap;
    }

    .footer-brand {
      max-width: 420px;
    }

    .footer-col {
      display: flex;
      flex-direction: column;
      gap: 8px;
      min-width: 160px;
    }

    .footer-col-label {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: var(--text-3);
      margin-bottom: 4px;
    }

    .footer-links {
      display: flex;
      flex-direction: column;
      gap: 9px;
      align-items: flex-end;
    }

    .footer-links a {
      font-size: 14px;
      color: var(--text-3);
      transition: 0.18s ease;
    }

    .footer-links a:hover {
      color: var(--teal);
    }

    .footer-copy {
      margin-top: var(--sp-5);
      padding-top: var(--sp-3);
      border-top: 1px solid var(--border);
      font-size: 12px;
      color: var(--text-3);
      text-align: center;
    }

    .scroll-top {
      position: fixed;
      right: 28px;
      bottom: 28px;
      z-index: 50;
      width: 46px;
      height: 46px;
      border-radius: 50%;
      border: none;
      background: var(--teal);
      color: #fff;
      font-size: 18px;
      cursor: pointer;
      display: grid;
      place-items: center;
      opacity: 0;
      transform: translateY(10px);
      transition: 0.25s ease;
      box-shadow: var(--shadow-md);
    }

    .scroll-top.visible {
      opacity: 1;
      transform: translateY(0);
    }

    .scroll-top:hover {
      background: var(--teal-dark);
    }

    .reveal {
      opacity: 0;
      transform: translateY(24px);
      transition: opacity 0.85s cubic-bezier(.16,1,.3,1), transform 0.85s cubic-bezier(.16,1,.3,1);
    }

    .reveal.visible {
      opacity: 1;
      transform: translateY(0);
    }

    .d1 { transition-delay: 0.12s; }
    .d2 { transition-delay: 0.24s; }
    .d3 { transition-delay: 0.36s; }

    .wrap {
      width: min(var(--container), calc(100% - 32px));
    }

    .nav-links,
    .nav-actions {
      display: none;
    }

    .hamburger {
      display: flex;
    }

    .mobile-menu {
      display: block;
    }

    .section {
      padding: var(--sp-8) 0;
    }

    .section-sm {
      padding: var(--sp-6) 0;
    }

    .hero-grid,
    .split,
    .service-hero,
    .problem-grid,
    .service-grid,
    .stats-grid,
    .fit-grid,
    .form-grid {
      grid-template-columns: 1fr;
    }

    .hero-aside {
      display: block;
      margin-top: 6px;
    }

    .hero-system-top {
      grid-template-columns: 1fr;
    }

    .hero-system-head {
      grid-template-columns: 1fr;
    }

    .hero-system-side-title {
      font-size: 22px;
    }

    .hero-core-main {
      grid-template-columns: 1fr;
    }

    .hero-core-grid {
      grid-template-columns: 1fr 1fr;
    }

    .hero-kpi-mini-grid {
      grid-template-columns: 1fr;
    }

    .hero-system-pipe {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .hero-system-bottom {
      grid-template-columns: 1fr;
    }

    .hero-chaos-note-grid {
      grid-template-columns: 1fr;
    }

    .hero-analytics-grid {
      grid-template-columns: 1fr 1fr;
    }

    .service-meta-layout {
      grid-template-columns: 1fr;
    }

    .web-side-kpi-grid,
    .web-results-grid,
    .web-badge-strip {
      grid-template-columns: 1fr 1fr;
    }

    .web-service-grid,
    .web-page-main {
      grid-template-columns: 1fr;
    }

    .why-trust-layout,
    .why-facts,
    .why-result-grid,
    .why-principles {
      grid-template-columns: 1fr;
    }

    .why-process-ops {
      grid-template-columns: 1fr;
    }

    .why-one-process,
    .why-one-results,
    .why-one-kpi-grid,
    .why-one-guarantees,
    .why-one-principles {
      grid-template-columns: 1fr;
    }

    .process-impact-grid {
      grid-template-columns: 1fr;
    }

    .contact-strategy-layout,
    .contact-strategy-flow,
    .contact-strategy-panels,
    .contact-proofs {
      grid-template-columns: 1fr;
    }

    .case-portfolio-layout,
    .case-meta-grid,
    .case-impact-grid,
    .portfolio-kpi-strip,
    .portfolio-realizations,
    .case-done-grid,
    .contact-strategy-panels {
      grid-template-columns: 1fr;
    }

    .contact-strategy-title {
      font-size: 34px;
    }

    .case-main-title {
      font-size: 33px;
    }

    .why-one-heading {
      font-size: 33px;
    }

    .cta-band,
    .footer-inner {
      flex-direction: column;
    }

    .footer-links {
      align-items: flex-start;
    }

    .form-shell,
    .bonus,
    .service-hero {
      padding: var(--sp-4);
    }

    .hero-copy .lead {
      font-size: 17px;
    }

    @media (min-width: 761px) {
      .wrap {
        width: min(var(--container), calc(100% - 48px));
      }

      .brand-logo {
        height: 74px;
        max-width: 520px;
      }

      .nav-links,
      .nav-actions {
        display: flex;
      }

      .hamburger,
      .mobile-menu {
        display: none;
      }

      .hero-actions .btn {
        width: auto;
      }

      .hero-system-top {
        grid-template-columns: 1fr 1.2fr;
      }

      .hero-system-head {
        grid-template-columns: 1fr 1fr;
      }

      .hero-kpi-stack {
        grid-column: 1 / -1;
        grid-template-columns: 1fr;
      }

      .hero-core-main {
        grid-template-columns: 1.2fr .9fr;
      }

      .hero-kpi-mini-grid {
        grid-template-columns: 1fr 1fr;
      }

      .hero-system-bottom {
        grid-template-columns: 1.4fr auto 1fr;
      }

      .hero-chaos-note-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }

      .hero-analytics-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
      }

      .service-meta-layout {
        grid-template-columns: 1fr 1.5fr;
      }

      .service-offer-layout {
        grid-template-columns: 1.25fr .95fr;
      }

      .service-what-you-get {
        grid-column: 1 / -1;
      }

      .web-side-rail {
        grid-column: 1 / -1;
      }

      .web-side-kpi-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .web-service-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .web-badge-strip {
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }

      .why-trust-layout {
        grid-template-columns: 1fr 1.4fr;
      }

      .why-trust-layout > :nth-child(3) {
        grid-column: 1 / -1;
      }

      .why-facts {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .why-result-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .why-principles {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .why-process-ops {
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }

      .why-one-process {
        grid-template-columns: 1fr 1fr;
      }

      .why-one-process .why-one-center {
        grid-column: 1 / -1;
      }

      .why-one-guarantees,
      .why-one-principles {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .why-one-results {
        grid-template-columns: 1fr 1fr;
      }

      .why-one-results .why-one-panel:last-child {
        grid-column: 1 / -1;
      }

      .process-track {
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }

      .process-impact-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }

      .contact-strategy-flow {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .contact-strategy-panels {
        grid-template-columns: 1fr 1fr;
      }

      .contact-strategy-panels .contact-panel:first-child {
        grid-column: 1 / -1;
      }

      .contact-intro-bar {
        padding: 14px;
      }

      .contact-proofs {
        grid-template-columns: 1fr 1fr;
      }

      .case-portfolio-layout {
        grid-template-columns: 1fr;
      }

      .case-meta-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }

      .case-score-row {
        grid-template-columns: 1.6fr 1fr 1fr auto;
      }

      .case-impact-grid {
        grid-template-columns: 1fr 1fr;
      }

      .portfolio-hero {
        grid-template-columns: 1.2fr .8fr;
      }

      .portfolio-preview-shell {
        grid-template-columns: 1fr 80px;
      }

      .portfolio-mobile {
        justify-self: end;
      }

      .portfolio-mini-grid {
        grid-template-columns: 1.5fr 1fr;
      }

      .portfolio-realizations {
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }

      .portfolio-kpi-strip {
        grid-template-columns: repeat(4, minmax(0, 1fr));
      }

      .case-done-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
      }

      .input,
      .textarea,
      .select {
        font-size: 15px;
      }
    }

    @media (min-width: 981px) {
      .section {
        padding: var(--sp-12) 0;
      }

      .section-sm {
        padding: var(--sp-8) 0;
      }

      .hero-grid,
      .split,
      .service-hero {
        grid-template-columns: 1fr;
      }

      .hero-aside {
        display: block;
      }

      .hero-system-top {
        grid-template-columns: 1fr;
      }

      .hero-kpi-stack {
        grid-column: 1 / -1;
        grid-template-columns: 1fr;
      }

      .hero-system-bottom {
        grid-template-columns: 1fr;
      }

      .hero-optimization-node {
        grid-column: auto;
        justify-self: start;
      }

      .problem-grid,
      .service-grid,
      .stats-grid,
      .fit-grid,
      .form-grid {
        grid-template-columns: 1fr 1fr;
      }

      .service-meta-layout {
        grid-template-columns: 1fr;
      }

      .service-offer-layout {
        grid-template-columns: 1.15fr .95fr;
      }

      .web-side-rail {
        grid-column: auto;
      }

      .web-service-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .web-results-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .web-badge-strip {
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }

      .service-case-metrics {
        grid-template-columns: repeat(4, minmax(0, 1fr));
      }

      .why-trust-layout {
        grid-template-columns: 1fr;
      }

      .why-trust-layout > :nth-child(3) {
        grid-column: 1 / -1;
      }

      .why-facts {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .why-principles {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .why-one-heading {
        font-size: 49px;
      }

      .why-one-process {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .why-one-process .why-one-center {
        grid-column: 1 / -1;
      }

      .why-one-guarantees,
      .why-one-principles {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .why-one-results {
        grid-template-columns: 1fr;
      }

      .why-one-results .why-one-panel:last-child {
        grid-column: auto;
      }

      .process-track {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .process-side {
        display: none;
      }

      .process-impact-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .contact-strategy-layout {
        grid-template-columns: 1fr;
      }

      .contact-strategy-flow {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .contact-strategy-panels {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .contact-strategy-panels .contact-panel:first-child {
        grid-column: 1 / -1;
      }

      .case-portfolio-layout {
        grid-template-columns: 1fr;
      }

      .case-meta-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .case-done-grid,
      .portfolio-realizations,
      .portfolio-kpi-strip {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .hero-core-title {
        font-size: clamp(32px, 5.2vw, 44px);
      }

      .hero-channel-head,
      .hero-channel-metric,
      .hero-kpi-label,
      .hero-pipeline-title,
      .hero-pipeline-row,
      .hero-chaos-note strong,
      .hero-chaos-note-grid span,
      .hero-analytics-title,
      .hero-analytics-cell .k,
      .hero-analytics-cell .d,
      .web-flow-label span,
      .web-side-kpi .k,
      .web-side-kpi .d,
      .case-kicker,
      .portfolio-card-title,
      .contact-panel-title,
      .contact-flow-step span {
        font-size: 11px;
      }

      .cta-band,
      .footer-inner {
        flex-direction: row;
      }

      .footer-links {
        align-items: flex-end;
      }
    }

    @media (min-width: 1280px) {
      .hero-grid,
      .split,
      .service-hero {
        grid-template-columns: minmax(0, 1fr) 420px;
      }

      .split {
        grid-template-columns: 320px minmax(0, 1fr);
      }

      .hero-system-top {
        grid-template-columns: 1fr 1.25fr;
      }

      .hero-kpi-stack {
        grid-template-columns: 1fr 1fr;
      }

      .hero-system-bottom {
        grid-template-columns: 1fr 1fr;
      }

      .hero-optimization-node {
        grid-column: 1 / -1;
        justify-self: center;
      }

      .service-meta-layout {
        grid-template-columns: .95fr 2fr 1fr;
      }

      .web-results-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }

      .why-trust-layout {
        grid-template-columns: 1fr 1.1fr 1fr;
      }

      .why-trust-layout > :nth-child(3) {
        grid-column: auto;
      }

      .why-one-results {
        grid-template-columns: 1fr 1fr;
      }

      .why-one-results .why-one-panel:last-child {
        grid-column: 1 / -1;
      }

      .process-track {
        grid-template-columns: 150px repeat(3, minmax(0, 1fr)) 150px;
      }

      .process-side {
        display: grid;
      }

      .process-impact-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }

      .contact-strategy-layout {
        grid-template-columns: 1.2fr .9fr;
      }

      .contact-strategy-flow {
        grid-template-columns: repeat(4, minmax(0, 1fr));
      }

      .contact-strategy-panels {
        grid-template-columns: 1.15fr 1fr 1fr;
      }

      .contact-strategy-panels .contact-panel:first-child {
        grid-column: auto;
      }

      .case-portfolio-layout {
        grid-template-columns: 1.05fr 1.25fr;
      }

      .case-meta-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }

      .case-done-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
      }

      .portfolio-realizations {
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }

      .portfolio-kpi-strip {
        grid-template-columns: repeat(4, minmax(0, 1fr));
      }
    }

    .home-semrush-flow .section {
      scroll-margin-top: 110px;
    }

    .home-structure-toggle {
      margin-top: 0;
      padding-top: 0;
      padding-bottom: 18px;
      border-bottom: 1px solid var(--border);
    }

    .home-structure-toggle-wrap {
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .home-structure-toggle-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      min-height: 48px;
      padding: 0 20px;
      border-radius: var(--r-pill);
      border: 1px solid var(--border-strong);
      background: #fff;
      color: var(--text);
      font-weight: 600;
      cursor: pointer;
      transition: transform 0.24s ease, box-shadow 0.24s ease, border-color 0.24s ease;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
    }

    .home-structure-toggle-btn:hover {
      transform: translateY(-2px);
      border-color: var(--teal);
      box-shadow: 0 14px 30px rgba(29, 158, 117, 0.16);
    }

    body.js-home-curated .home-semrush-flow .js-home-optional-section {
      display: none;
    }

    body.js-home-curated.home-all-sections-visible .home-semrush-flow .js-home-optional-section {
      display: block;
    }

    body.js-home-curated .home-semrush-flow .hero-trust-item:nth-child(n + 4),
    body.js-home-curated .home-semrush-flow .why-one-results .why-one-panel:nth-child(n + 3),
    body.js-home-curated .home-semrush-flow .service-what-item:nth-child(n + 4) {
      display: none;
    }

    body.js-home-curated.home-all-sections-visible .home-semrush-flow .hero-trust-item:nth-child(n + 4),
    body.js-home-curated.home-all-sections-visible .home-semrush-flow .why-one-results .why-one-panel:nth-child(n + 3),
    body.js-home-curated.home-all-sections-visible .home-semrush-flow .service-what-item:nth-child(n + 4) {
      display: block;
    }

    [data-interactive-card] {
      transform: perspective(900px) rotateX(var(--interactive-ry, 0deg)) rotateY(var(--interactive-rx, 0deg)) translateY(0);
      transform-style: preserve-3d;
      transition: transform 0.35s ease, box-shadow 0.35s ease;
      will-change: transform;
    }

    [data-interactive-card]:hover {
      box-shadow: 0 18px 34px rgba(17, 17, 16, 0.12);
    }

    /* Semrush-like composition without changing brand style */
    .home-semrush-flow .section {
      padding: clamp(56px, 7vw, 88px) 0;
    }

    .home-semrush-flow .section-sm {
      padding: clamp(34px, 4.8vw, 56px) 0;
    }

    .home-semrush-flow .hero {
      padding-top: clamp(30px, 5vw, 62px);
      padding-bottom: clamp(34px, 5vw, 62px);
    }

    .home-semrush-flow .hero-copy .h1 {
      max-width: 15ch;
      margin-bottom: clamp(12px, 1.8vw, 20px);
    }

    .home-semrush-flow .hero-copy .lead {
      max-width: 48ch;
      margin-bottom: clamp(12px, 2.2vw, 24px);
    }

    .home-semrush-flow .hero-actions {
      align-items: center;
      gap: 12px;
      margin-bottom: 10px;
    }

    .home-semrush-flow .hero-actions .btn {
      width: auto;
      min-height: 48px;
    }

    .home-semrush-flow .hero-actions .btn-primary {
      padding: 15px 30px;
      font-weight: 700;
      box-shadow: 0 10px 24px rgba(29, 158, 117, 0.24);
      animation-duration: 3.4s;
    }

    .home-semrush-flow .hero-actions .btn-secondary {
      padding: 14px 20px;
      border-color: color-mix(in srgb, var(--border-strong) 80%, var(--teal) 20%);
      color: var(--text-2);
      background: color-mix(in srgb, #fff 92%, var(--teal-soft) 8%);
    }

    .home-semrush-flow .hero-actions .btn-secondary:hover {
      color: var(--text);
      border-color: var(--teal);
      background: #fff;
    }

    .home-semrush-flow .hero-fast-lane {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 12px;
    }

    .home-semrush-flow .hero-fast-lane a {
      display: inline-flex;
      align-items: center;
      padding: 10px 14px;
      border-radius: 999px;
      border: 1px solid var(--border);
      background: #fff;
      color: var(--text-2);
      font-size: 13px;
      font-weight: 600;
    }

    .home-semrush-flow .hero-fast-lane a:hover {
      border-color: var(--teal);
      color: var(--text);
    }

    .home-semrush-flow .section-cta-row {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: clamp(16px, 2.5vw, 24px);
    }

    .home-semrush-flow .hero-aside {
      border-radius: 22px;
      padding: clamp(18px, 2.4vw, 28px);
    }

    @media (min-width: 981px) {
      .home-semrush-flow .hero-grid {
        grid-template-columns: minmax(0, 1.65fr) minmax(320px, 1fr);
        gap: clamp(24px, 3.2vw, 42px);
        align-items: start;
      }
    }

    @media (min-width: 1200px) {
      .home-semrush-flow .hero-grid {
        grid-template-columns: minmax(0, 1.7fr) minmax(340px, 1fr);
      }
    }

    @media (max-width: 760px) {
      .home-semrush-flow .btn,
      .home-semrush-flow .home-structure-toggle-btn,
      .home-semrush-flow button,
      .home-semrush-flow a[role="button"],
      .home-semrush-flow .nav a,
      .home-semrush-flow .mobile-menu a {
        min-height: 44px;
      }

      .home-semrush-flow .lead,
      .home-semrush-flow .body,
      .home-semrush-flow p,
      .home-semrush-flow li,
      .home-semrush-flow a,
      .home-semrush-flow span {
        font-size: max(16px, 1rem);
      }

      .home-semrush-flow .hero-system,
      .home-semrush-flow .service-meta-layout,
      .home-semrush-flow .case-portfolio-layout,
      .home-semrush-flow .contact-strategy-layout {
        overflow-x: clip;
      }

      .home-semrush-flow .hero-actions .btn {
        width: 100%;
      }

      .home-semrush-flow .hero {
        padding-top: 24px;
        padding-bottom: 26px;
      }

      .home-semrush-flow .hero-copy .h1 {
        max-width: 12ch;
      }

      .home-semrush-flow .hero-copy .lead {
        max-width: 34ch;
      }
    }
  </style><?php endif; ?>
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
  <?php wp_body_open(); ?>
  <header class="nav">
    <div class="wrap nav-inner">
      <a href="<?php echo esc_url(home_url("/#start")); ?>" class="brand" aria-label="Upsellio — strona główna">
        <img src="<?php echo esc_url($brand_logo_url); ?>" alt="Upsellio" class="brand-logo" decoding="async" />
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
      </ul>

      <div class="nav-actions">
        <a href="<?php echo esc_url(home_url("/#kontakt")); ?>" class="nav-cta">Bezpłatna rozmowa</a>
      </div>

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
        <a href="<?php echo esc_url(home_url("/#kontakt")); ?>">Bezpłatna rozmowa →</a>
        <a href="<?php echo esc_url(home_url("/#kontakt")); ?>">Przejdz od razu do formularza ↓</a>
      </div>
    </div>
  </header>

  <main class="home-semrush-flow" data-home-curated="1">
    <section class="hero" id="start">
      <div class="wrap hero-wrap">
        <div class="hero-copy">
          <div class="hero-pill reveal in d1">
            <div class="hero-pill-dot">●</div>
            <span>Marketing internetowy B2B, który zamienia ruch w klientów</span>
          </div>
          <h1 class="h1 hero-h1 reveal in d1">
            Masz ruch, nie masz klientów?
          </h1>
          <p class="lead hero-lead reveal in d2">
            Kampanie Meta Ads, Google Ads i strony internetowe powinny sprzedawać. Jeśli generują kliknięcia, ale nie przynoszą zapytań - tracisz pieniądze każdego dnia.
          </p>
          <p class="body reveal in d2" style="margin-top:10px;max-width:58ch">
            Buduję system marketingowy dla firm B2B, który łączy ruch, konwersję i sprzedaż oraz zamienia odwiedzających w realnych klientów.
          </p>
          <div class="hero-actions reveal in d3">
            <a href="<?php echo esc_url(home_url("/#kontakt")); ?>" class="btn btn-primary">Sprawdzę Twoją stronę</a>
            <a href="<?php echo esc_url(home_url("/#system")); ?>" class="btn btn-secondary">Zobacz jak działa system</a>
          </div>
          <p class="hero-micro reveal in d3">Bezpłatna analiza • konkretne wnioski • bez zobowiązań</p>
          <div class="hero-fast-lane reveal in d3">
            <a href="<?php echo esc_url(home_url("/#kontakt")); ?>">Szybki kontakt</a>
            <a href="<?php echo esc_url(home_url("/#case-study")); ?>">Zobacz wyniki</a>
            <a href="<?php echo esc_url(home_url("/#faq")); ?>">Najczęstsze pytania</a>
          </div>
        </div>

        <aside class="hero-aside reveal in d2">
          <div class="aside-label">Co ma znaczenie na stronie</div>
          <div class="aside-stats">
            <div class="stat-block">
              <div class="stat-num teal">Jasny przekaz</div>
              <div class="stat-lbl">Odwiedzający w kilka sekund wie, czym się zajmujesz</div>
            </div>
            <div class="stat-block">
              <div class="stat-num teal">Mocne CTA</div>
              <div class="stat-lbl">Wiadomo, jaki jest kolejny krok po wejściu</div>
            </div>
            <div class="stat-block">
              <div class="stat-num">Korzyści</div>
              <div class="stat-lbl">Oferta mówi językiem korzyści, nie narzędzi</div>
            </div>
            <div class="stat-block">
              <div class="stat-num">Zaufanie</div>
              <div class="stat-lbl">Liczby, opinie i proces obniżają opór</div>
            </div>
          </div>
          <div class="pipeline">
            <div class="pipeline-title">Checklista konwersji</div>
            <div class="pipeline-row"><span>Jasny przekaz</span><b>Tak</b></div>
            <div class="pipeline-row"><span>Korzyści dla odbiorcy</span><b>Tak</b></div>
            <div class="pipeline-row"><span>Widoczne CTA</span><b>Tak</b></div>
            <div class="pipeline-row"><span>Dowody zaufania</span><b>Tak</b></div>
          </div>
        </aside>
      </div>
    </section>

    <section class="section section-border bg-soft" id="problem">
      <div class="wrap">
        <div style="max-width:720px">
          <div class="eyebrow reveal">Problem</div>
          <h2 class="h2 reveal d1">Dlaczego marketing nie przynosi klientów?</h2>
          <p class="body reveal d2" style="margin-top:18px">Większość firm traci budżet nie przez brak reklam, ale przez brak systemu, który zamienia ruch w klientów.</p>
        </div>
        <div class="problem-grid" style="margin-top:var(--sp-4)">
          <div class="problem-card reveal">Masz ruch, ale brak zapytań</div>
          <div class="problem-card reveal d1">Reklamy generują kliknięcia bez efektu</div>
          <div class="problem-card reveal d2">Strona nie prowadzi do decyzji</div>
          <div class="problem-card reveal d3">Brak spójnej strategii marketingowej</div>
        </div>
        <div class="section-cta-row reveal d3">
          <a href="<?php echo esc_url(home_url("/#kontakt")); ?>" class="btn btn-primary btn-sm">Sprawdźmy, co blokuje zapytania →</a>
          <a href="<?php echo esc_url(home_url("/#kontakt")); ?>" class="btn btn-secondary btn-sm">Przejdź do formularza</a>
        </div>
      </div>
    </section>

    <section class="section section-border" id="case-study">
      <div class="wrap">
        <div style="max-width:780px">
          <div class="eyebrow reveal">Efekty współpracy</div>
          <h2 class="h2 reveal d1">Liczby zamiast słów</h2>
        </div>
        <div class="case-grid">
          <div class="reveal">
            <div class="case-tag">Przed współpracą</div>
            <div class="case-title">Ruch był, ale nie było wyników sprzedażowych</div>
            <table class="results-table">
              <thead><tr><th>Metryka</th><th>Wynik</th></tr></thead>
              <tbody>
                <tr><td>Wejścia miesięcznie</td><td>18 500</td></tr>
                <tr><td>Konwersja strony</td><td>0,6%</td></tr>
                <tr><td>Koszt pozyskania klienta</td><td>wysoki</td></tr>
              </tbody>
            </table>
          </div>
          <div class="reveal d1">
            <div class="chart-panel">
              <div class="cp-head"><span>Po wdrożeniu systemu</span><span class="live-dot"></span></div>
              <div class="cp-kpis">
                <div class="cp-kpi"><div class="cp-kpi-num">128 000</div><div class="cp-kpi-lbl">wejść miesięcznie</div></div>
                <div class="cp-kpi"><div class="cp-kpi-num">2,3%</div><div class="cp-kpi-lbl">konwersja</div></div>
                <div class="cp-kpi"><div class="cp-kpi-num">stabilnie</div><div class="cp-kpi-lbl">napływ zapytań</div></div>
                <div class="cp-kpi"><div class="cp-kpi-num">niżej</div><div class="cp-kpi-lbl">koszt pozyskania</div></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section section-border bg-soft" id="system">
      <div class="wrap">
        <div style="max-width:820px">
          <div class="eyebrow reveal">System</div>
          <h2 class="h2 reveal d1">Jak działa skuteczny marketing B2B?</h2>
          <p class="body reveal d2" style="margin-top:18px">Skuteczny marketing internetowy to nie pojedyncze działania. To system, który łączy ruch, konwersję i sprzedaż.</p>
        </div>
        <div class="service-grid" style="margin-top:var(--sp-5)">
          <article class="service-card reveal">
            <h3 class="h3" style="margin-bottom:10px">Meta Ads</h3>
            <p class="body">Pozyskiwanie nowych klientów i budowanie zainteresowania ofertą.</p>
          </article>
          <article class="service-card reveal d1">
            <h3 class="h3" style="margin-bottom:10px">Google Ads</h3>
            <p class="body">Docieranie do klientów, którzy aktywnie szukają Twojej usługi lub produktu.</p>
          </article>
          <article class="service-card reveal d2">
            <h3 class="h3" style="margin-bottom:10px">Strony internetowe</h3>
            <p class="body">Optymalizacja konwersji - zamiana ruchu w zapytania i sprzedaż.</p>
          </article>
        </div>
        <p class="body reveal d3" style="margin-top:22px;font-weight:700;color:var(--teal-dark)">Ruch → Strona → Klient</p>
      </div>
    </section>

    <section class="section section-border" id="uslugi">
      <div class="wrap">
        <div style="max-width:720px">
          <div class="eyebrow reveal">Oferta</div>
          <h2 class="h2 reveal d1">Co mogę dla Ciebie zrobić?</h2>
          <p class="body reveal d2" style="margin-top:18px">Pomagam firmom B2B zwiększać sprzedaż poprzez:</p>
          <ul class="fit-items reveal d2" style="margin-top:18px;max-width:680px">
            <li class="fit-item"><span class="fit-icon">✓</span><span>kampanie Meta Ads dla firm</span></li>
            <li class="fit-item"><span class="fit-icon">✓</span><span>kampanie Google Ads dla firm</span></li>
            <li class="fit-item"><span class="fit-icon">✓</span><span>strony internetowe dla firm i sklepy online</span></li>
            <li class="fit-item"><span class="fit-icon">✓</span><span>optymalizację konwersji i procesu sprzedaży online</span></li>
          </ul>
          <p class="body reveal d3" style="margin-top:18px;max-width:680px">Każdy element działa jako część jednego systemu marketingowego.</p>
        </div>
        <div class="section-cta-row reveal d2">
          <a href="<?php echo esc_url(home_url("/#kontakt")); ?>" class="btn btn-primary btn-sm">Napisz i sprawdź swoją stronę</a>
          <a href="<?php echo esc_url(home_url("/#kontakt")); ?>" class="btn btn-secondary btn-sm">Szybki formularz kontaktowy</a>
        </div>
      </div>
    </section>

    <section class="section section-border bg-soft" id="obszary-wsparcia">
      <div class="wrap">
        <div style="max-width:760px">
          <div class="eyebrow reveal">Ekspert</div>
          <h2 class="h2 reveal d1">Kim jestem?</h2>
          <p class="body reveal d2" style="margin-top:18px">Nazywam się Sebastian Kelm. Od ponad 10 lat pracuję w sprzedaży B2B i marketingu.</p>
          <p class="body reveal d2" style="margin-top:14px">Nie projektuję kampanii „ładnych”. Projektuję kampanie, które sprzedają.</p>
          <p class="body reveal d3" style="margin-top:14px">Znam proces od pierwszego kliknięcia do finalnej sprzedaży.</p>
        </div>
        <div class="section-cta-row reveal d3">
          <a href="<?php echo esc_url(home_url("/#kontakt")); ?>" class="btn btn-primary btn-sm">Porozmawiajmy o Twoich wynikach</a>
        </div>
      </div>
    </section>

    <section class="section-sm section-border">
      <div class="wrap">
        <div class="cta-band reveal">
          <div>
            <h3>Sprawdzę Twój marketing</h3>
            <p>Jeśli masz ruch, ale nie masz klientów - pokażę Ci, gdzie jest problem.</p>
          </div>
          <a href="<?php echo esc_url(home_url("/#kontakt")); ?>" class="btn btn-primary">Napisz i sprawdź swoją stronę</a>
        </div>
        <p class="hero-micro reveal d2" style="margin-top:12px">Odpowiadam konkretnie • bez sprzedażowego gadania</p>
      </div>
    </section>

    <section class="section section-border bg-soft" id="jak-dzialam">
      <div class="wrap">
        <div style="max-width:720px">
          <div class="eyebrow reveal">Jak dzialam</div>
          <h2 class="h2 reveal d1">Nie zaczynam od zmian na slepo</h2>
          <p class="body reveal d2" style="margin-top:18px">Najpierw sprawdzam, czy problem lezy w przekazie, ofercie, CTA, zaufaniu, stronie czy samym ruchu.</p>
        </div>
        <div class="steps reveal d1" style="margin-top:var(--sp-5)">
          <div class="step"><div class="step-num">01</div><div><div class="step-title">Analiza i diagnoza</div><div class="step-desc">Analizuje, czy odwiedzajacy od razu rozumie czym sie zajmujesz, czy widzi korzysci i czy ma powod, zeby zostawic kontakt.</div></div></div>
          <div class="step"><div class="step-num">02</div><div><div class="step-title">Rekomendacja i plan dzialan</div><div class="step-desc">Ukladam prosty plan: co uproscic, co doprecyzowac, co pokazac mocniej i gdzie ustawic glowny cel strony.</div></div></div>
          <div class="step"><div class="step-num">03</div><div><div class="step-title">Wdrozenie i optymalizacja</div><div class="step-desc">Wdrazam zmiany w reklamach, tresciach i stronie tak, zeby wszystko pracowalo na jeden wynik: wiecej wartosciowych zapytan.</div></div></div>
        </div>
        <div class="section-cta-row reveal d2">
          <a href="<?php echo esc_url(home_url("/#kontakt")); ?>" class="btn btn-primary btn-sm">Chce przejsc przez ten proces →</a>
        </div>
      </div>
    </section>

    <section class="section section-border" id="wyniki">
      <div class="wrap">
        <div style="max-width:720px">
          <div class="eyebrow reveal">Wyniki kampanii</div>
          <h2 class="h2 reveal d1">Wyniki, które rozumiesz i widzisz w liczbach</h2>
          <p class="body reveal d2" style="margin-top:18px">Na końcu liczy się to, czy Twoja reklama i strona przynoszą więcej dobrych rozmów, niższy CPL i realny wzrost sprzedaży.</p>
        </div>
        <div class="metrics-grid">
          <div class="metric-card reveal"><div class="mc-label">Leady / miesiac</div><div class="mc-num teal">362</div><span class="mc-change up">+28% vs poprzedni miesiac</span><div class="mc-sub">wartosciowe kontakty sprzedazowe</div></div>
          <div class="metric-card reveal d1"><div class="mc-label">Koszt pozyskania leada (CPL)</div><div class="mc-num red">37 zl</div><span class="mc-change dn">-18% vs poprzedni miesiac</span><div class="mc-sub">przy tym samym budzecie</div></div>
          <div class="metric-card dark reveal d2"><div class="mc-label">Lejek sprzedazowy</div><div class="funnel"><div class="funnel-row"><div class="f-lbl">Ruch</div><div class="f-track"><div class="f-fill" style="transform:scaleX(1)"></div></div><div class="f-val">23 810</div></div><div class="funnel-row"><div class="f-lbl">Leady</div><div class="f-track"><div class="f-fill" style="transform:scaleX(.62)"></div></div><div class="f-val">362</div></div><div class="funnel-row"><div class="f-lbl">Kwalifikacja</div><div class="f-track"><div class="f-fill" style="transform:scaleX(.4)"></div></div><div class="f-val">148</div></div><div class="funnel-row"><div class="f-lbl">Klienci</div><div class="f-track"><div class="f-fill" style="transform:scaleX(.17)"></div></div><div class="f-val">64</div></div></div></div>
        </div>
        <div class="section-cta-row reveal d2">
          <a href="<?php echo esc_url(home_url("/#kontakt")); ?>" class="btn btn-primary btn-sm">Sprawdźmy Twoje liczby i konwersję →</a>
        </div>
      </div>
    </section>

    <section class="section section-border" id="dla-kogo">
      <div class="wrap">
        <div style="max-width:720px">
          <div class="eyebrow reveal">Dla kogo</div>
          <h2 class="h2 reveal d1">Z kim pracuje najlepiej</h2>
        </div>
        <div class="fit-grid">
          <div class="fit-card yes reveal">
            <div class="fit-label">Dobry fit, jesli:</div>
            <div class="fit-items">
              <div class="fit-item"><span class="fit-icon">✅</span><span>Prowadzisz firme i chcesz laczyc marketing oraz strone WWW tak, by wspolnie dowozily wiecej zapytan</span></div>
              <div class="fit-item"><span class="fit-icon">✅</span><span>Chcesz jasnej oferty, wyraznych CTA i strony, ktora nie rozprasza</span></div>
              <div class="fit-item"><span class="fit-icon">✅</span><span>Szukasz partnera, ktory patrzy na marketing i sprzedaz razem</span></div>
              <div class="fit-item"><span class="fit-icon">✅</span><span>Masz ruch lub kampanie, ale czujesz, ze strona moglaby zamieniac wiecej odwiedzajacych w kontakty</span></div>
            </div>
          </div>
          <div class="fit-card no reveal d1">
            <div class="fit-label">Mniejszy fit, jesli:</div>
            <div class="fit-items">
              <div class="fit-item"><span class="fit-icon">—</span><span>Szukasz tylko najtanszego wykonania bez myslenia o wyniku</span></div>
              <div class="fit-item"><span class="fit-icon">—</span><span>Oczekujesz rozbudowanej agencji z duzym zespolem od wszystkiego</span></div>
              <div class="fit-item"><span class="fit-icon">—</span><span>Nie chcesz rozmawiac o ofercie, kliencie i procesie decyzji</span></div>
              <div class="fit-item"><span class="fit-icon">—</span><span>Zalezy Ci tylko na ruchu, a nie na jakosci leadow i sprzedazy</span></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section section-border bg-soft" id="faq">
      <div class="wrap">
        <div style="max-width:720px">
          <div class="eyebrow reveal">FAQ</div>
          <h2 class="h2 reveal d1">Najczestsze pytania</h2>
        </div>
        <div class="faq reveal d1">
          <div class="faq-item"><button class="faq-q" type="button"><span>Co konkretnie zmienia sie na stronie po takiej wspolpracy?</span><span class="faq-icon">+</span></button><div class="faq-a">Najczesciej porzadkuje przekaz, doprecyzowuje oferte, wzmacniam CTA, dodaje potrzebne elementy zaufania i upraszczam droge do kontaktu.</div></div>
          <div class="faq-item"><button class="faq-q" type="button"><span>Jak wyglada bezplatna konsultacja i co z niej wynika?</span><span class="faq-icon">+</span></button><div class="faq-a">Rozmawiamy o ofercie, stronie, reklamach i tym, gdzie uciekaja zapytania. Po rozmowie wiesz, co poprawic najpierw.</div></div>
          <div class="faq-item"><button class="faq-q" type="button"><span>Czy sama reklama wystarczy, zeby poprawic wyniki?</span><span class="faq-icon">+</span></button><div class="faq-a">Nie zawsze. Jesli strona ma slaby przekaz albo nie buduje zaufania, to nawet dobry ruch bedzie przeciekal.</div></div>
          <div class="faq-item"><button class="faq-q" type="button"><span>Czy obsługujesz tylko reklamy, czy też tworzenie stron internetowych?</span><span class="faq-icon">+</span></button><div class="faq-a">Obsługuję oba obszary: kampanie Google Ads i Meta Ads oraz strony WWW i landing page. Najlepsze wyniki daje połączenie marketingu i strony w jednym procesie.</div></div>
          <div class="faq-item"><button class="faq-q" type="button"><span>Dla jakich firm jest ta współpraca: usługi lokalne, e-commerce czy B2B?</span><span class="faq-icon">+</span></button><div class="faq-a">Pracuję z różnymi modelami: firmy usługowe, e-commerce i firmy B2B. Kluczowe jest to, żeby oferta była klarowna, a marketing i strona dowoziły wartościowe zapytania.</div></div>
          <div class="faq-item"><button class="faq-q" type="button"><span>Po jakim czasie widać efekty po zmianach na stronie i w kampaniach?</span><span class="faq-icon">+</span></button><div class="faq-a">Pierwsze sygnały poprawy zwykle widać po kilku tygodniach, ale stabilny efekt wymaga systematycznej optymalizacji. Działamy iteracyjnie: analiza, wdrożenie, pomiar i kolejne ulepszenia.</div></div>
        </div>
        <div class="section-cta-row reveal d2">
          <a href="<?php echo esc_url(home_url("/#kontakt")); ?>" class="btn btn-primary btn-sm">Mam podobne pytania — chce konsultacje →</a>
          <a href="<?php echo esc_url(home_url("/#kontakt")); ?>" class="btn btn-secondary btn-sm">Przejdz do kontaktu</a>
        </div>
      </div>
    </section>

    <section class="cta-dark">
      <div class="wrap">
        <div class="cta-dark-inner">
          <div>
            <h2 class="reveal">Twoja strona ma wygladac dobrze i sprzedawac</h2>
            <p class="reveal d1">Jesli chcesz, moge spojrzec na Twoja strone i marketing pod katem: jasnosci przekazu, CTA, zaufania i konwersji. Powiem Ci wprost, co warto poprawic najpierw.</p>
          </div>
          <div class="reveal d2" style="text-align:center">
            <a href="<?php echo esc_url(home_url("/#kontakt")); ?>" class="btn-white">
              Umow bezplatna konsultacje — 30 min
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
          </div>
        </div>
      </div>
    </section>

    <section class="section" id="kontakt">
      <div class="wrap">
        <?php $ups_form_status = isset($_GET["ups_lead_status"]) ? sanitize_text_field(wp_unslash($_GET["ups_lead_status"])) : ""; ?>
        <div style="max-width:720px;margin:0 auto 28px;">
          <div class="eyebrow reveal">Kontakt</div>
          <h2 class="h2 reveal d1">Umow bezplatna konsultacje</h2>
          <p class="body reveal d2" style="margin-top:10px;">Wypelnij formularz. Odpowiem osobiscie i podpowiem, od czego najlepiej zaczac.</p>
        </div>
        <div class="contact-strategy-form" style="max-width:860px;margin:0 auto;">
          <?php if ($ups_form_status === "success") : ?>
            <div style="margin-bottom:12px;padding:10px 12px;border:1px solid #c3eddd;background:#e8f8f2;border-radius:10px;color:#085041;font-size:13px;">Dziekuje! Wiadomosc zostala zapisana i odezwe sie mozliwie szybko.</div>
          <?php elseif ($ups_form_status === "error") : ?>
            <div style="margin-bottom:12px;padding:10px 12px;border:1px solid #edcccc;background:#fff2f2;border-radius:10px;color:#b13a3a;font-size:13px;">Nie udalo sie wyslac formularza. Sprawdz pola i sproboj ponownie.</div>
          <?php endif; ?>
          <form id="contact-form" method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" novalidate data-upsellio-lead-form="1" data-upsellio-server-form="1">
            <input type="hidden" name="action" value="upsellio_submit_lead" />
            <input type="hidden" name="redirect_url" value="<?php echo esc_url(home_url("/#kontakt")); ?>" />
            <input type="hidden" name="lead_form_origin" value="contact-form" />
            <input type="hidden" name="lead_source" value="contact-form" />
            <input type="hidden" name="utm_source" data-ups-utm="source" value="" />
            <input type="hidden" name="utm_medium" data-ups-utm="medium" value="" />
            <input type="hidden" name="utm_campaign" data-ups-utm="campaign" value="" />
            <input type="hidden" name="landing_url" data-ups-context="landing" value="" />
            <input type="hidden" name="referrer" data-ups-context="referrer" value="" />
            <input type="text" name="lead_website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;" />
            <?php wp_nonce_field("upsellio_unified_lead_form", "upsellio_lead_form_nonce"); ?>
            <div class="form-grid">
              <div class="field"><label for="fname">Imie i nazwisko *</label><input class="input" type="text" id="fname" name="lead_name" placeholder="Twoje imie i nazwisko" required /></div>
              <div class="field"><label for="femail">E-mail *</label><input class="input" type="email" id="femail" name="lead_email" placeholder="Twoj adres e-mail" required /></div>
              <div class="field"><label for="fcompany">Nazwa firmy</label><input class="input" type="text" id="fcompany" name="lead_company" placeholder="Nazwa Twojej firmy" /></div>
              <div class="field"><label for="fservice">Czego dotyczy rozmowa?</label><select class="select" id="fservice" name="lead_service"><option value="">Wybierz obszar</option><?php foreach ($contact_service_options as $service_option) : ?><?php $service_option = trim((string) $service_option); ?><?php if ($service_option === "") : ?><?php continue; ?><?php endif; ?><option><?php echo esc_html($service_option); ?></option><?php endforeach; ?></select></div>
              <div class="field full"><label for="fmsg">Krotko opisz swoj cel lub wyzwanie *</label><textarea class="textarea" id="fmsg" name="lead_message" placeholder="Napisz kilka slow o swoim biznesie i oczekiwaniach..." required></textarea></div>
              <div class="field full"><label style="display:flex;gap:8px;align-items:flex-start;"><input type="checkbox" name="lead_consent" value="1" required style="margin-top:3px;" /><span>Wyrazam zgode na kontakt w sprawie mojego zapytania.</span></label></div>
            </div>
            <button type="submit" class="btn btn-primary submit" id="submit-btn">Umow bezplatna konsultacje</button>
            <p class="form-note">Bez spamu. Odpowiadam osobiscie. Wolisz zadzwonic? <a href="<?php echo esc_url("tel:" . preg_replace("/\s+/", "", $contact_phone)); ?>"><?php echo esc_html($contact_phone); ?></a></p>
          </form>
        </div>
      </div>
    </section>
  </main>

  <script>
    var upsellioReducedMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    var upsellioIntervalRegistry = [];

    function upsellioStartInterval(callback, delay) {
      if (typeof callback !== "function") return null;
      var state = { callback: callback, delay: delay, timer: null };
      function run() {
        if (document.hidden) return;
        callback();
      }
      state.timer = setInterval(run, delay);
      upsellioIntervalRegistry.push(state);
      return state;
    }

    document.addEventListener("visibilitychange", function () {
      if (!upsellioIntervalRegistry.length) return;
      if (document.hidden) {
        upsellioIntervalRegistry.forEach(function (item) {
          if (!item || item.timer === null) return;
          clearInterval(item.timer);
          item.timer = null;
        });
        return;
      }
      upsellioIntervalRegistry.forEach(function (item) {
        if (!item || item.timer !== null) return;
        item.timer = setInterval(function () {
          if (document.hidden) return;
          item.callback();
        }, item.delay);
      });
    });

    (function () {
      var curatedRoot = document.querySelector("[data-home-curated='1']");
      if (!curatedRoot) return;
      document.body.classList.add("js-home-curated");

      var optionalSections = Array.prototype.slice.call(curatedRoot.querySelectorAll(".js-home-optional-section"));
      var toggleButton = document.getElementById("home-structure-toggle-btn");
      var isExpanded = false;

      function setExpanded(nextState, withScroll) {
        isExpanded = !!nextState;
        document.body.classList.toggle("home-all-sections-visible", isExpanded);
        if (toggleButton) {
          toggleButton.setAttribute("aria-expanded", isExpanded ? "true" : "false");
          toggleButton.textContent = isExpanded ? "Pokaz mniej sekcji" : "Pokaz pelny widok strony";
        }
        if (withScroll && toggleButton) {
          var offset = Math.max(0, toggleButton.getBoundingClientRect().top + window.scrollY - 120);
          window.scrollTo({ top: offset, behavior: upsellioReducedMotion ? "auto" : "smooth" });
        }
      }

      if (toggleButton) {
        toggleButton.addEventListener("click", function () {
          setExpanded(!isExpanded, true);
        });
      }

      function ensureSectionVisibleByHash(hash) {
        if (!hash || hash.length < 2) return;
        var target = document.getElementById(hash.replace("#", ""));
        if (!target) return;
        var isOptional = optionalSections.indexOf(target) > -1;
        if (isOptional && !isExpanded) setExpanded(true, false);
      }

      ensureSectionVisibleByHash(window.location.hash);

      document.addEventListener("click", function (event) {
        var anchor = event.target.closest('a[href^="#"]');
        if (!anchor) return;
        ensureSectionVisibleByHash(anchor.getAttribute("href") || "");
      });

      if (!upsellioReducedMotion && !window.matchMedia("(max-width: 980px)").matches) {
        var interactiveCards = Array.prototype.slice.call(document.querySelectorAll(
          ".hero-system-core, .hero-kpi-block, .why-one-panel, .service-meta-panel, .case-panel"
        ));
        interactiveCards.forEach(function (card) {
          card.setAttribute("data-interactive-card", "1");
          card.addEventListener("mousemove", function (event) {
            var rect = card.getBoundingClientRect();
            var cx = rect.left + (rect.width / 2);
            var cy = rect.top + (rect.height / 2);
            var dx = (event.clientX - cx) / rect.width;
            var dy = (event.clientY - cy) / rect.height;
            card.style.setProperty("--interactive-rx", (dx * 4).toFixed(2) + "deg");
            card.style.setProperty("--interactive-ry", (-dy * 4).toFixed(2) + "deg");
          });
          card.addEventListener("mouseleave", function () {
            card.style.setProperty("--interactive-rx", "0deg");
            card.style.setProperty("--interactive-ry", "0deg");
          });
        });
      }
    })();

    (function () {
      var heroSystem = document.getElementById("hero-system");
      if (!heroSystem) return;

      var sparkGroups = Array.prototype.slice.call(heroSystem.querySelectorAll("[data-hero-spark]"));
      var progressBars = Array.prototype.slice.call(heroSystem.querySelectorAll("[data-hero-kpi-progress]"));
      var pipeSteps = Array.prototype.slice.call(heroSystem.querySelectorAll(".hero-pipe-step"));
      var growthGroups = Array.prototype.slice.call(heroSystem.querySelectorAll("[data-hero-growth-line]"));
      var chaosNotes = Array.prototype.slice.call(heroSystem.querySelectorAll(".hero-chaos-note-grid span"));
      var pipeIndex = 0;

      function randomizeSparks() {
        sparkGroups.forEach(function (group) {
          Array.prototype.slice.call(group.children).forEach(function (bar) {
            var next = 16 + Math.floor(Math.random() * 70);
            bar.style.height = next + "%";
          });
        });
      }

      function pulseProgress() {
        progressBars.forEach(function (bar) {
          var current = parseInt(bar.style.width || "50", 10);
          if (!isFinite(current)) current = 50;
          var next = Math.max(38, Math.min(92, current + (Math.random() > 0.5 ? 6 : -6)));
          bar.style.width = next + "%";
        });
      }

      function rotatePipeline() {
        if (!pipeSteps.length) return;
        pipeSteps.forEach(function (step) { step.classList.remove("is-active"); });
        pipeSteps[pipeIndex % pipeSteps.length].classList.add("is-active");
        pipeIndex += 1;
      }

      function pulseGrowth() {
        growthGroups.forEach(function (group) {
          Array.prototype.slice.call(group.children).forEach(function (bar, idx) {
            var base = 20 + (idx * 8);
            var jitter = Math.floor(Math.random() * 15) - 7;
            var next = Math.max(14, Math.min(92, base + jitter));
            bar.style.height = next + "%";
          });
        });
      }

      function pulseChaos() {
        chaosNotes.forEach(function (note) {
          var shift = (Math.random() * 2.4) - 1.2;
          var opacity = 0.78 + (Math.random() * 0.22);
          note.style.transform = "translateY(" + shift.toFixed(2) + "px)";
          note.style.opacity = opacity.toFixed(2);
        });
      }

      randomizeSparks();
      pulseProgress();
      rotatePipeline();
      pulseGrowth();
      pulseChaos();

      if (upsellioReducedMotion || window.matchMedia("(max-width: 760px)").matches) return;

      upsellioStartInterval(randomizeSparks, 3600);
      upsellioStartInterval(pulseProgress, 4200);
      upsellioStartInterval(rotatePipeline, 3400);
      upsellioStartInterval(pulseGrowth, 3900);
      upsellioStartInterval(pulseChaos, 4300);
    })();

    (function () {
      var webVisual = document.getElementById("service-meta-visual");
      if (!webVisual) return;

      var flowItems = Array.prototype.slice.call(webVisual.querySelectorAll(".web-flow-item"));
      var uxItems = Array.prototype.slice.call(webVisual.querySelectorAll(".web-ux-item"));
      var lineGroups = Array.prototype.slice.call(webVisual.querySelectorAll("[data-web-line]"));
      var funnelBars = Array.prototype.slice.call(webVisual.querySelectorAll("[data-web-funnel-bar]"));
      var resultValues = Array.prototype.slice.call(webVisual.querySelectorAll("[data-web-result-value]"));
      var leadsValue = webVisual.querySelector("[data-web-leads]");
      var cplValue = webVisual.querySelector("[data-web-cpl]");
      var convValue = webVisual.querySelector("[data-web-conv]");
      var roasValue = webVisual.querySelector("[data-web-roas]");
      var flowIndex = 0;
      var uxIndex = 0;
      var baseLeads = 362;
      var baseCpl = 37.21;
      var baseConv = 6.42;
      var baseRoas = 4.87;

      function animateLines(min, max, drift) {
        lineGroups.forEach(function (group) {
          Array.prototype.slice.call(group.children).forEach(function (bar, idx) {
            var base = min + (idx * drift);
            var jitter = Math.floor(Math.random() * 12) - 6;
            var next = Math.max(min - 4, Math.min(max, base + jitter));
            bar.style.height = next + "%";
          });
        });
      }

      function rotateFlow() {
        if (!flowItems.length) return;
        flowItems.forEach(function (item) { item.classList.remove("is-active"); });
        flowItems[flowIndex % flowItems.length].classList.add("is-active");
        flowIndex += 1;
      }

      function rotateUx() {
        if (!uxItems.length) return;
        uxItems.forEach(function (item) { item.classList.remove("is-active"); });
        uxItems[uxIndex % uxItems.length].classList.add("is-active");
        uxIndex += 1;
      }

      function pulseFunnel() {
        funnelBars.forEach(function (bar, idx) {
          var base = 86 - (idx * 20);
          var jitter = Math.floor(Math.random() * 8) - 4;
          var next = Math.max(12, Math.min(92, base + jitter));
          bar.style.width = next + "%";
        });
      }

      function pulseKpis() {
        var leads = baseLeads + Math.floor(Math.random() * 21) - 9;
        var cpl = baseCpl + ((Math.random() * 1.2) - 0.6);
        var conv = baseConv + ((Math.random() * 0.22) - 0.11);
        var roas = baseRoas + ((Math.random() * 0.34) - 0.17);

        if (leadsValue) leadsValue.textContent = String(Math.max(330, leads));
        if (cplValue) cplValue.textContent = cpl.toFixed(2).replace(".", ",");
        if (convValue) convValue.textContent = conv.toFixed(2).replace(".", ",") + "%";
        if (roasValue) roasValue.textContent = roas.toFixed(2).replace(".", ",");

        if (resultValues.length === 3) {
          resultValues[0].textContent = "+" + String(Math.max(142, 168 + Math.floor(Math.random() * 16) - 7)) + "%";
          resultValues[1].textContent = "-" + String(Math.max(31, 42 + Math.floor(Math.random() * 9) - 4)) + "%";
          resultValues[2].textContent = "+" + String(Math.max(61, 73 + Math.floor(Math.random() * 11) - 5)) + "%";
        }
      }

      rotateFlow();
      rotateUx();
      pulseFunnel();
      pulseKpis();
      animateLines(16, 72, 8);

      if (upsellioReducedMotion || window.matchMedia("(max-width: 760px)").matches) return;

      upsellioStartInterval(rotateFlow, 3200);
      upsellioStartInterval(rotateUx, 3800);
      upsellioStartInterval(pulseFunnel, 3600);
      upsellioStartInterval(pulseKpis, 4300);
      upsellioStartInterval(function () { animateLines(16, 72, 8); }, 4000);
    })();

    (function () {
      var snapshot = document.querySelector("[data-service-case-snapshot]");
      if (!snapshot) return;

      var bars = Array.prototype.slice.call(snapshot.querySelectorAll("[data-service-case-bar]"));
      var values = Array.prototype.slice.call(snapshot.querySelectorAll("[data-service-case-value]"));

      function pulseSnapshot() {
        bars.forEach(function (bar, idx) {
          var base = 62 + (idx * 5);
          var jitter = Math.floor(Math.random() * 12) - 6;
          var next = Math.max(38, Math.min(92, base + jitter));
          bar.style.width = next + "%";
        });

        if (values.length >= 4) {
          values[0].textContent = String(152 + (Math.floor(Math.random() * 11) - 5));
          values[1].textContent = (4.8 + ((Math.random() * 0.4) - 0.2)).toFixed(1).replace(".", ",") + "%";
          values[2].textContent = String(49 + (Math.floor(Math.random() * 7) - 3)) + " zl";
          values[3].textContent = (5.2 + ((Math.random() * 0.4) - 0.2)).toFixed(1).replace(".", ",");
        }
      }

      pulseSnapshot();
      if (upsellioReducedMotion || window.matchMedia("(max-width: 760px)").matches) return;
      upsellioStartInterval(pulseSnapshot, 4200);
    })();

    (function () {
      var whyVisual = document.getElementById("why-trust-visual");
      if (!whyVisual) return;

      var processSteps = Array.prototype.slice.call(whyVisual.querySelectorAll(".why-one-step"));
      var guaranteeItems = Array.prototype.slice.call(whyVisual.querySelectorAll(".why-one-guarantee"));
      var principleItems = Array.prototype.slice.call(whyVisual.querySelectorAll(".why-one-principle"));
      var lineGroups = Array.prototype.slice.call(whyVisual.querySelectorAll("[data-why-one-line]"));
      var revenueGroups = Array.prototype.slice.call(whyVisual.querySelectorAll("[data-why-one-revenue]"));
      var funnelBars = Array.prototype.slice.call(whyVisual.querySelectorAll("[data-why-one-funnel-bar]"));
      var growthValue = whyVisual.querySelector("[data-why-one-growth]");
      var leadsValue = whyVisual.querySelector("[data-why-one-leads]");
      var cplValue = whyVisual.querySelector("[data-why-one-cpl]");
      var convValue = whyVisual.querySelector("[data-why-one-conv]");
      var roasValue = whyVisual.querySelector("[data-why-one-roas]");
      var stepIndex = 0;
      var guaranteeIndex = 0;
      var principleIndex = 0;

      function animateBarGroups(groups, min, max, drift) {
        groups.forEach(function (group) {
          Array.prototype.slice.call(group.children).forEach(function (bar, idx) {
            var base = min + (idx * drift);
            var jitter = Math.floor(Math.random() * 12) - 6;
            var next = Math.max(min - 4, Math.min(max, base + jitter));
            bar.style.height = next + "%";
          });
        });
      }

      function rotateProcess() {
        if (!processSteps.length) return;
        processSteps.forEach(function (step) { step.classList.remove("is-active"); });
        processSteps[stepIndex % processSteps.length].classList.add("is-active");
        stepIndex += 1;
      }

      function rotateGuarantees() {
        if (!guaranteeItems.length) return;
        guaranteeItems.forEach(function (item) { item.classList.remove("is-active"); });
        guaranteeItems[guaranteeIndex % guaranteeItems.length].classList.add("is-active");
        guaranteeIndex += 1;
      }

      function rotatePrinciples() {
        if (!principleItems.length) return;
        principleItems.forEach(function (item) { item.classList.remove("is-active"); });
        principleItems[principleIndex % principleItems.length].classList.add("is-active");
        principleIndex += 1;
      }

      function pulseFunnel() {
        funnelBars.forEach(function (bar, idx) {
          var base = 86 - (idx * 22);
          var jitter = Math.floor(Math.random() * 8) - 4;
          var next = Math.max(12, Math.min(92, base + jitter));
          bar.style.width = next + "%";
        });
      }

      function pulseWhyKpis() {
        var growth = 68 + Math.floor(Math.random() * 9) - 4;
        var leads = 362 + Math.floor(Math.random() * 21) - 9;
        var cpl = 37.21 + ((Math.random() * 1.2) - 0.6);
        var conv = 6.42 + ((Math.random() * 0.22) - 0.11);
        var roas = 4.87 + ((Math.random() * 0.34) - 0.17);

        if (growthValue) growthValue.textContent = "+" + String(Math.max(58, growth)) + "%";
        if (leadsValue) leadsValue.textContent = String(Math.max(330, leads));
        if (cplValue) cplValue.textContent = cpl.toFixed(2).replace(".", ",");
        if (convValue) convValue.textContent = conv.toFixed(2).replace(".", ",") + "%";
        if (roasValue) roasValue.textContent = roas.toFixed(2).replace(".", ",");
      }

      rotateProcess();
      rotateGuarantees();
      rotatePrinciples();
      pulseFunnel();
      pulseWhyKpis();
      animateBarGroups(lineGroups, 16, 72, 7);
      animateBarGroups(revenueGroups, 18, 92, 9);

      if (upsellioReducedMotion || window.matchMedia("(max-width: 760px)").matches) return;

      upsellioStartInterval(rotateProcess, 3400);
      upsellioStartInterval(rotateGuarantees, 3800);
      upsellioStartInterval(rotatePrinciples, 4200);
      upsellioStartInterval(pulseFunnel, 3600);
      upsellioStartInterval(pulseWhyKpis, 4400);
      upsellioStartInterval(function () { animateBarGroups(lineGroups, 16, 72, 7); }, 4000);
      upsellioStartInterval(function () { animateBarGroups(revenueGroups, 18, 92, 9); }, 4200);
    })();

    (function () {
      var processVisual = document.getElementById("process-visual");
      if (!processVisual) return;

      var processSteps = Array.prototype.slice.call(processVisual.querySelectorAll(".process-card"));
      var processLines = Array.prototype.slice.call(processVisual.querySelectorAll("[data-process-line]"));
      var impactValues = Array.prototype.slice.call(processVisual.querySelectorAll("[data-process-impact]"));
      var processIndex = 0;
      var impactBase = [62, 38, -27, 41, 55, 73];

      function animateProcessLines() {
        processLines.forEach(function (group) {
          Array.prototype.slice.call(group.children).forEach(function (bar, idx) {
            var base = 16 + (idx * 7);
            var jitter = Math.floor(Math.random() * 12) - 6;
            var next = Math.max(12, Math.min(72, base + jitter));
            bar.style.height = next + "%";
          });
        });
      }

      function rotateProcessSteps() {
        if (!processSteps.length) return;
        processSteps.forEach(function (step) { step.classList.remove("is-active"); });
        processSteps[processIndex % processSteps.length].classList.add("is-active");
        processIndex += 1;
      }

      function pulseImpacts() {
        impactValues.forEach(function (item, idx) {
          var base = impactBase[idx] || 0;
          var jitter = Math.floor(Math.random() * 7) - 3;
          var next = base + jitter;
          var sign = next > 0 ? "+" : "";
          item.textContent = sign + String(next) + "%";
        });
      }

      rotateProcessSteps();
      animateProcessLines();
      pulseImpacts();

      if (upsellioReducedMotion || window.matchMedia("(max-width: 760px)").matches) return;

      upsellioStartInterval(rotateProcessSteps, 3600);
      upsellioStartInterval(animateProcessLines, 4000);
      upsellioStartInterval(pulseImpacts, 4600);
    })();

    (function () {
      var caseVisual = document.getElementById("case-portfolio-visual");
      if (!caseVisual) return;

      var impactBars = Array.prototype.slice.call(caseVisual.querySelectorAll("[data-case-impact-bar]"));
      var afterValues = Array.prototype.slice.call(caseVisual.querySelectorAll("[data-case-after]"));
      var portfolioLines = Array.prototype.slice.call(caseVisual.querySelectorAll("[data-portfolio-line]"));
      var kpiValues = Array.prototype.slice.call(caseVisual.querySelectorAll("[data-portfolio-kpi]"));
      var kpiItems = Array.prototype.slice.call(caseVisual.querySelectorAll(".portfolio-kpi-item"));
      var kpiIndex = 0;

      function animateBars() {
        impactBars.forEach(function (bar, idx) {
          var base = idx === 1 ? 34 : (80 + (idx * 4));
          var jitter = Math.floor(Math.random() * 8) - 4;
          var next = Math.max(24, Math.min(92, base + jitter));
          bar.style.width = next + "%";
        });
      }

      function animateLines() {
        portfolioLines.forEach(function (group) {
          Array.prototype.slice.call(group.children).forEach(function (bar, idx) {
            var base = 14 + (idx * 8);
            var jitter = Math.floor(Math.random() * 10) - 5;
            var next = Math.max(10, Math.min(66, base + jitter));
            bar.style.height = next + "%";
          });
        });
      }

      function rotateKpis() {
        if (!kpiItems.length) return;
        kpiItems.forEach(function (item) { item.classList.remove("is-active"); });
        kpiItems[kpiIndex % kpiItems.length].classList.add("is-active");
        kpiIndex += 1;
      }

      function pulseValues() {
        if (afterValues.length >= 4) {
          afterValues[0].textContent = String(162 + (Math.floor(Math.random() * 7) - 3));
          afterValues[1].textContent = String(76 + (Math.floor(Math.random() * 5) - 2)) + " zl";
          afterValues[2].textContent = (2.89 + ((Math.random() * 0.16) - 0.08)).toFixed(2).replace(".", ",") + "%";
          afterValues[3].textContent = String(186000 + (Math.floor(Math.random() * 9000) - 4500)).replace(/\B(?=(\d{3})+(?!\d))/g, " ") + " zl";
        }

        if (kpiValues.length === 4) {
          kpiValues[0].textContent = "+" + String(108 + (Math.floor(Math.random() * 8) - 3)) + "%";
          kpiValues[1].textContent = "-" + String(46 + (Math.floor(Math.random() * 6) - 2)) + "%";
          kpiValues[2].textContent = "+" + String(139 + (Math.floor(Math.random() * 10) - 4)) + "%";
          kpiValues[3].textContent = "+" + String(114 + (Math.floor(Math.random() * 8) - 3)) + "%";
        }
      }

      animateBars();
      animateLines();
      rotateKpis();
      pulseValues();

      if (upsellioReducedMotion || window.matchMedia("(max-width: 760px)").matches) return;

      upsellioStartInterval(animateBars, 3900);
      upsellioStartInterval(animateLines, 3700);
      upsellioStartInterval(rotateKpis, 3400);
      upsellioStartInterval(pulseValues, 4500);
    })();

    (function () {
      var contactVisual = document.getElementById("contact-strategy-visual");
      if (!contactVisual) return;

      var flowItems = Array.prototype.slice.call(contactVisual.querySelectorAll(".contact-flow-step"));
      var proofItems = Array.prototype.slice.call(contactVisual.querySelectorAll(".contact-proof-item"));
      var metricGroups = Array.prototype.slice.call(contactVisual.querySelectorAll("[data-contact-line]"));
      var flowIndex = 0;
      var proofIndex = 0;

      function animateContactLines() {
        metricGroups.forEach(function (group) {
          Array.prototype.slice.call(group.children).forEach(function (bar, idx) {
            var base = 16 + (idx * 8);
            var jitter = Math.floor(Math.random() * 12) - 6;
            var next = Math.max(12, Math.min(72, base + jitter));
            bar.style.height = next + "%";
          });
        });
      }

      function rotateFlow() {
        if (!flowItems.length) return;
        flowItems.forEach(function (item) { item.classList.remove("is-active"); });
        flowItems[flowIndex % flowItems.length].classList.add("is-active");
        flowIndex += 1;
      }

      function rotateProofs() {
        if (!proofItems.length) return;
        proofItems.forEach(function (item) { item.classList.remove("is-active"); });
        proofItems[proofIndex % proofItems.length].classList.add("is-active");
        proofIndex += 1;
      }

      rotateFlow();
      rotateProofs();
      animateContactLines();

      if (upsellioReducedMotion || window.matchMedia("(max-width: 760px)").matches) return;

      upsellioStartInterval(rotateFlow, 3400);
      upsellioStartInterval(rotateProofs, 4200);
      upsellioStartInterval(animateContactLines, 3900);
    })();
  </script>

  <?php
  echo function_exists("upsellio_render_unified_footer")
      ? upsellio_render_unified_footer(["contact_email" => $contact_email])
      : "";
  ?>

  <button class="scroll-top" id="scroll-top" aria-label="Wróć na górę">↑</button>
  <?php wp_footer(); ?>
</body>
</html>

