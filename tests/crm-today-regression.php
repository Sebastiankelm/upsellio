<?php
// Render daily report using deterministic fixtures, without WordPress or network.
define("ABSPATH", __DIR__);
function get_option($key, $default = false) { return $GLOBALS["opts"][$key] ?? $default; }
function esc_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8"); }
function esc_attr($value) { return esc_html($value); }
function esc_url($value) { return esc_html($value); }
function home_url($path) { return "https://example.test" . $path; }
function add_query_arg($args, $url) { return $url . "?" . http_build_query($args); }
function upsellio_crm_url($view, $args) { return add_query_arg(["view" => $view] + $args, home_url("/crm-app/")); }
function upsellio_crm_app_collect_metric_posts($type, $args) { return []; }
function upsellio_crm_app_prime_post_meta_cache($posts) {}
function upsellio_rankmath_get_dashboard_summary($days) { return $GLOBALS["summary"]; }
function upsellio_gsc_visibility_stats($rows) { return ["total" => 212, "top3" => 3, "top10" => 10]; }
function check($ok, $name) { if (!$ok) { fwrite(STDERR, "FAIL: $name\n"); exit(1); } echo "PASS: $name\n"; }
function render_today() { ob_start(); require dirname(__DIR__) . "/wp-content/themes/upsellio/inc/crm-app/views/analytics-today.php"; return ob_get_clean(); }
$GLOBALS["opts"] = [
  "ups_ai_today_brief" => "# Podsumowanie\n<script>alert(1)</script>",
  "ups_gsc_indexation_summary" => ["submitted" => 335, "indexed" => 0],
  "upsellio_keyword_metrics_source" => "gsc_rankmath_bridge",
];
$GLOBALS["summary"] = [
  "source" => "rank_math_widget",
  "clicks" => ["total" => 2, "previous" => 0, "difference" => 2],
  "impressions" => ["total" => 6266, "previous" => 5000, "difference" => 6266],
  "position" => ["total" => 46.8, "previous" => 50, "difference" => 46.82],
  "keywords" => ["total" => 0, "previous" => 0, "difference" => 0, "na" => true],
];
$html = render_today();
check(strpos($html, "crm-today-board") !== false && substr_count($html, "card crm-today-panel") === 5, "five independently rendered dashboard panels");
check(strpos($html, "# Podsumowanie") === false && strpos($html, "&lt;script&gt;") !== false, "brief headings cleaned and HTML escaped");
check(strpos($html, "+25,3%") !== false && strpos($html, "+6266%") === false, "percentage derived from actual totals");
check(strpos($html, "-3,2 poz.") !== false, "rank changes shown in positions, not percent");
check(strpos($html, "Brak porównywalnego okresu") !== false, "zero baseline does not invent percent");
check(strpos($html, "Widoczność zapisanych fraz") !== false && strpos($html, "212") !== false, "keyword dataset labelled independently");
$GLOBALS["summary"] = ["source" => "empty", "clicks" => ["total" => 0]];
$html = render_today();
check(strpos($html, "Brak dostępnego podsumowania okresu") !== false, "missing data not displayed as real zero");
echo "Daily report regression complete.\n";
