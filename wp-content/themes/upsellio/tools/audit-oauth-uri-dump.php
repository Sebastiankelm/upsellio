<?php
if (!defined("ABSPATH")) {
    exit(1);
}
wp_set_current_user(1);
echo "redirect_uri=" . ups_audit_oauth_redirect_uri() . "\n";
echo "connect_url=" . ups_audit_oauth_connect_url(true, "test") . "\n";
$url = ups_audit_start_oauth_connect("t", true);
$parts = wp_parse_url($url);
$query = [];
if (!empty($parts["query"])) {
    parse_str((string) $parts["query"], $query);
}
echo "google_redirect_uri=" . (string) ($query["redirect_uri"] ?? "") . "\n";
