<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_crm_app_template_redirect()
{
    if (!upsellio_crm_app_is_crm_app_view()) {
        return;
    }
    if (!upsellio_crm_app_user_can_access()) {
        auth_redirect();
    }
    upsellio_crm_app_handle_post_actions();

    $view = isset($_GET["view"]) ? sanitize_key((string) wp_unslash($_GET["view"])) : "dashboard";
    if (!in_array($view, ["dashboard", "leads", "account-360", "clients", "client-edit", "contacts", "offers", "deals", "offer_analytics", "template-studio", "services", "pipeline", "contracts", "contract-detail", "followups", "tasks", "calendar", "prospecting", "inbox", "alerts", "analytics", "research", "suggestions", "engine", "settings", "contact-queue", "search"], true)) {
        $view = "dashboard";
    }
    $template_studio_tab = isset($_GET["tab"]) ? sanitize_key((string) wp_unslash($_GET["tab"])) : "offer";
    if (!in_array($template_studio_tab, ["offer", "contract"], true)) {
        $template_studio_tab = "offer";
    }
    $settings_tab = isset($_GET["settings_tab"]) ? sanitize_key((string) wp_unslash($_GET["settings_tab"])) : "general";
    if (!in_array($settings_tab, ["general", "mailbox", "scoring", "offer-template", "contract-template", "automation", "users", "integrations", "notifications", "ai"], true)) {
        $settings_tab = "general";
    }
    $task_tab = isset($_GET["task_tab"]) ? sanitize_key((string) wp_unslash($_GET["task_tab"])) : "all";
    if (!in_array($task_tab, ["all", "today", "tomorrow", "overdue", "week"], true)) {
        $task_tab = "all";
    }
    $task_cal_week_offset = 0;
    if ($view === "tasks" && $task_tab === "week" && isset($_GET["week_offset"])) {
        $task_cal_week_offset = (int) wp_unslash($_GET["week_offset"]);
    }
    $lead_tab = isset($_GET["lead_tab"]) ? sanitize_key((string) wp_unslash($_GET["lead_tab"])) : "all";
    if (!in_array($lead_tab, ["all", "new", "contact", "qualified", "rejected", "converted"], true)) {
        $lead_tab = "all";
    }
    $pipeline_mode = isset($_GET["pipeline_mode"]) ? sanitize_key((string) wp_unslash($_GET["pipeline_mode"])) : "kanban";
    if (!in_array($pipeline_mode, ["kanban", "table", "priorities"], true)) {
        $pipeline_mode = "kanban";
    }
    $analytics_tab = isset($_GET["analytics_tab"]) ? sanitize_key((string) wp_unslash($_GET["analytics_tab"])) : "sales";
    if (!in_array($analytics_tab, ["sales", "leads", "sources", "offers", "site", "followups"], true)) {
        $analytics_tab = "sales";
    }
    $suggestions_tab = isset($_GET["suggestions_tab"]) ? sanitize_key((string) wp_unslash($_GET["suggestions_tab"])) : "seo";
    if (!in_array($suggestions_tab, ["seo", "blog", "ads", "keywords"], true)) {
        $suggestions_tab = "seo";
    }
    $research_tab = isset($_GET["research_tab"]) ? sanitize_key((string) wp_unslash($_GET["research_tab"])) : "keywords";
    if (!in_array($research_tab, ["keywords", "campaigns", "competition", "client_plan"], true)) {
        $research_tab = "keywords";
    }
    $dash_period = isset($_GET["dash_period"]) ? sanitize_key((string) wp_unslash($_GET["dash_period"])) : "30d";
    if (!in_array($dash_period, ["today", "7d", "30d", "month", "quarter"], true)) {
        $dash_period = "30d";
    }
    $dash_src = isset($_GET["dash_src"]) ? sanitize_key((string) wp_unslash($_GET["dash_src"])) : "all";
    if (!in_array($dash_src, ["all", "seo", "google", "meta", "direct", "referral", "paid"], true)) {
        $dash_src = "all";
    }
    $dash_svc = isset($_GET["dash_svc"]) ? sanitize_text_field(wp_unslash($_GET["dash_svc"])) : "all";
    if ($dash_svc === "") {
        $dash_svc = "all";
    }
    $dash_trend_days = isset($_GET["dash_trend"]) ? max(7, min(90, (int) wp_unslash($_GET["dash_trend"]))) : 30;
    $selected_client_id = isset($_GET["client_id"]) ? (int) wp_unslash($_GET["client_id"]) : 0;
    $selected_client = $selected_client_id > 0 ? get_post($selected_client_id) : null;
    if (!($selected_client instanceof WP_Post) || $selected_client->post_type !== "crm_client") {
        $selected_client = null;
        $selected_client_id = 0;
    }
    $crm_new_client = ($view === "client-edit" && isset($_GET["new"]) && (string) wp_unslash($_GET["new"]) === "1");
    $selected_contract_id = isset($_GET["contract_id"]) ? (int) wp_unslash($_GET["contract_id"]) : 0;
    $selected_contract = $selected_contract_id > 0 ? get_post($selected_contract_id) : null;
    if (!($selected_contract instanceof WP_Post) || $selected_contract->post_type !== "crm_contract") {
        $selected_contract = null;
        $selected_contract_id = 0;
    }
    $selected_task_id = isset($_GET["task_id"]) ? (int) wp_unslash($_GET["task_id"]) : 0;
    $selected_task = $selected_task_id > 0 ? get_post($selected_task_id) : null;
    if (!($selected_task instanceof WP_Post) || $selected_task->post_type !== "lead_task") {
        $selected_task = null;
        $selected_task_id = 0;
    }
    $offer_editor_id = isset($_GET["offer_editor_id"]) ? (int) wp_unslash($_GET["offer_editor_id"]) : 0;
    $offer_editor_post = $offer_editor_id > 0 ? get_post($offer_editor_id) : null;
    if (!($offer_editor_post instanceof WP_Post) || $offer_editor_post->post_type !== "crm_offer") {
        $offer_editor_post = null;
        $offer_editor_id = 0;
    }

    $inbox_folder_sel = "fld_inbox";
    $inbox_flag_sel = "";
    $inbox_bucket_sel = "all";
    $inbox_segment_sel = "";
    $inbox_search_q = "";
    $inbox_paged = 1;
    if ($view === "inbox") {
        $inbox_folder_sel = isset($_GET["inbox_folder"]) ? sanitize_key(wp_unslash($_GET["inbox_folder"])) : "fld_inbox";
        if ($inbox_folder_sel === "") {
            $inbox_folder_sel = "fld_inbox";
        }
        $inbox_bucket_sel = isset($_GET["inbox_bucket"]) ? sanitize_key(wp_unslash($_GET["inbox_bucket"])) : "all";
        if (!in_array($inbox_bucket_sel, ["all", "received", "sent"], true)) {
            $inbox_bucket_sel = "all";
        }
        $inbox_flag_sel = isset($_GET["inbox_flag"]) ? sanitize_key(wp_unslash($_GET["inbox_flag"])) : "";
        if (
            $inbox_flag_sel !== "" &&
            function_exists("upsellio_inbox_flag_palette") &&
            !isset(upsellio_inbox_flag_palette()[$inbox_flag_sel])
        ) {
            $inbox_flag_sel = "";
        }
        $inbox_search_q = isset($_GET["inbox_search"]) ? sanitize_text_field(wp_unslash($_GET["inbox_search"])) : "";
        $inbox_search_q = trim($inbox_search_q);
        $inbox_paged = isset($_GET["inbox_paged"]) ? max(1, (int) wp_unslash($_GET["inbox_paged"])) : 1;
        $inbox_segment_sel = isset($_GET["inbox_segment"]) ? sanitize_key(wp_unslash($_GET["inbox_segment"])) : "";
        if (!in_array($inbox_segment_sel, ["", "awaiting", "unlinked", "lead_web", "email_direct", "open_pipeline"], true)) {
            $inbox_segment_sel = "";
        }
    }
    if ($view !== "inbox") {
        $inbox_segment_sel = "";
    }

    $crm_inbox_ctx = null;
    if ($view === "inbox") {
        $crm_inbox_ctx = [
            "folder" => $inbox_folder_sel,
            "flag" => $inbox_flag_sel,
            "bucket" => $inbox_bucket_sel,
            "segment" => $inbox_segment_sel,
            "search" => $inbox_search_q,
            "page" => $inbox_paged,
        ];
    }

    $crm_collections = upsellio_crm_app_load_render_collections($view, $template_studio_tab, $crm_inbox_ctx);
    $clients = $crm_collections["clients"];
    $offers = $crm_collections["offers"];
    $followups = $crm_collections["followups"];
    $prospects = $crm_collections["prospects"];
    $leads = $crm_collections["leads"];
    $contacts = $crm_collections["contacts"];
    $services = $crm_collections["services"];
    $tasks = $crm_collections["tasks"];
    $contracts = $crm_collections["contracts"];
    $offer_layout_templates = $crm_collections["offer_layout_templates"];
    $contract_layout_templates = $crm_collections["contract_layout_templates"];
    $contract_template_html = $crm_collections["contract_template_html"];
    $contract_template_css = $crm_collections["contract_template_css"];
    $inbox_offers = isset($crm_collections["inbox_offers"]) && is_array($crm_collections["inbox_offers"]) ? $crm_collections["inbox_offers"] : [];
    $inbox_list_total = (int) ($crm_collections["inbox_list_total"] ?? count($inbox_offers));
    $inbox_list_page = (int) ($crm_collections["inbox_list_page"] ?? 1);
    $inbox_per_page = (int) ($crm_collections["inbox_list_per_page"] ?? max(1, count($inbox_offers)));
    if ($inbox_per_page <= 0) {
        $inbox_per_page = 30;
    }

    $inbox_offers_visible = $inbox_offers;
    if ($view === "inbox") {
        $sel_pre = isset($_GET["inbox_offer"]) ? (int) wp_unslash($_GET["inbox_offer"]) : 0;
        if ($sel_pre > 0 && function_exists("upsellio_inbox_maybe_prepend_selected_offer")) {
            $inbox_offers_visible = upsellio_inbox_maybe_prepend_selected_offer($inbox_offers_visible, $sel_pre);
        }
        if (function_exists("upsellio_inbox_get_thread_summary")) {
            usort($inbox_offers_visible, static function ($a, $b) {
                $sa = upsellio_inbox_get_thread_summary((int) $a->ID);
                $sb = upsellio_inbox_get_thread_summary((int) $b->ID);
                $ta = strtotime((string) ($sa["last_ts"] ?? "")) ?: 0;
                $tb = strtotime((string) ($sb["last_ts"] ?? "")) ?: 0;

                return $tb <=> $ta;
            });
        }
    }
    $inbox_total_pages = max(1, (int) ceil($inbox_list_total / max(1, $inbox_per_page)));

    $inbox_compose = $view === "inbox" && isset($_GET["inbox_compose"]) && (string) wp_unslash($_GET["inbox_compose"]) === "1";
    $crm_inbox_selected_offer_id = 0;
    if ($view === "inbox" && !$inbox_compose) {
        if (
            $inbox_offers_visible !== [] &&
            function_exists("upsellio_inbox_mark_read") &&
            function_exists("upsellio_inbox_get_thread_summary")
        ) {
            $io_pick = $inbox_offers_visible;
            $sel_inbox = isset($_GET["inbox_offer"]) ? (int) wp_unslash($_GET["inbox_offer"]) : 0;
            if ($sel_inbox > 0) {
                $sel_ok = false;
                foreach ($io_pick as $io) {
                    if ((int) $io->ID === $sel_inbox) {
                        $sel_ok = true;
                        break;
                    }
                }
                if (!$sel_ok) {
                    $sel_inbox = 0;
                }
            }
            if ($sel_inbox <= 0) {
                foreach ($io_pick as $io) {
                    $s = upsellio_inbox_get_thread_summary((int) $io->ID);
                    if (((int) ($s["unread"] ?? 0)) > 0) {
                        $sel_inbox = (int) $io->ID;
                        break;
                    }
                }
                if ($sel_inbox <= 0) {
                    $sel_inbox = (int) $io_pick[0]->ID;
                }
            }
            upsellio_inbox_mark_read($sel_inbox);
            $crm_inbox_selected_offer_id = $sel_inbox;
        } else {
            $crm_inbox_selected_offer_id = isset($_GET["inbox_offer"]) ? (int) wp_unslash($_GET["inbox_offer"]) : 0;
        }
    }

    $crm_inbox_unread_total = function_exists("upsellio_inbox_sidebar_unread_total") ? upsellio_inbox_sidebar_unread_total() : 0;

    $crm_dashboard_payload = null;
    if ($view === "dashboard" && function_exists("upsellio_crm_app_build_dashboard_payload")) {
        $crm_dashboard_payload = upsellio_crm_app_build_dashboard_payload(
            is_array($leads) ? $leads : [],
            is_array($offers) ? $offers : [],
            is_array($tasks) ? $tasks : [],
            [
                "period" => $dash_period,
                "source" => $dash_src,
                "service" => $dash_svc,
                "trend_days" => $dash_trend_days,
            ]
        );
    }

    $pl_label = static function ($value, $type = "generic") {
        $value = (string) $value;
        $maps = [
            "subscription" => ["active" => "aktywny", "paused" => "wstrzymany", "cancelled" => "anulowany"],
            "offer_status" => ["open" => "otwarty", "sent" => "wysłana", "won" => "wygrany", "lost" => "przegrany"],
            "stage" => ["awareness" => "świadomość", "consideration" => "rozważanie", "decision" => "decyzja"],
            "contract_status" => ["draft" => "wersja robocza", "sent" => "wysłana", "signed" => "podpisana", "cancelled" => "anulowana"],
            "prospect_status" => ["active" => "aktywny", "paused" => "wstrzymany", "replied" => "odpowiedział", "converted" => "skonwertowany", "bounced" => "odbity"],
        ];
        if (isset($maps[$type][$value])) {
            return (string) $maps[$type][$value];
        }
        return $value;
    };

    status_header(200);
    nocache_headers();
    ?>
    <!doctype html>
    <html <?php language_attributes(); ?>>
    <head>
      <meta charset="<?php bloginfo("charset"); ?>" />
      <meta name="viewport" content="width=device-width,initial-scale=1" />
      <title>CRM App — Upsellio</title>
      <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet"/>
      <style>
        :root{--bg:#fafaf7;--surface:#fff;--text:#0a1410;--text-2:#3d3d38;--text-3:#7c7c74;--border:#e7e7e1;--border-s:#c9c9c3;--teal:#0d9488;--teal-hover:#0f766e;--teal-h:#0f766e;--teal-dark:#0f766e;--teal-soft:#ccfbf1;--teal-line:#99f6e4;--teal-s:var(--teal-soft);--teal-l:var(--teal-line);--danger:#d94c4c;--warn:#d97706;--success:#16a34a;--sidebar:220px;--r-sm:8px;--r-md:12px;--r-lg:18px;--r-xl:24px;--font-display:'Syne',sans-serif;--font-body:'DM Sans',sans-serif}
        *{box-sizing:border-box;margin:0;padding:0}
        html,body{height:100%;overflow:hidden}
        body{font-family:var(--font-body);background:var(--bg);color:var(--text);line-height:1.55;font-size:14px}
        button,input,select,textarea{font:inherit}
        a{text-decoration:none;color:inherit}
        .layout{display:flex;height:100vh;overflow:hidden}
        .side{width:var(--sidebar);flex-shrink:0;background:#0a1410;color:#d1e8e0;display:flex;flex-direction:column;overflow-y:auto}
        .side-brand{padding:18px 16px 14px;border-bottom:1px solid rgba(255,255,255,.07)}
        .side-logo{display:flex;align-items:center;gap:9px}
        .side-logo-mark{width:30px;height:30px;border-radius:9px;background:linear-gradient(135deg,#21ab82,#0f766e);color:#fff;display:grid;place-items:center;font-family:var(--font-display);font-weight:800;font-size:15px;flex-shrink:0}
        .side-logo-img{height:30px;width:auto;max-width:150px;object-fit:contain}
        .side-logo-name{font-family:var(--font-display);font-size:17px;font-weight:800;letter-spacing:-.4px;color:#fff}
        .side-logo-sub{font-size:9px;color:rgba(255,255,255,.35);letter-spacing:.7px;text-transform:uppercase;margin-top:1px}
        .side-nav{flex:1;padding:12px 8px}
        .side-section{font-size:9px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.28);padding:10px 10px 5px}
        .side-link{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:10px;color:rgba(255,255,255,.55);font-size:12.5px;font-weight:500;transition:.14s ease;margin-bottom:1px;cursor:pointer;border:1px solid transparent}
        .side-link:hover{background:rgba(255,255,255,.07);color:#fff}
        .side-link.active{background:rgba(13,148,136,.18);color:#5eead4;border-color:rgba(13,148,136,.22)}
        .side-icon{font-size:14px;width:16px;text-align:center;flex-shrink:0;opacity:.7}
        .side-link:hover .side-icon,.side-link.active .side-icon{opacity:1}
        .main{flex:1;display:flex;flex-direction:column;min-width:0;overflow:hidden}
        .topbar{background:var(--surface);border-bottom:1px solid var(--border);padding:10px 20px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;flex-shrink:0;z-index:40}
        .topbar-zone--title{display:flex;align-items:center;gap:12px;flex:1;min-width:0}
        .topbar-zone--actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;justify-content:flex-end}
        .topbar-title{font-family:var(--font-display);font-size:16px;font-weight:700;flex-shrink:0}
        .crm-global-search{flex:1;max-width:260px;min-width:min(180px,100%);display:flex;margin:0}
        .crm-global-search input{width:100%;border:1px solid var(--border);border-radius:999px;padding:8px 14px;font-size:12px;background:var(--bg);outline:none;color:var(--text)}
        .crm-global-search input:focus{border-color:var(--teal);box-shadow:0 0 0 3px rgba(13,148,136,.1)}
        .crm-top-quick{font-size:11px;padding:5px 11px;font-weight:700;border-radius:999px}
        .crm-top-bell{font-size:14px;padding:5px 11px;line-height:1;border-radius:999px}
        .crm-top-alerts-badge{display:inline-block;background:#e24b4a;color:#fff;border-radius:999px;font-size:9px;font-weight:800;padding:1px 5px;margin-left:2px;min-width:1.2em;text-align:center}
        .crm-top-user{display:flex;align-items:center;gap:6px;flex-wrap:wrap;font-size:12px;color:var(--text-2);padding-left:8px;border-left:1px solid var(--border);margin-left:2px}
        .crm-top-user-name{font-weight:600;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .crm-top-user-link{font-size:11px;padding:5px 10px}
        .content{padding:20px;flex:1;min-width:0;overflow-y:auto}
        .content.content--inbox{padding:0;overflow:hidden;display:flex;flex-direction:column;min-height:0}
        .content.content--inbox > .grid{flex:1;min-height:0;display:flex;flex-direction:column;margin:0;gap:0}
        .content.content--inbox > .grid > .crm-inbox-card{flex:1;min-height:0;display:flex;flex-direction:column;border-radius:0;border-left:0;border-right:0;border-bottom:0}
        .crm-view-tabs{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px;align-items:center}
        .crm-view-tabs a.crm-tab-link{font-size:13px;font-weight:600;padding:8px 14px;border-radius:999px;border:1px solid var(--border);background:var(--bg);color:var(--text-2)}
        .crm-view-tabs a.crm-tab-link:hover{border-color:var(--teal-line);color:var(--text)}
        .crm-view-tabs a.crm-tab-link.is-active{background:rgba(13,148,136,.14);border-color:rgba(13,148,136,.38);color:var(--teal-hover)}
        .muted{color:var(--text-3)}
        .grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:14px;min-width:0}
        /* Nested dashboard grid must span all 12 columns of the outer .grid (otherwise it defaults to 1 column). */
        .grid > .crm-dash{grid-column:1 / -1;width:100%;min-width:0}
        .crm-dash{max-width:1440px;margin:0 auto;width:100%}
        .crm-dash-pulse{display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:6px}
        .crm-dash-pulse h1{font-family:var(--font-display);font-size:24px;margin:0 0 4px}
        .crm-dash-actions{display:flex;flex-wrap:wrap;gap:8px}
        .crm-dash-actions .btn.alt{font-size:12px;padding:8px 12px}
        .crm-dash-filters{display:flex;flex-wrap:wrap;gap:8px;align-items:center}
        .crm-dash-filters a{font-size:12px;padding:6px 12px;border-radius:999px;border:1px solid var(--border);background:var(--bg);font-weight:600;color:var(--text-2)}
        .crm-dash-filters a.is-on{background:rgba(13,148,136,.16);border-color:var(--teal);color:var(--teal-hover)}
        .crm-kpi-ton-up{border-left:4px solid #0d9488}
        .crm-kpi-ton-down{border-left:4px solid #dc2626}
        .crm-kpi-ton-neutral{border-left:4px solid #94a3b8}
        .crm-prio-row{display:flex;flex-wrap:wrap;gap:12px;padding:12px 0;border-bottom:1px solid var(--border);align-items:flex-start;justify-content:space-between}
        .crm-prio-row:last-child{border-bottom:none}
        .crm-prio-meta{font-size:12px;color:var(--text-3);max-width:520px}
        .crm-snap-stage{padding:10px 0;border-bottom:1px solid var(--border)}
        .crm-snap-stage:last-child{border-bottom:none}
        .crm-snap-bar{display:flex;width:100%;height:8px;border-radius:999px;overflow:hidden;background:var(--border);margin-top:6px}
        .crm-snap-bar > span{display:block;height:100%;flex-shrink:0;min-width:3px}
        .crm-score-hot{color:#0d9488;font-weight:800}
        .crm-score-warm{color:#ca8a04}
        .crm-score-cold{color:#64748b}
        .crm-mini-chart{width:100%;height:220px;display:block}
        .card{grid-column:span 12;background:var(--surface);border:1px solid var(--border);border-radius:18px;padding:20px}
        h2{font-family:var(--font-display);font-size:18px;margin-bottom:10px}
        .kpi{grid-column:span 3}
        .kpi b{font-family:var(--font-display);font-size:30px;display:block;line-height:1.1}
        .half{grid-column:span 6}
        table{width:100%;border-collapse:collapse;font-size:13px}
        th,td{padding:10px 12px;border-bottom:1px solid var(--border);text-align:left;vertical-align:top}
        thead th{font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:var(--text-3)}
        .btn{display:inline-flex;align-items:center;gap:6px;border-radius:999px;padding:7px 14px;font-weight:700;font-size:12px;border:1px solid transparent;cursor:pointer;background:var(--teal);color:#fff;transition:.14s ease}
        .btn:hover{background:var(--teal-hover)}
        .btn.alt{background:var(--surface);border-color:var(--border);color:var(--text)}
        .btn.alt:hover{border-color:var(--border-s)}
        input,select,textarea{width:100%;border:1.5px solid var(--border);background:var(--bg);border-radius:8px;padding:10px 12px;font:inherit;font-size:14px;color:var(--text)}
        .grid2{display:grid;grid-template-columns:1fr 1fr;gap:10px}
        .badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;background:var(--teal-soft);color:#085041;font-size:11px;font-weight:700}
        .timeline-item{display:grid;grid-template-columns:170px 1fr;gap:8px;padding:6px 0;border-bottom:1px dashed var(--border)}
        .pipeline{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px}
        .pipeline-col{background:var(--bg);border:1px solid var(--border);border-radius:14px;padding:10px}
        .pipeline-col h3{font-size:13px;margin:0 0 8px}
        .pipeline-drop{min-height:180px;display:flex;flex-direction:column;gap:8px}
        .pipeline-card{background:#fff;border:1px solid var(--border);border-radius:10px;padding:10px;cursor:grab}
        .pipeline-card.dragging{opacity:.55}
        .pipeline-col.is-over{outline:2px dashed var(--teal)}
        .chart-wrap{height:260px}
        .crm-modal-overlay{position:fixed;inset:0;background:rgba(10,20,16,.48);z-index:200;display:none;align-items:flex-start;justify-content:center;padding:24px;overflow:auto;backdrop-filter:blur(4px)}
        .crm-modal-overlay.open{display:flex}
        .crm-modal{background:var(--surface);border:1px solid var(--border);border-radius:18px;max-width:940px;width:100%;max-height:calc(100vh - 40px);overflow:auto;padding:22px 24px 28px;box-shadow:0 28px 70px rgba(0,0,0,.14)}
        .crm-modal h3{font-family:var(--font-display);font-size:17px;margin:16px 0 10px}
        .crm-modal-actions{display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end;margin-top:16px;padding-top:14px;border-top:1px solid var(--border)}
        .crm-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px}
        .crm-tab{padding:8px 14px;border-radius:999px;border:1px solid var(--border);background:var(--bg);cursor:pointer;font-weight:600;font-size:13px;color:var(--text-2)}
        .crm-tab.active{background:rgba(13,148,136,.14);border-color:rgba(13,148,136,.38);color:var(--teal-hover)}
        .crm-pane{display:none}
        .crm-pane.active{display:grid}
        .side-badge{margin-left:auto;font-size:10px;font-weight:700;padding:2px 7px;border-radius:999px;line-height:1.2}
        .side-badge.hot{background:#e24b4a;color:#fff}
        .offer-dlg-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
        label.odlg-hint{font-size:11px;color:var(--text-3);margin:-4px 0 2px}
        @media(max-width:1100px){html,body{height:auto;overflow:auto}.layout{flex-direction:column;height:auto;min-height:100vh;overflow:visible}.side{width:100%;height:auto;max-height:none;position:relative}.main{overflow:visible;min-height:0}.kpi,.half{grid-column:span 12}.topbar{padding:12px 16px}.content{padding:16px}.content.content--inbox{overflow:visible}}
        @media(max-width:720px){.offer-dlg-grid{grid-template-columns:1fr}}
      </style>
      <?php
      $_ups_crm_theme_css = get_template_directory() . "/assets/css/upsellio.css";
      $_ups_crm_theme_css_v = file_exists($_ups_crm_theme_css) ? (string) filemtime($_ups_crm_theme_css) : "1";
      ?>
      <link rel="stylesheet" href="<?php echo esc_url(get_template_directory_uri() . "/assets/css/upsellio.css"); ?>?ver=<?php echo esc_attr($_ups_crm_theme_css_v); ?>" />
      <style id="upsellio-crm-width-fix">
        /* Theme upsellio.css defines .content { width: min(var(--content),100%); } (--content: 760px) for marketing pages; CRM reuses .content and must stay full width of .main. */
        .layout .main > .content { width: 100%; max-width: none; }
      </style>
      <?php
      if ($view === "inbox") {
          $_ups_crm_inbox_css = get_template_directory() . "/assets/css/crm-app-inbox.css";
          if (is_readable($_ups_crm_inbox_css)) {
              echo '<style id="upsellio-crm-inbox-embedded">';
              // Static theme stylesheet; not user input.
              echo file_get_contents($_ups_crm_inbox_css);
              echo '</style>';
          }
      }
      ?>
    <script>
    (function () {
      "use strict";
      var baselines = new WeakMap();
      var tracked = [];

      function serializeFormState(form) {
        var parts = [];
        var els = form.elements;
        var i;
        var el;
        var t;
        for (i = 0; i < els.length; i++) {
          el = els[i];
          if (!el.name || el.disabled) {
            continue;
          }
          t = (el.type || "").toLowerCase();
          if (t === "submit" || t === "button" || t === "reset" || t === "image") {
            continue;
          }
          if (t === "file") {
            continue;
          }
          if (t === "checkbox" || t === "radio") {
            parts.push(el.name + "=" + (el.checked ? String(el.value || "on") : ""));
            continue;
          }
          if (el.tagName === "SELECT" && el.multiple) {
            var sel = [];
            var k;
            for (k = 0; k < el.options.length; k++) {
              if (el.options[k].selected) {
                sel.push(el.options[k].value);
              }
            }
            sel.sort();
            parts.push(el.name + "=" + encodeURIComponent(sel.join(",")));
            continue;
          }
          parts.push(el.name + "=" + encodeURIComponent(el.value != null ? String(el.value) : ""));
        }
        return parts.sort().join("\u0001");
      }

      function isDirty(form) {
        if (!baselines.has(form)) {
          return false;
        }
        return serializeFormState(form) !== baselines.get(form);
      }

      function markClean(form) {
        if (!form) {
          return;
        }
        baselines.set(form, serializeFormState(form));
        form.dataset.crmDirty = "";
      }

      function syncDirty(form) {
        if (!form || !baselines.has(form)) {
          return;
        }
        form.dataset.crmDirty = serializeFormState(form) !== baselines.get(form) ? "1" : "";
      }

      function register(form) {
        if (!form || baselines.has(form)) {
          return;
        }
        tracked.push(form);
        baselines.set(form, serializeFormState(form));
        form.dataset.crmDirty = "";
        form.addEventListener(
          "input",
          function () {
            if (!baselines.has(form)) {
              return;
            }
            form.dataset.crmDirty = serializeFormState(form) !== baselines.get(form) ? "1" : "";
          },
          true
        );
        form.addEventListener(
          "change",
          function () {
            if (!baselines.has(form)) {
              return;
            }
            form.dataset.crmDirty = serializeFormState(form) !== baselines.get(form) ? "1" : "";
          },
          true
        );
        form.addEventListener("submit", function () {
          window.__upsCrmSkipDirtyUnload = true;
        });
      }

      window.addEventListener("beforeunload", function (e) {
        if (window.__upsCrmSkipDirtyUnload) {
          return;
        }
        var d = false;
        var j;
        for (j = 0; j < tracked.length; j++) {
          if (isDirty(tracked[j])) {
            d = true;
            break;
          }
        }
        if (!d) {
          return;
        }
        e.preventDefault();
        e.returnValue = "";
      });

      window.UpsellioCrmDirty = {
        register: register,
        markClean: markClean,
        isDirty: isDirty,
        sync: syncDirty,
      };

      document.addEventListener("DOMContentLoaded", function () {
        ["ups-crm-offer-layout-form", "ups-crm-contract-layout-form", "ups-crm-settings-offer-template-form", "ups-crm-settings-contract-template-form"].forEach(function (fid) {
          var f = document.getElementById(fid);
          if (f) {
            register(f);
          }
        });
      });
    })();
    </script>
    </head>
    <body>
      <?php
      $custom_logo_id = (int) get_theme_mod("custom_logo");
      $custom_logo_url = $custom_logo_id > 0 ? (string) wp_get_attachment_image_url($custom_logo_id, "full") : "";
      $mailbox_test_key = "ups_crm_mailbox_test_" . get_current_user_id();
      $mailbox_test_result = get_transient($mailbox_test_key);
      if ($mailbox_test_result !== false) {
          delete_transient($mailbox_test_key);
      }
      $smtp_test_key = "ups_crm_smtp_test_" . get_current_user_id();
      $smtp_test_result = get_transient($smtp_test_key);
      if ($smtp_test_result !== false) {
          delete_transient($smtp_test_key);
      }
      $crm_notice_key = "ups_crm_notice_" . get_current_user_id();
      $crm_notice_flash = get_transient($crm_notice_key);
      if (is_array($crm_notice_flash) && !empty($crm_notice_flash["message"])) {
          delete_transient($crm_notice_key);
      } else {
          $crm_notice_flash = null;
      }
      $view_titles = [
          "dashboard" => "Pulpit",
          "leads" => "Leady",
          "account-360" => "Karta 360",
          "clients" => "Klienci",
          "client-edit" => "Edycja klienta",
          "contacts" => "Kontakty",
          "offers" => "Oferty",
          "deals" => "Deale",
          "offer_analytics" => "Analityka oferty",
          "template-studio" => "Generator szablonów",
          "services" => "Katalog usług",
          "pipeline" => "Pipeline",
          "contracts" => "Umowy",
          "contract-detail" => "Szczegóły umowy",
          "followups" => "Szablony follow-up",
          "tasks" => "Zadania",
          "calendar" => "Kalendarz",
          "prospecting" => "Produkcja",
          "inbox" => "Inbox",
          "alerts" => "Alerty",
          "analytics" => "Analityka",
          "research" => "Research Centrum",
          "suggestions" => "Sugestie AI",
          "engine" => "Silnik sprzedaży",
          "settings" => "Ustawienia",
          "contact-queue" => "Do kontaktu",
          "search" => "Wyniki wyszukiwania",
      ];
      $current_view_title = isset($view_titles[$view]) ? (string) $view_titles[$view] : "CRM App";
      $crm_settings_tab_labels = [
          "general" => "Ogólne",
          "mailbox" => "Mail / skrzynki",
          "scoring" => "Scoring",
          "offer-template" => "Szablon oferty",
          "contract-template" => "Szablon umowy",
          "automation" => "Automatyzacje",
          "users" => "Użytkownicy",
          "integrations" => "Integracje",
          "notifications" => "Powiadomienia",
          "ai" => "AI / Anthropic",
      ];
      $crm_topbar_title = $current_view_title;
      if ($view === "settings") {
          $crm_topbar_title = isset($crm_settings_tab_labels[$settings_tab])
              ? "Ustawienia · " . $crm_settings_tab_labels[$settings_tab]
              : "Ustawienia";
      }
      if ($view === "suggestions") {
          $crm_suggestions_tab_labels = [
              "seo" => "SEO",
              "blog" => "Blog",
              "ads" => "Google Ads",
              "keywords" => __("Słowa kluczowe", "upsellio"),
          ];
          $crm_topbar_title = "Sugestie AI · " . ($crm_suggestions_tab_labels[$suggestions_tab] ?? "");
      }
      $crm_search_q = isset($_GET["crm_q"]) ? sanitize_text_field(wp_unslash($_GET["crm_q"])) : "";
      $crm_search_q = trim((string) $crm_search_q);
      $crm_topbar_alert_count = 0;
      if (is_array($tasks)) {
          foreach ($tasks as $_t_alert) {
              if (!($_t_alert instanceof WP_Post)) {
                  continue;
              }
              $_tid = (int) $_t_alert->ID;
              $_tst = (string) get_post_meta($_tid, "_upsellio_task_status", true);
              if (in_array($_tst, ["done", "cancelled"], true)) {
                  continue;
              }
              $_due = (int) get_post_meta($_tid, "_upsellio_task_due_at", true);
              if ($_due > 0 && $_due < time()) {
                  $crm_topbar_alert_count++;
              }
          }
      }
      ?>
      <div class="layout">
        <aside class="side">
          <div class="side-brand">
            <div class="side-logo">
              <?php if ($custom_logo_url !== "") : ?>
                <img src="<?php echo esc_url($custom_logo_url); ?>" alt="Upsellio" class="side-logo-img" />
              <?php else : ?>
                <div class="side-logo-mark">U</div>
                <div>
                  <div class="side-logo-name">Upsellio</div>
                  <div class="side-logo-sub">CRM App</div>
                </div>
              <?php endif; ?>
            </div>
          </div>
          <nav class="side-nav crm-side-nav" aria-label="<?php esc_attr_e("Nawigacja CRM", "upsellio"); ?>">
            <div class="side-section"><?php esc_html_e("Praca", "upsellio"); ?></div>
            <a class="side-link <?php echo $view === "dashboard" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "dashboard"], home_url("/crm-app/"))); ?>"><span class="side-icon" aria-hidden="true">⊞</span> <?php esc_html_e("Pulpit", "upsellio"); ?></a>
            <a class="side-link <?php echo $view === "inbox" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "inbox"], home_url("/crm-app/"))); ?>"><span class="side-icon" aria-hidden="true">✉</span> <?php esc_html_e("Inbox", "upsellio"); ?><?php if ($crm_inbox_unread_total > 0) : ?><span class="side-badge hot"><?php echo (int) $crm_inbox_unread_total; ?></span><?php endif; ?></a>
            <a class="side-link <?php echo $view === "tasks" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "tasks"], home_url("/crm-app/"))); ?>"><span class="side-icon" aria-hidden="true">✓</span> <?php esc_html_e("Zadania", "upsellio"); ?></a>
            <div class="side-section" style="margin-top:8px"><?php esc_html_e("Sprzedaż", "upsellio"); ?></div>
            <a class="side-link <?php echo $view === "leads" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "leads"], home_url("/crm-app/"))); ?>"><span class="side-icon" aria-hidden="true">↓</span> <?php esc_html_e("Leady", "upsellio"); ?></a>
            <a class="side-link <?php echo $view === "pipeline" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "pipeline"], home_url("/crm-app/"))); ?>"><span class="side-icon" aria-hidden="true">▣</span> <?php esc_html_e("Pipeline", "upsellio"); ?></a>
            <a class="side-link <?php echo in_array($view, ["offers", "deals", "offer_analytics"], true) ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "offers"], home_url("/crm-app/"))); ?>"><span class="side-icon" aria-hidden="true">◈</span> <?php esc_html_e("Oferty", "upsellio"); ?></a>
            <a class="side-link <?php echo $view === "clients" || $view === "client-edit" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "clients"], home_url("/crm-app/"))); ?>"><span class="side-icon" aria-hidden="true">◎</span> <?php esc_html_e("Klienci", "upsellio"); ?></a>
            <div class="side-section" style="margin-top:8px"><?php esc_html_e("Dane", "upsellio"); ?></div>
            <a class="side-link <?php echo $view === "analytics" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "analytics"], home_url("/crm-app/"))); ?>"><span class="side-icon" aria-hidden="true">↗</span> <?php esc_html_e("Analityka", "upsellio"); ?></a>
            <a class="side-link <?php echo $view === "research" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "research", "research_tab" => "keywords"], home_url("/crm-app/"))); ?>"><span class="side-icon" aria-hidden="true">🔬</span> <?php esc_html_e("Research", "upsellio"); ?></a>
            <a class="side-link <?php echo $view === "suggestions" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "suggestions", "suggestions_tab" => "seo"], home_url("/crm-app/"))); ?>"><span class="side-icon" aria-hidden="true">✨</span> <?php esc_html_e("Sugestie AI", "upsellio"); ?></a>
            <div class="side-section" style="margin-top:8px"><?php esc_html_e("System", "upsellio"); ?></div>
            <a class="side-link <?php echo $view === "settings" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "settings", "settings_tab" => "general"], home_url("/crm-app/"))); ?>"><span class="side-icon" aria-hidden="true">⚙</span> <?php esc_html_e("Ustawienia", "upsellio"); ?></a>
          </nav>
        </aside>
        <main class="main">
          <div class="topbar">
            <div class="topbar-zone topbar-zone--title">
              <div class="topbar-title"><?php echo esc_html($crm_topbar_title); ?></div>
              <form class="crm-global-search" method="get" action="<?php echo esc_url(home_url("/crm-app/")); ?>">
                <input type="hidden" name="view" value="search" />
                <input type="search" name="crm_q" value="<?php echo esc_attr($crm_search_q); ?>" placeholder="<?php esc_attr_e('Szukaj klientów, deali, zadań…', 'upsellio'); ?>" autocomplete="off" aria-label="<?php esc_attr_e('Szukaj w CRM', 'upsellio'); ?>" />
              </form>
            </div>
            <div class="topbar-zone topbar-zone--actions">
              <a class="btn alt crm-top-quick" href="<?php echo esc_url(add_query_arg(["view" => "alerts"], home_url("/crm-app/"))); ?>"><?php esc_html_e("Alerty", "upsellio"); ?><?php if ($crm_topbar_alert_count > 0) : ?><span class="crm-top-alerts-badge"><?php echo (int) min(99, $crm_topbar_alert_count); ?></span><?php endif; ?></a>
              <a class="btn alt crm-top-quick" href="<?php echo esc_url(add_query_arg(["view" => "leads"], home_url("/crm-app/"))); ?>" title="<?php esc_attr_e("Lista leadów — import ręczny / CSV wg procesu", "upsellio"); ?>"><?php esc_html_e("Import", "upsellio"); ?></a>
              <a class="btn crm-top-quick" href="<?php echo esc_url(add_query_arg(["view" => "leads"], home_url("/crm-app/"))); ?>"><?php esc_html_e("+ Nowy lead", "upsellio"); ?></a>
              <div class="crm-top-user">
                <span class="crm-top-user-name"><?php echo esc_html(wp_get_current_user()->display_name ?: __("Ty", "upsellio")); ?></span>
                <a class="btn alt crm-top-user-link" href="<?php echo esc_url(admin_url("profile.php")); ?>"><?php esc_html_e("Profil", "upsellio"); ?></a>
                <a class="btn alt crm-top-user-link" href="<?php echo esc_url(wp_logout_url(home_url("/crm-app/"))); ?>"><?php esc_html_e("Wyloguj", "upsellio"); ?></a>
              </div>
            </div>
          </div>
          <div class="content<?php echo $view === "inbox" ? " content--inbox" : ""; ?>">
          <?php if (is_array($crm_notice_flash)) : ?>
            <div class="card" style="margin-bottom:14px;border-color:#fecdd3;background:#fff1f2">
              <p style="margin:0;font-size:14px"><?php echo esc_html((string) $crm_notice_flash["message"]); ?></p>
            </div>
          <?php endif; ?>
          <div id="ups-loss-modal" class="crm-modal-overlay" aria-hidden="true">
            <div class="crm-modal" style="max-width:440px" onclick="event.stopPropagation()">
              <h3 style="margin-top:0">Dlaczego przegraliśmy ten deal?</h3>
              <p class="muted" style="font-size:13px;margin-top:0">Wymagane przy przeniesieniu karty do kolumny Lost w lejku.</p>
              <label>Powód</label>
              <select id="ups-loss-modal-reason" style="margin-bottom:10px">
                <option value="">— wybierz —</option>
                <option value="price">cena</option>
                <option value="budget">budżet</option>
                <option value="competitor">konkurencja</option>
                <option value="timing">timing</option>
                <option value="no_need">brak potrzeby</option>
                <option value="no_decision">brak decyzji</option>
                <option value="no_response">brak odpowiedzi</option>
                <option value="scope">zakres</option>
              </select>
              <label>Komentarz (opcjonalnie)</label>
              <textarea id="ups-loss-modal-note" rows="3" placeholder="Np. nazwa konkurenta, kontekst…"></textarea>
              <div class="crm-modal-actions">
                <button type="button" class="btn alt" id="ups-loss-modal-cancel">Anuluj</button>
                <button type="button" class="btn" id="ups-loss-modal-ok">Potwierdź</button>
              </div>
            </div>
          </div>
          <script>
          window.upsellioOpenLossModal = function () {
            return new Promise(function (resolve) {
              var overlay = document.getElementById("ups-loss-modal");
              var okBtn = document.getElementById("ups-loss-modal-ok");
              var cancelBtn = document.getElementById("ups-loss-modal-cancel");
              var sel = document.getElementById("ups-loss-modal-reason");
              var noteEl = document.getElementById("ups-loss-modal-note");
              if (!overlay || !okBtn || !cancelBtn || !sel) {
                resolve(null);
                return;
              }
              sel.value = "";
              if (noteEl) {
                noteEl.value = "";
              }
              function cleanup() {
                overlay.classList.remove("open");
                overlay.removeEventListener("click", onOverlay);
                okBtn.removeEventListener("click", onOk);
                cancelBtn.removeEventListener("click", onCancel);
              }
              function onOverlay(e) {
                if (e.target === overlay) {
                  cleanup();
                  resolve(null);
                }
              }
              function onOk() {
                var v = sel.value || "";
                if (!v) {
                  alert("Wybierz powód przegranej.");
                  return;
                }
                cleanup();
                resolve({ reason: v, note: noteEl ? noteEl.value || "" : "" });
              }
              function onCancel() {
                cleanup();
                resolve(null);
              }
              overlay.classList.add("open");
              overlay.addEventListener("click", onOverlay);
              okBtn.addEventListener("click", onOk);
              cancelBtn.addEventListener("click", onCancel);
            });
          };
          </script>
          <div class="grid">
            <?php if ($view === "search") : ?>
              <section class="card" style="grid-column:span 12">
                <h2>Wyniki wyszukiwania</h2>
                <?php if ($crm_search_q === "") : ?>
                  <p class="muted"><?php esc_html_e("Wpisz frazę w polu u góry strony i zatwierdź Enter.", "upsellio"); ?></p>
                <?php else : ?>
                  <p class="muted" style="margin-bottom:14px"><?php esc_html_e("Szukana fraza:", "upsellio"); ?> <strong><?php echo esc_html($crm_search_q); ?></strong></p>
                  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:18px">
                    <div>
                      <h3 style="font-size:15px;margin:0 0 8px;font-family:var(--font-display)"><?php esc_html_e("Klienci", "upsellio"); ?></h3>
                      <?php if (empty($clients)) : ?>
                        <p class="muted"><?php esc_html_e("Brak wyników.", "upsellio"); ?></p>
                      <?php else : ?>
                        <ul style="margin:0;padding-left:18px;line-height:1.55;font-size:14px">
                          <?php foreach (array_slice($clients, 0, 25) as $c_search) : ?>
                            <li><a href="<?php echo esc_url(add_query_arg(["view" => "client-edit", "client_id" => (int) $c_search->ID], home_url("/crm-app/"))); ?>"><?php echo esc_html((string) $c_search->post_title); ?></a></li>
                          <?php endforeach; ?>
                        </ul>
                      <?php endif; ?>
                    </div>
                    <div>
                      <h3 style="font-size:15px;margin:0 0 8px;font-family:var(--font-display)"><?php esc_html_e("Deale (oferty)", "upsellio"); ?></h3>
                      <?php if (empty($offers)) : ?>
                        <p class="muted"><?php esc_html_e("Brak wyników.", "upsellio"); ?></p>
                      <?php else : ?>
                        <ul style="margin:0;padding-left:18px;line-height:1.55;font-size:14px">
                          <?php foreach (array_slice($offers, 0, 25) as $o_search) : ?>
                            <li>
                              <a href="<?php echo esc_url(add_query_arg(["view" => "deals", "offer_editor_id" => (int) $o_search->ID], home_url("/crm-app/"))); ?>"><?php echo esc_html((string) $o_search->post_title); ?></a>
                              <?php
                              $ocid = (int) get_post_meta((int) $o_search->ID, "_ups_offer_client_id", true);
                              if ($ocid > 0) :
                                  ?>
                                <span class="muted"> · <?php echo esc_html(get_the_title($ocid)); ?></span>
                              <?php endif; ?>
                            </li>
                          <?php endforeach; ?>
                        </ul>
                      <?php endif; ?>
                    </div>
                    <div>
                      <h3 style="font-size:15px;margin:0 0 8px;font-family:var(--font-display)"><?php esc_html_e("Zadania", "upsellio"); ?></h3>
                      <?php if (empty($tasks)) : ?>
                        <p class="muted"><?php esc_html_e("Brak wyników.", "upsellio"); ?></p>
                      <?php else : ?>
                        <ul style="margin:0;padding-left:18px;line-height:1.55;font-size:14px">
                          <?php foreach (array_slice($tasks, 0, 25) as $t_search) : ?>
                            <li><a href="<?php echo esc_url(add_query_arg(["view" => "tasks", "task_id" => (int) $t_search->ID], home_url("/crm-app/"))); ?>"><?php echo esc_html((string) $t_search->post_title); ?></a></li>
                          <?php endforeach; ?>
                        </ul>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endif; ?>
              </section>
            <?php endif; ?>
            <?php if ($view === "contact-queue") : ?>
              <?php
              $today_start_ts = (int) strtotime(wp_date("Y-m-d 00:00:00"));
              $today_end_ts = $today_start_ts + DAY_IN_SECONDS - 1;
              $cq_overdue = [];
              $cq_today = [];
              $cq_planned = [];
              foreach ($tasks as $cq_task) {
                  $cq_tid = (int) $cq_task->ID;
                  $cq_st = (string) get_post_meta($cq_tid, "_upsellio_task_status", true);
                  if (in_array($cq_st, ["done", "cancelled"], true)) {
                      continue;
                  }
                  $cq_due = (int) get_post_meta($cq_tid, "_upsellio_task_due_at", true);
                  if ($cq_due <= 0) {
                      $cq_planned[] = $cq_task;
                  } elseif ($cq_due < $today_start_ts) {
                      $cq_overdue[] = $cq_task;
                  } elseif ($cq_due <= $today_end_ts) {
                      $cq_today[] = $cq_task;
                  } else {
                      $cq_planned[] = $cq_task;
                  }
              }
              ?>
              <section class="card" style="grid-column:span 12">
                <h2><?php esc_html_e("Do kontaktu", "upsellio"); ?></h2>
                <p class="muted"><?php esc_html_e("Zadania otwarte wg terminu oraz skrót do inboxa (czeka na odpowiedź).", "upsellio"); ?></p>
                <p style="margin:14px 0;display:flex;flex-wrap:wrap;gap:10px;align-items:center">
                  <a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "inbox", "inbox_segment" => "awaiting"], home_url("/crm-app/"))); ?>"><?php esc_html_e("Inbox — do odpowiedzi", "upsellio"); ?></a>
                  <a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "tasks", "task_tab" => "overdue"], home_url("/crm-app/"))); ?>"><?php esc_html_e("Zadania zaległe", "upsellio"); ?></a>
                </p>
                <?php
                $cq_render_bucket = static function (string $title, array $rows, string $tone = "") {
                    ?>
                <div style="margin-top:18px;padding-top:14px;border-top:1px solid var(--border)">
                  <h3 style="font-size:15px;margin:0 0 10px;font-family:var(--font-display);color:<?php echo $tone !== "" ? esc_attr($tone) : "inherit"; ?>"><?php echo esc_html($title); ?> <span class="muted">(<?php echo count($rows); ?>)</span></h3>
                  <?php if ($rows === []) : ?>
                    <p class="muted" style="margin:0;font-size:13px"><?php esc_html_e("Brak pozycji.", "upsellio"); ?></p>
                  <?php else : ?>
                    <table>
                      <thead><tr><th><?php esc_html_e("Zadanie", "upsellio"); ?></th><th><?php esc_html_e("Deal", "upsellio"); ?></th><th><?php esc_html_e("Termin", "upsellio"); ?></th><th></th></tr></thead>
                      <tbody>
                        <?php foreach ($rows as $cq_t) : ?>
                          <?php
                          $cx = (int) $cq_t->ID;
                          $cx_oid = (int) get_post_meta($cx, "_upsellio_task_offer_id", true);
                          $cx_due = (int) get_post_meta($cx, "_upsellio_task_due_at", true);
                          ?>
                          <tr>
                            <td><?php echo esc_html((string) $cq_t->post_title); ?></td>
                            <td><?php echo $cx_oid > 0 ? '<a href="' . esc_url(add_query_arg(["view" => "deals", "offer_editor_id" => $cx_oid], home_url("/crm-app/"))) . '">' . esc_html(get_the_title($cx_oid)) . "</a>" : "—"; ?></td>
                            <td><?php echo $cx_due > 0 ? esc_html(wp_date("d.m.Y H:i", $cx_due)) : "—"; ?></td>
                            <td style="white-space:nowrap">
                              <a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "tasks", "task_id" => $cx], home_url("/crm-app/"))); ?>"><?php esc_html_e("Wykonaj", "upsellio"); ?></a>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  <?php endif; ?>
                </div>
                    <?php
                };
                $cq_render_bucket(__("Dziś", "upsellio"), $cq_today);
                $cq_render_bucket(__("Zaległe", "upsellio"), $cq_overdue, "#b91c1c");
                $cq_render_bucket(__("Zaplanowane", "upsellio"), $cq_planned);
                ?>
              </section>
            <?php endif; ?>
            <?php if ($view === "dashboard") : ?>
              <?php
              $global_activity = upsellio_crm_app_collect_recent_activity_entries(48, false);
              $recent_activity = array_slice($global_activity, 0, 14);
              $DK = is_array($crm_dashboard_payload) ? $crm_dashboard_payload : [];
              $kpi = isset($DK["kpi"]) && is_array($DK["kpi"]) ? $DK["kpi"] : [];
              $prio_list = isset($DK["priorities"]) && is_array($DK["priorities"]) ? $DK["priorities"] : [];
              $snap = isset($DK["snapshot"]) && is_array($DK["snapshot"]) ? $DK["snapshot"] : [];
              $src_rows = isset($DK["sources_rows"]) && is_array($DK["sources_rows"]) ? $DK["sources_rows"] : [];
              $lost_r = isset($DK["lost_reasons"]) && is_array($DK["lost_reasons"]) ? $DK["lost_reasons"] : [];
              $trend = isset($DK["trend"]) && is_array($DK["trend"]) ? $DK["trend"] : ["labels" => [], "leads" => [], "qualified" => [], "sent" => [], "won" => []];
              $diag_txt = isset($DK["diagnosis"]) ? (string) $DK["diagnosis"] : "";
              $dash_alerts = isset($DK["alerts"]) && is_array($DK["alerts"]) ? $DK["alerts"] : [];
              $hot_leads_d = isset($DK["hot_leads"]) && is_array($DK["hot_leads"]) ? $DK["hot_leads"] : [];
              $actions_hint = isset($DK["actions_today_hint"]) ? (int) $DK["actions_today_hint"] : 0;
              $dash_base_q = ["view" => "dashboard", "dash_period" => $dash_period, "dash_src" => $dash_src, "dash_svc" => $dash_svc, "dash_trend" => $dash_trend_days];
              $dash_url = static function (array $extra) use ($dash_base_q) {
                  return esc_url(add_query_arg(array_merge($dash_base_q, $extra), home_url("/crm-app/")));
              };
              $k_new_delta = (int) ($kpi["new_leads_delta_pct"] ?? 0);
              $k_tone = (string) ($kpi["new_leads_tone"] ?? "neutral");
              $kpi_lead_class = $k_tone === "up" ? "crm-kpi-ton-up" : ($k_tone === "down" ? "crm-kpi-ton-down" : "crm-kpi-ton-neutral");
              $cur_u = wp_get_current_user();
              $pulse_name = $cur_u->exists() ? (string) $cur_u->display_name : __("Ty", "upsellio");
              $snap_total_val = 0.0;
              foreach ($snap as $sx) {
                  $snap_total_val += (float) ($sx["value_pln"] ?? 0);
              }
              $snap_colors = ["#0d9488", "#14b8a6", "#5eead4", "#f59e0b"];
              ?>
              <div class="crm-dash grid" style="gap:16px">
                <div class="crm-dash-pulse card" style="grid-column:1/-1;padding:18px 20px">
                  <div>
                    <h1><?php echo esc_html(sprintf(/* translators: %s: first name */ __("Witaj, %s", "upsellio"), $pulse_name !== "" ? $pulse_name : __("operatorze", "upsellio"))); ?></h1>
                    <p class="muted" style="margin:0;font-size:14px"><?php echo esc_html(sprintf(/* translators: %d: action count */ __("Masz ok. %d działań sprzedażowych do rozpatrzenia (lead / oferta / zadanie).", "upsellio"), max(1, $actions_hint))); ?></p>
                  </div>
                  <div style="display:flex;flex-direction:column;align-items:flex-end;gap:10px">
                    <div class="crm-dash-filters" aria-label="<?php esc_attr_e("Zakres", "upsellio"); ?>">
                      <?php foreach (["today" => __("Dziś", "upsellio"), "7d" => "7 dni", "30d" => "30 dni", "month" => __("Miesiąc", "upsellio"), "quarter" => __("Kwartał", "upsellio")] as $pk => $plab) : ?>
                        <a href="<?php echo $dash_url(["dash_period" => $pk]); ?>" class="<?php echo $dash_period === $pk ? "is-on" : ""; ?>"><?php echo esc_html($plab); ?></a>
                      <?php endforeach; ?>
                    </div>
                    <div class="crm-dash-filters" aria-label="<?php esc_attr_e("Źródło", "upsellio"); ?>">
                      <?php foreach (["all" => __("Wszystkie", "upsellio"), "seo" => "SEO", "google" => "Google Ads", "meta" => "Meta Ads", "direct" => "Direct", "referral" => "Referral", "paid" => __("Płatne", "upsellio")] as $sk => $slab) : ?>
                        <a href="<?php echo $dash_url(["dash_src" => $sk]); ?>" class="<?php echo $dash_src === $sk ? "is-on" : ""; ?>"><?php echo esc_html($slab); ?></a>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
                <div class="crm-dash-actions card" style="grid-column:1/-1;display:flex;flex-wrap:wrap;gap:8px;padding:12px 16px;align-items:center">
                  <span class="muted" style="font-size:12px;margin-right:8px"><?php esc_html_e("Szybkie akcje:", "upsellio"); ?></span>
                  <a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "leads"], home_url("/crm-app/"))); ?>"><?php esc_html_e("+ Lead", "upsellio"); ?></a>
                  <a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "client-edit", "new" => "1"], home_url("/crm-app/"))); ?>"><?php esc_html_e("+ Klient", "upsellio"); ?></a>
                  <a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "offers"], home_url("/crm-app/"))); ?>"><?php esc_html_e("+ Oferta", "upsellio"); ?></a>
                  <a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "tasks"], home_url("/crm-app/"))); ?>"><?php esc_html_e("+ Zadanie", "upsellio"); ?></a>
                  <a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "inbox"], home_url("/crm-app/"))); ?>"><?php esc_html_e("Inbox", "upsellio"); ?></a>
                  <form method="get" action="<?php echo esc_url(home_url("/crm-app/")); ?>" style="display:flex;gap:8px;align-items:center;margin-left:auto;flex-wrap:wrap">
                    <input type="hidden" name="view" value="dashboard" />
                    <input type="hidden" name="dash_period" value="<?php echo esc_attr($dash_period); ?>" />
                    <input type="hidden" name="dash_src" value="<?php echo esc_attr($dash_src); ?>" />
                    <input type="hidden" name="dash_trend" value="<?php echo esc_attr((string) $dash_trend_days); ?>" />
                    <label class="muted" style="font-size:12px"><?php esc_html_e("Filtr usługi (tekst):", "upsellio"); ?></label>
                    <input type="search" name="dash_svc" value="<?php echo esc_attr($dash_svc === "all" ? "" : $dash_svc); ?>" placeholder="<?php esc_attr_e("np. Google Ads, strona…", "upsellio"); ?>" style="max-width:220px;padding:8px 12px;font-size:13px" />
                    <button type="submit" class="btn alt" style="padding:8px 14px;font-size:12px"><?php esc_html_e("Zastosuj", "upsellio"); ?></button>
                  </form>
                </div>

                <?php
                $dashboard_hot_offers = function_exists("upsellio_offers_active_today") ? upsellio_offers_active_today(5) : [];
                ?>
                <?php if (!empty($dashboard_hot_offers)) : ?>
                <div class="card" style="grid-column:1/-1;padding:16px 20px">
                  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                    <h3 style="margin:0;font-size:14px"><?php esc_html_e("Aktywność na ofertach dziś", "upsellio"); ?></h3>
                    <a href="<?php echo esc_url(add_query_arg(["view" => "offers"], home_url("/crm-app/"))); ?>"
                       style="font-size:12px;color:var(--teal)"><?php esc_html_e("Wszystkie →", "upsellio"); ?></a>
                  </div>
                  <div style="display:flex;flex-direction:column;gap:6px">
                  <?php foreach ($dashboard_hot_offers as $dho) : ?>
                    <div style="display:flex;align-items:center;gap:12px;padding:8px 0;border-bottom:1px solid var(--border)">
                      <div style="width:32px;height:32px;border-radius:50%;border:2px solid <?php echo (int) ($dho["score"] ?? 0) >= 70 ? "#0d9488" : "#e2e5de"; ?>;display:grid;place-items:center;font-size:11px;font-weight:800;color:<?php echo (int) ($dho["score"] ?? 0) >= 70 ? "#0f766e" : "var(--text-3)"; ?>;flex-shrink:0">
                        <?php echo (int) ($dho["score"] ?? 0); ?>
                      </div>
                      <div style="flex:1;min-width:0">
                        <div style="font-size:13px;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                          <?php echo esc_html((string) ($dho["client_name"] ?? "")); ?>
                          <?php if (!empty($dho["hot"])) {
                              echo " 🔥";
                          } ?>
                        </div>
                        <div style="font-size:11px;color:var(--text-3)">
                          <?php echo esc_html((string) ($dho["title"] ?? "")); ?>
                          · <?php echo (int) ($dho["recent_events"] ?? 0); ?> <?php esc_html_e("zdarzeń dziś", "upsellio"); ?>
                        </div>
                      </div>
                      <a href="<?php echo esc_url(add_query_arg(["view" => "offer_analytics", "offer_id" => (int) ($dho["id"] ?? 0), "list_view" => "offers"], home_url("/crm-app/"))); ?>"
                         class="btn alt" style="font-size:11px;padding:4px 9px;flex-shrink:0">
                        <?php esc_html_e("Analityka", "upsellio"); ?>
                      </a>
                    </div>
                  <?php endforeach; ?>
                  </div>
                </div>
                <?php endif; ?>

                <section class="card kpi <?php echo esc_attr($kpi_lead_class); ?>">
                  <span class="muted"><?php esc_html_e("Nowe leady", "upsellio"); ?></span>
                  <b><?php echo esc_html((string) (int) ($kpi["new_leads"] ?? 0)); ?></b>
                  <span class="muted" style="font-size:12px"><?php echo esc_html(sprintf(/* translators: 1: percent */ __("vs poprzedni okres: %+d%%", "upsellio"), $k_new_delta)); ?></span>
                </section>
                <section class="card kpi">
                  <span class="muted"><?php esc_html_e("Leady do kontaktu", "upsellio"); ?></span>
                  <b><?php echo esc_html((string) (int) ($kpi["need_contact"] ?? 0)); ?></b>
                  <span class="muted" style="font-size:12px"><?php echo esc_html(sprintf(/* translators: %d overdue */ __("%d zaległych (>48h)", "upsellio"), (int) ($kpi["need_contact_overdue"] ?? 0))); ?></span>
                  <div style="margin-top:8px"><a class="btn alt" style="font-size:12px;padding:6px 12px" href="<?php echo esc_url(add_query_arg(["view" => "leads", "lead_tab" => "contact"], home_url("/crm-app/"))); ?>"><?php esc_html_e("Otwórz leady", "upsellio"); ?></a></div>
                </section>
                <section class="card kpi">
                  <span class="muted"><?php esc_html_e("Oferty do follow-upu", "upsellio"); ?></span>
                  <b><?php echo esc_html((string) (int) ($kpi["offers_followup"] ?? 0)); ?></b>
                  <span class="muted" style="font-size:12px"><?php echo esc_html(sprintf(/* translators: %d stale */ __("%d bez aktywności >3 dni", "upsellio"), (int) ($kpi["offers_followup_stale"] ?? 0))); ?></span>
                  <div style="margin-top:8px"><a class="btn alt" style="font-size:12px;padding:6px 12px" href="<?php echo esc_url(add_query_arg(["view" => "pipeline"], home_url("/crm-app/"))); ?>"><?php esc_html_e("Pipeline", "upsellio"); ?></a></div>
                </section>
                <section class="card kpi">
                  <span class="muted"><?php esc_html_e("Wartość otwartego pipeline", "upsellio"); ?></span>
                  <b><?php echo esc_html(number_format((float) ($kpi["pipeline_open_value"] ?? 0), 0, ",", " ")); ?> PLN</b>
                  <span class="muted" style="font-size:12px"><?php echo esc_html(sprintf(/* translators: amount PLN */ __("Najbliżej wygranej (ważone): %s PLN", "upsellio"), number_format((float) ($kpi["nearest_win_value"] ?? 0), 0, ",", " "))); ?></span>
                </section>
                <?php
                $all_clients_ch = get_posts(["post_type" => "crm_client", "post_status" => "publish", "posts_per_page" => 200, "fields" => "ids"]);
                $cancelled_90d = 0;
                $active_count = 0;
                $threshold_90 = time() - 90 * DAY_IN_SECONDS;
                foreach ($all_clients_ch as $cid_ch) {
                    $cid_ch = (int) $cid_ch;
                    $sub = (string) get_post_meta($cid_ch, "_ups_client_subscription_status", true);
                    if ($sub === "active") {
                        $active_count++;
                    }
                    if ($sub === "cancelled") {
                        $cancel_date = strtotime((string) get_post_meta($cid_ch, "_ups_client_cancellation_date", true));
                        if ($cancel_date && $cancel_date > $threshold_90) {
                            $cancelled_90d++;
                        }
                    }
                }
                $base_for_churn = max(1, $active_count + $cancelled_90d);
                $churn_rate_pct = round(($cancelled_90d / $base_for_churn) * 100, 1);
                ?>
                <section class="card kpi">
                  <span class="muted"><?php esc_html_e("Churn (90 dni)", "upsellio"); ?></span>
                  <b><?php echo esc_html((string) $churn_rate_pct); ?>%</b>
                  <span class="muted" style="font-size:12px"><?php echo esc_html((string) $cancelled_90d); ?> <?php esc_html_e("odejść", "upsellio"); ?> / <?php echo esc_html((string) $base_for_churn); ?> <?php esc_html_e("baza", "upsellio"); ?></span>
                </section>

                <section class="card half" style="grid-column:span 7">
                  <h2><?php esc_html_e("Priorytety na dziś", "upsellio"); ?></h2>
                  <?php if ($prio_list === []) : ?>
                    <p class="muted"><?php esc_html_e("Brak krytycznych priorytetów — świetna robota.", "upsellio"); ?></p>
                  <?php else : ?>
                    <?php foreach (array_slice($prio_list, 0, 18) as $pr) : ?>
                      <div class="crm-prio-row">
                        <div>
                          <strong><?php echo esc_html((string) ($pr["type"] ?? "")); ?></strong>
                          <div><?php echo esc_html((string) ($pr["title"] ?? "")); ?></div>
                          <div class="crm-prio-meta"><?php echo esc_html((string) ($pr["subtitle"] ?? "")); ?> · <?php echo esc_html((string) ($pr["age"] ?? "")); ?></div>
                        </div>
                        <div style="white-space:nowrap">
                          <a class="btn alt" style="font-size:12px;padding:6px 12px" href="<?php echo esc_url((string) ($pr["href"] ?? home_url("/crm-app/"))); ?>"><?php echo esc_html((string) ($pr["cta"] ?? __("Idź", "upsellio"))); ?></a>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </section>
                <section class="card half" style="grid-column:span 5">
                  <h2><?php esc_html_e("Pipeline snapshot", "upsellio"); ?></h2>
                  <?php if ($snap_total_val <= 0) : ?>
                    <p class="muted"><?php esc_html_e("Brak otwartych wartości w etapach (uzupełnij kwoty ofert).", "upsellio"); ?></p>
                  <?php else : ?>
                    <?php
                    $si = 0;
                    foreach (["awareness", "consideration", "decision", "offer_sent"] as $skey) :
                        if (!isset($snap[$skey]) || !is_array($snap[$skey])) {
                            continue;
                        }
                        $sv = $snap[$skey];
                        ?>
                      <div class="crm-snap-stage">
                        <div style="display:flex;justify-content:space-between;gap:8px;font-size:13px">
                          <span><?php echo esc_html((string) ($sv["label"] ?? $skey)); ?></span>
                          <span><strong><?php echo esc_html((string) (int) ($sv["count"] ?? 0)); ?></strong> · <?php echo esc_html(number_format((float) ($sv["value_pln"] ?? 0), 0, ",", " ")); ?> PLN</span>
                        </div>
                        <?php $pct = $snap_total_val > 0 ? min(100, round(((float) ($sv["value_pln"] ?? 0) / $snap_total_val) * 100)) : 0; ?>
                        <div class="crm-snap-bar"><span style="flex:0 0 <?php echo esc_attr((string) max(2, $pct)); ?>%;background:<?php echo esc_attr($snap_colors[$si % 4]); ?>"></span></div>
                        <?php if (!empty($sv["alert"])) : ?><p class="muted" style="font-size:12px;margin:4px 0 0"><?php echo esc_html((string) $sv["alert"]); ?></p><?php endif; ?>
                      </div>
                      <?php
                      $si++;
                    endforeach;
                    ?>
                    <p style="margin-top:10px"><a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "pipeline"], home_url("/crm-app/"))); ?>"><?php esc_html_e("Pełny pipeline", "upsellio"); ?></a></p>
                  <?php endif; ?>
                </section>

                <section class="card half" style="grid-column:span 6">
                  <h2><?php esc_html_e("Gorące i nowe leady", "upsellio"); ?></h2>
                  <?php if ($hot_leads_d === []) : ?>
                    <p class="muted"><?php esc_html_e("Brak leadów do wyświetlenia przy obecnym filtrze.", "upsellio"); ?></p>
                  <?php else : ?>
                    <table>
                      <thead><tr><th>Lead</th><th>Score</th><th><?php esc_html_e("Czas", "upsellio"); ?></th><th></th></tr></thead>
                      <tbody>
                        <?php foreach ($hot_leads_d as $hl) : ?>
                          <?php
                          if (!is_array($hl) || !isset($hl["post"]) || !($hl["post"] instanceof WP_Post)) {
                              continue;
                          }
                          $hlp = $hl["post"];
                          $hlid = (int) $hlp->ID;
                          $hls = (int) ($hl["score"] ?? 0);
                          $hl_reason = (string) get_post_meta($hlid, "_upsellio_lead_score_reason", true);
                          if ($hl_reason !== "" && function_exists("mb_strlen") && mb_strlen($hl_reason, "UTF-8") > 72) {
                              $hl_reason_snip = function_exists("mb_substr") ? mb_substr($hl_reason, 0, 72, "UTF-8") . "…" : substr($hl_reason, 0, 72) . "…";
                          } else {
                              $hl_reason_snip = $hl_reason;
                          }
                          $sc = $hls >= 70 ? "crm-score-hot" : ($hls >= 40 ? "crm-score-warm" : "crm-score-cold");
                          $hts = strtotime((string) $hlp->post_date_gmt);
                          ?>
                          <tr>
                            <td><?php echo esc_html((string) $hlp->post_title); ?></td>
                            <td title="<?php echo esc_attr($hl_reason); ?>"><span class="<?php echo esc_attr($sc); ?>"><?php echo esc_html((string) $hls); ?>/100</span><?php if ($hl_reason !== "") : ?><br/><small class="muted" style="font-size:11px;line-height:1.3"><?php echo esc_html($hl_reason_snip); ?></small><?php endif; ?></td>
                            <td><?php echo $hts !== false ? esc_html(human_time_diff($hts, time())) : "—"; ?></td>
                            <td><a class="btn alt" style="font-size:12px;padding:4px 10px" href="<?php echo esc_url(add_query_arg(["view" => "leads"], home_url("/crm-app/"))); ?>"><?php esc_html_e("Otwórz", "upsellio"); ?></a></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  <?php endif; ?>
                </section>
                <section class="card half" style="grid-column:span 6">
                  <h2><?php esc_html_e("Aktywność i inbox", "upsellio"); ?></h2>
                  <p style="margin-bottom:10px"><a class="btn" href="<?php echo esc_url(add_query_arg(["view" => "inbox"], home_url("/crm-app/"))); ?>"><?php esc_html_e("Przejdź do Inbox", "upsellio"); ?></a><?php if ($crm_inbox_unread_total > 0) : ?> <span class="badge"><?php echo esc_html((string) (int) $crm_inbox_unread_total); ?> <?php esc_html_e("nieprzeczytanych", "upsellio"); ?></span><?php endif; ?></p>
                  <?php if (empty($recent_activity)) : ?>
                    <p class="muted"><?php esc_html_e("Brak ostatniej aktywności.", "upsellio"); ?></p>
                  <?php else : ?>
                    <?php foreach ($recent_activity as $activity_row) : ?>
                      <?php if (!is_array($activity_row)) { continue; } $entry = isset($activity_row["entry"]) && is_array($activity_row["entry"]) ? $activity_row["entry"] : []; ?>
                      <div class="timeline-item">
                        <span class="muted"><?php echo esc_html((string) ($entry["ts"] ?? "")); ?></span>
                        <span><?php echo esc_html((string) ($activity_row["entity_type"] ?? "")); ?> — <?php echo esc_html((string) ($entry["message"] ?? "")); ?></span>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </section>

                <section class="card" style="grid-column:1/-1">
                  <div style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:12px;align-items:center;margin-bottom:8px">
                    <h2 style="margin:0"><?php esc_html_e("Trend leadów i ofert", "upsellio"); ?></h2>
                    <div class="crm-dash-filters">
                      <?php foreach ([7 => "7d", 30 => "30d", 90 => "90d"] as $td => $_x) : ?>
                        <a href="<?php echo $dash_url(["dash_trend" => $td]); ?>" class="<?php echo (int) $dash_trend_days === $td ? "is-on" : ""; ?>"><?php echo esc_html((string) $td); ?> <?php esc_html_e("dni", "upsellio"); ?></a>
                      <?php endforeach; ?>
                    </div>
                  </div>
                  <p class="muted" style="font-size:13px;margin-bottom:10px"><?php echo esc_html($diag_txt); ?></p>
                  <canvas id="crm-dash-trend" class="crm-mini-chart" width="900" height="220"></canvas>
                  <script>
                  (function(){
                    var c=document.getElementById("crm-dash-trend"); if(!c||!c.getContext) return;
                    var data=<?php echo wp_json_encode([
                        "labels" => $trend["labels"] ?? [],
                        "leads" => $trend["leads"] ?? [],
                        "qualified" => $trend["qualified"] ?? [],
                        "sent" => $trend["sent"] ?? [],
                        "won" => $trend["won"] ?? [],
                    ], JSON_UNESCAPED_UNICODE); ?>;
                    var ctx=c.getContext("2d"); var w=c.parentElement.clientWidth||900; c.width=w; c.height=220;
                    var pad=36, h=c.height, n=data.labels.length||1;
                    var maxV=1; ["leads","qualified","sent","won"].forEach(function(k){ (data[k]||[]).forEach(function(v){ maxV=Math.max(maxV,v); }); });
                    function x(i){ return pad+(i/(Math.max(1,n-1)))*(w-pad*2); }
                    function y(v){ return h-pad-(v/maxV)*(h-pad*2); }
                    ctx.strokeStyle="#e7e7e1"; ctx.lineWidth=1;
                    for(var g=0;g<=4;g++){ var gy=h-pad-(g/4)*(h-pad*2); ctx.beginPath(); ctx.moveTo(pad,gy); ctx.lineTo(w-pad,gy); ctx.stroke(); }
                    var colors={leads:"#0d9488",qualified:"#6366f1",sent:"#f59e0b",won:"#22c55e"};
                    ["leads","qualified","sent","won"].forEach(function(key){
                      ctx.strokeStyle=colors[key]; ctx.lineWidth=2; ctx.beginPath();
                      (data[key]||[]).forEach(function(v,i){ var X=x(i), Y=y(v); if(i===0) ctx.moveTo(X,Y); else ctx.lineTo(X,Y); });
                      ctx.stroke();
                    });
                  })();
                  </script>
                </section>

                <section class="card half" style="grid-column:span 6">
                  <h2><?php esc_html_e("Źródła leadów", "upsellio"); ?></h2>
                  <table>
                    <thead><tr><th><?php esc_html_e("Źródło", "upsellio"); ?></th><th><?php esc_html_e("Leady", "upsellio"); ?></th><th><?php esc_html_e("Kwalif.", "upsellio"); ?></th><th><?php esc_html_e("Wygrane", "upsellio"); ?></th></tr></thead>
                    <tbody>
                      <?php foreach ($src_rows as $sr) : ?>
                        <?php if (!is_array($sr)) { continue; } ?>
                        <tr>
                          <td><?php echo esc_html((string) ($sr["label"] ?? "")); ?></td>
                          <td><?php echo esc_html((string) (int) ($sr["leads"] ?? 0)); ?></td>
                          <td><?php echo esc_html((string) (int) ($sr["qualified"] ?? 0)); ?></td>
                          <td><?php echo esc_html((string) (int) ($sr["offers_won"] ?? 0)); ?></td>
                        </tr>
                      <?php endforeach; ?>
                      <?php if ($src_rows === []) : ?><tr><td colspan="4" class="muted"><?php esc_html_e("Brak danych.", "upsellio"); ?></td></tr><?php endif; ?>
                    </tbody>
                  </table>
                </section>
                <section class="card half" style="grid-column:span 6">
                  <h2><?php esc_html_e("Powody przegranych i alerty", "upsellio"); ?></h2>
                  <?php if ($lost_r !== []) : ?>
                    <table style="margin-bottom:12px">
                      <thead><tr><th><?php esc_html_e("Powód", "upsellio"); ?></th><th>#</th></tr></thead>
                      <tbody>
                        <?php foreach (array_slice($lost_r, 0, 12, true) as $lr => $lc) : ?>
                          <tr><td><?php echo esc_html((string) $lr); ?></td><td><?php echo esc_html((string) (int) $lc); ?></td></tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  <?php endif; ?>
                  <?php foreach ($dash_alerts as $al) : ?>
                    <div class="timeline-item" style="border-bottom:none;padding:4px 0"><span class="muted">!</span><span><?php echo esc_html((string) $al); ?></span></div>
                  <?php endforeach; ?>
                  <?php if ($lost_r === [] && $dash_alerts === []) : ?>
                    <p class="muted"><?php esc_html_e("Brak przegranych w próbce lub brak alertów.", "upsellio"); ?></p>
                  <?php endif; ?>
                </section>
              </div>
            <?php endif; ?>
            <?php if ($view === "leads") : ?>
              <section class="card">
                <h2><?php esc_html_e("Leady i kwalifikacja", "upsellio"); ?></h2>
                <div class="crm-view-tabs">
                  <?php
                  $lead_tab_links = [
                      "all" => __("Wszystkie", "upsellio"),
                      "new" => __("Nowe", "upsellio"),
                      "contact" => __("Do kontaktu", "upsellio"),
                      "qualified" => __("Zakwalifikowane", "upsellio"),
                      "rejected" => __("Odrzucone", "upsellio"),
                      "converted" => __("Skonwertowane", "upsellio"),
                  ];
                  foreach ($lead_tab_links as $lt_key => $lt_label) :
                      ?>
                    <a class="crm-tab-link <?php echo $lead_tab === $lt_key ? "is-active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "leads", "lead_tab" => $lt_key], home_url("/crm-app/"))); ?>"><?php echo esc_html($lt_label); ?></a>
                  <?php endforeach; ?>
                </div>
                <form method="post" class="grid2" style="margin:0 0 12px">
                  <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                  <input type="hidden" name="ups_action" value="save_lead" />
                  <input type="hidden" name="crm_view" value="leads" />
                  <input type="text" name="lead_title" placeholder="Nazwa leada / firma" required />
                  <input type="email" name="lead_email" placeholder="Email" />
                  <input type="text" name="lead_phone" placeholder="Telefon" />
                  <input type="text" name="lead_source" placeholder="Źródło (UTM/ref)" />
                  <select name="lead_type"><option value="inbound">inbound</option><option value="outbound">outbound</option><option value="referral">referral</option><option value="returning">powracający</option></select>
                  <select name="lead_qualification_status"><option value="new">nowy</option><option value="qualified">zakwalifikowany</option><option value="nurturing">nurturing</option><option value="rejected">odrzucony</option><option value="converted">skonwertowany</option></select>
                  <textarea name="lead_need" placeholder="Potrzeba / problem"></textarea>
                  <input type="number" step="0.01" min="0" name="lead_budget" placeholder="Budżet" />
                  <input type="date" name="lead_decision_date" />
                  <select name="lead_potential"><option value="low">niski potencjał</option><option value="medium">średni</option><option value="high">wysoki</option></select>
                  <textarea name="lead_notes" placeholder="Notatki"></textarea>
                  <button class="btn" type="submit">Zapisz lead</button>
                </form>
                <table>
                  <thead><tr><th><?php esc_html_e("Lead", "upsellio"); ?></th><th><?php esc_html_e("Firma / potrzeba", "upsellio"); ?></th><th><?php esc_html_e("Źródło", "upsellio"); ?></th><th><?php esc_html_e("Wiek", "upsellio"); ?></th><th><?php esc_html_e("Typ", "upsellio"); ?></th><th><?php esc_html_e("Status", "upsellio"); ?></th><th>Score</th><th><?php esc_html_e("Prawd.", "upsellio"); ?></th><th><?php esc_html_e("Temp.", "upsellio"); ?></th><th><?php esc_html_e("Budżet", "upsellio"); ?></th><th><?php esc_html_e("Decyzja", "upsellio"); ?></th><th><?php esc_html_e("Akcja", "upsellio"); ?></th></tr></thead>
                  <tbody>
                    <?php foreach ($leads as $lead) : $lid = (int) $lead->ID; ?>
                      <?php
                      $lst = (string) get_post_meta($lid, "_ups_lead_qualification_status", true);
                      if ($lead_tab === "new") {
                          if (!in_array($lst, ["new", ""], true)) {
                              continue;
                          }
                      } elseif ($lead_tab === "contact") {
                          if (!in_array($lst, ["new", "", "nurturing"], true)) {
                              continue;
                          }
                      } elseif ($lead_tab === "qualified") {
                          if ($lst !== "qualified") {
                              continue;
                          }
                      } elseif ($lead_tab === "rejected") {
                          if ($lst !== "rejected") {
                              continue;
                          }
                      } elseif ($lead_tab === "converted") {
                          if ($lst !== "converted") {
                              continue;
                          }
                      }
                      ?>
                      <tr>
                        <td><?php echo esc_html((string) $lead->post_title); ?><br/><small><?php echo esc_html((string) get_post_meta($lid, "_ups_lead_email", true)); ?></small></td>
                        <td><small><?php echo esc_html(wp_trim_words((string) get_post_meta($lid, "_ups_lead_need", true), 14)); ?></small></td>
                        <td><small><?php echo esc_html((string) get_post_meta($lid, "_ups_lead_source", true)); ?></small></td>
                        <?php
                        $l_created_raw = (string) $lead->post_date_gmt;
                        if ($l_created_raw === "" || $l_created_raw === "0000-00-00 00:00:00") {
                            $l_created_raw = (string) $lead->post_date;
                        }
                        $l_created = $l_created_raw !== "" ? strtotime($l_created_raw) : 0;
                        $l_age_h = $l_created > 0 ? round((time() - $l_created) / 3600, 1) : 0;
                        $l_age_color = $l_age_h > 48 ? "#ef4444" : ($l_age_h > 24 ? "#f59e0b" : "#16a34a");
                        $l_first_contact = (string) get_post_meta($lid, "_upsellio_first_contact_at", true);
                        $l_responded = $l_first_contact !== "";
                        ?>
                        <td style="font-size:12px">
                          <span style="color:<?php echo $l_responded ? "#16a34a" : esc_attr($l_age_color); ?>;font-weight:700">
                            <?php echo $l_responded ? esc_html__("✓ odpowiedź", "upsellio") : esc_html((string) $l_age_h . "h"); ?>
                          </span>
                          <?php if (!$l_responded && $l_age_h > 24) : ?>
                            <br/><small style="color:#ef4444"><?php esc_html_e("Przekroczono 24h!", "upsellio"); ?></small>
                          <?php endif; ?>
                        </td>
                        <td><?php echo esc_html((string) get_post_meta($lid, "_ups_lead_type", true)); ?></td>
                        <td><?php echo esc_html((string) get_post_meta($lid, "_ups_lead_qualification_status", true)); ?></td>
                        <?php
                        $l_score = (int) get_post_meta($lid, "_ups_lead_score_0_100", true);
                        if ($l_score <= 0) {
                            $l_score = (int) get_post_meta($lid, "_upsellio_lead_score", true);
                        }
                        $l_reason = (string) get_post_meta($lid, "_upsellio_lead_score_reason", true);
                        $l_reason_title = $l_reason;
                        if ($l_reason !== "" && function_exists("mb_strlen") && mb_strlen($l_reason, "UTF-8") > 60) {
                            $l_reason_snip = function_exists("mb_substr") ? mb_substr($l_reason, 0, 60, "UTF-8") : substr($l_reason, 0, 60);
                            $l_reason_snip .= "…";
                        } else {
                            $l_reason_snip = $l_reason;
                        }
                        ?>
                        <td title="<?php echo esc_attr($l_reason_title); ?>" style="cursor:<?php echo $l_reason !== "" ? "help" : "default"; ?>">
                          <strong><?php echo esc_html((string) $l_score); ?></strong>
                          <?php if ($l_reason !== "") : ?>
                            <br/><small style="color:var(--text-3);font-size:10px;line-height:1.3"><?php echo esc_html($l_reason_snip); ?></small>
                          <?php endif; ?>
                        </td>
                        <td><?php echo esc_html((string) (int) get_post_meta($lid, "_ups_lead_deal_probability_0_100", true)); ?>%</td>
                        <td><?php echo esc_html((string) get_post_meta($lid, "_ups_lead_temperature", true)); ?></td>
                        <td><?php echo esc_html(number_format((float) get_post_meta($lid, "_ups_lead_budget", true), 2, ",", " ")); ?></td>
                        <td><?php echo esc_html((string) get_post_meta($lid, "_ups_lead_decision_date", true)); ?></td>
                        <td>
                          <form method="post" style="display:flex;gap:6px">
                            <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                            <input type="hidden" name="ups_action" value="convert_lead" />
                            <input type="hidden" name="crm_view" value="leads" />
                            <input type="hidden" name="lead_id" value="<?php echo esc_attr((string) $lid); ?>" />
                            <select name="lead_decision"><option value="create_client_deal">utwórz klienta + deal</option><option value="reject">odrzuć</option></select>
                            <button class="btn alt" type="submit">Wykonaj</button>
                          </form>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                    <?php if (empty($leads)) : ?><tr><td colspan="12"><?php esc_html_e("Brak leadów.", "upsellio"); ?></td></tr><?php endif; ?>
                  </tbody>
                </table>
              </section>
            <?php endif; ?>
            <?php if ($view === "account-360") : ?>
              <section class="card">
                <h2>Karta klienta 360°</h2>
                <?php $selected_360_client_id = isset($_GET["account_client_id"]) ? (int) wp_unslash($_GET["account_client_id"]) : 0; ?>
                <form method="get" style="margin:0 0 12px">
                  <input type="hidden" name="view" value="account-360" />
                  <select name="account_client_id">
                    <option value="0">-- wybierz klienta --</option>
                    <?php foreach ($clients as $client) : ?><option value="<?php echo esc_attr((string) $client->ID); ?>" <?php selected((int) $client->ID, $selected_360_client_id); ?>><?php echo esc_html((string) $client->post_title); ?></option><?php endforeach; ?>
                  </select>
                  <button class="btn alt" type="submit">Pokaż kartę</button>
                </form>
                <?php if ($selected_360_client_id > 0) : ?>
                  <?php
                  $client_post_360 = get_post($selected_360_client_id);
                  $client_deals_360 = get_posts(["post_type" => "crm_offer", "post_status" => ["publish", "draft", "pending", "private"], "posts_per_page" => 100, "meta_query" => [["key" => "_ups_offer_client_id", "value" => $selected_360_client_id]]]);
                  $client_contracts_360 = get_posts(["post_type" => "crm_contract", "post_status" => ["publish", "draft", "pending", "private"], "posts_per_page" => 100, "meta_query" => [["key" => "_ups_contract_client_id", "value" => $selected_360_client_id]]]);
                  $client_contacts_360 = get_posts(["post_type" => "crm_contact", "post_status" => ["publish", "draft", "pending", "private"], "posts_per_page" => 100, "meta_query" => [["key" => "_ups_contact_client_id", "value" => $selected_360_client_id]]]);
                  ?>
                  <div class="timeline-item"><span class="muted">Firma</span><span><?php echo esc_html((string) get_post_meta($selected_360_client_id, "_ups_client_company", true)); ?></span></div>
                  <div class="timeline-item"><span class="muted">Email</span><span><?php echo esc_html((string) get_post_meta($selected_360_client_id, "_ups_client_email", true)); ?></span></div>
                  <div class="timeline-item"><span class="muted">Status relacji</span><span><?php echo esc_html((string) get_post_meta($selected_360_client_id, "_ups_client_lifecycle_status", true)); ?></span></div>
                  <div class="timeline-item"><span class="muted">Notatki</span><span><?php echo esc_html((string) get_post_meta($selected_360_client_id, "_ups_client_notes", true)); ?></span></div>
                  <?php
                  $a360_client_id = $selected_360_client_id;
                  $a360_offers = get_posts([
                      "post_type"   => "crm_offer",
                      "post_status" => ["publish", "private"],
                      "posts_per_page" => 20,
                      "meta_query"  => [["key" => "_ups_offer_client_id", "value" => $a360_client_id]],
                      "orderby"     => "modified",
                      "order"       => "DESC",
                  ]);
                  ?>
                  <div style="margin-top:20px">
                    <div style="font-size:12px;font-weight:700;color:var(--text-2);text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px">
                      <?php esc_html_e("Aktywność na ofertach", "upsellio"); ?>
                    </div>
                    <?php if (empty($a360_offers)) : ?>
                      <p class="muted" style="font-size:12px"><?php esc_html_e("Brak ofert dla tego klienta.", "upsellio"); ?></p>
                    <?php else : ?>
                    <div style="display:flex;flex-direction:column;gap:8px">
                    <?php foreach ($a360_offers as $a360_offer) :
                        $a360_oid   = (int) $a360_offer->ID;
                        $a360_score = (int) get_post_meta($a360_oid, "_ups_offer_score", true);
                        $a360_stage = (string) get_post_meta($a360_oid, "_ups_offer_stage", true);
                        $a360_hot   = get_post_meta($a360_oid, "_ups_offer_hot_offer", true) === "1";
                        $a360_last  = (string) get_post_meta($a360_oid, "_ups_offer_last_seen", true);
                        $a360_events = get_post_meta($a360_oid, "_ups_offer_events", true);
                        $a360_ev_count = is_array($a360_events) ? count($a360_events) : 0;
                        $a360_cta_clicks = 0;
                        if (is_array($a360_events)) {
                            foreach ($a360_events as $a360_ev) {
                                if (($a360_ev["event"] ?? "") === "offer_cta_click") {
                                    $a360_cta_clicks++;
                                }
                            }
                        }
                        ?>
                      <div style="background:#f8f9f4;border:1px solid var(--border);border-radius:10px;padding:10px 14px;display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                        <div style="flex:1;min-width:0">
                          <div style="font-size:13px;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            <?php echo esc_html((string) $a360_offer->post_title); ?>
                            <?php if ($a360_hot) {
                                echo " 🔥";
                            } ?>
                          </div>
                          <div style="font-size:11px;color:var(--text-3);margin-top:2px">
                            <?php echo $a360_last ? esc_html(wp_date("d.m.Y H:i", strtotime($a360_last))) : esc_html__("Brak aktywności", "upsellio"); ?>
                            · <?php echo (int) $a360_ev_count; ?> <?php esc_html_e("zdarzeń", "upsellio"); ?>
                            <?php if ($a360_cta_clicks > 0) : ?> · <?php echo (int) $a360_cta_clicks; ?>× CTA<?php endif; ?>
                          </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;flex-shrink:0">
                          <div style="font-size:13px;font-weight:800;color:<?php echo $a360_score >= 70 ? "#0f766e" : ($a360_score >= 40 ? "#d97706" : "var(--text-3)"); ?>">
                            <?php echo (int) $a360_score; ?>/100
                          </div>
                          <div style="font-size:10px;padding:2px 8px;border-radius:99px;font-weight:700;background:<?php echo $a360_stage === "decision" ? "#dcfce7" : ($a360_stage === "consideration" ? "#fef3c7" : "#f4f5f0"); ?>;color:<?php echo $a360_stage === "decision" ? "#16a34a" : ($a360_stage === "consideration" ? "#92400e" : "var(--text-3)"); ?>">
                            <?php echo esc_html($a360_stage !== "" ? $a360_stage : "awareness"); ?>
                          </div>
                          <a class="btn alt" style="font-size:11px;padding:4px 10px"
                             href="<?php echo esc_url(add_query_arg(["view" => "offer_analytics", "offer_id" => $a360_oid, "list_view" => "offers"], home_url("/crm-app/"))); ?>">
                            <?php esc_html_e("Analityka →", "upsellio"); ?>
                          </a>
                        </div>
                      </div>
                    <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                  </div>
                  <h3 style="margin-top:10px">Kontakty</h3>
                  <ul><?php foreach ($client_contacts_360 as $c) : ?><li><?php echo esc_html((string) $c->post_title . " (" . (string) get_post_meta((int) $c->ID, "_ups_contact_role", true) . ")"); ?></li><?php endforeach; ?></ul>
                  <h3>Deale</h3>
                  <ul><?php foreach ($client_deals_360 as $d) : ?><li><?php echo esc_html((string) $d->post_title); ?> - <?php echo esc_html((string) get_post_meta((int) $d->ID, "_ups_offer_status", true)); ?></li><?php endforeach; ?></ul>
                  <h3>Umowy</h3>
                  <ul><?php foreach ($client_contracts_360 as $c) : ?><li><?php echo esc_html((string) $c->post_title); ?> - <?php echo esc_html((string) get_post_meta((int) $c->ID, "_ups_contract_status", true)); ?></li><?php endforeach; ?></ul>
                <?php else : ?>
                  <p class="muted">Wybierz klienta, aby wyświetlić pełną kartę 360°.</p>
                <?php endif; ?>
              </section>
            <?php endif; ?>
            <?php if ($view === "contacts") : ?>
              <section class="card">
                <h2>Kontakty B2B przy firmach</h2>
                <form method="post" class="grid2" style="margin:0 0 12px">
                  <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                  <input type="hidden" name="ups_action" value="save_contact" />
                  <input type="hidden" name="crm_view" value="contacts" />
                  <input type="text" name="contact_name" placeholder="Imię i nazwisko" required />
                  <select name="contact_client_id"><?php foreach ($clients as $client) : ?><option value="<?php echo esc_attr((string) $client->ID); ?>"><?php echo esc_html((string) $client->post_title); ?></option><?php endforeach; ?></select>
                  <input type="text" name="contact_role" placeholder="Rola (np. decydent)" />
                  <input type="email" name="contact_email" placeholder="Email" />
                  <input type="text" name="contact_phone" placeholder="Telefon" />
                  <textarea name="contact_notes" placeholder="Notatki kontaktu"></textarea>
                  <button class="btn" type="submit">Zapisz kontakt</button>
                </form>
                <table><thead><tr><th>Kontakt</th><th>Firma</th><th>Rola</th><th>Email</th><th>Telefon</th></tr></thead><tbody>
                <?php foreach ($contacts as $contact) : $cid = (int) $contact->ID; ?>
                  <tr><td><?php echo esc_html((string) $contact->post_title); ?></td><td><?php echo esc_html((string) get_the_title((int) get_post_meta($cid, "_ups_contact_client_id", true))); ?></td><td><?php echo esc_html((string) get_post_meta($cid, "_ups_contact_role", true)); ?></td><td><?php echo esc_html((string) get_post_meta($cid, "_ups_contact_email", true)); ?></td><td><?php echo esc_html((string) get_post_meta($cid, "_ups_contact_phone", true)); ?></td></tr>
                <?php endforeach; ?>
                </tbody></table>
              </section>
            <?php endif; ?>
            <?php if ($view === "services") : ?>
              <section class="card">
                <h2>Katalog usług i pakietów</h2>
                <form method="post" class="grid2" style="margin:0 0 12px">
                  <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                  <input type="hidden" name="ups_action" value="save_service" />
                  <input type="hidden" name="crm_view" value="services" />
                  <input type="text" name="service_title" placeholder="Nazwa usługi/pakietu" required />
                  <select name="service_pricing_type"><option value="one_time">jednorazowa</option><option value="subscription">abonament</option><option value="success_fee">success fee</option></select>
                  <input type="number" step="0.01" min="0" name="service_price" placeholder="Cena bazowa" />
                  <input type="number" step="0.01" min="0" name="service_setup_fee" placeholder="Setup fee" />
                  <input type="number" step="0.01" min="0" name="service_success_fee" placeholder="Success fee" />
                  <textarea name="service_description" placeholder="Opis usługi"></textarea>
                  <button class="btn" type="submit">Zapisz usługę</button>
                </form>
                <table><thead><tr><th>Usługa</th><th>Typ</th><th>Cena</th><th>Setup</th><th>Success fee</th></tr></thead><tbody>
                <?php foreach ($services as $service) : $sid = (int) $service->ID; ?>
                  <tr><td><?php echo esc_html((string) $service->post_title); ?></td><td><?php echo esc_html((string) get_post_meta($sid, "_ups_service_pricing_type", true)); ?></td><td><?php echo esc_html(number_format((float) get_post_meta($sid, "_ups_service_price", true), 2, ",", " ")); ?></td><td><?php echo esc_html(number_format((float) get_post_meta($sid, "_ups_service_setup_fee", true), 2, ",", " ")); ?></td><td><?php echo esc_html(number_format((float) get_post_meta($sid, "_ups_service_success_fee", true), 2, ",", " ")); ?></td></tr>
                <?php endforeach; ?>
                </tbody></table>
              </section>
            <?php endif; ?>
            <?php if ($view === "clients") : ?>
              <section class="card">
                <h2>Lista klientow</h2>
                <table>
                  <thead><tr><th>Klient</th><th>Email</th><th>Firma</th><th>Status</th><th>MRR</th><th><?php esc_html_e("Ostatnia rozmowa", "upsellio"); ?></th><th><?php esc_html_e("Nast. kontakt", "upsellio"); ?></th><th>Akcje</th></tr></thead>
                  <tbody>
                  <?php foreach ($clients as $client) : $cid = (int) $client->ID; ?>
                    <tr>
                      <td><?php echo esc_html((string) $client->post_title); ?></td>
                      <td><?php echo esc_html((string) get_post_meta($cid, "_ups_client_email", true)); ?></td>
                      <td><?php echo esc_html((string) get_post_meta($cid, "_ups_client_company", true)); ?></td>
                      <td><span class="badge"><?php echo esc_html($pl_label((string) get_post_meta($cid, "_ups_client_subscription_status", true), "subscription")); ?></span></td>
                      <td><?php echo esc_html(number_format((float) get_post_meta($cid, "_ups_client_monthly_value", true), 2, ",", " ")); ?> PLN</td>
                      <?php
                      $last_sn = (string) get_post_meta($cid, "_ups_client_last_call_notes", true);
                      if ($last_sn !== "" && function_exists("mb_strlen") && mb_strlen($last_sn, "UTF-8") > 56) {
                          $last_sn_d = function_exists("mb_substr") ? mb_substr($last_sn, 0, 56, "UTF-8") . "…" : substr($last_sn, 0, 56) . "…";
                      } else {
                          $last_sn_d = $last_sn;
                      }
                      ?>
                      <td style="font-size:12px;max-width:220px" title="<?php echo esc_attr($last_sn); ?>"><?php echo $last_sn !== "" ? esc_html($last_sn_d) : "—"; ?></td>
                      <?php
                      $next = (string) get_post_meta($cid, "_ups_client_next_contact_date", true);
                      $today_ymd = wp_date("Y-m-d", current_time("timestamp"));
                      $next_overdue = $next !== "" && $next < $today_ymd;
                      ?>
                      <td style="font-size:12px;<?php echo $next_overdue ? "color:#ef4444;font-weight:700" : ""; ?>">
                        <?php echo $next !== "" ? esc_html($next) : "—"; ?>
                      </td>
                      <td><a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "client-edit", "client_id" => $cid], home_url("/crm-app/"))); ?>">Edytuj</a></td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </section>
            <?php endif; ?>
            <?php if ($view === "client-edit") : ?>
              <section class="card">
                <h2><?php echo $crm_new_client ? "Nowy klient" : "Edycja klienta"; ?></h2>
                <?php if (!$selected_client instanceof WP_Post && !$crm_new_client) : ?>
                  <p class="muted">Wybierz klienta z listy klientów lub użyj „+ Klient” na górze.</p>
                <?php else : ?>
                  <?php
                  if ($crm_new_client) {
                      $ce_id = 0;
                      $ce_email = "";
                      $ce_phone = "";
                      $ce_company = "";
                      $ce_industry = "";
                      $ce_company_size = "";
                      $ce_budget_range = "";
                      $ce_mrr = 0.0;
                      $ce_billing_start = "";
                      $ce_subscription = "active";
                      $ce_is_rec = false;
                      $ce_notes = "";
                      $ce_last_call_notes = "";
                      $ce_next_contact_date = "";
                      $ce_log = [];
                      $client_title_val = "";
                  } else {
                      $ce_id = (int) $selected_client->ID;
                      $ce_email = (string) get_post_meta($ce_id, "_ups_client_email", true);
                      $ce_phone = (string) get_post_meta($ce_id, "_ups_client_phone", true);
                      $ce_company = (string) get_post_meta($ce_id, "_ups_client_company", true);
                      $ce_industry = (string) get_post_meta($ce_id, "_ups_client_industry", true);
                      $ce_company_size = (string) get_post_meta($ce_id, "_ups_client_company_size", true);
                      $ce_budget_range = (string) get_post_meta($ce_id, "_ups_client_budget_range", true);
                      $ce_mrr = (float) get_post_meta($ce_id, "_ups_client_monthly_value", true);
                      $ce_billing_start = (string) get_post_meta($ce_id, "_ups_client_billing_start", true);
                      $ce_subscription = (string) get_post_meta($ce_id, "_ups_client_subscription_status", true);
                      $ce_is_rec = (string) get_post_meta($ce_id, "_ups_client_is_recurring", true) === "1";
                      $ce_notes = (string) get_post_meta($ce_id, "_ups_client_notes", true);
                      $ce_last_call_notes = (string) get_post_meta($ce_id, "_ups_client_last_call_notes", true);
                      $ce_next_contact_date = (string) get_post_meta($ce_id, "_ups_client_next_contact_date", true);
                      $ce_log = get_post_meta($ce_id, "_ups_client_activity_log", true);
                      if (!is_array($ce_log)) {
                          $ce_log = [];
                      }
                      $client_title_val = (string) $selected_client->post_title;
                  }
                  ?>
                  <form method="post" class="grid2">
                    <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                    <input type="hidden" name="ups_action" value="save_client" />
                    <input type="hidden" name="crm_view" value="client-edit" />
                    <input type="hidden" name="client_id" value="<?php echo esc_attr((string) $ce_id); ?>" />
                    <input type="text" name="client_title" value="<?php echo esc_attr($client_title_val); ?>" required placeholder="Nazwa / osoba kontaktowa" />
                    <input type="email" name="client_email" value="<?php echo esc_attr($ce_email); ?>" />
                    <input type="text" name="client_phone" value="<?php echo esc_attr($ce_phone); ?>" />
                    <input type="text" name="client_company" value="<?php echo esc_attr($ce_company); ?>" />
                    <input type="text" name="client_industry" value="<?php echo esc_attr($ce_industry); ?>" />
                    <input type="text" name="client_company_size" value="<?php echo esc_attr($ce_company_size); ?>" />
                    <input type="text" name="client_budget_range" value="<?php echo esc_attr($ce_budget_range); ?>" />
                    <label><input type="checkbox" name="client_is_recurring" value="1" <?php checked($ce_is_rec); ?> /> recurring</label>
                    <input type="number" step="0.01" min="0" name="client_monthly_value" value="<?php echo esc_attr((string) $ce_mrr); ?>" />
                    <input type="date" name="client_billing_start" value="<?php echo esc_attr($ce_billing_start); ?>" />
                    <select name="client_subscription_status"><option value="active" <?php selected($ce_subscription, "active"); ?>>aktywny</option><option value="paused" <?php selected($ce_subscription, "paused"); ?>>wstrzymany</option><option value="cancelled" <?php selected($ce_subscription, "cancelled"); ?>>anulowany</option></select>
                    <input type="date" name="client_cancellation_date" value="<?php echo esc_attr($ce_id > 0 ? (string) get_post_meta($ce_id, "_ups_client_cancellation_date", true) : ""); ?>" />
                    <textarea name="client_cancellation_reason"><?php echo esc_textarea($ce_id > 0 ? (string) get_post_meta($ce_id, "_ups_client_cancellation_reason", true) : ""); ?></textarea>
                    <textarea name="client_notes" placeholder="Notatki klienta (wewnętrzne)"><?php echo esc_textarea($ce_notes); ?></textarea>
                    <label style="grid-column:1/-1;font-weight:700;margin-top:8px"><?php esc_html_e("Ostatnia rozmowa / ustalenia", "upsellio"); ?> <small style="font-weight:400;color:var(--text-3)"><?php esc_html_e("— nadpisz po każdym kontakcie", "upsellio"); ?></small></label>
                    <textarea name="client_last_call_notes" rows="3" style="grid-column:1/-1" placeholder="<?php esc_attr_e("Np. 15.05 — klient pytał o rozszerzenie Google Ads. Czeka na wycenę do 20.05.", "upsellio"); ?>"><?php echo esc_textarea($ce_last_call_notes); ?></textarea>
                    <label style="grid-column:1/-1;font-weight:600;margin-top:4px"><?php esc_html_e("Następna data kontaktu", "upsellio"); ?></label>
                    <input type="date" name="client_next_contact_date" style="grid-column:1/-1;max-width:240px" value="<?php echo esc_attr($ce_next_contact_date); ?>" />
                    <button class="btn" type="submit"><?php echo $crm_new_client ? "Utwórz klienta" : "Zapisz klienta"; ?></button>
                  </form>
                  <?php if (!$crm_new_client && $ce_id > 0) : ?>
                  <h2 style="margin-top:12px">Historia klienta</h2>
                  <?php if (empty($ce_log)) : ?>
                    <p class="muted">Brak historii działań klienta.</p>
                  <?php else : ?>
                    <?php foreach (array_reverse(array_slice($ce_log, -30)) as $entry) : ?>
                      <?php if (!is_array($entry)) { continue; } ?>
                      <div class="timeline-item">
                        <span class="muted"><?php echo esc_html((string) ($entry["ts"] ?? "")); ?></span>
                        <span><?php echo esc_html((string) ($entry["message"] ?? ($entry["event"] ?? "event"))); ?></span>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                  <?php endif; ?>
                <?php endif; ?>
              </section>
            <?php endif; ?>
            <?php if ($view === "offer_analytics") : ?>
              <?php
              $oa_offer_id = isset($_GET["offer_id"]) ? (int) wp_unslash($_GET["offer_id"]) : 0;
              $oa_offer = $oa_offer_id > 0 ? get_post($oa_offer_id) : null;
              ?>
              <?php if (!($oa_offer instanceof WP_Post) || $oa_offer->post_type !== "crm_offer") : ?>
                <section class="card"><p class="muted"><?php esc_html_e("Brak oferty.", "upsellio"); ?></p></section>
              <?php else : ?>
                <?php
                $oa = function_exists("upsellio_offer_build_full_analytics") ? upsellio_offer_build_full_analytics($oa_offer_id) : [];
                $oa_section_labels = [
                    "zakres"    => __("Zakres działania", "upsellio"),
                    "szczegoly" => __("Szczegóły oferty", "upsellio"),
                    "etapy"     => __("Plan realizacji", "upsellio"),
                    "pytania"   => __("Pytania do Ciebie", "upsellio"),
                    "pricing"   => __("Cennik", "upsellio"),
                    "cennik"    => __("Cennik", "upsellio"),
                    "faq"       => __("FAQ", "upsellio"),
                ];
                $oa_sec_ids = !empty($oa["section_ids"]) && is_array($oa["section_ids"])
                    ? $oa["section_ids"]
                    : ["zakres", "szczegoly", "etapy", "pytania", "pricing", "faq"];
                $oa_sec_count = max(1, count($oa_sec_ids));
                $oa_client_id = (int) get_post_meta($oa_offer_id, "_ups_offer_client_id", true);
                $oa_client_name = $oa_client_id > 0 ? get_the_title($oa_client_id) : "—";
                $oa_list_view = isset($_GET["list_view"]) ? sanitize_key((string) wp_unslash($_GET["list_view"])) : "offers";
                if (!in_array($oa_list_view, ["offers", "deals"], true)) {
                    $oa_list_view = "offers";
                }
                $oa_an_editing_view = $oa_list_view;
                $oa_an_q = ["view" => "offer_analytics", "offer_id" => $oa_offer_id, "list_view" => $oa_list_view];
                ?>
                <div class="crm-view-tabs" role="navigation" aria-label="<?php esc_attr_e("Oferta — nawigacja", "upsellio"); ?>">
                  <a class="crm-tab-link" href="<?php echo esc_url(add_query_arg(["view" => $oa_an_editing_view], home_url("/crm-app/"))); ?>"><?php echo $oa_an_editing_view === "deals" ? esc_html__("Lista dealów", "upsellio") : esc_html__("Lista ofert", "upsellio"); ?></a>
                  <a class="crm-tab-link" href="<?php echo esc_url(add_query_arg(["view" => $oa_an_editing_view, "offer_editor_id" => $oa_offer_id], home_url("/crm-app/"))); ?>"><?php esc_html_e("Budowniczek", "upsellio"); ?></a>
                  <a class="crm-tab-link is-active" href="<?php echo esc_url(add_query_arg($oa_an_q, home_url("/crm-app/"))); ?>"><?php esc_html_e("Analityka oferty", "upsellio"); ?></a>
                  <a class="crm-tab-link" href="<?php echo esc_url(add_query_arg(["view" => "inbox", "inbox_offer" => $oa_offer_id], home_url("/crm-app/"))); ?>"><?php esc_html_e("Inbox wątku", "upsellio"); ?></a>
                </div>
                <section class="card" style="padding:0;overflow:hidden">
                  <div style="padding:18px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:16px;flex-wrap:wrap">
                    <div>
                      <div style="font-size:11px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.8px;margin-bottom:3px"><?php esc_html_e("Analityka oferty", "upsellio"); ?></div>
                      <h2 style="margin:0;font-size:18px"><?php echo esc_html((string) $oa_offer->post_title); ?></h2>
                      <div style="font-size:12px;color:var(--text-3);margin-top:2px">
                        <?php esc_html_e("Klient:", "upsellio"); ?> <strong><?php echo esc_html((string) $oa_client_name); ?></strong>
                        · <?php esc_html_e("Etap:", "upsellio"); ?> <strong><?php echo esc_html((string) ($oa["stage"] ?? "—")); ?></strong>
                        · <?php esc_html_e("Ostatnia aktywność:", "upsellio"); ?> <strong><?php echo !empty($oa["last_seen"]) ? esc_html(wp_date("d.m.Y H:i", strtotime((string) $oa["last_seen"]))) : "—"; ?></strong>
                      </div>
                    </div>
                    <div style="margin-left:auto;display:flex;gap:8px;flex-wrap:wrap">
                      <a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => $oa_an_editing_view, "offer_editor_id" => $oa_offer_id], home_url("/crm-app/"))); ?>"><?php esc_html_e("Edytuj ofertę", "upsellio"); ?></a>
                      <a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "inbox", "inbox_offer" => $oa_offer_id], home_url("/crm-app/"))); ?>"><?php esc_html_e("Inbox wątku", "upsellio"); ?></a>
                    </div>
                  </div>
                  <div style="display:grid;grid-template-columns:repeat(5,minmax(0,1fr));border-bottom:1px solid var(--border)">
                    <?php
                    $oa_pricing_sec_total = (int) ($oa["all_section_times"]["pricing"] ?? 0);
                    $oa_last_commit_disp = (string) ($oa["last_commit"] ?? "");
                    $oa_kpis = [
                        ["val" => (string) (int) ($oa["session_count"] ?? 0), "lbl" => __("Sesji", "upsellio"), "color" => ""],
                        ["val" => (string) (int) ($oa["score"] ?? 0) . "/100", "lbl" => __("Score intencji", "upsellio"), "color" => ($oa["score"] ?? 0) >= 70 ? "#0f766e" : (($oa["score"] ?? 0) >= 40 ? "#d97706" : "")],
                        ["val" => (string) (int) ($oa["total_cta"] ?? 0), "lbl" => __("Kliknięcia CTA", "upsellio"), "color" => ($oa["total_cta"] ?? 0) > 0 ? "#0f766e" : ""],
                        ["val" => $oa_pricing_sec_total > 0 ? (string) (int) ($oa_pricing_sec_total / 60) . " min" : "—", "lbl" => __("Czas na cenniku", "upsellio"), "color" => ""],
                        ["val" => $oa_last_commit_disp !== "" ? $oa_last_commit_disp : "—", "lbl" => __("Wybrany pakiet", "upsellio"), "color" => $oa_last_commit_disp !== "" ? "#7c3aed" : ""],
                    ];
                    foreach ($oa_kpis as $kpi) :
                        ?>
                    <div style="padding:14px 18px;border-right:1px solid var(--border)">
                      <div style="font-size:20px;font-weight:800;letter-spacing:-.5px;color:<?php echo $kpi["color"] !== "" ? esc_attr($kpi["color"]) : "var(--text)"; ?>">
                        <?php echo esc_html((string) $kpi["val"]); ?>
                      </div>
                      <div style="font-size:10px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.4px;margin-top:2px">
                        <?php echo esc_html((string) $kpi["lbl"]); ?>
                      </div>
                    </div>
                    <?php endforeach; ?>
                  </div>
                  <div style="display:grid;grid-template-columns:1fr 320px;gap:0">
                    <div style="padding:20px 24px;border-right:1px solid var(--border)">
                      <div style="margin-bottom:24px">
                        <div style="font-size:12px;font-weight:700;color:var(--text-2);margin-bottom:12px;text-transform:uppercase;letter-spacing:.5px"><?php esc_html_e("Mapa uwagi — czas per sekcja", "upsellio"); ?></div>
                        <div style="display:flex;flex-direction:column;gap:6px">
                        <?php
                        $oa_max_t = max(array_values($oa["all_section_times"] ?? [1]) ?: [1]);
                        foreach ($oa_sec_ids as $sid) :
                            $slabel = $oa_section_labels[$sid] ?? ucwords(str_replace(["_", "-"], " ", $sid));
                            $sec = (int) ($oa["all_section_times"][$sid] ?? 0);
                            $pct = $oa_max_t > 0 ? (int) round($sec / $oa_max_t * 100) : 0;
                            $views = (int) ($oa["all_sections_viewed"][$sid] ?? 0);
                            $bar_color = $pct >= 70 ? "#0d9488" : ($pct >= 40 ? "#d97706" : "#e2e5de");
                            $text_color = $pct >= 70 ? "#0f766e" : ($pct >= 40 ? "#92400e" : "var(--text-3)");
                            ?>
                          <div style="display:grid;grid-template-columns:140px 1fr 80px;gap:10px;align-items:center">
                            <div style="font-size:12px;font-weight:600;color:var(--text-2)"><?php echo esc_html($slabel); ?></div>
                            <div style="background:#f4f5f0;border-radius:999px;height:8px;overflow:hidden">
                              <div style="width:<?php echo (int) $pct; ?>%;height:100%;background:<?php echo esc_attr($bar_color); ?>;border-radius:999px;transition:width .4s ease"></div>
                            </div>
                            <div style="font-size:11px;color:<?php echo esc_attr($text_color); ?>;font-weight:700;text-align:right">
                              <?php echo $sec > 0 ? esc_html((string) round($sec / 60, 1)) . " min" : "—"; ?>
                              <?php if ($views > 0) : ?><span style="color:var(--text-3);font-weight:400"> · <?php echo (int) $views; ?>×</span><?php endif; ?>
                            </div>
                          </div>
                        <?php endforeach; ?>
                        </div>
                      </div>
                      <div style="margin-bottom:24px">
                        <div style="font-size:12px;font-weight:700;color:var(--text-2);margin-bottom:12px;text-transform:uppercase;letter-spacing:.5px"><?php esc_html_e("Lejek — jak daleko dochodzą sesje", "upsellio"); ?></div>
                        <div style="display:flex;flex-direction:column;gap:4px">
                        <?php
                        $oa_funnel_total = (int) ($oa["session_count"] ?: 1);
                        foreach (($oa["funnel"] ?? []) as $fid => $fdata) :
                            $fpct = (int) ($fdata["pct"] ?? 0);
                            $reached = (int) ($fdata["reached"] ?? 0);
                            ?>
                          <div style="display:grid;grid-template-columns:140px 1fr 60px;gap:10px;align-items:center">
                            <div style="font-size:12px;color:var(--text-2)"><?php echo esc_html((string) ($fdata["label"] ?? "")); ?></div>
                            <div style="background:#f4f5f0;border-radius:4px;height:20px;overflow:hidden;position:relative">
                              <div style="width:<?php echo (int) $fpct; ?>%;height:100%;background:<?php echo $fpct < 50 ? "#fee2e2" : ($fpct < 80 ? "#fef3c7" : "#dcfce7"); ?>;border-radius:4px"></div>
                              <div style="position:absolute;inset:0;display:flex;align-items:center;padding:0 8px;font-size:10px;font-weight:700;color:var(--text-2)">
                                <?php echo (int) $reached; ?>/<?php echo (int) $oa_funnel_total; ?> <?php esc_html_e("sesji", "upsellio"); ?>
                              </div>
                            </div>
                            <div style="font-size:12px;font-weight:800;color:var(--text);text-align:right"><?php echo (int) $fpct; ?>%</div>
                          </div>
                        <?php endforeach; ?>
                        </div>
                      </div>
                      <div>
                        <div style="font-size:12px;font-weight:700;color:var(--text-2);margin-bottom:12px;text-transform:uppercase;letter-spacing:.5px"><?php esc_html_e("Sesje — chronologicznie", "upsellio"); ?></div>
                        <?php if (empty($oa["sessions"])) : ?>
                          <p class="muted" style="font-size:12px"><?php esc_html_e("Brak zarejestrowanej aktywności na tej ofercie.", "upsellio"); ?></p>
                        <?php else : ?>
                        <div style="display:flex;flex-direction:column;gap:8px">
                        <?php foreach (array_reverse($oa["sessions"] ?? []) as $sess) :
                            $has_cta = !empty($sess["cta_clicks"]);
                            $has_commit = !empty($sess["commit"]);
                            ?>
                          <div style="background:#f8f9f4;border:1px solid var(--border);border-radius:10px;padding:10px 14px">
                            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px">
                              <div style="font-size:12px;font-weight:700;color:var(--text)"><?php echo esc_html((string) ($sess["ts_disp"] ?? "")); ?></div>
                              <div style="font-size:11px;color:var(--text-3)">
                                <?php echo (int) ($sess["duration_sec"] ?? 0) > 0 ? esc_html((string) round((int) $sess["duration_sec"] / 60, 1)) . " min" : "&lt;1 min"; ?>
                                · <?php echo (int) ($sess["depth"] ?? 0); ?>/<?php echo (int) $oa_sec_count; ?> <?php esc_html_e("sekcji", "upsellio"); ?>
                              </div>
                              <?php if (!empty($sess["utm"])) : ?>
                                <div style="font-size:10px;background:#e0e7ff;color:#3730a3;padding:2px 7px;border-radius:99px;font-weight:700">
                                  <?php echo esc_html((string) $sess["utm"]); ?><?php echo !empty($sess["campaign"]) ? "/" . esc_html((string) $sess["campaign"]) : ""; ?>
                                </div>
                              <?php endif; ?>
                              <?php if ($has_cta) : ?>
                                <div style="font-size:10px;background:#dcfce7;color:#16a34a;padding:2px 7px;border-radius:99px;font-weight:700">CTA ✓</div>
                              <?php endif; ?>
                              <?php if ($has_commit) : ?>
                                <div style="font-size:10px;background:#f5f3ff;color:#7c3aed;padding:2px 7px;border-radius:99px;font-weight:700"><?php esc_html_e("Pakiet:", "upsellio"); ?> <?php echo esc_html((string) $sess["commit"]); ?></div>
                              <?php endif; ?>
                            </div>
                            <div style="display:flex;gap:4px">
                              <?php foreach ($oa_sec_ids as $s_id) :
                                  $visited = in_array($s_id, $sess["sections_viewed"] ?? [], true);
                                  $s_time = (int) (($sess["section_times"] ?? [])[$s_id] ?? 0);
                                  $s_color = !$visited ? "#e2e5de" : ($s_time > 60 ? "#0d9488" : ($s_time > 20 ? "#5eead4" : "#99f6e4"));
                                  ?>
                                <div title="<?php echo esc_attr($s_id . ": " . $s_time . "s"); ?>"
                                     style="flex:1;min-width:0;height:6px;border-radius:3px;background:<?php echo esc_attr($s_color); ?>"></div>
                              <?php endforeach; ?>
                            </div>
                            <div style="display:flex;gap:4px;margin-top:3px">
                              <?php foreach ($oa_sec_ids as $s_id) :
                                  $mini_lbl = $oa_section_labels[$s_id] ?? $s_id;
                                  $short = function_exists("mb_substr")
                                      ? mb_substr((string) $mini_lbl, 0, 2, "UTF-8")
                                      : substr((string) $mini_lbl, 0, 2);
                                  ?>
                                <div style="flex:1;min-width:0;font-size:9px;color:var(--text-3);text-align:center"><?php echo esc_html($short); ?></div>
                              <?php endforeach; ?>
                            </div>
                          </div>
                        <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                      </div>
                    </div>
                    <div style="padding:18px;display:flex;flex-direction:column;gap:12px">
                      <?php if (!empty($oa["is_hot"])) : ?>
                      <div style="background:var(--text);color:#fff;border-radius:12px;padding:14px;position:relative;overflow:hidden">
                        <div style="font-size:11px;font-weight:700;letter-spacing:.5px;opacity:.5;margin-bottom:4px">HOT OFFER</div>
                        <div style="font-size:22px;font-weight:800;letter-spacing:-.5px"><?php echo esc_html(sprintf(/* translators: %d score */ __("Score %d/100", "upsellio"), (int) ($oa["score"] ?? 0))); ?></div>
                        <div style="font-size:11px;opacity:.65;margin-top:4px;line-height:1.45"><?php esc_html_e("Klient wykazuje wysoką intencję zakupu. Odpowiedz dziś.", "upsellio"); ?></div>
                      </div>
                      <?php else : ?>
                      <div style="background:#f8f9f4;border:1px solid var(--border);border-radius:12px;padding:14px">
                        <div style="font-size:11px;font-weight:700;color:var(--text-3);letter-spacing:.5px;margin-bottom:4px"><?php esc_html_e("SCORE INTENCJI", "upsellio"); ?></div>
                        <div style="font-size:22px;font-weight:800;color:var(--text);letter-spacing:-.5px"><?php echo (int) ($oa["score"] ?? 0); ?>/100</div>
                        <div style="background:#f4f5f0;border-radius:999px;height:4px;margin-top:8px">
                          <div style="width:<?php echo (int) min(100, (int) ($oa["score"] ?? 0)); ?>%;height:100%;background:<?php echo ($oa["score"] ?? 0) >= 70 ? "#0d9488" : (($oa["score"] ?? 0) >= 40 ? "#d97706" : "#e2e5de"); ?>;border-radius:999px"></div>
                        </div>
                      </div>
                      <?php endif; ?>
                      <div style="background:#f8f9f4;border:1px solid var(--border);border-radius:12px;padding:14px">
                        <div style="font-size:11px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px"><?php esc_html_e("Sygnały zakupowe", "upsellio"); ?></div>
                        <?php
                        $oa_signals = [
                            [(int) ($oa["total_cta"] ?? 0) > 0, __("Kliknął CTA", "upsellio"), __("Aktywna akcja — najsilniejszy sygnał", "upsellio")],
                            [!empty($oa["last_commit"]), __("Wybrał pakiet", "upsellio"), __("Porównywał opcje i wskazał wybór", "upsellio")],
                            [(int) ($oa["session_count"] ?? 0) >= 3, __("3+ sesje", "upsellio"), __("Wraca i analizuje", "upsellio")],
                            [(int) ($oa["all_section_times"]["pricing"] ?? 0) >= 60, __("Czas cennik 1+ min", "upsellio"), __("Analizuje wycenę szczegółowo", "upsellio")],
                            [(int) ($oa["all_sections_viewed"]["faq"] ?? 0) > 0, __("Przeczytał FAQ", "upsellio"), __("Szuka odpowiedzi na obiekcje", "upsellio")],
                        ];
                        foreach ($oa_signals as $oa_sig) :
                            [$active, $label, $desc] = $oa_sig;
                            ?>
                          <div style="display:flex;gap:8px;align-items:flex-start;padding:6px 0;border-bottom:1px solid var(--border)">
                            <div style="width:18px;height:18px;border-radius:50%;background:<?php echo $active ? "#dcfce7" : "#f4f5f0"; ?>;display:grid;place-items:center;flex-shrink:0;margin-top:1px">
                              <span style="font-size:10px;color:<?php echo $active ? "#16a34a" : "#d0d4cc"; ?>"><?php echo $active ? "✓" : "○"; ?></span>
                            </div>
                            <div>
                              <div style="font-size:12px;font-weight:<?php echo $active ? "700" : "400"; ?>;color:<?php echo $active ? "var(--text)" : "var(--text-3)"; ?>"><?php echo esc_html($label); ?></div>
                              <div style="font-size:10px;color:var(--text-3);line-height:1.35"><?php echo esc_html($desc); ?></div>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      </div>
                      <?php
                      $oa_score = (int) ($oa["score"] ?? 0);
                      $oa_cta_count = (int) ($oa["total_cta"] ?? 0);
                      $oa_sess_count = (int) ($oa["session_count"] ?? 0);
                      $oa_pricing_sec_sig = (int) ($oa["all_section_times"]["pricing"] ?? 0);
                      if ($oa_cta_count > 0 && !empty($oa["last_commit"])) {
                          $rec_action = __("Wyślij umowę lub konkretną propozycję", "upsellio");
                          $rec_reason = __("Kliknął CTA i wybrał pakiet — intencja zakupu bardzo wysoka.", "upsellio");
                      } elseif ($oa_cta_count > 0) {
                          $rec_action = __("Zadzwoń lub wyślij follow-up dziś", "upsellio");
                          $rec_reason = __("Kliknął CTA ale nie wybrał pakietu — potrzebuje impulsu decyzyjnego.", "upsellio");
                      } elseif ($oa_pricing_sec_sig >= 60 && $oa_sess_count >= 2) {
                          $rec_action = __("Wyślij uzupełnienie oferty z odpowiedzią na cenę", "upsellio");
                          $rec_reason = __("Długo na cenniku przy wielu sesjach — prawdopodobna obiekcja cenowa.", "upsellio");
                      } elseif ($oa_sess_count >= 3 && $oa_score < 40) {
                          $rec_action = __("Zmień zakres lub cenę — coś blokuje decyzję", "upsellio");
                          $rec_reason = __("Wiele sesji bez akcji — klient analizuje ale nie przechodzi dalej.", "upsellio");
                      } elseif ($oa_sess_count === 0) {
                          $rec_action = __("Sprawdź czy oferta dotarła", "upsellio");
                          $rec_reason = __("Brak aktywności na ofercie — klient może nie dostał linka.", "upsellio");
                      } else {
                          $rec_action = __("Wyślij follow-up za 24–48h", "upsellio");
                          $rec_reason = __("Klient ogląda ale nie podjął jeszcze akcji.", "upsellio");
                      }
                      ?>
                      <div style="background:#fff;border:1px solid var(--border);border-radius:12px;padding:14px;border-left:3px solid #0d9488">
                        <div style="font-size:11px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px"><?php esc_html_e("Rekomendacja", "upsellio"); ?></div>
                        <div style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:4px"><?php echo esc_html($rec_action); ?></div>
                        <div style="font-size:11px;color:var(--text-3);line-height:1.45"><?php echo esc_html($rec_reason); ?></div>
                      </div>
                    </div>
                  </div>
                </section>
              <?php endif; ?>
            <?php endif; ?>
            <?php if ($view === "offers" || $view === "deals") : ?>
              <?php
              $crm_offer_form_view = $view === "deals" ? "deals" : "offers";
              $crm_clients_json = [];
              foreach ($clients as $c_client) {
                  $ccid = (int) $c_client->ID;
                  $crm_clients_json[] = [
                      "id" => $ccid,
                      "name" => (string) $c_client->post_title,
                      "company" => (string) get_post_meta($ccid, "_ups_client_company", true),
                      "email" => (string) get_post_meta($ccid, "_ups_client_email", true),
                      "phone" => (string) get_post_meta($ccid, "_ups_client_phone", true),
                      "industry" => (string) get_post_meta($ccid, "_ups_client_industry", true),
                      "budget_range" => (string) get_post_meta($ccid, "_ups_client_budget_range", true),
                      "monthly_value" => (float) get_post_meta($ccid, "_ups_client_monthly_value", true),
                  ];
              }
              $oe = $offer_editor_post instanceof WP_Post ? $offer_editor_post : null;
              $oe_id = $oe instanceof WP_Post ? (int) $oe->ID : 0;
              $oe_exp_at = $oe_id > 0 ? (int) get_post_meta($oe_id, "_ups_offer_expires_at", true) : 0;
              $oe_exp_local = $oe_exp_at > 0 ? gmdate("Y-m-d\TH:i", $oe_exp_at + (int) (get_option("gmt_offset", 0) * HOUR_IN_SECONDS)) : "";
              ?>
              <script type="application/json" id="ups-crm-clients-json"><?php echo wp_json_encode($crm_clients_json); ?></script>
              <section class="card">
                <h2><?php echo $view === "deals" ? "Deale" : "Oferty"; ?></h2>
                <p class="muted" style="margin-bottom:12px"><?php echo $view === "deals" ? "Tabela dealów i quick actions. Szczegóły strony publicznej edytujesz w budowniczku (widok Oferty)." : "Lista aktywnych dealów. Konfiguracja strony publicznej (layout, zakres, pytania) jest w oknie <strong>budowniczka</strong>."; ?></p>
                <?php if ($offer_editor_id > 0) : ?>
                <div class="crm-view-tabs" role="navigation" aria-label="<?php esc_attr_e("Oferta — nawigacja", "upsellio"); ?>">
                  <a class="crm-tab-link" href="<?php echo esc_url(add_query_arg(["view" => $crm_offer_form_view], home_url("/crm-app/"))); ?>"><?php esc_html_e("Lista ofert", "upsellio"); ?></a>
                  <a class="crm-tab-link is-active" href="<?php echo esc_url(add_query_arg(["view" => $crm_offer_form_view, "offer_editor_id" => $offer_editor_id], home_url("/crm-app/"))); ?>"><?php esc_html_e("Budowniczek", "upsellio"); ?></a>
                  <a class="crm-tab-link" href="<?php echo esc_url(add_query_arg(["view" => "offer_analytics", "offer_id" => $offer_editor_id, "list_view" => $crm_offer_form_view], home_url("/crm-app/"))); ?>"><?php esc_html_e("Analityka oferty", "upsellio"); ?></a>
                  <a class="crm-tab-link" href="<?php echo esc_url(add_query_arg(["view" => "inbox", "inbox_offer" => $offer_editor_id], home_url("/crm-app/"))); ?>"><?php esc_html_e("Inbox wątku", "upsellio"); ?></a>
                </div>
                <?php endif; ?>
                <p style="margin-bottom:12px">
                  <button type="button" class="btn" id="ups-open-offer-builder">Nowa oferta — budowniczek</button>
                  <a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "template-studio"], home_url("/crm-app/"))); ?>">Generator szablonów</a>
                </p>

                <div class="crm-modal-overlay" id="ups-offer-builder-overlay" aria-hidden="true">
                  <div class="crm-modal" role="dialog" aria-labelledby="ups-offer-builder-title">
                    <h2 id="ups-offer-builder-title" style="font-family:var(--font-display);font-size:20px;margin-bottom:4px">Budowniczek oferty</h2>
                    <p class="muted" style="margin-bottom:12px;font-size:13px">Wybierz klienta — pola podpowiadają się z kartoteki (firma, budżet, branża). Szablon layoutu możesz przygotować w Generatorze szablonów.</p>
                    <form method="post" id="ups-offer-builder-form">
                      <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                      <input type="hidden" name="ups_action" value="save_offer" />
                      <input type="hidden" name="crm_view" value="<?php echo esc_attr($crm_offer_form_view); ?>" />
                      <input type="hidden" name="offer_id" id="offer_id_field" value="" />
                      <div class="crm-tabs" role="tablist">
                        <button type="button" class="crm-tab active" data-pane="p-basic">Podstawowe</button>
                        <button type="button" class="crm-tab" data-pane="p-landing">Strona publiczna</button>
                        <button type="button" class="crm-tab" data-pane="p-scope">Zakres i treść</button>
                        <button type="button" class="crm-tab" data-pane="p-internal">Notatki</button>
                      </div>
                      <div class="crm-pane active offer-dlg-grid" id="pane-p-basic" style="grid-column:1/-1">
                        <div style="grid-column:1/-1">
                          <label><strong>Tytuł oferty</strong></label>
                          <input type="text" name="offer_title" id="fld_offer_title" required value="<?php echo esc_attr($oe instanceof WP_Post ? (string) $oe->post_title : ""); ?>" />
                        </div>
                        <div>
                          <label><strong>Klient</strong></label>
                          <select name="offer_client_id" id="fld_offer_client_id">
                            <option value="">— wybierz —</option>
                            <?php foreach ($clients as $client) : ?>
                              <option value="<?php echo esc_attr((string) $client->ID); ?>" <?php selected($oe_id > 0 ? (int) get_post_meta($oe_id, "_ups_offer_client_id", true) : 0, (int) $client->ID); ?>><?php echo esc_html((string) $client->post_title); ?></option>
                            <?php endforeach; ?>
                          </select>
                          <span class="odlg-hint">Po zmianie klienta możesz kliknąć „Uzupełnij z klienta”.</span>
                          <button type="button" class="btn alt" style="margin-top:8px" id="ups-fill-from-client">Uzupełnij z klienta</button>
                        </div>
                        <div>
                          <label><strong>Szablon layoutu (nowa oferta)</strong></label>
                          <select name="offer_layout_template_id" id="fld_offer_layout_template_id">
                            <option value="">— bez szablonu —</option>
                            <?php foreach ($offer_layout_templates as $olt) : ?>
                              <option value="<?php echo esc_attr((string) $olt->ID); ?>"><?php echo esc_html((string) $olt->post_title); ?></option>
                            <?php endforeach; ?>
                          </select>
                          <span class="odlg-hint">Stosowany tylko przy pierwszym zapisie — potem edytuj pola niżej.</span>
                        </div>
                        <div style="grid-column:1/-1;margin-top:4px;display:flex;flex-wrap:wrap;align-items:center;gap:10px">
                          <button type="button" class="btn alt" id="ups-offer-ai-fill"><?php esc_html_e("✨ Wypełnij AI na podstawie danych klienta", "upsellio"); ?></button>
                          <span id="ups-offer-ai-fill-status" style="font-size:12px;color:var(--text-3)"></span>
                        </div>
                        <div>
                          <label><strong>Cena / inwestycja</strong></label>
                          <input type="text" name="offer_price" id="fld_offer_price" value="<?php echo esc_attr($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_price", true) : ""); ?>" />
                        </div>
                        <div>
                          <label><strong>Start / timeline</strong></label>
                          <input type="text" name="offer_timeline" id="fld_offer_timeline" value="<?php echo esc_attr($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_timeline", true) : ""); ?>" />
                        </div>
                        <div>
                          <label><strong>Planowana data decyzji klienta</strong></label>
                          <input type="date" name="offer_decision_date" id="fld_offer_decision_date" value="<?php echo esc_attr($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_decision_date", true) : ""); ?>" />
                          <span class="odlg-hint">Używana do timing score (np. gdy brak powiązanego leada).</span>
                        </div>
                        <div>
                          <label><strong>Wygasa</strong></label>
                          <input type="datetime-local" name="offer_expires_at" id="fld_offer_expires_at" value="<?php echo esc_attr($oe_exp_local); ?>" />
                        </div>
                        <div>
                          <label><strong>Status</strong></label>
                          <select name="offer_status" id="fld_offer_status">
                            <?php $oe_st = $oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_status", true) : "open"; ?>
                            <option value="open" <?php selected($oe_st, "open"); ?>>otwarty</option>
                            <option value="sent" <?php selected($oe_st, "sent"); ?>>wysłana</option>
                            <option value="won" <?php selected($oe_st, "won"); ?>>wygrany</option>
                            <option value="lost" <?php selected($oe_st, "lost"); ?>>przegrany</option>
                          </select>
                        </div>
                        <div>
                          <label><strong>Opiekun</strong></label>
                          <?php $oe_own = $oe_id > 0 ? (int) get_post_meta($oe_id, "_ups_offer_owner_id", true) : 0; ?>
                          <select name="offer_owner_id" id="fld_offer_owner_id">
                            <option value="">— domyślny —</option>
                            <?php foreach (get_users(["role__in" => ["administrator", "editor"], "orderby" => "display_name", "order" => "ASC"]) as $owner) : ?>
                              <?php $owner_id = isset($owner->ID) ? (int) $owner->ID : 0; if ($owner_id <= 0) { continue; } ?>
                              <option value="<?php echo esc_attr((string) $owner_id); ?>" <?php selected($oe_own, $owner_id); ?>><?php echo esc_html((string) ($owner->display_name ?? ("User #" . $owner_id))); ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div>
                          <label><strong>Wartość wygranej (PLN)</strong></label>
                          <input type="number" step="0.01" min="0" name="offer_won_value" id="fld_offer_won_value" value="<?php echo esc_attr($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_won_value", true) : ""); ?>" />
                        </div>
                        <div>
                          <label><strong>Powód wygranej</strong></label>
                          <select name="offer_win_reason" id="fld_offer_win_reason">
                            <option value="">—</option>
                            <option value="price_fit" <?php selected($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_win_reason", true) : "", "price_fit"); ?>>dopasowanie ceny</option>
                            <option value="trust" <?php selected($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_win_reason", true) : "", "trust"); ?>>zaufanie</option>
                            <option value="urgency" <?php selected($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_win_reason", true) : "", "urgency"); ?>>pilność</option>
                            <option value="referral" <?php selected($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_win_reason", true) : "", "referral"); ?>>referencje</option>
                            <option value="competitive_edge" <?php selected($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_win_reason", true) : "", "competitive_edge"); ?>>przewaga</option>
                          </select>
                        </div>
                        <div>
                          <label><strong>Powód przegranej</strong></label>
                          <select name="offer_loss_reason" id="fld_offer_loss_reason">
                            <option value="">—</option>
                            <option value="price" <?php selected($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_loss_reason", true) : "", "price"); ?>>cena</option>
                            <option value="budget" <?php selected($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_loss_reason", true) : "", "budget"); ?>>budżet</option>
                            <option value="competitor" <?php selected($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_loss_reason", true) : "", "competitor"); ?>>konkurencja</option>
                            <option value="timing" <?php selected($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_loss_reason", true) : "", "timing"); ?>>timing</option>
                            <option value="no_need" <?php selected($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_loss_reason", true) : "", "no_need"); ?>>brak potrzeby</option>
                            <option value="no_decision" <?php selected($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_loss_reason", true) : "", "no_decision"); ?>>brak decyzji</option>
                            <option value="no_response" <?php selected($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_loss_reason", true) : "", "no_response"); ?>>brak odpowiedzi</option>
                            <option value="scope" <?php selected($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_loss_reason", true) : "", "scope"); ?>>zakres</option>
                          </select>
                        </div>
                        <div style="grid-column:1/-1">
                          <label><strong>Komentarz do przegranej (opcjonalnie)</strong></label>
                          <textarea name="offer_loss_reason_note" id="fld_offer_loss_reason_note" rows="2" placeholder="Np. nazwa konkurenta, cytat klienta…"><?php echo esc_textarea($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_loss_reason_note", true) : ""); ?></textarea>
                        </div>
                        <div>
                          <label><strong>Tekst przycisku akceptacji</strong></label>
                          <input type="text" name="offer_cta_text" id="fld_offer_cta_text" value="<?php echo esc_attr($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_cta_text", true) : ""); ?>" placeholder="Akceptuję ofertę" />
                        </div>
                        <div style="grid-column:1/-1">
                          <label><input type="checkbox" name="offer_generate_from_template" value="1" /> Regeneruj treść wpisu z <em>legacy</em> szablonu HTML (Ustawienia → Szablon oferty)</label>
                        </div>
                      </div>
                      <div class="crm-pane offer-dlg-grid" id="pane-p-landing" style="grid-column:1/-1">
                        <div style="grid-column:1/-1">
                          <label><strong>Lead (pod tytułem)</strong></label>
                          <textarea name="offer_lead" id="fld_offer_lead" rows="3"><?php echo esc_textarea($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_lead", true) : ""); ?></textarea>
                        </div>
                        <div>
                          <label><strong>Czas trwania (karta)</strong></label>
                          <input type="text" name="offer_duration" id="fld_offer_duration" value="<?php echo esc_attr($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_duration", true) : ""); ?>" />
                        </div>
                        <div>
                          <label><strong>Model rozliczenia</strong></label>
                          <input type="text" name="offer_billing" id="fld_offer_billing" value="<?php echo esc_attr($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_billing", true) : ""); ?>" />
                        </div>
                        <div style="grid-column:1/-1">
                          <label><strong>Notka pod ceną</strong></label>
                          <input type="text" name="offer_price_note" id="fld_offer_price_note" value="<?php echo esc_attr($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_price_note", true) : ""); ?>" />
                        </div>
                        <div style="grid-column:1/-1">
                          <label><input type="checkbox" name="offer_show_proof" value="1" id="fld_offer_show_proof" <?php checked($oe_id > 0 && (string) get_post_meta($oe_id, "_ups_offer_show_proof", true) === "1"); ?> /> Pasek „podobne firmy”</label>
                        </div>
                        <div style="grid-column:1/-1">
                          <label><strong>Logo / branże (jedna linia = jeden badge)</strong></label>
                          <textarea name="offer_proof_lines" id="fld_offer_proof_lines" rows="3"><?php echo esc_textarea($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_proof_lines", true) : ""); ?></textarea>
                        </div>
                        <div style="grid-column:1/-1">
                          <label><strong>Warianty zainteresowania (JSON)</strong></label>
                          <textarea name="offer_services_json" id="fld_offer_services_json" rows="4" class="code"><?php echo esc_textarea($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_services_json", true) : ""); ?></textarea>
                          <span class="odlg-hint">Np. [{"key":"all","label":"Cały pakiet","price_hint":"od 4 900 PLN"}]</span>
                        </div>
                        <div style="grid-column:1/-1">
                          <label><strong>Pytania do klienta (linia = pytanie, opcjonalnie „pytanie|notka”)</strong></label>
                          <textarea name="offer_questions_raw" id="fld_offer_questions_raw" rows="4"><?php echo esc_textarea($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_questions_raw", true) : ""); ?></textarea>
                        </div>
                        <div style="grid-column:1/-1">
                          <label><strong>Zawarte w cenie (linie)</strong></label>
                          <textarea name="offer_include_lines" id="fld_offer_include_lines" rows="4"><?php echo esc_textarea($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_include_lines", true) : ""); ?></textarea>
                        </div>
                        <div style="grid-column:1/-1">
                          <label><strong>Opcje dodatkowe (linie)</strong></label>
                          <textarea name="offer_option_lines" id="fld_offer_option_lines" rows="3"><?php echo esc_textarea($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_option_lines", true) : ""); ?></textarea>
                        </div>
                      </div>
                      <div class="crm-pane offer-dlg-grid" id="pane-p-scope" style="grid-column:1/-1">
                        <label style="grid-column:1/-1;display:flex;gap:14px;align-items:center;flex-wrap:wrap">
                          <span><input type="checkbox" name="offer_has_google" value="1" <?php checked($oe_id <= 0 || (string) get_post_meta($oe_id, "_ups_offer_has_google", true) !== "0"); ?> /> Zakres Google Ads</span>
                          <span><input type="checkbox" name="offer_has_meta" value="1" <?php checked($oe_id <= 0 || (string) get_post_meta($oe_id, "_ups_offer_has_meta", true) !== "0"); ?> /> Zakres Meta Ads</span>
                          <span><input type="checkbox" name="offer_has_web" value="1" <?php checked($oe_id > 0 && (string) get_post_meta($oe_id, "_ups_offer_has_web", true) === "1"); ?> /> Strona / WWW</span>
                        </label>
                        <div style="grid-column:1/-1">
                          <label><strong>Dodatkowe wiersze zakresu (HTML)</strong></label>
                          <textarea name="offer_scope_extra_html" id="fld_offer_scope_extra_html" rows="4"><?php echo esc_textarea($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_scope_extra_html", true) : ""); ?></textarea>
                        </div>
                        <div style="grid-column:1/-1">
                          <label><strong>Treść sekcji „Szczegóły” (edytor wpisu)</strong></label>
                          <textarea name="offer_content" id="fld_offer_content" rows="8"><?php echo esc_textarea($oe instanceof WP_Post ? (string) $oe->post_content : ""); ?></textarea>
                        </div>
                      </div>
                      <div class="crm-pane offer-dlg-grid" id="pane-p-internal" style="grid-column:1/-1">
                        <div style="grid-column:1/-1">
                          <label><strong>Notatki deala</strong></label>
                          <textarea name="deal_notes" id="fld_deal_notes" rows="3"><?php echo esc_textarea($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_deal_notes", true) : ""); ?></textarea>
                        </div>
                        <div style="grid-column:1/-1">
                          <label><strong>Notatki wewnętrzne oferty</strong></label>
                          <textarea name="offer_internal_notes" id="fld_offer_internal_notes" rows="3"><?php echo esc_textarea($oe_id > 0 ? (string) get_post_meta($oe_id, "_ups_offer_internal_notes", true) : ""); ?></textarea>
                        </div>
                      </div>
                      <div class="crm-modal-actions">
                        <button type="button" class="btn alt" id="ups-close-offer-builder">Anuluj</button>
                        <button type="submit" class="btn">Zapisz ofertę</button>
                      </div>
                    </form>
                  </div>
                </div>

                <script>
                (function(){
                  var overlay=document.getElementById("ups-offer-builder-overlay");
                  var form=document.getElementById("ups-offer-builder-form");
                  if(!overlay||!form)return;
                  var offerAiNonce=<?php echo wp_json_encode(wp_create_nonce("ups_crm_app_action")); ?>;
                  var offerAiAjax=<?php echo wp_json_encode(admin_url("admin-ajax.php")); ?>;
                  var clientsEl=document.getElementById("ups-crm-clients-json");
                  var clients=[];
                  try{clients=clientsEl?JSON.parse(clientsEl.textContent||"[]"):[];}catch(e){clients=[];}
                  function openDlg(){overlay.classList.add("open");overlay.setAttribute("aria-hidden","false");}
                  function closeDlg(){
                    if(window.UpsellioCrmDirty&&UpsellioCrmDirty.isDirty(form)){
                      if(!window.confirm("Masz niezapisane zmiany w budowniczku. Zamknąć bez zapisu?")){return;}
                      UpsellioCrmDirty.markClean(form);
                    }
                    overlay.classList.remove("open");overlay.setAttribute("aria-hidden","true");
                  }
                  document.getElementById("ups-open-offer-builder").addEventListener("click",function(){
                    form.reset();
                    document.getElementById("offer_id_field").value="";
                    document.querySelectorAll("#ups-offer-builder-form input, #ups-offer-builder-form select, #ups-offer-builder-form textarea").forEach(function(el){if(el.type==="checkbox")el.checked=false;});
                    document.getElementById("fld_offer_status").value="open";
                    var g=document.querySelector("#pane-p-scope input[name=offer_has_google]");if(g)g.checked=true;
                    var m=document.querySelector("#pane-p-scope input[name=offer_has_meta]");if(m)m.checked=true;
                    var w=document.querySelector("#pane-p-scope input[name=offer_has_web]");if(w)w.checked=false;
                    openDlg();
                    if(window.UpsellioCrmDirty){UpsellioCrmDirty.markClean(form);}
                  });
                  <?php if ($offer_editor_id > 0) : ?>
                  document.getElementById("offer_id_field").value="<?php echo esc_js((string) $offer_editor_id); ?>";
                  <?php endif; ?>
                  document.getElementById("ups-close-offer-builder").addEventListener("click",closeDlg);
                  overlay.addEventListener("click",function(e){if(e.target===overlay)closeDlg();});
                  overlay.querySelector(".crm-modal").addEventListener("click",function(e){e.stopPropagation();});
                  document.querySelectorAll(".crm-tab").forEach(function(tab){
                    tab.addEventListener("click",function(){
                      document.querySelectorAll(".crm-tab").forEach(function(t){t.classList.remove("active");});
                      tab.classList.add("active");
                      var p=tab.getAttribute("data-pane");
                      document.querySelectorAll(".crm-pane").forEach(function(pn){pn.classList.toggle("active",pn.id==="pane-"+p);});
                    });
                  });
                  function clientById(id){id=String(id||"");for(var i=0;i<clients.length;i++){if(String(clients[i].id)===id)return clients[i];}return null;}
                  var aiFillBtn=document.getElementById("ups-offer-ai-fill");
                  if(aiFillBtn){aiFillBtn.addEventListener("click",function(){
                    var st=document.getElementById("ups-offer-ai-fill-status");
                    var oidEl=document.getElementById("offer_id_field");
                    var cidEl=document.getElementById("fld_offer_client_id");
                    var offerId=oidEl&&oidEl.value?String(oidEl.value):"0";
                    var clientId=cidEl&&cidEl.value?String(cidEl.value):"0";
                    if(!clientId||clientId==="0"){if(st)st.textContent="<?php echo esc_js(__("Wybierz klienta.", "upsellio")); ?>";return;}
                    aiFillBtn.disabled=true;if(st)st.textContent="⏳ …";
                    var body=new FormData();
                    body.append("action","upsellio_offer_ai_fill");
                    body.append("nonce",offerAiNonce);
                    body.append("offer_id",offerId);
                    body.append("client_id",clientId);
                    fetch(offerAiAjax,{method:"POST",body:body}).then(function(r){return r.json();}).then(function(data){
                      aiFillBtn.disabled=false;
                      if(!data||!data.success){if(st)st.textContent="✗ "+(data&&data.data&&data.data.message?String(data.data.message):"<?php echo esc_js(__("Błąd", "upsellio")); ?>");return;}
                      var d=data.data||{};
                      var aiMap={
                        title:"fld_offer_title",
                        price:"fld_offer_price",
                        timeline:"fld_offer_timeline",
                        decision_date:"fld_offer_decision_date",
                        lead:"fld_offer_lead",
                        duration:"fld_offer_duration",
                        billing:"fld_offer_billing",
                        price_note:"fld_offer_price_note",
                        proof_lines:"fld_offer_proof_lines",
                        services_json:"fld_offer_services_json",
                        questions_raw:"fld_offer_questions_raw",
                        include_lines:"fld_offer_include_lines",
                        option_lines:"fld_offer_option_lines",
                        cta_text:"fld_offer_cta_text",
                        scope_extra_html:"fld_offer_scope_extra_html",
                        content:"fld_offer_content",
                        deal_notes:"fld_deal_notes",
                        internal_notes:"fld_offer_internal_notes"
                      };
                      Object.keys(aiMap).forEach(function(k){
                        var id=aiMap[k];
                        var el=document.getElementById(id);
                        if(!el||d[k]===undefined||d[k]===null)return;
                        var v=d[k];
                        if(typeof v==="string"&&v.trim()==="")return;
                        if(typeof v==="string"||typeof v==="number")el.value=String(v);
                      });
                      var g=document.querySelector("#pane-p-scope input[name=offer_has_google]");
                      var m=document.querySelector("#pane-p-scope input[name=offer_has_meta]");
                      var w=document.querySelector("#pane-p-scope input[name=offer_has_web]");
                      if(g&&typeof d.has_google==="boolean")g.checked=!!d.has_google;
                      if(m&&typeof d.has_meta==="boolean")m.checked=!!d.has_meta;
                      if(w&&typeof d.has_web==="boolean")w.checked=!!d.has_web;
                      if(st)st.textContent="<?php echo esc_js(__("✓ Pola wypełnione — sprawdź zakładki i zapisz ofertę.", "upsellio")); ?>";
                      if(window.UpsellioCrmDirty){UpsellioCrmDirty.sync(form);}
                    }).catch(function(){
                      aiFillBtn.disabled=false;
                      var stE=document.getElementById("ups-offer-ai-fill-status");
                      if(stE)stE.textContent="✗ <?php echo esc_js(__("Błąd sieci", "upsellio")); ?>";
                    });
                  });}
                  document.getElementById("ups-fill-from-client").addEventListener("click",function(){
                    var c=clientById(document.getElementById("fld_offer_client_id").value);
                    if(!c)return;
                    var lead=document.getElementById("fld_offer_lead");
                    if(lead&&!lead.value)c.company?lead.value="Propozycja dla "+c.company+".":lead.value="Propozycja dopasowana do Twoich celów biznesowych.";
                    var price=document.getElementById("fld_offer_price");
                    if(price&&!price.value&&c.monthly_value>0)price.value=String(Math.round(c.monthly_value))+" PLN / mies.";
                    var br=document.getElementById("fld_offer_price_note");
                    if(br&&!br.value&&c.budget_range)br.value="Budżet reklamowy (orientacyjnie): "+c.budget_range;
                    var proof=document.getElementById("fld_offer_proof_lines");
                    if(proof&&!proof.value&&c.industry)proof.value=c.industry;
                    var title=document.getElementById("fld_offer_title");
                    if(title&&!title.value)c.company?title.value="Oferta — "+c.company:title.value="Oferta — "+c.name;
                    if(window.UpsellioCrmDirty){UpsellioCrmDirty.sync(form);}
                  });
                  if(window.UpsellioCrmDirty){UpsellioCrmDirty.register(form);}
                  <?php if ($offer_editor_id > 0) : ?>openDlg();<?php endif; ?>
                  form.addEventListener("submit", function (ev) {
                    var st = document.getElementById("fld_offer_status");
                    var lr = document.getElementById("fld_offer_loss_reason");
                    if (!st || st.value !== "lost") {
                      return;
                    }
                    if (lr && lr.value) {
                      return;
                    }
                    ev.preventDefault();
                    if (typeof window.upsellioOpenLossModal !== "function") {
                      alert("Wybierz powód przegranej na liście lub odśwież stronę.");
                      return;
                    }
                    window.upsellioOpenLossModal().then(function (res) {
                      if (!res || !res.reason) {
                        return;
                      }
                      lr.value = res.reason;
                      var noteEl = document.getElementById("fld_offer_loss_reason_note");
                      if (noteEl) {
                        noteEl.value = res.note || "";
                      }
                      if (typeof form.requestSubmit === "function") {
                        form.requestSubmit();
                      } else {
                        window.__upsCrmSkipDirtyUnload = true;
                        form.submit();
                      }
                    });
                  });
                })();
                </script>
                <table>
                  <thead><tr><th>Klient</th><th>Oferta</th><th><?php esc_html_e("Score intencji", "upsellio"); ?></th><th><?php esc_html_e("Aktywność", "upsellio"); ?></th><th><?php esc_html_e("Ostatnia wizyta", "upsellio"); ?></th><th>Status</th><th>Etap</th><th>Score / prawd.</th><th>Gorąca</th><th>Win / loss</th><th>Notatki</th><th>Publiczny / edycja</th><th>Follow-up</th></tr></thead>
                  <tbody>
                  <?php foreach ($offers as $offer) : ?>
                    <?php
                    $offer_id = (int) $offer->ID;
                    $offer_client_id = (int) get_post_meta($offer_id, "_ups_offer_client_id", true);
                    $offer_url = function_exists("upsellio_offer_get_public_url") ? (string) upsellio_offer_get_public_url($offer_id) : "";
                    $offer_log = get_post_meta($offer_id, "_ups_offer_activity_log", true);
                    if (!is_array($offer_log)) { $offer_log = []; }
                    $owr = (string) get_post_meta($offer_id, "_ups_offer_win_reason", true);
                    $olr = (string) get_post_meta($offer_id, "_ups_offer_loss_reason", true);
                    ?>
                    <tr>
                      <td><?php echo esc_html($offer_client_id > 0 ? (string) get_the_title($offer_client_id) : "—"); ?></td>
                      <td><?php echo esc_html((string) $offer->post_title); ?></td>
                      <?php
                      $o_score  = (int) get_post_meta($offer_id, "_ups_offer_score", true);
                      $o_hot    = get_post_meta($offer_id, "_ups_offer_hot_offer", true) === "1";
                      $o_last   = (string) get_post_meta($offer_id, "_ups_offer_last_seen", true);
                      $o_events = get_post_meta($offer_id, "_ups_offer_events", true);
                      $o_ev_cnt = is_array($o_events) ? count($o_events) : 0;
                      $o_last_disp = $o_last && strtotime($o_last) ? wp_date("d.m H:i", strtotime($o_last)) : "—";
                      $o_score_color = $o_score >= 70 ? "#0f766e" : ($o_score >= 40 ? "#d97706" : "var(--text-3)");
                      ?>
                      <td>
                        <div style="display:flex;align-items:center;gap:6px">
                          <div style="width:36px;height:36px;border-radius:50%;border:2px solid <?php echo esc_attr($o_score_color); ?>;display:grid;place-items:center;font-size:11px;font-weight:800;color:<?php echo esc_attr($o_score_color); ?>">
                            <?php echo (int) $o_score; ?>
                          </div>
                          <?php if ($o_hot) {
                              echo '<span title="Hot offer">🔥</span>';
                          } ?>
                        </div>
                      </td>
                      <td>
                        <div style="font-size:12px;color:var(--text-2)">
                          <?php echo (int) $o_ev_cnt; ?> <?php esc_html_e("zdarzeń", "upsellio"); ?>
                        </div>
                        <a href="<?php echo esc_url(add_query_arg(["view" => "offer_analytics", "offer_id" => $offer_id, "list_view" => $crm_offer_form_view], home_url("/crm-app/"))); ?>"
                           style="font-size:11px;color:var(--teal)"><?php esc_html_e("Analityka →", "upsellio"); ?></a>
                      </td>
                      <td style="font-size:12px;color:var(--text-3)"><?php echo esc_html($o_last_disp); ?></td>
                      <td>
                        <span class="badge gray"><?php echo esc_html($pl_label((string) get_post_meta($offer_id, "_ups_offer_status", true), "offer_status")); ?></span>
                        <?php
                        $first_sent_row = (string) get_post_meta($offer_id, "_ups_offer_first_sent_at", true);
                        $last_sent_row = (string) get_post_meta($offer_id, "_ups_offer_email_sent_at", true);
                        ?>
                        <?php if ($first_sent_row !== "") : ?>
                          <br/><small class="muted">Pierwsza wysyłka: <?php echo esc_html($first_sent_row); ?></small>
                        <?php elseif ($last_sent_row !== "") : ?>
                          <br/><small class="muted">Ostatni mail: <?php echo esc_html($last_sent_row); ?></small>
                        <?php endif; ?>
                      </td>
                      <td><span class="badge dark"><?php echo esc_html($pl_label((string) get_post_meta($offer_id, "_ups_offer_stage", true), "stage")); ?></span></td>
                      <td><small><?php echo esc_html((string) (int) get_post_meta($offer_id, "_ups_offer_lead_score_0_100", true)); ?> / <?php echo esc_html((string) (int) get_post_meta($offer_id, "_ups_offer_deal_probability_0_100", true)); ?>%</small><br/><small class="muted"><?php echo esc_html((string) get_post_meta($offer_id, "_ups_offer_temperature", true)); ?></small></td>
                      <td><?php echo (string) get_post_meta($offer_id, "_ups_offer_hot_offer", true) === "1" ? "🔥" : "—"; ?></td>
                      <td>
                        <form method="post" style="display:flex;flex-direction:column;gap:6px;max-width:220px">
                          <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                          <input type="hidden" name="ups_action" value="save_offer_outcomes" />
                          <input type="hidden" name="crm_view" value="<?php echo esc_attr($crm_offer_form_view); ?>" />
                          <input type="hidden" name="offer_id" value="<?php echo esc_attr((string) $offer_id); ?>" />
                          <select name="offer_win_reason">
                            <option value="">wygrana: —</option>
                            <option value="price_fit" <?php selected($owr, "price_fit"); ?>>dopasowanie ceny</option>
                            <option value="trust" <?php selected($owr, "trust"); ?>>zaufanie</option>
                            <option value="urgency" <?php selected($owr, "urgency"); ?>>pilność</option>
                            <option value="referral" <?php selected($owr, "referral"); ?>>referencje</option>
                            <option value="competitive_edge" <?php selected($owr, "competitive_edge"); ?>>przewaga</option>
                          </select>
                          <select name="offer_loss_reason">
                            <option value="">przegrana: —</option>
                            <option value="price" <?php selected($olr, "price"); ?>>cena</option>
                            <option value="budget" <?php selected($olr, "budget"); ?>>budżet</option>
                            <option value="competitor" <?php selected($olr, "competitor"); ?>>konkurencja</option>
                            <option value="timing" <?php selected($olr, "timing"); ?>>timing</option>
                            <option value="no_need" <?php selected($olr, "no_need"); ?>>brak potrzeby</option>
                            <option value="no_decision" <?php selected($olr, "no_decision"); ?>>brak decyzji</option>
                            <option value="no_response" <?php selected($olr, "no_response"); ?>>brak odpowiedzi</option>
                            <option value="scope" <?php selected($olr, "scope"); ?>>zakres</option>
                          </select>
                          <button class="btn alt" type="submit">Zapisz powody</button>
                        </form>
                      </td>
                      <td>
                        <small>Deal: <?php echo esc_html((string) get_post_meta($offer_id, "_ups_deal_notes", true)); ?></small><br/>
                        <small>Oferta: <?php echo esc_html((string) get_post_meta($offer_id, "_ups_offer_internal_notes", true)); ?></small>
                        <?php if (!empty($offer_log)) : ?>
                          <br/><small class="muted">Log: <?php echo esc_html((string) (end($offer_log)["message"] ?? "event")); ?></small>
                        <?php endif; ?>
                      </td>
                      <td style="white-space:nowrap">
                        <?php if ($offer_url !== "") : ?><a class="btn alt" href="<?php echo esc_url($offer_url); ?>" target="_blank" rel="noopener noreferrer">Podgląd</a><?php endif; ?>
                        <a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => $crm_offer_form_view, "offer_editor_id" => $offer_id], home_url("/crm-app/"))); ?>">Budowniczek</a>
                      </td>
                      <td>
                        <form method="post">
                          <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                          <input type="hidden" name="ups_action" value="send_offer_followup_now" />
                          <input type="hidden" name="crm_view" value="<?php echo esc_attr($crm_offer_form_view); ?>" />
                          <input type="hidden" name="offer_id" value="<?php echo esc_attr((string) $offer_id); ?>" />
                          <select name="template_id">
                            <?php foreach ($followups as $template) : ?>
                              <option value="<?php echo esc_attr((string) $template->ID); ?>"><?php echo esc_html((string) $template->post_title); ?></option>
                            <?php endforeach; ?>
                          </select>
                          <button class="btn alt" type="submit">Wyślij teraz</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </section>
            <?php endif; ?>
            <?php if ($view === "template-studio") : ?>
              <?php
              $edit_offer_layout_id = isset($_GET["edit_offer_layout"]) ? (int) wp_unslash($_GET["edit_offer_layout"]) : 0;
              if ($edit_offer_layout_id > 0 && (get_post_type($edit_offer_layout_id) !== "crm_offer_layout" || !current_user_can("edit_post", $edit_offer_layout_id))) {
                  $edit_offer_layout_id = 0;
              }
              $studio_offer_payload = function_exists("upsellio_offer_layout_get_default_payload") ? upsellio_offer_layout_get_default_payload() : [];
              if ($edit_offer_layout_id > 0) {
                  $loaded_studio = upsellio_offer_layout_get_payload_from_post($edit_offer_layout_id);
                  if (!empty($loaded_studio)) {
                      $studio_offer_payload = array_merge($studio_offer_payload, $loaded_studio);
                  }
              }
              $studio_services = function_exists("upsellio_offer_layout_services_array_from_payload") ? upsellio_offer_layout_services_array_from_payload($studio_offer_payload) : [];
              for ($ups_si = count($studio_services); $ups_si < 6; $ups_si++) {
                  $studio_services[] = ["key" => "", "label" => "", "price_hint" => ""];
              }
              $studio_layout_title = $edit_offer_layout_id > 0 ? (string) get_the_title($edit_offer_layout_id) : "";
              ?>
              <section class="card">
                <h2>Generator szablonów</h2>
                <p class="muted" style="margin-bottom:12px">Szablony pól strony publicznej oferty (formularz) oraz szablony HTML umów z placeholderami. <a href="<?php echo esc_url(add_query_arg(["view" => "settings", "settings_tab" => "offer-template"], home_url("/crm-app/"))); ?>">Legacy: globalny HTML oferty</a> · <a href="<?php echo esc_url(add_query_arg(["view" => "settings", "settings_tab" => "contract-template"], home_url("/crm-app/"))); ?>">Legacy: globalny HTML umowy</a></p>
                <div class="crm-tabs">
                  <a class="crm-tab <?php echo $template_studio_tab === "offer" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "template-studio", "tab" => "offer"], home_url("/crm-app/"))); ?>">Szablony ofert</a>
                  <a class="crm-tab <?php echo $template_studio_tab === "contract" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "template-studio", "tab" => "contract"], home_url("/crm-app/"))); ?>">Szablony umów</a>
                </div>
              </section>
              <?php if ($template_studio_tab === "offer") : ?>
                <section class="card">
                  <h3><?php echo $edit_offer_layout_id > 0 ? "Edycja szablonu layoutu oferty" : "Nowy szablon layoutu oferty"; ?></h3>
                  <?php if ($edit_offer_layout_id > 0) : ?>
                    <p style="margin:0 0 12px"><a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "template-studio", "tab" => "offer"], home_url("/crm-app/"))); ?>">+ Nowy szablon (wyczyść formularz)</a></p>
                  <?php endif; ?>
                  <form method="post" class="grid2" id="ups-crm-offer-layout-form">
                    <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                    <input type="hidden" name="ups_action" value="save_offer_layout" />
                    <input type="hidden" name="crm_view" value="template-studio" />
                    <input type="hidden" name="template_studio_tab" value="offer" />
                    <input type="hidden" name="offer_layout_form" value="1" />
                    <input type="hidden" name="offer_layout_id" value="<?php echo esc_attr((string) $edit_offer_layout_id); ?>" />
                    <label style="grid-column:1/-1"><strong>Nazwa szablonu</strong></label>
                    <input type="text" style="grid-column:1/-1" name="offer_layout_title" placeholder="Np. Pakiet Ads pełny" required value="<?php echo esc_attr($studio_layout_title); ?>" />
                    <label style="grid-column:1/-1"><strong>Lead (tekst pod tytułem na landing page)</strong></label>
                    <textarea name="offer_layout_lead" style="grid-column:1/-1" rows="3"><?php echo esc_textarea((string) ($studio_offer_payload["lead"] ?? "")); ?></textarea>
                    <label><strong>Czas trwania / model</strong></label>
                    <label><strong>Rozliczenie</strong></label>
                    <input type="text" name="offer_layout_duration" value="<?php echo esc_attr((string) ($studio_offer_payload["duration"] ?? "")); ?>" placeholder="np. 3 mies. start + wypowiedzenie" />
                    <input type="text" name="offer_layout_billing" value="<?php echo esc_attr((string) ($studio_offer_payload["billing"] ?? "")); ?>" placeholder="np. Abonament miesięczny, VAT" />
                    <label style="grid-column:1/-1"><strong>Notka pod ceną</strong></label>
                    <input type="text" style="grid-column:1/-1" name="offer_layout_price_note" value="<?php echo esc_attr((string) ($studio_offer_payload["price_note"] ?? "")); ?>" />
                    <label style="grid-column:1/-1;display:flex;flex-wrap:wrap;gap:14px;align-items:center">
                      <span><input type="checkbox" name="offer_layout_show_proof" value="1" <?php checked(!empty($studio_offer_payload["show_proof"])); ?> /> Pasek „podobne firmy”</span>
                      <span><input type="checkbox" name="offer_layout_has_google" value="1" <?php checked(!empty($studio_offer_payload["has_google"])); ?> /> Zakres Google Ads</span>
                      <span><input type="checkbox" name="offer_layout_has_meta" value="1" <?php checked(!empty($studio_offer_payload["has_meta"])); ?> /> Zakres Meta Ads</span>
                      <span><input type="checkbox" name="offer_layout_has_web" value="1" <?php checked(!empty($studio_offer_payload["has_web"])); ?> /> Strona / WWW</span>
                    </label>
                    <label style="grid-column:1/-1"><strong>Logo / branże (jedna linia = jeden badge)</strong></label>
                    <textarea name="offer_layout_proof_lines" style="grid-column:1/-1" rows="3"><?php echo esc_textarea((string) ($studio_offer_payload["proof_lines"] ?? "")); ?></textarea>
                    <label style="grid-column:1/-1"><strong>Warianty cenowe (do 6) — klucz techniczny, etykieta, podpowiedź ceny</strong></label>
                    <div style="grid-column:1/-1;overflow:auto">
                      <table style="min-width:520px;font-size:13px">
                        <thead><tr><th>Klucz (np. all, google)</th><th>Etykieta dla klienta</th><th>Podpowiedź ceny</th></tr></thead>
                        <tbody>
                        <?php foreach ($studio_services as $ups_row) : ?>
                          <tr>
                            <td><input type="text" name="offer_layout_svc_key[]" value="<?php echo esc_attr((string) ($ups_row["key"] ?? "")); ?>" placeholder="all" /></td>
                            <td><input type="text" name="offer_layout_svc_label[]" value="<?php echo esc_attr((string) ($ups_row["label"] ?? "")); ?>" placeholder="Cały pakiet" /></td>
                            <td><input type="text" name="offer_layout_svc_hint[]" value="<?php echo esc_attr((string) ($ups_row["price_hint"] ?? "")); ?>" placeholder="opcjonalnie" /></td>
                          </tr>
                        <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                    <label style="grid-column:1/-1"><strong>Pytania do klienta</strong> <span class="muted" style="font-weight:400">— linia = pytanie; opcjonalnie „pytanie|notka pomocnicza”</span></label>
                    <textarea name="offer_layout_questions_raw" style="grid-column:1/-1" rows="4"><?php echo esc_textarea((string) ($studio_offer_payload["questions_raw"] ?? "")); ?></textarea>
                    <label style="grid-column:1/-1"><strong>Zawarte w cenie (linie)</strong></label>
                    <textarea name="offer_layout_include_lines" style="grid-column:1/-1" rows="4"><?php echo esc_textarea((string) ($studio_offer_payload["include_lines"] ?? "")); ?></textarea>
                    <label style="grid-column:1/-1"><strong>Opcje dodatkowe (linie)</strong></label>
                    <textarea name="offer_layout_option_lines" style="grid-column:1/-1" rows="3"><?php echo esc_textarea((string) ($studio_offer_payload["option_lines"] ?? "")); ?></textarea>
                    <details style="grid-column:1/-1">
                      <summary class="muted" style="cursor:pointer;font-size:13px">Podgląd techniczny (JSON — tylko do skopiowania)</summary>
                      <pre style="margin-top:8px;font-size:11px;white-space:pre-wrap;max-height:200px;overflow:auto;background:var(--bg);padding:10px;border-radius:8px;border:1px solid var(--border)"><?php echo esc_html(wp_json_encode($studio_offer_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                    </details>
                    <button class="btn" type="submit">Zapisz szablon oferty</button>
                  </form>
                </section>
                <section class="card">
                  <h3>Zapisane szablony ofert</h3>
                  <table><thead><tr><th>Nazwa</th><th>Podsumowanie</th><th>Akcje</th></tr></thead><tbody>
                  <?php foreach ($offer_layout_templates as $olt) : ?>
                    <?php
                    $olt_id = (int) $olt->ID;
                    $prev_raw = (string) get_post_meta($olt_id, "_ups_offer_layout_payload", true);
                    $prev_pl = json_decode($prev_raw, true);
                    if (!is_array($prev_pl)) {
                        $prev_pl = [];
                    }
                    $pv_lead = isset($prev_pl["lead"]) ? wp_trim_words(wp_strip_all_tags((string) $prev_pl["lead"]), 22, "…") : "";
                    $pv_svc_n = function_exists("upsellio_offer_layout_services_array_from_payload") ? count(upsellio_offer_layout_services_array_from_payload($prev_pl)) : 0;
                    $pv_ch = [];
                    if (!empty($prev_pl["has_google"])) {
                        $pv_ch[] = "Google";
                    }
                    if (!empty($prev_pl["has_meta"])) {
                        $pv_ch[] = "Meta";
                    }
                    if (!empty($prev_pl["has_web"])) {
                        $pv_ch[] = "WWW";
                    }
                    ?>
                    <tr>
                      <td><?php echo esc_html((string) $olt->post_title); ?></td>
                      <td class="muted" style="font-size:12px;line-height:1.45">
                        <?php echo $pv_lead !== "" ? esc_html($pv_lead) : "—"; ?><br />
                        <span>Warianty: <?php echo esc_html((string) $pv_svc_n); ?> · zakres: <?php echo esc_html($pv_ch !== [] ? implode(", ", $pv_ch) : "—"); ?></span>
                      </td>
                      <td style="white-space:nowrap">
                        <a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "template-studio", "tab" => "offer", "edit_offer_layout" => $olt_id], home_url("/crm-app/"))); ?>">Edytuj</a>
                        <details style="display:inline-block;margin-left:4px;vertical-align:middle">
                          <summary class="btn alt" style="cursor:pointer;display:inline-block">JSON</summary>
                          <pre style="margin-top:8px;font-size:11px;white-space:pre-wrap;max-height:180px;overflow:auto"><?php echo esc_html($prev_raw); ?></pre>
                        </details>
                        <form method="post" style="display:inline;margin-left:6px" onsubmit="return confirm('Usunąć szablon?');">
                          <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                          <input type="hidden" name="ups_action" value="delete_offer_layout" />
                          <input type="hidden" name="crm_view" value="template-studio" />
                          <input type="hidden" name="template_studio_tab" value="offer" />
                          <input type="hidden" name="offer_layout_id" value="<?php echo esc_attr((string) $olt_id); ?>" />
                          <button type="submit" class="btn alt">Usuń</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  <?php if (empty($offer_layout_templates)) : ?><tr><td colspan="3" class="muted">Brak szablonów — dodaj pierwszy powyżej.</td></tr><?php endif; ?>
                  </tbody></table>
                </section>
              <?php else : ?>
                <section class="card">
                  <h3>Nowy / edytuj szablon umowy</h3>
                  <form method="post" class="grid2" id="ups-crm-contract-layout-form">
                    <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                    <input type="hidden" name="ups_action" value="save_contract_layout" />
                    <input type="hidden" name="crm_view" value="template-studio" />
                    <input type="hidden" name="template_studio_tab" value="contract" />
                    <input type="hidden" name="contract_layout_id" value="" />
                    <input type="text" name="contract_layout_title" placeholder="Nazwa szablonu umowy" required />
                    <span></span>
                    <label style="grid-column:1/-1"><strong>HTML</strong> — treść środka dokumentu na publicznej stronie umowy (nawigacja, sidebar z PDF i akceptacja są stałe). Placeholdery m.in. <code>{{client_name}}</code>, <code>{{client_company}}</code>, <code>{{offer_title}}</code>, <code>{{offer_price}}</code>, <code>{{offer_timeline}}</code>, <code>{{offer_url}}</code>, <code>{{contract_url}}</code>, <code>{{contract_title}}</code>, <code>{{offer_owner_email}}</code>, <code>{{today}}</code>.</label>
                    <textarea name="contract_layout_html" style="grid-column:1/-1;min-height:160px"><?php echo esc_textarea(function_exists("upsellio_contracts_get_default_template_html") ? (string) upsellio_contracts_get_default_template_html() : ""); ?></textarea>
                    <label style="grid-column:1/-1"><strong>CSS (opcjonalnie)</strong></label>
                    <textarea name="contract_layout_css" style="grid-column:1/-1;min-height:80px"><?php echo esc_textarea(function_exists("upsellio_contracts_get_default_template_css") ? (string) upsellio_contracts_get_default_template_css() : ""); ?></textarea>
                    <button class="btn" type="submit">Zapisz szablon umowy</button>
                  </form>
                </section>
                <section class="card">
                  <h3>Zapisane szablony umów</h3>
                  <table><thead><tr><th>Nazwa</th><th>Akcje</th></tr></thead><tbody>
                  <?php foreach ($contract_layout_templates as $clt) : ?>
                    <tr>
                      <td><?php echo esc_html((string) $clt->post_title); ?></td>
                      <td>
                        <form method="post" style="display:inline" onsubmit="return confirm('Usunąć szablon umowy?');">
                          <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                          <input type="hidden" name="ups_action" value="delete_contract_layout" />
                          <input type="hidden" name="crm_view" value="template-studio" />
                          <input type="hidden" name="template_studio_tab" value="contract" />
                          <input type="hidden" name="contract_layout_id" value="<?php echo esc_attr((string) $clt->ID); ?>" />
                          <button type="submit" class="btn alt">Usuń</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  <?php if (empty($contract_layout_templates)) : ?><tr><td colspan="2" class="muted">Brak szablonów umów.</td></tr><?php endif; ?>
                  </tbody></table>
                  <p class="muted" style="margin-top:10px">Przy tworzeniu umowy na widoku Umowy wybierz szablon z listy „Szablon z biblioteki”, aby podstawić HTML zamiast globalnego legacy.</p>
                </section>
              <?php endif; ?>
            <?php endif; ?>
            <?php if ($view === "pipeline") : ?>
              <?php
              $pipeline_total_deals = 0;
              $pipeline_open_deals = 0;
              $pipeline_won_deals = 0;
              $pipeline_lost_deals = 0;
              $pipeline_total_age_days = 0;
              $pipeline_total_stage_days = 0;
              $pipeline_open_age_count = 0;
              foreach ($offers as $offer_stats) {
                  $deal_id_stats = (int) $offer_stats->ID;
                  $deal_status_stats = (string) get_post_meta($deal_id_stats, "_ups_offer_status", true);
                  $pipeline_total_deals++;
                  if ($deal_status_stats === "won") {
                      $pipeline_won_deals++;
                  } elseif ($deal_status_stats === "lost") {
                      $pipeline_lost_deals++;
                  } else {
                      $pipeline_open_deals++;
                      $created_ts_stats = strtotime((string) $offer_stats->post_date_gmt);
                      if ($created_ts_stats !== false && $created_ts_stats > 0) {
                          $pipeline_total_age_days += max(0, (int) floor((time() - $created_ts_stats) / DAY_IN_SECONDS));
                          $pipeline_open_age_count++;
                      }
                      $stage_history_stats = get_post_meta($deal_id_stats, "_ups_offer_stage_history", true);
                      $stage_entered_ts_stats = false;
                      if (is_array($stage_history_stats) && !empty($stage_history_stats)) {
                          $last_entry_stats = end($stage_history_stats);
                          if (is_array($last_entry_stats) && !empty($last_entry_stats["ts"])) {
                              $stage_entered_ts_stats = strtotime((string) $last_entry_stats["ts"]);
                          }
                      }
                      if ($stage_entered_ts_stats === false || $stage_entered_ts_stats <= 0) {
                          $stage_entered_ts_stats = strtotime((string) $offer_stats->post_modified_gmt);
                      }
                      if ($stage_entered_ts_stats !== false && $stage_entered_ts_stats > 0) {
                          $pipeline_total_stage_days += max(0, (int) floor((time() - $stage_entered_ts_stats) / DAY_IN_SECONDS));
                      }
                  }
              }
              $pipeline_avg_age_days = $pipeline_open_age_count > 0 ? (int) round($pipeline_total_age_days / $pipeline_open_age_count) : 0;
              $pipeline_avg_stage_days = $pipeline_open_age_count > 0 ? (int) round($pipeline_total_stage_days / $pipeline_open_age_count) : 0;
              ?>
              <section class="card kpi"><span class="muted">Wszystkie deale</span><b><?php echo esc_html((string) $pipeline_total_deals); ?></b></section>
              <section class="card kpi"><span class="muted">Otwarte deale</span><b><?php echo esc_html((string) $pipeline_open_deals); ?></b></section>
              <section class="card kpi"><span class="muted">Śr. wiek deala</span><b><?php echo esc_html((string) $pipeline_avg_age_days); ?> dni</b></section>
              <section class="card kpi"><span class="muted">Śr. czas w etapie</span><b><?php echo esc_html((string) $pipeline_avg_stage_days); ?> dni</b></section>
              <section class="card" style="grid-column:1/-1;padding:12px 18px">
                <div class="crm-view-tabs" style="margin:0">
                  <a class="crm-tab-link <?php echo $pipeline_mode === "kanban" ? "is-active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "pipeline", "pipeline_mode" => "kanban"], home_url("/crm-app/"))); ?>"><?php esc_html_e("Kanban", "upsellio"); ?></a>
                  <a class="crm-tab-link <?php echo $pipeline_mode === "table" ? "is-active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "pipeline", "pipeline_mode" => "table"], home_url("/crm-app/"))); ?>"><?php esc_html_e("Tabela", "upsellio"); ?></a>
                  <a class="crm-tab-link <?php echo $pipeline_mode === "priorities" ? "is-active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "pipeline", "pipeline_mode" => "priorities"], home_url("/crm-app/"))); ?>"><?php esc_html_e("Priorytety", "upsellio"); ?></a>
                </div>
              </section>
              <?php if ($pipeline_mode === "kanban") : ?>
              <section class="card">
                <h2><?php esc_html_e("Lejek sprzedaży (przeciągnij i upuść)", "upsellio"); ?></h2>
                <div class="pipeline">
                  <?php
                  $pipeline_cols = [
                      "awareness" => "Świadomość",
                      "consideration" => "Rozważanie",
                      "decision" => "Decyzja",
                      "offer_sent" => "Oferta wysłana",
                      "won" => "Won",
                      "lost" => "Lost",
                  ];
                  foreach ($pipeline_cols as $pipeline_key => $pipeline_label) :
                      ?>
                    <div class="pipeline-col" data-pipeline-col="<?php echo esc_attr($pipeline_key); ?>">
                      <h3><?php echo esc_html($pipeline_label); ?></h3>
                      <div class="pipeline-drop">
                        <?php foreach ($offers as $offer) : ?>
                          <?php
                          $offer_id = (int) $offer->ID;
                          $offer_status = (string) get_post_meta($offer_id, "_ups_offer_status", true);
                          $offer_stage = (string) get_post_meta($offer_id, "_ups_offer_stage", true);
                          if ($offer_stage === "") {
                              $offer_stage = "awareness";
                          }
                          if ($pipeline_key === "offer_sent") {
                              if ($offer_status !== "sent") {
                                  continue;
                              }
                          } elseif ($offer_status === "won" || $offer_status === "lost") {
                              if ($offer_status !== $pipeline_key) {
                                  continue;
                              }
                          } else {
                              if ($offer_status === "sent") {
                                  continue;
                              }
                              if ($offer_stage !== $pipeline_key) {
                                  continue;
                              }
                          }
                          $deal_created_ts = strtotime((string) $offer->post_date_gmt);
                          $deal_age_days = ($deal_created_ts !== false && $deal_created_ts > 0) ? max(0, (int) floor((time() - $deal_created_ts) / DAY_IN_SECONDS)) : 0;
                          $stage_history = get_post_meta($offer_id, "_ups_offer_stage_history", true);
                          $stage_entered_ts = false;
                          if (is_array($stage_history) && !empty($stage_history)) {
                              $last_entry = end($stage_history);
                              if (is_array($last_entry) && !empty($last_entry["ts"])) {
                                  $stage_entered_ts = strtotime((string) $last_entry["ts"]);
                              }
                          }
                          if ($stage_entered_ts === false || $stage_entered_ts <= 0) {
                              $stage_entered_ts = strtotime((string) $offer->post_modified_gmt);
                          }
                          $stage_age_days = ($stage_entered_ts !== false && $stage_entered_ts > 0) ? max(0, (int) floor((time() - $stage_entered_ts) / DAY_IN_SECONDS)) : 0;
                          $deal_created_label = $deal_created_ts !== false && $deal_created_ts > 0 ? gmdate("Y-m-d", $deal_created_ts) : "brak daty";
                          $sla_stage = (string) get_post_meta($offer_id, "_ups_offer_pipeline_sla_stage", true);
                          $sla_entered = (int) get_post_meta($offer_id, "_ups_offer_pipeline_sla_entered_ts", true);
                          $sla_defs = function_exists("upsellio_automation_get_pipeline_sla_definitions") ? upsellio_automation_get_pipeline_sla_definitions() : [];
                          $sla_h = ($sla_stage !== "" && isset($sla_defs[$sla_stage])) ? (int) ($sla_defs[$sla_stage]["hours"] ?? 24) : 24;
                          $sla_elapsed_h = $sla_entered > 0 ? max(0, (int) floor((time() - $sla_entered) / HOUR_IN_SECONDS)) : 0;
                          $sla_alert = (string) get_post_meta($offer_id, "_ups_offer_sla_active_alert", true) === "1";
                          ?>
                          <article class="pipeline-card" draggable="true" data-offer-id="<?php echo esc_attr((string) $offer_id); ?>">
                            <strong>Deal #<?php echo esc_html((string) $offer_id); ?> - <?php echo esc_html((string) $offer->post_title); ?></strong>
                            <div class="muted">Status: <?php echo esc_html($offer_status !== "" ? $offer_status : "otwarty"); ?></div>
                            <div class="muted">Utworzony: <?php echo esc_html($deal_created_label); ?> (<?php echo esc_html((string) $deal_age_days); ?> dni)</div>
                            <div class="muted">W etapie: <?php echo esc_html((string) $stage_age_days); ?> dni</div>
                            <div class="muted" style="<?php echo $sla_alert ? "color:#b45309;font-weight:700" : ""; ?>">SLA: <?php echo esc_html($sla_stage !== "" ? $sla_stage : "—"); ?> — <?php echo esc_html((string) $sla_elapsed_h); ?>h / <?php echo esc_html((string) $sla_h); ?>h<?php echo $sla_alert ? " ⚠" : ""; ?></div>
                          </article>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </section>
              <script>
                (function () {
                  const cards = Array.from(document.querySelectorAll(".pipeline-card"));
                  const cols = Array.from(document.querySelectorAll("[data-pipeline-col]"));
                  let draggedCard = null;
                  cards.forEach((card) => {
                    card.addEventListener("dragstart", () => {
                      draggedCard = card;
                      card.classList.add("dragging");
                    });
                    card.addEventListener("dragend", () => {
                      card.classList.remove("dragging");
                      draggedCard = null;
                    });
                  });
                  cols.forEach((col) => {
                    col.addEventListener("dragover", (event) => {
                      event.preventDefault();
                      col.classList.add("is-over");
                    });
                    col.addEventListener("dragleave", () => {
                      col.classList.remove("is-over");
                    });
                    col.addEventListener("drop", async (event) => {
                      event.preventDefault();
                      col.classList.remove("is-over");
                      if (!draggedCard) return;
                      const dropzone = col.querySelector(".pipeline-drop");
                      if (!dropzone) return;
                      const stage = String(col.getAttribute("data-pipeline-col") || "");
                      let lossReason = "";
                      let lossNote = "";
                      if (stage === "lost") {
                        const modalRes =
                          typeof window.upsellioOpenLossModal === "function"
                            ? await window.upsellioOpenLossModal()
                            : null;
                        if (!modalRes || !modalRes.reason) {
                          return;
                        }
                        lossReason = modalRes.reason;
                        lossNote = modalRes.note || "";
                      }
                      const prevParent = draggedCard.parentElement;
                      dropzone.appendChild(draggedCard);
                      const payload = new URLSearchParams();
                      payload.append("action", "upsellio_crm_move_offer_pipeline");
                      payload.append("nonce", "<?php echo esc_js(wp_create_nonce("ups_crm_app_action")); ?>");
                      payload.append("offer_id", String(draggedCard.getAttribute("data-offer-id") || ""));
                      payload.append("stage", stage);
                      payload.append("loss_reason", lossReason);
                      payload.append("loss_reason_note", lossNote);
                      try {
                        const res = await fetch("<?php echo esc_url(admin_url("admin-ajax.php")); ?>", {
                          method: "POST",
                          headers: {"Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"},
                          body: payload.toString()
                        });
                        const json = await res.json();
                        if (!json || !json.success) {
                          const msg =
                            json && json.data && json.data.message ? String(json.data.message) : "Nie zapisano zmiany pipeline.";
                          alert(msg);
                          if (prevParent) {
                            prevParent.appendChild(draggedCard);
                          } else {
                            window.location.reload();
                          }
                        }
                      } catch (error) {
                        console.error("Pipeline update failed", error);
                        if (prevParent) {
                          prevParent.appendChild(draggedCard);
                        }
                      }
                    });
                  });
                })();
              </script>
              <?php elseif ($pipeline_mode === "table") : ?>
              <section class="card">
                <h2><?php esc_html_e("Pipeline — tabela", "upsellio"); ?></h2>
                <table>
                  <thead><tr><th><?php esc_html_e("Deal", "upsellio"); ?></th><th><?php esc_html_e("Klient", "upsellio"); ?></th><th><?php esc_html_e("Etap", "upsellio"); ?></th><th><?php esc_html_e("Status", "upsellio"); ?></th><th><?php esc_html_e("Wartość", "upsellio"); ?></th><th><?php esc_html_e("Akcja", "upsellio"); ?></th></tr></thead>
                  <tbody>
                    <?php foreach ($offers as $offer_pt) : ?>
                      <?php
                      $oid_t = (int) $offer_pt->ID;
                      $ost_t = (string) get_post_meta($oid_t, "_ups_offer_status", true);
                      $stage_t = (string) get_post_meta($oid_t, "_ups_offer_stage", true);
                      if ($stage_t === "") {
                          $stage_t = "awareness";
                      }
                      $cid_t = (int) get_post_meta($oid_t, "_ups_offer_client_id", true);
                      $cv_t = function_exists("upsellio_crm_app_offer_estimated_value_pln") ? upsellio_crm_app_offer_estimated_value_pln($oid_t) : 0.0;
                      ?>
                      <tr>
                        <td><?php echo esc_html((string) $offer_pt->post_title); ?> <small class="muted">#<?php echo esc_html((string) $oid_t); ?></small></td>
                        <td><?php echo $cid_t > 0 ? esc_html((string) get_the_title($cid_t)) : "—"; ?></td>
                        <td><?php echo esc_html($ost_t === "sent" ? __("oferta wysłana", "upsellio") : $stage_t); ?></td>
                        <td><?php echo esc_html($ost_t !== "" ? $ost_t : "open"); ?></td>
                        <td><?php echo esc_html(number_format($cv_t, 0, ",", " ")); ?> PLN</td>
                        <td><a class="btn alt" style="font-size:12px;padding:5px 10px" href="<?php echo esc_url(add_query_arg(["view" => "deals", "offer_editor_id" => $oid_t], home_url("/crm-app/"))); ?>"><?php esc_html_e("Otwórz", "upsellio"); ?></a></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </section>
              <?php else : ?>
              <section class="card">
                <h2><?php esc_html_e("Lista priorytetów (otwarte deale)", "upsellio"); ?></h2>
                <p class="muted" style="margin-bottom:10px"><?php esc_html_e("Posortowane wg ryzyka SLA i czasu bez ruchu.", "upsellio"); ?></p>
                <table>
                  <thead><tr><th><?php esc_html_e("Deal", "upsellio"); ?></th><th><?php esc_html_e("Priorytet", "upsellio"); ?></th><th><?php esc_html_e("SLA", "upsellio"); ?></th><th></th></tr></thead>
                  <tbody>
                    <?php
                    $prio_offers = [];
                    foreach ($offers as $po) {
                        $poid = (int) $po->ID;
                        $post = (string) get_post_meta($poid, "_ups_offer_status", true);
                        if (in_array($post, ["won", "lost"], true)) {
                            continue;
                        }
                        $sla_al = (string) get_post_meta($poid, "_ups_offer_sla_active_alert", true) === "1";
                        $modt = strtotime((string) $po->post_modified_gmt);
                        $idle = $modt !== false ? (time() - $modt) / DAY_IN_SECONDS : 0;
                        $score = ($sla_al ? 500 : 0) + min(300, (int) $idle);
                        $prio_offers[] = ["offer" => $po, "score" => $score, "sla_alert" => $sla_al];
                    }
                    usort($prio_offers, static function ($a, $b) {
                        return ($b["score"] ?? 0) <=> ($a["score"] ?? 0);
                    });
                    foreach (array_slice($prio_offers, 0, 40) as $prow) :
                        $po = $prow["offer"];
                        $poid = (int) $po->ID;
                        ?>
                      <tr>
                        <td><?php echo esc_html((string) $po->post_title); ?></td>
                        <td><?php echo esc_html((string) (int) ($prow["score"] ?? 0)); ?></td>
                        <td><?php echo !empty($prow["sla_alert"]) ? "<strong style=color:#b45309>" . esc_html__("⚠ Aktywny", "upsellio") . "</strong>" : esc_html__("OK", "upsellio"); ?></td>
                        <td><a class="btn alt" style="font-size:12px;padding:5px 10px" href="<?php echo esc_url(add_query_arg(["view" => "deals", "offer_editor_id" => $poid], home_url("/crm-app/"))); ?>"><?php esc_html_e("Otwórz", "upsellio"); ?></a></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </section>
              <?php endif; ?>
            <?php endif; ?>
            <?php if ($view === "contracts") : ?>
              <section class="card">
                <h2>Umowy</h2>
                <form method="post" class="grid2" style="margin:0 0 12px">
                  <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                  <input type="hidden" name="ups_action" value="save_contract" />
                  <input type="hidden" name="crm_view" value="contracts" />
                  <input type="text" name="contract_title" placeholder="Nazwa umowy" required />
                  <select name="contract_client_id">
                    <option value="">-- klient --</option>
                    <?php foreach ($clients as $client) : ?>
                      <option value="<?php echo esc_attr((string) $client->ID); ?>"><?php echo esc_html((string) $client->post_title); ?></option>
                    <?php endforeach; ?>
                  </select>
                  <select name="contract_offer_id">
                    <option value="">-- oferta --</option>
                    <?php foreach ($offers as $offer) : ?>
                      <option value="<?php echo esc_attr((string) $offer->ID); ?>"><?php echo esc_html((string) $offer->post_title); ?></option>
                    <?php endforeach; ?>
                  </select>
                  <select name="contract_layout_template_id">
                    <option value="">Szablon: globalny (legacy)</option>
                    <?php foreach ($contract_layout_templates as $cltpl) : ?>
                      <option value="<?php echo esc_attr((string) $cltpl->ID); ?>"><?php echo esc_html((string) $cltpl->post_title); ?></option>
                    <?php endforeach; ?>
                  </select>
                  <select name="contract_status"><option value="draft">wersja robocza</option><option value="sent">wysłana</option><option value="signed">podpisana</option><option value="cancelled">anulowana</option></select>
                  <label><input type="checkbox" name="contract_generate_from_template" value="1" checked /> Wygeneruj z szablonu</label>
                  <p class="muted" style="grid-column:1 / -1">Zmienne umowy: <code>{{client_name}}</code>, <code>{{offer_title}}</code>, <code>{{offer_price}}</code>, <code>{{offer_timeline}}</code>, <code>{{offer_url}}</code>, <code>{{contract_url}}</code>, <code>{{today}}</code>.</p>
                  <textarea name="contract_html" placeholder="HTML umowy"></textarea>
                  <textarea name="contract_css" placeholder="CSS umowy"></textarea>
                  <textarea name="contract_content" placeholder="Treść zapasowa"></textarea>
                  <button class="btn" type="submit">Dodaj umowę</button>
                </form>
                <table>
                  <thead><tr><th>Klient</th><th>Umowa</th><th>Status</th><th>Wersja</th><th>Link</th><th></th></tr></thead>
                  <tbody>
                  <?php foreach ($contracts as $contract) : ?>
                    <?php
                    $contract_id = (int) $contract->ID;
                    $contract_client_id = (int) get_post_meta($contract_id, "_ups_contract_client_id", true);
                    $contract_url = function_exists("upsellio_contracts_get_public_url") ? (string) upsellio_contracts_get_public_url($contract_id) : "";
                    ?>
                    <tr>
                      <td><?php echo esc_html($contract_client_id > 0 ? (string) get_the_title($contract_client_id) : "—"); ?></td>
                      <td><?php echo esc_html((string) $contract->post_title); ?></td>
                      <td><span class="badge warn"><?php echo esc_html($pl_label((string) get_post_meta($contract_id, "_ups_contract_status", true), "contract_status")); ?></span></td>
                      <td>v<?php echo esc_html((string) max(1, (int) get_post_meta($contract_id, "_ups_contract_version", true))); ?></td>
                      <td><?php if ($contract_url !== "") : ?><a class="btn alt" href="<?php echo esc_url($contract_url); ?>" target="_blank" rel="noopener noreferrer">Podgląd</a><?php endif; ?></td>
                      <td><a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "contract-detail", "contract_id" => $contract_id], home_url("/crm-app/"))); ?>">Szczegóły</a></td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </section>
            <?php endif; ?>
            <?php if ($view === "contract-detail") : ?>
              <section class="card">
                <?php if (!($selected_contract instanceof WP_Post)) : ?>
                  <h2>Brak umowy</h2>
                <?php else : ?>
                  <?php
                  $contract_id = (int) $selected_contract->ID;
                  $contract_timeline = function_exists("upsellio_contracts_get_timeline") ? upsellio_contracts_get_timeline($contract_id) : [];
                  $contract_status = (string) get_post_meta($contract_id, "_ups_contract_status", true);
                  $contract_client_id = (int) get_post_meta($contract_id, "_ups_contract_client_id", true);
                  $contract_offer_id = (int) get_post_meta($contract_id, "_ups_contract_offer_id", true);
                  ?>
                  <h2>Umowa: <?php echo esc_html((string) $selected_contract->post_title); ?></h2>
                  <form method="post" class="grid2">
                    <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                    <input type="hidden" name="ups_action" value="save_contract" />
                    <input type="hidden" name="crm_view" value="contract-detail" />
                    <input type="hidden" name="contract_id" value="<?php echo esc_attr((string) $contract_id); ?>" />
                    <input type="text" name="contract_title" value="<?php echo esc_attr((string) $selected_contract->post_title); ?>" required />
                    <select name="contract_status"><option value="draft" <?php selected($contract_status, "draft"); ?>>wersja robocza</option><option value="sent" <?php selected($contract_status, "sent"); ?>>wysłana</option><option value="signed" <?php selected($contract_status, "signed"); ?>>podpisana</option><option value="cancelled" <?php selected($contract_status, "cancelled"); ?>>anulowana</option></select>
                    <select name="contract_client_id"><option value="">-- klient --</option><?php foreach ($clients as $client_opt) : ?><option value="<?php echo esc_attr((string) $client_opt->ID); ?>" <?php selected($contract_client_id, (int) $client_opt->ID); ?>><?php echo esc_html((string) $client_opt->post_title); ?></option><?php endforeach; ?></select>
                    <select name="contract_offer_id"><option value="">-- oferta --</option><?php foreach ($offers as $offer_opt) : ?><option value="<?php echo esc_attr((string) $offer_opt->ID); ?>" <?php selected($contract_offer_id, (int) $offer_opt->ID); ?>><?php echo esc_html((string) $offer_opt->post_title); ?></option><?php endforeach; ?></select>
                    <label><input type="checkbox" name="contract_generate_from_template" value="1" /> Regeneruj z szablonu</label>
                    <p class="muted" style="grid-column:1 / -1">Zmienne umowy: <code>{{client_name}}</code>, <code>{{offer_title}}</code>, <code>{{offer_price}}</code>, <code>{{offer_timeline}}</code>, <code>{{offer_url}}</code>, <code>{{contract_url}}</code>, <code>{{today}}</code>.</p>
                    <textarea name="contract_html"><?php echo esc_textarea((string) get_post_meta($contract_id, "_ups_contract_html", true)); ?></textarea>
                    <textarea name="contract_css"><?php echo esc_textarea((string) get_post_meta($contract_id, "_ups_contract_css", true)); ?></textarea>
                    <textarea name="contract_content"><?php echo esc_textarea((string) $selected_contract->post_content); ?></textarea>
                    <button class="btn" type="submit">Zapisz umowę</button>
                  </form>
                  <h2 style="margin-top:20px">Timeline umowy</h2>
                  <?php if (!empty($contract_timeline)) : ?>
                    <?php foreach (array_reverse($contract_timeline) as $timeline_entry) : ?>
                      <div class="timeline-item">
                        <span class="muted"><?php echo esc_html((string) ($timeline_entry["ts"] ?? "")); ?></span>
                        <span><?php echo esc_html((string) ($timeline_entry["label"] ?? $timeline_entry["event"] ?? "event")); ?></span>
                      </div>
                    <?php endforeach; ?>
                  <?php else : ?>
                    <p class="muted">Brak zdarzeń.</p>
                  <?php endif; ?>
                <?php endif; ?>
              </section>
            <?php endif; ?>
            <?php if ($view === "followups") : ?>
              <section class="card">
                <h2>Follow-upy</h2>
                <form method="post" class="grid2" style="margin:0 0 12px">
                  <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                  <input type="hidden" name="ups_action" value="save_followup" />
                  <input type="hidden" name="crm_view" value="followups" />
                  <input type="text" name="template_title" placeholder="Nazwa follow-upu" required />
                  <input type="text" name="template_subject" placeholder="Temat maila" />
                  <select name="template_trigger"><?php foreach (["any","offer_view","offer_section_view","offer_engagement_tick","offer_cta_click","offer_hot_detected","inbound_positive","inbound_price_objection","inbound_timing_objection","inbound_no_priority"] as $event) : ?><option value="<?php echo esc_attr($event); ?>"><?php echo esc_html($event); ?></option><?php endforeach; ?></select>
                  <select name="template_stage"><option value="any">dowolny</option><option value="awareness">świadomość</option><option value="consideration">rozważanie</option><option value="decision">decyzja</option></select>
                  <input type="number" min="0" step="1" name="template_delay" placeholder="Opóźnienie (min)" />
                  <label><input type="checkbox" name="template_active" value="1" checked /> aktywny</label>
                  <textarea name="template_html" placeholder="HTML maila"></textarea>
                  <textarea name="template_css" placeholder="CSS maila"></textarea>
                  <textarea name="template_content" placeholder="Treść zapasowa"></textarea>
                  <button class="btn" type="submit">Dodaj follow-up</button>
                </form>
                <table>
                  <thead><tr><th>Nazwa</th><th>Wyzwalacz</th><th>Etap</th><th>Opóźnienie</th><th>Aktywny</th></tr></thead>
                  <tbody>
                    <?php foreach ($followups as $template) : $template_id = (int) $template->ID; ?>
                      <tr>
                        <td><?php echo esc_html((string) $template->post_title); ?></td>
                        <td><?php echo esc_html((string) get_post_meta($template_id, "_ups_followup_trigger_event", true)); ?></td>
                        <td><span class="badge gray"><?php echo esc_html((string) get_post_meta($template_id, "_ups_followup_stage", true)); ?></span></td>
                        <td><?php echo esc_html((string) get_post_meta($template_id, "_ups_followup_delay_minutes", true)); ?> min</td>
                        <td><?php echo (string) get_post_meta($template_id, "_ups_followup_active", true) === "1" ? "TAK" : "NIE"; ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </section>
            <?php endif; ?>
            <?php if ($view === "tasks") : ?>
              <?php
              $task_statuses = [
                  "open" => "Otwarte",
                  "in_progress" => "W toku",
                  "waiting" => "Oczekujące",
                  "done" => "Zakończone",
                  "cancelled" => "Anulowane",
              ];
              $open_tasks = 0;
              $done_tasks = 0;
              $overdue_tasks = 0;
              foreach ($tasks as $task) {
                  $tid = (int) $task->ID;
                  $task_status = (string) get_post_meta($tid, "_upsellio_task_status", true);
                  $due_at = (int) get_post_meta($tid, "_upsellio_task_due_at", true);
                  if (in_array($task_status, ["done", "cancelled"], true)) {
                      $done_tasks++;
                  } else {
                      $open_tasks++;
                      if ($due_at > 0 && $due_at < time()) {
                          $overdue_tasks++;
                      }
                  }
              }
              $today_start_ts = (int) strtotime(wp_date("Y-m-d 00:00:00"));
              $today_end_ts = $today_start_ts + DAY_IN_SECONDS - 1;
              $tomorrow_start_ts = $today_start_ts + DAY_IN_SECONDS;
              $tomorrow_end_ts = $tomorrow_start_ts + DAY_IN_SECONDS - 1;
              $tasks_filtered = [];
              foreach ($tasks as $task_tf) {
                  $tf_id = (int) $task_tf->ID;
                  $tf_st = (string) get_post_meta($tf_id, "_upsellio_task_status", true);
                  $tf_due = (int) get_post_meta($tf_id, "_upsellio_task_due_at", true);
                  if ($task_tab === "all") {
                      $tasks_filtered[] = $task_tf;
                      continue;
                  }
                  if (in_array($tf_st, ["done", "cancelled"], true)) {
                      continue;
                  }
                  if ($task_tab === "overdue") {
                      if ($tf_due > 0 && $tf_due < $today_start_ts) {
                          $tasks_filtered[] = $task_tf;
                      }
                      continue;
                  }
                  if ($task_tab === "today") {
                      if ($tf_due >= $today_start_ts && $tf_due <= $today_end_ts) {
                          $tasks_filtered[] = $task_tf;
                      }
                      continue;
                  }
                  if ($task_tab === "tomorrow") {
                      if ($tf_due >= $tomorrow_start_ts && $tf_due <= $tomorrow_end_ts) {
                          $tasks_filtered[] = $task_tf;
                      }
                  }
              }
              $task_tab_url = static function (string $tab) use ($task_cal_week_offset, $selected_task_id) {
                  $args = ["view" => "tasks", "task_tab" => $tab];
                  if ($tab === "week" && $task_cal_week_offset !== 0) {
                      $args["week_offset"] = $task_cal_week_offset;
                  }
                  if ($selected_task_id > 0) {
                      $args["task_id"] = $selected_task_id;
                  }

                  return esc_url(add_query_arg($args, home_url("/crm-app/")));
              };
              ?>
              <div class="crm-view-tabs" style="grid-column:span 12">
                <a class="crm-tab-link<?php echo $task_tab === "today" ? " is-active" : ""; ?>" href="<?php echo $task_tab_url("today"); ?>"><?php esc_html_e("Dziś", "upsellio"); ?></a>
                <a class="crm-tab-link<?php echo $task_tab === "tomorrow" ? " is-active" : ""; ?>" href="<?php echo $task_tab_url("tomorrow"); ?>"><?php esc_html_e("Jutro", "upsellio"); ?></a>
                <a class="crm-tab-link<?php echo $task_tab === "overdue" ? " is-active" : ""; ?>" href="<?php echo $task_tab_url("overdue"); ?>"><?php esc_html_e("Zaległe", "upsellio"); ?></a>
                <a class="crm-tab-link<?php echo $task_tab === "all" ? " is-active" : ""; ?>" href="<?php echo $task_tab_url("all"); ?>"><?php esc_html_e("Wszystkie", "upsellio"); ?></a>
                <a class="crm-tab-link<?php echo $task_tab === "week" ? " is-active" : ""; ?>" href="<?php echo $task_tab_url("week"); ?>"><?php esc_html_e("Tydzień (godziny)", "upsellio"); ?></a>
              </div>
              <section class="card kpi"><span class="muted">Otwarte taski</span><b><?php echo esc_html((string) $open_tasks); ?></b></section>
              <section class="card kpi"><span class="muted">Zamknięte taski</span><b><?php echo esc_html((string) $done_tasks); ?></b></section>
              <section class="card kpi"><span class="muted">Po terminie</span><b><?php echo esc_html((string) $overdue_tasks); ?></b></section>
              <section class="card kpi"><span class="muted">Wszystkie taski</span><b><?php echo esc_html((string) count($tasks)); ?></b></section>
              <section class="card">
                <h2>Dodaj task</h2>
                <form method="post" class="grid2" style="margin:0 0 12px">
                  <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                  <input type="hidden" name="ups_action" value="save_task" />
                  <input type="hidden" name="crm_view" value="tasks" />
                  <input type="hidden" name="task_tab" value="<?php echo esc_attr($task_tab); ?>" />
                  <?php if ($task_tab === "week") : ?>
                    <input type="hidden" name="week_offset" value="<?php echo esc_attr((string) (int) $task_cal_week_offset); ?>" />
                  <?php endif; ?>
                  <input type="text" name="task_title" placeholder="Nazwa taska" required />
                  <input type="datetime-local" name="task_due_at" />
                  <input type="number" min="15" step="15" name="task_duration_minutes" value="60" placeholder="Czas trwania (min)" />
                  <select name="task_offer_id">
                    <option value="0">Bez deala</option>
                    <?php foreach ($offers as $offer) : ?>
                      <option value="<?php echo esc_attr((string) $offer->ID); ?>"><?php echo esc_html((string) $offer->post_title); ?></option>
                    <?php endforeach; ?>
                  </select>
                  <select name="task_owner_id">
                    <?php foreach (get_users(["role__in" => ["administrator", "editor", "author"]]) as $u) : ?>
                      <option value="<?php echo esc_attr((string) $u->ID); ?>" <?php selected((int) $u->ID, get_current_user_id()); ?>><?php echo esc_html((string) $u->display_name); ?></option>
                    <?php endforeach; ?>
                  </select>
                  <textarea name="task_note" placeholder="Notatka do taska"></textarea>
                  <select name="task_status">
                    <?php foreach ($task_statuses as $task_status_key => $task_status_label) : ?>
                      <option value="<?php echo esc_attr($task_status_key); ?>"><?php echo esc_html($task_status_label); ?></option>
                    <?php endforeach; ?>
                  </select>
                  <label>Impact 1–100</label>
                  <input type="number" min="1" max="100" name="task_impact_score" value="50" />
                  <label>Prawdopodobieństwo domknięcia 1–100</label>
                  <input type="number" min="1" max="100" name="task_close_probability" value="50" />
                  <button class="btn" type="submit">Dodaj task</button>
                </form>
                <?php if ($selected_task instanceof WP_Post) : ?>
                  <?php
                  $selected_task_offer_id = (int) get_post_meta($selected_task->ID, "_upsellio_task_offer_id", true);
                  $selected_task_lead_id = (int) get_post_meta($selected_task->ID, "_upsellio_task_lead_id", true);
                  $selected_task_owner_id = (int) $selected_task->post_author;
                  $selected_task_status = (string) get_post_meta($selected_task->ID, "_upsellio_task_status", true);
                  if (!isset($task_statuses[$selected_task_status])) {
                      $selected_task_status = "open";
                  }
                  $selected_task_note = (string) get_post_meta($selected_task->ID, "_upsellio_task_note", true);
                  $selected_task_due_at_ts = (int) get_post_meta($selected_task->ID, "_upsellio_task_due_at", true);
                  $selected_task_due_at_value = $selected_task_due_at_ts > 0 ? wp_date("Y-m-d\\TH:i", $selected_task_due_at_ts) : "";
                  ?>
                  <h2 style="margin-top:14px">Podgląd i edycja taska #<?php echo esc_html((string) $selected_task->ID); ?></h2>
                  <form method="post" class="grid2" style="margin:0 0 14px">
                    <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                    <input type="hidden" name="ups_action" value="save_task" />
                    <input type="hidden" name="crm_view" value="tasks" />
                    <input type="hidden" name="task_tab" value="<?php echo esc_attr($task_tab); ?>" />
                    <?php if ($task_tab === "week") : ?>
                      <input type="hidden" name="week_offset" value="<?php echo esc_attr((string) (int) $task_cal_week_offset); ?>" />
                    <?php endif; ?>
                    <input type="hidden" name="task_id" value="<?php echo esc_attr((string) $selected_task->ID); ?>" />
                    <label>Tytuł taska</label>
                    <input type="text" name="task_title" value="<?php echo esc_attr((string) $selected_task->post_title); ?>" required />
                    <label>Termin</label>
                    <input type="datetime-local" name="task_due_at" value="<?php echo esc_attr($selected_task_due_at_value); ?>" />
                    <label>Deal</label>
                    <select name="task_offer_id">
                      <option value="0">Bez deala</option>
                      <?php foreach ($offers as $offer) : ?>
                        <option value="<?php echo esc_attr((string) $offer->ID); ?>" <?php selected((int) $offer->ID, $selected_task_offer_id); ?>><?php echo esc_html((string) $offer->post_title); ?></option>
                      <?php endforeach; ?>
                    </select>
                    <label>ID leada (opcjonalnie)</label>
                    <input type="number" min="0" name="task_lead_id" value="<?php echo esc_attr((string) $selected_task_lead_id); ?>" />
                    <label>Owner</label>
                    <select name="task_owner_id">
                      <?php foreach (get_users(["role__in" => ["administrator", "editor", "author"]]) as $u) : ?>
                        <option value="<?php echo esc_attr((string) $u->ID); ?>" <?php selected((int) $u->ID, $selected_task_owner_id); ?>><?php echo esc_html((string) $u->display_name); ?></option>
                      <?php endforeach; ?>
                    </select>
                    <label>Status</label>
                    <select name="task_status">
                      <?php foreach ($task_statuses as $task_status_key => $task_status_label) : ?>
                        <option value="<?php echo esc_attr($task_status_key); ?>" <?php selected($task_status_key, $selected_task_status); ?>><?php echo esc_html($task_status_label); ?></option>
                      <?php endforeach; ?>
                    </select>
                    <label>Notatka</label>
                    <textarea name="task_note"><?php echo esc_textarea($selected_task_note); ?></textarea>
                    <label>Czas trwania (min)</label>
                    <input type="number" min="15" step="15" name="task_duration_minutes" value="<?php echo esc_attr((string) max(15, (int) get_post_meta($selected_task->ID, "_upsellio_task_duration_minutes", true) ?: 60)); ?>" />
                    <label>Impact 1–100</label>
                    <input type="number" min="1" max="100" name="task_impact_score" value="<?php echo esc_attr((string) max(1, (int) get_post_meta($selected_task->ID, "_upsellio_task_impact_score", true) ?: 50)); ?>" />
                    <label>Prawdopodobieństwo domknięcia 1–100</label>
                    <input type="number" min="1" max="100" name="task_close_probability" value="<?php echo esc_attr((string) max(1, (int) get_post_meta($selected_task->ID, "_upsellio_task_close_probability", true) ?: 50)); ?>" />
                    <button class="btn" type="submit">Zapisz task</button>
                  </form>
                <?php endif; ?>
                <?php if ($task_tab !== "week") : ?>
                <table>
                  <thead><tr><th>Prio</th><th>Task</th><th>Deal</th><th>Kontekst oferty</th><th>Owner</th><th>Termin</th><th>Czas</th><th>Status</th><th>Akcje</th></tr></thead>
                  <tbody>
                    <?php foreach ($tasks_filtered as $task) : ?>
                      <?php
                      $tid = (int) $task->ID;
                      $task_offer_id = (int) get_post_meta($tid, "_upsellio_task_offer_id", true);
                      $task_due_at = (int) get_post_meta($tid, "_upsellio_task_due_at", true);
                      $task_status = (string) get_post_meta($tid, "_upsellio_task_status", true);
                      $task_duration = max(15, (int) get_post_meta($tid, "_upsellio_task_duration_minutes", true) ?: 60);
                      if (!isset($task_statuses[$task_status])) {
                          $task_status = "open";
                      }
                      $owner_name = get_the_author_meta("display_name", (int) $task->post_author);
                      $prio = (int) get_post_meta($tid, "_upsellio_task_priority_score", true);
                      $task_brief_html = $task_offer_id > 0 && function_exists("upsellio_sales_engine_format_offer_task_brief_html") ? upsellio_sales_engine_format_offer_task_brief_html($task_offer_id) : "";
                      ?>
                      <tr>
                        <td><strong><?php echo esc_html((string) ($prio > 0 ? $prio : "—")); ?></strong></td>
                        <td><?php echo esc_html((string) $task->post_title); ?></td>
                        <td><?php echo $task_offer_id > 0 ? esc_html((string) get_the_title($task_offer_id)) : "—"; ?></td>
                        <td><?php echo $task_brief_html !== "" ? $task_brief_html : '<span class="muted">—</span>'; ?></td>
                        <td><?php echo esc_html((string) $owner_name); ?></td>
                        <td><?php echo $task_due_at > 0 ? esc_html((string) wp_date("Y-m-d H:i", $task_due_at)) : "—"; ?></td>
                        <td><?php echo esc_html((string) $task_duration); ?> min</td>
                        <td><span class="badge"><?php echo esc_html((string) $task_statuses[$task_status]); ?></span></td>
                        <td style="white-space:nowrap">
                          <a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "tasks", "task_id" => $tid, "task_tab" => $task_tab], home_url("/crm-app/"))); ?>">Podgląd/Edytuj</a>
                          <form method="post" style="display:inline-flex;gap:6px">
                            <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                            <input type="hidden" name="ups_action" value="complete_task" />
                            <input type="hidden" name="crm_view" value="tasks" />
                            <input type="hidden" name="task_tab" value="<?php echo esc_attr($task_tab); ?>" />
                            <?php if ($task_tab === "week") : ?>
                              <input type="hidden" name="week_offset" value="<?php echo esc_attr((string) (int) $task_cal_week_offset); ?>" />
                            <?php endif; ?>
                            <input type="hidden" name="task_id" value="<?php echo esc_attr((string) $tid); ?>" />
                            <select name="task_status">
                              <?php foreach ($task_statuses as $task_status_key => $task_status_label) : ?>
                                <option value="<?php echo esc_attr($task_status_key); ?>" <?php selected($task_status_key, $task_status); ?>><?php echo esc_html($task_status_label); ?></option>
                              <?php endforeach; ?>
                            </select>
                            <button class="btn alt" type="submit">Zmień status</button>
                          </form>
                          <form method="post" style="display:inline-flex;gap:6px" onsubmit="return confirm('Usunąć task?');">
                            <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                            <input type="hidden" name="ups_action" value="delete_task" />
                            <input type="hidden" name="crm_view" value="tasks" />
                            <input type="hidden" name="task_tab" value="<?php echo esc_attr($task_tab); ?>" />
                            <?php if ($task_tab === "week") : ?>
                              <input type="hidden" name="week_offset" value="<?php echo esc_attr((string) (int) $task_cal_week_offset); ?>" />
                            <?php endif; ?>
                            <input type="hidden" name="task_id" value="<?php echo esc_attr((string) $tid); ?>" />
                            <button class="btn alt" type="submit">Usuń</button>
                          </form>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                    <?php if (empty($tasks_filtered)) : ?>
                      <tr><td colspan="9">Brak tasków w tym widoku.</td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
                <?php endif; ?>
              </section>
              <?php if ($task_tab === "week") : ?>
              <?php
              $tasks_week_calendar = [];
              $base_ts_w = current_time("timestamp") + ($task_cal_week_offset * WEEK_IN_SECONDS);
              $week_start_ts_w = strtotime("monday this week", $base_ts_w);
              if ($week_start_ts_w === false) {
                  $week_start_ts_w = $base_ts_w;
              }
              $week_days_w = [];
              for ($i_w = 0; $i_w < 7; $i_w++) {
                  $day_ts_w = strtotime("+{$i_w} day", $week_start_ts_w);
                  $week_days_w[] = [
                      "key" => wp_date("Y-m-d", $day_ts_w),
                      "label" => wp_date("D d.m", $day_ts_w),
                      "ts" => $day_ts_w,
                  ];
              }
              foreach ($tasks as $task_w) {
                  $tid_w = (int) $task_w->ID;
                  $due_w = (int) get_post_meta($tid_w, "_upsellio_task_due_at", true);
                  if ($due_w <= 0) {
                      continue;
                  }
                  $date_key_w = wp_date("Y-m-d", $due_w);
                  if (!isset($tasks_week_calendar[$date_key_w])) {
                      $tasks_week_calendar[$date_key_w] = [];
                  }
                  $tasks_week_calendar[$date_key_w][] = [
                      "id" => $tid_w,
                      "title" => (string) $task_w->post_title,
                      "status" => (string) get_post_meta($tid_w, "_upsellio_task_status", true),
                      "time" => wp_date("H:i", $due_w),
                      "hour" => (int) wp_date("G", $due_w),
                      "minute" => (int) wp_date("i", $due_w),
                      "duration" => max(15, (int) get_post_meta($tid_w, "_upsellio_task_duration_minutes", true) ?: 60),
                      "offer_id" => (int) get_post_meta($tid_w, "_upsellio_task_offer_id", true),
                  ];
              }
              ksort($tasks_week_calendar);
              ?>
              <section class="card">
                <h2><?php esc_html_e("Kalendarz tygodniowy (podział na godziny)", "upsellio"); ?></h2>
                <p class="muted" style="margin:0 0 10px;font-size:13px"><?php esc_html_e("Ten sam widok co w adresie /crm-app/?view=calendar — zadania z terminem w siatce 7:00–21:00. Przeciągnij kartę z backlogu na slot lub między slotami.", "upsellio"); ?></p>
                <p>
                  <a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "tasks", "task_tab" => "week", "week_offset" => $task_cal_week_offset - 1], home_url("/crm-app/"))); ?>">&larr; <?php esc_html_e("Poprzedni tydzień", "upsellio"); ?></a>
                  <a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "tasks", "task_tab" => "week", "week_offset" => 0], home_url("/crm-app/"))); ?>"><?php esc_html_e("Bieżący tydzień", "upsellio"); ?></a>
                  <a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "tasks", "task_tab" => "week", "week_offset" => $task_cal_week_offset + 1], home_url("/crm-app/"))); ?>"><?php esc_html_e("Następny tydzień", "upsellio"); ?> &rarr;</a>
                  <a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "calendar", "week_offset" => $task_cal_week_offset], home_url("/crm-app/"))); ?>"><?php esc_html_e("Pełny widok kalendarza (URL)", "upsellio"); ?></a>
                </p>
                <style>
                  .ups-cal-wrap-tasks{display:grid;grid-template-columns:160px 1fr;gap:12px}
                  .ups-cal-backlog-tasks{border:1px solid var(--border);border-radius:12px;background:#fff;padding:10px;max-height:720px;overflow:auto}
                  .ups-cal-grid-tasks{border:1px solid var(--border);border-radius:12px;background:#fff;overflow:auto}
                  .ups-cal-table-tasks{width:100%;border-collapse:collapse;table-layout:fixed}
                  .ups-cal-table-tasks th,.ups-cal-table-tasks td{border:1px solid var(--border);vertical-align:top;padding:6px}
                  .ups-cal-table-tasks th{background:#f4f4ef;font-size:12px}
                  .ups-cal-hour-tasks{width:64px;font-size:11px;color:var(--text-3);text-align:right}
                  .ups-cal-slot-tasks{min-height:48px;position:relative}
                  .ups-cal-slot-tasks.is-over{outline:2px dashed var(--teal)}
                  .ups-cal-task-tasks{background:#e6fffa;border:1px solid #99f6e4;border-radius:8px;padding:4px 6px;font-size:11px;cursor:grab;margin-bottom:4px}
                  .ups-cal-task-tasks small{display:block;color:#115e59}
                </style>
                <div class="ups-cal-wrap-tasks">
                  <aside class="ups-cal-backlog-tasks">
                    <h3><?php esc_html_e("Backlog (bez terminu godzinowego)", "upsellio"); ?></h3>
                    <?php foreach ($tasks as $task_b) : ?>
                      <?php
                      $tid_b = (int) $task_b->ID;
                      $due_b = (int) get_post_meta($tid_b, "_upsellio_task_due_at", true);
                      if ($due_b > 0) {
                          continue;
                      }
                      $duration_b = max(15, (int) get_post_meta($tid_b, "_upsellio_task_duration_minutes", true) ?: 60);
                      ?>
                      <article class="ups-cal-task-tasks" draggable="true" data-task-id="<?php echo esc_attr((string) $tid_b); ?>" data-duration="<?php echo esc_attr((string) $duration_b); ?>">
                        <?php echo esc_html((string) $task_b->post_title); ?>
                        <small><?php echo esc_html((string) $duration_b); ?> min</small>
                      </article>
                    <?php endforeach; ?>
                  </aside>
                  <div class="ups-cal-grid-tasks">
                    <table class="ups-cal-table-tasks">
                      <thead>
                        <tr>
                          <th class="ups-cal-hour-tasks"><?php esc_html_e("Godzina", "upsellio"); ?></th>
                          <?php foreach ($week_days_w as $day_w) : ?>
                            <th><?php echo esc_html((string) $day_w["label"]); ?></th>
                          <?php endforeach; ?>
                        </tr>
                      </thead>
                      <tbody>
                        <?php for ($hour_w = 7; $hour_w <= 21; $hour_w++) : ?>
                          <tr>
                            <td class="ups-cal-hour-tasks"><?php echo esc_html(sprintf("%02d:00", $hour_w)); ?></td>
                            <?php foreach ($week_days_w as $day_w) : ?>
                              <?php
                              $slot_ts_w = strtotime($day_w["key"] . " " . sprintf("%02d:00:00", $hour_w));
                              $slot_iso_w = wp_date("Y-m-d H:i:s", $slot_ts_w);
                              $items_w = isset($tasks_week_calendar[$day_w["key"]]) && is_array($tasks_week_calendar[$day_w["key"]]) ? $tasks_week_calendar[$day_w["key"]] : [];
                              ?>
                              <td class="ups-cal-slot-tasks" data-slot-datetime="<?php echo esc_attr($slot_iso_w); ?>">
                                <?php foreach ($items_w as $item_w) : ?>
                                  <?php
                                  if ((int) ($item_w["hour"] ?? -1) !== $hour_w) {
                                      continue;
                                  }
                                  ?>
                                  <article class="ups-cal-task-tasks" draggable="true" data-task-id="<?php echo esc_attr((string) $item_w["id"]); ?>" data-duration="<?php echo esc_attr((string) ($item_w["duration"] ?? 60)); ?>">
                                    <?php echo esc_html((string) $item_w["title"]); ?>
                                    <small><?php echo esc_html((string) ($item_w["time"] ?? "")); ?> • <?php echo esc_html((string) ($item_w["duration"] ?? 60)); ?> min</small>
                                  </article>
                                <?php endforeach; ?>
                              </td>
                            <?php endforeach; ?>
                          </tr>
                        <?php endfor; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
                <script>
                  (function () {
                    const slots = Array.from(document.querySelectorAll(".ups-cal-slot-tasks"));
                    const cards = Array.from(document.querySelectorAll(".ups-cal-task-tasks"));
                    let dragged = null;
                    cards.forEach((card) => {
                      card.addEventListener("dragstart", () => {
                        dragged = card;
                        card.classList.add("dragging");
                      });
                      card.addEventListener("dragend", () => {
                        card.classList.remove("dragging");
                      });
                    });
                    slots.forEach((slot) => {
                      slot.addEventListener("dragover", (event) => {
                        event.preventDefault();
                        slot.classList.add("is-over");
                      });
                      slot.addEventListener("dragleave", () => slot.classList.remove("is-over"));
                      slot.addEventListener("drop", async (event) => {
                        event.preventDefault();
                        slot.classList.remove("is-over");
                        if (!dragged) return;
                        const taskId = Number(dragged.getAttribute("data-task-id") || "0");
                        const durationMinutes = Number(dragged.getAttribute("data-duration") || "60");
                        const startAt = String(slot.getAttribute("data-slot-datetime") || "");
                        if (!taskId || !startAt) return;
                        const payload = new URLSearchParams();
                        payload.append("action", "upsellio_crm_schedule_task");
                        payload.append("nonce", "<?php echo esc_js(wp_create_nonce("ups_crm_app_action")); ?>");
                        payload.append("task_id", String(taskId));
                        payload.append("start_at", startAt);
                        payload.append("duration_minutes", String(durationMinutes));
                        const res = await fetch("<?php echo esc_url(admin_url("admin-ajax.php")); ?>", {
                          method: "POST",
                          credentials: "same-origin",
                          headers: {"Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"},
                          body: payload.toString()
                        });
                        if (!res.ok) return;
                        slot.appendChild(dragged);
                      });
                    });
                  })();
                </script>
              </section>
              <?php endif; ?>
            <?php endif; ?>
            <?php if ($view === "calendar") : ?>
              <?php
              $calendar_tasks = [];
              $week_offset = isset($_GET["week_offset"]) ? (int) wp_unslash($_GET["week_offset"]) : 0;
              $base_ts = current_time("timestamp") + ($week_offset * WEEK_IN_SECONDS);
              $week_start_ts = strtotime("monday this week", $base_ts);
              if ($week_start_ts === false) {
                  $week_start_ts = $base_ts;
              }
              $week_days = [];
              for ($i = 0; $i < 7; $i++) {
                  $day_ts = strtotime("+{$i} day", $week_start_ts);
                  $week_days[] = [
                      "key" => wp_date("Y-m-d", $day_ts),
                      "label" => wp_date("D d.m", $day_ts),
                      "ts" => $day_ts,
                  ];
              }
              foreach ($tasks as $task) {
                  $tid = (int) $task->ID;
                  $due_at = (int) get_post_meta($tid, "_upsellio_task_due_at", true);
                  if ($due_at <= 0) {
                      continue;
                  }
                  $date_key = wp_date("Y-m-d", $due_at);
                  if (!isset($calendar_tasks[$date_key])) {
                      $calendar_tasks[$date_key] = [];
                  }
                  $calendar_tasks[$date_key][] = [
                      "id" => $tid,
                      "title" => (string) $task->post_title,
                      "status" => (string) get_post_meta($tid, "_upsellio_task_status", true),
                      "time" => wp_date("H:i", $due_at),
                      "hour" => (int) wp_date("G", $due_at),
                      "minute" => (int) wp_date("i", $due_at),
                      "duration" => max(15, (int) get_post_meta($tid, "_upsellio_task_duration_minutes", true) ?: 60),
                      "offer_id" => (int) get_post_meta($tid, "_upsellio_task_offer_id", true),
                  ];
              }
              ksort($calendar_tasks);
              ?>
              <section class="card">
                <h2>Kalendarz tygodniowy (przeciągnij i upuść + oś czasu)</h2>
                <p>
                  <a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "calendar", "week_offset" => $week_offset - 1], home_url("/crm-app/"))); ?>">&larr; Poprzedni tydzień</a>
                  <a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "calendar", "week_offset" => 0], home_url("/crm-app/"))); ?>">Dziś</a>
                  <a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "calendar", "week_offset" => $week_offset + 1], home_url("/crm-app/"))); ?>">Następny tydzień &rarr;</a>
                </p>
                <style>
                  .ups-cal-wrap{display:grid;grid-template-columns:160px 1fr;gap:12px}
                  .ups-cal-backlog{border:1px solid var(--border);border-radius:12px;background:#fff;padding:10px;max-height:720px;overflow:auto}
                  .ups-cal-grid{border:1px solid var(--border);border-radius:12px;background:#fff;overflow:auto}
                  .ups-cal-table{width:100%;border-collapse:collapse;table-layout:fixed}
                  .ups-cal-table th,.ups-cal-table td{border:1px solid var(--border);vertical-align:top;padding:6px}
                  .ups-cal-table th{background:#f4f4ef;font-size:12px}
                  .ups-cal-hour{width:64px;font-size:11px;color:var(--text-3);text-align:right}
                  .ups-cal-slot{min-height:48px;position:relative}
                  .ups-cal-slot.is-over{outline:2px dashed var(--teal)}
                  .ups-cal-task{background:#e6fffa;border:1px solid #99f6e4;border-radius:8px;padding:4px 6px;font-size:11px;cursor:grab;margin-bottom:4px}
                  .ups-cal-task small{display:block;color:#115e59}
                </style>
                <div class="ups-cal-wrap">
                  <aside class="ups-cal-backlog">
                    <h3>Backlog (bez przypisanej godziny)</h3>
                    <?php foreach ($tasks as $task) : ?>
                      <?php
                      $tid = (int) $task->ID;
                      $due_at = (int) get_post_meta($tid, "_upsellio_task_due_at", true);
                      if ($due_at > 0) {
                          continue;
                      }
                      $duration = max(15, (int) get_post_meta($tid, "_upsellio_task_duration_minutes", true) ?: 60);
                      ?>
                      <article class="ups-cal-task" draggable="true" data-task-id="<?php echo esc_attr((string) $tid); ?>" data-duration="<?php echo esc_attr((string) $duration); ?>">
                        <?php echo esc_html((string) $task->post_title); ?>
                        <small><?php echo esc_html((string) $duration); ?> min</small>
                      </article>
                    <?php endforeach; ?>
                  </aside>
                  <div class="ups-cal-grid">
                    <table class="ups-cal-table">
                      <thead>
                        <tr>
                          <th class="ups-cal-hour">Godzina</th>
                          <?php foreach ($week_days as $day) : ?>
                            <th><?php echo esc_html((string) $day["label"]); ?></th>
                          <?php endforeach; ?>
                        </tr>
                      </thead>
                      <tbody>
                        <?php for ($hour = 7; $hour <= 21; $hour++) : ?>
                          <tr>
                            <td class="ups-cal-hour"><?php echo esc_html(sprintf("%02d:00", $hour)); ?></td>
                            <?php foreach ($week_days as $day) : ?>
                              <?php
                              $slot_ts = strtotime($day["key"] . " " . sprintf("%02d:00:00", $hour));
                              $slot_iso = wp_date("Y-m-d H:i:s", $slot_ts);
                              $items = isset($calendar_tasks[$day["key"]]) && is_array($calendar_tasks[$day["key"]]) ? $calendar_tasks[$day["key"]] : [];
                              ?>
                              <td class="ups-cal-slot" data-slot-datetime="<?php echo esc_attr($slot_iso); ?>">
                                <?php foreach ($items as $item) : ?>
                                  <?php
                                  if ((int) ($item["hour"] ?? -1) !== $hour) {
                                      continue;
                                  }
                                  ?>
                                  <article class="ups-cal-task" draggable="true" data-task-id="<?php echo esc_attr((string) $item["id"]); ?>" data-duration="<?php echo esc_attr((string) ($item["duration"] ?? 60)); ?>">
                                    <?php echo esc_html((string) $item["title"]); ?>
                                    <small><?php echo esc_html((string) ($item["time"] ?? "")); ?> • <?php echo esc_html((string) ($item["duration"] ?? 60)); ?> min</small>
                                  </article>
                                <?php endforeach; ?>
                              </td>
                            <?php endforeach; ?>
                          </tr>
                        <?php endfor; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
                <script>
                  (function () {
                    const slots = Array.from(document.querySelectorAll(".ups-cal-slot"));
                    const cards = Array.from(document.querySelectorAll(".ups-cal-task"));
                    let dragged = null;
                    cards.forEach((card) => {
                      card.addEventListener("dragstart", () => {
                        dragged = card;
                        card.classList.add("dragging");
                      });
                      card.addEventListener("dragend", () => {
                        card.classList.remove("dragging");
                      });
                    });
                    slots.forEach((slot) => {
                      slot.addEventListener("dragover", (event) => {
                        event.preventDefault();
                        slot.classList.add("is-over");
                      });
                      slot.addEventListener("dragleave", () => slot.classList.remove("is-over"));
                      slot.addEventListener("drop", async (event) => {
                        event.preventDefault();
                        slot.classList.remove("is-over");
                        if (!dragged) return;
                        const taskId = Number(dragged.getAttribute("data-task-id") || "0");
                        const durationMinutes = Number(dragged.getAttribute("data-duration") || "60");
                        const startAt = String(slot.getAttribute("data-slot-datetime") || "");
                        if (!taskId || !startAt) return;
                        const payload = new URLSearchParams();
                        payload.append("action", "upsellio_crm_schedule_task");
                        payload.append("nonce", "<?php echo esc_js(wp_create_nonce("ups_crm_app_action")); ?>");
                        payload.append("task_id", String(taskId));
                        payload.append("start_at", startAt);
                        payload.append("duration_minutes", String(durationMinutes));
                        const res = await fetch("<?php echo esc_url(admin_url("admin-ajax.php")); ?>", {
                          method: "POST",
                          credentials: "same-origin",
                          headers: {"Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"},
                          body: payload.toString()
                        });
                        if (!res.ok) return;
                        slot.appendChild(dragged);
                      });
                    });
                  })();
                </script>
              </section>
            <?php endif; ?>
            <?php if ($view === "prospecting") : ?>
              <section class="card">
                <h2>Prospecting zimnymi mailami</h2>
                <form method="post" class="grid2" style="margin:0 0 12px">
                  <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                  <input type="hidden" name="ups_action" value="save_prospect" />
                  <input type="hidden" name="crm_view" value="prospecting" />
                  <input type="text" name="prospect_title" placeholder="Nazwa prospekta / firma" required />
                  <input type="email" name="prospect_email" placeholder="Email" required />
                  <input type="text" name="prospect_name" placeholder="Imię osoby kontaktowej" />
                  <input type="text" name="prospect_company" placeholder="Firma" />
                  <select name="prospect_status">
                    <?php foreach (["active", "paused", "replied", "converted", "bounced"] as $ps) : ?>
                      <option value="<?php echo esc_attr($ps); ?>"><?php echo esc_html($ps === "active" ? "aktywny" : ($ps === "paused" ? "wstrzymany" : ($ps === "replied" ? "odpowiedział" : ($ps === "converted" ? "skonwertowany" : "odbity")))); ?></option>
                    <?php endforeach; ?>
                  </select>
                  <select name="prospect_stage">
                    <?php foreach (["awareness", "consideration", "decision"] as $pst) : ?>
                      <option value="<?php echo esc_attr($pst); ?>"><?php echo esc_html($pst === "awareness" ? "świadomość" : ($pst === "consideration" ? "rozważanie" : "decyzja")); ?></option>
                    <?php endforeach; ?>
                  </select>
                  <input type="number" min="1" max="5" step="1" name="prospect_step" value="1" />
                  <textarea name="prospect_notes" placeholder="Notatki prospecta"></textarea>
                  <label><input type="checkbox" name="prospect_send_now" value="1" /> Wyślij pierwszy krok od razu</label>
                  <button class="btn" type="submit">Dodaj prospecta</button>
                </form>
                <table>
                  <thead><tr><th>Prospekt</th><th>Email</th><th>Status</th><th>Etap</th><th>Krok</th><th>Następna wysyłka</th><th>Klasyfikacja odpowiedzi</th><th>Notatki</th><th>Akcja</th></tr></thead>
                  <tbody>
                    <?php foreach ($prospects as $prospect) : $pid = (int) $prospect->ID; ?>
                      <tr>
                        <td><?php echo esc_html((string) $prospect->post_title); ?></td>
                        <td><?php echo esc_html((string) get_post_meta($pid, "_ups_prospect_email", true)); ?></td>
                        <td><span class="badge gray"><?php echo esc_html($pl_label((string) get_post_meta($pid, "_ups_prospect_status", true), "prospect_status")); ?></span></td>
                        <td><span class="badge"><?php echo esc_html($pl_label((string) get_post_meta($pid, "_ups_prospect_stage", true), "stage")); ?></span></td>
                        <td><?php echo esc_html((string) get_post_meta($pid, "_ups_prospect_step", true)); ?>/5</td>
                        <td><?php echo esc_html((string) get_post_meta($pid, "_ups_prospect_next_at", true)); ?></td>
                        <td><?php echo esc_html((string) get_post_meta($pid, "_ups_prospect_reply_class", true)); ?></td>
                        <td><?php echo esc_html((string) get_post_meta($pid, "_ups_prospect_notes", true)); ?></td>
                        <td>
                          <form method="post">
                            <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                            <input type="hidden" name="ups_action" value="send_prospect_email_now" />
                            <input type="hidden" name="crm_view" value="prospecting" />
                            <input type="hidden" name="prospect_id" value="<?php echo esc_attr((string) $pid); ?>" />
                            <button class="btn alt" type="submit">Wyślij mail teraz</button>
                          </form>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
                <h2 style="margin-top:12px">Historia prospectingu</h2>
                <?php foreach ($prospects as $prospect) : $pid = (int) $prospect->ID; $p_log = get_post_meta($pid, "_ups_prospect_activity_log", true); if (!is_array($p_log) || empty($p_log)) { continue; } ?>
                  <h3 style="font-size:14px;margin-top:8px"><?php echo esc_html((string) $prospect->post_title); ?></h3>
                  <?php foreach (array_reverse(array_slice($p_log, -5)) as $entry) : ?>
                    <?php if (!is_array($entry)) { continue; } ?>
                    <div class="timeline-item">
                      <span class="muted"><?php echo esc_html((string) ($entry["ts"] ?? "")); ?></span>
                      <span><?php echo esc_html((string) ($entry["message"] ?? ($entry["event"] ?? "event"))); ?></span>
                    </div>
                  <?php endforeach; ?>
                <?php endforeach; ?>
              </section>
            <?php endif; ?>
            <?php if ($view === "inbox") : ?>
              <?php
              $inbox_offer_id = $crm_inbox_selected_offer_id;
              $inbox_thread = $inbox_offer_id > 0 ? (array) get_post_meta($inbox_offer_id, "_ups_offer_inbox_thread", true) : [];
              if (!is_array($inbox_thread)) {
                  $inbox_thread = [];
              }
              $inbox_client_id = $inbox_offer_id > 0 ? (int) get_post_meta($inbox_offer_id, "_ups_offer_client_id", true) : 0;
              $inbox_settings = function_exists("upsellio_followup_get_sender_settings") ? upsellio_followup_get_sender_settings() : [];
              $inbox_cls_colors = [
                  "positive" => "#1d9e75",
                  "price_objection" => "#f59e0b",
                  "timing_objection" => "#3b82f6",
                  "no_priority" => "#9ca3af",
                  "other" => "#a855f7",
              ];
              $inbox_cls_short = [
                  "positive" => "pozytywna",
                  "price_objection" => "cena",
                  "timing_objection" => "timing",
                  "no_priority" => "priorytet",
                  "other" => "inna",
              ];
              $inbox_nav_base = ["view" => "inbox"];
              if ($inbox_folder_sel !== "fld_inbox") {
                  $inbox_nav_base["inbox_folder"] = $inbox_folder_sel;
              }
              if ($inbox_bucket_sel !== "all") {
                  $inbox_nav_base["inbox_bucket"] = $inbox_bucket_sel;
              }
              if ($inbox_flag_sel !== "") {
                  $inbox_nav_base["inbox_flag"] = $inbox_flag_sel;
              }
              if ($inbox_search_q !== "") {
                  $inbox_nav_base["inbox_search"] = $inbox_search_q;
              }
              if ($inbox_paged > 1) {
                  $inbox_nav_base["inbox_paged"] = $inbox_paged;
              }
              if ($inbox_segment_sel !== "") {
                  $inbox_nav_base["inbox_segment"] = $inbox_segment_sel;
              }
              $inbox_list_q = $inbox_nav_base;
              unset($inbox_list_q["inbox_paged"]);
              $inbox_list_url = esc_url(add_query_arg($inbox_list_q, home_url("/crm-app/")));
              $inbox_compose_q = array_merge($inbox_nav_base, ["inbox_compose" => "1"]);
              unset($inbox_compose_q["inbox_paged"]);
              $inbox_compose_url = esc_url(add_query_arg($inbox_compose_q, home_url("/crm-app/")));
              $inbox_folder_defs = function_exists("upsellio_inbox_get_folder_defs") ? upsellio_inbox_get_folder_defs() : [];
              $inbox_flag_palette = function_exists("upsellio_inbox_flag_palette") ? upsellio_inbox_flag_palette() : [];
              $inbox_reply_prefill = ["to" => "", "cc" => ""];
              $inbox_reply_all_prefill = ["to" => "", "cc" => ""];
              if ($inbox_offer_id > 0 && function_exists("upsellio_inbox_reply_prefill")) {
                  $inbox_reply_all_prefill = upsellio_inbox_reply_prefill($inbox_offer_id);
                  $client_id_rp = (int) get_post_meta($inbox_offer_id, "_ups_offer_client_id", true);
                  $ce_rp = sanitize_email((string) get_post_meta($client_id_rp, "_ups_client_email", true));
                  $inbox_reply_prefill = ["to" => is_email($ce_rp) ? $ce_rp : "", "cc" => ""];
              }
              $inbox_current_flag = $inbox_offer_id > 0 && function_exists("upsellio_inbox_offer_flag") ? upsellio_inbox_offer_flag($inbox_offer_id) : "";
              $inbox_mailbox_enabled = function_exists("upsellio_followup_get_sender_settings") ? (bool) (upsellio_followup_get_sender_settings()["mailbox_enabled"] ?? false) : false;
              $inbox_mailbox_last_poll = (string) get_option("ups_followup_mailbox_last_poll_at", "");
              $inbox_mailbox_last_disp =
                  $inbox_mailbox_last_poll !== "" && strtotime($inbox_mailbox_last_poll)
                      ? wp_date("d.m.Y H:i", strtotime($inbox_mailbox_last_poll))
                      : "—";
              $inbox_settings_mail_url = esc_url(add_query_arg(["view" => "settings", "settings_tab" => "mailbox"], home_url("/crm-app/")));
              $inbox_kpis = function_exists("upsellio_inbox_aggregate_kpis")
                  ? upsellio_inbox_aggregate_kpis([
                      "folder" => $inbox_folder_sel,
                      "flag" => $inbox_flag_sel,
                      "bucket" => $inbox_bucket_sel,
                  ])
                  : [
                      "awaiting_reply" => 0,
                      "unlinked" => 0,
                      "lead_web" => 0,
                      "email_direct" => 0,
                      "open_pipeline" => 0,
                      "capped" => false,
                  ];
              $inbox_rail_all_q = array_merge($inbox_nav_base, []);
              unset($inbox_rail_all_q["inbox_offer"], $inbox_rail_all_q["inbox_paged"], $inbox_rail_all_q["inbox_segment"], $inbox_rail_all_q["inbox_bucket"]);
              $inbox_rail_all_url = esc_url(add_query_arg($inbox_rail_all_q, home_url("/crm-app/")));
              $inbox_rail_reply_q = array_merge($inbox_nav_base, ["inbox_segment" => "awaiting"]);
              unset($inbox_rail_reply_q["inbox_offer"], $inbox_rail_reply_q["inbox_paged"], $inbox_rail_reply_q["inbox_bucket"]);
              $inbox_rail_reply_url = esc_url(add_query_arg($inbox_rail_reply_q, home_url("/crm-app/")));
              $inbox_rail_unlinked_q = array_merge($inbox_nav_base, ["inbox_segment" => "unlinked"]);
              unset($inbox_rail_unlinked_q["inbox_offer"], $inbox_rail_unlinked_q["inbox_paged"], $inbox_rail_unlinked_q["inbox_bucket"]);
              $inbox_rail_unlinked_url = esc_url(add_query_arg($inbox_rail_unlinked_q, home_url("/crm-app/")));
              $inbox_rail_offer_q = array_merge($inbox_nav_base, ["inbox_segment" => "open_pipeline"]);
              unset($inbox_rail_offer_q["inbox_offer"], $inbox_rail_offer_q["inbox_paged"], $inbox_rail_offer_q["inbox_bucket"]);
              $inbox_rail_offer_url = esc_url(add_query_arg($inbox_rail_offer_q, home_url("/crm-app/")));
              $inbox_rail_sent_q = array_merge($inbox_nav_base, ["inbox_bucket" => "sent"]);
              unset($inbox_rail_sent_q["inbox_offer"], $inbox_rail_sent_q["inbox_paged"], $inbox_rail_sent_q["inbox_segment"]);
              $inbox_rail_sent_url = esc_url(add_query_arg($inbox_rail_sent_q, home_url("/crm-app/")));
              $inbox_rail_active_all = $inbox_segment_sel === "" && $inbox_bucket_sel === "all";
              $inbox_rail_active_reply = $inbox_segment_sel === "awaiting";
              $inbox_rail_active_unlinked = $inbox_segment_sel === "unlinked";
              $inbox_rail_active_offer = $inbox_segment_sel === "open_pipeline";
              $inbox_rail_active_sent = $inbox_bucket_sel === "sent";
              $inbox_kpi_seg_url = function ($seg) use ($inbox_nav_base) {
                  $q = array_merge($inbox_nav_base, []);
                  unset($q["inbox_offer"], $q["inbox_paged"]);
                  if ($seg === "") {
                      unset($q["inbox_segment"]);
                  } else {
                      $q["inbox_segment"] = $seg;
                  }
                  return esc_url(add_query_arg($q, home_url("/crm-app/")));
              };
              $inbox_kpi_url_awaiting = $inbox_kpi_seg_url("awaiting");
              $inbox_kpi_url_unlinked = $inbox_kpi_seg_url("unlinked");
              $inbox_kpi_url_lead_web = $inbox_kpi_seg_url("lead_web");
              $inbox_kpi_url_email_direct = $inbox_kpi_seg_url("email_direct");
              $inbox_kpi_url_open_pipeline = $inbox_kpi_seg_url("open_pipeline");
              $ups_ai_key_ok = trim((string) get_option("ups_anthropic_api_key", "")) !== ""
                  || (defined("UPSELLIO_ANTHROPIC_API_KEY") && (string) UPSELLIO_ANTHROPIC_API_KEY !== "");
              ?>
              <section class="card crm-inbox-card" style="grid-column:span 12;padding:0;overflow:hidden">
                <div class="crm-inbox-shell<?php echo ($inbox_offer_id > 0 && !$inbox_compose) ? " has-active-thread" : ""; ?>">
                <header class="crm-inbox-topbar">
                  <div class="crm-inbox-brand">
                    <span class="crm-inbox-logo" aria-hidden="true">U</span>
                    <div>
                      <h1 class="crm-inbox-title"><?php esc_html_e("Inbox CRM", "upsellio"); ?></h1>
                      <p class="crm-inbox-sub"><?php esc_html_e("Formularze, maile bezpośrednie i oferty w jednym ciągłym wątku klienta.", "upsellio"); ?></p>
                    </div>
                  </div>
                  <div class="crm-inbox-actions">
                    <form method="get" action="<?php echo esc_url(home_url("/crm-app/")); ?>" class="crm-inbox-search-form">
                      <input type="hidden" name="view" value="inbox" />
                      <?php if ($inbox_folder_sel !== "fld_inbox") : ?>
                        <input type="hidden" name="inbox_folder" value="<?php echo esc_attr($inbox_folder_sel); ?>" />
                      <?php endif; ?>
                      <?php if ($inbox_bucket_sel !== "all") : ?>
                        <input type="hidden" name="inbox_bucket" value="<?php echo esc_attr($inbox_bucket_sel); ?>" />
                      <?php endif; ?>
                      <?php if ($inbox_flag_sel !== "") : ?>
                        <input type="hidden" name="inbox_flag" value="<?php echo esc_attr($inbox_flag_sel); ?>" />
                      <?php endif; ?>
                      <?php if ($inbox_segment_sel !== "") : ?>
                        <input type="hidden" name="inbox_segment" value="<?php echo esc_attr($inbox_segment_sel); ?>" />
                      <?php endif; ?>
                      <input type="search" name="inbox_search" value="<?php echo esc_attr($inbox_search_q); ?>" placeholder="<?php esc_attr_e("Szukaj klienta, maila, tematu…", "upsellio"); ?>" maxlength="160" autocomplete="off" class="crm-inbox-search" />
                    </form>
                    <button type="button" class="btn alt crm-inbox-sync" id="inbox-sync-mailbox-btn" title="<?php esc_attr_e("Pobierz nieprzeczytane z IMAP (do 25).", "upsellio"); ?>" onclick="inboxSyncMailbox(this)"><?php esc_html_e("↻ Synchronizuj", "upsellio"); ?></button>
                    <a class="btn crm-inbox-new" href="<?php echo $inbox_compose_url; ?>"><?php esc_html_e("+ Nowa wiadomość", "upsellio"); ?></a>
                  </div>
                </header>

                <nav class="crm-inbox-kpi-row" role="navigation" aria-label="<?php esc_attr_e("Filtry KPI — kliknij, aby zawęzić listę wątków", "upsellio"); ?>">
                  <a href="<?php echo $inbox_kpi_url_awaiting; ?>" class="crm-inbox-metric danger<?php echo $inbox_segment_sel === "awaiting" ? " active" : ""; ?>">
                    <strong><?php echo (int) ($inbox_kpis["awaiting_reply"] ?? 0); ?></strong>
                    <span><?php esc_html_e("Do odpowiedzi", "upsellio"); ?></span>
                  </a>
                  <a href="<?php echo $inbox_kpi_url_unlinked; ?>" class="crm-inbox-metric warning<?php echo $inbox_segment_sel === "unlinked" ? " active" : ""; ?>">
                    <strong><?php echo (int) ($inbox_kpis["unlinked"] ?? 0); ?></strong>
                    <span><?php esc_html_e("Nieprzypięte", "upsellio"); ?></span>
                  </a>
                  <a href="<?php echo $inbox_kpi_url_lead_web; ?>" class="crm-inbox-metric<?php echo $inbox_segment_sel === "lead_web" ? " active" : ""; ?>">
                    <strong><?php echo (int) ($inbox_kpis["lead_web"] ?? 0); ?></strong>
                    <span><?php esc_html_e("Formularze", "upsellio"); ?></span>
                  </a>
                  <a href="<?php echo $inbox_kpi_url_email_direct; ?>" class="crm-inbox-metric<?php echo $inbox_segment_sel === "email_direct" ? " active" : ""; ?>">
                    <strong><?php echo (int) ($inbox_kpis["email_direct"] ?? 0); ?></strong>
                    <span><?php esc_html_e("Maile", "upsellio"); ?></span>
                  </a>
                  <a href="<?php echo $inbox_kpi_url_open_pipeline; ?>" class="crm-inbox-metric success<?php echo $inbox_segment_sel === "open_pipeline" ? " active" : ""; ?>">
                    <strong><?php echo (int) ($inbox_kpis["open_pipeline"] ?? 0); ?></strong>
                    <span><?php esc_html_e("Oferty w toku", "upsellio"); ?></span>
                  </a>
                </nav>

                <nav class="crm-inbox-rail" aria-label="<?php esc_attr_e("Skróty widoku inbox", "upsellio"); ?>">
                    <a href="<?php echo $inbox_rail_all_url; ?>"
                       class="crm-inbox-rail-btn<?php echo $inbox_rail_active_all ? " is-active" : ""; ?>"
                       aria-label="<?php echo esc_attr(sprintf(/* translators: %d thread count */ __("Wszystkie wątki (%d)", "upsellio"), (int) $inbox_list_total)); ?>"
                       aria-current="<?php echo $inbox_rail_active_all ? "page" : "false"; ?>">☰<?php if ($inbox_list_total > 0) : ?><span class="crm-inbox-rail-count" aria-hidden="true"><?php echo (int) $inbox_list_total; ?></span><?php endif; ?></a>
                    <a href="<?php echo $inbox_rail_reply_url; ?>"
                       class="crm-inbox-rail-btn<?php echo $inbox_rail_active_reply ? " is-active" : ""; ?>"
                       aria-label="<?php echo esc_attr(sprintf(/* translators: %d count */ __("Do odpowiedzi (%d)", "upsellio"), (int) ($inbox_kpis["awaiting_reply"] ?? 0))); ?>"
                       aria-current="<?php echo $inbox_rail_active_reply ? "page" : "false"; ?>">↩<?php if (($inbox_kpis["awaiting_reply"] ?? 0) > 0) : ?><span class="crm-inbox-rail-count" aria-hidden="true"><?php echo (int) $inbox_kpis["awaiting_reply"]; ?></span><?php endif; ?></a>
                    <a href="<?php echo $inbox_rail_unlinked_url; ?>"
                       class="crm-inbox-rail-btn<?php echo $inbox_rail_active_unlinked ? " is-active" : ""; ?>"
                       aria-label="<?php esc_attr_e("Nieprzypięte do CRM", "upsellio"); ?>"
                       aria-current="<?php echo $inbox_rail_active_unlinked ? "page" : "false"; ?>">⚠</a>
                    <a href="<?php echo $inbox_rail_offer_url; ?>"
                       class="crm-inbox-rail-btn<?php echo $inbox_rail_active_offer ? " is-active" : ""; ?>"
                       aria-label="<?php esc_attr_e("Oferty w toku", "upsellio"); ?>"
                       aria-current="<?php echo $inbox_rail_active_offer ? "page" : "false"; ?>">▣</a>
                    <a href="<?php echo $inbox_rail_sent_url; ?>"
                       class="crm-inbox-rail-btn<?php echo $inbox_rail_active_sent ? " is-active" : ""; ?>"
                       aria-label="<?php esc_attr_e("Wysłane (ostatnia wiadomość z CRM)", "upsellio"); ?>"
                       aria-current="<?php echo $inbox_rail_active_sent ? "page" : "false"; ?>">↑</a>
                  </nav>

                  <div class="crm-inbox-thread-column">
                    <div class="crm-inbox-column-head">
                      <h2><?php esc_html_e("Wątki", "upsellio"); ?></h2>
                      <p><?php esc_html_e("Najpierw odpowiadaj na rozmowy, które blokują sprzedaż.", "upsellio"); ?></p>
                      <p class="crm-inbox-column-meta muted" style="margin:8px 0 0;font-size:12px">
                        <?php echo (int) $inbox_list_total; ?> <?php esc_html_e("wątków", "upsellio"); ?><?php if ($inbox_total_pages > 1) : ?> · <?php esc_html_e("str.", "upsellio"); ?> <?php echo (int) $inbox_list_page; ?>/<?php echo (int) $inbox_total_pages; ?><?php endif; ?>
                        · <?php esc_html_e("na stronie:", "upsellio"); ?> <?php echo count($inbox_offers_visible); ?>
                      </p>
                      <?php if (!empty($inbox_kpis["capped"])) : ?>
                        <p class="muted" style="margin:8px 0 0;font-size:11px;line-height:1.4"><?php esc_html_e("KPI liczone z pierwszych 400 wątków w bieżącym folderze i filtrach.", "upsellio"); ?></p>
                      <?php endif; ?>
                      <?php if ($inbox_search_q !== "") : ?>
                        <p class="muted" style="margin:4px 0 0;font-size:11px;line-height:1.4"><?php esc_html_e("Licznik KPI dotyczy folderu i filtrów widoku listy (nie samej frazy wyszukiwania).", "upsellio"); ?></p>
                      <?php endif; ?>
                      <?php if ($inbox_compose) : ?>
                        <p style="margin:10px 0 0"><a class="btn alt" style="font-size:11px;padding:5px 10px" href="<?php echo $inbox_list_url; ?>"><?php esc_html_e("Lista wątków", "upsellio"); ?></a></p>
                      <?php endif; ?>
                    </div>
                    <?php if (!$inbox_mailbox_enabled) : ?>
                    <div class="crm-inbox-imap-line" style="background:#fffbeb;color:#92400e;border-color:#fde68a">
                      <span aria-hidden="true">⚠</span>
                      <span><?php esc_html_e("IMAP nie skonfigurowany — przychodzące maile nie będą pobierane.", "upsellio"); ?></span>
                      <a href="<?php echo esc_url($inbox_settings_mail_url); ?>" style="font-weight:700;color:#92400e"><?php esc_html_e("Konfiguruj", "upsellio"); ?> →</a>
                    </div>
                    <?php else : ?>
                    <div class="crm-inbox-imap-line">
                      <span><?php esc_html_e("Ostatnie IMAP:", "upsellio"); ?> <strong><?php echo esc_html($inbox_mailbox_last_disp); ?></strong></span>
                      <span id="inbox-sync-mailbox-status"></span>
                    </div>
                    <?php endif; ?>
                    <div class="crm-inbox-folder-section" id="inbox-folder-section">
                      <button type="button" class="crm-inbox-folder-toggle" id="inbox-folder-toggle" aria-expanded="false" aria-controls="inbox-folder-tree-panel">
                        <span><?php esc_html_e("Foldery poczty", "upsellio"); ?></span>
                        <span class="inbox-folder-chevron" aria-hidden="true">▾</span>
                      </button>
                      <div class="crm-inbox-folder-tree" id="inbox-folder-tree-panel">
                        <?php
                        $inbox_render_folder_branch = function ($parent_key, $depth) use (&$inbox_render_folder_branch, $inbox_folder_defs, $inbox_folder_sel, $inbox_nav_base) {
                            $rows = [];
                            foreach ($inbox_folder_defs as $fd) {
                                if (!is_array($fd)) {
                                    continue;
                                }
                                $p = (string) ($fd["parent"] ?? "");
                                if ($p !== $parent_key) {
                                    continue;
                                }
                                $rows[] = $fd;
                            }
                            usort($rows, function ($a, $b) {
                                return strcmp((string) ($a["name"] ?? ""), (string) ($b["name"] ?? ""));
                            });
                            foreach ($rows as $fd) {
                                $fid = (string) ($fd["id"] ?? "");
                                $nm = (string) ($fd["name"] ?? "");
                                if ($fid === "") {
                                    continue;
                                }
                                $q = array_merge($inbox_nav_base, ["inbox_folder" => $fid]);
                                unset($q["inbox_offer"], $q["inbox_paged"]);
                                $url = esc_url(add_query_arg($q, home_url("/crm-app/")));
                                $active = $inbox_folder_sel === $fid;
                                $pad = 8 + $depth * 10;
                                ?>
                        <a href="<?php echo $url; ?>"
                           class="inbox-folder-drop<?php echo $active ? " is-active" : ""; ?>"
                           data-folder-id="<?php echo esc_attr($fid); ?>"
                           style="padding-left:<?php echo (int) $pad; ?>px">
                          <?php echo esc_html($nm); ?>
                        </a>
                                <?php
                                $inbox_render_folder_branch($fid, $depth + 1);
                            }
                        };
                        $inbox_render_folder_branch("", 0);
                        ?>
                        <div style="padding:8px 10px;border-top:1px solid var(--border)">
                          <input type="text" id="inbox-new-folder-name" placeholder="<?php esc_attr_e("Nowy podfolder…", "upsellio"); ?>" style="width:100%;font-size:11px;padding:5px 7px;border:1px solid var(--border);border-radius:6px;background:var(--bg);margin-bottom:6px" />
                          <?php
                          $inbox_sel_folder_meta = function_exists("upsellio_inbox_folder_find") ? upsellio_inbox_folder_find($inbox_folder_sel) : null;
                          $inbox_sel_folder_name = is_array($inbox_sel_folder_meta) ? (string) ($inbox_sel_folder_meta["name"] ?? "") : "";
                          if ($inbox_sel_folder_name === "") {
                              $inbox_sel_folder_name = __("Główny", "upsellio");
                          }
                          ?>
                          <button type="button" class="btn alt" id="inbox-folder-create-btn" data-parent="<?php echo esc_attr($inbox_folder_sel); ?>" style="font-size:11px;padding:4px 8px;width:100%" onclick="inboxFolderManage('create')"><?php echo esc_html(sprintf(/* translators: folder name */ __("Utwórz w „%s”", "upsellio"), $inbox_sel_folder_name)); ?></button>
                        </div>
                      </div>
                    </div>
                    <div class="crm-inbox-filters" role="tablist" aria-label="<?php esc_attr_e("Filtry wątków", "upsellio"); ?>">
                      <?php
                      $inbox_filter_tabs = [
                          "" => __("Wszystkie", "upsellio"),
                          "awaiting" => __("Do odpowiedzi", "upsellio"),
                          "unlinked" => __("Nieprzypięte", "upsellio"),
                          "email_direct" => __("Maile", "upsellio"),
                          "lead_web" => __("Formularze", "upsellio"),
                      ];
                      foreach ($inbox_filter_tabs as $seg_key => $seg_label) :
                          $q_seg = $inbox_nav_base;
                          unset($q_seg["inbox_offer"], $q_seg["inbox_paged"]);
                          if ($seg_key === "") {
                              unset($q_seg["inbox_segment"]);
                          } else {
                              $q_seg["inbox_segment"] = $seg_key;
                          }
                          $u_seg = esc_url(add_query_arg($q_seg, home_url("/crm-app/")));
                          $seg_active = ($inbox_segment_sel === $seg_key);
                          ?>
                      <a class="crm-inbox-filter<?php echo $seg_active ? " is-active" : ""; ?>" href="<?php echo $u_seg; ?>" role="tab" aria-selected="<?php echo $seg_active ? "true" : "false"; ?>"><?php echo esc_html($seg_label); ?></a>
                      <?php endforeach; ?>
                    </div>
                    <div class="crm-inbox-flags">
                      <span style="font-size:10px;color:var(--text-3);width:100%;flex-basis:100%"><?php esc_html_e("Flagi", "upsellio"); ?></span>
                      <?php
                      $q_all = $inbox_nav_base;
                      unset($q_all["inbox_flag"], $q_all["inbox_offer"], $q_all["inbox_paged"]);
                      $u_all = esc_url(add_query_arg($q_all, home_url("/crm-app/")));
                      ?>
                      <a href="<?php echo $u_all; ?>" style="font-size:11px;padding:2px 8px;border-radius:999px;text-decoration:none;border:1px solid var(--border);color:var(--text-2);<?php echo $inbox_flag_sel === "" ? "background:rgba(13,148,136,.12)" : ""; ?>"><?php esc_html_e("Wszystkie", "upsellio"); ?></a>
                      <?php foreach ($inbox_flag_palette as $fk => $meta) :
                          $qfl = array_merge($inbox_nav_base, ["inbox_flag" => $fk]);
                          unset($qfl["inbox_offer"], $qfl["inbox_paged"]);
                          $ufl = esc_url(add_query_arg($qfl, home_url("/crm-app/")));
                          $hx = (string) ($meta["hex"] ?? "#999");
                          $active_fl = $inbox_flag_sel === $fk;
                          ?>
                      <a href="<?php echo $ufl; ?>" title="<?php echo esc_attr((string) ($meta["label"] ?? $fk)); ?>" style="display:inline-flex;width:22px;height:22px;border-radius:50%;background:<?php echo esc_attr($hx); ?>;border:2px solid <?php echo $active_fl ? "#0f766e" : "transparent"; ?>;box-shadow:0 0 0 1px var(--border);text-decoration:none"></a>
                      <?php endforeach; ?>
                    </div>
                    <div class="crm-inbox-threads">
                    <?php foreach ($inbox_offers_visible as $io) :
                        $ioid = (int) $io->ID;
                        $sum = function_exists("upsellio_inbox_get_thread_summary") ? upsellio_inbox_get_thread_summary($ioid) : [];
                        $icid = (int) get_post_meta($ioid, "_ups_offer_client_id", true);
                        $iname = $icid > 0 ? get_the_title($icid) : "—";
                        $iclient_email = $icid > 0 ? sanitize_email((string) get_post_meta($icid, "_ups_client_email", true)) : "";
                        $is_active = $ioid === $inbox_offer_id;
                        $has_unread = ((int) ($sum["unread"] ?? 0)) > 0;
                        $last_cls = (string) ($sum["last_cls"] ?? "");
                        $cls_color = $last_cls !== "" && isset($inbox_cls_colors[$last_cls]) ? $inbox_cls_colors[$last_cls] : "";
                        $last_ts_raw = (string) ($sum["last_ts"] ?? "");
                        $last_ts_disp = $last_ts_raw !== "" && strtotime($last_ts_raw) ? esc_html(wp_date("d.m H:i", strtotime($last_ts_raw))) : "—";
                        $awaiting = ($sum["last_direction"] ?? "") === "in";
                        $utm_src = trim((string) get_post_meta($ioid, "_ups_offer_utm_source", true));
                        $is_form_lead = $utm_src !== "";
                        $offer_stage_raw = (string) get_post_meta($ioid, "_ups_offer_stage", true);
                        $badge_offer = in_array($offer_stage_raw, ["consideration", "decision"], true);
                        $thread_flag = function_exists("upsellio_inbox_offer_flag") ? upsellio_inbox_offer_flag($ioid) : "";
                        $thread_flag_hex =
                            $thread_flag !== "" && isset($inbox_flag_palette[$thread_flag])
                                ? (string) ($inbox_flag_palette[$thread_flag]["hex"] ?? "")
                                : "";
                        $q_th = array_merge($inbox_nav_base, ["inbox_offer" => $ioid]);
                        unset($q_th["inbox_paged"]);
                        $href_th = esc_url(add_query_arg($q_th, home_url("/crm-app/")));
                        $snippet_title = get_the_title($ioid);
                        ?>
                      <div class="crm-inbox-thread-row">
                        <span class="inbox-drag-handle" draggable="true" data-offer-id="<?php echo (int) $ioid; ?>" title="<?php esc_attr_e("Przenieś do folderu", "upsellio"); ?>">⠿</span>
                        <a href="<?php echo $href_th; ?>"
                           class="crm-inbox-thread-card<?php echo $is_active ? " is-active" : ""; ?><?php echo $has_unread ? " is-unread" : ""; ?>"
                           data-offer-id="<?php echo (int) $ioid; ?>"
                           aria-label="<?php echo esc_attr(sprintf(/* translators: %s client name */ __("Wątek: %s", "upsellio"), $iname)); ?>">
                          <div class="crm-inbox-thread-top">
                            <div>
                              <div class="crm-inbox-thread-name"><?php echo esc_html($iname); ?></div>
                              <div class="crm-inbox-thread-email"><?php echo $iclient_email !== "" ? esc_html($iclient_email) : "—"; ?></div>
                            </div>
                            <div class="crm-inbox-thread-time"><?php echo $last_ts_disp; ?></div>
                          </div>
                          <div class="crm-inbox-thread-badges">
                            <span class="crm-inbox-badge <?php echo $is_form_lead ? "crm-inbox-badge--form" : "crm-inbox-badge--mail"; ?>"><?php echo $is_form_lead ? esc_html__("Formularz", "upsellio") : esc_html__("Mail", "upsellio"); ?></span>
                            <?php if ($awaiting) : ?>
                              <span class="crm-inbox-badge crm-inbox-badge--wait"><?php esc_html_e("Do odp.", "upsellio"); ?></span>
                            <?php endif; ?>
                            <?php if ($icid > 0) : ?>
                              <span class="crm-inbox-badge crm-inbox-badge--link"><?php esc_html_e("CRM", "upsellio"); ?></span>
                            <?php else : ?>
                              <span class="crm-inbox-badge crm-inbox-badge--unlink"><?php esc_html_e("Nieprzypięty", "upsellio"); ?></span>
                            <?php endif; ?>
                            <?php if ($badge_offer) : ?>
                              <span class="crm-inbox-badge crm-inbox-badge--offer"><?php esc_html_e("Oferta", "upsellio"); ?></span>
                            <?php endif; ?>
                            <?php if ($thread_flag_hex !== "") : ?>
                              <span class="crm-inbox-badge crm-inbox-badge--flagdot" style="background:<?php echo esc_attr($thread_flag_hex); ?>;color:#fff;border:0" title="<?php esc_attr_e("Flaga wątku", "upsellio"); ?>"></span>
                            <?php endif; ?>
                          </div>
                          <p class="crm-inbox-thread-snippet">
                            <strong><?php echo esc_html($snippet_title); ?></strong><br />
                            <?php echo esc_html((string) ($sum["last_direction"] ?? "") === "out" ? "↑ " : "↓ "); ?><?php echo esc_html((string) ($sum["last_body"] ?? "—")); ?>
                          </p>
                          <?php if ($has_unread || $cls_color !== "") : ?>
                          <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-top:6px">
                            <?php if ($has_unread) : ?>
                              <span class="crm-inbox-unread-pill"><?php echo (int) $sum["unread"]; ?></span>
                            <?php endif; ?>
                            <?php if ($cls_color !== "") : ?>
                              <span style="width:7px;height:7px;border-radius:50%;background:<?php echo esc_attr($cls_color); ?>;flex-shrink:0"></span>
                              <span style="font-size:11px;color:<?php echo esc_attr($cls_color); ?>"><?php echo esc_html($inbox_cls_short[$last_cls] ?? $last_cls); ?></span>
                            <?php endif; ?>
                          </div>
                          <?php endif; ?>
                        </a>
                      </div>
                    <?php endforeach; ?>
                    </div>
                    <?php if (empty($inbox_offers_visible)) : ?>
                      <?php
                      $inbox_empty_clear = ["view" => "inbox"];
                      if ($inbox_folder_sel !== "fld_inbox") {
                          $inbox_empty_clear["inbox_folder"] = $inbox_folder_sel;
                      }
                      if ($inbox_bucket_sel !== "all") {
                          $inbox_empty_clear["inbox_bucket"] = $inbox_bucket_sel;
                      }
                      if ($inbox_flag_sel !== "") {
                          $inbox_empty_clear["inbox_flag"] = $inbox_flag_sel;
                      }
                      if ($inbox_segment_sel !== "") {
                          $inbox_empty_clear["inbox_segment"] = $inbox_segment_sel;
                      }
                      ?>
                      <div class="crm-inbox-empty">
                        <?php if ($inbox_search_q !== "") : ?>
                          <div style="text-align:center">
                            <div style="font-size:32px;margin-bottom:12px" aria-hidden="true">⌕</div>
                            <div style="font-weight:700;margin-bottom:6px"><?php echo esc_html(sprintf(/* translators: %s search query */ __("Brak wyników dla „%s”", "upsellio"), $inbox_search_q)); ?></div>
                            <div style="font-size:12px;color:var(--text-3);margin-bottom:14px"><?php esc_html_e("Spróbuj innej frazy lub wyczyść filtr.", "upsellio"); ?></div>
                            <a class="btn alt" href="<?php echo esc_url(add_query_arg($inbox_empty_clear, home_url("/crm-app/"))); ?>"><?php esc_html_e("Wyczyść wyszukiwanie", "upsellio"); ?></a>
                          </div>
                        <?php elseif ($inbox_segment_sel === "awaiting") : ?>
                          <div style="text-align:center">
                            <div style="font-size:32px;margin-bottom:12px" aria-hidden="true">✓</div>
                            <div style="font-weight:700;margin-bottom:6px"><?php esc_html_e("Wszystko odpowiedziane", "upsellio"); ?></div>
                            <div style="font-size:12px;color:var(--text-3)"><?php esc_html_e("Brak wątków czekających na odpowiedź.", "upsellio"); ?></div>
                          </div>
                        <?php elseif ($inbox_list_total === 0) : ?>
                          <div style="text-align:center">
                            <div style="font-size:32px;margin-bottom:12px" aria-hidden="true">✉</div>
                            <div style="font-weight:700;margin-bottom:6px"><?php esc_html_e("Inbox pusty", "upsellio"); ?></div>
                            <div style="font-size:12px;color:var(--text-3);margin-bottom:14px"><?php esc_html_e("Wyślij pierwszą wiadomość lub skonfiguruj skrzynkę IMAP.", "upsellio"); ?></div>
                            <a class="btn" href="<?php echo esc_url($inbox_compose_url); ?>"><?php esc_html_e("+ Nowa wiadomość", "upsellio"); ?></a>
                          </div>
                        <?php else : ?>
                          <div style="text-align:center;font-size:12px"><?php echo esc_html(sprintf(/* translators: %d total threads */ __("Brak pozycji na tej stronie (łącznie %d wątków). Skorzystaj z paginacji poniżej.", "upsellio"), (int) $inbox_list_total)); ?></div>
                        <?php endif; ?>
                      </div>
                    <?php endif; ?>
                    <?php if ($inbox_total_pages > 1) : ?>
                      <div class="crm-inbox-pagination">
                        <?php
                        $inbox_pg_url = static function (int $p) use ($inbox_nav_base) {
                            $q = array_merge($inbox_nav_base, ["inbox_paged" => $p]);
                            if ($p <= 1) {
                                unset($q["inbox_paged"]);
                            }
                            return esc_url(add_query_arg($q, home_url("/crm-app/")));
                        };
                        ?>
                        <?php if ($inbox_list_page > 1) : ?>
                          <a class="btn alt" style="font-size:11px;padding:5px 12px" href="<?php echo $inbox_pg_url($inbox_list_page - 1); ?>">← Poprzednia</a>
                        <?php endif; ?>
                        <span class="muted"><?php echo (int) $inbox_list_page; ?> / <?php echo (int) $inbox_total_pages; ?></span>
                        <?php if ($inbox_list_page < $inbox_total_pages) : ?>
                          <a class="btn alt" style="font-size:11px;padding:5px 12px" href="<?php echo $inbox_pg_url($inbox_list_page + 1); ?>">Następna →</a>
                        <?php endif; ?>
                      </div>
                    <?php endif; ?>
                  </div>

                  <div class="crm-inbox-detail">

                    <?php if ($inbox_compose) : ?>
                      <div id="inbox-compose-panel" data-active="1" style="display:flex;flex-direction:column;flex:1 1 0;min-height:0;overflow:hidden">
                        <div style="padding:12px 20px;border-bottom:1px solid var(--border);flex-shrink:0">
                          <div style="font-size:14px;font-weight:700">Nowa wiadomość</div>
                          <div class="muted" style="font-size:12px;margin-top:4px;line-height:1.45">Zwykły e-mail z CRM: uzupełnij <strong>Do</strong>, temat i treść. Wątek na liście inboxu tworzy się przy odpowiedziach powiązanych z ofertą — ta wiadomość nie wymaga wyboru deala.</div>
                        </div>
                        <div style="padding:20px;overflow-y:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch;flex:1 1 0;min-height:0">
                          <label style="display:block;font-size:12px;font-weight:600;margin-bottom:6px;color:var(--text-2)">Do</label>
                          <input type="text" id="inbox-compose-to" placeholder="email@…" style="width:100%;margin-bottom:10px;font-size:13px;padding:8px 10px;border:1px solid var(--border);border-radius:8px;background:var(--bg);font:inherit;color:var(--text)" />
                          <label style="display:block;font-size:12px;font-weight:600;margin-bottom:6px;color:var(--text-2)">Dw (opcjonalnie)</label>
                          <input type="text" id="inbox-compose-cc" placeholder="oddziel przecinkami" style="width:100%;margin-bottom:10px;font-size:13px;padding:8px 10px;border:1px solid var(--border);border-radius:8px;background:var(--bg);font:inherit;color:var(--text)" />
                          <label style="display:block;font-size:12px;font-weight:600;margin-bottom:6px;color:var(--text-2)">Udw (opcjonalnie)</label>
                          <input type="text" id="inbox-compose-bcc" placeholder="oddziel przecinkami" style="width:100%;margin-bottom:12px;font-size:13px;padding:8px 10px;border:1px solid var(--border);border-radius:8px;background:var(--bg);font:inherit;color:var(--text)" />
                          <label style="display:block;font-size:12px;font-weight:600;margin-bottom:6px;color:var(--text-2)">Temat</label>
                          <input type="text" id="inbox-compose-subject" placeholder="Temat wiadomości" style="width:100%;margin-bottom:12px;font-size:13px;padding:8px 10px;border:1px solid var(--border);border-radius:8px;background:var(--bg);font:inherit;color:var(--text)" />
                          <label style="display:flex;align-items:center;gap:8px;margin-bottom:10px;font-size:13px;cursor:pointer">
                            <input type="checkbox" id="inbox-compose-html" style="margin:0" />
                            <span>Treść jako HTML (np. wklejony szablon)</span>
                          </label>
                          <label style="display:block;font-size:12px;font-weight:600;margin-bottom:6px;color:var(--text-2)">Treść</label>
                          <textarea id="inbox-compose-body" rows="6" placeholder="Treść wiadomości…" style="width:100%;max-height:min(40vh,320px);resize:vertical;font-size:14px;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--bg);font-family:inherit;color:var(--text)"></textarea>
                          <label style="display:flex;align-items:flex-start;gap:8px;margin-top:12px;font-size:13px;cursor:pointer;line-height:1.35">
                            <input type="checkbox" id="inbox-compose-use-footer" checked style="margin-top:3px;flex-shrink:0" />
                            <span>Dołącz domyślną stopkę (HTML + CSS z <strong>Ustawienia → Mail / Skrzynki</strong>)</span>
                          </label>
                          <label style="display:block;font-size:12px;font-weight:600;margin-bottom:6px;margin-top:14px;color:var(--text-2)">Załączniki (opcjonalnie)</label>
                          <input type="file" id="inbox-compose-files" name="inbox_files[]" multiple accept=".pdf,.png,.jpg,.jpeg,.gif,.webp,.txt,.doc,.docx,.xls,.xlsx,.zip" style="width:100%;margin-bottom:12px;font-size:13px" />
                          <p class="muted" style="font-size:11px;margin:0 0 12px;line-height:1.45">Do 6 plików, max 5 MB każdy (PDF, obrazy, dokumenty biurowe, ZIP).</p>
                          <label style="display:flex;align-items:flex-start;gap:8px;margin-top:10px;font-size:13px;cursor:pointer;line-height:1.35">
                            <input type="checkbox" id="inbox-compose-trigger-automation" style="margin-top:3px;flex-shrink:0" />
                            <span>Po wysłaniu wywołaj hook automatyzacji (<code>upsellio_crm_inbox_mail_sent</code>) — <strong>tylko przy odpowiedzi w wątku oferty</strong>, nie przy tej wiadomości</span>
                          </label>
                          <p class="muted" style="font-size:11px;margin-top:10px;line-height:1.45">Ścieżka wysyłki jak przy follow-upach i odpowiedziach (CRM). W stopce możesz użyć: <code>{{site_name}}</code>, <code>{{year}}</code>, <code>{{home_url}}</code>.</p>
                        </div>
                        <div style="border-top:1px solid var(--border);padding:14px 20px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;flex-shrink:0;background:var(--surface)">
                          <span style="font-size:12px;color:var(--text-3)">Wyślij z: <strong><?php echo esc_html((string) ($inbox_settings["from_email"] ?? "")); ?></strong></span>
                          <button type="button" class="btn" id="inbox-compose-send-btn" onclick="inboxSendCrmMail(0,'compose')">Wyślij wiadomość</button>
                        </div>
                        <div id="inbox-compose-status" style="display:none;font-size:12px;padding:0 20px 14px;flex-shrink:0"></div>
                      </div>
                    <?php elseif ($inbox_offer_id > 0) : ?>
                      <?php
                      $inbox_offer_title = get_the_title($inbox_offer_id);
                      $inbox_hot_ix = (int) get_post_meta($inbox_offer_id, "_ups_offer_hot_index", true);
                      $inbox_stage_raw = (string) get_post_meta($inbox_offer_id, "_ups_offer_stage", true);
                      $inbox_stage_disp = $pl_label($inbox_stage_raw, "stage");
                      $inbox_last_seen = (string) get_post_meta($inbox_offer_id, "_ups_offer_last_seen", true);
                      $inbox_last_seen_disp = $inbox_last_seen !== "" && strtotime($inbox_last_seen) ? wp_date("d.m.Y H:i", strtotime($inbox_last_seen)) : "brak";
                      $inbox_offer_public = function_exists("upsellio_offer_get_public_url") ? (string) upsellio_offer_get_public_url($inbox_offer_id) : "";
                      $inbox_edit_url = esc_url(add_query_arg(["view" => "offers", "offer_editor_id" => $inbox_offer_id], home_url("/crm-app/")));
                      $inbox_needs_reply_ui = (string) get_post_meta($inbox_offer_id, "_ups_offer_inbox_last_direction", true) === "in";
                      $inbox_head_display = $inbox_client_id > 0 ? get_the_title($inbox_client_id) : $inbox_offer_title;
                      $inbox_av_letter = function_exists("mb_substr") ? mb_strtoupper(mb_substr((string) $inbox_head_display, 0, 1, "UTF-8")) : strtoupper(substr((string) $inbox_head_display, 0, 1));
                      $inbox_cls_labels_msg = ["positive" => "pozytywna", "price_objection" => "obiekcja cenowa", "timing_objection" => "obiekcja terminu", "no_priority" => "brak priorytetu", "other" => "inna klasa"];
                      $inbox_cls_labels_short = ["positive" => "✓ Pozytywna", "price_objection" => "$ Cena", "timing_objection" => "⏱ Timing", "no_priority" => "○ Brak priorytetu", "other" => "? Inna"];
                      $inbox_timeline_events = [];
                      foreach (array_slice(array_reverse($inbox_thread), 0, 5) as $tm) {
                          if (!is_array($tm)) {
                              continue;
                          }
                          $tts = strtotime((string) ($tm["ts"] ?? "")) ?: 0;
                          $tdir = ($tm["direction"] ?? "") === "out" ? "↑" : "↓";
                          $tp = (string) ($tm["subject"] ?? "");
                          if ($tp === "") {
                              $tp = (string) ($tm["body_plain"] ?? "—");
                          }
                          if (function_exists("mb_substr")) {
                              $tp = mb_substr($tp, 0, 48, "UTF-8");
                          } else {
                              $tp = substr($tp, 0, 48);
                          }
                          $inbox_timeline_events[] = [
                              "text" => $tdir . " " . $tp,
                              "time" => $tts ? wp_date("d.m H:i", $tts) : "—",
                          ];
                      }
                      $inbox_offer_score = (int) get_post_meta($inbox_offer_id, "_ups_offer_score", true);
                      ?>
                      <div class="crm-inbox-conversation">
                      <div class="crm-inbox-conversation-head">
                        <button type="button" class="crm-inbox-back-btn btn alt" id="inbox-back-to-list"><?php esc_html_e("← Wróć", "upsellio"); ?></button>
                        <div class="crm-inbox-avatar" aria-hidden="true"><?php echo esc_html($inbox_av_letter); ?></div>
                        <div class="conv-head-text">
                          <div class="conv-head-title"><?php echo esc_html($inbox_head_display); ?></div>
                          <div class="conv-head-sub">
                            <?php echo esc_html($inbox_offer_title); ?>
                            · <?php echo esc_html($inbox_stage_disp); ?>
                            <?php if ($inbox_needs_reply_ui) : ?>
                              · <span style="color:#dc2626;font-weight:700"><?php esc_html_e("Do odpowiedzi", "upsellio"); ?></span>
                            <?php endif; ?>
                          </div>
                        </div>
                        <div class="crm-inbox-conv-actions">
                          <button type="button" class="crm-inbox-conv-action-btn" id="inbox-btn-reply"
                                  onclick="inboxFillReply(<?php echo (int) $inbox_offer_id; ?>,'reply')"
                                  title="<?php esc_attr_e("Odpowiedz", "upsellio"); ?>"
                                  aria-label="<?php esc_attr_e("Odpowiedz", "upsellio"); ?>">↩</button>
                          <button type="button" class="crm-inbox-conv-action-btn"
                                  onclick="inboxFillReply(<?php echo (int) $inbox_offer_id; ?>,'reply_all')"
                                  title="<?php esc_attr_e("Odpowiedz wszystkim", "upsellio"); ?>"
                                  aria-label="<?php esc_attr_e("Odpowiedz wszystkim", "upsellio"); ?>">↩↩</button>
                          <button type="button" class="crm-inbox-conv-action-btn"
                                  onclick="inboxMarkUnread(<?php echo (int) $inbox_offer_id; ?>)"
                                  title="<?php esc_attr_e("Oznacz nieodczytane", "upsellio"); ?>"
                                  aria-label="<?php esc_attr_e("Oznacz nieodczytane", "upsellio"); ?>">●</button>
                          <a href="<?php echo esc_url($inbox_edit_url); ?>"
                             class="crm-inbox-conv-action-btn"
                             title="<?php esc_attr_e("Edytuj ofertę", "upsellio"); ?>"
                             aria-label="<?php esc_attr_e("Edytuj ofertę", "upsellio"); ?>">✏</a>
                          <?php if ($inbox_offer_public !== "") : ?>
                            <a href="<?php echo esc_url($inbox_offer_public); ?>"
                               target="_blank" rel="noopener noreferrer"
                               class="crm-inbox-conv-action-btn"
                               title="<?php esc_attr_e("Strona oferty", "upsellio"); ?>"
                               aria-label="<?php esc_attr_e("Strona oferty", "upsellio"); ?>">↗</a>
                          <?php endif; ?>
                        </div>
                        <button type="button" class="crm-inbox-side-toggle btn alt" id="inbox-side-toggle"><?php esc_html_e("Kontekst ⊞", "upsellio"); ?></button>
                      </div>
                      <div class="crm-inbox-conv-toolbar">
                        <span style="font-size:11px;color:var(--text-3);margin-right:4px"><?php esc_html_e("Flaga:", "upsellio"); ?></span>
                        <?php foreach ($inbox_flag_palette as $fk => $fmeta) :
                            $fhx = (string) ($fmeta["hex"] ?? "#999");
                            $fon = $inbox_current_flag === $fk;
                            ?>
                        <button type="button"
                                class="crm-inbox-flag-btn<?php echo $fon ? " active" : ""; ?>"
                                data-flag="<?php echo esc_attr($fk); ?>"
                                onclick="inboxSetFlag(<?php echo (int) $inbox_offer_id; ?>, <?php echo wp_json_encode($fk); ?>)"
                                title="<?php echo esc_attr((string) ($fmeta["label"] ?? $fk)); ?>"
                                style="background:<?php echo esc_attr($fhx); ?>"></button>
                        <?php endforeach; ?>
                        <button type="button" class="btn alt" style="font-size:11px;padding:4px 8px" onclick="inboxSetFlag(<?php echo (int) $inbox_offer_id; ?>,'')"><?php esc_html_e("× wyczyść", "upsellio"); ?></button>
                      </div>

                      <div class="crm-inbox-thread-scroll" id="inbox-messages">
                        <?php if (empty($inbox_thread)) : ?>
                          <div style="text-align:center;padding:48px 20px;color:var(--text-3)">
                            <div style="font-size:28px;margin-bottom:10px" aria-hidden="true">✉</div>
                            <div style="font-size:13px;font-weight:600"><?php esc_html_e("Brak wiadomości w tym wątku", "upsellio"); ?></div>
                            <div style="font-size:11px;margin-top:4px"><?php esc_html_e("Wyślij pierwszą wiadomość poniżej.", "upsellio"); ?></div>
                          </div>
                        <?php endif; ?>
                        <?php
                        $inbox_last_date_sep = null;
                        foreach ($inbox_thread as $msg) :
                            if (!is_array($msg)) {
                                continue;
                            }
                            $is_out = ($msg["direction"] ?? "") === "out";
                            $cls = (string) ($msg["classification"] ?? "");
                            $cls_label = $inbox_cls_labels_msg[$cls] ?? "";
                            $msg_id_esc = esc_attr((string) ($msg["id"] ?? ""));
                            $ts_msg = strtotime((string) ($msg["ts"] ?? "")) ?: time();
                            $msg_date_str = wp_date("Y-m-d", $ts_msg);
                            if ($inbox_last_date_sep !== $msg_date_str) {
                                $inbox_last_date_sep = $msg_date_str;
                                $today = wp_date("Y-m-d");
                                $yesterday = wp_date("Y-m-d", strtotime("-1 day"));
                                if ($msg_date_str === $today) {
                                    $date_label = __("Dziś", "upsellio");
                                } elseif ($msg_date_str === $yesterday) {
                                    $date_label = __("Wczoraj", "upsellio");
                                } else {
                                    $date_label = wp_date("d.m.Y", $ts_msg);
                                }
                                ?>
                        <div class="crm-inbox-date-separator"><?php echo esc_html($date_label); ?></div>
                                <?php
                            }
                            $src_raw = (string) ($msg["source"] ?? "");
                            $src_note = "";
                            if ($src_raw === "reply_imap") {
                                $src_note = "źródło: IMAP";
                            } elseif ($src_raw === "reply_webhook") {
                                $src_note = "źródło: webhook";
                            } elseif ($src_raw === "crm_manual") {
                                $src_note = "wysłano z CRM";
                            } elseif ($src_raw === "followup_auto") {
                                $src_note = "follow-up automatyczny";
                            } elseif ($src_raw === "ai_followup_auto") {
                                $src_note = "follow-up AI (auto)";
                            }
                            $aria_msg = ($is_out ? __("Wiadomość wychodząca", "upsellio") : __("Wiadomość od klienta", "upsellio")) . ", " . wp_date("d.m.Y H:i", $ts_msg);
                            ?>
                          <article class="crm-inbox-msg<?php echo $is_out ? " out" : " in"; ?>" aria-label="<?php echo esc_attr($aria_msg); ?>">
                            <div class="crm-inbox-msg-meta">
                              <?php echo $is_out ? "↑ Ty" : "↓ Klient"; ?>
                              · <?php echo esc_html(wp_date("d.m.Y H:i", $ts_msg)); ?>
                              <?php if ($src_note !== "") : ?> · <?php echo esc_html($src_note); ?><?php endif; ?>
                            </div>
                            <div class="crm-inbox-msg-bubble">
                              <?php if ((string) ($msg["subject"] ?? "") !== "") : ?>
                                <div class="crm-inbox-msg-subject"><?php echo esc_html((string) ($msg["subject"] ?? "")); ?></div>
                              <?php endif; ?>
                              <?php if ($is_out && (string) ($msg["to"] ?? "") !== "") : ?>
                                <div style="font-size:11px;opacity:.72;margin-bottom:6px"><?php esc_html_e("Do:", "upsellio"); ?> <?php echo esc_html((string) ($msg["to"] ?? "")); ?><?php echo (string) ($msg["cc"] ?? "") !== "" ? " · Dw: " . esc_html((string) ($msg["cc"] ?? "")) : ""; ?></div>
                              <?php endif; ?>
                              <?php echo nl2br(esc_html((string) ($msg["body_plain"] ?? ""))); ?>
                              <?php if ($cls !== "") : ?>
                                <div class="crm-inbox-cls-tag" style="background:<?php echo $is_out ? "rgba(255,255,255,.12)" : "var(--bg-2)"; ?>;color:<?php echo $is_out ? "#fff" : "var(--text-3)"; ?>">
                                  <?php echo esc_html($cls_label !== "" ? $cls_label : $cls); ?>
                                </div>
                              <?php elseif (!$is_out) : ?>
                                <div class="crm-inbox-classify-row">
                                  <?php foreach ($inbox_cls_labels_short as $k => $label) : ?>
                                    <button type="button" class="crm-inbox-cls-btn"
                                      data-msg-id="<?php echo $msg_id_esc; ?>"
                                      onclick="inboxClassify(<?php echo (int) $inbox_offer_id; ?>, <?php echo wp_json_encode($k); ?>, this)">
                                      <?php echo esc_html($label); ?>
                                    </button>
                                  <?php endforeach; ?>
                                </div>
                              <?php endif; ?>
                            </div>
                          </article>
                        <?php endforeach; ?>
                      </div>

                      <div class="crm-inbox-compose" id="inbox-compose-wrap">
                        <div class="crm-inbox-compose-bar" onclick="inboxToggleCompose()">
                          <span class="crm-inbox-compose-bar-label"><?php esc_html_e("Napisz odpowiedź…", "upsellio"); ?></span>
                          <div class="crm-inbox-compose-bar-actions">
                            <?php if ((string) get_option("ups_anthropic_inbox_draft_enabled", "0") === "1" && $ups_ai_key_ok) : ?>
                              <button type="button" class="btn alt ai-draft-btn" id="inbox-ai-draft-btn"
                                      onclick="event.stopPropagation(); inboxAiDraftReply(<?php echo (int) $inbox_offer_id; ?>)">
                                <?php esc_html_e("✨ Szkic AI", "upsellio"); ?>
                              </button>
                            <?php endif; ?>
                            <button type="button" class="btn" id="inbox-send-btn"
                                    onclick="event.stopPropagation(); inboxSendCrmMail(<?php echo (int) $inbox_offer_id; ?>,'reply')">
                              <?php esc_html_e("Wyślij →", "upsellio"); ?>
                            </button>
                            <div class="crm-inbox-compose-toggle-icon" aria-hidden="true">▾</div>
                          </div>
                        </div>

                        <div class="crm-inbox-compose-body">
                          <?php if ((string) ($inbox_reply_prefill["to"] ?? "") === "") : ?>
                            <div style="padding:6px 10px;background:#fef2f2;border:1px solid #fecaca;border-radius:7px;font-size:11px;color:#dc2626;margin-bottom:8px">
                              <?php esc_html_e("⚠ Brak adresu email klienta — uzupełnij profil klienta w CRM.", "upsellio"); ?>
                            </div>
                          <?php endif; ?>

                          <div class="crm-inbox-compose-fields">
                            <span class="crm-inbox-compose-label"><?php esc_html_e("Do", "upsellio"); ?></span>
                            <input type="text" id="inbox-reply-to" class="crm-inbox-compose-input"
                                   value="<?php echo esc_attr((string) ($inbox_reply_prefill["to"] ?? "")); ?>" />
                            <span class="crm-inbox-compose-label"><?php esc_html_e("Dw", "upsellio"); ?></span>
                            <input type="text" id="inbox-reply-cc" class="crm-inbox-compose-input"
                                   value="<?php echo esc_attr((string) ($inbox_reply_prefill["cc"] ?? "")); ?>" />
                            <span class="crm-inbox-compose-label"><?php esc_html_e("Re:", "upsellio"); ?></span>
                            <input type="text" id="inbox-reply-subject" class="crm-inbox-compose-input"
                                   value="<?php echo esc_attr("Re: " . $inbox_offer_title); ?>" />
                          </div>

                          <label class="sr-only" for="inbox-reply-body"><?php esc_html_e("Treść odpowiedzi", "upsellio"); ?></label>
                          <textarea id="inbox-reply-body" rows="4"
                                    placeholder="<?php esc_attr_e("Treść wiadomości…", "upsellio"); ?>"></textarea>

                          <div class="crm-inbox-compose-footer">
                            <span style="font-size:11px;color:var(--text-3)">
                              <?php esc_html_e("Z:", "upsellio"); ?>
                              <strong><?php echo esc_html((string) ($inbox_settings["from_email"] ?? "")); ?></strong>
                            </span>
                            <?php if ((string) get_option("ups_anthropic_inbox_draft_enabled", "0") === "1" && $ups_ai_key_ok) : ?>
                              <button type="button" class="btn alt ai-draft-btn"
                                      onclick="inboxAiDraftReply(<?php echo (int) $inbox_offer_id; ?>)">
                                <?php esc_html_e("✨ Szkic AI", "upsellio"); ?>
                              </button>
                            <?php endif; ?>
                          </div>

                          <details class="crm-inbox-compose-advanced">
                            <summary><?php esc_html_e("Opcje zaawansowane", "upsellio"); ?></summary>
                            <div class="crm-inbox-compose-advanced-body">
                              <label style="display:flex;align-items:center;gap:8px;font-size:12px;cursor:pointer">
                                <input type="checkbox" id="inbox-reply-html" style="margin:0" />
                                <span><?php esc_html_e("Treść jako HTML", "upsellio"); ?></span>
                              </label>
                              <label style="display:flex;align-items:flex-start;gap:8px;font-size:12px;cursor:pointer">
                                <input type="checkbox" id="inbox-reply-use-footer" checked style="margin-top:3px;flex-shrink:0" />
                                <span><?php esc_html_e("Dołącz domyślną stopkę e-mail", "upsellio"); ?></span>
                              </label>
                              <label style="display:flex;align-items:flex-start;gap:8px;font-size:12px;cursor:pointer">
                                <input type="checkbox" id="inbox-reply-trigger-automation" style="margin-top:3px;flex-shrink:0" />
                                <span><?php esc_html_e("Hook automaty po wysłaniu", "upsellio"); ?></span>
                              </label>
                              <label for="inbox-reply-bcc" style="display:block;width:100%">
                                <span class="sr-only"><?php esc_html_e("Udw (opcjonalnie)", "upsellio"); ?></span>
                                <input type="text" id="inbox-reply-bcc" placeholder="<?php esc_attr_e("Udw (opcjonalnie)", "upsellio"); ?>"
                                       class="crm-inbox-compose-input"
                                       style="border:1px solid var(--border);border-radius:6px;padding:4px 8px;background:var(--bg);width:100%;box-sizing:border-box" />
                              </label>
                            </div>
                          </details>

                          <div id="inbox-reply-status" style="font-size:11px;margin-top:4px;display:none"></div>
                        </div>
                      </div>
                      </div>

                    <?php else : ?>
                      <div class="crm-inbox-empty"><?php esc_html_e("Wybierz konwersację z listy lub ", "upsellio"); ?><a href="<?php echo esc_url($inbox_compose_url); ?>"><?php esc_html_e("utwórz nową wiadomość", "upsellio"); ?></a>.</div>
                    <?php endif; ?>

                  </div>

                    <?php if ($inbox_offer_id > 0 && !$inbox_compose) : ?>
                      <aside class="crm-inbox-side">
                      <div class="crm-inbox-side-card crm-inbox-side-card--dark">
                        <h3><?php esc_html_e("Status rozmowy", "upsellio"); ?></h3>
                        <p><?php echo esc_html($inbox_needs_reply_ui ? __("Ostatnia wiadomość od klienta — warto odpowiedzieć.", "upsellio") : __("Ostatnia wiadomość wychodząca lub brak zaległej odpowiedzi.", "upsellio")); ?></p>
                      </div>
                      <div class="crm-inbox-side-card">
                        <div style="font-weight:700;margin-bottom:10px;color:var(--text);font-size:12px;text-transform:uppercase;letter-spacing:.5px">
                          <?php esc_html_e("Kontekst deala", "upsellio"); ?>
                        </div>
                        <div class="crm-inbox-side-stats">
                          <div class="crm-inbox-side-stats-row">
                            <span class="muted"><?php esc_html_e("Score", "upsellio"); ?></span>
                            <strong style="color:<?php
                              echo $inbox_offer_score >= 70 ? "#0f766e" : ($inbox_offer_score >= 40 ? "#d97706" : "var(--text)");
                            ?>"><?php echo (int) $inbox_offer_score; ?></strong>
                          </div>
                          <div class="crm-inbox-side-stats-row">
                            <span class="muted"><?php esc_html_e("Hot index", "upsellio"); ?></span>
                            <strong><?php echo $inbox_hot_ix > 0 ? (int) $inbox_hot_ix : "—"; ?></strong>
                          </div>
                          <div class="crm-inbox-side-stats-row" style="grid-column:1/-1">
                            <span class="muted"><?php esc_html_e("Ostatnia wizyta", "upsellio"); ?></span>
                            <strong style="font-size:13px"><?php echo esc_html($inbox_last_seen_disp); ?></strong>
                          </div>
                        </div>
                        <div class="crm-inbox-stage-btns">
                          <?php foreach (["awareness" => "Świadomość", "consideration" => "Rozważanie", "decision" => "Decyzja"] as $sk => $sl) : ?>
                            <button type="button"
                                    class="crm-inbox-stage-btn<?php echo $inbox_stage_raw === $sk ? " active" : ""; ?>"
                                    onclick="inboxMoveStage(<?php echo (int) $inbox_offer_id; ?>, <?php echo wp_json_encode($sk); ?>)">
                              <?php echo esc_html($sl); ?>
                            </button>
                          <?php endforeach; ?>
                        </div>
                      </div>
                      <div class="crm-inbox-side-card">
                        <div style="font-size:11px;font-weight:800;color:var(--text-3);letter-spacing:.5px;text-transform:uppercase;margin-bottom:8px"><?php esc_html_e("Oś czasu", "upsellio"); ?></div>
                        <?php foreach ($inbox_timeline_events as $tev) : ?>
                          <div class="crm-inbox-timeline-item">
                            <div class="crm-inbox-timeline-dot"></div>
                            <div>
                              <div style="font-size:11px;color:var(--text-2);line-height:1.4"><?php echo esc_html($tev["text"]); ?></div>
                              <div style="font-size:10px;color:var(--text-3)"><?php echo esc_html($tev["time"]); ?></div>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      </div>
                      <?php if ($inbox_offer_public !== "") : ?>
                        <div class="crm-inbox-side-card">
                          <a href="<?php echo esc_url($inbox_offer_public); ?>" target="_blank" rel="noopener noreferrer" class="btn alt" style="width:100%;justify-content:center;display:inline-flex"><?php esc_html_e("Link do oferty ↗", "upsellio"); ?></a>
                        </div>
                      <?php endif; ?>
                      </aside>
                    <?php else : ?>
                      <aside class="crm-inbox-side">
                        <div class="crm-inbox-empty" style="min-height:120px;padding:20px;font-size:12px">
                          <?php if ($inbox_compose) : ?>
                            <?php esc_html_e("Tryb nowej wiadomości — pełny kontekst deala pojawi się po wyborze wątku z listy.", "upsellio"); ?>
                          <?php else : ?>
                            <?php esc_html_e("Wybierz wątek z listy — status, deal i oś czasu pojawią się tutaj.", "upsellio"); ?>
                          <?php endif; ?>
                        </div>
                      </aside>
                    <?php endif; ?>
                </div>
              </section>
            <?php endif; ?>
            <?php if ($view === "alerts") : ?>
              <section class="card">
                <h2>Centrum alertów</h2>
                <?php
                $alerts = [];
                foreach ($offers as $offer) {
                    $oid = (int) $offer->ID;
                    $status = (string) get_post_meta($oid, "_ups_offer_status", true);
                    if ($status === "open") {
                        $last_seen = (string) get_post_meta($oid, "_ups_offer_last_seen", true);
                        if ($last_seen !== "" && strtotime($last_seen) < (time() - (7 * DAY_IN_SECONDS))) {
                            $alerts[] = "Deal #" . $oid . " jest bez aktywności >7 dni.";
                        }
                    }
                    $queue = get_post_meta($oid, "_ups_offer_followup_queue", true);
                    if (is_array($queue)) {
                        foreach ($queue as $item) {
                            if ((string) ($item["status"] ?? "") === "failed") {
                                $alerts[] = "Deal #" . $oid . " ma nieudaną wysyłkę follow-up.";
                                break;
                            }
                        }
                    }
                }
                foreach ($tasks as $task) {
                    $tid = (int) $task->ID;
                    $status = (string) get_post_meta($tid, "_upsellio_task_status", true);
                    $due = (int) get_post_meta($tid, "_upsellio_task_due_at", true);
                    if (!in_array($status, ["done", "cancelled"], true) && $due > 0 && $due < time()) {
                        $alerts[] = "Task #" . $tid . " jest po terminie.";
                    }
                }
                foreach ($offers as $offer) {
                    $oid = (int) $offer->ID;
                    if ((string) get_post_meta($oid, "_ups_offer_sla_active_alert", true) !== "1") {
                        continue;
                    }
                    $st = (string) get_post_meta($oid, "_ups_offer_status", true);
                    if ($st === "won" || $st === "lost") {
                        continue;
                    }
                    $sla_st = (string) get_post_meta($oid, "_ups_offer_pipeline_sla_stage", true);
                    $alerts[] = "SLA deal #" . $oid . " — przekroczenie etapu " . ($sla_st !== "" ? $sla_st : "?") . ".";
                }
                ?>
                <?php if (empty($alerts)) : ?>
                  <p class="muted">Brak krytycznych alertów.</p>
                <?php else : ?>
                  <?php foreach ($alerts as $alert) : ?><div class="timeline-item"><span class="muted">ALERT</span><span><?php echo esc_html($alert); ?></span></div><?php endforeach; ?>
                <?php endif; ?>
              </section>
            <?php endif; ?>
            <?php if ($view === "engine") : ?>
              <section class="card">
                <h2>Silnik sprzedaży</h2>
                <form method="post" class="grid2">
                  <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                  <input type="hidden" name="ups_action" value="save_quick_settings" />
                  <input type="hidden" name="crm_view" value="engine" />
                  <label>Waga intencji</label>
                  <input type="number" min="1" name="ups_sales_intent_weight" value="<?php echo esc_attr((string) get_option("ups_sales_intent_weight", 60)); ?>" />
                  <label>Waga dopasowania</label>
                  <input type="number" min="1" name="ups_sales_fit_weight" value="<?php echo esc_attr((string) get_option("ups_sales_fit_weight", 40)); ?>" />
                  <label>Próg hot index</label>
                  <input type="number" min="1" name="ups_sales_hot_index_threshold" value="<?php echo esc_attr((string) get_option("ups_sales_hot_index_threshold", 72)); ?>" />
                  <label>Opóźnienie dla świadomości (h)</label>
                  <input type="number" min="0" name="ups_sales_playbook_awareness_delay_h" value="<?php echo esc_attr((string) get_option("ups_sales_playbook_awareness_delay_h", 24)); ?>" />
                  <label>Opóźnienie dla rozważania (h)</label>
                  <input type="number" min="0" name="ups_sales_playbook_consideration_delay_h" value="<?php echo esc_attr((string) get_option("ups_sales_playbook_consideration_delay_h", 48)); ?>" />
                  <label>Opóźnienie dla decyzji (h)</label>
                  <input type="number" min="0" name="ups_sales_playbook_decision_delay_h" value="<?php echo esc_attr((string) get_option("ups_sales_playbook_decision_delay_h", 7)); ?>" />
                  <label><input type="checkbox" name="ups_sales_channel_email_enabled" value="1" <?php checked((string) get_option("ups_sales_channel_email_enabled", "1"), "1"); ?> /> Kanał email włączony</label>
                  <label><input type="checkbox" name="ups_sales_spf_ok" value="1" <?php checked((string) get_option("ups_sales_spf_ok", "0"), "1"); ?> /> SPF skonfigurowany</label>
                  <label><input type="checkbox" name="ups_sales_dkim_ok" value="1" <?php checked((string) get_option("ups_sales_dkim_ok", "0"), "1"); ?> /> DKIM skonfigurowany</label>
                  <label><input type="checkbox" name="ups_sales_dmarc_ok" value="1" <?php checked((string) get_option("ups_sales_dmarc_ok", "0"), "1"); ?> /> DMARC skonfigurowany</label>
                  <label>Notatki warm-up</label>
                  <textarea name="ups_sales_warmup_notes"><?php echo esc_textarea((string) get_option("ups_sales_warmup_notes", "")); ?></textarea>
                  <label style="grid-column:1/-1">Idealne branże (ICP) — jedna linia lub fraza po przecinku; dopasowanie substring w polu „Branża” klienta</label>
                  <textarea name="ups_sales_fit_ideal_industries" style="grid-column:1/-1" rows="3" placeholder="np. saas, software, marketing, ecommerce"><?php echo esc_textarea((string) get_option("ups_sales_fit_ideal_industries", "")); ?></textarea>
                  <label>Minimalny sensowny budżet (PLN / mies.) dla pełnych punktów „fit budżetu”</label>
                  <input type="number" min="0" step="100" name="ups_sales_fit_ideal_budget_min_pln" value="<?php echo esc_attr((string) (float) get_option("ups_sales_fit_ideal_budget_min_pln", 0)); ?>" />
                  <p class="muted" style="grid-column:1/-1;margin:0;font-size:12px">Jeśli oba pola są puste / 0, fit score działa jak wcześniej (kompletność danych). Po ustawieniu ICP scoring rozróżnia dopasowanie branży i wielkość budżetu.</p>
                  <button class="btn" type="submit">Zapisz ustawienia silnika sprzedaży</button>
                </form>
              </section>
            <?php endif; ?>
            <?php if ($view === "analytics") : ?>
              <?php
              $status_counts = ["open" => 0, "won" => 0, "lost" => 0];
              $stage_counts = ["awareness" => 0, "consideration" => 0, "decision" => 0];
              $monthly_revenue = [];
              $inbound_class_counts = ["positive" => 0, "price_objection" => 0, "timing_objection" => 0, "no_priority" => 0, "other" => 0];
              $lost_reasons = [];
              $win_reasons = [];
              $accepted_offers = 0;
              $template_wins = [];
              $channel_quality_scores = get_option("ups_automation_channel_quality_scores", []);
              if (!is_array($channel_quality_scores)) {
                  $channel_quality_scores = [];
              }
              $active_mrr = upsellio_crm_app_compute_active_mrr();
              foreach ($offers as $offer) {
                  $oid = (int) $offer->ID;
                  $status = (string) get_post_meta($oid, "_ups_offer_status", true);
                  if (!isset($status_counts[$status])) {
                      $status = "open";
                  }
                  $status_counts[$status]++;
                  $stage = (string) get_post_meta($oid, "_ups_offer_stage", true);
                  if (isset($stage_counts[$stage])) {
                      $stage_counts[$stage]++;
                  }
                  if ($status === "won") {
                      $won_ts = (string) get_post_meta($oid, "_ups_offer_closed_at", true);
                      $month_key = $won_ts !== "" ? gmdate("Y-m", strtotime($won_ts)) : gmdate("Y-m", strtotime((string) $offer->post_modified_gmt));
                      if (!isset($monthly_revenue[$month_key])) {
                          $monthly_revenue[$month_key] = 0.0;
                      }
                      $monthly_revenue[$month_key] += (float) get_post_meta($oid, "_ups_offer_won_value", true);
                      if ((string) get_post_meta($oid, "_ups_offer_accepted_at", true) !== "") {
                          $accepted_offers++;
                      }
                      $win_reason = (string) get_post_meta($oid, "_ups_offer_win_reason", true);
                      if ($win_reason === "") {
                          $win_reason = "brak_danych";
                      }
                      if (!isset($win_reasons[$win_reason])) {
                          $win_reasons[$win_reason] = 0;
                      }
                      $win_reasons[$win_reason]++;
                      $tpl = (string) get_post_meta($oid, "_ups_offer_ab_variant", true);
                      if ($tpl !== "") {
                          if (!isset($template_wins[$tpl])) {
                              $template_wins[$tpl] = 0;
                          }
                          $template_wins[$tpl]++;
                      }
                  }
                  $class = (string) get_post_meta($oid, "_ups_offer_last_inbound_classification", true);
                  if ($class === "") {
                      $class = (string) get_post_meta($oid, "_ups_offer_last_inbound_class", true);
                  }
                  if ($class === "") {
                      $class = "other";
                  }
                  if (!isset($inbound_class_counts[$class])) {
                      $class = "other";
                  }
                  $inbound_class_counts[$class]++;
                  if ($status === "lost") {
                      $reason = (string) get_post_meta($oid, "_ups_offer_loss_reason", true);
                      if ($reason === "") {
                          $reason = (string) get_post_meta($oid, "_ups_offer_last_inbound_classification", true);
                      }
                      if ($reason === "") {
                          $reason = (string) get_post_meta($oid, "_ups_offer_last_inbound_class", true);
                      }
                      if ($reason === "") {
                          $reason = "unknown";
                      }
                      if (!isset($lost_reasons[$reason])) {
                          $lost_reasons[$reason] = 0;
                      }
                      $lost_reasons[$reason]++;
                  }
              }
              ksort($monthly_revenue);
              $decision_analytics = function_exists("upsellio_sales_engine_build_decision_layer_analytics") ? upsellio_sales_engine_build_decision_layer_analytics() : ["owners" => [], "sources" => [], "price_bands" => [], "time_to_close_days" => ["avg" => 0, "count" => 0], "forecast_weighted" => 0];
              $roas_rows = function_exists("upsellio_sales_engine_build_roas_report_rows") ? upsellio_sales_engine_build_roas_report_rows() : [];
              $lead_qual_counts = [];
              $lead_src_counts = [];
              $lead_score_sum = 0;
              $lead_score_n = 0;
              foreach ($leads as $lead_an) {
                  $lid_an = (int) $lead_an->ID;
                  $lq = (string) get_post_meta($lid_an, "_ups_lead_qualification_status", true);
                  if ($lq === "") {
                      $lq = "new";
                  }
                  if (!isset($lead_qual_counts[$lq])) {
                      $lead_qual_counts[$lq] = 0;
                  }
                  $lead_qual_counts[$lq]++;
                  $lsrc = trim((string) get_post_meta($lid_an, "_ups_lead_source", true));
                  if ($lsrc === "") {
                      $lsrc = __("bez źródła", "upsellio");
                  }
                  if (!isset($lead_src_counts[$lsrc])) {
                      $lead_src_counts[$lsrc] = 0;
                  }
                  $lead_src_counts[$lsrc]++;
                  $sc = (int) get_post_meta($lid_an, "_ups_lead_score_0_100", true);
                  if ($sc > 0) {
                      $lead_score_sum += $sc;
                      $lead_score_n++;
                  }
              }
              arsort($lead_src_counts);
              ?>
              <section class="card">
                <h2><?php esc_html_e("Analityka", "upsellio"); ?></h2>
                <div class="crm-view-tabs">
                  <?php
                  $analytics_tab_links = [
                      "sales" => __("Sprzedaż i lejek", "upsellio"),
                      "leads" => __("Leady", "upsellio"),
                      "sources" => __("Źródła i ROAS", "upsellio"),
                      "offers" => __("Oferty i decyzje", "upsellio"),
                      "site" => __("Ruch (GA4)", "upsellio"),
                      "followups" => __("Follow-upy", "upsellio"),
                  ];
                  foreach ($analytics_tab_links as $atk => $atl) :
                      ?>
                    <a class="crm-tab-link <?php echo $analytics_tab === $atk ? "is-active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "analytics", "analytics_tab" => $atk], home_url("/crm-app/"))); ?>"><?php echo esc_html($atl); ?></a>
                  <?php endforeach; ?>
                </div>
              </section>
              <?php if ($analytics_tab === "sales") : ?>
              <section class="card kpi"><span class="muted">Oferty</span><b><?php echo esc_html((string) count($offers)); ?></b></section>
              <section class="card kpi"><span class="muted">Umowy</span><b><?php echo esc_html((string) count($contracts)); ?></b></section>
              <section class="card kpi"><span class="muted">Win rate</span><b><?php echo esc_html((string) (count($offers) > 0 ? round(($status_counts["won"] / count($offers)) * 100) : 0)); ?>%</b></section>
              <section class="card kpi"><span class="muted">MRR</span><b><?php echo esc_html(number_format($active_mrr, 0, ",", " ")); ?> PLN</b></section>
              <section class="card kpi"><span class="muted">Zaakceptowane oferty</span><b><?php echo esc_html((string) $accepted_offers); ?></b></section>
              <section class="card kpi"><span class="muted">Prognoza ważona pipeline</span><b><?php echo esc_html(number_format((float) ($decision_analytics["forecast_weighted"] ?? 0), 0, ",", " ")); ?> PLN</b></section>
              <section class="card kpi"><span class="muted">Śr. czas do wygranej</span><b><?php echo esc_html((string) ($decision_analytics["time_to_close_days"]["avg"] ?? 0)); ?> dni</b><span class="muted"> (n=<?php echo esc_html((string) (int) ($decision_analytics["time_to_close_days"]["count"] ?? 0)); ?>)</span></section>
              <section class="card half">
                <h2>Status ofert</h2>
                <div class="chart-wrap"><canvas id="ups-chart-status"></canvas></div>
              </section>
              <section class="card half">
                <h2>Etapy lejka</h2>
                <div class="chart-wrap"><canvas id="ups-chart-stage"></canvas></div>
              </section>
              <section class="card half">
                <h2>Przychód wygranych ofert (miesiące)</h2>
                <div class="chart-wrap"><canvas id="ups-chart-revenue"></canvas></div>
              </section>
              <section class="card half">
                <h2>Inbound klasyfikacje</h2>
                <div class="chart-wrap"><canvas id="ups-chart-inbound"></canvas></div>
              </section>
              <section class="card">
                <h2>Powody utraty i jakości pipeline</h2>
                <?php
                $loss_reason_labels = [
                    "price" => "Cena",
                    "budget" => "Budżet",
                    "competitor" => "Konkurencja",
                    "timing" => "Timing",
                    "no_need" => "Brak potrzeby",
                    "no_decision" => "Brak decyzji",
                    "no_response" => "Brak odpowiedzi",
                    "scope" => "Zakres",
                ];
                ?>
                <table>
                  <thead><tr><th>Kategoria</th><th>Liczba</th></tr></thead>
                  <tbody>
                    <?php foreach ($lost_reasons as $reason => $count_reason) : ?>
                      <tr><td><?php echo esc_html(isset($loss_reason_labels[$reason]) ? $loss_reason_labels[$reason] : (string) $reason); ?></td><td><?php echo esc_html((string) $count_reason); ?></td></tr>
                    <?php endforeach; ?>
                    <?php if (empty($lost_reasons)) : ?>
                      <tr><td colspan="2">Brak przegranych ofert z klasyfikacją.</td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </section>
              <section class="card">
                <h2>Powody wygranej</h2>
                <table>
                  <thead><tr><th>Powód</th><th>Liczba</th></tr></thead>
                  <tbody>
                    <?php foreach ($win_reasons as $reason => $count_reason) : ?>
                      <tr><td><?php echo esc_html((string) $reason); ?></td><td><?php echo esc_html((string) $count_reason); ?></td></tr>
                    <?php endforeach; ?>
                    <?php if (empty($win_reasons)) : ?>
                      <tr><td colspan="2">Brak danych o powodach wygranej.</td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </section>
              <section class="card">
                <h2>Skuteczność szablonów/variantów ofert</h2>
                <table>
                  <thead><tr><th>Wariant</th><th>Wygrane</th></tr></thead>
                  <tbody>
                    <?php foreach ($template_wins as $tpl => $cnt) : ?>
                      <tr><td><?php echo esc_html((string) $tpl); ?></td><td><?php echo esc_html((string) $cnt); ?></td></tr>
                    <?php endforeach; ?>
                    <?php if (empty($template_wins)) : ?>
                      <tr><td colspan="2">Brak danych A/B.</td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </section>
              <script>
                (function () {
                  function drawBars(canvasId, labels, values, color) {
                    const canvas = document.getElementById(canvasId);
                    if (!canvas) return;
                    const ctx = canvas.getContext("2d");
                    const w = canvas.width = canvas.parentElement.clientWidth;
                    const h = canvas.height = 240;
                    const pad = 24;
                    ctx.clearRect(0, 0, w, h);
                    const max = Math.max(1, ...values);
                    const colW = (w - pad * 2) / Math.max(1, values.length);
                    values.forEach((value, index) => {
                      const x = pad + index * colW + 6;
                      const bh = ((h - pad * 2 - 36) * value) / max;
                      const y = h - pad - bh - 18;
                      ctx.fillStyle = color;
                      ctx.fillRect(x, y, Math.max(8, colW - 12), bh);
                      ctx.fillStyle = "#3d3d38";
                      ctx.font = "11px sans-serif";
                      ctx.fillText(String(value), x, y - 4);
                      ctx.fillText(String(labels[index]).slice(0, 10), x, h - 6);
                    });
                  }
                  drawBars("ups-chart-status", <?php echo wp_json_encode(array_keys($status_counts)); ?>, <?php echo wp_json_encode(array_values($status_counts)); ?>, "#0d9488");
                  drawBars("ups-chart-stage", <?php echo wp_json_encode(array_keys($stage_counts)); ?>, <?php echo wp_json_encode(array_values($stage_counts)); ?>, "#2563eb");
                  drawBars("ups-chart-revenue", <?php echo wp_json_encode(array_keys($monthly_revenue)); ?>, <?php echo wp_json_encode(array_values($monthly_revenue)); ?>, "#16a34a");
                  drawBars("ups-chart-inbound", <?php echo wp_json_encode(array_keys($inbound_class_counts)); ?>, <?php echo wp_json_encode(array_values($inbound_class_counts)); ?>, "#7c3aed");
                })();
              </script>
              <?php endif; ?>
              <?php if ($analytics_tab === "leads") : ?>
              <section class="card kpi"><span class="muted"><?php esc_html_e("Leady w próbce", "upsellio"); ?></span><b><?php echo esc_html((string) count($leads)); ?></b></section>
              <section class="card kpi"><span class="muted"><?php esc_html_e("Śr. score (lead)", "upsellio"); ?></span><b><?php echo esc_html((string) ($lead_score_n > 0 ? round($lead_score_sum / $lead_score_n) : 0)); ?></b></section>
              <section class="card">
                <h2><?php esc_html_e("Status kwalifikacji", "upsellio"); ?></h2>
                <table>
                  <thead><tr><th><?php esc_html_e("Status", "upsellio"); ?></th><th><?php esc_html_e("Liczba", "upsellio"); ?></th></tr></thead>
                  <tbody>
                    <?php foreach ($lead_qual_counts as $qk => $qv) : ?>
                      <tr><td><?php echo esc_html((string) $qk); ?></td><td><?php echo esc_html((string) $qv); ?></td></tr>
                    <?php endforeach; ?>
                    <?php if (empty($lead_qual_counts)) : ?>
                      <tr><td colspan="2"><?php esc_html_e("Brak leadów w próbce.", "upsellio"); ?></td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </section>
              <section class="card">
                <h2><?php esc_html_e("Źródła (pole leada)", "upsellio"); ?></h2>
                <table>
                  <thead><tr><th><?php esc_html_e("Źródło", "upsellio"); ?></th><th><?php esc_html_e("Liczba", "upsellio"); ?></th></tr></thead>
                  <tbody>
                    <?php foreach ($lead_src_counts as $sk => $sv) : ?>
                      <tr><td><?php echo esc_html((string) $sk); ?></td><td><?php echo esc_html((string) $sv); ?></td></tr>
                    <?php endforeach; ?>
                    <?php if (empty($lead_src_counts)) : ?>
                      <tr><td colspan="2"><?php esc_html_e("Brak danych.", "upsellio"); ?></td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </section>
              <?php endif; ?>
              <?php if ($analytics_tab === "sources") : ?>
              <section class="card">
                <h2><?php esc_html_e("Źródła (UTM) — wolumen i wygrane", "upsellio"); ?></h2>
                <table>
                  <thead><tr><th><?php esc_html_e("Źródło", "upsellio"); ?></th><th><?php esc_html_e("Deale", "upsellio"); ?></th><th>Won</th><th><?php esc_html_e("Przychód", "upsellio"); ?></th></tr></thead>
                  <tbody>
                    <?php foreach ($decision_analytics["sources"] as $sname => $sstat) : ?>
                      <?php if (!is_array($sstat)) { continue; } ?>
                      <tr>
                        <td><?php echo esc_html((string) $sname); ?></td>
                        <td><?php echo esc_html((string) (int) ($sstat["deals"] ?? 0)); ?></td>
                        <td><?php echo esc_html((string) (int) ($sstat["won"] ?? 0)); ?></td>
                        <td><?php echo esc_html(number_format((float) ($sstat["revenue"] ?? 0), 0, ",", " ")); ?> PLN</td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </section>
              <section class="card">
                <h2><?php esc_html_e("Pętla marketing → revenue (ROAS / ROI)", "upsellio"); ?></h2>
                <p class="muted"><?php esc_html_e("Koszty kampanii ustaw w Ustawienia → Automatyzacje (CSV). Klucz: źródło + kampania jak w UTM leadów i deali.", "upsellio"); ?></p>
                <table>
                  <thead><tr><th><?php esc_html_e("Źródło", "upsellio"); ?></th><th><?php esc_html_e("Kampania", "upsellio"); ?></th><th><?php esc_html_e("Koszt", "upsellio"); ?></th><th><?php esc_html_e("Leady", "upsellio"); ?></th><th>Won</th><th><?php esc_html_e("Przychód", "upsellio"); ?></th><th>ROAS</th><th>ROI %</th></tr></thead>
                  <tbody>
                    <?php foreach ($roas_rows as $rr) : ?>
                      <?php if (!is_array($rr)) { continue; } ?>
                      <tr>
                        <td><?php echo esc_html((string) ($rr["source"] ?? "")); ?></td>
                        <td><?php echo esc_html((string) ($rr["campaign"] ?? "")); ?></td>
                        <td><?php echo esc_html(number_format((float) ($rr["spend"] ?? 0), 0, ",", " ")); ?></td>
                        <td><?php echo esc_html((string) (int) ($rr["leads"] ?? 0)); ?></td>
                        <td><?php echo esc_html((string) (int) ($rr["won"] ?? 0)); ?></td>
                        <td><?php echo esc_html(number_format((float) ($rr["revenue"] ?? 0), 0, ",", " ")); ?></td>
                        <td><?php echo esc_html((string) ($rr["roas"] ?? 0)); ?></td>
                        <td><?php echo esc_html((string) ($rr["roi_pct"] ?? 0)); ?></td>
                      </tr>
                    <?php endforeach; ?>
                    <?php if (empty($roas_rows)) : ?>
                      <tr><td colspan="8"><?php esc_html_e("Brak wierszy — dodaj koszty CSV lub poczekaj na leady/deale z UTM.", "upsellio"); ?></td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </section>
              <?php endif; ?>
              <?php if ($analytics_tab === "offers") : ?>
              <section class="card">
                <h2><?php esc_html_e("Warstwa decyzyjna: właściciele", "upsellio"); ?></h2>
                <table>
                  <thead><tr><th>Owner</th><th><?php esc_html_e("Deale", "upsellio"); ?></th><th>Won</th><th>Lost</th><th><?php esc_html_e("Przychód won", "upsellio"); ?></th></tr></thead>
                  <tbody>
                    <?php foreach ($decision_analytics["owners"] as $oname => $ostat) : ?>
                      <?php if (!is_array($ostat)) { continue; } ?>
                      <tr>
                        <td><?php echo esc_html((string) $oname); ?></td>
                        <td><?php echo esc_html((string) (int) ($ostat["deals"] ?? 0)); ?></td>
                        <td><?php echo esc_html((string) (int) ($ostat["won"] ?? 0)); ?></td>
                        <td><?php echo esc_html((string) (int) ($ostat["lost"] ?? 0)); ?></td>
                        <td><?php echo esc_html(number_format((float) ($ostat["revenue"] ?? 0), 0, ",", " ")); ?> PLN</td>
                      </tr>
                    <?php endforeach; ?>
                    <?php if (empty($decision_analytics["owners"])) : ?><tr><td colspan="5"><?php esc_html_e("Brak danych.", "upsellio"); ?></td></tr><?php endif; ?>
                  </tbody>
                </table>
              </section>
              <section class="card">
                <h2><?php esc_html_e("Wydajność przedziałów cenowych (won)", "upsellio"); ?></h2>
                <table>
                  <thead><tr><th><?php esc_html_e("Przedział", "upsellio"); ?></th><th><?php esc_html_e("Liczba won", "upsellio"); ?></th><th><?php esc_html_e("Przychód", "upsellio"); ?></th></tr></thead>
                  <tbody>
                    <?php foreach ($decision_analytics["price_bands"] as $pband => $pb) : ?>
                      <?php if (!is_array($pb)) { continue; } ?>
                      <tr>
                        <td><?php echo esc_html((string) $pband); ?></td>
                        <td><?php echo esc_html((string) (int) ($pb["won"] ?? 0)); ?></td>
                        <td><?php echo esc_html(number_format((float) ($pb["revenue"] ?? 0), 0, ",", " ")); ?> PLN</td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </section>
              <?php endif; ?>
              <?php if ($analytics_tab === "site") : ?>
              <section class="card">
                <h2><?php esc_html_e("GA4 → CRM: jakość kanałów (feedback loop)", "upsellio"); ?></h2>
                <table>
                  <thead><tr><th>Source</th><th>Campaign</th><th>Score</th><th>Sessions</th><th>Conversions</th></tr></thead>
                  <tbody>
                    <?php foreach ($channel_quality_scores as $channel_row) : ?>
                      <?php if (!is_array($channel_row)) { continue; } ?>
                      <tr>
                        <td><?php echo esc_html((string) ($channel_row["source"] ?? "")); ?></td>
                        <td><?php echo esc_html((string) ($channel_row["campaign"] ?? "")); ?></td>
                        <td><?php echo esc_html((string) ($channel_row["score"] ?? 0)); ?></td>
                        <td><?php echo esc_html((string) ($channel_row["sessions"] ?? 0)); ?></td>
                        <td><?php echo esc_html((string) ($channel_row["conversions"] ?? 0)); ?></td>
                      </tr>
                    <?php endforeach; ?>
                    <?php if (empty($channel_quality_scores)) : ?>
                      <tr><td colspan="5"><?php esc_html_e("Brak danych GA4 agregatów. Włącz sync i wyślij pierwszą paczkę na endpoint.", "upsellio"); ?></td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </section>
              <?php endif; ?>
              <?php if ($analytics_tab === "followups") : ?>
              <section class="card">
                <h2><?php esc_html_e("Szablony follow-up", "upsellio"); ?></h2>
                <p class="muted"><?php echo esc_html(sprintf(/* translators: %d: follow-up template count */ __("Liczba szablonów follow-up w bazie: %d", "upsellio"), count($followups))); ?></p>
                <p style="margin:0"><a class="btn" href="<?php echo esc_url(add_query_arg(["view" => "followups"], home_url("/crm-app/"))); ?>"><?php esc_html_e("Zarządzaj follow-upami", "upsellio"); ?></a></p>
              </section>
              <section class="card">
                <h2><?php esc_html_e("Skuteczność wariantów ofert (won)", "upsellio"); ?></h2>
                <table>
                  <thead><tr><th><?php esc_html_e("Wariant", "upsellio"); ?></th><th><?php esc_html_e("Wygrane", "upsellio"); ?></th></tr></thead>
                  <tbody>
                    <?php foreach ($template_wins as $tpl => $cnt) : ?>
                      <tr><td><?php echo esc_html((string) $tpl); ?></td><td><?php echo esc_html((string) $cnt); ?></td></tr>
                    <?php endforeach; ?>
                    <?php if (empty($template_wins)) : ?>
                      <tr><td colspan="2"><?php esc_html_e("Brak danych A/B.", "upsellio"); ?></td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </section>
              <?php endif; ?>
            <?php endif; ?>
            <?php if ($view === "research") : ?>
              <?php require get_template_directory() . "/inc/crm-app/research-view.php"; ?>
            <?php endif; ?>
            <?php if ($view === "suggestions") : ?>
              <?php
              if (function_exists("upsellio_crm_render_suggestions_page")) {
                  upsellio_crm_render_suggestions_page($suggestions_tab);
              }
              ?>
            <?php endif; ?>
            <?php if ($view === "settings") : ?>
              <section class="card">
                <h2>Zakładki ustawień</h2>
                <p style="display:flex;flex-wrap:wrap;gap:8px;align-items:center">
                  <a class="btn <?php echo $settings_tab === "general" ? "" : "alt"; ?>" href="<?php echo esc_url(add_query_arg(["view" => "settings", "settings_tab" => "general"], home_url("/crm-app/"))); ?>">Ogólne</a>
                  <a class="btn <?php echo $settings_tab === "mailbox" ? "" : "alt"; ?>" href="<?php echo esc_url(add_query_arg(["view" => "settings", "settings_tab" => "mailbox"], home_url("/crm-app/"))); ?>">Mail / Skrzynki</a>
                  <a class="btn <?php echo $settings_tab === "scoring" ? "" : "alt"; ?>" href="<?php echo esc_url(add_query_arg(["view" => "settings", "settings_tab" => "scoring"], home_url("/crm-app/"))); ?>">Scoring</a>
                  <a class="btn <?php echo $settings_tab === "automation" ? "" : "alt"; ?>" href="<?php echo esc_url(add_query_arg(["view" => "settings", "settings_tab" => "automation"], home_url("/crm-app/"))); ?>">Automatyzacje</a>
                  <a class="btn <?php echo $settings_tab === "offer-template" ? "" : "alt"; ?>" href="<?php echo esc_url(add_query_arg(["view" => "settings", "settings_tab" => "offer-template"], home_url("/crm-app/"))); ?>">Szablon oferty</a>
                  <a class="btn <?php echo $settings_tab === "contract-template" ? "" : "alt"; ?>" href="<?php echo esc_url(add_query_arg(["view" => "settings", "settings_tab" => "contract-template"], home_url("/crm-app/"))); ?>">Szablon umowy</a>
                  <a class="btn <?php echo $settings_tab === "users" ? "" : "alt"; ?>" href="<?php echo esc_url(add_query_arg(["view" => "settings", "settings_tab" => "users"], home_url("/crm-app/"))); ?>">Użytkownicy</a>
                  <a class="btn <?php echo $settings_tab === "integrations" ? "" : "alt"; ?>" href="<?php echo esc_url(add_query_arg(["view" => "settings", "settings_tab" => "integrations"], home_url("/crm-app/"))); ?>">Integracje</a>
                  <a class="btn <?php echo $settings_tab === "notifications" ? "" : "alt"; ?>" href="<?php echo esc_url(add_query_arg(["view" => "settings", "settings_tab" => "notifications"], home_url("/crm-app/"))); ?>">Powiadomienia</a>
                  <a class="btn <?php echo $settings_tab === "ai" ? "" : "alt"; ?>" href="<?php echo esc_url(add_query_arg(["view" => "settings", "settings_tab" => "ai"], home_url("/crm-app/"))); ?>">AI / Anthropic</a>
                  <a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "engine"], home_url("/crm-app/"))); ?>">Silnik sprzedaży</a>
                  <a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "template-studio"], home_url("/crm-app/"))); ?>">Szablony ofert/umów</a>
                </p>
              </section>

              <?php if ($settings_tab === "general") : ?>
                <section class="card">
                  <h2>Ustawienia ogólne CRM</h2>
                  <form method="post" class="grid2">
                    <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                    <input type="hidden" name="ups_action" value="save_quick_settings" />
                    <input type="hidden" name="crm_view" value="settings" />
                    <input type="hidden" name="settings_tab" value="general" />
                    <label>Pierwsze przypomnienie umowy (dni)</label>
                    <input type="number" min="1" name="contract_reminder_first_days" value="<?php echo esc_attr((string) get_option("ups_contract_reminder_first_days", 3)); ?>" />
                    <label>Drugie przypomnienie umowy (dni)</label>
                    <input type="number" min="2" name="contract_reminder_second_days" value="<?php echo esc_attr((string) get_option("ups_contract_reminder_second_days", 7)); ?>" />
                    <label>Cooldown follow-up (h)</label>
                    <input type="number" min="0" name="followup_cooldown_hours" value="<?php echo esc_attr((string) get_option("ups_followup_cooldown_hours", 24)); ?>" />
                    <label>Max follow-up / oferta</label>
                    <input type="number" min="1" name="followup_max_per_offer" value="<?php echo esc_attr((string) get_option("ups_followup_max_per_offer", 5)); ?>" />
                    <h3 style="grid-column:1/-1;margin:14px 0 6px;font-size:15px">Klasyfikacja odpowiedzi inbound (Anthropic)</h3>
                    <p class="muted" style="grid-column:1/-1;margin:0 0 6px;font-size:12px">Opcjonalnie: Claude Haiku zamiast samego regexu. Przy błędzie API lub braku klucza — fallback regex.</p>
                    <label><input type="checkbox" name="ups_anthropic_inbound_enabled" value="1" <?php checked((string) get_option("ups_anthropic_inbound_enabled", "0"), "1"); ?> /> Włącz klasyfikację przez API</label>
                    <span></span>
                    <label>Anthropic API key</label>
                    <input type="password" name="ups_anthropic_api_key" value="" autocomplete="new-password" placeholder="<?php echo esc_attr((string) get_option("ups_anthropic_api_key", "")) !== "" ? "Zapisany — wpisz nowy aby zamienić" : "sk-ant-…"; ?>" />
                    <label>Model (opcjonalnie)</label>
                    <input type="text" name="ups_anthropic_model" value="<?php echo esc_attr((string) get_option("ups_anthropic_model", "")); ?>" placeholder="claude-haiku-4-5-20251001" />
                    <p class="muted" style="grid-column:1/-1;margin:8px 0 4px;font-size:12px;line-height:1.45">Leady z formularza (post type <code>lead</code>): po zapisie — w tle — aktualizacja <code>_upsellio_lead_score</code> oraz taksonomii <code>lead_status</code> (JSON z API). Klucz można też podać stałą <code>UPSELLIO_ANTHROPIC_API_KEY</code> w <code>wp-config.php</code>.</p>
                    <label><input type="checkbox" name="ups_anthropic_wp_lead_form_enabled" value="1" <?php checked((string) get_option("ups_anthropic_wp_lead_form_enabled", "0"), "1"); ?> /> Włącz scoring + etap (lead_status) dla nowych leadów WP</label>
                    <span></span>
                    <label><input type="checkbox" name="ups_anthropic_inbox_draft_enabled" value="1" <?php checked((string) get_option("ups_anthropic_inbox_draft_enabled", "0"), "1"); ?> /> Inbox: przycisk ✨ szkicu odpowiedzi (Claude)</label>
                    <span></span>
                    <label><input type="checkbox" name="ups_anthropic_inbox_auto_followup_enabled" value="1" <?php checked((string) get_option("ups_anthropic_inbox_auto_followup_enabled", "0"), "1"); ?> /> Inbox: automatyczny follow-up AI po ciszy (cron godzinowy)</label>
                    <span></span>
                    <label>Godziny ciszy przed follow-upem AI (6–168)</label>
                    <input type="number" min="6" max="168" name="ups_anthropic_inbox_auto_followup_hours" value="<?php echo esc_attr((string) max(6, min(168, (int) get_option("ups_anthropic_inbox_auto_followup_hours", 24)))); ?>" />
                    <label style="grid-column:1/-1"><input type="checkbox" name="ups_anthropic_inbox_auto_followup_dry_run" value="1" <?php checked((string) get_option("ups_anthropic_inbox_auto_followup_dry_run", "0"), "1"); ?> /> Follow-up AI: tryb testowy (dry run) — bez wysyłki e-mail; zapis na timeline oferty + pełny tekst w logu skrzynki</label>
                    <p class="muted" style="grid-column:1/-1;margin:0;font-size:12px;line-height:1.45">Przy włączonym dry run dla każdej „ciszy” wygeneruje się treść raz na wątek; zobaczysz efekt w kartotece deala i w logu (Ustawienia → Mail). Po wyłączeniu dry run i nadal tym samym wątku — pierwsza prawdziwa wysyłka pójdzie normalnie (o ile nie zablokuje jej <code>_ups_offer_ai_fu_sent_msg_id</code>). Wymaga skonfigurowanej wysyłki CRM dopiero przy wyłączonym dry run. Dotyczy ofert <strong>open</strong>, ostatnia wiadomość od klienta. Max. 8 ofert na przebieg crona (filtr <code>upsellio_crm_ai_followup_max_offers_per_hour</code>).</p>
                    <button class="btn" type="submit">Zapisz ustawienia ogólne</button>
                  </form>
                  <h2 style="margin-top:12px">Import / eksport danych</h2>
                  <form method="post" enctype="multipart/form-data" class="grid2" style="margin:0 0 10px">
                    <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                    <input type="hidden" name="ups_action" value="import_leads_csv" />
                    <input type="hidden" name="crm_view" value="settings" />
                    <input type="hidden" name="settings_tab" value="general" />
                    <label>Import leadów CSV (kolumny: name,email,phone,source,type,status)</label>
                    <input type="file" name="leads_csv" accept=".csv,text/csv" />
                    <button class="btn alt" type="submit">Importuj leady</button>
                  </form>
                  <p class="muted" style="margin:0 0 8px;font-size:13px">Eksport przez <code>admin-ajax.php</code> (czyste nagłówki, bez HTML przed CSV). Wymaga roli administratora.</p>
                  <p style="margin:0;display:flex;flex-wrap:wrap;gap:8px;align-items:center">
                    <?php foreach (["leads" => "Leady", "clients" => "Klienci", "offers" => "Oferty", "tasks" => "Taski"] as $crm_ent => $crm_lbl) : ?>
                      <a class="btn alt" href="<?php echo esc_url(wp_nonce_url(add_query_arg(["action" => "upsellio_crm_export", "entity" => $crm_ent], admin_url("admin-ajax.php")), "ups_crm_export")); ?>">CSV: <?php echo esc_html($crm_lbl); ?></a>
                    <?php endforeach; ?>
                  </p>
                </section>
              <?php endif; ?>

              <?php if ($settings_tab === "ai") : ?>
                <?php
                $ups_blog_seed_prefill = isset($_GET["seed"]) ? sanitize_text_field(wp_unslash((string) $_GET["seed"])) : "";
                $ups_blog_focus_scroll = isset($_GET["blog_focus"]) && (string) wp_unslash($_GET["blog_focus"]) === "1";
                $ups_ai_company_val = trim((string) get_option("ups_ai_company_context", ""));
                if ($ups_ai_company_val === "") {
                    $ups_ai_company_val = trim((string) get_option("ups_anthropic_company_context", ""));
                }
                $ups_ai_lead_prompt_val = trim((string) get_option("ups_ai_prompt_lead_scoring", ""));
                if ($ups_ai_lead_prompt_val === "") {
                    $ups_ai_lead_prompt_val = (string) get_option("ups_anthropic_prompt_lead_score", "");
                }
                $ups_ai_inbox_draft_val = trim((string) get_option("ups_ai_prompt_inbox_draft", ""));
                if ($ups_ai_inbox_draft_val === "") {
                    $ups_ai_inbox_draft_val = (string) get_option("ups_anthropic_prompt_inbox_draft", "");
                }
                $ups_ai_followup_val = trim((string) get_option("ups_ai_prompt_followup", ""));
                if ($ups_ai_followup_val === "") {
                    $ups_ai_followup_val = (string) get_option("ups_anthropic_prompt_inbox_followup", "");
                }
                $ups_blog_cat_id = (int) get_option("ups_blog_bot_category", 0);
                $ups_blog_cats = get_categories(["hide_empty" => false, "orderby" => "name"]);
                $ups_blog_last_run = (string) get_option("ups_blog_bot_last_run", "");
                $ups_blog_last_draft = (int) get_option("ups_blog_bot_last_draft_id", 0);
                $ups_blog_sched = (string) get_option("ups_blog_bot_schedule", "weekly");
                $ups_seo_temp_raw = get_option("ups_ai_blog_seo_temperature", null);
                $ups_seo_temp_display = $ups_seo_temp_raw === null || $ups_seo_temp_raw === ""
                    ? 0.7
                    : max(0.0, min(1.2, (float) $ups_seo_temp_raw));
                $ups_seo_max_raw = get_option("ups_ai_blog_seo_max_tokens", null);
                $ups_seo_max_display = $ups_seo_max_raw === null || $ups_seo_max_raw === ""
                    ? 3500
                    : max(800, min(12000, (int) $ups_seo_max_raw));
                ?>
                <section class="card">
                  <h2>AI / Anthropic — prompty i Blog Bot</h2>
                  <p class="muted" style="margin:0 0 12px;font-size:13px;line-height:1.55">Kontekst firmy i prompty trafiają do <code>wp_options</code> (<code>ups_ai_*</code>). Przy zapisie kontekst jest synchronizowany także do <code>ups_anthropic_company_context</code> dla starszych integracji. Blog Bot tworzy wyłącznie <strong>szkice</strong> wpisów (nigdy nie publikuje sam). Narzędzie <strong>SEO Blog Tool</strong> (WP) korzysta z tych samych kluczy Anthropic i modelu <code>ups_blog_bot_model</code>; prompty systemowe / kampanii / limity tokenów dla generatora ustawiasz poniżej (bez osobnej konfiguracji w widoku SEO Blog Tool). Przy włączonym kontekście firmy wywołania bloga używają <strong>Prompt Caching</strong> (Anthropic) dla pierwszego bloku tekstu.</p>
                  <form method="post" class="grid2" style="align-items:start">
                    <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                    <input type="hidden" name="ups_action" value="save_quick_settings" />
                    <input type="hidden" name="crm_view" value="settings" />
                    <input type="hidden" name="settings_tab" value="ai" />
                    <label style="grid-column:1/-1;font-weight:700">Kontekst firmy (<code>ups_ai_company_context</code>)</label>
                    <textarea name="ups_ai_company_context" rows="6" style="grid-column:1/-1;width:100%;font-size:13px;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--bg);font-family:inherit" placeholder="Np. kim jesteś, specjalizacja, ton, właściciel…"><?php echo esc_textarea($ups_ai_company_val); ?></textarea>
                    <p class="muted" style="grid-column:1/-1;margin:-4px 0 4px;font-size:12px;line-height:1.5">Opcjonalnie: wąskie konteksty zamiast jednego bloku dla wszystkich funkcji — jeśli pole jest puste, używany jest ogólny kontekst powyżej.</p>
                    <label style="grid-column:1/-1;font-weight:700;margin-top:10px"><?php esc_html_e("Router modeli Anthropic", "upsellio"); ?></label>
                    <p class="muted" style="grid-column:1/-1;margin:-4px 0 6px;font-size:12px;line-height:1.5"><?php esc_html_e("Haiku — zadania wewnętrzne (scoring, klasyfikacja intencji, generator tematów, klastry keyword, sugestie SEO/Ads, research frazy). Sonnet — treści widoczne dla klienta lub eksportowane (artykuły Blog Bot, wypełnienie oferty, draft inbox, plan keyword dla klienta). Puste pola → użyty jest model z „Ogólne” (ups_anthropic_model). Stała UPSELLIO_AI_MODEL_ALL w wp-config nadpisuje wszystkie zadania.", "upsellio"); ?></p>
                    <label><?php esc_html_e("Model — Haiku (analityka, scoring, sugestie)", "upsellio"); ?></label>
                    <input type="text" name="ups_ai_model_haiku" value="<?php echo esc_attr((string) get_option("ups_ai_model_haiku", "claude-haiku-4-5-20251001")); ?>" placeholder="claude-haiku-4-5-20251001" style="width:100%;max-width:420px" />
                    <label><?php esc_html_e("Model — Sonnet (treść dla klienta)", "upsellio"); ?></label>
                    <input type="text" name="ups_ai_model_sonnet" value="<?php echo esc_attr((string) get_option("ups_ai_model_sonnet", "claude-sonnet-4-5")); ?>" placeholder="claude-sonnet-4-5" style="width:100%;max-width:420px" />
                    <label style="grid-column:1/-1;font-weight:700">Kontekst — scoring leadów (<code>ups_ai_context_scoring</code>)</label>
                    <textarea name="ups_ai_context_scoring" rows="4" style="grid-column:1/-1;width:100%;font-size:13px;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--bg);font-family:inherit" placeholder="<?php echo esc_attr("Przykład do wklejenia i dopasowania: Sebastian Kelm, konsultant Google Ads + Meta Ads + strony B2B. ICP: firmy z budżetem reklamowym min. 2000–3000 PLN/mies., branże SaaS, e-commerce B2B, usługi profesjonalne. Wyklucz: MLM, kryptowaluty, strony jednorazowe <1000 PLN. Oceniaj surowo — lepiej missed lead niż zmarnowany czas na niskiej jakości kontakt."); ?>"><?php echo esc_textarea((string) get_option("ups_ai_context_scoring", "")); ?></textarea>
                    <label style="grid-column:1/-1;font-weight:700;margin-top:6px">Kontekst — draft / follow-up inbox (<code>ups_ai_context_draft</code>, follow-up: <code>ups_ai_context_followup</code> jeśli ustawiony)</label>
                    <textarea name="ups_ai_context_draft" rows="4" style="grid-column:1/-1;width:100%;font-size:13px;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--bg);font-family:inherit" placeholder="<?php echo esc_attr("Przykład: Piszesz jako konsultant agencji — ton konkretny i partnerski, bez korpo-żargonu. Krótkie maile, jedna jasna propozycja następnego kroku (call, brief, termin). PL, B2B."); ?>"><?php echo esc_textarea((string) get_option("ups_ai_context_draft", "")); ?></textarea>
                    <label style="grid-column:1/-1;font-weight:700;margin-top:6px">Kontekst — tylko auto follow-up po ciszy (<code>ups_ai_context_followup</code>)</label>
                    <textarea name="ups_ai_context_followup" rows="3" style="grid-column:1/-1;width:100%;font-size:13px;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--bg);font-family:inherit" placeholder="<?php echo esc_attr("Opcjonalnie nadpisuje kontekst dla crona follow-up po ciszy; puste = użyj pola draft powyżej. Przykład: grzeczny reminder, bez presji, uzasadnij wartość jednym zdaniem."); ?>"><?php echo esc_textarea((string) get_option("ups_ai_context_followup", "")); ?></textarea>
                    <label style="grid-column:1/-1;font-weight:700;margin-top:6px">Kontekst — Blog Bot + generator tematów (<code>ups_ai_context_blog</code>)</label>
                    <textarea name="ups_ai_context_blog" rows="4" style="grid-column:1/-1;width:100%;font-size:13px;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--bg);font-family:inherit" placeholder="<?php echo esc_attr("Przykład do wklejenia: Piszesz jako Sebastian Kelm — praktyk B2B z 10-letnim doświadczeniem. Styl: partnerski, konkretny, bez korporacyjnego żargonu. Czytelnik: właściciel firmy lub marketer w firmie B2B, Polska. Unikaj teorii — każdy artykuł musi dawać czytelnikowi jeden konkretny krok do wykonania."); ?>"><?php echo esc_textarea((string) get_option("ups_ai_context_blog", "")); ?></textarea>
                    <label style="grid-column:1/-1;font-weight:700;margin-top:6px">Kontekst — ✨ Wypełnij AI (formularz oferty) (<code>ups_ai_context_offer_fill</code>)</label>
                    <p class="muted" style="grid-column:1/-1;margin:-4px 0 4px;font-size:12px;line-height:1.5">Używany przy przycisku „Wypełnij AI” w budowniczku oferty. Puste = użyj kontekstu draftu (<code>ups_ai_context_draft</code>).</p>
                    <textarea name="ups_ai_context_offer_fill" rows="3" style="grid-column:1/-1;width:100%;font-size:13px;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--bg);font-family:inherit" placeholder="<?php echo esc_attr("Np. ton ofert, co zawsze obiecujesz klientom, czego nie obiecujesz, typowy zakres usług."); ?>"><?php echo esc_textarea((string) get_option("ups_ai_context_offer_fill", "")); ?></textarea>

                    <label style="grid-column:1/-1;font-weight:700;margin-top:8px">Prompt — scoring leada (formularz → <code>lead</code>)</label>
                    <p class="muted" style="grid-column:1/-1;margin:-6px 0 4px;font-size:12px">Domyślny szablon oczekuje JSON z polami <code>lead_score</code>, <code>lead_status</code>, <code>score_reason</code>. Placeholdery: <code>{lead_data}</code> / <code>{lead_blob}</code>, <code>{lead_status_list}</code>, <code>{lead_name}</code>, <code>{lead_email}</code>, <code>{lead_phone}</code>, <code>{lead_company}</code>, <code>{lead_service}</code>, <code>{lead_budget}</code>, <code>{lead_goal}</code>, <code>{lead_message}</code>, <code>{lead_form_origin}</code></p>
                    <textarea name="ups_ai_prompt_lead_scoring" rows="10" style="grid-column:1/-1;width:100%;font-size:13px;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--bg);font-family:ui-monospace,Menlo,Consolas,monospace"><?php echo esc_textarea($ups_ai_lead_prompt_val); ?></textarea>

                    <label style="grid-column:1/-1;font-weight:700;margin-top:8px">Prompt — draft odpowiedzi (Inbox ✨)</label>
                    <p class="muted" style="grid-column:1/-1;margin:-6px 0 4px;font-size:12px">Placeholdery: <code>{offer_title}</code>, <code>{offer_stage}</code> / <code>{stage}</code>, <code>{behavior_section}</code> (zachowanie na stronie oferty — publiczny landing), <code>{thread}</code>, <code>{hint_section}</code> / <code>{hint_block}</code>, <code>{hint}</code>, <code>{intent_section}</code> (wstawiane automatycznie — klasyfikacja ostatniej wiadomości inbox)</p>
                    <textarea name="ups_ai_prompt_inbox_draft" rows="10" style="grid-column:1/-1;width:100%;font-size:13px;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--bg);font-family:ui-monospace,Menlo,Consolas,monospace"><?php echo esc_textarea($ups_ai_inbox_draft_val); ?></textarea>

                    <label style="grid-column:1/-1;font-weight:700;margin-top:8px">Prompt — follow-up po ciszy (cron)</label>
                    <p class="muted" style="grid-column:1/-1;margin:-6px 0 4px;font-size:12px">Placeholdery: <code>{offer_title}</code>, <code>{offer_stage}</code> / <code>{stage}</code>, <code>{channel_context}</code>, <code>{behavior_section}</code> (landing oferty), <code>{thread}</code>, <code>{hours_silence}</code>, <code>{client_name}</code>, <code>{last_message}</code>, <code>{days_silent}</code></p>
                    <textarea name="ups_ai_prompt_followup" rows="10" style="grid-column:1/-1;width:100%;font-size:13px;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--bg);font-family:ui-monospace,Menlo,Consolas,monospace"><?php echo esc_textarea($ups_ai_followup_val); ?></textarea>

                    <label style="grid-column:1/-1;font-weight:700;margin-top:8px">Prompt — opis oferty (integracje)</label>
                    <p class="muted" style="grid-column:1/-1;margin:-6px 0 4px;font-size:12px"><code>upsellio_anthropic_crm_build_offer_description_prompt()</code> — <code>{offer_title}</code>, <code>{client_name}</code>, <code>{offer_context}</code></p>
                    <textarea name="ups_anthropic_prompt_offer_description" rows="8" style="grid-column:1/-1;width:100%;font-size:13px;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--bg);font-family:ui-monospace,Menlo,Consolas,monospace"><?php echo esc_textarea((string) get_option("ups_anthropic_prompt_offer_description", "")); ?></textarea>

                    <h3 id="ups-blog-bot-panel" style="grid-column:1/-1;margin:18px 0 6px;font-size:16px;font-family:var(--font-display),Syne,sans-serif">Blog Bot (WP-Cron → draft)</h3>
                    <p class="muted" style="grid-column:1/-1;margin:-4px 0 8px;font-size:12px;line-height:1.5">Harmonogram: pierwsze uruchomienie w okolicy <strong>poniedziałku 07:00</strong> (strefa witryny), potem wg interwału. Fraza jest zdejmowana z kolejki dopiero po udanym zapisie szkicu (błąd API nie kasuje tematu).</p>
                    <label style="grid-column:1/-1"><input type="checkbox" name="ups_blog_bot_enabled" value="1" <?php checked((string) get_option("ups_blog_bot_enabled", "0"), "1"); ?> /> Włącz Blog Bota</label>
                    <label>Model Blog Bot (legacy / override)</label>
                    <input type="text" name="ups_blog_bot_model" value="<?php echo esc_attr((string) get_option("ups_blog_bot_model", "claude-haiku-4-5-20251001")); ?>" placeholder="np. claude-haiku-4-5-20251001, claude-sonnet-4-5" />
                    <p class="muted" style="grid-column:1/-1;margin:-4px 0 0;font-size:11px;line-height:1.45"><?php esc_html_e("Cron Blog Bot używa modelu z pola „Sonnet” w sekcji Router powyżej. To pole zapisuje nadal `ups_blog_bot_model` (kompatybilność, Testy AI). Snapshot Sonnet 4.5 w API:", "upsellio"); ?> <code>claude-sonnet-4-5-20250929</code> <?php esc_html_e("lub alias", "upsellio"); ?> <code>claude-sonnet-4-5</code>.</p>
                    <label>Harmonogram</label>
                    <select name="ups_blog_bot_schedule">
                      <option value="daily" <?php selected($ups_blog_sched, "daily"); ?>>Codziennie</option>
                      <option value="biweekly" <?php selected($ups_blog_sched, "biweekly"); ?>>Dwa razy w tygodniu</option>
                      <option value="weekly" <?php selected($ups_blog_sched, "weekly"); ?>>Raz w tygodniu</option>
                      <option value="monthly" <?php selected($ups_blog_sched, "monthly"); ?>>Raz w miesiącu</option>
                    </select>
                    <label>Email powiadomień</label>
                    <input type="email" name="ups_blog_bot_notify_email" value="<?php echo esc_attr((string) get_option("ups_blog_bot_notify_email", "")); ?>" placeholder="twoj@email.pl" />
                    <label>Autor draftów (ID użytkownika WP)</label>
                    <input type="number" min="1" name="ups_blog_bot_post_author" value="<?php echo esc_attr((string) max(1, (int) get_option("ups_blog_bot_post_author", max(1, (int) get_current_user_id())))); ?>" />
                    <label>Docelowa liczba słów</label>
                    <input type="number" min="400" step="50" name="ups_blog_bot_target_length" value="<?php echo esc_attr((string) max(400, (int) get_option("ups_blog_bot_target_length", 1200))); ?>" />
                    <label>Timeout HTTP (API Anthropic, sekundy)</label>
                    <input type="number" min="0" max="600" step="30" name="ups_blog_bot_http_timeout" value="<?php echo esc_attr((string) max(0, (int) get_option("ups_blog_bot_http_timeout", 0))); ?>" placeholder="0 = 240 s" />
                    <p class="muted" style="grid-column:1/-1;margin:-8px 0 4px;font-size:11px;line-height:1.45">Wpisz <strong>0</strong>, aby użyć domyślnych <strong>240 s</strong> (wcześniejsze stałe 90 s kończyły się <code>cURL error 28</code> przy długiej odpowiedzi JSON). Na wolnym hostingu ustaw np. 300–420. Górny limit techniczny: 600.</p>
                    <label>Domyślna miniatura draftu (ID załącznika)</label>
                    <input type="number" min="0" name="ups_blog_bot_default_thumbnail_id" value="<?php echo esc_attr((string) max(0, (int) get_option("ups_blog_bot_default_thumbnail_id", 0))); ?>" placeholder="0 = brak" />
                    <p class="muted" style="grid-column:1/-1;margin:-8px 0 4px;font-size:11px;line-height:1.45">Opcjonalnie: ID obrazka z biblioteki mediów — używany, gdy Blog Bot nie znajdzie pasującego obrazu po frazie z kolejki.</p>
                    <label>Kategoria draftów</label>
                    <select name="ups_blog_bot_category">
                      <option value="0" <?php selected($ups_blog_cat_id, 0); ?>>— bez kategorii —</option>
                      <?php foreach ($ups_blog_cats as $bc) : ?>
                        <?php if ($bc instanceof WP_Term) : ?>
                          <option value="<?php echo esc_attr((string) (int) $bc->term_id); ?>" <?php selected($ups_blog_cat_id, (int) $bc->term_id); ?>><?php echo esc_html((string) $bc->name); ?></option>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </select>
                    <label style="grid-column:1/-1;font-weight:700;margin-top:6px">Kolejka tematów (jedna linia = jeden temat)</label>
                    <textarea id="ups-blog-bot-keywords-queue" name="ups_blog_bot_keywords_queue" rows="10" style="grid-column:1/-1;width:100%;font-size:13px;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--bg);font-family:inherit"><?php echo esc_textarea((string) get_option("ups_blog_bot_keywords_queue", "")); ?></textarea>
                    <label style="grid-column:1/-1;font-weight:700">Użyte tematy (podgląd)</label>
                    <textarea readonly rows="5" style="grid-column:1/-1;width:100%;font-size:12px;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--bg-muted, rgba(0,0,0,.04));font-family:ui-monospace,Menlo,Consolas,monospace"><?php echo esc_textarea((string) get_option("ups_blog_bot_keywords_used", "")); ?></textarea>
                    <label style="grid-column:1/-1;font-weight:700;margin-top:8px">Prompt — artykuł blogowy (<code>ups_ai_prompt_blog_post</code>)</label>
                    <p class="muted" style="grid-column:1/-1;margin:-6px 0 4px;font-size:12px">Zmienne: <code>{keyword}</code>, <code>{target_length}</code>, <code>{existing_posts}</code>, <code>{services_context}</code>, <code>{converting_keywords}</code>, <code>{internal_url_catalog}</code>, <code>{tone}</code> — jeśli w szablonie nie ma <code>{internal_url_catalog}</code>, katalog URL (do linków markdown) jest dopisywany na końcu promptu.</p>
                    <textarea name="ups_ai_prompt_blog_post" rows="12" style="grid-column:1/-1;width:100%;font-size:13px;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--bg);font-family:ui-monospace,Menlo,Consolas,monospace"><?php echo esc_textarea((string) get_option("ups_ai_prompt_blog_post", "")); ?></textarea>

                    <h3 style="grid-column:1/-1;margin:18px 0 6px;font-size:15px;font-family:var(--font-display),Syne,sans-serif">Narzędzie SEO Blog Tool (generator w WP)</h3>
                    <p class="muted" style="grid-column:1/-1;margin:-4px 0 8px;font-size:12px;line-height:1.5">Te pola sterują wyłącznie ręcznym generatorem (<em>Wpisy → SEO Blog Tool</em>): lista tematów + JSON wpisu. Model i klucz API jak Blog Bot powyżej.</p>
                    <label style="grid-column:1/-1;font-weight:700">Prompt systemowy — generator SEO (<code>ups_ai_prompt_blog_seo_system</code>)</label>
                    <p class="muted" style="grid-column:1/-1;margin:-6px 0 4px;font-size:12px">Używany jako instrukcja systemowa przy generowaniu tematów i treści JSON w narzędziu SEO Blog Tool.</p>
                    <textarea name="ups_ai_prompt_blog_seo_system" rows="8" style="grid-column:1/-1;width:100%;font-size:13px;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--bg);font-family:inherit"><?php echo esc_textarea((string) get_option("ups_ai_prompt_blog_seo_system", "")); ?></textarea>
                    <label style="grid-column:1/-1;font-weight:700;margin-top:8px">Domyślny prompt kampanii — prefill w SEO Blog Tool (<code>ups_ai_blog_seo_campaign_default</code>)</label>
                    <p class="muted" style="grid-column:1/-1;margin:-6px 0 4px;font-size:12px">Wartość startowa pola „Prompt kampanii” w formularzu generatora (można nadpisać przy każdej serii).</p>
                    <textarea name="ups_ai_blog_seo_campaign_default" rows="5" style="grid-column:1/-1;width:100%;font-size:13px;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--bg);font-family:inherit"><?php echo esc_textarea((string) get_option("ups_ai_blog_seo_campaign_default", "")); ?></textarea>
                    <label>Temperature (SEO generator)</label>
                    <input type="number" step="0.05" min="0" max="1.2" name="ups_ai_blog_seo_temperature" value="<?php echo esc_attr((string) $ups_seo_temp_display); ?>" />
                    <label>Max tokens (SEO generator, max 4096 w API)</label>
                    <input type="number" min="800" max="12000" name="ups_ai_blog_seo_max_tokens" value="<?php echo esc_attr((string) $ups_seo_max_display); ?>" />

                    <label style="grid-column:1/-1">Ostatnie uruchomienie</label>
                    <input type="text" readonly style="grid-column:1/-1;max-width:420px" value="<?php echo esc_attr($ups_blog_last_run !== "" ? $ups_blog_last_run : "—"); ?>" />
                    <?php
                    $ups_blog_edit = $ups_blog_last_draft > 0 ? get_edit_post_link($ups_blog_last_draft, "raw") : "";
                    ?>
                    <?php if ($ups_blog_edit !== "") : ?>
                      <p style="grid-column:1/-1;margin:0;font-size:13px">Ostatni draft: <a href="<?php echo esc_url($ups_blog_edit); ?>">#<?php echo (int) $ups_blog_last_draft; ?></a></p>
                    <?php else : ?>
                      <p style="grid-column:1/-1;margin:0;font-size:13px" class="muted">Ostatni draft: —</p>
                    <?php endif; ?>

                    <p class="muted" style="grid-column:1/-1;margin:8px 0 0;font-size:12px">Klucz API i model inbound: <a href="<?php echo esc_url(add_query_arg(["view" => "settings", "settings_tab" => "general"], home_url("/crm-app/"))); ?>">Ustawienia → Ogólne</a>. Filtry: <code>upsellio_anthropic_crm_prompt_*</code>.</p>
                    <button class="btn" type="submit" style="grid-column:1/-1;margin-top:6px">Zapisz ustawienia AI / Blog</button>
                  </form>
                </section>
                <?php if ($ups_blog_seed_prefill !== "" || $ups_blog_focus_scroll) : ?>
                  <script>
                  document.addEventListener("DOMContentLoaded", function(){
                    var ta = document.getElementById("ups-blog-bot-keywords-queue");
                    <?php if ($ups_blog_seed_prefill !== "") : ?>
                    var pre = <?php echo wp_json_encode($ups_blog_seed_prefill); ?>;
                    if (ta && pre) {
                      ta.value = pre + (ta.value.trim() !== "" ? "\n" + ta.value : "");
                      ta.focus();
                    }
                    <?php endif; ?>
                    var anchor = document.getElementById("ups-blog-bot-panel");
                    if (anchor && (window.location.hash === "#ups-blog-bot-panel" || <?php echo $ups_blog_focus_scroll ? "true" : "false"; ?>)) {
                      anchor.scrollIntoView({ behavior: "smooth", block: "start" });
                    }
                  });
                  </script>
                <?php endif; ?>
                <?php
                if (function_exists("upsellio_crm_render_blog_keyword_research_panel")) {
                    upsellio_crm_render_blog_keyword_research_panel();
                }
                if (function_exists("upsellio_topicgen_render_panel")) {
                    upsellio_topicgen_render_panel();
                }
                ?>
              <?php endif; ?>

              <?php if ($settings_tab === "mailbox") : ?>
                <section class="card">
                  <h2>Mail / Skrzynki / Lejek</h2>
                  <form method="post" class="grid2">
                    <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                    <input type="hidden" name="ups_action" value="save_quick_settings" />
                    <input type="hidden" name="crm_view" value="settings" />
                    <input type="hidden" name="settings_tab" value="mailbox" />
                    <label>From name</label>
                    <input type="text" name="ups_followup_from_name" value="<?php echo esc_attr((string) get_option("ups_followup_from_name", get_bloginfo("name"))); ?>" />
                    <label>From email</label>
                    <input type="email" name="ups_followup_from_email" value="<?php echo esc_attr((string) get_option("ups_followup_from_email", get_option("admin_email"))); ?>" />
                    <p class="muted" style="grid-column:1/-1;margin:0 0 4px;font-size:12px;line-height:1.45"><strong>Wysyłka CRM (wersja A)</strong> — wyłącznie automatyczne follow-upy i ręczne odpowiedzi z inboxu CRM używają poniższego SMTP (PHPMailer), bez <code>wp_mail()</code> i bez hostingowego <code>mail()</code>. Reszta WordPressa nadal korzysta z domyślnej wysyłki.</p>
                    <label><input type="checkbox" name="ups_followup_smtp_enabled" value="1" <?php checked((string) get_option("ups_followup_smtp_enabled", "0"), "1"); ?> /> Włącz dedykowany SMTP dla wysyłki CRM</label>
                    <span></span>
                    <label>SMTP host (CRM)</label>
                    <input type="text" name="ups_followup_smtp_host" value="<?php echo esc_attr((string) get_option("ups_followup_smtp_host", "")); ?>" placeholder="smtp.twojadomena.pl" autocomplete="off" />
                    <label>SMTP port</label>
                    <input type="number" min="1" name="ups_followup_smtp_port" value="<?php echo esc_attr((string) get_option("ups_followup_smtp_port", 587)); ?>" />
                    <label>SMTP encryption</label>
                    <select name="ups_followup_smtp_encryption">
                      <?php $senc = (string) get_option("ups_followup_smtp_encryption", "tls"); ?>
                      <option value="tls" <?php selected($senc, "tls"); ?>>TLS (STARTTLS, np. 587)</option>
                      <option value="ssl" <?php selected($senc, "ssl"); ?>>SSL (SMTPS, np. 465)</option>
                      <option value="none" <?php selected($senc, "none"); ?>>Brak (niezalecane)</option>
                    </select>
                    <label>SMTP username</label>
                    <input type="text" name="ups_followup_smtp_username" value="<?php echo esc_attr((string) get_option("ups_followup_smtp_username", "")); ?>" autocomplete="username" />
                    <label>SMTP password (obecne: <?php echo esc_html(function_exists("upsellio_followup_mask_secret") ? (string) upsellio_followup_mask_secret(function_exists("upsellio_followup_get_smtp_password") ? (string) upsellio_followup_get_smtp_password() : "") : "ukryte"); ?>)</label>
                    <input type="password" name="ups_followup_smtp_password" value="" autocomplete="new-password" />
                    <label><input type="checkbox" name="ups_followup_smtp_test" value="1" /> Po zapisie testuj połączenie SMTP (sam handshake, bez wysyłki)</label>
                    <span></span>
                    <?php if (is_array($smtp_test_result) && isset($smtp_test_result["message"])) : ?>
                      <p class="muted" style="grid-column:1 / -1;color:<?php echo !empty($smtp_test_result["ok"]) ? "#0f766e" : "#b91c1c"; ?>"><?php echo esc_html((string) $smtp_test_result["message"]); ?></p>
                    <?php endif; ?>
                    <label>Inbound secret</label>
                    <input type="text" name="ups_followup_inbound_secret" value="<?php echo esc_attr((string) get_option("ups_followup_inbound_secret", "")); ?>" />
                    <label><input type="checkbox" name="ups_followup_mailbox_enabled" value="1" <?php checked((string) get_option("ups_followup_mailbox_enabled", "0"), "1"); ?> /> Włącz pobieranie odpowiedzi ze skrzynki (IMAP)</label>
                    <span></span>
                    <label>IMAP host</label>
                    <input type="text" name="ups_followup_mailbox_host" value="<?php echo esc_attr((string) get_option("ups_followup_mailbox_host", "")); ?>" placeholder="imap.twojadomena.pl" />
                    <label>IMAP port</label>
                    <input type="number" min="1" name="ups_followup_mailbox_port" value="<?php echo esc_attr((string) get_option("ups_followup_mailbox_port", 993)); ?>" />
                    <label>IMAP encryption</label>
                    <select name="ups_followup_mailbox_encryption">
                      <?php $enc = (string) get_option("ups_followup_mailbox_encryption", "ssl"); ?>
                      <option value="ssl" <?php selected($enc, "ssl"); ?>>SSL</option>
                      <option value="tls" <?php selected($enc, "tls"); ?>>TLS</option>
                      <option value="none" <?php selected($enc, "none"); ?>>None</option>
                    </select>
                    <label>IMAP username</label>
                    <input type="text" name="ups_followup_mailbox_username" value="<?php echo esc_attr((string) get_option("ups_followup_mailbox_username", "")); ?>" />
                    <label>IMAP password (obecne: <?php echo esc_html(function_exists("upsellio_followup_mask_secret") ? (string) upsellio_followup_mask_secret(function_exists("upsellio_followup_get_mailbox_password") ? (string) upsellio_followup_get_mailbox_password() : "") : "ukryte"); ?>)</label>
                    <input type="password" name="ups_followup_mailbox_password" value="" />
                    <label>IMAP folder</label>
                    <input type="text" name="ups_followup_mailbox_folder" value="<?php echo esc_attr((string) get_option("ups_followup_mailbox_folder", "INBOX")); ?>" />
                    <label><input type="checkbox" name="ups_followup_mailbox_ssl_novalidate" value="1" <?php checked((string) get_option("ups_followup_mailbox_ssl_novalidate", "0"), "1"); ?> /> IMAP: nie weryfikuj certyfikatu SSL (diagnostyka / hosting z własnym CA)</label>
                    <span class="muted" style="font-size:12px;line-height:1.4">Włącz tylko przy błędzie certyfikatu. Synchronizacja nadal obejmuje wyłącznie <strong>nieprzeczytane</strong> wiadomości (UNSEEN).</span>
                    <label><input type="checkbox" name="ups_followup_mailbox_test" value="1" /> Po zapisie wykonaj test połączenia IMAP</label>
                    <span></span>
                    <?php if (is_array($mailbox_test_result) && isset($mailbox_test_result["message"])) : ?>
                      <p class="muted" style="grid-column:1 / -1;color:<?php echo !empty($mailbox_test_result["ok"]) ? "#0f766e" : "#b91c1c"; ?>"><?php echo esc_html((string) $mailbox_test_result["message"]); ?></p>
                    <?php endif; ?>
                    <label>Offer email subject</label>
                    <input type="text" name="ups_offer_email_subject" value="<?php echo esc_attr((string) get_option("ups_offer_email_subject", "Twoja oferta: {{offer_title}}")); ?>" />
                    <label>Offer email HTML</label>
                    <textarea name="ups_offer_email_html"><?php echo esc_textarea((string) get_option("ups_offer_email_html", "")); ?></textarea>
                    <label>Offer email CSS</label>
                    <textarea name="ups_offer_email_css"><?php echo esc_textarea((string) get_option("ups_offer_email_css", "")); ?></textarea>
                    <h3 style="grid-column:1/-1;margin:14px 0 6px;font-size:15px;font-family:var(--font-display)">Stopka domyślna (maile CRM)</h3>
                    <p class="muted" style="grid-column:1/-1;margin:0 0 8px;font-size:12px;line-height:1.45">HTML i CSS doklejane do wiadomości wysyłanych jako <strong>automatyczne follow-upy</strong> oraz z inboxu CRM (gdy zaznaczono „Dołącz domyślną stopkę”). Klasa kontenera stopki: <code>.ups-crm-email-footer</code>. Placeholdery w HTML stopki: <code>{{site_name}}</code>, <code>{{year}}</code>, <code>{{home_url}}</code>.</p>
                    <label>Stopka — HTML</label>
                    <textarea name="ups_crm_email_footer_html" rows="6" placeholder="np. &lt;p&gt;Pozdrawiamy,&lt;br&gt;Zespół {{site_name}}&lt;/p&gt;"><?php echo esc_textarea((string) get_option("ups_crm_email_footer_html", "")); ?></textarea>
                    <label>Stopka — CSS</label>
                    <textarea name="ups_crm_email_footer_css" rows="5" placeholder=".ups-crm-email-footer { font-size:12px; color:#666; }"><?php echo esc_textarea((string) get_option("ups_crm_email_footer_css", "")); ?></textarea>
                    <label>Hint awareness</label>
                    <textarea name="ups_followup_hint_awareness"><?php echo esc_textarea((string) get_option("ups_followup_hint_awareness", "")); ?></textarea>
                    <label>Hint consideration</label>
                    <textarea name="ups_followup_hint_consideration"><?php echo esc_textarea((string) get_option("ups_followup_hint_consideration", "")); ?></textarea>
                    <label>Hint decision</label>
                    <textarea name="ups_followup_hint_decision"><?php echo esc_textarea((string) get_option("ups_followup_hint_decision", "")); ?></textarea>
                    <h3 style="grid-column:1/-1;margin:18px 0 6px;font-size:15px;font-family:var(--font-display)">Logi skrzynki (IMAP / wysyłka)</h3>
                    <p class="muted" style="grid-column:1/-1;margin:0 0 8px;font-size:12px;line-height:1.45">Świeże wpisy na górze. Włącz tryb rozmowny, aby dopisywać szczegółowe komunikaty SMTP (handshake). Przydatne przy diagnozie „nie wychodzi / nie wpada”.</p>
                    <label style="grid-column:1/-1"><input type="checkbox" name="ups_mailbox_log_verbose" value="1" <?php checked((string) get_option("ups_mailbox_log_verbose", "0"), "1"); ?> /> Rozmowny log SMTP (DEBUG_SERVER → dziennik)</label>
                    <div style="grid-column:1/-1;display:flex;flex-wrap:wrap;gap:10px;align-items:flex-start">
                      <textarea id="ups-mailbox-log-textarea" readonly rows="14" style="flex:1 1 320px;min-height:220px;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:11px;line-height:1.45;width:100%"><?php echo esc_textarea(function_exists("upsellio_mailbox_log_render_text") ? upsellio_mailbox_log_render_text() : ""); ?></textarea>
                      <button type="button" class="btn alt" id="ups-mailbox-log-clear-btn" style="flex-shrink:0">Wyczyść log</button>
                    </div>
                    <button class="btn" type="submit">Zapisz ustawienia maila i lejka</button>
                  </form>
                </section>
              <?php endif; ?>

              <?php if ($settings_tab === "scoring") : ?>
                <section class="card">
                  <h2>Scoring ofert</h2>
                  <form method="post" class="grid2">
                    <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                    <input type="hidden" name="ups_action" value="save_quick_settings" />
                    <input type="hidden" name="crm_view" value="settings" />
                    <input type="hidden" name="settings_tab" value="scoring" />
                    <label>Views for consideration</label>
                    <input type="number" min="1" name="ups_offer_stage_consideration_views" value="<?php echo esc_attr((string) get_option("ups_offer_stage_consideration_views", 2)); ?>" />
                    <label>Views for decision</label>
                    <input type="number" min="1" name="ups_offer_stage_decision_views" value="<?php echo esc_attr((string) get_option("ups_offer_stage_decision_views", 3)); ?>" />
                    <label><input type="checkbox" name="ups_offer_stage_decision_require_cta" value="1" <?php checked((string) get_option("ups_offer_stage_decision_require_cta", "0"), "1"); ?> /> CTA required for decision stage</label>
                    <span></span>
                    <label>Consideration score</label>
                    <input type="number" min="1" name="ups_offer_score_consideration" value="<?php echo esc_attr((string) get_option("ups_offer_score_consideration", 45)); ?>" />
                    <label>Decision score</label>
                    <input type="number" min="1" name="ups_offer_score_decision" value="<?php echo esc_attr((string) get_option("ups_offer_score_decision", 75)); ?>" />
                    <label>Hot score</label>
                    <input type="number" min="1" name="ups_offer_score_hot" value="<?php echo esc_attr((string) get_option("ups_offer_score_hot", 70)); ?>" />
                    <label>Consideration pricing seconds</label>
                    <input type="number" min="0" name="ups_offer_score_consideration_pricing_seconds" value="<?php echo esc_attr((string) get_option("ups_offer_score_consideration_pricing_seconds", 25)); ?>" />
                    <label>Decision pricing seconds</label>
                    <input type="number" min="0" name="ups_offer_score_decision_pricing_seconds" value="<?php echo esc_attr((string) get_option("ups_offer_score_decision_pricing_seconds", 60)); ?>" />
                    <label>Hot pricing seconds</label>
                    <input type="number" min="0" name="ups_offer_score_hot_pricing_seconds" value="<?php echo esc_attr((string) get_option("ups_offer_score_hot_pricing_seconds", 45)); ?>" />
                    <h3 style="grid-column:1/-1;margin-top:10px">Scoring hybrydowy 0–100 (wagi biznesowe)</h3>
                    <p class="muted" style="grid-column:1/-1">Łączy: jakość źródła, fit, intencję (z zachowania na ofercie), timing etapu, wartość oferty.</p>
                    <label>Waga: źródło</label>
                    <input type="number" min="1" name="ups_hybrid_weight_source" value="<?php echo esc_attr((string) get_option("ups_hybrid_weight_source", 15)); ?>" />
                    <label>Waga: fit</label>
                    <input type="number" min="1" name="ups_hybrid_weight_fit" value="<?php echo esc_attr((string) get_option("ups_hybrid_weight_fit", 25)); ?>" />
                    <label>Waga: intencja</label>
                    <input type="number" min="1" name="ups_hybrid_weight_intent" value="<?php echo esc_attr((string) get_option("ups_hybrid_weight_intent", 30)); ?>" />
                    <label>Waga: timing</label>
                    <input type="number" min="1" name="ups_hybrid_weight_timing" value="<?php echo esc_attr((string) get_option("ups_hybrid_weight_timing", 15)); ?>" />
                    <label>Waga: wartość</label>
                    <input type="number" min="1" name="ups_hybrid_weight_value" value="<?php echo esc_attr((string) get_option("ups_hybrid_weight_value", 15)); ?>" />
                    <p class="muted" style="grid-column:1/-1">Koszty kampanii (CSV) ustaw w zakładce <strong>Automatyzacje</strong> — raport ROAS jest w Analityce.</p>
                    <button class="btn" type="submit">Zapisz scoring</button>
                  </form>
                </section>
              <?php endif; ?>

              <?php if ($settings_tab === "automation") : ?>
                <section class="card">
                  <h2>Automatyzacje SLA / A/B / Alerting / Prospecting</h2>
                  <form method="post" class="grid2">
                    <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                    <input type="hidden" name="ups_action" value="save_quick_settings" />
                    <input type="hidden" name="crm_view" value="settings" />
                    <input type="hidden" name="settings_tab" value="automation" />
                    <label>SLA consideration (dni)</label>
                    <input type="number" min="1" name="ups_automation_sla_consideration_days" value="<?php echo esc_attr((string) get_option("ups_automation_sla_consideration_days", 7)); ?>" />
                    <label>Alert spadku win-rate (p.p.)</label>
                    <input type="number" min="1" name="ups_automation_alert_drop_win_rate_pct" value="<?php echo esc_attr((string) get_option("ups_automation_alert_drop_win_rate_pct", 10)); ?>" />
                    <label>Alert lost-rate (%)</label>
                    <input type="number" min="1" name="ups_automation_alert_lost_spike_pct" value="<?php echo esc_attr((string) get_option("ups_automation_alert_lost_spike_pct", 20)); ?>" />
                    <label>Prospecting odstęp kroków (dni)</label>
                    <input type="number" min="1" name="ups_automation_cold_followup_days" value="<?php echo esc_attr((string) get_option("ups_automation_cold_followup_days", 3)); ?>" />
                    <label>Próg próbki A/B (min views)</label>
                    <input type="number" min="5" name="ups_automation_ab_min_sample" value="<?php echo esc_attr((string) get_option("ups_automation_ab_min_sample", 20)); ?>" />
                    <label>Minimalny uplift A/B (%)</label>
                    <input type="number" min="1" name="ups_automation_ab_min_lift_pct" value="<?php echo esc_attr((string) get_option("ups_automation_ab_min_lift_pct", 5)); ?>" />
                    <label><input type="checkbox" name="ups_automation_ga4_sync_enabled" value="1" <?php checked((string) get_option("ups_automation_ga4_sync_enabled", "1"), "1"); ?> /> Włącz loop GA4 -> CRM (agregaty)</label>
                    <span></span>
                    <h3 style="grid-column:1/-1;margin-top:8px">SLA pipeline (godziny per etap decyzyjny)</h3>
                    <?php $sla_cfg = function_exists("upsellio_automation_get_pipeline_sla_definitions") ? upsellio_automation_get_pipeline_sla_definitions() : []; ?>
                    <label>new_lead (kontakt)</label>
                    <input type="number" min="1" name="crm_sla_new_lead_hours" value="<?php echo esc_attr((string) (int) ($sla_cfg["new_lead"]["hours"] ?? 24)); ?>" />
                    <label>qualification</label>
                    <input type="number" min="1" name="crm_sla_qualification_hours" value="<?php echo esc_attr((string) (int) ($sla_cfg["qualification"]["hours"] ?? 48)); ?>" />
                    <label>offer (follow-up)</label>
                    <input type="number" min="1" name="crm_sla_offer_hours" value="<?php echo esc_attr((string) (int) ($sla_cfg["offer"]["hours"] ?? 72)); ?>" />
                    <label>negotiation (domknięcie)</label>
                    <input type="number" min="1" name="crm_sla_negotiation_hours" value="<?php echo esc_attr((string) (int) ($sla_cfg["negotiation"]["hours"] ?? 168)); ?>" />
                    <label>Koszty kampanii (CSV) — merge z ROAS</label>
                    <textarea name="ups_crm_marketing_spend_csv" rows="4" placeholder="source,campaign,cost"></textarea>
                    <span></span>
                    <p class="muted" style="grid-column:1 / -1">
                      <?php
                        $upsellio_wp_analytics_url = function_exists("upsellio_site_analytics_admin_url")
                            ? upsellio_site_analytics_admin_url()
                            : admin_url("admin.php?page=upsellio-site-analytics");
                        ?>
                      Import GA4 z konta Google (OAuth):
                      <a href="<?php echo esc_url($upsellio_wp_analytics_url); ?>" target="_blank" rel="noopener noreferrer">Analityka SEO w WordPress (GSC + GA4)</a>
                      (menu w panelu: <strong>Analityka SEO</strong> lub <strong>Ustawienia → Analityka SEO</strong>).
                      Alternatywnie endpoint: <code><?php echo esc_html(rest_url("upsellio/v1/ga4-aggregate")); ?></code><br/>
                      Endpoint słów kluczowych GSC (POST JSON, service account poza WP): <code><?php echo esc_html(rest_url("upsellio/v1/gsc-keywords")); ?></code><br/>
                      Nagłówek: <code>x-upsellio-secret</code> = Twój Inbound secret (<code>ups_followup_inbound_secret</code>). Ostatni sync GA4: <code><?php echo esc_html((string) get_option("ups_automation_ga4_last_sync", "-")); ?></code>.
                    </p>
                    <?php for ($i = 1; $i <= 5; $i++) : ?>
                      <label>Prospecting step <?php echo esc_html((string) $i); ?> - subject</label>
                      <input type="text" name="ups_prospect_subject_step_<?php echo esc_attr((string) $i); ?>" value="<?php echo esc_attr((string) get_option("ups_prospect_subject_step_" . $i, "")); ?>" />
                      <label>Prospecting step <?php echo esc_html((string) $i); ?> - body</label>
                      <textarea name="ups_prospect_body_step_<?php echo esc_attr((string) $i); ?>"><?php echo esc_textarea((string) get_option("ups_prospect_body_step_" . $i, "")); ?></textarea>
                    <?php endfor; ?>
                    <p class="muted" style="grid-column:1 / -1">Zmienne prospecting: <code>{{name}}</code>, <code>{{company}}</code>, <code>{{today}}</code>.</p>
                    <button class="btn" type="submit">Zapisz automatyzacje</button>
                  </form>
                </section>
              <?php endif; ?>

              <?php if ($settings_tab === "offer-template") : ?>
                <section class="card">
                  <h2>Szablon oferty (dynamiczne pola)</h2>
                  <p class="muted">Strony publiczne ofert korzystają teraz z <a href="<?php echo esc_url(add_query_arg(["view" => "template-studio"], home_url("/crm-app/"))); ?>"><strong>Generatora szablonów</strong></a> i budowniczka na widoku Oferty. Poniższy HTML jest używany tylko przy opcji „Regeneruj z legacy szablonu” lub starych integracjach.</p>
                  <form method="post" class="grid2" id="ups-crm-settings-offer-template-form">
                    <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                    <input type="hidden" name="ups_action" value="save_offer_template" />
                    <input type="hidden" name="crm_view" value="settings" />
                    <input type="hidden" name="settings_tab" value="offer-template" />
                    <label>HTML szablonu oferty</label>
                    <textarea name="offer_template_html"><?php echo esc_textarea((string) get_option("ups_offer_template_html", function_exists("upsellio_offer_get_default_template_html") ? (string) upsellio_offer_get_default_template_html() : "")); ?></textarea>
                    <label>CSS szablonu oferty</label>
                    <textarea name="offer_template_css"><?php echo esc_textarea((string) get_option("ups_offer_template_css", function_exists("upsellio_offer_get_default_template_css") ? (string) upsellio_offer_get_default_template_css() : "")); ?></textarea>
                    <p class="muted" style="grid-column:1 / -1">Dostępne zmienne: <code>{{client_name}}</code>, <code>{{client_company}}</code>, <code>{{offer_title}}</code>, <code>{{offer_price}}</code>, <code>{{offer_timeline}}</code>, <code>{{offer_cta_text}}</code>, <code>{{offer_url}}</code>, <code>{{today}}</code>.</p>
                    <button class="btn" type="submit">Zapisz szablon oferty</button>
                  </form>
                </section>
              <?php endif; ?>

              <?php if ($settings_tab === "contract-template") : ?>
                <section class="card">
                  <h2>Szablon umowy (dynamiczne pola)</h2>
                  <p class="muted">Biblioteka wielu szablonów umów: <a href="<?php echo esc_url(add_query_arg(["view" => "template-studio", "tab" => "contract"], home_url("/crm-app/"))); ?>">Generator szablonów → Umowy</a>. Ten formularz to domyślny fallback, gdy nie wybierzesz szablonu z biblioteki.</p>
                  <form method="post" class="grid2" id="ups-crm-settings-contract-template-form">
                    <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                    <input type="hidden" name="ups_action" value="save_contract_template" />
                    <input type="hidden" name="crm_view" value="settings" />
                    <input type="hidden" name="settings_tab" value="contract-template" />
                    <label>HTML szablonu umowy</label>
                    <textarea name="contract_template_html"><?php echo esc_textarea((string) get_option("ups_contract_template_html", $contract_template_html)); ?></textarea>
                    <label>CSS szablonu umowy</label>
                    <textarea name="contract_template_css"><?php echo esc_textarea((string) get_option("ups_contract_template_css", $contract_template_css)); ?></textarea>
                    <p class="muted" style="grid-column:1 / -1">Dostępne zmienne: <code>{{client_name}}</code>, <code>{{offer_title}}</code>, <code>{{offer_price}}</code>, <code>{{offer_timeline}}</code>, <code>{{offer_url}}</code>, <code>{{contract_url}}</code>, <code>{{today}}</code>.</p>
                    <button class="btn" type="submit">Zapisz szablon umowy</button>
                  </form>
                </section>
              <?php endif; ?>

              <?php if ($settings_tab === "users") : ?>
                <section class="card">
                  <h2>Użytkownicy i dostęp</h2>
                  <p class="muted">Role i konta są zarządzane przez WordPress. Tutaj szybki skrót do panelu administracyjnego.</p>
                  <p style="margin-top:12px"><a class="btn" href="<?php echo esc_url(admin_url("users.php")); ?>">Lista użytkowników (WP Admin)</a></p>
                </section>
              <?php endif; ?>

              <?php if ($settings_tab === "integrations") : ?>
                <section class="card">
                  <h2>Integracje</h2>
                  <p class="muted">Konfiguracja techniczna (poczta, automatyzacje, silnik sprzedaży) jest rozproszona po dedykowanych zakładkach — poniżej skróty.</p>
                  <ul style="margin:12px 0 0;padding-left:18px;line-height:1.65;font-size:14px">
                    <li><a href="<?php echo esc_url(add_query_arg(["view" => "settings", "settings_tab" => "mailbox"], home_url("/crm-app/"))); ?>">Mail / SMTP / IMAP</a></li>
                    <li><a href="<?php echo esc_url(add_query_arg(["view" => "settings", "settings_tab" => "automation"], home_url("/crm-app/"))); ?>">Automatyzacje (SLA, GA4, prospecting)</a></li>
                    <li><a href="<?php echo esc_url(add_query_arg(["view" => "engine"], home_url("/crm-app/"))); ?>">Silnik sprzedaży</a></li>
                  </ul>
                </section>
              <?php endif; ?>

              <?php if ($settings_tab === "notifications") : ?>
                <section class="card">
                  <h2>Powiadomienia</h2>
                  <p class="muted">Powiadomienia operacyjne CRM (np. niedostarczone maile) zbierane są w widoku Alerty. Wysyłka e-maili korzysta z ustawień skrzynki.</p>
                  <p style="margin-top:12px"><a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "alerts"], home_url("/crm-app/"))); ?>">Otwórz alerty</a></p>
                </section>
              <?php endif; ?>
            <?php endif; ?>
          </div></div>
        </div>
      </div>
      <?php if ($view === "inbox") : ?>
      <script>
      var upsCrmAjax = <?php echo wp_json_encode(admin_url("admin-ajax.php")); ?>;
      var upsCrmNonce = <?php echo wp_json_encode(wp_create_nonce("ups_crm_app_action")); ?>;
      var inboxReplyPrefill = <?php echo wp_json_encode($inbox_reply_prefill ?? ["to" => "", "cc" => ""]); ?>;
      var inboxReplyAllPrefill = <?php echo wp_json_encode($inbox_reply_all_prefill ?? ["to" => "", "cc" => ""]); ?>;
      var inboxFlagPalette = <?php echo wp_json_encode($inbox_flag_palette ?? []); ?>;
      function inboxToast(message, type) {
        type = type || "info";
        var colors = {
          success: { bg: "#0f766e", icon: "✓" },
          error: { bg: "#dc2626", icon: "✕" },
          info: { bg: "#1d4ed8", icon: "ℹ" }
        };
        var c = colors[type] || colors.info;
        var toast = document.createElement("div");
        toast.style.cssText = "position:fixed;bottom:24px;right:24px;z-index:9999;padding:12px 16px;border-radius:10px;background:" + c.bg + ";color:#fff;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px;box-shadow:0 8px 24px rgba(0,0,0,.2);max-width:360px;";
        toast.innerHTML = "<span>" + c.icon + "</span><span>" + message + "</span>";
        document.body.appendChild(toast);
        setTimeout(function () {
          toast.style.opacity = "0";
          toast.style.transition = "opacity .2s";
          setTimeout(function () { toast.remove(); }, 200);
        }, 4000);
      }
      function inboxShowSkeleton(container, count) {
        if (!container) {
          return;
        }
        var n = count || 4;
        var i;
        var html = "";
        for (i = 0; i < n; i++) {
          html += "<div style=\"padding:10px 12px;border:1px solid var(--border);border-radius:12px;background:#fff;margin-bottom:4px\">";
          html += "<div class=\"crm-inbox-skeleton\" style=\"height:12px;width:60%;margin-bottom:8px\"></div>";
          html += "<div class=\"crm-inbox-skeleton\" style=\"height:10px;width:40%;margin-bottom:10px\"></div>";
          html += "<div class=\"crm-inbox-skeleton\" style=\"height:10px;width:80%\"></div></div>";
        }
        container.innerHTML = html;
      }
      function inboxOpenCompose() {
        var wrap = document.getElementById("inbox-compose-wrap");
        if (!wrap) {
          return;
        }
        wrap.classList.add("is-open");
        var ta = document.getElementById("inbox-reply-body");
        if (ta) {
          setTimeout(function () { ta.focus(); }, 50);
        }
      }
      window.inboxToggleCompose = function () {
        var wrap = document.getElementById("inbox-compose-wrap");
        if (!wrap) {
          return;
        }
        wrap.classList.toggle("is-open");
        if (wrap.classList.contains("is-open")) {
          var ta = document.getElementById("inbox-reply-body");
          if (ta) {
            setTimeout(function () { ta.focus(); }, 50);
          }
        }
      };
      function inboxAiDraftReply(offerId) {
        offerId = parseInt(offerId, 10) || 0;
        var bodyEl = document.getElementById("inbox-reply-body");
        var subEl = document.getElementById("inbox-reply-subject");
        var btns = document.querySelectorAll(".ai-draft-btn");
        if (offerId <= 0 || !bodyEl) {
          return;
        }
        var hint = bodyEl.value || "";
        var origTexts = [];
        btns.forEach(function (b, i) {
          origTexts[i] = b.textContent ? b.textContent : "";
          b.textContent = "Generuję…";
          b.classList.add("is-loading");
          b.disabled = true;
        });
        fetch(upsCrmAjax, {
          method: "POST",
          headers: {"Content-Type": "application/x-www-form-urlencoded"},
          body: new URLSearchParams({
            action: "upsellio_inbox_ai_draft",
            nonce: upsCrmNonce,
            offer_id: String(offerId),
            hint: hint
          })
        }).then(function (r) { return r.json(); }).then(function (data) {
          btns.forEach(function (b, i) {
            b.textContent = origTexts[i] || "";
            b.classList.remove("is-loading");
            b.disabled = false;
          });
          if (data.success && data.data && data.data.reply_body) {
            bodyEl.value = data.data.reply_body;
            if (subEl && data.data.reply_subject && String(data.data.reply_subject).trim() !== "") {
              subEl.value = data.data.reply_subject;
            }
            bodyEl.style.borderColor = "var(--teal)";
            setTimeout(function () { bodyEl.style.borderColor = ""; }, 2000);
            inboxToast("Szkic wstawiony — sprawdź przed wysyłką", "info");
          } else {
            inboxToast("Nie udało się wygenerować szkicu", "error");
          }
        }).catch(function () {
          btns.forEach(function (b, i) {
            b.textContent = origTexts[i] || "";
            b.classList.remove("is-loading");
            b.disabled = false;
          });
          inboxToast("Błąd sieci — spróbuj ponownie", "error");
        });
      }
      function inboxFillReply(offerId, rmode) {
        var toEl = document.getElementById("inbox-reply-to");
        var ccEl = document.getElementById("inbox-reply-cc");
        if (!toEl || !ccEl) {
          return;
        }
        if (rmode === "reply_all") {
          toEl.value = (inboxReplyAllPrefill && inboxReplyAllPrefill.to) ? inboxReplyAllPrefill.to : "";
          ccEl.value = (inboxReplyAllPrefill && inboxReplyAllPrefill.cc) ? inboxReplyAllPrefill.cc : "";
        } else {
          toEl.value = (inboxReplyPrefill && inboxReplyPrefill.to) ? inboxReplyPrefill.to : "";
          ccEl.value = (inboxReplyPrefill && inboxReplyPrefill.cc) ? inboxReplyPrefill.cc : "";
        }
        inboxOpenCompose();
      }
      function inboxMarkUnread(offerId) {
        offerId = parseInt(offerId, 10) || 0;
        if (offerId <= 0) {
          return;
        }
        fetch(upsCrmAjax, {
          method: "POST",
          headers: {"Content-Type": "application/x-www-form-urlencoded"},
          body: new URLSearchParams({
            action: "upsellio_inbox_mark_unread",
            nonce: upsCrmNonce,
            offer_id: String(offerId)
          })
        }).then(function (r) { return r.json(); }).then(function (data) {
          if (!data.success) {
            return;
          }
          var card = document.querySelector(".crm-inbox-thread-card[data-offer-id=\"" + offerId + "\"]");
          if (card) {
            card.classList.add("is-unread");
          }
          var hot = document.querySelector(".side-badge.hot");
          if (hot) {
            hot.textContent = String(parseInt(hot.textContent || "0", 10) + 1);
          }
          inboxToast("Oznaczono jako nieodczytane", "success");
        });
      }
      function inboxSetFlag(offerId, flag) {
        offerId = parseInt(offerId, 10) || 0;
        fetch(upsCrmAjax, {
          method: "POST",
          headers: {"Content-Type": "application/x-www-form-urlencoded"},
          body: new URLSearchParams({
            action: "upsellio_inbox_set_flag",
            nonce: upsCrmNonce,
            offer_id: String(offerId),
            flag: flag || ""
          })
        }).then(function (r) { return r.json(); }).then(function (data) {
          if (!data.success) {
            return;
          }
          var cur = data.data && data.data.flag !== undefined ? String(data.data.flag) : String(flag || "");
          document.querySelectorAll(".crm-inbox-flag-btn").forEach(function (btnEl) {
            var fk = btnEl.getAttribute("data-flag") || "";
            btnEl.classList.toggle("active", cur !== "" && fk === cur);
          });
          var card = document.querySelector(".crm-inbox-thread-card[data-offer-id=\"" + offerId + "\"]");
          if (card) {
            var dot = card.querySelector(".crm-inbox-badge--flagdot");
            var meta = cur && inboxFlagPalette[cur] ? inboxFlagPalette[cur] : null;
            var hex = meta && meta.hex ? meta.hex : "";
            if (!cur) {
              if (dot) {
                dot.remove();
              }
            } else if (hex) {
              if (dot) {
                dot.style.background = hex;
              } else {
                var span = document.createElement("span");
                span.className = "crm-inbox-badge crm-inbox-badge--flagdot";
                span.style.background = hex;
                span.title = "flag";
                var badges = card.querySelector(".crm-inbox-thread-badges");
                if (badges) {
                  badges.appendChild(span);
                }
              }
            }
          }
        });
      }
      function inboxMoveFolder(offerId, folderId) {
        offerId = parseInt(offerId, 10) || 0;
        if (offerId <= 0 || !folderId) {
          return;
        }
        fetch(upsCrmAjax, {
          method: "POST",
          headers: {"Content-Type": "application/x-www-form-urlencoded"},
          body: new URLSearchParams({
            action: "upsellio_inbox_move_folder",
            nonce: upsCrmNonce,
            offer_id: String(offerId),
            folder_id: String(folderId)
          })
        }).then(function (r) { return r.json(); }).then(function (data) {
          if (data.success) {
            location.reload();
          }
        });
      }
      function inboxSyncMailbox(btn) {
        var status = document.getElementById("inbox-sync-mailbox-status");
        var orig = btn && btn.textContent ? btn.textContent : "";
        if (btn) {
          btn.disabled = true;
          btn.textContent = "Synchronizacja…";
        }
        if (status) {
          status.textContent = "";
          status.style.color = "";
        }
        fetch(upsCrmAjax, {
          method: "POST",
          headers: {"Content-Type": "application/x-www-form-urlencoded"},
          body: new URLSearchParams({
            action: "upsellio_inbox_sync_mailbox",
            nonce: upsCrmNonce
          })
        }).then(function (r) { return r.json(); }).then(function (data) {
          if (btn) {
            btn.disabled = false;
            btn.textContent = orig;
          }
          if (!status) {
            if (data.success) {
              location.reload();
            }
            return;
          }
          if (data.success) {
            status.style.color = "#0f766e";
            status.textContent = data.data && data.data.message ? data.data.message : "Gotowe.";
            setTimeout(function () { location.reload(); }, 1200);
          } else {
            status.style.color = "#b91c1c";
            status.textContent = data.data && data.data.message ? data.data.message : "Nie udało się zsynchronizować.";
          }
        }).catch(function () {
          if (btn) {
            btn.disabled = false;
            btn.textContent = orig;
          }
          if (status) {
            status.style.color = "#b91c1c";
            status.textContent = "Błąd sieci.";
          }
        });
      }
      function inboxFolderManage(op) {
        if (op !== "create") {
          return;
        }
        var nameInput = document.getElementById("inbox-new-folder-name");
        var name = nameInput ? nameInput.value.trim() : "";
        if (!name) {
          alert("Podaj nazwę folderu.");
          return;
        }
        var parentBtn = document.getElementById("inbox-folder-create-btn");
        var parentId = parentBtn && parentBtn.getAttribute("data-parent") ? parentBtn.getAttribute("data-parent") : "fld_inbox";
        fetch(upsCrmAjax, {
          method: "POST",
          headers: {"Content-Type": "application/x-www-form-urlencoded"},
          body: new URLSearchParams({
            action: "upsellio_inbox_folder_manage",
            nonce: upsCrmNonce,
            op: "create",
            parent_id: parentId,
            name: name
          })
        }).then(function (r) { return r.json(); }).then(function (data) {
          if (data.success) {
            location.reload();
          } else {
            alert("Nie udało się utworzyć folderu.");
          }
        });
      }
      function inboxSendCrmMail(offerId, mode) {
        mode = mode || "reply";
        var subjectEl, bodyEl, btn, status, useFooterEl, btnDefault;
        var toEl, ccEl, bccEl, htmlEl, autoEl;
        var payload = {
          action: "upsellio_inbox_send_reply",
          nonce: upsCrmNonce,
          offer_id: "",
          subject: "",
          body: "",
          to: "",
          cc: "",
          bcc: "",
          use_footer: "1",
          body_is_html: "0",
          trigger_automation: "0"
        };
        if (mode === "compose") {
          subjectEl = document.getElementById("inbox-compose-subject");
          bodyEl = document.getElementById("inbox-compose-body");
          btn = document.getElementById("inbox-compose-send-btn");
          status = document.getElementById("inbox-compose-status");
          useFooterEl = document.getElementById("inbox-compose-use-footer");
          toEl = document.getElementById("inbox-compose-to");
          ccEl = document.getElementById("inbox-compose-cc");
          bccEl = document.getElementById("inbox-compose-bcc");
          htmlEl = document.getElementById("inbox-compose-html");
          autoEl = document.getElementById("inbox-compose-trigger-automation");
          btnDefault = "Wyślij wiadomość";
          offerId = 0;
          var toTrim = toEl ? toEl.value.trim() : "";
          if (!toTrim) {
            inboxToast("Podaj adres odbiorcy w polu „Do”.", "error");
            if (status) {
              status.textContent = "Podaj adres odbiorcy w polu „Do”.";
              status.style.display = "block";
              status.style.color = "#e24b4a";
            }
            return;
          }
        } else {
          subjectEl = document.getElementById("inbox-reply-subject");
          bodyEl = document.getElementById("inbox-reply-body");
          btn = document.getElementById("inbox-send-btn");
          status = document.getElementById("inbox-reply-status");
          useFooterEl = document.getElementById("inbox-reply-use-footer");
          toEl = document.getElementById("inbox-reply-to");
          ccEl = document.getElementById("inbox-reply-cc");
          bccEl = document.getElementById("inbox-reply-bcc");
          htmlEl = document.getElementById("inbox-reply-html");
          autoEl = document.getElementById("inbox-reply-trigger-automation");
          btnDefault = "Wyślij →";
          offerId = parseInt(offerId, 10) || 0;
        }
        if (!bodyEl || bodyEl.value.trim() === "") {
          inboxToast("Wpisz treść wiadomości.", "error");
          if (status) {
            status.textContent = "Wpisz treść wiadomości.";
            status.style.display = "block";
            status.style.color = "#e24b4a";
          }
          if (mode !== "compose") {
            inboxOpenCompose();
          }
          return;
        }
        if (!btn) {
          return;
        }
        payload.offer_id = String(offerId);
        payload.subject = subjectEl ? subjectEl.value : "";
        payload.body = bodyEl.value;
        payload.to = toEl ? toEl.value : "";
        payload.cc = ccEl ? ccEl.value : "";
        payload.bcc = bccEl ? bccEl.value : "";
        payload.use_footer = useFooterEl && useFooterEl.checked ? "1" : "0";
        payload.body_is_html = htmlEl && htmlEl.checked ? "1" : "0";
        payload.trigger_automation = autoEl && autoEl.checked ? "1" : "0";
        btn.disabled = true;
        btn.textContent = "Wysyłanie…";
        var errMap = {
          no_recipient: "Brak odbiorcy — uzupełnij pole „Do”.",
          empty_body: "Treść wiadomości jest pusta.",
          invalid_params: "Nieprawidłowe parametry żądania.",
          bad_nonce: "Sesja wygasła — odśwież stronę.",
          forbidden: "Brak uprawnień.",
          forbidden_offer: "Brak dostępu do tej oferty.",
          no_client_email: "Brak odbiorcy — wpisz adres w polu „Do” albo przypisz do klienta z uzupełnionym e-mailem.",
          send_failed: "Wysyłka nie powiodła się — sprawdź Ustawienia → Mail / Skrzynki → Logi.",
          attachments_unavailable: "Obsługa załączników jest niedostępna na serwerze."
        };
        function inboxSendFail(data) {
          var code = data && data.data && data.data.message ? String(data.data.message) : "";
          var msg = errMap[code] || code || "nieznany";
          inboxToast("Nie udało się wysłać — " + msg, "error");
          if (status) {
            status.textContent = "Błąd wysyłki: " + msg;
            status.style.color = "#e24b4a";
            status.style.display = "block";
          }
          btn.disabled = false;
          btn.textContent = btnDefault;
        }
        function inboxSendOk() {
          inboxToast(mode === "compose" ? "Wiadomość wysłana" : "Wiadomość wysłana", "success");
          if (status) {
            status.textContent = "Wysłano. Odświeżanie…";
            status.style.color = "#1d9e75";
            status.style.display = "block";
          }
          bodyEl.value = "";
          if (mode === "compose" && subjectEl) {
            subjectEl.value = "";
          }
          var filesEl = document.getElementById("inbox-compose-files");
          if (mode === "compose" && filesEl) {
            filesEl.value = "";
          }
          setTimeout(function () { location.reload(); }, 1200);
        }
        if (mode === "compose") {
          var fd = new FormData();
          fd.append("action", "upsellio_inbox_send_reply");
          fd.append("nonce", upsCrmNonce);
          fd.append("inbox_send_mode", "compose_free");
          fd.append("offer_id", "0");
          fd.append("subject", payload.subject);
          fd.append("body", payload.body);
          fd.append("to", payload.to);
          fd.append("cc", payload.cc);
          fd.append("bcc", payload.bcc);
          fd.append("use_footer", payload.use_footer);
          fd.append("body_is_html", payload.body_is_html);
          fd.append("trigger_automation", payload.trigger_automation);
          var filesElC = document.getElementById("inbox-compose-files");
          if (filesElC && filesElC.files && filesElC.files.length) {
            for (var fi = 0; fi < filesElC.files.length; fi++) {
              fd.append("inbox_files[]", filesElC.files[fi]);
            }
          }
          fetch(upsCrmAjax, { method: "POST", body: fd }).then(function (r) { return r.json(); }).then(function (data) {
            if (data.success) {
              inboxSendOk();
            } else {
              inboxSendFail(data);
            }
          }).catch(function () {
            inboxToast("Błąd sieci — spróbuj ponownie", "error");
            if (status) {
              status.textContent = "Błąd sieci.";
              status.style.color = "#e24b4a";
              status.style.display = "block";
            }
            btn.disabled = false;
            btn.textContent = btnDefault;
          });
          return;
        }
        fetch(upsCrmAjax, {
          method: "POST",
          headers: {"Content-Type": "application/x-www-form-urlencoded"},
          body: new URLSearchParams(payload)
        }).then(function (r) { return r.json(); }).then(function (data) {
          if (data.success) {
            inboxSendOk();
          } else {
            inboxSendFail(data);
          }
        }).catch(function () {
          inboxToast("Błąd sieci — spróbuj ponownie", "error");
          if (status) {
            status.textContent = "Błąd sieci.";
            status.style.color = "#e24b4a";
            status.style.display = "block";
          }
          btn.disabled = false;
          btn.textContent = btnDefault;
        });
      }
      function inboxClassify(offerId, classification, btn) {
        var msgId = btn && btn.getAttribute ? (btn.getAttribute("data-msg-id") || "") : "";
        fetch(upsCrmAjax, {
          method: "POST",
          headers: {"Content-Type": "application/x-www-form-urlencoded"},
          body: new URLSearchParams({
            action: "upsellio_inbox_classify",
            nonce: upsCrmNonce,
            offer_id: String(offerId),
            classification: classification,
            message_id: msgId
          })
        }).then(function (r) { return r.json(); }).then(function (data) {
          if (data.success) {
            var row = btn.closest(".crm-inbox-classify-row");
            var labels = {positive: "pozytywna", price_objection: "obiekcja cenowa", timing_objection: "obiekcja terminu", no_priority: "brak priorytetu", other: "inna"};
            var lb = labels[classification] || classification;
            if (row) {
              row.outerHTML = "<div style=\"margin-top:8px;font-size:11px;color:var(--text-3)\">Sklasyfikowano: <strong>" + lb + "</strong></div>";
            }
          }
        });
      }
      function inboxMoveStage(offerId, stage) {
        fetch(upsCrmAjax, {
          method: "POST",
          headers: {"Content-Type": "application/x-www-form-urlencoded"},
          body: new URLSearchParams({
            action: "upsellio_crm_move_offer_pipeline",
            nonce: upsCrmNonce,
            offer_id: String(offerId),
            stage: stage
          })
        }).then(function (r) { return r.json(); }).then(function (data) {
          if (data.success) {
            location.reload();
          }
        });
      }
      (function () {
        var msgs = document.getElementById("inbox-messages");
        if (msgs) {
          msgs.scrollTop = msgs.scrollHeight;
        }
      })();
      (function () {
        document.querySelectorAll(".inbox-drag-handle").forEach(function (el) {
          el.addEventListener("dragstart", function (e) {
            e.dataTransfer.setData("text/plain", el.getAttribute("data-offer-id") || "");
            e.dataTransfer.effectAllowed = "move";
            document.querySelectorAll(".inbox-folder-drop").forEach(function (z) {
              z.classList.add("is-drop-target");
            });
          });
          el.addEventListener("dragend", function () {
            document.querySelectorAll(".inbox-folder-drop").forEach(function (z) {
              z.classList.remove("is-drop-target", "is-drop-over");
            });
          });
        });
        document.querySelectorAll(".inbox-folder-drop").forEach(function (el) {
          el.addEventListener("dragover", function (e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = "move";
            el.classList.add("is-drop-over");
          });
          el.addEventListener("dragleave", function () {
            el.classList.remove("is-drop-over");
          });
          el.addEventListener("drop", function (e) {
            e.preventDefault();
            el.classList.remove("is-drop-over");
            var oid = e.dataTransfer.getData("text/plain");
            var fid = el.getAttribute("data-folder-id");
            if (oid && fid) {
              inboxMoveFolder(parseInt(oid, 10), fid);
            }
          });
        });
      })();
      (function () {
        var ft = document.getElementById("inbox-folder-toggle");
        var fs = document.getElementById("inbox-folder-section");
        if (ft && fs) {
          ft.addEventListener("click", function () {
            fs.classList.toggle("is-open");
            var open = fs.classList.contains("is-open");
            ft.setAttribute("aria-expanded", open ? "true" : "false");
          });
        }
      })();
      (function () {
        document.querySelectorAll(".crm-inbox-thread-card").forEach(function (card) {
          card.addEventListener("click", function () {
            if (window.innerWidth <= 860) {
              var shell = document.querySelector(".crm-inbox-shell");
              if (shell) {
                shell.classList.add("has-active-thread");
              }
            }
          });
        });
        var backBtn = document.getElementById("inbox-back-to-list");
        if (backBtn) {
          backBtn.addEventListener("click", function () {
            var shell = document.querySelector(".crm-inbox-shell");
            if (shell) {
              shell.classList.remove("has-active-thread");
            }
          });
        }
        var sideToggle = document.getElementById("inbox-side-toggle");
        var sidePanel = document.querySelector(".crm-inbox-side");
        if (sideToggle && sidePanel) {
          sideToggle.addEventListener("click", function (ev) {
            ev.stopPropagation();
            sidePanel.classList.toggle("is-open");
          });
          document.addEventListener("click", function (e) {
            if (!sidePanel.contains(e.target) && !sideToggle.contains(e.target)) {
              sidePanel.classList.remove("is-open");
            }
          });
        }
        document.addEventListener("keydown", function (e) {
          if (e.key === "Escape") {
            var sp = document.querySelector(".crm-inbox-side");
            if (sp) {
              sp.classList.remove("is-open");
            }
          }
        });
      })();
      </script>
      <?php endif; ?>
      <?php if ($view === "settings" && isset($settings_tab) && $settings_tab === "mailbox") : ?>
      <script>
      (function () {
        var ajax = <?php echo wp_json_encode(admin_url("admin-ajax.php")); ?>;
        var nonce = <?php echo wp_json_encode(wp_create_nonce("ups_crm_app_action")); ?>;
        var btn = document.getElementById("ups-mailbox-log-clear-btn");
        if (!btn) {
          return;
        }
        btn.addEventListener("click", function () {
          if (!confirm("Wyczyścić cały dziennik skrzynki?")) {
            return;
          }
          btn.disabled = true;
          fetch(ajax, {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: new URLSearchParams({ action: "upsellio_crm_clear_mailbox_log", nonce: nonce })
          }).then(function (r) { return r.json(); }).then(function (data) {
            btn.disabled = false;
            if (data.success) {
              location.reload();
            } else {
              alert("Nie udało się wyczyścić logu.");
            }
          }).catch(function () {
            btn.disabled = false;
            alert("Błąd sieci.");
          });
        });
      })();
      </script>
      <?php endif; ?>
    </body>
    </html>
    <?php
    exit;
}
add_action("template_redirect", "upsellio_crm_app_template_redirect", 0);
