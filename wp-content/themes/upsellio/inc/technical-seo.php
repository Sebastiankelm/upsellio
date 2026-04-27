<?php

if (!defined("ABSPATH")) {
    exit;
}
function upsellio_print_hreflang_tags()
{
    if (is_admin()) {
        return;
    }

    $request_uri = isset($_SERVER["REQUEST_URI"]) ? (string) $_SERVER["REQUEST_URI"] : "/";
    $canonical_url = is_front_page() ? home_url("/") : (is_singular() ? get_permalink() : home_url($request_uri));
    if (!is_string($canonical_url) || $canonical_url === "") {
        $canonical_url = home_url("/");
    }

    echo '<link rel="alternate" hreflang="pl-PL" href="' . esc_url($canonical_url) . '">' . "\n";
    echo '<link rel="alternate" hreflang="pl" href="' . esc_url($canonical_url) . '">' . "\n";
    echo '<link rel="alternate" hreflang="x-default" href="' . esc_url($canonical_url) . '">' . "\n";
}
add_action("wp_head", "upsellio_print_hreflang_tags", 9);

function upsellio_get_robots_txt_content()
{
    $lines = [
        "User-agent: *",
        "Allow: /",
        "Disallow: /wp-admin/",
        "Allow: /wp-admin/admin-ajax.php",
        "",
        "Sitemap: " . home_url("/sitemap.xml"),
    ];

    return implode("\n", $lines) . "\n";
}

function upsellio_filter_virtual_robots_txt($output, $is_public)
{
    if ((string) $is_public !== "1") {
        return $output;
    }

    return upsellio_get_robots_txt_content();
}
add_filter("robots_txt", "upsellio_filter_virtual_robots_txt", 20, 2);

function upsellio_output_robots_txt_directly()
{
    if (is_admin()) {
        return;
    }

    echo upsellio_get_robots_txt_content();
}
add_action("do_robots", "upsellio_output_robots_txt_directly", 20);

function upsellio_collect_sitemap_entries()
{
    $entries = [];
    $home_url = home_url("/");
    $entries[$home_url] = [
        "loc" => $home_url,
        "lastmod" => gmdate("c"),
    ];

    $candidate_types = ["page", "post", "portfolio", "marketing_portfolio", "lead_magnet", "definicja", "miasto"];
    $post_types = [];
    foreach ($candidate_types as $post_type) {
        if (post_type_exists($post_type)) {
            $post_types[] = $post_type;
        }
    }

    if (empty($post_types)) {
        return array_values($entries);
    }

    $post_ids = get_posts([
        "post_type" => $post_types,
        "post_status" => "publish",
        "numberposts" => -1,
        "fields" => "ids",
        "orderby" => "modified",
        "order" => "DESC",
        "suppress_filters" => false,
    ]);

    foreach ((array) $post_ids as $post_id) {
        $post_id = (int) $post_id;
        if ($post_id <= 0) {
            continue;
        }

        $permalink = get_permalink($post_id);
        if (!$permalink) {
            continue;
        }

        $modified = get_post_modified_time("c", true, $post_id);
        $entries[$permalink] = [
            "loc" => (string) $permalink,
            "lastmod" => $modified ? (string) $modified : gmdate("c"),
        ];
    }

    return array_values($entries);
}

function upsellio_render_sitemap_xml()
{
    $entries = upsellio_collect_sitemap_entries();
    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
    foreach ($entries as $entry) {
        $xml .= "  <url>\n";
        $xml .= "    <loc>" . esc_url((string) ($entry["loc"] ?? "")) . "</loc>\n";
        $xml .= "    <lastmod>" . esc_html((string) ($entry["lastmod"] ?? gmdate("c"))) . "</lastmod>\n";
        $xml .= "  </url>\n";
    }
    $xml .= "</urlset>\n";

    return $xml;
}

function upsellio_render_llms_txt()
{
    $lines = [
        "# Upsellio",
        "",
        "Upsellio to studio growth marketingu B2B i stron WWW prowadzone przez Sebastiana Kelma.",
        "Specjalizacja: Meta Ads, Google Ads, strony pod konwersję i pozyskiwanie klientów B2B.",
        "",
        "Kontakt: " . home_url("/kontakt/"),
        "Strona glowna: " . home_url("/"),
        "Blog: " . home_url("/blog/"),
        "Definicje: " . home_url("/definicje/"),
        "Miasta: " . home_url("/miasta/"),
        "Portfolio: " . home_url("/portfolio/"),
        "Portfolio marketingowe: " . home_url("/portfolio-marketingowe/"),
        "Lead magnety: " . home_url("/lead-magnety/"),
    ];

    return implode("\n", $lines) . "\n";
}

function upsellio_serve_technical_seo_files()
{
    if (is_admin()) {
        return;
    }

    $request_uri = isset($_SERVER["REQUEST_URI"]) ? (string) $_SERVER["REQUEST_URI"] : "/";
    $request_path = (string) wp_parse_url($request_uri, PHP_URL_PATH);
    if ($request_path === "") {
        $request_path = "/";
    }

    if ($request_path === "/robots.txt") {
        status_header(200);
        header("Content-Type: text/plain; charset=UTF-8");
        echo upsellio_get_robots_txt_content();
        exit;
    }

    if ($request_path === "/llms.txt") {
        status_header(200);
        header("Content-Type: text/plain; charset=UTF-8");
        echo upsellio_render_llms_txt();
        exit;
    }

    if ($request_path === "/sitemap.xml" || $request_path === "/sitemap_index.xml") {
        status_header(200);
        header("Content-Type: application/xml; charset=UTF-8");
        echo upsellio_render_sitemap_xml();
        exit;
    }
}
add_action("template_redirect", "upsellio_serve_technical_seo_files", 1);

function upsellio_serve_technical_seo_files_early($wp)
{
    if (is_admin()) {
        return;
    }

    $request_uri = isset($_SERVER["REQUEST_URI"]) ? (string) $_SERVER["REQUEST_URI"] : "/";
    $request_path = (string) wp_parse_url($request_uri, PHP_URL_PATH);
    if ($request_path === "") {
        $request_path = "/";
    }
    if ($request_path === "/robots.txt") {
        status_header(200);
        header("Content-Type: text/plain; charset=UTF-8");
        echo upsellio_get_robots_txt_content();
        exit;
    }
    if ($request_path === "/sitemap.xml" || $request_path === "/sitemap_index.xml") {
        status_header(200);
        header("Content-Type: application/xml; charset=UTF-8");
        echo upsellio_render_sitemap_xml();
        exit;
    }
}
add_action("parse_request", "upsellio_serve_technical_seo_files_early", 1);
