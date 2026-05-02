<?php

if (!defined("ABSPATH")) {
    exit;
}

require_once get_template_directory() . "/inc/offer-landing.php";

function upsellio_register_offers_post_types()
{
    register_post_type("crm_client", [
        "labels" => [
            "name" => "Klienci",
            "singular_name" => "Klient",
            "menu_name" => "CRM Klienci",
            "add_new_item" => "Dodaj klienta",
            "edit_item" => "Edytuj klienta",
        ],
        "public" => false,
        "show_ui" => true,
        "show_in_menu" => false,
        "menu_icon" => "dashicons-businessperson",
        "supports" => ["title"],
    ]);

    register_post_type("crm_offer", [
        "labels" => [
            "name" => "Oferty",
            "singular_name" => "Oferta",
            "menu_name" => "CRM Oferty",
            "add_new_item" => "Dodaj oferte",
            "edit_item" => "Edytuj oferte",
        ],
        "public" => false,
        "show_ui" => true,
        "show_in_menu" => false,
        "menu_icon" => "dashicons-media-spreadsheet",
        "supports" => ["title", "editor"],
    ]);
}
add_action("init", "upsellio_register_offers_post_types");

function upsellio_offer_register_rewrite()
{
    add_rewrite_rule("^oferta/([^/]+)/?$", "index.php?ups_offer_slug=$matches[1]", "top");
}
add_action("init", "upsellio_offer_register_rewrite", 20);

function upsellio_offer_maybe_flush_rewrite()
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }
    $version_key = "upsellio_offer_rewrite_version";
    $target_version = "2026-04-30-offer-links-v3";
    if ((string) get_option($version_key, "") === $target_version) {
        return;
    }
    flush_rewrite_rules(false);
    update_option($version_key, $target_version, false);
}
add_action("admin_init", "upsellio_offer_maybe_flush_rewrite");

function upsellio_offer_extract_slug_from_request_path()
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
    if (preg_match("#^oferta/([^/]+)/?$#i", rawurldecode($path), $matches)) {
        return sanitize_title((string) ($matches[1] ?? ""));
    }
    return "";
}

function upsellio_offer_render_not_found_page()
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
      <title>Oferta nie istnieje</title>
      <style>body{font-family:Arial,sans-serif;background:#f8fafc;color:#0f172a} .wrap{max-width:760px;margin:64px auto;padding:24px;background:#fff;border:1px solid #e2e8f0;border-radius:12px} h1{margin:0 0 8px}</style>
    </head>
    <body>
      <div class="wrap">
        <h1>Nie znaleziono oferty</h1>
        <p>Link jest nieprawidłowy lub oferta została usunięta.</p>
      </div>
    </body>
    </html>
    <?php
    exit;
}

function upsellio_offer_register_query_var($vars)
{
    $vars[] = "ups_offer_slug";
    return $vars;
}
add_filter("query_vars", "upsellio_offer_register_query_var");

function upsellio_offer_generate_unique_slug($offer_id)
{
    $offer_id = (int) $offer_id;
    if ($offer_id <= 0) {
        return "";
    }
    $existing = (string) get_post_meta($offer_id, "_ups_offer_public_slug", true);
    if ($existing !== "") {
        return $existing;
    }

    do {
        $token = strtolower(wp_generate_password(10, false, false));
        $slug = "ofr-" . $offer_id . "-" . $token;
        $already = get_posts([
            "post_type" => "crm_offer",
            "post_status" => ["publish", "draft", "pending", "private", "future"],
            "posts_per_page" => 1,
            "fields" => "ids",
            "meta_query" => [[
                "key" => "_ups_offer_public_slug",
                "value" => $slug,
            ]],
        ]);
    } while (!empty($already));

    update_post_meta($offer_id, "_ups_offer_public_slug", $slug);
    return $slug;
}

function upsellio_offer_get_public_url($offer_id)
{
    $offer_id = (int) $offer_id;
    if ($offer_id <= 0) {
        return "";
    }
    $slug = upsellio_offer_generate_unique_slug($offer_id);
    if ($slug === "") {
        return "";
    }
    return home_url("/oferta/" . rawurlencode($slug) . "/");
}

function upsellio_offer_replace_email_placeholders($text, $offer_id)
{
    $offer_id = (int) $offer_id;
    $client_id = (int) get_post_meta($offer_id, "_ups_offer_client_id", true);
    $client_name = $client_id > 0 ? (string) get_the_title($client_id) : "Klient";
    $replace = [
        "{{client_name}}" => $client_name,
        "{{offer_title}}" => (string) get_the_title($offer_id),
        "{{offer_url}}" => (string) upsellio_offer_get_public_url($offer_id),
        "{{offer_price}}" => (string) get_post_meta($offer_id, "_ups_offer_price", true),
        "{{offer_timeline}}" => (string) get_post_meta($offer_id, "_ups_offer_timeline", true),
    ];
    return strtr((string) $text, $replace);
}

function upsellio_offer_get_default_template_html()
{
    $default = "<h1>Oferta: {{offer_title}}</h1><p>Czesc {{client_name}},</p><p>Przygotowalismy dla Ciebie oferte.</p><ul><li>Cena: {{offer_price}}</li><li>Termin: {{offer_timeline}}</li></ul><p><a href='{{offer_url}}'>Zobacz oferte online</a></p>";
    return (string) get_option("ups_offer_template_html", $default);
}

function upsellio_offer_get_default_template_css()
{
    $default = "body{font-family:Arial,sans-serif;color:#0f172a}h1{margin:0 0 12px}a{color:#0ea5e9}";
    return (string) get_option("ups_offer_template_css", $default);
}

function upsellio_offer_replace_template_placeholders($text, $offer_id)
{
    $offer_id = (int) $offer_id;
    $client_id = (int) get_post_meta($offer_id, "_ups_offer_client_id", true);
    $client_name = $client_id > 0 ? (string) get_the_title($client_id) : "Klient";
    $client_company = $client_id > 0 ? (string) get_post_meta($client_id, "_ups_client_company", true) : "";
    $replace = [
        "{{client_name}}" => $client_name,
        "{{client_company}}" => $client_company,
        "{{offer_title}}" => (string) get_the_title($offer_id),
        "{{offer_url}}" => (string) upsellio_offer_get_public_url($offer_id),
        "{{offer_price}}" => (string) get_post_meta($offer_id, "_ups_offer_price", true),
        "{{offer_timeline}}" => (string) get_post_meta($offer_id, "_ups_offer_timeline", true),
        "{{offer_cta_text}}" => (string) get_post_meta($offer_id, "_ups_offer_cta_text", true),
        "{{today}}" => current_time("Y-m-d"),
    ];
    return strtr((string) $text, $replace);
}

function upsellio_offer_send_email($offer_id)
{
    $offer_id = (int) $offer_id;
    if ($offer_id <= 0 || get_post_type($offer_id) !== "crm_offer") {
        return false;
    }
    $client_id = (int) get_post_meta($offer_id, "_ups_offer_client_id", true);
    $client_email = $client_id > 0 ? sanitize_email((string) get_post_meta($client_id, "_ups_client_email", true)) : "";
    if (!is_email($client_email)) {
        return false;
    }

    $subject_tpl = (string) get_option("ups_offer_email_subject", "Twoja oferta: {{offer_title}}");
    $html_tpl = (string) get_option("ups_offer_email_html", "<p>Czesc {{client_name}},</p><p>Twoja oferta jest gotowa:</p><p><a href='{{offer_url}}'>{{offer_url}}</a></p>");
    $css = (string) get_option("ups_offer_email_css", "body{font-family:Arial,sans-serif;color:#0f172a}a{color:#0ea5e9}");
    $subject = sanitize_text_field(upsellio_offer_replace_email_placeholders($subject_tpl, $offer_id));
    $html_content = upsellio_offer_replace_email_placeholders($html_tpl, $offer_id);
    $html = "<html><head><meta charset='utf-8'><style>" . $css . "</style></head><body>" . $html_content . "</body></html>";
    $sent = function_exists("upsellio_followup_send_html_mail")
        ? (bool) upsellio_followup_send_html_mail($client_email, $subject, $html)
        : (bool) wp_mail($client_email, $subject, $html, ["Content-Type: text/html; charset=UTF-8"]);
    update_post_meta($offer_id, "_ups_offer_email_sent_at", $sent ? current_time("mysql") : "");
    update_post_meta($offer_id, "_ups_offer_email_last_status", $sent ? "sent" : "failed");
    if ($sent) {
        $snapshot = [
            "ts" => current_time("mysql"),
            "subject" => $subject,
            "html" => $html,
            "version" => (int) get_post_meta($offer_id, "_ups_offer_current_version", true),
            "sent_to" => $client_email,
        ];
        $versions = get_post_meta($offer_id, "_ups_offer_sent_snapshots", true);
        if (!is_array($versions)) {
            $versions = [];
        }
        $versions[] = $snapshot;
        if (count($versions) > 50) {
            $versions = array_slice($versions, -50);
        }
        update_post_meta($offer_id, "_ups_offer_sent_snapshots", $versions);
    }
    if (function_exists("upsellio_offer_add_timeline_event")) {
        upsellio_offer_add_timeline_event($offer_id, $sent ? "offer_email_sent" : "offer_email_failed", "Mail z oferta: " . ($sent ? "wyslany" : "blad"));
    }
    if ($sent) {
        upsellio_offer_run_post_email_sent_automation($offer_id);
    }
    return $sent;
}

/**
 * Automatyka po skutecznym wysłaniu maila z ofertą (szablon „Wyślij ofertę mailem”).
 * Status „wysłana”, data pierwszej wysyłki, follow-upy (szablony + wbudowane 2/5/10 dni).
 */
function upsellio_offer_run_post_email_sent_automation($offer_id)
{
    $offer_id = (int) $offer_id;
    if ($offer_id <= 0 || get_post_type($offer_id) !== "crm_offer") {
        return;
    }
    $prev = (string) get_post_meta($offer_id, "_ups_offer_status", true);
    $prev_norm = $prev !== "" ? $prev : "open";
    if ($prev_norm === "won" || $prev_norm === "lost") {
        return;
    }
    $now = current_time("mysql");
    if ((string) get_post_meta($offer_id, "_ups_offer_first_sent_at", true) === "") {
        update_post_meta($offer_id, "_ups_offer_first_sent_at", $now);
    }
    if ($prev_norm === "open") {
        update_post_meta($offer_id, "_ups_offer_status", "sent");
        update_post_meta($offer_id, "_ups_offer_stage", "consideration");
        do_action("upsellio_offer_status_changed", $offer_id, "sent", "open");
        upsellio_offer_add_timeline_event($offer_id, "offer_marked_sent", "Status deala: oferta wysłana do klienta.");
    } else {
        upsellio_offer_add_timeline_event($offer_id, "offer_resent", "Ponowna wysyłka maila z ofertą.");
    }
    if (function_exists("upsellio_followup_handle_offer_event")) {
        upsellio_followup_handle_offer_event($offer_id, "offer_email_sent", [], "awareness");
    }
    upsellio_offer_queue_builtin_sent_reminders($offer_id);
}

/**
 * Kolejka domyślnych przypomnień mailowych po pierwszym „sent” (wyłączalne opcją).
 */
function upsellio_offer_queue_builtin_sent_reminders($offer_id)
{
    $offer_id = (int) $offer_id;
    if ($offer_id <= 0 || (string) get_option("ups_offer_builtin_sent_reminders", "1") !== "1") {
        return;
    }
    if ((string) get_post_meta($offer_id, "_ups_offer_builtin_reminders_scheduled", true) === "1") {
        return;
    }
    $defs = [
        ["kind" => "day2", "delay_minutes" => 2 * 24 * 60],
        ["kind" => "day5", "delay_minutes" => 5 * 24 * 60],
        ["kind" => "day10", "delay_minutes" => 10 * 24 * 60],
    ];
    $queue = get_post_meta($offer_id, "_ups_offer_followup_queue", true);
    if (!is_array($queue)) {
        $queue = [];
    }
    foreach ($defs as $def) {
        $kind = sanitize_key((string) ($def["kind"] ?? ""));
        $delay = max(0, (int) ($def["delay_minutes"] ?? 0));
        $signature = "builtin_sent:" . $kind;
        $dup = false;
        foreach ($queue as $ex) {
            if ((string) ($ex["signature"] ?? "") === $signature && (string) ($ex["status"] ?? "") !== "sent") {
                $dup = true;
                break;
            }
        }
        if ($dup) {
            continue;
        }
        $queue[] = [
            "template_id" => 0,
            "builtin_reminder" => $kind,
            "stage" => "awareness",
            "signature" => $signature,
            "status" => "queued",
            "created_at" => current_time("mysql"),
            "send_at" => gmdate("Y-m-d H:i:s", time() + $delay * MINUTE_IN_SECONDS),
        ];
    }
    update_post_meta($offer_id, "_ups_offer_followup_queue", $queue);
    update_post_meta($offer_id, "_ups_offer_builtin_reminders_scheduled", "1");
    upsellio_offer_add_timeline_event($offer_id, "builtin_followups_queued", "Zaplanowano przypomnienia po wysłaniu oferty (2/5/10 dni).");
}

function upsellio_offer_builtin_reminder_parts($offer_id, $kind)
{
    $offer_id = (int) $offer_id;
    $offer_title = $offer_id > 0 ? (string) get_the_title($offer_id) : "Oferta";
    $url = function_exists("upsellio_offer_get_public_url") ? (string) upsellio_offer_get_public_url($offer_id) : "";
    $client_id = (int) get_post_meta($offer_id, "_ups_offer_client_id", true);
    $first = $client_id > 0 ? (string) get_the_title($client_id) : "{{client_name}}";
    $kind = sanitize_key((string) $kind);
    $lines = [
        "day2" => [
            "subject" => "Przypomnienie: " . $offer_title,
            "html" => "<p>Cześć " . esc_html($first) . ",</p><p>Wracam z krótkim przypomnieniem — udało się zapoznać z naszą propozycją? Jeśli masz pytania, odpowiedz na tego maila.</p>"
                . ($url !== "" ? "<p><a href=\"" . esc_url($url) . "\">Link do oferty online</a></p>" : ""),
        ],
        "day5" => [
            "subject" => "Oferta: " . $offer_title . " — kontakt",
            "html" => "<p>Cześć " . esc_html($first) . ",</p><p>Chciałbym domknąć temat oferty — daj znać, czy jesteś na „tak”, „nie”, czy potrzebujesz jeszcze doprecyzowań.</p>"
                . ($url !== "" ? "<p><a href=\"" . esc_url($url) . "\">Podgląd oferty</a></p>" : ""),
        ],
        "day10" => [
            "subject" => "Zamykam temat oferty — " . $offer_title,
            "html" => "<p>Cześć " . esc_html($first) . ",</p><p>Jeśli teraz nie jest dobry moment — bez problemu. Ostatnia wiadomość w tej rundzie; jak wrócisz do tematu, napisz śmiało.</p>"
                . ($url !== "" ? "<p><a href=\"" . esc_url($url) . "\">Link do oferty</a></p>" : ""),
        ],
    ];
    if (!isset($lines[$kind])) {
        $kind = "day2";
    }

    return [
        "subject" => $lines[$kind]["subject"],
        "html" => $lines[$kind]["html"],
    ];
}

function upsellio_offer_send_builtin_reminder_email($offer_id, $kind, $stage)
{
    $offer_id = (int) $offer_id;
    $kind = sanitize_key((string) $kind);
    if ($offer_id <= 0 || get_post_type($offer_id) !== "crm_offer") {
        return false;
    }
    $status = (string) get_post_meta($offer_id, "_ups_offer_status", true);
    if ($status === "won" || $status === "lost") {
        return false;
    }
    $client_id = (int) get_post_meta($offer_id, "_ups_offer_client_id", true);
    $client_email = $client_id > 0 ? sanitize_email((string) get_post_meta($client_id, "_ups_client_email", true)) : "";
    if (!is_email($client_email)) {
        return false;
    }
    $parts = upsellio_offer_builtin_reminder_parts($offer_id, $kind);
    $subject_tpl = (string) ($parts["subject"] ?? "");
    $html_fragment = (string) ($parts["html"] ?? "");
    if (function_exists("upsellio_followup_resolve_placeholders")) {
        $subject_tpl = upsellio_followup_resolve_placeholders($subject_tpl, $offer_id, sanitize_key((string) $stage));
        $html_fragment = upsellio_followup_resolve_placeholders($html_fragment, $offer_id, sanitize_key((string) $stage));
    }
    $subject = sanitize_text_field($subject_tpl);
    if (strpos($subject, "[OFFER#") === false) {
        $subject .= " [OFFER#" . $offer_id . "]";
    }
    $css = "body{font-family:Arial,sans-serif;color:#0f172a}a{color:#0ea5e9}";
    $html = "<html><head><meta charset='utf-8'><style>" . $css . "</style></head><body>" . wp_kses_post($html_fragment) . "</body></html>";

    return function_exists("upsellio_followup_send_html_mail")
        ? (bool) upsellio_followup_send_html_mail($client_email, $subject, $html, ["crm_smtp" => true])
        : false;
}

function upsellio_offer_get_expires_at($offer_id)
{
    return (int) get_post_meta((int) $offer_id, "_ups_offer_expires_at", true);
}

function upsellio_offer_is_expired($offer_id)
{
    $expires_at = upsellio_offer_get_expires_at((int) $offer_id);
    if ($expires_at <= 0) {
        return false;
    }
    return time() >= $expires_at;
}

function upsellio_offer_add_timeline_event($offer_id, $type, $message)
{
    $offer_id = (int) $offer_id;
    if ($offer_id <= 0) {
        return;
    }
    $events = get_post_meta($offer_id, "_ups_offer_timeline", true);
    if (!is_array($events)) {
        $events = [];
    }
    $events[] = [
        "ts" => current_time("mysql"),
        "type" => sanitize_key((string) $type),
        "message" => sanitize_text_field((string) $message),
    ];
    if (count($events) > 100) {
        $events = array_slice($events, -100);
    }
    update_post_meta($offer_id, "_ups_offer_timeline", $events);
}

function upsellio_offer_build_analytics_summary($offer_id)
{
    $offer_id = (int) $offer_id;
    $events = get_post_meta($offer_id, "_ups_offer_events", true);
    if (!is_array($events)) {
        $events = [];
    }
    $unique_views = 0;
    $section_views = [];
    $section_seconds = [];
    $cta_clicks = 0;
    $total_seconds = 0;
    $last_seen_ts = "";

    foreach ($events as $event) {
        $event_name = sanitize_key((string) ($event["event"] ?? ""));
        $section_id = sanitize_key((string) ($event["section_id"] ?? ""));
        $seconds = (int) ($event["seconds"] ?? 0);
        $ts = (string) ($event["ts"] ?? "");
        if ($ts !== "") {
            $last_seen_ts = $ts;
        }
        if ($event_name === "offer_view") {
            $unique_views++;
        }
        if ($event_name === "offer_section_view" && $section_id !== "") {
            if (!isset($section_views[$section_id])) {
                $section_views[$section_id] = 0;
            }
            $section_views[$section_id]++;
        }
        if ($event_name === "offer_engagement_tick") {
            $total_seconds = max($total_seconds, $seconds);
            if ($section_id !== "") {
                if (!isset($section_seconds[$section_id])) {
                    $section_seconds[$section_id] = 0;
                }
                $section_seconds[$section_id] += 20;
            }
        }
        if ($event_name === "offer_cta_click") {
            $cta_clicks++;
        }
    }

    $pricing_seconds = (int) ($section_seconds["pricing"] ?? 0);
    $score = 0;
    $score += min(20, $unique_views * 8);
    $score += min(24, count($section_views) * 8);
    $score += min(30, (int) floor($total_seconds / 20) * 5);
    if ($pricing_seconds >= 30) {
        $score += 16;
    }
    if ($pricing_seconds >= 60) {
        $score += 10;
    }
    $score += min(35, $cta_clicks * 35);

    $hot_score = (int) get_option("ups_offer_score_hot", 70);
    $hot_pricing_seconds = (int) get_option("ups_offer_score_hot_pricing_seconds", 45);
    $is_hot = $score >= $hot_score || ($pricing_seconds >= $hot_pricing_seconds && $cta_clicks > 0);

    return [
        "views" => $unique_views,
        "section_views" => $section_views,
        "section_seconds" => $section_seconds,
        "cta_clicks" => $cta_clicks,
        "total_seconds" => $total_seconds,
        "pricing_seconds" => $pricing_seconds,
        "score" => $score,
        "is_hot" => $is_hot,
        "last_seen_ts" => $last_seen_ts,
    ];
}

function upsellio_offer_detect_stage($summary)
{
    $score = (int) ($summary["score"] ?? 0);
    $views = (int) ($summary["views"] ?? 0);
    $cta_clicks = (int) ($summary["cta_clicks"] ?? 0);
    $pricing_seconds = (int) ($summary["pricing_seconds"] ?? 0);

    $consideration_score = (int) get_option("ups_offer_score_consideration", 45);
    $decision_score = (int) get_option("ups_offer_score_decision", 75);
    $consideration_pricing_seconds = (int) get_option("ups_offer_score_consideration_pricing_seconds", 25);
    $decision_pricing_seconds = (int) get_option("ups_offer_score_decision_pricing_seconds", 60);
    $consideration_views = (int) get_option("ups_offer_stage_consideration_views", 2);
    $decision_views = (int) get_option("ups_offer_stage_decision_views", 3);
    $decision_require_cta = (string) get_option("ups_offer_stage_decision_require_cta", "0") === "1";

    $decision_by_score = $score >= $decision_score;
    $decision_by_pricing = $pricing_seconds >= $decision_pricing_seconds;
    $decision_by_views = $views >= $decision_views;
    $decision_by_cta = $cta_clicks > 0;
    if ($decision_require_cta) {
        if (($decision_by_score || $decision_by_pricing || $decision_by_views) && $decision_by_cta) {
            return "decision";
        }
    } elseif ($decision_by_score || $decision_by_cta || $decision_by_pricing || $decision_by_views) {
        return "decision";
    }
    if ($score >= $consideration_score || $pricing_seconds >= $consideration_pricing_seconds || $views >= $consideration_views) {
        return "consideration";
    }
    return "awareness";
}

function upsellio_offer_create_followup_task($offer_id, $summary)
{
    $offer_id = (int) $offer_id;
    if ($offer_id <= 0 || empty($summary["is_hot"])) {
        return;
    }
    $existing_task_id = (int) get_post_meta($offer_id, "_ups_offer_hot_task_id", true);
    if ($existing_task_id > 0) {
        return;
    }

    $owner_id = function_exists("upsellio_crm_get_default_owner_id") ? (int) upsellio_crm_get_default_owner_id() : 1;
    $task_id = wp_insert_post([
        "post_type" => "lead_task",
        "post_status" => "publish",
        "post_title" => "Follow-up hot offer: " . (string) get_the_title($offer_id),
        "post_author" => $owner_id,
    ], true);
    if (is_wp_error($task_id) || (int) $task_id <= 0) {
        return;
    }
    update_post_meta((int) $task_id, "_upsellio_task_status", "open");
    update_post_meta((int) $task_id, "_upsellio_task_due_at", gmdate("Y-m-d H:i:s", time() + 2 * HOUR_IN_SECONDS));
    update_post_meta((int) $task_id, "_upsellio_task_offer_id", $offer_id);
    update_post_meta((int) $task_id, "_upsellio_task_note", "Kontakt po wysokim sygnale intencji zakupu.");
    update_post_meta($offer_id, "_ups_offer_hot_task_id", (int) $task_id);
    upsellio_offer_add_timeline_event($offer_id, "hot_offer", "Wykryto hot offer i utworzono zadanie follow-up.");
}

function upsellio_offer_refresh_score($offer_id)
{
    $offer_id = (int) $offer_id;
    if ($offer_id <= 0) {
        return;
    }
    $summary = upsellio_offer_build_analytics_summary($offer_id);
    $stage = upsellio_offer_detect_stage($summary);
    update_post_meta($offer_id, "_ups_offer_score", (int) $summary["score"]);
    update_post_meta($offer_id, "_ups_offer_hot_offer", !empty($summary["is_hot"]) ? "1" : "0");
    $previous_stage = (string) get_post_meta($offer_id, "_ups_offer_stage", true);
    update_post_meta($offer_id, "_ups_offer_stage", $stage);
    if ($previous_stage !== "" && $previous_stage !== $stage) {
        $history = get_post_meta($offer_id, "_ups_offer_stage_history", true);
        if (!is_array($history)) {
            $history = [];
        }
        $history[] = [
            "ts" => current_time("mysql"),
            "from" => $previous_stage,
            "to" => $stage,
            "reason" => "score_refresh",
        ];
        if (count($history) > 120) {
            $history = array_slice($history, -120);
        }
        update_post_meta($offer_id, "_ups_offer_stage_history", $history);
    }
    if ((string) $summary["last_seen_ts"] !== "") {
        update_post_meta($offer_id, "_ups_offer_last_seen", (string) $summary["last_seen_ts"]);
    }
    if (!empty($summary["is_hot"])) {
        upsellio_offer_create_followup_task($offer_id, $summary);
    }
    do_action("upsellio_offer_scores_refreshed", $offer_id, $summary, $stage);
}

function upsellio_offer_maybe_schedule_score_refresh($offer_id)
{
    $offer_id = (int) $offer_id;
    if ($offer_id <= 0) {
        return;
    }
    $hook = "upsellio_offer_deferred_refresh_score";
    if (wp_next_scheduled($hook, [$offer_id])) {
        return;
    }
    wp_schedule_single_event(time(), $hook, [$offer_id]);
}

function upsellio_offer_run_deferred_refresh_score($offer_id)
{
    upsellio_offer_refresh_score((int) $offer_id);
}
add_action("upsellio_offer_deferred_refresh_score", "upsellio_offer_run_deferred_refresh_score", 10, 1);

function upsellio_add_client_meta_box()
{
    add_meta_box("upsellio_client_details", "Dane klienta", "upsellio_render_client_meta_box", "crm_client", "normal", "high");
}
add_action("add_meta_boxes", "upsellio_add_client_meta_box");

function upsellio_render_client_meta_box($post)
{
    $post_id = (int) $post->ID;
    $email = (string) get_post_meta($post_id, "_ups_client_email", true);
    $company = (string) get_post_meta($post_id, "_ups_client_company", true);
    $phone = (string) get_post_meta($post_id, "_ups_client_phone", true);
    $industry = (string) get_post_meta($post_id, "_ups_client_industry", true);
    $company_size = (string) get_post_meta($post_id, "_ups_client_company_size", true);
    $budget_range = (string) get_post_meta($post_id, "_ups_client_budget_range", true);
    $is_recurring = (string) get_post_meta($post_id, "_ups_client_is_recurring", true) === "1";
    $monthly_value = (string) get_post_meta($post_id, "_ups_client_monthly_value", true);
    $billing_start = (string) get_post_meta($post_id, "_ups_client_billing_start", true);
    $subscription_status = (string) get_post_meta($post_id, "_ups_client_subscription_status", true);
    if ($subscription_status === "") {
        $subscription_status = "active";
    }
    $cancellation_date = (string) get_post_meta($post_id, "_ups_client_cancellation_date", true);
    $cancellation_reason = (string) get_post_meta($post_id, "_ups_client_cancellation_reason", true);
    $person_id = (string) get_post_meta($post_id, "_ups_client_person_id", true);
    wp_nonce_field("upsellio_client_meta", "upsellio_client_meta_nonce");
    ?>
    <p><label><strong>Firma</strong></label><br /><input type="text" class="widefat" name="ups_client_company" value="<?php echo esc_attr($company); ?>" /></p>
    <p><label><strong>E-mail</strong></label><br /><input type="email" class="widefat" name="ups_client_email" value="<?php echo esc_attr($email); ?>" /></p>
    <p><label><strong>Telefon</strong></label><br /><input type="text" class="widefat" name="ups_client_phone" value="<?php echo esc_attr($phone); ?>" /></p>
    <p><label><strong>Branża</strong></label><br /><input type="text" class="widefat" name="ups_client_industry" value="<?php echo esc_attr($industry); ?>" /></p>
    <p><label><strong>Wielkość firmy</strong></label><br /><input type="text" class="widefat" name="ups_client_company_size" value="<?php echo esc_attr($company_size); ?>" placeholder="np. 1-10, 11-50, 51-200" /></p>
    <p><label><strong>Budżet range</strong></label><br /><input type="text" class="widefat" name="ups_client_budget_range" value="<?php echo esc_attr($budget_range); ?>" placeholder="np. 5k-10k / mies." /></p>
    <hr />
    <p><label style="display:flex;gap:8px;align-items:flex-start;"><input type="checkbox" name="ups_client_is_recurring" value="1" <?php checked($is_recurring); ?> /><span>Usługa odnawialna (miesięczna)</span></label></p>
    <p><label><strong>MRR klienta (PLN / miesiąc)</strong></label><br /><input type="number" step="0.01" min="0" class="widefat" name="ups_client_monthly_value" value="<?php echo esc_attr($monthly_value); ?>" /></p>
    <p><label><strong>Data startu rozliczeń</strong></label><br /><input type="date" class="widefat" name="ups_client_billing_start" value="<?php echo esc_attr($billing_start); ?>" /></p>
    <p>
      <label><strong>Status subskrypcji</strong></label><br />
      <select class="widefat" name="ups_client_subscription_status">
        <?php foreach (["active" => "Aktywna", "paused" => "Wstrzymana", "cancelled" => "Zrezygnowana"] as $status_key => $status_label) : ?>
          <option value="<?php echo esc_attr($status_key); ?>" <?php selected($subscription_status, $status_key); ?>><?php echo esc_html($status_label); ?></option>
        <?php endforeach; ?>
      </select>
    </p>
    <p><label><strong>Data rezygnacji</strong> (jeśli anulowano)</label><br /><input type="date" class="widefat" name="ups_client_cancellation_date" value="<?php echo esc_attr($cancellation_date); ?>" /></p>
    <p><label><strong>Powód rezygnacji</strong></label><br /><textarea class="widefat" rows="3" name="ups_client_cancellation_reason"><?php echo esc_textarea($cancellation_reason); ?></textarea></p>
    <p><label><strong>Person ID</strong></label><br /><input type="text" class="widefat" value="<?php echo esc_attr($person_id); ?>" readonly /></p>
    <?php
}

function upsellio_save_client_meta_box($post_id)
{
    if (get_post_type((int) $post_id) !== "crm_client" || !isset($_POST["upsellio_client_meta_nonce"])) {
        return;
    }
    $nonce = sanitize_text_field(wp_unslash($_POST["upsellio_client_meta_nonce"]));
    if (!wp_verify_nonce($nonce, "upsellio_client_meta") || (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) || !current_user_can("edit_post", (int) $post_id)) {
        return;
    }
    $previous_subscription_status = (string) get_post_meta((int) $post_id, "_ups_client_subscription_status", true);
    update_post_meta((int) $post_id, "_ups_client_company", isset($_POST["ups_client_company"]) ? sanitize_text_field(wp_unslash($_POST["ups_client_company"])) : "");
    update_post_meta((int) $post_id, "_ups_client_email", isset($_POST["ups_client_email"]) ? sanitize_email(wp_unslash($_POST["ups_client_email"])) : "");
    update_post_meta((int) $post_id, "_ups_client_phone", isset($_POST["ups_client_phone"]) ? sanitize_text_field(wp_unslash($_POST["ups_client_phone"])) : "");
    update_post_meta((int) $post_id, "_ups_client_industry", isset($_POST["ups_client_industry"]) ? sanitize_text_field(wp_unslash($_POST["ups_client_industry"])) : "");
    update_post_meta((int) $post_id, "_ups_client_company_size", isset($_POST["ups_client_company_size"]) ? sanitize_text_field(wp_unslash($_POST["ups_client_company_size"])) : "");
    update_post_meta((int) $post_id, "_ups_client_budget_range", isset($_POST["ups_client_budget_range"]) ? sanitize_text_field(wp_unslash($_POST["ups_client_budget_range"])) : "");
    update_post_meta((int) $post_id, "_ups_client_is_recurring", isset($_POST["ups_client_is_recurring"]) ? "1" : "0");
    update_post_meta((int) $post_id, "_ups_client_monthly_value", isset($_POST["ups_client_monthly_value"]) ? (float) wp_unslash($_POST["ups_client_monthly_value"]) : 0);
    update_post_meta((int) $post_id, "_ups_client_billing_start", isset($_POST["ups_client_billing_start"]) ? sanitize_text_field(wp_unslash($_POST["ups_client_billing_start"])) : "");
    $subscription_status = isset($_POST["ups_client_subscription_status"]) ? sanitize_key(wp_unslash($_POST["ups_client_subscription_status"])) : "active";
    update_post_meta((int) $post_id, "_ups_client_subscription_status", $subscription_status);
    update_post_meta((int) $post_id, "_ups_client_cancellation_date", isset($_POST["ups_client_cancellation_date"]) ? sanitize_text_field(wp_unslash($_POST["ups_client_cancellation_date"])) : "");
    update_post_meta((int) $post_id, "_ups_client_cancellation_reason", isset($_POST["ups_client_cancellation_reason"]) ? sanitize_textarea_field(wp_unslash($_POST["ups_client_cancellation_reason"])) : "");
    if ((string) get_post_meta((int) $post_id, "_ups_client_person_id", true) === "") {
        $generated_person_id = "psn_" . strtolower(wp_generate_password(16, false, false));
        update_post_meta((int) $post_id, "_ups_client_person_id", $generated_person_id);
    }
    if ($previous_subscription_status !== "cancelled" && $subscription_status === "cancelled") {
        do_action("upsellio_client_subscription_cancelled", (int) $post_id);
    }
}
add_action("save_post", "upsellio_save_client_meta_box");

function upsellio_add_offer_meta_boxes()
{
    add_meta_box("upsellio_offer_details", "Konfiguracja oferty", "upsellio_render_offer_meta_box", "crm_offer", "normal", "high");
    add_meta_box("upsellio_offer_analytics", "Analityka oferty", "upsellio_render_offer_analytics_meta_box", "crm_offer", "side", "high");
}
add_action("add_meta_boxes", "upsellio_add_offer_meta_boxes");

function upsellio_render_offer_meta_box($post)
{
    $post_id = (int) $post->ID;
    $client_id = (int) get_post_meta($post_id, "_ups_offer_client_id", true);
    $price = (string) get_post_meta($post_id, "_ups_offer_price", true);
    $timeline = (string) get_post_meta($post_id, "_ups_offer_timeline", true);
    $cta = (string) get_post_meta($post_id, "_ups_offer_cta_text", true);
    $offer_status = (string) get_post_meta($post_id, "_ups_offer_status", true);
    $won_value = (string) get_post_meta($post_id, "_ups_offer_won_value", true);
    $offer_owner_id = (int) get_post_meta($post_id, "_ups_offer_owner_id", true);
    $expires_at = (int) get_post_meta($post_id, "_ups_offer_expires_at", true);
    $expires_input = $expires_at > 0 ? gmdate("Y-m-d\TH:i", $expires_at + (int) (get_option("gmt_offset", 0) * HOUR_IN_SECONDS)) : "";
    $slug = (string) get_post_meta($post_id, "_ups_offer_public_slug", true);
    $public_url = $slug !== "" ? home_url("/oferta/" . rawurlencode($slug) . "/") : "";
    $last_email_sent = (string) get_post_meta($post_id, "_ups_offer_email_sent_at", true);
    $last_email_status = (string) get_post_meta($post_id, "_ups_offer_email_last_status", true);
    $clients = get_posts(["post_type" => "crm_client", "post_status" => ["publish", "draft"], "posts_per_page" => 300, "orderby" => "title", "order" => "ASC"]);
    wp_nonce_field("upsellio_offer_meta", "upsellio_offer_meta_nonce");
    ?>
    <p>
      <label><strong>Klient</strong></label><br />
      <select name="ups_offer_client_id" class="widefat">
        <option value="">-- wybierz klienta --</option>
        <?php foreach ($clients as $client) : ?>
          <option value="<?php echo esc_attr((string) $client->ID); ?>" <?php selected($client_id, (int) $client->ID); ?>><?php echo esc_html((string) $client->post_title); ?></option>
        <?php endforeach; ?>
      </select>
    </p>
    <p><label><strong>Cena / inwestycja</strong></label><br /><input type="text" class="widefat" name="ups_offer_price" value="<?php echo esc_attr($price); ?>" /></p>
    <p><label><strong>Timeline</strong></label><br /><input type="text" class="widefat" name="ups_offer_timeline" value="<?php echo esc_attr($timeline); ?>" /></p>
    <p><label><strong>Tekst CTA</strong></label><br /><input type="text" class="widefat" name="ups_offer_cta_text" value="<?php echo esc_attr($cta); ?>" /></p>
    <p>
      <label><strong>Status oferty</strong></label><br />
      <select class="widefat" name="ups_offer_status">
        <?php foreach (["open" => "Szkic / przygotowanie", "sent" => "Wysłana", "won" => "Won", "lost" => "Lost"] as $status_key => $status_label) : ?>
          <option value="<?php echo esc_attr($status_key); ?>" <?php selected($offer_status, $status_key); ?>><?php echo esc_html($status_label); ?></option>
        <?php endforeach; ?>
      </select>
    </p>
    <p>
      <label><strong>Opiekun oferty (owner)</strong></label><br />
      <select class="widefat" name="ups_offer_owner_id">
        <option value="">-- domyslny owner CRM --</option>
        <?php
        $owners = get_users(["role__in" => ["administrator", "editor"], "orderby" => "display_name", "order" => "ASC"]);
        foreach ($owners as $owner) :
            $owner_id = isset($owner->ID) ? (int) $owner->ID : 0;
            if ($owner_id <= 0) {
                continue;
            }
        ?>
          <option value="<?php echo esc_attr((string) $owner_id); ?>" <?php selected($offer_owner_id, $owner_id); ?>>
            <?php echo esc_html((string) ($owner->display_name ?? ("User #" . $owner_id))); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </p>
    <p><label><strong>Wartość wygranej (PLN)</strong></label><br /><input type="number" step="0.01" min="0" class="widefat" name="ups_offer_won_value" value="<?php echo esc_attr($won_value); ?>" /></p>
    <p>
      <label><strong>Wygasa (opcjonalnie)</strong></label><br />
      <input type="datetime-local" class="widefat" name="ups_offer_expires_at" value="<?php echo esc_attr($expires_input); ?>" />
      <small>Puste pole = link bez terminu wygasniecia.</small>
    </p>
    <p>
      <label style="display:flex;gap:8px;align-items:flex-start;">
        <input type="checkbox" name="ups_offer_send_email_now" value="1" />
        <span>Wyslij te oferte mailem do klienta po zapisaniu</span>
      </label>
      <?php
        $first_sent_at = (string) get_post_meta($post_id, "_ups_offer_first_sent_at", true);
      ?>
      <?php if ($first_sent_at !== "") : ?>
        <small>Pierwsza wysyłka do klienta: <?php echo esc_html($first_sent_at); ?></small><br />
      <?php endif; ?>
      <?php if ($last_email_sent !== "") : ?>
        <small>Ostatnia wysylka: <?php echo esc_html($last_email_sent); ?> (<?php echo esc_html($last_email_status !== "" ? $last_email_status : "unknown"); ?>)</small>
      <?php endif; ?>
    </p>
    <?php if ($public_url !== "") : ?>
      <p><strong>Link publiczny:</strong><br /><a href="<?php echo esc_url($public_url); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($public_url); ?></a></p>
      <p><strong>Status linku:</strong> <?php echo upsellio_offer_is_expired($post_id) ? "Wygasl" : "Aktywny"; ?></p>
    <?php else : ?>
      <p><em>Link publiczny wygeneruje sie po zapisaniu oferty.</em></p>
    <?php endif; ?>
    <?php
}

function upsellio_render_offer_analytics_meta_box($post)
{
    $offer_id = (int) $post->ID;
    $summary = upsellio_offer_build_analytics_summary($offer_id);
    $score = (int) get_post_meta($offer_id, "_ups_offer_score", true);
    $intent_score = (int) get_post_meta($offer_id, "_ups_offer_intent_score", true);
    $fit_score = (int) get_post_meta($offer_id, "_ups_offer_fit_score", true);
    $hot_index = (int) get_post_meta($offer_id, "_ups_offer_hot_index", true);
    $action_recommendation = (string) get_post_meta($offer_id, "_ups_offer_action_recommendation", true);
    $is_hot = (string) get_post_meta($offer_id, "_ups_offer_hot_offer", true) === "1";
    $last_seen = (string) get_post_meta($offer_id, "_ups_offer_last_seen", true);
    ?>
    <p><strong>Score:</strong> <?php echo esc_html((string) $score); ?>/100</p>
    <p><strong>Intent/Fit/Hot:</strong> <?php echo esc_html((string) $intent_score); ?> / <?php echo esc_html((string) $fit_score); ?> / <?php echo esc_html((string) $hot_index); ?></p>
    <p><strong>Hot offer:</strong> <?php echo $is_hot ? "TAK" : "NIE"; ?></p>
    <p><strong>Ostatnia wizyta:</strong><br /><?php echo esc_html($last_seen !== "" ? $last_seen : "brak"); ?></p>
    <p><strong>Views:</strong> <?php echo esc_html((string) ($summary["views"] ?? 0)); ?></p>
    <p><strong>CTA clicks:</strong> <?php echo esc_html((string) ($summary["cta_clicks"] ?? 0)); ?></p>
    <p><strong>Czas na cenniku:</strong> <?php echo esc_html((string) ((int) ($summary["pricing_seconds"] ?? 0))); ?>s</p>
    <p><strong>Rekomendacja:</strong><br /><?php echo esc_html($action_recommendation !== "" ? $action_recommendation : "Brak rekomendacji."); ?></p>
    <?php
}

function upsellio_save_offer_meta_box($post_id)
{
    if (get_post_type((int) $post_id) !== "crm_offer" || !isset($_POST["upsellio_offer_meta_nonce"])) {
        return;
    }
    $nonce = sanitize_text_field(wp_unslash($_POST["upsellio_offer_meta_nonce"]));
    if (!wp_verify_nonce($nonce, "upsellio_offer_meta") || (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) || !current_user_can("edit_post", (int) $post_id)) {
        return;
    }

    $previous_status = (string) get_post_meta((int) $post_id, "_ups_offer_status", true);
    $previous_title = (string) get_the_title((int) $post_id);
    $previous_content = (string) get_post_field("post_content", (int) $post_id);
    $previous_price = (string) get_post_meta((int) $post_id, "_ups_offer_price", true);
    $previous_timeline = (string) get_post_meta((int) $post_id, "_ups_offer_timeline", true);
    update_post_meta((int) $post_id, "_ups_offer_client_id", isset($_POST["ups_offer_client_id"]) ? (int) wp_unslash($_POST["ups_offer_client_id"]) : 0);
    update_post_meta((int) $post_id, "_ups_offer_price", isset($_POST["ups_offer_price"]) ? sanitize_text_field(wp_unslash($_POST["ups_offer_price"])) : "");
    update_post_meta((int) $post_id, "_ups_offer_timeline", isset($_POST["ups_offer_timeline"]) ? sanitize_text_field(wp_unslash($_POST["ups_offer_timeline"])) : "");
    update_post_meta((int) $post_id, "_ups_offer_cta_text", isset($_POST["ups_offer_cta_text"]) ? sanitize_text_field(wp_unslash($_POST["ups_offer_cta_text"])) : "");
    $new_status = isset($_POST["ups_offer_status"]) ? sanitize_key(wp_unslash($_POST["ups_offer_status"])) : "open";
    if (!in_array($new_status, ["open", "sent", "won", "lost"], true)) {
        $new_status = "open";
    }
    update_post_meta((int) $post_id, "_ups_offer_status", $new_status);
    update_post_meta((int) $post_id, "_ups_offer_won_value", isset($_POST["ups_offer_won_value"]) ? (float) wp_unslash($_POST["ups_offer_won_value"]) : 0);
    $owner_from_form = isset($_POST["ups_offer_owner_id"]) ? (int) wp_unslash($_POST["ups_offer_owner_id"]) : 0;
    if ($owner_from_form > 0) {
        update_post_meta((int) $post_id, "_ups_offer_owner_id", $owner_from_form);
    } else {
        delete_post_meta((int) $post_id, "_ups_offer_owner_id");
    }
    $expires_input = isset($_POST["ups_offer_expires_at"]) ? sanitize_text_field(wp_unslash($_POST["ups_offer_expires_at"])) : "";
    if ($expires_input !== "") {
        $local_ts = strtotime($expires_input);
        if ($local_ts !== false) {
            $utc_ts = (int) $local_ts - (int) (get_option("gmt_offset", 0) * HOUR_IN_SECONDS);
            update_post_meta((int) $post_id, "_ups_offer_expires_at", (int) $utc_ts);
        }
    } else {
        delete_post_meta((int) $post_id, "_ups_offer_expires_at");
    }
    upsellio_offer_generate_unique_slug((int) $post_id);
    $current_title = (string) get_the_title((int) $post_id);
    $current_content = (string) get_post_field("post_content", (int) $post_id);
    $current_price = (string) get_post_meta((int) $post_id, "_ups_offer_price", true);
    $current_timeline = (string) get_post_meta((int) $post_id, "_ups_offer_timeline", true);
    $has_version_change = ($previous_title !== $current_title) || ($previous_content !== $current_content) || ($previous_price !== $current_price) || ($previous_timeline !== $current_timeline);
    if ($has_version_change) {
        $versions = get_post_meta((int) $post_id, "_ups_offer_versions", true);
        if (!is_array($versions)) {
            $versions = [];
        }
        $next_version = (int) get_post_meta((int) $post_id, "_ups_offer_current_version", true) + 1;
        $versions[] = [
            "version" => $next_version,
            "ts" => current_time("mysql"),
            "user_id" => get_current_user_id(),
            "title" => $current_title,
            "content_hash" => md5($current_content),
            "price" => $current_price,
            "timeline" => $current_timeline,
        ];
        if (count($versions) > 100) {
            $versions = array_slice($versions, -100);
        }
        update_post_meta((int) $post_id, "_ups_offer_versions", $versions);
        update_post_meta((int) $post_id, "_ups_offer_current_version", $next_version);
    }
    if ($new_status !== $previous_status) {
        if ($new_status === "won" || $new_status === "lost") {
            update_post_meta((int) $post_id, "_ups_offer_closed_at", current_time("mysql"));
        }
        if ($new_status === "sent" && $previous_status !== "sent") {
            if ((string) get_post_meta((int) $post_id, "_ups_offer_first_sent_at", true) === "") {
                update_post_meta((int) $post_id, "_ups_offer_first_sent_at", current_time("mysql"));
            }
            update_post_meta((int) $post_id, "_ups_offer_stage", "consideration");
            upsellio_offer_queue_builtin_sent_reminders((int) $post_id);
        }
        do_action("upsellio_offer_status_changed", (int) $post_id, (string) $new_status, (string) $previous_status);
    }
    if (isset($_POST["ups_offer_send_email_now"]) && (string) wp_unslash($_POST["ups_offer_send_email_now"]) === "1") {
        upsellio_offer_send_email((int) $post_id);
    }
}
add_action("save_post", "upsellio_save_offer_meta_box");

function upsellio_offer_track_event()
{
    $offer_id = isset($_POST["offer_id"]) ? (int) $_POST["offer_id"] : 0;
    $event_name = isset($_POST["event_name"]) ? sanitize_key(wp_unslash($_POST["event_name"])) : "";
    if ($offer_id <= 0 || $event_name === "" || get_post_type($offer_id) !== "crm_offer") {
        wp_send_json_error(["message" => "invalid_payload"], 400);
    }
    if (function_exists("upsellio_is_internal_tracking_user") && upsellio_is_internal_tracking_user()) {
        wp_send_json_success(["ok" => true, "skipped_internal" => true]);
    }
    if (upsellio_offer_is_expired($offer_id)) {
        wp_send_json_error(["message" => "offer_expired"], 410);
    }

    $ip = isset($_SERVER["REMOTE_ADDR"]) ? sanitize_text_field(wp_unslash((string) $_SERVER["REMOTE_ADDR"])) : "0";
    $rl_key = "ups_offer_track_rl_" . md5($ip . "|" . $offer_id);
    if (get_transient($rl_key)) {
        wp_send_json_success(["ok" => true, "throttled" => true]);
    }
    set_transient($rl_key, 1, 5);

    $events = get_post_meta($offer_id, "_ups_offer_events", true);
    if (!is_array($events)) {
        $events = [];
    }
    $events[] = [
        "ts" => current_time("mysql"),
        "event" => $event_name,
        "section_id" => isset($_POST["section_id"]) ? sanitize_key(wp_unslash($_POST["section_id"])) : "",
        "seconds" => isset($_POST["seconds"]) ? (int) $_POST["seconds"] : 0,
        "page" => isset($_POST["page"]) ? esc_url_raw(wp_unslash($_POST["page"])) : "",
        "client_id" => isset($_POST["client_id"]) ? (int) $_POST["client_id"] : 0,
        "person_id" => isset($_POST["person_id"]) ? sanitize_text_field(wp_unslash($_POST["person_id"])) : "",
        "utm_source" => isset($_POST["utm_source"]) ? sanitize_text_field(wp_unslash($_POST["utm_source"])) : "",
        "utm_campaign" => isset($_POST["utm_campaign"]) ? sanitize_text_field(wp_unslash($_POST["utm_campaign"])) : "",
        "gclid" => isset($_POST["gclid"]) ? sanitize_text_field(wp_unslash($_POST["gclid"])) : "",
        "offer_version" => (int) get_post_meta($offer_id, "_ups_offer_current_version", true),
    ];
    if (count($events) > 600) {
        $events = array_slice($events, -600);
    }
    update_post_meta($offer_id, "_ups_offer_events", $events);
    $first_source = (string) get_post_meta($offer_id, "_ups_offer_utm_source", true);
    $first_campaign = (string) get_post_meta($offer_id, "_ups_offer_utm_campaign", true);
    $event_utm_source = isset($_POST["utm_source"]) ? sanitize_text_field(wp_unslash($_POST["utm_source"])) : "";
    $event_utm_campaign = isset($_POST["utm_campaign"]) ? sanitize_text_field(wp_unslash($_POST["utm_campaign"])) : "";
    $event_gclid = isset($_POST["gclid"]) ? sanitize_text_field(wp_unslash($_POST["gclid"])) : "";
    if ($first_source === "" && $event_utm_source !== "") {
        update_post_meta($offer_id, "_ups_offer_utm_source", $event_utm_source);
    }
    if ($first_campaign === "" && $event_utm_campaign !== "") {
        update_post_meta($offer_id, "_ups_offer_utm_campaign", $event_utm_campaign);
    }
    if ($event_utm_source !== "") {
        update_post_meta($offer_id, "_ups_offer_last_utm_source", $event_utm_source);
    }
    if ($event_utm_campaign !== "") {
        update_post_meta($offer_id, "_ups_offer_last_utm_campaign", $event_utm_campaign);
    }
    if ($event_gclid !== "") {
        update_post_meta($offer_id, "_ups_offer_gclid", $event_gclid);
    }
    if ($event_name === "offer_view" && (int) get_post_meta($offer_id, "_ups_offer_first_seen_version", true) <= 0) {
        update_post_meta($offer_id, "_ups_offer_first_seen_version", (int) get_post_meta($offer_id, "_ups_offer_current_version", true));
    }
    update_post_meta($offer_id, "_ups_offer_last_seen", current_time("mysql"));
    $summary = upsellio_offer_build_analytics_summary($offer_id);
    $stage = upsellio_offer_detect_stage($summary);
    do_action("upsellio_offer_event_tracked", $offer_id, $event_name, $summary, $stage);
    upsellio_offer_maybe_schedule_score_refresh($offer_id);

    wp_send_json_success(["ok" => true]);
}
add_action("wp_ajax_upsellio_offer_track_event", "upsellio_offer_track_event");
add_action("wp_ajax_nopriv_upsellio_offer_track_event", "upsellio_offer_track_event");

function upsellio_offer_render_expired_page()
{
    status_header(410);
    ?>
    <!doctype html>
    <html>
    <head><meta charset="utf-8" /><meta name="viewport" content="width=device-width,initial-scale=1" /><title>Oferta wygasla</title></head>
    <body style="font-family:Arial,sans-serif;padding:40px;background:#0f172a;color:#e2e8f0;">
      <h1>Ta oferta wygasla</h1>
      <p>Skontaktuj sie z opiekunem, aby otrzymac aktualna wersje.</p>
    </body>
    </html>
    <?php
}

function upsellio_offer_render_public_page()
{
    $requested_slug = sanitize_title((string) get_query_var("ups_offer_slug"));
    if ($requested_slug === "") {
        $requested_slug = upsellio_offer_extract_slug_from_request_path();
    }
    if ($requested_slug === "") {
        return;
    }

    $offers = get_posts([
        "post_type" => "crm_offer",
        "post_status" => "publish",
        "posts_per_page" => 1,
        "meta_query" => [[
            "key" => "_ups_offer_public_slug",
            "value" => $requested_slug,
        ]],
    ]);
    $offer = !empty($offers) ? $offers[0] : null;
    if (!$offer instanceof WP_Post) {
        upsellio_offer_render_not_found_page();
    }

    $offer_id = (int) $offer->ID;
    if (upsellio_offer_is_expired($offer_id)) {
        upsellio_offer_render_expired_page();
        exit;
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["ups_offer_accept_nonce"]) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST["ups_offer_accept_nonce"])), "ups_offer_accept_" . $offer_id)) {
        $prev_accept_status = (string) get_post_meta($offer_id, "_ups_offer_status", true);
        update_post_meta($offer_id, "_ups_offer_status", "won");
        update_post_meta($offer_id, "_ups_offer_stage", "decision");
        update_post_meta($offer_id, "_ups_offer_closed_at", current_time("mysql"));
        update_post_meta($offer_id, "_ups_offer_accepted_at", current_time("mysql"));
        update_post_meta($offer_id, "_ups_offer_accepted_version", (int) get_post_meta($offer_id, "_ups_offer_current_version", true));
        update_post_meta($offer_id, "_ups_offer_accept_ip", isset($_SERVER["REMOTE_ADDR"]) ? sanitize_text_field(wp_unslash($_SERVER["REMOTE_ADDR"])) : "");
        update_post_meta($offer_id, "_ups_offer_accept_user_agent", isset($_SERVER["HTTP_USER_AGENT"]) ? sanitize_text_field(wp_unslash($_SERVER["HTTP_USER_AGENT"])) : "");
        if (function_exists("upsellio_offer_add_timeline_event")) {
            $v = (int) get_post_meta($offer_id, "_ups_offer_accepted_version", true);
            upsellio_offer_add_timeline_event($offer_id, "offer_accepted", "Klient zaakceptował ofertę publicznie (wersja handlowa #" . $v . ").");
        }
        do_action("upsellio_offer_status_changed", $offer_id, "won", $prev_accept_status !== "" ? $prev_accept_status : "open");
        if (function_exists("upsellio_offer_track_event")) {
            update_post_meta($offer_id, "_ups_offer_last_event", "offer_accepted");
        }
    }
    if (function_exists("upsellio_offer_render_public_landing")) {
        upsellio_offer_render_public_landing($offer);
    }
    exit;
}
add_action("template_redirect", "upsellio_offer_render_public_page", 0);

function upsellio_offer_admin_columns($columns)
{
    if (!is_array($columns)) {
        return $columns;
    }
    $columns["ups_offer_link"] = "Link oferty";
    $columns["ups_offer_score"] = "Score";
    $columns["ups_offer_hot"] = "Hot";
    return $columns;
}
add_filter("manage_crm_offer_posts_columns", "upsellio_offer_admin_columns");

function upsellio_offer_admin_sortable_columns($columns)
{
    if (!is_array($columns)) {
        return $columns;
    }
    $columns["ups_offer_score"] = "ups_offer_score";
    return $columns;
}
add_filter("manage_edit-crm_offer_sortable_columns", "upsellio_offer_admin_sortable_columns");

function upsellio_offer_admin_columns_content($column, $post_id)
{
    if ($column === "ups_offer_link") {
        $url = upsellio_offer_get_public_url((int) $post_id);
        if ($url === "") {
            echo "—";
            return;
        }
        $status = upsellio_offer_is_expired((int) $post_id) ? " (wygasl)" : "";
        echo '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">Otworz</a>' . esc_html($status);
        return;
    }
    if ($column === "ups_offer_score") {
        echo esc_html((string) ((int) get_post_meta((int) $post_id, "_ups_offer_score", true)));
        return;
    }
    if ($column === "ups_offer_hot") {
        $is_hot = (string) get_post_meta((int) $post_id, "_ups_offer_hot_offer", true) === "1";
        echo $is_hot ? "TAK" : "NIE";
    }
}
add_action("manage_crm_offer_posts_custom_column", "upsellio_offer_admin_columns_content", 10, 2);

function upsellio_offer_admin_list_hot_filter()
{
    global $typenow;
    if ($typenow !== "crm_offer") {
        return;
    }
    $current = isset($_GET["ups_hot_filter"]) ? sanitize_key(wp_unslash($_GET["ups_hot_filter"])) : "";
    ?>
    <select name="ups_hot_filter">
      <option value="">Wszystkie oferty</option>
      <option value="hot" <?php selected($current, "hot"); ?>>Tylko hot offers</option>
      <option value="cold" <?php selected($current, "cold"); ?>>Tylko nie-hot</option>
    </select>
    <?php
}
add_action("restrict_manage_posts", "upsellio_offer_admin_list_hot_filter");

function upsellio_offer_admin_filter_and_sort_query($query)
{
    if (!is_admin() || !$query instanceof WP_Query || !$query->is_main_query()) {
        return;
    }
    $post_type = (string) $query->get("post_type");
    if ($post_type !== "crm_offer") {
        return;
    }

    $hot_filter = isset($_GET["ups_hot_filter"]) ? sanitize_key(wp_unslash($_GET["ups_hot_filter"])) : "";
    if ($hot_filter === "hot") {
        $query->set("meta_query", [[
            "key" => "_ups_offer_hot_offer",
            "value" => "1",
        ]]);
    } elseif ($hot_filter === "cold") {
        $query->set("meta_query", [[
            "key" => "_ups_offer_hot_offer",
            "value" => "1",
            "compare" => "!=",
        ]]);
    }

    $orderby = (string) $query->get("orderby");
    if ($orderby === "ups_offer_score") {
        $query->set("meta_key", "_ups_offer_score");
        $query->set("orderby", "meta_value_num");
    }
}
add_action("pre_get_posts", "upsellio_offer_admin_filter_and_sort_query");

function upsellio_offer_render_scoring_settings_page()
{
    if (isset($_POST["ups_offer_scoring_nonce"]) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST["ups_offer_scoring_nonce"])), "ups_offer_scoring_save")) {
        update_option("ups_offer_score_consideration", isset($_POST["ups_offer_score_consideration"]) ? max(1, (int) wp_unslash($_POST["ups_offer_score_consideration"])) : 45);
        update_option("ups_offer_score_decision", isset($_POST["ups_offer_score_decision"]) ? max(1, (int) wp_unslash($_POST["ups_offer_score_decision"])) : 75);
        update_option("ups_offer_score_hot", isset($_POST["ups_offer_score_hot"]) ? max(1, (int) wp_unslash($_POST["ups_offer_score_hot"])) : 70);
        update_option("ups_offer_score_consideration_pricing_seconds", isset($_POST["ups_offer_score_consideration_pricing_seconds"]) ? max(0, (int) wp_unslash($_POST["ups_offer_score_consideration_pricing_seconds"])) : 25);
        update_option("ups_offer_score_decision_pricing_seconds", isset($_POST["ups_offer_score_decision_pricing_seconds"]) ? max(0, (int) wp_unslash($_POST["ups_offer_score_decision_pricing_seconds"])) : 60);
        update_option("ups_offer_score_hot_pricing_seconds", isset($_POST["ups_offer_score_hot_pricing_seconds"]) ? max(0, (int) wp_unslash($_POST["ups_offer_score_hot_pricing_seconds"])) : 45);
        echo '<div class="notice notice-success"><p>Zapisano progi scoringu ofert.</p></div>';
    }

    $consideration_score = (int) get_option("ups_offer_score_consideration", 45);
    $decision_score = (int) get_option("ups_offer_score_decision", 75);
    $hot_score = (int) get_option("ups_offer_score_hot", 70);
    $consideration_pricing = (int) get_option("ups_offer_score_consideration_pricing_seconds", 25);
    $decision_pricing = (int) get_option("ups_offer_score_decision_pricing_seconds", 60);
    $hot_pricing = (int) get_option("ups_offer_score_hot_pricing_seconds", 45);
    ?>
    <div class="wrap">
      <h1>Ustawienia scoringu ofert</h1>
      <form method="post">
        <?php wp_nonce_field("ups_offer_scoring_save", "ups_offer_scoring_nonce"); ?>
        <table class="form-table">
          <tr><th scope="row">Próg consideration (score)</th><td><input type="number" min="1" class="small-text" name="ups_offer_score_consideration" value="<?php echo esc_attr((string) $consideration_score); ?>" /></td></tr>
          <tr><th scope="row">Próg decision (score)</th><td><input type="number" min="1" class="small-text" name="ups_offer_score_decision" value="<?php echo esc_attr((string) $decision_score); ?>" /></td></tr>
          <tr><th scope="row">Próg hot offer (score)</th><td><input type="number" min="1" class="small-text" name="ups_offer_score_hot" value="<?php echo esc_attr((string) $hot_score); ?>" /></td></tr>
          <tr><th scope="row">Consideration: czas na cenniku (sek.)</th><td><input type="number" min="0" class="small-text" name="ups_offer_score_consideration_pricing_seconds" value="<?php echo esc_attr((string) $consideration_pricing); ?>" /></td></tr>
          <tr><th scope="row">Decision: czas na cenniku (sek.)</th><td><input type="number" min="0" class="small-text" name="ups_offer_score_decision_pricing_seconds" value="<?php echo esc_attr((string) $decision_pricing); ?>" /></td></tr>
          <tr><th scope="row">Hot: czas na cenniku + CTA (sek.)</th><td><input type="number" min="0" class="small-text" name="ups_offer_score_hot_pricing_seconds" value="<?php echo esc_attr((string) $hot_pricing); ?>" /></td></tr>
        </table>
        <p><button type="submit" class="button button-primary">Zapisz ustawienia</button></p>
      </form>
    </div>
    <?php
}

function upsellio_offer_render_analytics_page()
{
    $offers = get_posts([
        "post_type" => "crm_offer",
        "post_status" => ["publish", "draft", "pending", "private"],
        "posts_per_page" => 200,
        "orderby" => "modified",
        "order" => "DESC",
    ]);
    ?>
    <div class="wrap">
      <h1>Analityka ofert</h1>
      <table class="widefat striped">
        <thead><tr><th>Oferta</th><th>Score</th><th>Hot</th><th>Views</th><th>Czas na cenniku</th><th>Ostatnia wizyta</th><th>Status linku</th></tr></thead>
        <tbody>
        <?php foreach ($offers as $offer) : ?>
          <?php
          $offer_id = (int) $offer->ID;
          $summary = upsellio_offer_build_analytics_summary($offer_id);
          $score = (int) get_post_meta($offer_id, "_ups_offer_score", true);
          $is_hot = (string) get_post_meta($offer_id, "_ups_offer_hot_offer", true) === "1";
          $last_seen = (string) get_post_meta($offer_id, "_ups_offer_last_seen", true);
          $expired = upsellio_offer_is_expired($offer_id);
          ?>
          <tr>
            <td><a href="<?php echo esc_url(get_edit_post_link($offer_id)); ?>"><?php echo esc_html((string) get_the_title($offer_id)); ?></a></td>
            <td><?php echo esc_html((string) $score); ?></td>
            <td><?php echo $is_hot ? "TAK" : "NIE"; ?></td>
            <td><?php echo esc_html((string) ((int) ($summary["views"] ?? 0))); ?></td>
            <td><?php echo esc_html((string) ((int) ($summary["pricing_seconds"] ?? 0))); ?>s</td>
            <td><?php echo esc_html($last_seen !== "" ? $last_seen : "brak"); ?></td>
            <td><?php echo $expired ? "Wygasl" : "Aktywny"; ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php
}
