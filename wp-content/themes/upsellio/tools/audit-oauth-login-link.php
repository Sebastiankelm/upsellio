<?php
if (!defined("ABSPATH")) {
    exit(1);
}

echo "LOGIN_THEN_GOOGLE=" . ups_audit_oauth_login_entry_url(true, "wtapes Google Ads") . "\n";
echo "IF_LOGGED_IN=" . ups_audit_oauth_direct_connect_return_url(true, "wtapes Google Ads") . "\n";
