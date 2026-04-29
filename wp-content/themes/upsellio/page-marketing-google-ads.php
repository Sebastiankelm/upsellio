<?php
/*
Template Name: Upsellio - Google Ads
Template Post Type: page
*/
if (!defined("ABSPATH")) {
    exit;
}

add_filter("pre_get_document_title", static function ($title) {
    return is_page_template("page-marketing-google-ads.php") ? "Google Ads dla firm | Kampanie Search i PMax | Upsellio" : $title;
});

add_action("wp_head", static function () {
    if (!is_page_template("page-marketing-google-ads.php")) return;

    $url = function_exists("upsellio_get_google_ads_page_url") ? (string) upsellio_get_google_ads_page_url() : "";
    echo '<meta name="description" content="Prowadzenie kampanii Google Ads dla firm: Search, Performance Max, słowa kluczowe z intencją zakupową, landing pages i optymalizacja CPL.">' . "\n";
    echo '<meta property="og:title" content="Google Ads dla firm | Kampanie Search i PMax | Upsellio">' . "\n";
    echo '<meta property="og:description" content="Kampanie Google Ads dla firm: Search, Performance Max, słowa kluczowe z intencją zakupową, landing pages i optymalizacja kosztu leada.">' . "\n";
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

$front_page_sections = function_exists("upsellio_get_front_page_content_config") ? upsellio_get_front_page_content_config() : [];
$contact_phone = function_exists("upsellio_get_contact_phone")
    ? upsellio_get_contact_phone()
    : trim((string) ($front_page_sections["contact_phone"] ?? "+48 575 522 595"));
$contact_email = trim((string) ($front_page_sections["contact_email"] ?? "kontakt@upsellio.pl"));
$contact_email_href = function_exists("upsellio_get_mailto_href") ? upsellio_get_mailto_href($contact_email) : ("mailto:" . $contact_email);
$contact_email_display = function_exists("upsellio_obfuscate_email_address") ? upsellio_obfuscate_email_address($contact_email) : $contact_email;
$cities_url = function_exists("upsellio_get_cities_archive_url") ? (string) upsellio_get_cities_archive_url() : "";
$offer_url = function_exists("upsellio_get_offer_page_url") ? (string) upsellio_get_offer_page_url() : "";
$meta_ads_url = function_exists("upsellio_get_meta_ads_page_url") ? (string) upsellio_get_meta_ads_page_url() : "";
$websites_url = function_exists("upsellio_get_websites_page_url") ? (string) upsellio_get_websites_page_url() : "";

$faq_items = [
    [
        "question" => "Ile kosztuje prowadzenie Google Ads?",
        "answer" => "Na koszt kampanii Google Ads składa się budżet reklamowy płacony bezpośrednio do Google oraz wynagrodzenie za zarządzanie kampaniami. Ważniejsze od samego budżetu jest to, ile kosztuje pozyskanie jednego wartościowego leada i czy ten koszt jest opłacalny wobec wartości klienta.",
    ],
    [
        "question" => "Kiedy zacznę widzieć pierwsze efekty z Google Ads?",
        "answer" => "Przy kampaniach Search pierwsze zapytania mogą pojawić się już w ciągu pierwszych 1-2 tygodni, jeśli słowa kluczowe są dobrze dobrane, a strona docelowa konwertuje. Stabilniejsze wyniki zwykle pojawiają się po 6-10 tygodniach optymalizacji.",
    ],
    [
        "question" => "Czy mogę sam zarządzać Google Ads po konfiguracji?",
        "answer" => "Tak, ale Google Ads nie jest systemem ustaw i zapomnij. Bez bieżącej optymalizacji koszty zwykle rosną, a skuteczność spada. Jeśli chcesz samodzielnie prowadzić konto, mogę przygotować dokumentację i szkolenie.",
    ],
    [
        "question" => "Czy Google Ads wyklucza działania SEO?",
        "answer" => "Nie. Google Ads i SEO najlepiej działają razem. Google Ads daje ruch z intencją zakupową od razu, a SEO buduje widoczność długoterminowo. Dane z Google Ads pomagają też planować treści SEO.",
    ],
    [
        "question" => "Czy robisz też landing pages pod kampanie?",
        "answer" => "Analizuję strony docelowe i rekomenduję zmiany, które mogą poprawić konwersję. Jeśli potrzebujesz dedykowanego landing page'a pod Google Ads, mogę pomóc w jego stworzeniu w ramach szerszej współpracy.",
    ],
    [
        "question" => "Czy Google Ads dla małej firmy ma sens przy małym budżecie?",
        "answer" => "Tak, ale wymaga bardzo precyzyjnego zarządzania. Mały budżet nie może być marnowany na szerokie frazy, przypadkowy ruch i słabe strony docelowe. Kluczowe są konkretne słowa kluczowe, wykluczenia i dobra konwersja strony.",
    ],
    [
        "question" => "Automatyczne strategie stawek czy manualne CPC?",
        "answer" => "To zależy od etapu kampanii i liczby konwersji. Na początku większą kontrolę daje manualne lub ulepszone CPC. Po zebraniu danych można przejść na strategie automatyczne, takie jak Docelowy CPA lub Maksymalizacja konwersji.",
    ],
];
?>

<style>
  .gads-page {
    --gads-bg:#f8fafc;
    --gads-cream:#f1f5f9;
    --gads-surface:#fff;
    --gads-text:#071426;
    --gads-text-2:#334155;
    --gads-muted:#64748b;
    --gads-border:#e2e8f0;
    --gads-blue:#0d9488;
    --gads-blue-dark:#0f766e;
    --gads-blue-soft:#ecfeff;
    --gads-green:#14b8a6;
    --gads-green-soft:#ecfeff;
    --gads-yellow:#0d9488;
    --gads-red:#db4437;
    --gads-dark:#081827;
    --gads-shadow:0 24px 70px rgba(15,23,42,.12);
    --gads-shadow-soft:0 14px 40px rgba(15,23,42,.08);
    background:var(--gads-bg);
    color:var(--gads-text);
  }
  html { scroll-behavior:smooth; scroll-padding-top:140px; }
  .gads-wrap { width:min(1240px, calc(100% - 48px)); margin:0 auto; }
  .gads-section { padding:clamp(70px,8vw,112px) 0; }
  .gads-h1,.gads-h2,.gads-h3 { font-family:var(--font-display); line-height:1.04; letter-spacing:-1.3px; color:var(--gads-text); }
  .gads-h1 { font-size:clamp(42px,6vw,78px); max-width:1060px; }
  .gads-h2 { font-size:clamp(32px,4vw,54px); max-width:940px; }
  .gads-h3 { font-size:clamp(23px,3vw,34px); }
  .gads-page p { color:var(--gads-text-2); }
  .gads-lead { margin-top:24px; max-width:830px; font-size:clamp(18px,2vw,21px); line-height:1.75; }
  .gads-copy { margin-top:20px; max-width:960px; display:grid; gap:14px; }
  .gads-eyebrow { display:inline-flex; align-items:center; gap:10px; margin-bottom:18px; font-size:12px; font-weight:800; letter-spacing:1.6px; text-transform:uppercase; color:var(--gads-blue); }
  .gads-eyebrow::before { content:""; width:28px; height:2px; background:linear-gradient(90deg,var(--gads-blue),var(--gads-yellow)); border-radius:99px; }
  .gads-btn-row { display:flex; flex-wrap:wrap; gap:12px; margin-top:32px; }
  .gads-btn { min-height:50px; display:inline-flex; align-items:center; justify-content:center; border-radius:999px; padding:0 24px; font-size:15px; font-weight:800; transition:.2s ease; text-decoration:none; }
  .gads-btn-primary { background:linear-gradient(135deg,var(--gads-blue),var(--gads-green)); color:#fff; box-shadow:0 14px 28px rgba(13,148,136,.22); }
  .gads-btn-secondary { background:#fff; border:1px solid #cbd5e1; color:var(--gads-text); }
  .gads-btn-ghost { background:var(--gads-blue-soft); color:var(--gads-blue-dark); border:1px solid #99f6e4; }
  .gads-btn:hover { transform:translateY(-2px); }
  .gads-quick-nav { position:sticky; top:82px; z-index:70; background:rgba(255,255,255,.95); border-bottom:1px solid var(--gads-border); box-shadow:0 8px 20px rgba(16,18,15,.04); backdrop-filter:blur(12px); }
  .gads-quick-inner { min-height:58px; display:flex; align-items:center; justify-content:space-between; gap:18px; overflow-x:auto; scrollbar-width:none; }
  .gads-quick-inner::-webkit-scrollbar { display:none; }
  .gads-quick-links { display:flex; align-items:center; gap:10px; white-space:nowrap; }
  .gads-quick-links a { min-height:36px; display:inline-flex; align-items:center; padding:0 14px; border:1px solid var(--gads-border); border-radius:999px; font-size:13px; font-weight:700; color:var(--gads-text-2); background:#f8fafc; transition:.2s ease; }
  .gads-quick-links a:hover,.gads-quick-links a.is-active { color:var(--gads-blue-dark); border-color:#99f6e4; background:var(--gads-blue-soft); }
  .gads-quick-cta { flex:0 0 auto; min-height:38px; display:inline-flex; align-items:center; padding:0 16px; border-radius:999px; background:var(--gads-blue); color:#fff; font-size:13px; font-weight:800; white-space:nowrap; }
  .gads-hero { position:relative; overflow:hidden; padding:clamp(72px,8vw,124px) 0; border-bottom:1px solid var(--gads-border); background:linear-gradient(180deg,#fff,var(--gads-cream)); }
  .gads-hero::before { content:""; position:absolute; right:-150px; top:-180px; width:650px; height:650px; background:radial-gradient(circle,rgba(20,184,166,.14),transparent 65%); }
  .gads-hero::after { content:""; position:absolute; left:-120px; bottom:-180px; width:480px; height:480px; background:radial-gradient(circle,rgba(13,148,136,.18),transparent 65%); }
  .gads-hero-grid { position:relative; display:grid; grid-template-columns:minmax(0,1.12fr) minmax(330px,.88fr); gap:clamp(34px,5vw,64px); align-items:center; }
  .gads-search-card { border:1px solid var(--gads-border); border-radius:34px; background:#fff; overflow:hidden; box-shadow:var(--gads-shadow); }
  .gads-search-bar { display:flex; align-items:center; gap:10px; padding:18px; border-bottom:1px solid var(--gads-border); background:#f8fafc; color:var(--gads-muted); font-size:14px; }
  .gads-search-dots { display:inline-flex; align-items:center; gap:6px; margin-right:14px; }
  .gads-search-dot { width:11px; height:11px; border-radius:50%; display:inline-block; }
  .gads-search-dot--red { background:var(--gads-red); }
  .gads-search-dot--yellow { background:var(--gads-yellow); }
  .gads-search-dot--green { background:var(--gads-green); }
  .gads-serp { padding:24px; display:grid; gap:16px; }
  .gads-ad { padding:18px; border:1px solid var(--gads-border); border-radius:20px; background:#fff; }
  .gads-ad small { display:inline-flex; margin-bottom:8px; padding:4px 8px; border-radius:999px; background:var(--gads-blue-soft); color:var(--gads-blue-dark); font-weight:800; }
  .gads-ad strong { display:block; color:var(--gads-blue); font-size:18px; line-height:1.25; }
  .gads-ad p { margin-top:8px; font-size:14px; }
  .gads-intent-row { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; }
  .gads-intent { padding:14px; border:1px solid var(--gads-border); border-radius:16px; background:#f8fafc; }
  .gads-intent b { display:block; color:var(--gads-text); }
  .gads-intent span { display:block; margin-top:3px; color:var(--gads-muted); font-size:12px; }
  .gads-loss-grid { margin-top:38px; display:grid; grid-template-columns:1.1fr .9fr; gap:22px; align-items:start; }
  .gads-loss-panel { padding:32px; border-radius:30px; background:var(--gads-dark); color:#fff; box-shadow:var(--gads-shadow); }
  .gads-loss-panel .gads-h3 { color:#fff; }
  .gads-loss-panel p { color:rgba(255,255,255,.72); }
  .gads-waste-list { margin-top:22px; display:grid; gap:12px; list-style:none; padding:0; }
  .gads-waste-list li { padding:14px; border:1px solid rgba(255,255,255,.12); border-radius:16px; background:rgba(255,255,255,.06); color:rgba(255,255,255,.82); }
  .gads-card-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:16px; }
  .gads-card { position:relative; padding:24px; border:1px solid var(--gads-border); border-radius:24px; background:#fff; box-shadow:var(--gads-shadow-soft); transition:.2s ease; }
  .gads-card:hover { transform:translateY(-4px); border-color:#99f6e4; box-shadow:var(--gads-shadow); }
  .gads-card-icon { display:inline-flex; align-items:center; justify-content:center; width:40px; height:40px; border-radius:12px; background:var(--gads-blue-soft); color:var(--gads-blue-dark); margin-bottom:12px; }
  .gads-card-icon svg { width:22px; height:22px; }
  .gads-card strong { display:block; margin-bottom:8px; color:var(--gads-text); font-size:17px; }
  .gads-card p { font-size:15px; }
  .gads-service { background:#fff; border-top:1px solid var(--gads-border); border-bottom:1px solid var(--gads-border); }
  .gads-layers { margin-top:40px; display:grid; grid-template-columns:1fr 1fr; gap:18px; }
  .gads-layer { position:relative; padding:28px; border:1px solid var(--gads-border); border-radius:26px; background:#f8fafc; }
  .gads-layer.is-start { background:#fff; border-top:3px solid var(--gads-blue); box-shadow:var(--gads-shadow-soft); }
  .gads-layer.is-start::before { content:"Start"; position:absolute; top:-12px; left:24px; padding:4px 10px; border-radius:999px; background:var(--gads-blue); color:#fff; font-size:11px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; box-shadow:0 6px 16px -6px rgba(13,148,136,.5); }
  .gads-layer .gads-h3 { margin-bottom:14px; }
  .gads-screenshot-slot { margin-top:42px; padding:0; border:1px dashed var(--gads-border); border-radius:24px; background:#fff; overflow:hidden; }
  .gads-screenshot-head { display:flex; align-items:center; gap:10px; padding:14px 18px; background:#f8fafc; border-bottom:1px solid var(--gads-border); }
  .gads-screenshot-head .gads-search-dots { margin-right:8px; }
  .gads-screenshot-head-label { font-size:11px; letter-spacing:.16em; text-transform:uppercase; font-weight:700; color:var(--gads-muted); }
  .gads-screenshot-body { display:grid; grid-template-columns:1fr; gap:18px; align-items:center; padding:24px 26px; }
  .gads-screenshot-mock { display:grid; gap:10px; }
  .gads-screenshot-row { display:grid; grid-template-columns:auto 1fr 1fr 1fr 1fr; gap:8px; align-items:center; padding:10px 12px; border-radius:10px; background:#f8fafc; border:1px solid var(--gads-border); }
  .gads-screenshot-row.is-head { font-size:10px; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:var(--gads-muted); background:#fff; }
  .gads-screenshot-cell { height:8px; border-radius:6px; background:linear-gradient(90deg,#e2e8f0,#f1f5f9); }
  .gads-screenshot-cell.short { width:60%; }
  .gads-screenshot-cell.value { background:linear-gradient(90deg,#a7f3d0,#5eead4); height:10px; }
  .gads-screenshot-cell.value.warn { background:linear-gradient(90deg,#fde68a,#fbbf24); }
  .gads-screenshot-key { width:14px; height:14px; border-radius:4px; background:var(--gads-blue-soft); border:1px solid #99f6e4; }
  .gads-screenshot-note { font-size:13px; line-height:1.6; color:var(--gads-text-2); }
  .gads-screenshot-note strong { color:var(--gads-text); display:block; margin-bottom:6px; font-size:14px; }
  @media(min-width:760px){ .gads-screenshot-body { grid-template-columns:1.4fr .9fr; } }
  .gads-check-list { display:grid; gap:10px; list-style:none; padding:0; }
  .gads-check-list li { position:relative; padding-left:26px; font-size:15px; color:var(--gads-text-2); }
  .gads-check-list li::before { content:"✓"; position:absolute; left:0; color:var(--gads-blue); font-weight:900; }
  .gads-fit-grid { margin-top:38px; display:grid; grid-template-columns:repeat(3,1fr); gap:18px; }
  .gads-fit { padding:28px; border-radius:28px; background:#fff; border:1px solid var(--gads-border); box-shadow:var(--gads-shadow-soft); }
  .gads-fit-icon { display:inline-flex; align-items:center; justify-content:center; width:48px; height:48px; margin-bottom:16px; border-radius:14px; background:var(--gads-blue-soft); color:var(--gads-blue-dark); }
  .gads-fit-icon svg { width:24px; height:24px; }
  .gads-fit b { display:inline-flex; margin-bottom:18px; min-width:42px; height:42px; align-items:center; justify-content:center; border-radius:14px; background:var(--gads-blue-soft); color:var(--gads-blue-dark); font-family:var(--font-display); }
  .gads-fit strong { display:block; margin-bottom:10px; color:var(--gads-text); font-size:18px; }
  .gads-campaign-types { margin-top:40px; display:grid; grid-template-columns:1.08fr .92fr; gap:22px; }
  .gads-type-large { padding:32px; border-radius:30px; background:linear-gradient(145deg,var(--gads-blue-dark),#081827); color:#fff; box-shadow:var(--gads-shadow); }
  .gads-type-large .gads-h3 { color:#fff; }
  .gads-type-large p { color:rgba(255,255,255,.74); }
  .gads-type-stack { display:grid; gap:14px; }
  .gads-type-card { padding:24px; border:1px solid var(--gads-border); border-radius:24px; background:#fff; box-shadow:var(--gads-shadow-soft); }
  .gads-type-card strong { display:block; color:var(--gads-text); margin-bottom:8px; }
  .gads-process { background:linear-gradient(180deg,#fff,var(--gads-cream)); border-top:1px solid var(--gads-border); border-bottom:1px solid var(--gads-border); }
  .gads-process-grid { position:relative; margin-top:38px; display:grid; grid-template-columns:repeat(4,1fr); gap:14px; }
  .gads-process-grid::before { content:""; display:none; position:absolute; top:48px; left:8%; right:8%; height:2px; background:linear-gradient(90deg,var(--gads-blue-soft),#99f6e4 12%,#99f6e4 88%,var(--gads-blue-soft)); border-radius:2px; }
  .gads-step { position:relative; z-index:1; padding:24px; border:1px solid var(--gads-border); border-radius:24px; background:#fff; box-shadow:var(--gads-shadow-soft); }
  .gads-step-head { display:flex; align-items:center; gap:12px; margin-bottom:16px; }
  .gads-step-num { width:34px; height:34px; display:grid; place-items:center; border-radius:50%; background:var(--gads-blue-soft); color:var(--gads-blue-dark); font-family:var(--font-display); font-weight:800; flex-shrink:0; }
  .gads-step-icon { display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; border-radius:8px; background:#f1f5f9; color:var(--gads-blue-dark); }
  .gads-step-icon svg { width:18px; height:18px; }
  .gads-step strong { display:block; margin-bottom:8px; color:var(--gads-text); }
  .gads-step p { font-size:14px; }
  @media(min-width:1024px){ .gads-process-grid::before { display:block; } }
  .gads-effects { display:grid; grid-template-columns:.92fr 1.08fr; gap:28px; align-items:start; }
  .gads-kpi-box { padding:30px; border-radius:30px; background:#fff; border:1px solid var(--gads-border); box-shadow:var(--gads-shadow); }
  .gads-kpi-row { display:grid; gap:12px; margin-top:20px; }
  .gads-kpi { padding:16px 18px; border-radius:18px; background:#fff; border:1px solid var(--gads-border); }
  .gads-kpi-label { display:block; font-size:11px; font-weight:800; letter-spacing:.16em; text-transform:uppercase; color:var(--gads-muted); }
  .gads-kpi-value { display:block; margin-top:4px; font-family:var(--font-display); font-size:26px; line-height:1; letter-spacing:-.02em; color:var(--gads-blue); font-weight:800; }
  .gads-kpi-context { display:block; margin-top:6px; font-size:12px; color:var(--gads-text-2); line-height:1.55; }
  .gads-kpi strong { display:block; color:var(--gads-blue); font-size:22px; }
  .gads-effects-list { display:grid; gap:10px; list-style:none; padding:0; }
  .gads-effects-list li { position:relative; padding-left:30px; color:var(--gads-text-2); }
  .gads-effects-list li::before { content:"✓"; position:absolute; left:0; color:var(--gads-blue); font-weight:900; }
  .gads-faq-grid { margin-top:38px; display:grid; gap:14px; max-width:960px; }
  .gads-page details { border:1px solid var(--gads-border); border-radius:18px; background:#fff; padding:20px 22px; box-shadow:var(--gads-shadow-soft); }
  .gads-page summary { cursor:pointer; font-weight:800; color:var(--gads-text); }
  .gads-page details p { margin-top:12px; font-size:15px; }
  .gads-final { text-align:center; padding:clamp(42px,5vw,64px); border:1px solid #99f6e4; border-radius:32px; background:radial-gradient(circle at top,#ecfeff,#fff 62%); box-shadow:var(--gads-shadow-soft); }
  .gads-final .gads-h2 { margin:0 auto; }
  .gads-final p { max-width:850px; margin:20px auto 0; font-size:18px; }
  .gads-final .gads-btn-row { justify-content:center; }
  .gads-internal-links { margin-top:22px; display:flex; flex-wrap:wrap; justify-content:center; gap:10px; }
  .gads-internal-links a { display:inline-flex; min-height:38px; align-items:center; border:1px solid var(--gads-border); border-radius:999px; padding:0 14px; background:#fff; color:var(--gads-blue-dark); font-size:13px; font-weight:800; }
  @media(max-width:980px){
    html { scroll-padding-top:130px; }
    .gads-hero-grid,.gads-loss-grid,.gads-layers,.gads-campaign-types,.gads-effects { grid-template-columns:1fr; }
    .gads-fit-grid { grid-template-columns:1fr 1fr; }
    .gads-process-grid { grid-template-columns:repeat(2,1fr); }
  }
  @media(max-width:620px){
    html { scroll-padding-top:125px; }
    .gads-wrap { width:min(100% - 28px,1240px); }
    .gads-card-grid,.gads-fit-grid,.gads-process-grid,.gads-intent-row { grid-template-columns:1fr; }
    .gads-btn { width:100%; }
    .gads-quick-cta { display:none; }
    .gads-quick-inner { padding:10px 0; }
  }
  /* Mobile-first UX correction layer */
  .gads-section { padding:48px 0; }
  .gads-hero { padding:52px 0 46px; }
  .gads-h1 { font-size:clamp(34px,10vw,40px); line-height:1.09; letter-spacing:-1px; }
  .gads-h2 { font-size:clamp(28px,8vw,34px); line-height:1.12; letter-spacing:-.8px; }
  .gads-h3 { font-size:clamp(21px,6vw,26px); line-height:1.16; letter-spacing:-.5px; }
  .gads-lead { margin-top:16px; font-size:17px; line-height:1.65; }
  .gads-copy { margin-top:14px; gap:10px; }
  .gads-copy p { line-height:1.72; }
  .gads-btn-row { margin-top:22px; }
  .gads-quick-nav { position:static; }
  .gads-quick-inner { min-height:auto; padding:10px 0; }
  .gads-hero-grid,.gads-loss-grid,.gads-card-grid,.gads-layers,.gads-fit-grid,.gads-campaign-types,.gads-process-grid,.gads-effects,.gads-intent-row { grid-template-columns:1fr; }
  .gads-search-card,.gads-loss-panel,.gads-card,.gads-layer,.gads-fit,.gads-type-large,.gads-type-card,.gads-step,.gads-kpi-box,.gads-final { border-radius:20px; }
  .gads-loss-panel,.gads-layer,.gads-fit,.gads-type-large,.gads-kpi-box,.gads-final { padding:20px; }
  .gads-card,.gads-type-card,.gads-step { padding:18px; }
  .gads-serp { padding:18px; }
  @media(min-width:760px){
    .gads-section { padding:72px 0; }
    .gads-hero { padding:76px 0 68px; }
    .gads-quick-nav { position:sticky; }
    .gads-h1 { font-size:clamp(44px,6vw,58px); line-height:1.05; }
    .gads-h2 { font-size:clamp(34px,4vw,46px); }
    .gads-h3 { font-size:clamp(23px,3vw,30px); }
    .gads-card-grid,.gads-layers,.gads-fit-grid,.gads-process-grid,.gads-intent-row { grid-template-columns:repeat(2,minmax(0,1fr)); }
  }
  @media(min-width:1024px){
    .gads-section { padding:96px 0; }
    .gads-hero { padding:96px 0 88px; }
    .gads-h1 { font-size:66px; }
    .gads-h2 { font-size:50px; }
    .gads-hero-grid { grid-template-columns:minmax(0,1.05fr) minmax(300px,.82fr); }
    .gads-loss-grid,.gads-campaign-types,.gads-effects { grid-template-columns:1.05fr .95fr; }
    .gads-fit-grid { grid-template-columns:repeat(3,minmax(0,1fr)); }
    .gads-process-grid { grid-template-columns:repeat(4,minmax(0,1fr)); }
    .gads-loss-panel,.gads-layer,.gads-fit,.gads-type-large,.gads-kpi-box { padding:26px; }
  }
</style>

<main class="gads-page">
  <div class="gads-quick-nav">
    <div class="gads-wrap gads-quick-inner">
      <div class="gads-quick-links">
        <a href="#start">Start</a>
        <a href="#problemy">Budżet</a>
        <a href="#zakres">Zakres</a>
        <a href="#lokalnie">Lokalnie</a>
        <a href="#dla-kogo">Dla kogo</a>
        <a href="#typy">Typy kampanii</a>
        <a href="#proces">Proces</a>
        <a href="#faq">FAQ</a>
      </div>
      <a href="#kontakt" class="gads-quick-cta">Sprawdź koszt leada</a>
    </div>
  </div>

  <section class="gads-hero" id="start">
    <div class="gads-wrap gads-hero-grid">
      <div>
        <span class="gads-eyebrow">Google Ads i intencja zakupowa</span>
        <h1 class="gads-h1">Google Ads dla firm: kampanie Search i Performance Max, które docierają do klientów z wysoką intencją zakupową.</h1>
        <p class="gads-lead">Tworzenie i prowadzenie kampanii Google Ads dla firm B2B, usługowych i e-commerce. Słowa kluczowe z intencją zakupową, landing pages pod konwersję i systematyczna optymalizacja kosztu pozyskania leada.</p>
        <div class="gads-copy">
          <p>Google Ads pozwala dotrzeć do klientów dokładnie wtedy, gdy szukają rozwiązania. Ktoś wpisuje „dostawca opakowań przemysłowych”, „agencja rekrutacyjna Warszawa” albo „serwis maszyn CNC Śląsk” i jest bliżej decyzji niż osoba przypadkowo scrollująca social media.</p>
          <p>Skuteczna kampania Google Ads to precyzyjny dobór słów kluczowych z wysoką intencją zakupową, wykluczenie fraz generujących drogi ruch, spójność reklamy ze stroną docelową, poprawne śledzenie konwersji i ciągła optymalizacja.</p>
        </div>
        <div class="gads-btn-row">
          <a href="#kontakt" class="gads-btn gads-btn-primary">Chcę bezpłatną diagnozę</a>
          <a href="#zakres" class="gads-btn gads-btn-secondary">Zobacz zakres usługi</a>
          <a href="#typy" class="gads-btn gads-btn-ghost">Search czy PMax?</a>
        </div>
      </div>

      <aside class="gads-search-card" aria-label="Przykład intencji w Google Ads">
          <div class="gads-search-bar"><span class="gads-search-dots" aria-hidden="true"><span class="gads-search-dot gads-search-dot--red"></span><span class="gads-search-dot gads-search-dot--yellow"></span><span class="gads-search-dot gads-search-dot--green"></span></span>serwis maszyn cnc śląsk</div>
        <div class="gads-serp">
          <div class="gads-ad">
            <small>Reklama</small>
            <strong>Serwis maszyn CNC dla firm produkcyjnych</strong>
            <p>Krótki formularz, szybka diagnoza, konkretny kontakt. To kliknięcie ma intencję, dlatego nie można go zmarnować słabą stroną.</p>
          </div>
          <div class="gads-intent-row">
            <div class="gads-intent"><b>Search</b><span>istniejący popyt</span></div>
            <div class="gads-intent"><b>CPL</b><span>koszt leada</span></div>
            <div class="gads-intent"><b>Quality</b><span>jakość ruchu</span></div>
          </div>
        </div>
      </aside>
    </div>
  </section>

  <section class="gads-section" id="lokalnie">
    <div class="gads-wrap">
      <span class="gads-eyebrow">Google Ads lokalnie i ogólnopolsko</span>
      <h2 class="gads-h2">Obsługuję kampanie Google Ads dla firm z całej Polski, z naciskiem na lokalną intencję wyszukiwania.</h2>
      <div class="gads-copy">
        <p>W wielu branżach usługowych i B2B zapytania typu „Google Ads Poznań”, „agencja Google Ads Trójmiasto” albo „kampanie Google Ads Warszawa” mają niższą konkurencję niż ogólne frazy. Dlatego przy kampaniach i treściach sprawdzam nie tylko kanał reklamowy, ale też potencjał lokalnych podstron, wyników organicznych i dopasowania landing page do miasta lub regionu.</p>
        <p>Jeśli działasz lokalnie, kampania powinna uwzględniać promień działania, frazy z miastami, wykluczenia lokalizacji i osobne komunikaty dla klientów z konkretnego regionu. Jeśli sprzedajesz ogólnopolsko, lokalne frazy nadal mogą wspierać SEO i obniżać koszt pozyskania ruchu z wysoką intencją.</p>
      </div>
      <div class="gads-btn-row">
        <?php if ($cities_url !== "") : ?><a href="<?php echo esc_url($cities_url); ?>" class="gads-btn gads-btn-ghost">Zobacz podstrony miast</a><?php endif; ?>
        <?php if ($offer_url !== "") : ?><a href="<?php echo esc_url($offer_url); ?>" class="gads-btn gads-btn-secondary">Zobacz pełną ofertę</a><?php endif; ?>
      </div>
    </div>
  </section>

  <section class="gads-section" id="problemy">
    <div class="gads-wrap">
      <span class="gads-eyebrow">Gdzie ucieka budżet</span>
      <h2 class="gads-h2">Dlaczego kampanie Google Ads nie generują wartościowych zapytań?</h2>
      <div class="gads-copy">
        <p>Google Ads ma opinię drogiego kanału. Bywa drogi nie dlatego, że platforma jest zła, ale dlatego, że wiele kampanii generuje kliknięcia bez konwersji. Koszt kliknięcia jest wysoki, a koszt wartościowego leada jeszcze wyższy, gdy duża część ruchu nie składa się z potencjalnych klientów.</p>
        <p>Najczęściej problemem są zbyt ogólne słowa kluczowe, brak wykluczeń, niespójność między reklamą a stroną docelową, słaby Quality Score i brak poprawnego śledzenia konwersji.</p>
      </div>

      <div class="gads-loss-grid">
        <div class="gads-loss-panel">
          <h3 class="gads-h3">Budżet nie przepala się naraz. Ucieka przez małe nieszczelności.</h3>
          <p>Audyt kampanii pokazuje, które zapytania, reklamy, strony i ustawienia generują koszt bez realnego potencjału sprzedażowego.</p>
          <ul class="gads-waste-list">
            <li>Ogólne frazy bez intencji zakupu.</li>
            <li>Brak listy słów wykluczających.</li>
            <li>Reklama obiecuje jedno, a strona pokazuje coś innego.</li>
            <li>Konwersje nie są mierzone lub są źle skonfigurowane.</li>
          </ul>
        </div>

          <div class="gads-card-grid">
          <div class="gads-card">
            <div class="gads-card-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </div>
            <strong>Zły dobór słów kluczowych</strong>
            <p>Szerokie frazy generują ruch od osób, które dopiero orientują się w temacie, a nie są gotowe na kontakt.</p>
          </div>
          <div class="gads-card">
            <div class="gads-card-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
            </div>
            <strong>Brak wykluczeń</strong>
            <p>Kampania bez wykluczeń wyświetla reklamy na przypadkowe i kosztowne zapytania.</p>
          </div>
          <div class="gads-card">
            <div class="gads-card-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            </div>
            <strong>Słaby landing page</strong>
            <p>Użytkownik klika reklamę, ale strona nie kontynuuje przekazu i nie prowadzi do CTA.</p>
          </div>
          <div class="gads-card">
            <div class="gads-card-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            </div>
            <strong>Brak danych o konwersjach</strong>
            <p>Bez wiedzy, które kliknięcia dają leady, optymalizacja opiera się na domysłach.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="gads-section gads-service" id="zakres">
    <div class="gads-wrap">
      <span class="gads-eyebrow">Pełny zakres usługi</span>
      <h2 class="gads-h2">Google Ads od audytu i strategii, przez strukturę kampanii i landing pages, do optymalizacji oraz raportowania sprzedażowego.</h2>
      <div class="gads-copy">
        <p>Prowadzenie kampanii Google Ads zaczyna się długo przed kliknięciem w panel reklamowy. Kampania musi być zaplanowana pod konkretny cel sprzedażowy, zbudowana z odpowiednią strukturą i stale optymalizowana na podstawie danych.</p>
      </div>

      <div class="gads-layers">
        <div class="gads-layer is-start">
          <h3 class="gads-h3">Audyt i strategia</h3>
          <ul class="gads-check-list">
            <li>analiza struktury konta, słów kluczowych i wykluczeń</li>
            <li>ocena Quality Score, stron docelowych i konwersji</li>
            <li>identyfikacja miejsc, gdzie budżet jest tracony</li>
            <li>plan kampanii Search, PMax i remarketingu</li>
          </ul>
        </div>
        <div class="gads-layer">
          <h3 class="gads-h3">Słowa kluczowe z intencją zakupową</h3>
          <ul class="gads-check-list">
            <li>selekcja konkretnych fraz sprzedażowych</li>
            <li>ochrona budżetu przez listy słów wykluczających</li>
            <li>analiza search terms i nowych okazji</li>
            <li>unikanie ogólnych fraz niskiej jakości</li>
          </ul>
        </div>
        <div class="gads-layer">
          <h3 class="gads-h3">Search i Performance Max</h3>
          <ul class="gads-check-list">
            <li>kampanie Search dla istniejącego popytu</li>
            <li>Performance Max dla e-commerce i kont z danymi</li>
            <li>remarketing w Display i sieci Google</li>
            <li>dobór typu kampanii do celu, nie do mody</li>
          </ul>
        </div>
        <div class="gads-layer">
          <h3 class="gads-h3">Landing pages, konwersje i raporty</h3>
          <ul class="gads-check-list">
            <li>rekomendacje do strony docelowej pod konwersję</li>
            <li>śledzenie formularzy, telefonów, zakupów i zdarzeń</li>
            <li>optymalizacja stawek, reklam, grup i stron</li>
            <li>raportowanie liczby leadów, CPL, jakości kontaktów i trendów</li>
          </ul>
        </div>
      </div>

      <?php
      $gads_screenshot_url = function_exists("upsellio_get_template_asset_url") ? upsellio_get_template_asset_url("service_google_screenshot", "large") : "";
      $gads_screenshot_caption = function_exists("upsellio_template_asset_caption") ? upsellio_template_asset_caption("service_google_screenshot") : "";
      ?>
      <div class="gads-screenshot-slot" aria-label="Przykładowy widok z panelu Google Ads (zanonimizowany)">
        <div class="gads-screenshot-head">
          <span class="gads-search-dots" aria-hidden="true"><span class="gads-search-dot gads-search-dot--red"></span><span class="gads-search-dot gads-search-dot--yellow"></span><span class="gads-search-dot gads-search-dot--green"></span></span>
          <span class="gads-screenshot-head-label">Panel Google Ads · widok zanonimizowany</span>
        </div>
        <div class="gads-screenshot-body">
          <?php if ($gads_screenshot_url !== "") : ?>
            <img src="<?php echo esc_url($gads_screenshot_url); ?>" alt="Panel kampanii Google Ads" loading="lazy" decoding="async" style="width:100%;height:auto;display:block;border-bottom:1px solid var(--gads-border);" />
          <?php else : ?>
          <div class="gads-screenshot-mock" aria-hidden="true">
            <div class="gads-screenshot-row is-head">
              <span></span><span>Kampania</span><span>Kliknięcia</span><span>CPL</span><span>Konwersje</span>
            </div>
            <div class="gads-screenshot-row">
              <span class="gads-screenshot-key"></span>
              <span class="gads-screenshot-cell"></span>
              <span class="gads-screenshot-cell short"></span>
              <span class="gads-screenshot-cell value"></span>
              <span class="gads-screenshot-cell short"></span>
            </div>
            <div class="gads-screenshot-row">
              <span class="gads-screenshot-key"></span>
              <span class="gads-screenshot-cell short"></span>
              <span class="gads-screenshot-cell"></span>
              <span class="gads-screenshot-cell value"></span>
              <span class="gads-screenshot-cell"></span>
            </div>
            <div class="gads-screenshot-row">
              <span class="gads-screenshot-key"></span>
              <span class="gads-screenshot-cell"></span>
              <span class="gads-screenshot-cell"></span>
              <span class="gads-screenshot-cell value warn"></span>
              <span class="gads-screenshot-cell short"></span>
            </div>
          </div>
          <?php endif; ?>
          <div class="gads-screenshot-note">
            <strong>Co czytamy z panelu</strong>
            <?php if ($gads_screenshot_caption !== "") : ?>
              <?php echo esc_html($gads_screenshot_caption); ?>
            <?php else : ?>
              Co tydzień patrzę na search terms, CPL per kampania, jakość konwersji i Quality Score. Dane z konta klienta omawiamy w cyklu raportów; powyżej widok poglądowy z zanonimizowanymi etykietami.
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="gads-section" id="dla-kogo">
    <div class="gads-wrap">
      <span class="gads-eyebrow">Dla kogo</span>
      <h2 class="gads-h2">Google Ads ma sens, gdy klienci już szukają Twojej usługi lub produktu w wyszukiwarce.</h2>
      <div class="gads-copy">
        <p>Jeżeli ktoś wpisuje w Google „outsourcing IT Kraków”, „producent mebli na wymiar Gdańsk” czy „prawnik od umów handlowych Wrocław”, to jest klient z intencją zakupową. Google Ads pozwala być widocznym dokładnie w tym momencie.</p>
        <p>Ten kanał działa szczególnie dobrze dla firm B2B, usług lokalnych i e-commerce, gdy istnieje aktywny popyt. Jeśli rynek trzeba najpierw edukować, lepszym pierwszym krokiem może być <?php if ($meta_ads_url !== "") : ?><a href="<?php echo esc_url($meta_ads_url); ?>">Meta Ads dla firm</a><?php else : ?>Meta Ads dla firm<?php endif; ?>.</p>
      </div>

      <div class="gads-fit-grid">
        <div class="gads-fit">
          <div class="gads-fit-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21V7l9-4 9 4v14"/><path d="M9 21V12h6v9"/><path d="M3 21h18"/></svg>
          </div>
          <strong>Firmy B2B</strong>
          <p>Produkcja, IT, logistyka, finanse, doradztwo i prawo, gdzie pojedynczy klient ma wysoką wartość.</p>
        </div>
        <div class="gads-fit">
          <div class="gads-fit-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          </div>
          <strong>Lokalne usługi</strong>
          <p>Precyzyjne dotarcie do osób z miasta, regionu lub dzielnicy, które aktywnie szukają usługi.</p>
        </div>
        <div class="gads-fit">
          <div class="gads-fit-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
          </div>
          <strong>E-commerce</strong>
          <p>Shopping, Performance Max i optymalizacja pod ROAS przy kontrolowanym koszcie sprzedaży.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="gads-section" id="typy">
    <div class="gads-wrap">
      <span class="gads-eyebrow">Typy kampanii</span>
      <h2 class="gads-h2">Search, Performance Max czy Display: który typ Google Ads przyniesie najlepsze wyniki?</h2>
      <div class="gads-campaign-types">
        <div class="gads-type-large">
          <h3 class="gads-h3">Kampanie Search: najbliżej decyzji zakupowej.</h3>
          <p>Search Ads to najbardziej bezpośredni typ Google Ads. Reklamy tekstowe pojawiają się wtedy, gdy użytkownik wpisuje frazę związaną z Twoją usługą lub produktem. To pierwszy wybór dla firm B2B i usługowych, które chcą przechwytywać istniejący popyt.</p>
          <p>W Search najważniejsze są intencja słowa kluczowego, dopasowanie komunikatu do zapytania, wykluczenia i landing page, który szybko zamienia kliknięcie w kontakt.</p>
        </div>
        <div class="gads-type-stack">
          <div class="gads-type-card"><strong>Performance Max</strong><p>Łączy Search, Display, YouTube, Gmail i Maps. Dobrze działa dla e-commerce oraz kont z wystarczającą liczbą danych konwersji.</p></div>
          <div class="gads-type-card"><strong>Display i remarketing</strong><p>Wraca do osób, które odwiedziły stronę lub kliknęły reklamy. Najlepiej sprawdza się jako element domykania decyzji.</p></div>
          <div class="gads-type-card"><strong>Dobór formatu</strong><p>Typ kampanii zależy od celu, branży, etapu rynkowego i dostępności danych, a nie od tego, co jest aktualnie popularne.</p></div>
        </div>
      </div>
    </div>
  </section>

  <section class="gads-section gads-process" id="proces">
    <div class="gads-wrap">
      <span class="gads-eyebrow">Proces współpracy</span>
      <h2 class="gads-h2">Od pierwszej rozmowy do mierzalnych wyników sprzedażowych.</h2>
      <div class="gads-copy">
        <p>Każda współpraca przy Google Ads zaczyna się od diagnozy, a nie od uruchomienia kampanii. Muszę zrozumieć, co sprzedajesz, kto jest Twoim klientem, jaki jest średni zysk na kliencie, czy masz aktywne kampanie i jaka jest jakość strony docelowej.</p>
      </div>
      <div class="gads-process-grid">
        <div class="gads-step">
          <div class="gads-step-head">
            <span class="gads-step-num">1</span>
            <span class="gads-step-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
          </div>
          <strong>Audyt i diagnoza</strong>
          <p>Konto, słowa kluczowe, wykluczenia, reklamy, Quality Score, landing pages i konwersje.</p>
        </div>
        <div class="gads-step">
          <div class="gads-step-head">
            <span class="gads-step-num">2</span>
            <span class="gads-step-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg></span>
          </div>
          <strong>Strategia</strong>
          <p>Typ kampanii, budżet, struktura konta, KPI, słowa kluczowe i wykluczenia.</p>
        </div>
        <div class="gads-step">
          <div class="gads-step-head">
            <span class="gads-step-num">3</span>
            <span class="gads-step-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg></span>
          </div>
          <strong>Wdrożenie</strong>
          <p>Kampanie, grupy reklam, rozszerzenia, stawki, budżety i śledzenie konwersji.</p>
        </div>
        <div class="gads-step">
          <div class="gads-step-head">
            <span class="gads-step-num">4</span>
            <span class="gads-step-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 17 9 11 13 15 21 7"/><polyline points="14 7 21 7 21 14"/></svg></span>
          </div>
          <strong>Optymalizacja</strong>
          <p>Search terms, nowe wykluczenia, testy komunikatów, stawki, raporty i landing pages.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="gads-section" id="efekty">
    <div class="gads-wrap gads-effects">
      <div>
        <span class="gads-eyebrow">Efekty</span>
        <h2 class="gads-h2">Nie aktywna kampania, tylko przewidywalny system pozyskiwania klientów z wyszukiwarki.</h2>
        <div class="gads-copy">
          <p>Dobrze prowadzone kampanie Google Ads przestają być źródłem niekontrolowanych wydatków, a stają się kanałem, który można skalować, optymalizować i rozumieć.</p>
          <p>Nie obiecuję konkretnych liczb przed audytem, bo wyniki zależą od branży, oferty, budżetu, jakości strony i poziomu konkurencji. Po analizie mogę podać realistyczne oczekiwania i ramy czasowe.</p>
        </div>
      </div>
      <div class="gads-kpi-box">
        <h3 class="gads-h3">Realne benchmarki, na których pracujemy</h3>
        <div class="gads-kpi-row">
          <div class="gads-kpi">
            <span class="gads-kpi-label">CPL</span>
            <span class="gads-kpi-value">37–89 zł</span>
            <span class="gads-kpi-context">Średni koszt wartościowego leada w kampaniach B2B (usługi i IT, 2024).</span>
          </div>
          <div class="gads-kpi">
            <span class="gads-kpi-label">Quality Score</span>
            <span class="gads-kpi-value">+2 / +3 pkt</span>
            <span class="gads-kpi-context">Typowa poprawa jakości fraz po pierwszym cyklu optymalizacji i nowych landingach.</span>
          </div>
          <div class="gads-kpi">
            <span class="gads-kpi-label">ROAS (e-com)</span>
            <span class="gads-kpi-value">300–600%</span>
            <span class="gads-kpi-context">Realistyczny zakres dla sklepów z marżą i poprawnym śledzeniem zakupów.</span>
          </div>
        </div>
        <ul class="gads-effects-list" style="margin-top:22px;">
          <li>Niższy koszt pozyskania wartościowego leada względem obecnych wyników lub benchmarków.</li>
          <li>Pełna widoczność, które słowa kluczowe, reklamy i grupy generują konwersje.</li>
          <li>Remarketing do osób, które odwiedziły stronę, ale nie zostawiły kontaktu.</li>
          <li>Czytelne raportowanie z konkretnymi wnioskami, a nie tylko tabelkami.</li>
        </ul>
      </div>
    </div>
  </section>

  <section class="gads-section" id="faq">
    <div class="gads-wrap">
      <span class="gads-eyebrow">FAQ</span>
      <h2 class="gads-h2">Najczęstsze pytania przed rozpoczęciem kampanii Google Ads.</h2>
      <div class="gads-faq-grid">
        <?php foreach ($faq_items as $faq_item) : ?>
          <details>
            <summary><?php echo esc_html((string) $faq_item["question"]); ?></summary>
            <p><?php echo esc_html((string) $faq_item["answer"]); ?></p>
          </details>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="gads-section" id="kontakt">
    <div class="gads-wrap">
      <div class="gads-final">
        <h2 class="gads-h2">Chcesz sprawdzić, czy Google Ads ma sens w Twojej firmie i ile naprawdę powinien kosztować wartościowy lead?</h2>
        <p>Zanim zainwestujesz kolejny budżet w kampanie Google Ads albo uruchomisz reklamy po raz pierwszy, warto wiedzieć, czy Twoja strona jest gotowa na konwersję i czy słowa kluczowe mają realną intencję zakupową.</p>
        <p>Napisz, co sprzedajesz, do kogo kierujesz ofertę i co dzisiaj nie działa: za mało zapytań, za drogi lead, niskiej jakości kontakty albo brak widoczności na ważne frazy. Odpowiem konkretnie, czy Google Ads jest właściwym kanałem i od czego zacząć, żeby nie przepalić budżetu.</p>
        <div class="gads-btn-row">
          <a href="<?php echo esc_url($contact_email_href); ?>" class="gads-btn gads-btn-primary">Napisz: <?php echo esc_html($contact_email_display); ?></a>
          <a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $contact_phone)); ?>" class="gads-btn gads-btn-secondary">Zadzwoń: <?php echo esc_html($contact_phone); ?></a>
        </div>
        <div class="gads-internal-links" aria-label="Powiązane usługi">
          <?php if ($offer_url !== "") : ?><a href="<?php echo esc_url($offer_url); ?>">Pełna oferta marketingowa</a><?php endif; ?>
          <?php if ($meta_ads_url !== "") : ?><a href="<?php echo esc_url($meta_ads_url); ?>">Meta Ads dla firm</a><?php endif; ?>
          <?php if ($websites_url !== "") : ?><a href="<?php echo esc_url($websites_url); ?>">Tworzenie stron pod konwersję</a><?php endif; ?>
          <a href="#faq">Pytania o Google Ads</a>
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
<script type="application/ld+json">
<?php
echo wp_json_encode([
    "@context" => "https://schema.org",
    "@type" => "BreadcrumbList",
    "itemListElement" => [
        ["@type" => "ListItem", "position" => 1, "name" => "Strona główna", "item" => home_url("/")],
        ["@type" => "ListItem", "position" => 2, "name" => "Oferta", "item" => $offer_url],
        ["@type" => "ListItem", "position" => 3, "name" => "Google Ads", "item" => $url],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
?>
</script>
<?php endif; ?>
<?php
if (function_exists("upsellio_render_service_schema")) {
    upsellio_render_service_schema(
        "Kampanie Google Ads dla firm",
        "Prowadzenie kampanii Google Ads Search i Performance Max dla firm B2B, usługowych i e-commerce.",
        "/marketing-google-ads/",
        "Google Ads"
    );
}
?>

<script>
  (function () {
    const quickLinks = Array.from(document.querySelectorAll(".gads-quick-links a"));
    const sections = quickLinks.map((link) => document.querySelector(link.getAttribute("href"))).filter(Boolean);

    function setActiveQuickLink() {
      let current = "";
      sections.forEach((section) => {
        if (window.scrollY >= section.offsetTop - 170) current = "#" + section.id;
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
