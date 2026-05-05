<?php
/*
Template Name: Upsellio - Landing Strony WWW (Diagnoza)
Template Post Type: page
Description: Landing pod ruch z reklam — sprzedaje strony WWW dla firm B2B. Cel: bezpłatna 30-min rozmowa diagnostyczna. Adresuje 2 profile: ma stronę vs zaczyna od zera.
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

$lpw_service_options = [
    "Strona firmowa B2B (od zera)",
    "Landing page pod kampanie",
    "Przebudowa istniejącej strony",
    "Strona dla nowej oferty / produktu",
    "Nie wiem — chcę porozmawiać",
];

$lpw_portfolio = [];
if (function_exists("upsellio_get_portfolio_list")) {
    $all_portfolio = upsellio_get_portfolio_list(3);
    if (is_array($all_portfolio)) {
        $lpw_portfolio = array_slice($all_portfolio, 0, 3);
    }
}

$lpw_faq_items = [
    [
        "question" => "Ile kosztuje strona internetowa firmowa B2B?",
        "answer" => "Strony firmowe od 8 000 zł netto, landing pages od 4 000 zł netto, sklepy WooCommerce od 12 000 zł netto. Konkretną wycenę przygotowuję dopiero gdy wiem co dokładnie ma zrobić strona, dla kogo i jakie cele biznesowe spełnia. Na pierwszej rozmowie powiem Ci realistyczne widełki dla Twojego przypadku.",
    ],
    [
        "question" => "Ile trwa zrobienie strony?",
        "answer" => "Landing page: 2-3 tygodnie. Strona firmowa B2B (5-8 podstron): 4-6 tygodni. Sklep B2B z integracjami: 6-10 tygodni. Co istotne: ja nie czekam na Twoje treści tygodniami. Przed startem ustalamy harmonogram, a content piszę sam (chyba że masz własny copywriter).",
    ],
    [
        "question" => "Czy będę mógł sam edytować treści po oddaniu?",
        "answer" => "Tak. Strony robię na WordPress (CMS, panel administracyjny). Po oddaniu dostajesz dostęp i 30-minutowy filmik instruktażowy — pokazuję dokładnie jak edytować teksty, dodawać wpisy blog, podmieniać zdjęcia. Bez kodowania.",
    ],
    [
        "question" => "Czy piszesz też treści, czy muszę je dostarczyć sam?",
        "answer" => "Piszę. Nagłówki, oferta, sekcje sprzedażowe, FAQ, opisy usług. To istotne, bo strona B2B żyje albo umiera od jakości tekstu — nie od grafiki. Jeśli masz własnego copywritera, to oczywiście współpracuję, ale nie wymagam.",
    ],
    [
        "question" => "Co jeśli mam już stronę i nie wiem czy robić nową?",
        "answer" => "Często nie potrzebujesz nowej. Czasem wystarczy przebudować 2-3 sekcje, poprawić CTA, naprawić sekcję dla kogo. Na bezpłatnej rozmowie patrzę na Twoją obecną stronę i mówię wprost: wystarczy poprawka, czy rzeczywiście warto budować od zera. Nigdy nie sprzedaję nowej strony, jeśli stara da się uratować.",
    ],
    [
        "question" => "Co dostaję poza samą stroną?",
        "answer" => "Każdy projekt zawiera: copywriting (nagłówki, sekcje, CTA), SEO podstawowe (struktura, meta tagi, sitemap), integrację Google Analytics i Tag Manager, konfigurację formularzy, optymalizację Core Web Vitals (szybkość ładowania), 30 dni wsparcia po oddaniu (drobne poprawki bez dodatkowych kosztów).",
    ],
];

get_header();
?>

<style>
body.page-template-page-landing-www-php .nav-links,
body.page-template-page-landing-www-php .nav-dropdown,
body.page-template-page-landing-www-php .mobile-menu,
body.page-template-page-landing-www-php .hamburger,
body.page-template-page-landing-www-php .nav-cta,
body.page-template-page-landing-www-php .mobile-sticky-cta,
body.page-template-page-landing-www-php .ups-breadcrumbs,
body[class*="page-landing-www"] .nav-links,
body[class*="page-landing-www"] .nav-dropdown,
body[class*="page-landing-www"] .mobile-menu,
body[class*="page-landing-www"] .hamburger,
body[class*="page-landing-www"] .nav-cta,
body[class*="page-landing-www"] .mobile-sticky-cta,
body[class*="page-landing-www"] .ups-breadcrumbs {
    display: none !important;
}
.lpw { font-family: "DM Sans", system-ui, sans-serif; color: #0d0d0b; background: #fafaf6; line-height: 1.65; }
.lpw *, .lpw *::before, .lpw *::after { box-sizing: border-box; }
.lpw-wrap { max-width: 1180px; margin-inline: auto; padding: 0 24px; }
.lpw-hero {
    padding: 60px 0 88px;
    background:
        radial-gradient(ellipse at 88% 8%, rgba(13,148,136,.16), transparent 36%),
        radial-gradient(ellipse at 8% 92%, rgba(99,102,241,.06), transparent 32%),
        linear-gradient(180deg, #fafaf6 0%, #f1f1ec 100%);
}
.lpw-hero-grid { display: grid; grid-template-columns: 1.1fr 1fr; gap: 56px; align-items: start; }
.lpw-eyebrow {
    display: inline-flex; align-items: center; gap: 10px; margin-bottom: 18px;
    color: #0f766e; font-size: 12px; font-weight: 900; letter-spacing: 1.4px; text-transform: uppercase;
}
.lpw-eyebrow::before { content: ""; width: 24px; height: 2px; background: #0d9488; border-radius: 2px; }
.lpw-eyebrow-light { color: #5eead4; }
.lpw-eyebrow-light::before { background: #5eead4; }
.lpw-h1 {
    font-family: "Syne", "Bricolage Grotesque", sans-serif; font-weight: 800;
    font-size: clamp(40px, 5.4vw, 64px); line-height: .98; letter-spacing: -2px; margin: 0 0 22px; color: #0a1410;
}
.lpw-h1 em { font-style: normal; color: #0d9488; }
.lpw-h1 s { text-decoration: line-through; text-decoration-color: rgba(15,118,110,.4); color: #94918a; }
.lpw-lead { font-size: 18px; line-height: 1.65; color: #3d3d38; max-width: 56ch; margin: 0 0 28px; }
.lpw-hero-bullets { list-style: none; padding: 0; margin: 0 0 32px; display: grid; gap: 12px; }
.lpw-hero-bullets li { display: flex; align-items: flex-start; gap: 12px; font-size: 15px; color: #1a1a17; line-height: 1.55; }
.lpw-bullet-icon {
    flex: 0 0 24px; width: 24px; height: 24px; border-radius: 50%; background: #ccfbf1; color: #0f766e;
    display: grid; place-items: center; font-weight: 900; font-size: 13px; margin-top: 1px;
}
.lpw-hero-trust {
    display: flex; flex-wrap: wrap; gap: 20px; padding: 18px 22px; background: rgba(255,255,255,.7);
    border: 1px solid #e7e7e1; border-radius: 16px; font-size: 13px; color: #3d3d38;
}
.lpw-hero-trust > div { display: flex; align-items: center; gap: 8px; font-weight: 600; }
.lpw-hero-trust strong { color: #0d9488; font-size: 15px; }
.lpw-form-card {
    background: #ffffff; border: 1px solid #dbe7ea; border-radius: 24px; padding: 32px;
    box-shadow: 0 24px 60px rgba(15,23,42,.1), 0 4px 8px rgba(15,23,42,.04);
    position: sticky; top: 24px;
}
.lpw-form-card-tag {
    display: inline-block; padding: 5px 12px; background: #f0fdf4; border-radius: 99px;
    font-size: 11px; font-weight: 900; letter-spacing: 1.2px; text-transform: uppercase; color: #15803d; margin-bottom: 12px;
}
.lpw-form-card h2 {
    margin: 0 0 8px; font-family: "Syne", "Bricolage Grotesque", sans-serif;
    font-size: 26px; line-height: 1.08; letter-spacing: -.8px; color: #0a1410;
}
.lpw-form-card-sub { margin: 0 0 22px; color: #64748b; font-size: 14px; line-height: 1.55; }
.lpw-form-card-after {
    margin-top: 18px; padding: 14px 16px; background: #f8fafc; border: 1px solid #e2e8f0;
    border-radius: 12px; font-size: 12.5px; color: #475569; line-height: 1.55;
}
.lpw-form-card-after strong { color: #0a1410; display: block; margin-bottom: 4px; }
.lpw-form-card .ups-form input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),
.lpw-form-card .ups-form select,
.lpw-form-card .ups-form textarea {
    width: 100%; min-height: 46px; background: #ffffff; border: 1.5px solid #e7e7e1; border-radius: 12px;
    padding: 11px 14px; color: #0a1410; font-size: 14.5px; font-family: inherit; transition: border-color .15s, box-shadow .15s;
}
.lpw-form-card .ups-form textarea { min-height: 100px; resize: vertical; line-height: 1.55; }
.lpw-form-card .ups-form input:focus,
.lpw-form-card .ups-form select:focus,
.lpw-form-card .ups-form textarea:focus {
    border-color: #0d9488; box-shadow: 0 0 0 4px rgba(13,148,136,.13); outline: none;
}
.lpw-form-card .ups-form__consent { display: flex; align-items: flex-start; gap: 10px; font-size: 12.5px; color: #475569; line-height: 1.5; }
.lpw-form-card .ups-form__consent input[type="checkbox"] { width: 18px; height: 18px; min-height: 18px; margin: 2px 0 0; flex-shrink: 0; accent-color: #0d9488; }
.lpw-form-card .ups-form__submit,
.lpw-form-card button[type="submit"] {
    width: 100%; min-height: 56px; border: 0; border-radius: 999px; background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
    color: #ffffff; font-family: inherit; font-size: 15px; font-weight: 900; cursor: pointer; margin-top: 6px;
    box-shadow: 0 18px 40px rgba(13,148,136,.28); transition: transform .18s, box-shadow .18s;
}
.lpw-form-card .ups-form__submit:hover,
.lpw-form-card button[type="submit"]:hover { transform: translateY(-1px); box-shadow: 0 22px 44px rgba(13,148,136,.34); }
.lpw-section { padding: 88px 0; }
.lpw-section-soft { background: #f1f1ec; }
.lpw-section-dark { background: #0a1410; color: #fff; position: relative; overflow: hidden; }
.lpw-section-dark::before {
    content: ""; position: absolute; width: 600px; height: 600px; border-radius: 50%;
    background: radial-gradient(circle, rgba(20,184,166,.18), transparent 65%); right: -240px; top: -240px; pointer-events: none;
}
.lpw-section-dark .lpw-wrap { position: relative; z-index: 2; }
.lpw-sec-head { max-width: 760px; margin-bottom: 36px; }
.lpw-sec-head h2 {
    margin: 0; font-family: "Syne", "Bricolage Grotesque", sans-serif; font-weight: 800; font-size: clamp(30px, 3.6vw, 46px);
    line-height: 1.04; letter-spacing: -1.4px; color: #0a1410;
}
.lpw-section-dark .lpw-sec-head h2 { color: #fff; }
.lpw-sec-head p { margin: 14px 0 0; max-width: 64ch; color: #475569; font-size: 17px; line-height: 1.65; }
.lpw-section-dark .lpw-sec-head p { color: rgba(255,255,255,.7); }
.lpw-profiles { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
.lpw-profile {
    background: #fff; border: 1px solid #dbe7ea; border-radius: 22px; padding: 32px; transition: border-color .2s, transform .2s, box-shadow .2s;
}
.lpw-profile:hover { border-color: #0d9488; transform: translateY(-3px); box-shadow: 0 18px 36px rgba(13,148,136,.1); }
.lpw-profile-tag {
    display: inline-block; padding: 5px 12px; background: #ccfbf1; color: #0f766e; border-radius: 99px;
    font-size: 11px; font-weight: 900; letter-spacing: 1.2px; text-transform: uppercase; margin-bottom: 16px;
}
.lpw-profile.is-second .lpw-profile-tag { background: #fef3c7; color: #92400e; }
.lpw-profile h3 {
    margin: 0 0 14px; font-family: "Syne", "Bricolage Grotesque", sans-serif; font-size: 22px; line-height: 1.18; letter-spacing: -.6px; color: #0a1410;
}
.lpw-profile-list { list-style: none; padding: 0; margin: 0; display: grid; gap: 10px; }
.lpw-profile-list li { display: flex; gap: 10px; color: #475569; font-size: 14.5px; line-height: 1.55; }
.lpw-profile-list li::before { content: "→"; color: #0d9488; font-weight: 900; flex-shrink: 0; }
.lpw-problems-grid { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 16px; }
.lpw-problem { background: #fff; border: 1px solid #e7e7e1; border-radius: 20px; padding: 28px; transition: border-color .2s, transform .2s; }
.lpw-problem:hover { border-color: #0d9488; transform: translateY(-2px); }
.lpw-problem-num {
    display: inline-block; font-family: "Syne", sans-serif; font-size: 14px; font-weight: 800; color: #0d9488; letter-spacing: 1.5px; margin-bottom: 12px;
}
.lpw-problem h3 {
    margin: 0 0 10px; font-family: "Syne", "Bricolage Grotesque", sans-serif; font-size: 18px; line-height: 1.22; letter-spacing: -.4px; color: #0a1410;
}
.lpw-problem p { margin: 0; color: #475569; font-size: 14px; line-height: 1.6; }
.lpw-diagnosis {
    background: #fff; border: 1px solid #e7e7e1; border-radius: 28px; overflow: hidden; box-shadow: 0 18px 44px rgba(15,23,42,.06);
    display: grid; grid-template-columns: 1fr 1fr;
}
.lpw-diagnosis-side {
    background: linear-gradient(165deg, #0d9488 0%, #115e59 100%); color: #fff; padding: 48px 40px; position: relative; overflow: hidden;
}
.lpw-diagnosis-side::before {
    content: ""; position: absolute; top: -100px; right: -100px; width: 300px; height: 300px; border-radius: 50%; background: rgba(255,255,255,.06);
}
.lpw-diagnosis-side .lpw-eyebrow { color: #5eead4; }
.lpw-diagnosis-side .lpw-eyebrow::before { background: #5eead4; }
.lpw-diagnosis-side h2 {
    font-family: "Syne", "Bricolage Grotesque", sans-serif; font-weight: 800; font-size: 32px; line-height: 1.06; letter-spacing: -1.2px;
    margin: 0 0 18px; color: #fff; max-width: 14ch; position: relative;
}
.lpw-diagnosis-side p { margin: 0; color: rgba(255,255,255,.85); font-size: 15px; line-height: 1.65; position: relative; }
.lpw-diagnosis-list { padding: 48px 40px; list-style: none; margin: 0; display: grid; gap: 18px; }
.lpw-diagnosis-list li { display: grid; grid-template-columns: 32px 1fr; gap: 14px; align-items: flex-start; }
.lpw-diagnosis-num {
    width: 32px; height: 32px; border-radius: 50%; background: #ccfbf1; color: #0f766e; font-weight: 900; font-family: "Syne", sans-serif;
    font-size: 14px; display: grid; place-items: center;
}
.lpw-diagnosis-list li b {
    display: block; font-family: "Syne", "Bricolage Grotesque", sans-serif; font-weight: 800; font-size: 16px; color: #0a1410; margin-bottom: 4px;
}
.lpw-diagnosis-list li span { font-size: 14px; color: #475569; line-height: 1.55; }
.lpw-portfolio-grid { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 18px; }
.lpw-port {
    background: rgba(255,255,255,.045); border: 1px solid rgba(255,255,255,.12); border-radius: 20px; overflow: hidden;
    transition: border-color .2s, transform .2s; display: flex; flex-direction: column;
}
.lpw-port:hover { border-color: rgba(94,234,212,.4); transform: translateY(-3px); }
.lpw-port-thumb { aspect-ratio: 16/10; background: linear-gradient(135deg, #1a3530 0%, #0a1410 100%); position: relative; overflow: hidden; }
.lpw-port-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.lpw-port-thumb-placeholder {
    position: absolute; inset: 0; display: grid; place-items: center; color: rgba(94,234,212,.6); font-family: ui-monospace, monospace; font-size: 13px;
    letter-spacing: 1.5px; background-image: repeating-linear-gradient(135deg, rgba(20,184,166,.04) 0 12px, transparent 12px 24px);
}
.lpw-port-body { padding: 24px 28px 28px; flex: 1; display: flex; flex-direction: column; }
.lpw-port-tag { font-size: 11px; letter-spacing: 1.4px; text-transform: uppercase; color: rgba(255,255,255,.5); margin-bottom: 10px; }
.lpw-port h3 {
    margin: 0 0 10px; font-family: "Syne", "Bricolage Grotesque", sans-serif; font-size: 18px; line-height: 1.2; letter-spacing: -.4px; color: #fff; flex: 1;
}
.lpw-port-result { color: #5eead4; font-size: 14px; font-weight: 700; line-height: 1.55; margin-top: auto; }
.lpw-process-grid { display: grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 14px; counter-reset: lpwstep; }
.lpw-step { background: #fff; border: 1px solid #e7e7e1; border-radius: 20px; padding: 26px; position: relative; }
.lpw-step::before {
    counter-increment: lpwstep; content: counter(lpwstep, decimal-leading-zero); position: absolute; top: 18px; right: 20px;
    font-family: "Syne", sans-serif; font-size: 44px; font-weight: 800; color: #ccfbf1; line-height: 1; letter-spacing: -2px;
}
.lpw-step h3 {
    margin: 0 0 8px; font-family: "Syne", "Bricolage Grotesque", sans-serif; font-size: 17px; line-height: 1.2; letter-spacing: -.3px; color: #0a1410;
    max-width: 14ch; position: relative;
}
.lpw-step p { margin: 0 0 10px; color: #475569; font-size: 14px; line-height: 1.6; position: relative; }
.lpw-step-when {
    display: inline-block; padding: 4px 10px; background: #f0fdf4; color: #15803d; border-radius: 99px;
    font-size: 11px; font-weight: 700; letter-spacing: .4px; position: relative;
}
.lpw-author {
    background: #fff; border: 1px solid #e7e7e1; border-radius: 28px; padding: 40px; display: grid; grid-template-columns: 200px 1fr;
    gap: 36px; align-items: center; box-shadow: 0 18px 44px rgba(15,23,42,.06);
}
.lpw-author-photo {
    aspect-ratio: 1; border-radius: 20px; background: linear-gradient(165deg, #ccfbf1, #dff8f4);
    border: 1px solid #99f6e4; overflow: hidden; position: relative;
}
.lpw-author-photo img { width: 100%; height: 100%; object-fit: cover; display: block; }
.lpw-author-photo-placeholder {
    position: absolute; inset: 0; background-image: repeating-linear-gradient(135deg, rgba(13,148,136,.12) 0 12px, transparent 12px 24px);
    display: grid; place-items: center; color: #0f766e; font-family: ui-monospace, monospace; font-size: 12px;
}
.lpw-author-tag {
    display: inline-block; padding: 4px 12px; background: #f0fdfa; color: #0f766e; border-radius: 99px;
    font-size: 11px; font-weight: 900; letter-spacing: 1.2px; text-transform: uppercase; margin-bottom: 12px;
}
.lpw-author h3 { margin: 0 0 8px; font-family: "Syne", "Bricolage Grotesque", sans-serif; font-size: 26px; line-height: 1.1; letter-spacing: -.6px; }
.lpw-author > div p { margin: 0 0 18px; color: #475569; font-size: 15px; line-height: 1.65; }
.lpw-author-stats { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 12px; }
.lpw-author-stats > div { padding: 14px 16px; background: #fafaf6; border: 1px solid #e7e7e1; border-radius: 12px; }
.lpw-author-stats b { display: block; font-family: "Syne", sans-serif; font-weight: 800; font-size: 22px; color: #0d9488; line-height: 1; }
.lpw-author-stats span { display: block; font-size: 12px; color: #64748b; margin-top: 5px; line-height: 1.4; }
.lpw-faq { display: grid; gap: 10px; max-width: 880px; }
.lpw-faq-item { background: #fff; border: 1px solid #e7e7e1; border-radius: 16px; transition: border-color .2s; }
.lpw-faq-item[open] { border-color: #0d9488; }
.lpw-faq-item summary {
    list-style: none; cursor: pointer; display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 22px 26px;
    font-family: "Syne", "Bricolage Grotesque", sans-serif; font-size: 17px; font-weight: 700; color: #0a1410;
}
.lpw-faq-item summary::-webkit-details-marker { display: none; }
.lpw-faq-icon {
    width: 28px; height: 28px; border-radius: 50%; background: #f1f5f9; display: grid; place-items: center; font-size: 18px; color: #475569;
    flex: 0 0 28px; transition: transform .2s, background .2s, color .2s;
}
.lpw-faq-item[open] .lpw-faq-icon { transform: rotate(45deg); background: #ccfbf1; color: #0f766e; }
.lpw-faq-item p { margin: 0; padding: 0 26px 22px; color: #475569; font-size: 15px; line-height: 1.65; }
.lpw-final { padding: 0 0 110px; }
.lpw-final-box {
    background: radial-gradient(circle at 88% 20%, rgba(13,148,136,.4), transparent 38%), #0a1410;
    color: #fff; border-radius: 32px; padding: 56px; text-align: center;
}
.lpw-final-box h2 {
    font-family: "Syne", "Bricolage Grotesque", sans-serif; font-weight: 800; font-size: clamp(30px, 4vw, 50px);
    line-height: 1.02; letter-spacing: -1.6px; margin: 0 auto 16px; color: #fff; max-width: 22ch;
}
.lpw-final-box p { max-width: 56ch; margin: 0 auto 28px; color: rgba(255,255,255,.7); font-size: 17px; line-height: 1.65; }
.lpw-final-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 10px; min-height: 60px; padding: 0 36px; border-radius: 999px;
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); color: #fff; font-weight: 900; font-size: 16px; text-decoration: none;
    box-shadow: 0 20px 44px rgba(13,148,136,.42); transition: transform .2s, box-shadow .2s;
}
.lpw-final-btn:hover { transform: translateY(-2px); box-shadow: 0 26px 52px rgba(13,148,136,.5); }
.lpw-final-note { margin: 22px 0 0; font-size: 13px; color: rgba(255,255,255,.5); }
.lpw-final-note a { color: #5eead4; font-weight: 700; text-decoration: none; }
.lpw-final-form-wrap { max-width: 560px; margin: 28px auto 0; }
.lpw-final-form-wrap .lpw-form-card { position: static; text-align: left; margin: 0 auto; }
.lpw-mobile-cta {
    display: none; position: fixed; bottom: 0; left: 0; right: 0; background: #fff; border-top: 1px solid #e7e7e1;
    padding: 12px 16px; z-index: 100; box-shadow: 0 -6px 24px rgba(15,23,42,.08);
}
.lpw-mobile-cta a {
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
    .lpw-hero-grid { grid-template-columns: 1fr; gap: 40px; }
    .lpw-form-card { position: static; }
    .lpw-profiles { grid-template-columns: 1fr; }
    .lpw-problems-grid, .lpw-portfolio-grid, .lpw-process-grid { grid-template-columns: 1fr 1fr; }
    .lpw-diagnosis { grid-template-columns: 1fr; }
    .lpw-diagnosis-side { padding: 36px 32px; }
    .lpw-diagnosis-list { padding: 36px 32px; }
    .lpw-author { grid-template-columns: 140px 1fr; gap: 24px; padding: 32px; }
}
@media (max-width: 720px) {
    .lpw-hero { padding: 36px 0 56px; }
    .lpw-section { padding: 64px 0; }
    .lpw-problems-grid, .lpw-portfolio-grid, .lpw-process-grid { grid-template-columns: 1fr; }
    .lpw-form-card { padding: 24px; border-radius: 20px; }
    .lpw-final { padding-bottom: 100px; }
    .lpw-final-box { padding: 32px 22px; border-radius: 24px; }
    .lpw-author { grid-template-columns: 1fr; padding: 24px; text-align: left; }
    .lpw-author-photo { max-width: 200px; margin: 0 auto; }
    .lpw-author-stats { grid-template-columns: 1fr 1fr; }
    .lpw-mobile-cta { display: block; }
    .lpw { padding-bottom: 80px; }
    .lpw-step::before { font-size: 36px; top: 16px; right: 16px; }
}
</style>

<main class="lpw" id="diagnoza-strony">
<section class="lpw-hero">
    <div class="lpw-wrap">
        <div class="lpw-hero-grid">
            <div class="lpw-hero-copy" data-animate>
                <div class="lpw-eyebrow">⚡ Bezpłatna 30-min diagnoza</div>
                <h1 class="lpw-h1">
                    Strona dla firmy B2B<br>
                    która <s>wygląda dobrze</s> <em>generuje leady.</em>
                </h1>
                <p class="lpw-lead">
                    Większość stron firmowych B2B w Polsce to drogie wizytówki.
                    Wyglądają dobrze, milczą gdy klient ma kupić. W 30 minut sprawdzę gdzie
                    Twoja strona traci zapytania — albo zaprojektuję ją od początku tak,
                    żeby pracowała na sprzedaż, nie na podziw klienta z agencji.
                </p>
                <ul class="lpw-hero-bullets">
                    <li><span class="lpw-bullet-icon">✓</span><span>Strony firmowe, landing pages i sklepy B2B</span></li>
                    <li><span class="lpw-bullet-icon">✓</span><span>Copywriting i SEO w cenie projektu — nie dopłacasz osobno</span></li>
                    <li><span class="lpw-bullet-icon">✓</span><span>Pierwsze efekty (więcej zapytań) w 30-60 dni od uruchomienia</span></li>
                    <li><span class="lpw-bullet-icon">✓</span><span>Po oddaniu sam edytujesz treści — bez programisty</span></li>
                </ul>
                <div class="lpw-hero-trust">
                    <div><strong>10 lat</strong> sprzedaży B2B</div>
                    <div><strong>0 zł</strong> pierwsza rozmowa</div>
                    <div><strong>WordPress</strong> łatwa edycja</div>
                </div>
            </div>
            <aside class="lpw-form-card" id="formularz" data-animate data-delay="1">
                <span class="lpw-form-card-tag">▶ Zacznij tu</span>
                <h2>Umów bezpłatną 30-min rozmowę</h2>
                <p class="lpw-form-card-sub">
                    Pokaż link do obecnej strony albo opisz co planujesz zbudować.
                    Odpowiem w ciągu 24h.
                </p>
                <?php
                if (function_exists("upsellio_render_lead_form")) {
                    echo upsellio_render_lead_form([
                        "origin" => "landing-www-form",
                        "submit_label" => "Umów bezpłatną diagnozę →",
                        "variant" => "full",
                        "heading" => "",
                        "subheading" => "",
                        "redirect_url" => $current_page_url,
                        "service_options" => $lpw_service_options,
                        "message_placeholder" => "Np. Mamy stronę z 2020, ruch organiczny rośnie ale bounce 70% i zero leadów albo startujemy nową firmę usługową i potrzebujemy strony z formularzem i blogiem.",
                        "css_class" => "lpw-form",
                        "form_id" => "landing-www-form",
                    ]);
                } else {
                    echo '<p>Formularz: napisz na <a href="mailto:' . esc_attr($contact_email) . '">' . esc_html($contact_email) . "</a></p>";
                }
                ?>
                <div class="lpw-form-card-after">
                    <strong>Co dalej po wysłaniu?</strong>
                    Do 24h dostaniesz odpowiedź ze mną z terminami. Rozmawiamy przez telefon
                    lub Google Meet — jak Ci wygodniej.
                </div>
            </aside>
        </div>
    </div>
</section>

<section class="lpw-section lpw-section-soft" id="dla-kogo">
    <div class="lpw-wrap">
        <header class="lpw-sec-head" data-animate>
            <h2>Dwie sytuacje. Każda wymaga innej strony.</h2>
            <p>Najpierw chcę wiedzieć w której z tych dwóch sytuacji jesteś. Bo strony pisane od zera i strony naprawiane to dwa różne projekty.</p>
        </header>
        <div class="lpw-profiles">
            <div class="lpw-profile" data-animate>
                <span class="lpw-profile-tag">Profil A · Mam stronę</span>
                <h3>Strona jest, ale konwertuje słabo. Wiesz że mogłaby więcej.</h3>
                <ul class="lpw-profile-list">
                    <li>Wchodzą na stronę, ale formularz milczy</li>
                    <li>Bounce rate powyżej 60%, sesje krótkie</li>
                    <li>Płacisz za reklamy, ale ROI jest słabe</li>
                    <li>Strona jest „po bożemu", ale klient nie wie czym się zajmujesz</li>
                    <li>Próbowałeś już SEO i marketingu, dalej cisza</li>
                </ul>
            </div>
            <div class="lpw-profile is-second" data-animate data-delay="1">
                <span class="lpw-profile-tag">Profil B · Buduję od zera</span>
                <h3>Pierwsza strona albo nowy biznes. Nie chcesz zrobić tego źle.</h3>
                <ul class="lpw-profile-list">
                    <li>Konkurencja jest w Google, Ty jeszcze nie</li>
                    <li>Masz produkt/usługę, ale brakuje obecności online</li>
                    <li>Nie chcesz wpaść na agencję, która spali pierwsze 30 tysięcy</li>
                    <li>Masz oferty od freelancerów, każda mówi co innego</li>
                    <li>Wiesz że strona to inwestycja, nie wydatek — ale nie wiesz jak ją zrobić mądrze</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="lpw-section">
    <div class="lpw-wrap">
        <header class="lpw-sec-head" data-animate>
            <h2>Dlaczego większość stron firmowych nie generuje zapytań</h2>
            <p>W 9 na 10 stron B2B widzę te same błędy. Każdy z nich kosztuje konkretne leady miesięcznie.</p>
        </header>
        <div class="lpw-problems-grid">
            <article class="lpw-problem" data-animate><div class="lpw-problem-num">01</div><h3>Hero nie mówi czym się zajmujesz</h3><p>„Witamy na naszej stronie" zamiast pytania albo problemu klienta. Pierwsze 3 sekundy decydują czy zostanie.</p></article>
            <article class="lpw-problem" data-animate><div class="lpw-problem-num">02</div><h3>Strona mówi o firmie, nie o kliencie</h3><p>Klient szuka rozwiązania jego problemu i dowodu, że umiesz go rozwiązać.</p></article>
            <article class="lpw-problem" data-animate data-delay="1"><div class="lpw-problem-num">03</div><h3>Brak konkretnych dowodów</h3><p>Brak liczb, opinii, case studies, logotypów klientów.</p></article>
            <article class="lpw-problem" data-animate data-delay="1"><div class="lpw-problem-num">04</div><h3>CTA są niewidoczne lub w złych miejscach</h3><p>Klient nie wie który jest głównym krokiem.</p></article>
            <article class="lpw-problem" data-animate data-delay="2"><div class="lpw-problem-num">05</div><h3>Wolne ładowanie na mobile</h3><p>Strona ładująca się 6 sekund traci użytkownika zanim przeczyta pierwsze zdanie.</p></article>
            <article class="lpw-problem" data-animate data-delay="2"><div class="lpw-problem-num">06</div><h3>Strona nie pasuje do reklam</h3><p>Reklama obiecuje X, strona mówi Y i budżet się marnuje.</p></article>
        </div>
    </div>
</section>

<section class="lpw-section lpw-section-soft" id="co-dostaniesz">
    <div class="lpw-wrap" data-animate>
        <div class="lpw-diagnosis">
            <div class="lpw-diagnosis-side">
                <div class="lpw-eyebrow lpw-eyebrow-light">Co dostajesz</div>
                <h2>30 minut. Pięć konkretnych odpowiedzi.</h2>
                <p>Niezależnie od tego czy masz już stronę czy zaczynasz od zera — wychodzisz z rozmowy z konkretną mapą działania.</p>
            </div>
            <ol class="lpw-diagnosis-list">
                <li><div class="lpw-diagnosis-num">1</div><div><b>Co konkretnie blokuje sprzedaż</b><span>Wskażę 3-5 punktów do poprawy o najwyższym wpływie.</span></div></li>
                <li><div class="lpw-diagnosis-num">2</div><div><b>Czy potrzebna nowa strona</b><span>Powiem wprost: poprawka czy budowa od zera.</span></div></li>
                <li><div class="lpw-diagnosis-num">3</div><div><b>Jaki typ strony pasuje</b><span>Strona firmowa, one-pager, landing czy sklep B2B.</span></div></li>
                <li><div class="lpw-diagnosis-num">4</div><div><b>Realistyczny budżet i harmonogram</b><span>Konkretne widełki, terminy i zakres.</span></div></li>
                <li><div class="lpw-diagnosis-num">5</div><div><b>Czy ja jestem dobrym wyborem</b><span>Jeśli nie pasuję do projektu, powiem to wprost.</span></div></li>
            </ol>
        </div>
    </div>
</section>

<section class="lpw-section lpw-section-dark" id="realizacje">
    <div class="lpw-wrap">
        <header class="lpw-sec-head" data-animate>
            <div class="lpw-eyebrow lpw-eyebrow-light">Realizacje</div>
            <h2>Trzy strony, które dziś generują zapytania</h2>
            <p>Każdy z tych projektów zaczął się od tej samej 30-minutowej rozmowy.</p>
        </header>
        <div class="lpw-portfolio-grid">
            <?php if (!empty($lpw_portfolio)) : ?>
                <?php foreach ($lpw_portfolio as $port) : ?>
                    <article class="lpw-port" data-animate>
                        <div class="lpw-port-thumb">
                            <?php if (!empty($port["thumbnail"])) : ?>
                                <img src="<?php echo esc_url((string) $port["thumbnail"]); ?>" alt="<?php echo esc_attr((string) $port["title"]); ?>" loading="lazy" decoding="async" />
                            <?php else : ?>
                                <div class="lpw-port-thumb-placeholder">[ <?php echo esc_html((string) ($port["type"] ?? "Strona WWW")); ?> ]</div>
                            <?php endif; ?>
                        </div>
                        <div class="lpw-port-body">
                            <?php if (!empty($port["meta"])) : ?>
                                <div class="lpw-port-tag"><?php echo esc_html((string) $port["meta"]); ?></div>
                            <?php elseif (!empty($port["type"])) : ?>
                                <div class="lpw-port-tag"><?php echo esc_html((string) $port["type"]); ?></div>
                            <?php endif; ?>
                            <h3><?php echo esc_html((string) $port["title"]); ?></h3>
                            <?php if (!empty($port["badge"])) : ?>
                                <div class="lpw-port-result"><?php echo esc_html((string) $port["badge"]); ?></div>
                            <?php elseif (!empty($port["excerpt"])) : ?>
                                <div class="lpw-port-result"><?php echo esc_html(wp_trim_words((string) $port["excerpt"], 14)); ?></div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else : ?>
                <article class="lpw-port" data-animate><div class="lpw-port-thumb"><div class="lpw-port-thumb-placeholder">[ Producent maszyn B2B ]</div></div><div class="lpw-port-body"><div class="lpw-port-tag">Strona firmowa B2B</div><h3>Producent maszyn — przebudowa strony oferty</h3><div class="lpw-port-result">+475% leadów / mc · CPL spadł z 380 zł na 145 zł</div></div></article>
                <article class="lpw-port" data-animate data-delay="1"><div class="lpw-port-thumb"><div class="lpw-port-thumb-placeholder">[ SaaS Logistyka ]</div></div><div class="lpw-port-body"><div class="lpw-port-tag">Landing page pod kampanie</div><h3>SaaS dla logistyki — landing pod demo</h3><div class="lpw-port-result">Konwersja 0,4% → 2,1% · 80% leadów z firm 50+ pracowników</div></div></article>
                <article class="lpw-port" data-animate data-delay="2"><div class="lpw-port-thumb"><div class="lpw-port-thumb-placeholder">[ Consulting B2B ]</div></div><div class="lpw-port-body"><div class="lpw-port-tag">Strona firmowa + landing</div><h3>Consulting B2B — pełen rebranding sprzedażowy</h3><div class="lpw-port-result">320 tys. zł → 720 tys. zł rocznie · przewidywalny pipeline</div></div></article>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="lpw-section">
    <div class="lpw-wrap">
        <header class="lpw-sec-head" data-animate>
            <h2>Cztery kroki. Bez zaskoczeń.</h2>
            <p>Tu masz dokładny scenariusz tego co dzieje się od pierwszej rozmowy do uruchomienia gotowej strony.</p>
        </header>
        <div class="lpw-process-grid">
            <div class="lpw-step" data-animate><h3>Diagnoza i strategia</h3><p>Analiza strony i konkurencji, mapa sekcji, propozycja wartości.</p><span class="lpw-step-when">Tydzień 1</span></div>
            <div class="lpw-step" data-animate data-delay="1"><h3>Copywriting i projekt</h3><p>Piszę treści, projektuję układ mobile-first, pokazuję całość przed kodowaniem.</p><span class="lpw-step-when">Tydzień 2-3</span></div>
            <div class="lpw-step" data-animate data-delay="2"><h3>Wdrożenie</h3><p>WordPress, Core Web Vitals, SEO, formularze, tracking, RWD.</p><span class="lpw-step-when">Tydzień 3-5</span></div>
            <div class="lpw-step" data-animate data-delay="3"><h3>Uruchomienie</h3><p>Instruktaż edycji, analityka, monitoring i 30 dni wsparcia.</p><span class="lpw-step-when">Tydzień 5-6</span></div>
        </div>
    </div>
</section>

<section class="lpw-section lpw-section-soft" id="o-mnie">
    <div class="lpw-wrap" data-animate>
        <div class="lpw-author">
            <div class="lpw-author-photo" aria-hidden="true">
                <?php
                if (function_exists("upsellio_render_home_media_image")) {
                    $author_img = upsellio_render_home_media_image("about_portrait", [
                        "size" => "medium",
                        "loading" => "lazy",
                    ]);
                    if ($author_img !== "") echo $author_img;
                    else echo '<div class="lpw-author-photo-placeholder">[ Sebastian Kelm ]</div>';
                } else {
                    echo '<div class="lpw-author-photo-placeholder">[ Sebastian Kelm ]</div>';
                }
                ?>
            </div>
            <div>
                <span class="lpw-author-tag">Z kim rozmawiasz</span>
                <h3>Sebastian Kelm</h3>
                <p>Strony projektuję z perspektywy sprzedaży B2B: najpierw komunikat i konwersja, potem estetyka. Dlatego układ i copy piszę tak, żeby klient wiedział dlaczego ma zostawić zapytanie.</p>
                <div class="lpw-author-stats">
                    <div><b>10 lat</b><span>sprzedaży B2B</span></div>
                    <div><b>500 tys.</b><span>zł/mies. własny sklep B2B</span></div>
                    <div><b>WordPress</b><span>od 8 000 zł, edytowalne</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="lpw-section" id="faq">
    <div class="lpw-wrap">
        <header class="lpw-sec-head" data-animate>
            <h2>Najczęstsze pytania o strony B2B</h2>
            <p>Jeśli masz inne — napisz w formularzu, odpowiem osobiście.</p>
        </header>
        <div class="lpw-faq" data-animate>
            <?php foreach ($lpw_faq_items as $item) : ?>
                <details class="lpw-faq-item">
                    <summary>
                        <span><?php echo esc_html((string) $item["question"]); ?></span>
                        <span class="lpw-faq-icon" aria-hidden="true">+</span>
                    </summary>
                    <p><?php echo esc_html((string) $item["answer"]); ?></p>
                </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="lpw-final" id="formularz-stopka">
    <div class="lpw-wrap">
        <div class="lpw-final-box">
            <h2>Strona, która pracuje na sprzedaż 24/7. Albo zostaje wizytówką.</h2>
            <p>Wypełnij formularz, opisz w 2 zdaniach co masz lub czego potrzebujesz. Odezwę się w 24h z terminami.</p>
            <div class="lpw-final-form-wrap">
                <div class="lpw-form-card">
                    <span class="lpw-form-card-tag">Formularz</span>
                    <h2>Umów bezpłatną 30-min rozmowę</h2>
                    <p class="lpw-form-card-sub">Zapis od razu w CRM — ten sam przepływ co formularz u góry strony.</p>
                    <?php
                    if (function_exists("upsellio_render_lead_form")) {
                        echo upsellio_render_lead_form([
                            "origin" => "landing-www-form",
                            "submit_label" => "Umów bezpłatną diagnozę →",
                            "variant" => "full",
                            "heading" => "",
                            "subheading" => "",
                            "redirect_url" => $current_page_url . "#formularz-stopka",
                            "service_options" => $lpw_service_options,
                            "message_placeholder" => "Np. Mamy stronę z 2020, ruch organiczny rośnie ale bounce 70% i zero leadów albo startujemy nową firmę usługową i potrzebujemy strony z formularzem i blogiem.",
                            "css_class" => "lpw-form",
                            "form_id" => "landing-www-form-stopka",
                        ]);
                    } else {
                        echo '<p><a href="mailto:' . esc_attr($contact_email) . '" class="lpw-final-btn" style="display:inline-flex;">Napisz — ' . esc_html($contact_email) . "</a></p>";
                    }
                    ?>
                </div>
            </div>
            <p class="lpw-final-note">
                Albo zadzwoń: <a href="tel:<?php echo esc_attr($contact_phone_href); ?>"><?php echo esc_html($contact_phone); ?></a>
                · <a href="#formularz" style="color:rgba(255,255,255,.55);">Formularz na górze</a>
            </p>
        </div>
    </div>
</section>
</main>

<div class="lpw-mobile-cta">
    <a href="#formularz-stopka">Umów bezpłatną diagnozę →</a>
</div>

<script type="application/ld+json">
<?php
echo wp_json_encode([
    "@context" => "https://schema.org",
    "@type" => "Service",
    "serviceType" => "Tworzenie stron internetowych dla firm B2B",
    "name" => "Strony internetowe B2B pod konwersję — Upsellio",
    "provider" => [
        "@type" => "LocalBusiness",
        "name" => "Upsellio — Marketing B2B",
        "founder" => ["@type" => "Person", "name" => "Sebastian Kelm"],
        "telephone" => $contact_phone,
        "email" => $contact_email,
    ],
    "description" => "Projektowanie i wdrażanie stron internetowych dla firm B2B. Strony firmowe od 8 000 zł, landing pages od 4 000 zł, sklepy B2B od 12 000 zł. Bezpłatna 30-min diagnoza.",
    "areaServed" => "Polska",
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
</script>

<?php if (!empty($lpw_faq_items)) : ?>
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
    }, $lpw_faq_items),
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
    ["landing-www-form", "landing-www-form-stopka"].forEach(function (fid) {
        var form = document.getElementById(fid);
        if (form) {
            var msg = form.querySelector('textarea[name="lead_message"]');
            if (msg) {
                msg.setAttribute("placeholder", "Np. Mamy stronę z 2020, ruch organiczny rośnie ale bounce 70% i zero leadów albo startujemy nową firmę usługową i potrzebujemy strony z formularzem i blogiem.");
            }
        }
    });
})();
</script>

<?php get_footer(); ?>
