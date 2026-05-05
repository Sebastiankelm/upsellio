<?php
/*
Template Name: Upsellio - Strona Glowna
Template Post Type: page
*/
if (!defined("ABSPATH")) {
    exit;
}

$cfg = function_exists("upsellio_get_front_page_content_config") ? upsellio_get_front_page_content_config() : [];
$seo = is_array($cfg["seo"] ?? null) ? $cfg["seo"] : [];
$contact_phone = function_exists("upsellio_get_contact_phone") ? upsellio_get_contact_phone() : "+48 575 522 595";
$contact_email = trim((string) ($cfg["contact_email"] ?? "kontakt@upsellio.pl"));
$linkedin_url = "https://www.linkedin.com/in/kelm-sebastian/";
$site_url = home_url("/");

$google_ads_url = function_exists("upsellio_get_google_ads_page_url") ? trim((string) upsellio_get_google_ads_page_url()) : "";
$meta_ads_url = function_exists("upsellio_get_meta_ads_page_url") ? trim((string) upsellio_get_meta_ads_page_url()) : "";
$websites_url = function_exists("upsellio_get_websites_page_url") ? trim((string) upsellio_get_websites_page_url()) : "";
$marketing_portfolio_url = function_exists("upsellio_get_marketing_portfolio_page_url") ? trim((string) upsellio_get_marketing_portfolio_page_url()) : "";
if ($google_ads_url === "") {
    $google_ads_url = home_url("/marketing-google-ads/");
}
if ($meta_ads_url === "") {
    $meta_ads_url = home_url("/marketing-meta-ads/");
}
if ($websites_url === "") {
    $websites_url = home_url("/tworzenie-stron-internetowych/");
}
if ($marketing_portfolio_url === "") {
    $marketing_portfolio_url = home_url("/portfolio-marketingowe/");
}

$seo_title = trim((string) ($seo["title"] ?? "Upsellio — Marketing B2B, Google Ads, Meta Ads | Sebastian Kelm"));
$seo_description = trim((string) ($seo["description"] ?? "Marketing B2B nastawiony na leady i sprzedaż. Google Ads, Meta Ads i strony internetowe dla firm. Sebastian Kelm — praktyk sprzedaży i marketingu B2B."));
$seo_og_title = trim((string) ($seo["og_title"] ?? "Upsellio — marketing, który generuje klientów, nie kliknięcia"));
$seo_og_description = trim((string) ($seo["og_description"] ?? "Google Ads, Meta Ads, SEO i strony internetowe dla firm B2B. System marketingowy, który prowadzi od kliknięcia do zapytania."));
$seo_og_image = function_exists("upsellio_get_default_og_image_url") ? upsellio_get_default_og_image_url() : (get_template_directory_uri() . "/assets/images/upsellio-logo.png");

$home_faqs = [
    [
        "q" => "Ile kosztuje kampania Google Ads lub Meta Ads dla firmy B2B?",
        "a" => "Koszt kampanii zależy od budżetu reklamowego i zakresu obsługi. Budżet reklamowy ustalamy wspólnie przed startem — minimum to zwykle 2 000–3 000 zł/mies. na platformę. Do tego doliczamy wynagrodzenie za prowadzenie: zależy od zakresu i liczby kampanii. Na bezpłatnej rozmowie omówimy realistyczne widełki dla Twojej sytuacji.",
    ],
    [
        "q" => "Po jakim czasie widać efekty kampanii B2B?",
        "a" => "Pierwsze sygnały poprawy (więcej ruchu, pierwsze leady) pojawiają się zwykle po 2–4 tygodniach. Stabilny, przewidywalny napływ kwalifikowanych leadów wymaga 2–3 miesięcy optymalizacji. Działamy iteracyjnie: analiza → wdrożenie → pomiar → kolejne ulepszenia.",
    ],
    [
        "q" => "Czy sama reklama wystarczy, żeby pozyskiwać klientów B2B?",
        "a" => "Zazwyczaj nie. Jeśli strona ma słaby przekaz lub nie prowadzi odwiedzającego do decyzji, nawet dobra kampania będzie przeciekać. Dlatego zawsze patrzę na całość: reklama + strona + oferta + formularz. To system, nie pojedynczy kanał.",
    ],
    [
        "q" => "Czy obsługujesz tylko reklamy, czy też tworzenie stron internetowych?",
        "a" => "Obsługuję oba obszary: kampanie Google Ads i Meta Ads oraz projektowanie stron WWW i landing page. Najlepsze wyniki daje połączenie tych dwóch elementów w jednym procesie — reklama prowadzi na stronę, a strona konwertuje ruch w leady.",
    ],
    [
        "q" => "Dla jakich firm jest ta współpraca?",
        "a" => "Głównie dla firm B2B (usługi, produkcja, IT, SaaS), e-commerce B2B i firm usługowych z ambicją wzrostu. Kluczowe jest, żeby firma miała realny produkt lub usługę, określoną grupę klientów i gotowość do rozmowy o wynikach — nie tylko o kliknięciach.",
    ],
    [
        "q" => "Czy pracujesz jako agencja, czy samodzielnie?",
        "a" => "Pracuję samodzielnie. Nie ma juniorów, nie ma rotującego zespołu. Twoje kampanie prowadzę ja — Sebastian — od początku do końca. To wada (ograniczona przepustowość) i zaleta (stały kontakt z jedną osobą, która zna Twój projekt na wylot).",
    ],
    [
        "q" => "Co konkretnie zmienia się na stronie po takiej współpracy?",
        "a" => "Najczęściej porządkuję przekaz, doprecyzowuję ofertę, wzmacniam CTA, dodaję potrzebne elementy budujące zaufanie i upraszczam drogę do kontaktu. Zmiany są zawsze pod konkretny cel konwersji, nie pod estetykę.",
    ],
];

add_filter("pre_get_document_title", static function ($title) use ($seo_title) {
    return is_front_page() && $seo_title !== "" ? $seo_title : $title;
});
add_action("wp_head", static function () use ($seo_description, $seo_og_title, $seo_og_description, $site_url, $seo_og_image) {
    if (!is_front_page()) {
        return;
    }
    echo '<meta name="description" content="' . esc_attr($seo_description) . '">' . "\n";
    echo '<link rel="canonical" href="' . esc_url($site_url) . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($seo_og_title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($seo_og_description) . '">' . "\n";
    echo '<meta property="og:type" content="website">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($site_url) . '">' . "\n";
    echo '<meta property="og:image" content="' . esc_url($seo_og_image) . '">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:image" content="' . esc_url($seo_og_image) . '">' . "\n";
}, 1);

get_header();

include get_template_directory() . "/template-parts/home/front-page-sellwise-markup.php";

get_footer();
