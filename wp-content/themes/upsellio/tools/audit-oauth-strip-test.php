<?php
$uri = "/crm-app/?view=ca-accounts&state=eafdb7ef28b7cba04660c4efe9d4a04d&iss=https://accounts.google.com&code=4/0AdkVLPxrfXlNBlocmCWfT8fMPanZjPWf8D_jR-yfz5Wf1JypiJSnRQ6QsAX06OjRBnQe8g&scope=test";
$qs = "view=ca-accounts&state=eafdb7ef28b7cba04660c4efe9d4a04d&iss=https://accounts.google.com&code=4/0AdkVLPxrfXlNBlocmCWfT8fMPanZjPWf8D_jR-yfz5Wf1JypiJSnRQ6QsAX06OjRBnQe8g&scope=test";
$_SERVER["REQUEST_URI"] = $uri;
$_SERVER["QUERY_STRING"] = $qs;
$_GET = [];
parse_str($qs, $_GET);
echo "code=" . ups_audit_oauth_request_code() . "\n";
echo "state=" . ups_audit_oauth_request_state() . "\n";
echo "is_cb=" . (ups_audit_oauth_is_crm_callback_request() ? "yes" : "no") . "\n";
$stored = ups_audit_oauth_store_callback_query();
echo "stored=" . $stored . "\n";
