<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_strlen($value)
{
    $value = (string) $value;
    return function_exists("mb_strlen") ? mb_strlen($value) : strlen($value);
}

require_once get_template_directory() . "/inc/cities-data.php";
require_once get_template_directory() . "/inc/cities-seed.php";
require_once get_template_directory() . "/inc/definitions-data.php";
require_once get_template_directory() . "/inc/definitions-seed.php";
require_once get_template_directory() . "/inc/blog-seo-tool.php";
require_once get_template_directory() . "/inc/crm.php";
require_once get_template_directory() . "/inc/seo-automation.php";
require_once get_template_directory() . "/inc/data-schema.php";
require_once get_template_directory() . "/inc/site-analytics.php";

function upsellio_setup()
{
    add_theme_support("title-tag");
    add_theme_support("post-thumbnails");
    add_theme_support("html5", ["search-form", "comment-form", "comment-list", "gallery", "caption", "style", "script"]);

    register_nav_menus(
        [
            "primary" => __("Primary Menu", "upsellio"),
        ]
    );
}
add_action("after_setup_theme", "upsellio_setup");

function upsellio_primary_menu_name()
{
    return "Upsellio Primary Auto";
}

function upsellio_get_primary_navigation_links()
{
    $locations = get_nav_menu_locations();
    $menu_id = isset($locations["primary"]) ? (int) $locations["primary"] : 0;
    $links = [];

    if ($menu_id > 0) {
        $menu_items = wp_get_nav_menu_items($menu_id, ["update_post_term_cache" => false]);
        if (is_array($menu_items)) {
            foreach ($menu_items as $menu_item) {
                $url = isset($menu_item->url) ? (string) $menu_item->url : "";
                $title = isset($menu_item->title) ? wp_strip_all_tags((string) $menu_item->title) : "";
                if ($url === "" || $title === "") {
                    continue;
                }
                $links[] = [
                    "title" => $title,
                    "url" => $url,
                ];
            }
        }
    }

    if (!empty($links)) {
        return $links;
    }

    // Fallback if menu is not configured yet.
    $pages = get_pages([
        "post_status" => "publish",
        "sort_column" => "menu_order,post_title",
        "sort_order" => "ASC",
    ]);
    foreach ($pages as $page) {
        $links[] = [
            "title" => (string) $page->post_title,
            "url" => (string) get_permalink((int) $page->ID),
        ];
    }

    return $links;
}

function upsellio_build_page_tree($pages)
{
    $tree = [];
    foreach ((array) $pages as $page) {
        $parent_id = (int) $page->post_parent;
        if (!isset($tree[$parent_id])) {
            $tree[$parent_id] = [];
        }
        $tree[$parent_id][] = $page;
    }

    return $tree;
}

function upsellio_sync_primary_navigation_menu()
{
    $menu_name = upsellio_primary_menu_name();
    $menu_object = wp_get_nav_menu_object($menu_name);
    $menu_id = $menu_object ? (int) $menu_object->term_id : 0;

    if ($menu_id <= 0) {
        $menu_id = (int) wp_create_nav_menu($menu_name);
    }

    if ($menu_id <= 0) {
        return ["created" => 0, "updated" => 0, "message" => "menu_error"];
    }

    $existing_items = wp_get_nav_menu_items($menu_id, ["post_status" => "any"]);
    if (is_array($existing_items)) {
        foreach ($existing_items as $existing_item) {
            wp_delete_post((int) $existing_item->ID, true);
        }
    }

    $pages = get_pages([
        "post_status" => "publish",
        "sort_column" => "menu_order,post_title",
        "sort_order" => "ASC",
    ]);
    $page_tree = upsellio_build_page_tree($pages);
    $created = 0;

    $append_pages = function ($parent_page_id, $parent_menu_item_id) use (&$append_pages, $page_tree, $menu_id, &$created) {
        $children = isset($page_tree[$parent_page_id]) ? $page_tree[$parent_page_id] : [];
        foreach ($children as $page) {
            $menu_item_id = wp_update_nav_menu_item($menu_id, 0, [
                "menu-item-title" => (string) $page->post_title,
                "menu-item-object-id" => (int) $page->ID,
                "menu-item-object" => "page",
                "menu-item-type" => "post_type",
                "menu-item-status" => "publish",
                "menu-item-parent-id" => (int) $parent_menu_item_id,
            ]);
            if (!is_wp_error($menu_item_id)) {
                $created++;
                $append_pages((int) $page->ID, (int) $menu_item_id);
            }
        }
    };
    $append_pages(0, 0);

    $blog_page_id = (int) get_option("page_for_posts");
    if ($blog_page_id > 0) {
        $blog_page = get_post($blog_page_id);
        if ($blog_page instanceof WP_Post) {
            $blog_item = wp_update_nav_menu_item($menu_id, 0, [
                "menu-item-title" => (string) $blog_page->post_title,
                "menu-item-object-id" => $blog_page_id,
                "menu-item-object" => "page",
                "menu-item-type" => "post_type",
                "menu-item-status" => "publish",
                "menu-item-parent-id" => 0,
            ]);
            if (!is_wp_error($blog_item)) {
                $created++;
            }
        }
    }

    if (post_type_exists("definicja")) {
        $definitions_item = wp_update_nav_menu_item($menu_id, 0, [
            "menu-item-title" => "Definicje",
            "menu-item-url" => home_url("/definicje/"),
            "menu-item-type" => "custom",
            "menu-item-status" => "publish",
            "menu-item-parent-id" => 0,
        ]);
        if (!is_wp_error($definitions_item)) {
            $created++;
        }
    }

    if (post_type_exists("miasto")) {
        $cities_item = wp_update_nav_menu_item($menu_id, 0, [
            "menu-item-title" => "Miasta",
            "menu-item-url" => home_url("/miasta/"),
            "menu-item-type" => "custom",
            "menu-item-status" => "publish",
            "menu-item-parent-id" => 0,
        ]);
        if (!is_wp_error($cities_item)) {
            $created++;
        }
    }

    $locations = get_nav_menu_locations();
    $locations["primary"] = $menu_id;
    set_theme_mod("nav_menu_locations", $locations);

    return ["created" => $created, "updated" => 0, "message" => "ok"];
}

function upsellio_get_navigation_sync_url()
{
    return add_query_arg([
        "upsellio_sync_navigation" => 1,
        "_upsellio_nonce" => wp_create_nonce("upsellio_sync_navigation"),
    ], admin_url("themes.php?page=upsellio-navigation-sync"));
}

function upsellio_handle_navigation_sync_request()
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }

    if (!isset($_GET["upsellio_sync_navigation"])) {
        return;
    }

    $nonce = isset($_GET["_upsellio_nonce"]) ? sanitize_text_field(wp_unslash($_GET["_upsellio_nonce"])) : "";
    if (!wp_verify_nonce($nonce, "upsellio_sync_navigation")) {
        return;
    }

    $result = upsellio_sync_primary_navigation_menu();
    $redirect_url = add_query_arg([
        "upsellio_navigation_sync_done" => 1,
        "created" => (int) ($result["created"] ?? 0),
        "msg" => (string) ($result["message"] ?? "ok"),
    ], admin_url("themes.php?page=upsellio-navigation-sync"));
    wp_safe_redirect($redirect_url);
    exit;
}
add_action("admin_init", "upsellio_handle_navigation_sync_request");

function upsellio_navigation_sync_screen()
{
    if (!current_user_can("manage_options")) {
        return;
    }
    ?>
    <div class="wrap">
      <h1>Synchronizacja nawigacji</h1>
      <p>Jednym kliknięciem zaktualizujesz menu nawigacji na podstawie wszystkich opublikowanych stron i podstron.</p>
      <p><a class="button button-primary" href="<?php echo esc_url(upsellio_get_navigation_sync_url()); ?>">Wykonaj szybką aktualizację bazy menu</a></p>
    </div>
    <?php
}

function upsellio_register_navigation_sync_menu()
{
    add_theme_page(
        "Synchronizacja nawigacji",
        "Sync nawigacji",
        "manage_options",
        "upsellio-navigation-sync",
        "upsellio_navigation_sync_screen"
    );
}
add_action("admin_menu", "upsellio_register_navigation_sync_menu");

function upsellio_navigation_sync_admin_notice()
{
    if (!is_admin() || !isset($_GET["upsellio_navigation_sync_done"])) {
        return;
    }

    $created = isset($_GET["created"]) ? (int) $_GET["created"] : 0;
    $msg = isset($_GET["msg"]) ? sanitize_text_field(wp_unslash($_GET["msg"])) : "ok";
    if ($msg !== "ok") {
        echo '<div class="notice notice-error"><p>Nie udało się zsynchronizować nawigacji.</p></div>';
        return;
    }

    echo '<div class="notice notice-success"><p>';
    echo esc_html("Nawigacja została zsynchronizowana. Dodano pozycji: {$created}.");
    echo "</p></div>";
}
add_action("admin_notices", "upsellio_navigation_sync_admin_notice");

function upsellio_assets()
{
    $script_path = get_template_directory() . "/assets/js/upsellio.js";
    $script_uri = get_template_directory_uri() . "/assets/js/upsellio.js";
    $script_version = file_exists($script_path) ? (string) filemtime($script_path) : "1.0.0";

    wp_enqueue_script("upsellio-main", $script_uri, [], $script_version, true);
    wp_localize_script(
        "upsellio-main",
        "upsellioData",
        [
            "ajaxUrl" => admin_url("admin-ajax.php"),
            "blogNonce" => wp_create_nonce("upsellio_blog_filter"),
            "blogIndexUrl" => upsellio_get_blog_index_url(),
            "contactNonce" => wp_create_nonce("upsellio_contact_click"),
        ]
    );
}
add_action("wp_enqueue_scripts", "upsellio_assets");

function upsellio_city_seed_menu()
{
    add_submenu_page(
        "edit.php?post_type=miasto",
        "Generator miast SEO",
        "Generator SEO",
        "manage_options",
        "upsellio-seo-generator",
        "upsellio_city_seed_screen"
    );
}
add_action("admin_menu", "upsellio_city_seed_menu");

function upsellio_city_seed_screen()
{
    if (!current_user_can("manage_options")) {
        return;
    }
    ?>
    <div class="wrap">
      <h1>Generator podstron SEO dla miast</h1>
      <p>Wygeneruj 200 podstron lokalnych opartych o CPT <code>miasto</code>.</p>
      <p><a class="button button-primary" href="<?php echo esc_url(upsellio_get_seed_url(false)); ?>">Uruchom generator (jednorazowo)</a></p>
      <p><a class="button" href="<?php echo esc_url(upsellio_get_seed_url(true)); ?>">Wymus ponowne wygenerowanie</a></p>
      <p>Po uruchomieniu odswiez trwale linki: <strong>Ustawienia -> Bezposrednie odnosniki -> Zapisz</strong>.</p>
    </div>
    <?php
}

function upsellio_get_blog_index_url()
{
    $blog_page_id = (int) get_option("page_for_posts");
    $blog_index_url = $blog_page_id ? get_permalink($blog_page_id) : home_url("/");

    return $blog_index_url ?: home_url("/");
}

function upsellio_estimated_read_time($post_id)
{
    $content = wp_strip_all_tags((string) get_post_field("post_content", $post_id));
    $word_count = str_word_count($content);
    $minutes = max(1, (int) ceil($word_count / 220));

    return sprintf(__("%d min czytania", "upsellio"), $minutes);
}

function upsellio_parse_tag_filters($raw_tags)
{
    if (is_array($raw_tags)) {
        $candidates = $raw_tags;
    } else {
        $candidates = explode(",", (string) $raw_tags);
    }

    $sanitized_tags = [];
    foreach ($candidates as $tag_slug) {
        $tag_slug = sanitize_title(trim((string) $tag_slug));
        if ($tag_slug === "") {
            continue;
        }
        $sanitized_tags[] = $tag_slug;
    }

    $sanitized_tags = array_values(array_unique($sanitized_tags));

    return array_slice($sanitized_tags, 0, 3);
}

function upsellio_get_blog_payload($selected_category = "", $selected_tags = [], $search_term = "", $paged = 1)
{
    $query_args = [
        "post_type" => "post",
        "post_status" => "publish",
        "posts_per_page" => 7,
        "paged" => max(1, (int) $paged),
    ];

    if ($selected_category !== "" && $selected_category !== "all") {
        $query_args["category_name"] = $selected_category;
    }

    if (!empty($selected_tags)) {
        $query_args["tax_query"] = [
            [
                "taxonomy" => "post_tag",
                "field" => "slug",
                "terms" => $selected_tags,
                "operator" => "IN",
            ],
        ];
    }

    if ($search_term !== "") {
        $query_args["s"] = $search_term;
    }

    $blog_query = new WP_Query($query_args);
    $posts = $blog_query->posts;

    return [
        "blog_query" => $blog_query,
        "featured_post" => $posts ? $posts[0] : null,
        "regular_posts" => count($posts) > 1 ? array_slice($posts, 1) : [],
        "categories" => get_categories(["hide_empty" => true]),
        "tags" => get_tags(["hide_empty" => true]),
        "paged" => max(1, (int) $paged),
    ];
}

function upsellio_render_blog_dynamic_content($selected_category = "", $selected_tags = [], $search_term = "", $paged = 1)
{
    $data = upsellio_get_blog_payload($selected_category, $selected_tags, $search_term, $paged);
    $blog_query = $data["blog_query"];
    $featured_post = $data["featured_post"];
    $regular_posts = $data["regular_posts"];
    $categories = $data["categories"];
    $tags = $data["tags"];
    $current_paged = $data["paged"];
    $blog_index_url = upsellio_get_blog_index_url();

    ob_start();
    ?>
    <section class="ups-blog-featured-wrap">
      <div class="wrap ups-blog-featured-grid">
        <?php if ($featured_post) : ?>
          <?php
          $featured_categories = get_the_category($featured_post->ID);
          $featured_category = !empty($featured_categories) ? $featured_categories[0] : null;
          ?>
          <article class="ups-blog-featured-card">
            <div class="ups-blog-featured-main">
              <div class="ups-blog-featured-cover">
                <div class="ups-blog-featured-content">
                  <div class="ups-blog-featured-label">Wyróżniony wpis</div>
                  <div class="ups-blog-featured-title-shell">
                    <?php if ($featured_category) : ?>
                      <div class="ups-blog-featured-category"><?php echo esc_html($featured_category->name); ?></div>
                    <?php endif; ?>
                    <h2 class="ups-blog-featured-title"><?php echo esc_html(get_the_title($featured_post)); ?></h2>
                  </div>
                </div>
              </div>
              <div class="ups-blog-featured-text">
                <div>
                  <div class="ups-blog-featured-meta">
                    <?php echo esc_html(get_the_date("j F Y", $featured_post)); ?> · <?php echo esc_html(upsellio_estimated_read_time($featured_post->ID)); ?>
                  </div>
                  <p class="ups-blog-featured-excerpt"><?php echo esc_html(get_the_excerpt($featured_post)); ?></p>
                </div>
                <div class="ups-blog-actions">
                  <a href="<?php echo esc_url(get_permalink($featured_post)); ?>" class="ups-blog-btn-primary">Czytaj artykuł →</a>
                  <?php if ($featured_category) : ?>
                    <a href="<?php echo esc_url(get_category_link($featured_category)); ?>" class="ups-blog-btn-secondary">
                      Zobacz wszystkie <?php echo esc_html($featured_category->name); ?>
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </article>
        <?php else : ?>
          <div class="ups-blog-empty">
            Nie znaleziono wpisów pasujących do aktualnego filtrowania.
          </div>
        <?php endif; ?>

        <aside class="ups-blog-side">
          <div class="ups-blog-panel">
            <div class="eyebrow" style="margin-bottom: 0;">Newsletter / lead magnet</div>
            <h3 class="ups-blog-panel-title">Chcesz praktyczne materiały o reklamach i sprzedaży?</h3>
            <p class="ups-blog-panel-text">
              Raz na jakiś czas wyślę Ci konkretny materiał: checklistę, analizę albo wpis, który pomaga podejmować lepsze decyzje marketingowe.
            </p>
            <form class="ups-blog-newsletter" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" method="post" data-upsellio-lead-form="1">
              <input type="hidden" name="action" value="upsellio_submit_lead" />
              <input type="hidden" name="redirect_url" value="<?php echo esc_url($blog_index_url); ?>" />
              <input type="hidden" name="lead_form_origin" value="newsletter" />
              <input type="hidden" name="lead_source" value="newsletter" />
              <input type="hidden" name="lead_name" value="Newsletter" />
              <input type="hidden" name="lead_message" value="Nowa subskrypcja newslettera." />
              <input type="hidden" name="lead_consent" value="1" />
              <input type="hidden" name="utm_source" data-ups-utm="source" value="" />
              <input type="hidden" name="utm_medium" data-ups-utm="medium" value="" />
              <input type="hidden" name="utm_campaign" data-ups-utm="campaign" value="" />
              <input type="hidden" name="landing_url" data-ups-context="landing" value="" />
              <input type="hidden" name="referrer" data-ups-context="referrer" value="" />
              <input type="text" name="lead_website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;" />
              <?php wp_nonce_field("upsellio_unified_lead_form", "upsellio_lead_form_nonce"); ?>
              <input type="email" name="lead_email" placeholder="Twój e-mail" required />
              <button type="submit">Zapisz mnie</button>
            </form>
          </div>

          <div class="ups-blog-panel">
            <div class="eyebrow" style="margin-bottom: 0;">Popularne tematy</div>
            <div class="ups-blog-tags">
              <?php foreach (array_slice($tags, 0, 8) as $topic_tag) : ?>
                <span class="ups-blog-tag"><?php echo esc_html($topic_tag->name); ?></span>
              <?php endforeach; ?>
            </div>
          </div>
        </aside>
      </div>
    </section>

    <section>
      <div class="wrap ups-blog-list-wrap">
        <div class="ups-blog-list-head">
          <div>
            <div class="eyebrow" style="margin-bottom: 0;">Najnowsze wpisy</div>
            <h2 class="ups-blog-list-title">Wszystkie artykuły</h2>
          </div>
          <div class="ups-blog-list-meta">
            <?php echo esc_html((string) $blog_query->found_posts); ?> wpisów · sortowanie: najnowsze
          </div>
        </div>

        <?php if (!empty($regular_posts)) : ?>
          <div class="ups-blog-grid">
            <?php foreach ($regular_posts as $post_item) : ?>
              <?php
              $post_categories = get_the_category($post_item->ID);
              $post_category_name = !empty($post_categories) ? $post_categories[0]->name : "Artykuł";
              ?>
              <article class="ups-blog-card">
                <div class="ups-blog-card-category"><?php echo esc_html($post_category_name); ?></div>
                <h3 class="ups-blog-card-title"><?php echo esc_html(get_the_title($post_item)); ?></h3>
                <p class="ups-blog-card-excerpt"><?php echo esc_html(get_the_excerpt($post_item)); ?></p>
                <div class="ups-blog-card-footer">
                  <div class="ups-blog-card-meta">
                    <?php echo esc_html(upsellio_estimated_read_time($post_item->ID)); ?> · <?php echo esc_html(get_the_date("j F Y", $post_item)); ?>
                  </div>
                  <a class="ups-blog-card-link" href="<?php echo esc_url(get_permalink($post_item)); ?>">Czytaj dalej →</a>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php elseif (!$featured_post) : ?>
          <div class="ups-blog-empty">Nie znaleziono wpisów pasujących do aktualnego filtrowania.</div>
        <?php endif; ?>

        <?php
        $base_query_args = [];
        if ($selected_category !== "" && $selected_category !== "all") {
            $base_query_args["category"] = $selected_category;
        }
        if (!empty($selected_tags)) {
            $base_query_args["tags"] = implode(",", $selected_tags);
        }
        if ($search_term !== "") {
            $base_query_args["s"] = $search_term;
        }

        $base_url = add_query_arg($base_query_args, $blog_index_url);
        $pagination = paginate_links([
            "base" => esc_url(add_query_arg("paged", "%#%", $base_url)),
            "format" => "",
            "current" => $current_paged,
            "total" => $blog_query->max_num_pages,
            "type" => "array",
            "prev_text" => "← Poprzednia",
            "next_text" => "Następna →",
        ]);
        ?>
        <?php if (!empty($pagination)) : ?>
          <div class="ups-blog-pagination">
            <?php foreach ($pagination as $page_link) : ?>
              <?php
              $is_current = strpos($page_link, "current") !== false;
              $class_name = $is_current ? "ups-blog-page-link current" : "ups-blog-page-link";
              ?>
              <span class="<?php echo esc_attr($class_name); ?>"><?php echo wp_kses_post($page_link); ?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
    <?php

    wp_reset_postdata();

    return ob_get_clean();
}

function upsellio_ajax_filter_blog_posts()
{
    check_ajax_referer("upsellio_blog_filter", "nonce");

    $selected_category = isset($_POST["category"]) ? sanitize_title(wp_unslash($_POST["category"])) : "";
    $selected_tags = [];
    if (isset($_POST["tags"])) {
        $selected_tags = upsellio_parse_tag_filters(wp_unslash($_POST["tags"]));
    } elseif (isset($_POST["tag"])) {
        // Legacy fallback for older clients.
        $selected_tags = upsellio_parse_tag_filters(wp_unslash($_POST["tag"]));
    }
    $search_term = isset($_POST["search"]) ? sanitize_text_field(wp_unslash($_POST["search"])) : "";
    $paged = isset($_POST["paged"]) ? max(1, (int) $_POST["paged"]) : 1;

    wp_send_json_success([
        "html" => upsellio_render_blog_dynamic_content($selected_category, $selected_tags, $search_term, $paged),
    ]);
}
add_action("wp_ajax_upsellio_filter_blog_posts", "upsellio_ajax_filter_blog_posts");
add_action("wp_ajax_nopriv_upsellio_filter_blog_posts", "upsellio_ajax_filter_blog_posts");

function upsellio_submit_contact_form()
{
    check_ajax_referer("upsellio_contact_click", "nonce");

    $name = isset($_POST["name"]) ? sanitize_text_field(wp_unslash($_POST["name"])) : "";
    $email = isset($_POST["email"]) ? sanitize_email(wp_unslash($_POST["email"])) : "";
    $message = isset($_POST["message"]) ? sanitize_textarea_field(wp_unslash($_POST["message"])) : "";
    $phone = isset($_POST["phone"]) ? sanitize_text_field(wp_unslash($_POST["phone"])) : "";
    $service = isset($_POST["service"]) ? sanitize_text_field(wp_unslash($_POST["service"])) : "";
    $budget = isset($_POST["budget"]) ? sanitize_text_field(wp_unslash($_POST["budget"])) : "";
    $goal = isset($_POST["goal"]) ? sanitize_text_field(wp_unslash($_POST["goal"])) : "";
    $source = isset($_POST["source"]) ? esc_url_raw(wp_unslash($_POST["source"])) : "";
    $website = isset($_POST["website"]) ? sanitize_text_field(wp_unslash($_POST["website"])) : "";

    if ($website !== "") {
        wp_send_json_success([
            "message" => "Dziekujemy, formularz zostal wyslany.",
        ]);
    }

    if (upsellio_strlen($name) < 2) {
        wp_send_json_error([
            "message" => "Podaj imie i nazwe firmy.",
        ], 400);
    }

    if (!is_email($email)) {
        wp_send_json_error([
            "message" => "Podaj poprawny adres e-mail.",
        ], 400);
    }

    if (upsellio_strlen($message) < 10) {
        wp_send_json_error([
            "message" => "Opisz sytuacje w minimum 10 znakach.",
        ], 400);
    }

    $lead_id = upsellio_crm_create_lead([
        "name" => $name,
        "email" => $email,
        "phone" => $phone,
        "message" => $message,
        "service" => $service,
        "budget" => $budget,
        "goal" => $goal,
        "form_origin" => "contact-form-ajax",
        "source" => "contact-form",
        "landing_url" => $source,
        "referrer" => "",
    ]);

    if ($lead_id <= 0) {
        wp_send_json_error([
            "message" => "Nie udalo sie zapisac leada. Sprobuj ponownie za chwile.",
        ], 500);
    }
    upsellio_crm_send_emails($lead_id, $name, $email, $message);
    upsellio_crm_schedule_followup($lead_id);

    wp_send_json_success([
        "message" => "Wiadomosc wyslana. Odezwiemy sie wkrotce.",
    ]);
}
add_action("wp_ajax_upsellio_submit_contact_form", "upsellio_submit_contact_form");
add_action("wp_ajax_nopriv_upsellio_submit_contact_form", "upsellio_submit_contact_form");

