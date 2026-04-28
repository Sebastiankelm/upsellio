<?php
if (!defined("ABSPATH")) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();
    $postId = get_the_ID();
    $term = get_post_meta($postId, "_upsellio_definition_term", true) ?: get_the_title($postId);
    $slug = get_post_meta($postId, "_upsellio_definition_slug", true) ?: get_post_field("post_name", $postId);
    $mainKeyword = get_post_meta($postId, "_upsellio_definition_main_keyword", true);
    $category = get_post_meta($postId, "_upsellio_definition_category", true) ?: "marketing";
    $difficulty = get_post_meta($postId, "_upsellio_definition_difficulty", true) ?: "sredni";
    $related = upsellio_get_definition_related_links($slug, 6);
    $adjacent = upsellio_get_definition_adjacent_links($slug);
    $faq = get_post_meta($postId, "_upsellio_definition_faq", true);
    if (!is_array($faq)) {
        $faq = [];
    }
    $serviceLinks = get_post_meta($postId, "_upsellio_definition_service_links", true);
    if (!is_array($serviceLinks)) {
        $serviceLinks = [];
    }
    $contactPhone = function_exists("upsellio_get_contact_phone")
        ? upsellio_get_contact_phone()
        : "+48 575 522 595";
    $contactPhoneHref = preg_replace("/\s+/", "", $contactPhone);
    $contactEmail = "kontakt@upsellio.pl";
    $contactEmailHref = function_exists("upsellio_get_mailto_href") ? upsellio_get_mailto_href($contactEmail) : ("mailto:" . $contactEmail);
    $contactEmailDisplay = function_exists("upsellio_obfuscate_email_address") ? upsellio_obfuscate_email_address($contactEmail) : $contactEmail;
    $seed = abs(crc32($slug . "|" . $term));
    $archive_definitions_url = get_post_type_archive_link("definicja");
    $articleHtml = apply_filters("the_content", get_the_content());

    $toolIntroPool = [
        "Szybko oszacuj, jak termin %s przekłada się na wynik kampanii i strony.",
        "To proste narzędzie pomaga zinterpretować %s w kontekście realnych danych.",
        "Sprawdź praktyczny wynik dla %s na bazie trzech kluczowych liczb.",
        "W 30 sekund policz orientacyjny potencjał poprawy związany z %s.",
        "Narzędzie wspiera szybką diagnozę, czy %s jest obecnie dobrze wykorzystywane.",
    ];
    $toolScoreLabelPool = [
        "Potencjał optymalizacji",
        "Wskaźnik gotowości",
        "Priorytet wdrożenia",
        "Indeks skuteczności",
        "Poziom dopasowania",
    ];
    $toolPrimaryLabelPool = [
        "Miesięczny budżet reklamowy (PLN)",
        "Miesięczna liczba sesji na stronie",
        "Liczba leadów miesięcznie",
        "Średnia wartość koszyka/oferty (PLN)",
        "Liczba zapytań handlowych miesięcznie",
    ];
    $toolSecondaryLabelPool = [
        "Aktualny współczynnik konwersji (%)",
        "Szacowany CTR kampanii (%)",
        "Jaka część leadów jest wartościowa? (%)",
        "Jak oceniasz jakość ruchu? (1-100)",
        "Jaki odsetek ruchu wraca na stronę? (%)",
    ];
    $toolThirdLabelPool = [
        "Docelowa poprawa w 90 dni (%)",
        "Przewidywana poprawa po wdrożeniu (%)",
        "Możliwa redukcja kosztu pozyskania (%)",
        "Wzrost jakości leadów po zmianach (%)",
        "Planowany wzrost konwersji po testach (%)",
    ];
    $toolActionPool = [
        "Skup się na ujednoliceniu komunikatu reklamy i strony.",
        "Priorytet: poprawa intencji ruchu i filtrowanie leadów.",
        "Największy efekt da testowanie oferty i CTA.",
        "Wartym krokiem jest audyt lejka i analityki konwersji.",
        "Zacznij od 2-3 testów, potem skaluj działania.",
    ];
    $diagramKey = strtolower((string) $category);
    if (strpos($diagramKey, "seo") !== false) {
        $diagramType = "seo";
    } elseif (strpos($diagramKey, "konwers") !== false || strpos($diagramKey, "cro") !== false) {
        $diagramType = "konwersja";
    } elseif (strpos($diagramKey, "anali") !== false || strpos($diagramKey, "data") !== false) {
        $diagramType = "analityka";
    } elseif (strpos($diagramKey, "reklam") !== false || strpos($diagramKey, "ads") !== false || strpos($diagramKey, "ppc") !== false) {
        $diagramType = "reklamy";
    } else {
        $diagramType = "marketing";
    }
    $toolTitle = "Kalkulator praktyczny: " . $term;
    $toolIntro = sprintf($toolIntroPool[$seed % count($toolIntroPool)], $term);
    $toolScoreLabel = $toolScoreLabelPool[$seed % count($toolScoreLabelPool)];
    $toolPrimaryLabel = $toolPrimaryLabelPool[($seed + 5) % count($toolPrimaryLabelPool)];
    $toolSecondaryLabel = $toolSecondaryLabelPool[($seed + 11) % count($toolSecondaryLabelPool)];
    $toolThirdLabel = $toolThirdLabelPool[($seed + 19) % count($toolThirdLabelPool)];
    $toolActionText = $toolActionPool[($seed + 3) % count($toolActionPool)];

    $allDefinitionPosts = get_posts([
        "post_type" => "definicja",
        "post_status" => "publish",
        "numberposts" => 200,
        "orderby" => "title",
        "order" => "ASC",
    ]);
    $definitionInternalLinks = [];
    foreach ($allDefinitionPosts as $definitionPost) {
        if ((int) $definitionPost->ID === (int) $postId) {
            continue;
        }
        $definitionInternalLinks[] = [
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
    $cityInternalLinks = [];
    if (!empty($cityPosts)) {
        foreach ($cityPosts as $cityPostId) {
            $cityInternalLinks[] = [
                "name" => get_post_meta($cityPostId, "_upsellio_city_name", true) ?: get_the_title($cityPostId),
                "url" => get_permalink($cityPostId),
            ];
        }
    } else {
        foreach (upsellio_get_cities_dataset() as $city) {
            $cityInternalLinks[] = [
                "name" => $city["name"],
                "url" => home_url("/miasto/" . $city["slug"] . "/"),
            ];
        }
    }

    $selectedDefinitionLinks = [];
    $seenDefinitionLinks = [];
    $definitionCount = count($definitionInternalLinks);
    for ($i = 0; $i < min(12, $definitionCount); $i++) {
        $idx = ($seed + $i * 7) % $definitionCount;
        $urlKey = $definitionInternalLinks[$idx]["url"];
        if (isset($seenDefinitionLinks[$urlKey])) {
            continue;
        }
        $seenDefinitionLinks[$urlKey] = true;
        $selectedDefinitionLinks[] = $definitionInternalLinks[$idx];
    }

    $selectedCityLinks = [];
    $seenCityLinks = [];
    $cityCount = count($cityInternalLinks);
    for ($i = 0; $i < min(6, $cityCount); $i++) {
        $idx = ($seed + $i * 9) % $cityCount;
        $urlKey = $cityInternalLinks[$idx]["url"];
        if (isset($seenCityLinks[$urlKey])) {
            continue;
        }
        $seenCityLinks[$urlKey] = true;
        $selectedCityLinks[] = $cityInternalLinks[$idx];
    }

    $insertAfterParagraphs = [2 + ($seed % 2), 4 + ($seed % 3), 6 + ($seed % 2)];
    $paragraphCounter = 0;
    $inlineCounter = 0;
    $articleHtml = preg_replace_callback(
        "/<\/p>/i",
        function ($matches) use (&$paragraphCounter, &$inlineCounter, $insertAfterParagraphs, $selectedDefinitionLinks, $selectedCityLinks, $term) {
            $paragraphCounter++;
            if (!in_array($paragraphCounter, $insertAfterParagraphs, true)) {
                return $matches[0];
            }
            $definitionLink = $selectedDefinitionLinks[$inlineCounter] ?? null;
            $cityLink = $selectedCityLinks[$inlineCounter] ?? null;
            $inlineCounter++;
            $linksHtml = "";
            if (is_array($definitionLink) && !empty($definitionLink["url"])) {
                $linksHtml .= '<a href="' . esc_url($definitionLink["url"]) . '">Powiązana definicja: ' . esc_html($definitionLink["name"]) . "</a>";
            }
            if (is_array($cityLink) && !empty($cityLink["url"])) {
                $linksHtml .= '<a href="' . esc_url($cityLink["url"]) . '">Usługi lokalne: ' . esc_html("Marketing i strony WWW " . $cityLink["name"]) . "</a>";
            }
            return $matches[0] .
                '<aside class="definition-inline-cta">' .
                    '<strong>Wdróż ' . esc_html($term) . ' praktycznie, nie tylko teoretycznie.</strong>' .
                    '<div class="definition-inline-links">' . $linksHtml . "</div>" .
                    '<a class="definition-inline-btn" href="' . esc_url(home_url("/kontakt/")) . '">Umów bezpłatną rozmowę</a>' .
                "</aside>";
        },
        $articleHtml
    );
    add_action("wp_head", static function () use ($postId, $term, $archive_definitions_url, $faq) {
        $description = get_the_excerpt($postId);
        if ($description === "") {
            $description = wp_trim_words(wp_strip_all_tags((string) get_post_field("post_content", $postId)), 35, "");
        }
        $schema_payloads = [
            [
                "@context" => "https://schema.org",
                "@type" => "DefinedTerm",
                "name" => $term,
                "description" => $description,
                "url" => get_permalink($postId),
                "inDefinedTermSet" => $archive_definitions_url !== "" ? $archive_definitions_url : home_url("/definicje/"),
            ],
            [
                "@context" => "https://schema.org",
                "@type" => "BreadcrumbList",
                "itemListElement" => [
                    ["@type" => "ListItem", "position" => 1, "name" => "Strona glowna", "item" => home_url("/")],
                    ["@type" => "ListItem", "position" => 2, "name" => "Definicje", "item" => $archive_definitions_url !== "" ? $archive_definitions_url : home_url("/definicje/")],
                    ["@type" => "ListItem", "position" => 3, "name" => $term, "item" => get_permalink($postId)],
                ],
            ],
        ];
        $faq_entities = [];
        if (is_array($faq)) {
            foreach ($faq as $faq_item) {
                $question = trim((string) ($faq_item["question"] ?? ""));
                $answer = trim((string) ($faq_item["answer"] ?? ""));
                if ($question === "" || $answer === "") {
                    continue;
                }
                $faq_entities[] = [
                    "@type" => "Question",
                    "name" => $question,
                    "acceptedAnswer" => ["@type" => "Answer", "text" => $answer],
                ];
            }
        }
        if (!empty($faq_entities)) {
            $schema_payloads[] = [
                "@context" => "https://schema.org",
                "@type" => "FAQPage",
                "mainEntity" => $faq_entities,
            ];
        }
        foreach ($schema_payloads as $schema_payload) {
            echo '<script type="application/ld+json">' . wp_json_encode($schema_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
        }
    }, 2);
    ?>
    <style>
      .definition-progress{position:fixed;top:0;left:0;width:0;height:3px;background:linear-gradient(90deg,#0d9488,#14b8a6);z-index:90;transition:width .12s linear;will-change:width}
      .definition-wrap{width:min(1140px,calc(100% - 32px));margin:0 auto}
      .definition-hero{position:relative;overflow:hidden;padding:72px 0 34px;border-bottom:1px solid #e2e8f0;background:radial-gradient(circle at top right, rgba(20,184,166,0.14), transparent 36%), linear-gradient(180deg,#ecfeff,#f1f5f9)}
      .definition-hero-grid{display:grid;gap:30px;align-items:center}
      .definition-hero-copy{min-width:0}
      .definition-hero-diagram{display:none}
      .definition-hero-diagram svg{width:100%;height:auto;display:block;border-radius:22px;background:#fff;border:1px solid #e2e8f0;padding:18px;box-shadow:0 14px 40px rgba(15,23,42,.08)}
      @media(min-width:981px){.definition-hero-grid{grid-template-columns:1.2fr .8fr}.definition-hero-diagram{display:block}}
      .definition-breadcrumbs{font-size:12px;color:#6f6f67;margin-bottom:14px}
      .definition-title{font-family:Syne,sans-serif;font-size:clamp(34px,5vw,56px);line-height:1.05;letter-spacing:-1px}
      .definition-lead{margin-top:14px;max-width:860px;font-size:18px;line-height:1.75;color:#334155}
      .definition-pills{display:flex;flex-wrap:wrap;gap:10px;margin-top:20px}
      .definition-pill{font-size:12px;border:1px solid #c9c9c3;border-radius:999px;background:#fff;padding:7px 12px}
      .definition-main{padding:46px 0 60px;display:grid;grid-template-columns:1fr;gap:34px}
      .definition-content{line-height:1.8;color:#262624}
      .definition-content h2,.definition-content h3{font-family:Syne,sans-serif;color:#071426;line-height:1.2}
      .definition-content h2{font-size:33px;margin:0 0 14px}
      .definition-content h3{font-size:22px;margin:24px 0 8px}
      .definition-content p{margin:0 0 14px}
      .definition-content ul{margin:0 0 16px 20px}
      .definition-content li{margin:0 0 8px}
      .definition-content a{color:#0d9488}
      .definition-content a:hover{text-decoration:underline}
      .definition-inline-cta{margin:16px 0 20px;padding:15px;border:1px solid #99f6e4;background:#ecfeff;border-radius:12px}
      .definition-inline-cta strong{display:block;color:#0d4637;font-size:15px;line-height:1.5;margin-bottom:8px}
      .definition-inline-links{display:flex;flex-wrap:wrap;gap:9px;margin-bottom:10px}
      .definition-inline-links a{font-size:12px;color:#145f49}
      .definition-inline-btn{display:inline-flex;align-items:center;justify-content:center;background:#0d9488;color:#fff;padding:8px 12px;border-radius:999px;font-size:12px;font-weight:700}
      .definition-tool{margin:26px 0 0;padding:20px;border:1px solid #e6e6e1;border-radius:14px;background:#fff}
      .definition-tool h2{font-size:28px;margin:0 0 8px}
      .definition-tool p{margin:0 0 12px;color:#334155}
      .definition-tool-grid{display:grid;gap:12px}
      .definition-tool-grid label{display:grid;gap:6px;font-size:13px;color:#2f2f2a}
      .definition-tool-grid input{width:100%;border:1px solid #c9c9c3;border-radius:10px;padding:10px 12px;font:inherit}
      .definition-tool-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}
      .definition-tool-btn{border:none;border-radius:10px;padding:10px 14px;font:inherit;font-size:13px;font-weight:700;cursor:pointer}
      .definition-tool-btn.primary{background:#0d9488;color:#fff}
      .definition-tool-btn.ghost{background:#f3f3ef;color:#4c4c46}
      .definition-tool-result{margin-top:12px;padding:14px;border-radius:12px;background:#f1f5f9;border:1px solid #e2e8f0;display:none}
      .definition-tool-result.show{display:block}
      .definition-tool-score{font-size:30px;font-family:Syne,sans-serif;line-height:1}
      .definition-tool-note{margin-top:6px;font-size:13px;color:#3f3f38}
      .definition-contact{margin-top:26px;padding:20px;border:1px solid #e6e6e1;border-radius:14px;background:#fff}
      .definition-contact h2{font-size:26px;margin:0 0 8px}
      .definition-contact p{margin:0 0 12px;color:#334155}
      .definition-contact-links{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:12px}
      .definition-contact-links a{font-size:13px;font-weight:600}
      .definition-contact-form{display:grid;gap:12px}
      .definition-contact-row{display:grid;gap:12px;grid-template-columns:1fr}
      .definition-contact label{display:grid;gap:6px;font-size:13px;color:#2f2f2a}
      .definition-contact input,.definition-contact textarea{width:100%;border:1px solid #c9c9c3;border-radius:10px;padding:10px 12px;font:inherit}
      .definition-contact textarea{min-height:110px;resize:vertical}
      .definition-consent{display:flex;gap:8px;align-items:flex-start;font-size:12px;color:#56564f}
      .definition-consent input{margin-top:4px}
      .definition-linking{margin-top:28px;padding-top:22px;border-top:1px solid #e6e6e1}
      .definition-linking h2{font-size:24px;margin:0 0 10px}
      .definition-link-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px 14px}
      .definition-link-grid a{font-size:13px;color:#5f5f58}
      .definition-link-grid a:hover{color:#0d9488}
      .definition-side{position:static;display:grid;gap:16px;height:max-content}
      .definition-card{border:1px solid #e6e6e1;border-radius:14px;background:#fff;padding:18px}
      .definition-card-title{font-family:Syne,sans-serif;font-size:22px;margin-bottom:10px}
      .definition-list{display:grid;gap:8px}
      .definition-list a{font-size:14px;color:#5f5f58}
      .definition-list a:hover{color:#0d9488}
      .definition-phone-box{border:1px solid #99f6e4;background:#ecfeff;border-radius:12px;padding:12px;font-size:13px;color:#0f766e}
      .definition-phone-box a{font-weight:700}
      .definition-adjacent{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:26px;padding-top:18px;border-top:1px solid #e6e6e1}
      .definition-adjacent a{display:block;border:1px solid #e2e8f0;border-radius:12px;padding:12px;min-width:220px;color:#071426}
      .definition-adjacent small{display:block;font-size:12px;color:#6f6f67;margin-bottom:6px}
      .definition-faq{margin-top:28px;padding-top:24px;border-top:1px solid #e6e6e1}
      .definition-faq-item + .definition-faq-item{margin-top:14px}
      @media(min-width:781px){.definition-contact-row{grid-template-columns:1fr 1fr}.definition-link-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
      @media(min-width:981px){.definition-wrap{width:min(1140px,calc(100% - 40px))}.definition-main{grid-template-columns:minmax(0,1fr) 320px}.definition-side{position:sticky}}
    </style>

    <div class="definition-progress" id="definition-progress" aria-hidden="true"></div>
    <section class="definition-hero">
      <div class="definition-wrap">
        <div class="definition-hero-grid">
          <div class="definition-hero-copy">
            <div class="definition-breadcrumbs">
              <a href="<?php echo esc_url(home_url("/")); ?>">Strona główna</a> /
              <a href="<?php echo esc_url(home_url("/definicje/")); ?>">Definicje</a> /
              <span><?php echo esc_html($term); ?></span>
            </div>
            <h1 class="definition-title"><?php echo esc_html($term); ?></h1>
            <p class="definition-lead">
              Wyjaśnienie pojęcia <?php echo esc_html($term); ?> wraz z praktycznym zastosowaniem w SEO, kampaniach reklamowych i optymalizacji konwersji.
            </p>
            <div class="definition-pills">
              <span class="definition-pill">Kategoria: <?php echo esc_html($category); ?></span>
              <span class="definition-pill">Poziom: <?php echo esc_html($difficulty); ?></span>
              <?php if ($mainKeyword) : ?>
                <span class="definition-pill">Fraza: <?php echo esc_html($mainKeyword); ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="definition-hero-diagram" aria-hidden="true">
            <?php if ($diagramType === "seo") : ?>
              <svg viewBox="0 0 220 160" xmlns="http://www.w3.org/2000/svg">
                <rect x="14" y="14" width="192" height="22" rx="6" fill="#f1f5f9"/>
                <circle cx="28" cy="25" r="6" fill="none" stroke="#0d9488" stroke-width="2"/>
                <line x1="32" y1="29" x2="38" y2="35" stroke="#0d9488" stroke-width="2" stroke-linecap="round"/>
                <text x="48" y="29" font-family="Syne,sans-serif" font-size="11" fill="#475569">jak zoptymalizować...</text>
                <rect x="14" y="46" width="192" height="14" rx="4" fill="#ecfeff"/>
                <text x="20" y="56" font-size="10" fill="#0f766e" font-weight="700">1.</text>
                <text x="32" y="56" font-size="10" fill="#0f766e">Wynik #1: idealny tytuł, meta i intent</text>
                <rect x="14" y="64" width="192" height="14" rx="4" fill="#f8fafc"/>
                <text x="20" y="74" font-size="10" fill="#475569">2.</text>
                <text x="32" y="74" font-size="10" fill="#475569">Wynik #2 - poprawna struktura H</text>
                <rect x="14" y="82" width="192" height="14" rx="4" fill="#f8fafc"/>
                <text x="20" y="92" font-size="10" fill="#475569">3.</text>
                <text x="32" y="92" font-size="10" fill="#475569">Wynik #3 - mocne linkowanie</text>
                <g transform="translate(14,108)">
                  <rect x="0" y="20" width="22" height="20" fill="#99f6e4"/>
                  <rect x="28" y="10" width="22" height="30" fill="#5eead4"/>
                  <rect x="56" y="0" width="22" height="40" fill="#0d9488"/>
                  <rect x="84" y="14" width="22" height="26" fill="#5eead4"/>
                  <rect x="112" y="6" width="22" height="34" fill="#14b8a6"/>
                  <rect x="140" y="22" width="22" height="18" fill="#99f6e4"/>
                  <text x="170" y="34" font-size="10" font-weight="700" fill="#0f766e">SERP</text>
                </g>
              </svg>
            <?php elseif ($diagramType === "konwersja") : ?>
              <svg viewBox="0 0 220 160" xmlns="http://www.w3.org/2000/svg">
                <text x="14" y="22" font-family="Syne,sans-serif" font-size="11" font-weight="800" fill="#0f766e">LEJEK KONWERSJI</text>
                <path d="M14 36 L206 36 L160 84 L160 130 L60 130 L60 84 Z" fill="#ecfeff" stroke="#99f6e4"/>
                <text x="110" y="58" text-anchor="middle" font-size="11" font-weight="700" fill="#081827">Odwiedzający</text>
                <text x="110" y="73" text-anchor="middle" font-size="9" fill="#64748b">100%</text>
                <text x="110" y="100" text-anchor="middle" font-size="11" font-weight="700" fill="#081827">Zaangażowani</text>
                <text x="110" y="113" text-anchor="middle" font-size="9" fill="#64748b">~30%</text>
                <text x="110" y="123" text-anchor="middle" font-size="11" font-weight="800" fill="#0f766e">Lead</text>
                <rect x="80" y="138" width="60" height="14" rx="6" fill="#0d9488"/>
                <text x="110" y="148" text-anchor="middle" font-size="9" font-weight="700" fill="#fff">Klient</text>
              </svg>
            <?php elseif ($diagramType === "analityka") : ?>
              <svg viewBox="0 0 220 160" xmlns="http://www.w3.org/2000/svg">
                <text x="14" y="22" font-family="Syne,sans-serif" font-size="11" font-weight="800" fill="#0f766e">DASHBOARD ANALITYKI</text>
                <line x1="14" y1="130" x2="206" y2="130" stroke="#cbd5e1"/>
                <line x1="14" y1="40" x2="14" y2="130" stroke="#cbd5e1"/>
                <polyline points="14,110 50,90 86,98 122,72 158,80 194,52" fill="none" stroke="#0d9488" stroke-width="2"/>
                <circle cx="14" cy="110" r="3" fill="#0d9488"/>
                <circle cx="50" cy="90" r="3" fill="#0d9488"/>
                <circle cx="86" cy="98" r="3" fill="#0d9488"/>
                <circle cx="122" cy="72" r="3" fill="#0d9488"/>
                <circle cx="158" cy="80" r="3" fill="#0d9488"/>
                <circle cx="194" cy="52" r="4" fill="#0f766e"/>
                <text x="14" y="148" font-size="9" fill="#64748b">Pn</text>
                <text x="50" y="148" font-size="9" fill="#64748b">Wt</text>
                <text x="86" y="148" font-size="9" fill="#64748b">Śr</text>
                <text x="122" y="148" font-size="9" fill="#64748b">Cz</text>
                <text x="158" y="148" font-size="9" fill="#64748b">Pt</text>
                <text x="194" y="148" font-size="9" font-weight="700" fill="#0f766e">Sb</text>
              </svg>
            <?php elseif ($diagramType === "reklamy") : ?>
              <svg viewBox="0 0 220 160" xmlns="http://www.w3.org/2000/svg">
                <text x="14" y="22" font-family="Syne,sans-serif" font-size="11" font-weight="800" fill="#0f766e">KAMPANIA → STRONA → LEAD</text>
                <rect x="14" y="36" width="56" height="44" rx="8" fill="#ecfeff" stroke="#99f6e4"/>
                <text x="42" y="56" text-anchor="middle" font-size="10" font-weight="700" fill="#0f766e">Reklama</text>
                <text x="42" y="68" text-anchor="middle" font-size="9" fill="#475569">CTR</text>
                <path d="M70 58 H82" stroke="#94a3b8" stroke-width="2" marker-end="url(#defm)"/>
                <rect x="82" y="36" width="56" height="44" rx="8" fill="#fff" stroke="#cbd5e1"/>
                <text x="110" y="56" text-anchor="middle" font-size="10" font-weight="700" fill="#081827">Strona</text>
                <text x="110" y="68" text-anchor="middle" font-size="9" fill="#475569">CR</text>
                <path d="M138 58 H150" stroke="#94a3b8" stroke-width="2" marker-end="url(#defm)"/>
                <rect x="150" y="36" width="56" height="44" rx="8" fill="#0f766e"/>
                <text x="178" y="56" text-anchor="middle" font-size="10" font-weight="700" fill="#fff">Lead</text>
                <text x="178" y="68" text-anchor="middle" font-size="9" fill="#a7f3d0">CPL</text>
                <defs><marker id="defm" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="6" markerHeight="6" orient="auto"><path d="M0,0 L10,5 L0,10 z" fill="#94a3b8"/></marker></defs>
                <text x="110" y="110" text-anchor="middle" font-size="10" fill="#64748b">Mierzymy każdy etap, optymalizujemy najsłabszy.</text>
              </svg>
            <?php else : ?>
              <svg viewBox="0 0 220 160" xmlns="http://www.w3.org/2000/svg">
                <text x="14" y="22" font-family="Syne,sans-serif" font-size="11" font-weight="800" fill="#0f766e">SYSTEM MARKETINGU</text>
                <circle cx="55" cy="80" r="32" fill="#ecfeff" stroke="#99f6e4"/>
                <text x="55" y="78" text-anchor="middle" font-size="10" font-weight="700" fill="#0f766e">Ruch</text>
                <text x="55" y="92" text-anchor="middle" font-size="9" fill="#475569">Ads · SEO</text>
                <circle cx="110" cy="80" r="32" fill="#fff" stroke="#cbd5e1"/>
                <text x="110" y="78" text-anchor="middle" font-size="10" font-weight="700" fill="#081827">Strona</text>
                <text x="110" y="92" text-anchor="middle" font-size="9" fill="#475569">UX · CRO</text>
                <circle cx="165" cy="80" r="32" fill="#0f766e"/>
                <text x="165" y="78" text-anchor="middle" font-size="10" font-weight="700" fill="#fff">Lead</text>
                <text x="165" y="92" text-anchor="middle" font-size="9" fill="#a7f3d0">Sprzedaż</text>
                <text x="110" y="138" text-anchor="middle" font-size="10" fill="#64748b">Trzy elementy, jeden spójny wynik.</text>
              </svg>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>

    <section class="definition-main definition-wrap">
      <article class="definition-content">
        <?php echo $articleHtml; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

        <section class="definition-tool" id="narzedzie-definicji">
          <h2><?php echo esc_html($toolTitle); ?></h2>
          <p><?php echo esc_html($toolIntro); ?></p>
          <div class="definition-tool-grid">
            <label>
              <?php echo esc_html($toolPrimaryLabel); ?>
              <input type="number" min="0" step="1" data-tool-input="a" />
            </label>
            <label>
              <?php echo esc_html($toolSecondaryLabel); ?>
              <input type="number" min="0" step="0.1" data-tool-input="b" />
            </label>
            <label>
              <?php echo esc_html($toolThirdLabel); ?>
              <input type="number" min="0" step="0.1" data-tool-input="c" />
            </label>
          </div>
          <div class="definition-tool-actions">
            <button type="button" class="definition-tool-btn primary" data-tool-action="calculate">Policz wynik</button>
            <button type="button" class="definition-tool-btn ghost" data-tool-action="reset">Wyczysc</button>
          </div>
          <div class="definition-tool-result" data-tool-result>
            <div class="definition-tool-score" data-tool-score>0</div>
            <div class="definition-tool-note">
              <strong><?php echo esc_html($toolScoreLabel); ?>:</strong>
              <span data-tool-message></span>
            </div>
            <div class="definition-tool-note"><?php echo esc_html($toolActionText); ?></div>
          </div>
        </section>

        <section class="definition-contact" id="formularz-definicji">
          <h2>Formularz kontaktowy</h2>
          <p>Chcesz wdrożyć <?php echo esc_html($term); ?> w praktyce? Opisz krótko sytuację, przygotuję rekomendacje.</p>
          <div class="definition-contact-links">
            <a href="<?php echo esc_url("tel:" . $contactPhoneHref); ?>">Telefon: <?php echo esc_html($contactPhone); ?></a>
            <a href="<?php echo esc_url($contactEmailHref); ?>">E-mail: <?php echo esc_html($contactEmailDisplay); ?></a>
          </div>
          <form class="definition-contact-form" method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" data-upsellio-lead-form="1" data-upsellio-server-form="1">
            <input type="hidden" name="action" value="upsellio_submit_lead" />
            <input type="hidden" name="redirect_url" value="<?php echo esc_url(get_permalink($postId)); ?>" />
            <input type="hidden" name="lead_form_origin" value="definicja-single" />
            <input type="hidden" name="lead_source" value="definicja-single" />
            <input type="hidden" name="utm_source" data-ups-utm="source" value="" />
            <input type="hidden" name="utm_medium" data-ups-utm="medium" value="" />
            <input type="hidden" name="utm_campaign" data-ups-utm="campaign" value="" />
            <input type="hidden" name="landing_url" data-ups-context="landing" value="" />
            <input type="hidden" name="referrer" data-ups-context="referrer" value="" />
            <input type="hidden" name="lead_service" value="<?php echo esc_attr("Definicja: " . $term); ?>" />
            <input type="text" name="lead_website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;" />
            <?php wp_nonce_field("upsellio_unified_lead_form", "upsellio_lead_form_nonce"); ?>
            <div class="definition-contact-row">
              <label>
                Imię i firma *
                <input type="text" name="lead_name" required />
              </label>
              <label>
                E-mail *
                <input type="email" name="lead_email" required />
              </label>
            </div>
            <div class="definition-contact-row">
              <label>
                Telefon
                <input type="tel" name="lead_phone" />
              </label>
              <label>
                Co chcesz poprawić? *
                <textarea name="lead_message" required>Chcę wdrożyć definicję <?php echo esc_textarea($term); ?> w praktyce.</textarea>
              </label>
            </div>
            <label class="definition-consent">
              <input type="checkbox" name="lead_consent" value="1" required />
              <span>Wyrażam zgodę na kontakt w sprawie mojego zapytania.</span>
            </label>
            <button class="definition-tool-btn primary" type="submit">Wyślij formularz</button>
          </form>
        </section>

        <section class="definition-linking" aria-label="Linkowanie wewnętrzne definicji">
          <h2>Powiązane tematy i strony lokalne</h2>
          <div class="definition-link-grid">
            <?php foreach ($selectedDefinitionLinks as $definitionLink) : ?>
              <a href="<?php echo esc_url($definitionLink["url"]); ?>">
                <?php echo esc_html("Definicja: " . $definitionLink["name"]); ?>
              </a>
            <?php endforeach; ?>
            <?php foreach ($selectedCityLinks as $cityLink) : ?>
              <a href="<?php echo esc_url($cityLink["url"]); ?>">
                <?php echo esc_html("Marketing i strony WWW " . $cityLink["name"]); ?>
              </a>
            <?php endforeach; ?>
          </div>
        </section>

        <?php if (!empty($faq)) : ?>
          <div class="definition-faq">
            <h2>Dodatkowe FAQ</h2>
            <?php foreach ($faq as $item) : ?>
              <div class="definition-faq-item">
                <h3><?php echo esc_html($item["q"]); ?></h3>
                <p><?php echo esc_html($item["a"]); ?></p>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div class="definition-adjacent">
          <?php if (!empty($adjacent["prev"])) : ?>
            <a href="<?php echo esc_url($adjacent["prev"]["url"]); ?>">
              <small>Poprzednia definicja</small>
              <?php echo esc_html($adjacent["prev"]["name"]); ?>
            </a>
          <?php endif; ?>
          <?php if (!empty($adjacent["next"])) : ?>
            <a href="<?php echo esc_url($adjacent["next"]["url"]); ?>">
              <small>Następna definicja</small>
              <?php echo esc_html($adjacent["next"]["name"]); ?>
            </a>
          <?php endif; ?>
        </div>
      </article>

      <aside class="definition-side">
        <div class="definition-card">
          <div class="definition-card-title">Powiązane definicje</div>
          <div class="definition-list">
            <?php foreach ($related as $relatedItem) : ?>
              <a href="<?php echo esc_url($relatedItem["url"]); ?>"><?php echo esc_html($relatedItem["name"]); ?></a>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="definition-card">
          <div class="definition-card-title">Dalsze kroki</div>
          <div class="definition-list">
            <?php foreach ($serviceLinks as $relative) :
                $url = home_url($relative);
                $label = $relative === "/#kontakt" ? "Umów rozmowę" : ($relative === "/#uslugi" ? "Zobacz usługi" : "Sprawdź miasta obsługi");
                ?>
              <a href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a>
            <?php endforeach; ?>
            <a href="<?php echo esc_url(home_url("/definicje/")); ?>">Powrót do wszystkich definicji</a>
          </div>
        </div>

        <div class="definition-phone-box">
          Potrzebujesz szybkiej konsultacji? Zadzwoń:
          <a href="<?php echo esc_url("tel:" . $contactPhoneHref); ?>"><?php echo esc_html($contactPhone); ?></a>
        </div>
      </aside>
    </section>
    <script>
      (function () {
        var bar = document.getElementById("definition-progress");
        if (bar) {
          var article = document.querySelector(".definition-content");
          var update = function () {
            if (!article) return;
            var rect = article.getBoundingClientRect();
            var total = rect.height - window.innerHeight;
            if (total <= 0) { bar.style.width = "100%"; return; }
            var passed = Math.min(Math.max(-rect.top, 0), total);
            bar.style.width = ((passed / total) * 100) + "%";
          };
          window.addEventListener("scroll", update, { passive: true });
          window.addEventListener("resize", update);
          update();
        }
      })();
      (function () {
        var tool = document.getElementById("narzedzie-definicji");
        if (!tool) return;
        var inputA = tool.querySelector('[data-tool-input="a"]');
        var inputB = tool.querySelector('[data-tool-input="b"]');
        var inputC = tool.querySelector('[data-tool-input="c"]');
        var result = tool.querySelector("[data-tool-result]");
        var scoreEl = tool.querySelector("[data-tool-score]");
        var messageEl = tool.querySelector("[data-tool-message]");
        var calcBtn = tool.querySelector('[data-tool-action="calculate"]');
        var resetBtn = tool.querySelector('[data-tool-action="reset"]');
        if (!inputA || !inputB || !inputC || !result || !scoreEl || !messageEl || !calcBtn || !resetBtn) return;

        function normalize(value) {
          var n = parseFloat(value);
          if (!isFinite(n) || n < 0) return 0;
          return n;
        }

        function getMessage(score) {
          if (score < 35) return "Niski wynik. Najpierw uporzadkuj fundament: komunikat, pomiar i jasne CTA.";
          if (score < 70) return "Sredni wynik. Potencjal jest, ale potrzebna jest regularna optymalizacja.";
          return "Wysoki wynik. Możesz skalować to, co działa i poprawiać jakość leadów.";
        }

        calcBtn.addEventListener("click", function () {
          var a = normalize(inputA.value);
          var b = normalize(inputB.value);
          var c = normalize(inputC.value);
          var base = Math.min(100, Math.round((Math.log10(a + 10) * 18) + (b * 1.2) + (c * 1.4)));
          var score = Math.max(0, Math.min(100, base));
          scoreEl.textContent = score + "/100";
          messageEl.textContent = getMessage(score);
          result.classList.add("show");
        });

        resetBtn.addEventListener("click", function () {
          inputA.value = "";
          inputB.value = "";
          inputC.value = "";
          result.classList.remove("show");
          scoreEl.textContent = "0";
          messageEl.textContent = "";
        });
      })();
    </script>
    <?php
endwhile;

get_footer();

