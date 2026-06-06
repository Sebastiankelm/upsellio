<?php
if (!defined("ABSPATH")) {
    exit(1);
}

$_SERVER["REQUEST_URI"] = "/crm-app/?view=ca-accounts";
$_GET["view"] = "ca-accounts";

$user = get_user_by("login", "admin");
if (!$user) {
    $users = get_users(["role" => "administrator", "number" => 1]);
    $user = $users[0] ?? null;
}
if ($user instanceof WP_User) {
    wp_set_current_user($user->ID);
}

ob_start();
try {
    if (function_exists("upsellio_crm_render")) {
        upsellio_crm_render();
    } else {
        echo "no upsellio_crm_render\n";
    }
} catch (Throwable $e) {
    echo "THROW: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
}
$out = ob_get_clean();
echo "len=" . strlen($out) . " fatal_word=" . (strpos($out, "błąd krytyczny") !== false ? "yes" : "no") . "\n";
if (strlen($out) < 500) {
    echo $out;
}
