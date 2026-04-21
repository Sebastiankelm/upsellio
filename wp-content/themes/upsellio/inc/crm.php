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
        "show_in_menu" => true,
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
    $landingUrl = esc_url_raw($payload["landing_url"] ?? "");
    $referrer = esc_url_raw($payload["referrer"] ?? "");
    $ownerId = isset($payload["owner_id"]) ? (int) $payload["owner_id"] : 0;
    if ($ownerId <= 0) {
        $ownerId = upsellio_crm_get_default_owner_id();
    }

    $title = $name !== "" ? $name : ($email !== "" ? $email : "Nowy lead");
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

    update_post_meta($leadId, "_upsellio_lead_email", $email);
    update_post_meta($leadId, "_upsellio_lead_phone", $phone);
    update_post_meta($leadId, "_upsellio_lead_service", $service);
    update_post_meta($leadId, "_upsellio_lead_budget", $budget);
    update_post_meta($leadId, "_upsellio_lead_goal", $goal);
    update_post_meta($leadId, "_upsellio_lead_score", $score);
    update_post_meta($leadId, "_upsellio_lead_form_origin", $formOrigin);
    update_post_meta($leadId, "_upsellio_lead_utm_source", $utmSource);
    update_post_meta($leadId, "_upsellio_lead_utm_medium", $utmMedium);
    update_post_meta($leadId, "_upsellio_lead_utm_campaign", $utmCampaign);
    update_post_meta($leadId, "_upsellio_lead_landing_url", $landingUrl);
    update_post_meta($leadId, "_upsellio_lead_referrer", $referrer);

    $statusTermId = upsellio_crm_get_term_id_by_slug("lead_status", "new");
    if ($statusTermId > 0) {
        wp_set_object_terms($leadId, [$statusTermId], "lead_status", false);
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

    upsellio_crm_add_timeline_event($leadId, "created", "Lead został utworzony.");
    upsellio_crm_create_followup_tasks_for_owner($leadId, $ownerId);

    return (int) $leadId;
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

function upsellio_crm_send_emails($lead_id, $name, $email, $message)
{
    $adminEmail = get_option("admin_email");
    $subject = "Nowy lead w CRM Upsellio";
    $body = "Lead ID: {$lead_id}\nImię/Firma: {$name}\nE-mail: {$email}\n\nWiadomość:\n{$message}";
    wp_mail($adminEmail, $subject, $body);

    if (is_email($email)) {
        $autoresponderSubject = "Dziękuję za kontakt - Upsellio";
        $autoresponderBody = "Cześć {$name},\n\nDziękuję za wiadomość. Wrócę do Ciebie możliwie szybko z konkretną odpowiedzią.\n\nPozdrawiam,\nSebastian / Upsellio";
        wp_mail($email, $autoresponderSubject, $autoresponderBody);
    }
}

function upsellio_crm_handle_lead_submission()
{
    $redirectUrl = isset($_POST["redirect_url"]) ? esc_url_raw(wp_unslash($_POST["redirect_url"])) : home_url("/");
    if ($redirectUrl === "") {
        $redirectUrl = home_url("/");
    }

    if (!isset($_POST["upsellio_lead_form_nonce"]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST["upsellio_lead_form_nonce"])), "upsellio_unified_lead_form")) {
        wp_safe_redirect(add_query_arg("ups_lead_status", "error", $redirectUrl));
        exit;
    }

    $honeypot = isset($_POST["lead_website"]) ? sanitize_text_field(wp_unslash($_POST["lead_website"])) : "";
    if ($honeypot !== "") {
        wp_safe_redirect(add_query_arg("ups_lead_status", "success", $redirectUrl));
        exit;
    }

    $name = isset($_POST["lead_name"]) ? sanitize_text_field(wp_unslash($_POST["lead_name"])) : "";
    $email = isset($_POST["lead_email"]) ? sanitize_email(wp_unslash($_POST["lead_email"])) : "";
    $message = isset($_POST["lead_message"]) ? sanitize_textarea_field(wp_unslash($_POST["lead_message"])) : "";
    $consent = isset($_POST["lead_consent"]) ? sanitize_text_field(wp_unslash($_POST["lead_consent"])) : "";

    if ($name === "" || !is_email($email) || $message === "" || $consent !== "1") {
        wp_safe_redirect(add_query_arg("ups_lead_status", "error", $redirectUrl));
        exit;
    }

    $payload = [
        "name" => $name,
        "email" => $email,
        "phone" => isset($_POST["lead_phone"]) ? sanitize_text_field(wp_unslash($_POST["lead_phone"])) : "",
        "message" => $message,
        "service" => isset($_POST["lead_service"]) ? sanitize_text_field(wp_unslash($_POST["lead_service"])) : "",
        "budget" => isset($_POST["lead_budget"]) ? sanitize_text_field(wp_unslash($_POST["lead_budget"])) : "",
        "goal" => isset($_POST["lead_goal"]) ? sanitize_text_field(wp_unslash($_POST["lead_goal"])) : "",
        "form_origin" => isset($_POST["lead_form_origin"]) ? sanitize_text_field(wp_unslash($_POST["lead_form_origin"])) : "contact-form",
        "source" => isset($_POST["lead_source"]) ? sanitize_text_field(wp_unslash($_POST["lead_source"])) : "",
        "utm_source" => isset($_POST["utm_source"]) ? sanitize_text_field(wp_unslash($_POST["utm_source"])) : "",
        "utm_medium" => isset($_POST["utm_medium"]) ? sanitize_text_field(wp_unslash($_POST["utm_medium"])) : "",
        "utm_campaign" => isset($_POST["utm_campaign"]) ? sanitize_text_field(wp_unslash($_POST["utm_campaign"])) : "",
        "landing_url" => isset($_POST["landing_url"]) ? esc_url_raw(wp_unslash($_POST["landing_url"])) : "",
        "referrer" => isset($_POST["referrer"]) ? esc_url_raw(wp_unslash($_POST["referrer"])) : "",
    ];

    $leadId = upsellio_crm_create_lead($payload);
    if ($leadId <= 0) {
        wp_safe_redirect(add_query_arg("ups_lead_status", "error", $redirectUrl));
        exit;
    }

    upsellio_crm_send_emails($leadId, $name, $email, $message);
    upsellio_crm_schedule_followup($leadId);

    wp_safe_redirect(add_query_arg("ups_lead_status", "success", $redirectUrl));
    exit;
}
add_action("admin_post_upsellio_submit_lead", "upsellio_crm_handle_lead_submission");
add_action("admin_post_nopriv_upsellio_submit_lead", "upsellio_crm_handle_lead_submission");

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

    $adminEmail = get_option("admin_email");
    $subject = "Przypomnienie: lead bez kontaktu >24h";
    $body = "Lead #{$lead_id} nadal ma status NOWY i nie ma oznaczonego pierwszego kontaktu.";
    wp_mail($adminEmail, $subject, $body);
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
    $score = get_post_meta($post->ID, "_upsellio_lead_score", true);
    $notes = get_post_meta($post->ID, "_upsellio_lead_notes", true);
    $utmSource = get_post_meta($post->ID, "_upsellio_lead_utm_source", true);
    $utmMedium = get_post_meta($post->ID, "_upsellio_lead_utm_medium", true);
    $utmCampaign = get_post_meta($post->ID, "_upsellio_lead_utm_campaign", true);
    $landingUrl = get_post_meta($post->ID, "_upsellio_lead_landing_url", true);
    $referrer = get_post_meta($post->ID, "_upsellio_lead_referrer", true);
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
      <label><strong>Score</strong><br />
        <input type="number" min="0" max="100" name="upsellio_lead_score" value="<?php echo esc_attr((string) $score); ?>" class="small-text" />
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
    <p>Landing: <?php echo esc_html((string) $landingUrl); ?></p>
    <p>Referrer: <?php echo esc_html((string) $referrer); ?></p>
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
    update_post_meta($post_id, "_upsellio_lead_score", (int) ($_POST["upsellio_lead_score"] ?? 0));
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
        .ups-crm-tab.active{background:#1d9e75;color:#fff;border-color:#1d9e75}
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
        .ups-kanban-col.is-over{outline:2px dashed #1d9e75;outline-offset:2px}
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

function upsellio_crm_add_admin_menu()
{
    add_submenu_page(
        "edit.php?post_type=lead",
        "Pipeline CRM",
        "Pipeline",
        "edit_posts",
        "upsellio-crm-pipeline",
        "upsellio_crm_render_pipeline_page"
    );
    add_submenu_page(
        "edit.php?post_type=lead",
        "SLA Dashboard",
        "SLA Dashboard",
        "edit_posts",
        "upsellio-crm-sla",
        "upsellio_crm_render_sla_page"
    );
    add_submenu_page(
        "edit.php?post_type=lead",
        "Zadania Follow-up",
        "Zadania Follow-up",
        "edit_posts",
        "upsellio-crm-tasks",
        "upsellio_crm_render_tasks_page"
    );
    add_submenu_page(
        "edit.php?post_type=lead",
        "Raporty CRM",
        "Raporty CRM",
        "edit_posts",
        "upsellio-crm-reports",
        "upsellio_crm_render_reports_page"
    );
}
add_action("admin_menu", "upsellio_crm_add_admin_menu");

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
