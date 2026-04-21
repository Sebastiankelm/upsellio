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
    $city = get_post_meta($postId, "_upsellio_city_name", true);
    $description = get_post_meta($postId, "_upsellio_city_meta_description", true);
    $url = get_permalink($postId);

    if (!$description) {
        $description = "Pozyskuj wiecej klientow w miescie " . $city . " dzieki kampaniom Meta i Google Ads oraz stronie WWW nastawionej na konwersje.";
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
                "name" => "Strona glowna",
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
            return [
                "name" => get_post_meta($postId, "_upsellio_city_name", true) ?: get_the_title($postId),
                "url" => get_permalink($postId),
            ];
        }, $posts);
    }

    $previewLinks = array_slice($cityLinks, 0, 32);
    $hiddenLinks = array_slice($cityLinks, 32);
    $componentId = "upsellio-local-seo-" . wp_generate_password(6, false, false);

    ob_start();
    ?>
    <section class="upsellio-local-seo" aria-label="Miasta obslugi" id="<?php echo esc_attr($componentId); ?>">
      <style>
        .upsellio-local-seo{margin-top:32px;padding-top:24px;border-top:1px solid var(--border,#e6e6e1)}
        .upsellio-local-seo-head{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:14px}
        .upsellio-local-seo-title{font-size:13px;font-weight:700;letter-spacing:.3px;color:var(--text-2,#3d3d38)}
        .upsellio-local-seo-count{font-size:12px;color:var(--text-3,#7c7c74)}
        .upsellio-local-seo-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px 18px}
        .upsellio-local-seo-link{font-size:13px;line-height:1.5;color:var(--text-3,#7c7c74);display:inline-block;text-decoration:none}
        .upsellio-local-seo-link:hover{color:var(--teal,#1d9e75)}
        .upsellio-local-seo-more{overflow:hidden;max-height:0;opacity:0;transition:max-height .45s ease, opacity .25s ease}
        .upsellio-local-seo-more.is-open{opacity:1;max-height:2200px;margin-top:14px}
        .upsellio-local-seo-toggle{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border:1px solid var(--border-strong,#c9c9c3);border-radius:999px;background:transparent;color:var(--text-2,#3d3d38);cursor:pointer;font-size:12px}
        .upsellio-local-seo-toggle:hover{border-color:var(--teal,#1d9e75);color:var(--teal,#1d9e75)}
        @media(min-width:861px){.upsellio-local-seo-grid{grid-template-columns:repeat(4,minmax(0,1fr))}}
      </style>
      <div class="upsellio-local-seo-head">
        <div>
          <div class="upsellio-local-seo-title">Uslugi w najwiekszych miastach Polski</div>
          <div class="upsellio-local-seo-count"><?php echo esc_html(count($cityLinks)); ?> lokalizacji</div>
        </div>
        <?php if (!empty($hiddenLinks)) : ?>
          <button class="upsellio-local-seo-toggle" type="button" data-role="toggle">Pokaz wszystkie miasta</button>
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
              btn.textContent = open ? 'Ukryj liste miast' : 'Pokaz wszystkie miasta';
            });
          })();
        </script>
      <?php endif; ?>
    </section>
    <?php

    return ob_get_clean();
}

function upsellio_generate_city_content($city, $position)
{
    $name = $city["name"];
    $voivodeship = $city["voivodeship"];
    $marketAngle = $city["market_angle"];
    $serviceFocus = $city["service_focus"];
    $challenge = $city["local_challenge"] ?? "niska jakosc leadow";
    $advantage = $city["local_advantage"] ?? "stabilny popyt lokalny";
    $seasonality = $city["seasonality_angle"] ?? "stabilny popyt";
    $seed = abs(crc32($city["slug"] . "|" . $position));

    $leadPartsA = [
        "W miescie %s najwiecej firm przepala budzet na niespojne kampanie i strone, ktora nie domyka zapytania.",
        "Dla firm dzialajacych w %s kluczowe jest polaczenie precyzyjnego targetowania z komunikatem sprzedazowym.",
        "W %s wspieram firmy, ktore chca przewidywalnie skalowac leady bez podbijania kosztu pozyskania.",
        "W regionie %s laczymy marketing i strone WWW tak, aby ruch zamienial sie w konkretne rozmowy handlowe.",
        "Firmy z %s najczesciej traca wynik na etapie przejscia z reklamy do oferty - to miejsce optymalizujemy w pierwszej kolejnosci.",
        "Model dla %s opieram na danych i intencji klienta, a nie na samych metrykach platform reklamowych.",
        "W %s budujemy lejki, ktore skracaja dystans miedzy kliknieciem a wartosciowym zapytaniem.",
        "Dzialania dla %s projektuje tak, by marketing wspieral rzeczywista skutecznosc sprzedazy, nie tylko ruch.",
    ];
    $leadPartsB = [
        "Najwiekszym wyzwaniem jest tutaj %s, ale przewaga to rownoczesnie %s.",
        "Lokalna specyfika to %s, a mocna strona rynku to %s.",
        "Na tym rynku widac %s, jednak firmy moga wykorzystac %s.",
        "Najczestszy punkt blokady: %s. Potencjal wzrostu: %s.",
    ];
    $lead = sprintf(
        $leadPartsA[$seed % count($leadPartsA)] . " " . $leadPartsB[$seed % count($leadPartsB)],
        $name,
        $challenge,
        $advantage
    );

    $marketParagraphs = [
        "Dla rynku %s przygotowujemy scenariusze reklamowe pod intencje zakupowa i etap decyzji klienta.",
        "W %s kluczowe jest odroznienie leadow przypadkowych od zapytan realnie gotowych do rozmowy handlowej.",
        "Wojewodztwo %s ma specyficzna dynamike popytu, dlatego strategia sezonowa to: %s.",
        "Przy tym profilu rynku (%s) kampanie i strona musza dzialac jako jeden system, inaczej wynik szybko spada.",
        "W tym modelu (%s) monitorujemy koszt pozyskania, jakosc leadow i konwersje na kolejne etapy procesu.",
        "Na rynku %s wykorzystujemy testy kreacji i testy ofertowe rownolegle, zeby szybciej znalezc najskuteczniejsza kombinacje.",
    ];
    $marketSection = sprintf($marketParagraphs[$seed % count($marketParagraphs)], $name, $name, $voivodeship, $seasonality, $marketAngle, $serviceFocus, $name);

    $serviceBulletsPool = [
        "Audyt kont reklamowych i strony pod konwersje dla " . $name,
        "Plan 90 dni: kampanie, landing page i pomiar leadow",
        "Meta Ads i Google Ads z optymalizacja pod jakosc zapytan",
        "Strona WWW / landing page z jasna architektura decyzji zakupowej",
        "Tagowanie i analityka pod realne KPI sprzedazowe",
        "Raporty tygodniowe z rekomendacjami kolejnych testow",
        "Synchronizacja marketingu z procesem handlowym zespolu",
        "Iteracyjna optymalizacja kosztu pozyskania i konwersji",
    ];
    $serviceBullets = [];
    for ($i = 0; $i < 5; $i++) {
        $serviceBullets[] = $serviceBulletsPool[($seed + $i * 3) % count($serviceBulletsPool)];
    }

    $faqPool = [
        ["q" => "Czy obslugujesz firmy z miasta %s zdalnie czy lokalnie?", "a" => "Tak. Pracujemy zdalnie i lokalnie, zalezenie od potrzeb. Najwazniejszy jest rytm wdrozen i regularna optymalizacja."],
        ["q" => "Ile trwa start kampanii dla firmy z %s?", "a" => "Zwykle 7-21 dni: audyt, plan testow, wdrozenie, pomiar i pierwsza iteracja optymalizacji."],
        ["q" => "Czy mozna zaczac tylko od strony WWW dla %s?", "a" => "Tak. Strone budujemy tak, aby byla gotowa do pozniejszego skalowania kampanii i SEO lokalnego."],
        ["q" => "Jak mierzysz jakosc leadow w %s?", "a" => "Laczymy dane z formularzy, CRM i etapu handlowego. Patrzymy nie tylko na liczbe leadow, ale tez ich wartosc."],
        ["q" => "Czy wspierasz firmy B2B dzialajace w %s?", "a" => "Tak, to jeden z glownych obszarow. Ukladamy komunikacje, targetowanie i lejek pod dluzszy proces decyzji."],
        ["q" => "Jak szybko mozna zobaczyc pierwsze efekty w %s?", "a" => "Pierwsze sygnaly zwykle pojawiaja sie po kilku tygodniach, a stabilna optymalizacja najczesciej po 6-12 tygodniach."],
        ["q" => "Czy prowadzisz stale testy reklam dla rynku %s?", "a" => "Tak. Testujemy kreacje, grupy odbiorcow i oferty. Decyzje opieramy na danych, nie na domyslach."],
        ["q" => "Czy obslugujesz tez kampanie remarketingowe dla %s?", "a" => "Tak, remarketing jest stalym elementem strategii, zwlaszcza przy dluzszym cyklu zakupowym."],
        ["q" => "Czy mozna polaczyc kampanie i przebudowe strony w %s?", "a" => "Tak, to czesty scenariusz. Dzieki temu ruch i konwersja sa projektowane jako jeden system."],
        ["q" => "Jak wyglada raportowanie dla firm z %s?", "a" => "Raportujemy KPI biznesowe: koszt pozyskania, jakosc leadow, konwersje i rekomendacje kolejnych krokow."],
        ["q" => "Czy to rozwiazanie sprawdzi sie przy mniejszym budzecie w %s?", "a" => "Tak, zaczynamy od priorytetow o najwyzszym potencjale zwrotu i stopniowo skalujemy dzialania."],
        ["q" => "Czy wspierasz tylko pozyskanie leadow, czy tez sprzedaz po stronie firmy z %s?", "a" => "Wsparcie obejmuje takze proces handlowy: jakosc zapytan, etapy lejka i przekazywanie leadow."],
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
        "Umow bezplatna konsultacje dla %s i dostan plan dzialan pod Twoj budzet.",
        "Sprawdzmy, jak poprawic skutecznosc marketingu i strony WWW w %s w ciagu najblizszych 90 dni.",
        "Chcesz wiecej wartosciowych zapytan z %s? Zacznijmy od audytu i konkretnej mapy wdrozen.",
        "Umow rozmowe i zobacz, co na rynku %s da najszybszy zwrot z inwestycji.",
        "Dla miasta %s przygotuje plan: kampanie, strona i proces leadowy pod realny wynik.",
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
    $content .= '<h3>Zakres wspolpracy dla ' . esc_html($name) . '</h3><ul>';
    foreach ($serviceBullets as $bullet) {
        $content .= '<li>' . esc_html($bullet) . '</li>';
    }
    $content .= '</ul>';
    $content .= '<h3>FAQ lokalne</h3>';
    foreach ($faq as $item) {
        $content .= '<p><strong>' . esc_html($item["q"]) . '</strong><br>' . esc_html($item["a"]) . '</p>';
    }
    $content .= '<h3>Plan 90 dni</h3>';
    $content .= '<p>Intensywnosc dzialan: ' . esc_html((string) $cityProfile["intensity_index"]) . '/100. Poziom konkurencji: ' . esc_html((string) $cityProfile["competition_index"]) . '/100. Sezonowosc: ' . esc_html($seasonality) . '.</p>';
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

