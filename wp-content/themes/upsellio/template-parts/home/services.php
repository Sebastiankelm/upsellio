<?php

if (!defined("ABSPATH")) {
    exit;
}

$founder_config = function_exists("upsellio_get_trust_seo_section") ? upsellio_get_trust_seo_section("founder") : [];
$founder_stats = isset($founder_config["stats"]) && is_array($founder_config["stats"]) ? array_slice($founder_config["stats"], 0, 4) : [];
$client_sectors = isset($founder_config["client_sectors"]) && is_array($founder_config["client_sectors"]) ? array_slice($founder_config["client_sectors"], 0, 4) : [];
$pricing_ranges = function_exists("upsellio_get_pricing_ranges_config") ? upsellio_get_pricing_ranges_config() : [];
$service_cards = [
    [
        "title" => "Meta Ads",
        "text" => "Docieranie do decydentów, budowanie popytu i retargeting osób, które już znają Twoją ofertę.",
        "url" => home_url("/marketing-meta-ads/"),
        "slot" => "service_meta",
        "class" => "service-card-meta",
        "icon" => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v4m0 10v4M3 12h4m10 0h4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="12" cy="12" r="7" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="2.5" fill="currentColor"/></svg>',
    ],
    [
        "title" => "Google Ads",
        "text" => "Przechwytywanie intencji zakupowej wtedy, gdy klient aktywnie szuka usługi lub produktu.",
        "url" => home_url("/marketing-google-ads/"),
        "slot" => "service_google",
        "class" => "service-card-google",
        "icon" => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 18V9m7 9V5m7 13v-6" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/><path d="M4 19h16" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/></svg>',
    ],
    [
        "title" => "Strony internetowe",
        "text" => "Projektowanie przekazu, struktury i CTA tak, żeby ruch zamieniał się w zapytania sprzedażowe.",
        "url" => home_url("/tworzenie-stron-internetowych/"),
        "slot" => "service_web",
        "class" => "service-card-web",
        "icon" => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="11" rx="2" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M8 20h8m-4-4v4M7 9h5m-5 3h10" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
    ],
];
?>
<section class="section section-border bg-soft" id="system">
      <div class="wrap">
        <div style="max-width:820px">
          <div class="eyebrow reveal">Główna oferta</div>
          <h2 class="h2 reveal d1">Trzy filary pozyskiwania leadów B2B</h2>
          <p class="body reveal d2" style="margin-top:18px">Najpierw porządkujemy to, co ma największy wpływ na wynik: źródła ruchu, stronę i konwersję. Dopiero później dokładamy automatyzacje, audyty i narzędzia.</p>
        </div>
        <div class="service-grid service-grid-visual section-grid-gap-lg">
          <?php foreach ($service_cards as $index => $service_card) : ?>
            <a class="service-card service-card-link service-card-visual <?php echo esc_attr((string) $service_card["class"]); ?> reveal <?php echo $index > 0 ? "d" . esc_attr((string) $index) : ""; ?>" href="<?php echo esc_url((string) $service_card["url"]); ?>">
              <span class="service-card-icon"><?php echo $service_card["icon"]; ?></span>
              <span class="service-card-media">
                <?php echo function_exists("upsellio_render_home_media_image") ? upsellio_render_home_media_image((string) $service_card["slot"], ["class" => "service-card-img", "size" => "medium_large"]) : ""; ?>
              </span>
              <span class="service-card-copy">
                <h3 class="h3"><?php echo esc_html((string) $service_card["title"]); ?></h3>
                <p class="body"><?php echo esc_html((string) $service_card["text"]); ?></p>
                <span class="service-card-cta">Dowiedz się więcej →</span>
              </span>
            </a>
          <?php endforeach; ?>
        </div>
        <p class="body reveal d3" style="margin-top:22px;font-weight:700;color:var(--teal-dark)">Ruch → Konwersja → Lead → Sprzedaż</p>
      </div>
    </section>

    <section class="section section-border" id="uslugi">
      <div class="wrap">
        <div style="max-width:720px">
          <div class="eyebrow reveal">Wyróżnik</div>
          <h2 class="h2 reveal d1">Sprzedaż B2B, CRO i praktyka handlowa</h2>
          <p class="body reveal d2" style="margin-top:18px">Nie ustawiam kampanii w oderwaniu od sprzedaży. Patrzę na cały proces: od kliknięcia, przez stronę, po jakość zapytania.</p>
          <ul class="fit-items reveal d2" style="margin-top:18px;max-width:680px">
            <li class="fit-item"><span class="fit-icon">✓</span><span>jasny przekaz dla konkretnej grupy odbiorców</span></li>
            <li class="fit-item"><span class="fit-icon">✓</span><span>kampanie dopasowane do intencji i etapu decyzji</span></li>
            <li class="fit-item"><span class="fit-icon">✓</span><span>strona WWW nastawiona na leady, nie tylko wygląd</span></li>
            <li class="fit-item"><span class="fit-icon">✓</span><span>pomiar konwersji, CPL i jakości zapytań</span></li>
          </ul>
          <p class="body reveal d3" style="margin-top:18px;max-width:680px">Automatyzacje, audyty, narzędzia i portfolio są wsparciem tego systemu, a nie osobnym chaosem usług.</p>
        </div>
        <div class="section-cta-row reveal d2">
          <a href="<?php echo esc_url(home_url("/#co-sprawdze")); ?>" class="btn btn-primary btn-sm">Sprawdź, co blokuje wyniki</a>
          <a href="<?php echo esc_url(home_url("/#jak-dzialam")); ?>" class="btn btn-secondary btn-sm">Zobacz, jak pracuję</a>
        </div>
      </div>
    </section>

    <section class="section section-border bg-soft" id="obszary-wsparcia">
      <div class="wrap">
        <div class="about-expert">
          <div class="about-expert-copy">
            <div class="eyebrow reveal">Ekspert</div>
            <h2 class="h2 reveal d1">Kim jestem?</h2>
            <p class="body reveal d2" style="margin-top:18px">Nazywam się <?php echo esc_html((string) ($founder_config["name"] ?? "Sebastian Kelm")); ?>. <?php echo esc_html((string) ($founder_config["bio"] ?? "Od ponad 10 lat pracuję w sprzedaży B2B i marketingu.")); ?></p>
            <p class="body reveal d2" style="margin-top:14px">Nie projektuję kampanii „ładnych”. Projektuję kampanie i strony, które mają prowadzić do rozmów sprzedażowych.</p>
            <?php if (!empty($client_sectors)) : ?>
              <p class="body reveal d3" style="margin-top:14px"><strong>Sektory klientów:</strong> <?php echo esc_html(implode(", ", array_map("strval", $client_sectors))); ?></p>
            <?php endif; ?>
            <div class="tool-badges reveal d3" aria-label="Narzędzia używane w pracy">
              <span>Google Ads</span>
              <span>Meta Ads</span>
              <span>GA4</span>
              <span>Search Console</span>
            </div>
          </div>
          <div class="about-expert-card reveal d2">
            <div class="about-expert-photo">
              <?php echo function_exists("upsellio_render_home_media_image") ? upsellio_render_home_media_image("about_portrait", ["class" => "about-expert-img", "size" => "large"]) : ""; ?>
            </div>
            <div class="about-expert-card-body">
              <h3 class="h3"><?php echo esc_html((string) ($founder_config["role"] ?? "Growth marketer B2B i praktyk sprzedaży")); ?></h3>
              <p><?php echo esc_html(function_exists("upsellio_home_media_slot_caption") ? upsellio_home_media_slot_caption("about_portrait") : "10+ lat praktyki w sprzedaży i marketingu B2B"); ?></p>
            </div>
            <?php if (!empty($founder_stats)) : ?>
              <div class="about-stats">
                <?php foreach ($founder_stats as $founder_stat) : ?>
                  <div class="about-stat"><span><?php echo esc_html((string) $founder_stat); ?></span></div>
                <?php endforeach; ?>
              </div>
            <?php else : ?>
              <div class="about-stats">
                <div class="about-stat"><strong>10+</strong><span>lat praktyki</span></div>
                <div class="about-stat"><strong>B2B</strong><span>sprzedaż i leady</span></div>
                <div class="about-stat"><strong>CRO</strong><span>strony pod konwersję</span></div>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <div class="section-cta-row reveal d3">
          <a href="<?php echo esc_url(home_url("/#kontakt")); ?>" class="btn btn-primary btn-sm">Porozmawiajmy o leadach</a>
        </div>
      </div>
    </section>

    <?php if (!empty($pricing_ranges)) : ?>
    <section class="section section-border" id="ceny">
      <div class="wrap">
        <div style="max-width:760px">
          <div class="eyebrow reveal">Budżet współpracy</div>
          <h2 class="h2 reveal d1">Widełki i wycena bez zgadywania</h2>
          <p class="body reveal d2" style="margin-top:18px">Zakres i cena zależą od sytuacji, ale już przed rozmową możesz sprawdzić, czego dotyczy wycena i jakie elementy mają największy wpływ na budżet.</p>
        </div>
        <div class="service-grid section-grid-gap-lg">
          <?php foreach ($pricing_ranges as $pricing_item) : ?>
            <article class="service-card reveal">
              <h3 class="h3" style="margin-bottom:10px"><?php echo esc_html((string) ($pricing_item["name"] ?? "")); ?></h3>
              <p class="body" style="font-weight:800;color:var(--teal-dark);"><?php echo esc_html((string) ($pricing_item["price"] ?? "Wycena indywidualna")); ?></p>
              <p class="body" style="margin-top:10px"><?php echo esc_html((string) ($pricing_item["description"] ?? "")); ?></p>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <section class="section-sm section-border">
      <div class="wrap">
        <div class="cta-band reveal">
          <div>
            <h3>Sprawdzę Twój marketing</h3>
            <p>Jeśli masz ruch, ale nie masz klientów - pokażę Ci, gdzie jest problem.</p>
          </div>
          <a href="<?php echo esc_url(home_url("/#hero-analiza")); ?>" class="btn btn-primary">Poproś o analizę strony</a>
        </div>
        <p class="hero-micro reveal d2" style="margin-top:12px">Odpowiadam konkretnie • bez sprzedażowego gadania</p>
      </div>
    </section>
