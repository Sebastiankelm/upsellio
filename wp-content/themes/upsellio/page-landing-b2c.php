<?php
/*
Template Name: Upsellio - Landing Marketing B2C (Diagnoza)
Template Post Type: page
Description: Landing pod ruch z reklam — sprzedaje marketing Google/Meta dla firm B2C (lokalne usługi + e-commerce). Cel: bezpłatna 30-min rozmowa diagnostyczna. Brak B2C case studies — wiarygodność na metodologii i 10 latach sprzedaży.
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

$front_page_sections = function_exists("upsellio_get_front_page_content_config")
    ? upsellio_get_front_page_content_config()
    : [];
$contact_phone = function_exists("upsellio_get_contact_phone")
    ? upsellio_get_contact_phone()
    : trim((string) ($front_page_sections["contact_phone"] ?? "+48 575 522 595"));
$contact_phone_href = preg_replace("/\s+/", "", (string) $contact_phone);
$contact_email = trim((string) ($front_page_sections["contact_email"] ?? "kontakt@upsellio.pl"));
$current_page_url = function_exists("get_permalink") ? (string) (get_permalink() ?: home_url("/")) : home_url("/");

$lpc_service_options = [
    "Reklamy Meta (Facebook/Instagram)",
    "Reklamy Google (Search + Maps)",
    "Oba kanały (Google + Meta)",
    "Mam sklep online (e-commerce)",
    "Mam lokalną usługę (salon, gabinet, fitness)",
    "Nie wiem — chcę porozmawiać",
];

$lpc_message_placeholder =
    "Np. „Mam sklep z kosmetykami, ROAS 1.2x, koszyki porzucone 65%, nie wiem co poprawić” albo „Salon kosmetyczny, reklamy w Meta robi mi freelancer, kalendarz pełny tylko w piątki”.";

$lpc_faq_items = [
    [
        "question" => "Czy 30-minutowa rozmowa naprawdę nic nie kosztuje?",
        "answer" => "Tak. Bez zobowiązań, bez slajdów, bez przyślę ofertę po naszej rozmowie. Jeśli po rozmowie zorientujesz się że nie chcesz pracować ze mną — wychodzisz z 3-5 konkretnymi rzeczami do poprawy w marketingu, które możesz wdrożyć sam, ze swoją obecną agencją, lub z innym freelancerem. Pierwsze 30 minut to nie sprzedaż. To próbka pracy.",
    ],
    [
        "question" => "Robisz głównie marketing B2B. Dlaczego mam zaufać że ogarniesz mój salon / sklep online?",
        "answer" => "Uczciwa odpowiedź: bo marketing B2C i B2B to ten sam fundament — Quality Score w Google, struktura kampanii w Meta, koszyki porzucone, lejki sprzedażowe, retargeting. Różni się tylko język i poziom emocji. Przez 10 lat sprzedawałem B2B (1,5 mln zł netto/mc), więc rozumiem psychologię kupowania na poziomie który większość agencji B2C marketingowych pomija. Jeśli wolisz freelancera który robi tylko salony beauty — sam Ci go polecę po rozmowie. Ale jeśli zainteresuje Cię ktoś kto patrzy na kampanie z perspektywy czy to faktycznie zarabia, a nie tylko CTR rośnie — porozmawiajmy.",
    ],
    [
        "question" => "Jaki budżet reklamowy muszę mieć, żeby to miało sens?",
        "answer" => "Dla lokalnych usług (salon, gabinet, fitness): od 1 500 zł/mies. na platformę (Google lub Meta). Dla e-commerce: od 3 000-5 000 zł/mies. (zwykle Meta, bo lepiej skaluje produkty). Przy mniejszych budżetach kampanie nie zbierają wystarczająco danych żeby się optymalizować. Jeśli jesteś poniżej tego progu, na rozmowie powiem to wprost i polecę co zrobić zanim zaczniemy.",
    ],
    [
        "question" => "Po jakim czasie zobaczę pierwsze efekty?",
        "answer" => "Lokalne usługi: pierwsze rezerwacje/zapytania zwykle w 7-14 dni od startu. Stabilna jakość po 30-60 dniach. E-commerce: pierwsze sprzedaże w 14-30 dni, stabilny ROAS po 2-3 miesiącach (algorytm Meta potrzebuje danych). Działam iteracyjnie: analiza → wdrożenie → pomiar → poprawki co tydzień. Bez przyspieszania budżetem.",
    ],
    [
        "question" => "Pracujesz sam czy z zespołem?",
        "answer" => "Sam. Bez juniorów, bez rotującego account managera. Twoje kampanie prowadzę ja — Sebastian — od pierwszej rozmowy do raportu. To wada (przyjmuję 5-7 klientów jednocześnie) i zaleta (nie tłumaczysz swojego biznesu trzy razy trzem różnym osobom).",
    ],
    [
        "question" => "Czy zaglądasz tylko w reklamy, czy patrzysz też na sklep / stronę?",
        "answer" => "Patrzę na cały lejek. Reklamy bez konwertującej strony to wyrzucone pieniądze. W ramach pierwszej rozmowy diagnostycznej oceniam 3 miejsca: kampanie (jeśli masz), stronę docelową/sklep, oraz proces po zapytaniu (telefon, mail, wiadomości na Instagramie). Nie da się sensownie poprawić jednego elementu bez patrzenia na całość.",
    ],
];

get_header();
?>

<style>
body.page-template-page-landing-b2c-php .nav-links,
body.page-template-page-landing-b2c-php .nav-dropdown,
body.page-template-page-landing-b2c-php .mobile-menu,
body.page-template-page-landing-b2c-php .hamburger,
body.page-template-page-landing-b2c-php .nav-cta,
body.page-template-page-landing-b2c-php .mobile-sticky-cta,
body.page-template-page-landing-b2c-php .ups-breadcrumbs,
body[class*="page-landing-b2c"] .nav-links,
body[class*="page-landing-b2c"] .nav-dropdown,
body[class*="page-landing-b2c"] .mobile-menu,
body[class*="page-landing-b2c"] .hamburger,
body[class*="page-landing-b2c"] .nav-cta,
body[class*="page-landing-b2c"] .mobile-sticky-cta,
body[class*="page-landing-b2c"] .ups-breadcrumbs {
    display: none !important;
}
.lpc { font-family: "DM Sans", system-ui, sans-serif; color: #0d0d0b; background: #fafaf6; line-height: 1.65; }
.lpc *, .lpc *::before, .lpc *::after { box-sizing: border-box; }
.lpc-wrap { max-width: 1180px; margin-inline: auto; padding: 0 24px; }
.lpc-hero {
    padding: 60px 0 88px;
    background:
        radial-gradient(ellipse at 88% 8%, rgba(13,148,136,.16), transparent 36%),
        radial-gradient(ellipse at 8% 92%, rgba(244,114,182,.06), transparent 32%),
        linear-gradient(180deg, #fafaf6 0%, #f1f1ec 100%);
}
.lpc-hero-grid { display: grid; grid-template-columns: 1.1fr 1fr; gap: 56px; align-items: start; }
.lpc-eyebrow {
    display: inline-flex; align-items: center; gap: 10px; margin-bottom: 18px;
    color: #db2777; font-size: 12px; font-weight: 900; letter-spacing: 1.4px; text-transform: uppercase;
}
.lpc-eyebrow::before { content: ""; width: 24px; height: 2px; background: #db2777; border-radius: 2px; }
.lpc-eyebrow-light { color: #5eead4; }
.lpc-eyebrow-light::before { background: #5eead4; }
.lpc-eyebrow-teal { color: #0f766e; }
.lpc-eyebrow-teal::before { background: #0d9488; }
.lpc-h1 {
    font-family: "Syne", "Bricolage Grotesque", sans-serif; font-weight: 800;
    font-size: clamp(40px, 5.4vw, 64px); line-height: .98; letter-spacing: -2px; margin: 0 0 22px; color: #0a1410;
}
.lpc-h1 em { font-style: normal; color: #0d9488; }
.lpc-lead { font-size: 18px; line-height: 1.65; color: #3d3d38; max-width: 56ch; margin: 0 0 28px; }
.lpc-transparency {
    margin: 0 0 32px;
    padding: 22px 26px;
    background: #fff;
    border: 1px solid #e7e7e1;
    border-left: 4px solid #db2777;
    border-radius: 14px;
    box-shadow: 0 6px 18px rgba(15,23,42,.04);
    max-width: 600px;
}
.lpc-transparency b {
    display: block; margin-bottom: 6px;
    font-family: "Syne", "Bricolage Grotesque", sans-serif;
    font-weight: 800; font-size: 15px; color: #0a1410;
}
.lpc-transparency p {
    margin: 0; color: #475569;
    font-size: 14px; line-height: 1.6;
}
.lpc-hero-bullets { list-style: none; padding: 0; margin: 0 0 32px; display: grid; gap: 12px; }
.lpc-hero-bullets li { display: flex; align-items: flex-start; gap: 12px; font-size: 15px; color: #1a1a17; line-height: 1.55; }
.lpc-bullet-icon {
    flex: 0 0 24px; width: 24px; height: 24px; border-radius: 50%; background: #ccfbf1; color: #0f766e;
    display: grid; place-items: center; font-weight: 900; font-size: 13px; margin-top: 1px;
}
.lpc-hero-trust {
    display: flex; flex-wrap: wrap; gap: 20px; padding: 18px 22px; background: rgba(255,255,255,.7);
    border: 1px solid #e7e7e1; border-radius: 16px; font-size: 13px; color: #3d3d38;
}
.lpc-hero-trust > div { display: flex; align-items: center; gap: 8px; font-weight: 600; }
.lpc-hero-trust strong { color: #0d9488; font-size: 15px; }
.lpc-form-card {
    background: #ffffff; border: 1px solid #dbe7ea; border-radius: 24px; padding: 32px;
    box-shadow: 0 24px 60px rgba(15,23,42,.1), 0 4px 8px rgba(15,23,42,.04);
    position: sticky; top: 24px;
}
.lpc-form-card-tag {
    display: inline-block; padding: 5px 12px; background: #f0fdf4; border-radius: 99px;
    font-size: 11px; font-weight: 900; letter-spacing: 1.2px; text-transform: uppercase; color: #15803d; margin-bottom: 12px;
}
.lpc-form-card h2 {
    margin: 0 0 8px; font-family: "Syne", "Bricolage Grotesque", sans-serif;
    font-size: 26px; line-height: 1.08; letter-spacing: -.8px; color: #0a1410;
}
.lpc-form-card-sub { margin: 0 0 22px; color: #64748b; font-size: 14px; line-height: 1.55; }
.lpc-form-card-after {
    margin-top: 18px; padding: 14px 16px; background: #f8fafc; border: 1px solid #e2e8f0;
    border-radius: 12px; font-size: 12.5px; color: #475569; line-height: 1.55;
}
.lpc-form-card-after strong { color: #0a1410; display: block; margin-bottom: 4px; }
.lpc-form-card .ups-form input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),
.lpc-form-card .ups-form select,
.lpc-form-card .ups-form textarea {
    width: 100%; min-height: 46px; background: #ffffff; border: 1.5px solid #e7e7e1; border-radius: 12px;
    padding: 11px 14px; color: #0a1410; font-size: 14.5px; font-family: inherit; transition: border-color .15s, box-shadow .15s;
}
.lpc-form-card .ups-form textarea { min-height: 100px; resize: vertical; line-height: 1.55; }
.lpc-form-card .ups-form input:focus,
.lpc-form-card .ups-form select:focus,
.lpc-form-card .ups-form textarea:focus {
    border-color: #0d9488; box-shadow: 0 0 0 4px rgba(13,148,136,.13); outline: none;
}
.lpc-form-card .ups-form__consent { display: flex; align-items: flex-start; gap: 10px; font-size: 12.5px; color: #475569; line-height: 1.5; }
.lpc-form-card .ups-form__consent input[type="checkbox"] { width: 18px; height: 18px; min-height: 18px; margin: 2px 0 0; flex-shrink: 0; accent-color: #0d9488; }
.lpc-form-card .ups-form__submit,
.lpc-form-card button[type="submit"] {
    width: 100%; min-height: 56px; border: 0; border-radius: 999px; background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
    color: #ffffff; font-family: inherit; font-size: 15px; font-weight: 900; cursor: pointer; margin-top: 6px;
    box-shadow: 0 18px 40px rgba(13,148,136,.28); transition: transform .18s, box-shadow .18s;
}
.lpc-form-card .ups-form__submit:hover,
.lpc-form-card button[type="submit"]:hover { transform: translateY(-1px); box-shadow: 0 22px 44px rgba(13,148,136,.34); }
.lpc-section { padding: 88px 0; }
.lpc-section-soft { background: #f1f1ec; }
.lpc-section-dark { background: #0a1410; color: #fff; position: relative; overflow: hidden; }
.lpc-section-dark::before {
    content: ""; position: absolute; width: 600px; height: 600px; border-radius: 50%;
    background: radial-gradient(circle, rgba(20,184,166,.18), transparent 65%); right: -240px; top: -240px; pointer-events: none;
}
.lpc-section-dark .lpc-wrap { position: relative; z-index: 2; }
.lpc-sec-head { max-width: 760px; margin-bottom: 36px; }
.lpc-sec-head h2 {
    margin: 0; font-family: "Syne", "Bricolage Grotesque", sans-serif; font-weight: 800; font-size: clamp(30px, 3.6vw, 46px);
    line-height: 1.04; letter-spacing: -1.4px; color: #0a1410;
}
.lpc-section-dark .lpc-sec-head h2 { color: #fff; }
.lpc-sec-head p { margin: 14px 0 0; max-width: 64ch; color: #475569; font-size: 17px; line-height: 1.65; }
.lpc-section-dark .lpc-sec-head p { color: rgba(255,255,255,.7); }
.lpc-groups { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
.lpc-group {
    background: #fff; border: 1px solid #dbe7ea; border-radius: 22px; padding: 32px;
    transition: border-color .2s, transform .2s, box-shadow .2s; position: relative;
}
.lpc-group:hover { border-color: #0d9488; transform: translateY(-3px); box-shadow: 0 18px 36px rgba(13,148,136,.1); }
.lpc-group-tag {
    display: inline-block; padding: 5px 12px; background: #ccfbf1; color: #0f766e; border-radius: 99px;
    font-size: 11px; font-weight: 900; letter-spacing: 1.2px; text-transform: uppercase; margin-bottom: 16px;
}
.lpc-group.is-second .lpc-group-tag { background: #fce7f3; color: #be185d; }
.lpc-group h3 {
    margin: 0 0 14px; font-family: "Syne", "Bricolage Grotesque", sans-serif; font-size: 22px; line-height: 1.18; letter-spacing: -.6px; color: #0a1410;
}
.lpc-group-list { list-style: none; padding: 0; margin: 0 0 18px; display: grid; gap: 10px; }
.lpc-group-list li { display: flex; gap: 10px; color: #475569; font-size: 14.5px; line-height: 1.55; }
.lpc-group-list li::before { content: "→"; color: #0d9488; font-weight: 900; flex-shrink: 0; }
.lpc-group.is-second .lpc-group-list li::before { color: #db2777; }
.lpc-group-meta {
    padding-top: 16px; border-top: 1px solid #f1f1ec;
    font-size: 13px; color: #64748b;
}
.lpc-group-meta b { color: #0a1410; font-weight: 700; }
.lpc-mech-grid { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 16px; }
.lpc-mech {
    background: #fff; border: 1px solid #e7e7e1; border-radius: 20px; padding: 28px; transition: border-color .2s, transform .2s;
}
.lpc-mech:hover { border-color: #0d9488; transform: translateY(-2px); }
.lpc-mech-num {
    display: inline-block; font-family: "Syne", sans-serif; font-size: 14px; font-weight: 800; color: #0d9488; letter-spacing: 1.5px; margin-bottom: 12px;
}
.lpc-mech h3 {
    margin: 0 0 10px; font-family: "Syne", "Bricolage Grotesque", sans-serif; font-size: 18px; line-height: 1.22; letter-spacing: -.4px; color: #0a1410;
}
.lpc-mech p { margin: 0; color: #475569; font-size: 14px; line-height: 1.6; }
.lpc-mech-stat {
    display: inline-block; margin-top: 12px;
    padding: 4px 12px; background: #fef3c7; color: #92400e; border-radius: 99px;
    font-size: 12px; font-weight: 800; letter-spacing: .3px;
}
.lpc-diagnosis {
    background: #fff; border: 1px solid #e7e7e1; border-radius: 28px; overflow: hidden; box-shadow: 0 18px 44px rgba(15,23,42,.06);
    display: grid; grid-template-columns: 1fr 1fr;
}
.lpc-diagnosis-side {
    background: linear-gradient(165deg, #0d9488 0%, #115e59 100%); color: #fff; padding: 48px 40px; position: relative; overflow: hidden;
}
.lpc-diagnosis-side::before {
    content: ""; position: absolute; top: -100px; right: -100px; width: 300px; height: 300px; border-radius: 50%; background: rgba(255,255,255,.06);
}
.lpc-diagnosis-side .lpc-eyebrow { color: #5eead4; }
.lpc-diagnosis-side .lpc-eyebrow::before { background: #5eead4; }
.lpc-diagnosis-side h2 {
    font-family: "Syne", "Bricolage Grotesque", sans-serif; font-weight: 800; font-size: 32px; line-height: 1.06; letter-spacing: -1.2px;
    margin: 0 0 18px; color: #fff; max-width: 14ch; position: relative;
}
.lpc-diagnosis-side p { margin: 0; color: rgba(255,255,255,.85); font-size: 15px; line-height: 1.65; position: relative; }
.lpc-diagnosis-list { padding: 48px 40px; list-style: none; margin: 0; display: grid; gap: 18px; }
.lpc-diagnosis-list li { display: grid; grid-template-columns: 32px 1fr; gap: 14px; align-items: flex-start; }
.lpc-diagnosis-num {
    width: 32px; height: 32px; border-radius: 50%; background: #ccfbf1; color: #0f766e; font-weight: 900; font-family: "Syne", sans-serif;
    font-size: 14px; display: grid; place-items: center;
}
.lpc-diagnosis-list li b {
    display: block; font-family: "Syne", "Bricolage Grotesque", sans-serif; font-weight: 800; font-size: 16px; color: #0a1410; margin-bottom: 4px;
}
.lpc-diagnosis-list li span { font-size: 14px; color: #475569; line-height: 1.55; }
.lpc-stats-grid { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 18px; }
.lpc-stat {
    background: rgba(255,255,255,.045); border: 1px solid rgba(255,255,255,.12); border-radius: 20px; padding: 32px;
}
.lpc-stat-tag {
    font-size: 11px; letter-spacing: 1.4px; text-transform: uppercase; color: rgba(255,255,255,.5); margin-bottom: 18px;
}
.lpc-stat-num {
    font-family: "Syne", "Bricolage Grotesque", sans-serif; font-weight: 800; font-size: 44px;
    color: #5eead4; letter-spacing: -1.4px; line-height: 1;
}
.lpc-stat-label { color: #fff; margin: 12px 0 14px; font-size: 14px; font-weight: 700; }
.lpc-stat p { margin: 0; color: rgba(255,255,255,.65); font-size: 13.5px; line-height: 1.6; }
.lpc-stats-source {
    margin-top: 24px; text-align: center;
    color: rgba(255,255,255,.4); font-size: 11px; letter-spacing: .4px;
}
.lpc-process-grid { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 14px; counter-reset: lpcstep; }
.lpc-step { background: #fff; border: 1px solid #e7e7e1; border-radius: 20px; padding: 28px; position: relative; }
.lpc-step::before {
    counter-increment: lpcstep; content: counter(lpcstep, decimal-leading-zero); position: absolute; top: 22px; right: 22px;
    font-family: "Syne", sans-serif; font-size: 56px; font-weight: 800; color: #ccfbf1; line-height: 1; letter-spacing: -2px;
}
.lpc-step h3 {
    margin: 0 0 8px; font-family: "Syne", "Bricolage Grotesque", sans-serif; font-size: 19px; line-height: 1.15; letter-spacing: -.4px; color: #0a1410;
    max-width: 14ch; position: relative;
}
.lpc-step p { margin: 0; color: #475569; font-size: 14.5px; line-height: 1.6; position: relative; }
.lpc-author {
    background: #fff; border: 1px solid #e7e7e1; border-radius: 28px; padding: 40px; display: grid; grid-template-columns: 200px 1fr;
    gap: 36px; align-items: center; box-shadow: 0 18px 44px rgba(15,23,42,.06);
}
.lpc-author-photo {
    aspect-ratio: 1; border-radius: 20px; background: linear-gradient(165deg, #ccfbf1, #dff8f4);
    border: 1px solid #99f6e4; overflow: hidden; position: relative;
}
.lpc-author-photo img { width: 100%; height: 100%; object-fit: cover; display: block; }
.lpc-author-photo-placeholder {
    position: absolute; inset: 0; background-image: repeating-linear-gradient(135deg, rgba(13,148,136,.12) 0 12px, transparent 12px 24px);
    display: grid; place-items: center; color: #0f766e; font-family: ui-monospace, monospace; font-size: 12px;
}
.lpc-author-tag {
    display: inline-block; padding: 4px 12px; background: #f0fdfa; color: #0f766e; border-radius: 99px;
    font-size: 11px; font-weight: 900; letter-spacing: 1.2px; text-transform: uppercase; margin-bottom: 12px;
}
.lpc-author h3 { margin: 0 0 8px; font-family: "Syne", "Bricolage Grotesque", sans-serif; font-size: 26px; line-height: 1.1; letter-spacing: -.6px; }
.lpc-author > div p { margin: 0 0 18px; color: #475569; font-size: 15px; line-height: 1.65; }
.lpc-author-stats { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 12px; }
.lpc-author-stats > div { padding: 14px 16px; background: #fafaf6; border: 1px solid #e7e7e1; border-radius: 12px; }
.lpc-author-stats b { display: block; font-family: "Syne", sans-serif; font-weight: 800; font-size: 22px; color: #0d9488; line-height: 1; }
.lpc-author-stats span { display: block; font-size: 12px; color: #64748b; margin-top: 5px; line-height: 1.4; }
.lpc-faq { display: grid; gap: 10px; max-width: 880px; }
.lpc-faq-item { background: #fff; border: 1px solid #e7e7e1; border-radius: 16px; transition: border-color .2s; }
.lpc-faq-item[open] { border-color: #0d9488; }
.lpc-faq-item summary {
    list-style: none; cursor: pointer; display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 22px 26px;
    font-family: "Syne", "Bricolage Grotesque", sans-serif; font-size: 17px; font-weight: 700; color: #0a1410;
}
.lpc-faq-item summary::-webkit-details-marker { display: none; }
.lpc-faq-icon {
    width: 28px; height: 28px; border-radius: 50%; background: #f1f5f9; display: grid; place-items: center; font-size: 18px; color: #475569;
    flex: 0 0 28px; transition: transform .2s, background .2s, color .2s;
}
.lpc-faq-item[open] .lpc-faq-icon { transform: rotate(45deg); background: #ccfbf1; color: #0f766e; }
.lpc-faq-item p { margin: 0; padding: 0 26px 22px; color: #475569; font-size: 15px; line-height: 1.65; }
.lpc-final { padding: 0 0 110px; }
.lpc-final-box {
    background: radial-gradient(circle at 88% 20%, rgba(13,148,136,.4), transparent 38%), #0a1410;
    color: #fff; border-radius: 32px; padding: 56px; text-align: center;
}
.lpc-final-box h2 {
    font-family: "Syne", "Bricolage Grotesque", sans-serif; font-weight: 800; font-size: clamp(30px, 4vw, 50px);
    line-height: 1.02; letter-spacing: -1.6px; margin: 0 auto 16px; color: #fff; max-width: 22ch;
}
.lpc-final-box p { max-width: 56ch; margin: 0 auto 28px; color: rgba(255,255,255,.7); font-size: 17px; line-height: 1.65; }
.lpc-final-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 10px; min-height: 60px; padding: 0 36px; border-radius: 999px;
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); color: #fff; font-weight: 900; font-size: 16px; text-decoration: none;
    box-shadow: 0 20px 44px rgba(13,148,136,.42); transition: transform .2s, box-shadow .2s;
}
.lpc-final-btn:hover { transform: translateY(-2px); box-shadow: 0 26px 52px rgba(13,148,136,.5); }
.lpc-final-note { margin: 22px 0 0; font-size: 13px; color: rgba(255,255,255,.5); }
.lpc-final-note a { color: #5eead4; font-weight: 700; text-decoration: none; }
.lpc-final-form-wrap { max-width: 560px; margin: 28px auto 0; }
.lpc-final-form-wrap .lpc-form-card { position: static; text-align: left; margin: 0 auto; }
.lpc-mobile-cta {
    display: none; position: fixed; bottom: 0; left: 0; right: 0; background: #fff; border-top: 1px solid #e7e7e1;
    padding: 12px 16px; z-index: 100; box-shadow: 0 -6px 24px rgba(15,23,42,.08);
}
.lpc-mobile-cta a {
    display: flex; align-items: center; justify-content: center; width: 100%; min-height: 50px;
    background: linear-gradient(135deg, #0d9488, #0f766e); color: #fff; font-weight: 800; font-size: 15px;
    text-decoration: none; border-radius: 999px; box-shadow: 0 12px 24px rgba(13,148,136,.3);
}
[data-animate] { opacity: 0; transform: translateY(18px); transition: opacity .8s ease, transform .8s ease; }
[data-animate].is-visible { opacity: 1; transform: translateY(0); }
[data-delay="1"] { transition-delay: .1s; }
[data-delay="2"] { transition-delay: .2s; }
[data-delay="3"] { transition-delay: .3s; }
@media (prefers-reduced-motion: reduce) {
    [data-animate] { opacity: 1; transform: none; transition: none; }
}
@media (max-width: 1024px) {
    .lpc-hero-grid { grid-template-columns: 1fr; gap: 40px; }
    .lpc-form-card { position: static; }
    .lpc-groups { grid-template-columns: 1fr; }
    .lpc-mech-grid, .lpc-stats-grid, .lpc-process-grid { grid-template-columns: 1fr 1fr; }
    .lpc-diagnosis { grid-template-columns: 1fr; }
    .lpc-diagnosis-side { padding: 36px 32px; }
    .lpc-diagnosis-list { padding: 36px 32px; }
    .lpc-author { grid-template-columns: 140px 1fr; gap: 24px; padding: 32px; }
}
@media (max-width: 720px) {
    .lpc-hero { padding: 36px 0 56px; }
    .lpc-section { padding: 64px 0; }
    .lpc-mech-grid, .lpc-stats-grid, .lpc-process-grid { grid-template-columns: 1fr; }
    .lpc-form-card { padding: 24px; border-radius: 20px; }
    .lpc-final { padding-bottom: 100px; }
    .lpc-final-box { padding: 32px 22px; border-radius: 24px; }
    .lpc-author { grid-template-columns: 1fr; padding: 24px; text-align: left; }
    .lpc-author-photo { max-width: 200px; margin: 0 auto; }
    .lpc-author-stats { grid-template-columns: 1fr 1fr; }
    .lpc-mobile-cta { display: block; }
    .lpc { padding-bottom: 80px; }
    .lpc-step::before { font-size: 44px; top: 18px; right: 18px; }
}
</style>

<main class="lpc" id="diagnoza-b2c">
<section class="lpc-hero">
    <div class="lpc-wrap">
        <div class="lpc-hero-grid">
            <div class="lpc-hero-copy" data-animate>
                <div class="lpc-eyebrow">⚡ Bezpłatna 30-min diagnoza</div>
                <h1 class="lpc-h1">
                    Reklamy się kręcą,<br>
                    a sprzedaż <em>stoi w miejscu?</em>
                </h1>
                <p class="lpc-lead">
                    Twój sklep online albo lokalna usługa. Wydajesz na Meta i Google,
                    klientów więcej, ale przychód nie nadąża. W 30 minut sprawdzę dokładnie
                    gdzie wyciekają pieniądze — w samych reklamach, na stronie, w koszyku,
                    czy w obsłudze po zapytaniu.
                </p>
                <div class="lpc-transparency">
                    <b>Transparentnie — zanim wypełnisz formularz</b>
                    <p>
                        Jestem specjalistą sprzedaży i marketingu B2B z 10-letnim doświadczeniem.
                        W B2C wnoszę dokładnie tę perspektywę: nie patrzę tylko na CPC i CTR,
                        patrzę na to czy Twój biznes faktycznie zarabia. Jeśli szukasz freelancera
                        który robi tylko salony beauty albo tylko Shopify — szczerze polecę Ci kogoś
                        sprawdzonego po naszej rozmowie.
                    </p>
                </div>
                <ul class="lpc-hero-bullets">
                    <li><span class="lpc-bullet-icon">✓</span><span>Sklepy online (Shopify, WooCommerce) i lokalne usługi (salony, gabinety, fitness)</span></li>
                    <li><span class="lpc-bullet-icon">✓</span><span>Reklamy Meta (Facebook, Instagram) i Google (Search, Maps)</span></li>
                    <li><span class="lpc-bullet-icon">✓</span><span>Patrzę na cały lejek — kampania, sklep, koszyk, obsługa po zapytaniu</span></li>
                    <li><span class="lpc-bullet-icon">✓</span><span>Konkretna mapa działania nawet jeśli nie zostaniesz klientem</span></li>
                </ul>
                <div class="lpc-hero-trust">
                    <div><strong>10 lat</strong> sprzedaży</div>
                    <div><strong>0 zł</strong> pierwsza rozmowa</div>
                    <div><strong>1:1</strong> rozmawiasz ze mną</div>
                </div>
            </div>
            <aside class="lpc-form-card" id="formularz" data-animate data-delay="1">
                <span class="lpc-form-card-tag">▶ Zacznij tu</span>
                <h2>Umów bezpłatną 30-min rozmowę</h2>
                <p class="lpc-form-card-sub">
                    Pokaż link do sklepu / strony albo opisz w 2 zdaniach co chcesz poprawić.
                    Odpowiem w 24h.
                </p>
                <?php
                if (function_exists("upsellio_render_lead_form")) {
                    echo upsellio_render_lead_form([
                        "origin" => "landing-b2c-form",
                        "submit_label" => "Umów bezpłatną diagnozę →",
                        "variant" => "full",
                        "heading" => "",
                        "subheading" => "",
                        "redirect_url" => $current_page_url,
                        "service_options" => $lpc_service_options,
                        "message_placeholder" => $lpc_message_placeholder,
                        "css_class" => "lpc-form",
                        "form_id" => "landing-b2c-form",
                    ]);
                } else {
                    echo '<p>Formularz: napisz na <a href="mailto:' . esc_attr($contact_email) . '">' . esc_html($contact_email) . "</a></p>";
                }
                ?>
                <div class="lpc-form-card-after">
                    <strong>Co dalej po wysłaniu?</strong>
                    Do 24h dostaniesz odpowiedź ze mną z terminami. Rozmawiamy przez telefon
                    lub Google Meet — jak Ci wygodniej.
                </div>
            </aside>
        </div>
    </div>
</section>

<section class="lpc-section lpc-section-soft" id="dla-kogo">
    <div class="lpc-wrap">
        <header class="lpc-sec-head" data-animate>
            <h2>Dwa różne biznesy. Te same problemy z reklamami.</h2>
            <p>
                E-commerce mierzy ROAS, lokalna usługa CPL z rezerwacji.
                Liczby inne, ale fundament ten sam — kampanie nie zarabiają tyle, ile powinny.
                Sprawdź czy któraś sytuacja brzmi znajomo.
            </p>
        </header>
        <div class="lpc-groups">
            <div class="lpc-group" data-animate>
                <span class="lpc-group-tag">Sklep online B2C</span>
                <h3>Masz e-commerce. ROAS spada, koszyki porzucane, sprzedaż nie skaluje się z budżetem.</h3>
                <ul class="lpc-group-list">
                    <li>ROAS w okolicach 1-1,5 (a powinien być 3-5×)</li>
                    <li>Wysoki ruch z reklam, niska konwersja koszyka</li>
                    <li>Płacisz za kliknięcia, a algorytm Meta nie znajduje kupujących</li>
                    <li>Każdy ruch ze zwiększonym budżetem psuje ROAS</li>
                    <li>Masz dane o porzuconych koszykach, ale nie wiesz jak je odzyskać</li>
                </ul>
                <div class="lpc-group-meta">
                    Zwykle: <b>Shopify, WooCommerce, PrestaShop</b> · Meta + Google Shopping<br>
                    Budżet od 3 000 zł/mies. na platformę
                </div>
            </div>
            <div class="lpc-group is-second" data-animate data-delay="1">
                <span class="lpc-group-tag">Lokalna usługa</span>
                <h3>Salon, gabinet, fitness. Reklamy generują kliknięcia, ale telefon dzwoni rzadko.</h3>
                <ul class="lpc-group-list">
                    <li>Płacisz za reklamy w Meta, ale pełny grafik to dalej rzadkość</li>
                    <li>Google Maps pokazuje konkurencję, Twoja firma niżej</li>
                    <li>Rezerwacje przychodzą, ale klient nie wraca</li>
                    <li>Sezon dobry idzie z budżetem reklamowym, ale poza sezonem cisza</li>
                    <li>Płacisz freelancerowi za reklamy, nie wiesz czy się opłaca</li>
                </ul>
                <div class="lpc-group-meta">
                    Zwykle: <b>salony beauty, gabinety, fitness, dentyści, weterynarze</b> · Meta + Google Search/Maps<br>
                    Budżet od 1 500 zł/mies. na platformę
                </div>
            </div>
        </div>
    </div>
</section>

<section class="lpc-section">
    <div class="lpc-wrap">
        <header class="lpc-sec-head" data-animate>
            <h2>Sześć powodów dlaczego reklamy nie przekładają się na sprzedaż</h2>
            <p>
                W B2B i B2C te same fundamenty decydują o tym czy kampania zarabia czy się pali.
                Każdy z tych powodów kosztuje Cię konkretne pieniądze co miesiąc.
                Zobacz ile.
            </p>
        </header>
        <div class="lpc-mech-grid">
            <article class="lpc-mech" data-animate>
                <div class="lpc-mech-num">01</div>
                <h3>Niski Quality Score w Google Ads</h3>
                <p>Reklamy dopasowane słabo do landing page. Google karze niższym pozycjami przy wyższym koszcie. Słaby QS może zdublować Ci CPC.</p>
                <span class="lpc-mech-stat">QS 4 vs 8 = +60% kosztów</span>
            </article>
            <article class="lpc-mech" data-animate>
                <div class="lpc-mech-num">02</div>
                <h3>Koszyki porzucane bez follow-upu</h3>
                <p>69% koszyków online jest porzucana. Bez sekwencji email/SMS/Meta retargeting tracisz większość ruchu który już zapłaciłeś za pozyskanie.</p>
                <span class="lpc-mech-stat">~69% koszyków porzucanych</span>
            </article>
            <article class="lpc-mech" data-animate data-delay="1">
                <div class="lpc-mech-num">03</div>
                <h3>Targetowanie zbyt szerokie w Meta</h3>
                <p>Algorytm Meta optymalizuje pod „zaangażowanie”, a nie pod kupującego. Bez Conversions API i dobrych eventów kampanie wykarmiają się złymi danymi.</p>
                <span class="lpc-mech-stat">~40% niższy ROAS</span>
            </article>
            <article class="lpc-mech" data-animate data-delay="1">
                <div class="lpc-mech-num">04</div>
                <h3>Wolna strona / sklep na mobile</h3>
                <p>53% użytkowników opuszcza stronę ładującą się dłużej niż 3s. Każda dodatkowa sekunda obniża konwersję o 7-12%. To prosta strata pieniędzy z reklam.</p>
                <span class="lpc-mech-stat">+1s = -12% konwersji</span>
            </article>
            <article class="lpc-mech" data-animate data-delay="2">
                <div class="lpc-mech-num">05</div>
                <h3>Brak retargetingu i lookalike</h3>
                <p>Bez warstw remarketingowych płacisz pełną stawkę za każdego użytkownika. Dobrze ustawiony retargeting kosztuje 2-4× mniej i konwertuje 3-5× lepiej.</p>
                <span class="lpc-mech-stat">3-5× wyższa konwersja</span>
            </article>
            <article class="lpc-mech" data-animate data-delay="2">
                <div class="lpc-mech-num">06</div>
                <h3>Obsługa po zapytaniu zabija sprzedaż</h3>
                <p>Lokalna usługa: rezerwacja przyszła, oddzwoniliście za 6h. Klient już rezerwuje gdzie indziej. E-commerce: koszyk + brak chatu = stracony klient. Reklama wydała kasę za nic.</p>
                <span class="lpc-mech-stat">5 min vs 1h = 21× szansa</span>
            </article>
        </div>
    </div>
</section>

<section class="lpc-section lpc-section-soft" id="co-dostaniesz">
    <div class="lpc-wrap" data-animate>
        <div class="lpc-diagnosis">
            <div class="lpc-diagnosis-side">
                <div class="lpc-eyebrow lpc-eyebrow-light">Co dostajesz</div>
                <h2>30 minut. Pięć konkretnych odpowiedzi.</h2>
                <p>
                    Niezależnie od tego czy masz sklep online czy lokalną usługę —
                    wychodzisz z rozmowy z konkretną mapą działania. Bez prezentacji,
                    bez slajdów, bez „jaki ma Pan/Pani problem" przez 25 minut.
                </p>
            </div>
            <ol class="lpc-diagnosis-list">
                <li>
                    <div class="lpc-diagnosis-num">1</div>
                    <div>
                        <b>Gdzie konkretnie wyciekają pieniądze</b>
                        <span>Patrzę na obecne kampanie, sklep/landing i obsługę po zapytaniu. Wskażę 3-5 punktów, w których tracisz sprzedaż mimo tego że ruch jest.</span>
                    </div>
                </li>
                <li>
                    <div class="lpc-diagnosis-num">2</div>
                    <div>
                        <b>Co poprawić w pierwszej kolejności</b>
                        <span>Nie wszystko naraz. Wskażę zmianę o najwyższym wpływie na koszt — żebyś zaczął zarabiać szybciej, niezależnie od tego z kim ją wdrożysz.</span>
                    </div>
                </li>
                <li>
                    <div class="lpc-diagnosis-num">3</div>
                    <div>
                        <b>Realny ROAS / CPL dla Twojej branży</b>
                        <span>Powiem co jest realne dla Twojego rodzaju biznesu, jakie liczby powinieneś mierzyć i kiedy zacząć panikować. Bez „zwiększymy konwersję 3×" rzucanego na sucho.</span>
                    </div>
                </li>
                <li>
                    <div class="lpc-diagnosis-num">4</div>
                    <div>
                        <b>Czy w ogóle warto skalować budżet</b>
                        <span>Zwiększanie budżetu to czasem najgorsze co możesz zrobić. Pokażę Ci czy najpierw nie poprawić struktury kampanii, lejka i obsługi — zanim wleje więcej pieniędzy.</span>
                    </div>
                </li>
                <li>
                    <div class="lpc-diagnosis-num">5</div>
                    <div>
                        <b>Czy ja jestem dla Ciebie dobrym wyborem</b>
                        <span>Powiem wprost. Jeśli Twoja sytuacja wymaga specjalisty od konkretnej platformy (np. tylko TikTok Ads dla zoomerów) — polecę kogoś sprawdzonego.</span>
                    </div>
                </li>
            </ol>
        </div>
    </div>
</section>

<section class="lpc-section lpc-section-dark" id="dane">
    <div class="lpc-wrap">
        <header class="lpc-sec-head" data-animate>
            <div class="lpc-eyebrow lpc-eyebrow-light">Liczby z rynku</div>
            <h2>Co dane mówią o przeciętnym B2C w Polsce</h2>
            <p>
                Te liczby pomogą Ci ocenić czy Twoje wyniki są w normie, czy ktoś marnuje
                Twoje pieniądze. Każda z nich pochodzi z publicznych raportów branżowych.
            </p>
        </header>
        <div class="lpc-stats-grid">
            <article class="lpc-stat" data-animate>
                <div class="lpc-stat-tag">E-commerce — koszyki</div>
                <div class="lpc-stat-num">~69%</div>
                <div class="lpc-stat-label">koszyków porzucanych</div>
                <p>To dane Baymard Institute z analizy kilkudziesięciu badań. W praktyce każda firma która nie ma sekwencji odzyskiwania koszyków traci 2 z 3 potencjalnych zamówień.</p>
            </article>
            <article class="lpc-stat" data-animate data-delay="1">
                <div class="lpc-stat-tag">Lokalna usługa — czas reakcji</div>
                <div class="lpc-stat-num">21×</div>
                <div class="lpc-stat-label">wyższa szansa na klienta</div>
                <p>Badanie Harvard Business Review: oddzwonienie do 5 minut daje 21× wyższą konwersję niż po godzinie. Większość lokalnych usług oddzwania po 4-24 godzinach.</p>
            </article>
            <article class="lpc-stat" data-animate data-delay="2">
                <div class="lpc-stat-tag">Mobile — czas ładowania</div>
                <div class="lpc-stat-num">53%</div>
                <div class="lpc-stat-label">użytkowników odchodzi</div>
                <p>Google: 53% mobilnych użytkowników opuszcza stronę ładującą się dłużej niż 3 sekundy. Każda dodatkowa sekunda obniża konwersję o 7-12%.</p>
            </article>
        </div>
        <p class="lpc-stats-source">
            Źródła: Baymard Institute (2024), Harvard Business Review (Lead Response Management Study), Google/SOASTA (2017)
        </p>
    </div>
</section>

<section class="lpc-section">
    <div class="lpc-wrap">
        <header class="lpc-sec-head" data-animate>
            <h2>Trzy kroki. Bez prezentacji, bez „a może jednak".</h2>
            <p>
                Wiem co Cię najbardziej powstrzymuje przed wysłaniem formularza:
                strach że odezwę się 14 razy w ciągu miesiąca. Nie odzywam się.
                Tu masz dokładny scenariusz.
            </p>
        </header>
        <div class="lpc-process-grid">
            <div class="lpc-step" data-animate>
                <h3>Wypełniasz formularz</h3>
                <p>2 minuty. Imię, e-mail, krótki opis tego co dziś nie działa. Nie potrzebujesz briefu ani szczegółowej strategii.</p>
            </div>
            <div class="lpc-step" data-animate data-delay="1">
                <h3>Odpisuję w 24h z terminami</h3>
                <p>Dostajesz e-mail bezpośrednio ode mnie. Wybierasz dogodny termin — telefon lub Google Meet, jak wolisz.</p>
            </div>
            <div class="lpc-step" data-animate data-delay="2">
                <h3>30 minut, mapa wniosków</h3>
                <p>Rozmawiamy konkretnie. Po rozmowie dostajesz pisemne podsumowanie — żebyś mógł wrócić do tego później albo dać innym osobom w firmie.</p>
            </div>
        </div>
    </div>
</section>

<section class="lpc-section lpc-section-soft" id="o-mnie">
    <div class="lpc-wrap" data-animate>
        <div class="lpc-author">
            <div class="lpc-author-photo" aria-hidden="true">
                <?php
                if (function_exists("upsellio_render_home_media_image")) {
                    $author_img = upsellio_render_home_media_image("about_portrait", [
                        "size" => "medium",
                        "loading" => "lazy",
                    ]);
                    if ($author_img !== "") echo $author_img;
                    else echo '<div class="lpc-author-photo-placeholder">[ Sebastian Kelm ]</div>';
                } else {
                    echo '<div class="lpc-author-photo-placeholder">[ Sebastian Kelm ]</div>';
                }
                ?>
            </div>
            <div>
                <span class="lpc-author-tag">Z kim rozmawiasz</span>
                <h3>Sebastian Kelm</h3>
                <p>
                    10 lat sprzedaży B2B. Najpierw jako handlowiec robiący 1,5 mln zł/mc,
                    potem dyrektor sprzedaży w 15-osobowym zespole. W marketingu B2C wnoszę dokładnie
                    to czego brakuje większości specjalistów od reklam — perspektywę handlowca który wie,
                    że klient nie kupuje od liczby na CTR. Kupuje gdy mu się to opłaca,
                    ufa wykonawcy, i ma minimum tarcia w drodze do koszyka albo telefonu.
                </p>
                <div class="lpc-author-stats">
                    <div><b>10 lat</b><span>sprzedaży i marketingu</span></div>
                    <div><b>5-7</b><span>klientów naraz, sam</span></div>
                    <div><b>0 zł</b><span>pierwsza rozmowa</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="lpc-section" id="faq">
    <div class="lpc-wrap">
        <header class="lpc-sec-head" data-animate>
            <h2>Najczęstsze pytania przed rozmową</h2>
            <p>Jeśli masz inne — napisz w formularzu, odpowiem osobiście.</p>
        </header>
        <div class="lpc-faq" data-animate>
            <?php foreach ($lpc_faq_items as $item) : ?>
                <details class="lpc-faq-item">
                    <summary>
                        <span><?php echo esc_html((string) $item["question"]); ?></span>
                        <span class="lpc-faq-icon" aria-hidden="true">+</span>
                    </summary>
                    <p><?php echo esc_html((string) $item["answer"]); ?></p>
                </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="lpc-final" id="formularz-stopka">
    <div class="lpc-wrap">
        <div class="lpc-final-box">
            <h2>30 minut które nic nie kosztują. Nawet jeśli nie zostaniesz klientem.</h2>
            <p>
                Wypełnij formularz, opisz w 2 zdaniach co masz i co nie działa.
                Odezwę się w 24h z terminami. Bez follow-upów, bez pitchu, bez „a może jednak".
            </p>
            <div class="lpc-final-form-wrap">
                <div class="lpc-form-card">
                    <span class="lpc-form-card-tag">Formularz</span>
                    <h2>Umów bezpłatną 30-min rozmowę</h2>
                    <p class="lpc-form-card-sub">Zapis trafia do tego samego CRM co formularz w sekcji hero.</p>
                    <?php
                    if (function_exists("upsellio_render_lead_form")) {
                        echo upsellio_render_lead_form([
                            "origin" => "landing-b2c-form",
                            "submit_label" => "Umów bezpłatną diagnozę →",
                            "variant" => "full",
                            "heading" => "",
                            "subheading" => "",
                            "redirect_url" => $current_page_url . "#formularz-stopka",
                            "service_options" => $lpc_service_options,
                            "message_placeholder" => $lpc_message_placeholder,
                            "css_class" => "lpc-form",
                            "form_id" => "landing-b2c-form-stopka",
                        ]);
                    } else {
                        echo '<p><a href="mailto:' . esc_attr($contact_email) . '" class="lpc-final-btn" style="display:inline-flex;">Napisz — ' . esc_html($contact_email) . "</a></p>";
                    }
                    ?>
                </div>
            </div>
            <p class="lpc-final-note">
                Albo zadzwoń: <a href="tel:<?php echo esc_attr($contact_phone_href); ?>"><?php echo esc_html($contact_phone); ?></a>
                · <a href="#formularz" style="color:rgba(255,255,255,.55);">Formularz na górze</a>
            </p>
        </div>
    </div>
</section>
</main>

<div class="lpc-mobile-cta" aria-hidden="true">
    <a href="#formularz-stopka">Umów bezpłatną diagnozę →</a>
</div>

<script type="application/ld+json">
<?php
echo wp_json_encode([
    "@context" => "https://schema.org",
    "@type" => "Service",
    "serviceType" => "Marketing online dla firm B2C — sklepy i lokalne usługi",
    "name" => "Marketing B2C — bezpłatna 30-min diagnoza | Upsellio",
    "provider" => [
        "@type" => "LocalBusiness",
        "name" => "Upsellio — Marketing",
        "founder" => [
            "@type" => "Person",
            "name" => "Sebastian Kelm",
        ],
        "telephone" => $contact_phone,
        "email" => $contact_email,
    ],
    "description" => "Bezpłatna 30-min diagnoza marketingu Google i Meta dla sklepów online i lokalnych usług B2C. Specjalista sprzedaży z 10-letnim doświadczeniem.",
    "areaServed" => "Polska",
    "offers" => [
        "@type" => "Offer",
        "price" => "0",
        "priceCurrency" => "PLN",
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
</script>

<?php if (!empty($lpc_faq_items)) : ?>
<script type="application/ld+json">
<?php
echo wp_json_encode([
    "@context" => "https://schema.org",
    "@type" => "FAQPage",
    "mainEntity" => array_map(static function ($item) {
        return [
            "@type" => "Question",
            "name" => (string) $item["question"],
            "acceptedAnswer" => ["@type" => "Answer", "text" => (string) $item["answer"]],
        ];
    }, $lpc_faq_items),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
</script>
<?php endif; ?>

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
        document.querySelectorAll("[data-animate]").forEach(function(el) { io.observe(el); });
    } else {
        document.querySelectorAll("[data-animate]").forEach(function(el) { el.classList.add("is-visible"); });
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
    var _lpcPh = <?php echo wp_json_encode((string) $lpc_message_placeholder); ?>;
    ["landing-b2c-form", "landing-b2c-form-stopka"].forEach(function (fid) {
        var form = document.getElementById(fid);
        if (form) {
            var msg = form.querySelector('textarea[name="lead_message"]');
            if (msg && _lpcPh) msg.setAttribute("placeholder", _lpcPh);
        }
    });
})();
</script>

<?php get_footer(); ?>
