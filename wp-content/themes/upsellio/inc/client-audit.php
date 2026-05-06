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

function ups_audit_start_oauth_connect($label = "")
{
    $state = bin2hex(random_bytes(16));
    $uid = get_current_user_id();
    set_transient(
        upsellio_google_oauth_transient_key($uid),
        [
            "state" => $state,
            "gsc_property" => "",
            "ga4_property_id" => "",
            "managed_oauth" => true,
            "conn_type" => "audit",
            "label" => sanitize_text_field((string) $label),
        ],
        15 * MINUTE_IN_SECONDS
    );
    $return_ok = home_url("/crm-app/?view=ca-accounts&connected=1");
    return upsellio_google_managed_oauth_build_start_url($state, $return_ok, $uid);
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

function ups_audit_fetch_ga4_resources($google_account_id)
{
    $oauth = ups_audit_get_oauth_for_account((int) $google_account_id);
    $access = upsellio_gsc_get_access_token($oauth);
    if (!is_string($access) || $access === "") {
        return [];
    }
    $accs_resp = wp_remote_get("https://analyticsadmin.googleapis.com/v1beta/accounts", [
        "timeout" => 30,
        "sslverify" => true,
        "headers" => ["Authorization" => "Bearer " . $access],
    ]);
    if (is_wp_error($accs_resp)) {
        return [];
    }
    $accs = json_decode((string) wp_remote_retrieve_body($accs_resp), true);
    if (!is_array($accs)) {
        return [];
    }
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
    return $tree;
}

function ups_audit_fetch_gsc_resources($google_account_id)
{
    $oauth = ups_audit_get_oauth_for_account((int) $google_account_id);
    $access = upsellio_gsc_get_access_token($oauth);
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
    return $sites;
}

function ups_audit_fetch_ads_customer_name($oauth_credentials, $customer_id)
{
    $oauth_credentials = is_array($oauth_credentials) ? $oauth_credentials : [];
    $access = upsellio_gsc_get_access_token($oauth_credentials);
    if (!is_string($access) || $access === "") {
        return "";
    }
    $developer_token = (string) get_option("ups_google_ads_developer_token", "");
    $manager_id = preg_replace("/\D+/", "", (string) get_option("ups_google_ads_login_customer_id", ""));
    if ($developer_token === "" || $manager_id === "") {
        return "";
    }
    $url = "https://googleads.googleapis.com/v18/customers/" . rawurlencode((string) $customer_id) . "/googleAds:search";
    $query = "SELECT customer.descriptive_name FROM customer LIMIT 1";
    $r = wp_remote_post($url, [
        "timeout" => 35,
        "sslverify" => true,
        "headers" => [
            "Authorization" => "Bearer " . $access,
            "developer-token" => $developer_token,
            "login-customer-id" => $manager_id,
            "Content-Type" => "application/json",
        ],
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
    $oauth = ups_audit_get_oauth_for_account((int) $google_account_id);
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
    return $list;
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

function ups_audit_ga4_fetch($property_id, $oauth_credentials, $sync_days = 30)
{
    $property_id = preg_replace("/\D+/", "", (string) $property_id);
    $access = upsellio_gsc_get_access_token((array) $oauth_credentials);
    if (!is_string($access) || $access === "") {
        return new WP_Error("ups_audit_no_access", "Brak tokena access.");
    }
    $body = [
        "dateRanges" => [["startDate" => "-" . (int) $sync_days . "d", "endDate" => "yesterday"]],
        "dimensions" => [["name" => "sessionSource"], ["name" => "sessionMedium"], ["name" => "date"]],
        "metrics" => [["name" => "sessions"], ["name" => "conversions"], ["name" => "totalRevenue"]],
        "limit" => 250000,
    ];
    $r = wp_remote_post("https://analyticsdata.googleapis.com/v1beta/properties/" . $property_id . ":runReport", [
        "timeout" => 45,
        "sslverify" => true,
        "headers" => ["Authorization" => "Bearer " . $access, "Content-Type" => "application/json"],
        "body" => wp_json_encode($body),
    ]);
    if (is_wp_error($r)) {
        return $r;
    }
    return json_decode((string) wp_remote_retrieve_body($r), true);
}

function ups_audit_sync_ga4_resource($resource_id)
{
    $resource_id = (int) $resource_id;
    $ext_id = (string) get_post_meta($resource_id, "_ups_resource_external_id", true);
    $account_id = (int) get_post_meta($resource_id, "_ups_resource_google_account_id", true);
    $oauth = ups_audit_get_oauth_for_account($account_id);
    $raw = ups_audit_ga4_fetch($ext_id, $oauth, 30);
    if (is_wp_error($raw) || !is_array($raw)) {
        return;
    }
    $sessions = 0;
    $conversions = 0;
    $revenue = 0.0;
    foreach ((array) ($raw["rows"] ?? []) as $row) {
        if (!is_array($row)) {
            continue;
        }
        $m = (array) ($row["metricValues"] ?? []);
        $sessions += (int) ($m[0]["value"] ?? 0);
        $conversions += (int) ($m[1]["value"] ?? 0);
        $revenue += (float) ($m[2]["value"] ?? 0);
    }
    $current_cache = get_post_meta($resource_id, "_ups_resource_data_cache", true);
    if (is_array($current_cache) && !empty($current_cache)) {
        update_post_meta($resource_id, "_ups_resource_data_cache_previous", $current_cache);
    }
    update_post_meta($resource_id, "_ups_resource_data_cache", [
        "sessions" => $sessions,
        "conversions" => $conversions,
        "revenue" => $revenue,
    ]);
    update_post_meta($resource_id, "_ups_resource_last_data_sync", current_time("mysql"));
}

function ups_audit_sync_gsc_resource($resource_id)
{
    $resource_id = (int) $resource_id;
    $site_url = (string) get_post_meta($resource_id, "_ups_resource_external_id", true);
    $account_id = (int) get_post_meta($resource_id, "_ups_resource_google_account_id", true);
    $oauth = ups_audit_get_oauth_for_account($account_id);
    $rows = upsellio_gsc_fetch_rows(array_merge($oauth, ["property" => $site_url]), 30);
    if (!is_array($rows)) {
        return;
    }
    $clicks = 0;
    $impressions = 0;
    $pos_sum = 0.0;
    $pos_n = 0;
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $clicks += (int) ($row["clicks"] ?? 0);
        $impressions += (int) ($row["impressions"] ?? 0);
        $pos_sum += (float) ($row["position"] ?? 0);
        $pos_n++;
    }
    $current_cache = get_post_meta($resource_id, "_ups_resource_data_cache", true);
    if (is_array($current_cache) && !empty($current_cache)) {
        update_post_meta($resource_id, "_ups_resource_data_cache_previous", $current_cache);
    }
    update_post_meta($resource_id, "_ups_resource_data_cache", [
        "clicks" => $clicks,
        "impressions" => $impressions,
        "avg_position" => $pos_n > 0 ? round($pos_sum / $pos_n, 2) : 0,
    ]);
    update_post_meta($resource_id, "_ups_resource_last_data_sync", current_time("mysql"));
}

function ups_audit_sync_ads_resource($resource_id)
{
    $resource_id = (int) $resource_id;
    $account_id = (int) get_post_meta($resource_id, "_ups_resource_google_account_id", true);
    $oauth = ups_audit_get_oauth_for_account($account_id);
    $backup = upsellio_get_gsc_credentials();
    upsellio_save_gsc_credentials(
        (string) ($oauth["client_id"] ?? ""),
        (string) ($oauth["client_secret"] ?? ""),
        (string) ($oauth["refresh_token"] ?? ""),
        (string) ($backup["property"] ?? "")
    );
    $campaigns = upsellio_google_ads_fetch_campaigns("LAST_30_DAYS");
    upsellio_save_gsc_credentials(
        (string) ($backup["client_id"] ?? ""),
        (string) ($backup["client_secret"] ?? ""),
        (string) ($backup["refresh_token"] ?? ""),
        (string) ($backup["property"] ?? "")
    );
    if (!is_array($campaigns)) {
        return;
    }
    $cost = 0.0;
    $clicks = 0;
    $conversions = 0.0;
    foreach ($campaigns as $c) {
        if (!is_array($c)) {
            continue;
        }
        $cost += (float) ($c["cost_pln"] ?? 0);
        $clicks += (int) ($c["clicks"] ?? 0);
        $conversions += (float) ($c["conversions"] ?? 0);
    }
    $current_cache = get_post_meta($resource_id, "_ups_resource_data_cache", true);
    if (is_array($current_cache) && !empty($current_cache)) {
        update_post_meta($resource_id, "_ups_resource_data_cache_previous", $current_cache);
    }
    update_post_meta($resource_id, "_ups_resource_data_cache", [
        "cost" => $cost,
        "clicks" => $clicks,
        "conversions" => $conversions,
    ]);
    update_post_meta($resource_id, "_ups_resource_last_data_sync", current_time("mysql"));
}

function ups_audit_sync_resource_action($resource_id)
{
    $resource_id = (int) $resource_id;
    $type = (string) get_post_meta($resource_id, "_ups_resource_type", true);
    if ($type === "ga4") {
        ups_audit_sync_ga4_resource($resource_id);
    } elseif ($type === "gsc") {
        ups_audit_sync_gsc_resource($resource_id);
    } elseif ($type === "ads") {
        ups_audit_sync_ads_resource($resource_id);
    }
}
add_action("ups_audit_sync_resource_action", "ups_audit_sync_resource_action");

function ups_audit_daily_sync_job()
{
    $resources = get_posts([
        "post_type" => "crm_audit_resource",
        "posts_per_page" => -1,
        "post_status" => ["publish", "draft"],
        "meta_query" => [[
            "key" => "_ups_resource_client_id",
            "compare" => "EXISTS",
        ]],
    ]);
    foreach ($resources as $r) {
        if (!($r instanceof WP_Post)) {
            continue;
        }
        try {
            ups_audit_sync_resource_action((int) $r->ID);
        } catch (Exception $e) {
            if (function_exists("upsellio_gsc_log")) {
                upsellio_gsc_log("audit.sync.error", ["resource_id" => (int) $r->ID, "msg" => $e->getMessage()]);
            }
        }
        sleep(1);
    }
}
add_action("ups_audit_daily_sync", "ups_audit_daily_sync_job");

function ups_audit_schedule_daily_sync()
{
    if (!wp_next_scheduled("ups_audit_daily_sync")) {
        wp_schedule_event(strtotime("tomorrow 06:00"), "daily", "ups_audit_daily_sync");
    }
}
add_action("init", "ups_audit_schedule_daily_sync");

function ups_audit_aggregate_client_data($client_id, $days = 30, $offset = 0)
{
    $client_id = (int) $client_id;
    $resources = ups_audit_get_client_resources($client_id);
    $agg = [
        "ga4_sessions" => 0,
        "ga4_conversions" => 0,
        "ga4_revenue" => 0.0,
        "gsc_clicks" => 0,
        "gsc_impressions" => 0,
        "gsc_avg_position" => 0.0,
        "ads_cost" => 0.0,
        "ads_clicks" => 0,
        "ads_conversions" => 0.0,
        "roas" => 0.0,
        "sources" => ["ga4" => [], "gsc" => [], "ads" => []],
    ];
    $gsc_pos_weight = 0;
    foreach ($resources as $r) {
        if (!($r instanceof WP_Post)) {
            continue;
        }
        $type = (string) get_post_meta((int) $r->ID, "_ups_resource_type", true);
        $cache_key = ((int) $offset > 0) ? "_ups_resource_data_cache_previous" : "_ups_resource_data_cache";
        $cache = get_post_meta((int) $r->ID, $cache_key, true);
        if (!is_array($cache) || empty($cache)) {
            $cache = get_post_meta((int) $r->ID, "_ups_resource_data_cache", true);
        }
        if (!is_array($cache)) {
            continue;
        }
        if ($type === "ga4") {
            $agg["ga4_sessions"] += (int) ($cache["sessions"] ?? 0);
            $agg["ga4_conversions"] += (int) ($cache["conversions"] ?? 0);
            $agg["ga4_revenue"] += (float) ($cache["revenue"] ?? 0);
            $agg["sources"]["ga4"][] = $cache;
        } elseif ($type === "gsc") {
            $imp = (int) ($cache["impressions"] ?? 0);
            $agg["gsc_clicks"] += (int) ($cache["clicks"] ?? 0);
            $agg["gsc_impressions"] += $imp;
            $agg["gsc_avg_position"] += ((float) ($cache["avg_position"] ?? 0)) * $imp;
            $gsc_pos_weight += $imp;
            $agg["sources"]["gsc"][] = $cache;
        } elseif ($type === "ads") {
            $agg["ads_cost"] += (float) ($cache["cost"] ?? 0);
            $agg["ads_clicks"] += (int) ($cache["clicks"] ?? 0);
            $agg["ads_conversions"] += (float) ($cache["conversions"] ?? 0);
            $agg["sources"]["ads"][] = $cache;
        }
    }
    $agg["gsc_avg_position"] = $gsc_pos_weight > 0 ? round($agg["gsc_avg_position"] / $gsc_pos_weight, 2) : 0;
    $agg["roas"] = $agg["ads_cost"] > 0 ? round($agg["ga4_revenue"] / $agg["ads_cost"], 2) : 0;
    return $agg;
}

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
    return "Jesteś senior account managerem agencji marketingowej. Pisz po polsku, profesjonalnie, dla klienta.\n\nKLIENT: {$client_name}\nOKRES: ostatnie 30 dni\n\nDANE:\n- Sesje GA4: " . (int) ($cur["ga4_sessions"] ?? 0) . " vs " . (int) ($prev["ga4_sessions"] ?? 0) . " (" . $delta($cur["ga4_sessions"] ?? 0, $prev["ga4_sessions"] ?? 0) . "%)\n- Kliknięcia GSC: " . (int) ($cur["gsc_clicks"] ?? 0) . " vs " . (int) ($prev["gsc_clicks"] ?? 0) . "\n- Wydatek Ads: " . round((float) ($cur["ads_cost"] ?? 0), 2) . " PLN\n- Konwersje Ads: " . round((float) ($cur["ads_conversions"] ?? 0), 1) . "\n- ROAS: " . round((float) ($cur["roas"] ?? 0), 2) . "x\n\nKONTEKST CRM:\n" . (string) $ctx . "\n\nWygeneruj raport HTML z sekcjami: podsumowanie, źródła danych, rekomendacje, największe zmiany. Bez bloków markdown.";
}

function ups_audit_generate_monthly_report($client_id)
{
    $client_id = (int) $client_id;
    $client = get_post($client_id);
    if (!($client instanceof WP_Post) || $client->post_type !== "crm_client") {
        return ["id" => 0, "html" => ""];
    }
    $cur = ups_audit_aggregate_client_data($client_id, 30, 0);
    $prev = ups_audit_aggregate_client_data($client_id, 30, 30);
    $ctx = function_exists("upsellio_ai_master_context") ? (string) upsellio_ai_master_context("client_audit", $client_id) : "";
    $prompt = ups_audit_build_monthly_report_prompt($cur, $prev, $client, $ctx);
    $model = ups_audit_ai_model_from_option("ups_audit_anthropic_model_reports", "sonnet");
    $GLOBALS["upsellio_ai_current_task"] = "client_audit";
    $result = function_exists("upsellio_anthropic_crm_send_user_prompt")
        ? upsellio_anthropic_crm_send_user_prompt($prompt, 4000, 90, $model)
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
    $prev = ups_audit_aggregate_client_data($client_id, 30, 30);
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

