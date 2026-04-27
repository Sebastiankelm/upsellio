<?php
if (!defined("ABSPATH")) {
    exit;
}

get_header();

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
    $marketAngle = is_array($cityDatasetItem) && !empty($cityDatasetItem["market_angle"])
        ? $cityDatasetItem["market_angle"]
        : (get_post_meta($postId, "_upsellio_city_market_angle", true) ?: "lokalne firmy");
    $serviceFocus = is_array($cityDatasetItem) && !empty($cityDatasetItem["service_focus"])
        ? $cityDatasetItem["service_focus"]
        : (get_post_meta($postId, "_upsellio_city_service_focus", true) ?: "marketing i strony WWW");
    $localChallenge = is_array($cityDatasetItem) && !empty($cityDatasetItem["local_challenge"])
        ? $cityDatasetItem["local_challenge"]
        : (get_post_meta($postId, "_upsellio_city_local_challenge", true) ?: "niska jakość leadów z kampanii");
    $localAdvantage = is_array($cityDatasetItem) && !empty($cityDatasetItem["local_advantage"])
        ? $cityDatasetItem["local_advantage"]
        : (get_post_meta($postId, "_upsellio_city_local_advantage", true) ?: "stabilny popyt lokalny");
    $seasonalityAngle = is_array($cityDatasetItem) && !empty($cityDatasetItem["seasonality_angle"])
        ? $cityDatasetItem["seasonality_angle"]
        : (get_post_meta($postId, "_upsellio_city_seasonality_angle", true) ?: "stabilny popyt przez cały rok");
    $cta = get_post_meta($postId, "_upsellio_city_cta", true);
    $faq = get_post_meta($postId, "_upsellio_city_faq", true);
    if (!is_array($faq)) {
        $faq = [];
    }
    $related = upsellio_get_city_nearby_links($citySlug, 6);
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
    $ctaSeed = abs(crc32($citySlug . "|" . $cityName));

    $voivodeshipKey = function_exists("remove_accents") ? remove_accents((string) $voivodeship) : (string) $voivodeship;
    $voivodeshipKey = strtolower(preg_replace("/\s+/", "-", trim($voivodeshipKey)));
    $voivodeshipMap = [
        "mazowieckie" => [140, 90],
        "slaskie" => [110, 130],
        "wielkopolskie" => [90, 90],
        "malopolskie" => [130, 145],
        "dolnoslaskie" => [70, 110],
        "lubelskie" => [170, 110],
        "pomorskie" => [100, 35],
        "zachodniopomorskie" => [50, 50],
        "lodzkie" => [115, 100],
        "podlaskie" => [175, 65],
        "kujawsko-pomorskie" => [105, 65],
        "swietokrzyskie" => [135, 120],
        "warminsko-mazurskie" => [150, 50],
        "lubuskie" => [55, 80],
        "opolskie" => [95, 125],
        "podkarpackie" => [175, 145],
    ];
    if (isset($voivodeshipMap[$voivodeshipKey])) {
        [$pinX, $pinY] = $voivodeshipMap[$voivodeshipKey];
    } else {
        $pinX = 70 + ($ctaSeed % 110);
        $pinY = 60 + (($ctaSeed >> 4) % 90);
    }

    $ctaActionPool = [
        "Umów bezpłatną konsultację dla %s",
        "Sprawdź potencjał wzrostu firmy w %s",
        "Dopasuj kampanie do decyzji klienta w %s",
        "Popraw konwersję strony dla rynku %s",
        "Zaplanuj 90 dni marketingu dla %s",
        "Zweryfikuj jakość leadów z %s",
        "Uruchom lepiej targetowane reklamy dla %s",
        "Uspójnij lejek sprzedażowy dla %s",
        "Skróć czas od kliku do zapytania w %s",
        "Podnieś skuteczność sprzedaży B2B w %s",
        "Ułóż plan Meta Ads i Google Ads dla %s",
        "Przestaw marketing na mierzalny wynik w %s",
        "Wzmocnij pozycje firmy lokalnie w %s",
        "Zwiększ liczbę wartościowych rozmów z %s",
        "Ułóż stronę pod leady dla %s",
        "Zweryfikuj co blokuje sprzedaż w %s",
        "Skaluj zapytania bez podbijania CPL w %s",
        "Połącz reklamy i stronę dla miasta %s",
        "Przygotuj lokalny plan SEO i Ads dla %s",
        "Ustaw proces pozyskiwania klientów dla %s",
    ];
    $ctaBenefitPool = [
        "i otrzymaj konkretne rekomendacje na pierwsze 2 tygodnie.",
        "bez ogólników i bez przepalania budżetu reklamowego.",
        "z priorytetami wdrożeń pod realną sprzedaż.",
        "z szybkim audytem strony, reklam i analityki.",
        "z naciskiem na jakość leadów, nie tylko ich ilość.",
        "aby kampanie i oferta działały jako jeden system.",
        "z planem testów komunikatu i kreacji reklamowych.",
        "z jasnym podziałem: co poprawić od razu, co skalować dalej.",
        "z mierzeniem pełnej ścieżki: klik, lead, rozmowa, sprzedaż.",
        "aby szybciej domykać zapytania od klientów B2B.",
    ];
    $ctaLibrary = [];
    foreach ($ctaActionPool as $actionText) {
        foreach ($ctaBenefitPool as $benefitText) {
            $ctaLibrary[] = sprintf($actionText . " " . $benefitText, $cityName);
        }
    }
    $ctaLibraryCount = count($ctaLibrary);

    $articleHtml = apply_filters("the_content", get_the_content());

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

    $cityPosts = get_posts([
        "post_type" => "miasto",
        "post_status" => "publish",
        "numberposts" => 200,
        "orderby" => "title",
        "order" => "ASC",
        "fields" => "ids",
    ]);
    $cityLinks = [];
    if (!empty($cityPosts)) {
        foreach ($cityPosts as $cityPostId) {
            if ((int) $cityPostId === (int) $postId) {
                continue;
            }
            $linkedCitySlug = get_post_meta($cityPostId, "_upsellio_city_slug", true) ?: get_post_field("post_name", $cityPostId);
            $linkedCityDatasetItem = function_exists("upsellio_get_city_by_slug") ? upsellio_get_city_by_slug($linkedCitySlug) : null;
            $cityLinks[] = [
                "name" => is_array($linkedCityDatasetItem) && !empty($linkedCityDatasetItem["name"])
                    ? $linkedCityDatasetItem["name"]
                    : (get_post_meta($cityPostId, "_upsellio_city_name", true) ?: get_the_title($cityPostId)),
                "url" => get_permalink($cityPostId),
            ];
        }
    } else {
        foreach (upsellio_get_cities_dataset() as $city) {
            if (($city["slug"] ?? "") === $citySlug) {
                continue;
            }
            $cityLinks[] = [
                "name" => $city["name"],
                "url" => home_url("/miasto/" . $city["slug"] . "/"),
            ];
        }
    }

    $cityInternalLinks = [];
    for ($i = 0; $i < 12; $i++) {
        if (empty($cityLinks)) {
            break;
        }
        $cityInternalLinks[] = $cityLinks[($ctaSeed + $i * 11) % count($cityLinks)];
    }

    $definitionInternalLinks = [];
    for ($i = 0; $i < 8; $i++) {
        if (empty($definitionLinks)) {
            break;
        }
        $definitionInternalLinks[] = $definitionLinks[($ctaSeed + $i * 7) % count($definitionLinks)];
    }

    $inlineCtaIndexes = [
        $ctaSeed % max(1, $ctaLibraryCount),
        ($ctaSeed + 67) % max(1, $ctaLibraryCount),
        ($ctaSeed + 131) % max(1, $ctaLibraryCount),
    ];
    $inlineCityIndexes = [0, 1, 2];
    $inlineDefIndexes = [0, 1, 2];
    $paragraphCounter = 0;
    $inlineCounter = 0;
    $insertAfterParagraphs = [2 + ($ctaSeed % 2), 4 + ($ctaSeed % 3), 7 + ($ctaSeed % 2)];
    $articleHtml = preg_replace_callback(
        "/<\/p>/i",
        function ($matches) use (
            &$paragraphCounter,
            &$inlineCounter,
            $insertAfterParagraphs,
            $ctaLibrary,
            $inlineCtaIndexes,
            $cityInternalLinks,
            $inlineCityIndexes,
            $definitionInternalLinks,
            $inlineDefIndexes
        ) {
            $paragraphCounter++;
            if (!in_array($paragraphCounter, $insertAfterParagraphs, true)) {
                return $matches[0];
            }
            if ($inlineCounter >= count($inlineCtaIndexes)) {
                return $matches[0];
            }
            $ctaText = $ctaLibrary[$inlineCtaIndexes[$inlineCounter]] ?? "";
            $cityLink = $cityInternalLinks[$inlineCityIndexes[$inlineCounter]] ?? null;
            $definitionLink = $definitionInternalLinks[$inlineDefIndexes[$inlineCounter]] ?? null;
            $inlineCounter++;

            if ($ctaText === "") {
                return $matches[0];
            }

            $linksHtml = "";
            if (is_array($cityLink) && !empty($cityLink["url"])) {
                $linksHtml .= '<a href="' . esc_url($cityLink["url"]) . '">Zobacz też: ' . esc_html("Marketing i strony WWW " . $cityLink["name"]) . "</a>";
            }
            if (is_array($definitionLink) && !empty($definitionLink["url"])) {
                $linksHtml .= '<a href="' . esc_url($definitionLink["url"]) . '">Przeczytaj definicję: ' . esc_html($definitionLink["name"]) . "</a>";
            }

            return $matches[0] .
                '<aside class="city-inline-cta">' .
                    '<strong>' . esc_html($ctaText) . "</strong>" .
                    '<div class="city-inline-cta-links">' . $linksHtml . "</div>" .
                    '<a class="city-inline-cta-btn" href="' . esc_url(home_url("/kontakt/")) . '">Umów rozmowę</a>' .
                "</aside>";
        },
        $articleHtml
    );
    ?>
    <style>
      .city-wrap{width:min(1240px,calc(100% - 32px));margin:0 auto}
      .city-hero{padding:72px 0 48px;border-bottom:1px solid var(--border,#e2e8f0);background:radial-gradient(circle at top right,rgba(20,184,166,.18),transparent 36%),linear-gradient(180deg,#ecfeff,#f1f5f9)}
      .city-hero-grid{display:grid;gap:30px;align-items:center}
      .city-hero-copy{min-width:0}
      .city-hero-map{display:none;background:#fff;border:1px solid var(--border,#e2e8f0);border-radius:22px;padding:20px;box-shadow:0 14px 40px rgba(15,23,42,.08)}
      .city-hero-map svg{width:100%;height:auto;display:block}
      .city-hero-map-caption{margin-top:10px;display:flex;justify-content:space-between;gap:10px;align-items:baseline;font-size:12px;color:#475569}
      .city-hero-map-caption strong{color:#0f766e;font-family:Syne,sans-serif;font-size:16px;letter-spacing:-.02em}
      @media(min-width:961px){.city-hero-grid{grid-template-columns:1.25fr .75fr}.city-hero-map{display:block}}
      .city-breadcrumbs{font-size:12px;color:var(--text-3,#64748b);margin-bottom:14px}
      .city-h1{font-family:var(--font-display, "Syne", sans-serif);font-weight:800;font-size:clamp(36px,5vw,62px);line-height:1.02;letter-spacing:-1.5px}
      .city-lead{margin-top:18px;font-size:18px;line-height:1.8;color:var(--text-2,#334155);max-width:860px}
      .city-meta{display:flex;gap:10px;flex-wrap:wrap;margin-top:22px}
      .city-pill{font-size:12px;border:1px solid var(--border-strong,#c9c9c3);border-radius:999px;padding:6px 12px;background:var(--surface,#fff)}
      .city-main{padding:56px 0 72px;display:grid;grid-template-columns:1fr;gap:34px}
      .city-content{line-height:1.8;color:#262624;padding:26px;border:1px solid var(--border,#e6e6e1);border-radius:18px;background:var(--surface,#fff)}
      .city-content h2,.city-content h3{font-family:var(--font-display, "Syne", sans-serif);line-height:1.2;color:#071426}
      .city-content h2{font-size:32px;margin:0 0 16px}
      .city-content h3{font-size:23px;margin:28px 0 10px}
      .city-content p{margin:0 0 14px}
      .city-content ul{margin:0 0 16px 20px}
      .city-content li{margin:0 0 8px}
      .city-content .city-inline-cta{margin:18px 0 22px;padding:16px 16px 14px;border:1px solid var(--teal-line,#99f6e4);background:var(--teal-soft,#ecfeff);border-radius:12px}
      .city-content .city-inline-cta strong{display:block;font-size:15px;line-height:1.5;margin-bottom:8px;color:#0d4637}
      .city-inline-cta-links{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:10px}
      .city-inline-cta-links a{font-size:12px;color:#145f49;font-weight:500}
      .city-inline-cta-btn{display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;padding:7px 12px;border-radius:999px;background:var(--teal,#0d9488);color:#fff}
      .city-side-card{border:1px solid var(--border,#e6e6e1);border-radius:18px;padding:22px;background:var(--surface,#fff);position:static;top:96px}
      .city-side-title{font-family:var(--font-display, "Syne", sans-serif);font-size:22px;margin-bottom:10px}
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
                <span class="city-pill">Województwo: <?php echo esc_html($voivodeship); ?></span>
                <span class="city-pill">Specjalizacja: <?php echo esc_html($marketAngle); ?></span>
                <span class="city-pill">Model: <?php echo esc_html($serviceFocus); ?></span>
              </div>
            </div>
            <aside class="city-hero-map" aria-hidden="true">
              <svg viewBox="0 0 240 200" xmlns="http://www.w3.org/2000/svg">
                <defs>
                  <linearGradient id="city-map-grad" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stop-color="#ecfeff"/>
                    <stop offset="100%" stop-color="#f1f5f9"/>
                  </linearGradient>
                </defs>
                <path d="M40 60 L70 30 L110 28 L140 12 L180 28 L210 50 L218 92 L208 130 L196 158 L172 190 L140 200 L110 196 L78 184 L52 162 L28 130 L24 98 Z" fill="url(#city-map-grad)" stroke="#99f6e4" stroke-width="2"/>
                <circle cx="<?php echo (int) $pinX; ?>" cy="<?php echo (int) $pinY; ?>" r="14" fill="#0d9488" fill-opacity="0.18"/>
                <circle cx="<?php echo (int) $pinX; ?>" cy="<?php echo (int) $pinY; ?>" r="8" fill="#0d9488" fill-opacity="0.32"/>
                <circle cx="<?php echo (int) $pinX; ?>" cy="<?php echo (int) $pinY; ?>" r="4" fill="#0f766e"/>
              </svg>
              <div class="city-hero-map-caption">
                <strong><?php echo esc_html($cityName); ?></strong>
                <span><?php echo esc_html("woj. " . $voivodeship); ?></span>
              </div>
            </aside>
          </div>
        </div>
      </section>

      <section class="city-main city-wrap">
        <article class="city-content">
          <?php echo $articleHtml; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

          <section class="city-local-context" aria-labelledby="city-local-context-title">
            <h2 id="city-local-context-title">Lokalny kontekst dla <?php echo esc_html($cityName); ?></h2>
            <p>
              Każda strona miasta ma osobny kontekst: rynek, wyzwanie, przewagę i sezonowość.
              Dla <?php echo esc_html($cityName); ?> plan kampanii i strony opieram o realne bariery, a nie o ten sam szablon dla każdego miasta.
            </p>
            <div class="city-local-context-grid">
              <div class="city-local-context-item">
                <strong>Najczęstsze wyzwanie</strong>
                <?php echo esc_html($localChallenge); ?>
              </div>
              <div class="city-local-context-item">
                <strong>Lokalna przewaga</strong>
                <?php echo esc_html($localAdvantage); ?>
              </div>
              <div class="city-local-context-item">
                <strong>Sezonowość popytu</strong>
                <?php echo esc_html($seasonalityAngle); ?>
              </div>
            </div>
          </section>

          <div class="city-band">
            <h2>Potrzebujesz planu działań dla <?php echo esc_html($cityName); ?>?</h2>
            <p>W 30 minut pokażę, co warto poprawić najpierw, żeby szybciej podnieść jakość leadów i skuteczność sprzedaży.</p>
          </div>

          <section class="city-conversion-form" id="formularz-miasto">
            <h2>Formularz kontaktowy dla <?php echo esc_html($cityName); ?></h2>
            <p>Zostaw krótki opis sytuacji firmy. Otrzymasz konkretną rekomendację działań dla rynku lokalnego.</p>
            <div class="city-conversion-links">
              <?php if ($contactPhone !== "" && $phoneHref !== "") : ?>
                <a href="<?php echo esc_url("tel:" . $phoneHref); ?>">Zadzwoń: <?php echo esc_html($contactPhone); ?></a>
              <?php endif; ?>
              <a href="<?php echo esc_url($contactEmailHref); ?>">Napisz: <?php echo esc_html($contactEmailDisplay); ?></a>
            </div>
            <form method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" data-upsellio-lead-form="1" data-upsellio-server-form="1">
              <input type="hidden" name="action" value="upsellio_submit_lead" />
              <input type="hidden" name="redirect_url" value="<?php echo esc_url(get_permalink($postId)); ?>" />
              <input type="hidden" name="lead_form_origin" value="miasto-single" />
              <input type="hidden" name="lead_source" value="miasto-single" />
              <input type="hidden" name="utm_source" data-ups-utm="source" value="" />
              <input type="hidden" name="utm_medium" data-ups-utm="medium" value="" />
              <input type="hidden" name="utm_campaign" data-ups-utm="campaign" value="" />
              <input type="hidden" name="landing_url" data-ups-context="landing" value="" />
              <input type="hidden" name="referrer" data-ups-context="referrer" value="" />
              <input type="hidden" name="lead_service" value="<?php echo esc_attr("Marketing lokalny " . $cityName); ?>" />
              <input type="text" name="lead_website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;" />
              <?php wp_nonce_field("upsellio_unified_lead_form", "upsellio_lead_form_nonce"); ?>
              <div class="city-form-grid two">
                <label>
                  Imię i firma *
                  <input type="text" name="lead_name" autocomplete="name organization" required />
                </label>
                <label>
                  E-mail *
                  <input type="email" name="lead_email" autocomplete="email" required />
                </label>
              </div>
              <div class="city-form-grid">
                <label>
                  Telefon
                  <input type="tel" name="lead_phone" autocomplete="tel" />
                </label>
                <label>
                  Co chcesz poprawić? *
                  <textarea name="lead_message" required>Chcę omówić działania marketingowe dla firmy z miasta <?php echo esc_textarea($cityName); ?>.</textarea>
                </label>
              </div>
              <label class="city-form-consent">
                <input type="checkbox" name="lead_consent" value="1" required />
                <span>Wyrażam zgodę na kontakt w sprawie mojego zapytania.</span>
              </label>
              <button class="city-btn" type="submit">Wyślij formularz</button>
            </form>
          </section>

          <section class="city-local-links" aria-label="Linkowanie wewnętrzne lokalne">
            <h2>Sprawdź też inne miasta i tematy</h2>
            <div class="city-link-grid">
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
          <div class="city-side-title">Obsługiwane też w pobliżu</div>
          <div class="city-side-list">
            <?php foreach ($related as $item) : ?>
              <a class="city-side-link" href="<?php echo esc_url($item["url"]); ?>">
                <?php echo esc_html("Marketing i strony WWW " . $item["name"]); ?>
              </a>
            <?php endforeach; ?>
          </div>
          <div class="city-cta">
            <strong><?php echo esc_html($cta ?: ("Umów rozmowę dla " . $cityName)); ?></strong>
            <a class="city-btn" href="<?php echo esc_url(home_url("/#kontakt")); ?>">Umów bezpłatną rozmowę</a>
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

