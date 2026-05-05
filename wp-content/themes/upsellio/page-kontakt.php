<?php
/*
Template Name: Upsellio - Kontakt v2 (jezyk klienta)
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
    : trim((string) ($front_page_sections["contact_phone"] ?? "+48 575 522 595"));
$contact_phone_href = preg_replace("/\s+/", "", (string) $contact_phone);
$contact_email = trim((string) ($front_page_sections["contact_email"] ?? "kontakt@upsellio.pl"));
$contact_page_url = home_url("/kontakt/");
$linkedin_url = "https://www.linkedin.com/in/kelm-sebastian/";

$google_ads_url = function_exists("upsellio_get_google_ads_page_url") ? (string) upsellio_get_google_ads_page_url() : "";
$meta_ads_url = function_exists("upsellio_get_meta_ads_page_url") ? (string) upsellio_get_meta_ads_page_url() : "";
$websites_url = function_exists("upsellio_get_websites_page_url") ? (string) upsellio_get_websites_page_url() : "";

$contact_service_options = [
    "Kampanie Google Ads",
    "Kampanie Meta Ads",
    "Tworzenie strony lub landing page",
    "Pakiet kompletny (reklama + strona + lejek)",
    "Audyt istniejących kampanii lub strony",
    "Nie wiem — chcę porozmawiać o problemie",
];

$contact_faq_items = [
    [
        "question" => "Czy pierwsza diagnoza i rozmowa są płatne?",
        "answer" => "Nie. Pierwsza rozmowa i wstępna ocena sytuacji są bezpłatne. Jeśli temat wymaga głębszego audytu z dokumentacją, ustalamy osobny zakres. Ale diagnoza „gdzie i dlaczego tracisz zapytania” — to zawsze bezpłatnie.",
    ],
    [
        "question" => "Skąd mam wiedzieć, że to nie kolejna agencja, która sprzeda mi raporty?",
        "answer" => "Najuczciwsza odpowiedź: w pierwszej rozmowie. 30 minut, bez prezentacji. Jeśli po niej masz wrażenie że gadałeś z kolejnym agencyjnym handlowcem — nie zatrudniaj mnie. Jeśli wyszło z tego 5 konkretnych rzeczy do poprawy w Twoim marketingu, których nikt wcześniej nie wskazał, masz odpowiedź. Pierwsze 30 minut to nie sprzedaż, to próbka pracy.",
    ],
    [
        "question" => "Co jeśli po rozmowie zorientuję się, że nie chcę z Tobą pracować?",
        "answer" => "Idealna sytuacja. 30 minut które kosztowały Cię 0 zł zamieniły się w mapę 3-5 rzeczy do poprawy które możesz wdrożyć sam, ze swoją obecną agencją albo z innym wykonawcą. Nie ścigam się, nie wysyłam follow-upów typu „pamiętasz o naszej rozmowie”, nie zapraszam na webinar. Decyzja należy do Ciebie i tyle.",
    ],
    [
        "question" => "Czy muszę mieć gotowy brief lub szczegółowy opis projektu?",
        "answer" => "Nie. Wystarczy krótki opis sytuacji: co robisz, co nie działa tak jak powinno i jaki efekt chcesz osiągnąć. Pytania uzupełniające zadaję sam podczas rozmowy.",
    ],
    [
        "question" => "Czy mogę napisać, jeśli nie wiem, czy problem leży w reklamach czy na stronie?",
        "answer" => "Tak — i to jest bardzo częsta sytuacja. Patrzę na cały lejek: źródło ruchu, komunikat reklamowy, stronę docelową, formularz i dalszy kontakt z leadem. Pomogę zlokalizować, gdzie jest faktyczny problem.",
    ],
    [
        "question" => "Czy od razu dostanę ofertę cenową?",
        "answer" => "Nie. Pierwszy mail po rozmowie to nie oferta — to spisana mapa problemów które omówiliśmy, plus rekomendacja co zrobić w pierwszej kolejności (z Tobą lub beze mnie). Wycenę przygotowuję dopiero gdy ustalimy że ma sens współpraca i wiem dokładnie nad czym mam pracować. To oszczędza Twój i mój czas.",
    ],
    [
        "question" => "Dla jakich firm ta współpraca ma największy sens?",
        "answer" => "Dla firm B2B (usługi, produkcja, IT, SaaS), e-commerce B2B i firm usługowych, które mają już stronę lub kampanie reklamowe, ale nie są zadowolone z jakości zapytań lub sprzedaży. Minimalna wielkość firmy: 3-5 osób. Minimalny budżet reklamowy do pełnej współpracy: 2 000-3 000 zł/mies. na platformę.",
    ],
    [
        "question" => "Ile trwa odpowiedź na zgłoszenie?",
        "answer" => "Odpowiadam w ciągu 24 godzin w dni robocze. Zwykle szybciej. Jeśli sprawa jest pilna, napisz to w formularzu — priorytetuję takie zgłoszenia.",
    ],
];
?>

<style>
.ct-art { font-family: "DM Sans", system-ui, sans-serif; color: #0f172a; line-height: 1.65; }
.ct-art *, .ct-art *::before, .ct-art *::after { box-sizing: border-box; }
.ct-wrap { max-width: 1180px; margin: 0 auto; padding: 0 22px; }
.ct-hero-form { padding: 68px 0 88px; background: radial-gradient(ellipse at 86% 12%, rgba(13,148,136,.18), transparent 38%), radial-gradient(ellipse at 10% 90%, rgba(13,148,136,.08), transparent 32%), linear-gradient(180deg, #ffffff 0%, #f8fafc 55%, #eef6f7 100%); }
.ct-hero-form-grid { display: grid; grid-template-columns: minmax(0,1fr) 480px; gap: 56px; align-items: start; }
.ct-hero-copy { padding-top: 16px; }
.ct-eyebrow { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 16px; color: #0f766e; font-size: 11px; font-weight: 900; letter-spacing: 1.4px; text-transform: uppercase; }
.ct-eyebrow::before { content: ''; display: block; width: 20px; height: 2px; background: #0d9488; border-radius: 2px; }
.ct-h1 { max-width: 760px; margin: 0; font-family: "Syne", "Bricolage Grotesque", sans-serif; font-size: clamp(36px, 5vw, 64px); line-height: .98; letter-spacing: -2.4px; color: #0f172a; }
.ct-h1-accent { color: #0d9488; }
.ct-lead { max-width: 700px; margin: 22px 0 0; color: #475569; font-size: 17px; line-height: 1.7; }
.ct-consult-box { max-width: 700px; margin-top: 26px; padding: 22px 26px; background: #ffffff; border: 1px solid #dbe7ea; border-left: 4px solid #0d9488; border-radius: 20px; box-shadow: 0 10px 28px rgba(15,23,42,.06); }
.ct-consult-box strong { display: block; font-family: "Syne", "Bricolage Grotesque", sans-serif; font-size: 18px; line-height: 1.2; color: #0f172a; margin-bottom: 7px; }
.ct-consult-box p { margin: 0; color: #475569; line-height: 1.6; }
.ct-not-list { display: flex; flex-direction: column; gap: 8px; margin-top: 16px; max-width: 700px; }
.ct-not-item { display: flex; align-items: flex-start; gap: 10px; font-size: 14px; color: #475569; padding: 11px 14px; background: #fff; border: 1px solid #dbe7ea; border-radius: 12px; line-height: 1.55; }
.ct-not-icon { font-size: 15px; flex-shrink: 0; margin-top: 1px; }
.ct-proof-grid { display: grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 10px; max-width: 720px; margin-top: 22px; }
.ct-proof-cell { background: #ffffff; border: 1px solid #dbe7ea; border-radius: 18px; padding: 16px; text-align: center; }
.ct-proof-cell strong { display: block; font-family: "Syne", "Bricolage Grotesque", sans-serif; font-size: 24px; line-height: 1; letter-spacing: -1px; color: #0d9488; margin-bottom: 6px; }
.ct-proof-cell span { display: block; color: #64748b; font-size: 12px; line-height: 1.4; }
.ct-form-card { position: sticky; top: 88px; background: #ffffff; border: 1px solid #dbe7ea; border-radius: 26px; padding: 30px; box-shadow: 0 24px 70px rgba(15,23,42,.12); }
.ct-form-head { margin-bottom: 18px; }
.ct-form-head span { display: block; margin-bottom: 8px; color: #0f766e; font-size: 11px; font-weight: 900; letter-spacing: 1.3px; text-transform: uppercase; }
.ct-form-head h2 { margin: 0 0 8px; font-family: "Syne", "Bricolage Grotesque", sans-serif; font-size: 28px; line-height: 1.06; letter-spacing: -1px; color: #0f172a; }
.ct-form-head p { margin: 0; color: #64748b; font-size: 14px; line-height: 1.55; }
.ct-example-box { margin: 0 0 18px; padding: 14px 16px; background: #f0fdfa; border: 1px dashed #99f6e4; border-radius: 12px; font-size: 13px; color: #115e59; line-height: 1.55; }
.ct-example-box b { display: block; font-weight: 800; color: #0f766e; margin-bottom: 4px; font-size: 11px; letter-spacing: 1px; text-transform: uppercase; }
.ct-example-box em { font-style: italic; color: #134e4a; }
.ct-form-card input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]), .ct-form-card select, .ct-form-card textarea { width: 100%; min-height: 48px; background: #ffffff; border: 1px solid #dbe7ea; border-radius: 14px; padding: 12px 14px; color: #0f172a; font-size: 14px; font-family: inherit; }
.ct-form-card textarea { min-height: 130px; resize: vertical; line-height: 1.6; }
.ct-form-card input:focus, .ct-form-card select:focus, .ct-form-card textarea:focus { border-color: #0d9488; box-shadow: 0 0 0 4px rgba(13,148,136,.14); outline: none; }
.ct-form-card .ups-form__consent { display: flex; align-items: flex-start; gap: 10px; width: 100%; max-width: 100%; min-width: 0; font-size: 13px; line-height: 1.5; color: #475569; }
.ct-form-card .ups-form__consent input[type="checkbox"] { width: 18px; height: 18px; min-height: 18px; margin: 3px 0 0; flex-shrink: 0; padding: 0; border-radius: 4px; accent-color: #0d9488; }
.ct-form-card .ups-form__consent span { flex: 1; min-width: 0; }
.ct-form-card .ups-form { min-width: 0; width: 100%; }
.ct-form-card button, .ct-form-card input[type="submit"] { width: 100%; min-height: 52px; border: 0; border-radius: 999px; background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); color: #ffffff; font-family: inherit; font-size: 14px; font-weight: 900; cursor: pointer; box-shadow: 0 16px 36px rgba(13,148,136,.25); transition: transform .18s, box-shadow .18s; }
.ct-form-card button:hover { transform: translateY(-1px); box-shadow: 0 20px 40px rgba(13,148,136,.3); }
.ct-form-after { margin-top: 16px; padding: 16px; background: #f8fafc; border: 1px solid #dbe7ea; border-radius: 18px; }
.ct-form-after strong { display: block; font-size: 14px; color: #0f172a; margin-bottom: 5px; }
.ct-form-after p { margin: 0; color: #64748b; font-size: 13px; line-height: 1.5; }
.ct-topics, .ct-process, .ct-faq, .ct-opinions { padding: 88px 0; background: #ffffff; }
.ct-process { background: #f8fafc; }
.ct-opinions { background: #f8fafc; border-top: 1px solid #e2e8f0; }
.ct-section-head { max-width: 800px; margin-bottom: 30px; }
.ct-section-head span { display: inline-flex; margin-bottom: 10px; color: #0f766e; font-size: 11px; font-weight: 900; letter-spacing: 1.3px; text-transform: uppercase; }
.ct-section-head h2 { margin: 0; font-family: "Syne", "Bricolage Grotesque", sans-serif; font-size: clamp(30px, 3.8vw, 48px); line-height: 1.02; letter-spacing: -1.8px; color: #0f172a; }
.ct-section-head p { margin: 14px 0 0; color: #475569; font-size: 17px; line-height: 1.65; max-width: 70ch; }
.ct-topic-grid { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 14px; }
.ct-topic-card { background: #ffffff; border: 1px solid #dbe7ea; border-radius: 22px; padding: 26px; box-shadow: 0 8px 24px rgba(15,23,42,.05); transition: border-color .2s, box-shadow .2s, transform .2s; }
.ct-topic-card:hover { border-color: #0d9488; transform: translateY(-2px); box-shadow: 0 14px 36px rgba(13,148,136,.12); }
.ct-topic-icon { display: grid; place-items: center; width: 46px; height: 46px; border-radius: 14px; background: #ccfbf1; margin-bottom: 14px; font-size: 22px; }
.ct-topic-card strong { display: block; margin-bottom: 8px; font-family: "Syne", "Bricolage Grotesque", sans-serif; font-size: 19px; line-height: 1.15; color: #0f172a; }
.ct-topic-card > p { margin: 0; color: #475569; font-size: 14px; line-height: 1.6; }
.ct-topic-symptom { display: inline-block; margin-top: 12px; padding: 4px 12px; background: #f1f5f9; border-radius: 999px; font-size: 12px; font-weight: 700; color: #64748b; }
.ct-topic-link { display: inline-flex; align-items: center; gap: 5px; margin-top: 12px; font-size: 13px; font-weight: 700; color: #0d9488; text-decoration: none; }
.ct-topic-link:hover { color: #0f766e; }
.ct-testimonial-grid { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 14px; }
.ct-testimonial { background: #ffffff; border: 1px solid #dbe7ea; border-radius: 22px; padding: 26px; margin: 0; }
.ct-testimonial-quote { font-size: 15px; font-style: italic; color: #475569; line-height: 1.65; margin: 0 0 18px; }
.ct-testimonial-quote::before { content: '"'; }
.ct-testimonial-quote::after { content: '"'; }
.ct-testimonial-author { display: flex; align-items: center; gap: 12px; }
.ct-testimonial-avatar { width: 40px; height: 40px; border-radius: 50%; background: #ccfbf1; display: grid; place-items: center; font-weight: 900; font-size: 16px; color: #0f766e; flex-shrink: 0; }
.ct-testimonial-name { font-weight: 700; font-size: 14px; color: #0f172a; }
.ct-testimonial-role { font-size: 12px; color: #64748b; }
.ct-process-grid { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 14px; }
.ct-step-card { background: #ffffff; border: 1px solid #dbe7ea; border-radius: 24px; padding: 28px; transition: border-color .2s, transform .2s; }
.ct-step-card:hover { border-color: #0d9488; transform: translateY(-3px); }
.ct-step-num { display: grid; place-items: center; width: 48px; height: 48px; border-radius: 16px; background: #ccfbf1; color: #0f766e; font-weight: 900; font-family: "Syne", "Bricolage Grotesque", sans-serif; font-size: 18px; margin-bottom: 18px; }
.ct-step-card h3 { margin: 0 0 10px; font-family: "Syne", "Bricolage Grotesque", sans-serif; font-size: 21px; line-height: 1.1; letter-spacing: -.5px; color: #0f172a; }
.ct-step-card p { margin: 0; color: #475569; font-size: 14px; line-height: 1.62; }
.ct-step-timing { display: inline-flex; align-items: center; gap: 5px; margin-top: 12px; padding: 4px 12px; background: #f0fdf4; border-radius: 999px; font-size: 12px; font-weight: 700; color: #15803d; }
.ct-faq-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.ct-faq-item { background: #ffffff; border: 1px solid #dbe7ea; border-radius: 14px; transition: border-color .2s; }
.ct-faq-item[open] { border-color: #0d9488; }
.ct-faq-item summary { list-style: none; cursor: pointer; display: flex; justify-content: space-between; align-items: center; gap: 14px; padding: 18px 22px; font-family: "Syne", "Bricolage Grotesque", sans-serif; font-size: 15.5px; font-weight: 700; color: #0f172a; }
.ct-faq-item summary::-webkit-details-marker { display: none; }
.ct-faq-icon { width: 24px; height: 24px; border-radius: 50%; background: #f1f5f9; border: 1px solid #dbe7ea; display: grid; place-items: center; font-size: 15px; color: #475569; flex: 0 0 24px; transition: transform .2s, background .2s, color .2s, border-color .2s; }
.ct-faq-item[open] .ct-faq-icon { transform: rotate(45deg); background: #ffffff; border-color: #0d9488; color: #0d9488; }
.ct-faq-item p { margin: 0; padding: 0 22px 18px; color: #475569; font-size: 14px; line-height: 1.65; }
.ct-faq-cta-row { margin-top: 20px; font-size: 14px; color: #64748b; }
.ct-faq-cta-row a { color: #0d9488; font-weight: 700; text-decoration: none; margin-right: 8px; }
.ct-faq-cta-row a:hover { color: #0f766e; }
.ct-channels { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 22px; max-width: 700px; }
.ct-channel-link { display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border: 1px solid #dbe7ea; border-radius: 999px; font-size: 14px; font-weight: 700; color: #0f172a; text-decoration: none; background: #fff; transition: border-color .2s, color .2s, background .2s; }
.ct-channel-link:hover { border-color: #0d9488; color: #0d9488; background: #f0fdf9; }
.ct-final { padding: 0 0 110px; background: #ffffff; }
.ct-final-box { background: radial-gradient(circle at 88% 14%, rgba(13,148,136,.32), transparent 34%), #0f172a; color: #ffffff; border-radius: 32px; padding: 48px; display: grid; grid-template-columns: minmax(0,1fr) auto; gap: 36px; align-items: center; }
.ct-final-eyebrow { display: block; margin-bottom: 12px; color: #5eead4; font-size: 11px; font-weight: 900; letter-spacing: 1.4px; text-transform: uppercase; }
.ct-final-box h2 { margin: 0 0 14px; max-width: 680px; font-family: "Syne", "Bricolage Grotesque", sans-serif; font-size: clamp(26px, 3.5vw, 44px); line-height: 1.02; letter-spacing: -1.6px; }
.ct-final-box p { margin: 0; max-width: 600px; color: rgba(255,255,255,.7); font-size: 16px; line-height: 1.65; }
.ct-final-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 52px; padding: 0 26px; border-radius: 999px; background: linear-gradient(135deg, #0d9488, #0f766e); color: #ffffff; font-weight: 900; font-size: 15px; white-space: nowrap; text-decoration: none; box-shadow: 0 14px 32px rgba(13,148,136,.35); transition: transform .2s, box-shadow .2s; }
.ct-final-btn:hover { transform: translateY(-2px); box-shadow: 0 20px 40px rgba(13,148,136,.44); }
[data-animate] { opacity: 0; transform: translateY(20px); transition: opacity .8s ease, transform .8s ease; }
[data-animate="fade-up"] { transform: translateY(20px); }
[data-animate="fade-right"] { transform: translateX(-20px); }
[data-animate="fade-left"] { transform: translateX(20px); }
[data-animate].is-visible { opacity: 1; transform: translateY(0) translateX(0); }
[data-delay="1"] { transition-delay: .1s; }
[data-delay="2"] { transition-delay: .2s; }
@media (prefers-reduced-motion: reduce) { [data-animate] { opacity: 1; transform: none; transition: none; } }
@media (max-width: 1100px) { .ct-hero-form-grid { grid-template-columns: 1fr; gap: 36px; } .ct-form-card { position: static; } .ct-topic-grid, .ct-process-grid, .ct-testimonial-grid { grid-template-columns: 1fr 1fr; } .ct-final-box { grid-template-columns: 1fr; } }
@media (max-width: 720px) { .ct-hero-form { padding: 44px 0 64px; } .ct-h1 { font-size: 38px; letter-spacing: -1.6px; } .ct-lead { font-size: 16px; } .ct-proof-grid { grid-template-columns: repeat(2, minmax(0,1fr)); } .ct-form-card { padding: 22px; border-radius: 22px; } .ct-topics, .ct-process, .ct-faq, .ct-opinions { padding: 64px 0; } .ct-topic-grid, .ct-process-grid, .ct-testimonial-grid { grid-template-columns: 1fr; } .ct-faq-grid { grid-template-columns: 1fr; } .ct-final { padding-bottom: 72px; } .ct-final-box { padding: 28px; } .ct-final-btn { width: 100%; } }
</style>

<main class="ct-art" id="kontakt">
<section class="ct-hero-form" id="formularz-kontaktowy">
    <div class="ct-wrap">
        <div class="ct-hero-form-grid">
            <div class="ct-hero-copy" data-animate="fade-right">
                <div class="ct-eyebrow">Bezpłatna 30-min diagnoza</div>
                <h1 class="ct-h1">Opisz co nie działa w sprzedaży. <span class="ct-h1-accent">Wyjdziesz z 30 minut z mapą problemu.</span></h1>
                <p class="ct-lead">Wystarczy 2-zdaniowy opis: ile masz ruchu, ile zapytań, gdzie rozmowy się rozsypują. Jeśli temat ma sens — zadzwonimy. Jeśli nie pasujemy — powiem wprost po 5 minutach, nie po godzinie pitchu.</p>
                <div class="ct-consult-box"><strong>Co konkretnie wynosisz z tej rozmowy</strong><p>Trzy rzeczy: gdzie tracisz pieniądze już dziś, co poprawić w pierwszej kolejności żeby zatrzymać krwawienie, oraz uczciwą ocenę czy w ogóle warto rozmawiać o współpracy. Bez slajdów, bez „przyślę ofertę po naszej rozmowie", bez follow-upów typu „a może jednak".</p></div>
                <div class="ct-not-list">
                    <div class="ct-not-item"><span class="ct-not-icon">🚫</span><span>Nie dostaniesz szablonu PDF z innym logo i tych samych zaleceń co konkurencja</span></div>
                    <div class="ct-not-item"><span class="ct-not-icon">🚫</span><span>Nie będziesz tłumaczyć swojego biznesu trzy razy trzem różnym osobom</span></div>
                    <div class="ct-not-item"><span class="ct-not-icon">🚫</span><span>Nie usłyszysz „zwiększymy konwersję 3×" zanim ktokolwiek spojrzy na Twoje dane</span></div>
                    <div class="ct-not-item"><span class="ct-not-icon">🚫</span><span>Nie dostaniesz raportu z 47 wykresami CTR zamiast odpowiedzi „czy zarabiamy"</span></div>
                </div>
                <div class="ct-proof-grid">
                    <div class="ct-proof-cell"><strong>30 min</strong><span>rozmowa o Twoim problemie</span></div>
                    <div class="ct-proof-cell"><strong>0 zł</strong><span>pierwsza diagnoza</span></div>
                    <div class="ct-proof-cell"><strong>24h</strong><span>czas odpowiedzi</span></div>
                    <div class="ct-proof-cell"><strong>1 osoba</strong><span>od pierwszej rozmowy do raportu</span></div>
                </div>
                <div class="ct-channels">
                    <a href="tel:<?php echo esc_attr($contact_phone_href); ?>" class="ct-channel-link"><span>📞</span><?php echo esc_html($contact_phone); ?></a>
                    <a href="mailto:<?php echo esc_attr($contact_email); ?>" class="ct-channel-link"><span>✉️</span><?php echo esc_html($contact_email); ?></a>
                    <a href="<?php echo esc_url($linkedin_url); ?>" target="_blank" rel="noopener noreferrer" class="ct-channel-link"><span>💼</span>LinkedIn</a>
                </div>
            </div>

            <aside class="ct-form-card" aria-labelledby="form-heading" data-animate="fade-left" data-delay="1">
                <div class="ct-form-head"><span>Wypełnij formularz</span><h2 id="form-heading">Napisz, co chcesz poprawić</h2><p>Wystarczy krótki opis sytuacji. Nie potrzebujesz gotowego briefu.</p></div>
                <div class="ct-example-box"><b>Przykład odpowiedzi której oczekuję:</b><em>„Robimy 12 demo / mc z reklam, 1-2 zamykają się umową. Podejrzewam że strona albo follow-up zabija leady gdzieś w drodze."</em></div>
                <?php
                echo upsellio_render_lead_form([
                    "origin" => "contact-page-form",
                    "submit_label" => "Wyślij — odpiszę w 24h →",
                    "variant" => "full",
                    "heading" => "",
                    "subheading" => "",
                    "redirect_url" => $contact_page_url,
                    "service_options" => $contact_service_options,
                    "css_class" => "ct-form",
                    "form_id" => "contact-page-form",
                ]);
                ?>
                <div class="ct-form-after"><strong>Co stanie się po wysłaniu?</strong><p>Do 2h dostaniesz potwierdzenie, do 24h wstępną ocenę. Potem umawiamy 30-min rozmowę i wysyłam pisemną mapę problemów.</p></div>
            </aside>
        </div>
    </div>
</section>

<section class="ct-topics" id="o-co-zapytac" data-animate="fade-up">
    <div class="ct-wrap">
        <div class="ct-section-head"><span>O co możesz zapytać?</span><h2>Wiesz że coś nie działa. Niekoniecznie wiesz co dokładnie. To wystarczy.</h2><p>Wystarczy że opiszesz objaw. Pomogę zlokalizować, czy problem jest w ruchu, stronie, ofercie, formularzu czy procesie sprzedaży — i co naprawić najpierw.</p></div>
        <div class="ct-topic-grid">
            <div class="ct-topic-card"><div class="ct-topic-icon">📢</div><strong>Google Ads B2B</strong><p>Dlaczego kampania wydaje budżet, ale nie daje klientów?</p><span class="ct-topic-symptom">Kliknięcia są, rozmów nie ma</span><?php if ($google_ads_url !== "") : ?><br><a class="ct-topic-link" href="<?php echo esc_url($google_ads_url); ?>">Usługa Google Ads →</a><?php endif; ?></div>
            <div class="ct-topic-card"><div class="ct-topic-icon">📱</div><strong>Meta Ads (Facebook/Instagram)</strong><p>Dlaczego reklamy generują ruch, ale zapytania są złej jakości?</p><span class="ct-topic-symptom">Leady od osób, które nie kupują</span><?php if ($meta_ads_url !== "") : ?><br><a class="ct-topic-link" href="<?php echo esc_url($meta_ads_url); ?>">Usługa Meta Ads →</a><?php endif; ?></div>
            <div class="ct-topic-card"><div class="ct-topic-icon">🌐</div><strong>Strona WWW / Landing Page</strong><p>Dlaczego ludzie wchodzą na stronę i nie wysyłają formularza?</p><span class="ct-topic-symptom">Wysoki bounce rate, zero leadów</span><?php if ($websites_url !== "") : ?><br><a class="ct-topic-link" href="<?php echo esc_url($websites_url); ?>">Usługa strony WWW →</a><?php endif; ?></div>
            <div class="ct-topic-card"><div class="ct-topic-icon">🛒</div><strong>Sklep internetowy B2B</strong><p>Dlaczego produkty są oglądane, ale koszyk i zamówienia stoją?</p><span class="ct-topic-symptom">Koszyki się napełniają, zamówienia nie wpływają</span><br><a class="ct-topic-link" href="#formularz-kontaktowy">Opisz problem →</a></div>
            <div class="ct-topic-card"><div class="ct-topic-icon">🔍</div><strong>SEO i ruch organiczny</strong><p>Dlaczego ruch z Google nie przekłada się na wartościowe zapytania?</p><span class="ct-topic-symptom">Pozycje są, konwersja nie</span><br><a class="ct-topic-link" href="#formularz-kontaktowy">Opisz problem →</a></div>
            <div class="ct-topic-card"><div class="ct-topic-icon">💬</div><strong>Oferta i jakość leadów</strong><p>Dlaczego leady są, ale rozmowy nie kończą się sprzedażą?</p><span class="ct-topic-symptom">Dużo rozmów, mało zamkniętych</span><br><a class="ct-topic-link" href="#formularz-kontaktowy">Opisz problem →</a></div>
        </div>
    </div>
</section>

<section class="ct-opinions" id="opinie" data-animate="fade-up">
    <div class="ct-wrap">
        <div class="ct-section-head"><span>Opinie klientów</span><h2>Co mówią firmy po pierwszej rozmowie</h2></div>
        <div class="ct-testimonial-grid">
            <blockquote class="ct-testimonial"><p class="ct-testimonial-quote">Po analizie wiedzieliśmy dokładnie, gdzie ucieka budżet i które elementy strony trzeba poprawić jako pierwsze. Działamy od tamtej pory systematycznie.</p><div class="ct-testimonial-author"><div class="ct-testimonial-avatar">M</div><div><div class="ct-testimonial-name">Marek T.</div><div class="ct-testimonial-role">Właściciel · firma produkcyjna B2B</div></div></div></blockquote>
            <blockquote class="ct-testimonial"><p class="ct-testimonial-quote">Największa zmiana to przejście z raportów o kliknięciach na rozmowę o jakości leadów i realnym koszcie pozyskania. Wreszcie marketing mówi językiem sprzedaży.</p><div class="ct-testimonial-author"><div class="ct-testimonial-avatar">A</div><div><div class="ct-testimonial-name">Anna K.</div><div class="ct-testimonial-role">Marketing Manager · usługi profesjonalne</div></div></div></blockquote>
            <blockquote class="ct-testimonial"><p class="ct-testimonial-quote">Kampanie i landing zaczęły działać jak jeden system. Mniej chaosu, więcej konkretnych zapytań od firm, z którymi warto rozmawiać.</p><div class="ct-testimonial-author"><div class="ct-testimonial-avatar">P</div><div><div class="ct-testimonial-name">Piotr R.</div><div class="ct-testimonial-role">CEO · SaaS B2B</div></div></div></blockquote>
        </div>
    </div>
</section>

<section class="ct-process" id="jak-dziala" data-animate="fade-up">
    <div class="ct-wrap">
        <div class="ct-section-head"><span>Jak to działa?</span><h2>Krótko, konkretnie, bez przeciągania.</h2><p>Od formularza do konkretnej odpowiedzi — opisuję każdy krok, żebyś wiedział czego się spodziewać.</p></div>
        <div class="ct-process-grid">
            <div class="ct-step-card"><div class="ct-step-num">01</div><h3>Wysyłasz formularz</h3><p>Opisujesz, co nie działa. Może to być jedno zdanie, link do strony albo konkretny problem z kampanią lub leadami.</p><span class="ct-step-timing">⏱ Teraz, 2 minuty</span></div>
            <div class="ct-step-card"><div class="ct-step-num">02</div><h3>Twój przypadek trafia pod konkretną parę oczu</h3><p>Patrzę na Twoją stronę, kampanie i komunikat z perspektywy 10 lat sprzedaży B2B — nie freelancera od reklam. Szukam miejsc gdzie pieniądze wyciekają zanim klient zdąży zapytać o cenę.</p><span class="ct-step-timing">⏱ W ciągu 24h</span></div>
            <div class="ct-step-card"><div class="ct-step-num">03</div><h3>Dostajesz konkretny kierunek</h3><p>Wracam z odpowiedzią: co poprawić najpierw, gdzie jest problem i czy jest sens rozmawiać dalej. Bez owijania w bawełnę.</p><span class="ct-step-timing">⏱ Bezpłatnie</span></div>
        </div>
    </div>
</section>

<section class="ct-faq" id="faq" data-animate="fade-up">
    <div class="ct-wrap">
        <div class="ct-section-head"><span>Zanim wyślesz formularz</span><h2>Odpowiedzi na najczęstsze pytania</h2><p>Jeśli nie znajdziesz odpowiedzi — napisz, a odpowiem bezpośrednio.</p></div>
        <div class="ct-faq-grid">
            <?php foreach ($contact_faq_items as $faq_item) : ?>
            <details class="ct-faq-item"><summary><span><?php echo esc_html((string) $faq_item["question"]); ?></span><span class="ct-faq-icon" aria-hidden="true">+</span></summary><p><?php echo esc_html((string) $faq_item["answer"]); ?></p></details>
            <?php endforeach; ?>
        </div>
        <p class="ct-faq-cta-row">Potrzebujesz więcej kontekstu o usługach?
            <?php if ($google_ads_url !== "") : ?><a href="<?php echo esc_url($google_ads_url); ?>">Google Ads B2B</a> ·<?php endif; ?>
            <?php if ($meta_ads_url !== "") : ?><a href="<?php echo esc_url($meta_ads_url); ?>">Meta Ads B2B</a> ·<?php endif; ?>
            <?php if ($websites_url !== "") : ?><a href="<?php echo esc_url($websites_url); ?>">Strony WWW</a><?php endif; ?>
        </p>
    </div>
</section>

<section class="ct-final">
    <div class="ct-wrap">
        <div class="ct-final-box">
            <div><span class="ct-final-eyebrow">Nie odkładaj na później</span><h2>Jeśli leady uciekają, zwykle da się znaleźć konkretny powód.</h2><p>Wystarczą 2 zdania. Wrócisz do swojej pracy, a do 24h dostaniesz wstępną odpowiedź: gdzie szukać problemu w pierwszej kolejności — reklama, strona, oferta, formularz czy proces sprzedaży. Pierwsze 30 minut bez opłat, bez zobowiązań.</p></div>
            <a href="#formularz-kontaktowy" class="ct-final-btn">Wróć do formularza<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
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

<script type="application/ld+json">
<?php
echo wp_json_encode([
    "@context" => "https://schema.org",
    "@type" => "ContactPage",
    "name" => "Kontakt — Bezpłatna diagnoza marketingu B2B | Upsellio",
    "url" => $contact_page_url,
    "description" => "Bezpłatna 30-minutowa diagnoza kampanii Google Ads, Meta Ads i stron WWW dla firm B2B. Sebastian Kelm — specjalista marketingu i sprzedaży B2B.",
    "mainEntity" => [
        "@type" => "LocalBusiness",
        "name" => "Upsellio — Marketing B2B",
        "url" => home_url("/"),
        "email" => $contact_email,
        "telephone" => $contact_phone,
        "areaServed" => "Polska",
        "availableLanguage" => "Polish",
        "founder" => [
            "@type" => "Person",
            "name" => "Sebastian Kelm",
            "jobTitle" => "Specjalista marketingu B2B",
            "sameAs" => $linkedin_url,
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
</script>

<script>
(function() {
    'use strict';
    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    io.unobserve(entry.target);
                }
            });
        }, { rootMargin: '0px 0px -10% 0px', threshold: 0.08 });
        document.querySelectorAll('[data-animate]').forEach(function(el) { io.observe(el); });
    } else {
        document.querySelectorAll('[data-animate]').forEach(function(el) { el.classList.add('is-visible'); });
    }

    var contactForm = document.getElementById('contact-page-form');
    if (contactForm) {
        var msg = contactForm.querySelector('textarea[name="lead_message"]');
        if (msg) {
            msg.setAttribute('placeholder', 'Np. Robimy 12 demo / mc z reklam, 1-2 zamykają się umową. Podejrzewam że strona albo follow-up zabija leady gdzieś w drodze.');
        }
    }

    document.querySelectorAll('a[href^="#"]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            var href = link.getAttribute('href');
            if (href.length < 2) return;
            var target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
})();
</script>

<?php get_footer(); ?>
