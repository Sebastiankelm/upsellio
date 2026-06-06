<?php

/**
 * Diagnostyka OAuth — uruchom: cd public_html && php wp-content/themes/upsellio/tools/oauth-diag.php
 */
define("WP_USE_THEMES", false);
require dirname(__DIR__, 4) . "/wp-load.php";

header("Content-Type: text/plain; charset=utf-8");

$c = function_exists("upsellio_get_gsc_credentials") ? upsellio_get_gsc_credentials() : [];
echo "client_id: " . trim((string) ($c["client_id"] ?? "")) . "\n";
echo "audit_crm_callback: " . (function_exists("ups_audit_oauth_redirect_uri") ? ups_audit_oauth_redirect_uri() : "n/a") . "\n";
echo "bridge_callback: " . (function_exists("upsellio_google_managed_oauth_bridge_callback_uri") ? upsellio_google_managed_oauth_bridge_callback_uri() : "n/a") . "\n";
echo "hyphen_callback: " . (function_exists("upsellio_google_oauth_rest_redirect_uri_default") ? upsellio_google_oauth_rest_redirect_uri_default() : "n/a") . "\n";
echo "override: " . (string) get_option("upsellio_google_oauth_redirect_uri_override", "") . "\n";
echo "use_rest: " . (function_exists("upsellio_google_oauth_use_rest_callback") && upsellio_google_oauth_use_rest_callback() ? "yes" : "no") . "\n";
echo "\nDodaj w Google Cloud (OAuth client Web) DOKLADNIE:\n";
if (function_exists("upsellio_google_oauth_required_redirect_uris_for_google_console")) {
    foreach (upsellio_google_oauth_required_redirect_uris_for_google_console() as $u) {
        echo "  " . $u . "\n";
    }
}
