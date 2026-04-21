<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_seo_calculate_score($post_id)
{
    $post_id = (int) $post_id;
    $content = (string) get_post_field("post_content", $post_id);
    $seoTitle = (string) get_post_meta($post_id, "_yoast_wpseo_title", true);
    $seoDescription = (string) get_post_meta($post_id, "_yoast_wpseo_metadesc", true);
    $focusKeyword = (string) get_post_meta($post_id, "_yoast_wpseo_focuskw", true);

    $score = 0;
    $notes = [];

    if (upsellio_strlen(get_the_title($post_id)) >= 45 && upsellio_strlen(get_the_title($post_id)) <= 65) {
        $score += 15;
    } else {
        $notes[] = "Tytuł wpisu poza zakresem 45-65 znaków.";
    }

    if (upsellio_strlen($seoTitle) >= 45 && upsellio_strlen($seoTitle) <= 65) {
        $score += 15;
    } else {
        $notes[] = "SEO title wymaga dopracowania (45-65 znaków).";
    }

    if (upsellio_strlen($seoDescription) >= 140 && upsellio_strlen($seoDescription) <= 160) {
        $score += 15;
    } else {
        $notes[] = "Meta description powinien mieć 140-160 znaków.";
    }

    if ($focusKeyword !== "") {
        $score += 10;
    } else {
        $notes[] = "Brakuje focus keyword.";
    }

    $h2Count = preg_match_all("/<h2[\s>]/i", $content);
    $h3Count = preg_match_all("/<h3[\s>]/i", $content);
    if ($h2Count >= 2) {
        $score += 10;
    } else {
        $notes[] = "Dodaj minimum 2 nagłówki H2.";
    }
    if ($h3Count >= 1) {
        $score += 5;
    } else {
        $notes[] = "Dodaj co najmniej 1 nagłówek H3.";
    }

    if (strpos($content, "[upsellio_internal_links") !== false) {
        $score += 10;
    } else {
        $notes[] = "Dodaj internal linking shortcode.";
    }

    if (strpos($content, "FAQPage") !== false || strpos($content, "<h2>FAQ") !== false || strpos($content, "<h2>Najczęstsze") !== false) {
        $score += 10;
    } else {
        $notes[] = "Uzupełnij sekcję FAQ lub schema FAQ.";
    }

    if (str_word_count(wp_strip_all_tags($content)) >= 600) {
        $score += 10;
    } else {
        $notes[] = "Treść jest krótka - celuj w 600+ słów.";
    }

    return [
        "score" => min(100, $score),
        "notes" => $notes,
    ];
}

function upsellio_seo_update_post_score($post_id, $post, $is_update)
{
    if ($post->post_type !== "post") {
        return;
    }
    if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
        return;
    }

    $result = upsellio_seo_calculate_score($post_id);
    update_post_meta($post_id, "_upsellio_seo_score", (int) $result["score"]);
    update_post_meta($post_id, "_upsellio_seo_notes", $result["notes"]);
}
add_action("save_post", "upsellio_seo_update_post_score", 10, 3);

function upsellio_seo_add_score_column($columns)
{
    $columns["upsellio_seo_score"] = "SEO QA";
    return $columns;
}
add_filter("manage_post_posts_columns", "upsellio_seo_add_score_column");

function upsellio_seo_render_score_column($column, $post_id)
{
    if ($column !== "upsellio_seo_score") {
        return;
    }
    $score = (int) get_post_meta($post_id, "_upsellio_seo_score", true);
    $color = $score >= 75 ? "#1d9e75" : ($score >= 50 ? "#e18d2d" : "#d14c4c");
    echo '<strong style="color:' . esc_attr($color) . ';">' . esc_html((string) $score) . '/100</strong>';
}
add_action("manage_post_posts_custom_column", "upsellio_seo_render_score_column", 10, 2);

function upsellio_seo_add_meta_box()
{
    add_meta_box("upsellio_seo_quality_box", "SEO QA przed publikacją", "upsellio_seo_render_meta_box", "post", "side", "high");
}
add_action("add_meta_boxes", "upsellio_seo_add_meta_box");

function upsellio_seo_render_meta_box($post)
{
    $score = (int) get_post_meta($post->ID, "_upsellio_seo_score", true);
    $notes = get_post_meta($post->ID, "_upsellio_seo_notes", true);
    if (!is_array($notes)) {
        $notes = [];
    }
    ?>
    <p><strong>Wynik SEO:</strong> <?php echo esc_html((string) $score); ?>/100</p>
    <?php if (empty($notes)) : ?>
      <p style="color:#1d9e75;"><strong>OK:</strong> wpis jest gotowy pod publikację.</p>
    <?php else : ?>
      <ul style="margin-left:16px;">
        <?php foreach ($notes as $note) : ?>
          <li><?php echo esc_html((string) $note); ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <?php
}

function upsellio_seo_register_refresh_menu()
{
    add_submenu_page(
        "edit.php",
        "Content Refresh",
        "Content Refresh",
        "edit_posts",
        "upsellio-content-refresh",
        "upsellio_seo_render_refresh_page"
    );
}
add_action("admin_menu", "upsellio_seo_register_refresh_menu");

function upsellio_seo_mark_stale_posts_for_refresh()
{
    $staleQuery = new WP_Query([
        "post_type" => "post",
        "post_status" => "publish",
        "posts_per_page" => 100,
        "date_query" => [[
            "before" => gmdate("Y-m-d", strtotime("-180 days")),
            "inclusive" => true,
        ]],
        "fields" => "ids",
    ]);

    foreach ($staleQuery->posts as $postId) {
        update_post_meta((int) $postId, "_upsellio_refresh_due", "1");
    }
}

function upsellio_seo_schedule_refresh_cron()
{
    if (!wp_next_scheduled("upsellio_seo_daily_refresh_scan")) {
        wp_schedule_event(time() + HOUR_IN_SECONDS, "daily", "upsellio_seo_daily_refresh_scan");
    }
}
add_action("init", "upsellio_seo_schedule_refresh_cron");
add_action("upsellio_seo_daily_refresh_scan", "upsellio_seo_mark_stale_posts_for_refresh");

function upsellio_seo_render_refresh_page()
{
    if (!current_user_can("edit_posts")) {
        return;
    }

    if (isset($_GET["mark_refreshed"])) {
        $postId = (int) $_GET["mark_refreshed"];
        if ($postId > 0 && current_user_can("edit_post", $postId)) {
            delete_post_meta($postId, "_upsellio_refresh_due");
            wp_safe_redirect(menu_page_url("upsellio-content-refresh", false));
            exit;
        }
    }

    $query = new WP_Query([
        "post_type" => "post",
        "post_status" => "publish",
        "posts_per_page" => 100,
        "meta_key" => "_upsellio_refresh_due",
        "meta_value" => "1",
    ]);
    ?>
    <div class="wrap">
      <h1>Content Refresh Queue</h1>
      <p>Wpisy starsze niż 180 dni trafiają automatycznie do tej listy.</p>
      <?php if (!$query->have_posts()) : ?>
        <p><em>Brak wpisów do odświeżenia.</em></p>
      <?php else : ?>
        <table class="widefat striped">
          <thead>
            <tr>
              <th>Tytuł</th>
              <th>Ostatnia modyfikacja</th>
              <th>SEO score</th>
              <th>Akcja</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($query->have_posts()) : $query->the_post(); ?>
              <tr>
                <td><a href="<?php echo esc_url(get_edit_post_link(get_the_ID())); ?>"><?php echo esc_html(get_the_title()); ?></a></td>
                <td><?php echo esc_html(get_the_modified_date("Y-m-d", get_the_ID())); ?></td>
                <td><?php echo esc_html((string) ((int) get_post_meta(get_the_ID(), "_upsellio_seo_score", true))); ?>/100</td>
                <td>
                  <a class="button" href="<?php echo esc_url(add_query_arg("mark_refreshed", (string) get_the_ID(), menu_page_url("upsellio-content-refresh", false))); ?>">Oznacz jako odświeżony</a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
        <?php wp_reset_postdata(); ?>
      <?php endif; ?>
    </div>
    <?php
}

function upsellio_seo_register_brief_menu()
{
    add_submenu_page(
        "edit.php",
        "Generator Briefu SEO",
        "Generator Briefu SEO",
        "edit_posts",
        "upsellio-seo-brief",
        "upsellio_seo_render_brief_page"
    );
}
add_action("admin_menu", "upsellio_seo_register_brief_menu");

function upsellio_seo_render_brief_page()
{
    if (!current_user_can("edit_posts")) {
        return;
    }
    $cluster = isset($_POST["keyword_cluster"]) ? sanitize_text_field(wp_unslash($_POST["keyword_cluster"])) : "";
    $audience = isset($_POST["audience"]) ? sanitize_text_field(wp_unslash($_POST["audience"])) : "";
    $service = isset($_POST["service"]) ? sanitize_text_field(wp_unslash($_POST["service"])) : "";
    $keywords = array_values(array_filter(array_map("trim", explode(",", $cluster))));
    $primary = !empty($keywords) ? $keywords[0] : "";
    ?>
    <div class="wrap">
      <h1>Generator briefu SEO</h1>
      <form method="post" style="max-width:880px;background:#fff;border:1px solid #dcdcda;padding:16px;border-radius:12px;">
        <p>
          <label><strong>Klaster słów kluczowych (po przecinku)</strong><br />
            <input type="text" name="keyword_cluster" class="widefat" value="<?php echo esc_attr($cluster); ?>" />
          </label>
        </p>
        <p>
          <label><strong>Grupa docelowa</strong><br />
            <input type="text" name="audience" class="widefat" value="<?php echo esc_attr($audience); ?>" />
          </label>
        </p>
        <p>
          <label><strong>Usługa / oferta</strong><br />
            <input type="text" name="service" class="widefat" value="<?php echo esc_attr($service); ?>" />
          </label>
        </p>
        <p><button class="button button-primary" type="submit">Wygeneruj brief</button></p>
      </form>

      <?php if (!empty($keywords)) : ?>
        <div style="margin-top:18px;max-width:880px;background:#fff;border:1px solid #dcdcda;padding:16px;border-radius:12px;">
          <h2>Brief wpisu</h2>
          <p><strong>Primary keyword:</strong> <?php echo esc_html($primary); ?></p>
          <p><strong>Secondary keywords:</strong> <?php echo esc_html(implode(", ", array_slice($keywords, 1))); ?></p>
          <p><strong>Propozycja tytułu:</strong> <?php echo esc_html("Jak " . $primary . " poprawić wyniki w " . ($service !== "" ? $service : "Twojej firmie")); ?></p>
          <p><strong>Propozycja meta description:</strong> <?php echo esc_html("Praktyczny przewodnik dla " . ($audience !== "" ? $audience : "firm") . ", jak podejść do tematu: " . $primary . " i wdrożyć działania, które generują realny wynik."); ?></p>
          <h3>Szkic H2/H3</h3>
          <ol>
            <li>Dlaczego <?php echo esc_html($primary); ?> wpływa na wynik biznesowy</li>
            <li>Najczęstsze błędy i ich koszt</li>
            <li>Proces wdrożenia krok po kroku</li>
            <li>Checklist przed startem</li>
            <li>FAQ + następny krok</li>
          </ol>
        </div>
      <?php endif; ?>
    </div>
    <?php
}
