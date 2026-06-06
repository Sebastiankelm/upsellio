<?php

if (!defined("ABSPATH")) {
    exit;
}

/** @var string $atab */
/** @var int $range_days */

$tabs = upsellio_crm_analytics_tabs();
?>
<nav class="crm-page-tabs" aria-label="<?php esc_attr_e("Raporty marketingowe", "upsellio"); ?>">
  <?php foreach ($tabs as $key => $tab) : ?>
    <?php
    $url = upsellio_crm_url("analytics", [
        "atab" => $key,
        "range" => $range_days,
    ]);
    ?>
    <a class="<?php echo $atab === $key ? "is-active" : ""; ?>" href="<?php echo esc_url($url); ?>" title="<?php echo esc_attr((string) ($tab["desc"] ?? "")); ?>">
      <i class="ti <?php echo esc_attr($tab["icon"]); ?>" aria-hidden="true"></i>
      <?php echo esc_html($tab["label"]); ?>
    </a>
  <?php endforeach; ?>
</nav>
