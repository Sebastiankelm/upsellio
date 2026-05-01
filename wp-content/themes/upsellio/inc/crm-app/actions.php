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

    $global_log = get_option("ups_crm_activity_log", []);
    if (!is_array($global_log)) {
        $global_log = [];
    }
    $global_log[] = [
        "entity_type" => $entity_type,
        "entity_id" => $entity_id,
        "entry" => $entry,
    ];
    if (count($global_log) > 1000) {
        $global_log = array_slice($global_log, -1000);
    }
    update_option("ups_crm_activity_log", $global_log, false);
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
                update_post_meta($offer_id, "_ups_offer_loss_reason", sanitize_text_field(wp_unslash($_POST["offer_loss_reason"])));
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
            if ($previous_subscription_status !== "cancelled" && $subscription_status === "cancelled") {
                do_action("upsellio_client_subscription_cancelled", $client_id);
            }
            upsellio_crm_app_append_entity_log("client", $client_id, "client_saved", "Zapisano dane klienta.", []);
        }
    } elseif ($action === "save_offer") {
        $offer_id = isset($_POST["offer_id"]) ? (int) wp_unslash($_POST["offer_id"]) : 0;
        $title = isset($_POST["offer_title"]) ? sanitize_text_field(wp_unslash($_POST["offer_title"])) : "";
        $offer_content_raw = isset($_POST["offer_content"]) ? wp_kses_post(wp_unslash($_POST["offer_content"])) : "";
        $generate_offer_from_template = isset($_POST["offer_generate_from_template"]) && (string) wp_unslash($_POST["offer_generate_from_template"]) === "1";
        if ($title === "") {
            return;
        }
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
            $old_offer_status = (string) get_post_meta($offer_id, "_ups_offer_status", true);
            update_post_meta($offer_id, "_ups_offer_client_id", isset($_POST["offer_client_id"]) ? (int) wp_unslash($_POST["offer_client_id"]) : 0);
            update_post_meta($offer_id, "_ups_offer_price", isset($_POST["offer_price"]) ? sanitize_text_field(wp_unslash($_POST["offer_price"])) : "");
            update_post_meta($offer_id, "_ups_offer_timeline", isset($_POST["offer_timeline"]) ? sanitize_text_field(wp_unslash($_POST["offer_timeline"])) : "");
            update_post_meta($offer_id, "_ups_offer_cta_text", isset($_POST["offer_cta_text"]) ? sanitize_text_field(wp_unslash($_POST["offer_cta_text"])) : "");
            $new_offer_status = isset($_POST["offer_status"]) ? sanitize_key(wp_unslash($_POST["offer_status"])) : "open";
            update_post_meta($offer_id, "_ups_offer_status", $new_offer_status);
            update_post_meta($offer_id, "_ups_offer_won_value", isset($_POST["offer_won_value"]) ? (float) wp_unslash($_POST["offer_won_value"]) : 0);
            update_post_meta($offer_id, "_ups_offer_owner_id", isset($_POST["offer_owner_id"]) ? (int) wp_unslash($_POST["offer_owner_id"]) : 0);
            update_post_meta($offer_id, "_ups_deal_notes", isset($_POST["deal_notes"]) ? sanitize_textarea_field(wp_unslash($_POST["deal_notes"])) : "");
            update_post_meta($offer_id, "_ups_offer_internal_notes", isset($_POST["offer_internal_notes"]) ? sanitize_textarea_field(wp_unslash($_POST["offer_internal_notes"])) : "");
            update_post_meta($offer_id, "_ups_offer_win_reason", isset($_POST["offer_win_reason"]) ? sanitize_text_field(wp_unslash($_POST["offer_win_reason"])) : "");
            update_post_meta($offer_id, "_ups_offer_loss_reason", isset($_POST["offer_loss_reason"]) ? sanitize_text_field(wp_unslash($_POST["offer_loss_reason"])) : "");
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
            if ($generate_from_template && function_exists("upsellio_contracts_get_default_template_html")) {
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
    } elseif ($action === "export_crm_data") {
        if (!current_user_can("manage_options")) {
            return;
        }
        $entity = isset($_POST["export_entity"]) ? sanitize_key(wp_unslash($_POST["export_entity"])) : "clients";
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
        ]);
        nocache_headers();
        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=crm-" . $entity . "-" . gmdate("Ymd-His") . ".csv");
        $out = fopen("php://output", "w");
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
        exit;
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
        if (current_user_can("manage_options")) {
            update_option("ups_contract_reminder_first_days", isset($_POST["contract_reminder_first_days"]) ? max(1, (int) wp_unslash($_POST["contract_reminder_first_days"])) : 3);
            update_option("ups_contract_reminder_second_days", isset($_POST["contract_reminder_second_days"]) ? max(2, (int) wp_unslash($_POST["contract_reminder_second_days"])) : 7);
            update_option("ups_followup_cooldown_hours", isset($_POST["followup_cooldown_hours"]) ? max(0, (int) wp_unslash($_POST["followup_cooldown_hours"])) : 24);
            update_option("ups_followup_max_per_offer", isset($_POST["followup_max_per_offer"]) ? max(1, (int) wp_unslash($_POST["followup_max_per_offer"])) : 5);

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

            if (isset($_POST["ups_followup_from_name"])) {
                update_option("ups_followup_from_name", sanitize_text_field(wp_unslash($_POST["ups_followup_from_name"])));
            }
            if (isset($_POST["ups_followup_from_email"])) {
                update_option("ups_followup_from_email", sanitize_email(wp_unslash($_POST["ups_followup_from_email"])));
            }
            if (isset($_POST["ups_followup_inbound_secret"])) {
                update_option("ups_followup_inbound_secret", sanitize_text_field(wp_unslash($_POST["ups_followup_inbound_secret"])));
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
            if (isset($_POST["ups_followup_mailbox_test"]) && function_exists("upsellio_followup_test_mailbox_connection")) {
                $test_result = upsellio_followup_test_mailbox_connection();
                set_transient("ups_crm_mailbox_test_" . get_current_user_id(), $test_result, 120);
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
    if ($redirect_client > 0 && $redirect_view === "client-edit") {
        $redirect_url = add_query_arg(["client_id" => $redirect_client], $redirect_url);
    }
    if ($redirect_task > 0 && $redirect_view === "tasks") {
        $redirect_url = add_query_arg(["task_id" => $redirect_task], $redirect_url);
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
    if (!in_array($stage, ["awareness", "consideration", "decision", "won", "lost"], true)) {
        wp_send_json_error(["message" => "invalid_stage"], 400);
    }

    $old_status = (string) get_post_meta($offer_id, "_ups_offer_status", true);
    $old_stage = (string) get_post_meta($offer_id, "_ups_offer_stage", true);
    if ($stage === "won" || $stage === "lost") {
        update_post_meta($offer_id, "_ups_offer_status", $stage);
        update_post_meta($offer_id, "_ups_offer_stage", "decision");
        delete_post_meta($offer_id, "_ups_offer_sla_active_alert");
        if ($old_status !== $stage) {
            do_action("upsellio_offer_status_changed", $offer_id, $stage, $old_status);
        }
    } else {
        update_post_meta($offer_id, "_ups_offer_stage", $stage);
        if (function_exists("upsellio_automation_sync_offer_pipeline_sla_from_marketing_stage")) {
            upsellio_automation_sync_offer_pipeline_sla_from_marketing_stage($offer_id, $stage);
        }
        if ($old_status === "won" || $old_status === "lost") {
            update_post_meta($offer_id, "_ups_offer_status", "open");
            do_action("upsellio_offer_status_changed", $offer_id, "open", $old_status);
        }
    }
    if (function_exists("upsellio_offer_add_timeline_event") && $old_stage !== $stage) {
        upsellio_offer_add_timeline_event($offer_id, "pipeline_moved", "Przeniesiono oferte w pipeline do: " . $stage);
    }
    upsellio_crm_app_append_entity_log("offer", $offer_id, "pipeline_moved", "Przeniesiono deal w pipeline.", [
        "from_stage" => $old_stage,
        "to_stage" => $stage,
    ]);

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
