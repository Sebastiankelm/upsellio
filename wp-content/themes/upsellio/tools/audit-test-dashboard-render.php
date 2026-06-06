<?php
if (!defined("ABSPATH")) {
    exit(1);
}

$cid = 965;
echo "client exists: " . (get_post_type($cid) === "crm_client" ? "yes" : "no") . "\n";

$compare_window = 30;
$current = [];
try {
    $current = ups_audit_aggregate_client_data($cid, $compare_window, 0, false);
    if ($current !== [] && function_exists("ups_audit_attach_dashboard_extras")) {
        $current = ups_audit_attach_dashboard_extras($current, $cid, $compare_window);
    }
} catch (Throwable $e) {
    echo "pipeline FAIL: " . $e->getMessage() . "\n";
    exit(1);
}

echo "current is_array=" . (is_array($current) ? "yes" : "no") . " empty=" . ($current === [] ? "yes" : "no") . "\n";

if ($current === []) {
    echo "empty aggregate — dashboard skipped (OK)\n";
    exit(0);
}

$ca_client_id = $cid;
$ca_client = get_post($cid);

ob_start();
require get_template_directory() . "/inc/crm-app/views/client-audit-dashboard.php";
$html = ob_get_clean();
echo "html length: " . strlen($html) . "\n";
