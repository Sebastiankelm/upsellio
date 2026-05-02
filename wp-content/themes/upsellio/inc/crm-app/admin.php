<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_crm_app_admin_entry_menu()
{
    if (function_exists("upsellio_admin_hub_slug")) {
        add_submenu_page(
            upsellio_admin_hub_slug(),
            "CRM App",
            "CRM App",
            "edit_posts",
            "upsellio-crm-app-entry",
            "upsellio_crm_app_admin_entry_render",
            2
        );
        return;
    }
    add_menu_page(
        "CRM App",
        "CRM App",
        "edit_posts",
        "upsellio-crm-app-entry",
        "upsellio_crm_app_admin_entry_render",
        "dashicons-chart-area",
        23
    );
}
add_action("admin_menu", "upsellio_crm_app_admin_entry_menu", 9);

function upsellio_crm_app_admin_entry_render()
{
    $target = home_url("/crm-app/");
    wp_safe_redirect($target);
    exit;
}

function upsellio_crm_app_hide_legacy_admin_menus()
{
    if (!is_admin()) {
        return;
    }
    $crm_duplicate_post_type_menus = [
        "edit.php?post_type=crm_offer",
        "edit.php?post_type=crm_client",
        "edit.php?post_type=crm_contract",
        "edit.php?post_type=ups_followup_template",
        "edit.php?post_type=crm_offer_layout",
        "edit.php?post_type=crm_contract_layout",
        "edit.php?post_type=crm_contact",
        "edit.php?post_type=crm_service",
        "edit.php?post_type=crm_lead",
        "edit.php?post_type=crm_prospect",
        "edit.php?post_type=lead_task",
    ];
    foreach ($crm_duplicate_post_type_menus as $menu_slug) {
        remove_menu_page($menu_slug);
    }
}
add_action("admin_menu", "upsellio_crm_app_hide_legacy_admin_menus", 999);
