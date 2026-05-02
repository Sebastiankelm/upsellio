<?php
if (!defined("ABSPATH")) {
    exit;
}
$upsellio_load_public_tracking = !function_exists("upsellio_should_load_public_tracking_tags") || upsellio_should_load_public_tracking_tags();
$primary_navigation_links = function_exists("upsellio_get_primary_navigation_links") ? upsellio_get_primary_navigation_links() : [];
$brand_logo_assets = function_exists("upsellio_get_brand_logo_assets") ? upsellio_get_brand_logo_assets() : [];
$brand_logo_url = (string) ($brand_logo_assets["png"] ?? "");
$brand_logo_webp_320_url = (string) ($brand_logo_assets["webp_320"] ?? "");
$brand_logo_webp_640_url = (string) ($brand_logo_assets["webp_640"] ?? "");
$primary_navigation_top = [];
$primary_navigation_children = [];
foreach ((array) $primary_navigation_links as $nav_link) {
    $link_id = (int) ($nav_link["id"] ?? 0);
    $parent_id = (int) ($nav_link["parent"] ?? 0);
    if ($parent_id > 0) {
        if (!isset($primary_navigation_children[$parent_id])) {
            $primary_navigation_children[$parent_id] = [];
        }
        $primary_navigation_children[$parent_id][] = $nav_link;
        continue;
    }
    if ($link_id > 0 && !isset($primary_navigation_children[$link_id])) {
        $primary_navigation_children[$link_id] = [];
    }
    $primary_navigation_top[] = $nav_link;
}
if (function_exists("upsellio_register_template_seo_head")) {
    upsellio_register_template_seo_head("audyt_meta");
}
$audit_meta_faq_schema = [
    [
        "question" => "Czy ten audyt naprawde jest darmowy?",
        "answer" => "Tak. To darmowa analiza wstepna, ktora daje jasnosc, co blokuje wyniki i gdzie jest najwiekszy potencjal poprawy.",
    ],
    [
        "question" => "Czy musze od razu dawac pelen dostep do konta reklamowego?",
        "answer" => "Nie. Na start wystarczy formularz i opis sytuacji. Informacje o dostepach sa potrzebne dopiero na dalszym etapie.",
    ],
    [
        "question" => "Dla kogo ten audyt ma najwiekszy sens?",
        "answer" => "Dla firm, ktore prowadza kampanie Meta Ads lub planuja zwiekszyc budzet i chca poprawic jakosc leadow oraz skutecznosc reklam.",
    ],
    [
        "question" => "Czy po audycie od razu pojawi sie oferta wspolpracy?",
        "answer" => "Nie. Najpierw otrzymujesz wnioski i rekomendacje. Decyzja o dalszych krokach nalezy do Ciebie.",
    ],
];
$audit_meta_faq_schema_payload = [];
foreach ($audit_meta_faq_schema as $faq_item) {
    $question = trim((string) ($faq_item["question"] ?? ""));
    $answer = trim((string) ($faq_item["answer"] ?? ""));
    if ($question === "" || $answer === "") {
        continue;
    }
    $audit_meta_faq_schema_payload[] = [
        "@type" => "Question",
        "name" => $question,
        "acceptedAnswer" => [
            "@type" => "Answer",
            "text" => $answer,
        ],
    ];
}
add_action("wp_head", static function () use ($audit_meta_faq_schema_payload) {
    if (empty($audit_meta_faq_schema_payload)) {
        return;
    }
    echo '<script type="application/ld+json">' . wp_json_encode([
        "@context" => "https://schema.org",
        "@type" => "FAQPage",
        "mainEntity" => $audit_meta_faq_schema_payload,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
    echo '<script type="application/ld+json">' . wp_json_encode([
        "@context" => "https://schema.org",
        "@type" => "BreadcrumbList",
        "itemListElement" => [
            [
                "@type" => "ListItem",
                "position" => 1,
                "name" => "Strona glowna",
                "item" => home_url("/"),
            ],
            [
                "@type" => "ListItem",
                "position" => 2,
                "name" => "Audyt Meta Ads",
                "item" => home_url("/audyt-meta/"),
            ],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
}, 2);
$upsellio_css_path = get_template_directory() . "/assets/css/upsellio.css";
$upsellio_css_version = file_exists($upsellio_css_path) ? (string) filemtime($upsellio_css_path) : "1.0.0";
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <?php if ($upsellio_load_public_tracking) : ?>
  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-R37SMGVBNC"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-R37SMGVBNC');
  </script>
  <?php endif; ?>
  <meta charset="<?php bloginfo("charset"); ?>" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <?php if ($upsellio_load_public_tracking) : ?>
  <!-- Google Tag Manager -->
  <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-KM9J5XC2');</script>
  <!-- End Google Tag Manager -->
  <?php endif; ?>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'" />
  <noscript><link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet" /></noscript>
  <link rel="stylesheet" href="<?php echo esc_url(get_template_directory_uri() . "/assets/css/upsellio.css?ver=" . rawurlencode($upsellio_css_version)); ?>" />

  <?php if (false) : ?><style>
    :root {
      --bg: #ffffff;
      --bg-soft: #f1f5f9;
      --bg-muted: #f1f1ee;
      --surface: #ffffff;

      --text: #071426;
      --text-2: #334155;
      --text-3: #64748b;

      --border: #e6e6e1;
      --border-strong: #c9c9c3;

      --teal: #0d9488;
      --teal-hover: #0f766e;
      --teal-dark: #0f766e;
      --teal-soft: #ecfeff;
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

    * { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }
    body {
      font-family: var(--font-body);
      background: var(--bg);
      color: var(--text);
      line-height: 1.65;
      -webkit-font-smoothing: antialiased;
      text-size-adjust: 100%;
      overflow-x: hidden;
    }
    body.is-mobile-menu-open { overflow: hidden; }
    img { display: block; max-width: 100%; }
    a { color: inherit; text-decoration: none; }
    button, input, textarea, select { font: inherit; }

    .wrap {
      width: min(var(--container), calc(100% - 48px));
      margin: 0 auto;
    }

    .content { width: min(var(--content), 100%); }
    .section { padding: var(--sp-10) 0; }
    .section-sm { padding: var(--sp-7) 0; }
    .section-border { border-bottom: 1px solid var(--border); }
    .bg-soft { background: var(--bg-soft); }

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
    .accent { color: var(--teal); }
    .muted { color: var(--text-3); }

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
      box-shadow: 0 0 0 0 rgba(20, 184, 166, 0.38);
      animation: pulse 2.8s ease 2.5s infinite;
    }
    .btn-primary:hover {
      background: var(--teal-hover);
      transform: translateY(-2px);
      box-shadow: 0 8px 22px rgba(13, 148, 136, 0.28);
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
      0% { box-shadow: 0 0 0 0 rgba(20,184,166,0.36); }
      70% { box-shadow: 0 0 0 12px rgba(20,184,166,0); }
      100% { box-shadow: 0 0 0 0 rgba(20,184,166,0); }
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
    .nav-actions { display: flex; align-items: center; gap: var(--sp-2); }
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
    .hamburger.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
    .hamburger.open span:nth-child(2) { opacity: 0; }
    .hamburger.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

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
      background:
        radial-gradient(circle at top right, rgba(20,184,166,0.12), transparent 32%),
        var(--bg);
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
      grid-template-columns: minmax(0, 1fr) 380px;
      gap: var(--sp-10);
      align-items: center;
      padding: var(--sp-12) 0 var(--sp-10);
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
    .hero-copy .h1 { margin-bottom: var(--sp-4); }
    .hero-copy .lead {
      max-width: 700px;
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
      margin-bottom: var(--sp-5);
    }
    .hero-points {
      display: grid;
      gap: 12px;
      max-width: 720px;
    }
    .hero-point {
      display: flex;
      gap: 10px;
      align-items: flex-start;
      font-size: 14px;
      line-height: 1.65;
      color: var(--text-2);
    }
    .hero-point strong { color: var(--text); }
    .hero-check {
      width: 22px;
      height: 22px;
      border-radius: 50%;
      flex-shrink: 0;
      display: grid;
      place-items: center;
      background: var(--teal-soft);
      border: 1px solid var(--teal-line);
      color: var(--teal);
      margin-top: 1px;
      font-size: 12px;
      font-weight: 700;
    }

    .hero-form {
      background: linear-gradient(180deg, var(--bg-soft), var(--surface));
      border: 1px solid var(--border);
      border-top: 3px solid var(--teal);
      border-radius: var(--r-xl);
      padding: var(--sp-5);
      box-shadow: var(--shadow-md);
    }
    .hero-form-badge {
      display: inline-flex;
      margin-bottom: var(--sp-2);
      padding: 4px 10px;
      border-radius: var(--r-pill);
      background: var(--teal-soft);
      color: var(--teal-dark);
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.3px;
    }
    .hero-form-title {
      font-family: var(--font-display);
      font-size: 26px;
      font-weight: 700;
      line-height: 1.05;
      margin-bottom: 10px;
    }
    .hero-form-sub {
      font-size: 14px;
      line-height: 1.7;
      color: var(--text-2);
      margin-bottom: var(--sp-4);
    }

    .field {
      display: flex;
      flex-direction: column;
      gap: 6px;
      margin-bottom: var(--sp-3);
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
    .textarea::placeholder { color: var(--text-3); }
    .input:focus,
    .textarea:focus,
    .select:focus {
      border-color: var(--teal);
      box-shadow: 0 0 0 3px rgba(20,184,166,0.13);
    }
    .input.error,
    .textarea.error { border-color: var(--danger); }
    .field-error {
      display: none;
      font-size: 12px;
      color: var(--danger);
    }
    .field-error.show { display: block; }
    .form-note {
      margin-top: var(--sp-2);
      font-size: 12px;
      color: var(--text-3);
      text-align: center;
    }

    .split {
      display: grid;
      grid-template-columns: 320px minmax(0, 1fr);
      gap: var(--sp-10);
      align-items: start;
    }
    .cards-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: var(--sp-4);
      margin-top: var(--sp-5);
    }
    .card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--r-lg);
      padding: var(--sp-5);
      box-shadow: var(--shadow-sm);
      transition: 0.2s ease;
    }
    .card:hover {
      transform: translateY(-3px);
      border-color: var(--teal-line);
      box-shadow: var(--shadow-md);
    }
    .card-icon {
      width: 42px;
      height: 42px;
      border-radius: 12px;
      display: grid;
      place-items: center;
      background: var(--teal-soft);
      border: 1px solid var(--teal-line);
      color: var(--teal);
      margin-bottom: var(--sp-3);
    }
    .card .h3 { margin-bottom: 10px; }

    .steps {
      display: grid;
      gap: var(--sp-3);
      margin-top: var(--sp-5);
      max-width: 900px;
    }
    .step {
      display: grid;
      grid-template-columns: 56px minmax(0, 1fr);
      gap: var(--sp-4);
      padding: var(--sp-4);
      border: 1px solid var(--border);
      border-radius: var(--r-lg);
      background: var(--surface);
      transition: 0.2s ease;
    }
    .step:hover {
      transform: translateX(6px);
      border-color: var(--teal-line);
    }
    .step-num {
      font-family: var(--font-display);
      font-weight: 800;
      font-size: 36px;
      line-height: 1;
      color: var(--border-strong);
    }
    .step:hover .step-num { color: var(--teal); }
    .step-title {
      font-size: 17px;
      font-weight: 600;
      margin-bottom: 6px;
    }
    .step-desc {
      font-size: 14px;
      line-height: 1.75;
      color: var(--text-2);
    }

    .audit-list {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: var(--sp-3);
      margin-top: var(--sp-5);
    }
    .audit-item {
      display: flex;
      gap: 10px;
      align-items: flex-start;
      padding: var(--sp-4);
      background: var(--bg-soft);
      border: 1px solid var(--border);
      border-radius: var(--r-lg);
      transition: 0.2s ease;
    }
    .audit-item:hover {
      border-color: var(--teal-line);
      transform: translateY(-2px);
    }
    .audit-check {
      width: 24px;
      height: 24px;
      border-radius: 50%;
      flex-shrink: 0;
      display: grid;
      place-items: center;
      background: var(--teal-soft);
      border: 1px solid var(--teal-line);
      color: var(--teal);
      font-size: 12px;
      font-weight: 700;
      margin-top: 1px;
    }

    .faq {
      max-width: 900px;
      margin-top: var(--sp-5);
    }
    .faq-item { border-bottom: 1px solid var(--border); }
    .faq-item:last-child { border-bottom: none; }
    .faq-q {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: var(--sp-3);
      padding: var(--sp-3) 0;
      width: 100%;
      border: 0;
      background: transparent;
      text-align: left;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: 0.18s ease;
      user-select: none;
    }
    .faq-q:hover { color: var(--teal); }
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
    .footer-brand { max-width: 420px; }
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
    .footer-links a:hover { color: var(--teal); }
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
    .scroll-top:hover { background: var(--teal-dark); }

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

    .wrap { width: min(var(--container), calc(100% - 32px)); }
    .nav-links,
    .nav-actions { display: none; }
    .hamburger { display: flex; }
    .mobile-menu { display: block; }
    .hero-grid,
    .split,
    .cards-grid,
    .audit-list { grid-template-columns: 1fr; }
    .cta-band,
    .footer-inner { flex-direction: column; }
    .footer-links { align-items: flex-start; }
    .hero-grid { padding: var(--sp-8) 0 var(--sp-7); }
    .section { padding: var(--sp-8) 0; }
    .section-sm { padding: var(--sp-6) 0; }
    .hero-form,
    .card,
    .step,
    .cta-band { padding: var(--sp-4); }

    @media (min-width: 761px) {
      .wrap { width: min(var(--container), calc(100% - 48px)); }
      .nav-links,
      .nav-actions { display: flex; }
      .hamburger,
      .mobile-menu { display: none; }
      .hero-actions .btn { width: auto; }
      .input,
      .textarea,
      .select { font-size: 15px; }
    }

    @media (min-width: 981px) {
      .hero-grid {
        grid-template-columns: minmax(0, 1fr) 420px;
        padding: var(--sp-10) 0;
      }
      .split { grid-template-columns: 320px minmax(0, 1fr); }
      .cards-grid,
      .audit-list { grid-template-columns: 1fr 1fr; }
      .cta-band,
      .footer-inner { flex-direction: row; }
      .footer-links { align-items: flex-end; }
      .section { padding: var(--sp-10) 0; }
      .section-sm { padding: var(--sp-7) 0; }
    }
  </style><?php endif; ?>
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
  <?php wp_body_open(); ?>
  <?php if ($upsellio_load_public_tracking) : ?>
  <!-- Google Tag Manager (noscript) -->
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-KM9J5XC2"
  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
  <!-- End Google Tag Manager (noscript) -->
  <?php endif; ?>
  <header class="nav">
    <div class="wrap nav-inner">
      <a href="#start" class="brand" aria-label="Upsellio — strona główna">
        <?php if ($brand_logo_url !== "") : ?>
          <picture>
            <?php if ($brand_logo_webp_320_url !== "" && $brand_logo_webp_640_url !== "") : ?>
              <source type="image/webp" srcset="<?php echo esc_url($brand_logo_webp_320_url); ?> 320w, <?php echo esc_url($brand_logo_webp_640_url); ?> 640w" sizes="(max-width: 760px) 163px, 222px" />
            <?php endif; ?>
            <img src="<?php echo esc_url($brand_logo_url); ?>" alt="Upsellio" class="brand-logo" width="320" height="213" decoding="async" fetchpriority="high" />
          </picture>
        <?php else : ?>
          <div class="brand-text">
            <div class="brand-name">Upsellio</div>
            <div class="brand-sub">by Sebastian Kelm</div>
          </div>
        <?php endif; ?>
      </a>

      <ul class="nav-links">
        <?php foreach ($primary_navigation_top as $nav_link) : ?>
          <?php
          $nav_id = (int) ($nav_link["id"] ?? 0);
          $nav_children = ($nav_id > 0 && isset($primary_navigation_children[$nav_id])) ? (array) $primary_navigation_children[$nav_id] : [];
          ?>
          <?php if (!empty($nav_children)) : ?>
            <li class="nav-dropdown">
              <a href="<?php echo esc_url((string) $nav_link["url"]); ?>" class="nav-dropdown-parent"<?php echo ((string) ($nav_link["target"] ?? "") === "_blank") ? ' target="_blank" rel="noopener noreferrer"' : ""; ?>><?php echo esc_html((string) $nav_link["title"]); ?></a>
              <button type="button" class="nav-dropdown-toggle" aria-expanded="false" aria-label="<?php echo esc_attr("Rozwiń podmenu: " . (string) $nav_link["title"]); ?>">▾</button>
              <div class="nav-dropdown-menu">
                <?php foreach ($nav_children as $nav_child) : ?>
                  <a href="<?php echo esc_url((string) ($nav_child["url"] ?? "")); ?>"<?php echo ((string) ($nav_child["target"] ?? "") === "_blank") ? ' target="_blank" rel="noopener noreferrer"' : ""; ?>><?php echo esc_html((string) ($nav_child["title"] ?? "")); ?></a>
                <?php endforeach; ?>
              </div>
            </li>
          <?php else : ?>
            <li><a href="<?php echo esc_url((string) $nav_link["url"]); ?>"<?php echo ((string) ($nav_link["target"] ?? "") === "_blank") ? ' target="_blank" rel="noopener noreferrer"' : ""; ?>><?php echo esc_html((string) $nav_link["title"]); ?></a></li>
          <?php endif; ?>
        <?php endforeach; ?>
      </ul>

      <div class="nav-actions">
        <a href="#formularz" class="nav-cta">Darmowy audyt</a>
      </div>

      <button class="hamburger" id="hamburger" aria-label="Otwórz menu" aria-controls="mobile-menu" aria-expanded="false" type="button">
        <span></span><span></span><span></span>
      </button>
    </div>

    <div class="mobile-menu" id="mobile-menu">
      <div class="wrap">
        <?php foreach ($primary_navigation_top as $nav_link) : ?>
          <a href="<?php echo esc_url((string) $nav_link["url"]); ?>"<?php echo ((string) ($nav_link["target"] ?? "") === "_blank") ? ' target="_blank" rel="noopener noreferrer"' : ""; ?>><?php echo esc_html((string) $nav_link["title"]); ?></a>
          <?php
          $mobile_nav_id = (int) ($nav_link["id"] ?? 0);
          $mobile_children = ($mobile_nav_id > 0 && isset($primary_navigation_children[$mobile_nav_id])) ? (array) $primary_navigation_children[$mobile_nav_id] : [];
          ?>
          <?php foreach ($mobile_children as $mobile_child) : ?>
            <a class="mobile-sub-link" href="<?php echo esc_url((string) ($mobile_child["url"] ?? "")); ?>"<?php echo ((string) ($mobile_child["target"] ?? "") === "_blank") ? ' target="_blank" rel="noopener noreferrer"' : ""; ?>>
              <?php echo esc_html((string) ($mobile_child["title"] ?? "")); ?>
            </a>
          <?php endforeach; ?>
        <?php endforeach; ?>
        <a href="#formularz">Darmowy audyt →</a>
      </div>
    </div>
  </header>

  <main>
    <section class="hero" id="start">
      <div class="wrap hero-grid">
        <div class="hero-copy">
          <div class="hero-pill reveal visible">
            <div class="hero-pill-dot">M</div>
            <span>Dla firm, które chcą wiedzieć, czy ich <strong>reklamy Meta naprawdę dowożą wynik</strong></span>
          </div>

          <h1 class="h1 reveal visible">
            Darmowy audyt <span class="accent">wyników reklam Meta</span>
          </h1>

          <p class="lead reveal visible">
            Sprawdzę Twoje kampanie Meta Ads i pokażę Ci, <strong>co działa, co przepala budżet i co warto poprawić</strong>, żeby pozyskiwać lepsze leady lub sprzedawać skuteczniej.
          </p>

          <div class="hero-actions reveal visible">
            <a href="#formularz" class="btn btn-primary">Zamów darmowy audyt →</a>
            <a href="#co-sprawdze" class="btn btn-secondary">Zobacz co sprawdzę</a>
          </div>

          <div class="hero-micro reveal visible">
            Bez zobowiązań. Bez wciskania współpracy. Dostajesz konkretne wnioski i rekomendacje.
          </div>

          <div class="hero-points reveal visible">
            <div class="hero-point"><span class="hero-check">✓</span><span><strong>Nie tylko analiza reklam</strong> — patrzę też na jakość ruchu, komunikat i sens lejka.</span></div>
            <div class="hero-point"><span class="hero-check">✓</span><span><strong>Praktyczne spojrzenie sprzedażowe</strong> — nie oceniam kampanii wyłącznie po kliknięciach i CTR.</span></div>
            <div class="hero-point"><span class="hero-check">✓</span><span><strong>Wynik audytu</strong> dostaniesz w prostym, zrozumiałym języku — bez agencyjnego bełkotu.</span></div>
          </div>
        </div>

        <aside class="hero-form reveal visible" id="formularz">
          <div class="hero-form-badge">Darmowy audyt</div>
          <div class="hero-form-title">Zgłoś kampanie do sprawdzenia</div>
          <div class="hero-form-sub">
            Wypełnij krótki formularz. Odezwę się i powiem, czego potrzebuję do szybkiej analizy Twoich reklam Meta.
          </div>

          <?php
          echo upsellio_render_lead_form([
              "origin" => "audit-form",
              "variant" => "full",
              "submit_label" => "Zamów bezpłatny audyt →",
              "redirect_url" => home_url("/audyt-meta/"),
              "hidden_service" => "Audyt Meta Ads",
              "show_budget" => true,
              "budget_label" => "Miesięczny budżet reklam Meta",
              "budget_options" => [
                  "" => "— wybierz —",
                  "poniżej 2 000 zł" => "poniżej 2 000 zł",
                  "2 000 – 5 000 zł" => "2 000 – 5 000 zł",
                  "5 000 – 15 000 zł" => "5 000 – 15 000 zł",
                  "15 000 zł +" => "15 000 zł +",
              ],
              "show_goal" => true,
              "form_id" => "audit-form",
              "submit_button_id" => "submit-btn",
              "fineprint" => "Formularz zapisuje zgłoszenie w CRM i uruchamia automatyczny follow-up.",
          ]);
          ?>
        </aside>
      </div>
    </section>

    <section class="section section-border" id="co-sprawdze">
      <div class="wrap split">
        <div class="content">
          <div class="eyebrow reveal">Co sprawdzę</div>
          <h2 class="h2 reveal d1">Audyt nie kończy się na <span class="accent">„reklamy są dobrze ustawione”</span></h2>
          <p class="body reveal d2" style="margin-top:18px;">
            Patrzę na kampanie szerzej: wynik, koszt, jakość ruchu, sens kreacji, komunikatu i to, czy reklamy faktycznie prowadzą do zapytań albo sprzedaży.
          </p>
        </div>

        <div class="cards-grid">
          <div class="card reveal">
            <div class="card-icon">
              <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><rect x="2" y="10" width="3" height="6" rx="1.5" fill="currentColor"/><rect x="7.5" y="6" width="3" height="10" rx="1.5" fill="currentColor"/><rect x="13" y="2" width="3" height="14" rx="1.5" fill="currentColor"/></svg>
            </div>
            <div class="h3">Wyniki i opłacalność</div>
            <p class="body">Sprawdzę najważniejsze wskaźniki i ocenię, czy kampanie naprawdę pracują na sensowny wynik biznesowy.</p>
          </div>

          <div class="card reveal d1">
            <div class="card-icon">
              <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M3 9h12M9 3v12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            </div>
            <div class="h3">Strukturę kampanii</div>
            <p class="body">Zobaczę, czy konto reklamowe jest poukładane sensownie i czy struktura kampanii pomaga, a nie utrudnia optymalizację.</p>
          </div>

          <div class="card reveal d2">
            <div class="card-icon">
              <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><rect x="2" y="3" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M6 15h6M9 13v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            </div>
            <div class="h3">Kreacje i komunikat</div>
            <p class="body">Ocenię, czy reklamy przyciągają właściwe osoby i czy komunikat nie obiecuje czegoś, czego strona potem nie dowozi.</p>
          </div>

          <div class="card reveal d3">
            <div class="card-icon">
              <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><circle cx="9" cy="6" r="3.5" stroke="currentColor" stroke-width="1.5"/><path d="M3 15c0-3.314 2.686-5 6-5s6 1.686 6 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            </div>
            <div class="h3">Jakość leadów / ruchu</div>
            <p class="body">Nie tylko ile przyszło kontaktów, ale czy to byli właściwi ludzie i czy reklamy nie generują pustych wejść.</p>
          </div>
        </div>
      </div>

      <div class="wrap">
        <div class="audit-list">
          <div class="audit-item reveal"><span class="audit-check">✓</span><span>Czy kampanie mają sensowny cel i odpowiednie eventy</span></div>
          <div class="audit-item reveal d1"><span class="audit-check">✓</span><span>Czy budżet jest rozłożony logicznie, a nie chaotycznie</span></div>
          <div class="audit-item reveal d2"><span class="audit-check">✓</span><span>Czy kreacje i teksty przyciągają właściwe osoby</span></div>
          <div class="audit-item reveal d3"><span class="audit-check">✓</span><span>Czy problem leży w reklamie, czy może już na stronie / w lejku</span></div>
        </div>
      </div>
    </section>

    <section class="section bg-soft section-border" id="jak-to-dziala">
      <div class="wrap">
        <div class="content">
          <div class="eyebrow reveal">Jak to działa</div>
          <h2 class="h2 reveal d1">Prosty proces, <span class="accent">bez niepotrzebnego tarcia</span></h2>
          <p class="body reveal d2" style="margin-top:18px;">
            Chcę, żeby zgłoszenie było szybkie, a odpowiedź konkretna. Bez rozbudowanego briefu i bez formularza na 20 pól.
          </p>
        </div>

        <div class="steps">
          <div class="step reveal">
            <div class="step-num">01</div>
            <div>
              <div class="step-title">Wysyłasz zgłoszenie</div>
              <div class="step-desc">Opisujesz w kilku słowach, co Cię niepokoi i jaki masz cel reklamowy. To wystarczy na start.</div>
            </div>
          </div>

          <div class="step reveal d1">
            <div class="step-num">02</div>
            <div>
              <div class="step-title">Wracam z informacją, czego potrzebuję do analizy</div>
              <div class="step-desc">Jeśli temat ma sens, napiszę, co warto mi udostępnić do szybkiego audytu: np. screeny z wyników, dostęp tylko do odczytu albo kilka danych o kampanii.</div>
            </div>
          </div>

          <div class="step reveal d2">
            <div class="step-num">03</div>
            <div>
              <div class="step-title">Dostajesz konkretne wnioski</div>
              <div class="step-desc">Powiem wprost, co wygląda dobrze, co budzi zastrzeżenia i od czego zacząłbym poprawki. Bez lania wody i bez sztucznego straszenia.</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section section-border">
      <div class="wrap">
        <div class="cta-band reveal">
          <div>
            <h3>Masz wrażenie, że reklamy „coś robią”, ale nie wiesz czy dobrze?</h3>
            <p>To najczęstsza sytuacja. Audyt ma dać Ci jasność: zostawić, poprawić czy przebudować podejście.</p>
          </div>
          <a href="#formularz" class="btn btn-primary">Chcę darmowy audyt →</a>
        </div>
      </div>
    </section>

    <section class="section bg-soft" id="faq">
      <div class="wrap">
        <div class="content">
          <div class="eyebrow reveal">FAQ</div>
          <h2 class="h2 reveal d1">Najczęstsze <span class="accent">pytania</span></h2>
        </div>

        <div class="faq">
          <div class="faq-item reveal">
            <button class="faq-q" type="button">
              <span>Czy ten audyt naprawdę jest darmowy?</span>
              <span class="faq-icon">+</span>
            </button>
            <div class="faq-a">Tak. To darmowa analiza wstępna, która ma dać Ci jasność, czy kampanie idą w dobrym kierunku i gdzie widać największe ryzyko albo potencjał poprawy.</div>
          </div>

          <div class="faq-item reveal d1">
            <button class="faq-q" type="button">
              <span>Czy muszę od razu dawać pełen dostęp do konta reklamowego?</span>
              <span class="faq-icon">+</span>
            </button>
            <div class="faq-a">Nie. Na start wystarczy zgłoszenie i krótki opis sytuacji. Jeśli będzie sens i chęć pójścia dalej, dam Ci znać, jakie dane będą potrzebne do sensownej oceny.</div>
          </div>

          <div class="faq-item reveal d2">
            <button class="faq-q" type="button">
              <span>Dla kogo ten audyt ma największy sens?</span>
              <span class="faq-icon">+</span>
            </button>
            <div class="faq-a">Najbardziej dla firm, które już reklamują się na Meta lub planują zwiększać budżet, ale nie mają pewności, czy kampanie są prowadzone dobrze i czy przynoszą właściwy efekt.</div>
          </div>

          <div class="faq-item reveal d3">
            <button class="faq-q" type="button">
              <span>Czy po audycie będziesz próbował od razu sprzedać współpracę?</span>
              <span class="faq-icon">+</span>
            </button>
            <div class="faq-a">Nie taki jest cel tej strony. Najpierw masz dostać wartość i wnioski. Jeśli później uznasz, że chcesz iść dalej, wtedy dopiero możemy o tym rozmawiać.</div>
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
            <?php if ($brand_logo_url !== "") : ?>
              <picture>
                <?php if ($brand_logo_webp_320_url !== "" && $brand_logo_webp_640_url !== "") : ?>
                  <source type="image/webp" srcset="<?php echo esc_url($brand_logo_webp_320_url); ?> 320w, <?php echo esc_url($brand_logo_webp_640_url); ?> 640w" sizes="220px" />
                <?php endif; ?>
                <img src="<?php echo esc_url($brand_logo_url); ?>" alt="Upsellio" class="brand-logo" width="320" height="213" loading="lazy" decoding="async" />
              </picture>
            <?php else : ?>
              <div class="brand-text">
                <div class="brand-name">Upsellio</div>
                <div class="brand-sub">by Sebastian Kelm</div>
              </div>
            <?php endif; ?>
          </div>
          <div class="muted" style="margin-top:12px;font-size:13px;line-height:1.6;">
            Darmowy audyt wyników reklam Meta dla firm, które chcą lepiej rozumieć, co działa i co poprawić.
          </div>
        </div>

        <div class="footer-links">
          <a href="<?php echo esc_url(function_exists("upsellio_get_mailto_href") ? upsellio_get_mailto_href("kontakt@upsellio.pl") : "mailto:kontakt@upsellio.pl"); ?>"><?php echo esc_html(function_exists("upsellio_obfuscate_email_address") ? upsellio_obfuscate_email_address("kontakt@upsellio.pl") : "kontakt@upsellio.pl"); ?></a>
          <a href="https://www.linkedin.com/in/sebastiankelm/" target="_blank" rel="noopener">LinkedIn</a>
          <a href="#co-sprawdze">Co sprawdzę</a>
          <a href="#jak-to-dziala">Jak to działa</a>
        </div>
      </div>

      <?php echo upsellio_get_footer_popular_definitions_html(); ?>
      <?php echo upsellio_get_footer_city_links_html(); ?>
      <div class="footer-copy">© 2025 Upsellio / Sebastian Kelm. Wszelkie prawa zastrzeżone.</div>
    </div>
  </footer>

  <button class="scroll-top" id="scroll-top" aria-label="Wróć na górę">↑</button>

  <?php wp_footer(); ?>
</body>
</html>

