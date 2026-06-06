<?php

if (!defined("ABSPATH")) {
    exit(1);
}

$t = ups_audit_client_technical_signals(965, true);
echo json_encode($t["cwv"] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
