<?php
/*
Template Name: Upsellio - Landing Reklamowy (Diagnoza)
Template Post Type: page
Description: Landing page pod ruch z Google Ads i Meta Ads. Jeden cel: zapis na bezpłatną 30-min rozmowę diagnostyczną.
*/
if (!defined("ABSPATH")) {
    exit;
}

add_filter(
    "wp_robots",
    static function (array $robots): array {
        $robots["noindex"] = true;
        $robots["follow"] = true;
        return $robots;
    },
    20
);

/* ============================================================
   USTAWIENIA
   ============================================================ */

$front_page_sections = function_exists("upsellio_get_front_page_content_config")
    ? upsellio_get_front_page_content_config()
    : [];
$contact_phone = function_exists("upsellio_get_contact_phone")
    ? upsellio_get_contact_phone()
    : trim((string) ($front_page_sections["contact_phone"] ?? "+48 575 522 595"));
$contact_phone_href = preg_replace("/\s+/", "", (string) $contact_phone);
$contact_email = trim((string) ($front_page_sections["contact_email"] ?? "kontakt@upsellio.pl"));
$current_page_url = function_exists("get_permalink") ? (string) (get_permalink() ?: home_url("/")) : home_url("/");

/* Klient wybiera czy interesuje go Google, Meta czy oba */
$lp_service_options = [
    "Google Ads",
    "Meta Ads (Facebook / Instagram)",
    "Oba kanały (Google + Meta)",
    "Nie wiem — chcę porozmawiać",
];

/* FAQ pod konwersję - krótkie, bezpośrednie odpowiedzi */
$lp_faq_items = [
    [
        "question" => "Czy 30-minutowa rozmowa naprawdę nic nie kosztuje?",
        "answer" => "Tak, naprawdę nic nie kosztuje i nie wymaga żadnych zobowiązań. To moja inwestycja w to, żeby zobaczyć czy w ogóle pasujemy. Jeśli po rozmowie zorientujesz się że nie chcesz pracować ze mną — wychodzisz z 3-5 konkretnymi rzeczami do poprawy w marketingu, które możesz wdrożyć sam lub z innym wykonawcą.",
    ],
    [
        "question" => "Skąd mam wiedzieć, że to nie kolejny pitch agencyjny?",
        "answer" => "Najuczciwsza odpowiedź: w pierwszej rozmowie. 30 minut, bez prezentacji, bez slajdów. Jeśli po niej masz wrażenie że gadałeś z kolejnym handlowcem — nie zatrudniaj mnie. Jeśli wyniosłeś z tego konkretne wskazówki których nikt wcześniej nie dał — masz odpowiedź. Pierwsze 30 minut to nie sprzedaż. To próbka pracy.",
    ],
    [
        "question" => "Jaki budżet reklamowy muszę mieć, żeby to miało sens?",
        "answer" => "Minimum 2 000-3 000 zł/mies. na platformę (Google lub Meta). Przy mniejszym budżecie kampanie nie zbierają wystarczająco danych żeby się optymalizować — i ani Ty, ani ja nie zobaczymy realnych wyników. Jeśli jesteś poniżej tego progu, na rozmowie powiem to wprost i polecę co zrobić zanim zaczniemy.",
    ],
    [
        "question" => "Po jakim czasie zobaczę pierwsze efekty?",
        "answer" => "Pierwsze sygnały (ruch, pierwsze leady) zwykle po 2-4 tygodniach. Stabilny, przewidywalny napływ kwalifikowanych zapytań — po 2-3 miesiącach optymalizacji. Działam iteracyjnie: analiza → wdrożenie → pomiar → poprawki. Bez przyspieszania budżetem.",
    ],
    [
        "question" => "Czy pracujesz sam, czy z zespołem agencji?",
        "answer" => "Sam. Bez juniorów, bez rotującego account managera, bez handoffu między działami. Twoje kampanie prowadzę ja — Sebastian — od pierwszej rozmowy do raportu. To wada (przyjmuję 5-7 klientów jednocześnie) i zaleta (nie tłumaczysz swojego biznesu trzy razy trzem różnym osobom).",
    ],
    [
        "question" => "Co jeśli już mam agencję i nie wiem, czy zmieniać?",
        "answer" => "Możesz przyjść z konkretnym pytaniem typu czy moje obecne kampanie są ustawione sensownie. Patrzę na konto, mówię co zmieniłbym i dlaczego. Jeśli Twoja obecna agencja robi dobrą robotę — usłyszysz to ode mnie. Jeśli nie — będziesz wiedział na czym konkretnie polega problem, niezależnie od tego z kim zdecydujesz się pracować.",
    ],
];

get_header();
?>

<style>
/* ============================================================
   LANDING REKLAMOWY UPSELLIO
   Prefiks .lp- (landing page)
   Cel: jedna konwersja - zapis na 30-min diagnoze
   ============================================================ */

/* === UKRYJ NAWIGACJE GŁÓWNE + WYCIEKI ===
   WordPress: body.page-template-page-landing-reklama-php */
body.page-template-page-landing-reklama-php .nav-links,
body.page-template-page-landing-reklama-php .nav-dropdown,
body.page-template-page-landing-reklama-php .mobile-menu,
body.page-template-page-landing-reklama-php .hamburger,
body.page-template-page-landing-reklama-php .nav-cta,
body.page-template-page-landing-reklama-php .mobile-sticky-cta,
body.page-template-page-landing-reklama-php .ups-breadcrumbs,
body[class*="page-landing-reklama"] .nav-links,
body[class*="page-landing-reklama"] .nav-dropdown,
body[class*="page-landing-reklama"] .mobile-menu,
body[class*="page-landing-reklama"] .hamburger,
body[class*="page-landing-reklama"] .nav-cta,
body[class*="page-landing-reklama"] .mobile-sticky-cta,
body[class*="page-landing-reklama"] .ups-breadcrumbs {
    display: none !important;
}

/* === GLOBALNE === */
.lp { font-family: "DM Sans", system-ui, sans-serif; color: #0d0d0b; background: #fafaf6; line-height: 1.65; }
.lp *, .lp *::before, .lp *::after { box-sizing: border-box; }
.lp-wrap { max-width: 1180px; margin-inline: auto; padding: 0 24px; }

/* === HERO === */
.lp-hero {
    padding: 60px 0 88px;
    background:
        radial-gradient(ellipse at 88% 8%, rgba(13,148,136,.16), transparent 36%),
        radial-gradient(ellipse at 12% 90%, rgba(249,115,22,.06), transparent 32%),
        linear-gradient(180deg, #fafaf6 0%, #f1f1ec 100%);
}
.lp-hero-grid {
    display: grid;
    grid-template-columns: 1.1fr 1fr;
    gap: 56px;
    align-items: start;
}
.lp-eyebrow {
    display: inline-flex; align-items: center; gap: 10px;
    margin-bottom: 18px;
    color: #f97316;
    font-size: 12px; font-weight: 900;
    letter-spacing: 1.4px; text-transform: uppercase;
}
.lp-eyebrow::before {
    content: ""; width: 24px; height: 2px;
    background: #f97316; border-radius: 2px;
}
.lp-eyebrow-light { color: #5eead4; }
.lp-eyebrow-light::before { background: #5eead4; }

.lp-h1 {
    font-family: "Syne", "Bricolage Grotesque", sans-serif;
    font-weight: 800;
    font-size: clamp(40px, 5.4vw, 64px);
    line-height: .98; letter-spacing: -2px;
    margin: 0 0 22px;
    color: #0a1410;
}
.lp-h1 em { font-style: normal; color: #0d9488; }

.lp-lead {
    font-size: 18px; line-height: 1.65;
    color: #3d3d38; max-width: 56ch; margin: 0 0 28px;
}

/* Lista korzyści w hero */
.lp-hero-bullets {
    list-style: none; padding: 0; margin: 0 0 32px;
    display: grid; gap: 12px;
}
.lp-hero-bullets li {
    display: flex; align-items: flex-start; gap: 12px;
    font-size: 15px; color: #1a1a17; line-height: 1.55;
}
.lp-bullet-icon {
    flex: 0 0 24px; width: 24px; height: 24px;
    border-radius: 50%; background: #ccfbf1; color: #0f766e;
    display: grid; place-items: center;
    font-weight: 900; font-size: 13px;
    margin-top: 1px;
}

.lp-hero-trust {
    display: flex; flex-wrap: wrap; gap: 18px;
    padding: 18px 22px;
    background: rgba(255,255,255,.7);
    border: 1px solid #e7e7e1; border-radius: 16px;
    font-size: 13px; color: #3d3d38;
}
.lp-hero-trust > div {
    display: flex; align-items: center; gap: 8px;
    font-weight: 600;
}
.lp-hero-trust strong { color: #0d9488; font-size: 15px; }

.lp-hero-cta-row { margin: 20px 0 0; }
.lp-hero-cta {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 800;
    font-size: 15px;
    color: #0f766e;
    text-decoration: none;
    border-bottom: 2px solid rgba(13, 148, 136, 0.35);
    padding-bottom: 3px;
}
.lp-hero-cta:hover { color: #115e59; border-bottom-color: #0d9488; }

/* === KARTA FORMULARZA === */
.lp-form-card {
    background: #ffffff;
    border: 1px solid #dbe7ea;
    border-radius: 24px;
    padding: 32px;
    box-shadow:
        0 24px 60px rgba(15,23,42,.1),
        0 4px 8px rgba(15,23,42,.04);
    position: sticky; top: 24px;
}
.lp-form-card-tag {
    display: inline-block;
    padding: 5px 12px; background: #f0fdf4;
    border-radius: 99px;
    font-size: 11px; font-weight: 900;
    letter-spacing: 1.2px; text-transform: uppercase;
    color: #15803d; margin-bottom: 12px;
}
.lp-form-card h2 {
    margin: 0 0 8px;
    font-family: "Syne", "Bricolage Grotesque", sans-serif;
    font-size: 26px; line-height: 1.08;
    letter-spacing: -.8px; color: #0a1410;
}
.lp-form-card-sub {
    margin: 0 0 22px;
    color: #64748b;
    font-size: 14px; line-height: 1.55;
}
.lp-form-card-after {
    margin-top: 18px;
    padding: 14px 16px;
    background: #f8fafc; border: 1px solid #e2e8f0;
    border-radius: 12px;
    font-size: 12.5px; color: #475569; line-height: 1.55;
}
.lp-form-card-after strong { color: #0a1410; display: block; margin-bottom: 4px; }

/* Stylowanie pól formularza generowanych przez upsellio_render_lead_form */
.lp-form-card .ups-form input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),
.lp-form-card .ups-form select,
.lp-form-card .ups-form textarea {
    width: 100%; min-height: 46px;
    background: #ffffff; border: 1.5px solid #e7e7e1;
    border-radius: 12px; padding: 11px 14px;
    color: #0a1410; font-size: 14.5px; font-family: inherit;
    transition: border-color .15s, box-shadow .15s;
}
.lp-form-card .ups-form textarea { min-height: 100px; resize: vertical; line-height: 1.55; }
.lp-form-card .ups-form input:focus,
.lp-form-card .ups-form select:focus,
.lp-form-card .ups-form textarea:focus {
    border-color: #0d9488;
    box-shadow: 0 0 0 4px rgba(13,148,136,.13);
    outline: none;
}
.lp-form-card .ups-form__consent {
    display: flex; align-items: flex-start; gap: 10px;
    font-size: 12.5px; color: #475569; line-height: 1.5;
}
.lp-form-card .ups-form__consent input[type="checkbox"] {
    width: 18px; height: 18px; min-height: 18px;
    margin: 2px 0 0; flex-shrink: 0;
    accent-color: #0d9488;
}
.lp-form-card .ups-form__submit,
.lp-form-card button[type="submit"] {
    width: 100%; min-height: 56px;
    border: 0; border-radius: 999px;
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
    color: #ffffff; font-family: inherit;
    font-size: 15px; font-weight: 900;
    cursor: pointer; margin-top: 6px;
    box-shadow: 0 18px 40px rgba(13,148,136,.28);
    transition: transform .18s, box-shadow .18s;
}
.lp-form-card .ups-form__submit:hover,
.lp-form-card button[type="submit"]:hover {
    transform: translateY(-1px);
    box-shadow: 0 22px 44px rgba(13,148,136,.34);
}

/* === SEKCJE WSPOLNE === */
.lp-section { padding: 88px 0; }
.lp-section-soft { background: #f1f1ec; }
.lp-section-dark {
    background: #0a1410; color: #fff;
    position: relative; overflow: hidden;
}
.lp-section-dark::before {
    content: ""; position: absolute;
    width: 600px; height: 600px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(20,184,166,.18), transparent 65%);
    right: -240px; top: -240px;
    pointer-events: none;
}
.lp-section-dark .lp-wrap { position: relative; z-index: 2; }

.lp-sec-head { max-width: 760px; margin-bottom: 36px; }
.lp-sec-head h2 {
    margin: 0;
    font-family: "Syne", "Bricolage Grotesque", sans-serif;
    font-weight: 800;
    font-size: clamp(30px, 3.6vw, 46px);
    line-height: 1.04; letter-spacing: -1.4px;
    color: #0a1410;
}
.lp-section-dark .lp-sec-head h2 { color: #fff; }
.lp-sec-head p {
    margin: 14px 0 0; max-width: 64ch;
    color: #475569; font-size: 17px; line-height: 1.65;
}
.lp-section-dark .lp-sec-head p { color: rgba(255,255,255,.7); }

/* === SEKCJA "DLA KOGO" - 3 sytuacje === */
.lp-symptoms {
    display: grid; grid-template-columns: repeat(3, minmax(0,1fr));
    gap: 16px;
}
.lp-symptom {
    background: #fff; border: 1px solid #dbe7ea;
    border-radius: 20px; padding: 28px;
    transition: border-color .2s, transform .2s, box-shadow .2s;
}
.lp-symptom:hover {
    border-color: #0d9488; transform: translateY(-3px);
    box-shadow: 0 18px 36px rgba(13,148,136,.1);
}
.lp-symptom-icon {
    width: 52px; height: 52px;
    border-radius: 14px; background: #fef3c7;
    color: #92400e; display: grid; place-items: center;
    font-size: 24px; margin-bottom: 18px;
}
.lp-symptom h3 {
    margin: 0 0 10px;
    font-family: "Syne", "Bricolage Grotesque", sans-serif;
    font-size: 19px; line-height: 1.18;
    letter-spacing: -.4px; color: #0a1410;
}
.lp-symptom p {
    margin: 0; color: #475569;
    font-size: 14.5px; line-height: 1.6;
}

/* === DIAGNOZA - co dostajesz w 30 minut === */
.lp-diagnosis {
    background: #fff; border: 1px solid #e7e7e1;
    border-radius: 28px;
    overflow: hidden;
    box-shadow: 0 18px 44px rgba(15,23,42,.06);
    display: grid; grid-template-columns: 1fr 1fr;
}
.lp-diagnosis-side {
    background: linear-gradient(165deg, #0d9488 0%, #115e59 100%);
    color: #fff;
    padding: 48px 40px;
    position: relative; overflow: hidden;
}
.lp-diagnosis-side::before {
    content: ""; position: absolute;
    top: -100px; right: -100px;
    width: 300px; height: 300px;
    border-radius: 50%;
    background: rgba(255,255,255,.06);
}
.lp-diagnosis-side .lp-eyebrow { color: #5eead4; }
.lp-diagnosis-side .lp-eyebrow::before { background: #5eead4; }
.lp-diagnosis-side h2 {
    font-family: "Syne", "Bricolage Grotesque", sans-serif;
    font-weight: 800; font-size: 32px;
    line-height: 1.06; letter-spacing: -1.2px;
    margin: 0 0 18px;
    color: #fff; max-width: 14ch;
    position: relative;
}
.lp-diagnosis-side p {
    margin: 0; color: rgba(255,255,255,.85);
    font-size: 15px; line-height: 1.65;
    position: relative;
}
.lp-diagnosis-list {
    padding: 48px 40px;
    list-style: none; margin: 0;
    display: grid; gap: 18px;
}
.lp-diagnosis-list li {
    display: grid; grid-template-columns: 32px 1fr; gap: 14px;
    align-items: flex-start;
}
.lp-diagnosis-num {
    width: 32px; height: 32px;
    border-radius: 50%; background: #ccfbf1;
    color: #0f766e; font-weight: 900;
    font-family: "Syne", sans-serif;
    font-size: 14px;
    display: grid; place-items: center;
}
.lp-diagnosis-list li b {
    display: block;
    font-family: "Syne", "Bricolage Grotesque", sans-serif;
    font-weight: 800; font-size: 16px;
    color: #0a1410; margin-bottom: 4px;
}
.lp-diagnosis-list li span {
    font-size: 14px; color: #475569; line-height: 1.55;
}

/* === PROCES (3 kroki, prosty layout) === */
.lp-process-grid {
    display: grid; grid-template-columns: repeat(3, minmax(0,1fr));
    gap: 14px;
    counter-reset: lpstep;
}
.lp-step {
    background: #fff; border: 1px solid #e7e7e1;
    border-radius: 20px; padding: 28px;
    position: relative;
}
.lp-step::before {
    counter-increment: lpstep;
    content: counter(lpstep, decimal-leading-zero);
    position: absolute;
    top: 22px; right: 22px;
    font-family: "Syne", sans-serif;
    font-size: 56px; font-weight: 800;
    color: #ccfbf1; line-height: 1;
    letter-spacing: -2px;
}
.lp-step h3 {
    margin: 0 0 8px;
    font-family: "Syne", "Bricolage Grotesque", sans-serif;
    font-size: 19px; line-height: 1.15;
    letter-spacing: -.4px; color: #0a1410;
    max-width: 14ch;
    position: relative;
}
.lp-step p {
    margin: 0; color: #475569;
    font-size: 14.5px; line-height: 1.6;
    position: relative;
}

/* === PROOF (case studies skondensowane) === */
.lp-proof-grid {
    display: grid; grid-template-columns: repeat(3, minmax(0,1fr));
    gap: 18px;
}
.lp-proof {
    background: rgba(255,255,255,.045);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 20px; padding: 30px;
}
.lp-proof-tag {
    font-size: 11px; letter-spacing: 1.4px;
    text-transform: uppercase; color: rgba(255,255,255,.5);
    margin-bottom: 18px;
}
.lp-proof-num {
    font-family: "Syne", "Bricolage Grotesque", sans-serif;
    font-weight: 800; font-size: 44px;
    color: #5eead4; letter-spacing: -1.4px;
    line-height: 1;
}
.lp-proof-label {
    color: #fff; margin: 12px 0 14px;
    font-size: 14px; font-weight: 700;
}
.lp-proof p {
    margin: 0; color: rgba(255,255,255,.65);
    font-size: 13.5px; line-height: 1.6;
}

/* === AUTHOR (kto z toba rozmawia) === */
.lp-author {
    background: #fff; border: 1px solid #e7e7e1;
    border-radius: 28px; padding: 40px;
    display: grid; grid-template-columns: 200px 1fr;
    gap: 36px; align-items: center;
    box-shadow: 0 18px 44px rgba(15,23,42,.06);
}
.lp-author-photo {
    aspect-ratio: 1;
    border-radius: 20px;
    background: linear-gradient(165deg, #ccfbf1, #dff8f4);
    border: 1px solid #99f6e4;
    overflow: hidden;
    position: relative;
}
.lp-author-photo img {
    width: 100%; height: 100%; object-fit: cover;
    display: block;
}
.lp-author-photo-placeholder {
    position: absolute; inset: 0;
    background-image: repeating-linear-gradient(135deg,
        rgba(13,148,136,.12) 0 12px, transparent 12px 24px);
    display: grid; place-items: center;
    color: #0f766e; font-family: ui-monospace, monospace;
    font-size: 12px;
}
.lp-author-tag {
    display: inline-block; padding: 4px 12px;
    background: #f0fdfa; color: #0f766e;
    border-radius: 99px;
    font-size: 11px; font-weight: 900;
    letter-spacing: 1.2px; text-transform: uppercase;
    margin-bottom: 12px;
}
.lp-author h3 {
    margin: 0 0 8px;
    font-family: "Syne", "Bricolage Grotesque", sans-serif;
    font-size: 26px; line-height: 1.1;
    letter-spacing: -.6px;
}
.lp-author > div p {
    margin: 0 0 18px; color: #475569;
    font-size: 15px; line-height: 1.65;
}
.lp-author-stats {
    display: grid; grid-template-columns: repeat(3, minmax(0,1fr));
    gap: 12px;
}
.lp-author-stats > div {
    padding: 14px 16px;
    background: #fafaf6; border: 1px solid #e7e7e1;
    border-radius: 12px;
}
.lp-author-stats b {
    display: block;
    font-family: "Syne", sans-serif;
    font-weight: 800; font-size: 22px;
    color: #0d9488; line-height: 1;
}
.lp-author-stats span {
    display: block;
    font-size: 12px;
    color: #64748b; margin-top: 5px;
    line-height: 1.4;
}

/* === FAQ === */
.lp-faq { display: grid; gap: 10px; max-width: 880px; }
.lp-faq-item {
    background: #fff; border: 1px solid #e7e7e1;
    border-radius: 16px;
    transition: border-color .2s;
}
.lp-faq-item[open] { border-color: #0d9488; }
.lp-faq-item summary {
    list-style: none; cursor: pointer;
    display: flex; justify-content: space-between; align-items: center;
    gap: 16px; padding: 22px 26px;
    font-family: "Syne", "Bricolage Grotesque", sans-serif;
    font-size: 17px; font-weight: 700;
    color: #0a1410;
}
.lp-faq-item summary::-webkit-details-marker { display: none; }
.lp-faq-icon {
    width: 28px; height: 28px;
    border-radius: 50%; background: #f1f5f9;
    display: grid; place-items: center;
    font-size: 18px; color: #475569; flex: 0 0 28px;
    transition: transform .2s, background .2s, color .2s;
}
.lp-faq-item[open] .lp-faq-icon {
    transform: rotate(45deg);
    background: #ccfbf1; color: #0f766e;
}
.lp-faq-item p {
    margin: 0; padding: 0 26px 22px;
    color: #475569; font-size: 15px; line-height: 1.65;
}

/* === FINAL CTA === */
.lp-final {
    padding: 0 0 110px;
}
.lp-final-box {
    background:
        radial-gradient(circle at 88% 20%, rgba(13,148,136,.4), transparent 38%),
        #0a1410;
    color: #fff;
    border-radius: 32px; padding: 56px;
    text-align: center;
}
.lp-final-box h2 {
    font-family: "Syne", "Bricolage Grotesque", sans-serif;
    font-weight: 800;
    font-size: clamp(30px, 4vw, 50px);
    line-height: 1.02; letter-spacing: -1.6px;
    margin: 0 auto 16px;
    color: #fff; max-width: 22ch;
}
.lp-final-box p {
    max-width: 56ch; margin: 0 auto 28px;
    color: rgba(255,255,255,.7);
    font-size: 17px; line-height: 1.65;
}
.lp-final-btn {
    display: inline-flex; align-items: center; justify-content: center;
    gap: 10px; min-height: 60px; padding: 0 36px;
    border-radius: 999px;
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
    color: #fff; font-weight: 900; font-size: 16px;
    text-decoration: none;
    box-shadow: 0 20px 44px rgba(13,148,136,.42);
    transition: transform .2s, box-shadow .2s;
}
.lp-final-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 26px 52px rgba(13,148,136,.5);
}
.lp-final-note {
    margin: 22px 0 0; font-size: 13px;
    color: rgba(255,255,255,.5);
}
.lp-final-form-wrap {
    max-width: 560px;
    margin: 28px auto 0;
}
.lp-final-form-wrap .lp-form-card {
    position: static;
    text-align: left;
    margin: 0 auto;
}

/* === STICKY MOBILE CTA === */
.lp-mobile-cta {
    display: none;
    position: fixed; bottom: 0; left: 0; right: 0;
    background: #fff;
    border-top: 1px solid #e7e7e1;
    padding: 12px 16px;
    z-index: 100;
    box-shadow: 0 -6px 24px rgba(15,23,42,.08);
}
.lp-mobile-cta a {
    display: flex; align-items: center; justify-content: center;
    width: 100%; min-height: 50px;
    background: linear-gradient(135deg, #0d9488, #0f766e);
    color: #fff; font-weight: 800; font-size: 15px;
    text-decoration: none; border-radius: 999px;
    box-shadow: 0 12px 24px rgba(13,148,136,.3);
}

/* === ANIMACJE === */
[data-animate] { opacity: 0; transform: translateY(18px); transition: opacity .8s ease, transform .8s ease; }
[data-animate].is-visible { opacity: 1; transform: translateY(0); }
[data-delay="1"] { transition-delay: .1s; }
[data-delay="2"] { transition-delay: .2s; }
@media (prefers-reduced-motion: reduce) {
    [data-animate] { opacity: 1; transform: none; transition: none; }
}

/* === RESPONSIVE === */
@media (max-width: 1024px) {
    .lp-hero-grid { grid-template-columns: 1fr; gap: 40px; }
    .lp-form-card { position: static; }
    .lp-symptoms,
    .lp-process-grid,
    .lp-proof-grid { grid-template-columns: 1fr 1fr; }
    .lp-diagnosis { grid-template-columns: 1fr; }
    .lp-diagnosis-side { padding: 36px 32px; }
    .lp-diagnosis-list { padding: 36px 32px; }
    .lp-author { grid-template-columns: 140px 1fr; gap: 24px; padding: 32px; }
}
@media (max-width: 720px) {
    .lp-hero { padding: 36px 0 56px; }
    .lp-section { padding: 64px 0; }
    .lp-symptoms,
    .lp-process-grid,
    .lp-proof-grid { grid-template-columns: 1fr; }
    .lp-form-card { padding: 24px; border-radius: 20px; }
    .lp-final { padding-bottom: 100px; }
    .lp-final-box { padding: 32px 22px; border-radius: 24px; }
    .lp-author { grid-template-columns: 1fr; padding: 24px; }
    .lp-author-photo { max-width: 200px; margin: 0 auto; }
    .lp-author-stats { grid-template-columns: 1fr 1fr; }
    .lp-mobile-cta { display: block; }
    .lp { padding-bottom: 80px; }
    .lp-step::before { font-size: 44px; top: 18px; right: 18px; }
}
</style>

<main class="lp" id="diagnoza-marketingu">

<section class="lp-hero">
    <div class="lp-wrap">
        <div class="lp-hero-grid">

            <div class="lp-hero-copy" data-animate>
                <div class="lp-eyebrow">⚡ Bezpłatna 30-min diagnoza</div>

                <h1 class="lp-h1">
                    Wydajesz na Google Ads albo Meta Ads,<br>
                    a leady <em>nie przychodzą?</em>
                </h1>

                <p class="lp-lead">
                    W 30 minut pokażę Ci dokładnie gdzie tracisz pieniądze i co poprawić w pierwszej
                    kolejności — niezależnie od tego, czy ostatecznie zdecydujesz się ze mną pracować.
                    Bez prezentacji, bez slajdów, bez „przyślę ofertę po naszej rozmowie".
                </p>

                <ul class="lp-hero-bullets">
                    <li>
                        <span class="lp-bullet-icon">✓</span>
                        <span>Sprawdzę Twoje obecne kampanie i stronę docelową</span>
                    </li>
                    <li>
                        <span class="lp-bullet-icon">✓</span>
                        <span>Wskażę 3-5 konkretnych miejsc, w których uciekają zapytania</span>
                    </li>
                    <li>
                        <span class="lp-bullet-icon">✓</span>
                        <span>Powiem wprost, czy w ogóle warto rozmawiać o współpracy</span>
                    </li>
                    <li>
                        <span class="lp-bullet-icon">✓</span>
                        <span>Wyjdziesz z mapą do wdrożenia, nawet jeśli nie zostaniesz klientem</span>
                    </li>
                </ul>

                <div class="lp-hero-trust">
                    <div>
                        <strong>10 lat</strong> sprzedaży B2B
                    </div>
                    <div>
                        <strong>0 zł</strong> pierwsza rozmowa
                    </div>
                    <div>
                        <strong>1:1</strong> rozmawiasz ze mną
                    </div>
                </div>

                <p class="lp-hero-cta-row">
                    <a href="#formularz" class="lp-hero-cta">Umów bezpłatną diagnozę →</a>
                </p>
            </div>

            <aside class="lp-form-card" id="formularz" data-animate data-delay="1">
                <span class="lp-form-card-tag">▶ Zacznij tu</span>
                <h2>Umów bezpłatną 30-min rozmowę</h2>
                <p class="lp-form-card-sub">
                    Odpowiem w ciągu 24h. Wybierzemy termin, który Ci pasuje.
                </p>

                <?php
                if (function_exists("upsellio_render_lead_form")) {
                    echo upsellio_render_lead_form([
                        "origin" => "landing-reklama-form",
                        "submit_label" => "Umów 30-min diagnozę →",
                        "variant" => "full",
                        "heading" => "",
                        "subheading" => "",
                        "redirect_url" => $current_page_url,
                        "service_options" => $lp_service_options,
                        "message_placeholder" => "Np. Wydajemy 8 tys. zł/mies. na Google Ads, leady spadły o 40% od kwartału. Nie wiem czy to wina kampanii, strony, czy oferty.",
                        "css_class" => "lp-form",
                        "form_id" => "landing-reklama-form",
                    ]);
                } else {
                    echo '<p>Formularz: napisz na <a href="mailto:' . esc_attr($contact_email) . '">' . esc_html($contact_email) . "</a></p>";
                }
                ?>

                <div class="lp-form-card-after">
                    <strong>Co dalej po wysłaniu?</strong>
                    Do 24h dostaniesz odpowiedź ze mną z terminami. Rozmawiamy przez telefon
                    lub Google Meet — jak Ci wygodniej.
                </div>
            </aside>

        </div>
    </div>
</section>

<section class="lp-section lp-section-soft" id="dla-kogo">
    <div class="lp-wrap">
        <header class="lp-sec-head" data-animate>
            <h2>Brzmi znajomo? Któraś z tych sytuacji to Ty.</h2>
            <p>
                Nie jesteś pierwszą firmą, która tutaj trafiła. Większość mówi mi to samo na pierwszej
                rozmowie. Sprawdź czy któryś z poniższych scenariuszy jest też Twoim.
            </p>
        </header>

        <div class="lp-symptoms">
            <div class="lp-symptom" data-animate>
                <div class="lp-symptom-icon">💸</div>
                <h3>Włączyłeś kampanie i pieniądze znikają</h3>
                <p>
                    Budżet się wypala, klikają, ale formularz milczy. Patrzysz w panel
                    i nie wiesz czy to wina kampanii, strony czy oferty. Agencja mówi, że
                    „trzeba zwiększyć budżet". Czujesz, że to nie tędy droga.
                </p>
            </div>

            <div class="lp-symptom" data-animate data-delay="1">
                <div class="lp-symptom-icon">📞</div>
                <h3>Leady są, ale handlowcy narzekają</h3>
                <p>
                    Wpadają zapytania, ale 80% to przypadkowe firmy lub osoby, które
                    nie kupują. Handlowiec dzwoni i odkłada słuchawkę z dziesięciu rozmów
                    może zamknie jedną. Marketing pokazuje rosnący CTR. Sprzedaż mówi „te leady są beznadziejne".
                </p>
            </div>

            <div class="lp-symptom" data-animate data-delay="2">
                <div class="lp-symptom-icon">🤔</div>
                <h3>Nie ruszałeś jeszcze reklam, ale wiesz że czas</h3>
                <p>
                    Konkurencja jest w Google. Strona ma ruch organiczny, ale plateau.
                    Wiesz że Google Ads albo Meta Ads to logiczny krok, ale nie chcesz wpaść
                    na agencję, która spali Ci pierwsze 30 tysięcy „na testy".
                </p>
            </div>
        </div>
    </div>
</section>

<section class="lp-section" id="co-dostaniesz">
    <div class="lp-wrap" data-animate>
        <div class="lp-diagnosis">
            <div class="lp-diagnosis-side">
                <div class="lp-eyebrow lp-eyebrow-light">Co dostajesz</div>
                <h2>30 minut. Pięć konkretnych odpowiedzi.</h2>
                <p>
                    Nie sprzedaję pakietów na pierwszej rozmowie. Nie wysyłam slajdów. Nie pytam
                    „jaki ma Pan/Pani problem" przez 25 minut, żeby w ostatnich 5 wcisnąć ofertę.
                    Robię diagnozę. Tu masz dokładnie to, co wynosisz.
                </p>
            </div>

            <ol class="lp-diagnosis-list">
                <li>
                    <div class="lp-diagnosis-num">1</div>
                    <div>
                        <b>Gdzie konkretnie tracisz pieniądze</b>
                        <span>Patrzę na obecne kampanie (jeśli masz) i stronę docelową. Wskażę 3-5 punktów, w których uciekają leady — zanim w ogóle dotrą do handlowca.</span>
                    </div>
                </li>
                <li>
                    <div class="lp-diagnosis-num">2</div>
                    <div>
                        <b>Co poprawić w pierwszej kolejności</b>
                        <span>Nie wszystko naraz. Wskażę zmianę o najwyższym wpływie na koszt — żebyś zaczął zarabiać szybciej, niezależnie od tego z kim ją wdrożysz.</span>
                    </div>
                </li>
                <li>
                    <div class="lp-diagnosis-num">3</div>
                    <div>
                        <b>Czy Twoje obecne kampanie mają sens</b>
                        <span>Jeśli już prowadzisz Google Ads albo Meta Ads — ocenię strukturę kont, jakość ad-copy, dopasowanie do strony docelowej. Bez owijania.</span>
                    </div>
                </li>
                <li>
                    <div class="lp-diagnosis-num">4</div>
                    <div>
                        <b>Realistyczne oczekiwania</b>
                        <span>Jaki budżet ma sens dla Twojej branży, jaki CPL jest realny, po jakim czasie spodziewać się efektów. Bez „zwiększymy konwersję 3×" rzucanego na sucho.</span>
                    </div>
                </li>
                <li>
                    <div class="lp-diagnosis-num">5</div>
                    <div>
                        <b>Czy ja jestem dla Ciebie dobrym wyborem</b>
                        <span>Powiem wprost. Jeśli Twoja sytuacja wymaga czegoś innego niż to co robię — polecę kogoś sprawdzonego. Nie robię z każdej rozmowy klienta.</span>
                    </div>
                </li>
            </ol>
        </div>
    </div>
</section>

<section class="lp-section lp-section-soft" id="proces">
    <div class="lp-wrap">
        <header class="lp-sec-head" data-animate>
            <h2>Trzy kroki. Bez prezentacji, bez „a może jednak".</h2>
            <p>
                Wiem co Cię najbardziej powstrzymuje przed wysłaniem formularza:
                strach że odezwę się 14 razy w ciągu miesiąca. Nie odzywam się. Tu masz dokładny scenariusz.
            </p>
        </header>

        <div class="lp-process-grid">
            <div class="lp-step" data-animate>
                <h3>Wypełniasz formularz</h3>
                <p>2 minuty. Imię, e-mail, krótki opis tego co dziś nie działa. Nie potrzebujesz briefu ani gotowej strategii.</p>
            </div>
            <div class="lp-step" data-animate data-delay="1">
                <h3>Odpisuję w 24h z terminami</h3>
                <p>Dostajesz e-mail bezpośrednio ode mnie. Wybierasz dogodny termin — telefon lub Google Meet, jak wolisz.</p>
            </div>
            <div class="lp-step" data-animate data-delay="2">
                <h3>30 minut, mapa wniosków</h3>
                <p>Rozmawiamy konkretnie. Po rozmowie dostajesz pisemne podsumowanie — żebyś mógł wrócić do tego później albo dać innym osobom w firmie.</p>
            </div>
        </div>
    </div>
</section>

<section class="lp-section lp-section-dark" id="wyniki">
    <div class="lp-wrap">
        <header class="lp-sec-head" data-animate>
            <div class="lp-eyebrow lp-eyebrow-light">Realne wdrożenia</div>
            <h2>Trzy firmy, które zaczęły dokładnie tak jak Ty.</h2>
            <p>
                Każdy z tych klientów zaczynał od bezpłatnej 30-minutowej rozmowy. Nie obiecywałem
                cudów. Przedstawiłem konkretną diagnozę. Tu masz wyniki tego, co wdrożyliśmy potem.
            </p>
        </header>

        <div class="lp-proof-grid">
            <article class="lp-proof" data-animate>
                <div class="lp-proof-tag">Producent maszyn B2B</div>
                <div class="lp-proof-num">23 / mc</div>
                <div class="lp-proof-label">leadów (z 4)</div>
                <p>CPL spadł z 380 zł do 145 zł. 4 zamknięcia po 60 dniach od startu.</p>
            </article>
            <article class="lp-proof" data-animate data-delay="1">
                <div class="lp-proof-tag">SaaS dla logistyki</div>
                <div class="lp-proof-num">2,1%</div>
                <div class="lp-proof-label">konwersji (z 0,4%)</div>
                <p>80% leadów z firm 50+ pracowników. Demo robione tylko z dopasowanymi prospects.</p>
            </article>
            <article class="lp-proof" data-animate data-delay="2">
                <div class="lp-proof-tag">Consulting B2B</div>
                <div class="lp-proof-num">720k zł</div>
                <div class="lp-proof-label">rocznie (z 320 tys.)</div>
                <p>Przewidywalny pipeline: 6-8 rozmów ofertowych tygodniowo, niezależnie od sezonu.</p>
            </article>
        </div>
    </div>
</section>

<section class="lp-section" id="o-mnie">
    <div class="lp-wrap" data-animate>
        <div class="lp-author">
            <div class="lp-author-photo" aria-hidden="true">
                <?php
                if (function_exists("upsellio_render_home_media_image")) {
                    $author_img = upsellio_render_home_media_image("about_portrait", [
                        "size" => "medium",
                        "loading" => "lazy",
                    ]);
                    if ($author_img !== "") {
                        echo $author_img;
                    } else {
                        echo '<div class="lp-author-photo-placeholder">[ Sebastian Kelm ]</div>';
                    }
                } else {
                    echo '<div class="lp-author-photo-placeholder">[ Sebastian Kelm ]</div>';
                }
                ?>
            </div>
            <div>
                <span class="lp-author-tag">Z kim rozmawiasz</span>
                <h3>Sebastian Kelm</h3>
                <p>
                    10 lat sprzedaży B2B. Najpierw jako handlowiec robiący 1,5 mln zł netto/mies.,
                    potem dyrektor sprzedaży zarządzający 15-osobowym zespołem.
                    Marketing robię od strony sprzedaży — wiem dokładnie, które leady handlowiec
                    odbierze, a które zignoruje. Dlatego kampanie ustawiam pod jakość, nie pod CTR.
                </p>
                <div class="lp-author-stats">
                    <div>
                        <b>10 lat</b>
                        <span>sprzedaży B2B</span>
                    </div>
                    <div>
                        <b>1,5 mln</b>
                        <span>zł/mies. netto</span>
                    </div>
                    <div>
                        <b>5-7</b>
                        <span>klientów naraz, sam</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="lp-section lp-section-soft" id="faq">
    <div class="lp-wrap">
        <header class="lp-sec-head" data-animate>
            <h2>Najczęstsze pytania, które dostaję przed rozmową</h2>
            <p>Jeśli masz inne — napisz w formularzu, odpowiem osobiście.</p>
        </header>

        <div class="lp-faq" data-animate>
            <?php foreach ($lp_faq_items as $item) : ?>
            <details class="lp-faq-item">
                <summary>
                    <span><?php echo esc_html((string) $item["question"]); ?></span>
                    <span class="lp-faq-icon" aria-hidden="true">+</span>
                </summary>
                <p><?php echo esc_html((string) $item["answer"]); ?></p>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="lp-final" id="formularz-stopka">
    <div class="lp-wrap">
        <div class="lp-final-box">
            <h2>30 minut które nic nie kosztują. Nawet jeśli nie zostaniesz klientem.</h2>
            <p>
                Wypełnij formularz, opisz w 2 zdaniach co dziś nie działa.
                Odezwę się w 24h z terminami. Bez follow-upów, bez pitchu, bez „a może jednak".
            </p>
            <div class="lp-final-form-wrap">
                <div class="lp-form-card">
                    <span class="lp-form-card-tag">Formularz</span>
                    <h2>Umów bezpłatną 30-min rozmowę</h2>
                    <p class="lp-form-card-sub">Ten sam formularz co wyżej — zapis trafia do CRM natychmiast.</p>
                    <?php
                    if (function_exists("upsellio_render_lead_form")) {
                        echo upsellio_render_lead_form([
                            "origin" => "landing-reklama-form",
                            "submit_label" => "Umów 30-min diagnozę →",
                            "variant" => "full",
                            "heading" => "",
                            "subheading" => "",
                            "redirect_url" => $current_page_url . "#formularz-stopka",
                            "service_options" => $lp_service_options,
                            "message_placeholder" => "Np. Wydajemy 8 tys. zł/mies. na Google Ads, leady spadły o 40% od kwartału. Nie wiem czy to wina kampanii, strony, czy oferty.",
                            "css_class" => "lp-form",
                            "form_id" => "landing-reklama-form-stopka",
                        ]);
                    } else {
                        echo '<p><a href="mailto:' . esc_attr($contact_email) . '" class="lp-final-btn" style="display:inline-flex;">Napisz na ' . esc_html($contact_email) . "</a></p>";
                    }
                    ?>
                </div>
            </div>
            <p class="lp-final-note">
                Albo zadzwoń bezpośrednio: <a href="tel:<?php echo esc_attr($contact_phone_href); ?>" style="color:#5eead4;font-weight:700;text-decoration:none;"><?php echo esc_html($contact_phone); ?></a>
                · <a href="#formularz" style="color:rgba(255,255,255,.55);">Formularz na górze strony</a>
            </p>
        </div>
    </div>
</section>

</main>

<div class="lp-mobile-cta">
    <a href="#formularz-stopka">Umów bezpłatną diagnozę →</a>
</div>

<script type="application/ld+json">
<?php
echo wp_json_encode([
    "@context" => "https://schema.org",
    "@type" => "Service",
    "name" => "Bezpłatna 30-minutowa diagnoza marketingu B2B",
    "provider" => [
        "@type" => "LocalBusiness",
        "name" => "Upsellio — Marketing B2B",
        "founder" => [
            "@type" => "Person",
            "name" => "Sebastian Kelm",
        ],
        "telephone" => $contact_phone,
        "email" => $contact_email,
    ],
    "description" => "Bezpłatna 30-minutowa rozmowa diagnostyczna kampanii Google Ads i Meta Ads dla firm B2B. Konkretne wskazania co poprawić, bez prezentacji i bez zobowiązań.",
    "offers" => [
        "@type" => "Offer",
        "price" => "0",
        "priceCurrency" => "PLN",
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
</script>

<?php if (!empty($lp_faq_items)) : ?>
<script type="application/ld+json">
<?php
echo wp_json_encode([
    "@context" => "https://schema.org",
    "@type" => "FAQPage",
    "mainEntity" => array_map(static function ($item) {
        return [
            "@type" => "Question",
            "name" => (string) $item["question"],
            "acceptedAnswer" => [
                "@type" => "Answer",
                "text" => (string) $item["answer"],
            ],
        ];
    }, $lp_faq_items),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
</script>
<?php endif; ?>

<script>
(function() {
    var meta = document.createElement("meta");
    meta.name = "robots";
    meta.content = "noindex, follow";
    document.head.appendChild(meta);
})();
</script>

<script>
(function() {
    "use strict";

    if ("IntersectionObserver" in window) {
        var io = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add("is-visible");
                    io.unobserve(entry.target);
                }
            });
        }, { rootMargin: "0px 0px -8% 0px", threshold: 0.08 });

        document.querySelectorAll("[data-animate]").forEach(function(el) {
            io.observe(el);
        });
    } else {
        document.querySelectorAll("[data-animate]").forEach(function(el) {
            el.classList.add("is-visible");
        });
    }

    document.querySelectorAll('a[href^="#"]').forEach(function(link) {
        link.addEventListener("click", function(e) {
            var href = link.getAttribute("href");
            if (!href || href.length < 2) return;
            var target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: "smooth", block: "start" });
            }
        });
    });

    /* Fallback placeholder — serwer ustawia message_placeholder; to nadpisuje gdy cache/starszy HTML */
    ["landing-reklama-form", "landing-reklama-form-stopka"].forEach(function (fid) {
        var form = document.getElementById(fid);
        if (form) {
            var msg = form.querySelector('textarea[name="lead_message"]');
            if (msg) {
                msg.setAttribute("placeholder",
                    "Np. Wydajemy 8 tys. zł/mies. na Google Ads, leady spadły o 40% od kwartału. Nie wiem czy to wina kampanii, strony, czy oferty.");
            }
        }
    });

    /* Po udanym AJAX: assets/js/upsellio.js → pushLeadConversionStack → event lead_form_submit + form_type */
})();
</script>

<?php get_footer(); ?>
