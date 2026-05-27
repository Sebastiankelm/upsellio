<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_seo_indexing_is_routable_request()
{
    if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
        return false;
    }
    if (defined("REST_REQUEST") && REST_REQUEST) {
        return false;
    }
    if (defined("WP_CLI") && WP_CLI) {
        return false;
    }
    return true;
}

function upsellio_seo_indexing_redirect_querystring_cpt_to_permalink()
{
    if (!upsellio_seo_indexing_is_routable_request()) {
        return;
    }
    if (!is_singular()) {
        return;
    }

    $post = get_queried_object();
    if (!($post instanceof WP_Post)) {
        return;
    }

    $managed_cpts = ["miasto", "definicja", "lead_magnet", "portfolio", "marketing_portfolio"];
    if (!in_array($post->post_type, $managed_cpts, true)) {
        return;
    }

    $canonical = get_permalink($post);
    if (!is_string($canonical) || $canonical === "") {
        return;
    }

    $request_uri = isset($_SERVER["REQUEST_URI"]) ? (string) wp_unslash($_SERVER["REQUEST_URI"]) : "";
    $request_path = trim((string) wp_parse_url($request_uri, PHP_URL_PATH), "/");
    $canonical_path = trim((string) wp_parse_url($canonical, PHP_URL_PATH), "/");

    if ($request_path === $canonical_path) {
        return;
    }

    wp_safe_redirect($canonical, 301);
    exit;
}
add_action("template_redirect", "upsellio_seo_indexing_redirect_querystring_cpt_to_permalink", 5);

function upsellio_seo_indexing_legacy_url_redirect_map()
{
    return [
        "/jak-zoptymalizowac-formularz-kontaktowy" => "/formularz-kontaktowy/",
        "/reklamy-meta-nie-sprzedaja" => "/jak-zwiekszyc-konwersje-w-reklamach-meta-przewodnik-dla-firm-b2b/",
        "/miasto/malbork" => "/miasto/google-ads-malbork/",
    ];
}

function upsellio_seo_indexing_apply_legacy_redirects()
{
    if (!upsellio_seo_indexing_is_routable_request()) {
        return;
    }

    $request_uri = isset($_SERVER["REQUEST_URI"]) ? (string) wp_unslash($_SERVER["REQUEST_URI"]) : "";
    $path = rtrim((string) wp_parse_url($request_uri, PHP_URL_PATH), "/");
    if ($path === "") {
        return;
    }

    $map = upsellio_seo_indexing_legacy_url_redirect_map();
    if (!isset($map[$path])) {
        return;
    }

    wp_safe_redirect(home_url($map[$path]), 301);
    exit;
}
add_action("template_redirect", "upsellio_seo_indexing_apply_legacy_redirects", 1);

function upsellio_seo_indexing_blog_filter_params()
{
    return ["s", "category", "tags"];
}

function upsellio_seo_indexing_request_has_blog_filter_params()
{
    foreach (upsellio_seo_indexing_blog_filter_params() as $param) {
        if (isset($_GET[$param])) {
            return true;
        }
    }
    return false;
}

function upsellio_seo_indexing_override_robots_meta_for_blog_filters($robots)
{
    if (!(is_home() || is_page_template("ups-blog-core.php"))) {
        return $robots;
    }
    if (!upsellio_seo_indexing_request_has_blog_filter_params()) {
        return $robots;
    }

    if (!is_array($robots)) {
        $robots = [];
    }
    $robots["index"] = "noindex";
    $robots["follow"] = "follow";

    return $robots;
}
add_filter("wp_robots", "upsellio_seo_indexing_override_robots_meta_for_blog_filters", 20);
add_filter("rank_math/frontend/robots", "upsellio_seo_indexing_override_robots_meta_for_blog_filters", 20);

function upsellio_seo_indexing_noindex_feeds()
{
    if (!is_feed()) {
        return;
    }
    if (headers_sent()) {
        return;
    }
    header("X-Robots-Tag: noindex, follow", true);
}
add_action("template_redirect", "upsellio_seo_indexing_noindex_feeds", 1);
