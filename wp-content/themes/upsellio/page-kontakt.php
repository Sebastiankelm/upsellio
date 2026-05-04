<?php
/*
Template Name: Upsellio - Kontakt
Template Post Type: page
*/
if (!defined("ABSPATH")) {
    exit;
}

get_header();

$front_page_sections = function_exists("upsellio_get_front_page_content_config")
    ? upsellio_get_front_page_content_config()
    : [];
$contact_phone = function_exists("upsellio_get_contact_phone")
    ? upsellio_get_contact_phone()
    : trim((string) ($front_page_sections["contact_phone"] ?? ""));
$contact_email = trim((string) ($front_page_sections["contact_email"] ?? "kontakt@upsellio.pl"));
$contact_page_url = home_url("/kontakt/");
$contact_service_options = [
    "Kampanie Meta Ads",
    "Kampanie Google Ads",
    "Tworzenie strony lub landing page",
    "Marketing + strona (oba)",
    "Nie wiem — chcę porozmawiać",
];
$contact_faq_items = [
    [
        "question" => "Czy pierwsza diagnoza jest płatna?",
        "answer" => "Nie. Pierwsza odpowiedź i wstępne wskazanie kierunku są bezpłatne. Jeśli temat wymaga głębszego audytu, wtedy ustalamy osobny zakres.",
    ],
    [
        "question" => "Czy muszę mieć gotowy brief?",
        "answer" => "Nie. Wystarczy krótki opis sytuacji: co robisz, co nie działa i jaki efekt chcesz osiągnąć.",
    ],
    [
        "question" => "Czy mogę napisać, jeśli nie wiem, czy problem jest w reklamach czy stronie?",
        "answer" => "Tak. To bardzo częsta sytuacja. Wtedy patrzę na cały lejek: źródło ruchu, komunikat, stronę, formularz i dalszy kontakt z leadem.",
    ],
    [
        "question" => "Czy od razu dostanę ofertę?",
        "answer" => "Nie wysyłam szablonowych ofert. Najpierw muszę zrozumieć problem. Dopiero potem można przygotować zakres, który ma sens biznesowy.",
    ],
    [
        "question" => "Dla jakich firm to ma największy sens?",
        "answer" => "Najczęściej dla firm B2B, usługowych, e-commerce i właścicieli, którzy mają już stronę lub reklamy, ale nie są zadowoleni z jakości zapytań albo sprzedaży.",
    ],
    [
        "question" => "Czy mogę zgłosić samą stronę bez kampanii reklamowych?",
        "answer" => "Tak. Możesz zgłosić samą stronę, landing page, sklep internetowy albo formularz. Często poprawa konwersji strony daje więcej niż dokładanie budżetu do reklam.",
    ],
];
?>
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
    padding: 56px 0 78px;
    background:
      radial-gradient(circle at 82% 8%, rgba(13, 148, 136, 0.14), transparent 34%),
      linear-gradient(180deg, #ffffff 0%, #f8fafc 58%, #eef6f7 100%);
  }

  .ct-wrap {
    max-width: 1180px;
    margin: 0 auto;
    padding: 0 22px;
  }

  .ct-hero-form-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 460px;
    gap: 56px;
    align-items: start;
  }

  .ct-hero-copy {
    padding-top: 22px;
  }

  .ct-eyebrow {
    display: inline-flex;
    margin-bottom: 14px;
    color: #0f766e;
    font-size: 12px;
    font-weight: 900;
    letter-spacing: 1.3px;
    text-transform: uppercase;
  }

  .ct-h1 {
    max-width: 760px;
    margin: 0;
    font-family: "Syne", sans-serif;
    font-size: clamp(42px, 5.6vw, 72px);
    line-height: 0.96;
    letter-spacing: -3px;
    color: #0f172a;
  }

  .ct-lead {
    max-width: 720px;
    margin: 22px 0 0;
    color: #475569;
    font-size: 18px;
    line-height: 1.68;
  }

  .ct-consult-box {
    max-width: 680px;
    margin-top: 28px;
    padding: 22px 24px;
    background: #ffffff;
    border: 1px solid #dbe7ea;
    border-left: 4px solid #0d9488;
    border-radius: 20px;
    box-shadow: 0 12px 34px rgba(15, 23, 42, 0.06);
  }

  .ct-consult-box strong {
    display: block;
    font-family: "Syne", sans-serif;
    font-size: 21px;
    line-height: 1.15;
    color: #0f172a;
    margin-bottom: 8px;
  }

  .ct-consult-box p {
    margin: 0;
    color: #475569;
    line-height: 1.6;
  }

  .ct-proof-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    max-width: 680px;
    margin-top: 18px;
  }

  .ct-proof-grid div {
    background: #ffffff;
    border: 1px solid #dbe7ea;
    border-radius: 18px;
    padding: 18px;
  }

  .ct-proof-grid strong {
    display: block;
    font-family: "Syne", sans-serif;
    font-size: 28px;
    line-height: 1;
    letter-spacing: -1px;
    color: #0d9488;
    margin-bottom: 6px;
  }

  .ct-proof-grid span {
    display: block;
    color: #64748b;
    font-size: 13px;
    line-height: 1.35;
  }

  .ct-form-card {
    position: sticky;
    top: 92px;
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

  /* Formularz globalnie */
  .ct-form-card input,
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

  .ct-form-card input:focus,
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
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
  }

  .ct-topic-card strong {
    display: block;
    margin-bottom: 8px;
    font-family: "Syne", sans-serif;
    font-size: 21px;
    line-height: 1.15;
    color: #0f172a;
  }

  .ct-topic-card p {
    margin: 0;
    color: #475569;
    font-size: 14px;
    line-height: 1.6;
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
    border-radius: 22px;
    padding: 26px;
  }

  .ct-step-card > span {
    display: inline-grid;
    place-items: center;
    width: 44px;
    height: 44px;
    margin-bottom: 20px;
    border-radius: 15px;
    background: #ccfbf1;
    color: #0f766e;
    font-weight: 900;
  }

  .ct-step-card h3 {
    margin: 0 0 10px;
    font-family: "Syne", sans-serif;
    font-size: 23px;
    line-height: 1.1;
    letter-spacing: -0.7px;
    color: #0f172a;
  }

  .ct-step-card p {
    margin: 0;
    color: #475569;
    font-size: 14px;
    line-height: 1.6;
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
      radial-gradient(circle at 88% 12%, rgba(13, 148, 136, 0.32), transparent 32%),
      #0f172a;
    color: #ffffff;
    border-radius: 30px;
    padding: 42px;
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 32px;
    align-items: center;
  }

  .ct-final-box span {
    display: block;
    margin-bottom: 10px;
    color: #5eead4;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 1.3px;
    text-transform: uppercase;
  }

  .ct-final-box h2 {
    margin: 0 0 12px;
    max-width: 760px;
    font-family: "Syne", sans-serif;
    font-size: clamp(30px, 4vw, 52px);
    line-height: 1;
    letter-spacing: -2px;
  }

  .ct-final-box p {
    margin: 0;
    max-width: 760px;
    color: rgba(255, 255, 255, 0.72);
    font-size: 16px;
    line-height: 1.65;
  }

  .ct-final-box a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 50px;
    padding: 0 22px;
    border-radius: 999px;
    background: linear-gradient(135deg, #0d9488, #0f766e);
    color: #ffffff;
    font-weight: 900;
    white-space: nowrap;
    text-decoration: none;
  }

  /* MOBILE */

  @media (max-width: 1020px) {
    .ct-hero-form-grid,
    .ct-topic-grid,
    .ct-process-grid,
    .ct-final-box {
      grid-template-columns: 1fr;
    }

    .ct-form-card {
      position: static;
    }

    .ct-proof-grid {
      grid-template-columns: 1fr 1fr 1fr;
    }
  }

  @media (max-width: 720px) {
    .ct-hero-form {
      padding: 38px 0 58px;
    }

    .ct-h1 {
      font-size: 42px;
      letter-spacing: -1.8px;
    }

    .ct-lead {
      font-size: 16px;
    }

    .ct-proof-grid {
      grid-template-columns: 1fr;
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

    .ct-final-box a {
      width: 100%;
    }
  }
</style>

<script type="application/ld+json">
<?php
echo wp_json_encode([
    "@context" => "https://schema.org",
    "@type" => "ContactPage",
    "name" => "Kontakt Upsellio",
    "url" => $contact_page_url,
    "description" => "Kontakt i bezpłatna diagnoza marketingu B2B, kampanii Meta Ads, Google Ads oraz stron internetowych.",
    "mainEntity" => [
        "@type" => "LocalBusiness",
        "name" => "Upsellio",
        "url" => home_url("/"),
        "email" => $contact_email,
        "telephone" => $contact_phone,
        "areaServed" => "Polska",
        "availableLanguage" => "Polish",
        "founder" => [
            "@type" => "Person",
            "name" => "Sebastian Kelm",
            "jobTitle" => "Specjalista ds. marketingu B2B",
            "sameAs" => "https://www.linkedin.com/in/sebastiankelm/",
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
?>
</script>

<main class="ct-art">
  <section class="ct-hero-form" id="kontakt">
    <div class="ct-wrap">
      <div class="ct-hero-form-grid">

        <div class="ct-hero-copy" data-animate="fade-right">
          <div class="ct-eyebrow">Bezpłatna diagnoza</div>

          <h1 class="ct-h1">
            Zadaj mi konkretne pytanie o stronę, reklamy albo leady.
          </h1>

          <p class="ct-lead">
            Masz stronę, sklep, kampanie Google Ads lub Meta Ads, ale wynik jest słabszy niż powinien?
            Opisz sytuację. Sprawdzę, gdzie prawdopodobnie tracisz zapytania i co warto poprawić najpierw.
          </p>

          <div class="ct-consult-box">
            <strong>To nie jest rozmowa sprzedażowa na siłę.</strong>
            <p>
              Najpierw rozpoznajemy problem. Jeśli będę w stanie realnie pomóc,
              zaproponuję kierunek działania. Jeśli nie — powiem to wprost.
            </p>
          </div>

          <div class="ct-proof-grid">
            <div>
              <strong>30 min</strong>
              <span>krótka rozmowa o Twoim problemie</span>
            </div>
            <div>
              <strong>0 zł</strong>
              <span>pierwsza diagnoza bez zobowiązań</span>
            </div>
            <div>
              <strong>1:1</strong>
              <span>odpowiadam osobiście</span>
            </div>
          </div>
        </div>

        <aside class="ct-form-card" id="formularz-kontaktowy" data-animate="fade-left" data-delay="1">
          <div class="ct-form-head">
            <span>Wypełnij formularz</span>
            <h2>Napisz, co chcesz poprawić</h2>
            <p>
              Wystarczy krótki opis. Nie potrzebujesz gotowego briefu.
            </p>
          </div>

          <?php
          echo upsellio_render_lead_form([
              "origin" => "contact-page-form",
              "submit_label" => "Wyślij zapytanie",
              "variant" => "full",
              "heading" => "",
              "subheading" => "",
              "redirect_url" => $contact_page_url,
              "service_options" => $contact_service_options,
              "css_class" => "ct-form",
          ]);
          ?>

          <div class="ct-form-after">
            <strong>Co stanie się po wysłaniu?</strong>
            <p>
              Sprawdzę wiadomość, kontekst i wrócę z odpowiedzią.
              Jeśli temat ma sens, zaproponuję krótką rozmowę.
            </p>
          </div>
        </aside>

      </div>
    </div>
  </section>

  <section class="ct-topics" data-animate="fade-up">
    <div class="ct-wrap">
      <div class="ct-section-head">
        <span>O co możesz zapytać?</span>
        <h2>Nie musisz wiedzieć, gdzie jest problem.</h2>
        <p>
          Wystarczy, że opiszesz objaw. Ja pomogę rozdzielić, czy problem leży w ruchu,
          stronie, ofercie, formularzu czy procesie sprzedaży.
        </p>
      </div>

      <div class="ct-topic-grid">
        <div class="ct-topic-card">
          <strong>Google Ads</strong>
          <p>Dlaczego kampania wydaje budżet, ale nie daje klientów?</p>
        </div>

        <div class="ct-topic-card">
          <strong>Meta Ads</strong>
          <p>Dlaczego reklamy generują ruch, ale zapytania są słabe?</p>
        </div>

        <div class="ct-topic-card">
          <strong>Strona WWW</strong>
          <p>Dlaczego ludzie wchodzą na stronę i nie wysyłają formularza?</p>
        </div>

        <div class="ct-topic-card">
          <strong>Sklep internetowy</strong>
          <p>Dlaczego produkty są oglądane, ale koszyk i sprzedaż stoją?</p>
        </div>

        <div class="ct-topic-card">
          <strong>SEO</strong>
          <p>Dlaczego ruch z Google nie przekłada się na wartościowe zapytania?</p>
        </div>

        <div class="ct-topic-card">
          <strong>Oferta i leady</strong>
          <p>Dlaczego leady są, ale rozmowy nie kończą się sprzedażą?</p>
        </div>
      </div>
    </div>
  </section>

  <section class="ct-process" data-animate="fade-up">
    <div class="ct-wrap">
      <div class="ct-section-head">
        <span>Jak to działa?</span>
        <h2>Krótko, konkretnie, bez przeciągania.</h2>
      </div>

      <div class="ct-process-grid">
        <div class="ct-step-card">
          <span>01</span>
          <h3>Wysyłasz formularz</h3>
          <p>
            Opisujesz, co nie działa. Może to być jedno zdanie, link do strony
            albo konkretny problem z reklamami.
          </p>
        </div>

        <div class="ct-step-card">
          <span>02</span>
          <h3>Sprawdzam kontekst</h3>
          <p>
            Patrzę na stronę, komunikację, ofertę, formularz i możliwe miejsca,
            w których tracisz leady.
          </p>
        </div>

        <div class="ct-step-card">
          <span>03</span>
          <h3>Dostajesz kierunek</h3>
          <p>
            Wracam z konkretną odpowiedzią: co poprawić najpierw i czy jest sens
            rozmawiać dalej.
          </p>
        </div>
      </div>
    </div>
  </section>

  <section class="ct-faq" data-animate="fade-up">
    <div class="ct-wrap">
      <div class="ct-section-head">
        <span>Zanim wyślesz formularz</span>
        <h2>Krótkie odpowiedzi na częste pytania</h2>
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
    </div>
  </section>

  <section class="ct-final">
    <div class="ct-wrap">
      <div class="ct-final-box">
        <div>
          <span>Nie odkładaj tego na później</span>
          <h2>Jeśli leady uciekają, zwykle da się znaleźć konkretny powód.</h2>
          <p>
            Napisz, co obecnie nie działa. Odpowiem konkretnie, od czego zacząć:
            reklamy, strona, oferta, formularz albo proces sprzedaży.
          </p>
        </div>
        <a href="#formularz-kontaktowy">Wróć do formularza →</a>
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
