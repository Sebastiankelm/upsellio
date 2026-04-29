<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_normalize_internal_redirect_url($raw_url, $fallback_url = "")
{
    $fallback = is_string($fallback_url) && $fallback_url !== "" ? $fallback_url : home_url("/");
    $sanitized = is_string($raw_url) ? esc_url_raw($raw_url) : "";
    if ($sanitized === "") {
        return $fallback;
    }

    $validated = wp_validate_redirect($sanitized, $fallback);
    $target_host = (string) wp_parse_url($validated, PHP_URL_HOST);
    $site_host = (string) wp_parse_url(home_url("/"), PHP_URL_HOST);
    if ($target_host !== "" && $site_host !== "" && strtolower($target_host) !== strtolower($site_host)) {
        return $fallback;
    }

    return $validated;
}

function upsellio_is_homepage_request()
{
    if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
        return false;
    }

    $request_uri = isset($_SERVER["REQUEST_URI"]) ? (string) wp_unslash($_SERVER["REQUEST_URI"]) : "/";
    $request_path = (string) wp_parse_url($request_uri, PHP_URL_PATH);
    $request_query = (string) wp_parse_url($request_uri, PHP_URL_QUERY);
    $home_path = (string) wp_parse_url(home_url("/"), PHP_URL_PATH);

    $request_path = "/" . trim($request_path !== "" ? $request_path : "/", "/");
    $home_path = "/" . trim($home_path !== "" ? $home_path : "/", "/");

    if ($request_path !== $home_path) {
        return false;
    }

    if ($request_query === "") {
        return true;
    }

    parse_str($request_query, $query_vars);
    $routing_query_vars = [
        "attachment_id",
        "author",
        "cat",
        "category_name",
        "definicja",
        "day",
        "feed",
        "lead_magnet",
        "m",
        "marketing_portfolio",
        "miasto",
        "monthnum",
        "name",
        "p",
        "page_id",
        "pagename",
        "portfolio",
        "post_type",
        "preview",
        "rest_route",
        "s",
        "tag",
        "taxonomy",
        "term",
        "year",
    ];

    foreach ($routing_query_vars as $query_var) {
        if (!array_key_exists($query_var, $query_vars)) {
            continue;
        }
        $query_value = $query_vars[$query_var];
        if (!is_scalar($query_value) || trim((string) $query_value) !== "") {
            return false;
        }
    }

    return true;
}

function upsellio_reset_homepage_query_state($query = null)
{
    if (!($query instanceof WP_Query)) {
        global $wp_query;
        $query = $wp_query instanceof WP_Query ? $wp_query : null;
    }
    if (!($query instanceof WP_Query)) {
        return;
    }

    $query->is_404 = false;
    $query->is_home = true;
    $query->is_page = false;
    $query->is_singular = false;
    $query->is_posts_page = false;
}

function upsellio_prevent_homepage_404($preempt, $query)
{
    if (!upsellio_is_homepage_request()) {
        return $preempt;
    }

    upsellio_reset_homepage_query_state($query);
    status_header(200);

    return true;
}
add_filter("pre_handle_404", "upsellio_prevent_homepage_404", 1, 2);

function upsellio_force_homepage_template_on_root($template)
{
    if (!upsellio_is_homepage_request()) {
        return $template;
    }

    $front_page_template = get_template_directory() . "/front-page.php";
    if (!file_exists($front_page_template)) {
        return $template;
    }

    upsellio_reset_homepage_query_state();
    status_header(200);

    return $front_page_template;
}
add_filter("template_include", "upsellio_force_homepage_template_on_root", 1);

function upsellio_is_secure_request()
{
    if (is_ssl()) {
        return true;
    }

    $forwarded_proto = isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) ? strtolower((string) $_SERVER["HTTP_X_FORWARDED_PROTO"]) : "";
    if ($forwarded_proto !== "" && strpos($forwarded_proto, "https") !== false) {
        return true;
    }

    $forwarded_ssl = isset($_SERVER["HTTP_X_FORWARDED_SSL"]) ? strtolower((string) $_SERVER["HTTP_X_FORWARDED_SSL"]) : "";
    if ($forwarded_ssl === "on") {
        return true;
    }

    $cloudflare_visitor = isset($_SERVER["HTTP_CF_VISITOR"]) ? (string) $_SERVER["HTTP_CF_VISITOR"] : "";
    if ($cloudflare_visitor !== "" && stripos($cloudflare_visitor, "\"scheme\":\"https\"") !== false) {
        return true;
    }

    return false;
}

function upsellio_force_https_redirect()
{
    if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
        return;
    }
    if ((defined("REST_REQUEST") && REST_REQUEST) || (defined("WP_CLI") && WP_CLI)) {
        return;
    }
    if (upsellio_is_secure_request()) {
        return;
    }

    $host = isset($_SERVER["HTTP_HOST"]) ? trim((string) $_SERVER["HTTP_HOST"]) : "";
    if ($host === "") {
        return;
    }

    $request_uri = isset($_SERVER["REQUEST_URI"]) ? (string) $_SERVER["REQUEST_URI"] : "/";
    $target_url = "https://" . $host . ($request_uri !== "" ? $request_uri : "/");

    wp_safe_redirect($target_url, 301, "Upsellio HTTPS Redirect");
    exit;
}
add_action("template_redirect", "upsellio_force_https_redirect", 0);
