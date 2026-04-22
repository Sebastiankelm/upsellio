<?php
if (!defined("ABSPATH")) {
    exit;
}
?>
<?php
echo function_exists("upsellio_render_unified_footer")
    ? upsellio_render_unified_footer()
    : "";
?>
<?php wp_footer(); ?>
</body>
</html>

