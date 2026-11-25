<?php
define("ABSPATH", __DIR__);
function esc_html($v) { return htmlspecialchars((string) $v, ENT_QUOTES, "UTF-8"); }
function esc_attr($v) { return esc_html($v); }
function esc_js($v) { return addslashes($v); }
function esc_url($v) { return esc_html($v); }
function wp_json_encode($v, $flags = 0) { return json_encode($v, $flags); }
function wp_timezone() { return new DateTimeZone("Europe/Warsaw"); }
function wp_create_nonce($v) { return "fixture"; }
function admin_url($p) { return "https://example.test/wp-admin/" . $p; }
function current_user_can($v) { return $GLOBALS["admin"]; }
function get_option($k, $d = false) { return $d; }
function upsellio_rankmath_get_dashboard_summary($days) { $GLOBALS["called"]++; return $GLOBALS["summary"]; }
function upsellio_rankmath_gsc_daily_series($days) {
    $now = new DateTimeImmutable("today", wp_timezone());
    return [$now->modify("-40 days")->format("Y-m-d") => ["clicks" => 9999], $now->format("Y-m-d") => ["clicks" => 3, "impressions" => 100]];
}
function check($ok, $name) { if (!$ok) { fwrite(STDERR, "FAIL: $name\n"); exit(1); } echo "PASS: $name\n"; }
function widget($range_days) { ob_start(); require dirname(__DIR__) . "/wp-content/themes/upsellio/inc/crm-app/views/rankmath-seo-widgets.php"; return ob_get_clean(); }
$GLOBALS["admin"] = true;
$GLOBALS["called"] = 0;
$GLOBALS["summary"] = ["source" => "rank_math_widget",
 "clicks" => ["total" => 2, "previous" => 0, "difference" => 2],
 "impressions" => ["total" => 6266, "previous" => 5000, "difference" => 6266],
 "position" => ["total" => 46.8, "previous" => 50, "difference" => 46.82],
 "keywords" => ["total" => 0, "na" => true],
];
$html = widget(30);
check(strpos($html, "+25,3%") !== false && strpos($html, "+6266%") === false, "SEO percent uses actual totals");
check(strpos($html, "-3,2 poz.") !== false, "SEO position delta uses rank points");
check(strpos($html, "Brak porównywalnego okresu") !== false, "no comparison for absent baseline");
check(strpos($html, "9999") === false && strpos($html, '"clicks":[3]') !== false, "trend excludes dates outside current period");
check(strpos($html, 'id="ups-rm-sync-btn"') !== false, "administrator sees sync control");
$GLOBALS["admin"] = false;
check(strpos(widget(30), 'id="ups-rm-sync-btn"') === false, "non-admin does not see global sync");
$before = $GLOBALS["called"];
$html = widget(365);
check($GLOBALS["called"] === $before && strpos($html, "365 dni") !== false, "unsupported period never fetches 30-day summary");
$seo = file_get_contents(dirname(__DIR__) . "/wp-content/themes/upsellio/inc/crm-app/views/analytics-seo.php");
check(strpos($seo, "safeUrl(r.url)") !== false && strpos($seo, "&#39;") !== false, "keyword link validation and attribute escape present");
check(strpos($seo, "csvCell(r.keyword)") !== false && strpos($seo, "URL.revokeObjectURL") !== false, "CSV escape and object URL cleanup present");
echo "SEO regression complete.\n";
