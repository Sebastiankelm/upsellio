<?php
if (!defined("ABSPATH")) {
    exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo("charset"); ?>" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Upsellio — Marketing i strony internetowe dla firm B2B, które chcą sprzedawać więcej</title>
  <meta
    name="description"
    content="Upsellio to Sebastian Kelm — praktyk sprzedaży B2B. Kampanie Meta i Google Ads, strony i sklepy internetowe oraz doradztwo sprzedażowe w ramach współpracy."
  />
  <meta property="og:title" content="Upsellio — Marketing i strony dla firm B2B" />
  <meta
    property="og:description"
    content="Pomagam małym i średnim firmom zdobywać klientów i poprawiać sprzedaż dzięki marketingowi, stronom i praktycznemu spojrzeniu na biznes."
  />
  <meta property="og:type" content="website" />
  <meta property="og:url" content="<?php echo esc_url(home_url("/")); ?>" />
  <meta name="twitter:card" content="summary_large_image" />

  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "ProfessionalService",
    "name": "Upsellio",
    "url": "<?php echo esc_url(home_url("/")); ?>",
    "email": "kontakt@upsellio.pl",
    "description": "Marketing internetowy, strony internetowe, sklepy online i doradztwo sprzedażowe dla małych i średnich firm.",
    "founder": {
      "@type": "Person",
      "name": "Sebastian Kelm"
    }
  }
  </script>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;700&display=swap"
    rel="stylesheet"
  />

  <style>
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
    }

    @media (prefers-color-scheme: dark) {
      :root {
        --bg: #111110;
        --bg-soft: #181816;
        --bg-muted: #212120;
        --surface: #181816;

        --text: #f1eee8;
        --text-2: #c4c4bc;
        --text-3: #8b8b82;

        --border: #2d2d2b;
        --border-strong: #454540;

        --teal-soft: rgba(29, 158, 117, 0.12);
        --teal-line: rgba(29, 158, 117, 0.22);

        --shadow-sm: 0 1px 4px rgba(0, 0, 0, 0.35);
        --shadow-md: 0 10px 28px rgba(0, 0, 0, 0.45);
      }
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
      max-height: 420px;
    }

    .mobile-menu a {
      display: block;
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

    .hero-micro {
      font-size: 12px;
      color: var(--text-3);
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
      border: 1px solid var(--border-strong);
      background: var(--surface);
      color: var(--text);
      border-radius: var(--r-md);
      padding: 13px 15px;
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

    @media (max-width: 980px) {
      .hero-grid,
      .split,
      .service-hero {
        grid-template-columns: 1fr;
      }

      .hero-aside {
        display: none;
      }
    }

    @media (max-width: 760px) {
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

      .problem-grid,
      .service-grid,
      .stats-grid,
      .fit-grid,
      .form-grid {
        grid-template-columns: 1fr;
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
    }
  </style>
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
  <?php wp_body_open(); ?>
  <header class="nav">
    <div class="wrap nav-inner">
      <a href="#start" class="brand" aria-label="Upsellio — strona główna">
        <div class="brand-mark">U</div>
        <div class="brand-text">
          <div class="brand-name">Upsellio</div>
          <div class="brand-sub">by Sebastian Kelm</div>
        </div>
      </a>

      <ul class="nav-links">
        <li><a href="#uslugi">Usługi</a></li>
        <li><a href="#jak-dzialam">Jak działam</a></li>
        <li><a href="#wyniki">Wyniki</a></li>
        <li><a href="#faq">FAQ</a></li>
      </ul>

      <div class="nav-actions">
        <a href="#kontakt" class="nav-cta">Bezpłatna rozmowa</a>
      </div>

      <button class="hamburger" id="hamburger" aria-label="Otwórz menu">
        <span></span><span></span><span></span>
      </button>
    </div>

    <div class="mobile-menu" id="mobile-menu">
      <div class="wrap">
        <a href="#uslugi">Usługi</a>
        <a href="#jak-dzialam">Jak działam</a>
        <a href="#wyniki">Wyniki</a>
        <a href="#faq">FAQ</a>
        <a href="#kontakt">Bezpłatna rozmowa →</a>
      </div>
    </div>
  </header>

  <main>
    <section class="hero s-hero" id="start">
      <div class="wrap hero-grid">
        <div class="hero-copy">
          <div class="hero-pill reveal visible">
            <div class="hero-pill-dot">●</div>
            <span>Dla <strong>małych i średnich firm B2B</strong>, które chcą poukładać marketing i sprzedaż</span>
          </div>

          <h1 class="h1 reveal visible">
            Pomagam firmom zdobywać klientów i
            <span class="accent">poprawiać sprzedaż</span>
            — bez agencyjnego chaosu
          </h1>

          <p class="lead reveal visible">
            Tworzę kampanie i strony, które mają jeden cel:
            <strong>pomóc Ci zdobywać więcej wartościowych zapytań i sprzedawać skuteczniej.</strong>
            Do każdej współpracy wnoszę praktyczne doświadczenie sprzedażowe B2B — nie jako osobną usługę, tylko jako realną przewagę tej współpracy.
          </p>

          <div class="hero-actions reveal visible">
            <a href="#kontakt" class="btn btn-primary btn-pulse">Umów bezpłatną rozmowę →</a>
            <a href="#uslugi" class="btn btn-secondary">Zobacz co robię</a>
          </div>

          <div class="hero-micro reveal visible">
            Bez zobowiązań. Krótka rozmowa, żeby sprawdzić, czy i jak mogę pomóc.
          </div>
        </div>

        <aside class="hero-aside">
          <div class="hero-aside-label">Doświadczenie z praktyki</div>

          <div class="hero-stat">
            <div class="hero-stat-num">~1 mln PLN</div>
            <div class="hero-stat-text">miesięcznej sprzedaży handlowej w modelu B2B</div>
          </div>

          <div class="hero-stat">
            <div class="hero-stat-num">10 lat</div>
            <div class="hero-stat-text">w sprzedaży, zarządzaniu, analizie i marketingu</div>
          </div>

          <div class="hero-stat">
            <div class="hero-stat-num">15 osób</div>
            <div class="hero-stat-text">zbudowany i prowadzony dział sprzedaży</div>
          </div>
        </aside>
      </div>
    </section>

    <section class="section section-border" id="dlaczego">
      <div class="wrap split">
        <div class="content">
          <div class="eyebrow reveal">Dlaczego to działa</div>
          <h2 class="h2 reveal d1">Łączę rzeczy, które <span class="accent">rzadko idą razem</span></h2>
          <p class="body reveal d2" style="margin-top: 18px;">
            Większość agencji robi reklamy. Większość freelancerów robi strony.
            Mało kto rozumie przy tym, jak naprawdę wygląda sprzedaż B2B od środka
            i gdzie firmy realnie tracą klientów, marżę albo skuteczność działań.
          </p>
        </div>

        <div class="stack-cards">
          <div class="feature-row reveal">
            <div class="feature-icon">
              <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                <rect x="2" y="10" width="3" height="6" rx="1.5" fill="currentColor"/>
                <rect x="7.5" y="6" width="3" height="10" rx="1.5" fill="currentColor"/>
                <rect x="13" y="2" width="3" height="14" rx="1.5" fill="currentColor"/>
              </svg>
            </div>
            <div>
              <div class="feature-title">Marketing nastawiony na wynik</div>
              <div class="feature-desc">Kampanie Meta i Google Ads optymalizowane pod klientów i zapytania, a nie pod ładne statystyki.</div>
            </div>
          </div>

          <div class="feature-row reveal d1">
            <div class="feature-icon">
              <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                <rect x="2" y="3" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.5"/>
                <path d="M6 15h6M9 13v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
            </div>
            <div>
              <div class="feature-title">Strony i sklepy, które sprzedają</div>
              <div class="feature-desc">Nie tylko estetyczne. Zaprojektowane tak, żeby użytkownik wiedział co zrobić i dlaczego właśnie u Ciebie.</div>
            </div>
          </div>

          <div class="feature-row reveal d2">
            <div class="feature-icon">
              <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                <circle cx="9" cy="6" r="3.5" stroke="currentColor" stroke-width="1.5"/>
                <path d="M3 15c0-3.314 2.686-5 6-5s6 1.686 6 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
            </div>
            <div>
              <div class="feature-title">Praktyk sprzedaży B2B</div>
              <div class="feature-desc">To doświadczenie zmienia jakość współpracy, bo szybciej widać problem głębiej niż tylko w reklamie czy stronie.</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section bg-soft section-border" id="problem">
      <div class="wrap">
        <div class="content">
          <div class="eyebrow reveal">Problem</div>
          <h2 class="h2 reveal d1">Technicznie poprawne działania, które <span class="accent">nie przynoszą klientów</span></h2>
          <p class="body reveal d2" style="margin-top: 18px;">
            Kampania może być ustawiona poprawnie i nadal nie dowozić.
            Strona może wyglądać dobrze i nie generować zapytań.
            Zwykle problem leży głębiej — w komunikacie, ofercie, lejku albo sposobie, w jaki firma pracuje z ruchem i sprzedażą.
          </p>
        </div>

        <div class="problem-grid">
          <div class="problem-card reveal">Płacisz za reklamy, ale mało wartościowych klientów się odzywa</div>
          <div class="problem-card reveal d1">Strona wygląda profesjonalnie, ale nie generuje zapytań</div>
          <div class="problem-card reveal d2">Sklep ma ruch, ale konwersja jest zbyt niska</div>
          <div class="problem-card reveal d3">Nie wiesz, co faktycznie działa, a co jest stratą budżetu</div>
        </div>
      </div>
    </section>

    <section class="section section-border" id="uslugi">
      <div class="wrap">
        <div class="content">
          <div class="eyebrow reveal">Usługi</div>
          <h2 class="h2 reveal d1">Co konkretnie <span class="accent">dostajesz</span></h2>
          <p class="body reveal d2" style="margin-top: 18px;">
            Pracujesz bezpośrednio ze mną. Bez pośredników, bez przekazywania projektu dalej, bez rozmytej odpowiedzialności.
          </p>
        </div>

        <div class="service-hero reveal" style="margin-top: 40px;">
          <div>
            <div class="service-top">
              <div class="h3">Marketing — Meta i Google Ads</div>
              <span class="badge badge-green">Główna usługa</span>
            </div>
            <p class="body">
              Kampanie reklamowe zoptymalizowane pod pozyskiwanie wartościowych klientów, nie pod puste statystyki.
              Analizuję dane, testuję, wyciągam wnioski i jasno komunikuję, co robię i dlaczego.
            </p>
          </div>

          <div>
            <div class="chips">
              <span class="chip">Meta Ads</span>
              <span class="chip">Google Ads</span>
              <span class="chip">Analiza i raportowanie</span>
              <span class="chip">Optymalizacja budżetu</span>
              <span class="chip">Lepsza jakość leadów</span>
            </div>
          </div>
        </div>

        <div class="service-grid">
          <div class="service-card reveal">
            <div class="service-top">
              <div class="h3">Strony i sklepy internetowe</div>
              <span class="badge badge-gray">Usługa</span>
            </div>
            <p class="body">
              Projektuję i wdrażam strony oraz sklepy z myślą o konwersji.
              Każdy projekt zaczyna się od celu biznesowego i komunikatu, a nie od przypadkowego szablonu.
            </p>
            <div class="chips">
              <span class="chip">Landing page</span>
              <span class="chip">Strony firmowe</span>
              <span class="chip">WooCommerce</span>
              <span class="chip">Shopify</span>
              <span class="chip">UX pod konwersję</span>
            </div>
          </div>

          <div class="service-card reveal d1">
            <div class="service-top">
              <div class="h3">Rozwiązania webowe i automatyzacje</div>
              <span class="badge badge-gray">Dodatkowo</span>
            </div>
            <p class="body">
              Dla firm, które potrzebują czegoś więcej niż standardowej strony:
              prostych aplikacji webowych, systemów wewnętrznych i automatyzacji procesów.
            </p>
            <div class="chips">
              <span class="chip">Aplikacje webowe</span>
              <span class="chip">Systemy wewnętrzne</span>
              <span class="chip">Automatyzacje</span>
              <span class="chip">Integracje</span>
            </div>
          </div>
        </div>

        <div class="bonus reveal d2">
          <div class="bonus-head">
            <div class="bonus-icon">
              <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                <path d="M9 2L11.5 7H17L12.5 10.5L14 16L9 12.5L4 16L5.5 10.5L1 7H6.5L9 2Z" fill="white"/>
              </svg>
            </div>
            <div class="bonus-title">Doradztwo sprzedażowe — w ramach każdej współpracy</div>
            <div class="bonus-tag">W cenie</div>
          </div>

          <div class="bonus-body">
            Przez lata pracowałem jako handlowiec i dyrektor sprzedaży B2B.
            Tę wiedzę wnosisz do każdego projektu — nie jako osobny produkt, tylko jako realną przewagę współpracy.
            Dzięki temu kampania i strona są lepiej osadzone w tym, jak naprawdę wygląda Twoja sprzedaż, marża i proces podejmowania decyzji po stronie klienta.
          </div>

          <div class="bonus-chips">
            <span class="bonus-chip">Audyt procesów sprzedaży</span>
            <span class="bonus-chip">Analiza danych sprzedażowych</span>
            <span class="bonus-chip">Wąskie gardła</span>
            <span class="bonus-chip">Optymalizacja kosztowa</span>
            <span class="bonus-chip">Lepsze decyzje marketingowe</span>
          </div>
        </div>
      </div>
    </section>

    <section class="section-sm section-border">
      <div class="wrap">
        <div class="cta-band reveal">
          <div>
            <h3>Nie wiesz, od czego zacząć?</h3>
            <p>Powiedz mi w kilku słowach o swojej firmie. Powiem wprost, co moim zdaniem najbardziej blokuje wzrost i od czego warto zacząć.</p>
          </div>
          <a href="#kontakt" class="btn btn-primary">Umów rozmowę →</a>
        </div>
      </div>
    </section>

    <section class="section section-border" id="jak-dzialam">
      <div class="wrap">
        <div class="content">
          <div class="eyebrow reveal">Jak działam</div>
          <h2 class="h2 reveal d1">Nie zaczynam od <span class="accent">ustawiania kampanii</span></h2>
          <p class="body reveal d2" style="margin-top: 18px;">
            Zaczynam od zrozumienia, co dziś blokuje wzrost i które działanie da najlepszy efekt przy danym budżecie, ofercie i sposobie sprzedaży.
          </p>
        </div>

        <div class="steps" style="margin-top: 40px;">
          <div class="step reveal">
            <div class="step-num">01</div>
            <div>
              <div class="step-title">Poznaję firmę i diagnozuję problem</div>
              <div class="step-desc">
                Krótka rozmowa bez zobowiązań. Chcę zrozumieć, co dziś realnie hamuje wzrost:
                oferta, komunikacja, lejek, strona, dane czy sam proces sprzedaży.
              </div>
            </div>
          </div>

          <div class="step reveal d1">
            <div class="step-num">02</div>
            <div>
              <div class="step-title">Wybieram najlepszą drogę — nie najdroższą</div>
              <div class="step-desc">
                Nie wciskam z góry konkretnej usługi. Propozycja wychodzi z rozmowy i potrzeb firmy —
                czasem to kampania, czasem landing page, a czasem poprawa tego, co już działa.
              </div>
            </div>
          </div>

          <div class="step reveal d2">
            <div class="step-num">03</div>
            <div>
              <div class="step-title">Wdrażam i jestem w stałym kontakcie</div>
              <div class="step-desc">
                Pracujesz bezpośrednio ze mną. Wiesz, co robię, dlaczego to robię i jakie są efekty.
                Bez ciszy, bez chaosu i bez sytuacji, w której nie wiadomo, kto odpowiada za wynik.
              </div>
            </div>
          </div>

          <div class="step reveal d3">
            <div class="step-num">04</div>
            <div>
              <div class="step-title">Optymalizuję i patrzę szerzej na sprzedaż</div>
              <div class="step-desc">
                Analizuję liczby i poprawiam działania. Do tego aktywnie dzielę się obserwacjami dotyczącymi sprzedaży, procesu i miejsca, w którym firma traci potencjał.
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section bg-soft section-border" id="wyniki">
      <div class="wrap">
        <div class="content">
          <div class="eyebrow reveal">Doświadczenie i wyniki</div>
          <h2 class="h2 reveal d1">Moje podejście opiera się na <span class="accent">praktyce, nie teorii</span></h2>
          <p class="body reveal d2" style="margin-top: 18px;">
            Nie opieram komunikacji na samych hasłach. Przez lata pracowałem na wynikach sprzedażowych, marży, procesach, zespołach i danych.
          </p>
        </div>

        <div class="stats-grid">
          <div class="stat-card reveal">
            <div class="stat-num">~1 mln</div>
            <div class="stat-text">miesięcznej sprzedaży handlowej w modelu B2B</div>
          </div>
          <div class="stat-card reveal d1">
            <div class="stat-num">~500k</div>
            <div class="stat-text">miesięcznego obrotu sklepu zbudowanego od zera</div>
          </div>
          <div class="stat-card reveal d2">
            <div class="stat-num">3×</div>
            <div class="stat-text">wyższa marża sklepu niż w klasycznym kanale handlowym</div>
          </div>
          <div class="stat-card reveal d3">
            <div class="stat-num">15 os.</div>
            <div class="stat-text">zbudowany i zarządzany dział sprzedaży</div>
          </div>
        </div>

        <div class="cases">
          <div class="case reveal">
            <div class="case-tag">Sprzedaż B2B</div>
            <div class="case-title">Budowa sprzedaży do poziomu ok. 1 mln PLN miesięcznie</div>
            <div class="case-body">
              W praktyce oznaczało to nie tylko samo sprzedawanie, ale także pracę na procesie, lejku, segmentacji klientów i sposobie prowadzenia działań handlowych.
            </div>
            <div class="case-result">Efekt: ok. 1 mln PLN / mies. sprzedaży</div>
          </div>

          <div class="case reveal d1">
            <div class="case-tag">E-commerce</div>
            <div class="case-title">Sklep internetowy z wyższą marżą niż tradycyjna sprzedaż</div>
            <div class="case-body">
              Stworzyłem sklep od zera dla produktu sprzedawanego wcześniej głównie przez handlowców.
              Po kilku latach sklep generował znaczący obrót przy dużo lepszej marży.
            </div>
            <div class="case-result">Efekt: ok. 500k PLN / mies. i 3× wyższa marża</div>
          </div>

          <div class="case reveal d2">
            <div class="case-tag">Zarządzanie sprzedażą</div>
            <div class="case-title">Budowa 15-osobowego działu i systemu mierzenia efektywności</div>
            <div class="case-body">
              Rekrutacja, onboarding, procesy, analiza wyników, optymalizacja kosztów i stałe poprawianie skuteczności pracy zespołu.
            </div>
            <div class="case-result">Efekt: poukładany dział z procesem i mierzalnością</div>
          </div>
        </div>
      </div>
    </section>

    <section class="section section-border" id="dla-kogo">
      <div class="wrap">
        <div class="content">
          <div class="eyebrow reveal">Dla kogo</div>
          <h2 class="h2 reveal d1">Sprawdź, czy <span class="accent">do siebie pasujemy</span></h2>
        </div>

        <div class="fit-grid" style="margin-top: 40px;">
          <div class="fit-card yes reveal">
            <div class="fit-label">Dobry fit, jeśli:</div>
            <div class="fit-list">
              <div class="fit-item"><span class="fit-icon">✓</span>Prowadzisz małą lub średnią firmę B2B lub usługową</div>
              <div class="fit-item"><span class="fit-icon">✓</span>Chcesz rozumieć, co dzieje się w marketingu i dlaczego</div>
              <div class="fit-item"><span class="fit-icon">✓</span>Zależy Ci na partnerze, a nie tylko na wykonawcy</div>
              <div class="fit-item"><span class="fit-icon">✓</span>Masz reklamy lub stronę, ale wyniki Cię rozczarowują</div>
              <div class="fit-item"><span class="fit-icon">✓</span>Prowadzisz e-commerce i chcesz poprawić marżę lub konwersję</div>
            </div>
          </div>

          <div class="fit-card no reveal d1">
            <div class="fit-label">Mniejszy fit, jeśli:</div>
            <div class="fit-list">
              <div class="fit-item"><span class="fit-icon">—</span>Szukasz wyłącznie najtańszej opcji na rynku</div>
              <div class="fit-item"><span class="fit-icon">—</span>Potrzebujesz dużego zespołu wielu specjalistów naraz</div>
              <div class="fit-item"><span class="fit-icon">—</span>Nie masz przestrzeni na rozmowę o celach i danych</div>
              <div class="fit-item"><span class="fit-icon">—</span>Oczekujesz gwarantowanych wyników bez udziału po swojej stronie</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section bg-soft section-border" id="faq">
      <div class="wrap">
        <div class="content">
          <div class="eyebrow reveal">FAQ</div>
          <h2 class="h2 reveal d1">Odpowiedzi na <span class="accent">najczęstsze pytania</span></h2>
        </div>

        <div class="faq" style="margin-top: 40px;">
          <div class="faq-item reveal">
            <div class="faq-q">
              <span>Co zyskuję, współpracując bezpośrednio z Tobą, a nie z agencją?</span>
              <span class="faq-icon">+</span>
            </div>
            <div class="faq-a">
              Rozmawiasz z osobą, która faktycznie robi robotę. Masz bezpośredni kontakt, większą przejrzystość i kogoś, kto patrzy na Twój biznes szerzej niż tylko przez pryzmat jednej usługi.
            </div>
          </div>

          <div class="faq-item reveal d1">
            <div class="faq-q">
              <span>Co obejmuje wsparcie biznesowe i sprzedażowe w ramach współpracy?</span>
              <span class="faq-icon">+</span>
            </div>
            <div class="faq-a">
              Patrzę szerzej na Twój biznes: analizuję dane sprzedażowe, wskazuję miejsca utraty klientów lub marży i pomagam poukładać proces. To nie jest sztuczny upsell — tylko mój naturalny sposób pracy.
            </div>
          </div>

          <div class="faq-item reveal d2">
            <div class="faq-q">
              <span>Dla jakich firm to rozwiązanie działa najlepiej?</span>
              <span class="faq-icon">+</span>
            </div>
            <div class="faq-a">
              Najlepiej sprawdza się przy małych i średnich firmach B2B, usługowych oraz e-commerce, które chcą poprawić skuteczność marketingu i sprzedaży bez budowania od razu dużych struktur.
            </div>
          </div>

          <div class="faq-item reveal d3">
            <div class="faq-q">
              <span>Jak wygląda bezpłatna rozmowa wstępna?</span>
              <span class="faq-icon">+</span>
            </div>
            <div class="faq-a">
              To krótka rozmowa przez Google Meet lub telefon. Opowiadasz o swojej sytuacji, a ja mówię szczerze, co widzę i czy jestem właściwą osobą do pomocy.
            </div>
          </div>

          <div class="faq-item reveal">
            <div class="faq-q">
              <span>Czy musisz mieć gotowy brief lub materiały?</span>
              <span class="faq-icon">+</span>
            </div>
            <div class="faq-a">
              Nie. Możemy zacząć od zera. Pomagam zebrać informacje, uporządkować ofertę i przygotować sensowny kierunek działań.
            </div>
          </div>

          <div class="faq-item reveal d1">
            <div class="faq-q">
              <span>Ile trwa realizacja i jak wygląda wycena?</span>
              <span class="faq-icon">+</span>
            </div>
            <div class="faq-a">
              Kampanie można uruchomić relatywnie szybko. Strony i sklepy zwykle zajmują od 2 do 6 tygodni w zależności od zakresu. Wycena jest indywidualna, bo ma wynikać z realnych potrzeb firmy.
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section" id="kontakt">
      <div class="wrap">
        <div class="form-shell reveal visible">
          <div class="form-head">
            <div class="eyebrow">Kontakt</div>
            <h2 class="h2">Umów <span class="accent">bezpłatną rozmowę</span></h2>
            <p class="body" style="margin-top: 18px;">
              Napisz kilka słów o swojej firmie i tym, co chcesz poprawić lub osiągnąć. Odpowiem osobiście.
            </p>
          </div>

          <form id="contact-form" novalidate>
            <div class="form-grid">
              <div class="field">
                <label for="fname">Imię i firma *</label>
                <input class="input" type="text" id="fname" name="name" placeholder="np. Marek, firma XYZ" required />
                <span class="field-error" id="fname-err">Podaj imię i nazwę firmy</span>
              </div>

              <div class="field">
                <label for="femail">E-mail *</label>
                <input class="input" type="email" id="femail" name="email" placeholder="adres@firma.pl" required />
                <span class="field-error" id="femail-err">Podaj poprawny adres e-mail</span>
              </div>

              <div class="field">
                <label for="fphone">Telefon (opcjonalnie)</label>
                <input class="input" type="tel" id="fphone" name="phone" placeholder="+48 600 000 000" />
              </div>

              <div class="field">
                <label for="fservice">Czego szukasz?</label>
                <select class="select" id="fservice" name="service">
                  <option value="">— wybierz —</option>
                  <option>Kampanie Meta / Google Ads</option>
                  <option>Strona lub sklep internetowy</option>
                  <option>Aplikacja lub automatyzacja</option>
                  <option>Doradztwo sprzedażowe</option>
                  <option>Nie wiem — chcę porozmawiać</option>
                </select>
              </div>

              <div class="field full">
                <label for="fmsg">Co chcesz poprawić lub osiągnąć? *</label>
                <textarea class="textarea" id="fmsg" name="message" placeholder="np. reklamy nie przynoszą wartościowych klientów, chcę nowy sklep nastawiony na konwersję, chcę poukładać sprzedaż..." required></textarea>
                <span class="field-error" id="fmsg-err">Opisz w kilku słowach swoją sytuację</span>
              </div>
            </div>

            <button type="submit" class="btn btn-primary submit" id="submit-btn">Wyślij i umów rozmowę →</button>
            <p class="form-note">Odpowiadam osobiście — nie bot, nie asystent. Tu warto później podpiąć realną wysyłkę formularza.</p>
          </form>

          <div class="form-alt">
            <div class="form-alt-item">
              <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                <path d="M7 1a6 6 0 100 12A6 6 0 007 1zM7 4v3.5l2 2" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" fill="none"/>
              </svg>
              Odpowiedź zwykle tego samego dnia roboczego
            </div>
            <div class="form-alt-item">
              <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                <path d="M2 4.5C2 3.12 3.12 2 4.5 2h5C10.88 2 12 3.12 12 4.5v5C12 10.88 10.88 12 9.5 12h-5C3.12 12 2 10.88 2 9.5v-5z" stroke="currentColor" stroke-width="1.2" fill="none"/>
                <path d="M5 7l1.5 1.5L9 5.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              Bez zobowiązań i bez ciśnienia
            </div>
            <div class="form-alt-item">
              Wolisz zadzwonić?
              <a href="tel:+48000000000">+48 000 000 000</a>
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
            Marketing · Strony · Sklepy · Doradztwo sprzedażowe<br />
            Podejście oparte na praktyce sprzedażowej, nie tylko na narzędziach.
          </div>
        </div>

        <div class="footer-links">
          <a href="mailto:kontakt@upsellio.pl">kontakt@upsellio.pl</a>
          <a href="https://linkedin.com/in/sebastiankelm" target="_blank" rel="noopener">LinkedIn</a>
          <a href="#uslugi">Usługi</a>
          <a href="#jak-dzialam">Jak działam</a>
        </div>
      </div>

      <?php echo upsellio_get_footer_city_links_html(); ?>
      <div class="footer-copy">© 2025 Upsellio / Sebastian Kelm. Wszelkie prawa zastrzeżone.</div>
    </div>
  </footer>

  <button class="scroll-top" id="scroll-top" aria-label="Wróć na górę">↑</button>

  <script>
    (function () {
      const reveals = document.querySelectorAll(".reveal");
      const eyebrows = document.querySelectorAll(".eyebrow");
      const topBtn = document.getElementById("scroll-top");

      function onScroll() {
        const vh = window.innerHeight;

        reveals.forEach((el) => {
          if (el.getBoundingClientRect().top < vh * 0.9) {
            el.classList.add("visible");
          }
        });

        eyebrows.forEach((el) => {
          if (el.getBoundingClientRect().top < vh * 0.9) {
            el.classList.add("vis");
          }
        });

        if (window.scrollY > 450) topBtn.classList.add("visible");
        else topBtn.classList.remove("visible");
      }

      window.addEventListener("scroll", onScroll, { passive: true });
      setTimeout(onScroll, 120);

      topBtn.addEventListener("click", function () {
        window.scrollTo({ top: 0, behavior: "smooth" });
      });

      const ham = document.getElementById("hamburger");
      const mob = document.getElementById("mobile-menu");

      ham.addEventListener("click", function () {
        ham.classList.toggle("open");
        mob.classList.toggle("open");
      });

      mob.querySelectorAll("a").forEach((a) => {
        a.addEventListener("click", function () {
          ham.classList.remove("open");
          mob.classList.remove("open");
        });
      });

      document.querySelectorAll('a[href^="#"]').forEach((a) => {
        a.addEventListener("click", function (e) {
          const id = a.getAttribute("href").slice(1);
          if (!id) return;
          const target = document.getElementById(id);
          if (!target) return;
          e.preventDefault();
          const offset = target.getBoundingClientRect().top + window.scrollY - 72;
          window.scrollTo({ top: offset, behavior: "smooth" });
        });
      });

      document.querySelectorAll(".faq-item").forEach((item) => {
        const q = item.querySelector(".faq-q");
        q.addEventListener("click", function () {
          const isOpen = item.classList.contains("open");
          document.querySelectorAll(".faq-item").forEach((i) => i.classList.remove("open"));
          if (!isOpen) item.classList.add("open");
        });
      });

      const form = document.getElementById("contact-form");
      const submitBtn = document.getElementById("submit-btn");

      function validateEmail(v) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
      }

      function setError(inputId, errId, show) {
        const input = document.getElementById(inputId);
        const err = document.getElementById(errId);
        if (show) {
          input.classList.add("error");
          err.classList.add("show");
        } else {
          input.classList.remove("error");
          err.classList.remove("show");
        }
        return !show;
      }

      form.addEventListener("submit", function (e) {
        e.preventDefault();

        const name = document.getElementById("fname").value.trim();
        const email = document.getElementById("femail").value.trim();
        const msg = document.getElementById("fmsg").value.trim();

        let ok = true;
        ok = setError("fname", "fname-err", name.length < 2) && ok;
        ok = setError("femail", "femail-err", !validateEmail(email)) && ok;
        ok = setError("fmsg", "fmsg-err", msg.length < 10) && ok;

        if (!ok) return;

        submitBtn.textContent = "Wysyłanie...";
        submitBtn.disabled = true;

        setTimeout(function () {
          submitBtn.textContent = "Wysłano! Odezwę się wkrótce ✓";
          submitBtn.style.background = "var(--teal-dark)";

          setTimeout(function () {
            submitBtn.textContent = "Wyślij i umów rozmowę →";
            submitBtn.style.background = "";
            submitBtn.disabled = false;
            form.reset();
          }, 4500);
        }, 700);
      });

      ["fname", "femail", "fmsg"].forEach((id) => {
        const errId = id + "-err";
        document.getElementById(id).addEventListener("input", function () {
          if (this.classList.contains("error")) {
            if (id === "femail") {
              setError(id, errId, !validateEmail(this.value.trim()));
            } else {
              setError(id, errId, this.value.trim().length < (id === "fmsg" ? 10 : 2));
            }
          }
        });
      });
    })();
  </script>
  <?php wp_footer(); ?>
</body>
</html>

