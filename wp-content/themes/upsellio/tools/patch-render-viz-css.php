<?php

$path = dirname(__DIR__) . "/inc/crm-app/render.php";
$c = file_get_contents($path);
if ($c === false) {
    fwrite(STDERR, "READ_FAIL\n");
    exit(1);
}
if (strpos($c, "audit-dashboard-viz") !== false) {
    echo "ALREADY_PATCHED\n";
    exit(0);
}

$block = <<<'HTML'
      <?php
      if ($view === "ca-dashboard") :
          $_ups_audit_viz_css = get_template_directory() . "/inc/crm-app/assets/css/audit-dashboard-viz.css";
          if (file_exists($_ups_audit_viz_css)) :
              ?>
      <link rel="stylesheet" href="<?php echo esc_url(get_template_directory_uri() . "/inc/crm-app/assets/css/audit-dashboard-viz.css"); ?>?ver=<?php echo esc_attr((string) filemtime($_ups_audit_viz_css)); ?>" />
              <?php
          endif;
      endif;
      ?>
HTML;

$patterns = [
    "/crm-charts\.js.*?defer><\/script>\s*<\?php endif; \?>/s",
];
$replacement = '<script src="<?php echo esc_url($_ups_chart_js . "/crm-charts.js"); ?>?ver=<?php echo esc_attr($_ups_chart_v); ?>" defer></script>' . "\n" . $block . "\n      <?php endif; ?>";

$new = preg_replace($patterns, $replacement, $c, 1, $count);
if ($count < 1 || !is_string($new)) {
    fwrite(STDERR, "PATTERN_NOT_FOUND\n");
    exit(1);
}

file_put_contents($path, $new);
echo "PATCHED_OK\n";
