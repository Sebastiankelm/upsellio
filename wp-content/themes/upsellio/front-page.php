<?php
/*
Template Name: Upsellio - Strona Glowna v2 (jezyk klienta)
Template Post Type: page
*/
if (!defined("ABSPATH")) {
    exit;
}

$cfg = function_exists("upsellio_get_front_page_content_config") ? upsellio_get_front_page_content_config() : [];
$seo = is_array($cfg["seo"] ?? null) ? $cfg["seo"] : [];

$contact_phone = function_exists("upsellio_get_contact_phone") ? upsellio_get_contact_phone() : "+48 575 522 595";
$contact_phone_href = preg_replace("/\s+/", "", (string) $contact_phone);
$contact_email = trim((string) ($cfg["contact_email"] ?? "kontakt@upsellio.pl"));

$google_ads_url = function_exists("upsellio_get_google_ads_page_url") ? (string) upsellio_get_google_ads_page_url() : "";
$meta_ads_url = function_exists("upsellio_get_meta_ads_page_url") ? (string) upsellio_get_meta_ads_page_url() : "";
$websites_url = function_exists("upsellio_get_websites_page_url") ? (string) upsellio_get_websites_page_url() : "";
$marketing_portfolio_url = function_exists("upsellio_get_marketing_portfolio_page_url") ? (string) upsellio_get_marketing_portfolio_page_url() : "";

$hero_photo = function_exists("upsellio_render_home_media_image")
    ? upsellio_render_home_media_image("hero_portrait", [
        "class" => "home-hero-image",
        "size" => "medium_large",
        "sizes" => "(max-width: 980px) 92vw, 44vw",
        "loading" => "eager",
        "fetchpriority" => "high",
    ])
    : "";

$about_photo = function_exists("upsellio_render_home_media_image")
    ? upsellio_render_home_media_image("about_portrait", ["class" => "home-about-image", "size" => "large"])
    : "";

$lead_magnet = function_exists("upsellio_get_primary_lead_magnet") ? upsellio_get_primary_lead_magnet() : [];
$lead_magnet_title = (string) ($lead_magnet["title"] ?? "12 błędów blokujących leady B2B");
$lead_magnet_pdf_url = (string) ($lead_magnet["pdf_url"] ?? "");
$lead_magnet_bullets = is_array($lead_magnet["bullets"] ?? null) ? $lead_magnet["bullets"] : [
    "Komunikat reklamowy nie pasuje do strony",
    "Strona nie odpowiada na obiekcje klienta",
    "CTA są nieczytelne lub się dublują",
    "Formularz pyta o zbyt dużo lub zbyt mało",
    "Brak dowodu społecznego we właściwym miejscu",
    "Tracking konwersji nie odróżnia leada od wizyty",
];

$blog_query = new WP_Query([
    "post_type" => "post",
    "posts_per_page" => 3,
    "post_status" => "publish",
    "ignore_sticky_posts" => true,
]);

get_header();
?>

<style>
.hr-art *,
.hr-art *::before,
.hr-art *::after { box-sizing: border-box; }
.hr-art { font-family: "DM Sans", system-ui, sans-serif; color: #0d0d0b; background: #fafaf6; line-height: 1.65; }
.hr-wrap { width: min(1180px, 100% - 64px); margin-inline: auto; }
.hr-section { padding: 112px 0; }
.hr-section-soft { background: #f1f1ec; }
.hr-section-dark { background: #0a1410; color: #fff; position: relative; overflow: hidden; }
.hr-section-dark::before { content: ""; position: absolute; width: 560px; height: 560px; border-radius: 50%; background: radial-gradient(circle, rgba(20,184,166,.18), transparent 67%); right: -220px; top: -220px; pointer-events: none; }
.hr-section-dark .hr-wrap { position: relative; z-index: 2; }
.hr-divider { height: 1px; background: #e7e7e1; margin: 36px 0 56px; }
.hr-divider-light { background: rgba(255,255,255,.12); }
.hr-eyebrow { display: inline-flex; align-items: center; gap: 10px; font-size: 11px; font-weight: 700; letter-spacing: 1.6px; text-transform: uppercase; color: #0d9488; margin-bottom: 14px; }
.hr-eyebrow::before { content: ""; width: 26px; height: 2px; background: #0d9488; border-radius: 99px; }
.hr-eyebrow-light { color: #5eead4; }
.hr-eyebrow-light::before { background: #5eead4; }
.hr-eyebrow-warn { color: #f97316; }
.hr-eyebrow-warn::before { background: #f97316; }
.hr-h1 { font-family: "Bricolage Grotesque", "Syne", sans-serif; font-weight: 700; font-size: clamp(44px, 5.4vw, 72px); line-height: .98; letter-spacing: -2.4px; margin: 0 0 22px; }
.hr-h1 em { font-style: normal; color: #0d9488; }
.hr-h2 { font-family: "Bricolage Grotesque", "Syne", sans-serif; font-weight: 700; font-size: clamp(32px, 3.6vw, 50px); line-height: 1.04; letter-spacing: -1.6px; margin: 0 0 16px; max-width: 26ch; }
.hr-h2-light { color: #fff; }
.hr-h3 { font-family: "Bricolage Grotesque", "Syne", sans-serif; font-weight: 700; font-size: 21px; line-height: 1.18; letter-spacing: -.4px; margin: 0 0 10px; }
.hr-lead { font-size: 18px; line-height: 1.6; color: #3d3d38; max-width: 62ch; margin: 0; }
.hr-lead-light { color: rgba(255,255,255,.72); }
.hr-sec-head { max-width: 800px; }
.hr-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; border-radius: 999px; padding: 15px 24px; font-weight: 700; font-size: 15px; border: 1px solid transparent; transition: all .2s ease; text-decoration: none; cursor: pointer; font-family: inherit; }
.hr-btn-primary { background: #0d9488; color: #fff; box-shadow: 0 12px 28px rgba(13,148,136,.22); }
.hr-btn-primary:hover { background: #0f766e; transform: translateY(-1px); }
.hr-btn-ghost { background: #fff; border-color: #e7e7e1; color: #0a1410; }
.hr-btn-ghost:hover { border-color: #0d9488; color: #0d9488; }
.hr-btn-ghost-light { background: transparent; border-color: rgba(255,255,255,.24); color: #fff; }
.hr-btn-ghost-light:hover { border-color: #5eead4; color: #5eead4; }
.hr-btn-block { width: 100%; }
.hr-hero { padding: 88px 0 0; background: radial-gradient(circle at 88% 8%, rgba(13,148,136,.12), transparent 36%); }
.hr-hero-grid { display: grid; grid-template-columns: 1.05fr .95fr; gap: 64px; align-items: center; }
.hr-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 30px; }
.hr-micro { list-style: none; padding: 0; margin: 36px 0 0; display: grid; grid-template-columns: repeat(3,1fr); gap: 14px; }
.hr-micro li { display: flex; align-items: flex-start; gap: 8px; font-size: 13px; color: #3d3d38; }
.hr-check { flex: 0 0 22px; width: 22px; height: 22px; border-radius: 50%; display: grid; place-items: center; background: #ccfbf1; color: #0f766e; font-weight: 900; font-size: 11px; }
.hr-hero-side { position: relative; }
.hr-hero-photo { position: relative; aspect-ratio: .82; border-radius: 28px; overflow: hidden; background: #dff8f4; border: 1px solid #99f6e4; }
.hr-photo-stripes { position: absolute; inset: 0; background-image: repeating-linear-gradient(135deg, rgba(13,148,136,.12) 0 12px, transparent 12px 24px); }
.hr-photo-label { position: absolute; inset: 0; display: grid; place-items: center; font-family: ui-monospace, monospace; color: #0f766e; font-size: 13px; letter-spacing: 1px; }
.home-hero-image, .home-about-image { width: 100%; height: 100%; object-fit: cover; }
.hr-hero-stat { position: absolute; background: #fff; border: 1px solid #e7e7e1; border-radius: 18px; padding: 14px 18px; box-shadow: 0 12px 28px rgba(15,23,42,.06); }
.hr-hero-stat b { display: block; font-family: "Bricolage Grotesque", sans-serif; font-size: 24px; color: #0d9488; line-height: 1; }
.hr-hero-stat span { display: block; font-size: 12px; color: #7c7c74; margin-top: 3px; }
.hr-hero-stat-tl { left: -16px; top: 36px; }
.hr-hero-stat-br { right: -16px; bottom: 48px; }
.hr-proof { margin-top: 96px; display: grid; grid-template-columns: repeat(3,1fr); border-top: 1px solid #e7e7e1; border-bottom: 1px solid #e7e7e1; }
.hr-proof-cell { padding: 32px 24px; text-align: center; border-right: 1px solid #e7e7e1; }
.hr-proof-cell:last-child { border-right: 0; }
.hr-proof-cell b { display: block; font-family: "Bricolage Grotesque", sans-serif; font-size: 42px; color: #0d9488; letter-spacing: -1.4px; line-height: 1; font-weight: 700; }
.hr-proof-cell em { font-style: normal; display: block; font-size: 13px; color: #0a1410; font-weight: 700; margin-top: 6px; }
.hr-proof-cell span { display: block; color: #7c7c74; font-size: 13px; margin-top: 4px; max-width: 32ch; margin-inline: auto; }
.hr-diag { background: #fff; border: 1px solid #e7e7e1; border-radius: 24px; overflow: hidden; box-shadow: 0 18px 44px rgba(15,23,42,.05); }
.hr-diag table { width: 100%; border-collapse: collapse; }
.hr-diag thead th { background: #0a1410; color: #fff; text-align: left; padding: 18px 22px; font-size: 12px; letter-spacing: 1.2px; text-transform: uppercase; font-weight: 700; }
.hr-diag td { padding: 18px 22px; vertical-align: top; border-top: 1px solid #ededea; font-size: 15px; color: #3d3d38; }
.hr-diag td:first-child { font-weight: 700; color: #0a1410; }
.hr-diag td:last-child { color: #0d9488; font-weight: 700; }
.hr-diag tbody tr:hover { background: #fafaf7; }
.hr-diag-cta { margin-top: 24px; background: #ccfbf1; border-left: 4px solid #0d9488; padding: 22px 26px; border-radius: 0 16px 16px 0; }
.hr-diag-cta p { margin: 0; color: #0a1410; font-size: 15px; }
.hr-cards { display: grid; grid-template-columns: repeat(3,1fr); gap: 18px; }
.hr-card { background: #fff; border: 1px solid #e7e7e1; border-radius: 20px; padding: 32px; transition: border-color .2s ease, transform .2s ease; }
.hr-card:hover { border-color: #0d9488; transform: translateY(-3px); }
.hr-card-num { font-family: ui-monospace, monospace; font-size: 12px; color: #0d9488; letter-spacing: 1.4px; margin-bottom: 24px; }
.hr-card-body { color: #3d3d38; font-size: 15px; line-height: 1.6; }
.hr-list { list-style: none; padding: 0; margin: 18px 0; display: grid; gap: 9px; }
.hr-list li { display: flex; gap: 8px; color: #3d3d38; font-size: 14px; }
.hr-list li::before { content: "✓"; color: #0d9488; font-weight: 900; }
.hr-list-2col { grid-template-columns: 1fr 1fr; gap: 9px 24px; }
.hr-card-link { display: inline-flex; color: #0d9488; font-weight: 700; font-size: 14px; text-decoration: none; margin-top: 8px; }
.hr-card-link:hover { color: #0f766e; }
.hr-compare { background: #fff; border: 1px solid #e7e7e1; border-radius: 24px; overflow: hidden; box-shadow: 0 18px 44px rgba(15,23,42,.05); }
.hr-compare table { width: 100%; border-collapse: collapse; }
.hr-compare th, .hr-compare td { padding: 18px 22px; text-align: left; font-size: 14px; vertical-align: middle; }
.hr-compare thead th { background: #fafaf6; color: #0a1410; font-weight: 700; font-size: 12px; letter-spacing: 1px; text-transform: uppercase; border-bottom: 1px solid #e7e7e1; }
.hr-compare thead th:nth-child(3) { background: #ccfbf1; color: #0f766e; }
.hr-compare tbody tr { border-top: 1px solid #ededea; }
.hr-compare tbody td:first-child { font-weight: 600; color: #0a1410; }
.hr-compare tbody td:nth-child(2) { color: #94918a; }
.hr-compare tbody td:nth-child(3) { color: #0f766e; font-weight: 700; background: rgba(204,251,241,.35); }
.hr-compare-mark { display: inline-flex; align-items: center; gap: 6px; }
.hr-cases { display: grid; grid-template-columns: repeat(3,1fr); gap: 18px; }
.hr-case { background: rgba(255,255,255,.045); border: 1px solid rgba(255,255,255,.12); border-radius: 20px; padding: 32px; transition: border-color .2s, background .2s; }
.hr-case:hover { border-color: rgba(94,234,212,.4); background: rgba(255,255,255,.07); }
.hr-case-tag { font-size: 11px; letter-spacing: 1.4px; text-transform: uppercase; color: rgba(255,255,255,.5); margin-bottom: 24px; }
.hr-case-pain { color: rgba(255,255,255,.7); font-size: 14px; line-height: 1.6; border-left: 2px solid rgba(94,234,212,.4); padding-left: 16px; margin: 0 0 22px; font-style: italic; }
.hr-case-num { font-family: "Bricolage Grotesque", sans-serif; font-size: 46px; color: #5eead4; letter-spacing: -1.6px; line-height: 1; font-weight: 700; }
.hr-case-label { color: #fff; margin: 14px 0 8px; font-size: 14px; font-weight: 700; }
.hr-case-action { color: rgba(255,255,255,.55); font-size: 13px; margin: 0 0 18px; }
.hr-case-action b { color: #fff; font-weight: 700; }
.hr-case a { color: #5eead4; font-weight: 700; font-size: 14px; text-decoration: none; }
.hr-process { list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: repeat(4,1fr); gap: 14px; }
.hr-step { background: #fff; border: 1px solid #e7e7e1; border-radius: 20px; padding: 28px; position: relative; }
.hr-step-num { width: 38px; height: 38px; border-radius: 50%; background: #ccfbf1; color: #0f766e; font-family: "Bricolage Grotesque", sans-serif; font-weight: 800; display: grid; place-items: center; margin-bottom: 18px; }
.hr-step-when { display: inline-block; margin-top: 12px; padding: 4px 10px; background: #fafaf6; border-radius: 99px; font-size: 11px; color: #7c7c74; font-weight: 700; letter-spacing: .5px; }
.hr-step p { color: #3d3d38; font-size: 14px; margin: 0; }
.hr-fit { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-top: 40px; }
.hr-fit-block { background: #fff; border: 1px solid #e7e7e1; border-radius: 20px; padding: 32px; }
.hr-fit-block.is-bad { background: #fafaf6; }
.hr-fit-tag { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 99px; font-size: 11px; font-weight: 800; letter-spacing: 1.2px; text-transform: uppercase; margin-bottom: 20px; }
.hr-fit-block .hr-fit-tag { background: #ccfbf1; color: #0f766e; }
.hr-fit-block.is-bad .hr-fit-tag { background: #fef3c7; color: #92400e; }
.hr-fit-list { list-style: none; padding: 0; margin: 0; display: grid; gap: 10px; }
.hr-fit-list li { display: flex; gap: 10px; color: #3d3d38; font-size: 14px; line-height: 1.55; }
.hr-fit-block .hr-fit-list li::before { content: "✓"; color: #0d9488; font-weight: 900; flex-shrink: 0; }
.hr-fit-block.is-bad .hr-fit-list li::before { content: "—"; color: #94918a; font-weight: 900; flex-shrink: 0; }
.hr-leadbox { background: #fff; border: 1px solid #e7e7e1; border-radius: 28px; padding: 48px; display: grid; grid-template-columns: .85fr 1.15fr 1fr; gap: 42px; align-items: center; box-shadow: 0 24px 60px rgba(15,23,42,.06); }
.hr-leadbox-cover { display: grid; place-items: center; }
.hr-book { width: 200px; aspect-ratio: .72; background: linear-gradient(165deg, #0f766e 0%, #0a1410 100%); border-radius: 6px 14px 14px 6px; padding: 24px 22px; color: #fff; display: flex; flex-direction: column; justify-content: space-between; box-shadow: -6px 6px 16px rgba(0,0,0,.15); }
.hr-book-tag { font-size: 10px; letter-spacing: 1.4px; text-transform: uppercase; color: #5eead4; }
.hr-book-title { font-family: "Bricolage Grotesque", sans-serif; font-size: 22px; line-height: 1.05; letter-spacing: -.6px; font-weight: 700; }
.hr-book-foot { font-size: 11px; letter-spacing: 1.2px; text-transform: uppercase; color: rgba(255,255,255,.5); }
.hr-leadbox-form { display: grid; gap: 10px; }
.hr-leadbox-form label { font-size: 12px; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; color: #7c7c74; display: block; }
.hr-leadbox-form input[type=text], .hr-leadbox-form input[type=email] { display: block; width: 100%; border: 1.5px solid #e7e7e1; background: #fafaf7; border-radius: 12px; padding: 12px 14px; margin-top: 6px; font: inherit; outline: none; transition: border-color .15s, box-shadow .15s; }
.hr-leadbox-form input:focus { border-color: #0d9488; box-shadow: 0 0 0 3px rgba(13,148,136,.13); }
.hr-consent { display: flex !important; gap: 8px; align-items: flex-start; text-transform: none !important; letter-spacing: 0 !important; font-size: 12px !important; color: #7c7c74 !important; font-weight: 400 !important; line-height: 1.5; margin-top: 6px; }
.hr-fineprint { font-size: 12px; color: #7c7c74; margin: 0; text-align: center; }
.hr-split { display: grid; grid-template-columns: .85fr 1.15fr; gap: 64px; align-items: center; }
.hr-about-photo { position: relative; aspect-ratio: .85; border-radius: 28px; overflow: hidden; background: #ccfbf1; border: 1px solid #99f6e4; }
.hr-mini-grid { display: grid; grid-template-columns: repeat(2,1fr); gap: 12px; margin: 32px 0; }
.hr-mini-grid > div { background: #fff; border: 1px solid #e7e7e1; border-radius: 16px; padding: 18px; }
.hr-mini-grid b { display: block; font-family: "Bricolage Grotesque", sans-serif; font-size: 28px; color: #0d9488; line-height: 1; font-weight: 700; }
.hr-mini-grid em { font-style: normal; display: block; font-size: 13px; color: #0a1410; margin-top: 6px; font-weight: 700; }
.hr-mini-grid span { display: block; font-size: 12px; color: #7c7c74; margin-top: 2px; }
.hr-faq { display: grid; gap: 10px; max-width: 880px; }
.hr-faq-item { background: #fff; border: 1px solid #e7e7e1; border-radius: 16px; overflow: hidden; transition: border-color .2s; }
.hr-faq-item[open] { border-color: #0d9488; }
.hr-faq-item summary { list-style: none; cursor: pointer; display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 22px 26px; font-family: "Bricolage Grotesque", sans-serif; font-size: 17px; font-weight: 700; color: #0a1410; }
.hr-faq-item summary::-webkit-details-marker { display: none; }
.hr-faq-icon { width: 28px; height: 28px; border-radius: 50%; background: #f1f1ec; display: grid; place-items: center; font-size: 18px; color: #475569; flex: 0 0 28px; transition: transform .2s, background .2s, color .2s; }
.hr-faq-item[open] .hr-faq-icon { transform: rotate(45deg); background: #ccfbf1; color: #0f766e; }
.hr-faq-item p { margin: 0; padding: 0 26px 22px; color: #3d3d38; font-size: 15px; line-height: 1.65; }
.hr-blog-grid { display: grid; gap: 18px; grid-template-columns: repeat(3,1fr); }
.hr-post { background: #fff; border: 1px solid #e7e7e1; border-radius: 20px; overflow: hidden; transition: transform .2s, border-color .2s; }
.hr-post:hover { transform: translateY(-3px); border-color: #0d9488; }
.hr-thumb { height: 180px; background: #e7f8f5; }
.hr-thumb img { width: 100%; height: 100%; object-fit: cover; }
.hr-post-body { padding: 20px; }
.hr-post-body small { font-size: 12px; color: #7c7c74; }
.hr-post-body h3 { margin: 8px 0 12px; font-family: "Bricolage Grotesque", sans-serif; font-size: 20px; line-height: 1.2; }
.hr-post-body a { font-size: 14px; color: #0d9488; font-weight: 700; text-decoration: none; }
.hr-cta-band { background: #0a1410; color: #fff; padding: 80px 0; position: relative; overflow: hidden; }
.hr-cta-band::before { content: ""; position: absolute; width: 600px; height: 600px; border-radius: 50%; background: radial-gradient(circle, rgba(20,184,166,.2), transparent 65%); left: -200px; bottom: -300px; pointer-events: none; }
.hr-cta-inner { position: relative; display: grid; grid-template-columns: 1.4fr 1fr; gap: 48px; align-items: center; }
.hr-cta-actions { display: flex; gap: 12px; flex-wrap: wrap; justify-content: flex-end; }
.hr-contact-shell { background: #fff; border: 1px solid #e7e7e1; border-radius: 28px; padding: clamp(24px, 4vw, 44px); box-shadow: 0 18px 44px rgba(15,23,42,.06); }
.hr-contact-head { max-width: 780px; margin: 0 auto 24px; text-align: center; }
.hr-contact-head .hr-h2 { margin-inline: auto; }
.hr-contact-form { max-width: 920px; margin: 0 auto; }
.hr-contact-grid { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 14px; }
.hr-contact-field { display: grid; gap: 6px; }
.hr-contact-field.full { grid-column: 1 / -1; }
.hr-contact-field label { font-size: 12px; font-weight: 700; color: #3d3d38; }
.hr-contact-field input, .hr-contact-field textarea, .hr-contact-field select { width: 100%; border: 1.5px solid #e7e7e1; border-radius: 12px; min-height: 46px; padding: 12px 14px; font: inherit; color: #0a1410; background: #fff; outline: none; transition: border-color .18s ease, box-shadow .18s ease; }
.hr-contact-field textarea { min-height: 130px; resize: vertical; line-height: 1.6; }
.hr-contact-field input:focus, .hr-contact-field textarea:focus, .hr-contact-field select:focus { border-color: #0d9488; box-shadow: 0 0 0 3px rgba(13,148,136,.13); }
.hr-contact-consent { display: flex !important; gap: 8px; align-items: flex-start; font-size: 13px; color: #3d3d38; line-height: 1.5; }
.hr-contact-consent input { margin-top: 3px; width: auto; min-height: auto; accent-color: #0d9488; }
.hr-contact-submit { width: 100%; justify-content: center; margin-top: 10px; }
.hr-contact-note { margin-top: 10px; color: #7c7c74; font-size: 12px; text-align: center; }
.hr-contact-note a { color: #0d9488; font-weight: 700; }
.form-feedback { display: none; margin-top: 14px; padding: 14px 18px; border-radius: 12px; font-size: 14px; font-weight: 600; }
.form-feedback.is-success { display: block; background: #ccfbf1; color: #115e59; }
.form-feedback.is-error { display: block; background: #fee2e2; color: #991b1b; }
[data-animate] { opacity: 0; transform: translateY(20px); transition: opacity .8s ease, transform .8s ease; }
[data-animate="fade-up"] { transform: translateY(20px); }
[data-animate="scale"] { transform: scale(.96); }
[data-animate].is-visible { opacity: 1; transform: translateY(0) scale(1); }
[data-delay="1"] { transition-delay: .12s; }
[data-delay="2"] { transition-delay: .24s; }
[data-delay="3"] { transition-delay: .36s; }
@media (prefers-reduced-motion: reduce) { [data-animate] { opacity: 1; transform: none; transition: none; } }
@media (max-width: 1060px) { .hr-hero-grid,.hr-split,.hr-cta-inner,.hr-leadbox { grid-template-columns: 1fr; } .hr-cards,.hr-cases,.hr-process,.hr-blog-grid { grid-template-columns: 1fr 1fr; } .hr-fit { grid-template-columns: 1fr; } .hr-proof { grid-template-columns: 1fr; } .hr-cta-actions { justify-content: flex-start; } .hr-section { padding: 80px 0; } }
@media (max-width: 760px) { .hr-wrap { width: min(1180px, 100% - 32px); } .hr-cards,.hr-cases,.hr-process,.hr-blog-grid,.hr-micro,.hr-list-2col,.hr-mini-grid,.hr-contact-grid { grid-template-columns: 1fr; } .hr-proof-cell { border-right: 0; border-bottom: 1px solid #e7e7e1; } .hr-proof-cell:last-child { border-bottom: 0; } .hr-leadbox { padding: 28px; gap: 28px; } .hr-section { padding: 64px 0; } .hr-diag { overflow-x: auto; } .hr-diag table { min-width: 560px; } .hr-compare { overflow-x: auto; } .hr-compare table { min-width: 560px; } .hr-faq-item summary { padding: 18px 20px; font-size: 15px; } .hr-faq-item p { padding: 0 20px 18px; } }
</style>

<div class="hr-art">
  <section class="hr-hero">
    <div class="hr-wrap hr-hero-grid">
      <div class="hr-hero-copy">
        <div class="hr-eyebrow" data-animate="fade-up">Marketing B2B · Google Ads · Meta Ads · Strony WWW</div>
        <h1 class="hr-h1" data-animate="fade-up" data-delay="1">Wydajesz na reklamy,<br>a sprzedaż <em>stoi w miejscu?</em></h1>
        <p class="hr-lead" data-animate="fade-up" data-delay="2">Większość firm B2B traci 60-80% leadów między kliknięciem a podpisaniem umowy. Nie na reklamie. Na stronie, w ofercie, w follow-upie. Sprawdzę gdzie konkretnie tracisz pieniądze — i pokażę co naprawić najpierw, bez prezentacji i bez agencyjnego bełkotu.</p>
        <div class="hr-actions" data-animate="fade-up" data-delay="3">
          <a class="hr-btn hr-btn-primary" href="#kontakt">Umów bezpłatną diagnozę →</a>
          <a class="hr-btn hr-btn-ghost" href="#wyniki">Zobacz wyniki klientów</a>
        </div>
      </div>
      <aside class="hr-hero-side" data-animate="scale" data-delay="2">
        <div class="hr-hero-photo" aria-hidden="true">
          <?php echo $hero_photo !== "" ? $hero_photo : '<div class="hr-photo-stripes"></div><div class="hr-photo-label">[ portrait — Sebastian Kelm ]</div>'; ?>
        </div>
      </aside>
    </div>

    <div class="hr-wrap hr-proof" id="wyniki" data-animate="fade-up">
      <div class="hr-proof-cell"><b>10 lat</b><em>sprzedaży B2B jako handlowiec</em><span>dlatego wiem które leady handlowiec zignoruje</span></div>
      <div class="hr-proof-cell"><b>1,5 mln zł/mc</b><em>regularnej sprzedaży B2B</em><span>dlatego rozumiem matematykę cyklu sprzedaży</span></div>
      <div class="hr-proof-cell"><b>500 tys. zł/mc</b><em>ze sklepu B2B z marżą 4× wyższą</em><span>dlatego wiem jak strona zamienia ruch w marżę</span></div>
    </div>
  </section>

  <section class="hr-section hr-section-soft">
    <div class="hr-wrap">
      <header class="hr-sec-head" data-animate="fade-up">
        <div class="hr-eyebrow hr-eyebrow-warn">Co naprawdę się dzieje</div>
        <h2 class="hr-h2">Lead ogląda Twoją stronę 30 sekund i wychodzi. Powtórz to 1000 razy w miesiącu.</h2>
        <p class="hr-lead">Tak wygląda 80% budżetu reklamowego firm B2B w Polsce. Ruch jest. Kliknięcia są. Telefon milczy, formularz milczy, handlowcy narzekają na jakość. Problem prawie nigdy nie jest tam, gdzie go szukasz.</p>
      </header>
      <div class="hr-divider"></div>
      <div class="hr-diag" data-animate="fade-up">
        <table>
          <thead><tr><th>Co widzisz na co dzień</th><th>Prawdopodobna przyczyna</th><th>Co sprawdzam najpierw</th></tr></thead>
          <tbody>
            <tr><td>Dużo kliknięć, mało zapytań</td><td>Strona nie konwertuje albo CTA jest słabe</td><td>Heatmapy, lejek GA4, testy CTA</td></tr>
            <tr><td>Zapytania są, ale niekwalifikowane</td><td>Zły targeting albo niejasna oferta</td><td>Grupy odbiorców, komunikat, formularz</td></tr>
            <tr><td>CPL rośnie, kampania jest droga</td><td>Słaba struktura albo niska jakość reklam</td><td>Quality Score, Ad Relevance, landing match</td></tr>
            <tr><td>Reklamy działały, teraz przestały</td><td>Nasycenie grupy, zmiana algorytmu, sezon</td><td>Frequency, nowe kreacje, ekspansja</td></tr>
            <tr><td>Handlowcy mówią „leady słabej jakości”</td><td>Strona przyciąga niewłaściwą intencję</td><td>Dopasowanie keyword → landing → oferta</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <section class="hr-section">
    <div class="hr-wrap">
      <header class="hr-sec-head" data-animate="fade-up">
        <div class="hr-eyebrow">Co konkretnie robię</div>
        <h2 class="hr-h2">Trzy filary, które pracują razem — bo osobno nie działają.</h2>
      </header>
      <div class="hr-divider"></div>
      <div class="hr-cards">
        <article class="hr-card" data-animate="fade-up"><div class="hr-card-num">01 / GOOGLE ADS</div><h3 class="hr-h3">Klient szuka dostawcy w Google? Bądź pierwszą firmą, którą zobaczy.</h3><p class="hr-card-body">Google Ads w B2B to nie licytacja fraz. To łapanie intencji zakupowej.</p><?php if ($google_ads_url !== "") : ?><a class="hr-card-link" href="<?php echo esc_url($google_ads_url); ?>">Zobacz szczegóły →</a><?php endif; ?></article>
        <article class="hr-card" data-animate="fade-up" data-delay="1"><div class="hr-card-num">02 / META ADS</div><h3 class="hr-h3">Decydent jeszcze nie szuka. Ale już podejmuje decyzje.</h3><p class="hr-card-body">Meta Ads buduje obecność u decydentów, zanim zaczną szukać.</p><?php if ($meta_ads_url !== "") : ?><a class="hr-card-link" href="<?php echo esc_url($meta_ads_url); ?>">Zobacz szczegóły →</a><?php endif; ?></article>
        <article class="hr-card" data-animate="fade-up" data-delay="2"><div class="hr-card-num">03 / STRONY WWW</div><h3 class="hr-h3">Strona to nie wizytówka. To handlowiec 24/7.</h3><p class="hr-card-body">Różnicę robi struktura przekonywania i dopasowanie do intencji.</p><?php if ($websites_url !== "") : ?><a class="hr-card-link" href="<?php echo esc_url($websites_url); ?>">Zobacz szczegóły →</a><?php endif; ?></article>
      </div>
    </div>
  </section>

  <section class="hr-section hr-section-dark">
    <div class="hr-wrap">
      <header class="hr-sec-head"><div class="hr-eyebrow hr-eyebrow-light">Wyniki</div><h2 class="hr-h2 hr-h2-light">Trzy realne wdrożenia.</h2></header>
      <div class="hr-divider hr-divider-light"></div>
      <div class="hr-cases">
        <article class="hr-case"><div class="hr-case-tag">Producent maszyn B2B</div><div class="hr-case-num">23</div><div class="hr-case-label">leadów/mc po 90 dniach</div><?php if ($marketing_portfolio_url !== "") : ?><a href="<?php echo esc_url($marketing_portfolio_url); ?>">Zobacz pełny case →</a><?php endif; ?></article>
        <article class="hr-case"><div class="hr-case-tag">SaaS dla logistyki</div><div class="hr-case-num">2,1%</div><div class="hr-case-label">konwersji po 60 dniach</div><?php if ($marketing_portfolio_url !== "") : ?><a href="<?php echo esc_url($marketing_portfolio_url); ?>">Zobacz pełny case →</a><?php endif; ?></article>
        <article class="hr-case"><div class="hr-case-tag">Consulting B2B</div><div class="hr-case-num">720k</div><div class="hr-case-label">zł rocznie po 6 miesiącach</div><a href="#kontakt">Chcę podobny wynik →</a></article>
      </div>
    </div>
  </section>

  <section class="hr-section hr-section-soft" id="kontakt">
    <div class="hr-wrap">
      <div class="hr-contact-shell">
        <div class="hr-contact-head"><div class="hr-eyebrow">Wystarczą 2 zdania</div><h2 class="hr-h2">Opisz w 2 zdaniach co sprzedajesz i gdzie najmocniej swędzi.</h2></div>
        <form class="hr-contact-form" method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" data-upsellio-lead-form="1" data-upsellio-server-form="1">
          <input type="hidden" name="action" value="upsellio_submit_lead" />
          <input type="hidden" name="redirect_url" value="<?php echo esc_url(home_url("/#kontakt")); ?>" />
          <input type="hidden" name="lead_form_origin" value="home-contact-form" />
          <input type="hidden" name="lead_source" value="home-contact-form" />
          <input type="hidden" name="lead_service" value="Bezpłatna diagnoza marketingu" />
          <input type="hidden" name="utm_source" data-ups-utm="source" value="" />
          <input type="hidden" name="utm_medium" data-ups-utm="medium" value="" />
          <input type="hidden" name="utm_campaign" data-ups-utm="campaign" value="" />
          <input type="hidden" name="landing_url" data-ups-context="landing" value="" />
          <input type="hidden" name="referrer" data-ups-context="referrer" value="" />
          <input type="text" name="lead_website" value="" tabindex="-1" autocomplete="off"
            style="position:absolute;left:-9999px;opacity:0;height:0;width:0;" aria-hidden="true" />
          <?php wp_nonce_field("upsellio_unified_lead_form", "upsellio_lead_form_nonce"); ?>
          <div class="hr-contact-grid">
            <div class="hr-contact-field"><label for="hr-contact-name">Imię i firma *</label><input id="hr-contact-name" name="lead_name" type="text" required /></div>
            <div class="hr-contact-field"><label for="hr-contact-email">E-mail służbowy *</label><input id="hr-contact-email" name="lead_email" type="email" required /></div>
            <div class="hr-contact-field full"><label for="hr-contact-message">Co dokładnie dziś nie działa? *</label><textarea id="hr-contact-message" name="lead_message" required placeholder="Np. Robimy 12 demo / mc z reklam, 1-2 zamykają się umową. Podejrzewam że strona albo follow-up zabija leady gdzieś w drodze."></textarea></div>
            <div class="hr-contact-field full"><label class="hr-contact-consent"><input type="checkbox" name="lead_consent" value="1" required /><span>Wyrażam zgodę na kontakt w sprawie mojego zapytania.</span></label></div>
          </div>
          <button class="hr-btn hr-btn-primary hr-contact-submit" type="submit">Wyślij — odpiszę w 24h →</button>
        </form>
      </div>
    </div>
  </section>
</div>

<?php get_footer(); ?>
