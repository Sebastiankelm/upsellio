<?php
if (!defined("ABSPATH")) {
    exit;
}

$blog_page_id = function_exists("upsellio_get_blog_page_id") ? (int) upsellio_get_blog_page_id() : (int) get_option("page_for_posts");
$blog_index_url = $blog_page_id ? get_permalink($blog_page_id) : home_url("/");
if (!$blog_index_url) {
    $blog_index_url = home_url("/");
}
$is_homepage_context = function_exists("upsellio_is_homepage_request") ? (bool) upsellio_is_homepage_request() : is_front_page();
$is_blog_context = !$is_homepage_context && (is_home() || is_singular("post") || is_category() || is_tag() || is_search() || is_page_template("page-blog.php"));
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
$contact_phone = function_exists("upsellio_get_contact_phone") ? (string) upsellio_get_contact_phone() : "+48 575 522 595";
$contact_phone_href = preg_replace("/\s+/", "", $contact_phone);
$contact_email = "kontakt@upsellio.pl";
$nav_cta_url = !is_front_page() ? $contact_url : "";
$brand_logo_assets = function_exists("upsellio_get_brand_logo_assets") ? upsellio_get_brand_logo_assets() : [];
$brand_logo_url = (string) ($brand_logo_assets["png"] ?? "");
$brand_logo_webp_320_url = (string) ($brand_logo_assets["webp_320"] ?? "");
$brand_logo_webp_640_url = (string) ($brand_logo_assets["webp_640"] ?? "");
$brand_logo_attachment_id = (int) ($brand_logo_assets["custom_logo_id"] ?? 0);
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
<a class="skip-link" href="#main-content">Przejdź do treści</a>
<header class="nav">
  <div class="nav-topbar" aria-label="Szybki kontakt">
    <div class="wrap nav-topbar-inner">
      <a href="<?php echo esc_url("tel:" . $contact_phone_href); ?>"><?php echo esc_html($contact_phone); ?></a>
      <a href="<?php echo esc_url("mailto:" . $contact_email); ?>"><?php echo esc_html($contact_email); ?></a>
    </div>
  </div>
  <div class="wrap nav-inner">
    <a href="<?php echo esc_url(home_url("/")); ?>" class="brand" aria-label="Upsellio — strona główna">
      <?php if ($brand_logo_url !== "") : ?>
        <?php if ($brand_logo_attachment_id > 0) : ?>
          <?php
          echo wp_get_attachment_image($brand_logo_attachment_id, "medium", false, [
              "class" => "brand-logo",
              "alt" => "Upsellio — kampanie Google Ads i Meta Ads dla firm B2B",
              "loading" => "eager",
              "decoding" => "async",
              "fetchpriority" => "high",
              "sizes" => "(max-width: 760px) 163px, 222px",
          ]);
          ?>
        <?php else : ?>
          <picture>
            <?php if ($brand_logo_webp_320_url !== "" && $brand_logo_webp_640_url !== "") : ?>
              <source type="image/webp" srcset="<?php echo esc_url($brand_logo_webp_320_url); ?> 320w, <?php echo esc_url($brand_logo_webp_640_url); ?> 640w" sizes="(max-width: 760px) 163px, 222px" />
            <?php endif; ?>
            <img src="<?php echo esc_url($brand_logo_url); ?>" alt="Upsellio — kampanie Google Ads i Meta Ads dla firm B2B" class="brand-logo" width="320" height="213" decoding="async" fetchpriority="high" />
          </picture>
        <?php endif; ?>
      <?php else : ?>
        <span class="brand-fallback">Upsellio</span>
      <?php endif; ?>
    </a>

    <ul class="nav-links">
      <?php foreach ($primary_navigation_top as $nav_link) : ?>
        <?php
        $nav_id = (int) ($nav_link["id"] ?? 0);
        $nav_children = ($nav_id > 0 && isset($primary_navigation_children[$nav_id])) ? (array) $primary_navigation_children[$nav_id] : [];
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
        $has_active_child = false;
        foreach ($nav_children as $nav_child) {
            $child_url = (string) ($nav_child["url"] ?? "");
            if ($child_url !== "" && untrailingslashit($child_url) === untrailingslashit($current_url)) {
                $has_active_child = true;
                break;
            }
        }
        $is_item_active = $is_active || $has_active_child;
        ?>
        <?php if (!empty($nav_children)) : ?>
          <li class="nav-dropdown">
            <a href="<?php echo esc_url($nav_url); ?>" class="nav-dropdown-parent <?php echo $is_item_active ? "is-active" : ""; ?>" <?php echo $is_item_active ? 'aria-current="page"' : ""; ?><?php echo ((string) ($nav_link["target"] ?? "") === "_blank") ? ' target="_blank" rel="noopener noreferrer"' : ""; ?>>
              <?php echo esc_html((string) $nav_link["title"]); ?>
            </a>
            <button type="button" class="nav-dropdown-toggle" aria-expanded="false" aria-label="<?php echo esc_attr("Rozwiń podmenu: " . (string) $nav_link["title"]); ?>">▾</button>
            <div class="nav-dropdown-menu">
              <?php foreach ($nav_children as $nav_child) : ?>
                <?php
                $child_url = (string) ($nav_child["url"] ?? "");
                $is_child_active = $child_url !== "" && untrailingslashit($child_url) === untrailingslashit($current_url);
                ?>
                <a href="<?php echo esc_url($child_url); ?>" class="<?php echo $is_child_active ? "is-active" : ""; ?>" <?php echo ((string) ($nav_child["target"] ?? "") === "_blank") ? ' target="_blank" rel="noopener noreferrer"' : ""; ?><?php echo $is_child_active ? ' aria-current="page"' : ""; ?>>
                  <?php echo esc_html((string) ($nav_child["title"] ?? "")); ?>
                </a>
              <?php endforeach; ?>
            </div>
          </li>
        <?php else : ?>
          <li>
            <a href="<?php echo esc_url($nav_url); ?>" class="<?php echo $is_item_active ? "is-active" : ""; ?>" <?php echo $is_item_active ? 'aria-current="page"' : ""; ?><?php echo ((string) ($nav_link["target"] ?? "") === "_blank") ? ' target="_blank" rel="noopener noreferrer"' : ""; ?>>
              <?php echo esc_html((string) $nav_link["title"]); ?>
            </a>
          </li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ul>

    <?php if ($nav_cta_url !== "") : ?>
      <a href="<?php echo esc_url($nav_cta_url); ?>" class="btn btn-primary btn-sm nav-cta" aria-label="Bezpłatna analiza marketingu">
        <span class="nav-cta-long">Bezpłatna analiza</span>
        <span class="nav-cta-short">Bezpłatna analiza</span>
      </a>
    <?php endif; ?>

    <button class="hamburger" id="hamburger" aria-label="Otwórz menu" aria-controls="mobile-menu" aria-expanded="false" type="button">
      <span></span><span></span><span></span>
    </button>
  </div>
  <div class="mobile-menu" id="mobile-menu" role="navigation" aria-label="Menu mobilne">
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
      <?php if ($nav_cta_url !== "") : ?><a href="<?php echo esc_url($nav_cta_url); ?>" class="mobile-menu-cta">Bezpłatna analiza</a><?php endif; ?>
    </div>
  </div>
</header>
<?php if ($nav_cta_url !== "" && (!function_exists("upsellio_is_contact_page_context") || !upsellio_is_contact_page_context())) : ?>
<a href="<?php echo esc_url($nav_cta_url); ?>" class="mobile-sticky-cta">Umów bezpłatną konsultację →</a>
<?php endif; ?>
<?php if (function_exists("upsellio_render_breadcrumbs")) : ?>
<?php echo upsellio_render_breadcrumbs(); ?>
<?php endif; ?>
<div id="main-content" tabindex="-1"></div>

