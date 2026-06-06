<?php
$qs = "view=ca-accounts&state=eafdb7ef28b7cba04660c4efe9d4a04d&iss=https://accounts.google.com&code=4/0AdkVLPxrfXlNBlocmCWfT8fMPanZjPWf8D_jR-yfz5Wf1JypiJSnRQ6QsAX06OjRBnQe8g&scope=https://www.googleapis.com/auth/webmasters.readonly";
$_SERVER["QUERY_STRING"] = $qs;
$_SERVER["REQUEST_URI"] = "/crm-app/?" . $qs;
parse_str($qs, $parsed);
echo "parse_str code=" . var_export($parsed["code"] ?? null, true) . "\n";
echo "parse_str view=" . var_export($parsed["view"] ?? null, true) . "\n";
if (function_exists("ups_audit_oauth_request_code")) {
    $_GET = $parsed;
    echo "request_code=" . ups_audit_oauth_request_code() . "\n";
} else {
    echo "oauth fn not loaded (run via wp eval-file)\n";
}
