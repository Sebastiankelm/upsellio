<?php

if (!defined("ABSPATH")) {
    exit(1);
}

$on = function_exists("upsellio_google_managed_oauth_is_active") && upsellio_google_managed_oauth_is_active();
$self = $on && function_exists("upsellio_google_managed_oauth_is_self_hosted") && upsellio_google_managed_oauth_is_self_hosted();

if (class_exists("WP_CLI")) {
    WP_CLI::log("managed: " . ($on ? "yes" : "no"));
    WP_CLI::log("self_hosted: " . ($self ? "yes" : "no"));
    if (function_exists("upsellio_google_managed_oauth_bridge_callback_uri")) {
        WP_CLI::log("callback: " . upsellio_google_managed_oauth_bridge_callback_uri());
    }
    $c = function_exists("upsellio_get_gsc_credentials") ? upsellio_get_gsc_credentials() : [];
    WP_CLI::log("client_id: " . (trim((string) ($c["client_id"] ?? "")) !== "" ? "set" : "empty"));
    WP_CLI::log("refresh: " . (trim((string) ($c["refresh_token"] ?? "")) !== "" ? "ok" : "empty"));
}
