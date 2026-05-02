<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_crm_app_user_can_access()
{
    return is_user_logged_in() && (current_user_can("manage_options") || current_user_can("edit_posts"));
}

function upsellio_crm_app_ensure_page()
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }
    $slug = "crm-app";
    $page = get_page_by_path($slug);
    if ($page instanceof WP_Post) {
        return;
    }
    wp_insert_post([
        "post_type" => "page",
        "post_status" => "publish",
        "post_name" => $slug,
        "post_title" => "CRM App",
        "post_content" => "",
    ]);
}
add_action("admin_init", "upsellio_crm_app_ensure_page");

function upsellio_crm_app_register_query_var($vars)
{
    $vars[] = "upsellio_crm_app";
    return $vars;
}
add_filter("query_vars", "upsellio_crm_app_register_query_var");

function upsellio_crm_app_post_statuses_visible()
{
    return ["publish", "draft", "private", "pending"];
}

function upsellio_crm_count_posts_all_statuses($post_type)
{
    $post_type = sanitize_key((string) $post_type);
    $counts = $post_type !== "" ? wp_count_posts($post_type) : null;
    if (!is_object($counts)) {
        return 0;
    }
    return (int) $counts->publish + (int) $counts->draft + (int) $counts->pending + (int) $counts->private + (int) $counts->future;
}

function upsellio_crm_app_compute_active_mrr()
{
    if (!post_type_exists("crm_client")) {
        return 0.0;
    }
    $posts = get_posts([
        "post_type" => "crm_client",
        "post_status" => upsellio_crm_app_post_statuses_visible(),
        "posts_per_page" => 500,
        "meta_query" => [
            "relation" => "AND",
            [
                "key" => "_ups_client_is_recurring",
                "value" => "1",
            ],
            [
                "key" => "_ups_client_subscription_status",
                "value" => "active",
            ],
        ],
    ]);
    $sum = 0.0;
    foreach ($posts as $p) {
        $sum += (float) get_post_meta((int) $p->ID, "_ups_client_monthly_value", true);
    }

    return $sum;
}

function upsellio_crm_app_purge_legacy_global_activity_option()
{
    if (get_option("_upsellio_crm_purged_global_activity_log_v1", "") !== "") {
        return;
    }
    delete_option("ups_crm_activity_log");
    update_option("_upsellio_crm_purged_global_activity_log_v1", "1", false);
}
add_action("init", "upsellio_crm_app_purge_legacy_global_activity_option", 3);

/**
 * Zbiera ostatnie wpisy z meta `_ups_{entity}_activity_log` (bez wp_options).
 *
 * @return array<int, array{entity_type:string,entity_id:int,entry:array}>
 */
function upsellio_crm_app_collect_recent_activity_entries($limit = 40, $inbox_only = false)
{
    $limit = max(5, min(200, (int) $limit));
    $st = upsellio_crm_app_post_statuses_visible();
    $batches = [
        ["pt" => "crm_offer", "et" => "offer", "count" => 35],
        ["pt" => "crm_client", "et" => "client", "count" => 25],
        ["pt" => "lead_task", "et" => "task", "count" => 25],
        ["pt" => "crm_contract", "et" => "contract", "count" => 18],
        ["pt" => "crm_lead", "et" => "lead", "count" => 15],
        ["pt" => "crm_prospect", "et" => "prospect", "count" => 15],
        ["pt" => "crm_contact", "et" => "contact", "count" => 12],
        ["pt" => "crm_service", "et" => "service", "count" => 10],
        ["pt" => "ups_followup_template", "et" => "followup", "count" => 8],
        ["pt" => "crm_offer_layout", "et" => "offer_layout", "count" => 6],
        ["pt" => "crm_contract_layout", "et" => "contract_layout", "count" => 6],
    ];
    $rows = [];
    foreach ($batches as $b) {
        $pt = (string) ($b["pt"] ?? "");
        $et = (string) ($b["et"] ?? "");
        $count = (int) ($b["count"] ?? 20);
        if ($pt === "" || $et === "" || !post_type_exists($pt)) {
            continue;
        }
        $ids = get_posts([
            "post_type" => $pt,
            "post_status" => $st,
            "posts_per_page" => max(1, $count),
            "orderby" => "modified",
            "order" => "DESC",
            "fields" => "ids",
        ]);
        $meta_key = "_ups_" . $et . "_activity_log";
        foreach ($ids as $pid) {
            $pid = (int) $pid;
            $log = get_post_meta($pid, $meta_key, true);
            if (!is_array($log)) {
                continue;
            }
            foreach ($log as $entry) {
                if (!is_array($entry)) {
                    continue;
                }
                $ts_raw = (string) ($entry["ts"] ?? "");
                $ts_u = $ts_raw !== "" ? strtotime($ts_raw) : false;
                $rows[] = [
                    "entity_type" => $et,
                    "entity_id" => $pid,
                    "entry" => $entry,
                    "_ts_sort" => $ts_u !== false ? (int) $ts_u : 0,
                ];
            }
        }
    }
    usort($rows, static function ($a, $b) {
        return ($b["_ts_sort"] ?? 0) <=> ($a["_ts_sort"] ?? 0);
    });
    if ($inbox_only) {
        $rows = array_values(array_filter($rows, static function ($r) {
            $ev = strtolower((string) (($r["entry"] ?? [])["event"] ?? ""));
            return (strpos($ev, "followup") !== false)
                || (strpos($ev, "inbound") !== false)
                || (strpos($ev, "email") !== false);
        }));
    }
    $rows = array_slice($rows, 0, $limit);
    foreach ($rows as &$r) {
        unset($r["_ts_sort"]);
    }
    unset($r);

    return $rows;
}

function upsellio_crm_app_get_tasks_query_args($posts_per_page = 500)
{
    $args = [
        "post_type" => "lead_task",
        "post_status" => upsellio_crm_app_post_statuses_visible(),
        "posts_per_page" => max(1, (int) $posts_per_page),
        "orderby" => "date",
        "order" => "DESC",
    ];
    if (!current_user_can("manage_options")) {
        $args["author"] = get_current_user_id();
    }

    return $args;
}

function upsellio_crm_app_sort_tasks_by_priority(array $tasks)
{
    if ($tasks === []) {
        return $tasks;
    }
    usort($tasks, static function ($a, $b) {
        $pa = (int) get_post_meta((int) $a->ID, "_upsellio_task_priority_score", true);
        $pb = (int) get_post_meta((int) $b->ID, "_upsellio_task_priority_score", true);
        if ($pa === $pb) {
            return (int) $b->ID <=> (int) $a->ID;
        }

        return $pb <=> $pa;
    });

    return $tasks;
}

function upsellio_crm_app_load_render_collections($view, $template_studio_tab, $inbox_ctx = null)
{
    $st = upsellio_crm_app_post_statuses_visible();
    $empty = [
        "clients" => [],
        "offers" => [],
        "followups" => [],
        "prospects" => [],
        "leads" => [],
        "contacts" => [],
        "services" => [],
        "tasks" => [],
        "contracts" => [],
        "offer_layout_templates" => [],
        "contract_layout_templates" => [],
        "contract_template_html" => "",
        "contract_template_css" => "",
        "inbox_offers" => [],
        "inbox_list_total" => 0,
        "inbox_list_page" => 1,
        "inbox_list_per_page" => 30,
    ];

    $qlist = static function ($post_type, $n, $orderby = "modified", $order = "DESC") use ($st) {
        if (!post_type_exists($post_type)) {
            return [];
        }

        return get_posts([
            "post_type" => $post_type,
            "post_status" => $st,
            "posts_per_page" => max(1, (int) $n),
            "orderby" => $orderby,
            "order" => $order,
        ]);
    };

    switch ($view) {
        case "dashboard":
            $empty["clients"] = $qlist("crm_client", 12);
            $empty["contracts"] = $qlist("crm_contract", 8);
            if (post_type_exists("crm_lead")) {
                $empty["leads"] = $qlist("crm_lead", 500);
            }
            $empty["offers"] = $qlist("crm_offer", 500);
            if (post_type_exists("lead_task")) {
                $ta = upsellio_crm_app_get_tasks_query_args(150);
                $empty["tasks"] = upsellio_crm_app_sort_tasks_by_priority(get_posts($ta));
            }

            return $empty;

        case "leads":
            $empty["leads"] = $qlist("crm_lead", 300);

            return $empty;

        case "account-360":
        case "clients":
            $empty["clients"] = $qlist("crm_client", 300);

            return $empty;

        case "client-edit":
            return $empty;

        case "contacts":
            $empty["clients"] = $qlist("crm_client", 300);
            $empty["contacts"] = $qlist("crm_contact", 300);

            return $empty;

        case "services":
            $empty["clients"] = $qlist("crm_client", 300);
            $empty["services"] = $qlist("crm_service", 300);

            return $empty;

        case "offers":
            $empty["clients"] = $qlist("crm_client", 300);
            $empty["offers"] = $qlist("crm_offer", 300);
            $empty["followups"] = $qlist("ups_followup_template", 300);
            $empty["offer_layout_templates"] = $qlist("crm_offer_layout", 200, "title", "ASC");

            return $empty;

        case "template-studio":
            if ($template_studio_tab === "contract") {
                $empty["contract_layout_templates"] = $qlist("crm_contract_layout", 200, "title", "ASC");
            } else {
                $empty["offer_layout_templates"] = $qlist("crm_offer_layout", 200, "title", "ASC");
            }

            return $empty;

        case "pipeline":
            $empty["clients"] = $qlist("crm_client", 300);
            $empty["offers"] = $qlist("crm_offer", 300);

            return $empty;

        case "contracts":
            $empty["clients"] = $qlist("crm_client", 300);
            $empty["offers"] = $qlist("crm_offer", 300);
            $empty["contracts"] = $qlist("crm_contract", 300);
            $empty["contract_layout_templates"] = $qlist("crm_contract_layout", 200, "title", "ASC");

            return $empty;

        case "contract-detail":
            $empty["clients"] = $qlist("crm_client", 300);
            $empty["offers"] = $qlist("crm_offer", 300);

            return $empty;

        case "followups":
            $empty["followups"] = $qlist("ups_followup_template", 300);

            return $empty;

        case "tasks":
        case "calendar":
            if (post_type_exists("lead_task")) {
                $empty["tasks"] = upsellio_crm_app_sort_tasks_by_priority(get_posts(upsellio_crm_app_get_tasks_query_args(500)));
            }
            $empty["offers"] = $qlist("crm_offer", 300);

            return $empty;

        case "prospecting":
            $empty["prospects"] = $qlist("crm_prospect", 300);

            return $empty;

        case "inbox":
            if (post_type_exists("crm_offer")) {
                if (is_array($inbox_ctx) && function_exists("upsellio_inbox_query_list")) {
                    $inbox_result = upsellio_inbox_query_list([
                        "folder" => (string) ($inbox_ctx["folder"] ?? "fld_inbox"),
                        "flag" => (string) ($inbox_ctx["flag"] ?? ""),
                        "bucket" => (string) ($inbox_ctx["bucket"] ?? "all"),
                        "segment" => (string) ($inbox_ctx["segment"] ?? ""),
                        "search" => (string) ($inbox_ctx["search"] ?? ""),
                        "page" => (int) ($inbox_ctx["page"] ?? 1),
                        "post_statuses" => $st,
                    ]);
                    $empty["inbox_offers"] = $inbox_result["posts"];
                    $empty["inbox_list_total"] = $inbox_result["total"];
                    $empty["inbox_list_page"] = $inbox_result["page"];
                    $empty["inbox_list_per_page"] = $inbox_result["per_page"];
                } else {
                    $empty["inbox_offers"] = get_posts([
                        "post_type" => "crm_offer",
                        "post_status" => $st,
                        "posts_per_page" => 80,
                        "meta_query" => [[
                            "key" => "_ups_offer_inbox_thread",
                            "compare" => "EXISTS",
                        ]],
                        "orderby" => "modified",
                        "order" => "DESC",
                        "update_post_meta_cache" => false,
                        "update_post_term_cache" => false,
                    ]);
                    $empty["inbox_list_total"] = count($empty["inbox_offers"]);
                    $empty["inbox_list_page"] = 1;
                    $empty["inbox_list_per_page"] = max(1, count($empty["inbox_offers"]));
                }
                $empty["offers"] = $qlist("crm_offer", 250);
            }

            return $empty;

        case "engine":
            return $empty;

        case "alerts":
            $empty["offers"] = $qlist("crm_offer", 300);
            if (post_type_exists("lead_task")) {
                $ta = upsellio_crm_app_get_tasks_query_args(300);
                $empty["tasks"] = upsellio_crm_app_sort_tasks_by_priority(get_posts($ta));
            }

            return $empty;

        case "analytics":
            $empty["offers"] = $qlist("crm_offer", 300);
            $empty["contracts"] = $qlist("crm_contract", 300);

            return $empty;

        case "deals":
            $empty["clients"] = $qlist("crm_client", 300);
            $empty["offers"] = $qlist("crm_offer", 300);
            $empty["followups"] = $qlist("ups_followup_template", 300);
            $empty["offer_layout_templates"] = $qlist("crm_offer_layout", 200, "title", "ASC");

            return $empty;

        case "contact-queue":
            $empty["clients"] = $qlist("crm_client", 300);
            $empty["offers"] = $qlist("crm_offer", 300);
            if (post_type_exists("lead_task")) {
                $ta = upsellio_crm_app_get_tasks_query_args(500);
                $empty["tasks"] = upsellio_crm_app_sort_tasks_by_priority(get_posts($ta));
            }

            return $empty;

        case "search":
            $sq = isset($_GET["crm_q"]) ? sanitize_text_field(wp_unslash($_GET["crm_q"])) : "";
            $sq = trim((string) $sq);
            if ($sq !== "") {
                if (post_type_exists("crm_client")) {
                    $empty["clients"] = get_posts([
                        "post_type" => "crm_client",
                        "post_status" => $st,
                        "s" => $sq,
                        "posts_per_page" => 60,
                    ]);
                }
                if (post_type_exists("crm_offer")) {
                    $empty["offers"] = get_posts([
                        "post_type" => "crm_offer",
                        "post_status" => $st,
                        "s" => $sq,
                        "posts_per_page" => 60,
                    ]);
                }
                if (post_type_exists("lead_task")) {
                    $empty["tasks"] = get_posts([
                        "post_type" => "lead_task",
                        "post_status" => $st,
                        "s" => $sq,
                        "posts_per_page" => 60,
                    ]);
                }
            }

            return $empty;

        case "settings":
            $empty["contract_template_html"] = function_exists("upsellio_contracts_get_default_template_html") ? (string) upsellio_contracts_get_default_template_html() : "";
            $empty["contract_template_css"] = function_exists("upsellio_contracts_get_default_template_css") ? (string) upsellio_contracts_get_default_template_css() : "";

            return $empty;

        default:
            return $empty;
    }
}
