<?php
// Portfolio integration fixture without WordPress or live API calls.
define("ABSPATH", __DIR__);
class WP_Post {
    public $ID;
    public $post_type = "crm_client";
    public $post_title;
    public function __construct(int $id) { $this->ID = $id; $this->post_title = "Fixture " . $id; }
}
function ups_audit_collect_profile_client_ids() { return [1, 2, 3, 4, 5]; }
function get_post($id) { return new WP_Post($id); }
function upsellio_crm_app_can_access_audit_client($id) { return $id !== 5; }
function upsellio_crm_url($view, $args) { return "/crm-app/?" . http_build_query($args); }
function ups_audit_aggregate_client_data($id, $days, $offset, $extras) {
    $base = ["health_score" => 85, "usable_resource_count" => 1, "unavailable_resource_count" => 0];
    if ($id === 1) { return ["health_score" => 0, "usable_resource_count" => 0]; }
    if ($id === 2) { return array_merge($base, ["unavailable_resource_count" => 1]); }
    if ($id === 3) {
        $base["intelligence"]["alerts"] = [
            ["severity" => "warning", "title" => "First"],
            ["severity" => "info", "title" => "Info"],
            ["severity" => "critical", "title" => "Third"],
        ];
    }
    return $base;
}
function check($condition, $description) {
    if (!$condition) { fwrite(STDERR, "FAIL: " . $description . PHP_EOL); exit(1); }
    echo "PASS: " . $description . PHP_EOL;
}
require dirname(__DIR__) . "/wp-content/themes/upsellio/inc/client-audit-intelligence.php";
$result = ups_audit_build_command_center(30, true);
$rows = array_column($result["clients"], null, "id");
check($rows[1]["status"] === "unknown" && $rows[1]["health_score"] === null, "no usable source is not a zero health score");
check($rows[2]["status"] === "yellow", "partial source coverage requires attention");
check($rows[3]["status"] === "red", "critical alert sets critical health");
check($rows[4]["status"] === "green", "healthy available data remains green");
check(!isset($rows[5]), "forbidden client is excluded");
check($result["top_alerts"][0]["title"] === "Third", "critical alert beyond first two is retained and ranked");
check(count($result["top_alerts"]) === 2, "informational alerts do not enter actionable queue");
check($result["summary"] === ["total" => 4, "red" => 1, "yellow" => 1, "green" => 1, "unknown" => 1], "portfolio summary matches visible status counts");
