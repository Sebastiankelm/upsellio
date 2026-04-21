<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_get_data_schema_version()
{
    return "2026.04.21.1";
}

function upsellio_initialize_data_schema($force = false)
{
    static $isRunning = false;
    if ($isRunning) {
        return;
    }
    $isRunning = true;

    $optionKey = "upsellio_data_schema_version";
    $targetVersion = upsellio_get_data_schema_version();
    $currentVersion = (string) get_option($optionKey, "");

    if (!$force && $currentVersion === $targetVersion) {
        $isRunning = false;
        return;
    }

    // Re-register core entities before flushing rules on fresh installs/migrations.
    if (function_exists("upsellio_register_city_post_type")) {
        upsellio_register_city_post_type();
    }
    if (function_exists("upsellio_register_definition_post_type")) {
        upsellio_register_definition_post_type();
    }
    if (function_exists("upsellio_crm_register_post_type")) {
        upsellio_crm_register_post_type();
    }
    if (function_exists("upsellio_crm_register_task_post_type")) {
        upsellio_crm_register_task_post_type();
    }
    if (function_exists("upsellio_crm_register_taxonomies")) {
        upsellio_crm_register_taxonomies();
    }
    if (function_exists("upsellio_crm_ensure_default_terms")) {
        upsellio_crm_ensure_default_terms();
    }
    if (function_exists("upsellio_seo_schedule_refresh_cron")) {
        upsellio_seo_schedule_refresh_cron();
    }

    if (get_option("upsellio_installed_at", "") === "") {
        add_option("upsellio_installed_at", current_time("mysql"));
    }

    update_option($optionKey, $targetVersion, false);
    flush_rewrite_rules(false);
    $isRunning = false;
}

function upsellio_initialize_data_schema_on_init()
{
    upsellio_initialize_data_schema(false);
}
add_action("init", "upsellio_initialize_data_schema_on_init", 99);

function upsellio_initialize_data_schema_on_theme_switch()
{
    upsellio_initialize_data_schema(true);
}
add_action("after_switch_theme", "upsellio_initialize_data_schema_on_theme_switch");

function upsellio_handle_manual_data_schema_init()
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }
    if (!isset($_GET["upsellio_init_data_schema"])) {
        return;
    }

    $nonce = isset($_GET["_upsellio_nonce"]) ? sanitize_text_field(wp_unslash($_GET["_upsellio_nonce"])) : "";
    if (!wp_verify_nonce($nonce, "upsellio_init_data_schema")) {
        return;
    }

    upsellio_initialize_data_schema(true);
    $redirectUrl = add_query_arg(
        [
            "upsellio_data_schema_initialized" => 1,
        ],
        admin_url("themes.php")
    );
    wp_safe_redirect($redirectUrl);
    exit;
}
add_action("admin_init", "upsellio_handle_manual_data_schema_init");

function upsellio_data_schema_admin_notice()
{
    if (!is_admin() || !isset($_GET["upsellio_data_schema_initialized"])) {
        return;
    }

    echo '<div class="notice notice-success"><p>' . esc_html("Upsellio: inicjalizacja i migracja danych zostala wykonana.") . "</p></div>";
}
add_action("admin_notices", "upsellio_data_schema_admin_notice");

function upsellio_get_data_schema_init_url()
{
    return add_query_arg(
        [
            "upsellio_init_data_schema" => 1,
            "_upsellio_nonce" => wp_create_nonce("upsellio_init_data_schema"),
        ],
        admin_url("themes.php")
    );
}
