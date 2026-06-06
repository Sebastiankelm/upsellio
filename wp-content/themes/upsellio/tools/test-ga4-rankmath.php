<?php
define("WP_USE_THEMES", false);
require dirname(__DIR__, 4) . "/wp-load.php";

$tests = [
    "days" => ["start_date" => "7daysAgo", "end_date" => "yesterday", "days" => true],
    "source" => [
        "start_date" => "7daysAgo",
        "end_date" => "yesterday",
        "dimensions" => [["name" => "sessionSource"]],
        "metrics" => [["name" => "sessions"]],
    ],
    "channel" => [
        "start_date" => "7daysAgo",
        "end_date" => "yesterday",
        "dimensions" => [
            ["name" => "sessionSource"],
            ["name" => "sessionMedium"],
            ["name" => "sessionCampaignName"],
        ],
        "metrics" => [["name" => "sessions"], ["name" => "engagedSessions"]],
    ],
];

foreach ($tests as $name => $opts) {
    $r = \RankMath\Google\Analytics::get_analytics($opts);
    if (is_wp_error($r)) {
        echo "{$name}: ERR " . $r->get_error_message() . "\n";
    } elseif ($r === false) {
        echo "{$name}: false\n";
    } else {
        echo "{$name}: " . count($r) . " rows\n";
    }
}
