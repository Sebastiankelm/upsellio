<?php

if (!defined("ABSPATH")) {
    exit;
}

function ups_audit_register_post_types()
{
    register_post_type("crm_google_account", [
        "labels" => ["name" => "Konta Google", "singular_name" => "Konto Google"],
        "public" => false,
        "show_ui" => false,
        "supports" => ["title", "custom-fields"],
    ]);

    register_post_type("crm_audit_resource", [
        "labels" => ["name" => "Zasoby audytu", "singular_name" => "Zasób audytu"],
        "public" => false,
        "show_ui" => false,
        "supports" => ["title", "custom-fields"],
    ]);

    register_post_type("crm_audit_report", [
        "labels" => ["name" => "Raporty audytu", "singular_name" => "Raport audytu"],
        "public" => false,
        "show_ui" => false,
        "supports" => ["title", "editor", "custom-fields"],
    ]);
}
add_action("init", "ups_audit_register_post_types");

function ups_audit_encrypt($plaintext)
{
    $plaintext = (string) $plaintext;
    if ($plaintext === "") {
        return "";
    }
    if (!defined("AUTH_KEY")) {
        return $plaintext;
    }
    $key = hash("sha256", (string) AUTH_KEY, true);
    $iv = random_bytes(16);
    $ct = openssl_encrypt($plaintext, "aes-256-cbc", $key, OPENSSL_RAW_DATA, $iv);
    if (!is_string($ct) || $ct === "") {
        return "";
    }
    return base64_encode($iv . $ct);
}

function ups_audit_decrypt($encoded)
{
    $encoded = (string) $encoded;
    if ($encoded === "") {
        return "";
    }
    if (!defined("AUTH_KEY")) {
        return $encoded;
    }
    $data = base64_decode($encoded, true);
    if (!is_string($data) || strlen($data) < 17) {
        return "";
    }
    $key = hash("sha256", (string) AUTH_KEY, true);
    $iv = substr($data, 0, 16);
    $ct = substr($data, 16);
    $out = openssl_decrypt($ct, "aes-256-cbc", $key, OPENSSL_RAW_DATA, $iv);
    return is_string($out) ? $out : "";
}

/**
 * Liczba aktywnych połączeń Google (osobne konta OAuth).
 */
function ups_audit_count_google_accounts(): int
{
    $ids = get_posts([
        "post_type" => "crm_google_account",
        "posts_per_page" => -1,
        "post_status" => ["publish", "draft"],
        "fields" => "ids",
    ]);

    return is_array($ids) ? count($ids) : 0;
}

/**
 * @param bool $same_account_reconnect true = ten sam Gmail (np. dopięcie Ads), bez wymuszania „innego konta”.
 */
function ups_audit_google_oauth_prompt_param(bool $same_account_reconnect = false): string
{
    if ($same_account_reconnect) {
        return "consent";
    }

    return ups_audit_count_google_accounts() > 0 ? "consent select_account" : "consent";
}

function ups_audit_fetch_google_sub_from_access(string $access_token): string
{
    $access_token = trim($access_token);
    if ($access_token === "") {
        return "";
    }
    $r = wp_remote_get("https://oauth2.googleapis.com/tokeninfo?access_token=" . rawurlencode($access_token), [
        "timeout" => 20,
        "sslverify" => true,
    ]);
    if (is_wp_error($r)) {
        return "";
    }
    $json = json_decode((string) wp_remote_retrieve_body($r), true);

    return is_array($json) ? sanitize_text_field((string) ($json["sub"] ?? "")) : "";
}

function ups_audit_find_google_account_id_by_identity(string $google_sub = "", string $email = ""): int
{
    $google_sub = trim($google_sub);
    $email = sanitize_email($email);

    if ($google_sub !== "") {
        $by_sub = get_posts([
            "post_type" => "crm_google_account",
            "posts_per_page" => 1,
            "post_status" => ["publish", "draft"],
            "fields" => "ids",
            "meta_query" => [[
                "key" => "_ups_gacc_google_sub",
                "value" => $google_sub,
                "compare" => "=",
            ]],
        ]);
        if (!empty($by_sub)) {
            return (int) $by_sub[0];
        }
    }

    if ($email !== "" && is_email($email)) {
        $by_email = get_posts([
            "post_type" => "crm_google_account",
            "posts_per_page" => 1,
            "post_status" => ["publish", "draft"],
            "fields" => "ids",
            "meta_query" => [[
                "key" => "_ups_gacc_email",
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

/**
 * Tworzy lub odświeża konto Google (wiele kont równolegle — osobny refresh token na wpis).
 */
function ups_audit_upsert_google_account_from_tokens($refresh, $client_id, $client_secret, $label = "", $uid = 0, int $force_account_id = 0): int
{
    $refresh = trim((string) $refresh);
    $client_id = trim((string) $client_id);
    $client_secret = trim((string) $client_secret);
    if ($refresh === "" || $client_id === "" || $client_secret === "") {
        return 0;
    }

    $email = ups_audit_fetch_email_from_token($refresh, $client_id, $client_secret);
    $access = upsellio_gsc_get_access_token([
        "client_id" => $client_id,
        "client_secret" => $client_secret,
        "refresh_token" => $refresh,
    ]);
    $google_sub = is_string($access) && $access !== "" ? ups_audit_fetch_google_sub_from_access($access) : "";

    $account_id = ups_audit_find_google_account_id_by_identity($google_sub, $email);
    $force_account_id = (int) $force_account_id;
    if ($force_account_id > 0) {
        $account_id = $force_account_id;
    }
    $title = $email !== "" ? $email : "Konto " . substr(md5($refresh), 0, 8);

    if ($account_id <= 0) {
        $account_id = wp_insert_post([
            "post_type" => "crm_google_account",
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

    update_post_meta($account_id, "_ups_gacc_email", $email);
    if ($google_sub !== "") {
        update_post_meta($account_id, "_ups_gacc_google_sub", $google_sub);
    }
    $label = sanitize_text_field((string) $label);
    if ($label !== "") {
        update_post_meta($account_id, "_ups_gacc_label", $label);
    }
    update_post_meta($account_id, "_ups_gacc_oauth_client_id", $client_id);
    update_post_meta($account_id, "_ups_gacc_oauth_client_secret", ups_audit_encrypt($client_secret));
    update_post_meta($account_id, "_ups_gacc_oauth_refresh_token", ups_audit_encrypt($refresh));
    update_post_meta($account_id, "_ups_gacc_token_expires_at", gmdate("Y-m-d H:i:s", time() + (180 * DAY_IN_SECONDS)));
    update_post_meta($account_id, "_ups_gacc_last_sync_at", current_time("mysql"));
    if (is_string($access) && $access !== "") {
        $scopes = ups_audit_fetch_scopes($access);
        update_post_meta($account_id, "_ups_gacc_scopes", is_array($scopes) ? $scopes : []);
    }
    if ($uid > 0) {
        update_post_meta($account_id, "_ups_gacc_connected_by", (int) $uid);
    }

    return (int) $account_id;
}

function ups_audit_oauth_create_account_from_tokens($refresh, $client_id, $client_secret, $label = "", $uid = 0): int
{
    return ups_audit_upsert_google_account_from_tokens($refresh, $client_id, $client_secret, $label, $uid);
}

if (!function_exists("ups_audit_oauth_start_direct")) {
function ups_audit_oauth_start_direct(string $label = "", bool $include_ads = true, int $reconnect_account_id = 0): string
{
    $uid = get_current_user_id();
    $state = bin2hex(random_bytes(16));
    $reconnect_account_id = (int) $reconnect_account_id;
    if ($include_ads) {
        update_option("upsellio_google_ads_include_scope", "1", false);
    }
    $pending_payload = [
        "state" => $state,
        "gsc_property" => "",
        "ga4_property_id" => "",
        "managed_oauth" => false,
        "conn_type" => "audit",
        "label" => sanitize_text_field($label),
        "include_ads" => $include_ads ? "1" : "0",
        "wp_user_id" => (int) $uid,
        "reconnect_account_id" => $reconnect_account_id > 0 ? $reconnect_account_id : 0,
    ];
    set_transient(upsellio_google_oauth_transient_key($uid), $pending_payload, 30 * MINUTE_IN_SECONDS);
    if (function_exists("ups_audit_oauth_mirror_pending_by_state")) {
        ups_audit_oauth_mirror_pending_by_state($state, $pending_payload, 30 * MINUTE_IN_SECONDS);
    }
    $existing = upsellio_get_gsc_credentials();
    $client_id = (string) ($existing["client_id"] ?? "");
    $client_secret = (string) ($existing["client_secret"] ?? "");
    if ($client_id === "" || $client_secret === "") {
        return function_exists("ups_audit_oauth_crm_error_url")
            ? ups_audit_oauth_crm_error_url("Uzupełnij Client ID i Secret w Analityce SEO (OAuth Upsellio).")
            : add_query_arg(
                ["view" => "ca-accounts", "ups_audit_error" => rawurlencode("Uzupełnij Client ID i Secret w Analityce SEO (OAuth Upsellio).")],
                home_url("/crm-app/")
            );
    }
    $scopes = function_exists("upsellio_google_oauth_scope_string")
        ? upsellio_google_oauth_scope_string()
        : "https://www.googleapis.com/auth/webmasters.readonly https://www.googleapis.com/auth/analytics.readonly";

    $redirect_uri = function_exists("ups_audit_oauth_redirect_uri")
        ? ups_audit_oauth_redirect_uri()
        : upsellio_google_oauth_redirect_uri();

    return add_query_arg(
        [
            "client_id" => $client_id,
            "redirect_uri" => $redirect_uri,
            "response_type" => "code",
            "scope" => $scopes,
            "access_type" => "offline",
            "prompt" => function_exists("ups_audit_google_oauth_prompt_param")
                ? ups_audit_google_oauth_prompt_param($reconnect_account_id > 0)
                : "consent",
            "include_granted_scopes" => "true",
            "state" => $state,
        ],
        "https://accounts.google.com/o/oauth2/v2/auth"
    );
}
}

function ups_audit_start_oauth_connect($label = "", bool $include_ads = true, int $reconnect_account_id = 0)
{
    if (function_exists("ups_audit_oauth_ensure_managed_connect")) {
        ups_audit_oauth_ensure_managed_connect();
    }

    $info = function_exists("ups_audit_oauth_connection_info") ? ups_audit_oauth_connection_info() : ["ready" => true];
    $has_creds = !empty($info["has_app_credentials"]) || !empty($info["ready"]);
    if (!$has_creds) {
        return function_exists("ups_audit_oauth_crm_error_url")
            ? ups_audit_oauth_crm_error_url((string) ($info["message"] ?? "OAuth niedostępny."))
            : home_url("/crm-app/?view=ca-accounts");
    }

    $state = bin2hex(random_bytes(16));
    $uid = get_current_user_id();
    if ($include_ads) {
        update_option("upsellio_google_ads_include_scope", "1", false);
    } else {
        update_option("upsellio_google_ads_include_scope", "0", false);
    }
    $managed = function_exists("ups_audit_oauth_managed_is_available") && ups_audit_oauth_managed_is_available();
    $reconnect_account_id = (int) $reconnect_account_id;
    $pending_payload = [
        "state" => $state,
        "gsc_property" => "",
        "ga4_property_id" => "",
        "managed_oauth" => $managed,
        "conn_type" => "audit",
        "label" => sanitize_text_field((string) $label),
        "include_ads" => $include_ads ? "1" : "0",
        "wp_user_id" => (int) $uid,
        "reconnect_account_id" => $reconnect_account_id > 0 ? $reconnect_account_id : 0,
    ];
    set_transient(upsellio_google_oauth_transient_key($uid), $pending_payload, 30 * MINUTE_IN_SECONDS);
    if (function_exists("ups_audit_oauth_mirror_pending_by_state")) {
        ups_audit_oauth_mirror_pending_by_state($state, $pending_payload, 30 * MINUTE_IN_SECONDS);
    }
    $return_ok = function_exists("ups_audit_oauth_crm_success_url")
        ? ups_audit_oauth_crm_success_url(0)
        : home_url("/crm-app/?view=ca-accounts&connected=1");
    // Bezpośredni OAuth → redirect na CRM (często już w Google Cloud); most REST wymaga osobnego wpisu wp-json.
    if (function_exists("ups_audit_oauth_start_direct")) {
        return ups_audit_oauth_start_direct((string) $label, $include_ads, $reconnect_account_id);
    }

    if ($managed && function_exists("upsellio_google_managed_oauth_build_start_url")) {
        return upsellio_google_managed_oauth_build_start_url($state, $return_ok, $uid);
    }

    return function_exists("ups_audit_oauth_crm_error_url")
        ? ups_audit_oauth_crm_error_url("Logowanie Google (Connect) nie jest skonfigurowane. Uzupełnij Client ID i Secret w Analityce SEO.")
        : home_url("/crm-app/?view=ca-accounts");
}

function ups_audit_get_oauth_for_account($google_account_id)
{
    $google_account_id = (int) $google_account_id;
    if ($google_account_id <= 0) {
        return [];
    }
    return [
        "client_id" => (string) get_post_meta($google_account_id, "_ups_gacc_oauth_client_id", true),
        "client_secret" => ups_audit_decrypt((string) get_post_meta($google_account_id, "_ups_gacc_oauth_client_secret", true)),
        "refresh_token" => ups_audit_decrypt((string) get_post_meta($google_account_id, "_ups_gacc_oauth_refresh_token", true)),
    ];
}

function ups_audit_fetch_scopes($access_token)
{
    $access_token = trim((string) $access_token);
    if ($access_token === "") {
        return [];
    }
    $r = wp_remote_get("https://oauth2.googleapis.com/tokeninfo?access_token=" . rawurlencode($access_token), [
        "timeout" => 20,
        "sslverify" => true,
    ]);
    if (is_wp_error($r)) {
        return [];
    }
    $json = json_decode((string) wp_remote_retrieve_body($r), true);
    if (!is_array($json)) {
        return [];
    }
    $scope_raw = trim((string) ($json["scope"] ?? ""));
    if ($scope_raw === "") {
        return [];
    }
    return array_values(array_filter(array_map("trim", explode(" ", $scope_raw))));
}

function ups_audit_fetch_email_from_token($refresh, $client_id, $client_secret)
{
    $oauth = [
        "client_id" => (string) $client_id,
        "client_secret" => (string) $client_secret,
        "refresh_token" => (string) $refresh,
    ];
    $access = upsellio_gsc_get_access_token($oauth);
    if (!is_string($access) || $access === "") {
        return "";
    }
    $r = wp_remote_get("https://www.googleapis.com/oauth2/v2/userinfo", [
        "timeout" => 20,
        "sslverify" => true,
        "headers" => ["Authorization" => "Bearer " . $access],
    ]);
    if (is_wp_error($r)) {
        return "";
    }
    $json = json_decode((string) wp_remote_retrieve_body($r), true);
    $email = is_array($json) ? sanitize_email((string) ($json["email"] ?? "")) : "";
    return is_email($email) ? $email : "";
}

function ups_audit_gacc_fetch_error_meta_key(string $type): string
{
    return "_ups_gacc_" . sanitize_key($type) . "_fetch_error";
}

function ups_audit_set_gacc_fetch_error(int $google_account_id, string $type, string $message): void
{
    $google_account_id = (int) $google_account_id;
    $message = trim($message);
    if ($google_account_id <= 0 || $message === "") {
        return;
    }
    update_post_meta($google_account_id, ups_audit_gacc_fetch_error_meta_key($type), $message);
}

function ups_audit_clear_gacc_fetch_error(int $google_account_id, string $type): void
{
    $google_account_id = (int) $google_account_id;
    if ($google_account_id <= 0) {
        return;
    }
    delete_post_meta($google_account_id, ups_audit_gacc_fetch_error_meta_key($type));
}

function ups_audit_account_has_oauth_scope(int $google_account_id, string $needle): bool
{
    $scopes = get_post_meta((int) $google_account_id, "_ups_gacc_scopes", true);
    if (!is_array($scopes)) {
        return false;
    }
    $needle = strtolower(trim($needle));
    foreach ($scopes as $scope) {
        if (stripos((string) $scope, $needle) !== false) {
            return true;
        }
    }

    return false;
}

/**
 * Podpowiedź w UI, gdy lista GA4/Ads jest pusta.
 */
function ups_audit_get_gacc_fetch_error_hint(int $google_account_id, string $type): string
{
    $google_account_id = (int) $google_account_id;
    $type = sanitize_key($type);
    $stored = trim((string) get_post_meta($google_account_id, ups_audit_gacc_fetch_error_meta_key($type), true));
    if ($stored !== "") {
        return $stored;
    }

    if ($type === "ga4") {
        return "GA4: włącz w Google Cloud API „Google Analytics Admin API” dla projektu OAuth (936412824129), "
            . "potem kliknij „Odśwież zasoby”. Link: "
            . "https://console.developers.google.com/apis/api/analyticsadmin.googleapis.com/overview?project=936412824129";
    }

    if ($type === "ads") {
        $gads = function_exists("upsellio_google_ads_get_settings") ? upsellio_google_ads_get_settings() : [];
        $has_token = trim((string) ($gads["developer_token"] ?? "")) !== "";
        $has_scope = ups_audit_account_has_oauth_scope($google_account_id, "adwords");
        if (!$has_token) {
            return "Ads: Developer Token jest tylko na koncie menedżera (MCC), nie na zwykłym koncie reklamowym. "
                . "Załóż MCC, podłącz konta, token z Centrum API → WP Analityka SEO. Potem połącz ponownie z „Uwzględnij Google Ads”.";
        }
        if (!$has_scope) {
            return "Ads: brak zakresu adwords — użyj przycisku „Dodaj Google Ads” na tej karcie (ten sam Gmail, bez nowego konta).";
        }

        return "Ads: brak kont w API — sprawdź uprawnienia MCC i Developer Token.";
    }

    return __("Brak danych — kliknij „Odśwież zasoby”.", "upsellio");
}

function ups_audit_fetch_ga4_resources($google_account_id)
{
    $google_account_id = (int) $google_account_id;
    $oauth = ups_audit_get_oauth_for_account($google_account_id);
    $access = upsellio_gsc_get_access_token($oauth);
    if (!is_string($access) || $access === "") {
        ups_audit_set_gacc_fetch_error($google_account_id, "ga4", "GA4: brak ważnego tokena — odłącz konto i zaloguj ponownie.");
        return [];
    }
    $accs_resp = wp_remote_get("https://analyticsadmin.googleapis.com/v1beta/accounts", [
        "timeout" => 30,
        "sslverify" => true,
        "headers" => ["Authorization" => "Bearer " . $access],
    ]);
    if (is_wp_error($accs_resp)) {
        ups_audit_set_gacc_fetch_error($google_account_id, "ga4", "GA4: " . $accs_resp->get_error_message());
        return [];
    }
    $code = (int) wp_remote_retrieve_response_code($accs_resp);
    $accs = json_decode((string) wp_remote_retrieve_body($accs_resp), true);
    if ($code >= 400 || !is_array($accs)) {
        $api_msg = is_array($accs) && isset($accs["error"]["message"])
            ? (string) $accs["error"]["message"]
            : "HTTP {$code}";
        if (stripos($api_msg, "analyticsadmin") !== false || stripos($api_msg, "SERVICE_DISABLED") !== false) {
            $api_msg = "Włącz Google Analytics Admin API w Google Cloud (projekt 936412824129), potem „Odśwież zasoby”.";
        }
        ups_audit_set_gacc_fetch_error($google_account_id, "ga4", "GA4: " . $api_msg);
        return [];
    }
    ups_audit_clear_gacc_fetch_error($google_account_id, "ga4");
    $tree = [];
    foreach ((array) ($accs["accounts"] ?? []) as $acc) {
        if (!is_array($acc)) {
            continue;
        }
        $acc_name = sanitize_text_field((string) ($acc["displayName"] ?? ""));
        $acc_id = str_replace("accounts/", "", (string) ($acc["name"] ?? ""));
        if ($acc_id === "") {
            continue;
        }
        $props_resp = wp_remote_get("https://analyticsadmin.googleapis.com/v1beta/properties?filter=parent:accounts/" . rawurlencode($acc_id), [
            "timeout" => 30,
            "sslverify" => true,
            "headers" => ["Authorization" => "Bearer " . $access],
        ]);
        $props = is_wp_error($props_resp) ? [] : json_decode((string) wp_remote_retrieve_body($props_resp), true);
        $children = [];
        foreach ((array) ($props["properties"] ?? []) as $p) {
            if (!is_array($p)) {
                continue;
            }
            $children[] = [
                "id" => str_replace("properties/", "", (string) ($p["name"] ?? "")),
                "display_name" => sanitize_text_field((string) ($p["displayName"] ?? "")),
                "parent_account_id" => $acc_id,
            ];
        }
        $tree[] = [
            "account_id" => $acc_id,
            "account_name" => $acc_name,
            "properties" => $children,
        ];
    }
    if ($tree === []) {
        ups_audit_set_gacc_fetch_error(
            $google_account_id,
            "ga4",
            "GA4: konto Google nie ma żadnych usług Analytics (lub brak uprawnień do ich listy)."
        );
    }

    return $tree;
}

function ups_audit_fetch_gsc_resources($google_account_id)
{
    $oauth = ups_audit_get_oauth_for_account((int) $google_account_id);
    $access = upsellio_gsc_get_access_token($oauth);
    $google_account_id = (int) $google_account_id;
    if (!is_string($access) || $access === "") {
        return [];
    }
    $resp = wp_remote_get("https://www.googleapis.com/webmasters/v3/sites", [
        "timeout" => 30,
        "sslverify" => true,
        "headers" => ["Authorization" => "Bearer " . $access],
    ]);
    if (is_wp_error($resp)) {
        return [];
    }
    $body = json_decode((string) wp_remote_retrieve_body($resp), true);
    $sites = [];
    foreach ((array) ($body["siteEntry"] ?? []) as $s) {
        if (!is_array($s)) {
            continue;
        }
        $perm = sanitize_text_field((string) ($s["permissionLevel"] ?? ""));
        $sites[] = [
            "site_url" => (string) ($s["siteUrl"] ?? ""),
            "permission_level" => $perm,
            "is_verified" => in_array($perm, ["siteOwner", "siteFullUser"], true),
        ];
    }
    if ($sites !== []) {
        ups_audit_clear_gacc_fetch_error($google_account_id, "gsc");
    }

    return $sites;
}

function ups_audit_fetch_ads_customer_name($oauth_credentials, $customer_id)
{
    $oauth_credentials = is_array($oauth_credentials) ? $oauth_credentials : [];
    $access = upsellio_gsc_get_access_token($oauth_credentials);
    if (!is_string($access) || $access === "") {
        return "";
    }
    $gads_cfg = function_exists("upsellio_google_ads_get_settings") ? upsellio_google_ads_get_settings() : [];
    $developer_token = (string) ($gads_cfg["developer_token"] ?? "");
    $manager_id = (string) ($gads_cfg["login_customer_id"] ?? "");
    if ($developer_token === "") {
        return "";
    }
    $url = "https://googleads.googleapis.com/v18/customers/" . rawurlencode((string) $customer_id) . "/googleAds:search";
    $query = "SELECT customer.descriptive_name FROM customer LIMIT 1";
    $headers = [
        "Authorization" => "Bearer " . $access,
        "developer-token" => $developer_token,
        "Content-Type" => "application/json",
    ];
    if ($manager_id !== "") {
        $headers["login-customer-id"] = $manager_id;
    }
    $r = wp_remote_post($url, [
        "timeout" => 35,
        "sslverify" => true,
        "headers" => $headers,
        "body" => wp_json_encode(["query" => $query]),
    ]);
    if (is_wp_error($r)) {
        return "";
    }
    $json = json_decode((string) wp_remote_retrieve_body($r), true);
    return sanitize_text_field((string) ($json["results"][0]["customer"]["descriptiveName"] ?? ""));
}

function ups_audit_fetch_ads_resources($google_account_id)
{
    $google_account_id = (int) $google_account_id;
    $gads_cfg = function_exists("upsellio_google_ads_get_settings") ? upsellio_google_ads_get_settings() : [];
    if (trim((string) ($gads_cfg["developer_token"] ?? "")) === "") {
        ups_audit_set_gacc_fetch_error(
            $google_account_id,
            "ads",
            "Ads: brak Developer Token w Analityce SEO (WP). Uzupełnij i połącz konto ponownie z „Uwzględnij Google Ads”."
        );
        return [];
    }
    if (!ups_audit_account_has_oauth_scope($google_account_id, "adwords")) {
        ups_audit_set_gacc_fetch_error(
            $google_account_id,
            "ads",
            "Ads: brak zakresu OAuth adwords — odłącz konto i zaloguj ponownie z zaznaczonym Google Ads."
        );
        return [];
    }

    $oauth = ups_audit_get_oauth_for_account($google_account_id);
    $backup = upsellio_get_gsc_credentials();
    upsellio_save_gsc_credentials(
        (string) ($oauth["client_id"] ?? ""),
        (string) ($oauth["client_secret"] ?? ""),
        (string) ($oauth["refresh_token"] ?? ""),
        (string) ($backup["property"] ?? "")
    );
    $customers = upsellio_google_ads_list_accessible_customers();
    upsellio_save_gsc_credentials(
        (string) ($backup["client_id"] ?? ""),
        (string) ($backup["client_secret"] ?? ""),
        (string) ($backup["refresh_token"] ?? ""),
        (string) ($backup["property"] ?? "")
    );
    if (!is_array($customers)) {
        $err = is_wp_error($customers) ? $customers->get_error_message() : "Brak odpowiedzi Google Ads API.";
        ups_audit_set_gacc_fetch_error($google_account_id, "ads", "Ads: " . $err);
        return [];
    }
    $list = [];
    foreach ((array) ($customers["resourceNames"] ?? []) as $rn) {
        $cid = str_replace("customers/", "", (string) $rn);
        if ($cid === "") {
            continue;
        }
        $list[] = [
            "customer_id" => $cid,
            "name" => ups_audit_fetch_ads_customer_name($oauth, $cid),
        ];
    }
    if ($list === []) {
        ups_audit_set_gacc_fetch_error(
            $google_account_id,
            "ads",
            "Ads: API nie zwróciło kont — uzupełnij Login Customer ID (ID MCC) w Analityce SEO i upewnij się, że konto Google ma dostęp do tych kont pod MCC."
        );
    } else {
        ups_audit_clear_gacc_fetch_error($google_account_id, "ads");
    }

    return $list;
}

/**
 * Czy zasób z cache jest już zaimportowany do crm_audit_resource.
 */
function ups_audit_find_imported_resource_id(int $google_account_id, string $type, string $external_id): int
{
    $google_account_id = (int) $google_account_id;
    $type = sanitize_key($type);
    $external_id = sanitize_text_field($external_id);
    if ($google_account_id <= 0 || $type === "" || $external_id === "") {
        return 0;
    }
    $existing = get_posts([
        "post_type" => "crm_audit_resource",
        "posts_per_page" => 1,
        "post_status" => ["publish", "draft"],
        "fields" => "ids",
        "meta_query" => [
            "relation" => "AND",
            ["key" => "_ups_resource_google_account_id", "value" => $google_account_id],
            ["key" => "_ups_resource_type", "value" => $type],
            ["key" => "_ups_resource_external_id", "value" => $external_id],
        ],
    ]);

    return !empty($existing) ? (int) $existing[0] : 0;
}

function ups_audit_get_google_account_resources(int $google_account_id, string $type = ""): array
{
    $google_account_id = (int) $google_account_id;
    $type = sanitize_key($type);
    if ($google_account_id <= 0) {
        return [];
    }
    $meta_query = [[
        "key" => "_ups_resource_google_account_id",
        "value" => $google_account_id,
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

/**
 * Główny klient CRM przypisany do zasobów konta (jeśli jeden dominujący).
 */
function ups_audit_google_account_primary_client_id(int $google_account_id): int
{
    $google_account_id = (int) $google_account_id;
    if ($google_account_id <= 0) {
        return 0;
    }
    $counts = [];
    foreach (ups_audit_get_google_account_resources($google_account_id) as $r) {
        if (!($r instanceof WP_Post)) {
            continue;
        }
        $cid = (int) get_post_meta((int) $r->ID, "_ups_resource_client_id", true);
        if ($cid > 0) {
            $counts[$cid] = (int) ($counts[$cid] ?? 0) + 1;
        }
    }
    if ($counts === []) {
        return 0;
    }
    arsort($counts);

    return (int) key($counts);
}

function ups_audit_google_account_setup_status(int $google_account_id): array
{
    $google_account_id = (int) $google_account_id;
    $ga4 = count(ups_audit_get_google_account_resources($google_account_id, "ga4"));
    $gsc = count(ups_audit_get_google_account_resources($google_account_id, "gsc"));
    $ads = count(ups_audit_get_google_account_resources($google_account_id, "ads"));
    $imported = $ga4 + $gsc + $ads;
    $complete = $ga4 > 0 && $gsc > 0;

    return [
        "ga4" => $ga4,
        "gsc" => $gsc,
        "ads" => $ads,
        "imported" => $imported,
        "is_ready" => $complete,
        "steps" => [
            ["key" => "ga4", "done" => $ga4 > 0, "label" => "GA4 (" . $ga4 . ")"],
            ["key" => "gsc", "done" => $gsc > 0, "label" => "GSC (" . $gsc . ")"],
            ["key" => "ads", "done" => $ads > 0, "label" => "Google Ads (" . $ads . ")"],
        ],
    ];
}

function ups_audit_get_google_account_last_sync(int $google_account_id): int
{
    $last = 0;
    foreach (ups_audit_get_google_account_resources((int) $google_account_id) as $r) {
        if (!($r instanceof WP_Post)) {
            continue;
        }
        $ts = strtotime((string) get_post_meta((int) $r->ID, "_ups_resource_last_data_sync", true));
        if ($ts > $last) {
            $last = $ts;
        }
    }

    return $last;
}

function ups_audit_get_client_resources($client_id, $type = "")
{
    $client_id = (int) $client_id;
    $type = sanitize_key((string) $type);
    if ($client_id <= 0) {
        return [];
    }
    $meta_query = [[
        "key" => "_ups_resource_client_id",
        "value" => $client_id,
        "compare" => "=",
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
    ]);
}

function ups_audit_count_active_clients()
{
    $q = new WP_Query([
        "post_type" => "crm_audit_resource",
        "fields" => "ids",
        "posts_per_page" => -1,
        "post_status" => ["publish", "draft"],
        "meta_query" => [[
            "key" => "_ups_resource_client_id",
            "value" => 0,
            "compare" => ">",
            "type" => "NUMERIC",
        ]],
    ]);
    if (!$q->have_posts()) {
        return 0;
    }
    $client_ids = [];
    foreach ($q->posts as $rid) {
        $cid = (int) get_post_meta((int) $rid, "_ups_resource_client_id", true);
        if ($cid > 0) {
            $client_ids[$cid] = true;
        }
    }
    return count($client_ids);
}

function ups_audit_get_client_last_sync($client_id)
{
    $resources = ups_audit_get_client_resources((int) $client_id);
    $last = 0;
    foreach ($resources as $r) {
        if (!($r instanceof WP_Post)) {
            continue;
        }
        $ts = strtotime((string) get_post_meta((int) $r->ID, "_ups_resource_last_data_sync", true));
        if ($ts > $last) {
            $last = $ts;
        }
    }
    return $last;
}

function ups_audit_format_sync_time($ts)
{
    $ts = (int) $ts;
    if ($ts <= 0) {
        return "brak";
    }
    $diff = time() - $ts;
    if ($diff < HOUR_IN_SECONDS * 24) {
        return "dzis";
    }
    if ($diff < HOUR_IN_SECONDS * 48) {
        return "1d";
    }
    return floor($diff / DAY_IN_SECONDS) . "d";
}

/**
 * Makro-konwersje (lead/zakup) — konfigurowalne per zasób/kient.
 *
 * @return array<int, string>
 */
function ups_audit_ga4_macro_event_names(int $resource_id = 0, int $client_id = 0): array
{
    $raw = "";
    if ($resource_id > 0) {
        $raw = (string) get_post_meta($resource_id, "_ups_resource_ga4_macro_events", true);
    }
    if ($raw === "" && $client_id > 0) {
        $raw = (string) get_post_meta($client_id, "_ups_client_ga4_macro_events", true);
    }
    if ($raw !== "") {
        $parts = array_filter(array_map("trim", preg_split("/[\s,;]+/", $raw) ?: []));
        if ($parts !== []) {
            return array_values(array_unique(array_map("strtolower", $parts)));
        }
    }

    return [
        "purchase",
        "generate_lead",
        "lead",
        "contact",
        "quote_request",
        "zapytanie",
    ];
}

/**
 * Zdarzenia zaangażowania (nie KPI konwersji).
 *
 * @return array<int, string>
 */
function ups_audit_ga4_engagement_event_patterns(): array
{
    return [
        "form_submit",
        "form_start",
        "submit_form",
        "formularz",
        "wyslij_formularz",
        "add_to_cart",
        "begin_checkout",
        "view_item",
    ];
}

/**
 * @return array<int, string>
 */
function ups_audit_ga4_micro_event_patterns(): array
{
    return [
        "page_view",
        "scroll",
        "session_start",
        "user_engagement",
        "click",
        "file_download",
        "begin_checkout",
        "first_visit",
        "phone_click",
        "mail_click",
        "tel",
        "mailto",
    ];
}

function ups_audit_ga4_classify_event(string $event_name, array $macro_events, array $micro_patterns, array $engagement_patterns = []): string
{
    $event_name = strtolower(trim($event_name));
    if ($event_name === "") {
        return "other";
    }
    if (in_array($event_name, $macro_events, true)) {
        return "macro";
    }
    foreach ($macro_events as $macro) {
        if ($macro !== "" && strpos($event_name, $macro) !== false) {
            return "macro";
        }
    }
    foreach ($engagement_patterns as $pattern) {
        if ($pattern !== "" && strpos($event_name, $pattern) !== false) {
            return "engagement";
        }
    }
    foreach ($micro_patterns as $pattern) {
        if ($pattern !== "" && strpos($event_name, $pattern) !== false) {
            return "micro";
        }
    }

    return "other";
}

function ups_audit_ga4_is_not_set_source(string $source, string $medium): bool
{
    $source = strtolower(trim($source));
    $medium = strtolower(trim($medium));

    if ($source === "(not set)" || $medium === "(not set)") {
        return true;
    }
    if ($source === "not set" || $medium === "not set") {
        return true;
    }
    if ($source === "(not set)" && ($medium === "" || $medium === "(not set)" || $medium === "not set")) {
        return true;
    }

    return $source === "(direct)" && ($medium === "(not set)" || $medium === "" || $medium === "(none)");
}

function ups_audit_ga4_run_report(string $property_id, string $access, array $body, int $sync_days = 30)
{
    $timeout = $sync_days > 90 ? 120 : 45;
    $r = wp_remote_post(
        "https://analyticsdata.googleapis.com/v1beta/properties/" . $property_id . ":runReport",
        [
            "timeout" => $timeout,
            "sslverify" => true,
            "headers" => ["Authorization" => "Bearer " . $access, "Content-Type" => "application/json"],
            "body" => wp_json_encode($body),
        ]
    );
    if (is_wp_error($r)) {
        return $r;
    }

    return json_decode((string) wp_remote_retrieve_body($r), true);
}

function ups_audit_ga4_fetch($property_id, $oauth_credentials, $sync_days = 30)
{
    $property_id = preg_replace("/\D+/", "", (string) $property_id);
    $access = upsellio_gsc_get_access_token((array) $oauth_credentials);
    if (!is_string($access) || $access === "") {
        return new WP_Error("ups_audit_no_access", "Brak tokena access.");
    }
    $sync_days = max(1, min(365, (int) $sync_days));
    $body = [
        "dateRanges" => [["startDate" => $sync_days . "daysAgo", "endDate" => "yesterday"]],
        "dimensions" => [["name" => "sessionSource"], ["name" => "sessionMedium"], ["name" => "date"]],
        "metrics" => [["name" => "sessions"], ["name" => "conversions"], ["name" => "totalRevenue"]],
        "limit" => 250000,
    ];

    return ups_audit_ga4_run_report($property_id, $access, $body, $sync_days);
}

/**
 * Rozkład eventów GA4 (mikro vs makro).
 *
 * @return array<string, mixed>|\WP_Error
 */
function ups_audit_ga4_fetch_events($property_id, $oauth_credentials, $sync_days = 30, int $resource_id = 0, int $client_id = 0)
{
    $property_id = preg_replace("/\D+/", "", (string) $property_id);
    $access = upsellio_gsc_get_access_token((array) $oauth_credentials);
    if (!is_string($access) || $access === "") {
        return new WP_Error("ups_audit_no_access", "Brak tokena access.");
    }
    $sync_days = max(1, min(365, (int) $sync_days));
    $body = [
        "dateRanges" => [["startDate" => $sync_days . "daysAgo", "endDate" => "yesterday"]],
        "dimensions" => [["name" => "eventName"]],
        "metrics" => [["name" => "eventCount"], ["name" => "eventValue"]],
        "limit" => 10000,
    ];
    $raw = ups_audit_ga4_run_report($property_id, $access, $body, $sync_days);
    if (is_wp_error($raw)) {
        return $raw;
    }
    if (!is_array($raw) || isset($raw["error"])) {
        return is_array($raw) ? $raw : new WP_Error("ga4_events", "Błąd GA4 events API.");
    }

    $macro_events = ups_audit_ga4_macro_event_names($resource_id, $client_id);
    $micro_patterns = ups_audit_ga4_micro_event_patterns();
    $engagement_patterns = ups_audit_ga4_engagement_event_patterns();
    $breakdown = [];
    $macro_total = 0;
    $micro_total = 0;
    $engagement_total = 0;
    $other_total = 0;

    foreach ((array) ($raw["rows"] ?? []) as $row) {
        if (!is_array($row)) {
            continue;
        }
        $dims = (array) ($row["dimensionValues"] ?? []);
        $mets = (array) ($row["metricValues"] ?? []);
        $event = sanitize_text_field((string) ($dims[0]["value"] ?? ""));
        $count = (int) round((float) ($mets[0]["value"] ?? 0));
        $revenue = (float) ($mets[1]["value"] ?? 0);
        if ($event === "" || $count <= 0) {
            continue;
        }
        $kind = ups_audit_ga4_classify_event($event, $macro_events, $micro_patterns, $engagement_patterns);
        if ($kind === "macro") {
            $macro_total += $count;
        } elseif ($kind === "micro") {
            $micro_total += $count;
        } elseif ($kind === "engagement") {
            $engagement_total += $count;
        } else {
            $other_total += $count;
        }
        $breakdown[] = [
            "event" => $event,
            "count" => $count,
            "revenue" => round($revenue, 2),
            "kind" => $kind,
        ];
    }

    usort($breakdown, static function ($a, $b) {
        return ((int) ($b["count"] ?? 0)) <=> ((int) ($a["count"] ?? 0));
    });

    return [
        "macro_total" => $macro_total,
        "micro_total" => $micro_total,
        "engagement_total" => $engagement_total,
        "other_total" => $other_total,
        "breakdown" => array_slice($breakdown, 0, 40),
        "macro_events_config" => $macro_events,
    ];
}

/**
 * Oficjalne metryki e-commerce GA4 (purchase only — nie totalRevenue z sesji).
 *
 * @return array<string, float|int|string>|\WP_Error
 */
function ups_audit_ga4_fetch_ecommerce_kpi($property_id, $oauth_credentials, int $period_days, string $start_date = "", string $end_date = "")
{
    $property_id = preg_replace("/\D+/", "", (string) $property_id);
    $access = upsellio_gsc_get_access_token((array) $oauth_credentials);
    if (!is_string($access) || $access === "") {
        return new WP_Error("ups_audit_no_access", "Brak tokena access.");
    }
    $period_days = max(1, min(365, (int) $period_days));
    if ($start_date !== "" && $end_date !== "" && preg_match("/^\d{4}-\d{2}-\d{2}$/", $start_date) && preg_match("/^\d{4}-\d{2}-\d{2}$/", $end_date)) {
        $date_range = [["startDate" => $start_date, "endDate" => $end_date]];
    } else {
        $date_range = [["startDate" => $period_days . "daysAgo", "endDate" => "yesterday"]];
    }
    $body = [
        "dateRanges" => $date_range,
        "metrics" => [
            ["name" => "purchaseRevenue"],
            ["name" => "ecommercePurchases"],
            ["name" => "totalRevenue"],
        ],
    ];
    $raw = ups_audit_ga4_run_report($property_id, $access, $body, $period_days);
    if (is_wp_error($raw)) {
        return $raw;
    }
    if (!is_array($raw) || isset($raw["error"])) {
        return is_array($raw) ? $raw : new WP_Error("ga4_ecom", "Błąd GA4 ecommerce API.");
    }

    $row = (array) (($raw["rows"][0] ?? []));
    $mets = (array) ($row["metricValues"] ?? []);

    return [
        "purchase_revenue" => round((float) ($mets[0]["value"] ?? 0), 2),
        "purchase_count" => (int) round((float) ($mets[1]["value"] ?? 0)),
        "total_revenue" => round((float) ($mets[2]["value"] ?? 0), 2),
    ];
}

/**
 * KPI konwersji: purchase > leady makro (bez sumowania wszystkich makro-eventów).
 *
 * @param list<array<string, mixed>> $breakdown
 */
function ups_audit_ga4_resolve_kpi_conversions(array $breakdown, array $ecommerce_kpi): int
{
    $purchase_api = (int) ($ecommerce_kpi["purchase_count"] ?? 0);
    if ($purchase_api > 0) {
        return $purchase_api;
    }

    $purchase_ev = 0;
    $lead_ev = 0;
    $lead_names = ["generate_lead", "lead", "contact", "quote_request", "zapytanie", "submit_lead"];
    foreach ($breakdown as $row) {
        if (!is_array($row)) {
            continue;
        }
        $name = strtolower((string) ($row["event"] ?? ""));
        $cnt = (int) ($row["count"] ?? 0);
        if ($name === "purchase" || strpos($name, "purchase") !== false) {
            $purchase_ev += $cnt;
        } elseif (in_array($name, $lead_names, true)) {
            $lead_ev += $cnt;
        } elseif ((string) ($row["kind"] ?? "") === "macro" && $name !== "purchase") {
            foreach ($lead_names as $ln) {
                if ($ln !== "" && strpos($name, $ln) !== false) {
                    $lead_ev += $cnt;
                    break;
                }
            }
        }
    }

    if ($purchase_ev > 0) {
        return $purchase_ev;
    }

    return $lead_ev;
}

/**
 * @param list<array<string, mixed>> $breakdown
 * @return list<string>
 */
function ups_audit_ga4_build_revenue_quality_notes(
    array $breakdown,
    array $ecommerce_kpi,
    int $sessions,
    int $kpi_conversions,
    float $session_revenue_sum
): array {
    $notes = [];
    $purchase_rev = (float) ($ecommerce_kpi["purchase_revenue"] ?? 0);
    $purchase_cnt = (int) ($ecommerce_kpi["purchase_count"] ?? 0);
    $total_rev_api = (float) ($ecommerce_kpi["total_revenue"] ?? 0);

    if ($purchase_rev > 0 && $session_revenue_sum > 0 && $session_revenue_sum > $purchase_rev * 1.25) {
        $notes[] = sprintf(
            __("Przychód sesji (%.0f PLN) zawyża purchaseRevenue (%.0f PLN) — KPI używa tylko zakupów.", "upsellio"),
            $session_revenue_sum,
            $purchase_rev
        );
    }

    if ($purchase_cnt > 0 && $sessions > 0) {
        $cr = ($purchase_cnt / $sessions) * 100;
        if ($cr > 8) {
            $notes[] = sprintf(
                __("CR zakupów %.1f%% (%d purchase / %d sesji) — sprawdź duplikaty eventu purchase na thank-you.", "upsellio"),
                $cr,
                $purchase_cnt,
                $sessions
            );
        }
    }

    $checkout = 0;
    $purchase_ev = 0;
    $engagement_with_rev = [];
    foreach ($breakdown as $row) {
        if (!is_array($row)) {
            continue;
        }
        $name = strtolower((string) ($row["event"] ?? ""));
        $cnt = (int) ($row["count"] ?? 0);
        $rev = (float) ($row["revenue"] ?? 0);
        if ($name === "begin_checkout") {
            $checkout = $cnt;
        }
        if ($name === "purchase") {
            $purchase_ev = $cnt;
        }
        if ($rev > 0 && (string) ($row["kind"] ?? "") === "engagement") {
            $engagement_with_rev[] = $name . " (" . number_format($rev, 0, ",", " ") . " PLN)";
        }
    }

    if ($purchase_cnt > 0 && $checkout > 0 && $purchase_cnt > $checkout * 2) {
        $notes[] = sprintf(
            __("purchase (%d) >> begin_checkout (%d) — prawdopodobne wielokrotne odpalanie purchase.", "upsellio"),
            $purchase_cnt,
            $checkout
        );
    }

    if ($engagement_with_rev !== []) {
        $notes[] = __("Eventy engagement z revenue > 0: ", "upsellio") . implode(", ", array_slice($engagement_with_rev, 0, 4))
            . __(" — nie powinny zawyżać przychodu e-commerce.", "upsellio");
    }

    if ($kpi_conversions > 0 && $purchase_cnt <= 0 && $total_rev_api > 10000) {
        $notes[] = __("Brak ecommercePurchases, ale wysoki totalRevenue — zweryfikuj definicję konwersji w GA4.", "upsellio");
    }

    return $notes;
}

add_action("ups_audit_sync_resource_action", "ups_audit_sync_resource_action");

function ups_audit_daily_sync_job()
{
    ups_audit_sync_all_mapped_resources(0);
}
add_action("ups_audit_daily_sync", "ups_audit_daily_sync_job");

function ups_audit_schedule_daily_sync()
{
    if (!wp_next_scheduled("ups_audit_daily_sync")) {
        wp_schedule_event(strtotime("tomorrow 06:00"), "daily", "ups_audit_daily_sync");
    }
}
add_action("init", "ups_audit_schedule_daily_sync");

function ups_audit_ai_model_from_option($option_name, $fallback)
{
    $pref = sanitize_key((string) get_option($option_name, $fallback));
    if ($pref === "haiku") {
        return function_exists("upsellio_ai_model_for") ? upsellio_ai_model_for("scoring") : null;
    }
    return function_exists("upsellio_ai_model_for") ? upsellio_ai_model_for("weekly_brief") : null;
}

function ups_audit_build_monthly_report_prompt($cur, $prev, $client, $ctx)
{
    $client_name = $client instanceof WP_Post ? (string) $client->post_title : "Klient";
    $delta = function ($a, $b) {
        $a = (float) $a;
        $b = (float) $b;
        return $b > 0 ? round((($a - $b) / $b) * 100, 1) : 0;
    };
    $revenue_q = function_exists("ups_audit_revenue_quality") ? ups_audit_revenue_quality($cur) : ["trusted" => true];
    $ga4_trusted = !empty($revenue_q["trusted"]);
    $crm_funnel = (array) (($cur["intelligence"]["crm_revenue"]["funnel_totals"] ?? []));
    $crm_rev = (float) ($crm_funnel["revenue"] ?? 0);
    $crm_leads = (int) ($crm_funnel["leads"] ?? 0);
    $crm_won = (int) ($crm_funnel["won"] ?? 0);
    $ads_cost = round((float) ($cur["ads_cost"] ?? 0), 2);
    $crm_roas = $ads_cost > 0 && $crm_rev > 0 ? round($crm_rev / $ads_cost, 2) : 0;

    $roas_line = $ga4_trusted
        ? "- ROAS GA4 (purchase): " . round((float) ($cur["roas"] ?? 0), 2) . "x\n"
        : "- UWAGA: Przychód GA4 NIEJEST WIARYGODNY — NIE cytuj ROAS GA4 ani przychodu e-commerce.\n"
            . "- ROAS CRM (wygrane / koszt Ads): " . $crm_roas . "x\n"
            . "- Leady CRM: " . $crm_leads . " · Wygrane: " . $crm_won . " · Przychód CRM: " . round($crm_rev, 0) . " PLN\n";

    $attr_conf = (int) (($cur["attribution_confidence"]["score"] ?? 0));
    $attr_line = $attr_conf > 0 ? "- Attribution Confidence: {$attr_conf}%\n" : "";

    return "Jesteś senior account managerem agencji marketingowej. Pisz po polsku, profesjonalnie, dla klienta.\n\nKLIENT: {$client_name}\nOKRES: ostatnie 30 dni\n\nDANE:\n- Sesje GA4: " . (int) ($cur["ga4_sessions"] ?? 0) . " vs " . (int) ($prev["ga4_sessions"] ?? 0) . " (" . $delta($cur["ga4_sessions"] ?? 0, $prev["ga4_sessions"] ?? 0) . "%)\n- Kliknięcia GSC: " . (int) ($cur["gsc_clicks"] ?? 0) . " vs " . (int) ($prev["gsc_clicks"] ?? 0) . "\n- Wydatek Ads: {$ads_cost} PLN\n- Konwersje Ads: " . round((float) ($cur["ads_conversions"] ?? 0), 1) . "\n{$roas_line}{$attr_line}\nKONTEKST CRM:\n" . (string) $ctx . "\n\nWygeneruj raport HTML z sekcjami: podsumowanie, źródła danych, rekomendacje, największe zmiany. Bez bloków markdown."
        . ($ga4_trusted ? "" : "\n\nKRYTYCZNE: Nie rekomenduj skalowania na podstawie ROAS GA4. Używaj wyłącznie metryk CRM.");
}

function ups_audit_generate_monthly_report($client_id)
{
    $client_id = (int) $client_id;
    $client = get_post($client_id);
    if (!($client instanceof WP_Post) || $client->post_type !== "crm_client") {
        return ["id" => 0, "html" => ""];
    }
    $cur = ups_audit_aggregate_client_data($client_id, 30, 0);
    $prev = function_exists("ups_audit_aggregate_previous_slice")
        ? ups_audit_aggregate_previous_slice($cur)
        : ups_audit_aggregate_client_data($client_id, 30, 30);
    $ctx = function_exists("upsellio_ai_master_context") ? (string) upsellio_ai_master_context("client_audit", $client_id) : "";
    $prompt = ups_audit_build_monthly_report_prompt($cur, $prev, $client, $ctx);
    $model = ups_audit_ai_model_from_option("ups_audit_anthropic_model_reports", "sonnet");
    $GLOBALS["upsellio_ai_current_task"] = "client_audit";
    $result = function_exists("upsellio_anthropic_crm_send_user_prompt")
        ? upsellio_anthropic_crm_send_user_prompt($prompt, 5000, 120, $model)
        : null;
    $html = is_string($result) && $result !== "" ? $result : "<h2>Raport miesięczny</h2><p>Brak odpowiedzi AI. Sprawdź konfigurację API.</p>";
    $report_id = wp_insert_post([
        "post_type" => "crm_audit_report",
        "post_title" => "Raport miesięczny - " . wp_date("F Y") . " - " . $client->post_title,
        "post_content" => $html,
        "post_status" => "publish",
    ]);
    if ($report_id > 0) {
        update_post_meta($report_id, "_ups_report_client_id", $client_id);
        update_post_meta($report_id, "_ups_report_type", "monthly");
        update_post_meta($report_id, "_ups_report_data_snapshot", ["current" => $cur, "previous" => $prev]);
    }
    return ["id" => (int) $report_id, "html" => $html];
}

function ups_audit_generate_with_ai($prompt, $max_tokens, $timeout, $model_option, $fallback_html)
{
    $model = ups_audit_ai_model_from_option($model_option, "sonnet");
    $GLOBALS["upsellio_ai_current_task"] = "client_audit";
    $result = function_exists("upsellio_anthropic_crm_send_user_prompt")
        ? upsellio_anthropic_crm_send_user_prompt((string) $prompt, (int) $max_tokens, (int) $timeout, $model)
        : null;
    if (is_string($result) && trim($result) !== "") {
        return $result;
    }
    return (string) $fallback_html;
}

function ups_audit_create_report_post($client_id, $type, $title, $content, $snapshot = [])
{
    $report_id = wp_insert_post([
        "post_type" => "crm_audit_report",
        "post_title" => (string) $title,
        "post_content" => (string) $content,
        "post_status" => "publish",
    ]);
    if ($report_id > 0) {
        update_post_meta($report_id, "_ups_report_client_id", (int) $client_id);
        update_post_meta($report_id, "_ups_report_type", sanitize_key((string) $type));
        update_post_meta($report_id, "_ups_report_data_snapshot", is_array($snapshot) ? $snapshot : []);
    }
    return (int) $report_id;
}

function ups_audit_generate_audit_report($client_id)
{
    $client_id = (int) $client_id;
    $client = get_post($client_id);
    if (!($client instanceof WP_Post) || $client->post_type !== "crm_client") {
        return ["id" => 0, "html" => ""];
    }
    $cur = ups_audit_aggregate_client_data($client_id, 30, 0);
    $prompt = "Przygotuj audyt punktowy dla klienta " . $client->post_title . ". Dane: " . wp_json_encode($cur) . ". Zwróć HTML z listą problemów (impact/effort) i szybkimi poprawkami.";
    $html = ups_audit_generate_with_ai($prompt, 1800, 60, "ups_audit_anthropic_model_audits", "<h2>Audyt punktowy</h2><p>Brak odpowiedzi AI.</p>");
    $report_id = ups_audit_create_report_post($client_id, "audit", "Audyt punktowy - " . wp_date("Y-m-d") . " - " . $client->post_title, $html, ["current" => $cur]);
    return ["id" => $report_id, "html" => $html];
}

function ups_audit_generate_action_plan($client_id)
{
    $client_id = (int) $client_id;
    $client = get_post($client_id);
    if (!($client instanceof WP_Post) || $client->post_type !== "crm_client") {
        return ["id" => 0, "html" => ""];
    }
    $cur = ups_audit_aggregate_client_data($client_id, 30, 0);
    $prompt = "Stwórz plan działań 30d dla klienta " . $client->post_title . " na bazie danych: " . wp_json_encode($cur) . ". Wymagane: 8 zadań, priorytet, impact, effort. Format HTML.";
    $html = ups_audit_generate_with_ai($prompt, 2200, 70, "ups_audit_anthropic_model_reports", "<h2>Plan działań 30 dni</h2><p>Brak odpowiedzi AI.</p>");
    $report_id = ups_audit_create_report_post($client_id, "plan", "Plan działań 30d - " . wp_date("Y-m-d") . " - " . $client->post_title, $html, ["current" => $cur]);
    return ["id" => $report_id, "html" => $html];
}

function ups_audit_generate_comparison($client_id)
{
    $client_id = (int) $client_id;
    $client = get_post($client_id);
    if (!($client instanceof WP_Post) || $client->post_type !== "crm_client") {
        return ["id" => 0, "html" => ""];
    }
    $cur = ups_audit_aggregate_client_data($client_id, 30, 0);
    $prev = function_exists("ups_audit_aggregate_previous_slice")
        ? ups_audit_aggregate_previous_slice($cur)
        : ups_audit_aggregate_client_data($client_id, 30, 30);
    $prompt = "Porównaj okres-do-okresu dla klienta " . $client->post_title . ". Current: " . wp_json_encode($cur) . " Previous: " . wp_json_encode($prev) . ". Wyjaśnij 3 największe zmiany i rekomendacje. Format HTML.";
    $html = ups_audit_generate_with_ai($prompt, 1600, 55, "ups_audit_anthropic_model_audits", "<h2>Porównanie okresów</h2><p>Brak odpowiedzi AI.</p>");
    $report_id = ups_audit_create_report_post($client_id, "comparison", "Porównanie okresów - " . wp_date("Y-m-d") . " - " . $client->post_title, $html, ["current" => $cur, "previous" => $prev]);
    return ["id" => $report_id, "html" => $html];
}

function ups_audit_generate_brief($client_id)
{
    $client_id = (int) $client_id;
    $client = get_post($client_id);
    if (!($client instanceof WP_Post) || $client->post_type !== "crm_client") {
        return ["id" => 0, "html" => ""];
    }
    $cur = ups_audit_aggregate_client_data($client_id, 14, 0);
    $prompt = "Napisz krótki brief (4-6 zdań) dla klienta " . $client->post_title . " na bazie danych: " . wp_json_encode($cur) . ".";
    $text = ups_audit_generate_with_ai($prompt, 500, 35, "ups_audit_anthropic_model_audits", "Brief AI chwilowo niedostępny.");
    $html = "<h2>Brief AI</h2><p>" . nl2br(esc_html(wp_strip_all_tags($text))) . "</p>";
    $report_id = ups_audit_create_report_post($client_id, "brief", "Brief AI - " . wp_date("Y-m-d H:i") . " - " . $client->post_title, $html, ["current" => $cur]);
    return ["id" => $report_id, "html" => $html];
}

function ups_audit_generate_report_by_type($client_id, $type)
{
    $type = sanitize_key((string) $type);
    if ($type === "audit") {
        return ups_audit_generate_audit_report($client_id);
    }
    if ($type === "plan") {
        return ups_audit_generate_action_plan($client_id);
    }
    if ($type === "comparison") {
        return ups_audit_generate_comparison($client_id);
    }
    if ($type === "brief") {
        return ups_audit_generate_brief($client_id);
    }
    if ($type === "seo_roadmap" && function_exists("ups_audit_generate_seo_roadmap_ai")) {
        return ups_audit_generate_seo_roadmap_ai($client_id);
    }
    if ($type === "ux_audit" && function_exists("ups_audit_generate_ux_audit_ai")) {
        return ups_audit_generate_ux_audit_ai($client_id);
    }
    return ups_audit_generate_monthly_report($client_id);
}

function ups_audit_ensure_default_options()
{
    $defaults = [
        "ups_audit_default_compare_window" => 30,
        "ups_audit_report_templates" => [],
        "ups_audit_default_email_signature" => "",
        "ups_audit_pdf_brand_color" => "#0ABFA3",
        "ups_audit_anthropic_model_reports" => "sonnet",
        "ups_audit_anthropic_model_audits" => "haiku",
        "ups_audit_alert_email" => "",
        "ups_audit_slack_webhook_url" => "",
    ];
    foreach ($defaults as $key => $value) {
        if (get_option($key, null) === null) {
            update_option($key, $value, false);
        }
    }
}
add_action("init", "ups_audit_ensure_default_options", 20);

function ups_audit_cleanup_old_resource_cache()
{
    $resources = get_posts([
        "post_type" => "crm_audit_resource",
        "posts_per_page" => -1,
        "post_status" => ["publish", "draft"],
    ]);
    $limit_ts = time() - (90 * DAY_IN_SECONDS);
    foreach ($resources as $r) {
        if (!($r instanceof WP_Post)) {
            continue;
        }
        $last = strtotime((string) get_post_meta((int) $r->ID, "_ups_resource_last_data_sync", true));
        if ($last > 0 && $last < $limit_ts) {
            update_post_meta((int) $r->ID, "_ups_resource_data_cache", []);
        }
    }
}
add_action("ups_audit_daily_sync", "ups_audit_cleanup_old_resource_cache", 30);

