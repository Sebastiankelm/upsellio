<?php
/**
 * Ustawia GA4 ID, włącza sync CRM, opcjonalnie GSC property; generuje link OAuth dla admina.
 * php wp-content/themes/upsellio/tools/bootstrap-google-settings.php [login]
 */

define("WP_USE_THEMES", false);
require dirname(__DIR__, 4) . "/wp-load.php";

$login = isset($argv[1]) ? (string) $argv[1] : "vxiuqc";
$user = get_user_by("login", $login);
if (!$user) {
    $admins = get_users(["role" => "administrator", "number" => 1]);
    $user = $admins[0] ?? null;
}
if (!$user) {
    fwrite(STDERR, "Brak użytkownika admin.\n");
    exit(1);
}

wp_set_current_user((int) $user->ID);

$ga4_id = "535285648";
upsellio_save_ga4_property_id($ga4_id);
update_option("ups_automation_ga4_sync_enabled", "1", false);

$rm_profile = get_option("rank_math_google_analytic_profile", []);
$gsc_property = is_array($rm_profile) && trim((string) ($rm_profile["profile"] ?? "")) !== ""
    ? trim((string) $rm_profile["profile"])
    : "sc-domain:upsellio.pl";

$creds = upsellio_get_gsc_credentials();
upsellio_save_gsc_credentials(
    (string) ($creds["client_id"] ?? ""),
    (string) ($creds["client_secret"] ?? ""),
    (string) ($creds["refresh_token"] ?? ""),
    $gsc_property
);

echo "GA4 property ID: " . upsellio_get_ga4_property_id() . "\n";
echo "GA4 sync CRM: " . get_option("ups_automation_ga4_sync_enabled", "?") . "\n";
echo "GSC property: " . $gsc_property . "\n";
echo "Refresh token: " . (trim((string) ($creds["refresh_token"] ?? "")) !== "" ? "TAK" : "BRAK — wymagane OAuth") . "\n";

// Próba importu tokena Rank Math → Upsellio (ten sam projekt GCP).
if (trim((string) ($creds["refresh_token"] ?? "")) === "" && class_exists("\RankMath\Data_Encryption")) {
    $rm_tokens = get_option("rank_math_google_oauth_tokens", []);
    if (is_array($rm_tokens)) {
        $rt = trim((string) ($rm_tokens["refresh_token"] ?? ""));
        if ($rt !== "" && class_exists("\RankMath\Data_Encryption")) {
            $rt = \RankMath\Data_Encryption::deep_decrypt($rt);
        }
        if ($rt !== "" && (string) ($creds["client_id"] ?? "") !== "" && (string) ($creds["client_secret"] ?? "") !== "") {
            $test = upsellio_gsc_get_access_token([
                "client_id" => $creds["client_id"],
                "client_secret" => $creds["client_secret"],
                "refresh_token" => $rt,
            ], "bootstrap-rm");
            if (!is_wp_error($test) && $test !== "") {
                upsellio_save_gsc_credentials(
                    (string) $creds["client_id"],
                    (string) $creds["client_secret"],
                    $rt,
                    $gsc_property
                );
                echo "Import Rank Math refresh_token: SUKCES\n";
            } else {
                $msg = is_wp_error($test) ? $test->get_error_message() : "pusty access token";
                echo "Import Rank Math refresh_token: nie działa z klientem Upsellio ({$msg})\n";
            }
        }
    }
}

$creds = upsellio_get_gsc_credentials();
if (trim((string) ($creds["refresh_token"] ?? "")) !== "") {
    echo "OAuth: połączone.\n";
    exit(0);
}

$state = bin2hex(random_bytes(16));
set_transient(
    upsellio_google_oauth_transient_key((int) $user->ID),
    [
        "state" => $state,
        "gsc_property" => $gsc_property,
        "ga4_property_id" => $ga4_id,
    ],
    15 * MINUTE_IN_SECONDS
);

$redirect_uri = upsellio_google_oauth_redirect_uri();
$auth_url = add_query_arg(
    [
        "client_id" => $creds["client_id"],
        "redirect_uri" => $redirect_uri,
        "response_type" => "code",
        "scope" => upsellio_google_oauth_scope_string(),
        "access_type" => "offline",
        "prompt" => "consent",
        "include_granted_scopes" => "true",
        "state" => $state,
    ],
    "https://accounts.google.com/o/oauth2/v2/auth"
);

echo "\n=== Otwórz w przeglądarce (zalogowany jako {$user->user_login}) ===\n";
echo $auth_url . "\n";
echo "Redirect URI: {$redirect_uri}\n";
echo "Admin po callback: " . upsellio_site_analytics_admin_url(["upsellio_google_connected" => "1"]) . "\n";

exit(2);
