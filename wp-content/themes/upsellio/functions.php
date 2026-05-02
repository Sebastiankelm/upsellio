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

require_once get_template_directory() . "/inc/routing.php";

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

require_once get_template_directory() . "/inc/technical-seo.php";

require_once get_template_directory() . "/inc/admin-tools.php";
require_once get_template_directory() . "/inc/home-media.php";
require_once get_template_directory() . "/inc/template-assets.php";

function upsellio_is_strict_custom_embed_mode()
{
    // Default false: portfolio / marketing portfolio / lead magnet meta boxes expose a JS field;
    // strict true would wipe JS on every save and migration. Use add_filter( 'upsellio_strict_custom_embed_mode', '__return_true' ) to forbid storing custom JS.
    return (bool) apply_filters("upsellio_strict_custom_embed_mode", false);
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

function upsellio_ensure_offer_page_exists()
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }

    upsellio_upsert_page_with_template("oferta", "Oferta", "page-oferta.php");
    upsellio_upsert_page_with_template("marketing-meta-ads", "Meta Ads", "page-marketing-meta-ads.php");
    upsellio_upsert_page_with_template("marketing-google-ads", "Google Ads", "page-marketing-google-ads.php");
    upsellio_upsert_page_with_template("tworzenie-stron-internetowych", "Tworzenie stron internetowych", "page-tworzenie-stron-internetowych.php");
}
add_action("admin_init", "upsellio_ensure_offer_page_exists");

function upsellio_ensure_blog_page_exists()
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }

    $blog_page_id = upsellio_upsert_page_with_template("blog", "Blog", "page-blog.php");
    if ($blog_page_id > 0 && (int) get_option("page_for_posts") <= 0) {
        update_option("page_for_posts", (int) $blog_page_id);
    }
}
add_action("admin_init", "upsellio_ensure_blog_page_exists");

function upsellio_ensure_blog_content_categories()
{
    if (!is_admin() || !current_user_can("manage_categories")) {
        return;
    }

    $categories = [
        ["name" => "Meta Ads", "slug" => "meta-ads", "description" => "Kampanie Meta Ads, strategie, lejki, kreacje i remarketing dla firm."],
        ["name" => "Google Ads", "slug" => "google-ads", "description" => "Kampanie Search, Performance Max, słowa kluczowe i optymalizacja Google Ads."],
        ["name" => "Konwersja i strony WWW", "slug" => "konwersja-strony", "description" => "Landing pages, optymalizacja konwersji, copywriting, CTA i UX pod sprzedaż."],
        ["name" => "Pozyskiwanie klientów", "slug" => "pozyskiwanie-klientow", "description" => "Lead generation, lejki sprzedażowe, CPL, jakość leadów i system marketingowy."],
        ["name" => "Analityka i mierzenie", "slug" => "analityka", "description" => "GA4, śledzenie konwersji, Tag Manager, atrybucja i raportowanie."],
    ];

    foreach ($categories as $category) {
        $slug = sanitize_title((string) $category["slug"]);
        if ($slug === "" || term_exists($slug, "category")) {
            continue;
        }

        wp_insert_term((string) $category["name"], "category", [
            "slug" => $slug,
            "description" => (string) $category["description"],
        ]);
    }
}
add_action("admin_init", "upsellio_ensure_blog_content_categories");

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

function upsellio_get_page_url_by_template($template_file)
{
    $template_file = trim((string) $template_file);
    if ($template_file === "") {
        return "";
    }

    $pages = get_posts([
        "post_type" => "page",
        "post_status" => "publish",
        "posts_per_page" => 1,
        "meta_key" => "_wp_page_template",
        "meta_value" => $template_file,
        "fields" => "ids",
    ]);
    $page_id = (int) ($pages[0] ?? 0);
    if ($page_id <= 0) {
        return "";
    }

    $permalink = get_permalink($page_id);
    return is_string($permalink) ? $permalink : "";
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
    $template_url = upsellio_get_page_url_by_template("page-kontakt.php");
    if ($template_url !== "") {
        return $template_url;
    }
    $contact_path = function_exists("upsellio_get_special_navigation_path_by_title")
        ? (string) upsellio_get_special_navigation_path_by_title("Kontakt", "/kontakt/")
        : "";
    $contact_slug = trim((string) wp_parse_url($contact_path, PHP_URL_PATH), "/");
    if ($contact_slug === "") {
        return "";
    }
    $contact_page = get_page_by_path($contact_slug);
    if (!($contact_page instanceof WP_Post)) {
        return "";
    }
    $page_permalink = get_permalink((int) $contact_page->ID);
    return is_string($page_permalink) ? $page_permalink : "";
}

function upsellio_get_offer_page_url()
{
    return upsellio_get_page_url_by_template("page-oferta.php");
}

function upsellio_get_google_ads_page_url()
{
    return upsellio_get_page_url_by_template("page-marketing-google-ads.php");
}

function upsellio_get_meta_ads_page_url()
{
    return upsellio_get_page_url_by_template("page-marketing-meta-ads.php");
}

function upsellio_get_websites_page_url()
{
    return upsellio_get_page_url_by_template("page-tworzenie-stron-internetowych.php");
}

function upsellio_get_definitions_archive_url()
{
    $archive_url = get_post_type_archive_link("definicja");
    if (is_string($archive_url) && $archive_url !== "") {
        return $archive_url;
    }

    return upsellio_get_page_url_by_template("archive-definicja.php");
}

function upsellio_get_cities_archive_url()
{
    $archive_url = get_post_type_archive_link("miasto");
    if (is_string($archive_url) && $archive_url !== "") {
        return $archive_url;
    }

    return upsellio_get_page_url_by_template("archive-miasto.php");
}

function upsellio_get_dynamic_content_post_types()
{
    $post_types = [
        "definicja",
        "miasto",
        "lead_magnet",
        "portfolio",
        "marketing_portfolio",
    ];

    return array_values(array_unique(array_filter(array_map("sanitize_key", $post_types))));
}

function upsellio_get_post_type_rewrite_slug($post_type)
{
    $post_type = sanitize_key((string) $post_type);
    if ($post_type === "") {
        return "";
    }

    $object = get_post_type_object($post_type);
    if (!($object instanceof WP_Post_Type)) {
        return "";
    }

    if (!is_array($object->rewrite)) {
        return "";
    }

    $slug = isset($object->rewrite["slug"]) ? (string) $object->rewrite["slug"] : "";
    $slug = trim($slug, "/");
    return $slug !== "" ? $slug : "";
}

function upsellio_get_dynamic_content_route_map()
{
    $map = [];
    foreach (upsellio_get_dynamic_content_post_types() as $post_type) {
        $base = upsellio_get_post_type_rewrite_slug($post_type);
        if ($base === "") {
            continue;
        }
        $map[$base] = $post_type;
    }

    return $map;
}

function upsellio_force_content_permalink($post_link, $post, $leavename = false, $sample = false)
{
    if (!($post instanceof WP_Post)) {
        return $post_link;
    }
    if ((string) get_option("permalink_structure", "") === "") {
        return $post_link;
    }

    $post_type = sanitize_key((string) $post->post_type);
    if (!in_array($post_type, upsellio_get_dynamic_content_post_types(), true)) {
        return $post_link;
    }

    $base = upsellio_get_post_type_rewrite_slug($post_type);
    if ($base === "") {
        return $post_link;
    }

    $slug = $leavename ? "%postname%" : (string) $post->post_name;
    if ($slug === "") {
        return $post_link;
    }

    return home_url("/" . $base . "/" . $slug . "/");
}
add_filter("post_type_link", "upsellio_force_content_permalink", 10, 4);

function upsellio_register_content_permalink_rewrites()
{
    foreach (upsellio_get_dynamic_content_route_map() as $base => $post_type) {
        add_rewrite_rule(
            "^" . preg_quote($base, "#") . "/([^/]+)/?$",
            "index.php?post_type=" . rawurlencode($post_type) . "&name=\$matches[1]",
            "top"
        );
    }
}
add_action("init", "upsellio_register_content_permalink_rewrites", 20);

function upsellio_route_content_request_fallback($query_vars)
{
    if (!is_array($query_vars) || is_admin() || wp_doing_ajax() || wp_doing_cron()) {
        return $query_vars;
    }
    if (defined("REST_REQUEST") && REST_REQUEST) {
        return $query_vars;
    }
    if (!empty($query_vars)) {
        return $query_vars;
    }

    $request_uri = isset($_SERVER["REQUEST_URI"]) ? (string) wp_unslash($_SERVER["REQUEST_URI"]) : "/";
    $path = (string) wp_parse_url($request_uri, PHP_URL_PATH);
    $path = trim($path, "/");
    if ($path === "") {
        return $query_vars;
    }

    $segments = explode("/", $path);
    if (count($segments) < 2) {
        return $query_vars;
    }

    $base = sanitize_title((string) $segments[0]);
    $slug = sanitize_title((string) $segments[1]);
    if ($slug === "") {
        return $query_vars;
    }

    $route_map = upsellio_get_dynamic_content_route_map();
    if (isset($route_map[$base])) {
        return [
            "post_type" => (string) $route_map[$base],
            "name" => $slug,
        ];
    }

    return $query_vars;
}
add_filter("request", "upsellio_route_content_request_fallback", 0);

function upsellio_maybe_flush_content_permalink_rewrites()
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }

    $version_key = "upsellio_content_permalink_rewrite_version";
    $target_version = "2026-04-30-content-permalink-v4";
    if ((string) get_option($version_key, "") === $target_version) {
        return;
    }

    flush_rewrite_rules(false);
    update_option($version_key, $target_version, false);
}
add_action("admin_init", "upsellio_maybe_flush_content_permalink_rewrites");

function upsellio_flush_content_permalink_rewrites_after_permalink_change($old_value, $value)
{
    if ((string) $old_value === (string) $value) {
        return;
    }
    delete_option("upsellio_content_permalink_rewrite_version");
}
add_action("update_option_permalink_structure", "upsellio_flush_content_permalink_rewrites_after_permalink_change", 10, 2);

function upsellio_force_flush_content_permalink_rewrites_runtime()
{
    if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
        return;
    }
    if ((defined("REST_REQUEST") && REST_REQUEST) || (defined("WP_CLI") && WP_CLI)) {
        return;
    }

    $version_key = "upsellio_content_permalink_rewrite_version";
    $target_version = "2026-04-30-content-permalink-v4";
    if ((string) get_option($version_key, "") === $target_version) {
        return;
    }

    flush_rewrite_rules(false);
    update_option($version_key, $target_version, false);
}
add_action("init", "upsellio_force_flush_content_permalink_rewrites_runtime", 999);

function upsellio_get_social_profile_url($network)
{
    $network = strtolower(trim((string) $network));
    $defaults = [
        "linkedin" => "https://www.linkedin.com/in/sebastiankelm/",
        "facebook" => "https://www.facebook.com/profile.php?id=61563653369010",
        "instagram" => "https://www.instagram.com/upsellio.pl/",
        "x" => "https://x.com/upsellio",
        "youtube" => "https://www.youtube.com/@upsellio",
    ];
    if (!isset($defaults[$network])) {
        return "";
    }

    if (function_exists("upsellio_get_trust_seo_section")) {
        $social_profiles = upsellio_get_trust_seo_section("social_profiles");
        $configured_value = trim((string) ($social_profiles[$network] ?? ""));
        if ($configured_value !== "") {
            $configured_value = esc_url_raw($configured_value);
            if ($configured_value !== "") {
                return $configured_value;
            }
        }
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
            $city_name = (string) (get_post_meta($city_id, "_upsellio_city_name", true) ?: get_the_title($city_id));
            $links[] = [
                "label" => "Marketing i strony WWW " . $city_name,
                "city_name" => $city_name,
                "service_label" => "Marketing i strony WWW",
                "url" => (string) get_permalink($city_id),
            ];
        }
        return $links;
    }

    foreach (array_slice((array) upsellio_get_cities_dataset(), 0, $limit) as $city_item) {
        $city_name = (string) ($city_item["name"] ?? "");
        $links[] = [
            "label" => "Marketing i strony WWW " . $city_name,
            "city_name" => $city_name,
            "service_label" => "Marketing i strony WWW",
            "url" => home_url("/miasto/" . (string) ($city_item["slug"] ?? "") . "/"),
        ];
    }
    return $links;
}

function upsellio_get_generated_logo_assets()
{
    $assets = [
        "png" => "",
        "webp_320" => "",
        "webp_640" => "",
        "custom_logo_id" => 0,
    ];

    $base_dir = function_exists("upsellio_logo_tool_assets_dir")
        ? upsellio_logo_tool_assets_dir()
        : trailingslashit(get_template_directory()) . "assets/images";
    $base_url = trailingslashit(get_template_directory_uri() . "/assets/images");

    $targets = function_exists("upsellio_logo_tool_targets") ? upsellio_logo_tool_targets() : [];
    foreach ((array) $targets as $target) {
        $filename = (string) ($target["filename"] ?? "");
        if ($filename === "") {
            continue;
        }

        $path = trailingslashit($base_dir) . $filename;
        if (!file_exists($path)) {
            continue;
        }

        $url = $base_url . rawurlencode($filename);
        $version = filemtime($path);
        if ($version) {
            $url = add_query_arg("v", (string) $version, $url);
        }

        $format = (string) ($target["format"] ?? "");
        $width = (int) ($target["width"] ?? 0);
        if ($format === "png") {
            $assets["png"] = $url;
        } elseif ($format === "webp" && $width === 320) {
            $assets["webp_320"] = $url;
        } elseif ($format === "webp" && $width === 640) {
            $assets["webp_640"] = $url;
        }
    }

    return $assets;
}

function upsellio_get_brand_logo_assets()
{
    $assets = upsellio_get_generated_logo_assets();
    if ((string) ($assets["png"] ?? "") !== "") {
        return $assets;
    }

    $custom_logo_id = (int) get_theme_mod("custom_logo");
    if ($custom_logo_id <= 0) {
        return $assets;
    }

    $custom_logo_url = wp_get_attachment_image_url($custom_logo_id, "full");
    if (!is_string($custom_logo_url) || $custom_logo_url === "") {
        return $assets;
    }

    $assets["png"] = $custom_logo_url;
    $assets["custom_logo_id"] = $custom_logo_id;
    return $assets;
}

/**
 * Wyświetla logo z bazy (custom_logo / wygenerowane assety) — ta sama logika co w header.php.
 *
 * @param array $args img_class, sizes, width, height, alt
 * @return bool true jeśli wyświetlono obrazek
 */
function upsellio_echo_brand_logo_picture(array $args = [])
{
    if (!function_exists("upsellio_get_brand_logo_assets")) {
        return false;
    }
    $assets = upsellio_get_brand_logo_assets();
    $png = (string) ($assets["png"] ?? "");
    if ($png === "") {
        return false;
    }
    $webp320 = (string) ($assets["webp_320"] ?? "");
    $webp640 = (string) ($assets["webp_640"] ?? "");
    $img_class = (string) ($args["img_class"] ?? "brand-logo");
    $width = (int) ($args["width"] ?? 320);
    $height = (int) ($args["height"] ?? 213);
    $sizes = (string) ($args["sizes"] ?? "(max-width: 760px) 163px, 222px");
    $alt = (string) ($args["alt"] ?? "Upsellio — kampanie Google Ads i Meta Ads dla firm B2B");
    $loading = (string) ($args["loading"] ?? "eager");
    $fetchpriority = isset($args["fetchpriority"]) ? (string) $args["fetchpriority"] : "";

    echo "<picture>";
    if ($webp320 !== "" && $webp640 !== "") {
        echo '<source type="image/webp" srcset="' . esc_url($webp320) . " 320w, " . esc_url($webp640) . ' 640w" sizes="' . esc_attr($sizes) . '" />';
    }
    $img_extra = ' decoding="async" loading="' . esc_attr($loading) . '"';
    if ($fetchpriority !== "") {
        $img_extra .= ' fetchpriority="' . esc_attr($fetchpriority) . '"';
    }
    echo '<img src="' . esc_url($png) . '" alt="' . esc_attr($alt) . '" class="' . esc_attr($img_class) . '" width="' . $width . '" height="' . $height . '"' . $img_extra . " />";
    echo "</picture>";

    return true;
}

function upsellio_get_generated_logo_url($preferred = "png")
{
    $assets = upsellio_get_generated_logo_assets();
    $preferred = sanitize_key((string) $preferred);

    return (string) ($assets[$preferred] ?? $assets["png"] ?? "");
}

function upsellio_get_social_icon_svg($network)
{
    $network = sanitize_key((string) $network);
    $icons = [
        "linkedin" => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 8.8H3.1v11.1h3.5V8.8zM4.8 3.5a2 2 0 0 0 0 4 2 2 0 0 0 0-4zm15.9 9.4c0-3-1.6-4.4-3.9-4.4-1.8 0-2.6 1-3 1.7V8.8h-3.5v11.1h3.5v-6.2c0-1.6.3-3.2 2.2-3.2 1.9 0 1.9 1.8 1.9 3.3v6.1H21V12.9h-.3z" fill="currentColor"/></svg>',
        "facebook" => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13.7 21v-8h2.7l.4-3h-3.1V8.1c0-.9.2-1.5 1.5-1.5h1.6V3.8c-.8-.1-1.6-.1-2.5-.1-2.4 0-4 1.5-4 4.3V10H7.8v3h2.4v8h3.5z" fill="currentColor"/></svg>',
        "instagram" => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 7.2A4.8 4.8 0 1 0 16.8 12 4.8 4.8 0 0 0 12 7.2zm0 7.9A3.1 3.1 0 1 1 15.1 12 3.1 3.1 0 0 1 12 15.1zm6.1-8a1.1 1.1 0 1 0 1.1 1.1 1.1 1.1 0 0 0-1.1-1.1zm-1.4 13.8H7.3A3.9 3.9 0 0 1 3.4 17V7A3.9 3.9 0 0 1 7.3 3h9.4a3.9 3.9 0 0 1 3.9 4v10a3.9 3.9 0 0 1-3.9 3.9zM7.3 4.7A2.3 2.3 0 0 0 5.1 7v10a2.3 2.3 0 0 0 2.2 2.3h9.4a2.3 2.3 0 0 0 2.2-2.3V7a2.3 2.3 0 0 0-2.2-2.3H7.3z" fill="currentColor"/></svg>',
        "x" => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18.2 3h2.9l-6.3 7.2L22 21h-5.6l-4.4-5.8L6.9 21H4l6.8-7.7L2 3h5.7l4 5.3L18.2 3zm-1 16.4h1.6L6.9 4.5H5.2l12 14.9z" fill="currentColor"/></svg>',
        "youtube" => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 8.5a3.1 3.1 0 0 0-2.2-2.2C17.9 5.8 12 5.8 12 5.8s-5.9 0-7.8.5A3.1 3.1 0 0 0 2 8.5 32.7 32.7 0 0 0 1.5 12a32.7 32.7 0 0 0 .5 3.5 3.1 3.1 0 0 0 2.2 2.2c1.9.5 7.8.5 7.8.5s5.9 0 7.8-.5a3.1 3.1 0 0 0 2.2-2.2 32.7 32.7 0 0 0 .5-3.5 32.7 32.7 0 0 0-.5-3.5zM10.1 15.3V8.7l5.2 3.3-5.2 3.3z" fill="currentColor"/></svg>',
    ];

    return (string) ($icons[$network] ?? "");
}

function upsellio_render_unified_footer($args = [])
{
    $args = is_array($args) ? $args : [];
    $contact_email = isset($args["contact_email"]) && is_email((string) $args["contact_email"])
        ? (string) $args["contact_email"]
        : "kontakt@upsellio.pl";
    $contact_phone = function_exists("upsellio_get_contact_phone") ? (string) upsellio_get_contact_phone() : "+48 575 522 595";
    $contact_phone_href = preg_replace("/\s+/", "", $contact_phone);
    $contact_email_href = upsellio_get_mailto_href($contact_email);
    $brand_logo_assets = function_exists("upsellio_get_brand_logo_assets") ? upsellio_get_brand_logo_assets() : [];
    $brand_logo_url = (string) ($brand_logo_assets["png"] ?? "");
    $brand_logo_webp_320_url = (string) ($brand_logo_assets["webp_320"] ?? "");
    $brand_logo_webp_640_url = (string) ($brand_logo_assets["webp_640"] ?? "");
    $footer_config = function_exists("upsellio_get_footer_content_config") ? upsellio_get_footer_content_config() : [];
    $brand_config = isset($footer_config["brand"]) && is_array($footer_config["brand"]) ? $footer_config["brand"] : [];
    $section_config = isset($footer_config["sections"]) && is_array($footer_config["sections"]) ? $footer_config["sections"] : [];
    $cities_config = isset($footer_config["cities"]) && is_array($footer_config["cities"]) ? $footer_config["cities"] : [];

    $normalize_url = static function ($value) {
        $raw_url = trim((string) $value);
        if ($raw_url === "") {
            return "";
        }
        if (strpos($raw_url, "http://") === 0 || strpos($raw_url, "https://") === 0) {
            return esc_url($raw_url);
        }

        $path = (string) wp_parse_url($raw_url, PHP_URL_PATH);
        if ($path === "") {
            $path = "/" . ltrim($raw_url, "/");
        }
        $path = "/" . ltrim($path, "/");

        $dynamic_map = [
            "/" => home_url("/"),
            "/blog" => function_exists("upsellio_get_blog_index_url") ? (string) upsellio_get_blog_index_url() : "",
            "/blog/" => function_exists("upsellio_get_blog_index_url") ? (string) upsellio_get_blog_index_url() : "",
            "/oferta" => function_exists("upsellio_get_offer_page_url") ? (string) upsellio_get_offer_page_url() : "",
            "/oferta/" => function_exists("upsellio_get_offer_page_url") ? (string) upsellio_get_offer_page_url() : "",
            "/kontakt" => function_exists("upsellio_get_contact_page_url") ? (string) upsellio_get_contact_page_url() : "",
            "/kontakt/" => function_exists("upsellio_get_contact_page_url") ? (string) upsellio_get_contact_page_url() : "",
            "/portfolio" => function_exists("upsellio_get_portfolio_page_url") ? (string) upsellio_get_portfolio_page_url() : "",
            "/portfolio/" => function_exists("upsellio_get_portfolio_page_url") ? (string) upsellio_get_portfolio_page_url() : "",
            "/portfolio-marketingowe" => function_exists("upsellio_get_marketing_portfolio_page_url") ? (string) upsellio_get_marketing_portfolio_page_url() : "",
            "/portfolio-marketingowe/" => function_exists("upsellio_get_marketing_portfolio_page_url") ? (string) upsellio_get_marketing_portfolio_page_url() : "",
            "/lead-magnety" => function_exists("upsellio_get_lead_magnets_page_url") ? (string) upsellio_get_lead_magnets_page_url() : "",
            "/lead-magnety/" => function_exists("upsellio_get_lead_magnets_page_url") ? (string) upsellio_get_lead_magnets_page_url() : "",
            "/marketing-google-ads" => function_exists("upsellio_get_google_ads_page_url") ? (string) upsellio_get_google_ads_page_url() : "",
            "/marketing-google-ads/" => function_exists("upsellio_get_google_ads_page_url") ? (string) upsellio_get_google_ads_page_url() : "",
            "/marketing-meta-ads" => function_exists("upsellio_get_meta_ads_page_url") ? (string) upsellio_get_meta_ads_page_url() : "",
            "/marketing-meta-ads/" => function_exists("upsellio_get_meta_ads_page_url") ? (string) upsellio_get_meta_ads_page_url() : "",
            "/tworzenie-stron-internetowych" => function_exists("upsellio_get_websites_page_url") ? (string) upsellio_get_websites_page_url() : "",
            "/tworzenie-stron-internetowych/" => function_exists("upsellio_get_websites_page_url") ? (string) upsellio_get_websites_page_url() : "",
            "/miasta" => function_exists("upsellio_get_cities_archive_url") ? (string) upsellio_get_cities_archive_url() : "",
            "/miasta/" => function_exists("upsellio_get_cities_archive_url") ? (string) upsellio_get_cities_archive_url() : "",
            "/definicja" => function_exists("upsellio_get_definitions_archive_url") ? (string) upsellio_get_definitions_archive_url() : "",
            "/definicja/" => function_exists("upsellio_get_definitions_archive_url") ? (string) upsellio_get_definitions_archive_url() : "",
            "/definicje" => function_exists("upsellio_get_definitions_archive_url") ? (string) upsellio_get_definitions_archive_url() : "",
            "/definicje/" => function_exists("upsellio_get_definitions_archive_url") ? (string) upsellio_get_definitions_archive_url() : "",
        ];
        if (isset($dynamic_map[$path]) && (string) $dynamic_map[$path] !== "") {
            return esc_url((string) $dynamic_map[$path]);
        }

        $page = get_page_by_path(trim($path, "/"));
        if ($page instanceof WP_Post) {
            $permalink = get_permalink((int) $page->ID);
            if (is_string($permalink) && $permalink !== "") {
                return esc_url($permalink);
            }
        }

        if (strpos($path, "/miasto/") === 0 || strpos($path, "/definicje/") === 0 || strpos($path, "/realizacja/") === 0 || strpos($path, "/portfolio-marketingowe/") === 0 || strpos($path, "/lead-magnety/") === 0) {
            return esc_url(home_url($path));
        }

        return "";
    };

    $brand_description = trim((string) ($brand_config["description"] ?? ""));
    if ($brand_description === "") {
        $brand_description = "Marketing i sprzedaż dla firm, które chcą realnych klientów - nie wykresów na slajdach. Pracuję z producentami, dystrybutorami i firmami usługowymi z całej Polski.";
    }
    $brand_address = trim((string) ($brand_config["address"] ?? "wierzbowa 21A/2, Dopiewiec"));

    $normalized_sections = [];
    foreach ($section_config as $section_item) {
        if (!is_array($section_item)) {
            continue;
        }
        $section_title = trim((string) ($section_item["title"] ?? ""));
        $section_links = isset($section_item["links"]) && is_array($section_item["links"]) ? $section_item["links"] : [];
        if ($section_title === "" || empty($section_links)) {
            continue;
        }
        $normalized_links = [];
        foreach ($section_links as $section_link) {
            if (!is_array($section_link)) {
                continue;
            }
            $link_label = trim((string) ($section_link["label"] ?? ""));
            $link_url = $normalize_url((string) ($section_link["url"] ?? ""));
            if ($link_label === "" || $link_url === "") {
                continue;
            }
            $normalized_links[] = [
                "label" => $link_label,
                "url" => $link_url,
            ];
        }
        if (!empty($normalized_links)) {
            $normalized_sections[] = [
                "title" => $section_title,
                "links" => $normalized_links,
            ];
        }
    }

    $city_title = trim((string) ($cities_config["title"] ?? "Marketing w Twoim mieście"));
    $city_all_label = trim((string) ($cities_config["all_label"] ?? "Zobacz wszystkie miasta →"));
    $city_all_url = $normalize_url((string) ($cities_config["all_url"] ?? "/miasta/"));
    $city_limit = max(4, (int) ($cities_config["fallback_limit"] ?? 16));
    $city_links = isset($cities_config["links"]) && is_array($cities_config["links"]) ? $cities_config["links"] : [];

    $normalized_city_links = [];
    foreach ($city_links as $city_link) {
        if (!is_array($city_link)) {
            continue;
        }
        $city_label = trim((string) ($city_link["label"] ?? ""));
        $city_url = $normalize_url((string) ($city_link["url"] ?? ""));
        if ($city_label === "" || $city_url === "") {
            continue;
        }
        $normalized_city_links[] = ["label" => $city_label, "url" => $city_url];
    }
    if (empty($normalized_city_links)) {
        foreach (upsellio_get_footer_city_links($city_limit) as $city_item) {
            $city_label = trim((string) ($city_item["city_name"] ?? $city_item["label"] ?? ""));
            $city_url = $normalize_url((string) ($city_item["url"] ?? ""));
            if ($city_label === "" || $city_url === "") {
                continue;
            }
            $normalized_city_links[] = ["label" => $city_label, "url" => $city_url];
        }
    }

    ob_start();
    ?>
    <footer class="nf-footer">
      <style>
        .nf-footer{font-family:"DM Sans",system-ui,sans-serif;background:#0a1410;color:rgba(255,255,255,.78);position:relative;overflow:hidden}
        .nf-footer *,.nf-footer *::before,.nf-footer *::after{box-sizing:border-box}
        .nf-footer::before{content:"";position:absolute;width:700px;height:700px;border-radius:50%;background:radial-gradient(circle,rgba(20,184,166,.16),transparent 65%);right:-300px;top:-300px;pointer-events:none}
        .nf-wrap{width:min(1240px,100% - 48px);margin-inline:auto}
        .nf-logo{display:flex;align-items:center;gap:8px;font-family:"Syne",sans-serif;font-size:22px;font-weight:800;letter-spacing:-1px;color:#fff;text-decoration:none}
        .nf-logo img{height:30px;width:auto;display:block}
        .nf-logo-mark{width:26px;height:26px;border-radius:8px;background:linear-gradient(135deg,#0d9488,#0f766e);position:relative;flex:0 0 26px}
        .nf-logo-mark::after{content:"";position:absolute;inset:7px;border-radius:50%;background:#0a1410}
        .nf-footer-grid{position:relative;display:grid;grid-template-columns:1.3fr .9fr .9fr 1.2fr;gap:48px;padding:64px 0 48px}
        .nf-foot-brand p{margin:18px 0 24px;font-size:14px;line-height:1.65;color:rgba(255,255,255,.65);max-width:36ch}
        .nf-foot-contacts{display:grid;gap:8px}
        .nf-foot-contacts a,.nf-foot-contacts span{display:flex;align-items:center;gap:10px;color:rgba(255,255,255,.78);text-decoration:none;font-size:13.5px}
        .nf-foot-contacts a i,.nf-foot-contacts span i{width:30px;height:30px;border-radius:8px;background:rgba(255,255,255,.06);display:grid;place-items:center;font-style:normal;font-size:13px;color:#5eead4;flex:0 0 30px}
        .nf-foot-contacts a:hover{color:#5eead4}
        .nf-foot-h{font-size:11px;font-weight:700;letter-spacing:1.4px;text-transform:uppercase;color:#5eead4;margin:0 0 14px}
        .nf-foot-col ul{list-style:none;padding:0;margin:0 0 28px;display:grid;gap:8px}
        .nf-foot-col ul li a{color:rgba(255,255,255,.78);text-decoration:none;font-size:14px}
        .nf-foot-col ul li a:hover{color:#fff}
        .nf-cities-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:6px 18px;margin:0 0 18px;padding:0;list-style:none}
        .nf-cities-grid li a{font-size:13.5px;color:rgba(255,255,255,.65);text-decoration:none}
        .nf-cities-grid li a:hover{color:#5eead4}
        .nf-cities-all{color:#5eead4;font-weight:700;font-size:13.5px;text-decoration:none;display:inline-flex}
        .nf-foot-bar{position:relative;border-top:1px solid rgba(255,255,255,.08);padding:18px 0}
        .nf-foot-bar-row{display:flex;justify-content:space-between;align-items:center;gap:24px;flex-wrap:wrap;font-size:12.5px;color:rgba(255,255,255,.5)}
        .nf-foot-social{display:flex;gap:18px}
        .nf-foot-social a{color:rgba(255,255,255,.5);text-decoration:none}
        .nf-foot-social a:hover{color:#5eead4}
        @media (max-width:1100px){.nf-footer-grid{grid-template-columns:1fr 1fr;gap:30px}}
        @media (max-width:720px){.nf-wrap{width:min(1240px,100% - 24px)}.nf-footer-grid{grid-template-columns:1fr;padding:44px 0 36px}}
      </style>
      <div class="nf-wrap nf-footer-grid">
        <div class="nf-foot-brand">
          <a href="<?php echo esc_url(home_url("/")); ?>" class="nf-logo">
            <?php if ($brand_logo_url !== "") : ?>
              <picture>
                <?php if ($brand_logo_webp_320_url !== "" && $brand_logo_webp_640_url !== "") : ?>
                  <source type="image/webp" srcset="<?php echo esc_url($brand_logo_webp_320_url); ?> 320w, <?php echo esc_url($brand_logo_webp_640_url); ?> 640w" sizes="(max-width: 760px) 132px, 180px" />
                <?php endif; ?>
                <img src="<?php echo esc_url($brand_logo_url); ?>" alt="Upsellio" width="320" height="213" loading="lazy" decoding="async" />
              </picture>
            <?php else : ?>
              <span class="nf-logo-mark"></span>
              Upsellio
            <?php endif; ?>
          </a>
          <p><?php echo esc_html($brand_description); ?></p>
          <div class="nf-foot-contacts">
            <a href="<?php echo esc_url($contact_email_href); ?>"><i>@</i><span><?php echo esc_html($contact_email); ?></span></a>
            <a href="<?php echo esc_url("tel:" . $contact_phone_href); ?>"><i>T</i><span><?php echo esc_html($contact_phone); ?></span></a>
            <?php if ($brand_address !== "") : ?><span><i>A</i><span><?php echo esc_html($brand_address); ?></span></span><?php endif; ?>
          </div>
        </div>

        <?php
        $sections_for_column_one = array_slice($normalized_sections, 0, 2);
        $sections_for_column_two = array_slice($normalized_sections, 2, 2);
        ?>
        <div class="nf-foot-col">
          <?php foreach ($sections_for_column_one as $footer_section) : ?>
            <div class="nf-foot-h"><?php echo esc_html((string) $footer_section["title"]); ?></div>
            <ul>
              <?php foreach ((array) $footer_section["links"] as $footer_link) : ?>
                <li><a href="<?php echo esc_url((string) $footer_link["url"]); ?>"><?php echo esc_html((string) $footer_link["label"]); ?></a></li>
              <?php endforeach; ?>
            </ul>
          <?php endforeach; ?>
        </div>

        <div class="nf-foot-col">
          <?php foreach ($sections_for_column_two as $footer_section) : ?>
            <div class="nf-foot-h"><?php echo esc_html((string) $footer_section["title"]); ?></div>
            <ul>
              <?php foreach ((array) $footer_section["links"] as $footer_link) : ?>
                <li><a href="<?php echo esc_url((string) $footer_link["url"]); ?>"><?php echo esc_html((string) $footer_link["label"]); ?></a></li>
              <?php endforeach; ?>
            </ul>
          <?php endforeach; ?>
        </div>

        <div class="nf-foot-col nf-foot-cities">
          <div class="nf-foot-h"><?php echo esc_html($city_title); ?></div>
          <ul class="nf-cities-grid">
            <?php foreach ($normalized_city_links as $city_link_item) : ?>
              <li><a href="<?php echo esc_url((string) $city_link_item["url"]); ?>"><?php echo esc_html((string) $city_link_item["label"]); ?></a></li>
            <?php endforeach; ?>
          </ul>
          <?php if ($city_all_url !== "") : ?><a class="nf-cities-all" href="<?php echo esc_url($city_all_url); ?>"><?php echo esc_html($city_all_label); ?></a><?php endif; ?>
        </div>
      </div>

      <div class="nf-foot-bar">
        <div class="nf-wrap nf-foot-bar-row">
          <span>© <?php echo esc_html(gmdate("Y")); ?> Upsellio · Sebastian Kelm · NIP 7773388263</span>
          <div class="nf-foot-social">
            <a href="<?php echo esc_url(upsellio_get_social_profile_url("linkedin")); ?>">LinkedIn</a>
            <a href="<?php echo esc_url(upsellio_get_social_profile_url("facebook")); ?>">Facebook</a>
          </div>
        </div>
      </div>
    </footer>
    <?php
    return ob_get_clean();
}

function upsellio_get_contact_page_seo_payload()
{
    $contact_url = upsellio_get_contact_page_url();

    return [
        "title" => "Kontakt | Bezpłatna konsultacja marketingowa | Upsellio",
        "description" => "Umów bezpłatną konsultację marketingową. Opisz cel, stronę lub kampanie Google Ads i Meta Ads, a wrócę z konkretną rekomendacją.",
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
    $og_image = function_exists("upsellio_get_default_og_image_url") ? upsellio_get_default_og_image_url() : (function_exists("upsellio_get_generated_logo_url") ? upsellio_get_generated_logo_url("png") : "");
    if ($og_image !== "") {
        echo '<meta property="og:image" content="' . esc_url($og_image) . '">' . "\n";
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
require_once get_template_directory() . "/inc/analytics-internal-exclude.php";
require_once get_template_directory() . "/inc/seo-automation.php";
require_once get_template_directory() . "/inc/data-schema.php";
require_once get_template_directory() . "/inc/site-analytics.php";
require_once get_template_directory() . "/inc/breadcrumbs.php";
require_once get_template_directory() . "/inc/advanced-tests.php";
require_once get_template_directory() . "/inc/portfolio-seed.php";
require_once get_template_directory() . "/inc/marketing-portfolio-seed.php";
require_once get_template_directory() . "/inc/lead-magnet-seed.php";
require_once get_template_directory() . "/inc/theme-config.php";
require_once get_template_directory() . "/inc/offers.php";
require_once get_template_directory() . "/inc/inbox.php";
require_once get_template_directory() . "/inc/followups.php";
require_once get_template_directory() . "/inc/anthropic-crm-leads-inbox.php";
require_once get_template_directory() . "/inc/post-editor-seo-claude.php";
// Blog Bot (WP-Cron, drafty wpisów) — korzysta z upsellio_anthropic_crm_send_user_prompt() z pliku powyżej.
require_once get_template_directory() . "/inc/anthropic-blog-bot.php";
require_once get_template_directory() . "/inc/sales-engine.php";
require_once get_template_directory() . "/inc/automation-suite.php";
require_once get_template_directory() . "/inc/crm-app.php";
require_once get_template_directory() . "/inc/contracts.php";

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
    $measurement_id = "";
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
    if (function_exists("upsellio_should_load_public_tracking_tags") && !upsellio_should_load_public_tracking_tags()) {
        return;
    }

    $measurement_id = upsellio_get_ga4_measurement_id();
    if ($measurement_id !== "") {
        echo '<script async src="https://www.googletagmanager.com/gtag/js?id=' . esc_attr($measurement_id) . '"></script>' . "\n";
        echo "<script>\n";
        echo "window.dataLayer = window.dataLayer || [];\n";
        echo "function gtag(){dataLayer.push(arguments);}\n";
        echo "gtag('js', new Date());\n";
        echo "gtag('config', '" . esc_js($measurement_id) . "');\n";
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
    if (function_exists("upsellio_should_load_public_tracking_tags") && !upsellio_should_load_public_tracking_tags()) {
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

function upsellio_resolve_menu_item_url($menu_item)
{
    if (!is_object($menu_item)) {
        return "";
    }

    $menu_item_type = isset($menu_item->type) ? (string) $menu_item->type : "";
    $object_id = isset($menu_item->object_id) ? (int) $menu_item->object_id : 0;

    if ($menu_item_type === "post_type" && $object_id > 0) {
        $permalink = get_permalink($object_id);
        if (is_string($permalink) && $permalink !== "") {
            return $permalink;
        }
    }

    if ($menu_item_type === "taxonomy" && $object_id > 0) {
        $term_link = get_term_link($object_id);
        if (is_string($term_link) && $term_link !== "") {
            return $term_link;
        }
    }

    return isset($menu_item->url) ? (string) $menu_item->url : "";
}

function upsellio_get_primary_navigation_links()
{
    $locations = get_nav_menu_locations();
    $menu_id = isset($locations["primary"]) ? (int) $locations["primary"] : 0;
    $links = [];

    if ($menu_id > 0) {
        $menu_object = wp_get_nav_menu_object($menu_id);
        $menu_name = $menu_object ? (string) ($menu_object->name ?? "") : "";
        if ($menu_name === upsellio_primary_menu_name()) {
            return [];
        }

        $menu_items = wp_get_nav_menu_items($menu_id, ["update_post_term_cache" => false]);
        if (is_array($menu_items)) {
            foreach ($menu_items as $menu_item) {
                $url = upsellio_resolve_menu_item_url($menu_item);
                $title = isset($menu_item->title) ? wp_strip_all_tags((string) $menu_item->title) : "";
                if ($url === "" || $title === "") {
                    continue;
                }
                $links[] = [
                    "id" => (int) ($menu_item->ID ?? 0),
                    "title" => $title,
                    "url" => $url,
                    "parent" => (int) ($menu_item->menu_item_parent ?? 0),
                    "target" => (string) ($menu_item->target ?? ""),
                    "classes" => array_values(array_filter(array_map("sanitize_html_class", (array) ($menu_item->classes ?? [])))),
                ];
            }
        }
    }

    if (!empty($links)) {
        return $links;
    }

    return [];
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
        $definitions_url = upsellio_get_definitions_archive_url();
        if ($definitions_url !== "") {
        $definitions_item = wp_update_nav_menu_item($menu_id, 0, [
            "menu-item-title" => "Definicje",
            "menu-item-url" => $definitions_url,
            "menu-item-type" => "custom",
            "menu-item-status" => "publish",
            "menu-item-parent-id" => 0,
        ]);
        if (!is_wp_error($definitions_item)) {
            $created++;
        }
        }
    }

    if (post_type_exists("miasto")) {
        $cities_url = upsellio_get_cities_archive_url();
        if ($cities_url !== "") {
        $cities_item = wp_update_nav_menu_item($menu_id, 0, [
            "menu-item-title" => "Miasta",
            "menu-item-url" => $cities_url,
            "menu-item-type" => "custom",
            "menu-item-status" => "publish",
            "menu-item-parent-id" => 0,
        ]);
        if (!is_wp_error($cities_item)) {
            $created++;
        }
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
    add_submenu_page(
        "themes.php",
        "Synchronizacja nawigacji",
        "Sync nawigacji",
        "manage_options",
        "upsellio-navigation-sync",
        "upsellio_navigation_sync_screen"
    );
}

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

function upsellio_admin_content_tools_menu()
{
    add_submenu_page(
        "themes.php",
        "Dodawanie treści Upsellio",
        "Dodaj treści",
        "edit_posts",
        "upsellio-content-tools",
        "upsellio_render_admin_content_tools_screen",
        10
    );
}
add_action("admin_menu", "upsellio_admin_content_tools_menu");

function upsellio_admin_tool_card($title, $description, $primary_url, $primary_label, $secondary_url = "", $secondary_label = "")
{
    ?>
    <div style="background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:18px;box-shadow:0 1px 2px rgba(0,0,0,.04);">
      <h2 style="margin:0 0 8px;font-size:18px;"><?php echo esc_html((string) $title); ?></h2>
      <p style="margin:0 0 14px;color:#50575e;"><?php echo esc_html((string) $description); ?></p>
      <p style="display:flex;gap:8px;flex-wrap:wrap;margin:0;">
        <a class="button button-primary" href="<?php echo esc_url((string) $primary_url); ?>"><?php echo esc_html((string) $primary_label); ?></a>
        <?php if ((string) $secondary_url !== "" && (string) $secondary_label !== "") : ?>
          <a class="button" href="<?php echo esc_url((string) $secondary_url); ?>"><?php echo esc_html((string) $secondary_label); ?></a>
        <?php endif; ?>
      </p>
    </div>
    <?php
}

function upsellio_render_admin_content_tools_screen()
{
    if (!current_user_can("edit_posts")) {
        return;
    }

    $portfolio_url = function_exists("upsellio_get_portfolio_page_url") ? upsellio_get_portfolio_page_url() : home_url("/portfolio/");
    $marketing_portfolio_url = function_exists("upsellio_get_marketing_portfolio_page_url") ? upsellio_get_marketing_portfolio_page_url() : home_url("/portfolio-marketingowe/");
    $lead_magnets_url = function_exists("upsellio_get_lead_magnets_page_url") ? upsellio_get_lead_magnets_page_url() : home_url("/lead-magnety/");
    ?>
    <div class="wrap">
      <h1>Dodawanie treści Upsellio</h1>
      <p>Najważniejsze typy treści są podpięte jako osobne ekrany WordPressa. Ten panel zbiera skróty do dodawania realizacji, case studies marketingowych i lead magnetów.</p>

      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:14px;max-width:1100px;margin-top:18px;">
        <?php
        upsellio_admin_tool_card(
            "Portfolio stron i sklepów",
            "Dodawaj realizacje stron WWW, sklepów i aplikacji. Te wpisy zasilają szablon Portfolio.",
            admin_url("post-new.php?post_type=portfolio"),
            "Dodaj projekt",
            admin_url("edit.php?post_type=portfolio"),
            "Lista projektów"
        );
        upsellio_admin_tool_card(
            "Portfolio marketingowe",
            "Dodawaj case studies kampanii Google Ads, Meta Ads, landing pages i e-commerce.",
            admin_url("post-new.php?post_type=marketing_portfolio"),
            "Dodaj case study",
            admin_url("edit.php?post_type=marketing_portfolio"),
            "Lista case studies"
        );
        upsellio_admin_tool_card(
            "Lead magnety",
            "Dodawaj checklisty, audyty, raporty i materiały do pobrania widoczne w bibliotece materiałów.",
            admin_url("post-new.php?post_type=lead_magnet"),
            "Dodaj materiał",
            admin_url("edit.php?post_type=lead_magnet"),
            "Lista materiałów"
        );
        upsellio_admin_tool_card(
            "Automatyczne wgranie lead magnetów",
            "Wgraj gotowe materiały do bazy danych, żeby od razu zasiliły podstronę materiałów.",
            function_exists("upsellio_get_lead_magnet_seed_url") ? upsellio_get_lead_magnet_seed_url(false) : admin_url("edit.php?post_type=lead_magnet"),
            "Wgraj brakujące materiały",
            function_exists("upsellio_get_lead_magnet_seed_url") ? upsellio_get_lead_magnet_seed_url(true) : "",
            "Odśwież wszystkie"
        );
        upsellio_admin_tool_card(
            "Generator logo",
            "Wgraj JPG, PNG, WebP lub GIF, a motyw zapisze logo w rozmiarach i formatach używanych przez nagłówek oraz stopkę.",
            admin_url("themes.php?page=upsellio-logo-tool"),
            "Otwórz generator"
        );
        ?>
      </div>

      <h2 style="margin-top:28px;">Szablony stron</h2>
      <p>Te strony są pilnowane automatycznie przez motyw i powinny mieć przypisane odpowiednie szablony:</p>
      <table class="widefat striped" style="max-width:1100px;">
        <thead><tr><th>Strona</th><th>Szablon</th><th>URL</th></tr></thead>
        <tbody>
          <tr><td>Blog</td><td><code>page-blog.php</code></td><td><a href="<?php echo esc_url(upsellio_get_blog_index_url()); ?>" target="_blank" rel="noopener noreferrer">Otwórz</a></td></tr>
          <tr><td>Portfolio stron</td><td><code>page-portfolio.php</code></td><td><a href="<?php echo esc_url($portfolio_url); ?>" target="_blank" rel="noopener noreferrer">Otwórz</a></td></tr>
          <tr><td>Portfolio marketingowe</td><td><code>page-portfolio-marketingowe.php</code></td><td><a href="<?php echo esc_url($marketing_portfolio_url); ?>" target="_blank" rel="noopener noreferrer">Otwórz</a></td></tr>
          <tr><td>Lead magnety</td><td><code>page-lead-magnety.php</code></td><td><a href="<?php echo esc_url($lead_magnets_url); ?>" target="_blank" rel="noopener noreferrer">Otwórz</a></td></tr>
          <tr><td>Kontakt</td><td><code>page-kontakt.php</code></td><td><a href="<?php echo esc_url(upsellio_get_contact_page_url()); ?>" target="_blank" rel="noopener noreferrer">Otwórz</a></td></tr>
          <tr><td>Oferta</td><td><code>page-oferta.php</code></td><td><a href="<?php echo esc_url(home_url("/oferta/")); ?>" target="_blank" rel="noopener noreferrer">Otwórz</a></td></tr>
          <tr><td>Meta Ads</td><td><code>page-marketing-meta-ads.php</code></td><td><a href="<?php echo esc_url(home_url("/marketing-meta-ads/")); ?>" target="_blank" rel="noopener noreferrer">Otwórz</a></td></tr>
          <tr><td>Google Ads</td><td><code>page-marketing-google-ads.php</code></td><td><a href="<?php echo esc_url(home_url("/marketing-google-ads/")); ?>" target="_blank" rel="noopener noreferrer">Otwórz</a></td></tr>
          <tr><td>Tworzenie stron internetowych</td><td><code>page-tworzenie-stron-internetowych.php</code></td><td><a href="<?php echo esc_url(home_url("/tworzenie-stron-internetowych/")); ?>" target="_blank" rel="noopener noreferrer">Otwórz</a></td></tr>
        </tbody>
      </table>

      <h2 style="margin-top:28px;">Menu górne</h2>
      <p>Menu w nagłówku korzysta wyłącznie z lokalizacji <strong>Primary Menu</strong>. Skonfigurujesz je w WordPressie: <a href="<?php echo esc_url(admin_url("nav-menus.php")); ?>">Wygląd -> Menu</a>.</p>
    </div>
    <?php
}

function upsellio_assets()
{
    $style_path = get_template_directory() . "/assets/css/upsellio.css";
    $style_uri = get_template_directory_uri() . "/assets/css/upsellio.css";
    $style_version = file_exists($style_path) ? (string) filemtime($style_path) : "1.0.0";
    $landing_style_path = get_template_directory() . "/assets/css/upsellio-landing.css";
    $landing_style_uri = get_template_directory_uri() . "/assets/css/upsellio-landing.css";
    $landing_style_version = file_exists($landing_style_path) ? (string) filemtime($landing_style_path) : "1.0.0";
    $script_path = get_template_directory() . "/assets/js/upsellio.js";
    $script_uri = get_template_directory_uri() . "/assets/js/upsellio.js";
    $script_version = file_exists($script_path) ? (string) filemtime($script_path) : "1.0.0";
    $home_script_path = get_template_directory() . "/assets/js/upsellio-home.js";
    $home_script_uri = get_template_directory_uri() . "/assets/js/upsellio-home.js";
    $home_script_version = file_exists($home_script_path) ? (string) filemtime($home_script_path) : "1.0.0";

    $is_home_template_request = is_front_page() || (function_exists("upsellio_is_homepage_request") && upsellio_is_homepage_request());
    if ($is_home_template_request) {
        wp_enqueue_style("upsellio-main", $style_uri, [], $style_version);
        wp_enqueue_script("upsellio-home", $home_script_uri, [], $home_script_version, true);
        add_action(
            "wp_footer",
            function () use ($script_uri, $script_version) {
                ?>
                <script>
                  window.upsellioData = window.upsellioData || <?php echo wp_json_encode([
                      "ajaxUrl" => admin_url("admin-ajax.php"),
                      "blogNonce" => wp_create_nonce("upsellio_blog_filter"),
                      "blogIndexUrl" => upsellio_get_blog_index_url(),
                      "contactNonce" => wp_create_nonce("upsellio_contact_click"),
                      "skipAnalytics" => function_exists("upsellio_is_internal_tracking_user") && upsellio_is_internal_tracking_user(),
                  ], JSON_UNESCAPED_SLASHES); ?>;
                  (function () {
                    function loadUpsellioMain() {
                      if (window.__upsellioMainLoaded) return;
                      window.__upsellioMainLoaded = true;
                      var script = document.createElement("script");
                      script.src = <?php echo wp_json_encode($script_uri . "?ver=" . rawurlencode($script_version)); ?>;
                      script.async = true;
                      document.body.appendChild(script);
                    }
                    window.addEventListener("load", function () {
                      if ("requestIdleCallback" in window) {
                        window.requestIdleCallback(loadUpsellioMain, { timeout: 1200 });
                        return;
                      }
                      window.setTimeout(loadUpsellioMain, 1);
                    }, { once: true });
                  })();
                </script>
                <?php
            },
            20
        );
        return;
    }

    wp_enqueue_style("upsellio-main", $style_uri, [], $style_version);
    wp_enqueue_style("upsellio-landing", $landing_style_uri, ["upsellio-main"], $landing_style_version);
    wp_enqueue_script("upsellio-main", $script_uri, [], $script_version, true);
    wp_localize_script(
        "upsellio-main",
        "upsellioData",
        [
            "ajaxUrl" => admin_url("admin-ajax.php"),
            "blogNonce" => wp_create_nonce("upsellio_blog_filter"),
            "blogIndexUrl" => upsellio_get_blog_index_url(),
            "contactNonce" => wp_create_nonce("upsellio_contact_click"),
            "skipAnalytics" => function_exists("upsellio_is_internal_tracking_user") && upsellio_is_internal_tracking_user(),
        ]
    );
}
add_action("wp_enqueue_scripts", "upsellio_assets");

function upsellio_logo_tool_menu()
{
    add_submenu_page(
        "themes.php",
        "Generator logo Upsellio",
        "Generator logo",
        "manage_options",
        "upsellio-logo-tool",
        "upsellio_render_logo_tool_screen",
        80
    );
}
add_action("admin_menu", "upsellio_logo_tool_menu");

function upsellio_logo_tool_assets_dir()
{
    return trailingslashit(get_template_directory()) . "assets/images";
}

function upsellio_logo_tool_targets()
{
    return [
        "fallback" => [
            "filename" => "upsellio-logo.png",
            "width" => 640,
            "height" => 426,
            "format" => "png",
            "label" => "PNG fallback",
        ],
        "webp_320" => [
            "filename" => "upsellio-logo-320.webp",
            "width" => 320,
            "height" => 213,
            "format" => "webp",
            "label" => "WebP mobile",
        ],
        "webp_640" => [
            "filename" => "upsellio-logo-640.webp",
            "width" => 640,
            "height" => 426,
            "format" => "webp",
            "label" => "WebP desktop/retina",
        ],
    ];
}

function upsellio_logo_tool_format_bytes($bytes)
{
    $bytes = max(0, (int) $bytes);
    if ($bytes >= 1048576) {
        return number_format_i18n($bytes / 1048576, 2) . " MB";
    }
    if ($bytes >= 1024) {
        return number_format_i18n($bytes / 1024, 1) . " KB";
    }
    return $bytes . " B";
}

function upsellio_logo_tool_target_url($filename)
{
    $filename = (string) $filename;
    if ($filename === "") {
        return "";
    }

    $path = trailingslashit(upsellio_logo_tool_assets_dir()) . $filename;
    if (!file_exists($path)) {
        return "";
    }

    $url = trailingslashit(get_template_directory_uri() . "/assets/images") . rawurlencode($filename);
    $version = filemtime($path);
    if ($version) {
        $url = add_query_arg("v", (string) $version, $url);
    }

    return $url;
}

function upsellio_render_logo_tool_screen()
{
    if (!current_user_can("manage_options")) {
        return;
    }

    $assets_dir = upsellio_logo_tool_assets_dir();
    $targets = upsellio_logo_tool_targets();
    $status = isset($_GET["upsellio_logo_status"]) ? sanitize_key(wp_unslash($_GET["upsellio_logo_status"])) : "";
    ?>
    <div class="wrap">
      <h1>Generator logo Upsellio</h1>
      <p>Wgraj plik źródłowy logo w formacie JPG, PNG, WebP albo GIF. Narzędzie utworzy zoptymalizowane pliki używane przez motyw: <code>upsellio-logo.png</code>, <code>upsellio-logo-320.webp</code> i <code>upsellio-logo-640.webp</code>.</p>
      <style>
        .ups-logo-tool-grid { display:grid; grid-template-columns:minmax(0,520px) minmax(280px,1fr); gap:20px; align-items:start; max-width:1200px; margin-top:18px; }
        .ups-logo-tool-card { background:#fff; border:1px solid #dcdcde; border-radius:12px; padding:18px; }
        .ups-logo-preview-box { min-height:170px; display:grid; place-items:center; border:1px dashed #cbd5e1; border-radius:12px; background:linear-gradient(135deg,#f8fafc,#fff); padding:18px; text-align:center; color:#64748b; }
        .ups-logo-preview-box img { display:block; max-width:100%; max-height:180px; object-fit:contain; }
        .ups-logo-preview-meta { margin:10px 0 0; color:#646970; font-size:12px; }
        .ups-logo-generated-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:14px; margin-bottom:18px; }
        .ups-logo-generated-item { border:1px solid #e2e8f0; border-radius:12px; padding:12px; background:#f8fafc; }
        .ups-logo-generated-frame { min-height:120px; display:grid; place-items:center; border-radius:10px; background:repeating-conic-gradient(#fff 0% 25%, #f1f5f9 0% 50%) 50% / 18px 18px; padding:10px; }
        .ups-logo-generated-frame img { display:block; max-width:100%; max-height:130px; object-fit:contain; }
        .ups-logo-generated-name { margin:10px 0 2px; font-weight:600; }
        .ups-logo-empty { color:#64748b; font-size:12px; }
        @media (max-width: 960px) { .ups-logo-tool-grid { grid-template-columns:1fr; } }
      </style>

      <?php if ($status === "success") : ?>
        <div class="notice notice-success"><p>Logo zostało przetworzone i zapisane w odpowiednich rozmiarach.</p></div>
      <?php elseif ($status !== "") : ?>
        <div class="notice notice-error"><p><?php echo esc_html(upsellio_logo_tool_status_message($status)); ?></p></div>
      <?php endif; ?>

      <div class="ups-logo-tool-grid">
        <div class="ups-logo-tool-card">
          <form method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upsellio_generate_logo_assets" />
            <?php wp_nonce_field("upsellio_generate_logo_assets", "upsellio_logo_nonce"); ?>
            <p>
              <label for="upsellio_logo_file"><strong>Plik logo</strong></label><br />
              <input type="file" id="upsellio_logo_file" name="upsellio_logo_file" accept="image/jpeg,image/png,image/webp,image/gif" required />
            </p>
            <div class="ups-logo-preview-box" data-logo-upload-preview>
              <span>Wybierz plik, aby zobaczyć podgląd przed wygenerowaniem.</span>
            </div>
            <p class="ups-logo-preview-meta" data-logo-upload-meta></p>
            <p style="color:#646970;">Najlepiej wgraj możliwie duży plik z transparentnym tłem. Logo zostanie dopasowane do kadru 640×426 oraz 320×213 bez rozciągania.</p>
            <p><button type="submit" class="button button-primary">Wygeneruj logo</button></p>
          </form>
        </div>

        <div class="ups-logo-tool-card">
          <h2 style="margin-top:0;">Aktualne pliki</h2>
          <div class="ups-logo-generated-grid">
            <?php foreach ($targets as $target) : ?>
              <?php
              $filename = (string) $target["filename"];
              $path = trailingslashit($assets_dir) . $filename;
              $exists = file_exists($path);
              $url = $exists ? upsellio_logo_tool_target_url($filename) : "";
              ?>
              <div class="ups-logo-generated-item">
                <div class="ups-logo-generated-frame">
                  <?php if ($url !== "") : ?>
                    <img src="<?php echo esc_url($url); ?>" alt="<?php echo esc_attr((string) $target["label"]); ?>" loading="lazy" />
                  <?php else : ?>
                    <span class="ups-logo-empty">Brak wygenerowanego pliku</span>
                  <?php endif; ?>
                </div>
                <div class="ups-logo-generated-name"><?php echo esc_html((string) $target["label"]); ?></div>
                <code><?php echo esc_html($filename); ?></code>
              </div>
            <?php endforeach; ?>
          </div>
          <table class="widefat striped">
            <thead><tr><th>Plik</th><th>Rozmiar</th><th>Waga</th></tr></thead>
            <tbody>
              <?php foreach ($targets as $target) : ?>
                <?php
                $path = trailingslashit($assets_dir) . (string) $target["filename"];
                $exists = file_exists($path);
                ?>
                <tr>
                  <td><code><?php echo esc_html((string) $target["filename"]); ?></code><br /><span style="color:#646970;"><?php echo esc_html((string) $target["label"]); ?></span></td>
                  <td><?php echo esc_html((int) $target["width"] . "×" . (int) $target["height"]); ?></td>
                  <td><?php echo $exists ? esc_html(upsellio_logo_tool_format_bytes((int) filesize($path))) : "brak"; ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <script>
        (function () {
          var input = document.getElementById("upsellio_logo_file");
          var preview = document.querySelector("[data-logo-upload-preview]");
          var meta = document.querySelector("[data-logo-upload-meta]");
          if (!input || !preview) return;

          input.addEventListener("change", function () {
            var file = input.files && input.files[0] ? input.files[0] : null;
            if (!file) {
              preview.innerHTML = "<span>Wybierz plik, aby zobaczyć podgląd przed wygenerowaniem.</span>";
              if (meta) meta.textContent = "";
              return;
            }

            if (!file.type || file.type.indexOf("image/") !== 0) {
              preview.innerHTML = "<span>Wybrany plik nie jest obrazem.</span>";
              if (meta) meta.textContent = file.name;
              return;
            }

            var reader = new FileReader();
            reader.onload = function (event) {
              preview.innerHTML = "";
              var img = document.createElement("img");
              img.src = event.target.result;
              img.alt = "Podgląd wgrywanego logo";
              preview.appendChild(img);
            };
            reader.readAsDataURL(file);

            if (meta) {
              var sizeKb = Math.round(file.size / 1024);
              meta.textContent = file.name + " · " + sizeKb + " KB · " + (file.type || "nieznany typ");
            }
          });
        })();
      </script>
    </div>
    <?php
}

function upsellio_logo_tool_status_message($status)
{
    $messages = [
        "bad_nonce" => "Sesja wygasła. Odśwież stronę i spróbuj ponownie.",
        "no_permission" => "Brak uprawnień do generowania logo.",
        "upload_error" => "Nie udało się odebrać przesłanego pliku.",
        "invalid_upload" => "Przesłany plik jest nieprawidłowy.",
        "invalid_type" => "Dozwolone są tylko obrazy JPG, PNG, WebP i GIF.",
        "gd_missing" => "Na serwerze brakuje biblioteki GD wymaganej do przetwarzania obrazów.",
        "webp_missing" => "Serwer nie obsługuje zapisu WebP.",
        "not_writable" => "Folder assets/images nie jest zapisywalny.",
        "save_error" => "Nie udało się zapisać jednego z plików logo.",
        "db_save_error" => "Logo wygenerowano, ale nie udało się zapisać go trwale w bibliotece mediów.",
    ];
    return (string) ($messages[$status] ?? "Nie udało się wygenerować logo.");
}

function upsellio_logo_tool_redirect($status)
{
    wp_safe_redirect(add_query_arg("upsellio_logo_status", sanitize_key((string) $status), admin_url("themes.php?page=upsellio-logo-tool")));
    exit;
}

function upsellio_handle_generate_logo_assets()
{
    if (!current_user_can("manage_options")) {
        upsellio_logo_tool_redirect("no_permission");
    }
    if (!isset($_POST["upsellio_logo_nonce"]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST["upsellio_logo_nonce"])), "upsellio_generate_logo_assets")) {
        upsellio_logo_tool_redirect("bad_nonce");
    }
    if (!extension_loaded("gd") || !function_exists("imagecreatefromstring")) {
        upsellio_logo_tool_redirect("gd_missing");
    }
    if (!function_exists("imagewebp")) {
        upsellio_logo_tool_redirect("webp_missing");
    }

    $upload = $_FILES["upsellio_logo_file"] ?? null;
    if (!is_array($upload) || (int) ($upload["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        upsellio_logo_tool_redirect("upload_error");
    }

    $tmp_name = isset($upload["tmp_name"]) ? (string) $upload["tmp_name"] : "";
    if ($tmp_name === "" || !is_uploaded_file($tmp_name)) {
        upsellio_logo_tool_redirect("invalid_upload");
    }

    $image_info = @getimagesize($tmp_name);
    $mime = is_array($image_info) ? (string) ($image_info["mime"] ?? "") : "";
    if (!in_array($mime, ["image/jpeg", "image/png", "image/webp", "image/gif"], true)) {
        upsellio_logo_tool_redirect("invalid_type");
    }

    $assets_dir = upsellio_logo_tool_assets_dir();
    if (!is_dir($assets_dir) || !is_writable($assets_dir)) {
        upsellio_logo_tool_redirect("not_writable");
    }

    $binary = file_get_contents($tmp_name);
    $source = is_string($binary) && $binary !== "" ? @imagecreatefromstring($binary) : false;
    if (!$source) {
        upsellio_logo_tool_redirect("invalid_upload");
    }

    foreach (upsellio_logo_tool_targets() as $target) {
        $result = upsellio_logo_tool_save_variant(
            $source,
            trailingslashit($assets_dir) . (string) $target["filename"],
            (int) $target["width"],
            (int) $target["height"],
            (string) $target["format"]
        );
        if (!$result) {
            imagedestroy($source);
            upsellio_logo_tool_redirect("save_error");
        }
    }

    imagedestroy($source);

    if (!function_exists("media_handle_upload")) {
        require_once ABSPATH . "wp-admin/includes/image.php";
        require_once ABSPATH . "wp-admin/includes/file.php";
        require_once ABSPATH . "wp-admin/includes/media.php";
    }

    $attachment_id = media_handle_upload("upsellio_logo_file", 0, [], [
        "test_form" => false,
        "mimes" => [
            "jpg|jpeg|jpe" => "image/jpeg",
            "png" => "image/png",
            "webp" => "image/webp",
            "gif" => "image/gif",
        ],
    ]);

    if (is_wp_error($attachment_id) || (int) $attachment_id <= 0) {
        upsellio_logo_tool_redirect("db_save_error");
    }

    set_theme_mod("custom_logo", (int) $attachment_id);
    update_option("upsellio_generated_logo_attachment_id", (int) $attachment_id, false);
    update_option("upsellio_generated_logo_saved_at", time(), false);

    upsellio_logo_tool_redirect("success");
}
add_action("admin_post_upsellio_generate_logo_assets", "upsellio_handle_generate_logo_assets");

function upsellio_logo_tool_save_variant($source, $path, $width, $height, $format)
{
    $source_width = imagesx($source);
    $source_height = imagesy($source);
    if ($source_width <= 0 || $source_height <= 0 || $width <= 0 || $height <= 0) {
        return false;
    }

    $scale = min($width / $source_width, $height / $source_height);
    $next_width = max(1, (int) round($source_width * $scale));
    $next_height = max(1, (int) round($source_height * $scale));
    $dst_x = (int) floor(($width - $next_width) / 2);
    $dst_y = (int) floor(($height - $next_height) / 2);

    $canvas = imagecreatetruecolor($width, $height);
    imagealphablending($canvas, false);
    imagesavealpha($canvas, true);
    $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
    imagefilledrectangle($canvas, 0, 0, $width, $height, $transparent);
    imagecopyresampled($canvas, $source, $dst_x, $dst_y, 0, 0, $next_width, $next_height, $source_width, $source_height);

    if ($format === "webp") {
        $saved = imagewebp($canvas, $path, 72);
    } else {
        $saved = imagepng($canvas, $path, 8);
    }

    imagedestroy($canvas);
    return (bool) $saved;
}

function upsellio_city_seed_menu()
{
    add_submenu_page(
        "edit.php?post_type=miasto",
        "Generator miast SEO",
        "Generator miast",
        "manage_options",
        "upsellio-seo-generator",
        "upsellio_city_seed_screen",
        44
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
    $blog_index_url = $blog_page_id > 0 ? get_permalink($blog_page_id) : "";

    return is_string($blog_index_url) ? $blog_index_url : "";
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
    $fallback = "Marketing B2B, Google Ads i Meta Ads | Upsellio";
    if ($configured === "Marketing B2B i strony WWW, które sprzedają | Upsellio") {
        $configured = $fallback;
    }
    $resolved = $configured !== "" ? $configured : $fallback;
    $min_length = 35;
    $max_length = 60;
    $resolved_length = upsellio_strlen($resolved);
    if ($resolved_length < $min_length || $resolved_length > $max_length) {
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
    $offer_page_id = upsellio_upsert_page_with_template("oferta", "Oferta", "page-oferta.php");
    $meta_ads_page_id = upsellio_upsert_page_with_template("marketing-meta-ads", "Meta Ads", "page-marketing-meta-ads.php");
    $google_ads_page_id = upsellio_upsert_page_with_template("marketing-google-ads", "Google Ads", "page-marketing-google-ads.php");
    $websites_page_id = upsellio_upsert_page_with_template("tworzenie-stron-internetowych", "Tworzenie stron internetowych", "page-tworzenie-stron-internetowych.php");
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

    $critical_pages_exist = $definitions_page_id > 0 && $cities_page_id > 0 && $offer_page_id > 0 && $meta_ads_page_id > 0
        && $google_ads_page_id > 0 && $websites_page_id > 0 && $portfolio_page_id > 0
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
            <h2 class="ups-blog-list-title">Najnowsze artykuły — konkretne teksty o Meta Ads, Google Ads i stronach, które sprzedają.</h2>
            <p class="ups-blog-panel-text" style="max-width: 760px;">Artykuły na tym blogu są pisane pod jeden cel: żebyś po ich przeczytaniu wiedział więcej niż przed. Znajdziesz tu analizy kampanii, checklisty, studia przypadków i odpowiedzi na pytania, które najczęściej pojawiają się w rozmowach z firmami przed współpracą.</p>
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
                  <a class="ups-blog-card-link" href="<?php echo esc_url(get_permalink($post_item)); ?>">Czytaj artykuł →</a>
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

require_once get_template_directory() . "/inc/forms.php";

require_once get_template_directory() . "/inc/post-types.php";

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
    add_submenu_page(
        "tools.php",
        "Dziennik błędów Upsellio",
        "Dziennik błędów",
        "manage_options",
        "upsellio-error-logs",
        "upsellio_render_error_logs_admin_page",
        98
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

