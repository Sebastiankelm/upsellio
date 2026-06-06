<?php
if (!defined("ABSPATH")) {
    exit(1);
}

wp_set_current_user(1);
echo ups_audit_oauth_admin_connect_url(true, "wtapes Google Ads") . "\n";
