<?php
// Public editorial presentation contract, without a WordPress installation.
define("ABSPATH", __DIR__);
function add_action(...$args) {}
function add_filter(...$args) {}
class WP_Post {
    public $post_type, $post_status, $post_password;
    public function __construct($type, $status="publish", $password="") {
        $this->post_type=$type; $this->post_status=$status; $this->post_password=$password;
    }
}
function is_admin() { return $GLOBALS["admin"] ?? false; }
function wp_doing_ajax() { return false; }
function wp_doing_cron() { return false; }
function is_feed() { return false; }
function is_preview() { return false; }
function is_page($slug) { return ($GLOBALS["slug"] ?? "") === $slug; }
function is_singular() { return isset($GLOBALS["post"]); }
function get_queried_object() { return $GLOBALS["post"] ?? null; }
function is_front_page() { return !is_singular(); }
function is_home() { return false; }
function is_archive() { return false; }
function is_search() { return false; }
function is_404() { return false; }
function get_template_directory() { return __DIR__ . "/../wp-content/themes/upsellio"; }
function get_template_directory_uri() { return "https://upsellio.pl/wp-content/themes/upsellio"; }
function wp_enqueue_style(...$args) { $GLOBALS["styles"][]=$args; }
function wp_script_is($handle,$status) { return $handle === "upsellio-ui" && $status === "enqueued"; }
function wp_add_inline_script($handle,$code,$position) { $GLOBALS["scripts"][]=[$handle,$code,$position]; }
function check($value,$message) { if (!$value) { fwrite(STDERR,$message.PHP_EOL); exit(1); } }
require get_template_directory() . "/inc/technical-seo.php";
foreach (["post","page","miasto","definicja","portfolio","marketing_portfolio","lead_magnet"] as $type) {
    $post=new WP_Post($type); $styles=[]; $scripts=[];
    upsellio_public_editorial_assets();
    check(count($styles)===1 && $styles[0][0]==="upsellio-public-editorial","Public stylesheet: ".$type);
    check(count($scripts)===1 && $scripts[0][0]==="upsellio-ui" && $scripts[0][2]==="after","Public script: ".$type);
}
$js=$scripts[0][1];
foreach ([new WP_Post("ups_offer"),new WP_Post("page","draft"),new WP_Post("post","publish","secret")] as $post) {
    $styles=[]; $scripts=[]; upsellio_public_editorial_assets();
    check(!$styles && !$scripts,"Exclude non-public content");
}
$post=new WP_Post("page"); $slug="crm-app"; $styles=[]; $scripts=[];
upsellio_public_editorial_assets();
check(!$styles && !$scripts,"Exclude CRM");
$slug=""; $admin=true; upsellio_public_editorial_assets();
check(!$styles && !$scripts,"Exclude admin");
$admin=false; unset($post); upsellio_public_editorial_assets();
check(count($styles)===1 && count($scripts)===1,"Public archive or homepage");
$city=file_get_contents(get_template_directory()."/single-miasto.php");
$definition=file_get_contents(get_template_directory()."/single-definicja.php");
$css=file_get_contents(get_template_directory()."/style.css");
check(strpos($city,"Na co zwrócić uwagę w marketingu?")!==false && strpos($city,"Lokalny kontekst dla")===false,"Natural city heading");
check(strpos($city,'<details class="city-faq-item up-faq-disclosure">')!==false,"Native city FAQ");
check(strpos($definition,'<details class="definition-faq-item up-faq-disclosure">')!==false,"Native glossary FAQ");
check(strpos($definition,'<div class="definition-prose">')!==false,"Scoped glossary content");
check(strpos($css,".up-editorial-scroll")!==false && strpos($css,".up-faq-disclosure")!==false,"Shared styles");
check(strpos($css,".faq-item.open .faq-a{max-height:none")!==false,"Legacy FAQ answers do not clip");
check(strpos($city,"Potrzebujesz planu działań dla")===false && strpos($city,"Formularz kontaktowy dla")===false,"No malformed city inflections");
check(strpos($js,"table.closest")!==false && strpos($js,"revealFaq")!==false,"Progressive enhancement");
if (isset($argv[1])) { file_put_contents($argv[1],$js); }
echo "Public editorial scope and FAQ/table contracts passed\n";
