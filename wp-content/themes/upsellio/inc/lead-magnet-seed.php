<?php
if (!defined("ABSPATH")) {
    exit;
}

function upsellio_lead_magnet_seed_build_content($item)
{
    $includes = "";
    foreach ((array) ($item["includes"] ?? []) as $include) {
        $includes .= "<li>" . esc_html((string) $include) . "</li>";
    }

    $for_whom = "";
    foreach ((array) ($item["for_whom"] ?? []) as $person) {
        $for_whom .= "<li>" . esc_html((string) $person) . "</li>";
    }

    $title = esc_html((string) ($item["title"] ?? ""));
    $problem = esc_html((string) ($item["problem"] ?? ""));
    $outcome = esc_html((string) ($item["outcome"] ?? ""));
    $keyword = esc_html((string) ($item["keyword"] ?? ""));

    return <<<HTML
<h2>Co znajdziesz w materiale: {$title}</h2>
<p>{$problem}</p>

<h2>Co zawiera materiał</h2>
<ul>
{$includes}
</ul>

<h2>Dla kogo jest ten lead magnet</h2>
<ul>
{$for_whom}
</ul>

<h2>Jak wykorzystać materiał w praktyce</h2>
<p>{$outcome}</p>

<h2>Dlaczego warto pobrać ten materiał</h2>
<p>Materiał pomaga uporządkować decyzje marketingowe i sprzedażowe wokół tematu <strong>{$keyword}</strong>. Zamiast zgadywać, możesz przejść przez konkretne pytania, checklisty i kryteria oceny, które pokazują, co poprawić jako pierwsze.</p>
HTML;
}

function upsellio_get_seeded_lead_magnets()
{
    return [
        [
            "slug" => "checklista-meta-ads",
            "title" => "Checklista kampanii Meta Ads — 25 punktów przed uruchomieniem",
            "excerpt" => "Sprawdź piksel, grupy odbiorców, kreacje, lejek, stronę docelową i śledzenie konwersji zanim włożysz kolejną złotówkę w budżet.",
            "category" => "Meta Ads",
            "category_slug" => "meta-ads",
            "type" => "Checklista",
            "meta" => "PDF · 25 punktów · 12 min",
            "badge" => "Najczęściej pobierany",
            "cta" => "Pobierz checklistę",
            "image" => "https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1600&q=80",
            "keyword" => "checklista Meta Ads do pobrania",
            "problem" => "Kampanie Meta Ads często startują zbyt szybko: bez poprawnego piksela, spójnego lejka, dobrych kreacji i jasnych kryteriów oceny jakości leadów.",
            "outcome" => "Przejdź przez 25 punktów kontrolnych przed startem kampanii, zaznacz braki i popraw elementy, które wpływają na koszt oraz jakość zapytań.",
            "includes" => ["Lista 25 elementów do sprawdzenia przed uruchomieniem kampanii", "Kontrola piksela, zdarzeń i śledzenia konwersji", "Punkty dotyczące grup odbiorców, kreacji, lejka i landing page", "Wskazówki, jak sprawdzić kampanię Facebook przed startem"],
            "for_whom" => ["Właściciele firm i marketerzy przed startem Meta Ads", "Firmy B2B planujące nowy budżet reklamowy", "Zespoły, które chcą uniknąć przepalania kampanii", "Osoby szukające praktycznej checklisty Meta Ads do pobrania"],
            "is_featured" => true,
        ],
        [
            "slug" => "audyt-meta-ads",
            "title" => "Mini audyt Meta Ads — oceń swoją kampanię w 10 minut",
            "excerpt" => "Odpowiedz na 10 pytań diagnostycznych i sprawdź CPL, jakość leadów, remarketing, kreacje oraz spójność lejka.",
            "category" => "Meta Ads",
            "category_slug" => "meta-ads",
            "type" => "Audyt",
            "meta" => "PDF · 10 pytań · 10 min",
            "badge" => "Diagnoza kampanii",
            "cta" => "Pobierz mini audyt",
            "image" => "https://images.unsplash.com/photo-1551281044-8b5bd6fddf8f?auto=format&fit=crop&w=1600&q=80",
            "keyword" => "audyt kampanii Meta Ads",
            "problem" => "Firmy prowadzą Meta Ads, widzą wydatki i leady, ale nie wiedzą, czy kampania naprawdę działa oraz gdzie powstaje największa strata.",
            "outcome" => "Oceń kampanię w kilku obszarach i zobacz, czy problem leży w kreacjach, remarketingu, stronie, jakości leadów czy pomiarze.",
            "includes" => ["10 pytań diagnostycznych z interpretacją wyników", "Ocena CPL, jakości leadów, remarketingu i kreacji", "Sygnały ostrzegawcze pokazujące, że kampania Facebook nie działa", "Priorytety naprawy po przejściu audytu"],
            "for_whom" => ["Firmy, które już prowadzą Meta Ads", "Marketerzy chcący ocenić skuteczność bez zewnętrznego audytora", "Właściciele z rosnącym kosztem pozyskania leada", "Zespoły sprzedaży narzekające na jakość zapytań"],
            "is_featured" => false,
        ],
        [
            "slug" => "szablon-lejka-meta-ads",
            "title" => "Szablon struktury lejka Meta Ads — ToF, MoF, BoF i remarketing",
            "excerpt" => "Ułóż kampanię Meta Ads w logiczny lejek z celami, komunikatami i metrykami dla każdego etapu.",
            "category" => "Meta Ads",
            "category_slug" => "meta-ads",
            "type" => "Szablon",
            "meta" => "PDF · Schemat lejka · 11 min",
            "badge" => "Lejek reklamowy",
            "cta" => "Pobierz szablon",
            "image" => "https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1600&q=80",
            "keyword" => "szablon lejka Meta Ads",
            "problem" => "Pojedyncze reklamy bez struktury rzadko budują popyt i sprzedaż. Brakuje etapów, remarketingu i dopasowania komunikatu do świadomości odbiorcy.",
            "outcome" => "Użyj schematu jako mapy kampanii: zaplanuj ToF, MoF, BoF, remarketing, komunikaty oraz metryki oceny każdego etapu.",
            "includes" => ["Gotowy schemat lejka reklamowego Facebook i Instagram", "Opis celów, komunikatów i metryk dla ToF, MoF i BoF", "Struktura remarketingu dla firm B2B", "Pytania pomagające zbudować lejek reklamowy Facebook"],
            "for_whom" => ["Firmy porządkujące kampanie Meta Ads", "Marketerzy budujący kampanie B2B", "Zespoły chcące odejść od losowego testowania reklam", "Właściciele potrzebujący jasnej struktury lejka"],
            "is_featured" => false,
        ],
        [
            "slug" => "bledy-meta-ads",
            "title" => "7 błędów w kampaniach Meta Ads — lista kontrolna",
            "excerpt" => "Zobacz najczęstsze błędy, ich objawy, skutki i sposoby naprawy, gdy reklamy Facebook nie sprzedają.",
            "category" => "Meta Ads",
            "category_slug" => "meta-ads",
            "type" => "Lista kontrolna",
            "meta" => "PDF · 7 błędów · 8 min",
            "badge" => "Do szybkiej naprawy",
            "cta" => "Sprawdź błędy",
            "image" => "https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=1600&q=80",
            "keyword" => "błędy kampanii Meta Ads",
            "problem" => "Kampanie Meta Ads mogą generować kliknięcia i leady, ale nie sprzedaż. Najczęściej przyczyną są powtarzalne błędy w ofercie, kreacjach, targetowaniu i stronie.",
            "outcome" => "Porównaj swoją kampanię z listą błędów, znajdź najbardziej prawdopodobną przyczynę problemu i zaplanuj pierwszą poprawkę.",
            "includes" => ["7 najczęstszych błędów w kampaniach Meta Ads", "Objawy pokazujące, dlaczego reklamy Facebook nie sprzedają", "Skutki każdego błędu dla budżetu i jakości leadów", "Sposoby naprawy bez przebudowy całej kampanii"],
            "for_whom" => ["Firmy prowadzące Meta Ads bez efektów", "Właściciele szukający konkretnych powodów słabych wyników", "Marketerzy optymalizujący kampanie po pierwszych testach", "Zespoły z niską jakością leadów"],
            "is_featured" => false,
        ],
        [
            "slug" => "checklista-google-ads",
            "title" => "Checklista Google Ads Search — 20 punktów przed uruchomieniem kampanii",
            "excerpt" => "Sprawdź strukturę kampanii, słowa kluczowe, wykluczenia, rozszerzenia, śledzenie konwersji i landing page przed startem Search.",
            "category" => "Google Ads",
            "category_slug" => "google-ads",
            "type" => "Checklista",
            "meta" => "PDF · 20 punktów · 12 min",
            "badge" => "Search",
            "cta" => "Pobierz checklistę",
            "image" => "https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=1600&q=80",
            "keyword" => "checklista Google Ads do pobrania",
            "problem" => "Kampanie Search często startują z niepełną strukturą, zbyt szerokimi słowami, brakiem wykluczeń i niepewnym pomiarem konwersji.",
            "outcome" => "Przejdź przez 20 punktów przed uruchomieniem kampanii i sprawdź, czy konto jest gotowe do wydawania budżetu.",
            "includes" => ["Lista 20 elementów konfiguracji Google Ads Search", "Kontrola słów kluczowych, wykluczeń i rozszerzeń", "Punkty dotyczące śledzenia konwersji i landing page", "Wskazówki, jak skonfigurować kampanię Search krok po kroku"],
            "for_whom" => ["Firmy uruchamiające Google Ads po raz pierwszy", "Zespoły restartujące nieefektywne konto", "Marketerzy planujący kampanię Search", "Właściciele chcący ograniczyć ryzyko przepalania budżetu"],
            "is_featured" => false,
        ],
        [
            "slug" => "kalkulator-cpl",
            "title" => "Kalkulator CPL — ile powinien kosztować lead w Twojej branży",
            "excerpt" => "Policz maksymalny akceptowalny CPL na podstawie wartości klienta, skuteczności sprzedaży i docelowego zwrotu z reklamy.",
            "category" => "Analityka",
            "category_slug" => "analityka",
            "type" => "Kalkulator",
            "meta" => "Arkusz · CPL · 7 min",
            "badge" => "Budżet reklamowy",
            "cta" => "Otwórz kalkulator",
            "image" => "https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=1600&q=80",
            "keyword" => "kalkulator CPL reklamy",
            "problem" => "Bez progu opłacalności łatwo oceniać kampanię po samym koszcie leada, zamiast po potencjale sprzedaży i realnym ROI.",
            "outcome" => "Wpisz wartość klienta, wskaźnik zamknięć i docelowy ROAS, aby policzyć maksymalny CPL, który ma sens dla Twojej firmy.",
            "includes" => ["Pola do wpisania wartości klienta i skuteczności sprzedaży", "Wynik: maksymalny akceptowalny koszt pozyskania leada", "Wskazówki, ile kosztuje pozyskanie leada Google Ads", "Prosty model do planowania budżetu reklamowego"],
            "for_whom" => ["Właściciele firm planujący budżet reklamowy", "Marketerzy rozliczani z CPL i ROI", "Firmy usługowe porównujące kanały pozyskania", "Zespoły, które chcą policzyć opłacalność lead generation"],
            "is_featured" => false,
        ],
        [
            "slug" => "audyt-google-ads",
            "title" => "Mini audyt Google Ads — 12 pytań, które ujawniają problemy konta",
            "excerpt" => "Sprawdź strukturę konta, wykluczenia, Quality Score, strony docelowe, śledzenie i relację budżetu do wyników.",
            "category" => "Google Ads",
            "category_slug" => "google-ads",
            "type" => "Audyt",
            "meta" => "PDF · 12 pytań · 10 min",
            "badge" => "Audyt konta",
            "cta" => "Pobierz audyt",
            "image" => "https://images.unsplash.com/photo-1554224154-22dec7ec8818?auto=format&fit=crop&w=1600&q=80",
            "keyword" => "audyt Google Ads",
            "problem" => "W Google Ads problemem często nie jest sam budżet, tylko jego struktura: zbyt szerokie dopasowania, brak wykluczeń, słabe strony docelowe i niewłaściwe konwersje.",
            "outcome" => "Użyj listy pytań do szybkiej diagnozy konta. Jeśli kilka odpowiedzi wskazuje ryzyko, masz jasną kolejność działań naprawczych.",
            "includes" => ["12 pytań diagnostycznych do konta Google Ads", "Kontrola struktury, wykluczeń, Quality Score i landing page", "Pytania o śledzenie konwersji i budżet", "Interpretacja, jak sprawdzić czy kampania Google działa dobrze"],
            "for_whom" => ["Firmy prowadzące kampanie Search", "Osoby przejmujące konto po agencji", "Zespoły z rosnącym kosztem konwersji", "Marketerzy niepewni konfiguracji konta"],
            "is_featured" => false,
        ],
        [
            "slug" => "slowa-kluczowe-intencja-zakupowa",
            "title" => "Słownik intencji zakupowych — 50 fraz do kampanii Search dla firm B2B",
            "excerpt" => "Zobacz przykładowe frazy z intencją zakupową dla IT, produkcji, usług profesjonalnych i firm lokalnych.",
            "category" => "Google Ads",
            "category_slug" => "google-ads",
            "type" => "Słownik",
            "meta" => "PDF/Arkusz · 50 fraz · 9 min",
            "badge" => "Słowa kluczowe",
            "cta" => "Pobierz słownik",
            "image" => "https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1600&q=80",
            "keyword" => "słowa kluczowe intencja zakupowa B2B",
            "problem" => "Dobór słów kluczowych często zaczyna się od ogólnych fraz, które generują kliknięcia, ale nie prowadzą do zapytań z intencją zakupową.",
            "outcome" => "Użyj słownika jako punktu startowego do kampanii Search i dopasuj przykłady do swojej branży, oferty oraz lokalizacji.",
            "includes" => ["50 przykładowych fraz z intencją zakupową", "Podział na IT, produkcję, usługi profesjonalne i lokalne", "Przykłady fraz do Google Ads dla firm usługowych", "Wskazówki, od czego zacząć dobór słów kluczowych"],
            "for_whom" => ["Firmy zaczynające kampanie Search", "Marketerzy budujący listę słów kluczowych", "Właściciele firm usługowych B2B", "Zespoły chcące ograniczyć kliknięcia bez intencji zakupu"],
            "is_featured" => false,
        ],
        [
            "slug" => "checklista-landing-page",
            "title" => "Checklista landing page — 30 elementów skutecznej strony pod reklamy",
            "excerpt" => "Sprawdź nagłówek, propozycję wartości, CTA, elementy zaufania, formularz, szybkość i tracking przed wysłaniem ruchu z reklam.",
            "category" => "Strony i landing pages",
            "category_slug" => "strony-landing-pages",
            "type" => "Checklista",
            "meta" => "PDF · 30 elementów · 14 min",
            "badge" => "Pod reklamy",
            "cta" => "Pobierz checklistę",
            "image" => "https://images.unsplash.com/photo-1467232004584-a241de8bcf5d?auto=format&fit=crop&w=1600&q=80",
            "keyword" => "checklista landing page B2B",
            "problem" => "Kampania może mieć dobry ruch, ale jeśli strona nie komunikuje wartości, nie buduje zaufania i nie prowadzi do CTA, koszt leada rośnie.",
            "outcome" => "Przejdź przez 30 elementów strony i sprawdź, czy landing page jest gotowy na ruch płatny z Google Ads lub Meta Ads.",
            "includes" => ["30 elementów skutecznej strony pod reklamy", "Kontrola nagłówka, propozycji wartości, CTA i formularza", "Punkty dotyczące zaufania, szybkości, mobile i śledzenia", "Wskazówki, co powinna zawierać strona pod Google Ads"],
            "for_whom" => ["Firmy uruchamiające kampanie płatne", "Marketerzy poprawiający konwersję landing page", "Właściciele stron z ruchem, ale bez zapytań", "Zespoły przygotowujące stronę B2B pod reklamy"],
            "is_featured" => false,
        ],
        [
            "slug" => "audyt-konwersji-strony",
            "title" => "Szablon audytu konwersji strony — oceń, dlaczego strona nie generuje zapytań",
            "excerpt" => "Przejdź przez 15 obszarów diagnostycznych: komunikat, CTA, zaufanie, formularz, mobile i szybkość.",
            "category" => "Strony i landing pages",
            "category_slug" => "strony-landing-pages",
            "type" => "Audyt",
            "meta" => "PDF · 15 obszarów · 13 min",
            "badge" => "CRO",
            "cta" => "Pobierz audyt",
            "image" => "https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1600&q=80",
            "keyword" => "audyt konwersji strony internetowej",
            "problem" => "Strona może wyglądać dobrze i mieć ruch, ale nadal nie generować zapytań, jeśli użytkownik nie rozumie oferty, nie ufa firmie albo nie widzi jasnego następnego kroku.",
            "outcome" => "Oceń stronę w 15 obszarach i zobacz, które elementy najpewniej blokują konwersję.",
            "includes" => ["15 obszarów audytu konwersji strony", "Pytania diagnostyczne o komunikat, CTA, zaufanie i formularz", "Kontrola mobile, szybkości i pierwszego wrażenia", "Wskazówki, dlaczego strona www nie sprzedaje"],
            "for_whom" => ["Firmy z ruchem, ale zbyt małą liczbą zapytań", "Właściciele planujący przebudowę strony", "Marketerzy poprawiający współczynnik konwersji", "Firmy usługowe i B2B przed startem kampanii"],
            "is_featured" => false,
        ],
        [
            "slug" => "koszty-marketingu-b2b",
            "title" => "Przewodnik po kosztach marketingu B2B — ile wydawać i na co",
            "excerpt" => "Zaplanuj budżet między Meta Ads, Google Ads i stronę, policz ROI z marketingu i sprawdź benchmarki CPL.",
            "category" => "Lead generation",
            "category_slug" => "lead-generation",
            "type" => "Przewodnik",
            "meta" => "PDF · Budżet i CPL · 15 min",
            "badge" => "Budżet B2B",
            "cta" => "Pobierz przewodnik",
            "image" => "https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&w=1600&q=80",
            "keyword" => "ile wydawać na marketing B2B",
            "problem" => "Firmy często planują budżet reklamowy bez powiązania z wartością klienta, kosztem leada, stroną i możliwościami sprzedaży.",
            "outcome" => "Użyj przewodnika, żeby oszacować, ile wydawać na marketing B2B, na co przeznaczyć budżet i jak mierzyć zwrot.",
            "includes" => ["Podział budżetu między Meta Ads, Google Ads i stronę", "Sposób liczenia ROI z marketingu B2B", "Benchmarki CPL i pytania do planowania budżetu", "Wskazówki dla firm usługowych planujących kampanie"],
            "for_whom" => ["Właściciele firm planujący inwestycje marketingowe", "Firmy usługowe przed startem reklam", "Marketerzy uzasadniający budżet", "Zespoły chcące zoptymalizować wydatki"],
            "is_featured" => false,
        ],
        [
            "slug" => "szablon-briefu-marketingowego",
            "title" => "Szablon briefu marketingowego — co powiedzieć agencji lub freelancerowi",
            "excerpt" => "Przygotuj cel, grupę docelową, budżet, aktualne problemy, oczekiwane efekty, timeline i KPI przed rozmową o współpracy.",
            "category" => "Lead generation",
            "category_slug" => "lead-generation",
            "type" => "Szablon",
            "meta" => "PDF/DOC · Brief · 12 min",
            "badge" => "Przed współpracą",
            "cta" => "Pobierz szablon",
            "image" => "https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=1600&q=80",
            "keyword" => "szablon briefu marketingowego",
            "problem" => "Bez dobrego briefu agencja lub freelancer dostaje zbyt mało informacji, a rozmowa szybko schodzi na narzędzia zamiast celów, problemów i KPI.",
            "outcome" => "Wypełnij brief przed rozmową i przygotuj zapytanie, które ułatwia wycenę, plan działania oraz ocenę dopasowania partnera.",
            "includes" => ["Gotowy szablon briefu marketingowego", "Pola na cel, grupę docelową, budżet i problemy", "Sekcje dotyczące efektów, timeline i KPI", "Wskazówki, jak przygotować brief dla agencji"],
            "for_whom" => ["Firmy planujące współpracę z agencją", "Właściciele wysyłający zapytanie ofertowe", "Marketerzy porządkujący wymagania projektu", "Firmy wybierające freelancera lub partnera marketingowego"],
            "is_featured" => false,
        ],
    ];
}

function upsellio_seed_lead_magnets($force = false)
{
    if (!post_type_exists("lead_magnet")) {
        return ["created" => 0, "updated" => 0, "message" => "lead_magnet_post_type_missing"];
    }

    $items = upsellio_get_seeded_lead_magnets();
    if ($force) {
        $deprecated_seed_slugs = [
            "checklista-meta-ads-b2b",
            "audyt-google-ads-checklista",
            "szablon-landing-page-b2b",
            "kalkulator-jakosci-leadow",
            "brief-strony-internetowej",
            "plan-90-dni-marketing-b2b",
        ];

        foreach ($deprecated_seed_slugs as $deprecated_slug) {
            $deprecated_post = get_page_by_path($deprecated_slug, OBJECT, "lead_magnet");
            if ($deprecated_post instanceof WP_Post) {
                wp_trash_post((int) $deprecated_post->ID);
            }
        }
    }

    $created = 0;
    $updated = 0;

    foreach ($items as $index => $item) {
        $slug = sanitize_title((string) ($item["slug"] ?? ""));
        if ($slug === "") {
            continue;
        }

        $existing_post = get_page_by_path($slug, OBJECT, "lead_magnet");
        $post_data = [
            "post_type" => "lead_magnet",
            "post_status" => "publish",
            "post_title" => (string) ($item["title"] ?? ""),
            "post_name" => $slug,
            "post_excerpt" => (string) ($item["excerpt"] ?? ""),
            "post_content" => upsellio_lead_magnet_seed_build_content($item),
            "menu_order" => $index,
        ];

        if ($existing_post instanceof WP_Post) {
            if (!$force) {
                continue;
            }
            $post_data["ID"] = (int) $existing_post->ID;
            $post_id = wp_update_post($post_data, true);
            if (is_wp_error($post_id) || (int) $post_id <= 0) {
                continue;
            }
            $updated++;
        } else {
            $post_id = wp_insert_post($post_data, true);
            if (is_wp_error($post_id) || (int) $post_id <= 0) {
                continue;
            }
            $created++;
        }

        $term_name = (string) ($item["category"] ?? "Lead generation");
        $term_slug = sanitize_title((string) ($item["category_slug"] ?? $term_name));
        $term = term_exists($term_slug, "lead_magnet_category");
        if (!$term) {
            $term = wp_insert_term($term_name, "lead_magnet_category", ["slug" => $term_slug]);
        }
        if (!is_wp_error($term)) {
            $term_id = (int) (is_array($term) ? ($term["term_id"] ?? 0) : 0);
            if ($term_id > 0) {
                wp_set_object_terms((int) $post_id, [$term_id], "lead_magnet_category");
            }
        }

        update_post_meta((int) $post_id, "_ups_lm_type", (string) ($item["type"] ?? ""));
        update_post_meta((int) $post_id, "_ups_lm_meta", (string) ($item["meta"] ?? ""));
        update_post_meta((int) $post_id, "_ups_lm_badge", (string) ($item["badge"] ?? ""));
        update_post_meta((int) $post_id, "_ups_lm_cta", (string) ($item["cta"] ?? ""));
        update_post_meta((int) $post_id, "_ups_lm_image", esc_url_raw((string) ($item["image"] ?? "")));
        update_post_meta((int) $post_id, "_ups_lm_featured", !empty($item["is_featured"]) ? "1" : "0");
        update_post_meta((int) $post_id, "_upsellio_is_lead_magnet", "1");
    }

    return ["created" => $created, "updated" => $updated, "message" => "ok"];
}

function upsellio_get_lead_magnet_seed_url($force = false)
{
    return add_query_arg([
        "upsellio_seed_lead_magnets" => 1,
        "force" => $force ? 1 : 0,
        "_upsellio_nonce" => wp_create_nonce("upsellio_seed_lead_magnets"),
    ], admin_url("edit.php?post_type=lead_magnet&page=upsellio-lead-magnet-seed"));
}

function upsellio_handle_lead_magnet_seed_request()
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }
    if (!isset($_GET["upsellio_seed_lead_magnets"])) {
        return;
    }

    $nonce = isset($_GET["_upsellio_nonce"]) ? sanitize_text_field(wp_unslash($_GET["_upsellio_nonce"])) : "";
    if (!wp_verify_nonce($nonce, "upsellio_seed_lead_magnets")) {
        return;
    }

    $force = isset($_GET["force"]) && (int) $_GET["force"] === 1;
    $result = upsellio_seed_lead_magnets($force);
    update_option("upsellio_lead_magnet_seed_v1_done", "1");

    $redirect_url = add_query_arg([
        "upsellio_lead_magnet_seed_done" => 1,
        "created" => (int) ($result["created"] ?? 0),
        "updated" => (int) ($result["updated"] ?? 0),
        "msg" => (string) ($result["message"] ?? "ok"),
    ], admin_url("edit.php?post_type=lead_magnet&page=upsellio-lead-magnet-seed"));
    wp_safe_redirect($redirect_url);
    exit;
}
add_action("admin_init", "upsellio_handle_lead_magnet_seed_request");

function upsellio_register_lead_magnet_seed_menu()
{
    if (!post_type_exists("lead_magnet")) {
        return;
    }

    add_submenu_page(
        "edit.php?post_type=lead_magnet",
        "Generator lead magnetów",
        "Generator materiałów",
        "manage_options",
        "upsellio-lead-magnet-seed",
        "upsellio_lead_magnet_seed_screen",
        32
    );
}
add_action("admin_menu", "upsellio_register_lead_magnet_seed_menu");

function upsellio_lead_magnet_seed_screen()
{
    if (!current_user_can("manage_options")) {
        return;
    }
    ?>
    <div class="wrap">
      <h1>Generator lead magnetów</h1>
      <p>Tworzy gotowe materiały w typie treści <code>lead_magnet</code>. Po wygenerowaniu pojawią się automatycznie na podstronie materiałów.</p>
      <p><a class="button button-primary" href="<?php echo esc_url(upsellio_get_lead_magnet_seed_url(false)); ?>">Wgraj brakujące materiały do bazy</a></p>
      <p><a class="button" href="<?php echo esc_url(upsellio_get_lead_magnet_seed_url(true)); ?>">Nadpisz i odśwież wszystkie materiały</a></p>
    </div>
    <?php
}

function upsellio_lead_magnet_seed_notice()
{
    if (!is_admin() || !isset($_GET["upsellio_lead_magnet_seed_done"])) {
        return;
    }

    $created = isset($_GET["created"]) ? (int) $_GET["created"] : 0;
    $updated = isset($_GET["updated"]) ? (int) $_GET["updated"] : 0;
    $msg = isset($_GET["msg"]) ? sanitize_text_field(wp_unslash($_GET["msg"])) : "ok";
    if ($msg !== "ok") {
        echo '<div class="notice notice-error"><p>Nie udało się wygenerować lead magnetów.</p></div>';
        return;
    }

    echo '<div class="notice notice-success"><p>';
    echo esc_html("Lead magnety zaktualizowane. Utworzono: {$created}, zaktualizowano: {$updated}.");
    echo "</p></div>";
}
add_action("admin_notices", "upsellio_lead_magnet_seed_notice");
