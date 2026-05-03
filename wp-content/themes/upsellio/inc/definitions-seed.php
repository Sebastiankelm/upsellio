<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_register_definition_post_type()
{
    register_post_type("definicja", [
        "labels" => [
            "name" => "Definicje",
            "singular_name" => "Definicja",
            "add_new_item" => "Dodaj definicje",
            "edit_item" => "Edytuj definicje",
        ],
        "public" => true,
        "has_archive" => "definicje",
        "rewrite" => [
            "slug" => "definicje",
            "with_front" => false,
        ],
        "menu_icon" => "dashicons-book-alt",
        "supports" => ["title", "editor", "excerpt", "thumbnail", "custom-fields"],
        "show_in_rest" => true,
        "publicly_queryable" => true,
        "exclude_from_search" => false,
    ]);
}
add_action("init", "upsellio_register_definition_post_type");

function upsellio_get_definition_dataset_item($slug)
{
    foreach (upsellio_get_definitions_dataset() as $item) {
        if ($item["slug"] === $slug) {
            return $item;
        }
    }

    return null;
}

function upsellio_get_definition_permalink_by_slug($slug)
{
    $post = get_page_by_path($slug, OBJECT, "definicja");
    if ($post) {
        return get_permalink($post->ID);
    }

    return home_url("/definicje/" . $slug . "/");
}

function upsellio_get_definition_related_slugs($slug, $limit = 6)
{
    $dataset = upsellio_get_definitions_dataset();
    $current = upsellio_get_definition_dataset_item($slug);
    if (!$current) {
        return [];
    }

    $related = [];
    if (!empty($current["related_slugs"])) {
        foreach ($current["related_slugs"] as $candidate) {
            if ($candidate === $slug) {
                continue;
            }
            $related[] = $candidate;
            if (count($related) >= $limit) {
                return array_values(array_unique($related));
            }
        }
    }

    foreach ($dataset as $item) {
        if ($item["slug"] === $slug) {
            continue;
        }
        if ($item["category"] === $current["category"]) {
            $related[] = $item["slug"];
        }
        if (count(array_unique($related)) >= $limit) {
            break;
        }
    }

    return array_slice(array_values(array_unique($related)), 0, $limit);
}

function upsellio_get_definition_related_links($slug, $limit = 6)
{
    $related = upsellio_get_definition_related_slugs($slug, $limit);
    $links = [];
    foreach ($related as $relatedSlug) {
        $item = upsellio_get_definition_dataset_item($relatedSlug);
        if (!$item) {
            continue;
        }
        $links[] = [
            "slug" => $relatedSlug,
            "name" => $item["term"],
            "url" => upsellio_get_definition_permalink_by_slug($relatedSlug),
        ];
    }

    return $links;
}

function upsellio_get_definition_adjacent_links($slug)
{
    $dataset = upsellio_get_definitions_dataset();
    $slugs = array_map(function ($item) {
        return $item["slug"];
    }, $dataset);
    $index = array_search($slug, $slugs, true);
    if ($index === false) {
        return ["prev" => null, "next" => null];
    }

    $prevIndex = $index === 0 ? count($slugs) - 1 : $index - 1;
    $nextIndex = $index === count($slugs) - 1 ? 0 : $index + 1;

    $prevItem = upsellio_get_definition_dataset_item($slugs[$prevIndex]);
    $nextItem = upsellio_get_definition_dataset_item($slugs[$nextIndex]);

    return [
        "prev" => $prevItem ? [
            "name" => $prevItem["term"],
            "slug" => $prevItem["slug"],
            "url" => upsellio_get_definition_permalink_by_slug($prevItem["slug"]),
        ] : null,
        "next" => $nextItem ? [
            "name" => $nextItem["term"],
            "slug" => $nextItem["slug"],
            "url" => upsellio_get_definition_permalink_by_slug($nextItem["slug"]),
        ] : null,
    ];
}

function upsellio_definition_document_title($parts)
{
    if (!is_singular("definicja")) {
        return $parts;
    }

    $post_id = (int) get_the_ID();
    $seo_title = trim((string) get_post_meta($post_id, "rank_math_title", true));
    if ($seo_title === "") {
        $seo_title = trim((string) get_post_meta($post_id, "_yoast_wpseo_title", true));
    }
    if ($seo_title !== "") {
        $parts["title"] = $seo_title;
        unset($parts["tagline"]);

        return $parts;
    }

    if (function_exists("upsellio_is_seo_plugin_managing_frontend_meta") && upsellio_is_seo_plugin_managing_frontend_meta()) {
        return $parts;
    }

    $term = get_post_meta($post_id, "_upsellio_definition_term", true) ?: get_the_title();
    $parts["title"] = $term . " - definicja SEO i marketingu";
    $parts["tagline"] = "Upsellio";

    return $parts;
}
add_filter("document_title_parts", "upsellio_definition_document_title");

function upsellio_definition_meta_tags()
{
    if (!is_singular("definicja")) {
        return;
    }

    if (function_exists("upsellio_is_seo_plugin_managing_frontend_meta") && upsellio_is_seo_plugin_managing_frontend_meta()) {
        return;
    }

    $postId = get_the_ID();
    $term = get_post_meta($postId, "_upsellio_definition_term", true) ?: get_the_title($postId);
    $description = trim((string) get_post_meta($postId, "rank_math_description", true));
    if ($description === "") {
        $description = trim((string) get_post_meta($postId, "_yoast_wpseo_metadesc", true));
    }
    if ($description === "") {
        $description = (string) get_post_meta($postId, "_upsellio_definition_meta_description", true);
    }
    $url = get_permalink($postId);
    $faq = get_post_meta($postId, "_upsellio_definition_faq", true);
    if (!is_array($faq)) {
        $faq = [];
    }

    if ($description === "") {
        $description = "Sprawdz definicje terminu " . $term . " oraz praktyczne zastosowanie w SEO, kampaniach reklamowych i optymalizacji konwersji.";
    }

    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta name="robots" content="index,follow,max-snippet:-1,max-image-preview:large,max-video-preview:-1">' . "\n";
    echo '<link rel="canonical" href="' . esc_url($url) . '">' . "\n";
    echo '<meta property="og:type" content="article">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($term . " - definicja SEO i marketingu") . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";

    $breadcrumbs = [
        [
            "@type" => "ListItem",
            "position" => 1,
            "item" => [
                "@id" => home_url("/"),
                "name" => "Strona glowna",
            ],
        ],
        [
            "@type" => "ListItem",
            "position" => 2,
            "item" => [
                "@id" => home_url("/definicje/"),
                "name" => "Definicje",
            ],
        ],
        [
            "@type" => "ListItem",
            "position" => 3,
            "item" => [
                "@id" => $url,
                "name" => $term,
            ],
        ],
    ];

    $definedTermSchema = [
        "@context" => "https://schema.org",
        "@type" => "DefinedTerm",
        "name" => $term,
        "description" => wp_strip_all_tags($description),
        "url" => $url,
        "inDefinedTermSet" => home_url("/definicje/"),
    ];

    $webPageSchema = [
        "@context" => "https://schema.org",
        "@type" => "WebPage",
        "name" => $term . " - definicja",
        "url" => $url,
        "description" => wp_strip_all_tags($description),
    ];

    $breadcrumbSchema = [
        "@context" => "https://schema.org",
        "@type" => "BreadcrumbList",
        "itemListElement" => $breadcrumbs,
    ];

    echo '<script type="application/ld+json">' . wp_json_encode($definedTermSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
    echo '<script type="application/ld+json">' . wp_json_encode($webPageSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
    echo '<script type="application/ld+json">' . wp_json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";

    if (!empty($faq)) {
        $faqSchema = [
            "@context" => "https://schema.org",
            "@type" => "FAQPage",
            "mainEntity" => array_map(function ($item) {
                return [
                    "@type" => "Question",
                    "name" => $item["q"],
                    "acceptedAnswer" => [
                        "@type" => "Answer",
                        "text" => $item["a"],
                    ],
                ];
            }, $faq),
        ];
        echo '<script type="application/ld+json">' . wp_json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
    }
}
add_action("wp_head", "upsellio_definition_meta_tags", 5);

function upsellio_definition_build_service_links($serviceLinks)
{
    $links = [];
    foreach ((array) $serviceLinks as $relativeUrl) {
        $links[] = [
            "url" => home_url($relativeUrl),
            "label" => str_replace(["/#", "/"], ["", ""], trim($relativeUrl)) ?: "oferta",
        ];
    }

    return $links;
}

function upsellio_definition_pick_variant($options, $slug, $salt = "")
{
    $items = array_values((array) $options);
    if (empty($items)) {
        return "";
    }

    $hash = crc32($slug . "|" . $salt);
    $index = (int) ($hash % count($items));

    return $items[$index];
}

function upsellio_definition_category_context($category)
{
    $contexts = [
        "seo" => [
            "team" => "zespół contentowy i SEO",
            "goal" => "zwiększenie widoczności organicznej i wzrost ruchu z intencją zakupową",
            "risk" => "optymalizacja pod pojedynczą metrykę zamiast całości lejka",
        ],
        "sem" => [
            "team" => "zespół performance i handlowy",
            "goal" => "stabilna liczba jakościowych leadów przy kontrolowanym koszcie",
            "risk" => "przepalanie budżetu przez zbyt szerokie targetowanie",
        ],
        "analityka" => [
            "team" => "analityk i osoba odpowiedzialna za decyzje budżetowe",
            "goal" => "trafniejsze decyzje oparte na danych i lepsza rentowność kampanii",
            "risk" => "błędna interpretacja danych z powodu braku segmentacji",
        ],
        "seo-techniczne" => [
            "team" => "developer i SEO techniczne",
            "goal" => "szybsze indeksowanie, stabilna wydajność i lepsza jakość serwisu",
            "risk" => "wdrożenia bez testów, które pogarszają crawl i UX",
        ],
        "content" => [
            "team" => "content manager i copywriter",
            "goal" => "skalowalna produkcja treści wspierająca ruch i sprzedaż",
            "risk" => "duplikacja tematów i brak spójnej mapy treści",
        ],
        "ux" => [
            "team" => "projektant UX i właściciel produktu",
            "goal" => "wyższy współczynnik konwersji bez zwiększania kosztu ruchu",
            "risk" => "zmiany projektowe bez testów i walidacji hipotez",
        ],
        "strategia" => [
            "team" => "zarząd i marketing",
            "goal" => "spójna strategia, która łączy marketing z realnym procesem sprzedaży",
            "risk" => "brak priorytetów i rozproszenie działań między kanałami",
        ],
    ];

    return $contexts[$category] ?? [
        "team" => "zespół marketingu",
        "goal" => "poprawa skuteczności i przewidywalności działań",
        "risk" => "decyzje podejmowane bez kontekstu biznesowego",
    ];
}

function upsellio_definition_generate_content($definition, $position)
{
    $term = $definition["term"];
    $slug = $definition["slug"];
    $mainKeyword = $definition["main_keyword"];
    $secondaryKeywords = (array) $definition["secondary_keywords"];
    $category = $definition["category"];
    $difficulty = $definition["difficulty"];
    $intent = $definition["search_intent"];
    $relatedLinks = upsellio_get_definition_related_links($slug, 5);
    $serviceLinks = upsellio_definition_build_service_links($definition["service_links"]);
    $context = upsellio_definition_category_context($category);
    $secondaryAsText = implode(", ", array_slice($secondaryKeywords, 0, 3));

    $introTemplate = upsellio_definition_pick_variant([
        "Gdy zespół pyta o %s, najczęściej chodzi o to, jak ten termin przekłada się na decyzje budżetowe i wynik sprzedażowy.",
        "%s to nie tylko definicja słownikowa. To punkt kontrolny, który pozwala ocenić, czy marketing pracuje na realny cel firmy.",
        "W praktyce %s pojawia się tam, gdzie trzeba połączyć dane, treść i proces sprzedaży w jeden spójny system.",
        "Jeśli chcesz poprawić skuteczność działań online, zrozumienie %s jest jednym z pierwszych kroków.",
    ], $slug, "intro");
    $intro = sprintf($introTemplate, $term);

    $definitionTemplate = upsellio_definition_pick_variant([
        "%s to pojęcie opisujące mechanizm, który pomaga osiągnąć cel: %s.",
        "Najprościej: %s to sposób porządkowania decyzji w obszarze %s.",
        "W języku operacyjnym %s odpowiada za to, czy zespół utrzymuje kontrolę nad procesem i wynikiem.",
    ], $slug, "definition");
    $definitionParagraph = sprintf($definitionTemplate, $term, $context["goal"]);

    $howItWorksTemplate = upsellio_definition_pick_variant([
        "Aby %s działało poprawnie, %s powinien regularnie zestawiać dane operacyjne z celem biznesowym.",
        "Skuteczne wykorzystanie %s wymaga współpracy pomiędzy %s oraz jasnych zasad raportowania.",
        "%s daje najlepsze efekty, kiedy %s pracuje na tej samej definicji sukcesu i wspólnym słowniku metryk.",
    ], $slug, "how");
    $howItWorks = sprintf($howItWorksTemplate, $term, $context["team"]);

    $implementationTemplate = upsellio_definition_pick_variant([
        "Wdrożenie zacznij od audytu stanu obecnego, potem ustaw priorytety i wdrażaj poprawki w cyklach 2-tygodniowych.",
        "Najpierw porządkujesz pomiar, następnie hipotezy i testy, a na końcu standaryzujesz proces dla całego zespołu.",
        "Najlepiej sprawdza się podejście etapowe: diagnoza, szybkie poprawki, testy i kwartalny przegląd strategii.",
    ], $slug, "implementation");

    $mistakesTemplate = upsellio_definition_pick_variant([
        "Najczęstszy błąd to traktowanie %s jako celu samego w sobie. W efekcie zespół gubi kontekst rentowności i jakości leadów.",
        "Drugim częstym błędem jest brak segmentacji danych. Bez tego %s może prowadzić do mylnych wniosków.",
        "Firmy często wdrażają %s jednorazowo i nie wracają do optymalizacji, przez co efekt szybko wygasa.",
        "Ryzykiem bywa również brak wspólnej definicji terminu, co utrudnia współpracę między marketingiem i sprzedażą.",
    ], $slug, "mistakes");
    $mistakes = sprintf($mistakesTemplate, $term);

    $exampleTemplate = upsellio_definition_pick_variant([
        "Przykład: firma B2B analizuje %s co tydzień i łączy je z jakością zapytań. Po 6 tygodniach ogranicza koszt pozyskania bez spadku sprzedaży.",
        "Przykład redakcyjny: zespół wdraża %s w trzech kanałach, porównuje wyniki i przesuwa budżet do najbardziej rentownego segmentu.",
        "Przykład operacyjny: po wdrożeniu %s firma porządkuje lejki i szybciej identyfikuje, które kampanie generują tylko pozorny wynik.",
    ], $slug, "example");
    $exampleText = sprintf($exampleTemplate, $term);

    $faq = [
        [
            "q" => "Czy " . $term . " jest ważne przy małym budżecie?",
            "a" => "Tak. Przy małym budżecie " . $term . " pomaga szybciej ograniczyć koszt błędnych decyzji i lepiej ustawić priorytety.",
        ],
        [
            "q" => "Jak mierzyć postęp po wdrożeniu " . $term . "?",
            "a" => "Najlepiej łączyć metrykę główną z metrykami pomocniczymi: konwersja, koszt pozyskania, jakość leada i dynamika sprzedaży.",
        ],
        [
            "q" => "Z czym najczęściej łączy się " . $term . "?",
            "a" => "W praktyce z obszarami takimi jak pomiar, treść, struktura kampanii i proces handlowy po stronie firmy.",
        ],
    ];

    $relatedList = "";
    foreach ($relatedLinks as $related) {
        $relatedList .= '<li><a href="' . esc_url($related["url"]) . '">' . esc_html($related["name"]) . "</a></li>";
    }

    $serviceList = "";
    foreach ($serviceLinks as $service) {
        $label = strtoupper($service["label"]) === "USLUGI" ? "nasza oferta" : $service["label"];
        $serviceList .= '<li><a href="' . esc_url($service["url"]) . '">' . esc_html(ucfirst($label)) . "</a></li>";
    }

    $firstRelated = !empty($relatedLinks) ? $relatedLinks[0] : null;
    $secondRelated = count($relatedLinks) > 1 ? $relatedLinks[1] : null;
    $contextLine = "W praktyce " . $term . " warto polaczyc z ";
    if ($firstRelated) {
        $contextLine .= '<a href="' . esc_url($firstRelated["url"]) . '">' . esc_html($firstRelated["name"]) . "</a>";
    } else {
        $contextLine .= "planowaniem kampanii";
    }
    if ($secondRelated) {
        $contextLine .= " oraz " . '<a href="' . esc_url($secondRelated["url"]) . '">' . esc_html($secondRelated["name"]) . "</a>";
    }
    $contextLine .= ".";

    $difficultyLabel = ucfirst($difficulty);
    $intentLabel = ucfirst($intent);
    $readingTime = max(4, (int) ceil((strlen($term) + strlen($secondaryAsText)) / 18));

    $content = "";
    $content .= '<h2>Co to jest ' . esc_html($term) . "?</h2>";
    $content .= "<p>" . esc_html($intro) . "</p>";
    $content .= "<p>" . esc_html($definitionParagraph) . "</p>";
    $content .= '<p><strong>Fraza główna:</strong> ' . esc_html($mainKeyword) . ". ";
    $content .= '<strong>Frazy wspierające:</strong> ' . esc_html($secondaryAsText) . ". ";
    $content .= '<strong>Kategoria:</strong> ' . esc_html($category) . ". ";
    $content .= '<strong>Poziom trudności:</strong> ' . esc_html($difficultyLabel) . ". ";
    $content .= '<strong>Intencja:</strong> ' . esc_html($intentLabel) . ".</p>";

    $content .= '<h2>Jak działa ' . esc_html($term) . " w realnym projekcie?</h2>";
    $content .= "<p>" . esc_html($howItWorks) . "</p>";
    $content .= "<p>" . $contextLine . "</p>";
    $content .= "<p>" . esc_html($exampleText) . "</p>";

    $content .= '<h2>Jak wdrożyć ' . esc_html($term) . " krok po kroku?</h2>";
    $content .= "<p>" . esc_html($implementationTemplate) . "</p>";
    $content .= "<ul>";
    $content .= "<li>Ustal definicję sukcesu i KPI, które naprawdę wspierają " . esc_html($context["goal"]) . ".</li>";
    $content .= "<li>Zbieraj dane w jednym miejscu i opisuj zmiany, by nie tracić kontekstu decyzji.</li>";
    $content .= "<li>Pracuj iteracyjnie: test, pomiar, wniosek, poprawka.</li>";
    $content .= "<li>Raz w miesiącu sprawdź, czy " . esc_html($term) . " nadal wspiera cel biznesowy, a nie tylko wskaźnik.</li>";
    $content .= "</ul>";

    $content .= '<h2>Najczęstsze błędy przy pracy z ' . esc_html($term) . "</h2>";
    $content .= "<p>" . esc_html($mistakes) . "</p>";
    $content .= "<p><strong>Uwaga:</strong> głównym ryzykiem jest " . esc_html($context["risk"]) . ".</p>";

    $content .= "<h2>Powiazane definicje</h2>";
    $content .= "<ul>" . $relatedList . "</ul>";

    $content .= "<h2>Przydatne linki do dalszych działań</h2>";
    $content .= "<ul>" . $serviceList . "</ul>";

    $content .= "<h2>FAQ</h2>";
    foreach ($faq as $item) {
        $content .= "<p><strong>" . esc_html($item["q"]) . "</strong><br>" . esc_html($item["a"]) . "</p>";
    }

    $content .= '<p><strong>Chcesz wdrożyć ' . esc_html($term) . ' szybciej i w zgodzie z celem biznesowym? ';
    $content .= '<a href="' . esc_url(home_url("/#kontakt")) . '">Umów bezpłatną rozmowę i dostań plan działania</a>.</strong></p>';

    $metaDescription = $term . " - definicja, przykłady i praktyczne wdrożenie. Sprawdź, jak wykorzystać ten termin w SEO, reklamach i optymalizacji konwersji.";
    $fingerprint = md5($slug . "|" . $term . "|" . $mainKeyword . "|" . $position . "|" . $context["goal"]);

    return [
        "title" => $term . " - definicja",
        "excerpt" => wp_trim_words(wp_strip_all_tags($intro), 24, "..."),
        "content" => $content,
        "meta_description" => $metaDescription,
        "fingerprint" => $fingerprint,
        "faq" => $faq,
        "related_slugs" => upsellio_get_definition_related_slugs($slug, 6),
        "service_links" => $definition["service_links"],
        "reading_time" => $readingTime,
    ];
}

function upsellio_seed_definition_pages($force = false)
{
    $alreadySeeded = get_option("upsellio_definitions_seeded_v1");
    if ($alreadySeeded && !$force) {
        return ["created" => 0, "updated" => 0, "skipped" => count(upsellio_get_definitions_dataset()), "message" => "already_seeded"];
    }

    $created = 0;
    $updated = 0;
    $fingerprints = [];

    foreach (upsellio_get_definitions_dataset() as $index => $definition) {
        $generated = upsellio_definition_generate_content($definition, $index);
        if (isset($fingerprints[$generated["fingerprint"]])) {
            $generated["content"] .= "<p>Ta definicja zostala dopasowana do kontekstu terminu " . esc_html($definition["term"]) . ".</p>";
            $generated["fingerprint"] = md5($generated["fingerprint"] . "|" . $definition["slug"]);
        }
        $fingerprints[$generated["fingerprint"]] = true;

        $existing = get_page_by_path($definition["slug"], OBJECT, "definicja");
        $postData = [
            "post_type" => "definicja",
            "post_status" => "publish",
            "post_title" => $generated["title"],
            "post_name" => $definition["slug"],
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

        update_post_meta($postId, "_upsellio_definition_term", $definition["term"]);
        update_post_meta($postId, "_upsellio_definition_slug", $definition["slug"]);
        update_post_meta($postId, "_upsellio_definition_main_keyword", $definition["main_keyword"]);
        update_post_meta($postId, "_upsellio_definition_secondary_keywords", $definition["secondary_keywords"]);
        update_post_meta($postId, "_upsellio_definition_search_intent", $definition["search_intent"]);
        update_post_meta($postId, "_upsellio_definition_category", $definition["category"]);
        update_post_meta($postId, "_upsellio_definition_difficulty", $definition["difficulty"]);
        update_post_meta($postId, "_upsellio_definition_related_slugs", $generated["related_slugs"]);
        update_post_meta($postId, "_upsellio_definition_service_links", $generated["service_links"]);
        update_post_meta($postId, "_upsellio_definition_meta_description", $generated["meta_description"]);
        update_post_meta($postId, "_upsellio_definition_fingerprint", $generated["fingerprint"]);
        update_post_meta($postId, "_upsellio_definition_faq", $generated["faq"]);
    }

    update_option("upsellio_definitions_seeded_v1", current_time("mysql"));

    return ["created" => $created, "updated" => $updated, "skipped" => 0, "message" => "ok"];
}

function upsellio_get_definitions_seed_url($force = false)
{
    $params = [
        "upsellio_seed_definitions" => 1,
        "_upsellio_nonce" => wp_create_nonce("upsellio_seed_definitions"),
    ];

    if ($force) {
        $params["force"] = 1;
    }

    return add_query_arg($params, admin_url("edit.php?post_type=definicja"));
}

function upsellio_seed_definitions_if_requested()
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }

    if (!isset($_GET["upsellio_seed_definitions"])) {
        return;
    }

    $nonce = isset($_GET["_upsellio_nonce"]) ? sanitize_text_field(wp_unslash($_GET["_upsellio_nonce"])) : "";
    if (!wp_verify_nonce($nonce, "upsellio_seed_definitions")) {
        return;
    }

    $force = !empty($_GET["force"]);
    $result = upsellio_seed_definition_pages($force);

    $redirectUrl = add_query_arg([
        "upsellio_seed_definitions_done" => 1,
        "created" => (int) $result["created"],
        "updated" => (int) $result["updated"],
        "msg" => $result["message"],
    ], admin_url("edit.php?post_type=definicja"));
    wp_safe_redirect($redirectUrl);
    exit;
}
add_action("admin_init", "upsellio_seed_definitions_if_requested");

function upsellio_seed_definitions_admin_notice()
{
    if (!is_admin() || !isset($_GET["upsellio_seed_definitions_done"])) {
        return;
    }

    $created = isset($_GET["created"]) ? (int) $_GET["created"] : 0;
    $updated = isset($_GET["updated"]) ? (int) $_GET["updated"] : 0;
    $msg = isset($_GET["msg"]) ? sanitize_text_field(wp_unslash($_GET["msg"])) : "ok";

    echo '<div class="notice notice-success"><p>';
    if ($msg === "already_seeded") {
        echo esc_html("Generator definicji byl juz uruchomiony. Dodaj parametr force=1, aby nadpisac.");
    } else {
        echo esc_html("Wygenerowano definicje: utworzono {$created}, zaktualizowano {$updated}.");
    }
    echo "</p></div>";
}
add_action("admin_notices", "upsellio_seed_definitions_admin_notice");

function upsellio_definition_seed_menu()
{
    add_submenu_page(
        "edit.php?post_type=definicja",
        "Generator definicji SEO",
        "Generator definicji",
        "manage_options",
        "upsellio-definitions-generator",
        "upsellio_definition_seed_screen",
        43
    );
}
add_action("admin_menu", "upsellio_definition_seed_menu");

function upsellio_definition_seed_screen()
{
    if (!current_user_can("manage_options")) {
        return;
    }
    ?>
    <div class="wrap">
      <h1>Generator definicji SEO</h1>
      <p>Wygeneruj komplet definicji pod slownik wiedzy i linkowanie wewnetrzne.</p>
      <p><a class="button button-primary" href="<?php echo esc_url(upsellio_get_definitions_seed_url(false)); ?>">Uruchom generator (jednorazowo)</a></p>
      <p><a class="button" href="<?php echo esc_url(upsellio_get_definitions_seed_url(true)); ?>">Wymus ponowne wygenerowanie</a></p>
      <p>Po uruchomieniu odswiez trwale linki: <strong>Ustawienia -> Bezposrednie odnosniki -> Zapisz</strong>.</p>
    </div>
    <?php
}

