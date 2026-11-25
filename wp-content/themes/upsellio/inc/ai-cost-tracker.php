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

/** Atomic numeric option updates, including when PHP requests run concurrently. */
function upsellio_ai_increment_spend_option(string $key, float $amount): bool
{
    global $wpdb;
    $amount = number_format(max(0, $amount), 8, ".", "");
    $result = $wpdb->query($wpdb->prepare(
        "INSERT INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'no')
         ON DUPLICATE KEY UPDATE option_value = CAST(option_value AS DECIMAL(24,8)) + CAST(%s AS DECIMAL(24,8))",
        $key, $amount, $amount
    ));
    wp_cache_delete($key, "options");
    wp_cache_delete("notoptions", "options");
    wp_cache_delete("alloptions", "options");
    return $result !== false;
}

/** Read the accounting total directly, avoiding stale persistent option caches. */
function upsellio_ai_read_spend_option(string $key): float
{
    global $wpdb;
    return (float) $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", $key));
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

    $in = max(0, (int) ($usage["input_tokens"] ?? 0));
    $out = max(0, (int) ($usage["output_tokens"] ?? 0));
    $cache_in = max(0, (int) ($usage["cache_read_input_tokens"] ?? 0));

    $cost_usd = ($in * $rate["in"] / 1000000)
        + ($out * $rate["out"] / 1000000)
        + ($cache_in * $rate["cache_read"] / 1000000);
    $cost_pln = $cost_usd * $usd_to_pln;

    $today = current_time("Y-m-d");
    $month = current_time("Y-m");

    $daily_key = "ups_ai_spend_d_{$today}";
    $monthly_key = "ups_ai_spend_m_{$month}";

    $daily_ok = upsellio_ai_increment_spend_option($daily_key, $cost_pln);
    $monthly_ok = upsellio_ai_increment_spend_option($monthly_key, $cost_pln);
    if (!$daily_ok || !$monthly_ok) {
        error_log("[ai-cost-tracker] Failed to persist AI spend counters.");
    }

    global $wpdb;
    $lock = "ups_ai_logs_" . md5($wpdb->prefix);
    if ((string) $wpdb->get_var($wpdb->prepare("SELECT GET_LOCK(%s, 3)", $lock)) !== "1") {
        error_log("[ai-cost-tracker] Spend counters recorded; detailed log lock unavailable.");
        return;
    }
    try {
        wp_cache_delete("ups_ai_spend_tasks_" . $month, "options");
        wp_cache_delete("ups_ai_spend_log", "options");
        wp_cache_delete("notoptions", "options");
        wp_cache_delete("alloptions", "options");

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
    } finally {
        $wpdb->get_var($wpdb->prepare("SELECT RELEASE_LOCK(%s)", $lock));
    }
}

/**
 * Hard cap — wywołaj PRZED każdym wywołaniem AI. Zwraca true jeśli można jechać.
 */
function upsellio_ai_can_call(string $task, float $estimated_pln = 0.20): bool
{
    $month = current_time("Y-m");
    $current = upsellio_ai_read_spend_option("ups_ai_spend_m_{$month}");
    $estimated_pln = max(0, $estimated_pln);
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
    global $wpdb;
    $counter_key = "ups_ai_anon_daily_" . $today;
    // Seed today's existing legacy count once. The conditional SQL update is atomic.
    $seed = max(0, (int) get_transient($today_key));
    $created = $wpdb->query($wpdb->prepare(
        "INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %d, 'no')",
        $counter_key, $seed
    ));
    if ($created === false) {
        return false;
    }
    $changed = $wpdb->query($wpdb->prepare(
        "UPDATE {$wpdb->options} SET option_value = CAST(option_value AS UNSIGNED) + 1
         WHERE option_name = %s AND CAST(option_value AS UNSIGNED) < 100",
        $counter_key
    ));
    wp_cache_delete($counter_key, "options");
    wp_cache_delete("notoptions", "options");
    return $changed === 1;
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
