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
    $company = trim((string) get_option("ups_ai_company_context", ""));
    if ($company === "") {
        $company = trim((string) get_option("ups_anthropic_company_context", ""));
    }
    if ($company === "") {
        return "";
    }

    return "Kontekst firmy:\n" . $company . "\n\n";
}

function upsellio_blog_bot_build_prompt(string $keyword): string
{
    $prompt_template = (string) get_option("ups_ai_prompt_blog_post", "");
    $target_length = max(600, (int) get_option("ups_blog_bot_target_length", 1200));
    $posts_ctx = upsellio_blog_bot_get_posts_context(10);
    $services_ctx = upsellio_blog_bot_get_services_context();
    $tone = "partnerski, konkretny, B2B (PL)";

    if ($prompt_template === "") {
        $prompt_template = "Napisz artykuł blogowy na temat: {keyword}.\n"
            . "Długość: ok. {target_length} słów.\n"
            . "Ton: {tone}.\n"
            . "Istniejące wpisy (unikaj duplikowania tematów):\n{existing_posts}\n\n"
            . "Kontekst usług firmy:\n{services_context}\n\n"
            . "Odpowiedz TYLKO jednym obiektem JSON (bez markdown), dokładnie w formacie:\n"
            . '{"title":"...","slug":"...","meta_description":"...","tags":["..."],"content":"..."}';
    }

    $prompt = str_replace(
        ["{keyword}", "{target_length}", "{existing_posts}", "{services_context}", "{tone}"],
        [(string) $keyword, (string) $target_length, $posts_ctx, $services_ctx, $tone],
        $prompt_template
    );

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
    if ((string) get_option("ups_blog_bot_enabled", "0") !== "1") {
        return;
    }
    if (!function_exists("upsellio_anthropic_crm_api_key") || upsellio_anthropic_crm_api_key() === "") {
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

        return;
    }

    $model = trim((string) get_option("ups_blog_bot_model", ""));
    if ($model === "") {
        $model = "claude-haiku-4-5-20251001";
    }

    $full_prompt = upsellio_blog_bot_build_prompt($keyword);
    $cache_split = upsellio_blog_bot_prompt_cache_split($full_prompt);
    $raw = upsellio_anthropic_crm_send_user_prompt($full_prompt, 3000, 90, $model, $cache_split);
    if ($raw === null) {
        return;
    }

    $data = upsellio_anthropic_crm_parse_json_object($raw);
    if (!is_array($data)) {
        return;
    }

    $title = sanitize_text_field((string) ($data["title"] ?? ""));
    $content = wp_kses_post((string) ($data["content"] ?? ""));
    $slug = sanitize_title((string) ($data["slug"] ?? $title));
    $meta = sanitize_text_field((string) ($data["meta_description"] ?? ""));
    $tags = isset($data["tags"]) && is_array($data["tags"])
        ? array_map("sanitize_text_field", $data["tags"])
        : [];

    if ($title === "" || $content === "") {
        return;
    }

    $cat_id = (int) get_option("ups_blog_bot_category", 0);

    $post_id = wp_insert_post([
        "post_title" => $title,
        "post_content" => $content,
        "post_status" => "draft",
        "post_type" => "post",
        "post_name" => $slug,
        "post_author" => 1,
    ], true);

    if (is_wp_error($post_id) || (int) $post_id <= 0) {
        return;
    }
    $post_id = (int) $post_id;

    upsellio_blog_bot_shift_queue_and_archive($keyword);

    if ($cat_id > 0) {
        wp_set_post_categories($post_id, [$cat_id], false);
    }

    if ($tags !== []) {
        wp_set_post_tags($post_id, $tags, false);
    }

    if ($meta !== "") {
        update_post_meta($post_id, "_yoast_wpseo_metadesc", $meta);
        update_post_meta($post_id, "rank_math_description", $meta);
        update_post_meta($post_id, "_upsellio_meta_description", $meta);
    }
    update_post_meta($post_id, "_ups_blog_bot_keyword", $keyword);
    update_post_meta($post_id, "_ups_blog_bot_generated_at", current_time("mysql"));

    update_option("ups_blog_bot_last_run", current_time("mysql"), false);
    update_option("ups_blog_bot_last_draft_id", $post_id, false);

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
