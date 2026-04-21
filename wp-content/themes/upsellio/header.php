<?php
if (!defined("ABSPATH")) {
    exit;
}

$blog_page_id = function_exists("upsellio_get_blog_page_id") ? (int) upsellio_get_blog_page_id() : (int) get_option("page_for_posts");
$blog_index_url = $blog_page_id ? get_permalink($blog_page_id) : home_url("/");
if (!$blog_index_url) {
    $blog_index_url = home_url("/");
}
$is_blog_context = is_home() || is_singular("post") || is_category() || is_tag() || is_search() || is_page_template("page-blog.php");
$is_definitions_context = is_post_type_archive("definicja") || is_singular("definicja");
$is_cities_context = is_post_type_archive("miasto") || is_singular("miasto");
$primary_navigation_links = function_exists("upsellio_get_primary_navigation_links") ? upsellio_get_primary_navigation_links() : [];
$current_request_uri = isset($_SERVER["REQUEST_URI"]) ? (string) wp_unslash($_SERVER["REQUEST_URI"]) : "/";
$current_url = home_url($current_request_uri);
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
    body{margin:0;font-family:var(--font-body);background:var(--bg);color:var(--text);line-height:1.65;-webkit-font-smoothing:antialiased;text-size-adjust:100%}
    a{color:inherit;text-decoration:none}
    .wrap{width:min(var(--container),calc(100% - 32px));margin:0 auto}
    .nav{position:sticky;top:0;z-index:100;background:color-mix(in srgb,var(--bg) 90%,transparent);backdrop-filter:blur(12px);border-bottom:1px solid var(--border)}
    .nav-inner{height:72px;display:flex;align-items:center;justify-content:space-between;gap:var(--sp-3)}
    .brand{display:flex;align-items:center;gap:10px}
    .brand-mark{width:34px;height:34px;border-radius:12px;background:linear-gradient(135deg,var(--teal),var(--teal-dark));display:grid;place-items:center;color:#fff;font-family:var(--font-display);font-weight:800;font-size:15px}
    .brand-text{display:flex;flex-direction:column;line-height:1.05}
    .brand-name{font-family:var(--font-display);font-weight:800;font-size:18px;letter-spacing:-.5px}
    .brand-sub{font-size:11px;color:var(--text-3);margin-top:3px}
    .nav-links{display:none;align-items:center;gap:28px;list-style:none;margin:0;padding:0}
    .nav-links a{font-size:14px;color:var(--text-2);border-bottom:2px solid transparent;padding:4px 0;transition:.18s ease}
    .nav-links a.is-active{color:var(--text);border-bottom-color:var(--teal)}
    .nav-links a:hover{color:var(--text);border-bottom-color:var(--teal)}
    .nav-actions{display:none;align-items:center;gap:var(--sp-2)}
    .nav-cta{display:inline-flex;align-items:center;justify-content:center;min-height:44px;background:var(--teal);color:#fff;padding:10px 18px;border-radius:var(--r-md);font-size:14px;font-weight:600;transition:.18s ease}
    .nav-cta:hover{background:var(--teal-hover);transform:translateY(-1px)}
    .hamburger{display:flex;background:none;border:none;cursor:pointer;padding:4px;flex-direction:column;gap:5px}
    .hamburger span{width:22px;height:2px;background:var(--text);border-radius:2px;transition:.25s ease}
    .hamburger.open span:nth-child(1){transform:translateY(7px) rotate(45deg)}
    .hamburger.open span:nth-child(2){opacity:0}
    .hamburger.open span:nth-child(3){transform:translateY(-7px) rotate(-45deg)}
    .mobile-menu{display:block;max-height:0;overflow:hidden;transition:max-height .35s ease;border-top:1px solid var(--border);background:var(--bg)}
    .mobile-menu.open{max-height:420px}
    .mobile-menu a{display:flex;align-items:center;min-height:48px;padding:15px 0;border-bottom:1px solid var(--border);color:var(--text-2);font-size:15px}
    .mobile-menu a:last-child{color:var(--teal);font-weight:600}
    @media(min-width:761px){
      .wrap{width:min(var(--container),calc(100% - 48px))}
      .nav-links,.nav-actions{display:flex}
      .hamburger,.mobile-menu{display:none}
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
      <?php foreach ($primary_navigation_links as $nav_link) : ?>
        <?php
        $nav_url = (string) $nav_link["url"];
        $is_active = untrailingslashit($nav_url) === untrailingslashit($current_url);
        if (!$is_active && $is_blog_context) {
            $is_active = untrailingslashit($nav_url) === untrailingslashit($blog_index_url);
        }
        if (!$is_active && $is_definitions_context) {
            $is_active = untrailingslashit($nav_url) === untrailingslashit(home_url("/definicje/"));
        }
        if (!$is_active && $is_cities_context) {
            $is_active = untrailingslashit($nav_url) === untrailingslashit(home_url("/miasta/"));
        }
        ?>
        <li>
          <a href="<?php echo esc_url($nav_url); ?>" class="<?php echo $is_active ? "is-active" : ""; ?>" <?php echo $is_active ? 'aria-current="page"' : ""; ?>>
            <?php echo esc_html((string) $nav_link["title"]); ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>

    <div class="nav-actions">
      <a href="<?php echo esc_url(home_url("/#kontakt")); ?>" class="nav-cta">Bezpłatna rozmowa</a>
    </div>

    <button class="hamburger" id="hamburger" aria-label="Otwórz menu">
      <span></span><span></span><span></span>
    </button>
  </div>
  <div class="mobile-menu" id="mobile-menu">
    <div class="wrap">
      <?php foreach ($primary_navigation_links as $nav_link) : ?>
        <a href="<?php echo esc_url((string) $nav_link["url"]); ?>"><?php echo esc_html((string) $nav_link["title"]); ?></a>
      <?php endforeach; ?>
      <a href="<?php echo esc_url(home_url("/#kontakt")); ?>">Bezpłatna rozmowa →</a>
    </div>
  </div>
</header>

