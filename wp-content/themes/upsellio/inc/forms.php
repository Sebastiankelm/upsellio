<?php

if (!defined("ABSPATH")) {
    exit;
}
function upsellio_submit_contact_form()
{
    check_ajax_referer("upsellio_contact_click", "nonce");

    $name = isset($_POST["name"]) ? sanitize_text_field(wp_unslash($_POST["name"])) : "";
    $email = isset($_POST["email"]) ? sanitize_email(wp_unslash($_POST["email"])) : "";
    $message = isset($_POST["message"]) ? sanitize_textarea_field(wp_unslash($_POST["message"])) : "";
    $phone = isset($_POST["phone"]) ? sanitize_text_field(wp_unslash($_POST["phone"])) : "";
    $service = isset($_POST["service"]) ? sanitize_text_field(wp_unslash($_POST["service"])) : "";
    $budget = isset($_POST["budget"]) ? sanitize_text_field(wp_unslash($_POST["budget"])) : "";
    $goal = isset($_POST["goal"]) ? sanitize_text_field(wp_unslash($_POST["goal"])) : "";
    $source = isset($_POST["source"]) ? esc_url_raw(wp_unslash($_POST["source"])) : "";
    $website = isset($_POST["website"]) ? sanitize_text_field(wp_unslash($_POST["website"])) : "";

    if ($website !== "") {
        wp_send_json_success([
            "message" => "Dziekujemy, formularz zostal wyslany.",
        ]);
    }

    if (upsellio_strlen($name) < 2) {
        wp_send_json_error([
            "message" => "Podaj imie i nazwe firmy.",
        ], 400);
    }

    if (!is_email($email)) {
        wp_send_json_error([
            "message" => "Podaj poprawny adres e-mail.",
        ], 400);
    }

    if (upsellio_strlen($message) < 10) {
        wp_send_json_error([
            "message" => "Opisz sytuacje w minimum 10 znakach.",
        ], 400);
    }

    $lead_id = upsellio_crm_create_lead([
        "name" => $name,
        "email" => $email,
        "phone" => $phone,
        "message" => $message,
        "service" => $service,
        "budget" => $budget,
        "goal" => $goal,
        "form_origin" => "contact-form-ajax",
        "source" => "contact-form",
        "landing_url" => $source,
        "referrer" => "",
    ]);

    if ($lead_id <= 0) {
        wp_send_json_error([
            "message" => "Nie udalo sie zapisac leada. Sprobuj ponownie za chwile.",
        ], 500);
    }
    upsellio_crm_send_emails($lead_id, $name, $email, $message);
    upsellio_crm_schedule_followup($lead_id);

    wp_send_json_success([
        "message" => "Wiadomosc wyslana. Odezwiemy sie wkrotce.",
    ]);
}
add_action("wp_ajax_upsellio_submit_contact_form", "upsellio_submit_contact_form");
add_action("wp_ajax_nopriv_upsellio_submit_contact_form", "upsellio_submit_contact_form");
