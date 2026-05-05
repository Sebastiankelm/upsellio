<?php
/*
Template Name: Upsellio - Kontakt
Template Post Type: page
*/
if (!defined("ABSPATH")) {
    exit;
}

$front_page_sections = function_exists("upsellio_get_front_page_content_config")
    ? upsellio_get_front_page_content_config()
    : [];
$contact_phone = function_exists("upsellio_get_contact_phone")
    ? upsellio_get_contact_phone()
    : trim((string) ($front_page_sections["contact_phone"] ?? ""));
if ($contact_phone === "") {
    $contact_phone = "+48 575 522 595";
}
$contact_email = trim((string) ($front_page_sections["contact_email"] ?? "kontakt@upsellio.pl"));
$contact_page_url = home_url("/kontakt/");
$linkedin_url = "https://www.linkedin.com/in/kelm-sebastian/";

get_header();

$contact_service_options = [
    "Kampanie Google Ads B2B",
    "Kampanie Meta Ads B2B",
    "Tworzenie strony / landing page pod leady",
    "Marketing + strona (oba obszary razem)",
    "Audyt istniejących kampanii lub strony",
    "Nie wiem — chcę porozmawiać o problemie",
];

$contact_faq_items = [
    [
        "question" => "Czy pierwsza diagnoza i rozmowa są płatne?",
        "answer"   => "Nie. Pierwsza rozmowa i wstępna ocena sytuacji są bezpłatne. Jeśli temat wymaga głębszego audytu z dokumentacją, ustalamy osobny zakres. Ale diagnoza „gdzie i dlaczego tracisz zapytania” — to zawsze bezpłatnie.",
    ],
    [
        "question" => "Czy muszę mieć gotowy brief lub szczegółowy opis projektu?",
        "answer"   => "Nie. Wystarczy krótki opis sytuacji: co robisz, co nie działa tak jak powinno i jaki efekt chcesz osiągnąć. Pytania uzupełniające zadaję sam podczas rozmowy.",
    ],
    [
        "question" => "Czy mogę napisać, jeśli nie wiem, czy problem leży w reklamach czy na stronie?",
        "answer"   => "Tak — i to jest bardzo częsta sytuacja. Patrzę na cały lejek: źródło ruchu, komunikat reklamowy, stronę docelową, formularz i dalszy kontakt z leadem. Pomogę zlokalizować, gdzie jest faktyczny problem.",
    ],
    [
        "question" => "Czy od razu dostanę ofertę cenową?",
        "answer"   => "Nie wysyłam szablonowych ofert bez zrozumienia problemu. Najpierw musimy porozmawiać o sytuacji — dopiero potem mogę przygotować zakres i wycenę, które mają realny sens biznesowy.",
    ],
    [
        "question" => "Dla jakich firm ta współpraca ma największy sens?",
        "answer"   => "Dla firm B2B (usługi, produkcja, IT, SaaS), e-commerce B2B i firm usługowych, które mają już stronę lub kampanie reklamowe, ale nie są zadowolone z jakości zapytań lub sprzedaży. Minimalna wielkość firmy: 3–5 osób.",
    ],
    [
        "question" => "Czy mogę zgłosić samą stronę bez kampanii reklamowych?",
        "answer"   => "Tak. Możesz zgłosić samą stronę, landing page, sklep internetowy lub formularz kontaktowy. Poprawa konwersji strony często daje więcej niż dokładanie budżetu do reklam — i kosztuje mniej.",
    ],
    [
        "question" => "Ile trwa odpowiedź na zgłoszenie?",
        "answer"   => "Odpowiadam w ciągu 24 godzin w dni robocze. Zwykle szybciej. Jeśli sprawa jest pilna, napisz to w formularzu — priorytetuję takie zgłoszenia.",
    ],
    [
        "question" => "Jaki jest minimalny budżet reklamowy, żeby współpraca miała sens?",
        "answer"   => "Minimalny sensowny budżet to ok. 2 000–3 000 zł miesięcznie na platformę (Google Ads lub Meta Ads). Przy niższych kwotach optymalizacja kampanii jest ograniczona przez zbyt mało danych. Wyjątek: audyt lub projektowanie strony — tu budżet reklamowy nie ma znaczenia.",
    ],
];
?>

<script type="application/ld+json">
<?php echo wp_json_encode([
    "@context"        => "https://schema.org",
    "@type"           => "BreadcrumbList",
    "itemListElement" => [
        ["@type" => "ListItem", "position" => 1, "name" => "Strona główna", "item" => home_url("/")],
        ["@type" => "ListItem", "position" => 2, "name" => "Kontakt i bezpłatna diagnoza", "item" => $contact_page_url],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
</script>

<style>
  .ct-art {
    font-family: "DM Sans", system-ui, sans-serif;
    color: #0f172a;
    line-height: 1.65;
  }

  .ct-art *,
  .ct-art *::before,
  .ct-art *::after {
    box-sizing: border-box;
  }

  /* CONTACT PAGE V2 */

  .ct-hero-form {
    padding: 68px 0 88px;
    background:
      radial-gradient(ellipse at 86% 12%, rgba(13, 148, 136, 0.18), transparent 38%),
      radial-gradient(ellipse at 10% 90%, rgba(13, 148, 136, 0.08), transparent 32%),
      linear-gradient(180deg, #ffffff 0%, #f8fafc 55%, #eef6f7 100%);
  }

  .ct-wrap {
    max-width: 1180px;
    margin: 0 auto;
    padding: 0 22px;
  }

  .ct-hero-form-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 480px;
    gap: 56px;
    align-items: start;
  }

  .ct-hero-copy {
    padding-top: 16px;
  }

  .ct-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
    color: #0f766e;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 1.4px;
    text-transform: uppercase;
  }

  .ct-eyebrow::before {
    content: '';
    display: block;
    width: 20px;
    height: 2px;
    background: #0d9488;
    border-radius: 2px;
  }

  .ct-h1 {
    max-width: 720px;
    margin: 0;
    font-family: "Syne", sans-serif;
    font-size: clamp(38px, 5.2vw, 68px);
    line-height: 0.97;
    letter-spacing: -2.5px;
    color: #0f172a;
  }

  .ct-h1-accent {
    color: #0d9488;
  }

  .ct-lead {
    max-width: 680px;
    margin: 22px 0 0;
    color: #475569;
    font-size: 17px;
    line-height: 1.7;
  }

  .ct-consult-box {
    max-width: 660px;
    margin-top: 26px;
    padding: 20px 24px;
    background: #ffffff;
    border: 1px solid #dbe7ea;
    border-left: 4px solid #0d9488;
    border-radius: 20px;
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
  }

  .ct-consult-box strong {
    display: block;
    font-family: "Syne", sans-serif;
    font-size: 18px;
    line-height: 1.2;
    color: #0f172a;
    margin-bottom: 7px;
  }

  .ct-consult-box p {
    margin: 0;
    color: #475569;
    line-height: 1.6;
  }

  .ct-not-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 16px;
    max-width: 660px;
  }

  .ct-not-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    font-size: 14px;
    color: #475569;
    padding: 10px 14px;
    background: #fff;
    border: 1px solid #dbe7ea;
    border-radius: 12px;
  }

  .ct-not-icon {
    font-size: 15px;
    flex-shrink: 0;
    margin-top: 1px;
  }

  .ct-proof-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 10px;
    max-width: 720px;
    margin-top: 18px;
  }

  .ct-proof-cell {
    background: #ffffff;
    border: 1px solid #dbe7ea;
    border-radius: 18px;
    padding: 16px;
    text-align: center;
  }

  .ct-proof-cell strong {
    display: block;
    font-family: "Syne", sans-serif;
    font-size: 26px;
    line-height: 1;
    letter-spacing: -1px;
    color: #0d9488;
    margin-bottom: 5px;
  }

  .ct-proof-cell span {
    display: block;
    color: #64748b;
    font-size: 12px;
    line-height: 1.4;
  }

  .ct-form-card {
    position: sticky;
    top: 88px;
    background: #ffffff;
    border: 1px solid #dbe7ea;
    border-radius: 26px;
    padding: 30px;
    box-shadow: 0 24px 70px rgba(15, 23, 42, 0.12);
  }

  .ct-form-head {
    margin-bottom: 20px;
  }

  .ct-form-head span {
    display: block;
    margin-bottom: 8px;
    color: #0f766e;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 1.3px;
    text-transform: uppercase;
  }

  .ct-form-head h2 {
    margin: 0 0 8px;
    font-family: "Syne", sans-serif;
    font-size: 30px;
    line-height: 1.06;
    letter-spacing: -1px;
    color: #0f172a;
  }

  .ct-form-head p {
    margin: 0;
    color: #64748b;
    font-size: 14px;
    line-height: 1.55;
  }

  /* Formularz: NIE stosuj width:100% / min-height do checkboxów — rozjechałby układ zgody */
  .ct-form-card input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),
  .ct-form-card select,
  .ct-form-card textarea {
    width: 100%;
    min-height: 48px;
    background: #ffffff;
    border: 1px solid #dbe7ea;
    border-radius: 14px;
    padding: 12px 14px;
    color: #0f172a;
    font-size: 14px;
  }

  .ct-form-card textarea {
    min-height: 120px;
    resize: vertical;
  }

  .ct-form-card .ups-form__consent {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    width: 100%;
    max-width: 100%;
    min-width: 0;
  }

  .ct-form-card .ups-form__consent input[type="checkbox"] {
    width: 18px;
    height: 18px;
    min-height: 18px;
    margin: 3px 0 0;
    flex-shrink: 0;
    padding: 0;
    border-radius: 4px;
    accent-color: #0d9488;
  }

  .ct-form-card .ups-form__consent span {
    flex: 1;
    min-width: 0;
    line-height: 1.5;
  }

  .ct-form-card .ups-form {
    min-width: 0;
    width: 100%;
  }

  .ct-form-card input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):focus,
  .ct-form-card select:focus,
  .ct-form-card textarea:focus {
    border-color: #0d9488;
    box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.14);
    outline: none;
  }

  .ct-form-card button,
  .ct-form-card input[type="submit"] {
    width: 100%;
    min-height: 52px;
    border: 0;
    border-radius: 999px;
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
    color: #ffffff;
    font-size: 14px;
    font-weight: 900;
    cursor: pointer;
    box-shadow: 0 16px 36px rgba(13, 148, 136, 0.25);
  }

  .ct-form-after {
    margin-top: 16px;
    padding: 16px;
    background: #f8fafc;
    border: 1px solid #dbe7ea;
    border-radius: 18px;
  }

  .ct-form-after strong {
    display: block;
    font-size: 14px;
    color: #0f172a;
    margin-bottom: 5px;
  }

  .ct-form-after p {
    margin: 0;
    color: #64748b;
    font-size: 13px;
    line-height: 1.5;
  }

  /* TOPICS */

  .ct-topics,
  .ct-process,
  .ct-faq {
    padding: 88px 0;
    background: #ffffff;
  }

  .ct-process {
    background: #f8fafc;
  }

  .ct-topics.ct-opinions {
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
  }

  .ct-section-head {
    max-width: 760px;
    margin-bottom: 30px;
  }

  .ct-section-head span {
    display: inline-flex;
    margin-bottom: 10px;
    color: #0f766e;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 1.3px;
    text-transform: uppercase;
  }

  .ct-section-head h2 {
    margin: 0;
    font-family: "Syne", sans-serif;
    font-size: clamp(32px, 4vw, 52px);
    line-height: 1;
    letter-spacing: -2px;
    color: #0f172a;
  }

  .ct-section-head p {
    margin: 14px 0 0;
    color: #475569;
    font-size: 17px;
    line-height: 1.65;
  }

  .ct-topic-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
  }

  .ct-topic-card {
    background: #ffffff;
    border: 1px solid #dbe7ea;
    border-radius: 22px;
    padding: 26px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
    transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
  }

  .ct-topic-card:hover {
    border-color: #0d9488;
    transform: translateY(-2px);
    box-shadow: 0 14px 36px rgba(13, 148, 136, 0.12);
  }

  .ct-topic-icon {
    display: grid;
    place-items: center;
    width: 46px;
    height: 46px;
    border-radius: 14px;
    background: #ccfbf1;
    margin-bottom: 14px;
    font-size: 22px;
  }

  .ct-topic-card strong {
    display: block;
    margin-bottom: 8px;
    font-family: "Syne", sans-serif;
    font-size: 19px;
    line-height: 1.15;
    color: #0f172a;
  }

  .ct-topic-card p {
    margin: 0;
    color: #475569;
    font-size: 14px;
    line-height: 1.6;
  }

  .ct-topic-symptom {
    display: inline-block;
    margin-top: 10px;
    padding: 4px 12px;
    background: #f1f5f9;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
  }

  .ct-topic-link {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-top: 12px;
    font-size: 13px;
    font-weight: 700;
    color: #0d9488;
    text-decoration: none;
  }

  .ct-topic-link:hover {
    color: #0f766e;
  }

  /* PROCESS */

  .ct-process-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
  }

  .ct-step-card {
    background: #ffffff;
    border: 1px solid #dbe7ea;
    border-radius: 24px;
    padding: 28px;
  }

  .ct-step-num {
    display: grid;
    place-items: center;
    width: 48px;
    height: 48px;
    border-radius: 16px;
    background: #ccfbf1;
    color: #0f766e;
    font-weight: 900;
    font-family: "Syne", sans-serif;
    font-size: 18px;
    margin-bottom: 18px;
  }

  .ct-step-card h3 {
    margin: 0 0 10px;
    font-family: "Syne", sans-serif;
    font-size: 21px;
    line-height: 1.1;
    letter-spacing: -0.5px;
    color: #0f172a;
  }

  .ct-step-card p {
    margin: 0;
    color: #475569;
    font-size: 14px;
    line-height: 1.62;
  }

  .ct-step-timing {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-top: 12px;
    padding: 4px 12px;
    background: #f0fdf4;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    color: #15803d;
  }

  .ct-channels {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 22px;
  }

  .ct-channel-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    border: 1px solid #dbe7ea;
    border-radius: 999px;
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
    text-decoration: none;
    transition: border-color 0.2s, color 0.2s, background 0.2s;
    background: #fff;
  }

  .ct-channel-link:hover {
    border-color: #0d9488;
    color: #0d9488;
    background: #f0fdf9;
  }

  .ct-testimonial-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
    margin-top: 32px;
  }

  .ct-testimonial {
    background: #ffffff;
    border: 1px solid #dbe7ea;
    border-radius: 22px;
    padding: 26px;
  }

  .ct-testimonial-quote {
    font-size: 15px;
    font-style: italic;
    color: #475569;
    line-height: 1.65;
    margin: 0 0 18px;
  }

  .ct-testimonial-quote::before {
    content: '"';
  }

  .ct-testimonial-quote::after {
    content: '"';
  }

  .ct-testimonial-author {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .ct-testimonial-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #ccfbf1;
    display: grid;
    place-items: center;
    font-weight: 900;
    font-size: 16px;
    color: #0f766e;
    flex-shrink: 0;
  }

  .ct-testimonial-name {
    font-weight: 700;
    font-size: 14px;
    color: #0f172a;
  }

  .ct-testimonial-role {
    font-size: 12px;
    color: #64748b;
  }

  /* FAQ accordion */
  .ct-faq-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
  }

  .ct-faq-item {
    background: #ffffff;
    border: 1px solid #dbe7ea;
    border-radius: 14px;
  }

  .ct-faq-item summary {
    list-style: none;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 14px;
    padding: 18px 22px;
    font-family: "Syne", sans-serif;
    font-size: 15.5px;
    font-weight: 700;
  }

  .ct-faq-item summary::-webkit-details-marker {
    display: none;
  }

  .ct-faq-icon {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #f1f5f9;
    border: 1px solid #dbe7ea;
    display: grid;
    place-items: center;
    font-size: 15px;
    color: #475569;
    flex: 0 0 24px;
  }

  .ct-faq-item[open] .ct-faq-icon {
    transform: rotate(45deg);
    background: #ffffff;
    border-color: #0d9488;
    color: #0d9488;
  }

  .ct-faq-item p {
    margin: 0;
    padding: 0 22px 18px;
    color: #475569;
    font-size: 14px;
    line-height: 1.6;
  }

  /* FINAL CTA */

  .ct-final {
    padding: 0 0 110px;
    background: #ffffff;
  }

  .ct-final-box {
    background:
      radial-gradient(circle at 88% 14%, rgba(13, 148, 136, 0.32), transparent 34%),
      #0f172a;
    color: #ffffff;
    border-radius: 32px;
    padding: 48px;
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 36px;
    align-items: center;
  }

  .ct-final-eyebrow {
    display: block;
    margin-bottom: 12px;
    color: #5eead4;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 1.4px;
    text-transform: uppercase;
  }

  .ct-final-box h2 {
    margin: 0 0 14px;
    max-width: 680px;
    font-family: "Syne", sans-serif;
    font-size: clamp(26px, 3.5vw, 46px);
    line-height: 1.02;
    letter-spacing: -1.8px;
  }

  .ct-final-box p {
    margin: 0;
    max-width: 600px;
    color: rgba(255, 255, 255, 0.68);
    font-size: 16px;
    line-height: 1.65;
  }

  .ct-final-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 52px;
    padding: 0 26px;
    border-radius: 999px;
    background: linear-gradient(135deg, #0d9488, #0f766e);
    color: #ffffff;
    font-weight: 900;
    font-size: 15px;
    white-space: nowrap;
    text-decoration: none;
    box-shadow: 0 14px 32px rgba(13, 148, 136, 0.35);
    transition: transform 0.2s, box-shadow 0.2s;
  }

  .ct-final-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 20px 40px rgba(13, 148, 136, 0.44);
  }

  /* MOBILE */

  @media (max-width: 1020px) {
    .ct-hero-form-grid,
    .ct-topic-grid,
    .ct-process-grid,
    .ct-testimonial-grid,
    .ct-final-box {
      grid-template-columns: 1fr;
    }

    .ct-form-card {
      position: static;
    }

    .ct-proof-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }

  @media (max-width: 720px) {
    .ct-hero-form {
      padding: 44px 0 64px;
    }

    .ct-h1 {
      font-size: 38px;
      letter-spacing: -1.6px;
    }

    .ct-lead {
      font-size: 16px;
    }

    .ct-proof-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .ct-form-card {
      padding: 22px;
      border-radius: 22px;
    }

    .ct-topics,
    .ct-process,
    .ct-faq {
      padding: 64px 0;
    }

    .ct-faq-grid {
      grid-template-columns: 1fr;
    }

    .ct-final {
      padding-bottom: 72px;
    }

    .ct-final-box {
      padding: 26px;
    }

    .ct-final-btn {
      width: 100%;
    }

    .ct-final-box {
      padding: 28px;
    }
  }
</style>

<script type="application/ld+json">
<?php echo wp_json_encode([
    "@context"    => "https://schema.org",
    "@type"       => "ContactPage",
    "name"        => "Kontakt – Bezpłatna diagnoza marketingu B2B | Upsellio",
    "url"         => $contact_page_url,
    "description" => "Bezpłatna diagnoza kampanii Google Ads, Meta Ads i stron WWW dla firm B2B. Sebastian Kelm – specjalista marketingu i sprzedaży B2B.",
    "mainEntity"  => [
        "@type"             => "LocalBusiness",
        "name"              => "Upsellio – Marketing B2B",
        "url"               => home_url("/"),
        "email"             => $contact_email,
        "telephone"         => $contact_phone,
        "areaServed"        => "Polska",
        "availableLanguage" => "Polish",
        "founder" => [
            "@type"    => "Person",
            "name"     => "Sebastian Kelm",
            "jobTitle" => "Specjalista marketingu B2B",
            "sameAs"   => $linkedin_url,
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>
</script>

<main class="ct-art" id="kontakt">
  <section class="ct-hero-form" id="formularz-kontaktowy">
    <div class="ct-wrap">
      <div class="ct-hero-form-grid">

        <div class="ct-hero-copy" data-animate="fade-right">
          <div class="ct-eyebrow">Bezpłatna 30-min diagnoza</div>

          <h1 class="ct-h1">Opisz, co dziś nie działa w twojej sprzedaży. Powiem, gdzie jest problem.</h1>

          <p class="ct-lead">Wystarczy opis sytuacji: ile masz ruchu, ile zapytań i co handlowiec mówi o jakości leadów. Jeśli temat ma sens, zadzwonimy. Jeśli nie, powiem to wprost i polecę kogoś zaufanego.</p>

          <div class="ct-consult-box">
            <strong>Co dostaniesz w 30 minutowej rozmowie.</strong>
            <p>Mapę problemu w lejku, 3 rzeczy do poprawy od jutra i szczerą ocenę czy pasujemy. Bez pitchu i bez slajdów.</p>
          </div>

          <div class="ct-not-list">
            <div class="ct-not-item"><span class="ct-not-icon">🚫</span><span>Nie wysyłam szablonowych ofert bez zrozumienia Twojego problemu</span></div>
            <div class="ct-not-item"><span class="ct-not-icon">🚫</span><span>Nie przekazuję rozmowy juniorom — obsługuję sam od początku do końca</span></div>
            <div class="ct-not-item"><span class="ct-not-icon">🚫</span><span>Nie obiecuję wyników bez wcześniejszej analizy danych</span></div>
            <div class="ct-not-item"><span class="ct-not-icon">🚫</span><span>Nie raportuje kliknięć jako sukces — liczy się jakość leadów i sprzedaż</span></div>
          </div>

          <div class="ct-proof-grid">
            <div class="ct-proof-cell">
              <strong>30 min</strong>
              <span>rozmowa o Twoim problemie</span>
            </div>
            <div class="ct-proof-cell">
              <strong>0 zł</strong>
              <span>pierwsza diagnoza</span>
            </div>
            <div class="ct-proof-cell">
              <strong>24h</strong>
              <span>czas odpowiedzi</span>
            </div>
            <div class="ct-proof-cell">
              <strong>1:1</strong>
              <span>rozmawia Sebastian, nie junior</span>
            </div>
          </div>

          <div class="ct-channels">
            <a href="tel:<?php echo esc_attr(preg_replace("/\s+/", "", (string) $contact_phone)); ?>" class="ct-channel-link">
              <span>📞</span><?php echo esc_html((string) $contact_phone); ?>
            </a>
            <a href="mailto:<?php echo esc_attr($contact_email); ?>" class="ct-channel-link">
              <span>✉️</span><?php echo esc_html($contact_email); ?>
            </a>
            <a href="<?php echo esc_url($linkedin_url); ?>" target="_blank" rel="noopener noreferrer" class="ct-channel-link">
              <span>💼</span>LinkedIn
            </a>
          </div>
        </div>

        <aside class="ct-form-card" aria-labelledby="form-heading" data-animate="fade-left" data-delay="1">
          <div class="ct-form-head">
            <span>Wypełnij formularz</span>
            <h2 id="form-heading">Napisz, co chcesz poprawić</h2>
            <p>Wystarczy krótki opis sytuacji. Nie potrzebujesz gotowego briefu.</p>
          </div>

          <?php
          echo upsellio_render_lead_form([
              "origin"          => "contact-page-form",
              "submit_label"    => "Wyślij i zadzwoń mi w ciągu 24h →",
              "variant"         => "full",
              "heading"         => "",
              "subheading"      => "",
              "redirect_url"    => $contact_page_url,
              "service_options" => $contact_service_options,
              "css_class"       => "ct-form",
          ]);
          ?>

          <div class="ct-form-after">
            <strong>Co stanie się po wysłaniu?</strong>
            <p>Do 2h dostaniesz potwierdzenie, do 24h wstępną ocenę. Potem umawiamy 30 min rozmowę i wysyłam pisemną mapę problemów.</p>
          </div>
        </aside>

      </div>
    </div>
  </section>

  <section class="ct-topics" id="o-co-zapytac" data-animate="fade-up">
    <div class="ct-wrap">
      <div class="ct-section-head">
        <span>O co możesz zapytać?</span>
        <h2>Nie musisz wiedzieć, gdzie leży problem.</h2>
        <p>Wystarczy, że opiszesz objaw. Pomogę zlokalizować, czy problem jest w ruchu, stronie, ofercie, formularzu czy procesie sprzedaży — i co naprawić najpierw.</p>
      </div>

      <div class="ct-topic-grid">
        <?php
        $topics = [
            [
                "icon"     => "📢",
                "title"    => "Google Ads B2B",
                "q"        => "Dlaczego kampania wydaje budżet, ale nie daje klientów?",
                "symptom"  => "Kliknięcia są, rozmów nie ma",
                "link"     => home_url("/marketing-google-ads/"),
                "link_txt" => "Usługa Google Ads →",
            ],
            [
                "icon"     => "📱",
                "title"    => "Meta Ads (Facebook/Instagram)",
                "q"        => "Dlaczego reklamy generują ruch, ale zapytania są złej jakości?",
                "symptom"  => "Leady od osób, które nie kupują",
                "link"     => home_url("/marketing-meta-ads/"),
                "link_txt" => "Usługa Meta Ads →",
            ],
            [
                "icon"     => "🌐",
                "title"    => "Strona WWW / Landing Page",
                "q"        => "Dlaczego ludzie wchodzą na stronę i nie wysyłają formularza?",
                "symptom"  => "Wysoki bounce rate, zero leadów",
                "link"     => home_url("/tworzenie-stron-internetowych/"),
                "link_txt" => "Usługa strony WWW →",
            ],
            [
                "icon"     => "🛒",
                "title"    => "Sklep internetowy B2B",
                "q"        => "Dlaczego produkty są oglądane, ale koszyk i zamówienia stoją?",
                "symptom"  => "Wysoki ruch, niska sprzedaż",
                "link"     => "#formularz-kontaktowy",
                "link_txt" => "Opisz problem →",
            ],
            [
                "icon"     => "🔍",
                "title"    => "SEO i ruch organiczny",
                "q"        => "Dlaczego ruch z Google nie przekłada się na wartościowe zapytania?",
                "symptom"  => "Pozycje są, konwersja nie",
                "link"     => "#formularz-kontaktowy",
                "link_txt" => "Opisz problem →",
            ],
            [
                "icon"     => "💬",
                "title"    => "Oferta i jakość leadów",
                "q"        => "Dlaczego leady są, ale rozmowy nie kończą się sprzedażą?",
                "symptom"  => "Dużo rozmów, mało zamkniętych",
                "link"     => "#formularz-kontaktowy",
                "link_txt" => "Opisz problem →",
            ],
        ];
        foreach ($topics as $topic) :
        ?>
          <div class="ct-topic-card">
            <div class="ct-topic-icon"><?php echo $topic["icon"]; ?></div>
            <strong><?php echo esc_html($topic["title"]); ?></strong>
            <p><?php echo esc_html($topic["q"]); ?></p>
            <span class="ct-topic-symptom"><?php echo esc_html($topic["symptom"]); ?></span><br>
            <a class="ct-topic-link" href="<?php echo esc_url($topic["link"]); ?>">
              <?php echo esc_html($topic["link_txt"]); ?>
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="ct-topics ct-opinions" id="opinie" data-animate="fade-up">
    <div class="ct-wrap">
      <div class="ct-section-head">
        <span>Opinie klientów</span>
        <h2>Co mówią firmy po pierwszej rozmowie</h2>
      </div>

      <div class="ct-testimonial-grid">
        <?php
        $testimonials = [
            [
                "quote"   => "Po analizie wiedzieliśmy dokładnie, gdzie ucieka budżet i które elementy strony trzeba poprawić jako pierwsze. Działamy od tamtej pory systematycznie.",
                "name"    => "Marek T.",
                "role"    => "Właściciel",
                "company" => "firma produkcyjna B2B",
                "initial" => "M",
            ],
            [
                "quote"   => "Największa zmiana to przejście z raportów o kliknięciach na rozmowę o jakości leadów i realnym koszcie pozyskania. Wreszcie marketing mówi językiem sprzedaży.",
                "name"    => "Anna K.",
                "role"    => "Marketing Manager",
                "company" => "usługi profesjonalne",
                "initial" => "A",
            ],
            [
                "quote"   => "Kampanie i landing zaczęły działać jak jeden system. Mniej chaosu, więcej konkretnych zapytań od firm, z którymi warto rozmawiać.",
                "name"    => "Piotr R.",
                "role"    => "CEO",
                "company" => "SaaS B2B",
                "initial" => "P",
            ],
        ];
        foreach ($testimonials as $t) :
        ?>
          <blockquote class="ct-testimonial">
            <p class="ct-testimonial-quote"><?php echo esc_html($t["quote"]); ?></p>
            <div class="ct-testimonial-author">
              <div class="ct-testimonial-avatar"><?php echo esc_html($t["initial"]); ?></div>
              <div>
                <div class="ct-testimonial-name"><?php echo esc_html($t["name"]); ?></div>
                <div class="ct-testimonial-role"><?php echo esc_html($t["role"]); ?> · <?php echo esc_html($t["company"]); ?></div>
              </div>
            </div>
          </blockquote>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="ct-process" id="jak-dziala" data-animate="fade-up">
    <div class="ct-wrap">
      <div class="ct-section-head">
        <span>Jak to działa?</span>
        <h2>Krótko, konkretnie, bez przeciągania.</h2>
        <p>Od formularza do konkretnej odpowiedzi — opisuję każdy krok, żebyś wiedział, czego się spodziewać.</p>
      </div>

      <div class="ct-process-grid">
        <div class="ct-step-card">
          <div class="ct-step-num">01</div>
          <h3>Wysyłasz formularz</h3>
          <p>Opisujesz, co nie działa. Może to być jedno zdanie, link do strony albo konkretny problem z kampanią lub leadami.</p>
          <span class="ct-step-timing">⏱ Teraz</span>
        </div>

        <div class="ct-step-card">
          <div class="ct-step-num">02</div>
          <h3>Sprawdzam kontekst</h3>
          <p>Patrzę na stronę, komunikację, ofertę i kampanie. Szukam możliwych miejsc, w których tracisz zapytania lub budżet.</p>
          <span class="ct-step-timing">⏱ W ciągu 24h</span>
        </div>

        <div class="ct-step-card">
          <div class="ct-step-num">03</div>
          <h3>Dostajesz konkretny kierunek</h3>
          <p>Wracam z odpowiedzią: co poprawić najpierw, gdzie jest problem i czy jest sens rozmawiać dalej. Bez owijania w bawełnę.</p>
          <span class="ct-step-timing">⏱ Bezpłatnie</span>
        </div>
      </div>
    </div>
  </section>

  <section class="ct-faq" id="faq" data-animate="fade-up">
    <div class="ct-wrap">
      <div class="ct-section-head">
        <span>Zanim wyślesz formularz</span>
        <h2>Odpowiedzi na najczęstsze pytania</h2>
        <p>Jeśli nie znajdziesz odpowiedzi — napisz, a odpowiem bezpośrednio.</p>
      </div>

      <div class="ct-faq-grid">
        <?php foreach ($contact_faq_items as $faq_item) : ?>
          <details class="ct-faq-item">
            <summary>
              <span><?php echo esc_html((string) $faq_item["question"]); ?></span>
              <span class="ct-faq-icon">+</span>
            </summary>
            <p><?php echo esc_html((string) $faq_item["answer"]); ?></p>
          </details>
        <?php endforeach; ?>
      </div>

      <p style="margin-top:20px;font-size:14px;color:#64748b;">
        Potrzebujesz więcej kontekstu o usługach?
        <a href="<?php echo esc_url(home_url("/marketing-google-ads/")); ?>" style="color:#0d9488;font-weight:700;">Google Ads B2B</a> ·
        <a href="<?php echo esc_url(home_url("/marketing-meta-ads/")); ?>" style="color:#0d9488;font-weight:700;">Meta Ads B2B</a> ·
        <a href="<?php echo esc_url(home_url("/tworzenie-stron-internetowych/")); ?>" style="color:#0d9488;font-weight:700;">Strony WWW</a> ·
        <a href="<?php echo esc_url(home_url("/portfolio-marketingowe/")); ?>" style="color:#0d9488;font-weight:700;">Case study</a>
      </p>
    </div>
  </section>

  <section class="ct-final">
    <div class="ct-wrap">
      <div class="ct-final-box">
        <div>
          <span class="ct-final-eyebrow">Nie odkładaj na później</span>
          <h2>Jeśli leady uciekają, zwykle da się znaleźć konkretny powód.</h2>
          <p>Napisz, co nie działa. Odpowiem konkretnie: reklamy, strona, oferta, formularz albo proces sprzedaży. Pierwsze 30 minut — bez opłat.</p>
        </div>
        <a href="#formularz-kontaktowy" class="ct-final-btn">
          Wróć do formularza
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
      </div>
    </div>
  </section>
</main>

<?php if (!empty($contact_faq_items)) : ?>
<script type="application/ld+json">
<?php
echo wp_json_encode([
    "@context" => "https://schema.org",
    "@type" => "FAQPage",
    "mainEntity" => array_map(static function ($faq_item) {
        return [
            "@type" => "Question",
            "name" => (string) $faq_item["question"],
            "acceptedAnswer" => [
                "@type" => "Answer",
                "text" => (string) $faq_item["answer"],
            ],
        ];
    }, $contact_faq_items),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
?>
</script>
<?php endif; ?>

<?php
get_footer();
?>
