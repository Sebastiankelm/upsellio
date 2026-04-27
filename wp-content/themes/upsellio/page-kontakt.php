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
  .contact-page { background:#fff; }
  .contact-hero { border-bottom:1px solid var(--border); background:radial-gradient(circle at top right, rgba(20,184,166,0.22), rgba(255,255,255,0) 55%), linear-gradient(180deg, rgba(20,184,166,0.14), rgba(255,255,255,0) 70%); }
  .contact-hero-inner { padding:56px 0 44px; display:grid; gap:26px; }
  .contact-hero-points { display:grid; gap:10px; margin-top:20px; }
  .contact-hero-point { display:flex; gap:8px; align-items:flex-start; color:var(--text-2); font-size:14px; line-height:1.65; }
  .contact-check { color:var(--teal); font-weight:700; }
  .contact-hero-side { display:none; }
  .contact-host-card { display:flex; flex-direction:column; align-items:flex-start; gap:14px; padding:22px; border:1px solid var(--border); border-radius:22px; background:#fff; box-shadow:var(--shadow-sm); max-width:320px; }
  .contact-host-row { display:flex; gap:14px; align-items:center; }
  .contact-host-photo { width:72px; height:72px; border-radius:50%; object-fit:cover; border:3px solid #ecfeff; box-shadow:0 6px 18px -8px rgba(15,23,42,.25); flex-shrink:0; }
  .contact-host-initials { width:72px; height:72px; border-radius:50%; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#0d9488,#14b8a6); color:#fff; font-family:var(--font-display); font-size:26px; font-weight:800; letter-spacing:-.02em; box-shadow:0 6px 18px -8px rgba(13,148,136,.5); flex-shrink:0; }
  .contact-host-meta strong { display:block; font-family:var(--font-display); font-size:18px; line-height:1.15; letter-spacing:-.02em; color:var(--text); }
  .contact-host-meta span { display:block; margin-top:4px; font-size:12px; color:var(--text-3); }
  .contact-host-promise { margin:0; padding:10px 14px; border:1px solid #99f6e4; background:#ecfeff; border-radius:14px; color:#0f766e; font-weight:700; font-size:13px; line-height:1.5; align-self:stretch; }
  .contact-host-list { margin:0; padding:0; list-style:none; display:grid; gap:8px; align-self:stretch; }
  .contact-host-list li { display:flex; gap:8px; color:var(--text-2); font-size:13px; line-height:1.55; }
  .contact-host-list li::before { content:"✓"; color:var(--teal); font-weight:900; }
  .contact-grid { display:grid; gap:16px; grid-template-columns:1fr; }
  .contact-card { border:1px solid var(--border); border-radius:18px; background:#fff; padding:22px; box-shadow:var(--shadow-sm); }
  .contact-card-icon { width:42px; height:42px; border-radius:12px; background:#ecfeff; color:#0d9488; display:inline-flex; align-items:center; justify-content:center; margin-bottom:14px; }
  .contact-card-icon svg { width:22px; height:22px; }
  .contact-card-label { font-size:11px; letter-spacing:.14em; text-transform:uppercase; color:var(--text-3); font-weight:700; }
  .contact-card-title { margin:10px 0 8px; font-family:var(--font-display); font-size:24px; line-height:1.04; letter-spacing:-.03em; }
  .contact-card-copy { color:var(--text-2); font-size:14px; line-height:1.72; }
  .contact-card-link { display:inline-flex; margin-top:14px; color:var(--teal); font-weight:700; font-size:14px; }
  .contact-seo-copy { margin-top:18px; max-width:900px; display:grid; gap:14px; color:var(--text-2); line-height:1.78; }
  .contact-form-shell { border:1px solid var(--border); border-top:3px solid var(--teal); border-radius:24px; padding:26px; background:linear-gradient(180deg, var(--bg-soft), #fff); box-shadow:var(--shadow-md); }
  .contact-form-grid { display:grid; gap:12px; grid-template-columns:1fr; }
  .contact-form-note { margin-top:8px; color:var(--text-3); font-size:12px; text-align:center; }
  .contact-form-alt { margin-top:16px; padding-top:16px; border-top:1px solid var(--border); display:grid; gap:8px; color:var(--text-2); font-size:13px; }
  .contact-process-grid { margin-top:28px; display:grid; gap:12px; }
  .contact-process-card { position:relative; padding-top:30px; }
  .contact-process-card .contact-step-num { position:absolute; top:18px; right:20px; font-family:var(--font-display); font-size:36px; font-weight:800; line-height:1; letter-spacing:-.04em; color:var(--teal); opacity:.18; }
  .contact-process-card strong { display:block; margin-bottom:8px; color:var(--text); }
  .contact-faq { max-width:900px; }
  .contact-cta { border:1px solid var(--teal-line); background:var(--teal-soft); border-radius:22px; padding:26px; display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:16px; }
  .contact-cta-copy { max-width:700px; color:var(--teal-dark); }
  .contact-cta-copy h3 { font-family:var(--font-display); font-size:26px; line-height:1.05; margin-bottom:8px; }

  @media (min-width: 761px) {
    .contact-hero-inner { padding:72px 0 56px; grid-template-columns:minmax(0,1.4fr) minmax(0,0.8fr); align-items:start; gap:40px; }
    .contact-hero-side { display:block; }
    .contact-grid { grid-template-columns:repeat(3, minmax(0, 1fr)); }
    .contact-form-grid { grid-template-columns:1fr 1fr; }
    .contact-form-grid .field.full { grid-column:1 / -1; }
    .contact-process-grid { grid-template-columns:repeat(3, minmax(0, 1fr)); }
  }
  /* Mobile-first UX correction layer */
  .contact-page .h1 { font-size:clamp(34px,10vw,40px); line-height:1.09; letter-spacing:-1px; }
  .contact-page .h2 { font-size:clamp(28px,8vw,34px); line-height:1.12; letter-spacing:-.8px; }
  .contact-page .lead { font-size:17px; line-height:1.65; }
  .contact-page .section { padding:48px 0; }
  .contact-hero-inner { padding:48px 0 42px; }
  .contact-grid,.contact-form-grid,.contact-process-grid { grid-template-columns:1fr; }
  .contact-card,.contact-form-shell,.contact-cta { padding:20px; border-radius:20px; }
  .contact-seo-copy { gap:10px; line-height:1.72; }
  .contact-cta-copy h3 { font-size:clamp(22px,7vw,28px); line-height:1.12; }
  @media (min-width: 761px) {
    .contact-page .h1 { font-size:clamp(44px,6vw,58px); line-height:1.05; }
    .contact-page .h2 { font-size:clamp(34px,4vw,46px); }
    .contact-page .section { padding:72px 0; }
    .contact-hero-inner { padding:70px 0 56px; }
    .contact-grid,.contact-process-grid { grid-template-columns:repeat(3,minmax(0,1fr)); }
    .contact-form-grid { grid-template-columns:1fr 1fr; }
  }
  @media (min-width: 1024px) {
    .contact-page .h1 { font-size:64px; }
    .contact-page .h2 { font-size:50px; }
    .contact-page .section { padding:92px 0; }
  }
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

<main class="contact-page">
  <section class="contact-hero">
    <div class="wrap contact-hero-inner">
      <div class="content">
        <div class="eyebrow reveal visible">Kontakt</div>
        <h1 class="h1 reveal visible">Porozmawiajmy o tym, co blokuje wzrost Twojej firmy — i co warto zrobić najpierw.</h1>
        <p class="lead reveal visible" style="margin-top:16px;">Opisz krótko sytuację firmy, ofertę i główny problem. Wrócę z konkretną rekomendacją — nie z ofertą sprzedażową. Bez zobowiązań, bez ogólników.</p>
        <div class="contact-seo-copy reveal visible">
          <p>Strona kontaktowa Upsellio to początek konkretnej rozmowy o tym, gdzie Twoja firma traci potencjalnych klientów i co warto poprawić najpierw: w kampaniach reklamowych, stronie internetowej albo samej ofercie.</p>
          <p>Nie zaczynam współpracy od wysyłania gotowych pakietów. Najpierw chcę zrozumieć, co sprzedajesz, do kogo, co dziś nie działa i jaki efekt chcesz osiągnąć. Na tej podstawie daję rekomendację, który kanał ma sens, od czego zacząć i czego realnie można oczekiwać.</p>
        </div>
        <div class="contact-hero-points reveal visible">
          <div class="contact-hero-point"><span class="contact-check">✓</span><span>Odpowiedź zwykle w 24h robocze.</span></div>
          <div class="contact-hero-point"><span class="contact-check">✓</span><span>Bez presji sprzedażowej i bez gotowych pakietów.</span></div>
          <div class="contact-hero-point"><span class="contact-check">✓</span><span>Kontakt bezpośrednio z Sebastianem Kelmem, praktykiem sprzedaży i marketingu B2B.</span></div>
        </div>
        <a href="#formularz-kontaktowy" class="btn btn-primary reveal visible" style="width:fit-content;margin-top:18px;">Przejdź do formularza →</a>
      </div>
      <aside class="contact-hero-side reveal visible">
        <div class="contact-host-card">
          <div class="contact-host-row">
            <?php if ($contact_founder_photo !== "") : ?>
              <img class="contact-host-photo" src="<?php echo esc_url($contact_founder_photo); ?>" alt="<?php echo esc_attr($contact_founder_name); ?>" width="72" height="72" loading="lazy" decoding="async" />
            <?php else : ?>
              <div class="contact-host-initials" aria-hidden="true"><?php echo esc_html($contact_founder_initials !== "" ? $contact_founder_initials : "SK"); ?></div>
            <?php endif; ?>
            <div class="contact-host-meta">
              <strong><?php echo esc_html($contact_founder_name); ?></strong>
              <span><?php echo esc_html($contact_founder_role); ?></span>
            </div>
          </div>
          <p class="contact-host-promise">„Odpiszę osobiście w ciągu 24h roboczych — bez automatycznych ofert.”</p>
          <ul class="contact-host-list">
            <li>Czytam każde zgłoszenie i wracam z konkretną rekomendacją.</li>
            <li>Nie wysyłam szablonowych cenników.</li>
            <li>Decyzja należy do Ciebie. Bez presji sprzedażowej.</li>
          </ul>
        </div>
      </aside>
    </div>
  </section>

  <section class="section section-border">
    <div class="wrap contact-grid">
      <article class="contact-card reveal">
        <div class="contact-card-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        </div>
        <div class="contact-card-label">E-mail</div>
        <h2 class="contact-card-title">Napisz wiadomość</h2>
        <p class="contact-card-copy">Każda wiadomość trafia bezpośrednio do mnie. Odpisuję z rekomendacją, nie z szablonową odpowiedzią.</p>
        <a class="contact-card-link" href="<?php echo esc_url($contact_email_href); ?>"><?php echo esc_html($contact_email_display); ?></a>
      </article>
      <article class="contact-card reveal d1">
        <div class="contact-card-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.86 19.86 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.86 19.86 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.37 1.9.72 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.35 1.85.59 2.81.72A2 2 0 0 1 22 16.92z"/></svg>
        </div>
        <div class="contact-card-label">Telefon</div>
        <h2 class="contact-card-title">Szybka rozmowa</h2>
        <p class="contact-card-copy">Masz pilny temat lub wolisz porozmawiać zanim wypełnisz formularz? Zadzwoń. Jeśli nie odbiorę, wrócę z kontaktem tego samego dnia.</p>
        <a class="contact-card-link" href="<?php echo esc_url("tel:" . preg_replace("/\s+/", "", $contact_phone)); ?>"><?php echo esc_html($contact_phone); ?></a>
      </article>
      <article class="contact-card reveal d2">
        <div class="contact-card-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="13" y2="17"/></svg>
        </div>
        <div class="contact-card-label">Formularz</div>
        <h2 class="contact-card-title">Bezpłatna diagnoza</h2>
        <p class="contact-card-copy">Formularz zbiera kontekst biznesowy Twojej firmy, dzięki temu od pierwszej odpowiedzi przechodzimy do konkretów, a nie ogólnych pytań.</p>
        <a class="contact-card-link" href="#formularz-kontaktowy">Wypełnij formularz i wybierz termin</a>
      </article>
    </div>
  </section>

  <section class="section bg-soft section-border" id="formularz-kontaktowy">
    <div class="wrap">
      <div class="contact-form-shell reveal visible">
        <div class="content" style="margin-bottom:24px;">
          <div class="eyebrow">Formularz kontaktowy</div>
          <h2 class="h2">Opisz sytuację firmy — <span class="accent">wrócę z konkretną rekomendacją.</span></h2>
          <p class="body" style="margin-top:14px;">Im więcej kontekstu podasz, tym precyzyjniejszą odpowiedź dostaniesz. Nie musisz wiedzieć, czego konkretnie potrzebujesz — wystarczy opisać problem i cel.</p>
        </div>

        <?php $ups_form_status = isset($_GET["ups_lead_status"]) ? sanitize_text_field(wp_unslash($_GET["ups_lead_status"])) : ""; ?>
        <?php if ($ups_form_status === "success") : ?>
          <div style="margin-bottom:12px;padding:10px 12px;border:1px solid #99f6e4;background:#ecfeff;border-radius:10px;color:#0f766e;font-size:13px;">Dziękuję! Formularz dotarł. Przejrzę opisaną sytuację i odpiszę z konkretną rekomendacją — zazwyczaj do końca kolejnego dnia roboczego. Jeśli masz pilną sprawę, możesz zadzwonić: <?php echo esc_html($contact_phone); ?>.</div>
        <?php elseif ($ups_form_status === "error") : ?>
          <div style="margin-bottom:12px;padding:10px 12px;border:1px solid #edcccc;background:#fff2f2;border-radius:10px;color:#b13a3a;font-size:13px;">Nie udało się wysłać formularza. Sprawdź pola i spróbuj ponownie.</div>
        <?php endif; ?>

        <form id="contact-form" method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" novalidate data-upsellio-lead-form="1" data-upsellio-server-form="1">
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

          <div class="contact-form-grid">
            <div class="field">
              <label for="fname">Imię i nazwa firmy *</label>
              <input class="input" type="text" id="fname" name="lead_name" placeholder="np. Marek Kowalski, firma XYZ" required autocomplete="name organization" />
              <span class="field-error" id="fname-err">Podaj imię i nazwę firmy</span>
            </div>
            <div class="field">
              <label for="femail">E-mail służbowy *</label>
              <input class="input" type="email" id="femail" name="lead_email" placeholder="adres@twojafirma.pl" required autocomplete="email" />
              <span class="field-error" id="femail-err">Podaj poprawny adres e-mail</span>
            </div>
            <div class="field">
              <label for="fphone">Telefon (opcjonalnie)</label>
              <input class="input" type="tel" id="fphone" name="lead_phone" placeholder="+48 575 522 595" autocomplete="tel" />
            </div>
            <div class="field">
              <label for="fservice">Czego szukasz?</label>
              <select class="select" id="fservice" name="lead_service">
                <option value="">— wybierz —</option>
                <?php foreach ($contact_service_options as $service_option) : ?>
                  <?php $service_option = trim((string) $service_option); ?>
                  <?php if ($service_option === "") : ?>
                    <?php continue; ?>
                  <?php endif; ?>
                  <option><?php echo esc_html($service_option); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field full">
              <label for="fmsg">Co chcesz poprawić lub osiągnąć? *</label>
              <textarea class="textarea" id="fmsg" name="lead_message" placeholder="np. mam aktywne kampanie, ale za mało zapytań, chcę zbudować nową stronę pod reklamy albo kompletnie nie wiem od czego zacząć..." required></textarea>
              <span class="field-error" id="fmsg-err">Opisz w kilku słowach swoją sytuację</span>
            </div>
            <div class="field full">
              <label style="display:flex;gap:8px;align-items:flex-start;">
                <input type="checkbox" name="lead_consent" value="1" required style="margin-top:3px;" />
                <span>Wyrażam zgodę na kontakt w sprawie mojego zapytania i przetwarzanie danych w celu przygotowania odpowiedzi.</span>
              </label>
            </div>
          </div>

          <button type="submit" class="btn btn-primary" id="submit-btn" style="width:100%;justify-content:center;margin-top:10px;">Wyślij i umów bezpłatną rozmowę →</button>
          <p class="contact-form-note">Dane z formularza służą wyłącznie do kontaktu. Nie wysyłam spamu. Odpowiedź zazwyczaj w ciągu 24h roboczych.</p>
        </form>

        <div class="contact-form-alt">
          <div>✓ Współpraca zaczyna się od rozmowy, nie od umowy.</div>
          <div>✓ Po wysłaniu formularza analizuję sytuację i wracam z odpowiedzią: co warto zrobić najpierw, który kanał ma sens i jakich efektów można realnie oczekiwać.</div>
          <div>✓ Jeśli wygodniej, napisz bezpośrednio: <a href="<?php echo esc_url($contact_email_href); ?>" style="color:var(--teal);font-weight:700;"><?php echo esc_html($contact_email_display); ?></a></div>
        </div>
      </div>
    </div>
  </section>

  <section class="section section-border">
    <div class="wrap">
      <div class="content">
        <div class="eyebrow reveal">Proces</div>
        <h2 class="h2 reveal d1">Co dzieje się po wysłaniu formularza — trzy kroki od zapytania do konkretnej odpowiedzi.</h2>
        <p class="body reveal d2" style="margin-top:14px;">Wiele firm obawia się kontaktu z agencją marketingową, bo spodziewa się automatycznej odpowiedzi z cennikiem i presji na szybką decyzję. U mnie wygląda to inaczej.</p>
      </div>
      <div class="contact-process-grid">
        <div class="contact-card contact-process-card reveal">
          <span class="contact-step-num" aria-hidden="true">01</span>
          <strong>Analiza zgłoszenia</strong>
          <p class="contact-card-copy">Po otrzymaniu formularza czytam opisaną sytuację i przygotowuję się do odpowiedzi. Analizuję problem, obecne działania i najbardziej logiczny następny krok.</p>
        </div>
        <div class="contact-card contact-process-card reveal d1">
          <span class="contact-step-num" aria-hidden="true">02</span>
          <strong>Odpowiedź z rekomendacją</strong>
          <p class="contact-card-copy">Wracam z konkretnymi obserwacjami: co warto sprawdzić lub poprawić najpierw, który kanał ma sens i jaki następny krok proponuję.</p>
        </div>
        <div class="contact-card contact-process-card reveal d2">
          <span class="contact-step-num" aria-hidden="true">03</span>
          <strong>Decyzja bez presji</strong>
          <p class="contact-card-copy">Po wymianie informacji decydujesz, czy chcesz kontynuować. Jeśli nie, zostajesz z wiedzą, którą możesz wdrożyć samodzielnie lub porównać z innymi ofertami.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="section bg-soft section-border">
    <div class="wrap">
      <div class="content">
        <div class="eyebrow reveal">FAQ</div>
        <h2 class="h2 reveal d1">Najczęstsze pytania przed <span class="accent">pierwszym kontaktem</span></h2>
      </div>
      <div class="contact-faq" style="margin-top:26px;">
        <?php foreach ($contact_faq_items as $faq_index => $faq_item) : ?>
          <div class="faq-item reveal <?php echo esc_attr($faq_index > 0 ? "d" . min($faq_index, 2) : ""); ?>">
            <button class="faq-q" type="button"><span><?php echo esc_html((string) $faq_item["question"]); ?></span><span class="faq-icon">+</span></button>
            <div class="faq-a"><?php echo esc_html((string) $faq_item["answer"]); ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="wrap">
      <div class="contact-cta reveal">
        <div class="contact-cta-copy">
          <h3>Masz temat do omówienia? Wystarczy krótki opis — wrócę z konkretną odpowiedzią.</h3>
          <p>Jeśli czujesz, że marketing może dawać lepsze wyniki, ale nie wiesz, co poprawić najpierw, wyślij formularz, napisz maila albo zadzwoń. Bez zobowiązań i bez presji.</p>
        </div>
        <a href="#formularz-kontaktowy" class="btn btn-primary">Przejdź do formularza →</a>
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
