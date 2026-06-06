<?php
/**
 * wp eval-file wp-content/themes/upsellio/tools/audit-test-intel.php [cid] [days]
 */

if (!defined("ABSPATH")) {
    exit(1);
}

$cid = isset($args[0]) ? (int) $args[0] : 965;
$days = isset($args[1]) ? (int) $args[1] : 30;
$agg = ups_audit_aggregate_client_data($cid, $days, 0, false);
$agg = ups_audit_attach_intelligence($agg, $cid, ups_audit_client_setup_status($cid));
$intel = (array) ($agg["intelligence"] ?? []);
$opp = (array) ($intel["opportunity"] ?? []);
$st = (array) ($intel["search_terms"] ?? []);
$crm = (array) ($intel["crm_revenue"] ?? []);
$clusters = (array) ($intel["seo_clusters"] ?? []);

echo "opportunity=" . (int) ($opp["score"] ?? 0) . " (" . (string) ($opp["label"] ?? "") . ")\n";
echo "exclude_terms=" . count((array) ($st["exclude_candidates"] ?? [])) . "\n";
echo "watch_terms=" . count((array) ($st["watch"] ?? [])) . "\n";
echo "seo_clusters=" . count($clusters) . "\n";
if ($clusters !== []) {
    $c0 = $clusters[0];
    echo "top_cluster=" . (string) ($c0["label"] ?? "") . " +" . (int) ($c0["potential_clicks"] ?? 0) . " variants=" . count((array) ($c0["keywords"] ?? [])) . "\n";
}
echo "crm_rows=" . count((array) ($crm["rows"] ?? [])) . "\n";
$journey = (array) ($intel["customer_journey"] ?? []);
echo "journey_stages=" . count((array) ($journey["stages"] ?? [])) . " crm=" . (!empty($journey["has_crm"]) ? "1" : "0") . "\n";
