<?php

if (!defined("ABSPATH")) {
    exit;
}

/** @var string $view */
/** @var int $cid */

if (!in_array($view, ["ca-dashboard", "ca-reports", "ca-plan", "ca-library"], true)) {
    return;
}

if ($cid <= 0) :
    ?>
<div class="crm-context-strip">
  <?php esc_html_e("Wybierz klienta z portfolio, aby otworzyć panel, raporty lub plan.", "upsellio"); ?>
  <a href="<?php echo esc_url(upsellio_crm_url("ca-clients")); ?>"><?php esc_html_e("Przejdź do portfolio →", "upsellio"); ?></a>
</div>
    <?php
    return;
endif;

$client_name = get_the_title($cid);
$dash_url = upsellio_crm_url("ca-dashboard", ["cid" => $cid]);
$reports_url = upsellio_crm_url("ca-reports", ["cid" => $cid]);
$plan_url = upsellio_crm_url("ca-plan", ["cid" => $cid]);
?>
<div class="crm-context-strip" style="align-items:center;">
  <strong><?php echo esc_html($client_name !== "" ? $client_name : __("Profil", "upsellio")); ?></strong>
  <span aria-hidden="true">·</span>
  <a href="<?php echo esc_url($dash_url); ?>"<?php echo $view === "ca-dashboard" ? ' style="font-weight:800"' : ""; ?>><?php esc_html_e("Dashboard", "upsellio"); ?></a>
  <a href="<?php echo esc_url($reports_url); ?>"<?php echo $view === "ca-reports" ? ' style="font-weight:800"' : ""; ?>><?php esc_html_e("Raporty", "upsellio"); ?></a>
  <a href="<?php echo esc_url($plan_url); ?>"<?php echo $view === "ca-plan" ? ' style="font-weight:800"' : ""; ?>><?php esc_html_e("Plan AI", "upsellio"); ?></a>
  <a href="<?php echo esc_url(upsellio_crm_url("ca-clients")); ?>"><?php esc_html_e("← Profile", "upsellio"); ?></a>
  <?php require __DIR__ . "/crm-audit-profile-switcher.php"; ?>
</div>
