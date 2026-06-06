<?php
/**
 * CLI health check: Google APIs + WP-Cron automations.
 * Run: wp eval-file wp-content/themes/upsellio/tools/health-check-google-automation.php
 */

if (!defined("ABSPATH")) {
    exit(1);
}

function ups_health_line(string $label, string $status, string $detail = ""): void
{
    $detail = $detail !== "" ? " — " . $detail : "";
    WP_CLI::log(sprintf("[%s] %s%s", $status, $label, $detail));
}

$ok = "OK";
$warn = "WARN";
$fail = "FAIL";

WP_CLI::log("=== Google OAuth / API ===");

$gsc = function_exists("upsellio_get_gsc_credentials") ? upsellio_get_gsc_credentials() : [];
$has_refresh = trim((string) ($gsc["refresh_token"] ?? "")) !== "";
$has_gsc_prop = trim((string) ($gsc["property"] ?? "")) !== "";
$has_client = trim((string) ($gsc["client_id"] ?? "")) !== "" || function_exists("upsellio_google_managed_oauth_is_active") && upsellio_google_managed_oauth_is_active();

$managed_on = function_exists("upsellio_google_managed_oauth_is_active") && upsellio_google_managed_oauth_is_active();
$managed_self = $managed_on && function_exists("upsellio_google_managed_oauth_is_self_hosted") && upsellio_google_managed_oauth_is_self_hosted();
ups_health_line(
    "Upsellio Connect (managed OAuth)",
    $managed_on ? $ok : $warn,
    $managed_on
        ? ($managed_self ? "self-hosted bridge" : "external bridge " . (function_exists("upsellio_google_managed_oauth_bridge_base") ? upsellio_google_managed_oauth_bridge_base() : ""))
        : "inactive — need OAuth Client ID + Secret"
);
if ($managed_self && function_exists("upsellio_google_managed_oauth_bridge_callback_uri")) {
    ups_health_line("Managed bridge callback URI", $ok, upsellio_google_managed_oauth_bridge_callback_uri());
}

ups_health_line(
    "OAuth refresh token",
    $has_refresh ? $ok : $fail,
    $has_refresh ? "present" : "missing — reconnect Google in Site Analytics"
);

ups_health_line(
    "GSC property",
    $has_gsc_prop ? $ok : $warn,
    $has_gsc_prop ? (string) $gsc["property"] : "not set"
);

$ga4_id = function_exists("upsellio_get_ga4_property_id") ? trim((string) upsellio_get_ga4_property_id()) : "";
ups_health_line("GA4 property ID", $ga4_id !== "" ? $ok : $warn, $ga4_id !== "" ? $ga4_id : "not set");

$gads_ready = function_exists("upsellio_google_ads_api_ready") && upsellio_google_ads_api_ready();
$gads_cfg = function_exists("upsellio_google_ads_get_settings") ? upsellio_google_ads_get_settings() : [];
ups_health_line(
    "Google Ads API ready",
    $gads_ready ? $ok : $warn,
    $gads_ready
        ? "customer " . ($gads_cfg["customer_id"] ?? "")
        : "needs refresh token + developer token + customer ID + adwords scope"
);

$snap = function_exists("upsellio_google_get_permission_snapshot") ? upsellio_google_get_permission_snapshot() : [];
if (is_array($snap) && $snap !== []) {
    ups_health_line(
        "OAuth scopes cache",
        $ok,
        "GSC=" . (!empty($snap["has_gsc"]) ? "yes" : "no")
        . " GA4=" . (!empty($snap["has_ga4"]) ? "yes" : "no")
        . " Ads=" . (!empty($snap["has_google_ads"]) ? "yes" : "no")
    );
}

if ($has_refresh && function_exists("upsellio_gsc_get_access_token")) {
    $token = upsellio_gsc_get_access_token($gsc, "health-" . wp_generate_password(6, false));
    if (is_wp_error($token)) {
        ups_health_line("GSC access token", $fail, $token->get_error_message());
    } else {
        ups_health_line("GSC access token", $ok, "obtained");
        if ($has_gsc_prop && function_exists("upsellio_gsc_fetch_rows")) {
            $rows = upsellio_gsc_fetch_rows($gsc, 7, "health-gsc");
            if (is_wp_error($rows)) {
                ups_health_line("GSC API fetch (7d)", $fail, $rows->get_error_message());
            } else {
                ups_health_line("GSC API fetch (7d)", $ok, count($rows) . " rows");
            }
        }
    }
}

if ($has_refresh && $ga4_id !== "" && function_exists("upsellio_ga4_data_api_fetch_aggregates")) {
    $ga4_num = preg_replace("/\D/", "", $ga4_id);
    if ($ga4_num !== "") {
        $ga4_rows = upsellio_ga4_data_api_fetch_aggregates($ga4_num, 7, "health-ga4");
        if (is_wp_error($ga4_rows)) {
            ups_health_line("GA4 Data API (7d)", $fail, $ga4_rows->get_error_message());
        } else {
            ups_health_line("GA4 Data API (7d)", $ok, count($ga4_rows) . " channel rows");
        }
    }
}

if ($gads_ready && function_exists("upsellio_google_ads_list_accessible_customers")) {
    $gads_test = upsellio_google_ads_list_accessible_customers("health-gads");
    if (is_wp_error($gads_test)) {
        ups_health_line("Google Ads listAccessibleCustomers", $fail, $gads_test->get_error_message());
    } else {
        $ids = isset($gads_test["resourceNames"]) && is_array($gads_test["resourceNames"])
            ? count($gads_test["resourceNames"])
            : 0;
        ups_health_line("Google Ads listAccessibleCustomers", $ok, $ids . " accounts");
    }
}

WP_CLI::log("");
WP_CLI::log("=== Last sync timestamps ===");

$sync_opts = [
    "GSC keywords" => "upsellio_keyword_metrics_last_sync",
    "GA4 CRM aggregates" => "ups_automation_ga4_last_sync",
    "GA4 cohort" => "ups_ga4_cohort_last_sync",
    "Google Ads campaigns" => "ups_ads_campaigns_synced",
    "GSC indexation" => "ups_gsc_indexation_last_sync",
    "Audit daily sync" => "ups_audit_last_daily_sync",
];
foreach ($sync_opts as $label => $key) {
    $val = (string) get_option($key, "");
    $st = $val !== "" && $val !== "-" ? $ok : $warn;
    if ($val !== "" && $val !== "-") {
        $ts = strtotime($val);
        if ($ts !== false && (time() - $ts) > 3 * DAY_IN_SECONDS) {
            $st = $warn;
        }
    }
    ups_health_line($label, $st, $val !== "" ? $val : "never");
}

$gads_err = (string) get_option("ups_ads_campaigns_sync_error", "");
if ($gads_err !== "") {
    ups_health_line("Google Ads last error", $fail, $gads_err);
}

WP_CLI::log("");
WP_CLI::log("=== WP-Cron (upsellio / ups_*) ===");

$cron = _get_cron_array();
$ups_hooks = [];
$now = time();
foreach ($cron as $ts => $hooks) {
    if (!is_array($hooks)) {
        continue;
    }
    foreach ($hooks as $hook => $events) {
        if (
            strpos($hook, "upsellio_") !== 0
            && strpos($hook, "ups_") !== 0
            && strpos($hook, "upsellio") === false
        ) {
            continue;
        }
        if (!isset($ups_hooks[$hook])) {
            $ups_hooks[$hook] = ["next" => (int) $ts, "late" => ((int) $ts < $now)];
        }
    }
}

ksort($ups_hooks);
if ($ups_hooks === []) {
    ups_health_line("Scheduled events", $warn, "none found — visit site or run wp cron event run --due-now");
} else {
    foreach ($ups_hooks as $hook => $meta) {
        $late = !empty($meta["late"]);
        ups_health_line(
            $hook,
            $late ? $warn : $ok,
            "next " . wp_date("Y-m-d H:i:s", (int) $meta["next"]) . ($late ? " (OVERDUE)" : "")
        );
    }
}

$disabled = defined("DISABLE_WP_CRON") && DISABLE_WP_CRON;
ups_health_line("DISABLE_WP_CRON", $disabled ? $warn : $ok, $disabled ? "true — external cron must hit wp-cron.php" : "false");

WP_CLI::log("");
WP_CLI::log("=== Automation flags ===");

$flags = [
    "GA4 sync enabled" => (string) get_option("ups_automation_ga4_sync_enabled", "1"),
    "Blog bot enabled" => (string) get_option("ups_blog_bot_enabled", "0"),
    "Inbox AI follow-up" => (string) get_option("ups_anthropic_inbox_auto_followup_enabled", "0"),
    "Follow-up queue (5min)" => wp_next_scheduled("upsellio_followup_process_queue") ? "scheduled" : "not scheduled",
];
foreach ($flags as $label => $val) {
    $st = ($val === "1" || $val === "scheduled") ? $ok : $warn;
    if (strpos($label, "enabled") !== false && $val === "0") {
        $st = $warn;
    }
    ups_health_line($label, $st, (string) $val);
}

WP_CLI::success("Health check finished.");
