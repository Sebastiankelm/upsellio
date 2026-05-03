<?php
if (!defined("ABSPATH")) {
    exit;
}

function upsellio_get_related_post_ids($post_id, $limit = 3)
{
    $limit = max(1, (int) $limit);
    $post_id = (int) $post_id;

    $post_tags = wp_get_post_tags($post_id, ["fields" => "ids"]);
    $post_categories = wp_get_post_categories($post_id, ["fields" => "ids"]);
    $post_title = get_the_title($post_id);
    $keywords = array_filter(array_map("trim", explode(" ", (string) $post_title)));

    $query_args = [
        "post_type" => "post",
        "post_status" => "publish",
        "posts_per_page" => $limit,
        "post__not_in" => [$post_id],
    ];

    if (!empty($post_tags)) {
        $query_args["tag__in"] = $post_tags;
    }

    if (!empty($post_categories)) {
        $query_args["category__in"] = $post_categories;
    }

    if (count($keywords) >= 2) {
        $query_args["s"] = implode(" ", array_slice($keywords, 0, 5));
    }

    $related_query = new WP_Query($query_args);
    $related_ids = wp_list_pluck($related_query->posts, "ID");

    wp_reset_postdata();

    return array_slice(array_map("intval", $related_ids), 0, $limit);
}

function upsellio_render_internal_links_html($post_id, $limit = 3, $title = "Powiązane artykuły")
{
    $post_id = (int) $post_id;
    if ($post_id <= 0) {
        return "";
    }

    $stored_ids = get_post_meta($post_id, "_upsellio_related_post_ids", true);
    $related_ids = [];

    if (is_array($stored_ids)) {
        $related_ids = array_map("intval", $stored_ids);
    }

    if (empty($related_ids)) {
        $related_ids = upsellio_get_related_post_ids($post_id, $limit);
        if (!empty($related_ids)) {
            update_post_meta($post_id, "_upsellio_related_post_ids", $related_ids);
        }
    }

    if (empty($related_ids)) {
        return "";
    }

    $posts = get_posts([
        "post_type" => ["post", "page"],
        "post_status" => "publish",
        "post__in" => $related_ids,
        "orderby" => "post__in",
        "posts_per_page" => max(1, (int) $limit),
    ]);

    if (empty($posts)) {
        return "";
    }

    ob_start();
    ?>
    <section class="ups-inline-links" aria-label="<?php echo esc_attr($title); ?>">
      <h3><?php echo esc_html($title); ?></h3>
      <ul>
        <?php foreach ($posts as $related_post) : ?>
          <li>
            <a href="<?php echo esc_url(get_permalink($related_post->ID)); ?>">
              <?php echo esc_html(get_the_title($related_post->ID)); ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </section>
    <?php

    return ob_get_clean();
}

function upsellio_internal_links_shortcode($atts = [])
{
    $atts = shortcode_atts(
        [
            "limit" => 3,
            "title" => "Powiązane artykuły",
        ],
        $atts,
        "upsellio_internal_links"
    );

    $post_id = get_the_ID();
    if (!$post_id) {
        return "";
    }

    return upsellio_render_internal_links_html($post_id, (int) $atts["limit"], (string) $atts["title"]);
}
add_shortcode("upsellio_internal_links", "upsellio_internal_links_shortcode");

function upsellio_prepare_toc_content($raw_content)
{
    $raw_content = (string) $raw_content;
    if (trim($raw_content) === "") {
        return [
            "toc" => [],
            "content" => "",
        ];
    }

    libxml_use_internal_errors(true);

    $doc = new DOMDocument();
    $wrapped_html = '<?xml encoding="UTF-8"><!DOCTYPE html><html><body>' . $raw_content . "</body></html>";
    $doc->loadHTML($wrapped_html);
    $xpath = new DOMXPath($doc);
    $headings = $xpath->query("//h2 | //h3");

    $seen_ids = [];
    $toc = [];

    if ($headings instanceof DOMNodeList) {
        foreach ($headings as $heading) {
            $heading_text = trim((string) $heading->textContent);
            if ($heading_text === "") {
                continue;
            }

            $base_id = sanitize_title($heading_text);
            if ($base_id === "") {
                $base_id = "sekcja";
            }

            $unique_id = $base_id;
            $suffix = 2;
            while (in_array($unique_id, $seen_ids, true)) {
                $unique_id = "{$base_id}-{$suffix}";
                $suffix++;
            }
            $seen_ids[] = $unique_id;
            $heading->setAttribute("id", $unique_id);

            $toc[] = [
                "id" => $unique_id,
                "title" => $heading_text,
                "level" => strtolower($heading->nodeName),
            ];
        }
    }

    $body_nodes = $doc->getElementsByTagName("body")->item(0);
    $content = "";

    if ($body_nodes) {
        foreach ($body_nodes->childNodes as $child_node) {
            $content .= $doc->saveHTML($child_node);
        }
    }

    libxml_clear_errors();

    return [
        "toc" => $toc,
        "content" => $content,
    ];
}

function upsellio_blog_generator_menu()
{
    add_submenu_page(
        "edit.php",
        "Upsellio SEO Blog Tool",
        "SEO Blog Tool",
        "edit_posts",
        "upsellio-seo-blog-tool",
        "upsellio_render_blog_generator_screen",
        50
    );
}
add_action("admin_menu", "upsellio_blog_generator_menu");

function upsellio_blog_tool_add_post_row_action($actions, $post)
{
    if (!($post instanceof WP_Post) || $post->post_type !== "post") {
        return $actions;
    }

    $post_id = (int) $post->ID;
    if ($post_id <= 0 || !current_user_can("edit_post", $post_id)) {
        return $actions;
    }

    $sbt_quick_edit_url = add_query_arg(
        [
            "page" => "upsellio-seo-blog-tool",
            "quick_edit_post_id" => $post_id,
        ],
        admin_url("edit.php")
    );

    $sbt_action = '<a href="' . esc_url($sbt_quick_edit_url) . '">Edytuj SBT</a>';
    $updated_actions = [];
    $inserted = false;

    foreach ((array) $actions as $action_key => $action_markup) {
        $updated_actions[$action_key] = $action_markup;
        if ($action_key === "edit") {
            $updated_actions["upsellio_sbt_edit"] = $sbt_action;
            $inserted = true;
        }
    }

    if (!$inserted) {
        $updated_actions["upsellio_sbt_edit"] = $sbt_action;
    }

    return $updated_actions;
}
add_filter("post_row_actions", "upsellio_blog_tool_add_post_row_action", 10, 2);


/**
 * Parametry AI dla narzędzia SEO Blog Tool — wyłącznie z CRM (Ustawienia → AI).
 * Opcjonalnie odczyt ze starej opcji upsellio_blog_ai_settings, jeśli nowe pola CRM są puste.
 *
 * @return array{temperature: float, max_tokens: int, system_prompt: string, campaign_prompt_template: string}
 */
function upsellio_blog_tool_get_ai_settings()
{
    $legacy = get_option("upsellio_blog_ai_settings", []);
    if (!is_array($legacy)) {
        $legacy = [];
    }

    $def_system = "Tworzysz merytoryczne, praktyczne wpisy blogowe po polsku, gotowe do publikacji w WordPress.";
    $def_campaign = "Pisz w języku polskim, konkretnie i merytorycznie. Używaj przykładów, checklist i sekcji FAQ.";

    $system = trim((string) get_option("ups_ai_prompt_blog_seo_system", ""));
    if ($system === "" && isset($legacy["system_prompt"]) && trim((string) $legacy["system_prompt"]) !== "") {
        $system = sanitize_textarea_field((string) $legacy["system_prompt"]);
    }
    if ($system === "") {
        $system = $def_system;
    }

    $campaign = trim((string) get_option("ups_ai_blog_seo_campaign_default", ""));
    if ($campaign === "" && isset($legacy["campaign_prompt_template"]) && trim((string) $legacy["campaign_prompt_template"]) !== "") {
        $campaign = sanitize_textarea_field((string) $legacy["campaign_prompt_template"]);
    }
    if ($campaign === "") {
        $campaign = $def_campaign;
    }

    $temp_opt = get_option("ups_ai_blog_seo_temperature", null);
    if ($temp_opt === null || $temp_opt === "") {
        $temperature = isset($legacy["temperature"]) ? (float) $legacy["temperature"] : 0.7;
    } else {
        $temperature = (float) $temp_opt;
    }
    $temperature = max(0.0, min(1.2, $temperature));

    $max_opt = get_option("ups_ai_blog_seo_max_tokens", null);
    if ($max_opt === null || $max_opt === "") {
        $max_tokens = isset($legacy["max_tokens"]) ? (int) $legacy["max_tokens"] : 3500;
    } else {
        $max_tokens = (int) $max_opt;
    }
    $max_tokens = max(800, min(12000, $max_tokens));

    return [
        "temperature" => $temperature,
        "max_tokens" => $max_tokens,
        "system_prompt" => $system,
        "campaign_prompt_template" => $campaign,
    ];
}

/**
 * Chat dla generatora SEO — wyłącznie Anthropic CRM (jak blog bot).
 *
 * @return string|WP_Error
 */
function upsellio_blog_tool_ai_chat($ai_settings, $messages, $temperature = null, $max_tokens = null, $task_name = "blog")
{
    $temperature_value = $temperature === null ? (float) ($ai_settings["temperature"] ?? 0.7) : (float) $temperature;
    $max_tokens_value = $max_tokens === null ? (int) ($ai_settings["max_tokens"] ?? 3500) : (int) $max_tokens;
    $task_name = sanitize_key((string) $task_name);

    if (!in_array($task_name, ["topics", "blog"], true)) {
        return new WP_Error("upsellio_ai_task_unsupported", "Ten generator obsługuje tylko zadania topics/blog przez Claude (CRM).");
    }
    if (!function_exists("upsellio_anthropic_crm_send_user_prompt") || !function_exists("upsellio_anthropic_crm_api_key") || upsellio_anthropic_crm_api_key() === "") {
        return new WP_Error("upsellio_ai_crm_no_key", "Brak klucza Anthropic (CRM). Ustaw UPSELLIO_ANTHROPIC_API_KEY lub ups_anthropic_api_key.");
    }

    $model = trim((string) get_option("ups_blog_bot_model", ""));
    $model_override = $model !== "" ? $model : null;
    $parts = [];
    foreach ((array) $messages as $message) {
        $role = strtoupper(sanitize_key((string) ($message["role"] ?? "user")));
        $content = trim((string) ($message["content"] ?? ""));
        if ($content === "") {
            continue;
        }
        $parts[] = $role . ":\n" . $content;
    }
    $full_prompt = implode("\n\n", $parts);
    if ($full_prompt === "") {
        return new WP_Error("upsellio_ai_crm_empty_prompt", "Brak treści promptu dla Claude.");
    }
    $cache_split = function_exists("upsellio_blog_bot_prompt_cache_split")
        ? upsellio_blog_bot_prompt_cache_split($full_prompt)
        : null;
    $raw = upsellio_anthropic_crm_send_user_prompt($full_prompt, max(800, min(8192, $max_tokens_value)), 90, $model_override, $cache_split);
    if ($raw === null || trim($raw) === "") {
        $detail = function_exists("upsellio_anthropic_crm_get_last_send_error") ? trim(upsellio_anthropic_crm_get_last_send_error()) : "";
        $msg = "Brak odpowiedzi z Claude (CRM). Sprawdź klucz API Anthropic, model i limity.";
        if ($detail !== "") {
            $msg .= " " . $detail;
        }

        return new WP_Error("upsellio_ai_crm_failed", $msg);
    }

    return $raw;
}

function upsellio_blog_tool_extract_json_object($raw_content)
{
    $raw_content = trim((string) $raw_content);
    if ($raw_content === "") {
        return null;
    }

    $decoded = json_decode($raw_content, true);
    if (is_array($decoded)) {
        return $decoded;
    }

    $start = strpos($raw_content, "{");
    $end = strrpos($raw_content, "}");
    if ($start === false || $end === false || $end <= $start) {
        return null;
    }

    $json_candidate = substr($raw_content, $start, ($end - $start + 1));
    $decoded = json_decode((string) $json_candidate, true);
    return is_array($decoded) ? $decoded : null;
}

function upsellio_blog_tool_parse_topics($topics_raw)
{
    $topics_raw = (string) $topics_raw;
    $topics = preg_split("/[\r\n,;]+/", $topics_raw);
    if (!is_array($topics)) {
        return [];
    }

    $topics = array_values(array_filter(array_map("trim", $topics)));
    return array_slice($topics, 0, 40);
}

function upsellio_blog_tool_generate_topics_via_ai($ai_settings, $seed_topic, $count, $audience, $article_type, $extra_instructions)
{
    $seed_topic = trim((string) $seed_topic);
    $count = max(1, min(40, (int) $count));
    if ($seed_topic === "") {
        return new WP_Error("upsellio_ai_topics_missing_seed", "Brakuje tematu bazowego do wygenerowania listy tematów.");
    }

    $system_prompt = (string) ($ai_settings["system_prompt"] ?? "");
    $campaign_prompt = (string) ($ai_settings["campaign_prompt_template"] ?? "");
    $messages = [
        [
            "role" => "system",
            "content" => $system_prompt . " Zwracaj wyłącznie poprawny JSON.",
        ],
        [
            "role" => "user",
            "content" => "Wygeneruj " . $count . " unikalnych tematów wpisów blogowych po polsku. Zwróć JSON w formacie: {\"topics\":[\"...\"]}. " .
                "Kontekst: temat bazowy: " . $seed_topic . ". Grupa docelowa: " . $audience . ". Typ wpisu: " . $article_type . ". Prompt kampanii: " . $campaign_prompt . ". Dodatkowe wytyczne: " . $extra_instructions . ".",
        ],
    ];

    $response = upsellio_blog_tool_ai_chat($ai_settings, $messages, 0.6, 1400, "topics");
    if (is_wp_error($response)) {
        return $response;
    }

    $json = upsellio_blog_tool_extract_json_object($response);
    if (!is_array($json)) {
        return new WP_Error("upsellio_ai_topics_invalid_json", "AI nie zwróciło poprawnego JSON dla listy tematów.");
    }
    $topics = [];
    if (isset($json["topics"]) && is_array($json["topics"])) {
        foreach ($json["topics"] as $topic_item) {
            $topic_clean = sanitize_text_field((string) $topic_item);
            if ($topic_clean !== "") {
                $topics[] = $topic_clean;
            }
        }
    }

    $topics = array_values(array_unique($topics));
    return array_slice($topics, 0, $count);
}

function upsellio_blog_tool_generate_post_payload_via_ai($ai_settings, $topic, $context)
{
    $system_prompt = (string) ($ai_settings["system_prompt"] ?? "");
    $campaign_prompt = (string) ($ai_settings["campaign_prompt_template"] ?? "");
    $topic = sanitize_text_field((string) $topic);
    $context = is_array($context) ? $context : [];

    $prompt_context = [
        "topic" => $topic,
        "audience" => (string) ($context["audience"] ?? ""),
        "search_intent" => (string) ($context["search_intent"] ?? ""),
        "article_type" => (string) ($context["article_type"] ?? "seo_article"),
        "cta_text" => (string) ($context["cta_text"] ?? ""),
        "problem" => (string) ($context["problem"] ?? ""),
        "outcome" => (string) ($context["outcome"] ?? ""),
        "query_cluster" => (string) ($context["query_cluster"] ?? ""),
        "user_questions" => (string) ($context["user_questions"] ?? ""),
        "extra_instructions" => (string) ($context["ai_extra_instructions"] ?? ""),
    ];

    $messages = [
        [
            "role" => "system",
            "content" => $system_prompt . " Tworzysz wpisy gotowe do publikacji w WordPress. Zwracaj wyłącznie JSON.",
        ],
        [
            "role" => "user",
            "content" => "Wygeneruj wpis blogowy i zwróć TYLKO JSON z kluczami: " .
                "title, seo_title, seo_description, primary_query, query_cluster (array string), user_questions (array string), content_html, tags (array string). " .
                "Treść ma zawierać min. 900 słów, nagłówki H2/H3, sekcję FAQ i logiczne podsumowanie. " .
                "Prompt kampanii (stosuj konsekwentnie): " . $campaign_prompt . ". " .
                "Dane wejściowe: " . wp_json_encode($prompt_context),
        ],
    ];

    $response = upsellio_blog_tool_ai_chat($ai_settings, $messages, 0.7, null, "blog");
    if (is_wp_error($response)) {
        return $response;
    }

    $json = upsellio_blog_tool_extract_json_object($response);
    if (!is_array($json)) {
        return new WP_Error("upsellio_ai_invalid_json", "AI zwróciło niepoprawny JSON dla wpisu: " . $topic);
    }

    $title = sanitize_text_field((string) ($json["title"] ?? $topic));
    $seo_title = sanitize_text_field((string) ($json["seo_title"] ?? $title));
    $seo_description = sanitize_text_field((string) ($json["seo_description"] ?? ""));
    $primary_query = sanitize_text_field((string) ($json["primary_query"] ?? $topic));
    $content_html = wp_kses_post((string) ($json["content_html"] ?? ""));

    $query_cluster_arr = isset($json["query_cluster"]) && is_array($json["query_cluster"]) ? $json["query_cluster"] : [];
    $user_questions_arr = isset($json["user_questions"]) && is_array($json["user_questions"]) ? $json["user_questions"] : [];
    $tags_arr = isset($json["tags"]) && is_array($json["tags"]) ? $json["tags"] : [];

    $query_cluster_arr = array_values(array_filter(array_map("sanitize_text_field", $query_cluster_arr)));
    $user_questions_arr = array_values(array_filter(array_map("sanitize_text_field", $user_questions_arr)));
    $tags_arr = array_values(array_filter(array_map("sanitize_text_field", $tags_arr)));

    if ($content_html === "") {
        return new WP_Error("upsellio_ai_missing_content", "AI nie zwróciło treści dla wpisu: " . $topic);
    }

    return [
        "title" => $title !== "" ? $title : $topic,
        "seo_title" => $seo_title,
        "seo_description" => $seo_description,
        "primary_query" => $primary_query,
        "query_cluster" => implode(", ", array_slice($query_cluster_arr, 0, 14)),
        "user_questions" => implode("\n", array_slice($user_questions_arr, 0, 8)),
        "content_html" => $content_html,
        "tags" => array_slice($tags_arr, 0, 12),
    ];
}

/**
 * Claude (CRM) — uzupełnienie istniejącego wpisu tym samym schematem JSON co generator AI.
 *
 * @return array<string, mixed>|WP_Error
 */
function upsellio_blog_tool_run_claude_editor_fill(int $post_id, string $extra_user_notes = "")
{
    $post_id = max(1, $post_id);
    $post = get_post($post_id);
    if (!($post instanceof WP_Post) || $post->post_type !== "post") {
        return new WP_Error("upsellio_editor_invalid_post", "Nieprawidłowy wpis.");
    }
    if (!function_exists("upsellio_anthropic_crm_api_key") || upsellio_anthropic_crm_api_key() === "") {
        return new WP_Error("upsellio_editor_no_crm", "Brak klucza Anthropic (CRM / UPSELLIO_ANTHROPIC_API_KEY).");
    }

    $ai_settings = upsellio_blog_tool_get_ai_settings();
    $body = (string) $post->post_content;
    if (function_exists("mb_substr")) {
        $body = mb_substr($body, 0, 42000, "UTF-8");
    } else {
        $body = substr($body, 0, 42000);
    }

    $snapshot = [
        "current_title" => (string) get_the_title($post_id),
        "current_excerpt" => (string) $post->post_excerpt,
        "article_type" => (string) get_post_meta($post_id, "_upsellio_article_type", true),
        "primary_query_meta" => (string) get_post_meta($post_id, "_upsellio_primary_query", true),
        "query_cluster_meta" => (string) get_post_meta($post_id, "_upsellio_query_cluster", true),
        "user_questions_meta" => (string) get_post_meta($post_id, "_upsellio_user_questions", true),
        "yoast_title" => (string) get_post_meta($post_id, "_yoast_wpseo_title", true),
        "yoast_desc" => (string) get_post_meta($post_id, "_yoast_wpseo_metadesc", true),
        "focus_kw" => (string) get_post_meta($post_id, "_yoast_wpseo_focuskw", true),
        "post_content_html" => $body,
    ];
    if ($snapshot["article_type"] === "" || !in_array($snapshot["article_type"], ["blog_educational", "seo_article", "landing_sales"], true)) {
        $snapshot["article_type"] = "seo_article";
    }

    $system_prompt = (string) ($ai_settings["system_prompt"] ?? "");
    $campaign_prompt = (string) ($ai_settings["campaign_prompt_template"] ?? "");
    $notes = sanitize_textarea_field($extra_user_notes);

    $messages = [
        [
            "role" => "system",
            "content" => $system_prompt . " Tworzysz / aktualizujesz wpisy WordPress. Zwracaj wyłącznie jeden obiekt JSON (bez markdown).",
        ],
        [
            "role" => "user",
            "content" => "Przygotuj kompletny wpis blogowy (PL, B2B). Zwróć TYLKO JSON z kluczami: "
                . "title, seo_title, seo_description, primary_query, query_cluster (array string), user_questions (array string), "
                . "content_html, tags (array string), audience, search_intent, problem, outcome, cta_text, article_type "
                . "(jeden z: blog_educational, seo_article, landing_sales). "
                . "Treść HTML: min. 900 słów, H2/H3, FAQ, shortcode [upsellio_internal_links] i dla seo_article/landing_sales [upsellio_contact_form]. "
                . "Prompt kampanii: " . $campaign_prompt . ". "
                . ($notes !== "" ? "Notatki redaktora: " . $notes . "\n" : "")
                . "Bieżący stan wpisu (uzupełnij / popraw): " . wp_json_encode($snapshot),
        ],
    ];

    $response = upsellio_blog_tool_ai_chat($ai_settings, $messages, 0.65, null, "blog");
    if (is_wp_error($response)) {
        return $response;
    }

    $json = upsellio_blog_tool_extract_json_object($response);
    if (!is_array($json)) {
        return new WP_Error("upsellio_editor_bad_json", "Model nie zwrócił poprawnego JSON.");
    }

    $title = sanitize_text_field((string) ($json["title"] ?? $snapshot["current_title"]));
    $seo_title = sanitize_text_field((string) ($json["seo_title"] ?? ""));
    $seo_description = sanitize_text_field((string) ($json["seo_description"] ?? ""));
    $primary_query = sanitize_text_field((string) ($json["primary_query"] ?? ""));
    $content_html = wp_kses_post((string) ($json["content_html"] ?? ""));

    $query_cluster_arr = isset($json["query_cluster"]) && is_array($json["query_cluster"]) ? $json["query_cluster"] : [];
    $user_questions_arr = isset($json["user_questions"]) && is_array($json["user_questions"]) ? $json["user_questions"] : [];
    $tags_arr = isset($json["tags"]) && is_array($json["tags"]) ? $json["tags"] : [];

    $query_cluster_arr = array_values(array_filter(array_map("sanitize_text_field", $query_cluster_arr)));
    $user_questions_arr = array_values(array_filter(array_map("sanitize_text_field", $user_questions_arr)));
    $tags_arr = array_values(array_filter(array_map("sanitize_text_field", $tags_arr)));

    $article_type = sanitize_key((string) ($json["article_type"] ?? $snapshot["article_type"]));
    if (!in_array($article_type, ["blog_educational", "seo_article", "landing_sales"], true)) {
        $article_type = (string) $snapshot["article_type"];
    }

    if ($content_html === "") {
        return new WP_Error("upsellio_editor_empty_content", "Model nie zwrócił treści HTML.");
    }

    return [
        "title" => $title !== "" ? $title : $snapshot["current_title"],
        "seo_title" => $seo_title,
        "seo_description" => $seo_description,
        "primary_query" => $primary_query,
        "query_cluster" => implode(", ", array_slice($query_cluster_arr, 0, 14)),
        "user_questions" => implode("\n", array_slice($user_questions_arr, 0, 8)),
        "content_html" => $content_html,
        "tags" => array_slice($tags_arr, 0, 12),
        "article_type" => $article_type,
        "audience" => sanitize_text_field((string) ($json["audience"] ?? "")),
        "search_intent" => sanitize_text_field((string) ($json["search_intent"] ?? "")),
        "problem" => sanitize_textarea_field((string) ($json["problem"] ?? "")),
        "outcome" => sanitize_textarea_field((string) ($json["outcome"] ?? "")),
        "cta_text" => sanitize_text_field((string) ($json["cta_text"] ?? "")),
    ];
}

function upsellio_blog_tool_get_batch_publish_date($schedule_mode, $publish_at_raw, $interval_value, $interval_unit, $index)
{
    $schedule_mode = sanitize_key((string) $schedule_mode);
    $publish_at_raw = sanitize_text_field((string) $publish_at_raw);
    $interval_value = max(1, (int) $interval_value);
    $interval_unit = in_array($interval_unit, ["minutes", "hours", "days"], true) ? $interval_unit : "days";
    $index = max(0, (int) $index);

    if ($publish_at_raw === "") {
        return null;
    }

    $site_timezone = wp_timezone();
    $base_dt = DateTimeImmutable::createFromFormat("Y-m-d\\TH:i", $publish_at_raw, $site_timezone);
    if (!$base_dt instanceof DateTimeImmutable) {
        return null;
    }

    if ($schedule_mode === "interval") {
        $unit_map = [
            "minutes" => "minutes",
            "hours" => "hours",
            "days" => "days",
        ];
        $unit = (string) ($unit_map[$interval_unit] ?? "days");
        $step = $interval_value * $index;
        return $base_dt->modify("+" . $step . " " . $unit);
    }

    if ($schedule_mode === "at_time") {
        return $base_dt->modify("+" . $index . " minutes");
    }

    return null;
}

function upsellio_generate_default_post_content($headline, $primary_query, $query_cluster, $user_questions, $audience, $intent, $problem, $outcome, $cta_text, $article_type)
{
    $headline = trim((string) $headline);
    $primary_query = trim((string) $primary_query);
    $query_cluster = array_values(array_filter(array_map("trim", preg_split("/[\r\n,;]+/", (string) $query_cluster) ?: [])));
    $user_questions = array_values(array_filter(array_map("trim", preg_split("/[\r\n]+/", (string) $user_questions) ?: [])));
    $audience = trim((string) $audience);
    $intent = trim((string) $intent);
    $problem = trim((string) $problem);
    $outcome = trim((string) $outcome);
    $cta_text = trim((string) $cta_text);
    $article_type = sanitize_key((string) $article_type);

    $intro = "W tym artykule pokażę, jak podejść do tematu '{$primary_query}' w sposób praktyczny i decyzjny.";
    if ($audience !== "") {
        $intro .= " Materiał jest przygotowany szczególnie dla: {$audience}.";
    }

    $query_cluster_list = "";
    if (!empty($query_cluster)) {
        $cluster_items = array_map(
            static function ($query) {
                return "<li>" . esc_html($query) . "</li>";
            },
            array_slice($query_cluster, 0, 10)
        );
        $query_cluster_list = "<h2>Powiązane zapytania i konteksty</h2><ul>" . implode("", $cluster_items) . "</ul>";
    }

    $faq_section = "";
    if (!empty($user_questions)) {
        $faq_items = [];
        foreach (array_slice($user_questions, 0, 6) as $question) {
            $faq_items[] = "<h3>" . esc_html($question) . "</h3><p>Uzupełnij odpowiedź opartą na danych i doświadczeniu.</p>";
        }
        $faq_section = "<h2>FAQ</h2>" . implode("", $faq_items);
    }

    $contact_form_shortcode = "";
    if (in_array($article_type, ["seo_article", "landing_sales"], true)) {
        $contact_form_shortcode = "[upsellio_contact_form]";
    }

    return implode(
        "\n\n",
        [
            "<p>{$intro}</p>",
            "<h2>Dlaczego temat jest ważny biznesowo</h2>",
            "<p>{$problem}</p>",
            "<h2>Najczęstsze błędy i ich konsekwencje</h2>",
            "<p>Większość firm skupia się na objawach, zamiast diagnozować źródło problemu. To prowadzi do przepalania budżetu i błędnych decyzji.</p>",
            "<h2>Proces krok po kroku</h2>",
            "<h3>Krok 1: Audyt stanu obecnego</h3>",
            "<p>Sprawdź dane, jakość leadów, spójność komunikacji i skuteczność strony docelowej.</p>",
            "<h3>Krok 2: Priorytetyzacja działań</h3>",
            "<p>Wybierz 2-3 poprawki, które dają największy wpływ na wynik i wdrażaj je sekwencyjnie.</p>",
            "<h3>Krok 3: Weryfikacja efektów</h3>",
            "<p>Porównuj wyniki biznesowe, a nie tylko metryki panelowe.</p>",
            "<h2>Co wdrożyć od razu</h2>",
            "<ul><li>Jasne KPI biznesowe.</li><li>Spójny komunikat reklama → landing page.</li><li>Cykl tygodniowej analizy jakości leadów.</li></ul>",
            $query_cluster_list,
            $faq_section,
            "<h2>Podsumowanie</h2>",
            "<p>{$outcome}</p>",
            "<p><strong>Intencja artykułu:</strong> {$intent}</p>",
            "<p><strong>Następny krok:</strong> {$cta_text}</p>",
            "[upsellio_internal_links limit=\"3\" title=\"Warto przeczytać także\"]",
            $contact_form_shortcode,
        ]
    );
}

/**
 * Zapisuje wynik generatora (tablica jak z upsellio_blog_tool_generate_post_payload_via_ai) do istniejącego wpisu.
 *
 * @param array<string, mixed> $ai_payload
 */
function upsellio_blog_tool_apply_ai_payload_to_post(int $post_id, array $ai_payload): void
{
    $post_id = max(1, $post_id);
    $title = sanitize_text_field((string) ($ai_payload["title"] ?? ""));
    $content_html = wp_kses_post((string) ($ai_payload["content_html"] ?? ""));
    $excerpt = sanitize_textarea_field((string) ($ai_payload["seo_description"] ?? ""));

    $update = ["ID" => $post_id];
    if ($title !== "") {
        $update["post_title"] = $title;
    }
    if ($content_html !== "") {
        $update["post_content"] = $content_html;
    }
    if ($excerpt !== "") {
        $update["post_excerpt"] = $excerpt;
    }
    if (count($update) > 1) {
        wp_update_post($update);
    }

    $tags = isset($ai_payload["tags"]) && is_array($ai_payload["tags"]) ? $ai_payload["tags"] : [];
    $tags = array_values(array_filter(array_map("sanitize_text_field", $tags)));
    if ($tags !== []) {
        wp_set_post_tags($post_id, $tags, false);
    }

    $article_type = (string) ($ai_payload["article_type"] ?? "");
    if ($article_type === "" || !in_array($article_type, ["blog_educational", "seo_article", "landing_sales"], true)) {
        $article_type = (string) get_post_meta($post_id, "_upsellio_article_type", true);
    }
    if ($article_type === "" || !in_array($article_type, ["blog_educational", "seo_article", "landing_sales"], true)) {
        $article_type = "seo_article";
    }

    upsellio_save_seo_meta_for_post(
        $post_id,
        (string) ($ai_payload["seo_title"] ?? ""),
        (string) ($ai_payload["seo_description"] ?? ""),
        (string) ($ai_payload["primary_query"] ?? ""),
        (string) ($ai_payload["query_cluster"] ?? ""),
        (string) ($ai_payload["user_questions"] ?? ""),
        $article_type
    );

    $optional = [
        "_upsellio_audience" => (string) ($ai_payload["audience"] ?? ""),
        "_upsellio_search_intent" => (string) ($ai_payload["search_intent"] ?? ""),
        "_upsellio_problem" => (string) ($ai_payload["problem"] ?? ""),
        "_upsellio_outcome" => (string) ($ai_payload["outcome"] ?? ""),
        "_upsellio_cta_text" => (string) ($ai_payload["cta_text"] ?? ""),
    ];
    foreach ($optional as $meta_key => $meta_val) {
        $meta_val = is_string($meta_val) ? trim($meta_val) : "";
        if ($meta_val !== "") {
            update_post_meta($post_id, $meta_key, $meta_val);
        }
    }
}

function upsellio_save_seo_meta_for_post($post_id, $seo_title, $seo_description, $primary_query, $query_cluster, $user_questions, $article_type)
{
    $post_id = (int) $post_id;
    $seo_title = trim((string) $seo_title);
    $seo_description = trim((string) $seo_description);
    $primary_query = trim((string) $primary_query);
    $query_cluster = trim((string) $query_cluster);
    $user_questions = trim((string) $user_questions);
    $article_type = sanitize_key((string) $article_type);

    if ($seo_title !== "") {
        update_post_meta($post_id, "_yoast_wpseo_title", $seo_title);
        update_post_meta($post_id, "rank_math_title", $seo_title);
    }
    if ($seo_description !== "") {
        update_post_meta($post_id, "_yoast_wpseo_metadesc", $seo_description);
        update_post_meta($post_id, "rank_math_description", $seo_description);
    }
    if ($primary_query !== "") {
        update_post_meta($post_id, "_yoast_wpseo_focuskw", $primary_query);
        update_post_meta($post_id, "rank_math_focus_keyword", $primary_query);
    }
    update_post_meta($post_id, "_upsellio_primary_query", $primary_query);
    update_post_meta($post_id, "_upsellio_query_cluster", $query_cluster);
    update_post_meta($post_id, "_upsellio_user_questions", $user_questions);
    update_post_meta($post_id, "_upsellio_article_type", $article_type !== "" ? $article_type : "seo_article");
}

function upsellio_handle_blog_generator_submit()
{
    if (!isset($_POST["upsellio_blog_generator_submit"])) {
        return;
    }

    if (!current_user_can("edit_posts")) {
        return;
    }

    check_admin_referer("upsellio_blog_generator_action", "upsellio_blog_generator_nonce");

    $edit_post_id = isset($_POST["edit_post_id"]) ? (int) $_POST["edit_post_id"] : 0;
    $title = isset($_POST["post_title"]) ? sanitize_text_field(wp_unslash($_POST["post_title"])) : "";
    $primary_query = isset($_POST["primary_query"]) ? sanitize_text_field(wp_unslash($_POST["primary_query"])) : "";
    if ($primary_query === "" && isset($_POST["focus_keyword"])) {
        $primary_query = sanitize_text_field(wp_unslash($_POST["focus_keyword"]));
    }
    $query_cluster = isset($_POST["query_cluster"]) ? sanitize_textarea_field(wp_unslash($_POST["query_cluster"])) : "";
    $user_questions = isset($_POST["user_questions"]) ? sanitize_textarea_field(wp_unslash($_POST["user_questions"])) : "";
    $article_type = isset($_POST["article_type"]) ? sanitize_key(wp_unslash($_POST["article_type"])) : "seo_article";
    $article_type = in_array($article_type, ["blog_educational", "seo_article", "landing_sales"], true) ? $article_type : "seo_article";
    $seo_title = isset($_POST["seo_title"]) ? sanitize_text_field(wp_unslash($_POST["seo_title"])) : $title;
    $seo_description = isset($_POST["seo_description"]) ? sanitize_textarea_field(wp_unslash($_POST["seo_description"])) : "";
    $intent = isset($_POST["search_intent"]) ? sanitize_text_field(wp_unslash($_POST["search_intent"])) : "";
    $audience = isset($_POST["audience"]) ? sanitize_text_field(wp_unslash($_POST["audience"])) : "";
    $problem = isset($_POST["problem"]) ? sanitize_textarea_field(wp_unslash($_POST["problem"])) : "";
    $outcome = isset($_POST["outcome"]) ? sanitize_textarea_field(wp_unslash($_POST["outcome"])) : "";
    $cta_text = isset($_POST["cta_text"]) ? sanitize_text_field(wp_unslash($_POST["cta_text"])) : "Umów bezpłatną rozmowę";
    $custom_content = isset($_POST["content_template"]) ? wp_kses_post(wp_unslash($_POST["content_template"])) : "";
    $status = isset($_POST["post_status"]) ? sanitize_text_field(wp_unslash($_POST["post_status"])) : "draft";
    $status = in_array($status, ["draft", "publish"], true) ? $status : "draft";
    $publish_at_raw = isset($_POST["publish_at"]) ? sanitize_text_field(wp_unslash($_POST["publish_at"])) : "";
    $generation_mode = isset($_POST["generation_mode"]) ? sanitize_key(wp_unslash($_POST["generation_mode"])) : "manual";
    $generation_mode = in_array($generation_mode, ["manual", "ai"], true) ? $generation_mode : "manual";
    $ai_topics_raw = isset($_POST["ai_topics"]) ? sanitize_textarea_field(wp_unslash($_POST["ai_topics"])) : "";
    $ai_topic_seed = isset($_POST["ai_topic_seed"]) ? sanitize_text_field(wp_unslash($_POST["ai_topic_seed"])) : "";
    $ai_topics_count = isset($_POST["ai_topics_count"]) ? (int) wp_unslash($_POST["ai_topics_count"]) : 1;
    $ai_topics_count = max(1, min(40, $ai_topics_count));
    $ai_campaign_prompt = isset($_POST["ai_campaign_prompt"]) ? sanitize_textarea_field(wp_unslash($_POST["ai_campaign_prompt"])) : "";
    $ai_extra_instructions = isset($_POST["ai_extra_instructions"]) ? sanitize_textarea_field(wp_unslash($_POST["ai_extra_instructions"])) : "";
    $publish_schedule_mode = isset($_POST["publish_schedule_mode"]) ? sanitize_key(wp_unslash($_POST["publish_schedule_mode"])) : "immediate";
    $publish_schedule_mode = in_array($publish_schedule_mode, ["immediate", "at_time", "interval"], true) ? $publish_schedule_mode : "immediate";
    $publish_interval_value = isset($_POST["publish_interval_value"]) ? (int) wp_unslash($_POST["publish_interval_value"]) : 1;
    $publish_interval_value = max(1, min(365, $publish_interval_value));
    $publish_interval_unit = isset($_POST["publish_interval_unit"]) ? sanitize_key(wp_unslash($_POST["publish_interval_unit"])) : "days";
    $publish_interval_unit = in_array($publish_interval_unit, ["minutes", "hours", "days"], true) ? $publish_interval_unit : "days";

    if ($generation_mode === "manual" && $title === "") {
        wp_safe_redirect(add_query_arg("upsellio_tool_status", "missing_title", menu_page_url("upsellio-seo-blog-tool", false)));
        exit;
    }

    $category_id = isset($_POST["category_id"]) ? (int) $_POST["category_id"] : 0;
    $tags_input = isset($_POST["tags"]) ? sanitize_text_field(wp_unslash($_POST["tags"])) : "";
    $tag_names = array_filter(array_map("trim", explode(",", $tags_input)));
    $featured_image_url = isset($_POST["featured_image_url"]) ? esc_url_raw(wp_unslash($_POST["featured_image_url"])) : "";

    $is_quick_edit_mode = $edit_post_id > 0;
    if ($is_quick_edit_mode && !current_user_can("edit_post", $edit_post_id)) {
        wp_safe_redirect(add_query_arg("upsellio_tool_status", "error", menu_page_url("upsellio-seo-blog-tool", false)));
        exit;
    }

    if ($generation_mode === "ai" && !$is_quick_edit_mode) {
        $ai_settings = upsellio_blog_tool_get_ai_settings();
        if ($ai_campaign_prompt !== "") {
            $ai_settings["campaign_prompt_template"] = $ai_campaign_prompt;
        }
        $crm_ready = function_exists("upsellio_anthropic_crm_api_key") && upsellio_anthropic_crm_api_key() !== "";
        if (!$crm_ready) {
            wp_safe_redirect(add_query_arg("upsellio_tool_status", "ai_missing_config", menu_page_url("upsellio-seo-blog-tool", false)));
            exit;
        }

        $topics = upsellio_blog_tool_parse_topics($ai_topics_raw);
        if (empty($topics)) {
            $seed_topic = $ai_topic_seed !== "" ? $ai_topic_seed : ($title !== "" ? $title : $primary_query);
            if ($seed_topic === "") {
                wp_safe_redirect(add_query_arg("upsellio_tool_status", "ai_missing_seed", menu_page_url("upsellio-seo-blog-tool", false)));
                exit;
            }

            $topics_result = upsellio_blog_tool_generate_topics_via_ai($ai_settings, $seed_topic, $ai_topics_count, $audience, $article_type, $ai_extra_instructions);
            if (is_wp_error($topics_result)) {
                wp_safe_redirect(add_query_arg(
                    [
                        "upsellio_tool_status" => "ai_error",
                        "upsellio_ai_error" => rawurlencode($topics_result->get_error_message()),
                    ],
                    menu_page_url("upsellio-seo-blog-tool", false)
                ));
                exit;
            }
            $topics = $topics_result;
        }

        if (empty($topics)) {
            wp_safe_redirect(add_query_arg("upsellio_tool_status", "ai_empty_topics", menu_page_url("upsellio-seo-blog-tool", false)));
            exit;
        }
        $topics = array_slice($topics, 0, $ai_topics_count);

        $created_post_ids = [];
        $failed_topics = [];

        foreach ($topics as $index => $topic_item) {
            $ai_payload = upsellio_blog_tool_generate_post_payload_via_ai($ai_settings, $topic_item, [
                "audience" => $audience,
                "search_intent" => $intent,
                "article_type" => $article_type,
                "cta_text" => $cta_text,
                "problem" => $problem,
                "outcome" => $outcome,
                "query_cluster" => $query_cluster,
                "user_questions" => $user_questions,
                "ai_extra_instructions" => $ai_extra_instructions,
            ]);

            if (is_wp_error($ai_payload)) {
                $failed_topics[] = (string) $topic_item;
                continue;
            }

            $post_data = [
                "post_title" => (string) ($ai_payload["title"] ?? (string) $topic_item),
                "post_name" => sanitize_title((string) ($ai_payload["primary_query"] ?? (string) $topic_item)),
                "post_excerpt" => (string) ($ai_payload["seo_description"] ?? ""),
                "post_content" => (string) ($ai_payload["content_html"] ?? ""),
                "post_status" => $status,
                "post_type" => "post",
            ];

            if ($status === "publish" && $publish_schedule_mode !== "immediate") {
                $publish_dt = upsellio_blog_tool_get_batch_publish_date(
                    $publish_schedule_mode,
                    $publish_at_raw,
                    $publish_interval_value,
                    $publish_interval_unit,
                    (int) $index
                );

                if ($publish_dt instanceof DateTimeImmutable) {
                    $now = new DateTimeImmutable("now", wp_timezone());
                    $post_data["post_date"] = $publish_dt->format("Y-m-d H:i:s");
                    $post_data["post_date_gmt"] = get_gmt_from_date($post_data["post_date"]);
                    if ($publish_dt > $now) {
                        $post_data["post_status"] = "future";
                    }
                }
            }

            $post_id = wp_insert_post($post_data, true);
            if (is_wp_error($post_id)) {
                $failed_topics[] = (string) $topic_item;
                continue;
            }

            if ($category_id > 0) {
                wp_set_post_categories($post_id, [$category_id], false);
            }

            $merged_tags = array_values(array_unique(array_filter(array_merge(
                $tag_names,
                is_array($ai_payload["tags"] ?? null) ? $ai_payload["tags"] : []
            ))));
            if (!empty($merged_tags)) {
                wp_set_post_tags($post_id, $merged_tags, false);
            }

            if ($featured_image_url !== "") {
                update_post_meta($post_id, "_upsellio_featured_image_url", $featured_image_url);
            }

            upsellio_save_seo_meta_for_post(
                $post_id,
                (string) ($ai_payload["seo_title"] ?? ""),
                (string) ($ai_payload["seo_description"] ?? ""),
                (string) ($ai_payload["primary_query"] ?? ""),
                (string) ($ai_payload["query_cluster"] ?? ""),
                (string) ($ai_payload["user_questions"] ?? ""),
                $article_type
            );

            $related_ids = upsellio_get_related_post_ids($post_id, 3);
            if (!empty($related_ids)) {
                update_post_meta($post_id, "_upsellio_related_post_ids", $related_ids);
            }

            $created_post_ids[] = (int) $post_id;
        }

        $redirect = add_query_arg(
            [
                "upsellio_tool_status" => "created_batch",
                "created_count" => count($created_post_ids),
                "failed_count" => count($failed_topics),
                "post_id" => (int) ($created_post_ids[0] ?? 0),
            ],
            menu_page_url("upsellio-seo-blog-tool", false)
        );
        wp_safe_redirect($redirect);
        exit;
    }

    $content = $custom_content !== "" ? $custom_content : upsellio_generate_default_post_content(
        $title,
        $primary_query,
        $query_cluster,
        $user_questions,
        $audience,
        $intent,
        $problem,
        $outcome,
        $cta_text,
        $article_type
    );

    $post_data = [
        "post_title" => $title,
        "post_name" => sanitize_title($primary_query !== "" ? $primary_query : $title),
        "post_excerpt" => $seo_description,
        "post_content" => $content,
        "post_status" => $status,
        "post_type" => "post",
    ];

    if ($is_quick_edit_mode) {
        $post_data["ID"] = $edit_post_id;
        unset($post_data["post_name"]);
    }

    if ($publish_at_raw !== "") {
        $site_timezone = wp_timezone();
        $publish_at_dt = DateTimeImmutable::createFromFormat("Y-m-d\\TH:i", $publish_at_raw, $site_timezone);
        if ($publish_at_dt instanceof DateTimeImmutable) {
            $now = new DateTimeImmutable("now", $site_timezone);
            $post_data["post_date"] = $publish_at_dt->format("Y-m-d H:i:s");
            $post_data["post_date_gmt"] = get_gmt_from_date($post_data["post_date"]);

            // If user selected publish + future date, schedule the post.
            if ($status === "publish" && $publish_at_dt > $now) {
                $post_data["post_status"] = "future";
            }
        }
    }

    $post_id = $is_quick_edit_mode ? wp_update_post($post_data, true) : wp_insert_post($post_data, true);

    if (is_wp_error($post_id)) {
        wp_safe_redirect(add_query_arg("upsellio_tool_status", "error", menu_page_url("upsellio-seo-blog-tool", false)));
        exit;
    }

    if ($category_id > 0) {
        wp_set_post_categories($post_id, [$category_id], false);
    }

    if (!empty($tag_names)) {
        wp_set_post_tags($post_id, $tag_names, false);
    }

    if ($featured_image_url !== "") {
        update_post_meta($post_id, "_upsellio_featured_image_url", $featured_image_url);
    }

    upsellio_save_seo_meta_for_post($post_id, $seo_title, $seo_description, $primary_query, $query_cluster, $user_questions, $article_type);

    $related_ids = upsellio_get_related_post_ids($post_id, 3);
    if (!empty($related_ids)) {
        update_post_meta($post_id, "_upsellio_related_post_ids", $related_ids);
    }

    $redirect_status = $is_quick_edit_mode ? "updated" : "created";
    $redirect = add_query_arg(
        [
            "upsellio_tool_status" => $redirect_status,
            "post_id" => $post_id,
        ],
        menu_page_url("upsellio-seo-blog-tool", false)
    );
    wp_safe_redirect($redirect);
    exit;
}
add_action("admin_init", "upsellio_handle_blog_generator_submit");

function upsellio_render_blog_generator_screen()
{
    if (!current_user_can("edit_posts")) {
        return;
    }

    $categories = get_categories(["hide_empty" => false]);
    $status = isset($_GET["upsellio_tool_status"]) ? sanitize_text_field(wp_unslash($_GET["upsellio_tool_status"])) : "";
    $created_post_id = isset($_GET["post_id"]) ? (int) $_GET["post_id"] : 0;
    $ai_settings = upsellio_blog_tool_get_ai_settings();
    $created_count = isset($_GET["created_count"]) ? (int) $_GET["created_count"] : 0;
    $failed_count = isset($_GET["failed_count"]) ? (int) $_GET["failed_count"] : 0;
    $ai_error = isset($_GET["upsellio_ai_error"]) ? sanitize_text_field(rawurldecode((string) wp_unslash($_GET["upsellio_ai_error"]))) : "";
    $existing_posts = get_posts([
        "post_type" => "post",
        "post_status" => "publish",
        "posts_per_page" => 120,
        "orderby" => "date",
        "order" => "DESC",
    ]);
    $existing_posts_data = [];
    foreach ($existing_posts as $existing_post) {
        $existing_id = (int) $existing_post->ID;
        $existing_posts_data[] = [
            "id" => $existing_id,
            "title" => (string) get_the_title($existing_id),
            "excerpt" => (string) get_the_excerpt($existing_id),
            "categories" => wp_get_post_categories($existing_id, ["fields" => "names"]),
            "tags" => wp_get_post_tags($existing_id, ["fields" => "names"]),
            "url" => (string) get_permalink($existing_id),
        ];
    }
    $quick_edit_post_id = isset($_GET["quick_edit_post_id"]) ? (int) $_GET["quick_edit_post_id"] : 0;
    $quick_edit_post = $quick_edit_post_id > 0 ? get_post($quick_edit_post_id) : null;
    $is_quick_edit = $quick_edit_post instanceof WP_Post && $quick_edit_post->post_type === "post";

    $prefill_values = [
        "post_title" => "",
        "primary_query" => "",
        "query_cluster" => "",
        "user_questions" => "",
        "article_type" => "seo_article",
        "seo_title" => "",
        "seo_description" => "",
        "search_intent" => "Informacyjna",
        "audience" => "",
        "problem" => "",
        "outcome" => "",
        "cta_text" => "Umów bezpłatną rozmowę",
        "content_template" => "",
        "category_id" => 0,
        "tags" => "",
        "post_status" => "draft",
        "featured_image_url" => "",
        "generation_mode" => "manual",
        "ai_topics" => "",
        "ai_topic_seed" => "",
        "ai_topics_count" => 1,
        "ai_campaign_prompt" => (string) ($ai_settings["campaign_prompt_template"] ?? ""),
        "ai_extra_instructions" => "",
        "publish_schedule_mode" => "immediate",
        "publish_interval_value" => 1,
        "publish_interval_unit" => "days",
    ];

    if ($is_quick_edit) {
        $prefill_values["post_title"] = (string) get_the_title($quick_edit_post_id);
        $prefill_values["primary_query"] = (string) get_post_meta($quick_edit_post_id, "_upsellio_primary_query", true);
        if ($prefill_values["primary_query"] === "") {
            $prefill_values["primary_query"] = (string) get_post_meta($quick_edit_post_id, "_yoast_wpseo_focuskw", true);
        }
        $prefill_values["query_cluster"] = (string) get_post_meta($quick_edit_post_id, "_upsellio_query_cluster", true);
        $prefill_values["user_questions"] = (string) get_post_meta($quick_edit_post_id, "_upsellio_user_questions", true);
        $prefill_values["article_type"] = (string) get_post_meta($quick_edit_post_id, "_upsellio_article_type", true);
        if (!in_array($prefill_values["article_type"], ["blog_educational", "seo_article", "landing_sales"], true)) {
            $prefill_values["article_type"] = "seo_article";
        }
        $prefill_values["seo_title"] = (string) get_post_meta($quick_edit_post_id, "_yoast_wpseo_title", true);
        $prefill_values["seo_description"] = (string) get_post_meta($quick_edit_post_id, "_yoast_wpseo_metadesc", true);
        $prefill_values["content_template"] = (string) $quick_edit_post->post_content;
        $prefill_values["category_id"] = (int) (wp_get_post_categories($quick_edit_post_id)[0] ?? 0);
        $prefill_values["tags"] = implode(", ", wp_get_post_tags($quick_edit_post_id, ["fields" => "names"]));
        $prefill_values["post_status"] = $quick_edit_post->post_status === "publish" ? "publish" : "draft";
        $prefill_values["featured_image_url"] = (string) get_post_meta($quick_edit_post_id, "_upsellio_featured_image_url", true);
    }
    ?>
    <div class="wrap">
      <style>
        .ups-tool-grid { display:grid; grid-template-columns: 1.35fr 0.65fr; gap:18px; align-items:start; margin-top:18px; max-width:1280px; }
        .ups-tool-form { background:#fff; border:1px solid #dcdcda; border-radius:14px; padding:20px; }
        .ups-tool-panel { position:sticky; top:32px; background:#fff; border:1px solid #dcdcda; border-radius:14px; padding:18px; box-shadow:0 1px 6px rgba(0,0,0,0.04); }
        .ups-tool-panel h3 { margin:0; font-size:16px; }
        .ups-next-action {
          margin-top: 10px;
          width: 100%;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          gap: 6px;
          border: 1px solid #0d9488;
          border-radius: 10px;
          background: #0d9488;
          color: #fff;
          padding: 9px 12px;
          font-size: 12px;
          font-weight: 700;
          cursor: pointer;
          transition: 0.18s ease;
        }
        .ups-next-action:hover { background:#0f766e; border-color:#0f766e; }
        .ups-next-action[disabled] {
          background:#f2f2ef;
          border-color:#e2e2dd;
          color:#8d8d85;
          cursor:not-allowed;
        }
        .ups-score-head { display:flex; align-items:center; gap:14px; margin-top:12px; }
        .ups-score-ring {
          --score-deg: 0deg;
          width:96px; height:96px; border-radius:999px;
          background: conic-gradient(var(--score-color, #d14c4c) var(--score-deg), #ecece9 var(--score-deg));
          display:grid; place-items:center; transition:all .35s ease;
        }
        .ups-score-ring::before {
          content:"";
          width:72px; height:72px; border-radius:999px; background:#fff; border:1px solid #ecece9;
          position:absolute;
        }
        .ups-score-value {
          position:relative; z-index:2; font-size:22px; font-weight:700; color:#181818; line-height:1;
        }
        .ups-score-state { font-size:12px; color:#6a6a62; margin-top:2px; }
        .ups-score-bars { margin-top:14px; display:grid; gap:10px; }
        .ups-score-row { display:grid; gap:4px; }
        .ups-score-row-top { display:flex; justify-content:space-between; font-size:12px; color:#585850; }
        .ups-score-track { width:100%; height:7px; border-radius:999px; background:#efefec; overflow:hidden; }
        .ups-score-fill { width:0%; height:100%; border-radius:999px; transition:width .35s ease; background:#0d9488; }
        .ups-score-tips { margin-top:16px; border-top:1px solid #ecece9; padding-top:12px; }
        .ups-score-tips h4 { margin:0 0 8px; font-size:13px; }
        .ups-score-list { margin:0; padding-left:16px; display:grid; gap:8px; }
        .ups-score-list li { font-size:12px; line-height:1.5; color:#4f4f48; }
        .ups-score-list li strong { color:#191919; }
        .ups-score-jump {
          border:none; background:none; padding:0; margin:0; text-align:left; cursor:pointer;
          color:inherit; font:inherit; line-height:inherit;
        }
        .ups-score-jump:hover { color:#0d9488; text-decoration:underline; text-underline-offset:2px; }
        .ups-ok-chip { margin-top:8px; display:inline-flex; border:1px solid #99f6e4; background:#ecfeff; color:#0f766e; border-radius:999px; padding:5px 10px; font-size:11px; font-weight:600; }
        .ups-form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
        .ups-form-grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; }
        .ups-field { display:block; }
        .ups-field > strong { display:block; }
        .ups-field input, .ups-field textarea, .ups-field select { width:100%; margin-top:6px; }
        .ups-field-highlight {
          outline: 2px solid #0d9488;
          border-radius: 8px;
          box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.12);
          transition: box-shadow .2s ease, outline-color .2s ease;
        }
        .ups-tool-grid { grid-template-columns:1fr; }
        .ups-tool-panel { position:static; }
        @media (min-width: 1261px) {
          .ups-tool-grid { grid-template-columns:minmax(0,1fr) 320px; }
          .ups-tool-panel { position:sticky; }
        }
        .ups-score-metrics { margin-top:14px; display:grid; grid-template-columns:1fr 1fr; gap:8px; }
        .ups-score-metric {
          border:1px solid #ecece9; border-radius:10px; background:#fafaf8; padding:8px;
        }
        .ups-score-metric-name { font-size:11px; color:#5a5a53; margin-bottom:4px; }
        .ups-score-metric-value { font-size:14px; font-weight:700; color:#121212; }
        .ups-critical-box { margin-top:14px; border:1px solid #f0d3d3; background:#fff4f4; border-radius:12px; padding:10px; }
        .ups-mode-toggle { margin-top:10px; display:flex; align-items:center; gap:8px; font-size:12px; color:#4f4f48; }
        .ups-mode-toggle input { margin:0; }
        .ups-critical-box h4 { margin:0 0 8px; font-size:12px; color:#922f2f; }
        .ups-critical-list { margin:0; padding-left:16px; display:grid; gap:6px; }
        .ups-critical-list li { font-size:12px; color:#7f2c2c; line-height:1.45; }
        .ups-ready-chip { margin-top:10px; display:inline-flex; border-radius:999px; padding:5px 10px; font-size:11px; font-weight:700; }
        .ups-ready-chip.no { background:#fff2f2; color:#9f3636; border:1px solid #f0d4d4; }
        .ups-ready-chip.yes { background:#e9f9f3; color:#0b5d48; border:1px solid #bfe9d9; }
        .ups-delta-box { margin-top:12px; border-top:1px solid #ecece9; padding-top:10px; font-size:12px; color:#5f5f57; }
        .ups-delta-up { color:#0d9488; font-weight:700; }
        .ups-delta-down { color:#c14545; font-weight:700; }
        .ups-fix-box { margin-top:12px; border-top:1px solid #ecece9; padding-top:10px; }
        .ups-fix-title { margin:0 0 8px; font-size:12px; color:#5a5a52; font-weight:700; }
        .ups-fix-buttons { display:flex; flex-wrap:wrap; gap:8px; }
        .ups-fix-btn {
          border:1px solid #d9d9d5; background:#fff; border-radius:999px; padding:6px 10px;
          font-size:11px; color:#3f3f39; cursor:pointer; transition:.15s ease;
        }
        .ups-fix-btn:hover { border-color:#0d9488; color:#0d9488; }
        .ups-heatmap { margin-top:12px; border-top:1px solid #ecece9; padding-top:10px; }
        .ups-heatmap-title { margin:0 0 8px; font-size:12px; color:#5a5a52; font-weight:700; }
        .ups-heatmap-list { margin:0; padding:0; list-style:none; display:grid; gap:6px; }
        .ups-heatmap-item {
          border-radius:10px; padding:7px 9px; border:1px solid #ecece8; background:#fafaf8;
          display:flex; align-items:center; justify-content:space-between; gap:8px;
        }
        .ups-heatmap-item-name { font-size:12px; color:#3f3f3a; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .ups-heatmap-badge { border-radius:999px; padding:3px 7px; font-size:10px; font-weight:700; }
        .ups-heatmap-badge.good { background:#ecfeff; color:#0f766e; border:1px solid #99f6e4; }
        .ups-heatmap-badge.mid { background:#fff4e5; color:#8d5b1b; border:1px solid #f1ddbf; }
        .ups-heatmap-badge.bad { background:#fff2f2; color:#963636; border:1px solid #f2d0d0; }
        .ups-posts-box { margin-top:20px; border-top:1px solid #ecece9; padding-top:14px; }
        .ups-posts-box h4 { margin:0 0 10px; font-size:13px; color:#34342f; }
        .ups-posts-list { margin:0; padding:0; list-style:none; display:grid; gap:8px; max-height:240px; overflow:auto; }
        .ups-post-item { border:1px solid #ecece8; border-radius:10px; padding:8px 10px; background:#fafaf8; }
        .ups-post-title { margin:0 0 6px; font-size:12px; color:#20201d; font-weight:600; }
        .ups-post-actions { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
        .ups-post-link { font-size:11px; color:#2271b1; text-decoration:none; }
        .ups-post-link:hover { text-decoration:underline; }
        .ups-quick-edit-btn {
          border:1px solid #0d9488;
          background:#fff;
          color:#0d9488;
          border-radius:999px;
          font-size:11px;
          line-height:1;
          padding:6px 10px;
          text-decoration:none;
          font-weight:700;
        }
        .ups-quick-edit-btn:hover { background:#0d9488; color:#fff; text-decoration:none; }
      </style>
      <h1 style="margin-bottom:16px;">Upsellio SEO Blog Tool</h1>
      <p style="max-width:920px;">
        Generator ocenia wpis pod SEO 2026: intent match, pokrycie semantyczne, topical coverage,
        benchmark względem konkurencji i dopasowanie do typu wpisu.
      </p>

      <?php if ($status === "created" && $created_post_id > 0) : ?>
        <div class="notice notice-success is-dismissible">
          <p>
            Wpis został utworzony.
            <a href="<?php echo esc_url(get_edit_post_link($created_post_id)); ?>">Edytuj wpis</a>
            ·
            <a href="<?php echo esc_url(get_permalink($created_post_id)); ?>" target="_blank" rel="noopener">Podgląd</a>
          </p>
        </div>
      <?php elseif ($status === "created_batch") : ?>
        <div class="notice notice-success is-dismissible">
          <p>
            Wygenerowano wpisy: <strong><?php echo esc_html((string) $created_count); ?></strong>.
            <?php if ($failed_count > 0) : ?>
              Nieudane: <strong><?php echo esc_html((string) $failed_count); ?></strong>.
            <?php endif; ?>
            <?php if ($created_post_id > 0) : ?>
              <a href="<?php echo esc_url(get_edit_post_link($created_post_id)); ?>">Otwórz pierwszy wygenerowany wpis</a>
            <?php endif; ?>
          </p>
        </div>
      <?php elseif ($status === "updated" && $created_post_id > 0) : ?>
        <div class="notice notice-success is-dismissible">
          <p>
            Wpis został zaktualizowany.
            <a href="<?php echo esc_url(get_edit_post_link($created_post_id)); ?>">Przejdź do klasycznej edycji</a>
            ·
            <a href="<?php echo esc_url(get_permalink($created_post_id)); ?>" target="_blank" rel="noopener">Podgląd</a>
          </p>
        </div>
      <?php elseif ($status === "missing_title") : ?>
        <div class="notice notice-error is-dismissible"><p>Uzupełnij tytuł wpisu.</p></div>
      <?php elseif ($status === "ai_missing_config") : ?>
        <div class="notice notice-error is-dismissible"><p>Skonfiguruj klucz Anthropic dla CRM (<code>UPSELLIO_ANTHROPIC_API_KEY</code> lub opcja <code>ups_anthropic_api_key</code>).</p></div>
      <?php elseif ($status === "ai_missing_seed") : ?>
        <div class="notice notice-error is-dismissible"><p>Podaj temat bazowy albo listę tematów do wygenerowania wpisów.</p></div>
      <?php elseif ($status === "ai_empty_topics") : ?>
        <div class="notice notice-error is-dismissible"><p>AI nie zwróciło listy tematów. Zmień wytyczne i spróbuj ponownie.</p></div>
      <?php elseif ($status === "ai_error") : ?>
        <div class="notice notice-error is-dismissible"><p>Błąd AI: <?php echo esc_html($ai_error !== "" ? $ai_error : "Nie udało się wygenerować wpisów."); ?></p></div>
      <?php elseif ($status === "error") : ?>
        <div class="notice notice-error is-dismissible"><p>Wystąpił błąd podczas zapisu wpisu.</p></div>
      <?php endif; ?>

      <?php if ($is_quick_edit) : ?>
        <div class="notice notice-info">
          <p>
            Tryb szybkiej korekty aktywny dla wpisu: <strong><?php echo esc_html(get_the_title($quick_edit_post_id)); ?></strong>.
            <a href="<?php echo esc_url(menu_page_url("upsellio-seo-blog-tool", false)); ?>">Wróć do tworzenia nowego wpisu</a>
          </p>
        </div>
      <?php endif; ?>

      <div class="ups-tool-grid">
        <form method="post" class="ups-tool-form js-ups-seo-form">
          <?php wp_nonce_field("upsellio_blog_generator_action", "upsellio_blog_generator_nonce"); ?>
          <input type="hidden" name="upsellio_blog_generator_submit" value="1" />
          <input type="hidden" name="edit_post_id" value="<?php echo esc_attr((string) ($is_quick_edit ? $quick_edit_post_id : 0)); ?>" />

          <h2 style="margin:0 0 12px;">Treść i SEO</h2>
          <div class="ups-form-grid-3">
            <label class="ups-field">
              <strong>Tryb generowania</strong>
              <select name="generation_mode">
                <option value="manual" <?php selected($prefill_values["generation_mode"], "manual"); ?>>Manualny (1 wpis)</option>
                <option value="ai" <?php selected($prefill_values["generation_mode"], "ai"); ?>>AI (1 lub wiele wpisów)</option>
              </select>
            </label>
            <label class="ups-field">
              <strong>Liczba wpisów (AI)</strong>
              <input type="number" name="ai_topics_count" min="1" max="40" value="<?php echo esc_attr((string) ((int) $prefill_values["ai_topics_count"])); ?>" />
            </label>
            <label class="ups-field">
              <strong>Plan publikacji (AI)</strong>
              <select name="publish_schedule_mode">
                <option value="immediate" <?php selected($prefill_values["publish_schedule_mode"], "immediate"); ?>>Od razu</option>
                <option value="at_time" <?php selected($prefill_values["publish_schedule_mode"], "at_time"); ?>>Wskazana data/godzina</option>
                <option value="interval" <?php selected($prefill_values["publish_schedule_mode"], "interval"); ?>>Okresowo w odstępach</option>
              </select>
            </label>
          </div>

          <div class="ups-form-grid-2" style="margin-top:14px;">
            <label class="ups-field">
              <strong>Temat bazowy dla AI</strong>
              <input type="text" name="ai_topic_seed" value="<?php echo esc_attr($prefill_values["ai_topic_seed"]); ?>" placeholder="np. Jak zwiększyć sprzedaż w e-commerce" />
              <small>Gdy lista tematów jest pusta, AI wygeneruje listę na bazie tego tematu.</small>
            </label>
            <label class="ups-field">
              <strong>Tematy wpisów (1 temat w linii lub po przecinku)</strong>
              <textarea name="ai_topics" rows="4" placeholder="Temat 1&#10;Temat 2&#10;Temat 3"><?php echo esc_textarea($prefill_values["ai_topics"]); ?></textarea>
            </label>
          </div>
          <label class="ups-field" style="margin-top:14px;">
            <strong>Prompt kampanii (dla tej serii wpisów)</strong>
            <textarea name="ai_campaign_prompt" rows="4" placeholder="Opis stylu, tonu, struktury, wymagań biznesowych i SEO dla całej kampanii."><?php echo esc_textarea($prefill_values["ai_campaign_prompt"]); ?></textarea>
          </label>
          <label class="ups-field" style="margin-top:14px;">
            <strong>Dodatkowe wytyczne dla AI</strong>
            <textarea name="ai_extra_instructions" rows="4" placeholder="Styl, długość, czego unikać, jak ma wyglądać CTA itd."><?php echo esc_textarea($prefill_values["ai_extra_instructions"]); ?></textarea>
          </label>
          <div class="ups-form-grid-2">
            <label class="ups-field">
              <strong>Tytuł wpisu *</strong>
              <input type="text" name="post_title" data-seo-field="post_title" value="<?php echo esc_attr($prefill_values["post_title"]); ?>" />
            </label>
            <label class="ups-field">
              <strong>Fraza główna (primary query)</strong>
              <input type="text" name="primary_query" data-seo-field="primary_query" value="<?php echo esc_attr($prefill_values["primary_query"]); ?>" />
            </label>
            <label class="ups-field">
              <strong>Typ wpisu</strong>
              <select name="article_type" data-seo-field="article_type">
                <option value="blog_educational" <?php selected($prefill_values["article_type"], "blog_educational"); ?>>Blog edukacyjny</option>
                <option value="seo_article" <?php selected($prefill_values["article_type"], "seo_article"); ?>>Artykuł SEO</option>
                <option value="landing_sales" <?php selected($prefill_values["article_type"], "landing_sales"); ?>>Landing / sprzedaż</option>
              </select>
            </label>
            <label class="ups-field">
              <strong>SEO Title</strong>
              <input type="text" name="seo_title" data-seo-field="seo_title" value="<?php echo esc_attr($prefill_values["seo_title"]); ?>" />
            </label>
            <label class="ups-field">
              <strong>Meta description (150-160 znaków)</strong>
              <input type="text" name="seo_description" data-seo-field="seo_description" value="<?php echo esc_attr($prefill_values["seo_description"]); ?>" maxlength="160" />
            </label>
            <label class="ups-field">
              <strong>Search intent</strong>
              <select name="search_intent" data-seo-field="search_intent">
                <option value="Informacyjna" <?php selected($prefill_values["search_intent"], "Informacyjna"); ?>>Informacyjna</option>
                <option value="Porównawcza" <?php selected($prefill_values["search_intent"], "Porównawcza"); ?>>Porównawcza</option>
                <option value="Decyzyjna" <?php selected($prefill_values["search_intent"], "Decyzyjna"); ?>>Decyzyjna</option>
                <option value="Transakcyjna" <?php selected($prefill_values["search_intent"], "Transakcyjna"); ?>>Transakcyjna</option>
              </select>
            </label>
            <label class="ups-field">
              <strong>Grupa docelowa</strong>
              <input type="text" name="audience" data-seo-field="audience" value="<?php echo esc_attr($prefill_values["audience"]); ?>" placeholder="np. właściciele firm B2B" />
            </label>
          </div>

          <div class="ups-form-grid-2" style="margin-top:14px;">
            <label class="ups-field">
              <strong>Query cluster (5-10 powiązanych fraz)</strong>
              <textarea name="query_cluster" data-seo-field="query_cluster" rows="4" placeholder="fraza 1, fraza 2, fraza 3"><?php echo esc_textarea($prefill_values["query_cluster"]); ?></textarea>
            </label>
            <label class="ups-field">
              <strong>Pytania użytkowników (po 1 w linii)</strong>
              <textarea name="user_questions" data-seo-field="user_questions" rows="4" placeholder="Jak...?&#10;Ile trwa...?&#10;Co wybrać...?"><?php echo esc_textarea($prefill_values["user_questions"]); ?></textarea>
            </label>
          </div>

          <div class="ups-form-grid-2" style="margin-top:14px;">
            <label class="ups-field">
              <strong>Główny problem odbiorcy</strong>
              <textarea name="problem" data-seo-field="problem" rows="4"><?php echo esc_textarea($prefill_values["problem"]); ?></textarea>
            </label>
            <label class="ups-field">
              <strong>Docelowy efekt / outcome</strong>
              <textarea name="outcome" data-seo-field="outcome" rows="4"><?php echo esc_textarea($prefill_values["outcome"]); ?></textarea>
            </label>
          </div>

          <label class="ups-field" style="margin-top:14px;">
            <strong>CTA końcowe</strong>
            <input type="text" name="cta_text" data-seo-field="cta_text" value="<?php echo esc_attr($prefill_values["cta_text"]); ?>" />
          </label>

          <label class="ups-field" style="margin-top:14px;">
            <strong>Szablon treści (opcjonalnie, jeśli pusty - generator przygotuje strukturę automatycznie)</strong>
            <textarea name="content_template" data-seo-field="content_template" rows="14" placeholder="<h2>...</h2><p>...</p>"><?php echo esc_textarea($prefill_values["content_template"]); ?></textarea>
            <small>Możesz użyć shortcode: <code>[upsellio_internal_links]</code> i <code>[upsellio_contact_form]</code>.</small>
          </label>

          <label class="ups-field" style="margin-top:14px;">
            <strong>Benchmark konkurencji TOP10 (opcjonalnie)</strong>
            <textarea name="competitive_notes" data-seo-field="competitive_notes" rows="6" placeholder="Wklej nagłówki H2/H3 lub krótkie notatki o TOP10: długość, sekcje, luki."></textarea>
            <small>Na tej podstawie narzędzie wskaże brakujące sekcje i lukę względem konkurencji.</small>
          </label>

          <h2 style="margin:24px 0 12px;">Publikacja i klasyfikacja</h2>
          <div class="ups-form-grid-3">
            <label class="ups-field">
              <strong>Kategoria</strong>
              <select name="category_id" data-seo-field="category_id">
                <option value="0">— wybierz —</option>
                <?php foreach ($categories as $category) : ?>
                  <option value="<?php echo esc_attr((string) $category->term_id); ?>" <?php selected((int) $prefill_values["category_id"], (int) $category->term_id); ?>><?php echo esc_html($category->name); ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <label class="ups-field">
              <strong>Tagi (po przecinku)</strong>
              <input type="text" name="tags" data-seo-field="tags" value="<?php echo esc_attr($prefill_values["tags"]); ?>" placeholder="meta ads, pozyskiwanie klientów, landing page" />
            </label>
            <label class="ups-field">
              <strong>Status wpisu</strong>
              <select name="post_status">
                <option value="draft" <?php selected($prefill_values["post_status"], "draft"); ?>>Szkic</option>
                <option value="publish" <?php selected($prefill_values["post_status"], "publish"); ?>>Opublikuj</option>
              </select>
            </label>
          </div>

          <label class="ups-field" style="margin-top:14px;">
            <strong>Data publikacji (opcjonalnie)</strong>
            <input type="datetime-local" name="publish_at" />
            <small>Dla AI + "wskazana data": wszystkie wpisy dostaną czas startowy. Dla AI + "okresowo": kolejne wpisy będą publikowane w zadanym interwale.</small>
          </label>

          <div class="ups-form-grid-2" style="margin-top:14px;">
            <label class="ups-field">
              <strong>Interwał publikacji (AI, tryb okresowy)</strong>
              <input type="number" name="publish_interval_value" min="1" max="365" value="<?php echo esc_attr((string) ((int) $prefill_values["publish_interval_value"])); ?>" />
            </label>
            <label class="ups-field">
              <strong>Jednostka interwału</strong>
              <select name="publish_interval_unit">
                <option value="minutes" <?php selected($prefill_values["publish_interval_unit"], "minutes"); ?>>Minuty</option>
                <option value="hours" <?php selected($prefill_values["publish_interval_unit"], "hours"); ?>>Godziny</option>
                <option value="days" <?php selected($prefill_values["publish_interval_unit"], "days"); ?>>Dni</option>
              </select>
            </label>
          </div>

          <label class="ups-field" style="margin-top:14px;">
            <strong>URL obrazka hero (opcjonalnie)</strong>
            <input type="url" name="featured_image_url" data-seo-field="featured_image_url" value="<?php echo esc_attr($prefill_values["featured_image_url"]); ?>" placeholder="https://..." />
          </label>

          <div style="margin-top:22px;display:flex;align-items:center;gap:12px;">
            <button type="submit" class="button button-primary button-hero"><?php echo esc_html($is_quick_edit ? "Zapisz szybką korektę SEO" : "Utwórz SEO-ready wpis"); ?></button>
            <span style="color:#5f5f57;"><?php echo esc_html($is_quick_edit ? "Zmiany zostaną zapisane bezpośrednio w tym wpisie." : "Generator dopasuje strukturę i scoring do typu wpisu oraz celów SEO 2026."); ?></span>
          </div>
        </form>

        <aside class="ups-tool-panel js-ups-score-panel">
          <h3>SEO / Content Score</h3>
          <button type="button" class="ups-next-action js-ups-next-action" disabled>Napraw teraz</button>
          <div class="ups-score-head">
            <div class="ups-score-ring js-ups-score-ring">
              <div class="ups-score-value js-ups-score-value">0</div>
            </div>
            <div>
              <div style="font-size:13px;color:#4f4f48;font-weight:600;">Aktualna jakość wpisu</div>
              <div class="ups-score-state js-ups-score-state">Uzupełnij pola, aby rozpocząć analizę.</div>
            </div>
          </div>

          <div class="ups-score-bars">
            <div class="ups-score-row">
              <div class="ups-score-row-top"><span>Fundamenty SEO</span><strong class="js-score-seo">0/30</strong></div>
              <div class="ups-score-track"><div class="ups-score-fill js-score-fill-seo"></div></div>
            </div>
            <div class="ups-score-row">
              <div class="ups-score-row-top"><span>Pokrycie tematu</span><strong class="js-score-content">0/50</strong></div>
              <div class="ups-score-track"><div class="ups-score-fill js-score-fill-content"></div></div>
            </div>
            <div class="ups-score-row">
              <div class="ups-score-row-top"><span>Konwersja / Cel wpisu</span><strong class="js-score-conversion">0/20</strong></div>
              <div class="ups-score-track"><div class="ups-score-fill js-score-fill-conversion"></div></div>
            </div>
          </div>

          <div class="ups-score-metrics">
            <div class="ups-score-metric"><div class="ups-score-metric-name">Intent Match</div><div class="ups-score-metric-value js-metric-intent">0/35</div></div>
            <div class="ups-score-metric"><div class="ups-score-metric-name">Semantic Coverage</div><div class="ups-score-metric-value js-metric-semantic">0/20</div></div>
            <div class="ups-score-metric"><div class="ups-score-metric-name">Topical Coverage</div><div class="ups-score-metric-value js-metric-topical">0/15</div></div>
            <div class="ups-score-metric"><div class="ups-score-metric-name">Competitive Fit</div><div class="ups-score-metric-value js-metric-competitive">0/10</div></div>
            <div class="ups-score-metric"><div class="ups-score-metric-name">Opportunity Score</div><div class="ups-score-metric-value js-metric-opportunity">0/15</div></div>
            <div class="ups-score-metric"><div class="ups-score-metric-name">Internal Link Quality</div><div class="ups-score-metric-value js-metric-links">0/15</div></div>
            <div class="ups-score-metric"><div class="ups-score-metric-name">Snippet Strength</div><div class="ups-score-metric-value js-metric-snippet">0/15</div></div>
            <div class="ups-score-metric"><div class="ups-score-metric-name">Readability</div><div class="ups-score-metric-value js-metric-readability">0/10</div></div>
            <div class="ups-score-metric"><div class="ups-score-metric-name">Conversion Readiness</div><div class="ups-score-metric-value js-metric-conv-readiness">0/10</div></div>
            <div class="ups-score-metric"><div class="ups-score-metric-name">Trust Signals</div><div class="ups-score-metric-value js-metric-trust">0/10</div></div>
          </div>

          <div class="ups-critical-box">
            <label class="ups-mode-toggle"><input type="checkbox" class="js-ups-prepublish-mode" checked /> Tryb przed publikacją (wymaga score >=75 i brak krytycznych błędów)</label>
            <h4>Krytyczne błędy przed publikacją</h4>
            <ul class="ups-critical-list js-ups-critical-list"></ul>
            <span class="ups-ready-chip no js-ups-ready-chip">Not ready to publish</span>
          </div>

          <div class="ups-score-tips">
            <h4>Co poprawić teraz</h4>
            <ul class="ups-score-list js-ups-score-list"></ul>
            <span class="ups-ok-chip js-ups-ok-chip" style="display:none;">Świetnie — wpis jest gotowy do publikacji.</span>
          </div>

          <div class="ups-fix-box">
            <div class="ups-fix-title">Szybkie poprawki (1 click)</div>
            <div class="ups-fix-buttons">
              <button type="button" class="ups-fix-btn js-fix-btn" data-fix="add-h2">Dodaj brakujący H2</button>
              <button type="button" class="ups-fix-btn js-fix-btn" data-fix="add-faq">Dodaj sekcję FAQ</button>
              <button type="button" class="ups-fix-btn js-fix-btn" data-fix="add-links">Wstaw linkowanie wewnętrzne</button>
              <button type="button" class="ups-fix-btn js-fix-btn" data-fix="add-contact">Wstaw formularz leadowy</button>
              <button type="button" class="ups-fix-btn js-fix-btn" data-fix="optimize-meta">Uzupełnij SEO title + meta</button>
            </div>
          </div>

          <div class="ups-delta-box">
            Zmiana względem poprzedniej wersji: <span class="js-ups-score-delta">0 pkt</span>
          </div>

          <div class="ups-heatmap">
            <div class="ups-heatmap-title">Heatmapa sekcji wpisu</div>
            <ul class="ups-heatmap-list js-ups-heatmap-list"></ul>
          </div>

          <div class="ups-posts-box">
            <h4>Widok wpisów blogowych - szybka korekta</h4>
            <ul class="ups-posts-list">
              <?php foreach ($existing_posts as $existing_post) : ?>
                <?php $row_post_id = (int) $existing_post->ID; ?>
                <li class="ups-post-item">
                  <p class="ups-post-title"><?php echo esc_html(get_the_title($row_post_id)); ?></p>
                  <div class="ups-post-actions">
                    <a class="ups-quick-edit-btn" href="<?php echo esc_url(add_query_arg("quick_edit_post_id", $row_post_id, menu_page_url("upsellio-seo-blog-tool", false))); ?>">
                      Szybka korekta SEO
                    </a>
                    <a class="ups-post-link" href="<?php echo esc_url(get_edit_post_link($row_post_id)); ?>">Edycja WP</a>
                    <a class="ups-post-link" href="<?php echo esc_url(get_permalink($row_post_id)); ?>" target="_blank" rel="noopener">Podgląd</a>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </aside>
      </div>

      <script>
        (function () {
          const form = document.querySelector(".js-ups-seo-form");
          if (!form) return;

          const existingPosts = <?php echo wp_json_encode($existing_posts_data); ?> || [];

          const ring = document.querySelector(".js-ups-score-ring");
          const scoreValue = document.querySelector(".js-ups-score-value");
          const scoreState = document.querySelector(".js-ups-score-state");
          const nextActionButton = document.querySelector(".js-ups-next-action");
          const scoreList = document.querySelector(".js-ups-score-list");
          const criticalList = document.querySelector(".js-ups-critical-list");
          const okChip = document.querySelector(".js-ups-ok-chip");
          const readyChip = document.querySelector(".js-ups-ready-chip");
          const prepublishMode = document.querySelector(".js-ups-prepublish-mode");
          const heatmapList = document.querySelector(".js-ups-heatmap-list");
          const scoreDelta = document.querySelector(".js-ups-score-delta");
          const metricIntent = document.querySelector(".js-metric-intent");
          const metricSemantic = document.querySelector(".js-metric-semantic");
          const metricTopical = document.querySelector(".js-metric-topical");
          const metricCompetitive = document.querySelector(".js-metric-competitive");
          const metricOpportunity = document.querySelector(".js-metric-opportunity");
          const metricLinks = document.querySelector(".js-metric-links");
          const metricSnippet = document.querySelector(".js-metric-snippet");
          const metricReadability = document.querySelector(".js-metric-readability");
          const metricConvReadiness = document.querySelector(".js-metric-conv-readiness");
          const metricTrust = document.querySelector(".js-metric-trust");
          const seoLabel = document.querySelector(".js-score-seo");
          const contentLabel = document.querySelector(".js-score-content");
          const conversionLabel = document.querySelector(".js-score-conversion");
          const seoFill = document.querySelector(".js-score-fill-seo");
          const contentFill = document.querySelector(".js-score-fill-content");
          const conversionFill = document.querySelector(".js-score-fill-conversion");
          const fixButtons = Array.from(document.querySelectorAll(".js-fix-btn"));

          const fields = {
            post_title: form.querySelector('[name="post_title"]'),
            primary_query: form.querySelector('[name="primary_query"]'),
            query_cluster: form.querySelector('[name="query_cluster"]'),
            user_questions: form.querySelector('[name="user_questions"]'),
            article_type: form.querySelector('[name="article_type"]'),
            seo_title: form.querySelector('[name="seo_title"]'),
            seo_description: form.querySelector('[name="seo_description"]'),
            search_intent: form.querySelector('[name="search_intent"]'),
            audience: form.querySelector('[name="audience"]'),
            problem: form.querySelector('[name="problem"]'),
            outcome: form.querySelector('[name="outcome"]'),
            cta_text: form.querySelector('[name="cta_text"]'),
            content_template: form.querySelector('[name="content_template"]'),
            category_id: form.querySelector('[name="category_id"]'),
            tags: form.querySelector('[name="tags"]'),
            featured_image_url: form.querySelector('[name="featured_image_url"]'),
            competitive_notes: form.querySelector('[name="competitive_notes"]')
          };

          const metricRanges = {
            intentMatch: 35,
            semanticCoverage: 20,
            topicalCoverage: 15,
            competitiveFit: 10,
            opportunityScore: 15,
            internalLinkQuality: 15,
            snippetStrength: 15,
            readability: 10,
            conversionReadiness: 10,
            trustSignals: 10
          };

          const storageKey = "upsellio-seo-tool-last-score";
          const baselineScore = Number(localStorage.getItem(storageKey) || "0");
          let actionQueue = [];
          let actionQueueIndex = 0;
          let actionQueueSignature = "";

          function textValue(key) {
            return (fields[key]?.value || "").trim();
          }

          function plainText(value) {
            return (value || "")
              .toLowerCase()
              .replace(/<[^>]*>/g, " ")
              .replace(/[^\p{L}\p{N}\s]/gu, " ");
          }

          function tokenize(value) {
            return plainText(value)
              .split(/\s+/)
              .map((token) => token.trim())
              .filter((token) => token.length > 2);
          }

          function uniqueTokens(value) {
            return Array.from(new Set(tokenize(value)));
          }

          function countWords(text) {
            return text.replace(/<[^>]*>/g, " ").trim().split(/\s+/).filter(Boolean).length;
          }

          function addSuggestion(bucket, html, targetField) {
            bucket.push({ html, targetField: targetField || "" });
          }

          function uniqueSuggestionItems(items) {
            const map = new Map();
            items.forEach((item) => {
              const key = item.html;
              if (!map.has(key)) map.set(key, item);
            });
            return Array.from(map.values());
          }

          function jaccard(aTokens, bTokens) {
            const setA = new Set(aTokens);
            const setB = new Set(bTokens);
            if (!setA.size || !setB.size) return 0;
            let intersection = 0;
            setA.forEach((token) => {
              if (setB.has(token)) intersection += 1;
            });
            const union = setA.size + setB.size - intersection;
            return union ? intersection / union : 0;
          }

          function parseMultiValueList(value) {
            return (value || "")
              .split(/[\n,;]+/)
              .map((item) => item.trim())
              .filter(Boolean);
          }

          function getSemanticTargets(primaryQuery, queryCluster, userQuestions, intent) {
            const base = uniqueTokens([primaryQuery, queryCluster, userQuestions].join(" "));
            const intentMap = {
              informacyjna: ["co", "dlaczego", "jak", "krok", "błędy", "wskazówki"],
              porównawcza: ["porównanie", "różnice", "vs", "zalety", "wady", "najlepszy"],
              decyzyjna: ["wdrożenie", "plan", "priorytet", "audyt", "rekomendacje", "efekt"],
              transakcyjna: ["oferta", "cena", "kontakt", "formularz", "konsultacja", "wdrożenie"]
            };
            const intentKey = plainText(intent).trim();
            const intentTokens = intentMap[intentKey] || intentMap.decyzyjna;
            return Array.from(new Set([...base, ...intentTokens]));
          }

          function scoreSnippetStrength(title, seoTitle, seoDescription, primaryQuery) {
            let score = 0;
            const checks = [];
            const titleToCheck = seoTitle || title;

            if (titleToCheck.length >= 45 && titleToCheck.length <= 60) {
              score += 5;
            } else {
              addSuggestion(checks, "<strong>Snippet:</strong> SEO title 45-60 znaków (CMS dokleja nazwę witryny).", "seo_title");
            }

            if (seoDescription.length >= 140 && seoDescription.length <= 160) {
              score += 6;
            } else {
              addSuggestion(checks, "<strong>Snippet:</strong> meta description ustaw na 140-160 znaków.", "seo_description");
            }

            if (primaryQuery && plainText(titleToCheck).includes(plainText(primaryQuery))) {
              score += 2;
            } else {
              addSuggestion(checks, "<strong>Snippet:</strong> umieść frazę główną w SEO title.", "seo_title");
            }

            if (/\d/.test(titleToCheck) || /jak|dlaczego|kiedy|co\b/i.test(titleToCheck)) {
              score += 2;
            } else {
              addSuggestion(checks, "<strong>Snippet:</strong> dodaj element zwiększający CTR (liczba/pytanie).", "seo_title");
            }

            return { score: Math.min(metricRanges.snippetStrength, score), checks };
          }

          function scoreReadability(content) {
            const plain = content.replace(/<[^>]*>/g, " ");
            const sentences = plain.split(/[.!?]+/).map((item) => item.trim()).filter(Boolean);
            const words = plain.split(/\s+/).map((item) => item.trim()).filter(Boolean);
            const avgSentence = sentences.length ? words.length / sentences.length : 0;
            const paragraphs = content.split(/<\/p>/i).filter(Boolean).length;

            let score = 0;
            const checks = [];

            if (avgSentence > 0 && avgSentence <= 20) score += 5;
            else addSuggestion(checks, "<strong>Czytelność:</strong> skróć zdania (średnio <= 20 słów).", "content_template");

            if (paragraphs >= 5) score += 3;
            else addSuggestion(checks, "<strong>Czytelność:</strong> podziel wpis na więcej akapitów.", "content_template");

            if (/<ul[\s>]|<ol[\s>]/i.test(content)) score += 2;
            else addSuggestion(checks, "<strong>Czytelność:</strong> dodaj listę punktowaną dla skanowalności.", "content_template");

            return { score: Math.min(metricRanges.readability, score), checks };
          }

          function scoreTrustSignals(content) {
            let score = 0;
            const checks = [];
            const hasNumber = /\d{2,}/.test(content);
            const hasSourceLink = /https?:\/\//i.test(content);
            const hasQuote = /<blockquote[\s>]/i.test(content);
            const hasUpdatePhrase = /aktualizac|badani|raport|dane/i.test(plainText(content));

            if (hasNumber) score += 3;
            else addSuggestion(checks, "<strong>Trust:</strong> dodaj dane/liczby wzmacniające wiarygodność.", "content_template");

            if (hasSourceLink) score += 3;
            else addSuggestion(checks, "<strong>Trust:</strong> podeprzyj tezy źródłem lub referencją.", "content_template");

            if (hasQuote) score += 2;
            else addSuggestion(checks, "<strong>Trust:</strong> dodaj blok cytatu / insight.", "content_template");

            if (hasUpdatePhrase) score += 2;
            else addSuggestion(checks, "<strong>Trust:</strong> dodaj kontekst aktualności (np. aktualizacja, raport).", "content_template");

            return { score: Math.min(metricRanges.trustSignals, score), checks };
          }

          function scoreInternalLinks(content) {
            let score = 0;
            const checks = [];
            const shortcodeMatches = (content.match(/\[upsellio_internal_links/gi) || []).length;
            const htmlLinks = (content.match(/<a\s+[^>]*href=/gi) || []).length;
            const uniqueAnchors = new Set((content.match(/<a\s+[^>]*>(.*?)<\/a>/gi) || []).map((item) => item.replace(/<[^>]*>/g, "").trim()).filter(Boolean));

            if (shortcodeMatches > 0) score += 6;
            else addSuggestion(checks, "<strong>Linkowanie:</strong> dodaj shortcode [upsellio_internal_links].", "content_template");

            if (htmlLinks >= 2) score += 4;
            else addSuggestion(checks, "<strong>Linkowanie:</strong> dodaj min. 2 linki kontekstowe w treści.", "content_template");

            if (uniqueAnchors.size >= 2) score += 3;
            else addSuggestion(checks, "<strong>Linkowanie:</strong> zróżnicuj anchory linków wewnętrznych.", "content_template");

            if ((shortcodeMatches + htmlLinks) >= 4) score += 2;

            return { score: Math.min(metricRanges.internalLinkQuality, score), checks };
          }

          function scoreIntentMatch(content, primaryQuery, queryCluster, userQuestions, intent, gscTopQueries) {
            const targetTokens = getSemanticTargets(primaryQuery, queryCluster, userQuestions, intent);
            const contentTokens = uniqueTokens(content);
            const overlap = targetTokens.filter((token) => contentTokens.includes(token)).length;
            const declaredRatio = targetTokens.length ? overlap / targetTokens.length : 0;
            const factQueries = Array.isArray(gscTopQueries) ? gscTopQueries.slice(0, 12) : [];
            let factCoverage = 0;
            if (factQueries.length) {
              const covered = factQueries.filter((row) => {
                const tokens = uniqueTokens(row.query || "");
                if (!tokens.length) return false;
                return tokens.some((token) => contentTokens.includes(token));
              }).length;
              factCoverage = covered / factQueries.length;
            }
            const blendedRatio = factQueries.length ? ((factCoverage * 0.7) + (declaredRatio * 0.3)) : declaredRatio;
            const score = Math.min(metricRanges.intentMatch, Math.round(blendedRatio * metricRanges.intentMatch));
            const checks = [];

            if (score < 22) {
              addSuggestion(checks, "<strong>Intent:</strong> rozwiń treść pod deklarowaną intencję wyszukiwania.", "content_template");
            }
            return { score, checks, targetTokens };
          }

          function scoreSemanticCoverage(content, targetTokens, userQuestions) {
            const contentTokens = uniqueTokens(content);
            const covered = targetTokens.filter((token) => contentTokens.includes(token)).length;
            const ratio = targetTokens.length ? covered / targetTokens.length : 0;
            const score = Math.min(metricRanges.semanticCoverage, Math.round(ratio * metricRanges.semanticCoverage));
            const missing = targetTokens.filter((token) => !contentTokens.includes(token)).slice(0, 6);
            const checks = [];
            const questionList = parseMultiValueList(userQuestions);
            const unansweredQuestions = questionList.filter((question) => {
              const questionTokens = uniqueTokens(question);
              if (!questionTokens.length) return false;
              return !questionTokens.some((token) => contentTokens.includes(token));
            });

            if (missing.length) {
              addSuggestion(checks, "<strong>Semantyka:</strong> dodaj brakujące podtematy: " + missing.join(", ") + ".", "content_template");
            }
            if (unansweredQuestions.length) {
              addSuggestion(checks, "<strong>Semantyka:</strong> brakuje odpowiedzi na pytania: " + unansweredQuestions.slice(0, 3).join(", ") + ".", "user_questions");
            }

            return { score, checks, missing };
          }

          function scoreTopicalCoverage(content, queryCluster, userQuestions) {
            const contentTokens = uniqueTokens(content);
            const clusterItems = parseMultiValueList(queryCluster);
            const questionItems = parseMultiValueList(userQuestions);
            const checks = [];

            if (!clusterItems.length && !questionItems.length) {
              addSuggestion(checks, "<strong>Topical coverage:</strong> dodaj query cluster i pytania użytkowników.", "query_cluster");
              return { score: 0, checks };
            }

            let coveredCluster = 0;
            clusterItems.forEach((item) => {
              const tokens = uniqueTokens(item);
              if (tokens.length && tokens.some((token) => contentTokens.includes(token))) coveredCluster += 1;
            });

            let coveredQuestions = 0;
            questionItems.forEach((item) => {
              const tokens = uniqueTokens(item);
              if (tokens.length && tokens.some((token) => contentTokens.includes(token))) coveredQuestions += 1;
            });

            const clusterRatio = clusterItems.length ? coveredCluster / clusterItems.length : 0;
            const questionRatio = questionItems.length ? coveredQuestions / questionItems.length : 0;
            const blended = (clusterItems.length ? clusterRatio * 0.6 : 0) + (questionItems.length ? questionRatio * 0.4 : 0);
            const score = Math.min(metricRanges.topicalCoverage, Math.round(blended * metricRanges.topicalCoverage));

            if (clusterItems.length && clusterRatio < 0.55) {
              addSuggestion(checks, "<strong>Topical coverage:</strong> rozwiń podtematy z query cluster.", "query_cluster");
            }
            if (questionItems.length && questionRatio < 0.6) {
              addSuggestion(checks, "<strong>Topical coverage:</strong> odpowiedz na więcej pytań użytkowników.", "user_questions");
            }

            return { score, checks };
          }

          function scoreCompetitiveFit(content, competitiveNotes) {
            const notes = parseMultiValueList(competitiveNotes);
            const checks = [];
            if (!notes.length) {
              addSuggestion(checks, "<strong>Konkurencja:</strong> wklej benchmark TOP10, aby ocenić luki tematyczne.", "competitive_notes");
              return { score: 0, checks };
            }

            const contentTokens = uniqueTokens(content);
            const missingTopics = [];
            let covered = 0;
            notes.forEach((note) => {
              const noteTokens = uniqueTokens(note);
              if (!noteTokens.length) return;
              const isCovered = noteTokens.some((token) => contentTokens.includes(token));
              if (isCovered) covered += 1;
              else missingTopics.push(note);
            });

            const ratio = notes.length ? covered / notes.length : 0;
            const score = Math.min(metricRanges.competitiveFit, Math.round(ratio * metricRanges.competitiveFit));
            if (missingTopics.length) {
              addSuggestion(checks, "<strong>Konkurencja:</strong> brakuje sekcji względem TOP10: " + missingTopics.slice(0, 3).join(", ") + ".", "competitive_notes");
            }

            return { score, checks };
          }

          function scoreOpportunity(competitiveNotes) {
            const checks = [];
            const notes = parseMultiValueList(competitiveNotes);
            if (!notes.length) {
              addSuggestion(checks, "<strong>Opportunity:</strong> uzupełnij benchmark TOP10, aby wskazać luki tematyczne.", "competitive_notes");
              return { score: 0, checks };
            }
            const score = Math.min(metricRanges.opportunityScore, Math.min(15, notes.length * 3));
            if (score < 10) {
              addSuggestion(checks, "<strong>Opportunity:</strong> porównaj treść z notatkami konkurencji i dodaj brakujące podtematy.", "competitive_notes");
            }
            return { score, checks };
          }

          function scoreConversionReadiness(content, cta, problem, outcome, articleType) {
            let score = 0;
            const checks = [];
            const needsLeadForm = articleType === "seo_article" || articleType === "landing_sales";
            if (content.includes("[upsellio_contact_form]")) score += 4;
            else if (needsLeadForm) addSuggestion(checks, "<strong>Konwersja:</strong> wstaw formularz [upsellio_contact_form].", "content_template");
            else score += 2;

            if (cta.length >= 12) score += 2;
            else addSuggestion(checks, "<strong>Konwersja:</strong> doprecyzuj CTA końcowe.", "cta_text");

            if (problem.length >= 80) score += 2;
            else addSuggestion(checks, "<strong>Konwersja:</strong> opisz konkretny problem odbiorcy.", "problem");

            if (outcome.length >= 80) score += 2;
            else addSuggestion(checks, "<strong>Konwersja:</strong> doprecyzuj oczekiwany efekt.", "outcome");

            return { score: Math.min(metricRanges.conversionReadiness, score), checks };
          }

          function detectCannibalization(title, focusKeyword) {
            const queryTokens = uniqueTokens([title, focusKeyword].join(" "));
            if (!queryTokens.length) return [];

            const matches = existingPosts
              .map((post) => {
                const postTokens = uniqueTokens([post.title, post.excerpt, (post.tags || []).join(" ")].join(" "));
                const similarity = jaccard(queryTokens, postTokens);
                return { post, similarity };
              })
              .filter((item) => item.similarity >= 0.2)
              .sort((a, b) => b.similarity - a.similarity)
              .slice(0, 3);

            return matches;
          }

          function parseHeatmapSections(content) {
            const sections = [];
            const headingRegex = /<(h2|h3)[^>]*>(.*?)<\/\1>/gi;
            let match;
            let lastIndex = 0;
            let currentTitle = "Wprowadzenie";

            while ((match = headingRegex.exec(content)) !== null) {
              const segment = content.slice(lastIndex, match.index);
              sections.push({ title: currentTitle, content: segment });
              currentTitle = match[2].replace(/<[^>]*>/g, "").trim() || "Sekcja";
              lastIndex = headingRegex.lastIndex;
            }
            sections.push({ title: currentTitle, content: content.slice(lastIndex) });
            return sections.filter((item) => item.content.trim() !== "");
          }

          function sectionQuality(section, keywordTokens) {
            const words = countWords(section.content);
            const tokens = uniqueTokens(section.content);
            const overlap = keywordTokens.filter((token) => tokens.includes(token)).length;
            const hasList = /<ul[\s>]|<ol[\s>]/i.test(section.content);
            let score = 0;
            if (words >= 120) score += 45;
            else if (words >= 70) score += 30;
            else score += 15;
            if (keywordTokens.length) score += Math.min(35, overlap * 7);
            if (hasList) score += 20;
            return Math.min(100, score);
          }

          function getScoreColor(total) {
            if (total < 50) return "#d14c4c";
            if (total < 75) return "#e18d2d";
            return "#0d9488";
          }

          function getState(total) {
            if (total < 50) return "Niski potencjał SEO - wymaga dopracowania.";
            if (total < 75) return "Solidna baza - dopracuj detale przed publikacją.";
            if (total < 90) return "Bardzo dobry wynik - wpis blisko wersji docelowej.";
            return "Świetny wynik - wpis jest SEO-ready i gotowy do publikacji.";
          }

          function applyQuickFix(action) {
            const contentField = fields.content_template;
            if (!contentField) return;

            const content = contentField.value || "";
            if (action === "add-h2" && !/<h2[\s>]/i.test(content)) {
              contentField.value = content + "\n\n<h2>Najważniejsze wnioski</h2>\n<p>Uzupełnij tę sekcję konkretnymi rekomendacjami.</p>";
            }
            if (action === "add-faq" && !/<h2[^>]*>.*faq/i.test(content)) {
              contentField.value = contentField.value + "\n\n<h2>FAQ</h2>\n<h3>Najczęstsze pytanie</h3>\n<p>Dodaj odpowiedź opartą o praktykę.</p>";
            }
            if (action === "add-links" && !/\[upsellio_internal_links/i.test(contentField.value)) {
              contentField.value = contentField.value + "\n\n[upsellio_internal_links limit=\"3\" title=\"Powiązane artykuły\"]";
            }
            if (action === "add-contact" && !/\[upsellio_contact_form\]/i.test(contentField.value)) {
              contentField.value = contentField.value + "\n\n[upsellio_contact_form]";
            }
            if (action === "optimize-meta") {
              const title = textValue("post_title");
              const keyword = textValue("primary_query");
              if (!textValue("seo_title") && fields.seo_title) {
                fields.seo_title.value = title ? `${title} | Upsellio` : "";
              }
              if (!textValue("seo_description") && fields.seo_description) {
                fields.seo_description.value = keyword
                  ? `Sprawdź praktyczne wskazówki: ${keyword}. Konkretne kroki, najczęstsze błędy i rekomendacje wdrożeniowe.`
                  : "Praktyczny poradnik z konkretnymi krokami, błędami i rekomendacjami wdrożeniowymi.";
              }
            }
            render();
          }

          function focusField(fieldName) {
            const input = fields[fieldName];
            if (!input) return;
            const wrapper = input.closest(".ups-field") || input;
            wrapper.scrollIntoView({ behavior: "smooth", block: "center" });
            wrapper.classList.add("ups-field-highlight");
            input.focus({ preventScroll: true });
            setTimeout(() => wrapper.classList.remove("ups-field-highlight"), 1500);
          }

          function setActionQueue(items) {
            const normalized = (items || []).filter((item) => item && item.targetField);
            const signature = normalized.map((item) => `${item.targetField}|${item.html}`).join("||");
            if (signature !== actionQueueSignature) {
              actionQueueIndex = 0;
              actionQueueSignature = signature;
            }
            actionQueue = normalized;
            renderNextAction();
          }

          function renderNextAction() {
            if (!nextActionButton) return;
            const fieldNames = {
              post_title: "uzupełnij tytuł",
              primary_query: "dodaj frazę główną",
              query_cluster: "uzupełnij query cluster",
              user_questions: "dodaj pytania użytkowników",
              seo_title: "popraw SEO title",
              seo_description: "uzupełnij meta description",
              category_id: "wybierz kategorię",
              tags: "uzupełnij tagi",
              content_template: "dopracuj treść",
              problem: "doprecyzuj problem",
              outcome: "doprecyzuj outcome",
              cta_text: "doprecyzuj CTA",
              competitive_notes: "dodaj benchmark konkurencji"
            };
            if (!actionQueue.length) {
              nextActionButton.disabled = true;
              nextActionButton.textContent = "Napraw teraz";
              nextActionButton.removeAttribute("data-target-field");
              nextActionButton.removeAttribute("data-queue-index");
              return;
            }

            const item = actionQueue[actionQueueIndex] || actionQueue[0];
            const label = item.label || fieldNames[item.targetField] || "przejdź do pola";
            const currentStep = actionQueueIndex + 1;
            const totalSteps = actionQueue.length;
            const remaining = totalSteps - currentStep;

            nextActionButton.disabled = false;
            nextActionButton.textContent = `Napraw teraz (${currentStep}/${totalSteps}): ${label} · zostało ${Math.max(0, remaining)}`;
            nextActionButton.setAttribute("data-target-field", item.targetField);
            nextActionButton.setAttribute("data-queue-index", String(actionQueueIndex));
          }

          function render() {
            const title = textValue("post_title");
            const primaryQuery = textValue("primary_query");
            const queryCluster = textValue("query_cluster");
            const userQuestions = textValue("user_questions");
            const articleType = textValue("article_type");
            const seoTitle = textValue("seo_title");
            const seoDescription = textValue("seo_description");
            const audience = textValue("audience");
            const problem = textValue("problem");
            const outcome = textValue("outcome");
            const cta = textValue("cta_text");
            const content = textValue("content_template");
            const tagsValue = textValue("tags");
            const categoryId = textValue("category_id");
            const competitiveNotes = textValue("competitive_notes");

            let seoScore = 0;
            let contentScore = 0;
            let conversionScore = 0;
            const suggestions = [];
            const critical = [];

            if (title.length >= 45 && title.length <= 65) seoScore += 10;
            else addSuggestion(suggestions, "<strong>Tytuł:</strong> celuj w 45-65 znaków.", "post_title");

            if (primaryQuery.length >= 3) seoScore += 8;
            else {
              addSuggestion(suggestions, "<strong>Primary query:</strong> dodaj główną frazę.", "primary_query");
              critical.push("Brak frazy głównej (primary query).");
            }

            if (seoTitle.length >= 45 && seoTitle.length <= 60) seoScore += 8;
            else addSuggestion(suggestions, "<strong>SEO title:</strong> ustaw 45-60 znaków (max przed sufiksem witryny).", "seo_title");

            if (seoDescription.length >= 140 && seoDescription.length <= 160) seoScore += 9;
            else {
              addSuggestion(suggestions, "<strong>Meta description:</strong> ustaw 140-160 znaków.", "seo_description");
              if (seoDescription.length < 120) critical.push("Meta description jest za krótki.");
            }

            if (categoryId && categoryId !== "0") seoScore += 5;
            else addSuggestion(suggestions, "<strong>Kategoria:</strong> wybierz kategorię.", "category_id");

            const tagsCount = tagsValue ? tagsValue.split(",").map((item) => item.trim()).filter(Boolean).length : 0;
            if (tagsCount >= 3) seoScore += 5;
            else addSuggestion(suggestions, "<strong>Tagi:</strong> dodaj min. 3 tagi.", "tags");

            const words = countWords(content);
            if (words >= 700) contentScore += 15;
            else if (words >= 400) contentScore += 10;
            else {
              addSuggestion(suggestions, "<strong>Treść:</strong> zwiększ długość wpisu (docelowo 700+ słów).", "content_template");
              critical.push("Treść wpisu jest zbyt krótka.");
            }

            const h2Count = (content.match(/<h2[\s>]/gi) || []).length;
            const h3Count = (content.match(/<h3[\s>]/gi) || []).length;
            if (h2Count >= 3) contentScore += 10;
            else addSuggestion(suggestions, "<strong>Struktura:</strong> dodaj co najmniej 3 sekcje H2.", "content_template");
            if (h3Count >= 2) contentScore += 5;
            else addSuggestion(suggestions, "<strong>Struktura:</strong> dodaj podsekcje H3.", "content_template");

            if (/\[upsellio_internal_links/i.test(content)) contentScore += 5;
            else addSuggestion(suggestions, "<strong>SEO wewnętrzne:</strong> dodaj shortcode [upsellio_internal_links].", "content_template");

            if (/\[upsellio_contact_form\]/i.test(content)) conversionScore += 10;
            else {
              if (articleType === "seo_article" || articleType === "landing_sales") {
                addSuggestion(suggestions, "<strong>Lead generation:</strong> dodaj [upsellio_contact_form].", "content_template");
                critical.push("Brak formularza pozyskiwania klientów w treści.");
              }
            }
            if (problem.length >= 80) conversionScore += 5;
            else addSuggestion(suggestions, "<strong>Problem:</strong> doprecyzuj problem odbiorcy (>= 80 znaków).", "problem");
            if (outcome.length >= 80) conversionScore += 5;
            else addSuggestion(suggestions, "<strong>Outcome:</strong> doprecyzuj oczekiwany efekt (>= 80 znaków).", "outcome");
            if (cta.length >= 12) conversionScore += 5;
            else addSuggestion(suggestions, "<strong>CTA:</strong> użyj bardziej konkretnego wezwania do działania.", "cta_text");

            const intentResult = scoreIntentMatch(content, primaryQuery, queryCluster, userQuestions, textValue("search_intent"), []);
            const semanticResult = scoreSemanticCoverage(content, intentResult.targetTokens, userQuestions);
            const topicalResult = scoreTopicalCoverage(content, queryCluster, userQuestions);
            const competitiveResult = scoreCompetitiveFit(content, competitiveNotes);
            const opportunityResult = scoreOpportunity(competitiveNotes);
            const linksResult = scoreInternalLinks(content);
            const snippetResult = scoreSnippetStrength(title, seoTitle, seoDescription, primaryQuery);
            const readabilityResult = scoreReadability(content);
            const trustResult = scoreTrustSignals(content);
            const conversionReadinessResult = scoreConversionReadiness(content, cta, problem, outcome, articleType);
            const cannibalization = detectCannibalization(title, primaryQuery);

            suggestions.push(...intentResult.checks, ...semanticResult.checks, ...topicalResult.checks, ...competitiveResult.checks, ...opportunityResult.checks, ...linksResult.checks, ...snippetResult.checks, ...readabilityResult.checks, ...trustResult.checks, ...conversionReadinessResult.checks);

            if (cannibalization.length) {
              addSuggestion(suggestions, "<strong>Kanibalizacja:</strong> podobne wpisy już istnieją. Rozważ linkowanie zamiast duplikacji tematu.", "post_title");
            }

            seoScore = Math.min(30, seoScore);
            contentScore = Math.min(50, contentScore);
            conversionScore = Math.min(20, conversionScore);
            const weightedMetrics = intentResult.score + semanticResult.score + topicalResult.score + competitiveResult.score + opportunityResult.score;
            const structuralSupport = Math.round((seoScore + contentScore + conversionScore) * 0.2);
            const total = Math.min(100, weightedMetrics + structuralSupport);
            const color = getScoreColor(total);
            const deg = Math.round((total / 100) * 360);

            ring.style.setProperty("--score-deg", deg + "deg");
            ring.style.setProperty("--score-color", color);
            scoreValue.textContent = String(total);
            scoreState.textContent = getState(total);

            seoLabel.textContent = `${seoScore}/30`;
            contentLabel.textContent = `${contentScore}/50`;
            conversionLabel.textContent = `${conversionScore}/20`;
            seoFill.style.width = `${(seoScore / 30) * 100}%`;
            contentFill.style.width = `${(contentScore / 50) * 100}%`;
            conversionFill.style.width = `${(conversionScore / 20) * 100}%`;
            seoFill.style.background = color;
            contentFill.style.background = color;
            conversionFill.style.background = color;

            metricIntent.textContent = `${intentResult.score}/${metricRanges.intentMatch}`;
            metricSemantic.textContent = `${semanticResult.score}/${metricRanges.semanticCoverage}`;
            metricTopical.textContent = `${topicalResult.score}/${metricRanges.topicalCoverage}`;
            metricCompetitive.textContent = `${competitiveResult.score}/${metricRanges.competitiveFit}`;
            metricOpportunity.textContent = `${opportunityResult.score}/${metricRanges.opportunityScore}`;
            metricLinks.textContent = `${linksResult.score}/${metricRanges.internalLinkQuality}`;
            metricSnippet.textContent = `${snippetResult.score}/${metricRanges.snippetStrength}`;
            metricReadability.textContent = `${readabilityResult.score}/${metricRanges.readability}`;
            metricConvReadiness.textContent = `${conversionReadinessResult.score}/${metricRanges.conversionReadiness}`;
            metricTrust.textContent = `${trustResult.score}/${metricRanges.trustSignals}`;

            const strictMode = prepublishMode ? prepublishMode.checked : true;
            const isReady = strictMode ? (!critical.length && total >= 75) : (total >= 65);
            if (isReady) {
              readyChip.className = "ups-ready-chip yes js-ups-ready-chip";
              readyChip.textContent = "Ready to publish";
            } else {
              readyChip.className = "ups-ready-chip no js-ups-ready-chip";
              readyChip.textContent = "Not ready to publish";
            }

            criticalList.innerHTML = critical.length
              ? critical.map((item) => `<li>${item}</li>`).join("")
              : "<li>Brak krytycznych błędów.</li>";

            if (suggestions.length === 0) {
              scoreList.innerHTML = "";
              okChip.style.display = "inline-flex";
              setActionQueue([]);
            } else {
              okChip.style.display = "none";
              const canonList = uniqueSuggestionItems(suggestions).slice(0, 9);
              if (cannibalization.length) {
                const cannibalHtml = cannibalization
                  .map((item) => `${item.post.title} (${Math.round(item.similarity * 100)}%)`)
                  .join(", ");
                canonList.unshift({ html: `<strong>Kanibalizacja:</strong> podobne tematy: ${cannibalHtml}.`, targetField: "post_title", label: "zweryfikuj temat wpisu" });
              }
              const actionableQueue = canonList.filter((item) => item.targetField);
              setActionQueue(actionableQueue);
              scoreList.innerHTML = canonList.map((tip) => {
                if (tip.targetField) {
                  return `<li><button type="button" class="ups-score-jump" data-target-field="${tip.targetField}">${tip.html}</button></li>`;
                }
                return `<li>${tip.html}</li>`;
              }).join("");
            }
            if (!suggestions.length) setActionQueue([]);

            const delta = total - baselineScore;
            if (delta > 0) {
              scoreDelta.innerHTML = `<span class="ups-delta-up">+${delta} pkt</span>`;
            } else if (delta < 0) {
              scoreDelta.innerHTML = `<span class="ups-delta-down">${delta} pkt</span>`;
            } else {
              scoreDelta.textContent = "0 pkt";
            }

            const keywordTokens = uniqueTokens([title, primaryQuery, queryCluster].join(" "));
            const sections = parseHeatmapSections(content);
            if (!sections.length) {
              heatmapList.innerHTML = "<li class=\"ups-heatmap-item\"><span class=\"ups-heatmap-item-name\">Brak sekcji do analizy</span><span class=\"ups-heatmap-badge bad\">brak</span></li>";
            } else {
              heatmapList.innerHTML = sections.slice(0, 8).map((section) => {
                const quality = sectionQuality(section, keywordTokens);
                let cls = "bad";
                let label = "niska";
                if (quality >= 70) { cls = "good"; label = "wysoka"; }
                else if (quality >= 45) { cls = "mid"; label = "średnia"; }
                const shortTitle = section.title.length > 44 ? section.title.slice(0, 44) + "..." : section.title;
                return `<li class="ups-heatmap-item"><span class="ups-heatmap-item-name">${shortTitle}</span><span class="ups-heatmap-badge ${cls}">${label}</span></li>`;
              }).join("");
            }
          }

          fixButtons.forEach((button) => {
            button.addEventListener("click", function () {
              const action = this.getAttribute("data-fix");
              applyQuickFix(action);
            });
          });

          if (scoreList) {
            scoreList.addEventListener("click", function (event) {
              const trigger = event.target.closest("[data-target-field]");
              if (!trigger) return;
              const fieldName = trigger.getAttribute("data-target-field");
              if (!fieldName) return;
              const queueMatchIndex = actionQueue.findIndex((item) => item.targetField === fieldName);
              if (queueMatchIndex >= 0) {
                actionQueueIndex = queueMatchIndex;
                renderNextAction();
              }
              focusField(fieldName);
            });
          }

          if (nextActionButton) {
            nextActionButton.addEventListener("click", function () {
              const fieldName = this.getAttribute("data-target-field");
              if (!fieldName) return;
              focusField(fieldName);
              if (actionQueue.length > 1) {
                actionQueueIndex = actionQueueIndex >= actionQueue.length - 1 ? 0 : actionQueueIndex + 1;
              }
              renderNextAction();
            });
          }

          form.addEventListener("input", render);
          form.addEventListener("change", render);
          if (prepublishMode) prepublishMode.addEventListener("change", render);
          form.addEventListener("submit", () => {
            const currentScore = Number(scoreValue.textContent || "0");
            localStorage.setItem(storageKey, String(currentScore));
          });
          render();
        })();
      </script>
    </div>
    <?php
}

