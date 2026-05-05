<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_resolve_ga_client_id(array $payload): string
{
    if (!empty($_COOKIE["_ga"])) {
        $parts = explode(".", sanitize_text_field((string) $_COOKIE["_ga"]));
        if (count($parts) >= 4) {
            return $parts[2] . "." . $parts[3];
        }
    }
    return wp_generate_uuid4();
}

function upsellio_build_fbc(array $payload): ?string
{
    if (!empty($_COOKIE["_fbc"])) {
        return sanitize_text_field((string) $_COOKIE["_fbc"]);
    }
    $fbclid = sanitize_text_field((string) ($payload["fbclid"] ?? ""));
    if ($fbclid === "") {
        return null;
    }
    return "fb.1." . time() . "." . $fbclid;
}

function upsellio_server_send_lead_conversion(int $lead_id, array $payload): void
{
    $gaApiSecret = defined("UPSELLIO_GA4_API_SECRET") ? (string) UPSELLIO_GA4_API_SECRET : "";
    $gaMeasurementId = defined("UPSELLIO_GA4_MEASUREMENT_ID") ? (string) UPSELLIO_GA4_MEASUREMENT_ID : "G-R37SMGVBNC";
    $metaToken = defined("UPSELLIO_META_CAPI_TOKEN") ? (string) UPSELLIO_META_CAPI_TOKEN : "";
    $metaPixelId = defined("UPSELLIO_META_PIXEL_ID") ? (string) UPSELLIO_META_PIXEL_ID : "";

    $eventId = sanitize_text_field((string) ($payload["event_id"] ?? ""));
    $email = strtolower(trim((string) ($payload["email"] ?? "")));
    $phone = preg_replace("/\D+/", "", (string) ($payload["phone"] ?? ""));
    if ($phone !== "" && strpos($phone, "48") !== 0 && strlen($phone) === 9) {
        $phone = "48" . $phone;
    }
    $clientId = upsellio_resolve_ga_client_id($payload);
    $ip = isset($_SERVER["REMOTE_ADDR"]) ? sanitize_text_field((string) $_SERVER["REMOTE_ADDR"]) : "";
    $ua = isset($_SERVER["HTTP_USER_AGENT"]) ? sanitize_text_field((string) $_SERVER["HTTP_USER_AGENT"]) : "";
    $eventTime = time();

    if ($gaApiSecret !== "" && $gaMeasurementId !== "") {
        $ga4Url = add_query_arg([
            "measurement_id" => $gaMeasurementId,
            "api_secret" => $gaApiSecret,
        ], "https://www.google-analytics.com/mp/collect");

        wp_remote_post($ga4Url, [
            "timeout" => 4,
            "blocking" => false,
            "headers" => ["Content-Type" => "application/json"],
            "body" => wp_json_encode([
                "client_id" => $clientId,
                "events" => [[
                    "name" => "generate_lead",
                    "params" => [
                        "event_id" => $eventId,
                        "currency" => "PLN",
                        "value" => 0,
                        "form_origin" => (string) ($payload["form_origin"] ?? ""),
                        "utm_source" => (string) ($payload["utm_source"] ?? ""),
                        "utm_medium" => (string) ($payload["utm_medium"] ?? ""),
                        "utm_campaign" => (string) ($payload["utm_campaign"] ?? ""),
                    ],
                ]],
            ]),
        ]);
    }

    if ($metaToken !== "" && $metaPixelId !== "") {
        $capiBody = [
            "data" => [[
                "event_name" => "Lead",
                "event_time" => $eventTime,
                "event_id" => $eventId,
                "action_source" => "website",
                "event_source_url" => (string) ($payload["landing_url"] ?? home_url("/")),
                "user_data" => array_filter([
                    "em" => $email !== "" ? hash("sha256", $email) : null,
                    "ph" => $phone !== "" ? hash("sha256", $phone) : null,
                    "client_ip_address" => $ip !== "" ? $ip : null,
                    "client_user_agent" => $ua !== "" ? $ua : null,
                    "fbc" => upsellio_build_fbc($payload),
                    "fbp" => !empty($_COOKIE["_fbp"]) ? sanitize_text_field((string) $_COOKIE["_fbp"]) : null,
                ]),
                "custom_data" => array_filter([
                    "content_name" => (string) ($payload["form_origin"] ?? ""),
                    "content_category" => (string) ($payload["service"] ?? ""),
                    "lead_id" => $lead_id,
                ]),
            ]],
        ];

        $capiUrl = sprintf(
            "https://graph.facebook.com/v19.0/%s/events?access_token=%s",
            rawurlencode($metaPixelId),
            rawurlencode($metaToken)
        );
        wp_remote_post($capiUrl, [
            "timeout" => 4,
            "blocking" => false,
            "headers" => ["Content-Type" => "application/json"],
            "body" => wp_json_encode($capiBody),
        ]);
    }
}

function upsellio_server_send_closed_won_conversion(int $lead_id): void
{
    $lead_id = (int) $lead_id;
    if ($lead_id <= 0) {
        return;
    }
    if (get_post_type($lead_id) !== "lead") {
        return;
    }

    $gaApiSecret = defined("UPSELLIO_GA4_API_SECRET") ? (string) UPSELLIO_GA4_API_SECRET : "";
    $gaMeasurementId = defined("UPSELLIO_GA4_MEASUREMENT_ID") ? (string) UPSELLIO_GA4_MEASUREMENT_ID : "G-R37SMGVBNC";
    $metaToken = defined("UPSELLIO_META_CAPI_TOKEN") ? (string) UPSELLIO_META_CAPI_TOKEN : "";
    $metaPixelId = defined("UPSELLIO_META_PIXEL_ID") ? (string) UPSELLIO_META_PIXEL_ID : "";

    $email = strtolower(trim((string) get_post_meta($lead_id, "_upsellio_lead_email", true)));
    $phone = preg_replace("/\D+/", "", (string) get_post_meta($lead_id, "_upsellio_lead_phone", true));
    if ($phone !== "" && strpos($phone, "48") !== 0 && strlen($phone) === 9) {
        $phone = "48" . $phone;
    }

    $landing_url = (string) get_post_meta($lead_id, "_upsellio_lead_landing_url", true);
    $form_origin = (string) get_post_meta($lead_id, "_upsellio_lead_form_origin", true);
    $service = (string) get_post_meta($lead_id, "_upsellio_lead_service", true);
    $close_value = (float) get_post_meta($lead_id, "_upsellio_lead_close_value", true);
    if ($close_value < 0) {
        $close_value = 0.0;
    }

    $event_id = (string) get_post_meta($lead_id, "_upsellio_lead_event_id", true);
    if ($event_id === "") {
        $event_id = "won_" . $lead_id . "_" . time();
    } else {
        $event_id .= "_won";
    }

    $clientId = upsellio_resolve_ga_client_id([]);
    $ip = isset($_SERVER["REMOTE_ADDR"]) ? sanitize_text_field((string) $_SERVER["REMOTE_ADDR"]) : "";
    $ua = isset($_SERVER["HTTP_USER_AGENT"]) ? sanitize_text_field((string) $_SERVER["HTTP_USER_AGENT"]) : "";
    $eventTime = time();

    if ($gaApiSecret !== "" && $gaMeasurementId !== "") {
        $ga4Url = add_query_arg([
            "measurement_id" => $gaMeasurementId,
            "api_secret" => $gaApiSecret,
        ], "https://www.google-analytics.com/mp/collect");

        wp_remote_post($ga4Url, [
            "timeout" => 4,
            "blocking" => false,
            "headers" => ["Content-Type" => "application/json"],
            "body" => wp_json_encode([
                "client_id" => $clientId,
                "events" => [[
                    "name" => "purchase",
                    "params" => [
                        "event_id" => $event_id,
                        "transaction_id" => "lead_" . $lead_id,
                        "currency" => "PLN",
                        "value" => $close_value,
                        "form_origin" => $form_origin,
                        "lead_id" => $lead_id,
                    ],
                ]],
            ]),
        ]);
    }

    if ($metaToken !== "" && $metaPixelId !== "") {
        $capiBody = [
            "data" => [[
                "event_name" => "Purchase",
                "event_time" => $eventTime,
                "event_id" => $event_id,
                "action_source" => "website",
                "event_source_url" => $landing_url !== "" ? $landing_url : home_url("/"),
                "user_data" => array_filter([
                    "em" => $email !== "" ? hash("sha256", $email) : null,
                    "ph" => $phone !== "" ? hash("sha256", $phone) : null,
                    "client_ip_address" => $ip !== "" ? $ip : null,
                    "client_user_agent" => $ua !== "" ? $ua : null,
                ]),
                "custom_data" => array_filter([
                    "currency" => "PLN",
                    "value" => $close_value,
                    "content_name" => $form_origin,
                    "content_category" => $service,
                    "lead_id" => $lead_id,
                ]),
            ]],
        ];

        $capiUrl = sprintf(
            "https://graph.facebook.com/v19.0/%s/events?access_token=%s",
            rawurlencode($metaPixelId),
            rawurlencode($metaToken)
        );
        wp_remote_post($capiUrl, [
            "timeout" => 4,
            "blocking" => false,
            "headers" => ["Content-Type" => "application/json"],
            "body" => wp_json_encode($capiBody),
        ]);
    }
}
