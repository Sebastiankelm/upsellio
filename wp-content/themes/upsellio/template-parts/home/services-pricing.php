<?php

if (!defined("ABSPATH")) {
    exit;
}

$pricing_ranges = function_exists("upsellio_get_pricing_ranges_config") ? upsellio_get_pricing_ranges_config() : [];
if (empty($pricing_ranges)) {
    return;
}

$pricing_links = [
    "google" => home_url("/marketing-google-ads/"),
    "meta" => home_url("/marketing-meta-ads/"),
    "strony" => home_url("/tworzenie-stron-internetowych/"),
];
?>
<section class="section section-border" id="ceny">
  <div class="wrap">
    <div style="max-width:760px">
      <div class="eyebrow reveal">Budżet współpracy</div>
      <h2 class="h2 reveal d1">Widełki i wycena bez zgadywania</h2>
      <p class="body reveal d2" style="margin-top:18px">Zakres i cena zależą od sytuacji, ale już przed rozmową możesz sprawdzić, czego dotyczy wycena i jakie elementy mają największy wpływ na budżet.</p>
    </div>
    <div class="service-grid section-grid-gap-lg">
      <?php foreach ($pricing_ranges as $pricing_item) : ?>
        <?php
        $pricing_name = (string) ($pricing_item["name"] ?? "");
        $pricing_name_key = function_exists("remove_accents") ? remove_accents($pricing_name) : $pricing_name;
        $pricing_name_key = strtolower($pricing_name_key);
        $pricing_url = "";
        foreach ($pricing_links as $needle => $url) {
            if (strpos($pricing_name_key, $needle) !== false) {
                $pricing_url = (string) $url;
                break;
            }
        }
        ?>
        <article class="service-card reveal">
          <h3 class="h3" style="margin-bottom:10px"><?php echo esc_html((string) ($pricing_item["name"] ?? "")); ?></h3>
          <p class="body" style="font-weight:800;color:var(--teal-dark);"><?php echo esc_html((string) ($pricing_item["price"] ?? "Wycena indywidualna")); ?></p>
          <p class="body" style="margin-top:10px"><?php echo esc_html((string) ($pricing_item["description"] ?? "")); ?></p>
          <?php if ($pricing_url !== "") : ?>
            <p style="margin-top:12px;"><a class="service-card-cta-link" href="<?php echo esc_url($pricing_url); ?>">Zobacz szczegóły usługi →</a></p>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
