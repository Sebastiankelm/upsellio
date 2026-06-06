<?php
/**
 * Test połączenia OAuth / Google API (CLI, bez przeglądarki).
 * Uruchom: cd public_html && php wp-content/themes/upsellio/tools/test-oauth-connection.php
 */
define("WP_USE_THEMES", false);

$root = dirname(__DIR__, 4);
if (!is_file($root . "/wp-load.php")) {
    fwrite(STDERR, "wp-load.php not found\n");
    exit(1);
}
require $root . "/wp-load.php";

header("Content-Type: text/plain; charset=utf-8");

function ups_test_line(string $label, string $status, string $detail = ""): void
{
    $d = $detail !== "" ? " — {$detail}" : "";
    echo "[{$status}] {$label}{$d}\n";
}

$fail = 0;
$warn = 0;

echo "=== Upsellio — test połączenia (SSH) ===\n";
echo "host: " . home_url("/") . "\n";
echo "time: " . gmdate("Y-m-d H:i:s") . " UTC\n\n";

// --- OAuth config ---
$managed = function_exists("upsellio_google_managed_oauth_is_active") && upsellio_google_managed_oauth_is_active();
$self = $managed && function_exists("upsellio_google_managed_oauth_is_self_hosted") && upsellio_google_managed_oauth_is_self_hosted();
ups_test_line("Upsellio Connect", $managed ? "OK" : "FAIL", $managed ? ($self ? "self-hosted bridge" : "external bridge") : "nieaktywny");
if (!$managed) {
    $fail++;
}

$creds = function_exists("upsellio_get_gsc_credentials") ? upsellio_get_gsc_credentials() : [];
$client_id = trim((string) ($creds["client_id"] ?? ""));
$has_refresh = trim((string) ($creds["refresh_token"] ?? "")) !== "";
ups_test_line("Client ID", $client_id !== "" ? "OK" : "FAIL", $client_id !== "" ? substr($client_id, 0, 20) . "…" : "brak");
if ($client_id === "") {
    $fail++;
}

$audit_uri = function_exists("ups_audit_oauth_redirect_uri") ? ups_audit_oauth_redirect_uri() : "";
$bridge_uri = function_exists("upsellio_google_managed_oauth_bridge_callback_uri")
    ? upsellio_google_managed_oauth_bridge_callback_uri()
    : "";
ups_test_line("Redirect URI audyt CRM", $audit_uri !== "" ? "OK" : "FAIL", $audit_uri);
ups_test_line("Redirect URI most REST", $bridge_uri !== "" ? "OK" : "WARN", $bridge_uri);

// --- OAuth start URL (audit) ---
if (function_exists("ups_audit_oauth_start_direct")) {
    $start = ups_audit_oauth_start_direct("ssh-test", false);
    $parsed = parse_url($start);
    $q = [];
    if (isset($parsed["query"])) {
        parse_str($parsed["query"], $q);
    }
    $ru = rawurldecode((string) ($q["redirect_uri"] ?? ""));
    $match = $ru === $audit_uri;
    ups_test_line("URL startu Google (audit)", $match && strpos($start, "accounts.google.com") !== false ? "OK" : "FAIL", $ru);
    if (!$match) {
        $fail++;
    }
} else {
    ups_test_line("URL startu Google (audit)", "FAIL", "brak ups_audit_oauth_start_direct");
    $fail++;
}

// --- HTTP endpoints ---
$urls = [
    "REST callback" => $bridge_uri,
    "CRM ca-accounts" => $audit_uri,
    "REST index" => rest_url(),
];
foreach ($urls as $label => $url) {
    if ($url === "") {
        continue;
    }
    $resp = wp_remote_head($url, ["timeout" => 15, "redirection" => 0, "sslverify" => true]);
    if (is_wp_error($resp)) {
        ups_test_line("HTTP {$label}", "FAIL", $resp->get_error_message());
        $fail++;
        continue;
    }
    $code = (int) wp_remote_retrieve_response_code($resp);
    $ok = in_array($code, [200, 302, 400, 401], true);
    ups_test_line("HTTP {$label}", $ok ? "OK" : "WARN", "HTTP {$code}");
    if (!$ok) {
        $warn++;
    }
}

// --- Refresh token / API ---
ups_test_line("Refresh token (główne konto)", $has_refresh ? "OK" : "WARN", $has_refresh ? "ustawiony" : "brak — połącz Google w panelu");
if (!$has_refresh) {
    $warn++;
}

if ($has_refresh && function_exists("upsellio_gsc_get_access_token")) {
    $tok = upsellio_gsc_get_access_token($creds);
    if (is_wp_error($tok)) {
        ups_test_line("GSC access token", "FAIL", $tok->get_error_message());
        $fail++;
    } else {
        ups_test_line("GSC access token", "OK", "otrzymany");
        if (function_exists("upsellio_gsc_fetch_rows")) {
            $rows = upsellio_gsc_fetch_rows($creds, 3);
            if (is_wp_error($rows)) {
                ups_test_line("GSC API zapytanie", "FAIL", $rows->get_error_message());
                $fail++;
            } else {
                $n = is_array($rows) ? count($rows) : 0;
                ups_test_line("GSC API zapytanie", $n > 0 ? "OK" : "WARN", "wierszy: {$n}");
                if ($n === 0) {
                    $warn++;
                }
            }
        }
    }
}

// --- CRM Google accounts ---
$accounts = get_posts([
    "post_type" => "crm_google_account",
    "post_status" => "any",
    "posts_per_page" => 20,
    "fields" => "ids",
]);
$n_acc = count($accounts);
ups_test_line("Konta crm_google_account", $n_acc > 0 ? "OK" : "WARN", (string) $n_acc);
if ($n_acc === 0) {
    $warn++;
}

foreach (array_slice($accounts, 0, 3) as $aid) {
    $aid = (int) $aid;
    $email = (string) get_post_meta($aid, "_ups_gacc_email", true);
    $rt = function_exists("ups_audit_get_oauth_for_account")
        ? ups_audit_get_oauth_for_account($aid)
        : [];
    $has_rt = trim((string) ($rt["refresh_token"] ?? "")) !== "";
    ups_test_line("  konto #{$aid}", $has_rt ? "OK" : "WARN", $email !== "" ? $email : get_the_title($aid));
}

echo "\n=== Podsumowanie ===\n";
echo "FAIL: {$fail}  WARN: {$warn}\n";
if ($fail > 0) {
    echo "Krytyczne: OAuth/API nie gotowe. Dodaj redirect URI w Google Cloud (audit_crm_callback z oauth-diag.php).\n";
    exit(1);
}
if ($warn > 0 && !$has_refresh && $n_acc === 0) {
    echo "Infrastruktura OK — brak połączonego konta Google (zaloguj w CRM).\n";
    exit(2);
}
echo "Test zakonczony — infrastruktura OAuth wyglada poprawnie.\n";
exit(0);
