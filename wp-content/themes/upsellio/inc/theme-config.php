<?php
if (!defined("ABSPATH")) {
    exit;
}

function upsellio_get_theme_config_defaults()
{
    return [
        "template_seo" => [
            "audyt_meta" => [
                "title" => "Darmowy audyt wynikow reklam Meta | Upsellio",
                "description" => "Darmowy audyt wynikow reklam Meta dla firm, ktore chca wiedziec, co dziala, co przepala budzet i co poprawic, zeby pozyskiwac lepsze leady.",
                "og_title" => "Darmowy audyt wynikow reklam Meta | Upsellio",
                "og_description" => "Sprawdze Twoje kampanie Meta Ads i pokaze, co poprawic, zeby zwiekszyc skutecznosc reklam i jakosc zapytan.",
                "og_type" => "website",
                "og_url" => "/audyt-meta",
                "twitter_card" => "summary_large_image",
                "schema_type" => "ProfessionalService",
                "schema_name" => "Upsellio",
                "schema_url" => "/audyt-meta",
                "schema_email" => "kontakt@upsellio.pl",
                "schema_description" => "Darmowy audyt wynikow reklam Meta dla malych i srednich firm.",
                "schema_founder_name" => "Sebastian Kelm",
            ],
            "error_modern" => [
                "title" => "Wystapil blad {code} | Upsellio",
                "description" => "Wystapil blad podczas ladowania zasobu. Wroc do strony glownej lub przejdz do portfolio i bloga.",
                "og_title" => "Wystapil blad | Upsellio",
                "og_description" => "Ta strona jest chwilowo niedostepna lub nie istnieje.",
                "og_type" => "website",
                "og_url" => "/",
                "twitter_card" => "summary",
            ],
            "portfolio" => [
                "title" => "Portfolio stron i aplikacji | Upsellio",
                "description" => "Portfolio realizacji Upsellio: strony, sklepy i aplikacje webowe tworzone pod cele biznesowe, konwersje i SEO.",
                "og_title" => "Portfolio stron i aplikacji | Upsellio",
                "og_description" => "Zobacz realizacje stron, sklepow i aplikacji webowych wraz z opisem zakresu i efektu projektu.",
                "og_type" => "website",
                "og_url" => "/portfolio/",
                "twitter_card" => "summary_large_image",
            ],
            "portfolio_marketingowe" => [
                "title" => "Portfolio marketingowe i case studies | Upsellio",
                "description" => "Case studies kampanii marketingowych: Meta Ads, Google Ads, strony i e-commerce z naciskiem na wynik biznesowy.",
                "og_title" => "Portfolio marketingowe i case studies | Upsellio",
                "og_description" => "Przeglad case studies marketingowych z KPI, kontekstem i rekomendacjami wdrozen.",
                "og_type" => "website",
                "og_url" => "/portfolio-marketingowe/",
                "twitter_card" => "summary_large_image",
            ],
            "lead_magnety" => [
                "title" => "Biblioteka materialow marketingowych | Upsellio",
                "description" => "Checklisty, audyty i materialy do pobrania dla firm B2B, ktore chca poprawic marketing i jakosc leadow.",
                "og_title" => "Biblioteka materialow marketingowych | Upsellio",
                "og_description" => "Praktyczne materialy: checklisty, szablony i audyty wspierajace decyzje marketingowe.",
                "og_type" => "website",
                "og_url" => "/lead-magnety/",
                "twitter_card" => "summary_large_image",
            ],
        ],
        "special_navigation_links" => [
            ["title" => "Portfolio", "path" => "/portfolio/", "enabled" => true],
            ["title" => "Portfolio marketingowe", "path" => "/portfolio-marketingowe/", "enabled" => true],
            ["title" => "Lead magnety", "path" => "/lead-magnety/", "enabled" => true],
            ["title" => "Kontakt", "path" => "/kontakt/", "enabled" => true],
        ],
        "front_page_sections" => [
            "seo" => [
                "title" => "Upsellio - Marketing internetowy i strony WWW dla firm B2B | Sebastian Kelm",
                "description" => "Kampanie Meta Ads i Google Ads, strony i sklepy internetowe dla malych i srednich firm B2B. Sebastian Kelm - praktyk z 10-letnim doswiadczeniem w sprzedazy B2B.",
                "og_title" => "Upsellio - Marketing internetowy i strony WWW dla firm B2B",
                "og_description" => "Kampanie Meta Ads i Google Ads, strony i sklepy internetowe. 10 lat praktyki w sprzedazy B2B. Bezplatna rozmowa wstepna.",
                "og_type" => "website",
                "og_url" => "/",
                "twitter_card" => "summary_large_image",
                "schema_type" => "ProfessionalService",
                "schema_name" => "Upsellio",
                "schema_url" => "/",
                "schema_email" => "kontakt@upsellio.pl",
                "schema_description" => "Marketing internetowy, strony internetowe, sklepy online i doradztwo sprzedazowe dla malych i srednich firm.",
                "schema_founder_name" => "Sebastian Kelm",
            ],
            "nav_links" => [
                ["title" => "Uslugi", "url" => "/#uslugi"],
                ["title" => "Jak dzialam", "url" => "/#jak-dzialam"],
                ["title" => "Wyniki", "url" => "/#wyniki"],
                ["title" => "FAQ", "url" => "/#faq"],
                ["title" => "Blog", "url" => "/blog"],
            ],
            "hero" => [
                "pill" => "Dla malych i srednich firm B2B, ktore chca poukladac marketing i sprzedaz",
                "title" => "Marketing internetowy i strony WWW, ktore realnie sprzedaja",
                "lead" => "Kampanie Meta Ads i Google Ads, strony i sklepy internetowe dla firm B2B.",
                "micro" => "Bez zobowiazan. 30 minut rozmowy, zeby sprawdzic czy i jak moge pomoc.",
                "trust_items" => [
                    "Ponad 10 lat praktyki",
                    "Doradztwo sprzedazowe w cenie",
                    "Odpowiedz w 24h",
                ],
                "aside_label" => "Doswiadczenie z praktyki",
                "aside_stats" => [
                    ["number" => "~1 mln PLN", "text" => "miesieczna sprzedaz zbudowana jako handlowiec B2B - w 2 lata"],
                    ["number" => "~500k PLN", "text" => "obrot sklepu internetowego zbudowanego od zera - po 3 latach"],
                    ["number" => "3x", "text" => "wyzsza marza sklepu vs. ten sam produkt sprzedawany przez handlowcow"],
                    ["number" => "15 osob", "text" => "dzial sprzedazy zbudowany i zarzadzany - od rekrutacji po wyniki"],
                ],
                "primary_cta_label" => "Umow bezplatna rozmowe",
                "primary_cta_url" => "/#kontakt",
                "secondary_cta_label" => "Zobacz co robie",
                "secondary_cta_url" => "/#uslugi",
            ],
            "problem" => [
                "eyebrow" => "Problem",
                "title" => "Technicznie poprawne dzialania, ktore nie przynosza klientow",
                "lead" => "Kampania lub strona moze byc poprawna technicznie i nadal nie dowozic wyniku biznesowego.",
                "items" => [
                    "Placisz za reklamy, ale malo wartosciowych klientow sie odzywa",
                    "Strona wyglada profesjonalnie, ale nie generuje zapytan",
                    "Sklep ma ruch, ale konwersja jest zbyt niska",
                    "Nie wiesz, co faktycznie dziala, a co jest strata budzetu",
                ],
            ],
            "why" => [
                "eyebrow" => "Dlaczego to dziala",
                "title" => "Lacze rzeczy, ktore rzadko ida razem",
                "lead" => "Marketing, strona i praktyka sprzedazy B2B pracuja razem, dlatego latwiej o wyniki.",
                "features" => [
                    [
                        "title" => "Marketing nastawiony na wynik",
                        "description" => "Kampanie sa optymalizowane pod wartosciowe zapytania i klientow.",
                    ],
                    [
                        "title" => "Strony i sklepy pod konwersje",
                        "description" => "Widoki i komunikaty prowadza uzytkownika do konkretnej akcji.",
                    ],
                    [
                        "title" => "Praktyka sprzedazy B2B",
                        "description" => "Decyzje marketingowe sa osadzone w realnym procesie handlowym.",
                    ],
                ],
            ],
            "services" => [
                "eyebrow" => "Uslugi",
                "title" => "Co konkretnie dostajesz",
                "lead" => "Praca bezposrednio z osoba odpowiedzialna za efekt, bez warstwy posrednikow.",
                "primary_service" => [
                    "title" => "Marketing - Meta i Google Ads",
                    "badge" => "Glowna usluga",
                    "description" => "Kampanie reklamowe i iteracyjna optymalizacja pod jakosc leadow oraz sprzedaz.",
                    "checklist_title" => "W ramach tej uslugi",
                    "checklist" => [
                        "Staly nadzor i optymalizacja kampanii",
                        "Raport z wnioskami i rekomendacjami",
                        "Bezposredni kontakt i szybkie decyzje",
                    ],
                    "cta_label" => "Zapytaj o kampanie",
                    "cta_url" => "/#kontakt",
                ],
                "cards" => [
                    [
                        "title" => "Strony i sklepy internetowe",
                        "badge" => "Usluga",
                        "description" => "Projektowanie i wdrozenie stron nastawionych na konwersje i cele biznesowe.",
                        "chips" => ["Landing page", "Strony firmowe", "WooCommerce", "Shopify", "UX"],
                    ],
                    [
                        "title" => "Rozwiazania webowe i automatyzacje",
                        "badge" => "Dodatkowo",
                        "description" => "Aplikacje i automatyzacje wspierajace zespoly sprzedazy i marketingu.",
                        "chips" => ["Aplikacje webowe", "Automatyzacje", "Integracje API"],
                    ],
                ],
                "bonus" => [
                    "title" => "Doradztwo sprzedazowe - w ramach kazdej wspolpracy",
                    "tag" => "W cenie",
                    "body" => "Wiedza handlowa i zarzadcza wspiera kazdy projekt, a nie tylko wybrane uslugi.",
                    "chips" => [
                        "Audyt procesow sprzedazy",
                        "Analiza danych sprzedazowych",
                        "Identyfikacja waskich gardel",
                        "Optymalizacja kosztowa",
                    ],
                ],
            ],
            "results" => [
                "eyebrow" => "Doswiadczenie i wyniki",
                "title" => "Podejscie oparte na praktyce",
                "lead" => "Wyniki budowane na bazie wieloletniej pracy w sprzedazy i ecommerce.",
                "stats" => [
                    ["number" => "~1 mln", "text" => "PLN / mies. sprzedazy B2B po 2 latach pracy"],
                    ["number" => "~500k", "text" => "PLN / mies. obrot sklepu internetowego"],
                    ["number" => "3x", "text" => "wyzsza marza sklepu wzgledem kanalu handlowego"],
                    ["number" => "15 os.", "text" => "zbudowany zespol sprzedazy z procesami i KPI"],
                ],
                "cases" => [
                    [
                        "tag" => "Sprzedaz B2B",
                        "title" => "Budowa sprzedazy od zera",
                        "body" => "Budowa lejka, targetingu i procesu handlowego od podstaw.",
                        "result" => "Efekt: ok. 1 mln PLN / mies. w 24 miesiace",
                    ],
                    [
                        "tag" => "E-commerce",
                        "title" => "Sklep z wyzsza marza",
                        "body" => "Sklep zbudowany od zera dla produktu sprzedawanego tradycyjnie.",
                        "result" => "Efekt: 500k PLN / mies. i 3x wyzsza marza",
                    ],
                ],
            ],
            "fit" => [
                "eyebrow" => "Dla kogo",
                "title" => "Sprawdz, czy do siebie pasujemy",
                "good_label" => "Dobry fit, jesli:",
                "good_items" => [
                    "Prowadzisz firme B2B lub uslugowa",
                    "Chcesz rozumiec dzialania marketingowe i ich sens",
                    "Szukasz partnera, nie tylko wykonawcy",
                ],
                "good_cta_label" => "Umow bezplatna rozmowe",
                "good_cta_url" => "/#kontakt",
                "bad_label" => "Mniejszy fit, jesli:",
                "bad_items" => [
                    "Szukasz tylko najtanszej opcji",
                    "Potrzebujesz wielu specjalistow naraz",
                    "Nie masz czasu na rozmowe o celach",
                ],
            ],
            "cta_band" => [
                "title" => "Nie wiesz, od czego zaczac?",
                "text" => "Powiedz kilka zdan o firmie - otrzymasz konkretna rekomendacje pierwszego kroku.",
                "cta_label" => "Umow bezplatna rozmowe",
                "cta_url" => "/#kontakt",
            ],
            "process" => [
                "eyebrow" => "Jak dzialam",
                "title" => "Nie zaczynam od ustawiania kampanii",
                "lead" => "Zaczynam od diagnozy: co blokuje wzrost i gdzie najszybciej dowiezc efekt.",
                "steps" => [
                    [
                        "number" => "01",
                        "title" => "Poznaje firme i diagnozuje problem",
                        "description" => "Krotka rozmowa i analiza zrodel problemu: oferta, komunikacja, lejek, strona lub proces sprzedazy.",
                    ],
                    [
                        "number" => "02",
                        "title" => "Wybieram najlepsza droge",
                        "description" => "Rekomendacja wynika z potrzeb i danych, a nie z gotowego pakietu uslug.",
                    ],
                    [
                        "number" => "03",
                        "title" => "Wdrazam i raportuje postep",
                        "description" => "Bezposrednia wspolpraca, stale usprawnienia i transparentna komunikacja.",
                    ],
                    [
                        "number" => "04",
                        "title" => "Mierze i optymalizuje",
                        "description" => "Regularna optymalizacja na bazie wynikow oraz rekomendacje dla sprzedazy.",
                    ],
                ],
            ],
            "faq_items" => [
                [
                    "question" => "Co zyskuje, wspolpracujac z Upsellio zamiast z agencja?",
                    "answer" => "Masz bezposredni kontakt i pelna przejrzystosc wspolpracy, bez warstwy posrednikow.",
                ],
                [
                    "question" => "Jak wyglada bezplatna rozmowa wstepna?",
                    "answer" => "30 minut rozmowy o sytuacji i celach. Bez presji sprzedazowej i gotowych pakietow.",
                ],
            ],
            "contact_service_options" => [
                "Kampanie Meta / Google Ads",
                "Strona lub sklep internetowy",
                "Marketing + strona (oba)",
                "Aplikacja lub automatyzacja",
                "Nie wiem - chce porozmawiac",
            ],
            "contact_phone" => "+48 000 000 000",
            "contact_email" => "kontakt@upsellio.pl",
        ],
    ];
}

function upsellio_get_theme_config()
{
    $defaults = upsellio_get_theme_config_defaults();
    $stored = get_option("upsellio_theme_config_v1", []);
    if (!is_array($stored)) {
        $stored = [];
    }

    return array_replace_recursive($defaults, $stored);
}

function upsellio_update_theme_config($new_config)
{
    $defaults = upsellio_get_theme_config_defaults();
    $safe_config = is_array($new_config) ? array_replace_recursive($defaults, $new_config) : $defaults;
    update_option("upsellio_theme_config_v1", $safe_config);
}

function upsellio_get_special_navigation_links_config()
{
    $config = upsellio_get_theme_config();
    $rows = isset($config["special_navigation_links"]) && is_array($config["special_navigation_links"])
        ? $config["special_navigation_links"]
        : [];

    $links = [];
    foreach ($rows as $row) {
        $title = isset($row["title"]) ? sanitize_text_field((string) $row["title"]) : "";
        $path = isset($row["path"]) ? sanitize_text_field((string) $row["path"]) : "";
        $enabled = !isset($row["enabled"]) || (bool) $row["enabled"];
        if ($title === "" || $path === "" || !$enabled) {
            continue;
        }
        $links[] = [
            "title" => $title,
            "path" => "/" . ltrim($path, "/"),
        ];
    }

    return $links;
}

function upsellio_get_template_seo_config($template_key)
{
    $template_key = sanitize_key((string) $template_key);
    if ($template_key === "") {
        return [];
    }
    $config = upsellio_get_theme_config();
    $template_seo = isset($config["template_seo"]) && is_array($config["template_seo"])
        ? $config["template_seo"]
        : [];
    $payload = isset($template_seo[$template_key]) && is_array($template_seo[$template_key])
        ? $template_seo[$template_key]
        : [];

    return $payload;
}

function upsellio_register_template_seo_head($template_key, $replacements = [])
{
    $payload = upsellio_get_template_seo_config($template_key);
    if (empty($payload)) {
        return;
    }
    $replacements = is_array($replacements) ? $replacements : [];
    $replace_tokens = static function ($value) use ($replacements) {
        $value = (string) $value;
        foreach ($replacements as $token => $replacement) {
            $value = str_replace("{" . (string) $token . "}", (string) $replacement, $value);
        }
        return $value;
    };

    $title = trim($replace_tokens((string) ($payload["title"] ?? "")));
    $description = trim($replace_tokens((string) ($payload["description"] ?? "")));
    $og_title = trim($replace_tokens((string) ($payload["og_title"] ?? "")));
    $og_description = trim($replace_tokens((string) ($payload["og_description"] ?? "")));
    $og_type = trim($replace_tokens((string) ($payload["og_type"] ?? "website")));
    $og_url = trim($replace_tokens((string) ($payload["og_url"] ?? "/")));
    $twitter_card = trim($replace_tokens((string) ($payload["twitter_card"] ?? "summary")));
    $schema_type = trim($replace_tokens((string) ($payload["schema_type"] ?? "")));
    $schema_name = trim($replace_tokens((string) ($payload["schema_name"] ?? "")));
    $schema_url = trim($replace_tokens((string) ($payload["schema_url"] ?? "")));
    $schema_email = trim($replace_tokens((string) ($payload["schema_email"] ?? "")));
    $schema_description = trim($replace_tokens((string) ($payload["schema_description"] ?? "")));
    $schema_founder_name = trim($replace_tokens((string) ($payload["schema_founder_name"] ?? "")));

    if ($title !== "") {
        add_filter("pre_get_document_title", function ($current_title) use ($title) {
            return $title;
        });
    }

    add_action("wp_head", function () use (
        $description,
        $og_title,
        $og_description,
        $og_type,
        $og_url,
        $twitter_card,
        $schema_type,
        $schema_name,
        $schema_url,
        $schema_email,
        $schema_description,
        $schema_founder_name
    ) {
        if ($description !== "") {
            echo '<meta name="description" content="' . esc_attr($description) . '" />' . "\n";
        }
        if ($og_title !== "") {
            echo '<meta property="og:title" content="' . esc_attr($og_title) . '" />' . "\n";
        }
        if ($og_description !== "") {
            echo '<meta property="og:description" content="' . esc_attr($og_description) . '" />' . "\n";
        }
        echo '<meta property="og:type" content="' . esc_attr($og_type !== "" ? $og_type : "website") . '" />' . "\n";
        echo '<meta property="og:url" content="' . esc_url(home_url($og_url !== "" ? $og_url : "/")) . '" />' . "\n";
        echo '<meta name="twitter:card" content="' . esc_attr($twitter_card !== "" ? $twitter_card : "summary") . '" />' . "\n";

        if ($schema_type !== "") {
            $schema_payload = [
                "@context" => "https://schema.org",
                "@type" => $schema_type,
                "name" => $schema_name !== "" ? $schema_name : get_bloginfo("name"),
                "url" => home_url($schema_url !== "" ? $schema_url : "/"),
                "description" => $schema_description,
            ];
            if ($schema_email !== "") {
                $schema_payload["email"] = $schema_email;
            }
            if ($schema_founder_name !== "") {
                $schema_payload["founder"] = [
                    "@type" => "Person",
                    "name" => $schema_founder_name,
                ];
            }
            echo '<script type="application/ld+json">' . wp_json_encode($schema_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
        }
    }, 1);
}

function upsellio_get_front_page_content_config()
{
    $config = upsellio_get_theme_config();
    return isset($config["front_page_sections"]) && is_array($config["front_page_sections"])
        ? $config["front_page_sections"]
        : [];
}

function upsellio_get_front_page_data_issues()
{
    $sections = upsellio_get_front_page_content_config();
    $issues = [];

    $hero_title = trim((string) ($sections["hero"]["title"] ?? ""));
    if ($hero_title === "") {
        $issues[] = "Brak hero.title w konfiguracji sekcji strony glownej.";
    }
    $seo_title = trim((string) ($sections["seo"]["title"] ?? ""));
    if ($seo_title === "") {
        $issues[] = "Brak seo.title w konfiguracji sekcji strony glownej.";
    }
    $seo_description = trim((string) ($sections["seo"]["description"] ?? ""));
    if ($seo_description === "") {
        $issues[] = "Brak seo.description w konfiguracji sekcji strony glownej.";
    }

    $faq_items = isset($sections["faq_items"]) && is_array($sections["faq_items"]) ? $sections["faq_items"] : [];
    if (empty($faq_items)) {
        $issues[] = "Brak FAQ w konfiguracji strony glownej.";
    }

    $required_section_keys = ["problem", "why", "services", "cta_band", "process", "results", "fit"];
    foreach ($required_section_keys as $required_section_key) {
        $section = isset($sections[$required_section_key]) && is_array($sections[$required_section_key])
            ? $sections[$required_section_key]
            : [];
        $section_title = trim((string) ($section["title"] ?? ""));
        if ($section_title === "") {
            $issues[] = "Brak " . $required_section_key . ".title w konfiguracji sekcji strony glownej.";
        }
    }

    return $issues;
}

function upsellio_register_theme_config_menu()
{
    add_theme_page(
        "Konfiguracja dynamiczna",
        "Konfiguracja dynamiczna",
        "manage_options",
        "upsellio-theme-config",
        "upsellio_render_theme_config_screen"
    );
}
add_action("admin_menu", "upsellio_register_theme_config_menu");

function upsellio_handle_theme_config_submit()
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }
    if (!isset($_POST["upsellio_theme_config_submit"])) {
        return;
    }
    if (!isset($_POST["upsellio_theme_config_nonce"])) {
        return;
    }
    $nonce = sanitize_text_field(wp_unslash($_POST["upsellio_theme_config_nonce"]));
    if (!wp_verify_nonce($nonce, "upsellio_theme_config_save")) {
        return;
    }

    $special_links_json = isset($_POST["ups_special_links_json"]) ? (string) wp_unslash($_POST["ups_special_links_json"]) : "[]";
    $front_page_json = isset($_POST["ups_front_page_sections_json"]) ? (string) wp_unslash($_POST["ups_front_page_sections_json"]) : "{}";
    $template_seo_json = isset($_POST["ups_template_seo_json"]) ? (string) wp_unslash($_POST["ups_template_seo_json"]) : "{}";
    $special_links = json_decode($special_links_json, true);
    $front_sections = json_decode($front_page_json, true);
    $template_seo = json_decode($template_seo_json, true);
    if (!is_array($special_links)) {
        $special_links = upsellio_get_theme_config_defaults()["special_navigation_links"];
    }
    if (!is_array($front_sections)) {
        $front_sections = upsellio_get_theme_config_defaults()["front_page_sections"];
    }
    if (!is_array($template_seo)) {
        $template_seo = upsellio_get_theme_config_defaults()["template_seo"];
    }

    upsellio_update_theme_config([
        "template_seo" => $template_seo,
        "special_navigation_links" => $special_links,
        "front_page_sections" => $front_sections,
    ]);

    wp_safe_redirect(add_query_arg(["page" => "upsellio-theme-config", "saved" => 1], admin_url("themes.php")));
    exit;
}
add_action("admin_init", "upsellio_handle_theme_config_submit");

function upsellio_render_theme_config_screen()
{
    if (!current_user_can("manage_options")) {
        return;
    }

    $config = upsellio_get_theme_config();
    $template_seo_json = wp_json_encode($config["template_seo"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $special_links_json = wp_json_encode($config["special_navigation_links"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $front_page_json = wp_json_encode($config["front_page_sections"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    ?>
    <div class="wrap">
      <h1>Konfiguracja dynamiczna Upsellio</h1>
      <?php if (isset($_GET["saved"])) : ?>
        <div class="notice notice-success"><p>Zapisano konfiguracje.</p></div>
      <?php endif; ?>
      <p>Utrzymuj tresci i linki jako data-driven. Wklej JSON dla linkow specjalnych i sekcji strony glownej.</p>
      <form method="post" action="">
        <?php wp_nonce_field("upsellio_theme_config_save", "upsellio_theme_config_nonce"); ?>
        <h2>Template SEO (JSON)</h2>
        <textarea name="ups_template_seo_json" rows="16" class="large-text code"><?php echo esc_textarea((string) $template_seo_json); ?></textarea>
        <h2>Special navigation links (JSON)</h2>
        <textarea name="ups_special_links_json" rows="14" class="large-text code"><?php echo esc_textarea((string) $special_links_json); ?></textarea>
        <h2>Front page sections (JSON)</h2>
        <textarea name="ups_front_page_sections_json" rows="24" class="large-text code"><?php echo esc_textarea((string) $front_page_json); ?></textarea>
        <p><button type="submit" name="upsellio_theme_config_submit" value="1" class="button button-primary">Zapisz konfiguracje</button></p>
      </form>
    </div>
    <?php
}
