<?php
if (!defined("ABSPATH")) {
    exit;
}
?>
<link rel="stylesheet" href="<?php echo esc_url(get_template_directory_uri() . "/assets/css/front-page-sellwise.css?ver=2026050512"); ?>" />

<section class="hero" id="start">
  <div class="wrap hero-wrap">
    <div class="hero-copy">
      <div class="hero-pill reveal in d1">
        <div class="hero-pill-dot">B2B</div>
        <span>Marketing B2B + Sprzedaż w jednym ręku</span>
      </div>

      <h1 class="h1 hero-h1 reveal in d1">
        Wiem, jak wygląda P&amp;L handlowca. Wiem, jak wygląda kampania, która go realnie karmi.
      </h1>

      <p class="lead hero-lead reveal in d2">
        10 lat sprzedaży B2B. Robiłem 1,5 mln zł netto miesięcznie jako handlowiec. Zbudowałem sklep B2B, który doszedł do 500 tys. zł miesięcznie z marżą 4x wyższą niż dział handlowy. Buduję reklamy, stronę i lejek jako jeden system.
      </p>

      <div class="hero-guarantee-strip reveal in d2">
        <div class="hero-guarantee-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="20 6 9 17 4 12"/></svg>
          Reklama + strona + lejek jako jeden system
        </div>
        <div class="hero-guarantee-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="20 6 9 17 4 12"/></svg>
          Praca 1:1 z właścicielem lub szefem sprzedaży
        </div>
        <div class="hero-guarantee-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="20 6 9 17 4 12"/></svg>
          Pierwsze spotkanie 0 zł, 30 minut, bez prezentacji
        </div>
      </div>

      <div class="hero-actions reveal in d3">
        <a href="#kontakt" class="btn btn-primary">Umów bezpłatną diagnozę</a>
        <a href="#system" class="btn btn-secondary">Zobacz, co dokładnie robię →</a>
      </div>
      <p class="hero-micro reveal in d3">Odpowiadam osobiście w 24h. Telefon: <?php echo esc_html((string) $contact_phone); ?></p>
    </div>

    <aside class="hero-aside hero-aside-system reveal in d2" aria-label="Mini dashboard analizy leadów">
      <div class="hero-author-card">
        <div class="hero-author-photo">
          <?php echo function_exists("upsellio_render_home_media_image") ? upsellio_render_home_media_image("hero_portrait", ["class" => "hero-author-img", "size" => "medium_large", "loading" => "eager", "fetchpriority" => "high"]) : ""; ?>
        </div>
        <div>
          <div class="hero-author-kicker">Sebastian Kelm</div>
          <p>Marketing B2B oparty o dane, sprzedaż i konwersję.</p>
        </div>
      </div>

      <div class="hero-aside-label">Przykładowy wynik z kampanii — branża: usługi B2B</div>
      <div class="hero-system" id="hero-system">
        <div class="hero-system-head">
          <div>
            <div class="hero-system-side-title">23 810</div>
            <div class="hero-system-side-sub">ruch / mies.</div>
          </div>
          <div>
            <div class="hero-system-side-title">362</div>
            <div class="hero-system-side-sub">leady do oceny</div>
          </div>
        </div>
        <div class="hero-system-core">
          <div class="hero-core-head">
            <span>Lead quality</span>
            <strong>72%</strong>
          </div>
          <div class="hero-kpi-row">
            <div class="hero-kpi-block">
              <span>Konwersja strony</span>
              <b>2,3%</b>
              <i data-hero-kpi-progress style="width:62%"></i>
            </div>
            <div class="hero-kpi-block">
              <span>Budżet bez efektu</span>
              <b>-18%</b>
              <i data-hero-kpi-progress style="width:48%"></i>
            </div>
          </div>
          <div class="hero-spark-grid" data-hero-spark aria-hidden="true">
            <span style="height:32%"></span><span style="height:58%"></span><span style="height:44%"></span><span style="height:76%"></span><span style="height:61%"></span><span style="height:86%"></span>
          </div>
        </div>
        <div class="hero-system-pipe" aria-label="Lejek: ruch, strona, lead, rozmowa, sprzedaż">
          <div class="hero-pipe-step is-active"><span>Ruch</span><b>100%</b></div>
          <div class="hero-pipe-step"><span>Strona</span><b>42%</b></div>
          <div class="hero-pipe-step"><span>Lead</span><b>18%</b></div>
          <div class="hero-pipe-step"><span>Rozmowa</span><b>9%</b></div>
          <div class="hero-pipe-step"><span>Sprzedaż</span><b>4%</b></div>
        </div>
      </div>

      <?php
      if (function_exists("upsellio_render_lead_form")) {
          echo upsellio_render_lead_form([
              "origin"       => "hero-microform",
              "variant"      => "micro",
              "heading"      => "Sprawdź, gdzie uciekają zapytania",
              "submit_label" => "Umów bezpłatną diagnozę →",
              "redirect_url" => home_url("/#kontakt"),
              "css_class"    => "hero-microform",
              "form_id"      => "hero-analiza",
          ]);
      }
      ?>
    </aside>
  </div>
</section>

<section class="logos-section section-border" id="klienci" aria-label="Liczby z mojej drogi">
  <div class="wrap">
    <div class="section-num">
      <span class="section-num-digit">02</span>
      <span class="section-num-line"></span>
    </div>
    <h2 class="h2" style="margin-bottom:12px;">Liczby z mojej drogi — nie z prezentacji agencji</h2>
    <div class="logos-grid">
      <?php foreach ([
          ["10", "lat sprzedaży B2B jako handlowiec i dyrektor sprzedaży"],
          ["1,5 mln", "PLN/mc netto regularnej sprzedaży po 4 latach pracy handlowej"],
          ["500 tys.", "PLN/mc ze sklepu B2B z marżą 4x wyższą niż dział handlowy"],
          ["15", "osób w zespole, którym zarządzałem jako dyrektor sprzedaży"],
      ] as $credibility) : ?>
        <div class="logo-card"><strong><?php echo esc_html($credibility[0]); ?></strong><br><?php echo esc_html($credibility[1]); ?></div>
      <?php endforeach; ?>
    </div>
    <p class="body" style="color:var(--text-muted-strong);margin-top:14px;">Te liczby to lata pracy nad realnymi cyklami sprzedaży B2B, zanim zacząłem budować lejki dla innych.</p>
  </div>
</section>

<section class="section section-border bg-soft" id="problem">
  <div class="wrap">
    <div class="section-num reveal">
      <span class="section-num-digit">01</span>
      <span class="section-num-line"></span>
      <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-soft);">Dlaczego kampanie nie działają</span>
    </div>
    <div style="max-width:820px">
      <h2 class="h2 reveal d1">Masz ruch, ale brakuje zapytań. Wiesz, gdzie jest problem?</h2>
      <p class="body reveal d2" style="margin-top:16px">Reklama to tylko jeden element. Bez odpowiedniej strony docelowej, klarownej oferty i systemu prowadzącego do decyzji, nawet dobra kampania przecieka.</p>
    </div>

    <div class="cs-table-wrap reveal d2">
      <table class="cs-table">
        <thead>
          <tr>
            <th>Objaw, który widzisz</th>
            <th>Prawdopodobna przyczyna</th>
            <th>Co sprawdzam najpierw</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Dużo kliknięć, mało zapytań</td>
            <td>Strona nie konwertuje lub CTA jest słabe</td>
            <td>Heatmapy, lejek GA4, testy CTA</td>
          </tr>
          <tr>
            <td>Zapytania są, ale niekwalifikowane</td>
            <td>Zły targeting lub niejasna oferta</td>
            <td>Grupy odbiorców, komunikat, formularz</td>
          </tr>
          <tr>
            <td>Kampania jest droga, CPL rośnie</td>
            <td>Zła struktura kampanii lub słaba jakość reklamy</td>
            <td>Quality Score, Ad Relevance, landing match</td>
          </tr>
          <tr>
            <td>Reklamy działały, teraz przestały</td>
            <td>Nasycenie grupy, zmiana algorytmu, sezonowość</td>
            <td>Frequency, nowe kreacje, ekspansja grup</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="section-cta-row reveal d3">
      <a href="#kontakt" class="btn btn-primary btn-sm">Sprawdź mój marketing →</a>
      <a href="#jak-dzialam" class="btn btn-secondary btn-sm">Zobacz, jak działam</a>
    </div>
  </div>
</section>

<section class="section section-border" id="system">
  <div class="wrap">
    <div class="section-num reveal">
      <span class="section-num-digit">02</span>
      <span class="section-num-line"></span>
      <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-soft);">Główna oferta</span>
    </div>
    <div style="max-width:820px">
      <h2 class="h2 reveal d1">Trzy filary pozyskiwania leadów B2B dla Twojej firmy</h2>
      <p class="body reveal d2" style="margin-top:16px">Najpierw porządkuję to, co ma największy wpływ na wynik: źródła ruchu, stronę i konwersję. Dopiero potem dokładam optymalizacje i automatyzacje.</p>
    </div>

    <?php
    $service_cards = [
        [
            "title"   => "Google Ads B2B",
            "text"    => "Przechwytywanie intencji zakupowej dokładnie wtedy, gdy Twój klient aktywnie szuka usługi. Kampanie Search, Display i Performance Max dopasowane do lejka sprzedaży B2B.",
            "url"     => $google_ads_url,
            "slot"    => "service_google",
            "class"   => "service-card-google",
            "kpis"    => ["Niższy CPL", "Wyższy CTR", "Lepsza jakość leadów"],
            "icon"    => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 18V9m7 9V5m7 13v-6" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/><path d="M4 19h16" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/></svg>',
        ],
        [
            "title"   => "Meta Ads dla firm B2B",
            "text"    => "Docieranie do decydentów, budowanie popytu i retargeting osób, które znają Twoją ofertę. Reklamy na Facebooku i Instagramie zoptymalizowane pod lead gen i konwersję.",
            "url"     => $meta_ads_url,
            "slot"    => "service_meta",
            "class"   => "service-card-meta",
            "kpis"    => ["Leady od decydentów", "Retargeting", "Budowanie popytu"],
            "icon"    => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v4m0 10v4M3 12h4m10 0h4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="12" cy="12" r="7" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="2.5" fill="currentColor"/></svg>',
        ],
        [
            "title"   => "Strony WWW pod konwersję",
            "text"    => "Projektuję przekaz, strukturę i CTA tak, żeby ruch zamieniał się w zapytania sprzedażowe. Landing page B2B, strony firmowe i sklepy zoptymalizowane pod leady.",
            "url"     => $websites_url,
            "slot"    => "service_web",
            "class"   => "service-card-web",
            "kpis"    => ["Wyższa konwersja", "Klarowny przekaz", "CTA, które działają"],
            "icon"    => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="11" rx="2" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M8 20h8m-4-4v4M7 9h5m-5 3h10" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
        ],
    ];
    ?>
    <div class="service-grid service-grid-visual section-grid-gap-lg">
      <?php foreach ($service_cards as $index => $sc) : ?>
        <a class="service-card service-card-link service-card-visual <?php echo esc_attr($sc["class"]); ?> reveal <?php echo $index > 0 ? "d".$index : ""; ?>" href="<?php echo esc_url($sc["url"]); ?>">
          <span class="service-card-icon"><?php echo $sc["icon"]; ?></span>
          <span class="service-card-media">
            <?php echo function_exists("upsellio_render_home_media_image") ? upsellio_render_home_media_image($sc["slot"], ["class" => "service-card-img", "size" => "medium_large"]) : ""; ?>
          </span>
          <span class="service-card-copy">
            <h3 class="h3"><?php echo esc_html($sc["title"]); ?></h3>
            <p class="body"><?php echo esc_html($sc["text"]); ?></p>
            <span style="display:flex;flex-wrap:wrap;gap:6px;margin-top:10px;">
              <?php foreach ($sc["kpis"] as $kpi) : ?>
                <span style="font-size:11px;font-weight:700;padding:3px 10px;background:var(--brand-soft);color:var(--brand-dark);border-radius:var(--r-pill);"><?php echo esc_html($kpi); ?></span>
              <?php endforeach; ?>
            </span>
            <span class="service-card-cta">Dowiedz się więcej →</span>
          </span>
        </a>
      <?php endforeach; ?>
    </div>
    <p class="body reveal d3" style="margin-top:22px;font-weight:700;color:var(--teal-dark)">Ruch → Konwersja → Lead → Sprzedaż — jeden spójny system, nie osobne kampanie</p>
  </div>
</section>

<section class="section section-border" id="pakiet-flagship" style="background:var(--section-dark);color:#fff;">
  <div class="wrap">
    <div class="section-num reveal">
      <span class="section-num-digit">06</span>
      <span class="section-num-line"></span>
      <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#94a3b8;">Pakiet kompletny</span>
    </div>
    <div style="max-width:920px">
      <h2 class="h2 reveal d1" style="color:#fff;">System Sprzedażowy B2B — reklama, strona, lejek, follow-up. Jedna osoba, jeden system, mierzona sprzedaż.</h2>
      <p class="body reveal d2" style="margin-top:16px;color:rgba(255,255,255,.78);">Dla firm, które nie chcą żonglować trzema dostawcami. Buduję pełny lejek od kliknięcia do umowy i raportuję wynik per kampania, per landing, per query.</p>
    </div>
    <div class="qualifier-grid reveal d2" style="margin-top:26px;">
      <div class="qualifier-card good" style="background:var(--section-dark-2);border-color:rgba(255,255,255,.16);">
        <div class="qualifier-label" style="color:#fff;">Co dostajesz</div>
        <div class="qualifier-item"><span class="qualifier-icon">✓</span><span>Audyt startowy w 5 dni</span></div>
        <div class="qualifier-item"><span class="qualifier-icon">✓</span><span>Pierwsze leady w 2-4 tygodnie</span></div>
        <div class="qualifier-item"><span class="qualifier-icon">✓</span><span>Landing pisany pod cykl B2B</span></div>
        <div class="qualifier-item"><span class="qualifier-icon">✓</span><span>Tracking i atrybucję do CRM</span></div>
        <div class="qualifier-item"><span class="qualifier-icon">✓</span><span>Cotygodniowy raport sprzedażowy</span></div>
        <div class="qualifier-item"><span class="qualifier-icon">✓</span><span>Telefon bezpośrednio do mnie</span></div>
      </div>
      <div class="qualifier-card" style="background:var(--section-dark-2);border-color:rgba(255,255,255,.16);color:#fff;">
        <div class="qualifier-label" style="color:#fff;">Widełki współpracy</div>
        <p style="margin:0;color:rgba(255,255,255,.82);line-height:1.7;">Pakiet od 6 000 zł / mc przy budżecie reklamowym min. 5 000 zł / mc po stronie klienta. Strona lub przebudowa landing page od 8 000 zł one-time. Minimum współpracy: 3 miesiące.</p>
        <p style="margin-top:12px;color:#fcd34d;font-weight:700;">To nie jest oferta dla każdej firmy. Jeśli jesteś przed progiem, polecę tańsze i sensowne rozwiązanie.</p>
      </div>
    </div>
    <div class="section-cta-row reveal d3">
      <a href="#kontakt" class="btn btn-primary btn-sm" style="background:linear-gradient(135deg,var(--accent),#ea580c);">Sprawdź, czy pasuję do Twojego biznesu →</a>
    </div>
  </div>
</section>

<section class="section section-border bg-soft" id="wyroznik">
  <div class="wrap">
    <div class="section-num reveal">
      <span class="section-num-digit">03</span>
      <span class="section-num-line"></span>
      <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-soft);">Dlaczego Upsellio, nie agencja</span>
    </div>
    <div style="max-width:820px">
      <h2 class="h2 reveal d1">Typowa agencja raportuje kliknięcia. Ja raportuje sprzedaż.</h2>
      <p class="body reveal d2" style="margin-top:16px">Nie ustawiam kampanii w oderwaniu od procesu handlowego. Patrzę na cały lejek: od kliknięcia, przez stronę, po jakość rozmów z klientem.</p>
    </div>

    <div class="compare-table-wrap reveal d2">
      <table class="compare-table">
        <thead>
          <tr>
            <th>Co sprawdzam / robię</th>
            <th>Typowa agencja</th>
            <th class="col-us">Upsellio</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Pomiar jakości leadów (nie tylko liczby)</td>
            <td class="col-no">—</td>
            <td class="col-us-yes">✓</td>
          </tr>
          <tr>
            <td>Analiza lejka: reklama + strona + oferta</td>
            <td class="col-no">Rzadko</td>
            <td class="col-us-yes">✓</td>
          </tr>
          <tr>
            <td>Optymalizacja CPL i kosztu pozyskania klienta</td>
            <td class="col-no">Częściowo</td>
            <td class="col-us-yes">✓</td>
          </tr>
          <tr>
            <td>Raportowanie w języku sprzedaży (nie kliknięć)</td>
            <td class="col-no">—</td>
            <td class="col-us-yes">✓</td>
          </tr>
          <tr>
            <td>Strona + kampania jako jeden system</td>
            <td class="col-no">—</td>
            <td class="col-us-yes">✓</td>
          </tr>
          <tr>
            <td>Kontakt z jedną osobą, nie z rotującym zespołem</td>
            <td class="col-no">—</td>
            <td class="col-us-yes">✓</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="section-cta-row reveal d3">
      <a href="#kontakt" class="btn btn-primary btn-sm">Porozmawiajmy o wynikach</a>
    </div>
  </div>
</section>

<section class="section section-border" id="case-study">
  <div class="wrap">
    <div class="section-num reveal">
      <span class="section-num-digit">07</span>
      <span class="section-num-line"></span>
      <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-soft);">Liczby, nie historie sukcesu</span>
    </div>
    <div style="max-width:820px">
      <h2 class="h2 reveal d1">Trzy realne wdrożenia. Branże, liczby, decyzje.</h2>
    </div>
    <div class="service-grid section-grid-gap-lg reveal d2">
      <article class="service-card">
        <h3 class="h3">Producent maszyn B2B</h3>
        <p class="body">Stan: 4 leady/mc, CPL 380 zł, cykl 6 mies. Po 90 dniach: 23 leady/mc, CPL 145 zł, 4 zamknięcia po 60 dniach.</p>
        <p class="body">Decyzja: przebudowa strony oferty + Google Ads na bottom-funnel queries.</p>
      </article>
      <article class="service-card">
        <h3 class="h3">SaaS dla logistyki</h3>
        <p class="body">Stan: dużo ruchu, 0,4% konwersji. Po 60 dniach: 2,1% konwersji, 80% leadów z firm 50+ pracowników.</p>
        <p class="body">Decyzja: zmiana copy strony + segmentacja formularza + retargeting Meta.</p>
      </article>
      <article class="service-card">
        <h3 class="h3">Consulting B2B</h3>
        <p class="body">Stan: 320 tys. zł rocznie i brak przewidywalności. Po 6 miesiącach: 720 tys. zł rocznie z regularnym pipeline.</p>
        <p class="body">Decyzja: pełen pakiet — strona, Google Ads, follow-up sequence.</p>
      </article>
    </div>

    <div class="section-cta-row reveal d3">
      <a href="<?php echo esc_url($marketing_portfolio_url); ?>" class="btn btn-secondary btn-sm">Zobacz wszystkie wdrożenia →</a>
    </div>
  </div>
</section>

<section class="section section-border bg-soft" id="o-mnie">
  <div class="wrap">
    <div class="section-num reveal">
      <span class="section-num-digit">04</span>
      <span class="section-num-line"></span>
      <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-soft);">Pracowałem po obu stronach stołu</span>
    </div>
    <div class="about-expert">
      <div class="about-expert-copy">
        <h2 class="h2 reveal d1">Większość ludzi w marketingu nigdy nie sprzedawała. Ja sprzedaję od 10 lat i dlatego moje lejki działają.</h2>
        <p class="body reveal d2" style="margin-top:18px">Zacząłem jako handlowiec B2B. Po 2 latach robiłem 1 mln zł sprzedaży miesięcznie, po 4 latach regularnie 1,5 mln zł. Wiem, co dzieje się w kalendarzu handlowca, kiedy połowa leadów to "zbieram informacje".</p>
        <p class="body reveal d2" style="margin-top:14px">Potem zbudowałem sklep B2B, który po 2 latach doszedł do 500 tys. zł miesięcznie i marży 4x wyższej niż dział handlowy. Po 7 latach zostałem dyrektorem sprzedaży i prowadziłem zespół 15 osób.</p>
        <p class="body reveal d3" style="margin-top:14px">Dlatego projektuję kampanie pod matematykę P&amp;L i jakość rozmów, nie pod CTR. Strony i lejki piszę sam, bez podwykonawców.</p>
        <div class="tool-badges reveal d3" aria-label="Narzędzia używane w pracy">
          <span>Google Ads</span>
          <span>Meta Ads</span>
          <span>GA4</span>
          <span>Search Console</span>
          <span>Looker Studio</span>
          <span>HotJar</span>
        </div>
      </div>
      <div class="about-expert-card reveal d2">
        <div class="about-expert-photo">
          <?php echo function_exists("upsellio_render_home_media_image") ? upsellio_render_home_media_image("about_portrait", ["class" => "about-expert-img", "size" => "large"]) : ""; ?>
        </div>
        <div class="about-expert-card-body">
          <h3 class="h3">Sebastian Kelm</h3>
          <p>10 lat sprzedaży B2B · założyciel Upsellio</p>
        </div>
        <div class="about-stats">
          <div class="about-stat"><strong>10+</strong><span>lat praktyki</span></div>
          <div class="about-stat"><strong>B2B</strong><span>sprzedaż i leady</span></div>
          <div class="about-stat"><strong>CRO</strong><span>strony pod konwersję</span></div>
          <div class="about-stat"><strong>1:1</strong><span>pracuję sam, nie z juniorami</span></div>
        </div>
      </div>
    </div>
    <div class="section-cta-row reveal d3">
      <a href="#kontakt" class="btn btn-primary btn-sm">Porozmawiajmy o leadach →</a>
      <a href="<?php echo esc_url(home_url("/o-mnie/")); ?>" class="btn btn-secondary btn-sm">Więcej o mnie</a>
    </div>
  </div>
</section>

<section class="section section-border" id="jak-dzialam">
  <div class="wrap">
    <div class="section-num reveal">
      <span class="section-num-digit">06</span>
      <span class="section-num-line"></span>
      <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-soft);">Jak działam</span>
    </div>
    <div class="lead-section-grid">
      <div>
        <h2 class="h2 reveal d1">Najpierw diagnoza. Potem działanie.</h2>
        <p class="body reveal d2" style="margin-top:16px">Zanim cokolwiek uruchomię, sprawdzam, gdzie naprawdę traci Twój marketing. Nie zakładam, że problem jest w reklamach — może być na stronie, w ofercie albo w formularzu kontaktowym.</p>

        <div class="timeline-numbered reveal d2" style="margin-top:32px;">
          <?php
          $steps = [
              [
                  "num"         => "01",
                  "title"       => "Diagnoza",
                  "duration"    => "Tydzień 1",
                  "desc"        => "Sprawdzam kampanie, stronę, ofertę i jakość leadów. Szukam realnej przyczyny braku sprzedaży, nie objawów.",
                  "deliverable" => "Dostajesz: raport analizy z listą priorytetów",
              ],
              [
                  "num"         => "02",
                  "title"       => "Strategia i plan",
                  "duration"    => "Tydzień 2",
                  "desc"        => "Układam priorytety: co poprawić w reklamach, co uprościć na stronie i który komunikat ma prowadzić do decyzji.",
                  "deliverable" => "Dostajesz: roadmapę z kolejnością wdrożeń i estymowanym wpływem",
              ],
              [
                  "num"         => "03",
                  "title"       => "Wdrożenie",
                  "duration"    => "Tydzień 3–4",
                  "desc"        => "Wdrażam kampanie, treści, sekcje sprzedażowe, CTA i pomiar GA4 tak, żeby każdy element pracował na leady.",
                  "deliverable" => "Dostajesz: uruchomione kampanie i poprawioną stronę",
              ],
              [
                  "num"         => "04",
                  "title"       => "Optymalizacja",
                  "duration"    => "Stały proces",
                  "desc"        => "Na podstawie danych poprawiam CPL, konwersję strony i jakość rozmów. Nie dokładam budżetu — poprawiam wynik.",
                  "deliverable" => "Dostajesz: miesięczny raport w języku sprzedaży",
              ],
          ];
          foreach ($steps as $step) :
          ?>
            <div class="timeline-step">
              <div class="timeline-step-num"><?php echo esc_html($step["num"]); ?></div>
              <div class="timeline-step-content">
                <div class="timeline-step-title"><?php echo esc_html($step["title"]); ?></div>
                <div class="timeline-step-duration"><?php echo esc_html($step["duration"]); ?></div>
                <div class="timeline-step-desc"><?php echo esc_html($step["desc"]); ?></div>
                <div class="timeline-deliverable">
                  <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                  <?php echo esc_html($step["deliverable"]); ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="section-cta-row reveal d3" style="margin-top:8px;">
          <a href="#kontakt" class="btn btn-primary btn-sm">Umów bezpłatną rozmowę →</a>
        </div>
      </div>

      <div class="sticky-cta-aside reveal d2">
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r-xl);padding:28px;box-shadow:var(--shadow);">
          <div style="font-size:11px;font-weight:900;letter-spacing:1.2px;text-transform:uppercase;color:var(--brand);margin-bottom:10px;">Bezpłatna diagnoza</div>
          <h3 style="font-family:var(--font-display);font-size:22px;line-height:1.1;letter-spacing:-.5px;margin:0 0 10px;">Sprawdź, gdzie uciekają Twoje zapytania</h3>
          <p style="font-size:14px;color:var(--text-muted);line-height:1.6;margin:0 0 18px;">Opisz sytuację w 2 zdaniach. Wrócę z konkretnym kierunkiem działania.</p>
          <?php
          if (function_exists("upsellio_render_lead_form")) {
              echo upsellio_render_lead_form([
                  "origin"       => "process-sidebar-form",
                  "variant"      => "micro",
                  "heading"      => "",
                  "submit_label" => "Wyślij zapytanie →",
                  "redirect_url" => home_url("/#kontakt"),
                  "css_class"    => "process-microform",
                  "form_id"      => "process-form",
              ]);
          }
          ?>
          <p style="font-size:12px;color:var(--text-soft);margin-top:10px;text-align:center;">Bez spamu · Odpowiadam osobiście</p>
        </div>

        <blockquote style="margin:14px 0 0;padding:18px 20px;background:var(--surface-soft);border:1px solid var(--border);border-radius:var(--r-lg);">
          <p style="font-size:14px;font-style:italic;color:var(--text-muted);margin:0 0 10px;">"Po pierwszej rozmowie wiedzieliśmy dokładnie, co poprawić najpierw i gdzie uciekały zapytania."</p>
          <footer style="font-size:12px;font-weight:700;color:var(--text-soft);">— Marek T., właściciel firmy B2B</footer>
        </blockquote>
      </div>
    </div>
  </div>
</section>

<section class="section section-border bg-soft" id="dla-kogo">
  <div class="wrap">
    <div class="section-num reveal">
      <span class="section-num-digit">07</span>
      <span class="section-num-line"></span>
      <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-soft);">Dla kogo</span>
    </div>
    <div style="max-width:760px">
      <h2 class="h2 reveal d1">Z kim pracuję najlepiej — i kto skorzysta najmniej</h2>
      <p class="body reveal d2" style="margin-top:14px">Wolę powiedzieć wprost, żeby obie strony nie traciły czasu.</p>
    </div>

    <div class="qualifier-grid reveal d2">
      <div class="qualifier-card good">
        <div class="qualifier-label">✅ Dobry fit</div>
        <?php
        $yes_items = [
            "Prowadzisz firmę B2B (usługi, produkcja, IT, e-commerce B2B) i chcesz więcej kwalifikowanych zapytań",
            "Masz już kampanie lub stronę, ale wyniki są słabsze niż oczekujesz",
            "Zależy Ci na jakości leadów, nie tylko na ich liczbie",
            "Szukasz partnera, który patrzy na marketing i sprzedaż razem — nie osobno",
            "Możesz poświęcić 1-2h miesięcznie na przegląd wyników i ustalone priorytety",
            "Firma ma co najmniej 3-5 osób i realny produkt / usługę do sprzedania",
        ];
        foreach ($yes_items as $item) :
        ?>
          <div class="qualifier-item">
            <span class="qualifier-icon">✅</span>
            <span><?php echo esc_html($item); ?></span>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="qualifier-card bad">
        <div class="qualifier-label">— Mniejszy fit</div>
        <?php
        $no_items = [
            "Szukasz wyłącznie najtańszej opcji bez dbania o wynik",
            "Oczekujesz rozbudowanego zespołu agencji do wszystkiego naraz",
            "Nie chcesz rozmawiać o ofercie, kliencie i procesie sprzedaży",
            "Zależy Ci tylko na statystykach kliknięć, a nie na sprzedaży",
            "Nie masz określonego produktu lub ceny — szukasz dopiero modelu biznesowego",
        ];
        foreach ($no_items as $item) :
        ?>
          <div class="qualifier-item">
            <span class="qualifier-icon">—</span>
            <span><?php echo esc_html($item); ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="section-cta-row reveal d3">
      <a href="#kontakt" class="btn btn-primary btn-sm">Sprawdź, czy pasuję do Twojego projektu</a>
    </div>
  </div>
</section>

<section class="lead-magnet-band section-border" id="checklist">
  <div class="wrap">
    <div class="lead-magnet-grid">
      <div>
        <div class="lead-magnet-eyebrow">Darmowy zasób</div>
        <h2>Checklist: 27 punktów, które sprawdzam zanim ruszę z kampanią</h2>
        <p>Lista konkretnych elementów strony, kampanii i oferty, które mają największy wpływ na jakość leadów B2B. Pobierz bezpłatnie.</p>

        <div class="lead-magnet-checklist">
          <div class="lead-magnet-check-item">Audyt struktury kampanii Google Ads i Meta Ads</div>
          <div class="lead-magnet-check-item">Checklista konwersji strony — 12 najczęstszych błędów</div>
          <div class="lead-magnet-check-item">Ocena jakości CTA i przekazu oferty</div>
          <div class="lead-magnet-check-item">Konfiguracja GA4 pod pomiar leadów B2B</div>
        </div>
      </div>

      <div class="lead-magnet-form-wrap">
        <div class="lead-magnet-form-card">
          <p style="color:rgba(255,255,255,.7);font-size:13px;margin:0 0 14px;">Wpisz e-mail → dostaniesz PDF</p>
          <?php
          if (function_exists("upsellio_render_lead_form")) {
              echo upsellio_render_lead_form([
                  "origin"          => "lead-magnet",
                  "variant"         => "email-only",
                  "heading"         => "",
                  "submit_label"    => "Wyślij mi checklist →",
                  "redirect_url"    => home_url("/#checklist"),
                  "css_class"       => "lead-magnet-form",
                  "hidden_service"  => "Checklist: 27 punktów przed startem kampanii B2B",
                  "preset_message"  => "Prośba o PDF: Checklist 27 punktów przed startem kampanii (strona główna).",
                  "hidden_lead_name" => "Checklist PDF — formularz SG",
              ]);
          }
          ?>
          <p class="lead-magnet-form-note">Bez spamu. Jeden e-mail z checklistą.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section section-border bg-soft" id="faq">
  <div class="wrap">
    <div class="section-num reveal">
      <span class="section-num-digit">08</span>
      <span class="section-num-line"></span>
      <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-soft);">FAQ</span>
    </div>
    <div style="max-width:720px">
      <h2 class="h2 reveal d1">Najczęściej zadawane pytania o kampanie Google Ads i Meta Ads B2B</h2>
    </div>

    <div class="faq-schema reveal d2">
      <?php foreach ($home_faqs as $faq) : ?>
        <div class="faq-schema-item">
          <button class="faq-schema-q" type="button" aria-expanded="false">
            <span><?php echo esc_html($faq["q"]); ?></span>
            <span class="faq-schema-icon" aria-hidden="true">+</span>
          </button>
          <div class="faq-schema-a" role="region"><?php echo esc_html($faq["a"]); ?></div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="section-cta-row reveal d3">
      <a href="#kontakt" class="btn btn-primary btn-sm">Masz inne pytanie? Napisz →</a>
    </div>
  </div>
</section>

<script type="application/ld+json">
<?php echo wp_json_encode([
    "@context" => "https://schema.org",
    "@type"    => "FAQPage",
    "mainEntity" => array_map(static function ($faq) {
        return [
            "@type" => "Question",
            "name"  => $faq["q"],
            "acceptedAnswer" => [
                "@type" => "Answer",
                "text"  => $faq["a"],
            ],
        ];
    }, $home_faqs),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
</script>

<section class="section section-border" id="kontakt">
  <div class="wrap">
    <div class="section-num reveal">
      <span class="section-num-digit">09</span>
      <span class="section-num-line"></span>
      <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-soft);">Kontakt</span>
    </div>
    <div style="max-width:720px;margin:0 auto 36px;">
      <h2 class="h2 reveal d1">Umów bezpłatną diagnozę marketingu</h2>
      <p class="body reveal d2" style="margin-top:12px;">Opowiesz o firmie, kampaniach i obecnych wynikach. Wrócę z konkretną rekomendacją: co poprawić najpierw, żeby zwiększyć liczbę wartościowych zapytań.</p>
    </div>
    <div class="contact-strategy-form contact-extended-layout" style="max-width:980px;margin:0 auto;">
      <div class="contact-extended-benefits">
        <h3 class="h3">Co dostaniesz po wysłaniu formularza?</h3>
        <ul>
          <li>Odpowiedź w ciągu 24h z pierwszym konkretnym kierunkiem działań.</li>
          <li>30-minutową rozmowę o kampanii, stronie i jakości leadów.</li>
          <li>Checklistę priorytetów do wdrożenia po rozmowie.</li>
          <li>Ocenę, czy Twoja strona konwertuje ruch na zapytania.</li>
        </ul>
        <blockquote>"Po pierwszej rozmowie wiedzieliśmy dokładnie, co poprawić najpierw i gdzie uciekały zapytania."</blockquote>
        <div class="contact-channels">
          <a href="tel:<?php echo esc_attr(preg_replace("/\s+/", "", (string) $contact_phone)); ?>">📞 <?php echo esc_html((string) $contact_phone); ?></a>
          <a href="<?php echo esc_url(function_exists("upsellio_get_contact_page_url") ? upsellio_get_contact_page_url() : home_url("/kontakt/")); ?>">📅 Umów termin</a>
          <a href="<?php echo esc_url($linkedin_url); ?>" target="_blank" rel="noopener noreferrer">💼 LinkedIn</a>
        </div>
        <p style="font-size:13px;color:var(--text-soft);margin-top:14px;">Pracuję z firmami B2B, produkcyjnymi, IT i e-commerce. Minimalny budżet reklamowy: 2 000 zł/mies. na platformę.</p>
      </div>
      <?php
      if (function_exists("upsellio_render_lead_form")) {
          echo upsellio_render_lead_form([
              "origin"          => "contact-form",
              "variant"         => "full",
              "submit_label"    => "Umów bezpłatną diagnozę",
              "fineprint"       => "Bez spamu. Odpowiadam osobiście w ciągu 24h.",
              "service_options" => [
                  "Kampanie Google Ads B2B",
                  "Kampanie Meta Ads B2B",
                  "Tworzenie strony / landing page",
                  "Marketing + strona (oba)",
                  "Audyt kampanii lub strony",
                  "Nie wiem — chcę porozmawiać",
              ],
              "redirect_url"    => home_url("/#kontakt"),
              "form_id"         => "contact-form",
          ]);
      }
      ?>
    </div>
  </div>
</section>

<script type="application/ld+json">
<?php echo wp_json_encode([
    "@context"         => "https://schema.org",
    "@type"            => "LocalBusiness",
    "name"             => "Upsellio – Marketing B2B",
    "url"              => home_url("/"),
    "telephone"        => $contact_phone,
    "email"            => $contact_email,
    "description"      => "Specjalista Google Ads, Meta Ads i stron WWW dla firm B2B w Polsce. Sebastian Kelm – 10+ lat w sprzedaży i marketingu B2B.",
    "areaServed"       => "Polska",
    "availableLanguage"=> "Polish",
    "founder" => [
        "@type"     => "Person",
        "name"      => "Sebastian Kelm",
        "jobTitle"  => "Specjalista marketingu B2B",
        "sameAs"    => $linkedin_url,
    ],
    "hasOfferCatalog" => [
        "@type" => "OfferCatalog",
        "name"  => "Usługi marketingowe B2B",
        "itemListElement" => [
            ["@type" => "Offer", "itemOffered" => ["@type" => "Service", "name" => "Kampanie Google Ads B2B"]],
            ["@type" => "Offer", "itemOffered" => ["@type" => "Service", "name" => "Kampanie Meta Ads B2B"]],
            ["@type" => "Offer", "itemOffered" => ["@type" => "Service", "name" => "Tworzenie stron internetowych pod konwersję"]],
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
</script>

<script>
document.querySelectorAll('.faq-schema-q').forEach(function(btn) {
  btn.addEventListener('click', function() {
    var item = this.closest('.faq-schema-item');
    var isOpen = item.classList.contains('open');
    document.querySelectorAll('.faq-schema-item.open').forEach(function(el) {
      el.classList.remove('open');
      var b = el.querySelector('.faq-schema-q');
      if (b) b.setAttribute('aria-expanded', 'false');
    });
    if (!isOpen) {
      item.classList.add('open');
      this.setAttribute('aria-expanded', 'true');
    }
  });
});
</script>
