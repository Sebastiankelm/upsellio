<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_post_editor_seo_claude_append_log(int $post_id, string $line): void
{
    $post_id = max(1, $post_id);
    $line = "[" . current_time("mysql") . "] " . trim($line);
    $prev = (string) get_post_meta($post_id, "_upsellio_seo_ai_log", true);
    $lines = array_values(array_filter(explode("\n", $prev), static function ($l) {
        return trim((string) $l) !== "";
    }));
    $lines[] = $line;
    $lines = array_slice($lines, -80);
    update_post_meta($post_id, "_upsellio_seo_ai_log", implode("\n", $lines));
}

function upsellio_post_editor_seo_claude_register_metabox(): void
{
    add_meta_box(
        "upsellio_post_seo_claude",
        "SEO — Claude (Upsellio)",
        "upsellio_post_editor_seo_claude_metabox_cb",
        "post",
        "normal",
        "default",
        ["__back_compat_meta_box" => true]
    );
}
add_action("add_meta_boxes", "upsellio_post_editor_seo_claude_register_metabox");

function upsellio_post_editor_seo_claude_metabox_cb(WP_Post $post): void
{
    if (!current_user_can("edit_post", (int) $post->ID)) {
        return;
    }
    wp_nonce_field("upsellio_post_seo_claude", "upsellio_post_seo_claude_nonce");
    $status = (string) get_post_meta((int) $post->ID, "_upsellio_seo_ai_status", true);
    if ($status === "") {
        $status = "idle";
    }
    $log = (string) get_post_meta((int) $post->ID, "_upsellio_seo_ai_log", true);
    ?>
    <p class="description">Jeden przycisk: Claude (API jak w CRM) uzupełnia tytuł, treść HTML, zajawkę, tagi oraz meta SEO (Yoast / Rank Math / Upsellio). Wymaga klucza <code>UPSELLIO_ANTHROPIC_API_KEY</code> lub opcji CRM.</p>
    <p>
      <button type="button" class="button button-primary" id="upsellio-seo-claude-run"><?php esc_html_e("Uzupełnij wpis (Claude)", "upsellio"); ?></button>
      <span id="upsellio-seo-claude-status-text" style="margin-left:10px;"></span>
    </p>
    <p><strong>Status:</strong> <code id="upsellio-seo-claude-status-code"><?php echo esc_html($status); ?></code></p>
    <p>
      <label for="upsellio-seo-claude-notes"><strong>Krótka notatka dla modelu (opcjonalnie)</strong></label><br />
      <textarea id="upsellio-seo-claude-notes" rows="2" style="width:100%;" placeholder="np. nacisk na lead generation, branża medyczna…"></textarea>
    </p>
    <p><strong>Log integracji</strong></p>
    <textarea readonly rows="10" style="width:100%;font-family:ui-monospace,monospace;font-size:11px;" id="upsellio-seo-claude-log"><?php echo esc_textarea($log); ?></textarea>
    <p class="description">Po pomyślnym zapisie strona się przeładuje, żeby edytor pokazał nową treść z bazy.</p>
    <?php
}

function upsellio_post_editor_seo_claude_assets(string $hook_suffix): void
{
    if ($hook_suffix !== "post.php" && $hook_suffix !== "post-new.php") {
        return;
    }
    $screen = function_exists("get_current_screen") ? get_current_screen() : null;
    if (!$screen || $screen->post_type !== "post") {
        return;
    }
    wp_enqueue_script(
        "upsellio-post-seo-claude",
        get_template_directory_uri() . "/assets/js/post-seo-claude.js",
        ["jquery"],
        "1.0",
        true
    );
    wp_localize_script("upsellio-post-seo-claude", "upsellioPostSeoClaude", [
        "ajaxurl" => admin_url("admin-ajax.php"),
        "nonce" => wp_create_nonce("upsellio_post_seo_claude"),
    ]);
}
add_action("admin_enqueue_scripts", "upsellio_post_editor_seo_claude_assets");

function upsellio_ajax_post_seo_claude_fill(): void
{
    check_ajax_referer("upsellio_post_seo_claude", "nonce");
    $post_id = isset($_POST["post_id"]) ? (int) wp_unslash($_POST["post_id"]) : 0;
    $notes = isset($_POST["notes"]) ? sanitize_textarea_field(wp_unslash($_POST["notes"])) : "";
    if ($post_id <= 0 || !current_user_can("edit_post", $post_id)) {
        wp_send_json_success([
            "ok" => false,
            "status" => "error",
            "message" => "Brak uprawnień lub nieprawidłowy identyfikator wpisu.",
            "log" => "",
        ]);
    }

    update_post_meta($post_id, "_upsellio_seo_ai_status", "running");
    upsellio_post_editor_seo_claude_append_log($post_id, "Start: żądanie do Claude (CRM).");

    if (!function_exists("upsellio_blog_tool_run_claude_editor_fill")) {
        update_post_meta($post_id, "_upsellio_seo_ai_status", "error");
        upsellio_post_editor_seo_claude_append_log($post_id, "Błąd: brak modułu blog-seo-tool.");
        wp_send_json_success([
            "ok" => false,
            "status" => "error",
            "message" => "Brak modułu generatora (blog-seo-tool).",
            "log" => (string) get_post_meta($post_id, "_upsellio_seo_ai_log", true),
        ]);
    }

    $result = upsellio_blog_tool_run_claude_editor_fill($post_id, $notes);
    if (is_wp_error($result)) {
        update_post_meta($post_id, "_upsellio_seo_ai_status", "error");
        upsellio_post_editor_seo_claude_append_log($post_id, "Błąd: " . $result->get_error_message());
        wp_send_json_success([
            "ok" => false,
            "status" => "error",
            "message" => $result->get_error_message(),
            "log" => (string) get_post_meta($post_id, "_upsellio_seo_ai_log", true),
        ]);
    }

    upsellio_blog_tool_apply_ai_payload_to_post($post_id, $result);
    update_post_meta($post_id, "_upsellio_seo_ai_status", "ok");
    upsellio_post_editor_seo_claude_append_log($post_id, "Zakończono: zapisano treść, tagi i meta SEO.");

    wp_send_json_success([
        "ok" => true,
        "status" => "ok",
        "log" => (string) get_post_meta($post_id, "_upsellio_seo_ai_log", true),
    ]);
}
add_action("wp_ajax_upsellio_post_seo_claude_fill", "upsellio_ajax_post_seo_claude_fill");
