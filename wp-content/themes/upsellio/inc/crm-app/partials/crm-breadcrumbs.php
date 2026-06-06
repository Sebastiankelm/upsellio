<?php

if (!defined("ABSPATH")) {
    exit;
}

/** @var list<array{label:string,url?:string}> $crm_breadcrumbs */
if (empty($crm_breadcrumbs) || count($crm_breadcrumbs) < 2) {
    return;
}
?>
<nav class="crm-crumb-bar" aria-label="<?php esc_attr_e("Ścieżka", "upsellio"); ?>">
  <?php foreach ($crm_breadcrumbs as $i => $crumb) : ?>
    <?php if ($i > 0) : ?><span class="crm-crumb-sep" aria-hidden="true">/</span><?php endif; ?>
    <?php if (!empty($crumb["url"]) && $i < count($crm_breadcrumbs) - 1) : ?>
      <a href="<?php echo esc_url((string) $crumb["url"]); ?>"><?php echo esc_html((string) $crumb["label"]); ?></a>
    <?php else : ?>
      <span class="crm-crumb-current"><?php echo esc_html((string) $crumb["label"]); ?></span>
    <?php endif; ?>
  <?php endforeach; ?>
</nav>
