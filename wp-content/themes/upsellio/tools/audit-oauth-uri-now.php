<?php
if (!defined("ABSPATH")) {
    exit(1);
}
echo "redirect_uri=" . ups_audit_oauth_redirect_uri() . "\n";
if (function_exists("ups_audit_oauth_start_direct")) {
    $u = ups_audit_oauth_start_direct("uri-check", true);
    $parts = wp_parse_url($u);
    parse_str((string) ($parts["query"] ?? ""), $q);
    echo "google_redirect_uri=" . ($q["redirect_uri"] ?? "") . "\n";
    echo "client_id_suffix=" . substr((string) ($q["client_id"] ?? ""), -8) . "\n";
}
