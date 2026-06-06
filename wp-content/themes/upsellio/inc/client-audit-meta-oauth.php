<?php

if (!defined("ABSPATH")) {
    exit;
}

if (defined("UPS_AUDIT_META_OAUTH_LOADED")) {
    return;
}
define("UPS_AUDIT_META_OAUTH_LOADED", true);

if (!defined("UPS_AUDIT_META_OAUTH_ACTION")) {
    define("UPS_AUDIT_META_OAUTH_ACTION", "ups_audit_meta_oauth");
}

function ups_audit_meta_oauth_action_url(array $extra = []): string
{
    return add_query_arg(
        array_merge(["action" => UPS_AUDIT_META_OAUTH_ACTION], $extra),
        admin_url("admin-post.php")
    );
}

function ups_audit_meta_oauth_redirect_uri(): string
{
    $uri = home_url("/crm-app/?view=ca-meta-accounts");
    if (function_exists("upsellio_google_oauth_normalize_redirect_uri_string")) {
        return upsellio_google_oauth_normalize_redirect_uri_string($uri);
    }

    return esc_url_raw($uri);
}

function ups_audit_meta_oauth_scope_string(): string
{
    return "ads_read,business_management";
}

function ups_audit_meta_oauth_connection_info(): array
{
    $cfg = upsellio_meta_ads_get_settings();
    $ready = ups_audit_meta_api_configured();
    $redirect = ups_audit_meta_oauth_redirect_uri();

    return [
        "ready" => $ready,
        "message" => $ready
            ? __("Meta OAuth gotowy — połącz konto Facebook.", "upsellio")
            : __("Uzupełnij App ID i App Secret Meta Ads w sekcji poniżej.", "upsellio"),
        "redirect_uri" => $redirect,
        "meta_console_uri" => $redirect,
        "app_id" => $cfg["app_id"],
    ];
}

function ups_audit_meta_oauth_connect_url(string $label = ""): string
{
    if (!is_user_logged_in()) {
        return wp_login_url(ups_audit_meta_oauth_connect_url($label));
    }

    return ups_audit_meta_oauth_action_url([
        "op" => "start",
        "label" => sanitize_text_field($label),
    ]);
}

function ups_audit_meta_oauth_start_connect(string $label = ""): string
{
    if (!is_user_logged_in() || !current_user_can("edit_posts")) {
        return wp_login_url(ups_audit_meta_oauth_connect_url($label));
    }
    if (!ups_audit_meta_api_configured()) {
        return add_query_arg(
            ["ups_audit_error" => rawurlencode(__("Uzupełnij App ID i App Secret Meta.", "upsellio"))],
            ups_audit_meta_oauth_redirect_uri()
        );
    }

    $uid = get_current_user_id();
    $state = wp_generate_password(24, false, false);
    $pending = [
        "conn_type" => "audit_meta",
        "uid" => $uid,
        "label" => sanitize_text_field($label),
        "redirect_uri" => ups_audit_meta_oauth_redirect_uri(),
        "created" => time(),
    ];
    set_transient("ups_audit_meta_oauth_st_" . $state, $pending, 15 * MINUTE_IN_SECONDS);

    $cfg = upsellio_meta_ads_get_settings();
    $auth_url = add_query_arg([
        "client_id" => $cfg["app_id"],
        "redirect_uri" => ups_audit_meta_oauth_redirect_uri(),
        "state" => $state,
        "scope" => ups_audit_meta_oauth_scope_string(),
        "response_type" => "code",
    ], "https://www.facebook.com/" . rawurlencode(upsellio_meta_ads_api_version()) . "/dialog/oauth");

    return $auth_url;
}

function ups_audit_meta_oauth_process_callback(): void
{
    $code = isset($_GET["code"]) ? sanitize_text_field(wp_unslash($_GET["code"])) : "";
    $state = isset($_GET["state"]) ? sanitize_text_field(wp_unslash($_GET["state"])) : "";
    $error = isset($_GET["error"]) ? sanitize_text_field(wp_unslash($_GET["error"])) : "";
    $error_desc = isset($_GET["error_description"]) ? sanitize_text_field(wp_unslash($_GET["error_description"])) : "";
    $redirect = ups_audit_meta_oauth_redirect_uri();

    if ($error !== "") {
        $msg = $error_desc !== "" ? $error_desc : $error;
        wp_safe_redirect(add_query_arg(["ups_audit_error" => rawurlencode($msg)], $redirect));
        exit;
    }
    if ($code === "" || $state === "") {
        return;
    }

    $pending = get_transient("ups_audit_meta_oauth_st_" . $state);
    delete_transient("ups_audit_meta_oauth_st_" . $state);
    if (!is_array($pending) || (string) ($pending["conn_type"] ?? "") !== "audit_meta") {
        wp_safe_redirect(add_query_arg(["ups_audit_error" => rawurlencode(__("Sesja OAuth Meta wygasła — spróbuj ponownie.", "upsellio"))], $redirect));
        exit;
    }

    $token_resp = upsellio_meta_ads_exchange_code_for_token($code, ups_audit_meta_oauth_redirect_uri());
    if (is_wp_error($token_resp)) {
        wp_safe_redirect(add_query_arg(["ups_audit_error" => rawurlencode($token_resp->get_error_message())], $redirect));
        exit;
    }

    $short = (string) ($token_resp["access_token"] ?? "");
    $expires = (int) ($token_resp["expires_in"] ?? 0);
    if ($short === "") {
        wp_safe_redirect(add_query_arg(["ups_audit_error" => rawurlencode(__("Meta nie zwróciło tokena.", "upsellio"))], $redirect));
        exit;
    }

    $long = upsellio_meta_ads_exchange_long_lived_token($short);
    $access = $short;
    $long_expires = $expires;
    if (!is_wp_error($long) && is_array($long) && !empty($long["access_token"])) {
        $access = (string) $long["access_token"];
        $long_expires = (int) ($long["expires_in"] ?? 5184000);
    }

    $cfg = upsellio_meta_ads_get_settings();
    $account_id = ups_audit_upsert_meta_account_from_tokens(
        $access,
        $cfg["app_id"],
        $cfg["app_secret"],
        (string) ($pending["label"] ?? ""),
        (int) ($pending["uid"] ?? 0),
        0,
        $long_expires
    );

    if ($account_id <= 0) {
        wp_safe_redirect(add_query_arg(["ups_audit_error" => rawurlencode(__("Nie udało się zapisać konta Meta.", "upsellio"))], $redirect));
        exit;
    }

    wp_safe_redirect(add_query_arg([
        "connected" => "1",
        "account_id" => $account_id,
    ], $redirect));
    exit;
}

function ups_audit_meta_oauth_handle()
{
    if (!is_user_logged_in() || !current_user_can("edit_posts")) {
        auth_redirect();
    }
    $op = isset($_REQUEST["op"]) ? sanitize_key(wp_unslash($_REQUEST["op"])) : "";
    if ($op === "start") {
        $label = isset($_REQUEST["label"]) ? sanitize_text_field(wp_unslash($_REQUEST["label"])) : "";
        wp_safe_redirect(ups_audit_meta_oauth_start_connect($label));
        exit;
    }
    if ($op === "callback" || isset($_GET["code"])) {
        ups_audit_meta_oauth_process_callback();
        exit;
    }
    wp_safe_redirect(ups_audit_meta_oauth_redirect_uri());
    exit;
}
add_action("admin_post_" . UPS_AUDIT_META_OAUTH_ACTION, "ups_audit_meta_oauth_handle");
add_action("admin_post_nopriv_" . UPS_AUDIT_META_OAUTH_ACTION, "ups_audit_meta_oauth_handle");

function ups_audit_meta_oauth_crm_app_callback(): void
{
    if (!function_exists("ups_audit_oauth_is_crm_return_request")) {
        return;
    }
    $view = isset($_GET["view"]) ? sanitize_key(wp_unslash($_GET["view"])) : "";
    if ($view !== "ca-meta-accounts") {
        return;
    }
    if (!isset($_GET["code"]) && !isset($_GET["error"])) {
        return;
    }
    ups_audit_meta_oauth_process_callback();
}
add_action("init", "ups_audit_meta_oauth_crm_app_callback", -998);
