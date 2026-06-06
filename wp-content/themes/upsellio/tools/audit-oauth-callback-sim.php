<?php
/**
 * Symulacja powrotu Google na index.php (LiteSpeed-safe).
 * wp eval-file wp-content/themes/upsellio/tools/audit-oauth-callback-sim.php
 */

if (!defined("ABSPATH")) {
    exit(1);
}

$state = "eafdb7ef28b7cba04660c4efe9d4a04d";
$code = "4/0AdkVLPxrfXlNBlocmCWfT8fMPanZjPWf8D_jR-yfz5Wf1JypiJSnRQ6QsAX06OjRBnQe8g";
$qs = "pagename=crm-app&view=ca-accounts&state={$state}&code=" . rawurlencode($code);

$_SERVER["REQUEST_URI"] = "/index.php?" . $qs;
$_SERVER["QUERY_STRING"] = $qs;
$_GET = [];
parse_str($qs, $_GET);

echo "redirect_uri=" . ups_audit_oauth_redirect_uri() . "\n";
echo "code_len=" . strlen(ups_audit_oauth_request_code()) . "\n";
echo "state=" . ups_audit_oauth_request_state() . "\n";
echo "is_return=" . (ups_audit_oauth_is_crm_return_request() ? "yes" : "no") . "\n";
$stored = ups_audit_oauth_store_callback_payload();
echo "stored=" . $stored . "\n";
