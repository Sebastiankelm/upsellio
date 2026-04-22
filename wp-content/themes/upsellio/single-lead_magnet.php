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
$type = (string) get_post_meta($post_id, "_ups_lm_type", true);
$meta = (string) get_post_meta($post_id, "_ups_lm_meta", true);
$badge = (string) get_post_meta($post_id, "_ups_lm_badge", true);
$image = (string) get_post_meta($post_id, "_ups_lm_image", true);
$custom_html = (string) get_post_meta($post_id, "_ups_lm_custom_html", true);
$custom_css = (string) get_post_meta($post_id, "_ups_lm_custom_css", true);
$custom_js = (string) get_post_meta($post_id, "_ups_lm_custom_js", true);
$custom_payload = function_exists("upsellio_prepare_custom_embed_payload")
    ? upsellio_prepare_custom_embed_payload($custom_html, $custom_css, $custom_js)
    : ["html" => $custom_html, "css" => $custom_css, "js" => $custom_js];
$custom_html = (string) ($custom_payload["html"] ?? "");
$custom_css = (string) ($custom_payload["css"] ?? "");
$custom_js = (string) ($custom_payload["js"] ?? "");
?>
<style>
  .lms-page { background:#f6f7f5; color:#101312; }
  .lms-wrap { width:min(1050px, calc(100% - 32px)); margin:0 auto; }
  .lms-hero { border-bottom:1px solid #e5e7e4; background:linear-gradient(180deg, rgba(29,158,117,0.08), rgba(255,255,255,0)); }
  .lms-hero-inner { padding:56px 0 42px; }
  .lms-back { color:#5f635f; font-size:14px; font-weight:600; }
  .lms-badge { display:inline-flex; margin-top:12px; border-radius:999px; border:1px solid #cde9dd; background:#e8f8f2; color:#085041; font-size:12px; font-weight:700; padding:6px 12px; }
  .lms-title { margin:16px 0 14px; max-width:830px; font-family:"Syne",sans-serif; font-size:clamp(34px, 6vw, 62px); line-height:.98; letter-spacing:-.05em; }
  .lms-excerpt { margin:0; max-width:850px; color:#565a56; font-size:19px; line-height:1.72; }
  .lms-meta { margin-top:15px; color:#6c706c; font-size:14px; }
  .lms-main { padding:36px 0 52px; }
  .lms-layout { display:grid; grid-template-columns:1fr; gap:16px; }
  .lms-card { border:1px solid #e5e7e4; border-radius:24px; background:#fff; padding:22px; overflow:hidden; }
  .lms-cover { border-radius:18px; overflow:hidden; margin-bottom:18px; max-height:390px; }
  .lms-cover img { width:100%; height:100%; object-fit:cover; display:block; }
  .lms-content { color:#313531; line-height:1.85; }
  .lms-content h2, .lms-content h3 { font-family:"Syne",sans-serif; letter-spacing:-.03em; color:#111412; margin:20px 0 8px; }
  .lms-form-title { margin:0 0 7px; font-family:"Syne",sans-serif; font-size:28px; line-height:1.05; letter-spacing:-.03em; }
  .lms-form-text { margin:0 0 14px; color:#5e635e; line-height:1.7; }
  .lms-form .field { margin-bottom:12px; }
  .lms-form label { display:block; margin-bottom:6px; color:#3d3d38; font-size:12px; font-weight:600; }
  .lms-form input,.lms-form textarea {
    width:100%;
    border:1px solid #c9c9c3;
    border-radius:12px;
    min-height:46px;
    padding:13px 15px;
    font-size:15px;
    outline:none;
    background:#fff;
    color:#111110;
    transition:border-color .18s,box-shadow .18s;
  }
  .lms-form textarea { min-height:110px; resize:vertical; line-height:1.6; }
  .lms-form input:focus,.lms-form textarea:focus { border-color:#1d9e75; box-shadow:0 0 0 3px rgba(29,158,117,.13); }
  .lms-submit { width:100%; margin-top:4px; min-height:46px; border:none; border-radius:12px; background:#1d9e75; color:#fff; font-size:15px; font-weight:700; cursor:pointer; transition:background .18s,transform .18s; }
  .lms-submit:hover { background:#17885f; transform:translateY(-1px); }
  .lms-custom-block { margin-top:16px; border:1px solid #dce6e0; border-radius:18px; padding:14px; background:#fafdfb; }
  @media (min-width:761px){ .lms-wrap{width:min(1050px, calc(100% - 48px));} }
  @media (min-width:981px){ .lms-layout{grid-template-columns:1.18fr .82fr;} }
</style>

<main class="lms-page">
  <section class="lms-hero">
    <div class="lms-wrap lms-hero-inner">
      <a class="lms-back" href="<?php echo esc_url(upsellio_get_lead_magnets_page_url()); ?>">← Wróć do katalogu materiałów</a>
      <?php if ($badge !== "") : ?><div class="lms-badge"><?php echo esc_html($badge); ?></div><?php endif; ?>
      <h1 class="lms-title"><?php echo esc_html($title); ?></h1>
      <?php if ($excerpt !== "") : ?><p class="lms-excerpt"><?php echo esc_html($excerpt); ?></p><?php endif; ?>
      <?php if ($type !== "" || $meta !== "") : ?><div class="lms-meta"><?php echo esc_html(trim($type . " · " . $meta, " ·")); ?></div><?php endif; ?>
    </div>
  </section>

  <section class="lms-main">
    <div class="lms-wrap lms-layout">
      <article class="lms-card">
        <?php if ($image !== "") : ?>
          <div class="lms-cover">
            <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy" decoding="async" width="1400" height="900" />
          </div>
        <?php endif; ?>
        <div class="lms-content"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
        <?php if ($custom_html !== "" || $custom_css !== "" || $custom_js !== "") : ?>
          <div class="lms-custom-block">
            <?php if ($custom_html !== "") : ?>
              <div class="lms-custom-html"><?php echo $custom_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
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

      <aside class="lms-card">
        <h2 class="lms-form-title">Pobierz materiał i umów konsultację</h2>
        <p class="lms-form-text">Wypełnij krótki formularz. Otrzymasz materiał i wrócę do Ciebie z rekomendacją, co poprawić jako pierwsze.</p>
        <form class="lms-form" method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" data-upsellio-lead-form="1" data-upsellio-server-form="1">
          <input type="hidden" name="action" value="upsellio_submit_lead" />
          <input type="hidden" name="redirect_url" value="<?php echo esc_url(get_permalink($post_id)); ?>" />
          <input type="hidden" name="lead_form_origin" value="lead-magnet-single" />
          <input type="hidden" name="lead_source" value="lead-magnet-single" />
          <input type="hidden" name="utm_source" data-ups-utm="source" value="" />
          <input type="hidden" name="utm_medium" data-ups-utm="medium" value="" />
          <input type="hidden" name="utm_campaign" data-ups-utm="campaign" value="" />
          <input type="hidden" name="landing_url" data-ups-context="landing" value="" />
          <input type="hidden" name="referrer" data-ups-context="referrer" value="" />
          <input type="hidden" name="lead_service" value="<?php echo esc_attr($title); ?>" />
          <input type="text" name="lead_website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;" />
          <?php wp_nonce_field("upsellio_unified_lead_form", "upsellio_lead_form_nonce"); ?>
          <div class="field">
            <label for="lms-name">Imię i firma *</label>
            <input id="lms-name" name="lead_name" type="text" autocomplete="name organization" inputmode="text" required />
          </div>
          <div class="field">
            <label for="lms-email">E-mail *</label>
            <input id="lms-email" name="lead_email" type="email" autocomplete="email" inputmode="email" required />
          </div>
          <div class="field">
            <label for="lms-phone">Telefon</label>
            <input id="lms-phone" name="lead_phone" type="tel" autocomplete="tel" inputmode="tel" />
          </div>
          <div class="field">
            <label for="lms-message">W czym potrzebujesz wsparcia? *</label>
            <textarea id="lms-message" name="lead_message" required>Chcę pobrać materiał: <?php echo esc_textarea($title); ?> i skonsultować działania marketingowe.</textarea>
          </div>
          <div class="field">
            <label style="display:flex;gap:8px;align-items:flex-start;">
              <input type="checkbox" name="lead_consent" value="1" required style="width:auto;min-height:auto;margin-top:3px;" />
              <span>Wyrażam zgodę na kontakt w sprawie mojego zapytania.</span>
            </label>
          </div>
          <button class="lms-submit" type="submit">Wyślij i pobierz materiał</button>
        </form>
      </aside>
    </div>
  </section>
</main>

<?php
get_footer();
