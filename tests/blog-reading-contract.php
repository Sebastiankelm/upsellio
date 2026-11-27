<?php
// Regression checks for the single-post reading layout and redundant manual TOCs.
define("ABSPATH", __DIR__);
function get_header() {}
function get_footer() {}
function have_posts() { return false; }
$template = __DIR__ . "/../wp-content/themes/upsellio/single.php";
require $template;
function verify_blog($ok, $message) {
    if (!$ok) { fwrite(STDERR, $message . PHP_EOL); exit(1); }
}
$toc = [["id"=>"krok-1"],["id"=>"krok-2"],["id"=>"faq"]];
$intro = "<p>Wprowadzenie</p>";
$body = '<h2 id="krok-1">Pierwszy krok</h2><p>Treść</p><h2 id="krok-2">Drugi krok</h2>';
$duplicate = '<p><strong>Spis treści</strong></p><!-- /wp:paragraph --><!-- wp:list --><ul class="wp-block-list"><!-- wp:list-item --><li><a href="#krok-1">Pierwszy krok</a></li><!-- /wp:list-item --><li><a href="#krok-2">Drugi krok</a></li></ul>';
verify_blog(upsellio_single_remove_redundant_toc($intro.$duplicate.$body,$toc)===$intro.$body,"Remove duplicated Gutenberg TOC only");
$plain = '<p><strong>Spis treści</strong></p><ul><li><a href="#krok-1">Krok</a></li></ul>';
verify_blog(upsellio_single_remove_redundant_toc($plain.$body,$toc)===$body,"Remove plain HTML TOC");
foreach ([
    '<p>Spis treści</p><ul><li><a href="#missing">Brak</a></li></ul>',
    '<p>Spis treści</p><ul><li><a href="https://example.com">Zewnętrzny</a></li></ul>',
    '<p>Spis treści</p><ul><li><a href="#krok-1">Krok</a> z opisem</li></ul>',
    '<p>Inne materiały</p><ul><li><a href="#krok-1">Krok</a></li></ul>',
    '<p id="navigation">Spis treści</p><ul><li><a href="#krok-1">Krok</a></li></ul>'
] as $fixture) {
    verify_blog(upsellio_single_remove_redundant_toc($fixture,$toc)===$fixture,"Preserve non-redundant content");
}
verify_blog(upsellio_single_remove_redundant_toc($duplicate,[])===$duplicate,"Keep manual TOC if generated TOC is absent");
$source=file_get_contents($template);
verify_blog(strpos($source,'class="sp-wrap sp-grid"')!==false,"Use wide reading wrapper");
verify_blog(strpos($source,'grid-template-columns:minmax(0,1fr) 248px')!==false,"Flexible desktop article column");
verify_blog(strpos($source,'<details class="sp-toc-disclosure" open>')!==false,"Native TOC disclosure");
verify_blog(strpos($source,'class="sp-wrap-narrow sp-grid"')===false,"Remove competing narrow wrapper");
echo "Blog reading layout and redundant TOC contracts passed\n";
