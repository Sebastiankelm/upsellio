<?php

if (!defined("ABSPATH")) {
    exit;
}

/** @var string $view */
/** @var int $crm_inbox_unread_total */
/** @var int $crm_tasks_open_count */

$registry = upsellio_crm_view_registry();
$sections = upsellio_crm_section_labels();
$items = upsellio_crm_sidebar_items();
$hints = [
    "analytics" => __("GSC, GA4, Ads, lejek", "upsellio"),
    "ca-clients" => __("Profile, mapowanie, KPI", "upsellio"),
    "ca-dashboard" => __("Dashboard ze zmapowanych danych", "upsellio"),
    "insights" => __("Brief i rekomendacje", "upsellio"),
];
?>
<aside class="side crm-sidebar" id="ups-crm-sidebar">
  <div class="side-brand crm-sb-top">
    <a class="crm-sb-top-link" href="<?php echo esc_url(upsellio_crm_url("dashboard")); ?>" style="display:flex;align-items:center;gap:9px;color:inherit;text-decoration:none">
      <div class="crm-sb-mark"><i class="ti ti-chart-dots" aria-hidden="true"></i></div>
      <div>
        <div class="crm-sb-brand">Upsellio CRM</div>
        <div class="crm-sb-user"><?php esc_html_e("Panel operacyjny", "upsellio"); ?></div>
      </div>
    </a>
    <button type="button" class="crm-mobile-menu" aria-controls="ups-crm-sidebar-nav" aria-expanded="true"><i class="ti ti-menu-2" aria-hidden="true"></i> <?php esc_html_e("Menu", "upsellio"); ?></button>
  </div>
  <form class="crm-sidebar-search" method="get" action="<?php echo esc_url(upsellio_crm_base_url()); ?>">
    <i class="ti ti-search" aria-hidden="true"></i>
    <input type="hidden" name="view" value="search" />
    <input type="search" name="crm_q" placeholder="<?php esc_attr_e("Szukaj…", "upsellio"); ?>" value="<?php echo esc_attr($crm_search_q ?? ""); ?>" aria-label="<?php esc_attr_e("Szukaj w CRM", "upsellio"); ?>" autocomplete="off" />
    <kbd>Ctrl/⌘ K</kbd>
  </form>
  <nav id="ups-crm-sidebar-nav" class="side-nav crm-side-nav crm-sb-body" aria-label="<?php esc_attr_e("Nawigacja CRM", "upsellio"); ?>">
    <?php foreach ($items as $item) : ?>
      <?php if (($item["type"] ?? "") === "section") : ?>
        <?php
        $sid = (string) ($item["id"] ?? "");
        if ($sid === "" || !isset($sections[$sid])) {
            continue;
        }
        ?>
        <div class="side-section crm-sb-sep"><?php echo esc_html($sections[$sid]); ?></div>
        <?php continue; ?>
      <?php endif; ?>

      <?php
      $nav_view = (string) ($item["view"] ?? "");
      if ($nav_view === "" || !isset($registry[$nav_view]) || !upsellio_crm_app_user_can_view($nav_view)) {
          continue;
      }
      $meta = $registry[$nav_view];
      $args = isset($item["args"]) && is_array($item["args"]) ? $item["args"] : [];
      if ($nav_view === "ca-dashboard" && empty($args["cid"])) {
          $nav_cid = isset($_GET["cid"]) ? (int) wp_unslash($_GET["cid"]) : 0;
          if ($nav_cid <= 0 && function_exists("ups_audit_default_profile_client_id")) {
              $nav_cid = ups_audit_default_profile_client_id();
          }
          if (upsellio_crm_app_can_access_audit_client($nav_cid)) {
              $args["cid"] = $nav_cid;
          }
      }
      $href = upsellio_crm_url($nav_view, $args);
      $active = upsellio_crm_nav_is_active($nav_view, $view, $args);
      $badge = "";
      if (($item["badge"] ?? "") === "inbox" && $crm_inbox_unread_total > 0) {
          $badge = '<span class="side-badge hot crm-sb-badge warn">' . (int) $crm_inbox_unread_total . "</span>";
      }
      if (($item["badge"] ?? "") === "tasks" && $crm_tasks_open_count > 0) {
          $badge = '<span class="crm-sb-badge">' . (int) min(99, $crm_tasks_open_count) . "</span>";
      }
      if (($item["badge"] ?? "") === "audit_clients" && current_user_can("manage_options")) {
          $ca_cnt = function_exists("ups_audit_count_active_clients")
              ? (int) ups_audit_count_active_clients()
              : (function_exists("ups_audit_count_google_accounts") ? (int) ups_audit_count_google_accounts() : 0);
          if ($ca_cnt > 0) {
              $badge = '<span class="crm-sb-badge">' . (int) min(99, $ca_cnt) . "</span>";
          }
      }
      $hint = isset($hints[$nav_view]) ? $hints[$nav_view] : "";
      ?>
      <a class="side-link crm-si <?php echo $active ? "active" : ""; ?>" href="<?php echo esc_url($href); ?>"<?php echo $active ? ' aria-current="page"' : ""; ?>>
        <i class="ti <?php echo esc_attr($meta["icon"]); ?>" aria-hidden="true"></i>
        <span class="crm-si-label">
          <?php echo esc_html($meta["label"]); ?>
          <?php if ($hint !== "") : ?>
            <span class="crm-si-hint"><?php echo esc_html($hint); ?></span>
          <?php endif; ?>
        </span>
        <?php echo $badge; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="crm-sb-foot">
    <?php if (upsellio_crm_app_user_can_view("settings")) : ?>
    <a class="side-link crm-si <?php echo $view === "settings" ? "active" : ""; ?>" href="<?php echo esc_url(upsellio_crm_url("settings", ["settings_tab" => "general"])); ?>">
      <i class="ti ti-settings" aria-hidden="true"></i>
      <span class="crm-si-label"><?php esc_html_e("Ustawienia", "upsellio"); ?></span>
    </a>
    <?php endif; ?>
    <div class="crm-sidebar-account">
      <span class="crm-sidebar-avatar" aria-hidden="true"><i class="ti ti-user"></i></span>
      <div class="crm-sidebar-account-name">
        <strong><?php echo esc_html(wp_get_current_user()->display_name ?: __("Ty", "upsellio")); ?></strong>
        <a href="<?php echo esc_url(admin_url("profile.php")); ?>"><?php esc_html_e("Profil", "upsellio"); ?></a>
        <span aria-hidden="true"> · </span>
        <a href="<?php echo esc_url(wp_logout_url(upsellio_crm_base_url())); ?>"><?php esc_html_e("Wyloguj", "upsellio"); ?></a>
      </div>
    </div>
  </div>
</aside>
<script>
(function () {
  var side = document.getElementById("ups-crm-sidebar");
  if (!side || !document.body.classList.contains("ups-crm-redesign")) return;
  var toggle = side.querySelector(".crm-mobile-menu");
  var search = side.querySelector('input[type="search"]');
  function collapse(value) {
    side.dataset.collapsed = value ? "true" : "false";
    toggle.setAttribute("aria-expanded", value ? "false" : "true");
  }
  var narrow = window.matchMedia("(max-width: 1100px)");
  collapse(narrow.matches);
  if (narrow.addEventListener) narrow.addEventListener("change", function (e) { collapse(e.matches); });
  toggle.addEventListener("click", function () { collapse(side.dataset.collapsed !== "true"); });
  document.addEventListener("keydown", function (event) {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === "k" && !event.altKey) {
      event.preventDefault();
      collapse(false);
      search.focus();
    }
  });
})();
</script>
