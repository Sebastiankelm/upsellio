<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_crm_app_user_can_access()
{
    return is_user_logged_in() && (current_user_can("manage_options") || current_user_can("edit_posts"));
}

function upsellio_crm_app_ensure_page()
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }
    $slug = "crm-app";
    $page = get_page_by_path($slug);
    if ($page instanceof WP_Post) {
        return;
    }
    wp_insert_post([
        "post_type" => "page",
        "post_status" => "publish",
        "post_name" => $slug,
        "post_title" => "CRM App",
        "post_content" => "",
    ]);
}
add_action("admin_init", "upsellio_crm_app_ensure_page");

function upsellio_crm_app_register_query_var($vars)
{
    $vars[] = "upsellio_crm_app";
    return $vars;
}
add_filter("query_vars", "upsellio_crm_app_register_query_var");
