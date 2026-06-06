<?php

if (!defined("ABSPATH")) {
    exit(1);
}

$lines = [];
$lines[] = "=== Upsellio — Authorized redirect URIs (Google Cloud) ===";
$lines[] = "";
$lines[] = "Dodaj KAZDY ponizszy URI do tego samego OAuth Client ID (Web application).";
$lines[] = "Musi byc identyczny ciag (bez koncowego /).";
$lines[] = "";

if (function_exists("upsellio_google_managed_oauth_is_active") && upsellio_google_managed_oauth_is_active()) {
    $lines[] = "[PRIORYTET] Upsellio Connect (przycisk Zaloguj przez Google):";
    if (function_exists("upsellio_google_managed_oauth_bridge_callback_uri")) {
        $lines[] = "  " . upsellio_google_managed_oauth_bridge_callback_uri();
    }
    $lines[] = "";
}

$lines[] = "Analityka SEO (legacy REST / admin):";
if (function_exists("upsellio_google_oauth_redirect_uri")) {
    $lines[] = "  aktywny: " . upsellio_google_oauth_redirect_uri();
}
if (function_exists("upsellio_google_oauth_rest_redirect_uri_default")) {
    $lines[] = "  REST (google-oauth-callback): " . upsellio_google_oauth_rest_redirect_uri_default();
}
if (function_exists("upsellio_google_oauth_admin_redirect_uri_default")) {
    $lines[] = "  admin.php: " . upsellio_google_oauth_admin_redirect_uri_default();
}
$lines[] = "";

if (function_exists("ups_audit_oauth_redirect_uri")) {
    $lines[] = "CRM audyt (direct OAuth, gdy Connect wylaczony):";
    $lines[] = "  " . ups_audit_oauth_redirect_uri();
    $lines[] = "";
}

if (function_exists("upsellio_google_oauth_redirect_uri_variants")) {
    $lines[] = "Wszystkie warianty (unikalne):";
    foreach (upsellio_google_oauth_redirect_uri_variants() as $u) {
        $lines[] = "  " . $u;
    }
}

$lines[] = "";
$lines[] = "managed_active: " . (function_exists("upsellio_google_managed_oauth_is_active") && upsellio_google_managed_oauth_is_active() ? "yes" : "no");
$lines[] = "self_hosted: " . (function_exists("upsellio_google_managed_oauth_is_self_hosted") && upsellio_google_managed_oauth_is_self_hosted() ? "yes" : "no");

foreach ($lines as $line) {
    if (class_exists("WP_CLI")) {
        WP_CLI::log($line);
    } else {
        echo $line . "\n";
    }
}
