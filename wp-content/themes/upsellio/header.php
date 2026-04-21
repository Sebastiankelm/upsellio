<?php
if (!defined("ABSPATH")) {
    exit;
}

$blog_page_id = (int) get_option("page_for_posts");
$blog_index_url = $blog_page_id ? get_permalink($blog_page_id) : home_url("/");
if (!$blog_index_url) {
    $blog_index_url = home_url("/");
}
$is_blog_context = is_home() || is_singular("post") || is_category() || is_tag() || is_search();
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo("charset"); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    :root{
      --bg:#ffffff;--bg-soft:#f8f8f6;--surface:#ffffff;--text:#111110;--text-2:#3d3d38;--text-3:#7c7c74;
      --border:#e6e6e1;--border-strong:#c9c9c3;--teal:#1d9e75;--teal-hover:#17885f;--teal-dark:#085041;
      --teal-soft:#e8f8f2;--teal-line:#c3eddd;--r-md:12px;--r-lg:18px;--r-xl:28px;--r-pill:999px;
      --sp-2:16px;--sp-3:24px;--sp-4:32px;--sp-5:40px;--sp-6:48px;--sp-8:64px;--container:1180px;
      --font-display:"Syne",sans-serif;--font-body:"DM Sans",sans-serif;
    }
    *{box-sizing:border-box}
    html{scroll-behavior:smooth}
    body{margin:0;font-family:var(--font-body);background:var(--bg);color:var(--text);line-height:1.65;-webkit-font-smoothing:antialiased}
    a{color:inherit;text-decoration:none}
    .wrap{width:min(var(--container),calc(100% - 48px));margin:0 auto}
    .nav{position:sticky;top:0;z-index:100;background:color-mix(in srgb,var(--bg) 90%,transparent);backdrop-filter:blur(12px);border-bottom:1px solid var(--border)}
    .nav-inner{height:72px;display:flex;align-items:center;justify-content:space-between;gap:var(--sp-3)}
    .brand{display:flex;align-items:center;gap:10px}
    .brand-mark{width:34px;height:34px;border-radius:12px;background:linear-gradient(135deg,var(--teal),var(--teal-dark));display:grid;place-items:center;color:#fff;font-family:var(--font-display);font-weight:800;font-size:15px}
    .brand-text{display:flex;flex-direction:column;line-height:1.05}
    .brand-name{font-family:var(--font-display);font-weight:800;font-size:18px;letter-spacing:-.5px}
    .brand-sub{font-size:11px;color:var(--text-3);margin-top:3px}
    .nav-links{display:flex;align-items:center;gap:28px;list-style:none;margin:0;padding:0}
    .nav-links a{font-size:14px;color:var(--text-2);border-bottom:2px solid transparent;padding:4px 0;transition:.18s ease}
    .nav-links a.is-active{color:var(--text);border-bottom-color:var(--teal)}
    .nav-links a:hover{color:var(--text);border-bottom-color:var(--teal)}
    .nav-actions{display:flex;align-items:center;gap:var(--sp-2)}
    .nav-cta{background:var(--teal);color:#fff;padding:10px 18px;border-radius:var(--r-md);font-size:14px;font-weight:600;transition:.18s ease}
    .nav-cta:hover{background:var(--teal-hover);transform:translateY(-1px)}
    .hamburger{display:none;background:none;border:none;cursor:pointer;padding:4px;flex-direction:column;gap:5px}
    .hamburger span{width:22px;height:2px;background:var(--text);border-radius:2px;transition:.25s ease}
    .hamburger.open span:nth-child(1){transform:translateY(7px) rotate(45deg)}
    .hamburger.open span:nth-child(2){opacity:0}
    .hamburger.open span:nth-child(3){transform:translateY(-7px) rotate(-45deg)}
    .mobile-menu{display:none;max-height:0;overflow:hidden;transition:max-height .35s ease;border-top:1px solid var(--border);background:var(--bg)}
    .mobile-menu.open{max-height:420px}
    .mobile-menu a{display:block;padding:15px 0;border-bottom:1px solid var(--border);color:var(--text-2);font-size:15px}
    .mobile-menu a:last-child{color:var(--teal);font-weight:600}
    @media(max-width:760px){
      .wrap{width:min(var(--container),calc(100% - 32px))}
      .nav-links,.nav-actions{display:none}
      .hamburger{display:flex}
      .mobile-menu{display:block}
    }
  </style>
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="nav">
  <div class="wrap nav-inner">
    <a href="<?php echo esc_url(home_url("/")); ?>" class="brand" aria-label="Upsellio — strona główna">
      <div class="brand-mark">U</div>
      <div class="brand-text">
        <div class="brand-name">Upsellio</div>
        <div class="brand-sub">by Sebastian Kelm</div>
      </div>
    </a>

    <ul class="nav-links">
      <li><a href="<?php echo esc_url(home_url("/#uslugi")); ?>">Usługi</a></li>
      <li><a href="<?php echo esc_url(home_url("/#jak-dzialam")); ?>">Jak działam</a></li>
      <li><a href="<?php echo esc_url(home_url("/#wyniki")); ?>">Wyniki</a></li>
      <li><a href="<?php echo esc_url(home_url("/#faq")); ?>">FAQ</a></li>
      <li><a href="<?php echo esc_url($blog_index_url); ?>" class="<?php echo $is_blog_context ? "is-active" : ""; ?>">Blog</a></li>
      <li><a href="<?php echo esc_url(home_url("/miasta/")); ?>">Miasta</a></li>
    </ul>

    <div class="nav-actions">
      <a href="<?php echo esc_url(home_url("/#kontakt")); ?>" class="nav-cta">Bezpłatna rozmowa</a>
    </div>

    <button class="hamburger" id="upsellio-hamburger" aria-label="Otwórz menu">
      <span></span><span></span><span></span>
    </button>
  </div>
  <div class="mobile-menu" id="upsellio-mobile-menu">
    <div class="wrap">
      <a href="<?php echo esc_url(home_url("/#uslugi")); ?>">Usługi</a>
      <a href="<?php echo esc_url(home_url("/#jak-dzialam")); ?>">Jak działam</a>
      <a href="<?php echo esc_url(home_url("/#wyniki")); ?>">Wyniki</a>
      <a href="<?php echo esc_url(home_url("/#faq")); ?>">FAQ</a>
      <a href="<?php echo esc_url($blog_index_url); ?>">Blog</a>
      <a href="<?php echo esc_url(home_url("/miasta/")); ?>">Miasta</a>
      <a href="<?php echo esc_url(home_url("/#kontakt")); ?>">Bezpłatna rozmowa →</a>
    </div>
  </div>
</header>

