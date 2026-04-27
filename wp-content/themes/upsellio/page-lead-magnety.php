<?php
/*
Template Name: Upsellio - Lead Magnety
Template Post Type: page
*/
if (!defined("ABSPATH")) {
    exit;
}

if (function_exists("upsellio_register_template_seo_head")) {
    upsellio_register_template_seo_head("lead_magnety");
}

get_header();

$lead_magnets = function_exists("upsellio_get_lead_magnet_list") ? upsellio_get_lead_magnet_list(60) : [];
$featured = null;
$categories = [
    "meta-ads" => "Meta Ads",
    "google-ads" => "Google Ads",
    "strony-landing-pages" => "Strony i landing pages",
    "lead-generation" => "Lead generation",
    "analityka" => "Analityka",
];

foreach ($lead_magnets as $item) {
    if ($featured === null && !empty($item["is_featured"])) {
        $featured = $item;
    }

    $category_slug = (string) ($item["category_slug"] ?? "");
    $category_name = (string) ($item["category"] ?? "");
    if ($category_slug !== "" && $category_name !== "") {
        $categories[$category_slug] = $category_name;
    }
}

if ($featured === null && !empty($lead_magnets)) {
    $featured = $lead_magnets[0];
}

$schema_items = [];
foreach ($lead_magnets as $index => $item) {
    $schema_items[] = [
        "@type" => "ListItem",
        "position" => $index + 1,
        "url" => (string) ($item["url"] ?? ""),
        "name" => (string) ($item["title"] ?? ""),
    ];
}
$faq_items = [
    [
        "question" => "Czy materiały marketingowe są darmowe?",
        "answer" => "Tak. Checklisty, audyty i szablony są bezpłatne i dostępne po zostawieniu adresu e-mail albo bezpośrednio na stronie, jeśli dany materiał ma wspierać ruch organiczny.",
    ],
    [
        "question" => "Który lead magnet wybrać jako pierwszy?",
        "answer" => "Wybierz materiał odpowiadający aktualnemu problemowi: checklistę Meta Ads przed startem kampanii, audyt Google Ads przy rosnącym koszcie konwersji albo checklistę landing page, jeśli strona nie generuje zapytań.",
    ],
    [
        "question" => "Czy mogę użyć checklist bez doświadczenia w reklamach?",
        "answer" => "Tak. Materiały są pisane praktycznie i prowadzą przez konkretne punkty kontroli, więc pomagają uporządkować kampanię, stronę lub lejek nawet bez pracy z agencją.",
    ],
    [
        "question" => "Czy materiały nadają się dla firm B2B?",
        "answer" => "Tak. Biblioteka jest przygotowana z myślą o firmach B2B, usługowych i lokalnych, które chcą poprawić jakość leadów, mierzyć CPL i podejmować decyzje marketingowe na danych.",
    ],
];
?>
<style>
  .lm-page { background:#f8fafc; color:#071426; }
  .lm-wrap { width:min(1240px, calc(100% - 32px)); margin:0 auto; }
  .lm-hero { border-bottom:1px solid #e2e8f0; background:linear-gradient(180deg, rgba(20,184,166,0.12), rgba(255,255,255,0)); }
  .lm-hero-inner { padding:64px 0 52px; }
  .lm-pill { display:inline-flex; align-items:center; gap:10px; border:1px solid #99f6e4; background:#ecfeff; color:#0f766e; font-size:12px; font-weight:600; border-radius:999px; padding:9px 14px; }
  .lm-pill-dot { width:8px; height:8px; border-radius:50%; background:#0d9488; }
  .lm-h1 { margin:18px 0 16px; max-width:920px; font-family:"Syne",sans-serif; font-size:clamp(36px, 6vw, 64px); line-height:0.97; letter-spacing:-0.05em; }
  .lm-accent { color:#0d9488; }
  .lm-lead { margin:0; max-width:860px; font-size:19px; line-height:1.72; color:#334155; }
  .lm-search { margin-top:30px; display:grid; gap:12px; grid-template-columns:1fr; }
  .lm-search-input { border:1px solid #e2e8f0; background:#fff; border-radius:16px; padding:13px 16px; width:100%; font-size:15px; outline:none; }
  .lm-search-input:focus { border-color:#0d9488; box-shadow:0 0 0 3px rgba(20,184,166,.14); }
  .lm-category-bar { background:#fff; border-bottom:1px solid #e2e8f0; }
  .lm-categories { padding:18px 0; display:flex; flex-wrap:wrap; gap:10px; }
  .lm-cat-btn { border:1px solid #e2e8f0; background:#fff; color:#334155; border-radius:999px; padding:8px 14px; font-size:13px; font-weight:600; cursor:pointer; }
  .lm-cat-btn.is-active, .lm-cat-btn:hover { border-color:#0d9488; background:#ecfeff; color:#0f766e; }
  .lm-featured { padding:46px 0; border-bottom:1px solid #e2e8f0; }
  .lm-featured-card { overflow:hidden; border:1px solid #e2e8f0; border-radius:28px; background:#fff; display:grid; grid-template-columns:1fr; }
  .lm-featured-visual { min-height:260px; position:relative; background:#dce3df; }
  .lm-featured-visual img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
  .lm-featured-body { padding:28px; }
  .lm-badge { display:inline-flex; border-radius:999px; border:1px solid #99f6e4; background:#ecfeff; color:#0f766e; font-size:12px; font-weight:700; padding:5px 11px; }
  .lm-featured-title { margin:14px 0 12px; font-family:"Syne",sans-serif; font-size:clamp(30px, 3.6vw, 44px); line-height:1.02; letter-spacing:-0.04em; }
  .lm-featured-excerpt { margin:0; color:#334155; line-height:1.78; }
  .lm-btn { margin-top:18px; display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:44px; border-radius:12px; background:linear-gradient(135deg,#0d9488,#14b8a6); color:#fff; font-size:14px; font-weight:700; padding:10px 18px; }
  .lm-grid-section { background:#fff; }
  .lm-grid-head { padding:42px 0 14px; display:flex; justify-content:space-between; align-items:end; gap:16px; flex-wrap:wrap; }
  .lm-eyebrow { font-size:11px; letter-spacing:.18em; text-transform:uppercase; font-weight:700; color:#6f746f; }
  .lm-h2 { margin:10px 0 0; font-family:"Syne",sans-serif; font-size:clamp(31px, 4vw, 44px); line-height:1.06; letter-spacing:-0.04em; }
  .lm-grid { display:grid; gap:14px; padding:16px 0 54px; grid-template-columns:1fr; }
  .lm-card { border:1px solid #e2e8f0; border-radius:24px; padding:22px; background:#fff; display:flex; flex-direction:column; min-height:100%; transition:.2s ease; }
  .lm-card:hover { border-color:#0d9488; transform:translateY(-2px); box-shadow:0 14px 40px rgba(15,23,42,.08); }
  .lm-card-top { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; }
  .lm-card-category { font-size:11px; letter-spacing:.14em; text-transform:uppercase; color:#6a6f6a; font-weight:700; }
  .lm-card-type { font-size:12px; color:#596059; margin-top:5px; }
  .lm-card-title { margin:14px 0 8px; font-family:"Syne",sans-serif; font-size:26px; line-height:1.06; letter-spacing:-.04em; }
  .lm-card-excerpt { margin:0; color:#5a5f5a; line-height:1.74; font-size:15px; }
  .lm-card-meta { margin-top:10px; font-size:13px; color:#737973; }
  .lm-card-link { margin-top:auto; padding-top:18px; color:#0d9488; font-size:14px; font-weight:700; }
  .lm-empty { padding:18px; border:1px dashed #d8d8d3; border-radius:14px; color:#676d67; }
  .lm-extra { border-top:1px solid #e2e8f0; background:#f1f5f9; padding:48px 0; }
  .lm-extra-grid { display:grid; grid-template-columns:1fr; gap:16px; }
  .lm-extra-card { border:1px solid #e2e8f0; border-radius:22px; background:#fff; padding:22px; }
  .lm-extra-card h3 { margin:8px 0 10px; font-family:"Syne",sans-serif; font-size:28px; line-height:1.06; letter-spacing:-.03em; }
  .lm-extra-card p { margin:0; color:#5f635f; line-height:1.75; }
  .lm-extra-list { margin:14px 0 0; padding-left:17px; color:#535953; line-height:1.8; }
  .lm-seo-section { border-top:1px solid #e2e8f0; background:#fff; padding:48px 0; }
  .lm-seo-section.is-soft { background:#f1f5f9; }
  .lm-section-head { max-width:920px; }
  .lm-rich-copy { margin-top:18px; max-width:960px; display:grid; gap:14px; color:#3f453f; line-height:1.78; }
  .lm-rich-copy p { margin:0; color:#3f453f; }
  .lm-problem-grid,
  .lm-tool-grid,
  .lm-traffic-grid { margin-top:28px; display:grid; grid-template-columns:1fr; gap:14px; }
  .lm-info-card { border:1px solid #e2e8f0; border-radius:22px; background:#fff; padding:22px; box-shadow:0 14px 40px rgba(15,23,42,.08); }
  .lm-info-card strong { display:block; margin-bottom:8px; color:#101312; font-size:17px; }
  .lm-info-card p { margin:0; color:#4c534d; line-height:1.7; font-size:15px; }
  .lm-check-list { margin:14px 0 0; padding:0; list-style:none; display:grid; gap:8px; }
  .lm-check-list li { position:relative; padding-left:24px; color:#4c534d; line-height:1.65; }
  .lm-check-list li::before { content:"✓"; position:absolute; left:0; color:#0d9488; font-weight:900; }
  .lm-cta-box { border:1px solid #99f6e4; border-radius:26px; background:linear-gradient(135deg,#ecfeff,#fff); padding:28px; box-shadow:0 14px 40px rgba(15,23,42,.08); }
  .lm-faq-grid { margin-top:28px; display:grid; gap:12px; max-width:900px; }
  .lm-faq-item { border:1px solid #e2e8f0; border-radius:18px; background:#fff; padding:18px 20px; box-shadow:0 14px 40px rgba(15,23,42,.06); }
  .lm-faq-item summary { cursor:pointer; font-weight:800; color:#101312; }
  .lm-faq-item p { margin:12px 0 0; color:#4c534d; line-height:1.72; }
  .lm-hidden { display:none !important; }
  @media (min-width:760px){ .lm-wrap{width:min(1240px,calc(100% - 48px));} .lm-search{grid-template-columns:1fr 220px;} }
  @media (min-width:982px){
    .lm-featured-card{grid-template-columns:1.02fr .98fr;}
    .lm-grid{grid-template-columns:repeat(3,minmax(0,1fr));}
    .lm-extra-grid{grid-template-columns:1.05fr .95fr;}
    .lm-problem-grid{grid-template-columns:repeat(2,minmax(0,1fr));}
    .lm-tool-grid,.lm-traffic-grid{grid-template-columns:repeat(4,minmax(0,1fr));}
  }
  /* Mobile-first UX correction layer */
  .lm-hero-inner { padding:52px 0 46px; }
  .lm-h1 { font-size:clamp(34px,10vw,40px); line-height:1.09; letter-spacing:-1px; }
  .lm-h2 { font-size:clamp(28px,8vw,34px); line-height:1.12; letter-spacing:-.8px; }
  .lm-featured-title { font-size:clamp(24px,7vw,30px); line-height:1.12; }
  .lm-lead { font-size:17px; line-height:1.65; }
  .lm-seo-section,.lm-extra,.lm-featured { padding:48px 0; }
  .lm-info-card,.lm-extra-card,.lm-featured-body,.lm-cta-box { padding:20px; border-radius:20px; }
  .lm-btn { background:#075041; }
  .lm-accent { color:#075041; }
  .lm-pill-dot { background:#075041; }
  @media (min-width:760px){
    .lm-hero-inner { padding:76px 0 68px; }
    .lm-h1 { font-size:clamp(44px,6vw,58px); line-height:1.05; }
    .lm-h2 { font-size:clamp(34px,4vw,46px); }
    .lm-seo-section,.lm-extra,.lm-featured { padding:72px 0; }
  }
  @media (min-width:1024px){
    .lm-h1 { font-size:64px; }
    .lm-h2 { font-size:48px; }
  }
</style>

<main class="lm-page">
  <section class="lm-hero">
    <div class="lm-wrap lm-hero-inner">
      <div class="lm-pill"><span class="lm-pill-dot"></span>Darmowe materiały marketingowe B2B do pobrania</div>
      <h1 class="lm-h1">Darmowe materiały marketingowe dla firm B2B.<br /><span class="lm-accent">Checklisty, audyty i szablony, które pomagają podjąć lepszą decyzję.</span></h1>
      <p class="lm-lead">Pobierz gotowe narzędzia do sprawdzenia kampanii Meta Ads i Google Ads, oceny strony internetowej i poprawy jakości leadów. Każdy materiał jest praktyczny i napisany przez praktyka — nie przez konsultanta od prezentacji.</p>
      <div class="lm-rich-copy">
        <p>Większość firm, które prowadzą kampanie reklamowe lub mają stronę internetową, popełnia te same błędy — i nie wie o tym, dopóki nie porówna swoich działań z konkretną checklistą lub audytem.</p>
        <p>Materiały dostępne w tej bibliotece powstały na bazie realnych problemów firm B2B: przepalone budżety reklamowe, strony, które nie konwertują, leady zbyt słabej jakości i brak spójności między reklamą a stroną docelową.</p>
        <p>Materiały są bezpłatne i dostępne od razu po zostawieniu adresu e-mail. Nie ma zobowiązań ani agresywnej sprzedaży. Jeśli po pobraniu okaże się, że chcesz porozmawiać o konkretnym problemie — formularz kontaktowy jest zawsze dostępny.</p>
      </div>
      <div class="lm-search">
        <input id="lm-search" class="lm-search-input" type="search" placeholder="Szukaj materiału po nazwie lub opisie..." aria-label="Szukaj materiału" />
        <a href="<?php echo esc_url(home_url("/#kontakt")); ?>" class="lm-btn">Umów rozmowę</a>
      </div>
    </div>
  </section>

  <section class="lm-seo-section is-soft" id="problem">
    <div class="lm-wrap">
      <div class="lm-section-head">
        <div class="lm-eyebrow">Rola strony</div>
        <h2 class="lm-h2">Dlaczego biblioteka lead magnetów jest aktywem SEO i sprzedaży?</h2>
      </div>
      <div class="lm-rich-copy">
        <p>Strona <strong>/lead-magnety/</strong> agreguje podstrony z unikalnymi adresami, treścią i frazami kluczowymi. Dzięki temu buduje topical authority wokół tematów takich jak audyt Meta Ads, checklista Google Ads, szablon landing page i poprawa jakości leadów.</p>
        <p>Z perspektywy sprzedaży lead magnety pozwalają pozyskiwać kontakty od osób, które jeszcze nie są gotowe na bezpośrednią rozmowę. Pobierają checklistę, zostawiają e-mail, a później mogą wejść do newslettera, remarketingu lub spokojnej rozmowy sprzedażowej.</p>
      </div>
      <div class="lm-problem-grid">
        <article class="lm-info-card"><strong>Ruch z konkretną intencją</strong><p>Frazy typu „checklista Meta Ads do pobrania” i „audyt Google Ads darmowy” przyciągają osoby z realnym problemem.</p></article>
        <article class="lm-info-card"><strong>Podstrony pod long-tail</strong><p>Każdy materiał może mieć własny URL, tekst SEO, FAQ i linkowanie do usługi lub artykułu blogowego.</p></article>
        <article class="lm-info-card"><strong>Tańsze pozyskiwanie kontaktów</strong><p>Materiały zamieniają anonimowy ruch w kontakty, które można edukować newsletterem i kampaniami remarketingowymi.</p></article>
        <article class="lm-info-card"><strong>Dane do optymalizacji</strong><p>Oddzielne zdarzenia pobrań pokazują, które tematy generują leady i późniejsze rozmowy sprzedażowe.</p></article>
      </div>
    </div>
  </section>

  <section class="lm-seo-section" id="narzedzia">
    <div class="lm-wrap">
      <div class="lm-section-head">
        <div class="lm-eyebrow">Co znajdziesz</div>
        <h2 class="lm-h2">Checklisty, audyty i szablony do samodzielnego wdrożenia.</h2>
      </div>
      <div class="lm-rich-copy">
        <p>Wybierz materiał, który odpowiada Twojemu aktualnemu problemowi. Każdy z nich można zastosować samodzielnie — bez konieczności współpracy z agencją.</p>
      </div>
      <div class="lm-tool-grid">
        <article class="lm-info-card"><strong>Meta Ads</strong><p>Checklisty i audyty do sprawdzenia piksela, kreacji, lejka, remarketingu i jakości leadów.</p></article>
        <article class="lm-info-card"><strong>Google Ads</strong><p>Materiały do diagnozy kampanii Search, intencji słów kluczowych, wykluczeń i kosztu pozyskania leada.</p></article>
        <article class="lm-info-card"><strong>Strony i landing pages</strong><p>Szablony do oceny komunikatu, CTA, formularzy, zaufania, szybkości i gotowości strony na ruch płatny.</p></article>
        <article class="lm-info-card"><strong>Lead generation</strong><p>Narzędzia do poprawy jakości leadów, briefów, budżetu marketingowego i spójności działań sprzedażowych.</p></article>
      </div>
    </div>
  </section>

  <section class="lm-category-bar">
    <div class="lm-wrap">
      <div class="lm-categories" id="lm-categories">
        <button type="button" class="lm-cat-btn is-active" data-cat="all">Wszystkie</button>
        <?php foreach ($categories as $slug => $name) : ?>
          <button type="button" class="lm-cat-btn" data-cat="<?php echo esc_attr($slug); ?>"><?php echo esc_html($name); ?></button>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <?php if (!empty($featured)) : ?>
    <section class="lm-featured">
      <div class="lm-wrap">
        <article class="lm-featured-card">
          <div class="lm-featured-visual">
            <?php if (!empty($featured["image"])) : ?>
              <img src="<?php echo esc_url((string) $featured["image"]); ?>" alt="<?php echo esc_attr((string) $featured["title"]); ?>" loading="lazy" />
            <?php endif; ?>
          </div>
          <div class="lm-featured-body">
            <?php if (!empty($featured["badge"])) : ?><span class="lm-badge"><?php echo esc_html((string) $featured["badge"]); ?></span><?php endif; ?>
            <h2 class="lm-featured-title"><?php echo esc_html((string) $featured["title"]); ?></h2>
            <p class="lm-featured-excerpt"><?php echo esc_html((string) $featured["excerpt"]); ?></p>
            <a class="lm-btn" href="<?php echo esc_url((string) $featured["url"]); ?>"><?php echo esc_html(!empty($featured["cta"]) ? (string) $featured["cta"] : "Zobacz materiał"); ?></a>
          </div>
        </article>
      </div>
    </section>
  <?php endif; ?>

  <section class="lm-grid-section">
    <div class="lm-wrap">
      <div class="lm-grid-head">
        <div>
          <div class="lm-eyebrow">Katalog materiałów</div>
          <h2 class="lm-h2">Wszystkie materiały — checklisty, audyty i szablony dla firm B2B.</h2>
          <p class="lm-lead">Wybierz materiał, który odpowiada Twojemu aktualnemu problemowi. Każdy z nich można zastosować samodzielnie — bez konieczności współpracy z agencją.</p>
        </div>
      </div>
      <?php if (!empty($lead_magnets)) : ?>
        <div class="lm-grid" id="lm-grid">
          <?php foreach ($lead_magnets as $item) : ?>
            <?php
            $search_text = (string) $item["title"] . " " . (string) $item["excerpt"];
            $search_text = function_exists("mb_strtolower") ? mb_strtolower($search_text) : strtolower($search_text);
            ?>
            <article class="lm-card" data-cat="<?php echo esc_attr((string) $item["category_slug"]); ?>" data-search="<?php echo esc_attr($search_text); ?>">
              <div class="lm-card-top">
                <div>
                  <div class="lm-card-category"><?php echo esc_html((string) $item["category"]); ?></div>
                  <div class="lm-card-type"><?php echo esc_html(!empty($item["type"]) ? (string) $item["type"] : "Materiał do pobrania"); ?></div>
                </div>
              </div>
              <h3 class="lm-card-title"><?php echo esc_html((string) $item["title"]); ?></h3>
              <p class="lm-card-excerpt"><?php echo esc_html((string) $item["excerpt"]); ?></p>
              <?php if (!empty($item["meta"])) : ?><div class="lm-card-meta"><?php echo esc_html((string) $item["meta"]); ?></div><?php endif; ?>
              <a class="lm-card-link" href="<?php echo esc_url((string) $item["url"]); ?>">Przejdź do materiału →</a>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else : ?>
        <div class="lm-empty">Nie masz jeszcze dodanych materiałów. Wejdź do panelu admina: <strong>Materiały → Dodaj nowy</strong>.</div>
      <?php endif; ?>
    </div>
  </section>

  <section class="lm-extra">
    <div class="lm-wrap lm-extra-grid">
      <article class="lm-extra-card">
        <div class="lm-eyebrow">SEO i struktura</div>
        <h3>Każdy lead magnet powinien mieć własną podstronę.</h3>
        <p>Osobny URL pozwala zaindeksować konkretną frazę, dodać pełny tekst SEO, FAQ, linkowanie wewnętrzne i zdarzenie konwersji dla danego materiału.</p>
        <ul class="lm-extra-list">
          <li>/lead-magnety/checklista-meta-ads/</li>
          <li>/lead-magnety/audyt-google-ads/</li>
          <li>/lead-magnety/checklista-landing-page/</li>
          <li>/lead-magnety/kalkulator-cpl/</li>
        </ul>
      </article>
      <article class="lm-extra-card">
        <div class="lm-eyebrow">Pomiar konwersji</div>
        <h3>Śledź każde pobranie jako osobne zdarzenie.</h3>
        <p>Dla pobrań warto mierzyć zdarzenie <strong>lead_magnet_download</strong> z nazwą materiału. Dzięki temu wiesz, które checklisty i audyty generują najlepsze kontakty.</p>
        <ul class="lm-extra-list">
          <li>Google Tag Manager z parametrem nazwy materiału</li>
          <li>strona podziękowania lub zdarzenie inline po wysłaniu formularza</li>
          <li>ItemList schema na indeksie i DigitalDocument na podstronach</li>
        </ul>
      </article>
    </div>
  </section>

  <section class="lm-seo-section" id="audyt">
    <div class="lm-wrap">
      <div class="lm-cta-box">
        <div class="lm-eyebrow">Bezpłatna analiza</div>
        <h2 class="lm-h2">Chcesz sprawdzić kampanie, stronę lub jakość leadów szybciej?</h2>
        <div class="lm-rich-copy">
          <p>Materiały pozwalają zrobić pierwszy krok: zidentyfikować problem i zobaczyć, co wymaga poprawy. Jeśli chcesz dokładnej diagnozy, konkretnego planu działania i wskazania, gdzie tracisz budżet, możesz skorzystać z bezpłatnej analizy marketingu.</p>
        </div>
        <a href="<?php echo esc_url(home_url("/kontakt/")); ?>" class="lm-btn">Sprawdź swój marketing — darmowy audyt</a>
      </div>
    </div>
  </section>

  <section class="lm-seo-section is-soft" id="faq">
    <div class="lm-wrap">
      <div class="lm-section-head">
        <div class="lm-eyebrow">FAQ</div>
        <h2 class="lm-h2">Najczęstsze pytania o narzędzia marketingowe</h2>
      </div>
      <div class="lm-faq-grid">
        <?php foreach ($faq_items as $faq_item) : ?>
          <details class="lm-faq-item">
            <summary><?php echo esc_html((string) $faq_item["question"]); ?></summary>
            <p><?php echo esc_html((string) $faq_item["answer"]); ?></p>
          </details>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>

<?php if (!empty($schema_items)) : ?>
<script type="application/ld+json">
<?php
echo wp_json_encode([
    "@context" => "https://schema.org",
    "@type" => "ItemList",
    "name" => "Checklisty i materiały marketingowe do pobrania | Upsellio",
    "description" => "Katalog darmowych checklist, audytów i szablonów dla firm B2B: Meta Ads, Google Ads, landing page i lead generation.",
    "itemListElement" => $schema_items,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
?>
</script>
<?php endif; ?>

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

<script>
  (function () {
    const grid = document.getElementById("lm-grid");
    const searchInput = document.getElementById("lm-search");
    const categoryButtons = Array.from(document.querySelectorAll(".lm-cat-btn"));
    if (!grid || !searchInput || !categoryButtons.length) return;

    let activeCategory = "all";

    function normalize(value) {
      return String(value || "").toLowerCase().trim();
    }

    function updateGrid() {
      const searchValue = normalize(searchInput.value);
      const cards = Array.from(grid.querySelectorAll(".lm-card"));

      cards.forEach((card) => {
        const cardCategory = card.getAttribute("data-cat") || "";
        const searchableText = card.getAttribute("data-search") || "";
        const isCategoryMatch = activeCategory === "all" || cardCategory === activeCategory;
        const isSearchMatch = searchValue === "" || searchableText.includes(searchValue);
        card.classList.toggle("lm-hidden", !(isCategoryMatch && isSearchMatch));
      });
    }

    categoryButtons.forEach((button) => {
      button.addEventListener("click", function () {
        activeCategory = this.getAttribute("data-cat") || "all";
        categoryButtons.forEach((item) => item.classList.remove("is-active"));
        this.classList.add("is-active");
        updateGrid();
      });
    });

    searchInput.addEventListener("input", updateGrid);
  })();
</script>
<?php
get_footer();
