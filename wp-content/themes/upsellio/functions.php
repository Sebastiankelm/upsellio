<?php

if (!defined("ABSPATH")) {
    exit;
}

require_once get_template_directory() . "/inc/cities-data.php";
require_once get_template_directory() . "/inc/cities-seed.php";

function upsellio_setup()
{
    add_theme_support("title-tag");
    add_theme_support("post-thumbnails");
    add_theme_support("html5", ["search-form", "comment-form", "comment-list", "gallery", "caption", "style", "script"]);

    register_nav_menus(
        [
            "primary" => __("Primary Menu", "upsellio"),
        ]
    );
}
add_action("after_setup_theme", "upsellio_setup");

function upsellio_assets()
{
    // Szablony front-page i page-audyt-meta maja kompletne style/skrypty inline (wersja 1:1).
    // Zostawiamy hook na przyszle rozszerzenia motywu.
}
add_action("wp_enqueue_scripts", "upsellio_assets");

function upsellio_city_seed_menu()
{
    add_submenu_page(
        "edit.php?post_type=miasto",
        "Generator miast SEO",
        "Generator SEO",
        "manage_options",
        "upsellio-seo-generator",
        "upsellio_city_seed_screen"
    );
}
add_action("admin_menu", "upsellio_city_seed_menu");

function upsellio_city_seed_screen()
{
    if (!current_user_can("manage_options")) {
        return;
    }
    ?>
    <div class="wrap">
      <h1>Generator podstron SEO dla miast</h1>
      <p>Wygeneruj 200 podstron lokalnych opartych o CPT <code>miasto</code>.</p>
      <p><a class="button button-primary" href="<?php echo esc_url(upsellio_get_seed_url(false)); ?>">Uruchom generator (jednorazowo)</a></p>
      <p><a class="button" href="<?php echo esc_url(upsellio_get_seed_url(true)); ?>">Wymus ponowne wygenerowanie</a></p>
      <p>Po uruchomieniu odswiez trwale linki: <strong>Ustawienia -> Bezposrednie odnosniki -> Zapisz</strong>.</p>
    </div>
    <?php
}

