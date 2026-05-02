<?php
/*
Template Name: Upsellio - Kontakt
Template Post Type: page
*/
if (!defined("ABSPATH")) {
    exit;
}

get_header();

$front_page_sections = function_exists("upsellio_get_front_page_content_config")
    ? upsellio_get_front_page_content_config()
    : [];
$contact_service_options = isset($front_page_sections["contact_service_options"]) && is_array($front_page_sections["contact_service_options"])
    ? $front_page_sections["contact_service_options"]
    : [];
$contact_phone = function_exists("upsellio_get_contact_phone")
    ? upsellio_get_contact_phone()
    : trim((string) ($front_page_sections["contact_phone"] ?? ""));
$contact_email = trim((string) ($front_page_sections["contact_email"] ?? "kontakt@upsellio.pl"));
$contact_page_url = home_url("/kontakt/");
$contact_email_href = function_exists("upsellio_get_mailto_href") ? upsellio_get_mailto_href($contact_email) : ("mailto:" . $contact_email);
$contact_email_display = function_exists("upsellio_obfuscate_email_address") ? upsellio_obfuscate_email_address($contact_email) : $contact_email;
$contact_service_options = [
    "Kampanie Meta Ads",
    "Kampanie Google Ads",
    "Tworzenie strony lub landing page",
    "Marketing + strona (oba)",
    "Nie wiem — chcę porozmawiać",
];
$contact_founder = function_exists("upsellio_get_trust_seo_section") ? upsellio_get_trust_seo_section("founder") : [];
$contact_founder_name = (string) ($contact_founder["name"] ?? "Sebastian Kelm");
$contact_founder_role = (string) ($contact_founder["role"] ?? "Growth marketer B2B");
$contact_founder_photo = (string) ($contact_founder["photo_url"] ?? "");
$contact_founder_initials = "";
if ($contact_founder_name !== "") {
    $name_parts = preg_split("/\s+/", trim($contact_founder_name));
    foreach ((array) $name_parts as $part) {
        if ($part === "" || mb_strlen($contact_founder_initials) >= 2) {
            continue;
        }
        $contact_founder_initials .= mb_strtoupper(mb_substr($part, 0, 1));
    }
}
$contact_faq_items = [
    [
        "question" => "Jak szybko wracasz z odpowiedzią?",
        "answer" => "Zazwyczaj do końca kolejnego dnia roboczego, często szybciej, jeśli opis sytuacji jest konkretny i nie mam dodatkowych pytań. Jeśli sprawa jest pilna, napisz to wprost w formularzu.",
    ],
    [
        "question" => "Czy muszę mieć gotowy budżet, zanim się skontaktuję?",
        "answer" => "Nie. Najpierw ustalamy, co realnie warto zrobić i jaki zakres ma sens w Twojej sytuacji. O budżecie rozmawiamy dopiero wtedy, gdy wiemy, co ma być robione i po co.",
    ],
    [
        "question" => "Czy to kontakt tylko dla nowych klientów?",
        "answer" => "Nie. Formularz jest również dla firm, które już prowadzą kampanie reklamowe albo mają stronę i chcą je poprawić. Audyt kampanii i analiza konwersji strony to częsty powód kontaktu.",
    ],
    [
        "question" => "Czy po wysłaniu formularza dostanę automatyczną ofertę?",
        "answer" => "Nie. Po wysłaniu formularza przeglądam opisaną sytuację i wracam z personalną odpowiedzią. Celem pierwszego kontaktu jest zrozumienie problemu, a nie wysłanie szablonowego cennika.",
    ],
    [
        "question" => "Z kim będę rozmawiać?",
        "answer" => "Bezpośrednio ze mną, Sebastianem Kelmem. Nie ma asystenta, który zbiera briefy i przekazuje dalej. Od pierwszej wiadomości masz jeden punkt kontaktu.",
    ],
    [
        "question" => "Czy współpraca jest możliwa zdalnie, z dowolnego miasta Polski?",
        "answer" => "Tak. Cała współpraca może odbywać się zdalnie przez e-mail, wideokonferencje i współdzielone narzędzia. Lokalizacja firmy nie ma znaczenia dla zakresu i jakości współpracy.",
    ],
];
?>
<style>
  .ct-art{font-family:"DM Sans",system-ui,sans-serif;color:#0a1410;background:#fafaf7;line-height:1.65}
  .ct-art *,.ct-art *::before,.ct-art *::after{box-sizing:border-box}
  .ct-wrap{width:min(1180px,100% - 64px);margin-inline:auto}
  .ct-eyebrow{display:inline-flex;align-items:center;gap:10px;font-size:11px;font-weight:700;letter-spacing:1.6px;text-transform:uppercase;color:#0d9488;margin-bottom:14px}
  .ct-eyebrow::before{content:"";width:26px;height:2px;background:#0d9488;border-radius:99px}
  .ct-h1{font-family:"Syne",sans-serif;font-weight:700;font-size:clamp(38px,4.4vw,60px);line-height:1.02;letter-spacing:-1.8px;margin:0 0 20px;max-width:20ch}
  .ct-h2{font-family:"Syne",sans-serif;font-weight:700;font-size:clamp(24px,2.8vw,36px);line-height:1.05;letter-spacing:-1.2px;margin:0 0 8px}
  .ct-lead{font-size:18px;line-height:1.6;color:#3d3d38;max-width:60ch;margin:0}
  .ct-divider{height:1px;background:#e7e7e1;margin:32px 0 48px}
  .ct-hero{padding:96px 0 56px;background:radial-gradient(circle at 90% 0%,rgba(13,148,136,.1),transparent 40%)}
  .ct-section{padding:64px 0 128px}
  .ct-faq{padding:0 0 128px}
  .ct-sec-head{max-width:780px}
  .ct-grid{display:grid;grid-template-columns:.85fr 1.15fr;gap:48px;align-items:start}
  .ct-side{display:flex;flex-direction:column;gap:24px}
  .ct-side-head{display:flex;align-items:center;gap:14px;padding:18px;background:#fff;border:1px solid #e7e7e1;border-radius:18px}
  .ct-photo{position:relative;width:56px;height:56px;border-radius:50%;background:#dff8f4;border:1px solid #99f6e4;overflow:hidden;flex:0 0 56px}
  .ct-photo img{width:100%;height:100%;object-fit:cover}
  .ct-photo-stripes{position:absolute;inset:0;background-image:repeating-linear-gradient(135deg,rgba(13,148,136,.18) 0 6px,transparent 6px 12px)}
  .ct-photo-label{position:absolute;inset:0;display:grid;place-items:center;font-family:"Syne",sans-serif;font-weight:800;color:#0f766e;font-size:18px}
  .ct-side-head strong{display:block;font-family:"Syne",sans-serif;font-size:17px;font-weight:700}
  .ct-side-head span{display:block;font-size:13px;color:#7c7c74;margin-top:2px}
  .ct-channels{display:grid;gap:10px}
  .ct-ch{display:flex;align-items:center;gap:14px;padding:16px 18px;background:#fff;border:1px solid #e7e7e1;border-radius:14px;text-decoration:none;color:inherit;transition:.2s ease}
  .ct-ch:hover{border-color:#99f6e4;background:#fafaf7}
  .ct-ch i{flex:0 0 38px;width:38px;height:38px;border-radius:12px;background:#ccfbf1;color:#0f766e;display:grid;place-items:center;font-style:normal;font-size:15px;font-weight:700}
  .ct-ch strong{display:block;font-size:14.5px;font-weight:700}
  .ct-ch span{display:block;font-size:12.5px;color:#7c7c74;margin-top:1px}
  .ct-promise{padding:24px;background:#0a1410;color:#fff;border-radius:18px;position:relative;overflow:hidden}
  .ct-promise::before{content:"";position:absolute;width:240px;height:240px;border-radius:50%;background:radial-gradient(circle,rgba(20,184,166,.2),transparent 65%);right:-80px;top:-80px;pointer-events:none}
  .ct-promise .ct-eyebrow{color:#5eead4;position:relative}
  .ct-promise .ct-eyebrow::before{background:#5eead4}
  .ct-promise-list{list-style:none;padding:0;margin:8px 0 0;position:relative;display:grid;gap:8px}
  .ct-promise-list li{font-size:13.5px;color:rgba(255,255,255,.78);padding-left:18px;position:relative}
  .ct-promise-list li::before{content:"✓";position:absolute;left:0;color:#5eead4;font-weight:900}
  .ct-form{background:#fff;border:1px solid #e7e7e1;border-radius:24px;padding:40px;box-shadow:0 24px 60px rgba(15,23,42,.06)}
  .ct-form-head{margin-bottom:24px}
  .ct-form-head p{margin:0;font-size:14.5px;color:#7c7c74}
  .ct-form label{display:block;font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:#7c7c74;margin:14px 0 6px}
  .ct-form input[type=text],.ct-form input[type=email],.ct-form input[type=tel],.ct-form textarea,.ct-form select{display:block;width:100%;border:1.5px solid #e7e7e1;background:#fafaf7;border-radius:12px;padding:13px 14px;font:inherit;outline:none;color:#0a1410;box-sizing:border-box}
  .ct-form input:focus,.ct-form textarea:focus,.ct-form select:focus{border-color:#0d9488;background:#fff;box-shadow:0 0 0 4px rgba(13,148,136,.1)}
  .ct-form textarea{min-height:120px;resize:vertical}
  .ct-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
  .ct-chips{display:flex;flex-wrap:wrap;gap:8px;margin:6px 0 0}
  .ct-chip{padding:8px 14px;border-radius:999px;border:1px solid #e7e7e1;background:#fafaf7;font-size:13px;font-weight:600;color:#3d3d38}
  .ct-chip.is-active{background:#0a1410;color:#fff;border-color:#0a1410}
  .ct-consent{display:flex !important;gap:10px;align-items:flex-start;text-transform:none !important;letter-spacing:0 !important;font-size:13px !important;color:#7c7c74 !important;font-weight:400 !important;line-height:1.55;margin-top:18px !important}
  .ct-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border-radius:999px;padding:15px 24px;font-weight:700;font-size:15px;border:0;cursor:pointer;font-family:inherit;width:100%;margin-top:18px}
  .ct-btn-primary{background:#0d9488;color:#fff}
  .ct-fineprint{font-size:12.5px;color:#7c7c74;margin:12px 0 0;text-align:center}
  .ct-faq-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
  .ct-faq-item{background:#fff;border:1px solid #e7e7e1;border-radius:14px}
  .ct-faq-item summary{list-style:none;cursor:pointer;display:flex;justify-content:space-between;align-items:center;gap:14px;padding:18px 22px;font-family:"Syne",sans-serif;font-size:15.5px;font-weight:700}
  .ct-faq-item summary::-webkit-details-marker{display:none}
  .ct-faq-icon{width:24px;height:24px;border-radius:50%;background:#fafaf7;border:1px solid #e7e7e1;display:grid;place-items:center;font-size:15px;color:#0d9488;flex:0 0 24px}
  .ct-faq-item[open] .ct-faq-icon{transform:rotate(45deg);background:#ccfbf1;border-color:#99f6e4}
  .ct-faq-item p{margin:0;padding:0 22px 18px;color:#3d3d38;font-size:14px;line-height:1.6}
  @media (max-width:980px){.ct-wrap{width:min(1180px,100% - 40px)}.ct-grid{grid-template-columns:1fr}}
  @media (max-width:760px){.ct-wrap{width:min(1180px,100% - 24px)}.ct-row,.ct-faq-grid{grid-template-columns:1fr}.ct-form{padding:24px}}
</style>

<script type="application/ld+json">
<?php
echo wp_json_encode([
    "@context" => "https://schema.org",
    "@type" => "ContactPage",
    "name" => "Kontakt Upsellio",
    "url" => $contact_page_url,
    "description" => "Kontakt i bezpłatna diagnoza marketingu B2B, kampanii Meta Ads, Google Ads oraz stron internetowych.",
    "mainEntity" => [
        "@type" => "LocalBusiness",
        "name" => "Upsellio",
        "url" => home_url("/"),
        "email" => $contact_email,
        "telephone" => $contact_phone,
        "areaServed" => "Polska",
        "availableLanguage" => "Polish",
        "founder" => [
            "@type" => "Person",
            "name" => "Sebastian Kelm",
            "jobTitle" => "Specjalista ds. marketingu B2B",
            "sameAs" => "https://www.linkedin.com/in/sebastiankelm/",
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
?>
</script>

<main class="ct-art">
  <section class="ct-hero">
    <div class="ct-wrap">
      <div class="ct-eyebrow">Kontakt</div>
      <h1 class="ct-h1">Sprawdźmy, co blokuje sprzedaż z Twojej strony lub reklam.</h1>
      <p class="ct-lead">
        Krótka rozmowa, konkretny kierunek. Bez ofert szablonowych i bez zobowiązań.
        Odpisuję zwykle w ciągu 24 godzin w dni robocze.
      </p>
    </div>
  </section>

  <section class="ct-section" id="formularz-kontaktowy">
    <div class="ct-wrap ct-grid">
      <aside class="ct-side">
        <div class="ct-side-head">
          <div class="ct-photo" aria-hidden="true">
            <?php if ($contact_founder_photo !== "") : ?>
              <img src="<?php echo esc_url($contact_founder_photo); ?>" alt="<?php echo esc_attr($contact_founder_name); ?>" width="56" height="56" loading="lazy" decoding="async" />
            <?php else : ?>
              <div class="ct-photo-stripes"></div>
              <div class="ct-photo-label"><?php echo esc_html($contact_founder_initials !== "" ? $contact_founder_initials : "SK"); ?></div>
            <?php endif; ?>
          </div>
          <div>
            <strong><?php echo esc_html($contact_founder_name); ?></strong>
            <span><?php echo esc_html($contact_founder_role); ?></span>
          </div>
        </div>

        <div class="ct-channels">
          <a href="<?php echo esc_url($contact_email_href); ?>" class="ct-ch">
            <i>@</i>
            <div>
              <strong><?php echo esc_html($contact_email_display); ?></strong>
              <span>Najszybsza droga kontaktu</span>
            </div>
          </a>
          <a href="<?php echo esc_url("tel:" . preg_replace("/\s+/", "", $contact_phone)); ?>" class="ct-ch">
            <i>T</i>
            <div>
              <strong><?php echo esc_html($contact_phone); ?></strong>
              <span>pon-pt 9:00-17:00</span>
            </div>
          </a>
          <a href="https://www.linkedin.com/in/sebastiankelm/" class="ct-ch" target="_blank" rel="noopener">
            <i>in</i>
            <div>
              <strong>LinkedIn</strong>
              <span>linkedin.com/in/sebastiankelm</span>
            </div>
          </a>
        </div>

        <div class="ct-promise">
          <div class="ct-eyebrow">Co dostajesz</div>
          <ul class="ct-promise-list">
            <li>15 minut bezpłatnej rozmowy</li>
            <li>Konkretny kierunek - co poprawić najpierw</li>
            <li>Brak presji na sprzedaż usług</li>
            <li>Rozmawiasz bezpośrednio ze mną, nie z handlowcem</li>
          </ul>
        </div>
      </aside>

      <form class="ct-form" method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" data-upsellio-lead-form="1" data-upsellio-server-form="1">
        <input type="hidden" name="action" value="upsellio_submit_lead" />
        <input type="hidden" name="redirect_url" value="<?php echo esc_url($contact_page_url); ?>" />
        <input type="hidden" name="lead_form_origin" value="contact-page-form" />
        <input type="hidden" name="lead_source" value="contact-page-form" />
        <input type="hidden" name="utm_source" data-ups-utm="source" value="" />
        <input type="hidden" name="utm_medium" data-ups-utm="medium" value="" />
        <input type="hidden" name="utm_campaign" data-ups-utm="campaign" value="" />
        <input type="hidden" name="landing_url" data-ups-context="landing" value="" />
        <input type="hidden" name="referrer" data-ups-context="referrer" value="" />
        <input type="text" name="lead_website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;" />
        <?php wp_nonce_field("upsellio_unified_lead_form", "upsellio_lead_form_nonce"); ?>

        <div class="ct-form-head">
          <h2 class="ct-h2">Zamów bezpłatną diagnozę</h2>
          <p>Krótko opisz, co chcesz poprawić. Odezwę się z kierunkiem.</p>
        </div>
        <?php $ups_form_status = isset($_GET["ups_lead_status"]) ? sanitize_text_field(wp_unslash($_GET["ups_lead_status"])) : ""; ?>
        <?php if ($ups_form_status === "success") : ?>
          <p style="margin:0 0 10px;color:#0f766e;">Dziękuję! Formularz dotarł i wrócę z odpowiedzią.</p>
        <?php elseif ($ups_form_status === "error") : ?>
          <p style="margin:0 0 10px;color:#b13a3a;">Nie udało się wysłać formularza. Spróbuj ponownie.</p>
        <?php endif; ?>
        <div class="ct-row">
          <label>Imię
            <input type="text" name="lead_name" placeholder="Sebastian" required />
          </label>
          <label>Firma
            <input type="text" name="lead_company" placeholder="Nazwa firmy" />
          </label>
        </div>
        <label>E-mail firmowy
          <input type="email" name="lead_email" placeholder="kontakt@firma.pl" required />
        </label>
        <label>Telefon (opcjonalnie)
          <input type="tel" name="lead_phone" placeholder="+48..." />
        </label>
        <label>Czego szukasz?
          <select name="lead_service">
            <option value="">Wybierz</option>
            <?php foreach ($contact_service_options as $service_option) : ?>
              <option value="<?php echo esc_attr((string) $service_option); ?>"><?php echo esc_html((string) $service_option); ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>Orientacyjny budżet (opcjonalnie)
          <select name="lead_budget">
            <option value="">Wybierz lub pomiń</option>
            <option value="do 2000 zł">do 2000 zł</option>
            <option value="2000–5000 zł">2000–5000 zł</option>
            <option value="5000–10000 zł">5000–10 000 zł</option>
            <option value="powyżej 10000 zł">powyżej 10 000 zł</option>
            <option value="nie wiem">nie wiem</option>
          </select>
        </label>
        <label>Wiadomość
          <textarea name="lead_message" placeholder="Krótko opisz sytuację: co działa, co nie działa, jaki jest cel." required></textarea>
        </label>
        <label class="ct-consent">
          <input type="checkbox" name="lead_consent" value="1" required />
          <span>Wyrażam zgodę na kontakt w sprawie przesłanego zapytania.</span>
        </label>
        <button type="submit" class="ct-btn ct-btn-primary">Wyślij i umów rozmowę →</button>
        <p class="ct-fineprint">Nie wysyłam newslettera ani ofert. Tylko odpowiedź na Twoje pytanie.</p>
      </form>
    </div>
  </section>

  <section class="ct-faq">
    <div class="ct-wrap">
      <header class="ct-sec-head">
        <div class="ct-eyebrow">Zanim napiszesz</div>
        <h2 class="ct-h2">Najczęstsze pytania.</h2>
      </header>
      <div class="ct-divider"></div>
      <div class="ct-faq-grid">
        <?php foreach (array_slice($contact_faq_items, 0, 4) as $faq_item) : ?>
          <details class="ct-faq-item">
            <summary>
              <span><?php echo esc_html((string) $faq_item["question"]); ?></span>
              <span class="ct-faq-icon">+</span>
            </summary>
            <p><?php echo esc_html((string) $faq_item["answer"]); ?></p>
          </details>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>

<?php if (!empty($contact_faq_items)) : ?>
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
    }, $contact_faq_items),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
?>
</script>
<?php endif; ?>

<?php
get_footer();
?>
