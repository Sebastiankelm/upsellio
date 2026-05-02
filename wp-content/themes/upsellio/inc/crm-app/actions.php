<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_crm_app_append_entity_log($entity_type, $entity_id, $event, $message, $context = [])
{
    $entity_type = sanitize_key((string) $entity_type);
    $entity_id = (int) $entity_id;
    $event = sanitize_key((string) $event);
    if ($entity_type === "" || $entity_id <= 0 || $event === "") {
        return;
    }
    $entry = [
        "ts" => current_time("mysql"),
        "event" => $event,
        "message" => sanitize_text_field((string) $message),
        "user_id" => get_current_user_id(),
        "context" => is_array($context) ? $context : [],
    ];
    $entity_log_key = "_ups_" . $entity_type . "_activity_log";
    $entity_log = get_post_meta($entity_id, $entity_log_key, true);
    if (!is_array($entity_log)) {
        $entity_log = [];
    }
    $entity_log[] = $entry;
    if (count($entity_log) > 200) {
        $entity_log = array_slice($entity_log, -200);
    }
    update_post_meta($entity_id, $entity_log_key, $entity_log);
}

function upsellio_crm_app_handle_post_actions()
{
    if (!upsellio_crm_app_user_can_access() || $_SERVER["REQUEST_METHOD"] !== "POST") {
        return;
    }
    if (!isset($_POST["ups_crm_app_nonce"])) {
        return;
    }
    $nonce = sanitize_text_field(wp_unslash($_POST["ups_crm_app_nonce"]));
    if (!wp_verify_nonce($nonce, "ups_crm_app_action")) {
        return;
    }

    $action = isset($_POST["ups_action"]) ? sanitize_key(wp_unslash($_POST["ups_action"])) : "";
    if ($action === "save_lead") {
        $lead_id = isset($_POST["lead_id"]) ? (int) wp_unslash($_POST["lead_id"]) : 0;
        $title = isset($_POST["lead_title"]) ? sanitize_text_field(wp_unslash($_POST["lead_title"])) : "";
        if ($title === "" || !post_type_exists("crm_lead")) {
            return;
        }
        if ($lead_id > 0 && get_post_type($lead_id) === "crm_lead") {
            wp_update_post(["ID" => $lead_id, "post_title" => $title]);
        } else {
            $lead_id = (int) wp_insert_post([
                "post_type" => "crm_lead",
                "post_status" => "publish",
                "post_title" => $title,
            ]);
        }
        if ($lead_id > 0) {
            update_post_meta($lead_id, "_ups_lead_email", isset($_POST["lead_email"]) ? sanitize_email(wp_unslash($_POST["lead_email"])) : "");
            update_post_meta($lead_id, "_ups_lead_phone", isset($_POST["lead_phone"]) ? sanitize_text_field(wp_unslash($_POST["lead_phone"])) : "");
            update_post_meta($lead_id, "_ups_lead_source", isset($_POST["lead_source"]) ? sanitize_text_field(wp_unslash($_POST["lead_source"])) : "");
            update_post_meta($lead_id, "_ups_lead_type", isset($_POST["lead_type"]) ? sanitize_key(wp_unslash($_POST["lead_type"])) : "inbound");
            update_post_meta($lead_id, "_ups_lead_qualification_status", isset($_POST["lead_qualification_status"]) ? sanitize_key(wp_unslash($_POST["lead_qualification_status"])) : "new");
            update_post_meta($lead_id, "_ups_lead_need", isset($_POST["lead_need"]) ? sanitize_textarea_field(wp_unslash($_POST["lead_need"])) : "");
            update_post_meta($lead_id, "_ups_lead_budget", isset($_POST["lead_budget"]) ? (float) wp_unslash($_POST["lead_budget"]) : 0);
            update_post_meta($lead_id, "_ups_lead_decision_date", isset($_POST["lead_decision_date"]) ? sanitize_text_field(wp_unslash($_POST["lead_decision_date"])) : "");
            update_post_meta($lead_id, "_ups_lead_potential", isset($_POST["lead_potential"]) ? sanitize_key(wp_unslash($_POST["lead_potential"])) : "medium");
            update_post_meta($lead_id, "_ups_lead_notes", isset($_POST["lead_notes"]) ? sanitize_textarea_field(wp_unslash($_POST["lead_notes"])) : "");
            if (function_exists("upsellio_sales_engine_refresh_lead_hybrid_scores")) {
                upsellio_sales_engine_refresh_lead_hybrid_scores($lead_id);
            }
            upsellio_crm_app_append_entity_log("lead", $lead_id, "lead_saved", "Zapisano lead.", []);
        }
    } elseif ($action === "save_offer_outcomes") {
        $offer_id = isset($_POST["offer_id"]) ? (int) wp_unslash($_POST["offer_id"]) : 0;
        if ($offer_id > 0 && get_post_type($offer_id) === "crm_offer" && current_user_can("edit_post", $offer_id)) {
            if (isset($_POST["offer_win_reason"])) {
                update_post_meta($offer_id, "_ups_offer_win_reason", sanitize_text_field(wp_unslash($_POST["offer_win_reason"])));
            }
            if (isset($_POST["offer_loss_reason"])) {
                update_post_meta($offer_id, "_ups_offer_loss_reason", sanitize_key(wp_unslash($_POST["offer_loss_reason"])));
            }
            if (isset($_POST["offer_loss_reason_note"])) {
                update_post_meta($offer_id, "_ups_offer_loss_reason_note", sanitize_text_field(wp_unslash($_POST["offer_loss_reason_note"])));
            }
            upsellio_crm_app_append_entity_log("offer", $offer_id, "offer_outcomes_saved", "Zapisano powody win/loss.", []);
        }
    } elseif ($action === "convert_lead") {
        $lead_id = isset($_POST["lead_id"]) ? (int) wp_unslash($_POST["lead_id"]) : 0;
        $decision = isset($_POST["lead_decision"]) ? sanitize_key(wp_unslash($_POST["lead_decision"])) : "";
        if ($lead_id > 0 && get_post_type($lead_id) === "crm_lead") {
            if ($decision === "reject") {
                update_post_meta($lead_id, "_ups_lead_qualification_status", "rejected");
                upsellio_crm_app_append_entity_log("lead", $lead_id, "lead_rejected", "Lead odrzucony.", []);
            } elseif ($decision === "create_client_deal") {
                $lead_title = (string) get_the_title($lead_id);
                $client_id = (int) wp_insert_post([
                    "post_type" => "crm_client",
                    "post_status" => "publish",
                    "post_title" => $lead_title !== "" ? $lead_title : "Nowy klient",
                ]);
                if ($client_id > 0) {
                    update_post_meta($client_id, "_ups_client_email", (string) get_post_meta($lead_id, "_ups_lead_email", true));
                    update_post_meta($client_id, "_ups_client_phone", (string) get_post_meta($lead_id, "_ups_lead_phone", true));
                    update_post_meta($client_id, "_ups_client_lifecycle_status", "qualified");
                    $offer_id = (int) wp_insert_post([
                        "post_type" => "crm_offer",
                        "post_status" => "publish",
                        "post_title" => "Deal: " . $lead_title,
                    ]);
                    if ($offer_id > 0) {
                        update_post_meta($offer_id, "_ups_offer_client_id", $client_id);
                        update_post_meta($offer_id, "_ups_offer_status", "open");
                        update_post_meta($offer_id, "_ups_offer_stage", "awareness");
                        $utm_s = (string) get_post_meta($lead_id, "_ups_lead_utm_source", true);
                        $utm_c = (string) get_post_meta($lead_id, "_ups_lead_utm_campaign", true);
                        if ($utm_s !== "") {
                            update_post_meta($offer_id, "_ups_offer_utm_source", $utm_s);
                        } elseif ((string) get_post_meta($lead_id, "_ups_lead_source", true) !== "") {
                            update_post_meta($offer_id, "_ups_offer_utm_source", (string) get_post_meta($lead_id, "_ups_lead_source", true));
                        }
                        if ($utm_c !== "") {
                            update_post_meta($offer_id, "_ups_offer_utm_campaign", $utm_c);
                        }
                    }
                    update_post_meta($lead_id, "_ups_lead_qualification_status", "converted");
                    update_post_meta($lead_id, "_ups_lead_converted_client_id", $client_id);
                    update_post_meta($lead_id, "_ups_lead_converted_offer_id", $offer_id);
                    upsellio_crm_app_append_entity_log("lead", $lead_id, "lead_converted", "Lead skonwertowany do klienta i deala.", [
                        "client_id" => $client_id,
                        "offer_id" => $offer_id,
                    ]);
                    if ($offer_id > 0 && function_exists("upsellio_inbox_append_message")) {
                        $sender = function_exists("upsellio_followup_get_sender_settings") ? upsellio_followup_get_sender_settings() : [];
                        $from_em = sanitize_email((string) get_post_meta($client_id, "_ups_client_email", true));
                        if (!is_email($from_em)) {
                            $from_em = sanitize_email((string) get_post_meta($lead_id, "_ups_lead_email", true));
                        }
                        $need = trim((string) get_post_meta($lead_id, "_ups_lead_need", true));
                        $notes = trim((string) get_post_meta($lead_id, "_ups_lead_notes", true));
                        $body_plain = $need !== "" ? $need : $notes;
                        if ($body_plain === "") {
                            $body_plain = "Lead CRM #" . $lead_id . " przekształcony w deal — brak treści potrzeby w meta.";
                        }
                        upsellio_inbox_append_message($offer_id, [
                            "direction" => "in",
                            "from" => is_email($from_em) ? $from_em : "",
                            "to" => (string) ($sender["from_email"] ?? ""),
                            "subject" => "Źródło: konwersja leada CRM",
                            "body_plain" => $body_plain,
                            "body_html" => "",
                            "source" => "lead_conversion",
                            "read" => false,
                        ]);
                    }
                }
            }
        }
    } elseif ($action === "save_contact") {
        $contact_id = isset($_POST["contact_id"]) ? (int) wp_unslash($_POST["contact_id"]) : 0;
        $title = isset($_POST["contact_name"]) ? sanitize_text_field(wp_unslash($_POST["contact_name"])) : "";
        if ($title === "" || !post_type_exists("crm_contact")) {
            return;
        }
        if ($contact_id > 0 && get_post_type($contact_id) === "crm_contact") {
            wp_update_post(["ID" => $contact_id, "post_title" => $title]);
        } else {
            $contact_id = (int) wp_insert_post([
                "post_type" => "crm_contact",
                "post_status" => "publish",
                "post_title" => $title,
            ]);
        }
        if ($contact_id > 0) {
            update_post_meta($contact_id, "_ups_contact_client_id", isset($_POST["contact_client_id"]) ? (int) wp_unslash($_POST["contact_client_id"]) : 0);
            update_post_meta($contact_id, "_ups_contact_role", isset($_POST["contact_role"]) ? sanitize_text_field(wp_unslash($_POST["contact_role"])) : "");
            update_post_meta($contact_id, "_ups_contact_email", isset($_POST["contact_email"]) ? sanitize_email(wp_unslash($_POST["contact_email"])) : "");
            update_post_meta($contact_id, "_ups_contact_phone", isset($_POST["contact_phone"]) ? sanitize_text_field(wp_unslash($_POST["contact_phone"])) : "");
            update_post_meta($contact_id, "_ups_contact_notes", isset($_POST["contact_notes"]) ? sanitize_textarea_field(wp_unslash($_POST["contact_notes"])) : "");
            upsellio_crm_app_append_entity_log("contact", $contact_id, "contact_saved", "Zapisano kontakt B2B.", []);
        }
    } elseif ($action === "save_service") {
        $service_id = isset($_POST["service_id"]) ? (int) wp_unslash($_POST["service_id"]) : 0;
        $title = isset($_POST["service_title"]) ? sanitize_text_field(wp_unslash($_POST["service_title"])) : "";
        if ($title === "" || !post_type_exists("crm_service")) {
            return;
        }
        $content = isset($_POST["service_description"]) ? wp_kses_post(wp_unslash($_POST["service_description"])) : "";
        if ($service_id > 0 && get_post_type($service_id) === "crm_service") {
            wp_update_post(["ID" => $service_id, "post_title" => $title, "post_content" => $content]);
        } else {
            $service_id = (int) wp_insert_post([
                "post_type" => "crm_service",
                "post_status" => "publish",
                "post_title" => $title,
                "post_content" => $content,
            ]);
        }
        if ($service_id > 0) {
            update_post_meta($service_id, "_ups_service_pricing_type", isset($_POST["service_pricing_type"]) ? sanitize_key(wp_unslash($_POST["service_pricing_type"])) : "one_time");
            update_post_meta($service_id, "_ups_service_price", isset($_POST["service_price"]) ? (float) wp_unslash($_POST["service_price"]) : 0);
            update_post_meta($service_id, "_ups_service_setup_fee", isset($_POST["service_setup_fee"]) ? (float) wp_unslash($_POST["service_setup_fee"]) : 0);
            update_post_meta($service_id, "_ups_service_success_fee", isset($_POST["service_success_fee"]) ? (float) wp_unslash($_POST["service_success_fee"]) : 0);
            upsellio_crm_app_append_entity_log("service", $service_id, "service_saved", "Zapisano usługę/pakiet.", []);
        }
    } elseif ($action === "save_client") {
        $client_id = isset($_POST["client_id"]) ? (int) wp_unslash($_POST["client_id"]) : 0;
        $title = isset($_POST["client_title"]) ? sanitize_text_field(wp_unslash($_POST["client_title"])) : "";
        if ($title === "") {
            return;
        }
        if ($client_id > 0 && get_post_type($client_id) === "crm_client") {
            wp_update_post(["ID" => $client_id, "post_title" => $title]);
        } else {
            $client_id = (int) wp_insert_post([
                "post_type" => "crm_client",
                "post_status" => "publish",
                "post_title" => $title,
            ]);
        }
        if ($client_id > 0) {
            $previous_subscription_status = (string) get_post_meta($client_id, "_ups_client_subscription_status", true);
            update_post_meta($client_id, "_ups_client_email", isset($_POST["client_email"]) ? sanitize_email(wp_unslash($_POST["client_email"])) : "");
            update_post_meta($client_id, "_ups_client_phone", isset($_POST["client_phone"]) ? sanitize_text_field(wp_unslash($_POST["client_phone"])) : "");
            update_post_meta($client_id, "_ups_client_company", isset($_POST["client_company"]) ? sanitize_text_field(wp_unslash($_POST["client_company"])) : "");
            update_post_meta($client_id, "_ups_client_industry", isset($_POST["client_industry"]) ? sanitize_text_field(wp_unslash($_POST["client_industry"])) : "");
            update_post_meta($client_id, "_ups_client_company_size", isset($_POST["client_company_size"]) ? sanitize_text_field(wp_unslash($_POST["client_company_size"])) : "");
            update_post_meta($client_id, "_ups_client_budget_range", isset($_POST["client_budget_range"]) ? sanitize_text_field(wp_unslash($_POST["client_budget_range"])) : "");
            update_post_meta($client_id, "_ups_client_is_recurring", isset($_POST["client_is_recurring"]) ? "1" : "0");
            update_post_meta($client_id, "_ups_client_monthly_value", isset($_POST["client_monthly_value"]) ? (float) wp_unslash($_POST["client_monthly_value"]) : 0);
            update_post_meta($client_id, "_ups_client_billing_start", isset($_POST["client_billing_start"]) ? sanitize_text_field(wp_unslash($_POST["client_billing_start"])) : "");
            $subscription_status = isset($_POST["client_subscription_status"]) ? sanitize_key(wp_unslash($_POST["client_subscription_status"])) : "active";
            update_post_meta($client_id, "_ups_client_subscription_status", $subscription_status);
            update_post_meta($client_id, "_ups_client_cancellation_date", isset($_POST["client_cancellation_date"]) ? sanitize_text_field(wp_unslash($_POST["client_cancellation_date"])) : "");
            update_post_meta($client_id, "_ups_client_cancellation_reason", isset($_POST["client_cancellation_reason"]) ? sanitize_textarea_field(wp_unslash($_POST["client_cancellation_reason"])) : "");
            update_post_meta($client_id, "_ups_client_notes", isset($_POST["client_notes"]) ? sanitize_textarea_field(wp_unslash($_POST["client_notes"])) : "");
            if (isset($_POST["client_last_call_notes"])) {
                update_post_meta($client_id, "_ups_client_last_call_notes", sanitize_textarea_field(wp_unslash($_POST["client_last_call_notes"])));
            }
            if (isset($_POST["client_next_contact_date"])) {
                update_post_meta($client_id, "_ups_client_next_contact_date", sanitize_text_field(wp_unslash($_POST["client_next_contact_date"])));
            }
            if ($previous_subscription_status !== "cancelled" && $subscription_status === "cancelled") {
                do_action("upsellio_client_subscription_cancelled", $client_id);
            }
            upsellio_crm_app_append_entity_log("client", $client_id, "client_saved", "Zapisano dane klienta.", []);
            $_POST["client_id"] = (string) $client_id;
        }
    } elseif ($action === "save_offer_layout") {
        $tid = isset($_POST["offer_layout_id"]) ? (int) wp_unslash($_POST["offer_layout_id"]) : 0;
        $name = isset($_POST["offer_layout_title"]) ? sanitize_text_field(wp_unslash($_POST["offer_layout_title"])) : "";
        $json_raw = isset($_POST["offer_layout_payload"]) ? wp_unslash($_POST["offer_layout_payload"]) : "";
        if ($name === "") {
            return;
        }
        if (!empty($_POST["offer_layout_form"]) && function_exists("upsellio_offer_layout_build_payload_from_form_post")) {
            $decoded = upsellio_offer_layout_build_payload_from_form_post();
        } else {
            $decoded = json_decode(is_string($json_raw) ? $json_raw : "", true);
            if (!is_array($decoded)) {
                $decoded = function_exists("upsellio_offer_layout_get_default_payload") ? upsellio_offer_layout_get_default_payload() : [];
            }
        }
        $canonical = wp_json_encode($decoded, JSON_UNESCAPED_UNICODE);
        if ($tid > 0 && get_post_type($tid) === "crm_offer_layout") {
            wp_update_post(["ID" => $tid, "post_title" => $name]);
        } else {
            $tid = (int) wp_insert_post([
                "post_type" => "crm_offer_layout",
                "post_status" => "publish",
                "post_title" => $name,
            ]);
        }
        if ($tid > 0) {
            update_post_meta($tid, "_ups_offer_layout_payload", $canonical);
            upsellio_crm_app_append_entity_log("offer_layout", $tid, "offer_layout_saved", "Zapisano szablon layoutu oferty.", []);
            set_transient("ups_crm_offer_layout_just_saved_" . get_current_user_id(), $tid, 120);
        }
    } elseif ($action === "delete_offer_layout") {
        $tid = isset($_POST["offer_layout_id"]) ? (int) wp_unslash($_POST["offer_layout_id"]) : 0;
        if ($tid > 0 && get_post_type($tid) === "crm_offer_layout" && current_user_can("delete_post", $tid)) {
            wp_trash_post($tid);
            upsellio_crm_app_append_entity_log("offer_layout", $tid, "offer_layout_trashed", "Usunięto szablon layoutu oferty.", []);
        }
    } elseif ($action === "save_contract_layout") {
        $tid = isset($_POST["contract_layout_id"]) ? (int) wp_unslash($_POST["contract_layout_id"]) : 0;
        $name = isset($_POST["contract_layout_title"]) ? sanitize_text_field(wp_unslash($_POST["contract_layout_title"])) : "";
        $body = isset($_POST["contract_layout_html"]) ? wp_kses_post((string) wp_unslash($_POST["contract_layout_html"])) : "";
        $css = isset($_POST["contract_layout_css"]) ? wp_strip_all_tags((string) wp_unslash($_POST["contract_layout_css"])) : "";
        if ($name === "") {
            return;
        }
        if ($tid > 0 && get_post_type($tid) === "crm_contract_layout") {
            wp_update_post(["ID" => $tid, "post_title" => $name, "post_content" => $body]);
        } else {
            $tid = (int) wp_insert_post([
                "post_type" => "crm_contract_layout",
                "post_status" => "publish",
                "post_title" => $name,
                "post_content" => $body,
            ]);
        }
        if ($tid > 0) {
            update_post_meta($tid, "_ups_contract_layout_css", $css);
            upsellio_crm_app_append_entity_log("contract_layout", $tid, "contract_layout_saved", "Zapisano szablon umowy.", []);
        }
    } elseif ($action === "delete_contract_layout") {
        $tid = isset($_POST["contract_layout_id"]) ? (int) wp_unslash($_POST["contract_layout_id"]) : 0;
        if ($tid > 0 && get_post_type($tid) === "crm_contract_layout" && current_user_can("delete_post", $tid)) {
            wp_trash_post($tid);
            upsellio_crm_app_append_entity_log("contract_layout", $tid, "contract_layout_trashed", "Usunięto szablon umowy.", []);
        }
    } elseif ($action === "save_offer") {
        $offer_id = isset($_POST["offer_id"]) ? (int) wp_unslash($_POST["offer_id"]) : 0;
        $title = isset($_POST["offer_title"]) ? sanitize_text_field(wp_unslash($_POST["offer_title"])) : "";
        $offer_content_raw = isset($_POST["offer_content"]) ? wp_kses_post(wp_unslash($_POST["offer_content"])) : "";
        $generate_offer_from_template = isset($_POST["offer_generate_from_template"]) && (string) wp_unslash($_POST["offer_generate_from_template"]) === "1";
        if ($title === "") {
            return;
        }
        $was_new_offer = !($offer_id > 0 && get_post_type($offer_id) === "crm_offer");
        if ($offer_id > 0 && get_post_type($offer_id) === "crm_offer") {
            wp_update_post([
                "ID" => $offer_id,
                "post_title" => $title,
                "post_content" => $offer_content_raw,
            ]);
        } else {
            $offer_id = (int) wp_insert_post([
                "post_type" => "crm_offer",
                "post_status" => "publish",
                "post_title" => $title,
                "post_content" => $offer_content_raw,
            ]);
        }
        if ($offer_id > 0) {
            $layout_tid = isset($_POST["offer_layout_template_id"]) ? (int) wp_unslash($_POST["offer_layout_template_id"]) : 0;
            if ($was_new_offer && $layout_tid > 0 && function_exists("upsellio_offer_merge_payload_into_offer_meta") && function_exists("upsellio_offer_layout_get_payload_from_post")) {
                upsellio_offer_merge_payload_into_offer_meta($offer_id, upsellio_offer_layout_get_payload_from_post($layout_tid));
            }
            $old_offer_status = (string) get_post_meta($offer_id, "_ups_offer_status", true);
            update_post_meta($offer_id, "_ups_offer_client_id", isset($_POST["offer_client_id"]) ? (int) wp_unslash($_POST["offer_client_id"]) : 0);
            update_post_meta($offer_id, "_ups_offer_price", isset($_POST["offer_price"]) ? sanitize_text_field(wp_unslash($_POST["offer_price"])) : "");
            update_post_meta($offer_id, "_ups_offer_timeline", isset($_POST["offer_timeline"]) ? sanitize_text_field(wp_unslash($_POST["offer_timeline"])) : "");
            update_post_meta($offer_id, "_ups_offer_cta_text", isset($_POST["offer_cta_text"]) ? sanitize_text_field(wp_unslash($_POST["offer_cta_text"])) : "");
            $new_offer_status = isset($_POST["offer_status"]) ? sanitize_key(wp_unslash($_POST["offer_status"])) : "open";
            if (!in_array($new_offer_status, ["open", "sent", "won", "lost"], true)) {
                $new_offer_status = "open";
            }
            $posted_loss_reason = isset($_POST["offer_loss_reason"]) ? sanitize_key(wp_unslash($_POST["offer_loss_reason"])) : "";
            if ($new_offer_status === "lost" && $posted_loss_reason === "") {
                $new_offer_status = $old_offer_status;
                set_transient("ups_crm_notice_" . get_current_user_id(), [
                    "type" => "error",
                    "message" => "Status „przegrany” wymaga wyboru powodu przegranej na liście „Powód przegranej”.",
                ], 60);
            }
            update_post_meta($offer_id, "_ups_offer_status", $new_offer_status);
            if ($new_offer_status === "sent" && $old_offer_status !== "sent") {
                if ((string) get_post_meta($offer_id, "_ups_offer_first_sent_at", true) === "") {
                    update_post_meta($offer_id, "_ups_offer_first_sent_at", current_time("mysql"));
                }
                update_post_meta($offer_id, "_ups_offer_stage", "consideration");
                if (function_exists("upsellio_offer_queue_builtin_sent_reminders")) {
                    upsellio_offer_queue_builtin_sent_reminders($offer_id);
                }
            }
            update_post_meta($offer_id, "_ups_offer_won_value", isset($_POST["offer_won_value"]) ? (float) wp_unslash($_POST["offer_won_value"]) : 0);
            update_post_meta($offer_id, "_ups_offer_owner_id", isset($_POST["offer_owner_id"]) ? (int) wp_unslash($_POST["offer_owner_id"]) : 0);
            update_post_meta($offer_id, "_ups_deal_notes", isset($_POST["deal_notes"]) ? sanitize_textarea_field(wp_unslash($_POST["deal_notes"])) : "");
            update_post_meta($offer_id, "_ups_offer_internal_notes", isset($_POST["offer_internal_notes"]) ? sanitize_textarea_field(wp_unslash($_POST["offer_internal_notes"])) : "");
            update_post_meta($offer_id, "_ups_offer_win_reason", isset($_POST["offer_win_reason"]) ? sanitize_text_field(wp_unslash($_POST["offer_win_reason"])) : "");
            update_post_meta($offer_id, "_ups_offer_loss_reason", isset($_POST["offer_loss_reason"]) ? sanitize_key(wp_unslash($_POST["offer_loss_reason"])) : "");
            update_post_meta($offer_id, "_ups_offer_loss_reason_note", isset($_POST["offer_loss_reason_note"]) ? sanitize_text_field(wp_unslash($_POST["offer_loss_reason_note"])) : "");
            update_post_meta($offer_id, "_ups_offer_lead", isset($_POST["offer_lead"]) ? sanitize_textarea_field(wp_unslash($_POST["offer_lead"])) : "");
            update_post_meta($offer_id, "_ups_offer_duration", isset($_POST["offer_duration"]) ? sanitize_text_field(wp_unslash($_POST["offer_duration"])) : "");
            update_post_meta($offer_id, "_ups_offer_billing", isset($_POST["offer_billing"]) ? sanitize_text_field(wp_unslash($_POST["offer_billing"])) : "");
            update_post_meta($offer_id, "_ups_offer_price_note", isset($_POST["offer_price_note"]) ? sanitize_text_field(wp_unslash($_POST["offer_price_note"])) : "");
            update_post_meta($offer_id, "_ups_offer_show_proof", isset($_POST["offer_show_proof"]) ? "1" : "0");
            update_post_meta($offer_id, "_ups_offer_proof_lines", isset($_POST["offer_proof_lines"]) ? sanitize_textarea_field(wp_unslash($_POST["offer_proof_lines"])) : "");
            update_post_meta($offer_id, "_ups_offer_has_google", isset($_POST["offer_has_google"]) ? "1" : "0");
            update_post_meta($offer_id, "_ups_offer_has_meta", isset($_POST["offer_has_meta"]) ? "1" : "0");
            update_post_meta($offer_id, "_ups_offer_has_web", isset($_POST["offer_has_web"]) ? "1" : "0");
            update_post_meta($offer_id, "_ups_offer_questions_raw", isset($_POST["offer_questions_raw"]) ? sanitize_textarea_field(wp_unslash($_POST["offer_questions_raw"])) : "");
            $srv_json = isset($_POST["offer_services_json"]) ? (string) wp_unslash($_POST["offer_services_json"]) : "";
            if ($srv_json !== "") {
                $dec = json_decode($srv_json, true);
                update_post_meta($offer_id, "_ups_offer_services_json", is_array($dec) ? wp_json_encode($dec) : "");
            } elseif (isset($_POST["offer_services_json"])) {
                update_post_meta($offer_id, "_ups_offer_services_json", "");
            }
            update_post_meta($offer_id, "_ups_offer_include_lines", isset($_POST["offer_include_lines"]) ? sanitize_textarea_field(wp_unslash($_POST["offer_include_lines"])) : "");
            update_post_meta($offer_id, "_ups_offer_option_lines", isset($_POST["offer_option_lines"]) ? sanitize_textarea_field(wp_unslash($_POST["offer_option_lines"])) : "");
            update_post_meta($offer_id, "_ups_offer_scope_extra_html", isset($_POST["offer_scope_extra_html"]) ? wp_kses_post(wp_unslash($_POST["offer_scope_extra_html"])) : "");
            $exp_in = isset($_POST["offer_expires_at"]) ? sanitize_text_field(wp_unslash($_POST["offer_expires_at"])) : "";
            if ($exp_in !== "") {
                $local_ts = strtotime($exp_in);
                if ($local_ts !== false) {
                    $utc_ts = (int) $local_ts - (int) (get_option("gmt_offset", 0) * HOUR_IN_SECONDS);
                    update_post_meta($offer_id, "_ups_offer_expires_at", (int) $utc_ts);
                }
            } else {
                delete_post_meta($offer_id, "_ups_offer_expires_at");
            }
            $decision_date_in = isset($_POST["offer_decision_date"]) ? sanitize_text_field(wp_unslash($_POST["offer_decision_date"])) : "";
            if ($decision_date_in !== "") {
                update_post_meta($offer_id, "_ups_offer_decision_date", $decision_date_in);
            } else {
                delete_post_meta($offer_id, "_ups_offer_decision_date");
            }
            if (function_exists("upsellio_offer_generate_unique_slug")) {
                upsellio_offer_generate_unique_slug($offer_id);
            }
            if ($generate_offer_from_template && function_exists("upsellio_offer_get_default_template_html")) {
                $template_html = (string) upsellio_offer_get_default_template_html();
                $template_css = (string) upsellio_offer_get_default_template_css();
                $resolved_html = function_exists("upsellio_offer_replace_template_placeholders")
                    ? (string) upsellio_offer_replace_template_placeholders($template_html, $offer_id)
                    : $template_html;
                $resolved_css = function_exists("upsellio_offer_replace_template_placeholders")
                    ? (string) upsellio_offer_replace_template_placeholders($template_css, $offer_id)
                    : $template_css;
                wp_update_post([
                    "ID" => $offer_id,
                    "post_content" => "<style>" . $resolved_css . "</style>\n" . $resolved_html,
                ]);
            }
            if ($old_offer_status !== $new_offer_status) {
                do_action("upsellio_offer_status_changed", $offer_id, $new_offer_status, $old_offer_status);
            }
            if (function_exists("upsellio_sales_engine_refresh_hybrid_deal_scores")) {
                $intent = (int) get_post_meta($offer_id, "_ups_offer_intent_score", true);
                $fit = (int) get_post_meta($offer_id, "_ups_offer_fit_score", true);
                $st = (string) get_post_meta($offer_id, "_ups_offer_stage", true);
                if ($st === "") {
                    $st = "awareness";
                }
                upsellio_sales_engine_refresh_hybrid_deal_scores($offer_id, $intent > 0 ? $intent : (int) get_post_meta($offer_id, "_ups_offer_score", true), $fit > 0 ? $fit : 40, $st);
            }
            upsellio_crm_app_append_entity_log("offer", $offer_id, "offer_saved", "Zapisano dane deala/oferty.", [
                "status" => $new_offer_status,
            ]);
        }
    } elseif ($action === "save_followup") {
        $template_id = isset($_POST["template_id"]) ? (int) wp_unslash($_POST["template_id"]) : 0;
        $title = isset($_POST["template_title"]) ? sanitize_text_field(wp_unslash($_POST["template_title"])) : "";
        if ($title === "") {
            return;
        }
        if ($template_id > 0 && get_post_type($template_id) === "ups_followup_template") {
            wp_update_post([
                "ID" => $template_id,
                "post_title" => $title,
                "post_content" => isset($_POST["template_content"]) ? wp_kses_post(wp_unslash($_POST["template_content"])) : "",
            ]);
        } else {
            $template_id = (int) wp_insert_post([
                "post_type" => "ups_followup_template",
                "post_status" => "publish",
                "post_title" => $title,
                "post_content" => isset($_POST["template_content"]) ? wp_kses_post(wp_unslash($_POST["template_content"])) : "",
            ]);
        }
        if ($template_id > 0) {
            update_post_meta($template_id, "_ups_followup_trigger_event", isset($_POST["template_trigger"]) ? sanitize_key(wp_unslash($_POST["template_trigger"])) : "any");
            update_post_meta($template_id, "_ups_followup_stage", isset($_POST["template_stage"]) ? sanitize_key(wp_unslash($_POST["template_stage"])) : "any");
            update_post_meta($template_id, "_ups_followup_delay_minutes", isset($_POST["template_delay"]) ? max(0, (int) wp_unslash($_POST["template_delay"])) : 0);
            update_post_meta($template_id, "_ups_followup_subject", isset($_POST["template_subject"]) ? sanitize_text_field(wp_unslash($_POST["template_subject"])) : "");
            update_post_meta($template_id, "_ups_followup_html", isset($_POST["template_html"]) ? wp_kses_post((string) wp_unslash($_POST["template_html"])) : "");
            update_post_meta($template_id, "_ups_followup_css", isset($_POST["template_css"]) ? wp_strip_all_tags((string) wp_unslash($_POST["template_css"])) : "");
            update_post_meta($template_id, "_ups_followup_active", isset($_POST["template_active"]) ? "1" : "0");
            upsellio_crm_app_append_entity_log("followup", $template_id, "followup_saved", "Zapisano szablon follow-up.", []);
        }
    } elseif ($action === "save_contract_template") {
        update_option("ups_contract_template_html", isset($_POST["contract_template_html"]) ? wp_kses_post((string) wp_unslash($_POST["contract_template_html"])) : "");
        update_option("ups_contract_template_css", isset($_POST["contract_template_css"]) ? wp_strip_all_tags((string) wp_unslash($_POST["contract_template_css"])) : "");
    } elseif ($action === "save_offer_template") {
        update_option("ups_offer_template_html", isset($_POST["offer_template_html"]) ? wp_kses_post((string) wp_unslash($_POST["offer_template_html"])) : "");
        update_option("ups_offer_template_css", isset($_POST["offer_template_css"]) ? wp_strip_all_tags((string) wp_unslash($_POST["offer_template_css"])) : "");
    } elseif ($action === "save_contract") {
        $contract_id = isset($_POST["contract_id"]) ? (int) wp_unslash($_POST["contract_id"]) : 0;
        $title = isset($_POST["contract_title"]) ? sanitize_text_field(wp_unslash($_POST["contract_title"])) : "";
        $client_id = isset($_POST["contract_client_id"]) ? (int) wp_unslash($_POST["contract_client_id"]) : 0;
        $offer_id = isset($_POST["contract_offer_id"]) ? (int) wp_unslash($_POST["contract_offer_id"]) : 0;
        $status = isset($_POST["contract_status"]) ? sanitize_key(wp_unslash($_POST["contract_status"])) : "draft";
        $html = isset($_POST["contract_html"]) ? wp_kses_post((string) wp_unslash($_POST["contract_html"])) : "";
        $css = isset($_POST["contract_css"]) ? wp_strip_all_tags((string) wp_unslash($_POST["contract_css"])) : "";
        $generate_from_template = isset($_POST["contract_generate_from_template"]) && (string) wp_unslash($_POST["contract_generate_from_template"]) === "1";
        $contract_layout_tpl_id = isset($_POST["contract_layout_template_id"]) ? (int) wp_unslash($_POST["contract_layout_template_id"]) : 0;
        if ($title === "") {
            $title = "Umowa";
        }
        $is_new_contract = !($contract_id > 0 && get_post_type($contract_id) === "crm_contract");
        $previous_status = $contract_id > 0 ? (string) get_post_meta($contract_id, "_ups_contract_status", true) : "";
        if ($contract_id > 0 && get_post_type($contract_id) === "crm_contract") {
            wp_update_post([
                "ID" => $contract_id,
                "post_title" => $title,
                "post_content" => isset($_POST["contract_content"]) ? wp_kses_post(wp_unslash($_POST["contract_content"])) : "",
            ]);
            $version = (int) get_post_meta($contract_id, "_ups_contract_version", true);
            if ($version <= 0) {
                $version = 1;
            } else {
                $version++;
            }
            update_post_meta($contract_id, "_ups_contract_version", $version);
        } else {
            $contract_id = (int) wp_insert_post([
                "post_type" => "crm_contract",
                "post_status" => "publish",
                "post_title" => $title,
                "post_content" => isset($_POST["contract_content"]) ? wp_kses_post(wp_unslash($_POST["contract_content"])) : "",
            ]);
            update_post_meta($contract_id, "_ups_contract_version", 1);
        }
        if ($contract_id > 0) {
            if ($generate_from_template && $contract_layout_tpl_id > 0 && get_post_type($contract_layout_tpl_id) === "crm_contract_layout") {
                $tpl_html = (string) get_post_field("post_content", $contract_layout_tpl_id);
                $tpl_css = (string) get_post_meta($contract_layout_tpl_id, "_ups_contract_layout_css", true);
                if ($tpl_css === "") {
                    $tpl_css = function_exists("upsellio_contracts_get_default_template_css") ? (string) upsellio_contracts_get_default_template_css() : "";
                }
                $html = function_exists("upsellio_contracts_replace_placeholders")
                    ? (string) upsellio_contracts_replace_placeholders($tpl_html, $client_id, $offer_id, $contract_id)
                    : $tpl_html;
                $css = $tpl_css;
            } elseif ($generate_from_template && function_exists("upsellio_contracts_get_default_template_html")) {
                $tpl_html = (string) upsellio_contracts_get_default_template_html();
                $tpl_css = (string) upsellio_contracts_get_default_template_css();
                $html = function_exists("upsellio_contracts_replace_placeholders")
                    ? (string) upsellio_contracts_replace_placeholders($tpl_html, $client_id, $offer_id, $contract_id)
                    : $tpl_html;
                $css = $tpl_css;
            } elseif (function_exists("upsellio_contracts_replace_placeholders")) {
                $html = (string) upsellio_contracts_replace_placeholders($html, $client_id, $offer_id, $contract_id);
            }
            update_post_meta($contract_id, "_ups_contract_client_id", $client_id);
            update_post_meta($contract_id, "_ups_contract_offer_id", $offer_id);
            if (function_exists("upsellio_contracts_set_status")) {
                upsellio_contracts_set_status($contract_id, $status, [
                    "source" => "crm_app",
                    "client_id" => $client_id,
                    "offer_id" => $offer_id,
                ]);
            } else {
                update_post_meta($contract_id, "_ups_contract_status", $status);
            }
            update_post_meta($contract_id, "_ups_contract_html", $html);
            update_post_meta($contract_id, "_ups_contract_css", $css);
            if (function_exists("upsellio_contracts_get_public_url")) {
                upsellio_contracts_get_public_url($contract_id);
            }
            if (function_exists("upsellio_contracts_save_version_snapshot")) {
                upsellio_contracts_save_version_snapshot($contract_id);
            }
            if (function_exists("upsellio_contracts_log_event")) {
                if ($is_new_contract) {
                    upsellio_contracts_log_event($contract_id, "created", "Utworzono umowe", [
                        "status" => $status,
                        "client_id" => $client_id,
                        "offer_id" => $offer_id,
                    ]);
                } else {
                    upsellio_contracts_log_event($contract_id, "updated", "Zapisano zmiany umowy", [
                        "status" => $status,
                        "client_id" => $client_id,
                        "offer_id" => $offer_id,
                    ]);
                }
                if ($status !== $previous_status) {
                    if ($status === "sent") {
                        upsellio_contracts_log_event($contract_id, "sent", "Wyslano umowe", []);
                    } elseif ($status === "signed") {
                        upsellio_contracts_log_event($contract_id, "signed", "Podpisano umowe", []);
                    } elseif ($status === "cancelled") {
                        upsellio_contracts_log_event($contract_id, "cancelled", "Anulowano umowe", []);
                    }
                }
            }
            upsellio_crm_app_append_entity_log("contract", $contract_id, "contract_saved", "Zapisano umowę.", [
                "status" => $status,
                "client_id" => $client_id,
                "offer_id" => $offer_id,
            ]);
        }
    } elseif ($action === "save_prospect") {
        $prospect_id = isset($_POST["prospect_id"]) ? (int) wp_unslash($_POST["prospect_id"]) : 0;
        $title = isset($_POST["prospect_title"]) ? sanitize_text_field(wp_unslash($_POST["prospect_title"])) : "";
        if ($title === "") {
            return;
        }
        if ($prospect_id > 0 && get_post_type($prospect_id) === "crm_prospect") {
            wp_update_post([
                "ID" => $prospect_id,
                "post_title" => $title,
            ]);
        } else {
            $prospect_id = (int) wp_insert_post([
                "post_type" => "crm_prospect",
                "post_status" => "publish",
                "post_title" => $title,
            ]);
        }
        if ($prospect_id > 0) {
            update_post_meta($prospect_id, "_ups_prospect_email", isset($_POST["prospect_email"]) ? sanitize_email(wp_unslash($_POST["prospect_email"])) : "");
            update_post_meta($prospect_id, "_ups_prospect_name", isset($_POST["prospect_name"]) ? sanitize_text_field(wp_unslash($_POST["prospect_name"])) : "");
            update_post_meta($prospect_id, "_ups_prospect_company", isset($_POST["prospect_company"]) ? sanitize_text_field(wp_unslash($_POST["prospect_company"])) : "");
            update_post_meta($prospect_id, "_ups_prospect_status", isset($_POST["prospect_status"]) ? sanitize_key(wp_unslash($_POST["prospect_status"])) : "active");
            update_post_meta($prospect_id, "_ups_prospect_step", isset($_POST["prospect_step"]) ? max(1, min(5, (int) wp_unslash($_POST["prospect_step"]))) : 1);
            update_post_meta($prospect_id, "_ups_prospect_stage", isset($_POST["prospect_stage"]) ? sanitize_key(wp_unslash($_POST["prospect_stage"])) : "awareness");
            update_post_meta($prospect_id, "_ups_prospect_notes", isset($_POST["prospect_notes"]) ? sanitize_textarea_field(wp_unslash($_POST["prospect_notes"])) : "");
            if (isset($_POST["prospect_send_now"])) {
                update_post_meta($prospect_id, "_ups_prospect_next_at", gmdate("Y-m-d H:i:s", time()));
            }
            upsellio_crm_app_append_entity_log("prospect", $prospect_id, "prospect_saved", "Zapisano prospecta.", []);
        }
    } elseif ($action === "send_prospect_email_now") {
        $prospect_id = isset($_POST["prospect_id"]) ? (int) wp_unslash($_POST["prospect_id"]) : 0;
        if ($prospect_id > 0 && get_post_type($prospect_id) === "crm_prospect") {
            $email = sanitize_email((string) get_post_meta($prospect_id, "_ups_prospect_email", true));
            if (is_email($email) && function_exists("upsellio_followup_send_html_mail")) {
                $step = max(1, min(5, (int) get_post_meta($prospect_id, "_ups_prospect_step", true)));
                $subject = (string) get_option("ups_prospect_subject_step_" . $step, "Krótka propozycja współpracy");
                $body_tpl = (string) get_option("ups_prospect_body_step_" . $step, "Cześć {{name}},\n\nCzy mogę podesłać 2 pomysły?");
                $name = (string) get_post_meta($prospect_id, "_ups_prospect_name", true);
                $company = (string) get_post_meta($prospect_id, "_ups_prospect_company", true);
                $body = strtr($body_tpl, [
                    "{{name}}" => $name !== "" ? $name : "tam",
                    "{{company}}" => $company,
                    "{{today}}" => current_time("Y-m-d"),
                ]);
                $sent = upsellio_followup_send_html_mail($email, $subject, nl2br(esc_html($body)));
                if ($sent) {
                    update_post_meta($prospect_id, "_ups_prospect_last_sent_at", current_time("mysql"));
                    update_post_meta($prospect_id, "_ups_prospect_status", "active");
                    update_post_meta($prospect_id, "_ups_prospect_step", min(5, $step + 1));
                    upsellio_crm_app_append_entity_log("prospect", $prospect_id, "prospect_email_sent", "Wysłano email do prospecta.", ["step" => $step]);
                }
            }
        }
    } elseif ($action === "send_offer_followup_now") {
        $offer_id = isset($_POST["offer_id"]) ? (int) wp_unslash($_POST["offer_id"]) : 0;
        $template_id = isset($_POST["template_id"]) ? (int) wp_unslash($_POST["template_id"]) : 0;
        if ($offer_id > 0 && $template_id > 0 && function_exists("upsellio_followup_queue_message")) {
            $stage = (string) get_post_meta($offer_id, "_ups_offer_stage", true);
            if ($stage === "") {
                $stage = "awareness";
            }
            upsellio_followup_queue_message($offer_id, $template_id, $stage);
            if (function_exists("upsellio_followup_send_due_queue")) {
                upsellio_followup_send_due_queue();
            }
            upsellio_crm_app_append_entity_log("offer", $offer_id, "followup_manual_send", "Ręcznie wysłano follow-up.", ["template_id" => $template_id]);
        }
    } elseif ($action === "save_task") {
        $task_id = isset($_POST["task_id"]) ? (int) wp_unslash($_POST["task_id"]) : 0;
        $title = isset($_POST["task_title"]) ? sanitize_text_field(wp_unslash($_POST["task_title"])) : "";
        if ($title === "" || !post_type_exists("lead_task")) {
            return;
        }
        $offer_id = isset($_POST["task_offer_id"]) ? (int) wp_unslash($_POST["task_offer_id"]) : 0;
        $lead_id = isset($_POST["task_lead_id"]) ? (int) wp_unslash($_POST["task_lead_id"]) : 0;
        $owner_id = isset($_POST["task_owner_id"]) ? (int) wp_unslash($_POST["task_owner_id"]) : get_current_user_id();
        if ($owner_id <= 0) {
            $owner_id = get_current_user_id();
        }
        $status = isset($_POST["task_status"]) ? sanitize_key(wp_unslash($_POST["task_status"])) : "open";
        if (!in_array($status, ["open", "in_progress", "waiting", "done", "cancelled"], true)) {
            $status = "open";
        }
        $note = isset($_POST["task_note"]) ? sanitize_textarea_field(wp_unslash($_POST["task_note"])) : "";
        $due_at_raw = isset($_POST["task_due_at"]) ? sanitize_text_field(wp_unslash($_POST["task_due_at"])) : "";
        $due_at_ts = $due_at_raw !== "" ? strtotime($due_at_raw) : false;
        $duration_minutes = isset($_POST["task_duration_minutes"]) ? max(15, (int) wp_unslash($_POST["task_duration_minutes"])) : 60;
        $impact_score = isset($_POST["task_impact_score"]) ? max(1, min(100, (int) wp_unslash($_POST["task_impact_score"]))) : 50;
        $close_probability = isset($_POST["task_close_probability"]) ? max(1, min(100, (int) wp_unslash($_POST["task_close_probability"]))) : 50;
        if ($task_id > 0 && get_post_type($task_id) === "lead_task") {
            if (!current_user_can("edit_post", $task_id)) {
                return;
            }
            wp_update_post([
                "ID" => $task_id,
                "post_title" => $title,
                "post_author" => $owner_id,
            ]);
        } else {
            $task_id = (int) wp_insert_post([
                "post_type" => "lead_task",
                "post_status" => "publish",
                "post_title" => $title,
                "post_author" => $owner_id,
            ]);
        }
        if ($task_id > 0) {
            update_post_meta($task_id, "_upsellio_task_offer_id", $offer_id);
            update_post_meta($task_id, "_upsellio_task_lead_id", $lead_id);
            update_post_meta($task_id, "_upsellio_task_status", $status);
            update_post_meta($task_id, "_upsellio_task_note", $note);
            update_post_meta($task_id, "_upsellio_task_duration_minutes", $duration_minutes);
            if ($due_at_ts !== false && $due_at_ts > 0) {
                update_post_meta($task_id, "_upsellio_task_due_at", (int) $due_at_ts);
            } else {
                delete_post_meta($task_id, "_upsellio_task_due_at");
            }
            update_post_meta($task_id, "_upsellio_task_impact_score", $impact_score);
            update_post_meta($task_id, "_upsellio_task_close_probability", $close_probability);
            if (function_exists("upsellio_automation_refresh_task_priority_meta")) {
                upsellio_automation_refresh_task_priority_meta($task_id);
            }
            upsellio_crm_app_append_entity_log("task", $task_id, "task_saved", "Zapisano task.", ["status" => $status]);
        }
    } elseif ($action === "complete_task") {
        $task_id = isset($_POST["task_id"]) ? (int) wp_unslash($_POST["task_id"]) : 0;
        if ($task_id > 0 && get_post_type($task_id) === "lead_task" && current_user_can("edit_post", $task_id)) {
            $status = isset($_POST["task_status"]) ? sanitize_key(wp_unslash($_POST["task_status"])) : "done";
            if (!in_array($status, ["open", "in_progress", "waiting", "done", "cancelled"], true)) {
                $status = "done";
            }
            update_post_meta($task_id, "_upsellio_task_status", $status);
            upsellio_crm_app_append_entity_log("task", $task_id, "task_status_changed", "Zmieniono status taska.", ["status" => $status]);
        }
    } elseif ($action === "delete_task") {
        $task_id = isset($_POST["task_id"]) ? (int) wp_unslash($_POST["task_id"]) : 0;
        if ($task_id > 0 && get_post_type($task_id) === "lead_task" && current_user_can("delete_post", $task_id)) {
            upsellio_crm_app_append_entity_log("task", $task_id, "task_deleted", "Usunięto task.", []);
            wp_delete_post($task_id, true);
        }
    } elseif ($action === "import_leads_csv") {
        if (!current_user_can("manage_options") || !post_type_exists("crm_lead")) {
            return;
        }
        if (!isset($_FILES["leads_csv"]) || !is_array($_FILES["leads_csv"])) {
            return;
        }
        $tmp = (string) ($_FILES["leads_csv"]["tmp_name"] ?? "");
        if ($tmp === "" || !file_exists($tmp)) {
            return;
        }
        $handle = fopen($tmp, "r");
        if ($handle) {
            $row = 0;
            while (($cols = fgetcsv($handle, 0, ",")) !== false) {
                $row++;
                if ($row === 1) {
                    continue;
                }
                $name = sanitize_text_field((string) ($cols[0] ?? ""));
                if ($name === "") {
                    continue;
                }
                $lead_id = (int) wp_insert_post([
                    "post_type" => "crm_lead",
                    "post_status" => "publish",
                    "post_title" => $name,
                ]);
                if ($lead_id > 0) {
                    update_post_meta($lead_id, "_ups_lead_email", sanitize_email((string) ($cols[1] ?? "")));
                    update_post_meta($lead_id, "_ups_lead_phone", sanitize_text_field((string) ($cols[2] ?? "")));
                    update_post_meta($lead_id, "_ups_lead_source", sanitize_text_field((string) ($cols[3] ?? "csv")));
                    update_post_meta($lead_id, "_ups_lead_type", sanitize_key((string) ($cols[4] ?? "inbound")));
                    update_post_meta($lead_id, "_ups_lead_qualification_status", sanitize_key((string) ($cols[5] ?? "new")));
                }
            }
            fclose($handle);
        }
    } elseif ($action === "save_quick_settings") {
        $is_admin = current_user_can("manage_options");
        if (upsellio_crm_app_user_can_save_quick_settings()) {
            update_option("ups_contract_reminder_first_days", isset($_POST["contract_reminder_first_days"]) ? max(1, (int) wp_unslash($_POST["contract_reminder_first_days"])) : 3);
            update_option("ups_contract_reminder_second_days", isset($_POST["contract_reminder_second_days"]) ? max(2, (int) wp_unslash($_POST["contract_reminder_second_days"])) : 7);
            update_option("ups_followup_cooldown_hours", isset($_POST["followup_cooldown_hours"]) ? max(0, (int) wp_unslash($_POST["followup_cooldown_hours"])) : 24);
            update_option("ups_followup_max_per_offer", isset($_POST["followup_max_per_offer"]) ? max(1, (int) wp_unslash($_POST["followup_max_per_offer"])) : 5);
            $settings_tab_save = isset($_POST["settings_tab"]) ? sanitize_key(wp_unslash($_POST["settings_tab"])) : "";
            if ($settings_tab_save === "general") {
                if ($is_admin) {
                    $anthropic_key_in = isset($_POST["ups_anthropic_api_key"]) ? trim(sanitize_text_field(wp_unslash($_POST["ups_anthropic_api_key"]))) : "";
                    if ($anthropic_key_in !== "") {
                        update_option("ups_anthropic_api_key", $anthropic_key_in);
                    }
                }
                update_option("ups_anthropic_inbound_enabled", isset($_POST["ups_anthropic_inbound_enabled"]) ? "1" : "0");
                update_option("ups_anthropic_wp_lead_form_enabled", isset($_POST["ups_anthropic_wp_lead_form_enabled"]) ? "1" : "0");
                update_option("ups_anthropic_inbox_draft_enabled", isset($_POST["ups_anthropic_inbox_draft_enabled"]) ? "1" : "0");
                update_option("ups_anthropic_inbox_auto_followup_enabled", isset($_POST["ups_anthropic_inbox_auto_followup_enabled"]) ? "1" : "0");
                update_option("ups_anthropic_inbox_auto_followup_dry_run", isset($_POST["ups_anthropic_inbox_auto_followup_dry_run"]) ? "1" : "0");
                if (isset($_POST["ups_anthropic_inbox_auto_followup_hours"])) {
                    update_option(
                        "ups_anthropic_inbox_auto_followup_hours",
                        max(6, min(168, (int) wp_unslash($_POST["ups_anthropic_inbox_auto_followup_hours"])))
                    );
                }
                if ((string) get_option("ups_anthropic_inbox_auto_followup_enabled", "0") !== "1") {
                    wp_clear_scheduled_hook("upsellio_crm_ai_inbox_followup_hourly");
                } elseif (function_exists("upsellio_crm_ai_register_inbox_followup_cron")) {
                    upsellio_crm_ai_register_inbox_followup_cron();
                }
                if (isset($_POST["ups_anthropic_model"]) && trim((string) wp_unslash($_POST["ups_anthropic_model"])) !== "") {
                    update_option("ups_anthropic_model", sanitize_text_field(wp_unslash($_POST["ups_anthropic_model"])));
                }
            }

            if ($settings_tab_save === "ai") {
                if (isset($_POST["ups_ai_company_context"])) {
                    $ctx = upsellio_crm_app_sanitize_large_text_option($_POST["ups_ai_company_context"]);
                    update_option("ups_ai_company_context", $ctx, false);
                    update_option("ups_anthropic_company_context", $ctx, false);
                }
                foreach ([
                    "ups_ai_context_scoring",
                    "ups_ai_context_draft",
                    "ups_ai_context_followup",
                    "ups_ai_context_blog",
                    "ups_ai_context_offer_fill",
                ] as $ctx_field) {
                    if (isset($_POST[$ctx_field])) {
                        update_option($ctx_field, upsellio_crm_app_sanitize_large_text_option($_POST[$ctx_field]), false);
                    }
                }
                foreach ([
                    "ups_ai_prompt_lead_scoring",
                    "ups_ai_prompt_inbox_draft",
                    "ups_ai_prompt_followup",
                ] as $ai_prompt_field) {
                    if (isset($_POST[$ai_prompt_field])) {
                        update_option($ai_prompt_field, upsellio_crm_app_sanitize_large_text_option($_POST[$ai_prompt_field]), false);
                    }
                }
                if (isset($_POST["ups_anthropic_prompt_offer_description"])) {
                    update_option(
                        "ups_anthropic_prompt_offer_description",
                        upsellio_crm_app_sanitize_large_text_option($_POST["ups_anthropic_prompt_offer_description"]),
                        false
                    );
                }
                if (isset($_POST["ups_ai_prompt_blog_post"])) {
                    update_option("ups_ai_prompt_blog_post", upsellio_crm_app_sanitize_large_text_option($_POST["ups_ai_prompt_blog_post"]), false);
                }
                if (isset($_POST["ups_ai_prompt_blog_seo_system"])) {
                    update_option("ups_ai_prompt_blog_seo_system", upsellio_crm_app_sanitize_large_text_option($_POST["ups_ai_prompt_blog_seo_system"]), false);
                }
                if (isset($_POST["ups_ai_blog_seo_campaign_default"])) {
                    update_option("ups_ai_blog_seo_campaign_default", upsellio_crm_app_sanitize_large_text_option($_POST["ups_ai_blog_seo_campaign_default"]), false);
                }
                if (isset($_POST["ups_ai_blog_seo_temperature"])) {
                    update_option(
                        "ups_ai_blog_seo_temperature",
                        max(0.0, min(1.2, (float) wp_unslash($_POST["ups_ai_blog_seo_temperature"]))),
                        false
                    );
                }
                if (isset($_POST["ups_ai_blog_seo_max_tokens"])) {
                    update_option(
                        "ups_ai_blog_seo_max_tokens",
                        max(800, min(12000, (int) wp_unslash($_POST["ups_ai_blog_seo_max_tokens"]))),
                        false
                    );
                }
                update_option("ups_blog_bot_enabled", isset($_POST["ups_blog_bot_enabled"]) ? "1" : "0", false);
                if (isset($_POST["ups_blog_bot_model"])) {
                    update_option("ups_blog_bot_model", sanitize_text_field(wp_unslash($_POST["ups_blog_bot_model"])), false);
                }
                if (isset($_POST["ups_blog_bot_schedule"])) {
                    $sch = sanitize_key(wp_unslash($_POST["ups_blog_bot_schedule"]));
                    update_option(
                        "ups_blog_bot_schedule",
                        in_array($sch, ["daily", "biweekly", "weekly", "monthly"], true) ? $sch : "weekly",
                        false
                    );
                }
                if (isset($_POST["ups_blog_bot_notify_email"])) {
                    update_option("ups_blog_bot_notify_email", sanitize_email(wp_unslash($_POST["ups_blog_bot_notify_email"])), false);
                }
                if (isset($_POST["ups_blog_bot_post_author"])) {
                    update_option("ups_blog_bot_post_author", max(1, (int) wp_unslash($_POST["ups_blog_bot_post_author"])), false);
                }
                if (isset($_POST["ups_blog_bot_target_length"])) {
                    update_option("ups_blog_bot_target_length", max(400, (int) wp_unslash($_POST["ups_blog_bot_target_length"])), false);
                }
                if (isset($_POST["ups_blog_bot_category"])) {
                    update_option("ups_blog_bot_category", max(0, (int) wp_unslash($_POST["ups_blog_bot_category"])), false);
                }
                if (isset($_POST["ups_blog_bot_keywords_queue"])) {
                    update_option("ups_blog_bot_keywords_queue", upsellio_crm_app_sanitize_large_text_option($_POST["ups_blog_bot_keywords_queue"]), false);
                }
                if (function_exists("upsellio_blog_bot_ensure_cron")) {
                    upsellio_blog_bot_ensure_cron();
                }
            }

            if (isset($_POST["ups_sales_intent_weight"])) {
                update_option("ups_sales_intent_weight", max(1, (int) wp_unslash($_POST["ups_sales_intent_weight"])));
            }
            if (isset($_POST["ups_sales_fit_weight"])) {
                update_option("ups_sales_fit_weight", max(1, (int) wp_unslash($_POST["ups_sales_fit_weight"])));
            }
            if (isset($_POST["ups_sales_hot_index_threshold"])) {
                update_option("ups_sales_hot_index_threshold", max(1, (int) wp_unslash($_POST["ups_sales_hot_index_threshold"])));
            }
            if (isset($_POST["ups_sales_playbook_awareness_delay_h"])) {
                update_option("ups_sales_playbook_awareness_delay_h", max(0, (int) wp_unslash($_POST["ups_sales_playbook_awareness_delay_h"])));
            }
            if (isset($_POST["ups_sales_playbook_consideration_delay_h"])) {
                update_option("ups_sales_playbook_consideration_delay_h", max(0, (int) wp_unslash($_POST["ups_sales_playbook_consideration_delay_h"])));
            }
            if (isset($_POST["ups_sales_playbook_decision_delay_h"])) {
                update_option("ups_sales_playbook_decision_delay_h", max(0, (int) wp_unslash($_POST["ups_sales_playbook_decision_delay_h"])));
            }
            update_option("ups_sales_channel_email_enabled", isset($_POST["ups_sales_channel_email_enabled"]) ? "1" : "0");
            update_option("ups_sales_spf_ok", isset($_POST["ups_sales_spf_ok"]) ? "1" : "0");
            update_option("ups_sales_dkim_ok", isset($_POST["ups_sales_dkim_ok"]) ? "1" : "0");
            update_option("ups_sales_dmarc_ok", isset($_POST["ups_sales_dmarc_ok"]) ? "1" : "0");
            if (isset($_POST["ups_sales_warmup_notes"])) {
                update_option("ups_sales_warmup_notes", sanitize_textarea_field(wp_unslash($_POST["ups_sales_warmup_notes"])));
            }
            if (isset($_POST["ups_sales_fit_ideal_industries"])) {
                update_option("ups_sales_fit_ideal_industries", sanitize_textarea_field(wp_unslash($_POST["ups_sales_fit_ideal_industries"])), false);
            }
            if (isset($_POST["ups_sales_fit_ideal_budget_min_pln"])) {
                update_option("ups_sales_fit_ideal_budget_min_pln", max(0, (float) wp_unslash($_POST["ups_sales_fit_ideal_budget_min_pln"])), false);
            }

            if ($settings_tab_save === "mailbox" && $is_admin) {
                if (isset($_POST["ups_followup_from_name"])) {
                    update_option("ups_followup_from_name", sanitize_text_field(wp_unslash($_POST["ups_followup_from_name"])));
                }
                if (isset($_POST["ups_followup_from_email"])) {
                    update_option("ups_followup_from_email", sanitize_email(wp_unslash($_POST["ups_followup_from_email"])));
                }
                if (isset($_POST["ups_followup_inbound_secret"])) {
                    update_option("ups_followup_inbound_secret", sanitize_text_field(wp_unslash($_POST["ups_followup_inbound_secret"])));
                }
                update_option("ups_followup_smtp_enabled", isset($_POST["ups_followup_smtp_enabled"]) ? "1" : "0");
                if (isset($_POST["ups_followup_smtp_host"])) {
                    update_option("ups_followup_smtp_host", sanitize_text_field(wp_unslash($_POST["ups_followup_smtp_host"])));
                }
                if (isset($_POST["ups_followup_smtp_port"])) {
                    update_option("ups_followup_smtp_port", max(1, (int) wp_unslash($_POST["ups_followup_smtp_port"])));
                }
                if (isset($_POST["ups_followup_smtp_encryption"])) {
                    $senc = sanitize_key(wp_unslash($_POST["ups_followup_smtp_encryption"]));
                    update_option("ups_followup_smtp_encryption", in_array($senc, ["ssl", "tls", "none"], true) ? $senc : "tls");
                }
                if (isset($_POST["ups_followup_smtp_username"])) {
                    update_option("ups_followup_smtp_username", sanitize_text_field(wp_unslash($_POST["ups_followup_smtp_username"])));
                }
                if (isset($_POST["ups_followup_smtp_password"])) {
                    $raw_smtp_pw = (string) wp_unslash($_POST["ups_followup_smtp_password"]);
                    if ($raw_smtp_pw !== "" && function_exists("upsellio_followup_store_smtp_password")) {
                        upsellio_followup_store_smtp_password($raw_smtp_pw);
                    }
                }
                update_option("ups_followup_mailbox_enabled", isset($_POST["ups_followup_mailbox_enabled"]) ? "1" : "0");
                if (isset($_POST["ups_followup_mailbox_host"])) {
                    update_option("ups_followup_mailbox_host", sanitize_text_field(wp_unslash($_POST["ups_followup_mailbox_host"])));
                }
                if (isset($_POST["ups_followup_mailbox_port"])) {
                    update_option("ups_followup_mailbox_port", max(1, (int) wp_unslash($_POST["ups_followup_mailbox_port"])));
                }
                if (isset($_POST["ups_followup_mailbox_encryption"])) {
                    $enc = sanitize_key(wp_unslash($_POST["ups_followup_mailbox_encryption"]));
                    update_option("ups_followup_mailbox_encryption", in_array($enc, ["ssl", "tls", "none"], true) ? $enc : "ssl");
                }
                if (isset($_POST["ups_followup_mailbox_username"])) {
                    update_option("ups_followup_mailbox_username", sanitize_text_field(wp_unslash($_POST["ups_followup_mailbox_username"])));
                }
                if (isset($_POST["ups_followup_mailbox_password"])) {
                    $raw_password = (string) wp_unslash($_POST["ups_followup_mailbox_password"]);
                    if ($raw_password !== "" && function_exists("upsellio_followup_store_mailbox_password")) {
                        upsellio_followup_store_mailbox_password($raw_password);
                    }
                }
                if (isset($_POST["ups_followup_mailbox_folder"])) {
                    update_option("ups_followup_mailbox_folder", sanitize_text_field(wp_unslash($_POST["ups_followup_mailbox_folder"])));
                }
                update_option("ups_followup_mailbox_ssl_novalidate", isset($_POST["ups_followup_mailbox_ssl_novalidate"]) ? "1" : "0");
                if (isset($_POST["ups_offer_email_subject"])) {
                    update_option("ups_offer_email_subject", sanitize_text_field(wp_unslash($_POST["ups_offer_email_subject"])));
                }
                if (isset($_POST["ups_offer_email_html"])) {
                    update_option("ups_offer_email_html", wp_kses_post((string) wp_unslash($_POST["ups_offer_email_html"])));
                }
                if (isset($_POST["ups_offer_email_css"])) {
                    update_option("ups_offer_email_css", wp_strip_all_tags((string) wp_unslash($_POST["ups_offer_email_css"])));
                }
                if (isset($_POST["ups_followup_hint_awareness"])) {
                    update_option("ups_followup_hint_awareness", sanitize_textarea_field(wp_unslash($_POST["ups_followup_hint_awareness"])));
                }
                if (isset($_POST["ups_followup_hint_consideration"])) {
                    update_option("ups_followup_hint_consideration", sanitize_textarea_field(wp_unslash($_POST["ups_followup_hint_consideration"])));
                }
                if (isset($_POST["ups_followup_hint_decision"])) {
                    update_option("ups_followup_hint_decision", sanitize_textarea_field(wp_unslash($_POST["ups_followup_hint_decision"])));
                }
                if (isset($_POST["ups_crm_email_footer_html"])) {
                    update_option("ups_crm_email_footer_html", wp_kses_post((string) wp_unslash($_POST["ups_crm_email_footer_html"])));
                }
                if (isset($_POST["ups_crm_email_footer_css"])) {
                    update_option("ups_crm_email_footer_css", wp_strip_all_tags((string) wp_unslash($_POST["ups_crm_email_footer_css"])));
                }
                if (isset($_POST["ups_followup_mailbox_test"]) && function_exists("upsellio_followup_test_mailbox_connection")) {
                    $test_result = upsellio_followup_test_mailbox_connection();
                    set_transient("ups_crm_mailbox_test_" . get_current_user_id(), $test_result, 120);
                }
                if (isset($_POST["ups_followup_smtp_test"]) && function_exists("upsellio_followup_test_smtp_connection")) {
                    $smtp_test = upsellio_followup_test_smtp_connection();
                    set_transient("ups_crm_smtp_test_" . get_current_user_id(), $smtp_test, 120);
                }
                update_option("ups_mailbox_log_verbose", isset($_POST["ups_mailbox_log_verbose"]) ? "1" : "0");
            }

            if (isset($_POST["ups_offer_stage_consideration_views"])) {
                update_option("ups_offer_stage_consideration_views", max(1, (int) wp_unslash($_POST["ups_offer_stage_consideration_views"])));
            }
            if (isset($_POST["ups_offer_stage_decision_views"])) {
                update_option("ups_offer_stage_decision_views", max(1, (int) wp_unslash($_POST["ups_offer_stage_decision_views"])));
            }
            update_option("ups_offer_stage_decision_require_cta", isset($_POST["ups_offer_stage_decision_require_cta"]) ? "1" : "0");
            if (isset($_POST["ups_offer_score_consideration"])) {
                update_option("ups_offer_score_consideration", max(1, (int) wp_unslash($_POST["ups_offer_score_consideration"])));
            }
            if (isset($_POST["ups_offer_score_decision"])) {
                update_option("ups_offer_score_decision", max(1, (int) wp_unslash($_POST["ups_offer_score_decision"])));
            }
            if (isset($_POST["ups_offer_score_hot"])) {
                update_option("ups_offer_score_hot", max(1, (int) wp_unslash($_POST["ups_offer_score_hot"])));
            }
            if (isset($_POST["ups_offer_score_consideration_pricing_seconds"])) {
                update_option("ups_offer_score_consideration_pricing_seconds", max(0, (int) wp_unslash($_POST["ups_offer_score_consideration_pricing_seconds"])));
            }
            if (isset($_POST["ups_offer_score_decision_pricing_seconds"])) {
                update_option("ups_offer_score_decision_pricing_seconds", max(0, (int) wp_unslash($_POST["ups_offer_score_decision_pricing_seconds"])));
            }
            if (isset($_POST["ups_offer_score_hot_pricing_seconds"])) {
                update_option("ups_offer_score_hot_pricing_seconds", max(0, (int) wp_unslash($_POST["ups_offer_score_hot_pricing_seconds"])));
            }
            if (isset($_POST["ups_automation_sla_consideration_days"])) {
                update_option("ups_automation_sla_consideration_days", max(1, (int) wp_unslash($_POST["ups_automation_sla_consideration_days"])));
            }
            if (isset($_POST["ups_automation_alert_drop_win_rate_pct"])) {
                update_option("ups_automation_alert_drop_win_rate_pct", max(1, (int) wp_unslash($_POST["ups_automation_alert_drop_win_rate_pct"])));
            }
            if (isset($_POST["ups_automation_alert_lost_spike_pct"])) {
                update_option("ups_automation_alert_lost_spike_pct", max(1, (int) wp_unslash($_POST["ups_automation_alert_lost_spike_pct"])));
            }
            if (isset($_POST["ups_automation_cold_followup_days"])) {
                update_option("ups_automation_cold_followup_days", max(1, (int) wp_unslash($_POST["ups_automation_cold_followup_days"])));
            }
            if (isset($_POST["ups_automation_ab_min_sample"])) {
                update_option("ups_automation_ab_min_sample", max(5, (int) wp_unslash($_POST["ups_automation_ab_min_sample"])));
            }
            if (isset($_POST["ups_automation_ab_min_lift_pct"])) {
                update_option("ups_automation_ab_min_lift_pct", max(1, (int) wp_unslash($_POST["ups_automation_ab_min_lift_pct"])));
            }
            update_option("ups_automation_ga4_sync_enabled", isset($_POST["ups_automation_ga4_sync_enabled"]) ? "1" : "0");
            if (isset($_POST["ups_hybrid_weight_source"])) {
                update_option("ups_hybrid_weight_source", max(1, (int) wp_unslash($_POST["ups_hybrid_weight_source"])));
            }
            if (isset($_POST["ups_hybrid_weight_fit"])) {
                update_option("ups_hybrid_weight_fit", max(1, (int) wp_unslash($_POST["ups_hybrid_weight_fit"])));
            }
            if (isset($_POST["ups_hybrid_weight_intent"])) {
                update_option("ups_hybrid_weight_intent", max(1, (int) wp_unslash($_POST["ups_hybrid_weight_intent"])));
            }
            if (isset($_POST["ups_hybrid_weight_timing"])) {
                update_option("ups_hybrid_weight_timing", max(1, (int) wp_unslash($_POST["ups_hybrid_weight_timing"])));
            }
            if (isset($_POST["ups_hybrid_weight_value"])) {
                update_option("ups_hybrid_weight_value", max(1, (int) wp_unslash($_POST["ups_hybrid_weight_value"])));
            }
            if (isset($_POST["ups_crm_marketing_spend_csv"]) && function_exists("upsellio_sales_engine_parse_campaign_cost_csv_from_text") && function_exists("upsellio_sales_engine_get_campaign_costs") && function_exists("upsellio_sales_engine_save_campaign_costs")) {
                $raw_csv = (string) wp_unslash($_POST["ups_crm_marketing_spend_csv"]);
                if (trim($raw_csv) !== "") {
                    $parsed = upsellio_sales_engine_parse_campaign_cost_csv_from_text($raw_csv);
                    if (!empty($parsed)) {
                        $existing = upsellio_sales_engine_get_campaign_costs();
                        $merged = is_array($existing) ? array_merge($existing, $parsed) : $parsed;
                        upsellio_sales_engine_save_campaign_costs($merged);
                    }
                }
            }
            if (function_exists("upsellio_automation_get_pipeline_sla_definitions") && (
                isset($_POST["crm_sla_new_lead_hours"]) || isset($_POST["crm_sla_qualification_hours"]) || isset($_POST["crm_sla_offer_hours"]) || isset($_POST["crm_sla_negotiation_hours"])
            )) {
                $defs = upsellio_automation_get_pipeline_sla_definitions();
                if (isset($defs["new_lead"], $_POST["crm_sla_new_lead_hours"])) {
                    $defs["new_lead"]["hours"] = max(1, (int) wp_unslash($_POST["crm_sla_new_lead_hours"]));
                }
                if (isset($defs["qualification"], $_POST["crm_sla_qualification_hours"])) {
                    $defs["qualification"]["hours"] = max(1, (int) wp_unslash($_POST["crm_sla_qualification_hours"]));
                }
                if (isset($defs["offer"], $_POST["crm_sla_offer_hours"])) {
                    $defs["offer"]["hours"] = max(1, (int) wp_unslash($_POST["crm_sla_offer_hours"]));
                }
                if (isset($defs["negotiation"], $_POST["crm_sla_negotiation_hours"])) {
                    $defs["negotiation"]["hours"] = max(1, (int) wp_unslash($_POST["crm_sla_negotiation_hours"]));
                }
                update_option("ups_crm_pipeline_sla_config", $defs, false);
            }
            for ($i = 1; $i <= 5; $i++) {
                if (isset($_POST["ups_prospect_subject_step_" . $i])) {
                    update_option("ups_prospect_subject_step_" . $i, sanitize_text_field(wp_unslash($_POST["ups_prospect_subject_step_" . $i])));
                }
                if (isset($_POST["ups_prospect_body_step_" . $i])) {
                    update_option("ups_prospect_body_step_" . $i, sanitize_textarea_field(wp_unslash($_POST["ups_prospect_body_step_" . $i])));
                }
            }
        }
    }
    $redirect_view = isset($_POST["crm_view"]) ? sanitize_key(wp_unslash($_POST["crm_view"])) : "dashboard";
    $redirect_client = isset($_POST["client_id"]) ? (int) wp_unslash($_POST["client_id"]) : 0;
    $redirect_task = isset($_POST["task_id"]) ? (int) wp_unslash($_POST["task_id"]) : 0;
    $redirect_url = add_query_arg(["view" => $redirect_view], home_url("/crm-app/"));
    $redirect_settings_tab = isset($_POST["settings_tab"]) ? sanitize_key(wp_unslash($_POST["settings_tab"])) : "";
    if ($redirect_view === "settings" && $redirect_settings_tab !== "") {
        $redirect_url = add_query_arg(["settings_tab" => $redirect_settings_tab], $redirect_url);
    }
    $redirect_task_tab = isset($_POST["task_tab"]) ? sanitize_key(wp_unslash($_POST["task_tab"])) : "";
    if ($redirect_view === "tasks" && $redirect_task_tab !== "" && in_array($redirect_task_tab, ["all", "today", "tomorrow", "overdue", "week"], true)) {
        $redirect_url = add_query_arg(["task_tab" => $redirect_task_tab], $redirect_url);
    }
    if ($redirect_view === "tasks" && $redirect_task_tab === "week" && isset($_POST["week_offset"])) {
        $wo = (int) wp_unslash($_POST["week_offset"]);
        if ($wo !== 0) {
            $redirect_url = add_query_arg(["week_offset" => $wo], $redirect_url);
        }
    }
    if ($redirect_client > 0 && $redirect_view === "client-edit") {
        $redirect_url = add_query_arg(["client_id" => $redirect_client], $redirect_url);
    }
    if ($redirect_task > 0 && $redirect_view === "tasks") {
        $redirect_url = add_query_arg(["task_id" => $redirect_task], $redirect_url);
    }
    $template_studio_tab = isset($_POST["template_studio_tab"]) ? sanitize_key(wp_unslash($_POST["template_studio_tab"])) : "";
    if ($redirect_view === "template-studio" && $template_studio_tab !== "") {
        $redirect_url = add_query_arg(["tab" => $template_studio_tab], $redirect_url);
    }
    if ($redirect_view === "template-studio" && $template_studio_tab === "offer") {
        $just_saved_layout = get_transient("ups_crm_offer_layout_just_saved_" . get_current_user_id());
        if ((int) $just_saved_layout > 0) {
            delete_transient("ups_crm_offer_layout_just_saved_" . get_current_user_id());
            $redirect_url = add_query_arg(["edit_offer_layout" => (int) $just_saved_layout], $redirect_url);
        }
    }
    wp_safe_redirect($redirect_url);
    exit;
}

function upsellio_crm_app_ajax_move_offer_pipeline()
{
    if (!upsellio_crm_app_user_can_access()) {
        wp_send_json_error(["message" => "forbidden"], 403);
    }
    $nonce = isset($_POST["nonce"]) ? sanitize_text_field(wp_unslash($_POST["nonce"])) : "";
    if (!wp_verify_nonce($nonce, "ups_crm_app_action")) {
        wp_send_json_error(["message" => "bad_nonce"], 403);
    }
    $offer_id = isset($_POST["offer_id"]) ? (int) wp_unslash($_POST["offer_id"]) : 0;
    $stage = isset($_POST["stage"]) ? sanitize_key(wp_unslash($_POST["stage"])) : "";
    if ($offer_id <= 0 || get_post_type($offer_id) !== "crm_offer") {
        wp_send_json_error(["message" => "invalid_offer"], 400);
    }
    if (!current_user_can("edit_post", $offer_id)) {
        wp_send_json_error(["message" => "forbidden_offer"], 403);
    }
    if (!in_array($stage, ["awareness", "consideration", "decision", "offer_sent", "won", "lost"], true)) {
        wp_send_json_error(["message" => "invalid_stage"], 400);
    }

    $loss_reason = isset($_POST["loss_reason"]) ? sanitize_key(wp_unslash($_POST["loss_reason"])) : "";
    $loss_reason_note = isset($_POST["loss_reason_note"]) ? sanitize_text_field(wp_unslash($_POST["loss_reason_note"])) : "";
    if ($stage === "lost" && $loss_reason === "") {
        wp_send_json_error(["message" => "loss_reason_required"], 400);
    }

    $old_status = (string) get_post_meta($offer_id, "_ups_offer_status", true);
    $old_stage = (string) get_post_meta($offer_id, "_ups_offer_stage", true);
    if ($stage === "offer_sent") {
        if ($old_status !== "sent") {
            if ((string) get_post_meta($offer_id, "_ups_offer_first_sent_at", true) === "") {
                update_post_meta($offer_id, "_ups_offer_first_sent_at", current_time("mysql"));
            }
            update_post_meta($offer_id, "_ups_offer_status", "sent");
            update_post_meta($offer_id, "_ups_offer_stage", "consideration");
            if (function_exists("upsellio_offer_queue_builtin_sent_reminders")) {
                upsellio_offer_queue_builtin_sent_reminders($offer_id);
            }
            do_action("upsellio_offer_status_changed", $offer_id, "sent", $old_status !== "" ? $old_status : "open");
        } else {
            update_post_meta($offer_id, "_ups_offer_stage", "consideration");
        }
    } elseif ($stage === "won" || $stage === "lost") {
        update_post_meta($offer_id, "_ups_offer_status", $stage);
        update_post_meta($offer_id, "_ups_offer_stage", "decision");
        delete_post_meta($offer_id, "_ups_offer_sla_active_alert");
        if ($old_status !== $stage) {
            do_action("upsellio_offer_status_changed", $offer_id, $stage, $old_status);
        }
        if ($stage === "won") {
            $won_val = (float) get_post_meta($offer_id, "_ups_offer_won_value", true);
            if ($won_val <= 0 && function_exists("upsellio_sales_engine_parse_amount")) {
                $price_raw = (string) get_post_meta($offer_id, "_ups_offer_price", true);
                $parsed = upsellio_sales_engine_parse_amount($price_raw);
                if ($parsed > 0) {
                    update_post_meta($offer_id, "_ups_offer_won_value", $parsed);
                }
            }
        }
        if ($stage === "lost") {
            update_post_meta($offer_id, "_ups_offer_loss_reason", $loss_reason);
            update_post_meta($offer_id, "_ups_offer_loss_reason_note", $loss_reason_note);
        }
    } else {
        update_post_meta($offer_id, "_ups_offer_stage", $stage);
        if (function_exists("upsellio_automation_sync_offer_pipeline_sla_from_marketing_stage")) {
            upsellio_automation_sync_offer_pipeline_sla_from_marketing_stage($offer_id, $stage);
        }
        if ($old_status === "won" || $old_status === "lost") {
            update_post_meta($offer_id, "_ups_offer_status", "open");
            do_action("upsellio_offer_status_changed", $offer_id, "open", $old_status);
        } elseif ($old_status === "sent") {
            update_post_meta($offer_id, "_ups_offer_status", "open");
            do_action("upsellio_offer_status_changed", $offer_id, "open", "sent");
        }
    }
    if (function_exists("upsellio_offer_add_timeline_event") && $old_stage !== $stage) {
        upsellio_offer_add_timeline_event($offer_id, "pipeline_moved", "Przeniesiono oferte w pipeline do: " . $stage);
    }
    upsellio_crm_app_append_entity_log("offer", $offer_id, "pipeline_moved", "Przeniesiono deal w pipeline.", [
        "from_stage" => $old_stage,
        "to_stage" => $stage,
    ]);

    if (function_exists("upsellio_sales_engine_refresh_hybrid_deal_scores")) {
        $intent = (int) get_post_meta($offer_id, "_ups_offer_intent_score", true);
        $fit = (int) get_post_meta($offer_id, "_ups_offer_fit_score", true);
        $st_h = (string) get_post_meta($offer_id, "_ups_offer_stage", true);
        if ($st_h === "") {
            $st_h = "awareness";
        }
        upsellio_sales_engine_refresh_hybrid_deal_scores(
            $offer_id,
            $intent > 0 ? $intent : (int) get_post_meta($offer_id, "_ups_offer_score", true),
            $fit > 0 ? $fit : 40,
            $st_h
        );
    }

    wp_send_json_success(["ok" => true]);
}
add_action("wp_ajax_upsellio_crm_move_offer_pipeline", "upsellio_crm_app_ajax_move_offer_pipeline");

function upsellio_crm_app_ajax_schedule_task()
{
    if (!upsellio_crm_app_user_can_access()) {
        wp_send_json_error(["message" => "forbidden"], 403);
    }
    $nonce = isset($_POST["nonce"]) ? sanitize_text_field(wp_unslash($_POST["nonce"])) : "";
    if (!wp_verify_nonce($nonce, "ups_crm_app_action")) {
        wp_send_json_error(["message" => "bad_nonce"], 403);
    }
    $task_id = isset($_POST["task_id"]) ? (int) wp_unslash($_POST["task_id"]) : 0;
    $start_at = isset($_POST["start_at"]) ? sanitize_text_field(wp_unslash($_POST["start_at"])) : "";
    $duration_minutes = isset($_POST["duration_minutes"]) ? max(15, (int) wp_unslash($_POST["duration_minutes"])) : 60;
    if ($task_id <= 0 || get_post_type($task_id) !== "lead_task") {
        wp_send_json_error(["message" => "invalid_task"], 400);
    }
    if (!current_user_can("edit_post", $task_id)) {
        wp_send_json_error(["message" => "forbidden_task"], 403);
    }
    $ts = strtotime($start_at);
    if ($ts === false || $ts <= 0) {
        wp_send_json_error(["message" => "invalid_start_at"], 400);
    }
    update_post_meta($task_id, "_upsellio_task_due_at", (int) $ts);
    update_post_meta($task_id, "_upsellio_task_duration_minutes", $duration_minutes);
    if (function_exists("upsellio_automation_refresh_task_priority_meta")) {
        upsellio_automation_refresh_task_priority_meta($task_id);
    }
    upsellio_crm_app_append_entity_log("task", $task_id, "task_rescheduled", "Przesunięto task w kalendarzu.", [
        "start_at" => wp_date("Y-m-d H:i", $ts),
        "duration_minutes" => $duration_minutes,
    ]);
    wp_send_json_success([
        "ok" => true,
        "task_id" => $task_id,
        "start_at" => wp_date("Y-m-d H:i", $ts),
        "duration_minutes" => $duration_minutes,
    ]);
}
add_action("wp_ajax_upsellio_crm_schedule_task", "upsellio_crm_app_ajax_schedule_task");

function upsellio_crm_app_ajax_export_csv()
{
    if (!is_user_logged_in() || !current_user_can("manage_options")) {
        wp_die("Forbidden", "", ["response" => 403]);
    }
    $nonce = isset($_REQUEST["_wpnonce"]) ? sanitize_text_field(wp_unslash($_REQUEST["_wpnonce"])) : "";
    if (!wp_verify_nonce($nonce, "ups_crm_export")) {
        wp_die("Bad nonce", "", ["response" => 403]);
    }
    $entity = isset($_GET["entity"]) ? sanitize_key(wp_unslash($_GET["entity"])) : "clients";
    $map = [
        "leads" => "crm_lead",
        "clients" => "crm_client",
        "offers" => "crm_offer",
        "tasks" => "lead_task",
    ];
    if (!isset($map[$entity])) {
        $entity = "clients";
    }
    $posts = get_posts([
        "post_type" => $map[$entity],
        "post_status" => ["publish", "draft", "pending", "private"],
        "posts_per_page" => 2000,
        "orderby" => "ID",
        "order" => "ASC",
    ]);
    nocache_headers();
    header("Content-Type: text/csv; charset=utf-8");
    header("Content-Disposition: attachment; filename=crm-" . $entity . "-" . gmdate("Ymd-His") . ".csv");
    $out = fopen("php://output", "w");
    if ($out !== false) {
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ["id", "title", "type", "status", "created_at"]);
        foreach ($posts as $post) {
            fputcsv($out, [
                (int) $post->ID,
                (string) $post->post_title,
                (string) $post->post_type,
                (string) $post->post_status,
                (string) $post->post_date_gmt,
            ]);
        }
        fclose($out);
    }
    exit;
}
add_action("wp_ajax_upsellio_crm_export", "upsellio_crm_app_ajax_export_csv");

function upsellio_crm_inbox_send_reply()
{
    if (!upsellio_crm_app_user_can_access()) {
        wp_send_json_error(["message" => "forbidden"], 403);
    }
    $nonce = isset($_POST["nonce"]) ? sanitize_text_field(wp_unslash($_POST["nonce"])) : "";
    if (!wp_verify_nonce($nonce, "ups_crm_app_action")) {
        wp_send_json_error(["message" => "bad_nonce"], 403);
    }

    $send_mode = isset($_POST["inbox_send_mode"]) ? sanitize_key(wp_unslash($_POST["inbox_send_mode"])) : "";
    $compose_free = $send_mode === "compose_free";

    $offer_id = isset($_POST["offer_id"]) ? (int) wp_unslash($_POST["offer_id"]) : 0;
    $body_raw = isset($_POST["body"]) ? wp_unslash($_POST["body"]) : "";
    $body_raw = is_string($body_raw) ? $body_raw : "";
    if (trim($body_raw) === "") {
        wp_send_json_error(["message" => "empty_body"], 400);
    }

    $to_field = isset($_POST["to"]) ? (string) wp_unslash($_POST["to"]) : "";
    $cc_field = isset($_POST["cc"]) ? (string) wp_unslash($_POST["cc"]) : "";
    $bcc_field = isset($_POST["bcc"]) ? (string) wp_unslash($_POST["bcc"]) : "";
    $to_emails = function_exists("upsellio_inbox_parse_email_field") ? upsellio_inbox_parse_email_field($to_field) : [];
    $cc_emails = function_exists("upsellio_inbox_parse_email_field") ? upsellio_inbox_parse_email_field($cc_field) : [];
    $bcc_emails = function_exists("upsellio_inbox_parse_email_field") ? upsellio_inbox_parse_email_field($bcc_field) : [];

    $settings = upsellio_followup_get_sender_settings();

    if ($compose_free) {
        $offer_id = 0;
        if ($to_emails === []) {
            wp_send_json_error(["message" => "no_recipient"], 400);
        }
        $subject = isset($_POST["subject"]) ? sanitize_text_field(wp_unslash($_POST["subject"])) : "";
        if ($subject === "") {
            $subject = "Wiadomość z CRM";
        }
    } else {
        if ($offer_id <= 0 || get_post_type($offer_id) !== "crm_offer") {
            wp_send_json_error(["message" => "invalid_params"], 400);
        }
        if (!current_user_can("edit_post", $offer_id)) {
            wp_send_json_error(["message" => "forbidden_offer"], 403);
        }

        $client_id = (int) get_post_meta($offer_id, "_ups_offer_client_id", true);
        $client_email = sanitize_email((string) get_post_meta($client_id, "_ups_client_email", true));

        if ($to_emails === []) {
            if (!is_email($client_email)) {
                wp_send_json_error(["message" => "no_client_email"], 400);
            }
            $to_emails = [$client_email];
        }

        $offer_title = get_the_title($offer_id);
        $subject = isset($_POST["subject"]) ? sanitize_text_field(wp_unslash($_POST["subject"])) : "";
        if ($subject === "") {
            $subject = "Re: " . $offer_title;
        }
    }

    $attachments_saved = [];
    if (!empty($_FILES["inbox_files"]) && is_array($_FILES["inbox_files"])) {
        if (!function_exists("upsellio_mailbox_save_uploaded_attachments")) {
            wp_send_json_error(["message" => "attachments_unavailable"], 500);
        }
        $saved_atts = upsellio_mailbox_save_uploaded_attachments();
        if (is_wp_error($saved_atts)) {
            wp_send_json_error(["message" => $saved_atts->get_error_message()], 400);
        }
        $attachments_saved = is_array($saved_atts) ? $saved_atts : [];
    }

    $is_html = isset($_POST["body_is_html"]) && (string) wp_unslash($_POST["body_is_html"]) === "1";
    if ($is_html) {
        $html_fragment = wp_kses_post($body_raw);
        $body_plain = wp_strip_all_tags($html_fragment);
        $lower = strtolower($html_fragment);
        if (strpos($lower, "<html") !== false) {
            $html_core = $html_fragment;
        } else {
            $html_core =
                "<html><head><meta charset=\"utf-8\"></head><body>" . $html_fragment . "</body></html>";
        }
    } else {
        $body_plain = sanitize_textarea_field($body_raw);
        $html_core =
            "<html><head><meta charset=\"utf-8\"></head><body>" .
            nl2br(esc_html($body_plain)) .
            "</body></html>";
    }

    $mail_args = [
        "crm_smtp" => true,
        "to" => $to_emails,
        "cc" => $cc_emails,
        "bcc" => $bcc_emails,
        "attachments" => $attachments_saved,
    ];
    if (isset($_POST["use_footer"]) && (string) wp_unslash($_POST["use_footer"]) === "0") {
        $mail_args["skip_footer"] = true;
    }

    if (function_exists("upsellio_mailbox_log")) {
        upsellio_mailbox_log(
            "mail",
            "info",
            $compose_free ? "Żądanie wysłania wolnej wiadomości (bez oferty)." : "Żądanie wysłania z wątku oferty #{$offer_id}.",
            "Do: " . implode(", ", $to_emails) . " · załączników: " . count($attachments_saved)
        );
    }

    $primary_to = $to_emails[0];
    $sent = upsellio_followup_send_html_mail($primary_to, $subject, $html_core, $mail_args);
    if ($attachments_saved !== [] && function_exists("upsellio_mailbox_delete_temp_attachments")) {
        upsellio_mailbox_delete_temp_attachments($attachments_saved);
    }

    $html_for_meta = function_exists("upsellio_followup_finalize_crm_html")
        ? upsellio_followup_finalize_crm_html($html_core, $mail_args)
        : $html_core;

    if ($sent) {
        if (!$compose_free && function_exists("upsellio_inbox_append_message")) {
            upsellio_inbox_append_message($offer_id, [
                "direction" => "out",
                "from" => (string) ($settings["from_email"] ?? ""),
                "to" => implode(", ", $to_emails),
                "cc" => implode(", ", $cc_emails),
                "bcc" => implode(", ", $bcc_emails),
                "subject" => $subject,
                "body_plain" => $body_plain,
                "body_html" => $html_for_meta,
                "source" => "crm_manual",
                "read" => true,
            ]);
        }
        if (!$compose_free) {
            update_post_meta(
                $offer_id,
                "_ups_offer_followup_snooze_until",
                gmdate("Y-m-d H:i:s", time() + (48 * HOUR_IN_SECONDS))
            );
            if (function_exists("upsellio_offer_add_timeline_event")) {
                upsellio_offer_add_timeline_event($offer_id, "manual_reply_sent", "Ręczna odpowiedź z CRM: " . $subject);
            }
        }
        if (!$compose_free && !empty($_POST["trigger_automation"]) && (string) wp_unslash($_POST["trigger_automation"]) === "1") {
            do_action("upsellio_crm_inbox_mail_sent", $offer_id, [
                "subject" => $subject,
                "to" => $to_emails,
                "cc" => $cc_emails,
                "bcc" => $bcc_emails,
                "body_plain" => $body_plain,
            ]);
        }
        wp_send_json_success(["ok" => true]);
    }
    wp_send_json_error(["message" => "send_failed"], 500);
}
add_action("wp_ajax_upsellio_inbox_send_reply", "upsellio_crm_inbox_send_reply");

function upsellio_crm_inbox_mark_read_ajax()
{
    if (!upsellio_crm_app_user_can_access()) {
        wp_send_json_error([], 403);
    }
    $nonce = isset($_POST["nonce"]) ? sanitize_text_field(wp_unslash($_POST["nonce"])) : "";
    if (!wp_verify_nonce($nonce, "ups_crm_app_action")) {
        wp_send_json_error([], 403);
    }
    $offer_id = isset($_POST["offer_id"]) ? (int) wp_unslash($_POST["offer_id"]) : 0;
    if ($offer_id > 0 && function_exists("upsellio_inbox_mark_read")) {
        upsellio_inbox_mark_read($offer_id);
    }
    wp_send_json_success();
}
add_action("wp_ajax_upsellio_inbox_mark_read", "upsellio_crm_inbox_mark_read_ajax");

function upsellio_crm_inbox_classify_ajax()
{
    if (!upsellio_crm_app_user_can_access()) {
        wp_send_json_error([], 403);
    }
    $nonce = isset($_POST["nonce"]) ? sanitize_text_field(wp_unslash($_POST["nonce"])) : "";
    if (!wp_verify_nonce($nonce, "ups_crm_app_action")) {
        wp_send_json_error([], 403);
    }
    $offer_id = isset($_POST["offer_id"]) ? (int) wp_unslash($_POST["offer_id"]) : 0;
    $cls = isset($_POST["classification"]) ? sanitize_key(wp_unslash($_POST["classification"])) : "";
    $message_id = isset($_POST["message_id"]) ? sanitize_text_field(wp_unslash($_POST["message_id"])) : "";
    $allowed = ["positive", "price_objection", "timing_objection", "no_priority", "other"];
    if ($offer_id <= 0 || !in_array($cls, $allowed, true) || get_post_type($offer_id) !== "crm_offer") {
        wp_send_json_error([], 400);
    }
    if (!current_user_can("edit_post", $offer_id)) {
        wp_send_json_error([], 403);
    }
    if ($message_id !== "" && function_exists("upsellio_inbox_set_message_classification")) {
        $ok = upsellio_inbox_set_message_classification($offer_id, $message_id, $cls);
        if (!$ok) {
            wp_send_json_error([], 400);
        }
    } elseif (function_exists("upsellio_inbox_update_last_inbound_classification")) {
        upsellio_inbox_update_last_inbound_classification($offer_id, $cls);
    }
    $stage = (string) get_post_meta($offer_id, "_ups_offer_stage", true);
    if ($stage === "") {
        $stage = "awareness";
    }
    do_action("upsellio_inbound_classified", $offer_id, $cls, $stage);
    wp_send_json_success();
}
add_action("wp_ajax_upsellio_inbox_classify", "upsellio_crm_inbox_classify_ajax");

function upsellio_crm_inbox_mark_unread_ajax()
{
    if (!upsellio_crm_app_user_can_access()) {
        wp_send_json_error([], 403);
    }
    $nonce = isset($_POST["nonce"]) ? sanitize_text_field(wp_unslash($_POST["nonce"])) : "";
    if (!wp_verify_nonce($nonce, "ups_crm_app_action")) {
        wp_send_json_error([], 403);
    }
    $offer_id = isset($_POST["offer_id"]) ? (int) wp_unslash($_POST["offer_id"]) : 0;
    if ($offer_id <= 0 || get_post_type($offer_id) !== "crm_offer") {
        wp_send_json_error([], 400);
    }
    if (!current_user_can("edit_post", $offer_id)) {
        wp_send_json_error([], 403);
    }
    if (function_exists("upsellio_inbox_mark_thread_unread")) {
        upsellio_inbox_mark_thread_unread($offer_id);
    }
    wp_send_json_success();
}
add_action("wp_ajax_upsellio_inbox_mark_unread", "upsellio_crm_inbox_mark_unread_ajax");

function upsellio_crm_inbox_set_flag_ajax()
{
    if (!upsellio_crm_app_user_can_access()) {
        wp_send_json_error([], 403);
    }
    $nonce = isset($_POST["nonce"]) ? sanitize_text_field(wp_unslash($_POST["nonce"])) : "";
    if (!wp_verify_nonce($nonce, "ups_crm_app_action")) {
        wp_send_json_error([], 403);
    }
    $offer_id = isset($_POST["offer_id"]) ? (int) wp_unslash($_POST["offer_id"]) : 0;
    $flag = isset($_POST["flag"]) ? sanitize_key(wp_unslash($_POST["flag"])) : "";
    if ($offer_id <= 0 || get_post_type($offer_id) !== "crm_offer") {
        wp_send_json_error([], 400);
    }
    if (!current_user_can("edit_post", $offer_id)) {
        wp_send_json_error([], 403);
    }
    if (function_exists("upsellio_inbox_set_offer_flag")) {
        upsellio_inbox_set_offer_flag($offer_id, $flag);
    }
    wp_send_json_success(["flag" => function_exists("upsellio_inbox_offer_flag") ? upsellio_inbox_offer_flag($offer_id) : ""]);
}
add_action("wp_ajax_upsellio_inbox_set_flag", "upsellio_crm_inbox_set_flag_ajax");

function upsellio_crm_inbox_move_folder_ajax()
{
    if (!upsellio_crm_app_user_can_access()) {
        wp_send_json_error([], 403);
    }
    $nonce = isset($_POST["nonce"]) ? sanitize_text_field(wp_unslash($_POST["nonce"])) : "";
    if (!wp_verify_nonce($nonce, "ups_crm_app_action")) {
        wp_send_json_error([], 403);
    }
    $offer_id = isset($_POST["offer_id"]) ? (int) wp_unslash($_POST["offer_id"]) : 0;
    $folder_id = isset($_POST["folder_id"]) ? sanitize_key(wp_unslash($_POST["folder_id"])) : "";
    if ($offer_id <= 0 || $folder_id === "" || get_post_type($offer_id) !== "crm_offer") {
        wp_send_json_error([], 400);
    }
    if (!current_user_can("edit_post", $offer_id)) {
        wp_send_json_error([], 403);
    }
    if (function_exists("upsellio_inbox_set_offer_folder")) {
        upsellio_inbox_set_offer_folder($offer_id, $folder_id);
    }
    wp_send_json_success();
}
add_action("wp_ajax_upsellio_inbox_move_folder", "upsellio_crm_inbox_move_folder_ajax");

function upsellio_crm_inbox_folder_manage_ajax()
{
    if (!upsellio_crm_app_user_can_access()) {
        wp_send_json_error(["message" => "forbidden"], 403);
    }
    $nonce = isset($_POST["nonce"]) ? sanitize_text_field(wp_unslash($_POST["nonce"])) : "";
    if (!wp_verify_nonce($nonce, "ups_crm_app_action")) {
        wp_send_json_error(["message" => "bad_nonce"], 403);
    }
    $op = isset($_POST["op"]) ? sanitize_key(wp_unslash($_POST["op"])) : "";
    if ($op === "create") {
        $parent_id = isset($_POST["parent_id"]) ? sanitize_key(wp_unslash($_POST["parent_id"])) : "";
        if ($parent_id === "") {
            $parent_id = "fld_inbox";
        }
        $name = isset($_POST["name"]) ? sanitize_text_field(wp_unslash($_POST["name"])) : "";
        if ($name === "" || !function_exists("upsellio_inbox_folder_create")) {
            wp_send_json_error(["message" => "invalid"], 400);
        }
        $new_id = upsellio_inbox_folder_create($parent_id, $name);
        if ($new_id === "") {
            wp_send_json_error(["message" => "create_failed"], 400);
        }
        wp_send_json_success(["folder_id" => $new_id]);

        return;
    }
    if ($op === "rename") {
        $folder_id = isset($_POST["folder_id"]) ? sanitize_key(wp_unslash($_POST["folder_id"])) : "";
        $name = isset($_POST["name"]) ? sanitize_text_field(wp_unslash($_POST["name"])) : "";
        if ($folder_id === "" || $name === "" || !function_exists("upsellio_inbox_folder_rename")) {
            wp_send_json_error(["message" => "invalid"], 400);
        }
        if (!upsellio_inbox_folder_rename($folder_id, $name)) {
            wp_send_json_error(["message" => "rename_failed"], 400);
        }
        wp_send_json_success();

        return;
    }
    if ($op === "delete") {
        $folder_id = isset($_POST["folder_id"]) ? sanitize_key(wp_unslash($_POST["folder_id"])) : "";
        if ($folder_id === "" || !function_exists("upsellio_inbox_folder_delete")) {
            wp_send_json_error(["message" => "invalid"], 400);
        }
        if (!upsellio_inbox_folder_delete($folder_id)) {
            wp_send_json_error(["message" => "delete_failed"], 400);
        }
        wp_send_json_success();

        return;
    }
    wp_send_json_error(["message" => "bad_op"], 400);
}
add_action("wp_ajax_upsellio_inbox_folder_manage", "upsellio_crm_inbox_folder_manage_ajax");

function upsellio_crm_inbox_sync_mailbox_ajax()
{
    if (!upsellio_crm_app_user_can_access()) {
        wp_send_json_error(["message" => "forbidden"], 403);
    }
    $nonce = isset($_POST["nonce"]) ? sanitize_text_field(wp_unslash($_POST["nonce"])) : "";
    if (!wp_verify_nonce($nonce, "ups_crm_app_action")) {
        wp_send_json_error(["message" => "bad_nonce"], 403);
    }
    if (!function_exists("upsellio_followup_run_mailbox_poll")) {
        wp_send_json_error(["message" => "unavailable"], 500);
    }
    $r = upsellio_followup_run_mailbox_poll();
    if (function_exists("upsellio_mailbox_log")) {
        upsellio_mailbox_log(
            "imap",
            !empty($r["ok"]) ? "info" : "warn",
            "Synchronizacja skrzynki z poziomu inboxu (AJAX).",
            wp_json_encode(
                [
                    "ok" => !empty($r["ok"]),
                    "message" => (string) ($r["message"] ?? ""),
                    "imported" => (int) ($r["imported"] ?? 0),
                    "processed" => (int) ($r["processed"] ?? 0),
                ],
                JSON_UNESCAPED_UNICODE
            )
        );
    }
    if (!empty($r["ok"])) {
        wp_send_json_success([
            "message" => (string) ($r["message"] ?? ""),
            "imported" => (int) ($r["imported"] ?? 0),
            "processed" => (int) ($r["processed"] ?? 0),
        ]);

        return;
    }
    wp_send_json_error(["message" => (string) ($r["message"] ?? "sync_failed")], 400);
}
add_action("wp_ajax_upsellio_inbox_sync_mailbox", "upsellio_crm_inbox_sync_mailbox_ajax");

function upsellio_crm_mailbox_log_clear_ajax()
{
    if (!current_user_can("manage_options")) {
        wp_send_json_error(["message" => "forbidden"], 403);
    }
    if (!upsellio_crm_app_user_can_access()) {
        wp_send_json_error(["message" => "forbidden"], 403);
    }
    $nonce = isset($_POST["nonce"]) ? sanitize_text_field(wp_unslash($_POST["nonce"])) : "";
    if (!wp_verify_nonce($nonce, "ups_crm_app_action")) {
        wp_send_json_error(["message" => "bad_nonce"], 403);
    }
    update_option("ups_mailbox_activity_log", [], false);
    if (function_exists("upsellio_mailbox_log")) {
        upsellio_mailbox_log("imap", "info", "Log skrzynki został wyczyszczony (przycisk w ustawieniach).");
    }
    wp_send_json_success(["ok" => true]);
}
add_action("wp_ajax_upsellio_crm_clear_mailbox_log", "upsellio_crm_mailbox_log_clear_ajax");
