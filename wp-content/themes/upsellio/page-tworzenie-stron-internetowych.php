<?php
/*
Template Name: Upsellio - Tworzenie Stron
Template Post Type: page
*/
if (!defined("ABSPATH")) {
    exit;
}

add_filter("pre_get_document_title", static function ($title) {
    return is_page_template("page-tworzenie-stron-internetowych.php") ? "Tworzenie stron internetowych dla firm | Upsellio" : $title;
});

add_action("wp_head", static function () {
    if (!is_page_template("page-tworzenie-stron-internetowych.php")) return;

    $url = home_url("/tworzenie-stron-internetowych/");
    echo '<meta name="description" content="Tworzenie stron internetowych dla firm B2B i usługowych. Strony i landing pages pod konwersję, SEO i kampanie reklamowe. Bezpłatna analiza.">' . "\n";
    echo '<meta property="og:title" content="Tworzenie stron internetowych dla firm | Upsellio">' . "\n";
    echo '<meta property="og:description" content="Strony internetowe i landing pages pod konwersję, SEO oraz kampanie reklamowe dla firm B2B, usługowych i e-commerce.">' . "\n";
    echo '<meta property="og:type" content="website">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
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

$faq_items = [
    [
        "question" => "Ile kosztuje tworzenie strony internetowej dla firmy?",
        "answer" => "Koszt zależy od zakresu projektu: liczby podstron, funkcji, copywritingu, integracji i wymagań technicznych. Landing page pod kampanię to inny projekt niż rozbudowana strona firmowa z wieloma podstronami. Wycena powstaje po rozmowie o celu i zakresie.",
    ],
    [
        "question" => "Ile trwa stworzenie strony internetowej?",
        "answer" => "Prosty landing page można zwykle wdrożyć w 2-3 tygodnie od zatwierdzenia treści i projektu. Strona firmowa z kilkoma podstronami, copywritingiem i integracjami trwa zazwyczaj 4-8 tygodni.",
    ],
    [
        "question" => "Czy tworzysz strony na WordPress czy na innych systemach?",
        "answer" => "Dobór technologii zależy od potrzeb projektu. WordPress jest dobrym wyborem dla stron firmowych i blogowych, a landing pages mogą powstawać także jako szybkie wdrożenia statyczne lub w innych narzędziach, jeśli lepiej służą celowi.",
    ],
    [
        "question" => "Czy po oddaniu strony będę mógł ją samodzielnie edytować?",
        "answer" => "Tak. Przy oddaniu strony można przejść przez podstawową edycję treści, zdjęć i elementów strony, żeby proste aktualizacje nie wymagały każdorazowego wsparcia technicznego.",
    ],
    [
        "question" => "Czy zajmujesz się też SEO strony?",
        "answer" => "Przy tworzeniu strony wdrażam podstawy SEO technicznego i treściowego: strukturę nagłówków, meta tagi, szybkość, sitemap, intencje wyszukiwania i naturalne użycie fraz kluczowych. Długofalowe pozycjonowanie to osobny proces.",
    ],
    [
        "question" => "Co jeśli mam już stronę i chcę tylko ją poprawić?",
        "answer" => "Nie zawsze potrzeba nowej strony. Jeśli obecna strona ma ruch, ale za mało zapytań, sensowny może być audyt konwersji i wdrożenie konkretnych poprawek: nagłówka, CTA, sekcji zaufania, formularza lub szybkości ładowania.",
    ],
    [
        "question" => "Czy tworzenie strony obejmuje też treści i zdjęcia?",
        "answer" => "Copywriting sprzedażowy i SEO może być częścią pracy nad stroną. Zdjęcia firmowe, produktowe i realizacyjne najlepiej dostarczyć po stronie klienta, bo autentyczne materiały zwykle budują większe zaufanie niż stocki.",
    ],
];
?>

<style>
  .web-page {
    --web-bg:#f8fafc;
    --web-paper:#fff;
    --web-soft:#f1f5f9;
    --web-ink:#071426;
    --web-text:#334155;
    --web-muted:#64748b;
    --web-border:#e2e8f0;
    --web-green:#0d9488;
    --web-green-dark:#0f766e;
    --web-green-soft:#ecfeff;
    --web-blue:#081827;
    --web-blue-soft:#ecfeff;
    --web-amber:#f59e0b;
    --web-dark:#081827;
    --web-shadow:0 24px 70px rgba(15,23,42,.12);
    --web-shadow-soft:0 14px 40px rgba(15,23,42,.08);
    background:var(--web-bg);
    color:var(--web-ink);
  }
  html { scroll-behavior:smooth; scroll-padding-top:140px; }
  .web-wrap { width:min(1240px, calc(100% - 48px)); margin:0 auto; }
  .web-section { padding:clamp(70px,8vw,112px) 0; }
  .web-h1,.web-h2,.web-h3 { font-family:var(--font-display); color:var(--web-ink); line-height:1.04; letter-spacing:-1.3px; }
  .web-h1 { font-size:clamp(42px,6vw,78px); max-width:1060px; }
  .web-h2 { font-size:clamp(32px,4vw,54px); max-width:950px; }
  .web-h3 { font-size:clamp(23px,3vw,34px); }
  .web-page p { color:var(--web-text); }
  .web-lead { margin-top:24px; max-width:820px; font-size:clamp(18px,2vw,21px); line-height:1.75; }
  .web-copy { margin-top:20px; max-width:960px; display:grid; gap:14px; }
  .web-eyebrow { display:inline-flex; align-items:center; gap:10px; margin-bottom:18px; font-size:12px; font-weight:800; letter-spacing:1.6px; text-transform:uppercase; color:var(--web-green-dark); }
  .web-eyebrow::before { content:""; width:28px; height:2px; border-radius:99px; background:linear-gradient(90deg,var(--web-green),var(--web-amber)); }
  .web-btn-row { display:flex; flex-wrap:wrap; gap:12px; margin-top:32px; }
  .web-btn { min-height:50px; display:inline-flex; align-items:center; justify-content:center; border-radius:999px; padding:0 24px; font-size:15px; font-weight:800; transition:.2s ease; text-decoration:none; }
  .web-btn-primary { background:linear-gradient(135deg,var(--web-green),#14b8a6); color:#fff; box-shadow:0 14px 28px rgba(13,148,136,.22); }
  .web-btn-secondary { background:#fff; border:1px solid #cbd5e1; color:var(--web-ink); }
  .web-btn-ghost { background:var(--web-green-soft); color:var(--web-green-dark); border:1px solid #99f6e4; }
  .web-btn:hover { transform:translateY(-2px); }
  .web-quick { position:sticky; top:82px; z-index:70; background:rgba(255,255,255,.95); border-bottom:1px solid var(--web-border); box-shadow:0 8px 20px rgba(16,18,15,.04); backdrop-filter:blur(12px); }
  .web-quick-inner { min-height:58px; display:flex; align-items:center; justify-content:space-between; gap:18px; overflow-x:auto; scrollbar-width:none; }
  .web-quick-inner::-webkit-scrollbar { display:none; }
  .web-quick-links { display:flex; align-items:center; gap:10px; white-space:nowrap; }
  .web-quick-links a { min-height:36px; display:inline-flex; align-items:center; padding:0 14px; border:1px solid var(--web-border); border-radius:999px; font-size:13px; font-weight:700; color:var(--web-text); background:#f8fafc; transition:.2s ease; }
  .web-quick-links a:hover,.web-quick-links a.is-active { color:var(--web-green-dark); border-color:#99f6e4; background:var(--web-green-soft); }
  .web-quick-cta { flex:0 0 auto; min-height:38px; display:inline-flex; align-items:center; padding:0 16px; border-radius:999px; background:var(--web-green-dark); color:#fff; font-size:13px; font-weight:800; white-space:nowrap; }
  .web-hero { position:relative; overflow:hidden; padding:clamp(72px,8vw,124px) 0; border-bottom:1px solid var(--web-border); background:linear-gradient(180deg,#fff,var(--web-soft)); }
  .web-hero::before { content:""; position:absolute; right:-180px; top:-170px; width:650px; height:650px; background:radial-gradient(circle,rgba(20,184,166,.14),transparent 64%); }
  .web-hero::after { content:""; position:absolute; left:-150px; bottom:-210px; width:540px; height:540px; background:radial-gradient(circle,rgba(8,24,39,.12),transparent 65%); }
  .web-hero-grid { position:relative; display:grid; grid-template-columns:minmax(0,1.08fr) minmax(330px,.92fr); gap:clamp(34px,5vw,64px); align-items:center; }
  .web-blueprint { border:1px solid var(--web-border); border-radius:34px; background:#fff; box-shadow:var(--web-shadow); overflow:hidden; }
  .web-browser { display:flex; align-items:center; gap:8px; padding:16px 18px; border-bottom:1px solid var(--web-border); background:#f8fafc; }
  .web-browser span { width:12px; height:12px; border-radius:50%; background:#ef6155; box-shadow:20px 0 0 #f4c14f, 40px 0 0 #46b37d; }
  .web-wireframe { padding:24px; display:grid; gap:14px; }
  .web-wire-hero { padding:22px; border:1px dashed #99f6e4; border-radius:22px; background:linear-gradient(135deg,#ecfeff,#fff); }
  .web-wire-hero strong { display:block; font-family:var(--font-display); font-size:25px; line-height:1.08; color:var(--web-ink); }
  .web-wire-hero p { margin-top:8px; font-size:14px; }
  .web-wire-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; }
  .web-wire-card { min-height:96px; padding:14px; border:1px solid var(--web-border); border-radius:16px; background:#f8fafc; }
  .web-wire-card b { display:block; color:var(--web-green-dark); }
  .web-wire-card small { display:block; color:var(--web-muted); margin-top:4px; line-height:1.4; }
  .web-problem-grid { margin-top:38px; display:grid; grid-template-columns:1fr 1fr; gap:18px; }
  .web-problem-card { padding:26px; border:1px solid var(--web-border); border-radius:26px; background:#fff; box-shadow:var(--web-shadow-soft); transition:.2s ease; }
  .web-problem-card:hover { transform:translateY(-4px); border-color:#99f6e4; box-shadow:var(--web-shadow); }
  .web-problem-card strong { display:block; margin-bottom:10px; color:var(--web-ink); font-size:18px; }
  .web-problem-card p { font-size:15px; }
  .web-mid-note { margin-top:34px; display:grid; grid-template-columns:1fr auto; gap:20px; align-items:center; padding:28px; border:1px solid #99f6e4; border-radius:28px; background:linear-gradient(135deg,#ecfeff,#fff); box-shadow:var(--web-shadow-soft); }
  .web-mid-note strong { display:block; font-family:var(--font-display); font-size:clamp(24px,3vw,36px); line-height:1.05; letter-spacing:-1px; color:var(--web-ink); margin-bottom:8px; }
  .web-service { background:#fff; border-top:1px solid var(--web-border); border-bottom:1px solid var(--web-border); }
  .web-service-grid { margin-top:40px; display:grid; grid-template-columns:repeat(3,1fr); gap:18px; }
  .web-service-card { padding:28px; border:1px solid var(--web-border); border-radius:28px; background:#f8fafc; }
  .web-service-card .web-h3 { margin-bottom:14px; }
  .web-check-list { display:grid; gap:10px; list-style:none; padding:0; }
  .web-check-list li { position:relative; padding-left:26px; color:var(--web-text); font-size:15px; }
  .web-check-list li::before { content:"✓"; position:absolute; left:0; color:var(--web-green-dark); font-weight:900; }
  .web-elements { margin-top:40px; display:grid; grid-template-columns:repeat(4,1fr); gap:14px; }
  .web-element { padding:22px; border:1px solid var(--web-border); border-radius:22px; background:#fff; box-shadow:var(--web-shadow-soft); }
  .web-element b { display:inline-grid; place-items:center; width:34px; height:34px; margin-bottom:14px; border-radius:50%; background:var(--web-blue-soft); color:var(--web-blue); font-family:var(--font-display); }
  .web-element strong { display:block; margin-bottom:8px; color:var(--web-ink); }
  .web-element p { font-size:14px; }
  .web-dark-split { background:radial-gradient(circle at right top,rgba(20,184,166,.22),transparent 35%),linear-gradient(145deg,#081827,#0f172a); border-radius:34px; padding:clamp(34px,5vw,56px); color:#fff; display:grid; grid-template-columns:.9fr 1.1fr; gap:34px; align-items:center; box-shadow:var(--web-shadow); }
  .web-dark-split .web-h2 { color:#fff; }
  .web-dark-split p,.web-dark-split .web-lead { color:rgba(255,255,255,.74); }
  .web-dark-split .web-eyebrow { color:#8ff0ca; }
  .web-dark-split .web-eyebrow::before { background:#8ff0ca; }
  .web-fit-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
  .web-fit-card { padding:22px; border:1px solid rgba(255,255,255,.13); border-radius:22px; background:rgba(255,255,255,.06); }
  .web-fit-card strong { display:block; color:#fff; margin-bottom:8px; }
  .web-fit-card p { font-size:14px; }
  .web-types { margin-top:40px; display:grid; grid-template-columns:1.1fr .9fr; gap:22px; }
  .web-type-feature { padding:34px; border-radius:30px; background:linear-gradient(135deg,#fff,#ecfeff); border:1px solid #99f6e4; box-shadow:var(--web-shadow); }
  .web-type-feature .web-h3 { margin-bottom:12px; }
  .web-type-stack { display:grid; gap:14px; }
  .web-type-card { padding:24px; border:1px solid var(--web-border); border-radius:24px; background:#fff; box-shadow:var(--web-shadow-soft); }
  .web-type-card strong { display:block; color:var(--web-ink); margin-bottom:8px; }
  .web-process { background:linear-gradient(180deg,#fff,var(--web-soft)); border-top:1px solid var(--web-border); border-bottom:1px solid var(--web-border); }
  .web-process-grid { margin-top:38px; display:grid; grid-template-columns:repeat(5,1fr); gap:14px; }
  .web-step { padding:22px; border:1px solid var(--web-border); border-radius:24px; background:#fff; box-shadow:var(--web-shadow-soft); }
  .web-step b { width:34px; height:34px; display:grid; place-items:center; margin-bottom:14px; border-radius:50%; background:var(--web-green-soft); color:var(--web-green-dark); font-family:var(--font-display); }
  .web-step strong { display:block; color:var(--web-ink); margin-bottom:8px; }
  .web-step p { font-size:14px; }
  .web-faq-grid { margin-top:38px; display:grid; gap:14px; max-width:960px; }
  .web-page details { border:1px solid var(--web-border); border-radius:18px; background:#fff; padding:20px 22px; box-shadow:var(--web-shadow-soft); }
  .web-page summary { cursor:pointer; font-weight:800; color:var(--web-ink); }
  .web-page details p { margin-top:12px; font-size:15px; }
  .web-final { text-align:center; padding:clamp(42px,5vw,64px); border:1px solid #bae6d6; border-radius:32px; background:radial-gradient(circle at top,#effcf7,#fff 62%); box-shadow:var(--web-shadow-soft); }
  .web-final .web-h2 { margin:0 auto; }
  .web-final p { max-width:850px; margin:20px auto 0; font-size:18px; }
  .web-final .web-btn-row { justify-content:center; }
  .web-internal-links { margin-top:22px; display:flex; flex-wrap:wrap; justify-content:center; gap:10px; }
  .web-internal-links a { display:inline-flex; min-height:38px; align-items:center; border:1px solid var(--web-border); border-radius:999px; padding:0 14px; background:#fff; color:var(--web-green-dark); font-size:13px; font-weight:800; }
  @media(max-width:1060px){
    .web-process-grid { grid-template-columns:repeat(3,1fr); }
    .web-elements { grid-template-columns:repeat(2,1fr); }
  }
  @media(max-width:980px){
    html { scroll-padding-top:130px; }
    .web-hero-grid,.web-dark-split,.web-types { grid-template-columns:1fr; }
    .web-service-grid { grid-template-columns:1fr 1fr; }
    .web-mid-note { grid-template-columns:1fr; }
  }
  @media(max-width:620px){
    html { scroll-padding-top:125px; }
    .web-wrap { width:min(100% - 28px,1240px); }
    .web-wire-grid,.web-problem-grid,.web-service-grid,.web-elements,.web-fit-grid,.web-process-grid { grid-template-columns:1fr; }
    .web-btn { width:100%; }
    .web-quick-cta { display:none; }
    .web-quick-inner { padding:10px 0; }
  }
  /* Mobile-first UX correction layer */
  .web-section { padding:48px 0; }
  .web-hero { padding:52px 0 46px; }
  .web-h1 { font-size:clamp(34px,10vw,40px); line-height:1.09; letter-spacing:-1px; }
  .web-h2 { font-size:clamp(28px,8vw,34px); line-height:1.12; letter-spacing:-.8px; }
  .web-h3 { font-size:clamp(21px,6vw,26px); line-height:1.16; letter-spacing:-.5px; }
  .web-lead { margin-top:16px; font-size:17px; line-height:1.65; }
  .web-copy { margin-top:14px; gap:10px; }
  .web-copy p { line-height:1.72; }
  .web-btn-row { margin-top:22px; }
  .web-quick { position:static; }
  .web-quick-inner { min-height:auto; padding:10px 0; }
  .web-hero-grid,.web-wire-grid,.web-problem-grid,.web-service-grid,.web-elements,.web-fit-grid,.web-types,.web-process-grid { grid-template-columns:1fr; }
  .web-blueprint,.web-problem-card,.web-service-card,.web-element,.web-dark-split,.web-fit-card,.web-type-feature,.web-type-card,.web-step,.web-final { border-radius:20px; }
  .web-problem-card,.web-service-card,.web-element,.web-type-card,.web-step { padding:18px; }
  .web-dark-split,.web-type-feature,.web-final,.web-mid-note { padding:20px; }
  .web-wireframe { padding:18px; }
  .web-wire-hero strong { font-size:22px; line-height:1.14; }
  .web-mid-note strong { font-size:clamp(22px,7vw,28px); line-height:1.12; }
  @media(min-width:760px){
    .web-section { padding:72px 0; }
    .web-hero { padding:76px 0 68px; }
    .web-quick { position:sticky; }
    .web-h1 { font-size:clamp(44px,6vw,58px); line-height:1.05; }
    .web-h2 { font-size:clamp(34px,4vw,46px); }
    .web-h3 { font-size:clamp(23px,3vw,30px); }
    .web-wire-grid,.web-problem-grid,.web-service-grid,.web-elements,.web-fit-grid,.web-process-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
  }
  @media(min-width:1024px){
    .web-section { padding:96px 0; }
    .web-hero { padding:96px 0 88px; }
    .web-h1 { font-size:66px; }
    .web-h2 { font-size:50px; }
    .web-hero-grid { grid-template-columns:minmax(0,1.05fr) minmax(300px,.85fr); }
    .web-service-grid { grid-template-columns:repeat(3,minmax(0,1fr)); }
    .web-elements { grid-template-columns:repeat(4,minmax(0,1fr)); }
    .web-process-grid { grid-template-columns:repeat(5,minmax(0,1fr)); }
    .web-types,.web-dark-split { grid-template-columns:1.05fr .95fr; }
    .web-problem-card,.web-service-card,.web-element,.web-type-card,.web-step { padding:22px; }
  }
</style>

<main class="web-page">
  <div class="web-quick">
    <div class="web-wrap web-quick-inner">
      <div class="web-quick-links">
        <a href="#start">Start</a>
        <a href="#problemy">Konwersja</a>
        <a href="#zakres">Zakres</a>
        <a href="#elementy">Elementy</a>
        <a href="#typy">Typy stron</a>
        <a href="#proces">Proces</a>
        <a href="#faq">FAQ</a>
      </div>
      <a href="#kontakt" class="web-quick-cta">Sprawdź stronę</a>
    </div>
  </div>

  <section class="web-hero" id="start">
    <div class="web-wrap web-hero-grid">
      <div>
        <span class="web-eyebrow">Strony WWW pod konwersję</span>
        <h1 class="web-h1">Tworzenie stron internetowych dla firm: strona, która nie tylko wygląda dobrze, ale zamienia odwiedzających w klientów.</h1>
        <p class="web-lead">Projektuję i tworzę strony internetowe oraz landing pages dla firm B2B, usługowych i e-commerce. Każda strona jest zaprojektowana pod jeden cel: przekształcenie ruchu w zapytania, kontakty i sprzedaż.</p>
        <div class="web-copy">
          <p>Większość firm ma stronę internetową. Ale posiadanie strony i posiadanie strony, która sprzedaje, to dwie zupełnie różne rzeczy. Strona firmowa nie jest wizytówką do zawieszenia w internecie, tylko narzędziem sprzedażowym, które pracuje 24 godziny na dobę.</p>
          <p>Różnica między stroną, która konwertuje, a stroną, która nie konwertuje, rzadko leży w grafice. Leży w jasności komunikatu, strukturze przekonywania i zaufaniu budowanym przez liczby, opinie, case studies oraz konkretne CTA.</p>
        </div>
        <div class="web-btn-row">
          <a href="#kontakt" class="web-btn web-btn-primary">Chcę bezpłatną analizę strony</a>
          <a href="#elementy" class="web-btn web-btn-secondary">Zobacz elementy skutecznej strony</a>
          <a href="#typy" class="web-btn web-btn-ghost">Strona czy landing page?</a>
        </div>
      </div>

      <aside class="web-blueprint" aria-label="Blueprint skutecznej strony">
        <div class="web-browser"><span></span></div>
        <div class="web-wireframe">
          <div class="web-wire-hero">
            <strong>Hero, które mówi: dla kogo, co i dlaczego teraz.</strong>
            <p>Nagłówek, jasna propozycja wartości, dowód zaufania i jedno główne CTA.</p>
          </div>
          <div class="web-wire-grid">
            <div class="web-wire-card"><b>Problem</b><small>Nazywa sytuację klienta.</small></div>
            <div class="web-wire-card"><b>Dowód</b><small>Opinie, liczby, realizacje.</small></div>
            <div class="web-wire-card"><b>CTA</b><small>Prosty następny krok.</small></div>
          </div>
        </div>
      </aside>
    </div>
  </section>

  <section class="web-section" id="problemy">
    <div class="web-wrap">
      <span class="web-eyebrow">Dlaczego strona nie generuje zapytań</span>
      <h2 class="web-h2">Dlaczego Twoja strona internetowa ma ruch, ale nie ma zapytań?</h2>
      <div class="web-copy">
        <p>Brak zapytań ze strony internetowej to jeden z najczęstszych problemów firm. Strona jest, wejścia są, budżet reklamowy jest wydawany, ale formularz milczy, a telefon nie dzwoni. Problem zwykle leży w komunikacji, zaufaniu i prowadzeniu do kontaktu.</p>
        <p>Pierwsze sekundy na stronie decydują, czy użytkownik zostanie. Jeśli nagłówek nie mówi, czym się zajmujesz, dla kogo jest oferta i dlaczego warto zostać, odwiedzający wróci do Google albo kliknie reklamę konkurencji.</p>
      </div>

      <div class="web-problem-grid">
        <div class="web-problem-card"><strong>Niejasny komunikat na górze strony</strong><p>Nagłówek powinien od razu wyjaśniać, co oferujesz, komu pomagasz i jaki problem rozwiązujesz.</p></div>
        <div class="web-problem-card"><strong>Strona mówi o firmie, nie o kliencie</strong><p>Klient nie szuka opisu firmy, tylko rozwiązania swojego problemu i powodu, żeby zaufać właśnie Tobie.</p></div>
        <div class="web-problem-card"><strong>Brak lub słabe CTA</strong><p>Wezwanie do działania musi być widoczne, konkretne i pojawiać się w kilku miejscach ścieżki.</p></div>
        <div class="web-problem-card"><strong>Brak elementów zaufania</strong><p>Liczby, opinie, case studies i logotypy klientów obniżają opór przed kontaktem.</p></div>
        <div class="web-problem-card"><strong>Strona nie jest dostosowana do kampanii</strong><p>Ruch z Google Ads lub Meta Ads potrzebuje spójnego landing page, a nie przypadkowej strony głównej.</p></div>
        <div class="web-problem-card"><strong>Wolne ładowanie na mobile</strong><p>Strona wolniejsza niż kilka sekund traci użytkownika zanim przeczyta pierwszą linijkę tekstu.</p></div>
      </div>

      <div class="web-mid-note">
        <div>
          <strong>Nie zawsze potrzebujesz nowej strony. Czasem wystarczy naprawić sekcje, które blokują kontakt.</strong>
          <p>Audyt konwersji pokazuje, czy problem leży w treści, strukturze, szybkości, CTA, zaufaniu czy dopasowaniu do kampanii reklamowej.</p>
        </div>
        <a href="#kontakt" class="web-btn web-btn-primary">Sprawdźmy obecną stronę</a>
      </div>
    </div>
  </section>

  <section class="web-section web-service" id="zakres">
    <div class="web-wrap">
      <span class="web-eyebrow">Zakres usługi</span>
      <h2 class="web-h2">Tworzenie stron internetowych i landing pages jako element systemu marketingowego, nie izolowany projekt graficzny.</h2>
      <div class="web-copy">
        <p>Projektowanie stron dla firm zaczyna się długo przed wyborem szablonu czy palety kolorów. Żeby strona działała jako narzędzie sprzedażowe, musi być zaprojektowana pod konkretny cel, grupę docelową i ścieżkę konwersji.</p>
      </div>

      <div class="web-service-grid">
        <div class="web-service-card">
          <h3 class="web-h3">Analiza i strategia konwersji</h3>
          <ul class="web-check-list">
            <li>profil klienta, pytania i obiekcje przed decyzją</li>
            <li>oczekiwany następny krok: formularz, telefon, wycena lub zapis</li>
            <li>mapa sekcji i cel każdej części strony</li>
            <li>propozycja wartości i elementy zaufania</li>
          </ul>
        </div>
        <div class="web-service-card">
          <h3 class="web-h3">Copywriting sprzedażowy i SEO</h3>
          <ul class="web-check-list">
            <li>nagłówki H1-H3 i treści pisane językiem klienta</li>
            <li>naturalna optymalizacja pod frazy i intencje wyszukiwania</li>
            <li>odpowiedzi na pytania, obiekcje i ryzyka klienta</li>
            <li>meta tagi, linkowanie wewnętrzne i struktura URL</li>
          </ul>
        </div>
        <div class="web-service-card">
          <h3 class="web-h3">Projektowanie i wdrożenie</h3>
          <ul class="web-check-list">
            <li>mobile-first, czytelność i hierarchia informacji</li>
            <li>szybkość ładowania i Core Web Vitals</li>
            <li>formularze, CTA i sekcje pod kampanie reklamowe</li>
            <li>wdrożenie analityki, Tag Managera i śledzenia konwersji</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section class="web-section" id="elementy">
    <div class="web-wrap">
      <span class="web-eyebrow">Elementy skutecznej strony</span>
      <h2 class="web-h2">Co musi zawierać strona internetowa firmy, żeby zamieniała ruch w zapytania?</h2>
      <div class="web-copy">
        <p>Skuteczna strona internetowa to nie kwestia samej estetyki. To kwestia struktury i komunikatu. Brak kilku elementów bezpośrednio obniża konwersję i sprawia, że ruch z SEO lub reklam nie zamienia się w kontakt.</p>
      </div>
      <div class="web-elements">
        <div class="web-element"><b>1</b><strong>Jasny nagłówek</strong><p>Odpowiada, czym się zajmujesz, dla kogo jest oferta i jaka jest główna korzyść.</p></div>
        <div class="web-element"><b>2</b><strong>Propozycja wartości</strong><p>Pokazuje, dlaczego warto wybrać Twoją firmę, a nie konkurencję.</p></div>
        <div class="web-element"><b>3</b><strong>Dowody zaufania</strong><p>Liczby, opinie, case studies i logotypy klientów obniżają ryzyko kontaktu.</p></div>
        <div class="web-element"><b>4</b><strong>Widoczne CTA</strong><p>Jeden główny cel pojawia się w pierwszej sekcji, po usługach i po dowodach.</p></div>
        <div class="web-element"><b>5</b><strong>Odpowiedzi na obiekcje</strong><p>FAQ, proces i sekcje dla kogo zatrzymują użytkownika przed powrotem do Google.</p></div>
        <div class="web-element"><b>6</b><strong>Szybkość i mobile</strong><p>Core Web Vitals, responsywność i szybkie ładowanie wpływają na SEO i konwersję.</p></div>
        <div class="web-element"><b>7</b><strong>Struktura SEO</strong><p>Poprawne nagłówki, meta tagi, URL, alt texty i linkowanie pomagają Google zrozumieć temat.</p></div>
        <div class="web-element"><b>8</b><strong>Integracja z reklamami</strong><p>Spójny przekaz z Google Ads i Meta Ads oraz śledzenie konwersji zwiększają zwrot z budżetu.</p></div>
      </div>
    </div>
  </section>

  <section class="web-section" id="dla-kogo">
    <div class="web-wrap">
      <div class="web-dark-split">
        <div>
          <span class="web-eyebrow">Dla kogo</span>
          <h2 class="web-h2">Dla jakich firm tworzę strony internetowe i kiedy nowa strona ma większy sens niż kolejna reklama?</h2>
          <p class="web-lead">Tworzenie stron ma sens dla firm, które chcą traktować obecność w internecie jako inwestycję w pozyskiwanie klientów, a nie obowiązek spełniony raz na kilka lat.</p>
        </div>
        <div class="web-fit-grid">
          <div class="web-fit-card"><strong>Firmy B2B i usługowe</strong><p>Strona jest często pierwszym punktem kontaktu i musi szybko budować wiarygodność.</p></div>
          <div class="web-fit-card"><strong>Kampanie bez wyników</strong><p>Nowy landing page może poprawić CPL bez zwiększania wydatków reklamowych.</p></div>
          <div class="web-fit-card"><strong>Nowe firmy i produkty</strong><p>Dobra strona na start oszczędza czas i pieniądze, które inaczej idą w poprawki.</p></div>
          <div class="web-fit-card"><strong>Strony z ruchem bez leadów</strong><p>Audyt pokazuje, co blokuje zapytania i które zmiany wdrożyć najpierw.</p></div>
        </div>
      </div>
    </div>
  </section>

  <section class="web-section" id="typy">
    <div class="web-wrap">
      <span class="web-eyebrow">Typy stron</span>
      <h2 class="web-h2">Strona firmowa, landing page czy sklep internetowy: które rozwiązanie ma większy sens dla Twojego celu?</h2>
      <div class="web-types">
        <div class="web-type-feature">
          <h3 class="web-h3">Landing page: jeden cel, mniej rozproszeń, szybszy test komunikatu.</h3>
          <p>Landing page jest zaprojektowany pod jeden konkretny cel: formularz, konsultację, wycenę, zapis lub zakup. Nie rozprasza nawigacją, tylko prowadzi użytkownika przez problem, rozwiązanie, dowody i CTA.</p>
          <p>To najlepszy wybór dla kampanii <a href="<?php echo esc_url(home_url("/marketing-google-ads/")); ?>">Google Ads</a> i <a href="<?php echo esc_url(home_url("/marketing-meta-ads/")); ?>">Meta Ads</a>, promocji pojedynczej usługi oraz testowania komunikatów przed budową pełnej strony.</p>
        </div>
        <div class="web-type-stack">
          <div class="web-type-card"><strong>Strona firmowa wielostronicowa</strong><p>Najlepsza dla SEO, wielu usług, portfolio, bloga i pełnej prezentacji firmy.</p></div>
          <div class="web-type-card"><strong>Strona one-page</strong><p>Dobra dla jednej głównej usługi, szybkiego startu lub prostszej prezentacji online.</p></div>
          <div class="web-type-card"><strong>Sklep internetowy B2B</strong><p>Katalogi, zapytania ofertowe, warianty produktów, integracje i proces zamawiania dla firm.</p></div>
        </div>
      </div>
    </div>
  </section>

  <section class="web-section web-process" id="proces">
    <div class="web-wrap">
      <span class="web-eyebrow">Proces współpracy</span>
      <h2 class="web-h2">Od pierwszej rozmowy do uruchomienia strony, która konwertuje.</h2>
      <div class="web-copy">
        <p>Tworzenie strony zaczyna się od diagnozy, a nie od wyboru szablonu. Najpierw trzeba zrozumieć klienta, obiekcje, proces decyzyjny, konkurencję w Google i cel biznesowy strony.</p>
      </div>
      <div class="web-process-grid">
        <div class="web-step"><b>1</b><strong>Analiza</strong><p>Obecna strona, oferta, grupa docelowa, konkurencja i frazy w Google.</p></div>
        <div class="web-step"><b>2</b><strong>Treści</strong><p>Copywriting, propozycja wartości, sekcje zaufania, FAQ, CTA i SEO.</p></div>
        <div class="web-step"><b>3</b><strong>Projekt</strong><p>Hierarchia informacji, mobile-first, czytelność i prowadzenie do działania.</p></div>
        <div class="web-step"><b>4</b><strong>Wdrożenie</strong><p>Core Web Vitals, meta tagi, formularze, tracking, sitemap i robots.txt.</p></div>
        <div class="web-step"><b>5</b><strong>Optymalizacja</strong><p>Analiza zachowań, testy nagłówków, CTA, sekcji i formularzy po uruchomieniu.</p></div>
      </div>
    </div>
  </section>

  <section class="web-section" id="faq">
    <div class="web-wrap">
      <span class="web-eyebrow">FAQ</span>
      <h2 class="web-h2">Najczęstsze pytania przed zleceniem strony internetowej.</h2>
      <div class="web-faq-grid">
        <?php foreach ($faq_items as $faq_item) : ?>
          <details>
            <summary><?php echo esc_html((string) $faq_item["question"]); ?></summary>
            <p><?php echo esc_html((string) $faq_item["answer"]); ?></p>
          </details>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="web-section" id="kontakt">
    <div class="web-wrap">
      <div class="web-final">
        <h2 class="web-h2">Chcesz sprawdzić, co blokuje zapytania na Twojej stronie albo zbudować nową stronę, która będzie sprzedawać?</h2>
        <p>Zanim zlecisz nową stronę lub kolejną poprawkę, warto wiedzieć, czy problem leży w projekcie graficznym, treści, strukturze, szybkości ładowania czy braku CTA.</p>
        <p>Napisz, czym się zajmujesz, jaki masz obecny problem i podaj adres strony. Odpowiem konkretnie, co zmienić najpierw, czy sensowniejszy jest audyt i poprawki, czy nowa strona, oraz jaki typ strony będzie najefektywniejszy dla Twojego celu.</p>
        <div class="web-btn-row">
          <a href="<?php echo esc_url($contact_email_href); ?>" class="web-btn web-btn-primary">Napisz: <?php echo esc_html($contact_email_display); ?></a>
          <a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $contact_phone)); ?>" class="web-btn web-btn-secondary">Zadzwoń: <?php echo esc_html($contact_phone); ?></a>
        </div>
        <div class="web-internal-links" aria-label="Powiązane usługi">
          <a href="<?php echo esc_url(home_url("/oferta/")); ?>">Pełna oferta marketingowa</a>
          <a href="<?php echo esc_url(home_url("/marketing-google-ads/")); ?>">Google Ads dla firm</a>
          <a href="<?php echo esc_url(home_url("/marketing-meta-ads/")); ?>">Meta Ads dla firm</a>
          <a href="#faq">Pytania o tworzenie stron</a>
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
        ["@type" => "ListItem", "position" => 2, "name" => "Oferta", "item" => home_url("/oferta/")],
        ["@type" => "ListItem", "position" => 3, "name" => "Tworzenie stron internetowych", "item" => home_url("/tworzenie-stron-internetowych/")],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
?>
</script>
<script type="application/ld+json">
<?php
echo wp_json_encode([
    "@context" => "https://schema.org",
    "@type" => "ProfessionalService",
    "name" => "Upsellio - tworzenie stron internetowych",
    "url" => home_url("/tworzenie-stron-internetowych/"),
    "email" => $contact_email,
    "telephone" => $contact_phone,
    "description" => "Tworzenie stron internetowych, landing pages i optymalizacja konwersji dla firm B2B, usługowych i e-commerce.",
    "areaServed" => "PL",
    "serviceType" => "Web Design and Development",
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
?>
</script>
<?php endif; ?>

<script>
  (function () {
    const quickLinks = Array.from(document.querySelectorAll(".web-quick-links a"));
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
