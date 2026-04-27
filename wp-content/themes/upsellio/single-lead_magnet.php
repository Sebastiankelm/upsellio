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
$single_faq_items = [
    [
        "question" => "Czy mogę użyć tego materiału bez doświadczenia w reklamach?",
        "answer" => "Tak. Materiał prowadzi przez konkretne punkty kontrolne i pytania diagnostyczne, więc możesz wykorzystać go samodzielnie przed rozmową z agencją lub specjalistą.",
    ],
    [
        "question" => "Jak długo zajmuje przejście przez materiał?",
        "answer" => "Większość checklist, audytów i szablonów zajmuje od kilku do kilkunastu minut. Największą wartość daje jednak wdrożenie poprawek, które materiał pomaga znaleźć.",
    ],
    [
        "question" => "Czy materiał jest aktualny?",
        "answer" => "Materiały są przygotowane pod aktualne problemy firm B2B: koszty kampanii, jakość leadów, konwersję landing page i poprawny pomiar działań marketingowych.",
    ],
];
?>
<style>
  .lms-page { background:#f8fafc; color:#071426; }
  .lms-wrap { width:min(1240px, calc(100% - 32px)); margin:0 auto; }
  .lms-hero { border-bottom:1px solid #e2e8f0; background:linear-gradient(180deg, rgba(20,184,166,0.12), rgba(255,255,255,0)); }
  .lms-hero-inner { padding:56px 0 42px; }
  .lms-back { color:#64748b; font-size:14px; font-weight:600; }
  .lms-badge { display:inline-flex; margin-top:12px; border-radius:999px; border:1px solid #99f6e4; background:#ecfeff; color:#0f766e; font-size:12px; font-weight:700; padding:6px 12px; }
  .lms-title { margin:16px 0 14px; max-width:830px; font-family:"Syne",sans-serif; font-size:clamp(34px, 6vw, 62px); line-height:.98; letter-spacing:-.05em; }
  .lms-excerpt { margin:0; max-width:850px; color:#334155; font-size:19px; line-height:1.72; }
  .lms-meta { margin-top:15px; color:#64748b; font-size:14px; }
  .lms-main { padding:36px 0 52px; }
  .lms-layout { display:grid; grid-template-columns:1fr; gap:16px; }
  .lms-card { border:1px solid #e2e8f0; border-radius:24px; background:#fff; padding:22px; overflow:hidden; }
  .lms-cover { border-radius:18px; overflow:hidden; margin-bottom:18px; max-height:390px; }
  .lms-cover img { width:100%; height:100%; object-fit:cover; display:block; }
  .lms-content { color:#334155; line-height:1.85; }
  .lms-content h2, .lms-content h3 { font-family:"Syne",sans-serif; letter-spacing:-.03em; color:#071426; margin:20px 0 8px; }
  .lms-form-title { margin:0 0 7px; font-family:"Syne",sans-serif; font-size:28px; line-height:1.05; letter-spacing:-.03em; }
  .lms-form-text { margin:0 0 14px; color:#334155; line-height:1.7; }
  .lms-form .field { margin-bottom:12px; }
  .lms-form label { display:block; margin-bottom:6px; color:#334155; font-size:12px; font-weight:600; }
  .lms-form input,.lms-form textarea {
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
  .lms-form textarea { min-height:110px; resize:vertical; line-height:1.6; }
  .lms-form input:focus,.lms-form textarea:focus { border-color:#0d9488; box-shadow:0 0 0 3px rgba(20,184,166,.13); }
  .lms-submit { width:100%; margin-top:4px; min-height:46px; border:none; border-radius:12px; background:linear-gradient(135deg,#0d9488,#14b8a6); color:#fff; font-size:15px; font-weight:700; cursor:pointer; transition:background .18s,transform .18s; }
  .lms-submit:hover { background:#0f766e; transform:translateY(-1px); }
  .lms-custom-block { margin-top:16px; border:1px solid #e2e8f0; border-radius:18px; padding:14px; background:#f8fafc; }
  .lms-faq { margin-top:22px; display:grid; gap:10px; }
  .lms-faq details { border:1px solid #e2e8f0; border-radius:16px; background:#f8fafc; padding:15px 16px; }
  .lms-faq summary { cursor:pointer; font-weight:800; color:#071426; }
  .lms-faq p { margin:10px 0 0; color:#334155; line-height:1.72; }
  @media (min-width:761px){ .lms-wrap{width:min(1240px, calc(100% - 48px));} }
  @media (min-width:1100px){ .lms-layout{grid-template-columns:minmax(0, 1fr) 350px;align-items:start;} }
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
        <?php if (!empty($single_faq_items)) : ?>
          <div class="lms-faq">
            <h2>Najczęstsze pytania o materiał</h2>
            <?php foreach ($single_faq_items as $faq_item) : ?>
              <details>
                <summary><?php echo esc_html((string) $faq_item["question"]); ?></summary>
                <p><?php echo esc_html((string) $faq_item["answer"]); ?></p>
              </details>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
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
        <h2 class="lms-form-title">Pobierz materiał bezpłatnie</h2>
        <p class="lms-form-text">Zostaw e-mail, a otrzymasz materiał i krótką informację, jak wykorzystać go w praktyce. Bez spamu. Wypis jednym kliknięciem.</p>
        <form class="lms-form" method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" data-upsellio-lead-form="1" data-upsellio-server-form="1" data-lead-magnet-name="<?php echo esc_attr($title); ?>">
          <input type="hidden" name="action" value="upsellio_submit_lead" />
          <input type="hidden" name="redirect_url" value="<?php echo esc_url(get_permalink($post_id)); ?>" />
          <input type="hidden" name="lead_form_origin" value="lead-magnet-single" />
          <input type="hidden" name="lead_source" value="lead-magnet-single" />
          <input type="hidden" name="lead_magnet_name" value="<?php echo esc_attr($title); ?>" />
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
          <button class="lms-submit" type="submit">Pobierz materiał</button>
        </form>
      </aside>
    </div>
  </section>
</main>

<script type="application/ld+json">
<?php
echo wp_json_encode([
    "@context" => "https://schema.org",
    "@type" => "DigitalDocument",
    "name" => $title,
    "description" => $excerpt,
    "url" => (string) get_permalink($post_id),
    "isPartOf" => [
        "@type" => "WebSite",
        "name" => "Upsellio",
        "url" => home_url("/"),
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
?>
</script>

<script type="application/ld+json">
<?php
echo wp_json_encode([
    "@context" => "https://schema.org",
    "@type" => "BreadcrumbList",
    "itemListElement" => [
        [
            "@type" => "ListItem",
            "position" => 1,
            "name" => "Strona główna",
            "item" => home_url("/"),
        ],
        [
            "@type" => "ListItem",
            "position" => 2,
            "name" => "Lead magnety",
            "item" => upsellio_get_lead_magnets_page_url(),
        ],
        [
            "@type" => "ListItem",
            "position" => 3,
            "name" => $title,
            "item" => (string) get_permalink($post_id),
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
?>
</script>

<?php if (!empty($single_faq_items)) : ?>
<script type="application/ld+json">
<?php
echo wp_json_encode([
    "@context" => "https://schema.org",
    "@type" => "FAQPage",
    "mainEntity" => array_map(static function ($faq_item) {
        return [
            "@type" => "Question",
            "name" => (string) $faq_item["question"],
            "acceptedAnswer" => [
                "@type" => "Answer",
                "text" => (string) $faq_item["answer"],
            ],
        ];
    }, $single_faq_items),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
?>
</script>
<?php endif; ?>

<script>
  (function () {
    const form = document.querySelector(".lms-form[data-lead-magnet-name]");
    if (!form) return;

    form.addEventListener("submit", function () {
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({
        event: "lead_magnet_download",
        lead_magnet_name: form.getAttribute("data-lead-magnet-name") || "",
      });
    });
  })();
</script>

<?php
get_footer();
