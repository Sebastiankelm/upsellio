<?php
/*
Template Name: Upsellio - Oferta
Template Post Type: page
*/
if (!defined("ABSPATH")) {
    exit;
}

add_filter("pre_get_document_title", static function ($title) {
    return is_page_template("page-oferta.php") ? "Google Ads, Meta Ads i strony WWW dla firm | Upsellio" : $title;
});

add_action("wp_head", static function () {
    if (!is_page_template("page-oferta.php")) {
        return;
    }
    echo '<meta name="description" content="Kampanie Google Ads, Meta Ads i tworzenie stron internetowych nastawionych na leady i sprzedaż. Bezpłatna diagnoza marketingu — bez zobowiązań.">' . "\n";
    echo '<meta property="og:title" content="Google Ads, Meta Ads i strony WWW dla firm | Upsellio">' . "\n";
    echo '<meta property="og:description" content="Oferta Upsellio: kampanie Google Ads, Meta Ads i strony internetowe dla firm nastawione na leady, sprzedaż i konwersję.">' . "\n";
    echo '<meta property="og:type" content="website">' . "\n";
    $offer_url = function_exists("upsellio_get_offer_page_url") ? (string) upsellio_get_offer_page_url() : "";
    if ($offer_url !== "") {
        echo '<meta property="og:url" content="' . esc_url($offer_url) . '">' . "\n";
        echo '<link rel="canonical" href="' . esc_url($offer_url) . '">' . "\n";
    }
    $og_image = function_exists("upsellio_get_default_og_image_url") ? upsellio_get_default_og_image_url() : "";
    if ($og_image !== "") {
        echo '<meta property="og:image" content="' . esc_url($og_image) . '">' . "\n";
        echo '<meta name="twitter:image" content="' . esc_url($og_image) . '">' . "\n";
    }
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
}, 1);

get_header();

$front_page_sections = function_exists("upsellio_get_front_page_content_config")
    ? upsellio_get_front_page_content_config()
    : [];
$contact_service_options = isset($front_page_sections["contact_service_options"]) && is_array($front_page_sections["contact_service_options"])
    ? $front_page_sections["contact_service_options"]
    : [];
$contact_phone = function_exists("upsellio_get_contact_phone")
    ? upsellio_get_contact_phone()
    : trim((string) ($front_page_sections["contact_phone"] ?? "+48 575 522 595"));
$contact_email = trim((string) ($front_page_sections["contact_email"] ?? "kontakt@upsellio.pl"));
$contact_email_href = function_exists("upsellio_get_mailto_href") ? upsellio_get_mailto_href($contact_email) : ("mailto:" . $contact_email);
$contact_email_display = function_exists("upsellio_obfuscate_email_address") ? upsellio_obfuscate_email_address($contact_email) : $contact_email;
$offer_page_url = function_exists("upsellio_get_offer_page_url") ? (string) upsellio_get_offer_page_url() : "";
$google_ads_url = function_exists("upsellio_get_google_ads_page_url") ? (string) upsellio_get_google_ads_page_url() : "";
$meta_ads_url = function_exists("upsellio_get_meta_ads_page_url") ? (string) upsellio_get_meta_ads_page_url() : "";
$websites_url = function_exists("upsellio_get_websites_page_url") ? (string) upsellio_get_websites_page_url() : "";
$offer_founder = function_exists("upsellio_get_trust_seo_section") ? upsellio_get_trust_seo_section("founder") : [];
$offer_founder_name = (string) ($offer_founder["name"] ?? "Sebastian Kelm");
$offer_founder_role = (string) ($offer_founder["role"] ?? "Growth marketer B2B");
$offer_founder_photo = (string) ($offer_founder["photo_url"] ?? "");
$offer_founder_initials = function_exists("upsellio_get_initials_from_text") ? upsellio_get_initials_from_text($offer_founder_name) : "SK";
$offer_faq_items = [
    [
        "question" => "Czy najpierw powinienem zrobić stronę, czy reklamy?",
        "answer" => "To zależy. Jeśli obecna strona nie tłumaczy, czym się zajmujesz, nie ma jasnych CTA i nie buduje zaufania, reklamy tylko szybciej pokażą ten problem. Jeśli strona jest klarowna, możemy zacząć od kampanii reklamowych i testować ruch.",
    ],
    [
        "question" => "Czy mogę zacząć tylko od jednej usługi?",
        "answer" => "Tak. Możesz zacząć od Google Ads, Meta Ads albo od nowej strony internetowej, w zależności od tego, który element blokuje pozyskiwanie klientów najbardziej.",
    ],
    [
        "question" => "Czy pracujesz tylko przy reklamach, czy też pomagasz z komunikatem i treścią strony?",
        "answer" => "Pracuję szerzej niż sama konfiguracja kampanii. Pomagam z komunikatem oferty, strukturą strony i landing page'y, copywritingiem sprzedażowym, sekcjami zaufania, widocznością CTA oraz ścieżką klienta od reklamy do kontaktu.",
    ],
    [
        "question" => "Dla jakich firm jest ta oferta?",
        "answer" => "Oferta jest skierowana głównie do firm usługowych B2B, firm lokalnych szukających więcej zapytań, producentów i dystrybutorów, marek e-commerce oraz nowych firm, które chcą zbudować skuteczną obecność marketingową od podstaw.",
    ],
    [
        "question" => "Jak szybko widać pierwsze efekty?",
        "answer" => "Kampanie Google Ads i Meta Ads mogą zacząć generować pierwsze zapytania w ciągu 2-4 tygodni od uruchomienia, jeśli strona konwertuje i budżet jest odpowiedni do konkurencji. Stabilne wyniki zwykle pojawiają się po 2-3 miesiącach systematycznej optymalizacji.",
    ],
];
?>

<style>
  .offer-page {
    --offer-bg: #f8fafc;
    --offer-surface: #fff;
    --offer-soft: #f1f5f9;
    --offer-text: #071426;
    --offer-text-2: #334155;
    --offer-muted: #64748b;
    --offer-border: #e2e8f0;
    --offer-green: #0d9488;
    --offer-green-dark: #0f766e;
    --offer-green-soft: #ecfeff;
    --offer-green-line: #99f6e4;
    --offer-indigo: #0d9488;
    --offer-indigo-dark: #0f766e;
    --offer-indigo-soft: #ccfbf1;
    --offer-indigo-line: #99f6e4;
    --offer-amber: #0d9488;
    --offer-amber-dark: #0f766e;
    --offer-amber-soft: #ccfbf1;
    --offer-amber-line: #99f6e4;
    --offer-navy: #081827;
    --offer-dark: #081827;
    --offer-shadow: 0 24px 70px rgba(15, 23, 42, 0.12);
    --offer-shadow-soft: 0 14px 40px rgba(15, 23, 42, 0.08);
    background: var(--offer-bg);
    color: var(--offer-text);
  }
  .offer-page .offer-section { padding: clamp(70px, 8vw, 110px) 0; }
  .offer-page .offer-section-border { border-bottom: 1px solid var(--offer-border); }
  .offer-page .offer-soft { background: var(--offer-soft); }
  .offer-page .offer-content { width: min(860px, 100%); }
  .offer-page .offer-eyebrow { display: inline-flex; align-items: center; gap: 10px; margin-bottom: 18px; font-size: 12px; font-weight: 800; letter-spacing: 1.6px; text-transform: uppercase; color: var(--offer-green); }
  .offer-page .offer-eyebrow::before { content: ""; width: 26px; height: 2px; background: var(--offer-green); border-radius: 99px; }
  .offer-page .offer-h1,
  .offer-page .offer-h2,
  .offer-page .offer-h3 { font-family: var(--font-display); line-height: 1.05; letter-spacing: -1.3px; color: var(--offer-text); }
  .offer-page .offer-h1 { font-size: clamp(42px, 6vw, 76px); max-width: 980px; }
  .offer-page .offer-h2 { font-size: clamp(32px, 4vw, 52px); max-width: 900px; }
  .offer-page .offer-h3 { font-size: clamp(24px, 3vw, 34px); }
  .offer-page p { color: var(--offer-text-2); }
  .offer-page .offer-lead { margin-top: 24px; max-width: 780px; font-size: clamp(18px, 2vw, 21px); line-height: 1.75; }
  .offer-page .offer-body { margin-top: 18px; max-width: 860px; font-size: 15px; line-height: 1.82; }
  .offer-page .offer-btn-row { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 32px; }
  .offer-page .offer-btn { min-height: 50px; display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; padding: 0 24px; font-size: 15px; font-weight: 800; transition: .2s ease; text-decoration: none; }
  .offer-page .offer-btn-primary { background: linear-gradient(135deg, #0d9488, #14b8a6); color: #fff; box-shadow: 0 14px 28px rgba(13, 148, 136, .24); }
  .offer-page .offer-btn-secondary { background: #fff; border: 1px solid #cbd5e1; color: var(--offer-text); }
  .offer-page .offer-btn-ghost { background: var(--offer-green-soft); color: var(--offer-green-dark); border: 1px solid var(--offer-green-line); }
  .offer-page .offer-btn:hover { transform: translateY(-2px); }
  .offer-anchor-bar { position: sticky; top: 82px; z-index: 70; background: rgba(255, 255, 255, .94); border-bottom: 1px solid var(--offer-border); box-shadow: 0 8px 20px rgba(16, 18, 15, .04); backdrop-filter: blur(12px); }
  .offer-anchor-inner { min-height: 58px; display: flex; align-items: center; justify-content: space-between; gap: 18px; overflow-x: auto; scrollbar-width: none; }
  .offer-anchor-inner::-webkit-scrollbar { display: none; }
  .offer-anchor-links { display: flex; align-items: center; gap: 10px; white-space: nowrap; }
  .offer-anchor-links a { display: inline-flex; align-items: center; min-height: 36px; padding: 0 14px; border: 1px solid var(--offer-border); border-radius: 999px; font-size: 13px; font-weight: 700; color: var(--offer-text-2); background: #f8fafc; transition: .2s ease; }
  .offer-anchor-links a:hover { color: var(--offer-green-dark); border-color: var(--offer-green-line); background: var(--offer-green-soft); }
  .offer-anchor-cta { flex: 0 0 auto; display: inline-flex; align-items: center; min-height: 38px; padding: 0 16px; border-radius: 999px; background: var(--offer-green); color: #fff; font-size: 13px; font-weight: 800; white-space: nowrap; }
  .offer-hero { padding: clamp(72px, 8vw, 120px) 0; border-bottom: 1px solid var(--offer-border); position: relative; overflow: hidden; background: radial-gradient(circle at right top, rgba(20, 184, 166, .14), transparent 34%), var(--offer-bg); }
  .offer-hero-grid { display:grid; gap:36px; align-items:center; }
  .offer-hero-copy { min-width:0; }
  .offer-hero-funnel { background:#fff; border:1px solid var(--offer-border); border-radius:28px; padding:26px; box-shadow:var(--offer-shadow-soft); }
  .offer-hero-funnel-title { font-size:12px; font-weight:800; letter-spacing:.16em; text-transform:uppercase; color:var(--offer-green-dark); margin-bottom:14px; }
  .offer-hero-funnel-svg { width:100%; height:auto; display:block; }
  .offer-hero-funnel-legend { margin-top:14px; display:grid; grid-template-columns:repeat(3,1fr); gap:8px; font-size:12px; color:var(--offer-muted); text-align:center; }
  .offer-hero-funnel-legend span { padding:4px; }
  .offer-decision { margin-top: 36px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; max-width: 980px; }
  .offer-decision-card { display:flex; gap:12px; align-items:flex-start; padding: 18px; border: 1px solid var(--offer-border); border-radius: 20px; background: #fff; box-shadow: var(--offer-shadow-soft); transition: .2s ease; }
  .offer-decision-card:hover { transform: translateY(-3px); border-color: var(--offer-green-line); }
  .offer-decision-card .offer-decision-icon { flex:0 0 36px; width:36px; height:36px; display:grid; place-items:center; border-radius:10px; background:var(--offer-green-soft); color:var(--offer-green-dark); }
  .offer-decision-card .offer-decision-icon svg { width:18px; height:18px; }
  .offer-decision-card.is-meta .offer-decision-icon { background:var(--offer-indigo-soft); color:var(--offer-indigo-dark); }
  .offer-decision-card.is-www .offer-decision-icon { background:var(--offer-amber-soft); color:var(--offer-amber-dark); }
  .offer-decision-card strong { display: block; margin-bottom: 6px; font-size: 15px; color: var(--offer-text); }
  .offer-decision-card span { display: block; font-size: 13px; color: var(--offer-muted); line-height: 1.5; }
  @media(min-width:1024px){ .offer-hero-grid { grid-template-columns: 1.15fr .85fr; } }
  .offer-grid { margin-top: 42px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 22px; }
  .offer-card { position: relative; background: var(--offer-surface); border: 1px solid var(--offer-border); border-radius: 28px; padding: 32px; box-shadow: var(--offer-shadow-soft); overflow: hidden; transition: .22s ease; }
  .offer-card:hover { transform: translateY(-5px); border-color: var(--offer-green-line); box-shadow: var(--offer-shadow); }
  .offer-card::after { content: ""; position: absolute; right: -60px; top: -60px; width: 150px; height: 150px; background: rgba(20, 184, 166, .1); border-radius: 50%; }
  .offer-card.is-google { border-top:4px solid var(--offer-green); }
  .offer-card.is-meta { border-top:4px solid var(--offer-indigo); }
  .offer-card.is-meta::after { background: rgba(79,70,229,.10); }
  .offer-card.is-www { border-top:4px solid var(--offer-amber); }
  .offer-card.is-www::after { background: rgba(217,119,6,.10); }
  .offer-icon { width: 52px; height: 52px; display: grid; place-items: center; margin-bottom: 22px; border-radius: 16px; background: var(--offer-green-soft); color: var(--offer-green-dark); }
  .offer-icon svg { width:26px; height:26px; }
  .offer-card.is-meta .offer-icon { background: var(--offer-indigo-soft); color: var(--offer-indigo-dark); }
  .offer-card.is-www .offer-icon { background: var(--offer-amber-soft); color: var(--offer-amber-dark); }
  .offer-card.is-meta .offer-link { color: var(--offer-indigo-dark); }
  .offer-card.is-www .offer-link { color: var(--offer-amber-dark); }
  .offer-card.is-meta .offer-list li::before { color: var(--offer-indigo); }
  .offer-card.is-www .offer-list li::before { color: var(--offer-amber); }
  .offer-card .offer-h3 { margin-bottom: 14px; }
  .offer-card p { margin-bottom: 20px; font-size: 15px; }
  .offer-list { list-style: none; display: grid; gap: 9px; margin: 20px 0 26px; padding: 0; }
  .offer-list li { position: relative; padding-left: 24px; font-size: 14px; color: var(--offer-text-2); }
  .offer-list li::before { content: "✓"; position: absolute; left: 0; color: var(--offer-green); font-weight: 900; }
  .offer-link { display: inline-flex; align-items: center; gap: 8px; font-weight: 800; color: var(--offer-green-dark); }
  .offer-rich-copy { margin-top: 24px; padding-top: 22px; border-top: 1px solid var(--offer-border); display: grid; gap: 14px; }
  .offer-rich-copy p { margin: 0; font-size: 14px; line-height: 1.75; }
  .offer-mid-cta { margin-top: 34px; padding: 28px; border: 1px solid var(--offer-green-line); border-radius: 26px; background: linear-gradient(135deg, #ecfeff, #fff); display: grid; grid-template-columns: 1fr auto; align-items: center; gap: 20px; box-shadow: var(--offer-shadow-soft); }
  .offer-mid-cta strong { display: block; font-family: var(--font-display); font-size: clamp(24px, 3vw, 36px); line-height: 1.05; letter-spacing: -1px; margin-bottom: 8px; color: var(--offer-text); }
  .offer-mid-cta p { font-size: 15px; max-width: 700px; }
  .offer-compare-grid { margin-top: 38px; display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
  .offer-compare-item { padding: 24px; background: #f8fafc; border: 1px solid var(--offer-border); border-radius: 20px; }
  .offer-compare-item strong { display: block; margin-bottom: 8px; font-size: 17px; color: var(--offer-text); }
  .offer-compare-item p { font-size: 14px; }
  .offer-chooser-grid { margin-top: 38px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
  .offer-chooser-card { padding: 28px; border: 1px solid var(--offer-border); border-radius: 26px; background: #fff; box-shadow: var(--offer-shadow-soft); border-top:4px solid var(--offer-green); }
  .offer-chooser-card.is-meta { border-top-color: var(--offer-indigo); }
  .offer-chooser-card.is-www { border-top-color: var(--offer-amber); }
  .offer-chooser-card .tag { display: inline-flex; margin-bottom: 18px; padding: 6px 12px; border-radius: 999px; background: var(--offer-green-soft); color: var(--offer-green-dark); font-size: 12px; font-weight: 800; }
  .offer-chooser-card.is-meta .tag { background: var(--offer-indigo-soft); color: var(--offer-indigo-dark); }
  .offer-chooser-card.is-www .tag { background: var(--offer-amber-soft); color: var(--offer-amber-dark); }
  .offer-chooser-card.is-meta .offer-link { color: var(--offer-indigo-dark); }
  .offer-chooser-card.is-www .offer-link { color: var(--offer-amber-dark); }
  .offer-chooser-card h3 { font-size: 26px; margin-bottom: 12px; font-family: var(--font-display); }
  .offer-chooser-card p { font-size: 15px; margin-bottom: 20px; }
  .offer-dark-box { background: radial-gradient(circle at right top, rgba(20, 184, 166, .22), transparent 35%), linear-gradient(145deg, #081827, #0f172a); color: #fff; border-radius: 32px; padding: clamp(34px, 5vw, 56px); display: grid; grid-template-columns: .95fr 1.05fr; gap: 36px; align-items: center; box-shadow: var(--offer-shadow); }
  .offer-dark-box p, .offer-dark-box .offer-lead { color: rgba(255, 255, 255, .72); }
  .offer-dark-box .offer-h2 { color: #fff; }
  .offer-dark-box .offer-eyebrow { color: #8ff0ca; }
  .offer-dark-box .offer-eyebrow::before { background: #8ff0ca; }
  .offer-steps { position:relative; display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
  .offer-steps::before { content:""; position:absolute; left:38px; right:38px; top:38px; height:2px; background:linear-gradient(90deg, rgba(143,240,202,.0), rgba(143,240,202,.4), rgba(143,240,202,.0)); pointer-events:none; }
  .offer-step { padding: 22px; border: 1px solid rgba(255, 255, 255, .12); border-radius: 20px; background: rgba(255, 255, 255, .06); position:relative; }
  .offer-step-head { display:flex; align-items:center; gap:10px; margin-bottom:14px; }
  .offer-step b { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%; background: rgba(143, 240, 202, .14); color: #8ff0ca; font-family: var(--font-display); font-weight:800; }
  .offer-step-icon { display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; border-radius:8px; background: rgba(255,255,255,.06); color:#8ff0ca; }
  .offer-step-icon svg { width:18px; height:18px; }
  .offer-step strong { display: block; margin-bottom: 6px; color: #fff; }
  .offer-step p { font-size: 14px; }
  .offer-faq-grid { margin-top: 38px; display: grid; gap: 14px; max-width: 900px; }
  .offer-page details.offer-faq-item { border: 1px solid var(--offer-border); border-radius: 18px; background: #fff; padding: 20px 22px; box-shadow: var(--offer-shadow-soft); }
  .offer-page details.offer-faq-item summary { cursor: pointer; font-weight: 800; color: var(--offer-text); }
  .offer-page details.offer-faq-item p { margin-top: 12px; font-size: 15px; }
  .offer-form-shell { border: 1px solid #e7e7e1; border-radius: 28px; background: #fff; box-shadow: 0 18px 44px rgba(15,23,42,.06); padding: clamp(28px, 5vw, 52px); }
  .offer-form-host { display:flex; gap:16px; align-items:center; margin:0 auto 24px; padding:18px; max-width:560px; border:1px solid #e7e7e1; background:#fafaf7; border-radius:20px; }
  .offer-form-host-photo { width:64px; height:64px; border-radius:50%; object-fit:cover; flex:0 0 64px; }
  .offer-form-host-fallback { width:64px; height:64px; border-radius:50%; flex:0 0 64px; display:grid; place-items:center; background:linear-gradient(135deg,#0d9488,#14b8a6); color:#fff; font-family:var(--font-display); font-size:22px; font-weight:800; letter-spacing:.04em; }
  .offer-form-host-body { text-align:left; min-width:0; }
  .offer-form-host-body strong { display:block; color:var(--offer-text); font-size:15px; }
  .offer-form-host-body span { display:block; color:var(--offer-muted); font-size:12px; margin-top:2px; }
  .offer-form-host-body em { display:inline-flex; align-items:center; gap:6px; margin-top:6px; padding:2px 10px; border-radius:999px; background:var(--offer-green-soft); color:var(--offer-green-dark); font-style:normal; font-size:11px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; }
  .offer-form-head { text-align: center; max-width: 760px; margin: 0 auto 30px; }
  .offer-form-head .offer-h2 { margin: 0 auto; }
  .offer-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
  .offer-form-grid .field.full { grid-column: 1 / -1; }
  .offer-form-grid .field { display:grid; gap:6px; }
  .offer-form-grid label { font-size:12px; font-weight:700; color:var(--offer-text-2); }
  .offer-form-grid .input,
  .offer-form-grid .select,
  .offer-form-grid .textarea {
    width:100%;
    border:1.5px solid #e7e7e1;
    border-radius:12px;
    min-height:46px;
    padding:12px 14px;
    font:inherit;
    color:#0a1410;
    background:#fff;
    outline:none;
    transition:border-color .18s ease, box-shadow .18s ease;
  }
  .offer-form-grid .select { cursor:pointer; }
  .offer-form-grid .textarea { min-height:120px; resize:vertical; line-height:1.6; }
  .offer-form-grid .input:focus,
  .offer-form-grid .select:focus,
  .offer-form-grid .textarea:focus {
    border-color:#0d9488;
    box-shadow:0 0 0 3px rgba(13,148,136,.13);
  }
  .offer-consent-label { display:flex; gap:8px; align-items:flex-start; }
  .offer-consent-label input { margin-top:3px; }
  .field-error { display:none; font-size:12px; color:#b13a3a; }
  .offer-form-alert { margin-bottom:12px; padding:10px 12px; border-radius:10px; font-size:13px; }
  .offer-form-alert.is-success { border:1px solid #99f6e4; background:#ecfeff; color:#0f766e; }
  .offer-form-alert.is-error { border:1px solid #edcccc; background:#fff2f2; color:#b13a3a; }
  .offer-submit { width:100%; justify-content:center; margin-top:10px; }
  .offer-form-note { margin-top: 10px; color: var(--offer-muted); font-size: 12px; text-align: center; }
  .offer-form-note a,.offer-form-alt a { color:var(--teal); font-weight:700; text-decoration:none; }
  .offer-form-alt { margin-top: 18px; padding-top: 18px; border-top: 1px solid var(--offer-border); display: grid; gap: 8px; color: var(--offer-text-2); font-size: 13px; }
  @media (max-width: 980px) {
    .offer-grid, .offer-dark-box, .offer-chooser-grid { grid-template-columns: 1fr; }
    .offer-compare-grid, .offer-decision { grid-template-columns: repeat(2, 1fr); }
    .offer-mid-cta { grid-template-columns: 1fr; }
  }
  @media (max-width: 760px) {
    .offer-anchor-bar { top: 70px; }
    .offer-grid, .offer-compare-grid, .offer-steps, .offer-decision, .offer-form-grid { grid-template-columns: 1fr; }
    .offer-btn { width: 100%; }
    .offer-anchor-inner { padding: 10px 0; align-items: flex-start; }
    .offer-anchor-cta { display: none; }
  }
  /* Mobile-first UX correction layer */
  .offer-page .offer-section { padding:48px 0; }
  .offer-hero { padding:52px 0 46px; }
  .offer-page .offer-h1 { font-size:clamp(34px,10vw,40px); line-height:1.09; letter-spacing:-1px; }
  .offer-page .offer-h2 { font-size:clamp(28px,8vw,34px); line-height:1.12; letter-spacing:-.8px; }
  .offer-page .offer-h3 { font-size:clamp(21px,6vw,26px); line-height:1.16; letter-spacing:-.5px; }
  .offer-page .offer-lead { margin-top:16px; font-size:17px; line-height:1.65; }
  .offer-page .offer-body { margin-top:14px; line-height:1.72; }
  .offer-page .offer-btn-row { margin-top:22px; }
  .offer-anchor-bar { position:static; }
  .offer-anchor-inner { min-height:auto; padding:10px 0; }
  .offer-grid,.offer-compare-grid,.offer-steps,.offer-decision,.offer-form-grid,.offer-chooser-grid,.offer-dark-box { grid-template-columns:1fr; }
  .offer-card,.offer-compare-item,.offer-chooser-card,.offer-step,.offer-form-shell,.offer-mid-cta { padding:20px; border-radius:20px; }
  .offer-dark-box { padding:28px 20px; border-radius:24px; }
  .offer-mid-cta strong { font-size:clamp(22px,7vw,28px); line-height:1.12; }
  @media (min-width: 760px) {
    .offer-page .offer-section { padding:72px 0; }
    .offer-hero { padding:76px 0 68px; }
    .offer-anchor-bar { position:sticky; top:82px; }
    .offer-page .offer-h1 { font-size:clamp(44px,6vw,58px); line-height:1.05; }
    .offer-page .offer-h2 { font-size:clamp(34px,4vw,46px); }
    .offer-page .offer-h3 { font-size:clamp(23px,3vw,30px); }
    .offer-decision,.offer-compare-grid,.offer-steps { grid-template-columns:repeat(2,minmax(0,1fr)); }
  }
  @media (min-width: 1024px) {
    .offer-page .offer-section { padding:96px 0; }
    .offer-hero { padding:96px 0 88px; }
    .offer-page .offer-h1 { font-size:66px; }
    .offer-page .offer-h2 { font-size:50px; }
    .offer-grid,.offer-chooser-grid { grid-template-columns:repeat(3,minmax(0,1fr)); }
    .offer-compare-grid { grid-template-columns:repeat(4,minmax(0,1fr)); }
    .offer-dark-box { grid-template-columns:.95fr 1.05fr; }
    .offer-card,.offer-chooser-card { padding:26px; }
  }
</style>

<main class="offer-page">
  <div class="offer-anchor-bar" aria-label="Nawigacja po ofercie">
    <div class="wrap offer-anchor-inner">
      <div class="offer-anchor-links">
        <a href="#oferta">Oferta</a>
        <a href="#google-ads">Google Ads</a>
        <a href="#meta-ads">Meta Ads</a>
        <a href="#strony-www">Strony WWW</a>
        <a href="#wybor">Co wybrać?</a>
        <a href="#proces">Proces</a>
        <a href="#faq">FAQ</a>
        <a href="#formularz-oferta">Formularz</a>
      </div>
      <a href="#formularz-oferta" class="offer-anchor-cta">Chcę sprawdzić, co wybrać</a>
    </div>
  </div>

  <section class="offer-hero" id="start">
    <div class="wrap">
      <div class="offer-hero-grid">
        <div class="offer-hero-copy">
          <span class="offer-eyebrow">Oferta Upsellio</span>
          <h1 class="offer-h1">Google Ads, Meta Ads i strony internetowe dla firm, które chcą więcej klientów - nie tylko ruchu.</h1>
          <p class="offer-lead">Pomagam firmom B2B, usługowym i e-commerce poukładać marketing tak, żeby reklamy i strona internetowa nie działały osobno. Razem prowadzą odwiedzającego od pierwszego kliknięcia do decyzji zakupowej.</p>
          <p class="offer-body">Większość firm ma jeden z trzech problemów: albo nie docierają do wystarczającej liczby potencjalnych klientów, albo docierają do złej grupy, albo - co najczęstsze - mają ruch i budżet reklamowy, ale strona nie zamienia odwiedzających w kontakty i zapytania. W każdym z tych przypadków rozwiązanie leży nie w jednym narzędziu, lecz w systemie: spójnych kampaniach reklamowych, klarownej ofercie i stronie zoptymalizowanej pod konwersję.</p>
          <p class="offer-body">Moja oferta obejmuje trzy kluczowe obszary wpływające na pozyskiwanie klientów: kampanie Google Ads dla firm, kampanie Meta Ads na Facebooku i Instagramie oraz tworzenie stron internetowych i landing page'y zoptymalizowanych pod konwersję, wiarygodność i jasną ścieżkę do kontaktu.</p>
          <div class="offer-btn-row">
            <a href="#oferta" class="offer-btn offer-btn-primary">Zobacz pełną ofertę</a>
            <a href="#wybor" class="offer-btn offer-btn-secondary">Nie wiem, co wybrać</a>
            <a href="#formularz-oferta" class="offer-btn offer-btn-ghost">Chcę bezpłatną diagnozę</a>
          </div>
        </div>
        <aside class="offer-hero-funnel" aria-label="System marketingowy Upsellio">
          <div class="offer-hero-funnel-title">System pozyskiwania klientów</div>
          <svg class="offer-hero-funnel-svg" viewBox="0 0 320 240" xmlns="http://www.w3.org/2000/svg" role="img" aria-labelledby="offer-funnel-title">
            <title id="offer-funnel-title">Lejek Ruch -> Strona -> Lead -> Klient</title>
            <defs>
              <linearGradient id="offer-funnel-grad" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0%" stop-color="#0d9488" stop-opacity="0.18"/>
                <stop offset="100%" stop-color="#0d9488" stop-opacity="0.18"/>
              </linearGradient>
            </defs>
            <!-- Ruch: trzy źródła -->
            <g font-family="var(--font-display, sans-serif)" font-size="11" font-weight="700">
              <rect x="6" y="14" width="78" height="34" rx="9" fill="#ecfeff" stroke="#99f6e4"/>
              <text x="45" y="35" text-anchor="middle" fill="#0f766e">Google</text>
              <rect x="6" y="56" width="78" height="34" rx="9" fill="#ccfbf1" stroke="#99f6e4"/>
              <text x="45" y="77" text-anchor="middle" fill="#0f766e">Meta</text>
              <rect x="6" y="98" width="78" height="34" rx="9" fill="#ccfbf1" stroke="#99f6e4"/>
              <text x="45" y="119" text-anchor="middle" fill="#0f766e">SEO / direct</text>
            </g>
            <!-- Lejek -->
            <path d="M104 14 L260 14 L218 80 L218 150 L146 150 L146 80 Z" fill="url(#offer-funnel-grad)" stroke="#cbd5e1"/>
            <text x="182" y="50" text-anchor="middle" font-size="13" font-weight="800" fill="#081827">Strona</text>
            <text x="182" y="68" text-anchor="middle" font-size="11" fill="#64748b">konwertuje ruch</text>
            <text x="182" y="116" text-anchor="middle" font-size="13" font-weight="800" fill="#081827">Lead</text>
            <text x="182" y="134" text-anchor="middle" font-size="11" fill="#64748b">formularz / telefon</text>
            <!-- Strzałki ruchu -->
            <path d="M84 31 H102" stroke="#0d9488" stroke-width="2" fill="none" marker-end="url(#offer-arrow)"/>
            <path d="M84 73 H102" stroke="#0d9488" stroke-width="2" fill="none" marker-end="url(#offer-arrow)"/>
            <path d="M84 115 H102" stroke="#0d9488" stroke-width="2" fill="none" marker-end="url(#offer-arrow)"/>
            <defs>
              <marker id="offer-arrow" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="6" markerHeight="6" orient="auto">
                <path d="M0,0 L10,5 L0,10 z" fill="#94a3b8"/>
              </marker>
            </defs>
            <!-- Klient -->
            <rect x="120" y="170" width="124" height="46" rx="12" fill="#0f766e"/>
            <text x="182" y="194" text-anchor="middle" fill="#fff" font-size="13" font-weight="800">Klient</text>
            <text x="182" y="210" text-anchor="middle" fill="#a7f3d0" font-size="10">decyzja zakupowa</text>
            <path d="M182 150 V168" stroke="#0f766e" stroke-width="2" marker-end="url(#offer-arrow-dark)"/>
            <defs>
              <marker id="offer-arrow-dark" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="6" markerHeight="6" orient="auto">
                <path d="M0,0 L10,5 L0,10 z" fill="#0f766e"/>
              </marker>
            </defs>
          </svg>
          <div class="offer-hero-funnel-legend">
            <span><strong style="color:#0f766e;">Ruch</strong><br/>z reklam i SEO</span>
            <span><strong style="color:#081827;">Strona</strong><br/>konwertująca</span>
            <span><strong style="color:#0f766e;">Lead → klient</strong><br/>mierzalny wynik</span>
          </div>
        </aside>
      </div>
      <div class="offer-decision">
        <a href="#google-ads" class="offer-decision-card">
          <span class="offer-decision-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
          <span><strong>Mam popyt w Google</strong><span>Klienci już szukają usługi lub produktu.</span></span>
        </a>
        <a href="#meta-ads" class="offer-decision-card is-meta">
          <span class="offer-decision-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l18-5v12L3 14v-3z"/></svg></span>
          <span><strong>Chcę budować popyt</strong><span>Potrzebuję dotrzeć do nowych odbiorców.</span></span>
        </a>
        <a href="#strony-www" class="offer-decision-card is-www">
          <span class="offer-decision-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 17 9 11 13 15 21 7"/><polyline points="14 7 21 7 21 14"/></svg></span>
          <span><strong>Strona nie konwertuje</strong><span>Mam ruch, ale za mało zapytań.</span></span>
        </a>
      </div>
    </div>
  </section>

  <section class="offer-section offer-section-border" id="oferta">
    <div class="wrap">
      <span class="offer-eyebrow">Co robię</span>
      <h2 class="offer-h2">Trzy główne obszary, które wpływają na pozyskiwanie klientów.</h2>
      <div class="offer-grid">
        <article class="offer-card is-google" id="google-ads">
          <div class="offer-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></div>
          <h3 class="offer-h3">Google Ads</h3>
          <p>Docieraj do klientów w chwili, gdy aktywnie szukają Twojej usługi lub produktu.</p>
          <ul class="offer-list">
            <li>Audyt obecnych kampanii lub budowa struktury od zera</li>
            <li>Dobór słów kluczowych z wysoką intencją zakupową</li>
            <li>Optymalizacja stron docelowych pod konwersję</li>
            <li>Konfiguracja i weryfikacja śledzenia konwersji</li>
            <li>Bieżąca optymalizacja stawek, grup reklam i komunikatów</li>
            <li>Miesięczne raporty z danymi o kosztach, leadach i CPL</li>
          </ul>
          <?php if ($google_ads_url !== "") : ?><a href="<?php echo esc_url($google_ads_url); ?>" class="offer-link">Zobacz szczegóły Google Ads →</a><?php endif; ?>
          <div class="offer-rich-copy">
            <p>Google Ads trafia do osób z wysoką intencją zakupową - szukających konkretnej usługi lub produktu właśnie teraz. Skuteczna kampania to nie tylko uruchomienie reklam, ale precyzyjny dobór słów kluczowych, spójność przekazu z treścią strony docelowej, optymalizacja Quality Score oraz ciągłe śledzenie kosztu pozyskania leada.</p>
            <p>W ramach kampanii prowadzę reklamy Search i Performance Max, w zależności od tego, co przynosi lepsze wyniki dla danej firmy i branży. Każda kampania jest połączona z analizą jakości leadów, bo niska jakość zapytań, nawet przy niskim koszcie kliknięcia, nadal oznacza przepalony budżet.</p>
          </div>
        </article>

        <article class="offer-card is-meta" id="meta-ads">
          <div class="offer-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg></div>
          <h3 class="offer-h3">Kampanie Meta Ads</h3>
          <p>Buduj popyt i docieraj do nowych klientów, zanim zaczną szukać w Google.</p>
          <ul class="offer-list">
            <li>Analiza grupy docelowej i budowa lejka reklamowego</li>
            <li>Kampanie ToF, MoF i BoF dopasowane do celu</li>
            <li>Testy kreacji reklamowych i komunikatów</li>
            <li>Remarketing do odwiedzających stronę i zaangażowanych użytkowników</li>
            <li>Optymalizacja kosztów i jakości leadów</li>
            <li>Spójność przekazu reklama-strona docelowa</li>
          </ul>
          <?php if ($meta_ads_url !== "") : ?><a href="<?php echo esc_url($meta_ads_url); ?>" class="offer-link">Zobacz szczegóły Meta Ads →</a><?php endif; ?>
          <div class="offer-rich-copy">
            <p>Meta Ads - reklamy na Facebooku i Instagramie - pozwalają dotrzeć do potencjalnych klientów zanim zaczną aktywnie szukać Twojej usługi. Kampanie mogą wzbudzić zainteresowanie u precyzyjnie określonej grupy docelowej: według branży, stanowiska, zainteresowań, zachowań lub podobieństwa do obecnych klientów.</p>
            <p>Dobrze zaprojektowany lejek Meta Ads składa się z trzech poziomów: ToF buduje świadomość, MoF angażuje osoby po pierwszym kontakcie z marką, a BoF kieruje mocne oferty do osób gotowych podjąć decyzję. Wynik reklamy zależy nie tylko od kreacji, ale też od tego, co dzieje się po kliknięciu.</p>
          </div>
        </article>

        <article class="offer-card is-www" id="strony-www">
          <div class="offer-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="14" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><circle cx="6" cy="6.5" r=".7" fill="currentColor"/><circle cx="8.5" cy="6.5" r=".7" fill="currentColor"/></svg></div>
          <h3 class="offer-h3">Tworzenie stron internetowych</h3>
          <p>Strona, która przekonuje, buduje zaufanie i zamienia ruch w zapytania.</p>
          <ul class="offer-list">
            <li>Analiza obecnej strony pod kątem konwersji i komunikatu</li>
            <li>Struktura treści wspierająca widoczność w Google</li>
            <li>Copywriting sprzedażowy: nagłówki, korzyści, CTA</li>
            <li>Sekcje zaufania: opinie, liczby, case studies, FAQ</li>
            <li>Integracja z kampaniami reklamowymi</li>
            <li>Konfiguracja analityki i śledzenia konwersji</li>
          </ul>
          <?php if ($websites_url !== "") : ?><a href="<?php echo esc_url($websites_url); ?>" class="offer-link">Zobacz szczegóły stron WWW →</a><?php endif; ?>
          <div class="offer-rich-copy">
            <p>Strona internetowa to centrum każdego systemu marketingowego. Nawet najlepsza kampania Google Ads czy Meta Ads nie przyniesie efektów, jeśli strona nie tłumaczy oferty jasno, nie buduje zaufania i nie prowadzi do konkretnego działania.</p>
            <p>Strona firmowa powinna w kilka sekund odpowiedzieć na pytania: czym się zajmujesz, dla kogo jesteś, co wyróżnia Cię od konkurencji i dlaczego warto skontaktować się właśnie teraz. Efektem współpracy jest strona lub landing page, który aktywnie pracuje na wyniki i obniża koszt pozyskania leada z reklam.</p>
          </div>
        </article>
      </div>
      <div class="offer-mid-cta">
        <div>
          <strong>Nie wiesz, która usługa ma największy sens teraz?</strong>
          <p>W wielu firmach problem nie jest w samych reklamach, tylko w stronie, ofercie lub ścieżce kontaktu. Sprawdzę, od czego zacząć, żeby nie przepalić budżetu.</p>
        </div>
        <a href="#formularz-oferta" class="offer-btn offer-btn-primary">Chcę diagnozę</a>
      </div>
    </div>
  </section>

  <section class="offer-section offer-soft offer-section-border" id="dla-kogo">
    <div class="wrap">
      <span class="offer-eyebrow">Dla kogo</span>
      <h2 class="offer-h2">Dla jakich firm jest ta oferta - i kiedy marketing naprawdę zaczyna działać?</h2>
      <p class="offer-body">Ta oferta jest dla firm, które chcą pozyskiwać klientów przez internet w sposób przewidywalny i mierzalny - nie przez przypadek. Bez znaczenia, czy chodzi o firmę usługową B2B, producenta szukającego dystrybutorów, lokalny biznes chcący więcej zapytań ze swojego regionu, czy sklep online celujący w wyższy ROAS i niższy CPL.</p>
      <div class="offer-compare-grid">
        <div class="offer-compare-item"><strong>Masz stronę, ale nie masz zapytań</strong><p>Strona wygląda poprawnie, ale nie tłumaczy wartości i nie prowadzi klienta do kontaktu.</p></div>
        <div class="offer-compare-item"><strong>Reklamy klikają, ale nie sprzedają</strong><p>Budżet jest wydawany, raporty są, ale wartościowych leadów jest zbyt mało.</p></div>
        <div class="offer-compare-item"><strong>Nie wiesz, gdzie jest problem</strong><p>Sprawdzam kampanię, stronę, ofertę, formularze i proces obsługi zapytań.</p></div>
        <div class="offer-compare-item"><strong>Marketing i sprzedaż nie są spójne</strong><p>Reklama mówi jedno, strona drugie, a handlowiec słyszy pytania bez odpowiedzi.</p></div>
      </div>
      <p class="offer-body">Jeśli rozpoznajesz się w którymkolwiek z tych punktów, zaczynamy od diagnozy, żeby nie wdrażać działań, które rozwiążą zły problem.</p>
    </div>
  </section>

  <section class="offer-section offer-section-border" id="wybor">
    <div class="wrap">
      <span class="offer-eyebrow">Szybki wybór</span>
      <h2 class="offer-h2">Google Ads, Meta Ads czy nowa strona internetowa? Jak wybrać właściwy punkt startowy?</h2>
      <p class="offer-body">To pytanie pojawia się w niemal każdej rozmowie wstępnej. Odpowiedź zależy od jednej kluczowej kwestii: czy problem jest po stronie ruchu, czy konwersji?</p>
      <div class="offer-chooser-grid">
        <div class="offer-chooser-card"><span class="tag">Wybierz Google Ads</span><h3>Gdy klient już szuka</h3><p>Jeśli potencjalni klienci aktywnie wpisują w Google frazy związane z Twoją usługą lub produktem, reklamy Search są szybkim sposobem na przechwycenie istniejącego popytu.</p><?php if ($google_ads_url !== "") : ?><a href="<?php echo esc_url($google_ads_url); ?>" class="offer-link">Przejdź do Google Ads →</a><?php endif; ?></div>
        <div class="offer-chooser-card is-meta"><span class="tag">Wybierz Meta Ads</span><h3>Gdy trzeba zbudować uwagę</h3><p>Jeśli klienci nie szukają aktywnie w Google, ale można ich zainteresować problemem, efektem lub propozycją wartości, Meta Ads pomaga budować świadomość i remarketing.</p><?php if ($meta_ads_url !== "") : ?><a href="<?php echo esc_url($meta_ads_url); ?>" class="offer-link">Przejdź do Meta Ads →</a><?php endif; ?></div>
        <div class="offer-chooser-card is-www"><span class="tag">Wybierz stronę WWW</span><h3>Gdy ruch nie zamienia się w leady</h3><p>Jeśli masz już ruch z SEO, kampanii, poleceń lub social media, ale za mało osób zostawia kontakt, priorytetem jest strona i jej komunikat.</p><?php if ($websites_url !== "") : ?><a href="<?php echo esc_url($websites_url); ?>" class="offer-link">Przejdź do stron WWW →</a><?php endif; ?></div>
      </div>
      <div class="offer-mid-cta">
        <div>
          <strong>Najczęściej najlepszy wynik daje połączenie kilku elementów.</strong>
          <p>Strona konwertuje, Google Ads przechwytuje popyt, Meta Ads buduje świadomość i zasila remarketing. Zacząć można od jednego elementu - tego, który blokuje wzrost najbardziej.</p>
        </div>
        <a href="#formularz-oferta" class="offer-btn offer-btn-primary">Sprawdźmy Twój przypadek</a>
      </div>
    </div>
  </section>

  <section class="offer-section" id="proces">
    <div class="wrap">
      <div class="offer-dark-box">
        <div>
          <span class="offer-eyebrow">Jak pracuję</span>
          <h2 class="offer-h2">Jak wygląda współpraca - od pierwszej rozmowy do mierzalnych wyników?</h2>
          <p class="offer-lead">Nie zaczynam od działania, zaczynam od diagnozy. Zanim uruchomię kampanię, zoptymalizuję stronę lub przygotuję strukturę reklam, najpierw rozumiem, gdzie faktycznie blokuje się pozyskiwanie klientów w Twojej firmie.</p>
          <p class="offer-body">Wiele firm traci budżet reklamowy nie dlatego, że reklamy są złe, ale dlatego, że strona nie konwertuje, oferta nie jest zrozumiała albo CTA jest ukryte i nieskuteczne. Diagnoza pozwala znaleźć właściwy problem - a dopiero potem wdrożyć właściwe rozwiązanie.</p>
          <div class="offer-btn-row"><a href="#formularz-oferta" class="offer-btn offer-btn-primary">Chcę zacząć od diagnozy</a></div>
        </div>
        <div class="offer-steps">
          <div class="offer-step">
            <div class="offer-step-head">
              <b>1</b>
              <span class="offer-step-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
            </div>
            <strong>Analiza</strong>
            <p>Sprawdzam stronę, obecne kampanie, ofertę, komunikat i ścieżkę klienta od pierwszego kontaktu do zapytania lub zakupu.</p>
          </div>
          <div class="offer-step">
            <div class="offer-step-head">
              <b>2</b>
              <span class="offer-step-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M7 12h10M11 18h2"/></svg></span>
            </div>
            <strong>Strategia</strong>
            <p>Ustalam priorytety: Google Ads, Meta Ads, strona albo cała ścieżka. Określam cele, KPI i sposób mierzenia efektów.</p>
          </div>
          <div class="offer-step">
            <div class="offer-step-head">
              <b>3</b>
              <span class="offer-step-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg></span>
            </div>
            <strong>Wdrożenie</strong>
            <p>Tworzę strukturę kampanii, komunikaty reklamowe, landing pages, CTA i elementy konwersji.</p>
          </div>
          <div class="offer-step">
            <div class="offer-step-head">
              <b>4</b>
              <span class="offer-step-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 17 9 11 13 15 21 7"/><polyline points="14 7 21 7 21 14"/></svg></span>
            </div>
            <strong>Optymalizacja</strong>
            <p>Regularnie analizuję jakość leadów, koszt pozyskania, konwersję strony i ROAS, a potem optymalizuję kampanie i stronę iteracyjnie.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="offer-section offer-soft offer-section-border" id="faq">
    <div class="wrap">
      <span class="offer-eyebrow">FAQ</span>
      <h2 class="offer-h2">Najczęstsze pytania przed rozpoczęciem współpracy - odpowiedzi bez owijania w bawełnę.</h2>
      <div class="offer-faq-grid">
        <details class="offer-faq-item"><summary>Czy najpierw powinienem zrobić stronę, czy reklamy?</summary><p>To zależy. Jeśli obecna strona nie tłumaczy, czym się zajmujesz, nie ma jasnych CTA i nie buduje zaufania, reklamy tylko szybciej pokażą ten problem. Każde kliknięcie w reklamę, które trafia na słabą stronę, to zmarnowany budżet. Jeśli strona jest klarowna, ma dobrą strukturę i wiarygodny komunikat, możemy zacząć od kampanii reklamowych i testować ruch.</p></details>
        <details class="offer-faq-item"><summary>Czy mogę zacząć tylko od jednej usługi?</summary><p>Tak. Możesz zacząć od Google Ads, Meta Ads albo od nowej strony internetowej - w zależności od tego, który element blokuje pozyskiwanie klientów najbardziej. Wiele współprac zaczyna się od jednego obszaru, a naturalnie rozszerza się w miarę jak pojawiają się wyniki.</p></details>
        <details class="offer-faq-item"><summary>Czy pracujesz tylko przy reklamach, czy też pomagasz z komunikatem i treścią strony?</summary><p>Pracuję szerzej niż sama konfiguracja kampanii. Pomagam z komunikatem oferty, strukturą strony i landing page'y, copywritingiem sprzedażowym, sekcjami zaufania, widocznością CTA oraz ścieżką klienta od reklamy do kontaktu.</p></details>
        <details class="offer-faq-item"><summary>Dla jakich firm jest ta oferta?</summary><p>Oferta jest skierowana głównie do firm usługowych B2B, firm lokalnych szukających więcej zapytań, producentów i dystrybutorów, marek e-commerce chcących poprawić ROAS i obniżyć CPL oraz nowych firm, które chcą zbudować skuteczną obecność marketingową od podstaw.</p></details>
        <details class="offer-faq-item"><summary>Jak szybko widać pierwsze efekty?</summary><p>Kampanie Google Ads i Meta Ads mogą zacząć generować pierwsze zapytania w ciągu 2-4 tygodni od uruchomienia, jeśli strona konwertuje i budżet jest odpowiedni do konkurencji. Stabilne wyniki zwykle pojawiają się po 2-3 miesiącach systematycznej optymalizacji. Praca nad stroną może poprawić konwersję już w pierwszych tygodniach po wdrożeniu zmian.</p></details>
      </div>
    </div>
  </section>

  <section class="offer-section" id="formularz-oferta">
    <div class="wrap">
      <div class="offer-form-shell">
        <div class="offer-form-host">
          <?php if ($offer_founder_photo !== "") : ?>
            <img class="offer-form-host-photo" src="<?php echo esc_url($offer_founder_photo); ?>" alt="<?php echo esc_attr($offer_founder_name); ?>" width="64" height="64" loading="lazy" decoding="async" />
          <?php else : ?>
            <span class="offer-form-host-fallback" aria-hidden="true"><?php echo esc_html($offer_founder_initials !== "" ? $offer_founder_initials : "SK"); ?></span>
          <?php endif; ?>
          <div class="offer-form-host-body">
            <strong><?php echo esc_html($offer_founder_name); ?></strong>
            <span><?php echo esc_html($offer_founder_role); ?> · Upsellio</span>
            <em>Odpowiadam w ciągu 24h</em>
          </div>
        </div>
        <div class="offer-form-head">
          <span class="offer-eyebrow">Bezpłatna diagnoza</span>
          <h2 class="offer-h2">Nie wiesz, od czego zacząć: Google Ads, Meta Ads czy nowa strona? Zacznijmy od diagnozy.</h2>
          <p class="offer-lead">Zanim zainwestujesz kolejny budżet w reklamy lub zlecisz nową stronę, warto wiedzieć, gdzie faktycznie tracisz potencjalnych klientów. Napisz, co sprzedajesz, do kogo kierujesz ofertę i co dzisiaj nie działa.</p>
          <p class="offer-body" style="margin-left:auto;margin-right:auto;">Bezpłatna diagnoza to 30-45 minut rozmowy lub analizy pisemnej, po której wiesz: co hamuje wyniki, który kanał ma największy potencjał w Twoim przypadku i od czego zacząć, żeby nie przepalić budżetu.</p>
        </div>

        <?php
        $offer_service_choices = array_values(array_filter(array_unique(array_merge(
            ["Google Ads", "Meta Ads", "Tworzenie strony internetowej", "Nie wiem, co wybrać"],
            is_array($contact_service_options) ? $contact_service_options : []
        )), static function ($item) {
            return trim((string) $item) !== "";
        }));
        echo upsellio_render_lead_form([
            "origin" => "offer-page-form",
            "variant" => "full",
            "submit_label" => "Wyślij zapytanie →",
            "redirect_url" => $offer_page_url !== "" ? $offer_page_url . "#formularz-oferta" : home_url("/oferta/#formularz-oferta"),
            "service_options" => $offer_service_choices,
            "form_id" => "contact-form",
            "submit_button_id" => "submit-btn",
        ]);
        ?>
          <p class="offer-form-note">Dane z formularza służą wyłącznie do kontaktu i przygotowania rekomendacji. Możesz też napisać bezpośrednio: <a href="<?php echo esc_url($contact_email_href); ?>"><?php echo esc_html($contact_email_display); ?></a></p>

        <div class="offer-form-alt">
          <div>✓ Bez presji sprzedażowej i bez ogólnych porad.</div>
          <div>✓ Jeśli po rozmowie zdecydujemy się na współpracę - świetnie. Jeśli nie - zostajesz z konkretną wskazówką.</div>
          <div>✓ Kontakt telefoniczny: <a href="<?php echo esc_url("tel:" . preg_replace("/\s+/", "", $contact_phone)); ?>"><?php echo esc_html($contact_phone); ?></a></div>
        </div>
      </div>
    </div>
  </section>
</main>

<?php
if (function_exists("upsellio_render_faq_schema")) {
    upsellio_render_faq_schema($offer_faq_items);
}
if (function_exists("upsellio_render_service_schema")) {
    upsellio_render_service_schema(
        "Google Ads, Meta Ads i strony internetowe dla firm",
        "Oferta Upsellio obejmuje kampanie Google Ads, kampanie Meta Ads oraz strony internetowe nastawione na leady, sprzedaż i konwersję.",
        "/oferta/",
        "Marketing internetowy i tworzenie stron"
    );
}
get_footer();
?>
