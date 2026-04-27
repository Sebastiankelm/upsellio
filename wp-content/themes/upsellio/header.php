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
$is_portfolio_context = function_exists("upsellio_is_portfolio_page_context")
    ? upsellio_is_portfolio_page_context()
    : (is_page("portfolio") || is_page_template("page-portfolio.php") || is_singular("portfolio"));
$is_marketing_portfolio_context = function_exists("upsellio_is_marketing_portfolio_page_context")
    ? upsellio_is_marketing_portfolio_page_context()
    : (is_page("portfolio-marketingowe") || is_page_template("page-portfolio-marketingowe.php") || is_singular("marketing_portfolio"));
$is_lead_magnets_context = function_exists("upsellio_is_lead_magnets_page_context")
    ? upsellio_is_lead_magnets_page_context()
    : (is_page("lead-magnety") || is_page_template("page-lead-magnety.php") || is_singular("lead_magnet"));
$is_contact_context = function_exists("upsellio_is_contact_page_context")
    ? upsellio_is_contact_page_context()
    : (is_page("kontakt") || is_page_template("page-kontakt.php"));
$primary_navigation_links = function_exists("upsellio_get_primary_navigation_links") ? upsellio_get_primary_navigation_links() : [];
$current_request_uri = isset($_SERVER["REQUEST_URI"]) ? (string) wp_unslash($_SERVER["REQUEST_URI"]) : "/";
$current_url = home_url($current_request_uri);
$portfolio_url = function_exists("upsellio_get_portfolio_page_url") ? (string) upsellio_get_portfolio_page_url() : home_url("/portfolio/");
$marketing_portfolio_url = function_exists("upsellio_get_marketing_portfolio_page_url") ? (string) upsellio_get_marketing_portfolio_page_url() : home_url("/portfolio-marketingowe/");
$lead_magnets_url = function_exists("upsellio_get_lead_magnets_page_url") ? (string) upsellio_get_lead_magnets_page_url() : home_url("/lead-magnety/");
$contact_url = function_exists("upsellio_get_contact_page_url") ? (string) upsellio_get_contact_page_url() : home_url("/kontakt/");
$brand_logo_assets = function_exists("upsellio_get_generated_logo_assets") ? upsellio_get_generated_logo_assets() : [];
$brand_logo_url = (string) ($brand_logo_assets["png"] ?? "");
$brand_logo_webp_320_url = (string) ($brand_logo_assets["webp_320"] ?? "");
$brand_logo_webp_640_url = (string) ($brand_logo_assets["webp_640"] ?? "");
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo("charset"); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet"></noscript>
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="nav">
  <div class="wrap nav-inner">
    <a href="<?php echo esc_url(home_url("/")); ?>" class="brand" aria-label="Upsellio — strona główna">
      <?php if ($brand_logo_url !== "") : ?>
        <picture>
          <?php if ($brand_logo_webp_320_url !== "" && $brand_logo_webp_640_url !== "") : ?>
            <source type="image/webp" srcset="<?php echo esc_url($brand_logo_webp_320_url); ?> 320w, <?php echo esc_url($brand_logo_webp_640_url); ?> 640w" sizes="(max-width: 760px) 163px, 222px" />
          <?php endif; ?>
          <img src="<?php echo esc_url($brand_logo_url); ?>" alt="Upsellio" class="brand-logo" width="320" height="213" decoding="async" fetchpriority="high" />
        </picture>
      <?php else : ?>
        <span class="brand-fallback">Upsellio</span>
      <?php endif; ?>
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
        if (!$is_active && $is_portfolio_context) {
            $is_active = untrailingslashit($nav_url) === untrailingslashit($portfolio_url);
        }
        if (!$is_active && $is_marketing_portfolio_context) {
            $is_active = untrailingslashit($nav_url) === untrailingslashit($marketing_portfolio_url);
        }
        if (!$is_active && $is_lead_magnets_context) {
            $is_active = untrailingslashit($nav_url) === untrailingslashit($lead_magnets_url);
        }
        if (!$is_active && $is_contact_context) {
            $is_active = untrailingslashit($nav_url) === untrailingslashit($contact_url);
        }
        ?>
        <li>
          <a href="<?php echo esc_url($nav_url); ?>" class="<?php echo $is_active ? "is-active" : ""; ?>" <?php echo $is_active ? 'aria-current="page"' : ""; ?><?php echo ((string) ($nav_link["target"] ?? "") === "_blank") ? ' target="_blank" rel="noopener noreferrer"' : ""; ?>>
            <?php echo esc_html((string) $nav_link["title"]); ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>

    <button class="hamburger" id="hamburger" aria-label="Otwórz menu" aria-controls="mobile-menu" aria-expanded="false" type="button">
      <span></span><span></span><span></span>
    </button>
  </div>
  <div class="mobile-menu" id="mobile-menu" role="navigation" aria-label="Menu mobilne">
    <div class="wrap">
      <?php foreach ($primary_navigation_links as $nav_link) : ?>
        <a href="<?php echo esc_url((string) $nav_link["url"]); ?>"<?php echo ((string) ($nav_link["target"] ?? "") === "_blank") ? ' target="_blank" rel="noopener noreferrer"' : ""; ?>><?php echo esc_html((string) $nav_link["title"]); ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</header>

