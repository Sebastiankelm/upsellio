<?php
if (!defined("ABSPATH")) {
    exit;
}

function upsellio_email_is_unsubscribed(string $email): bool
{
    $list = get_option("ups_unsubscribed_emails", []);
    if (!is_array($list)) {
        return false;
    }
    return isset($list[strtolower(trim($email))]);
}

add_action("init", function () {
    $is_get = isset($_GET["ups_unsub"], $_GET["email"]);
    $is_post = isset($_POST["List-Unsubscribe"], $_POST["email"], $_POST["ups_unsub"]);
    if (!$is_get && !$is_post) {
        return;
    }

    $token = sanitize_text_field((string) ($is_get ? $_GET["ups_unsub"] : $_POST["ups_unsub"]));
    $email = sanitize_email((string) ($is_get ? $_GET["email"] : $_POST["email"]));
    if (!is_email($email) || !upsellio_email_verify_unsub_token($email, $token)) {
        wp_die("Link wygasł. Napisz na kontakt@upsellio.pl, jeśli nie chcesz dostawać dalszych wiadomości.");
    }

    $list = get_option("ups_unsubscribed_emails", []);
    if (!is_array($list)) {
        $list = [];
    }
    $list[strtolower($email)] = current_time("mysql");
    update_option("ups_unsubscribed_emails", $list, false);

    if ($is_post) {
        status_header(200);
        exit;
    }

    wp_die(
        "<h1>Wypisany ✓</h1><p>Adres " . esc_html($email) . " został wypisany. Nie dostaniesz już automatycznych wiadomości od Upsellio.</p>",
        "Wypisany",
        200
    );
}, 20);
