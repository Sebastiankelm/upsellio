<?php

if (!defined("ABSPATH")) {
    exit(1);
}

foreach ([962, 963] as $aid) {
    echo "Account #{$aid}\n";
    $cache = get_post_meta($aid, "_ups_gacc_resources_cache", true);
    foreach ((array) ($cache["ga4"] ?? []) as $node) {
        if (!is_array($node)) {
            continue;
        }
        echo "  " . ($node["account_name"] ?? "") . "\n";
        foreach ((array) ($node["properties"] ?? []) as $p) {
            echo "    - " . ($p["display_name"] ?? "") . " id=" . ($p["id"] ?? "") . "\n";
        }
    }
}

echo "\nResources:\n";
foreach (get_posts(["post_type" => "crm_audit_resource", "posts_per_page" => -1, "post_status" => ["publish", "draft"]]) as $r) {
    echo "#{$r->ID} " . get_post_meta($r->ID, "_ups_resource_type", true) . " "
        . get_post_meta($r->ID, "_ups_resource_external_id", true)
        . " client=" . get_post_meta($r->ID, "_ups_resource_client_id", true) . "\n";
}
