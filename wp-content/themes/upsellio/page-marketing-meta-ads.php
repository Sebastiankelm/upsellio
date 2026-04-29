<?php
/*
Template Name: Upsellio - Meta Ads
Template Post Type: page
*/
if (!defined("ABSPATH")) {
    exit;
}

add_filter("pre_get_document_title", static function ($title) {
    return is_page_template("page-marketing-meta-ads.php") ? "Meta Ads dla firm | Facebook i Instagram Ads | Upsellio" : $title;
});

add_action("wp_head", static function () {
    if (!is_page_template("page-marketing-meta-ads.php")) return;

    $url = function_exists("upsellio_get_meta_ads_page_url") ? (string) upsellio_get_meta_ads_page_url() : "";
    echo '<meta name="description" content="Kampanie Meta Ads dla firm: Facebook Ads i Instagram Ads nastawione na leady, sprzedaż i remarketing. Strategia, lejek, kreacje i optymalizacja.">' . "\n";
    echo '<meta property="og:title" content="Meta Ads dla firm | Facebook i Instagram Ads | Upsellio">' . "\n";
    echo '<meta property="og:description" content="Kampanie Facebook Ads i Instagram Ads nastawione na leady, sprzedaż i remarketing. Strategia, kreacje, testy i optymalizacja.">' . "\n";
    echo '<meta property="og:type" content="website">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
    $og_image = function_exists("upsellio_get_default_og_image_url") ? upsellio_get_default_og_image_url() : "";
    if ($og_image !== "") {
        echo '<meta property="og:image" content="' . esc_url($og_image) . '">' . "\n";
        echo '<meta name="twitter:image" content="' . esc_url($og_image) . '">' . "\n";
    }
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<link rel="canonical" href="' . esc_url($url) . '">' . "\n";
}, 1);

get_header();

$front_page_sections = function_exists("upsellio_get_front_page_content_config")
    ? upsellio_get_front_page_content_config()
    : [];
$contact_phone = function_exists("upsellio_get_contact_phone")
    ? upsellio_get_contact_phone()
    : trim((string) ($front_page_sections["contact_phone"] ?? "+48 575 522 595"));
$contact_email = trim((string) ($front_page_sections["contact_email"] ?? "kontakt@upsellio.pl"));
$contact_email_href = function_exists("upsellio_get_mailto_href") ? upsellio_get_mailto_href($contact_email) : ("mailto:" . $contact_email);
$contact_email_display = function_exists("upsellio_obfuscate_email_address") ? upsellio_obfuscate_email_address($contact_email) : $contact_email;
$offer_url = function_exists("upsellio_get_offer_page_url") ? (string) upsellio_get_offer_page_url() : "";
$google_ads_url = function_exists("upsellio_get_google_ads_page_url") ? (string) upsellio_get_google_ads_page_url() : "";
$websites_url = function_exists("upsellio_get_websites_page_url") ? (string) upsellio_get_websites_page_url() : "";

$faq_items = [
    [
        "question" => "Czy Meta Ads nadaje się do B2B?",
        "answer" => "Tak, ale wymaga innego podejścia niż w B2C czy e-commerce. W B2B kluczem jest precyzyjne targetowanie, komunikat zaczynający się od problemu biznesowego i lejek, który prowadzi odbiorcę przez kilka punktów styku z marką.",
    ],
    [
        "question" => "Czy potrzebuję landing page do kampanii Meta Ads?",
        "answer" => "Nie zawsze, ale w większości przypadków landing page poprawia wyniki. Jeśli strona nie kontynuuje przekazu reklamy i nie ma wyraźnego CTA, część kliknięć zostanie zmarnowana.",
    ],
    [
        "question" => "Jaki minimalny budżet ma sens przy Meta Ads?",
        "answer" => "Minimalny sensowny budżet zależy od branży, grupy docelowej i celu kampanii. Na starcie ważniejsze niż sam budżet są strategia, spójność komunikatów i możliwość wyciągania wiarygodnych wniosków z testów.",
    ],
    [
        "question" => "Czy przygotowujesz treści i kreacje reklamowe?",
        "answer" => "Przygotowuję komunikaty, hooki, teksty reklamowe, kierunki kreacji i rekomendacje wizualne. Produkcja grafik lub wideo może być realizowana przez Twój zespół, grafika zewnętrznego albo w ramach ustalonego zakresu.",
    ],
    [
        "question" => "Jak odróżnić dobry lead od złego?",
        "answer" => "Dobry lead to kontakt od osoby z realnym problemem, który możesz rozwiązać, i gotowością do rozmowy. Dlatego w kampaniach analizuję nie tylko CPL, ale też kwalifikowalność leadów i ich dalszy los w procesie sprzedaży.",
    ],
    [
        "question" => "Czy Meta Ads można łączyć z Google Ads?",
        "answer" => "Tak, bardzo często to najlepsza strategia. Meta Ads buduje świadomość i zainteresowanie, Google Ads przechwytuje osoby z intencją zakupową, a remarketing w Meta domyka decyzję u osób, które były blisko kontaktu.",
    ],
];
?>

<style>
  .meta-offer-page {
    --meta-bg:#f8fafc;
    --meta-surface:#fff;
    --meta-text:#071426;
    --meta-text-2:#334155;
    --meta-muted:#64748b;
    --meta-border:#e2e8f0;
    --meta-green:#0d9488;
    --meta-green-dark:#0f766e;
    --meta-green-soft:#ecfeff;
    --meta-green-line:#99f6e4;
    --meta-indigo:#0d9488;
    --meta-indigo-soft:#ccfbf1;
    --meta-indigo-line:#99f6e4;
    --meta-dark:#081827;
    --meta-shadow:0 24px 70px rgba(15,23,42,.12);
    --meta-shadow-soft:0 14px 40px rgba(15,23,42,.08);
    background:var(--meta-bg);
    color:var(--meta-text);
  }
  html { scroll-behavior:smooth; scroll-padding-top:140px; }
  .meta-wrap { width:min(1240px, calc(100% - 48px)); margin:0 auto; }
  .meta-section { padding:clamp(70px,8vw,110px) 0; }
  .meta-offer-page .meta-h1,
  .meta-offer-page .meta-h2,
  .meta-offer-page .meta-h3 { font-family:var(--font-display); line-height:1.05; letter-spacing:-1.3px; color:var(--meta-text); }
  .meta-offer-page .meta-h1 { font-size:clamp(42px,6vw,76px); max-width:1040px; }
  .meta-offer-page .meta-h2 { font-size:clamp(32px,4vw,52px); max-width:920px; }
  .meta-offer-page .meta-h3 { font-size:clamp(23px,3vw,34px); }
  .meta-offer-page p { color:var(--meta-text-2); }
  .meta-lead { margin-top:24px; max-width:820px; font-size:clamp(18px,2vw,21px); line-height:1.75; }
  .meta-body { margin-top:18px; max-width:940px; display:grid; gap:14px; }
  .meta-eyebrow { display:inline-flex; align-items:center; gap:10px; margin-bottom:18px; font-size:12px; font-weight:800; letter-spacing:1.6px; text-transform:uppercase; color:var(--meta-indigo); }
  .meta-eyebrow::before { content:""; width:26px; height:2px; background:linear-gradient(90deg,var(--meta-indigo),var(--meta-green)); border-radius:99px; }
  .meta-btn-row { display:flex; flex-wrap:wrap; gap:12px; margin-top:32px; }
  .meta-btn { min-height:50px; display:inline-flex; align-items:center; justify-content:center; border-radius:999px; padding:0 24px; font-size:15px; font-weight:800; transition:.2s ease; text-decoration:none; }
  .meta-btn-primary { background:linear-gradient(135deg,#0d9488,#14b8a6); color:#fff; box-shadow:0 14px 28px rgba(13,148,136,.24); }
  .meta-btn-secondary { background:#fff; border:1px solid #cbd5e1; color:var(--meta-text); }
  .meta-btn-ghost { background:var(--meta-green-soft); color:var(--meta-green-dark); border:1px solid var(--meta-green-line); }
  .meta-btn:hover { transform:translateY(-2px); }
  .meta-quick-nav { position:sticky; top:82px; z-index:70; background:rgba(255,255,255,.95); border-bottom:1px solid var(--meta-border); box-shadow:0 8px 20px rgba(16,18,15,.04); backdrop-filter:blur(12px); }
  .meta-quick-nav-inner { min-height:58px; display:flex; align-items:center; justify-content:space-between; gap:18px; overflow-x:auto; scrollbar-width:none; }
  .meta-quick-nav-inner::-webkit-scrollbar { display:none; }
  .meta-quick-links { display:flex; align-items:center; gap:10px; white-space:nowrap; }
  .meta-quick-links a { min-height:36px; display:inline-flex; align-items:center; padding:0 14px; border:1px solid var(--meta-border); border-radius:999px; font-size:13px; font-weight:700; color:var(--meta-text-2); background:#f8fafc; transition:.2s ease; }
  .meta-quick-links a:hover,
  .meta-quick-links a.is-active { color:var(--meta-green-dark); border-color:var(--meta-green-line); background:var(--meta-green-soft); }
  .meta-quick-cta { flex:0 0 auto; min-height:38px; display:inline-flex; align-items:center; padding:0 16px; border-radius:999px; background:var(--meta-green); color:#fff; font-size:13px; font-weight:800; white-space:nowrap; }
  .meta-hero { position:relative; overflow:hidden; padding:clamp(72px,8vw,120px) 0; border-bottom:1px solid var(--meta-border); }
  .meta-hero::before { content:""; position:absolute; right:-180px; top:-180px; width:620px; height:620px; background:radial-gradient(circle,rgba(13,148,136,.16),transparent 65%); }
  .meta-hero::after { content:""; position:absolute; left:-160px; bottom:-180px; width:520px; height:520px; background:radial-gradient(circle,rgba(20,184,166,.12),transparent 65%); }
  .meta-hero-grid { position:relative; display:grid; grid-template-columns:minmax(0,1.2fr) minmax(320px,.8fr); gap:clamp(36px,5vw,64px); align-items:center; }
  .meta-hero-card { position:relative; background:#fff; border:1px solid var(--meta-border); border-top:4px solid var(--meta-indigo); border-radius:32px; padding:clamp(24px,3vw,34px); box-shadow:var(--meta-shadow); }
  .meta-hero-card-icon { display:inline-flex; align-items:center; justify-content:center; width:48px; height:48px; border-radius:14px; background:linear-gradient(135deg,#0d9488,#0f766e); color:#fff; margin-bottom:14px; box-shadow:0 12px 26px -10px rgba(13,148,136,.5); }
  .meta-hero-card-icon svg { width:26px; height:26px; }
  .meta-hero-card .meta-h3 { margin-bottom:16px; }
  .meta-hero-list { display:flex; flex-direction:column; gap:10px; margin-top:22px; list-style:none; padding:0; }
  .meta-hero-list li { display:flex; align-items:flex-start; gap:12px; padding:14px; border:1px solid var(--meta-border); border-radius:16px; background:#f8fafc; font-size:14px; color:var(--meta-text-2); }
  .meta-hero-check { flex-shrink:0; width:26px; height:26px; display:inline-flex; align-items:center; justify-content:center; border-radius:50%; background:var(--meta-indigo-soft); color:var(--meta-indigo); }
  .meta-hero-check svg { width:14px; height:14px; }
  .meta-card-grid { margin-top:38px; display:grid; grid-template-columns:repeat(3,1fr); gap:18px; }
  .meta-card { padding:28px; border:1px solid var(--meta-border); border-radius:26px; background:#fff; box-shadow:var(--meta-shadow-soft); transition:.2s ease; }
  .meta-card:hover { transform:translateY(-4px); border-color:var(--meta-green-line); box-shadow:var(--meta-shadow); }
  .meta-card strong { display:block; margin-bottom:10px; font-size:18px; color:var(--meta-text); }
  .meta-card p { font-size:15px; }
  .meta-service-detail { background:#fff; border-top:1px solid var(--meta-border); border-bottom:1px solid var(--meta-border); }
  .meta-detail-grid { margin-top:40px; display:grid; grid-template-columns:1fr 1fr; gap:22px; }
  .meta-detail-box { padding:32px; border:1px solid var(--meta-border); border-radius:28px; background:#f8fafc; }
  .meta-detail-box .meta-h3 { margin-bottom:18px; }
  .meta-check-list { display:grid; gap:10px; list-style:none; padding:0; }
  .meta-check-list li { position:relative; padding-left:26px; font-size:15px; color:var(--meta-text-2); }
  .meta-check-list li::before { content:"✓"; position:absolute; left:0; color:var(--meta-green); font-weight:900; }
  .meta-copy-box { margin-top:28px; padding:28px; border:1px solid var(--meta-border); border-radius:26px; background:#fff; display:grid; gap:14px; box-shadow:var(--meta-shadow-soft); }
  .meta-funnel-visual { margin-top:34px; display:grid; gap:24px; grid-template-columns:1fr; align-items:center; padding:24px; border:1px solid var(--meta-border); border-radius:26px; background:#fff; box-shadow:var(--meta-shadow-soft); }
  .meta-funnel-svg { width:100%; max-width:420px; margin:0 auto; aspect-ratio:1.1 / 1; }
  .meta-funnel-svg-tof { fill:#ccfbf1; }
  .meta-funnel-svg-mof { fill:#99f6e4; }
  .meta-funnel-svg-bof { fill:#5eead4; }
  .meta-funnel-svg-rmk { fill:#5eead4; }
  .meta-funnel-svg-line { stroke:#fff; stroke-width:1.5; }
  .meta-funnel-svg-text { font-family:var(--font-display); font-weight:800; letter-spacing:.06em; fill:#fff; }
  .meta-funnel-list { display:grid; gap:12px; }
  .meta-funnel-list-item { display:flex; gap:14px; align-items:flex-start; padding:14px 16px; border:1px solid var(--meta-border); border-radius:14px; background:#f8fafc; }
  .meta-funnel-list-bullet { display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:50%; flex-shrink:0; font-family:var(--font-display); font-weight:800; color:#fff; }
  .meta-funnel-list-bullet.is-tof { background:#14b8a6; }
  .meta-funnel-list-bullet.is-mof { background:#0f766e; }
  .meta-funnel-list-bullet.is-bof { background:#0d9488; }
  .meta-funnel-list-bullet.is-rmk { background:#0d9488; }
  .meta-funnel-list-text strong { display:block; color:var(--meta-text); margin-bottom:4px; }
  .meta-funnel-list-text p { font-size:14px; line-height:1.6; margin:0; }
  @media(min-width:760px){ .meta-funnel-visual { grid-template-columns:.85fr 1.15fr; } }
  .meta-formats { margin-top:42px; display:grid; gap:18px; grid-template-columns:1fr; }
  .meta-format-card { display:flex; flex-direction:column; gap:12px; padding:22px; border:1px solid var(--meta-border); border-radius:24px; background:#fff; box-shadow:var(--meta-shadow-soft); }
  .meta-format-card .meta-format-title { display:flex; align-items:center; gap:10px; font-family:var(--font-display); font-size:18px; letter-spacing:-.02em; color:var(--meta-text); }
  .meta-format-card .meta-format-title::before { content:""; width:8px; height:8px; border-radius:50%; background:var(--meta-indigo); }
  .meta-format-card p { font-size:14px; line-height:1.6; }
  .meta-format-mock { display:flex; align-items:center; justify-content:center; padding:18px; border-radius:18px; background:linear-gradient(135deg,#ccfbf1,#ecfeff); border:1px solid var(--meta-border); min-height:200px; }
  .meta-phone { width:140px; aspect-ratio:9 / 18; background:#0f172a; border-radius:24px; padding:6px; box-shadow:0 18px 40px -18px rgba(15,23,42,.5); }
  .meta-phone-screen { width:100%; height:100%; background:#fff; border-radius:18px; overflow:hidden; display:flex; flex-direction:column; }
  .meta-phone-bar { height:14px; background:#f1f5f9; border-bottom:1px solid var(--meta-border); display:flex; align-items:center; justify-content:center; gap:3px; }
  .meta-phone-bar span { display:block; width:3px; height:3px; border-radius:50%; background:#cbd5e1; }
  .meta-phone-content { flex:1; padding:6px; display:flex; flex-direction:column; gap:5px; }
  .meta-phone-line { height:5px; border-radius:3px; background:linear-gradient(90deg,#e2e8f0,#f1f5f9); }
  .meta-phone-line.short { width:60%; }
  .meta-phone-image { flex:1; min-height:50px; border-radius:6px; background:linear-gradient(135deg,#99f6e4,#5eead4); }
  .meta-phone-cta { height:18px; border-radius:8px; background:linear-gradient(90deg,#0d9488,#0f766e); }
  .meta-stories-frame { display:flex; gap:5px; padding:6px; }
  .meta-stories-frame-thumb { flex:1; aspect-ratio:9 / 16; border-radius:8px; background:linear-gradient(180deg,#99f6e4,#5eead4); border:2px solid #fff; box-shadow:0 6px 14px -6px rgba(13,148,136,.4); }
  .meta-stories-frame-thumb.is-active { background:linear-gradient(180deg,#0d9488,#0f766e); }
  .meta-carousel-row { flex:1; display:flex; gap:5px; padding:6px; align-items:flex-end; min-height:80px; }
  .meta-carousel-tile { flex:0 0 38%; aspect-ratio:1; border-radius:8px; background:linear-gradient(135deg,#99f6e4,#5eead4); }
  .meta-carousel-tile.is-secondary { background:linear-gradient(135deg,#5eead4,#86efac); }
  .meta-carousel-tile.is-tertiary { flex:0 0 30%; background:linear-gradient(135deg,#ccfbf1,#a7f3d0); }
  @media(min-width:760px){ .meta-formats { grid-template-columns:repeat(3, minmax(0,1fr)); } }
  .meta-mid-cta { margin-top:34px; padding:28px; border:1px solid var(--meta-green-line); border-radius:26px; background:linear-gradient(135deg,#ecfeff,#fff); display:grid; grid-template-columns:1fr auto; align-items:center; gap:20px; box-shadow:var(--meta-shadow-soft); }
  .meta-mid-cta strong { display:block; font-family:var(--font-display); font-size:clamp(24px,3vw,36px); line-height:1.05; letter-spacing:-1px; margin-bottom:8px; color:var(--meta-text); }
  .meta-mid-cta p { font-size:15px; max-width:720px; }
  .meta-dark-box { background:radial-gradient(circle at right top, rgba(20,184,166,.22), transparent 35%), var(--meta-dark); color:#fff; border-radius:32px; padding:clamp(34px,5vw,56px); display:grid; grid-template-columns:.95fr 1.05fr; gap:36px; align-items:center; box-shadow:var(--meta-shadow); }
  .meta-dark-box p,
  .meta-dark-box .meta-lead { color:rgba(255,255,255,.72); }
  .meta-dark-box .meta-h2 { color:#fff; }
  .meta-dark-box .meta-eyebrow { color:#8ff0ca; }
  .meta-dark-box .meta-eyebrow::before { background:#8ff0ca; }
  .meta-steps { display:grid; grid-template-columns:repeat(2,1fr); gap:14px; }
  .meta-step { padding:22px; border:1px solid rgba(255,255,255,.12); border-radius:20px; background:rgba(255,255,255,.06); }
  .meta-step-head { display:flex; align-items:center; gap:10px; margin-bottom:14px; }
  .meta-step-num { width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center; border-radius:50%; background:rgba(143,240,202,.14); color:#8ff0ca; font-family:var(--font-display); font-weight:800; }
  .meta-step-icon { display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; border-radius:8px; background:rgba(143,240,202,.10); color:#fff; }
  .meta-step-icon svg { width:18px; height:18px; }
  .meta-step b { width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center; margin-bottom:14px; border-radius:50%; background:rgba(143,240,202,.14); color:#8ff0ca; font-family:var(--font-display); }
  .meta-step strong { display:block; margin-bottom:6px; color:#fff; }
  .meta-step p { font-size:14px; }
  .meta-effects-list { margin-top:26px; display:grid; gap:10px; max-width:920px; list-style:none; padding:0; }
  .meta-effects-list li { position:relative; padding-left:30px; color:var(--meta-text-2); }
  .meta-effects-list li::before { content:"✓"; position:absolute; left:0; top:0; color:var(--meta-green); font-weight:900; }
  .meta-faq-grid { margin-top:38px; display:grid; gap:14px; max-width:960px; }
  .meta-offer-page details { border:1px solid var(--meta-border); border-radius:18px; background:#fff; padding:20px 22px; box-shadow:var(--meta-shadow-soft); }
  .meta-offer-page summary { cursor:pointer; font-weight:800; color:var(--meta-text); }
  .meta-offer-page details p { margin-top:12px; font-size:15px; }
  .meta-final-cta { text-align:center; padding:clamp(42px,5vw,64px); border:1px solid var(--meta-green-line); border-radius:32px; background:radial-gradient(circle at top,#ecfeff,#fff 60%); box-shadow:var(--meta-shadow-soft); }
  .meta-final-cta .meta-h2 { margin:0 auto; }
  .meta-final-cta p { max-width:820px; margin:20px auto 0; font-size:18px; }
  .meta-final-cta .meta-btn-row { justify-content:center; }
  .meta-internal-links { margin-top:22px; display:flex; flex-wrap:wrap; justify-content:center; gap:10px; }
  .meta-internal-links a { display:inline-flex; min-height:38px; align-items:center; border:1px solid var(--meta-border); border-radius:999px; padding:0 14px; background:#fff; color:var(--meta-green-dark); font-size:13px; font-weight:800; }
  @media(max-width:980px){
    html { scroll-padding-top:130px; }
    .meta-hero-grid,.meta-detail-grid,.meta-dark-box { grid-template-columns:1fr; }
    .meta-card-grid { grid-template-columns:1fr 1fr; }
    .meta-funnel { grid-template-columns:repeat(2,1fr); }
    .meta-mid-cta { grid-template-columns:1fr; }
  }
  @media(max-width:620px){
    html { scroll-padding-top:125px; }
    .meta-wrap { width:min(100% - 28px, 1240px); }
    .meta-card-grid,.meta-funnel,.meta-steps { grid-template-columns:1fr; }
    .meta-btn { width:100%; }
    .meta-quick-cta { display:none; }
    .meta-quick-nav-inner { padding:10px 0; }
  }
  /* Mobile-first UX correction layer */
  .meta-section { padding:48px 0; }
  .meta-hero { padding:52px 0 46px; }
  .meta-offer-page .meta-h1 { font-size:clamp(34px,10vw,40px); line-height:1.09; letter-spacing:-1px; }
  .meta-offer-page .meta-h2 { font-size:clamp(28px,8vw,34px); line-height:1.12; letter-spacing:-.8px; }
  .meta-offer-page .meta-h3 { font-size:clamp(21px,6vw,26px); line-height:1.16; letter-spacing:-.5px; }
  .meta-lead { margin-top:16px; font-size:17px; line-height:1.65; }
  .meta-body { margin-top:14px; gap:10px; }
  .meta-body p { line-height:1.72; }
  .meta-btn-row { margin-top:22px; }
  .meta-quick-nav { position:static; }
  .meta-quick-nav-inner { min-height:auto; padding:10px 0; }
  .meta-card-grid,.meta-detail-grid,.meta-funnel,.meta-steps { grid-template-columns:1fr; }
  .meta-card,.meta-detail-box,.meta-copy-box,.meta-funnel-step,.meta-final-cta { padding:20px; border-radius:20px; }
  .meta-dark-box { padding:28px 20px; border-radius:24px; }
  .meta-mid-cta { padding:20px; border-radius:20px; }
  .meta-mid-cta strong { font-size:clamp(22px,7vw,28px); line-height:1.12; }
  .meta-hero-card { padding:20px; border-radius:22px; }
  @media(min-width:760px){
    .meta-section { padding:72px 0; }
    .meta-hero { padding:76px 0 68px; }
    .meta-quick-nav { position:sticky; }
    .meta-offer-page .meta-h1 { font-size:clamp(44px,6vw,58px); line-height:1.05; }
    .meta-offer-page .meta-h2 { font-size:clamp(34px,4vw,46px); }
    .meta-offer-page .meta-h3 { font-size:clamp(23px,3vw,30px); }
    .meta-card-grid,.meta-funnel { grid-template-columns:repeat(2,minmax(0,1fr)); }
    .meta-steps { grid-template-columns:repeat(2,minmax(0,1fr)); }
  }
  @media(min-width:1024px){
    .meta-section { padding:96px 0; }
    .meta-hero { padding:96px 0 88px; }
    .meta-offer-page .meta-h1 { font-size:66px; }
    .meta-offer-page .meta-h2 { font-size:50px; }
    .meta-hero-grid { grid-template-columns:minmax(0,1.08fr) minmax(300px,.78fr); }
    .meta-card-grid { grid-template-columns:repeat(3,minmax(0,1fr)); }
    .meta-funnel { grid-template-columns:repeat(4,minmax(0,1fr)); }
    .meta-card,.meta-detail-box,.meta-copy-box,.meta-funnel-step { padding:24px; }
  }
</style>

<main class="meta-offer-page">
  <div class="meta-quick-nav">
    <div class="meta-wrap meta-quick-nav-inner">
      <div class="meta-quick-links">
        <a href="#start">Start</a>
        <a href="#problemy">Problemy</a>
        <a href="#b2b">B2B</a>
        <a href="#dla-kogo">Dla kogo</a>
        <a href="#co-robie">Co robię</a>
        <a href="#lejek">Lejek</a>
        <a href="#proces">Proces</a>
        <a href="#faq">FAQ</a>
      </div>
      <a href="#kontakt" class="meta-quick-cta">Chcę sprawdzić Meta Ads</a>
    </div>
  </div>

  <section class="meta-hero" id="start">
    <div class="meta-wrap meta-hero-grid">
      <div>
        <span class="meta-eyebrow">Marketing Meta Ads</span>
        <h1 class="meta-h1">Reklamy Meta Ads dla firm: kampanie Facebook Ads i Instagram Ads, które generują leady i sprzedaż, nie tylko kliknięcia.</h1>
        <p class="meta-lead">Tworzenie i prowadzenie kampanii Meta Ads dla firm B2B, usługowych i e-commerce. Lejki reklamowe, remarketing i kreacje, które prowadzą klienta od pierwszego kontaktu z reklamą do decyzji zakupowej.</p>
        <div class="meta-body">
          <p>Reklamy na Facebooku i Instagramie to jeden z najskuteczniejszych kanałów pozyskiwania klientów dla firm, pod warunkiem że są prowadzone jako system, a nie przypadkowe kampanie uruchamiane bez strategii. Samo puszczenie reklamy to za mało, żeby generować wartościowe zapytania i sprzedaż.</p>
          <p>Skuteczne kampanie Meta Ads łączą precyzyjne targetowanie grupy docelowej, spójny komunikat dopasowany do etapu decyzyjnego, kreacje zatrzymujące uwagę w pierwszych sekundach scrollowania i stronę docelową, która zamienia kliknięcia w kontakty.</p>
        </div>
        <div class="meta-btn-row">
          <a href="#kontakt" class="meta-btn meta-btn-primary">Chcę bezpłatną diagnozę</a>
          <a href="#co-robie" class="meta-btn meta-btn-secondary">Zobacz, co obejmuje usługa</a>
          <a href="#lejek" class="meta-btn meta-btn-ghost">Zobacz lejek Meta Ads</a>
        </div>
      </div>

      <aside class="meta-hero-card">
        <span class="meta-hero-card-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06C2 17.08 5.66 21.24 10.44 22v-7.03H7.9v-2.91h2.54V9.85c0-2.51 1.49-3.89 3.78-3.89 1.09 0 2.24.2 2.24.2v2.47h-1.26c-1.24 0-1.63.77-1.63 1.56v1.87h2.78l-.45 2.91h-2.33V22C18.34 21.24 22 17.08 22 12.06z"/></svg>
        </span>
        <h2 class="meta-h3">Meta Ads działa najlepiej, gdy nie jest przypadkowym boostowaniem posta.</h2>
        <p>Projektuję strukturę lejka reklamowego ToF, MoF, BoF i remarketing, przygotowuję komunikaty oraz kierunki kreacji, konfiguruję zdarzenia i piksel Mety, prowadzę testy A/B i na bieżąco optymalizuję koszty oraz jakość leadów.</p>
        <ul class="meta-hero-list">
          <li><span class="meta-hero-check" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span><span>Kampanie pod konkretne etapy decyzji klienta.</span></li>
          <li><span class="meta-hero-check" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span><span>Kreacje reklamowe z jasnym komunikatem i CTA.</span></li>
          <li><span class="meta-hero-check" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span><span>Remarketing do osób, które już wykazały zainteresowanie.</span></li>
          <li><span class="meta-hero-check" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span><span>Analiza jakości leadów, nie tylko kosztu kliknięcia.</span></li>
        </ul>
      </aside>
    </div>
  </section>

  <section class="meta-section" id="b2b">
    <div class="meta-wrap">
      <span class="meta-eyebrow">Meta Ads w B2B</span>
      <h2 class="meta-h2">Meta Ads w B2B działa inaczej niż w B2C: liczy się dłuższy lejek, remarketing i jakość kontaktu.</h2>
      <div class="meta-body">
        <p>W B2B użytkownik rzadko podejmuje decyzję po jednym kliknięciu. Kampania musi najpierw nazwać problem, potem zbudować zaufanie, a dopiero później poprosić o rozmowę. Dlatego zamiast jednej reklamy „kup teraz” potrzebny jest lejek z osobnymi komunikatami na etap świadomości, rozważania i kontaktu.</p>
        <p>Najlepiej działają kampanie, które łączą listy klientów, podobne grupy odbiorców, remarketing do osób odwiedzających stronę oraz treści pokazujące konkretną wiedzę branżową. Lead Ads mogą obniżyć tarcie, ale przy droższych usługach często lepszą jakość daje landing page z jasną kwalifikacją i dowodami zaufania.</p>
      </div>
      <div class="meta-card-grid">
        <div class="meta-card"><strong>Dłuższy cykl decyzji</strong><p>Komunikaty muszą prowadzić przez problem, dowód i zaproszenie do rozmowy.</p></div>
        <div class="meta-card"><strong>Remarketing jako rdzeń</strong><p>Osoby po kontakcie z marką powinny dostawać kolejny, bardziej konkretny argument.</p></div>
        <div class="meta-card"><strong>Lookalike od jakościowych danych</strong><p>Lepszą bazą są klienci i dobre leady, nie przypadkowe kliknięcia.</p></div>
      </div>
    </div>
  </section>

  <section class="meta-section" id="problemy">
    <div class="meta-wrap">
      <span class="meta-eyebrow">Typowe problemy</span>
      <h2 class="meta-h2">Dlaczego reklamy na Facebooku i Instagramie nie przynoszą zapytań ani sprzedaży?</h2>
      <div class="meta-body">
        <p>Wiele firm zna ten schemat: reklama jest aktywna, budżet się wydaje, są kliknięcia i wyświetlenia, ale zapytania nie przychodzą, a sprzedaż nie rośnie. Problem zwykle nie leży w samym Facebooku czy Instagramie jako kanale. Leży w konkretnych elementach kampanii, które nie działają razem.</p>
        <p>Najczęstsze przyczyny to brak spójności między reklamą a stroną docelową, jedna reklama kierowana do wszystkich, kreacje mówiące o firmie zamiast o problemie klienta, brak remarketingu i optymalizacja pod metryki, które nie mówią nic o jakości kontaktów.</p>
      </div>

      <div class="meta-card-grid">
        <div class="meta-card"><strong>Reklamy mają kliknięcia, ale nie ma zapytań</strong><p>Odbiorcy reagują, ale nie przechodzą do rozmowy, formularza lub zakupu.</p></div>
        <div class="meta-card"><strong>Leady są tanie, ale słabe</strong><p>Koszt kontaktu wygląda dobrze, ale rozmowy nie kończą się sprzedażą.</p></div>
        <div class="meta-card"><strong>Kreacje szybko się wypalają</strong><p>Po kilku dniach wyniki spadają, bo reklamy nie są testowane według lejka.</p></div>
        <div class="meta-card"><strong>Brakuje remarketingu</strong><p>Osoby, które były na stronie lub kliknęły reklamę, nie dostają kolejnego komunikatu.</p></div>
        <div class="meta-card"><strong>Reklamy mówią o firmie, nie o problemie klienta</strong><p>Komunikat jest poprawny, ale nie zatrzymuje scrolla i nie buduje powodu do kontaktu.</p></div>
        <div class="meta-card"><strong>Strona nie domyka reklamy</strong><p>Nawet dobra kampania nie pomoże, jeśli landing page nie tłumaczy oferty i nie prowadzi do CTA.</p></div>
      </div>

      <div class="meta-mid-cta">
        <div>
          <strong>Nie chodzi o to, żeby puścić reklamę. Chodzi o to, żeby zbudować ścieżkę decyzji.</strong>
          <p>Zanim zaproponuję zmiany, sprawdzam kampanie, kreacje, grupy odbiorców, stronę docelową i ofertę.</p>
        </div>
        <a href="#kontakt" class="meta-btn meta-btn-primary">Sprawdźmy Twoje kampanie</a>
      </div>
    </div>
  </section>

  <section class="meta-section meta-service-detail" id="co-robie">
    <div class="meta-wrap">
      <span class="meta-eyebrow">Co obejmuje usługa</span>
      <h2 class="meta-h2">Pełna usługa Meta Ads: od strategii i konfiguracji, przez kreacje i kampanie, do remarketingu i optymalizacji jakości leadów.</h2>
      <div class="meta-copy-box">
        <p>Prowadzenie kampanii Meta Ads to znacznie więcej niż uruchomienie zestawu reklam w Menedżerze Reklam. Skuteczna kampania to system złożony z kilku warstw, które muszą ze sobą współpracować.</p>
        <p>Dlatego usługa obejmuje wszystkie etapy: analizę i strategię, tworzenie komunikatów, konfigurację techniczną, uruchomienie kampanii, remarketing, testy i bieżącą ocenę jakości kontaktów.</p>
      </div>

      <div class="meta-detail-grid">
        <div class="meta-detail-box">
          <h3 class="meta-h3">Strategia i konfiguracja techniczna</h3>
          <ul class="meta-check-list">
            <li>analiza oferty, grup docelowych i etapu decyzyjnego klienta</li>
            <li>ustalenie celu kampanii: leady, sprzedaż, ruch jakościowy, remarketing</li>
            <li>struktura kampanii pod ToF, MoF i BoF</li>
            <li>konfiguracja piksela Mety, zdarzeń konwersji i analityki</li>
            <li>rekomendacje do landing page lub strony docelowej</li>
          </ul>
        </div>

        <div class="meta-detail-box">
          <h3 class="meta-h3">Komunikaty i kierunki kreatywne</h3>
          <ul class="meta-check-list">
            <li>hooki zatrzymujące uwagę w pierwszych sekundach</li>
            <li>teksty reklamowe nastawione na problem, efekt i CTA</li>
            <li>wersje A/B/C do testowania komunikatów</li>
            <li>reklamy sprzedażowe, edukacyjne, remarketingowe i dowodowe</li>
            <li>kierunki kreacji dopasowane do etapu lejka</li>
          </ul>
        </div>

        <div class="meta-detail-box">
          <h3 class="meta-h3">Prowadzenie kampanii i testy</h3>
          <ul class="meta-check-list">
            <li>uruchomienie kampanii Facebook Ads i Instagram Ads</li>
            <li>testowanie grup odbiorców, kreacji i kątów komunikacji</li>
            <li>kontrola budżetu i kosztów pozyskania kontaktu</li>
            <li>optymalizacja pod jakość leadów, nie tylko tani kontakt</li>
            <li>cykliczne wnioski i rekomendacje dalszych działań</li>
          </ul>
        </div>

        <div class="meta-detail-box">
          <h3 class="meta-h3">Remarketing i domykanie decyzji</h3>
          <ul class="meta-check-list">
            <li>reklamy do osób, które były na stronie</li>
            <li>reklamy do osób zaangażowanych w social media</li>
            <li>komunikaty pod obiekcje, dowody i zaufanie</li>
            <li>CTA do rozmowy, formularza, oferty lub zakupu</li>
            <li>analiza, gdzie użytkownik odpada ze ścieżki</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section class="meta-section" id="dla-kogo">
    <div class="meta-wrap">
      <span class="meta-eyebrow">Dla kogo</span>
      <h2 class="meta-h2">Dla jakich firm Meta Ads ma sens i kiedy jest to lepszy wybór niż Google Ads?</h2>
      <div class="meta-body">
        <p>Meta Ads sprawdza się tam, gdzie proces decyzyjny klienta nie zaczyna się od wyszukiwania w Google albo tam, gdzie chcesz dotrzeć do klientów zanim trafią do wyszukiwarki. To kanał, który pozwala budować popyt, edukację i zainteresowanie, a następnie domykać decyzję przez remarketing.</p>
        <p>Dla firm usługowych Meta Ads pozwala dotrzeć do osób z konkretnym profilem zawodowym lub problemem i przeprowadzić je przez proces edukacji. Dla firm B2B umożliwia targetowanie według stanowisk, branż, wielkości firmy lub podobieństwa do obecnych klientów. Dla e-commerce to kanał kampanii produktowych, remarketingu dynamicznego i testowania kreacji sprzedażowych z pomiarem ROAS.</p>
      </div>

      <div class="meta-card-grid">
        <div class="meta-card"><strong>Firmy usługowe</strong><p>Dla usług, które wymagają zaufania, edukacji klienta i kilku punktów styku przed decyzją.</p></div>
        <div class="meta-card"><strong>B2B i lokalne firmy</strong><p>Dla firm, które chcą dotrzeć do właścicieli, managerów lub lokalnych klientów z konkretnym problemem.</p></div>
        <div class="meta-card"><strong>E-commerce</strong><p>Dla sklepów, które potrzebują kampanii produktowych, remarketingu i testowania kreacji sprzedażowych.</p></div>
      </div>
    </div>
  </section>

  <section class="meta-section" id="lejek">
    <div class="meta-wrap">
      <span class="meta-eyebrow">Lejek Meta Ads</span>
      <h2 class="meta-h2">Jak działa lejek Meta Ads i dlaczego jedna reklama do wszystkich to najczęstszy błąd?</h2>
      <div class="meta-body">
        <p>Jednym z najczęstszych błędów w kampaniach Meta Ads jest traktowanie wszystkich odbiorców tak samo. Firma uruchamia jedną reklamę z ofertą do szerokiej grupy, nie widzi wyników i uznaje, że Facebook Ads nie działa. Problem zwykle nie leży w kanale, tylko w braku lejka.</p>
        <p>Lejek reklamowy Meta Ads składa się z czterech poziomów, z których każdy wymaga innego komunikatu, innego celu kampanii i innego sposobu mierzenia wyniku.</p>
      </div>

      <div class="meta-funnel-visual">
        <svg class="meta-funnel-svg" viewBox="0 0 320 280" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <polygon class="meta-funnel-svg-tof meta-funnel-svg-line" points="20,20 300,20 270,80 50,80" />
          <polygon class="meta-funnel-svg-mof meta-funnel-svg-line" points="50,80 270,80 240,140 80,140" />
          <polygon class="meta-funnel-svg-bof meta-funnel-svg-line" points="80,140 240,140 210,200 110,200" />
          <polygon class="meta-funnel-svg-rmk meta-funnel-svg-line" points="110,210 210,210 195,260 125,260" />
          <text class="meta-funnel-svg-text" x="160" y="55" text-anchor="middle" font-size="14">TOF</text>
          <text class="meta-funnel-svg-text" x="160" y="115" text-anchor="middle" font-size="14">MOF</text>
          <text class="meta-funnel-svg-text" x="160" y="175" text-anchor="middle" font-size="14">BOF</text>
          <text class="meta-funnel-svg-text" x="160" y="240" text-anchor="middle" font-size="12">REMARKETING</text>
        </svg>
        <div class="meta-funnel-list">
          <div class="meta-funnel-list-item">
            <span class="meta-funnel-list-bullet is-tof">1</span>
            <div class="meta-funnel-list-text">
              <strong>ToF: zimny odbiorca</strong>
              <p>Zatrzymanie uwagi, nazwanie problemu i pokazanie efektu lub błędu, który klient rozpoznaje.</p>
            </div>
          </div>
          <div class="meta-funnel-list-item">
            <span class="meta-funnel-list-bullet is-mof">2</span>
            <div class="meta-funnel-list-text">
              <strong>MoF: zainteresowanie</strong>
              <p>Edukacja, argumenty, przykłady, porównania i pokazanie, dlaczego warto rozważyć Twoją ofertę.</p>
            </div>
          </div>
          <div class="meta-funnel-list-item">
            <span class="meta-funnel-list-bullet is-bof">3</span>
            <div class="meta-funnel-list-text">
              <strong>BoF: decyzja</strong>
              <p>Dowody, oferta, CTA, ograniczenie ryzyka, odpowiedź na obiekcje i zachęta do kontaktu.</p>
            </div>
          </div>
          <div class="meta-funnel-list-item">
            <span class="meta-funnel-list-bullet is-rmk">4</span>
            <div class="meta-funnel-list-text">
              <strong>Remarketing</strong>
              <p>Powrót do osób, które były blisko decyzji, ale nie zostawiły kontaktu lub nie kupiły.</p>
            </div>
          </div>
        </div>
      </div>

      <div class="meta-formats" aria-label="Formaty reklam Meta">
        <div class="meta-format-card">
          <div class="meta-format-mock" aria-hidden="true">
            <div class="meta-phone">
              <div class="meta-phone-screen">
                <div class="meta-phone-bar"><span></span><span></span><span></span></div>
                <div class="meta-phone-content">
                  <div class="meta-phone-line short"></div>
                  <div class="meta-phone-image"></div>
                  <div class="meta-phone-line"></div>
                  <div class="meta-phone-line short"></div>
                  <div class="meta-phone-cta"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="meta-format-title">Feed Ads</div>
          <p>Klasyczne reklamy w feedzie Facebooka i Instagrama — najlepsze do edukacji, dowodów i kampanii sprzedażowych.</p>
        </div>
        <div class="meta-format-card">
          <div class="meta-format-mock" aria-hidden="true">
            <div class="meta-phone">
              <div class="meta-phone-screen">
                <div class="meta-phone-bar"><span></span><span></span><span></span></div>
                <div class="meta-stories-frame">
                  <div class="meta-stories-frame-thumb is-active"></div>
                  <div class="meta-stories-frame-thumb"></div>
                  <div class="meta-stories-frame-thumb"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="meta-format-title">Stories i Reels</div>
          <p>Pełnoekranowe formaty pionowe — szybki hook, mocna kreacja i jasne CTA. Doskonałe dla remarketingu i kampanii zasięgowych.</p>
        </div>
        <div class="meta-format-card">
          <div class="meta-format-mock" aria-hidden="true">
            <div class="meta-phone">
              <div class="meta-phone-screen">
                <div class="meta-phone-bar"><span></span><span></span><span></span></div>
                <div class="meta-phone-content">
                  <div class="meta-phone-line short"></div>
                  <div class="meta-carousel-row">
                    <div class="meta-carousel-tile"></div>
                    <div class="meta-carousel-tile is-secondary"></div>
                    <div class="meta-carousel-tile is-tertiary"></div>
                  </div>
                  <div class="meta-phone-line"></div>
                  <div class="meta-phone-cta"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="meta-format-title">Carousel</div>
          <p>Wiele kart w jednej reklamie — idealne do pokazania kilku korzyści, kroków procesu lub produktów w kampanii sprzedażowej.</p>
        </div>
      </div>

      <div class="meta-copy-box">
        <p>Reklama ToF nie musi sprzedawać od razu. Jej zadaniem jest zatrzymanie uwagi i zbudowanie świadomości problemu. Reklama MoF wzmacnia zaufanie przez przykłady, porównania i edukację. Reklama BoF jest konkretna: oferta, dowód, CTA i odpowiedź na obiekcje.</p>
        <p>Remarketing wraca do osób, które odwiedziły stronę, obejrzały reklamę, kliknęły post albo dodały produkt do koszyka. To część lejka, którą firmy często pomijają, tracąc leady, które były o krok od decyzji.</p>
      </div>

      <div class="meta-mid-cta">
        <div>
          <strong>Największy błąd? Reklamowanie oferty osobom, które jeszcze nie rozumieją problemu.</strong>
          <p>Dlatego układam komunikaty tak, żeby prowadzić klienta od uwagi do decyzji.</p>
        </div>
        <a href="#kontakt" class="meta-btn meta-btn-primary">Chcę lejek Meta Ads</a>
      </div>
    </div>
  </section>

  <section class="meta-section" id="proces">
    <div class="meta-wrap">
      <div class="meta-dark-box">
        <div>
          <span class="meta-eyebrow">Proces współpracy</span>
          <h2 class="meta-h2">Jak wygląda współpraca przy prowadzeniu Meta Ads: od diagnozy do optymalizacji?</h2>
          <p class="meta-lead">Każda współpraca zaczyna się od diagnozy, a nie od uruchomienia kampanii. Najpierw trzeba zrozumieć, co sprzedajesz, do kogo, jaki jest proces decyzyjny klienta i gdzie dziś giną potencjalni klienci.</p>
          <div class="meta-btn-row">
            <a href="#kontakt" class="meta-btn meta-btn-primary">Zacznijmy od diagnozy</a>
          </div>
        </div>

        <div class="meta-steps">
          <div class="meta-step">
            <div class="meta-step-head">
              <span class="meta-step-num">1</span>
              <span class="meta-step-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
            </div>
            <strong>Diagnoza</strong>
            <p>Oferta, grupa docelowa, strona, poprzednie kampanie i realny problem sprzedażowy.</p>
          </div>
          <div class="meta-step">
            <div class="meta-step-head">
              <span class="meta-step-num">2</span>
              <span class="meta-step-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg></span>
            </div>
            <strong>Strategia</strong>
            <p>Cel kampanii, struktura lejka, podział budżetu, komunikaty i sposób mierzenia sukcesu.</p>
          </div>
          <div class="meta-step">
            <div class="meta-step-head">
              <span class="meta-step-num">3</span>
              <span class="meta-step-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg></span>
            </div>
            <strong>Wdrożenie</strong>
            <p>Piksel, zdarzenia, konta reklamowe, kampanie, kreacje, hooki, teksty i tracking leadów.</p>
          </div>
          <div class="meta-step">
            <div class="meta-step-head">
              <span class="meta-step-num">4</span>
              <span class="meta-step-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 17 9 11 13 15 21 7"/><polyline points="14 7 21 7 21 14"/></svg></span>
            </div>
            <strong>Optymalizacja</strong>
            <p>Analiza wyników, jakości leadów, kosztów, kreacji i kolejnych testów.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="meta-section" id="efekty">
    <div class="meta-wrap">
      <span class="meta-eyebrow">Co dostajesz</span>
      <h2 class="meta-h2">Efektem współpracy nie jest tylko aktywna kampania, ale system, który systematycznie generuje wartościowe zapytania.</h2>
      <div class="meta-body">
        <p>Dobrze zaprojektowane kampanie Meta Ads nie są losowym generatorem kliknięć. Są systemem, który można skalować, optymalizować i rozumieć. Wspólnie pracujemy nad spadkiem kosztu pozyskania wartościowego leada, wyższą jakością zapytań, systematycznym remarketingiem i wiedzą o tym, które kreacje oraz komunikaty działają.</p>
        <p>Nie obiecuję konkretnych liczb przed diagnozą, bo wyniki zależą od branży, oferty, budżetu i jakości strony docelowej. Po analizie sytuacji mogę jednak powiedzieć, czego można realnie oczekiwać i w jakim czasie.</p>
      </div>
      <ul class="meta-effects-list">
        <li>Spadek kosztu pozyskania wartościowego leada przy stabilnym lub rosnącym budżecie.</li>
        <li>Wyższa jakość zapytań od osób, które rozumieją ofertę i są bliżej decyzji.</li>
        <li>Remarketing, który odzyskuje osoby zainteresowane, ale niezdecydowane.</li>
        <li>Jasna wiedza o tym, które kreacje, komunikaty i grupy odbiorców działają.</li>
        <li>Lejek reklamowy, który można rozszerzać i skalować bez przepalania budżetu.</li>
      </ul>

      <div class="meta-card-grid">
        <div class="meta-card"><strong>Jasną strukturę kampanii</strong><p>Wiesz, które kampanie odpowiadają za zasięg, które za zainteresowanie, a które za domykanie.</p></div>
        <div class="meta-card"><strong>Kreacje do testowania</strong><p>Nie opieramy wyniku na jednej grafice. Testujemy różne komunikaty, kąty i CTA.</p></div>
        <div class="meta-card"><strong>Wnioski sprzedażowe</strong><p>Patrzymy nie tylko na CPM, CTR i CPL, ale też na to, czy leady są wartościowe.</p></div>
      </div>
    </div>
  </section>

  <section class="meta-section" id="faq">
    <div class="meta-wrap">
      <span class="meta-eyebrow">FAQ</span>
      <h2 class="meta-h2">Najczęstsze pytania przed rozpoczęciem kampanii Meta Ads.</h2>
      <div class="meta-faq-grid">
        <?php foreach ($faq_items as $faq_item) : ?>
          <details>
            <summary><?php echo esc_html((string) $faq_item["question"]); ?></summary>
            <p><?php echo esc_html((string) $faq_item["answer"]); ?></p>
          </details>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="meta-section" id="kontakt">
    <div class="meta-wrap">
      <div class="meta-final-cta">
        <h2 class="meta-h2">Chcesz sprawdzić, czy Meta Ads ma sens w Twojej firmie i od czego zacząć?</h2>
        <p>Zanim zainwestujesz budżet w reklamy Meta, warto wiedzieć, czy problem leży w kampaniach, kreacjach, targetowaniu, stronie czy samej ofercie. Napisz, co sprzedajesz, do kogo kierujesz ofertę i co dzisiaj nie działa: za mało zapytań, za drogie leady, słaba jakość kontaktów lub brak remarketingu.</p>
        <p>Bezpłatna diagnoza to krótka rozmowa lub analiza pisemna, po której wiesz, czy Meta Ads ma sens w Twoim przypadku, który etap lejka blokuje wyniki i jakich rezultatów można realnie oczekiwać przy Twoim budżecie oraz branży.</p>
        <div class="meta-btn-row">
          <a href="<?php echo esc_url($contact_email_href); ?>" class="meta-btn meta-btn-primary">Napisz: <?php echo esc_html($contact_email_display); ?></a>
          <a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $contact_phone)); ?>" class="meta-btn meta-btn-secondary">Zadzwoń: <?php echo esc_html($contact_phone); ?></a>
        </div>
        <div class="meta-internal-links" aria-label="Powiązane usługi">
          <?php if ($offer_url !== "") : ?><a href="<?php echo esc_url($offer_url); ?>">Pełna oferta marketingowa</a><?php endif; ?>
          <?php if ($google_ads_url !== "") : ?><a href="<?php echo esc_url($google_ads_url); ?>">Kampanie Google Ads dla firm</a><?php endif; ?>
          <?php if ($websites_url !== "") : ?><a href="<?php echo esc_url($websites_url); ?>">Tworzenie stron pod konwersję</a><?php endif; ?>
        </div>
      </div>
    </div>
  </section>
</main>

<?php if (!empty($faq_items)) : ?>
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
    }, $faq_items),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
?>
</script>
<?php endif; ?>
<?php
if (function_exists("upsellio_render_breadcrumb_schema")) {
    upsellio_render_breadcrumb_schema([
        ["name" => "Strona główna", "url" => "/"],
        ["name" => "Oferta", "url" => "/oferta/"],
        ["name" => "Meta Ads", "url" => "/marketing-meta-ads/"],
    ]);
}
if (function_exists("upsellio_render_service_schema")) {
    upsellio_render_service_schema(
        "Kampanie Meta Ads dla firm",
        "Prowadzenie kampanii Facebook Ads i Instagram Ads nastawionych na leady, sprzedaż i remarketing.",
        "/marketing-meta-ads/",
        "Meta Ads"
    );
}
?>

<script>
  (function () {
    const quickLinks = Array.from(document.querySelectorAll(".meta-quick-links a"));
    const sections = quickLinks
      .map((link) => document.querySelector(link.getAttribute("href")))
      .filter(Boolean);

    function setActiveQuickLink() {
      let current = "";
      sections.forEach((section) => {
        const sectionTop = section.offsetTop - 170;
        if (window.scrollY >= sectionTop) current = "#" + section.id;
      });
      quickLinks.forEach((link) => {
        link.classList.toggle("is-active", link.getAttribute("href") === current);
      });
    }

    window.addEventListener("scroll", setActiveQuickLink, { passive: true });
    setActiveQuickLink();
  })();
</script>

<?php
get_footer();
