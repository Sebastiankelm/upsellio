<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_upsert_page_with_template($slug, $title, $template_file)
{
    $slug = trim((string) $slug, "/");
    $title = trim((string) $title);
    $template_file = trim((string) $template_file);
    if ($slug === "") {
        return 0;
    }

    $page = get_page_by_path($slug);
    $page_id = ($page instanceof WP_Post) ? (int) $page->ID : 0;

    if ($page_id > 0) {
        $updates = [
            "ID" => $page_id,
            "post_status" => "publish",
        ];
        $has_changes = false;
        if ($page->post_status !== "publish") {
            $has_changes = true;
        }
        if ($title !== "" && (string) $page->post_title !== $title) {
            $updates["post_title"] = $title;
            $has_changes = true;
        }
        if ($has_changes) {
            wp_update_post($updates);
        }
    } else {
        $created_id = wp_insert_post([
            "post_type" => "page",
            "post_status" => "publish",
            "post_title" => $title !== "" ? $title : ucfirst(str_replace("-", " ", $slug)),
            "post_name" => $slug,
            "post_content" => "",
        ]);
        if (is_wp_error($created_id) || (int) $created_id <= 0) {
            return 0;
        }
        $page_id = (int) $created_id;
    }

    if ($page_id > 0 && $template_file !== "") {
        if ((string) get_post_meta($page_id, "_wp_page_template", true) !== $template_file) {
            update_post_meta($page_id, "_wp_page_template", $template_file);
        }
    }

    return $page_id;
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

function upsellio_obfuscate_email_address($email)
{
    $email = sanitize_email((string) $email);
    if ($email === "") {
        return "";
    }

    return antispambot($email);
}

function upsellio_limit_meta_description($description, $max_length = 130)
{
    $description = trim(preg_replace("/\s+/", " ", wp_strip_all_tags((string) $description)));
    $max_length = max(70, (int) $max_length);
    if ($description === "" || upsellio_strlen($description) <= $max_length) {
        return $description;
    }

    $truncated = function_exists("mb_substr")
        ? mb_substr($description, 0, $max_length)
        : substr($description, 0, $max_length);
    $last_space = function_exists("mb_strrpos")
        ? mb_strrpos($truncated, " ")
        : strrpos($truncated, " ");
    if ($last_space !== false) {
        $truncated = function_exists("mb_substr")
            ? mb_substr($truncated, 0, (int) $last_space)
            : substr($truncated, 0, (int) $last_space);
    }

    return rtrim($truncated, " ,.;:-");
}

function upsellio_get_mailto_href($email)
{
    $obfuscated = upsellio_obfuscate_email_address($email);
    if ($obfuscated === "") {
        return "";
    }

    return "mailto:" . $obfuscated;
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
        "Specjalizacja: Meta Ads, Google Ads, strony pod konwersje, lead generation B2B.",
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
    $request_path = trim((string) wp_parse_url($request_uri, PHP_URL_PATH));
    if ($request_path === "") {
        return;
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
    $request_path = trim((string) wp_parse_url($request_uri, PHP_URL_PATH));
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

function upsellio_is_strict_custom_embed_mode()
{
    return (bool) apply_filters("upsellio_strict_custom_embed_mode", true);
}

function upsellio_get_custom_embed_allowed_html()
{
    return [
        "div" => ["class" => true, "id" => true, "aria-label" => true, "aria-hidden" => true, "data-*"=> true],
        "section" => ["class" => true, "id" => true, "aria-label" => true, "data-*"=> true],
        "article" => ["class" => true, "id" => true],
        "p" => ["class" => true],
        "span" => ["class" => true, "id" => true],
        "strong" => ["class" => true],
        "em" => ["class" => true],
        "small" => ["class" => true],
        "br" => [],
        "h2" => ["class" => true, "id" => true],
        "h3" => ["class" => true, "id" => true],
        "h4" => ["class" => true, "id" => true],
        "ul" => ["class" => true],
        "ol" => ["class" => true],
        "li" => ["class" => true],
        "a" => ["class" => true, "href" => true, "target" => true, "rel" => true, "aria-label" => true],
        "img" => ["class" => true, "src" => true, "alt" => true, "width" => true, "height" => true, "loading" => true],
        "button" => ["class" => true, "type" => true, "aria-label" => true],
    ];
}

function upsellio_sanitize_custom_embed_html($html)
{
    $html = (string) $html;
    if ($html === "") {
        return "";
    }
    return wp_kses($html, upsellio_get_custom_embed_allowed_html());
}

function upsellio_sanitize_custom_embed_css($css)
{
    $css = wp_strip_all_tags((string) $css, false);
    if ($css === "") {
        return "";
    }
    $css = preg_replace("/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/", "", $css);
    $css = preg_replace("/@import/i", "", $css);
    $css = preg_replace("/expression\s*\(/i", "", $css);
    $css = preg_replace("/javascript\s*:/i", "", $css);
    $css = preg_replace("/behavior\s*:/i", "", $css);
    $css = preg_replace("/url\s*\(\s*['\"]?\s*javascript\s*:/i", "url(", $css);
    return trim((string) $css);
}

function upsellio_prepare_custom_embed_payload($custom_html, $custom_css, $custom_js)
{
    $strict_mode = upsellio_is_strict_custom_embed_mode();
    $sanitized_html = upsellio_sanitize_custom_embed_html((string) $custom_html);
    $sanitized_css = upsellio_sanitize_custom_embed_css((string) $custom_css);
    $sanitized_js = $strict_mode ? "" : wp_strip_all_tags((string) $custom_js, false);

    return [
        "html" => $sanitized_html,
        "css" => $sanitized_css,
        "js" => trim((string) $sanitized_js),
    ];
}

function upsellio_run_custom_embed_safety_migration()
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }
    if (get_option("upsellio_custom_embed_safety_migrated_v1")) {
        return;
    }

    $config = [
        "lead_magnet" => ["html" => "_ups_lm_custom_html", "css" => "_ups_lm_custom_css", "js" => "_ups_lm_custom_js"],
        "portfolio" => ["html" => "_ups_port_custom_html", "css" => "_ups_port_custom_css", "js" => "_ups_port_custom_js"],
        "marketing_portfolio" => ["html" => "_ups_mport_custom_html", "css" => "_ups_mport_custom_css", "js" => "_ups_mport_custom_js"],
    ];

    foreach ($config as $post_type => $meta_map) {
        $ids = get_posts([
            "post_type" => $post_type,
            "post_status" => ["publish", "draft", "pending", "private", "future"],
            "numberposts" => 400,
            "fields" => "ids",
            "orderby" => "ID",
            "order" => "ASC",
        ]);
        foreach ((array) $ids as $post_id) {
            $post_id = (int) $post_id;
            if ($post_id <= 0) {
                continue;
            }
            $payload = upsellio_prepare_custom_embed_payload(
                (string) get_post_meta($post_id, (string) $meta_map["html"], true),
                (string) get_post_meta($post_id, (string) $meta_map["css"], true),
                (string) get_post_meta($post_id, (string) $meta_map["js"], true)
            );
            update_post_meta($post_id, (string) $meta_map["html"], (string) $payload["html"]);
            update_post_meta($post_id, (string) $meta_map["css"], (string) $payload["css"]);
            update_post_meta($post_id, (string) $meta_map["js"], (string) $payload["js"]);
        }
    }

    update_option("upsellio_custom_embed_safety_migrated_v1", current_time("mysql"), false);
}
add_action("admin_init", "upsellio_run_custom_embed_safety_migration");

function upsellio_ensure_contact_page_exists()
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }

    $contact_path = function_exists("upsellio_get_special_navigation_path_by_title")
        ? upsellio_get_special_navigation_path_by_title("Kontakt", "/kontakt/")
        : "/kontakt/";
    $contact_slug = trim((string) wp_parse_url($contact_path, PHP_URL_PATH), "/");
    if ($contact_slug === "") {
        $contact_slug = "kontakt";
    }
    upsellio_upsert_page_with_template($contact_slug, "Kontakt", "page-kontakt.php");
}
add_action("admin_init", "upsellio_ensure_contact_page_exists");

function upsellio_ensure_front_page_exists()
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }

    $front_page_id = (int) get_option("page_on_front");
    $front_page = $front_page_id > 0 ? get_post($front_page_id) : null;

    if (!($front_page instanceof WP_Post) || $front_page->post_type !== "page" || $front_page->post_status === "trash") {
        $candidate_slugs = ["strona-glowna", "home", "start", "glowna"];
        $front_page = null;
        foreach ($candidate_slugs as $candidate_slug) {
            $candidate_page = get_page_by_path($candidate_slug);
            if ($candidate_page instanceof WP_Post) {
                $front_page = $candidate_page;
                break;
            }
        }
        if (!($front_page instanceof WP_Post)) {
            $created_id = wp_insert_post([
                "post_type" => "page",
                "post_status" => "publish",
                "post_title" => "Strona glowna",
                "post_name" => "strona-glowna",
                "post_content" => "",
            ]);
            if (!is_wp_error($created_id) && (int) $created_id > 0) {
                $front_page = get_post((int) $created_id);
            }
        }
    }

    if (!($front_page instanceof WP_Post)) {
        return;
    }

    $front_page_id = upsellio_upsert_page_with_template((string) $front_page->post_name, (string) $front_page->post_title, "front-page.php");
    if ($front_page_id <= 0) {
        return;
    }

    update_option("show_on_front", "page");
    update_option("page_on_front", $front_page_id);
}
add_action("admin_init", "upsellio_ensure_front_page_exists");

function upsellio_is_special_page_context($title, $default_path, $template_file)
{
    $template_file = (string) $template_file;
    if ($template_file !== "" && is_page_template($template_file)) {
        return true;
    }

    $path = function_exists("upsellio_get_special_navigation_path_by_title")
        ? (string) upsellio_get_special_navigation_path_by_title((string) $title, (string) $default_path)
        : (string) $default_path;
    $slug = trim((string) wp_parse_url($path, PHP_URL_PATH), "/");
    if ($slug === "") {
        return false;
    }

    $page = get_page_by_path($slug);
    if ($page instanceof WP_Post) {
        return is_page((int) $page->ID);
    }

    return is_page($slug);
}

function upsellio_is_contact_page_context()
{
    return upsellio_is_special_page_context("Kontakt", "/kontakt/", "page-kontakt.php");
}

function upsellio_is_portfolio_page_context()
{
    return is_singular("portfolio") || upsellio_is_special_page_context("Portfolio", "/portfolio/", "page-portfolio.php");
}

function upsellio_is_marketing_portfolio_page_context()
{
    return is_singular("marketing_portfolio") || upsellio_is_special_page_context("Portfolio marketingowe", "/portfolio-marketingowe/", "page-portfolio-marketingowe.php");
}

function upsellio_is_lead_magnets_page_context()
{
    return is_singular("lead_magnet") || upsellio_is_special_page_context("Lead magnety", "/lead-magnety/", "page-lead-magnety.php");
}

function upsellio_get_contact_page_url()
{
    $contact_path = function_exists("upsellio_get_special_navigation_path_by_title")
        ? upsellio_get_special_navigation_path_by_title("Kontakt", "/kontakt/")
        : "/kontakt/";
    $contact_slug = trim((string) wp_parse_url($contact_path, PHP_URL_PATH), "/");
    if ($contact_slug !== "") {
        $contact_page = get_page_by_path($contact_slug);
        if ($contact_page instanceof WP_Post) {
            $page_permalink = get_permalink((int) $contact_page->ID);
            if (is_string($page_permalink) && $page_permalink !== "") {
                return $page_permalink;
            }
        }
    }

    return home_url("/#kontakt");
}

function upsellio_get_definitions_archive_url()
{
    $archive_url = get_post_type_archive_link("definicja");
    if (is_string($archive_url) && $archive_url !== "") {
        return $archive_url;
    }

    return add_query_arg("post_type", "definicja", home_url("/"));
}

function upsellio_get_cities_archive_url()
{
    $archive_url = get_post_type_archive_link("miasto");
    if (is_string($archive_url) && $archive_url !== "") {
        return $archive_url;
    }

    return add_query_arg("post_type", "miasto", home_url("/"));
}

function upsellio_get_social_profile_url($network)
{
    $network = strtolower(trim((string) $network));
    $defaults = [
        "linkedin" => "https://www.linkedin.com/in/sebastiankelm/",
        "facebook" => "https://www.facebook.com/upsellio.pl",
        "instagram" => "https://www.instagram.com/upsellio.pl/",
        "x" => "https://x.com/upsellio",
        "youtube" => "https://www.youtube.com/@upsellio",
    ];
    if (!isset($defaults[$network])) {
        return "";
    }

    $option_key = "upsellio_social_" . $network . "_url";
    $env_key = "UPSELLIO_SOCIAL_" . strtoupper($network) . "_URL";
    $value = trim((string) get_option($option_key, ""));
    if ($value === "") {
        $value = trim((string) getenv($env_key));
    }
    if ($value === "") {
        $value = (string) $defaults[$network];
    }

    $value = esc_url_raw($value);
    return $value !== "" ? $value : (string) $defaults[$network];
}

function upsellio_get_footer_popular_definitions_links($limit = 12)
{
    $limit = max(1, (int) $limit);
    $posts = get_posts([
        "post_type" => "definicja",
        "post_status" => "publish",
        "numberposts" => $limit,
        "orderby" => "date",
        "order" => "DESC",
    ]);
    $links = [];
    foreach ((array) $posts as $post_item) {
        $post_id = (int) $post_item->ID;
        $links[] = [
            "label" => (string) (get_post_meta($post_id, "_upsellio_definition_term", true) ?: get_the_title($post_id)),
            "url" => (string) get_permalink($post_id),
        ];
    }
    return $links;
}

function upsellio_get_footer_city_links($limit = 54)
{
    $limit = max(16, (int) $limit);
    $ids = get_posts([
        "post_type" => "miasto",
        "post_status" => "publish",
        "numberposts" => $limit,
        "orderby" => "title",
        "order" => "ASC",
        "fields" => "ids",
    ]);
    $links = [];
    if (!empty($ids)) {
        foreach ((array) $ids as $city_id) {
            $city_id = (int) $city_id;
            $links[] = [
                "label" => "Marketing i strony WWW " . (string) (get_post_meta($city_id, "_upsellio_city_name", true) ?: get_the_title($city_id)),
                "url" => (string) get_permalink($city_id),
            ];
        }
        return $links;
    }

    foreach (array_slice((array) upsellio_get_cities_dataset(), 0, $limit) as $city_item) {
        $links[] = [
            "label" => "Marketing i strony WWW " . (string) ($city_item["name"] ?? ""),
            "url" => home_url("/miasto/" . (string) ($city_item["slug"] ?? "") . "/"),
        ];
    }
    return $links;
}

function upsellio_render_unified_footer($args = [])
{
    $args = is_array($args) ? $args : [];
    $contact_email = isset($args["contact_email"]) && is_email((string) $args["contact_email"])
        ? (string) $args["contact_email"]
        : "kontakt@upsellio.pl";
    $contact_phone = function_exists("upsellio_get_contact_phone") ? (string) upsellio_get_contact_phone() : "+48 575 522 595";
    $contact_phone_href = preg_replace("/\s+/", "", $contact_phone);
    $definitions = upsellio_get_footer_popular_definitions_links(12);
    $cities = upsellio_get_footer_city_links(54);
    $cities_visible = array_slice($cities, 0, 16);
    $cities_hidden = array_slice($cities, 16);
    $component_id = "ups-footer-" . wp_generate_password(6, false, false);

    $portfolio_url = function_exists("upsellio_get_portfolio_page_url") ? (string) upsellio_get_portfolio_page_url() : home_url("/portfolio/");
    $marketing_portfolio_url = function_exists("upsellio_get_marketing_portfolio_page_url") ? (string) upsellio_get_marketing_portfolio_page_url() : home_url("/portfolio-marketingowe/");
    $lead_magnets_url = function_exists("upsellio_get_lead_magnets_page_url") ? (string) upsellio_get_lead_magnets_page_url() : home_url("/lead-magnety/");
    $contact_url = function_exists("upsellio_get_contact_page_url") ? (string) upsellio_get_contact_page_url() : home_url("/kontakt/");
    $definitions_url = upsellio_get_definitions_archive_url();
    $cities_url = upsellio_get_cities_archive_url();
    $blog_page_id = function_exists("upsellio_get_blog_page_id") ? (int) upsellio_get_blog_page_id() : (int) get_option("page_for_posts");
    $blog_url = $blog_page_id > 0 ? (string) get_permalink($blog_page_id) : home_url("/blog/");
    $contact_email_href = upsellio_get_mailto_href($contact_email);
    $contact_email_display = upsellio_obfuscate_email_address($contact_email);
    $linkedin_url = upsellio_get_social_profile_url("linkedin");
    $facebook_url = upsellio_get_social_profile_url("facebook");
    $instagram_url = upsellio_get_social_profile_url("instagram");
    $x_url = upsellio_get_social_profile_url("x");
    $youtube_url = upsellio_get_social_profile_url("youtube");

    ob_start();
    ?>
    <footer class="ups-footer" aria-labelledby="ups-footer-title" id="<?php echo esc_attr($component_id); ?>">
      <?php if (false) : ?><style>
        .ups-footer{--uf-bg:#f8f8f5;--uf-surface:#fff;--uf-border:#e6e7df;--uf-border-strong:#cfd2c7;--uf-text:#111;--uf-text-2:#4d524b;--uf-text-3:#71776f;--uf-teal:#1d9e75;--uf-teal-dark:#15785a;--uf-shadow:0 20px 50px rgba(18,24,18,.06);--uf-radius:28px;padding:72px 0 28px;border-top:1px solid var(--uf-border);background:radial-gradient(circle at top left, rgba(29,158,117,.09), transparent 24%),linear-gradient(180deg,#fcfcfa 0%,#f6f6f2 100%);color:var(--uf-text)}
        .ups-footer .wrap{max-width:1240px;margin:0 auto;padding:0 20px}.ups-footer__top{display:grid;grid-template-columns:minmax(320px,1.05fr) minmax(0,1.2fr);gap:24px;align-items:start}
        .ups-footer__brand,.ups-footer__cols,.ups-footer__definitions,.ups-footer__cities{background:var(--uf-surface);border:1px solid var(--uf-border);border-radius:var(--uf-radius);box-shadow:var(--uf-shadow)}
        .ups-footer__brand{padding:28px}.ups-footer__logo{display:inline-flex;align-items:center;gap:14px;text-decoration:none;color:inherit}.ups-footer__logo-mark{width:44px;height:44px;border-radius:14px;display:flex;align-items:center;justify-content:center;background:linear-gradient(180deg,#21ab82 0%,#177358 100%);color:#fff;font-family:Syne,sans-serif;font-weight:800;font-size:20px;box-shadow:0 10px 24px rgba(29,158,117,.22)}
        .ups-footer__logo-copy{display:flex;flex-direction:column;line-height:1.1}.ups-footer__logo-name{font-family:Syne,sans-serif;font-size:24px;font-weight:800;letter-spacing:-.03em}.ups-footer__logo-sub{margin-top:4px;font-size:13px;color:var(--uf-text-3)}.ups-footer__lead{margin:18px 0 0;max-width:58ch;font-size:15px;line-height:1.8;color:var(--uf-text-2)}
        .ups-footer__trust{display:grid;gap:12px;margin-top:22px}.ups-footer__trust-item{padding:14px 16px;border:1px solid var(--uf-border);border-radius:18px;background:#fbfcfa}.ups-footer__trust-item strong{display:block;font-size:14px;line-height:1.35}.ups-footer__trust-item span{display:block;margin-top:4px;font-size:13px;line-height:1.6;color:var(--uf-text-3)}
        .ups-footer__cta{margin-top:24px;padding:22px;border-radius:22px;border:1px solid rgba(29,158,117,.18);background:linear-gradient(180deg, rgba(29,158,117,.08), rgba(255,255,255,.98))}.ups-footer__cta-copy h2{margin:0;font-family:Syne,sans-serif;font-size:24px;line-height:1.05;letter-spacing:-.03em}.ups-footer__cta-copy p{margin:10px 0 0;font-size:14px;line-height:1.75;color:var(--uf-text-2)}
        .ups-footer__cta-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:16px}.ups-footer__btn{display:inline-flex;align-items:center;justify-content:center;min-height:46px;padding:0 16px;border-radius:999px;font-size:14px;font-weight:700;text-decoration:none;transition:.2s ease}.ups-footer__btn--primary{background:var(--uf-teal);border:1px solid var(--uf-teal);color:#fff}.ups-footer__btn--secondary{background:#fff;border:1px solid var(--uf-border-strong);color:var(--uf-text)}
        .ups-footer__cols{padding:28px;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:22px}.ups-footer__heading{margin:0 0 14px;font-size:12px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--uf-text-3)}.ups-footer__links{list-style:none;margin:0;padding:0;display:grid;gap:10px}
        .ups-footer__links a,.ups-footer__contact a,.ups-footer__cities-grid a,.ups-footer__section-link,.ups-footer__legal a{color:var(--uf-text-2);text-decoration:none}.ups-footer__links a:hover,.ups-footer__contact a:hover,.ups-footer__cities-grid a:hover,.ups-footer__section-link:hover,.ups-footer__legal a:hover{color:var(--uf-teal)}
        .ups-footer__contact{font-style:normal;display:grid;gap:10px}.ups-footer__mini-box{margin-top:16px;padding:14px 16px;border-radius:18px;background:#fafbf9;border:1px solid var(--uf-border)}.ups-footer__mini-box p{margin:6px 0 0;font-size:13px;line-height:1.65;color:var(--uf-text-3)}
        .ups-footer__definitions,.ups-footer__cities{margin-top:22px;padding:26px 28px}.ups-footer__section-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:18px}.ups-footer__section-head--cities{align-items:center}.ups-footer__section-title{margin:0;font-family:Syne,sans-serif;font-size:22px;line-height:1.08;letter-spacing:-.03em}.ups-footer__section-sub{margin:8px 0 0;font-size:14px;line-height:1.7;color:var(--uf-text-3)}
        .ups-footer__chips{display:flex;flex-wrap:wrap;gap:10px}.ups-footer__chips a{display:inline-flex;align-items:center;min-height:38px;padding:0 14px;border-radius:999px;border:1px solid var(--uf-border);background:#f7f8f4;font-size:13px}
        .ups-footer__toggle{appearance:none;border:1px solid var(--uf-border-strong);background:#fff;color:var(--uf-text);min-height:42px;padding:0 14px;border-radius:999px;font-size:13px;font-weight:700;cursor:pointer}
        .ups-footer__cities-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px 18px}.ups-footer__cities-grid a{display:block;font-size:13px;line-height:1.55;color:var(--uf-text-3)}.ups-footer__cities-more{margin-top:18px;padding-top:18px;border-top:1px solid var(--uf-border)}
        .ups-footer__bottom{margin-top:22px;padding-top:18px;border-top:1px solid var(--uf-border);display:flex;justify-content:space-between;align-items:center;gap:12px 24px;flex-wrap:wrap}.ups-footer__copyright{margin:0;font-size:12px;line-height:1.6;color:var(--uf-text-3)}.ups-footer__legal{display:flex;flex-wrap:wrap;gap:16px}
        @media(max-width:1180px){.ups-footer__cols{grid-template-columns:repeat(2,minmax(0,1fr))}.ups-footer__cities-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}@media(max-width:920px){.ups-footer__top{grid-template-columns:1fr}}@media(max-width:720px){.ups-footer{padding:56px 0 24px}.ups-footer__brand,.ups-footer__cols,.ups-footer__definitions,.ups-footer__cities{padding:20px;border-radius:22px}.ups-footer__cols{grid-template-columns:1fr;gap:20px}.ups-footer__cities-grid{grid-template-columns:1fr}.ups-footer__section-title,.ups-footer__cta-copy h2{font-size:20px}.ups-footer__btn{width:100%}}
      </style><?php endif; ?>
      <div class="wrap">
        <div class="ups-footer__top">
          <section class="ups-footer__brand" aria-label="O marce Upsellio">
            <a href="<?php echo esc_url(home_url("/")); ?>" class="ups-footer__logo" aria-label="Upsellio - strona glowna">
              <span class="ups-footer__logo-mark" aria-hidden="true">U</span>
              <span class="ups-footer__logo-copy">
                <span class="ups-footer__logo-name" id="ups-footer-title">Upsellio</span>
                <span class="ups-footer__logo-sub">by Sebastian Kelm</span>
              </span>
            </a>
            <p class="ups-footer__lead">Marketing internetowy B2B, kampanie Meta Ads i Google Ads dla firm oraz strony internetowe dla firm B2B, ktore wspieraja pozyskiwanie klientow i wzrost sprzedazy.</p>
            <div class="ups-footer__trust">
              <div class="ups-footer__trust-item"><strong>10 lat praktyki w sprzedazy B2B</strong><span>Praktyk, nie konsultant z prezentacji.</span></div>
              <div class="ups-footer__trust-item"><strong>Jeden punkt kontaktu</strong><span>Bez posrednikow, bez agencyjnego chaosu.</span></div>
              <div class="ups-footer__trust-item"><strong>Cel: leady i sprzedaz</strong><span>Nie zasiegi dla samego zasiegu.</span></div>
            </div>
            <div class="ups-footer__cta">
              <div class="ups-footer__cta-copy"><h2>Chcesz sprawdzic, co blokuje wzrost leadow lub sprzedazy?</h2><p>Opisz krotko firme, oferte i problem. Wroce z konkretna rekomendacja, od czego zaczac i gdzie uciekaja wyniki.</p></div>
              <div class="ups-footer__cta-actions">
                <a href="<?php echo esc_url($contact_url); ?>" class="ups-footer__btn ups-footer__btn--primary">Umow bezplatna rozmowe</a>
                <a href="<?php echo esc_url($contact_email_href); ?>" class="ups-footer__btn ups-footer__btn--secondary"><?php echo esc_html($contact_email_display); ?></a>
              </div>
            </div>
          </section>
          <div class="ups-footer__cols">
            <nav class="ups-footer__col" aria-label="Nawigacja glowna"><h2 class="ups-footer__heading">Nawigacja</h2><ul class="ups-footer__links"><li><a href="<?php echo esc_url(home_url("/")); ?>">Strona glowna</a></li><li><a href="<?php echo esc_url($blog_url); ?>">Blog</a></li><li><a href="<?php echo esc_url($definitions_url); ?>">Baza wiedzy</a></li><li><a href="<?php echo esc_url($portfolio_url); ?>">Portfolio</a></li><li><a href="<?php echo esc_url($marketing_portfolio_url); ?>">Portfolio marketingowe</a></li><li><a href="<?php echo esc_url($lead_magnets_url); ?>">Lead magnety</a></li><li><a href="<?php echo esc_url($contact_url); ?>">Kontakt</a></li></ul></nav>
            <nav class="ups-footer__col" aria-label="Uslugi Upsellio"><h2 class="ups-footer__heading">Uslugi</h2><ul class="ups-footer__links"><li><a href="<?php echo esc_url(home_url("/#uslugi")); ?>">Marketing internetowy B2B</a></li><li><a href="<?php echo esc_url(home_url("/#uslugi")); ?>">Kampanie Meta Ads dla firm</a></li><li><a href="<?php echo esc_url(home_url("/#uslugi")); ?>">Google Ads dla firm B2B</a></li><li><a href="<?php echo esc_url(home_url("/#uslugi")); ?>">Strony internetowe dla firm B2B</a></li><li><a href="<?php echo esc_url(home_url("/#uslugi")); ?>">Landing page pod konwersje</a></li><li><a href="<?php echo esc_url(home_url("/#uslugi")); ?>">Doradztwo sprzedazowe</a></li></ul></nav>
            <nav class="ups-footer__col" aria-label="Zasoby i wiedza"><h2 class="ups-footer__heading">Wiedza</h2><ul class="ups-footer__links"><li><a href="<?php echo esc_url($definitions_url); ?>">Slownik pojec marketingowych</a></li><li><a href="<?php echo esc_url($lead_magnets_url); ?>">Materialy do pobrania</a></li><li><a href="<?php echo esc_url($portfolio_url); ?>">Realizacje stron i sklepow</a></li><li><a href="<?php echo esc_url($marketing_portfolio_url); ?>">Case studies marketingowe</a></li><li><a href="<?php echo esc_url($cities_url); ?>">Obslugiwane miasta</a></li></ul></nav>
            <section class="ups-footer__col" aria-labelledby="ups-footer-contact-heading"><h2 class="ups-footer__heading" id="ups-footer-contact-heading">Kontakt</h2><address class="ups-footer__contact"><a href="<?php echo esc_url($contact_email_href); ?>"><?php echo esc_html($contact_email_display); ?></a><a href="<?php echo esc_url("tel:" . $contact_phone_href); ?>"><?php echo esc_html($contact_phone); ?></a><a href="<?php echo esc_url($linkedin_url); ?>" target="_blank" rel="noopener noreferrer">LinkedIn</a><a href="<?php echo esc_url($facebook_url); ?>" target="_blank" rel="noopener noreferrer">Facebook</a><a href="<?php echo esc_url($instagram_url); ?>" target="_blank" rel="noopener noreferrer">Instagram</a><a href="<?php echo esc_url($x_url); ?>" target="_blank" rel="noopener noreferrer">X</a><a href="<?php echo esc_url($youtube_url); ?>" target="_blank" rel="noopener noreferrer">YouTube</a></address><div class="ups-footer__mini-box"><strong>Pracujesz bezposrednio ze mna</strong><p>Przechodzimy od razu do konkretow: lead generation B2B, konwersja strony i wzrost sprzedazy.</p></div></section>
          </div>
        </div>
        <?php if (!empty($definitions)) : ?>
        <section class="ups-footer__definitions" aria-labelledby="ups-footer-definitions-heading"><div class="ups-footer__section-head"><div><h2 class="ups-footer__section-title" id="ups-footer-definitions-heading">Popularne definicje</h2><p class="ups-footer__section-sub">Krotkie pojecia z obszaru marketingu, analityki i sprzedazy B2B.</p></div><a href="<?php echo esc_url($definitions_url); ?>" class="ups-footer__section-link">Zobacz cala baze wiedzy</a></div><div class="ups-footer__chips"><?php foreach ($definitions as $item) : ?><a href="<?php echo esc_url((string) $item["url"]); ?>"><?php echo esc_html((string) $item["label"]); ?></a><?php endforeach; ?></div></section>
        <?php endif; ?>
        <section class="ups-footer__cities" aria-labelledby="ups-footer-cities-heading"><div class="ups-footer__section-head ups-footer__section-head--cities"><div><h2 class="ups-footer__section-title" id="ups-footer-cities-heading">Uslugi w najwiekszych miastach Polski</h2><p class="ups-footer__section-sub">Marketing internetowy, Google Ads, Meta Ads i strony WWW dla firm z calej Polski.</p></div><?php if (!empty($cities_hidden)) : ?><button class="ups-footer__toggle" type="button" data-role="cities-toggle" aria-expanded="false" aria-controls="<?php echo esc_attr($component_id . "-cities-more"); ?>">Pokaz wiecej miast</button><?php endif; ?></div><div class="ups-footer__cities-grid"><?php foreach ($cities_visible as $city_item) : ?><a href="<?php echo esc_url((string) $city_item["url"]); ?>"><?php echo esc_html((string) $city_item["label"]); ?></a><?php endforeach; ?></div><?php if (!empty($cities_hidden)) : ?><div class="ups-footer__cities-more" id="<?php echo esc_attr($component_id . "-cities-more"); ?>" data-role="cities-more" hidden><div class="ups-footer__cities-grid"><?php foreach ($cities_hidden as $city_item) : ?><a href="<?php echo esc_url((string) $city_item["url"]); ?>"><?php echo esc_html((string) $city_item["label"]); ?></a><?php endforeach; ?></div></div><?php endif; ?></section>
        <div class="ups-footer__bottom"><p class="ups-footer__copyright">© <?php echo esc_html(gmdate("Y")); ?> Upsellio / Sebastian Kelm. Wszelkie prawa zastrzezone.</p><nav class="ups-footer__legal" aria-label="Linki dodatkowe"><a href="<?php echo esc_url(home_url("/polityka-prywatnosci/")); ?>">Polityka prywatnosci</a><a href="<?php echo esc_url($contact_url); ?>">Kontakt</a><a href="<?php echo esc_url(home_url("/")); ?>">Upsellio.pl</a></nav></div>
      </div>
      <script type="application/ld+json"><?php echo wp_json_encode(["@context"=>"https://schema.org","@type"=>"ProfessionalService","name"=>"Upsellio","alternateName"=>"Upsellio by Sebastian Kelm","url"=>home_url("/"),"email"=>$contact_email,"telephone"=>$contact_phone,"areaServed"=>"Polska","description"=>"Marketing internetowy B2B, kampanie Meta Ads, Google Ads oraz strony internetowe dla firm B2B.","sameAs"=>[$linkedin_url,$facebook_url,$instagram_url,$x_url,$youtube_url]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
      <script>
        (function () {
          var root = document.getElementById('<?php echo esc_js($component_id); ?>');
          if (!root) return;
          var toggle = root.querySelector('[data-role="cities-toggle"]');
          var more = root.querySelector('[data-role="cities-more"]');
          if (!toggle || !more) return;
          toggle.addEventListener('click', function () {
            var isExpanded = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', String(!isExpanded));
            more.hidden = isExpanded;
            toggle.textContent = isExpanded ? 'Pokaz wiecej miast' : 'Ukryj dodatkowe miasta';
          });
        })();
      </script>
    </footer>
    <?php
    return ob_get_clean();
}

function upsellio_get_contact_page_seo_payload()
{
    $contact_url = upsellio_get_contact_page_url();

    return [
        "title" => "Kontakt | Upsellio - Marketing i strony WWW dla firm B2B",
        "description" => "Skontaktuj się z Upsellio. Opisz cele i wyzwania, a wrócę z konkretną rekomendacją działań marketingowych, webowych i lead generation dla Twojej firmy.",
        "canonical" => $contact_url,
    ];
}

function upsellio_contact_page_document_title($title)
{
    if (!upsellio_is_contact_page_context()) {
        return $title;
    }

    $seo_payload = upsellio_get_contact_page_seo_payload();
    $custom_title = trim((string) ($seo_payload["title"] ?? ""));
    return $custom_title !== "" ? $custom_title : $title;
}
add_filter("pre_get_document_title", "upsellio_contact_page_document_title");

function upsellio_print_contact_page_seo_meta()
{
    if (!upsellio_is_contact_page_context()) {
        return;
    }

    $seo_payload = upsellio_get_contact_page_seo_payload();
    $description = trim((string) ($seo_payload["description"] ?? ""));
    $canonical = trim((string) ($seo_payload["canonical"] ?? ""));
    $title = trim((string) ($seo_payload["title"] ?? ""));

    if ($description !== "") {
        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
    }
    if ($title !== "") {
        echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
    }
    if ($canonical !== "") {
        echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url($canonical) . '">' . "\n";
    }
    echo '<meta property="og:type" content="website">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
}
add_action("wp_head", "upsellio_print_contact_page_seo_meta", 1);

function upsellio_strlen($value)
{
    $value = (string) $value;
    return function_exists("mb_strlen") ? mb_strlen($value) : strlen($value);
}

require_once get_template_directory() . "/inc/cities-data.php";
require_once get_template_directory() . "/inc/definitions-data.php";
require_once get_template_directory() . "/inc/cities-seed.php";
require_once get_template_directory() . "/inc/definitions-seed.php";
require_once get_template_directory() . "/inc/blog-seo-tool.php";
require_once get_template_directory() . "/inc/crm.php";
require_once get_template_directory() . "/inc/seo-automation.php";
require_once get_template_directory() . "/inc/data-schema.php";
require_once get_template_directory() . "/inc/site-analytics.php";
require_once get_template_directory() . "/inc/advanced-tests.php";
require_once get_template_directory() . "/inc/portfolio-seed.php";
require_once get_template_directory() . "/inc/marketing-portfolio-seed.php";
require_once get_template_directory() . "/inc/theme-config.php";

function upsellio_setup()
{
    add_theme_support("title-tag");
    add_theme_support("post-thumbnails");
    add_theme_support("html5", ["search-form", "comment-form", "comment-list", "gallery", "caption", "style", "script"]);

    register_nav_menus(
        [
            "primary" => __("Primary Menu", "upsellio"),
        ]
    );
}
add_action("after_setup_theme", "upsellio_setup");

function upsellio_print_favicon_links()
{
    $assets_base_url = get_template_directory_uri() . "/assets/images";
    echo '<link rel="icon" type="image/png" href="' . esc_url($assets_base_url . '/favicon.png') . '" sizes="any" />';
    echo '<link rel="shortcut icon" type="image/png" href="' . esc_url($assets_base_url . '/favicon.png') . '" />';
    echo '<link rel="icon" type="image/png" href="' . esc_url($assets_base_url . '/favicon-16x16.png') . '" sizes="16x16" />';
    echo '<link rel="icon" type="image/png" href="' . esc_url($assets_base_url . '/favicon-32x32.png') . '" sizes="32x32" />';
    echo '<link rel="apple-touch-icon" href="' . esc_url($assets_base_url . '/apple-touch-icon.png') . '" sizes="180x180" />';
}
add_action("wp_head", "upsellio_print_favicon_links", 5);

function upsellio_get_ga4_measurement_id()
{
    $measurement_id = trim((string) get_option("upsellio_ga4_measurement_id", ""));
    if ($measurement_id === "") {
        $measurement_id = trim((string) getenv("UPSELLIO_GA4_MEASUREMENT_ID"));
    }
    $measurement_id = strtoupper(preg_replace("/[^A-Z0-9-]/", "", $measurement_id));

    return preg_match("/^G-[A-Z0-9]+$/", $measurement_id) ? $measurement_id : "";
}

function upsellio_get_facebook_pixel_id()
{
    $pixel_id = trim((string) get_option("upsellio_facebook_pixel_id", ""));
    if ($pixel_id === "") {
        $pixel_id = trim((string) getenv("UPSELLIO_FACEBOOK_PIXEL_ID"));
    }
    $pixel_id = preg_replace("/\D+/", "", $pixel_id);

    return $pixel_id !== "" ? $pixel_id : "";
}

function upsellio_print_tracking_scripts_head()
{
    if (is_admin()) {
        return;
    }

    $measurement_id = upsellio_get_ga4_measurement_id();
    if ($measurement_id !== "") {
        echo '<script async src="https://www.googletagmanager.com/gtag/js?id=' . esc_attr($measurement_id) . '"></script>' . "\n";
        echo "<script>\n";
        echo "window.dataLayer = window.dataLayer || [];\n";
        echo "function gtag(){dataLayer.push(arguments);}\n";
        echo "gtag('js', new Date());\n";
        echo "gtag('config', '" . esc_js($measurement_id) . "', { anonymize_ip: true, transport_type: 'beacon' });\n";
        echo "</script>\n";
    }

    $pixel_id = upsellio_get_facebook_pixel_id();
    if ($pixel_id !== "") {
        echo "<script>\n";
        echo "!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script','https://connect.facebook.net/en_US/fbevents.js');\n";
        echo "fbq('init', '" . esc_js($pixel_id) . "');\n";
        echo "fbq('track', 'PageView');\n";
        echo "</script>\n";
    }
}
add_action("wp_head", "upsellio_print_tracking_scripts_head", 6);

function upsellio_print_tracking_scripts_body()
{
    if (is_admin()) {
        return;
    }

    $pixel_id = upsellio_get_facebook_pixel_id();
    if ($pixel_id === "") {
        return;
    }

    echo '<noscript><img height="1" width="1" class="ups-pixel-noscript" src="' .
        esc_url("https://www.facebook.com/tr?id=" . rawurlencode($pixel_id) . "&ev=PageView&noscript=1") .
        '" alt="" /></noscript>' . "\n";
}
add_action("wp_body_open", "upsellio_print_tracking_scripts_body", 1);

function upsellio_primary_menu_name()
{
    return "Upsellio Primary Auto";
}

function upsellio_get_primary_navigation_links()
{
    $locations = get_nav_menu_locations();
    $menu_id = isset($locations["primary"]) ? (int) $locations["primary"] : 0;
    $links = [];

    if ($menu_id > 0) {
        $menu_items = wp_get_nav_menu_items($menu_id, ["update_post_term_cache" => false]);
        if (is_array($menu_items)) {
            $seen_urls = [];
            foreach ($menu_items as $menu_item) {
                if ((int) ($menu_item->menu_item_parent ?? 0) > 0) {
                    continue;
                }
                $url = isset($menu_item->url) ? (string) $menu_item->url : "";
                $title = isset($menu_item->title) ? wp_strip_all_tags((string) $menu_item->title) : "";
                if ($url === "" || $title === "") {
                    continue;
                }
                $normalized_url = untrailingslashit((string) wp_parse_url($url, PHP_URL_PATH));
                if ($normalized_url === "") {
                    $normalized_url = "/";
                }
                if (isset($seen_urls[$normalized_url])) {
                    continue;
                }
                $seen_urls[$normalized_url] = true;
                $links[] = [
                    "title" => $title,
                    "url" => $url,
                ];
            }
        }
    }

    if (!empty($links)) {
        return upsellio_append_special_navigation_links($links);
    }

    // Fallback if menu is not configured yet.
    $pages = get_pages([
        "post_status" => "publish",
        "sort_column" => "menu_order,post_title",
        "sort_order" => "ASC",
    ]);
    foreach ($pages as $page) {
        $links[] = [
            "title" => (string) $page->post_title,
            "url" => (string) get_permalink((int) $page->ID),
        ];
    }

    return upsellio_append_special_navigation_links($links);
}

function upsellio_build_page_tree($pages)
{
    $tree = [];
    foreach ((array) $pages as $page) {
        $parent_id = (int) $page->post_parent;
        if (!isset($tree[$parent_id])) {
            $tree[$parent_id] = [];
        }
        $tree[$parent_id][] = $page;
    }

    return $tree;
}

function upsellio_get_lead_magnet_candidates($limit = 12)
{
    $limit = max(1, (int) $limit);
    $items = [];

    $query = new WP_Query([
        "post_type" => ["lead_magnet", "page", "post", "definicja"],
        "post_status" => "publish",
        "posts_per_page" => max($limit, 12),
        "orderby" => "menu_order date",
        "order" => "ASC",
        "ignore_sticky_posts" => true,
    ]);

    if (!empty($query->posts)) {
        foreach ($query->posts as $post) {
            $post_id = (int) $post->ID;
            $post_type = (string) get_post_type($post_id);
            $is_marked = $post_type === "lead_magnet" || (string) get_post_meta($post_id, "_upsellio_is_lead_magnet", true) === "1";
            if (!$is_marked) {
                continue;
            }
            $items[] = [
                "id" => $post_id,
                "title" => (string) get_the_title($post_id),
                "url" => (string) get_permalink($post_id),
                "excerpt" => (string) get_the_excerpt($post_id),
            ];
        }
    }
    wp_reset_postdata();

    return array_slice($items, 0, $limit);
}

function upsellio_get_primary_lead_magnet()
{
    $items = upsellio_get_lead_magnet_candidates(1);
    if (!empty($items)) {
        return $items[0];
    }

    return [];
}

function upsellio_add_lead_magnet_meta_box()
{
    $screens = ["page", "post"];
    if (post_type_exists("definicja")) {
        $screens[] = "definicja";
    }

    foreach ($screens as $screen) {
        add_meta_box(
            "upsellio_lead_magnet_box",
            "Upsellio: Materiał do pobrania",
            "upsellio_render_lead_magnet_meta_box",
            $screen,
            "side",
            "high"
        );
    }
}
add_action("add_meta_boxes", "upsellio_add_lead_magnet_meta_box");

function upsellio_render_lead_magnet_meta_box($post)
{
    $is_marked = (string) get_post_meta((int) $post->ID, "_upsellio_is_lead_magnet", true) === "1";
    wp_nonce_field("upsellio_lead_magnet_meta_box", "upsellio_lead_magnet_nonce");
    ?>
    <label for="upsellio_is_lead_magnet" style="display:flex;gap:8px;align-items:flex-start;">
      <input type="checkbox" id="upsellio_is_lead_magnet" name="upsellio_is_lead_magnet" value="1" <?php checked($is_marked); ?> />
      <span>Oznacz ten wpis jako materiał do pobrania (pojawi się w synchronizacji nawigacji).</span>
    </label>
    <?php
}

function upsellio_save_lead_magnet_meta_box($post_id)
{
    if (!isset($_POST["upsellio_lead_magnet_nonce"])) {
        return;
    }
    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST["upsellio_lead_magnet_nonce"])), "upsellio_lead_magnet_meta_box")) {
        return;
    }
    if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can("edit_post", (int) $post_id)) {
        return;
    }

    $is_marked = isset($_POST["upsellio_is_lead_magnet"]) ? "1" : "0";
    update_post_meta((int) $post_id, "_upsellio_is_lead_magnet", $is_marked);
}
add_action("save_post", "upsellio_save_lead_magnet_meta_box");


function upsellio_sync_primary_navigation_menu()
{
    $menu_name = upsellio_primary_menu_name();
    $menu_object = wp_get_nav_menu_object($menu_name);
    $menu_id = $menu_object ? (int) $menu_object->term_id : 0;

    if ($menu_id <= 0) {
        $menu_id = (int) wp_create_nav_menu($menu_name);
    }

    if ($menu_id <= 0) {
        return ["created" => 0, "updated" => 0, "message" => "menu_error"];
    }

    $existing_items = wp_get_nav_menu_items($menu_id, ["post_status" => "any"]);
    if (is_array($existing_items)) {
        foreach ($existing_items as $existing_item) {
            wp_delete_post((int) $existing_item->ID, true);
        }
    }

    $pages = get_pages([
        "post_status" => "publish",
        "sort_column" => "menu_order,post_title",
        "sort_order" => "ASC",
    ]);
    $page_tree = upsellio_build_page_tree($pages);
    $created = 0;

    $append_pages = function ($parent_page_id, $parent_menu_item_id) use (&$append_pages, $page_tree, $menu_id, &$created) {
        $children = isset($page_tree[$parent_page_id]) ? $page_tree[$parent_page_id] : [];
        foreach ($children as $page) {
            $menu_item_id = wp_update_nav_menu_item($menu_id, 0, [
                "menu-item-title" => (string) $page->post_title,
                "menu-item-object-id" => (int) $page->ID,
                "menu-item-object" => "page",
                "menu-item-type" => "post_type",
                "menu-item-status" => "publish",
                "menu-item-parent-id" => (int) $parent_menu_item_id,
            ]);
            if (!is_wp_error($menu_item_id)) {
                $created++;
                $append_pages((int) $page->ID, (int) $menu_item_id);
            }
        }
    };
    $append_pages(0, 0);

    $blog_page_id = (int) upsellio_get_blog_page_id();
    if ($blog_page_id > 0) {
        $blog_page = get_post($blog_page_id);
        if ($blog_page instanceof WP_Post) {
            $blog_item = wp_update_nav_menu_item($menu_id, 0, [
                "menu-item-title" => (string) $blog_page->post_title,
                "menu-item-object-id" => $blog_page_id,
                "menu-item-object" => "page",
                "menu-item-type" => "post_type",
                "menu-item-status" => "publish",
                "menu-item-parent-id" => 0,
            ]);
            if (!is_wp_error($blog_item)) {
                $created++;
            }
        }
    }

    if (post_type_exists("definicja")) {
        $definitions_item = wp_update_nav_menu_item($menu_id, 0, [
            "menu-item-title" => "Definicje",
            "menu-item-url" => home_url("/definicje/"),
            "menu-item-type" => "custom",
            "menu-item-status" => "publish",
            "menu-item-parent-id" => 0,
        ]);
        if (!is_wp_error($definitions_item)) {
            $created++;
        }
    }

    if (post_type_exists("miasto")) {
        $cities_item = wp_update_nav_menu_item($menu_id, 0, [
            "menu-item-title" => "Miasta",
            "menu-item-url" => home_url("/miasta/"),
            "menu-item-type" => "custom",
            "menu-item-status" => "publish",
            "menu-item-parent-id" => 0,
        ]);
        if (!is_wp_error($cities_item)) {
            $created++;
        }
    }

    $marketing_portfolio_page_url = upsellio_get_marketing_portfolio_page_url();
    if ($marketing_portfolio_page_url !== "") {
        $marketing_portfolio_item = wp_update_nav_menu_item($menu_id, 0, [
            "menu-item-title" => "Portfolio marketingowe",
            "menu-item-url" => $marketing_portfolio_page_url,
            "menu-item-type" => "custom",
            "menu-item-status" => "publish",
            "menu-item-parent-id" => 0,
        ]);
        if (!is_wp_error($marketing_portfolio_item)) {
            $created++;
        }
    }

    $lead_magnets_page_url = upsellio_get_lead_magnets_page_url();
    if ($lead_magnets_page_url !== "") {
        $lead_magnets_parent_item = wp_update_nav_menu_item($menu_id, 0, [
            "menu-item-title" => "Materiały",
            "menu-item-url" => $lead_magnets_page_url,
            "menu-item-type" => "custom",
            "menu-item-status" => "publish",
            "menu-item-parent-id" => 0,
        ]);
        if (!is_wp_error($lead_magnets_parent_item)) {
            $created++;
            $lead_magnets = upsellio_get_lead_magnet_candidates(20);
            foreach ($lead_magnets as $lead_magnet) {
                $lead_item = wp_update_nav_menu_item($menu_id, 0, [
                    "menu-item-title" => (string) $lead_magnet["title"],
                    "menu-item-url" => (string) $lead_magnet["url"],
                    "menu-item-type" => "custom",
                    "menu-item-status" => "publish",
                    "menu-item-parent-id" => (int) $lead_magnets_parent_item,
                ]);
                if (!is_wp_error($lead_item)) {
                    $created++;
                }
            }
        }
    }

    $locations = get_nav_menu_locations();
    $locations["primary"] = $menu_id;
    set_theme_mod("nav_menu_locations", $locations);

    return ["created" => $created, "updated" => 0, "message" => "ok"];
}

function upsellio_get_navigation_sync_url()
{
    return add_query_arg([
        "upsellio_sync_navigation" => 1,
        "_upsellio_nonce" => wp_create_nonce("upsellio_sync_navigation"),
    ], admin_url("themes.php?page=upsellio-navigation-sync"));
}

function upsellio_handle_navigation_sync_request()
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }

    if (!isset($_GET["upsellio_sync_navigation"])) {
        return;
    }

    $nonce = isset($_GET["_upsellio_nonce"]) ? sanitize_text_field(wp_unslash($_GET["_upsellio_nonce"])) : "";
    if (!wp_verify_nonce($nonce, "upsellio_sync_navigation")) {
        return;
    }

    $result = upsellio_sync_primary_navigation_menu();
    $redirect_url = add_query_arg([
        "upsellio_navigation_sync_done" => 1,
        "created" => (int) ($result["created"] ?? 0),
        "msg" => (string) ($result["message"] ?? "ok"),
    ], admin_url("themes.php?page=upsellio-navigation-sync"));
    wp_safe_redirect($redirect_url);
    exit;
}
add_action("admin_init", "upsellio_handle_navigation_sync_request");

function upsellio_navigation_sync_screen()
{
    if (!current_user_can("manage_options")) {
        return;
    }
    ?>
    <div class="wrap">
      <h1>Synchronizacja nawigacji</h1>
      <p>Jednym kliknięciem zaktualizujesz menu nawigacji na podstawie wszystkich opublikowanych stron, podstron i oznaczonych materiałów.</p>
      <p><a class="button button-primary" href="<?php echo esc_url(upsellio_get_navigation_sync_url()); ?>">Wykonaj szybką aktualizację bazy menu</a></p>
    </div>
    <?php
}

function upsellio_register_navigation_sync_menu()
{
    add_theme_page(
        "Synchronizacja nawigacji",
        "Sync nawigacji",
        "manage_options",
        "upsellio-navigation-sync",
        "upsellio_navigation_sync_screen"
    );
}
add_action("admin_menu", "upsellio_register_navigation_sync_menu");

function upsellio_navigation_sync_admin_notice()
{
    if (!is_admin() || !isset($_GET["upsellio_navigation_sync_done"])) {
        return;
    }

    $created = isset($_GET["created"]) ? (int) $_GET["created"] : 0;
    $msg = isset($_GET["msg"]) ? sanitize_text_field(wp_unslash($_GET["msg"])) : "ok";
    if ($msg !== "ok") {
        echo '<div class="notice notice-error"><p>Nie udało się zsynchronizować nawigacji.</p></div>';
        return;
    }

    echo '<div class="notice notice-success"><p>';
    echo esc_html("Nawigacja została zsynchronizowana. Dodano pozycji: {$created}.");
    echo "</p></div>";
}
add_action("admin_notices", "upsellio_navigation_sync_admin_notice");

function upsellio_assets()
{
    $style_path = get_template_directory() . "/assets/css/upsellio.css";
    $style_uri = get_template_directory_uri() . "/assets/css/upsellio.css";
    $style_version = file_exists($style_path) ? (string) filemtime($style_path) : "1.0.0";
    $script_path = get_template_directory() . "/assets/js/upsellio.js";
    $script_uri = get_template_directory_uri() . "/assets/js/upsellio.js";
    $script_version = file_exists($script_path) ? (string) filemtime($script_path) : "1.0.0";

    wp_enqueue_style("upsellio-main", $style_uri, [], $style_version);
    wp_enqueue_script("upsellio-main", $script_uri, [], $script_version, true);
    wp_localize_script(
        "upsellio-main",
        "upsellioData",
        [
            "ajaxUrl" => admin_url("admin-ajax.php"),
            "blogNonce" => wp_create_nonce("upsellio_blog_filter"),
            "blogIndexUrl" => upsellio_get_blog_index_url(),
            "contactNonce" => wp_create_nonce("upsellio_contact_click"),
        ]
    );
}
add_action("wp_enqueue_scripts", "upsellio_assets");

function upsellio_city_seed_menu()
{
    add_submenu_page(
        "edit.php?post_type=miasto",
        "Generator miast SEO",
        "Generator SEO",
        "manage_options",
        "upsellio-seo-generator",
        "upsellio_city_seed_screen"
    );
}
add_action("admin_menu", "upsellio_city_seed_menu");

function upsellio_city_seed_screen()
{
    if (!current_user_can("manage_options")) {
        return;
    }
    ?>
    <div class="wrap">
      <h1>Generator podstron SEO dla miast</h1>
      <p>Wygeneruj 200 podstron lokalnych opartych o CPT <code>miasto</code>.</p>
      <p><a class="button button-primary" href="<?php echo esc_url(upsellio_get_seed_url(false)); ?>">Uruchom generator (jednorazowo)</a></p>
      <p><a class="button" href="<?php echo esc_url(upsellio_get_seed_url(true)); ?>">Wymus ponowne wygenerowanie</a></p>
      <p>Po uruchomieniu odswiez trwale linki: <strong>Ustawienia -> Bezposrednie odnosniki -> Zapisz</strong>.</p>
    </div>
    <?php
}

function upsellio_get_blog_page_id()
{
    $blog_page_id = (int) get_option("page_for_posts");
    if ($blog_page_id > 0) {
        return $blog_page_id;
    }

    $template_pages = get_pages([
        "post_status" => "publish",
        "meta_key" => "_wp_page_template",
        "meta_value" => "page-blog.php",
        "number" => 1,
    ]);
    if (!empty($template_pages)) {
        return (int) $template_pages[0]->ID;
    }

    return 0;
}

function upsellio_get_blog_index_url()
{
    $blog_page_id = upsellio_get_blog_page_id();
    $blog_index_url = $blog_page_id > 0 ? get_permalink($blog_page_id) : home_url("/");

    return $blog_index_url ?: home_url("/");
}

function upsellio_front_page_document_title($title)
{
    if (!is_front_page()) {
        return $title;
    }

    $sections = function_exists("upsellio_get_front_page_content_config")
        ? upsellio_get_front_page_content_config()
        : [];
    $seo = isset($sections["seo"]) && is_array($sections["seo"]) ? $sections["seo"] : [];
    $configured = trim((string) ($seo["title"] ?? ""));
    $fallback = "Upsellio | Marketing i strony WWW dla firm B2B";
    $resolved = $configured !== "" ? $configured : $fallback;
    $max_length = 60;
    if (upsellio_strlen($resolved) > $max_length) {
        return $fallback;
    }

    return $resolved;
}
add_filter("pre_get_document_title", "upsellio_front_page_document_title");

function upsellio_runtime_bootstrap_site_structure()
{
    if (get_option("upsellio_runtime_bootstrap_v1")) {
        return;
    }

    $definitions_page_id = upsellio_upsert_page_with_template("definicje", "Definicje", "archive-definicja.php");
    $cities_page_id = upsellio_upsert_page_with_template("miasta", "Miasta", "archive-miasto.php");
    $portfolio_page_id = upsellio_upsert_page_with_template("portfolio", "Portfolio", "page-portfolio.php");
    $marketing_portfolio_page_id = upsellio_upsert_page_with_template("portfolio-marketingowe", "Portfolio marketingowe", "page-portfolio-marketingowe.php");
    $lead_magnets_page_id = upsellio_upsert_page_with_template("lead-magnety", "Lead magnety", "page-lead-magnety.php");
    $contact_page_id = upsellio_upsert_page_with_template("kontakt", "Kontakt", "page-kontakt.php");
    upsellio_upsert_page_with_template("polityka-prywatnosci", "Polityka prywatnosci", "");

    if ((int) get_option("page_for_posts") <= 0) {
        $blog_page_id = upsellio_upsert_page_with_template("blog", "Blog", "page-blog.php");
        if ($blog_page_id > 0) {
            update_option("page_for_posts", (int) $blog_page_id);
        }
    }

    $critical_pages_exist = $definitions_page_id > 0 && $cities_page_id > 0 && $portfolio_page_id > 0
        && $marketing_portfolio_page_id > 0 && $lead_magnets_page_id > 0 && $contact_page_id > 0;

    if ($critical_pages_exist) {
        flush_rewrite_rules(false);
    }

    update_option("upsellio_runtime_bootstrap_v1", gmdate("c"), false);
}
add_action("init", "upsellio_runtime_bootstrap_site_structure", 99);

function upsellio_estimated_read_time($post_id)
{
    $content = wp_strip_all_tags((string) get_post_field("post_content", $post_id));
    $word_count = str_word_count($content);
    $minutes = max(1, (int) ceil($word_count / 220));

    return sprintf(__("%d min czytania", "upsellio"), $minutes);
}

function upsellio_parse_tag_filters($raw_tags)
{
    if (is_array($raw_tags)) {
        $candidates = $raw_tags;
    } else {
        $candidates = explode(",", (string) $raw_tags);
    }

    $sanitized_tags = [];
    foreach ($candidates as $tag_slug) {
        $tag_slug = sanitize_title(trim((string) $tag_slug));
        if ($tag_slug === "") {
            continue;
        }
        $sanitized_tags[] = $tag_slug;
    }

    $sanitized_tags = array_values(array_unique($sanitized_tags));

    return array_slice($sanitized_tags, 0, 3);
}

function upsellio_get_blog_payload($selected_category = "", $selected_tags = [], $search_term = "", $paged = 1)
{
    $query_args = [
        "post_type" => "post",
        "post_status" => "publish",
        "posts_per_page" => 7,
        "paged" => max(1, (int) $paged),
    ];

    if ($selected_category !== "" && $selected_category !== "all") {
        $query_args["category_name"] = $selected_category;
    }

    if (!empty($selected_tags)) {
        $query_args["tax_query"] = [
            [
                "taxonomy" => "post_tag",
                "field" => "slug",
                "terms" => $selected_tags,
                "operator" => "IN",
            ],
        ];
    }

    if ($search_term !== "") {
        $query_args["s"] = $search_term;
    }

    $blog_query = new WP_Query($query_args);
    $posts = $blog_query->posts;

    return [
        "blog_query" => $blog_query,
        "featured_post" => $posts ? $posts[0] : null,
        "regular_posts" => count($posts) > 1 ? array_slice($posts, 1) : [],
        "categories" => get_categories(["hide_empty" => true]),
        "tags" => get_tags(["hide_empty" => true]),
        "paged" => max(1, (int) $paged),
    ];
}

function upsellio_render_blog_dynamic_content($selected_category = "", $selected_tags = [], $search_term = "", $paged = 1)
{
    $data = upsellio_get_blog_payload($selected_category, $selected_tags, $search_term, $paged);
    $blog_query = $data["blog_query"];
    $featured_post = $data["featured_post"];
    $regular_posts = $data["regular_posts"];
    $categories = $data["categories"];
    $tags = $data["tags"];
    $current_paged = $data["paged"];
    $blog_index_url = upsellio_get_blog_index_url();

    ob_start();
    ?>
    <section class="ups-blog-featured-wrap">
      <div class="wrap ups-blog-featured-grid">
        <?php if ($featured_post) : ?>
          <?php
          $featured_categories = get_the_category($featured_post->ID);
          $featured_category = !empty($featured_categories) ? $featured_categories[0] : null;
          ?>
          <article class="ups-blog-featured-card">
            <div class="ups-blog-featured-main">
              <div class="ups-blog-featured-cover">
                <div class="ups-blog-featured-content">
                  <div class="ups-blog-featured-label">Wyróżniony wpis</div>
                  <div class="ups-blog-featured-title-shell">
                    <?php if ($featured_category) : ?>
                      <div class="ups-blog-featured-category"><?php echo esc_html($featured_category->name); ?></div>
                    <?php endif; ?>
                    <h2 class="ups-blog-featured-title"><?php echo esc_html(get_the_title($featured_post)); ?></h2>
                  </div>
                </div>
              </div>
              <div class="ups-blog-featured-text">
                <div>
                  <div class="ups-blog-featured-meta">
                    <?php echo esc_html(get_the_date("j F Y", $featured_post)); ?> · <?php echo esc_html(upsellio_estimated_read_time($featured_post->ID)); ?>
                  </div>
                  <p class="ups-blog-featured-excerpt"><?php echo esc_html(get_the_excerpt($featured_post)); ?></p>
                </div>
                <div class="ups-blog-actions">
                  <a href="<?php echo esc_url(get_permalink($featured_post)); ?>" class="ups-blog-btn-primary">Czytaj artykuł →</a>
                  <?php if ($featured_category) : ?>
                    <a href="<?php echo esc_url(get_category_link($featured_category)); ?>" class="ups-blog-btn-secondary">
                      Zobacz wszystkie <?php echo esc_html($featured_category->name); ?>
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </article>
        <?php else : ?>
          <div class="ups-blog-empty">
            Nie znaleziono wpisów pasujących do aktualnego filtrowania.
          </div>
        <?php endif; ?>

        <aside class="ups-blog-side">
          <div class="ups-blog-panel">
            <div class="eyebrow" style="margin-bottom: 0;">Newsletter / materiały do pobrania</div>
            <h3 class="ups-blog-panel-title">Chcesz praktyczne materiały o reklamach i sprzedaży?</h3>
            <p class="ups-blog-panel-text">
              Raz na jakiś czas wyślę Ci konkretny materiał: checklistę, analizę albo wpis, który pomaga podejmować lepsze decyzje marketingowe.
            </p>
            <form class="ups-blog-newsletter" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" method="post" data-upsellio-lead-form="1">
              <input type="hidden" name="action" value="upsellio_submit_lead" />
              <input type="hidden" name="redirect_url" value="<?php echo esc_url($blog_index_url); ?>" />
              <input type="hidden" name="lead_form_origin" value="newsletter" />
              <input type="hidden" name="lead_source" value="newsletter" />
              <input type="hidden" name="lead_name" value="Newsletter" />
              <input type="hidden" name="lead_message" value="Nowa subskrypcja newslettera." />
              <input type="hidden" name="lead_consent" value="1" />
              <input type="hidden" name="utm_source" data-ups-utm="source" value="" />
              <input type="hidden" name="utm_medium" data-ups-utm="medium" value="" />
              <input type="hidden" name="utm_campaign" data-ups-utm="campaign" value="" />
              <input type="hidden" name="landing_url" data-ups-context="landing" value="" />
              <input type="hidden" name="referrer" data-ups-context="referrer" value="" />
              <input type="text" name="lead_website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;" />
              <?php wp_nonce_field("upsellio_unified_lead_form", "upsellio_lead_form_nonce"); ?>
              <input type="email" name="lead_email" placeholder="Twój e-mail" required />
              <button type="submit">Zapisz mnie</button>
            </form>
          </div>

          <div class="ups-blog-panel">
            <div class="eyebrow" style="margin-bottom: 0;">Popularne tematy</div>
            <div class="ups-blog-tags">
              <?php foreach (array_slice($tags, 0, 8) as $topic_tag) : ?>
                <span class="ups-blog-tag"><?php echo esc_html($topic_tag->name); ?></span>
              <?php endforeach; ?>
            </div>
          </div>
        </aside>
      </div>
    </section>

    <section>
      <div class="wrap ups-blog-list-wrap">
        <div class="ups-blog-list-head">
          <div>
            <div class="eyebrow" style="margin-bottom: 0;">Najnowsze wpisy</div>
            <h2 class="ups-blog-list-title">Wszystkie artykuły</h2>
          </div>
          <div class="ups-blog-list-meta">
            <?php echo esc_html((string) $blog_query->found_posts); ?> wpisów · sortowanie: najnowsze
          </div>
        </div>

        <?php if (!empty($regular_posts)) : ?>
          <div class="ups-blog-grid">
            <?php foreach ($regular_posts as $post_index => $post_item) : ?>
              <?php
              $post_categories = get_the_category($post_item->ID);
              $post_category_name = !empty($post_categories) ? $post_categories[0]->name : "Artykuł";
              $card_class = "ups-blog-card";
              if ($post_index === 0) {
                  $card_class .= " is-wide";
              }
              ?>
              <article class="<?php echo esc_attr($card_class); ?>">
                <div class="ups-blog-card-top">
                  <div class="ups-blog-card-category"><?php echo esc_html($post_category_name); ?></div>
                  <div class="ups-blog-card-time"><?php echo esc_html(upsellio_estimated_read_time($post_item->ID)); ?></div>
                </div>
                <h3 class="ups-blog-card-title"><?php echo esc_html(get_the_title($post_item)); ?></h3>
                <p class="ups-blog-card-excerpt"><?php echo esc_html(get_the_excerpt($post_item)); ?></p>
                <div class="ups-blog-card-footer">
                  <div class="ups-blog-card-meta">
                    <?php echo esc_html(get_the_date("j F Y", $post_item)); ?>
                  </div>
                  <a class="ups-blog-card-link" href="<?php echo esc_url(get_permalink($post_item)); ?>">Czytaj dalej →</a>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php elseif ($featured_post) : ?>
          <div class="ups-blog-empty">Brak kolejnych artykułów dla tych filtrów. Zmień kategorię lub tagi, aby zobaczyć więcej wpisów.</div>
        <?php else : ?>
          <div class="ups-blog-empty">Nie znaleziono wpisów pasujących do aktualnego filtrowania.</div>
        <?php endif; ?>

        <?php
        $base_query_args = [];
        if ($selected_category !== "" && $selected_category !== "all") {
            $base_query_args["category"] = $selected_category;
        }
        if (!empty($selected_tags)) {
            $base_query_args["tags"] = implode(",", $selected_tags);
        }
        if ($search_term !== "") {
            $base_query_args["s"] = $search_term;
        }

        $base_url = add_query_arg($base_query_args, $blog_index_url);
        $pagination = paginate_links([
            "base" => esc_url(add_query_arg("paged", "%#%", $base_url)),
            "format" => "",
            "current" => $current_paged,
            "total" => $blog_query->max_num_pages,
            "type" => "array",
            "prev_text" => "← Poprzednia",
            "next_text" => "Następna →",
        ]);
        ?>
        <?php if (!empty($pagination)) : ?>
          <div class="ups-blog-pagination">
            <?php foreach ($pagination as $page_link) : ?>
              <?php
              $is_current = strpos($page_link, "current") !== false;
              $class_name = $is_current ? "ups-blog-page-link current" : "ups-blog-page-link";
              ?>
              <span class="<?php echo esc_attr($class_name); ?>"><?php echo wp_kses_post($page_link); ?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
    <?php

    wp_reset_postdata();

    return ob_get_clean();
}

function upsellio_ajax_filter_blog_posts()
{
    check_ajax_referer("upsellio_blog_filter", "nonce");

    $selected_category = isset($_POST["category"]) ? sanitize_title(wp_unslash($_POST["category"])) : "";
    $selected_tags = [];
    if (isset($_POST["tags"])) {
        $selected_tags = upsellio_parse_tag_filters(wp_unslash($_POST["tags"]));
    } elseif (isset($_POST["tag"])) {
        // Legacy fallback for older clients.
        $selected_tags = upsellio_parse_tag_filters(wp_unslash($_POST["tag"]));
    }
    $search_term = isset($_POST["search"]) ? sanitize_text_field(wp_unslash($_POST["search"])) : "";
    $paged = isset($_POST["paged"]) ? max(1, (int) $_POST["paged"]) : 1;

    wp_send_json_success([
        "html" => upsellio_render_blog_dynamic_content($selected_category, $selected_tags, $search_term, $paged),
    ]);
}
add_action("wp_ajax_upsellio_filter_blog_posts", "upsellio_ajax_filter_blog_posts");
add_action("wp_ajax_nopriv_upsellio_filter_blog_posts", "upsellio_ajax_filter_blog_posts");

function upsellio_submit_contact_form()
{
    check_ajax_referer("upsellio_contact_click", "nonce");

    $name = isset($_POST["name"]) ? sanitize_text_field(wp_unslash($_POST["name"])) : "";
    $email = isset($_POST["email"]) ? sanitize_email(wp_unslash($_POST["email"])) : "";
    $message = isset($_POST["message"]) ? sanitize_textarea_field(wp_unslash($_POST["message"])) : "";
    $phone = isset($_POST["phone"]) ? sanitize_text_field(wp_unslash($_POST["phone"])) : "";
    $service = isset($_POST["service"]) ? sanitize_text_field(wp_unslash($_POST["service"])) : "";
    $budget = isset($_POST["budget"]) ? sanitize_text_field(wp_unslash($_POST["budget"])) : "";
    $goal = isset($_POST["goal"]) ? sanitize_text_field(wp_unslash($_POST["goal"])) : "";
    $source = isset($_POST["source"]) ? esc_url_raw(wp_unslash($_POST["source"])) : "";
    $website = isset($_POST["website"]) ? sanitize_text_field(wp_unslash($_POST["website"])) : "";

    if ($website !== "") {
        wp_send_json_success([
            "message" => "Dziekujemy, formularz zostal wyslany.",
        ]);
    }

    if (upsellio_strlen($name) < 2) {
        wp_send_json_error([
            "message" => "Podaj imie i nazwe firmy.",
        ], 400);
    }

    if (!is_email($email)) {
        wp_send_json_error([
            "message" => "Podaj poprawny adres e-mail.",
        ], 400);
    }

    if (upsellio_strlen($message) < 10) {
        wp_send_json_error([
            "message" => "Opisz sytuacje w minimum 10 znakach.",
        ], 400);
    }

    $lead_id = upsellio_crm_create_lead([
        "name" => $name,
        "email" => $email,
        "phone" => $phone,
        "message" => $message,
        "service" => $service,
        "budget" => $budget,
        "goal" => $goal,
        "form_origin" => "contact-form-ajax",
        "source" => "contact-form",
        "landing_url" => $source,
        "referrer" => "",
    ]);

    if ($lead_id <= 0) {
        wp_send_json_error([
            "message" => "Nie udalo sie zapisac leada. Sprobuj ponownie za chwile.",
        ], 500);
    }
    upsellio_crm_send_emails($lead_id, $name, $email, $message);
    upsellio_crm_schedule_followup($lead_id);

    wp_send_json_success([
        "message" => "Wiadomosc wyslana. Odezwiemy sie wkrotce.",
    ]);
}
add_action("wp_ajax_upsellio_submit_contact_form", "upsellio_submit_contact_form");
add_action("wp_ajax_nopriv_upsellio_submit_contact_form", "upsellio_submit_contact_form");

function upsellio_get_lead_magnets_page_url()
{
    $lead_magnets_path = upsellio_get_special_navigation_path_by_title("Lead magnety", "/lead-magnety/");
    $lead_magnets_page = get_page_by_path(trim($lead_magnets_path, "/"));
    if ($lead_magnets_page instanceof WP_Post) {
        $permalink = get_permalink((int) $lead_magnets_page->ID);
        if ($permalink) {
            return $permalink;
        }
    }

    return home_url($lead_magnets_path);
}

function upsellio_append_special_navigation_links($links)
{
    $links = is_array($links) ? $links : [];
    $special_links = [];
    if (function_exists("upsellio_get_special_navigation_links_config")) {
        foreach (upsellio_get_special_navigation_links_config() as $configured_link) {
            $special_links[] = [
                "title" => (string) $configured_link["title"],
                "url" => home_url((string) $configured_link["path"]),
            ];
        }
    }

    foreach ($special_links as $special_link) {
        $special_url = (string) $special_link["url"];
        if ($special_url === "") {
            continue;
        }
        $already_exists = false;

        foreach ($links as $link) {
            $url = isset($link["url"]) ? (string) $link["url"] : "";
            if ($url !== "" && untrailingslashit($url) === untrailingslashit($special_url)) {
                $already_exists = true;
                break;
            }
        }

        if (!$already_exists) {
            $links[] = [
                "title" => (string) $special_link["title"],
                "url" => $special_url,
            ];
        }
    }

    return $links;
}

function upsellio_append_lead_magnets_link($links)
{
    return upsellio_append_special_navigation_links($links);
}

function upsellio_get_special_navigation_path_by_title($title, $default_path)
{
    $title = (string) $title;
    $default_path = "/" . ltrim((string) $default_path, "/");
    if (function_exists("upsellio_get_special_navigation_links_config")) {
        foreach (upsellio_get_special_navigation_links_config() as $configured_link) {
            if ((string) ($configured_link["title"] ?? "") === $title) {
                $path = (string) ($configured_link["path"] ?? "");
                return $path !== "" ? "/" . ltrim($path, "/") : $default_path;
            }
        }
    }

    return $default_path;
}

function upsellio_register_lead_magnets_cpt()
{
    register_post_type("lead_magnet", [
        "labels" => [
            "name" => "Materiały",
            "singular_name" => "Materiał",
            "add_new" => "Dodaj materiał",
            "add_new_item" => "Dodaj nowy materiał",
            "edit_item" => "Edytuj materiał",
            "new_item" => "Nowy materiał",
            "view_item" => "Zobacz materiał",
            "search_items" => "Szukaj materiałów",
            "not_found" => "Nie znaleziono materiałów",
            "menu_name" => "Materiały",
        ],
        "public" => true,
        "show_in_rest" => true,
        "menu_icon" => "dashicons-download",
        "supports" => ["title", "editor", "excerpt", "thumbnail", "page-attributes"],
        "has_archive" => false,
        "rewrite" => ["slug" => "lead-magnet", "with_front" => false],
    ]);

    register_taxonomy("lead_magnet_category", ["lead_magnet"], [
        "labels" => [
            "name" => "Kategorie materiałów",
            "singular_name" => "Kategoria materiału",
            "search_items" => "Szukaj kategorii",
            "all_items" => "Wszystkie kategorie",
            "edit_item" => "Edytuj kategorię",
            "update_item" => "Aktualizuj kategorię",
            "add_new_item" => "Dodaj nową kategorię",
            "new_item_name" => "Nowa kategoria",
            "menu_name" => "Kategorie",
        ],
        "hierarchical" => true,
        "show_admin_column" => true,
        "show_in_rest" => true,
        "rewrite" => ["slug" => "kategoria-lead-magnetu", "with_front" => false],
    ]);
}
add_action("init", "upsellio_register_lead_magnets_cpt");

function upsellio_add_lead_magnet_details_meta_box()
{
    add_meta_box(
        "upsellio_lead_magnet_details",
        "Dane katalogu materiału",
        "upsellio_render_lead_magnet_details_meta_box",
        "lead_magnet",
        "normal",
        "high"
    );
}
add_action("add_meta_boxes", "upsellio_add_lead_magnet_details_meta_box");

function upsellio_render_lead_magnet_details_meta_box($post)
{
    $post_id = (int) $post->ID;
    $type = (string) get_post_meta($post_id, "_ups_lm_type", true);
    $meta = (string) get_post_meta($post_id, "_ups_lm_meta", true);
    $badge = (string) get_post_meta($post_id, "_ups_lm_badge", true);
    $cta = (string) get_post_meta($post_id, "_ups_lm_cta", true);
    $image = (string) get_post_meta($post_id, "_ups_lm_image", true);
    $is_featured = (string) get_post_meta($post_id, "_ups_lm_featured", true) === "1";
    $custom_html = (string) get_post_meta($post_id, "_ups_lm_custom_html", true);
    $custom_css = (string) get_post_meta($post_id, "_ups_lm_custom_css", true);
    $custom_js = (string) get_post_meta($post_id, "_ups_lm_custom_js", true);

    wp_nonce_field("upsellio_lead_magnet_details", "upsellio_lead_magnet_details_nonce");
    ?>
    <p>
      <label for="ups_lm_type"><strong>Typ materiału</strong></label><br />
      <input type="text" id="ups_lm_type" name="ups_lm_type" value="<?php echo esc_attr($type); ?>" class="widefat" placeholder="np. Checklista, Audyt, Raport" />
    </p>
    <p>
      <label for="ups_lm_meta"><strong>Meta materiału</strong></label><br />
      <input type="text" id="ups_lm_meta" name="ups_lm_meta" value="<?php echo esc_attr($meta); ?>" class="widefat" placeholder="np. PDF · 7 min" />
    </p>
    <p>
      <label for="ups_lm_badge"><strong>Badge wyróżnienia</strong></label><br />
      <input type="text" id="ups_lm_badge" name="ups_lm_badge" value="<?php echo esc_attr($badge); ?>" class="widefat" placeholder="np. Najczęściej pobierany" />
    </p>
    <p>
      <label for="ups_lm_cta"><strong>Tekst CTA</strong></label><br />
      <input type="text" id="ups_lm_cta" name="ups_lm_cta" value="<?php echo esc_attr($cta); ?>" class="widefat" placeholder="np. Zobacz materiał" />
    </p>
    <p>
      <label for="ups_lm_image"><strong>URL obrazka (hero/karta)</strong></label><br />
      <input type="url" id="ups_lm_image" name="ups_lm_image" value="<?php echo esc_attr($image); ?>" class="widefat" placeholder="https://..." />
    </p>
    <p>
      <label style="display:flex;align-items:flex-start;gap:8px;">
        <input type="checkbox" name="ups_lm_featured" value="1" <?php checked($is_featured); ?> />
        <span>Ustaw jako wyróżniony materiał na stronie katalogu.</span>
      </label>
    </p>
    <hr />
    <p><strong>Niestandardowy widok (HTML + CSS + JS)</strong><br />
      <span style="color:#6b7280;">Wklej kod, jeśli ten materiał ma mieć własny layout osadzony na stronie szczegółów.</span>
    </p>
    <p>
      <label for="ups_lm_custom_html"><strong>HTML</strong></label>
      <textarea id="ups_lm_custom_html" name="ups_lm_custom_html" class="widefat" rows="8"><?php echo esc_textarea($custom_html); ?></textarea>
    </p>
    <p>
      <label for="ups_lm_custom_css"><strong>CSS</strong></label>
      <textarea id="ups_lm_custom_css" name="ups_lm_custom_css" class="widefat" rows="8"><?php echo esc_textarea($custom_css); ?></textarea>
    </p>
    <p>
      <label for="ups_lm_custom_js"><strong>JS</strong></label>
      <textarea id="ups_lm_custom_js" name="ups_lm_custom_js" class="widefat" rows="8"><?php echo esc_textarea($custom_js); ?></textarea>
    </p>
    <?php
}

function upsellio_save_lead_magnet_details_meta_box($post_id)
{
    if (get_post_type((int) $post_id) !== "lead_magnet") {
        return;
    }
    if (!isset($_POST["upsellio_lead_magnet_details_nonce"])) {
        return;
    }

    $nonce = sanitize_text_field(wp_unslash($_POST["upsellio_lead_magnet_details_nonce"]));
    if (!wp_verify_nonce($nonce, "upsellio_lead_magnet_details")) {
        return;
    }
    if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can("edit_post", (int) $post_id)) {
        return;
    }

    $fields = [
        "_ups_lm_type" => isset($_POST["ups_lm_type"]) ? sanitize_text_field(wp_unslash($_POST["ups_lm_type"])) : "",
        "_ups_lm_meta" => isset($_POST["ups_lm_meta"]) ? sanitize_text_field(wp_unslash($_POST["ups_lm_meta"])) : "",
        "_ups_lm_badge" => isset($_POST["ups_lm_badge"]) ? sanitize_text_field(wp_unslash($_POST["ups_lm_badge"])) : "",
        "_ups_lm_cta" => isset($_POST["ups_lm_cta"]) ? sanitize_text_field(wp_unslash($_POST["ups_lm_cta"])) : "",
        "_ups_lm_image" => isset($_POST["ups_lm_image"]) ? esc_url_raw(wp_unslash($_POST["ups_lm_image"])) : "",
    ];

    foreach ($fields as $meta_key => $meta_value) {
        update_post_meta((int) $post_id, $meta_key, $meta_value);
    }

    update_post_meta((int) $post_id, "_ups_lm_featured", isset($_POST["ups_lm_featured"]) ? "1" : "0");
    update_post_meta((int) $post_id, "_upsellio_is_lead_magnet", "1");

    $custom_html = isset($_POST["ups_lm_custom_html"]) ? wp_unslash($_POST["ups_lm_custom_html"]) : "";
    $custom_css = isset($_POST["ups_lm_custom_css"]) ? wp_unslash($_POST["ups_lm_custom_css"]) : "";
    $custom_js = isset($_POST["ups_lm_custom_js"]) ? wp_unslash($_POST["ups_lm_custom_js"]) : "";
    $payload = upsellio_prepare_custom_embed_payload((string) $custom_html, (string) $custom_css, (string) $custom_js);
    update_post_meta((int) $post_id, "_ups_lm_custom_html", (string) $payload["html"]);
    update_post_meta((int) $post_id, "_ups_lm_custom_css", (string) $payload["css"]);
    update_post_meta((int) $post_id, "_ups_lm_custom_js", (string) $payload["js"]);
}
add_action("save_post", "upsellio_save_lead_magnet_details_meta_box");

function upsellio_get_lead_magnet_list($limit = 30)
{
    $query = new WP_Query([
        "post_type" => "lead_magnet",
        "post_status" => "publish",
        "posts_per_page" => max(1, (int) $limit),
        "orderby" => "menu_order date",
        "order" => "ASC",
    ]);

    $items = [];
    foreach ((array) $query->posts as $post_item) {
        $post_id = (int) $post_item->ID;
        $terms = get_the_terms($post_id, "lead_magnet_category");
        $first_term = (!empty($terms) && !is_wp_error($terms)) ? $terms[0] : null;

        $items[] = [
            "id" => $post_id,
            "title" => (string) get_the_title($post_id),
            "url" => (string) get_permalink($post_id),
            "excerpt" => (string) get_the_excerpt($post_id),
            "type" => (string) get_post_meta($post_id, "_ups_lm_type", true),
            "meta" => (string) get_post_meta($post_id, "_ups_lm_meta", true),
            "badge" => (string) get_post_meta($post_id, "_ups_lm_badge", true),
            "cta" => (string) get_post_meta($post_id, "_ups_lm_cta", true),
            "image" => (string) get_post_meta($post_id, "_ups_lm_image", true),
            "category" => $first_term ? (string) $first_term->name : "Lead generation",
            "category_slug" => $first_term ? (string) $first_term->slug : "lead-generation",
            "is_featured" => (string) get_post_meta($post_id, "_ups_lm_featured", true) === "1",
        ];
    }
    wp_reset_postdata();

    return $items;
}

function upsellio_ensure_lead_magnets_page_exists()
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }

    $lead_magnets_path = upsellio_get_special_navigation_path_by_title("Lead magnety", "/lead-magnety/");
    $lead_magnets_slug = trim((string) wp_parse_url($lead_magnets_path, PHP_URL_PATH), "/");
    if ($lead_magnets_slug === "") {
        $lead_magnets_slug = "lead-magnety";
    }
    upsellio_upsert_page_with_template($lead_magnets_slug, "Lead magnety", "page-lead-magnety.php");
}
add_action("admin_init", "upsellio_ensure_lead_magnets_page_exists");

function upsellio_get_portfolio_page_url()
{
    $portfolio_path = upsellio_get_special_navigation_path_by_title("Portfolio", "/portfolio/");
    $portfolio_page = get_page_by_path(trim($portfolio_path, "/"));
    if ($portfolio_page instanceof WP_Post) {
        $permalink = get_permalink((int) $portfolio_page->ID);
        if ($permalink) {
            return $permalink;
        }
    }

    return home_url($portfolio_path);
}

function upsellio_register_portfolio_cpt()
{
    register_post_type("portfolio", [
        "labels" => [
            "name" => "Portfolio",
            "singular_name" => "Projekt portfolio",
            "add_new" => "Dodaj projekt",
            "add_new_item" => "Dodaj nowy projekt portfolio",
            "edit_item" => "Edytuj projekt",
            "new_item" => "Nowy projekt",
            "view_item" => "Zobacz projekt",
            "search_items" => "Szukaj projektów",
            "not_found" => "Nie znaleziono projektów",
            "menu_name" => "Portfolio",
        ],
        "public" => true,
        "show_in_rest" => true,
        "menu_icon" => "dashicons-portfolio",
        "supports" => ["title", "editor", "excerpt", "thumbnail", "page-attributes"],
        "has_archive" => false,
        "rewrite" => ["slug" => "realizacja", "with_front" => false],
    ]);

    register_taxonomy("portfolio_category", ["portfolio"], [
        "labels" => [
            "name" => "Kategorie portfolio",
            "singular_name" => "Kategoria portfolio",
            "search_items" => "Szukaj kategorii",
            "all_items" => "Wszystkie kategorie",
            "edit_item" => "Edytuj kategorię",
            "update_item" => "Aktualizuj kategorię",
            "add_new_item" => "Dodaj nową kategorię",
            "new_item_name" => "Nowa kategoria",
            "menu_name" => "Kategorie",
        ],
        "hierarchical" => true,
        "show_admin_column" => true,
        "show_in_rest" => true,
        "rewrite" => ["slug" => "kategoria-portfolio", "with_front" => false],
    ]);
}
add_action("init", "upsellio_register_portfolio_cpt");

function upsellio_add_portfolio_details_meta_box()
{
    add_meta_box(
        "upsellio_portfolio_details",
        "Dane projektu portfolio",
        "upsellio_render_portfolio_details_meta_box",
        "portfolio",
        "normal",
        "high"
    );
}
add_action("add_meta_boxes", "upsellio_add_portfolio_details_meta_box");

function upsellio_render_portfolio_details_meta_box($post)
{
    $post_id = (int) $post->ID;
    $type = (string) get_post_meta($post_id, "_ups_port_type", true);
    $meta = (string) get_post_meta($post_id, "_ups_port_meta", true);
    $badge = (string) get_post_meta($post_id, "_ups_port_badge", true);
    $cta = (string) get_post_meta($post_id, "_ups_port_cta", true);
    $image = (string) get_post_meta($post_id, "_ups_port_image", true);
    $result = (string) get_post_meta($post_id, "_ups_port_result", true);
    $problem = (string) get_post_meta($post_id, "_ups_port_problem", true);
    $scope = (string) get_post_meta($post_id, "_ups_port_scope", true);
    $external_url = (string) get_post_meta($post_id, "_ups_port_external_url", true);
    $metrics = (string) get_post_meta($post_id, "_ups_port_metrics", true);
    $is_featured = (string) get_post_meta($post_id, "_ups_port_featured", true) === "1";
    $custom_html = (string) get_post_meta($post_id, "_ups_port_custom_html", true);
    $custom_css = (string) get_post_meta($post_id, "_ups_port_custom_css", true);
    $custom_js = (string) get_post_meta($post_id, "_ups_port_custom_js", true);

    wp_nonce_field("upsellio_portfolio_details", "upsellio_portfolio_details_nonce");
    ?>
    <p>
      <label for="ups_port_type"><strong>Typ realizacji</strong></label><br />
      <input type="text" id="ups_port_type" name="ups_port_type" value="<?php echo esc_attr($type); ?>" class="widefat" placeholder="np. Strona firmowa, Aplikacja webowa, E-commerce" />
    </p>
    <p>
      <label for="ups_port_meta"><strong>Meta projektu</strong></label><br />
      <input type="text" id="ups_port_meta" name="ups_port_meta" value="<?php echo esc_attr($meta); ?>" class="widefat" placeholder="np. B2B · UX · SEO · Konwersja" />
    </p>
    <p>
      <label for="ups_port_badge"><strong>Badge wyróżnienia</strong></label><br />
      <input type="text" id="ups_port_badge" name="ups_port_badge" value="<?php echo esc_attr($badge); ?>" class="widefat" placeholder="np. Wyróżniony projekt" />
    </p>
    <p>
      <label for="ups_port_cta"><strong>Tekst CTA</strong></label><br />
      <input type="text" id="ups_port_cta" name="ups_port_cta" value="<?php echo esc_attr($cta); ?>" class="widefat" placeholder="np. Zobacz case study" />
    </p>
    <p>
      <label for="ups_port_image"><strong>URL obrazka projektu</strong></label><br />
      <input type="url" id="ups_port_image" name="ups_port_image" value="<?php echo esc_attr($image); ?>" class="widefat" placeholder="https://..." />
    </p>
    <p>
      <label for="ups_port_external_url"><strong>Link zewnętrzny (opcjonalnie)</strong></label><br />
      <input type="url" id="ups_port_external_url" name="ups_port_external_url" value="<?php echo esc_attr($external_url); ?>" class="widefat" placeholder="https://..." />
    </p>
    <p>
      <label for="ups_port_problem"><strong>Problem biznesowy (SEO + case study)</strong></label>
      <textarea id="ups_port_problem" name="ups_port_problem" class="widefat" rows="4" placeholder="Jakie wyzwanie miał klient?"><?php echo esc_textarea($problem); ?></textarea>
    </p>
    <p>
      <label for="ups_port_scope"><strong>Zakres prac</strong></label>
      <textarea id="ups_port_scope" name="ups_port_scope" class="widefat" rows="4" placeholder="Jakie działania zostały wykonane?"><?php echo esc_textarea($scope); ?></textarea>
    </p>
    <p>
      <label for="ups_port_result"><strong>Efekt biznesowy</strong></label>
      <textarea id="ups_port_result" name="ups_port_result" class="widefat" rows="4" placeholder="Jaki był rezultat projektu?"><?php echo esc_textarea($result); ?></textarea>
    </p>
    <p>
      <label for="ups_port_metrics"><strong>Metryki projektu (jedna na linię)</strong></label>
      <textarea id="ups_port_metrics" name="ups_port_metrics" class="widefat" rows="5" placeholder="np. +42% zapytań&#10;-31% CPL&#10;+19% konwersji"><?php echo esc_textarea($metrics); ?></textarea>
    </p>
    <p>
      <label style="display:flex;align-items:flex-start;gap:8px;">
        <input type="checkbox" name="ups_port_featured" value="1" <?php checked($is_featured); ?> />
        <span>Ustaw jako wyróżniony projekt w katalogu portfolio.</span>
      </label>
    </p>
    <hr />
    <p><strong>Niestandardowy widok projektu (HTML + CSS + JS)</strong><br />
      <span style="color:#6b7280;">Wklej kod, jeśli ta realizacja ma mieć dedykowaną sekcję osadzoną na podstronie case study.</span>
    </p>
    <p>
      <label for="ups_port_custom_html"><strong>HTML</strong></label>
      <textarea id="ups_port_custom_html" name="ups_port_custom_html" class="widefat" rows="8"><?php echo esc_textarea($custom_html); ?></textarea>
    </p>
    <p>
      <label for="ups_port_custom_css"><strong>CSS</strong></label>
      <textarea id="ups_port_custom_css" name="ups_port_custom_css" class="widefat" rows="8"><?php echo esc_textarea($custom_css); ?></textarea>
    </p>
    <p>
      <label for="ups_port_custom_js"><strong>JS</strong></label>
      <textarea id="ups_port_custom_js" name="ups_port_custom_js" class="widefat" rows="8"><?php echo esc_textarea($custom_js); ?></textarea>
    </p>
    <?php
}

function upsellio_save_portfolio_details_meta_box($post_id)
{
    if (get_post_type((int) $post_id) !== "portfolio") {
        return;
    }
    if (!isset($_POST["upsellio_portfolio_details_nonce"])) {
        return;
    }

    $nonce = sanitize_text_field(wp_unslash($_POST["upsellio_portfolio_details_nonce"]));
    if (!wp_verify_nonce($nonce, "upsellio_portfolio_details")) {
        return;
    }
    if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can("edit_post", (int) $post_id)) {
        return;
    }

    $fields = [
        "_ups_port_type" => isset($_POST["ups_port_type"]) ? sanitize_text_field(wp_unslash($_POST["ups_port_type"])) : "",
        "_ups_port_meta" => isset($_POST["ups_port_meta"]) ? sanitize_text_field(wp_unslash($_POST["ups_port_meta"])) : "",
        "_ups_port_badge" => isset($_POST["ups_port_badge"]) ? sanitize_text_field(wp_unslash($_POST["ups_port_badge"])) : "",
        "_ups_port_cta" => isset($_POST["ups_port_cta"]) ? sanitize_text_field(wp_unslash($_POST["ups_port_cta"])) : "",
        "_ups_port_image" => isset($_POST["ups_port_image"]) ? esc_url_raw(wp_unslash($_POST["ups_port_image"])) : "",
        "_ups_port_external_url" => isset($_POST["ups_port_external_url"]) ? esc_url_raw(wp_unslash($_POST["ups_port_external_url"])) : "",
        "_ups_port_problem" => isset($_POST["ups_port_problem"]) ? sanitize_textarea_field(wp_unslash($_POST["ups_port_problem"])) : "",
        "_ups_port_scope" => isset($_POST["ups_port_scope"]) ? sanitize_textarea_field(wp_unslash($_POST["ups_port_scope"])) : "",
        "_ups_port_result" => isset($_POST["ups_port_result"]) ? sanitize_textarea_field(wp_unslash($_POST["ups_port_result"])) : "",
        "_ups_port_metrics" => isset($_POST["ups_port_metrics"]) ? sanitize_textarea_field(wp_unslash($_POST["ups_port_metrics"])) : "",
    ];

    foreach ($fields as $meta_key => $meta_value) {
        update_post_meta((int) $post_id, $meta_key, $meta_value);
    }

    update_post_meta((int) $post_id, "_ups_port_featured", isset($_POST["ups_port_featured"]) ? "1" : "0");

    $custom_html = isset($_POST["ups_port_custom_html"]) ? wp_unslash($_POST["ups_port_custom_html"]) : "";
    $custom_css = isset($_POST["ups_port_custom_css"]) ? wp_unslash($_POST["ups_port_custom_css"]) : "";
    $custom_js = isset($_POST["ups_port_custom_js"]) ? wp_unslash($_POST["ups_port_custom_js"]) : "";
    $payload = upsellio_prepare_custom_embed_payload((string) $custom_html, (string) $custom_css, (string) $custom_js);
    update_post_meta((int) $post_id, "_ups_port_custom_html", (string) $payload["html"]);
    update_post_meta((int) $post_id, "_ups_port_custom_css", (string) $payload["css"]);
    update_post_meta((int) $post_id, "_ups_port_custom_js", (string) $payload["js"]);
}
add_action("save_post", "upsellio_save_portfolio_details_meta_box");

function upsellio_parse_metrics_lines($value)
{
    $raw_lines = preg_split("/\r\n|\r|\n/", (string) $value);
    $lines = [];
    foreach ((array) $raw_lines as $line) {
        $line = trim((string) $line);
        if ($line === "") {
            continue;
        }
        $lines[] = $line;
    }

    return array_slice($lines, 0, 8);
}

function upsellio_get_portfolio_list($limit = 60)
{
    $query = new WP_Query([
        "post_type" => "portfolio",
        "post_status" => "publish",
        "posts_per_page" => max(1, (int) $limit),
        "orderby" => "menu_order date",
        "order" => "ASC",
    ]);

    $items = [];
    foreach ((array) $query->posts as $post_item) {
        $post_id = (int) $post_item->ID;
        $terms = get_the_terms($post_id, "portfolio_category");
        $first_term = (!empty($terms) && !is_wp_error($terms)) ? $terms[0] : null;

        $items[] = [
            "id" => $post_id,
            "title" => (string) get_the_title($post_id),
            "url" => (string) get_permalink($post_id),
            "excerpt" => (string) get_the_excerpt($post_id),
            "type" => (string) get_post_meta($post_id, "_ups_port_type", true),
            "meta" => (string) get_post_meta($post_id, "_ups_port_meta", true),
            "badge" => (string) get_post_meta($post_id, "_ups_port_badge", true),
            "cta" => (string) get_post_meta($post_id, "_ups_port_cta", true),
            "image" => (string) get_post_meta($post_id, "_ups_port_image", true),
            "problem" => (string) get_post_meta($post_id, "_ups_port_problem", true),
            "scope" => (string) get_post_meta($post_id, "_ups_port_scope", true),
            "result" => (string) get_post_meta($post_id, "_ups_port_result", true),
            "metrics" => upsellio_parse_metrics_lines((string) get_post_meta($post_id, "_ups_port_metrics", true)),
            "category" => $first_term ? (string) $first_term->name : "Realizacje",
            "category_slug" => $first_term ? (string) $first_term->slug : "realizacje",
            "is_featured" => (string) get_post_meta($post_id, "_ups_port_featured", true) === "1",
        ];
    }
    wp_reset_postdata();

    return $items;
}

function upsellio_ensure_portfolio_page_exists()
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }

    $portfolio_path = upsellio_get_special_navigation_path_by_title("Portfolio", "/portfolio/");
    $portfolio_slug = trim((string) wp_parse_url($portfolio_path, PHP_URL_PATH), "/");
    if ($portfolio_slug === "") {
        $portfolio_slug = "portfolio";
    }
    upsellio_upsert_page_with_template($portfolio_slug, "Portfolio", "page-portfolio.php");
}
add_action("admin_init", "upsellio_ensure_portfolio_page_exists");

function upsellio_get_marketing_portfolio_page_url()
{
    $marketing_portfolio_path = upsellio_get_special_navigation_path_by_title("Portfolio marketingowe", "/portfolio-marketingowe/");
    $page = get_page_by_path(trim($marketing_portfolio_path, "/"));
    if ($page instanceof WP_Post) {
        $permalink = get_permalink((int) $page->ID);
        if ($permalink) {
            return $permalink;
        }
    }

    return home_url($marketing_portfolio_path);
}

function upsellio_register_marketing_portfolio_cpt()
{
    register_post_type("marketing_portfolio", [
        "labels" => [
            "name" => "Portfolio marketingowe",
            "singular_name" => "Case marketingowy",
            "add_new" => "Dodaj case",
            "add_new_item" => "Dodaj nowy case marketingowy",
            "edit_item" => "Edytuj case marketingowy",
            "new_item" => "Nowy case marketingowy",
            "view_item" => "Zobacz case",
            "search_items" => "Szukaj case studies",
            "not_found" => "Nie znaleziono case studies",
            "menu_name" => "Portfolio marketingowe",
        ],
        "public" => true,
        "show_in_rest" => true,
        "menu_icon" => "dashicons-chart-line",
        "supports" => ["title", "editor", "excerpt", "thumbnail", "page-attributes"],
        "has_archive" => false,
        "rewrite" => ["slug" => "portfolio-marketingowe", "with_front" => false],
    ]);

    register_taxonomy("marketing_portfolio_category", ["marketing_portfolio"], [
        "labels" => [
            "name" => "Kategorie case studies",
            "singular_name" => "Kategoria case study",
            "search_items" => "Szukaj kategorii",
            "all_items" => "Wszystkie kategorie",
            "edit_item" => "Edytuj kategorię",
            "update_item" => "Aktualizuj kategorię",
            "add_new_item" => "Dodaj nową kategorię",
            "new_item_name" => "Nowa kategoria",
            "menu_name" => "Kategorie",
        ],
        "hierarchical" => true,
        "show_admin_column" => true,
        "show_in_rest" => true,
        "rewrite" => ["slug" => "kategoria-portfolio-marketingowego", "with_front" => false],
    ]);
}
add_action("init", "upsellio_register_marketing_portfolio_cpt");

function upsellio_add_marketing_portfolio_details_meta_box()
{
    add_meta_box(
        "upsellio_marketing_portfolio_details",
        "Dane case study marketingowego",
        "upsellio_render_marketing_portfolio_details_meta_box",
        "marketing_portfolio",
        "normal",
        "high"
    );
}
add_action("add_meta_boxes", "upsellio_add_marketing_portfolio_details_meta_box");

function upsellio_render_marketing_portfolio_details_meta_box($post)
{
    $post_id = (int) $post->ID;
    $type = (string) get_post_meta($post_id, "_ups_mport_type", true);
    $meta = (string) get_post_meta($post_id, "_ups_mport_meta", true);
    $badge = (string) get_post_meta($post_id, "_ups_mport_badge", true);
    $cta = (string) get_post_meta($post_id, "_ups_mport_cta", true);
    $image = (string) get_post_meta($post_id, "_ups_mport_image", true);
    $date = (string) get_post_meta($post_id, "_ups_mport_date", true);
    $sector = (string) get_post_meta($post_id, "_ups_mport_sector", true);
    $problem = (string) get_post_meta($post_id, "_ups_mport_problem", true);
    $solution = (string) get_post_meta($post_id, "_ups_mport_solution", true);
    $result = (string) get_post_meta($post_id, "_ups_mport_result", true);
    $tags = (string) get_post_meta($post_id, "_ups_mport_tags", true);
    $kpis = (string) get_post_meta($post_id, "_ups_mport_kpis", true);
    $theme = (string) get_post_meta($post_id, "_ups_mport_theme", true);
    $is_featured = (string) get_post_meta($post_id, "_ups_mport_featured", true) === "1";
    $custom_html = (string) get_post_meta($post_id, "_ups_mport_custom_html", true);
    $custom_css = (string) get_post_meta($post_id, "_ups_mport_custom_css", true);
    $custom_js = (string) get_post_meta($post_id, "_ups_mport_custom_js", true);
    $seo_title = (string) get_post_meta($post_id, "_ups_mport_seo_title", true);
    $seo_description = (string) get_post_meta($post_id, "_ups_mport_seo_description", true);
    $seo_canonical = (string) get_post_meta($post_id, "_ups_mport_seo_canonical", true);

    wp_nonce_field("upsellio_marketing_portfolio_details", "upsellio_marketing_portfolio_details_nonce");
    ?>
    <p>
      <label for="ups_mport_type"><strong>Typ case study</strong></label><br />
      <input type="text" id="ups_mport_type" name="ups_mport_type" value="<?php echo esc_attr($type); ?>" class="widefat" placeholder="np. Meta Ads, Google Ads, Landing page" />
    </p>
    <p>
      <label for="ups_mport_meta"><strong>Meta projektu</strong></label><br />
      <input type="text" id="ups_mport_meta" name="ups_mport_meta" value="<?php echo esc_attr($meta); ?>" class="widefat" placeholder="np. Lead generation · B2B · Q1 2024" />
    </p>
    <p>
      <label for="ups_mport_badge"><strong>Badge</strong></label><br />
      <input type="text" id="ups_mport_badge" name="ups_mport_badge" value="<?php echo esc_attr($badge); ?>" class="widefat" placeholder="np. Meta Ads" />
    </p>
    <p>
      <label for="ups_mport_cta"><strong>CTA na karcie/listingu</strong></label><br />
      <input type="text" id="ups_mport_cta" name="ups_mport_cta" value="<?php echo esc_attr($cta); ?>" class="widefat" placeholder="np. Zobacz case study" />
    </p>
    <p>
      <label for="ups_mport_image"><strong>URL obrazka</strong></label><br />
      <input type="url" id="ups_mport_image" name="ups_mport_image" value="<?php echo esc_attr($image); ?>" class="widefat" placeholder="https://..." />
    </p>
    <p>
      <label for="ups_mport_theme"><strong>Motyw wizualny karty</strong></label><br />
      <select id="ups_mport_theme" name="ups_mport_theme" class="widefat">
        <option value="">Domyślny</option>
        <option value="vis-meta" <?php selected($theme, "vis-meta"); ?>>Meta Ads</option>
        <option value="vis-google" <?php selected($theme, "vis-google"); ?>>Google Ads</option>
        <option value="vis-ecom" <?php selected($theme, "vis-ecom"); ?>>E-commerce</option>
        <option value="vis-landing" <?php selected($theme, "vis-landing"); ?>>Landing page</option>
        <option value="vis-b2b" <?php selected($theme, "vis-b2b"); ?>>B2B</option>
        <option value="vis-social" <?php selected($theme, "vis-social"); ?>>Social</option>
      </select>
    </p>
    <p>
      <label for="ups_mport_date"><strong>Data/okres case study</strong></label><br />
      <input type="text" id="ups_mport_date" name="ups_mport_date" value="<?php echo esc_attr($date); ?>" class="widefat" placeholder="np. Q1 2024" />
    </p>
    <p>
      <label for="ups_mport_sector"><strong>Sektor klienta</strong></label><br />
      <input type="text" id="ups_mport_sector" name="ups_mport_sector" value="<?php echo esc_attr($sector); ?>" class="widefat" placeholder="np. Firma usługowa B2B" />
    </p>
    <p>
      <label for="ups_mport_problem"><strong>Problem wyjściowy</strong></label>
      <textarea id="ups_mport_problem" name="ups_mport_problem" class="widefat" rows="4"><?php echo esc_textarea($problem); ?></textarea>
    </p>
    <p>
      <label for="ups_mport_solution"><strong>Rozwiązanie / zakres działań</strong></label>
      <textarea id="ups_mport_solution" name="ups_mport_solution" class="widefat" rows="4"><?php echo esc_textarea($solution); ?></textarea>
    </p>
    <p>
      <label for="ups_mport_result"><strong>Wynik biznesowy</strong></label>
      <textarea id="ups_mport_result" name="ups_mport_result" class="widefat" rows="4"><?php echo esc_textarea($result); ?></textarea>
    </p>
    <p>
      <label for="ups_mport_tags"><strong>Tagi (jedna pozycja na linię)</strong></label>
      <textarea id="ups_mport_tags" name="ups_mport_tags" class="widefat" rows="4" placeholder="Meta Ads&#10;Lead generation&#10;B2B"><?php echo esc_textarea($tags); ?></textarea>
    </p>
    <p>
      <label for="ups_mport_kpis"><strong>KPI rows (label|przed|po|zmiana|opis, jedna linia = jeden KPI)</strong></label>
      <textarea id="ups_mport_kpis" name="ups_mport_kpis" class="widefat" rows="6" placeholder="CPL|312 PLN|150 PLN|-52%|w 4 miesiące"><?php echo esc_textarea($kpis); ?></textarea>
    </p>
    <p>
      <label style="display:flex;align-items:flex-start;gap:8px;">
        <input type="checkbox" name="ups_mport_featured" value="1" <?php checked($is_featured); ?> />
        <span>Wyróżnij ten case na stronie głównej portfolio marketingowego.</span>
      </label>
    </p>
    <hr />
    <p><strong>SEO per case study</strong><br />
      <span style="color:#6b7280;">Uzupełnij pola, aby nadpisać domyślny title/description/canonical dla tego wpisu.</span>
    </p>
    <p>
      <label for="ups_mport_seo_title"><strong>Meta title</strong></label><br />
      <input type="text" id="ups_mport_seo_title" name="ups_mport_seo_title" value="<?php echo esc_attr($seo_title); ?>" class="widefat" maxlength="160" placeholder="np. Case Meta Ads B2B -52% CPL | Upsellio" />
    </p>
    <p>
      <label for="ups_mport_seo_description"><strong>Meta description</strong></label><br />
      <textarea id="ups_mport_seo_description" name="ups_mport_seo_description" class="widefat" rows="3" maxlength="320" placeholder="Krótki opis case study do wyników wyszukiwania."><?php echo esc_textarea($seo_description); ?></textarea>
    </p>
    <p>
      <label for="ups_mport_seo_canonical"><strong>Canonical URL</strong></label><br />
      <input type="url" id="ups_mport_seo_canonical" name="ups_mport_seo_canonical" value="<?php echo esc_attr($seo_canonical); ?>" class="widefat" placeholder="https://twojadomena.pl/portfolio-marketingowe/nazwa-case-study/" />
    </p>
    <hr />
    <p><strong>Niestandardowy blok HTML + CSS + JS</strong><br />
      <span style="color:#6b7280;">Możesz osadzić interaktywną sekcję case study na stronie wpisu.</span>
    </p>
    <p>
      <label for="ups_mport_custom_html"><strong>HTML</strong></label>
      <textarea id="ups_mport_custom_html" name="ups_mport_custom_html" class="widefat" rows="8"><?php echo esc_textarea($custom_html); ?></textarea>
    </p>
    <p>
      <label for="ups_mport_custom_css"><strong>CSS</strong></label>
      <textarea id="ups_mport_custom_css" name="ups_mport_custom_css" class="widefat" rows="8"><?php echo esc_textarea($custom_css); ?></textarea>
    </p>
    <p>
      <label for="ups_mport_custom_js"><strong>JS</strong></label>
      <textarea id="ups_mport_custom_js" name="ups_mport_custom_js" class="widefat" rows="8"><?php echo esc_textarea($custom_js); ?></textarea>
    </p>
    <?php
}

function upsellio_save_marketing_portfolio_details_meta_box($post_id)
{
    if (get_post_type((int) $post_id) !== "marketing_portfolio") {
        return;
    }
    if (!isset($_POST["upsellio_marketing_portfolio_details_nonce"])) {
        return;
    }
    $nonce = sanitize_text_field(wp_unslash($_POST["upsellio_marketing_portfolio_details_nonce"]));
    if (!wp_verify_nonce($nonce, "upsellio_marketing_portfolio_details")) {
        return;
    }
    if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can("edit_post", (int) $post_id)) {
        return;
    }

    $fields = [
        "_ups_mport_type" => isset($_POST["ups_mport_type"]) ? sanitize_text_field(wp_unslash($_POST["ups_mport_type"])) : "",
        "_ups_mport_meta" => isset($_POST["ups_mport_meta"]) ? sanitize_text_field(wp_unslash($_POST["ups_mport_meta"])) : "",
        "_ups_mport_badge" => isset($_POST["ups_mport_badge"]) ? sanitize_text_field(wp_unslash($_POST["ups_mport_badge"])) : "",
        "_ups_mport_cta" => isset($_POST["ups_mport_cta"]) ? sanitize_text_field(wp_unslash($_POST["ups_mport_cta"])) : "",
        "_ups_mport_image" => isset($_POST["ups_mport_image"]) ? esc_url_raw(wp_unslash($_POST["ups_mport_image"])) : "",
        "_ups_mport_theme" => isset($_POST["ups_mport_theme"]) ? sanitize_key(wp_unslash($_POST["ups_mport_theme"])) : "",
        "_ups_mport_date" => isset($_POST["ups_mport_date"]) ? sanitize_text_field(wp_unslash($_POST["ups_mport_date"])) : "",
        "_ups_mport_sector" => isset($_POST["ups_mport_sector"]) ? sanitize_text_field(wp_unslash($_POST["ups_mport_sector"])) : "",
        "_ups_mport_problem" => isset($_POST["ups_mport_problem"]) ? sanitize_textarea_field(wp_unslash($_POST["ups_mport_problem"])) : "",
        "_ups_mport_solution" => isset($_POST["ups_mport_solution"]) ? sanitize_textarea_field(wp_unslash($_POST["ups_mport_solution"])) : "",
        "_ups_mport_result" => isset($_POST["ups_mport_result"]) ? sanitize_textarea_field(wp_unslash($_POST["ups_mport_result"])) : "",
        "_ups_mport_tags" => isset($_POST["ups_mport_tags"]) ? sanitize_textarea_field(wp_unslash($_POST["ups_mport_tags"])) : "",
        "_ups_mport_kpis" => isset($_POST["ups_mport_kpis"]) ? sanitize_textarea_field(wp_unslash($_POST["ups_mport_kpis"])) : "",
        "_ups_mport_seo_title" => isset($_POST["ups_mport_seo_title"]) ? sanitize_text_field(wp_unslash($_POST["ups_mport_seo_title"])) : "",
        "_ups_mport_seo_description" => isset($_POST["ups_mport_seo_description"]) ? sanitize_textarea_field(wp_unslash($_POST["ups_mport_seo_description"])) : "",
        "_ups_mport_seo_canonical" => isset($_POST["ups_mport_seo_canonical"]) ? esc_url_raw(wp_unslash($_POST["ups_mport_seo_canonical"])) : "",
    ];

    foreach ($fields as $meta_key => $meta_value) {
        update_post_meta((int) $post_id, $meta_key, $meta_value);
    }
    update_post_meta((int) $post_id, "_ups_mport_featured", isset($_POST["ups_mport_featured"]) ? "1" : "0");

    $custom_html = isset($_POST["ups_mport_custom_html"]) ? wp_unslash($_POST["ups_mport_custom_html"]) : "";
    $custom_css = isset($_POST["ups_mport_custom_css"]) ? wp_unslash($_POST["ups_mport_custom_css"]) : "";
    $custom_js = isset($_POST["ups_mport_custom_js"]) ? wp_unslash($_POST["ups_mport_custom_js"]) : "";
    $payload = upsellio_prepare_custom_embed_payload((string) $custom_html, (string) $custom_css, (string) $custom_js);
    update_post_meta((int) $post_id, "_ups_mport_custom_html", (string) $payload["html"]);
    update_post_meta((int) $post_id, "_ups_mport_custom_css", (string) $payload["css"]);
    update_post_meta((int) $post_id, "_ups_mport_custom_js", (string) $payload["js"]);
}
add_action("save_post", "upsellio_save_marketing_portfolio_details_meta_box");

function upsellio_parse_textarea_lines($value, $limit = 12)
{
    $raw_lines = preg_split("/\r\n|\r|\n/", (string) $value);
    $lines = [];
    foreach ((array) $raw_lines as $line) {
        $line = trim((string) $line);
        if ($line === "") {
            continue;
        }
        $lines[] = $line;
    }

    return array_slice($lines, 0, max(1, (int) $limit));
}

function upsellio_parse_marketing_kpi_lines($value)
{
    $rows = [];
    $lines = upsellio_parse_textarea_lines($value, 8);
    foreach ($lines as $line) {
        $parts = array_map("trim", explode("|", (string) $line));
        $rows[] = [
            "label" => (string) ($parts[0] ?? ""),
            "before" => (string) ($parts[1] ?? ""),
            "after" => (string) ($parts[2] ?? ""),
            "change" => (string) ($parts[3] ?? ""),
            "desc" => (string) ($parts[4] ?? ""),
        ];
    }

    return $rows;
}

function upsellio_get_marketing_portfolio_category_mapping($source_name = "", $source_slug = "")
{
    $normalized_slug = sanitize_title((string) $source_slug);
    $normalized_name = sanitize_title((string) $source_name);
    $lookup = strtolower(trim($normalized_slug !== "" ? $normalized_slug : $normalized_name));

    $map = [
        "meta" => ["label" => "Meta", "theme" => "vis-meta", "aliases" => ["meta", "meta-ads", "facebook", "facebook-ads", "social"]],
        "google" => ["label" => "Google", "theme" => "vis-google", "aliases" => ["google", "google-ads", "ads", "search", "performance-max", "pmax"]],
        "strona" => ["label" => "Strona", "theme" => "vis-landing", "aliases" => ["strona", "strony", "strony-www", "strona-www", "landing", "landing-page", "www"]],
        "ecom" => ["label" => "Ecom", "theme" => "vis-ecom", "aliases" => ["ecom", "ecommerce", "e-commerce", "sklep", "sklep-online", "woocommerce"]],
    ];

    foreach ($map as $target_slug => $entry) {
        if ($lookup === $target_slug || in_array($lookup, (array) $entry["aliases"], true)) {
            return [
                "slug" => $target_slug,
                "label" => (string) $entry["label"],
                "theme" => (string) $entry["theme"],
            ];
        }
    }

    return [
        "slug" => "meta",
        "label" => "Meta",
        "theme" => "vis-meta",
    ];
}

function upsellio_sync_marketing_portfolio_primary_category($post_id, $post, $update)
{
    if (!(int) $post_id || !($post instanceof WP_Post) || $post->post_type !== "marketing_portfolio") {
        return;
    }
    if (wp_is_post_revision((int) $post_id) || (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)) {
        return;
    }

    $terms = get_the_terms((int) $post_id, "marketing_portfolio_category");
    $first_term = (!empty($terms) && !is_wp_error($terms)) ? $terms[0] : null;
    $mapped = upsellio_get_marketing_portfolio_category_mapping($first_term ? (string) $first_term->name : "", $first_term ? (string) $first_term->slug : "");

    $target_term = term_exists((string) $mapped["slug"], "marketing_portfolio_category");
    if (!$target_term) {
        $target_term = wp_insert_term((string) $mapped["label"], "marketing_portfolio_category", ["slug" => (string) $mapped["slug"]]);
    }
    if (is_wp_error($target_term)) {
        return;
    }

    $target_term_id = (int) (is_array($target_term) ? ($target_term["term_id"] ?? 0) : 0);
    if ($target_term_id > 0) {
        wp_set_object_terms((int) $post_id, [$target_term_id], "marketing_portfolio_category");
    }
}
add_action("save_post_marketing_portfolio", "upsellio_sync_marketing_portfolio_primary_category", 20, 3);

function upsellio_get_marketing_portfolio_list($limit = 120)
{
    $query = new WP_Query([
        "post_type" => "marketing_portfolio",
        "post_status" => "publish",
        "posts_per_page" => max(1, (int) $limit),
        "orderby" => "menu_order date",
        "order" => "ASC",
    ]);

    $items = [];
    foreach ((array) $query->posts as $post_item) {
        $post_id = (int) $post_item->ID;
        $terms = get_the_terms($post_id, "marketing_portfolio_category");
        $first_term = (!empty($terms) && !is_wp_error($terms)) ? $terms[0] : null;
        $mapped_category = upsellio_get_marketing_portfolio_category_mapping($first_term ? (string) $first_term->name : "", $first_term ? (string) $first_term->slug : "");
        $theme = (string) get_post_meta($post_id, "_ups_mport_theme", true);
        if ($theme === "") {
            $theme = (string) $mapped_category["theme"];
        }
        $items[] = [
            "id" => $post_id,
            "title" => (string) get_the_title($post_id),
            "url" => (string) get_permalink($post_id),
            "excerpt" => (string) get_the_excerpt($post_id),
            "type" => (string) get_post_meta($post_id, "_ups_mport_type", true),
            "meta" => (string) get_post_meta($post_id, "_ups_mport_meta", true),
            "badge" => (string) get_post_meta($post_id, "_ups_mport_badge", true),
            "cta" => (string) get_post_meta($post_id, "_ups_mport_cta", true),
            "image" => (string) get_post_meta($post_id, "_ups_mport_image", true),
            "theme" => $theme,
            "date" => (string) get_post_meta($post_id, "_ups_mport_date", true),
            "sector" => (string) get_post_meta($post_id, "_ups_mport_sector", true),
            "problem" => (string) get_post_meta($post_id, "_ups_mport_problem", true),
            "solution" => (string) get_post_meta($post_id, "_ups_mport_solution", true),
            "result" => (string) get_post_meta($post_id, "_ups_mport_result", true),
            "tags" => upsellio_parse_textarea_lines((string) get_post_meta($post_id, "_ups_mport_tags", true), 8),
            "kpis" => upsellio_parse_marketing_kpi_lines((string) get_post_meta($post_id, "_ups_mport_kpis", true)),
            "category" => (string) $mapped_category["label"],
            "category_slug" => (string) $mapped_category["slug"],
            "is_featured" => (string) get_post_meta($post_id, "_ups_mport_featured", true) === "1",
            "custom_html" => (string) get_post_meta($post_id, "_ups_mport_custom_html", true),
            "custom_css" => (string) get_post_meta($post_id, "_ups_mport_custom_css", true),
            "custom_js" => (string) get_post_meta($post_id, "_ups_mport_custom_js", true),
            "seo_title" => (string) get_post_meta($post_id, "_ups_mport_seo_title", true),
            "seo_description" => (string) get_post_meta($post_id, "_ups_mport_seo_description", true),
            "seo_canonical" => (string) get_post_meta($post_id, "_ups_mport_seo_canonical", true),
        ];
    }
    wp_reset_postdata();

    return $items;
}

function upsellio_ensure_marketing_portfolio_page_exists()
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }

    $marketing_portfolio_path = upsellio_get_special_navigation_path_by_title("Portfolio marketingowe", "/portfolio-marketingowe/");
    $marketing_portfolio_slug = trim((string) wp_parse_url($marketing_portfolio_path, PHP_URL_PATH), "/");
    if ($marketing_portfolio_slug === "") {
        $marketing_portfolio_slug = "portfolio-marketingowe";
    }
    upsellio_upsert_page_with_template($marketing_portfolio_slug, "Portfolio marketingowe", "page-portfolio-marketingowe.php");
}
add_action("admin_init", "upsellio_ensure_marketing_portfolio_page_exists");

function upsellio_get_marketing_portfolio_seo_payload($post_id)
{
    $post_id = (int) $post_id;
    if ($post_id <= 0 || get_post_type($post_id) !== "marketing_portfolio") {
        return [];
    }

    $title = trim((string) get_post_meta($post_id, "_ups_mport_seo_title", true));
    $description = trim((string) get_post_meta($post_id, "_ups_mport_seo_description", true));
    $canonical = trim((string) get_post_meta($post_id, "_ups_mport_seo_canonical", true));
    $fallback_description = (string) get_the_excerpt($post_id);
    if ($description === "") {
        $description = wp_strip_all_tags($fallback_description);
    }

    return [
        "title" => $title,
        "description" => wp_trim_words($description, 34, ""),
        "canonical" => $canonical !== "" ? esc_url_raw($canonical) : (string) get_permalink($post_id),
    ];
}

function upsellio_marketing_portfolio_document_title($title)
{
    if (!is_singular("marketing_portfolio")) {
        return $title;
    }

    $post_id = (int) get_queried_object_id();
    $seo_payload = upsellio_get_marketing_portfolio_seo_payload($post_id);
    $custom_title = trim((string) ($seo_payload["title"] ?? ""));

    return $custom_title !== "" ? $custom_title : $title;
}
add_filter("pre_get_document_title", "upsellio_marketing_portfolio_document_title");

function upsellio_print_marketing_portfolio_seo_meta()
{
    if (!is_singular("marketing_portfolio")) {
        return;
    }

    $post_id = (int) get_queried_object_id();
    $seo_payload = upsellio_get_marketing_portfolio_seo_payload($post_id);
    $description = trim((string) ($seo_payload["description"] ?? ""));
    $canonical = trim((string) ($seo_payload["canonical"] ?? ""));
    $title = trim((string) ($seo_payload["title"] ?? get_the_title($post_id)));

    if ($description !== "") {
        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
    }
    if ($title !== "") {
        echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
    }
    if ($canonical !== "") {
        echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url($canonical) . '">' . "\n";
    }
}
add_action("wp_head", "upsellio_print_marketing_portfolio_seo_meta", 3);

function upsellio_get_supported_error_codes()
{
    return [400, 401, 403, 404, 429, 500, 503];
}

function upsellio_get_marketing_portfolio_redirect_aliases()
{
    return [
        "meta-ads" => "meta",
        "facebook" => "meta",
        "facebook-ads" => "meta",
        "social" => "meta",
        "google-ads" => "google",
        "ads" => "google",
        "search" => "google",
        "performance-max" => "google",
        "pmax" => "google",
        "strony" => "strona",
        "strony-www" => "strona",
        "strona-www" => "strona",
        "landing" => "strona",
        "landing-page" => "strona",
        "www" => "strona",
        "ecommerce" => "ecom",
        "e-commerce" => "ecom",
        "sklep" => "ecom",
        "sklep-online" => "ecom",
        "woocommerce" => "ecom",
    ];
}

function upsellio_get_marketing_portfolio_category_redirect_target($requested_slug)
{
    $requested_slug = sanitize_title((string) $requested_slug);
    if ($requested_slug === "") {
        return "";
    }

    $aliases = upsellio_get_marketing_portfolio_redirect_aliases();

    return isset($aliases[$requested_slug]) ? (string) $aliases[$requested_slug] : "";
}

function upsellio_maybe_redirect_legacy_marketing_portfolio_category_slug()
{
    if (is_admin() || wp_doing_ajax() || (defined("REST_REQUEST") && REST_REQUEST)) {
        return;
    }

    $taxonomy_base = "kategoria-portfolio-marketingowego";
    $request_uri = isset($_SERVER["REQUEST_URI"]) ? (string) wp_unslash($_SERVER["REQUEST_URI"]) : "";
    $request_path = (string) parse_url($request_uri, PHP_URL_PATH);
    $request_path = trim($request_path, "/");
    if ($request_path === "") {
        return;
    }

    $match = [];
    if (!preg_match("#^" . preg_quote($taxonomy_base, "#") . "/([^/]+)/?$#", $request_path, $match)) {
        return;
    }

    $requested_slug = isset($match[1]) ? sanitize_title((string) $match[1]) : "";
    $target_slug = upsellio_get_marketing_portfolio_category_redirect_target($requested_slug);
    if ($target_slug === "") {
        return;
    }

    $target_url = home_url("/" . $taxonomy_base . "/" . $target_slug . "/");
    $query_string = (string) parse_url($request_uri, PHP_URL_QUERY);
    if ($query_string !== "") {
        $target_url = $target_url . "?" . $query_string;
    }

    wp_safe_redirect($target_url, 301);
    exit;
}
add_action("template_redirect", "upsellio_maybe_redirect_legacy_marketing_portfolio_category_slug", 1);

function upsellio_register_error_page_rewrite()
{
    add_rewrite_rule("^blad/([0-9]{3})/?$", "index.php?ups_error_code=$matches[1]", "top");
}
add_action("init", "upsellio_register_error_page_rewrite");

function upsellio_add_error_query_var($query_vars)
{
    $query_vars[] = "ups_error_code";

    return $query_vars;
}
add_filter("query_vars", "upsellio_add_error_query_var");

function upsellio_maybe_flush_error_page_rewrite()
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }

    $version_key = "upsellio_error_page_rewrite_version";
    $target_version = "2026-04-21-1";
    $current_version = (string) get_option($version_key, "");
    if ($current_version === $target_version) {
        return;
    }

    flush_rewrite_rules(false);
    update_option($version_key, $target_version, false);
}
add_action("admin_init", "upsellio_maybe_flush_error_page_rewrite");

function upsellio_get_forced_error_code()
{
    if (is_404()) {
        return 404;
    }

    $requested_code = (int) get_query_var("ups_error_code");
    if ($requested_code <= 0) {
        return 0;
    }

    return in_array($requested_code, upsellio_get_supported_error_codes(), true) ? $requested_code : 0;
}

function upsellio_render_forced_error_template()
{
    $error_code = upsellio_get_forced_error_code();
    if ($error_code <= 0 || is_404()) {
        return;
    }

    status_header($error_code);
    nocache_headers();
    $GLOBALS["upsellio_forced_error_code"] = $error_code;
    $GLOBALS["upsellio_error_context"] = upsellio_prepare_error_page_context($error_code);
    require get_template_directory() . "/template-error-modern.php";
    exit;
}
add_action("template_redirect", "upsellio_render_forced_error_template", 2);

function upsellio_error_pages_document_title($title)
{
    $error_code = upsellio_get_forced_error_code();
    if ($error_code <= 0) {
        return $title;
    }

    return "Błąd " . $error_code . " | Upsellio";
}
add_filter("pre_get_document_title", "upsellio_error_pages_document_title");

function upsellio_print_error_pages_meta()
{
    $error_code = upsellio_get_forced_error_code();
    if ($error_code <= 0) {
        return;
    }

    echo '<meta name="robots" content="noindex, nofollow">' . "\n";
}
add_action("wp_head", "upsellio_print_error_pages_meta", 2);

function upsellio_get_error_log_option_key()
{
    return "upsellio_error_log_entries_v1";
}

function upsellio_get_error_logs_limit()
{
    return 400;
}

function upsellio_get_error_logs()
{
    $logs = get_option(upsellio_get_error_log_option_key(), []);

    return is_array($logs) ? $logs : [];
}

function upsellio_get_error_logs_filters_from_request()
{
    $preset = isset($_GET["filter_preset"]) ? sanitize_key((string) wp_unslash($_GET["filter_preset"])) : "";
    $type = isset($_GET["filter_type"]) ? sanitize_key((string) wp_unslash($_GET["filter_type"])) : "";
    $status_code = isset($_GET["filter_code"]) ? (int) $_GET["filter_code"] : 0;
    $date_from = isset($_GET["filter_date_from"]) ? sanitize_text_field((string) wp_unslash($_GET["filter_date_from"])) : "";
    $date_to = isset($_GET["filter_date_to"]) ? sanitize_text_field((string) wp_unslash($_GET["filter_date_to"])) : "";

    if ($date_from !== "" && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from)) {
        $date_from = "";
    }
    if ($date_to !== "" && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)) {
        $date_to = "";
    }
    if ($preset === "last_24h") {
        $date_from = gmdate("Y-m-d", time() - DAY_IN_SECONDS);
        $date_to = gmdate("Y-m-d");
    } elseif ($preset === "only_js") {
        $type = "js";
        $status_code = 0;
    } elseif ($preset === "only_5xx") {
        $type = "http";
    } else {
        $preset = "";
    }

    return [
        "preset" => $preset,
        "type" => $type,
        "status_code" => $status_code > 0 ? $status_code : 0,
        "date_from" => $date_from,
        "date_to" => $date_to,
    ];
}

function upsellio_filter_error_logs($logs, $filters)
{
    $logs = is_array($logs) ? $logs : [];
    $type = isset($filters["type"]) ? sanitize_key((string) $filters["type"]) : "";
    $status_code = isset($filters["status_code"]) ? (int) $filters["status_code"] : 0;
    $date_from = isset($filters["date_from"]) ? (string) $filters["date_from"] : "";
    $date_to = isset($filters["date_to"]) ? (string) $filters["date_to"] : "";
    $preset = isset($filters["preset"]) ? sanitize_key((string) $filters["preset"]) : "";

    $date_from_ts = $date_from !== "" ? strtotime($date_from . " 00:00:00") : 0;
    $date_to_ts = $date_to !== "" ? strtotime($date_to . " 23:59:59") : 0;

    return array_values(array_filter($logs, function ($row) use ($type, $status_code, $date_from_ts, $date_to_ts, $preset) {
        $row_type = isset($row["type"]) ? sanitize_key((string) $row["type"]) : "";
        $row_code = isset($row["status_code"]) ? (int) $row["status_code"] : 0;
        $row_ts = isset($row["timestamp"]) ? strtotime((string) $row["timestamp"]) : 0;

        if ($type !== "" && $row_type !== $type) {
            return false;
        }
        if ($status_code > 0 && $row_code !== $status_code) {
            return false;
        }
        if ($preset === "only_5xx" && ($row_code < 500 || $row_code > 599)) {
            return false;
        }
        if ($date_from_ts > 0 && ($row_ts <= 0 || $row_ts < $date_from_ts)) {
            return false;
        }
        if ($date_to_ts > 0 && ($row_ts <= 0 || $row_ts > $date_to_ts)) {
            return false;
        }

        return true;
    }));
}

function upsellio_output_error_logs_csv($rows)
{
    nocache_headers();
    header("Content-Type: text/csv; charset=utf-8");
    header("Content-Disposition: attachment; filename=upsellio-error-logs-" . gmdate("Ymd-His") . ".csv");

    $stream = fopen("php://output", "w");
    if ($stream === false) {
        exit;
    }

    fputcsv($stream, ["timestamp", "type", "status_code", "incident_id", "message", "url", "path", "referrer", "client_hash", "context_json"]);
    foreach ((array) $rows as $row) {
        $context = isset($row["context"]) && is_array($row["context"]) ? wp_json_encode($row["context"], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : "";
        fputcsv($stream, [
            isset($row["timestamp"]) ? (string) $row["timestamp"] : "",
            isset($row["type"]) ? (string) $row["type"] : "",
            isset($row["status_code"]) ? (string) ((int) $row["status_code"]) : "0",
            isset($row["incident_id"]) ? (string) $row["incident_id"] : "",
            isset($row["message"]) ? (string) $row["message"] : "",
            isset($row["url"]) ? (string) $row["url"] : "",
            isset($row["path"]) ? (string) $row["path"] : "",
            isset($row["referrer"]) ? (string) $row["referrer"] : "",
            isset($row["client_hash"]) ? (string) $row["client_hash"] : "",
            (string) $context,
        ]);
    }
    fclose($stream);
    exit;
}

function upsellio_generate_incident_id()
{
    $raw = wp_generate_uuid4();
    $normalized = strtoupper(str_replace("-", "", (string) $raw));

    return substr($normalized, 0, 12);
}

function upsellio_get_request_path()
{
    $request_uri = isset($_SERVER["REQUEST_URI"]) ? (string) wp_unslash($_SERVER["REQUEST_URI"]) : "/";
    $path = (string) parse_url($request_uri, PHP_URL_PATH);

    return $path !== "" ? $path : "/";
}

function upsellio_get_client_hash()
{
    $ip = isset($_SERVER["REMOTE_ADDR"]) ? (string) wp_unslash($_SERVER["REMOTE_ADDR"]) : "unknown";
    $salt = (string) wp_salt("auth");

    return substr(hash("sha256", $ip . "|" . $salt), 0, 18);
}

function upsellio_log_error_event($payload)
{
    $defaults = [
        "timestamp" => current_time("mysql"),
        "timestamp_gmt" => gmdate("c"),
        "incident_id" => "",
        "type" => "http",
        "status_code" => 0,
        "message" => "",
        "url" => home_url(upsellio_get_request_path()),
        "path" => upsellio_get_request_path(),
        "referrer" => isset($_SERVER["HTTP_REFERER"]) ? esc_url_raw((string) wp_unslash($_SERVER["HTTP_REFERER"])) : "",
        "user_agent" => isset($_SERVER["HTTP_USER_AGENT"]) ? sanitize_text_field((string) wp_unslash($_SERVER["HTTP_USER_AGENT"])) : "",
        "client_hash" => upsellio_get_client_hash(),
        "context" => [],
    ];
    $entry = wp_parse_args((array) $payload, $defaults);
    $entry["status_code"] = (int) $entry["status_code"];
    $entry["message"] = sanitize_text_field((string) $entry["message"]);
    $entry["path"] = sanitize_text_field((string) $entry["path"]);
    $entry["url"] = esc_url_raw((string) $entry["url"]);
    $entry["referrer"] = esc_url_raw((string) $entry["referrer"]);
    $entry["type"] = sanitize_key((string) $entry["type"]);
    $entry["incident_id"] = sanitize_text_field((string) $entry["incident_id"]);
    $entry["context"] = is_array($entry["context"]) ? $entry["context"] : [];

    if ($entry["incident_id"] === "" && in_array((int) $entry["status_code"], [500, 503], true)) {
        $entry["incident_id"] = upsellio_generate_incident_id();
    }

    $fingerprint = md5(implode("|", [
        (string) $entry["type"],
        (string) $entry["status_code"],
        (string) $entry["path"],
        (string) $entry["message"],
        (string) $entry["client_hash"],
    ]));
    $dedupe_key = "upsellio_err_log_" . $fingerprint;
    if (get_transient($dedupe_key)) {
        return (string) $entry["incident_id"];
    }
    set_transient($dedupe_key, "1", 90);

    $logs = upsellio_get_error_logs();
    array_unshift($logs, $entry);
    $logs = array_slice($logs, 0, upsellio_get_error_logs_limit());
    update_option(upsellio_get_error_log_option_key(), $logs, false);

    return (string) $entry["incident_id"];
}

function upsellio_prepare_error_page_context($error_code)
{
    $error_code = (int) $error_code;
    $timestamp_iso = current_time("c");
    $incident_id = in_array($error_code, [500, 503], true) ? upsellio_generate_incident_id() : "";

    upsellio_log_error_event([
        "type" => "http",
        "status_code" => $error_code,
        "incident_id" => $incident_id,
        "message" => "Rendered error page",
        "path" => upsellio_get_request_path(),
        "url" => home_url(upsellio_get_request_path()),
        "context" => [
            "template" => "template-error-modern.php",
        ],
    ]);

    return [
        "error_code" => $error_code,
        "timestamp_iso" => $timestamp_iso,
        "incident_id" => $incident_id,
    ];
}

function upsellio_track_frontend_error_ajax()
{
    check_ajax_referer("upsellio_frontend_error_logger", "nonce");

    $message = isset($_POST["message"]) ? sanitize_text_field((string) wp_unslash($_POST["message"])) : "";
    $source = isset($_POST["source"]) ? sanitize_text_field((string) wp_unslash($_POST["source"])) : "";
    $line = isset($_POST["line"]) ? (int) $_POST["line"] : 0;
    $column = isset($_POST["column"]) ? (int) $_POST["column"] : 0;
    $stack = isset($_POST["stack"]) ? sanitize_textarea_field((string) wp_unslash($_POST["stack"])) : "";
    $page_url = isset($_POST["page_url"]) ? esc_url_raw((string) wp_unslash($_POST["page_url"])) : "";

    if ($message === "" || $page_url === "") {
        wp_send_json_error(["message" => "missing_payload"], 400);
    }
    $page_host = (string) wp_parse_url($page_url, PHP_URL_HOST);
    $site_host = (string) wp_parse_url(home_url("/"), PHP_URL_HOST);
    if ($page_host === "" || $site_host === "" || strtolower($page_host) !== strtolower($site_host)) {
        wp_send_json_error(["message" => "invalid_host"], 400);
    }

    $client_ip = isset($_SERVER["REMOTE_ADDR"]) ? (string) wp_unslash($_SERVER["REMOTE_ADDR"]) : "";
    $client_ip = preg_replace("/[^0-9a-fA-F:\\.]/", "", $client_ip);
    if ($client_ip === null || $client_ip === "") {
        $client_ip = "unknown";
    }
    $rate_limit_key = "ups_errlog_" . md5($client_ip);
    $request_count = (int) get_transient($rate_limit_key);
    if ($request_count >= 25) {
        wp_send_json_error(["message" => "rate_limited"], 429);
    }
    set_transient($rate_limit_key, $request_count + 1, MINUTE_IN_SECONDS);

    $incident_id = upsellio_log_error_event([
        "type" => "js",
        "status_code" => 0,
        "incident_id" => upsellio_generate_incident_id(),
        "message" => $message,
        "path" => (string) wp_parse_url($page_url, PHP_URL_PATH),
        "url" => $page_url,
        "context" => [
            "source" => $source,
            "line" => $line,
            "column" => $column,
            "stack" => $stack,
        ],
    ]);

    wp_send_json_success(["incident_id" => $incident_id], 200);
}
add_action("wp_ajax_upsellio_track_frontend_error", "upsellio_track_frontend_error_ajax");
add_action("wp_ajax_nopriv_upsellio_track_frontend_error", "upsellio_track_frontend_error_ajax");

function upsellio_print_frontend_error_logger_script()
{
    if (is_admin()) {
        return;
    }

    $ajax_url = admin_url("admin-ajax.php");
    $nonce = wp_create_nonce("upsellio_frontend_error_logger");
    ?>
    <script>
    (function () {
      var ajaxUrl = <?php echo wp_json_encode($ajax_url); ?>;
      var nonce = <?php echo wp_json_encode($nonce); ?>;
      var sent = 0;
      var limit = 3;
      function send(payload) {
        if (!ajaxUrl || sent >= limit) return;
        sent += 1;
        var body = new URLSearchParams();
        body.set("action", "upsellio_track_frontend_error");
        body.set("nonce", nonce);
        body.set("message", String(payload.message || "").slice(0, 400));
        body.set("source", String(payload.source || "").slice(0, 400));
        body.set("line", String(payload.line || 0));
        body.set("column", String(payload.column || 0));
        body.set("stack", String(payload.stack || "").slice(0, 2000));
        body.set("page_url", window.location.href);
        if (navigator.sendBeacon) {
          navigator.sendBeacon(ajaxUrl, body);
          return;
        }
        fetch(ajaxUrl, { method: "POST", body: body, keepalive: true, credentials: "same-origin" }).catch(function () {});
      }
      window.addEventListener("error", function (event) {
        send({
          message: event && event.message ? event.message : "window.error",
          source: event && event.filename ? event.filename : "unknown",
          line: event && event.lineno ? event.lineno : 0,
          column: event && event.colno ? event.colno : 0,
          stack: event && event.error && event.error.stack ? event.error.stack : ""
        });
      });
      window.addEventListener("unhandledrejection", function (event) {
        var reason = event && event.reason ? event.reason : {};
        send({
          message: reason && reason.message ? reason.message : "unhandledrejection",
          source: "promise",
          line: 0,
          column: 0,
          stack: reason && reason.stack ? reason.stack : ""
        });
      });
    })();
    </script>
    <?php
}
add_action("wp_footer", "upsellio_print_frontend_error_logger_script", 99);

function upsellio_register_error_logs_admin_page()
{
    add_management_page(
        "Dziennik błędów Upsellio",
        "Dziennik błędów",
        "manage_options",
        "upsellio-error-logs",
        "upsellio_render_error_logs_admin_page"
    );
}
add_action("admin_menu", "upsellio_register_error_logs_admin_page");

function upsellio_handle_error_logs_admin_actions()
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }
    if (!isset($_GET["upsellio_error_logs_action"])) {
        return;
    }

    $action = sanitize_key((string) wp_unslash($_GET["upsellio_error_logs_action"]));
    if (!in_array($action, ["clear", "export_csv"], true)) {
        return;
    }

    if ($action === "clear") {
        $nonce = isset($_GET["_upsellio_nonce"]) ? sanitize_text_field((string) wp_unslash($_GET["_upsellio_nonce"])) : "";
        if (!wp_verify_nonce($nonce, "upsellio_error_logs_clear")) {
            return;
        }

        update_option(upsellio_get_error_log_option_key(), [], false);
        wp_safe_redirect(add_query_arg(["page" => "upsellio-error-logs", "cleared" => 1], admin_url("tools.php")));
        exit;
    }

    $nonce = isset($_GET["_upsellio_nonce"]) ? sanitize_text_field((string) wp_unslash($_GET["_upsellio_nonce"])) : "";
    if (!wp_verify_nonce($nonce, "upsellio_error_logs_export_csv")) {
        return;
    }

    $filters = upsellio_get_error_logs_filters_from_request();
    $logs = upsellio_get_error_logs();
    $filtered_logs = upsellio_filter_error_logs($logs, $filters);
    upsellio_output_error_logs_csv($filtered_logs);
}
add_action("admin_init", "upsellio_handle_error_logs_admin_actions");

function upsellio_render_error_logs_admin_page()
{
    if (!current_user_can("manage_options")) {
        return;
    }

    $logs = upsellio_get_error_logs();
    $filters = upsellio_get_error_logs_filters_from_request();
    $filtered_logs = upsellio_filter_error_logs($logs, $filters);
    $filter_preset = (string) ($filters["preset"] ?? "");
    $filter_type = (string) ($filters["type"] ?? "");
    $filter_code = (int) ($filters["status_code"] ?? 0);
    $filter_date_from = (string) ($filters["date_from"] ?? "");
    $filter_date_to = (string) ($filters["date_to"] ?? "");
    $clear_url = wp_nonce_url(
        add_query_arg(["page" => "upsellio-error-logs", "upsellio_error_logs_action" => "clear"], admin_url("tools.php")),
        "upsellio_error_logs_clear",
        "_upsellio_nonce"
    );
    $export_url = wp_nonce_url(
        add_query_arg([
            "page" => "upsellio-error-logs",
            "upsellio_error_logs_action" => "export_csv",
            "filter_preset" => $filter_preset,
            "filter_type" => $filter_type,
            "filter_code" => $filter_code > 0 ? $filter_code : "",
            "filter_date_from" => $filter_date_from,
            "filter_date_to" => $filter_date_to,
        ], admin_url("tools.php")),
        "upsellio_error_logs_export_csv",
        "_upsellio_nonce"
    );
    $total_logs = count($logs);
    $total_filtered_logs = count($filtered_logs);
    $preset_last_24h_url = esc_url(add_query_arg([
        "page" => "upsellio-error-logs",
        "filter_preset" => "last_24h",
    ], admin_url("tools.php")));
    $preset_only_5xx_url = esc_url(add_query_arg([
        "page" => "upsellio-error-logs",
        "filter_preset" => "only_5xx",
    ], admin_url("tools.php")));
    $preset_only_js_url = esc_url(add_query_arg([
        "page" => "upsellio-error-logs",
        "filter_preset" => "only_js",
    ], admin_url("tools.php")));
    ?>
    <div class="wrap">
      <h1>Dziennik błędów Upsellio</h1>
      <p>Zapisuje błędy HTTP oraz błędy JavaScript z przeglądarek użytkowników (z ograniczeniem duplikatów i limitem rekordów).</p>
      <p>
        <strong>Presety:</strong>
        <a class="button" href="<?php echo esc_url($preset_last_24h_url); ?>">Ostatnie 24h</a>
        <a class="button" href="<?php echo esc_url($preset_only_5xx_url); ?>">Tylko 5xx</a>
        <a class="button" href="<?php echo esc_url($preset_only_js_url); ?>">Tylko JS</a>
      </p>
      <form method="get" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;margin:16px 0;">
        <input type="hidden" name="page" value="upsellio-error-logs" />
        <input type="hidden" name="filter_preset" value="" />
        <label>
          <span style="display:block;margin-bottom:4px;">Typ</span>
          <select name="filter_type">
            <option value="">Wszystkie</option>
            <option value="http" <?php selected($filter_type, "http"); ?>>HTTP</option>
            <option value="js" <?php selected($filter_type, "js"); ?>>JS</option>
          </select>
        </label>
        <label>
          <span style="display:block;margin-bottom:4px;">Kod</span>
          <input type="number" min="0" step="1" name="filter_code" value="<?php echo esc_attr($filter_code > 0 ? (string) $filter_code : ""); ?>" placeholder="np. 404" />
        </label>
        <label>
          <span style="display:block;margin-bottom:4px;">Data od</span>
          <input type="date" name="filter_date_from" value="<?php echo esc_attr($filter_date_from); ?>" />
        </label>
        <label>
          <span style="display:block;margin-bottom:4px;">Data do</span>
          <input type="date" name="filter_date_to" value="<?php echo esc_attr($filter_date_to); ?>" />
        </label>
        <button type="submit" class="button button-primary">Filtruj</button>
        <a href="<?php echo esc_url(add_query_arg(["page" => "upsellio-error-logs"], admin_url("tools.php"))); ?>" class="button">Reset</a>
      </form>
      <p>
        <a href="<?php echo esc_url($export_url); ?>" class="button button-primary">Eksport CSV</a>
        <a href="<?php echo esc_url($clear_url); ?>" class="button">Wyczyść dziennik</a>
      </p>
      <p><strong>Wyniki:</strong> <?php echo esc_html((string) $total_filtered_logs); ?> / <?php echo esc_html((string) $total_logs); ?></p>
      <?php if (isset($_GET["cleared"])) : ?>
        <div class="notice notice-success is-dismissible"><p>Dziennik błędów został wyczyszczony.</p></div>
      <?php endif; ?>
      <table class="widefat striped">
        <thead>
          <tr>
            <th style="width:150px;">Czas</th>
            <th style="width:80px;">Typ</th>
            <th style="width:90px;">Kod</th>
            <th style="width:140px;">Incident ID</th>
            <th>Wiadomość / URL</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($filtered_logs)) : ?>
            <tr><td colspan="5">Brak wpisów.</td></tr>
          <?php else : ?>
            <?php foreach ($filtered_logs as $row) : ?>
              <?php
              $type = isset($row["type"]) ? (string) $row["type"] : "";
              $status_code = isset($row["status_code"]) ? (int) $row["status_code"] : 0;
              $incident_id = isset($row["incident_id"]) ? (string) $row["incident_id"] : "";
              $message = isset($row["message"]) ? (string) $row["message"] : "";
              $url = isset($row["url"]) ? (string) $row["url"] : "";
              $timestamp = isset($row["timestamp"]) ? (string) $row["timestamp"] : "";
              $context = isset($row["context"]) && is_array($row["context"]) ? (array) $row["context"] : [];
              ?>
              <tr>
                <td><?php echo esc_html($timestamp); ?></td>
                <td><code><?php echo esc_html($type); ?></code></td>
                <td><?php echo esc_html($status_code > 0 ? (string) $status_code : "—"); ?></td>
                <td><code><?php echo esc_html($incident_id !== "" ? $incident_id : "—"); ?></code></td>
                <td>
                  <strong><?php echo esc_html($message); ?></strong><br />
                  <?php if ($url !== "") : ?>
                    <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener"><?php echo esc_html($url); ?></a><br />
                  <?php endif; ?>
                  <?php if (!empty($context)) : ?>
                    <code style="display:block;white-space:pre-wrap;margin-top:4px;"><?php echo esc_html(wp_json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?></code>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php
}

