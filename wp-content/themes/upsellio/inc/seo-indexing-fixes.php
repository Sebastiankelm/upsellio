<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_seo_indexing_is_routable_request()
{
    $method = strtoupper((string) ($_SERVER["REQUEST_METHOD"] ?? "GET"));
    if (!in_array($method, ["GET", "HEAD"], true) || is_admin() || wp_doing_ajax() || wp_doing_cron() || is_preview() || is_feed()) {
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

    $tracking = [];
    foreach ($_GET as $key => $value) {
        if (is_string($value) && preg_match('/^(utm_[a-z0-9_]+|gclid|gbraid|wbraid|fbclid)$/D', $key)) {
            $tracking[$key] = wp_unslash($value);
        }
    }
    wp_safe_redirect($tracking ? add_query_arg($tracking, $canonical) : $canonical, 301);
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

// Resolve stable dataset identifiers and WordPress old slugs to a unique
// published object. Never guess a target for an ambiguous or unknown alias.
function upsellio_seo_resolve_public_content_alias($post_type, $slug)
{
    if (!in_array($post_type, ["miasto", "definicja", "portfolio", "marketing_portfolio", "lead_magnet"], true) ||
        !is_string($slug) || !preg_match('/^[a-z0-9-]+$/D', $slug)) {
        return null;
    }
    static $maps = [];
    if (!isset($maps[$post_type])) {
        $maps[$post_type] = ["current" => [], "aliases" => []];
        $posts = get_posts(["post_type" => $post_type, "post_status" => "publish",
            "numberposts" => -1, "orderby" => "ID", "order" => "ASC"]);
        $key = ["miasto" => "_upsellio_city_slug", "definicja" => "_upsellio_definition_slug"][$post_type] ?? "";
        foreach ($posts as $post) {
            if ($post->post_password !== "") {
                continue;
            }
            $maps[$post_type]["current"][$post->post_name] = $post;
            $aliases = get_post_meta($post->ID, "_wp_old_slug", false);
            if ($key !== "") {
                $aliases[] = get_post_meta($post->ID, $key, true);
            }
            foreach (array_unique(array_filter($aliases, "is_string")) as $alias) {
                if ($alias !== "") {
                    $maps[$post_type]["aliases"][$alias][$post->ID] = $post;
                }
            }
        }
    }
    if (isset($maps[$post_type]["current"][$slug])) {
        return $maps[$post_type]["current"][$slug];
    }
    $matches = $maps[$post_type]["aliases"][$slug] ?? [];
    return count($matches) === 1 ? reset($matches) : null;
}

function upsellio_seo_redirect_legacy_content_alias(): void
{
    if (!upsellio_seo_indexing_is_routable_request() || !is_404()) {
        return;
    }
    $path = trim((string) wp_parse_url($_SERVER["REQUEST_URI"] ?? "", PHP_URL_PATH), "/");
    $types = ["miasto" => "miasto", "definicje" => "definicja", "realizacja" => "portfolio",
        "portfolio-marketingowe" => "marketing_portfolio", "lead-magnety" => "lead_magnet"];
    $post = null;
    if (preg_match('~^([^/]+)/([a-z0-9-]+)$~D', $path, $m) && isset($types[$m[1]])) {
        $post = upsellio_seo_resolve_public_content_alias($types[$m[1]], $m[2]);
    } elseif ($path === "") {
        $found = array_intersect(array_values($types), array_keys($_GET));
        if (count($found) === 1) {
            $type = reset($found);
            $post = upsellio_seo_resolve_public_content_alias($type, $_GET[$type]);
        }
    }
    if (!($post instanceof WP_Post)) {
        return;
    }
    $target = get_permalink($post);
    if (!$target) {
        return;
    }
    // Keep attribution parameters, but never forward WordPress routing keys.
    $tracking = [];
    foreach ($_GET as $key => $value) {
        if (is_string($value) && preg_match('/^(utm_[a-z0-9_]+|gclid|gbraid|wbraid|fbclid)$/D', $key)) {
            $tracking[$key] = wp_unslash($value);
        }
    }
    if ($tracking) {
        $target = add_query_arg($tracking, $target);
    }
    wp_safe_redirect($target, 301);
    exit;
}
add_action("template_redirect", "upsellio_seo_redirect_legacy_content_alias", 3);

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
    if (!(is_home() || is_page_template("ups-blog-core.php") || (is_search() && strpos((string) wp_parse_url($_SERVER["REQUEST_URI"] ?? "", PHP_URL_PATH), "/blog/") === 0))) {
        return $robots;
    }
    if (!upsellio_seo_indexing_request_has_blog_filter_params()) {
        return $robots;
    }

    if (!is_array($robots)) {
        $robots = [];
    }
    $robots["index"] = "noindex";
    return $robots;
}
add_filter("rank_math/frontend/robots", "upsellio_seo_indexing_override_robots_meta_for_blog_filters", 20);

// Core wp_robots expects boolean directives, unlike Rank Math's keyed values.
function upsellio_seo_indexing_wp_robots_for_blog_filters($robots)
{
    if (!(is_home() || is_page_template("ups-blog-core.php") || (is_search() && strpos((string) wp_parse_url($_SERVER["REQUEST_URI"] ?? "", PHP_URL_PATH), "/blog/") === 0)) ||
        !upsellio_seo_indexing_request_has_blog_filter_params()) {
        return $robots;
    }
    unset($robots["index"]);
    $robots["noindex"] = true;
    return $robots;
}
add_filter("wp_robots", "upsellio_seo_indexing_wp_robots_for_blog_filters", 20);

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
