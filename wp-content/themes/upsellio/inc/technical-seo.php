<?php

if (!defined("ABSPATH")) {
    exit;
}

/**
 * Czy Rank Math / Yoast przejmuje title, meta description i canonical (żeby motyw nie duplikował znaczników w head).
 */
function upsellio_is_seo_plugin_managing_frontend_meta(): bool
{
    return function_exists("rank_math") || defined("WPSEO_VERSION") || defined("RANK_MATH_VERSION");
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

/**
 * JSON-LD BlogPosting / Article dla pojedynczego wpisu (E-E-A-T: autor, daty, wydawca).
 *
 * @return array<string, mixed>
 */
function upsellio_get_blogposting_schema_payload(int $post_id): array
{
    $post = get_post($post_id);
    if (!$post instanceof WP_Post || $post->post_type !== "post" || $post->post_status !== "publish") {
        return [];
    }

    $desc = trim((string) get_post_meta($post_id, "_upsellio_meta_description", true));
    if ($desc === "") {
        $raw_ex = trim((string) $post->post_excerpt);
        $desc = $raw_ex !== ""
            ? wp_strip_all_tags($raw_ex)
            : wp_trim_words(wp_strip_all_tags((string) $post->post_content), 55, "…");
    }

    $keywords = trim((string) get_post_meta($post_id, "_upsellio_query_cluster", true));
    if ($keywords === "") {
        $tags = get_the_tags($post_id);
        if (is_array($tags) && $tags !== []) {
            $keywords = implode(", ", array_map(static function ($t) {
                return $t instanceof WP_Term ? $t->name : "";
            }, $tags));
        }
    }

    $img = get_the_post_thumbnail_url($post_id, "large");
    $author_id = (int) $post->post_author;
    $author_name = get_the_author_meta("display_name", $author_id);
    if ($author_name === "") {
        $author_name = __("Autor", "upsellio");
    }

    $org = function_exists("upsellio_get_organization_schema_payload") ? upsellio_get_organization_schema_payload() : [];
    $publisher = [
        "@type" => "Organization",
        "@id" => home_url("/#organization"),
        "name" => (string) ($org["name"] ?? get_bloginfo("name")),
        "url" => (string) ($org["url"] ?? home_url("/")),
    ];
    if (!empty($org["logo"])) {
        $publisher["logo"] = [
            "@type" => "ImageObject",
            "url" => (string) $org["logo"],
        ];
    }

    $permalink = get_permalink($post_id);
    if (!is_string($permalink) || $permalink === "") {
        return [];
    }

    $published = get_post_time("c", true, $post_id);
    $modified = get_post_modified_time("c", true, $post_id);
    if (!is_string($published) || $published === "") {
        $published = gmdate("c", (int) strtotime((string) $post->post_date_gmt));
    }
    if (!is_string($modified) || $modified === "") {
        $modified = $published;
    }

    $payload = [
        "@context" => "https://schema.org",
        "@type" => "BlogPosting",
        "headline" => get_the_title($post_id),
        "description" => $desc,
        "datePublished" => $published,
        "dateModified" => $modified,
        "author" => [
            "@type" => "Person",
            "name" => $author_name,
            "url" => get_author_posts_url($author_id),
        ],
        "publisher" => $publisher,
        "mainEntityOfPage" => [
            "@type" => "WebPage",
            "@id" => $permalink . "#webpage",
        ],
        "url" => $permalink,
    ];

    if ($keywords !== "") {
        $payload["keywords"] = $keywords;
    }

    if (is_string($img) && $img !== "") {
        $payload["image"] = [$img];
    } else {
        $fallback_img = trim((string) get_post_meta($post_id, "_upsellio_featured_image_url", true));
        if ($fallback_img !== "") {
            $payload["image"] = [esc_url_raw($fallback_img)];
        }
    }

    return array_filter($payload, static function ($v) {
        return $v !== "" && $v !== [] && $v !== null;
    });
}

/**
 * FAQ z treści wpisu (sekcja po nagłówku FAQ / Często zadawane).
 *
 * @return list<array{question:string, answer:string}>
 */
function upsellio_extract_faq_from_content(string $content): array
{
    $content = trim($content);
    if ($content === "") {
        return [];
    }
    if (!preg_match('/<h[23][^>]*>(?:[^<]*(?:FAQ|Często zadawane|często zadawane)[^<]*)<\/h[23]>/iu', $content)) {
        return [];
    }
    $parts = preg_split('/<h[23][^>]*>(?:[^<]*(?:FAQ|Często zadawane|często zadawane)[^<]*)<\/h[23]>/iu', $content);
    if (!isset($parts[1]) || !is_string($parts[1])) {
        return [];
    }
    $faq_section = $parts[1];
    if (preg_match('/<h2[^>]*>/i', $faq_section, $m, PREG_OFFSET_CAPTURE)) {
        $faq_section = substr($faq_section, 0, (int) $m[0][1]);
    }
    $items = [];
    preg_match_all(
        '/<h3[^>]*>(.*?)<\/h3>\s*(?:<p[^>]*>(.*?)<\/p>|<div[^>]*>(.*?)<\/div>)/is',
        $faq_section,
        $matches,
        PREG_SET_ORDER
    );
    foreach ($matches as $match) {
        $question = trim(wp_strip_all_tags((string) ($match[1] ?? "")));
        $answer = trim(wp_strip_all_tags((string) (($match[2] ?? "") !== "" ? $match[2] : ($match[3] ?? ""))));
        if ($question !== "" && $answer !== "") {
            $items[] = ["question" => $question, "answer" => $answer];
            if (count($items) >= 10) {
                break;
            }
        }
    }

    return $items;
}

/**
 * BlogPosting + FAQPage + BreadcrumbList dla pojedynczego wpisu.
 */
function upsellio_print_blog_post_schema(): void
{
    if (is_admin()) {
        return;
    }
    if (!apply_filters("upsellio_output_blogposting_jsonld", true)) {
        return;
    }
    if (!is_singular("post")) {
        return;
    }

    $post_id = (int) get_queried_object_id();
    if ($post_id <= 0) {
        return;
    }

    $payload = upsellio_get_blogposting_schema_payload($post_id);
    if ($payload === []) {
        return;
    }

    $content_raw = (string) get_post_field("post_content", $post_id);
    $faq_items = upsellio_extract_faq_from_content($content_raw);
    if ($faq_items !== []) {
        $entities = [];
        foreach ($faq_items as $item) {
            $entities[] = [
                "@type" => "Question",
                "name" => (string) ($item["question"] ?? ""),
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => (string) ($item["answer"] ?? ""),
                ],
            ];
        }
        echo '<script type="application/ld+json">' . wp_json_encode([
            "@context" => "https://schema.org",
            "@type" => "FAQPage",
            "mainEntity" => $entities,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
    }

    echo '<script type="application/ld+json">' . wp_json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";

    $title = get_the_title($post_id);
    $url = get_permalink($post_id);
    if (!is_string($url) || $url === "") {
        return;
    }
    $blog_archive = get_post_type_archive_link("post");
    $blog_archive = is_string($blog_archive) ? $blog_archive : home_url("/");
    $crumbs = [
        ["name" => __("Strona główna", "upsellio"), "url" => home_url("/")],
        ["name" => __("Blog", "upsellio"), "url" => $blog_archive],
    ];
    $categories = get_the_category($post_id);
    if (is_array($categories) && isset($categories[0]) && $categories[0] instanceof WP_Term) {
        $cat = $categories[0];
        $link = get_category_link((int) $cat->term_id);
        if (!is_wp_error($link) && is_string($link) && $link !== "") {
            $crumbs[] = ["name" => (string) $cat->name, "url" => $link];
        }
    }
    $crumbs[] = ["name" => $title, "url" => $url];
    upsellio_render_breadcrumb_schema($crumbs);
}

add_action("wp_head", "upsellio_print_blog_post_schema", 9);

/**
 * BreadcrumbList na stronie szablonu Blog (archiwum bloga jako strona).
 */
function upsellio_print_blog_page_breadcrumb_schema(): void
{
    if (is_admin() || !is_page()) {
        return;
    }
    $tid = (int) get_queried_object_id();
    if ($tid <= 0) {
        return;
    }
    $tpl = get_page_template_slug($tid);
    if ($tpl !== "page-blog.php") {
        return;
    }
    $permalink = get_permalink($tid);
    if (!is_string($permalink) || $permalink === "") {
        return;
    }
    upsellio_render_breadcrumb_schema([
        ["name" => __("Strona główna", "upsellio"), "url" => home_url("/")],
        ["name" => __("Blog", "upsellio"), "url" => $permalink],
    ]);
}

add_action("wp_head", "upsellio_print_blog_page_breadcrumb_schema", 9);

/**
 * Canonical gdy Rank Math / Yoast nie dodają znacznika.
 */
function upsellio_canonical_fallback(): void
{
    if (is_admin()) {
        return;
    }
    if (defined("WPSEO_VERSION") || defined("RANK_MATH_VERSION")) {
        return;
    }

    $canonical = "";
    if (is_singular()) {
        $canonical = (string) get_permalink();
    } elseif (is_category() || is_tag() || is_tax()) {
        $term_link = get_term_link(get_queried_object());
        $canonical = !is_wp_error($term_link) ? (string) $term_link : "";
    } elseif (is_home() && !is_front_page()) {
        $canonical = (string) get_post_type_archive_link("post");
    } elseif (is_front_page()) {
        $canonical = home_url("/");
    }

    $canonical = trim($canonical);
    if ($canonical !== "") {
        echo '<link rel="canonical" href="' . esc_url($canonical) . "\" />\n";
    }
}

add_action("wp_head", "upsellio_canonical_fallback", 1);
