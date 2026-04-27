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

function upsellio_get_schema_url($value, $fallback = "/")
{
    $value = trim((string) $value);
    if ($value === "") {
        $value = (string) $fallback;
    }
    if (preg_match("#^https?://#i", $value)) {
        return esc_url_raw($value);
    }
    return home_url("/" . ltrim($value, "/"));
}

function upsellio_get_organization_schema_payload()
{
    $organization = function_exists("upsellio_get_organization_schema_config") ? upsellio_get_organization_schema_config() : [];
    $social_profiles = function_exists("upsellio_get_trust_seo_section") ? upsellio_get_trust_seo_section("social_profiles") : [];
    $same_as = [];
    foreach ((array) $social_profiles as $profile_url) {
        $profile_url = esc_url_raw((string) $profile_url);
        if ($profile_url !== "") {
            $same_as[] = $profile_url;
        }
    }

    $logo_url = trim((string) ($organization["logo_url"] ?? ""));
    if ($logo_url === "" && function_exists("upsellio_get_generated_logo_url")) {
        $logo_url = upsellio_get_generated_logo_url("png");
    }

    $payload = [
        "@context" => "https://schema.org",
        "@type" => "Organization",
        "@id" => home_url("/#organization"),
        "name" => trim((string) ($organization["name"] ?? "Upsellio")),
        "url" => upsellio_get_schema_url((string) ($organization["url"] ?? "/")),
        "description" => trim((string) ($organization["description"] ?? "")),
    ];

    $alternate_name = trim((string) ($organization["alternate_name"] ?? ""));
    if ($alternate_name !== "") {
        $payload["alternateName"] = $alternate_name;
    }
    if ($logo_url !== "") {
        $payload["logo"] = esc_url_raw($logo_url);
    }
    $email = trim((string) ($organization["email"] ?? ""));
    $telephone = trim((string) ($organization["telephone"] ?? ""));
    if ($email !== "") {
        $payload["email"] = $email;
    }
    if ($telephone !== "") {
        $payload["contactPoint"] = [
            "@type" => "ContactPoint",
            "telephone" => $telephone,
            "email" => $email,
            "contactType" => "customer support",
            "areaServed" => trim((string) ($organization["area_served"] ?? "PL")),
            "availableLanguage" => ["pl-PL"],
        ];
    }
    $founder_name = trim((string) ($organization["founder_name"] ?? ""));
    if ($founder_name !== "") {
        $payload["founder"] = [
            "@type" => "Person",
            "name" => $founder_name,
        ];
    }
    if (!empty($same_as)) {
        $payload["sameAs"] = array_values(array_unique($same_as));
    }

    return array_filter($payload, static function ($value) {
        return $value !== "" && $value !== [];
    });
}

function upsellio_print_organization_schema()
{
    if (is_admin()) {
        return;
    }
    echo '<script type="application/ld+json">' . wp_json_encode(upsellio_get_organization_schema_payload(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
}
add_action("wp_head", "upsellio_print_organization_schema", 8);

function upsellio_render_faq_schema($faq_items)
{
    $entities = [];
    foreach ((array) $faq_items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $question = trim(wp_strip_all_tags((string) ($item["question"] ?? "")));
        $answer = trim(wp_strip_all_tags((string) ($item["answer"] ?? "")));
        if ($question === "" || $answer === "") {
            continue;
        }
        $entities[] = [
            "@type" => "Question",
            "name" => $question,
            "acceptedAnswer" => [
                "@type" => "Answer",
                "text" => $answer,
            ],
        ];
    }
    if (empty($entities)) {
        return;
    }
    echo '<script type="application/ld+json">' . wp_json_encode([
        "@context" => "https://schema.org",
        "@type" => "FAQPage",
        "mainEntity" => $entities,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
}

function upsellio_get_service_schema_payload($name, $description, $url, $service_type = "")
{
    $organization = upsellio_get_organization_schema_payload();
    $payload = [
        "@context" => "https://schema.org",
        "@type" => "Service",
        "name" => trim((string) $name),
        "description" => trim((string) $description),
        "url" => upsellio_get_schema_url($url),
        "serviceType" => trim((string) $service_type),
        "areaServed" => trim((string) ($organization["contactPoint"]["areaServed"] ?? "PL")),
        "provider" => [
            "@type" => "Organization",
            "@id" => home_url("/#organization"),
            "name" => (string) ($organization["name"] ?? "Upsellio"),
            "url" => (string) ($organization["url"] ?? home_url("/")),
        ],
    ];
    return array_filter($payload, static function ($value) {
        return $value !== "" && $value !== [];
    });
}

function upsellio_render_service_schema($name, $description, $url, $service_type = "")
{
    echo '<script type="application/ld+json">' . wp_json_encode(upsellio_get_service_schema_payload($name, $description, $url, $service_type), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
}

function upsellio_render_breadcrumb_schema($items)
{
    $list_items = [];
    $position = 1;
    foreach ((array) $items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $name = trim((string) ($item["name"] ?? ""));
        $url = trim((string) ($item["url"] ?? ""));
        if ($name === "" || $url === "") {
            continue;
        }
        $list_items[] = [
            "@type" => "ListItem",
            "position" => $position,
            "name" => $name,
            "item" => upsellio_get_schema_url($url),
        ];
        $position++;
    }
    if (empty($list_items)) {
        return;
    }
    echo '<script type="application/ld+json">' . wp_json_encode([
        "@context" => "https://schema.org",
        "@type" => "BreadcrumbList",
        "itemListElement" => $list_items,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
}
