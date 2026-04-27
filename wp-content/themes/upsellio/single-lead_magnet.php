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
$image = function_exists("upsellio_resolve_post_image_url")
    ? upsellio_resolve_post_image_url($post_id, "_ups_lm_image", "large")
    : (string) get_post_meta($post_id, "_ups_lm_image", true);
$bullets = function_exists("upsellio_parse_textarea_lines") ? upsellio_parse_textarea_lines((string) get_post_meta($post_id, "_ups_lm_bullets", true), 8) : [];
$trust_bullets = function_exists("upsellio_get_trust_seo_section") ? upsellio_get_trust_seo_section("lead_magnet_bullets") : [];
if (empty($bullets) && is_array($trust_bullets)) {
    $bullets = array_slice($trust_bullets, 0, 6);
}
$format_meta_parts = array_filter([
    $type !== "" ? $type : "",
    $meta !== "" ? $meta : "",
    "bezpłatny",
], static function ($value) {
    return (string) $value !== "";
});
$format_label = !empty($format_meta_parts) ? "Format: " . implode(" · ", $format_meta_parts) : "";
$is_gated = (string) get_post_meta($post_id, "_ups_lm_gated", true) === "1";
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
  .lms-hero { border-bottom:1px solid #e2e8f0; background:radial-gradient(circle at top right, rgba(20,184,166,0.18), rgba(255,255,255,0) 60%), linear-gradient(180deg, rgba(20,184,166,0.10), rgba(255,255,255,0) 70%); }
  .lms-hero-inner { padding:56px 0 42px; display:grid; grid-template-columns:1fr; gap:32px; align-items:center; }
  .lms-hero-copy { min-width:0; }
  .lms-hero-visual { display:none; }
  .lms-back { color:#64748b; font-size:14px; font-weight:600; }
  .lms-badge { display:inline-flex; margin-top:12px; border-radius:999px; border:1px solid #99f6e4; background:#ecfeff; color:#0f766e; font-size:12px; font-weight:700; padding:6px 12px; }
  .lms-format { display:inline-flex; align-items:center; gap:8px; margin:10px 0 0; padding:5px 11px; border-radius:999px; background:#fff; border:1px solid #e2e8f0; color:#475569; font-size:12px; font-weight:600; letter-spacing:.02em; }
  .lms-format svg { width:13px; height:13px; color:#0d9488; }
  .lms-title { margin:16px 0 14px; max-width:830px; font-family:"Syne",sans-serif; font-size:clamp(34px, 6vw, 62px); line-height:.98; letter-spacing:-.05em; }
  .lms-excerpt { margin:0; max-width:850px; color:#334155; font-size:19px; line-height:1.72; }
  .lms-meta { margin-top:15px; color:#64748b; font-size:14px; }
  .lms-mockup { position:relative; max-width:340px; margin-left:auto; aspect-ratio:1 / 1.32; border-radius:18px; background:#fff; box-shadow:0 30px 80px -30px rgba(15,23,42,.35), 0 8px 28px -10px rgba(15,23,42,.18); border:1px solid #e2e8f0; transform:rotate(-2deg); padding:28px 24px; display:flex; flex-direction:column; gap:14px; }
  .lms-mockup::after { content:""; position:absolute; inset:0; border-radius:18px; background:linear-gradient(160deg, rgba(20,184,166,0.10), rgba(255,255,255,0) 45%); pointer-events:none; }
  .lms-mockup-head { display:flex; align-items:center; justify-content:space-between; }
  .lms-mockup-brand { font-family:"Syne",sans-serif; font-weight:800; font-size:14px; letter-spacing:-.02em; color:#0d9488; }
  .lms-mockup-tag { font-size:9px; font-weight:700; letter-spacing:.16em; text-transform:uppercase; color:#94a3b8; }
  .lms-mockup-title { margin:6px 0 4px; font-family:"Syne",sans-serif; font-size:18px; line-height:1.15; letter-spacing:-.02em; color:#071426; }
  .lms-mockup-line { height:8px; border-radius:6px; background:linear-gradient(90deg,#e2e8f0,#f1f5f9); }
  .lms-mockup-line.short { width:62%; }
  .lms-mockup-check { display:flex; align-items:center; gap:10px; }
  .lms-mockup-box { flex:0 0 14px; width:14px; height:14px; border-radius:4px; background:#ecfeff; border:1.5px solid #14b8a6; display:flex; align-items:center; justify-content:center; color:#0d9488; font-size:10px; font-weight:900; }
  .lms-mockup-foot { margin-top:auto; font-size:10px; font-weight:700; letter-spacing:.16em; text-transform:uppercase; color:#94a3b8; }
  .lms-main { padding:36px 0 52px; }
  .lms-layout { display:grid; grid-template-columns:1fr; gap:16px; }
  .lms-card { border:1px solid #e2e8f0; border-radius:24px; background:#fff; padding:22px; overflow:hidden; }
  .lms-cover { border-radius:18px; overflow:hidden; margin-bottom:18px; max-height:390px; }
  .lms-cover img { width:100%; height:100%; object-fit:cover; display:block; }
  .lms-content { color:#334155; line-height:1.85; }
  .lms-content h2, .lms-content h3 { font-family:"Syne",sans-serif; letter-spacing:-.03em; color:#071426; margin:20px 0 8px; }
  .lms-bullets { margin:22px 0; border:1px solid #99f6e4; border-radius:18px; background:#ecfeff; padding:18px; }
  .lms-bullets h2 { margin:0 0 12px; font-family:"Syne",sans-serif; color:#071426; }
  .lms-bullets ul { margin:0; padding:0; list-style:none; display:grid; gap:10px; }
  .lms-bullets li { position:relative; padding-left:24px; color:#334155; }
  .lms-bullets li::before { content:"✓"; position:absolute; left:0; color:#0d9488; font-weight:900; }
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
  .lms-form-bullets { margin:0 0 16px; padding:14px 16px; border:1px solid #99f6e4; border-radius:14px; background:#ecfeff; }
  .lms-form-bullets-title { margin:0 0 8px; font-size:11px; letter-spacing:.16em; text-transform:uppercase; font-weight:700; color:#0f766e; }
  .lms-form-bullets ul { margin:0; padding:0; list-style:none; display:grid; gap:6px; }
  .lms-form-bullets li { position:relative; padding-left:20px; color:#0f5f56; font-size:13px; line-height:1.55; }
  .lms-form-bullets li::before { content:"✓"; position:absolute; left:0; top:0; color:#0d9488; font-weight:900; }
  .lms-form-trust { margin-top:10px; padding:10px 12px; border:1px dashed #cbd5e1; border-radius:10px; color:#64748b; font-size:11px; line-height:1.55; text-align:center; }
  @media (min-width:761px){ .lms-wrap{width:min(1240px, calc(100% - 48px));} }
  @media (min-width:980px){
    .lms-hero-inner { grid-template-columns:minmax(0, 1.25fr) minmax(0, 0.75fr); padding:72px 0 60px; }
    .lms-hero-visual { display:flex; justify-content:flex-end; align-items:center; }
  }
  @media (min-width:1100px){ .lms-layout{grid-template-columns:minmax(0, 1fr) 350px;align-items:start;} }
</style>

<main class="lms-page">
  <section class="lms-hero">
    <div class="lms-wrap lms-hero-inner">
      <div class="lms-hero-copy">
        <a class="lms-back" href="<?php echo esc_url(upsellio_get_lead_magnets_page_url()); ?>">← Wróć do katalogu materiałów</a>
        <?php if ($badge !== "") : ?><div class="lms-badge"><?php echo esc_html($badge); ?></div><?php endif; ?>
        <?php if ($format_label !== "") : ?>
          <div class="lms-format" aria-label="<?php echo esc_attr($format_label); ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="13" y2="17"/></svg>
            <span><?php echo esc_html($format_label); ?></span>
          </div>
        <?php endif; ?>
        <h1 class="lms-title"><?php echo esc_html($title); ?></h1>
        <?php if ($excerpt !== "") : ?><p class="lms-excerpt"><?php echo esc_html($excerpt); ?></p><?php endif; ?>
      </div>
      <div class="lms-hero-visual" aria-hidden="true">
        <div class="lms-mockup">
          <div class="lms-mockup-head">
            <div class="lms-mockup-brand">Upsellio</div>
            <div class="lms-mockup-tag"><?php echo esc_html($badge !== "" ? $badge : "Materiał"); ?></div>
          </div>
          <div class="lms-mockup-title"><?php echo esc_html(mb_substr($title, 0, 60)); ?></div>
          <div class="lms-mockup-line short"></div>
          <div class="lms-mockup-check"><span class="lms-mockup-box">✓</span><div class="lms-mockup-line" style="flex:1;"></div></div>
          <div class="lms-mockup-check"><span class="lms-mockup-box">✓</span><div class="lms-mockup-line short" style="flex:1;"></div></div>
          <div class="lms-mockup-check"><span class="lms-mockup-box">✓</span><div class="lms-mockup-line" style="flex:1;"></div></div>
          <div class="lms-mockup-check"><span class="lms-mockup-box">✓</span><div class="lms-mockup-line short" style="flex:1;"></div></div>
          <div class="lms-mockup-foot"><?php echo esc_html($format_label !== "" ? $format_label : "Format · PDF"); ?></div>
        </div>
      </div>
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
        <?php if (!empty($bullets)) : ?>
          <section class="lms-bullets">
            <h2>Co znajdziesz w środku:</h2>
            <ul>
              <?php foreach ($bullets as $bullet) : ?>
                <li><?php echo esc_html((string) $bullet); ?></li>
              <?php endforeach; ?>
            </ul>
          </section>
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
        <h2 class="lms-form-title"><?php echo $is_gated ? "Pobierz materiał premium" : "Pobierz materiał bezpłatnie"; ?></h2>
        <p class="lms-form-text"><?php echo esc_html($is_gated ? "Zostaw imię i e-mail. Otrzymasz dostęp do materiału i krótką informację, jak go wykorzystać w praktyce." : "Zostaw imię i e-mail. Materiał trafi prosto do Twojej skrzynki. Bez spamu, wypis jednym kliknięciem."); ?></p>
        <?php if (!empty($bullets)) : ?>
          <div class="lms-form-bullets">
            <div class="lms-form-bullets-title">Co dostaniesz</div>
            <ul>
              <?php foreach (array_slice($bullets, 0, 5) as $bullet) : ?>
                <li><?php echo esc_html((string) $bullet); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
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
          <input type="hidden" name="lead_message" value="<?php echo esc_attr("Pobranie materiału: " . $title); ?>" />
          <input type="text" name="lead_website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;" />
          <?php wp_nonce_field("upsellio_unified_lead_form", "upsellio_lead_form_nonce"); ?>
          <div class="field">
            <label for="lms-name">Imię *</label>
            <input id="lms-name" name="lead_name" type="text" autocomplete="given-name" inputmode="text" required />
          </div>
          <div class="field">
            <label for="lms-email">E-mail *</label>
            <input id="lms-email" name="lead_email" type="email" autocomplete="email" inputmode="email" required />
          </div>
          <div class="field">
            <label style="display:flex;gap:8px;align-items:flex-start;">
              <input type="checkbox" name="lead_consent" value="1" required style="width:auto;min-height:auto;margin-top:3px;" />
              <span>Wyrażam zgodę na kontakt w sprawie pobranego materiału.</span>
            </label>
          </div>
          <button class="lms-submit" type="submit">Pobierz materiał</button>
          <div class="lms-form-trust">Bez spamu. Wypis jednym kliknięciem. Materiał trafi na podany e-mail.</div>
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
