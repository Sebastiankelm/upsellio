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

    $clients = get_posts([
        "post_type" => "crm_client",
        "post_status" => ["publish", "draft", "private", "pending"],
        "posts_per_page" => 300,
        "orderby" => "modified",
        "order" => "DESC",
    ]);
    $offers = get_posts([
        "post_type" => "crm_offer",
        "post_status" => ["publish", "draft", "private", "pending"],
        "posts_per_page" => 300,
        "orderby" => "modified",
        "order" => "DESC",
    ]);
    $followups = get_posts([
        "post_type" => "ups_followup_template",
        "post_status" => ["publish", "draft", "private", "pending"],
        "posts_per_page" => 300,
        "orderby" => "modified",
        "order" => "DESC",
    ]);
    $prospects = get_posts([
        "post_type" => "crm_prospect",
        "post_status" => ["publish", "draft", "private", "pending"],
        "posts_per_page" => 300,
        "orderby" => "modified",
        "order" => "DESC",
    ]);
    $leads = post_type_exists("crm_lead") ? get_posts([
        "post_type" => "crm_lead",
        "post_status" => ["publish", "draft", "private", "pending"],
        "posts_per_page" => 300,
        "orderby" => "modified",
        "order" => "DESC",
    ]) : [];
    $contacts = post_type_exists("crm_contact") ? get_posts([
        "post_type" => "crm_contact",
        "post_status" => ["publish", "draft", "private", "pending"],
        "posts_per_page" => 300,
        "orderby" => "modified",
        "order" => "DESC",
    ]) : [];
    $services = post_type_exists("crm_service") ? get_posts([
        "post_type" => "crm_service",
        "post_status" => ["publish", "draft", "private", "pending"],
        "posts_per_page" => 300,
        "orderby" => "modified",
        "order" => "DESC",
    ]) : [];
    $tasks_query_args = [
        "post_type" => "lead_task",
        "post_status" => ["publish", "draft", "private", "pending"],
        "posts_per_page" => 500,
        "orderby" => "date",
        "order" => "DESC",
    ];
    if (!current_user_can("manage_options")) {
        $tasks_query_args["author"] = get_current_user_id();
    }
    $tasks = post_type_exists("lead_task") ? get_posts($tasks_query_args) : [];
    if (!empty($tasks)) {
        usort($tasks, static function ($a, $b) {
            $pa = (int) get_post_meta((int) $a->ID, "_upsellio_task_priority_score", true);
            $pb = (int) get_post_meta((int) $b->ID, "_upsellio_task_priority_score", true);
            if ($pa === $pb) {
                return (int) $b->ID <=> (int) $a->ID;
            }
            return $pb <=> $pa;
        });
    }
    $contracts = get_posts([
        "post_type" => "crm_contract",
        "post_status" => ["publish", "draft", "private", "pending"],
        "posts_per_page" => 300,
        "orderby" => "modified",
        "order" => "DESC",
    ]);
    $contract_template_html = function_exists("upsellio_contracts_get_default_template_html") ? (string) upsellio_contracts_get_default_template_html() : "";
    $contract_template_css = function_exists("upsellio_contracts_get_default_template_css") ? (string) upsellio_contracts_get_default_template_css() : "";
    $view = isset($_GET["view"]) ? sanitize_key((string) wp_unslash($_GET["view"])) : "dashboard";
    if (!in_array($view, ["dashboard", "leads", "account-360", "clients", "client-edit", "contacts", "offers", "services", "pipeline", "contracts", "contract-detail", "followups", "tasks", "calendar", "prospecting", "inbox", "alerts", "analytics", "engine", "settings"], true)) {
        $view = "dashboard";
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
        @media(max-width:1100px){.layout{grid-template-columns:1fr}.side{display:none}.kpi,.half{grid-column:span 12}.topbar{padding:0 16px}.content{padding:16px}}
      </style>
    </head>
    <body>
      <?php
      $hot_offers_count = 0;
      $active_recurring = 0;
      $active_mrr = 0.0;
      $won_offers = 0;
      foreach ($offers as $offer) {
          $oid = (int) $offer->ID;
          if ((string) get_post_meta($oid, "_ups_offer_hot_offer", true) === "1") {
              $hot_offers_count++;
          }
          if ((string) get_post_meta($oid, "_ups_offer_status", true) === "won") {
              $won_offers++;
          }
      }
      foreach ($clients as $client) {
          $cid = (int) $client->ID;
          $is_recurring = (string) get_post_meta($cid, "_ups_client_is_recurring", true) === "1";
          $sub_status = (string) get_post_meta($cid, "_ups_client_subscription_status", true);
          if ($sub_status === "") {
              $sub_status = "active";
          }
          if ($is_recurring && $sub_status === "active") {
              $active_recurring++;
              $active_mrr += (float) get_post_meta($cid, "_ups_client_monthly_value", true);
          }
      }
      ?>
      <?php
      $custom_logo_id = (int) get_theme_mod("custom_logo");
      $custom_logo_url = $custom_logo_id > 0 ? (string) wp_get_attachment_image_url($custom_logo_id, "full") : "";
      $mailbox_test_key = "ups_crm_mailbox_test_" . get_current_user_id();
      $mailbox_test_result = get_transient($mailbox_test_key);
      if ($mailbox_test_result !== false) {
          delete_transient($mailbox_test_key);
      }
      $view_titles = [
          "dashboard" => "Pulpit",
          "leads" => "Leady",
          "account-360" => "Karta 360",
          "clients" => "Klienci",
          "client-edit" => "Edycja klienta",
          "contacts" => "Kontakty B2B",
          "offers" => "Oferty",
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
            <a class="side-link <?php echo $view === "services" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "services"], home_url("/crm-app/"))); ?>">Katalog usług</a>
            <a class="side-link <?php echo $view === "pipeline" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "pipeline"], home_url("/crm-app/"))); ?>">Lejek</a>
            <a class="side-link <?php echo $view === "contracts" || $view === "contract-detail" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "contracts"], home_url("/crm-app/"))); ?>">Umowy</a>
            <div class="side-section">Automatyzacja</div>
            <a class="side-link <?php echo $view === "followups" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "followups"], home_url("/crm-app/"))); ?>">Follow-upy</a>
            <a class="side-link <?php echo $view === "tasks" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "tasks"], home_url("/crm-app/"))); ?>">Taski</a>
            <a class="side-link <?php echo $view === "calendar" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "calendar"], home_url("/crm-app/"))); ?>">Kalendarz</a>
            <a class="side-link <?php echo $view === "prospecting" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "prospecting"], home_url("/crm-app/"))); ?>">Prospecting (zimne maile)</a>
            <a class="side-link <?php echo $view === "inbox" ? "active" : ""; ?>" href="<?php echo esc_url(add_query_arg(["view" => "inbox"], home_url("/crm-app/"))); ?>">Inbox</a>
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
          <div class="grid">
            <?php if ($view === "dashboard") : ?>
              <?php $global_activity = get_option("ups_crm_activity_log", []); if (!is_array($global_activity)) { $global_activity = []; } ?>
              <section class="card kpi"><span class="muted">Klienci</span><b><?php echo esc_html((string) count($clients)); ?></b></section>
              <section class="card kpi"><span class="muted">Gorące oferty</span><b><?php echo esc_html((string) $hot_offers_count); ?></b></section>
              <section class="card kpi"><span class="muted">Wygrane oferty</span><b><?php echo esc_html((string) $won_offers); ?></b></section>
              <section class="card kpi"><span class="muted">Aktywne MRR</span><b><?php echo esc_html(number_format($active_mrr, 0, ",", " ")); ?> PLN</b></section>
              <section class="card half">
                <h2>Najnowsi klienci</h2>
                <table><thead><tr><th>Klient</th><th>Status</th><th>MRR</th><th></th></tr></thead><tbody>
                <?php foreach (array_slice($clients, 0, 8) as $client) : $cid = (int) $client->ID; ?>
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
                <?php foreach (array_slice($contracts, 0, 5) as $contract) : $tid = function_exists("upsellio_contracts_get_timeline") ? upsellio_contracts_get_timeline((int) $contract->ID) : []; $last = !empty($tid) ? end($tid) : []; ?>
                  <div class="timeline-item">
                    <span class="muted"><?php echo esc_html(isset($last["ts"]) ? (string) $last["ts"] : "brak"); ?></span>
                    <span><strong><?php echo esc_html((string) $contract->post_title); ?></strong> - <?php echo esc_html(isset($last["label"]) ? (string) $last["label"] : "Brak zdarzen"); ?></span>
                  </div>
                <?php endforeach; ?>
              </section>
              <section class="card">
                <h2>Co robić dziś (priorytet decyzyjny)</h2>
                <p class="muted" style="margin-bottom:10px">Taski otwarte sortowane po <code>priority_score</code> (wpływ ×40% + prawdopodobieństwo ×40% + presja terminu ×20%).</p>
                <table><thead><tr><th>Priorytet</th><th>Task</th><th>Deal</th><th>Termin</th><th>Status</th></tr></thead><tbody>
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
                ?>
                  <tr>
                    <td><strong><?php echo esc_html((string) ($d_pri > 0 ? $d_pri : "—")); ?></strong></td>
                    <td><?php echo esc_html((string) $dtask->post_title); ?></td>
                    <td><?php echo $d_oid > 0 ? esc_html((string) get_the_title($d_oid)) : "—"; ?></td>
                    <td><?php echo $d_due > 0 ? esc_html((string) wp_date("Y-m-d H:i", $d_due)) : "—"; ?></td>
                    <td><?php echo esc_html((string) $dst); ?></td>
                  </tr>
                <?php } ?>
                <?php if ($dash_tasks === 0) : ?><tr><td colspan="5" class="muted">Brak otwartych tasków lub wszystkie zamknięte.</td></tr><?php endif; ?>
                </tbody></table>
              </section>
              <section class="card">
                <h2>Ostatnia historia działań CRM</h2>
                <?php $recent_activity = array_reverse(array_slice($global_activity, -20)); ?>
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
              <section class="card">
                <h2>Oferty</h2>
                <form method="post" class="grid2" style="margin:0 0 12px">
                  <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                  <input type="hidden" name="ups_action" value="save_offer" />
                  <input type="hidden" name="crm_view" value="offers" />
                  <input type="text" name="offer_title" placeholder="Tytuł oferty" required />
                  <select name="offer_client_id">
                    <option value="">-- klient --</option>
                    <?php foreach ($clients as $client) : ?>
                      <option value="<?php echo esc_attr((string) $client->ID); ?>"><?php echo esc_html((string) $client->post_title); ?></option>
                    <?php endforeach; ?>
                  </select>
                  <input type="text" name="offer_price" placeholder="Cena" />
                  <input type="text" name="offer_timeline" placeholder="Timeline" />
                  <input type="text" name="offer_cta_text" placeholder="CTA" />
                  <select name="offer_status"><option value="open">otwarty</option><option value="won">wygrany</option><option value="lost">przegrany</option></select>
                  <select name="offer_win_reason"><option value="">— powód wygranej —</option><option value="price_fit">dopasowanie ceny</option><option value="trust">zaufanie</option><option value="urgency">pilność</option><option value="referral">referencje</option><option value="competitive_edge">przewaga</option></select>
                  <select name="offer_loss_reason"><option value="">— powód przegranej —</option><option value="timing">timing</option><option value="budget">budżet</option><option value="competitor">konkurencja</option><option value="no_response">brak odpowiedzi</option><option value="scope">zakres</option></select>
                  <input type="number" step="0.01" min="0" name="offer_won_value" placeholder="Wartość wygranej" />
                  <select name="offer_owner_id">
                    <option value="">-- owner --</option>
                    <?php foreach (get_users(["role__in" => ["administrator", "editor"], "orderby" => "display_name", "order" => "ASC"]) as $owner) : ?>
                      <?php $owner_id = isset($owner->ID) ? (int) $owner->ID : 0; if ($owner_id <= 0) { continue; } ?>
                      <option value="<?php echo esc_attr((string) $owner_id); ?>"><?php echo esc_html((string) ($owner->display_name ?? ("User #" . $owner_id))); ?></option>
                    <?php endforeach; ?>
                  </select>
                  <label><input type="checkbox" name="offer_generate_from_template" value="1" /> Generuj treść oferty z domyślnego szablonu</label>
                  <textarea name="deal_notes" placeholder="Notatki deala"></textarea>
                  <textarea name="offer_internal_notes" placeholder="Wewnętrzne notatki oferty"></textarea>
                  <textarea name="offer_content" placeholder="Treść oferty (HTML/tekst)"></textarea>
                  <button class="btn" type="submit">Dodaj ofertę</button>
                </form>
                <p class="muted">Zmienne w szablonie oferty: <code>{{client_name}}</code>, <code>{{client_company}}</code>, <code>{{offer_title}}</code>, <code>{{offer_price}}</code>, <code>{{offer_timeline}}</code>, <code>{{offer_cta_text}}</code>, <code>{{offer_url}}</code>, <code>{{today}}</code>.</p>
                <table>
                  <thead><tr><th>Klient</th><th>Oferta</th><th>Status</th><th>Etap</th><th>Score / prawd.</th><th>Gorąca</th><th>Win / loss</th><th>Notatki</th><th>Link</th><th>Follow-up</th></tr></thead>
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
                            <option value="timing" <?php selected($olr, "timing"); ?>>timing</option>
                            <option value="budget" <?php selected($olr, "budget"); ?>>budżet</option>
                            <option value="competitor" <?php selected($olr, "competitor"); ?>>konkurencja</option>
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
                      <td><?php if ($offer_url !== "") : ?><a class="btn alt" href="<?php echo esc_url($offer_url); ?>" target="_blank" rel="noopener noreferrer">Podgląd</a><?php endif; ?></td>
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
                      dropzone.appendChild(draggedCard);
                      const payload = new URLSearchParams();
                      payload.append("action", "upsellio_crm_move_offer_pipeline");
                      payload.append("nonce", "<?php echo esc_js(wp_create_nonce("ups_crm_app_action")); ?>");
                      payload.append("offer_id", String(draggedCard.getAttribute("data-offer-id") || ""));
                      payload.append("stage", String(col.getAttribute("data-pipeline-col") || ""));
                      try {
                        await fetch("<?php echo esc_url(admin_url("admin-ajax.php")); ?>", {
                          method: "POST",
                          headers: {"Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"},
                          body: payload.toString()
                        });
                      } catch (error) {
                        console.error("Pipeline update failed", error);
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
                  <thead><tr><th>Prio</th><th>Task</th><th>Deal</th><th>Owner</th><th>Termin</th><th>Czas</th><th>Status</th><th>Akcje</th></tr></thead>
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
                      ?>
                      <tr>
                        <td><strong><?php echo esc_html((string) ($prio > 0 ? $prio : "—")); ?></strong></td>
                        <td><?php echo esc_html((string) $task->post_title); ?></td>
                        <td><?php echo $task_offer_id > 0 ? esc_html((string) get_the_title($task_offer_id)) : "—"; ?></td>
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
                      <tr><td colspan="8">Brak tasków.</td></tr>
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
              <section class="card">
                <h2>Inbox komunikacji (klient/deal)</h2>
                <table>
                  <thead><tr><th>Encja</th><th>Ostatnia akcja</th><th>Czas</th><th>Szczegóły</th></tr></thead>
                  <tbody>
                    <?php
                    $global_activity = get_option("ups_crm_activity_log", []);
                    if (!is_array($global_activity)) {
                        $global_activity = [];
                    }
                    $inbox_rows = array_reverse(array_slice($global_activity, -100));
                    foreach ($inbox_rows as $row) :
                        if (!is_array($row)) { continue; }
                        $entry = isset($row["entry"]) && is_array($row["entry"]) ? $row["entry"] : [];
                        $event = (string) ($entry["event"] ?? "");
                        if (strpos($event, "followup") === false && strpos($event, "inbound") === false && strpos($event, "email") === false) {
                            continue;
                        }
                    ?>
                      <tr>
                        <td><?php echo esc_html((string) ($row["entity_type"] ?? "") . " #" . (string) ($row["entity_id"] ?? 0)); ?></td>
                        <td><?php echo esc_html((string) ($entry["event"] ?? "")); ?></td>
                        <td><?php echo esc_html((string) ($entry["ts"] ?? "")); ?></td>
                        <td><?php echo esc_html((string) ($entry["message"] ?? "")); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
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
                <table>
                  <thead><tr><th>Kategoria</th><th>Liczba</th></tr></thead>
                  <tbody>
                    <?php foreach ($lost_reasons as $reason => $count_reason) : ?>
                      <tr><td><?php echo esc_html((string) $reason); ?></td><td><?php echo esc_html((string) $count_reason); ?></td></tr>
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
                  <form method="post" class="grid2">
                    <?php wp_nonce_field("ups_crm_app_action", "ups_crm_app_nonce"); ?>
                    <input type="hidden" name="ups_action" value="export_crm_data" />
                    <input type="hidden" name="crm_view" value="settings" />
                    <input type="hidden" name="settings_tab" value="general" />
                    <label>Eksport encji</label>
                    <select name="export_entity"><option value="leads">Leady</option><option value="clients">Klienci</option><option value="offers">Oferty</option><option value="tasks">Taski</option></select>
                    <button class="btn alt" type="submit">Eksportuj CSV</button>
                  </form>
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
                    <label>Hint awareness</label>
                    <textarea name="ups_followup_hint_awareness"><?php echo esc_textarea((string) get_option("ups_followup_hint_awareness", "")); ?></textarea>
                    <label>Hint consideration</label>
                    <textarea name="ups_followup_hint_consideration"><?php echo esc_textarea((string) get_option("ups_followup_hint_consideration", "")); ?></textarea>
                    <label>Hint decision</label>
                    <textarea name="ups_followup_hint_decision"><?php echo esc_textarea((string) get_option("ups_followup_hint_decision", "")); ?></textarea>
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
    </body>
    </html>
    <?php
    exit;
}
add_action("template_redirect", "upsellio_crm_app_template_redirect", 0);
