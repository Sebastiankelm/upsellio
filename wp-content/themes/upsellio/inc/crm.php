<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_crm_get_default_statuses()
{
    return [
        "new" => "Nowy",
        "contacted" => "Skontaktowany",
        "qualified" => "Zakwalifikowany",
        "proposal" => "Oferta",
        "won" => "Wygrany",
        "lost" => "Przegrany",
    ];
}

function upsellio_extract_search_query_from_referrer(string $referrer): string
{
    if ($referrer === "") {
        return "";
    }
    $host = (string) wp_parse_url($referrer, PHP_URL_HOST);
    if ($host === "" || !preg_match("/(google|bing|yahoo|duckduckgo|yandex)\./i", $host)) {
        return "";
    }
    $query = (string) wp_parse_url($referrer, PHP_URL_QUERY);
    if ($query === "") {
        return "";
    }
    parse_str($query, $params);
    $value = isset($params["q"]) ? (string) $params["q"] : (isset($params["p"]) ? (string) $params["p"] : "");
    return function_exists("mb_substr")
        ? mb_substr(trim($value), 0, 200)
        : substr(trim($value), 0, 200);
}

function upsellio_crm_attribute_lead_to_gsc_keyword(int $lead_id, string $landing_url, string $referrer): void
{
    $rows = get_option("upsellio_keyword_metrics_rows", []);
    if (!is_array($rows) || $landing_url === "") {
        return;
    }
    $landing_path = (string) wp_parse_url($landing_url, PHP_URL_PATH);
    if ($landing_path === "") {
        return;
    }

    $matched = [];
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $row_path = (string) wp_parse_url((string) ($row["page"] ?? ""), PHP_URL_PATH);
        if ($row_path !== $landing_path) {
            continue;
        }
        $matched[] = $row;
    }
    if (empty($matched)) {
        return;
    }

    usort($matched, static function ($a, $b) {
        return (int) ($b["clicks"] ?? 0) <=> (int) ($a["clicks"] ?? 0);
    });

    $top3 = array_slice($matched, 0, 3);
    $candidates = array_map(static function ($row) {
        return [
            "query" => (string) ($row["query"] ?? ""),
            "clicks" => (int) ($row["clicks"] ?? 0),
            "position" => (float) ($row["position"] ?? 0),
        ];
    }, $top3);

    update_post_meta($lead_id, "_upsellio_lead_gsc_top_queries", $candidates);
    if (!empty($candidates[0]["query"])) {
        update_post_meta($lead_id, "_upsellio_lead_gsc_likely_query", (string) $candidates[0]["query"]);
    }
    $referrerQuery = upsellio_extract_search_query_from_referrer($referrer);
    if ($referrerQuery !== "") {
        update_post_meta($lead_id, "_upsellio_lead_gsc_query", $referrerQuery);
    }
}

function upsellio_crm_get_default_owner_id()
{
    $adminUsers = get_users([
        "role__in" => ["administrator"],
        "number" => 1,
        "orderby" => "ID",
        "order" => "ASC",
        "fields" => ["ID"],
    ]);
    if (!empty($adminUsers) && isset($adminUsers[0]->ID)) {
        return (int) $adminUsers[0]->ID;
    }
    return 1;
}

/**
 * Zgłoszenia z formularza idą przez admin-post bez zalogowanego użytkownika (ID 0).
 * WordPress wymaga przy tworzeniu wpisów CPT uprawnień — tymczasowo ustawiamy właściciela.
 *
 * @template T
 * @param callable(): T $callback
 * @return T
 */
function upsellio_crm_run_as_user($user_id, $callback)
{
    $user_id = (int) $user_id;
    if ($user_id <= 0) {
        $user_id = upsellio_crm_get_default_owner_id();
    }
    $prev = get_current_user_id();
    wp_set_current_user($user_id);
    try {
        return $callback();
    } finally {
        wp_set_current_user($prev);
    }
}

function upsellio_crm_register_post_type()
{
    register_post_type("lead", [
        "labels" => [
            "name" => __("Leady", "upsellio"),
            "singular_name" => __("Lead", "upsellio"),
            "menu_name" => __("CRM Leady", "upsellio"),
            "add_new" => __("Dodaj lead", "upsellio"),
            "add_new_item" => __("Dodaj nowy lead", "upsellio"),
            "edit_item" => __("Edytuj lead", "upsellio"),
            "new_item" => __("Nowy lead", "upsellio"),
            "view_item" => __("Pokaż lead", "upsellio"),
            "search_items" => __("Szukaj leadów", "upsellio"),
        ],
        "public" => false,
        "show_ui" => true,
        "show_in_menu" => false,
        "menu_position" => 25,
        "menu_icon" => "dashicons-groups",
        "supports" => ["title", "editor", "author"],
        "capability_type" => "post",
        "map_meta_cap" => true,
    ]);
}
add_action("init", "upsellio_crm_register_post_type");

function upsellio_crm_register_task_post_type()
{
    register_post_type("lead_task", [
        "labels" => [
            "name" => __("Zadania CRM", "upsellio"),
            "singular_name" => __("Zadanie CRM", "upsellio"),
        ],
        "public" => false,
        "show_ui" => false,
        "supports" => ["title", "author"],
        "capability_type" => "post",
        "map_meta_cap" => true,
    ]);
}
add_action("init", "upsellio_crm_register_task_post_type");

function upsellio_crm_register_taxonomies()
{
    register_taxonomy("lead_status", "lead", [
        "labels" => [
            "name" => __("Statusy leadów", "upsellio"),
            "singular_name" => __("Status leada", "upsellio"),
        ],
        "public" => false,
        "show_ui" => true,
        "show_in_menu" => false,
        "show_admin_column" => true,
        "hierarchical" => false,
    ]);

    register_taxonomy("lead_source", "lead", [
        "labels" => [
            "name" => __("Źródła leadów", "upsellio"),
            "singular_name" => __("Źródło leada", "upsellio"),
        ],
        "public" => false,
        "show_ui" => true,
        "show_in_menu" => false,
        "show_admin_column" => true,
        "hierarchical" => false,
    ]);
}
add_action("init", "upsellio_crm_register_taxonomies");

function upsellio_crm_ensure_default_terms()
{
    $statuses = upsellio_crm_get_default_statuses();
    foreach ($statuses as $slug => $name) {
        if (!term_exists($slug, "lead_status")) {
            wp_insert_term($name, "lead_status", ["slug" => $slug]);
        }
    }

    $sources = [
        "contact-form" => "Formularz kontaktowy",
        "hero-microform" => "Mikroformularz hero",
        "audit-form" => "Formularz audytu",
        "blog-form" => "Formularz blogowy",
        "newsletter" => "Newsletter",
        "mailto-click" => "Klik mailto",
        "tel-click" => "Klik tel",
    ];
    foreach ($sources as $slug => $name) {
        if (!term_exists($slug, "lead_source")) {
            wp_insert_term($name, "lead_source", ["slug" => $slug]);
        }
    }
}
add_action("init", "upsellio_crm_ensure_default_terms", 20);

function upsellio_crm_get_term_id_by_slug($taxonomy, $slug)
{
    $term = get_term_by("slug", $slug, $taxonomy);
    return $term && !is_wp_error($term) ? (int) $term->term_id : 0;
}

function upsellio_crm_add_timeline_event($lead_id, $type, $message)
{
    $lead_id = (int) $lead_id;
    if ($lead_id <= 0) {
        return;
    }

    $events = get_post_meta($lead_id, "_upsellio_lead_timeline", true);
    if (!is_array($events)) {
        $events = [];
    }

    $events[] = [
        "timestamp" => current_time("mysql"),
        "type" => sanitize_key($type),
        "message" => sanitize_text_field($message),
        "user_id" => get_current_user_id(),
    ];
    update_post_meta($lead_id, "_upsellio_lead_timeline", $events);
}

function upsellio_crm_create_lead($payload)
{
    $name = sanitize_text_field($payload["name"] ?? "");
    $email = sanitize_email($payload["email"] ?? "");
    $message = sanitize_textarea_field($payload["message"] ?? "");
    $company = sanitize_text_field($payload["company"] ?? "");
    $phone = sanitize_text_field($payload["phone"] ?? "");
    $service = sanitize_text_field($payload["service"] ?? "");
    $budget = sanitize_text_field($payload["budget"] ?? "");
    $goal = sanitize_text_field($payload["goal"] ?? "");
    $score = isset($payload["score"]) ? (int) $payload["score"] : 0;
    $formOrigin = sanitize_text_field($payload["form_origin"] ?? "contact-form");
    $source = sanitize_title($payload["source"] ?? $formOrigin);
    $utmSource = sanitize_text_field($payload["utm_source"] ?? "");
    $utmMedium = sanitize_text_field($payload["utm_medium"] ?? "");
    $utmCampaign = sanitize_text_field($payload["utm_campaign"] ?? "");
    $utmTerm = sanitize_text_field($payload["utm_term"] ?? "");
    $utmContent = sanitize_text_field($payload["utm_content"] ?? "");
    $gclid = sanitize_text_field($payload["gclid"] ?? "");
    $fbclid = sanitize_text_field($payload["fbclid"] ?? "");
    $msclkid = sanitize_text_field($payload["msclkid"] ?? "");
    $eventId = sanitize_text_field($payload["event_id"] ?? "");
    $gscQuery = sanitize_text_field($payload["gsc_query"] ?? "");
    $quizProblem = sanitize_text_field($payload["quiz_problem"] ?? "");
    $quizIndustry = sanitize_text_field($payload["quiz_industry"] ?? "");
    $quizBudget = sanitize_text_field($payload["quiz_budget"] ?? "");
    $landingUrl = esc_url_raw($payload["landing_url"] ?? "");
    $referrer = esc_url_raw($payload["referrer"] ?? "");
    $monthlySales = sanitize_text_field($payload["monthly_sales"] ?? "");
    $consentTimestamp = sanitize_text_field($payload["consent_timestamp"] ?? "");
    $consentIp = sanitize_text_field($payload["consent_ip"] ?? "");
    $consentUa = sanitize_text_field($payload["consent_ua"] ?? "");
    $consentForm = sanitize_text_field($payload["consent_form"] ?? "");
    $consentVersion = sanitize_text_field($payload["consent_version"] ?? "");
    $ownerId = isset($payload["owner_id"]) ? (int) $payload["owner_id"] : 0;
    if ($ownerId <= 0) {
        $ownerId = upsellio_crm_get_default_owner_id();
    }

    return upsellio_crm_run_as_user($ownerId, static function () use (
        $name,
        $email,
        $message,
        $company,
        $phone,
        $service,
        $budget,
        $goal,
        $score,
        $formOrigin,
        $source,
        $utmSource,
        $utmMedium,
        $utmCampaign,
        $utmTerm,
        $utmContent,
        $gclid,
        $fbclid,
        $msclkid,
        $eventId,
        $gscQuery,
        $quizProblem,
        $quizIndustry,
        $quizBudget,
        $landingUrl,
        $referrer,
        $monthlySales,
        $consentTimestamp,
        $consentIp,
        $consentUa,
        $consentForm,
        $consentVersion,
        $ownerId
    ) {
    $title = $name !== "" ? $name : ($email !== "" ? $email : "Nowy lead");
    $leadId = upsellio_crm_find_existing_wp_lead_by_email($email);
    $isNewLead = $leadId <= 0;

    if ($isNewLead) {
        $leadId = wp_insert_post([
            "post_type" => "lead",
            "post_status" => "publish",
            "post_title" => $title,
            "post_content" => $message,
            "post_author" => $ownerId,
        ], true);

        if (is_wp_error($leadId)) {
            return 0;
        }
    } else {
        $existingMessage = (string) get_post_field("post_content", $leadId);
        $newChunk = trim((string) $message);
        if ($newChunk !== "") {
            $merged = trim($existingMessage);
            if ($merged !== "") {
                $merged .= "\n\n---\n" . current_time("mysql") . "\n" . $newChunk;
            } else {
                $merged = $newChunk;
            }
            wp_update_post([
                "ID" => (int) $leadId,
                "post_content" => $merged,
                "post_title" => $title !== "" ? $title : get_the_title((int) $leadId),
            ]);
        }
    }

    update_post_meta($leadId, "_upsellio_lead_email", $email);
    update_post_meta($leadId, "_upsellio_lead_company", $company);
    update_post_meta($leadId, "_upsellio_lead_phone", $phone);
    update_post_meta($leadId, "_upsellio_lead_service", $service);
    update_post_meta($leadId, "_upsellio_lead_budget", $budget);
    update_post_meta($leadId, "_upsellio_lead_goal", $goal);
    update_post_meta($leadId, "_upsellio_lead_monthly_sales", $monthlySales);
    update_post_meta($leadId, "_upsellio_lead_score", $score);
    update_post_meta($leadId, "_upsellio_lead_form_origin", $formOrigin);
    update_post_meta($leadId, "_upsellio_lead_utm_source", $utmSource);
    update_post_meta($leadId, "_upsellio_lead_utm_medium", $utmMedium);
    update_post_meta($leadId, "_upsellio_lead_utm_campaign", $utmCampaign);
    update_post_meta($leadId, "_upsellio_lead_utm_term", $utmTerm);
    update_post_meta($leadId, "_upsellio_lead_utm_content", $utmContent);
    update_post_meta($leadId, "_upsellio_lead_gclid", $gclid);
    update_post_meta($leadId, "_upsellio_lead_fbclid", $fbclid);
    update_post_meta($leadId, "_upsellio_lead_msclkid", $msclkid);
    update_post_meta($leadId, "_upsellio_lead_event_id", $eventId);
    update_post_meta($leadId, "_upsellio_lead_gsc_query", $gscQuery);
    update_post_meta($leadId, "_upsellio_lead_quiz_problem", $quizProblem);
    update_post_meta($leadId, "_upsellio_lead_quiz_industry", $quizIndustry);
    update_post_meta($leadId, "_upsellio_lead_quiz_budget", $quizBudget);
    update_post_meta($leadId, "_upsellio_lead_landing_url", $landingUrl);
    update_post_meta($leadId, "_upsellio_lead_referrer", $referrer);
    update_post_meta($leadId, "_upsellio_lead_consent_ua", $consentUa);
    if (function_exists("upsellio_crm_attribute_lead_to_gsc_keyword")) {
        upsellio_crm_attribute_lead_to_gsc_keyword((int) $leadId, (string) $landingUrl, (string) $referrer);
    }

    if ($isNewLead) {
        update_post_meta($leadId, "_upsellio_lead_consent_at", $consentTimestamp);
        update_post_meta($leadId, "_upsellio_lead_consent_ip", $consentIp);
        update_post_meta($leadId, "_upsellio_lead_consent_form", $consentForm);
        update_post_meta($leadId, "_upsellio_lead_consent_version", $consentVersion);
        $statusTermId = upsellio_crm_get_term_id_by_slug("lead_status", "new");
        if ($statusTermId > 0) {
            wp_set_object_terms($leadId, [$statusTermId], "lead_status", false);
        }
    }

    $sourceTermId = upsellio_crm_get_term_id_by_slug("lead_source", $source);
    if ($sourceTermId <= 0) {
        $sourceTerm = wp_insert_term($formOrigin, "lead_source", ["slug" => $source]);
        if (!is_wp_error($sourceTerm) && isset($sourceTerm["term_id"])) {
            $sourceTermId = (int) $sourceTerm["term_id"];
        }
    }
    if ($sourceTermId > 0) {
        wp_set_object_terms($leadId, [$sourceTermId], "lead_source", false);
    }

    if ($isNewLead) {
        upsellio_crm_add_timeline_event($leadId, "created", "Lead został utworzony.");
        upsellio_crm_create_followup_tasks_for_owner($leadId, $ownerId);
    } else {
        upsellio_crm_add_timeline_event($leadId, "updated", "Scalono nowe zgłoszenie po adresie e-mail.");
    }

    upsellio_crm_sync_wp_lead_to_crm_lead_module((int) $leadId);
    upsellio_crm_sync_wp_lead_to_inbox_offer((int) $leadId);

    if ($isNewLead) {
        do_action("upsellio_crm_contact_lead_created", (int) $leadId);
    }

    return (int) $leadId;
    });
}

function upsellio_crm_find_existing_wp_lead_by_email($email)
{
    $email = sanitize_email((string) $email);
    if (!is_email($email)) {
        return 0;
    }

    $ids = get_posts([
        "post_type" => "lead",
        "post_status" => ["publish", "private", "draft", "pending"],
        "posts_per_page" => 1,
        "fields" => "ids",
        "orderby" => "modified",
        "order" => "DESC",
        "meta_query" => [[
            "key" => "_upsellio_lead_email",
            "value" => $email,
            "compare" => "=",
        ]],
    ]);

    return !empty($ids) ? (int) $ids[0] : 0;
}

/**
 * Moduł CRM (CPT crm_lead) — inbox / Leady / scoring. Formularze WWW zapisują legacy CPT `lead`;
 * ta funkcja tworzy lustrzany wpis crm_lead, żeby zgłoszenia były widoczne w aplikacji CRM.
 *
 * @return int ID crm_lead lub 0
 */
function upsellio_crm_sync_wp_lead_to_crm_lead_module($wp_lead_id)
{
    $wp_lead_id = (int) $wp_lead_id;
    if ($wp_lead_id <= 0 || get_post_type($wp_lead_id) !== "lead") {
        return 0;
    }
    if (!post_type_exists("crm_lead")) {
        return 0;
    }

    $existing = (int) get_post_meta($wp_lead_id, "_upsellio_synced_crm_lead_id", true);
    if ($existing > 0 && get_post_type($existing) === "crm_lead") {
        return $existing;
    }

    $post = get_post($wp_lead_id);
    if (!($post instanceof WP_Post)) {
        return 0;
    }

    $email = (string) get_post_meta($wp_lead_id, "_upsellio_lead_email", true);
    $phone = (string) get_post_meta($wp_lead_id, "_upsellio_lead_phone", true);
    $company = (string) get_post_meta($wp_lead_id, "_upsellio_lead_company", true);
    $service = (string) get_post_meta($wp_lead_id, "_upsellio_lead_service", true);
    $goal = (string) get_post_meta($wp_lead_id, "_upsellio_lead_goal", true);
    $budget_raw = (string) get_post_meta($wp_lead_id, "_upsellio_lead_budget", true);
    $origin = (string) get_post_meta($wp_lead_id, "_upsellio_lead_form_origin", true);

    $need = (string) $post->post_content;
    $notes_parts = [];
    if ($company !== "") {
        $notes_parts[] = "Firma: " . $company;
    }
    if ($service !== "") {
        $notes_parts[] = "Obszar / usługa: " . $service;
    }
    if ($goal !== "") {
        $notes_parts[] = "Cel: " . $goal;
    }
    $notes = implode("\n", $notes_parts);

    $budget_float = 0.0;
    if ($budget_raw !== "" && is_numeric($budget_raw)) {
        $budget_float = (float) $budget_raw;
    }

    $sync_author = (int) $post->post_author;
    if ($sync_author <= 0) {
        $sync_author = upsellio_crm_get_default_owner_id();
    }

    return (int) upsellio_crm_run_as_user($sync_author, static function () use (
        $wp_lead_id,
        $post,
        $email,
        $phone,
        $origin,
        $need,
        $notes,
        $budget_float,
        $sync_author
    ) {
        $crm_id = (int) wp_insert_post([
            "post_type" => "crm_lead",
            "post_status" => "publish",
            "post_title" => $post->post_title,
            "post_author" => $sync_author,
        ], true);

        if (is_wp_error($crm_id) || $crm_id <= 0) {
            return 0;
        }

        update_post_meta($crm_id, "_ups_lead_email", sanitize_email($email));
        update_post_meta($crm_id, "_ups_lead_phone", sanitize_text_field($phone));
        update_post_meta(
            $crm_id,
            "_ups_lead_source",
            $origin !== "" ? sanitize_text_field($origin) : "web-form"
        );
        update_post_meta($crm_id, "_ups_lead_type", "inbound");
        update_post_meta($crm_id, "_ups_lead_qualification_status", "new");
        update_post_meta($crm_id, "_ups_lead_need", sanitize_textarea_field($need));
        update_post_meta($crm_id, "_ups_lead_budget", $budget_float);
        update_post_meta($crm_id, "_ups_lead_notes", sanitize_textarea_field($notes));
        update_post_meta(
            $crm_id,
            "_ups_lead_utm_source",
            sanitize_text_field((string) get_post_meta($wp_lead_id, "_upsellio_lead_utm_source", true))
        );
        update_post_meta(
            $crm_id,
            "_ups_lead_utm_medium",
            sanitize_text_field((string) get_post_meta($wp_lead_id, "_upsellio_lead_utm_medium", true))
        );
        update_post_meta(
            $crm_id,
            "_ups_lead_utm_campaign",
            sanitize_text_field((string) get_post_meta($wp_lead_id, "_upsellio_lead_utm_campaign", true))
        );

        update_post_meta($wp_lead_id, "_upsellio_synced_crm_lead_id", $crm_id);
        update_post_meta($crm_id, "_upsellio_wp_lead_id", $wp_lead_id);

        if (function_exists("upsellio_sales_engine_refresh_lead_hybrid_scores")) {
            upsellio_sales_engine_refresh_lead_hybrid_scores($crm_id);
        }

        return $crm_id;
    });
}

/**
 * Zapewnia obecność leada webowego w module Inbox (crm_offer + pierwszy inbound message).
 *
 * @return int ID crm_offer lub 0
 */
function upsellio_crm_sync_wp_lead_to_inbox_offer($wp_lead_id)
{
    $wp_lead_id = (int) $wp_lead_id;
    if ($wp_lead_id <= 0 || get_post_type($wp_lead_id) !== "lead") {
        return 0;
    }
    if (!post_type_exists("crm_offer") || !function_exists("upsellio_inbox_append_message")) {
        return 0;
    }

    $existing = (int) get_post_meta($wp_lead_id, "_upsellio_synced_inbox_offer_id", true);
    if ($existing > 0 && get_post_type($existing) === "crm_offer") {
        return $existing;
    }

    $post = get_post($wp_lead_id);
    if (!($post instanceof WP_Post)) {
        return 0;
    }

    $leadTitle = trim((string) $post->post_title);
    $leadEmail = sanitize_email((string) get_post_meta($wp_lead_id, "_upsellio_lead_email", true));
    $leadFormOrigin = sanitize_text_field((string) get_post_meta($wp_lead_id, "_upsellio_lead_form_origin", true));
    $leadMessage = trim((string) $post->post_content);
    $leadUtmSource = sanitize_text_field((string) get_post_meta($wp_lead_id, "_upsellio_lead_utm_source", true));
    $leadUtmCampaign = sanitize_text_field((string) get_post_meta($wp_lead_id, "_upsellio_lead_utm_campaign", true));

    $offerId = upsellio_crm_find_existing_inbox_offer_by_email($leadEmail);
    if ($offerId <= 0) {
        $offerTitle = $leadTitle !== "" ? $leadTitle : ($leadEmail !== "" ? $leadEmail : "Lead web");
        $offerId = (int) wp_insert_post([
            "post_type" => "crm_offer",
            "post_status" => "publish",
            "post_title" => "Lead web: " . $offerTitle,
            "post_author" => (int) $post->post_author,
        ], true);
    }

    if ($offerId <= 0 || is_wp_error($offerId)) {
        return 0;
    }

    update_post_meta($offerId, "_ups_offer_status", "open");
    update_post_meta($offerId, "_ups_offer_stage", "awareness");
    update_post_meta($offerId, "_ups_offer_client_id", 0);
    update_post_meta($offerId, "_ups_offer_contact_email", $leadEmail);
    update_post_meta($offerId, "_ups_offer_form_origin", $leadFormOrigin !== "" ? $leadFormOrigin : "web-form");
    if ($leadUtmSource !== "") {
        update_post_meta($offerId, "_ups_offer_utm_source", $leadUtmSource);
    }
    if ($leadUtmCampaign !== "") {
        update_post_meta($offerId, "_ups_offer_utm_campaign", $leadUtmCampaign);
    }

    $sender = function_exists("upsellio_followup_get_sender_settings") ? upsellio_followup_get_sender_settings() : [];
    upsellio_inbox_append_message($offerId, [
        "direction" => "in",
        "from" => $leadEmail,
        "to" => sanitize_email((string) ($sender["from_email"] ?? "")),
        "subject" => "Nowy lead z formularza",
        "body_plain" => $leadMessage !== "" ? $leadMessage : "Lead przesłał formularz bez dodatkowej wiadomości.",
        "body_html" => "",
        "source" => "lead_form",
        "read" => false,
    ]);

    update_post_meta($wp_lead_id, "_upsellio_synced_inbox_offer_id", $offerId);
    update_post_meta($offerId, "_upsellio_wp_lead_id", $wp_lead_id);

    return $offerId;
}

function upsellio_crm_find_existing_inbox_offer_by_email($email)
{
    $email = sanitize_email((string) $email);
    if (!is_email($email)) {
        return 0;
    }

    $offers = get_posts([
        "post_type" => "crm_offer",
        "post_status" => ["publish", "draft", "pending", "private"],
        "posts_per_page" => 1,
        "fields" => "ids",
        "orderby" => "modified",
        "order" => "DESC",
        "meta_query" => [[
            "key" => "_ups_offer_contact_email",
            "value" => $email,
            "compare" => "=",
        ]],
    ]);

    return !empty($offers) ? (int) $offers[0] : 0;
}

function upsellio_crm_get_open_tasks_for_lead($lead_id)
{
    $lead_id = (int) $lead_id;
    return get_posts([
        "post_type" => "lead_task",
        "post_status" => "publish",
        "posts_per_page" => 50,
        "meta_query" => [
            [
                "key" => "_upsellio_task_lead_id",
                "value" => (string) $lead_id,
            ],
            [
                "key" => "_upsellio_task_status",
                "value" => "open",
            ],
        ],
    ]);
}

function upsellio_crm_mark_lead_tasks_done($lead_id, $note = "")
{
    $tasks = upsellio_crm_get_open_tasks_for_lead($lead_id);
    foreach ($tasks as $task) {
        update_post_meta($task->ID, "_upsellio_task_status", "done");
    }
    if ($note !== "") {
        upsellio_crm_add_timeline_event((int) $lead_id, "task", $note);
    }
}

function upsellio_crm_create_followup_tasks_for_owner($lead_id, $owner_id)
{
    $lead_id = (int) $lead_id;
    $owner_id = (int) $owner_id;
    if ($lead_id <= 0 || $owner_id <= 0) {
        return;
    }

    $templates = [
        ["type" => "followup-4h", "hours" => 4, "label" => "Follow-up 4h"],
        ["type" => "followup-24h", "hours" => 24, "label" => "Follow-up 24h"],
    ];

    foreach ($templates as $template) {
        $dueTs = time() + ((int) $template["hours"] * HOUR_IN_SECONDS);
        $taskId = wp_insert_post([
            "post_type" => "lead_task",
            "post_status" => "publish",
            "post_title" => $template["label"] . " - Lead #" . $lead_id,
            "post_author" => $owner_id,
        ], true);
        if (is_wp_error($taskId)) {
            continue;
        }
        update_post_meta($taskId, "_upsellio_task_lead_id", $lead_id);
        update_post_meta($taskId, "_upsellio_task_type", $template["type"]);
        update_post_meta($taskId, "_upsellio_task_due_at", (string) $dueTs);
        update_post_meta($taskId, "_upsellio_task_status", "open");
        wp_schedule_single_event($dueTs, "upsellio_crm_task_due", [(int) $taskId]);
    }
}

function upsellio_crm_schedule_followup($lead_id)
{
    $lead_id = (int) $lead_id;
    if ($lead_id <= 0) {
        return;
    }
    wp_schedule_single_event(time() + (24 * HOUR_IN_SECONDS), "upsellio_crm_followup_reminder", [$lead_id]);
}

function upsellio_crm_get_request_ip_hash()
{
    $candidates = [
        $_SERVER["HTTP_CF_CONNECTING_IP"] ?? "",
        $_SERVER["HTTP_X_FORWARDED_FOR"] ?? "",
        $_SERVER["REMOTE_ADDR"] ?? "",
    ];

    foreach ($candidates as $candidate) {
        $candidate = trim((string) $candidate);
        if ($candidate === "") {
            continue;
        }
        $ip = trim(explode(",", $candidate)[0]);
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return hash_hmac("sha256", $ip, wp_salt("auth"));
        }
    }

    return "";
}

function upsellio_crm_is_rate_limited($email)
{
    $limits = [];
    $ipHash = upsellio_crm_get_request_ip_hash();
    if ($ipHash !== "") {
        $limits["ip_" . $ipHash] = 5;
    }

    $email = sanitize_email((string) $email);
    if (is_email($email)) {
        $limits["email_" . hash_hmac("sha256", strtolower($email), wp_salt("auth"))] = 3;
    }

    foreach ($limits as $key => $maxAttempts) {
        $transientKey = "ups_crm_rl_" . md5($key);
        $attempts = (int) get_transient($transientKey);
        if ($attempts >= $maxAttempts) {
            return true;
        }
        set_transient($transientKey, $attempts + 1, HOUR_IN_SECONDS);
    }

    return false;
}

function upsellio_crm_is_endpoint_rate_limited(): bool
{
    $ip_hash = upsellio_crm_get_request_ip_hash();
    if ($ip_hash === "") {
        return false;
    }

    $transient_key = "ups_lead_rl_ip_" . md5($ip_hash);
    $attempts = (int) get_transient($transient_key);
    if ($attempts >= 25) {
        return true;
    }

    set_transient($transient_key, $attempts + 1, HOUR_IN_SECONDS);
    return false;
}

function upsellio_crm_get_backup_recipient()
{
    $optionEmail = sanitize_email((string) get_option("upsellio_backup_lead_email", ""));
    if (is_email($optionEmail)) {
        return $optionEmail;
    }

    $envEmail = sanitize_email((string) getenv("UPSELLIO_BACKUP_LEAD_EMAIL"));
    if (is_email($envEmail)) {
        return $envEmail;
    }

    return "kontakt@upsellio.pl";
}

function upsellio_crm_send_emails($lead_id, $name, $email, $message)
{
    $adminEmail = sanitize_email((string) get_option("admin_email"));
    $backupEmail = upsellio_crm_get_backup_recipient();
    $recipient = is_email($adminEmail) ? $adminEmail : $backupEmail;
    $score = (int) get_post_meta($lead_id, "_upsellio_lead_score", true);
    $score_reason = (string) get_post_meta($lead_id, "_upsellio_lead_score_reason", true);
    $company = (string) get_post_meta($lead_id, "_upsellio_lead_company", true);
    $service = (string) get_post_meta($lead_id, "_upsellio_lead_service", true);
    $utm_source = (string) get_post_meta($lead_id, "_upsellio_lead_utm_source", true);
    $gclid = get_post_meta($lead_id, "_upsellio_lead_gclid", true) ? "✓" : "";
    $quiz_industry = (string) get_post_meta($lead_id, "_upsellio_lead_quiz_industry", true);
    $quiz_problem = (string) get_post_meta($lead_id, "_upsellio_lead_quiz_problem", true);
    $quiz_budget = (string) get_post_meta($lead_id, "_upsellio_lead_quiz_budget", true);
    $consent_at = (string) get_post_meta($lead_id, "_upsellio_lead_consent_at", true);
    $edit_url = admin_url("post.php?post=" . (int) $lead_id . "&action=edit");
    $subject = "🎯 Lead: {$name}" . ($company !== "" ? " ({$company})" : "") . " · score {$score}";
    $body = "<p><strong>" . esc_html($name) . "</strong>" . ($company !== "" ? " · " . esc_html($company) : "") . "<br><span style='font-size:13px;color:#64748b;'>" . esc_html($email) . "</span></p>
<div style='background:#f0fdfa;border-left:4px solid #0d9488;padding:14px 16px;margin:14px 0;'><strong>AI Score: {$score}/100</strong><p style='margin:6px 0 0;font-size:13px;'>" . esc_html($score_reason) . "</p></div>
<p><strong>Zgłoszenie:</strong></p><blockquote style='border-left:3px solid #e5e7eb;padding:8px 12px;color:#475569;font-style:italic;'>" . nl2br(esc_html($message)) . "</blockquote>
<p><strong>Kontekst:</strong></p><table style='font-size:13px;border-collapse:collapse;'>
<tr><td style='padding:3px 12px 3px 0;color:#64748b;'>Usługa:</td><td>" . esc_html($service ?: "—") . "</td></tr>
<tr><td style='padding:3px 12px 3px 0;color:#64748b;'>Quiz:</td><td>" . esc_html($quiz_problem) . " · " . esc_html($quiz_industry) . " · " . esc_html($quiz_budget) . "</td></tr>
<tr><td style='padding:3px 12px 3px 0;color:#64748b;'>Źródło:</td><td>" . esc_html($utm_source ?: "organic") . " " . esc_html($gclid) . "</td></tr>
<tr><td style='padding:3px 12px 3px 0;color:#64748b;'>Zgoda:</td><td>" . esc_html($consent_at !== "" ? $consent_at : "brak daty") . "</td></tr>
</table>
<p><a href='" . esc_url($edit_url) . "' class='ups-cta'>Otwórz lead w CRM →</a></p>";
    $adminSent = is_email($recipient) && function_exists("upsellio_send_crm_mail")
        ? upsellio_send_crm_mail($recipient, $subject, $body, [
            "type" => "transactional",
            "preheader" => "AI score {$score}. " . wp_trim_words($message, 10),
            "allow_internal" => true,
        ])
        : false;
    if (!$adminSent && is_email($backupEmail) && strtolower($backupEmail) !== strtolower((string) $recipient)) {
        $adminSent = function_exists("upsellio_send_crm_mail")
            ? upsellio_send_crm_mail($backupEmail, $subject, $body, ["type" => "transactional", "allow_internal" => true])
            : false;
    }
    if (!$adminSent) {
        upsellio_crm_add_timeline_event((int) $lead_id, "mail_error", "Nie udało się wysłać powiadomienia e-mail do administratora.");
    }

    if (is_email($email)) {
        $first_name_parts = preg_split('/\s+/', trim((string) $name));
        $first_name = $first_name_parts[0] ?? "Cześć";
        $autoresponderSubject = "Mam Twoje zgłoszenie — odezwę się jutro do 14:00";
        $autoresponderBody = "<p>Cześć " . esc_html($first_name) . ",</p>
<p><strong>Mam Twoje zgłoszenie.</strong> Jutro do 14:00 dostaniesz ode mnie konkretną odpowiedź — nie szablon, tylko wstępną diagnozę.</p>
<p><strong>Co zrobię w tych ~24h:</strong></p>
<ul style='margin:0 0 14px 20px;padding:0;'><li>Rzut oka na stronę i kampanie</li><li>Szybki benchmark Search Console i CPL</li><li>2-3 największe blokery lejka</li></ul>
<p>Jeśli masz pilny temat — <a href='tel:+48575522595'>+48 575 522 595</a>.</p>
<p>Do jutra,<br><strong>Sebastian Kelm</strong></p>";
        $sent_lead = function_exists("upsellio_send_crm_mail")
            ? upsellio_send_crm_mail($email, $autoresponderSubject, $autoresponderBody, [
                "type" => "transactional",
                "preheader" => "Konkretna odpowiedź w ciągu 24h. Nie auto-reply.",
                "reply_to" => (string) get_option("ups_followup_from_email", get_bloginfo("admin_email")),
            ])
            : false;
        if (!$sent_lead) {
            upsellio_crm_add_timeline_event((int) $lead_id, "mail_error", "Nie udało się wysłać autorespondera do leada.");
        }
    }
}

function upsellio_crm_redirect_lead_form_success($redirect_url)
{
    $base = remove_query_arg(["ups_lead_status", "ups_lead_reason"], (string) $redirect_url);
    wp_safe_redirect(add_query_arg("ups_lead_status", "success", $base));
    exit;
}

/**
 * @param string $redirect_url
 * @param string $reason       nonce|fields|rate|save
 */
function upsellio_crm_redirect_lead_form_error($redirect_url, $reason)
{
    $base = remove_query_arg(["ups_lead_status", "ups_lead_reason"], (string) $redirect_url);
    $reason_key = sanitize_key($reason);
    if ($reason_key === "") {
        $reason_key = "fields";
    }
    wp_safe_redirect(add_query_arg(
        [
            "ups_lead_status" => "error",
            "ups_lead_reason" => $reason_key,
        ],
        $base
    ));
    exit;
}

function upsellio_crm_handle_lead_submission()
{
    $rawRedirectUrl = isset($_POST["redirect_url"]) ? wp_unslash($_POST["redirect_url"]) : "";
    $redirectUrl = function_exists("upsellio_normalize_internal_redirect_url")
        ? upsellio_normalize_internal_redirect_url((string) $rawRedirectUrl, home_url("/"))
        : home_url("/");

    if (upsellio_crm_is_endpoint_rate_limited()) {
        upsellio_crm_redirect_lead_form_error($redirectUrl, "rate");
    }

    if (!isset($_POST["upsellio_lead_form_nonce"]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST["upsellio_lead_form_nonce"])), "upsellio_unified_lead_form")) {
        upsellio_crm_redirect_lead_form_error($redirectUrl, "nonce");
    }

    $honeypot = isset($_POST["lead_website"]) ? sanitize_text_field(wp_unslash($_POST["lead_website"])) : "";
    $honeypot_alt = isset($_POST["lead_company_url"]) ? sanitize_text_field(wp_unslash($_POST["lead_company_url"])) : "";
    if ($honeypot !== "" || $honeypot_alt !== "") {
        upsellio_crm_redirect_lead_form_success($redirectUrl);
    }

    $name = isset($_POST["lead_name"]) ? sanitize_text_field(wp_unslash($_POST["lead_name"])) : "";
    $email = isset($_POST["lead_email"]) ? sanitize_email(wp_unslash($_POST["lead_email"])) : "";
    $message = isset($_POST["lead_message"]) ? sanitize_textarea_field(wp_unslash($_POST["lead_message"])) : "";
    $consent = isset($_POST["lead_consent"]) ? sanitize_text_field(wp_unslash($_POST["lead_consent"])) : "";

    if ($name === "" || !is_email($email) || $message === "" || $consent !== "1") {
        upsellio_crm_redirect_lead_form_error($redirectUrl, "fields");
    }

    $consentTimestamp = current_time("mysql");
    $consentIp = isset($_SERVER["REMOTE_ADDR"])
        ? sanitize_text_field(wp_unslash($_SERVER["REMOTE_ADDR"]))
        : "";
    $consentUa = isset($_SERVER["HTTP_USER_AGENT"])
        ? sanitize_text_field(wp_unslash($_SERVER["HTTP_USER_AGENT"]))
        : "";
    $consentForm = isset($_POST["lead_form_origin"])
        ? sanitize_text_field(wp_unslash($_POST["lead_form_origin"]))
        : "contact-form";
    $consentVersion = sanitize_text_field((string) get_option("ups_consent_policy_version", "v1.0"));

    if (upsellio_crm_is_rate_limited($email)) {
        upsellio_crm_redirect_lead_form_error($redirectUrl, "rate");
    }

    $company = isset($_POST["lead_company"]) ? sanitize_text_field(wp_unslash($_POST["lead_company"])) : "";
    if ($company !== "" && strpos($message, $company) === false) {
        $message .= "\n\nStrona / firma: " . $company;
    }

    $payload = [
        "name" => $name,
        "email" => $email,
        "company" => $company,
        "phone" => isset($_POST["lead_phone"]) ? sanitize_text_field(wp_unslash($_POST["lead_phone"])) : "",
        "message" => $message,
        "service" => isset($_POST["lead_service"]) ? sanitize_text_field(wp_unslash($_POST["lead_service"])) : "",
        "budget" => isset($_POST["lead_budget"]) ? sanitize_text_field(wp_unslash($_POST["lead_budget"])) : "",
        "goal" => isset($_POST["lead_goal"]) ? sanitize_text_field(wp_unslash($_POST["lead_goal"])) : "",
        "monthly_sales" => isset($_POST["lead_monthly_sales"]) ? sanitize_text_field(wp_unslash($_POST["lead_monthly_sales"])) : "",
        "form_origin" => isset($_POST["lead_form_origin"]) ? sanitize_text_field(wp_unslash($_POST["lead_form_origin"])) : "contact-form",
        "source" => isset($_POST["lead_source"]) ? sanitize_text_field(wp_unslash($_POST["lead_source"])) : "",
        "utm_source" => isset($_POST["utm_source"]) ? sanitize_text_field(wp_unslash($_POST["utm_source"])) : "",
        "utm_medium" => isset($_POST["utm_medium"]) ? sanitize_text_field(wp_unslash($_POST["utm_medium"])) : "",
        "utm_campaign" => isset($_POST["utm_campaign"]) ? sanitize_text_field(wp_unslash($_POST["utm_campaign"])) : "",
        "utm_term" => isset($_POST["utm_term"]) ? sanitize_text_field(wp_unslash($_POST["utm_term"])) : "",
        "utm_content" => isset($_POST["utm_content"]) ? sanitize_text_field(wp_unslash($_POST["utm_content"])) : "",
        "gclid" => isset($_POST["gclid"]) ? sanitize_text_field(wp_unslash($_POST["gclid"])) : "",
        "fbclid" => isset($_POST["fbclid"]) ? sanitize_text_field(wp_unslash($_POST["fbclid"])) : "",
        "msclkid" => isset($_POST["msclkid"]) ? sanitize_text_field(wp_unslash($_POST["msclkid"])) : "",
        "event_id" => isset($_POST["lead_event_id"]) ? sanitize_text_field(wp_unslash($_POST["lead_event_id"])) : "",
        "landing_url" => isset($_POST["landing_url"]) ? esc_url_raw(wp_unslash($_POST["landing_url"])) : "",
        "referrer" => isset($_POST["referrer"]) ? esc_url_raw(wp_unslash($_POST["referrer"])) : "",
        "gsc_query" => upsellio_extract_search_query_from_referrer(
            isset($_POST["referrer"]) ? esc_url_raw(wp_unslash($_POST["referrer"])) : ""
        ),
        "quiz_problem" => isset($_POST["lead_quiz_problem"]) ? sanitize_text_field(wp_unslash($_POST["lead_quiz_problem"])) : "",
        "quiz_industry" => isset($_POST["lead_quiz_industry"]) ? sanitize_text_field(wp_unslash($_POST["lead_quiz_industry"])) : "",
        "quiz_budget" => isset($_POST["lead_quiz_budget"]) ? sanitize_text_field(wp_unslash($_POST["lead_quiz_budget"])) : "",
        "consent_timestamp" => $consentTimestamp,
        "consent_ip" => $consentIp,
        "consent_ua" => $consentUa,
        "consent_form" => $consentForm,
        "consent_version" => $consentVersion,
    ];

    $leadId = upsellio_crm_create_lead($payload);
    if ($leadId <= 0) {
        upsellio_crm_redirect_lead_form_error($redirectUrl, "save");
    }

    if (function_exists("upsellio_is_internal_tracking_user") && upsellio_is_internal_tracking_user()) {
        update_post_meta((int) $leadId, "_upsellio_lead_internal_tester", "1");
        $crm_synced = (int) get_post_meta((int) $leadId, "_upsellio_synced_crm_lead_id", true);
        if ($crm_synced > 0) {
            update_post_meta($crm_synced, "_upsellio_lead_internal_tester", "1");
        }
    }

    upsellio_crm_send_emails($leadId, $name, $email, $message);
    upsellio_crm_schedule_followup($leadId);
    if (function_exists("upsellio_server_send_lead_conversion")) {
        if (function_exists("upsellio_is_internal_tracking_user") && upsellio_is_internal_tracking_user()) {
            // Skip server-side conversion for internal test submissions.
        } else {
            upsellio_server_send_lead_conversion((int) $leadId, $payload);
        }
    }

    upsellio_crm_redirect_lead_form_success($redirectUrl);
}
add_action("admin_post_upsellio_submit_lead", "upsellio_crm_handle_lead_submission");
add_action("admin_post_nopriv_upsellio_submit_lead", "upsellio_crm_handle_lead_submission");

function upsellio_crm_refresh_lead_form_nonce_ajax()
{
    wp_send_json_success([
        "nonce" => wp_create_nonce("upsellio_unified_lead_form"),
    ]);
}
add_action("wp_ajax_upsellio_refresh_lead_form_nonce", "upsellio_crm_refresh_lead_form_nonce_ajax");
add_action("wp_ajax_nopriv_upsellio_refresh_lead_form_nonce", "upsellio_crm_refresh_lead_form_nonce_ajax");

function upsellio_crm_followup_reminder($lead_id)
{
    $lead_id = (int) $lead_id;
    if ($lead_id <= 0) {
        return;
    }

    $statusTerms = wp_get_object_terms($lead_id, "lead_status", ["fields" => "slugs"]);
    if (!is_array($statusTerms) || !in_array("new", $statusTerms, true)) {
        return;
    }

    $contactAt = get_post_meta($lead_id, "_upsellio_first_contact_at", true);
    if ($contactAt) {
        return;
    }

    $adminEmail = (string) get_option("admin_email");
    $lead_title = get_the_title($lead_id);
    $lead_email = (string) get_post_meta($lead_id, "_upsellio_lead_email", true);
    $score = (int) get_post_meta($lead_id, "_upsellio_lead_score", true);
    $company = (string) get_post_meta($lead_id, "_upsellio_lead_company", true);
    $urgency = $score >= 70 ? "🔥 HOT" : ($score >= 50 ? "☀️ WARM" : "🌑 COLD");
    $subject = "{$urgency} · 24h cisza: {$lead_title}";
    $body = "<p><strong>{$urgency} · " . esc_html($lead_title) . "</strong>" . ($company !== "" ? " (" . esc_html($company) . ")" : "") . "</p>
<p style='font-size:13px;color:#64748b;'>Score: {$score}/100 · Cisza: 24h</p>
<p>Lead nadal ma status NOWY i nie ma oznaczonego pierwszego kontaktu.</p>
<p><a href='" . esc_url(admin_url("post.php?post=" . $lead_id . "&action=edit")) . "' class='ups-cta'>Otwórz lead</a> &nbsp; <a href='mailto:" . esc_attr($lead_email) . "' style='color:#64748b'>Mailto: " . esc_html($lead_email) . "</a></p>";
    if (function_exists("upsellio_send_crm_mail")) {
        upsellio_send_crm_mail($adminEmail, $subject, $body, [
            "type" => "transactional",
            "preheader" => "Lead z score {$score} czeka 24h.",
            "allow_internal" => true,
        ]);
    }
    upsellio_crm_add_timeline_event($lead_id, "reminder", "Wysłano przypomnienie follow-up >24h.");
}
add_action("upsellio_crm_followup_reminder", "upsellio_crm_followup_reminder");

function upsellio_crm_task_due($task_id)
{
    $task_id = (int) $task_id;
    if ($task_id <= 0) {
        return;
    }
    $status = (string) get_post_meta($task_id, "_upsellio_task_status", true);
    if ($status !== "open") {
        return;
    }
    $leadId = (int) get_post_meta($task_id, "_upsellio_task_lead_id", true);
    if ($leadId <= 0) {
        return;
    }
    $contactAt = get_post_meta($leadId, "_upsellio_first_contact_at", true);
    if ($contactAt) {
        update_post_meta($task_id, "_upsellio_task_status", "done");
        return;
    }

    $ownerId = (int) get_post_field("post_author", $task_id);
    $ownerEmail = $ownerId > 0 ? get_the_author_meta("user_email", $ownerId) : "";
    $recipient = is_email($ownerEmail) ? $ownerEmail : get_option("admin_email");
    $subject = "CRM: zadanie follow-up jest wymagalne";
    $body = "Lead #{$leadId} wymaga kontaktu. Zadanie: " . get_the_title($task_id);
    wp_mail($recipient, $subject, $body);
    upsellio_crm_add_timeline_event($leadId, "task_due", "Zadanie follow-up jest wymagalne: " . get_the_title($task_id));
}
add_action("upsellio_crm_task_due", "upsellio_crm_task_due");

function upsellio_crm_add_meta_boxes()
{
    add_meta_box("upsellio_lead_details", "CRM: Szczegóły leada", "upsellio_crm_render_lead_meta_box", "lead", "normal", "high");
}
add_action("add_meta_boxes", "upsellio_crm_add_meta_boxes");

function upsellio_crm_render_lead_meta_box($post)
{
    wp_nonce_field("upsellio_lead_meta_action", "upsellio_lead_meta_nonce");
    $email = get_post_meta($post->ID, "_upsellio_lead_email", true);
    $phone = get_post_meta($post->ID, "_upsellio_lead_phone", true);
    $service = get_post_meta($post->ID, "_upsellio_lead_service", true);
    $budget = get_post_meta($post->ID, "_upsellio_lead_budget", true);
    $goal = get_post_meta($post->ID, "_upsellio_lead_goal", true);
    $monthlySales = get_post_meta($post->ID, "_upsellio_lead_monthly_sales", true);
    $score = get_post_meta($post->ID, "_upsellio_lead_score", true);
    $closeValue = get_post_meta($post->ID, "_upsellio_lead_close_value", true);
    $notes = get_post_meta($post->ID, "_upsellio_lead_notes", true);
    $utmSource = get_post_meta($post->ID, "_upsellio_lead_utm_source", true);
    $utmMedium = get_post_meta($post->ID, "_upsellio_lead_utm_medium", true);
    $utmCampaign = get_post_meta($post->ID, "_upsellio_lead_utm_campaign", true);
    $utmTerm = get_post_meta($post->ID, "_upsellio_lead_utm_term", true);
    $utmContent = get_post_meta($post->ID, "_upsellio_lead_utm_content", true);
    $gclid = get_post_meta($post->ID, "_upsellio_lead_gclid", true);
    $fbclid = get_post_meta($post->ID, "_upsellio_lead_fbclid", true);
    $msclkid = get_post_meta($post->ID, "_upsellio_lead_msclkid", true);
    $eventId = get_post_meta($post->ID, "_upsellio_lead_event_id", true);
    $landingUrl = get_post_meta($post->ID, "_upsellio_lead_landing_url", true);
    $referrer = get_post_meta($post->ID, "_upsellio_lead_referrer", true);
    $gscTop = get_post_meta($post->ID, "_upsellio_lead_gsc_top_queries", true);
    $firstContactAt = get_post_meta($post->ID, "_upsellio_first_contact_at", true);
    $ownerId = (int) get_post_field("post_author", $post->ID);
    $timeline = get_post_meta($post->ID, "_upsellio_lead_timeline", true);
    if (!is_array($timeline)) {
        $timeline = [];
    }
    ?>
    <p>
      <label><strong>E-mail</strong><br />
        <input type="email" name="upsellio_lead_email" value="<?php echo esc_attr((string) $email); ?>" class="widefat" />
      </label>
    </p>
    <p>
      <label><strong>Telefon</strong><br />
        <input type="text" name="upsellio_lead_phone" value="<?php echo esc_attr((string) $phone); ?>" class="widefat" />
      </label>
    </p>
    <p>
      <label><strong>Usługa</strong><br />
        <input type="text" name="upsellio_lead_service" value="<?php echo esc_attr((string) $service); ?>" class="widefat" />
      </label>
    </p>
    <p>
      <label><strong>Budżet</strong><br />
        <input type="text" name="upsellio_lead_budget" value="<?php echo esc_attr((string) $budget); ?>" class="widefat" />
      </label>
    </p>
    <p>
      <label><strong>Cel</strong><br />
        <input type="text" name="upsellio_lead_goal" value="<?php echo esc_attr((string) $goal); ?>" class="widefat" />
      </label>
    </p>
    <p>
      <label><strong>Miesięczna sprzedaż</strong><br />
        <input type="text" name="upsellio_lead_monthly_sales" value="<?php echo esc_attr((string) $monthlySales); ?>" class="widefat" />
      </label>
    </p>
    <p>
      <label><strong>Score</strong><br />
        <input type="number" min="0" max="100" name="upsellio_lead_score" value="<?php echo esc_attr((string) $score); ?>" class="small-text" />
      </label>
    </p>
    <p>
      <label><strong>Wartosc zamkniecia (PLN)</strong><br />
        <input type="number" min="0" step="0.01" name="upsellio_lead_close_value" value="<?php echo esc_attr((string) $closeValue); ?>" class="small-text" />
      </label>
    </p>
    <p>
      <label><strong>Opiekun leada</strong><br />
        <?php
        wp_dropdown_users([
            "name" => "upsellio_lead_owner_id",
            "selected" => $ownerId,
            "show_option_none" => "— wybierz opiekuna —",
            "option_none_value" => "0",
        ]);
        ?>
      </label>
    </p>
    <p>
      <label><strong>Notatki handlowe</strong><br />
        <textarea name="upsellio_lead_notes" rows="5" class="widefat"><?php echo esc_textarea((string) $notes); ?></textarea>
      </label>
    </p>
    <p>
      <label>
        <input type="checkbox" name="upsellio_mark_contacted" value="1" />
        Oznacz pierwszy kontakt i ustaw status na "Skontaktowany"
      </label>
      <?php if ($firstContactAt) : ?>
        <br /><small>Pierwszy kontakt: <?php echo esc_html((string) $firstContactAt); ?></small>
      <?php endif; ?>
    </p>
    <hr />
    <p><strong>Atrybucja</strong></p>
    <p>UTM source: <?php echo esc_html((string) $utmSource); ?> | medium: <?php echo esc_html((string) $utmMedium); ?> | campaign: <?php echo esc_html((string) $utmCampaign); ?></p>
    <p>UTM term: <?php echo esc_html((string) $utmTerm); ?> | content: <?php echo esc_html((string) $utmContent); ?></p>
    <p>gclid: <?php echo esc_html((string) $gclid); ?> | fbclid: <?php echo esc_html((string) $fbclid); ?> | msclkid: <?php echo esc_html((string) $msclkid); ?></p>
    <p>event_id: <?php echo esc_html((string) $eventId); ?></p>
    <p>Landing: <?php echo esc_html((string) $landingUrl); ?></p>
    <p>Referrer: <?php echo esc_html((string) $referrer); ?></p>
    <?php if (is_array($gscTop) && !empty($gscTop)) : ?>
      <p><strong>GSC — możliwe zapytanie wejściowe:</strong></p>
      <ul>
        <?php foreach ($gscTop as $candidate) : ?>
          <li>
            <?php
            printf(
                "%s - %d klikniec, pozycja %.1f",
                esc_html((string) ($candidate["query"] ?? "")),
                (int) ($candidate["clicks"] ?? 0),
                (float) ($candidate["position"] ?? 0)
            );
            ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <hr />
    <p><strong>Timeline</strong></p>
    <?php if (empty($timeline)) : ?>
      <p><em>Brak wpisów na osi czasu.</em></p>
    <?php else : ?>
      <ul>
        <?php foreach (array_reverse($timeline) as $event) : ?>
          <li><?php echo esc_html((string) ($event["timestamp"] ?? "")); ?> - <?php echo esc_html((string) ($event["message"] ?? "")); ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <?php
}

function upsellio_crm_save_lead_meta($post_id)
{
    if (!isset($_POST["upsellio_lead_meta_nonce"]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST["upsellio_lead_meta_nonce"])), "upsellio_lead_meta_action")) {
        return;
    }
    if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can("edit_post", $post_id)) {
        return;
    }

    update_post_meta($post_id, "_upsellio_lead_email", sanitize_email(wp_unslash($_POST["upsellio_lead_email"] ?? "")));
    update_post_meta($post_id, "_upsellio_lead_phone", sanitize_text_field(wp_unslash($_POST["upsellio_lead_phone"] ?? "")));
    update_post_meta($post_id, "_upsellio_lead_service", sanitize_text_field(wp_unslash($_POST["upsellio_lead_service"] ?? "")));
    update_post_meta($post_id, "_upsellio_lead_budget", sanitize_text_field(wp_unslash($_POST["upsellio_lead_budget"] ?? "")));
    update_post_meta($post_id, "_upsellio_lead_goal", sanitize_text_field(wp_unslash($_POST["upsellio_lead_goal"] ?? "")));
    update_post_meta($post_id, "_upsellio_lead_monthly_sales", sanitize_text_field(wp_unslash($_POST["upsellio_lead_monthly_sales"] ?? "")));
    update_post_meta($post_id, "_upsellio_lead_score", (int) ($_POST["upsellio_lead_score"] ?? 0));
    update_post_meta($post_id, "_upsellio_lead_close_value", (float) ($_POST["upsellio_lead_close_value"] ?? 0));
    update_post_meta($post_id, "_upsellio_lead_notes", sanitize_textarea_field(wp_unslash($_POST["upsellio_lead_notes"] ?? "")));
    $ownerId = isset($_POST["upsellio_lead_owner_id"]) ? (int) $_POST["upsellio_lead_owner_id"] : 0;
    if ($ownerId > 0) {
        wp_update_post([
            "ID" => (int) $post_id,
            "post_author" => $ownerId,
        ]);
        $openTasks = upsellio_crm_get_open_tasks_for_lead($post_id);
        foreach ($openTasks as $task) {
            wp_update_post([
                "ID" => (int) $task->ID,
                "post_author" => $ownerId,
            ]);
        }
    }

    if (isset($_POST["upsellio_mark_contacted"]) && sanitize_text_field(wp_unslash($_POST["upsellio_mark_contacted"])) === "1") {
        update_post_meta($post_id, "_upsellio_first_contact_at", current_time("mysql"));
        $contactedTermId = upsellio_crm_get_term_id_by_slug("lead_status", "contacted");
        if ($contactedTermId > 0) {
            wp_set_object_terms($post_id, [$contactedTermId], "lead_status", false);
        }
        upsellio_crm_add_timeline_event($post_id, "contacted", "Lead oznaczony jako skontaktowany.");
        upsellio_crm_mark_lead_tasks_done($post_id, "Automatycznie zamknięto zadania follow-up po kontakcie.");
    }
}
add_action("save_post_lead", "upsellio_crm_save_lead_meta");

add_action("set_object_terms", function ($object_id, $terms, $tt_ids, $taxonomy) {
    if ($taxonomy !== "lead_status") {
        return;
    }
    if (get_post_type((int) $object_id) !== "lead") {
        return;
    }

    $hasWon = false;
    foreach ((array) $terms as $termValue) {
        $term = is_numeric($termValue)
            ? get_term((int) $termValue, "lead_status")
            : get_term_by("slug", (string) $termValue, "lead_status");
        if ($term && !is_wp_error($term) && ((string) $term->slug) === "won") {
            $hasWon = true;
            break;
        }
    }

    if ($hasWon && function_exists("upsellio_send_offline_conversion")) {
        upsellio_send_offline_conversion((int) $object_id);
    }
    if ($hasWon && function_exists("upsellio_server_send_closed_won_conversion")) {
        upsellio_server_send_closed_won_conversion((int) $object_id);
    }
}, 10, 4);

function upsellio_crm_admin_columns($columns)
{
    $newColumns = [];
    foreach ($columns as $key => $label) {
        $newColumns[$key] = $label;
        if ($key === "title") {
            $newColumns["lead_email"] = "E-mail";
            $newColumns["lead_phone"] = "Telefon";
            $newColumns["lead_service"] = "Usługa";
        }
    }
    return $newColumns;
}
add_filter("manage_lead_posts_columns", "upsellio_crm_admin_columns");

function upsellio_crm_admin_column_content($column, $post_id)
{
    if ($column === "lead_email") {
        echo esc_html((string) get_post_meta($post_id, "_upsellio_lead_email", true));
    }
    if ($column === "lead_phone") {
        echo esc_html((string) get_post_meta($post_id, "_upsellio_lead_phone", true));
    }
    if ($column === "lead_service") {
        echo esc_html((string) get_post_meta($post_id, "_upsellio_lead_service", true));
    }
}
add_action("manage_lead_posts_custom_column", "upsellio_crm_admin_column_content", 10, 2);

function upsellio_crm_add_admin_filters($post_type)
{
    if ($post_type !== "lead") {
        return;
    }

    $selectedStatus = isset($_GET["lead_status_filter"]) ? sanitize_text_field(wp_unslash($_GET["lead_status_filter"])) : "";
    $selectedSource = isset($_GET["lead_source_filter"]) ? sanitize_text_field(wp_unslash($_GET["lead_source_filter"])) : "";
    $statusTerms = get_terms(["taxonomy" => "lead_status", "hide_empty" => false]);
    $sourceTerms = get_terms(["taxonomy" => "lead_source", "hide_empty" => false]);
    ?>
    <select name="lead_status_filter">
      <option value="">Wszystkie statusy</option>
      <?php foreach ($statusTerms as $term) : ?>
        <option value="<?php echo esc_attr($term->slug); ?>" <?php selected($selectedStatus, $term->slug); ?>><?php echo esc_html($term->name); ?></option>
      <?php endforeach; ?>
    </select>
    <select name="lead_source_filter">
      <option value="">Wszystkie źródła</option>
      <?php foreach ($sourceTerms as $term) : ?>
        <option value="<?php echo esc_attr($term->slug); ?>" <?php selected($selectedSource, $term->slug); ?>><?php echo esc_html($term->name); ?></option>
      <?php endforeach; ?>
    </select>
    <?php
}
add_action("restrict_manage_posts", "upsellio_crm_add_admin_filters");

function upsellio_crm_apply_admin_filters($query)
{
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }
    if (($query->get("post_type") ?? "") !== "lead") {
        return;
    }

    $taxQuery = [];
    $statusFilter = isset($_GET["lead_status_filter"]) ? sanitize_text_field(wp_unslash($_GET["lead_status_filter"])) : "";
    $sourceFilter = isset($_GET["lead_source_filter"]) ? sanitize_text_field(wp_unslash($_GET["lead_source_filter"])) : "";

    if ($statusFilter !== "") {
        $taxQuery[] = [
            "taxonomy" => "lead_status",
            "field" => "slug",
            "terms" => [$statusFilter],
        ];
    }
    if ($sourceFilter !== "") {
        $taxQuery[] = [
            "taxonomy" => "lead_source",
            "field" => "slug",
            "terms" => [$sourceFilter],
        ];
    }

    if (!empty($taxQuery)) {
        if (count($taxQuery) > 1) {
            $taxQuery["relation"] = "AND";
        }
        $query->set("tax_query", $taxQuery);
    }
}
add_action("pre_get_posts", "upsellio_crm_apply_admin_filters");

function upsellio_crm_get_admin_tabs()
{
    return [
        "upsellio-crm-pipeline" => ["label" => "Pipeline", "url" => menu_page_url("upsellio-crm-pipeline", false)],
        "upsellio-crm-sla" => ["label" => "SLA Dashboard", "url" => menu_page_url("upsellio-crm-sla", false)],
        "upsellio-crm-tasks" => ["label" => "Zadania Follow-up", "url" => menu_page_url("upsellio-crm-tasks", false)],
        "upsellio-crm-reports" => ["label" => "Raporty", "url" => menu_page_url("upsellio-crm-reports", false)],
    ];
}

function upsellio_crm_render_admin_shell_start($title, $subtitle, $active_tab)
{
    $tabs = upsellio_crm_get_admin_tabs();
    ?>
    <div class="wrap ups-crm-wrap">
      <style>
        .ups-crm-wrap{margin-top:16px}
        .ups-crm-shell{max-width:1360px}
        .ups-crm-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;margin-bottom:14px}
        .ups-crm-title{margin:0;font-size:28px;line-height:1.2}
        .ups-crm-sub{margin:6px 0 0;color:#5f6368;font-size:14px}
        .ups-crm-tabs{display:flex;gap:8px;flex-wrap:wrap;margin:14px 0 18px}
        .ups-crm-tab{display:inline-flex;align-items:center;border:1px solid #d9dde3;background:#fff;border-radius:999px;padding:8px 14px;text-decoration:none;color:#1d2327;font-weight:600}
        .ups-crm-tab.active{background:#0d9488;color:#fff;border-color:#0d9488}
        .ups-crm-card{background:#fff;border:1px solid #d9dde3;border-radius:14px;padding:14px;box-shadow:0 1px 2px rgba(0,0,0,.04)}
        .ups-crm-kpi-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
        .ups-crm-kpi-value{font-size:26px;font-weight:700;line-height:1;margin-top:6px}
        .ups-crm-kpi-label{font-size:12px;color:#5f6368;text-transform:uppercase;letter-spacing:.04em}
        .ups-crm-table{width:100%;border-collapse:separate;border-spacing:0;overflow:hidden}
        .ups-crm-table th{background:#f6f8fa;font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:#5f6368}
        .ups-crm-table th,.ups-crm-table td{padding:11px 12px;border-bottom:1px solid #eceff3;text-align:left;vertical-align:middle}
        .ups-crm-table tr:last-child td{border-bottom:none}
        .ups-badge{display:inline-flex;align-items:center;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:700;border:1px solid transparent}
        .ups-badge--red{background:#fff1f1;color:#b42318;border-color:#f8d4d4}
        .ups-badge--orange{background:#fff7ed;color:#b45309;border-color:#fed7aa}
        .ups-badge--green{background:#ecfdf3;color:#027a48;border-color:#b7ebcf}
        .ups-badge--gray{background:#f5f7fa;color:#475467;border-color:#d9dde3}
        .ups-kanban-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;align-items:start}
        .ups-kanban-col{background:#fff;border:1px solid #d9dde3;border-radius:14px;padding:10px}
        .ups-kanban-col-head{display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:8px}
        .ups-kanban-col-title{margin:0;font-size:14px;font-weight:700}
        .ups-kanban-count{font-size:12px;color:#5f6368}
        .ups-kanban-drop{display:grid;gap:10px;min-height:120px}
        .ups-kanban-col.is-over{outline:2px dashed #0d9488;outline-offset:2px}
        .ups-kanban-card{border:1px solid #e8ebef;border-radius:12px;padding:10px;background:#fafbfc;cursor:grab;transition:all .12s ease}
        .ups-kanban-card:hover{border-color:#c7ced8;background:#fff}
        .ups-kanban-card:active{cursor:grabbing}
        .ups-kanban-name{font-size:14px;font-weight:700;margin-bottom:4px}
        .ups-kanban-meta{font-size:12px;color:#5f6368;line-height:1.5}
        .ups-link-btn{display:inline-flex;align-items:center;border:1px solid #d9dde3;background:#fff;border-radius:8px;padding:6px 10px;font-size:12px;font-weight:600;text-decoration:none}
        @media(max-width:1100px){.ups-crm-kpi-grid,.ups-kanban-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media(max-width:760px){.ups-crm-kpi-grid,.ups-kanban-grid{grid-template-columns:1fr}}
      </style>
      <div class="ups-crm-shell">
        <div class="ups-crm-head">
          <div>
            <h1 class="ups-crm-title"><?php echo esc_html($title); ?></h1>
            <p class="ups-crm-sub"><?php echo esc_html($subtitle); ?></p>
          </div>
        </div>
        <nav class="ups-crm-tabs" aria-label="Nawigacja CRM">
          <?php foreach ($tabs as $tab_key => $tab_data) : ?>
            <a class="ups-crm-tab <?php echo $tab_key === $active_tab ? "active" : ""; ?>" href="<?php echo esc_url($tab_data["url"]); ?>">
              <?php echo esc_html($tab_data["label"]); ?>
            </a>
          <?php endforeach; ?>
        </nav>
    <?php
}

function upsellio_crm_render_admin_shell_end()
{
    echo "</div></div>";
}

function upsellio_crm_get_status_label($slug)
{
    $statuses = upsellio_crm_get_default_statuses();
    return $statuses[$slug] ?? $slug;
}

function upsellio_crm_get_sla_badge_class($hours_open)
{
    $hours_open = (float) $hours_open;
    if ($hours_open >= 24) {
        return "ups-badge ups-badge--red";
    }
    if ($hours_open >= 4) {
        return "ups-badge ups-badge--orange";
    }
    return "ups-badge ups-badge--green";
}

function upsellio_crm_render_pipeline_page()
{
    if (!current_user_can("edit_posts")) {
        return;
    }
    $statuses = upsellio_crm_get_default_statuses();
    $nonce = wp_create_nonce("upsellio_crm_move_lead");
    $status_counts = [];
    ?>
    <?php upsellio_crm_render_admin_shell_start("Pipeline CRM", "Przeciągnij kartę leada do odpowiedniej kolumny statusu.", "upsellio-crm-pipeline"); ?>
      <div class="ups-kanban-grid">
        <?php foreach ($statuses as $slug => $label) : ?>
          <?php
          $query = new WP_Query([
              "post_type" => "lead",
              "post_status" => "publish",
              "posts_per_page" => 20,
              "tax_query" => [[
                  "taxonomy" => "lead_status",
                  "field" => "slug",
                  "terms" => [$slug],
              ]],
          ]);
          $status_counts[$slug] = (int) $query->found_posts;
          ?>
          <section class="ups-kanban-col" data-status="<?php echo esc_attr($slug); ?>">
            <div class="ups-kanban-col-head">
              <h2 class="ups-kanban-col-title"><?php echo esc_html($label); ?></h2>
              <span class="ups-kanban-count"><?php echo esc_html((string) $query->found_posts); ?></span>
            </div>
            <?php if ($query->have_posts()) : ?>
              <div class="ups-kanban-drop">
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                  <?php
                  $lead_id = get_the_ID();
                  $owner_name = get_the_author_meta("display_name", (int) get_post_field("post_author", $lead_id));
                  ?>
                  <article class="ups-kanban-card" draggable="true" data-lead-id="<?php echo esc_attr((string) $lead_id); ?>">
                    <div class="ups-kanban-name"><?php echo esc_html(get_the_title()); ?></div>
                    <div class="ups-kanban-meta">
                      <?php echo esc_html((string) get_post_meta($lead_id, "_upsellio_lead_email", true)); ?><br />
                      Opiekun: <?php echo esc_html((string) $owner_name); ?>
                    </div>
                    <div style="margin-top:8px;">
                      <a class="ups-link-btn" href="<?php echo esc_url(get_edit_post_link($lead_id)); ?>">Otwórz kartę</a>
                    </div>
                  </article>
                <?php endwhile; ?>
              </div>
            <?php else : ?>
              <p><em>Brak leadów.</em></p>
            <?php endif; ?>
          </section>
          <?php wp_reset_postdata(); ?>
        <?php endforeach; ?>
      </div>
      <div style="margin-top:14px" class="ups-crm-kpi-grid">
        <?php foreach ($statuses as $slug => $label) : ?>
          <div class="ups-crm-card">
            <div class="ups-crm-kpi-label"><?php echo esc_html($label); ?></div>
            <div class="ups-crm-kpi-value"><?php echo esc_html((string) ($status_counts[$slug] ?? 0)); ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php upsellio_crm_render_admin_shell_end(); ?>
    <script>
      (function () {
        const nonce = <?php echo wp_json_encode($nonce); ?>;
        const ajaxUrl = <?php echo wp_json_encode(admin_url("admin-ajax.php")); ?>;
        let draggedId = "";
        let draggedElement = null;

        document.querySelectorAll(".ups-kanban-card").forEach((card) => {
          card.addEventListener("dragstart", () => {
            draggedId = card.dataset.leadId || "";
            draggedElement = card;
            card.style.opacity = "0.5";
          });
          card.addEventListener("dragend", () => {
            card.style.opacity = "1";
          });
        });

        document.querySelectorAll(".ups-kanban-col").forEach((col) => {
          col.addEventListener("dragover", (event) => {
            event.preventDefault();
          });
          col.addEventListener("dragenter", () => {
            col.classList.add("is-over");
          });
          col.addEventListener("dragleave", () => {
            col.classList.remove("is-over");
          });

          col.addEventListener("drop", async (event) => {
            event.preventDefault();
            col.classList.remove("is-over");
            if (!draggedId) return;
            const status = col.dataset.status || "";
            if (!status) return;

            const payload = new URLSearchParams();
            payload.append("action", "upsellio_crm_move_lead_status");
            payload.append("nonce", nonce);
            payload.append("lead_id", draggedId);
            payload.append("status", status);

            const response = await fetch(ajaxUrl, {
              method: "POST",
              headers: { "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8" },
              body: payload.toString(),
              credentials: "same-origin",
            });
            const result = await response.json();
            if (result?.success) {
              const dropContainer = col.querySelector(".ups-kanban-drop");
              if (dropContainer && draggedElement) {
                dropContainer.prepend(draggedElement);
              }
              window.location.reload();
            } else {
              alert(result?.data?.message || "Nie udało się zaktualizować statusu.");
            }
          });
        });
      })();
    </script>
    <?php
}

function upsellio_crm_move_lead_status_ajax()
{
    check_ajax_referer("upsellio_crm_move_lead", "nonce");
    if (!current_user_can("edit_posts")) {
        wp_send_json_error(["message" => "Brak uprawnień."], 403);
    }

    $leadId = isset($_POST["lead_id"]) ? (int) $_POST["lead_id"] : 0;
    $status = isset($_POST["status"]) ? sanitize_title(wp_unslash($_POST["status"])) : "";
    if ($leadId <= 0 || $status === "") {
        wp_send_json_error(["message" => "Nieprawidłowe dane."], 400);
    }

    $termId = upsellio_crm_get_term_id_by_slug("lead_status", $status);
    if ($termId <= 0) {
        wp_send_json_error(["message" => "Nie znaleziono statusu."], 400);
    }

    wp_set_object_terms($leadId, [$termId], "lead_status", false);
    upsellio_crm_add_timeline_event($leadId, "status_change", "Zmieniono status na: " . $status);

    if (in_array($status, ["contacted", "qualified", "proposal", "won", "lost"], true)) {
        if (!get_post_meta($leadId, "_upsellio_first_contact_at", true)) {
            update_post_meta($leadId, "_upsellio_first_contact_at", current_time("mysql"));
        }
        upsellio_crm_mark_lead_tasks_done($leadId, "Zamknięto zadania follow-up po zmianie statusu.");
    }

    wp_send_json_success(["message" => "Status zaktualizowany."]);
}
add_action("wp_ajax_upsellio_crm_move_lead_status", "upsellio_crm_move_lead_status_ajax");

function upsellio_crm_render_sla_page()
{
    if (!current_user_can("edit_posts")) {
        return;
    }
    $query = new WP_Query([
        "post_type" => "lead",
        "post_status" => "publish",
        "posts_per_page" => 300,
    ]);
    $rows = [];
    $count4h = 0;
    $count24h = 0;

    while ($query->have_posts()) {
        $query->the_post();
        $leadId = get_the_ID();
        $firstContact = get_post_meta($leadId, "_upsellio_first_contact_at", true);
        if ($firstContact) {
            continue;
        }
        $statusTerms = wp_get_object_terms($leadId, "lead_status", ["fields" => "slugs"]);
        if (is_array($statusTerms) && (in_array("won", $statusTerms, true) || in_array("lost", $statusTerms, true))) {
            continue;
        }
        $createdTs = get_post_time("U", true, $leadId);
        $hoursOpen = max(0, round((time() - $createdTs) / HOUR_IN_SECONDS, 1));
        if ($hoursOpen >= 4) {
            $count4h++;
        }
        if ($hoursOpen >= 24) {
            $count24h++;
        }
        $rows[] = [
            "id" => $leadId,
            "title" => get_the_title($leadId),
            "owner" => get_the_author_meta("display_name", (int) get_post_field("post_author", $leadId)),
            "hours" => $hoursOpen,
            "status" => !empty($statusTerms) ? (string) $statusTerms[0] : "new",
        ];
    }
    wp_reset_postdata();
    ?>
    <?php upsellio_crm_render_admin_shell_start("SLA Dashboard", "Leady bez pierwszego kontaktu i priorytety reakcji.", "upsellio-crm-sla"); ?>
      <div class="ups-crm-kpi-grid" style="margin-bottom:14px;">
        <div class="ups-crm-card"><div class="ups-crm-kpi-label">Leady >4h bez kontaktu</div><div class="ups-crm-kpi-value"><?php echo esc_html((string) $count4h); ?></div></div>
        <div class="ups-crm-card"><div class="ups-crm-kpi-label">Leady >24h bez kontaktu</div><div class="ups-crm-kpi-value"><?php echo esc_html((string) $count24h); ?></div></div>
        <div class="ups-crm-card"><div class="ups-crm-kpi-label">Wszystkie do kontaktu</div><div class="ups-crm-kpi-value"><?php echo esc_html((string) count($rows)); ?></div></div>
        <div class="ups-crm-card"><div class="ups-crm-kpi-label">SLA compliance</div><div class="ups-crm-kpi-value"><?php echo esc_html((string) (count($rows) > 0 ? round(((count($rows) - $count4h) / count($rows)) * 100) : 100)); ?>%</div></div>
      </div>
      <div class="ups-crm-card">
      <table class="ups-crm-table">
        <thead><tr><th>Lead</th><th>Opiekun</th><th>Status</th><th>Godziny bez kontaktu</th><th>Priorytet</th><th>Akcja</th></tr></thead>
        <tbody>
          <?php if (empty($rows)) : ?>
            <tr><td colspan="6"><em>Brak zaległych leadów bez kontaktu.</em></td></tr>
          <?php else : ?>
            <?php foreach ($rows as $row) : ?>
              <tr>
                <td><?php echo esc_html((string) $row["title"]); ?></td>
                <td><?php echo esc_html((string) $row["owner"]); ?></td>
                <td><?php echo esc_html((string) upsellio_crm_get_status_label((string) $row["status"])); ?></td>
                <td><?php echo esc_html((string) $row["hours"]); ?>h</td>
                <td><span class="<?php echo esc_attr(upsellio_crm_get_sla_badge_class((float) $row["hours"])); ?>"><?php echo (float) $row["hours"] >= 24 ? "Krytyczny" : ((float) $row["hours"] >= 4 ? "Wysoki" : "OK"); ?></span></td>
                <td><a class="ups-link-btn" href="<?php echo esc_url(get_edit_post_link((int) $row["id"])); ?>">Otwórz</a></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
      </div>
    <?php upsellio_crm_render_admin_shell_end(); ?>
    <?php
}

function upsellio_crm_render_tasks_page()
{
    if (!current_user_can("edit_posts")) {
        return;
    }
    $currentUserId = get_current_user_id();
    $isAdmin = current_user_can("manage_options");

    if (isset($_GET["complete_task"]) && isset($_GET["_wpnonce"]) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET["_wpnonce"])), "upsellio_complete_task")) {
        $taskId = (int) $_GET["complete_task"];
        if ($taskId > 0) {
            $taskOwner = (int) get_post_field("post_author", $taskId);
            if ($isAdmin || $taskOwner === $currentUserId) {
                update_post_meta($taskId, "_upsellio_task_status", "done");
                $leadId = (int) get_post_meta($taskId, "_upsellio_task_lead_id", true);
                if ($leadId > 0) {
                    upsellio_crm_add_timeline_event($leadId, "task_done", "Zadanie follow-up oznaczone jako wykonane.");
                }
            }
        }
    }

    $queryArgs = [
        "post_type" => "lead_task",
        "post_status" => "publish",
        "posts_per_page" => 200,
        "meta_key" => "_upsellio_task_status",
        "meta_value" => "open",
        "orderby" => "date",
        "order" => "ASC",
    ];
    if (!$isAdmin) {
        $queryArgs["author"] = $currentUserId;
    }

    $taskQuery = new WP_Query($queryArgs);
    $task_count = (int) $taskQuery->found_posts;
    ?>
    <?php upsellio_crm_render_admin_shell_start("Zadania Follow-up", "Lista otwartych zadań dla opiekunów leadów.", "upsellio-crm-tasks"); ?>
      <div class="ups-crm-kpi-grid" style="margin-bottom:14px;">
        <div class="ups-crm-card"><div class="ups-crm-kpi-label">Otwarte zadania</div><div class="ups-crm-kpi-value"><?php echo esc_html((string) $task_count); ?></div></div>
        <div class="ups-crm-card"><div class="ups-crm-kpi-label">Widok</div><div class="ups-crm-kpi-value" style="font-size:18px;"><?php echo $isAdmin ? "Administrator" : "Mój zakres"; ?></div></div>
      </div>
      <div class="ups-crm-card">
      <table class="ups-crm-table">
        <thead><tr><th>Zadanie</th><th>Lead</th><th>Opiekun</th><th>Termin</th><th>Status SLA</th><th>Akcja</th></tr></thead>
        <tbody>
          <?php if (!$taskQuery->have_posts()) : ?>
            <tr><td colspan="6"><em>Brak otwartych zadań.</em></td></tr>
          <?php else : ?>
            <?php while ($taskQuery->have_posts()) : $taskQuery->the_post(); ?>
              <?php
              $taskId = get_the_ID();
              $leadId = (int) get_post_meta($taskId, "_upsellio_task_lead_id", true);
              $dueAt = (int) get_post_meta($taskId, "_upsellio_task_due_at", true);
              $ownerName = get_the_author_meta("display_name", (int) get_post_field("post_author", $taskId));
              $isOverdue = $dueAt > 0 && $dueAt < time();
              ?>
              <tr>
                <td><?php echo esc_html(get_the_title($taskId)); ?></td>
                <td><?php echo $leadId > 0 ? '<a href="' . esc_url(get_edit_post_link($leadId)) . '">Lead #' . esc_html((string) $leadId) . '</a>' : "—"; ?></td>
                <td><?php echo esc_html((string) $ownerName); ?></td>
                <td><?php echo $dueAt > 0 ? esc_html(wp_date("Y-m-d H:i", $dueAt)) : "—"; ?></td>
                <td><span class="<?php echo esc_attr($isOverdue ? "ups-badge ups-badge--red" : "ups-badge ups-badge--green"); ?>"><?php echo $isOverdue ? "Po terminie" : "W czasie"; ?></span></td>
                <td><a class="ups-link-btn" href="<?php echo esc_url(wp_nonce_url(add_query_arg("complete_task", (string) $taskId, menu_page_url("upsellio-crm-tasks", false)), "upsellio_complete_task")); ?>">Oznacz jako zrobione</a></td>
              </tr>
            <?php endwhile; wp_reset_postdata(); ?>
          <?php endif; ?>
        </tbody>
      </table>
      </div>
    <?php upsellio_crm_render_admin_shell_end(); ?>
    <?php
}

function upsellio_crm_render_reports_page()
{
    if (!current_user_can("edit_posts")) {
        return;
    }
    $statuses = upsellio_crm_get_default_statuses();
    $sourceTerms = get_terms(["taxonomy" => "lead_source", "hide_empty" => false]);
    $total_leads = 0;
    $total_won = 0;
    ?>
    <?php upsellio_crm_render_admin_shell_start("Raporty CRM", "Efektywność źródeł leadów na etapach pipeline.", "upsellio-crm-reports"); ?>
      <div class="ups-crm-card">
      <table class="ups-crm-table">
        <thead>
          <tr>
            <th>Źródło</th>
            <th>Leady łącznie</th>
            <?php foreach ($statuses as $statusSlug => $statusLabel) : ?>
              <th><?php echo esc_html($statusLabel); ?></th>
            <?php endforeach; ?>
            <th>Win rate</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($sourceTerms as $sourceTerm) : ?>
            <?php
            $total = (int) (new WP_Query([
                "post_type" => "lead",
                "post_status" => "publish",
                "posts_per_page" => 1,
                "fields" => "ids",
                "tax_query" => [[
                    "taxonomy" => "lead_source",
                    "field" => "term_id",
                    "terms" => [(int) $sourceTerm->term_id],
                ]],
            ]))->found_posts;

            $statusCounts = [];
            foreach (array_keys($statuses) as $statusSlug) {
                $statusCounts[$statusSlug] = (int) (new WP_Query([
                    "post_type" => "lead",
                    "post_status" => "publish",
                    "posts_per_page" => 1,
                    "fields" => "ids",
                    "tax_query" => [
                        "relation" => "AND",
                        [
                            "taxonomy" => "lead_source",
                            "field" => "term_id",
                            "terms" => [(int) $sourceTerm->term_id],
                        ],
                        [
                            "taxonomy" => "lead_status",
                            "field" => "slug",
                            "terms" => [$statusSlug],
                        ],
                    ],
                ]))->found_posts;
            }
            $won = $statusCounts["won"] ?? 0;
            $winRate = $total > 0 ? round(($won / $total) * 100, 1) . "%" : "0%";
            $total_leads += $total;
            $total_won += $won;
            ?>
            <tr>
              <td><?php echo esc_html((string) $sourceTerm->name); ?></td>
              <td><?php echo esc_html((string) $total); ?></td>
              <?php foreach (array_keys($statuses) as $statusSlug) : ?>
                <td><?php echo esc_html((string) $statusCounts[$statusSlug]); ?></td>
              <?php endforeach; ?>
              <td><?php echo esc_html($winRate); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      </div>
      <div class="ups-crm-kpi-grid" style="margin-top:14px;">
        <div class="ups-crm-card"><div class="ups-crm-kpi-label">Leady łącznie</div><div class="ups-crm-kpi-value"><?php echo esc_html((string) $total_leads); ?></div></div>
        <div class="ups-crm-card"><div class="ups-crm-kpi-label">Wygrane leady</div><div class="ups-crm-kpi-value"><?php echo esc_html((string) $total_won); ?></div></div>
        <div class="ups-crm-card"><div class="ups-crm-kpi-label">Globalny win rate</div><div class="ups-crm-kpi-value"><?php echo esc_html((string) ($total_leads > 0 ? round(($total_won / $total_leads) * 100, 1) : 0)); ?>%</div></div>
      </div>
    <?php upsellio_crm_render_admin_shell_end(); ?>
    <?php
}

function upsellio_crm_track_contact_click()
{
    check_ajax_referer("upsellio_contact_click", "nonce");

    if (function_exists("upsellio_is_internal_tracking_user") && upsellio_is_internal_tracking_user()) {
        wp_send_json_success(["skipped_internal" => true]);
    }

    $type = isset($_POST["contact_type"]) ? sanitize_text_field(wp_unslash($_POST["contact_type"])) : "";
    $target = isset($_POST["target"]) ? sanitize_text_field(wp_unslash($_POST["target"])) : "";
    if ($type === "" || $target === "") {
        wp_send_json_error(["message" => "Missing data"], 400);
    }

    $source = $type === "tel" ? "tel-click" : "mailto-click";
    $leadId = upsellio_crm_create_lead([
        "name" => "Klik kontaktu: " . $type,
        "email" => $type === "mailto" ? str_replace("mailto:", "", $target) : "",
        "phone" => $type === "tel" ? str_replace("tel:", "", $target) : "",
        "message" => "Użytkownik kliknął przycisk kontaktu: {$target}",
        "form_origin" => "contact-click",
        "source" => $source,
        "landing_url" => isset($_POST["landing_url"]) ? esc_url_raw(wp_unslash($_POST["landing_url"])) : "",
        "referrer" => isset($_POST["referrer"]) ? esc_url_raw(wp_unslash($_POST["referrer"])) : "",
    ]);

    if ($leadId > 0) {
        wp_send_json_success(["lead_id" => $leadId]);
    }

    wp_send_json_error(["message" => "Create failed"], 500);
}
add_action("wp_ajax_upsellio_track_contact_click", "upsellio_crm_track_contact_click");
add_action("wp_ajax_nopriv_upsellio_track_contact_click", "upsellio_crm_track_contact_click");

function upsellio_crm_touch_last_changed(): void
{
    update_option("upsellio_crm_last_changed", wp_date("c"), false);
}
add_action("save_post_lead", "upsellio_crm_touch_last_changed", 20);
add_action("set_object_terms", static function ($object_id, $terms, $tt_ids, $taxonomy) {
    if (get_post_type((int) $object_id) !== "lead") {
        return;
    }
    if (in_array((string) $taxonomy, ["lead_status", "lead_source"], true)) {
        upsellio_crm_touch_last_changed();
    }
}, 20, 4);
