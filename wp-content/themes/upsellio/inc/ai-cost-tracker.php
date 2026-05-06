<?php
if (!defined("ABSPATH")) {
    exit;
}

/**
 * Pricing per 1M tokenów (USD). Aktualizuj przy zmianie cennika Anthropic.
 */
function upsellio_ai_pricing_table(): array
{
    return [
        "claude-haiku-4-5" => ["in" => 1.00, "out" => 5.00, "cache_read" => 0.10],
        "claude-sonnet-4-5" => ["in" => 3.00, "out" => 15.00, "cache_read" => 0.30],
        "claude-sonnet-4-6" => ["in" => 3.00, "out" => 15.00, "cache_read" => 0.30],
        "claude-opus-4-7" => ["in" => 15.00, "out" => 75.00, "cache_read" => 1.50],
    ];
}

/**
 * Wywołuj po każdym successful API response. $usage z odpowiedzi Anthropic.
 *
 * @param string $model
 * @param array  $usage  ['input_tokens' => N, 'output_tokens' => N, 'cache_read_input_tokens' => N]
 * @param string $task   nazwa funkcji wywołującej (np. 'pre_call_brief', 'lead_scoring')
 */
function upsellio_ai_record_usage(string $model, array $usage, string $task): void
{
    $usd_to_pln = (float) apply_filters("upsellio_ai_usd_pln", 4.0);
    $pricing = upsellio_ai_pricing_table();

    $base_model = "";
    foreach ($pricing as $key => $_) {
        if (strpos($model, $key) === 0) {
            $base_model = $key;
            break;
        }
    }
    if ($base_model === "") {
        return;
    }
    $rate = $pricing[$base_model];

    $in = (int) ($usage["input_tokens"] ?? 0);
    $out = (int) ($usage["output_tokens"] ?? 0);
    $cache_in = (int) ($usage["cache_read_input_tokens"] ?? 0);

    $cost_usd = ($in * $rate["in"] / 1000000)
        + ($out * $rate["out"] / 1000000)
        + ($cache_in * $rate["cache_read"] / 1000000);
    $cost_pln = $cost_usd * $usd_to_pln;

    $today = current_time("Y-m-d");
    $month = current_time("Y-m");

    $daily_key = "ups_ai_spend_d_{$today}";
    $monthly_key = "ups_ai_spend_m_{$month}";

    update_option($daily_key, ((float) get_option($daily_key, 0)) + $cost_pln, false);
    update_option($monthly_key, ((float) get_option($monthly_key, 0)) + $cost_pln, false);

    $task_log = get_option("ups_ai_spend_tasks_" . $month, []);
    if (!is_array($task_log)) {
        $task_log = [];
    }
    $task_log[$task] = ($task_log[$task] ?? 0) + $cost_pln;
    update_option("ups_ai_spend_tasks_" . $month, $task_log, false);

    $log = get_option("ups_ai_spend_log", []);
    if (!is_array($log)) {
        $log = [];
    }
    array_unshift($log, [
        "ts" => current_time("mysql"),
        "task" => $task,
        "model" => $base_model,
        "in" => $in,
        "out" => $out,
        "cache" => $cache_in,
        "pln" => round($cost_pln, 4),
    ]);
    if (count($log) > 1000) {
        $log = array_slice($log, 0, 1000);
    }
    update_option("ups_ai_spend_log", $log, false);
}

/**
 * Hard cap — wywołaj PRZED każdym wywołaniem AI. Zwraca true jeśli można jechać.
 */
function upsellio_ai_can_call(string $task, float $estimated_pln = 0.20): bool
{
    $month = current_time("Y-m");
    $current = (float) get_option("ups_ai_spend_m_{$month}", 0);
    $budget = (float) get_option("ups_ai_monthly_budget_pln", 300);

    if ($budget <= 0) {
        return true;
    }

    if (($current + $estimated_pln) > $budget) {
        error_log("[ai-cost-tracker] BUDGET BLOCK: task={$task}, current={$current}, est={$estimated_pln}, budget={$budget}");
        $alerts = get_option("ups_ai_budget_alerts", []);
        if (!is_array($alerts)) {
            $alerts = [];
        }
        $alerts[] = [
            "ts" => current_time("mysql"),
            "task" => $task,
            "current" => $current,
            "budget" => $budget,
        ];
        if (count($alerts) > 50) {
            $alerts = array_slice($alerts, -50);
        }
        update_option("ups_ai_budget_alerts", $alerts, false);

        return false;
    }

    return true;
}

function upsellio_ai_can_call_strict_global(string $task, float $estimated_pln = 0.20): bool
{
    if (!upsellio_ai_can_call($task, $estimated_pln)) {
        return false;
    }

    $today = current_time("Y-m-d");
    $today_key = "ups_ai_anon_calls_" . $today;
    $daily_calls = (int) get_transient($today_key);
    if ($daily_calls >= 100) {
        return false;
    }

    set_transient($today_key, $daily_calls + 1, DAY_IN_SECONDS);
    return true;
}

function upsellio_prune_option_log_by_ts(string $option_name, int $max_items = 1000, string $time_key = "ts"): void
{
    $rows = get_option($option_name, []);
    if (!is_array($rows)) {
        return;
    }
    $cutoff = strtotime("-90 days");
    $filtered = [];
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $ts_raw = (string) ($row[$time_key] ?? "");
        $ts = strtotime($ts_raw);
        if ($ts !== false && $ts >= $cutoff) {
            $filtered[] = $row;
        }
    }
    if (count($filtered) > $max_items) {
        $filtered = array_slice($filtered, -$max_items);
    }
    update_option($option_name, array_values($filtered), false);
}

add_action("upsellio_log_prune_daily", function () {
    if (function_exists("upsellio_prune_option_log_by_ts")) {
        upsellio_prune_option_log_by_ts("ups_ai_spend_log", 1000, "ts");
        upsellio_prune_option_log_by_ts("ups_ai_anomaly_explanations", 1000, "detected_at");
    }
});

add_action("init", function () {
    if (!wp_next_scheduled("upsellio_log_prune_daily")) {
        wp_schedule_event(time() + DAY_IN_SECONDS, "daily", "upsellio_log_prune_daily");
    }
}, 30);
