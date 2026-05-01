<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_crm_app_template_redirect()
{
    $is_crm_page = is_page("crm-app");
    if (!$is_crm_page) {
        return;
    }
    if (!upsellio_crm_app_user_can_access()) {
        auth_redirect();
    }
    upsellio_crm_app_handle_post_actions();

    $view = isset($_GET["view"]) ? sanitize_key((string) wp_unslash($_GET["view"])) : "dashboard";
    if (!in_array($view, ["dashboard", "leads", "account-360", "clients", "client-edit", "contacts", "offers", "template-studio", "services", "pipeline", "contracts", "contract-detail", "followups", "tasks", "calendar", "prospecting", "inbox", "alerts", "analytics", "engine", "settings"], true)) {
        $view = "dashboard";
    }
    $template_studio_tab = isset($_GET["tab"]) ? sanitize_key((string) wp_unslash($_GET["tab"])) : "offer";
    if (!in_array($template_studio_tab, ["offer", "contract"], true)) {
        $template_studio_tab = "offer";
    }
    $settings_tab = isset($_GET["settings_tab"]) ? sanitize_key((string) wp_unslash($_GET["settings_tab"])) : "general";
    if (!in_array($settings_tab, ["general", "mailbox", "scoring", "offer-template", "contract-template", "automation"], true)) {
        $settings_tab = "general";
    }
    $selected_client_id = isset($_GET["client_id"]) ? (int) wp_unslash($_GET["client_id"]) : 0;
    $selected_client = $selected_client_id > 0 ? get_post($selected_client_id) : null;
    if (!($selected_client instanceof WP_Post) || $selected_client->post_type !== "crm_client") {
        $selected_client = null;
        $selected_client_id = 0;
    }
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
    }

    $crm_inbox_ctx = null;
    if ($view === "inbox") {
        $crm_inbox_ctx = [
            "folder" => $inbox_folder_sel,
            "flag" => $inbox_flag_sel,
            "bucket" => $inbox_bucket_sel,
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

    $pl_label = static function ($value, $type = "generic") {
        $value = (string) $value;
        $maps = [
            "subscription" => ["active" => "aktywny", "paused" => "wstrzymany", "cancelled" => "anulowany"],
            "offer_status" => ["open" => "otwarty", "won" => "wygrany", "lost" => "przegrany"],
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
      <title>CRM App - Upsellio</title>
      <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
      <style>
        :root{--bg:#fafaf7;--surface:#ffffff;--text:#0a1410;--text-2:#3d3d38;--text-3:#7c7c74;--border:#e7e7e1;--teal:#0d9488;--teal-hover:#0f766e;--teal-soft:#ccfbf1;--teal-line:#99f6e4;--sidebar:260px;--font-display:'Syne',sans-serif;--font-body:'DM Sans',sans-serif}
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:var(--font-body);background:var(--bg);color:var(--text);line-height:1.6;font-size:15px}
        a{text-decoration:none;color:inherit}
        .layout{display:grid;grid-template-columns:var(--sidebar) 1fr;min-height:100vh}
        .side{background:#0a1410;color:#d9e8fb;display:flex;flex-direction:column;position:sticky;top:0;height:100vh;overflow-y:auto}
        .side-brand{padding:22px 20px 18px;border-bottom:1px solid rgba(255,255,255,.07)}
        .side-logo{display:flex;align-items:center;gap:10px}
        .side-logo-mark{width:34px;height:34px;border-radius:10px;background:linear-gradient(180deg,#21ab82 0%,#0f766e 100%);color:#fff;display:grid;place-items:center;font-family:var(--font-display);font-weight:800;font-size:17px;flex-shrink:0}
        .side-logo-img{height:34px;width:auto;max-width:160px;object-fit:contain}
        .side-logo-name{font-family:var(--font-display);font-size:20px;font-weight:800;letter-spacing:-.5px;color:#fff}
        .side-logo-sub{font-size:10px;color:rgba(255,255,255,.38);letter-spacing:.6px;text-transform:uppercase}
        .side-nav{flex:1;padding:16px 10px}
        .side-section{font-size:10px;font-weight:700;letter-spacing:1.4px;text-transform:uppercase;color:rgba(255,255,255,.28);padding:12px 10px 6px}
        .side-link{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:12px;color:rgba(255,255,255,.6);font-size:13.5px;font-weight:500;transition:.15s ease;margin-bottom:2px}
        .side-link:hover{background:rgba(255,255,255,.07);color:#fff}
        .side-link.active{background:rgba(13,148,136,.18);color:#5eead4;border:1px solid rgba(13,148,136,.22)}
        .main{min-height:100vh;display:flex;flex-direction:column}
        .topbar{background:var(--surface);border-bottom:1px solid var(--border);padding:0 32px;height:64px;display:flex;align-items:center;justify-content:space-between;gap:16px;position:sticky;top:0;z-index:40}
        .topbar-title{font-family:var(--font-display);font-size:20px;font-weight:700}
        .topbar-right{display:flex;align-items:center;gap:12px}
        .topbar-search{display:flex;align-items:center;gap:8px;background:var(--bg);border:1px solid var(--border);border-radius:999px;padding:8px 14px;font-size:13px;color:var(--text-3);width:240px}
        .content{padding:24px;flex:1}
        .muted{color:var(--text-3)}
        .grid{display:grid;grid-template-columns:repeat(12,1fr);gap:14px}
        .card{grid-column:span 12;background:var(--surface);border:1px solid var(--border);border-radius:18px;padding:20px}
        h2{font-family:var(--font-display);font-size:18px;margin-bottom:10px}
        .kpi{grid-column:span 3}
        .kpi b{font-family:var(--font-display);font-size:30px;display:block;line-height:1.1}
        .half{grid-column:span 6}
        table{width:100%;border-collapse:collapse;font-size:13px}
        th,td{padding:10px 12px;border-bottom:1px solid var(--border);text-align:left;vertical-align:top}
        thead th{font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:var(--text-3)}
        .btn{display:inline-flex;align-items:center;gap:7px;border-radius:999px;padding:9px 16px;font-weight:700;font-size:13px;border:1px solid transparent;cursor:pointer;background:var(--teal);color:#fff}
        .btn.alt{background:#fff;border-color:var(--border);color:var(--text)}
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
        .side-badge{margin-left:auto;font-size:11px;font-weight:700;padding:2px 8px;border-radius:999px;line-height:1.2}
        .side-badge.hot{background:#e24b4a;color:#fff}
        .offer-dlg-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
        label.odlg-hint{font-size:11px;color:var(--text-3);margin:-4px 0 2px}
        @media(max-width:1100px){.layout{grid-template-columns:1fr}.side{display:none}.kpi,.half{grid-column:span 12}.topbar{padding:0 16px}.content{padding:16px}}
        @media(max-width:720px){.offer-dlg-grid{grid-template-columns:1fr}}
      </style>
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
          "contacts" => "Kontakty B2B",
          "offers" => "Oferty",
          "template-studio" => "Generator szablonów",
          "services" => "Katalog usług",
          "pipeline" => "Lejek",
          "contracts" => "Umowy",
          "contract-detail" => "Szczegóły umowy",
          "followups" => "Follow-upy",
          "tasks" => "Taski",
          "calendar" => "Kalendarz",
          "prospecting" => "Prospecting (zimne maile)",
          "inbox" => "Inbox",
          "alerts" => "Alerty",
          "analytics" => "Analityka i statystyki",
          "engine" => "Silnik sprzedaży",
          "settings" => "Ustawienia",
      ];
      $current_view_title = isset($view_titles[$view]) ? (string) $view_titles[$view] : "CRM App";
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
          <nav class="side-nav">
            <div class="side-section">Główne</div>
            <a class="side-link <?php echo $view === "dashboard" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "dashboard"], home_url("/crm-app/"))); ?>">Pulpit</a>
            <a class="side-link <?php echo $view === "leads" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "leads"], home_url("/crm-app/"))); ?>">Leady</a>
            <a class="side-link <?php echo $view === "account-360" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "account-360"], home_url("/crm-app/"))); ?>">Karta 360</a>
            <a class="side-link <?php echo $view === "clients" || $view === "client-edit" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "clients"], home_url("/crm-app/"))); ?>">Klienci</a>
            <a class="side-link <?php echo $view === "contacts" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "contacts"], home_url("/crm-app/"))); ?>">Kontakty B2B</a>
            <a class="side-link <?php echo $view === "offers" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "offers"], home_url("/crm-app/"))); ?>">Oferty</a>
            <a class="side-link <?php echo $view === "template-studio" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "template-studio"], home_url("/crm-app/"))); ?>">Generator szablonów</a>
            <a class="side-link <?php echo $view === "services" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "services"], home_url("/crm-app/"))); ?>">Katalog usług</a>
            <a class="side-link <?php echo $view === "pipeline" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "pipeline"], home_url("/crm-app/"))); ?>">Lejek</a>
            <a class="side-link <?php echo $view === "contracts" || $view === "contract-detail" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "contracts"], home_url("/crm-app/"))); ?>">Umowy</a>
            <div class="side-section">Automatyzacja</div>
            <a class="side-link <?php echo $view === "followups" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "followups"], home_url("/crm-app/"))); ?>">Follow-upy</a>
            <a class="side-link <?php echo $view === "tasks" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "tasks"], home_url("/crm-app/"))); ?>">Taski</a>
            <a class="side-link <?php echo $view === "calendar" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "calendar"], home_url("/crm-app/"))); ?>">Kalendarz</a>
            <a class="side-link <?php echo $view === "prospecting" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "prospecting"], home_url("/crm-app/"))); ?>">Prospecting (zimne maile)</a>
            <a class="side-link <?php echo $view === "inbox" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "inbox"], home_url("/crm-app/"))); ?>">Inbox<?php if ($crm_inbox_unread_total > 0) : ?><span class="side-badge hot"><?php echo (int) $crm_inbox_unread_total; ?></span><?php endif; ?></a>
            <a class="side-link <?php echo $view === "alerts" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "alerts"], home_url("/crm-app/"))); ?>">Alerty</a>
            <a class="side-link <?php echo $view === "analytics" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "analytics"], home_url("/crm-app/"))); ?>">Analityka & Atrybucja</a>
            <div class="side-section">Konfiguracja</div>
            <a class="side-link <?php echo $view === "engine" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "engine"], home_url("/crm-app/"))); ?>">Silnik sprzedaży</a>
            <a class="side-link <?php echo $view === "settings" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "settings"], home_url("/crm-app/"))); ?>">Ustawienia</a>
          </nav>
        </aside>
        <main class="main">
          <div class="topbar">
            <div class="topbar-title"><?php echo esc_html($current_view_title); ?></div>
            <div class="topbar-right">
              <div class="topbar-search">Szukaj klientów, ofert i zadań...</div>
              <a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "engine"], home_url("/crm-app/"))); ?>">Silnik sprzedaży</a>
            </div>
          </div>
          <div class="content">
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
            <?php if ($view === "dashboard") : ?>
              <?php
              $dashboard_client_total = upsellio_crm_count_posts_all_statuses("crm_client");
              $st_vis = upsellio_crm_app_post_statuses_visible();
              $hot_q = new WP_Query([
                  "post_type" => "crm_offer",
                  "post_status" => $st_vis,
                  "posts_per_page" => 1,
                  "fields" => "ids",
                  "meta_query" => [["key" => "_ups_offer_hot_offer", "value" => "1"]],
              ]);
              $hot_offers_count = (int) $hot_q->found_posts;
              wp_reset_postdata();
              $won_q = new WP_Query([
                  "post_type" => "crm_offer",
                  "post_status" => $st_vis,
                  "posts_per_page" => 1,
                  "fields" => "ids",
                  "meta_query" => [["key" => "_ups_offer_status", "value" => "won"]],
              ]);
              $won_offers = (int) $won_q->found_posts;
              wp_reset_postdata();
              $active_mrr = upsellio_crm_app_compute_active_mrr();
              $global_activity = upsellio_crm_app_collect_recent_activity_entries(48, false);
              $recent_activity = array_slice($global_activity, 0, 20);
              ?>
              <section class="card kpi"><span class="muted">Klienci</span><b><?php echo esc_html((string) $dashboard_client_total); ?></b></section>
              <section class="card kpi"><span class="muted">Gorące oferty</span><b><?php echo esc_html((string) $hot_offers_count); ?></b></section>
              <section class="card kpi"><span class="muted">Wygrane oferty</span><b><?php echo esc_html((string) $won_offers); ?></b></section>
              <section class="card kpi"><span class="muted">Aktywne MRR</span><b><?php echo esc_html(number_format($active_mrr, 0, ",", " ")); ?> PLN</b></section>
              <section class="card half">
                <h2>Najnowsi klienci</h2>
                <table><thead><tr><th>Klient</th><th>Status</th><th>MRR</th><th></th></tr></thead><tbody>
                <?php foreach ($clients as $client) : $cid = (int) $client->ID; ?>
                  <tr>
                    <td><?php echo esc_html((string) $client->post_title); ?></td>
                    <td><?php echo esc_html($pl_label((string) get_post_meta($cid, "_ups_client_subscription_status", true), "subscription")); ?></td>
                    <td><?php echo esc_html(number_format((float) get_post_meta($cid, "_ups_client_monthly_value", true), 2, ",", " ")); ?> PLN</td>
                    <td><a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "client-edit", "client_id" => $cid], home_url("/crm-app/"))); ?>">Edycja</a></td>
                  </tr>
                <?php endforeach; ?>
                </tbody></table>
              </section>
              <section class="card half">
                <h2>Umowy - ostatnie zdarzenia</h2>
                <?php foreach ($contracts as $contract) : $tid = function_exists("upsellio_contracts_get_timeline") ? upsellio_contracts_get_timeline((int) $contract->ID) : []; $last = !empty($tid) ? end($tid) : []; ?>
                  <div class="timeline-item">
                    <span class="muted"><?php echo esc_html(isset($last["ts"]) ? (string) $last["ts"] : "brak"); ?></span>
                    <span><strong><?php echo esc_html((string) $contract->post_title); ?></strong> - <?php echo esc_html(isset($last["label"]) ? (string) $last["label"] : "Brak zdarzen"); ?></span>
                  </div>
                <?php endforeach; ?>
              </section>
              <section class="card">
                <h2>Co robić dziś (priorytet decyzyjny)</h2>
                <p class="muted" style="margin-bottom:10px">Taski otwarte sortowane po <code>priority_score</code> (wpływ ×40% + prawdopodobieństwo ×40% + presja terminu ×20%).</p>
                <table><thead><tr><th>Priorytet</th><th>Task</th><th>Deal</th><th>Kontekst oferty</th><th>Termin</th><th>Status</th></tr></thead><tbody>
                <?php
                $dash_tasks = 0;
                foreach ($tasks as $dtask) {
                    $dtid = (int) $dtask->ID;
                    $dst = (string) get_post_meta($dtid, "_upsellio_task_status", true);
                    if (in_array($dst, ["done", "cancelled"], true)) {
                        continue;
                    }
                    $dash_tasks++;
                    if ($dash_tasks > 12) {
                        break;
                    }
                    $d_oid = (int) get_post_meta($dtid, "_upsellio_task_offer_id", true);
                    $d_due = (int) get_post_meta($dtid, "_upsellio_task_due_at", true);
                    $d_pri = (int) get_post_meta($dtid, "_upsellio_task_priority_score", true);
                    $brief_html = $d_oid > 0 && function_exists("upsellio_sales_engine_format_offer_task_brief_html") ? upsellio_sales_engine_format_offer_task_brief_html($d_oid) : "";
                ?>
                  <tr>
                    <td><strong><?php echo esc_html((string) ($d_pri > 0 ? $d_pri : "—")); ?></strong></td>
                    <td><?php echo esc_html((string) $dtask->post_title); ?></td>
                    <td><?php echo $d_oid > 0 ? esc_html((string) get_the_title($d_oid)) : "—"; ?></td>
                    <td><?php echo $brief_html !== "" ? $brief_html : '<span class="muted">—</span>'; ?></td>
                    <td><?php echo $d_due > 0 ? esc_html((string) wp_date("Y-m-d H:i", $d_due)) : "—"; ?></td>
                    <td><?php echo esc_html((string) $dst); ?></td>
                  </tr>
                <?php } ?>
                <?php if ($dash_tasks === 0) : ?><tr><td colspan="6" class="muted">Brak otwartych tasków lub wszystkie zamknięte.</td></tr><?php endif; ?>
                </tbody></table>
              </section>
              <section class="card">
                <h2>Ostatnia historia działań CRM</h2>
                <?php if (empty($recent_activity)) : ?>
                  <p class="muted">Brak logów aktywności.</p>
                <?php else : ?>
                  <?php foreach ($recent_activity as $activity_row) : ?>
                    <?php if (!is_array($activity_row)) { continue; } $entry = isset($activity_row["entry"]) && is_array($activity_row["entry"]) ? $activity_row["entry"] : []; ?>
                    <div class="timeline-item">
                      <span class="muted"><?php echo esc_html((string) ($entry["ts"] ?? "")); ?></span>
                      <span>
                        <strong><?php echo esc_html((string) ($activity_row["entity_type"] ?? "")); ?> #<?php echo esc_html((string) ($activity_row["entity_id"] ?? 0)); ?></strong>
                        - <?php echo esc_html((string) ($entry["message"] ?? ($entry["event"] ?? "event"))); ?>
                      </span>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </section>
            <?php endif; ?>
            <?php if ($view === "leads") : ?>
              <section class="card">
                <h2>Leady i kwalifikacja</h2>
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
                  <thead><tr><th>Lead</th><th>Typ</th><th>Status</th><th>Score 0–100</th><th>Prawd. deal</th><th>Temp.</th><th>Budżet</th><th>Decyzja</th><th>Akcja</th></tr></thead>
                  <tbody>
                    <?php foreach ($leads as $lead) : $lid = (int) $lead->ID; ?>
                      <tr>
                        <td><?php echo esc_html((string) $lead->post_title); ?><br/><small><?php echo esc_html((string) get_post_meta($lid, "_ups_lead_email", true)); ?></small></td>
                        <td><?php echo esc_html((string) get_post_meta($lid, "_ups_lead_type", true)); ?></td>
                        <td><?php echo esc_html((string) get_post_meta($lid, "_ups_lead_qualification_status", true)); ?></td>
                        <td><?php echo esc_html((string) (int) get_post_meta($lid, "_ups_lead_score_0_100", true)); ?></td>
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
                    <?php if (empty($leads)) : ?><tr><td colspan="9">Brak leadów.</td></tr><?php endif; ?>
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
                  <thead><tr><th>Klient</th><th>Email</th><th>Firma</th><th>Status</th><th>MRR</th><th>Akcje</th></tr></thead>
                  <tbody>
                  <?php foreach ($clients as $client) : $cid = (int) $client->ID; ?>
                    <tr>
                      <td><?php echo esc_html((string) $client->post_title); ?></td>
                      <td><?php echo esc_html((string) get_post_meta($cid, "_ups_client_email", true)); ?></td>
                      <td><?php echo esc_html((string) get_post_meta($cid, "_ups_client_company", true)); ?></td>
                      <td><span class="badge"><?php echo esc_html($pl_label((string) get_post_meta($cid, "_ups_client_subscription_status", true), "subscription")); ?></span></td>
                      <td><?php echo esc_html(number_format((float) get_post_meta($cid, "_ups_client_monthly_value", true), 2, ",", " ")); ?> PLN</td>
                      <td><a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "client-edit", "client_id" => $cid], home_url("/crm-app/"))); ?>">Edytuj</a></td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </section>
            <?php endif; ?>
            <?php if ($view === "client-edit") : ?>
              <section class="card">
                <h2>Edycja klienta</h2>
                <?php if (!$selected_client instanceof WP_Post) : ?>
                  <p class="muted">Wybierz klienta z listy klientow.</p>
                <?php else : ?>
                  <?php
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
                  $ce_log = get_post_meta($ce_id, "_ups_client_activity_log", true);
                  if (!is_array($ce_log)) { $ce_log = []; }
                  ?>
                  <form method="post" class="grid2">
                    <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                    <input type="hidden" name="ups_action" value="save_client" />
                    <input type="hidden" name="crm_view" value="client-edit" />
                    <input type="hidden" name="client_id" value="<?php echo esc_attr((string) $ce_id); ?>" />
                    <input type="text" name="client_title" value="<?php echo esc_attr((string) $selected_client->post_title); ?>" required />
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
                    <input type="date" name="client_cancellation_date" value="<?php echo esc_attr((string) get_post_meta($ce_id, "_ups_client_cancellation_date", true)); ?>" />
                    <textarea name="client_cancellation_reason"><?php echo esc_textarea((string) get_post_meta($ce_id, "_ups_client_cancellation_reason", true)); ?></textarea>
                    <textarea name="client_notes" placeholder="Notatki klienta (wewnętrzne)"><?php echo esc_textarea($ce_notes); ?></textarea>
                    <button class="btn" type="submit">Zapisz klienta</button>
                  </form>
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
              </section>
            <?php endif; ?>
            <?php if ($view === "offers") : ?>
              <?php
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
                <h2>Oferty</h2>
                <p class="muted" style="margin-bottom:12px">Lista aktywnych dealów. Konfiguracja strony publicznej (layout, zakres, pytania) jest w oknie <strong>budowniczka</strong>.</p>
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
                      <input type="hidden" name="crm_view" value="offers" />
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
                  var clientsEl=document.getElementById("ups-crm-clients-json");
                  var clients=[];
                  try{clients=clientsEl?JSON.parse(clientsEl.textContent||"[]"):[];}catch(e){clients=[];}
                  function openDlg(){overlay.classList.add("open");overlay.setAttribute("aria-hidden","false");}
                  function closeDlg(){overlay.classList.remove("open");overlay.setAttribute("aria-hidden","true");}
                  document.getElementById("ups-open-offer-builder").addEventListener("click",function(){
                    form.reset();
                    document.getElementById("offer_id_field").value="";
                    document.querySelectorAll("#ups-offer-builder-form input, #ups-offer-builder-form select, #ups-offer-builder-form textarea").forEach(function(el){if(el.type==="checkbox")el.checked=false;});
                    document.getElementById("fld_offer_status").value="open";
                    var g=document.querySelector("#pane-p-scope input[name=offer_has_google]");if(g)g.checked=true;
                    var m=document.querySelector("#pane-p-scope input[name=offer_has_meta]");if(m)m.checked=true;
                    var w=document.querySelector("#pane-p-scope input[name=offer_has_web]");if(w)w.checked=false;
                    openDlg();
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
                  });
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
                      form.submit();
                    });
                  });
                })();
                </script>
                <table>
                  <thead><tr><th>Klient</th><th>Oferta</th><th>Status</th><th>Etap</th><th>Score / prawd.</th><th>Gorąca</th><th>Win / loss</th><th>Notatki</th><th>Publiczny / edycja</th><th>Follow-up</th></tr></thead>
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
                      <td><span class="badge gray"><?php echo esc_html($pl_label((string) get_post_meta($offer_id, "_ups_offer_status", true), "offer_status")); ?></span></td>
                      <td><span class="badge dark"><?php echo esc_html($pl_label((string) get_post_meta($offer_id, "_ups_offer_stage", true), "stage")); ?></span></td>
                      <td><small><?php echo esc_html((string) (int) get_post_meta($offer_id, "_ups_offer_lead_score_0_100", true)); ?> / <?php echo esc_html((string) (int) get_post_meta($offer_id, "_ups_offer_deal_probability_0_100", true)); ?>%</small><br/><small class="muted"><?php echo esc_html((string) get_post_meta($offer_id, "_ups_offer_temperature", true)); ?></small></td>
                      <td><?php echo (string) get_post_meta($offer_id, "_ups_offer_hot_offer", true) === "1" ? "🔥" : "—"; ?></td>
                      <td>
                        <form method="post" style="display:flex;flex-direction:column;gap:6px;max-width:220px">
                          <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                          <input type="hidden" name="ups_action" value="save_offer_outcomes" />
                          <input type="hidden" name="crm_view" value="offers" />
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
                        <a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "offers", "offer_editor_id" => $offer_id], home_url("/crm-app/"))); ?>">Budowniczek</a>
                      </td>
                      <td>
                        <form method="post">
                          <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                          <input type="hidden" name="ups_action" value="send_offer_followup_now" />
                          <input type="hidden" name="crm_view" value="offers" />
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
                  <form method="post" class="grid2">
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
                  <form method="post" class="grid2">
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
              <section class="card">
                <h2>Lejek sprzedaży (przeciągnij i upuść)</h2>
                <div class="pipeline">
                  <?php
                  $pipeline_cols = [
                      "awareness" => "Świadomość",
                      "consideration" => "Rozważanie",
                      "decision" => "Decyzja",
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
                          $bucket = ($offer_status === "won" || $offer_status === "lost") ? $offer_status : $offer_stage;
                          if ($bucket !== $pipeline_key) {
                              continue;
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
              ?>
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
                <table>
                  <thead><tr><th>Prio</th><th>Task</th><th>Deal</th><th>Kontekst oferty</th><th>Owner</th><th>Termin</th><th>Czas</th><th>Status</th><th>Akcje</th></tr></thead>
                  <tbody>
                    <?php foreach ($tasks as $task) : ?>
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
                          <a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "tasks", "task_id" => $tid], home_url("/crm-app/"))); ?>">Podgląd/Edytuj</a>
                          <form method="post" style="display:inline-flex;gap:6px">
                            <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                            <input type="hidden" name="ups_action" value="complete_task" />
                            <input type="hidden" name="crm_view" value="tasks" />
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
                            <input type="hidden" name="task_id" value="<?php echo esc_attr((string) $tid); ?>" />
                            <button class="btn alt" type="submit">Usuń</button>
                          </form>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                    <?php if (empty($tasks)) : ?>
                      <tr><td colspan="9">Brak tasków.</td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </section>
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
              ?>
              <section class="card" style="grid-column:span 12;padding:0;overflow:hidden">
                <div style="display:grid;grid-template-columns:152px minmax(248px,300px) 1fr;grid-template-rows:minmax(0,1fr);min-height:calc(100vh - 168px);max-height:calc(100vh - 168px);overflow:hidden;gap:0;border-radius:18px;background:var(--surface);align-items:stretch">

                  <div style="border-right:1px solid var(--border);overflow-y:auto;display:flex;flex-direction:column;min-height:0;max-height:100%;background:rgba(0,0,0,.02)">
                    <div style="padding:8px 10px;border-bottom:1px solid var(--border);font-size:10px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:var(--text-3)">Foldery</div>
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
                         class="inbox-folder-drop"
                         data-folder-id="<?php echo esc_attr($fid); ?>"
                         style="display:block;padding:7px <?php echo (int) $pad; ?>px;font-size:12px;text-decoration:none;color:inherit;background:<?php echo $active ? "rgba(13,148,136,.12)" : "transparent"; ?>;border-bottom:1px solid var(--border);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        <?php echo esc_html($nm); ?>
                      </a>
                            <?php
                            $inbox_render_folder_branch($fid, $depth + 1);
                        }
                    };
                    $inbox_render_folder_branch("", 0);
                    ?>
                    <div style="padding:8px;border-top:1px solid var(--border);border-bottom:1px solid var(--border)">
                      <input type="text" id="inbox-new-folder-name" placeholder="Nowy podfolder…" style="width:100%;font-size:11px;padding:5px 7px;border:1px solid var(--border);border-radius:6px;background:var(--bg);margin-bottom:6px" />
                      <?php
                      $inbox_sel_folder_meta = function_exists("upsellio_inbox_folder_find") ? upsellio_inbox_folder_find($inbox_folder_sel) : null;
                      $inbox_sel_folder_name = is_array($inbox_sel_folder_meta) ? (string) ($inbox_sel_folder_meta["name"] ?? "") : "";
                      if ($inbox_sel_folder_name === "") {
                          $inbox_sel_folder_name = "Główny";
                      }
                      ?>
                      <button type="button" class="btn alt" id="inbox-folder-create-btn" data-parent="<?php echo esc_attr($inbox_folder_sel); ?>" style="font-size:11px;padding:4px 8px;width:100%" onclick="inboxFolderManage('create')">Utwórz w „<?php echo esc_html($inbox_sel_folder_name); ?>”</button>
                    </div>
                  </div>

                  <div style="border-right:1px solid var(--border);overflow-y:auto;display:flex;flex-direction:column;min-height:0;max-height:100%">
                    <div style="padding:10px 12px;border-bottom:1px solid var(--border);display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:8px">
                      <span style="font-size:12px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-3);line-height:1.35">
                        <?php echo (int) $inbox_list_total; ?> wątków<?php if ($inbox_total_pages > 1) : ?> · str. <?php echo (int) $inbox_list_page; ?>/<?php echo (int) $inbox_total_pages; ?><?php endif; ?>
                        <span style="display:block;font-size:10px;font-weight:600;color:var(--text-3);text-transform:none;margin-top:2px">Na liście: <?php echo count($inbox_offers_visible); ?> · szukaj po tytule oferty, treści wątku i e-mailu klienta</span>
                      </span>
                      <span style="display:flex;flex-wrap:wrap;gap:6px;align-items:center">
                        <button type="button" class="btn alt" id="inbox-sync-mailbox-btn" style="font-size:11px;padding:5px 10px" title="Pobierz nieprzeczytane wiadomości z IMAP (do 25 na raz). Wymaga włączonej skrzynki w ustawieniach." onclick="inboxSyncMailbox(this)">Synchronizuj skrzynkę</button>
                        <a class="btn alt" style="font-size:11px;padding:5px 10px" href="<?php echo $inbox_compose_url; ?>">Nowa wiadomość</a>
                        <?php if ($inbox_compose) : ?>
                          <a class="btn alt" style="font-size:11px;padding:5px 10px" href="<?php echo $inbox_list_url; ?>">Lista wątków</a>
                        <?php endif; ?>
                      </span>
                    </div>
                    <div style="padding:0 12px 8px;font-size:10px;color:var(--text-3);line-height:1.4;border-bottom:1px solid var(--border)">
                      Ostatnie pobranie IMAP: <strong><?php echo esc_html($inbox_mailbox_last_disp); ?></strong>
                      <?php if (!$inbox_mailbox_enabled) : ?>
                        · <a href="<?php echo $inbox_settings_mail_url; ?>" style="color:inherit">Skonfiguruj skrzynkę</a>
                      <?php endif; ?>
                      <span id="inbox-sync-mailbox-status" style="display:block;margin-top:4px;font-weight:600;color:var(--text-2)"></span>
                    </div>
                    <div style="padding:8px 12px;border-bottom:1px solid var(--border);display:flex;flex-wrap:wrap;gap:6px;align-items:center">
                      <span style="font-size:10px;color:var(--text-3);width:100%;font-weight:600">Widok skrzynki</span>
                      <?php
                      $inbox_bucket_tabs = [
                          "all" => "Wszystkie",
                          "received" => "Odebrane",
                          "sent" => "Wysłane",
                      ];
                      foreach ($inbox_bucket_tabs as $bk => $blabel) :
                          $q_bk = $inbox_nav_base;
                          unset($q_bk["inbox_offer"], $q_bk["inbox_paged"]);
                          if ($bk === "all") {
                              unset($q_bk["inbox_bucket"]);
                          } else {
                              $q_bk["inbox_bucket"] = $bk;
                          }
                          $u_bk = esc_url(add_query_arg($q_bk, home_url("/crm-app/")));
                          $active_bk = $inbox_bucket_sel === $bk;
                          ?>
                      <a href="<?php echo $u_bk; ?>" style="font-size:11px;padding:4px 10px;border-radius:999px;text-decoration:none;border:1px solid var(--border);color:var(--text-2);<?php echo $active_bk ? "background:rgba(13,148,136,.14);font-weight:700;border-color:rgba(13,148,136,.35)" : ""; ?>"><?php echo esc_html($blabel); ?></a>
                      <?php endforeach; ?>
                      <p class="muted" style="font-size:10px;margin:4px 0 0;line-height:1.4;width:100%">Filtr wg <strong>ostatniej</strong> wiadomości w wątku: od klienta (odebrane) lub wychodzącej z CRM (wysłane). Działa razem z folderami po lewej.</p>
                    </div>
                    <form method="get" action="<?php echo esc_url(home_url("/crm-app/")); ?>" style="padding:8px 12px;border-bottom:1px solid var(--border);display:flex;flex-wrap:wrap;gap:8px;align-items:center;background:rgba(0,0,0,.015)">
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
                      <input type="search" name="inbox_search" value="<?php echo esc_attr($inbox_search_q); ?>" placeholder="Szukaj: temat, e-mail…" maxlength="160" autocomplete="off" style="flex:1;min-width:160px;font-size:12px;padding:7px 10px;border:1px solid var(--border);border-radius:8px;background:var(--bg);font:inherit;color:var(--text)" />
                      <button type="submit" class="btn alt" style="font-size:11px;padding:6px 12px">Szukaj</button>
                      <?php if ($inbox_search_q !== "") :
                          $inbox_clear_q = ["view" => "inbox"];
                          if ($inbox_folder_sel !== "fld_inbox") {
                              $inbox_clear_q["inbox_folder"] = $inbox_folder_sel;
                          }
                          if ($inbox_bucket_sel !== "all") {
                              $inbox_clear_q["inbox_bucket"] = $inbox_bucket_sel;
                          }
                          if ($inbox_flag_sel !== "") {
                              $inbox_clear_q["inbox_flag"] = $inbox_flag_sel;
                          }
                          ?>
                        <a class="btn alt" style="font-size:11px;padding:6px 12px" href="<?php echo esc_url(add_query_arg($inbox_clear_q, home_url("/crm-app/"))); ?>">Wyczyść</a>
                      <?php endif; ?>
                    </form>
                    <div style="padding:8px 12px;border-bottom:1px solid var(--border);display:flex;flex-wrap:wrap;gap:6px;align-items:center">
                      <span style="font-size:10px;color:var(--text-3);width:100%">Flagi</span>
                      <?php
                      $q_all = $inbox_nav_base;
                      unset($q_all["inbox_flag"], $q_all["inbox_offer"], $q_all["inbox_paged"]);
                      $u_all = esc_url(add_query_arg($q_all, home_url("/crm-app/")));
                      ?>
                      <a href="<?php echo $u_all; ?>" style="font-size:11px;padding:2px 8px;border-radius:999px;text-decoration:none;border:1px solid var(--border);color:var(--text-2);<?php echo $inbox_flag_sel === "" ? "background:rgba(13,148,136,.12)" : ""; ?>">Wszystkie</a>
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
                    <?php foreach ($inbox_offers_visible as $io) :
                        $ioid = (int) $io->ID;
                        $sum = function_exists("upsellio_inbox_get_thread_summary") ? upsellio_inbox_get_thread_summary($ioid) : [];
                        $icid = (int) get_post_meta($ioid, "_ups_offer_client_id", true);
                        $iname = $icid > 0 ? get_the_title($icid) : "—";
                        $is_active = $ioid === $inbox_offer_id;
                        $has_unread = ((int) ($sum["unread"] ?? 0)) > 0;
                        $last_cls = (string) ($sum["last_cls"] ?? "");
                        $cls_color = $last_cls !== "" && isset($inbox_cls_colors[$last_cls]) ? $inbox_cls_colors[$last_cls] : "";
                        $last_ts_raw = (string) ($sum["last_ts"] ?? "");
                        $last_ts_disp = $last_ts_raw !== "" && strtotime($last_ts_raw) ? esc_html(wp_date("d.m H:i", strtotime($last_ts_raw))) : "—";
                        $awaiting = ($sum["last_direction"] ?? "") === "in";
                        $thread_flag = function_exists("upsellio_inbox_offer_flag") ? upsellio_inbox_offer_flag($ioid) : "";
                        $thread_flag_hex =
                            $thread_flag !== "" && isset($inbox_flag_palette[$thread_flag])
                                ? (string) ($inbox_flag_palette[$thread_flag]["hex"] ?? "")
                                : "";
                        $q_th = array_merge($inbox_nav_base, ["inbox_offer" => $ioid]);
                        unset($q_th["inbox_paged"]);
                        $href_th = esc_url(add_query_arg($q_th, home_url("/crm-app/")));
                        ?>
                      <div style="display:flex;align-items:stretch;border-bottom:1px solid var(--border);background:<?php echo $is_active ? "rgba(13,148,136,.07)" : "transparent"; ?>">
                        <span class="inbox-drag-handle" draggable="true" data-offer-id="<?php echo (int) $ioid; ?>" title="Przenieś do folderu" style="cursor:grab;padding:14px 8px;flex-shrink:0;color:var(--text-3);font-size:14px;line-height:1.2;user-select:none;align-self:center">⠿</span>
                        <a href="<?php echo $href_th; ?>"
                           style="display:block;padding:14px 16px 14px 4px;text-decoration:none;color:inherit;flex:1;min-width:0;transition:background .15s">
                          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:3px;gap:8px">
                            <span style="display:flex;align-items:center;gap:6px;min-width:0">
                              <?php if ($thread_flag_hex !== "") : ?>
                                <span style="width:8px;height:8px;border-radius:50%;background:<?php echo esc_attr($thread_flag_hex); ?>;flex-shrink:0" title="Flaga"></span>
                              <?php endif; ?>
                              <span style="font-size:13px;font-weight:<?php echo $has_unread ? "700" : "600"; ?>;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?php echo esc_html($iname); ?></span>
                            </span>
                            <span style="font-size:11px;color:var(--text-3);flex-shrink:0"><?php echo $last_ts_disp; ?></span>
                          </div>
                          <div style="font-size:12px;color:var(--text-2);margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            <?php echo ($sum["last_direction"] ?? "") === "out" ? "↑ " : "↓ "; ?>
                            <?php echo esc_html((string) ($sum["last_body"] ?? "—")); ?>
                          </div>
                          <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                            <?php if ($has_unread) : ?>
                              <span style="background:#1d9e75;color:#fff;border-radius:999px;font-size:10px;font-weight:700;padding:1px 7px"><?php echo (int) $sum["unread"]; ?></span>
                            <?php endif; ?>
                            <?php if ($awaiting) : ?>
                              <span style="background:rgba(245,158,11,.15);color:#b45309;border-radius:999px;font-size:10px;font-weight:700;padding:1px 7px">czeka na odp.</span>
                            <?php endif; ?>
                            <?php if ($cls_color !== "") : ?>
                              <span style="width:7px;height:7px;border-radius:50%;background:<?php echo esc_attr($cls_color); ?>;flex-shrink:0"></span>
                              <span style="font-size:11px;color:<?php echo esc_attr($cls_color); ?>"><?php echo esc_html($inbox_cls_short[$last_cls] ?? $last_cls); ?></span>
                            <?php endif; ?>
                            <span style="font-size:11px;color:var(--text-3);margin-left:auto;max-width:46%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?php echo esc_html(get_the_title($ioid)); ?></span>
                          </div>
                        </a>
                      </div>
                    <?php endforeach; ?>
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
                      ?>
                      <div style="padding:32px 20px;text-align:center;color:var(--text-3);font-size:13px;line-height:1.55">
                        <?php if ($inbox_search_q !== "") : ?>
                          Brak wyników dla „<?php echo esc_html($inbox_search_q); ?>”.
                          <br /><a href="<?php echo esc_url(add_query_arg($inbox_empty_clear, home_url("/crm-app/"))); ?>">Wyczyść wyszukiwanie</a>
                        <?php elseif ($inbox_list_total === 0) : ?>
                          Brak konwersacji spełniających filtry.<br />Wyślij pierwszy follow-up lub zbierz odpowiedź z IMAP.
                        <?php else : ?>
                          Brak pozycji na tej stronie (łącznie <?php echo (int) $inbox_list_total; ?> wątków). Skorzystaj z paginacji poniżej.
                        <?php endif; ?>
                      </div>
                    <?php endif; ?>
                    <?php if ($inbox_total_pages > 1) : ?>
                      <div style="padding:10px 12px;border-top:1px solid var(--border);display:flex;flex-wrap:wrap;gap:10px;justify-content:center;align-items:center;font-size:12px;color:var(--text-2)">
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

                  <div style="display:flex;flex-direction:column;min-height:0;max-height:100%;overflow:hidden">

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
                      ?>
                      <div style="border-bottom:1px solid var(--border);padding:12px 20px;display:flex;align-items:center;gap:12px;flex-shrink:0">
                        <div style="min-width:0">
                          <div style="font-size:14px;font-weight:700"><?php echo esc_html($inbox_client_id > 0 ? get_the_title($inbox_client_id) : $inbox_offer_title); ?></div>
                          <div style="font-size:12px;color:var(--text-3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?php echo esc_html($inbox_offer_title); ?> · <?php echo esc_html($inbox_stage_disp); ?></div>
                        </div>
                        <div style="margin-left:auto;display:flex;gap:8px;flex-shrink:0;flex-wrap:wrap">
                          <button type="button" class="btn alt" style="font-size:12px;padding:6px 12px" onclick="inboxMarkUnread(<?php echo (int) $inbox_offer_id; ?>)">Oznacz nieodczytane</button>
                          <a class="btn alt" href="<?php echo $inbox_edit_url; ?>" style="font-size:12px;padding:6px 12px">Edytuj ofertę</a>
                          <?php if ($inbox_offer_public !== "") : ?>
                            <a class="btn alt" href="<?php echo esc_url($inbox_offer_public); ?>" target="_blank" rel="noopener noreferrer" style="font-size:12px;padding:6px 12px">Strona oferty ↗</a>
                          <?php endif; ?>
                        </div>
                      </div>
                      <div style="border-bottom:1px solid var(--border);padding:8px 20px;display:flex;flex-wrap:wrap;gap:10px;align-items:center;background:rgba(0,0,0,.02);flex-shrink:0">
                        <span style="font-size:11px;color:var(--text-3);margin-right:4px">Szybkie akcje:</span>
                        <button type="button" class="btn alt" style="font-size:11px;padding:4px 10px" onclick="inboxFillReply(<?php echo (int) $inbox_offer_id; ?>,'reply')">Odpowiedz</button>
                        <button type="button" class="btn alt" style="font-size:11px;padding:4px 10px" onclick="inboxFillReply(<?php echo (int) $inbox_offer_id; ?>,'reply_all')">Odpowiedz wszystkim</button>
                        <span style="font-size:11px;color:var(--text-3);margin-left:8px">Flaga wątku:</span>
                        <?php foreach ($inbox_flag_palette as $fk => $fmeta) :
                            $fhx = (string) ($fmeta["hex"] ?? "#999");
                            $fon = $inbox_current_flag === $fk;
                            ?>
                        <button type="button" onclick="inboxSetFlag(<?php echo (int) $inbox_offer_id; ?>, <?php echo wp_json_encode($fk); ?>)" title="<?php echo esc_attr((string) ($fmeta["label"] ?? $fk)); ?>" style="width:20px;height:20px;border-radius:50%;border:2px solid <?php echo $fon ? "#0f766e" : "transparent"; ?>;background:<?php echo esc_attr($fhx); ?>;cursor:pointer;padding:0;box-shadow:0 0 0 1px var(--border)"></button>
                        <?php endforeach; ?>
                        <button type="button" class="btn alt" style="font-size:11px;padding:4px 8px" onclick="inboxSetFlag(<?php echo (int) $inbox_offer_id; ?>,'')">× wyczyść</button>
                      </div>

                      <div style="flex:1 1 0;min-height:0;overflow-y:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch;padding:20px;display:flex;flex-direction:column;gap:14px" id="inbox-messages">
                        <?php if (empty($inbox_thread)) : ?>
                          <div style="text-align:center;color:var(--text-3);font-size:13px;padding:40px 0">Brak wiadomości w tym wątku.</div>
                        <?php endif; ?>
                        <?php foreach ($inbox_thread as $msg) :
                            if (!is_array($msg)) {
                                continue;
                            }
                            $is_out = ($msg["direction"] ?? "") === "out";
                            $cls = (string) ($msg["classification"] ?? "");
                            $cls_labels = ["positive" => "pozytywna", "price_objection" => "obiekcja cenowa", "timing_objection" => "obiekcja terminu", "no_priority" => "brak priorytetu", "other" => "inna klasa"];
                            $cls_label = $cls_labels[$cls] ?? "";
                            $msg_id_esc = esc_attr((string) ($msg["id"] ?? ""));
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
                            }
                            ?>
                          <div style="display:flex;flex-direction:column;align-items:<?php echo $is_out ? "flex-end" : "flex-start"; ?>;gap:4px">
                            <div style="font-size:11px;color:var(--text-3)"><?php echo $is_out ? "↑ Ty" : "↓ Klient"; ?> · <?php echo esc_html(wp_date("d.m.Y H:i", strtotime((string) ($msg["ts"] ?? "")) ?: time())); ?></div>
                            <div style="max-width:72%;background:<?php echo $is_out ? "var(--teal-soft)" : "var(--bg)"; ?>;border:1px solid <?php echo $is_out ? "var(--teal-line)" : "var(--border)"; ?>;border-radius:<?php echo $is_out ? "14px 14px 4px 14px" : "14px 14px 14px 4px"; ?>;padding:12px 16px">
                              <div style="font-size:12px;font-weight:600;color:var(--text-3);margin-bottom:5px"><?php echo esc_html((string) ($msg["subject"] ?? "")); ?></div>
                              <?php if ($is_out && (string) ($msg["to"] ?? "") !== "") : ?>
                                <div style="font-size:11px;color:var(--text-3);margin-bottom:4px">Do: <?php echo esc_html((string) ($msg["to"] ?? "")); ?><?php echo (string) ($msg["cc"] ?? "") !== "" ? " · Dw: " . esc_html((string) ($msg["cc"] ?? "")) : ""; ?></div>
                              <?php endif; ?>
                              <div style="font-size:14px;color:var(--text);white-space:pre-wrap;line-height:1.55"><?php echo esc_html((string) ($msg["body_plain"] ?? "")); ?></div>
                              <?php if ($cls_label !== "") : ?>
                                <div style="margin-top:8px;font-size:11px;color:var(--text-3)">Klasyfikacja AI: <strong><?php echo esc_html($cls_label); ?></strong></div>
                              <?php endif; ?>
                              <?php if (!$is_out && $cls === "") : ?>
                                <div style="margin-top:8px;display:flex;gap:6px;flex-wrap:wrap" class="inbox-classify-row">
                                  <?php foreach (["positive" => "✓ Pozytywna", "price_objection" => "$ Cena", "timing_objection" => "⏱ Timing", "no_priority" => "○ Brak priorytetu", "other" => "? Inna"] as $k => $label) : ?>
                                    <button type="button" data-msg-id="<?php echo $msg_id_esc; ?>" onclick="inboxClassify(<?php echo (int) $inbox_offer_id; ?>, <?php echo wp_json_encode($k); ?>, this)" style="font-size:11px;padding:2px 9px;border-radius:999px;border:1px solid var(--border);background:var(--bg);cursor:pointer"><?php echo esc_html($label); ?></button>
                                  <?php endforeach; ?>
                                </div>
                              <?php endif; ?>
                            </div>
                            <?php if ($src_note !== "") : ?>
                              <div style="font-size:10px;color:var(--text-3)"><?php echo esc_html($src_note); ?></div>
                            <?php endif; ?>
                          </div>
                        <?php endforeach; ?>
                      </div>

                      <div style="border-top:1px solid var(--border);padding:12px 20px;background:var(--surface);flex-shrink:0;font-size:12px;color:var(--text-2);max-height:42vh;overflow-y:auto;overscroll-behavior:contain">
                        <div style="font-weight:700;margin-bottom:8px;color:var(--text);font-size:13px">Kontekst deala</div>
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px">
                          <div><span class="muted">Score</span><br /><strong><?php echo (int) get_post_meta($inbox_offer_id, "_ups_offer_score", true); ?></strong></div>
                          <div><span class="muted">Hot index</span><br /><strong><?php echo $inbox_hot_ix > 0 ? (int) $inbox_hot_ix : "—"; ?></strong></div>
                          <div><span class="muted">Etap</span><br /><strong><?php echo esc_html($inbox_stage_disp); ?></strong></div>
                          <div style="grid-column:1/-1"><span class="muted">Ostatnia wizyta na ofercie</span><br /><strong><?php echo esc_html($inbox_last_seen_disp); ?></strong></div>
                        </div>
                        <div style="margin-top:10px;display:flex;flex-wrap:wrap;gap:6px;align-items:center">
                          <span class="muted" style="font-size:11px;margin-right:4px">Szybki etap:</span>
                          <?php foreach (["awareness" => "Świadomość", "consideration" => "Rozważanie", "decision" => "Decyzja"] as $st_key => $st_lab) : ?>
                            <button type="button" class="btn alt" style="font-size:11px;padding:4px 10px;cursor:pointer" onclick="inboxMoveStage(<?php echo (int) $inbox_offer_id; ?>, <?php echo wp_json_encode($st_key); ?>)"><?php echo esc_html($st_lab); ?></button>
                          <?php endforeach; ?>
                        </div>
                      </div>

                      <div style="border-top:1px solid var(--border);padding:16px 20px;background:var(--surface);flex-shrink:0;max-height:38vh;overflow-y:auto;overscroll-behavior:contain">
                        <p class="muted" style="font-size:11px;margin:0 0 10px;line-height:1.45">Wątek CRM — edytuj odbiorców i treść przed wysyłką. „Odpowiedz wszystkim” uzupełnia listę na podstawie nagłówków z wiadomości w wątku (wymaga zapisanych pól Do/Dw w meta).</p>
                        <label style="display:block;font-size:11px;font-weight:600;margin-bottom:4px;color:var(--text-2)">Do</label>
                        <input type="text" id="inbox-reply-to" value="<?php echo esc_attr((string) ($inbox_reply_prefill["to"] ?? "")); ?>"
                               style="width:100%;font-size:13px;padding:7px 10px;border:1px solid var(--border);border-radius:8px;background:var(--bg);margin-bottom:8px" />
                        <label style="display:block;font-size:11px;font-weight:600;margin-bottom:4px;color:var(--text-2)">Dw</label>
                        <input type="text" id="inbox-reply-cc" value="<?php echo esc_attr((string) ($inbox_reply_prefill["cc"] ?? "")); ?>"
                               style="width:100%;font-size:13px;padding:7px 10px;border:1px solid var(--border);border-radius:8px;background:var(--bg);margin-bottom:8px" />
                        <label style="display:block;font-size:11px;font-weight:600;margin-bottom:4px;color:var(--text-2)">Udw</label>
                        <input type="text" id="inbox-reply-bcc" placeholder="opcjonalnie"
                               style="width:100%;font-size:13px;padding:7px 10px;border:1px solid var(--border);border-radius:8px;background:var(--bg);margin-bottom:10px" />
                        <div style="margin-bottom:8px">
                          <label style="display:block;font-size:11px;font-weight:600;margin-bottom:4px;color:var(--text-2)">Temat</label>
                          <input type="text" id="inbox-reply-subject" placeholder="Temat (opcjonalny)"
                                 value="<?php echo esc_attr("Re: " . $inbox_offer_title); ?>"
                                 style="width:100%;font-size:13px;padding:7px 10px;border:1px solid var(--border);border-radius:8px;background:var(--bg)" />
                        </div>
                        <label style="display:flex;align-items:center;gap:8px;margin-bottom:8px;font-size:13px;cursor:pointer">
                          <input type="checkbox" id="inbox-reply-html" style="margin:0" />
                          <span>Treść jako HTML</span>
                        </label>
                        <textarea id="inbox-reply-body" rows="4" placeholder="Napisz odpowiedź…"
                                  style="width:100%;resize:vertical;font-size:14px;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--bg);font-family:inherit"></textarea>
                        <label style="display:flex;align-items:flex-start;gap:8px;margin-top:10px;font-size:13px;cursor:pointer;line-height:1.35">
                          <input type="checkbox" id="inbox-reply-use-footer" checked style="margin-top:3px;flex-shrink:0" />
                          <span>Dołącz domyślną stopkę e-mail (HTML + CSS z ustawień)</span>
                        </label>
                        <label style="display:flex;align-items:flex-start;gap:8px;margin-top:8px;font-size:13px;cursor:pointer;line-height:1.35">
                          <input type="checkbox" id="inbox-reply-trigger-automation" style="margin-top:3px;flex-shrink:0" />
                          <span>Po wysłaniu: hook <code>upsellio_crm_inbox_mail_sent</code> (automaty / integracje)</span>
                        </label>
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;gap:12px;flex-wrap:wrap">
                          <span style="font-size:12px;color:var(--text-3)">Wyślij z: <strong><?php echo esc_html((string) ($inbox_settings["from_email"] ?? "")); ?></strong></span>
                          <button type="button" onclick="inboxSendCrmMail(<?php echo (int) $inbox_offer_id; ?>,'reply')" class="btn" id="inbox-send-btn">Wyślij</button>
                        </div>
                        <div id="inbox-reply-status" style="font-size:12px;margin-top:6px;display:none"></div>
                      </div>

                    <?php else : ?>
                      <div style="display:flex;align-items:center;justify-content:center;flex:1 1 0;min-height:0;color:var(--text-3);font-size:14px;padding:24px;text-align:center;line-height:1.5"><?php echo $inbox_compose ? "" : "Wybierz konwersację z listy lub "; ?><a href="<?php echo $inbox_compose_url; ?>">utwórz nową wiadomość</a>.</div>
                    <?php endif; ?>
                  </div>
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
              ?>
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
              <section class="card">
                <h2>GA4 -> CRM: jakość kanałów (feedback loop)</h2>
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
                      <tr><td colspan="5">Brak danych GA4 agregatów. Włącz sync i wyślij pierwszą paczkę na endpoint.</td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </section>
              <section class="card">
                <h2>Warstwa decyzyjna: właściciele</h2>
                <table>
                  <thead><tr><th>Owner</th><th>Deale</th><th>Won</th><th>Lost</th><th>Przychód won</th></tr></thead>
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
                    <?php if (empty($decision_analytics["owners"])) : ?><tr><td colspan="5">Brak danych.</td></tr><?php endif; ?>
                  </tbody>
                </table>
              </section>
              <section class="card">
                <h2>Źródła (UTM) — wolumen i wygrane</h2>
                <table>
                  <thead><tr><th>Źródło</th><th>Deale</th><th>Won</th><th>Przychód</th></tr></thead>
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
                <h2>Wydajność przedziałów cenowych (won)</h2>
                <table>
                  <thead><tr><th>Przedział</th><th>Liczba won</th><th>Przychód</th></tr></thead>
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
              <section class="card">
                <h2>Pętla marketing → revenue (ROAS / ROI)</h2>
                <p class="muted">Koszty kampanii ustaw w <strong>Ustawienia → Automatyzacje</strong> (CSV). Klucz: źródło + kampania jak w UTM leadów i deali.</p>
                <table>
                  <thead><tr><th>Źródło</th><th>Kampania</th><th>Koszt</th><th>Leady</th><th>Won</th><th>Przychód</th><th>ROAS</th><th>ROI %</th></tr></thead>
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
                      <tr><td colspan="8">Brak wierszy — dodaj koszty CSV lub poczekaj na leady/deale z UTM.</td></tr>
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
            <?php if ($view === "settings") : ?>
              <section class="card">
                <h2>Podwidoki ustawień</h2>
                <p>
                  <a class="btn <?php echo $settings_tab === "general" ? "" : "alt"; ?>" href="<?php echo esc_url(add_query_arg(["view" => "settings", "settings_tab" => "general"], home_url("/crm-app/"))); ?>">Ogólne</a>
                  <a class="btn <?php echo $settings_tab === "mailbox" ? "" : "alt"; ?>" href="<?php echo esc_url(add_query_arg(["view" => "settings", "settings_tab" => "mailbox"], home_url("/crm-app/"))); ?>">Mail / Skrzynki</a>
                  <a class="btn <?php echo $settings_tab === "scoring" ? "" : "alt"; ?>" href="<?php echo esc_url(add_query_arg(["view" => "settings", "settings_tab" => "scoring"], home_url("/crm-app/"))); ?>">Scoring</a>
                  <a class="btn <?php echo $settings_tab === "automation" ? "" : "alt"; ?>" href="<?php echo esc_url(add_query_arg(["view" => "settings", "settings_tab" => "automation"], home_url("/crm-app/"))); ?>">Automatyzacje</a>
                  <a class="btn <?php echo $settings_tab === "offer-template" ? "" : "alt"; ?>" href="<?php echo esc_url(add_query_arg(["view" => "settings", "settings_tab" => "offer-template"], home_url("/crm-app/"))); ?>">Szablon oferty</a>
                  <a class="btn <?php echo $settings_tab === "contract-template" ? "" : "alt"; ?>" href="<?php echo esc_url(add_query_arg(["view" => "settings", "settings_tab" => "contract-template"], home_url("/crm-app/"))); ?>">Szablon umowy</a>
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
                    <input type="text" name="ups_anthropic_model" value="<?php echo esc_attr((string) get_option("ups_anthropic_model", "")); ?>" placeholder="claude-3-5-haiku-20241022" />
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
                    <label><input type="checkbox" name="ups_automation_ga4_sync_enabled" value="1" <?php checked((string) get_option("ups_automation_ga4_sync_enabled", "0"), "1"); ?> /> Włącz loop GA4 -> CRM (agregaty)</label>
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
                      Endpoint importu GA4 agregatów: <code><?php echo esc_html(rest_url("upsellio/v1/ga4-aggregate")); ?></code><br/>
                      Nagłówek: <code>x-upsellio-secret</code> = Twój Inbound secret. Ostatni sync: <code><?php echo esc_html((string) get_option("ups_automation_ga4_last_sync", "-")); ?></code>.
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
                  <form method="post" class="grid2">
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
                  <form method="post" class="grid2">
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
          if (data.success) {
            location.reload();
          }
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
          if (data.success) {
            location.reload();
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
          btnDefault = "Wyślij";
          offerId = parseInt(offerId, 10) || 0;
        }
        if (!bodyEl || bodyEl.value.trim() === "") {
          if (status) {
            status.textContent = "Wpisz treść wiadomości.";
            status.style.display = "block";
            status.style.color = "#e24b4a";
          }
          return;
        }
        if (!btn || !status) {
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
          no_client_email: "Klient nie ma adresu e-mail.",
          send_failed: "Wysyłka nie powiodła się — sprawdź Ustawienia → Mail / Skrzynki → Logi.",
          attachments_unavailable: "Obsługa załączników jest niedostępna na serwerze."
        };
        function inboxSendFail(data) {
          var code = data && data.data && data.data.message ? String(data.data.message) : "";
          status.textContent = "Błąd wysyłki: " + (errMap[code] || code || "nieznany");
          status.style.color = "#e24b4a";
          status.style.display = "block";
          btn.disabled = false;
          btn.textContent = btnDefault;
        }
        function inboxSendOk() {
          status.textContent = "Wysłano. Odświeżanie…";
          status.style.color = "#1d9e75";
          status.style.display = "block";
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
            status.textContent = "Błąd sieci.";
            status.style.color = "#e24b4a";
            status.style.display = "block";
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
          status.textContent = "Błąd sieci.";
          status.style.color = "#e24b4a";
          status.style.display = "block";
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
            var row = btn.closest(".inbox-classify-row");
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
          });
        });
        document.querySelectorAll(".inbox-folder-drop").forEach(function (el) {
          el.addEventListener("dragover", function (e) {
            e.preventDefault();
            el.style.boxShadow = "inset 0 0 0 2px rgba(13,148,136,.45)";
          });
          el.addEventListener("dragleave", function () {
            el.style.boxShadow = "";
          });
          el.addEventListener("drop", function (e) {
            e.preventDefault();
            el.style.boxShadow = "";
            var oid = e.dataTransfer.getData("text/plain");
            var fid = el.getAttribute("data-folder-id");
            if (oid && fid) {
              inboxMoveFolder(oid, fid);
            }
          });
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
