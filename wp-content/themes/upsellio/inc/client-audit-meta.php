<?php

if (!defined("ABSPATH")) {
    exit;
}

function ups_audit_register_meta_account_post_type()
{
    register_post_type("crm_meta_account", [
        "labels" => ["name" => "Konta Meta", "singular_name" => "Konto Meta"],
        "public" => false,
        "show_ui" => false,
        "supports" => ["title", "custom-fields"],
    ]);
}
add_action("init", "ups_audit_register_meta_account_post_type", 11);

function ups_audit_count_meta_accounts(): int
{
    $ids = get_posts([
        "post_type" => "crm_meta_account",
        "posts_per_page" => -1,
        "post_status" => ["publish", "draft"],
        "fields" => "ids",
    ]);

    return is_array($ids) ? count($ids) : 0;
}

function ups_audit_find_meta_account_id_by_identity(string $meta_user_id = "", string $email = ""): int
{
    $meta_user_id = trim($meta_user_id);
    $email = sanitize_email($email);

    if ($meta_user_id !== "") {
        $by_id = get_posts([
            "post_type" => "crm_meta_account",
            "posts_per_page" => 1,
            "post_status" => ["publish", "draft"],
            "fields" => "ids",
            "meta_query" => [[
                "key" => "_ups_macc_meta_user_id",
                "value" => $meta_user_id,
                "compare" => "=",
            ]],
        ]);
        if (!empty($by_id)) {
            return (int) $by_id[0];
        }
    }

    if ($email !== "" && is_email($email)) {
        $by_email = get_posts([
            "post_type" => "crm_meta_account",
            "posts_per_page" => 1,
            "post_status" => ["publish", "draft"],
            "fields" => "ids",
            "meta_query" => [[
                "key" => "_ups_macc_email",
                "value" => $email,
                "compare" => "=",
            ]],
        ]);
        if (!empty($by_email)) {
            return (int) $by_email[0];
        }
    }

    return 0;
}

function ups_audit_get_oauth_for_meta_account(int $meta_account_id): array
{
    $meta_account_id = (int) $meta_account_id;
    if ($meta_account_id <= 0) {
        return [];
    }
    $global = upsellio_meta_ads_get_settings();
    $secret = (string) get_post_meta($meta_account_id, "_ups_macc_oauth_app_secret", true);
    if ($secret !== "" && function_exists("ups_audit_decrypt")) {
        $dec = ups_audit_decrypt($secret);
        if ($dec !== "") {
            $secret = $dec;
        }
    }
    $token = (string) get_post_meta($meta_account_id, "_ups_macc_oauth_access_token", true);
    if ($token !== "" && function_exists("ups_audit_decrypt")) {
        $dec = ups_audit_decrypt($token);
        if ($dec !== "") {
            $token = $dec;
        }
    }

    return [
        "app_id" => (string) get_post_meta($meta_account_id, "_ups_macc_oauth_app_id", true) ?: $global["app_id"],
        "app_secret" => $secret !== "" ? $secret : $global["app_secret"],
        "access_token" => $token,
        "meta_user_id" => (string) get_post_meta($meta_account_id, "_ups_macc_meta_user_id", true),
        "email" => (string) get_post_meta($meta_account_id, "_ups_macc_email", true),
    ];
}

function ups_audit_upsert_meta_account_from_tokens(
    string $access_token,
    string $app_id = "",
    string $app_secret = "",
    string $label = "",
    int $uid = 0,
    int $force_account_id = 0,
    int $expires_in = 0
): int {
    $access_token = trim($access_token);
    if ($access_token === "") {
        return 0;
    }
    $global = upsellio_meta_ads_get_settings();
    $app_id = trim($app_id) !== "" ? trim($app_id) : $global["app_id"];
    $app_secret = trim($app_secret) !== "" ? trim($app_secret) : $global["app_secret"];

    $profile = upsellio_meta_ads_fetch_user_profile($access_token);
    $meta_user_id = "";
    $name = "";
    $email = "";
    if (!is_wp_error($profile) && is_array($profile)) {
        $meta_user_id = sanitize_text_field((string) ($profile["id"] ?? ""));
        $name = sanitize_text_field((string) ($profile["name"] ?? ""));
        $email = sanitize_email((string) ($profile["email"] ?? ""));
    }

    $account_id = ups_audit_find_meta_account_id_by_identity($meta_user_id, $email);
    $force_account_id = (int) $force_account_id;
    if ($force_account_id > 0) {
        $account_id = $force_account_id;
    }
    $title = $name !== "" ? $name : ($email !== "" ? $email : "Meta " . substr(md5($access_token), 0, 8));

    if ($account_id <= 0) {
        $account_id = wp_insert_post([
            "post_type" => "crm_meta_account",
            "post_title" => $title,
            "post_status" => "publish",
        ]);
        if ($account_id <= 0) {
            return 0;
        }
    } else {
        wp_update_post([
            "ID" => $account_id,
            "post_title" => $title,
        ]);
    }

    update_post_meta($account_id, "_ups_macc_email", $email);
    update_post_meta($account_id, "_ups_macc_meta_user_id", $meta_user_id);
    update_post_meta($account_id, "_ups_macc_label", sanitize_text_field($label));
    update_post_meta($account_id, "_ups_macc_oauth_app_id", $app_id);
    update_post_meta($account_id, "_ups_macc_oauth_app_secret", function_exists("ups_audit_encrypt") ? ups_audit_encrypt($app_secret) : $app_secret);
    update_post_meta($account_id, "_ups_macc_oauth_access_token", function_exists("ups_audit_encrypt") ? ups_audit_encrypt($access_token) : $access_token);
    update_post_meta($account_id, "_ups_macc_connected_by", (int) $uid);
    if ($expires_in > 0) {
        update_post_meta($account_id, "_ups_macc_token_expires_at", gmdate("Y-m-d H:i:s", time() + (int) $expires_in));
    }

    $scopes = [];
    $debug = upsellio_meta_ads_debug_token($access_token);
    if (!is_wp_error($debug) && is_array($debug["data"] ?? null)) {
        $scopes = array_values(array_filter(array_map("trim", explode(",", (string) ($debug["data"]["scopes"] ?? "")))));
    }
    update_post_meta($account_id, "_ups_macc_scopes", $scopes);

    $cache = ["ad_accounts" => ups_audit_fetch_meta_ad_accounts($account_id)];
    update_post_meta($account_id, "_ups_macc_resources_cache", $cache);
    update_post_meta($account_id, "_ups_macc_last_sync_at", current_time("mysql"));

    if ($uid > 0) {
        set_transient("ups_audit_meta_last_connected_" . $uid, $account_id, HOUR_IN_SECONDS);
    }

    return $account_id;
}

function ups_audit_set_macc_fetch_error(int $meta_account_id, string $message): void
{
    update_post_meta((int) $meta_account_id, "_ups_macc_fetch_error", sanitize_text_field($message));
}

function ups_audit_clear_macc_fetch_error(int $meta_account_id): void
{
    delete_post_meta((int) $meta_account_id, "_ups_macc_fetch_error");
}

function ups_audit_get_macc_fetch_error_hint(int $meta_account_id): string
{
    $err = trim((string) get_post_meta((int) $meta_account_id, "_ups_macc_fetch_error", true));

    return $err !== "" ? $err : __("Brak danych — odśwież listę kont reklamowych.", "upsellio");
}

function ups_audit_fetch_meta_ad_accounts(int $meta_account_id)
{
    $meta_account_id = (int) $meta_account_id;
    if (!ups_audit_meta_api_configured()) {
        ups_audit_set_macc_fetch_error($meta_account_id, __("Meta: uzupełnij App ID i App Secret w CRM.", "upsellio"));

        return [];
    }
    $oauth = ups_audit_get_oauth_for_meta_account($meta_account_id);
    $token = trim((string) ($oauth["access_token"] ?? ""));
    if ($token === "") {
        ups_audit_set_macc_fetch_error($meta_account_id, __("Meta: brak tokena — połącz konto ponownie.", "upsellio"));

        return [];
    }

    $list = upsellio_meta_ads_list_ad_accounts($token);
    if (is_wp_error($list)) {
        ups_audit_set_macc_fetch_error($meta_account_id, "Meta: " . $list->get_error_message());

        return [];
    }
    if ($list === []) {
        ups_audit_set_macc_fetch_error(
            $meta_account_id,
            __("Meta: brak kont reklamowych — sprawdź uprawnienia ads_read i business_management.", "upsellio")
        );
    } else {
        ups_audit_clear_macc_fetch_error($meta_account_id);
    }

    return $list;
}

function ups_audit_find_imported_meta_resource_id(int $meta_account_id, string $external_id): int
{
    $meta_account_id = (int) $meta_account_id;
    $external_id = upsellio_meta_ads_normalize_ad_account_id(sanitize_text_field($external_id));
    if ($meta_account_id <= 0 || $external_id === "") {
        return 0;
    }
    $existing = get_posts([
        "post_type" => "crm_audit_resource",
        "posts_per_page" => 1,
        "post_status" => ["publish", "draft"],
        "fields" => "ids",
        "meta_query" => [
            "relation" => "AND",
            ["key" => "_ups_resource_meta_account_id", "value" => $meta_account_id],
            ["key" => "_ups_resource_type", "value" => "meta"],
            ["key" => "_ups_resource_external_id", "value" => $external_id],
        ],
    ]);

    return !empty($existing) ? (int) $existing[0] : 0;
}

function ups_audit_get_meta_account_resources(int $meta_account_id, string $type = ""): array
{
    $meta_account_id = (int) $meta_account_id;
    $type = sanitize_key($type);
    if ($meta_account_id <= 0) {
        return [];
    }
    $meta_query = [[
        "key" => "_ups_resource_meta_account_id",
        "value" => $meta_account_id,
        "compare" => "=",
        "type" => "NUMERIC",
    ]];
    if ($type !== "") {
        $meta_query[] = ["key" => "_ups_resource_type", "value" => $type];
        $meta_query["relation"] = "AND";
    }

    return get_posts([
        "post_type" => "crm_audit_resource",
        "posts_per_page" => -1,
        "post_status" => ["publish", "draft"],
        "meta_query" => $meta_query,
        "orderby" => "title",
        "order" => "ASC",
    ]);
}

function ups_audit_meta_account_setup_status(int $meta_account_id): array
{
    $meta_account_id = (int) $meta_account_id;
    $meta = count(ups_audit_get_meta_account_resources($meta_account_id, "meta"));

    return [
        "meta" => $meta,
        "imported" => $meta,
        "is_ready" => $meta > 0,
        "steps" => [
            ["key" => "meta", "done" => $meta > 0, "label" => "Meta Ads (" . $meta . ")"],
        ],
    ];
}

function ups_audit_with_meta_account_oauth(int $meta_account_id, callable $callback)
{
    $oauth = ups_audit_get_oauth_for_meta_account($meta_account_id);
    if (trim((string) ($oauth["access_token"] ?? "")) === "") {
        return new WP_Error("meta_oauth", __("Brak tokena Meta dla konta.", "upsellio"));
    }

    return $callback($oauth);
}
