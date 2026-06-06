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
<aside class="side crm-sidebar">
  <div class="side-brand crm-sb-top">
    <a class="crm-sb-top-link" href="<?php echo esc_url(upsellio_crm_url("dashboard")); ?>" style="display:flex;align-items:center;gap:9px;color:inherit;text-decoration:none">
      <div class="crm-sb-mark"><i class="ti ti-chart-dots" aria-hidden="true"></i></div>
      <div>
        <div class="crm-sb-brand">Upsellio</div>
        <div class="crm-sb-user"><?php echo esc_html(wp_get_current_user()->display_name ?: __("Ty", "upsellio")); ?></div>
      </div>
    </a>
  </div>
  <nav class="side-nav crm-side-nav crm-sb-body" aria-label="<?php esc_attr_e("Nawigacja CRM", "upsellio"); ?>">
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
      if ($nav_view === "" || !isset($registry[$nav_view])) {
          continue;
      }
      $meta = $registry[$nav_view];
      $args = isset($item["args"]) && is_array($item["args"]) ? $item["args"] : [];
      if ($nav_view === "ca-dashboard" && empty($args["cid"])) {
          $nav_cid = isset($_GET["cid"]) ? (int) wp_unslash($_GET["cid"]) : 0;
          if ($nav_cid <= 0 && function_exists("ups_audit_default_profile_client_id")) {
              $nav_cid = ups_audit_default_profile_client_id();
          }
          if ($nav_cid > 0) {
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
      if (($item["badge"] ?? "") === "audit_clients") {
          $ca_cnt = function_exists("ups_audit_count_active_clients")
              ? (int) ups_audit_count_active_clients()
              : (function_exists("ups_audit_count_google_accounts") ? (int) ups_audit_count_google_accounts() : 0);
          if ($ca_cnt > 0) {
              $badge = '<span class="crm-sb-badge">' . (int) min(99, $ca_cnt) . "</span>";
          }
      }
      $hint = isset($hints[$nav_view]) ? $hints[$nav_view] : "";
      ?>
      <a class="side-link crm-si <?php echo $active ? "active" : ""; ?>" href="<?php echo esc_url($href); ?>">
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
    <a class="side-link crm-si <?php echo $view === "settings" ? "active" : ""; ?>" href="<?php echo esc_url(upsellio_crm_url("settings", ["settings_tab" => "general"])); ?>">
      <i class="ti ti-settings" aria-hidden="true"></i>
      <span class="crm-si-label"><?php esc_html_e("Ustawienia", "upsellio"); ?></span>
    </a>
  </div>
</aside>
