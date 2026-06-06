<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_meta_ads_config_option_key(): string
{
    return "upsellio_meta_ads_config";
}

/**
 * @return array{app_id:string,app_secret:string,api_version:string}
 */
function upsellio_meta_ads_get_settings(): array
{
    $raw = get_option(upsellio_meta_ads_config_option_key(), []);
    if (!is_array($raw)) {
        $raw = [];
    }
    $secret = (string) ($raw["app_secret"] ?? "");
    if ($secret !== "" && function_exists("ups_audit_decrypt")) {
        $dec = ups_audit_decrypt($secret);
        if ($dec !== "") {
            $secret = $dec;
        }
    }

    return [
        "app_id" => trim((string) ($raw["app_id"] ?? "")),
        "app_secret" => $secret,
        "api_version" => trim((string) ($raw["api_version"] ?? "v21.0")) ?: "v21.0",
    ];
}

function upsellio_meta_ads_api_version(): string
{
    $cfg = upsellio_meta_ads_get_settings();

    return $cfg["api_version"] !== "" ? $cfg["api_version"] : "v21.0";
}

function upsellio_meta_ads_graph_url(string $path, array $query = []): string
{
    $path = ltrim($path, "/");
    $ver = upsellio_meta_ads_api_version();
    $url = "https://graph.facebook.com/" . rawurlencode($ver) . "/" . $path;
    if ($query !== []) {
        $url = add_query_arg($query, $url);
    }

    return $url;
}

function ups_audit_meta_api_configured(): bool
{
    $cfg = upsellio_meta_ads_get_settings();

    return $cfg["app_id"] !== "" && $cfg["app_secret"] !== "";
}

function upsellio_meta_ads_normalize_ad_account_id(string $ad_account_id): string
{
    $ad_account_id = trim($ad_account_id);
    if ($ad_account_id === "") {
        return "";
    }
    if (strpos($ad_account_id, "act_") === 0) {
        return $ad_account_id;
    }

    return "act_" . preg_replace("/\D+/", "", $ad_account_id);
}

function upsellio_meta_ads_handle_settings_save(): void
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }
    if (!isset($_POST["upsellio_meta_ads_settings_save"])) {
        return;
    }
    check_admin_referer("upsellio_meta_ads_settings", "upsellio_meta_ads_settings_nonce");

    $app_id = isset($_POST["meta_app_id"]) ? sanitize_text_field(wp_unslash($_POST["meta_app_id"])) : "";
    $app_secret_in = isset($_POST["meta_app_secret"]) ? sanitize_text_field(wp_unslash($_POST["meta_app_secret"])) : "";
    $api_version = isset($_POST["meta_api_version"]) ? sanitize_text_field(wp_unslash($_POST["meta_api_version"])) : "v21.0";
    $prev = get_option(upsellio_meta_ads_config_option_key(), []);
    if (!is_array($prev)) {
        $prev = [];
    }
    $secret_store = (string) ($prev["app_secret"] ?? "");
    if ($app_secret_in !== "") {
        $secret_store = function_exists("ups_audit_encrypt")
            ? ups_audit_encrypt($app_secret_in)
            : $app_secret_in;
    }
    update_option(upsellio_meta_ads_config_option_key(), [
        "app_id" => $app_id,
        "app_secret" => $secret_store,
        "api_version" => $api_version !== "" ? $api_version : "v21.0",
    ], false);

    $redirect = function_exists("upsellio_crm_url")
        ? upsellio_crm_url("ca-meta-accounts", ["meta_settings_saved" => "1"])
        : home_url("/crm-app/?view=ca-meta-accounts&meta_settings_saved=1");
    wp_safe_redirect($redirect);
    exit;
}
add_action("admin_init", "upsellio_meta_ads_handle_settings_save", 2);

function upsellio_meta_ads_graph_request(string $path, array $query = [], string $method = "GET", array $body = [])
{
    $url = upsellio_meta_ads_graph_url($path, $query);
    $args = [
        "timeout" => 45,
        "sslverify" => true,
        "method" => strtoupper($method),
    ];
    if ($body !== []) {
        $args["body"] = $body;
    }
    $resp = wp_remote_request($url, $args);
    if (is_wp_error($resp)) {
        return $resp;
    }
    $code = (int) wp_remote_retrieve_response_code($resp);
    $json = json_decode((string) wp_remote_retrieve_body($resp), true);
    if (!is_array($json)) {
        return new WP_Error("meta_ads_bad_json", __("Meta API: nieprawidłowa odpowiedź.", "upsellio"));
    }
    if (isset($json["error"]) && is_array($json["error"])) {
        $msg = (string) ($json["error"]["message"] ?? "Meta API error");
        $sub = (string) ($json["error"]["error_user_msg"] ?? "");
        if ($sub !== "") {
            $msg .= " — " . $sub;
        }

        return new WP_Error("meta_ads_api", $msg, $json["error"]);
    }
    if ($code >= 400) {
        return new WP_Error("meta_ads_http", "Meta API HTTP " . $code);
    }

    return $json;
}

function upsellio_meta_ads_exchange_code_for_token(string $code, string $redirect_uri)
{
    $cfg = upsellio_meta_ads_get_settings();
    if ($cfg["app_id"] === "" || $cfg["app_secret"] === "") {
        return new WP_Error("meta_ads_config", __("Uzupełnij App ID i App Secret Meta w CRM.", "upsellio"));
    }
    $code = trim($code);
    if ($code === "") {
        return new WP_Error("meta_ads_code", __("Brak kodu OAuth Meta.", "upsellio"));
    }

    return upsellio_meta_ads_graph_request("oauth/access_token", [
        "client_id" => $cfg["app_id"],
        "client_secret" => $cfg["app_secret"],
        "redirect_uri" => $redirect_uri,
        "code" => $code,
    ]);
}

function upsellio_meta_ads_exchange_long_lived_token(string $short_token)
{
    $cfg = upsellio_meta_ads_get_settings();
    $short_token = trim($short_token);
    if ($short_token === "") {
        return new WP_Error("meta_ads_token", __("Brak tokena Meta.", "upsellio"));
    }

    return upsellio_meta_ads_graph_request("oauth/access_token", [
        "grant_type" => "fb_exchange_token",
        "client_id" => $cfg["app_id"],
        "client_secret" => $cfg["app_secret"],
        "fb_exchange_token" => $short_token,
    ]);
}

function upsellio_meta_ads_debug_token(string $access_token)
{
    $access_token = trim($access_token);
    if ($access_token === "") {
        return new WP_Error("meta_ads_token", __("Brak tokena Meta.", "upsellio"));
    }
    $cfg = upsellio_meta_ads_get_settings();

    return upsellio_meta_ads_graph_request("debug_token", [
        "input_token" => $access_token,
        "access_token" => $cfg["app_id"] . "|" . $cfg["app_secret"],
    ]);
}

function upsellio_meta_ads_fetch_user_profile(string $access_token)
{
    return upsellio_meta_ads_graph_request("me", [
        "fields" => "id,name,email",
        "access_token" => $access_token,
    ]);
}

function upsellio_meta_ads_list_ad_accounts(string $access_token)
{
    $access_token = trim($access_token);
    if ($access_token === "") {
        return new WP_Error("meta_ads_token", __("Brak tokena Meta.", "upsellio"));
    }

    $out = [];
    $url_path = "me/adaccounts";
    $query = [
        "fields" => "id,name,account_id,account_status,currency,business_name",
        "limit" => 100,
        "access_token" => $access_token,
    ];
    $guard = 0;
    while ($url_path !== "" && $guard < 20) {
        $guard++;
        $json = upsellio_meta_ads_graph_request($url_path, $query);
        if (is_wp_error($json)) {
            return $json;
        }
        foreach ((array) ($json["data"] ?? []) as $row) {
            if (!is_array($row)) {
                continue;
            }
            $id = (string) ($row["id"] ?? "");
            if ($id === "") {
                continue;
            }
            $out[] = [
                "id" => $id,
                "account_id" => (string) ($row["account_id"] ?? preg_replace("/\D+/", "", $id)),
                "name" => (string) ($row["name"] ?? $row["business_name"] ?? $id),
                "account_status" => (int) ($row["account_status"] ?? 0),
                "currency" => (string) ($row["currency"] ?? ""),
            ];
        }
        $next = (string) ($json["paging"]["next"] ?? "");
        if ($next === "") {
            break;
        }
        $parsed = wp_parse_url($next);
        if (!is_array($parsed) || empty($parsed["path"])) {
            break;
        }
        $url_path = ltrim((string) $parsed["path"], "/");
        $url_path = preg_replace("#^" . preg_quote(upsellio_meta_ads_api_version(), "#") . "/#", "", $url_path);
        parse_str((string) ($parsed["query"] ?? ""), $query);
    }

    return $out;
}

function upsellio_meta_ads_parse_conversions($actions): float
{
    if (!is_array($actions)) {
        return 0.0;
    }
    $total = 0.0;
    $types = [
        "lead",
        "purchase",
        "omni_purchase",
        "complete_registration",
        "offsite_conversion",
        "offsite_conversion.fb_pixel_lead",
        "offsite_conversion.fb_pixel_purchase",
        "onsite_conversion.lead_grouped",
        "onsite_conversion.messaging_conversation_started_7d",
    ];
    foreach ($actions as $action) {
        if (!is_array($action)) {
            continue;
        }
        $type = (string) ($action["action_type"] ?? "");
        $val = (float) ($action["value"] ?? 0);
        if ($val <= 0) {
            continue;
        }
        foreach ($types as $needle) {
            if ($type === $needle || strpos($type, $needle) !== false) {
                $total += $val;
                break;
            }
        }
    }

    return $total;
}

function upsellio_meta_ads_fetch_daily_insights(string $ad_account_id, string $access_token, string $since, string $until)
{
    $ad_account_id = upsellio_meta_ads_normalize_ad_account_id($ad_account_id);
    if ($ad_account_id === "") {
        return new WP_Error("meta_ads_account", __("Brak ID konta reklamowego Meta.", "upsellio"));
    }

    $time_range = wp_json_encode(["since" => $since, "until" => $until]);
    $json = upsellio_meta_ads_graph_request($ad_account_id . "/insights", [
        "fields" => "date_start,spend,impressions,clicks,actions",
        "time_increment" => 1,
        "time_range" => $time_range,
        "limit" => 500,
        "access_token" => $access_token,
    ]);
    if (is_wp_error($json)) {
        return $json;
    }

    $daily = [];
    foreach ((array) ($json["data"] ?? []) as $row) {
        if (!is_array($row)) {
            continue;
        }
        $date = (string) ($row["date_start"] ?? "");
        if ($date === "") {
            continue;
        }
        $daily[$date] = [
            "cost" => (float) ($row["spend"] ?? 0),
            "clicks" => (int) ($row["clicks"] ?? 0),
            "impressions" => (int) ($row["impressions"] ?? 0),
            "conversions" => upsellio_meta_ads_parse_conversions($row["actions"] ?? []),
        ];
    }

    return $daily;
}

function upsellio_meta_ads_fetch_campaign_insights(string $ad_account_id, string $access_token, string $since, string $until)
{
    $ad_account_id = upsellio_meta_ads_normalize_ad_account_id($ad_account_id);
    if ($ad_account_id === "") {
        return new WP_Error("meta_ads_account", __("Brak ID konta reklamowego Meta.", "upsellio"));
    }

    $time_range = wp_json_encode(["since" => $since, "until" => $until]);
    $json = upsellio_meta_ads_graph_request($ad_account_id . "/insights", [
        "fields" => "campaign_id,campaign_name,spend,impressions,clicks,actions",
        "level" => "campaign",
        "time_range" => $time_range,
        "limit" => 100,
        "access_token" => $access_token,
    ]);
    if (is_wp_error($json)) {
        return $json;
    }

    $campaigns = [];
    foreach ((array) ($json["data"] ?? []) as $row) {
        if (!is_array($row)) {
            continue;
        }
        $id = (string) ($row["campaign_id"] ?? "");
        if ($id === "") {
            continue;
        }
        $campaigns[] = [
            "id" => $id,
            "name" => (string) ($row["campaign_name"] ?? $id),
            "cost" => (float) ($row["spend"] ?? 0),
            "clicks" => (int) ($row["clicks"] ?? 0),
            "impressions" => (int) ($row["impressions"] ?? 0),
            "conversions" => upsellio_meta_ads_parse_conversions($row["actions"] ?? []),
        ];
    }

    return $campaigns;
}

function upsellio_meta_ads_test_connection(string $access_token)
{
    $list = upsellio_meta_ads_list_ad_accounts($access_token);
    if (is_wp_error($list)) {
        return $list;
    }

    return [
        "accounts" => count($list),
        "sample" => array_slice($list, 0, 3),
    ];
}
