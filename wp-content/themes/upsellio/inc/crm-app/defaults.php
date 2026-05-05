<?php
if (!defined("ABSPATH")) {
    exit;
}

/**
 * Hardcoded settings defaults for legacy options removed from UI.
 */
function upsellio_setting(string $key, $fallback = null)
{
    static $hardcoded = null;
    if ($hardcoded === null) {
        $hardcoded = [
            "ups_contract_reminder_first_days" => 3,
            "ups_contract_reminder_second_days" => 7,
            "ups_followup_max_per_offer" => 5,
            "ups_followup_from_name" => get_bloginfo("name"),
            "ups_followup_smtp_port" => 587,
            "ups_followup_smtp_encryption" => "tls",
            "ups_followup_mailbox_port" => 993,
            "ups_followup_mailbox_encryption" => "ssl",
            "ups_followup_mailbox_folder" => "INBOX",
            "ups_followup_mailbox_ssl_novalidate" => false,
            "ups_offer_stage_consideration_views" => 2,
            "ups_offer_stage_decision_views" => 3,
            "ups_offer_stage_decision_require_cta" => false,
            "ups_offer_score_consideration" => 45,
            "ups_offer_score_decision" => 75,
            "ups_offer_score_hot" => 70,
            "ups_offer_score_consideration_pricing_seconds" => 25,
            "ups_offer_score_decision_pricing_seconds" => 60,
            "ups_offer_score_hot_pricing_seconds" => 45,
            "ups_hybrid_weight_source" => 15,
            "ups_hybrid_weight_fit" => 25,
            "ups_hybrid_weight_intent" => 30,
            "ups_hybrid_weight_timing" => 15,
            "ups_hybrid_weight_value" => 15,
            "ups_automation_cold_followup_days" => 3,
            "ups_automation_ab_min_sample" => 20,
            "ups_automation_ab_min_lift_pct" => 5,
            "ups_blog_bot_target_length" => 1200,
            "ups_blog_bot_http_timeout" => 240,
            "ups_ai_blog_seo_temperature" => 0.7,
            "ups_ai_blog_seo_max_tokens" => 3500,
        ];
    }

    if (isset($hardcoded[$key])) {
        return apply_filters("upsellio_setting_default_{$key}", $hardcoded[$key]);
    }

    return get_option($key, $fallback);
}
