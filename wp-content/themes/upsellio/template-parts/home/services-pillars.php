<?php

if (!defined("ABSPATH")) {
    exit;
}

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
