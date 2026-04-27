<?php

if (!defined("ABSPATH")) {
    exit;
}

$trust_seo = function_exists("upsellio_get_trust_seo_config") ? upsellio_get_trust_seo_config() : [];
$featured_case = isset($trust_seo["featured_case"]) && is_array($trust_seo["featured_case"]) ? $trust_seo["featured_case"] : [];
$case_metrics = isset($featured_case["metrics"]) && is_array($featured_case["metrics"]) ? array_slice($featured_case["metrics"], 0, 4) : [];
$testimonials = function_exists("upsellio_get_testimonials_config") ? upsellio_get_testimonials_config() : [];
$testimonial_defaults = [
    ["quote" => "Po analizie wiedzieliśmy dokładnie, gdzie ucieka budżet i które elementy strony trzeba poprawić jako pierwsze.", "name" => "Marek", "role" => "właściciel", "company" => "firma B2B"],
    ["quote" => "Największa zmiana to przejście z raportów o kliknięciach na rozmowę o jakości leadów i realnym koszcie pozyskania.", "name" => "Anna", "role" => "marketing manager", "company" => "usługi profesjonalne"],
    ["quote" => "Kampanie i landing zaczęły działać jak jeden system. Mniej chaosu, więcej konkretnych zapytań.", "name" => "Piotr", "role" => "CEO", "company" => "e-commerce"],
];
$testimonial_items = !empty($testimonials) ? array_slice($testimonials, 0, 3) : $testimonial_defaults;
$testimonial_slots = ["testimonial_1", "testimonial_2", "testimonial_3"];
$portfolio_posts = new WP_Query([
    "post_type" => "marketing_portfolio",
    "posts_per_page" => 3,
    "post_status" => "publish",
    "ignore_sticky_posts" => true,
]);
?>
<section class="section section-border" id="case-study">
      <div class="wrap">
        <div style="max-width:780px">
          <div class="eyebrow reveal">Efekty współpracy</div>
          <h2 class="h2 reveal d1">Case study z kontekstem, nie anonimowe liczby</h2>
        </div>
        <div class="case-grid section-grid-gap-lg">
          <div class="reveal">
            <div class="case-tag"><?php echo esc_html((string) ($featured_case["label"] ?? "Case study")); ?></div>
            <div class="case-title"><?php echo esc_html((string) ($featured_case["client_context"] ?? "Klient z branży usług B2B, rynek ogólnopolski")); ?></div>
            <p class="body" style="margin:12px 0 18px;"><?php echo esc_html((string) ($featured_case["period"] ?? "Wyniki po kilku miesiącach pracy nad kampanią i stroną.")); ?></p>
            <table class="results-table">
              <thead><tr><th>Metryka</th><th>Wynik</th></tr></thead>
              <tbody>
                <tr><td>Przed</td><td><?php echo esc_html((string) ($featured_case["before"] ?? "Ruch bez stabilnych zapytań")); ?></td></tr>
                <tr><td>Po</td><td><?php echo esc_html((string) ($featured_case["after"] ?? "Stały napływ kwalifikowanych leadów")); ?></td></tr>
                <?php foreach ($case_metrics as $case_metric) : ?>
                  <tr><td>Wynik</td><td><?php echo esc_html((string) $case_metric); ?></td></tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="reveal d1">
            <div class="chart-panel chart-panel-kpi-grid">
              <div class="cp-head"><span>Po wdrożeniu systemu</span><span class="live-dot"></span></div>
              <div class="mps-kpi-grid home-mps-kpi-grid">
                <div class="mps-kpi-cell"><b>128 000</b><span>wejść miesięcznie</span></div>
                <div class="mps-kpi-cell"><b>2,3%</b><span>konwersja</span></div>
                <div class="mps-kpi-cell"><b>+42%</b><span>więcej zapytań</span></div>
                <div class="mps-kpi-cell"><b>-18%</b><span>koszt pozyskania</span></div>
              </div>
            </div>
            <figure class="case-proof-card">
              <div class="case-proof-media">
                <?php echo function_exists("upsellio_render_home_media_image") ? upsellio_render_home_media_image("case_dashboard", ["class" => "case-proof-img", "size" => "large"]) : ""; ?>
              </div>
              <figcaption><?php echo esc_html(function_exists("upsellio_home_media_slot_caption") ? upsellio_home_media_slot_caption("case_dashboard") : "Anonimizowany widok danych z kampanii i lejka."); ?></figcaption>
            </figure>
          </div>
        </div>
        <div class="case-portfolio-grid section-grid-gap-lg">
          <?php if ($portfolio_posts->have_posts()) : ?>
            <?php while ($portfolio_posts->have_posts()) : $portfolio_posts->the_post(); ?>
              <article class="case-portfolio-card reveal">
                <a class="case-portfolio-cover" href="<?php the_permalink(); ?>">
                  <?php if (has_post_thumbnail()) : ?>
                    <?php the_post_thumbnail("medium_large", ["loading" => "lazy", "decoding" => "async"]); ?>
                  <?php else : ?>
                    <div class="case-portfolio-fallback">Case study</div>
                  <?php endif; ?>
                </a>
                <h3 class="h3"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                <p class="body"><?php echo esc_html(wp_trim_words((string) get_the_excerpt(), 18, "...")); ?></p>
              </article>
            <?php endwhile; ?>
          <?php endif; ?>
          <?php wp_reset_postdata(); ?>
        </div>
        <div class="section-cta-row reveal d2">
          <a href="<?php echo esc_url(home_url("/portfolio-marketingowe/")); ?>" class="btn btn-secondary btn-sm">Zobacz wszystkie case study</a>
        </div>
      </div>
    </section>

    <section class="section section-border results-dark" id="wyniki">
      <div class="wrap">
        <div style="max-width:720px">
          <div class="eyebrow reveal">Wyniki kampanii</div>
          <h2 class="h2 reveal d1">Wyniki, które rozumiesz i widzisz w liczbach</h2>
          <p class="body reveal d2" style="margin-top:18px">Na końcu liczy się to, czy Twoja reklama i strona przynoszą więcej dobrych rozmów, niższy CPL i realny wzrost sprzedaży.</p>
        </div>
        <div class="metrics-grid section-grid-gap-lg">
          <div class="metric-card reveal"><div class="mc-label">Leady / miesiąc</div><div class="mc-num teal">362</div><span class="mc-change up">+28% vs poprzedni miesiąc</span><div class="mc-sub">wartościowe kontakty sprzedażowe</div></div>
          <div class="metric-card reveal d1"><div class="mc-label">Koszt pozyskania leada (CPL)</div><div class="mc-num red">37 zł</div><span class="mc-change dn">-18% vs poprzedni miesiąc</span><div class="mc-sub">przy tym samym budżecie</div></div>
          <div class="metric-card dark reveal d2">
            <div class="mc-label">Lejek sprzedażowy</div>
            <table class="home-funnel-table">
              <thead><tr><th>Etap</th><th>Liczba</th><th>Zmiana %</th></tr></thead>
              <tbody>
                <tr><td>Ruch</td><td>23 810</td><td>-</td></tr>
                <tr><td>Leady</td><td>362</td><td>1,5%</td></tr>
                <tr><td>Kwalifikacja</td><td>148</td><td>40,9%</td></tr>
                <tr><td>Rozmowy</td><td>92</td><td>62,1%</td></tr>
                <tr><td>Klienci</td><td>64</td><td>69,6%</td></tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="section-cta-row reveal d2">
          <a href="<?php echo esc_url(home_url("/#hero-analiza")); ?>" class="btn btn-primary btn-sm">Zobacz, czy kampanie przepalają budżet →</a>
        </div>
        <?php if (!empty($testimonial_items)) : ?>
          <div class="testimonials-grid section-grid-gap-lg">
            <?php foreach ($testimonial_items as $testimonial_index => $testimonial) : ?>
              <?php $testimonial_slot = (string) ($testimonial_slots[$testimonial_index] ?? "testimonial_1"); ?>
              <blockquote class="testimonial-card reveal <?php echo $testimonial_index > 0 ? "d" . esc_attr((string) $testimonial_index) : ""; ?>">
                <div class="testimonial-head">
                  <div class="testimonial-avatar">
                    <?php echo function_exists("upsellio_render_home_media_image") ? upsellio_render_home_media_image($testimonial_slot, ["class" => "testimonial-avatar-img", "size" => "thumbnail"]) : ""; ?>
                  </div>
                  <div>
                    <strong><?php echo esc_html((string) ($testimonial["name"] ?? "")); ?></strong>
                    <span><?php echo esc_html(trim((string) ($testimonial["role"] ?? "") . " · " . (string) ($testimonial["company"] ?? ""), " · ")); ?></span>
                  </div>
                </div>
                <p>“<?php echo esc_html((string) ($testimonial["quote"] ?? "")); ?>”</p>
              </blockquote>
            <?php endforeach; ?>
          </div>
          <script type="application/ld+json"><?php echo wp_json_encode([
              "@context" => "https://schema.org",
              "@type" => "Organization",
              "name" => "Upsellio",
              "review" => array_map(static function ($testimonial) {
                  return [
                      "@type" => "Review",
                      "author" => [
                          "@type" => "Person",
                          "name" => (string) ($testimonial["name"] ?? ""),
                      ],
                      "reviewBody" => (string) ($testimonial["quote"] ?? ""),
                  ];
              }, $testimonial_items),
          ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
        <?php endif; ?>
      </div>
    </section>
