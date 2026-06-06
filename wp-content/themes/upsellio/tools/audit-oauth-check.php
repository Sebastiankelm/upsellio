<?php
if (!defined("ABSPATH")) {
    exit(1);
}

wp_set_current_user(1);
$info = ups_audit_oauth_connection_info();
echo "ready=" . (!empty($info["ready"]) ? "yes" : "no") . " mode=" . ($info["mode"] ?? "") . "\n";
echo "message=" . ($info["message"] ?? "") . "\n";
$url = ups_audit_start_oauth_connect("test", true);
echo "url_start=" . substr($url, 0, 100) . "\n";
echo "is_google=" . (strpos($url, "accounts.google.com") !== false ? "yes" : "no") . "\n";
