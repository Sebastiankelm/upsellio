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
        if ($page instanceof WP_Post) {
            $text = wp_strip_all_tags((string) $page->post_content);
            if (function_exists("mb_substr")) {
                $text = mb_substr($text, 0, 600, "UTF-8");
            } else {
                $text = substr($text, 0, 600);
            }
            $parts[] = get_the_title((int) $page->ID) . ":\n" . $text;
        }
    }

    return implode("\n\n", $parts);
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

    $page_ids = get_posts([
        "post_type" => "page",
        "post_status" => "publish",
        "posts_per_page" => 100,
        "orderby" => "menu_order title",
        "order" => "ASC",
        "no_found_rows" => true,
        "fields" => "ids",
    ]);
    $post_ids = get_posts([
        "post_type" => "post",
        "post_status" => "publish",
        "posts_per_page" => 100,
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
        $title = get_the_title($pid);
        if ($title === "") {
            continue;
        }
        $url = get_permalink($pid);
        if (!is_string($url) || $url === "") {
            continue;
        }
        $url = esc_url_raw($url);
        $tlow = function_exists("mb_strtolower") ? mb_strtolower($title, "UTF-8") : strtolower($title);
        $path = (string) (wp_parse_url($url, PHP_URL_PATH) ?? "");
        $plow = function_exists("mb_strtolower") ? mb_strtolower($path, "UTF-8") : strtolower($path);
        $hay = $tlow . " " . $plow;
        $score = 0;
        foreach ($needles as $w) {
            if ($w !== "" && strpos($hay, $w) !== false) {
                $score += 3;
            }
        }
        if ($kw_low !== "" && strpos($hay, $kw_low) !== false) {
            $score += 10;
        }
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
 * SEO Tool: meta description 140–160 znaków.
 */
function upsellio_blog_bot_clamp_meta_description(string $meta, string $title, string $primary_query): string
{
    $meta = trim($meta);
    if ($meta === "") {
        if ($primary_query !== "") {
            $base = "Praktyczny poradnik: " . $primary_query . ". Konkretne kroki i przykłady dla firm B2B.";
            $meta = upsellio_blog_bot_mb_len($base) <= 160
                ? $base
                : upsellio_blog_bot_mb_cut($base, 157) . "...";
        } else {
            $meta = "Poradnik B2B — konkretne metody, przykłady wdrożeń i checklista dla marketerów i właścicieli firm.";
        }
    }
    $len = upsellio_blog_bot_mb_len($meta);
    if ($len >= 140 && $len <= 160) {
        return $meta;
    }
    if ($len > 160) {
        return upsellio_blog_bot_mb_cut($meta, 160);
    }
    $tail = $primary_query !== ""
        ? " Sprawdź jak wdrożyć i jakich błędów unikać — praktyczny przewodnik dla firm B2B."
        : " Konkretne metody i przykłady wdrożeń dla marketerów i właścicieli firm B2B.";
    while (upsellio_blog_bot_mb_len($meta) < 140) {
        $meta .= $tail;
        if (upsellio_blog_bot_mb_len($meta) > 300) {
            break;
        }
    }

    return upsellio_blog_bot_mb_cut($meta, 160);
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
 */
function upsellio_blog_bot_build_prompt(string $keyword, ?array $catalog = null): string
{
    if ($catalog === null) {
        $catalog = upsellio_blog_bot_catalog_for_keyword($keyword, 48);
    }
    $catalog_block = upsellio_blog_bot_format_catalog_for_prompt($catalog);

    $prompt_template = (string) get_option("ups_ai_prompt_blog_post", "");
    $target_length = max(600, (int) get_option("ups_blog_bot_target_length", 1200));
    $posts_ctx = upsellio_blog_bot_get_posts_context(10);
    $services_ctx = upsellio_blog_bot_get_services_context();
    $tone = "partnerski, konkretny, B2B (PL)";
    $converting_kw = upsellio_blog_bot_get_converting_keywords(8);

    if ($prompt_template === "") {
        $prompt_template = "Napisz artykuł blogowy B2B (PL) na temat: {keyword}.\n"
            . "Długość: ok. {target_length} słów.\n"
            . "Ton: {tone}.\n"
            . "Istniejące wpisy (unikaj duplikowania tematów):\n{existing_posts}\n\n"
            . "Frazy które już przynoszą kliknięcia z Google (nie powielaj tych tematów; możesz je rozwinąć lub powiązać):\n{converting_keywords}\n\n"
            . "Kontekst usług firmy:\n{services_context}\n\n"
            . "KATALOG LINKOWANIA WEWNĘTRZNEGO (tylko te URL — jedna linia = URL | tytuł):\n{internal_url_catalog}\n\n"
            . "WYMAGANIA TECHNICZNE (WordPress, Rank Math SEO, wewnętrzny SEO tool):\n"
            . "- Pole \"content\": wyłącznie HTML (<p>, <ul>/<li>, <h2>, <h3>). Bez Markdown nagłówków (#, ##, ###).\n"
            . "- W pierwszym akapicie naturalnie użyj głównej frazy (primary_query / temat).\n"
            . "- W treści umieść 2–5 linków wewnętrznych w formacie Markdown [krótki anchor](PEŁNY_URL) — URL MUSI być identyczny z jednej linii katalogu powyżej (skopiuj 1:1). Wstaw linki w miejscach merytorycznych (nie na końcu w jednym bloku).\n"
            . "- Sekcja FAQ: <h2>FAQ</h2> + co najmniej jedno <h3> z pytaniem.\n"
            . "- article_type: zwykle \"seo_article\".\n"
            . "- meta_description: 140–160 znaków, z frazą związaną z {keyword} (dobry snippet pod Rank Math).\n"
            . "- seo_title: 45–60 znaków MAX (CMS dodaje nazwę witryny; może być zbliżony do title).\n"
            . "- primary_query: główna fraza SEO (Rank Math focus keyword).\n"
            . "- query_cluster: 5–12 powiązanych fraz oddzielonych przecinkami.\n"
            . "- user_questions: 3 pytania czytelnika, każde w osobnej linii (\\n).\n"
            . "- problem i outcome: każde minimum 90 znaków — konkretny błąd mentalny / procesowy i oczekiwany efekt po lekturze.\n"
            . "- tags: min. 3 krótkie tagi.\n\n"
            . "Spis treści (H2/H3) zostanie dodany automatycznie po stronie serwera — nie wstawiaj własnego spisu w treści.\n\n"
            . "Odpowiedz TYLKO jednym obiektem JSON (bez markdown poza linkami [tekst](url) WEWNĄTRZ pola \"content\", bez ```), ze stringami / tablicą zgodnie z polami: "
            . "title, slug, seo_title, meta_description, primary_query, query_cluster, user_questions, article_type, "
            . "audience, search_intent, problem, outcome, cta_text, tags (tablica stringów), content (HTML + linki markdown [anchor](url) z katalogu).";
    }

    $prompt = str_replace(
        [
            "{keyword}",
            "{target_length}",
            "{existing_posts}",
            "{services_context}",
            "{tone}",
            "{converting_keywords}",
            "{internal_url_catalog}",
        ],
        [
            (string) $keyword,
            (string) $target_length,
            $posts_ctx,
            $services_ctx,
            $tone,
            $converting_kw,
            $catalog_block,
        ],
        $prompt_template
    );

    if (strpos($prompt_template, "{internal_url_catalog}") === false && trim($prompt_template) !== "") {
        $prompt .= "\n\n---\nINTERNAL_URL_CATALOG — używaj WYŁĄCZNIE tych adresów w linkach [tekst](url) w polu content (2–5 linków), kopiuj URL 1:1:\n"
            . $catalog_block;
    }

    if (function_exists("upsellio_ai_master_context")) {
        $master_blog = upsellio_ai_master_context("blog");
        if ($master_blog !== "") {
            $prompt .= "\n\n---\nDane o skuteczności Twojego bloga (priorytety treści — co generuje leady vs martwy ruch):\n" . $master_blog;
        }
    }

    $prefix = upsellio_blog_bot_company_prefix();
    if ($prefix !== "") {
        $prompt = $prefix . $prompt;
    }

    return $prompt;
}

/**
 * @return array{cached: string, dynamic: string}|null
 */
function upsellio_blog_bot_prompt_cache_split(string $full_prompt): ?array
{
    $prefix = upsellio_blog_bot_company_prefix();
    if ($prefix === "") {
        return null;
    }
    if (strpos($full_prompt, $prefix) !== 0) {
        return null;
    }
    $plen = function_exists("mb_strlen") ? (int) mb_strlen($prefix, "UTF-8") : strlen($prefix);
    $dynamic = function_exists("mb_substr")
        ? mb_substr($full_prompt, $plen, null, "UTF-8")
        : substr($full_prompt, $plen);
    $dynamic = trim((string) $dynamic);
    if ($dynamic === "") {
        return null;
    }

    return ["cached" => $prefix, "dynamic" => $dynamic];
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

    $model = trim((string) get_option("ups_blog_bot_model", ""));
    if ($model === "") {
        $model = "claude-haiku-4-5-20251001";
    }

    $catalog = upsellio_blog_bot_catalog_for_keyword($keyword, 48);
    $full_prompt = upsellio_blog_bot_build_prompt($keyword, $catalog);
    $cache_split = upsellio_blog_bot_prompt_cache_split($full_prompt);
    $raw = upsellio_anthropic_crm_send_user_prompt($full_prompt, 3000, 90, $model, $cache_split);
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
    if (!is_array($data)) {
        $snippet = function_exists("mb_substr") ? mb_substr(trim((string) $raw), 0, 400, "UTF-8") : substr(trim((string) $raw), 0, 400);
        upsellio_blog_bot_set_last_error([
            "code" => "bad_json",
            "detail" => "Nie udało się wyciągnąć obiektu JSON z odpowiedzi. Początek: " . $snippet,
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

    $plain = wp_strip_all_tags((string) $content);
    $word_count = $plain !== ""
        ? count(preg_split("/\s+/u", trim($plain), -1, PREG_SPLIT_NO_EMPTY))
        : 0;
    $has_h2 = (substr_count((string) $content, "<h2") + substr_count((string) $content, "<h3")) >= 2;
    $has_faq = stripos((string) $content, "FAQ") !== false
        || stripos((string) $content, "często zadawane") !== false
        || stripos((string) $content, "CZĘSTO ZADAWANE") !== false;
    $target_len = max(400, (int) get_option("ups_blog_bot_target_length", 1200));
    $min_words = (int) max(400, (int) round($target_len * 0.4));
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
