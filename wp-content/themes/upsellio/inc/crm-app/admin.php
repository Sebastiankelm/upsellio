<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_crm_app_admin_entry_menu()
{
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
add_action("admin_menu", "upsellio_crm_app_admin_entry_menu", 3);

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
    remove_menu_page("edit.php?post_type=crm_offer");
    remove_menu_page("edit.php?post_type=crm_client");
    remove_menu_page("edit.php?post_type=ups_followup_template");
    remove_menu_page("edit.php?post_type=crm_offer_layout");
    remove_menu_page("edit.php?post_type=crm_contract_layout");
}
add_action("admin_menu", "upsellio_crm_app_hide_legacy_admin_menus", 999);
