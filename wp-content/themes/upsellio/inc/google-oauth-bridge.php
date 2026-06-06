<?php

if (!defined("ABSPATH")) {
    exit;
}

/**
 * Wbudowany most OAuth (model Rank Math) — start + callback na REST tej samej witryny.
 * Wymaga jednego OAuth Client ID w Google Cloud z redirect URI mostu (poniżej).
 */

function upsellio_google_managed_oauth_bridge_credentials(): array
{
    $client_id = "";
    $client_secret = "";

    if (defined("UPSELLIO_MANAGED_GOOGLE_OAUTH_CLIENT_ID") && is_string(UPSELLIO_MANAGED_GOOGLE_OAUTH_CLIENT_ID)) {
        $client_id = trim(UPSELLIO_MANAGED_GOOGLE_OAUTH_CLIENT_ID);
    }
    if (defined("UPSELLIO_MANAGED_GOOGLE_OAUTH_CLIENT_SECRET") && is_string(UPSELLIO_MANAGED_GOOGLE_OAUTH_CLIENT_SECRET)) {
        $client_secret = trim(UPSELLIO_MANAGED_GOOGLE_OAUTH_CLIENT_SECRET);
    }

    if ($client_id === "" || $client_secret === "") {
        $stored = get_option("upsellio_managed_oauth_app", []);
        if (is_array($stored)) {
            if ($client_id === "") {
                $client_id = trim((string) ($stored["client_id"] ?? ""));
            }
            if ($client_secret === "") {
                $client_secret = trim((string) ($stored["client_secret"] ?? ""));
            }
        }
    }

    if ($client_id === "" || $client_secret === "") {
        $gsc = function_exists("upsellio_get_gsc_credentials") ? upsellio_get_gsc_credentials() : [];
        if ($client_id === "") {
            $client_id = trim((string) ($gsc["client_id"] ?? ""));
        }
        if ($client_secret === "") {
            $client_secret = trim((string) ($gsc["client_secret"] ?? ""));
        }
    }

    return [
        "client_id" => $client_id,
        "client_secret" => $client_secret,
    ];
}

function upsellio_google_managed_oauth_bridge_credentials_ready(): bool
{
    $c = upsellio_google_managed_oauth_bridge_credentials();

    return $c["client_id"] !== "" && $c["client_secret"] !== "";
}

/**
 * Redirect URI wysyłany do Google (musi być 1:1 w Authorized redirect URIs).
 *
 * @param array<string, mixed>|null $pending_raw Transient OAuth (conn_type, label, …).
 */
function upsellio_google_managed_oauth_bridge_redirect_uri_for_pending(?array $pending_raw = null): string
{
    $conn_type = is_array($pending_raw)
        ? sanitize_key((string) ($pending_raw["conn_type"] ?? "main"))
        : "main";

    if ($conn_type === "audit" && function_exists("ups_audit_oauth_redirect_uri")) {
        return ups_audit_oauth_redirect_uri();
    }

    if (
        function_exists("upsellio_google_oauth_use_rest_callback")
        && !upsellio_google_oauth_use_rest_callback()
        && function_exists("upsellio_google_oauth_redirect_uri")
    ) {
        return upsellio_google_oauth_redirect_uri();
    }

    return upsellio_google_managed_oauth_bridge_callback_uri();
}

function upsellio_google_managed_oauth_bridge_callback_uri(): string
{
    if (function_exists("upsellio_google_oauth_redirect_uri_override_option_key")) {
        $override = function_exists("upsellio_google_oauth_normalize_redirect_uri_string")
            ? upsellio_google_oauth_normalize_redirect_uri_string(
                (string) get_option(upsellio_google_oauth_redirect_uri_override_option_key(), "")
            )
            : trim((string) get_option(upsellio_google_oauth_redirect_uri_override_option_key(), ""));
        if (
            $override !== ""
            && filter_var($override, FILTER_VALIDATE_URL)
            && function_exists("upsellio_google_oauth_redirect_uri_is_allowed_host")
            && upsellio_google_oauth_redirect_uri_is_allowed_host($override)
        ) {
            return (string) apply_filters("upsellio_google_managed_oauth_bridge_callback_uri", $override);
        }
    }

    // Domyślnie URI z myślnikiem (legacy) — wiele projektów Google Cloud ma już ten wpis.
    // Dodaj w Console także wariant z ukośnikiem /google-oauth/callback (oba działają).
    if (function_exists("upsellio_google_oauth_rest_redirect_uri_default")) {
        $uri = upsellio_google_oauth_rest_redirect_uri_default();
    } else {
        $uri = (string) rest_url("upsellio/v1/google-oauth-callback");
        if (function_exists("upsellio_google_oauth_normalize_redirect_uri_string")) {
            $uri = upsellio_google_oauth_normalize_redirect_uri_string($uri);
        } else {
            $uri = untrailingslashit(esc_url_raw($uri));
        }
    }

    return (string) apply_filters("upsellio_google_managed_oauth_bridge_callback_uri", $uri);
}

/**
 * Wariant z ukośnikiem (nowy most) — opcjonalnie w Google Cloud obok myślnika.
 */
function upsellio_google_managed_oauth_bridge_callback_uri_slash(): string
{
    $uri = (string) rest_url("upsellio/v1/google-oauth/callback");

    return function_exists("upsellio_google_oauth_normalize_redirect_uri_string")
        ? upsellio_google_oauth_normalize_redirect_uri_string($uri)
        : untrailingslashit(esc_url_raw($uri));
}

function upsellio_google_managed_oauth_is_self_hosted(?string $base = null): bool
{
    $base = $base ?? (function_exists("upsellio_google_managed_oauth_bridge_base")
        ? upsellio_google_managed_oauth_bridge_base()
        : "");
    if ($base === "") {
        return false;
    }

    $rest = rtrim((string) rest_url("upsellio/v1"), "/");

    return $base === $rest || strpos($base, $rest) === 0;
}

function upsellio_google_managed_oauth_validate_return_url(string $url): string
{
    $url = trim(rawurldecode($url));
    if ($url === "" || !filter_var($url, FILTER_VALIDATE_URL)) {
        return home_url("/");
    }

    $host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));
    $home_host = strtolower((string) wp_parse_url(home_url("/"), PHP_URL_HOST));
    if ($host === "" || $home_host === "" || $host !== $home_host) {
        return home_url("/");
    }

    return esc_url_raw($url);
}

function upsellio_google_managed_oauth_bridge_session_key(string $state): string
{
    return "upsellio_managed_bridge_" . preg_replace("/[^a-f0-9]/", "", strtolower($state));
}

function upsellio_google_managed_oauth_state_index_key(string $state): string
{
    return "upsellio_oauth_st_" . preg_replace("/[^a-f0-9]/", "", strtolower($state));
}

function upsellio_google_managed_oauth_bridge_session_ttl(): int
{
    return 20 * MINUTE_IN_SECONDS;
}

function upsellio_google_managed_oauth_bridge_send_nocache_headers(): void
{
    if (!headers_sent()) {
        nocache_headers();
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    }
}

/**
 * @param array{wp_user_id:int,return_success:string,conn_type:string} $session
 */
function upsellio_google_managed_oauth_bridge_save_session(string $state, array $session): void
{
    $ttl = upsellio_google_managed_oauth_bridge_session_ttl();
    set_transient(upsellio_google_managed_oauth_bridge_session_key($state), $session, $ttl);
    set_transient(upsellio_google_managed_oauth_state_index_key($state), $session, $ttl);
}

/**
 * @return array{wp_user_id:int,return_success:string,conn_type:string}|null
 */
function upsellio_google_managed_oauth_bridge_load_session(string $state): ?array
{
    $state = sanitize_text_field($state);
    if ($state === "") {
        return null;
    }

    foreach (
        [
            upsellio_google_managed_oauth_bridge_session_key($state),
            upsellio_google_managed_oauth_state_index_key($state),
        ] as $key
    ) {
        $raw = get_transient($key);
        if (is_array($raw) && (int) ($raw["wp_user_id"] ?? 0) > 0) {
            return [
                "wp_user_id" => (int) $raw["wp_user_id"],
                "return_success" => (string) ($raw["return_success"] ?? ""),
                "conn_type" => sanitize_key((string) ($raw["conn_type"] ?? "main")),
            ];
        }
    }

    return null;
}

function upsellio_google_managed_oauth_bridge_clear_session(string $state): void
{
    delete_transient(upsellio_google_managed_oauth_bridge_session_key($state));
    delete_transient(upsellio_google_managed_oauth_state_index_key($state));
}

function upsellio_google_managed_oauth_ensure_webhook_secret(): void
{
    if (defined("UPSELLIO_MANAGED_GOOGLE_OAUTH_WEBHOOK_SECRET") && is_string(UPSELLIO_MANAGED_GOOGLE_OAUTH_WEBHOOK_SECRET)) {
        return;
    }

    $existing = trim((string) get_option("upsellio_managed_oauth_webhook_secret", ""));
    if ($existing !== "") {
        return;
    }

    if (!upsellio_google_managed_oauth_bridge_credentials_ready()) {
        return;
    }

    update_option("upsellio_managed_oauth_webhook_secret", wp_generate_password(64, false, false), false);
}

add_action("init", "upsellio_google_managed_oauth_ensure_webhook_secret", 5);

/**
 * @return array{refresh_token:string,access_token:string,expires_in:int,body:array}|WP_Error
 */
function upsellio_google_managed_oauth_exchange_code(string $code, string $redirect_uri)
{
    $code = trim($code);
    $creds = upsellio_google_managed_oauth_bridge_credentials();
    $client_id = $creds["client_id"];
    $client_secret = $creds["client_secret"];
    if ($code === "" || $client_id === "" || $client_secret === "") {
        return new WP_Error("ups_managed_bridge_exchange", "Brak kodu lub danych aplikacji OAuth mostu.");
    }

    $response = wp_remote_post("https://oauth2.googleapis.com/token", [
        "timeout" => 25,
        "sslverify" => true,
        "body" => [
            "code" => $code,
            "client_id" => $client_id,
            "client_secret" => $client_secret,
            "redirect_uri" => $redirect_uri,
            "grant_type" => "authorization_code",
        ],
    ]);

    if (is_wp_error($response)) {
        return $response;
    }

    $status = (int) wp_remote_retrieve_response_code($response);
    $raw_body = (string) wp_remote_retrieve_body($response);
    $body = json_decode($raw_body, true);
    if ($status >= 400) {
        $msg = function_exists("upsellio_gsc_extract_error_message")
            ? upsellio_gsc_extract_error_message(is_array($body) ? $body : [], "Wymiana kodu OAuth (most) nie powiodła się.")
            : "Wymiana kodu OAuth (most) nie powiodła się.";

        return new WP_Error("ups_managed_bridge_http", $msg, ["status" => $status]);
    }

    $refresh = is_array($body) ? trim((string) ($body["refresh_token"] ?? "")) : "";
    $access = is_array($body) ? trim((string) ($body["access_token"] ?? "")) : "";
    if ($refresh === "") {
        return new WP_Error(
            "ups_managed_bridge_no_refresh",
            "Google nie zwrócił refresh tokena. Usuń dostęp aplikacji w koncie Google i połącz ponownie (wymagany ekran zgody)."
        );
    }

    return [
        "refresh_token" => $refresh,
        "access_token" => $access,
        "expires_in" => is_array($body) ? (int) ($body["expires_in"] ?? 0) : 0,
        "body" => is_array($body) ? $body : [],
    ];
}

/**
 * @param array<string, mixed> $payload
 * @return WP_REST_Response|WP_Error
 */
function upsellio_google_managed_oauth_internal_handoff(array $payload)
{
    if (!function_exists("upsellio_handle_google_managed_oauth_handoff_rest")) {
        return new WP_Error("ups_managed_bridge_handoff", "Handoff OAuth nie jest dostępny.");
    }

    $secret = function_exists("upsellio_google_managed_oauth_webhook_secret")
        ? upsellio_google_managed_oauth_webhook_secret()
        : "";
    if ($secret === "") {
        return new WP_Error("ups_managed_bridge_handoff", "Brak sekretu webhook OAuth.");
    }

    $raw = wp_json_encode($payload);
    if (!is_string($raw)) {
        return new WP_Error("ups_managed_bridge_handoff", "Nie udało się zakodować payloadu handoff.");
    }

    $request = new WP_REST_Request("POST", "/upsellio/v1/google-managed-oauth-handoff");
    $request->set_body($raw);
    $request->set_header("Content-Type", "application/json");
    $request->set_header("X-Upsellio-Signature", hash_hmac("sha256", $raw, $secret));

    return upsellio_handle_google_managed_oauth_handoff_rest($request);
}

function upsellio_google_managed_oauth_bridge_error_redirect(string $return_url, string $message): void
{
    $target = upsellio_google_managed_oauth_validate_return_url($return_url);
    if (strpos($target, "/crm-app/") !== false) {
        $query = [];
        $parsed = wp_parse_url($target);
        if (!empty($parsed["query"])) {
            parse_str((string) $parsed["query"], $query);
        }
        $crm_view = isset($query["view"]) ? sanitize_key((string) $query["view"]) : "";
        if ($crm_view === "ca-accounts") {
            $target = add_query_arg(
                ["view" => "ca-accounts", "ups_audit_error" => rawurlencode($message)],
                home_url("/crm-app/")
            );
        } else {
            $target = function_exists("upsellio_site_analytics_admin_url")
                ? upsellio_site_analytics_admin_url(["upsellio_managed_oauth_error" => rawurlencode($message)])
                : add_query_arg(
                    ["view" => "analytics", "atab" => "today", "upsellio_managed_oauth_error" => rawurlencode($message)],
                    home_url("/crm-app/")
                );
        }
    } elseif (function_exists("upsellio_site_analytics_admin_url")) {
        $target = upsellio_site_analytics_admin_url([
            "upsellio_managed_oauth_error" => rawurlencode($message),
        ]);
    } else {
        $target = add_query_arg("upsellio_managed_oauth_error", rawurlencode($message), $target);
    }

    wp_safe_redirect($target);
    exit;
}

function upsellio_register_google_oauth_bridge_routes(): void
{
    register_rest_route("upsellio/v1", "/google-oauth/start", [
        "methods" => "GET",
        "callback" => "upsellio_handle_google_oauth_bridge_start",
        "permission_callback" => "__return_true",
    ]);

    register_rest_route("upsellio/v1", "/google-oauth/callback", [
        "methods" => "GET",
        "callback" => "upsellio_handle_google_oauth_bridge_callback",
        "permission_callback" => "__return_true",
    ]);
}

add_action("rest_api_init", "upsellio_register_google_oauth_bridge_routes");

function upsellio_handle_google_oauth_bridge_start(WP_REST_Request $request)
{
    upsellio_google_managed_oauth_bridge_send_nocache_headers();

    if (!upsellio_google_managed_oauth_is_self_hosted()) {
        return new WP_Error("ups_managed_bridge_disabled", "Wbudowany most OAuth nie jest aktywny.", ["status" => 501]);
    }

    $state = sanitize_text_field((string) $request->get_param("state"));
    $wp_user_id = (int) $request->get_param("wp_user_id");
    $return_success = (string) $request->get_param("return_success");
    $scope_mode = sanitize_key((string) $request->get_param("scope_mode"));
    $site_url = esc_url_raw((string) $request->get_param("site_url"));

    if ($state === "" || $wp_user_id <= 0) {
        return new WP_Error("ups_managed_bridge_bad_request", "Brak state lub wp_user_id.", ["status" => 400]);
    }

    $home = untrailingslashit(home_url("/"));
    if ($site_url !== "" && untrailingslashit($site_url) !== $home) {
        return new WP_Error("ups_managed_bridge_bad_site", "Niezgodny site_url.", ["status" => 400]);
    }

    $pending = function_exists("upsellio_google_oauth_get_pending")
        ? upsellio_google_oauth_get_pending($wp_user_id)
        : null;
    if ($pending === null || !hash_equals((string) $pending["state"], $state)) {
        return new WP_Error("ups_managed_bridge_bad_state", "Sesja OAuth wygasła — uruchom połączenie ponownie.", ["status" => 400]);
    }

    if ($scope_mode === "with_ads") {
        update_option("upsellio_google_ads_include_scope", "1", false);
    } elseif ($scope_mode === "default") {
        update_option("upsellio_google_ads_include_scope", "0", false);
    }

    $return_success = upsellio_google_managed_oauth_validate_return_url($return_success);
    $pending_raw = get_transient(upsellio_google_oauth_transient_key($wp_user_id));
    upsellio_google_managed_oauth_bridge_save_session(
        $state,
        [
            "wp_user_id" => $wp_user_id,
            "return_success" => $return_success,
            "conn_type" => sanitize_key((string) (is_array($pending_raw) ? ($pending_raw["conn_type"] ?? "main") : "main")),
        ]
    );

    $creds = upsellio_google_managed_oauth_bridge_credentials();
    $redirect_uri = upsellio_google_managed_oauth_bridge_redirect_uri_for_pending(
        is_array($pending_raw) ? $pending_raw : null
    );
    $scopes = function_exists("upsellio_google_oauth_scope_string")
        ? upsellio_google_oauth_scope_string()
        : "https://www.googleapis.com/auth/webmasters.readonly https://www.googleapis.com/auth/analytics.readonly";

    $auth_url = add_query_arg(
        [
            "client_id" => $creds["client_id"],
            "redirect_uri" => $redirect_uri,
            "response_type" => "code",
            "scope" => $scopes,
            "access_type" => "offline",
            "prompt" => (function_exists("ups_audit_google_oauth_prompt_param")
                && is_array($pending_raw)
                && sanitize_key((string) ($pending_raw["conn_type"] ?? "")) === "audit")
                ? ups_audit_google_oauth_prompt_param()
                : "consent",
            "include_granted_scopes" => "true",
            "state" => $state,
        ],
        "https://accounts.google.com/o/oauth2/v2/auth"
    );

    if (function_exists("upsellio_gsc_log")) {
        upsellio_gsc_log("google.oauth.bridge_start", [
            "user_id" => $wp_user_id,
            "conn_type" => sanitize_key((string) (is_array($pending_raw) ? ($pending_raw["conn_type"] ?? "main") : "main")),
        ], upsellio_gsc_debug_trace_id());
    }

    wp_redirect(esc_url_raw($auth_url));
    exit;
}

function upsellio_handle_google_oauth_bridge_callback(WP_REST_Request $request)
{
    upsellio_google_managed_oauth_bridge_send_nocache_headers();

    if (!upsellio_google_managed_oauth_is_self_hosted()) {
        return new WP_Error("ups_managed_bridge_disabled", "Wbudowany most OAuth nie jest aktywny.", ["status" => 501]);
    }

    $return_fallback = function_exists("upsellio_site_analytics_admin_url")
        ? upsellio_site_analytics_admin_url()
        : home_url("/crm-app/?view=analytics&atab=today");
    $error = sanitize_text_field((string) $request->get_param("error"));
    $state = sanitize_text_field((string) $request->get_param("state"));
    $session = $state !== "" ? upsellio_google_managed_oauth_bridge_load_session($state) : null;
    $return_success = is_array($session) ? (string) ($session["return_success"] ?? $return_fallback) : $return_fallback;

    if ($error !== "") {
        $desc = sanitize_text_field((string) $request->get_param("error_description"));
        $msg = function_exists("ups_audit_oauth_format_google_error")
            ? ups_audit_oauth_format_google_error($error, $desc)
            : ($desc !== "" ? "{$error}: {$desc}" : $error);
        upsellio_google_managed_oauth_bridge_error_redirect($return_success, $msg);
    }

    if ($state === "" || !is_array($session)) {
        upsellio_google_managed_oauth_bridge_error_redirect(
            $return_fallback,
            "Sesja mostu OAuth wygasła — kliknij „Zaloguj przez Google” jeszcze raz (nie odświeżaj strony Google wstecz)."
        );
    }

    $wp_user_id = (int) ($session["wp_user_id"] ?? 0);
    $code = (string) $request->get_param("code");

    if ($code === "" || $wp_user_id <= 0) {
        upsellio_google_managed_oauth_bridge_error_redirect($return_success, "Brak kodu autoryzacji Google.");
    }

    $pending_for_uri = get_transient(upsellio_google_oauth_transient_key($wp_user_id));
    $redirect_for_exchange = upsellio_google_managed_oauth_bridge_redirect_uri_for_pending(
        is_array($pending_for_uri) ? $pending_for_uri : null
    );
    if (
        is_array($session)
        && sanitize_key((string) ($session["conn_type"] ?? "")) === "audit"
        && function_exists("ups_audit_oauth_redirect_uri")
    ) {
        $redirect_for_exchange = ups_audit_oauth_redirect_uri();
    }
    $exchange = upsellio_google_managed_oauth_exchange_code($code, $redirect_for_exchange);
    if (is_wp_error($exchange)) {
        upsellio_google_managed_oauth_bridge_error_redirect($return_success, $exchange->get_error_message());
    }

    $creds = upsellio_google_managed_oauth_bridge_credentials();
    $handoff = upsellio_google_managed_oauth_internal_handoff([
        "state" => $state,
        "refresh_token" => (string) $exchange["refresh_token"],
        "client_id" => $creds["client_id"],
        "client_secret" => $creds["client_secret"],
        "wp_user_id" => $wp_user_id,
        "refresh_token_expires_in" => 0,
    ]);

    if (is_wp_error($handoff)) {
        upsellio_google_managed_oauth_bridge_error_redirect($return_success, $handoff->get_error_message());
    }

    $account_id = 0;
    if ($handoff instanceof WP_REST_Response) {
        $data = $handoff->get_data();
        if (is_array($data)) {
            $account_id = (int) ($data["account_id"] ?? 0);
        }
    }

    $conn_type = sanitize_key((string) ($session["conn_type"] ?? "main"));
    $target = upsellio_google_managed_oauth_validate_return_url($return_success);

    if ($conn_type === "audit") {
        if (function_exists("ups_audit_oauth_crm_success_url")) {
            $target = ups_audit_oauth_crm_success_url($account_id > 0 ? $account_id : 0);
        } else {
            $target = add_query_arg(
                ["view" => "ca-accounts", "connected" => "1", "account_id" => max(0, $account_id)],
                home_url("/crm-app/")
            );
        }
    } elseif (function_exists("upsellio_site_analytics_admin_url")) {
        $target = upsellio_site_analytics_admin_url([
            "upsellio_managed_oauth_return" => "1",
        ]);
    }

    upsellio_google_managed_oauth_bridge_clear_session($state);

    wp_safe_redirect($target);
    exit;
}

/**
 * Powiadomienia po powrocie z zarządzanego OAuth (admin).
 */
function upsellio_google_managed_oauth_admin_notices(): void
{
    if (!is_admin() || !current_user_can("edit_posts")) {
        return;
    }

    if (isset($_GET["upsellio_managed_oauth_return"])) {
        echo '<div class="notice notice-success is-dismissible"><p><strong>Upsellio Connect:</strong> Konto Google zostało połączone.</p></div>';
    }

    if (isset($_GET["upsellio_managed_oauth_error"])) {
        $err = rawurldecode(sanitize_text_field((string) wp_unslash($_GET["upsellio_managed_oauth_error"])));
        echo '<div class="notice notice-error is-dismissible"><p><strong>Upsellio Connect:</strong> ' . esc_html($err) . "</p></div>";
    }
}

add_action("admin_notices", "upsellio_google_managed_oauth_admin_notices");

/**
 * Wszystkie URI do wpisania w Google Cloud (Connect + legacy).
 *
 * @return list<string>
 */
function upsellio_google_oauth_required_redirect_uris_for_google_console(): array
{
    $uris = [];
    if (function_exists("upsellio_google_managed_oauth_is_active") && upsellio_google_managed_oauth_is_active()) {
        $uris[] = upsellio_google_managed_oauth_bridge_callback_uri();
        if (function_exists("upsellio_google_managed_oauth_bridge_callback_uri_slash")) {
            $uris[] = upsellio_google_managed_oauth_bridge_callback_uri_slash();
        }
    }
    if (function_exists("upsellio_google_oauth_rest_redirect_uri_default")) {
        $uris[] = upsellio_google_oauth_rest_redirect_uri_default();
    }
    if (function_exists("upsellio_google_oauth_admin_redirect_uri_default")) {
        $uris[] = upsellio_google_oauth_admin_redirect_uri_default();
    }
    if (function_exists("ups_audit_oauth_redirect_uri")) {
        $uris[] = ups_audit_oauth_redirect_uri();
    }
    $rest_route = add_query_arg("rest_route", "/upsellio/v1/google-oauth/callback", home_url("/"));
    if (function_exists("upsellio_google_oauth_normalize_redirect_uri_string")) {
        $uris[] = upsellio_google_oauth_normalize_redirect_uri_string($rest_route);
    }
    $uris = array_values(array_unique(array_filter(array_map("strval", $uris))));

    return (array) apply_filters("upsellio_google_oauth_required_redirect_uris", $uris);
}

/**
 * @param list<string> $variants
 * @return list<string>
 */
function upsellio_google_oauth_append_bridge_redirect_variants(array $variants, string $primary): array
{
    foreach (upsellio_google_oauth_required_redirect_uris_for_google_console() as $u) {
        $variants[] = $u;
    }

    return array_values(array_unique(array_filter(array_map("strval", $variants))));
}

add_filter("upsellio_google_oauth_redirect_uri_variants", "upsellio_google_oauth_append_bridge_redirect_variants", 20, 2);
