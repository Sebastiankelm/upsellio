<?php

if (!defined("ABSPATH")) {
    exit;
}

/**
 * Scentralizowany OAuth Google (model jak Rank Math) — użytkownik strony NIE konfiguruje Google Cloud.
 *
 * Wymaga osobnej usługi HTTPS (most Upsellio) z:
 * - jednym projektem Google Cloud + typem Web application,
 * - stałymi Authorized redirect URI wskazującymi na domenę mostu (np. https://oauth.upsellio.pl/.../callback),
 * - po sukcesie: bezpieczne przekazanie refresh tokena do WordPressa (poniżej: POST + HMAC).
 *
 * Włączenie: w wp-config.php (lub filtr):
 *   define( 'UPSELLIO_MANAGED_GOOGLE_OAUTH_BASE', 'https://oauth.upsellio.pl' );
 *   define( 'UPSELLIO_MANAGED_GOOGLE_OAUTH_WEBHOOK_SECRET', 'wspólny-sekret-z-mostu' );
 *
 * Most musi implementować start URL (przekierowanie do Google) i po OAuth POST na handoff poniżej.
 */
function upsellio_google_managed_oauth_bridge_base(): string
{
    if (defined("UPSELLIO_MANAGED_GOOGLE_OAUTH_BASE") && is_string(UPSELLIO_MANAGED_GOOGLE_OAUTH_BASE)) {
        $b = trim(UPSELLIO_MANAGED_GOOGLE_OAUTH_BASE);
    } else {
        $b = "";
    }

    $b = (string) apply_filters("upsellio_google_managed_oauth_bridge_base", $b);
    $b = rtrim($b, "/");
    if ($b !== "" && stripos($b, "https://") !== 0) {
        return "";
    }

    return $b;
}

function upsellio_google_managed_oauth_is_active(): bool
{
    if (!(bool) apply_filters("upsellio_google_managed_oauth_enabled", true)) {
        return false;
    }

    return upsellio_google_managed_oauth_bridge_base() !== "";
}

function upsellio_google_managed_oauth_webhook_secret(): string
{
    if (defined("UPSELLIO_MANAGED_GOOGLE_OAUTH_WEBHOOK_SECRET") && is_string(UPSELLIO_MANAGED_GOOGLE_OAUTH_WEBHOOK_SECRET)) {
        $s = (string) UPSELLIO_MANAGED_GOOGLE_OAUTH_WEBHOOK_SECRET;
    } else {
        $s = (string) get_option("upsellio_managed_oauth_webhook_secret", "");
    }

    return (string) apply_filters("upsellio_google_managed_oauth_webhook_secret", $s);
}

/**
 * Buduje URL startu na moście Upsellio (Google OAuth → callback na moście → handoff do WP).
 */
function upsellio_google_managed_oauth_build_start_url(string $state, string $return_success_url, int $wp_user_id): string
{
    $base = upsellio_google_managed_oauth_bridge_base();
    $path = (string) apply_filters("upsellio_google_managed_oauth_start_path", "/v1/google-oauth/start");

    return add_query_arg(
        [
            "site_url" => home_url("/"),
            "return_success" => rawurlencode($return_success_url),
            "state" => $state,
            "wp_user_id" => (string) $wp_user_id,
            "scope_mode" => (string) get_option("upsellio_google_ads_include_scope", "0") === "1" ? "with_ads" : "default",
        ],
        $base . $path
    );
}

/**
 * Obsługa przycisku „Zaloguj przez Google” w trybie managed — zapisuje pola pomocnicze, transient, redirect na most.
 * Kończy żądanie (redirect + exit), jeśli tryb managed jest aktywny i to jest ten formularz.
 */
function upsellio_google_managed_oauth_try_handle_connect_post(): void
{
    if (!is_admin() || !current_user_can("edit_posts")) {
        return;
    }

    if (!isset($_POST["upsellio_google_oauth_start"])) {
        return;
    }

    if (!upsellio_google_managed_oauth_is_active()) {
        return;
    }

    check_admin_referer("upsellio_google_oauth_start_action", "upsellio_google_oauth_start_nonce");

    if (isset($_POST["g_oauth_redirect_uri_override"])) {
        $ov_raw = trim(wp_unslash((string) $_POST["g_oauth_redirect_uri_override"]));
        if ($ov_raw === "") {
            delete_option(upsellio_google_oauth_redirect_uri_override_option_key());
        } elseif (filter_var($ov_raw, FILTER_VALIDATE_URL)) {
            $ov_norm = upsellio_google_oauth_normalize_redirect_uri_string(esc_url_raw($ov_raw));
            if (upsellio_google_oauth_redirect_uri_is_allowed_host($ov_norm)) {
                update_option(upsellio_google_oauth_redirect_uri_override_option_key(), $ov_norm, false);
            }
        }
    }

    $use_rest_on = isset($_POST["upsellio_google_oauth_use_rest"]) && (string) wp_unslash($_POST["upsellio_google_oauth_use_rest"]) === "1";
    update_option(upsellio_google_oauth_use_rest_callback_option_key(), $use_rest_on ? "1" : "0", false);

    $gsc_property_in = isset($_POST["g_oauth_gsc_property"]) ? wp_unslash($_POST["g_oauth_gsc_property"]) : "";
    $ga4_id_in = isset($_POST["g_oauth_ga4_property_id"]) ? wp_unslash($_POST["g_oauth_ga4_property_id"]) : "";

    $existing = upsellio_get_gsc_credentials();
    $gsc_property = trim((string) $gsc_property_in) !== ""
        ? sanitize_text_field(trim((string) $gsc_property_in))
        : (string) ($existing["property"] ?? "");

    upsellio_save_gsc_credentials(
        (string) ($existing["client_id"] ?? ""),
        (string) ($existing["client_secret"] ?? ""),
        (string) ($existing["refresh_token"] ?? ""),
        $gsc_property
    );

    if (trim((string) $ga4_id_in) !== "") {
        upsellio_save_ga4_property_id($ga4_id_in);
    }

    $ads_scope_on = isset($_POST["g_oauth_include_google_ads"]) && (string) wp_unslash($_POST["g_oauth_include_google_ads"]) === "1";
    update_option("upsellio_google_ads_include_scope", $ads_scope_on ? "1" : "0", false);

    $state = bin2hex(random_bytes(16));
    $uid = get_current_user_id();
    set_transient(
        upsellio_google_oauth_transient_key($uid),
        [
            "state" => $state,
            "gsc_property" => $gsc_property,
            "ga4_property_id" => preg_replace("/\D+/", "", (string) $ga4_id_in),
            "managed_oauth" => true,
        ],
        15 * MINUTE_IN_SECONDS
    );

    $return_ok = upsellio_site_analytics_admin_url([
        "upsellio_managed_oauth_return" => "1",
    ]);

    $start_url = upsellio_google_managed_oauth_build_start_url($state, $return_ok, $uid);

    upsellio_gsc_log("google.oauth.managed_redirect", [
        "user_id" => $uid,
        "bridge_base" => upsellio_google_managed_oauth_bridge_base(),
    ], upsellio_gsc_debug_trace_id());

    wp_redirect(esc_url_raw($start_url));
    exit;
}

/**
 * POST JSON z mostu po stronie serwera — podpis nagłówkiem X-Upsellio-Signature (sha256 HMAC raw body).
 */
function upsellio_register_google_managed_oauth_handoff_route(): void
{
    register_rest_route("upsellio/v1", "/google-managed-oauth-handoff", [
        "methods" => "POST",
        "callback" => "upsellio_handle_google_managed_oauth_handoff_rest",
        "permission_callback" => "__return_true",
    ]);
}
add_action("rest_api_init", "upsellio_register_google_managed_oauth_handoff_route");

function upsellio_handle_google_managed_oauth_handoff_rest(WP_REST_Request $request)
{
    $secret = upsellio_google_managed_oauth_webhook_secret();
    if ($secret === "") {
        return new WP_Error(
            "upsellio_managed_oauth_not_configured",
            "Brak UPSELLIO_MANAGED_GOOGLE_OAUTH_WEBHOOK_SECRET (lub opcji) — handoff wyłączony.",
            ["status" => 501]
        );
    }

    $raw = $request->get_body();
    $sig_in = $request->get_header("x-upsellio-signature");
    $sig_in = is_string($sig_in) ? trim($sig_in) : "";
    $expected = hash_hmac("sha256", $raw, $secret);
    if ($sig_in === "" || !hash_equals($expected, $sig_in)) {
        return new WP_Error("upsellio_managed_oauth_bad_signature", "Nieprawidłowy podpis.", ["status" => 403]);
    }

    $body = json_decode($raw, true);
    if (!is_array($body)) {
        return new WP_Error("upsellio_managed_oauth_bad_json", "Oczekiwano JSON.", ["status" => 400]);
    }

    $state_in = isset($body["state"]) ? (string) $body["state"] : "";
    $refresh = isset($body["refresh_token"]) ? trim((string) $body["refresh_token"]) : "";
    $client_id = isset($body["client_id"]) ? trim((string) $body["client_id"]) : "";
    $client_secret = isset($body["client_secret"]) ? trim((string) $body["client_secret"]) : "";
    $wp_user_id = isset($body["wp_user_id"]) ? (int) $body["wp_user_id"] : 0;

    if ($state_in === "" || $refresh === "" || $client_id === "" || $client_secret === "") {
        return new WP_Error("upsellio_managed_oauth_missing_fields", "Brak state / refresh_token / client_id / client_secret.", ["status" => 400]);
    }

    if ($wp_user_id <= 0) {
        return new WP_Error("upsellio_managed_oauth_bad_user", "Brak poprawnego wp_user_id.", ["status" => 400]);
    }

    $pending = upsellio_google_oauth_get_pending($wp_user_id);
    if ($pending === null || !hash_equals($pending["state"], $state_in)) {
        return new WP_Error("upsellio_managed_oauth_bad_state", "Nieprawidłowy stan OAuth.", ["status" => 400]);
    }

    delete_transient(upsellio_google_oauth_transient_key($wp_user_id));

    $gsc_property = $pending["gsc_property"] !== ""
        ? sanitize_text_field($pending["gsc_property"])
        : (string) (upsellio_get_gsc_credentials()["property"] ?? "");

    upsellio_save_gsc_credentials($client_id, $client_secret, $refresh, $gsc_property);

    if ($pending["ga4_property_id"] !== "") {
        upsellio_save_ga4_property_id($pending["ga4_property_id"]);
    }

    $access_probe = upsellio_gsc_get_access_token(upsellio_get_gsc_credentials(), upsellio_gsc_debug_trace_id());
    if (is_string($access_probe) && $access_probe !== "") {
        upsellio_google_fetch_and_store_permissions_from_access_token($access_probe);
    }

    upsellio_gsc_log("google.oauth.managed_handoff.success", ["user_id" => $wp_user_id], upsellio_gsc_debug_trace_id());

    return new WP_REST_Response(["ok" => true], 200);
}
