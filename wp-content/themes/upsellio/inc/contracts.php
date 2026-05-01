<?php

if (!defined("ABSPATH")) {
    exit;
}

require_once __DIR__ . "/contract-landing.php";

function upsellio_contracts_register_post_type()
{
    register_post_type("crm_contract", [
        "labels" => [
            "name" => "Umowy",
            "singular_name" => "Umowa",
            "menu_name" => "CRM Umowy",
        ],
        "public" => false,
        "show_ui" => true,
        "show_in_menu" => false,
        "supports" => ["title", "editor"],
    ]);
}
add_action("init", "upsellio_contracts_register_post_type");

function upsellio_contracts_register_rewrite()
{
    add_rewrite_rule("^umowa/([^/]+)/?$", "index.php?ups_contract_token=$matches[1]", "top");
}
add_action("init", "upsellio_contracts_register_rewrite", 20);

function upsellio_contracts_register_query_var($vars)
{
    $vars[] = "ups_contract_token";
    return $vars;
}
add_filter("query_vars", "upsellio_contracts_register_query_var");

function upsellio_contracts_maybe_flush_rewrite()
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }
    $version_key = "upsellio_contracts_rewrite_version";
    $target_version = "2026-04-30-contracts-v2";
    if ((string) get_option($version_key, "") === $target_version) {
        return;
    }
    flush_rewrite_rules(false);
    update_option($version_key, $target_version, false);
}
add_action("admin_init", "upsellio_contracts_maybe_flush_rewrite");

function upsellio_contracts_extract_token_from_request_path()
{
    $request_uri = isset($_SERVER["REQUEST_URI"]) ? (string) wp_unslash($_SERVER["REQUEST_URI"]) : "";
    if ($request_uri === "") {
        return "";
    }
    $path = (string) wp_parse_url($request_uri, PHP_URL_PATH);
    if ($path === "") {
        return "";
    }
    $home_path = (string) wp_parse_url(home_url("/"), PHP_URL_PATH);
    if ($home_path !== "" && $home_path !== "/" && strpos($path, $home_path) === 0) {
        $path = (string) substr($path, strlen($home_path));
        if ($path === false) {
            $path = "";
        }
    }
    $path = ltrim($path, "/");
    if (preg_match("#^umowa/([^/]+)/?$#i", rawurldecode($path), $matches)) {
        return sanitize_text_field((string) ($matches[1] ?? ""));
    }
    return "";
}

function upsellio_contracts_render_not_found()
{
    status_header(404);
    nocache_headers();
    ?>
    <!doctype html>
    <html>
    <head>
      <meta charset="utf-8" />
      <meta name="viewport" content="width=device-width,initial-scale=1" />
      <meta name="robots" content="noindex,nofollow" />
      <title>Umowa nie istnieje</title>
      <style>body{font-family:Arial,sans-serif;background:#f8fafc;color:#0f172a} .wrap{max-width:760px;margin:64px auto;padding:24px;background:#fff;border:1px solid #e2e8f0;border-radius:12px} h1{margin:0 0 8px}</style>
    </head>
    <body>
      <div class="wrap">
        <h1>Nie znaleziono umowy</h1>
        <p>Link jest nieprawidłowy lub umowa została usunięta.</p>
      </div>
    </body>
    </html>
    <?php
    exit;
}

function upsellio_contracts_get_default_template_html()
{
    $default = "<h1>Umowa współpracy - {{client_name}}</h1><p>Zakres: {{offer_title}}</p><p>Wartość: {{offer_price}}</p><p>Start: {{offer_timeline}}</p><p>Link do oferty: <a href='{{offer_url}}'>{{offer_url}}</a></p>";
    return (string) get_option("ups_contract_template_html", $default);
}

function upsellio_contracts_get_default_template_css()
{
    $default = "body{font-family:DM Sans,Arial,sans-serif;color:#0f172a;line-height:1.6}h1{font-size:28px;margin:0 0 12px}";
    return (string) get_option("ups_contract_template_css", $default);
}

function upsellio_contracts_generate_token()
{
    return "ctr_" . strtolower(wp_generate_password(20, false, false));
}

function upsellio_contracts_replace_placeholders($text, $client_id, $offer_id, $contract_id = 0)
{
    $client_id = (int) $client_id;
    $offer_id = (int) $offer_id;
    $contract_id = (int) $contract_id;
    $client_name = $client_id > 0 ? (string) get_the_title($client_id) : "Klient";
    $client_company = $client_id > 0 ? (string) get_post_meta($client_id, "_ups_client_company", true) : "";
    $client_email = $client_id > 0 ? (string) get_post_meta($client_id, "_ups_client_email", true) : "";
    $client_nip = $client_id > 0 ? (string) get_post_meta($client_id, "_ups_client_nip", true) : "";
    $person_id = "";
    if ($offer_id > 0) {
        $person_id = (string) get_post_meta($offer_id, "_ups_offer_person_id", true);
    }
    if ($person_id === "" && $client_id > 0) {
        $person_id = (string) get_post_meta($client_id, "_ups_client_person_id", true);
    }

    $offer_title = $offer_id > 0 ? (string) get_the_title($offer_id) : "";
    $offer_price = $offer_id > 0 ? (string) get_post_meta($offer_id, "_ups_offer_price", true) : "";
    $offer_timeline = $offer_id > 0 ? (string) get_post_meta($offer_id, "_ups_offer_timeline", true) : "";
    $offer_price_note = $offer_id > 0 ? (string) get_post_meta($offer_id, "_ups_offer_price_note", true) : "";
    $payment_terms = $offer_id > 0 ? (string) get_post_meta($offer_id, "_ups_offer_payment_terms", true) : "";
    if ($payment_terms === "") {
        $payment_terms = (string) get_option("ups_contract_default_payment_terms", "miesięcznie z dołu");
    }

    $offer_date_label = "";
    if ($offer_id > 0) {
        $op = get_post($offer_id);
        if ($op instanceof WP_Post) {
            $ots = strtotime((string) $op->post_date_gmt . " UTC");
            $offer_date_label = $ots ? (string) wp_date("j.m.Y", $ots) : "";
        }
    }

    $offer_url = ($offer_id > 0 && function_exists("upsellio_offer_get_public_url")) ? (string) upsellio_offer_get_public_url($offer_id) : "";
    $contract_token = $contract_id > 0 ? (string) get_post_meta($contract_id, "_ups_contract_public_token", true) : "";
    $contract_url = $contract_id > 0 && $contract_token !== "" ? (string) home_url("/umowa/" . rawurlencode($contract_token) . "/") : "";

    $contract_title = $contract_id > 0 ? (string) get_the_title($contract_id) : "";
    $contract_post = $contract_id > 0 ? get_post($contract_id) : null;
    $contract_version = $contract_id > 0 ? max(1, (int) get_post_meta($contract_id, "_ups_contract_version", true)) : 1;
    $contract_sent_raw = $contract_id > 0 ? (string) get_post_meta($contract_id, "_ups_contract_sent_at", true) : "";
    $contract_signed_raw = $contract_id > 0 ? (string) get_post_meta($contract_id, "_ups_contract_signed_at", true) : "";

    $fmt_dt = static function ($mysql) {
        if ($mysql === "") {
            return "";
        }
        $t = strtotime($mysql);
        return $t ? (string) wp_date("j.m.Y H:i", $t) : $mysql;
    };

    $contract_created_label = "";
    if ($contract_post instanceof WP_Post) {
        $cts = strtotime((string) $contract_post->post_date_gmt . " UTC");
        $contract_created_label = $cts ? (string) wp_date("j.m.Y H:i", $cts) : "";
    }

    $offer_owner_email = (string) get_option("admin_email");
    if ($offer_id > 0) {
        $owner_id = (int) get_post_meta($offer_id, "_ups_offer_owner_id", true);
        if ($owner_id <= 0 && function_exists("upsellio_crm_get_default_owner_id")) {
            $owner_id = (int) upsellio_crm_get_default_owner_id();
        }
        if ($owner_id > 0) {
            $u = get_userdata($owner_id);
            if ($u instanceof WP_User && is_email((string) $u->user_email)) {
                $offer_owner_email = (string) $u->user_email;
            }
        }
    }

    $contract_pdf_url = $contract_id > 0 && $contract_token !== ""
        ? (string) apply_filters("upsellio_contract_pdf_url", "", $contract_id, $contract_token)
        : "";

    $contract_html_content = "";
    if ($contract_id > 0) {
        $contract_html_content = (string) get_post_meta($contract_id, "_ups_contract_html", true);
        if ($contract_html_content === "" && $contract_post instanceof WP_Post) {
            $contract_html_content = wpautop((string) $contract_post->post_content);
        }
    }

    $replace = [
        "{{client_name}}" => $client_name,
        "{{client_company}}" => $client_company,
        "{{client_email}}" => $client_email,
        "{{client_nip}}" => $client_nip,
        "{{person_id}}" => $person_id,
        "{{offer_title}}" => $offer_title,
        "{{offer_price}}" => $offer_price,
        "{{offer_price_note}}" => $offer_price_note !== "" ? $offer_price_note : "netto",
        "{{offer_timeline}}" => $offer_timeline,
        "{{offer_url}}" => $offer_url,
        "{{offer_id}}" => $offer_id > 0 ? (string) $offer_id : "",
        "{{offer_date}}" => $offer_date_label,
        "{{payment_terms}}" => $payment_terms,
        "{{contract_url}}" => $contract_url,
        "{{contract_title}}" => $contract_title,
        "{{contract_id}}" => $contract_id > 0 ? (string) $contract_id : "",
        "{{contract_token}}" => $contract_token,
        "{{contract_version}}" => (string) $contract_version,
        "{{contract_created_at}}" => $contract_created_label,
        "{{contract_sent_at}}" => $fmt_dt($contract_sent_raw),
        "{{contract_signed_at}}" => $fmt_dt($contract_signed_raw),
        "{{contract_pdf_url}}" => $contract_pdf_url,
        "{{offer_owner_email}}" => $offer_owner_email,
        "{{contract_html_content}}" => $contract_html_content,
        "{{today}}" => current_time("Y-m-d"),
    ];
    return strtr((string) $text, $replace);
}

function upsellio_contracts_get_public_url($contract_id)
{
    $contract_id = (int) $contract_id;
    if ($contract_id <= 0) {
        return "";
    }
    $token = (string) get_post_meta($contract_id, "_ups_contract_public_token", true);
    if ($token === "") {
        $token = upsellio_contracts_generate_token();
        update_post_meta($contract_id, "_ups_contract_public_token", $token);
    }
    return home_url("/umowa/" . rawurlencode($token) . "/");
}

function upsellio_contracts_get_timeline($contract_id)
{
    $contract_id = (int) $contract_id;
    if ($contract_id <= 0) {
        return [];
    }
    $timeline = get_post_meta($contract_id, "_ups_contract_timeline", true);
    if (!is_array($timeline)) {
        $timeline = [];
    }
    return $timeline;
}

function upsellio_contracts_log_event($contract_id, $event_key, $label = "", $details = [])
{
    $contract_id = (int) $contract_id;
    $event_key = sanitize_key((string) $event_key);
    if ($contract_id <= 0 || $event_key === "") {
        return;
    }
    $labels = [
        "created" => "Utworzono",
        "updated" => "Zaktualizowano",
        "sent" => "Wyslano",
        "opened" => "Otwarto",
        "signed" => "Podpisano",
        "cancelled" => "Anulowano",
    ];
    $timeline = upsellio_contracts_get_timeline($contract_id);
    $entry = [
        "ts" => current_time("mysql"),
        "event" => $event_key,
        "label" => $label !== "" ? $label : (isset($labels[$event_key]) ? $labels[$event_key] : ucfirst($event_key)),
        "user_id" => get_current_user_id(),
        "details" => is_array($details) ? $details : [],
    ];
    $timeline[] = $entry;
    if (count($timeline) > 250) {
        $timeline = array_slice($timeline, -250);
    }
    update_post_meta($contract_id, "_ups_contract_timeline", $timeline);
    update_post_meta($contract_id, "_ups_contract_last_event", $entry["event"]);
    update_post_meta($contract_id, "_ups_contract_last_event_at", $entry["ts"]);
    do_action("upsellio_contract_event_logged", (int) $contract_id, (string) $event_key, $entry);
}

function upsellio_contracts_set_status($contract_id, $new_status, $context = [])
{
    $contract_id = (int) $contract_id;
    $new_status = sanitize_key((string) $new_status);
    if ($contract_id <= 0 || $new_status === "") {
        return;
    }
    $old_status = (string) get_post_meta($contract_id, "_ups_contract_status", true);
    if ($old_status === $new_status) {
        return;
    }
    update_post_meta($contract_id, "_ups_contract_status", $new_status);
    if ($new_status === "sent") {
        update_post_meta($contract_id, "_ups_contract_sent_at", current_time("mysql"));
    } elseif ($new_status === "signed") {
        update_post_meta($contract_id, "_ups_contract_signed_at", current_time("mysql"));
    }
    do_action("upsellio_contract_status_changed", $contract_id, $new_status, $old_status, is_array($context) ? $context : []);
}

function upsellio_contracts_send_unsigned_reminders()
{
    update_option("ups_contracts_last_reminder_cron_run", current_time("mysql"), false);
    $contracts = get_posts([
        "post_type" => "crm_contract",
        "post_status" => ["publish", "draft", "pending", "private"],
        "posts_per_page" => 300,
        "fields" => "ids",
        "meta_query" => [[
            "key" => "_ups_contract_status",
            "value" => "sent",
        ]],
    ]);
    $first_days = max(1, (int) get_option("ups_contract_reminder_first_days", 3));
    $second_days = max($first_days + 1, (int) get_option("ups_contract_reminder_second_days", 7));
    foreach ($contracts as $contract_id) {
        $contract_id = (int) $contract_id;
        $sent_at_raw = (string) get_post_meta($contract_id, "_ups_contract_sent_at", true);
        if ($sent_at_raw === "") {
            continue;
        }
        $sent_ts = strtotime($sent_at_raw);
        if ($sent_ts === false) {
            continue;
        }
        $days_since = (int) floor((time() - $sent_ts) / DAY_IN_SECONDS);
        $reminder_count = (int) get_post_meta($contract_id, "_ups_contract_reminder_count", true);
        $should_send = ($reminder_count === 0 && $days_since >= $first_days) || ($reminder_count === 1 && $days_since >= $second_days);
        if (!$should_send) {
            continue;
        }
        $client_id = (int) get_post_meta($contract_id, "_ups_contract_client_id", true);
        $offer_id = (int) get_post_meta($contract_id, "_ups_contract_offer_id", true);
        $client_email = $client_id > 0 ? sanitize_email((string) get_post_meta($client_id, "_ups_client_email", true)) : "";
        if (!is_email($client_email)) {
            upsellio_contracts_log_event($contract_id, "send_failed", "Brak poprawnego email klienta - przypomnienie pominiete.", []);
            continue;
        }
        $contract_url = upsellio_contracts_get_public_url($contract_id);
        $subject = "Przypomnienie: umowa " . (string) get_the_title($contract_id);
        $message = "<p>Czesc {{client_name}},</p><p>To przypomnienie o umowie czekajacej na akceptacje.</p><p><a href='{{contract_url}}'>Przejdz do umowy</a></p>";
        $message = (string) upsellio_contracts_replace_placeholders($message, $client_id, $offer_id, $contract_id);
        $html = "<html><head><meta charset='utf-8'></head><body>" . $message . "</body></html>";
        $sent = function_exists("upsellio_followup_send_html_mail")
            ? (bool) upsellio_followup_send_html_mail($client_email, $subject, $html)
            : false;
        if ($sent) {
            update_post_meta($contract_id, "_ups_contract_reminder_count", $reminder_count + 1);
            update_post_meta($contract_id, "_ups_contract_last_reminder_at", current_time("mysql"));
            upsellio_contracts_log_event($contract_id, "sent", "Wyslano przypomnienie o podpisie", [
                "days_since_sent" => $days_since,
                "contract_url" => $contract_url,
            ]);
        } else {
            upsellio_contracts_log_event($contract_id, "send_failed", "Nieudana wysylka przypomnienia o podpisie.", [
                "days_since_sent" => $days_since,
                "contract_url" => $contract_url,
            ]);
        }
    }
}

function upsellio_contracts_schedule_cron()
{
    if (!wp_next_scheduled("upsellio_contracts_unsigned_reminders")) {
        wp_schedule_event(time() + 300, "hourly", "upsellio_contracts_unsigned_reminders");
    }
}
add_action("init", "upsellio_contracts_schedule_cron");
add_action("upsellio_contracts_unsigned_reminders", "upsellio_contracts_send_unsigned_reminders");

function upsellio_contracts_save_version_snapshot($contract_id)
{
    $contract_id = (int) $contract_id;
    if ($contract_id <= 0) {
        return;
    }
    $html = (string) get_post_meta($contract_id, "_ups_contract_html", true);
    $css = (string) get_post_meta($contract_id, "_ups_contract_css", true);
    $status = (string) get_post_meta($contract_id, "_ups_contract_status", true);
    $version = (int) get_post_meta($contract_id, "_ups_contract_version", true);
    if ($version <= 0) {
        $version = 1;
    }
    $versions = get_post_meta($contract_id, "_ups_contract_versions", true);
    if (!is_array($versions)) {
        $versions = [];
    }
    $versions[] = [
        "version" => $version,
        "ts" => current_time("mysql"),
        "user_id" => get_current_user_id(),
        "status" => $status,
        "html_hash" => md5($html),
        "css_hash" => md5($css),
    ];
    if (count($versions) > 120) {
        $versions = array_slice($versions, -120);
    }
    update_post_meta($contract_id, "_ups_contract_versions", $versions);
}

function upsellio_contracts_render_public()
{
    $token = sanitize_text_field((string) get_query_var("ups_contract_token"));
    if ($token === "") {
        $token = upsellio_contracts_extract_token_from_request_path();
    }
    if ($token === "") {
        return;
    }
    $contracts = get_posts([
        "post_type" => "crm_contract",
        "post_status" => ["publish", "draft", "private", "pending"],
        "posts_per_page" => 1,
        "meta_query" => [[
            "key" => "_ups_contract_public_token",
            "value" => $token,
        ]],
    ]);
    $contract = !empty($contracts) ? $contracts[0] : null;
    if (!$contract instanceof WP_Post) {
        upsellio_contracts_render_not_found();
    }
    $contract_id = (int) $contract->ID;
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["ups_contract_action"]) && (string) wp_unslash($_POST["ups_contract_action"]) === "accept_contract") {
        $nonce = isset($_POST["ups_contract_nonce"]) ? sanitize_text_field((string) wp_unslash($_POST["ups_contract_nonce"])) : "";
        $accepted = isset($_POST["ups_contract_accept"]) && (string) wp_unslash($_POST["ups_contract_accept"]) === "1";
        $accept_name = isset($_POST["ups_contract_accept_name"]) ? sanitize_text_field((string) wp_unslash($_POST["ups_contract_accept_name"])) : "";
        if (wp_verify_nonce($nonce, "ups_contract_accept_" . $contract_id) && $accepted && $accept_name !== "") {
            update_post_meta($contract_id, "_ups_contract_accept_name", $accept_name);
            update_post_meta($contract_id, "_ups_contract_accepted_at", current_time("mysql"));
            upsellio_contracts_set_status($contract_id, "signed", [
                "source" => "public_accept",
                "accept_name" => $accept_name,
            ]);
            upsellio_contracts_log_event($contract_id, "signed", "Zaakceptowano umowe na stronie publicznej.", [
                "accept_name" => $accept_name,
                "ip" => isset($_SERVER["REMOTE_ADDR"]) ? sanitize_text_field((string) wp_unslash($_SERVER["REMOTE_ADDR"])) : "",
                "ua" => isset($_SERVER["HTTP_USER_AGENT"]) ? sanitize_text_field((string) wp_unslash($_SERVER["HTTP_USER_AGENT"])) : "",
            ]);
            wp_safe_redirect(upsellio_contracts_get_public_url($contract_id));
            exit;
        }
    }
    $has_opened_cookie = isset($_COOKIE["ups_contract_opened_" . $contract_id]) && $_COOKIE["ups_contract_opened_" . $contract_id] === "1";
    if (!$has_opened_cookie) {
        $details = [
            "ip" => isset($_SERVER["REMOTE_ADDR"]) ? sanitize_text_field((string) wp_unslash($_SERVER["REMOTE_ADDR"])) : "",
            "ua" => isset($_SERVER["HTTP_USER_AGENT"]) ? sanitize_text_field((string) wp_unslash($_SERVER["HTTP_USER_AGENT"])) : "",
            "ref" => isset($_SERVER["HTTP_REFERER"]) ? esc_url_raw((string) wp_unslash($_SERVER["HTTP_REFERER"])) : "",
        ];
        upsellio_contracts_log_event($contract_id, "opened", "Otwarto umowe", $details);
        // T-12: 24h — unika wielokrotnego logu „opened” przy normalnym przeglądaniu tego samego dnia
        setcookie("ups_contract_opened_" . $contract_id, "1", time() + DAY_IN_SECONDS, COOKIEPATH ?: "/");
    }
    upsellio_contract_render_public_landing($contract);
    exit;
}
add_action("template_redirect", "upsellio_contracts_render_public", 1);

function upsellio_contract_track_event_ajax()
{
    $token = isset($_POST["contract_token"]) ? sanitize_text_field((string) wp_unslash($_POST["contract_token"])) : "";
    $event = isset($_POST["event"]) ? sanitize_key((string) wp_unslash($_POST["event"])) : "";
    $label = isset($_POST["label"]) ? sanitize_text_field((string) wp_unslash($_POST["label"])) : "";
    if ($token === "" || $event === "") {
        wp_send_json_error(["message" => "bad_request"], 400);
    }
    $contracts = get_posts([
        "post_type" => "crm_contract",
        "post_status" => ["publish", "draft", "private", "pending"],
        "posts_per_page" => 1,
        "fields" => "ids",
        "meta_query" => [[
            "key" => "_ups_contract_public_token",
            "value" => $token,
        ]],
    ]);
    $contract_id = !empty($contracts) ? (int) $contracts[0] : 0;
    if ($contract_id <= 0) {
        wp_send_json_error(["message" => "not_found"], 404);
    }
    $rows = get_post_meta($contract_id, "_ups_contract_track_events", true);
    if (!is_array($rows)) {
        $rows = [];
    }
    $rows[] = [
        "ts" => current_time("mysql"),
        "event" => $event,
        "label" => $label,
        "ip" => isset($_SERVER["REMOTE_ADDR"]) ? sanitize_text_field((string) wp_unslash($_SERVER["REMOTE_ADDR"])) : "",
    ];
    if (count($rows) > 500) {
        $rows = array_slice($rows, -500);
    }
    update_post_meta($contract_id, "_ups_contract_track_events", $rows);
    wp_send_json_success(["ok" => true]);
}
add_action("wp_ajax_upsellio_contract_track_event", "upsellio_contract_track_event_ajax");
add_action("wp_ajax_nopriv_upsellio_contract_track_event", "upsellio_contract_track_event_ajax");
