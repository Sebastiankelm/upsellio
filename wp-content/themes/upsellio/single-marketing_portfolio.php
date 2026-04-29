<?php
if (!defined("ABSPATH")) {
    exit;
}

get_header();
if (!have_posts()) {
    get_footer();
    return;
}

the_post();
$post_id = (int) get_the_ID();
$title = (string) get_the_title();
$excerpt = (string) get_the_excerpt();
$content = (string) apply_filters("the_content", get_the_content());
$type = (string) get_post_meta($post_id, "_ups_mport_type", true);
$meta = (string) get_post_meta($post_id, "_ups_mport_meta", true);
$badge = (string) get_post_meta($post_id, "_ups_mport_badge", true);
$image = function_exists("upsellio_resolve_post_image_url")
    ? upsellio_resolve_post_image_url($post_id, "_ups_mport_image", "large")
    : (string) get_post_meta($post_id, "_ups_mport_image", true);
$date = (string) get_post_meta($post_id, "_ups_mport_date", true);
$sector = (string) get_post_meta($post_id, "_ups_mport_sector", true);
$problem = (string) get_post_meta($post_id, "_ups_mport_problem", true);
$solution = (string) get_post_meta($post_id, "_ups_mport_solution", true);
$result = (string) get_post_meta($post_id, "_ups_mport_result", true);
$tags = function_exists("upsellio_parse_textarea_lines") ? upsellio_parse_textarea_lines((string) get_post_meta($post_id, "_ups_mport_tags", true), 12) : [];
$kpis = function_exists("upsellio_parse_marketing_kpi_lines") ? upsellio_parse_marketing_kpi_lines((string) get_post_meta($post_id, "_ups_mport_kpis", true)) : [];
$case_faq = get_post_meta($post_id, "_ups_mport_faq", true);
if (!is_array($case_faq)) {
    $case_faq = [];
}
$case_faq = array_values(array_filter(array_map(static function ($item) {
    if (!is_array($item)) {
        return null;
    }
    $question = trim((string) ($item["question"] ?? ""));
    $answer = trim((string) ($item["answer"] ?? ""));
    if ($question === "" || $answer === "") {
        return null;
    }
    return [
        "question" => $question,
        "answer" => $answer,
    ];
}, $case_faq)));
$custom_html = (string) get_post_meta($post_id, "_ups_mport_custom_html", true);
$custom_css = (string) get_post_meta($post_id, "_ups_mport_custom_css", true);
$custom_js = (string) get_post_meta($post_id, "_ups_mport_custom_js", true);
$custom_payload = function_exists("upsellio_prepare_custom_embed_payload")
    ? upsellio_prepare_custom_embed_payload($custom_html, $custom_css, $custom_js)
    : ["html" => $custom_html, "css" => $custom_css, "js" => $custom_js];
$custom_html = (string) ($custom_payload["html"] ?? "");
$custom_css = (string) ($custom_payload["css"] ?? "");
$custom_js = (string) ($custom_payload["js"] ?? "");
$list_url = function_exists("upsellio_get_marketing_portfolio_page_url") ? upsellio_get_marketing_portfolio_page_url() : home_url("/portfolio-marketingowe/");
$seo_payload = function_exists("upsellio_get_marketing_portfolio_seo_payload") ? upsellio_get_marketing_portfolio_seo_payload($post_id) : [];
$canonical_url = trim((string) ($seo_payload["canonical"] ?? ""));
if ($canonical_url === "") {
    $canonical_url = (string) get_permalink($post_id);
}
$schema_description = trim((string) ($seo_payload["description"] ?? ""));
if ($schema_description === "") {
    $schema_description = $excerpt !== "" ? $excerpt : wp_strip_all_tags((string) get_the_content(null, false, $post_id));
}
$schema_article = [
    "@context" => "https://schema.org",
    "@type" => "Article",
    "headline" => $title,
    "description" => wp_trim_words((string) $schema_description, 40, ""),
    "mainEntityOfPage" => $canonical_url,
    "author" => [
        "@type" => "Person",
        "name" => "Sebastian Kelm",
        "sameAs" => "https://www.linkedin.com/in/sebastiankelm/",
    ],
    "publisher" => [
        "@type" => "Organization",
        "name" => "Upsellio",
        "url" => home_url("/"),
    ],
    "datePublished" => get_the_date("c", $post_id),
    "dateModified" => get_the_modified_date("c", $post_id),
];
if ($image !== "") {
    $schema_article["image"] = [$image];
}
$schema_breadcrumbs = [
    "@context" => "https://schema.org",
    "@type" => "BreadcrumbList",
    "itemListElement" => [
        ["@type" => "ListItem", "position" => 1, "name" => "Strona glowna", "item" => home_url("/")],
        ["@type" => "ListItem", "position" => 2, "name" => "Portfolio marketingowe", "item" => $list_url],
        ["@type" => "ListItem", "position" => 3, "name" => $title, "item" => $canonical_url],
    ],
];
$schema_faq_entities = [];
foreach ($case_faq as $faq_item) {
    $schema_faq_entities[] = [
        "@type" => "Question",
        "name" => (string) $faq_item["question"],
        "acceptedAnswer" => [
            "@type" => "Answer",
            "text" => (string) $faq_item["answer"],
        ],
    ];
}
?>
<style>
  .mc-art{font-family:"DM Sans",system-ui,sans-serif;color:#0a1410;background:#fafaf7;line-height:1.7}
  .mc-art *,.mc-art *::before,.mc-art *::after{box-sizing:border-box}
  .mc-wrap{width:min(1180px,100% - 64px);margin-inline:auto}
  .mc-eyebrow{display:inline-flex;align-items:center;gap:10px;font-size:11px;font-weight:700;letter-spacing:1.6px;text-transform:uppercase;color:#0d9488;margin-bottom:14px}
  .mc-eyebrow::before{content:"";width:26px;height:2px;background:#0d9488;border-radius:99px}
  .mc-eyebrow-light{color:#5eead4}
  .mc-eyebrow-light::before{background:#5eead4}
  .mc-h1{font-family:"Syne",sans-serif;font-weight:700;font-size:clamp(36px,4.2vw,56px);line-height:1.04;letter-spacing:-1.7px;margin:0 0 20px;max-width:20ch}
  .mc-h2{font-family:"Syne",sans-serif;font-weight:700;font-size:clamp(24px,2.8vw,34px);line-height:1.1;letter-spacing:-1.2px;margin:0 0 16px;max-width:22ch}
  .mc-h2-light{color:#fff}
  .mc-lead{font-size:18px;line-height:1.6;color:#3d3d38;max-width:60ch;margin:0 0 28px}
  .mc-crumbs{padding:32px 0 0;font-size:13px;color:#7c7c74}
  .mc-crumbs a{color:#7c7c74;text-decoration:none;margin-right:8px}
  .mc-crumbs span{margin-right:8px;color:#c4c4bd}
  .mc-head{padding:48px 0 64px}
  .mc-head-grid{display:grid;grid-template-columns:1fr;gap:32px;align-items:start}
  .mc-meta-row{display:grid;grid-template-columns:repeat(2,1fr);gap:18px;margin-top:32px;padding-top:24px;border-top:1px solid #e7e7e1}
  .mc-meta-row > div{display:flex;flex-direction:column;gap:2px}
  .mc-meta-row span{font-size:11px;letter-spacing:1.2px;text-transform:uppercase;color:#7c7c74;font-weight:700}
  .mc-meta-row strong{font-size:14.5px;font-weight:600;color:#0a1410}
  .mc-thumb-stripes{position:absolute;inset:0;background-image:repeating-linear-gradient(135deg,rgba(13,148,136,.12) 0 14px,transparent 14px 28px)}
  .mc-thumb-label{position:absolute;inset:0;display:grid;place-items:center;font-family:ui-monospace,monospace;color:#0f766e;font-size:13px;letter-spacing:1px;text-align:center;padding:0 24px}
  .mc-side{background:#0a1410;color:#fff;border-radius:20px;padding:28px;position:relative;overflow:hidden}
  .mc-side::before{content:"";position:absolute;width:240px;height:240px;border-radius:50%;background:radial-gradient(circle,rgba(20,184,166,.2),transparent 65%);right:-80px;top:-80px;pointer-events:none}
  .mc-side .mc-eyebrow{color:#5eead4;position:relative}
  .mc-side .mc-eyebrow::before{background:#5eead4}
  .mc-results{position:relative;display:grid;grid-template-columns:1fr 1fr;gap:18px}
  .mc-results > div{display:flex;flex-direction:column;gap:2px}
  .mc-results strong{font-family:"Syne",sans-serif;font-size:30px;color:#5eead4;letter-spacing:-1px;font-weight:700}
  .mc-results span{font-size:12.5px;color:rgba(255,255,255,.7)}
  .mc-cover{padding:0 0 96px}
  .mc-cover-img{position:relative;aspect-ratio:2.2;background:#dff8f4;border-radius:24px;overflow:hidden;border:1px solid #99f6e4}
  .mc-cover-img img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
  .mc-section{padding:32px 0 96px}
  .mc-block{display:grid;grid-template-columns:80px 1fr;gap:32px;padding:32px 0;border-top:1px solid #e7e7e1}
  .mc-block:first-child{border-top:0;padding-top:0}
  .mc-block-num{font-family:"Syne",sans-serif;font-size:54px;font-weight:700;color:#dff8f4;letter-spacing:-2px;line-height:1}
  .mc-block-body p{margin:0 0 16px;font-size:16px;line-height:1.75;color:#262625}
  .mc-bullets{list-style:none;padding:0;margin:0 0 24px;display:grid;gap:10px}
  .mc-bullets li{padding-left:24px;position:relative;font-size:15px;line-height:1.7;color:#262625}
  .mc-bullets li::before{content:"";position:absolute;left:2px;top:11px;width:8px;height:8px;background:#0d9488;border-radius:50%}
  .mc-phases{display:grid;gap:14px;margin-top:18px}
  .mc-phase{background:#fff;border:1px solid #e7e7e1;border-left:3px solid #0d9488;border-radius:0 14px 14px 0;padding:22px 26px}
  .mc-phase strong{display:block;font-family:"Syne",sans-serif;font-size:15px;font-weight:700;margin-bottom:8px;color:#0d9488;letter-spacing:-0.2px}
  .mc-phase p{margin:0 !important;font-size:14.5px;color:#3d3d38;line-height:1.65}
  .mc-chart{background:#fff;border:1px solid #e7e7e1;border-radius:18px;padding:24px;display:grid;grid-template-columns:60px 1fr;column-gap:14px;row-gap:8px;align-items:end}
  .mc-chart-y{display:grid;grid-template-rows:repeat(5,1fr);font-size:10.5px;color:#7c7c74;letter-spacing:.4px;height:200px}
  .mc-chart-y span:first-child{font-weight:700;letter-spacing:1.4px;text-transform:uppercase;color:#0a1410}
  .mc-chart svg{height:200px;width:100%;display:block}
  .mc-chart-x{grid-column:2;display:flex;justify-content:space-between;font-size:11px;color:#7c7c74;padding:0 4px}
  .mc-results-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-top:16px}
  .mc-r-card{background:#fff;border:1px solid #e7e7e1;border-radius:14px;padding:22px}
  .mc-r-card strong{display:block;font-family:"Syne",sans-serif;font-weight:700;font-size:36px;color:#0d9488;letter-spacing:-1.4px;line-height:1}
  .mc-r-card span{display:block;font-size:11px;letter-spacing:1.2px;text-transform:uppercase;color:#7c7c74;font-weight:700;margin:8px 0 4px}
  .mc-r-card p{margin:0 !important;font-size:13px;color:#3d3d38}
  .mc-quote{background:#0a1410;color:#fff;border-radius:24px;padding:40px;margin-top:32px;position:relative;overflow:hidden}
  .mc-quote::before{content:"\201C";position:absolute;font-family:"Syne",sans-serif;font-size:240px;line-height:1;color:rgba(94,234,212,.12);top:0;left:24px;pointer-events:none}
  .mc-quote p{position:relative;margin:0 0 22px;font-family:"Syne",sans-serif;font-size:24px;line-height:1.4;letter-spacing:-0.5px;font-weight:600}
  .mc-quote-author{position:relative;display:flex;align-items:center;gap:12px;padding-top:18px;border-top:1px solid rgba(255,255,255,.12)}
  .mc-avatar{width:42px;height:42px;border-radius:50%;background:#0f766e;display:grid;place-items:center;font-family:"Syne",sans-serif;color:#fff;font-weight:800;font-size:14px}
  .mc-quote-author strong{display:block;font-family:"Syne",sans-serif;font-size:15px;font-weight:700}
  .mc-quote-author span{display:block;font-size:12.5px;color:rgba(255,255,255,.7)}
  .mc-cta{background:#0d9488;color:#fff;padding:80px 0;position:relative;overflow:hidden}
  .mc-cta::before{content:"";position:absolute;width:600px;height:600px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,.18),transparent 65%);right:-200px;top:-300px;pointer-events:none}
  .mc-cta-inner{position:relative;display:flex;justify-content:space-between;align-items:center;gap:32px;flex-wrap:wrap}
  .mc-btn-primary{display:inline-flex;align-items:center;gap:8px;background:#0a1410;color:#fff;padding:15px 24px;border-radius:999px;font-weight:700;font-size:15px;text-decoration:none}
  .mc-custom{margin-top:24px;padding:16px;border:1px solid #dce7e1;border-radius:14px;background:#f8fcfa}
  .mc-contact{padding:0 0 128px}
  .mc-contact-shell{border:1px solid #e7e7e1;border-radius:22px;background:#fff;padding:28px}
  .mc-contact-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
  .mc-contact-shell label{display:grid;gap:6px;font-size:13px;color:#3d3d38}
  .mc-contact-shell input,.mc-contact-shell textarea{width:100%;border:1px solid #d6d6d0;border-radius:10px;padding:10px 12px;font:inherit}
  .mc-contact-shell textarea{min-height:120px;resize:vertical}
  .mc-contact-consent{display:flex !important;align-items:flex-start;gap:8px;font-size:12px !important;color:#6b6b64 !important}
  .mc-contact-consent input{margin-top:3px}
  .mc-contact-submit{display:inline-flex;align-items:center;justify-content:center;border:0;background:#0d9488;color:#fff;padding:12px 18px;border-radius:10px;font-weight:700;cursor:pointer}
  @media(max-width:980px){
    .mc-wrap{width:min(1180px,100% - 40px)}
    .mc-head-grid{grid-template-columns:1fr}
    .mc-block{grid-template-columns:1fr;gap:14px}
    .mc-results-grid{grid-template-columns:1fr 1fr}
  }
  @media(max-width:700px){
    .mc-h1{max-width:none}
    .mc-meta-row{grid-template-columns:1fr}
    .mc-results{grid-template-columns:1fr}
    .mc-results-grid{grid-template-columns:1fr}
    .mc-contact-grid{grid-template-columns:1fr}
  }
</style>

<?php
$kpi_items = array_slice($kpis, 0, 4);
$phase_items = array_values(array_filter(array_map("trim", preg_split("/\r\n|\r|\n/", wp_strip_all_tags($solution)))));
if (empty($phase_items)) {
    $phase_items = [
        "Audyt i fundamenty techniczne kampanii.",
        "Restrukturyzacja kampanii i dopasowanie komunikatów.",
        "Optymalizacja i skalowanie działań.",
    ];
}
$bullet_items = !empty($tags) ? array_slice($tags, 0, 5) : [
    "Precyzyjne targetowanie pod realną intencję zakupową.",
    "Nowa struktura kampanii i grup reklam.",
    "Lepsze dopasowanie kreacji do etapu lejka.",
    "Weryfikacja jakości leadów i ich wartości.",
    "Stałe testy i iteracje co 2 tygodnie.",
];
?>

<main class="mc-art">
  <?php
  $hero_cover_url = $image !== "" ? $image : (has_post_thumbnail($post_id) ? (string) get_the_post_thumbnail_url($post_id, "large") : "");
  ?>
  <nav class="mc-crumbs">
    <div class="mc-wrap">
      <a href="<?php echo esc_url(home_url("/")); ?>">Strona główna</a>
      <span>›</span>
      <a href="<?php echo esc_url($list_url); ?>">Portfolio marketingowe</a>
      <span>›</span>
      <span><?php echo esc_html($title); ?></span>
    </div>
  </nav>

  <header class="mc-head">
    <div class="mc-wrap mc-head-grid">
      <div>
        <div class="mc-eyebrow"><?php echo esc_html($badge !== "" ? $badge : "Case study · Marketing"); ?></div>
        <h1 class="mc-h1"><?php echo esc_html($title); ?></h1>
        <p class="mc-lead">
          <?php
          $lead_text = $excerpt !== "" ? $excerpt : "Case study wdrożenia działań marketingowych i optymalizacji lejka sprzedażowego.";
          echo esc_html($lead_text);
          ?>
        </p>
        <div class="mc-meta-row">
          <div><span>Klient</span><strong><?php echo esc_html($type !== "" ? $type : "Firma B2B/B2C"); ?></strong></div>
          <div><span>Rynek</span><strong><?php echo esc_html($sector !== "" ? $sector : "Polska + UE"); ?></strong></div>
          <div><span>Zakres</span><strong><?php echo esc_html($meta !== "" ? $meta : "Strategia, kampanie, analytics"); ?></strong></div>
          <div><span>Czas</span><strong><?php echo esc_html($date !== "" ? $date : "4 miesiące"); ?></strong></div>
        </div>
      </div>

      <aside class="mc-side">
        <div class="mc-eyebrow">Wyniki</div>
        <div class="mc-results">
          <?php if (!empty($kpi_items)) : ?>
            <?php foreach ($kpi_items as $kpi) : ?>
              <div>
                <strong><?php echo esc_html((string) (($kpi["after"] ?? "") !== "" ? $kpi["after"] : ($kpi["change"] ?? "-"))); ?></strong>
                <span><?php echo esc_html((string) ($kpi["label"] ?? "KPI")); ?></span>
              </div>
            <?php endforeach; ?>
          <?php else : ?>
            <div><strong>−42%</strong><span>CPL z kampanii</span></div>
            <div><strong>+180%</strong><span>Liczba zapytań / mies.</span></div>
            <div><strong>6,2×</strong><span>ROAS po optymalizacji</span></div>
            <div><strong>3,2 zł</strong><span>Średni CPC</span></div>
          <?php endif; ?>
        </div>
      </aside>
    </div>
  </header>

  <section class="mc-cover">
    <div class="mc-wrap">
      <div class="mc-cover-img">
        <?php if ($hero_cover_url !== "") : ?>
          <img src="<?php echo esc_url($hero_cover_url); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy" decoding="async" width="1400" height="900" />
        <?php else : ?>
          <div class="mc-thumb-stripes"></div>
          <div class="mc-thumb-label">[ desktop mockup — case marketingowy ]</div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="mc-section">
    <div class="mc-wrap">
      <div class="mc-block">
        <div class="mc-block-num">01</div>
        <div class="mc-block-body">
          <div class="mc-eyebrow">Punkt wyjścia</div>
          <h2 class="mc-h2">Konto działało, ale na dużo niższym potencjale.</h2>
          <p><?php echo esc_html($problem !== "" ? $problem : "Kampanie były aktywne, ale wyniki i jakość leadów pogarszały się z miesiąca na miesiąc."); ?></p>
          <ul class="mc-bullets">
            <?php foreach ($bullet_items as $bullet_item) : ?>
              <li><?php echo esc_html((string) $bullet_item); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>

      <div class="mc-block">
        <div class="mc-block-num">02</div>
        <div class="mc-block-body">
          <div class="mc-eyebrow">Co zrobiłem</div>
          <h2 class="mc-h2">Restrukturyzacja w 3 fazach.</h2>
          <div class="mc-phases">
            <?php foreach (array_slice($phase_items, 0, 3) as $index => $phase_item) : ?>
              <div class="mc-phase">
                <strong>Faza <?php echo esc_html((string) ($index + 1)); ?></strong>
                <p><?php echo esc_html((string) $phase_item); ?></p>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="mc-block">
        <div class="mc-block-num">03</div>
        <div class="mc-block-body">
          <div class="mc-eyebrow">Wykres wyników</div>
          <h2 class="mc-h2">Co się działo w danych.</h2>
          <div class="mc-chart" aria-hidden="true">
            <div class="mc-chart-y">
              <span>CPL</span>
              <span>320 zł</span>
              <span>240 zł</span>
              <span>160 zł</span>
              <span>80 zł</span>
            </div>
            <svg viewBox="0 0 600 220" preserveAspectRatio="none">
              <defs>
                <linearGradient id="mcg" x1="0" x2="0" y1="0" y2="1">
                  <stop offset="0%" stop-color="#0d9488" stop-opacity=".22"></stop>
                  <stop offset="100%" stop-color="#0d9488" stop-opacity="0"></stop>
                </linearGradient>
              </defs>
              <path d="M0,30 L80,40 L160,55 L240,80 L320,120 L400,150 L480,170 L560,180 L600,180 L600,220 L0,220 Z" fill="url(#mcg)"></path>
              <path d="M0,30 L80,40 L160,55 L240,80 L320,120 L400,150 L480,170 L560,180" fill="none" stroke="#0d9488" stroke-width="2.5"></path>
              <circle cx="0" cy="30" r="3.5" fill="#0d9488"></circle>
              <circle cx="80" cy="40" r="3.5" fill="#0d9488"></circle>
              <circle cx="160" cy="55" r="3.5" fill="#0d9488"></circle>
              <circle cx="240" cy="80" r="3.5" fill="#0d9488"></circle>
              <circle cx="320" cy="120" r="3.5" fill="#0d9488"></circle>
              <circle cx="400" cy="150" r="3.5" fill="#0d9488"></circle>
              <circle cx="480" cy="170" r="3.5" fill="#0d9488"></circle>
              <circle cx="560" cy="180" r="3.5" fill="#0d9488"></circle>
            </svg>
            <div class="mc-chart-x">
              <span>Mies. 0</span><span>1</span><span>2</span><span>3</span><span>4</span>
            </div>
          </div>
        </div>
      </div>

      <div class="mc-block">
        <div class="mc-block-num">04</div>
        <div class="mc-block-body">
          <div class="mc-eyebrow">Wynik</div>
          <h2 class="mc-h2">Liczby po wdrożeniu.</h2>
          <div class="mc-results-grid">
            <?php if (!empty($kpi_items)) : ?>
              <?php foreach ($kpi_items as $kpi) : ?>
                <div class="mc-r-card">
                  <strong><?php echo esc_html((string) (($kpi["after"] ?? "") !== "" ? $kpi["after"] : ($kpi["change"] ?? "-"))); ?></strong>
                  <span><?php echo esc_html((string) ($kpi["label"] ?? "KPI")); ?></span>
                  <p><?php echo esc_html((string) ($kpi["desc"] ?? ($kpi["before"] ?? ""))); ?></p>
                </div>
              <?php endforeach; ?>
            <?php else : ?>
              <div class="mc-r-card"><strong>−42%</strong><span>CPL</span><p>320 zł → 184 zł</p></div>
              <div class="mc-r-card"><strong>+180%</strong><span>Zapytania / mies.</span><p>22 → 62</p></div>
              <div class="mc-r-card"><strong>6,2×</strong><span>ROAS</span><p>Z 2,1× do 6,2×</p></div>
              <div class="mc-r-card"><strong>+340%</strong><span>Wartość zamówień</span><p>Wzrost łącznej sprzedaży</p></div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="mc-quote">
        <p><?php echo esc_html($result !== "" ? $result : "Po wdrożeniu kampanie zaczęły regularnie dostarczać jakościowe zapytania i realny wzrost sprzedaży."); ?></p>
        <div class="mc-quote-author">
          <div class="mc-avatar">UK</div>
          <div>
            <strong>Opinia klienta</strong>
            <span><?php echo esc_html($title); ?></span>
          </div>
        </div>
      </div>

      <div class="mc-block">
        <div class="mc-block-num">05</div>
        <div class="mc-block-body">
          <div class="mc-eyebrow">Szczegóły</div>
          <h2 class="mc-h2">Pełny opis wdrożenia.</h2>
          <?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
          <?php if (!empty($case_faq)) : ?>
            <?php foreach ($case_faq as $faq_item) : ?>
              <details style="border:1px solid #e2e8f0;border-radius:12px;padding:12px 14px;margin-bottom:8px;background:#fff;">
                <summary style="cursor:pointer;font-weight:700;"><?php echo esc_html((string) $faq_item["question"]); ?></summary>
                <p style="margin-top:8px;"><?php echo esc_html((string) $faq_item["answer"]); ?></p>
              </details>
            <?php endforeach; ?>
          <?php endif; ?>
          <?php if ($custom_html !== "" || $custom_css !== "" || $custom_js !== "") : ?>
            <div class="mc-custom">
              <?php if ($custom_html !== "") : ?><div><?php echo $custom_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?>
              <?php if ($custom_css !== "") : ?><style><?php echo $custom_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></style><?php endif; ?>
              <?php if ($custom_js !== "") : ?><script><?php echo $custom_js; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></script><?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <section class="mc-cta">
    <div class="mc-wrap mc-cta-inner">
      <div>
        <div class="mc-eyebrow mc-eyebrow-light">Twoje konto</div>
        <h2 class="mc-h2 mc-h2-light">Zobaczmy, co da się poprawić u Ciebie.</h2>
      </div>
      <a class="mc-btn-primary" href="<?php echo esc_url(home_url("/#kontakt")); ?>">Bezpłatny audyt konta →</a>
    </div>
  </section>

  <section class="mc-contact">
    <div class="mc-wrap">
      <form class="mc-contact-shell" method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" data-upsellio-lead-form="1" data-upsellio-server-form="1">
        <input type="hidden" name="action" value="upsellio_submit_lead" />
        <input type="hidden" name="redirect_url" value="<?php echo esc_url(get_permalink($post_id)); ?>" />
        <input type="hidden" name="lead_form_origin" value="single-marketing-portfolio-form" />
        <input type="hidden" name="lead_source" value="single-marketing-portfolio-form" />
        <input type="hidden" name="utm_source" data-ups-utm="source" value="" />
        <input type="hidden" name="utm_medium" data-ups-utm="medium" value="" />
        <input type="hidden" name="utm_campaign" data-ups-utm="campaign" value="" />
        <input type="hidden" name="landing_url" data-ups-context="landing" value="" />
        <input type="hidden" name="referrer" data-ups-context="referrer" value="" />
        <input type="text" name="lead_website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;" />
        <?php wp_nonce_field("upsellio_unified_lead_form", "upsellio_lead_form_nonce"); ?>
        <div class="mc-eyebrow">Kontakt</div>
        <h2 class="mc-h2" style="max-width:none;">Chcesz podobny wynik? Napisz.</h2>
        <div class="mc-contact-grid">
          <label>Imię
            <input type="text" name="lead_name" required />
          </label>
          <label>E-mail
            <input type="email" name="lead_email" required />
          </label>
        </div>
        <label>Wiadomość
          <textarea name="lead_message" placeholder="Napisz, jaki wynik chcesz osiągnąć." required></textarea>
        </label>
        <label class="mc-contact-consent">
          <input type="checkbox" name="lead_consent" value="1" required />
          <span>Wyrażam zgodę na kontakt w sprawie przesłanego zapytania.</span>
        </label>
        <button type="submit" class="mc-contact-submit">Wyślij i umów rozmowę →</button>
      </form>
    </div>
  </section>
</main>
<script type="application/ld+json">
<?php echo wp_json_encode($schema_article, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
</script>
<script type="application/ld+json">
<?php echo wp_json_encode($schema_breadcrumbs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
</script>
<?php if (!empty($schema_faq_entities)) : ?>
<script type="application/ld+json">
<?php echo wp_json_encode(["@context" => "https://schema.org", "@type" => "FAQPage", "mainEntity" => $schema_faq_entities], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
</script>
<?php endif; ?>
<?php
get_footer();
