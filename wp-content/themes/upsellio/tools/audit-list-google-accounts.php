<?php
/**
 * Lista kont Google OAuth + zasoby + GA4/GSC tree per konto.
 * wp eval-file wp-content/themes/upsellio/tools/audit-list-google-accounts.php
 */

if (!defined("ABSPATH")) {
    exit(1);
}

$accounts = get_posts([
    "post_type" => "crm_google_account",
    "posts_per_page" => -1,
    "post_status" => "publish",
    "orderby" => "ID",
    "order" => "ASC",
]);

echo "=== Google accounts (" . count($accounts) . ") ===\n\n";

foreach ($accounts as $acc) {
    if (!($acc instanceof WP_Post)) {
        continue;
    }
    $aid = (int) $acc->ID;
    $email = (string) get_post_meta($aid, "_ups_gacc_email", true);
    $label = (string) get_post_meta($aid, "_ups_gacc_label", true);
    $scopes = get_post_meta($aid, "_ups_gacc_scopes", true);
    $has_adwords = is_array($scopes) && in_array("https://www.googleapis.com/auth/adwords", $scopes, true);
    $has_analytics = is_array($scopes) && in_array("https://www.googleapis.com/auth/analytics.readonly", $scopes, true);
    $has_gsc = is_array($scopes) && in_array("https://www.googleapis.com/auth/webmasters.readonly", $scopes, true);

    echo "#{$aid} label=" . ($label !== "" ? $label : "(brak)") . " email=" . ($email !== "" ? $email : "(brak)") . "\n";
    echo "  scopes: adwords=" . ($has_adwords ? "yes" : "no")
        . " analytics=" . ($has_analytics ? "yes" : "no")
        . " gsc=" . ($has_gsc ? "yes" : "no") . "\n";

    $resources = function_exists("ups_audit_get_google_account_resources")
        ? ups_audit_get_google_account_resources($aid)
        : [];
    echo "  resources: " . count($resources) . "\n";
    foreach ($resources as $res) {
        if (!($res instanceof WP_Post)) {
            continue;
        }
        $rid = (int) $res->ID;
        $type = (string) get_post_meta($rid, "_ups_resource_type", true);
        $ext = (string) get_post_meta($rid, "_ups_resource_external_id", true);
        $client = (int) get_post_meta($rid, "_ups_resource_client_id", true);
        $cache = get_post_meta($rid, "_ups_resource_data_cache", true);
        $err = is_array($cache) ? trim((string) ($cache["error"] ?? "")) : "";
        echo "    #{$rid} {$type} ext={$ext} client={$client} err=" . ($err !== "" ? substr($err, 0, 80) : "ok") . "\n";
    }

    echo "\n  GA4 properties:\n";
    $ga4 = ups_audit_fetch_ga4_resources($aid);
    foreach ((array) $ga4 as $gacc) {
        if (!is_array($gacc)) {
            continue;
        }
        echo "    Account " . ($gacc["account_id"] ?? "?") . " — " . ($gacc["account_name"] ?? "") . "\n";
        foreach ((array) ($gacc["properties"] ?? []) as $prop) {
            if (!is_array($prop)) {
                continue;
            }
            $flag = "";
            if ((string) ($gacc["account_id"] ?? "") === "351402842") {
                $flag = " <-- wtapes account";
            }
            if (stripos((string) ($prop["display_name"] ?? ""), "wtapes") !== false
                || stripos((string) ($prop["display_name"] ?? ""), "tape") !== false) {
                $flag = " <-- wtapes?";
            }
            echo "      property " . ($prop["id"] ?? "?") . " — " . ($prop["display_name"] ?? "") . $flag . "\n";
        }
    }

    echo "\n  GSC sites:\n";
    $gsc = ups_audit_fetch_gsc_resources($aid);
    foreach ((array) $gsc as $site) {
        if (!is_array($site)) {
            continue;
        }
        $url = (string) ($site["site_url"] ?? "");
        $verified = !empty($site["is_verified"]) ? "verified" : "unverified";
        $perm = (string) ($site["permission_level"] ?? "");
        $flag = stripos($url, "wtapes") !== false ? " <-- wtapes" : "";
        echo "    {$url} ({$verified}, {$perm}){$flag}\n";
    }
    echo "\n";
}

echo "=== wtapes client #965 resources ===\n";
foreach (ups_audit_get_client_resources(965) as $res) {
    if (!($res instanceof WP_Post)) {
        continue;
    }
    $rid = (int) $res->ID;
    echo "#{$rid} " . get_post_meta($rid, "_ups_resource_type", true)
        . " gacc=" . get_post_meta($rid, "_ups_resource_google_account_id", true)
        . " ext=" . get_post_meta($rid, "_ups_resource_external_id", true)
        . " title=" . $res->post_title . "\n";
}
