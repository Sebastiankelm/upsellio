<?php
if (!defined("ABSPATH")) {
    exit;
}

function upsellio_build_breadcrumb_items()
{
    if (is_front_page()) {
        return [];
    }

    $items = [
        ["label" => "Strona główna", "url" => home_url("/")],
    ];

    if (is_home() || is_singular("post") || is_category() || is_tag()) {
        $blog_page_id = (int) get_option("page_for_posts");
        $blog_url = $blog_page_id > 0 ? (string) get_permalink($blog_page_id) : home_url("/blog/");
        $items[] = ["label" => "Blog", "url" => $blog_url];
    }

    if (is_post_type_archive("definicja") || is_singular("definicja")) {
        $items[] = ["label" => "Słownik pojęć", "url" => home_url("/definicje/")];
    }

    if (is_post_type_archive("miasto") || is_singular("miasto")) {
        $items[] = ["label" => "Miasta", "url" => home_url("/miasta/")];
    }

    if (is_post_type_archive("portfolio") || is_singular("portfolio")) {
        $items[] = ["label" => "Portfolio", "url" => home_url("/portfolio/")];
    }

    if (is_post_type_archive("marketing_portfolio") || is_singular("marketing_portfolio")) {
        $items[] = ["label" => "Portfolio marketingowe", "url" => home_url("/portfolio-marketingowe/")];
    }

    if (is_post_type_archive("lead_magnet") || is_singular("lead_magnet")) {
        $items[] = ["label" => "Lead magnety", "url" => home_url("/lead-magnety/")];
    }

    if (is_singular()) {
        $items[] = ["label" => wp_strip_all_tags((string) get_the_title()), "url" => ""];
    } elseif (is_category()) {
        $items[] = ["label" => single_cat_title("", false), "url" => ""];
    } elseif (is_tag()) {
        $items[] = ["label" => single_tag_title("", false), "url" => ""];
    } elseif (is_search()) {
        $items[] = ["label" => "Wyniki wyszukiwania", "url" => ""];
    } elseif (is_post_type_archive()) {
        $post_type = get_post_type_object(get_query_var("post_type"));
        $items[] = ["label" => $post_type ? (string) $post_type->labels->name : "Archiwum", "url" => ""];
    } elseif (is_archive()) {
        $items[] = ["label" => wp_strip_all_tags((string) get_the_archive_title()), "url" => ""];
    } elseif (is_page()) {
        $items[] = ["label" => wp_strip_all_tags((string) get_the_title()), "url" => ""];
    }

    return $items;
}

function upsellio_render_breadcrumbs()
{
    $items = upsellio_build_breadcrumb_items();
    if (count($items) < 2) {
        return "";
    }

    $schema_items = [];
    foreach ($items as $index => $item) {
        $schema_items[] = [
            "@type" => "ListItem",
            "position" => $index + 1,
            "name" => (string) ($item["label"] ?? ""),
            "item" => (string) ($item["url"] !== "" ? $item["url"] : home_url(add_query_arg([]))),
        ];
    }

    ob_start();
    ?>
    <nav class="ups-breadcrumbs" aria-label="Breadcrumb">
      <div class="wrap">
        <ol class="ups-breadcrumbs__list">
          <?php foreach ($items as $index => $item) : ?>
            <?php $is_last = $index === count($items) - 1; ?>
            <li class="ups-breadcrumbs__item">
              <?php if (!$is_last && (string) ($item["url"] ?? "") !== "") : ?>
                <a href="<?php echo esc_url((string) $item["url"]); ?>"><?php echo esc_html((string) $item["label"]); ?></a>
              <?php else : ?>
                <span aria-current="page"><?php echo esc_html((string) $item["label"]); ?></span>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ol>
      </div>
      <script type="application/ld+json"><?php echo wp_json_encode(["@context" => "https://schema.org", "@type" => "BreadcrumbList", "itemListElement" => $schema_items], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
    </nav>
    <?php
    return ob_get_clean();
}
