<?php
/*
Template Name: Upsellio - O mnie
Template Post Type: page
*/
if (!defined("ABSPATH")) {
    exit;
}

if (function_exists("upsellio_register_template_seo_head")) {
    upsellio_register_template_seo_head("about");
}

$site_name = get_bloginfo("name");
$about_url = get_permalink();
$offer_url = function_exists("upsellio_get_offer_page_url") ? (string) upsellio_get_offer_page_url() : home_url("/oferta/");
$contact_url = function_exists("upsellio_get_contact_page_url") ? (string) upsellio_get_contact_page_url() : home_url("/kontakt/");
$blog_url = function_exists("upsellio_get_blog_index_url") ? (string) upsellio_get_blog_index_url() : home_url("/blog/");
$portfolio_url = function_exists("upsellio_get_portfolio_page_url") ? (string) upsellio_get_portfolio_page_url() : home_url("/portfolio/");
$marketing_portfolio_url = function_exists("upsellio_get_marketing_portfolio_page_url") ? (string) upsellio_get_marketing_portfolio_page_url() : home_url("/portfolio-marketingowe/");
$contact_phone = function_exists("upsellio_get_contact_phone") ? (string) upsellio_get_contact_phone() : "+48 575 522 595";
$contact_phone_href = preg_replace("/\s+/", "", $contact_phone);
$contact_email = "kontakt@upsellio.pl";
$contact_email_href = function_exists("upsellio_get_mailto_href") ? upsellio_get_mailto_href($contact_email) : ("mailto:" . $contact_email);
$contact_email_display = function_exists("upsellio_obfuscate_email_address") ? upsellio_obfuscate_email_address($contact_email) : $contact_email;
$founder = function_exists("upsellio_get_trust_seo_section") ? upsellio_get_trust_seo_section("founder") : [];
$founder_name = (string) ($founder["name"] ?? "Sebastian Kelm");
$founder_role = "Konsultant marketingu i sprzedaży";
$founder_photo = "";
$hero_portrait_slot = function_exists("upsellio_get_home_media_slot") ? upsellio_get_home_media_slot("hero_portrait") : [];
if (is_array($hero_portrait_slot) && !empty($hero_portrait_slot["attachment_id"])) {
    $founder_photo = (string) wp_get_attachment_image_url((int) $hero_portrait_slot["attachment_id"], "large");
}
if ($founder_photo === "") {
    $founder_photo = (string) ($founder["photo_url"] ?? "");
}
$linkedin_url = trim((string) ($founder["linkedin_url"] ?? "https://www.linkedin.com/in/sebastiankelm/"));
$service_options = [
    "Google Ads",
    "Meta Ads",
    "Strona internetowa / landing",
    "Pakiet kompletny (kampanie + strona)",
    "Audyt obecnych działań",
    "Nie wiem, chcę porozmawiać",
];

add_action("wp_head", static function () use ($about_url, $founder_name, $founder_role, $contact_email, $linkedin_url, $site_name) {
    $schema = [
        "@context" => "https://schema.org",
        "@graph" => [
            [
                "@type" => "Person",
                "name" => $founder_name,
                "jobTitle" => $founder_role,
                "url" => $about_url,
                "email" => $contact_email,
                "sameAs" => [$linkedin_url],
                "worksFor" => [
                    "@type" => "Organization",
                    "name" => $site_name,
                ],
            ],
            [
                "@type" => "BreadcrumbList",
                "itemListElement" => [
                    ["@type" => "ListItem", "position" => 1, "name" => "Strona glowna", "item" => home_url("/")],
                    ["@type" => "ListItem", "position" => 2, "name" => "O mnie", "item" => $about_url],
                ],
            ],
        ],
    ];
    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
}, 2);

get_header();
?>
<style id="upsellio-about-reference-css">
.am-art{--am-ink:#0f1115;--am-text:#3a3d44;--am-muted:#5f636b;--am-teal:#06745f;--am-accent:#0bb39c;--am-border:#e3e6ea;--am-field:#d8dde3;--am-mint:#dff5ef;--am-hero:#effaf7;--am-soft:#f5f6f8;--am-display:"Bricolage Grotesque",sans-serif;font:400 16px/1.6 "DM Sans",system-ui,sans-serif;color:var(--am-ink);background:#fff;overflow-wrap:break-word}
.am-art *,.am-art *::before,.am-art *::after{box-sizing:border-box}
.am-art [id]{scroll-margin-top:90px}
.am-art .am-wrap{width:100%;max-width:1160px;margin-inline:auto;padding-inline:24px}
.am-art .am-eyebrow{display:inline-flex;align-items:center;gap:8px;font-size:14px;font-weight:700;color:var(--am-teal);margin:0 0 12px;letter-spacing:normal;text-transform:none}
.am-art .am-hero .am-eyebrow{background:#fff;border:1px solid #cdeee6;border-radius:999px;padding:6px 14px 6px 10px;margin-bottom:24px}
.am-art .am-hero .am-eyebrow::before{content:"";width:8px;height:8px;border-radius:50%;background:var(--am-accent);flex:0 0 8px}
.am-art .am-h1,.am-art .am-h2,.am-art .am-h3{font-family:var(--am-display);font-weight:800;color:var(--am-ink)}
.am-art .am-h1{font-size:clamp(38px,4.8vw,60px);line-height:1.04;letter-spacing:-.035em;margin:0 0 24px;max-width:22ch}
.am-art .am-h1 span{color:var(--am-teal)}
.am-art .am-h2{font-size:clamp(30px,3.4vw,42px);line-height:1.08;letter-spacing:-.03em;margin:0 0 32px;max-width:25ch}
.am-art .am-h3{font-size:22px;line-height:1.2;letter-spacing:-.02em;margin:0 0 12px}
.am-art .am-lead{font-size:18px;line-height:1.65;color:var(--am-text);max-width:58ch;margin:0 0 20px}
.am-art .am-body{font-size:16px;line-height:1.7;color:var(--am-text);max-width:70ch;margin:0}
.am-art .am-section{padding:96px 0;background:#fff;border:0}
.am-art .am-section.am-process{background:var(--am-soft)}
.am-art .am-hero{padding:72px 0 88px;background:var(--am-hero)}
.am-art .am-hero-grid{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(0,.85fr);gap:64px;align-items:center}
.am-art .am-hero-portrait{margin:0;min-width:0}
.am-art .am-hero-photo{position:relative;aspect-ratio:4/5;border-radius:28px;overflow:hidden;background:var(--am-mint);border:1px solid #cdeee6}
.am-art .am-hero-photo img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center}
.am-art .am-photo-label{position:absolute;inset:0;display:grid;place-items:center;font:800 64px/1 var(--am-display);color:var(--am-teal)}
.am-art .am-portrait-caption{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;padding-top:16px;color:var(--am-muted);font-size:14px}
.am-art .am-portrait-caption strong{display:block;color:var(--am-ink);font-size:16px}
.am-art .am-portrait-caption a{font-weight:700;color:var(--am-teal)}
.am-art .am-proof{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-top:32px;padding-top:24px;border-top:1px solid #cdeee6}
.am-art .am-proof div{min-width:0}
.am-art .am-proof strong{display:block;font-family:var(--am-display);font-weight:800;color:var(--am-teal);font-size:clamp(19px,2vw,28px);line-height:1.15;letter-spacing:-.02em}
.am-art .am-proof span{display:block;margin-top:6px;font-size:13px;color:var(--am-muted)}
.am-art .am-btn-row{display:flex;flex-wrap:wrap;gap:12px;margin-top:28px}
.am-art .am-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border-radius:999px;min-height:54px;padding:12px 24px;font-weight:700;font-size:16px;line-height:1.4;text-decoration:none;border:1.5px solid transparent;text-align:center;transition:background .18s,transform .18s,border-color .18s}
.am-art .am-btn:hover{transform:translateY(-1px)}
.am-art .am-btn-primary{background:var(--am-teal);color:#fff;border-color:var(--am-teal)}
.am-art .am-btn-primary:hover{background:#055e4d;color:#fff;border-color:#055e4d}
.am-art .am-btn-ghost{background:#fff;color:var(--am-ink);border-color:var(--am-field)}
.am-art .am-btn-ghost:hover{border-color:var(--am-accent);color:var(--am-teal)}
.am-art .am-grid-3{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px}
.am-art .am-card{min-width:0;background:#fff;border:1px solid var(--am-border);border-radius:20px;padding:28px;transition:box-shadow .2s,border-color .2s,transform .2s}
.am-art .am-card:hover{border-color:#cdeee6;box-shadow:0 12px 28px rgba(15,17,21,.06);transform:translateY(-2px)}
.am-art .am-card-icon{display:grid;place-items:center;width:48px;height:48px;border-radius:14px;background:var(--am-mint);color:var(--am-teal);margin-bottom:20px;font:800 20px/1 var(--am-display)}
.am-art .am-card-icon svg{width:24px;height:24px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.am-art .am-list{list-style:none;padding:0;margin:16px 0 0;display:grid;gap:10px}
.am-art .am-list li{position:relative;padding-left:24px;font-size:15px;color:var(--am-text)}
.am-art .am-list li::before{content:"✓";position:absolute;left:0;color:var(--am-teal);font-weight:800}
.am-art .am-service-link{display:inline-flex;gap:6px;margin-top:22px;color:var(--am-teal);font-size:15px;font-weight:700;text-decoration:none}
.am-art .am-service-link:hover{text-decoration:underline}
.am-art .am-faq{display:grid;gap:0;max-width:800px;margin-inline:auto;border-top:1px solid var(--am-border)}
.am-art .am-faq details{background:#fff;border:0;border-bottom:1px solid var(--am-border);padding:0;border-radius:0}
.am-art .am-faq summary{display:flex;align-items:center;justify-content:space-between;gap:16px;cursor:pointer;font-size:18px;font-weight:700;list-style:none;padding:20px 0;line-height:1.45}
.am-art .am-faq summary::-webkit-details-marker{display:none}
.am-art .am-faq summary::after{content:"+";display:grid;place-items:center;width:34px;height:34px;flex:0 0 34px;border-radius:50%;background:var(--am-soft);font:400 24px/1 "DM Sans",sans-serif;color:var(--am-teal);transition:transform .2s,background .2s}
.am-art .am-faq details[open] summary::after{transform:rotate(45deg);background:var(--am-mint)}
.am-art .am-faq p{margin:0 48px 22px 0;color:var(--am-text);font-size:16px;line-height:1.7}
.am-art .am-faq-title{text-align:center;margin-bottom:36px}
.am-art .am-faq-title .am-h2{margin:0 auto;max-width:none}
.am-art .am-cta{background:var(--am-ink);color:#fff;padding:88px 0}
.am-art .am-cta-inner{display:flex;align-items:center;justify-content:space-between;gap:32px;flex-wrap:wrap}
.am-art .am-cta-inner>div:first-child{flex:1 1 500px;min-width:0}
.am-art .am-cta .am-eyebrow{color:#7fe3d2}
.am-art .am-cta .am-h2{color:#fff;max-width:22ch;margin:0 0 12px}
.am-art .am-cta p{color:#c4c8ce;max-width:58ch;margin:0;font-size:18px}
.am-art .am-cta .am-btn-row{flex:1 1 240px;max-width:360px;flex-direction:column;align-items:flex-start;margin:0}
.am-art .am-cta .am-btn-primary{background:var(--am-accent);border-color:var(--am-accent);color:var(--am-ink)}
.am-art .am-cta .am-btn-primary:hover{background:#7fe3d2}
.am-art .am-cta .am-btn-ghost{background:transparent;border:0;color:#fff;min-height:36px;padding:0;text-align:left;font-size:17px}
.am-art .am-contact-form{padding:96px 0;background:var(--am-hero)}
.am-art .am-contact-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:64px;align-items:start}
.am-art .am-contact-card{background:#fff;border:1px solid var(--am-border);border-radius:24px;padding:32px;box-shadow:0 24px 48px rgba(15,17,21,.08);min-width:0}
.am-art .am-form-head{display:flex;align-items:center;gap:14px;margin-bottom:24px}
.am-art .am-form-head .am-card-icon{width:44px;height:44px;flex:0 0 44px;margin:0}
.am-art .am-form-head h3{margin:0;font:800 24px/1.15 var(--am-display);letter-spacing:-.02em}
.am-art .am-form-head p{margin:4px 0 0;color:var(--am-muted);font-size:14px}
.am-art .am-contact-card .ups-form{display:flex;flex-direction:column;gap:16px;margin:0;padding:0;border:0;box-shadow:none;background:transparent;min-width:0}
.am-art .am-contact-card .ups-form__row-2{display:grid;grid-template-columns:minmax(0,1fr);gap:16px}
.am-art .am-contact-card .ups-form__row{display:flex;flex-direction:column;gap:6px}
.am-art .am-contact-card .ups-form__label{display:block;margin:0 0 6px;font:600 14px/1.5 "DM Sans",sans-serif;color:var(--am-ink)}
.am-art .am-contact-card .ups-form input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),.am-art .am-contact-card .ups-form select,.am-art .am-contact-card .ups-form textarea{width:100%;min-width:0;min-height:48px;background:#fff;color:var(--am-ink);border:1.5px solid var(--am-field);border-radius:12px;padding:12px 14px;font:400 15px/1.5 "DM Sans",sans-serif;box-shadow:none}
.am-art .am-contact-card .ups-form textarea{min-height:108px;resize:vertical}
.am-art .am-contact-card .ups-form :is(input,select,textarea):focus{outline:none;border-color:var(--am-accent);box-shadow:0 0 0 3px rgba(11,179,156,.18)}
.am-art .am-contact-card .ups-form__submit{display:flex;align-items:center;justify-content:center;width:100%;min-height:54px;border-radius:999px;background:var(--am-teal);color:#fff;border:0;font:700 16px/1.4 "DM Sans",sans-serif;padding:12px 20px;cursor:pointer}
.am-art .am-contact-card .ups-form__submit:hover{background:#055e4d}
.am-art .am-contact-card .ups-form__consent{display:flex;align-items:flex-start;gap:10px;font:400 13px/1.5 "DM Sans",sans-serif;color:var(--am-muted);margin:0}
.am-art .am-contact-card .ups-form__consent input[type="checkbox"]{width:18px;height:18px;flex:0 0 18px;margin:2px 0 0;accent-color:var(--am-accent)}
.am-art .am-contact-card .ups-form__consent a{color:var(--am-teal);text-decoration:underline}
.am-art .am-contact-card .ups-form__fineprint{font-size:13px;text-align:center;color:var(--am-muted);margin:0}
.am-art .am-contact-notes{margin-top:32px;padding-top:24px;border-top:1px solid #cdeee6;display:grid;gap:12px}
.am-art .am-contact-notes a{color:var(--am-teal);font-weight:700}
.am-art :focus-visible{outline:3px solid var(--am-accent);outline-offset:3px}
@media(max-width:979px){.am-art .am-hero-grid,.am-art .am-contact-grid{grid-template-columns:1fr;gap:40px}.am-art .am-hero-portrait{width:min(100%,480px)}.am-art .am-contact-card{max-width:600px;width:100%}.am-art .am-grid-3{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:700px){.am-art .am-section,.am-art .am-contact-form,.am-art .am-cta{padding:64px 0}.am-art .am-hero{padding:48px 0 64px}.am-art .am-wrap{padding-inline:20px}.am-art .am-hero-grid,.am-art .am-contact-grid{gap:32px}.am-art .am-h1{font-size:clamp(36px,8.5vw,46px)}.am-art .am-h2{font-size:32px;margin-bottom:24px}.am-art .am-grid-3{grid-template-columns:1fr;gap:16px}.am-art .am-card,.am-art .am-contact-card{padding:24px}.am-art .am-proof{grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}.am-art .am-proof strong{font-size:19px}.am-art .am-btn-row,.am-art .am-cta .am-btn-row{width:100%;max-width:none}.am-art .am-btn{width:100%}.am-art .am-cta .am-btn-ghost{justify-content:center}.am-art .am-faq summary{font-size:16px}.am-art .am-faq p{margin-right:0}}
@media(max-width:360px){.am-art .am-proof{grid-template-columns:1fr}.am-art .am-contact-card{padding:20px}}
@media(prefers-reduced-motion:reduce){.am-art *{transition:none!important;animation:none!important;transform:none!important}}
</style>

<main class="am-art">
  <section class="am-hero" id="o-mnie">
    <div class="am-wrap am-hero-grid">
      <div>
        <div class="am-eyebrow">O mnie · marketing i sprzedaż</div>
        <h1 class="am-h1"><?php echo esc_html($founder_name); ?><br><span>Łączę marketing z wynikiem sprzedaży.</span></h1>
        <p class="am-lead">Pomagam różnym firmom budować system pozyskiwania klientów oparty o Google Ads, Meta Ads i strony internetowe nastawione na konwersję. Pracuję z biznesem usługowym i produktowym, lokalnym i ogólnopolskim, B2B i B2C oraz e-commerce. Bez przypadkowych działań i bez raportów, które nic nie zmieniają.</p>
        <p class="am-body">Moje podejście wyrasta ze sprzedaży, nie tylko z samej reklamy. Dlatego zaczynam od zrozumienia oferty, procesu sprzedaży i jakości zapytań. Dopiero potem optymalizuję kampanie i stronę tak, aby ruch zamieniał się w realne rozmowy i klientów.</p>
        <div class="am-btn-row">
          <a class="am-btn am-btn-primary" href="#kontakt-o-mnie">Umów bezpłatną diagnozę →</a>
          <a class="am-btn am-btn-ghost" href="<?php echo esc_url($offer_url); ?>">Zobacz ofertę</a>
        </div>
        <div class="am-proof">
          <div><strong>10+</strong><span>lat praktyki sprzedaży i marketingu</span></div>
          <div><strong>Google + Meta</strong><span>kampanie z naciskiem na jakość leadów</span></div>
          <div><strong>SEO + CRO</strong><span>strony, które wspierają decyzję zakupową</span></div>
        </div>
      </div>
      <figure class="am-hero-portrait"><div class="am-hero-photo">
        <?php if ($founder_photo !== "") : ?>
          <img src="<?php echo esc_url($founder_photo); ?>" alt="<?php echo esc_attr($founder_name); ?>" loading="eager" fetchpriority="high" decoding="async" />
        <?php else : ?>
          <div class="am-photo-label" aria-hidden="true">SK</div>
        <?php endif; ?>
      </div><figcaption class="am-portrait-caption"><div><strong><?php echo esc_html($founder_name); ?></strong><?php echo esc_html($founder_role); ?></div><?php if ($linkedin_url !== "") : ?><a href="<?php echo esc_url($linkedin_url); ?>">LinkedIn ↗</a><?php endif; ?></figcaption></figure>
    </div>
  </section>

  <section class="am-section am-process" id="jak-pracuje">
    <div class="am-wrap">
      <div class="am-eyebrow">Jak pracuję</div>
      <h2 class="am-h2">Strategia marketingu i sprzedaży w trzech krokach.</h2>
      <div class="am-grid-3">
        <article class="am-card"><span class="am-card-icon" aria-hidden="true">01</span><h3 class="am-h3">Diagnoza</h3><p class="am-body">Analizuję kampanie, stronę, ofertę i proces obsługi leadów. Szukam największego wąskiego gardła.</p></article>
        <article class="am-card"><span class="am-card-icon" aria-hidden="true">02</span><h3 class="am-h3">Priorytety</h3><p class="am-body">Ustalam plan działań na 30-90 dni: co wdrożyć najpierw, żeby poprawić wynik przy realnym budżecie.</p></article>
        <article class="am-card"><span class="am-card-icon" aria-hidden="true">03</span><h3 class="am-h3">Wdrożenie</h3><p class="am-body">Buduję i optymalizuję kampanie, komunikat i strony docelowe pod konwersję i jakość zapytań.</p></article>
      </div>
    </div>
  </section>

  <section class="am-section" id="specjalizacje">
    <div class="am-wrap">
      <div class="am-eyebrow">Specjalizacje</div>
      <h2 class="am-h2">W czym wspieram różne firmy najczęściej.</h2>
      <p class="am-body" style="margin-bottom:24px">Ten sam system działa w różnych branżach: reklama, strona i pomiar zapytań. Dopasowuję kanał i komunikat do modelu biznesu: usług, produkcji, sklepu albo sprzedaży lokalnej.</p>
      <div class="am-grid-3">
        <article class="am-card">
          <span class="am-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="4"/><path d="M12 4v4m8 4h-4m-4 8v-4m-8-4h4"/></svg></span>
          <h3 class="am-h3">Google Ads dla firm</h3>
          <ul class="am-list">
            <li>kampanie Search i Performance Max</li>
            <li>dobór słów kluczowych pod intencję zakupową</li>
            <li>optymalizacja CPL i jakości leadów</li>
          </ul>
        <a class="am-service-link" href="<?php echo esc_url(home_url('/marketing-google-ads/')); ?>">Zobacz usługę →</a>
        </article>
        <article class="am-card">
          <span class="am-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M7 20h10M8 11l3 3 5-6"/></svg></span>
          <h3 class="am-h3">Meta Ads i lejki reklamowe</h3>
          <ul class="am-list">
            <li>budowa popytu i remarketing</li>
            <li>testy kreacji, copy i segmentów odbiorców</li>
            <li>spójność reklama → landing page</li>
          </ul>
        <a class="am-service-link" href="<?php echo esc_url(home_url('/marketing-meta-ads/')); ?>">Zobacz usługę →</a>
        </article>
        <article class="am-card">
          <span class="am-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 9h18m-13 4-3 2 3 2m8-4 3 2-3 2"/></svg></span>
          <h3 class="am-h3">Strony WWW, SEO i CRO</h3>
          <ul class="am-list">
            <li>struktura treści pod SEO i konwersję</li>
            <li>copywriting sprzedażowy i mocne CTA</li>
            <li>analiza ścieżki użytkownika i formularzy</li>
          </ul>
        <a class="am-service-link" href="<?php echo esc_url(home_url('/tworzenie-stron-internetowych/')); ?>">Zobacz usługę →</a>
        </article>
      </div>
      <div class="am-btn-row">
        <a class="am-btn am-btn-ghost" href="<?php echo esc_url($portfolio_url); ?>">Portfolio stron</a>
        <a class="am-btn am-btn-ghost" href="<?php echo esc_url($marketing_portfolio_url); ?>">Realizacje marketingowe</a>
        <a class="am-btn am-btn-ghost" href="<?php echo esc_url($blog_url); ?>">Artykuły blogowe</a>
      </div>
    </div>
  </section>

  <section class="am-section" id="faq">
    <div class="am-wrap">
      <div class="am-faq-title"><div class="am-eyebrow">FAQ</div>
      <h2 class="am-h2">Najczęstsze pytania o współpracę.</h2></div>
      <div class="am-faq">
        <details><summary>Czy współpraca jest tylko dla dużych budżetów?</summary><p>Nie. Kluczowe jest dopasowanie strategii do etapu firmy i realnych zasobów. Często zaczynamy od uporządkowania jednego obszaru, który blokuje wyniki najbardziej.</p></details>
        <details><summary>Dla jakich firm pracujesz?</summary><p>Dla różnych firm: usługowych i produkcyjnych, lokalnych i ogólnopolskich, B2B i B2C, e-commerce i tradycyjnego biznesu. Ważne, żeby dało się mierzyć zapytania i sprzedaż, nie branża sama w sobie.</p></details>
        <details><summary>Od czego najlepiej zacząć?</summary><p>Najlepiej od krótkiej diagnozy. Wtedy wiadomo, czy najpierw poprawiać kampanie, stronę, ofertę, czy proces kwalifikacji leadów.</p></details>
      </div>
    </div>
  </section>



  <section class="am-contact-form" id="kontakt-o-mnie">
    <div class="am-wrap am-contact-grid">
      <div>
        <div class="am-eyebrow">Formularz kontaktowy</div>
        <h2 class="am-h2">Napisz, co dziś blokuje wyniki. Odezwę się z konkretem.</h2>
        <p class="am-body">Możesz opisać swoją sytuację marketingową, zadać pytanie lub umówić się na rozmowę. Krótka wiadomość wystarczy. Odpowiadam w ciągu 24 godzin roboczych.</p>
      </div>
      <div class="am-contact-card">
        <div class="am-form-head"><span class="am-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5h16v11H8l-4 4z"/></svg></span><div><h3>Bezpłatna diagnoza</h3><p>2 minuty. Odpowiadam osobiście w 24h roboczych.</p></div></div>
        <?php
        if (function_exists("upsellio_render_lead_form")) {
            echo upsellio_render_lead_form([
                "origin" => "about-contact-form",
                "submit_label" => "Wyślij wiadomość →",
                "variant" => "full",
                "heading" => "",
                "subheading" => "",
                "redirect_url" => $about_url . "#kontakt-o-mnie",
                "service_options" => $service_options,
                "css_class" => "am-form",
                "form_id" => "about-contact-form",
            ]);
        } else {
            echo '<p>Napisz na <a href="' . esc_url($contact_email_href) . '">' . esc_html($contact_email_display) . '</a></p>';
        }
        ?>
      </div>
    </div>
  </section>
  <section class="am-cta">
    <div class="am-wrap am-cta-inner">
      <div>
        <div class="am-eyebrow">Kontakt</div>
        <h2 class="am-h2">Sprawdźmy, gdzie uciekają Twoi klienci.</h2>
        <p>30 minut rozmowy. Bez prezentacji i bez zobowiązań.</p>
      </div>
      <div class="am-btn-row">
        <a class="am-btn am-btn-primary" href="#kontakt-o-mnie">Umów bezpłatną diagnozę →</a>
        <a class="am-btn am-btn-ghost" href="<?php echo esc_url("tel:" . $contact_phone_href); ?>"><?php echo esc_html($contact_phone); ?></a>
        <a class="am-btn am-btn-ghost" href="<?php echo esc_url($contact_email_href); ?>"><?php echo esc_html($contact_email_display); ?></a>
      </div>
    </div>
  </section>
</main>
<?php
get_footer();

