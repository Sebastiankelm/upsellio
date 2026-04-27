<?php
if (!defined("ABSPATH")) {
    exit;
}

function upsellio_get_theme_config_defaults()
{
    return [
        "template_seo" => [
            "audyt_meta" => [
                "title" => "Darmowy audyt wyników reklam Meta | Upsellio",
                "description" => "Darmowy audyt wyników reklam Meta dla firm, które chcą wiedzieć, co działa, co przepala budżet i co poprawić, żeby pozyskiwać lepsze leady.",
                "og_title" => "Darmowy audyt wyników reklam Meta | Upsellio",
                "og_description" => "Sprawdzę Twoje kampanie Meta Ads i pokażę, co poprawić, żeby zwiększyć skuteczność reklam i jakość zapytań.",
                "og_type" => "website",
                "og_url" => "/audyt-meta",
                "twitter_card" => "summary_large_image",
                "schema_type" => "ProfessionalService",
                "schema_name" => "Upsellio",
                "schema_url" => "/audyt-meta",
                "schema_email" => "kontakt@upsellio.pl",
                "schema_description" => "Darmowy audyt wyników reklam Meta dla małych i średnich firm.",
                "schema_founder_name" => "Sebastian Kelm",
            ],
            "error_modern" => [
                "title" => "Wystąpił błąd {code} | Upsellio",
                "description" => "Wystąpił błąd podczas ładowania zasobu. Wróć do strony głównej lub przejdź do portfolio i bloga.",
                "og_title" => "Wystąpił błąd | Upsellio",
                "og_description" => "Ta strona jest chwilowo niedostępna lub nie istnieje.",
                "og_type" => "website",
                "og_url" => "/",
                "twitter_card" => "summary",
            ],
            "portfolio" => [
                "title" => "Portfolio stron i aplikacji | Upsellio",
                "description" => "Portfolio realizacji Upsellio: strony, sklepy i aplikacje webowe tworzone pod cele biznesowe, konwersje i SEO.",
                "og_title" => "Portfolio stron i aplikacji | Upsellio",
                "og_description" => "Zobacz realizacje stron, sklepów i aplikacji webowych wraz z opisem zakresu i efektu projektu.",
                "og_type" => "website",
                "og_url" => "/portfolio/",
                "twitter_card" => "summary_large_image",
            ],
            "portfolio_marketingowe" => [
                "title" => "Portfolio marketingowe i case studies | Upsellio",
                "description" => "Case studies kampanii marketingowych: Meta Ads, Google Ads, strony i e-commerce z naciskiem na wynik biznesowy.",
                "og_title" => "Portfolio marketingowe i case studies | Upsellio",
                "og_description" => "Przegląd case studies marketingowych z KPI, kontekstem i rekomendacjami wdrożeń.",
                "og_type" => "website",
                "og_url" => "/portfolio-marketingowe/",
                "twitter_card" => "summary_large_image",
            ],
            "lead_magnety" => [
                "title" => "Checklisty i materiały marketingowe do pobrania | Upsellio",
                "description" => "Darmowe checklisty, audyty i szablony dla firm B2B: Meta Ads, Google Ads, landing page i lead generation. Pobierz i popraw wyniki kampanii bez zgadywania.",
                "og_title" => "Checklisty i materiały marketingowe do pobrania | Upsellio",
                "og_description" => "Darmowe checklisty, audyty i szablony dla firm B2B: Meta Ads, Google Ads, landing page i lead generation.",
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
                "title" => "Marketing B2B, Google Ads i Meta Ads | Upsellio",
                "description" => "Prowadzę kampanie Google Ads, Meta Ads i tworzę strony WWW nastawione na leady. Marketing B2B, CRO i sprzedaż w jednym procesie.",
                "og_title" => "Marketing B2B, Google Ads i Meta Ads | Upsellio",
                "og_description" => "Prowadzę kampanie Google Ads, Meta Ads i tworzę strony WWW nastawione na leady. Marketing B2B, CRO i sprzedaż w jednym procesie.",
                "og_type" => "website",
                "og_url" => "/",
                "twitter_card" => "summary_large_image",
                "schema_type" => "ProfessionalService",
                "schema_name" => "Upsellio",
                "schema_url" => "/",
                "schema_email" => "kontakt@upsellio.pl",
                "schema_description" => "Marketing internetowy, strony internetowe, sklepy online i doradztwo sprzedażowe dla małych i średnich firm.",
                "schema_founder_name" => "Sebastian Kelm",
            ],
            "nav_links" => [
                ["title" => "Usługi", "url" => "/#uslugi"],
                ["title" => "Jak działam", "url" => "/#jak-dzialam"],
                ["title" => "Wyniki", "url" => "/#wyniki"],
                ["title" => "FAQ", "url" => "/#faq"],
                ["title" => "Blog", "url" => "/blog"],
            ],
            "hero" => [
                "pill" => "Dla małych i średnich firm B2B, które chcą poukładać marketing i sprzedaż",
                "title" => "Marketing internetowy i strony WWW, które realnie sprzedają",
                "lead" => "Kampanie Meta Ads i Google Ads, strony i sklepy internetowe dla firm B2B.",
                "micro" => "Bez zobowiązań. 30 minut rozmowy, żeby sprawdzić, czy i jak mogę pomóc.",
                "trust_items" => [
                    "Ponad 10 lat praktyki",
                    "Doradztwo sprzedażowe w cenie",
                    "Odpowiedź w 24h",
                ],
                "aside_label" => "Doswiadczenie z praktyki",
                "aside_stats" => [
                    ["number" => "~1 mln PLN", "text" => "miesięczna sprzedaż zbudowana jako handlowiec B2B - w 2 lata"],
                    ["number" => "~500k PLN", "text" => "obrót sklepu internetowego zbudowanego od zera - po 3 latach"],
                    ["number" => "3x", "text" => "wyższa marża sklepu vs. ten sam produkt sprzedawany przez handlowców"],
                    ["number" => "15 osób", "text" => "dział sprzedaży zbudowany i zarządzany - od rekrutacji po wyniki"],
                ],
                "primary_cta_label" => "Umów bezpłatną rozmowę",
                "primary_cta_url" => "/#kontakt",
                "secondary_cta_label" => "Zobacz co robię",
                "secondary_cta_url" => "/#uslugi",
            ],
            "problem" => [
                "eyebrow" => "Problem",
                "title" => "Technicznie poprawne działania, które nie przynoszą klientów",
                "lead" => "Kampania lub strona może być poprawna technicznie i nadal nie dowozić wyniku biznesowego.",
                "items" => [
                    "Płacisz za reklamy, ale mało wartościowych klientów się odzywa",
                    "Strona wygląda profesjonalnie, ale nie generuje zapytań",
                    "Sklep ma ruch, ale konwersja jest zbyt niska",
                    "Nie wiesz, co faktycznie działa, a co jest stratą budżetu",
                ],
            ],
            "why" => [
                "eyebrow" => "Dlaczego to działa",
                "title" => "Łączę rzeczy, które rzadko idą razem",
                "lead" => "Marketing, strona i praktyka sprzedaży B2B pracują razem, dlatego łatwiej o wyniki.",
                "features" => [
                    [
                        "title" => "Marketing nastawiony na wynik",
                        "description" => "Kampanie są optymalizowane pod wartościowe zapytania i klientów.",
                    ],
                    [
                        "title" => "Strony i sklepy pod konwersję",
                        "description" => "Widoki i komunikaty prowadzą użytkownika do konkretnej akcji.",
                    ],
                    [
                        "title" => "Praktyka sprzedaży B2B",
                        "description" => "Decyzje marketingowe są osadzone w realnym procesie handlowym.",
                    ],
                ],
            ],
            "services" => [
                "eyebrow" => "Usługi",
                "title" => "Co konkretnie dostajesz",
                "lead" => "Praca bezpośrednio z osobą odpowiedzialną za efekt, bez warstwy pośredników.",
                "primary_service" => [
                    "title" => "Marketing - Meta i Google Ads",
                    "badge" => "Główna usługa",
                    "description" => "Kampanie reklamowe i iteracyjna optymalizacja pod jakość leadów oraz sprzedaż.",
                    "checklist_title" => "W ramach tej usługi",
                    "checklist" => [
                        "Stały nadzór i optymalizacja kampanii",
                        "Raport z wnioskami i rekomendacjami",
                        "Bezpośredni kontakt i szybkie decyzje",
                    ],
                    "cta_label" => "Zapytaj o kampanie",
                    "cta_url" => "/#kontakt",
                ],
                "cards" => [
                    [
                        "title" => "Strony i sklepy internetowe",
                        "badge" => "Usługa",
                        "description" => "Projektowanie i wdrożenie stron nastawionych na konwersję i cele biznesowe.",
                        "chips" => ["Landing page", "Strony firmowe", "WooCommerce", "Shopify", "UX"],
                    ],
                    [
                        "title" => "Rozwiązania webowe i automatyzacje",
                        "badge" => "Dodatkowo",
                        "description" => "Aplikacje i automatyzacje wspierające zespoły sprzedaży i marketingu.",
                        "chips" => ["Aplikacje webowe", "Automatyzacje", "Integracje API"],
                    ],
                ],
                "bonus" => [
                    "title" => "Doradztwo sprzedażowe - w ramach każdej współpracy",
                    "tag" => "W cenie",
                    "body" => "Wiedza handlowa i zarządcza wspiera każdy projekt, a nie tylko wybrane usługi.",
                    "chips" => [
                        "Audyt procesów sprzedaży",
                        "Analiza danych sprzedażowych",
                        "Identyfikacja wąskich gardeł",
                        "Optymalizacja kosztowa",
                    ],
                ],
            ],
            "results" => [
                "eyebrow" => "Doświadczenie i wyniki",
                "title" => "Podejście oparte na praktyce",
                "lead" => "Wyniki budowane na bazie wieloletniej pracy w sprzedaży i ecommerce.",
                "stats" => [
                    ["number" => "~1 mln", "text" => "PLN / mies. sprzedaży B2B po 2 latach pracy"],
                    ["number" => "~500k", "text" => "PLN / mies. obrót sklepu internetowego"],
                    ["number" => "3x", "text" => "wyższa marża sklepu względem kanału handlowego"],
                    ["number" => "15 os.", "text" => "zbudowany zespół sprzedaży z procesami i KPI"],
                ],
                "cases" => [
                    [
                        "tag" => "Sprzedaż B2B",
                        "title" => "Budowa sprzedaży od zera",
                        "body" => "Budowa lejka, targetingu i procesu handlowego od podstaw.",
                        "result" => "Efekt: ok. 1 mln PLN / mies. w 24 miesiące",
                    ],
                    [
                        "tag" => "E-commerce",
                        "title" => "Sklep z wyższą marżą",
                        "body" => "Sklep zbudowany od zera dla produktu sprzedawanego tradycyjnie.",
                        "result" => "Efekt: 500k PLN / mies. i 3x wyższa marża",
                    ],
                ],
            ],
            "fit" => [
                "eyebrow" => "Dla kogo",
                "title" => "Sprawdź, czy do siebie pasujemy",
                "good_label" => "Dobry fit, jeśli:",
                "good_items" => [
                    "Prowadzisz firmę B2B lub usługową",
                    "Chcesz rozumieć działania marketingowe i ich sens",
                    "Szukasz partnera, nie tylko wykonawcy",
                ],
                "good_cta_label" => "Umów bezpłatną rozmowę",
                "good_cta_url" => "/#kontakt",
                "bad_label" => "Mniejszy fit, jeśli:",
                "bad_items" => [
                    "Szukasz tylko najtańszej opcji",
                    "Potrzebujesz wielu specjalistów naraz",
                    "Nie masz czasu na rozmowę o celach",
                ],
            ],
            "cta_band" => [
                "title" => "Nie wiesz, od czego zacząć?",
                "text" => "Powiedz kilka zdań o firmie - otrzymasz konkretną rekomendację pierwszego kroku.",
                "cta_label" => "Umów bezpłatną rozmowę",
                "cta_url" => "/#kontakt",
            ],
            "process" => [
                "eyebrow" => "Jak działam",
                "title" => "Nie zaczynam od ustawiania kampanii",
                "lead" => "Zaczynam od diagnozy: co blokuje wzrost i gdzie najszybciej dowieźć efekt.",
                "steps" => [
                    [
                        "number" => "01",
                        "title" => "Poznaję firmę i diagnozuję problem",
                        "description" => "Krótka rozmowa i analiza źródeł problemu: oferta, komunikacja, lejek, strona lub proces sprzedaży.",
                    ],
                    [
                        "number" => "02",
                        "title" => "Wybieram najlepszą drogę",
                        "description" => "Rekomendacja wynika z potrzeb i danych, a nie z gotowego pakietu usług.",
                    ],
                    [
                        "number" => "03",
                        "title" => "Wdrażam i raportuję postęp",
                        "description" => "Bezpośrednia współpraca, stałe usprawnienia i transparentna komunikacja.",
                    ],
                    [
                        "number" => "04",
                        "title" => "Mierzę i optymalizuję",
                        "description" => "Regularna optymalizacja na bazie wyników oraz rekomendacje dla sprzedaży.",
                    ],
                ],
            ],
            "faq_items" => [
                [
                    "question" => "Co zyskuję, współpracując z Upsellio zamiast z agencją?",
                    "answer" => "Masz bezpośredni kontakt i pełną przejrzystość współpracy, bez warstwy pośredników.",
                ],
                [
                    "question" => "Jak wygląda bezpłatna rozmowa wstępna?",
                    "answer" => "30 minut rozmowy o sytuacji i celach. Bez presji sprzedażowej i gotowych pakietów.",
                ],
            ],
            "contact_service_options" => [
                "Kampanie Meta / Google Ads",
                "Strona lub sklep internetowy",
                "Marketing + strona (oba)",
                "Aplikacja lub automatyzacja",
                "Nie wiem - chcę porozmawiać",
            ],
            "contact_phone" => "+48 575 522 595",
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

function upsellio_get_contact_phone()
{
    return "+48 575 522 595";
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

function upsellio_theme_config_should_export_text($key, $value)
{
    if (!is_string($value)) {
        return false;
    }

    $trimmed = trim($value);
    if ($trimmed === "") {
        return false;
    }

    $key = strtolower((string) $key);
    $non_content_suffixes = ["url", "path", "email", "phone"];
    if (in_array($key, $non_content_suffixes, true)) {
        return false;
    }

    if (strpos($trimmed, "/") === 0 || strpos($trimmed, "http://") === 0 || strpos($trimmed, "https://") === 0) {
        return false;
    }

    return true;
}

function upsellio_theme_config_row_id($source, $page, $section, $field_path)
{
    return md5(implode("|", [(string) $source, (string) $page, (string) $section, (string) $field_path]));
}

function upsellio_collect_theme_text_rows_recursive($value, $context, $path, &$rows)
{
    if (is_array($value)) {
        foreach ($value as $child_key => $child_value) {
            $next_path = $path;
            $next_path[] = (string) $child_key;
            upsellio_collect_theme_text_rows_recursive($child_value, $context, $next_path, $rows);
        }
        return;
    }

    if (!is_string($value)) {
        return;
    }

    $field_key = isset($path[count($path) - 1]) ? (string) $path[count($path) - 1] : "";
    if (!upsellio_theme_config_should_export_text($field_key, $value)) {
        return;
    }

    $field_path = implode(".", $path);
    $row_id = upsellio_theme_config_row_id($context["source"], $context["page"], $context["section"], $field_path);
    $rows[] = [
        "row_id" => $row_id,
        "source" => (string) $context["source"],
        "page" => (string) $context["page"],
        "section" => (string) $context["section"],
        "field_path" => $field_path,
        "current_text" => $value,
        "updated_text" => $value,
    ];
}

function upsellio_collect_theme_config_text_rows($config = null)
{
    $config = is_array($config) ? $config : upsellio_get_theme_config();
    $rows = [];

    $template_seo = isset($config["template_seo"]) && is_array($config["template_seo"]) ? $config["template_seo"] : [];
    foreach ($template_seo as $template_key => $template_payload) {
        if (!is_array($template_payload)) {
            continue;
        }
        $context = [
            "source" => "template_seo",
            "page" => (string) $template_key,
            "section" => "seo",
        ];
        upsellio_collect_theme_text_rows_recursive($template_payload, $context, [(string) $template_key], $rows);
    }

    $front_page_sections = isset($config["front_page_sections"]) && is_array($config["front_page_sections"])
        ? $config["front_page_sections"]
        : [];
    foreach ($front_page_sections as $section_key => $section_payload) {
        $context = [
            "source" => "front_page_sections",
            "page" => "front_page",
            "section" => (string) $section_key,
        ];
        upsellio_collect_theme_text_rows_recursive($section_payload, $context, [(string) $section_key], $rows);
    }

    $special_links = isset($config["special_navigation_links"]) && is_array($config["special_navigation_links"])
        ? $config["special_navigation_links"]
        : [];
    foreach ($special_links as $index => $link_payload) {
        if (!is_array($link_payload)) {
            continue;
        }
        $context = [
            "source" => "special_navigation_links",
            "page" => "global_navigation",
            "section" => "link_" . (string) $index,
        ];
        upsellio_collect_theme_text_rows_recursive($link_payload, $context, [(string) $index], $rows);
    }

    return $rows;
}

function upsellio_get_theme_config_row_index($config = null)
{
    $config = is_array($config) ? $config : upsellio_get_theme_config();
    $rows = upsellio_collect_theme_config_text_rows($config);
    $index = [];

    foreach ($rows as $row) {
        $source = (string) ($row["source"] ?? "");
        $field_path = (string) ($row["field_path"] ?? "");
        $parts = $field_path !== "" ? explode(".", $field_path) : [];
        if (empty($parts)) {
            continue;
        }

        $base_path = [];
        if ($source === "template_seo") {
            $base_path = ["template_seo"];
        } elseif ($source === "front_page_sections") {
            $base_path = ["front_page_sections"];
        } elseif ($source === "special_navigation_links") {
            $base_path = ["special_navigation_links"];
        } else {
            continue;
        }

        $row_id = (string) ($row["row_id"] ?? "");
        if ($row_id === "") {
            continue;
        }

        $index[$row_id] = [
            "source" => $source,
            "path" => array_merge($base_path, $parts),
            "current_text" => (string) ($row["current_text"] ?? ""),
        ];
    }

    return $index;
}

function upsellio_set_theme_config_value_by_path(&$config, $path, $value)
{
    if (!is_array($config) || !is_array($path) || empty($path)) {
        return false;
    }

    $cursor = &$config;
    $last_index = count($path) - 1;
    foreach ($path as $index => $segment) {
        $segment = (string) $segment;
        if ($segment === "") {
            return false;
        }

        if ($index === $last_index) {
            if (!is_array($cursor) || !array_key_exists($segment, $cursor) || !is_string($cursor[$segment])) {
                return false;
            }
            $cursor[$segment] = (string) $value;
            return true;
        }

        if (!is_array($cursor) || !array_key_exists($segment, $cursor) || !is_array($cursor[$segment])) {
            return false;
        }
        $cursor = &$cursor[$segment];
    }

    return false;
}

function upsellio_get_theme_text_csv_export_url()
{
    return wp_nonce_url(
        add_query_arg(
            [
                "action" => "upsellio_export_theme_text_csv",
            ],
            admin_url("admin-post.php")
        ),
        "upsellio_export_theme_text_csv"
    );
}

function upsellio_send_csv_download_headers($filename)
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    nocache_headers();
    header("Content-Type: text/csv; charset=utf-8");
    header("Content-Type: application/csv; charset=utf-8");
    header("Content-Disposition: attachment; filename=" . $filename);
    header("Content-Transfer-Encoding: binary");
    header("Pragma: no-cache");
    header("Expires: 0");
}

function upsellio_handle_theme_text_csv_export()
{
    if (!current_user_can("manage_options")) {
        wp_die(esc_html__("Nie masz uprawnień do tego działania.", "upsellio"));
    }

    check_admin_referer("upsellio_export_theme_text_csv");

    $rows = upsellio_collect_theme_config_text_rows();
    upsellio_send_csv_download_headers("upsellio-theme-texts-" . gmdate("Ymd-His") . ".csv");

    $stream = fopen("php://output", "w");
    if ($stream === false) {
        wp_die(esc_html__("Nie udalo sie wygenerowac pliku CSV.", "upsellio"));
    }

    fputcsv($stream, ["row_id", "source", "page", "section", "field_path", "current_text", "updated_text"]);
    foreach ($rows as $row) {
        fputcsv($stream, [
            (string) $row["row_id"],
            (string) $row["source"],
            (string) $row["page"],
            (string) $row["section"],
            (string) $row["field_path"],
            (string) $row["current_text"],
            (string) $row["updated_text"],
        ]);
    }
    fclose($stream);
    exit;
}
add_action("admin_post_upsellio_export_theme_text_csv", "upsellio_handle_theme_text_csv_export");

function upsellio_find_theme_import_text_from_csv_row($row, $header_map)
{
    $candidate_columns = ["updated_text", "new_text", "edited_text", "seo_text", "text"];
    foreach ($candidate_columns as $column_name) {
        if (!isset($header_map[$column_name])) {
            continue;
        }
        $column_index = (int) $header_map[$column_name];
        if (isset($row[$column_index])) {
            return (string) $row[$column_index];
        }
    }

    if (isset($header_map["current_text"])) {
        $current_index = (int) $header_map["current_text"];
        if (isset($row[$current_index])) {
            return (string) $row[$current_index];
        }
    }

    return "";
}

function upsellio_handle_theme_text_csv_import()
{
    if (!current_user_can("manage_options")) {
        wp_die(esc_html__("Nie masz uprawnień do tego działania.", "upsellio"));
    }

    check_admin_referer("upsellio_import_theme_text_csv");

    $redirect_url = add_query_arg(["page" => "upsellio-theme-config"], admin_url("themes.php"));
    if (!isset($_FILES["upsellio_theme_text_csv_file"])) {
        wp_safe_redirect(add_query_arg(["theme_text_import_error" => "missing_file"], $redirect_url));
        exit;
    }

    $upload = $_FILES["upsellio_theme_text_csv_file"];
    if (!is_array($upload) || (int) ($upload["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        wp_safe_redirect(add_query_arg(["theme_text_import_error" => "upload_error"], $redirect_url));
        exit;
    }

    $tmp_name = isset($upload["tmp_name"]) ? (string) $upload["tmp_name"] : "";
    if ($tmp_name === "" || !is_uploaded_file($tmp_name)) {
        wp_safe_redirect(add_query_arg(["theme_text_import_error" => "invalid_upload"], $redirect_url));
        exit;
    }

    $stream = fopen($tmp_name, "r");
    if ($stream === false) {
        wp_safe_redirect(add_query_arg(["theme_text_import_error" => "read_error"], $redirect_url));
        exit;
    }

    $headers = fgetcsv($stream);
    if (!is_array($headers) || empty($headers)) {
        fclose($stream);
        wp_safe_redirect(add_query_arg(["theme_text_import_error" => "missing_headers"], $redirect_url));
        exit;
    }

    $header_map = [];
    foreach ($headers as $index => $header_name) {
        $header_map[strtolower(trim((string) $header_name))] = (int) $index;
    }
    if (!isset($header_map["row_id"])) {
        fclose($stream);
        wp_safe_redirect(add_query_arg(["theme_text_import_error" => "missing_row_id"], $redirect_url));
        exit;
    }

    $config = upsellio_get_theme_config();
    $index = upsellio_get_theme_config_row_index($config);
    $updated = 0;
    $skipped = 0;
    $processed = 0;

    while (($row = fgetcsv($stream)) !== false) {
        if (!is_array($row) || empty($row)) {
            continue;
        }

        $row_id = isset($row[$header_map["row_id"]]) ? trim((string) $row[$header_map["row_id"]]) : "";
        if ($row_id === "" || !isset($index[$row_id])) {
            $skipped++;
            continue;
        }

        $next_text = upsellio_find_theme_import_text_from_csv_row($row, $header_map);
        $current_text = (string) ($index[$row_id]["current_text"] ?? "");
        $processed++;
        if ($next_text === $current_text) {
            continue;
        }

        $path = isset($index[$row_id]["path"]) && is_array($index[$row_id]["path"]) ? $index[$row_id]["path"] : [];
        $did_update = upsellio_set_theme_config_value_by_path($config, $path, $next_text);
        if ($did_update) {
            $updated++;
        } else {
            $skipped++;
        }
    }
    fclose($stream);

    if ($updated > 0) {
        upsellio_update_theme_config($config);
    }

    wp_safe_redirect(
        add_query_arg(
            [
                "theme_text_imported" => 1,
                "theme_text_import_processed" => $processed,
                "theme_text_import_updated" => $updated,
                "theme_text_import_skipped" => $skipped,
            ],
            $redirect_url
        )
    );
    exit;
}
add_action("admin_post_upsellio_import_theme_text_csv", "upsellio_handle_theme_text_csv_import");

function upsellio_get_content_post_type_options()
{
    $excluded_types = [
        "attachment",
        "revision",
        "nav_menu_item",
        "custom_css",
        "customize_changeset",
        "oembed_cache",
        "user_request",
        "wp_block",
        "wp_navigation",
        "wp_template",
        "wp_template_part",
    ];

    $post_type_objects = get_post_types(["public" => true], "objects");
    $options = [];
    if (!is_array($post_type_objects)) {
        $post_type_objects = [];
    }

    foreach ($post_type_objects as $post_type => $post_type_object) {
        if (in_array($post_type, $excluded_types, true)) {
            continue;
        }

        $label = isset($post_type_object->labels->singular_name)
            ? (string) $post_type_object->labels->singular_name
            : (string) $post_type;
        $options[(string) $post_type] = $label;
    }

    // Keep critical content types available even when UI flags vary by environment.
    $priority_post_types = ["post", "page", "miasto", "definicja", "portfolio", "marketing_portfolio", "lead_magnet"];
    foreach ($priority_post_types as $post_type) {
        if (!post_type_exists($post_type)) {
            continue;
        }
        if (isset($options[$post_type])) {
            continue;
        }
        $post_type_object = get_post_type_object($post_type);
        $label = ($post_type_object && isset($post_type_object->labels->singular_name))
            ? (string) $post_type_object->labels->singular_name
            : (string) $post_type;
        $options[$post_type] = $label;
    }

    ksort($options);
    return $options;
}

function upsellio_get_selected_content_post_types($raw_post_types)
{
    $available_types = array_keys(upsellio_get_content_post_type_options());
    if (!is_array($raw_post_types)) {
        return $available_types;
    }

    $selected_types = [];
    foreach ($raw_post_types as $post_type) {
        $post_type = sanitize_key((string) $post_type);
        if ($post_type !== "" && in_array($post_type, $available_types, true)) {
            $selected_types[] = $post_type;
        }
    }

    if (empty($selected_types)) {
        return $available_types;
    }

    return array_values(array_unique($selected_types));
}

function upsellio_collect_content_text_rows($post_types)
{
    $post_types = is_array($post_types) ? array_values(array_unique($post_types)) : [];
    if (empty($post_types)) {
        return [];
    }

    $post_ids = get_posts([
        "post_type" => $post_types,
        "post_status" => "any",
        "numberposts" => -1,
        "orderby" => "date",
        "order" => "DESC",
        "fields" => "ids",
    ]);

    if (!is_array($post_ids)) {
        return [];
    }

    $rows = [];
    foreach ($post_ids as $post_id) {
        $post_id = (int) $post_id;
        if ($post_id <= 0) {
            continue;
        }

        $post = get_post($post_id);
        if (!$post instanceof WP_Post || !in_array($post->post_type, $post_types, true)) {
            continue;
        }

        $field_map = [
            "post_title" => (string) $post->post_title,
            "post_excerpt" => (string) $post->post_excerpt,
            "post_content" => (string) $post->post_content,
        ];

        foreach ($field_map as $field_name => $current_text) {
            $rows[] = [
                "row_id" => md5("entry|" . $post->post_type . "|" . $post_id . "|" . $field_name),
                "source" => "wp_content",
                "post_id" => (string) $post_id,
                "post_type" => (string) $post->post_type,
                "post_title" => (string) $post->post_title,
                "post_status" => (string) $post->post_status,
                "slug" => (string) $post->post_name,
                "field" => $field_name,
                "current_text" => $current_text,
                "updated_text" => $current_text,
            ];
        }
    }

    return $rows;
}

function upsellio_handle_content_text_csv_export()
{
    if (!current_user_can("manage_options")) {
        wp_die(esc_html__("Nie masz uprawnień do tego działania.", "upsellio"));
    }

    check_admin_referer("upsellio_export_content_text_csv");

    $raw_post_types = isset($_POST["ups_content_post_types"]) ? wp_unslash($_POST["ups_content_post_types"]) : [];
    $post_types = upsellio_get_selected_content_post_types(is_array($raw_post_types) ? $raw_post_types : []);
    $rows = upsellio_collect_content_text_rows($post_types);
    upsellio_send_csv_download_headers("upsellio-content-texts-" . gmdate("Ymd-His") . ".csv");

    $stream = fopen("php://output", "w");
    if ($stream === false) {
        wp_die(esc_html__("Nie udalo sie wygenerowac pliku CSV.", "upsellio"));
    }

    fputcsv($stream, ["row_id", "source", "post_id", "post_type", "post_title", "post_status", "slug", "field", "current_text", "updated_text"]);
    foreach ($rows as $row) {
        fputcsv($stream, [
            (string) $row["row_id"],
            (string) $row["source"],
            (string) $row["post_id"],
            (string) $row["post_type"],
            (string) $row["post_title"],
            (string) $row["post_status"],
            (string) $row["slug"],
            (string) $row["field"],
            (string) $row["current_text"],
            (string) $row["updated_text"],
        ]);
    }
    fclose($stream);
    exit;
}
add_action("admin_post_upsellio_export_content_text_csv", "upsellio_handle_content_text_csv_export");

function upsellio_handle_content_text_csv_import()
{
    if (!current_user_can("manage_options")) {
        wp_die(esc_html__("Nie masz uprawnień do tego działania.", "upsellio"));
    }

    check_admin_referer("upsellio_import_content_text_csv");

    $redirect_url = add_query_arg(["page" => "upsellio-theme-config"], admin_url("themes.php"));
    if (!isset($_FILES["upsellio_content_text_csv_file"])) {
        wp_safe_redirect(add_query_arg(["content_text_import_error" => "missing_file"], $redirect_url));
        exit;
    }

    $upload = $_FILES["upsellio_content_text_csv_file"];
    if (!is_array($upload) || (int) ($upload["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        wp_safe_redirect(add_query_arg(["content_text_import_error" => "upload_error"], $redirect_url));
        exit;
    }

    $tmp_name = isset($upload["tmp_name"]) ? (string) $upload["tmp_name"] : "";
    if ($tmp_name === "" || !is_uploaded_file($tmp_name)) {
        wp_safe_redirect(add_query_arg(["content_text_import_error" => "invalid_upload"], $redirect_url));
        exit;
    }

    $stream = fopen($tmp_name, "r");
    if ($stream === false) {
        wp_safe_redirect(add_query_arg(["content_text_import_error" => "read_error"], $redirect_url));
        exit;
    }

    $headers = fgetcsv($stream);
    if (!is_array($headers) || empty($headers)) {
        fclose($stream);
        wp_safe_redirect(add_query_arg(["content_text_import_error" => "missing_headers"], $redirect_url));
        exit;
    }

    $header_map = [];
    foreach ($headers as $index => $header_name) {
        $header_map[strtolower(trim((string) $header_name))] = (int) $index;
    }

    $required_headers = ["row_id", "post_id", "post_type", "field"];
    foreach ($required_headers as $required_header) {
        if (!isset($header_map[$required_header])) {
            fclose($stream);
            wp_safe_redirect(add_query_arg(["content_text_import_error" => "missing_required_header"], $redirect_url));
            exit;
        }
    }

    $raw_post_types = isset($_POST["ups_content_post_types"]) ? wp_unslash($_POST["ups_content_post_types"]) : [];
    $allowed_post_types = upsellio_get_selected_content_post_types(is_array($raw_post_types) ? $raw_post_types : []);
    $allowed_fields = ["post_title", "post_excerpt", "post_content"];
    $updates_by_post = [];
    $updated_fields = 0;
    $updated_posts = 0;
    $processed = 0;
    $skipped = 0;

    while (($row = fgetcsv($stream)) !== false) {
        if (!is_array($row) || empty($row)) {
            continue;
        }

        $row_id = isset($row[$header_map["row_id"]]) ? trim((string) $row[$header_map["row_id"]]) : "";
        $post_id = isset($row[$header_map["post_id"]]) ? (int) $row[$header_map["post_id"]] : 0;
        $post_type = isset($row[$header_map["post_type"]]) ? sanitize_key((string) $row[$header_map["post_type"]]) : "";
        $field_name = isset($row[$header_map["field"]]) ? trim((string) $row[$header_map["field"]]) : "";

        if ($row_id === "" || $post_id <= 0 || $post_type === "" || !in_array($field_name, $allowed_fields, true)) {
            $skipped++;
            continue;
        }

        if (!in_array($post_type, $allowed_post_types, true)) {
            $skipped++;
            continue;
        }

        $expected_row_id = md5("entry|" . $post_type . "|" . $post_id . "|" . $field_name);
        if (!hash_equals($expected_row_id, $row_id)) {
            $skipped++;
            continue;
        }

        $post = get_post($post_id);
        if (!$post instanceof WP_Post || $post->post_type !== $post_type) {
            $skipped++;
            continue;
        }

        $next_text = upsellio_find_theme_import_text_from_csv_row($row, $header_map);
        $current_text = (string) $post->{$field_name};
        $processed++;
        if ($next_text === $current_text) {
            continue;
        }

        if (!isset($updates_by_post[$post_id])) {
            $updates_by_post[$post_id] = [
                "ID" => $post_id,
            ];
        }
        $updates_by_post[$post_id][$field_name] = $next_text;
        $updated_fields++;
    }
    fclose($stream);

    if (!empty($updates_by_post)) {
        foreach ($updates_by_post as $post_payload) {
            $result = wp_update_post($post_payload, true);
            if (is_wp_error($result)) {
                $skipped++;
                continue;
            }
            $updated_posts++;
        }
    }

    wp_safe_redirect(
        add_query_arg(
            [
                "content_text_imported" => 1,
                "content_text_import_processed" => $processed,
                "content_text_import_updated_fields" => $updated_fields,
                "content_text_import_updated_posts" => $updated_posts,
                "content_text_import_skipped" => $skipped,
            ],
            $redirect_url
        )
    );
    exit;
}
add_action("admin_post_upsellio_import_content_text_csv", "upsellio_handle_content_text_csv_import");

function upsellio_register_theme_config_menu()
{
    add_submenu_page(
        "themes.php",
        "Konfiguracja dynamiczna",
        "Konfiguracja dynamiczna",
        "manage_options",
        "upsellio-theme-config",
        "upsellio_render_theme_config_screen",
        11
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
    $content_post_type_options = upsellio_get_content_post_type_options();
    ?>
    <div class="wrap">
      <h1>Konfiguracja dynamiczna Upsellio</h1>
      <?php if (isset($_GET["saved"])) : ?>
        <div class="notice notice-success"><p>Zapisano konfiguracje.</p></div>
      <?php endif; ?>
      <?php if (isset($_GET["theme_text_imported"])) : ?>
        <div class="notice notice-success">
          <p>
            <?php
            $processed = isset($_GET["theme_text_import_processed"]) ? (int) $_GET["theme_text_import_processed"] : 0;
            $updated = isset($_GET["theme_text_import_updated"]) ? (int) $_GET["theme_text_import_updated"] : 0;
            $skipped = isset($_GET["theme_text_import_skipped"]) ? (int) $_GET["theme_text_import_skipped"] : 0;
            echo esc_html("Import CSV zakonczony. Przetworzono: {$processed}, zaktualizowano: {$updated}, pominieto: {$skipped}.");
            ?>
          </p>
        </div>
      <?php endif; ?>
      <?php if (isset($_GET["theme_text_import_error"])) : ?>
        <div class="notice notice-error"><p>Import CSV nie udal sie. Sprawdz, czy plik zawiera naglowek row_id i poprawny format CSV.</p></div>
      <?php endif; ?>
      <?php if (isset($_GET["content_text_imported"])) : ?>
        <div class="notice notice-success">
          <p>
            <?php
            $processed_posts = isset($_GET["content_text_import_processed"]) ? (int) $_GET["content_text_import_processed"] : 0;
            $updated_posts_count = isset($_GET["content_text_import_updated_posts"]) ? (int) $_GET["content_text_import_updated_posts"] : 0;
            $updated_fields_count = isset($_GET["content_text_import_updated_fields"]) ? (int) $_GET["content_text_import_updated_fields"] : 0;
            $skipped_posts = isset($_GET["content_text_import_skipped"]) ? (int) $_GET["content_text_import_skipped"] : 0;
            echo esc_html("Import CSV tresci WordPress zakonczony. Przetworzono: {$processed_posts}, zaktualizowano wpisy/strony: {$updated_posts_count}, zmienione pola: {$updated_fields_count}, pominieto: {$skipped_posts}.");
            ?>
          </p>
        </div>
      <?php endif; ?>
      <?php if (isset($_GET["content_text_import_error"])) : ?>
        <div class="notice notice-error"><p>Import CSV tresci WordPress nie udal sie. Upewnij sie, ze plik ma naglowki row_id, post_id, post_type i field.</p></div>
      <?php endif; ?>
      <p>Utrzymuj tresci i linki jako data-driven. Wklej JSON dla linkow specjalnych i sekcji strony glownej.</p>
      <h2>Eksport / import tresci SEO (CSV)</h2>
      <p>Wyeksportuj tresci tekstowe z backendu, popraw je np. w ChatGPT i zaimportuj z powrotem po kolumnie <code>row_id</code> bez zmiany struktury sekcji.</p>
      <p><a href="<?php echo esc_url(upsellio_get_theme_text_csv_export_url()); ?>" class="button">Eksportuj CSV tresci</a></p>
      <form method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" enctype="multipart/form-data" style="margin-bottom:16px;">
        <?php wp_nonce_field("upsellio_import_theme_text_csv"); ?>
        <input type="hidden" name="action" value="upsellio_import_theme_text_csv">
        <input type="file" name="upsellio_theme_text_csv_file" accept=".csv,text/csv" required>
        <button type="submit" class="button button-primary">Importuj CSV tresci</button>
      </form>
      <h2>Eksport / import wszystkich stron i CPT (CSV)</h2>
      <p>Zakres obejmuje cale tresci WordPress dla wybranych typow: <code>post_title</code>, <code>post_excerpt</code>, <code>post_content</code>.</p>
      <form method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" style="margin-bottom:12px;">
        <?php wp_nonce_field("upsellio_export_content_text_csv"); ?>
        <input type="hidden" name="action" value="upsellio_export_content_text_csv">
        <p><strong>Zakres eksportu:</strong></p>
        <p>
          <?php foreach ($content_post_type_options as $post_type_key => $post_type_label) : ?>
            <label style="display:inline-block; margin-right:12px; margin-bottom:8px;">
              <input type="checkbox" name="ups_content_post_types[]" value="<?php echo esc_attr((string) $post_type_key); ?>" checked>
              <?php echo esc_html((string) $post_type_label . " (" . $post_type_key . ")"); ?>
            </label>
          <?php endforeach; ?>
        </p>
        <button type="submit" class="button">Eksportuj CSV tresci WordPress</button>
      </form>
      <form method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" enctype="multipart/form-data" style="margin-bottom:16px;">
        <?php wp_nonce_field("upsellio_import_content_text_csv"); ?>
        <input type="hidden" name="action" value="upsellio_import_content_text_csv">
        <p><strong>Zakres importu:</strong></p>
        <p>
          <?php foreach ($content_post_type_options as $post_type_key => $post_type_label) : ?>
            <label style="display:inline-block; margin-right:12px; margin-bottom:8px;">
              <input type="checkbox" name="ups_content_post_types[]" value="<?php echo esc_attr((string) $post_type_key); ?>" checked>
              <?php echo esc_html((string) $post_type_label . " (" . $post_type_key . ")"); ?>
            </label>
          <?php endforeach; ?>
        </p>
        <input type="file" name="upsellio_content_text_csv_file" accept=".csv,text/csv" required>
        <button type="submit" class="button button-primary">Importuj CSV tresci WordPress</button>
      </form>
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
