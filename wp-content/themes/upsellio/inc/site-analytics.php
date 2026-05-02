<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_site_analytics_menu()
{
    add_submenu_page(
        "edit.php",
        "Analityka SEO",
        "Analityka SEO",
        "edit_posts",
        "upsellio-site-analytics",
        "upsellio_render_site_analytics_page",
        53
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
        add_query_arg(
            [
                "page" => "upsellio-site-analytics",
                "upsellio_metrics_imported" => (string) count($rows),
            ],
            admin_url("edit.php")
        )
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
            add_query_arg(
                [
                    "page" => "upsellio-site-analytics",
                    "upsellio_gsc_error" => rawurlencode($rows->get_error_message()),
                    "upsellio_gsc_trace_id" => rawurlencode($trace_id),
                ],
                admin_url("edit.php")
            )
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
        add_query_arg(
            [
                "page" => "upsellio-site-analytics",
                "upsellio_gsc_synced" => (string) count($rows),
                "upsellio_gsc_trace_id" => rawurlencode($trace_id),
            ],
            admin_url("edit.php")
        )
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
        add_query_arg(
            [
                "page" => "upsellio-site-analytics",
                "upsellio_gsc_logs_cleared" => "1",
            ],
            admin_url("edit.php")
        )
    );
    exit;
}
add_action("admin_init", "upsellio_handle_gsc_logs_clear_submit");

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

        <form method="get" action="<?php echo esc_url(admin_url("edit.php")); ?>">
          <input type="hidden" name="page" value="upsellio-site-analytics" />
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
          <form method="post">
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
              <label><strong>Google Refresh Token</strong><br />
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
              API GSC jest darmowe. Wymaga jednorazowego przygotowania OAuth i dostępu do property w Search Console.
            </p>
          </form>
          <hr />
          <h3 style="margin-bottom:8px;">Logi debug autoryzacji GSC</h3>
          <p style="font-size:12px;color:#5f6368;margin-top:0;">
            Logi pokazują pełny przebieg OAuth i zapytań GSC (sekrety są maskowane). Najnowsze wpisy są na górze.
          </p>
          <form method="post" style="margin:8px 0 12px;">
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
          <form method="post">
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

