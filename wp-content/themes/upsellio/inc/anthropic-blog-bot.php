<?php

if (!defined("ABSPATH")) {
    exit;
}

/**
 * @return list<string>
 */
function upsellio_blog_bot_keywords_queue_lines(): array
{
    $raw = (string) get_option("ups_blog_bot_keywords_queue", "");

    return array_values(array_filter(array_map("trim", preg_split("/\r\n|\n|\r/", $raw)), static function ($line) {
        return $line !== "";
    }));
}

function upsellio_blog_bot_peek_keyword(): string
{
    $lines = upsellio_blog_bot_keywords_queue_lines();

    return $lines[0] ?? "";
}

/**
 * Ostatni błąd generowania draftu (dla diagnostyki / Testy AI).
 *
 * @param array{time: string, code: string, detail?: string}|null $payload
 */
function upsellio_blog_bot_set_last_error(?array $payload): void
{
    if ($payload === null || !isset($payload["code"])) {
        delete_option("ups_blog_bot_last_error");

        return;
    }
    update_option(
        "ups_blog_bot_last_error",
        [
            "time" => (string) ($payload["time"] ?? current_time("mysql")),
            "code" => sanitize_key((string) $payload["code"]),
            "detail" => isset($payload["detail"])
                ? (function_exists("mb_substr") ? mb_substr((string) $payload["detail"], 0, 800, "UTF-8") : substr((string) $payload["detail"], 0, 800))
                : "",
        ],
        false
    );
}

function upsellio_blog_bot_shift_queue_and_archive(string $keyword): void
{
    $keyword = trim($keyword);
    if ($keyword === "") {
        return;
    }
    $lines = upsellio_blog_bot_keywords_queue_lines();
    if ($lines === [] || $lines[0] !== $keyword) {
        return;
    }
    array_shift($lines);
    update_option("ups_blog_bot_keywords_queue", implode("\n", $lines), false);

    $used = (string) get_option("ups_blog_bot_keywords_used", "");
    $used_lines = array_values(array_filter(array_map("trim", preg_split("/\r\n|\n|\r/", $used)), static function ($line) {
        return $line !== "";
    }));
    array_unshift($used_lines, current_time("Y-m-d") . ": " . $keyword);
    $used_lines = array_slice($used_lines, 0, 500);
    update_option("ups_blog_bot_keywords_used", implode("\n", $used_lines), false);
}

/**
 * Limit tokenów wyjścia dla Blog Bota (z ~10% zapasu względem żądania, ograniczony cap API).
 */
function upsellio_blog_bot_resolve_max_output_tokens(int $requested = 8192): int
{
    $requested = max(512, (int) $requested);
    $cap = (int) apply_filters("upsellio_anthropic_max_output_cap", 8192);
    $cap = max(512, min(16384, $cap));
    $with_tolerance = (int) ceil($requested * 1.10);

    return max(64, min($cap, max($requested, $with_tolerance)));
}

function upsellio_blog_bot_get_posts_context(int $limit = 12): string
{
    $posts = get_posts([
        "post_type" => "post",
        "post_status" => "publish",
        "posts_per_page" => $limit,
        "orderby" => "date",
        "order" => "DESC",
    ]);
    if ($posts === []) {
        return "Brak opublikowanych wpisów.";
    }
    $lines = [];
    foreach ($posts as $post) {
        if (!($post instanceof WP_Post)) {
            continue;
        }
        $pid = (int) $post->ID;
        $excerpt = has_excerpt($pid)
            ? (string) get_the_excerpt($post)
            : wp_trim_words((string) $post->post_content, 20, "...");
        $lines[] = "- " . get_the_title($pid) . ": " . $excerpt;
    }

    return implode("\n", $lines);
}

function upsellio_blog_bot_get_page_stub_by_slug(string $slug): ?WP_Post
{
    $slug = trim($slug, "/");
    if ($slug === "") {
        return null;
    }
    $q = new WP_Query([
        "post_type" => "page",
        "name" => $slug,
        "post_status" => "publish",
        "posts_per_page" => 1,
        "no_found_rows" => true,
    ]);
    if ($q->have_posts()) {
        $p = $q->posts[0];

        return $p instanceof WP_Post ? $p : null;
    }

    return null;
}

function upsellio_blog_bot_get_services_context(): string
{
    $slugs = [
        "page-marketing-google-ads",
        "page-marketing-meta-ads",
        "page-tworzenie-stron-internetowych",
        "page-oferta",
    ];
    $parts = [];
    foreach ($slugs as $slug) {
        $page = upsellio_blog_bot_get_page_stub_by_slug($slug);
        if (!($page instanceof WP_Post)) {
            continue;
        }

        $text = wp_strip_all_tags((string) $page->post_content);
        $limit = (int) apply_filters("upsellio_blog_bot_services_ctx_chars", 160);
        $limit = max(80, min(400, $limit));
        if (function_exists("mb_substr")) {
            $text = mb_substr($text, 0, $limit, "UTF-8");
        } else {
            $text = substr($text, 0, $limit);
        }

        $parts[] = "- " . get_the_title((int) $page->ID) . ": " . $text;
    }

    return implode("\n", $parts);
}

/**
 * Reguły JSON/HTML dla pola content — dopinane do promptu (domyślny szablon + szablony niestandardowe bez tych linii).
 */
function upsellio_blog_bot_prompt_json_html_rules(): string
{
    return "- Cudzysłowy w atrybutach HTML w polu \"content\" zastąp encją &quot; "
        . "(np. <a href=&quot;url&quot;>). Pole \"content\" jest wewnątrz JSON — "
        . "zwykłe cudzysłowy w HTML psują parsowanie.\n"
        . "- Nie używaj surowego HTML z cudzysłowami w atrybutach wewnątrz JSON — preferuj linki [anchor](url); "
        . "konwerter po stronie serwera zamieni je na HTML.\n";
}

function upsellio_blog_bot_company_prefix(): string
{
    $company = "";
    if (function_exists("upsellio_anthropic_crm_get_specialized_company_context")) {
        $company = upsellio_anthropic_crm_get_specialized_company_context("blog");
    }
    if ($company === "") {
        $company = trim((string) get_option("ups_ai_company_context", ""));
    }
    if ($company === "") {
        $company = trim((string) get_option("ups_anthropic_company_context", ""));
    }
    if ($company === "") {
        return "";
    }

    return "Kontekst firmy:\n" . $company . "\n\n";
}

/**
 * Ścieżka URL bez hosta i bez końcowego slasha — spójna z kluczami z GSC.
 */
function upsellio_blog_bot_normalize_local_path(string $url): string
{
    $path = (string) (wp_parse_url($url, PHP_URL_PATH) ?? "");
    $path = "/" . trim(str_replace("\\", "/", $path), "/");
    if ($path === "/") {
        return "";
    }

    return untrailingslashit($path);
}

/**
 * Suma kliknięć z importu GSC (upsellio_keyword_metrics_rows) per ścieżka lokalna.
 *
 * @return array<string, int>
 */
function upsellio_blog_bot_gsc_clicks_by_normalized_path(): array
{
    static $memo = null;
    if ($memo !== null) {
        return $memo;
    }
    $memo = [];
    $rows = get_option("upsellio_keyword_metrics_rows", []);
    if (!is_array($rows)) {
        return $memo;
    }
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $u = trim((string) ($row["url"] ?? ""));
        if ($u === "") {
            continue;
        }
        if (strpos($u, "http") !== 0) {
            $u = home_url("/" . ltrim($u, "/"));
        }
        $norm = upsellio_blog_bot_normalize_local_path($u);
        if ($norm === "") {
            continue;
        }
        $memo[$norm] = ($memo[$norm] ?? 0) + (int) ($row["clicks"] ?? 0);
    }

    return $memo;
}

/**
 * Katalog stron i wpisów (URL + tytuł) do promptu — sort wg dopasowania do frazy kolejki (mniej tokenów niż pełna baza).
 *
 * @return array<int, array{id:int, title:string, url:string}>
 */
function upsellio_blog_bot_catalog_for_keyword(string $keyword, int $limit = 48): array
{
    $limit = max(12, min(80, $limit));
    $keyword = trim($keyword);
    $kw_low = function_exists("mb_strtolower") ? mb_strtolower($keyword, "UTF-8") : strtolower($keyword);
    $needles = array_values(
        array_filter(
            preg_split("/\s+/u", $kw_low) ?: [],
            static function ($w) {
                $w = (string) $w;

                return $w !== "" && strlen($w) > 2;
            }
        )
    );

    $excluded_slugs = apply_filters(
        "upsellio_blog_bot_catalog_excluded_slugs",
        [
            "polityka-prywatnosci",
            "polityka-prywatności",
            "regulamin",
            "404",
            "kontakt",
            "thank-you",
            "crm-app",
            "cookie-policy",
            "cookies",
            "privacy-policy",
            "sample-page",
            "polityka-cookies",
            "podziekowanie",
            "sitemap",
        ]
    );
    $excluded_lower = [];
    foreach ((array) $excluded_slugs as $ex) {
        $ex = (string) $ex;
        if ($ex === "") {
            continue;
        }
        $excluded_lower[] = function_exists("mb_strtolower") ? mb_strtolower($ex, "UTF-8") : strtolower($ex);
    }
    $front_id = (int) get_option("page_on_front");
    $privacy_id = (int) get_option("wp_page_for_privacy_policy");
    $gsc_clicks_map = upsellio_blog_bot_gsc_clicks_by_normalized_path();
    $priority_slugs = apply_filters(
        "upsellio_blog_bot_catalog_priority_slugs",
        [
            "page-marketing-google-ads",
            "page-marketing-meta-ads",
            "page-tworzenie-stron-internetowych",
            "page-oferta",
            "page-o-mnie",
        ]
    );
    $priority_lower = [];
    foreach ((array) $priority_slugs as $ps) {
        $ps = (string) $ps;
        if ($ps === "") {
            continue;
        }
        $priority_lower[] = function_exists("mb_strtolower") ? mb_strtolower($ps, "UTF-8") : strtolower($ps);
    }

    $page_ids = get_posts([
        "post_type" => "page",
        "post_status" => "publish",
        "posts_per_page" => 120,
        "orderby" => "menu_order title",
        "order" => "ASC",
        "no_found_rows" => true,
        "fields" => "ids",
    ]);
    $post_ids = get_posts([
        "post_type" => "post",
        "post_status" => "publish",
        "posts_per_page" => 120,
        "orderby" => "date",
        "order" => "DESC",
        "no_found_rows" => true,
        "fields" => "ids",
    ]);
    $ids = array_values(array_unique(array_merge(is_array($page_ids) ? $page_ids : [], is_array($post_ids) ? $post_ids : [])));

    $candidates = [];
    foreach ($ids as $pid) {
        $pid = (int) $pid;
        if ($pid <= 0) {
            continue;
        }
        if ($front_id > 0 && $pid === $front_id) {
            continue;
        }
        if ($privacy_id > 0 && $pid === $privacy_id) {
            continue;
        }
        $slug = (string) get_post_field("post_name", $pid);
        $slug_cmp = $slug !== "" && function_exists("mb_strtolower")
            ? mb_strtolower($slug, "UTF-8")
            : strtolower($slug);
        if ($slug_cmp !== "" && in_array($slug_cmp, $excluded_lower, true)) {
            continue;
        }
        $title = get_the_title($pid);
        if ($title === "") {
            continue;
        }
        $url = get_permalink($pid);
        if (!is_string($url) || $url === "") {
            continue;
        }
        $url = esc_url_raw($url);
        $post_obj = get_post($pid);
        $pt = $post_obj instanceof WP_Post ? $post_obj->post_type : "";

        $tlow = function_exists("mb_strtolower") ? mb_strtolower($title, "UTF-8") : strtolower($title);
        $path = (string) (wp_parse_url($url, PHP_URL_PATH) ?? "");
        $plow = function_exists("mb_strtolower") ? mb_strtolower($path, "UTF-8") : strtolower($path);
        $hay = $tlow . " " . $plow;
        $score = 0;
        if ($slug_cmp !== "" && in_array($slug_cmp, $priority_lower, true)) {
            $score += 15;
        }
        foreach ($needles as $w) {
            if ($w !== "" && strpos($hay, $w) !== false) {
                $score += 3;
            }
        }
        if ($kw_low !== "" && strpos($hay, $kw_low) !== false) {
            $score += 10;
        }
        if ($pt === "page") {
            $score += 2;
        } elseif ($pt === "post") {
            $score += 1;
        }
        if ($pt === "post" && $post_obj instanceof WP_Post) {
            $ts = strtotime((string) $post_obj->post_date_gmt);
            if ($ts !== false) {
                $days = (time() - $ts) / DAY_IN_SECONDS;
                if ($days >= 0 && $days < 400) {
                    $score += 2;
                } elseif ($days < 800) {
                    $score += 1;
                }
            }
        }
        if ($pt === "page" && $post_obj instanceof WP_Post && (int) $post_obj->menu_order > 0) {
            $score += 1;
        }
        $path_norm = upsellio_blog_bot_normalize_local_path($url);
        $gsc_clicks = $path_norm !== "" ? (int) ($gsc_clicks_map[$path_norm] ?? 0) : 0;
        $score += min(20, $gsc_clicks * 2);

        $candidates[] = [
            "id" => $pid,
            "title" => $title,
            "url" => $url,
            "score" => $score,
        ];
    }

    usort($candidates, static function ($a, $b) {
        $sa = (int) ($a["score"] ?? 0);
        $sb = (int) ($b["score"] ?? 0);
        if ($sa !== $sb) {
            return $sb <=> $sa;
        }

        return (int) ($b["id"] ?? 0) <=> (int) ($a["id"] ?? 0);
    });

    $out = [];
    foreach (array_slice($candidates, 0, $limit) as $row) {
        $out[] = [
            "id" => (int) ($row["id"] ?? 0),
            "title" => (string) ($row["title"] ?? ""),
            "url" => (string) ($row["url"] ?? ""),
        ];
    }

    return $out;
}

/**
 * @param array<int, array{id:int, title:string, url:string}> $rows
 */
function upsellio_blog_bot_format_catalog_for_prompt(array $rows): string
{
    $lines = [];
    foreach ($rows as $r) {
        $url = trim((string) ($r["url"] ?? ""));
        $title = trim((string) ($r["title"] ?? ""));
        if ($url === "") {
            continue;
        }
        if (function_exists("mb_substr")) {
            $title = mb_substr($title, 0, 88, "UTF-8");
        } else {
            $title = substr($title, 0, 88);
        }
        $lines[] = $url . " | " . $title;
    }

    return $lines !== [] ? implode("\n", $lines) : "(brak opublikowanych stron/wpisów)";
}

/**
 * Mapa dozwolonych URL (z i bez końcowego slasha) dla linków markdown zwróconych przez model.
 *
 * @param array<int, array{id:int, title:string, url:string}> $catalog
 * @return array<string, true>
 */
function upsellio_blog_bot_allowed_urls_map(array $catalog): array
{
    $map = [];
    foreach ($catalog as $row) {
        $u = trim((string) ($row["url"] ?? ""));
        if ($u === "") {
            continue;
        }
        $u = esc_url_raw($u);
        $host = (string) (wp_parse_url($u, PHP_URL_HOST) ?? "");
        $home_host = (string) (wp_parse_url(home_url("/"), PHP_URL_HOST) ?? "");
        if ($host === "" || $home_host === "" || strtolower($host) !== strtolower($home_host)) {
            continue;
        }
        $map[$u] = true;
        $map[untrailingslashit($u)] = true;
        $map[trailingslashit($u)] = true;
    }

    return $map;
}

function upsellio_blog_bot_url_is_allowed(string $url, array $allowed_map): bool
{
    $url = esc_url_raw(trim($url));
    if ($url === "") {
        return false;
    }
    foreach ([$url, untrailingslashit($url), trailingslashit($url)] as $v) {
        if (isset($allowed_map[$v])) {
            return true;
        }
    }

    return false;
}

function upsellio_blog_bot_canonical_internal_url(string $url): string
{
    $url = esc_url_raw(trim($url));

    return untrailingslashit($url);
}

/**
 * [anchor](https://...) → <a> tylko dla URL z allowlisty (ta sama domena co witryna).
 */
function upsellio_blog_bot_markdown_links_to_html(string $html, array $allowed_map): string
{
    if ($allowed_map === []) {
        return $html;
    }

    return (string) preg_replace_callback(
        '/\[([^\]]{1,220})\]\(\s*(https?:\/\/[^\s\)]+)\s*\)/u',
        static function (array $m) use ($allowed_map): string {
            $anchor = (string) $m[1];
            $raw = trim((string) $m[2]);
            if (!upsellio_blog_bot_url_is_allowed($raw, $allowed_map)) {
                return $anchor;
            }
            $href = esc_url(upsellio_blog_bot_canonical_internal_url($raw));

            return '<a href="' . $href . '">' . esc_html($anchor) . "</a>";
        },
        $html
    );
}

/**
 * SEO Blog Tool liczy nagłówki HTML — konwersja Markdown (# / ## / ###) na tagi.
 */
function upsellio_blog_bot_content_markdown_headings_to_html(string $html): string
{
    $out = preg_replace("/^####\\s+(.+)$/m", "<h4>$1</h4>", $html);
    $out = preg_replace("/^###\\s+(.+)$/m", "<h3>$1</h3>", (string) $out);
    $out = preg_replace("/^##\\s+(.+)$/m", "<h2>$1</h2>", (string) $out);
    $out = preg_replace("/^#\\s+(.+)$/m", "<h2>$1</h2>", (string) $out);

    return (string) $out;
}

/**
 * Spis treści z nagłówków H2/H3 (id na nagłówkach) — bez dodatkowego wywołania AI.
 */
function upsellio_blog_bot_prepend_toc_block(string $html): string
{
    if (!function_exists("upsellio_prepare_toc_content")) {
        return $html;
    }
    $r = upsellio_prepare_toc_content($html);
    $toc = $r["toc"] ?? [];
    $content = isset($r["content"]) ? (string) $r["content"] : $html;
    if (!is_array($toc) || count($toc) < 2) {
        return $content;
    }

    $items = [];
    foreach ($toc as $row) {
        if (!is_array($row)) {
            continue;
        }
        $id = (string) ($row["id"] ?? "");
        $t = trim((string) ($row["title"] ?? ""));
        if ($id === "" || $t === "") {
            continue;
        }
        $lvl = strtolower((string) ($row["level"] ?? "h2"));
        $cls = $lvl === "h3" ? "ups-toc-item ups-toc-h3" : "ups-toc-item ups-toc-h2";

        $items[] = '<li class="' . esc_attr($cls) . '"><a href="#' . esc_attr($id) . '">' . esc_html($t) . "</a></li>";
    }
    if ($items === []) {
        return $content;
    }

    $nav = '<div class="ups-article-toc" role="navigation" aria-label="' . esc_attr__("Spis treści", "upsellio") . '">'
        . '<p class="ups-article-toc-title"><strong>' . esc_html__("Spis treści", "upsellio") . "</strong></p>"
        . "<ol>" . implode("", $items) . "</ol></div>";

    return $nav . "\n\n" . $content;
}

/**
 * Shortcodes wymagane przez SEO Blog Tool (krytyczne przy typie seo_article / landing_sales).
 */
function upsellio_blog_bot_ensure_sbt_shortcodes(string $html, string $article_type): string
{
    if (stripos($html, "[upsellio_internal_links") === false) {
        $html .= "\n\n<h2>Powiązane artykuły</h2>\n<p>[upsellio_internal_links limit=\"3\" title=\"Warto przeczytać także\"]</p>";
    }
    if ($article_type === "seo_article" || $article_type === "landing_sales" || $article_type === "blog_educational") {
        if (stripos($html, "[upsellio_contact_form") === false) {
            $html .= "\n\n<h2>Kontakt</h2>\n<p>[upsellio_contact_form]</p>";
        }
    }

    return $html;
}

function upsellio_blog_bot_mb_len(string $s): int
{
    return function_exists("mb_strlen") ? (int) mb_strlen($s, "UTF-8") : strlen($s);
}

/**
 * Wyszukiwanie podłańcucha bez rozróżniania wielkości liter (UTF-8).
 */
function upsellio_blog_bot_str_contains_ci(string $haystack, string $needle): bool
{
    if ($needle === "") {
        return true;
    }
    if (function_exists("mb_stripos")) {
        return mb_stripos($haystack, $needle, 0, "UTF-8") !== false;
    }

    return stripos($haystack, $needle) !== false;
}

function upsellio_blog_bot_mb_cut(string $s, int $max): string
{
    if (upsellio_blog_bot_mb_len($s) <= $max) {
        return $s;
    }
    $cut = function_exists("mb_substr") ? mb_substr($s, 0, $max, "UTF-8") : substr($s, 0, $max);
    $last_space = function_exists("mb_strrpos") ? mb_strrpos($cut, " ", 0, "UTF-8") : strrpos($cut, " ");
    if ($last_space !== false && $last_space > (int) ($max * 0.7)) {
        $cut = function_exists("mb_substr") ? mb_substr($cut, 0, $last_space, "UTF-8") : substr($cut, 0, $last_space);
    }

    return rtrim($cut, ",;: ");
}

/**
 * SEO Tool: SEO title 45–60 znaków (CMS dokleja nazwę witryny do tytułu strony w SERP).
 */
function upsellio_blog_bot_clamp_seo_title(string $seo_title, string $fallback_title, string $primary_query): string
{
    $seo_title = trim($seo_title);
    if ($seo_title === "") {
        $seo_title = trim($fallback_title);
    }
    $len = upsellio_blog_bot_mb_len($seo_title);
    if ($len >= 45 && $len <= 60) {
        return $seo_title;
    }
    if ($len > 60) {
        return upsellio_blog_bot_mb_cut($seo_title, 60);
    }
    $suffix = " — poradnik B2B";
    $candidate = $seo_title . $suffix;
    if (upsellio_blog_bot_mb_len($candidate) < 45 && $primary_query !== "") {
        $candidate = $seo_title . " — " . upsellio_blog_bot_mb_cut($primary_query, 48);
    }
    if (upsellio_blog_bot_mb_len($candidate) < 45) {
        $candidate = $candidate . " — checklista i metryki";
    }

    return upsellio_blog_bot_mb_cut($candidate, 60);
}

/**
 * Zapewnia dosłowną obecność frazy focus (Rank Math — exact match w opisie).
 */
function upsellio_blog_bot_meta_ensure_primary_query(string $meta, string $primary_query): string
{
    $primary_query = trim($primary_query);
    if ($primary_query === "" || $meta === "") {
        return $meta;
    }
    if (upsellio_blog_bot_str_contains_ci($meta, $primary_query)) {
        return $meta;
    }
    $prefix = $primary_query . " — ";

    return $prefix . $meta;
}

/**
 * SEO Tool: meta description 140–160 znaków.
 */
function upsellio_blog_bot_clamp_meta_description(string $meta, string $title, string $primary_query): string
{
    $meta = trim($meta);
    $pq = trim($primary_query);
    if ($meta === "") {
        if ($pq !== "") {
            $base = "Praktyczny poradnik: " . $pq . ". Konkretne kroki i przykłady dla firm B2B.";
            $meta = upsellio_blog_bot_mb_len($base) <= 160
                ? $base
                : upsellio_blog_bot_mb_cut($base, 157) . "...";
        } else {
            $meta = "Poradnik B2B — konkretne metody, przykłady wdrożeń i checklista dla marketerów i właścicieli firm.";
        }
    }
    $meta = upsellio_blog_bot_meta_ensure_primary_query($meta, $pq);
    $len = upsellio_blog_bot_mb_len($meta);
    if ($len >= 140 && $len <= 160) {
        return $meta;
    }
    if ($len > 160) {
        $meta = upsellio_blog_bot_mb_cut($meta, 160);
        if ($pq !== "" && !upsellio_blog_bot_str_contains_ci($meta, $pq)) {
            $meta = upsellio_blog_bot_mb_cut($pq . " — " . upsellio_blog_bot_mb_cut(wp_strip_all_tags($title), 120), 160);
        }

        return $meta;
    }
    $tail = $pq !== ""
        ? " Sprawdź jak wdrożyć i jakich błędów unikać — praktyczny przewodnik dla firm B2B."
        : " Konkretne metody i przykłady wdrożeń dla marketerów i właścicieli firm B2B.";
    while (upsellio_blog_bot_mb_len($meta) < 140) {
        $meta .= $tail;
        if (upsellio_blog_bot_mb_len($meta) > 300) {
            break;
        }
    }
    $meta = upsellio_blog_bot_mb_cut($meta, 160);
    if ($pq !== "" && !upsellio_blog_bot_str_contains_ci($meta, $pq)) {
        $meta = upsellio_blog_bot_mb_cut($pq . ". " . $meta, 160);
    }

    return $meta;
}

/**
 * Post-processing treści — naprawia typowe błędy SEO (Rank Math) zanim treść trafi do WP.
 * Wywołaj po markdown_links_to_html / ensure_sbt_shortcodes, przed wp_kses_post.
 *
 * @param string $primary_query Fraza focus (primary_query lub kolejka).
 */
function upsellio_blog_bot_fix_seo_issues(string $content, string $primary_query, string $keyword): string
{
    (void) $keyword;
    if ($content === "" || trim($primary_query) === "") {
        return $content;
    }

    $primary_query = trim($primary_query);
    $kw_lower = function_exists("mb_strtolower")
        ? mb_strtolower($primary_query, "UTF-8")
        : strtolower($primary_query);
    $kw_words = preg_split("/\s+/u", $kw_lower, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $kw_prefix = implode(" ", array_slice($kw_words, 0, 2));
    if (upsellio_blog_bot_mb_len($kw_prefix) <= 3) {
        $kw_prefix = $kw_lower;
    }

    // 1. Pierwszy akapit — jeśli brak prefiksu frazy, wstrzyknij zdanie otwierające.
    if (preg_match("/<p[^>]*>(.*?)<\/p>/is", $content, $first_p_match)) {
        $first_p_text = function_exists("mb_strtolower")
            ? mb_strtolower(wp_strip_all_tags($first_p_match[1]), "UTF-8")
            : strtolower(wp_strip_all_tags($first_p_match[1]));
        if (upsellio_blog_bot_mb_len($kw_prefix) > 3 && !upsellio_blog_bot_str_contains_ci($first_p_text, $kw_prefix)) {
            $inject = ucfirst($primary_query)
                . " to temat który warto dobrze rozumieć, jeśli prowadzisz firmę B2B. ";
            $content = (string) preg_replace("/<p([^>]*)>/i", "<p$1>" . $inject, $content, 1);
        }
    }

    // 2. Co najmniej jeden nagłówek H2/H3 z fragmentem frazy — inaczej dopisz do pierwszego H2/H3.
    preg_match_all("/<h[23][^>]*>.*?<\/h[23]>/is", $content, $heading_blocks);
    $kw_in_heading = false;
    if (!empty($heading_blocks[0])) {
        foreach ($heading_blocks[0] as $block) {
            $ht = function_exists("mb_strtolower")
                ? mb_strtolower(wp_strip_all_tags($block), "UTF-8")
                : strtolower(wp_strip_all_tags($block));
            if (upsellio_blog_bot_str_contains_ci($ht, $kw_prefix)) {
                $kw_in_heading = true;
                break;
            }
        }
    }
    if (!$kw_in_heading) {
        $pq = $primary_query;
        $content = (string) preg_replace_callback(
            '/<h([23])(\b[^>]*)>(.*?)<\/h\1>/is',
            static function (array $m) use ($pq): string {
                $plain = trim(wp_strip_all_tags($m[3]));

                return "<h" . $m[1] . $m[2] . ">" . esc_html($plain . " — " . $pq) . "</h" . $m[1] . ">";
            },
            $content,
            1
        );
    }

    // 3. Link zewnętrzny (authority), jeśli brak jakiegokolwiek linku poza domeną.
    if (!upsellio_blog_bot_content_has_external_http_link($content)) {
        $external_url = "https://think.withgoogle.com/";
        $external_text = "Think with Google";
        $external_link = " (<a href=\"" . esc_url($external_url) . "\" rel=\"noopener noreferrer\" target=\"_blank\">"
            . esc_html($external_text) . "</a>)";
        $count = 0;
        $content = (string) preg_replace_callback(
            "/<\/p>/i",
            static function (array $m) use (&$count, $external_link): string {
                $count++;

                return $count === 2 ? $external_link . "</p>" : $m[0];
            },
            $content
        );
    }

    return $content;
}

/**
 * Czy w treści jest link http(s) na inny host niż bieżąca witryna.
 */
function upsellio_blog_bot_content_has_external_http_link(string $content): bool
{
    $home = (string) (wp_parse_url(home_url(), PHP_URL_HOST) ?? "");
    if (!preg_match_all('/<a\s[^>]*href\s*=\s*["\']([^"\']+)["\']/i', $content, $m)) {
        return false;
    }
    foreach ($m[1] as $url) {
        $url = trim((string) $url);
        if ($url === "" || preg_match("#^(mailto:|tel:|#)#i", $url)) {
            continue;
        }
        if (!preg_match("#^https?://#i", $url)) {
            continue;
        }
        $host = (string) (wp_parse_url($url, PHP_URL_HOST) ?? "");
        if ($host !== "" && strcasecmp($host, $home) !== 0) {
            return true;
        }
    }

    return false;
}

/**
 * Zapis metadanych zgodnych z SEO Blog Tool po utworzeniu draftu przez Blog Bota.
 *
 * @param array<string, mixed> $data
 */
function upsellio_blog_bot_save_sbt_meta(int $post_id, array $data, string $title, string $meta_description, string $queue_keyword): void
{
    if (!function_exists("upsellio_save_seo_meta_for_post")) {
        return;
    }
    $post_id = max(1, $post_id);
    $queue_keyword = trim($queue_keyword);
    $title = trim($title);

    $article_type = sanitize_key((string) ($data["article_type"] ?? "seo_article"));
    if (!in_array($article_type, ["blog_educational", "seo_article", "landing_sales"], true)) {
        $article_type = "seo_article";
    }

    $primary_query = trim((string) ($data["primary_query"] ?? ""));
    if ($primary_query === "") {
        $primary_query = $queue_keyword;
    }

    $seo_title = upsellio_blog_bot_clamp_seo_title(
        trim((string) ($data["seo_title"] ?? "")),
        $title,
        $primary_query
    );
    $seo_desc = upsellio_blog_bot_clamp_meta_description($meta_description, $title, $primary_query);

    $query_cluster = trim((string) ($data["query_cluster"] ?? ""));
    if ($query_cluster === "" && isset($data["tags"]) && is_array($data["tags"])) {
        $query_cluster = implode(", ", array_slice(array_map("strval", $data["tags"]), 0, 8));
    }
    if ($query_cluster === "" && $primary_query !== "") {
        $query_cluster = $primary_query . ", wdrożenie B2B, checklista, KPI, konwersje, strategia, proces, najlepsze praktyki";
    }

    $user_questions = trim((string) ($data["user_questions"] ?? ""));
    if ($user_questions === "" && isset($data["user_questions"]) && is_array($data["user_questions"])) {
        $user_questions = implode("\n", array_map("strval", $data["user_questions"]));
    }
    if ($user_questions === "") {
        $tq = $queue_keyword !== "" ? $queue_keyword : $primary_query;
        $user_questions = "Od czego zacząć wdrożenie w temacie „" . $tq . "”?\n"
            . "Jakie typowe błędy warto wyeliminować na starcie?\n"
            . "Jak sprawdzić, czy wybrane działania przynoszą mierzalny efekt?";
    }

    upsellio_save_seo_meta_for_post($post_id, $seo_title, $seo_desc, $primary_query, $query_cluster, $user_questions, $article_type);
    update_post_meta($post_id, "_upsellio_meta_description", $seo_desc);

    if ($seo_title !== "") {
        update_post_meta($post_id, "rank_math_facebook_title", $seo_title);
        update_post_meta($post_id, "rank_math_twitter_title", $seo_title);
    }
    if ($seo_desc !== "") {
        update_post_meta($post_id, "rank_math_facebook_description", $seo_desc);
        update_post_meta($post_id, "rank_math_twitter_description", $seo_desc);
    }

    $problem = trim((string) ($data["problem"] ?? ""));
    if (upsellio_blog_bot_mb_len($problem) < 80) {
        $problem = "Firmy B2B mają trudność z tematem „" . $queue_keyword . "”: brakuje czytelnego procesu, mierzalnych KPI i wiedzy o typowych błędach. Artykuł porządkuje temat krok po kroku.";
    }
    $outcome = trim((string) ($data["outcome"] ?? ""));
    if (upsellio_blog_bot_mb_len($outcome) < 80) {
        $outcome = "Po lekturze czytelnik wie jak podejść do „" . $queue_keyword . "”, jakich błędów unikać i od czego zacząć wdrożenie. Oszczędza czas i redukuje ryzyko złych decyzji.";
    }

    $optional = [
        "_upsellio_audience" => trim((string) ($data["audience"] ?? "")),
        "_upsellio_search_intent" => trim((string) ($data["search_intent"] ?? "Informacyjna")),
        "_upsellio_problem" => $problem,
        "_upsellio_outcome" => $outcome,
        "_upsellio_cta_text" => trim((string) ($data["cta_text"] ?? "Umów bezpłatną rozmowę")),
    ];
    foreach ($optional as $meta_key => $meta_val) {
        if ($meta_val !== "") {
            update_post_meta($post_id, $meta_key, $meta_val);
        }
    }
}

/**
 * Frazy z GSC (endpoint /gsc-keywords lub import) — do wzbogacenia promptu blog bota.
 */
function upsellio_blog_bot_get_converting_keywords(int $top = 8): string
{
    $top = max(1, min(40, $top));
    $rows = get_option("upsellio_keyword_metrics_rows", []);
    if (!is_array($rows) || $rows === []) {
        return "Brak danych GSC (synchronizuj przez REST /wp-json/upsellio/v1/gsc-keywords lub import w Site analytics).";
    }
    usort($rows, static function ($a, $b) {
        $ca = is_array($a) ? (int) ($a["clicks"] ?? 0) : 0;
        $cb = is_array($b) ? (int) ($b["clicks"] ?? 0) : 0;

        return $cb <=> $ca;
    });
    $lines = [];
    foreach (array_slice($rows, 0, $top) as $r) {
        if (!is_array($r)) {
            continue;
        }
        $kw = trim((string) ($r["keyword"] ?? ""));
        if ($kw === "") {
            continue;
        }
        $pos = (float) ($r["position"] ?? 0);
        $cl = (int) ($r["clicks"] ?? 0);
        $ctr = (float) ($r["ctr"] ?? 0);
        $lines[] = sprintf('- "%s" | poz. %.1f | %d kliknięć | CTR %.1f%%', $kw, $pos, $cl, $ctr);
    }

    return $lines !== [] ? implode("\n", $lines) : "Brak przetworzonych wierszy GSC.";
}

/**
 * @param array<int, array{id:int, title:string, url:string}>|null $catalog
 * @param string $prompt_mode "normal" | "slim_retry" — drugi przebieg: krótszy kontekst, by zmieścić pełny JSON.
 */
function upsellio_blog_bot_build_prompt(string $keyword, ?array $catalog = null, string $prompt_mode = "normal"): string
{
    $slim = $prompt_mode === "slim_retry";
    if ($slim) {
        if ($catalog === null) {
            $catalog = upsellio_blog_bot_catalog_for_keyword($keyword, 14);
        } else {
            $catalog = array_slice($catalog, 0, 14);
        }
    } elseif ($catalog === null) {
        $cat_limit = (int) apply_filters("upsellio_blog_bot_catalog_limit", 32);
        $catalog = upsellio_blog_bot_catalog_for_keyword($keyword, max(12, min(80, $cat_limit)));
    }
    $catalog_block = upsellio_blog_bot_format_catalog_for_prompt($catalog);

    $prompt_template = (string) get_option("ups_ai_prompt_blog_post", "");
    $target_length = max(600, (int) get_option("ups_blog_bot_target_length", 1200));
    if ($slim) {
        $target_length = max(500, (int) round($target_length * 0.90));
    }
    $posts_ctx = upsellio_blog_bot_get_posts_context($slim ? 6 : 10);
    $services_ctx = upsellio_blog_bot_get_services_context();
    $tone = "partnerski, konkretny, B2B (PL)";
    $converting_kw = upsellio_blog_bot_get_converting_keywords($slim ? 5 : 8);

    $gsc_keyword_context = "";
    if (function_exists("upsellio_gsc_build_keyword_context")) {
        $gsc_keyword_context = upsellio_gsc_build_keyword_context($keyword);
    }
    if ($slim && $gsc_keyword_context !== "") {
        if (function_exists("mb_strlen") && mb_strlen($gsc_keyword_context, "UTF-8") > 2600) {
            $gsc_keyword_context = mb_substr($gsc_keyword_context, 0, 2600, "UTF-8") . "\n[…]\n";
        } elseif (strlen($gsc_keyword_context) > 2600) {
            $gsc_keyword_context = substr($gsc_keyword_context, 0, 2600) . "\n[…]\n";
        }
    }

    if ($prompt_template === "") {
        $json_rules = upsellio_blog_bot_prompt_json_html_rules();
        $prompt_template = "Napisz artykuł blogowy B2B (PL) na temat: {keyword}\n\n"
            . "Długość: ok. {target_length} słów. Ton: {tone}.\n\n"
            . "BEZWZGLĘDNE WYMAGANIA SEO (niespełnienie = błąd):\n"
            . "1. Pierwsze zdanie pierwszego akapitu MUSI zawierać dosłownie frazę „{keyword}” lub jej bezpośrednią odmianę gramatyczną. Nie zaczynaj od pytania — zacznij od frazy.\n"
            . "2. META DESCRIPTION musi zawierać dosłownie „{keyword}” i mieć 140–160 znaków.\n"
            . "3. Fraza „{keyword}” lub jej odmiana musi pojawić się minimum 3× w treści (H2/H3 + akapity). Gęstość: 0,5–2%.\n"
            . "4. Przynajmniej jeden H2 lub H3 musi zawierać fragment frazy „{keyword}”.\n"
            . "5. Wstaw 1–2 linki zewnętrzne do wiarygodnych źródeł (think.withgoogle.com, semrush.com, searchengineland.com lub inne branżowe). Format: [anchor](https://url).\n\n"
            . "Istniejące wpisy (unikaj duplikowania tematów):\n{existing_posts}\n\n"
            . "Frazy które już przynoszą kliknięcia:\n{converting_keywords}\n\n"
            . "ANALIZA GSC DLA TEJ FRAZY:\n{gsc_keyword_context}\n\n"
            . "INSTRUKCJE NA PODSTAWIE GSC:\n"
            . "- Użyj fraz z sekcji „POWIĄZANE FRAZY Z GSC” jako query_cluster w JSON.\n"
            . "- Dopasuj ton do INTENCJI WYSZUKIWANIA.\n"
            . "- Intencja lokalna: wpleć kontekst miejsca naturalnie.\n"
            . "- Intencja transakcyjna: wyraźne korzyści, CTA.\n\n"
            . "Kontekst usług firmy:\n{services_context}\n\n"
            . "KATALOG LINKOWANIA WEWNĘTRZNEGO (tylko te URL):\n{internal_url_catalog}\n\n"
            . "ZASADY LINKOWANIA:\n"
            . "- 3–5 linków wewnętrznych WEWNĄTRZ treści.\n"
            . "- 1–2 linki zewnętrzne do authority sources (think.withgoogle.com, semrush.com itp.).\n"
            . "- Format linków: [anchor text](PEŁNY_URL).\n\n"
            . "STRUKTURA ARTYKUŁU:\n"
            . "- H1: tytuł (nie wstawiaj — WordPress doda automatycznie)\n"
            . "- Pierwszy akapit: fraza kluczowa + problem czytelnika\n"
            . "- H2 (min. 3): pytania lub twierdzenia, przynajmniej jedno z frazą kluczową\n"
            . "- H3: podsekcje gdzie potrzeba\n"
            . "- FAQ: <h2>Najczęstsze pytania</h2> + min. 3× <h3>Pytanie?</h3> + <p>Odpowiedź</p>\n"
            . "- Ostatni akapit: konkretny krok który czytelnik zrobi dziś\n\n"
            . "WYMAGANIA TECHNICZNE:\n"
            . "- content: wyłącznie HTML (<p>, <ul>/<li>, <h2>, <h3>). Zero Markdown nagłówków.\n"
            . "- seo_title: 45–60 znaków z frazą na początku.\n"
            . "- meta_description: 140–160 znaków, MUSI zawierać „{keyword}”.\n"
            . "- primary_query: dokładna fraza SEO = „{keyword}”.\n"
            . "- query_cluster: 8–12 fraz powiązanych przez przecinek.\n"
            . "- tags: min. 3 tagi.\n"
            . $json_rules
            . "\n\n"
            . "Spis treści (H2/H3) zostanie dodany automatycznie po stronie serwera — nie wstawiaj własnego spisu w treści.\n\n"
            . "KRYTERIA (stosuj zawsze): Pisz dla decydenta B2B; JSON parsowalny (bez fence markdown); jeden spójny ton.\n\n"
            . "Odpowiedz WYŁĄCZNIE jednym obiektem JSON (pierwszy znak {, ostatni }):\n"
            . "title, slug, seo_title, meta_description, primary_query, query_cluster,\n"
            . "user_questions, article_type, audience, search_intent, problem, outcome,\n"
            . "cta_text, tags (tablica), content (HTML + linki markdown).\n\n"
            . "---\n"
            . "DANE DLA TEGO ARTYKUŁU:\n"
            . "Główna fraza / temat: {keyword}\n"
            . "Długość docelowa: ok. {target_length} słów.\n"
            . "Ton: {tone}.\n\n"
            . "Istniejące wpisy (unikaj duplikowania tematów):\n{existing_posts}\n\n"
            . "Frazy które już przynoszą kliknięcia z Google:\n{converting_keywords}\n\n"
            . "ANALIZA GSC DLA TEJ FRAZY:\n{gsc_keyword_context}\n\n"
            . "Kontekst usług firmy:\n{services_context}\n\n"
            . "KATALOG LINKOWANIA WEWNĘTRZNEGO (tylko te URL — jedna linia = URL | tytuł):\n{internal_url_catalog}\n\n"
            . "Na podstawie powyższych danych wygeneruj kompletny obiekt JSON.";
    }

    $prompt = str_replace(
        [
            "{keyword}",
            "{target_length}",
            "{existing_posts}",
            "{services_context}",
            "{tone}",
            "{converting_keywords}",
            "{gsc_keyword_context}",
            "{internal_url_catalog}",
        ],
        [
            (string) $keyword,
            (string) $target_length,
            $posts_ctx,
            $services_ctx,
            $tone,
            $converting_kw,
            $gsc_keyword_context !== "" ? $gsc_keyword_context : __("Brak dodatkowego kontekstu GSC dla tej frazy (importuj dane fraz w Analityce).", "upsellio"),
            $catalog_block,
        ],
        $prompt_template
    );

    if (stripos($prompt, "cudzysłowy w atrybutach HTML") === false) {
        $prompt .= "\n\n" . __("WYMAGANIA JSON — pole content:", "upsellio") . "\n" . upsellio_blog_bot_prompt_json_html_rules();
    }

    if (strpos($prompt_template, "{gsc_keyword_context}") === false && trim($prompt_template) !== "" && $gsc_keyword_context !== "") {
        $prompt .= "\n\n---\n" . __("ANALIZA GSC DLA TEJ FRAZY:", "upsellio") . "\n" . $gsc_keyword_context;
    }

    if (strpos($prompt_template, "{internal_url_catalog}") === false && trim($prompt_template) !== "") {
        $prompt .= "\n\n---\nINTERNAL_URL_CATALOG — używaj WYŁĄCZNIE tych adresów w linkach [tekst](url) w polu content (2–5 linków), kopiuj URL 1:1:\n"
            . $catalog_block;
    }

    if (!$slim && function_exists("upsellio_ai_master_context")) {
        $master_blog = upsellio_ai_master_context("blog");
        if ($master_blog !== "") {
            $prompt .= "\n\n---\nDane o skuteczności Twojego bloga (priorytety treści — co generuje leady vs martwy ruch):\n" . $master_blog;
        }
    }

    $prefix = upsellio_blog_bot_company_prefix();
    if ($prefix !== "") {
        $prompt = $prefix . $prompt;
    }

    if ($slim) {
        $prompt .= "\n\n---\nRETRY (kompakt)\n"
            . "Poprzednia odpowiedź była obcięta lub nieparsowalna. Odpowiedz WYŁĄCZNIE jednym kompletnym obiektem JSON kończącym się na „}”.\n"
            . "Pole content: krótszy artykuł HTML (orientacyjnie " . (int) round($target_length * 6) . "–" . (int) round($target_length * 10) . " znaków), 2–3 linki markdown z katalogu, 2–3 nagłówki H2/H3 + krótka sekcja FAQ — ale WSZYSTKIE klucze JSON muszą być obecne i poprawne.\n";
    }

    return $prompt;
}

/**
 * Dzieli prompt na blok cache’owany (długie, powtarzalne instrukcje) i dynamiczny (fraza, wpisy, GSC, katalog).
 * Wymaga ≥ upsellio_anthropic_crm_cache_min_chars znaków przed separatorem — inaczej API nie włączy prompt caching.
 *
 * @return array{cached: string, dynamic: string}|null
 */
function upsellio_blog_bot_prompt_cache_split(string $full_prompt): ?array
{
    $full_prompt = (string) $full_prompt;
    if ($full_prompt === "") {
        return null;
    }

    $min_cached = (int) apply_filters(
        "upsellio_blog_bot_cache_split_min_chars",
        (int) apply_filters("upsellio_anthropic_crm_cache_min_chars", 4000)
    );
    $min_cached = max(1024, $min_cached);

    $markers = (array) apply_filters(
        "upsellio_blog_bot_prompt_cache_markers",
        [
            "\n---\nDANE DLA TEGO ARTYKUŁU:",
            "\n---\nDANE DLA TEGO ARTYKUŁU:\n",
            "DANE DLA TEGO ARTYKUŁU:",
            "Istniejące wpisy",
            "existing_posts",
            "Frazy które już",
        ]
    );

    $split_pos = false;
    foreach ($markers as $m) {
        $m = (string) $m;
        if ($m === "") {
            continue;
        }
        $pos = function_exists("mb_strpos")
            ? mb_strpos($full_prompt, $m, 0, "UTF-8")
            : strpos($full_prompt, $m);
        if ($pos !== false && $pos >= $min_cached && ($split_pos === false || $pos > (int) $split_pos)) {
            $split_pos = (int) $pos;
        }
    }

    if ($split_pos === false) {
        return null;
    }

    $cached = function_exists("mb_substr")
        ? mb_substr($full_prompt, 0, $split_pos, "UTF-8")
        : substr($full_prompt, 0, $split_pos);
    $dynamic = function_exists("mb_substr")
        ? mb_substr($full_prompt, $split_pos, null, "UTF-8")
        : substr($full_prompt, $split_pos);

    if (trim((string) $dynamic) === "") {
        return null;
    }

    $cache_len = function_exists("mb_strlen")
        ? (int) mb_strlen((string) $cached, "UTF-8")
        : (int) strlen((string) $cached);
    if ($cache_len < $min_cached) {
        return null;
    }

    return [
        "cached" => (string) $cached,
        "dynamic" => (string) $dynamic,
    ];
}

function upsellio_blog_bot_clear_cron(): void
{
    while (($ts = wp_next_scheduled("upsellio_blog_bot_cron_run")) !== false) {
        wp_unschedule_event((int) $ts, "upsellio_blog_bot_cron_run");
    }
}

function upsellio_blog_bot_next_slot_timestamp(): int
{
    $now = (int) current_time("timestamp");
    $next = strtotime("next monday 07:00:00", $now);

    return $next !== false ? $next : $now + DAY_IN_SECONDS;
}

/**
 * Miniaturka wpisu: wyszukanie w mediach po słowach z frazy lub obraz z opcji CRM.
 */
function upsellio_blog_bot_assign_featured_image(int $post_id, string $keyword): void
{
    $post_id = (int) $post_id;
    if ($post_id <= 0 || (int) get_post_thumbnail_id($post_id) > 0) {
        return;
    }
    $keyword = trim($keyword);
    $search = "";
    if ($keyword !== "") {
        $parts = array_values(array_filter(preg_split("/\s+/u", $keyword) ?: [], static function ($w) {
            return trim((string) $w) !== "";
        }));
        $search = implode(" ", array_slice($parts, 0, 3));
    }
    if ($search !== "") {
        $media = get_posts([
            "post_type" => "attachment",
            "post_status" => "inherit",
            "posts_per_page" => 1,
            "s" => $search,
            "post_mime_type" => "image",
            "orderby" => "date",
            "order" => "DESC",
        ]);
        if (!empty($media[0]) && $media[0] instanceof WP_Post) {
            $img_id = (int) $media[0]->ID;
            set_post_thumbnail($post_id, $img_id);
            if ($keyword !== "") {
                $existing_alt = (string) get_post_meta($img_id, "_wp_attachment_image_alt", true);
                if ($existing_alt === "") {
                    update_post_meta($img_id, "_wp_attachment_image_alt", sanitize_text_field($keyword));
                }
            }

            return;
        }
    }
    $default_id = (int) get_option("ups_blog_bot_default_thumbnail_id", 0);
    if ($default_id > 0 && wp_attachment_is_image($default_id)) {
        set_post_thumbnail($post_id, $default_id);
        if ($keyword !== "") {
            $existing_alt = (string) get_post_meta($default_id, "_wp_attachment_image_alt", true);
            if ($existing_alt === "") {
                update_post_meta($default_id, "_wp_attachment_image_alt", sanitize_text_field($keyword));
            }
        }
    }
}

function upsellio_blog_bot_ensure_cron(): void
{
    upsellio_blog_bot_clear_cron();
    if ((string) get_option("ups_blog_bot_enabled", "0") !== "1") {
        return;
    }
    $schedule = (string) get_option("ups_blog_bot_schedule", "weekly");
    if (!in_array($schedule, ["daily", "biweekly", "weekly", "monthly"], true)) {
        $schedule = "weekly";
    }
    $start = upsellio_blog_bot_next_slot_timestamp();
    wp_schedule_event($start, $schedule, "upsellio_blog_bot_cron_run");
}

function upsellio_blog_bot_generate_and_save(): void
{
    upsellio_blog_bot_set_last_error(null);

    if (function_exists("set_time_limit")) {
        $tl = (int) apply_filters("upsellio_blog_bot_time_limit", 300);
        @set_time_limit(max(120, min(900, $tl)));
    }

    if ((string) get_option("ups_blog_bot_enabled", "0") !== "1") {
        upsellio_blog_bot_set_last_error([
            "code" => "disabled",
            "detail" => "Włącz Blog Bota w CRM → Ustawienia → AI.",
        ]);

        return;
    }
    if (!function_exists("upsellio_anthropic_crm_api_key") || upsellio_anthropic_crm_api_key() === "") {
        upsellio_blog_bot_set_last_error([
            "code" => "no_api_key",
            "detail" => "Brak UPSELLIO_ANTHROPIC_API_KEY / ups_anthropic_api_key.",
        ]);

        return;
    }

    $keyword = upsellio_blog_bot_peek_keyword();
    if ($keyword === "") {
        $notify = (string) get_option("ups_blog_bot_notify_email", "");
        if (is_email($notify)) {
            wp_mail(
                $notify,
                "Blog Bot: pusta kolejka fraz",
                "Kolejka tematów jest pusta. Dodaj nowe frazy w CRM → Ustawienia → AI / Anthropic."
            );
        }
        upsellio_blog_bot_set_last_error([
            "code" => "empty_queue",
            "detail" => "Brak tematu na początku kolejki ups_blog_bot_keywords_queue.",
        ]);

        return;
    }

    if (function_exists("upsellio_ai_model_for")) {
        $model = upsellio_ai_model_for("blog_post");
    } else {
        $model = trim((string) get_option("ups_blog_bot_model", ""));
        if ($model === "") {
            $model = "claude-haiku-4-5-20251001";
        }
    }

    $cat_lim = (int) apply_filters("upsellio_blog_bot_catalog_limit", 32);
    $catalog = upsellio_blog_bot_catalog_for_keyword($keyword, max(12, min(80, $cat_lim)));
    $full_prompt = upsellio_blog_bot_build_prompt($keyword, $catalog);
    $cache_split = upsellio_blog_bot_prompt_cache_split($full_prompt);
    // Pełny wpis w jednym obiekcie JSON — mały limit obcina odpowiedź w połowie i psuje parsowanie.
    // HTTP: domyślnie 240 s (wcześniej 90 s — częsty cURL 28 przy dużym max_tokens / wolnym API).
    $stored_to = (int) get_option("ups_blog_bot_http_timeout", 0);
    $php_limit = (int) ini_get("max_execution_time");
    if ($php_limit <= 0) {
        $safe_timeout = 180;
    } else {
        $safe_timeout = $php_limit > 30 ? min($php_limit - 20, 180) : 120;
    }
    $safe_timeout = max(60, min(300, $safe_timeout));
    $api_timeout = $stored_to > 0
        ? max(60, min(600, $stored_to))
        : (int) apply_filters("upsellio_blog_bot_api_timeout", $safe_timeout);
    $api_timeout = max(60, min(600, $api_timeout));
    $max_out = upsellio_blog_bot_resolve_max_output_tokens(8192);
    $raw = upsellio_anthropic_crm_send_user_prompt($full_prompt, $max_out, $api_timeout, $model, $cache_split);
    if ($raw === null) {
        $api_detail = function_exists("upsellio_anthropic_crm_get_last_send_error")
            ? upsellio_anthropic_crm_get_last_send_error()
            : "";
        upsellio_blog_bot_set_last_error([
            "code" => "api_null",
            "detail" => $api_detail !== "" ? $api_detail : "Brak treści odpowiedzi (HTTP / sieć / limity).",
        ]);

        return;
    }

    $data = upsellio_anthropic_crm_parse_json_object($raw);
    if (!is_array($data) && apply_filters("upsellio_blog_bot_retry_on_bad_json", true)) {
        $catalog_slim = array_slice($catalog, 0, 14);
        $slim_prompt = upsellio_blog_bot_build_prompt($keyword, $catalog_slim, "slim_retry");
        $raw_retry = upsellio_anthropic_crm_send_user_prompt($slim_prompt, $max_out, $api_timeout, $model, null);
        if ($raw_retry !== null) {
            $data = upsellio_anthropic_crm_parse_json_object($raw_retry);
        }
    }
    if (!is_array($data)) {
        $snippet = function_exists("mb_substr") ? mb_substr(trim((string) $raw), 0, 400, "UTF-8") : substr(trim((string) $raw), 0, 400);
        $hint = "";
        $stop = function_exists("upsellio_anthropic_crm_get_last_stop_reason")
            ? upsellio_anthropic_crm_get_last_stop_reason()
            : "";
        if ($stop === "max_tokens") {
            $hint = " API zakończyło na limicie wyjścia (stop_reason: max_tokens) — JSON jest obcięty. Wykonano automatyczny retry w trybie kompaktowym; jeśli nadal błąd: zmniejsz „Docelowa liczba słów”, filtr upsellio_blog_bot_catalog_limit lub model (Haiku).";
        }
        $stripped_try = function_exists("upsellio_anthropic_crm_strip_json_markdown_fence")
            ? upsellio_anthropic_crm_strip_json_markdown_fence((string) $raw)
            : trim((string) $raw);
        if ($stripped_try !== ""
            && strpos($stripped_try, "{") !== false
            && function_exists("upsellio_anthropic_crm_extract_first_json_object")
            && upsellio_anthropic_crm_extract_first_json_object($stripped_try) === null
        ) {
            $hint .= " Typowa przyczyna: obcięta odpowiedź (brak zamykającego „}”) albo niepoprawny JSON w środku pola „content”.";
        }
        upsellio_blog_bot_set_last_error([
            "code" => "bad_json",
            "detail" => "Nie udało się wyciągnąć obiektu JSON z odpowiedzi (w tym po retry kompaktowym)." . $hint . " Początek: " . $snippet,
        ]);

        return;
    }

    $title = sanitize_text_field((string) ($data["title"] ?? ""));
    if ($title === "") {
        $title = sanitize_text_field((string) ($data["headline"] ?? ""));
    }

    $article_type = sanitize_key((string) ($data["article_type"] ?? "seo_article"));
    if (!in_array($article_type, ["blog_educational", "seo_article", "landing_sales"], true)) {
        $article_type = "seo_article";
    }

    $content_raw = trim((string) ($data["content"] ?? ""));
    if ($content_raw === "") {
        $content_raw = trim((string) ($data["content_html"] ?? ""));
    }
    if ($content_raw === "") {
        $content_raw = trim((string) ($data["body"] ?? ""));
    }
    $allowed_urls = upsellio_blog_bot_allowed_urls_map($catalog);
    $content_raw = upsellio_blog_bot_markdown_links_to_html($content_raw, $allowed_urls);
    $content_raw = upsellio_blog_bot_content_markdown_headings_to_html($content_raw);
    // Spis treści jest budowany w single.php (.sp-toc) — nie wstrzykuj ups-article-toc do treści (uniknięcie podwójnego TOC).
    // $content_raw = upsellio_blog_bot_prepend_toc_block($content_raw);
    $content_raw = upsellio_blog_bot_ensure_sbt_shortcodes($content_raw, $article_type);
    $pq_seo = trim((string) ($data["primary_query"] ?? ""));
    if ($pq_seo === "") {
        $pq_seo = $keyword;
    }
    $content_raw = upsellio_blog_bot_fix_seo_issues($content_raw, $pq_seo, $keyword);
    $content = wp_kses_post($content_raw);

    $slug = sanitize_title((string) ($data["slug"] ?? ""));
    if ($slug === "" && $title !== "") {
        $slug = sanitize_title($title);
    }

    $meta = sanitize_text_field((string) ($data["meta_description"] ?? ""));
    if ($meta === "") {
        $meta = sanitize_text_field((string) ($data["seo_description"] ?? ""));
    }
    $tags = isset($data["tags"]) && is_array($data["tags"])
        ? array_map("sanitize_text_field", $data["tags"])
        : [];

    if ($title === "" || $content === "") {
        upsellio_blog_bot_set_last_error([
            "code" => "empty_fields",
            "detail" => "Model musi zwrócić JSON z polami title oraz content (alternatywnie: content_html / body).",
        ]);

        return;
    }

    $cat_id = (int) get_option("ups_blog_bot_category", 0);

    $post_id = wp_insert_post([
        "post_title" => $title,
        "post_content" => $content,
        "post_status" => "draft",
        "post_type" => "post",
        "post_name" => $slug,
        "post_author" => (int) get_option("ups_blog_bot_post_author", 1),
    ], true);

    if (is_wp_error($post_id) || (int) $post_id <= 0) {
        $err_msg = is_wp_error($post_id) ? $post_id->get_error_message() : "wp_insert_post zwrócił 0.";
        upsellio_blog_bot_set_last_error([
            "code" => "wp_insert_failed",
            "detail" => $err_msg,
        ]);

        return;
    }
    $post_id = (int) $post_id;

    upsellio_blog_bot_assign_featured_image($post_id, $keyword);

    $plain = wp_strip_all_tags((string) $content);
    $word_count = $plain !== ""
        ? count(preg_split("/\s+/u", trim($plain), -1, PREG_SPLIT_NO_EMPTY))
        : 0;
    $has_h2 = (substr_count((string) $content, "<h2") + substr_count((string) $content, "<h3")) >= 2;
    $has_faq = stripos((string) $content, "FAQ") !== false
        || stripos((string) $content, "często zadawane") !== false
        || stripos((string) $content, "CZĘSTO ZADAWANE") !== false;
    $target_len = max(400, (int) get_option("ups_blog_bot_target_length", 1200));
    $min_words = (int) max(350, (int) round($target_len * 0.36));
    $quality_notes = [];
    if ($word_count < $min_words) {
        $quality_notes[] = "Krótki: " . (string) $word_count . " słów (min. " . (string) $min_words . ")";
    }
    if (!$has_h2) {
        $quality_notes[] = "Brak min. 2 nagłówków H2/H3";
    }
    if (!$has_faq) {
        $quality_notes[] = "Brak sekcji FAQ";
    }
    if ($quality_notes !== []) {
        update_post_meta($post_id, "_ups_blog_bot_quality_notes", implode("; ", $quality_notes));
    }

    upsellio_blog_bot_shift_queue_and_archive($keyword);

    if ($cat_id > 0) {
        wp_set_post_categories($post_id, [$cat_id], false);
    }

    if ($tags !== []) {
        wp_set_post_tags($post_id, $tags, false);
    }

    upsellio_blog_bot_save_sbt_meta($post_id, $data, $title, $meta, $keyword);

    $related_ids = [];
    foreach ($catalog as $row) {
        $rid = (int) ($row["id"] ?? 0);
        if ($rid <= 0 || $rid === $post_id) {
            continue;
        }
        $related_ids[] = $rid;
        if (count($related_ids) >= 8) {
            break;
        }
    }
    if ($related_ids !== []) {
        update_post_meta($post_id, "_upsellio_related_post_ids", $related_ids);
    }

    update_post_meta($post_id, "_ups_blog_bot_keyword", $keyword);
    update_post_meta($post_id, "_ups_blog_bot_generated_at", current_time("mysql"));

    update_option("ups_blog_bot_last_run", current_time("mysql"), false);
    update_option("ups_blog_bot_last_draft_id", $post_id, false);
    upsellio_blog_bot_set_last_error(null);

    $notify = (string) get_option("ups_blog_bot_notify_email", "");
    if (is_email($notify)) {
        $edit_url = (string) get_edit_post_link($post_id, "raw");
        $preview = (string) get_preview_post_link($post_id);
        wp_mail(
            $notify,
            "Blog Bot: nowy draft — " . $title,
            "Wygenerowano draft dla frazy: {$keyword}\n\nEdycja: {$edit_url}\nPodgląd: {$preview}"
        );
    }
}

add_action("upsellio_blog_bot_cron_run", "upsellio_blog_bot_generate_and_save");

add_filter("cron_schedules", static function (array $schedules): array {
    $schedules["biweekly"] = [
        "interval" => 302400,
        "display" => __("Dwa razy w tygodniu", "upsellio"),
    ];
    if (!isset($schedules["weekly"])) {
        $schedules["weekly"] = [
            "interval" => WEEK_IN_SECONDS,
            "display" => __("Raz w tygodniu", "upsellio"),
        ];
    }
    if (!isset($schedules["monthly"])) {
        $schedules["monthly"] = [
            "interval" => 30 * DAY_IN_SECONDS,
            "display" => __("Raz w miesiącu", "upsellio"),
        ];
    }

    return $schedules;
});

add_action("init", static function (): void {
    if ((string) get_option("ups_blog_bot_enabled", "0") !== "1") {
        return;
    }
    if (!wp_next_scheduled("upsellio_blog_bot_cron_run")) {
        upsellio_blog_bot_ensure_cron();
    }
}, 40);
