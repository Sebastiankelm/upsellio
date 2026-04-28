<?php

if (!defined("ABSPATH")) {
    exit;
}
function upsellio_get_lead_magnets_page_url()
{
    $lead_magnets_path = upsellio_get_special_navigation_path_by_title("Lead magnety", "/lead-magnety/");
    $lead_magnets_page = get_page_by_path(trim($lead_magnets_path, "/"));
    if ($lead_magnets_page instanceof WP_Post) {
        $permalink = get_permalink((int) $lead_magnets_page->ID);
        if ($permalink) {
            return $permalink;
        }
    }

    return home_url($lead_magnets_path);
}

function upsellio_append_special_navigation_links($links)
{
    $links = is_array($links) ? $links : [];
    $special_links = [];
    if (function_exists("upsellio_get_special_navigation_links_config")) {
        foreach (upsellio_get_special_navigation_links_config() as $configured_link) {
            $special_links[] = [
                "title" => (string) $configured_link["title"],
                "url" => home_url((string) $configured_link["path"]),
            ];
        }
    }

    foreach ($special_links as $special_link) {
        $special_url = (string) $special_link["url"];
        if ($special_url === "") {
            continue;
        }
        $already_exists = false;

        foreach ($links as $link) {
            $url = isset($link["url"]) ? (string) $link["url"] : "";
            if ($url !== "" && untrailingslashit($url) === untrailingslashit($special_url)) {
                $already_exists = true;
                break;
            }
        }

        if (!$already_exists) {
            $links[] = [
                "title" => (string) $special_link["title"],
                "url" => $special_url,
            ];
        }
    }

    return $links;
}

function upsellio_append_lead_magnets_link($links)
{
    return upsellio_append_special_navigation_links($links);
}

function upsellio_get_special_navigation_path_by_title($title, $default_path)
{
    $title = (string) $title;
    $default_path = "/" . ltrim((string) $default_path, "/");
    if (function_exists("upsellio_get_special_navigation_links_config")) {
        foreach (upsellio_get_special_navigation_links_config() as $configured_link) {
            if ((string) ($configured_link["title"] ?? "") === $title) {
                $path = (string) ($configured_link["path"] ?? "");
                return $path !== "" ? "/" . ltrim($path, "/") : $default_path;
            }
        }
    }

    return $default_path;
}

function upsellio_register_lead_magnets_cpt()
{
    register_post_type("lead_magnet", [
        "labels" => [
            "name" => "Materiały",
            "singular_name" => "Materiał",
            "add_new" => "Dodaj materiał",
            "add_new_item" => "Dodaj nowy materiał",
            "edit_item" => "Edytuj materiał",
            "new_item" => "Nowy materiał",
            "view_item" => "Zobacz materiał",
            "search_items" => "Szukaj materiałów",
            "not_found" => "Nie znaleziono materiałów",
            "menu_name" => "Materiały",
        ],
        "public" => true,
        "show_in_rest" => true,
        "menu_icon" => "dashicons-download",
        "supports" => ["title", "editor", "excerpt", "thumbnail", "page-attributes"],
        "has_archive" => false,
        "rewrite" => ["slug" => "lead-magnety", "with_front" => false],
    ]);

    register_taxonomy("lead_magnet_category", ["lead_magnet"], [
        "labels" => [
            "name" => "Kategorie materiałów",
            "singular_name" => "Kategoria materiału",
            "search_items" => "Szukaj kategorii",
            "all_items" => "Wszystkie kategorie",
            "edit_item" => "Edytuj kategorię",
            "update_item" => "Aktualizuj kategorię",
            "add_new_item" => "Dodaj nową kategorię",
            "new_item_name" => "Nowa kategoria",
            "menu_name" => "Kategorie",
        ],
        "hierarchical" => true,
        "show_admin_column" => true,
        "show_in_rest" => true,
        "rewrite" => ["slug" => "kategoria-lead-magnetu", "with_front" => false],
    ]);
}
add_action("init", "upsellio_register_lead_magnets_cpt");

function upsellio_add_lead_magnet_details_meta_box()
{
    add_meta_box(
        "upsellio_lead_magnet_details",
        "Dane katalogu materiału",
        "upsellio_render_lead_magnet_details_meta_box",
        "lead_magnet",
        "normal",
        "high"
    );
}
add_action("add_meta_boxes", "upsellio_add_lead_magnet_details_meta_box");

function upsellio_render_lead_magnet_details_meta_box($post)
{
    $post_id = (int) $post->ID;
    $type = (string) get_post_meta($post_id, "_ups_lm_type", true);
    $meta = (string) get_post_meta($post_id, "_ups_lm_meta", true);
    $badge = (string) get_post_meta($post_id, "_ups_lm_badge", true);
    $cta = (string) get_post_meta($post_id, "_ups_lm_cta", true);
    $image = (string) get_post_meta($post_id, "_ups_lm_image", true);
    $is_featured = (string) get_post_meta($post_id, "_ups_lm_featured", true) === "1";
    $bullets = (string) get_post_meta($post_id, "_ups_lm_bullets", true);
    $is_gated = (string) get_post_meta($post_id, "_ups_lm_gated", true) === "1";
    $custom_html = (string) get_post_meta($post_id, "_ups_lm_custom_html", true);
    $custom_css = (string) get_post_meta($post_id, "_ups_lm_custom_css", true);
    $custom_js = (string) get_post_meta($post_id, "_ups_lm_custom_js", true);
    $pdf_url = (string) get_post_meta($post_id, "_ups_lm_pdf_url", true);

    wp_nonce_field("upsellio_lead_magnet_details", "upsellio_lead_magnet_details_nonce");
    ?>
    <p>
      <label for="ups_lm_type"><strong>Typ materiału</strong></label><br />
      <input type="text" id="ups_lm_type" name="ups_lm_type" value="<?php echo esc_attr($type); ?>" class="widefat" placeholder="np. Checklista, Audyt, Raport" />
    </p>
    <p>
      <label for="ups_lm_meta"><strong>Meta materiału</strong></label><br />
      <input type="text" id="ups_lm_meta" name="ups_lm_meta" value="<?php echo esc_attr($meta); ?>" class="widefat" placeholder="np. PDF · 7 min" />
    </p>
    <p>
      <label for="ups_lm_badge"><strong>Badge wyróżnienia</strong></label><br />
      <input type="text" id="ups_lm_badge" name="ups_lm_badge" value="<?php echo esc_attr($badge); ?>" class="widefat" placeholder="np. Najczęściej pobierany" />
    </p>
    <p>
      <label for="ups_lm_cta"><strong>Tekst CTA</strong></label><br />
      <input type="text" id="ups_lm_cta" name="ups_lm_cta" value="<?php echo esc_attr($cta); ?>" class="widefat" placeholder="np. Zobacz materiał" />
    </p>
    <p>
      <label for="ups_lm_image"><strong>URL obrazka (hero/karta)</strong></label><br />
      <input type="url" id="ups_lm_image" name="ups_lm_image" value="<?php echo esc_attr($image); ?>" class="widefat" placeholder="https://..." />
    </p>
    <p>
      <label for="ups_lm_pdf_url"><strong>URL pliku PDF do pobrania</strong></label><br />
      <input type="url" id="ups_lm_pdf_url" name="ups_lm_pdf_url" value="<?php echo esc_attr($pdf_url); ?>" class="widefat" placeholder="https://.../mini-audyt.pdf" />
    </p>
    <p>
      <label for="ups_lm_bullets"><strong>Co znajdziesz w środku (jedna pozycja na linię)</strong></label>
      <textarea id="ups_lm_bullets" name="ups_lm_bullets" class="widefat" rows="5" placeholder="Checklista błędów&#10;Punkty kontrolne&#10;Rekomendacje wdrożeniowe"><?php echo esc_textarea($bullets); ?></textarea>
    </p>
    <p>
      <label style="display:flex;align-items:flex-start;gap:8px;">
        <input type="checkbox" name="ups_lm_featured" value="1" <?php checked($is_featured); ?> />
        <span>Ustaw jako wyróżniony materiał na stronie katalogu.</span>
      </label>
    </p>
    <p>
      <label style="display:flex;align-items:flex-start;gap:8px;">
        <input type="checkbox" name="ups_lm_gated" value="1" <?php checked($is_gated); ?> />
        <span>Materiał premium: pokaż bramkę e-mail przed pobraniem/konsultacją.</span>
      </label>
    </p>
    <hr />
    <p><strong>Niestandardowy widok (HTML + CSS + JS)</strong><br />
      <span style="color:#6b7280;">Wklej kod, jeśli ten materiał ma mieć własny layout osadzony na stronie szczegółów.</span>
    </p>
    <p>
      <label for="ups_lm_custom_html"><strong>HTML</strong></label>
      <textarea id="ups_lm_custom_html" name="ups_lm_custom_html" class="widefat" rows="8"><?php echo esc_textarea($custom_html); ?></textarea>
    </p>
    <p>
      <label for="ups_lm_custom_css"><strong>CSS</strong></label>
      <textarea id="ups_lm_custom_css" name="ups_lm_custom_css" class="widefat" rows="8"><?php echo esc_textarea($custom_css); ?></textarea>
    </p>
    <p>
      <label for="ups_lm_custom_js"><strong>JS</strong></label>
      <textarea id="ups_lm_custom_js" name="ups_lm_custom_js" class="widefat" rows="8"><?php echo esc_textarea($custom_js); ?></textarea>
    </p>
    <?php
}

function upsellio_save_lead_magnet_details_meta_box($post_id)
{
    if (get_post_type((int) $post_id) !== "lead_magnet") {
        return;
    }
    if (!isset($_POST["upsellio_lead_magnet_details_nonce"])) {
        return;
    }

    $nonce = sanitize_text_field(wp_unslash($_POST["upsellio_lead_magnet_details_nonce"]));
    if (!wp_verify_nonce($nonce, "upsellio_lead_magnet_details")) {
        return;
    }
    if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can("edit_post", (int) $post_id)) {
        return;
    }

    $fields = [
        "_ups_lm_type" => isset($_POST["ups_lm_type"]) ? sanitize_text_field(wp_unslash($_POST["ups_lm_type"])) : "",
        "_ups_lm_meta" => isset($_POST["ups_lm_meta"]) ? sanitize_text_field(wp_unslash($_POST["ups_lm_meta"])) : "",
        "_ups_lm_badge" => isset($_POST["ups_lm_badge"]) ? sanitize_text_field(wp_unslash($_POST["ups_lm_badge"])) : "",
        "_ups_lm_cta" => isset($_POST["ups_lm_cta"]) ? sanitize_text_field(wp_unslash($_POST["ups_lm_cta"])) : "",
        "_ups_lm_image" => isset($_POST["ups_lm_image"]) ? esc_url_raw(wp_unslash($_POST["ups_lm_image"])) : "",
        "_ups_lm_pdf_url" => isset($_POST["ups_lm_pdf_url"]) ? esc_url_raw(wp_unslash($_POST["ups_lm_pdf_url"])) : "",
        "_ups_lm_bullets" => isset($_POST["ups_lm_bullets"]) ? sanitize_textarea_field(wp_unslash($_POST["ups_lm_bullets"])) : "",
    ];

    foreach ($fields as $meta_key => $meta_value) {
        update_post_meta((int) $post_id, $meta_key, $meta_value);
    }

    update_post_meta((int) $post_id, "_ups_lm_featured", isset($_POST["ups_lm_featured"]) ? "1" : "0");
    update_post_meta((int) $post_id, "_ups_lm_gated", isset($_POST["ups_lm_gated"]) ? "1" : "0");
    update_post_meta((int) $post_id, "_upsellio_is_lead_magnet", "1");

    $custom_html = isset($_POST["ups_lm_custom_html"]) ? wp_unslash($_POST["ups_lm_custom_html"]) : "";
    $custom_css = isset($_POST["ups_lm_custom_css"]) ? wp_unslash($_POST["ups_lm_custom_css"]) : "";
    $custom_js = isset($_POST["ups_lm_custom_js"]) ? wp_unslash($_POST["ups_lm_custom_js"]) : "";
    $payload = upsellio_prepare_custom_embed_payload((string) $custom_html, (string) $custom_css, (string) $custom_js);
    update_post_meta((int) $post_id, "_ups_lm_custom_html", (string) $payload["html"]);
    update_post_meta((int) $post_id, "_ups_lm_custom_css", (string) $payload["css"]);
    update_post_meta((int) $post_id, "_ups_lm_custom_js", (string) $payload["js"]);
}
add_action("save_post", "upsellio_save_lead_magnet_details_meta_box");

function upsellio_get_lead_magnet_list($limit = 30)
{
    $query = new WP_Query([
        "post_type" => "lead_magnet",
        "post_status" => "publish",
        "posts_per_page" => max(1, (int) $limit),
        "orderby" => "menu_order date",
        "order" => "ASC",
    ]);

    $items = [];
    foreach ((array) $query->posts as $post_item) {
        $post_id = (int) $post_item->ID;
        $terms = get_the_terms($post_id, "lead_magnet_category");
        $first_term = (!empty($terms) && !is_wp_error($terms)) ? $terms[0] : null;

        $items[] = [
            "id" => $post_id,
            "title" => (string) get_the_title($post_id),
            "url" => (string) get_permalink($post_id),
            "excerpt" => (string) get_the_excerpt($post_id),
            "type" => (string) get_post_meta($post_id, "_ups_lm_type", true),
            "meta" => (string) get_post_meta($post_id, "_ups_lm_meta", true),
            "badge" => (string) get_post_meta($post_id, "_ups_lm_badge", true),
            "cta" => (string) get_post_meta($post_id, "_ups_lm_cta", true),
            "image" => (string) get_post_meta($post_id, "_ups_lm_image", true),
            "pdf_url" => (string) get_post_meta($post_id, "_ups_lm_pdf_url", true),
            "bullets" => upsellio_parse_textarea_lines((string) get_post_meta($post_id, "_ups_lm_bullets", true), 8),
            "is_gated" => (string) get_post_meta($post_id, "_ups_lm_gated", true) === "1",
            "category" => $first_term ? (string) $first_term->name : "Lead generation",
            "category_slug" => $first_term ? (string) $first_term->slug : "lead-generation",
            "is_featured" => (string) get_post_meta($post_id, "_ups_lm_featured", true) === "1",
        ];
    }
    wp_reset_postdata();

    return $items;
}

function upsellio_ensure_lead_magnets_page_exists()
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }

    $lead_magnets_path = upsellio_get_special_navigation_path_by_title("Lead magnety", "/lead-magnety/");
    $lead_magnets_slug = trim((string) wp_parse_url($lead_magnets_path, PHP_URL_PATH), "/");
    if ($lead_magnets_slug === "") {
        $lead_magnets_slug = "lead-magnety";
    }
    upsellio_upsert_page_with_template($lead_magnets_slug, "Lead magnety", "page-lead-magnety.php");
}
add_action("admin_init", "upsellio_ensure_lead_magnets_page_exists");

function upsellio_maybe_flush_lead_magnet_rewrite()
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }

    $version_key = "upsellio_lead_magnet_rewrite_version";
    $target_version = "2026-04-27-lead-magnety-base";
    if ((string) get_option($version_key, "") === $target_version) {
        return;
    }

    flush_rewrite_rules(false);
    update_option($version_key, $target_version, false);
}
add_action("admin_init", "upsellio_maybe_flush_lead_magnet_rewrite");

function upsellio_get_portfolio_page_url()
{
    $portfolio_path = upsellio_get_special_navigation_path_by_title("Portfolio", "/portfolio/");
    $portfolio_page = get_page_by_path(trim($portfolio_path, "/"));
    if ($portfolio_page instanceof WP_Post) {
        $permalink = get_permalink((int) $portfolio_page->ID);
        if ($permalink) {
            return $permalink;
        }
    }

    return home_url($portfolio_path);
}

function upsellio_register_portfolio_cpt()
{
    register_post_type("portfolio", [
        "labels" => [
            "name" => "Portfolio",
            "singular_name" => "Projekt portfolio",
            "add_new" => "Dodaj projekt",
            "add_new_item" => "Dodaj nowy projekt portfolio",
            "edit_item" => "Edytuj projekt",
            "new_item" => "Nowy projekt",
            "view_item" => "Zobacz projekt",
            "search_items" => "Szukaj projektów",
            "not_found" => "Nie znaleziono projektów",
            "menu_name" => "Portfolio",
        ],
        "public" => true,
        "show_in_rest" => true,
        "menu_icon" => "dashicons-portfolio",
        "supports" => ["title", "editor", "excerpt", "thumbnail", "page-attributes"],
        "has_archive" => false,
        "rewrite" => ["slug" => "realizacja", "with_front" => false],
    ]);

    register_taxonomy("portfolio_category", ["portfolio"], [
        "labels" => [
            "name" => "Kategorie portfolio",
            "singular_name" => "Kategoria portfolio",
            "search_items" => "Szukaj kategorii",
            "all_items" => "Wszystkie kategorie",
            "edit_item" => "Edytuj kategorię",
            "update_item" => "Aktualizuj kategorię",
            "add_new_item" => "Dodaj nową kategorię",
            "new_item_name" => "Nowa kategoria",
            "menu_name" => "Kategorie",
        ],
        "hierarchical" => true,
        "show_admin_column" => true,
        "show_in_rest" => true,
        "rewrite" => ["slug" => "kategoria-portfolio", "with_front" => false],
    ]);
}
add_action("init", "upsellio_register_portfolio_cpt");

function upsellio_use_classic_editor_for_portfolio_meta_boxes($use_block_editor, $post_type)
{
    if (in_array((string) $post_type, ["portfolio", "marketing_portfolio"], true)) {
        return false;
    }

    return $use_block_editor;
}
add_filter("use_block_editor_for_post_type", "upsellio_use_classic_editor_for_portfolio_meta_boxes", 10, 2);

function upsellio_add_portfolio_details_meta_box()
{
    add_meta_box(
        "upsellio_portfolio_details",
        "Dane projektu portfolio",
        "upsellio_render_portfolio_details_meta_box",
        "portfolio",
        "normal",
        "high"
    );
}
add_action("add_meta_boxes", "upsellio_add_portfolio_details_meta_box");

function upsellio_render_portfolio_details_meta_box($post)
{
    $post_id = (int) $post->ID;
    $type = (string) get_post_meta($post_id, "_ups_port_type", true);
    $meta = (string) get_post_meta($post_id, "_ups_port_meta", true);
    $badge = (string) get_post_meta($post_id, "_ups_port_badge", true);
    $cta = (string) get_post_meta($post_id, "_ups_port_cta", true);
    $image = (string) get_post_meta($post_id, "_ups_port_image", true);
    $result = (string) get_post_meta($post_id, "_ups_port_result", true);
    $problem = (string) get_post_meta($post_id, "_ups_port_problem", true);
    $scope = (string) get_post_meta($post_id, "_ups_port_scope", true);
    $external_url = (string) get_post_meta($post_id, "_ups_port_external_url", true);
    $metrics = (string) get_post_meta($post_id, "_ups_port_metrics", true);
    $technologies = (string) get_post_meta($post_id, "_ups_port_technologies", true);
    $client_quote = (string) get_post_meta($post_id, "_ups_port_client_quote", true);
    $has_publish_consent = (string) get_post_meta($post_id, "_ups_port_publish_consent", true) === "1";
    $is_featured = (string) get_post_meta($post_id, "_ups_port_featured", true) === "1";
    $custom_html = (string) get_post_meta($post_id, "_ups_port_custom_html", true);
    $custom_css = (string) get_post_meta($post_id, "_ups_port_custom_css", true);
    $custom_js = (string) get_post_meta($post_id, "_ups_port_custom_js", true);

    wp_nonce_field("upsellio_portfolio_details", "upsellio_portfolio_details_nonce");
    ?>
    <p>
      <label for="ups_port_type"><strong>Typ realizacji</strong></label><br />
      <input type="text" id="ups_port_type" name="ups_port_type" value="<?php echo esc_attr($type); ?>" class="widefat" placeholder="np. Strona firmowa, Aplikacja webowa, E-commerce" />
    </p>
    <p>
      <label for="ups_port_meta"><strong>Meta projektu</strong></label><br />
      <input type="text" id="ups_port_meta" name="ups_port_meta" value="<?php echo esc_attr($meta); ?>" class="widefat" placeholder="np. B2B · UX · SEO · Konwersja" />
    </p>
    <p>
      <label for="ups_port_badge"><strong>Badge wyróżnienia</strong></label><br />
      <input type="text" id="ups_port_badge" name="ups_port_badge" value="<?php echo esc_attr($badge); ?>" class="widefat" placeholder="np. Wyróżniony projekt" />
    </p>
    <p>
      <label for="ups_port_cta"><strong>Tekst CTA</strong></label><br />
      <input type="text" id="ups_port_cta" name="ups_port_cta" value="<?php echo esc_attr($cta); ?>" class="widefat" placeholder="np. Zobacz case study" />
    </p>
    <p>
      <label for="ups_port_image"><strong>URL obrazka projektu</strong></label><br />
      <input type="url" id="ups_port_image" name="ups_port_image" value="<?php echo esc_attr($image); ?>" class="widefat" placeholder="https://..." />
    </p>
    <p>
      <label for="ups_port_external_url"><strong>Link zewnętrzny (opcjonalnie)</strong></label><br />
      <input type="url" id="ups_port_external_url" name="ups_port_external_url" value="<?php echo esc_attr($external_url); ?>" class="widefat" placeholder="https://..." />
    </p>
    <p>
      <label for="ups_port_problem"><strong>Problem biznesowy (SEO + case study)</strong></label>
      <textarea id="ups_port_problem" name="ups_port_problem" class="widefat" rows="4" placeholder="Jakie wyzwanie miał klient?"><?php echo esc_textarea($problem); ?></textarea>
    </p>
    <p>
      <label for="ups_port_scope"><strong>Zakres prac</strong></label>
      <textarea id="ups_port_scope" name="ups_port_scope" class="widefat" rows="4" placeholder="Jakie działania zostały wykonane?"><?php echo esc_textarea($scope); ?></textarea>
    </p>
    <p>
      <label for="ups_port_result"><strong>Efekt biznesowy</strong></label>
      <textarea id="ups_port_result" name="ups_port_result" class="widefat" rows="4" placeholder="Jaki był rezultat projektu?"><?php echo esc_textarea($result); ?></textarea>
    </p>
    <p>
      <label for="ups_port_metrics"><strong>Metryki projektu (jedna na linię)</strong></label>
      <textarea id="ups_port_metrics" name="ups_port_metrics" class="widefat" rows="5" placeholder="np. +42% zapytań&#10;-31% CPL&#10;+19% konwersji"><?php echo esc_textarea($metrics); ?></textarea>
    </p>
    <p>
      <label for="ups_port_technologies"><strong>Technologie / narzędzia (jedna pozycja na linię)</strong></label>
      <textarea id="ups_port_technologies" name="ups_port_technologies" class="widefat" rows="4" placeholder="WordPress&#10;GA4&#10;Google Tag Manager"><?php echo esc_textarea($technologies); ?></textarea>
    </p>
    <p>
      <label for="ups_port_client_quote"><strong>Cytat klienta / social proof</strong></label>
      <textarea id="ups_port_client_quote" name="ups_port_client_quote" class="widefat" rows="4" placeholder="Krótka opinia klienta, jeśli masz zgodę na publikację."><?php echo esc_textarea($client_quote); ?></textarea>
    </p>
    <p>
      <label style="display:flex;align-items:flex-start;gap:8px;">
        <input type="checkbox" name="ups_port_publish_consent" value="1" <?php checked($has_publish_consent); ?> />
        <span>Mam zgodę na publikację danych, cytatu lub nazwy klienta w tym case study.</span>
      </label>
    </p>
    <p>
      <label style="display:flex;align-items:flex-start;gap:8px;">
        <input type="checkbox" name="ups_port_featured" value="1" <?php checked($is_featured); ?> />
        <span>Ustaw jako wyróżniony projekt w katalogu portfolio.</span>
      </label>
    </p>
    <hr />
    <p><strong>Niestandardowy widok projektu (HTML + CSS + JS)</strong><br />
      <span style="color:#6b7280;">Wklej kod, jeśli ta realizacja ma mieć dedykowaną sekcję osadzoną na podstronie case study.</span>
    </p>
    <p>
      <label for="ups_port_custom_html"><strong>HTML</strong></label>
      <textarea id="ups_port_custom_html" name="ups_port_custom_html" class="widefat" rows="8"><?php echo esc_textarea($custom_html); ?></textarea>
    </p>
    <p>
      <label for="ups_port_custom_css"><strong>CSS</strong></label>
      <textarea id="ups_port_custom_css" name="ups_port_custom_css" class="widefat" rows="8"><?php echo esc_textarea($custom_css); ?></textarea>
    </p>
    <p>
      <label for="ups_port_custom_js"><strong>JS</strong></label>
      <textarea id="ups_port_custom_js" name="ups_port_custom_js" class="widefat" rows="8"><?php echo esc_textarea($custom_js); ?></textarea>
    </p>
    <?php
}

function upsellio_save_portfolio_details_meta_box($post_id)
{
    if (get_post_type((int) $post_id) !== "portfolio") {
        return;
    }
    if (!isset($_POST["upsellio_portfolio_details_nonce"])) {
        return;
    }

    $nonce = sanitize_text_field(wp_unslash($_POST["upsellio_portfolio_details_nonce"]));
    if (!wp_verify_nonce($nonce, "upsellio_portfolio_details")) {
        return;
    }
    if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can("edit_post", (int) $post_id)) {
        return;
    }

    $fields = [
        "_ups_port_type" => isset($_POST["ups_port_type"]) ? sanitize_text_field(wp_unslash($_POST["ups_port_type"])) : "",
        "_ups_port_meta" => isset($_POST["ups_port_meta"]) ? sanitize_text_field(wp_unslash($_POST["ups_port_meta"])) : "",
        "_ups_port_badge" => isset($_POST["ups_port_badge"]) ? sanitize_text_field(wp_unslash($_POST["ups_port_badge"])) : "",
        "_ups_port_cta" => isset($_POST["ups_port_cta"]) ? sanitize_text_field(wp_unslash($_POST["ups_port_cta"])) : "",
        "_ups_port_image" => isset($_POST["ups_port_image"]) ? esc_url_raw(wp_unslash($_POST["ups_port_image"])) : "",
        "_ups_port_external_url" => isset($_POST["ups_port_external_url"]) ? esc_url_raw(wp_unslash($_POST["ups_port_external_url"])) : "",
        "_ups_port_problem" => isset($_POST["ups_port_problem"]) ? sanitize_textarea_field(wp_unslash($_POST["ups_port_problem"])) : "",
        "_ups_port_scope" => isset($_POST["ups_port_scope"]) ? sanitize_textarea_field(wp_unslash($_POST["ups_port_scope"])) : "",
        "_ups_port_result" => isset($_POST["ups_port_result"]) ? sanitize_textarea_field(wp_unslash($_POST["ups_port_result"])) : "",
        "_ups_port_metrics" => isset($_POST["ups_port_metrics"]) ? sanitize_textarea_field(wp_unslash($_POST["ups_port_metrics"])) : "",
        "_ups_port_technologies" => isset($_POST["ups_port_technologies"]) ? sanitize_textarea_field(wp_unslash($_POST["ups_port_technologies"])) : "",
        "_ups_port_client_quote" => isset($_POST["ups_port_client_quote"]) ? sanitize_textarea_field(wp_unslash($_POST["ups_port_client_quote"])) : "",
    ];

    foreach ($fields as $meta_key => $meta_value) {
        update_post_meta((int) $post_id, $meta_key, $meta_value);
    }

    update_post_meta((int) $post_id, "_ups_port_featured", isset($_POST["ups_port_featured"]) ? "1" : "0");
    update_post_meta((int) $post_id, "_ups_port_publish_consent", isset($_POST["ups_port_publish_consent"]) ? "1" : "0");

    $custom_html = isset($_POST["ups_port_custom_html"]) ? wp_unslash($_POST["ups_port_custom_html"]) : "";
    $custom_css = isset($_POST["ups_port_custom_css"]) ? wp_unslash($_POST["ups_port_custom_css"]) : "";
    $custom_js = isset($_POST["ups_port_custom_js"]) ? wp_unslash($_POST["ups_port_custom_js"]) : "";
    $payload = upsellio_prepare_custom_embed_payload((string) $custom_html, (string) $custom_css, (string) $custom_js);
    update_post_meta((int) $post_id, "_ups_port_custom_html", (string) $payload["html"]);
    update_post_meta((int) $post_id, "_ups_port_custom_css", (string) $payload["css"]);
    update_post_meta((int) $post_id, "_ups_port_custom_js", (string) $payload["js"]);
}
add_action("save_post", "upsellio_save_portfolio_details_meta_box");

function upsellio_parse_metrics_lines($value)
{
    $raw_lines = preg_split("/\r\n|\r|\n/", (string) $value);
    $lines = [];
    foreach ((array) $raw_lines as $line) {
        $line = trim((string) $line);
        if ($line === "") {
            continue;
        }
        $lines[] = $line;
    }

    return array_slice($lines, 0, 8);
}

function upsellio_get_portfolio_list($limit = 60)
{
    $query = new WP_Query([
        "post_type" => "portfolio",
        "post_status" => "publish",
        "posts_per_page" => max(1, (int) $limit),
        "orderby" => "menu_order date",
        "order" => "ASC",
    ]);

    $items = [];
    foreach ((array) $query->posts as $post_item) {
        $post_id = (int) $post_item->ID;
        $terms = get_the_terms($post_id, "portfolio_category");
        $first_term = (!empty($terms) && !is_wp_error($terms)) ? $terms[0] : null;

        $port_image = (string) get_post_meta($post_id, "_ups_port_image", true);
        $featured_image_url = "";
        if ($port_image === "" && has_post_thumbnail($post_id)) {
            $featured_image_url = (string) get_the_post_thumbnail_url($post_id, "large");
        }
        $thumbnail = $port_image !== "" ? $port_image : $featured_image_url;

        $items[] = [
            "id" => $post_id,
            "title" => (string) get_the_title($post_id),
            "url" => (string) get_permalink($post_id),
            "excerpt" => (string) get_the_excerpt($post_id),
            "type" => (string) get_post_meta($post_id, "_ups_port_type", true),
            "meta" => (string) get_post_meta($post_id, "_ups_port_meta", true),
            "badge" => (string) get_post_meta($post_id, "_ups_port_badge", true),
            "cta" => (string) get_post_meta($post_id, "_ups_port_cta", true),
            "image" => $port_image,
            "thumbnail" => $thumbnail,
            "external_url" => (string) get_post_meta($post_id, "_ups_port_external_url", true),
            "problem" => (string) get_post_meta($post_id, "_ups_port_problem", true),
            "scope" => (string) get_post_meta($post_id, "_ups_port_scope", true),
            "result" => (string) get_post_meta($post_id, "_ups_port_result", true),
            "metrics" => upsellio_parse_metrics_lines((string) get_post_meta($post_id, "_ups_port_metrics", true)),
            "technologies" => upsellio_parse_metrics_lines((string) get_post_meta($post_id, "_ups_port_technologies", true)),
            "client_quote" => (string) get_post_meta($post_id, "_ups_port_client_quote", true),
            "has_publish_consent" => (string) get_post_meta($post_id, "_ups_port_publish_consent", true) === "1",
            "category" => $first_term ? (string) $first_term->name : "Realizacje",
            "category_slug" => $first_term ? (string) $first_term->slug : "realizacje",
            "is_featured" => (string) get_post_meta($post_id, "_ups_port_featured", true) === "1",
        ];
    }
    wp_reset_postdata();

    return $items;
}

function upsellio_get_initials_from_text($text, $limit = 2)
{
    $text = trim((string) $text);
    if ($text === "") {
        return "";
    }
    $parts = preg_split("/\s+/", $text);
    $initials = "";
    foreach ((array) $parts as $part) {
        if ($part === "" || mb_strlen($initials) >= $limit) {
            continue;
        }
        $initials .= mb_strtoupper(mb_substr($part, 0, 1));
    }
    return $initials;
}

function upsellio_split_metric_line($line)
{
    $line = trim((string) $line);
    if ($line === "") {
        return ["value" => "", "label" => ""];
    }
    if (mb_strpos($line, ":") !== false) {
        $parts = explode(":", $line, 2);
        $label = trim((string) $parts[0]);
        $value = trim((string) ($parts[1] ?? ""));
        if ($value !== "") {
            return ["value" => $value, "label" => $label];
        }
    }
    if (preg_match("/^([+\-]?[\d][\d\.,]*\s*(?:%|×|x|pkt|pt)?)\s*(.*)$/u", $line, $matches)) {
        $value = trim((string) $matches[1]);
        $label = trim((string) $matches[2]);
        if ($value !== "") {
            return ["value" => $value, "label" => $label];
        }
    }
    return ["value" => "", "label" => $line];
}

function upsellio_ensure_portfolio_page_exists()
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }

    $portfolio_path = upsellio_get_special_navigation_path_by_title("Portfolio", "/portfolio/");
    $portfolio_slug = trim((string) wp_parse_url($portfolio_path, PHP_URL_PATH), "/");
    if ($portfolio_slug === "") {
        $portfolio_slug = "portfolio";
    }
    upsellio_upsert_page_with_template($portfolio_slug, "Portfolio", "page-portfolio.php");
}
add_action("admin_init", "upsellio_ensure_portfolio_page_exists");

function upsellio_get_marketing_portfolio_page_url()
{
    $marketing_portfolio_path = upsellio_get_special_navigation_path_by_title("Portfolio marketingowe", "/portfolio-marketingowe/");
    $page = get_page_by_path(trim($marketing_portfolio_path, "/"));
    if ($page instanceof WP_Post) {
        $permalink = get_permalink((int) $page->ID);
        if ($permalink) {
            return $permalink;
        }
    }

    return home_url($marketing_portfolio_path);
}

function upsellio_register_marketing_portfolio_cpt()
{
    register_post_type("marketing_portfolio", [
        "labels" => [
            "name" => "Portfolio marketingowe",
            "singular_name" => "Case marketingowy",
            "add_new" => "Dodaj case",
            "add_new_item" => "Dodaj nowy case marketingowy",
            "edit_item" => "Edytuj case marketingowy",
            "new_item" => "Nowy case marketingowy",
            "view_item" => "Zobacz case",
            "search_items" => "Szukaj case studies",
            "not_found" => "Nie znaleziono case studies",
            "menu_name" => "Portfolio marketingowe",
        ],
        "public" => true,
        "show_in_rest" => true,
        "menu_icon" => "dashicons-chart-line",
        "supports" => ["title", "editor", "excerpt", "thumbnail", "page-attributes"],
        "has_archive" => false,
        "rewrite" => ["slug" => "portfolio-marketingowe", "with_front" => false],
    ]);

    register_taxonomy("marketing_portfolio_category", ["marketing_portfolio"], [
        "labels" => [
            "name" => "Kategorie case studies",
            "singular_name" => "Kategoria case study",
            "search_items" => "Szukaj kategorii",
            "all_items" => "Wszystkie kategorie",
            "edit_item" => "Edytuj kategorię",
            "update_item" => "Aktualizuj kategorię",
            "add_new_item" => "Dodaj nową kategorię",
            "new_item_name" => "Nowa kategoria",
            "menu_name" => "Kategorie",
        ],
        "hierarchical" => true,
        "show_admin_column" => true,
        "show_in_rest" => true,
        "rewrite" => ["slug" => "kategoria-portfolio-marketingowego", "with_front" => false],
    ]);
}
add_action("init", "upsellio_register_marketing_portfolio_cpt");

function upsellio_add_marketing_portfolio_details_meta_box()
{
    add_meta_box(
        "upsellio_marketing_portfolio_details",
        "Dane case study marketingowego",
        "upsellio_render_marketing_portfolio_details_meta_box",
        "marketing_portfolio",
        "normal",
        "high"
    );
}
add_action("add_meta_boxes", "upsellio_add_marketing_portfolio_details_meta_box");

function upsellio_render_marketing_portfolio_details_meta_box($post)
{
    $post_id = (int) $post->ID;
    $type = (string) get_post_meta($post_id, "_ups_mport_type", true);
    $meta = (string) get_post_meta($post_id, "_ups_mport_meta", true);
    $badge = (string) get_post_meta($post_id, "_ups_mport_badge", true);
    $cta = (string) get_post_meta($post_id, "_ups_mport_cta", true);
    $image = (string) get_post_meta($post_id, "_ups_mport_image", true);
    $date = (string) get_post_meta($post_id, "_ups_mport_date", true);
    $sector = (string) get_post_meta($post_id, "_ups_mport_sector", true);
    $problem = (string) get_post_meta($post_id, "_ups_mport_problem", true);
    $solution = (string) get_post_meta($post_id, "_ups_mport_solution", true);
    $result = (string) get_post_meta($post_id, "_ups_mport_result", true);
    $tags = (string) get_post_meta($post_id, "_ups_mport_tags", true);
    $kpis = (string) get_post_meta($post_id, "_ups_mport_kpis", true);
    $theme = (string) get_post_meta($post_id, "_ups_mport_theme", true);
    $is_featured = (string) get_post_meta($post_id, "_ups_mport_featured", true) === "1";
    $custom_html = (string) get_post_meta($post_id, "_ups_mport_custom_html", true);
    $custom_css = (string) get_post_meta($post_id, "_ups_mport_custom_css", true);
    $custom_js = (string) get_post_meta($post_id, "_ups_mport_custom_js", true);
    $seo_title = (string) get_post_meta($post_id, "_ups_mport_seo_title", true);
    $seo_description = (string) get_post_meta($post_id, "_ups_mport_seo_description", true);
    $seo_canonical = (string) get_post_meta($post_id, "_ups_mport_seo_canonical", true);

    wp_nonce_field("upsellio_marketing_portfolio_details", "upsellio_marketing_portfolio_details_nonce");
    ?>
    <p>
      <label for="ups_mport_type"><strong>Typ case study</strong></label><br />
      <input type="text" id="ups_mport_type" name="ups_mport_type" value="<?php echo esc_attr($type); ?>" class="widefat" placeholder="np. Meta Ads, Google Ads, Landing page" />
    </p>
    <p>
      <label for="ups_mport_meta"><strong>Meta projektu</strong></label><br />
      <input type="text" id="ups_mport_meta" name="ups_mport_meta" value="<?php echo esc_attr($meta); ?>" class="widefat" placeholder="np. Lead generation · B2B · Q1 2024" />
    </p>
    <p>
      <label for="ups_mport_badge"><strong>Badge</strong></label><br />
      <input type="text" id="ups_mport_badge" name="ups_mport_badge" value="<?php echo esc_attr($badge); ?>" class="widefat" placeholder="np. Meta Ads" />
    </p>
    <p>
      <label for="ups_mport_cta"><strong>CTA na karcie/listingu</strong></label><br />
      <input type="text" id="ups_mport_cta" name="ups_mport_cta" value="<?php echo esc_attr($cta); ?>" class="widefat" placeholder="np. Zobacz case study" />
    </p>
    <p>
      <label for="ups_mport_image"><strong>URL obrazka</strong></label><br />
      <input type="url" id="ups_mport_image" name="ups_mport_image" value="<?php echo esc_attr($image); ?>" class="widefat" placeholder="https://..." />
    </p>
    <p>
      <label for="ups_mport_theme"><strong>Motyw wizualny karty</strong></label><br />
      <select id="ups_mport_theme" name="ups_mport_theme" class="widefat">
        <option value="">Domyślny</option>
        <option value="vis-meta" <?php selected($theme, "vis-meta"); ?>>Meta Ads</option>
        <option value="vis-google" <?php selected($theme, "vis-google"); ?>>Google Ads</option>
        <option value="vis-ecom" <?php selected($theme, "vis-ecom"); ?>>E-commerce</option>
        <option value="vis-landing" <?php selected($theme, "vis-landing"); ?>>Landing page</option>
        <option value="vis-b2b" <?php selected($theme, "vis-b2b"); ?>>B2B</option>
        <option value="vis-social" <?php selected($theme, "vis-social"); ?>>Social</option>
      </select>
    </p>
    <p>
      <label for="ups_mport_date"><strong>Data/okres case study</strong></label><br />
      <input type="text" id="ups_mport_date" name="ups_mport_date" value="<?php echo esc_attr($date); ?>" class="widefat" placeholder="np. Q1 2024" />
    </p>
    <p>
      <label for="ups_mport_sector"><strong>Sektor klienta</strong></label><br />
      <input type="text" id="ups_mport_sector" name="ups_mport_sector" value="<?php echo esc_attr($sector); ?>" class="widefat" placeholder="np. Firma usługowa B2B" />
    </p>
    <p>
      <label for="ups_mport_problem"><strong>Problem wyjściowy</strong></label>
      <textarea id="ups_mport_problem" name="ups_mport_problem" class="widefat" rows="4"><?php echo esc_textarea($problem); ?></textarea>
    </p>
    <p>
      <label for="ups_mport_solution"><strong>Rozwiązanie / zakres działań</strong></label>
      <textarea id="ups_mport_solution" name="ups_mport_solution" class="widefat" rows="4"><?php echo esc_textarea($solution); ?></textarea>
    </p>
    <p>
      <label for="ups_mport_result"><strong>Wynik biznesowy</strong></label>
      <textarea id="ups_mport_result" name="ups_mport_result" class="widefat" rows="4"><?php echo esc_textarea($result); ?></textarea>
    </p>
    <p>
      <label for="ups_mport_tags"><strong>Tagi (jedna pozycja na linię)</strong></label>
      <textarea id="ups_mport_tags" name="ups_mport_tags" class="widefat" rows="4" placeholder="Meta Ads&#10;Lead generation&#10;B2B"><?php echo esc_textarea($tags); ?></textarea>
    </p>
    <p>
      <label for="ups_mport_kpis"><strong>KPI rows (label|przed|po|zmiana|opis, jedna linia = jeden KPI)</strong></label>
      <textarea id="ups_mport_kpis" name="ups_mport_kpis" class="widefat" rows="6" placeholder="CPL|312 PLN|150 PLN|-52%|w 4 miesiące"><?php echo esc_textarea($kpis); ?></textarea>
    </p>
    <p>
      <label style="display:flex;align-items:flex-start;gap:8px;">
        <input type="checkbox" name="ups_mport_featured" value="1" <?php checked($is_featured); ?> />
        <span>Wyróżnij ten case na stronie głównej portfolio marketingowego.</span>
      </label>
    </p>
    <hr />
    <p><strong>SEO per case study</strong><br />
      <span style="color:#6b7280;">Uzupełnij pola, aby nadpisać domyślny title/description/canonical dla tego wpisu.</span>
    </p>
    <p>
      <label for="ups_mport_seo_title"><strong>Meta title</strong></label><br />
      <input type="text" id="ups_mport_seo_title" name="ups_mport_seo_title" value="<?php echo esc_attr($seo_title); ?>" class="widefat" maxlength="160" placeholder="np. Case Meta Ads B2B -52% CPL | Upsellio" />
    </p>
    <p>
      <label for="ups_mport_seo_description"><strong>Meta description</strong></label><br />
      <textarea id="ups_mport_seo_description" name="ups_mport_seo_description" class="widefat" rows="3" maxlength="320" placeholder="Krótki opis case study do wyników wyszukiwania."><?php echo esc_textarea($seo_description); ?></textarea>
    </p>
    <p>
      <label for="ups_mport_seo_canonical"><strong>Canonical URL</strong></label><br />
      <input type="url" id="ups_mport_seo_canonical" name="ups_mport_seo_canonical" value="<?php echo esc_attr($seo_canonical); ?>" class="widefat" placeholder="https://twojadomena.pl/portfolio-marketingowe/nazwa-case-study/" />
    </p>
    <hr />
    <p><strong>Niestandardowy blok HTML + CSS + JS</strong><br />
      <span style="color:#6b7280;">Możesz osadzić interaktywną sekcję case study na stronie wpisu.</span>
    </p>
    <p>
      <label for="ups_mport_custom_html"><strong>HTML</strong></label>
      <textarea id="ups_mport_custom_html" name="ups_mport_custom_html" class="widefat" rows="8"><?php echo esc_textarea($custom_html); ?></textarea>
    </p>
    <p>
      <label for="ups_mport_custom_css"><strong>CSS</strong></label>
      <textarea id="ups_mport_custom_css" name="ups_mport_custom_css" class="widefat" rows="8"><?php echo esc_textarea($custom_css); ?></textarea>
    </p>
    <p>
      <label for="ups_mport_custom_js"><strong>JS</strong></label>
      <textarea id="ups_mport_custom_js" name="ups_mport_custom_js" class="widefat" rows="8"><?php echo esc_textarea($custom_js); ?></textarea>
    </p>
    <?php
}

function upsellio_save_marketing_portfolio_details_meta_box($post_id)
{
    if (get_post_type((int) $post_id) !== "marketing_portfolio") {
        return;
    }
    if (!isset($_POST["upsellio_marketing_portfolio_details_nonce"])) {
        return;
    }
    $nonce = sanitize_text_field(wp_unslash($_POST["upsellio_marketing_portfolio_details_nonce"]));
    if (!wp_verify_nonce($nonce, "upsellio_marketing_portfolio_details")) {
        return;
    }
    if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can("edit_post", (int) $post_id)) {
        return;
    }

    $fields = [
        "_ups_mport_type" => isset($_POST["ups_mport_type"]) ? sanitize_text_field(wp_unslash($_POST["ups_mport_type"])) : "",
        "_ups_mport_meta" => isset($_POST["ups_mport_meta"]) ? sanitize_text_field(wp_unslash($_POST["ups_mport_meta"])) : "",
        "_ups_mport_badge" => isset($_POST["ups_mport_badge"]) ? sanitize_text_field(wp_unslash($_POST["ups_mport_badge"])) : "",
        "_ups_mport_cta" => isset($_POST["ups_mport_cta"]) ? sanitize_text_field(wp_unslash($_POST["ups_mport_cta"])) : "",
        "_ups_mport_image" => isset($_POST["ups_mport_image"]) ? esc_url_raw(wp_unslash($_POST["ups_mport_image"])) : "",
        "_ups_mport_theme" => isset($_POST["ups_mport_theme"]) ? sanitize_key(wp_unslash($_POST["ups_mport_theme"])) : "",
        "_ups_mport_date" => isset($_POST["ups_mport_date"]) ? sanitize_text_field(wp_unslash($_POST["ups_mport_date"])) : "",
        "_ups_mport_sector" => isset($_POST["ups_mport_sector"]) ? sanitize_text_field(wp_unslash($_POST["ups_mport_sector"])) : "",
        "_ups_mport_problem" => isset($_POST["ups_mport_problem"]) ? sanitize_textarea_field(wp_unslash($_POST["ups_mport_problem"])) : "",
        "_ups_mport_solution" => isset($_POST["ups_mport_solution"]) ? sanitize_textarea_field(wp_unslash($_POST["ups_mport_solution"])) : "",
        "_ups_mport_result" => isset($_POST["ups_mport_result"]) ? sanitize_textarea_field(wp_unslash($_POST["ups_mport_result"])) : "",
        "_ups_mport_tags" => isset($_POST["ups_mport_tags"]) ? sanitize_textarea_field(wp_unslash($_POST["ups_mport_tags"])) : "",
        "_ups_mport_kpis" => isset($_POST["ups_mport_kpis"]) ? sanitize_textarea_field(wp_unslash($_POST["ups_mport_kpis"])) : "",
        "_ups_mport_seo_title" => isset($_POST["ups_mport_seo_title"]) ? sanitize_text_field(wp_unslash($_POST["ups_mport_seo_title"])) : "",
        "_ups_mport_seo_description" => isset($_POST["ups_mport_seo_description"]) ? sanitize_textarea_field(wp_unslash($_POST["ups_mport_seo_description"])) : "",
        "_ups_mport_seo_canonical" => isset($_POST["ups_mport_seo_canonical"]) ? esc_url_raw(wp_unslash($_POST["ups_mport_seo_canonical"])) : "",
    ];

    foreach ($fields as $meta_key => $meta_value) {
        update_post_meta((int) $post_id, $meta_key, $meta_value);
    }
    update_post_meta((int) $post_id, "_ups_mport_featured", isset($_POST["ups_mport_featured"]) ? "1" : "0");

    $custom_html = isset($_POST["ups_mport_custom_html"]) ? wp_unslash($_POST["ups_mport_custom_html"]) : "";
    $custom_css = isset($_POST["ups_mport_custom_css"]) ? wp_unslash($_POST["ups_mport_custom_css"]) : "";
    $custom_js = isset($_POST["ups_mport_custom_js"]) ? wp_unslash($_POST["ups_mport_custom_js"]) : "";
    $payload = upsellio_prepare_custom_embed_payload((string) $custom_html, (string) $custom_css, (string) $custom_js);
    update_post_meta((int) $post_id, "_ups_mport_custom_html", (string) $payload["html"]);
    update_post_meta((int) $post_id, "_ups_mport_custom_css", (string) $payload["css"]);
    update_post_meta((int) $post_id, "_ups_mport_custom_js", (string) $payload["js"]);
}
add_action("save_post", "upsellio_save_marketing_portfolio_details_meta_box");

function upsellio_parse_textarea_lines($value, $limit = 12)
{
    $raw_lines = preg_split("/\r\n|\r|\n/", (string) $value);
    $lines = [];
    foreach ((array) $raw_lines as $line) {
        $line = trim((string) $line);
        if ($line === "") {
            continue;
        }
        $lines[] = $line;
    }

    return array_slice($lines, 0, max(1, (int) $limit));
}

function upsellio_parse_marketing_kpi_lines($value)
{
    $rows = [];
    $lines = upsellio_parse_textarea_lines($value, 8);
    foreach ($lines as $line) {
        $parts = array_map("trim", explode("|", (string) $line));
        $rows[] = [
            "label" => (string) ($parts[0] ?? ""),
            "before" => (string) ($parts[1] ?? ""),
            "after" => (string) ($parts[2] ?? ""),
            "change" => (string) ($parts[3] ?? ""),
            "desc" => (string) ($parts[4] ?? ""),
        ];
    }

    return $rows;
}

function upsellio_get_marketing_portfolio_category_mapping($source_name = "", $source_slug = "")
{
    $normalized_slug = sanitize_title((string) $source_slug);
    $normalized_name = sanitize_title((string) $source_name);
    $lookup = strtolower(trim($normalized_slug !== "" ? $normalized_slug : $normalized_name));

    $map = [
        "meta" => ["label" => "Meta", "theme" => "vis-meta", "aliases" => ["meta", "meta-ads", "facebook", "facebook-ads", "social"]],
        "google" => ["label" => "Google", "theme" => "vis-google", "aliases" => ["google", "google-ads", "ads", "search", "performance-max", "pmax"]],
        "strona" => ["label" => "Strona", "theme" => "vis-landing", "aliases" => ["strona", "strony", "strony-www", "strona-www", "landing", "landing-page", "www"]],
        "ecom" => ["label" => "Ecom", "theme" => "vis-ecom", "aliases" => ["ecom", "ecommerce", "e-commerce", "sklep", "sklep-online", "woocommerce"]],
    ];

    foreach ($map as $target_slug => $entry) {
        if ($lookup === $target_slug || in_array($lookup, (array) $entry["aliases"], true)) {
            return [
                "slug" => $target_slug,
                "label" => (string) $entry["label"],
                "theme" => (string) $entry["theme"],
            ];
        }
    }

    return [
        "slug" => "meta",
        "label" => "Meta",
        "theme" => "vis-meta",
    ];
}

function upsellio_sync_marketing_portfolio_primary_category($post_id, $post, $update)
{
    if (!(int) $post_id || !($post instanceof WP_Post) || $post->post_type !== "marketing_portfolio") {
        return;
    }
    if (wp_is_post_revision((int) $post_id) || (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)) {
        return;
    }

    $terms = get_the_terms((int) $post_id, "marketing_portfolio_category");
    $first_term = (!empty($terms) && !is_wp_error($terms)) ? $terms[0] : null;
    $mapped = upsellio_get_marketing_portfolio_category_mapping($first_term ? (string) $first_term->name : "", $first_term ? (string) $first_term->slug : "");

    $target_term = term_exists((string) $mapped["slug"], "marketing_portfolio_category");
    if (!$target_term) {
        $target_term = wp_insert_term((string) $mapped["label"], "marketing_portfolio_category", ["slug" => (string) $mapped["slug"]]);
    }
    if (is_wp_error($target_term)) {
        return;
    }

    $target_term_id = (int) (is_array($target_term) ? ($target_term["term_id"] ?? 0) : 0);
    if ($target_term_id > 0) {
        wp_set_object_terms((int) $post_id, [$target_term_id], "marketing_portfolio_category");
    }
}
add_action("save_post_marketing_portfolio", "upsellio_sync_marketing_portfolio_primary_category", 20, 3);

function upsellio_get_marketing_portfolio_list($limit = 120)
{
    $query = new WP_Query([
        "post_type" => "marketing_portfolio",
        "post_status" => "publish",
        "posts_per_page" => max(1, (int) $limit),
        "orderby" => "menu_order date",
        "order" => "ASC",
    ]);

    $items = [];
    foreach ((array) $query->posts as $post_item) {
        $post_id = (int) $post_item->ID;
        $terms = get_the_terms($post_id, "marketing_portfolio_category");
        $first_term = (!empty($terms) && !is_wp_error($terms)) ? $terms[0] : null;
        $mapped_category = upsellio_get_marketing_portfolio_category_mapping($first_term ? (string) $first_term->name : "", $first_term ? (string) $first_term->slug : "");
        $theme = (string) get_post_meta($post_id, "_ups_mport_theme", true);
        if ($theme === "") {
            $theme = (string) $mapped_category["theme"];
        }
        $items[] = [
            "id" => $post_id,
            "title" => (string) get_the_title($post_id),
            "url" => (string) get_permalink($post_id),
            "excerpt" => (string) get_the_excerpt($post_id),
            "type" => (string) get_post_meta($post_id, "_ups_mport_type", true),
            "meta" => (string) get_post_meta($post_id, "_ups_mport_meta", true),
            "badge" => (string) get_post_meta($post_id, "_ups_mport_badge", true),
            "cta" => (string) get_post_meta($post_id, "_ups_mport_cta", true),
            "image" => (string) get_post_meta($post_id, "_ups_mport_image", true),
            "theme" => $theme,
            "date" => (string) get_post_meta($post_id, "_ups_mport_date", true),
            "sector" => (string) get_post_meta($post_id, "_ups_mport_sector", true),
            "problem" => (string) get_post_meta($post_id, "_ups_mport_problem", true),
            "solution" => (string) get_post_meta($post_id, "_ups_mport_solution", true),
            "result" => (string) get_post_meta($post_id, "_ups_mport_result", true),
            "tags" => upsellio_parse_textarea_lines((string) get_post_meta($post_id, "_ups_mport_tags", true), 8),
            "kpis" => upsellio_parse_marketing_kpi_lines((string) get_post_meta($post_id, "_ups_mport_kpis", true)),
            "category" => (string) $mapped_category["label"],
            "category_slug" => (string) $mapped_category["slug"],
            "is_featured" => (string) get_post_meta($post_id, "_ups_mport_featured", true) === "1",
            "custom_html" => (string) get_post_meta($post_id, "_ups_mport_custom_html", true),
            "custom_css" => (string) get_post_meta($post_id, "_ups_mport_custom_css", true),
            "custom_js" => (string) get_post_meta($post_id, "_ups_mport_custom_js", true),
            "seo_title" => (string) get_post_meta($post_id, "_ups_mport_seo_title", true),
            "seo_description" => (string) get_post_meta($post_id, "_ups_mport_seo_description", true),
            "seo_canonical" => (string) get_post_meta($post_id, "_ups_mport_seo_canonical", true),
        ];
    }
    wp_reset_postdata();

    return $items;
}

function upsellio_ensure_marketing_portfolio_page_exists()
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }

    $marketing_portfolio_path = upsellio_get_special_navigation_path_by_title("Portfolio marketingowe", "/portfolio-marketingowe/");
    $marketing_portfolio_slug = trim((string) wp_parse_url($marketing_portfolio_path, PHP_URL_PATH), "/");
    if ($marketing_portfolio_slug === "") {
        $marketing_portfolio_slug = "portfolio-marketingowe";
    }
    upsellio_upsert_page_with_template($marketing_portfolio_slug, "Portfolio marketingowe", "page-portfolio-marketingowe.php");
}
add_action("admin_init", "upsellio_ensure_marketing_portfolio_page_exists");

function upsellio_get_marketing_portfolio_seo_payload($post_id)
{
    $post_id = (int) $post_id;
    if ($post_id <= 0 || get_post_type($post_id) !== "marketing_portfolio") {
        return [];
    }

    $title = trim((string) get_post_meta($post_id, "_ups_mport_seo_title", true));
    $description = trim((string) get_post_meta($post_id, "_ups_mport_seo_description", true));
    $canonical = trim((string) get_post_meta($post_id, "_ups_mport_seo_canonical", true));
    $fallback_description = (string) get_the_excerpt($post_id);
    if ($description === "") {
        $description = wp_strip_all_tags($fallback_description);
    }

    return [
        "title" => $title,
        "description" => wp_trim_words($description, 34, ""),
        "canonical" => $canonical !== "" ? esc_url_raw($canonical) : (string) get_permalink($post_id),
    ];
}

function upsellio_marketing_portfolio_document_title($title)
{
    if (!is_singular("marketing_portfolio")) {
        return $title;
    }

    $post_id = (int) get_queried_object_id();
    $seo_payload = upsellio_get_marketing_portfolio_seo_payload($post_id);
    $custom_title = trim((string) ($seo_payload["title"] ?? ""));

    return $custom_title !== "" ? $custom_title : $title;
}
add_filter("pre_get_document_title", "upsellio_marketing_portfolio_document_title");

function upsellio_print_marketing_portfolio_seo_meta()
{
    if (!is_singular("marketing_portfolio")) {
        return;
    }

    $post_id = (int) get_queried_object_id();
    $seo_payload = upsellio_get_marketing_portfolio_seo_payload($post_id);
    $description = trim((string) ($seo_payload["description"] ?? ""));
    $canonical = trim((string) ($seo_payload["canonical"] ?? ""));
    $title = trim((string) ($seo_payload["title"] ?? get_the_title($post_id)));

    if ($description !== "") {
        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
    }
    if ($title !== "") {
        echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
    }
    if ($canonical !== "") {
        echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url($canonical) . '">' . "\n";
    }
}
add_action("wp_head", "upsellio_print_marketing_portfolio_seo_meta", 3);

function upsellio_get_supported_error_codes()
{
    return [400, 401, 403, 404, 429, 500, 503];
}

function upsellio_get_marketing_portfolio_redirect_aliases()
{
    return [
        "meta-ads" => "meta",
        "facebook" => "meta",
        "facebook-ads" => "meta",
        "social" => "meta",
        "google-ads" => "google",
        "ads" => "google",
        "search" => "google",
        "performance-max" => "google",
        "pmax" => "google",
        "strony" => "strona",
        "strony-www" => "strona",
        "strona-www" => "strona",
        "landing" => "strona",
        "landing-page" => "strona",
        "www" => "strona",
        "ecommerce" => "ecom",
        "e-commerce" => "ecom",
        "sklep" => "ecom",
        "sklep-online" => "ecom",
        "woocommerce" => "ecom",
    ];
}

function upsellio_get_marketing_portfolio_category_redirect_target($requested_slug)
{
    $requested_slug = sanitize_title((string) $requested_slug);
    if ($requested_slug === "") {
        return "";
    }

    $aliases = upsellio_get_marketing_portfolio_redirect_aliases();

    return isset($aliases[$requested_slug]) ? (string) $aliases[$requested_slug] : "";
}

function upsellio_maybe_redirect_legacy_marketing_portfolio_category_slug()
{
    if (is_admin() || wp_doing_ajax() || (defined("REST_REQUEST") && REST_REQUEST)) {
        return;
    }

    $taxonomy_base = "kategoria-portfolio-marketingowego";
    $request_uri = isset($_SERVER["REQUEST_URI"]) ? (string) wp_unslash($_SERVER["REQUEST_URI"]) : "";
    $request_path = (string) parse_url($request_uri, PHP_URL_PATH);
    $request_path = trim($request_path, "/");
    if ($request_path === "") {
        return;
    }

    $match = [];
    if (!preg_match("#^" . preg_quote($taxonomy_base, "#") . "/([^/]+)/?$#", $request_path, $match)) {
        return;
    }

    $requested_slug = isset($match[1]) ? sanitize_title((string) $match[1]) : "";
    $target_slug = upsellio_get_marketing_portfolio_category_redirect_target($requested_slug);
    if ($target_slug === "") {
        return;
    }

    $target_url = home_url("/" . $taxonomy_base . "/" . $target_slug . "/");
    $query_string = (string) parse_url($request_uri, PHP_URL_QUERY);
    if ($query_string !== "") {
        $target_url = $target_url . "?" . $query_string;
    }

    wp_safe_redirect($target_url, 301);
    exit;
}
add_action("template_redirect", "upsellio_maybe_redirect_legacy_marketing_portfolio_category_slug", 1);
