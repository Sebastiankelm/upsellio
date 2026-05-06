<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_batch_get_jobs_table()
{
    global $wpdb;
    return $wpdb->prefix . "upsellio_batch_jobs";
}

function upsellio_batch_get_items_table()
{
    global $wpdb;
    return $wpdb->prefix . "upsellio_batch_items";
}

function upsellio_batch_require_permissions()
{
    if (!current_user_can("manage_options")) {
        wp_send_json_error(["message" => "forbidden"], 403);
    }
}

function upsellio_batch_require_nonce()
{
    check_ajax_referer("upsellio_batch_api_nonce", "nonce");
}

function upsellio_batch_normalize_item($item)
{
    $entityId = isset($item["entity_id"]) ? (int) $item["entity_id"] : 0;
    $entityType = isset($item["entity_type"]) ? sanitize_key((string) $item["entity_type"]) : "";
    $payload = isset($item["payload"]) && is_array($item["payload"]) ? $item["payload"] : [];

    return [
        "entity_id" => max(0, $entityId),
        "entity_type" => $entityType,
        "payload_json" => wp_json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ];
}

function upsellio_batch_create_job_ajax()
{
    upsellio_batch_require_permissions();
    upsellio_batch_require_nonce();

    global $wpdb;

    $jobType = isset($_POST["job_type"]) ? sanitize_key((string) wp_unslash($_POST["job_type"])) : "";
    if ($jobType === "") {
        wp_send_json_error(["message" => "missing_job_type"], 400);
    }

    $meta = isset($_POST["meta"]) ? (array) wp_unslash($_POST["meta"]) : [];
    $inserted = $wpdb->insert(
        upsellio_batch_get_jobs_table(),
        [
            "job_type" => $jobType,
            "status" => "pending",
            "total_items" => 0,
            "processed_items" => 0,
            "failed_items" => 0,
            "created_by" => (int) get_current_user_id(),
            "meta_json" => wp_json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ],
        ["%s", "%s", "%d", "%d", "%d", "%d", "%s"]
    );

    if (!$inserted) {
        wp_send_json_error(["message" => "job_insert_failed"], 500);
    }

    wp_send_json_success([
        "job_id" => (int) $wpdb->insert_id,
        "status" => "pending",
    ]);
}
add_action("wp_ajax_upsellio_batch_create_job", "upsellio_batch_create_job_ajax");

function upsellio_batch_enqueue_items_ajax()
{
    upsellio_batch_require_permissions();
    upsellio_batch_require_nonce();

    global $wpdb;

    $jobId = isset($_POST["job_id"]) ? (int) $_POST["job_id"] : 0;
    if ($jobId <= 0) {
        wp_send_json_error(["message" => "invalid_job_id"], 400);
    }

    $job = $wpdb->get_row(
        $wpdb->prepare("SELECT id FROM " . upsellio_batch_get_jobs_table() . " WHERE id = %d", $jobId),
        ARRAY_A
    );
    if (!$job) {
        wp_send_json_error(["message" => "job_not_found"], 404);
    }

    $items = isset($_POST["items"]) ? json_decode((string) wp_unslash($_POST["items"]), true) : [];
    if (!is_array($items) || empty($items)) {
        wp_send_json_error(["message" => "missing_items"], 400);
    }

    $items = array_slice($items, 0, 500);
    $created = 0;
    foreach ($items as $rawItem) {
        if (!is_array($rawItem)) {
            continue;
        }
        $item = upsellio_batch_normalize_item($rawItem);
        $ok = $wpdb->insert(
            upsellio_batch_get_items_table(),
            [
                "job_id" => $jobId,
                "entity_id" => $item["entity_id"],
                "entity_type" => $item["entity_type"],
                "status" => "pending",
                "attempts" => 0,
                "payload_json" => $item["payload_json"],
            ],
            ["%d", "%d", "%s", "%s", "%d", "%s"]
        );
        if ($ok) {
            $created++;
        }
    }

    if ($created > 0) {
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE " . upsellio_batch_get_jobs_table() . " SET total_items = total_items + %d WHERE id = %d",
                $created,
                $jobId
            )
        );
    }

    wp_send_json_success([
        "job_id" => $jobId,
        "created_items" => $created,
    ]);
}
add_action("wp_ajax_upsellio_batch_enqueue_items", "upsellio_batch_enqueue_items_ajax");

function upsellio_batch_get_job_ajax()
{
    upsellio_batch_require_permissions();
    upsellio_batch_require_nonce();

    global $wpdb;
    $jobId = isset($_POST["job_id"]) ? (int) $_POST["job_id"] : 0;
    if ($jobId <= 0) {
        wp_send_json_error(["message" => "invalid_job_id"], 400);
    }

    $job = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id, job_type, status, total_items, processed_items, failed_items, created_at, started_at, finished_at, meta_json
             FROM " . upsellio_batch_get_jobs_table() . " WHERE id = %d",
            $jobId
        ),
        ARRAY_A
    );

    if (!$job) {
        wp_send_json_error(["message" => "job_not_found"], 404);
    }

    $pendingCount = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM " . upsellio_batch_get_items_table() . " WHERE job_id = %d AND status = 'pending'",
            $jobId
        )
    );

    wp_send_json_success([
        "job" => [
            "id" => (int) $job["id"],
            "job_type" => (string) $job["job_type"],
            "status" => (string) $job["status"],
            "total_items" => (int) $job["total_items"],
            "processed_items" => (int) $job["processed_items"],
            "failed_items" => (int) $job["failed_items"],
            "pending_items" => $pendingCount,
            "created_at" => (string) $job["created_at"],
            "started_at" => (string) $job["started_at"],
            "finished_at" => (string) $job["finished_at"],
            "meta" => json_decode((string) $job["meta_json"], true),
        ],
    ]);
}
add_action("wp_ajax_upsellio_batch_get_job", "upsellio_batch_get_job_ajax");

function upsellio_batch_list_jobs_ajax()
{
    upsellio_batch_require_permissions();
    upsellio_batch_require_nonce();

    global $wpdb;
    $limit = isset($_POST["limit"]) ? (int) $_POST["limit"] : 20;
    $limit = max(1, min(100, $limit));

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT id, job_type, status, total_items, processed_items, failed_items, created_at
             FROM " . upsellio_batch_get_jobs_table() . "
             ORDER BY id DESC
             LIMIT %d",
            $limit
        ),
        ARRAY_A
    );

    $jobs = array_map(static function ($row) {
        return [
            "id" => (int) $row["id"],
            "job_type" => (string) $row["job_type"],
            "status" => (string) $row["status"],
            "total_items" => (int) $row["total_items"],
            "processed_items" => (int) $row["processed_items"],
            "failed_items" => (int) $row["failed_items"],
            "created_at" => (string) $row["created_at"],
        ];
    }, is_array($rows) ? $rows : []);

    wp_send_json_success(["jobs" => $jobs]);
}
add_action("wp_ajax_upsellio_batch_list_jobs", "upsellio_batch_list_jobs_ajax");

function upsellio_batch_claim_items_ajax()
{
    upsellio_batch_require_permissions();
    upsellio_batch_require_nonce();

    global $wpdb;
    $jobId = isset($_POST["job_id"]) ? (int) $_POST["job_id"] : 0;
    $limit = isset($_POST["limit"]) ? (int) $_POST["limit"] : 20;
    $limit = max(1, min(200, $limit));

    if ($jobId <= 0) {
        wp_send_json_error(["message" => "invalid_job_id"], 400);
    }

    $itemsTable = upsellio_batch_get_items_table();
    $jobsTable = upsellio_batch_get_jobs_table();

    $pendingRows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT id, entity_id, entity_type, payload_json
             FROM {$itemsTable}
             WHERE job_id = %d AND status = 'pending'
             ORDER BY id ASC
             LIMIT %d",
            $jobId,
            $limit
        ),
        ARRAY_A
    );

    if (!is_array($pendingRows) || empty($pendingRows)) {
        wp_send_json_success(["items" => []]);
    }

    $claimed = [];
    foreach ($pendingRows as $row) {
        $itemId = (int) $row["id"];
        $updated = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$itemsTable}
                 SET status = 'processing', attempts = attempts + 1
                 WHERE id = %d AND status = 'pending'",
                $itemId
            )
        );
        if ($updated !== 1) {
            continue;
        }

        $claimed[] = [
            "id" => $itemId,
            "entity_id" => (int) $row["entity_id"],
            "entity_type" => (string) $row["entity_type"],
            "payload" => json_decode((string) $row["payload_json"], true),
        ];
    }

    if (!empty($claimed)) {
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$jobsTable}
                 SET status = CASE WHEN status = 'pending' THEN 'running' ELSE status END,
                     started_at = CASE WHEN started_at IS NULL THEN %s ELSE started_at END
                 WHERE id = %d",
                current_time("mysql"),
                $jobId
            )
        );
    }

    wp_send_json_success(["items" => $claimed]);
}
add_action("wp_ajax_upsellio_batch_claim_items", "upsellio_batch_claim_items_ajax");

function upsellio_batch_mark_item_done_ajax()
{
    upsellio_batch_require_permissions();
    upsellio_batch_require_nonce();

    global $wpdb;
    $jobId = isset($_POST["job_id"]) ? (int) $_POST["job_id"] : 0;
    $itemId = isset($_POST["item_id"]) ? (int) $_POST["item_id"] : 0;

    if ($jobId <= 0 || $itemId <= 0) {
        wp_send_json_error(["message" => "invalid_params"], 400);
    }

    $itemsTable = upsellio_batch_get_items_table();
    $jobsTable = upsellio_batch_get_jobs_table();

    $updated = $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$itemsTable}
             SET status = 'done', processed_at = %s, error_message = NULL
             WHERE id = %d AND job_id = %d",
            current_time("mysql"),
            $itemId,
            $jobId
        )
    );

    if ($updated !== 1) {
        wp_send_json_error(["message" => "item_update_failed"], 400);
    }

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$jobsTable}
             SET processed_items = processed_items + 1
             WHERE id = %d",
            $jobId
        )
    );

    upsellio_batch_maybe_finish_job($jobId);
    wp_send_json_success(["item_id" => $itemId, "status" => "done"]);
}
add_action("wp_ajax_upsellio_batch_mark_item_done", "upsellio_batch_mark_item_done_ajax");

function upsellio_batch_mark_item_failed_ajax()
{
    upsellio_batch_require_permissions();
    upsellio_batch_require_nonce();

    global $wpdb;
    $jobId = isset($_POST["job_id"]) ? (int) $_POST["job_id"] : 0;
    $itemId = isset($_POST["item_id"]) ? (int) $_POST["item_id"] : 0;
    $error = isset($_POST["error_message"]) ? sanitize_textarea_field((string) wp_unslash($_POST["error_message"])) : "";

    if ($jobId <= 0 || $itemId <= 0) {
        wp_send_json_error(["message" => "invalid_params"], 400);
    }

    $itemsTable = upsellio_batch_get_items_table();
    $jobsTable = upsellio_batch_get_jobs_table();

    $updated = $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$itemsTable}
             SET status = 'failed', processed_at = %s, error_message = %s
             WHERE id = %d AND job_id = %d",
            current_time("mysql"),
            mb_substr($error, 0, 2000),
            $itemId,
            $jobId
        )
    );

    if ($updated !== 1) {
        wp_send_json_error(["message" => "item_update_failed"], 400);
    }

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$jobsTable}
             SET processed_items = processed_items + 1,
                 failed_items = failed_items + 1
             WHERE id = %d",
            $jobId
        )
    );

    upsellio_batch_maybe_finish_job($jobId);
    wp_send_json_success(["item_id" => $itemId, "status" => "failed"]);
}
add_action("wp_ajax_upsellio_batch_mark_item_failed", "upsellio_batch_mark_item_failed_ajax");

function upsellio_batch_maybe_finish_job($jobId)
{
    global $wpdb;
    $jobId = (int) $jobId;
    if ($jobId <= 0) {
        return;
    }

    $jobsTable = upsellio_batch_get_jobs_table();
    $itemsTable = upsellio_batch_get_items_table();

    $remaining = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*)
             FROM {$itemsTable}
             WHERE job_id = %d AND status IN ('pending', 'processing')",
            $jobId
        )
    );

    if ($remaining > 0) {
        return;
    }

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$jobsTable}
             SET status = 'completed',
                 finished_at = CASE WHEN finished_at IS NULL THEN %s ELSE finished_at END
             WHERE id = %d",
            current_time("mysql"),
            $jobId
        )
    );
}
