<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_home_media_option_key()
{
    return "upsellio_home_media_v1";
}

function upsellio_home_media_slots()
{
    return [
        "hero_portrait" => [
            "group" => "Hero i autor",
            "label" => "Portret w hero",
            "description" => "Profesjonalny półportret obok mini dashboardu.",
            "fallback" => "SK",
            "default_alt" => "Sebastian Kelm - marketing B2B i kampanie lead generation",
            "default_caption" => "Sebastian Kelm",
        ],
        "about_portrait" => [
            "group" => "Hero i autor",
            "label" => "Zdjęcie w sekcji Kim jestem",
            "description" => "Zdjęcie budujące zaufanie w sekcji eksperckiej.",
            "fallback" => "SK",
            "default_alt" => "Sebastian Kelm - ekspert Google Ads, Meta Ads i sprzedaży B2B",
            "default_caption" => "10+ lat praktyki w sprzedaży i marketingu B2B",
        ],
        "service_meta" => [
            "group" => "Usługi",
            "label" => "Meta Ads",
            "description" => "Screenshot, kreacja lub grafika wspierająca kartę Meta Ads.",
            "fallback" => "Meta",
            "default_alt" => "Panel kampanii Meta Ads",
            "default_caption" => "Popyt, retargeting i jakościowe leady",
        ],
        "service_google" => [
            "group" => "Usługi",
            "label" => "Google Ads",
            "description" => "Screenshot Google Ads lub grafika intencji zakupowej.",
            "fallback" => "GAds",
            "default_alt" => "Panel wyników kampanii Google Ads",
            "default_caption" => "Intencja zakupowa przełożona na zapytania",
        ],
        "service_web" => [
            "group" => "Usługi",
            "label" => "Strony internetowe",
            "description" => "Mockup strony, landing page albo fragment dashboardu CRO.",
            "fallback" => "WWW",
            "default_alt" => "Landing page zaprojektowany pod konwersję",
            "default_caption" => "Strona, która domyka ruch z kampanii",
        ],
        "case_dashboard" => [
            "group" => "Dowody i wyniki",
            "label" => "Screenshot case study",
            "description" => "Anonimizowany screenshot z kampanii lub dashboardu.",
            "fallback" => "KPI",
            "default_alt" => "Anonimizowany dashboard wyników kampanii",
            "default_caption" => "Dowód pracy: dane kampanii po uporządkowaniu lejka",
        ],
        "testimonial_1" => [
            "group" => "Opinie",
            "label" => "Avatar opinii 1",
            "description" => "Zdjęcie klienta lub logo. Bez obrazu pojawią się inicjały.",
            "fallback" => "MK",
            "default_alt" => "Avatar klienta Upsellio",
            "default_caption" => "Marek, właściciel firmy B2B",
        ],
        "testimonial_2" => [
            "group" => "Opinie",
            "label" => "Avatar opinii 2",
            "description" => "Zdjęcie klienta lub logo. Bez obrazu pojawią się inicjały.",
            "fallback" => "AN",
            "default_alt" => "Avatar klientki Upsellio",
            "default_caption" => "Anna, marketing manager",
        ],
        "testimonial_3" => [
            "group" => "Opinie",
            "label" => "Avatar opinii 3",
            "description" => "Zdjęcie klienta lub logo. Bez obrazu pojawią się inicjały.",
            "fallback" => "PT",
            "default_alt" => "Avatar klienta Upsellio",
            "default_caption" => "Piotr, CEO e-commerce",
        ],
    ];
}

function upsellio_sanitize_home_media_config($config)
{
    $slots = upsellio_home_media_slots();
    $safe_config = [];

    foreach ($slots as $slot_key => $slot) {
        $raw_slot = isset($config[$slot_key]) && is_array($config[$slot_key]) ? $config[$slot_key] : [];
        $attachment_id = isset($raw_slot["attachment_id"]) ? absint($raw_slot["attachment_id"]) : 0;
        if ($attachment_id > 0 && !wp_attachment_is_image($attachment_id)) {
            $attachment_id = 0;
        }

        $safe_config[$slot_key] = [
            "attachment_id" => $attachment_id,
            "alt" => sanitize_text_field((string) ($raw_slot["alt"] ?? "")),
            "caption" => sanitize_text_field((string) ($raw_slot["caption"] ?? "")),
        ];
    }

    return $safe_config;
}

function upsellio_get_home_media_config()
{
    $stored = get_option(upsellio_home_media_option_key(), []);
    if (!is_array($stored)) {
        $stored = [];
    }

    return upsellio_sanitize_home_media_config($stored);
}

function upsellio_get_home_media_slot($slot_key)
{
    $slot_key = (string) $slot_key;
    $slots = upsellio_home_media_slots();
    if (!isset($slots[$slot_key])) {
        return [];
    }

    $config = upsellio_get_home_media_config();
    $saved = isset($config[$slot_key]) && is_array($config[$slot_key]) ? $config[$slot_key] : [];

    return array_merge($slots[$slot_key], [
        "key" => $slot_key,
        "attachment_id" => (int) ($saved["attachment_id"] ?? 0),
        "alt" => trim((string) ($saved["alt"] ?? "")),
        "caption" => trim((string) ($saved["caption"] ?? "")),
    ]);
}

function upsellio_get_home_media_slot_alt($slot)
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

function upsellio_render_home_media_image($slot_key, $args = [])
{
    $slot = upsellio_get_home_media_slot($slot_key);
    if ($slot === []) {
        return "";
    }

    $class_name = trim((string) ($args["class"] ?? "home-media-image"));
    $size = (string) ($args["size"] ?? "large");
    $attachment_id = (int) ($slot["attachment_id"] ?? 0);
    if ($attachment_id > 0) {
        $image = wp_get_attachment_image($attachment_id, $size, false, [
            "class" => $class_name,
            "alt" => upsellio_get_home_media_slot_alt($slot),
            "loading" => (string) ($args["loading"] ?? "lazy"),
            "decoding" => "async",
        ]);
        if ($image !== "") {
            return $image;
        }
    }

    $fallback_class = trim($class_name . " home-media-fallback");
    $fallback = trim((string) ($slot["fallback"] ?? "UP"));
    return '<div class="' . esc_attr($fallback_class) . '" aria-hidden="true"><span>' . esc_html($fallback) . '</span></div>';
}

function upsellio_home_media_slot_caption($slot_key)
{
    $slot = upsellio_get_home_media_slot($slot_key);
    if ($slot === []) {
        return "";
    }

    $caption = trim((string) ($slot["caption"] ?? ""));
    if ($caption !== "") {
        return $caption;
    }

    return trim((string) ($slot["default_caption"] ?? ""));
}

function upsellio_home_media_admin_url()
{
    return admin_url("themes.php?page=upsellio-home-media");
}

function upsellio_home_media_admin_menu()
{
    add_submenu_page(
        "themes.php",
        "Media strony głównej Upsellio",
        "Media strony głównej",
        "manage_options",
        "upsellio-home-media",
        "upsellio_render_home_media_admin_screen",
        82
    );
}
add_action("admin_menu", "upsellio_home_media_admin_menu");

function upsellio_home_media_admin_enqueue($hook)
{
    if ((string) $hook !== "appearance_page_upsellio-home-media") {
        return;
    }

    wp_enqueue_media();
}
add_action("admin_enqueue_scripts", "upsellio_home_media_admin_enqueue");

function upsellio_home_media_redirect($status)
{
    wp_safe_redirect(add_query_arg("upsellio_home_media_status", sanitize_key((string) $status), upsellio_home_media_admin_url()));
    exit;
}

function upsellio_handle_save_home_media()
{
    if (!current_user_can("manage_options")) {
        upsellio_home_media_redirect("no_permission");
    }
    if (!isset($_POST["upsellio_home_media_nonce"]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST["upsellio_home_media_nonce"])), "upsellio_save_home_media")) {
        upsellio_home_media_redirect("bad_nonce");
    }

    $raw_media = isset($_POST["upsellio_home_media"]) && is_array($_POST["upsellio_home_media"])
        ? wp_unslash($_POST["upsellio_home_media"])
        : [];

    update_option(upsellio_home_media_option_key(), upsellio_sanitize_home_media_config($raw_media), false);
    upsellio_home_media_redirect("saved");
}
add_action("admin_post_upsellio_save_home_media", "upsellio_handle_save_home_media");

function upsellio_home_media_status_message($status)
{
    $messages = [
        "saved" => "Media strony głównej zostały zapisane.",
        "bad_nonce" => "Sesja wygasła. Odśwież stronę i spróbuj ponownie.",
        "no_permission" => "Brak uprawnień do edycji mediów strony głównej.",
    ];

    return (string) ($messages[$status] ?? "Nie udało się zapisać zmian.");
}

function upsellio_render_home_media_admin_screen()
{
    if (!current_user_can("manage_options")) {
        return;
    }

    $slots = upsellio_home_media_slots();
    $config = upsellio_get_home_media_config();
    $status = isset($_GET["upsellio_home_media_status"]) ? sanitize_key(wp_unslash($_GET["upsellio_home_media_status"])) : "";
    $current_group = "";
    ?>
    <div class="wrap">
      <h1>Media strony głównej</h1>
      <p style="max-width:820px;">Przypisz obrazy do konkretnych miejsc landing page: hero, sekcji autora, usług, case study i opinii. Gdy slot zostanie pusty, front pokaże estetyczny placeholder albo inicjały.</p>
      <style>
        .ups-home-media-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:16px; max-width:1240px; }
        .ups-home-media-group { margin:26px 0 12px; }
        .ups-home-media-card { background:#fff; border:1px solid #dcdcde; border-radius:14px; padding:16px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
        .ups-home-media-frame { min-height:170px; display:grid; place-items:center; border:1px dashed #cbd5e1; border-radius:12px; background:linear-gradient(135deg,#f8fafc,#fff); overflow:hidden; color:#64748b; text-align:center; }
        .ups-home-media-frame img { width:100%; height:190px; object-fit:cover; display:block; }
        .ups-home-media-empty { display:grid; gap:6px; place-items:center; padding:18px; }
        .ups-home-media-empty strong { width:56px; height:56px; border-radius:18px; display:grid; place-items:center; background:#e8f8f2; color:#085041; font-size:16px; }
        .ups-home-media-actions { display:flex; gap:8px; flex-wrap:wrap; margin-top:12px; }
        .ups-home-media-field { margin-top:12px; }
        .ups-home-media-field label { display:block; font-weight:600; margin-bottom:5px; }
        .ups-home-media-field input { width:100%; }
        .ups-home-media-description { min-height:38px; color:#646970; }
      </style>

      <?php if ($status !== "") : ?>
        <div class="notice <?php echo $status === "saved" ? "notice-success" : "notice-error"; ?>"><p><?php echo esc_html(upsellio_home_media_status_message($status)); ?></p></div>
      <?php endif; ?>

      <form method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>">
        <input type="hidden" name="action" value="upsellio_save_home_media" />
        <?php wp_nonce_field("upsellio_save_home_media", "upsellio_home_media_nonce"); ?>

        <?php foreach ($slots as $slot_key => $slot) : ?>
          <?php
          $slot_config = isset($config[$slot_key]) && is_array($config[$slot_key]) ? $config[$slot_key] : [];
          $attachment_id = (int) ($slot_config["attachment_id"] ?? 0);
          $image_url = $attachment_id > 0 ? wp_get_attachment_image_url($attachment_id, "medium") : "";
          if ($current_group !== (string) $slot["group"]) :
              if ($current_group !== "") :
                  ?>
                  </div>
                  <?php
              endif;
              $current_group = (string) $slot["group"];
              ?>
              <h2 class="ups-home-media-group"><?php echo esc_html($current_group); ?></h2>
              <div class="ups-home-media-grid">
          <?php endif; ?>

          <section class="ups-home-media-card" data-home-media-card>
            <h3 style="margin:0 0 6px;"><?php echo esc_html((string) $slot["label"]); ?></h3>
            <p class="ups-home-media-description"><?php echo esc_html((string) $slot["description"]); ?></p>
            <div class="ups-home-media-frame" data-home-media-preview>
              <?php if ($image_url !== "") : ?>
                <img src="<?php echo esc_url($image_url); ?>" alt="" />
              <?php else : ?>
                <span class="ups-home-media-empty"><strong><?php echo esc_html((string) $slot["fallback"]); ?></strong><span>Brak wybranego obrazu</span></span>
              <?php endif; ?>
            </div>
            <input type="hidden" data-home-media-id name="upsellio_home_media[<?php echo esc_attr($slot_key); ?>][attachment_id]" value="<?php echo esc_attr((string) $attachment_id); ?>" />
            <div class="ups-home-media-actions">
              <button type="button" class="button" data-home-media-select>Wybierz obraz</button>
              <button type="button" class="button" data-home-media-clear>Usuń</button>
            </div>
            <div class="ups-home-media-field">
              <label for="upsellio_home_media_<?php echo esc_attr($slot_key); ?>_alt">Alt</label>
              <input id="upsellio_home_media_<?php echo esc_attr($slot_key); ?>_alt" type="text" name="upsellio_home_media[<?php echo esc_attr($slot_key); ?>][alt]" value="<?php echo esc_attr((string) ($slot_config["alt"] ?? "")); ?>" placeholder="<?php echo esc_attr((string) $slot["default_alt"]); ?>" />
            </div>
            <div class="ups-home-media-field">
              <label for="upsellio_home_media_<?php echo esc_attr($slot_key); ?>_caption">Podpis / opis</label>
              <input id="upsellio_home_media_<?php echo esc_attr($slot_key); ?>_caption" type="text" name="upsellio_home_media[<?php echo esc_attr($slot_key); ?>][caption]" value="<?php echo esc_attr((string) ($slot_config["caption"] ?? "")); ?>" placeholder="<?php echo esc_attr((string) $slot["default_caption"]); ?>" />
            </div>
          </section>
        <?php endforeach; ?>
        <?php if ($current_group !== "") : ?>
          </div>
        <?php endif; ?>

        <p style="margin-top:22px;"><button type="submit" class="button button-primary button-large">Zapisz media strony głównej</button></p>
      </form>

      <script>
        (function () {
          var cards = Array.prototype.slice.call(document.querySelectorAll("[data-home-media-card]"));
          if (!cards.length || !window.wp || !wp.media) return;

          cards.forEach(function (card) {
            var idInput = card.querySelector("[data-home-media-id]");
            var preview = card.querySelector("[data-home-media-preview]");
            var selectButton = card.querySelector("[data-home-media-select]");
            var clearButton = card.querySelector("[data-home-media-clear]");
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
