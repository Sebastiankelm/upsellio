<?php
define("ABSPATH", __DIR__);
define("DAY_IN_SECONDS",86400);
define("HOUR_IN_SECONDS",3600);
function __($s,$domain="") { return $s; }
function sanitize_key($s) { return preg_replace("/[^a-z0-9_-]/","",strtolower($s)); }
function sanitize_text_field($s) { return trim($s); }
function wp_timezone() { return new DateTimeZone("Europe/Warsaw"); }
function get_post_meta($id,$key,$single=true) { return $GLOBALS["meta"][$id][$key] ?? ""; }
function get_the_title($id) { return "Record " . $id; }
function home_url($path) { return "https://example.test" . $path; }
function add_query_arg($args,$url) { return $url . "?" . http_build_query($args); }
function human_time_diff($a,$b) { return "fixture"; }
function check($ok,$name) { if (!$ok) { fwrite(STDERR,"FAIL: $name\n"); exit(1); } echo "PASS: $name\n"; }
require dirname(__DIR__) . "/wp-content/themes/upsellio/inc/crm-app/dashboard-data.php";
check(upsellio_crm_app_dashboard_source_bucket("google organic") === "SEO", "Google organic is not paid");
check(upsellio_crm_app_dashboard_source_bucket("google cpc") === "Google Ads", "Google CPC classified");
check(upsellio_crm_app_dashboard_source_bucket("facebook paid") === "Meta Ads", "Meta paid classified");
check(upsellio_crm_app_dashboard_source_bucket("   ") === "nieznane", "empty attribution is not direct");
$GLOBALS["meta"] = [
  1 => ["_ups_lead_utm_source"=>"google","_ups_lead_utm_medium"=>"organic","_ups_lead_qualification_status"=>"new","_ups_lead_score_0_100"=>75],
  2 => ["_ups_lead_qualification_status"=>"rejected","_ups_lead_score_0_100"=>95],
  10 => ["_ups_offer_status"=>"sent","_ups_offer_price"=>"1000","_ups_offer_deal_probability_0_100"=>50],
  11 => ["_ups_offer_status"=>"open","_ups_offer_price"=>"2000","_ups_offer_deal_probability_0_100"=>25,"_ups_offer_stage"=>"invalid"],
  20 => ["_upsellio_task_due_at"=>time()-3600,"_upsellio_task_status"=>"open"],
  21 => ["_upsellio_task_due_at"=>time()-3600,"_upsellio_task_status"=>"done"],
];
check(upsellio_crm_app_dashboard_lead_passes_source_filter(1,"seo") && !upsellio_crm_app_dashboard_lead_passes_source_filter(1,"google"), "lead filter matches canonical attribution");
$record = static fn($id) => (object)["ID"=>$id,"post_date_gmt"=>gmdate("Y-m-d H:i:s",time()-60),"post_modified_gmt"=>gmdate("Y-m-d H:i:s",time()-4*DAY_IN_SECONDS),"post_title"=>"Record ".$id];
$payload=upsellio_crm_app_build_dashboard_payload([$record(1),$record(2)],[$record(10),$record(11)],[$record(20),$record(21)],["period"=>"7d","source"=>"all","service"=>"all","trend_days"=>7]);
check($payload["kpi"]["new_leads_delta_pct"] === null, "no invented percent at zero baseline");
check($payload["tasks_due_today_or_overdue"] === 1, "only open due tasks counted");
check($payload["kpi"]["pipeline_weighted_value"] === 1000.0 && $payload["kpi"]["nearest_win_value"] === 500.0, "total weighted pipeline distinct from highest deal");
check(array_sum(array_column($payload["snapshot"],"count")) === 2, "unknown stages retained in pipeline totals");
check(count($payload["hot_leads"]) === 1 && $payload["hot_leads"][0]["post"]->ID === 1, "rejected leads excluded");
check(array_sum($payload["trend"]["sent"]) === 0, "no invented sent timestamp");
echo "Dashboard regression complete.\n";
