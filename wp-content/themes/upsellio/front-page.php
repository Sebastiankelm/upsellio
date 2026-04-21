<?php
if (!defined("ABSPATH")) {
    exit;
}

$front_page_sections = function_exists("upsellio_get_front_page_content_config")
    ? upsellio_get_front_page_content_config()
    : [];
$front_page_issues = function_exists("upsellio_get_front_page_data_issues")
    ? upsellio_get_front_page_data_issues()
    : [];
$front_nav_links = isset($front_page_sections["nav_links"]) && is_array($front_page_sections["nav_links"])
    ? $front_page_sections["nav_links"]
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
$contact_phone = trim((string) ($front_page_sections["contact_phone"] ?? ""));
$contact_email = trim((string) ($front_page_sections["contact_email"] ?? ""));

$seo_title = trim((string) ($seo_section["title"] ?? ""));
$seo_description = trim((string) ($seo_section["description"] ?? ""));
$seo_og_title = trim((string) ($seo_section["og_title"] ?? ""));
$seo_og_description = trim((string) ($seo_section["og_description"] ?? ""));
$seo_og_type = trim((string) ($seo_section["og_type"] ?? "website"));
$seo_og_url = trim((string) ($seo_section["og_url"] ?? "/"));
$seo_twitter_card = trim((string) ($seo_section["twitter_card"] ?? "summary_large_image"));
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
  <meta name="twitter:card" content="<?php echo esc_attr($seo_twitter_card !== "" ? $seo_twitter_card : "summary_large_image"); ?>" />

  <script type="application/ld+json"><?php echo wp_json_encode($seo_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;700&display=swap"
    rel="stylesheet"
  />
  <link rel="stylesheet" href="<?php echo esc_url(get_template_directory_uri() . "/assets/css/upsellio.css?ver=" . rawurlencode($upsellio_css_version)); ?>" />

  <?php if (false) : ?><style>
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
      height: 72px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: var(--sp-3);
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .brand-mark {
      width: 34px;
      height: 34px;
      border-radius: 12px;
      background: linear-gradient(135deg, var(--teal), var(--teal-dark));
      display: grid;
      place-items: center;
      color: #fff;
      font-family: var(--font-display);
      font-weight: 800;
      font-size: 15px;
      box-shadow: var(--shadow-sm);
    }

    .brand-text {
      display: flex;
      flex-direction: column;
      line-height: 1.05;
    }

    .brand-name {
      font-family: var(--font-display);
      font-weight: 800;
      font-size: 18px;
      letter-spacing: -0.5px;
    }

    .brand-sub {
      font-size: 11px;
      color: var(--text-3);
      margin-top: 3px;
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
      max-height: calc(100vh - 72px);
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
      align-items: center;
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
      background: linear-gradient(180deg, var(--bg-soft), var(--surface));
      border: 1px solid var(--border);
      border-radius: var(--r-xl);
      padding: var(--sp-5);
      box-shadow: var(--shadow-md);
    }

    .hero-aside-label {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 1.6px;
      text-transform: uppercase;
      color: var(--text-3);
      margin-bottom: var(--sp-3);
    }

    .hero-stat + .hero-stat {
      margin-top: var(--sp-3);
      padding-top: var(--sp-3);
      border-top: 1px solid var(--border);
    }

    .hero-stat-num {
      font-family: var(--font-display);
      font-size: 30px;
      font-weight: 700;
      line-height: 1;
      color: var(--teal);
    }

    .hero-stat-text {
      margin-top: 6px;
      font-size: 13px;
      color: var(--text-2);
      line-height: 1.5;
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
      transition: opacity 0.65s cubic-bezier(.16,1,.3,1), transform 0.65s cubic-bezier(.16,1,.3,1);
    }

    .reveal.visible {
      opacity: 1;
      transform: translateY(0);
    }

    .d1 { transition-delay: 0.08s; }
    .d2 { transition-delay: 0.16s; }
    .d3 { transition-delay: 0.24s; }

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
      display: none;
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
        grid-template-columns: minmax(0, 1fr) 420px;
      }

      .split {
        grid-template-columns: 320px minmax(0, 1fr);
      }

      .hero-aside {
        display: block;
      }

      .problem-grid,
      .service-grid,
      .stats-grid,
      .fit-grid,
      .form-grid {
        grid-template-columns: 1fr 1fr;
      }

      .cta-band,
      .footer-inner {
        flex-direction: row;
      }

      .footer-links {
        align-items: flex-end;
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
        <div class="brand-mark">U</div>
        <div class="brand-text">
          <div class="brand-name">Upsellio</div>
          <div class="brand-sub">by Sebastian Kelm</div>
        </div>
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
          <li><a href="<?php echo esc_url(home_url($nav_url)); ?>"><?php echo esc_html($nav_title); ?></a></li>
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
          <a href="<?php echo esc_url(home_url($nav_url)); ?>"><?php echo esc_html($nav_title); ?></a>
        <?php endforeach; ?>
        <a href="<?php echo esc_url(home_url("/#kontakt")); ?>">Bezpłatna rozmowa →</a>
      </div>
    </div>
  </header>

  <main>
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

        <aside class="hero-aside">
          <div class="hero-aside-label"><?php echo esc_html((string) ($hero_section["aside_label"] ?? "")); ?></div>
          <?php $hero_aside_stats = isset($hero_section["aside_stats"]) && is_array($hero_section["aside_stats"]) ? $hero_section["aside_stats"] : []; ?>
          <?php foreach ($hero_aside_stats as $hero_aside_stat) : ?>
            <?php
            $hero_stat_number = trim((string) ($hero_aside_stat["number"] ?? ""));
            $hero_stat_text = trim((string) ($hero_aside_stat["text"] ?? ""));
            if ($hero_stat_number === "" || $hero_stat_text === "") {
                continue;
            }
            ?>
            <div class="hero-stat">
              <div class="hero-stat-num"><?php echo esc_html($hero_stat_number); ?></div>
              <div class="hero-stat-text"><?php echo esc_html($hero_stat_text); ?></div>
            </div>
          <?php endforeach; ?>
        </aside>
      </div>
    </section>

    <section class="section section-border" id="dlaczego">
      <div class="wrap split">
        <div class="content">
          <div class="eyebrow reveal"><?php echo esc_html((string) ($why_section["eyebrow"] ?? "Dlaczego to dziala")); ?></div>
          <h2 class="h2 reveal d1"><?php echo esc_html((string) ($why_section["title"] ?? "")); ?></h2>
          <p class="body reveal d2" style="margin-top: 18px;"><?php echo esc_html((string) ($why_section["lead"] ?? "")); ?></p>
        </div>

        <div class="stack-cards">
          <?php $why_features = isset($why_section["features"]) && is_array($why_section["features"]) ? $why_section["features"] : []; ?>
          <?php if (!empty($why_features)) : ?>
            <?php foreach ($why_features as $why_feature_index => $why_feature) : ?>
              <?php
              $feature_title = trim((string) ($why_feature["title"] ?? ""));
              $feature_desc = trim((string) ($why_feature["description"] ?? ""));
              if ($feature_title === "" || $feature_desc === "") {
                  continue;
              }
              $feature_delay_class = "";
              if ($why_feature_index % 3 === 1) {
                  $feature_delay_class = " d1";
              } elseif ($why_feature_index % 3 === 2) {
                  $feature_delay_class = " d2";
              }
              ?>
              <div class="feature-row reveal<?php echo esc_attr($feature_delay_class); ?>">
                <div class="feature-icon">
                  <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <circle cx="9" cy="9" r="8" stroke="currentColor" stroke-width="1.4"/>
                    <path d="M5.2 9.1l2.2 2.2 5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </div>
                <div>
                  <div class="feature-title"><?php echo esc_html($feature_title); ?></div>
                  <div class="feature-desc"><?php echo esc_html($feature_desc); ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php elseif (current_user_can("manage_options")) : ?>
            <div class="feature-row reveal">
              <div>
                <div class="feature-desc">Brak danych sekcji "why.features" w konfiguracji.</div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <section class="section bg-soft section-border" id="problem">
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
          <div>
            <div class="service-top">
              <div class="h3"><?php echo esc_html((string) ($services_primary["title"] ?? "")); ?></div>
              <span class="badge badge-green"><?php echo esc_html((string) ($services_primary["badge"] ?? "Glowna usluga")); ?></span>
            </div>
            <p class="body"><?php echo esc_html((string) ($services_primary["description"] ?? "")); ?></p>
          </div>

          <div>
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

    <section class="section section-border" id="jak-dzialam">
      <div class="wrap">
        <div class="content">
          <div class="eyebrow reveal"><?php echo esc_html((string) ($process_section["eyebrow"] ?? "Jak dzialam")); ?></div>
          <h2 class="h2 reveal d1"><?php echo esc_html((string) ($process_section["title"] ?? "")); ?></h2>
          <p class="body reveal d2" style="margin-top: 18px;"><?php echo esc_html((string) ($process_section["lead"] ?? "")); ?></p>
        </div>

        <div class="steps" style="margin-top: 40px;">
          <?php $process_steps = isset($process_section["steps"]) && is_array($process_section["steps"]) ? $process_section["steps"] : []; ?>
          <?php foreach ($process_steps as $process_step_index => $process_step) : ?>
            <?php
            $process_step_number = trim((string) ($process_step["number"] ?? ""));
            $process_step_title = trim((string) ($process_step["title"] ?? ""));
            $process_step_description = trim((string) ($process_step["description"] ?? ""));
            if ($process_step_number === "" || $process_step_title === "" || $process_step_description === "") {
                continue;
            }
            $process_delay_class = "";
            if ($process_step_index % 4 === 1) {
                $process_delay_class = " d1";
            } elseif ($process_step_index % 4 === 2) {
                $process_delay_class = " d2";
            } elseif ($process_step_index % 4 === 3) {
                $process_delay_class = " d3";
            }
            ?>
            <div class="step reveal<?php echo esc_attr($process_delay_class); ?>">
              <div class="step-num"><?php echo esc_html($process_step_number); ?></div>
              <div>
                <div class="step-title"><?php echo esc_html($process_step_title); ?></div>
                <div class="step-desc"><?php echo esc_html($process_step_description); ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <section class="section bg-soft section-border" id="wyniki">
      <div class="wrap">
        <div class="content">
          <div class="eyebrow reveal"><?php echo esc_html((string) ($results_section["eyebrow"] ?? "Doswiadczenie i wyniki")); ?></div>
          <h2 class="h2 reveal d1"><?php echo esc_html((string) ($results_section["title"] ?? "")); ?></h2>
          <p class="body reveal d2" style="margin-top: 18px;"><?php echo esc_html((string) ($results_section["lead"] ?? "")); ?></p>
        </div>

        <div class="stats-grid">
          <?php $results_stats = isset($results_section["stats"]) && is_array($results_section["stats"]) ? $results_section["stats"] : []; ?>
          <?php foreach ($results_stats as $results_stat_index => $results_stat) : ?>
            <?php
            $results_stat_number = trim((string) ($results_stat["number"] ?? ""));
            $results_stat_text = trim((string) ($results_stat["text"] ?? ""));
            if ($results_stat_number === "" || $results_stat_text === "") {
                continue;
            }
            $results_stat_delay_class = "";
            if ($results_stat_index % 4 === 1) {
                $results_stat_delay_class = " d1";
            } elseif ($results_stat_index % 4 === 2) {
                $results_stat_delay_class = " d2";
            } elseif ($results_stat_index % 4 === 3) {
                $results_stat_delay_class = " d3";
            }
            ?>
            <div class="stat-card reveal<?php echo esc_attr($results_stat_delay_class); ?>">
              <div class="stat-num"><?php echo esc_html($results_stat_number); ?></div>
              <div class="stat-text"><?php echo esc_html($results_stat_text); ?></div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="cases">
          <?php $results_cases = isset($results_section["cases"]) && is_array($results_section["cases"]) ? $results_section["cases"] : []; ?>
          <?php foreach ($results_cases as $results_case_index => $results_case) : ?>
            <?php
            $results_case_tag = trim((string) ($results_case["tag"] ?? ""));
            $results_case_title = trim((string) ($results_case["title"] ?? ""));
            $results_case_body = trim((string) ($results_case["body"] ?? ""));
            $results_case_result = trim((string) ($results_case["result"] ?? ""));
            if ($results_case_tag === "" || $results_case_title === "" || $results_case_body === "" || $results_case_result === "") {
                continue;
            }
            $results_case_delay_class = $results_case_index % 3 === 1 ? " d1" : ($results_case_index % 3 === 2 ? " d2" : "");
            ?>
            <div class="case reveal<?php echo esc_attr($results_case_delay_class); ?>">
              <div class="case-tag"><?php echo esc_html($results_case_tag); ?></div>
              <div class="case-title"><?php echo esc_html($results_case_title); ?></div>
              <div class="case-body"><?php echo esc_html($results_case_body); ?></div>
              <div class="case-result"><?php echo esc_html($results_case_result); ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <section class="section section-border" id="dla-kogo">
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

    <section class="section bg-soft section-border" id="faq">
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
        <div class="form-shell reveal visible">
          <div class="form-head">
            <div class="eyebrow">Kontakt</div>
            <h2 class="h2">Umów <span class="accent">bezpłatną rozmowę</span></h2>
            <p class="body" style="margin-top: 18px;">Napisz kilka słów o firmie i tym, co chcesz poprawić. Odpiszę do końca dnia roboczego — osobiście, nie bot.</p>
          </div>

          <?php $ups_form_status = isset($_GET["ups_lead_status"]) ? sanitize_text_field(wp_unslash($_GET["ups_lead_status"])) : ""; ?>
          <?php if ($ups_form_status === "success") : ?>
            <div style="margin-bottom:12px;padding:10px 12px;border:1px solid #c3eddd;background:#e8f8f2;border-radius:10px;color:#085041;font-size:13px;">Dziękuję! Wiadomość została zapisana i odezwę się możliwie szybko.</div>
          <?php elseif ($ups_form_status === "error") : ?>
            <div style="margin-bottom:12px;padding:10px 12px;border:1px solid #edcccc;background:#fff2f2;border-radius:10px;color:#b13a3a;font-size:13px;">Nie udało się wysłać formularza. Sprawdź pola i spróbuj ponownie.</div>
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
                <label for="fname">Imię i nazwa firmy *</label>
                <input class="input" type="text" id="fname" name="lead_name" placeholder="np. Marek Kowalski, firma XYZ" required />
                <span class="field-error" id="fname-err">Podaj imię i nazwę firmy</span>
              </div>

              <div class="field">
                <label for="femail">E-mail służbowy *</label>
                <input class="input" type="email" id="femail" name="lead_email" placeholder="adres@twojafirma.pl" required />
                <span class="field-error" id="femail-err">Podaj poprawny adres e-mail</span>
              </div>

              <div class="field">
                <label for="fphone">Telefon (opcjonalnie)</label>
                <input class="input" type="tel" id="fphone" name="lead_phone" placeholder="+48 600 000 000" autocomplete="tel" />
              </div>

              <div class="field">
                <label for="fservice">Czego szukasz?</label>
                <select class="select" id="fservice" name="lead_service">
                  <option value="">— wybierz —</option>
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
                <label for="fmsg">Co chcesz poprawić lub osiągnąć? *</label>
                <textarea class="textarea" id="fmsg" name="lead_message" placeholder="np. reklamy nie przynoszą wartościowych klientów, chcę nowy sklep nastawiony na konwersję, chcę poukładać sprzedaż..." required></textarea>
                <span class="field-error" id="fmsg-err">Opisz w kilku słowach swoją sytuację</span>
              </div>
              <div class="field full">
                <label style="display:flex;gap:8px;align-items:flex-start;">
                  <input type="checkbox" name="lead_consent" value="1" required style="margin-top:3px;" />
                  <span>Wyrażam zgodę na kontakt w sprawie mojego zapytania.</span>
                </label>
              </div>
            </div>

            <button type="submit" class="btn btn-primary submit" id="submit-btn">Wyślij i umów rozmowę →</button>
            <p class="form-note">Dane przekazane w formularzu są poufne i służą wyłącznie do kontaktu.</p>
          </form>

          <div class="form-alt">
            <div class="form-alt-item">
              <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                <path d="M7 1a6 6 0 100 12A6 6 0 007 1zM7 4v3.5l2 2" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" fill="none"/>
              </svg>
              Odpowiem do końca dnia roboczego
            </div>
            <div class="form-alt-item">
              <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                <path d="M2 4.5C2 3.12 3.12 2 4.5 2h5C10.88 2 12 3.12 12 4.5v5C12 10.88 10.88 12 9.5 12h-5C3.12 12 2 10.88 2 9.5v-5z" stroke="currentColor" stroke-width="1.2" fill="none"/>
                <path d="M5 7l1.5 1.5L9 5.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              Bez ciśnienia — sprawdzimy czy możemy razem działać
            </div>
            <div class="form-alt-item">
              Wolisz zadzwonić?
              <?php if ($contact_phone !== "") : ?>
                <a href="<?php echo esc_url("tel:" . preg_replace("/\s+/", "", $contact_phone)); ?>"><?php echo esc_html($contact_phone); ?></a>
              <?php else : ?>
                <span>Brak numeru telefonu w konfiguracji.</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer class="footer">
    <div class="wrap">
      <div class="footer-inner">
        <div class="footer-brand">
          <div class="brand">
            <div class="brand-mark">U</div>
            <div class="brand-text">
              <div class="brand-name">Upsellio</div>
              <div class="brand-sub">by Sebastian Kelm</div>
            </div>
          </div>
          <div class="footer-sub">
            Upsellio — Sebastian Kelm<br />
            Marketing internetowy i strony WWW dla firm B2B
          </div>
        </div>

        <div class="footer-col">
          <div class="footer-col-label">Usługi</div>
          <a href="#uslugi">Meta Ads i Google Ads</a>
          <a href="#uslugi">Strony internetowe</a>
          <a href="#uslugi">Sklepy internetowe</a>
          <a href="#uslugi">Doradztwo sprzedażowe</a>
        </div>

        <div class="footer-col">
          <div class="footer-col-label">Nawigacja</div>
          <a href="#jak-dzialam">Jak działam</a>
          <a href="#wyniki">Wyniki</a>
          <a href="#faq">FAQ</a>
          <a href="<?php echo esc_url(home_url("/blog")); ?>">Blog</a>
        </div>

        <div class="footer-col footer-links">
          <div class="footer-col-label">Kontakt</div>
          <?php if ($contact_email !== "") : ?>
            <a href="<?php echo esc_url("mailto:" . $contact_email); ?>"><?php echo esc_html($contact_email); ?></a>
          <?php endif; ?>
          <?php if ($contact_phone !== "") : ?>
            <a href="<?php echo esc_url("tel:" . preg_replace("/\s+/", "", $contact_phone)); ?>"><?php echo esc_html($contact_phone); ?></a>
          <?php endif; ?>
          <a href="https://linkedin.com/in/sebastiankelm" target="_blank" rel="noopener">LinkedIn</a>
          <a href="#kontakt">Bezpłatna rozmowa</a>
        </div>
      </div>

      <?php echo upsellio_get_footer_popular_definitions_html(); ?>
      <?php echo upsellio_get_footer_city_links_html(); ?>
      <div class="footer-copy">© 2025 Upsellio / Sebastian Kelm. Wszelkie prawa zastrzeżone. · <a href="<?php echo esc_url(home_url("/polityka-prywatnosci")); ?>" style="color:inherit;">Polityka prywatności</a></div>
    </div>
  </footer>

  <button class="scroll-top" id="scroll-top" aria-label="Wróć na górę">↑</button>
  <?php wp_footer(); ?>
</body>
</html>

