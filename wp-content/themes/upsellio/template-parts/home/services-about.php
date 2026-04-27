<?php

if (!defined("ABSPATH")) {
    exit;
}

$founder_config = function_exists("upsellio_get_trust_seo_section") ? upsellio_get_trust_seo_section("founder") : [];
$founder_stats = isset($founder_config["stats"]) && is_array($founder_config["stats"]) ? array_slice($founder_config["stats"], 0, 4) : [];
$client_sectors = isset($founder_config["client_sectors"]) && is_array($founder_config["client_sectors"]) ? array_slice($founder_config["client_sectors"], 0, 4) : [];
?>
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
