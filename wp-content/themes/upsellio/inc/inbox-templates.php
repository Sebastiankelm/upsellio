<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_inbox_default_quick_templates(): array
{
    return [
        "tpl_1" => ["label" => "Dzieki, oddzwonie w 24h", "body" => "Czesc {first_name},\n\nDziekuje za wiadomosc. Wracam do Ciebie w ciagu 24h z konkretna odpowiedzia.\n\nPozdrawiam,\nSebastian"],
        "tpl_2" => ["label" => "Prosba o brief", "body" => "Czesc {first_name},\n\nDzieki za kontakt. Zeby przygotowac sensowna propozycje dla {lead_company}, podejrzesz prosze:\n- cel na najblizsze 90 dni,\n- aktualny budzet,\n- najwieksza blokade.\n\nWtedy wracam z konkretami."],
        "tpl_3" => ["label" => "Propozycja calla", "body" => "Czesc {first_name},\n\nNajprosciej bedzie to domknac na krotkim callu. Pasuje Ci ktorys termin?\n- wt 10:00\n- sr 13:30\n- czw 16:00\n\nMozesz tez od razu wybrac termin: https://calendly.com/upsellio"],
        "tpl_4" => ["label" => "Domkniete / oddzwonione", "body" => "Czesc {first_name},\n\nDzieki za rozmowe - potwierdzam, temat po naszej stronie jest domkniety.\nGdyby cos sie zmienilo, odpisz smialo i wracamy do tematu."],
        "tpl_5" => ["label" => "Nie pasujemy + polecenie", "body" => "Czesc {first_name},\n\nPo analizie widze, ze na tym etapie nie bedziemy najlepszym fit dla {lead_company}.\nSzczerze polecam rozwazyc wspolprace z partnerem specjalizujacym sie stricte w {quiz_industry}.\n\nJesli bedziesz chcial/chciala, moge podrzucic 1-2 kontakty."],
        "tpl_6" => ["label" => "Potwierdzenie odbioru", "body" => "Czesc {first_name},\n\nPotwierdzam odbior i wracam z odpowiedzia najpozniej jutro do 12:00."],
        "tpl_7" => ["label" => "Follow-up po ciszy", "body" => "Czesc {first_name},\n\nDopytam krotko, czy temat po stronie {lead_company} jest dalej aktualny.\nJesli tak, moge zaproponowac nastepny krok jeszcze w tym tygodniu."],
        "tpl_8" => ["label" => "Odpowiedz na cene", "body" => "Czesc {first_name},\n\nRozumiem pytanie o cene. Dla takiego zakresu zwykle pracujemy w widekach uzaleznionych od celu i tempa wdrozenia.\nJesli chcesz, rozpisze 2 warianty z roznym zakresem i efektem."],
        "tpl_9" => ["label" => "Proponowany nastepny krok", "body" => "Czesc {first_name},\n\nZ mojej strony najlepszy nastepny krok to krotka diagnoza i decyzja, czy idziemy w pelna wspolprace.\nDaj znac, czy mam podeslac plan na 30 dni dla {lead_company}."],
    ];
}

function upsellio_inbox_quick_templates(): array
{
    $stored = get_option("ups_inbox_quick_templates", []);
    $defaults = upsellio_inbox_default_quick_templates();
    if (!is_array($stored)) {
        $stored = [];
    }
    $merged = [];
    foreach ($defaults as $key => $row) {
        $stored_row = isset($stored[$key]) && is_array($stored[$key]) ? $stored[$key] : [];
        $label = trim((string) ($stored_row["label"] ?? $row["label"]));
        $body = trim((string) ($stored_row["body"] ?? $row["body"]));
        $merged[$key] = [
            "label" => $label !== "" ? $label : (string) $row["label"],
            "body" => $body !== "" ? $body : (string) $row["body"],
        ];
    }
    return $merged;
}

function upsellio_inbox_find_offer_lead_id(int $offer_id): int
{
    $offer_id = (int) $offer_id;
    if ($offer_id <= 0 || !post_type_exists("crm_lead")) {
        return 0;
    }
    $lead_ids = get_posts([
        "post_type" => "crm_lead",
        "post_status" => ["publish", "draft", "pending", "private"],
        "posts_per_page" => 1,
        "fields" => "ids",
        "orderby" => "modified",
        "order" => "DESC",
        "meta_query" => [["key" => "_ups_lead_converted_offer_id", "value" => $offer_id]],
    ]);
    return !empty($lead_ids) ? (int) $lead_ids[0] : 0;
}
