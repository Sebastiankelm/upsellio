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
    $query = array_merge(["view" => "analytics", "atab" => "today"], $extra);
    return add_query_arg($query, home_url("/crm-app/"));
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
    // Deprecated: analytics moved to CRM app.
    add_action("admin_init", "upsellio_site_analytics_redirect_to_crm_app");
}
add_action("admin_menu", "upsellio_site_analytics_menu");

function upsellio_site_analytics_redirect_to_crm_app(): void
{
    if (!is_admin() || !current_user_can("edit_posts")) {
        return;
    }
    if (!isset($_GET["page"]) || (string) wp_unslash($_GET["page"]) !== upsellio_site_analytics_page_slug()) {
        return;
    }
    $old_section = isset($_GET["section"]) ? sanitize_key((string) wp_unslash($_GET["section"])) : "overview";
    $tab_map = ["overview" => "today", "seo" => "seo", "ads" => "paid", "sales" => "sales"];
    $atab = isset($tab_map[$old_section]) ? $tab_map[$old_section] : "today";
    wp_safe_redirect(add_query_arg(["view" => "analytics", "atab" => $atab], home_url("/crm-app/")));
    exit;
}

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
        "sslverify" => true,
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
                "sslverify" => true,
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
    update_option("upsellio_gsc_sync_days_last", $sync_days, false);
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

function upsellio_gsc_daily_sync_job()
{
    $credentials = upsellio_get_gsc_credentials();
    if (
        !is_array($credentials) ||
        (string) ($credentials["client_id"] ?? "") === "" ||
        (string) ($credentials["client_secret"] ?? "") === "" ||
        (string) ($credentials["refresh_token"] ?? "") === "" ||
        (string) ($credentials["property"] ?? "") === ""
    ) {
        return;
    }

    $sync_days = (int) get_option("upsellio_gsc_sync_days_last", 30);
    $sync_days = in_array($sync_days, [7, 14, 30, 60, 90], true) ? $sync_days : 30;
    $trace_id = "gsc_cron_" . (function_exists("wp_generate_uuid4") ? wp_generate_uuid4() : uniqid("", true));
    $rows = upsellio_gsc_fetch_rows($credentials, $sync_days, $trace_id);
    if (is_wp_error($rows) || $rows === []) {
        if (is_wp_error($rows)) {
            upsellio_gsc_log("gsc.cron.failed", ["message" => $rows->get_error_message()], $trace_id);
        }

        return;
    }

    update_option("upsellio_keyword_metrics_rows", array_values($rows), false);
    update_option("upsellio_keyword_metrics_source", "gsc_live", false);
    update_option("upsellio_keyword_metrics_last_sync", wp_date("Y-m-d H:i:s"), false);
    upsellio_gsc_log("gsc.cron.success", ["rows" => count($rows)], $trace_id);
}

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

function upsellio_handle_site_analytics_goals_submit()
{
    if (!is_admin() || !current_user_can("edit_posts")) {
        return;
    }
    if (!isset($_POST["upsellio_analytics_goals_submit"])) {
        return;
    }
    check_admin_referer("upsellio_analytics_goals_action", "upsellio_analytics_goals_nonce");

    $lead_target = isset($_POST["ups_goal_leads"]) ? max(0, (int) $_POST["ups_goal_leads"]) : 0;
    $won_target = isset($_POST["ups_goal_won"]) ? max(0, (int) $_POST["ups_goal_won"]) : 0;
    $revenue_target = isset($_POST["ups_goal_revenue"]) ? max(0, (float) $_POST["ups_goal_revenue"]) : 0.0;
    update_option("ups_analytics_goal_leads", $lead_target, false);
    update_option("ups_analytics_goal_won", $won_target, false);
    update_option("ups_analytics_goal_revenue", $revenue_target, false);

    wp_safe_redirect(
        add_query_arg(
            [
                "page" => upsellio_site_analytics_page_slug(),
                "ups_goals_saved" => "1",
            ],
            admin_url("admin.php")
        )
    );
    exit;
}
add_action("admin_init", "upsellio_handle_site_analytics_goals_submit");

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
    if ((string) get_option("upsellio_google_ads_include_scope", "1") === "1") {
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
        "sslverify" => true,
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
                "sslverify" => true,
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
    $post_id = (int) ($row["post_id"] ?? 0);
    $ai_suggestions = get_option("ups_ai_page_perf_suggestions", []);
    if ($post_id > 0 && is_array($ai_suggestions) && !empty($ai_suggestions)) {
        if (isset($ai_suggestions[$post_id]) && is_array($ai_suggestions[$post_id])) {
            $actions = isset($ai_suggestions[$post_id]["actions"]) && is_array($ai_suggestions[$post_id]["actions"])
                ? $ai_suggestions[$post_id]["actions"]
                : [];
            if (!empty($actions)) {
                return array_slice(array_map("strval", $actions), 0, 3);
            }
        }
        foreach ($ai_suggestions as $ai_row) {
            if (!is_array($ai_row)) {
                continue;
            }
            if ((int) ($ai_row["post_id"] ?? 0) !== $post_id) {
                continue;
            }
            $actions = isset($ai_row["actions"]) && is_array($ai_row["actions"]) ? $ai_row["actions"] : [];
            if (!empty($actions)) {
                return array_slice(array_map("strval", $actions), 0, 3);
            }
            break;
        }
    }

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

function upsellio_calculate_period_delta_percent($current, $previous)
{
    $current = (float) $current;
    $previous = (float) $previous;
    if ($previous <= 0.0) {
        return $current > 0 ? 100.0 : 0.0;
    }

    return round((($current - $previous) / $previous) * 100, 1);
}

function upsellio_is_anomaly_delta($delta_pct)
{
    return abs((float) $delta_pct) >= 25.0;
}

function upsellio_build_query_to_lead_value_rows(array $keyword_rows, string $from_date): array
{
    if (function_exists("upsellio_analytics_query_lead_value")) {
        $days = 30;
        $from_ts = strtotime($from_date);
        if ($from_ts !== false) {
            $days = max(1, (int) floor((time() - $from_ts) / DAY_IN_SECONDS));
        }
        $data = upsellio_analytics_query_lead_value($days, 1200);
        return (array) ($data["rows"] ?? []);
    }
    $lead_ids = get_posts([
        "post_type" => "lead",
        "post_status" => "publish",
        "posts_per_page" => 1200,
        "date_query" => [[
            "after" => $from_date,
            "inclusive" => true,
        ]],
        "fields" => "ids",
    ]);
    if (!empty($lead_ids)) {
        update_meta_cache("post", array_map("intval", $lead_ids));
    }

    $keyword_index = [];
    foreach (array_slice($keyword_rows, 0, 1200) as $row) {
        if (!is_array($row)) {
            continue;
        }
        $query = trim((string) ($row["keyword"] ?? ""));
        if ($query === "") {
            continue;
        }
        $key = mb_strtolower($query);
        if (!isset($keyword_index[$key])) {
            $keyword_index[$key] = [
                "query" => $query,
                "impressions" => 0,
                "clicks" => 0,
                "leads" => 0,
                "won" => 0,
                "value" => 0.0,
            ];
        }
        $keyword_index[$key]["impressions"] += (int) ($row["impressions"] ?? 0);
        $keyword_index[$key]["clicks"] += (int) ($row["clicks"] ?? 0);
    }

    foreach ($lead_ids as $lead_id) {
        $lead_id = (int) $lead_id;
        $likely_query = trim((string) get_post_meta($lead_id, "_upsellio_lead_gsc_likely_query", true));
        if ($likely_query === "") {
            continue;
        }
        $key = mb_strtolower($likely_query);
        if (!isset($keyword_index[$key])) {
            $keyword_index[$key] = [
                "query" => $likely_query,
                "impressions" => 0,
                "clicks" => 0,
                "leads" => 0,
                "won" => 0,
                "value" => 0.0,
            ];
        }
        $keyword_index[$key]["leads"]++;
        $won_slugs = wp_get_object_terms($lead_id, "lead_status", ["fields" => "slugs"]);
        $is_won = is_array($won_slugs) && in_array("won", $won_slugs, true);
        if ($is_won) {
            $keyword_index[$key]["won"]++;
            $keyword_index[$key]["value"] += (float) get_post_meta($lead_id, "_upsellio_lead_close_value", true);
        }
    }

    $rows = array_values($keyword_index);
    foreach ($rows as &$row) {
        $imp = (int) $row["impressions"];
        $val = (float) $row["value"];
        $row["rpm"] = $imp > 0 ? round(($val / $imp) * 1000, 2) : 0.0;
    }
    unset($row);

    usort($rows, static function ($a, $b) {
        return (($b["value"] ?? 0) <=> ($a["value"] ?? 0))
            ?: (($b["leads"] ?? 0) <=> ($a["leads"] ?? 0))
            ?: (($b["clicks"] ?? 0) <=> ($a["clicks"] ?? 0));
    });

    return array_slice($rows, 0, 40);
}

function upsellio_build_channel_ltv_rows(array $ga4_rows, string $from_date): array
{
    $cache_key = "ups_channel_ltv_rows_" . md5($from_date . "|" . count($ga4_rows));
    $cached = get_transient($cache_key);
    if ($cached !== false && is_array($cached)) {
        return $cached;
    }
    $channels = [];
    foreach ($ga4_rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $source = sanitize_text_field((string) ($row["source"] ?? "(direct)"));
        $medium = sanitize_text_field((string) ($row["medium"] ?? ""));
        $key = strtolower(trim($source . "|" . $medium));
        if (!isset($channels[$key])) {
            $channels[$key] = [
                "source" => $source,
                "medium" => $medium,
                "sessions" => 0,
                "ga4_conversions" => 0,
                "leads" => 0,
                "won" => 0,
                "value" => 0.0,
            ];
        }
        $channels[$key]["sessions"] += (int) ($row["sessions"] ?? 0);
        $channels[$key]["ga4_conversions"] += (int) round((float) ($row["conversions"] ?? 0));
    }

    $lead_ids = get_posts([
        "post_type" => "lead",
        "post_status" => "publish",
        "posts_per_page" => 1200,
        "date_query" => [[
            "after" => $from_date,
            "inclusive" => true,
        ]],
        "fields" => "ids",
    ]);
    if (!empty($lead_ids)) {
        update_meta_cache("post", array_map("intval", $lead_ids));
    }
    foreach ($lead_ids as $lead_id) {
        $lead_id = (int) $lead_id;
        $source = sanitize_text_field((string) get_post_meta($lead_id, "_upsellio_lead_utm_source", true));
        $medium = sanitize_text_field((string) get_post_meta($lead_id, "_upsellio_lead_utm_medium", true));
        $key = strtolower(trim(($source !== "" ? $source : "(direct)") . "|" . $medium));
        if (!isset($channels[$key])) {
            $channels[$key] = [
                "source" => $source !== "" ? $source : "(direct)",
                "medium" => $medium,
                "sessions" => 0,
                "ga4_conversions" => 0,
                "leads" => 0,
                "won" => 0,
                "value" => 0.0,
            ];
        }
        $channels[$key]["leads"]++;
        $won_slugs = wp_get_object_terms($lead_id, "lead_status", ["fields" => "slugs"]);
        $is_won = is_array($won_slugs) && in_array("won", $won_slugs, true);
        if ($is_won) {
            $channels[$key]["won"]++;
            $channels[$key]["value"] += (float) get_post_meta($lead_id, "_upsellio_lead_close_value", true);
        }
    }

    $rows = array_values($channels);
    foreach ($rows as &$row) {
        $sessions = max(0, (int) $row["sessions"]);
        $leads = max(0, (int) $row["leads"]);
        $won = max(0, (int) $row["won"]);
        $value = (float) $row["value"];
        $row["cr_sessions_to_lead"] = $sessions > 0 ? round(($leads / $sessions) * 100, 2) : 0.0;
        $row["ltv_per_session"] = $sessions > 0 ? round($value / $sessions, 2) : 0.0;
        $row["win_rate"] = $leads > 0 ? round(($won / $leads) * 100, 2) : 0.0;
    }
    unset($row);

    usort($rows, static function ($a, $b) {
        return (($b["value"] ?? 0) <=> ($a["value"] ?? 0))
            ?: (($b["leads"] ?? 0) <=> ($a["leads"] ?? 0));
    });
    $result = array_slice($rows, 0, 30);
    set_transient($cache_key, $result, DAY_IN_SECONDS);
    return $result;
}

function upsellio_build_landing_funnel_rows(array $report_rows, string $from_date): array
{
    $rows = [];
    foreach (array_slice($report_rows, 0, 60) as $row) {
        if (!is_array($row)) {
            continue;
        }
        $path = (string) wp_parse_url((string) ($row["url"] ?? ""), PHP_URL_PATH);
        $won = 0;
        $value = 0.0;
        $lead_ids = get_posts([
            "post_type" => "lead",
            "post_status" => "publish",
            "posts_per_page" => 500,
            "date_query" => [[
                "after" => $from_date,
                "inclusive" => true,
            ]],
            "meta_query" => [[
                "key" => "_upsellio_lead_landing_url",
                "value" => $path,
                "compare" => "LIKE",
            ]],
            "fields" => "ids",
        ]);
        foreach ($lead_ids as $lead_id) {
            $lead_id = (int) $lead_id;
            $won_slugs = wp_get_object_terms($lead_id, "lead_status", ["fields" => "slugs"]);
            if (is_array($won_slugs) && in_array("won", $won_slugs, true)) {
                $won++;
                $value += (float) get_post_meta($lead_id, "_upsellio_lead_close_value", true);
            }
        }
        $rows[] = [
            "title" => (string) ($row["title"] ?? ""),
            "path" => $path,
            "impressions" => (int) ($row["impressions"] ?? 0),
            "clicks" => (int) ($row["clicks"] ?? 0),
            "sessions" => (int) ($row["views_30d"] ?? 0),
            "leads" => (int) ($row["leads"] ?? 0),
            "won" => $won,
            "value" => round($value, 2),
        ];
    }
    usort($rows, static function ($a, $b) {
        return (($b["value"] ?? 0) <=> ($a["value"] ?? 0))
            ?: (($b["leads"] ?? 0) <=> ($a["leads"] ?? 0))
            ?: (($b["sessions"] ?? 0) <=> ($a["sessions"] ?? 0));
    });

    return array_slice($rows, 0, 20);
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
        $trend_delta_pct = upsellio_calculate_period_delta_percent($views_last_7, $views_prev_7);
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
            "trend_delta_pct" => $trend_delta_pct,
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

    $prev_dates = upsellio_get_analytics_dates($days * 2);
    $prev_period_dates = array_slice($prev_dates, 0, $days);
    $prev_views_series = upsellio_get_daily_views_series($prev_period_dates);
    $prev_leads_series = upsellio_get_daily_leads_series($prev_period_dates);
    $prev_keyword_series = upsellio_get_daily_keyword_series($keyword_rows, $prev_period_dates);
    $prev_views_total = (int) array_sum($prev_views_series);
    $prev_leads_total = (int) array_sum($prev_leads_series);
    $prev_impressions_total = 0;
    $prev_clicks_total = 0;
    foreach ($prev_keyword_series as $prev_keyword_day) {
        $prev_impressions_total += (int) ($prev_keyword_day["impressions"] ?? 0);
        $prev_clicks_total += (int) ($prev_keyword_day["clicks"] ?? 0);
    }
    $views_delta_pct = upsellio_calculate_period_delta_percent($total_views, $prev_views_total);
    $leads_delta_pct = upsellio_calculate_period_delta_percent($total_leads, $prev_leads_total);
    $impressions_delta_pct = upsellio_calculate_period_delta_percent($total_impressions, $prev_impressions_total);
    $clicks_delta_pct = upsellio_calculate_period_delta_percent($total_clicks, $prev_clicks_total);

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
    $join_cache_suffix = md5(
        wp_json_encode([
            "from" => $from_date,
            "keyword_rows" => count($keyword_rows),
            "report_rows" => count($report_rows),
            "ga4_rows" => count((array) get_option("ups_automation_ga4_daily_aggregates", [])),
            "leads_last_changed" => (string) get_option("upsellio_crm_last_changed", ""),
        ])
    );
    $query_value_rows = get_transient("ups_sa_query_value_" . $join_cache_suffix);
    if (!is_array($query_value_rows)) {
        $query_value_rows = upsellio_build_query_to_lead_value_rows($keyword_rows, $from_date);
        set_transient("ups_sa_query_value_" . $join_cache_suffix, $query_value_rows, HOUR_IN_SECONDS);
    }
    $gsc_credentials = upsellio_get_gsc_credentials();
    $gsc_debug_logs = upsellio_gsc_get_logs();
    $ga4_property_id_display = upsellio_get_ga4_property_id();
    $ga4_oauth_override = upsellio_get_ga4_oauth_override();
    $ga4_last = (string) get_option("ups_automation_ga4_last_sync", "");
    $ga4_daily_aggregates = get_option("ups_automation_ga4_daily_aggregates", []);
    if (!is_array($ga4_daily_aggregates)) {
        $ga4_daily_aggregates = [];
    }
    $ga4_sessions_total = 0;
    $ga4_conversions_total = 0;
    foreach ($ga4_daily_aggregates as $ga4_row) {
        if (!is_array($ga4_row)) {
            continue;
        }
        $ga4_sessions_total += (int) ($ga4_row["sessions"] ?? 0);
        $ga4_conversions_total += (int) ($ga4_row["conversions"] ?? 0);
    }
    $ga4_last_ts = $ga4_last !== "" ? strtotime($ga4_last) : false;
    $ga4_is_fresh = $ga4_last_ts && (time() - (int) $ga4_last_ts) <= DAY_IN_SECONDS;
    $views_primary_label = $ga4_is_fresh && $ga4_sessions_total > 0 ? "GA4 sessions" : "Lokalny licznik odsłon";
    $views_display_total = $ga4_is_fresh && $ga4_sessions_total > 0 ? $ga4_sessions_total : $total_views;
    $conversion_total = $views_display_total > 0 ? round(($total_leads / $views_display_total) * 100, 2) : 0;
    $channel_ltv_rows = get_transient("ups_sa_channel_ltv_" . $join_cache_suffix);
    if (!is_array($channel_ltv_rows)) {
        $channel_ltv_rows = upsellio_build_channel_ltv_rows($ga4_daily_aggregates, $from_date);
        set_transient("ups_sa_channel_ltv_" . $join_cache_suffix, $channel_ltv_rows, HOUR_IN_SECONDS);
    }
    $landing_funnel_rows = get_transient("ups_sa_landing_funnel_" . $join_cache_suffix);
    if (!is_array($landing_funnel_rows)) {
        $landing_funnel_rows = upsellio_build_landing_funnel_rows($report_rows, $from_date);
        set_transient("ups_sa_landing_funnel_" . $join_cache_suffix, $landing_funnel_rows, HOUR_IN_SECONDS);
    }
    $ads_campaign_rows = get_option("ups_ads_campaigns_data", []);
    $ads_search_term_rows = get_option("ups_ads_search_terms_data", []);
    $ads_auction_rows = get_option("ups_ads_auction_data", []);
    $gsc_sitemaps_rows = get_option("ups_gsc_sitemaps_data", []);
    $gsc_sitemaps_last = (string) get_option("ups_gsc_sitemaps_last_sync", "");
    $gsc_url_inspection_rows = get_option("ups_gsc_url_inspection_rows", []);
    $gsc_url_inspection_last = (string) get_option("ups_gsc_url_inspection_last_sync", "");
    $ga4_funnel_snapshot = get_option("ups_ga4_funnel_snapshot", []);
    $ga4_funnel_last = (string) get_option("ups_ga4_funnel_last_sync", "");
    $ga4_cohort_snapshot = get_option("ups_ga4_cohort_snapshot", []);
    $ga4_cohort_last = (string) get_option("ups_ga4_cohort_last_sync", "");
    if (!is_array($ads_campaign_rows)) {
        $ads_campaign_rows = [];
    }
    if (!is_array($ads_search_term_rows)) {
        $ads_search_term_rows = [];
    }
    if (!is_array($ads_auction_rows)) {
        $ads_auction_rows = [];
    }
    if (!is_array($gsc_sitemaps_rows)) {
        $gsc_sitemaps_rows = [];
    }
    if (!is_array($gsc_url_inspection_rows)) {
        $gsc_url_inspection_rows = [];
    }
    if (!is_array($ga4_funnel_snapshot)) {
        $ga4_funnel_snapshot = [];
    }
    if (!is_array($ga4_cohort_snapshot)) {
        $ga4_cohort_snapshot = [];
    }
    $ads_campaign_join_rows = get_transient("ups_sa_ads_join_" . $join_cache_suffix);
    if (!is_array($ads_campaign_join_rows)) {
        $ads_campaign_join_rows = [];
        foreach ($ads_campaign_rows as $ads_campaign) {
            if (!is_array($ads_campaign)) {
                continue;
            }
            $campaign_name = sanitize_text_field((string) ($ads_campaign["name"] ?? ""));
            if ($campaign_name === "") {
                continue;
            }
            $campaign_key = mb_strtolower($campaign_name);
            $lead_ids = get_posts([
                "post_type" => "lead",
                "post_status" => "publish",
                "posts_per_page" => 800,
                "date_query" => [[
                    "after" => $from_date,
                    "inclusive" => true,
                ]],
                "meta_query" => [[
                    "key" => "_upsellio_lead_utm_campaign",
                    "value" => $campaign_name,
                    "compare" => "LIKE",
                ]],
                "fields" => "ids",
            ]);
            $leads_count = count($lead_ids);
            $won_count = 0;
            $won_value = 0.0;
            foreach ($lead_ids as $lead_id) {
                $lead_id = (int) $lead_id;
                $won_slugs = wp_get_object_terms($lead_id, "lead_status", ["fields" => "slugs"]);
                if (is_array($won_slugs) && in_array("won", $won_slugs, true)) {
                    $won_count++;
                    $won_value += (float) get_post_meta($lead_id, "_upsellio_lead_close_value", true);
                }
            }
            $spend = (float) ($ads_campaign["cost_pln"] ?? 0);
            $ads_campaign_join_rows[] = [
                "campaign" => $campaign_name,
                "spend" => round($spend, 2),
                "clicks" => (int) ($ads_campaign["clicks"] ?? 0),
                "conversions" => (float) ($ads_campaign["conversions"] ?? 0),
                "leads" => $leads_count,
                "won" => $won_count,
                "value" => round($won_value, 2),
                "cac" => $won_count > 0 ? round($spend / $won_count, 2) : 0.0,
                "roas" => $spend > 0 ? round($won_value / $spend, 2) : 0.0,
                "campaign_key" => $campaign_key,
            ];
        }
        usort($ads_campaign_join_rows, static function ($a, $b) {
            return (($b["value"] ?? 0) <=> ($a["value"] ?? 0))
                ?: (($b["spend"] ?? 0) <=> ($a["spend"] ?? 0));
        });
        $ads_campaign_join_rows = array_slice($ads_campaign_join_rows, 0, 30);
        set_transient("ups_sa_ads_join_" . $join_cache_suffix, $ads_campaign_join_rows, HOUR_IN_SECONDS);
    }

    $anomalies = [];
    $delta_map = [
        "views" => $views_delta_pct,
        "leads" => $leads_delta_pct,
        "impressions" => $impressions_delta_pct,
        "clicks" => $clicks_delta_pct,
    ];
    foreach ($delta_map as $metric => $delta_pct) {
        if (!upsellio_is_anomaly_delta($delta_pct)) {
            continue;
        }
        $anomalies[] = [
            "metric" => $metric,
            "delta_pct" => $delta_pct,
            "direction" => $delta_pct >= 0 ? "up" : "down",
            "severity" => abs($delta_pct) >= 40 ? "high" : "medium",
            "suggestion" => $delta_pct < 0
                ? "Spadek " . $metric . " wymaga audytu kanału i landing pages."
                : "Silny wzrost " . $metric . " — rozważ skalowanie budżetu / contentu.",
        ];
    }

    $goal_target_leads = (int) get_option("ups_analytics_goal_leads", 0);
    $goal_target_won = (int) get_option("ups_analytics_goal_won", 0);
    $goal_target_revenue = (float) get_option("ups_analytics_goal_revenue", 0);
    $month_start = wp_date("Y-m-01");
    $lead_month_ids = get_posts([
        "post_type" => "lead",
        "post_status" => "publish",
        "posts_per_page" => 1200,
        "date_query" => [[
            "after" => $month_start,
            "inclusive" => true,
        ]],
        "fields" => "ids",
    ]);
    $goal_current_leads = count($lead_month_ids);
    $goal_current_won = 0;
    $goal_current_revenue = 0.0;
    foreach ($lead_month_ids as $lead_month_id) {
        $lead_month_id = (int) $lead_month_id;
        $won_slugs = wp_get_object_terms($lead_month_id, "lead_status", ["fields" => "slugs"]);
        if (is_array($won_slugs) && in_array("won", $won_slugs, true)) {
            $goal_current_won++;
            $goal_current_revenue += (float) get_post_meta($lead_month_id, "_upsellio_lead_close_value", true);
        }
    }
    $days_in_month = (int) wp_date("t");
    $day_of_month = (int) wp_date("j");
    $days_left = max(0, $days_in_month - $day_of_month);
    $ga4_ui_days = (int) get_option("upsellio_ga4_sync_days_last", 30);
    $ga4_ui_days = in_array($ga4_ui_days, [7, 14, 30, 60, 90], true) ? $ga4_ui_days : 30;
    $google_perm = upsellio_google_get_permission_snapshot();
    $gads_cfg = upsellio_google_ads_get_settings();
    $gads_scope_on = (string) get_option("upsellio_google_ads_include_scope", "1") === "1";
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
    $ads_campaigns_synced = (string) get_option("ups_ads_campaigns_synced", "");
    $ads_search_terms_synced = (string) get_option("ups_ads_search_terms_synced", "");
    $ads_auction_synced = (string) get_option("ups_ads_auction_synced", "");
    $gsc_last_ts = $last_sync !== "" ? strtotime($last_sync) : false;
    $gsc_age_days = $gsc_last_ts ? max(0, (int) floor((time() - (int) $gsc_last_ts) / DAY_IN_SECONDS)) : null;
    $ga4_age_hours = $ga4_last_ts ? max(0, (int) floor((time() - (int) $ga4_last_ts) / HOUR_IN_SECONDS)) : null;
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
        .ups-freshness{margin:10px 0 14px;padding:10px 12px;border:1px solid #d9dde3;border-radius:10px;background:#fff;display:flex;gap:12px;flex-wrap:wrap}
        .ups-freshness span{font-size:12px}
        .ups-freshness .is-stale{color:#b45309;font-weight:700}
        .ups-freshness .is-ok{color:#027a48;font-weight:700}
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
        .ups-trend-chart{height:220px}
        .ups-trend-meta{display:flex;justify-content:space-between;gap:10px;font-size:12px;color:#5f6368;margin-top:8px}
        .ups-priority-score{font-size:20px;font-weight:700}
        .ups-priority-high{color:#b42318}
        .ups-priority-mid{color:#b45309}
        .ups-priority-low{color:#027a48}
        .ups-chip.alert{background:#fff7ed;color:#b45309}
        .ups-chip.alert-high{background:#fff1f1;color:#b42318}
        .ups-goals-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}
        .ups-goal-item{padding:10px;border:1px solid #e5e7eb;border-radius:10px;background:#fafafa}
        .ups-reco-panel{margin-top:14px;background:#fff;border:1px solid #d9dde3;border-radius:14px;padding:12px}
        .ups-reco-panel ul{margin:0;padding-left:18px;display:grid;gap:6px}
        .ups-reco-panel li{font-size:13px}
        @media(max-width:1100px){.ups-analytics-kpi{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media(max-width:1100px){.ups-trend-grid{grid-template-columns:1fr}}
      </style>
      <div class="ups-analytics-wrap">
        <h1>Analityka SEO i konwersji</h1>
        <p>Panel łączy odsłony stron, trendy ruchu, pozycje słów kluczowych (z importu CSV) i konwersje z CRM, a następnie generuje rekomendacje optymalizacji per URL.</p>
        <p><strong>Źródło danych keywordów:</strong> <?php echo esc_html($source_label); ?><?php echo $last_sync !== "" ? " · ostatnia synchronizacja: " . esc_html($last_sync) : ""; ?></p>
        <div class="ups-freshness">
          <span>
            <strong>GSC:</strong>
            <?php if ($last_sync !== "") : ?>
              <span class="<?php echo ($gsc_age_days !== null && $gsc_age_days > 4) ? "is-stale" : "is-ok"; ?>">
                <?php echo esc_html($last_sync); ?><?php echo $gsc_age_days !== null ? " (" . esc_html((string) $gsc_age_days) . " dni temu)" : ""; ?>
              </span>
            <?php else : ?>
              <span class="is-stale">brak synchronizacji</span>
            <?php endif; ?>
          </span>
          <span>
            <strong>GA4:</strong>
            <?php if ($ga4_last !== "") : ?>
              <span class="<?php echo ($ga4_age_hours !== null && $ga4_age_hours > 24) ? "is-stale" : "is-ok"; ?>">
                <?php echo esc_html($ga4_last); ?><?php echo $ga4_age_hours !== null ? " (" . esc_html((string) $ga4_age_hours) . " h temu)" : ""; ?>
              </span>
            <?php else : ?>
              <span class="is-stale">brak synchronizacji</span>
            <?php endif; ?>
          </span>
        </div>
        <?php
        $sa_weekly_brief = function_exists("upsellio_get_latest_weekly_brief") ? upsellio_get_latest_weekly_brief() : ["html" => "", "at" => 0];
        $sa_wb_html = (string) ($sa_weekly_brief["html"] ?? "");
        $sa_wb_at = (int) ($sa_weekly_brief["at"] ?? 0);
        ?>
        <?php if ($sa_wb_html !== "" && $sa_wb_at > 0 && (time() - $sa_wb_at) < (5 * DAY_IN_SECONDS)) : ?>
          <?php $sa_wb_age = max(0, (int) floor((time() - $sa_wb_at) / DAY_IN_SECONDS)); ?>
          <section class="ups-analytics-card" style="background:linear-gradient(135deg,#fff7ed,#fff);border-left:4px solid #f97316;">
            <div style="font-size:11px;font-weight:800;color:#9a3412;letter-spacing:.5px;text-transform:uppercase;margin-bottom:6px;">
              🎯 Brief AI · <?php echo $sa_wb_age === 0 ? esc_html__("dzisiaj", "upsellio") : esc_html(sprintf(__("%d dni temu", "upsellio"), $sa_wb_age)); ?>
            </div>
            <h3 style="margin:0 0 8px;font-size:18px;"><?php esc_html_e("Twój brief sprzedażowy", "upsellio"); ?></h3>
            <div class="ups-weekly-brief-content" style="font-size:14px;line-height:1.55;"><?php echo wp_kses_post($sa_wb_html); ?></div>
          </section>
        <?php endif; ?>

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
          <div class="ups-analytics-card"><div class="ups-analytics-label">Wyświetlenia</div><div class="ups-analytics-value"><?php echo esc_html((string) $views_display_total); ?></div><div class="ups-analytics-sub">Źródło: <?php echo esc_html($views_primary_label); ?> <span class="ups-chip <?php echo $views_delta_pct >= 0 ? "up" : "down"; ?>"><?php echo $views_delta_pct >= 0 ? "+" : ""; ?><?php echo esc_html((string) $views_delta_pct); ?>% vs poprzedni okres</span></div></div>
          <div class="ups-analytics-card"><div class="ups-analytics-label">Leady</div><div class="ups-analytics-value"><?php echo esc_html((string) $total_leads); ?></div><div class="ups-analytics-sub">Atrybucja po landing URL <span class="ups-chip <?php echo $leads_delta_pct >= 0 ? "up" : "down"; ?>"><?php echo $leads_delta_pct >= 0 ? "+" : ""; ?><?php echo esc_html((string) $leads_delta_pct); ?>%</span></div></div>
          <div class="ups-analytics-card"><div class="ups-analytics-label">Konwersja</div><div class="ups-analytics-value"><?php echo esc_html((string) $conversion_total); ?>%</div><div class="ups-analytics-sub">Leady / wyświetlenia</div></div>
          <div class="ups-analytics-card"><div class="ups-analytics-label">Śr. pozycja</div><div class="ups-analytics-value"><?php echo esc_html($avg_position_total > 0 ? (string) $avg_position_total : "—"); ?></div><div class="ups-analytics-sub">Z zaimportowanych słów kluczowych</div></div>
          <div class="ups-analytics-card"><div class="ups-analytics-label">CTR</div><div class="ups-analytics-value"><?php echo esc_html((string) $ctr_total); ?>%</div><div class="ups-analytics-sub"><?php echo esc_html((string) $total_clicks); ?> kliknięć / <?php echo esc_html((string) $total_impressions); ?> wyświetleń <span class="ups-chip <?php echo $clicks_delta_pct >= 0 ? "up" : "down"; ?>"><?php echo $clicks_delta_pct >= 0 ? "+" : ""; ?><?php echo esc_html((string) $clicks_delta_pct); ?>%</span><?php echo $ga4_is_fresh ? " · GA4 conv: " . esc_html((string) $ga4_conversions_total) : ""; ?></div></div>
        </div>

        <div class="ups-analytics-card" style="margin-top:12px;">
          <h2 style="margin-top:0;">Cel miesięczny i pacing</h2>
          <form method="post" action="<?php echo esc_url($upsellio_sa_form_action); ?>" style="margin-bottom:10px;">
            <?php wp_nonce_field("upsellio_analytics_goals_action", "upsellio_analytics_goals_nonce"); ?>
            <input type="hidden" name="upsellio_analytics_goals_submit" value="1" />
            <div class="ups-goals-grid">
              <div class="ups-goal-item"><label>Target leadów<br /><input type="number" min="0" name="ups_goal_leads" value="<?php echo esc_attr((string) $goal_target_leads); ?>" /></label></div>
              <div class="ups-goal-item"><label>Target wygranych<br /><input type="number" min="0" name="ups_goal_won" value="<?php echo esc_attr((string) $goal_target_won); ?>" /></label></div>
              <div class="ups-goal-item"><label>Target przychodu (PLN)<br /><input type="number" min="0" step="0.01" name="ups_goal_revenue" value="<?php echo esc_attr((string) $goal_target_revenue); ?>" /></label></div>
            </div>
            <p><button type="submit" class="button button-primary">Zapisz cele</button></p>
          </form>
          <p><strong>Aktualnie (miesiąc):</strong> leady <?php echo esc_html((string) $goal_current_leads); ?> / wygrane <?php echo esc_html((string) $goal_current_won); ?> / przychód <?php echo esc_html(number_format_i18n($goal_current_revenue, 2)); ?> zł</p>
          <p><strong>Pozostało dni:</strong> <?php echo esc_html((string) $days_left); ?> · Potrzebne tempo dzienne:
            leady <?php echo esc_html((string) ($days_left > 0 && $goal_target_leads > $goal_current_leads ? ceil(($goal_target_leads - $goal_current_leads) / $days_left) : 0)); ?>,
            wygrane <?php echo esc_html((string) ($days_left > 0 && $goal_target_won > $goal_current_won ? ceil(($goal_target_won - $goal_current_won) / $days_left) : 0)); ?>,
            przychód <?php echo esc_html(number_format_i18n($days_left > 0 && $goal_target_revenue > $goal_current_revenue ? (($goal_target_revenue - $goal_current_revenue) / $days_left) : 0, 2)); ?> zł.
          </p>
          <?php
          $days_in_month = (int) wp_date("t");
          $day_of_month = (int) wp_date("j");
          $expected_pct = $days_in_month > 0 ? ($day_of_month / $days_in_month) * 100 : 0;
          $goal_rows = [
              ["label" => "Leady", "current" => (float) $goal_current_leads, "target" => (float) $goal_target_leads, "unit" => ""],
              ["label" => "Wygrane", "current" => (float) $goal_current_won, "target" => (float) $goal_target_won, "unit" => ""],
              ["label" => "Przychód", "current" => (float) $goal_current_revenue, "target" => (float) $goal_target_revenue, "unit" => "zł"],
          ];
          ?>
          <?php foreach ($goal_rows as $gr) : ?>
            <?php
            $pct = $gr["target"] > 0 ? min(110, ($gr["current"] / $gr["target"]) * 100) : 0;
            $on_track = $pct >= ($expected_pct - 5);
            $bar_color = $pct >= 100 ? "#15803d" : ($on_track ? "#0d9488" : ($pct > ($expected_pct - 20) ? "#f97316" : "#d94c4c"));
            $left_days = max(1, $days_in_month - $day_of_month);
            $needed_per_day = $gr["target"] > $gr["current"] ? ceil(($gr["target"] - $gr["current"]) / $left_days) : 0;
            ?>
            <div style="margin-bottom:14px;">
              <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;">
                <strong><?php echo esc_html((string) $gr["label"]); ?></strong>
                <span><?php echo esc_html(number_format_i18n($gr["current"], 0)); ?> / <?php echo esc_html(number_format_i18n($gr["target"], 0)); ?> <?php echo esc_html((string) $gr["unit"]); ?> (<?php echo esc_html(number_format_i18n($pct, 1)); ?>%)</span>
              </div>
              <div style="position:relative;height:14px;background:#e5e7eb;border-radius:7px;overflow:hidden;">
                <div style="position:absolute;left:<?php echo esc_attr((string) $expected_pct); ?>%;top:-2px;bottom:-2px;width:2px;background:#666;z-index:2"></div>
                <div style="height:100%;width:<?php echo esc_attr((string) $pct); ?>%;background:<?php echo esc_attr($bar_color); ?>;transition:width .3s"></div>
              </div>
              <small style="color:var(--text-2);font-size:11px;">
                <?php if ($needed_per_day > 0) : ?>
                  <?php echo esc_html(sprintf(__("Tempo wymagane: %d/dzień", "upsellio"), (int) $needed_per_day)); ?>
                <?php else : ?>
                  <?php esc_html_e("✓ Cel osiągnięty", "upsellio"); ?>
                <?php endif; ?>
              </small>
            </div>
          <?php endforeach; ?>
        </div>

        <?php if (!empty($anomalies)) : ?>
        <?php $ai_anomaly_explanations = get_option("ups_ai_anomaly_explanations", []); ?>
        <div class="ups-reco-panel">
          <h2 style="margin-top:0;">Anomalie i alerty</h2>
          <ul>
            <?php foreach ($anomalies as $anomaly) : ?>
              <li>
                <span class="ups-chip <?php echo esc_attr($anomaly["delta_pct"] >= 0 ? "up" : "down"); ?> <?php echo esc_attr($anomaly["severity"] === "high" ? "alert-high" : "alert"); ?>">
                  <?php echo esc_html((string) strtoupper((string) $anomaly["metric"])); ?>: <?php echo ($anomaly["delta_pct"] >= 0 ? "+" : "") . esc_html((string) $anomaly["delta_pct"]); ?>%
                </span>
                — <?php echo esc_html((string) $anomaly["suggestion"]); ?>
                <?php
                $aikey = md5(serialize($anomaly));
                if (is_array($ai_anomaly_explanations) && isset($ai_anomaly_explanations[$aikey])) :
                  $aiexpl = $ai_anomaly_explanations[$aikey];
                ?>
                  <div class="ai-explanation" style="margin-top:8px;padding:10px;background:#fff7ed;border-left:3px solid #f97316;">
                    <p><strong>Co:</strong> <?php echo esc_html((string) ($aiexpl["what"] ?? "")); ?></p>
                    <p><strong>Dlaczego:</strong> <?php echo esc_html((string) ($aiexpl["why"] ?? "")); ?></p>
                    <p><strong>Akcja:</strong> <?php echo esc_html((string) ($aiexpl["action"] ?? "")); ?></p>
                  </div>
                <?php else : ?>
                  <button type="button" class="button button-secondary" style="margin-top:8px"
                          onclick="upsellioExplainAnomaly('<?php echo esc_js($aikey); ?>', this)">
                    🤖 <?php esc_html_e("Wygeneruj wyjaśnienie AI", "upsellio"); ?>
                  </button>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>

        <div class="ups-analytics-card" style="margin-top:14px;">
          <h2 style="margin-top:0;">GA4 Funnel Definitions (MVP)</h2>
          <p class="ups-analytics-sub">Last sync: <?php echo $ga4_funnel_last !== "" ? esc_html($ga4_funnel_last) : "brak"; ?></p>
          <table class="ups-analytics-table">
            <thead>
              <tr>
                <th>Event</th>
                <th>Count (30 dni)</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach (["page_view", "cta_click", "form_start", "generate_lead"] as $step_event) : ?>
                <tr>
                  <td><?php echo esc_html($step_event); ?></td>
                  <td><?php echo esc_html((string) (int) ($ga4_funnel_snapshot[$step_event] ?? 0)); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="ups-analytics-card" style="margin-top:14px;">
          <h2 style="margin-top:0;">GA4 Cohort Snapshot (new vs returning)</h2>
          <p class="ups-analytics-sub">Last sync: <?php echo $ga4_cohort_last !== "" ? esc_html($ga4_cohort_last) : "brak"; ?></p>
          <table class="ups-analytics-table">
            <thead>
              <tr>
                <th>Segment</th>
                <th>Sessions</th>
                <th>Conversions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($ga4_cohort_snapshot)) : ?>
                <tr><td colspan="3"><em>Brak danych cohort.</em></td></tr>
              <?php else : ?>
                <?php foreach ($ga4_cohort_snapshot as $cohort_row) : ?>
                  <tr>
                    <td><?php echo esc_html((string) ($cohort_row["segment"] ?? "")); ?></td>
                    <td><?php echo esc_html((string) (int) ($cohort_row["sessions"] ?? 0)); ?></td>
                    <td><?php echo esc_html((string) (int) ($cohort_row["conversions"] ?? 0)); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div class="ups-analytics-card" style="margin-top:14px;">
          <h2 style="margin-top:0;">GSC URL Inspection Health</h2>
          <p class="ups-analytics-sub">Last sync: <?php echo $gsc_url_inspection_last !== "" ? esc_html($gsc_url_inspection_last) : "brak"; ?></p>
          <table class="ups-analytics-table">
            <thead>
              <tr>
                <th>URL</th>
                <th>Verdict</th>
                <th>Coverage state</th>
                <th>Last crawl</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($gsc_url_inspection_rows)) : ?>
                <tr><td colspan="4"><em>Brak danych URL Inspection.</em></td></tr>
              <?php else : ?>
                <?php foreach (array_slice($gsc_url_inspection_rows, 0, 20) as $inspection_row) : ?>
                  <tr>
                    <td><?php echo esc_html((string) wp_parse_url((string) ($inspection_row["url"] ?? ""), PHP_URL_PATH)); ?></td>
                    <td><?php echo esc_html((string) ($inspection_row["verdict"] ?? "—")); ?></td>
                    <td><?php echo esc_html((string) ($inspection_row["coverage_state"] ?? "—")); ?></td>
                    <td><?php echo esc_html((string) ($inspection_row["last_crawl_time"] ?? "—")); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div class="ups-analytics-card" style="margin-top:14px;">
          <h2 style="margin-top:0;">GSC Sitemap Monitoring</h2>
          <p class="ups-analytics-sub">Last sync: <?php echo $gsc_sitemaps_last !== "" ? esc_html($gsc_sitemaps_last) : "brak"; ?></p>
          <table class="ups-analytics-table">
            <thead>
              <tr>
                <th>Sitemap</th>
                <th>Typ</th>
                <th>Ostatnio przesłana</th>
                <th>Pobrana</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($gsc_sitemaps_rows)) : ?>
                <tr><td colspan="4"><em>Brak danych sitemap.</em></td></tr>
              <?php else : ?>
                <?php foreach (array_slice($gsc_sitemaps_rows, 0, 20) as $sitemap_row) : ?>
                  <tr>
                    <td><?php echo esc_html((string) ($sitemap_row["path"] ?? "—")); ?></td>
                    <td><?php echo esc_html((string) ($sitemap_row["type"] ?? "—")); ?></td>
                    <td><?php echo esc_html((string) ($sitemap_row["lastSubmitted"] ?? "—")); ?></td>
                    <td><?php echo esc_html((string) ($sitemap_row["lastDownloaded"] ?? "—")); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div class="ups-trend-grid">
          <div class="ups-trend-card">
            <h2 class="ups-trend-title">Trend dzień-po-dniu: wyświetlenia</h2><div id="ups-chart-views" class="ups-trend-chart"></div>
            <div class="ups-trend-meta"><span>max: <?php echo esc_html((string) $max_daily_views); ?></span><span>dni: <?php echo esc_html((string) count($dates)); ?></span></div>
          </div>
          <div class="ups-trend-card">
            <h2 class="ups-trend-title">Trend dzień-po-dniu: leady</h2><div id="ups-chart-leads" class="ups-trend-chart"></div>
            <div class="ups-trend-meta"><span>max: <?php echo esc_html((string) $max_daily_leads); ?></span><span>dni: <?php echo esc_html((string) count($dates)); ?></span></div>
          </div>
          <div class="ups-trend-card">
            <h2 class="ups-trend-title">Trend dzień-po-dniu: impressions</h2><div id="ups-chart-impressions" class="ups-trend-chart"></div>
            <div class="ups-trend-meta"><span>max: <?php echo esc_html((string) $max_daily_impressions); ?></span><span>dane z CSV/API</span></div>
          </div>
          <div class="ups-trend-card">
            <h2 class="ups-trend-title">Trend dzień-po-dniu: kliknięcia</h2><div id="ups-chart-clicks" class="ups-trend-chart"></div>
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
          <?php $page_perf_suggestions = get_option("ups_ai_page_perf_suggestions", []); ?>
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
                $row_post_id = (int) ($row["post_id"] ?? 0);
                $row_perf_class = "";
                if ($row_post_id > 0 && is_array($page_perf_suggestions) && isset($page_perf_suggestions[$row_post_id]) && is_array($page_perf_suggestions[$row_post_id])) {
                    $row_perf_class = sanitize_key((string) ($page_perf_suggestions[$row_post_id]["classification"] ?? ""));
                } elseif (is_array($page_perf_suggestions)) {
                    foreach ($page_perf_suggestions as $pp_item) {
                        if (!is_array($pp_item) || (int) ($pp_item["post_id"] ?? 0) !== $row_post_id) {
                            continue;
                        }
                        $row_perf_class = sanitize_key((string) ($pp_item["classification"] ?? ""));
                        break;
                    }
                }
                ?>
                <tr>
                  <td>
                    <strong><?php echo esc_html($row["title"]); ?></strong><br />
                    <a href="<?php echo esc_url($row["url"]); ?>" target="_blank" rel="noopener" class="ups-mono"><?php echo esc_html((string) wp_parse_url($row["url"], PHP_URL_PATH)); ?></a>
                    <?php if ($row_perf_class !== "") : ?>
                      <div style="margin-top:6px"><span class="ups-chip flat"><?php echo esc_html(strtoupper($row_perf_class)); ?></span></div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php echo esc_html((string) $row["views_30d"]); ?> wyświetleń<br />
                    <span class="ups-chip <?php echo esc_attr($trend_class); ?> <?php echo upsellio_is_anomaly_delta((float) ($row["trend_delta_pct"] ?? 0)) ? "alert" : ""; ?>">
                      trend 7d: <?php echo (int) $row["trend_delta"] > 0 ? "+" : ""; ?><?php echo esc_html((string) $row["trend_delta"]); ?> (<?php echo ((float) ($row["trend_delta_pct"] ?? 0)) >= 0 ? "+" : ""; ?><?php echo esc_html((string) ($row["trend_delta_pct"] ?? 0)); ?>%)
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

        <div class="ups-analytics-card" style="margin-top:14px;">
          <h2 style="margin-top:0;">Słowa kluczowe → Leady → Wartość zamknięcia</h2>
          <p class="ups-analytics-sub">Last sync: <?php echo $last_sync !== "" ? esc_html($last_sync) : "brak"; ?></p>
          <table class="ups-analytics-table">
            <thead>
              <tr>
                <th>Query</th>
                <th>Impressions</th>
                <th>Clicks</th>
                <th>Leady</th>
                <th>Wygrane</th>
                <th>Wartość</th>
                <th>RPM</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($query_value_rows)) : ?>
                <tr><td colspan="7"><em>Brak danych join dla wybranego okresu.</em></td></tr>
              <?php else : ?>
                <?php foreach ($query_value_rows as $join_row) : ?>
                  <tr>
                    <td><?php echo esc_html((string) ($join_row["query"] ?? "")); ?></td>
                    <td><?php echo esc_html((string) (int) ($join_row["impressions"] ?? 0)); ?></td>
                    <td><?php echo esc_html((string) (int) ($join_row["clicks"] ?? 0)); ?></td>
                    <td><?php echo esc_html((string) (int) ($join_row["leads"] ?? 0)); ?></td>
                    <td><?php echo esc_html((string) (int) ($join_row["won"] ?? 0)); ?></td>
                    <td><?php echo esc_html(number_format_i18n((float) ($join_row["value"] ?? 0), 2)); ?> zł</td>
                    <td><?php echo esc_html(number_format_i18n((float) ($join_row["rpm"] ?? 0), 2)); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div class="ups-analytics-card" style="margin-top:14px;">
          <h2 style="margin-top:0;">Kanał (GA4) → Leady → LTV</h2>
          <p class="ups-analytics-sub">Last sync: <?php echo $ga4_last !== "" ? esc_html($ga4_last) : "brak"; ?></p>
          <table class="ups-analytics-table">
            <thead>
              <tr>
                <th>Source / medium</th>
                <th>Sessions</th>
                <th>GA4 conv</th>
                <th>Leady</th>
                <th>Wygrane</th>
                <th>Wartość</th>
                <th>CR</th>
                <th>LTV / session</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($channel_ltv_rows)) : ?>
                <tr><td colspan="8"><em>Brak danych kanałów.</em></td></tr>
              <?php else : ?>
                <?php foreach ($channel_ltv_rows as $channel_row) : ?>
                  <tr>
                    <td><?php echo esc_html((string) $channel_row["source"] . " / " . (string) $channel_row["medium"]); ?></td>
                    <td><?php echo esc_html((string) (int) $channel_row["sessions"]); ?></td>
                    <td><?php echo esc_html((string) (int) $channel_row["ga4_conversions"]); ?></td>
                    <td><?php echo esc_html((string) (int) $channel_row["leads"]); ?></td>
                    <td><?php echo esc_html((string) (int) $channel_row["won"]); ?></td>
                    <td><?php echo esc_html(number_format_i18n((float) $channel_row["value"], 2)); ?> zł</td>
                    <td><?php echo esc_html(number_format_i18n((float) $channel_row["cr_sessions_to_lead"], 2)); ?>%</td>
                    <td><?php echo esc_html(number_format_i18n((float) $channel_row["ltv_per_session"], 2)); ?> zł</td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div class="ups-analytics-card" style="margin-top:14px;">
          <h2 style="margin-top:0;">Landing pages → Funnel</h2>
          <p class="ups-analytics-sub">Last sync: GSC <?php echo $last_sync !== "" ? esc_html($last_sync) : "brak"; ?> · GA4 <?php echo $ga4_last !== "" ? esc_html($ga4_last) : "brak"; ?></p>
          <table class="ups-analytics-table">
            <thead>
              <tr>
                <th>Landing</th>
                <th>Impressions</th>
                <th>Clicks</th>
                <th>Sessions</th>
                <th>Leady</th>
                <th>Wygrane</th>
                <th>Wartość</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($landing_funnel_rows as $funnel_row) : ?>
                <tr>
                  <td><?php echo esc_html((string) $funnel_row["path"]); ?></td>
                  <td><?php echo esc_html((string) (int) $funnel_row["impressions"]); ?></td>
                  <td><?php echo esc_html((string) (int) $funnel_row["clicks"]); ?></td>
                  <td><?php echo esc_html((string) (int) $funnel_row["sessions"]); ?></td>
                  <td><a href="<?php echo esc_url(add_query_arg(["post_type" => "lead", "s" => (string) $funnel_row["path"]], admin_url("edit.php"))); ?>"><?php echo esc_html((string) (int) $funnel_row["leads"]); ?></a></td>
                  <td><?php echo esc_html((string) (int) $funnel_row["won"]); ?></td>
                  <td><?php echo esc_html(number_format_i18n((float) $funnel_row["value"], 2)); ?> zł</td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="ups-analytics-card" style="margin-top:14px;">
          <h2 style="margin-top:0;">Google Ads: kampania → lead → ROAS</h2>
          <p class="ups-analytics-sub">Last sync: <?php echo $ads_campaigns_synced !== "" ? esc_html($ads_campaigns_synced) : "brak"; ?></p>
          <table class="ups-analytics-table">
            <thead>
              <tr>
                <th>Kampania</th>
                <th>Spend</th>
                <th>Kliknięcia</th>
                <th>Leady</th>
                <th>Wygrane</th>
                <th>Wartość</th>
                <th>CAC</th>
                <th>ROAS</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($ads_campaign_join_rows)) : ?>
                <tr><td colspan="8"><em>Brak danych Ads (uruchom sync kampanii).</em></td></tr>
              <?php else : ?>
                <?php foreach ($ads_campaign_join_rows as $ads_row) : ?>
                  <tr>
                    <td><?php echo esc_html((string) $ads_row["campaign"]); ?></td>
                    <td><?php echo esc_html(number_format_i18n((float) $ads_row["spend"], 2)); ?> zł</td>
                    <td><?php echo esc_html((string) (int) $ads_row["clicks"]); ?></td>
                    <td><a href="<?php echo esc_url(add_query_arg(["post_type" => "lead", "s" => (string) $ads_row["campaign"]], admin_url("edit.php"))); ?>"><?php echo esc_html((string) (int) $ads_row["leads"]); ?></a></td>
                    <td><?php echo esc_html((string) (int) $ads_row["won"]); ?></td>
                    <td><?php echo esc_html(number_format_i18n((float) $ads_row["value"], 2)); ?> zł</td>
                    <td><?php echo esc_html(number_format_i18n((float) $ads_row["cac"], 2)); ?> zł</td>
                    <td><?php echo esc_html(number_format_i18n((float) $ads_row["roas"], 2)); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div class="ups-analytics-card" style="margin-top:14px;">
          <h2 style="margin-top:0;">Google Ads: Search Terms (top koszt)</h2>
          <p class="ups-analytics-sub">Last sync: <?php echo $ads_search_terms_synced !== "" ? esc_html($ads_search_terms_synced) : "brak"; ?></p>
          <table class="ups-analytics-table">
            <thead>
              <tr>
                <th>Search term</th>
                <th>Kampania</th>
                <th>Koszt</th>
                <th>Kliknięcia</th>
                <th>Conv</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($ads_search_term_rows)) : ?>
                <tr><td colspan="5"><em>Brak danych search terms.</em></td></tr>
              <?php else : ?>
                <?php foreach (array_slice($ads_search_term_rows, 0, 30) as $term_row) : ?>
                  <tr>
                    <td><?php echo esc_html((string) ($term_row["search_term"] ?? "")); ?></td>
                    <td><?php echo esc_html((string) ($term_row["campaign_name"] ?? "")); ?></td>
                    <td><?php echo esc_html(number_format_i18n((float) ($term_row["cost_pln"] ?? 0), 2)); ?> zł</td>
                    <td><?php echo esc_html((string) (int) ($term_row["clicks"] ?? 0)); ?></td>
                    <td><?php echo esc_html(number_format_i18n((float) ($term_row["conversions"] ?? 0), 2)); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div class="ups-analytics-card" style="margin-top:14px;">
          <h2 style="margin-top:0;">Google Ads: Auction insights</h2>
          <p class="ups-analytics-sub">Last sync: <?php echo $ads_auction_synced !== "" ? esc_html($ads_auction_synced) : "brak"; ?></p>
          <table class="ups-analytics-table">
            <thead>
              <tr>
                <th>Domena</th>
                <th>Impr. share</th>
                <th>Overlap</th>
                <th>Position above</th>
                <th>Top share</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($ads_auction_rows)) : ?>
                <tr><td colspan="5"><em>Brak danych auction insights.</em></td></tr>
              <?php else : ?>
                <?php foreach (array_slice($ads_auction_rows, 0, 25) as $auction_row) : ?>
                  <tr>
                    <td><?php echo esc_html((string) ($auction_row["domain"] ?? "")); ?></td>
                    <td><?php echo esc_html(number_format_i18n((float) ($auction_row["impression_share"] ?? 0), 1)); ?>%</td>
                    <td><?php echo esc_html(number_format_i18n((float) ($auction_row["overlap_rate"] ?? 0), 1)); ?>%</td>
                    <td><?php echo esc_html(number_format_i18n((float) ($auction_row["position_above_rate"] ?? 0), 1)); ?>%</td>
                    <td><?php echo esc_html(number_format_i18n((float) ($auction_row["top_share"] ?? 0), 1)); ?>%</td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
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
          <p style="font-size:12px;color:#5f6368;">Domyślne zakresy zgody: Search Console (read-only), Analytics (read-only) oraz <strong>Google Ads API</strong> (<code>adwords</code>). Po kliknięciu zalogujesz się na Google i zatwierdzisz dostęp — refresh token uzupełni się automatycznie.</p>
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
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
          function upsellioExplainAnomaly(key, btn) {
            if (!key || typeof ajaxurl === "undefined") return;
            if (btn) {
              btn.disabled = true;
              btn.textContent = "Generuję...";
            }
            var body = new URLSearchParams({
              action: "ups_explain_anomaly",
              key: key,
              _wpnonce: <?php echo wp_json_encode(wp_create_nonce("ups_explain_anomaly_action")); ?>
            });
            fetch(ajaxurl, {
              method: "POST",
              headers: {"Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"},
              body: body.toString()
            }).then(function (r) { return r.json(); }).then(function (d) {
              if (d && d.success) {
                window.location.reload();
              } else if (btn) {
                btn.disabled = false;
                btn.textContent = "Spróbuj ponownie";
              }
            }).catch(function () {
              if (btn) {
                btn.disabled = false;
                btn.textContent = "Spróbuj ponownie";
              }
            });
          }
          (function(){
            if (typeof window.ApexCharts !== "function") return;
            var labels = <?php echo wp_json_encode(array_values($dates)); ?>;
            var prevLabels = <?php echo wp_json_encode(array_values($prev_period_dates)); ?>;
            var viewsData = <?php echo wp_json_encode(array_values($daily_views_series)); ?>;
            var viewsPrev = <?php echo wp_json_encode(array_values($prev_views_series)); ?>;
            var leadsData = <?php echo wp_json_encode(array_values($daily_leads_series)); ?>;
            var leadsPrev = <?php echo wp_json_encode(array_values($prev_leads_series)); ?>;
            var impData = <?php echo wp_json_encode(array_values(array_map(static function($r){ return (int) ($r["impressions"] ?? 0); }, $daily_keyword_series))); ?>;
            var impPrev = <?php echo wp_json_encode(array_values(array_map(static function($r){ return (int) ($r["impressions"] ?? 0); }, $prev_keyword_series))); ?>;
            var clickData = <?php echo wp_json_encode(array_values(array_map(static function($r){ return (int) ($r["clicks"] ?? 0); }, $daily_keyword_series))); ?>;
            var clickPrev = <?php echo wp_json_encode(array_values(array_map(static function($r){ return (int) ($r["clicks"] ?? 0); }, $prev_keyword_series))); ?>;
            function mountChart(elId, name, current, previous, color){
              var el = document.getElementById(elId);
              if (!el) return;
              var opts = {
                chart: {type: "area", height: 220, toolbar: {show: false}},
                colors: [color, "#94a3b8"],
                series: [
                  {name: name, data: current},
                  {name: name + " (poprzedni okres)", data: previous}
                ],
                dataLabels: {enabled: false},
                stroke: {curve: "smooth", width: [2, 2]},
                fill: {type: "gradient", gradient: {opacityFrom: 0.25, opacityTo: 0.03}},
                xaxis: {categories: labels, labels: {rotate: -35}},
                yaxis: {labels: {formatter: function(v){ return Math.round(v); }}},
                tooltip: {shared: true},
                legend: {position: "top", horizontalAlign: "left"}
              };
              new ApexCharts(el, opts).render();
            }
            mountChart("ups-chart-views", "Views", viewsData, viewsPrev, "#0d9488");
            mountChart("ups-chart-leads", "Leads", leadsData, leadsPrev, "#2271b1");
            mountChart("ups-chart-impressions", "Impressions", impData, impPrev, "#8b5cf6");
            mountChart("ups-chart-clicks", "Clicks", clickData, clickPrev, "#f59e0b");
          })();
        </script>
      </div>
    </div>
    <?php
}

/**
 * Parsuje odpowiedź googleAds:searchStream (NDJSON lub pojedynczy JSON).
 *
 * @return array<int, array<string, mixed>>
 */
function upsellio_google_ads_parse_search_stream_body(string $body_raw): array
{
    $body_raw = trim($body_raw);
    if ($body_raw === "") {
        return [];
    }

    $single = json_decode($body_raw, true);
    if (is_array($single) && isset($single["results"]) && is_array($single["results"])) {
        return $single["results"];
    }

    $rows = [];
    $lines = preg_split("/\R/", $body_raw);
    if (!is_array($lines)) {
        return [];
    }

    foreach ($lines as $line) {
        $line = trim((string) $line);
        if ($line === "") {
            continue;
        }
        $decoded = json_decode($line, true);
        if (!is_array($decoded)) {
            continue;
        }
        if (isset($decoded["results"]) && is_array($decoded["results"])) {
            foreach ($decoded["results"] as $r) {
                if (is_array($r)) {
                    $rows[] = $r;
                }
            }
        } else {
            $rows[] = $decoded;
        }
    }

    return $rows;
}

/**
 * Wykonuje GAQL przez searchStream.
 *
 * @return array<int, array<string, mixed>>|\WP_Error
 */
function upsellio_google_ads_gaql_search_stream(string $query)
{
    if (!function_exists("upsellio_google_ads_api_ready") || !upsellio_google_ads_api_ready()) {
        return new WP_Error("not_ready", __("Skonfiguruj Google Ads API (Developer token + Customer ID).", "upsellio"));
    }

    $cfg = upsellio_google_ads_get_settings();
    $customer_id = $cfg["customer_id"];
    $creds = upsellio_get_gsc_credentials();
    $token = upsellio_gsc_get_access_token($creds);
    if (is_wp_error($token)) {
        return $token;
    }

    $url = upsellio_google_ads_rest_base_url() . "/customers/{$customer_id}/googleAds:searchStream";

    $response = wp_remote_post($url, [
        "timeout" => 45,
        "sslverify" => true,
        "headers" => array_merge(
            upsellio_google_ads_request_headers((string) $token),
            ["Content-Type" => "application/json"]
        ),
        "body" => wp_json_encode(["query" => $query]),
    ]);

    if (is_wp_error($response)) {
        return $response;
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    $body_raw = (string) wp_remote_retrieve_body($response);

    if ($code >= 400) {
        $body = json_decode($body_raw, true);
        $msg = upsellio_gsc_extract_error_message(is_array($body) ? $body : [], "GAQL HTTP {$code}");

        return new WP_Error("gaql_error", $msg);
    }

    return upsellio_google_ads_parse_search_stream_body($body_raw);
}

/**
 * Kampanie z metrykami (agregat za okres dat).
 *
 * @return array<int, array<string, mixed>>|\WP_Error
 */
function upsellio_google_ads_fetch_campaigns(string $date_range = "LAST_30_DAYS")
{
    $query = "SELECT
      campaign.id,
      campaign.name,
      campaign.status,
      campaign.advertising_channel_type,
      segments.date,
      metrics.cost_micros,
      metrics.clicks,
      metrics.impressions,
      metrics.conversions,
      metrics.ctr,
      metrics.average_cpc
    FROM campaign
    WHERE campaign.status = 'ENABLED'
      AND segments.date DURING {$date_range}";

    $rows = upsellio_google_ads_gaql_search_stream($query);
    if (is_wp_error($rows)) {
        return $rows;
    }

    $agg = [];
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $c = (array) ($row["campaign"] ?? []);
        $metrics = (array) ($row["metrics"] ?? []);
        $id = (string) ($c["id"] ?? "");
        if ($id === "") {
            continue;
        }
        if (!isset($agg[$id])) {
            $agg[$id] = [
                "id" => $id,
                "name" => (string) ($c["name"] ?? ""),
                "type" => (string) ($c["advertisingChannelType"] ?? ""),
                "cost_micros" => 0,
                "clicks" => 0,
                "impressions" => 0,
                "conversions" => 0.0,
                "ctr_weighted_num" => 0.0,
                "ctr_weighted_den" => 0,
                "cpc_micros_weighted_num" => 0,
                "cpc_micros_weighted_den" => 0,
            ];
        }

        $agg[$id]["cost_micros"] += (int) ($metrics["costMicros"] ?? 0);
        $agg[$id]["clicks"] += (int) ($metrics["clicks"] ?? 0);
        $agg[$id]["impressions"] += (int) ($metrics["impressions"] ?? 0);
        $agg[$id]["conversions"] += (float) ($metrics["conversions"] ?? 0);

        $imp_slice = (int) ($metrics["impressions"] ?? 0);
        if ($imp_slice > 0) {
            $agg[$id]["ctr_weighted_num"] += (float) ($metrics["ctr"] ?? 0) * $imp_slice;
            $agg[$id]["ctr_weighted_den"] += $imp_slice;
        }
        $clk_slice = (int) ($metrics["clicks"] ?? 0);
        if ($clk_slice > 0) {
            $avg_cpc_micros = (int) ($metrics["averageCpc"] ?? 0);
            $agg[$id]["cpc_micros_weighted_num"] += $avg_cpc_micros * $clk_slice;
            $agg[$id]["cpc_micros_weighted_den"] += $clk_slice;
        }
    }

    $campaigns = [];
    foreach ($agg as $a) {
        $cost_pln = round((int) $a["cost_micros"] / 1000000, 2);
        $avg_ctr = $a["ctr_weighted_den"] > 0 ? ($a["ctr_weighted_num"] / $a["ctr_weighted_den"]) : 0.0;
        $avg_cpc_micros = $a["cpc_micros_weighted_den"] > 0
            ? (int) round($a["cpc_micros_weighted_num"] / $a["cpc_micros_weighted_den"])
            : 0;
        $avg_cpc_pln = round($avg_cpc_micros / 1000000, 2);
        $conv_total = (float) $a["conversions"];
        $cpa_pln = $conv_total > 0.0001 ? round($cost_pln / $conv_total, 2) : 0.0;

        $campaigns[] = [
            "id" => $a["id"],
            "name" => $a["name"],
            "type" => $a["type"],
            "cost_pln" => $cost_pln,
            "clicks" => (int) $a["clicks"],
            "impressions" => (int) $a["impressions"],
            "conversions" => (float) $a["conversions"],
            "ctr" => round((float) $avg_ctr * 100, 2),
            "avg_cpc_pln" => $avg_cpc_pln,
            "cpa_pln" => $cpa_pln,
        ];
    }

    usort(
        $campaigns,
        static function ($x, $y) {
            return ($y["cost_pln"] ?? 0) <=> ($x["cost_pln"] ?? 0);
        }
    );

    return array_slice($campaigns, 0, 80);
}

/**
 * Auction insights per domena (keyword_view — wymaga dostępu do metryk auction w API).
 *
 * @return array<int, array<string, mixed>>|\WP_Error
 */
function upsellio_google_ads_fetch_auction_insights(string $campaign_id = "")
{
    $campaign_filter = "";
    if ($campaign_id !== "") {
        $cid = preg_replace("/\D+/", "", $campaign_id);
        if ($cid !== "") {
            $campaign_filter = " AND campaign.id = {$cid}";
        }
    }

    $query = "SELECT
      campaign.id,
      campaign.status,
      segments.auction_insight_domain,
      metrics.auction_insight_search_impression_share,
      metrics.auction_insight_search_overlap_rate,
      metrics.auction_insight_search_position_above_rate,
      metrics.auction_insight_search_top_impression_percentage,
      metrics.auction_insight_search_absolute_top_impression_percentage
    FROM keyword_view
    WHERE segments.date DURING LAST_30_DAYS
      AND campaign.status = 'ENABLED'
      {$campaign_filter}
    LIMIT 200";

    $rows = upsellio_google_ads_gaql_search_stream($query);
    if (is_wp_error($rows)) {
        return $rows;
    }

    $domains = [];
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $seg = isset($row["segments"]) && is_array($row["segments"]) ? $row["segments"] : [];
        $domain = (string) ($seg["auctionInsightDomain"] ?? "");

        if ($domain === "") {
            continue;
        }

        $metrics = (array) ($row["metrics"] ?? []);
        $imp_share = (float) ($metrics["auctionInsightSearchImpressionShare"] ?? 0);
        $overlap = (float) ($metrics["auctionInsightSearchOverlapRate"] ?? 0);
        $above = (float) ($metrics["auctionInsightSearchPositionAboveRate"] ?? 0);
        $top = (float) ($metrics["auctionInsightSearchTopImpressionPercentage"] ?? 0);
        $abs_top = (float) ($metrics["auctionInsightSearchAbsoluteTopImpressionPercentage"] ?? 0);

        if (!isset($domains[$domain])) {
            $domains[$domain] = [
                "domain" => $domain,
                "impression_share_sum" => 0.0,
                "overlap_sum" => 0.0,
                "above_sum" => 0.0,
                "top_sum" => 0.0,
                "abs_top_sum" => 0.0,
                "n" => 0,
            ];
        }
        $domains[$domain]["impression_share_sum"] += $imp_share;
        $domains[$domain]["overlap_sum"] += $overlap;
        $domains[$domain]["above_sum"] += $above;
        $domains[$domain]["top_sum"] += $top;
        $domains[$domain]["abs_top_sum"] += $abs_top;
        $domains[$domain]["n"]++;
    }

    $competitors = [];
    foreach ($domains as $d) {
        $n = max(1, (int) $d["n"]);
        $competitors[] = [
            "domain" => (string) $d["domain"],
            "impression_share" => round(($d["impression_share_sum"] / $n) * 100, 1),
            "overlap_rate" => round(($d["overlap_sum"] / $n) * 100, 1),
            "position_above_rate" => round(($d["above_sum"] / $n) * 100, 1),
            "top_share" => round(($d["top_sum"] / $n) * 100, 1),
            "abs_top_share" => round(($d["abs_top_sum"] / $n) * 100, 1),
        ];
    }

    usort(
        $competitors,
        static function ($a, $b) {
            return ($b["impression_share"] ?? 0) <=> ($a["impression_share"] ?? 0);
        }
    );

    return array_slice($competitors, 0, 40);
}

function upsellio_google_ads_fetch_search_terms(string $date_range = "LAST_30_DAYS")
{
    $query = "SELECT
      search_term_view.search_term,
      campaign.name,
      ad_group.name,
      metrics.cost_micros,
      metrics.clicks,
      metrics.impressions,
      metrics.conversions
    FROM search_term_view
    WHERE segments.date DURING {$date_range}
    LIMIT 600";

    $rows = upsellio_google_ads_gaql_search_stream($query);
    if (is_wp_error($rows)) {
        return $rows;
    }

    $terms = [];
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $term = sanitize_text_field((string) (($row["searchTermView"]["searchTerm"] ?? "")));
        if ($term === "") {
            continue;
        }
        $campaign_name = sanitize_text_field((string) (($row["campaign"]["name"] ?? "")));
        $ad_group_name = sanitize_text_field((string) (($row["adGroup"]["name"] ?? "")));
        $metrics = (array) ($row["metrics"] ?? []);
        $terms[] = [
            "search_term" => $term,
            "campaign_name" => $campaign_name,
            "ad_group_name" => $ad_group_name,
            "cost_pln" => round(((int) ($metrics["costMicros"] ?? 0)) / 1000000, 2),
            "clicks" => (int) ($metrics["clicks"] ?? 0),
            "impressions" => (int) ($metrics["impressions"] ?? 0),
            "conversions" => (float) ($metrics["conversions"] ?? 0),
        ];
    }

    usort($terms, static function ($a, $b) {
        return ($b["cost_pln"] ?? 0) <=> ($a["cost_pln"] ?? 0);
    });

    return array_slice($terms, 0, 250);
}

function upsellio_google_ads_sync_campaigns(): void
{
    $campaigns = upsellio_google_ads_fetch_campaigns("LAST_30_DAYS");

    if (is_wp_error($campaigns)) {
        update_option("ups_ads_campaigns_sync_error", $campaigns->get_error_message(), false);

        return;
    }

    update_option("ups_ads_campaigns_data", $campaigns, false);
    update_option("ups_ads_campaigns_synced", current_time("mysql"), false);
    delete_option("ups_ads_campaigns_sync_error");

    $auction = upsellio_google_ads_fetch_auction_insights("");
    if (!is_wp_error($auction)) {
        update_option("ups_ads_auction_data", $auction, false);
        update_option("ups_ads_auction_synced", current_time("mysql"), false);
    }

    $search_terms = upsellio_google_ads_fetch_search_terms("LAST_30_DAYS");
    if (!is_wp_error($search_terms)) {
        update_option("ups_ads_search_terms_data", $search_terms, false);
        update_option("ups_ads_search_terms_synced", current_time("mysql"), false);
    }

    if (!function_exists("upsellio_sales_engine_get_campaign_costs") || !function_exists("upsellio_sales_engine_save_campaign_costs")) {
        return;
    }

    $existing_costs = upsellio_sales_engine_get_campaign_costs();
    if (!is_array($existing_costs)) {
        $existing_costs = [];
    }

    $updated = false;

    foreach ($campaigns as $camp) {
        $cost_pln = (float) ($camp["cost_pln"] ?? 0);
        if ($cost_pln <= 0) {
            continue;
        }

        $target_key = null;
        $camp_name = strtolower(trim((string) ($camp["name"] ?? "")));

        foreach ($existing_costs as $ek => $ev) {
            $parts = array_map("trim", explode("|", str_replace(" | ", "|", (string) $ek), 2));
            $cmp = isset($parts[1]) ? strtolower(trim((string) $parts[1])) : "";
            if ($cmp !== "" && $cmp === $camp_name) {
                $target_key = $ek;
                break;
            }
        }

        if ($target_key !== null) {
            $existing_amount = (float) $existing_costs[$target_key];
            if ($existing_amount > 0 && abs($cost_pln - $existing_amount) / $existing_amount < 0.05) {
                continue;
            }
            $existing_costs[$target_key] = $cost_pln;
            $updated = true;
        } else {
            $new_key = "google | " . trim((string) ($camp["name"] ?? ""));
            $existing_costs[$new_key] = $cost_pln;
            $updated = true;
        }
    }

    if ($updated) {
        upsellio_sales_engine_save_campaign_costs($existing_costs);
    }
}

add_action("upsellio_google_ads_daily_sync", "upsellio_google_ads_sync_campaigns");
add_action("upsellio_automation_daily", "upsellio_google_ads_sync_campaigns", 34);
add_action("upsellio_gsc_daily_sync_hook", "upsellio_gsc_daily_sync_job");

add_action(
    "init",
    static function () {
        if (!wp_next_scheduled("upsellio_gsc_daily_sync")) {
            wp_schedule_event(time() + (45 * MINUTE_IN_SECONDS), "daily", "upsellio_gsc_daily_sync");
        }
        if (!wp_next_scheduled("upsellio_gsc_daily_sync_hook")) {
            wp_schedule_event(time() + MINUTE_IN_SECONDS, "daily", "upsellio_gsc_daily_sync_hook");
        }
        if (!wp_next_scheduled("upsellio_google_ads_daily_sync")) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, "daily", "upsellio_google_ads_daily_sync");
        }
    },
    20
);

add_action("upsellio_gsc_daily_sync", "upsellio_gsc_daily_sync_job");
add_action("switch_theme", static function () {
    wp_clear_scheduled_hook("upsellio_gsc_daily_sync_hook");
    wp_clear_scheduled_hook("upsellio_gsc_daily_sync");
    wp_clear_scheduled_hook("upsellio_google_ads_daily_sync");
});

function upsellio_analytics_build_weekly_digest_text(): string
{
    $ga4_rows = get_option("ups_automation_ga4_daily_aggregates", []);
    $keyword_rows = upsellio_get_keyword_metrics_data();
    $leads_week = get_posts([
        "post_type" => "lead",
        "post_status" => "publish",
        "posts_per_page" => 400,
        "date_query" => [[
            "after" => wp_date("Y-m-d", strtotime("-7 days")),
            "inclusive" => true,
        ]],
        "fields" => "ids",
    ]);
    $won_week = 0;
    $value_week = 0.0;
    foreach ($leads_week as $lead_id) {
        $lead_id = (int) $lead_id;
        $won_slugs = wp_get_object_terms($lead_id, "lead_status", ["fields" => "slugs"]);
        if (is_array($won_slugs) && in_array("won", $won_slugs, true)) {
            $won_week++;
            $value_week += (float) get_post_meta($lead_id, "_upsellio_lead_close_value", true);
        }
    }

    $top_queries = [];
    foreach (array_slice($keyword_rows, 0, 200) as $row) {
        if (!is_array($row)) {
            continue;
        }
        $query = (string) ($row["keyword"] ?? "");
        if ($query === "") {
            continue;
        }
        $top_queries[$query] = ($top_queries[$query] ?? 0) + (int) ($row["clicks"] ?? 0);
    }
    arsort($top_queries);
    $top_query_list = implode(", ", array_slice(array_keys($top_queries), 0, 5));

    $ga4_sessions = 0;
    $ga4_conversions = 0;
    if (is_array($ga4_rows)) {
        foreach ($ga4_rows as $ga4_row) {
            if (!is_array($ga4_row)) {
                continue;
            }
            $ga4_sessions += (int) ($ga4_row["sessions"] ?? 0);
            $ga4_conversions += (int) ($ga4_row["conversions"] ?? 0);
        }
    }

    $fallback = "Tygodniowy digest Upsellio\n"
        . "- GA4 sessions: {$ga4_sessions}, GA4 conversions: {$ga4_conversions}\n"
        . "- Leady (7d): " . count($leads_week) . ", wygrane: {$won_week}, wartość: " . number_format_i18n($value_week, 2) . " zł\n"
        . "- Top queries: " . ($top_query_list !== "" ? $top_query_list : "brak danych") . "\n"
        . "- Rekomendacja: sprawdź kampanie o najwyższym koszcie i najniższym win-rate.";

    if (function_exists("upsellio_anthropic_crm_send_user_prompt")) {
        $prompt = "Przygotuj zwięzły tygodniowy executive digest (PL) dla właściciela firmy B2B.\n"
            . "Dane:\n{$fallback}\n"
            . "Wymagane: 1 kluczowy insight, 3 ryzyka/anomalie, 2 szanse do skalowania, 3 konkretne akcje.";
        $ai = upsellio_anthropic_crm_send_user_prompt($prompt, 900, 45, null);
        if (is_string($ai) && trim($ai) !== "") {
            return trim($ai);
        }
    }

    return $fallback;
}

function upsellio_analytics_weekly_digest_job(): void
{
    $weekday = (int) wp_date("N");
    if ($weekday !== 1) {
        return;
    }
    $digest = upsellio_analytics_build_weekly_digest_text();
    update_option("ups_analytics_weekly_digest_last", $digest, false);
    wp_mail(
        sanitize_email((string) get_option("admin_email")),
        "Upsellio: Weekly Analytics Digest",
        $digest
    );
}
add_action("upsellio_automation_daily", "upsellio_analytics_weekly_digest_job", 35);

function upsellio_analytics_daily_anomaly_alert_job(): void
{
    $dates = upsellio_get_analytics_dates(14);
    $curr = array_slice($dates, 7, 7);
    $prev = array_slice($dates, 0, 7);
    $curr_views = array_sum(upsellio_get_daily_views_series($curr));
    $prev_views = array_sum(upsellio_get_daily_views_series($prev));
    $delta_views = upsellio_calculate_period_delta_percent($curr_views, $prev_views);
    if (!upsellio_is_anomaly_delta($delta_views)) {
        return;
    }
    $msg = "Anomalia ruchu: " . ($delta_views >= 0 ? "+" : "") . $delta_views . "% vs poprzednie 7 dni.";
    update_option("ups_analytics_last_anomaly_alert", $msg, false);
    wp_mail(sanitize_email((string) get_option("admin_email")), "Upsellio: Anomaly alert", $msg);
}
add_action("upsellio_automation_daily", "upsellio_analytics_daily_anomaly_alert_job", 36);

function upsellio_ga4_run_report_raw(string $property_numeric_id, array $body, string $trace_id = "")
{
    $property_numeric_id = preg_replace("/\D+/", "", $property_numeric_id);
    if ($property_numeric_id === "") {
        return new WP_Error("upsellio_ga4_missing_property", "Brak numerycznego ID property GA4.");
    }
    $oauth = upsellio_get_oauth_credentials_for_ga4();
    if (
        (string) ($oauth["client_id"] ?? "") === "" ||
        (string) ($oauth["client_secret"] ?? "") === "" ||
        (string) ($oauth["refresh_token"] ?? "") === ""
    ) {
        return new WP_Error("upsellio_ga4_missing_oauth", "Brak OAuth do GA4.");
    }
    $access_token = upsellio_gsc_get_access_token($oauth, $trace_id);
    if (is_wp_error($access_token)) {
        return $access_token;
    }
    $endpoint = "https://analyticsdata.googleapis.com/v1beta/properties/" . $property_numeric_id . ":runReport";
    $response = wp_remote_post($endpoint, [
        "timeout" => 45,
        "sslverify" => true,
        "headers" => [
            "Authorization" => "Bearer " . $access_token,
            "Content-Type" => "application/json",
        ],
        "body" => wp_json_encode($body),
    ]);
    if (is_wp_error($response)) {
        return $response;
    }
    $status = (int) wp_remote_retrieve_response_code($response);
    $decoded = json_decode((string) wp_remote_retrieve_body($response), true);
    if ($status >= 400) {
        return new WP_Error("upsellio_ga4_report_error", upsellio_gsc_extract_error_message(is_array($decoded) ? $decoded : [], "Błąd GA4 report HTTP " . $status));
    }
    return is_array($decoded) ? $decoded : new WP_Error("upsellio_ga4_report_invalid", "Nieprawidłowa odpowiedź GA4.");
}

function upsellio_analytics_health_daily_job(): void
{
    $trace_id = upsellio_gsc_debug_trace_id();
    $credentials = upsellio_get_gsc_credentials();
    if (
        (string) ($credentials["client_id"] ?? "") !== "" &&
        (string) ($credentials["client_secret"] ?? "") !== "" &&
        (string) ($credentials["refresh_token"] ?? "") !== "" &&
        (string) ($credentials["property"] ?? "") !== ""
    ) {
        $access_token = upsellio_gsc_get_access_token($credentials, $trace_id);
        if (!is_wp_error($access_token)) {
            $property = (string) ($credentials["property"] ?? "");
            $sitemaps_endpoint = "https://searchconsole.googleapis.com/webmasters/v3/sites/" . rawurlencode($property) . "/sitemaps";
            $sitemaps_response = wp_remote_get($sitemaps_endpoint, [
                "timeout" => 25,
                "headers" => ["Authorization" => "Bearer " . $access_token],
            ]);
            if (!is_wp_error($sitemaps_response) && (int) wp_remote_retrieve_response_code($sitemaps_response) < 400) {
                $payload = json_decode((string) wp_remote_retrieve_body($sitemaps_response), true);
                $entries = isset($payload["sitemap"]) && is_array($payload["sitemap"]) ? $payload["sitemap"] : [];
                update_option("ups_gsc_sitemaps_data", $entries, false);
                update_option("ups_gsc_sitemaps_last_sync", wp_date("Y-m-d H:i:s"), false);
            }

            $keyword_rows = get_option("upsellio_keyword_metrics_rows", []);
            if (is_array($keyword_rows) && !empty($keyword_rows)) {
                $pages = [];
                foreach ($keyword_rows as $row) {
                    if (!is_array($row)) {
                        continue;
                    }
                    $page = esc_url_raw((string) ($row["page"] ?? ""));
                    if ($page === "") {
                        continue;
                    }
                    $pages[$page] = ((int) ($pages[$page] ?? 0)) + (int) ($row["clicks"] ?? 0);
                }
                arsort($pages);
                $inspection_rows = [];
                foreach (array_slice(array_keys($pages), 0, 20) as $page_url) {
                    $inspect_response = wp_remote_post("https://searchconsole.googleapis.com/v1/urlInspection/index:inspect", [
                        "timeout" => 25,
                        "sslverify" => true,
                        "headers" => [
                            "Authorization" => "Bearer " . $access_token,
                            "Content-Type" => "application/json",
                        ],
                        "body" => wp_json_encode([
                            "inspectionUrl" => $page_url,
                            "siteUrl" => $property,
                            "languageCode" => "pl-PL",
                        ]),
                    ]);
                    if (is_wp_error($inspect_response) || (int) wp_remote_retrieve_response_code($inspect_response) >= 400) {
                        continue;
                    }
                    $inspect_payload = json_decode((string) wp_remote_retrieve_body($inspect_response), true);
                    $result = is_array($inspect_payload) ? (array) ($inspect_payload["inspectionResult"] ?? []) : [];
                    $index = (array) ($result["indexStatusResult"] ?? []);
                    $inspection_rows[] = [
                        "url" => $page_url,
                        "verdict" => (string) ($index["verdict"] ?? ""),
                        "coverage_state" => (string) ($index["coverageState"] ?? ""),
                        "last_crawl_time" => (string) ($index["lastCrawlTime"] ?? ""),
                        "referring_urls_count" => (int) ($index["referringUrlsCount"] ?? 0),
                    ];
                }
                if (!empty($inspection_rows)) {
                    update_option("ups_gsc_url_inspection_rows", $inspection_rows, false);
                    update_option("ups_gsc_url_inspection_last_sync", wp_date("Y-m-d H:i:s"), false);
                }
            }
        }
    }

    $property_id = upsellio_get_ga4_property_id();
    if ($property_id !== "") {
        $funnel_report = upsellio_ga4_run_report_raw($property_id, [
            "dateRanges" => [["startDate" => "30daysAgo", "endDate" => "yesterday"]],
            "dimensions" => [["name" => "eventName"]],
            "metrics" => [["name" => "eventCount"]],
            "dimensionFilter" => [
                "filter" => [
                    "fieldName" => "eventName",
                    "inListFilter" => [
                        "values" => ["page_view", "cta_click", "form_start", "generate_lead"],
                    ],
                ],
            ],
            "limit" => 50,
        ], $trace_id);
        if (!is_wp_error($funnel_report)) {
            $rows = [];
            foreach ((array) ($funnel_report["rows"] ?? []) as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $name = (string) ($row["dimensionValues"][0]["value"] ?? "");
                $count = (int) round((float) ($row["metricValues"][0]["value"] ?? 0));
                if ($name !== "") {
                    $rows[$name] = $count;
                }
            }
            update_option("ups_ga4_funnel_snapshot", $rows, false);
            update_option("ups_ga4_funnel_last_sync", wp_date("Y-m-d H:i:s"), false);
        }

        $cohort_report = upsellio_ga4_run_report_raw($property_id, [
            "dateRanges" => [["startDate" => "30daysAgo", "endDate" => "yesterday"]],
            "dimensions" => [["name" => "newVsReturning"]],
            "metrics" => [["name" => "sessions"], ["name" => "conversions"]],
            "limit" => 10,
        ], $trace_id);
        if (!is_wp_error($cohort_report)) {
            $rows = [];
            foreach ((array) ($cohort_report["rows"] ?? []) as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $segment = (string) ($row["dimensionValues"][0]["value"] ?? "");
                if ($segment === "") {
                    continue;
                }
                $rows[] = [
                    "segment" => $segment,
                    "sessions" => (int) round((float) ($row["metricValues"][0]["value"] ?? 0)),
                    "conversions" => (int) round((float) ($row["metricValues"][1]["value"] ?? 0)),
                ];
            }
            update_option("ups_ga4_cohort_snapshot", $rows, false);
            update_option("ups_ga4_cohort_last_sync", wp_date("Y-m-d H:i:s"), false);
        }
    }
}
add_action("upsellio_automation_daily", "upsellio_analytics_health_daily_job", 37);

function upsellio_register_dashboard_rest_routes(): void
{
    register_rest_route("upsellio/v1", "/dashboard/kpi", [
        "methods" => "GET",
        "permission_callback" => static function () {
            return current_user_can("edit_posts");
        },
        "callback" => static function (WP_REST_Request $request) {
            $days = (int) $request->get_param("range");
            $days = in_array($days, [7, 14, 30, 60, 90], true) ? $days : 30;
            $dates = upsellio_get_analytics_dates($days);
            $views = array_sum(upsellio_get_daily_views_series($dates));
            $leads = array_sum(upsellio_get_daily_leads_series($dates));
            return [
                "range_days" => $days,
                "views" => (int) $views,
                "leads" => (int) $leads,
                "conversion_rate" => $views > 0 ? round(($leads / $views) * 100, 2) : 0,
                "gsc_last_sync" => (string) get_option("upsellio_keyword_metrics_last_sync", ""),
                "ga4_last_sync" => (string) get_option("ups_automation_ga4_last_sync", ""),
            ];
        },
    ]);
}
add_action("rest_api_init", "upsellio_register_dashboard_rest_routes");

function upsellio_ajax_ads_sync_campaigns(): void
{
    check_ajax_referer("ups_crm_app_action", "nonce");
    if (!current_user_can("manage_options")) {
        wp_send_json_error("forbidden");
    }

    upsellio_google_ads_sync_campaigns();

    $error = (string) get_option("ups_ads_campaigns_sync_error", "");
    if ($error !== "") {
        wp_send_json_error($error);
    }

    $data = get_option("ups_ads_campaigns_data", []);
    wp_send_json_success([
        "count" => is_array($data) ? count($data) : 0,
        "synced" => (string) get_option("ups_ads_campaigns_synced", ""),
    ]);
}
add_action("wp_ajax_upsellio_ads_sync_campaigns", "upsellio_ajax_ads_sync_campaigns");

