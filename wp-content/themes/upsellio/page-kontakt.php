<?php
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
$contact_phone = trim((string) ($front_page_sections["contact_phone"] ?? ""));
$contact_email = trim((string) ($front_page_sections["contact_email"] ?? "kontakt@upsellio.pl"));
$contact_page_url = home_url("/kontakt/");
?>
<style>
  .contact-page { background:#fff; }
  .contact-hero { border-bottom:1px solid var(--border); background:linear-gradient(180deg, rgba(29,158,117,0.08), rgba(255,255,255,0) 60%); }
  .contact-hero-inner { padding:56px 0 44px; display:grid; gap:26px; }
  .contact-hero-points { display:grid; gap:10px; margin-top:20px; }
  .contact-hero-point { display:flex; gap:8px; align-items:flex-start; color:var(--text-2); font-size:14px; line-height:1.65; }
  .contact-check { color:var(--teal); font-weight:700; }
  .contact-grid { display:grid; gap:16px; grid-template-columns:1fr; }
  .contact-card { border:1px solid var(--border); border-radius:18px; background:#fff; padding:22px; box-shadow:var(--shadow-sm); }
  .contact-card-label { font-size:11px; letter-spacing:.14em; text-transform:uppercase; color:var(--text-3); font-weight:700; }
  .contact-card-title { margin:10px 0 8px; font-family:var(--font-display); font-size:24px; line-height:1.04; letter-spacing:-.03em; }
  .contact-card-copy { color:var(--text-2); font-size:14px; line-height:1.72; }
  .contact-card-link { display:inline-flex; margin-top:14px; color:var(--teal); font-weight:700; font-size:14px; }
  .contact-form-shell { border:1px solid var(--border); border-top:3px solid var(--teal); border-radius:24px; padding:26px; background:linear-gradient(180deg, var(--bg-soft), #fff); box-shadow:var(--shadow-md); }
  .contact-form-grid { display:grid; gap:12px; grid-template-columns:1fr; }
  .contact-form-note { margin-top:8px; color:var(--text-3); font-size:12px; text-align:center; }
  .contact-form-alt { margin-top:16px; padding-top:16px; border-top:1px solid var(--border); display:grid; gap:8px; color:var(--text-2); font-size:13px; }
  .contact-faq { max-width:900px; }
  .contact-cta { border:1px solid var(--teal-line); background:var(--teal-soft); border-radius:22px; padding:26px; display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:16px; }
  .contact-cta-copy { max-width:700px; color:var(--teal-dark); }
  .contact-cta-copy h3 { font-family:var(--font-display); font-size:26px; line-height:1.05; margin-bottom:8px; }

  @media (min-width: 761px) {
    .contact-hero-inner { padding:72px 0 56px; }
    .contact-grid { grid-template-columns:repeat(3, minmax(0, 1fr)); }
    .contact-form-grid { grid-template-columns:1fr 1fr; }
    .contact-form-grid .field.full { grid-column:1 / -1; }
  }
</style>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "ContactPage",
  "name": "Kontakt Upsellio",
  "url": "<?php echo esc_url($contact_page_url); ?>",
  "mainEntity": {
    "@type": "ProfessionalService",
    "name": "Upsellio",
    "url": "<?php echo esc_url(home_url("/")); ?>",
    "email": "<?php echo esc_html($contact_email); ?>",
    "telephone": "<?php echo esc_html($contact_phone); ?>"
  }
}
</script>

<main class="contact-page">
  <section class="contact-hero">
    <div class="wrap contact-hero-inner">
      <div class="content">
        <div class="eyebrow reveal visible">Kontakt</div>
        <h1 class="h1 reveal visible">Porozmawiajmy o tym, jak zwiększyć <span class="accent">liczbę jakościowych leadów</span></h1>
        <p class="lead reveal visible" style="margin-top:16px;">Wypełnij formularz i opisz krótko sytuację firmy. Wrócę z konkretną rekomendacją, od czego zacząć i gdzie najszybciej możesz poprawić wynik.</p>
        <div class="contact-hero-points reveal visible">
          <div class="contact-hero-point"><span class="contact-check">✓</span><span>Odpowiedź zwykle w 24h robocze.</span></div>
          <div class="contact-hero-point"><span class="contact-check">✓</span><span>Bez presji sprzedażowej i bez gotowych pakietów.</span></div>
          <div class="contact-hero-point"><span class="contact-check">✓</span><span>Kontakt bezpośrednio z osobą, która prowadzi projekty.</span></div>
        </div>
      </div>
      <a href="#formularz-kontaktowy" class="btn btn-primary reveal visible" style="width:fit-content;">Przejdź do formularza →</a>
    </div>
  </section>

  <section class="section section-border">
    <div class="wrap contact-grid">
      <article class="contact-card reveal">
        <div class="contact-card-label">E-mail</div>
        <h2 class="contact-card-title">Napisz wiadomość</h2>
        <p class="contact-card-copy">Jeśli wolisz klasyczny kontakt, napisz bezpośrednio na skrzynkę. Każda wiadomość trafia do mnie.</p>
        <a class="contact-card-link" href="<?php echo esc_url("mailto:" . $contact_email); ?>"><?php echo esc_html($contact_email); ?></a>
      </article>
      <article class="contact-card reveal d1">
        <div class="contact-card-label">Telefon</div>
        <h2 class="contact-card-title">Szybka rozmowa</h2>
        <p class="contact-card-copy">Masz pilny temat? Zadzwoń, a jeśli nie odbiorę, wrócę z kontaktem możliwie szybko.</p>
        <?php if ($contact_phone !== "") : ?>
          <a class="contact-card-link" href="<?php echo esc_url("tel:" . preg_replace("/\s+/", "", $contact_phone)); ?>"><?php echo esc_html($contact_phone); ?></a>
        <?php else : ?>
          <span class="contact-card-link">Numer telefonu w konfiguracji strony.</span>
        <?php endif; ?>
      </article>
      <article class="contact-card reveal d2">
        <div class="contact-card-label">Lead generation</div>
        <h2 class="contact-card-title">Formularz strategiczny</h2>
        <p class="contact-card-copy">Formularz zbiera kontekst biznesowy i dane źródła ruchu, dzięki czemu od pierwszej odpowiedzi przechodzimy do konkretów.</p>
        <a class="contact-card-link" href="#formularz-kontaktowy">Wypełnij formularz</a>
      </article>
    </div>
  </section>

  <section class="section bg-soft section-border" id="formularz-kontaktowy">
    <div class="wrap">
      <div class="contact-form-shell reveal visible">
        <div class="content" style="margin-bottom:24px;">
          <div class="eyebrow">Formularz kontaktowy</div>
          <h2 class="h2">Umów <span class="accent">bezpłatną rozmowę wstępną</span></h2>
          <p class="body" style="margin-top:14px;">Im lepiej opiszesz cel i problem, tym precyzyjniejszą rekomendację dostaniesz w pierwszej odpowiedzi.</p>
        </div>

        <?php $ups_form_status = isset($_GET["ups_lead_status"]) ? sanitize_text_field(wp_unslash($_GET["ups_lead_status"])) : ""; ?>
        <?php if ($ups_form_status === "success") : ?>
          <div style="margin-bottom:12px;padding:10px 12px;border:1px solid #c3eddd;background:#e8f8f2;border-radius:10px;color:#085041;font-size:13px;">Dziękuję! Wiadomość została zapisana i odezwę się możliwie szybko.</div>
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
              <input class="input" type="text" id="fname" name="lead_name" placeholder="np. Marek Kowalski, firma XYZ" required />
              <span class="field-error" id="fname-err">Podaj imię i nazwę firmy</span>
            </div>
            <div class="field">
              <label for="femail">E-mail służbowy *</label>
              <input class="input" type="email" id="femail" name="lead_email" placeholder="adres@twojafirma.pl" required />
              <span class="field-error" id="femail-err">Podaj poprawny adres e-mail</span>
            </div>
            <div class="field">
              <label for="fphone">Telefon (opcjonalnie)</label>
              <input class="input" type="tel" id="fphone" name="lead_phone" placeholder="+48 600 000 000" autocomplete="tel" />
            </div>
            <div class="field">
              <label for="fservice">Zakres wsparcia</label>
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
              <textarea class="textarea" id="fmsg" name="lead_message" placeholder="np. potrzebuję więcej wartościowych zapytań, chcę poprawić konwersję strony albo uporządkować działania reklamowe..." required></textarea>
              <span class="field-error" id="fmsg-err">Opisz w kilku słowach swoją sytuację</span>
            </div>
            <div class="field full">
              <label style="display:flex;gap:8px;align-items:flex-start;">
                <input type="checkbox" name="lead_consent" value="1" required style="margin-top:3px;" />
                <span>Wyrażam zgodę na kontakt w sprawie mojego zapytania.</span>
              </label>
            </div>
          </div>

          <button type="submit" class="btn btn-primary" id="submit-btn" style="width:100%;justify-content:center;margin-top:10px;">Wyślij i umów rozmowę →</button>
          <p class="contact-form-note">Dane z formularza służą wyłącznie do kontaktu i przygotowania rekomendacji.</p>
        </form>

        <div class="contact-form-alt">
          <div>✓ Lead trafia od razu do CRM i dostaje priorytet kontaktu.</div>
          <div>✓ Źródło ruchu (UTM) zapisuje się automatycznie dla lepszej analityki leadów.</div>
          <div>✓ Jeśli wygodniej, napisz bezpośrednio: <a href="<?php echo esc_url("mailto:" . $contact_email); ?>" style="color:var(--teal);font-weight:700;"><?php echo esc_html($contact_email); ?></a></div>
        </div>
      </div>
    </div>
  </section>

  <section class="section section-border">
    <div class="wrap">
      <div class="content">
        <div class="eyebrow reveal">Proces</div>
        <h2 class="h2 reveal d1">Co dzieje się <span class="accent">po wysłaniu formularza</span></h2>
      </div>
      <div style="margin-top:28px;display:grid;gap:10px;">
        <div class="contact-card reveal"><strong>1. Analiza zgłoszenia</strong><p class="contact-card-copy">Sprawdzam kontekst i cel biznesowy, żeby wrócić z konkretem, a nie z automatyczną odpowiedzią.</p></div>
        <div class="contact-card reveal d1"><strong>2. Odpowiedź z rekomendacją</strong><p class="contact-card-copy">Dostajesz informację, co warto zrobić najpierw, gdzie jest największa dźwignia i jaki kolejny krok ma sens.</p></div>
        <div class="contact-card reveal d2"><strong>3. Decyzja bez presji</strong><p class="contact-card-copy">Jeśli chcesz, przechodzimy dalej. Jeśli nie, zostajesz z jasnym kierunkiem działań.</p></div>
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
        <div class="faq-item reveal">
          <button class="faq-q" type="button"><span>Jak szybko wracasz z odpowiedzią?</span><span class="faq-icon">+</span></button>
          <div class="faq-a">Zazwyczaj do końca kolejnego dnia roboczego. W pilniejszych tematach często szybciej.</div>
        </div>
        <div class="faq-item reveal d1">
          <button class="faq-q" type="button"><span>Czy muszę mieć gotowy budżet na start?</span><span class="faq-icon">+</span></button>
          <div class="faq-a">Nie. Najpierw ustalamy, co realnie warto zrobić i jaki zakres ma sens przy Twojej sytuacji.</div>
        </div>
        <div class="faq-item reveal d2">
          <button class="faq-q" type="button"><span>Czy to kontakt tylko dla nowych klientów?</span><span class="faq-icon">+</span></button>
          <div class="faq-a">Nie. Formularz jest także dla firm, które mają już działania marketingowe i chcą je poprawić.</div>
        </div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="wrap">
      <div class="contact-cta reveal">
        <div class="contact-cta-copy">
          <h3>Masz temat do omówienia?</h3>
          <p>Wystarczy krótki opis. Odpowiem konkretnie, czy i jak mogę pomóc, oraz jaki pierwszy krok da największy efekt.</p>
        </div>
        <a href="#formularz-kontaktowy" class="btn btn-primary">Przejdź do formularza →</a>
      </div>
    </div>
  </section>
</main>

<?php
get_footer();
?>
