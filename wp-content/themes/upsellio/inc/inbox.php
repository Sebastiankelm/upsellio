<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_inbox_sidebar_unread_total(): int
{
    if (!post_type_exists("crm_client")) {
        return 0;
    }
    $ids = get_posts([
        "post_type" => "crm_client",
        "post_status" => ["publish", "draft", "private", "pending"],
        "posts_per_page" => 300,
        "fields" => "ids",
        "meta_query" => [[
            "key" => "_ups_client_unread_count",
            "value" => 0,
            "compare" => ">",
            "type" => "NUMERIC",
        ]],
    ]);
    $sum = 0;
    foreach ($ids as $cid) {
        $sum += (int) get_post_meta((int) $cid, "_ups_client_unread_count", true);
    }

    return $sum;
}

/**
 * Przelicza liczbę nieprzeczytanych wiadomości przychodzących dla klienta (wszystkie jego oferty).
 */
function upsellio_inbox_recalc_client_unread_count(int $client_id): void
{
    if ($client_id <= 0 || !post_type_exists("crm_offer")) {
        return;
    }
    $offers = get_posts([
        "post_type" => "crm_offer",
        "post_status" => ["publish", "draft", "private", "pending"],
        "posts_per_page" => 500,
        "fields" => "ids",
        "meta_query" => [[
            "key" => "_ups_offer_client_id",
            "value" => (string) $client_id,
        ]],
    ]);
    $n = 0;
    foreach ($offers as $oid) {
        $thread = get_post_meta((int) $oid, "_ups_offer_inbox_thread", true);
        if (!is_array($thread)) {
            continue;
        }
        foreach ($thread as $m) {
            if (!is_array($m)) {
                continue;
            }
            if (($m["direction"] ?? "") === "in" && empty($m["read"])) {
                $n++;
            }
        }
    }
    if ($n > 0) {
        update_post_meta($client_id, "_ups_client_unread_count", $n);
    } else {
        delete_post_meta($client_id, "_ups_client_unread_count");
    }
}

function upsellio_inbox_append_message(int $offer_id, array $msg): void
{
    $offer_id = (int) $offer_id;
    if ($offer_id <= 0 || get_post_type($offer_id) !== "crm_offer") {
        return;
    }
    $thread = get_post_meta($offer_id, "_ups_offer_inbox_thread", true);
    if (!is_array($thread)) {
        $thread = [];
    }

    $msg["id"] = "msg_" . substr(md5(uniqid("", true)), 0, 12);
    $msg["ts"] = isset($msg["ts"]) ? (string) $msg["ts"] : current_time("mysql");
    $msg["read"] = array_key_exists("read", $msg) ? (bool) $msg["read"] : false;
    $msg["classification"] = isset($msg["classification"]) ? sanitize_key((string) $msg["classification"]) : "";

    $thread[] = $msg;
    if (count($thread) > 200) {
        $thread = array_slice($thread, -200);
    }
    update_post_meta($offer_id, "_ups_offer_inbox_thread", $thread);
    $ld = (string) ($msg["direction"] ?? "");
    if ($ld === "in" || $ld === "out") {
        update_post_meta($offer_id, "_ups_offer_inbox_last_direction", $ld);
    }

    if (($msg["direction"] ?? "") === "in") {
        $client_id = (int) get_post_meta($offer_id, "_ups_offer_client_id", true);
        if ($client_id > 0 && function_exists("upsellio_inbox_recalc_client_unread_count")) {
            upsellio_inbox_recalc_client_unread_count($client_id);
        }
    }
}

function upsellio_inbox_mark_read(int $offer_id): void
{
    $offer_id = (int) $offer_id;
    if ($offer_id <= 0) {
        return;
    }
    $thread = get_post_meta($offer_id, "_ups_offer_inbox_thread", true);
    if (!is_array($thread)) {
        return;
    }
    $updated = false;
    foreach ($thread as &$msg) {
        if (!is_array($msg)) {
            continue;
        }
        if (!($msg["read"] ?? true)) {
            $msg["read"] = true;
            $updated = true;
        }
    }
    unset($msg);
    if (!$updated) {
        return;
    }
    update_post_meta($offer_id, "_ups_offer_inbox_thread", $thread);
    $client_id = (int) get_post_meta($offer_id, "_ups_offer_client_id", true);
    if ($client_id > 0) {
        upsellio_inbox_recalc_client_unread_count($client_id);
    }
}

function upsellio_inbox_update_last_inbound_classification(int $offer_id, string $cls): void
{
    $offer_id = (int) $offer_id;
    $cls = sanitize_key((string) $cls);
    if ($offer_id <= 0 || $cls === "") {
        return;
    }
    $thread = get_post_meta($offer_id, "_ups_offer_inbox_thread", true);
    if (!is_array($thread)) {
        return;
    }
    for ($i = count($thread) - 1; $i >= 0; $i--) {
        if (!is_array($thread[$i])) {
            continue;
        }
        if (($thread[$i]["direction"] ?? "") === "in" && ($thread[$i]["classification"] ?? "") === "") {
            $thread[$i]["classification"] = $cls;
            update_post_meta($offer_id, "_ups_offer_inbox_thread", $thread);

            return;
        }
    }
}

/**
 * Ustawia klasyfikację dla konkretnej wiadomości (np. ręczna korekta AI).
 */
function upsellio_inbox_set_message_classification(int $offer_id, string $message_id, string $cls): bool
{
    $offer_id = (int) $offer_id;
    $message_id = sanitize_text_field((string) $message_id);
    $cls = sanitize_key((string) $cls);
    if ($offer_id <= 0 || $message_id === "" || $cls === "") {
        return false;
    }
    $thread = get_post_meta($offer_id, "_ups_offer_inbox_thread", true);
    if (!is_array($thread)) {
        return false;
    }
    $updated = false;
    foreach ($thread as &$msg) {
        if (!is_array($msg)) {
            continue;
        }
        if (($msg["id"] ?? "") !== $message_id || ($msg["direction"] ?? "") !== "in") {
            continue;
        }
        $msg["classification"] = $cls;
        $updated = true;
        break;
    }
    unset($msg);
    if (!$updated) {
        return false;
    }
    update_post_meta($offer_id, "_ups_offer_inbox_thread", $thread);

    return true;
}

function upsellio_inbox_get_thread_summary(int $offer_id): array
{
    $offer_id = (int) $offer_id;
    $thread = get_post_meta($offer_id, "_ups_offer_inbox_thread", true);
    if (!is_array($thread) || $thread === []) {
        return [];
    }
    $last = end($thread);
    if (!is_array($last)) {
        return [];
    }
    $unread = 0;
    $last_in_cls = "";
    foreach ($thread as $m) {
        if (!is_array($m)) {
            continue;
        }
        if (($m["direction"] ?? "") === "in" && empty($m["read"])) {
            $unread++;
        }
    }
    for ($i = count($thread) - 1; $i >= 0; $i--) {
        $m = $thread[$i];
        if (!is_array($m) || ($m["direction"] ?? "") !== "in") {
            continue;
        }
        $c = (string) ($m["classification"] ?? "");
        if ($c !== "") {
            $last_in_cls = $c;
            break;
        }
    }
    $last_ts = (string) ($last["ts"] ?? "");
    $last_direction = (string) ($last["direction"] ?? "");

    return [
        "last_ts" => $last_ts,
        "last_direction" => $last_direction,
        "last_subject" => (string) ($last["subject"] ?? ""),
        "last_body" => wp_trim_words((string) ($last["body_plain"] ?? ""), 12),
        "unread" => $unread,
        "total" => count($thread),
        "last_cls" => $last_in_cls,
    ];
}

function upsellio_inbox_flag_palette(): array
{
    return [
        "f1" => ["label" => "Czerwony", "hex" => "#dc2626"],
        "f2" => ["label" => "Pomarańcz", "hex" => "#ea580c"],
        "f3" => ["label" => "Bursztyn", "hex" => "#ca8a04"],
        "f4" => ["label" => "Limonka", "hex" => "#65a30d"],
        "f5" => ["label" => "Zieleń", "hex" => "#16a34a"],
        "f6" => ["label" => "Teal", "hex" => "#0d9488"],
        "f7" => ["label" => "Niebieski", "hex" => "#2563eb"],
        "f8" => ["label" => "Fiolet", "hex" => "#7c3aed"],
        "f9" => ["label" => "Róż", "hex" => "#db2777"],
        "f10" => ["label" => "Szary", "hex" => "#64748b"],
    ];
}

function upsellio_inbox_get_folder_defs(): array
{
    $raw = get_option("ups_inbox_folder_defs", null);
    if (!is_array($raw) || $raw === []) {
        return [
            ["id" => "fld_inbox", "name" => "Główny", "parent" => ""],
        ];
    }

    return $raw;
}

function upsellio_inbox_save_folder_defs(array $defs): void
{
    update_option("ups_inbox_folder_defs", array_values($defs), false);
}

function upsellio_inbox_offer_resolved_folder_id(int $offer_id): string
{
    $fid = sanitize_key((string) get_post_meta($offer_id, "_ups_offer_inbox_folder_id", true));

    return $fid !== "" ? $fid : "fld_inbox";
}

function upsellio_inbox_offer_flag(int $offer_id): string
{
    $f = sanitize_key((string) get_post_meta($offer_id, "_ups_offer_inbox_flag", true));
    $palette = upsellio_inbox_flag_palette();

    return isset($palette[$f]) ? $f : "";
}

function upsellio_inbox_set_offer_flag(int $offer_id, string $flag): void
{
    $offer_id = (int) $offer_id;
    $flag = sanitize_key((string) $flag);
    if ($offer_id <= 0 || get_post_type($offer_id) !== "crm_offer") {
        return;
    }
    if ($flag === "" || $flag === "0") {
        delete_post_meta($offer_id, "_ups_offer_inbox_flag");

        return;
    }
    if (!isset(upsellio_inbox_flag_palette()[$flag])) {
        return;
    }
    update_post_meta($offer_id, "_ups_offer_inbox_flag", $flag);
}

function upsellio_inbox_set_offer_folder(int $offer_id, string $folder_id): void
{
    $offer_id = (int) $offer_id;
    $folder_id = sanitize_key((string) $folder_id);
    if ($offer_id <= 0 || get_post_type($offer_id) !== "crm_offer") {
        return;
    }
    $valid = false;
    foreach (upsellio_inbox_get_folder_defs() as $fd) {
        if (!is_array($fd)) {
            continue;
        }
        if (($fd["id"] ?? "") === $folder_id) {
            $valid = true;
            break;
        }
    }
    if (!$valid) {
        return;
    }
    if ($folder_id === "fld_inbox") {
        delete_post_meta($offer_id, "_ups_offer_inbox_folder_id");
    } else {
        update_post_meta($offer_id, "_ups_offer_inbox_folder_id", $folder_id);
    }
}

function upsellio_inbox_mark_thread_unread(int $offer_id): void
{
    $offer_id = (int) $offer_id;
    if ($offer_id <= 0) {
        return;
    }
    $thread = get_post_meta($offer_id, "_ups_offer_inbox_thread", true);
    if (!is_array($thread)) {
        return;
    }
    $updated = false;
    foreach ($thread as &$msg) {
        if (!is_array($msg)) {
            continue;
        }
        if (($msg["direction"] ?? "") === "in") {
            $msg["read"] = false;
            $updated = true;
        }
    }
    unset($msg);
    if (!$updated) {
        return;
    }
    update_post_meta($offer_id, "_ups_offer_inbox_thread", $thread);
    $client_id = (int) get_post_meta($offer_id, "_ups_offer_client_id", true);
    if ($client_id > 0) {
        upsellio_inbox_recalc_client_unread_count($client_id);
    }
}

function upsellio_inbox_parse_email_field(string $raw): array
{
    $raw = str_replace([";", "\n"], ",", $raw);
    $parts = array_filter(array_map("trim", explode(",", $raw)));
    $out = [];
    foreach ($parts as $p) {
        if ($p === "") {
            continue;
        }
        if (preg_match("/<\s*([^>]+)>\s*$/", $p, $m)) {
            $p = trim($m[1]);
        }
        $e = sanitize_email($p);
        if (is_email($e)) {
            $out[strtolower($e)] = $e;
        }
    }

    return array_values($out);
}

/**
 * Adresy do „odpowiedz wszystkim” (bez naszej skrzynki nadawcy).
 */
function upsellio_inbox_reply_all_participants(int $offer_id): array
{
    $offer_id = (int) $offer_id;
    $settings = function_exists("upsellio_followup_get_sender_settings") ? upsellio_followup_get_sender_settings() : [];
    $our = strtolower((string) ($settings["from_email"] ?? ""));
    $thread = get_post_meta($offer_id, "_ups_offer_inbox_thread", true);
    if (!is_array($thread)) {
        return [];
    }
    $set = [];
    foreach ($thread as $m) {
        if (!is_array($m)) {
            continue;
        }
        foreach (["from", "to"] as $k) {
            $e = sanitize_email((string) ($m[$k] ?? ""));
            if (is_email($e) && strtolower($e) !== $our) {
                $set[strtolower($e)] = $e;
            }
        }
        $cc_raw = (string) ($m["cc"] ?? "");
        if ($cc_raw !== "") {
            foreach (upsellio_inbox_parse_email_field($cc_raw) as $e) {
                if (strtolower($e) !== $our) {
                    $set[strtolower($e)] = $e;
                }
            }
        }
    }

    return array_values($set);
}

function upsellio_inbox_reply_prefill(int $offer_id): array
{
    $offer_id = (int) $offer_id;
    $client_id = (int) get_post_meta($offer_id, "_ups_offer_client_id", true);
    $client_email = sanitize_email((string) get_post_meta($client_id, "_ups_client_email", true));
    $all = upsellio_inbox_reply_all_participants($offer_id);
    if (!is_email($client_email)) {
        $to = isset($all[0]) ? $all[0] : "";
        $cc = array_slice($all, 1);

        return [
            "to" => $to,
            "cc" => implode(", ", $cc),
        ];
    }
    $cc = [];
    foreach ($all as $e) {
        if (strtolower((string) $e) !== strtolower($client_email)) {
            $cc[] = $e;
        }
    }

    return [
        "to" => $client_email,
        "cc" => implode(", ", $cc),
    ];
}

function upsellio_inbox_generate_folder_id(): string
{
    return "fld_" . substr(md5(uniqid("", true)), 0, 10);
}

function upsellio_inbox_folder_find(string $folder_id): ?array
{
    $folder_id = sanitize_key($folder_id);
    foreach (upsellio_inbox_get_folder_defs() as $fd) {
        if (!is_array($fd)) {
            continue;
        }
        if (($fd["id"] ?? "") === $folder_id) {
            return $fd;
        }
    }

    return null;
}

function upsellio_inbox_folder_create(string $parent_id, string $name): string
{
    $parent_id = sanitize_key((string) $parent_id);
    $name = sanitize_text_field((string) $name);
    if ($name === "") {
        return "";
    }
    $defs = upsellio_inbox_get_folder_defs();
    if ($parent_id !== "") {
        $parent = upsellio_inbox_folder_find($parent_id);
        if ($parent === null) {
            return "";
        }
    }
    $new_id = upsellio_inbox_generate_folder_id();
    $defs[] = [
        "id" => $new_id,
        "name" => $name,
        "parent" => $parent_id === "" ? "fld_inbox" : $parent_id,
    ];
    upsellio_inbox_save_folder_defs($defs);

    return $new_id;
}

function upsellio_inbox_folder_rename(string $folder_id, string $name): bool
{
    $folder_id = sanitize_key((string) $folder_id);
    $name = sanitize_text_field((string) $name);
    if ($folder_id === "" || $folder_id === "fld_inbox" || $name === "") {
        return false;
    }
    $defs = upsellio_inbox_get_folder_defs();
    $ok = false;
    foreach ($defs as &$fd) {
        if (!is_array($fd)) {
            continue;
        }
        if (($fd["id"] ?? "") === $folder_id) {
            $fd["name"] = $name;
            $ok = true;
            break;
        }
    }
    unset($fd);
    if (!$ok) {
        return false;
    }
    upsellio_inbox_save_folder_defs($defs);

    return true;
}

function upsellio_inbox_folder_delete(string $folder_id): bool
{
    $folder_id = sanitize_key((string) $folder_id);
    if ($folder_id === "" || $folder_id === "fld_inbox") {
        return false;
    }
    $defs = upsellio_inbox_get_folder_defs();
    $target = upsellio_inbox_folder_find($folder_id);
    if ($target === null) {
        return false;
    }
    $parent = sanitize_key((string) ($target["parent"] ?? ""));
    if ($parent === "" || upsellio_inbox_folder_find($parent) === null) {
        $parent = "fld_inbox";
    }
    $new_defs = [];
    foreach ($defs as $fd) {
        if (!is_array($fd)) {
            continue;
        }
        $fid = (string) ($fd["id"] ?? "");
        if ($fid === $folder_id) {
            continue;
        }
        if (($fd["parent"] ?? "") === $folder_id) {
            $fd["parent"] = $parent;
        }
        $new_defs[] = $fd;
    }
    upsellio_inbox_save_folder_defs($new_defs);

    $move_ids = get_posts([
        "post_type" => "crm_offer",
        "post_status" => ["publish", "draft", "private", "pending"],
        "posts_per_page" => 500,
        "fields" => "ids",
        "meta_query" => [[
            "key" => "_ups_offer_inbox_folder_id",
            "value" => $folder_id,
        ]],
    ]);
    foreach ($move_ids as $oid) {
        upsellio_inbox_set_offer_folder((int) $oid, $parent);
    }

    return true;
}

function upsellio_inbox_list_per_page(): int
{
    return max(10, min(100, (int) apply_filters("upsellio_inbox_per_page", 30)));
}

/**
 * Uzupełnia meta kierunku ostatniej wiadomości (odebrane vs wysłane) z zapisu wątku.
 */
function upsellio_inbox_sync_last_direction_from_thread(int $offer_id): void
{
    $offer_id = (int) $offer_id;
    if ($offer_id <= 0 || get_post_type($offer_id) !== "crm_offer") {
        return;
    }
    $thread = get_post_meta($offer_id, "_ups_offer_inbox_thread", true);
    if (!is_array($thread) || $thread === []) {
        delete_post_meta($offer_id, "_ups_offer_inbox_last_direction");

        return;
    }
    $last = end($thread);
    if (!is_array($last)) {
        return;
    }
    $d = (string) ($last["direction"] ?? "");
    if ($d === "in" || $d === "out") {
        update_post_meta($offer_id, "_ups_offer_inbox_last_direction", $d);
    }
}

/**
 * Jednorazowo uzupełnia brakujące meta dla starych wątków (limit na wywołanie).
 */
function upsellio_inbox_backfill_last_direction_batch(int $limit = 80): int
{
    global $wpdb;
    $limit = max(1, min(400, $limit));
    $pm = $wpdb->postmeta;
    $p = $wpdb->posts;
    $ids = $wpdb->get_col(
        "SELECT DISTINCT th.post_id FROM {$pm} th
        LEFT JOIN {$pm} ld ON ld.post_id = th.post_id AND ld.meta_key = '_ups_offer_inbox_last_direction'
        INNER JOIN {$p} po ON po.ID = th.post_id AND po.post_type = 'crm_offer'
        WHERE th.meta_key = '_ups_offer_inbox_thread' AND ld.meta_id IS NULL
        LIMIT " . (int) $limit
    );
    $n = 0;
    foreach (array_map("intval", (array) $ids) as $oid) {
        if ($oid <= 0) {
            continue;
        }
        upsellio_inbox_sync_last_direction_from_thread($oid);
        $n++;
    }

    return $n;
}

/**
 * Dodatkowe filtry smart (zakładki prototypu inboxu).
 *
 * @return array<int, mixed>
 */
function upsellio_inbox_segment_meta_clauses(string $segment): array
{
    $segment = sanitize_key((string) $segment);
    if (!in_array($segment, ["awaiting", "unlinked", "lead_web", "email_direct", "open_pipeline"], true)) {
        return [];
    }
    if ($segment === "awaiting") {
        return [
            [
                "key" => "_ups_offer_inbox_last_direction",
                "value" => "in",
                "compare" => "=",
            ],
        ];
    }
    if ($segment === "unlinked") {
        return [
            [
                "relation" => "OR",
                [
                    "key" => "_ups_offer_client_id",
                    "compare" => "NOT EXISTS",
                ],
                [
                    "key" => "_ups_offer_client_id",
                    "value" => 0,
                    "type" => "NUMERIC",
                    "compare" => "=",
                ],
            ],
        ];
    }
    if ($segment === "lead_web") {
        return [
            [
                "relation" => "AND",
                [
                    "key" => "_ups_offer_utm_source",
                    "compare" => "EXISTS",
                ],
                [
                    "key" => "_ups_offer_utm_source",
                    "value" => "",
                    "compare" => "!=",
                ],
            ],
        ];
    }
    if ($segment === "email_direct") {
        return [
            [
                "relation" => "OR",
                [
                    "key" => "_ups_offer_utm_source",
                    "compare" => "NOT EXISTS",
                ],
                [
                    "key" => "_ups_offer_utm_source",
                    "value" => "",
                    "compare" => "=",
                ],
            ],
        ];
    }
    if ($segment === "open_pipeline") {
        return [
            [
                "key" => "_ups_offer_status",
                "value" => "open",
                "compare" => "=",
            ],
        ];
    }

    return [];
}

/**
 * Fragment SQL dla wyszukiwania tekstowego (segment wątków).
 */
function upsellio_inbox_segment_sql_fragment(string $segment): string
{
    global $wpdb;
    $segment = sanitize_key((string) $segment);
    $pm = $wpdb->postmeta;
    if (!in_array($segment, ["awaiting", "unlinked", "lead_web", "email_direct", "open_pipeline"], true)) {
        return "";
    }
    if ($segment === "awaiting") {
        return $wpdb->prepare(
            " AND EXISTS (SELECT 1 FROM {$pm} sg WHERE sg.post_id = p.ID AND sg.meta_key = %s AND sg.meta_value = %s)",
            "_ups_offer_inbox_last_direction",
            "in"
        );
    }
    if ($segment === "unlinked") {
        return " AND (
            NOT EXISTS (SELECT 1 FROM {$pm} uc WHERE uc.post_id = p.ID AND uc.meta_key = '_ups_offer_client_id')
            OR EXISTS (SELECT 1 FROM {$pm} uc2 WHERE uc2.post_id = p.ID AND uc2.meta_key = '_ups_offer_client_id' AND (TRIM(IFNULL(uc2.meta_value,'')) = '' OR uc2.meta_value = '0'))
        )";
    }
    if ($segment === "lead_web") {
        return " AND EXISTS (SELECT 1 FROM {$pm} ut WHERE ut.post_id = p.ID AND ut.meta_key = '_ups_offer_utm_source' AND TRIM(IFNULL(ut.meta_value,'')) <> '')";
    }
    if ($segment === "email_direct") {
        return " AND (
            NOT EXISTS (SELECT 1 FROM {$pm} ut2 WHERE ut2.post_id = p.ID AND ut2.meta_key = '_ups_offer_utm_source')
            OR EXISTS (SELECT 1 FROM {$pm} ut3 WHERE ut3.post_id = p.ID AND ut3.meta_key = '_ups_offer_utm_source' AND TRIM(IFNULL(ut3.meta_value,'')) = '')
        )";
    }
    if ($segment === "open_pipeline") {
        return $wpdb->prepare(
            " AND EXISTS (SELECT 1 FROM {$pm} st WHERE st.post_id = p.ID AND st.meta_key = %s AND st.meta_value = %s)",
            "_ups_offer_status",
            "open"
        );
    }

    return "";
}

/**
 * KPI dla paska nad listą (folder / flaga / bucket — bez segmentu i bez wyszukiwania).
 *
 * @param array{folder?: string, flag?: string, bucket?: string} $ctx
 * @return array{awaiting_reply: int, unlinked: int, lead_web: int, email_direct: int, open_pipeline: int, capped: bool}
 */
function upsellio_inbox_aggregate_kpis(array $ctx): array
{
    $out = [
        "awaiting_reply" => 0,
        "unlinked" => 0,
        "lead_web" => 0,
        "email_direct" => 0,
        "open_pipeline" => 0,
        "capped" => false,
    ];
    if (!post_type_exists("crm_offer")) {
        return $out;
    }
    $folder = sanitize_key((string) ($ctx["folder"] ?? "fld_inbox"));
    if ($folder === "") {
        $folder = "fld_inbox";
    }
    $flag = sanitize_key((string) ($ctx["flag"] ?? ""));
    if ($flag !== "" && !isset(upsellio_inbox_flag_palette()[$flag])) {
        $flag = "";
    }
    $bucket = sanitize_key((string) ($ctx["bucket"] ?? "all"));
    if (!in_array($bucket, ["all", "received", "sent"], true)) {
        $bucket = "all";
    }
    $mq = upsellio_inbox_list_meta_query($folder, $flag, $bucket);
    $q = new WP_Query([
        "post_type" => "crm_offer",
        "post_status" => ["publish", "draft", "private", "pending"],
        "posts_per_page" => 400,
        "paged" => 1,
        "fields" => "ids",
        "orderby" => "modified",
        "order" => "DESC",
        "meta_query" => $mq,
        "no_found_rows" => false,
        "update_post_meta_cache" => false,
        "update_post_term_cache" => false,
    ]);
    $ids = array_map("intval", (array) $q->posts);
    if ((int) $q->found_posts > 400) {
        $out["capped"] = true;
    }
    foreach ($ids as $oid) {
        if ($oid <= 0) {
            continue;
        }
        $sum = upsellio_inbox_get_thread_summary($oid);
        if (($sum["last_direction"] ?? "") === "in") {
            $out["awaiting_reply"]++;
        }
        $cid = (int) get_post_meta($oid, "_ups_offer_client_id", true);
        if ($cid <= 0) {
            $out["unlinked"]++;
        }
        $utm = trim((string) get_post_meta($oid, "_ups_offer_utm_source", true));
        if ($utm !== "") {
            $out["lead_web"]++;
        } else {
            $out["email_direct"]++;
        }
        if ((string) get_post_meta($oid, "_ups_offer_status", true) === "open") {
            $out["open_pipeline"]++;
        }
    }

    return $out;
}

/**
 * Meta zapytania listy inbox (wątek istnieje + folder + opcjonalnie flaga + widok odebrane/wysłane).
 *
 * @return array<int, mixed>
 */
function upsellio_inbox_list_meta_query(string $folder_id, string $flag_key, string $bucket = "all", string $segment = ""): array
{
    $folder_id = sanitize_key((string) $folder_id);
    if ($folder_id === "") {
        $folder_id = "fld_inbox";
    }
    $flag_key = sanitize_key((string) $flag_key);
    $bucket = sanitize_key((string) $bucket);
    if (!in_array($bucket, ["all", "received", "sent"], true)) {
        $bucket = "all";
    }
    $segment = sanitize_key((string) $segment);
    if (!in_array($segment, ["", "awaiting", "unlinked", "lead_web", "email_direct", "open_pipeline"], true)) {
        $segment = "";
    }

    $mq = [
        "relation" => "AND",
        [
            "key" => "_ups_offer_inbox_thread",
            "compare" => "EXISTS",
        ],
    ];

    if ($folder_id === "fld_inbox") {
        $mq[] = [
            "relation" => "OR",
            [
                "key" => "_ups_offer_inbox_folder_id",
                "compare" => "NOT EXISTS",
            ],
            [
                "key" => "_ups_offer_inbox_folder_id",
                "value" => "",
                "compare" => "=",
            ],
        ];
    } else {
        $mq[] = [
            "key" => "_ups_offer_inbox_folder_id",
            "value" => $folder_id,
            "compare" => "=",
        ];
    }

    if ($flag_key !== "" && isset(upsellio_inbox_flag_palette()[$flag_key])) {
        $mq[] = [
            "key" => "_ups_offer_inbox_flag",
            "value" => $flag_key,
            "compare" => "=",
        ];
    }

    if ($bucket === "received") {
        $mq[] = [
            "key" => "_ups_offer_inbox_last_direction",
            "value" => "in",
            "compare" => "=",
        ];
    } elseif ($bucket === "sent") {
        $mq[] = [
            "key" => "_ups_offer_inbox_last_direction",
            "value" => "out",
            "compare" => "=",
        ];
    }

    foreach (upsellio_inbox_segment_meta_clauses($segment) as $clause) {
        $mq[] = $clause;
    }

    return $mq;
}

/**
 * Lista inbox z paginacją (folder, flaga, opcjonalnie wyszukiwanie po tytule / meta wątku / e-mailu klienta).
 *
 * @param array{folder?: string, flag?: string, bucket?: string, segment?: string, search?: string, page?: int, post_statuses?: string[]} $ctx
 * @return array{posts: WP_Post[], total: int, page: int, per_page: int}
 */
function upsellio_inbox_query_list(array $ctx): array
{
    $per_page = upsellio_inbox_list_per_page();
    $page = max(1, (int) ($ctx["page"] ?? 1));
    $folder = sanitize_key((string) ($ctx["folder"] ?? "fld_inbox"));
    if ($folder === "") {
        $folder = "fld_inbox";
    }
    $flag = sanitize_key((string) ($ctx["flag"] ?? ""));
    if ($flag !== "" && !isset(upsellio_inbox_flag_palette()[$flag])) {
        $flag = "";
    }
    $bucket = sanitize_key((string) ($ctx["bucket"] ?? "all"));
    if (!in_array($bucket, ["all", "received", "sent"], true)) {
        $bucket = "all";
    }
    $segment = sanitize_key((string) ($ctx["segment"] ?? ""));
    if (!in_array($segment, ["", "awaiting", "unlinked", "lead_web", "email_direct", "open_pipeline"], true)) {
        $segment = "";
    }
    $search = trim((string) ($ctx["search"] ?? ""));
    if (strlen($search) > 160) {
        $search = substr($search, 0, 160);
    }
    $statuses = isset($ctx["post_statuses"]) && is_array($ctx["post_statuses"]) ? $ctx["post_statuses"] : ["publish", "draft", "private", "pending"];

    if (!post_type_exists("crm_offer")) {
        return ["posts" => [], "total" => 0, "page" => $page, "per_page" => $per_page];
    }

    static $inbox_ld_backfill_done = false;
    if (!$inbox_ld_backfill_done) {
        $inbox_ld_backfill_done = true;
        upsellio_inbox_backfill_last_direction_batch(100);
    }

    if ($search !== "") {
        return upsellio_inbox_query_list_with_search($folder, $flag, $search, $page, $per_page, $statuses, $bucket, $segment);
    }

    $q = new WP_Query([
        "post_type" => "crm_offer",
        "post_status" => $statuses,
        "posts_per_page" => $per_page,
        "paged" => $page,
        "orderby" => "modified",
        "order" => "DESC",
        "meta_query" => upsellio_inbox_list_meta_query($folder, $flag, $bucket, $segment),
        "update_post_meta_cache" => true,
        "update_post_term_cache" => false,
        "no_found_rows" => false,
    ]);

    return [
        "posts" => $q->posts,
        "total" => (int) $q->found_posts,
        "page" => $page,
        "per_page" => $per_page,
    ];
}

/**
 * @param string[] $statuses
 * @return array{posts: WP_Post[], total: int, page: int, per_page: int}
 */
function upsellio_inbox_query_list_with_search(
    string $folder_id,
    string $flag_key,
    string $search,
    int $page,
    int $per_page,
    array $statuses,
    string $bucket = "all",
    string $segment = ""
): array {
    global $wpdb;

    $folder_id = sanitize_key($folder_id);
    $flag_key = sanitize_key($flag_key);
    $bucket = sanitize_key($bucket);
    if (!in_array($bucket, ["all", "received", "sent"], true)) {
        $bucket = "all";
    }
    $segment = sanitize_key((string) $segment);
    if (!in_array($segment, ["", "awaiting", "unlinked", "lead_web", "email_direct", "open_pipeline"], true)) {
        $segment = "";
    }
    if ($folder_id === "") {
        $folder_id = "fld_inbox";
    }

    $p = $wpdb->posts;
    $pm = $wpdb->postmeta;

    $allowed_status = array_values(array_intersect(
        array_map("sanitize_key", $statuses),
        ["publish", "draft", "private", "pending", "future"]
    ));
    if ($allowed_status === []) {
        $allowed_status = ["publish", "draft", "private", "pending"];
    }
    $in_status = "'" . implode("','", array_map("esc_sql", $allowed_status)) . "'";

    $thread_exists = "EXISTS (SELECT 1 FROM {$pm} th WHERE th.post_id = p.ID AND th.meta_key = '_ups_offer_inbox_thread')";

    if ($folder_id === "fld_inbox") {
        $folder_sql = " AND NOT EXISTS (
            SELECT 1 FROM {$pm} fx
            WHERE fx.post_id = p.ID AND fx.meta_key = '_ups_offer_inbox_folder_id'
            AND TRIM(IFNULL(fx.meta_value,'')) <> ''
        )";
    } else {
        $folder_sql = $wpdb->prepare(
            " AND EXISTS (SELECT 1 FROM {$pm} fx WHERE fx.post_id = p.ID AND fx.meta_key = '_ups_offer_inbox_folder_id' AND fx.meta_value = %s)",
            $folder_id
        );
    }

    $flag_sql = "";
    if ($flag_key !== "" && isset(upsellio_inbox_flag_palette()[$flag_key])) {
        $flag_sql = $wpdb->prepare(
            " AND EXISTS (SELECT 1 FROM {$pm} fl WHERE fl.post_id = p.ID AND fl.meta_key = '_ups_offer_inbox_flag' AND fl.meta_value = %s)",
            $flag_key
        );
    }

    $bucket_sql = "";
    if ($bucket === "received") {
        $bucket_sql = $wpdb->prepare(
            " AND EXISTS (SELECT 1 FROM {$pm} bkd WHERE bkd.post_id = p.ID AND bkd.meta_key = %s AND bkd.meta_value = %s)",
            "_ups_offer_inbox_last_direction",
            "in"
        );
    } elseif ($bucket === "sent") {
        $bucket_sql = $wpdb->prepare(
            " AND EXISTS (SELECT 1 FROM {$pm} bkd WHERE bkd.post_id = p.ID AND bkd.meta_key = %s AND bkd.meta_value = %s)",
            "_ups_offer_inbox_last_direction",
            "out"
        );
    }

    $segment_sql = upsellio_inbox_segment_sql_fragment($segment);

    $like = "%" . $wpdb->esc_like($search) . "%";
    $search_sql = $wpdb->prepare(
        " AND (
            p.post_title LIKE %s
            OR EXISTS (SELECT 1 FROM {$pm} tm WHERE tm.post_id = p.ID AND tm.meta_key = '_ups_offer_inbox_thread' AND tm.meta_value LIKE %s)
            OR EXISTS (
                SELECT 1 FROM {$pm} cid
                INNER JOIN {$pm} em ON em.post_id = CAST(cid.meta_value AS UNSIGNED) AND em.meta_key = '_ups_client_email'
                INNER JOIN {$p} cp ON cp.ID = em.post_id AND cp.post_type = 'crm_client'
                WHERE cid.post_id = p.ID AND cid.meta_key = '_ups_offer_client_id'
                AND cid.meta_value REGEXP '^[0-9]+$'
                AND em.meta_value LIKE %s
            )
        )",
        $like,
        $like,
        $like
    );

    $where_core =
        "p.post_type = 'crm_offer' AND p.post_status IN ({$in_status}) AND {$thread_exists}{$folder_sql}{$flag_sql}{$bucket_sql}{$segment_sql}{$search_sql}";

    $count_sql = "SELECT COUNT(DISTINCT p.ID) FROM {$p} p WHERE {$where_core}";
    $total = (int) $wpdb->get_var($count_sql);

    $offset = ($page - 1) * $per_page;
    $ids_sql =
        "SELECT p.ID FROM {$p} p WHERE {$where_core} ORDER BY p.post_modified DESC LIMIT " .
        (int) $per_page .
        " OFFSET " .
        (int) $offset;
    $ids = $wpdb->get_col($ids_sql);
    $ids = array_values(array_filter(array_map("intval", $ids)));

    if ($ids === []) {
        return ["posts" => [], "total" => $total, "page" => $page, "per_page" => $per_page];
    }

    $q = new WP_Query([
        "post_type" => "crm_offer",
        "post_status" => $allowed_status,
        "post__in" => $ids,
        "orderby" => "post__in",
        "posts_per_page" => count($ids),
        "update_post_meta_cache" => true,
        "update_post_term_cache" => false,
    ]);

    return [
        "posts" => $q->posts,
        "total" => $total,
        "page" => $page,
        "per_page" => $per_page,
    ];
}

/**
 * Gdy wybrany wątek nie jest na bieżącej stronie listy, zwraca pojedynczy wpis do dopięcia na górze (bez zmiany total).
 */
function upsellio_inbox_maybe_prepend_selected_offer(array $posts, int $offer_id): array
{
    $offer_id = (int) $offer_id;
    if ($offer_id <= 0 || !post_type_exists("crm_offer")) {
        return $posts;
    }
    foreach ($posts as $po) {
        if ((int) $po->ID === $offer_id) {
            return $posts;
        }
    }
    if (!current_user_can("edit_post", $offer_id)) {
        return $posts;
    }
    $thread = get_post_meta($offer_id, "_ups_offer_inbox_thread", true);
    if (!is_array($thread) || $thread === []) {
        return $posts;
    }
    $post = get_post($offer_id);
    if (!$post instanceof WP_Post || $post->post_type !== "crm_offer") {
        return $posts;
    }

    return array_merge([$post], $posts);
}
