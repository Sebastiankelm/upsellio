<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_get_data_schema_version()
{
    return "2026.08.18.1";
}

function upsellio_create_custom_tables()
{
    global $wpdb;

    require_once ABSPATH . "wp-admin/includes/upgrade.php";

    $charsetCollate = $wpdb->get_charset_collate();
    $leadEventsTable = $wpdb->prefix . "upsellio_lead_events";
    $batchJobsTable = $wpdb->prefix . "upsellio_batch_jobs";
    $batchItemsTable = $wpdb->prefix . "upsellio_batch_items";

    $leadEventsSql = "CREATE TABLE {$leadEventsTable} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        lead_id BIGINT UNSIGNED NOT NULL,
        event_type VARCHAR(100) NOT NULL,
        event_source VARCHAR(120) DEFAULT '' NOT NULL,
        event_payload LONGTEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY lead_id_created_at (lead_id, created_at),
        KEY event_type_created_at (event_type, created_at)
    ) {$charsetCollate};";

    $batchJobsSql = "CREATE TABLE {$batchJobsTable} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        job_type VARCHAR(100) NOT NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'pending',
        total_items INT UNSIGNED NOT NULL DEFAULT 0,
        processed_items INT UNSIGNED NOT NULL DEFAULT 0,
        failed_items INT UNSIGNED NOT NULL DEFAULT 0,
        created_by BIGINT UNSIGNED NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        started_at DATETIME NULL,
        finished_at DATETIME NULL,
        meta_json LONGTEXT NULL,
        PRIMARY KEY  (id),
        KEY job_type_status (job_type, status),
        KEY created_at (created_at)
    ) {$charsetCollate};";

    $batchItemsSql = "CREATE TABLE {$batchItemsTable} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        job_id BIGINT UNSIGNED NOT NULL,
        entity_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
        entity_type VARCHAR(80) NOT NULL DEFAULT '',
        status VARCHAR(30) NOT NULL DEFAULT 'pending',
        attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
        error_message TEXT NULL,
        payload_json LONGTEXT NULL,
        processed_at DATETIME NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY job_id_status (job_id, status),
        KEY entity_lookup (entity_type, entity_id),
        KEY processed_at (processed_at)
    ) {$charsetCollate};";

    $landingEventsTable = $wpdb->prefix . "ups_landing_events";
    $landingSessionsTable = $wpdb->prefix . "ups_landing_sessions";

    $landingEventsSql = "CREATE TABLE {$landingEventsTable} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        landing_key VARCHAR(32) NOT NULL DEFAULT 'butik',
        event_date DATE NOT NULL,
        event_name VARCHAR(40) NOT NULL,
        section_id VARCHAR(40) NOT NULL DEFAULT '',
        extra VARCHAR(80) NOT NULL DEFAULT '',
        hits INT UNSIGNED NOT NULL DEFAULT 0,
        PRIMARY KEY  (id),
        UNIQUE KEY agg_key (landing_key, event_date, event_name, section_id, extra),
        KEY landing_date (landing_key, event_date)
    ) {$charsetCollate};";

    $landingSessionsSql = "CREATE TABLE {$landingSessionsTable} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        landing_key VARCHAR(32) NOT NULL DEFAULT 'butik',
        session_key CHAR(32) NOT NULL,
        visitor_key CHAR(32) NOT NULL DEFAULT '',
        first_at DATETIME NOT NULL,
        last_at DATETIME NOT NULL,
        device VARCHAR(16) NOT NULL DEFAULT '',
        utm_source VARCHAR(80) NOT NULL DEFAULT '',
        utm_medium VARCHAR(80) NOT NULL DEFAULT '',
        utm_campaign VARCHAR(120) NOT NULL DEFAULT '',
        sections LONGTEXT NULL,
        converted TINYINT(1) NOT NULL DEFAULT 0,
        convert_type VARCHAR(16) NOT NULL DEFAULT '',
        time_sec SMALLINT UNSIGNED NOT NULL DEFAULT 0,
        PRIMARY KEY  (id),
        UNIQUE KEY landing_session (landing_key, session_key),
        KEY landing_first (landing_key, first_at)
    ) {$charsetCollate};";

    dbDelta($leadEventsSql);
    dbDelta($batchJobsSql);
    dbDelta($batchItemsSql);
    dbDelta($landingEventsSql);
    dbDelta($landingSessionsSql);
}

function upsellio_ensure_postmeta_indexes()
{
    global $wpdb;

    $postmetaTable = $wpdb->postmeta;
    $requiredIndexes = [
        "upsellio_meta_key_post_id" => "ALTER TABLE {$postmetaTable} ADD INDEX upsellio_meta_key_post_id (meta_key, post_id)",
        "upsellio_post_id_meta_key" => "ALTER TABLE {$postmetaTable} ADD INDEX upsellio_post_id_meta_key (post_id, meta_key)",
    ];

    foreach ($requiredIndexes as $indexName => $alterSql) {
        $indexExists = (bool) $wpdb->get_var(
            $wpdb->prepare("SHOW INDEX FROM {$postmetaTable} WHERE Key_name = %s", $indexName)
        );

        if ($indexExists) {
            continue;
        }

        $wpdb->query($alterSql);
    }
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
    if (function_exists("upsellio_register_lead_magnets_cpt")) {
        upsellio_register_lead_magnets_cpt();
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

    upsellio_create_custom_tables();
    upsellio_ensure_postmeta_indexes();

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
