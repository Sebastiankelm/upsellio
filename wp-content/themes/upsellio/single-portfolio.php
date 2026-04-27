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
$image = (string) get_post_meta($post_id, "_ups_port_image", true);
$problem = (string) get_post_meta($post_id, "_ups_port_problem", true);
$scope = (string) get_post_meta($post_id, "_ups_port_scope", true);
$result = (string) get_post_meta($post_id, "_ups_port_result", true);
$external_url = (string) get_post_meta($post_id, "_ups_port_external_url", true);
$metrics = function_exists("upsellio_parse_metrics_lines") ? upsellio_parse_metrics_lines((string) get_post_meta($post_id, "_ups_port_metrics", true)) : [];
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
?>
<style>
  .ports-page { background:#f8fafc; color:#071426; }
  .ports-wrap { width:min(1240px, calc(100% - 32px)); margin:0 auto; }
  .ports-hero { border-bottom:1px solid #e2e8f0; background:linear-gradient(180deg, rgba(20,184,166,0.12), rgba(255,255,255,0)); }
  .ports-hero-inner { padding:56px 0 42px; }
  .ports-back { color:#64748b; font-size:14px; font-weight:600; }
  .ports-badge { display:inline-flex; margin-top:12px; border-radius:999px; border:1px solid #99f6e4; background:#ecfeff; color:#0f766e; font-size:12px; font-weight:700; padding:6px 12px; }
  .ports-title { margin:16px 0 14px; max-width:830px; font-family:"Syne",sans-serif; font-size:clamp(34px, 6vw, 62px); line-height:.98; letter-spacing:-.05em; }
  .ports-excerpt { margin:0; max-width:850px; color:#334155; font-size:19px; line-height:1.72; }
  .ports-meta { margin-top:15px; color:#64748b; font-size:14px; }
  .ports-metrics { margin-top:14px; display:flex; flex-wrap:wrap; gap:8px; }
  .ports-metric { border:1px solid #e2e8f0; background:#f8fafc; color:#334155; border-radius:999px; font-size:12px; padding:6px 10px; }
  .ports-main { padding:36px 0 52px; }
  .ports-layout { display:grid; grid-template-columns:1fr; gap:16px; }
  .ports-card { border:1px solid #e2e8f0; border-radius:24px; background:#fff; padding:22px; overflow:hidden; }
  .ports-cover { border-radius:18px; overflow:hidden; margin-bottom:18px; max-height:390px; }
  .ports-cover img { width:100%; height:100%; object-fit:cover; display:block; }
  .ports-content { color:#334155; line-height:1.85; }
  .ports-content h2, .ports-content h3 { font-family:"Syne",sans-serif; letter-spacing:-.03em; color:#071426; margin:20px 0 8px; }
  .ports-sections { margin-top:18px; display:grid; gap:10px; }
  .ports-section { border:1px solid #e2e8f0; background:#f8fafc; border-radius:14px; padding:14px; }
  .ports-section-title { margin:0 0 6px; font-family:"Syne",sans-serif; font-size:20px; letter-spacing:-.02em; }
  .ports-section-copy { margin:0; color:#334155; line-height:1.74; }
  .ports-custom-block { margin-top:16px; border:1px solid #e2e8f0; border-radius:18px; padding:14px; background:#f8fafc; }
  .ports-live-block { margin-top:16px; border:1px solid #e2e8f0; border-radius:18px; padding:14px; background:#f8fafc; }
  .ports-live-head { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:10px; }
  .ports-live-title { margin:0; font-family:"Syne",sans-serif; font-size:22px; letter-spacing:-.02em; color:#071426; }
  .ports-live-copy { margin:0; color:#64748b; font-size:14px; line-height:1.6; }
  .ports-live-switch { display:flex; gap:8px; }
  .ports-live-btn { border:1px solid #e2e8f0; background:#fff; color:#334155; border-radius:999px; padding:6px 12px; font-size:12px; font-weight:700; cursor:pointer; transition:.18s ease; }
  .ports-live-btn.is-active { border-color:#0d9488; background:#ecfeff; color:#0f766e; }
  .ports-live-frame-wrap { border:1px solid #e2e8f0; border-radius:14px; background:#fff; padding:10px; transition:.2s ease; }
  .ports-live-frame { width:100%; height:640px; border:0; border-radius:10px; display:block; background:#fff; }
  .ports-live-frame-wrap.is-mobile { max-width:410px; margin:0 auto; }
  .ports-live-frame-wrap.is-mobile .ports-live-frame { height:760px; }
  .ports-live-note { margin:10px 0 0; color:#69726d; font-size:12px; line-height:1.6; }
  .ports-form-title { margin:0 0 7px; font-family:"Syne",sans-serif; font-size:28px; line-height:1.05; letter-spacing:-.03em; }
  .ports-form-text { margin:0 0 14px; color:#334155; line-height:1.7; }
  .ports-form .field { margin-bottom:12px; }
  .ports-form label { display:block; margin-bottom:6px; color:#334155; font-size:12px; font-weight:600; }
  .ports-form input,.ports-form textarea {
    width:100%;
    border:1px solid #cbd5e1;
    border-radius:12px;
    min-height:46px;
    padding:13px 15px;
    font-size:15px;
    outline:none;
    background:#fff;
    color:#071426;
    transition:border-color .18s,box-shadow .18s;
  }
  .ports-form textarea { min-height:110px; resize:vertical; line-height:1.6; }
  .ports-form input:focus,.ports-form textarea:focus { border-color:#0d9488; box-shadow:0 0 0 3px rgba(20,184,166,.13); }
  .ports-submit { width:100%; margin-top:4px; min-height:46px; border:none; border-radius:12px; background:linear-gradient(135deg,#0d9488,#14b8a6); color:#fff; font-size:15px; font-weight:700; cursor:pointer; transition:background .18s,transform .18s; }
  .ports-submit:hover { background:#0f766e; transform:translateY(-1px); }
  .ports-side-note { margin-top:14px; padding-top:12px; border-top:1px solid #e2e8f0; color:#64748b; font-size:13px; line-height:1.6; }
  .ports-side-actions { margin-top:10px; display:grid; gap:8px; }
  .ports-side-link { display:inline-flex; align-items:center; justify-content:center; min-height:42px; border-radius:10px; font-size:14px; font-weight:700; }
  .ports-side-link.secondary { border:1px solid #e2e8f0; background:#fff; color:#334155; }
  .ports-side-link.secondary:hover { border-color:#0d9488; color:#0d9488; }
  @media (min-width:761px){ .ports-wrap{width:min(1240px, calc(100% - 48px));} }
  @media (min-width:1100px){
    .ports-layout{grid-template-columns:minmax(0, 1fr) 350px;align-items:start;gap:22px;}
    .ports-sidebar{position:sticky;top:104px;align-self:start;padding:18px;}
    .ports-sidebar .ports-form-title{font-size:24px;line-height:1.08;margin-bottom:6px;}
    .ports-sidebar .ports-form-text{font-size:14px;line-height:1.55;margin-bottom:10px;}
    .ports-sidebar .ports-form .field{margin-bottom:8px;}
    .ports-sidebar .ports-form label{margin-bottom:4px;font-size:11px;}
    .ports-sidebar .ports-form input,
    .ports-sidebar .ports-form textarea{min-height:40px;padding:10px 12px;font-size:14px;border-radius:10px;}
    .ports-sidebar .ports-form textarea{min-height:74px;line-height:1.45;}
    .ports-sidebar .ports-submit{min-height:42px;font-size:14px;}
    .ports-sidebar .ports-side-note{margin-top:10px;padding-top:10px;font-size:12px;line-height:1.45;}
    .ports-sidebar .ports-side-actions{margin-top:8px;gap:6px;}
    .ports-sidebar .ports-side-link{min-height:38px;font-size:13px;}
  }
</style>

<main class="ports-page">
  <section class="ports-hero">
    <div class="ports-wrap ports-hero-inner">
      <a class="ports-back" href="<?php echo esc_url($portfolio_url); ?>">← Wróć do katalogu portfolio</a>
      <?php if ($badge !== "") : ?><div class="ports-badge"><?php echo esc_html($badge); ?></div><?php endif; ?>
      <h1 class="ports-title"><?php echo esc_html($title); ?></h1>
      <?php if ($excerpt !== "") : ?><p class="ports-excerpt"><?php echo esc_html($excerpt); ?></p><?php endif; ?>
      <?php if ($type !== "" || $meta !== "") : ?><div class="ports-meta"><?php echo esc_html(trim($type . " · " . $meta, " ·")); ?></div><?php endif; ?>
      <?php if (!empty($metrics)) : ?>
        <div class="ports-metrics">
          <?php foreach ((array) $metrics as $metric) : ?>
            <span class="ports-metric"><?php echo esc_html((string) $metric); ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <section class="ports-main">
    <div class="ports-wrap ports-layout">
      <article class="ports-card">
        <?php if ($image !== "") : ?>
          <div class="ports-cover">
            <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy" decoding="async" width="1400" height="900" />
          </div>
        <?php endif; ?>

        <?php if ($problem !== "" || $scope !== "" || $result !== "") : ?>
          <div class="ports-sections">
            <?php if ($problem !== "") : ?>
              <section class="ports-section">
                <h2 class="ports-section-title">Problem biznesowy</h2>
                <p class="ports-section-copy"><?php echo esc_html($problem); ?></p>
              </section>
            <?php endif; ?>
            <?php if ($scope !== "") : ?>
              <section class="ports-section">
                <h2 class="ports-section-title">Zakres prac</h2>
                <p class="ports-section-copy"><?php echo esc_html($scope); ?></p>
              </section>
            <?php endif; ?>
            <?php if ($result !== "") : ?>
              <section class="ports-section">
                <h2 class="ports-section-title">Efekt biznesowy</h2>
                <p class="ports-section-copy"><?php echo esc_html($result); ?></p>
              </section>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <div class="ports-content"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>

        <?php if ($external_url !== "") : ?>
          <section class="ports-live-block" data-live-preview="1">
            <div class="ports-live-head">
              <div>
                <h2 class="ports-live-title">Interaktywny podgląd wdrożonej strony</h2>
                <p class="ports-live-copy">Sprawdź działającą wersję projektu bez wychodzenia z case study.</p>
              </div>
              <div class="ports-live-switch">
                <button class="ports-live-btn is-active" type="button" data-preview-device="desktop">Desktop</button>
                <button class="ports-live-btn" type="button" data-preview-device="mobile">Mobile</button>
              </div>
            </div>
            <div class="ports-live-frame-wrap" data-preview-frame-wrap>
              <iframe
                class="ports-live-frame"
                src="<?php echo esc_url($external_url); ?>"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                sandbox="allow-forms allow-scripts allow-same-origin allow-popups"
                title="Podgląd projektu: <?php echo esc_attr($title); ?>"
              ></iframe>
            </div>
            <p class="ports-live-note">
              Jeśli podgląd nie ładuje się poprawnie (część serwisów blokuje osadzanie), otwórz projekt w nowej karcie:
              <a href="<?php echo esc_url($external_url); ?>" target="_blank" rel="noopener">zobacz wersję live</a>.
            </p>
          </section>
        <?php endif; ?>

        <?php if ($custom_html !== "" || $custom_css !== "" || $custom_js !== "") : ?>
          <div class="ports-custom-block">
            <?php if ($custom_html !== "") : ?>
              <div class="ports-custom-html"><?php echo $custom_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
            <?php endif; ?>
            <?php if ($custom_css !== "") : ?>
              <style><?php echo $custom_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></style>
            <?php endif; ?>
            <?php if ($custom_js !== "") : ?>
              <script><?php echo $custom_js; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></script>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </article>

      <aside class="ports-card ports-sidebar">
        <h2 class="ports-form-title">Chcesz podobny efekt w swojej firmie?</h2>
        <p class="ports-form-text">Wypełnij krótki formularz. Wrócę z rekomendacją, jak przełożyć podobne podejście na Twój biznes.</p>
        <form class="ports-form" method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" data-upsellio-lead-form="1" data-upsellio-server-form="1">
          <input type="hidden" name="action" value="upsellio_submit_lead" />
          <input type="hidden" name="redirect_url" value="<?php echo esc_url(get_permalink($post_id)); ?>" />
          <input type="hidden" name="lead_form_origin" value="portfolio-single" />
          <input type="hidden" name="lead_source" value="portfolio-single" />
          <input type="hidden" name="utm_source" data-ups-utm="source" value="" />
          <input type="hidden" name="utm_medium" data-ups-utm="medium" value="" />
          <input type="hidden" name="utm_campaign" data-ups-utm="campaign" value="" />
          <input type="hidden" name="landing_url" data-ups-context="landing" value="" />
          <input type="hidden" name="referrer" data-ups-context="referrer" value="" />
          <input type="hidden" name="lead_service" value="<?php echo esc_attr($title); ?>" />
          <input type="text" name="lead_website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;" />
          <?php wp_nonce_field("upsellio_unified_lead_form", "upsellio_lead_form_nonce"); ?>
          <div class="field">
            <label for="ports-name">Imię i firma *</label>
            <input id="ports-name" name="lead_name" type="text" autocomplete="name organization" inputmode="text" required />
          </div>
          <div class="field">
            <label for="ports-email">E-mail *</label>
            <input id="ports-email" name="lead_email" type="email" autocomplete="email" inputmode="email" required />
          </div>
          <div class="field">
            <label for="ports-phone">Telefon</label>
            <input id="ports-phone" name="lead_phone" type="tel" autocomplete="tel" inputmode="tel" />
          </div>
          <div class="field">
            <label for="ports-message">Krótko opisz cel projektu *</label>
            <textarea id="ports-message" name="lead_message" required>Chcę omówić podobny projekt jak: <?php echo esc_textarea($title); ?>.</textarea>
          </div>
          <div class="field">
            <label style="display:flex;gap:8px;align-items:flex-start;">
              <input type="checkbox" name="lead_consent" value="1" required style="width:auto;min-height:auto;margin-top:3px;" />
              <span>Wyrażam zgodę na kontakt w sprawie mojego zapytania.</span>
            </label>
          </div>
          <button class="ports-submit" type="submit">Wyślij zapytanie</button>
        </form>

        <div class="ports-side-note">Jeśli podoba Ci się kierunek tej realizacji, możemy przełożyć podobne podejście na Twoją ofertę, proces sprzedaży i potrzeby klientów.</div>
        <div class="ports-side-actions">
          <?php if ($external_url !== "") : ?>
            <a class="ports-side-link secondary" href="<?php echo esc_url($external_url); ?>" target="_blank" rel="noopener">Zobacz projekt online</a>
          <?php endif; ?>
          <a class="ports-side-link secondary" href="<?php echo esc_url($portfolio_url); ?>">Przejdź do wszystkich realizacji</a>
          <?php if ($cta !== "") : ?><a class="ports-side-link secondary" href="<?php echo esc_url(home_url("/#kontakt")); ?>"><?php echo esc_html($cta); ?></a><?php endif; ?>
        </div>
      </aside>
    </div>
  </section>
</main>
<?php
get_footer();
