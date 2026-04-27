<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_register_city_post_type()
{
    register_post_type("miasto", [
        "labels" => [
            "name" => "Miasta",
            "singular_name" => "Miasto",
            "add_new_item" => "Dodaj podstrone miasta",
            "edit_item" => "Edytuj podstrone miasta",
        ],
        "public" => true,
        "has_archive" => "miasta",
        "rewrite" => [
            "slug" => "miasto",
            "with_front" => false,
        ],
        "menu_icon" => "dashicons-location-alt",
        "supports" => ["title", "editor", "excerpt", "thumbnail", "custom-fields"],
        "show_in_rest" => true,
        "publicly_queryable" => true,
        "exclude_from_search" => false,
    ]);
}
add_action("init", "upsellio_register_city_post_type");

function upsellio_city_document_title($parts)
{
    if (!is_singular("miasto")) {
        return $parts;
    }

    $city = get_post_meta(get_the_ID(), "_upsellio_city_name", true);
    $parts["title"] = "Marketing i strony WWW " . $city;
    $parts["tagline"] = "Upsellio";

    return $parts;
}
add_filter("document_title_parts", "upsellio_city_document_title");

function upsellio_city_meta_tags()
{
    if (!is_singular("miasto")) {
        return;
    }

    $postId = get_the_ID();
    $citySlug = get_post_meta($postId, "_upsellio_city_slug", true) ?: get_post_field("post_name", $postId);
    $cityDatasetItem = upsellio_get_city_by_slug($citySlug);
    $city = is_array($cityDatasetItem) && !empty($cityDatasetItem["name"])
        ? $cityDatasetItem["name"]
        : get_post_meta($postId, "_upsellio_city_name", true);
    $description = get_post_meta($postId, "_upsellio_city_meta_description", true);
    $url = get_permalink($postId);

    if (!$description) {
        $description = "Pozyskuj więcej klientów w mieście " . $city . " dzięki kampaniom Meta i Google Ads oraz stronie WWW nastawionej na konwersję.";
    }

    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta name="robots" content="index,follow,max-snippet:-1,max-image-preview:large,max-video-preview:-1">' . "\n";
    echo '<link rel="canonical" href="' . esc_url($url) . '">' . "\n";
    echo '<meta property="og:type" content="article">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr("Marketing i strony WWW " . $city . " | Upsellio") . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";

    $schema = [
        "@context" => "https://schema.org",
        "@type" => "ProfessionalService",
        "name" => "Upsellio",
        "areaServed" => [
            "@type" => "City",
            "name" => $city,
        ],
        "url" => $url,
        "description" => $description,
    ];
    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";

    $breadcrumbs = [
        "@context" => "https://schema.org",
        "@type" => "BreadcrumbList",
        "itemListElement" => [
            [
                "@type" => "ListItem",
                "position" => 1,
                "name" => "Strona główna",
                "item" => home_url("/"),
            ],
            [
                "@type" => "ListItem",
                "position" => 2,
                "name" => "Miasta",
                "item" => home_url("/miasta/"),
            ],
            [
                "@type" => "ListItem",
                "position" => 3,
                "name" => "Marketing i strony WWW " . $city,
                "item" => $url,
            ],
        ],
    ];
    echo '<script type="application/ld+json">' . wp_json_encode($breadcrumbs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
}
add_action("wp_head", "upsellio_city_meta_tags", 5);

function upsellio_get_city_nearby_links($currentSlug, $limit = 6)
{
    $cities = upsellio_get_cities_dataset();
    $currentIndex = 0;

    foreach ($cities as $index => $city) {
        if ($city["slug"] === $currentSlug) {
            $currentIndex = $index;
            break;
        }
    }

    $links = [];
    for ($i = 1; $i <= $limit; $i++) {
        $nextIndex = ($currentIndex + $i) % count($cities);
        $nextCity = $cities[$nextIndex];
        $links[] = [
            "name" => $nextCity["name"],
            "url" => home_url("/miasto/" . $nextCity["slug"] . "/"),
        ];
    }

    return $links;
}

function upsellio_get_footer_city_links_html()
{
    $posts = get_posts([
        "post_type" => "miasto",
        "post_status" => "publish",
        "numberposts" => 200,
        "orderby" => "title",
        "order" => "ASC",
        "fields" => "ids",
    ]);

    if (empty($posts)) {
        $cityLinks = array_map(function ($city) {
            return [
                "name" => $city["name"],
                "url" => home_url("/miasto/" . $city["slug"] . "/"),
            ];
        }, upsellio_get_cities_dataset());
    } else {
        $cityLinks = array_map(function ($postId) {
            $citySlug = get_post_meta($postId, "_upsellio_city_slug", true) ?: get_post_field("post_name", $postId);
            $cityDatasetItem = upsellio_get_city_by_slug($citySlug);
            return [
                "name" => is_array($cityDatasetItem) && !empty($cityDatasetItem["name"])
                    ? $cityDatasetItem["name"]
                    : (get_post_meta($postId, "_upsellio_city_name", true) ?: get_the_title($postId)),
                "url" => get_permalink($postId),
            ];
        }, $posts);
    }

    $previewLinks = array_slice($cityLinks, 0, 32);
    $hiddenLinks = array_slice($cityLinks, 32);
    $componentId = "upsellio-local-seo-" . wp_generate_password(6, false, false);

    ob_start();
    ?>
    <section class="upsellio-local-seo" aria-label="Miasta obsługi" id="<?php echo esc_attr($componentId); ?>">
      <style>
        .upsellio-local-seo{margin-top:32px;padding-top:24px;border-top:1px solid var(--border,#e6e6e1)}
        .upsellio-local-seo-head{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:14px}
        .upsellio-local-seo-title{font-size:13px;font-weight:700;letter-spacing:.3px;color:var(--text-2,#334155)}
        .upsellio-local-seo-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px 18px}
        .upsellio-local-seo-link{font-size:13px;line-height:1.5;color:var(--text-3,#64748b);display:inline-block;text-decoration:none}
        .upsellio-local-seo-link:hover{color:var(--teal,#0d9488)}
        .upsellio-local-seo-more{overflow:hidden;max-height:0;opacity:0;transition:max-height .45s ease, opacity .25s ease}
        .upsellio-local-seo-more.is-open{opacity:1;max-height:2200px;margin-top:14px}
        .upsellio-local-seo-toggle{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border:1px solid var(--border-strong,#cbd5e1);border-radius:999px;background:transparent;color:var(--text-2,#334155);cursor:pointer;font-size:12px}
        .upsellio-local-seo-toggle:hover{border-color:var(--teal,#0d9488);color:var(--teal,#0d9488)}
        @media(min-width:861px){.upsellio-local-seo-grid{grid-template-columns:repeat(4,minmax(0,1fr))}}
      </style>
      <div class="upsellio-local-seo-head">
        <div class="upsellio-local-seo-title">Usługi w największych miastach Polski</div>
        <?php if (!empty($hiddenLinks)) : ?>
          <button class="upsellio-local-seo-toggle" type="button" data-role="toggle">Pokaż wszystkie miasta</button>
        <?php endif; ?>
      </div>
      <div class="upsellio-local-seo-grid">
        <?php foreach ($previewLinks as $item) : ?>
          <a class="upsellio-local-seo-link" href="<?php echo esc_url($item["url"]); ?>">
            <?php echo esc_html("Marketing i strony WWW " . $item["name"]); ?>
          </a>
        <?php endforeach; ?>
      </div>
      <?php if (!empty($hiddenLinks)) : ?>
        <div class="upsellio-local-seo-more" data-role="hidden-list">
          <div class="upsellio-local-seo-grid">
            <?php foreach ($hiddenLinks as $item) : ?>
              <a class="upsellio-local-seo-link" href="<?php echo esc_url($item["url"]); ?>">
                <?php echo esc_html("Marketing i strony WWW " . $item["name"]); ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
        <script>
          (function () {
            var root = document.getElementById('<?php echo esc_js($componentId); ?>');
            if (!root) return;
            var btn = root.querySelector('[data-role="toggle"]');
            var hidden = root.querySelector('[data-role="hidden-list"]');
            if (!btn || !hidden) return;
            btn.addEventListener('click', function () {
              var open = hidden.classList.toggle('is-open');
              btn.textContent = open ? 'Ukryj listę miast' : 'Pokaż wszystkie miasta';
            });
          })();
        </script>
      <?php endif; ?>
    </section>
    <?php

    return ob_get_clean();
}

function upsellio_get_footer_popular_definitions_html()
{
    $popularDefinitions = get_posts([
        "post_type" => "definicja",
        "post_status" => "publish",
        "numberposts" => 12,
        "orderby" => "date",
        "order" => "DESC",
    ]);

    if (empty($popularDefinitions)) {
        return "";
    }

    ob_start();
    ?>
    <section style="margin-top:28px;padding-top:24px;border-top:1px solid #e6e6e1;">
      <h3 style="margin:0 0 12px;font-family:Syne,sans-serif;font-size:18px;color:#071426;">Popularne definicje</h3>
      <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px 16px;">
        <?php foreach ($popularDefinitions as $definition) :
            $term = get_post_meta($definition->ID, "_upsellio_definition_term", true) ?: get_the_title($definition->ID);
            ?>
          <a href="<?php echo esc_url(get_permalink($definition->ID)); ?>" style="font-size:13px;line-height:1.5;color:#64748b;">
            <?php echo esc_html($term); ?>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
    <style>
      @media(min-width:861px){
        footer [style*="grid-template-columns:repeat(2,minmax(0,1fr));"]{grid-template-columns:repeat(4,minmax(0,1fr)) !important}
      }
    </style>
    <?php

    return ob_get_clean();
}

function upsellio_generate_city_content($city, $position)
{
    $name = $city["name"];
    $voivodeship = $city["voivodeship"];
    $marketAngle = $city["market_angle"];
    $serviceFocus = $city["service_focus"];
    $challenge = $city["local_challenge"] ?? "niska jakość leadów";
    $advantage = $city["local_advantage"] ?? "stabilny popyt lokalny";
    $seasonality = $city["seasonality_angle"] ?? "stabilny popyt";
    $seed = abs(crc32($city["slug"] . "|" . $position));

    $leadPartsA = [
        "W mieście %s najwięcej firm przepala budżet na niespójne kampanie i stronę, która nie domyka zapytania.",
        "Dla firm działających w %s kluczowe jest połączenie precyzyjnego targetowania z komunikatem sprzedażowym.",
        "W %s wspieram firmy, które chcą przewidywalnie skalować leady bez podbijania kosztu pozyskania.",
        "W regionie %s łączymy marketing i stronę WWW tak, aby ruch zamieniał się w konkretne rozmowy handlowe.",
        "Firmy z %s najczęściej tracą wynik na etapie przejścia z reklamy do oferty - to miejsce optymalizujemy w pierwszej kolejności.",
        "Model dla %s opieram na danych i intencji klienta, a nie na samych metrykach platform reklamowych.",
        "W %s budujemy lejki, które skracają dystans między kliknięciem a wartościowym zapytaniem.",
        "Działania dla %s projektuję tak, by marketing wspierał rzeczywistą skuteczność sprzedaży, nie tylko ruch.",
    ];
    $leadPartsB = [
        "Największym wyzwaniem jest tutaj %s, ale przewaga to równocześnie %s.",
        "Lokalna specyfika to %s, a mocna strona rynku to %s.",
        "Na tym rynku widać %s, jednak firmy mogą wykorzystać %s.",
        "Najczęstszy punkt blokady: %s. Potencjał wzrostu: %s.",
    ];
    $lead = sprintf(
        $leadPartsA[$seed % count($leadPartsA)] . " " . $leadPartsB[$seed % count($leadPartsB)],
        $name,
        $challenge,
        $advantage
    );

    $marketParagraphs = [
        "Dla rynku %s przygotowujemy scenariusze reklamowe pod intencję zakupową i etap decyzji klienta.",
        "W %s kluczowe jest odróżnienie leadów przypadkowych od zapytań realnie gotowych do rozmowy handlowej.",
        "Województwo %s ma specyficzną dynamikę popytu, dlatego strategia sezonowa to: %s.",
        "Przy tym profilu rynku (%s) kampanie i strona muszą działać jako jeden system, inaczej wynik szybko spada.",
        "W tym modelu (%s) monitorujemy koszt pozyskania, jakość leadów i konwersję na kolejne etapy procesu.",
        "Na rynku %s wykorzystujemy testy kreacji i testy ofertowe równolegle, żeby szybciej znaleźć najskuteczniejszą kombinację.",
    ];
    $marketSection = sprintf($marketParagraphs[$seed % count($marketParagraphs)], $name, $name, $voivodeship, $seasonality, $marketAngle, $serviceFocus, $name);

    $serviceBulletsPool = [
        "Audyt kont reklamowych i strony pod konwersję dla " . $name,
        "Plan 90 dni: kampanie, landing page i pomiar leadów",
        "Meta Ads i Google Ads z optymalizacją pod jakość zapytań",
        "Strona WWW / landing page z jasną architekturą decyzji zakupowej",
        "Tagowanie i analityka pod realne KPI sprzedażowe",
        "Raporty tygodniowe z rekomendacjami kolejnych testów",
        "Synchronizacja marketingu z procesem handlowym zespołu",
        "Iteracyjna optymalizacja kosztu pozyskania i konwersji",
    ];
    $serviceBullets = [];
    for ($i = 0; $i < 5; $i++) {
        $serviceBullets[] = $serviceBulletsPool[($seed + $i * 3) % count($serviceBulletsPool)];
    }

    $faqPool = [
        ["q" => "Czy obsługujesz firmy z miasta %s zdalnie czy lokalnie?", "a" => "Tak. Pracujemy zdalnie i lokalnie, zależnie od potrzeb. Najważniejszy jest rytm wdrożeń i regularna optymalizacja."],
        ["q" => "Ile trwa start kampanii dla firmy z %s?", "a" => "Zwykle 7-21 dni: audyt, plan testów, wdrożenie, pomiar i pierwsza iteracja optymalizacji."],
        ["q" => "Czy można zacząć tylko od strony WWW dla %s?", "a" => "Tak. Stronę budujemy tak, aby była gotowa do późniejszego skalowania kampanii i SEO lokalnego."],
        ["q" => "Jak mierzysz jakość leadów w %s?", "a" => "Łączymy dane z formularzy, CRM i etapu handlowego. Patrzymy nie tylko na liczbę leadów, ale też ich wartość."],
        ["q" => "Czy wspierasz firmy B2B działające w %s?", "a" => "Tak, to jeden z głównych obszarów. Układamy komunikację, targetowanie i lejek pod dłuższy proces decyzji."],
        ["q" => "Jak szybko można zobaczyć pierwsze efekty w %s?", "a" => "Pierwsze sygnały zwykle pojawiają się po kilku tygodniach, a stabilna optymalizacja najczęściej po 6-12 tygodniach."],
        ["q" => "Czy prowadzisz stałe testy reklam dla rynku %s?", "a" => "Tak. Testujemy kreacje, grupy odbiorców i oferty. Decyzje opieramy na danych, nie na domysłach."],
        ["q" => "Czy obsługujesz też kampanie remarketingowe dla %s?", "a" => "Tak, remarketing jest stałym elementem strategii, zwłaszcza przy dłuższym cyklu zakupowym."],
        ["q" => "Czy można połączyć kampanie i przebudowę strony w %s?", "a" => "Tak, to częsty scenariusz. Dzięki temu ruch i konwersja są projektowane jako jeden system."],
        ["q" => "Jak wygląda raportowanie dla firm z %s?", "a" => "Raportujemy KPI biznesowe: koszt pozyskania, jakość leadów, konwersję i rekomendacje kolejnych kroków."],
        ["q" => "Czy to rozwiązanie sprawdzi się przy mniejszym budżecie w %s?", "a" => "Tak, zaczynamy od priorytetów o najwyższym potencjale zwrotu i stopniowo skalujemy działania."],
        ["q" => "Czy wspierasz tylko pozyskanie leadów, czy też sprzedaż po stronie firmy z %s?", "a" => "Wsparcie obejmuje także proces handlowy: jakość zapytań, etapy lejka i przekazywanie leadów."],
    ];
    $faq = [];
    for ($i = 0; $i < 4; $i++) {
        $idx = ($seed + $i * 5) % count($faqPool);
        $faq[] = [
            "q" => sprintf($faqPool[$idx]["q"], $name),
            "a" => $faqPool[$idx]["a"],
        ];
    }

    $ctaVariants = [
        "Umów bezpłatną konsultację dla %s i dostań plan działań pod Twój budżet.",
        "Sprawdźmy, jak poprawić skuteczność marketingu i strony WWW w %s w ciągu najbliższych 90 dni.",
        "Chcesz więcej wartościowych zapytań z %s? Zacznijmy od audytu i konkretnej mapy wdrożeń.",
        "Umów rozmowę i zobacz, co na rynku %s da najszybszy zwrot z inwestycji.",
        "Dla miasta %s przygotuję plan: kampanie, strona i proces leadowy pod realny wynik.",
    ];
    $cta = sprintf($ctaVariants[$seed % count($ctaVariants)], $name);

    $metaDescription = "Marketing i strony WWW " . $name . ": kampanie Meta i Google Ads, SEO lokalne, landing pages i optymalizacja konwersji dla firm z woj. " . $voivodeship . ".";

    $cityProfile = [
        "intensity_index" => (int) (60 + ($seed % 39)),
        "competition_index" => (int) (55 + (($seed >> 3) % 43)),
        "conversion_window_days" => (int) (7 + (($seed >> 4) % 19)),
        "priority_channel" => ["Meta Ads", "Google Ads", "SEO lokalne", "Landing + PPC"][$seed % 4],
    ];

    $content = '<h2>Marketing i strony WWW ' . esc_html($name) . '</h2>';
    $content .= '<p>' . esc_html($lead) . '</p>';
    $content .= '<h3>Specyfika lokalnego rynku</h3>';
    $content .= '<p>' . esc_html($marketSection) . '</p>';
    $content .= '<p>W "profilu rynku ' . esc_html($name) . '" priorytet to: ' . esc_html($cityProfile["priority_channel"]) . ', okno konwersji: ' . esc_html((string) $cityProfile["conversion_window_days"]) . ' dni.</p>';
    $content .= '<h3>Zakres współpracy dla ' . esc_html($name) . '</h3><ul>';
    foreach ($serviceBullets as $bullet) {
        $content .= '<li>' . esc_html($bullet) . '</li>';
    }
    $content .= '</ul>';
    $content .= '<h3>FAQ lokalne</h3>';
    foreach ($faq as $item) {
        $content .= '<p><strong>' . esc_html($item["q"]) . '</strong><br>' . esc_html($item["a"]) . '</p>';
    }
    $content .= '<h3>Plan 90 dni</h3>';
    $content .= '<p>Intensywność działań: ' . esc_html((string) $cityProfile["intensity_index"]) . '/100. Poziom konkurencji: ' . esc_html((string) $cityProfile["competition_index"]) . '/100. Sezonowość: ' . esc_html($seasonality) . '.</p>';
    $content .= '<p><strong>' . esc_html($cta) . '</strong></p>';

    $fingerprint = md5($city["slug"] . "|" . $lead . "|" . $marketSection . "|" . implode("|", $serviceBullets) . "|" . implode("|", wp_list_pluck($faq, "q")) . "|" . $cta);

    return [
        "title" => "Marketing i strony WWW " . $name,
        "excerpt" => $lead,
        "meta_description" => $metaDescription,
        "content" => $content,
        "faq" => $faq,
        "cta" => $cta,
        "fingerprint" => $fingerprint,
    ];
}

function upsellio_validate_city_uniqueness_map($fingerprints)
{
    $duplicates = 0;
    foreach (array_count_values($fingerprints) as $count) {
        if ($count > 1) {
            $duplicates += ($count - 1);
        }
    }

    return $duplicates;
}

function upsellio_seed_city_pages($force = false)
{
    $alreadySeeded = get_option("upsellio_cities_seeded_v1");
    if ($alreadySeeded && !$force) {
        return ["created" => 0, "updated" => 0, "skipped" => 200, "message" => "already_seeded"];
    }

    $fingerprints = [];
    $collisionCount = 0;
    $created = 0;
    $updated = 0;

    foreach (upsellio_get_cities_dataset() as $index => $city) {
        $generated = upsellio_generate_city_content($city, $index);
        if (isset($fingerprints[$generated["fingerprint"]])) {
            $collisionCount++;
            $generated["content"] .= "<p>Kod lokalizacji: " . esc_html(strtoupper(substr(md5($city["slug"]), 0, 10))) . ".</p>";
            $generated["fingerprint"] = md5($generated["fingerprint"] . "|" . $city["slug"] . "|" . $index);
        }
        $fingerprints[] = $generated["fingerprint"];

        $existing = get_page_by_path($city["slug"], OBJECT, "miasto");

        $postData = [
            "post_type" => "miasto",
            "post_status" => "publish",
            "post_title" => $generated["title"],
            "post_name" => $city["slug"],
            "post_excerpt" => $generated["excerpt"],
            "post_content" => $generated["content"],
        ];

        if ($existing) {
            $postData["ID"] = $existing->ID;
            $postId = wp_update_post($postData, true);
            if (!is_wp_error($postId)) {
                $updated++;
            }
        } else {
            $postId = wp_insert_post($postData, true);
            if (!is_wp_error($postId)) {
                $created++;
            }
        }

        if (is_wp_error($postId)) {
            continue;
        }

        update_post_meta($postId, "_upsellio_city_name", $city["name"]);
        update_post_meta($postId, "_upsellio_city_slug", $city["slug"]);
        update_post_meta($postId, "_upsellio_city_voivodeship", $city["voivodeship"]);
        update_post_meta($postId, "_upsellio_city_market_angle", $city["market_angle"]);
        update_post_meta($postId, "_upsellio_city_service_focus", $city["service_focus"]);
        update_post_meta($postId, "_upsellio_city_local_challenge", $city["local_challenge"]);
        update_post_meta($postId, "_upsellio_city_local_advantage", $city["local_advantage"]);
        update_post_meta($postId, "_upsellio_city_seasonality_angle", $city["seasonality_angle"]);
        update_post_meta($postId, "_upsellio_city_meta_description", $generated["meta_description"]);
        update_post_meta($postId, "_upsellio_city_faq", $generated["faq"]);
        update_post_meta($postId, "_upsellio_city_cta", $generated["cta"]);
        update_post_meta($postId, "_upsellio_city_fingerprint", $generated["fingerprint"]);
    }

    update_option("upsellio_cities_seeded_v1", current_time("mysql"));

    $duplicateFingerprints = upsellio_validate_city_uniqueness_map($fingerprints);

    return [
        "created" => $created,
        "updated" => $updated,
        "skipped" => 0,
        "message" => "ok",
        "runtime_collisions" => $collisionCount,
        "duplicate_fingerprints" => $duplicateFingerprints,
    ];
}

function upsellio_seed_cities_if_requested()
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }

    if (!isset($_GET["upsellio_seed_cities"])) {
        return;
    }

    $nonce = isset($_GET["_upsellio_nonce"]) ? sanitize_text_field(wp_unslash($_GET["_upsellio_nonce"])) : "";
    if (!wp_verify_nonce($nonce, "upsellio_seed_cities")) {
        return;
    }

    $force = !empty($_GET["force"]);
    $result = upsellio_seed_city_pages($force);

    $redirectUrl = add_query_arg([
        "upsellio_seed_done" => 1,
        "created" => (int) $result["created"],
        "updated" => (int) $result["updated"],
        "runtime_collisions" => (int) ($result["runtime_collisions"] ?? 0),
        "duplicate_fingerprints" => (int) ($result["duplicate_fingerprints"] ?? 0),
        "msg" => $result["message"],
    ], admin_url("edit.php?post_type=miasto"));
    wp_safe_redirect($redirectUrl);
    exit;
}
add_action("admin_init", "upsellio_seed_cities_if_requested");

function upsellio_seed_cities_admin_notice()
{
    if (!is_admin() || !isset($_GET["upsellio_seed_done"])) {
        return;
    }

    $created = isset($_GET["created"]) ? (int) $_GET["created"] : 0;
    $updated = isset($_GET["updated"]) ? (int) $_GET["updated"] : 0;
    $runtimeCollisions = isset($_GET["runtime_collisions"]) ? (int) $_GET["runtime_collisions"] : 0;
    $duplicates = isset($_GET["duplicate_fingerprints"]) ? (int) $_GET["duplicate_fingerprints"] : 0;
    $msg = isset($_GET["msg"]) ? sanitize_text_field(wp_unslash($_GET["msg"])) : "ok";

    echo '<div class="notice notice-success"><p>';
    if ($msg === "already_seeded") {
        echo esc_html("Generator miast byl juz uruchomiony. Dodaj parametr force=1, aby nadpisac.");
    } else {
        echo esc_html("Wygenerowano podstrony miast: utworzono {$created}, zaktualizowano {$updated}. Kolizje runtime: {$runtimeCollisions}. Duplikaty fingerprintow po generacji: {$duplicates}.");
    }
    echo "</p></div>";
}
add_action("admin_notices", "upsellio_seed_cities_admin_notice");

function upsellio_get_seed_url($force = false)
{
    $params = [
        "upsellio_seed_cities" => 1,
        "_upsellio_nonce" => wp_create_nonce("upsellio_seed_cities"),
    ];

    if ($force) {
        $params["force"] = 1;
    }

    return add_query_arg($params, admin_url("edit.php?post_type=miasto"));
}

