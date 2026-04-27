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
$image = (string) get_post_meta($post_id, "_ups_mport_image", true);
$date = (string) get_post_meta($post_id, "_ups_mport_date", true);
$sector = (string) get_post_meta($post_id, "_ups_mport_sector", true);
$problem = (string) get_post_meta($post_id, "_ups_mport_problem", true);
$solution = (string) get_post_meta($post_id, "_ups_mport_solution", true);
$result = (string) get_post_meta($post_id, "_ups_mport_result", true);
$tags = function_exists("upsellio_parse_textarea_lines") ? upsellio_parse_textarea_lines((string) get_post_meta($post_id, "_ups_mport_tags", true), 12) : [];
$kpis = function_exists("upsellio_parse_marketing_kpi_lines") ? upsellio_parse_marketing_kpi_lines((string) get_post_meta($post_id, "_ups_mport_kpis", true)) : [];
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
        "@type" => "Organization",
        "name" => "Upsellio",
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
?>
<style>
  .mps{background:#f8fafc;color:#071426}
  .mps-wrap{max-width:1240px;margin:0 auto;padding:0 40px}
  .mps-breadcrumb{padding:20px 0;border-bottom:1px solid #e2e8f0;background:#f1f5f9;font-size:13px;color:#64748b}
  .mps-breadcrumb a{color:#64748b}
  .mps-hero{background:linear-gradient(135deg,#1a3a5c,#2563a8);padding:80px 0 0}
  .mps-tags{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px}
  .mps-tag{font-size:11px;font-weight:600;color:rgba(255,255,255,.85);background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.22);border-radius:100px;padding:4px 12px}
  .mps h1{margin:0 0 14px;font-family:"Syne",sans-serif;font-size:clamp(28px,4.5vw,48px);line-height:1.06;color:#fff;letter-spacing:-1.4px;max-width:820px}
  .mps-sub{max-width:650px;color:rgba(255,255,255,.74);line-height:1.75;font-size:16px;margin:0 0 34px}
  .mps-meta{display:flex;gap:40px;flex-wrap:wrap;padding:26px 0;border-top:1px solid rgba(255,255,255,.18)}
  .mps-meta small{display:block;font-size:11px;letter-spacing:1.2px;text-transform:uppercase;color:rgba(255,255,255,.5)}
  .mps-meta span{font-size:14px;color:rgba(255,255,255,.92)}
  .mps-kpi{background:#fff;border-bottom:1px solid #e2e8f0;box-shadow:0 14px 40px rgba(15,23,42,.08)}
  .mps-kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);border-left:1px solid #e2e8f0}
  .mps-kpi-cell{padding:24px;border-right:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0}
  .mps-kpi-cell b{display:block;font-family:"Syne",sans-serif;font-size:30px;color:#0d9488;line-height:1}
  .mps-kpi-cell span{font-size:12px;color:#334155}
  .mps-content{display:grid;grid-template-columns:1fr;gap:28px;padding:70px 0}
  .mps article h2{font-family:"Syne",sans-serif;font-size:22px;line-height:1.18;margin:0 0 14px}
  .mps article p,.mps article li{font-size:15px;line-height:1.78;color:#334155}
  .mps-block{margin-bottom:38px}
  .mps-problem,.mps-solution{border-radius:16px;padding:20px;border-left:3px solid}
  .mps-problem{background:rgba(226,75,74,.06);border-color:#e24b4a}
  .mps-solution{background:#ecfeff;border-color:#0d9488}
  .mps-chart{background:#f1f5f9;border:1px solid #e2e8f0;border-radius:22px;padding:20px}
  .mps-chart-bars{display:flex;align-items:flex-end;gap:6px;height:120px;padding-bottom:8px;border-bottom:1px solid #e2e8f0}
  .mps-chart-bars div{flex:1;border-radius:4px 4px 0 0}
  .mps-chart-bars .before{background:#c8c8c2}
  .mps-chart-bars .after{background:#0d9488}
  .mps-side-card{border:1px solid #e2e8f0;border-radius:22px;padding:22px;background:#fff;margin-bottom:14px}
  .mps-side-card h3{font-family:"Syne",sans-serif;font-size:18px;margin:0 0 8px}
  .mps-side-card p{font-size:13px;color:#334155;line-height:1.65}
  .mps-side-card .btn{display:inline-flex;width:100%;justify-content:center;border-radius:12px;padding:12px 14px;margin-top:8px;background:linear-gradient(135deg,#0d9488,#14b8a6);color:#fff;font-weight:600}
  .mps-side-card .row{padding:10px 0;border-bottom:1px solid #e2e8f0;font-size:13px;display:flex;justify-content:space-between;gap:12px}
  .mps-side-card .row:last-child{border-bottom:none}
  .mps-tags-list{display:flex;flex-wrap:wrap;gap:7px}
  .mps-tags-list span{font-size:12px;background:#f1f5f9;border:1px solid #e2e8f0;padding:5px 10px;border-radius:8px}
  .mps-custom{margin-top:20px;padding:16px;border:1px solid #dce7e1;border-radius:14px;background:#f8fcfa}
  .mps-form input,.mps-form textarea{width:100%;border:1px solid #c8c8c2;border-radius:12px;padding:11px 12px;font-size:14px;margin-bottom:10px}
  .mps-form textarea{min-height:96px;resize:vertical}
  @media(min-width:1100px){
    .mps-content{grid-template-columns:minmax(0,1fr) 340px;gap:34px}
    .mps-side{position:sticky;top:104px;align-self:start}
    .mps-side .mps-side-card{padding:18px;margin-bottom:10px}
    .mps-side .mps-side-card h3{font-size:17px;margin-bottom:6px}
    .mps-side .mps-side-card p{font-size:12px;line-height:1.5;margin:0 0 8px}
    .mps-side .mps-form input,
    .mps-side .mps-form textarea{padding:9px 11px;font-size:13px;margin-bottom:8px;border-radius:10px}
    .mps-side .mps-form textarea{min-height:72px;line-height:1.45}
    .mps-side .mps-form label{font-size:11px;line-height:1.35;margin:4px 0 8px}
    .mps-side .mps-side-card .btn{padding:10px 12px;font-size:13px}
    .mps-side .mps-side-card .row{padding:8px 0;font-size:12px}
    .mps-side .mps-tags-list{gap:5px}
    .mps-side .mps-tags-list span{font-size:11px;padding:4px 8px}
  }
  @media(max-width:980px){.mps-kpi-grid{grid-template-columns:1fr 1fr}.mps-content{grid-template-columns:1fr}}
  @media(max-width:760px){.mps-wrap{padding:0 24px}.mps-kpi-grid{grid-template-columns:1fr}}
</style>

<main class="mps">
  <nav class="mps-breadcrumb">
    <div class="mps-wrap">
      <a href="<?php echo esc_url(home_url("/")); ?>">Upsellio</a> ›
      <a href="<?php echo esc_url($list_url); ?>">Portfolio marketingowe</a> ›
      <span><?php echo esc_html($title); ?></span>
    </div>
  </nav>

  <section class="mps-hero">
    <div class="mps-wrap">
      <div class="mps-tags">
        <?php if ($badge !== "") : ?><span class="mps-tag"><?php echo esc_html($badge); ?></span><?php endif; ?>
        <?php if ($type !== "") : ?><span class="mps-tag"><?php echo esc_html($type); ?></span><?php endif; ?>
        <?php foreach (array_slice($tags, 0, 2) as $tag) : ?><span class="mps-tag"><?php echo esc_html((string) $tag); ?></span><?php endforeach; ?>
      </div>
      <h1><?php echo esc_html($title); ?></h1>
      <?php if ($excerpt !== "") : ?><p class="mps-sub"><?php echo esc_html($excerpt); ?></p><?php endif; ?>
      <div class="mps-meta">
        <div><small>Branża</small><span><?php echo esc_html($sector !== "" ? $sector : "Marketing B2B/B2C"); ?></span></div>
        <div><small>Okres</small><span><?php echo esc_html($date !== "" ? $date : "Q1 2024"); ?></span></div>
        <div><small>Zakres</small><span><?php echo esc_html($meta !== "" ? $meta : "Kampanie + landing page"); ?></span></div>
      </div>
    </div>
  </section>

  <?php if (!empty($kpis)) : ?>
    <section class="mps-kpi">
      <div class="mps-wrap mps-kpi-grid">
        <?php foreach (array_slice($kpis, 0, 4) as $kpi) : ?>
          <div class="mps-kpi-cell">
            <div style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:1.1px;margin-bottom:6px;"><?php echo esc_html((string) ($kpi["label"] ?? "KPI")); ?></div>
            <div style="font-size:12px;color:#64748b;margin-bottom:6px;">Przed: <?php echo esc_html((string) ($kpi["before"] ?? "-")); ?></div>
            <b><?php echo esc_html((string) (($kpi["after"] ?? "") !== "" ? $kpi["after"] : ($kpi["change"] ?? "-"))); ?></b>
            <span><?php echo esc_html((string) ($kpi["desc"] ?? ($kpi["change"] ?? ""))); ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <section>
    <div class="mps-wrap mps-content">
      <article>
        <?php if ($image !== "") : ?><img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>" style="width:100%;border-radius:20px;margin-bottom:22px;" loading="lazy" /><?php endif; ?>
        <?php if ($problem !== "") : ?><div class="mps-block mps-problem"><h2>Problem wyjściowy</h2><p><?php echo esc_html($problem); ?></p></div><?php endif; ?>
        <?php if ($solution !== "") : ?><div class="mps-block mps-solution"><h2>Co wdrożyliśmy</h2><p><?php echo esc_html($solution); ?></p></div><?php endif; ?>
        <div class="mps-block"><h2>Szczegóły case study</h2><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
        <?php if ($result !== "") : ?><div class="mps-block"><h2>Wynik biznesowy</h2><p><?php echo esc_html($result); ?></p></div><?php endif; ?>

        <div class="mps-chart">
          <div style="font-size:13px;color:#334155;margin-bottom:10px;">Przykładowy trend poprawy KPI po wdrożeniu</div>
          <div class="mps-chart-bars">
            <div class="before" style="height:95%"></div><div class="after" style="height:100%"></div>
            <div class="before" style="height:88%"></div><div class="after" style="height:78%"></div>
            <div class="before" style="height:93%"></div><div class="after" style="height:62%"></div>
            <div class="before" style="height:90%"></div><div class="after" style="height:50%"></div>
            <div class="before" style="height:91%"></div><div class="after" style="height:42%"></div>
          </div>
        </div>

        <?php if ($custom_html !== "" || $custom_css !== "" || $custom_js !== "") : ?>
          <div class="mps-custom">
            <?php if ($custom_html !== "") : ?><div><?php echo $custom_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?>
            <?php if ($custom_css !== "") : ?><style><?php echo $custom_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></style><?php endif; ?>
            <?php if ($custom_js !== "") : ?><script><?php echo $custom_js; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></script><?php endif; ?>
          </div>
        <?php endif; ?>
      </article>

      <aside class="mps-side">
        <div class="mps-side-card">
          <h3>Chcesz podobny wynik?</h3>
          <p>Wypełnij krótki formularz. Otrzymasz rekomendację działań dla Twojej kampanii lub strony.</p>
          <form class="mps-form" method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" data-upsellio-lead-form="1" data-upsellio-server-form="1">
            <input type="hidden" name="action" value="upsellio_submit_lead" />
            <input type="hidden" name="redirect_url" value="<?php echo esc_url(get_permalink($post_id)); ?>" />
            <input type="hidden" name="lead_form_origin" value="marketing-portfolio-single" />
            <input type="hidden" name="lead_source" value="marketing-portfolio-single" />
            <input type="hidden" name="lead_service" value="<?php echo esc_attr($title); ?>" />
            <input type="hidden" name="utm_source" data-ups-utm="source" value="" />
            <input type="hidden" name="utm_medium" data-ups-utm="medium" value="" />
            <input type="hidden" name="utm_campaign" data-ups-utm="campaign" value="" />
            <input type="hidden" name="landing_url" data-ups-context="landing" value="" />
            <input type="hidden" name="referrer" data-ups-context="referrer" value="" />
            <input type="text" name="lead_website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;" />
            <?php wp_nonce_field("upsellio_unified_lead_form", "upsellio_lead_form_nonce"); ?>
            <input type="text" name="lead_name" placeholder="Imię i firma *" autocomplete="name organization" inputmode="text" required />
            <input type="email" name="lead_email" placeholder="E-mail *" autocomplete="email" inputmode="email" required />
            <input type="tel" name="lead_phone" placeholder="Telefon" autocomplete="tel" inputmode="tel" />
            <textarea name="lead_message" required>Chcę omówić podobny case marketingowy jak: <?php echo esc_textarea($title); ?>.</textarea>
            <label style="display:flex;gap:8px;align-items:flex-start;font-size:12px;margin:6px 0 10px;"><input type="checkbox" name="lead_consent" value="1" required />Wyrażam zgodę na kontakt w sprawie zapytania.</label>
            <button class="btn" type="submit">Wyślij zapytanie</button>
          </form>
        </div>

        <div class="mps-side-card">
          <div class="row"><span>Typ</span><span><?php echo esc_html($type !== "" ? $type : "Case marketingowy"); ?></span></div>
          <div class="row"><span>Branża</span><span><?php echo esc_html($sector !== "" ? $sector : "B2B/B2C"); ?></span></div>
          <div class="row"><span>Okres</span><span><?php echo esc_html($date !== "" ? $date : "2024"); ?></span></div>
        </div>

        <?php if (!empty($tags)) : ?>
          <div class="mps-side-card">
            <h3>Tagi</h3>
            <div class="mps-tags-list"><?php foreach ($tags as $tag) : ?><span><?php echo esc_html((string) $tag); ?></span><?php endforeach; ?></div>
          </div>
        <?php endif; ?>

        <div class="mps-side-card">
          <a class="btn" href="<?php echo esc_url(home_url("/#kontakt")); ?>">Umów bezpłatną rozmowę →</a>
        </div>
      </aside>
    </div>
  </section>
</main>
<script type="application/ld+json">
<?php echo wp_json_encode($schema_article, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
</script>
<?php
get_footer();
