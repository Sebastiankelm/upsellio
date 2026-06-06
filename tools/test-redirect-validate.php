<?php
$url = "https://upsellio.pl/crm-app/?view=ca-accounts&ups_audit_connect=1&include_ads=1&label=wtapes-google-ads";
echo "validate: " . var_export(wp_validate_redirect($url, home_url("/crm-app/")), true) . PHP_EOL;
$enc = "https%3A%2F%2Fupsellio.pl%2Fcrm-app%2F%3Fview%3Dca-accounts%26ups_audit_connect%3D1%26include_ads%3D1%26label%3Dwtapes-google-ads";
echo "encoded literal connect: " . (strpos($enc, "ups_audit_connect=1") !== false ? "yes" : "no") . PHP_EOL;
echo "decoded connect: " . (strpos(rawurldecode($enc), "ups_audit_connect=1") !== false ? "yes" : "no") . PHP_EOL;
