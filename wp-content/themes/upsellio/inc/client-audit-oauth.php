<?php

if (!defined("ABSPATH")) {
    exit;
}

if (defined("UPS_AUDIT_OAUTH_LOADED")) {
    return;
}
define("UPS_AUDIT_OAUTH_LOADED", true);

/**
 * OAuth kont Google w CRM — jeden endpoint admin-post (bez hooków na /crm-app/).
 * Google Console → Authorized redirect URIs (URI #4 u Ciebie):
 * https://upsellio.pl/crm-app/?view=ca-accounts
 * (opcjonalnie też: /index.php?pagename=crm-app&view=ca-accounts)
 */

if (!defined("UPS_AUDIT_OAUTH_ACTION")) {
    define("UPS_AUDIT_OAUTH_ACTION", "ups_audit_google_oauth");
}

function ups_audit_oauth_action_url(array $extra = []): string
{
    return add_query_arg(
        array_merge(["action" => UPS_AUDIT_OAUTH_ACTION], $extra),
        admin_url("admin-post.php")
    );
}

/**
 * Redirect URI w Google Cloud — musi być 1:1 z „Authorized redirect URIs”.
 * Domyślnie /crm-app/ (już wpisane w Console użytkownika).
 */
function ups_audit_oauth_redirect_uri(): string
{
    return ups_audit_oauth_redirect_uri_pretty();
}

/** Wewnętrzny handler (nie dodawaj do Google). */
function ups_audit_oauth_redirect_uri_handler(): string
{
    $uri = ups_audit_oauth_action_url();
    if (function_exists("upsellio_google_oauth_normalize_redirect_uri_string")) {
        return upsellio_google_oauth_normalize_redirect_uri_string($uri);
    }

    return untrailingslashit(esc_url_raw($uri));
}

function ups_audit_oauth_redirect_uri_pretty(): string
{
    // Musi być bajt w bajt jak „Authorized redirect URI” #4 w Google Console.
    $uri = home_url("/crm-app/?view=ca-accounts");
    if (function_exists("upsellio_google_oauth_normalize_redirect_uri_string")) {
        return upsellio_google_oauth_normalize_redirect_uri_string($uri);
    }

    return esc_url_raw($uri);
}

/** Alternatywa (LiteSpeed) — tylko wymiana tokenu, nie w żądaniu authorize. */
function ups_audit_oauth_redirect_uri_index(): string
{
    $uri = home_url("/index.php?pagename=crm-app&view=ca-accounts");
    if (function_exists("upsellio_google_oauth_normalize_redirect_uri_string")) {
        return upsellio_google_oauth_normalize_redirect_uri_string($uri);
    }

    return esc_url_raw($uri);
}

function ups_audit_oauth_request_query_sources(): array
{
    $sources = [];
    foreach (
        [
            "QUERY_STRING",
            "REDIRECT_QUERY_STRING",
            "HTTP_X_ORIGINAL_QUERY_STRING",
            "HTTP_X_FORWARDED_QUERY",
        ] as $key
    ) {
        if (!empty($_SERVER[$key])) {
            $sources[] = (string) $_SERVER[$key];
        }
    }

    foreach (["REQUEST_URI", "HTTP_X_ORIGINAL_URL", "REDIRECT_URL"] as $key) {
        if (empty($_SERVER[$key])) {
            continue;
        }
        $uri = (string) $_SERVER[$key];
        $qpos = strpos($uri, "?");
        if ($qpos !== false) {
            $sources[] = substr($uri, $qpos + 1);
        }
    }

    return array_values(array_unique(array_filter($sources)));
}

function ups_audit_oauth_extract_code_from_query(string $query): string
{
    $query = ltrim($query, "?&");
    if ($query === "") {
        return "";
    }

    if (preg_match("/(?:^|[?&])code=([^&]+)/", $query, $matches)) {
        $code = rawurldecode((string) $matches[1]);
        if ($code !== "" && $code !== "4") {
            return $code;
        }
        if (strpos($code, "4/") === 0) {
            return $code;
        }
    }

    return "";
}

function ups_audit_oauth_request_code(): string
{
    foreach (ups_audit_oauth_request_query_sources() as $query) {
        $code = ups_audit_oauth_extract_code_from_query($query);
        if ($code !== "") {
            return $code;
        }
    }

    if (isset($_GET["code"])) {
        $code = (string) wp_unslash($_GET["code"]);
        if ($code !== "" && $code !== "4") {
            return $code;
        }
        if (strpos($code, "4/") === 0) {
            return $code;
        }
    }

    return "";
}

function ups_audit_oauth_request_state(): string
{
    foreach (ups_audit_oauth_request_query_sources() as $query) {
        if (preg_match("/(?:^|[?&])state=([a-f0-9]{16,64})/i", $query, $matches)) {
            return strtolower((string) $matches[1]);
        }
    }

    if (isset($_GET["state"])) {
        $state = sanitize_text_field((string) wp_unslash($_GET["state"]));

        return preg_match("/^[a-f0-9]{16,64}$/i", $state) ? strtolower($state) : "";
    }

    return "";
}

function ups_audit_oauth_is_crm_return_request(): bool
{
    if (isset($_GET["error"]) || isset($_GET["oauth_resume"])) {
        return true;
    }

    if (ups_audit_oauth_request_code() !== "" || ups_audit_oauth_request_state() !== "") {
        return true;
    }

    return false;
}

function ups_audit_oauth_pending_state_key(string $state): string
{
    $state = preg_replace("/[^a-f0-9]/", "", strtolower($state));

    return "ups_audit_oauth_st_" . $state;
}

function ups_audit_oauth_callback_payload_key(string $state): string
{
    $state = preg_replace("/[^a-f0-9]/", "", strtolower($state));

    return "ups_audit_oauth_cb_" . $state;
}

function ups_audit_oauth_mirror_pending_by_state(string $state, array $pending, int $ttl = 1800): void
{
    $state = sanitize_text_field($state);
    if ($state === "" || !isset($pending["state"])) {
        return;
    }

    set_transient(ups_audit_oauth_pending_state_key($state), $pending, $ttl);
}

function ups_audit_oauth_resolve_pending_session(string $state_in, int $uid = 0): ?array
{
    $state_in = sanitize_text_field($state_in);
    if ($state_in === "") {
        return null;
    }

    if ($uid > 0 && function_exists("upsellio_google_oauth_transient_key")) {
        $by_user = get_transient(upsellio_google_oauth_transient_key($uid));
        if (is_array($by_user) && isset($by_user["state"]) && hash_equals((string) $by_user["state"], $state_in)) {
            return $by_user;
        }
    }

    $by_state = get_transient(ups_audit_oauth_pending_state_key($state_in));
    if (is_array($by_state) && isset($by_state["state"]) && hash_equals((string) $by_state["state"], $state_in)) {
        return $by_state;
    }

    return null;
}

/** Gdy transient zginął (np. błąd pamięci), ale Google zwrócił poprawny state + code. */
function ups_audit_oauth_fallback_pending(string $state, int $uid = 0): array
{
    return [
        "state" => $state,
        "gsc_property" => "",
        "ga4_property_id" => "",
        "managed_oauth" => false,
        "conn_type" => "audit",
        "label" => "",
        "include_ads" => (string) get_option("upsellio_google_ads_include_scope", "0") === "1" ? "1" : "0",
        "wp_user_id" => (int) $uid,
    ];
}

function ups_audit_oauth_crm_resume_url(string $state = ""): string
{
    $args = ["view" => "ca-accounts"];
    if ($state !== "") {
        $args["oauth_resume"] = $state;
    }

    return add_query_arg($args, home_url("/crm-app/"));
}

function ups_audit_oauth_store_callback_payload(): string
{
    $code = ups_audit_oauth_request_code();
    $state = ups_audit_oauth_request_state();
    if ($code === "" || $state === "") {
        return "";
    }

    set_transient(
        ups_audit_oauth_callback_payload_key($state),
        [
            "code" => $code,
            "state" => $state,
            "error" => isset($_GET["error"]) ? sanitize_text_field((string) wp_unslash($_GET["error"])) : "",
            "error_description" => isset($_GET["error_description"])
                ? sanitize_text_field((string) wp_unslash($_GET["error_description"]))
                : "",
        ],
        HOUR_IN_SECONDS
    );

    return $state;
}

function ups_audit_oauth_restore_callback_payload(): bool
{
    $resume = "";
    if (isset($_GET["oauth_resume"])) {
        $resume = sanitize_text_field((string) wp_unslash($_GET["oauth_resume"]));
    } elseif (isset($_GET["state"])) {
        $resume = ups_audit_oauth_request_state();
    }

    if ($resume === "") {
        return false;
    }

    $payload = get_transient(ups_audit_oauth_callback_payload_key($resume));
    if (!is_array($payload)) {
        return false;
    }

    if (!empty($payload["code"])) {
        $_GET["code"] = (string) $payload["code"];
        $_REQUEST["code"] = $_GET["code"];
    }
    if (!empty($payload["state"])) {
        $_GET["state"] = (string) $payload["state"];
        $_REQUEST["state"] = $_GET["state"];
    }
    if (!empty($payload["error"])) {
        $_GET["error"] = (string) $payload["error"];
        $_REQUEST["error"] = $_GET["error"];
    }
    if (!empty($payload["error_description"])) {
        $_GET["error_description"] = (string) $payload["error_description"];
        $_REQUEST["error_description"] = $_GET["error_description"];
    }

    return true;
}

function ups_audit_oauth_exit_redirect(string $url): void
{
    $url = trim($url);
    if ($url === "") {
        status_header(500);
        echo "Przekierowanie OAuth nie powiodło się.";
        exit;
    }

    wp_safe_redirect($url);
    exit;
}

function ups_audit_oauth_crm_error_url(string $message): string
{
    return add_query_arg(
        ["view" => "ca-accounts", "ups_audit_error" => rawurlencode($message)],
        home_url("/crm-app/")
    );
}

function ups_audit_oauth_crm_success_url(int $account_id): string
{
    return add_query_arg(
        [
            "view" => "ca-accounts",
            "connected" => "1",
            "account_id" => max(0, $account_id),
        ],
        home_url("/crm-app/")
    );
}

function ups_audit_oauth_format_google_error(string $code, string $description = ""): string
{
    $code = sanitize_key($code);
    $description = sanitize_text_field($description);

    if ($code === "access_denied") {
        return "Google zablokował logowanie (access_denied). W Google Cloud → OAuth consent screen → Test users dodaj e-mail konta Google (np. seba.k434@gmail.com).";
    }

    if ($code === "invalid_grant") {
        return "Kod OAuth wygasł lub został już użyty (np. odświeżenie strony po powrocie z Google). "
            . "Kliknij ponownie „Dodaj Google Ads (ten sam Gmail)” i przejdź cały flow w jednej karcie — nie wklejaj starego URL z paska.";
    }

    if ($description !== "") {
        return $code !== "" ? "{$code}: {$description}" : $description;
    }

    return $code !== "" ? $code : "Logowanie Google nie powiodło się.";
}

function ups_audit_oauth_exchange_code(string $code, string $redirect_uri, string $client_id, string $client_secret)
{
    $code = trim($code);
    $redirect_uri = trim($redirect_uri);
    if ($code === "" || $client_id === "" || $client_secret === "") {
        return new WP_Error("ups_audit_oauth_missing", "Brak kodu lub danych aplikacji OAuth.");
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
    $body = json_decode((string) wp_remote_retrieve_body($response), true);
    if ($status >= 400) {
        $err_code = is_array($body) ? sanitize_key((string) ($body["error"] ?? "")) : "";
        $err_desc = is_array($body) ? sanitize_text_field((string) ($body["error_description"] ?? "")) : "";
        if ($err_code === "invalid_grant") {
            return new WP_Error(
                "ups_audit_oauth_invalid_grant",
                ups_audit_oauth_format_google_error("invalid_grant", $err_desc)
            );
        }
        $msg = function_exists("upsellio_gsc_extract_error_message")
            ? upsellio_gsc_extract_error_message(is_array($body) ? $body : [], "Wymiana kodu OAuth nie powiodła się.")
            : "Wymiana kodu OAuth nie powiodła się.";

        return new WP_Error("ups_audit_oauth_http", $msg, ["status" => $status]);
    }

    $refresh = is_array($body) ? trim((string) ($body["refresh_token"] ?? "")) : "";
    if ($refresh === "") {
        return new WP_Error(
            "ups_audit_oauth_no_refresh",
            "Google nie zwrócił refresh tokena. Usuń dostęp aplikacji w koncie Google i połącz ponownie."
        );
    }

    return [
        "refresh_token" => $refresh,
        "access_token" => is_array($body) ? trim((string) ($body["access_token"] ?? "")) : "",
        "body" => is_array($body) ? $body : [],
    ];
}

function ups_audit_oauth_auth_code_spent_key(string $code): string
{
    return "ups_audit_oauth_spent_" . substr(hash("sha256", $code), 0, 32);
}

/** Jednorazowe użycie authorization code (Google unieważnia kod po wymianie). */
function ups_audit_oauth_claim_auth_code(string $code): bool
{
    $code = trim($code);
    if ($code === "") {
        return false;
    }

    $key = ups_audit_oauth_auth_code_spent_key($code);
    if (get_transient($key)) {
        return false;
    }

    set_transient($key, "1", 15 * MINUTE_IN_SECONDS);

    return true;
}

function ups_audit_oauth_exchange_code_for_audit(string $code, string $client_id, string $client_secret)
{
    return ups_audit_oauth_exchange_code(
        $code,
        ups_audit_oauth_redirect_uri_pretty(),
        $client_id,
        $client_secret
    );
}

function ups_audit_oauth_prime_account_resources(int $account_id): void
{
    $account_id = (int) $account_id;
    if ($account_id <= 0 || !function_exists("ups_audit_fetch_ga4_resources")) {
        return;
    }

    $cache = [
        "ga4" => ups_audit_fetch_ga4_resources($account_id),
        "gsc" => ups_audit_fetch_gsc_resources($account_id),
        "ads" => ups_audit_fetch_ads_resources($account_id),
    ];
    update_post_meta($account_id, "_ups_gacc_resources_cache", $cache);
    update_post_meta($account_id, "_ups_gacc_last_sync_at", current_time("mysql"));
}

function ups_audit_oauth_ensure_managed_connect(): void
{
    if (function_exists("upsellio_google_managed_oauth_ensure_webhook_secret")) {
        upsellio_google_managed_oauth_ensure_webhook_secret();
    }
}

function ups_audit_oauth_managed_is_available(): bool
{
    ups_audit_oauth_ensure_managed_connect();

    return function_exists("upsellio_google_managed_oauth_is_active") && upsellio_google_managed_oauth_is_active();
}

function ups_audit_oauth_connection_info(): array
{
    $creds = function_exists("upsellio_get_gsc_credentials") ? upsellio_get_gsc_credentials() : [];
    $has_app_full = trim((string) ($creds["client_id"] ?? "")) !== ""
        && trim((string) ($creds["client_secret"] ?? "")) !== "";
    $crm_uri = ups_audit_oauth_redirect_uri();

    return [
        "mode" => $has_app_full ? "direct" : "unavailable",
        "ready" => $has_app_full,
        "message" => $has_app_full
            ? "Zaloguj przez Google (URI musi być w Google Console jak poniżej)."
            : "Uzupełnij Client ID i Secret w Analityce SEO.",
        "redirect_uri" => $crm_uri,
        "managed" => ups_audit_oauth_managed_is_available(),
        "has_app_credentials" => $has_app_full,
        "google_console_uri" => $crm_uri,
        "google_console_uri_index" => function_exists("ups_audit_oauth_redirect_uri_index")
            ? ups_audit_oauth_redirect_uri_index()
            : "",
        "google_console_uri_handler" => ups_audit_oauth_redirect_uri_handler(),
        "google_client_id" => trim((string) ($creds["client_id"] ?? "")),
    ];
}

/** Link startu OAuth (zalogowany → Google; niezalogowany → wp-login). */
function ups_audit_oauth_connect_url(bool $include_ads = true, string $label = "", int $reconnect_account_id = 0): string
{
    $args = ["op" => "start", "include_ads" => $include_ads ? "1" : "0"];
    if ($label !== "") {
        $args["label"] = sanitize_text_field($label);
    }
    $reconnect_account_id = (int) $reconnect_account_id;
    if ($reconnect_account_id > 0) {
        $args["reconnect_id"] = $reconnect_account_id;
    }

    return ups_audit_oauth_action_url($args);
}

/** Ponowne OAuth tego samego Gmail (np. dopięcie zakresu adwords) — nie tworzy nowej karty. */
function ups_audit_oauth_reconnect_account_url(int $account_id, bool $include_ads = true): string
{
    $account_id = (int) $account_id;
    if ($account_id <= 0) {
        return ups_audit_oauth_connect_url($include_ads);
    }

    $label = (string) get_post_meta($account_id, "_ups_gacc_label", true);
    $email = (string) get_post_meta($account_id, "_ups_gacc_email", true);
    if ($label === "" && $email !== "") {
        $label = $email;
    }

    return ups_audit_oauth_connect_url($include_ads, $label, $account_id);
}

function ups_audit_oauth_login_entry_url(bool $include_ads = true, string $label = ""): string
{
    return wp_login_url(ups_audit_oauth_connect_url($include_ads, $label));
}

function ups_audit_oauth_admin_post_connect_url(bool $include_ads = true, string $label = ""): string
{
    return ups_audit_oauth_connect_url($include_ads, $label);
}

function ups_audit_oauth_direct_connect_return_url(bool $include_ads = true, string $label = ""): string
{
    return ups_audit_oauth_connect_url($include_ads, $label);
}

function ups_audit_oauth_process_callback(): void
{
    ups_audit_oauth_restore_callback_payload();

    $code = ups_audit_oauth_request_code();
    $state = ups_audit_oauth_request_state();
    $has_code = $code !== "" && $state !== "";
    $has_error = isset($_GET["error"]);

    if (!$has_code && !$has_error) {
        $hint = isset($_GET["oauth_resume"])
            ? "Kod OAuth nie dotarł do serwera (często obcięcie URL z code=4/0…). Połącz konto ponownie — używamy teraz index.php jako redirect URI."
            : "Brak kodu OAuth z Google.";
        ups_audit_oauth_exit_redirect(ups_audit_oauth_crm_error_url($hint));
    }

    if (!is_user_logged_in()) {
        $stored = ups_audit_oauth_store_callback_payload();
        if ($stored === "") {
            $stored = $state;
        }
        ups_audit_oauth_exit_redirect(
            wp_login_url(ups_audit_oauth_crm_resume_url($stored !== "" ? $stored : $state))
        );
    }

    if (!current_user_can("edit_posts")) {
        ups_audit_oauth_exit_redirect(ups_audit_oauth_crm_error_url("Brak uprawnień do połączenia Google."));
    }

    $uid = get_current_user_id();

    if ($has_error) {
        $err = sanitize_text_field((string) wp_unslash($_GET["error"]));
        $desc = isset($_GET["error_description"]) ? sanitize_text_field((string) wp_unslash($_GET["error_description"])) : "";
        delete_transient(upsellio_google_oauth_transient_key($uid));
        ups_audit_oauth_exit_redirect(ups_audit_oauth_crm_error_url(ups_audit_oauth_format_google_error($err, $desc)));
    }

    $pending = ups_audit_oauth_resolve_pending_session($state, $uid);
    if (!is_array($pending) || sanitize_key((string) ($pending["conn_type"] ?? "")) !== "audit") {
        if ($has_code && $state !== "") {
            $pending = ups_audit_oauth_fallback_pending($state, $uid);
        } else {
            ups_audit_oauth_exit_redirect(
                ups_audit_oauth_crm_error_url("Sesja OAuth wygasła — kliknij „Połącz konto” ponownie.")
            );
        }
    }

    $creds = upsellio_get_gsc_credentials();
    $client_id = (string) ($creds["client_id"] ?? "");
    $client_secret = (string) ($creds["client_secret"] ?? "");
    if ($client_id === "" || $client_secret === "") {
        ups_audit_oauth_exit_redirect(ups_audit_oauth_crm_error_url("Brak Client ID / Secret w Analityce SEO."));
    }

    if (!ups_audit_oauth_claim_auth_code($code)) {
        delete_transient(ups_audit_oauth_callback_payload_key($state));
        ups_audit_oauth_exit_redirect(
            ups_audit_oauth_crm_error_url(
                ups_audit_oauth_format_google_error("invalid_grant", "")
            )
        );
    }

    delete_transient(ups_audit_oauth_callback_payload_key($state));

    $exchange = ups_audit_oauth_exchange_code_for_audit($code, $client_id, $client_secret);
    if (is_wp_error($exchange)) {
        ups_audit_oauth_exit_redirect(ups_audit_oauth_crm_error_url($exchange->get_error_message()));
    }

    $label = sanitize_text_field((string) ($pending["label"] ?? ""));
    $reconnect_target = (int) ($pending["reconnect_account_id"] ?? 0);
    if ($label === "" && $reconnect_target > 0) {
        $label = (string) get_post_meta($reconnect_target, "_ups_gacc_label", true);
    }
    if ($reconnect_target > 0) {
        $target_email = (string) get_post_meta($reconnect_target, "_ups_gacc_email", true);
        $probe_access = upsellio_gsc_get_access_token([
            "client_id" => $client_id,
            "client_secret" => $client_secret,
            "refresh_token" => (string) $exchange["refresh_token"],
        ]);
        if ($target_email !== "" && is_string($probe_access) && $probe_access !== "") {
            $new_email = ups_audit_fetch_email_from_token(
                (string) $exchange["refresh_token"],
                $client_id,
                $client_secret
            );
            if ($new_email !== "" && strcasecmp($target_email, $new_email) !== 0) {
                ups_audit_oauth_exit_redirect(
                    ups_audit_oauth_crm_error_url(
                        "Wybrałeś inny Gmail niż na tej karcie ({$target_email}). Zaloguj się tym samym adresem."
                    )
                );
            }
        }
    }

    $account_id = function_exists("ups_audit_upsert_google_account_from_tokens")
        ? ups_audit_upsert_google_account_from_tokens(
            (string) $exchange["refresh_token"],
            $client_id,
            $client_secret,
            $label,
            $uid,
            $reconnect_target
        )
        : 0;

    if ($account_id <= 0) {
        ups_audit_oauth_exit_redirect(ups_audit_oauth_crm_error_url("Nie udało się zapisać konta Google."));
    }

    delete_transient(upsellio_google_oauth_transient_key($uid));
    delete_transient(ups_audit_oauth_pending_state_key($state));

    set_transient("ups_audit_last_connected_" . $uid, (int) $account_id, 10 * MINUTE_IN_SECONDS);

    if (function_exists("wp_schedule_single_event")) {
        wp_schedule_single_event(time() + 5, "ups_audit_oauth_prime_resources_event", [(int) $account_id]);
    }

    ups_audit_oauth_exit_redirect(ups_audit_oauth_crm_success_url($account_id));
}

function ups_audit_oauth_prime_resources_event(int $account_id): void
{
    if ($account_id <= 0 || !function_exists("ups_audit_oauth_prime_account_resources")) {
        return;
    }
    if (function_exists("set_time_limit")) {
        @set_time_limit(120);
    }
    try {
        ups_audit_oauth_prime_account_resources($account_id);
    } catch (Throwable $e) {
        if (function_exists("error_log")) {
            error_log("ups_audit oauth prime: " . $e->getMessage());
        }
    }
}

add_action("ups_audit_oauth_prime_resources_event", "ups_audit_oauth_prime_resources_event");

function ups_audit_oauth_handle_start(): void
{
    if (!is_user_logged_in()) {
        ups_audit_oauth_exit_redirect(wp_login_url(ups_audit_oauth_connect_url()));
    }

    if (!current_user_can("edit_posts")) {
        ups_audit_oauth_exit_redirect(ups_audit_oauth_crm_error_url("Brak uprawnień."));
    }

    $include_ads = !isset($_GET["include_ads"]) || (string) wp_unslash($_GET["include_ads"]) !== "0";
    $label = isset($_GET["label"]) ? sanitize_text_field((string) wp_unslash($_GET["label"])) : "";
    $reconnect_id = isset($_GET["reconnect_id"]) ? (int) wp_unslash($_GET["reconnect_id"]) : 0;
    $url = function_exists("ups_audit_start_oauth_connect")
        ? ups_audit_start_oauth_connect($label, $include_ads, $reconnect_id)
        : ups_audit_oauth_start_direct($label, $include_ads, $reconnect_id);

    if ($url !== "" && strpos($url, "accounts.google.com") !== false) {
        wp_redirect($url);
        exit;
    }

    ups_audit_oauth_exit_redirect(
        is_string($url) && strpos($url, "http") === 0
            ? $url
            : ups_audit_oauth_crm_error_url("Nie udało się uruchomić logowania Google.")
    );
}

/**
 * Jeden handler admin-post: start (op=start) lub callback (code/state).
 */
function ups_audit_google_oauth_handle(): void
{
    $op = isset($_GET["op"]) ? sanitize_key((string) wp_unslash($_GET["op"])) : "";

    if ($op === "start") {
        ups_audit_oauth_handle_start();
    }

    if (
        isset($_GET["oauth_resume"])
        || ups_audit_oauth_request_code() !== ""
        || isset($_GET["error"])
    ) {
        $state_for_resume = ups_audit_oauth_request_state();
        if ($state_for_resume === "" && isset($_GET["oauth_resume"])) {
            $state_for_resume = sanitize_text_field((string) wp_unslash($_GET["oauth_resume"]));
        }
        if (ups_audit_oauth_request_code() !== "" && $state_for_resume !== "") {
            ups_audit_oauth_store_callback_payload();
        }
        ups_audit_oauth_exit_redirect(ups_audit_oauth_crm_resume_url($state_for_resume));
    }

    ups_audit_oauth_exit_redirect(ups_audit_oauth_crm_error_url("Nieprawidłowe żądanie OAuth."));
}

add_action("admin_post_" . UPS_AUDIT_OAUTH_ACTION, "ups_audit_google_oauth_handle");
add_action("admin_post_nopriv_" . UPS_AUDIT_OAUTH_ACTION, "ups_audit_google_oauth_handle");

/** Stare akcje → nowa. */
function ups_audit_oauth_legacy_admin_post_router(): void
{
    ups_audit_google_oauth_handle();
}

add_action("admin_post_ups_audit_connect_google", "ups_audit_oauth_legacy_admin_post_router");
add_action("admin_post_nopriv_ups_audit_connect_google", "ups_audit_oauth_legacy_admin_post_router");
add_action("admin_post_ups_audit_oauth_crm_callback", "ups_audit_oauth_legacy_admin_post_router");
add_action("admin_post_nopriv_ups_audit_oauth_crm_callback", "ups_audit_oauth_legacy_admin_post_router");

/**
 * Powrót Google na /crm-app/?code=… — przetwórz tutaj (bez admin-post = mniej RAM).
 */
function ups_audit_oauth_legacy_crm_app_bounce(): void
{
    if (is_admin() || wp_doing_ajax()) {
        return;
    }

    if (!ups_audit_oauth_is_crm_return_request()) {
        return;
    }

    if (isset($_GET["oauth_resume"]) && ups_audit_oauth_request_code() === "") {
        ups_audit_oauth_restore_callback_payload();
    }

    $code = ups_audit_oauth_request_code();
    $state = ups_audit_oauth_request_state();
    if ($code === "" && $state === "" && !isset($_GET["error"]) && !isset($_GET["oauth_resume"])) {
        return;
    }

    if (isset($_GET["ups_audit_connect"]) && $code === "" && !isset($_GET["error"])) {
        $include_ads = !isset($_GET["include_ads"]) || (string) wp_unslash($_GET["include_ads"]) !== "0";
        $label = isset($_GET["label"]) ? sanitize_text_field((string) wp_unslash($_GET["label"])) : "";
        ups_audit_oauth_exit_redirect(ups_audit_oauth_connect_url($include_ads, $label));
    }

    ups_audit_oauth_store_callback_payload();

    if (!is_user_logged_in()) {
        $resume = $state !== "" ? $state : "";
        if ($resume === "" && isset($_GET["oauth_resume"])) {
            $resume = sanitize_text_field((string) wp_unslash($_GET["oauth_resume"]));
        }
        ups_audit_oauth_exit_redirect(
            wp_login_url(ups_audit_oauth_crm_resume_url($resume))
        );
    }

    if (current_user_can("edit_posts")) {
        ups_audit_oauth_process_callback();
    }

    ups_audit_oauth_exit_redirect(ups_audit_oauth_crm_error_url("Brak uprawnień do połączenia Google."));
}

add_action("init", "ups_audit_oauth_legacy_crm_app_bounce", -999);

/** CRM: ups_audit_connect=1 → admin-post start (bez template_redirect). */
function ups_audit_oauth_handle_direct_connect_link(): void
{
    if (is_admin()) {
        return;
    }

    $view = isset($_GET["view"]) ? sanitize_key((string) wp_unslash($_GET["view"])) : "";
    if ($view !== "ca-accounts" || !isset($_GET["ups_audit_connect"])) {
        return;
    }

    if (ups_audit_oauth_request_code() !== "" || isset($_GET["error"])) {
        return;
    }

    $include_ads = !isset($_GET["include_ads"]) || (string) wp_unslash($_GET["include_ads"]) !== "0";
    $label = isset($_GET["label"]) ? sanitize_text_field((string) wp_unslash($_GET["label"])) : "";
    ups_audit_oauth_exit_redirect(ups_audit_oauth_connect_url($include_ads, $label));
}

add_action("template_redirect", "ups_audit_oauth_handle_direct_connect_link", 0);

function ups_audit_oauth_filter_rest_callback_target(string $target, array $args, int $uid): string
{
    $pending_raw = get_transient(upsellio_google_oauth_transient_key($uid));
    if (!is_array($pending_raw) || sanitize_key((string) ($pending_raw["conn_type"] ?? "")) !== "audit") {
        return $target;
    }

    return ups_audit_oauth_action_url(
        array_filter([
            "code" => isset($args["code"]) ? (string) $args["code"] : "",
            "state" => isset($args["state"]) ? (string) $args["state"] : "",
            "error" => isset($args["error"]) ? (string) $args["error"] : "",
        ])
    );
}

add_filter("upsellio_google_oauth_rest_callback_redirect", "ups_audit_oauth_filter_rest_callback_target", 10, 3);
