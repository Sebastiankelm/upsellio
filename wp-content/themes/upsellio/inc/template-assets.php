<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_template_assets_option_key()
{
    return "upsellio_template_assets_v1";
}

function upsellio_template_assets_slots()
{
    return [
        "founder_main" => [
            "group" => "Autor i zaufanie",
            "label" => "Zdjęcie założyciela (główne)",
            "description" => "Pojawia się przy formularzach kontaktu, oferty i w sekcjach zaufania. Najlepiej kwadratowe 600x600.",
            "fallback" => "SK",
            "default_alt" => "Sebastian Kelm - Upsellio",
            "default_caption" => "Sebastian Kelm - odpiszę osobiście w 24h.",
        ],
        "founder_compact" => [
            "group" => "Autor i zaufanie",
            "label" => "Zdjęcie założyciela (kompakt)",
            "description" => "Mniejsza wersja używana w kartach blogowych, paskach autora i sekcjach \"od autora\".",
            "fallback" => "SK",
            "default_alt" => "Sebastian Kelm - Upsellio",
            "default_caption" => "Sebastian Kelm - autor wpisu",
        ],
        "service_google_screenshot" => [
            "group" => "Screenshoty usług",
            "label" => "Panel Google Ads",
            "description" => "Anonimizowany screenshot panelu kampanii Google Ads. Wyświetla się w sekcji zakresu na stronie Google Ads.",
            "fallback" => "GAds",
            "default_alt" => "Panel kampanii Google Ads - Upsellio",
            "default_caption" => "Anonimizowany dashboard kampanii Google Ads",
        ],
        "service_meta_screenshot" => [
            "group" => "Screenshoty usług",
            "label" => "Panel Meta Ads",
            "description" => "Anonimizowany screenshot menedżera reklam Meta Ads. Pasuje do sekcji procesu lub zakresu kampanii.",
            "fallback" => "Meta",
            "default_alt" => "Menedżer reklam Meta Ads - Upsellio",
            "default_caption" => "Menedżer reklam Meta Ads po uporządkowaniu",
        ],
        "service_meta_creative" => [
            "group" => "Screenshoty usług",
            "label" => "Kreacja Meta Ads",
            "description" => "Przykładowa kreacja reklamy Meta - feed, stories lub carousel. Wzmacnia sekcję formatów.",
            "fallback" => "Ad",
            "default_alt" => "Kreacja reklamy Meta - Upsellio",
            "default_caption" => "Kreacja reklamy Meta - format feed",
        ],
        "service_web_dashboard" => [
            "group" => "Screenshoty usług",
            "label" => "Dashboard / mockup strony",
            "description" => "Ujęcie zaprojektowanej strony albo dashboardu CRO. Wzmacnia stronę \"Tworzenie stron\" i ofertę WWW.",
            "fallback" => "WWW",
            "default_alt" => "Mockup strony zaprojektowanej pod konwersję",
            "default_caption" => "Strona, która domyka ruch z kampanii",
        ],
        "portfolio_default" => [
            "group" => "Domyślne miniatury",
            "label" => "Portfolio - fallback",
            "description" => "Pokazywany, gdy konkretny wpis portfolio stron nie ma własnego obrazu lub featured image.",
            "fallback" => "WWW",
            "default_alt" => "Realizacja Upsellio - portfolio stron",
            "default_caption" => "Realizacja Upsellio",
        ],
        "portfolio_marketing_default" => [
            "group" => "Domyślne miniatury",
            "label" => "Portfolio marketingowe - fallback",
            "description" => "Pokazywany, gdy case study marketingowe nie ma podpiętego screenshotu.",
            "fallback" => "KPI",
            "default_alt" => "Case study marketingowe Upsellio",
            "default_caption" => "Case study marketingowe Upsellio",
        ],
        "lead_magnet_default" => [
            "group" => "Domyślne miniatury",
            "label" => "Lead magnet - fallback",
            "description" => "Domyślna grafika materiału, jeśli wpis nie ma swojego mockupu PDF.",
            "fallback" => "PDF",
            "default_alt" => "Materiał Upsellio - lead magnet",
            "default_caption" => "Materiał Upsellio do pobrania",
        ],
        "og_default" => [
            "group" => "Open Graph",
            "label" => "OG image - domyślny",
            "description" => "Fallback Open Graph używany, gdy strona/wpis nie ma własnego OG image. Zalecane 1200x630.",
            "fallback" => "OG",
            "default_alt" => "Upsellio - marketing i strony pod konwersję",
            "default_caption" => "Upsellio",
        ],
        "og_blog" => [
            "group" => "Open Graph",
            "label" => "OG image - blog",
            "description" => "Fallback OG dla wpisów blogowych bez własnego obrazu lub featured image.",
            "fallback" => "Blog",
            "default_alt" => "Blog Upsellio",
            "default_caption" => "Blog Upsellio",
        ],
        "og_services" => [
            "group" => "Open Graph",
            "label" => "OG image - usługi",
            "description" => "Fallback OG dla landing pages usługowych: Google Ads, Meta Ads, strony, oferta.",
            "fallback" => "Ofr",
            "default_alt" => "Usługi marketingu i stron Upsellio",
            "default_caption" => "Usługi Upsellio",
        ],
        "og_portfolio" => [
            "group" => "Open Graph",
            "label" => "OG image - portfolio",
            "description" => "Fallback OG dla wpisów portfolio i listy realizacji.",
            "fallback" => "Port",
            "default_alt" => "Portfolio realizacji Upsellio",
            "default_caption" => "Portfolio Upsellio",
        ],
    ];
}

function upsellio_sanitize_template_assets_config($config)
{
    $slots = upsellio_template_assets_slots();
    $safe = [];

    foreach ($slots as $slot_key => $slot) {
        $raw = isset($config[$slot_key]) && is_array($config[$slot_key]) ? $config[$slot_key] : [];
        $attachment_id = isset($raw["attachment_id"]) ? absint($raw["attachment_id"]) : 0;
        if ($attachment_id > 0 && !wp_attachment_is_image($attachment_id)) {
            $attachment_id = 0;
        }
        $url = isset($raw["url"]) ? esc_url_raw((string) $raw["url"]) : "";

        $safe[$slot_key] = [
            "attachment_id" => $attachment_id,
            "url" => $url,
            "alt" => sanitize_text_field((string) ($raw["alt"] ?? "")),
            "caption" => sanitize_text_field((string) ($raw["caption"] ?? "")),
        ];
    }

    return $safe;
}

function upsellio_get_template_assets_config()
{
    $stored = get_option(upsellio_template_assets_option_key(), []);
    if (!is_array($stored)) {
        $stored = [];
    }

    return upsellio_sanitize_template_assets_config($stored);
}

function upsellio_get_template_asset($slot_key)
{
    $slot_key = (string) $slot_key;
    $slots = upsellio_template_assets_slots();
    if (!isset($slots[$slot_key])) {
        return [];
    }

    $config = upsellio_get_template_assets_config();
    $saved = isset($config[$slot_key]) && is_array($config[$slot_key]) ? $config[$slot_key] : [];

    return array_merge($slots[$slot_key], [
        "key" => $slot_key,
        "attachment_id" => (int) ($saved["attachment_id"] ?? 0),
        "url" => trim((string) ($saved["url"] ?? "")),
        "alt" => trim((string) ($saved["alt"] ?? "")),
        "caption" => trim((string) ($saved["caption"] ?? "")),
    ]);
}

function upsellio_get_template_asset_alt($slot)
{
    $slot = is_array($slot) ? $slot : [];
    $alt = trim((string) ($slot["alt"] ?? ""));
    if ($alt !== "") {
        return $alt;
    }

    $attachment_id = (int) ($slot["attachment_id"] ?? 0);
    if ($attachment_id > 0) {
        $attachment_alt = trim((string) get_post_meta($attachment_id, "_wp_attachment_image_alt", true));
        if ($attachment_alt !== "") {
            return $attachment_alt;
        }
    }

    return (string) ($slot["default_alt"] ?? "");
}

function upsellio_get_template_asset_url($slot_key, $size = "large")
{
    $slot = upsellio_get_template_asset($slot_key);
    if ($slot === []) {
        return "";
    }
    $attachment_id = (int) ($slot["attachment_id"] ?? 0);
    if ($attachment_id > 0) {
        $url = wp_get_attachment_image_url($attachment_id, (string) $size);
        if (is_string($url) && $url !== "") {
            return $url;
        }
    }

    return (string) ($slot["url"] ?? "");
}

function upsellio_render_template_asset_image($slot_key, $args = [])
{
    $slot = upsellio_get_template_asset($slot_key);
    if ($slot === []) {
        return "";
    }

    $class_name = trim((string) ($args["class"] ?? "tpl-asset-image"));
    $size = (string) ($args["size"] ?? "large");
    $loading = (string) ($args["loading"] ?? "lazy");
    $attachment_id = (int) ($slot["attachment_id"] ?? 0);

    if ($attachment_id > 0) {
        $image = wp_get_attachment_image($attachment_id, $size, false, [
            "class" => $class_name,
            "alt" => upsellio_get_template_asset_alt($slot),
            "loading" => $loading,
            "decoding" => "async",
        ]);
        if (is_string($image) && $image !== "") {
            return $image;
        }
    }

    $url = trim((string) ($slot["url"] ?? ""));
    if ($url !== "") {
        $alt = upsellio_get_template_asset_alt($slot);
        return '<img class="' . esc_attr($class_name) . '" src="' . esc_url($url) . '" alt="' . esc_attr($alt) . '" loading="' . esc_attr($loading) . '" decoding="async" />';
    }

    if (!empty($args["no_fallback"])) {
        return "";
    }

    $fallback_class = trim($class_name . " tpl-asset-fallback");
    $fallback = trim((string) ($slot["fallback"] ?? "UP"));
    return '<div class="' . esc_attr($fallback_class) . '" aria-hidden="true"><span>' . esc_html($fallback) . '</span></div>';
}

function upsellio_template_asset_caption($slot_key)
{
    $slot = upsellio_get_template_asset($slot_key);
    if ($slot === []) {
        return "";
    }

    $caption = trim((string) ($slot["caption"] ?? ""));
    if ($caption !== "") {
        return $caption;
    }

    return trim((string) ($slot["default_caption"] ?? ""));
}

function upsellio_resolve_post_image_url($post_id, $url_meta_key, $size = "large")
{
    $post_id = (int) $post_id;
    if ($post_id <= 0) {
        return "";
    }

    $thumbnail_id = (int) get_post_thumbnail_id($post_id);
    if ($thumbnail_id > 0) {
        $url = wp_get_attachment_image_url($thumbnail_id, (string) $size);
        if (is_string($url) && $url !== "") {
            return $url;
        }
    }

    if ($url_meta_key !== "") {
        $url = trim((string) get_post_meta($post_id, (string) $url_meta_key, true));
        if ($url !== "") {
            return $url;
        }
    }

    return "";
}

function upsellio_template_assets_admin_url()
{
    return admin_url("themes.php?page=upsellio-template-assets");
}

function upsellio_template_assets_admin_menu()
{
    add_submenu_page(
        "themes.php",
        "Assety stron Upsellio",
        "Assety stron",
        "manage_options",
        "upsellio-template-assets",
        "upsellio_render_template_assets_admin_screen",
        83
    );
}
add_action("admin_menu", "upsellio_template_assets_admin_menu");

function upsellio_template_assets_admin_enqueue($hook)
{
    if ((string) $hook !== "appearance_page_upsellio-template-assets") {
        return;
    }
    wp_enqueue_media();
}
add_action("admin_enqueue_scripts", "upsellio_template_assets_admin_enqueue");

function upsellio_template_assets_redirect($status)
{
    wp_safe_redirect(add_query_arg("upsellio_template_assets_status", sanitize_key((string) $status), upsellio_template_assets_admin_url()));
    exit;
}

function upsellio_handle_save_template_assets()
{
    if (!current_user_can("manage_options")) {
        upsellio_template_assets_redirect("no_permission");
    }
    if (!isset($_POST["upsellio_template_assets_nonce"]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST["upsellio_template_assets_nonce"])), "upsellio_save_template_assets")) {
        upsellio_template_assets_redirect("bad_nonce");
    }

    $raw = isset($_POST["upsellio_template_assets"]) && is_array($_POST["upsellio_template_assets"])
        ? wp_unslash($_POST["upsellio_template_assets"])
        : [];

    update_option(upsellio_template_assets_option_key(), upsellio_sanitize_template_assets_config($raw), false);
    upsellio_template_assets_redirect("saved");
}
add_action("admin_post_upsellio_save_template_assets", "upsellio_handle_save_template_assets");

function upsellio_template_assets_status_message($status)
{
    $messages = [
        "saved" => "Assety zostały zapisane.",
        "bad_nonce" => "Sesja wygasła. Odśwież stronę i spróbuj ponownie.",
        "no_permission" => "Brak uprawnień do edycji assetów.",
    ];

    return (string) ($messages[$status] ?? "Nie udało się zapisać zmian.");
}

function upsellio_render_template_assets_admin_screen()
{
    if (!current_user_can("manage_options")) {
        return;
    }

    $slots = upsellio_template_assets_slots();
    $config = upsellio_get_template_assets_config();
    $status = isset($_GET["upsellio_template_assets_status"]) ? sanitize_key(wp_unslash($_GET["upsellio_template_assets_status"])) : "";
    $current_group = "";
    ?>
    <div class="wrap">
      <h1>Assety stron</h1>
      <p style="max-width:860px;">Centralne miejsce na zdjęcie założyciela, screenshoty paneli reklamowych, mockupy stron, fallbacki Open Graph oraz domyślne miniatury portfolio i lead magnetów. Każdy slot przyjmuje zarówno obraz z biblioteki mediów (preferowane), jak i URL jako migracyjny fallback. Bez obrazu front pokaże estetyczny placeholder z inicjałami.</p>
      <style>
        .ups-tpl-assets-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(290px,1fr)); gap:16px; max-width:1280px; }
        .ups-tpl-assets-group { margin:26px 0 12px; }
        .ups-tpl-assets-card { background:#fff; border:1px solid #dcdcde; border-radius:14px; padding:16px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
        .ups-tpl-assets-frame { min-height:170px; display:grid; place-items:center; border:1px dashed #cbd5e1; border-radius:12px; background:linear-gradient(135deg,#f8fafc,#fff); overflow:hidden; color:#64748b; text-align:center; }
        .ups-tpl-assets-frame img { width:100%; height:190px; object-fit:cover; display:block; }
        .ups-tpl-assets-empty { display:grid; gap:6px; place-items:center; padding:18px; }
        .ups-tpl-assets-empty strong { width:56px; height:56px; border-radius:18px; display:grid; place-items:center; background:#e8f8f2; color:#085041; font-size:16px; }
        .ups-tpl-assets-actions { display:flex; gap:8px; flex-wrap:wrap; margin-top:12px; }
        .ups-tpl-assets-field { margin-top:12px; }
        .ups-tpl-assets-field label { display:block; font-weight:600; margin-bottom:5px; }
        .ups-tpl-assets-field input { width:100%; }
        .ups-tpl-assets-description { min-height:46px; color:#646970; }
      </style>

      <?php if ($status !== "") : ?>
        <div class="notice <?php echo $status === "saved" ? "notice-success" : "notice-error"; ?>"><p><?php echo esc_html(upsellio_template_assets_status_message($status)); ?></p></div>
      <?php endif; ?>

      <form method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>">
        <input type="hidden" name="action" value="upsellio_save_template_assets" />
        <?php wp_nonce_field("upsellio_save_template_assets", "upsellio_template_assets_nonce"); ?>

        <?php foreach ($slots as $slot_key => $slot) : ?>
          <?php
          $slot_config = isset($config[$slot_key]) && is_array($config[$slot_key]) ? $config[$slot_key] : [];
          $attachment_id = (int) ($slot_config["attachment_id"] ?? 0);
          $url_value = trim((string) ($slot_config["url"] ?? ""));
          $image_url = $attachment_id > 0 ? wp_get_attachment_image_url($attachment_id, "medium") : "";
          if ($image_url === "" && $url_value !== "") {
              $image_url = $url_value;
          }
          if ($current_group !== (string) $slot["group"]) :
              if ($current_group !== "") :
                  ?>
                  </div>
                  <?php
              endif;
              $current_group = (string) $slot["group"];
              ?>
              <h2 class="ups-tpl-assets-group"><?php echo esc_html($current_group); ?></h2>
              <div class="ups-tpl-assets-grid">
          <?php endif; ?>

          <section class="ups-tpl-assets-card" data-tpl-asset-card>
            <h3 style="margin:0 0 6px;"><?php echo esc_html((string) $slot["label"]); ?></h3>
            <p class="ups-tpl-assets-description"><?php echo esc_html((string) $slot["description"]); ?></p>
            <div class="ups-tpl-assets-frame" data-tpl-asset-preview>
              <?php if ($image_url !== "") : ?>
                <img src="<?php echo esc_url($image_url); ?>" alt="" />
              <?php else : ?>
                <span class="ups-tpl-assets-empty"><strong><?php echo esc_html((string) $slot["fallback"]); ?></strong><span>Brak obrazu</span></span>
              <?php endif; ?>
            </div>
            <input type="hidden" data-tpl-asset-id name="upsellio_template_assets[<?php echo esc_attr($slot_key); ?>][attachment_id]" value="<?php echo esc_attr((string) $attachment_id); ?>" />
            <div class="ups-tpl-assets-actions">
              <button type="button" class="button" data-tpl-asset-select>Wybierz z biblioteki</button>
              <button type="button" class="button" data-tpl-asset-clear>Usuń</button>
            </div>
            <div class="ups-tpl-assets-field">
              <label for="upsellio_template_assets_<?php echo esc_attr($slot_key); ?>_url">URL (fallback)</label>
              <input id="upsellio_template_assets_<?php echo esc_attr($slot_key); ?>_url" type="url" name="upsellio_template_assets[<?php echo esc_attr($slot_key); ?>][url]" value="<?php echo esc_attr($url_value); ?>" placeholder="https://" />
            </div>
            <div class="ups-tpl-assets-field">
              <label for="upsellio_template_assets_<?php echo esc_attr($slot_key); ?>_alt">Alt</label>
              <input id="upsellio_template_assets_<?php echo esc_attr($slot_key); ?>_alt" type="text" name="upsellio_template_assets[<?php echo esc_attr($slot_key); ?>][alt]" value="<?php echo esc_attr((string) ($slot_config["alt"] ?? "")); ?>" placeholder="<?php echo esc_attr((string) $slot["default_alt"]); ?>" />
            </div>
            <div class="ups-tpl-assets-field">
              <label for="upsellio_template_assets_<?php echo esc_attr($slot_key); ?>_caption">Podpis / opis</label>
              <input id="upsellio_template_assets_<?php echo esc_attr($slot_key); ?>_caption" type="text" name="upsellio_template_assets[<?php echo esc_attr($slot_key); ?>][caption]" value="<?php echo esc_attr((string) ($slot_config["caption"] ?? "")); ?>" placeholder="<?php echo esc_attr((string) $slot["default_caption"]); ?>" />
            </div>
          </section>
        <?php endforeach; ?>
        <?php if ($current_group !== "") : ?>
          </div>
        <?php endif; ?>

        <p style="margin-top:22px;"><button type="submit" class="button button-primary button-large">Zapisz assety</button></p>
      </form>

      <script>
        (function () {
          var cards = Array.prototype.slice.call(document.querySelectorAll("[data-tpl-asset-card]"));
          if (!cards.length || !window.wp || !wp.media) return;

          cards.forEach(function (card) {
            var idInput = card.querySelector("[data-tpl-asset-id]");
            var preview = card.querySelector("[data-tpl-asset-preview]");
            var selectButton = card.querySelector("[data-tpl-asset-select]");
            var clearButton = card.querySelector("[data-tpl-asset-clear]");
            var fallback = preview ? preview.innerHTML : "";
            var frame = null;

            if (selectButton) {
              selectButton.addEventListener("click", function () {
                if (!frame) {
                  frame = wp.media({
                    title: "Wybierz obraz",
                    button: { text: "Użyj tego obrazu" },
                    library: { type: "image" },
                    multiple: false
                  });

                  frame.on("select", function () {
                    var attachment = frame.state().get("selection").first();
                    if (!attachment) return;
                    var data = attachment.toJSON();
                    var url = data.sizes && data.sizes.medium ? data.sizes.medium.url : data.url;
                    if (idInput) idInput.value = data.id || "";
                    if (preview && url) preview.innerHTML = '<img src="' + url.replace(/"/g, "&quot;") + '" alt="" />';
                  });
                }
                frame.open();
              });
            }

            if (clearButton) {
              clearButton.addEventListener("click", function () {
                if (idInput) idInput.value = "";
                if (preview) preview.innerHTML = fallback;
              });
            }
          });
        })();
      </script>
    </div>
    <?php
}
