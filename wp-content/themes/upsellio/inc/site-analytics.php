<?php

if (!defined("ABSPATH")) {
    exit;
}

/**
 * Slug strony admin „Analityka SEO” (menu Upsellio).
 */
function upsellio_site_analytics_page_slug(): string
{
    return "upsellio-site-analytics";
}

/**
 * Kanoniczny URL panelu Analityka SEO (admin.php — OAuth redirect URI musi z tym się zgadzać).
 *
 * @param array<string, scalar> $extra Dodatkowe parametry GET.
 */
function upsellio_site_analytics_admin_url(array $extra = []): string
{
    $query = array_merge(["page" => upsellio_site_analytics_page_slug()], $extra);

    return add_query_arg($query, admin_url("admin.php"));
}

/**
 * Stary link Wpisy → (edit.php?page=…) — przekieruj na menu Upsellio.
 */
function upsellio_site_analytics_legacy_edit_redirect(): void
{
    if (!is_admin() || !current_user_can("edit_posts")) {
        return;
    }
    if (!isset($_GET["page"]) || (string) wp_unslash($_GET["page"]) !== upsellio_site_analytics_page_slug()) {
        return;
    }
    global $pagenow;
    if ($pagenow !== "edit.php") {
        return;
    }
    wp_safe_redirect(upsellio_site_analytics_admin_url());
    exit;
}

add_action("admin_init", "upsellio_site_analytics_legacy_edit_redirect", 0);

/**
 * Skrót w Ustawienia → Analityka SEO: ten sam panel co w menu głównym.
 */
function upsellio_site_analytics_redirect_from_wp_settings(): void
{
    if (!current_user_can("edit_posts")) {
        wp_die(esc_html__("Brak uprawnień.", "upsellio"));
    }
    wp_safe_redirect(upsellio_site_analytics_admin_url());
    exit;
}

function upsellio_site_analytics_menu(): void
{
    add_menu_page(
        __("Analityka SEO i konwersji", "upsellio"),
        __("Analityka SEO", "upsellio"),
        "edit_posts",
        upsellio_site_analytics_page_slug(),
        "upsellio_render_site_analytics_page",
        "dashicons-chart-area",
        59
    );

    add_submenu_page(
        "options-general.php",
        __("Analityka SEO (GSC, GA4, konwersje)", "upsellio"),
        __("Analityka SEO", "upsellio"),
        "edit_posts",
        "upsellio-analytics-from-settings",
        "upsellio_site_analytics_redirect_from_wp_settings"
    );
}
add_action("admin_menu", "upsellio_site_analytics_menu");

function upsellio_is_trackable_content_view()
{
    if (is_admin() || wp_doing_ajax() || is_preview()) {
        return false;
    }

    if (!is_singular(["post", "page", "miasto", "definicja"])) {
        return false;
    }

    if (function_exists("upsellio_is_internal_tracking_user") && upsellio_is_internal_tracking_user()) {
        return false;
    }

    return true;
}

function upsellio_track_content_view()
{
    if (!upsellio_is_trackable_content_view()) {
        return;
    }

    $post_id = (int) get_queried_object_id();
    if ($post_id <= 0) {
        return;
    }

    $cookie_key = "ups_view_" . $post_id;
    if (isset($_COOKIE[$cookie_key])) {
        return;
    }

    $total_views = (int) get_post_meta($post_id, "_upsellio_views_total", true);
    update_post_meta($post_id, "_upsellio_views_total", $total_views + 1);

    $today = wp_date("Y-m-d");
    $daily_views = get_option("upsellio_daily_views", []);
    if (!is_array($daily_views)) {
        $daily_views = [];
    }
    if (!isset($daily_views[$today]) || !is_array($daily_views[$today])) {
        $daily_views[$today] = [];
    }
    $daily_views[$today][$post_id] = (int) ($daily_views[$today][$post_id] ?? 0) + 1;

    if (count($daily_views) > 120) {
        ksort($daily_views);
        $daily_views = array_slice($daily_views, -120, 120, true);
    }

    update_option("upsellio_daily_views", $daily_views, false);
    setcookie($cookie_key, "1", time() + HOUR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
}
add_action("template_redirect", "upsellio_track_content_view");

function upsellio_get_analytics_dates($days)
{
    $days = max(1, (int) $days);
    $dates = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $dates[] = wp_date("Y-m-d", strtotime("-{$i} days"));
    }

    return $dates;
}

function upsellio_get_post_views_for_dates($post_id, $dates)
{
    $daily_views = get_option("upsellio_daily_views", []);
    if (!is_array($daily_views)) {
        $daily_views = [];
    }

    $total = 0;
    foreach ($dates as $date_key) {
        $total += (int) ($daily_views[$date_key][$post_id] ?? 0);
    }

    return $total;
}

function upsellio_get_daily_views_series($dates)
{
    $daily_views = get_option("upsellio_daily_views", []);
    if (!is_array($daily_views)) {
        $daily_views = [];
    }

    $series = [];
    foreach ($dates as $date_key) {
        $views_for_date = $daily_views[$date_key] ?? [];
        $series[$date_key] = is_array($views_for_date) ? (int) array_sum($views_for_date) : 0;
    }

    return $series;
}

function upsellio_get_daily_leads_series($dates)
{
    $series = [];
    if (empty($dates)) {
        return $series;
    }

    foreach ($dates as $date_key) {
        $series[$date_key] = 0;
    }

    $from_date = $dates[0];
    $to_date = $dates[count($dates) - 1];
    $query = new WP_Query([
        "post_type" => "lead",
        "post_status" => "publish",
        "posts_per_page" => 1000,
        "date_query" => [[
            "after" => $from_date,
            "before" => $to_date,
            "inclusive" => true,
        ]],
        "fields" => "ids",
    ]);

    foreach ($query->posts as $lead_id) {
        $date_key = (string) get_post_time("Y-m-d", false, (int) $lead_id);
        if (isset($series[$date_key])) {
            $series[$date_key]++;
        }
    }

    return $series;
}

function upsellio_get_daily_keyword_series($keyword_rows, $dates)
{
    $series = [];
    foreach ($dates as $date_key) {
        $series[$date_key] = [
            "impressions" => 0,
            "clicks" => 0,
        ];
    }

    foreach ($keyword_rows as $row) {
        $date_key = (string) ($row["date"] ?? "");
        if (!isset($series[$date_key])) {
            continue;
        }
        $series[$date_key]["impressions"] += (int) ($row["impressions"] ?? 0);
        $series[$date_key]["clicks"] += (int) ($row["clicks"] ?? 0);
    }

    return $series;
}

function upsellio_get_keyword_metrics_data()
{
    $rows = get_option("upsellio_keyword_metrics_rows", []);
    return is_array($rows) ? $rows : [];
}

function upsellio_normalize_keyword_metrics_csv($csv_raw)
{
    $csv_raw = trim((string) $csv_raw);
    if ($csv_raw === "") {
        return [];
    }

    $lines = preg_split("/\r\n|\n|\r/", $csv_raw);
    if (!$lines || count($lines) < 1) {
        return [];
    }

    $rows = [];
    foreach ($lines as $index => $line) {
        if (trim($line) === "") {
            continue;
        }

        $cells = str_getcsv($line, ",");
        if (!is_array($cells) || count($cells) < 5) {
            continue;
        }

        if ($index === 0 && preg_match("/keyword|fraza/i", (string) ($cells[0] ?? ""))) {
            continue;
        }

        $keyword = sanitize_text_field((string) ($cells[0] ?? ""));
        $url = esc_url_raw((string) ($cells[1] ?? ""));
        $position = (float) str_replace(",", ".", (string) ($cells[2] ?? 0));
        $impressions = (int) preg_replace("/[^0-9]/", "", (string) ($cells[3] ?? 0));
        $clicks = (int) preg_replace("/[^0-9]/", "", (string) ($cells[4] ?? 0));
        $ctr_cell = (string) ($cells[5] ?? "");
        $date_cell = sanitize_text_field((string) ($cells[6] ?? wp_date("Y-m-d")));

        if ($keyword === "" || $url === "") {
            continue;
        }

        $ctr_clean = str_replace(["%", ","], ["", "."], $ctr_cell);
        $ctr = $ctr_clean !== "" ? (float) $ctr_clean : ($impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0);
        $date = preg_match("/^\d{4}-\d{2}-\d{2}$/", $date_cell) ? $date_cell : wp_date("Y-m-d");

        $rows[] = [
            "keyword" => $keyword,
            "url" => $url,
            "position" => max(1, $position),
            "impressions" => max(0, $impressions),
            "clicks" => max(0, $clicks),
            "ctr" => max(0, $ctr),
            "date" => $date,
        ];
    }

    return array_slice($rows, 0, 5000);
}

function upsellio_handle_keyword_metrics_import()
{
    if (!is_admin() || !current_user_can("edit_posts")) {
        return;
    }

    if (!isset($_POST["upsellio_keyword_metrics_import"])) {
        return;
    }

    check_admin_referer("upsellio_keyword_metrics_action", "upsellio_keyword_metrics_nonce");
    $csv_raw = isset($_POST["keyword_metrics_csv"]) ? wp_unslash($_POST["keyword_metrics_csv"]) : "";
    $rows = upsellio_normalize_keyword_metrics_csv($csv_raw);

    update_option("upsellio_keyword_metrics_rows", $rows, false);
    wp_safe_redirect(
        upsellio_site_analytics_admin_url([
            "upsellio_metrics_imported" => (string) count($rows),
        ])
    );
    exit;
}
add_action("admin_init", "upsellio_handle_keyword_metrics_import");

function upsellio_get_gsc_credentials()
{
    $credentials = get_option("upsellio_gsc_credentials", []);
    if (!is_array($credentials)) {
        return [
            "client_id" => "",
            "client_secret" => "",
            "refresh_token" => "",
            "property" => "",
        ];
    }

    return [
        "client_id" => trim((string) ($credentials["client_id"] ?? "")),
        "client_secret" => trim((string) ($credentials["client_secret"] ?? "")),
        "refresh_token" => trim((string) ($credentials["refresh_token"] ?? "")),
        "property" => trim((string) ($credentials["property"] ?? "")),
    ];
}

function upsellio_normalize_oauth_credential($value)
{
    return preg_replace("/\s+/", "", trim((string) $value));
}

function upsellio_gsc_debug_logs_option_key()
{
    return "upsellio_gsc_debug_logs";
}

function upsellio_gsc_debug_trace_id()
{
    if (function_exists("wp_generate_uuid4")) {
        return "gsc_" . wp_generate_uuid4();
    }
    return "gsc_" . uniqid("", true);
}

function upsellio_gsc_truncate($value, $max_length = 1200)
{
    $value = (string) $value;
    if (strlen($value) <= $max_length) {
        return $value;
    }
    return substr($value, 0, $max_length) . "...[truncated]";
}

function upsellio_gsc_mask_value($value, $prefix = 6, $suffix = 4)
{
    $value = (string) $value;
    $length = strlen($value);
    if ($length === 0) {
        return "";
    }
    if ($length <= ($prefix + $suffix)) {
        return str_repeat("*", $length);
    }
    return substr($value, 0, $prefix) . str_repeat("*", max(4, $length - ($prefix + $suffix))) . substr($value, -$suffix);
}

function upsellio_gsc_redact_sensitive_fields($value)
{
    $sensitive_keys = ["client_secret", "refresh_token", "access_token", "authorization", "id_token"];
    if (!is_array($value)) {
        return $value;
    }

    $redacted = [];
    foreach ($value as $key => $item) {
        $normalized_key = strtolower((string) $key);
        if (in_array($normalized_key, $sensitive_keys, true) && is_string($item)) {
            $redacted[$key] = upsellio_gsc_mask_value($item);
            continue;
        }

        if (is_array($item)) {
            $redacted[$key] = upsellio_gsc_redact_sensitive_fields($item);
            continue;
        }
        $redacted[$key] = $item;
    }

    return $redacted;
}

function upsellio_gsc_log($event, $data = [], $trace_id = "")
{
    $logs = get_option(upsellio_gsc_debug_logs_option_key(), []);
    if (!is_array($logs)) {
        $logs = [];
    }

    $entry = [
        "time" => wp_date("Y-m-d H:i:s"),
        "event" => sanitize_text_field((string) $event),
        "trace_id" => sanitize_text_field((string) $trace_id),
        "data" => upsellio_gsc_redact_sensitive_fields(is_array($data) ? $data : ["value" => (string) $data]),
    ];

    $logs[] = $entry;
    if (count($logs) > 250) {
        $logs = array_slice($logs, -250, 250, false);
    }

    update_option(upsellio_gsc_debug_logs_option_key(), $logs, false);
}

function upsellio_gsc_get_logs()
{
    $logs = get_option(upsellio_gsc_debug_logs_option_key(), []);
    return is_array($logs) ? $logs : [];
}

function upsellio_save_gsc_credentials($client_id, $client_secret, $refresh_token, $property)
{
    $existing = upsellio_get_gsc_credentials();
    $payload = [
        "client_id" => upsellio_normalize_oauth_credential($client_id),
        "client_secret" => upsellio_normalize_oauth_credential($client_secret),
        "refresh_token" => upsellio_normalize_oauth_credential($refresh_token),
        "property" => sanitize_text_field((string) $property),
    ];

    if (
        $existing["client_id"] !== $payload["client_id"] ||
        $existing["client_secret"] !== $payload["client_secret"] ||
        $existing["refresh_token"] !== $payload["refresh_token"]
    ) {
        delete_transient("upsellio_gsc_access_token");
        delete_transient(upsellio_gsc_access_token_transient_key($existing));
        delete_transient(upsellio_gsc_access_token_transient_key($payload));
    }

    update_option("upsellio_gsc_credentials", $payload, false);
}

function upsellio_gsc_access_token_transient_key($credentials)
{
    $client_id = (string) ($credentials["client_id"] ?? "");
    $refresh_token = (string) ($credentials["refresh_token"] ?? "");
    $fingerprint = md5($client_id . "|" . $refresh_token);
    return "upsellio_gsc_access_token_" . $fingerprint;
}

/**
 * Po invalid_grant z Google — usuń odwołany refresh token z opcji, żeby uniknąć pętli i wymusić ponowne „Zaloguj przez Google”.
 */
function upsellio_gsc_clear_stored_refresh_token_after_invalid_grant(array $attempted_credentials): void
{
    $stored = upsellio_get_gsc_credentials();
    $attempted_rt = trim((string) ($attempted_credentials["refresh_token"] ?? ""));
    $stored_rt = trim((string) ($stored["refresh_token"] ?? ""));
    if ($attempted_rt === "" || $stored_rt === "" || !hash_equals($stored_rt, $attempted_rt)) {
        return;
    }

    upsellio_save_gsc_credentials(
        (string) ($stored["client_id"] ?? ""),
        (string) ($stored["client_secret"] ?? ""),
        "",
        (string) ($stored["property"] ?? "")
    );
    delete_option(upsellio_google_oauth_permissions_option_key());
    upsellio_gsc_log(
        "oauth.refresh_token.cleared_invalid_grant",
        ["message" => "Stored refresh token removed after invalid_grant; user must re-authorize OAuth."],
        ""
    );
}

function upsellio_gsc_get_access_token($credentials, $trace_id = "")
{
    $transient_key = upsellio_gsc_access_token_transient_key($credentials);
    $cached_token = get_transient($transient_key);
    if (is_string($cached_token) && $cached_token !== "") {
        upsellio_gsc_log("oauth.access_token.cache_hit", [
            "transient_key" => $transient_key,
            "access_token_preview" => upsellio_gsc_mask_value($cached_token),
        ], $trace_id);
        return $cached_token;
    }

    $client_id = (string) ($credentials["client_id"] ?? "");
    $client_secret = (string) ($credentials["client_secret"] ?? "");
    $refresh_token = (string) ($credentials["refresh_token"] ?? "");

    upsellio_gsc_log("oauth.access_token.request_started", [
        "transient_key" => $transient_key,
        "client_id" => $client_id,
        "client_secret_preview" => upsellio_gsc_mask_value($client_secret),
        "refresh_token_preview" => upsellio_gsc_mask_value($refresh_token),
    ], $trace_id);

    if ($client_id === "" || $client_secret === "" || $refresh_token === "") {
        upsellio_gsc_log("oauth.access_token.missing_credentials", [
            "has_client_id" => $client_id !== "",
            "has_client_secret" => $client_secret !== "",
            "has_refresh_token" => $refresh_token !== "",
        ], $trace_id);
        return new WP_Error("upsellio_gsc_missing_credentials", "Brakuje danych OAuth do Google Search Console.");
    }

    $response = wp_remote_post("https://oauth2.googleapis.com/token", [
        "timeout" => 25,
        "body" => [
            "client_id" => $client_id,
            "client_secret" => $client_secret,
            "refresh_token" => $refresh_token,
            "grant_type" => "refresh_token",
        ],
    ]);
    if (is_wp_error($response)) {
        upsellio_gsc_log("oauth.access_token.http_wp_error", [
            "message" => $response->get_error_message(),
        ], $trace_id);
        return $response;
    }

    $status = (int) wp_remote_retrieve_response_code($response);
    $raw_body = (string) wp_remote_retrieve_body($response);
    $body = json_decode($raw_body, true);
    upsellio_gsc_log("oauth.access_token.http_response", [
        "status" => $status,
        "body" => is_array($body) ? $body : upsellio_gsc_truncate($raw_body),
    ], $trace_id);

    if ($status >= 400) {
        $error = is_array($body) ? (string) ($body["error"] ?? "") : "";
        $error_description = is_array($body) ? (string) ($body["error_description"] ?? "") : "";
        $details = trim($error . ($error_description !== "" ? ": " . $error_description : ""));
        if ($details === "") {
            $details = "Nie udało się odświeżyć tokena OAuth.";
        }
        upsellio_gsc_log("oauth.access_token.http_error", [
            "status" => $status,
            "error" => $error,
            "error_description" => $error_description,
            "details" => $details,
        ], $trace_id);
        if ($error === "invalid_grant") {
            upsellio_gsc_clear_stored_refresh_token_after_invalid_grant($credentials);
            return new WP_Error(
                "upsellio_gsc_token_revoked",
                "Google odwołał refresh token (invalid_grant). Zapisane połączenie zostało usunięte — kliknij ponownie „Zaloguj przez Google i autoryzuj GSC + GA4” i zatwierdź dostęp."
            );
        }
        return new WP_Error(
            "upsellio_gsc_token_http_error",
            "OAuth token error (HTTP " . $status . "): " . $details . ". Sprawdź czy refresh token pochodzi z tego samego OAuth Client ID i aktualnego Client Secret."
        );
    }

    $access_token = is_array($body) ? (string) ($body["access_token"] ?? "") : "";
    $expires_in = is_array($body) ? (int) ($body["expires_in"] ?? 3600) : 3600;
    if ($access_token === "") {
        $error = is_array($body) ? (string) ($body["error"] ?? "") : "";
        $error_description = is_array($body) ? (string) ($body["error_description"] ?? "") : "";
        $details = trim($error . ($error_description !== "" ? ": " . $error_description : ""));
        if ($details === "") {
            $details = "Nie udało się pobrać access tokena.";
        }
        upsellio_gsc_log("oauth.access_token.empty_token_error", [
            "details" => $details,
            "raw_body" => is_array($body) ? $body : upsellio_gsc_truncate($raw_body),
        ], $trace_id);
        return new WP_Error("upsellio_gsc_token_error", $details);
    }

    set_transient($transient_key, $access_token, max(300, $expires_in - 120));
    upsellio_gsc_log("oauth.access_token.saved_to_cache", [
        "transient_key" => $transient_key,
        "expires_in" => $expires_in,
        "access_token_preview" => upsellio_gsc_mask_value($access_token),
    ], $trace_id);

    return $access_token;
}

function upsellio_gsc_extract_error_message($body, $fallback_message)
{
    if (is_array($body) && isset($body["error"])) {
        if (is_array($body["error"]) && isset($body["error"]["message"])) {
            return (string) $body["error"]["message"];
        }
        if (is_string($body["error"])) {
            return (string) $body["error"];
        }
    }

    if (is_array($body) && isset($body["error_description"]) && is_string($body["error_description"])) {
        return (string) $body["error_description"];
    }

    return (string) $fallback_message;
}

function upsellio_gsc_has_property_access($property, $site_entries)
{
    $property = trim((string) $property);
    if ($property === "") {
        return false;
    }

    $property_with_slash = preg_match("/^https?:\/\/.+\/$/", $property) ? $property : $property . "/";
    foreach ($site_entries as $entry) {
        $site_url = (string) ($entry["siteUrl"] ?? "");
        if ($site_url === "") {
            continue;
        }
        if ($site_url === $property || $site_url === $property_with_slash) {
            return true;
        }
    }

    return false;
}

function upsellio_gsc_fetch_rows($credentials, $days = 30, $trace_id = "")
{
    upsellio_gsc_log("gsc.sync.started", [
        "days" => (int) $days,
        "property" => (string) ($credentials["property"] ?? ""),
        "client_id" => (string) ($credentials["client_id"] ?? ""),
    ], $trace_id);

    $access_token = upsellio_gsc_get_access_token($credentials, $trace_id);
    if (is_wp_error($access_token)) {
        upsellio_gsc_log("gsc.sync.access_token_error", [
            "message" => $access_token->get_error_message(),
        ], $trace_id);
        return $access_token;
    }

    $property = (string) ($credentials["property"] ?? "");
    if ($property === "") {
        return new WP_Error("upsellio_gsc_missing_property", "Uzupełnij property URL (np. https://twojadomena.pl/ lub sc-domain:twojadomena.pl).");
    }

    $token_transient_key = upsellio_gsc_access_token_transient_key($credentials);
    $sites_response = null;
    for ($attempt = 0; $attempt < 2; $attempt++) {
        upsellio_gsc_log("gsc.sites_list.request", [
            "attempt" => $attempt + 1,
            "endpoint" => "https://searchconsole.googleapis.com/webmasters/v3/sites",
            "token_preview" => upsellio_gsc_mask_value($access_token),
        ], $trace_id);
        $sites_response = wp_remote_get("https://searchconsole.googleapis.com/webmasters/v3/sites", [
            "timeout" => 25,
            "headers" => [
                "Authorization" => "Bearer " . $access_token,
                "Content-Type" => "application/json",
            ],
        ]);
        if (is_wp_error($sites_response)) {
            return $sites_response;
        }

        $sites_status = (int) wp_remote_retrieve_response_code($sites_response);
        $sites_raw_body = (string) wp_remote_retrieve_body($sites_response);
        $sites_decoded = json_decode($sites_raw_body, true);
        upsellio_gsc_log("gsc.sites_list.response", [
            "attempt" => $attempt + 1,
            "status" => $sites_status,
            "body" => is_array($sites_decoded) ? $sites_decoded : upsellio_gsc_truncate($sites_raw_body),
        ], $trace_id);

        if ($sites_status !== 401) {
            break;
        }

        delete_transient($token_transient_key);
        upsellio_gsc_log("gsc.sites_list.retry_after_401", [
            "attempt" => $attempt + 1,
            "transient_key_deleted" => $token_transient_key,
        ], $trace_id);
        $access_token = upsellio_gsc_get_access_token($credentials, $trace_id);
        if (is_wp_error($access_token)) {
            return $access_token;
        }
    }

    $sites_status = (int) wp_remote_retrieve_response_code($sites_response);
    $sites_body = json_decode((string) wp_remote_retrieve_body($sites_response), true);
    if ($sites_status >= 400) {
        $error_message = upsellio_gsc_extract_error_message($sites_body, "Błąd autoryzacji Google Search Console.");
        upsellio_gsc_log("gsc.sites_list.error", [
            "status" => $sites_status,
            "message" => $error_message,
        ], $trace_id);
        return new WP_Error("upsellio_gsc_sites_error", $error_message);
    }

    $site_entries = is_array($sites_body) && isset($sites_body["siteEntry"]) && is_array($sites_body["siteEntry"]) ? $sites_body["siteEntry"] : [];
    $site_urls = array_map(function ($entry) {
        return (string) ($entry["siteUrl"] ?? "");
    }, $site_entries);
    upsellio_gsc_log("gsc.sites_list.property_check", [
        "property" => $property,
        "available_sites_count" => count($site_urls),
        "available_sites" => array_slice($site_urls, 0, 50),
    ], $trace_id);

    if (!upsellio_gsc_has_property_access($property, $site_entries)) {
        upsellio_gsc_log("gsc.sites_list.property_access_denied", [
            "property" => $property,
        ], $trace_id);
        return new WP_Error(
            "upsellio_gsc_property_access_error",
            "Konto OAuth nie ma dostępu do podanego GSC Property. Użyj dokładnie wartości z Search Console (np. sc-domain:twojadomena.pl lub pełny URL property)."
        );
    }

    $end_date = wp_date("Y-m-d");
    $start_date = wp_date("Y-m-d", strtotime("-" . max(2, (int) $days) . " days"));
    $endpoint = "https://searchconsole.googleapis.com/webmasters/v3/sites/" . rawurlencode($property) . "/searchAnalytics/query";

    $rows = [];
    $start_row = 0;
    $row_limit = 25000;
    for ($page = 0; $page < 4; $page++) {
        $request_body = [
            "startDate" => $start_date,
            "endDate" => $end_date,
            "dimensions" => ["query", "page", "date"],
            "rowLimit" => $row_limit,
            "startRow" => $start_row,
            "dataState" => "final",
        ];
        $response = null;
        for ($attempt = 0; $attempt < 2; $attempt++) {
            upsellio_gsc_log("gsc.search_analytics.request", [
                "page" => $page + 1,
                "attempt" => $attempt + 1,
                "endpoint" => $endpoint,
                "request_body" => $request_body,
                "token_preview" => upsellio_gsc_mask_value($access_token),
            ], $trace_id);
            $response = wp_remote_post($endpoint, [
                "timeout" => 35,
                "headers" => [
                    "Authorization" => "Bearer " . $access_token,
                    "Content-Type" => "application/json",
                ],
                "body" => wp_json_encode($request_body),
            ]);
            if (is_wp_error($response)) {
                return $response;
            }

            $status = (int) wp_remote_retrieve_response_code($response);
            $raw_body = (string) wp_remote_retrieve_body($response);
            $decoded_body = json_decode($raw_body, true);
            upsellio_gsc_log("gsc.search_analytics.response", [
                "page" => $page + 1,
                "attempt" => $attempt + 1,
                "status" => $status,
                "body" => is_array($decoded_body) ? $decoded_body : upsellio_gsc_truncate($raw_body),
            ], $trace_id);
            if ($status !== 401) {
                break;
            }

            delete_transient($token_transient_key);
            upsellio_gsc_log("gsc.search_analytics.retry_after_401", [
                "page" => $page + 1,
                "attempt" => $attempt + 1,
                "transient_key_deleted" => $token_transient_key,
            ], $trace_id);
            $access_token = upsellio_gsc_get_access_token($credentials, $trace_id);
            if (is_wp_error($access_token)) {
                return $access_token;
            }
        }

        $status = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode((string) wp_remote_retrieve_body($response), true);
        if ($status >= 400) {
            $error_message = upsellio_gsc_extract_error_message($body, "Błąd API Google Search Console.");
            upsellio_gsc_log("gsc.search_analytics.error", [
                "page" => $page + 1,
                "status" => $status,
                "message" => $error_message,
            ], $trace_id);
            return new WP_Error("upsellio_gsc_api_error", $error_message);
        }

        $batch_rows = is_array($body) && isset($body["rows"]) && is_array($body["rows"]) ? $body["rows"] : [];
        upsellio_gsc_log("gsc.search_analytics.batch_processed", [
            "page" => $page + 1,
            "batch_rows" => count($batch_rows),
            "aggregated_rows" => count($rows) + count($batch_rows),
            "start_row" => $start_row,
        ], $trace_id);
        foreach ($batch_rows as $row) {
            $keys = isset($row["keys"]) && is_array($row["keys"]) ? $row["keys"] : [];
            $keyword = sanitize_text_field((string) ($keys[0] ?? ""));
            $page_url = esc_url_raw((string) ($keys[1] ?? ""));
            $date_key = sanitize_text_field((string) ($keys[2] ?? ""));
            if ($keyword === "" || $page_url === "" || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $date_key)) {
                continue;
            }

            $clicks = (int) round((float) ($row["clicks"] ?? 0));
            $impressions = (int) round((float) ($row["impressions"] ?? 0));
            $ctr = (float) ($row["ctr"] ?? 0) * 100;
            $position = (float) ($row["position"] ?? 0);

            $rows[] = [
                "keyword" => $keyword,
                "url" => $page_url,
                "position" => max(1, round($position, 2)),
                "impressions" => max(0, $impressions),
                "clicks" => max(0, $clicks),
                "ctr" => round(max(0, $ctr), 2),
                "date" => $date_key,
            ];
        }

        if (count($batch_rows) < $row_limit) {
            break;
        }
        $start_row += $row_limit;
    }

    $final_rows = array_slice($rows, 0, 100000);
    upsellio_gsc_log("gsc.sync.finished", [
        "total_rows" => count($final_rows),
    ], $trace_id);

    return $final_rows;
}

function upsellio_handle_gsc_sync_submit()
{
    if (!is_admin() || !current_user_can("edit_posts")) {
        return;
    }

    if (!isset($_POST["upsellio_gsc_sync_submit"])) {
        return;
    }

    check_admin_referer("upsellio_gsc_sync_action", "upsellio_gsc_sync_nonce");

    $client_id = isset($_POST["gsc_client_id"]) ? wp_unslash($_POST["gsc_client_id"]) : "";
    $client_secret = isset($_POST["gsc_client_secret"]) ? wp_unslash($_POST["gsc_client_secret"]) : "";
    $refresh_token = isset($_POST["gsc_refresh_token"]) ? wp_unslash($_POST["gsc_refresh_token"]) : "";
    $property = isset($_POST["gsc_property"]) ? wp_unslash($_POST["gsc_property"]) : "";
    $sync_days = isset($_POST["gsc_sync_days"]) ? (int) $_POST["gsc_sync_days"] : 30;
    $sync_days = in_array($sync_days, [7, 14, 30, 60, 90], true) ? $sync_days : 30;
    $trace_id = upsellio_gsc_debug_trace_id();

    upsellio_gsc_log("gsc.sync.form_submit", [
        "trace_id" => $trace_id,
        "sync_days" => $sync_days,
        "property_input" => (string) $property,
        "client_id_input" => (string) $client_id,
    ], $trace_id);

    upsellio_save_gsc_credentials($client_id, $client_secret, $refresh_token, $property);
    $credentials = upsellio_get_gsc_credentials();
    $rows = upsellio_gsc_fetch_rows($credentials, $sync_days, $trace_id);

    if (is_wp_error($rows)) {
        upsellio_gsc_log("gsc.sync.failed", [
            "trace_id" => $trace_id,
            "error_message" => $rows->get_error_message(),
        ], $trace_id);
        wp_safe_redirect(
            upsellio_site_analytics_admin_url([
                "upsellio_gsc_error" => rawurlencode($rows->get_error_message()),
                "upsellio_gsc_trace_id" => rawurlencode($trace_id),
            ])
        );
        exit;
    }

    update_option("upsellio_keyword_metrics_rows", $rows, false);
    update_option("upsellio_keyword_metrics_source", "gsc_live", false);
    update_option("upsellio_keyword_metrics_last_sync", wp_date("Y-m-d H:i:s"), false);
    upsellio_gsc_log("gsc.sync.success", [
        "trace_id" => $trace_id,
        "rows_count" => count($rows),
    ], $trace_id);

    wp_safe_redirect(
        upsellio_site_analytics_admin_url([
            "upsellio_gsc_synced" => (string) count($rows),
            "upsellio_gsc_trace_id" => rawurlencode($trace_id),
        ])
    );
    exit;
}
add_action("admin_init", "upsellio_handle_gsc_sync_submit");

function upsellio_handle_gsc_logs_clear_submit()
{
    if (!is_admin() || !current_user_can("edit_posts")) {
        return;
    }

    if (!isset($_POST["upsellio_gsc_logs_clear_submit"])) {
        return;
    }

    check_admin_referer("upsellio_gsc_logs_clear_action", "upsellio_gsc_logs_clear_nonce");
    delete_option(upsellio_gsc_debug_logs_option_key());

    wp_safe_redirect(
        upsellio_site_analytics_admin_url([
            "upsellio_gsc_logs_cleared" => "1",
        ])
    );
    exit;
}
add_action("admin_init", "upsellio_handle_gsc_logs_clear_submit");

/**
 * Opcja: wymuszenie redirect URI (musi być tym samym co w Google Cloud + ten sam host co strona).
 */
function upsellio_google_oauth_redirect_uri_override_option_key(): string
{
    return "upsellio_google_oauth_redirect_uri_override";
}

/**
 * @deprecated Nie używany do wyboru URI od v2 — pozostawiony dla spójności nazw w bazie u starszych instalacji.
 */
function upsellio_google_oauth_prefer_admin_redirect_option_key(): string
{
    return "upsellio_google_oauth_prefer_admin_redirect";
}

/**
 * Gdy "1" — redirect_uri = REST `/wp-json/upsellio/v1/google-oauth-callback`.
 * Gdy "0" lub brak wpisu (domyślnie) — `admin.php?page=…` (zwykle zgodne z wpisem w Google Cloud).
 *
 * Sufiks _v2: reset domyślny po wcześniejszym błędnym zapisie v1 (checkbox REST); stary klucz jest ignorowany.
 */
function upsellio_google_oauth_use_rest_callback_option_key(): string
{
    return "upsellio_google_oauth_use_rest_callback_v2";
}

function upsellio_google_oauth_use_rest_callback(): bool
{
    return (string) get_option(upsellio_google_oauth_use_rest_callback_option_key(), "0") === "1";
}

function upsellio_google_oauth_normalize_redirect_uri_string(string $url): string
{
    $url = trim($url);
    if ($url === "") {
        return "";
    }

    return untrailingslashit(esc_url_raw($url));
}

/**
 * Dozwolone tylko URIs z hostem zgodnym z tym, co WordPress uważa za domenę witryny (bezpieczeństwo).
 */
function upsellio_google_oauth_redirect_uri_is_allowed_host(string $url): bool
{
    $p = wp_parse_url($url);
    $host = isset($p["host"]) ? strtolower((string) $p["host"]) : "";
    if ($host === "") {
        return false;
    }

    $bases = [home_url(), site_url(), admin_url()];
    if (function_exists("network_home_url")) {
        $bases[] = network_home_url();
        $bases[] = network_site_url();
    }

    foreach ($bases as $b) {
        $bh = wp_parse_url($b, PHP_URL_HOST);
        if ($bh && strtolower((string) $bh) === $host) {
            return true;
        }
    }

    return false;
}

/**
 * Domyślny redirect OAuth (bez filtra) — krótki URL REST; Google łatwiej wpisać niż admin.php?page=…
 */
function upsellio_google_oauth_rest_redirect_uri_default(): string
{
    return upsellio_google_oauth_normalize_redirect_uri_string((string) rest_url("upsellio/v1/google-oauth-callback"));
}

function upsellio_google_oauth_admin_redirect_uri_default(): string
{
    return upsellio_google_oauth_normalize_redirect_uri_string((string) upsellio_site_analytics_admin_url());
}

/**
 * Redirect URI rejestrowany w Google Cloud Console (OAuth client typ „Web application”).
 * Musi być 1:1 taki sam jak w „Authorized redirect URIs” — inaczej Google: redirect_uri_mismatch.
 *
 * Domyślnie: `admin.php?page=…` — opcja „Używaj REST” w panelu przełącza na `/wp-json/upsellio/v1/google-oauth-callback`.
 * Opcja `upsellio_google_oauth_redirect_uri_override`: wklej dokładnie URI z Google (ten sam host).
 * Filtr: `upsellio_google_oauth_redirect_uri` — np. przy proxy lub niestandardowym URL.
 */
function upsellio_google_oauth_redirect_uri()
{
    $override = upsellio_google_oauth_normalize_redirect_uri_string(
        (string) get_option(upsellio_google_oauth_redirect_uri_override_option_key(), "")
    );
    if ($override !== "" && filter_var($override, FILTER_VALIDATE_URL) && upsellio_google_oauth_redirect_uri_is_allowed_host($override)) {
        return $override;
    }

    $use_rest = upsellio_google_oauth_use_rest_callback();
    $uri = $use_rest
        ? upsellio_google_oauth_rest_redirect_uri_default()
        : upsellio_google_oauth_admin_redirect_uri_default();

    return upsellio_google_oauth_normalize_redirect_uri_string((string) apply_filters("upsellio_google_oauth_redirect_uri", $uri));
}

/**
 * Warianty redirect_uri (REST + legacy admin + http/https) — dodaj w Google te, które pasują do Twojej domeny.
 *
 * @return list<string>
 */
function upsellio_google_oauth_redirect_uri_variants(): array
{
    $primary = upsellio_google_oauth_redirect_uri();
    $saved_override = upsellio_google_oauth_normalize_redirect_uri_string(
        (string) get_option(upsellio_google_oauth_redirect_uri_override_option_key(), "")
    );
    $candidates = array_unique(
        array_filter(
            [
                $primary,
                $saved_override !== "" ? $saved_override : null,
                upsellio_google_oauth_rest_redirect_uri_default(),
                upsellio_google_oauth_admin_redirect_uri_default(),
            ],
            static function ($u) {
                return is_string($u) && $u !== "";
            }
        )
    );

    $variants = [];
    foreach ($candidates as $u) {
        $u = upsellio_google_oauth_normalize_redirect_uri_string($u);
        if ($u === "") {
            continue;
        }
        $variants[] = $u;
        if (strpos($u, "https://") === 0) {
            $variants[] = preg_replace('#^https://#', "http://", $u, 1);
        } elseif (strpos($u, "http://") === 0) {
            $variants[] = preg_replace('#^http://#', "https://", $u, 1);
        }
    }

    $variants = array_values(array_unique(array_filter($variants)));

    /**
     * @param list<string> $variants
     * @param string       $primary
     */
    $out = apply_filters("upsellio_google_oauth_redirect_uri_variants", $variants, $primary);

    return is_array($out) ? array_values(array_unique(array_filter(array_map("strval", $out)))) : $variants;
}

/**
 * OAuth wraca na REST; przekazujemy parametry do istniejącego handlera admin (wymiana kodu na token).
 */
function upsellio_register_google_oauth_rest_callback(): void
{
    register_rest_route("upsellio/v1", "/google-oauth-callback", [
        "methods" => "GET",
        "callback" => "upsellio_handle_google_oauth_rest_callback",
        "permission_callback" => "__return_true",
    ]);
}
add_action("rest_api_init", "upsellio_register_google_oauth_rest_callback");

function upsellio_handle_google_oauth_rest_callback(WP_REST_Request $request)
{
    $args = array_filter(
        [
            "page" => upsellio_site_analytics_page_slug(),
            "code" => $request->get_param("code"),
            "state" => $request->get_param("state"),
            "error" => $request->get_param("error"),
            "error_description" => $request->get_param("error_description"),
        ],
        static function ($v) {
            return $v !== null && $v !== "";
        }
    );

    $target = add_query_arg($args, admin_url("admin.php"));
    if (!is_user_logged_in()) {
        wp_safe_redirect(wp_login_url($target));
        exit;
    }

    wp_safe_redirect($target);
    exit;
}

function upsellio_google_oauth_scope_string()
{
    $default = [
        "https://www.googleapis.com/auth/webmasters.readonly",
        "https://www.googleapis.com/auth/analytics.readonly",
    ];
    if ((string) get_option("upsellio_google_ads_include_scope", "0") === "1") {
        $default[] = "https://www.googleapis.com/auth/adwords";
    }
    $scopes = apply_filters("upsellio_google_oauth_scopes", $default);
    if (!is_array($scopes)) {
        $scopes = $default;
    }
    $scopes = array_values(array_unique(array_filter(array_map("strval", $scopes))));

    return implode(" ", $scopes);
}

function upsellio_google_ads_config_option_key(): string
{
    return "upsellio_google_ads_config";
}

/**
 * @return array{developer_token:string,customer_id:string,login_customer_id:string}
 */
function upsellio_google_ads_get_settings(): array
{
    $raw = get_option(upsellio_google_ads_config_option_key(), []);
    if (!is_array($raw)) {
        $raw = [];
    }

    return [
        "developer_token" => trim((string) ($raw["developer_token"] ?? "")),
        "customer_id" => upsellio_google_ads_normalize_customer_id((string) ($raw["customer_id"] ?? "")),
        "login_customer_id" => upsellio_google_ads_normalize_customer_id((string) ($raw["login_customer_id"] ?? "")),
    ];
}

function upsellio_google_ads_normalize_customer_id(string $value): string
{
    $digits = preg_replace("/\D+/", "", $value);

    return $digits;
}

/**
 * Wersja Google Ads API (ścieżka REST), np. v17.
 */
function upsellio_google_ads_api_version(): string
{
    $v = apply_filters("upsellio_google_ads_api_version", "v17");
    $v = trim((string) $v);
    if ($v === "" || !preg_match('/^v\d+$/', $v)) {
        return "v17";
    }

    return $v;
}

function upsellio_google_ads_rest_base_url(): string
{
    return "https://googleads.googleapis.com/" . upsellio_google_ads_api_version();
}

/**
 * Nagłówki wymagane przez Google Ads API (REST).
 *
 * @return array<string, string>
 */
function upsellio_google_ads_request_headers(string $access_token): array
{
    $access_token = trim($access_token);
    $cfg = upsellio_google_ads_get_settings();
    $headers = [
        "Authorization" => "Bearer " . $access_token,
        "developer-token" => $cfg["developer_token"],
    ];
    if ($cfg["login_customer_id"] !== "") {
        $headers["login-customer-id"] = $cfg["login_customer_id"];
    }

    return $headers;
}

/**
 * Minimalna walidacja gotowości do wywołań API (Bearer + developer token + CID + zakres adwords w cache).
 */
function upsellio_google_ads_api_ready(): bool
{
    $c = upsellio_get_gsc_credentials();
    if (trim((string) ($c["refresh_token"] ?? "")) === "") {
        return false;
    }
    $cfg = upsellio_google_ads_get_settings();
    if ($cfg["developer_token"] === "" || $cfg["customer_id"] === "") {
        return false;
    }
    $snap = upsellio_google_get_permission_snapshot();

    return !empty($snap["has_google_ads"]);
}

/**
 * Lista kont dostępnych dla tokena (diagnostyka połączenia).
 *
 * @return array|\WP_Error
 */
function upsellio_google_ads_list_accessible_customers(string $trace_id = "")
{
    $cfg = upsellio_google_ads_get_settings();
    if ($cfg["developer_token"] === "") {
        return new WP_Error("upsellio_gads_no_dev_token", "Uzupełnij Developer token w ustawieniach Google Ads.");
    }

    $creds = upsellio_get_gsc_credentials();
    $token = upsellio_gsc_get_access_token($creds, $trace_id);
    if (is_wp_error($token)) {
        return $token;
    }

    $url = upsellio_google_ads_rest_base_url() . "/customers:listAccessibleCustomers";
    $headers = upsellio_google_ads_request_headers((string) $token);
    $response = wp_remote_get($url, [
        "timeout" => 25,
        "headers" => $headers,
    ]);

    if (is_wp_error($response)) {
        return $response;
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    $body_raw = (string) wp_remote_retrieve_body($response);
    $body = json_decode($body_raw, true);
    if ($code >= 400) {
        $msg = upsellio_gsc_extract_error_message(is_array($body) ? $body : [], "Google Ads API HTTP " . $code);

        return new WP_Error("upsellio_gads_http", $msg);
    }

    return is_array($body) ? $body : [];
}

/**
 * Opcja z cache’m zakresów OAuth (jak Rank Math: tokeninfo po dostępie).
 */
function upsellio_google_oauth_permissions_option_key(): string
{
    return "upsellio_google_oauth_permissions";
}

/**
 * Normalizacja listy zakresów z pola „scope” (tokeninfo).
 *
 * @return array<int, string>
 */
function upsellio_google_normalize_scope_fragments(string $scope_raw): array
{
    $scope_raw = trim($scope_raw);
    if ($scope_raw === "") {
        return [];
    }
    $parts = preg_split("/\s+/", $scope_raw);
    $out = [];
    foreach ($parts as $p) {
        $p = trim((string) $p);
        if ($p === "") {
            continue;
        }
        $p = str_replace("https://www.googleapis.com/auth/", "", $p);
        $out[] = $p;
    }

    return array_values(array_unique($out));
}

/**
 * Zapisuje skrócone nazwy zakresów po tokeninfo (Rank Math robi to samo w Permissions::fetch).
 *
 * @return array<int, string>|null
 */
function upsellio_google_fetch_and_store_permissions_from_access_token(string $access_token)
{
    $access_token = trim($access_token);
    if ($access_token === "") {
        delete_option(upsellio_google_oauth_permissions_option_key());

        return null;
    }

    $url = "https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=" . rawurlencode($access_token);
    $response = wp_remote_get($url, ["timeout" => 12]);
    if (is_wp_error($response)) {
        upsellio_gsc_log("google.permissions.tokeninfo_error", ["message" => $response->get_error_message()], upsellio_gsc_debug_trace_id());

        return null;
    }
    if ((int) wp_remote_retrieve_response_code($response) !== 200) {
        upsellio_gsc_log("google.permissions.tokeninfo_http", ["status" => (int) wp_remote_retrieve_response_code($response)], upsellio_gsc_debug_trace_id());

        return null;
    }

    $body = json_decode((string) wp_remote_retrieve_body($response), true);
    $scope_raw = is_array($body) ? (string) ($body["scope"] ?? "") : "";
    $scopes = upsellio_google_normalize_scope_fragments($scope_raw);
    update_option(
        upsellio_google_oauth_permissions_option_key(),
        [
            "scopes" => $scopes,
            "checked_at" => current_time("mysql"),
        ],
        false
    );

    return $scopes;
}

/**
 * @return array{scopes: array<int, string>, checked_at: string, has_console: bool, has_analytics: bool, has_adsense: bool, has_google_ads: bool}
 */
function upsellio_google_get_permission_snapshot(): array
{
    $opt = get_option(upsellio_google_oauth_permissions_option_key(), []);
    if (!is_array($opt)) {
        $opt = [];
    }
    $scopes = isset($opt["scopes"]) && is_array($opt["scopes"]) ? $opt["scopes"] : [];
    $scopes = array_map("strval", $scopes);

    $has_console = false;
    foreach (["webmasters", "webmasters.readonly"] as $s) {
        if (in_array($s, $scopes, true)) {
            $has_console = true;
            break;
        }
    }

    $has_analytics = false;
    foreach ($scopes as $s) {
        if (
            $s === "analytics.readonly"
            || $s === "analytics.edit"
            || $s === "analytics.provision"
            || strpos($s, "analytics") === 0
        ) {
            $has_analytics = true;
            break;
        }
    }

    $has_adsense = in_array("adsense.readonly", $scopes, true);
    $has_google_ads = in_array("adwords", $scopes, true);

    return [
        "scopes" => $scopes,
        "checked_at" => (string) ($opt["checked_at"] ?? ""),
        "has_console" => $has_console,
        "has_analytics" => $has_analytics,
        "has_adsense" => $has_adsense,
        "has_google_ads" => $has_google_ads,
    ];
}

function upsellio_google_oauth_transient_key($user_id)
{
    return "upsellio_goauth_" . (int) $user_id;
}

/**
 * @return array{state:string,gsc_property:string,ga4_property_id:string}|null
 */
function upsellio_google_oauth_get_pending($user_id)
{
    $raw = get_transient(upsellio_google_oauth_transient_key($user_id));
    if (!is_array($raw) || !isset($raw["state"], $raw["gsc_property"], $raw["ga4_property_id"])) {
        return null;
    }

    return [
        "state" => (string) $raw["state"],
        "gsc_property" => (string) $raw["gsc_property"],
        "ga4_property_id" => (string) $raw["ga4_property_id"],
    ];
}

function upsellio_google_oauth_handle_callback()
{
    if (!is_admin() || !current_user_can("edit_posts")) {
        return;
    }
    if (!isset($_GET["page"]) || (string) wp_unslash($_GET["page"]) !== upsellio_site_analytics_page_slug()) {
        return;
    }

    $uid = get_current_user_id();
    if ($uid <= 0) {
        return;
    }

    if (isset($_GET["error"])) {
        $pending = upsellio_google_oauth_get_pending($uid);
        if ($pending !== null && isset($_GET["state"]) && hash_equals($pending["state"], (string) wp_unslash($_GET["state"]))) {
            delete_transient(upsellio_google_oauth_transient_key($uid));
        }
        $err = sanitize_text_field((string) wp_unslash($_GET["error"]));
        $desc = isset($_GET["error_description"]) ? sanitize_text_field((string) wp_unslash($_GET["error_description"])) : "";
        $msg = $desc !== "" ? "{$err}: {$desc}" : $err;
        wp_safe_redirect(
            upsellio_site_analytics_admin_url([
                "upsellio_google_oauth_error" => rawurlencode($msg),
            ])
        );
        exit;
    }

    if (!isset($_GET["code"], $_GET["state"])) {
        return;
    }

    $code = (string) wp_unslash($_GET["code"]);
    $state_in = (string) wp_unslash($_GET["state"]);
    $pending = upsellio_google_oauth_get_pending($uid);
    if ($pending === null || !hash_equals($pending["state"], $state_in)) {
        wp_safe_redirect(
            upsellio_site_analytics_admin_url([
                "upsellio_google_oauth_error" => rawurlencode("Nieprawidłowy stan OAuth (odśwież stronę i spróbuj ponownie)."),
            ])
        );
        exit;
    }

    delete_transient(upsellio_google_oauth_transient_key($uid));

    $creds = upsellio_get_gsc_credentials();
    $client_id = (string) ($creds["client_id"] ?? "");
    $client_secret = (string) ($creds["client_secret"] ?? "");
    if ($client_id === "" || $client_secret === "") {
        wp_safe_redirect(
            upsellio_site_analytics_admin_url([
                "upsellio_google_oauth_error" => rawurlencode("Brak Client ID / Secret — uzupełnij je przed autoryzacją."),
            ])
        );
        exit;
    }

    $trace_id = upsellio_gsc_debug_trace_id();
    upsellio_gsc_log("google.oauth.code_exchange.started", ["trace_id" => $trace_id], $trace_id);

    $response = wp_remote_post("https://oauth2.googleapis.com/token", [
        "timeout" => 25,
        "body" => [
            "code" => $code,
            "client_id" => $client_id,
            "client_secret" => $client_secret,
            "redirect_uri" => upsellio_google_oauth_redirect_uri(),
            "grant_type" => "authorization_code",
        ],
    ]);

    if (is_wp_error($response)) {
        upsellio_gsc_log("google.oauth.code_exchange.wp_error", ["message" => $response->get_error_message()], $trace_id);
        wp_safe_redirect(
            upsellio_site_analytics_admin_url([
                "upsellio_google_oauth_error" => rawurlencode($response->get_error_message()),
            ])
        );
        exit;
    }

    $status = (int) wp_remote_retrieve_response_code($response);
    $raw_body = (string) wp_remote_retrieve_body($response);
    $body = json_decode($raw_body, true);
    upsellio_gsc_log("google.oauth.code_exchange.response", [
        "status" => $status,
        "body" => is_array($body) ? upsellio_gsc_redact_sensitive_fields($body) : upsellio_gsc_truncate($raw_body),
    ], $trace_id);

    if ($status >= 400) {
        $msg = upsellio_gsc_extract_error_message(is_array($body) ? $body : [], "Wymiana kodu OAuth nie powiodła się.");
        wp_safe_redirect(
            upsellio_site_analytics_admin_url([
                "upsellio_google_oauth_error" => rawurlencode($msg),
            ])
        );
        exit;
    }

    $new_refresh = is_array($body) ? trim((string) ($body["refresh_token"] ?? "")) : "";
    $existing_refresh = trim((string) ($creds["refresh_token"] ?? ""));
    $refresh_to_store = $new_refresh !== "" ? $new_refresh : $existing_refresh;
    if ($refresh_to_store === "") {
        wp_safe_redirect(
            upsellio_site_analytics_admin_url([
                "upsellio_google_oauth_error" => rawurlencode("Google nie zwrócił refresh tokena. Usuń powiązanie aplikacji w ustawieniach konta Google i spróbuj ponownie z prompt=consent (użyj ponownie przycisku autoryzacji)."),
            ])
        );
        exit;
    }

    $gsc_property = $pending["gsc_property"] !== ""
        ? sanitize_text_field($pending["gsc_property"])
        : (string) ($creds["property"] ?? "");
    upsellio_save_gsc_credentials($client_id, $client_secret, $refresh_to_store, $gsc_property);

    if ($pending["ga4_property_id"] !== "") {
        upsellio_save_ga4_property_id($pending["ga4_property_id"]);
    }

    $access_from_body = is_array($body) ? trim((string) ($body["access_token"] ?? "")) : "";
    if ($access_from_body !== "") {
        upsellio_google_fetch_and_store_permissions_from_access_token($access_from_body);
    }

    upsellio_gsc_log("google.oauth.code_exchange.success", ["trace_id" => $trace_id], $trace_id);

    wp_safe_redirect(
        upsellio_site_analytics_admin_url([
            "upsellio_google_connected" => "1",
        ])
    );
    exit;
}
add_action("admin_init", "upsellio_google_oauth_handle_callback", 1);

function upsellio_google_oauth_handle_start()
{
    if (!is_admin() || !current_user_can("edit_posts")) {
        return;
    }

    if (!isset($_POST["upsellio_google_oauth_start"])) {
        return;
    }

    check_admin_referer("upsellio_google_oauth_start_action", "upsellio_google_oauth_start_nonce");

    if (function_exists("upsellio_google_managed_oauth_try_handle_connect_post")) {
        upsellio_google_managed_oauth_try_handle_connect_post();
    }

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

    $client_id = isset($_POST["g_oauth_client_id"]) ? wp_unslash($_POST["g_oauth_client_id"]) : "";
    $client_secret = isset($_POST["g_oauth_client_secret"]) ? wp_unslash($_POST["g_oauth_client_secret"]) : "";
    $gsc_property_in = isset($_POST["g_oauth_gsc_property"]) ? wp_unslash($_POST["g_oauth_gsc_property"]) : "";
    $ga4_id_in = isset($_POST["g_oauth_ga4_property_id"]) ? wp_unslash($_POST["g_oauth_ga4_property_id"]) : "";

    $existing = upsellio_get_gsc_credentials();
    if (trim((string) $client_id) === "") {
        $client_id = (string) ($existing["client_id"] ?? "");
    }
    if (trim((string) $client_secret) === "") {
        $client_secret = (string) ($existing["client_secret"] ?? "");
    }
    $gsc_property = trim((string) $gsc_property_in) !== ""
        ? sanitize_text_field(trim((string) $gsc_property_in))
        : (string) ($existing["property"] ?? "");

    upsellio_save_gsc_credentials(
        $client_id,
        $client_secret,
        (string) ($existing["refresh_token"] ?? ""),
        $gsc_property
    );

    $saved = upsellio_get_gsc_credentials();
    if ($saved["client_id"] === "" || $saved["client_secret"] === "") {
        wp_safe_redirect(
            upsellio_site_analytics_admin_url([
                "upsellio_google_oauth_error" => rawurlencode("Uzupełnij Client ID i Client Secret z Google Cloud Console."),
            ])
        );
        exit;
    }

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
        ],
        15 * MINUTE_IN_SECONDS
    );

    $redirect_uri = upsellio_google_oauth_redirect_uri();

    $auth_url = add_query_arg(
        [
            "client_id" => $saved["client_id"],
            "redirect_uri" => $redirect_uri,
            "response_type" => "code",
            "scope" => upsellio_google_oauth_scope_string(),
            "access_type" => "offline",
            "prompt" => "consent",
            "include_granted_scopes" => "true",
            "state" => $state,
        ],
        "https://accounts.google.com/o/oauth2/v2/auth"
    );

    upsellio_gsc_log("google.oauth.redirect", [
        "user_id" => $uid,
        "redirect_uri" => $redirect_uri,
        "oauth_redirect_mode" => upsellio_google_oauth_use_rest_callback() ? "rest" : "admin",
        "redirect_uri_variants_hint" => upsellio_google_oauth_redirect_uri_variants(),
        "oauth_client_id" => $saved["client_id"],
    ], upsellio_gsc_debug_trace_id());

    // Nie używaj wp_safe_redirect — blokuje host accounts.google.com i wpada w fallback admin_url() (kokpit).
    wp_redirect(esc_url_raw($auth_url));
    exit;
}
add_action("admin_init", "upsellio_google_oauth_handle_start", 2);

function upsellio_google_oauth_handle_disconnect()
{
    if (!is_admin() || !current_user_can("edit_posts")) {
        return;
    }

    if (!isset($_POST["upsellio_google_oauth_disconnect"])) {
        return;
    }

    check_admin_referer("upsellio_google_oauth_disconnect_action", "upsellio_google_oauth_disconnect_nonce");

    $c = upsellio_get_gsc_credentials();
    upsellio_save_gsc_credentials(
        (string) ($c["client_id"] ?? ""),
        (string) ($c["client_secret"] ?? ""),
        "",
        (string) ($c["property"] ?? "")
    );
    delete_option(upsellio_google_oauth_permissions_option_key());

    wp_safe_redirect(
        upsellio_site_analytics_admin_url([
            "upsellio_google_disconnected" => "1",
        ])
    );
    exit;
}
add_action("admin_init", "upsellio_google_oauth_handle_disconnect", 2);

function upsellio_google_handle_permissions_refresh(): void
{
    if (!is_admin() || !current_user_can("edit_posts")) {
        return;
    }
    if (!isset($_POST["upsellio_google_permissions_refresh"])) {
        return;
    }
    check_admin_referer("upsellio_google_permissions_refresh_action", "upsellio_google_permissions_refresh_nonce");

    $creds = upsellio_get_gsc_credentials();
    $at = upsellio_gsc_get_access_token($creds, upsellio_gsc_debug_trace_id());
    if (!is_wp_error($at) && is_string($at) && $at !== "") {
        upsellio_google_fetch_and_store_permissions_from_access_token($at);
    }

    wp_safe_redirect(
        upsellio_site_analytics_admin_url([
            "upsellio_google_perm_refreshed" => "1",
        ])
    );
    exit;
}
add_action("admin_init", "upsellio_google_handle_permissions_refresh", 2);

function upsellio_google_handle_ads_scope_pref_save(): void
{
    if (!is_admin() || !current_user_can("edit_posts")) {
        return;
    }
    if (!isset($_POST["upsellio_google_ads_scope_save"])) {
        return;
    }
    check_admin_referer("upsellio_google_ads_scope_action", "upsellio_google_ads_scope_nonce");
    $on = isset($_POST["upsellio_google_ads_include_scope"]) && (string) wp_unslash($_POST["upsellio_google_ads_include_scope"]) === "1";
    update_option("upsellio_google_ads_include_scope", $on ? "1" : "0", false);
    wp_safe_redirect(upsellio_site_analytics_admin_url(["upsellio_google_ads_scope_saved" => "1"]));
    exit;
}
add_action("admin_init", "upsellio_google_handle_ads_scope_pref_save", 2);

function upsellio_google_handle_oauth_redirect_mode_save(): void
{
    if (!is_admin() || !current_user_can("edit_posts")) {
        return;
    }
    if (!isset($_POST["upsellio_google_oauth_redirect_mode_save"])) {
        return;
    }
    check_admin_referer("upsellio_google_oauth_redirect_mode_action", "upsellio_google_oauth_redirect_mode_nonce");
    $use_rest_on = isset($_POST["upsellio_google_oauth_use_rest"]) && (string) wp_unslash($_POST["upsellio_google_oauth_use_rest"]) === "1";
    update_option(upsellio_google_oauth_use_rest_callback_option_key(), $use_rest_on ? "1" : "0", false);
    wp_safe_redirect(upsellio_site_analytics_admin_url(["upsellio_google_oauth_redirect_mode_saved" => "1"]));
    exit;
}
add_action("admin_init", "upsellio_google_handle_oauth_redirect_mode_save", 2);

function upsellio_google_ads_handle_settings_save(): void
{
    if (!is_admin() || !current_user_can("edit_posts")) {
        return;
    }
    if (!isset($_POST["upsellio_google_ads_config_save"])) {
        return;
    }
    check_admin_referer("upsellio_google_ads_config_action", "upsellio_google_ads_config_nonce");
    $cfg = [
        "developer_token" => isset($_POST["upsellio_gads_developer_token"])
            ? sanitize_text_field(wp_unslash($_POST["upsellio_gads_developer_token"]))
            : "",
        "customer_id" => upsellio_google_ads_normalize_customer_id(
            isset($_POST["upsellio_gads_customer_id"]) ? (string) wp_unslash($_POST["upsellio_gads_customer_id"]) : ""
        ),
        "login_customer_id" => upsellio_google_ads_normalize_customer_id(
            isset($_POST["upsellio_gads_login_customer_id"]) ? (string) wp_unslash($_POST["upsellio_gads_login_customer_id"]) : ""
        ),
    ];
    update_option(upsellio_google_ads_config_option_key(), $cfg, false);
    wp_safe_redirect(upsellio_site_analytics_admin_url(["upsellio_google_ads_saved" => "1"]));
    exit;
}
add_action("admin_init", "upsellio_google_ads_handle_settings_save", 2);

function upsellio_google_ads_handle_test_connection(): void
{
    if (!is_admin() || !current_user_can("edit_posts")) {
        return;
    }
    if (!isset($_POST["upsellio_google_ads_test_submit"])) {
        return;
    }
    check_admin_referer("upsellio_google_ads_test_action", "upsellio_google_ads_test_nonce");
    $trace_id = upsellio_gsc_debug_trace_id();
    $result = upsellio_google_ads_list_accessible_customers($trace_id);
    if (is_wp_error($result)) {
        set_transient(
            "upsellio_gads_test_err_" . get_current_user_id(),
            $result->get_error_message(),
            120
        );
        wp_safe_redirect(upsellio_site_analytics_admin_url(["upsellio_google_ads_test" => "err"]));
        exit;
    }
    $json = wp_json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    if (!is_string($json)) {
        $json = "{}";
    }
    set_transient("upsellio_gads_test_ok_" . get_current_user_id(), $json, 120);
    wp_safe_redirect(upsellio_site_analytics_admin_url(["upsellio_google_ads_test" => "ok"]));
    exit;
}
add_action("admin_init", "upsellio_google_ads_handle_test_connection", 2);

/**
 * Numeryczne ID właściwości GA4 (Admin → Ustawienia właściwości).
 */
function upsellio_get_ga4_property_id()
{
    $raw = get_option("upsellio_ga4_property_id", "");
    $digits = preg_replace("/\D+/", "", (string) $raw);

    return $digits;
}

function upsellio_save_ga4_property_id($property_id)
{
    $digits = preg_replace("/\D+/", "", (string) $property_id);
    update_option("upsellio_ga4_property_id", $digits, false);
}

/**
 * Opcjonalny osobny OAuth tylko do GA4. Gdy pusty — używane są dane z sekcji GSC (wspólny refresh token musi mieć scope analytics.readonly).
 *
 * @return array{client_id:string,client_secret:string,refresh_token:string}
 */
function upsellio_get_ga4_oauth_override()
{
    $stored = get_option("upsellio_ga4_oauth_credentials", []);
    if (!is_array($stored)) {
        return ["client_id" => "", "client_secret" => "", "refresh_token" => ""];
    }

    return [
        "client_id" => trim((string) ($stored["client_id"] ?? "")),
        "client_secret" => trim((string) ($stored["client_secret"] ?? "")),
        "refresh_token" => trim((string) ($stored["refresh_token"] ?? "")),
    ];
}

function upsellio_save_ga4_oauth_override($client_id, $client_secret, $refresh_token)
{
    $prev = upsellio_get_ga4_oauth_override();
    $payload = [
        "client_id" => upsellio_normalize_oauth_credential($client_id),
        "client_secret" => upsellio_normalize_oauth_credential($client_secret),
        "refresh_token" => upsellio_normalize_oauth_credential($refresh_token),
    ];
    if ($payload["client_id"] === "" && $payload["client_secret"] === "" && $payload["refresh_token"] === "") {
        delete_option("upsellio_ga4_oauth_credentials");
        delete_transient(upsellio_gsc_access_token_transient_key([
            "client_id" => $prev["client_id"],
            "client_secret" => $prev["client_secret"],
            "refresh_token" => $prev["refresh_token"],
        ]));

        return;
    }
    if (
        $prev["client_id"] !== $payload["client_id"] ||
        $prev["client_secret"] !== $payload["client_secret"] ||
        $prev["refresh_token"] !== $payload["refresh_token"]
    ) {
        delete_transient(upsellio_gsc_access_token_transient_key([
            "client_id" => $prev["client_id"],
            "client_secret" => $prev["client_secret"],
            "refresh_token" => $prev["refresh_token"],
        ]));
        delete_transient(upsellio_gsc_access_token_transient_key([
            "client_id" => $payload["client_id"],
            "client_secret" => $payload["client_secret"],
            "refresh_token" => $payload["refresh_token"],
        ]));
    }
    update_option("upsellio_ga4_oauth_credentials", $payload, false);
}

/**
 * Tablica zgodna z upsellio_gsc_get_access_token (pole property ignorowane przy tokenie).
 */
function upsellio_get_oauth_credentials_for_ga4()
{
    $ov = upsellio_get_ga4_oauth_override();
    if ($ov["refresh_token"] !== "" && $ov["client_id"] !== "" && $ov["client_secret"] !== "") {
        return [
            "client_id" => $ov["client_id"],
            "client_secret" => $ov["client_secret"],
            "refresh_token" => $ov["refresh_token"],
            "property" => "",
        ];
    }

    return upsellio_get_gsc_credentials();
}

function upsellio_ga4_sync_days_to_start_relative($sync_days)
{
    $sync_days = in_array((int) $sync_days, [7, 14, 30, 60, 90], true) ? (int) $sync_days : 30;
    $map = [
        7 => "7daysAgo",
        14 => "14daysAgo",
        30 => "30daysAgo",
        60 => "60daysAgo",
        90 => "90daysAgo",
    ];

    return $map[$sync_days] ?? "30daysAgo";
}

/**
 * Pobiera agregaty źródło / medium / kampania z GA4 Data API (OAuth).
 *
 * @return array<int, array<string, mixed>>|WP_Error
 */
function upsellio_ga4_data_api_fetch_aggregates($property_numeric_id, $sync_days, $trace_id = "")
{
    $property_numeric_id = preg_replace("/\D+/", "", (string) $property_numeric_id);
    if ($property_numeric_id === "") {
        return new WP_Error("upsellio_ga4_missing_property", "Uzupełnij numeryczne ID właściwości GA4.");
    }

    $oauth = upsellio_get_oauth_credentials_for_ga4();
    if (
        (string) ($oauth["client_id"] ?? "") === "" ||
        (string) ($oauth["client_secret"] ?? "") === "" ||
        (string) ($oauth["refresh_token"] ?? "") === ""
    ) {
        return new WP_Error(
            "upsellio_ga4_missing_oauth",
            "Brak OAuth: uzupełnij Google Client ID / Secret / Refresh token w sekcji GSC powyżej (z scope analytics.readonly) albo osobne pola OAuth tylko dla GA4."
        );
    }

    $access_token = upsellio_gsc_get_access_token($oauth, $trace_id);
    if (is_wp_error($access_token)) {
        return $access_token;
    }

    $prop_resource = "properties/" . $property_numeric_id;
    $endpoint = "https://analyticsdata.googleapis.com/v1beta/" . $prop_resource . ":runReport";
    $start_rel = upsellio_ga4_sync_days_to_start_relative($sync_days);
    $metric_attempts = [
        [
            ["name" => "sessions"],
            ["name" => "engagedSessions"],
            ["name" => "conversions"],
            ["name" => "totalRevenue"],
        ],
        [
            ["name" => "sessions"],
            ["name" => "engagedSessions"],
        ],
    ];

    $token_key = upsellio_gsc_access_token_transient_key($oauth);
    $decoded = null;
    $status = 0;
    foreach ($metric_attempts as $attempt => $metrics) {
        $body = [
            "dateRanges" => [
                [
                    "startDate" => $start_rel,
                    "endDate" => "yesterday",
                ],
            ],
            "dimensions" => [
                ["name" => "sessionSource"],
                ["name" => "sessionMedium"],
                ["name" => "sessionCampaignName"],
            ],
            "metrics" => $metrics,
            "limit" => 250000,
        ];

        $response = null;
        for ($try = 0; $try < 2; $try++) {
            upsellio_gsc_log("ga4.run_report.request", [
                "attempt_metrics" => $attempt + 1,
                "try" => $try + 1,
                "endpoint" => $endpoint,
            ], $trace_id);
            $response = wp_remote_post($endpoint, [
                "timeout" => 45,
                "headers" => [
                    "Authorization" => "Bearer " . $access_token,
                    "Content-Type" => "application/json",
                ],
                "body" => wp_json_encode($body),
            ]);
            if (is_wp_error($response)) {
                upsellio_gsc_log("ga4.run_report.http_error", ["message" => $response->get_error_message()], $trace_id);

                return $response;
            }
            $status = (int) wp_remote_retrieve_response_code($response);
            $raw = (string) wp_remote_retrieve_body($response);
            $decoded = json_decode($raw, true);
            if ($status === 401) {
                delete_transient($token_key);
                $access_token = upsellio_gsc_get_access_token($oauth, $trace_id);
                if (is_wp_error($access_token)) {
                    return $access_token;
                }
                continue;
            }
            break;
        }

        upsellio_gsc_log("ga4.run_report.response", [
            "attempt_metrics" => $attempt + 1,
            "status" => $status,
            "body" => is_array($decoded) ? upsellio_gsc_redact_sensitive_fields($decoded) : upsellio_gsc_truncate($raw),
        ], $trace_id);

        if ($status < 400) {
            break;
        }
        if ($attempt === count($metric_attempts) - 1) {
            $msg = upsellio_gsc_extract_error_message(is_array($decoded) ? $decoded : [], "Błąd GA4 Data API (HTTP {$status}).");
            return new WP_Error("upsellio_ga4_api_error", $msg);
        }
    }

    if (!is_array($decoded)) {
        return new WP_Error("upsellio_ga4_api_error", "Nieprawidłowa odpowiedź GA4 Data API.");
    }

    $api_rows = isset($decoded["rows"]) && is_array($decoded["rows"]) ? $decoded["rows"] : [];
    $metric_headers = isset($decoded["metricHeaders"]) && is_array($decoded["metricHeaders"]) ? $decoded["metricHeaders"] : [];
    $metric_count = count($metric_headers);
    $sync_date = wp_date("Y-m-d");
    $out = [];
    foreach ($api_rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $dims = isset($row["dimensionValues"]) && is_array($row["dimensionValues"]) ? $row["dimensionValues"] : [];
        $mets = isset($row["metricValues"]) && is_array($row["metricValues"]) ? $row["metricValues"] : [];
        $source = sanitize_text_field((string) ($dims[0]["value"] ?? ""));
        $medium = sanitize_text_field((string) ($dims[1]["value"] ?? ""));
        $campaign = sanitize_text_field((string) ($dims[2]["value"] ?? ""));
        if ($source === "" && $campaign === "") {
            continue;
        }
        $sessions = (int) round((float) ($mets[0]["value"] ?? 0));
        $engaged = (int) round((float) ($mets[1]["value"] ?? 0));
        $conversions = 0;
        $revenue = 0.0;
        if ($metric_count >= 4 && count($mets) >= 4) {
            $conversions = (int) round((float) ($mets[2]["value"] ?? 0));
            $revenue = (float) ($mets[3]["value"] ?? 0);
        }
        $key = strtolower(trim($source . "|" . $campaign));
        if ($key === "|") {
            continue;
        }
        $out[$key] = [
            "date" => $sync_date,
            "source" => $source !== "" ? $source : "(direct)",
            "medium" => $medium,
            "campaign" => $campaign !== "" ? $campaign : "(not set)",
            "sessions" => max(0, $sessions),
            "engaged_sessions" => max(0, $engaged),
            "conversions" => max(0, $conversions),
            "revenue" => max(0.0, $revenue),
        ];
    }

    upsellio_gsc_log("ga4.fetch.finished", ["rows" => count($out)], $trace_id);

    return array_values($out);
}

function upsellio_ga4_apply_aggregates_to_crm(array $normalized_rows)
{
    update_option("ups_automation_ga4_daily_aggregates", array_values($normalized_rows), false);
    update_option("ups_automation_ga4_last_sync", current_time("mysql"), false);
    if (function_exists("upsellio_automation_sync_ga4_channel_quality")) {
        upsellio_automation_sync_ga4_channel_quality();
    }
}

function upsellio_handle_ga4_sync_submit()
{
    if (!is_admin() || !current_user_can("edit_posts")) {
        return;
    }

    if (!isset($_POST["upsellio_ga4_sync_submit"])) {
        return;
    }

    check_admin_referer("upsellio_ga4_sync_action", "upsellio_ga4_sync_nonce");

    $property_id = isset($_POST["ga4_property_id"]) ? wp_unslash($_POST["ga4_property_id"]) : "";
    $sync_days = isset($_POST["ga4_sync_days"]) ? (int) $_POST["ga4_sync_days"] : 30;
    $sync_days = in_array($sync_days, [7, 14, 30, 60, 90], true) ? $sync_days : 30;

    $ga4_cid = isset($_POST["ga4_oauth_client_id"]) ? wp_unslash($_POST["ga4_oauth_client_id"]) : "";
    $ga4_cs = isset($_POST["ga4_oauth_client_secret"]) ? wp_unslash($_POST["ga4_oauth_client_secret"]) : "";
    $ga4_rt = isset($_POST["ga4_oauth_refresh_token"]) ? wp_unslash($_POST["ga4_oauth_refresh_token"]) : "";
    upsellio_save_ga4_property_id($property_id);
    upsellio_save_ga4_oauth_override($ga4_cid, $ga4_cs, $ga4_rt);
    update_option("upsellio_ga4_sync_days_last", $sync_days, false);

    $trace_id = upsellio_gsc_debug_trace_id();
    $pid = upsellio_get_ga4_property_id();
    $rows = upsellio_ga4_data_api_fetch_aggregates($pid, $sync_days, $trace_id);

    if (is_wp_error($rows)) {
        upsellio_gsc_log("ga4.sync.failed", ["message" => $rows->get_error_message()], $trace_id);
        wp_safe_redirect(
            upsellio_site_analytics_admin_url([
                "upsellio_ga4_error" => rawurlencode($rows->get_error_message()),
                "upsellio_ga4_trace_id" => rawurlencode($trace_id),
            ])
        );
        exit;
    }

    upsellio_ga4_apply_aggregates_to_crm($rows);
    upsellio_gsc_log("ga4.sync.success", ["rows" => count($rows)], $trace_id);

    wp_safe_redirect(
        upsellio_site_analytics_admin_url([
            "upsellio_ga4_synced" => (string) count($rows),
            "upsellio_ga4_trace_id" => rawurlencode($trace_id),
        ])
    );
    exit;
}
add_action("admin_init", "upsellio_handle_ga4_sync_submit");

/**
 * Codzienny import GA4 do CRM, jeśli skonfigurowano ID właściwości i OAuth.
 */
function upsellio_ga4_daily_oauth_sync_job()
{
    if ((string) get_option("ups_automation_ga4_sync_enabled", "1") !== "1") {
        return;
    }
    $pid = upsellio_get_ga4_property_id();
    if ($pid === "") {
        return;
    }
    $oauth = upsellio_get_oauth_credentials_for_ga4();
    if ($oauth["client_id"] === "" || $oauth["client_secret"] === "" || $oauth["refresh_token"] === "") {
        return;
    }
    $trace_id = "ga4_cron_" . (function_exists("wp_generate_uuid4") ? wp_generate_uuid4() : uniqid("", true));
    $rows = upsellio_ga4_data_api_fetch_aggregates($pid, 30, $trace_id);
    if (is_wp_error($rows) || $rows === []) {
        if (is_wp_error($rows)) {
            upsellio_gsc_log("ga4.cron.failed", ["message" => $rows->get_error_message()], $trace_id);
        }

        return;
    }
    upsellio_ga4_apply_aggregates_to_crm($rows);
    upsellio_gsc_log("ga4.cron.success", ["rows" => count($rows)], $trace_id);
}
add_action("upsellio_automation_daily", "upsellio_ga4_daily_oauth_sync_job", 8);

function upsellio_get_leads_for_post_url($post_url, $from_date)
{
    $from_timestamp = strtotime($from_date . " 00:00:00");
    $query = new WP_Query([
        "post_type" => "lead",
        "post_status" => "publish",
        "posts_per_page" => 500,
        "date_query" => [[
            "after" => $from_date,
            "inclusive" => true,
        ]],
        "fields" => "ids",
    ]);

    $count = 0;
    $target_path = (string) wp_parse_url($post_url, PHP_URL_PATH);
    foreach ($query->posts as $lead_id) {
        $landing_url = (string) get_post_meta((int) $lead_id, "_upsellio_lead_landing_url", true);
        if ($landing_url === "") {
            continue;
        }
        $created_timestamp = (int) get_post_time("U", true, (int) $lead_id);
        if ($created_timestamp < $from_timestamp) {
            continue;
        }
        $landing_path = (string) wp_parse_url($landing_url, PHP_URL_PATH);
        if ($target_path !== "" && $landing_path !== "" && strpos($landing_path, $target_path) !== false) {
            $count++;
        }
    }

    return $count;
}

function upsellio_get_keyword_metrics_for_url($url, $rows)
{
    $url_path = (string) wp_parse_url($url, PHP_URL_PATH);
    $matched = [];
    foreach ($rows as $row) {
        $row_path = (string) wp_parse_url((string) ($row["url"] ?? ""), PHP_URL_PATH);
        if ($url_path !== "" && $row_path !== "" && $row_path === $url_path) {
            $matched[] = $row;
        }
    }

    if (empty($matched)) {
        return [
            "avg_position" => 0,
            "impressions" => 0,
            "clicks" => 0,
            "keywords" => [],
        ];
    }

    $position_sum = 0;
    $impressions = 0;
    $clicks = 0;
    $keywords = [];
    foreach ($matched as $row) {
        $position_sum += (float) $row["position"];
        $impressions += (int) $row["impressions"];
        $clicks += (int) $row["clicks"];
        $keywords[] = [
            "keyword" => (string) $row["keyword"],
            "position" => (float) $row["position"],
            "impressions" => (int) $row["impressions"],
            "clicks" => (int) $row["clicks"],
            "ctr" => (float) $row["ctr"],
        ];
    }

    usort($keywords, function ($a, $b) {
        return $a["position"] <=> $b["position"];
    });

    return [
        "avg_position" => round($position_sum / count($matched), 1),
        "impressions" => $impressions,
        "clicks" => $clicks,
        "keywords" => array_slice($keywords, 0, 6),
    ];
}

function upsellio_build_page_recommendations($row)
{
    $tips = [];
    if ((float) $row["avg_position"] > 10 && (float) $row["avg_position"] <= 20 && (int) $row["impressions"] >= 100) {
        $tips[] = "Pozycje 11-20: rozbuduj sekcje H2/H3 i dodaj linkowanie wewnętrzne do tej strony.";
    }
    if ((float) $row["avg_position"] > 0 && (float) $row["avg_position"] <= 8 && (float) $row["ctr"] < 1.5) {
        $tips[] = "Niskie CTR przy dobrych pozycjach: popraw SEO title i meta description pod wyższy CTR.";
    }
    if ((int) $row["views_30d"] >= 120 && (float) $row["conversion_rate"] < 1) {
        $tips[] = "Dużo wejść, mało leadów: wzmocnij CTA i dodaj formularz wyżej w treści.";
    }
    if ((int) $row["trend_delta"] < 0) {
        $tips[] = "Trend ruchu spada: odśwież treść i dopisz aktualne dane/case study.";
    }
    if ((float) $row["avg_position"] > 20 && (int) $row["impressions"] > 50) {
        $tips[] = "Słaba widoczność: dodaj nowy artykuł satelitarny i podlinkuj tę stronę exact/partial anchorami.";
    }

    if (empty($tips)) {
        $tips[] = "Strona działa stabilnie. Kontynuuj linkowanie i monitoruj CTR głównych fraz.";
    }

    return array_slice($tips, 0, 2);
}

function upsellio_calculate_roi_score($row)
{
    $avg_position = (float) ($row["avg_position"] ?? 0);
    $impressions = (int) ($row["impressions"] ?? 0);
    $views = (int) ($row["views_30d"] ?? 0);
    $conversion_rate = (float) ($row["conversion_rate"] ?? 0);
    $trend_delta = (int) ($row["trend_delta"] ?? 0);

    $traffic_potential = min(100, (int) round(($impressions / 20) + ($views / 6)));

    if ($avg_position <= 0) {
        $rank_opportunity = 30;
    } elseif ($avg_position <= 3) {
        $rank_opportunity = 10;
    } elseif ($avg_position <= 10) {
        $rank_opportunity = 35;
    } elseif ($avg_position <= 20) {
        $rank_opportunity = 80;
    } else {
        $rank_opportunity = 55;
    }

    $conversion_opportunity = $views >= 80
        ? max(0, min(100, (int) round(100 - ($conversion_rate * 20))))
        : 40;
    $trend_urgency = $trend_delta < 0 ? min(100, abs($trend_delta) * 8) : 10;

    $score = (int) round(
        ($traffic_potential * 0.35) +
        ($rank_opportunity * 0.30) +
        ($conversion_opportunity * 0.25) +
        ($trend_urgency * 0.10)
    );

    $target_conversion_rate = 2.5;
    $expected_lead_uplift = round(max(0, (($target_conversion_rate - $conversion_rate) / 100) * $views), 1);

    return [
        "score" => max(0, min(100, $score)),
        "traffic_potential" => $traffic_potential,
        "rank_opportunity" => $rank_opportunity,
        "conversion_opportunity" => $conversion_opportunity,
        "trend_urgency" => $trend_urgency,
        "expected_lead_uplift" => $expected_lead_uplift,
    ];
}

function upsellio_render_site_analytics_page()
{
    if (!current_user_can("edit_posts")) {
        return;
    }

    $upsellio_sa_form_action = upsellio_site_analytics_admin_url();

    $days = isset($_GET["range"]) ? (int) $_GET["range"] : 30;
    $days = in_array($days, [7, 14, 30, 60, 90], true) ? $days : 30;
    $dates = upsellio_get_analytics_dates($days);
    $from_date = $dates[0];
    $keyword_rows = upsellio_get_keyword_metrics_data();
    $posts = get_posts([
        "post_type" => "post",
        "post_status" => "publish",
        "posts_per_page" => 120,
        "orderby" => "date",
        "order" => "DESC",
    ]);

    $report_rows = [];
    $total_views = 0;
    $total_leads = 0;
    $total_impressions = 0;
    $total_clicks = 0;
    $position_values = [];

    $last_7_dates = upsellio_get_analytics_dates(7);
    $prev_7_dates = upsellio_get_analytics_dates(14);
    $prev_7_dates = array_slice($prev_7_dates, 0, 7);

    foreach ($posts as $post) {
        $post_id = (int) $post->ID;
        $post_url = (string) get_permalink($post_id);
        $views_30d = upsellio_get_post_views_for_dates($post_id, $dates);
        $views_last_7 = upsellio_get_post_views_for_dates($post_id, $last_7_dates);
        $views_prev_7 = upsellio_get_post_views_for_dates($post_id, $prev_7_dates);
        $trend_delta = $views_last_7 - $views_prev_7;
        $leads = upsellio_get_leads_for_post_url($post_url, $from_date);
        $keyword_metrics = upsellio_get_keyword_metrics_for_url($post_url, $keyword_rows);
        $avg_position = (float) $keyword_metrics["avg_position"];
        $impressions = (int) $keyword_metrics["impressions"];
        $clicks = (int) $keyword_metrics["clicks"];
        $ctr = $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0;
        $conversion_rate = $views_30d > 0 ? round(($leads / $views_30d) * 100, 2) : 0;

        $row = [
            "post_id" => $post_id,
            "title" => (string) get_the_title($post_id),
            "url" => $post_url,
            "views_30d" => $views_30d,
            "trend_delta" => $trend_delta,
            "leads" => $leads,
            "conversion_rate" => $conversion_rate,
            "avg_position" => $avg_position,
            "impressions" => $impressions,
            "clicks" => $clicks,
            "ctr" => $ctr,
            "keywords" => $keyword_metrics["keywords"],
        ];
        $row["recommendations"] = upsellio_build_page_recommendations($row);
        $report_rows[] = $row;

        $total_views += $views_30d;
        $total_leads += $leads;
        $total_impressions += $impressions;
        $total_clicks += $clicks;
        if ($avg_position > 0) {
            $position_values[] = $avg_position;
        }
    }

    usort($report_rows, function ($a, $b) {
        if ($b["views_30d"] === $a["views_30d"]) {
            return $b["impressions"] <=> $a["impressions"];
        }
        return $b["views_30d"] <=> $a["views_30d"];
    });

    $avg_position_total = !empty($position_values) ? round(array_sum($position_values) / count($position_values), 1) : 0;
    $conversion_total = $total_views > 0 ? round(($total_leads / $total_views) * 100, 2) : 0;
    $ctr_total = $total_impressions > 0 ? round(($total_clicks / $total_impressions) * 100, 2) : 0;

    $keywords_view = $keyword_rows;
    usort($keywords_view, function ($a, $b) {
        return ((float) $a["position"]) <=> ((float) $b["position"]);
    });
    $keywords_view = array_slice($keywords_view, 0, 25);

    $daily_views_series = upsellio_get_daily_views_series($dates);
    $daily_leads_series = upsellio_get_daily_leads_series($dates);
    $daily_keyword_series = upsellio_get_daily_keyword_series($keyword_rows, $dates);
    $max_daily_views = max(1, !empty($daily_views_series) ? max($daily_views_series) : 1);
    $max_daily_leads = max(1, !empty($daily_leads_series) ? max($daily_leads_series) : 1);
    $daily_impressions_values = array_map(function ($row) {
        return (int) ($row["impressions"] ?? 0);
    }, $daily_keyword_series);
    $daily_clicks_values = array_map(function ($row) {
        return (int) ($row["clicks"] ?? 0);
    }, $daily_keyword_series);
    $max_daily_impressions = max(1, !empty($daily_impressions_values) ? max($daily_impressions_values) : 1);
    $max_daily_clicks = max(1, !empty($daily_clicks_values) ? max($daily_clicks_values) : 1);

    foreach ($report_rows as $index => $row) {
        $report_rows[$index]["roi"] = upsellio_calculate_roi_score($row);
    }
    $priority_rows = $report_rows;
    usort($priority_rows, function ($a, $b) {
        return ((int) $b["roi"]["score"]) <=> ((int) $a["roi"]["score"]);
    });
    $priority_rows = array_slice($priority_rows, 0, 10);
    $gsc_credentials = upsellio_get_gsc_credentials();
    $gsc_debug_logs = upsellio_gsc_get_logs();
    $ga4_property_id_display = upsellio_get_ga4_property_id();
    $ga4_oauth_override = upsellio_get_ga4_oauth_override();
    $ga4_last = (string) get_option("ups_automation_ga4_last_sync", "");
    $ga4_ui_days = (int) get_option("upsellio_ga4_sync_days_last", 30);
    $ga4_ui_days = in_array($ga4_ui_days, [7, 14, 30, 60, 90], true) ? $ga4_ui_days : 30;
    $google_perm = upsellio_google_get_permission_snapshot();
    $gads_cfg = upsellio_google_ads_get_settings();
    $gads_scope_on = (string) get_option("upsellio_google_ads_include_scope", "0") === "1";
    $g_oauth_redirect_uri_override_val = (string) get_option(upsellio_google_oauth_redirect_uri_override_option_key(), "");
    $g_oauth_use_rest = upsellio_google_oauth_use_rest_callback();
    $ups_managed_google_oauth = function_exists("upsellio_google_managed_oauth_is_active") && upsellio_google_managed_oauth_is_active();
    $gads_ready = upsellio_google_ads_api_ready();
    $gads_test_uid = get_current_user_id();
    $gads_test_err_msg = get_transient("upsellio_gads_test_err_" . $gads_test_uid);
    if ($gads_test_err_msg !== false) {
        delete_transient("upsellio_gads_test_err_" . $gads_test_uid);
    }
    $gads_test_ok_body = get_transient("upsellio_gads_test_ok_" . $gads_test_uid);
    if ($gads_test_ok_body !== false) {
        delete_transient("upsellio_gads_test_ok_" . $gads_test_uid);
    }
    $keyword_source = (string) get_option("upsellio_keyword_metrics_source", "csv_import");
    $last_sync = (string) get_option("upsellio_keyword_metrics_last_sync", "");
    if ($keyword_source === "gsc_live") {
        $source_label = "Google Search Console (live sync)";
    } elseif ($keyword_source === "gsc_service_account") {
        $source_label = "Google Search Console (service account / REST)";
    } else {
        $source_label = "Ręczny import CSV";
    }
    ?>
    <div class="wrap">
      <style>
        .ups-analytics-wrap{max-width:1320px}
        .ups-analytics-kpi{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin:16px 0}
        .ups-analytics-card{background:#fff;border:1px solid #d9dde3;border-radius:14px;padding:14px}
        .ups-analytics-label{font-size:12px;color:#5f6368;text-transform:uppercase;letter-spacing:.03em}
        .ups-analytics-value{font-size:26px;font-weight:700;line-height:1.1;margin-top:6px}
        .ups-analytics-sub{font-size:12px;color:#5f6368;margin-top:4px}
        .ups-analytics-table{width:100%;border-collapse:separate;border-spacing:0}
        .ups-analytics-table th,.ups-analytics-table td{border-bottom:1px solid #eceff3;padding:10px 9px;vertical-align:top;text-align:left}
        .ups-analytics-table th{font-size:12px;text-transform:uppercase;color:#5f6368;background:#f6f8fa}
        .ups-chip{display:inline-flex;border-radius:999px;padding:2px 8px;font-size:11px;font-weight:700}
        .ups-chip.up{background:#ecfeff;color:#0f766e}
        .ups-chip.down{background:#fff2f2;color:#9f3636}
        .ups-chip.flat{background:#f3f4f6;color:#475467}
        .ups-mono{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace}
        .ups-keyword-list{margin:0;padding-left:16px;display:grid;gap:4px}
        .ups-keyword-list li{font-size:12px}
        .ups-reco-list{margin:0;padding-left:16px;display:grid;gap:4px}
        .ups-reco-list li{font-size:12px;color:#3f3f39}
        .ups-import-box{margin-top:18px;background:#fff;border:1px solid #d9dde3;border-radius:14px;padding:14px}
        .ups-trend-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:14px}
        .ups-trend-card{background:#fff;border:1px solid #d9dde3;border-radius:14px;padding:12px}
        .ups-trend-title{margin:0 0 8px;font-size:14px}
        .ups-trend-bars{display:grid;grid-auto-flow:column;grid-auto-columns:minmax(3px,1fr);align-items:end;gap:2px;height:120px}
        .ups-trend-bar{display:block;border-radius:3px 3px 0 0;min-height:2px}
        .ups-trend-bar.views{background:#0d9488}
        .ups-trend-bar.leads{background:#2271b1}
        .ups-trend-bar.impressions{background:#8b5cf6}
        .ups-trend-bar.clicks{background:#f59e0b}
        .ups-trend-meta{display:flex;justify-content:space-between;gap:10px;font-size:12px;color:#5f6368;margin-top:8px}
        .ups-priority-score{font-size:20px;font-weight:700}
        .ups-priority-high{color:#b42318}
        .ups-priority-mid{color:#b45309}
        .ups-priority-low{color:#027a48}
        @media(max-width:1100px){.ups-analytics-kpi{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media(max-width:1100px){.ups-trend-grid{grid-template-columns:1fr}}
      </style>
      <div class="ups-analytics-wrap">
        <h1>Analityka SEO i konwersji</h1>
        <p>Panel łączy odsłony stron, trendy ruchu, pozycje słów kluczowych (z importu CSV) i konwersje z CRM, a następnie generuje rekomendacje optymalizacji per URL.</p>
        <p><strong>Źródło danych keywordów:</strong> <?php echo esc_html($source_label); ?><?php echo $last_sync !== "" ? " · ostatnia synchronizacja: " . esc_html($last_sync) : ""; ?></p>

        <form method="get" action="<?php echo esc_url(admin_url("admin.php")); ?>">
          <input type="hidden" name="page" value="<?php echo esc_attr(upsellio_site_analytics_page_slug()); ?>" />
          <label>
            Zakres danych:
            <select name="range" onchange="this.form.submit()">
              <option value="7" <?php selected($days, 7); ?>>7 dni</option>
              <option value="14" <?php selected($days, 14); ?>>14 dni</option>
              <option value="30" <?php selected($days, 30); ?>>30 dni</option>
              <option value="60" <?php selected($days, 60); ?>>60 dni</option>
              <option value="90" <?php selected($days, 90); ?>>90 dni</option>
            </select>
          </label>
        </form>

        <div class="ups-analytics-kpi">
          <div class="ups-analytics-card"><div class="ups-analytics-label">Wyświetlenia</div><div class="ups-analytics-value"><?php echo esc_html((string) $total_views); ?></div><div class="ups-analytics-sub">Zakres: <?php echo esc_html((string) $days); ?> dni</div></div>
          <div class="ups-analytics-card"><div class="ups-analytics-label">Leady</div><div class="ups-analytics-value"><?php echo esc_html((string) $total_leads); ?></div><div class="ups-analytics-sub">Atrybucja po landing URL</div></div>
          <div class="ups-analytics-card"><div class="ups-analytics-label">Konwersja</div><div class="ups-analytics-value"><?php echo esc_html((string) $conversion_total); ?>%</div><div class="ups-analytics-sub">Leady / wyświetlenia</div></div>
          <div class="ups-analytics-card"><div class="ups-analytics-label">Śr. pozycja</div><div class="ups-analytics-value"><?php echo esc_html($avg_position_total > 0 ? (string) $avg_position_total : "—"); ?></div><div class="ups-analytics-sub">Z zaimportowanych słów kluczowych</div></div>
          <div class="ups-analytics-card"><div class="ups-analytics-label">CTR</div><div class="ups-analytics-value"><?php echo esc_html((string) $ctr_total); ?>%</div><div class="ups-analytics-sub"><?php echo esc_html((string) $total_clicks); ?> kliknięć / <?php echo esc_html((string) $total_impressions); ?> wyświetleń</div></div>
        </div>

        <div class="ups-trend-grid">
          <div class="ups-trend-card">
            <h2 class="ups-trend-title">Trend dzień-po-dniu: wyświetlenia</h2>
            <div class="ups-trend-bars">
              <?php foreach ($daily_views_series as $date_key => $value) : ?>
                <?php $height = max(2, (int) round(($value / $max_daily_views) * 120)); ?>
                <span class="ups-trend-bar views" style="height:<?php echo esc_attr((string) $height); ?>px;" title="<?php echo esc_attr($date_key . ": " . $value); ?>"></span>
              <?php endforeach; ?>
            </div>
            <div class="ups-trend-meta"><span>max: <?php echo esc_html((string) $max_daily_views); ?></span><span>dni: <?php echo esc_html((string) count($dates)); ?></span></div>
          </div>
          <div class="ups-trend-card">
            <h2 class="ups-trend-title">Trend dzień-po-dniu: leady</h2>
            <div class="ups-trend-bars">
              <?php foreach ($daily_leads_series as $date_key => $value) : ?>
                <?php $height = max(2, (int) round(($value / $max_daily_leads) * 120)); ?>
                <span class="ups-trend-bar leads" style="height:<?php echo esc_attr((string) $height); ?>px;" title="<?php echo esc_attr($date_key . ": " . $value); ?>"></span>
              <?php endforeach; ?>
            </div>
            <div class="ups-trend-meta"><span>max: <?php echo esc_html((string) $max_daily_leads); ?></span><span>dni: <?php echo esc_html((string) count($dates)); ?></span></div>
          </div>
          <div class="ups-trend-card">
            <h2 class="ups-trend-title">Trend dzień-po-dniu: impressions</h2>
            <div class="ups-trend-bars">
              <?php foreach ($daily_keyword_series as $date_key => $values) : ?>
                <?php
                $impressions_value = (int) ($values["impressions"] ?? 0);
                $height = max(2, (int) round(($impressions_value / $max_daily_impressions) * 120));
                ?>
                <span class="ups-trend-bar impressions" style="height:<?php echo esc_attr((string) $height); ?>px;" title="<?php echo esc_attr($date_key . ": " . $impressions_value); ?>"></span>
              <?php endforeach; ?>
            </div>
            <div class="ups-trend-meta"><span>max: <?php echo esc_html((string) $max_daily_impressions); ?></span><span>dane z CSV/API</span></div>
          </div>
          <div class="ups-trend-card">
            <h2 class="ups-trend-title">Trend dzień-po-dniu: kliknięcia</h2>
            <div class="ups-trend-bars">
              <?php foreach ($daily_keyword_series as $date_key => $values) : ?>
                <?php
                $clicks_value = (int) ($values["clicks"] ?? 0);
                $height = max(2, (int) round(($clicks_value / $max_daily_clicks) * 120));
                ?>
                <span class="ups-trend-bar clicks" style="height:<?php echo esc_attr((string) $height); ?>px;" title="<?php echo esc_attr($date_key . ": " . $clicks_value); ?>"></span>
              <?php endforeach; ?>
            </div>
            <div class="ups-trend-meta"><span>max: <?php echo esc_html((string) $max_daily_clicks); ?></span><span>dane z CSV/API</span></div>
          </div>
        </div>

        <div class="ups-analytics-card" style="margin-top:14px;">
          <h2 style="margin-top:0;">Top 10 stron do poprawy najpierw (ROI score)</h2>
          <table class="ups-analytics-table">
            <thead>
              <tr>
                <th>Strona</th>
                <th>ROI score</th>
                <th>Potencjał</th>
                <th>Prognoza efektu</th>
                <th>Pierwszy krok</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($priority_rows as $row) : ?>
                <?php
                $roi_score = (int) $row["roi"]["score"];
                $score_class = $roi_score >= 70 ? "ups-priority-high" : ($roi_score >= 50 ? "ups-priority-mid" : "ups-priority-low");
                ?>
                <tr>
                  <td>
                    <strong><?php echo esc_html($row["title"]); ?></strong><br />
                    <a href="<?php echo esc_url($row["url"]); ?>" target="_blank" rel="noopener" class="ups-mono"><?php echo esc_html((string) wp_parse_url($row["url"], PHP_URL_PATH)); ?></a>
                  </td>
                  <td>
                    <span class="ups-priority-score <?php echo esc_attr($score_class); ?>"><?php echo esc_html((string) $roi_score); ?></span>/100
                  </td>
                  <td>
                    ruch: <?php echo esc_html((string) $row["roi"]["traffic_potential"]); ?>/100<br />
                    ranking: <?php echo esc_html((string) $row["roi"]["rank_opportunity"]); ?>/100<br />
                    konwersja: <?php echo esc_html((string) $row["roi"]["conversion_opportunity"]); ?>/100
                  </td>
                  <td>
                    +<?php echo esc_html((string) $row["roi"]["expected_lead_uplift"]); ?> leadów / 30 dni<br />
                    (przy CR docelowym 2.5%)
                  </td>
                  <td>
                    <?php echo esc_html((string) ($row["recommendations"][0] ?? "Utrzymuj aktualność treści i linkowanie.")); ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="ups-analytics-card">
          <h2 style="margin-top:0;">Strony do optymalizacji</h2>
          <table class="ups-analytics-table">
            <thead>
              <tr>
                <th>Strona</th>
                <th>Ruch i trend</th>
                <th>Frazy i widoczność</th>
                <th>Konwersje</th>
                <th>Rekomendacje</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach (array_slice($report_rows, 0, 20) as $row) : ?>
                <?php
                $trend_class = "flat";
                if ((int) $row["trend_delta"] > 0) {
                    $trend_class = "up";
                } elseif ((int) $row["trend_delta"] < 0) {
                    $trend_class = "down";
                }
                ?>
                <tr>
                  <td>
                    <strong><?php echo esc_html($row["title"]); ?></strong><br />
                    <a href="<?php echo esc_url($row["url"]); ?>" target="_blank" rel="noopener" class="ups-mono"><?php echo esc_html((string) wp_parse_url($row["url"], PHP_URL_PATH)); ?></a>
                  </td>
                  <td>
                    <?php echo esc_html((string) $row["views_30d"]); ?> wyświetleń<br />
                    <span class="ups-chip <?php echo esc_attr($trend_class); ?>">
                      trend 7d: <?php echo (int) $row["trend_delta"] > 0 ? "+" : ""; ?><?php echo esc_html((string) $row["trend_delta"]); ?>
                    </span>
                  </td>
                  <td>
                    śr. pozycja: <?php echo esc_html($row["avg_position"] > 0 ? (string) $row["avg_position"] : "—"); ?><br />
                    impressions: <?php echo esc_html((string) $row["impressions"]); ?> · clicks: <?php echo esc_html((string) $row["clicks"]); ?><br />
                    <?php if (!empty($row["keywords"])) : ?>
                      <ul class="ups-keyword-list">
                        <?php foreach (array_slice($row["keywords"], 0, 2) as $keyword) : ?>
                          <li><?php echo esc_html($keyword["keyword"]); ?> (poz. <?php echo esc_html((string) $keyword["position"]); ?>)</li>
                        <?php endforeach; ?>
                      </ul>
                    <?php endif; ?>
                  </td>
                  <td>
                    leady: <?php echo esc_html((string) $row["leads"]); ?><br />
                    CR: <?php echo esc_html((string) $row["conversion_rate"]); ?>%
                  </td>
                  <td>
                    <ul class="ups-reco-list">
                      <?php foreach ($row["recommendations"] as $tip) : ?>
                        <li><?php echo esc_html($tip); ?></li>
                      <?php endforeach; ?>
                    </ul>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="ups-analytics-card" style="margin-top:14px;">
          <h2 style="margin-top:0;">Pozycje na konkretne słowa kluczowe</h2>
          <?php if (empty($keywords_view)) : ?>
            <p>Brak danych keywordów. Zaimportuj CSV poniżej, aby zobaczyć pozycje i trendy.</p>
          <?php else : ?>
            <table class="ups-analytics-table">
              <thead>
                <tr>
                  <th>Keyword</th>
                  <th>URL</th>
                  <th>Pozycja</th>
                  <th>Impressions</th>
                  <th>Kliknięcia</th>
                  <th>CTR</th>
                  <th>Data</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($keywords_view as $keyword_row) : ?>
                  <tr>
                    <td><?php echo esc_html((string) $keyword_row["keyword"]); ?></td>
                    <td class="ups-mono"><?php echo esc_html((string) wp_parse_url((string) $keyword_row["url"], PHP_URL_PATH)); ?></td>
                    <td><?php echo esc_html((string) $keyword_row["position"]); ?></td>
                    <td><?php echo esc_html((string) $keyword_row["impressions"]); ?></td>
                    <td><?php echo esc_html((string) $keyword_row["clicks"]); ?></td>
                    <td><?php echo esc_html((string) $keyword_row["ctr"]); ?>%</td>
                    <td><?php echo esc_html((string) $keyword_row["date"]); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>

        <div class="ups-import-box">
          <h2 style="margin-top:0;">Google — logowanie przez konto Gmail (GSC + GA4)</h2>
          <details style="margin:0 0 14px;font-size:13px;color:#5f6368;">
            <summary style="cursor:pointer;font-weight:600;color:#1d2327;">Jak to robi Rank Math vs Upsellio</summary>
            <p style="margin:10px 0 0;line-height:1.55;">
              Wtyczka <strong>Rank Math</strong> kieruje Cię na ich serwer <code>oauth.rankmath.com</code> — Google widzi aplikację Rank Math;
              tokeny wracają do WordPressa już po autoryzacji (wygodne, bez własnego OAuth Client ID).
              <strong>Upsellio</strong> używa <strong>Twojego</strong> klienta OAuth z Google Cloud (jak „własna aplikacja”) —
              ten sam mechanizm Google, ale redirect URI i Client ID są pod Twoją domeną.
              Rank Math w darmowej wersji łączy Search Console, Analytics i <em>AdSense</em>. W Upsellio możesz dodać zakres OAuth
              <code>adwords</code> i zapisać developer token / Customer ID pod wywołania Google Ads API (sekcja niżej).
              GA4 używa zakresu <code>analytics.readonly</code>.
            </p>
          </details>
          <?php if ($gsc_credentials["refresh_token"] !== "") : ?>
            <table class="widefat" style="max-width:720px;margin-bottom:14px;">
              <thead><tr><th>Uprawnienie Google (tokeninfo)</th><th>Status</th></tr></thead>
              <tbody>
                <tr>
                  <td>Search Console (GSC)</td>
                  <td><?php echo $google_perm["has_console"] ? "<span style=\"color:#0a0;font-weight:700;\">tak</span>" : "<span style=\"color:#a00;\">brak</span>"; ?></td>
                </tr>
                <tr>
                  <td>Google Analytics (Data API / GA4)</td>
                  <td><?php echo $google_perm["has_analytics"] ? "<span style=\"color:#0a0;font-weight:700;\">tak</span>" : "<span style=\"color:#a00;\">brak</span>"; ?></td>
                </tr>
                <tr>
                  <td>AdSense (tylko jeśli dodasz zakres <code>adsense.readonly</code> przez filtr)</td>
                  <td><?php echo $google_perm["has_adsense"] ? "<span style=\"color:#0a0;font-weight:700;\">tak</span>" : "<span style=\"color:#666;\">—</span>"; ?></td>
                </tr>
                <tr>
                  <td>Google Ads API (<code>https://www.googleapis.com/auth/adwords</code>)</td>
                  <td><?php echo !empty($google_perm["has_google_ads"]) ? "<span style=\"color:#0a0;font-weight:700;\">tak</span>" : "<span style=\"color:#a00;\">brak</span>"; ?></td>
                </tr>
              </tbody>
            </table>
            <?php if ($google_perm["checked_at"] !== "") : ?>
              <p class="description" style="margin:-6px 0 10px;">Ostatnie sprawdzenie zakresów: <?php echo esc_html($google_perm["checked_at"]); ?> (zapis <code>tokeninfo</code>).</p>
            <?php endif; ?>
            <form method="post" action="<?php echo esc_url($upsellio_sa_form_action); ?>" style="margin-bottom:16px;">
              <?php wp_nonce_field("upsellio_google_permissions_refresh_action", "upsellio_google_permissions_refresh_nonce"); ?>
              <input type="hidden" name="upsellio_google_permissions_refresh" value="1" />
              <button type="submit" class="button">Odśwież status uprawnień (tokeninfo)</button>
            </form>
          <?php endif; ?>
          <?php if (isset($_GET["upsellio_google_perm_refreshed"])) : ?>
            <div class="notice notice-success inline"><p>Zaktualizowano listę przyznanych zakresów OAuth.</p></div>
          <?php endif; ?>
          <?php if (isset($_GET["upsellio_google_connected"])) : ?>
            <div class="notice notice-success inline"><p>Konto Google połączone. Refresh token zapisany — możesz zsynchronizować GSC i GA4 poniżej.</p></div>
          <?php endif; ?>
          <?php if (isset($_GET["upsellio_google_disconnected"])) : ?>
            <div class="notice notice-success inline"><p>Odłączono refresh token (Client ID / Secret i property GSC zostają).</p></div>
          <?php endif; ?>
          <?php if (isset($_GET["upsellio_google_oauth_error"])) : ?>
            <div class="notice notice-error inline"><p>OAuth Google: <?php echo esc_html(rawurldecode((string) $_GET["upsellio_google_oauth_error"])); ?></p></div>
          <?php endif; ?>
          <?php if (isset($_GET["upsellio_google_ads_scope_saved"])) : ?>
            <div class="notice notice-success inline"><p>Zapisano preferencję zakresu Google Ads (obowiązuje przy następnym logowaniu przez Google).</p></div>
          <?php endif; ?>
          <?php if (isset($_GET["upsellio_google_oauth_redirect_mode_saved"])) : ?>
            <div class="notice notice-success inline"><p>Zapisano sposób callback OAuth (REST vs panel admin). Sprawdź niebieskie pole „Redirect URI” — w Google musi być wpisany dokładnie ten adres.</p></div>
          <?php endif; ?>
          <?php if (isset($_GET["upsellio_google_ads_saved"])) : ?>
            <div class="notice notice-success inline"><p>Zapisano ustawienia Google Ads API (developer token / Customer ID).</p></div>
          <?php endif; ?>
          <?php if (isset($_GET["upsellio_google_ads_test"]) && (string) $_GET["upsellio_google_ads_test"] === "err" && is_string($gads_test_err_msg)) : ?>
            <div class="notice notice-error inline"><p>Test Google Ads API: <?php echo esc_html($gads_test_err_msg); ?></p></div>
          <?php endif; ?>
          <?php if (isset($_GET["upsellio_google_ads_test"]) && (string) $_GET["upsellio_google_ads_test"] === "ok" && is_string($gads_test_ok_body)) : ?>
            <div class="notice notice-success inline"><p>Odpowiedź <code>customers:listAccessibleCustomers</code>:</p><pre style="max-height:200px;overflow:auto;background:#f6f8fa;padding:8px;"><?php echo esc_html($gads_test_ok_body); ?></pre></div>
          <?php endif; ?>
          <?php if ($ups_managed_google_oauth) : ?>
            <div class="notice notice-success inline" style="max-width:720px;"><p style="margin:0;font-size:13px;"><strong>Tryb Upsellio Connect (zarządzany OAuth)</strong> — nie musisz tworzyć projektu ani redirectów w Google Cloud. Po kliknięciu „Zaloguj przez Google” otworzy się most Upsellio; po zalogowaniu kontem Google token zapisze się w WordPressie. Wymaga wdrożonego serwera mostu i stałych <code>UPSELLIO_MANAGED_GOOGLE_OAUTH_*</code> w <code>wp-config.php</code>.</p></div>
          <?php else : ?>
          <p style="font-size:13px;color:#3f3f39;">
            W <a href="https://console.cloud.google.com/apis/credentials" target="_blank" rel="noopener">Google Cloud Console</a> → <strong>APIs &amp; Services</strong> → <strong>Credentials</strong> → wybierz <strong>ten sam</strong> projekt i ten sam <strong>OAuth 2.0 Client ID</strong>, którego numer masz w polu Client ID poniżej (typ klienta: <strong>Web application</strong>). W sekcji klienta dodaj wpisy w polu <strong>Authorized redirect URIs</strong> — <em>nie</em> myl z polem „Authorized JavaScript origins” (to osobna lista; dla mismatch liczy się wyłącznie redirect URIs). Zapisz w Google (Save) i odczekaj ok. minutę.
          </p>
          <?php endif; ?>
          <?php
            $ups_oauth_effective_redirect = upsellio_google_oauth_redirect_uri();
            $ups_oauth_redirect_variants = upsellio_google_oauth_redirect_uri_variants();
            ?>
          <?php if (!$ups_managed_google_oauth) : ?>
          <p style="font-size:13px;margin:10px 0 8px;padding:10px 12px;background:#f0f6fc;border:1px solid #c5d9ed;border-radius:6px;">
            <strong>Redirect URI wysyłany w żądaniu do Google (musi być wpisany w Authorized redirect URIs):</strong><br />
            <code style="word-break:break-all;font-size:13px;"><?php echo esc_html($ups_oauth_effective_redirect); ?></code>
          </p>
          <p style="margin:0 0 10px;">
            <label for="upsellio-oauth-uri-copy" style="display:block;font-size:12px;color:#5f6368;margin-bottom:4px;">Skopiuj do schowka (pole tylko do odczytu — klik zaznacza całość, potem Ctrl+C):</label>
            <input type="text" readonly="readonly" id="upsellio-oauth-uri-copy" class="large-text code" style="font-size:13px;max-width:100%;box-sizing:border-box;" value="<?php echo esc_attr($ups_oauth_effective_redirect); ?>" onclick="this.select();" onfocus="this.select();" autocomplete="off" spellcheck="false" />
          </p>
          <div style="font-size:12px;color:#3f3f39;margin:0 0 12px;padding:10px 12px;background:#fff8e5;border:1px solid #e6d9a8;border-radius:6px;max-width:720px;">
            <strong>Jeśli w logu jest <code>oauth_redirect_mode":"admin"</code> i nadal <code>redirect_uri_mismatch</code></strong> — Upsellio wysyła już właściwy adres; brakuje go wyłącznie w konsoli Google dla <strong>tego samego</strong> Client ID co w WordPressie. Sprawdź po kolei:
            <ol style="margin:8px 0 0;padding-left:20px;line-height:1.5;">
              <li>Otwierasz <strong>Credentials</strong> → klikasz klienta o ID kończącym się tak jak w polu „OAuth Client ID” poniżej (nie inny projekt i nie inny ekran „OAuth consent”).</li>
              <li>Typ klienta to <strong>Web application</strong> (nie „Desktop”).</li>
              <li>W sekcji <strong>Authorized redirect URIs</strong> (nie „Authorized JavaScript origins”) dodajesz <strong>jedną linię</strong> — dokładnie jak w niebieskim polu powyżej: ten sam schemat (<code>https://</code>), host, ścieżka, <code>?</code> i <code>page=…</code>, <strong>bez</strong> końcowego <code>/</code> i bez spacji.</li>
              <li><strong>Save</strong> w Google Cloud, odczekaj 1–5 minut i spróbuj ponownie „Zaloguj przez Google”.</li>
            </ol>
          </div>
          <p style="font-size:12px;color:#5f6368;margin:0 0 10px;">Jeśli permalinki są wyłączone, WordPress może użyć innego formatu URL (<code>?rest_route=...</code>) — wtedy skopiuj dokładnie ten z powyższego pola lub z listy.</p>
          <ul style="font-size:13px;margin:8px 0 12px;padding-left:20px;list-style:disc;">
            <?php foreach ($ups_oauth_redirect_variants as $ups_oauth_one_uri) : ?>
              <li style="margin-bottom:6px;"><code style="word-break:break-all;"><?php echo esc_html($ups_oauth_one_uri); ?></code></li>
            <?php endforeach; ?>
          </ul>
          <form method="post" action="<?php echo esc_url($upsellio_sa_form_action); ?>" style="margin:0 0 14px;padding:10px 12px;background:#fafafa;border:1px solid #ddd;border-radius:6px;max-width:720px;">
            <?php wp_nonce_field("upsellio_google_oauth_redirect_mode_action", "upsellio_google_oauth_redirect_mode_nonce"); ?>
            <input type="hidden" name="upsellio_google_oauth_redirect_mode_save" value="1" />
            <p style="margin:0 0 8px;font-size:13px;"><strong>Callback OAuth</strong> — domyślnie wysyłany jest adres <strong>admin.php</strong> (najczęściej zgodny z wpisem w Google). Zaznacz poniżej tylko wtedy, gdy w Google Cloud masz <strong>wyłącznie</strong> URI z <code>/wp-json/upsellio/v1/google-oauth-callback</code>. Przy <code>redirect_uri_mismatch</code> „Authorized redirect URIs” musi zawierać <em>dokładnie</em> ten sam ciąg co niebieskie pole (bez końcowego <code>/</code>).</p>
            <p style="margin:0;">
              <label>
                <input type="checkbox" name="upsellio_google_oauth_use_rest" value="1" <?php checked($g_oauth_use_rest); ?> />
                Używaj endpointu <strong>REST</strong> (<code>/wp-json/upsellio/v1/google-oauth-callback</code>) zamiast <strong>admin.php</strong>
              </label>
            </p>
            <p style="margin:8px 0 0;"><button type="submit" class="button">Zapisz tryb callback</button></p>
          </form>
          <p style="font-size:12px;color:#5f6368;margin-top:0;">
            Nadal <code>redirect_uri_mismatch</code>? Sprawdź, czy edytujesz dane logowania powiązane z Client ID <code><?php echo esc_html((string) ($gsc_credentials["client_id"] ?? "")); ?></code>, oraz czy w Google nie ma końcowego ukośnika ani literówki. Opcjonalnie wypełnij „Nadpisanie redirect URI” w formularzu poniżej — identycznie jak w konsoli Google.
          </p>
          <?php endif; ?>
          <p style="font-size:12px;color:#5f6368;">Domyślne zakresy zgody: Search Console (read-only) oraz Analytics (read-only). Opcjonalnie możesz dołączyć <strong>Google Ads API</strong> (<code>adwords</code>). Po kliknięciu zalogujesz się na Google i zatwierdzisz dostęp — refresh token uzupełni się automatycznie.</p>
          <form method="post" action="<?php echo esc_url($upsellio_sa_form_action); ?>" style="margin-bottom:12px;">
            <?php wp_nonce_field("upsellio_google_ads_scope_action", "upsellio_google_ads_scope_nonce"); ?>
            <input type="hidden" name="upsellio_google_ads_scope_save" value="1" />
            <p style="margin-bottom:6px;">
              <label>
                <input type="checkbox" name="upsellio_google_ads_include_scope" value="1" <?php checked($gads_scope_on); ?> />
                Przy następnym logowaniu przez Google dołącz zakres <strong>Google Ads API</strong> (<code>https://www.googleapis.com/auth/adwords</code>)
              </label>
            </p>
            <p><button type="submit" class="button">Zapisz preferencję zakresu (bez ponownego logowania)</button></p>
          </form>
          <form method="post" action="<?php echo esc_url($upsellio_sa_form_action); ?>"<?php echo $ups_managed_google_oauth ? "" : " target=\"_blank\""; ?> style="margin-bottom:16px;">
            <?php wp_nonce_field("upsellio_google_oauth_start_action", "upsellio_google_oauth_start_nonce"); ?>
            <input type="hidden" name="upsellio_google_oauth_start" value="1" />
            <?php if (!$ups_managed_google_oauth) : ?>
            <p style="margin-bottom:10px;">
              <label>
                <input type="checkbox" name="upsellio_google_oauth_use_rest" value="1" <?php checked($g_oauth_use_rest); ?> />
                Ten sam wybór co wyżej: użyj <strong>REST</strong> zamiast <strong>admin.php</strong>
              </label>
            </p>
            <p>
              <label><strong>OAuth Client ID</strong><br />
                <input type="text" name="g_oauth_client_id" class="large-text" value="<?php echo esc_attr($gsc_credentials["client_id"]); ?>" placeholder="xxxx.apps.googleusercontent.com" autocomplete="off" />
              </label>
            </p>
            <p>
              <label><strong>OAuth Client Secret</strong><br />
                <input type="password" name="g_oauth_client_secret" class="large-text" value="<?php echo esc_attr($gsc_credentials["client_secret"]); ?>" placeholder="GOCSPX-..." autocomplete="new-password" />
              </label>
            </p>
            <p>
              <label><strong>Nadpisanie redirect URI</strong> (opcjonalnie — ten sam host co strona; puste = domyślny z niebieskiego pola powyżej)<br />
                <input type="url" name="g_oauth_redirect_uri_override" class="large-text" value="<?php echo esc_attr($g_oauth_redirect_uri_override_val); ?>" placeholder="https://… (wklej dokładnie z Google Authorized redirect URIs)" autocomplete="off" spellcheck="false" />
              </label>
            </p>
            <?php endif; ?>
            <p>
              <label><strong>GSC Property</strong> (opcjonalnie teraz; ten sam co w formularzu niżej)<br />
                <input type="text" name="g_oauth_gsc_property" class="regular-text" value="<?php echo esc_attr($gsc_credentials["property"]); ?>" placeholder="https://twojadomena.pl/ albo sc-domain:twojadomena.pl" />
              </label>
            </p>
            <p>
              <label><strong>ID właściwości GA4</strong> (opcjonalnie; cyfry)<br />
                <input type="text" name="g_oauth_ga4_property_id" class="regular-text" value="<?php echo esc_attr($ga4_property_id_display); ?>" placeholder="np. 123456789" inputmode="numeric" />
              </label>
            </p>
            <p>
              <label>
                <input type="checkbox" name="g_oauth_include_google_ads" value="1" <?php checked($gads_scope_on); ?> />
                Dołącz zakres Google Ads API przy tej autoryzacji (ta sama preferencja co powyżej)
              </label>
            </p>
            <?php if ($ups_managed_google_oauth) : ?>
            <p class="description" style="font-size:12px;margin-top:0;">
              Otworzy się most Upsellio, potem logowanie Google — po zakończeniu token wraca do tej witryny przez zabezpieczony webhook (serwer–serwer).
            </p>
            <?php else : ?>
            <p class="description" style="font-size:12px;margin-top:0;">
              Przekierowanie na Google otwiera się w <strong>nowej karcie</strong>, żeby ta strona została pod ręką. Jeśli przeglądarka blokuje nową kartę, zezwól na wyskakujące okna dla tej domeny lub tymczasowo wyłącz blokadę.
            </p>
            <?php endif; ?>
            <p>
              <button type="submit" class="button button-primary">Zaloguj przez Google i autoryzuj GSC + GA4<?php echo $gads_scope_on ? " + Ads" : ""; ?></button>
            </p>
          </form>
          <?php if ($gsc_credentials["refresh_token"] !== "") : ?>
            <form method="post" action="<?php echo esc_url($upsellio_sa_form_action); ?>" style="display:inline-block;margin-right:8px;">
              <?php wp_nonce_field("upsellio_google_oauth_disconnect_action", "upsellio_google_oauth_disconnect_nonce"); ?>
              <input type="hidden" name="upsellio_google_oauth_disconnect" value="1" />
              <button type="submit" class="button">Odłącz konto Google (usuń refresh token)</button>
            </form>
          <?php endif; ?>
          <hr />
          <h2 style="margin-top:0;">Google Ads API — przygotowanie (OAuth + Developer token)</h2>
          <p style="font-size:13px;color:#3f3f39;">
            Do wywołań Google Ads API potrzebny jest <strong>Developer token</strong> z konta Google Ads (API Center), opcjonalnie <strong>login-customer-id</strong> dla konta menedżerskiego (MCC) oraz <strong>Customer ID</strong> konta reklamowego (10 cyfr).
            Token OAuth musi obejmować zakres <code>adwords</code> — włącz go w preferencji powyżej i ponownie zaloguj przez Google.
          </p>
          <p style="font-size:12px;color:#5f6368;">
            Status integracji: <?php echo $gads_ready ? "<strong style=\"color:#0a0;\">gotowe do zapytań API</strong> (zakres + refresh token + developer token + CID)" : "<strong>niekompletne</strong> — sprawdź tabelę uprawnień, pola poniżej i ewentualnie test połączenia."; ?>
          </p>
          <form method="post" action="<?php echo esc_url($upsellio_sa_form_action); ?>" style="margin-bottom:12px;">
            <?php wp_nonce_field("upsellio_google_ads_config_action", "upsellio_google_ads_config_nonce"); ?>
            <input type="hidden" name="upsellio_google_ads_config_save" value="1" />
            <p>
              <label><strong>Developer token</strong><br />
                <input type="text" name="upsellio_gads_developer_token" class="large-text" value="<?php echo esc_attr($gads_cfg["developer_token"]); ?>" autocomplete="off" spellcheck="false" />
              </label>
            </p>
            <p>
              <label><strong>Customer ID</strong> (tylko cyfry, bez myślników)<br />
                <input type="text" name="upsellio_gads_customer_id" class="regular-text" value="<?php echo esc_attr($gads_cfg["customer_id"]); ?>" placeholder="np. 1234567890" inputmode="numeric" />
              </label>
            </p>
            <p>
              <label><strong>Login Customer ID</strong> (opcjonalnie; MCC — gdy pracujesz z kontem podrzędnym)<br />
                <input type="text" name="upsellio_gads_login_customer_id" class="regular-text" value="<?php echo esc_attr($gads_cfg["login_customer_id"]); ?>" placeholder="puste jeśli nie używasz MCC" inputmode="numeric" />
              </label>
            </p>
            <p>
              <button type="submit" class="button button-primary">Zapisz ustawienia Google Ads</button>
            </p>
          </form>
          <form method="post" action="<?php echo esc_url($upsellio_sa_form_action); ?>" style="margin-bottom:8px;">
            <?php wp_nonce_field("upsellio_google_ads_test_action", "upsellio_google_ads_test_nonce"); ?>
            <input type="hidden" name="upsellio_google_ads_test_submit" value="1" />
            <button type="submit" class="button" <?php echo $gads_cfg["developer_token"] === "" ? "disabled" : ""; ?>>Test: listAccessibleCustomers</button>
            <?php if ($gads_cfg["developer_token"] === "") : ?>
              <span class="description" style="margin-left:8px;">Uzupełnij developer token, aby wysłać zapytanie testowe.</span>
            <?php endif; ?>
          </form>
          <p style="font-size:12px;color:#5f6368;margin-top:0;">Wersja API REST: <code><?php echo esc_html(upsellio_google_ads_api_version()); ?></code> — filtr <code>upsellio_google_ads_api_version</code>.</p>
          <hr />
          <h2 style="margin-top:0;">Google Search Console API (darmowe live dane)</h2>
          <?php if (isset($_GET["upsellio_gsc_synced"])) : ?>
            <div class="notice notice-success inline"><p>Zsynchronizowano live dane z GSC: <?php echo esc_html((string) ((int) $_GET["upsellio_gsc_synced"])); ?> rekordów.</p></div>
          <?php endif; ?>
          <?php if (isset($_GET["upsellio_gsc_trace_id"])) : ?>
            <div class="notice notice-info inline"><p>Trace ID synchronizacji: <code><?php echo esc_html(rawurldecode((string) $_GET["upsellio_gsc_trace_id"])); ?></code></p></div>
          <?php endif; ?>
          <?php if (isset($_GET["upsellio_gsc_logs_cleared"])) : ?>
            <div class="notice notice-success inline"><p>Logi debug GSC zostały wyczyszczone.</p></div>
          <?php endif; ?>
          <?php if (isset($_GET["upsellio_gsc_error"])) : ?>
            <div class="notice notice-error inline"><p>Błąd GSC: <?php echo esc_html(rawurldecode((string) $_GET["upsellio_gsc_error"])); ?></p></div>
          <?php endif; ?>
          <form method="post" action="<?php echo esc_url($upsellio_sa_form_action); ?>">
            <?php wp_nonce_field("upsellio_gsc_sync_action", "upsellio_gsc_sync_nonce"); ?>
            <input type="hidden" name="upsellio_gsc_sync_submit" value="1" />
            <p>
              <label><strong>Google OAuth Client ID</strong><br />
                <input type="text" name="gsc_client_id" class="large-text" value="<?php echo esc_attr($gsc_credentials["client_id"]); ?>" placeholder="xxxx.apps.googleusercontent.com" />
              </label>
            </p>
            <p>
              <label><strong>Google OAuth Client Secret</strong><br />
                <input type="text" name="gsc_client_secret" class="large-text" value="<?php echo esc_attr($gsc_credentials["client_secret"]); ?>" placeholder="GOCSPX-..." />
              </label>
            </p>
            <p>
              <label><strong>Google Refresh Token</strong> (opcjonalnie ręcznie — inaczej ustawia się po „Zaloguj przez Google” powyżej)<br />
                <input type="text" name="gsc_refresh_token" class="large-text" value="<?php echo esc_attr($gsc_credentials["refresh_token"]); ?>" placeholder="1//0g..." />
              </label>
            </p>
            <p>
              <label><strong>GSC Property</strong><br />
                <input type="text" name="gsc_property" class="regular-text" value="<?php echo esc_attr($gsc_credentials["property"]); ?>" placeholder="https://twojadomena.pl/ albo sc-domain:twojadomena.pl" />
              </label>
            </p>
            <p>
              <label><strong>Zakres synchronizacji</strong><br />
                <select name="gsc_sync_days">
                  <option value="7">7 dni</option>
                  <option value="14">14 dni</option>
                  <option value="30" selected>30 dni</option>
                  <option value="60">60 dni</option>
                  <option value="90">90 dni</option>
                </select>
              </label>
            </p>
            <p><button type="submit" class="button button-primary">Połącz i zsynchronizuj live z GSC</button></p>
            <p style="font-size:12px;color:#5f6368;margin-top:8px;">
              API GSC jest darmowe. Najpierw użyj sekcji <strong>logowania przez Google</strong> powyżej (ten sam token obejmuje GSC + GA4). Ręczne wklejanie refresh tokena jest opcjonalne.
            </p>
          </form>
          <hr />
          <h2 style="margin-top:0;">Google Analytics 4 — kanały do CRM (OAuth, bez zewnętrznego skryptu)</h2>
          <?php if (isset($_GET["upsellio_ga4_synced"])) : ?>
            <div class="notice notice-success inline"><p>Zapisano agregaty GA4 (źródło / kampania) do CRM: <?php echo esc_html((string) ((int) $_GET["upsellio_ga4_synced"])); ?> wierszy.</p></div>
          <?php endif; ?>
          <?php if (isset($_GET["upsellio_ga4_trace_id"])) : ?>
            <div class="notice notice-info inline"><p>Trace GA4: <code><?php echo esc_html(rawurldecode((string) $_GET["upsellio_ga4_trace_id"])); ?></code></p></div>
          <?php endif; ?>
          <?php if (isset($_GET["upsellio_ga4_error"])) : ?>
            <div class="notice notice-error inline"><p>Błąd GA4: <?php echo esc_html(rawurldecode((string) $_GET["upsellio_ga4_error"])); ?></p></div>
          <?php endif; ?>
          <p style="font-size:13px;color:#3f3f39;">
            <strong>Google Tag Manager</strong> nie udostępnia API z raportami o konwersjach — dane zbiera GA4. Tu WordPress pobiera raport z <strong>GA4 Data API</strong> przy użyciu konta Google (OAuth), tak jak GSC.
            <strong>Google Ads</strong> to osobne REST API — nagłówki, developer token i diagnostykę skonfigurujesz w sekcji <em>Google Ads API — przygotowanie</em> powyżej.
          </p>
          <form method="post" action="<?php echo esc_url($upsellio_sa_form_action); ?>">
            <?php wp_nonce_field("upsellio_ga4_sync_action", "upsellio_ga4_sync_nonce"); ?>
            <input type="hidden" name="upsellio_ga4_sync_submit" value="1" />
            <p>
              <label><strong>ID właściwości GA4</strong> (tylko cyfry, Admin GA4 → Ustawienia właściwości)<br />
                <input type="text" name="ga4_property_id" class="regular-text" value="<?php echo esc_attr($ga4_property_id_display); ?>" placeholder="np. 123456789" inputmode="numeric" />
              </label>
            </p>
            <p>
              <label><strong>Zakres dat raportu</strong><br />
                <select name="ga4_sync_days">
                  <option value="7" <?php selected($ga4_ui_days, 7); ?>>7 dni</option>
                  <option value="14" <?php selected($ga4_ui_days, 14); ?>>14 dni</option>
                  <option value="30" <?php selected($ga4_ui_days, 30); ?>>30 dni</option>
                  <option value="60" <?php selected($ga4_ui_days, 60); ?>>60 dni</option>
                  <option value="90" <?php selected($ga4_ui_days, 90); ?>>90 dni</option>
                </select>
              </label>
            </p>
            <p style="font-size:12px;color:#5f6368;">
              Domyślnie używane są <strong>Client ID / Secret / Refresh token z formularza GSC</strong> powyżej. Jeśli chcesz inne konto tylko do GA4, uzupełnij pola opcjonalne (nadpisują token tylko dla tego importu):
            </p>
            <p>
              <label><strong>Opcjonalnie: Client ID (tylko GA4)</strong><br />
                <input type="text" name="ga4_oauth_client_id" class="large-text" value="<?php echo esc_attr($ga4_oauth_override["client_id"]); ?>" placeholder="puste = jak GSC" />
              </label>
            </p>
            <p>
              <label><strong>Opcjonalnie: Client Secret (tylko GA4)</strong><br />
                <input type="text" name="ga4_oauth_client_secret" class="large-text" value="<?php echo esc_attr($ga4_oauth_override["client_secret"]); ?>" />
              </label>
            </p>
            <p>
              <label><strong>Opcjonalnie: Refresh token (tylko GA4)</strong><br />
                <input type="text" name="ga4_oauth_refresh_token" class="large-text" value="<?php echo esc_attr($ga4_oauth_override["refresh_token"]); ?>" />
              </label>
            </p>
            <p><button type="submit" class="button button-primary">Pobierz z GA4 i zapisz w CRM</button></p>
            <p style="font-size:12px;color:#5f6368;">Ostatni zapisany sync w CRM: <code><?php echo esc_html($ga4_last !== "" ? $ga4_last : "—"); ?></code>. Przy włączonej automatyzacji dzienny cron spróbuje odświeżyć dane raz dziennie.</p>
          </form>
          <hr />
          <h3 style="margin-bottom:8px;">Logi debug autoryzacji GSC</h3>
          <p style="font-size:12px;color:#5f6368;margin-top:0;">
            Logi pokazują pełny przebieg OAuth i zapytań GSC (sekrety są maskowane). Najnowsze wpisy są na górze.
          </p>
          <form method="post" action="<?php echo esc_url($upsellio_sa_form_action); ?>" style="margin:8px 0 12px;">
            <?php wp_nonce_field("upsellio_gsc_logs_clear_action", "upsellio_gsc_logs_clear_nonce"); ?>
            <input type="hidden" name="upsellio_gsc_logs_clear_submit" value="1" />
            <button type="submit" class="button">Wyczyść logi debug</button>
          </form>
          <?php if (empty($gsc_debug_logs)) : ?>
            <p>Brak logów debug. Uruchom synchronizację, aby wygenerować ślad autoryzacji.</p>
          <?php else : ?>
            <textarea rows="18" class="large-text code" readonly><?php
            $debug_lines = [];
            $logs_for_display = array_reverse($gsc_debug_logs);
            foreach ($logs_for_display as $entry) {
                $time = (string) ($entry["time"] ?? "");
                $trace = (string) ($entry["trace_id"] ?? "");
                $event = (string) ($entry["event"] ?? "");
                $payload = isset($entry["data"]) && is_array($entry["data"]) ? $entry["data"] : [];
                $payload_json = wp_json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                if (!is_string($payload_json)) {
                    $payload_json = "{}";
                }
                $debug_lines[] = "[" . $time . "] [" . ($trace !== "" ? $trace : "-") . "] " . $event . " => " . $payload_json;
            }
            echo esc_textarea(implode("\n", $debug_lines));
            ?></textarea>
          <?php endif; ?>
        </div>

        <div class="ups-import-box">
          <h2 style="margin-top:0;">Import danych słów kluczowych (CSV)</h2>
          <p>Format: <code>keyword,url,position,impressions,clicks,ctr,date</code> (nagłówek opcjonalny).</p>
          <?php if (isset($_GET["upsellio_metrics_imported"])) : ?>
            <div class="notice notice-success inline"><p>Zaimportowano <?php echo esc_html((string) ((int) $_GET["upsellio_metrics_imported"])); ?> rekordów.</p></div>
          <?php endif; ?>
          <form method="post" action="<?php echo esc_url($upsellio_sa_form_action); ?>">
            <?php wp_nonce_field("upsellio_keyword_metrics_action", "upsellio_keyword_metrics_nonce"); ?>
            <input type="hidden" name="upsellio_keyword_metrics_import" value="1" />
            <textarea name="keyword_metrics_csv" rows="8" class="large-text" placeholder="keyword,url,position,impressions,clicks,ctr,date"></textarea>
            <p><button type="submit" class="button button-primary">Importuj metryki keywordów</button></p>
          </form>
        </div>
      </div>
    </div>
    <?php
}

