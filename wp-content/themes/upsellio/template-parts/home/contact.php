<?php

if (!defined("ABSPATH")) {
    exit;
}

$contact_service_options = isset($args["contact_service_options"]) && is_array($args["contact_service_options"])
    ? $args["contact_service_options"]
    : [];
$contact_phone = trim((string) ($args["contact_phone"] ?? ""));
?>
<section class="section" id="kontakt">
      <div class="wrap">
        <div style="max-width:720px;margin:0 auto 28px;">
          <div class="eyebrow reveal">Kontakt</div>
          <h2 class="h2 reveal d1">Umów bezpłatną konsultację</h2>
          <p class="body reveal d2" style="margin-top:10px;">Opowiesz o firmie, ruchu i obecnych wynikach. Wrócę z konkretną rekomendacją: co poprawić najpierw, żeby zwiększyć liczbę wartościowych zapytań.</p>
        </div>
        <div class="contact-strategy-form contact-extended-layout" style="max-width:980px;margin:0 auto;">
          <div class="contact-extended-benefits">
            <h3 class="h3">Co dostaniesz po wysłaniu formularza?</h3>
            <ul>
              <li>Odpowiedź w ciągu 24h z pierwszym kierunkiem działań.</li>
              <li>30-minutową rozmowę o kampanii, stronie i jakości leadów.</li>
              <li>Checklistę priorytetów do wdrożenia po rozmowie.</li>
            </ul>
            <blockquote>“Po pierwszej rozmowie wiedzieliśmy dokładnie, co poprawić najpierw i gdzie uciekały zapytania.”</blockquote>
            <div class="contact-channels">
              <a href="<?php echo esc_url("tel:" . preg_replace("/\s+/", "", $contact_phone)); ?>">📞 Zadzwoń</a>
              <a href="<?php echo esc_url(home_url("/kontakt/")); ?>">📅 Umów termin</a>
              <a href="<?php echo esc_url("https://www.linkedin.com/"); ?>" target="_blank" rel="noopener noreferrer">💼 LinkedIn</a>
            </div>
          </div>
          <?php
          echo upsellio_render_lead_form([
              "origin" => "contact-form",
              "variant" => "full",
              "submit_label" => "Umów bezpłatną konsultację",
              "fineprint" => "Bez spamu. Odpowiadam osobiście.",
              "service_options" => $contact_service_options,
              "redirect_url" => home_url("/#kontakt"),
              "form_id" => "contact-form",
          ]);
          ?>
            <p class="form-note">Wolisz zadzwonić? <a href="<?php echo esc_url("tel:" . preg_replace("/\s+/", "", $contact_phone)); ?>"><?php echo esc_html($contact_phone); ?></a></p>
            <div class="contact-inline-proof">Pracuję z firmami B2B, usługowymi i e-commerce w modelu wzrostu leadów.</div>
        </div>
      </div>
    </section>
