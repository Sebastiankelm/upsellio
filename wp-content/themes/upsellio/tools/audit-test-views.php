<?php
if (!defined("ABSPATH")) {
    exit(1);
}

wp_set_current_user(1);
$views = [
    "ca-accounts" => [],
    "ca-dashboard" => ["cid" => 965],
    "ca-clients" => [],
];

foreach ($views as $view => $extra) {
    $_GET["view"] = $view;
    foreach ($extra as $k => $v) {
        $_GET[$k] = (string) $v;
    }
    $file = get_template_directory() . "/inc/crm-app/views/client-audit-" . ($view === "ca-accounts" ? "accounts" : ($view === "ca-dashboard" ? "dashboard" : "clients")) . ".php";
    if (!is_readable($file)) {
        echo "{$view}: missing {$file}\n";
        continue;
    }
    ob_start();
    try {
        include $file;
        $len = strlen((string) ob_get_clean());
        echo "{$view}: OK len={$len}\n";
    } catch (Throwable $e) {
        ob_end_clean();
        echo "{$view}: FAIL " . $e->getMessage() . "\n";
    }
}
