<?php
if (!defined("ABSPATH")) {
    exit;
}

$GLOBALS["upsellio_forced_error_code"] = 404;
$GLOBALS["upsellio_error_context"] = function_exists("upsellio_prepare_error_page_context")
    ? upsellio_prepare_error_page_context(404)
    : [];
require get_template_directory() . "/template-error-modern.php";
