<?php
/**
 * wp eval-file wp-content/themes/upsellio/tools/audit-check-scores.php [cid] [days]
 */

if (!defined("ABSPATH")) {
    exit(1);
}

$client_id = isset($args[0]) ? (int) $args[0] : 965;
$days = isset($args[1]) ? (int) $args[1] : 30;
$agg = ups_audit_aggregate_client_data($client_id, $days, 0, true);
$attr = (array) ($agg["attribution_confidence"] ?? []);
$rev = (array) ($agg["revenue_confidence"] ?? []);
echo "attribution=" . (int) ($attr["score"] ?? 0) . "%\n";
echo "revenue_conf=" . (int) ($rev["score"] ?? 0) . "%\n";
$clusters = (array) (($agg["intelligence"]["seo_clusters"] ?? []));
if (!empty($clusters[0]) && is_array($clusters[0])) {
    $c = $clusters[0];
    echo "top_cluster=" . (string) ($c["label"] ?? "") . " variants=" . count((array) ($c["keywords"] ?? [])) . "\n";
    echo "keywords=" . implode(" | ", (array) ($c["keywords"] ?? [])) . "\n";
}
