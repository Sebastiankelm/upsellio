<?php
/**
 * Smoke test: OAuth helpers load without fatal redeclare.
 * wp eval-file wp-content/themes/upsellio/tools/audit-oauth-smoke.php
 */

if (!defined("ABSPATH")) {
    fwrite(STDERR, "Run via: wp eval-file .../audit-oauth-smoke.php\n");
    exit(1);
}

$checks = [
    "ups_audit_oauth_start_direct" => function_exists("ups_audit_oauth_start_direct"),
    "ups_audit_google_oauth_handle" => function_exists("ups_audit_google_oauth_handle"),
    "UPS_AUDIT_OAUTH_ACTION" => defined("UPS_AUDIT_OAUTH_ACTION"),
];

foreach ($checks as $name => $ok) {
    echo ($ok ? "OK" : "FAIL") . " {$name}\n";
}

if (function_exists("ups_audit_oauth_redirect_uri")) {
    echo "redirect_uri=" . ups_audit_oauth_redirect_uri() . "\n";
}

$fail = in_array(false, $checks, true);
exit($fail ? 1 : 0);
