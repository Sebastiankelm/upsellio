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
$founder_role = (string) ($founder["role"] ?? "Konsultant marketingu i sprzedaży B2B");
$founder_photo = (string) ($founder["photo_url"] ?? "");
if ($founder_photo === "" && function_exists("upsellio_render_home_media_image")) {
    $founder_photo = "";
}
$linkedin_url = trim((string) ($founder["linkedin_url"] ?? "https://www.linkedin.com/in/sebastiankelm/"));

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
<style>
  .am-art{font-family:"DM Sans",system-ui,sans-serif;color:#0a1410;background:#fafaf7;line-height:1.66}
  .am-art *,.am-art *::before,.am-art *::after{box-sizing:border-box}
  .am-wrap{width:min(1180px,100% - 64px);margin-inline:auto}
  .am-eyebrow{display:inline-flex;align-items:center;gap:10px;font-size:11px;font-weight:700;letter-spacing:1.6px;text-transform:uppercase;color:#0d9488;margin-bottom:14px}
  .am-eyebrow::before{content:"";width:26px;height:2px;background:#0d9488;border-radius:99px}
  .am-h1{font-family:"Syne",sans-serif;font-weight:700;font-size:clamp(42px,5vw,68px);line-height:1.02;letter-spacing:-2px;margin:0 0 20px;max-width:18ch}
  .am-h2{font-family:"Syne",sans-serif;font-weight:700;font-size:clamp(30px,3.6vw,48px);line-height:1.06;letter-spacing:-1.4px;margin:0 0 14px;max-width:22ch}
  .am-h3{font-family:"Syne",sans-serif;font-weight:700;font-size:22px;line-height:1.2;letter-spacing:-.4px;margin:0 0 10px}
  .am-lead{font-size:18px;line-height:1.65;color:#3d3d38;max-width:64ch;margin:0 0 30px}
  .am-body{font-size:15px;color:#3d3d38;line-height:1.8;max-width:78ch}
  .am-section{padding:96px 0;border-bottom:1px solid #e7e7e1}
  .am-hero{padding:96px 0 78px;background:radial-gradient(circle at 90% 0%,rgba(13,148,136,.1),transparent 42%)}
  .am-hero-grid{display:grid;grid-template-columns:1.05fr .95fr;gap:42px;align-items:center}
  .am-hero-photo{position:relative;aspect-ratio:.86;border-radius:24px;overflow:hidden;background:#dff8f4;border:1px solid #99f6e4}
  .am-hero-photo img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
  .am-photo-stripes{position:absolute;inset:0;background-image:repeating-linear-gradient(135deg,rgba(13,148,136,.12) 0 12px,transparent 12px 24px)}
  .am-photo-label{position:absolute;inset:0;display:grid;place-items:center;font-family:ui-monospace,monospace;color:#0f766e;font-size:12px;letter-spacing:.8px;text-align:center;padding:0 16px}
  .am-proof{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-top:26px}
  .am-proof div{background:#fff;border:1px solid #e7e7e1;border-radius:16px;padding:18px}
  .am-proof strong{display:block;font-family:"Syne",sans-serif;color:#0d9488;font-size:30px;line-height:1}
  .am-proof span{display:block;margin-top:6px;font-size:13px;color:#5e5e56}
  .am-btn-row{display:flex;flex-wrap:wrap;gap:12px;margin-top:28px}
  .am-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border-radius:999px;min-height:48px;padding:0 24px;font-weight:700;font-size:15px;text-decoration:none;border:1px solid transparent}
  .am-btn-primary{background:#0d9488;color:#fff}
  .am-btn-ghost{background:#fff;color:#0a1410;border-color:#e7e7e1}
  .am-grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
  .am-card{background:#fff;border:1px solid #e7e7e1;border-radius:20px;padding:24px}
  .am-list{list-style:none;padding:0;margin:14px 0 0;display:grid;gap:8px}
  .am-list li{position:relative;padding-left:18px;font-size:14px;color:#3d3d38}
  .am-list li::before{content:"✓";position:absolute;left:0;color:#0d9488;font-weight:800}
  .am-faq{display:grid;gap:12px;max-width:900px}
  .am-faq details{background:#fff;border:1px solid #e7e7e1;border-radius:14px;padding:16px 18px}
  .am-faq summary{cursor:pointer;font-weight:700}
  .am-faq p{margin:10px 0 0;color:#3d3d38;font-size:15px;line-height:1.75}
  .am-cta{background:#0a1410;color:#fff;padding:72px 0;position:relative;overflow:hidden}
  .am-cta::before{content:"";position:absolute;width:560px;height:560px;border-radius:50%;background:radial-gradient(circle,rgba(20,184,166,.2),transparent 66%);right:-220px;top:-260px;pointer-events:none}
  .am-cta-inner{position:relative;display:grid;grid-template-columns:1fr auto;gap:28px;align-items:center}
  .am-cta .am-h2{color:#fff;max-width:16ch}
  .am-cta p{color:rgba(255,255,255,.72);max-width:64ch}
  @media(max-width:980px){.am-hero-grid,.am-grid-3,.am-cta-inner,.am-proof{grid-template-columns:1fr}}
  @media(max-width:700px){.am-wrap{width:min(1180px,100% - 28px)}.am-btn{width:100%}}
</style>

<main class="am-art">
  <section class="am-hero">
    <div class="am-wrap am-hero-grid">
      <div>
        <div class="am-eyebrow">O mnie · marketing B2B</div>
        <h1 class="am-h1"><?php echo esc_html($founder_name); ?> - łączę marketing z wynikiem sprzedaży.</h1>
        <p class="am-lead">Pomagam firmom usługowym i B2B budować system pozyskiwania klientów oparty o Google Ads, Meta Ads i strony internetowe nastawione na konwersję. Bez przypadkowych działań i bez raportów, które nic nie zmieniają.</p>
        <p class="am-body">Moje podejście wyrasta ze sprzedaży, nie tylko z samej reklamy. Dlatego zaczynam od zrozumienia oferty, procesu handlowego i jakości leadów. Dopiero potem optymalizuję kampanie i stronę tak, aby ruch zamieniał się w realne rozmowy handlowe i klientów.</p>
        <div class="am-btn-row">
          <a class="am-btn am-btn-primary" href="<?php echo esc_url($contact_url); ?>">Umów rozmowę strategiczną →</a>
          <a class="am-btn am-btn-ghost" href="<?php echo esc_url($offer_url); ?>">Zobacz ofertę</a>
        </div>
        <div class="am-proof">
          <div><strong>10+</strong><span>lat praktyki sprzedaży i marketingu B2B</span></div>
          <div><strong>Google + Meta</strong><span>kampanie z naciskiem na jakość leadów</span></div>
          <div><strong>SEO + CRO</strong><span>strony, które wspierają decyzję zakupową</span></div>
        </div>
      </div>
      <aside class="am-hero-photo" aria-label="Zdjęcie konsultanta">
        <?php if ($founder_photo !== "") : ?>
          <img src="<?php echo esc_url($founder_photo); ?>" alt="<?php echo esc_attr($founder_name); ?>" loading="lazy" decoding="async" />
        <?php else : ?>
          <div class="am-photo-stripes"></div>
          <div class="am-photo-label">[ <?php echo esc_html($founder_name); ?> ]</div>
        <?php endif; ?>
      </aside>
    </div>
  </section>

  <section class="am-section">
    <div class="am-wrap">
      <div class="am-eyebrow">Jak pracuję</div>
      <h2 class="am-h2">Strategia marketingu i sprzedaży w czterech krokach.</h2>
      <div class="am-grid-3">
        <article class="am-card"><h3 class="am-h3">1. Diagnoza</h3><p class="am-body">Analizuję kampanie, stronę, ofertę i proces obsługi leadów. Szukam największego wąskiego gardła.</p></article>
        <article class="am-card"><h3 class="am-h3">2. Priorytety</h3><p class="am-body">Ustalam plan działań na 30-90 dni: co wdrożyć najpierw, żeby poprawić wynik przy realnym budżecie.</p></article>
        <article class="am-card"><h3 class="am-h3">3. Wdrożenie</h3><p class="am-body">Buduję i optymalizuję kampanie, komunikat i strony docelowe pod konwersję i jakość zapytań.</p></article>
      </div>
    </div>
  </section>

  <section class="am-section">
    <div class="am-wrap">
      <div class="am-eyebrow">Specjalizacje</div>
      <h2 class="am-h2">W czym wspieram firmy najczęściej.</h2>
      <div class="am-grid-3">
        <article class="am-card">
          <h3 class="am-h3">Google Ads dla firm B2B</h3>
          <ul class="am-list">
            <li>kampanie Search i Performance Max</li>
            <li>dobór słów kluczowych pod intencję zakupową</li>
            <li>optymalizacja CPL i jakości leadów</li>
          </ul>
        </article>
        <article class="am-card">
          <h3 class="am-h3">Meta Ads i lejki reklamowe</h3>
          <ul class="am-list">
            <li>budowa popytu i remarketing</li>
            <li>testy kreacji, copy i segmentów odbiorców</li>
            <li>spójność reklama → landing page</li>
          </ul>
        </article>
        <article class="am-card">
          <h3 class="am-h3">Strony WWW, SEO i CRO</h3>
          <ul class="am-list">
            <li>struktura treści pod SEO i konwersję</li>
            <li>copywriting sprzedażowy i mocne CTA</li>
            <li>analiza ścieżki użytkownika i formularzy</li>
          </ul>
        </article>
      </div>
      <div class="am-btn-row">
        <a class="am-btn am-btn-ghost" href="<?php echo esc_url($portfolio_url); ?>">Portfolio stron</a>
        <a class="am-btn am-btn-ghost" href="<?php echo esc_url($marketing_portfolio_url); ?>">Case studies marketingowe</a>
        <a class="am-btn am-btn-ghost" href="<?php echo esc_url($blog_url); ?>">Artykuły blogowe</a>
      </div>
    </div>
  </section>

  <section class="am-section">
    <div class="am-wrap">
      <div class="am-eyebrow">FAQ</div>
      <h2 class="am-h2">Najczęstsze pytania o współpracę.</h2>
      <div class="am-faq">
        <details><summary>Czy współpraca jest tylko dla dużych budżetów?</summary><p>Nie. Kluczowe jest dopasowanie strategii do etapu firmy i realnych zasobów. Często zaczynamy od uporządkowania jednego obszaru, który blokuje wyniki najbardziej.</p></details>
        <details><summary>Czy pracujesz także z firmami usługowymi lokalnie?</summary><p>Tak. Wspieram zarówno firmy lokalne, jak i marki działające ogólnopolsko w modelu B2B i e-commerce.</p></details>
        <details><summary>Od czego najlepiej zacząć?</summary><p>Najlepiej od krótkiej diagnozy. Wtedy wiadomo, czy najpierw poprawiać kampanie, stronę, ofertę, czy proces kwalifikacji leadów.</p></details>
      </div>
    </div>
  </section>

  <section class="am-cta">
    <div class="am-wrap am-cta-inner">
      <div>
        <div class="am-eyebrow">Kontakt</div>
        <h2 class="am-h2">Sprawdźmy, co realnie blokuje wzrost w Twoim marketingu.</h2>
        <p>Napisz lub zadzwoń. Otrzymasz konkretny kierunek działań, bez ogólników i bez presji sprzedażowej.</p>
      </div>
      <div class="am-btn-row">
        <a class="am-btn am-btn-primary" href="<?php echo esc_url($contact_url); ?>">Formularz kontaktowy</a>
        <a class="am-btn am-btn-ghost" href="<?php echo esc_url("tel:" . $contact_phone_href); ?>"><?php echo esc_html($contact_phone); ?></a>
        <a class="am-btn am-btn-ghost" href="<?php echo esc_url($contact_email_href); ?>"><?php echo esc_html($contact_email_display); ?></a>
      </div>
    </div>
  </section>
</main>
<?php
get_footer();

