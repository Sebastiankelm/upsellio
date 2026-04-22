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
if (function_exists("upsellio_limit_meta_description")) {
    $seo_description = upsellio_limit_meta_description($seo_description, 130);
}
$seo_og_title = trim((string) ($seo_section["og_title"] ?? ""));
$seo_og_description = trim((string) ($seo_section["og_description"] ?? ""));
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
      padding-top: clamp(46px, 6.5vw, 80px);
      padding-bottom: clamp(48px, 6.2vw, 78px);
    }

    .home-semrush-flow .hero-copy .h1 {
      max-width: 18ch;
      margin-bottom: clamp(16px, 2.2vw, 24px);
    }

    .home-semrush-flow .hero-copy .lead {
      max-width: 56ch;
      margin-bottom: clamp(18px, 2.6vw, 30px);
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
      </div>
    </div>
  </header>

  <main class="home-semrush-flow" data-home-curated="1">
    <?php if (!empty($front_page_issues) && current_user_can("manage_options")) : ?>
      <section class="section-sm">
        <div class="wrap">
          <div style="padding:12px 14px;border:1px solid #edcccc;background:#fff2f2;border-radius:10px;color:#b13a3a;font-size:13px;">
            <strong>Brakujaca konfiguracja dynamiczna:</strong>
            <ul style="margin:8px 0 0 18px;">
              <?php foreach ($front_page_issues as $issue) : ?>
                <li><?php echo esc_html((string) $issue); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </section>
    <?php endif; ?>
    <section class="hero s-hero" id="start">
      <div class="wrap hero-grid">
        <div class="hero-copy">
          <div class="hero-pill reveal visible">
            <div class="hero-pill-dot">●</div>
            <span><?php echo esc_html((string) ($hero_section["pill"] ?? "Dla malych i srednich firm B2B")); ?></span>
          </div>

          <h1 class="h1 reveal visible"><?php echo esc_html((string) ($hero_section["title"] ?? "Marketing internetowy i strony WWW, ktore realnie sprzedaja")); ?></h1>

          <p class="lead reveal visible"><?php echo esc_html((string) ($hero_section["lead"] ?? "")); ?></p>

          <div class="hero-actions reveal visible">
            <a href="<?php echo esc_url(home_url((string) ($hero_section["primary_cta_url"] ?? "/#kontakt"))); ?>" class="btn btn-primary btn-pulse"><?php echo esc_html((string) ($hero_section["primary_cta_label"] ?? "Umow bezplatna rozmowe")); ?> →</a>
            <a href="<?php echo esc_url(home_url((string) ($hero_section["secondary_cta_url"] ?? "/#uslugi"))); ?>" class="btn btn-secondary"><?php echo esc_html((string) ($hero_section["secondary_cta_label"] ?? "Zobacz co robie")); ?></a>
          </div>

          <div class="hero-micro reveal visible">
            <?php echo esc_html((string) ($hero_section["micro"] ?? "")); ?>
          </div>

          <div class="hero-trust reveal visible">
            <?php $hero_trust_items = isset($hero_section["trust_items"]) && is_array($hero_section["trust_items"]) ? $hero_section["trust_items"] : []; ?>
            <?php foreach ($hero_trust_items as $hero_trust_item) : ?>
              <?php $hero_trust_item = trim((string) $hero_trust_item); ?>
              <?php if ($hero_trust_item === "") : ?>
                <?php continue; ?>
              <?php endif; ?>
              <div class="hero-trust-item"><span class="hero-trust-dot">✓</span><?php echo esc_html($hero_trust_item); ?></div>
            <?php endforeach; ?>
          </div>
        </div>

        <?php
        $hero_aside_stats = isset($hero_section["aside_stats"]) && is_array($hero_section["aside_stats"]) ? $hero_section["aside_stats"] : [];
        $hero_stat_1 = $hero_aside_stats[0] ?? ["number" => "128", "text" => "Leady / miesiac"];
        $hero_stat_2 = $hero_aside_stats[1] ?? ["number" => "82%", "text" => "Jakosc leadow"];
        $hero_stat_3 = $hero_aside_stats[2] ?? ["number" => "37 zl", "text" => "Koszt / lead"];
        ?>
        <aside class="hero-aside" id="hero-system">
          <div class="hero-aside-label"><?php echo esc_html((string) ($hero_section["aside_label"] ?? "System pozyskiwania leadow B2B")); ?></div>
          <div class="hero-system">
            <div class="hero-system-head">
              <div>
                <div class="hero-system-side-title">Od chaosu w marketingu</div>
                <div class="hero-system-side-sub">Rozproszone dzialania</div>
              </div>
              <div>
                <div class="hero-system-side-title">Do przewidywalnego wzrostu</div>
                <div class="hero-system-side-sub">Uporzadkowany system</div>
              </div>
            </div>
            <div class="hero-system-top">
              <div class="hero-channel-stack">
                <article class="hero-channel-card">
                  <div class="hero-channel-head"><span>Meta Ads</span><span>+24%</span></div>
                  <div class="hero-channel-metric">Kampanie leadowe</div>
                  <div class="hero-spark" data-hero-spark><span style="height:24%"></span><span style="height:36%"></span><span style="height:45%"></span><span style="height:38%"></span><span style="height:58%"></span><span style="height:74%"></span></div>
                </article>
                <article class="hero-channel-card">
                  <div class="hero-channel-head"><span>Google Ads</span><span>+18%</span></div>
                  <div class="hero-channel-metric">Ruch o wysokiej intencji</div>
                  <div class="hero-spark" data-hero-spark><span style="height:18%"></span><span style="height:27%"></span><span style="height:29%"></span><span style="height:46%"></span><span style="height:64%"></span><span style="height:70%"></span></div>
                </article>
                <article class="hero-channel-card">
                  <div class="hero-channel-head"><span>LinkedIn Ads</span><span>+12%</span></div>
                  <div class="hero-channel-metric">Dotarcie do decydentow</div>
                  <div class="hero-spark" data-hero-spark><span style="height:22%"></span><span style="height:31%"></span><span style="height:42%"></span><span style="height:39%"></span><span style="height:52%"></span><span style="height:68%"></span></div>
                </article>
                <article class="hero-channel-card">
                  <div class="hero-channel-head"><span>E-mail / Outreach</span><span>+17%</span></div>
                  <div class="hero-channel-metric">Follow-up i sekwencje</div>
                  <div class="hero-spark" data-hero-spark><span style="height:20%"></span><span style="height:28%"></span><span style="height:35%"></span><span style="height:44%"></span><span style="height:52%"></span><span style="height:66%"></span></div>
                </article>
              </div>

              <article class="hero-system-core">
                <div class="hero-core-nav">
                  <span class="is-active">Oferta</span><span>Case studies</span><span>O mnie</span><span>Proces</span>
                </div>
                <div class="hero-core-main">
                  <div>
                    <div class="hero-core-title">Skuteczny marketing. <span class="accent">Wiecej leadow.</span></div>
                    <p class="hero-core-lead">Pomagam firmom B2B systematycznie pozyskiwac wartosciowe leady i zamieniac je w klientow.</p>
                    <a href="<?php echo esc_url(home_url("/#kontakt")); ?>" class="hero-core-btn">Umow konsultacje</a>
                  </div>
                  <div class="hero-core-form">
                    <div class="hero-core-field"></div>
                    <div class="hero-core-field"></div>
                    <div class="hero-core-field"></div>
                    <div class="hero-core-submit"></div>
                  </div>
                </div>
                <div class="hero-core-grid">
                  <div>Strategia oparta na danych</div>
                  <div>Skuteczne kampanie performance</div>
                  <div>Konwersja strony i oferty</div>
                  <div>Leady gotowe do rozmowy</div>
                </div>
              </article>

              <div class="hero-kpi-stack">
                <article class="hero-kpi-block">
                  <div class="hero-kpi-label">Leady i konwersja</div>
                  <div class="hero-kpi-mini-grid">
                    <div class="hero-kpi-mini-card">
                      <div class="hero-kpi-label"><?php echo esc_html((string) ($hero_stat_1["text"] ?? "Leady")); ?></div>
                      <div class="hero-kpi-row"><div class="hero-kpi-value" data-hero-kpi-value><?php echo esc_html((string) ($hero_stat_1["number"] ?? "142")); ?></div><div class="hero-kpi-change">+25%</div></div>
                      <div class="hero-kpi-progress"><i data-hero-kpi-progress style="width:74%"></i></div>
                    </div>
                    <div class="hero-kpi-mini-card">
                      <div class="hero-kpi-label">Konwersja</div>
                      <div class="hero-kpi-row"><div class="hero-kpi-value" data-hero-kpi-value><?php echo esc_html((string) ($hero_stat_2["number"] ?? "6,42%")); ?></div><div class="hero-kpi-change">+18%</div></div>
                      <div class="hero-kpi-progress"><i data-hero-kpi-progress style="width:66%"></i></div>
                    </div>
                  </div>
                </article>
                <article class="hero-pipeline-box">
                  <div class="hero-pipeline-title">Pipeline sprzedazy</div>
                  <div class="hero-pipeline-row"><span>Nowe szanse</span><b>142</b></div>
                  <div class="hero-pipeline-row"><span>Kwalifikacja</span><b>64</b></div>
                  <div class="hero-pipeline-row"><span>Oferta</span><b>28</b></div>
                  <div class="hero-pipeline-row"><span>Negocjacje</span><b>11</b></div>
                  <div class="hero-pipeline-row"><span>Zamkniete</span><b>7</b></div>
                </article>
              </div>
            </div>

            <div class="hero-system-pipe" data-hero-pipe>
              <div class="hero-pipe-step is-active">Wejscie<br />Ruch</div>
              <div class="hero-pipe-step">Zaangazowanie<br />Strona</div>
              <div class="hero-pipe-step">Konwersja<br />Lead</div>
              <div class="hero-pipe-step">Kwalifikacja<br />Jakosc</div>
              <div class="hero-pipe-step">Sprzedaz<br />Wynik</div>
            </div>

            <div class="hero-system-bottom">
              <div>
                <article class="hero-chaos-note">
                  <strong>Brak spojnosci = brak wzrostu</strong>
                  <div class="hero-chaos-note-grid">
                    <span>Rozproszone zrodla i priorytety.</span>
                    <span>Niska konwersja ruchu na ofercie.</span>
                    <span>Nieprzewidywalne wyniki i decyzje.</span>
                  </div>
                </article>
                <article class="hero-analytics-strip" style="margin-top:8px;">
                  <div class="hero-analytics-title">Analityka i optymalizacja</div>
                  <div class="hero-analytics-grid">
                    <div class="hero-analytics-cell"><div class="k">Koszt / lead</div><div class="v">37 zl</div><div class="d">-16%</div></div>
                    <div class="hero-analytics-cell"><div class="k">Wsp. konwersji</div><div class="v">6,42%</div><div class="d">+18%</div></div>
                    <div class="hero-analytics-cell"><div class="k">ROAS</div><div class="v">4,21</div><div class="d">+27%</div></div>
                    <div class="hero-analytics-cell"><div class="k">Przychody</div><div class="v">+67%</div><div class="d">Wzrost</div></div>
                  </div>
                </article>
              </div>

              <div class="hero-optimization-node">Optymalizacja na danych</div>

              <article class="hero-growth-panel">
                <div class="hero-growth-meta"><div class="k">Wzrost przychodow</div><div class="v">+35%</div></div>
                <div class="hero-growth-chart">
                  <div class="hero-growth-line" data-hero-growth-line>
                    <span style="height:18%"></span><span style="height:24%"></span><span style="height:28%"></span><span style="height:32%"></span><span style="height:38%"></span><span style="height:44%"></span><span style="height:52%"></span><span style="height:58%"></span>
                  </div>
                </div>
              </article>
            </div>
          </div>
        </aside>
      </div>
    </section>

    <section class="section section-border" id="dlaczego">
      <div class="wrap">
        <div class="content why-intro-block">
          <div class="eyebrow reveal"><?php echo esc_html((string) ($why_section["eyebrow"] ?? "Dlaczego to dziala")); ?></div>
          <h2 class="h2 reveal d1"><?php echo esc_html((string) ($why_section["title"] ?? "")); ?></h2>
          <p class="body reveal d2" style="margin-top: 18px;"><?php echo esc_html((string) ($why_section["lead"] ?? "")); ?></p>
        </div>

        <div class="why-trust-visual reveal d1" id="why-trust-visual">
          <div class="why-horizontal-block why-contact-block">
            <h3 class="why-one-heading">Jeden punkt kontaktu. <span class="accent">Od wyzwania do mierzalnego wzrostu.</span></h3>
            <p class="why-one-sub">Bez posrednikow. Bez chaosu. Skupienie na tym, co daje wynik.</p>

            <div class="why-one-process" data-why-one-process>
              <article class="why-one-step is-active">
                <div class="why-one-step-num">1. Wyzwanie</div>
                <div class="why-one-step-title">Diagnoza sytuacji</div>
                <div class="why-one-step-text">Rozmawiamy o celach i problemach, ktore blokuja wzrost.</div>
                <div class="why-one-step-list"><span>Niska jakosc leadow</span><span>Wysoki koszt pozyskania</span><span>Niska konwersja strony</span></div>
              </article>
              <article class="why-one-step">
                <div class="why-one-step-num">2. Strategia</div>
                <div class="why-one-step-title">Plan dzialan pod cel</div>
                <div class="why-one-step-text">Analiza danych i roadmapa na wzrost leadow i sprzedazy.</div>
                <div class="why-one-step-list"><span>Audyt i analiza</span><span>Plan kampanii i lejka</span><span>Strategia strony</span></div>
              </article>
              <article class="why-one-center">
                <div class="why-one-avatar">
                  <b>U</b>
                  <strong>Sebastian Kelm</strong>
                  <span>Jeden punkt kontaktu</span>
                </div>
              </article>
              <article class="why-one-step">
                <div class="why-one-step-num">3. Wdrozenie</div>
                <div class="why-one-step-title">Realizacja od A do Z</div>
                <div class="why-one-step-text">Kampanie, strona i automatyzacja wdrazane jako jeden system.</div>
                <div class="why-one-step-list"><span>Meta Ads / Google Ads</span><span>Strony i landing page</span><span>Analityka i optymalizacja</span></div>
              </article>
              <article class="why-one-step">
                <div class="why-one-step-num">4. Mierzalny wynik</div>
                <div class="why-one-step-title">Leady i przychody</div>
                <div class="why-one-step-text">Wiecej wartosciowych leadow i przewidywalny wzrost sprzedazy.</div>
                <div class="why-one-step-list"><span>Lepsza jakosc leadow</span><span>Wyzsza konwersja</span><span>Wzrost przychodow</span></div>
              </article>
            </div>

            <div class="why-one-guarantees" data-why-one-guarantees>
              <div class="why-one-guarantee is-active"><i></i><span>Bez agencji</span></div>
              <div class="why-one-guarantee"><i></i><span>Bez posrednikow</span></div>
              <div class="why-one-guarantee"><i></i><span>Bez zbednych kosztow</span></div>
              <div class="why-one-guarantee"><i></i><span>Pelna transparentnosc</span></div>
            </div>
          </div>

          <div class="why-horizontal-block why-results-block">
            <div class="why-one-results">
              <article class="why-one-panel">
                <div class="why-one-panel-title">Lejek sprzedazowy</div>
                <div class="why-one-funnel-row"><div class="why-one-funnel-bar"><i data-why-one-funnel-bar style="width:86%"></i></div><b>23 810</b></div>
                <div class="why-one-funnel-row"><div class="why-one-funnel-bar"><i data-why-one-funnel-bar style="width:58%"></i></div><b>362</b></div>
                <div class="why-one-funnel-row"><div class="why-one-funnel-bar"><i data-why-one-funnel-bar style="width:35%"></i></div><b>148</b></div>
                <div class="why-one-funnel-row"><div class="why-one-funnel-bar"><i data-why-one-funnel-bar style="width:16%"></i></div><b>64</b></div>
              </article>

              <article class="why-one-panel">
                <div class="why-one-panel-title">Wyniki mierzone w liczbach</div>
                <div class="why-one-kpi-grid">
                  <div class="why-one-kpi"><div class="k">Liczba leadow</div><div class="v" data-why-one-leads>362</div><div class="d">+28%</div><div class="why-one-line" data-why-one-line><i style="height:18%"></i><i style="height:24%"></i><i style="height:31%"></i><i style="height:38%"></i><i style="height:51%"></i></div></div>
                  <div class="why-one-kpi"><div class="k">Koszt / lead</div><div class="v" data-why-one-cpl>37,21</div><div class="d">-18%</div><div class="why-one-line" data-why-one-line><i style="height:52%"></i><i style="height:46%"></i><i style="height:36%"></i><i style="height:29%"></i><i style="height:22%"></i></div></div>
                  <div class="why-one-kpi"><div class="k">Wsp. konwersji</div><div class="v" data-why-one-conv>6,42%</div><div class="d">+18%</div><div class="why-one-line" data-why-one-line><i style="height:16%"></i><i style="height:24%"></i><i style="height:32%"></i><i style="height:41%"></i><i style="height:48%"></i></div></div>
                  <div class="why-one-kpi"><div class="k">ROAS</div><div class="v" data-why-one-roas>4,87</div><div class="d">+25%</div><div class="why-one-line" data-why-one-line><i style="height:14%"></i><i style="height:23%"></i><i style="height:30%"></i><i style="height:39%"></i><i style="height:56%"></i></div></div>
                </div>
              </article>

              <article class="why-one-panel">
                <div class="why-one-panel-title">Przychody (PLN)</div>
                <div class="why-one-growth-badge" data-why-one-growth>+68%</div>
                <div class="why-one-revenue-chart" data-why-one-revenue>
                  <i style="height:22%"></i><i style="height:31%"></i><i style="height:35%"></i><i style="height:44%"></i><i style="height:61%"></i><i style="height:76%"></i>
                </div>
                <div class="why-one-impact-list">
                  <span>Wyzsza jakosc leadow</span>
                  <span>Nizszy koszt pozyskania</span>
                  <span>Wiecej zamknietych transakcji</span>
                  <span>Przewidywalne przychody</span>
                </div>
              </article>
            </div>

            <div class="why-one-principles" data-why-one-principles>
              <div class="why-one-principle is-active"><i></i><span>Strategia oparta na danych, nie na domyslach.</span></div>
              <div class="why-one-principle"><i></i><span>Testujemy, mierzymy i stale optymalizujemy.</span></div>
              <div class="why-one-principle"><i></i><span>Transparentne zasady wspolpracy i raportowania.</span></div>
              <div class="why-one-principle"><i></i><span>Skupienie na leadach, marzy i przychodzie.</span></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section-sm home-structure-toggle">
      <div class="wrap home-structure-toggle-wrap">
        <button class="home-structure-toggle-btn" id="home-structure-toggle-btn" type="button" aria-expanded="false" aria-controls="problem jak-dzialam wyniki dla-kogo faq">
          Pokaz pelny widok strony
        </button>
      </div>
    </section>

    <section class="section bg-soft section-border js-home-optional-section" id="problem">
      <div class="wrap">
        <div class="content">
          <div class="eyebrow reveal"><?php echo esc_html((string) ($problem_section["eyebrow"] ?? "Problem")); ?></div>
          <h2 class="h2 reveal d1"><?php echo esc_html((string) ($problem_section["title"] ?? "")); ?></h2>
          <p class="body reveal d2" style="margin-top: 18px;"><?php echo esc_html((string) ($problem_section["lead"] ?? "")); ?></p>
        </div>

        <div class="problem-grid">
          <?php $problem_items = isset($problem_section["items"]) && is_array($problem_section["items"]) ? $problem_section["items"] : []; ?>
          <?php foreach ($problem_items as $problem_item_index => $problem_item) : ?>
            <?php
            $problem_item = trim((string) $problem_item);
            if ($problem_item === "") {
                continue;
            }
            $problem_delay_class = "";
            if ($problem_item_index % 4 === 1) {
                $problem_delay_class = " d1";
            } elseif ($problem_item_index % 4 === 2) {
                $problem_delay_class = " d2";
            } elseif ($problem_item_index % 4 === 3) {
                $problem_delay_class = " d3";
            }
            ?>
            <div class="problem-card reveal<?php echo esc_attr($problem_delay_class); ?>"><?php echo esc_html($problem_item); ?></div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <section class="section section-border" id="uslugi">
      <div class="wrap">
        <div class="content">
          <div class="eyebrow reveal"><?php echo esc_html((string) ($services_section["eyebrow"] ?? "Uslugi")); ?></div>
          <h2 class="h2 reveal d1"><?php echo esc_html((string) ($services_section["title"] ?? "")); ?></h2>
          <p class="body reveal d2" style="margin-top: 18px;"><?php echo esc_html((string) ($services_section["lead"] ?? "")); ?></p>
        </div>

        <?php $services_primary = isset($services_section["primary_service"]) && is_array($services_section["primary_service"]) ? $services_section["primary_service"] : []; ?>
        <div class="service-hero reveal" style="margin-top: 40px;">
          <div class="service-offer-layout">
            <div class="service-offer-main">
              <div class="service-top">
                <div class="h3"><?php echo esc_html((string) ($services_primary["title"] ?? "")); ?></div>
                <span class="badge badge-green"><?php echo esc_html((string) ($services_primary["badge"] ?? "Glowna usluga")); ?></span>
              </div>
              <p class="body"><?php echo esc_html((string) ($services_primary["description"] ?? "")); ?></p>
              <div class="service-check-title"><?php echo esc_html((string) ($services_primary["checklist_title"] ?? "W ramach tej uslugi")); ?></div>
              <div class="service-checklist">
                <?php $services_primary_checklist = isset($services_primary["checklist"]) && is_array($services_primary["checklist"]) ? $services_primary["checklist"] : []; ?>
                <?php foreach ($services_primary_checklist as $services_primary_checklist_item) : ?>
                  <?php $services_primary_checklist_item = trim((string) $services_primary_checklist_item); ?>
                  <?php if ($services_primary_checklist_item === "") : ?>
                    <?php continue; ?>
                  <?php endif; ?>
                  <div class="service-check"><span class="service-check-icon">✓</span><span><?php echo esc_html($services_primary_checklist_item); ?></span></div>
                <?php endforeach; ?>
              </div>
              <a href="<?php echo esc_url(home_url((string) ($services_primary["cta_url"] ?? "/#kontakt"))); ?>" class="btn btn-primary" style="margin-top: var(--sp-3);"><?php echo esc_html((string) ($services_primary["cta_label"] ?? "Zapytaj o kampanie")); ?> →</a>
            </div>

            <aside class="service-seo-block">
              <div class="service-seo-title">Sekcja SEO / tresc ekspercka</div>
              <details class="service-seo-item" open>
                <summary>Co zyskujesz poza kampania Meta?</summary>
                <p>Oprocz kampanii performance dostajesz strukture tresci pod SEO, mapowanie intencji, plan podstron i sekcji sprzedazowych, aby ruch organiczny rowniez dowozil leady.</p>
              </details>
              <details class="service-seo-item">
                <summary>Jak wyglada ukrywanie/rozwijanie tresci?</summary>
                <p>Stosujemy rozwijane bloki FAQ i sekcje kontekstowe, ktore porzadkuja informacje dla uzytkownika, a jednoczesnie rozszerzaja pokrycie fraz kluczowych.</p>
              </details>
              <details class="service-seo-item">
                <summary>Efekt biznesowy SEO + Ads</summary>
                <p>Lepsza widocznosc, nizszy CPL w dluzszym okresie i stabilniejszy pipeline, bo nie opierasz sie tylko na jednym zrodle pozyskania.</p>
              </details>
            </aside>

            <div class="service-what-you-get">
              <div class="service-what-title">Co konkretnie dostajesz</div>
              <div class="service-what-grid">
                <div class="service-what-item">Strategie i plan kampanii pod leady B2B</div>
                <div class="service-what-item">Animowany dashboard wynikow i statystyk</div>
                <div class="service-what-item">Strone/landing zoptymalizowana pod konwersje</div>
                <div class="service-what-item">Analityke, testy i ciagla optymalizacje</div>
              </div>
            </div>
          </div>

          <div class="service-meta-visual" id="service-meta-visual">
            <div class="service-meta-layout">
              <article class="service-meta-panel">
                <div class="service-meta-title">Mapa konwersji strony</div>
                <div class="web-flow-list" data-web-flow>
                  <div class="web-flow-item is-active"><div class="web-flow-number">01</div><div class="web-flow-label"><strong>Hero / UVP</strong><span>Jasny przekaz i mocne CTA.</span></div></div>
                  <div class="web-flow-item"><div class="web-flow-number">02</div><div class="web-flow-label"><strong>Uslugi</strong><span>Oferta dopasowana do B2B.</span></div></div>
                  <div class="web-flow-item"><div class="web-flow-number">03</div><div class="web-flow-label"><strong>Dowody zaufania</strong><span>Case studies i liczby.</span></div></div>
                  <div class="web-flow-item"><div class="web-flow-number">04</div><div class="web-flow-label"><strong>Social proof</strong><span>Efekty i referencje klientow.</span></div></div>
                  <div class="web-flow-item"><div class="web-flow-number">05</div><div class="web-flow-label"><strong>Konwersja</strong><span>Formularz i mikro-CTA.</span></div></div>
                  <div class="web-flow-item"><div class="web-flow-number">06</div><div class="web-flow-label"><strong>Analityka</strong><span>Mierzenie i optymalizacja.</span></div></div>
                </div>
              </article>

              <article class="web-page-shell">
                <div class="web-page-topbar">
                  <div style="display:flex;align-items:center;gap:6px;">
                    <div class="web-page-logo"></div>
                    <div class="web-page-nav"><span>Oferta</span><span>Case studies</span><span>Kontakt</span></div>
                  </div>
                  <button class="web-page-cta-pill">Umow konsultacje</button>
                </div>

                <div class="web-page-main">
                  <div class="web-page-copy">
                    <div class="web-page-eyebrow">Dla firm B2B</div>
                    <div class="web-page-headline">Wiecej wartosciowych leadow. <em>Wieksza sprzedaz.</em></div>
                    <div class="web-page-lead">Strona internetowa zaprojektowana pod pozyskiwanie i domykanie klientow B2B.</div>
                    <div class="web-page-actions">
                      <button class="is-primary">Umow rozmowe</button>
                      <button>Zobacz case studies</button>
                    </div>
                  </div>
                  <div class="web-page-form"><i></i><i></i><i></i><i></i></div>
                </div>

                <div class="web-service-grid">
                  <div class="web-service-card"><strong>Kampanie Meta Ads</strong>Pozyskiwanie leadow B2B.</div>
                  <div class="web-service-card"><strong>Google Ads</strong>Ruch o wysokiej intencji.</div>
                  <div class="web-service-card"><strong>Strony B2B</strong>Konwersja i UX.</div>
                  <div class="web-service-card"><strong>Optymalizacja</strong>Ciagla poprawa wynikow.</div>
                </div>

                <div class="web-logo-row">
                  <span></span><span></span><span></span><span></span><span></span>
                </div>

                <div class="web-results-grid">
                  <div class="web-result-card">
                    <div class="web-result-label">Producent przemyslowy</div>
                    <div class="web-result-value" data-web-result-value>+168%</div>
                    <div class="web-result-delta">Wzrost leadow B2B</div>
                    <div class="web-result-line" data-web-line><i style="height:18%"></i><i style="height:23%"></i><i style="height:27%"></i><i style="height:32%"></i><i style="height:41%"></i><i style="height:54%"></i></div>
                  </div>
                  <div class="web-result-card">
                    <div class="web-result-label">Uslugi B2B</div>
                    <div class="web-result-value" data-web-result-value>-42%</div>
                    <div class="web-result-delta">Spadek CPL</div>
                    <div class="web-result-line" data-web-line><i style="height:44%"></i><i style="height:39%"></i><i style="height:34%"></i><i style="height:30%"></i><i style="height:26%"></i><i style="height:20%"></i></div>
                  </div>
                  <div class="web-result-card">
                    <div class="web-result-label">E-commerce B2B</div>
                    <div class="web-result-value" data-web-result-value>+73%</div>
                    <div class="web-result-delta">Wzrost przychodow</div>
                    <div class="web-result-line" data-web-line><i style="height:16%"></i><i style="height:21%"></i><i style="height:28%"></i><i style="height:35%"></i><i style="height:46%"></i><i style="height:58%"></i></div>
                  </div>
                </div>

                <div class="web-bottom-cta">
                  <span>Zrobmy pierwszy krok do wzrostu Twojej firmy.</span>
                  <button>Umow bezplatna rozmowe</button>
                </div>
              </article>

              <aside class="web-side-rail">
                <article class="web-side-card">
                  <div class="web-side-head">Analityka i wyniki</div>
                  <div class="web-side-kpi-grid">
                    <div class="web-side-kpi"><div class="k">Leady</div><div class="v" data-web-leads>362</div><div class="d">+28%</div><div class="web-side-line" data-web-line><i style="height:22%"></i><i style="height:33%"></i><i style="height:28%"></i><i style="height:46%"></i><i style="height:51%"></i></div></div>
                    <div class="web-side-kpi"><div class="k">CPL</div><div class="v" data-web-cpl>37,21</div><div class="d">-18%</div><div class="web-side-line" data-web-line><i style="height:51%"></i><i style="height:41%"></i><i style="height:39%"></i><i style="height:32%"></i><i style="height:26%"></i></div></div>
                    <div class="web-side-kpi"><div class="k">Konwersja</div><div class="v" data-web-conv>6,42%</div><div class="d">+33%</div><div class="web-side-line" data-web-line><i style="height:18%"></i><i style="height:24%"></i><i style="height:31%"></i><i style="height:42%"></i><i style="height:49%"></i></div></div>
                    <div class="web-side-kpi"><div class="k">ROAS</div><div class="v" data-web-roas>4,87</div><div class="d">+35%</div><div class="web-side-line" data-web-line><i style="height:16%"></i><i style="height:27%"></i><i style="height:34%"></i><i style="height:41%"></i><i style="height:57%"></i></div></div>
                  </div>
                </article>

                <article class="web-side-card">
                  <div class="web-side-head">Sciezka konwersji</div>
                  <div class="web-conversion-funnel">
                    <div class="web-funnel-row"><div class="web-funnel-bar"><i data-web-funnel-bar style="width:88%"></i></div><b>18 742</b></div>
                    <div class="web-funnel-row"><div class="web-funnel-bar"><i data-web-funnel-bar style="width:57%"></i></div><b>2 846</b></div>
                    <div class="web-funnel-row"><div class="web-funnel-bar"><i data-web-funnel-bar style="width:33%"></i></div><b>362</b></div>
                    <div class="web-funnel-row"><div class="web-funnel-bar"><i data-web-funnel-bar style="width:16%"></i></div><b>64</b></div>
                  </div>
                </article>

                <article class="web-side-card">
                  <div class="web-side-head">Optymalizacja UX</div>
                  <div class="web-ux-list" data-web-ux>
                    <div class="web-ux-item is-active"><i></i><span>Jasny komunikat wartosci</span></div>
                    <div class="web-ux-item"><i></i><span>Wyrazne CTA na kazdym ekranie</span></div>
                    <div class="web-ux-item"><i></i><span>Dowody zaufania i case studies</span></div>
                    <div class="web-ux-item"><i></i><span>Prosty formularz kontaktowy</span></div>
                    <div class="web-ux-item"><i></i><span>Analityka i ciagla optymalizacja</span></div>
                  </div>
                </article>
              </aside>
            </div>

            <div class="web-badge-strip">
              <div class="web-badge-item"><i></i><span>Precyzyjne targetowanie</span></div>
              <div class="web-badge-item"><i></i><span>Skuteczne kreacje</span></div>
              <div class="web-badge-item"><i></i><span>Leady wysokiej jakosci</span></div>
              <div class="web-badge-item"><i></i><span>Optymalizacja i skalowanie</span></div>
              <div class="web-badge-item"><i></i><span>Pelna przejrzystosc</span></div>
            </div>

            <article class="service-case-snapshot" data-service-case-snapshot>
              <div class="service-case-head">
                <strong>Snapshot interaktywny case study</strong>
                <span>Blok pod: Strony i sklepy internetowe</span>
              </div>
              <div class="service-case-metrics">
                <div class="service-case-metric"><span>Leady / miesiac</span><b data-service-case-value="152">152</b></div>
                <div class="service-case-metric"><span>Konwersja</span><b data-service-case-value="4.8">4,8%</b></div>
                <div class="service-case-metric"><span>CPL</span><b data-service-case-value="49">49 zl</b></div>
                <div class="service-case-metric"><span>ROAS</span><b data-service-case-value="5.2">5,2</b></div>
              </div>
              <div class="service-case-lines">
                <div class="service-case-line"><i data-service-case-bar style="width:72%"></i></div>
                <div class="service-case-line"><i data-service-case-bar style="width:64%"></i></div>
                <div class="service-case-line"><i data-service-case-bar style="width:58%"></i></div>
                <div class="service-case-line"><i data-service-case-bar style="width:76%"></i></div>
              </div>
            </article>
          </div>
        </div>

        <div class="service-grid">
          <?php $services_cards = isset($services_section["cards"]) && is_array($services_section["cards"]) ? $services_section["cards"] : []; ?>
          <?php foreach ($services_cards as $services_card_index => $services_card) : ?>
            <?php
            $services_card_title = trim((string) ($services_card["title"] ?? ""));
            $services_card_description = trim((string) ($services_card["description"] ?? ""));
            if ($services_card_title === "" || $services_card_description === "") {
                continue;
            }
            $services_card_delay_class = $services_card_index % 3 === 1 ? " d1" : ($services_card_index % 3 === 2 ? " d2" : "");
            ?>
            <div class="service-card reveal<?php echo esc_attr($services_card_delay_class); ?>">
              <div class="service-top">
                <div class="h3"><?php echo esc_html($services_card_title); ?></div>
                <span class="badge badge-gray"><?php echo esc_html((string) ($services_card["badge"] ?? "Usluga")); ?></span>
              </div>
              <p class="body"><?php echo esc_html($services_card_description); ?></p>
              <?php $services_card_chips = isset($services_card["chips"]) && is_array($services_card["chips"]) ? $services_card["chips"] : []; ?>
              <div class="chips">
                <?php foreach ($services_card_chips as $services_card_chip) : ?>
                  <?php $services_card_chip = trim((string) $services_card_chip); ?>
                  <?php if ($services_card_chip === "") : ?>
                    <?php continue; ?>
                  <?php endif; ?>
                  <span class="chip"><?php echo esc_html($services_card_chip); ?></span>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <?php $services_bonus = isset($services_section["bonus"]) && is_array($services_section["bonus"]) ? $services_section["bonus"] : []; ?>
        <div class="bonus reveal d2">
          <div class="bonus-head">
            <div class="bonus-icon">
              <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                <path d="M9 2L11.5 7H17L12.5 10.5L14 16L9 12.5L4 16L5.5 10.5L1 7H6.5L9 2Z" fill="white"/>
              </svg>
            </div>
            <div class="bonus-title"><?php echo esc_html((string) ($services_bonus["title"] ?? "")); ?></div>
            <div class="bonus-tag"><?php echo esc_html((string) ($services_bonus["tag"] ?? "W cenie")); ?></div>
          </div>

          <div class="bonus-body"><?php echo esc_html((string) ($services_bonus["body"] ?? "")); ?></div>

          <div class="bonus-chips">
            <?php $services_bonus_chips = isset($services_bonus["chips"]) && is_array($services_bonus["chips"]) ? $services_bonus["chips"] : []; ?>
            <?php foreach ($services_bonus_chips as $services_bonus_chip) : ?>
              <?php $services_bonus_chip = trim((string) $services_bonus_chip); ?>
              <?php if ($services_bonus_chip === "") : ?>
                <?php continue; ?>
              <?php endif; ?>
              <span class="bonus-chip"><?php echo esc_html($services_bonus_chip); ?></span>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </section>

    <section class="section-sm section-border industry-strip-section" aria-label="Branze wspolpracy">
      <div class="wrap">
        <div class="industry-strip">
          <div class="industry-strip-track">
            <span>Produkcja i przemysl</span>
            <span>Budownictwo</span>
            <span>OZE</span>
            <span>IT / SaaS B2B</span>
            <span>Logistyka</span>
            <span>E-commerce B2B</span>
            <span>Uslugi profesjonalne</span>
            <span>Hurt i dystrybucja</span>
            <span>Produkcja i przemysl</span>
            <span>Budownictwo</span>
            <span>OZE</span>
            <span>IT / SaaS B2B</span>
            <span>Logistyka</span>
            <span>E-commerce B2B</span>
            <span>Uslugi profesjonalne</span>
            <span>Hurt i dystrybucja</span>
          </div>
        </div>
      </div>
    </section>

    <section class="section-sm section-border">
      <div class="wrap">
        <div class="cta-band reveal">
          <div>
            <h3><?php echo esc_html((string) ($cta_band_section["title"] ?? "")); ?></h3>
            <p><?php echo esc_html((string) ($cta_band_section["text"] ?? "")); ?></p>
          </div>
          <a href="<?php echo esc_url(home_url((string) ($cta_band_section["cta_url"] ?? "/#kontakt"))); ?>" class="btn btn-primary"><?php echo esc_html((string) ($cta_band_section["cta_label"] ?? "Umow bezplatna rozmowe")); ?> →</a>
        </div>
      </div>
    </section>

    <section class="section section-border js-home-optional-section" id="jak-dzialam">
      <div class="wrap">
        <div class="content">
          <div class="eyebrow reveal"><?php echo esc_html((string) ($process_section["eyebrow"] ?? "Jak dzialam")); ?></div>
          <h2 class="h2 reveal d1"><?php echo esc_html((string) ($process_section["title"] ?? "")); ?></h2>
          <p class="body reveal d2" style="margin-top: 18px;"><?php echo esc_html((string) ($process_section["lead"] ?? "")); ?></p>
        </div>

        <div class="process-visual reveal d1" id="process-visual" style="margin-top: 40px;">
          <div class="process-track" data-process-track>
            <aside class="process-side">
              <div>
                <i>🏢</i>
                <strong>Twoj biznes</strong><br />
                Wyzwania, cele i ambicje.
              </div>
            </aside>

            <article class="process-card is-active">
              <div class="process-step-dot">1</div>
              <div class="process-card-title">Analiza i diagnoza</div>
              <div class="process-card-sub">Rozumienie Twojego biznesu i barier wzrostu.</div>
              <div class="process-card-list">
                <span>Analiza rynku, konkurencji i oferty</span>
                <span>Audyt kampanii, strony i lejka</span>
                <span>Dane i liczby zamiast domyslow</span>
              </div>
              <div class="process-card-effect"><strong>Efekt:</strong> Wiesz, co dziala i gdzie sa najwieksze rezerwy wzrostu.</div>
            </article>

            <article class="process-card">
              <div class="process-step-dot">2</div>
              <div class="process-card-title">Rekomendacja i plan dzialan</div>
              <div class="process-card-sub">Konkretny plan wzrostu na leady i sprzedaz.</div>
              <div class="process-card-list">
                <span>Plan marketingu i pozyskiwania leadow</span>
                <span>Plan kampanii, tresci i automatyzacji</span>
                <span>Priorytety, harmonogram i KPI</span>
              </div>
              <div class="process-card-effect"><strong>Efekt:</strong> Jasny plan: co, kiedy i po co robimy.</div>
            </article>

            <article class="process-card">
              <div class="process-step-dot">3</div>
              <div class="process-card-title">Wdrozenie i optymalizacja</div>
              <div class="process-card-sub">Realizacja i stale doskonalenie systemu.</div>
              <div class="process-card-list">
                <span>Wdrazamy kampanie, strony i automatyzacje</span>
                <span>Monitorujemy i analizujemy wyniki</span>
                <span>Skalujemy to, co daje najlepszy zwrot</span>
              </div>
              <div class="process-card-effect"><strong>Efekt:</strong> Lepsze wyniki, nizsze koszty i przewidywalny wzrost.</div>
            </article>

            <aside class="process-side">
              <div>
                <i>📈</i>
                <strong>Przewidywalny wzrost</strong><br />
                Wiecej leadow i wyzsza sprzedaz.
              </div>
            </aside>
          </div>

          <div class="process-impact">
            <div class="process-impact-head">Na co wplywamy i co poprawiamy</div>
            <div class="process-impact-grid">
              <div class="process-impact-card">
                <div class="k">Liczba leadow</div>
                <div class="v" data-process-impact="+62%">+62%</div>
                <div class="process-impact-line" data-process-line><i style="height:18%"></i><i style="height:22%"></i><i style="height:27%"></i><i style="height:33%"></i><i style="height:43%"></i><i style="height:56%"></i></div>
              </div>
              <div class="process-impact-card">
                <div class="k">Wspolczynnik konwersji</div>
                <div class="v" data-process-impact="+38%">+38%</div>
                <div class="process-impact-line" data-process-line><i style="height:16%"></i><i style="height:23%"></i><i style="height:29%"></i><i style="height:37%"></i><i style="height:44%"></i><i style="height:51%"></i></div>
              </div>
              <div class="process-impact-card">
                <div class="k">Koszt pozyskania leada</div>
                <div class="v" data-process-impact="-27%">-27%</div>
                <div class="process-impact-line" data-process-line><i style="height:52%"></i><i style="height:47%"></i><i style="height:39%"></i><i style="height:33%"></i><i style="height:27%"></i><i style="height:20%"></i></div>
              </div>
              <div class="process-impact-card">
                <div class="k">Wartosc transakcji</div>
                <div class="v" data-process-impact="+41%">+41%</div>
                <div class="process-impact-line" data-process-line><i style="height:18%"></i><i style="height:22%"></i><i style="height:28%"></i><i style="height:35%"></i><i style="height:41%"></i><i style="height:53%"></i></div>
              </div>
              <div class="process-impact-card">
                <div class="k">ROI kampanii</div>
                <div class="v" data-process-impact="+55%">+55%</div>
                <div class="process-impact-line" data-process-line><i style="height:15%"></i><i style="height:23%"></i><i style="height:31%"></i><i style="height:39%"></i><i style="height:47%"></i><i style="height:57%"></i></div>
              </div>
              <div class="process-impact-card">
                <div class="k">Przewidywalnosc sprzedazy</div>
                <div class="v" data-process-impact="+73%">+73%</div>
                <div class="process-impact-line" data-process-line><i style="height:14%"></i><i style="height:20%"></i><i style="height:29%"></i><i style="height:40%"></i><i style="height:54%"></i><i style="height:68%"></i></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section bg-soft section-border js-home-optional-section" id="wyniki">
      <div class="wrap">
        <div class="content">
          <div class="eyebrow reveal">Case study</div>
          <h2 class="h2 reveal d1">Portfolio realizacji z naciskiem na <span class="accent">wynik biznesowy</span></h2>
          <p class="body reveal d2" style="margin-top: 18px;">Strategia, kampanie i strona www zintegrowane w jeden system wzrostu leadow i sprzedazy.</p>
        </div>

        <div class="case-portfolio-visual reveal d1" id="case-portfolio-visual" style="margin-top: 34px;">
          <div class="case-portfolio-layout">
            <article class="case-panel">
              <div class="case-kicker">Case study</div>
              <div class="case-main-title">Producent komponentow dla przemyslu</div>
              <p class="case-desc">Kompleksowa wspolpraca: strategia marketingowa, kampanie paid ads i nowa strona internetowa pod konwersje.</p>

              <div class="case-meta-grid">
                <div class="case-meta-card"><strong>Branza</strong>Przemysl / B2B</div>
                <div class="case-meta-card"><strong>Model wspolpracy</strong>Strategia, kampanie, strona WWW</div>
                <div class="case-meta-card"><strong>Okres wspolpracy</strong>6 miesiecy</div>
              </div>

              <div class="case-score-grid">
                <div class="case-score-row"><span>Liczba leadow / miesiac</span><span>78</span><span class="after" data-case-after>162</span><span class="delta">+108%</span></div>
                <div class="case-score-row"><span>Koszt pozyskania leada (CPL)</span><span>142 zl</span><span class="after" data-case-after>76 zl</span><span class="delta">-46%</span></div>
                <div class="case-score-row"><span>Wspolczynnik konwersji strony</span><span>1,21%</span><span class="after" data-case-after>2,89%</span><span class="delta">+139%</span></div>
                <div class="case-score-row"><span>Przychody z kanalu / miesiac</span><span>87 000 zl</span><span class="after" data-case-after>186 000 zl</span><span class="delta">+114%</span></div>
              </div>

              <div class="case-done-grid">
                <div class="case-done-item"><strong>Strategia i audyt</strong>Analiza rynku i lejka.</div>
                <div class="case-done-item"><strong>Kampanie performance</strong>Meta Ads i Google Ads.</div>
                <div class="case-done-item"><strong>Nowa strona WWW</strong>Struktura pod konwersje.</div>
                <div class="case-done-item"><strong>Optymalizacja</strong>Testy i raportowanie.</div>
              </div>

              <div class="case-impact-grid">
                <div class="case-impact-list">
                  <span>Wiecej wartosciowych leadow</span>
                  <span>Nizszy koszt pozyskania klienta</span>
                  <span>Wyzsza konwersja strony</span>
                  <span>Przewidywalny wzrost przychodow</span>
                </div>
                <div class="case-impact-bars">
                  <div class="case-impact-bar-row"><span>Leady / miesiac</span><div class="case-impact-bar"><i data-case-impact-bar style="width:84%"></i></div><b>+108%</b></div>
                  <div class="case-impact-bar-row"><span>CPL</span><div class="case-impact-bar"><i data-case-impact-bar style="width:35%"></i></div><b>-46%</b></div>
                  <div class="case-impact-bar-row"><span>Konwersja strony</span><div class="case-impact-bar"><i data-case-impact-bar style="width:88%"></i></div><b>+139%</b></div>
                </div>
              </div>

              <div class="case-quote">"Wreszcie mamy partnera, ktory mysli o sprzedazy, a nie tylko o kliknieciach. Lepsze leady, nizsze koszty, przewidywalny pipeline."<strong>Dyrektor Handlowy</strong></div>
            </article>

            <article class="case-panel">
              <div class="case-kicker">Nowa strona WWW - struktura pod konwersje</div>
              <div class="portfolio-topbar"><span>INDUSTRIQ</span><span>Oferta | Branza | Kontakt</span><button class="portfolio-hero-cta">Zapytaj o oferte</button></div>
              <div class="portfolio-preview-shell">
                <div class="portfolio-screen">
                  <div class="portfolio-hero">
                    <div class="portfolio-hero-copy">
                      <strong>Komponenty dla przemyslu. Jakosc. Terminowosc. Zaufanie.</strong>
                      <span>Dostarczamy sprawdzone rozwiazania dla wymagajacych sektorow przemyslowych.</span>
                      <button class="portfolio-hero-cta">Zapytaj o oferte</button>
                    </div>
                    <div class="portfolio-hero-preview"></div>
                  </div>
                  <div class="portfolio-mini-grid">
                    <div class="portfolio-mini-card">Dlaczego warto z nami wspolpracowac?<div class="line" data-portfolio-line><i style="height:18%"></i><i style="height:26%"></i><i style="height:34%"></i><i style="height:45%"></i><i style="height:56%"></i></div></div>
                    <div class="portfolio-mini-card">Formularz leadowy<div class="line" data-portfolio-line><i style="height:15%"></i><i style="height:21%"></i><i style="height:30%"></i><i style="height:38%"></i><i style="height:48%"></i></div></div>
                  </div>
                </div>
                <div class="portfolio-mobile"><div class="portfolio-mobile-screen"></div></div>
              </div>

              <div class="case-kicker" style="margin-top:2px;">Przykladowe realizacje</div>
              <div class="portfolio-realizations">
                <div class="portfolio-card"><div class="portfolio-thumb"></div><div class="portfolio-card-title">Branza IT / Automatyzacja</div></div>
                <div class="portfolio-card"><div class="portfolio-thumb"></div><div class="portfolio-card-title">Branza Budownictwo</div></div>
                <div class="portfolio-card"><div class="portfolio-thumb"></div><div class="portfolio-card-title">Branza OZE</div></div>
              </div>
            </article>
          </div>

          <div class="portfolio-kpi-strip" data-portfolio-kpi-strip>
            <div class="portfolio-kpi-item is-active"><strong data-portfolio-kpi="+108%">+108%</strong><span>wiecej leadow</span></div>
            <div class="portfolio-kpi-item"><strong data-portfolio-kpi="-46%">-46%</strong><span>nizszy koszt leada</span></div>
            <div class="portfolio-kpi-item"><strong data-portfolio-kpi="+139%">+139%</strong><span>wyzsza konwersja</span></div>
            <div class="portfolio-kpi-item"><strong data-portfolio-kpi="+114%">+114%</strong><span>wzrost przychodow</span></div>
          </div>

          <div style="display:flex;justify-content:flex-end;">
            <a href="<?php echo esc_url(home_url("/#kontakt")); ?>" class="btn btn-primary">Kontakt strategiczny i bezplatna rozmowa</a>
          </div>
        </div>
      </div>
    </section>

    <section class="section section-border js-home-optional-section" id="dla-kogo">
      <div class="wrap">
        <div class="content">
          <div class="eyebrow reveal"><?php echo esc_html((string) ($fit_section["eyebrow"] ?? "Dla kogo")); ?></div>
          <h2 class="h2 reveal d1"><?php echo esc_html((string) ($fit_section["title"] ?? "")); ?></h2>
        </div>

        <div class="fit-grid" style="margin-top: 40px;">
          <div class="fit-card yes reveal">
            <div class="fit-label"><?php echo esc_html((string) ($fit_section["good_label"] ?? "Dobry fit, jesli:")); ?></div>
            <div class="fit-list">
              <?php $fit_good_items = isset($fit_section["good_items"]) && is_array($fit_section["good_items"]) ? $fit_section["good_items"] : []; ?>
              <?php foreach ($fit_good_items as $fit_good_item) : ?>
                <?php $fit_good_item = trim((string) $fit_good_item); ?>
                <?php if ($fit_good_item === "") : ?>
                  <?php continue; ?>
                <?php endif; ?>
                <div class="fit-item"><span class="fit-icon">✓</span><?php echo esc_html($fit_good_item); ?></div>
              <?php endforeach; ?>
            </div>
            <a href="<?php echo esc_url(home_url((string) ($fit_section["good_cta_url"] ?? "/#kontakt"))); ?>" class="btn btn-primary"><?php echo esc_html((string) ($fit_section["good_cta_label"] ?? "Umow bezplatna rozmowe")); ?> →</a>
          </div>

          <div class="fit-card no reveal d1">
            <div class="fit-label"><?php echo esc_html((string) ($fit_section["bad_label"] ?? "Mniejszy fit, jesli:")); ?></div>
            <div class="fit-list">
              <?php $fit_bad_items = isset($fit_section["bad_items"]) && is_array($fit_section["bad_items"]) ? $fit_section["bad_items"] : []; ?>
              <?php foreach ($fit_bad_items as $fit_bad_item) : ?>
                <?php $fit_bad_item = trim((string) $fit_bad_item); ?>
                <?php if ($fit_bad_item === "") : ?>
                  <?php continue; ?>
                <?php endif; ?>
                <div class="fit-item"><span class="fit-icon">—</span><?php echo esc_html($fit_bad_item); ?></div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section bg-soft section-border js-home-optional-section" id="faq">
      <div class="wrap">
        <div class="content">
          <div class="eyebrow reveal">FAQ</div>
          <h2 class="h2 reveal d1">Najczęstsze <span class="accent">pytania</span></h2>
        </div>

        <div class="faq" style="margin-top: 40px;">
          <?php if (!empty($faq_items)) : ?>
            <?php foreach ($faq_items as $faq_index => $faq_item) : ?>
              <?php
              $question = trim((string) ($faq_item["question"] ?? ""));
              $answer = trim((string) ($faq_item["answer"] ?? ""));
              if ($question === "" || $answer === "") {
                  continue;
              }
              $delay_class = "";
              if ($faq_index % 4 === 1) {
                  $delay_class = " d1";
              } elseif ($faq_index % 4 === 2) {
                  $delay_class = " d2";
              } elseif ($faq_index % 4 === 3) {
                  $delay_class = " d3";
              }
              ?>
              <div class="faq-item reveal<?php echo esc_attr($delay_class); ?>">
                <button class="faq-q" type="button">
                  <span><?php echo esc_html($question); ?></span>
                  <span class="faq-icon">+</span>
                </button>
                <div class="faq-a"><?php echo esc_html($answer); ?></div>
              </div>
            <?php endforeach; ?>
          <?php else : ?>
            <div class="faq-item reveal">
              <div class="faq-a">Brak danych FAQ. Uzupelnij konfiguracje w panelu: Wyglad -> Konfiguracja dynamiczna.</div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <section class="section" id="kontakt">
      <div class="wrap">
        <?php $ups_form_status = isset($_GET["ups_lead_status"]) ? sanitize_text_field(wp_unslash($_GET["ups_lead_status"])) : ""; ?>
        <article class="contact-intro-bar reveal visible">
          <div>
            <div class="eyebrow">Bezplatna konsultacja wstepna</div>
            <h2 class="contact-strategy-title">Zacznijmy od rozmowy. <span class="accent">Skupmy sie na rozwiazaniach.</span></h2>
            <p class="contact-strategy-lead">30-45 minut rozmowy, ktora pomoze zobaczyc, co realnie ogranicza wzrost Twojego biznesu.</p>
          </div>
        </article>

        <div class="contact-strategy-layout reveal visible" id="contact-strategy-visual">
          <article class="contact-strategy-info">
            <div class="contact-strategy-flow" data-contact-flow>
              <div class="contact-flow-step is-active"><i>🔎</i><strong>Diagnoza sytuacji</strong><span>Rozumiemy cele i wyzwania.</span></div>
              <div class="contact-flow-step"><i>📊</i><strong>Identyfikacja barier</strong><span>Wskazujemy, co ogranicza wzrost.</span></div>
              <div class="contact-flow-step"><i>🎯</i><strong>Kierunki dzialan</strong><span>Dobieramy konkretne rekomendacje.</span></div>
              <div class="contact-flow-step"><i>📈</i><strong>Plan kolejnych krokow</strong><span>Ustalamy priorytety i harmonogram.</span></div>
            </div>

            <div class="contact-strategy-panels">
              <div class="contact-panel">
                <div class="contact-panel-title">Potencjalne obszary</div>
                <div class="contact-list">
                  <span>Jakosc i ilosc leadow</span>
                  <span>Skutecznosc kampanii</span>
                  <span>Koszt pozyskania klienta</span>
                  <span>Konwersja strony www</span>
                  <span>Proces sprzedazy</span>
                  <span>Przewidywalnosc wynikow</span>
                </div>
              </div>
              <div class="contact-panel">
                <div class="contact-panel-title">Na co zwracamy uwage</div>
                <div class="contact-metric-row"><span>Leady (jakosc)</span><div class="contact-metric-line" data-contact-line><i style="height:18%"></i><i style="height:23%"></i><i style="height:30%"></i><i style="height:39%"></i><i style="height:47%"></i></div></div>
                <div class="contact-metric-row"><span>Konwersja strony</span><div class="contact-metric-line" data-contact-line><i style="height:16%"></i><i style="height:24%"></i><i style="height:31%"></i><i style="height:43%"></i><i style="height:54%"></i></div></div>
                <div class="contact-metric-row"><span>Koszt pozyskania (CPL)</span><div class="contact-metric-line" data-contact-line><i style="height:52%"></i><i style="height:43%"></i><i style="height:37%"></i><i style="height:30%"></i><i style="height:23%"></i></div></div>
                <div class="contact-metric-row"><span>Przychody</span><div class="contact-metric-line" data-contact-line><i style="height:18%"></i><i style="height:25%"></i><i style="height:33%"></i><i style="height:45%"></i><i style="height:58%"></i></div></div>
              </div>
              <div class="contact-panel">
                <div class="contact-panel-title">Przejrzyste podejscie</div>
                <div class="contact-list">
                  <span>Bez zobowiazan</span>
                  <span>Szczera ocena sytuacji</span>
                  <span>Konkret, nie ogolniki</span>
                  <span>Skupienie na wynikach</span>
                </div>
                <div class="contact-mini-note">Dobra strategia zaczyna sie od wlasciwych pytan.</div>
              </div>
            </div>

            <div class="contact-proofs" data-contact-proofs>
              <div class="contact-proof-item is-active"><i></i><span>Poufnosc rozmowy gwarantowana.</span></div>
              <div class="contact-proof-item"><i></i><span>Doswiadczenie w B2B i sprzedazy.</span></div>
            </div>
          </article>

          <div class="contact-strategy-form">
            <div class="form-head" style="margin-bottom:2px;">
              <h3 class="h3">Umow bezplatna konsultacje</h3>
              <p class="body" style="margin-top: 8px;">Wybierz termin, ktory Ci odpowiada i opisz wyzwanie.</p>
            </div>

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
                <div class="field">
                  <label for="fname">Imie i nazwisko *</label>
                  <input class="input" type="text" id="fname" name="lead_name" placeholder="Twoje imie i nazwisko" required />
                  <span class="field-error" id="fname-err">Podaj imie i nazwisko</span>
                </div>

                <div class="field">
                  <label for="femail">E-mail *</label>
                  <input class="input" type="email" id="femail" name="lead_email" placeholder="Twoj adres e-mail" required />
                  <span class="field-error" id="femail-err">Podaj poprawny adres e-mail</span>
                </div>

                <div class="field">
                  <label for="fcompany">Nazwa firmy</label>
                  <input class="input" type="text" id="fcompany" name="lead_company" placeholder="Nazwa Twojej firmy" />
                </div>

                <div class="field">
                  <label for="fservice">Czego dotyczy rozmowa?</label>
                  <select class="select" id="fservice" name="lead_service">
                    <option value="">Wybierz obszar</option>
                    <?php foreach ($contact_service_options as $service_option) : ?>
                      <?php $service_option = trim((string) $service_option); ?>
                      <?php if ($service_option === "") : ?>
                        <?php continue; ?>
                      <?php endif; ?>
                      <option><?php echo esc_html($service_option); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="field full">
                  <label for="fmsg">Krotko opisz swoj cel lub wyzwanie *</label>
                  <textarea class="textarea" id="fmsg" name="lead_message" placeholder="Napisz kilka slow o swoim biznesie i oczekiwaniach..." required></textarea>
                  <span class="field-error" id="fmsg-err">Opisz w kilku slowach swoja sytuacje</span>
                </div>

                <div class="field">
                  <label for="fphone">Telefon (opcjonalnie)</label>
                  <input class="input" type="tel" id="fphone" name="lead_phone" placeholder="+48 575 522 595" autocomplete="tel" />
                </div>

                <div class="field">
                  <label for="fmeeting">Dostepne terminy</label>
                  <select class="select" id="fmeeting" name="lead_meeting_time">
                    <option value="">Wybierz dogodny termin</option>
                    <option>Pon-Pt, 9:00-12:00</option>
                    <option>Pon-Pt, 12:00-16:00</option>
                    <option>Pon-Pt, 16:00-19:00</option>
                  </select>
                </div>

                <div class="field full">
                  <label style="display:flex;gap:8px;align-items:flex-start;">
                    <input type="checkbox" name="lead_consent" value="1" required style="margin-top:3px;" />
                    <span>Wyrazam zgode na kontakt w sprawie mojego zapytania.</span>
                  </label>
                </div>
              </div>

              <button type="submit" class="btn btn-primary submit" id="submit-btn">Umow bezplatna konsultacje</button>
              <p class="form-note">Bez spamu. Odpowiadam osobiscie. Wolisz zadzwonic? <a href="<?php echo esc_url("tel:" . preg_replace("/\s+/", "", $contact_phone)); ?>"><?php echo esc_html($contact_phone); ?></a></p>
            </form>
          </div>
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

