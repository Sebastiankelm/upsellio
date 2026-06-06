<?php
/**
 * Plugin Name: Upsellio CRM memory
 * Description: Raises PHP memory limit for /crm-app/ and OAuth admin-post to avoid critical errors on large CRM render.
 */

if (!defined("ABSPATH")) {
    exit;
}

(function (): void {
    $uri = isset($_SERVER["REQUEST_URI"]) ? (string) $_SERVER["REQUEST_URI"] : "";
    $is_crm = $uri !== "" && stripos($uri, "/crm-app") !== false;
    $is_oauth_post = isset($_REQUEST["action"]) && (string) $_REQUEST["action"] === "ups_audit_google_oauth";

    if (!$is_crm && !$is_oauth_post) {
        return;
    }

    if (function_exists("wp_raise_memory_limit")) {
        wp_raise_memory_limit("admin");
    }

    @ini_set("memory_limit", "1024M");
})();
