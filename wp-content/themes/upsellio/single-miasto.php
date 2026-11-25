<?php
if (!defined("ABSPATH")) {
    exit;
}

if (!have_posts()) {
    get_header();
    get_footer();
    return;
}

while (have_posts()) :
    the_post();
    $postId = get_the_ID();
    $citySlug = get_post_meta($postId, "_upsellio_city_slug", true) ?: get_post_field("post_name", $postId);
    $cityDatasetItem = function_exists("upsellio_get_city_by_slug") ? upsellio_get_city_by_slug($citySlug) : null;
    $cityName = is_array($cityDatasetItem) && !empty($cityDatasetItem["name"])
        ? $cityDatasetItem["name"]
        : (get_post_meta($postId, "_upsellio_city_name", true) ?: get_the_title());
    $voivodeship = is_array($cityDatasetItem) && !empty($cityDatasetItem["voivodeship"])
        ? $cityDatasetItem["voivodeship"]
        : (get_post_meta($postId, "_upsellio_city_voivodeship", true) ?: "polska");
    // A region is valid only if it is one of Poland's 16 voivodeships.
    $validRegions = [
        "dolnoslaskie" => "dolnośląskie", "kujawsko-pomorskie" => "kujawsko-pomorskie",
        "lubelskie" => "lubelskie", "lubuskie" => "lubuskie", "lodzkie" => "łódzkie",
        "malopolskie" => "małopolskie", "mazowieckie" => "mazowieckie", "opolskie" => "opolskie",
        "podkarpackie" => "podkarpackie", "podlaskie" => "podlaskie", "pomorskie" => "pomorskie",
        "slaskie" => "śląskie", "swietokrzyskie" => "świętokrzyskie",
        "warminsko-mazurskie" => "warmińsko-mazurskie", "wielkopolskie" => "wielkopolskie",
        "zachodniopomorskie" => "zachodniopomorskie",
    ];
    $normalizeRegion = static function ($value) use ($validRegions) {
        $key = strtolower(trim(remove_accents((string) $value)));
        $key = preg_replace('/^woj(?:ewodztwo|\\.)?\\s+/u', '', $key);
        $key = preg_replace('/\\s+/', '-', trim($key));
        return isset($validRegions[$key]) ? $key : "";
    };
    $voivodeshipKey = $normalizeRegion($voivodeship);
    if ($voivodeshipKey === "") {
        $voivodeshipKey = $normalizeRegion(get_post_meta($postId, "_upsellio_city_voivodeship", true));
    }
    $voivodeship = $validRegions[$voivodeshipKey] ?? "";
    $faq = get_post_meta($postId, "_upsellio_city_faq", true);
    $faq = is_array($faq) ? array_values(array_filter($faq, static function ($item) {
        return is_array($item) && isset($item["q"], $item["a"]) &&
            is_string($item["q"]) && is_string($item["a"]) &&
            trim($item["q"]) !== "" && trim($item["a"]) !== "";
    })) : [];
    $frontPageSections = function_exists("upsellio_get_front_page_content_config")
        ? upsellio_get_front_page_content_config()
        : [];
    $contactPhone = function_exists("upsellio_get_contact_phone")
        ? upsellio_get_contact_phone()
        : trim((string) ($frontPageSections["contact_phone"] ?? ""));
    $contactEmail = trim((string) ($frontPageSections["contact_email"] ?? ""));
    if ($contactEmail === "") {
        $contactEmail = "kontakt@upsellio.pl";
    }
    $contactEmailHref = function_exists("upsellio_get_mailto_href") ? upsellio_get_mailto_href($contactEmail) : ("mailto:" . $contactEmail);
    $contactEmailDisplay = function_exists("upsellio_obfuscate_email_address") ? upsellio_obfuscate_email_address($contactEmail) : $contactEmail;
    $phoneHref = preg_replace("/[^0-9+]/", "", $contactPhone);
    $city_page_title = "Marketing i strony WWW " . $cityName . " - Google Ads i Meta Ads dla firm | Upsellio";
    $city_page_description = "Google Ads, Meta Ads i strony WWW B2B. Obszar obsługi: " . $cityName . ". Poznaj zakres współpracy z Upsellio.";
    $saved_desc = trim((string) get_post_meta($postId, "rank_math_description", true));
    if ($saved_desc === "") {
        $saved_desc = trim((string) get_post_meta($postId, "_yoast_wpseo_metadesc", true));
    }
    if ($saved_desc === "") {
        $saved_desc = trim((string) get_post_meta($postId, "_upsellio_city_meta_description", true));
    }
    if ($saved_desc !== "") {
        $city_page_description = $saved_desc;
    }

    $seo_plugin = function_exists("upsellio_is_seo_plugin_managing_frontend_meta") && upsellio_is_seo_plugin_managing_frontend_meta();

    add_filter("pre_get_document_title", static function ($title) use ($city_page_title, $postId, $seo_plugin) {
        if ($seo_plugin) {
            return $title;
        }
        $rm = trim((string) get_post_meta($postId, "rank_math_title", true));
        if ($rm !== "") {
            return $rm;
        }
        $yoast = trim((string) get_post_meta($postId, "_yoast_wpseo_title", true));
        if ($yoast !== "") {
            return $yoast;
        }

        return $city_page_title !== "" ? $city_page_title : $title;
    });
    add_action("wp_head", static function () use ($postId, $city_page_description, $cityName, $voivodeship, $seo_plugin) {
        $canonical = get_permalink($postId);
        if (!$seo_plugin) {
            echo '<meta name="description" content="' . esc_attr($city_page_description) . "\" />\n";
            echo '<link rel="canonical" href="' . esc_url($canonical) . "\" />\n";
        }
        // Rank Math (or another configured SEO plugin) owns frontend schema.
        if (!$seo_plugin) {
            $areas = [["@type" => "City", "name" => $cityName]];
            if ($voivodeship !== "") {
                $areas[] = ["@type" => "AdministrativeArea", "name" => $voivodeship];
            }
            $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
            echo '<script type="application/ld+json">' . wp_json_encode([
                "@context" => "https://schema.org",
                "@type" => "Service",
                "name" => "Marketing internetowy i strony WWW – " . $cityName,
                "url" => $canonical,
                "provider" => ["@type" => "Organization", "name" => "Upsellio", "url" => home_url("/")],
                "areaServed" => $areas,
            ], $flags) . "</script>\n";
            echo '<script type="application/ld+json">' . wp_json_encode([
                "@context" => "https://schema.org",
                "@type" => "BreadcrumbList",
                "itemListElement" => [
                    ["@type" => "ListItem", "position" => 1, "name" => "Strona główna", "item" => home_url("/")],
                    ["@type" => "ListItem", "position" => 2, "name" => "Miasta", "item" => home_url("/miasta/")],
                    ["@type" => "ListItem", "position" => 3, "name" => $cityName, "item" => $canonical],
                ],
            ], $flags) . "</script>\n";
        }
    }, 2);
    // Register all metadata filters before the header triggers wp_head.
    get_header();
    $polandMapFile = trailingslashit(get_template_directory()) . "assets/images/POL_location_map.svg";
    $polandMapVersion = file_exists($polandMapFile) ? (string) filemtime($polandMapFile) : "1";
    $polandMapSrc = add_query_arg("v", $polandMapVersion, trailingslashit(get_template_directory_uri()) . "assets/images/POL_location_map.svg");

    $articleHtml = apply_filters("the_content", get_the_content());
    // The hero owns the page H1. Preserve legacy heading text and attributes.
    $articleHtml = preg_replace('~<(/?)h1(?=[\\s>])~i', '<$1h2', $articleHtml);
    // Do not append a second FAQ if the editorial article already contains one.
    if (preg_match('~<h[2-6]\\b[^>]*>[^<]*(?:FAQ|najczęstsze pytania|pytania dotyczące)[^<]*</h[2-6]>~iu', $articleHtml)) {
        $faq = [];
    }

    $definitionPosts = get_posts([
        "post_type" => "definicja",
        "post_status" => "publish",
        "numberposts" => 16,
        "orderby" => "date",
        "order" => "DESC",
    ]);
    $definitionLinks = [];
    foreach ($definitionPosts as $definitionPost) {
        $definitionLinks[] = [
            "name" => get_post_meta($definitionPost->ID, "_upsellio_definition_term", true) ?: get_the_title($definitionPost->ID),
            "url" => get_permalink($definitionPost->ID),
        ];
    }

    // Link only published entries in the same verified administrative region.
    $cityInternalLinks = [];
    if ($voivodeshipKey !== "") {
        $cityPosts = get_posts([
            "post_type" => "miasto",
            "post_status" => "publish",
            "numberposts" => -1,
            "orderby" => "title",
            "order" => "ASC",
            "fields" => "ids",
        ]);
        foreach ($cityPosts as $cityPostId) {
            if ((int) $cityPostId === (int) $postId) {
                continue;
            }
            $linkedCitySlug = get_post_meta($cityPostId, "_upsellio_city_slug", true) ?: get_post_field("post_name", $cityPostId);
            $dataset = function_exists("upsellio_get_city_by_slug") ? upsellio_get_city_by_slug($linkedCitySlug) : null;
            $region = $normalizeRegion(is_array($dataset) ? ($dataset["voivodeship"] ?? "") : "");
            if ($region === "") {
                $region = $normalizeRegion(get_post_meta($cityPostId, "_upsellio_city_voivodeship", true));
            }
            if ($region !== $voivodeshipKey) {
                continue;
            }
            $cityInternalLinks[] = [
                "name" => is_array($dataset) && !empty($dataset["name"]) ? $dataset["name"] :
                    (get_post_meta($cityPostId, "_upsellio_city_name", true) ?: get_the_title($cityPostId)),
                "url" => get_permalink($cityPostId),
            ];
            if (count($cityInternalLinks) >= 12) {
                break;
            }
        }
    }
    $related = array_slice($cityInternalLinks, 0, 6);
    $definitionInternalLinks = array_slice($definitionLinks, 0, 8);
    ?>
    <style>
      .city-wrap{width:min(1240px,calc(100% - 32px));margin:0 auto}
      .city-hero{padding:72px 0 48px;border-bottom:1px solid var(--border,#e2e8f0);background:radial-gradient(circle at top right,rgba(20,184,166,.18),transparent 36%),linear-gradient(180deg,#ecfeff,#f1f5f9)}
      .city-hero-grid{display:grid;gap:30px;align-items:center}
      .city-hero-copy{min-width:0}
      .city-hero-map{display:none;background:#fff;border:1px solid var(--border,#e2e8f0);border-radius:22px;padding:20px;box-shadow:0 14px 40px rgba(15,23,42,.08)}
      .city-hero-map-visual{position:relative}
      .city-hero-map-visual img{width:100%;aspect-ratio:497/463;height:auto;display:block;object-fit:contain}
      .city-hero-map-pin{position:absolute;transform:translate(-50%,-50%);pointer-events:none}
      .city-hero-map-pin::before,.city-hero-map-pin::after{content:"";position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);border-radius:999px}
      .city-hero-map-pin::before{width:34px;height:34px;background:rgba(13,148,136,.18)}
      .city-hero-map-pin::after{width:18px;height:18px;background:rgba(13,148,136,.32)}
      .city-hero-map-pin-dot{display:block;width:10px;height:10px;border-radius:999px;background:#0f766e}
      .city-hero-map-caption{margin-top:10px;display:flex;justify-content:space-between;gap:10px;align-items:baseline;font-size:12px;color:#475569}
      .city-hero-map-caption strong{color:#0f766e;font-family:"Bricolage Grotesque",sans-serif;font-size:16px;letter-spacing:-.02em}
      @media(min-width:961px){.city-hero-grid{grid-template-columns:1.25fr .75fr}.city-hero-map{display:block}}
      .city-breadcrumbs{font-size:12px;color:var(--text-3,#64748b);margin-bottom:14px}
      .city-h1{font-family:var(--font-display, "Bricolage Grotesque", sans-serif);font-weight:800;font-size:clamp(36px,5vw,62px);line-height:1.02;letter-spacing:-1.5px}
      .city-lead{margin-top:18px;font-size:18px;line-height:1.8;color:var(--text-2,#334155);max-width:860px}
      .city-meta{display:flex;gap:10px;flex-wrap:wrap;margin-top:22px}
      .city-pill{font-size:12px;border:1px solid var(--border-strong,#c9c9c3);border-radius:999px;padding:6px 12px;background:var(--surface,#fff)}
      .city-main{padding:56px 0 72px;display:grid;grid-template-columns:1fr;gap:34px}
      .city-content{min-width:0;overflow-wrap:anywhere;line-height:1.8;color:#262624;padding:26px;border:1px solid var(--border,#e6e6e1);border-radius:18px;background:var(--surface,#fff)}
      .city-content h2,.city-content h3{font-family:var(--font-display, "Bricolage Grotesque", sans-serif);line-height:1.2;color:#071426}
      .city-content h2{font-size:32px;margin:0 0 16px}
      .city-content h3{font-size:23px;margin:28px 0 10px}
      .city-content p{margin:0 0 14px}
      .city-content table{display:block;max-width:100%;overflow-x:auto;border-collapse:collapse;margin:18px 0}
      .city-content th,.city-content td{padding:10px 12px;border:1px solid var(--border,#e2e8f0);vertical-align:top;text-align:left}
      .city-content ul{margin:0 0 16px 20px}
      .city-content li{margin:0 0 8px}
      .city-content .city-inline-cta{margin:18px 0 22px;padding:16px 16px 14px;border:1px solid var(--teal-line,#99f6e4);background:var(--teal-soft,#ecfeff);border-radius:12px}
      .city-content .city-inline-cta strong{display:block;font-size:15px;line-height:1.5;margin-bottom:8px;color:#0d4637}
      .city-inline-cta-links{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:10px}
      .city-inline-cta-links a{font-size:12px;color:#145f49;font-weight:500}
      .city-inline-cta-btn{display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;padding:7px 12px;border-radius:999px;background:var(--teal,#0d9488);color:#fff}
      .city-side-card{border:1px solid var(--border,#e6e6e1);border-radius:18px;padding:22px;background:var(--surface,#fff);position:static;top:96px}
      .city-side-title{font-family:var(--font-display, "Bricolage Grotesque", sans-serif);font-size:22px;margin-bottom:10px}
      .city-side-list{display:grid;gap:8px;margin-top:14px}
      .city-side-link{font-size:14px;color:#5f5f58}
      .city-side-link:hover{color:var(--teal,#0d9488)}
      .city-cta{margin-top:22px;padding:16px;border-radius:12px;background:var(--teal-soft,#ecfeff);border:1px solid var(--teal-line,#99f6e4)}
      .city-cta strong{display:block;margin-bottom:8px}
      .city-cta-meta{display:flex;flex-direction:column;gap:5px;font-size:13px;margin-top:10px}
      .city-cta-meta a{color:#125f47}
      .city-btn{display:inline-flex;margin-top:12px;background:var(--teal,#0d9488);color:#fff;padding:11px 16px;border-radius:10px}
      .city-btn:hover{background:var(--teal-hover,#0f766e)}
      .city-faq{margin-top:42px;border-top:1px solid var(--border,#e6e6e1);padding-top:28px}
      .city-faq-item + .city-faq-item{margin-top:16px}
      .city-band{margin-top:26px;padding:24px;border-radius:16px;background:var(--teal-soft,#ecfeff);border:1px solid var(--teal-line,#99f6e4)}
      .city-band h2{font-size:26px;margin:0 0 8px}
      .city-band p{margin:0;color:#0f766e}
      .city-local-context{margin:28px 0;padding:24px;border:1px solid var(--border,#e6e6e1);border-radius:16px;background:#f8fafc}
      .city-local-context h2{font-size:26px;margin:0 0 12px}
      .city-local-context-grid{display:grid;gap:12px;margin-top:18px}
      .city-local-context-item{padding:14px;border-radius:12px;background:#fff;border:1px solid var(--border,#e6e6e1)}
      .city-local-context-item strong{display:block;margin-bottom:6px;color:#071426}
      .city-conversion-form{margin-top:28px;padding:22px;border:1px solid var(--border,#e6e6e1);border-radius:16px;background:#fff}
      .city-conversion-form h2{font-size:26px;margin:0 0 8px}
      .city-conversion-form p{margin:0 0 14px;color:#484842}
      .city-conversion-links{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:14px}
      .city-conversion-links a{font-size:13px;font-weight:600}
      .city-form-grid{display:grid;gap:12px}
      .city-form-grid.two{grid-template-columns:1fr}
      .city-form-grid input,.city-form-grid textarea{width:100%;border:1px solid var(--border-strong,#c9c9c3);border-radius:10px;padding:10px 12px;font:inherit}
      .city-form-grid textarea{min-height:110px;resize:vertical}
      .city-form-grid label{display:grid;gap:5px;font-size:13px;color:#30302c}
      .city-form-consent{display:flex;gap:8px;align-items:flex-start;font-size:12px;color:#56564f}
      .city-form-consent input{margin-top:4px}
      .city-local-links{margin-top:30px;padding-top:24px;border-top:1px solid var(--border,#e6e6e1)}
      .city-local-links h2{font-size:24px;margin:0 0 10px}
      .city-link-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px 16px}
      .city-link-grid a{font-size:13px;color:#5f5f58}
      .city-link-grid a:hover{color:var(--teal,#0d9488)}
      @media(min-width:761px){.city-wrap{width:min(1240px,calc(100% - 48px))}}
      @media(min-width:761px){.city-form-grid.two{grid-template-columns:1fr 1fr}.city-link-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.city-local-context-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
      @media(min-width:961px){.city-main{grid-template-columns:minmax(0,1fr) 340px}.city-side-card{position:sticky}}
    </style>

    <main>
      <section class="city-hero">
        <div class="city-wrap">
          <div class="city-hero-grid">
            <div class="city-hero-copy">
              <nav class="city-breadcrumbs" aria-label="Breadcrumb">
                <a href="<?php echo esc_url(home_url("/")); ?>">Strona główna</a> /
                <a href="<?php echo esc_url(home_url("/miasta/")); ?>">Miasta</a> /
                <span><?php echo esc_html($cityName); ?></span>
              </nav>
              <h1 class="city-h1">Marketing i strony WWW <?php echo esc_html($cityName); ?></h1>
              <p class="city-lead">
                Skuteczne pozyskiwanie klientów w mieście <?php echo esc_html($cityName); ?>:
                kampanie Meta i Google Ads, strony pod konwersję oraz wsparcie sprzedaży B2B i usług.
              </p>
              <div class="city-meta">
                <?php if ($voivodeship !== "") : ?>
                  <span class="city-pill">Województwo: <?php echo esc_html($voivodeship); ?></span>
                <?php endif; ?>
                <span class="city-pill">Google Ads · Meta Ads · Strony WWW</span>
              </div>
            </div>
            <aside class="city-hero-map" aria-hidden="true">
              <div class="city-hero-map-visual">
                <img src="<?php echo esc_url($polandMapSrc); ?>" alt="" loading="lazy" decoding="async"/>
              </div>
              <div class="city-hero-map-caption">
                <strong><?php echo esc_html($cityName); ?></strong>
                <?php if ($voivodeship !== "") : ?><span><?php echo esc_html("woj. " . $voivodeship); ?></span><?php endif; ?>
              </div>
            </aside>
          </div>
        </div>
      </section>

      <section class="city-main city-wrap">
        <article class="city-content">
          <?php echo $articleHtml; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

          <div class="city-band">
            <h2>Omówmy plan marketingowy – <?php echo esc_html($cityName); ?></h2>
            <p>Opisz ofertę, obszar działania i cele sprzedażowe, aby ustalić zakres współpracy.</p>
          </div>

          <section class="city-conversion-form" id="formularz-miasto">
            <h2>Formularz kontaktowy – <?php echo esc_html($cityName); ?></h2>
            <p>Zostaw krótki opis sytuacji firmy, aby omówić możliwe działania.</p>
            <div class="city-conversion-links">
              <?php if ($contactPhone !== "" && $phoneHref !== "") : ?>
                <a href="<?php echo esc_url("tel:" . $phoneHref); ?>">Zadzwoń: <?php echo esc_html($contactPhone); ?></a>
              <?php endif; ?>
              <a href="<?php echo esc_url($contactEmailHref); ?>">Napisz: <?php echo esc_html($contactEmailDisplay); ?></a>
            </div>
            <?php
            echo upsellio_render_lead_form([
                "origin" => "miasto-single",
                "variant" => "compact",
                "submit_label" => "Wyślij zapytanie →",
                "redirect_url" => get_permalink($postId),
                "preset_message" => "Chcę omówić działania marketingowe dla firmy z miasta " . $cityName . ".",
                "hidden_service" => "Marketing lokalny " . $cityName,
                "css_class" => "city-lead-form",
            ]);
            ?>
          </section>

          <section class="city-local-links" aria-label="Linkowanie wewnętrzne lokalne">
            <h2>Inne lokalizacje i tematy</h2>
            <div class="city-link-grid">
              <a href="<?php echo esc_url(home_url("/miasta/")); ?>">Wszystkie miasta</a>
              <?php foreach ($cityInternalLinks as $cityLink) : ?>
                <a href="<?php echo esc_url($cityLink["url"]); ?>">
                  <?php echo esc_html("Marketing i strony WWW " . $cityLink["name"]); ?>
                </a>
              <?php endforeach; ?>
              <?php foreach ($definitionInternalLinks as $definitionLink) : ?>
                <a href="<?php echo esc_url($definitionLink["url"]); ?>">
                  <?php echo esc_html("Definicja: " . $definitionLink["name"]); ?>
                </a>
              <?php endforeach; ?>
            </div>
          </section>

          <?php if (!empty($faq)) : ?>
            <div class="city-faq">
              <h2>Lokalne FAQ - <?php echo esc_html($cityName); ?></h2>
              <?php foreach ($faq as $item) : ?>
                <div class="city-faq-item">
                  <h3><?php echo esc_html($item["q"]); ?></h3>
                  <p><?php echo esc_html($item["a"]); ?></p>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </article>

        <aside class="city-side-card">
          <div class="city-side-title"><?php echo $voivodeship !== "" ? "Inne miasta w województwie" : "Obszar obsługi"; ?></div>
          <div class="city-side-list">
            <a class="city-side-link" href="<?php echo esc_url(home_url("/miasta/")); ?>">Katalog miast</a>
            <?php foreach ($related as $item) : ?>
              <a class="city-side-link" href="<?php echo esc_url($item["url"]); ?>">
                <?php echo esc_html("Marketing i strony WWW " . $item["name"]); ?>
              </a>
            <?php endforeach; ?>
          </div>
          <div class="city-cta">
            <strong>Porozmawiaj o marketingu – <?php echo esc_html($cityName); ?></strong>
            <a class="city-btn" href="<?php echo esc_url(home_url("/kontakt/")); ?>">Skontaktuj się</a>
            <div class="city-cta-meta">
              <?php if ($contactPhone !== "" && $phoneHref !== "") : ?>
                <a href="<?php echo esc_url("tel:" . $phoneHref); ?>">Telefon: <?php echo esc_html($contactPhone); ?></a>
              <?php endif; ?>
              <a href="<?php echo esc_url($contactEmailHref); ?>">E-mail: <?php echo esc_html($contactEmailDisplay); ?></a>
            </div>
          </div>
        </aside>
      </section>
    </main>
    <?php
endwhile;

get_footer();

