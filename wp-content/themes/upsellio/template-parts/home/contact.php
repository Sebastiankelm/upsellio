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
        <?php $ups_form_status = isset($_GET["ups_lead_status"]) ? sanitize_text_field(wp_unslash($_GET["ups_lead_status"])) : ""; ?>
        <div style="max-width:720px;margin:0 auto 28px;">
          <div class="eyebrow reveal">Kontakt</div>
          <h2 class="h2 reveal d1">Umów bezpłatną konsultację</h2>
          <p class="body reveal d2" style="margin-top:10px;">Opowiesz o firmie, ruchu i obecnych wynikach. Wrócę z konkretną rekomendacją: co poprawić najpierw, żeby zwiększyć liczbę wartościowych zapytań.</p>
        </div>
        <div class="contact-strategy-form" style="max-width:860px;margin:0 auto;">
          <?php if ($ups_form_status === "success") : ?>
            <div style="margin-bottom:12px;padding:10px 12px;border:1px solid #c3eddd;background:#e8f8f2;border-radius:10px;color:#085041;font-size:13px;">Dziękuję! Wiadomość została zapisana i odezwę się możliwie szybko.</div>
          <?php elseif ($ups_form_status === "error") : ?>
            <div style="margin-bottom:12px;padding:10px 12px;border:1px solid #edcccc;background:#fff2f2;border-radius:10px;color:#b13a3a;font-size:13px;">Nie udało się wysłać formularza. Sprawdź pola i spróbuj ponownie.</div>
          <?php endif; ?>
          <form id="contact-form" method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" novalidate data-upsellio-lead-form="1" data-upsellio-server-form="1">
            <input type="hidden" name="action" value="upsellio_submit_lead" />
            <input type="hidden" name="redirect_url" value="<?php echo esc_url(home_url("/#kontakt")); ?>" />
            <input type="hidden" name="lead_form_origin" value="contact-form" />
            <input type="hidden" name="lead_source" value="contact-form" />
            <input type="hidden" name="utm_source" data-ups-utm="source" value="" />
            <input type="hidden" name="utm_medium" data-ups-utm="medium" value="" />
            <input type="hidden" name="utm_campaign" data-ups-utm="campaign" value="" />
            <input type="hidden" name="landing_url" data-ups-context="landing" value="" />
            <input type="hidden" name="referrer" data-ups-context="referrer" value="" />
            <input type="text" name="lead_website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;" />
            <?php wp_nonce_field("upsellio_unified_lead_form", "upsellio_lead_form_nonce"); ?>
            <div class="form-grid">
              <div class="field"><label for="fname">Imię i nazwisko *</label><input class="input" type="text" id="fname" name="lead_name" placeholder="Twoje imię i nazwisko" required /></div>
              <div class="field"><label for="femail">E-mail *</label><input class="input" type="email" id="femail" name="lead_email" placeholder="Twój adres e-mail" required /></div>
              <div class="field"><label for="fcompany">Nazwa firmy</label><input class="input" type="text" id="fcompany" name="lead_company" placeholder="Nazwa Twojej firmy" /></div>
              <div class="field"><label for="fservice">Czego dotyczy rozmowa?</label><select class="select" id="fservice" name="lead_service"><option value="">Wybierz obszar</option><?php foreach ($contact_service_options as $service_option) : ?><?php $service_option = trim((string) $service_option); ?><?php if ($service_option === "") : ?><?php continue; ?><?php endif; ?><option><?php echo esc_html($service_option); ?></option><?php endforeach; ?></select></div>
              <div class="field full"><label for="fmsg">Krótko opisz swój cel lub wyzwanie *</label><textarea class="textarea" id="fmsg" name="lead_message" placeholder="Napisz kilka słów o swoim biznesie i oczekiwaniach..." required></textarea></div>
              <div class="field full"><label style="display:flex;gap:8px;align-items:flex-start;"><input type="checkbox" name="lead_consent" value="1" required style="margin-top:3px;" /><span>Wyrażam zgodę na kontakt w sprawie mojego zapytania i przesłanie odpowiedzi na podane dane kontaktowe.</span></label></div>
            </div>
            <button type="submit" class="btn btn-primary submit" id="submit-btn">Umów bezpłatną konsultację</button>
            <p class="form-note">Bez spamu. Odpowiadam osobiście. Wolisz zadzwonić? <a href="<?php echo esc_url("tel:" . preg_replace("/\s+/", "", $contact_phone)); ?>"><?php echo esc_html($contact_phone); ?></a></p>
          </form>
        </div>
      </div>
    </section>
