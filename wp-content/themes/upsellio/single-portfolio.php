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
$type = (string) get_post_meta($post_id, "_ups_port_type", true);
$meta = (string) get_post_meta($post_id, "_ups_port_meta", true);
$badge = (string) get_post_meta($post_id, "_ups_port_badge", true);
$cta = (string) get_post_meta($post_id, "_ups_port_cta", true);
$image = function_exists("upsellio_resolve_post_image_url")
    ? upsellio_resolve_post_image_url($post_id, "_ups_port_image", "large")
    : (string) get_post_meta($post_id, "_ups_port_image", true);
$problem = (string) get_post_meta($post_id, "_ups_port_problem", true);
$scope = (string) get_post_meta($post_id, "_ups_port_scope", true);
$result = (string) get_post_meta($post_id, "_ups_port_result", true);
$external_url = (string) get_post_meta($post_id, "_ups_port_external_url", true);
$metrics = function_exists("upsellio_parse_metrics_lines") ? upsellio_parse_metrics_lines((string) get_post_meta($post_id, "_ups_port_metrics", true)) : [];
$technologies = function_exists("upsellio_parse_metrics_lines") ? upsellio_parse_metrics_lines((string) get_post_meta($post_id, "_ups_port_technologies", true)) : [];
$client_quote = (string) get_post_meta($post_id, "_ups_port_client_quote", true);
$has_publish_consent = (string) get_post_meta($post_id, "_ups_port_publish_consent", true) === "1";
$project_faq = get_post_meta($post_id, "_ups_port_faq", true);
if (!is_array($project_faq)) {
    $project_faq = [];
}
$project_faq = array_values(array_filter(array_map(static function ($item) {
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
}, $project_faq)));
$custom_html = (string) get_post_meta($post_id, "_ups_port_custom_html", true);
$custom_css = (string) get_post_meta($post_id, "_ups_port_custom_css", true);
$custom_js = (string) get_post_meta($post_id, "_ups_port_custom_js", true);
$custom_payload = function_exists("upsellio_prepare_custom_embed_payload")
    ? upsellio_prepare_custom_embed_payload($custom_html, $custom_css, $custom_js)
    : ["html" => $custom_html, "css" => $custom_css, "js" => $custom_js];
$custom_html = (string) ($custom_payload["html"] ?? "");
$custom_css = (string) ($custom_payload["css"] ?? "");
$custom_js = (string) ($custom_payload["js"] ?? "");
$portfolio_url = function_exists("upsellio_get_portfolio_page_url") ? upsellio_get_portfolio_page_url() : home_url("/portfolio/");
$schema_description = $excerpt !== "" ? $excerpt : wp_trim_words(wp_strip_all_tags((string) get_the_content(null, false, $post_id)), 35, "");
add_action("wp_head", static function () use ($post_id, $title, $schema_description, $external_url, $project_faq, $portfolio_url) {
    $schema_payloads = [];
    $creative_work = [
        "@context" => "https://schema.org",
        "@type" => "CreativeWork",
        "name" => $title,
        "description" => $schema_description,
        "url" => get_permalink($post_id),
        "author" => [
            "@type" => "Organization",
            "name" => "Upsellio",
        ],
        "datePublished" => get_the_date("c", $post_id),
    ];
    if ($external_url !== "") {
        $creative_work["sameAs"] = [esc_url_raw($external_url)];
    }
    $schema_payloads[] = $creative_work;
    $schema_payloads[] = [
        "@context" => "https://schema.org",
        "@type" => "BreadcrumbList",
        "itemListElement" => [
            ["@type" => "ListItem", "position" => 1, "name" => "Strona glowna", "item" => home_url("/")],
            ["@type" => "ListItem", "position" => 2, "name" => "Portfolio", "item" => $portfolio_url],
            ["@type" => "ListItem", "position" => 3, "name" => $title, "item" => get_permalink($post_id)],
        ],
    ];
    $faq_entities = [];
    foreach ($project_faq as $faq_item) {
        $faq_entities[] = [
            "@type" => "Question",
            "name" => (string) $faq_item["question"],
            "acceptedAnswer" => ["@type" => "Answer", "text" => (string) $faq_item["answer"]],
        ];
    }
    if (!empty($faq_entities)) {
        $schema_payloads[] = [
            "@context" => "https://schema.org",
            "@type" => "FAQPage",
            "mainEntity" => $faq_entities,
        ];
    }
    foreach ($schema_payloads as $schema_payload) {
        echo '<script type="application/ld+json">' . wp_json_encode($schema_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
    }
}, 2);
?>
<style>
  .pf-art{font-family:"DM Sans",system-ui,sans-serif;color:#0a1410;background:#fafaf7;line-height:1.7}
  .pf-art *,.pf-art *::before,.pf-art *::after{box-sizing:border-box}
  .pf-wrap{width:min(1180px,100% - 64px);margin-inline:auto}
  .pf-eyebrow{display:inline-flex;align-items:center;gap:10px;font-size:11px;font-weight:700;letter-spacing:1.6px;text-transform:uppercase;color:#0d9488;margin-bottom:14px}
  .pf-eyebrow::before{content:"";width:26px;height:2px;background:#0d9488;border-radius:99px}
  .pf-eyebrow-light{color:#5eead4}
  .pf-eyebrow-light::before{background:#5eead4}
  .pf-h1{font-family:"Syne",sans-serif;font-weight:700;font-size:clamp(38px,4.4vw,58px);line-height:1.02;letter-spacing:-1.8px;margin:0 0 20px;max-width:18ch}
  .pf-h2{font-family:"Syne",sans-serif;font-weight:700;font-size:clamp(24px,2.8vw,34px);line-height:1.1;letter-spacing:-1.2px;margin:48px 0 16px}
  .pf-h2:first-child{margin-top:0}
  .pf-h2-light{color:#fff}
  .pf-lead{font-size:18px;line-height:1.6;color:#3d3d38;max-width:60ch;margin:0 0 28px}
  .pf-divider{height:1px;background:#e7e7e1;margin:32px 0 48px}
  .pf-thumb-stripes{position:absolute;inset:0;background-image:repeating-linear-gradient(135deg,rgba(13,148,136,.12) 0 14px,transparent 14px 28px)}
  .pf-thumb-label{position:absolute;inset:0;display:grid;place-items:center;font-family:ui-monospace,monospace;color:#0f766e;font-size:13px;letter-spacing:1px;text-align:center;padding:0 24px}
  .pf-crumbs{padding:32px 0 0;font-size:13px;color:#7c7c74}
  .pf-crumbs a{color:#7c7c74;text-decoration:none;margin-right:8px}
  .pf-crumbs span{margin-right:8px;color:#c4c4bd}
  .pf-head{padding:48px 0 64px}
  .pf-head-grid{display:grid;grid-template-columns:1.4fr .8fr;gap:48px;align-items:start}
  .pf-meta-row{display:grid;grid-template-columns:repeat(2,1fr);gap:18px;margin-top:32px;padding-top:24px;border-top:1px solid #e7e7e1}
  .pf-meta-row>div{display:flex;flex-direction:column;gap:2px}
  .pf-meta-row span{font-size:11px;letter-spacing:1.2px;text-transform:uppercase;color:#7c7c74;font-weight:700}
  .pf-meta-row strong{font-size:14.5px;font-weight:600;color:#0a1410}
  .pf-side{background:#0a1410;color:#fff;border-radius:20px;padding:28px;position:relative;overflow:hidden}
  .pf-side::before{content:"";position:absolute;width:240px;height:240px;border-radius:50%;background:radial-gradient(circle,rgba(20,184,166,.2),transparent 65%);right:-80px;top:-80px;pointer-events:none}
  .pf-side .pf-eyebrow{color:#5eead4;position:relative}
  .pf-side .pf-eyebrow::before{background:#5eead4}
  .pf-stat-big{position:relative;font-family:"Syne",sans-serif;font-weight:700;font-size:64px;line-height:1;letter-spacing:-3px;color:#5eead4;margin:6px 0 4px}
  .pf-stat-label{position:relative;font-size:13px;color:rgba(255,255,255,.7);margin-bottom:18px}
  .pf-side ul{position:relative;list-style:none;padding:0;margin:0;display:grid;gap:10px;border-top:1px solid rgba(255,255,255,.12);padding-top:16px}
  .pf-side ul li{display:flex;justify-content:space-between;align-items:baseline;font-size:13.5px;gap:10px}
  .pf-side ul strong{font-family:"Syne",sans-serif;font-weight:700;color:#5eead4;font-size:18px;letter-spacing:-.3px;white-space:nowrap}
  .pf-side ul span{color:rgba(255,255,255,.7);font-size:13px;text-align:right}
  .pf-cover{padding:0 0 96px}
  .pf-cover-img{position:relative;aspect-ratio:2.2;background:#dff8f4;border-radius:24px;overflow:hidden;border:1px solid #99f6e4}
  .pf-cover-img img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
  .pf-section{padding:0 0 96px}
  .pf-content-grid{display:grid;grid-template-columns:1fr 280px;gap:64px;align-items:start}
  .pf-content p{margin:0 0 18px;font-size:16px;color:#262625;line-height:1.75}
  .pf-content h2,.pf-content h3{font-family:"Syne",sans-serif;font-size:clamp(24px,2.8vw,34px);line-height:1.1;letter-spacing:-1.2px;margin:48px 0 16px}
  .pf-bullets{list-style:none;padding:0;margin:0 0 32px;display:grid;gap:12px}
  .pf-bullets li{padding-left:24px;position:relative;font-size:15.5px;line-height:1.7;color:#262625}
  .pf-bullets li::before{content:"";position:absolute;left:2px;top:11px;width:8px;height:8px;background:#0d9488;border-radius:50%}
  .pf-bullets strong{color:#0a1410;font-weight:700}
  .pf-quote{background:#fff;border:1px solid #e7e7e1;border-left:3px solid #0d9488;border-radius:0 18px 18px 0;padding:28px 32px;margin:32px 0}
  .pf-quote p{margin:0 0 18px !important;font-family:"Syne",sans-serif;font-size:20px;line-height:1.4;letter-spacing:-.4px;color:#0a1410}
  .pf-quote-author{display:flex;align-items:center;gap:12px;padding-top:16px;border-top:1px solid #e7e7e1}
  .pf-avatar{width:40px;height:40px;border-radius:50%;background:#dff8f4;border:1px solid #99f6e4;display:grid;place-items:center;font-family:"Syne",sans-serif;color:#0f766e;font-weight:800;font-size:13px}
  .pf-quote-author strong{display:block;font-family:"Syne",sans-serif;font-size:14.5px;font-weight:700}
  .pf-quote-author span{display:block;font-size:12.5px;color:#7c7c74}
  .pf-shots{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin:24px 0 32px}
  .pf-shot{margin:0;background:#fff;border:1px solid #e7e7e1;border-radius:14px;overflow:hidden}
  .pf-shot-img{position:relative;aspect-ratio:1.55;background:#dff8f4}
  .pf-shot-img img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
  .pf-shot figcaption{padding:12px 16px;font-size:12.5px;color:#7c7c74;border-top:1px solid #e7e7e1}
  .pf-tech{position:sticky;top:32px;background:#fff;border:1px solid #e7e7e1;border-radius:18px;padding:24px}
  .pf-tech ul{list-style:none;padding:0;margin:0 0 22px;display:grid;gap:8px}
  .pf-tech ul li{font-size:13.5px;color:#3d3d38;padding-left:18px;position:relative}
  .pf-tech ul li::before{content:"›";position:absolute;left:0;color:#0d9488;font-weight:900}
  .pf-tech-link{display:inline-flex;color:#0d9488;font-weight:700;font-size:14px;text-decoration:none}
  .pf-cta{background:#0a1410;color:#fff;padding:80px 0;position:relative;overflow:hidden}
  .pf-cta::before{content:"";position:absolute;width:600px;height:600px;border-radius:50%;background:radial-gradient(circle,rgba(20,184,166,.2),transparent 65%);right:-200px;top:-300px;pointer-events:none}
  .pf-cta-inner{position:relative;display:flex;justify-content:space-between;align-items:center;gap:32px;flex-wrap:wrap}
  .pf-btn-primary{display:inline-flex;align-items:center;gap:8px;background:#0d9488;color:#fff;padding:15px 24px;border-radius:999px;font-weight:700;font-size:15px;text-decoration:none}
  .pf-related{padding:96px 0 128px}
  .pf-sec-head{max-width:780px}
  .pf-rel-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
  .pf-rel-card{background:#fff;border:1px solid #e7e7e1;border-radius:18px;overflow:hidden;text-decoration:none;color:inherit;transition:.2s ease}
  .pf-rel-card:hover{transform:translateY(-3px);border-color:#99f6e4}
  .pf-rel-thumb{position:relative;aspect-ratio:1.5;background:#dff8f4}
  .pf-rel-thumb img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
  .pf-rel-body{padding:20px 22px}
  .pf-rel-tag{font-size:11px;letter-spacing:1.2px;text-transform:uppercase;color:#7c7c74;font-weight:700;margin-bottom:8px}
  .pf-rel-card h3{margin:0 0 10px;font-family:"Syne",sans-serif;font-size:18px;letter-spacing:-.4px;line-height:1.2;font-weight:700}
  .pf-rel-card strong{font-family:"Syne",sans-serif;font-size:13px;color:#0d9488}
  @media (max-width:1060px){
    .pf-wrap{width:min(1180px,100% - 40px)}
    .pf-head-grid,.pf-content-grid{grid-template-columns:1fr;gap:28px}
    .pf-tech{position:static}
    .pf-rel-grid{grid-template-columns:1fr 1fr}
  }
  @media (max-width:760px){
    .pf-wrap{width:min(1180px,100% - 24px)}
    .pf-rel-grid,.pf-shots{grid-template-columns:1fr}
    .pf-meta-row{grid-template-columns:1fr}
  }
</style>

<main class="pf-art">
  <?php
  $hero_cover_url = $image !== "" ? $image : (has_post_thumbnail($post_id) ? (string) get_the_post_thumbnail_url($post_id, "large") : "");
  $kpi_metrics = array_slice((array) $metrics, 0, 3);
  $metric_primary = null;
  if (!empty($kpi_metrics)) {
      $metric_primary = function_exists("upsellio_split_metric_line")
          ? upsellio_split_metric_line((string) $kpi_metrics[0])
          : ["value" => "", "label" => (string) $kpi_metrics[0]];
  }
  $related_projects = get_posts([
      "post_type" => "portfolio",
      "post_status" => "publish",
      "posts_per_page" => 3,
      "post__not_in" => [$post_id],
  ]);
  ?>
  <nav class="pf-crumbs">
    <div class="pf-wrap">
      <a href="<?php echo esc_url(home_url("/")); ?>">Strona główna</a>
      <span>›</span>
      <a href="<?php echo esc_url($portfolio_url); ?>">Portfolio</a>
      <span>›</span>
      <span><?php echo esc_html($title); ?></span>
    </div>
  </nav>

  <header class="pf-head">
    <div class="pf-wrap pf-head-grid">
      <div>
        <div class="pf-eyebrow"><?php echo esc_html($badge !== "" ? $badge : "Realizacja · strona firmowa"); ?></div>
        <h1 class="pf-h1"><?php echo esc_html($title); ?></h1>
        <?php if ($excerpt !== "") : ?><p class="pf-lead"><?php echo esc_html($excerpt); ?></p><?php endif; ?>
        <div class="pf-meta-row">
          <div><span>Klient</span><strong><?php echo esc_html($title); ?></strong></div>
          <div><span>Branża</span><strong><?php echo esc_html($type !== "" ? $type : "Projekt B2B"); ?></strong></div>
          <div><span>Zakres</span><strong><?php echo esc_html($scope !== "" ? $scope : ($meta !== "" ? $meta : "UX, strona WWW, copy")); ?></strong></div>
          <div><span>Czas</span><strong><?php echo esc_html($meta !== "" ? $meta : "Wdrożenie projektu"); ?></strong></div>
        </div>
      </div>
      <aside class="pf-side">
        <div class="pf-eyebrow">Wynik w liczbach</div>
        <div class="pf-stat-big"><?php echo esc_html((string) ($metric_primary["value"] ?? "N/A")); ?></div>
        <div class="pf-stat-label"><?php echo esc_html((string) ($metric_primary["label"] ?? "Wynik projektu")); ?></div>
        <?php if (!empty($kpi_metrics)) : ?>
          <ul>
            <?php foreach ($kpi_metrics as $metric_line) :
                $metric_split = function_exists("upsellio_split_metric_line")
                    ? upsellio_split_metric_line((string) $metric_line)
                    : ["value" => "", "label" => (string) $metric_line];
                ?>
              <li>
                <strong><?php echo esc_html((string) ($metric_split["value"] ?? "•")); ?></strong>
                <span><?php echo esc_html((string) ($metric_split["label"] ?? "")); ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </aside>
    </div>
  </header>

  <section class="pf-cover">
    <div class="pf-wrap">
      <div class="pf-cover-img">
        <?php if ($hero_cover_url !== "") : ?>
          <img src="<?php echo esc_url($hero_cover_url); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy" decoding="async" width="1400" height="900" />
        <?php else : ?>
          <div class="pf-thumb-stripes"></div>
          <div class="pf-thumb-label">[ desktop mockup — strona główna projektu ]</div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="pf-section">
    <div class="pf-wrap pf-content-grid">
      <article class="pf-content">
        <?php if ($problem !== "") : ?>
          <h2 class="pf-h2">Punkt wyjścia</h2>
          <p><?php echo wp_kses_post($problem); ?></p>
        <?php endif; ?>

        <?php if ($scope !== "") : ?>
          <h2 class="pf-h2">Co zrobiłem</h2>
          <ul class="pf-bullets">
            <li><strong>Zakres prac</strong> — <?php echo wp_kses_post($scope); ?></li>
            <?php if ($meta !== "") : ?><li><strong>Meta projektu</strong> — <?php echo wp_kses_post($meta); ?></li><?php endif; ?>
            <?php if ($result !== "") : ?><li><strong>Kierunek celu</strong> — <?php echo wp_kses_post($result); ?></li><?php endif; ?>
          </ul>
        <?php endif; ?>

        <?php if ($client_quote !== "" && $has_publish_consent) : ?>
          <div class="pf-quote">
            <p>"<?php echo esc_html($client_quote); ?>"</p>
            <div class="pf-quote-author">
              <div class="pf-avatar">UK</div>
              <div>
                <strong>Klient Upsellio</strong>
                <span>Opinia po wdrożeniu projektu</span>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <div><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>

        <h2 class="pf-h2">Wybrane ekrany</h2>
        <div class="pf-shots">
          <?php foreach ([1, 2, 3, 4] as $shot_number) : ?>
            <figure class="pf-shot">
              <div class="pf-shot-img">
                <?php if ($hero_cover_url !== "") : ?>
                  <img src="<?php echo esc_url($hero_cover_url); ?>" alt="<?php echo esc_attr($title . " - ekran " . $shot_number); ?>" loading="lazy" decoding="async" width="900" height="600" />
                <?php else : ?>
                  <div class="pf-thumb-stripes"></div>
                <?php endif; ?>
              </div>
              <figcaption><?php echo esc_html("Widok projektu " . $shot_number); ?></figcaption>
            </figure>
          <?php endforeach; ?>
        </div>

        <?php if ($custom_html !== "" || $custom_css !== "" || $custom_js !== "") : ?>
          <div>
            <?php if ($custom_html !== "") : ?><div><?php echo $custom_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?>
            <?php if ($custom_css !== "") : ?><style><?php echo $custom_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></style><?php endif; ?>
            <?php if ($custom_js !== "") : ?><script><?php echo $custom_js; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></script><?php endif; ?>
          </div>
        <?php endif; ?>
      </article>

      <aside class="pf-tech">
        <div class="pf-eyebrow">Stack &amp; narzędzia</div>
        <?php if (!empty($technologies)) : ?>
          <ul>
            <?php foreach ($technologies as $technology) : ?>
              <li><?php echo esc_html((string) $technology); ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
        <?php if ($external_url !== "") : ?>
          <div class="pf-eyebrow">Linki</div>
          <a class="pf-tech-link" href="<?php echo esc_url($external_url); ?>" target="_blank" rel="noopener">Zobacz wdrożenie ↗</a>
        <?php endif; ?>
      </aside>
    </div>
  </section>

  <section class="pf-cta">
    <div class="pf-wrap pf-cta-inner">
      <div>
        <div class="pf-eyebrow pf-eyebrow-light">Twój projekt</div>
        <h2 class="pf-h2 pf-h2-light">Twoja strona też może realnie pracować na sprzedaż.</h2>
      </div>
      <a class="pf-btn-primary" href="<?php echo esc_url(home_url("/#kontakt")); ?>"><?php echo esc_html($cta !== "" ? $cta : "Porozmawiajmy o projekcie"); ?> →</a>
    </div>
  </section>

  <?php if (!empty($related_projects)) : ?>
    <section class="pf-related">
      <div class="pf-wrap">
        <header class="pf-sec-head">
          <div class="pf-eyebrow">Kolejne realizacje</div>
          <h2 class="pf-h2">Zobacz też.</h2>
        </header>
        <div class="pf-divider"></div>
        <div class="pf-rel-grid">
          <?php foreach ($related_projects as $related_project) :
              $related_project_id = (int) $related_project->ID;
              $related_image = function_exists("upsellio_resolve_post_image_url")
                  ? upsellio_resolve_post_image_url($related_project_id, "_ups_port_image", "medium_large")
                  : (string) get_the_post_thumbnail_url($related_project_id, "medium_large");
              $related_metric_line = (string) get_post_meta($related_project_id, "_ups_port_metrics", true);
              $related_metric = "";
              if ($related_metric_line !== "") {
                  $related_metrics = function_exists("upsellio_parse_metrics_lines") ? upsellio_parse_metrics_lines($related_metric_line) : [$related_metric_line];
                  $related_first_metric = (string) ($related_metrics[0] ?? "");
                  $related_split = function_exists("upsellio_split_metric_line") ? upsellio_split_metric_line($related_first_metric) : ["value" => "", "label" => $related_first_metric];
                  $related_metric = trim(((string) ($related_split["value"] ?? "")) . " " . ((string) ($related_split["label"] ?? "")));
              }
              ?>
            <a class="pf-rel-card" href="<?php echo esc_url(get_permalink($related_project_id)); ?>">
              <div class="pf-rel-thumb">
                <?php if ($related_image !== "") : ?>
                  <img src="<?php echo esc_url($related_image); ?>" alt="<?php echo esc_attr(get_the_title($related_project_id)); ?>" loading="lazy" decoding="async" width="900" height="600" />
                <?php else : ?>
                  <div class="pf-thumb-stripes"></div>
                <?php endif; ?>
              </div>
              <div class="pf-rel-body">
                <div class="pf-rel-tag"><?php echo esc_html((string) get_post_meta($related_project_id, "_ups_port_type", true)); ?></div>
                <h3><?php echo esc_html(get_the_title($related_project_id)); ?></h3>
                <strong><?php echo esc_html($related_metric !== "" ? $related_metric : "Sprawdz case study"); ?></strong>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>
</main>
<?php
get_footer();
