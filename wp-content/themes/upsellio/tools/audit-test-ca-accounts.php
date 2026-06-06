<?php
if (!defined("ABSPATH")) {
    exit(1);
}

$uid = 1;
wp_set_current_user($uid);

echo "prime resources #963...\n";
if (function_exists("ups_audit_oauth_prime_account_resources")) {
    try {
        ups_audit_oauth_prime_account_resources(963);
        echo "prime OK\n";
    } catch (Throwable $e) {
        echo "prime FAIL: " . $e->getMessage() . "\n";
    }
}

echo "render ca-accounts view...\n";
$view_file = get_template_directory() . "/inc/crm-app/views/client-audit-accounts.php";
ob_start();
try {
    include $view_file;
    $html = ob_get_clean();
    echo "render OK len=" . strlen($html) . "\n";
} catch (Throwable $e) {
    ob_end_clean();
    echo "render FAIL: " . $e->getMessage() . " @ " . $e->getFile() . ":" . $e->getLine() . "\n";
}
