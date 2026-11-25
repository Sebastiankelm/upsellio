<?php
define("ABSPATH", __DIR__);
function esc_html($v) { return htmlspecialchars((string) $v, ENT_QUOTES, "UTF-8"); }
function esc_attr($v) { return esc_html($v); }
function upsellio_sales_engine_build_decision_layer_analytics() { return $GLOBALS["decision"]; }
function upsellio_sales_engine_build_roas_report_rows() { return $GLOBALS["roas"]; }
function check($ok, $name) { if (!$ok) { fwrite(STDERR, "FAIL: $name\n"); exit(1); } echo "PASS: $name\n"; }
function view($name) { ob_start(); require dirname(__DIR__) . "/wp-content/themes/upsellio/inc/crm-app/views/analytics-" . $name . ".php"; return ob_get_clean(); }
$GLOBALS["decision"] = ["owners" => ["<owner>" => ["deals" => 10, "won" => 2, "lost" => 3, "revenue" => 1200]], "forecast_weighted" => 250, "time_to_close_days" => ["count" => 0]];
$html = view("sales");
check(strpos($html, "40,0%") !== false, "win rate uses closed deals");
check(strpos($html, "&lt;owner&gt;") !== false, "owner names escaped");
check(strpos($html, "Active MRR</span><b>—") !== false, "missing MRR not fabricated");
check(strpos($html, "Przełącznik dni nie filtruje") !== false, "historical scope explicit");
$GLOBALS["decision"] = ["owners" => [], "forecast_weighted" => 0, "time_to_close_days" => ["count" => 0]];
check(strpos(view("sales"), "Win rate (zamknięte)</span><b>—") !== false, "no closed deals has no win rate");
$GLOBALS["roas"] = [
 ["spend" => 100, "revenue" => 300, "roas" => 3, "roi_pct" => 200],
 ["spend" => 300, "revenue" => 300, "roas" => 1, "roi_pct" => 0],
 ["spend" => 0, "revenue" => 9000, "roas" => 90000000, "roi_pct" => 9000000000],
];
$html = view("roas");
check(strpos($html, "1,50x") !== false, "weighted ROAS excludes unmatched revenue");
check(strpos($html, "9 000,00 zł") !== false, "unmatched revenue shown separately");
check(strpos($html, "90 000 000") === false, "zero spend does not expose artificial ROAS");
echo "Analytics regression complete.\n";
