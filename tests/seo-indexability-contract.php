<?php
// Standalone regression tests for SEO routing and sitemap rules.
define("ABSPATH", __DIR__);
function add_action(...$args) {}
function add_filter(...$args) {}
class WP_Post {
    public $ID, $post_type, $post_name, $post_status, $post_password;
    public function __construct($id, $type, $slug, $status = "publish", $password = "") {
        $this->ID = $id; $this->post_type = $type; $this->post_name = $slug;
        $this->post_status = $status; $this->post_password = $password;
    }
}
function get_post_meta($id, $key, $single = false) {
    global $meta;
    return $meta[$id][$key] ?? ($single ? "" : []);
}
function get_posts($args) {
    global $posts;
    return array_values(array_filter($posts, function ($p) use ($args) {
        return $p->post_type === $args["post_type"] && $p->post_status === "publish";
    }));
}
function untrailingslashit($s) { return rtrim($s, "/"); }
function get_option($key) { global $options; return $options[$key] ?? ""; }
function home_url($p) { return "https://upsellio.pl" . $p; }
function check($condition, $message) {
    if (!$condition) { fwrite(STDERR, $message . PHP_EOL); exit(1); }
}
require __DIR__ . "/../wp-content/themes/upsellio/inc/technical-seo.php";
require __DIR__ . "/../wp-content/themes/upsellio/inc/seo-indexing-fixes.php";
$posts = [new WP_Post(1, "miasto", "marketing-x"), new WP_Post(2, "miasto", "marketing-y"),
    new WP_Post(3, "miasto", "marketing-z"), new WP_Post(4, "miasto", "draft", "draft"),
    new WP_Post(5, "miasto", "private", "publish", "password")];
$meta = [1 => ["_upsellio_city_slug"=>"x", "_wp_old_slug"=>["former-x","ambiguous"]],
    2 => ["_wp_old_slug"=>["ambiguous"]], 3 => ["_wp_old_slug"=>["marketing-x"]]];
check(upsellio_seo_resolve_public_content_alias("miasto","x")->ID === 1, "Dataset alias");
check(upsellio_seo_resolve_public_content_alias("miasto","former-x")->ID === 1, "Old slug");
check(upsellio_seo_resolve_public_content_alias("miasto","ambiguous") === null, "Reject ambiguous");
check(upsellio_seo_resolve_public_content_alias("miasto","marketing-x")->ID === 1, "Current slug wins");
check(upsellio_seo_resolve_public_content_alias("miasto","draft") === null, "Exclude draft");
check(upsellio_seo_resolve_public_content_alias("miasto","private") === null, "Exclude password");
check(upsellio_seo_resolve_public_content_alias("miasto",["x"]) === null, "Reject array input");
check(upsellio_seo_resolve_public_content_alias("miasto","missing") === null, "No guessed target");
$url = home_url("/miasto/marketing-x/");
check(upsellio_sitemap_post_is_indexable($posts[0],$url), "Published page eligible");
$meta[1]["rank_math_robots"] = ["noindex"];
check(!upsellio_sitemap_post_is_indexable($posts[0],$url), "Exclude noindex");
unset($meta[1]["rank_math_robots"]);
$meta[1]["rank_math_canonical_url"] = home_url("/miasto/marketing-y/");
check(!upsellio_sitemap_post_is_indexable($posts[0],$url), "Exclude alternate canonical");
$meta[1]["rank_math_canonical_url"] = $url;
check(upsellio_sitemap_post_is_indexable($posts[0],$url), "Self canonical eligible");
check(!upsellio_sitemap_post_is_indexable(new WP_Post(8,"page","crm-app"),home_url("/crm-app/")), "Exclude CRM");
$options = ["blog_public"=>0];
check(strpos(upsellio_get_robots_txt_content(),"Disallow: /\n") !== false, "Respect private site");
check(upsellio_collect_sitemap_entries() === [], "Private sitemap empty");
check(upsellio_public_copy_short_dash("Nagłówek — treść — przykład") === "Nagłówek - treść - przykład", "Replace em dashes");
check(upsellio_public_copy_short_dash("2024–2026, e-commerce, 5 − 2") === "2024–2026, e-commerce, 5 − 2", "Preserve ranges, compounds and minus");
check(upsellio_public_copy_short_dash("Bez zmian.") === "Bez zmian.", "Ordinary copy unchanged");
check(upsellio_public_copy_output("<?xml version=\"1.0\"?><url>—</url>", PHP_OUTPUT_HANDLER_FINAL) === "<?xml version=\"1.0\"?><url>—</url>", "Non-HTML output untouched");
check(upsellio_public_copy_output("<html>—</html>", PHP_OUTPUT_HANDLER_FLUSH) === "<html>—</html>", "Partial output untouched");
check(upsellio_public_copy_normalize_html("<html>—</html>") === "<html>—</html>", "Fail closed without WordPress HTML API");
echo "SEO indexing and editorial typography regression tests passed\n";
