<?php
 define("ABSPATH", __DIR__);
function check($condition, $description) { if (!$condition) { fwrite(STDERR, "FAIL: " . $description . PHP_EOL); exit(1); } echo "PASS: " . $description . PHP_EOL; }

function add_action(...$args) {}
require dirname(__DIR__) . "/wp-content/themes/upsellio/inc/client-audit-sync.php";
$mer = ["ga4_revenue_source" => "purchaseRevenue", "ga4_purchase_window_verified" => true, "ga4_purchase_source_count" => 1, "ga4_revenue" => 12000, "paid_cost" => 3000, "unavailable_resource_count" => 0];
check(ups_audit_sitewide_mer($mer) === 4.0, "sitewide MER uses combined paid spend");
check(ups_audit_sitewide_mer(array_replace($mer, ["paid_cost" => 0])) === null, "zero spend yields unknown MER");
check(ups_audit_sitewide_mer(array_replace($mer, ["ga4_revenue" => 0])) === 0.0, "observed zero purchase revenue yields zero MER");
check(ups_audit_sitewide_mer(array_replace($mer, ["ga4_revenue_source" => "sessionTotalRevenue"])) === null, "session revenue is not purchase revenue");
check(ups_audit_sitewide_mer(array_replace($mer, ["unavailable_resource_count" => 1])) === null, "partial sources suppress MER");
check(ups_audit_sitewide_mer(array_replace($mer, ["ga4_purchase_window_verified" => false])) === null, "mismatched purchase window suppresses MER");
check(ups_audit_sitewide_mer(array_replace($mer, ["ga4_purchase_source_count" => 2])) === null, "potentially overlapping GA4 properties suppress MER");
