<?php

if (!defined("ABSPATH")) {
    exit;
}

/**
 * Zalogowany użytkownik z uprawnieniami redakcyjnymi — nie liczymy go w GA4/GTM ani jako leadów WWW.
 * Filtr: upsellio_is_internal_tracking_user (bool).
 */
function upsellio_is_internal_tracking_user()
{
    if (!function_exists("is_user_logged_in") || !is_user_logged_in()) {
        return false;
    }

    return (bool) apply_filters("upsellio_is_internal_tracking_user", current_user_can("edit_posts"));
}

/**
 * Czy ładować na froncie skrypty GTM/gtag (hardcoded w szablonach + wp_head opcjonalne).
 */
function upsellio_should_load_public_tracking_tags()
{
    if (function_exists("is_admin") && is_admin()) {
        return false;
    }

    return (bool) apply_filters(
        "upsellio_should_load_public_tracking_tags",
        !upsellio_is_internal_tracking_user()
    );
}

function upsellio_body_class_internal_analytics($classes)
{
    if (!is_array($classes)) {
        $classes = [];
    }
    if (upsellio_is_internal_tracking_user()) {
        $classes[] = "upsellio-internal-user";
    }

    return $classes;
}
add_filter("body_class", "upsellio_body_class_internal_analytics");
