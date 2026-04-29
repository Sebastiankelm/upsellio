<?php

if (!defined("ABSPATH")) {
    exit;
}

?>
<section class="hero" id="start">
  <div class="wrap hero-wrap">
    <div class="hero-copy">
      <div class="hero-pill reveal in d1">
        <div class="hero-pill-dot">B2B</div>
        <span>Marketing B2B, który zamienia ruch w wartościowe rozmowy</span>
      </div>
      <h1 class="h1 hero-h1 reveal in d1">
        Kampanie <span class="hero-accent">Google Ads i Meta Ads</span>, które zamieniają ruch w <span class="hero-accent">klientów B2B</span>
      </h1>
      <p class="lead hero-lead reveal in d2">
        Tworzę kampanie i strony, które zamieniają ruch w sprzedaż. Sprawdzam, gdzie uciekają zapytania: w kampanii, na stronie, w ofercie albo w jakości leadów.
      </p>
      <p class="body hero-support-copy reveal in d2">
        Nie robię marketingu dla samego marketingu. Robię sprzedaż przez marketing: ruch → leady → rozmowy → klienci.
      </p>
      <div class="hero-actions reveal in d3">
        <a href="<?php echo esc_url(home_url("/#hero-analiza")); ?>" class="btn btn-primary">Sprawdź potencjał</a>
        <a href="<?php echo esc_url(home_url("/#jak-dzialam")); ?>" class="btn btn-secondary">Zobacz, jak pracuję</a>
      </div>
      <p class="hero-micro reveal in d3">Bezpłatna analiza • konkretne wnioski • bez zobowiązań</p>
    </div>

    <aside class="hero-aside hero-aside-system reveal in d2" aria-label="Mini dashboard analizy leadów">
      <div class="hero-author-card">
        <div class="hero-author-photo">
          <?php echo function_exists("upsellio_render_home_media_image") ? upsellio_render_home_media_image("hero_portrait", ["class" => "hero-author-img", "size" => "medium_large", "loading" => "eager"]) : ""; ?>
        </div>
        <div>
          <div class="hero-author-kicker">Sebastian Kelm</div>
          <p><?php echo esc_html(function_exists("upsellio_home_media_slot_caption") ? upsellio_home_media_slot_caption("hero_portrait") : "Marketing B2B oparty o sprzedaż, dane i konwersję."); ?></p>
        </div>
      </div>
      <div class="hero-aside-label">Przykładowy wynik z kampanii — branża: usługi B2B</div>
      <div class="hero-system" id="hero-system">
        <div class="hero-system-head">
          <div>
            <div class="hero-system-side-title">23 810</div>
            <div class="hero-system-side-sub">ruch / mies.</div>
          </div>
          <div>
            <div class="hero-system-side-title">362</div>
            <div class="hero-system-side-sub">leady do oceny</div>
          </div>
        </div>
        <div class="hero-system-core">
          <div class="hero-core-head">
            <span>Lead quality</span>
            <strong>72%</strong>
          </div>
          <div class="hero-kpi-row">
            <div class="hero-kpi-block">
              <span>Konwersja strony</span>
              <b>2,3%</b>
              <i data-hero-kpi-progress style="width:62%"></i>
            </div>
            <div class="hero-kpi-block">
              <span>Budżet bez efektu</span>
              <b>-18%</b>
              <i data-hero-kpi-progress style="width:48%"></i>
            </div>
          </div>
          <div class="hero-spark-grid" data-hero-spark aria-hidden="true">
            <span style="height:32%"></span><span style="height:58%"></span><span style="height:44%"></span><span style="height:76%"></span><span style="height:61%"></span><span style="height:86%"></span>
          </div>
        </div>
        <div class="hero-system-pipe" aria-label="Lejek: ruch, strona, lead, rozmowa, sprzedaż">
          <div class="hero-pipe-step is-active"><span>Ruch</span><b>100%</b></div>
          <div class="hero-pipe-step"><span>Strona</span><b>42%</b></div>
          <div class="hero-pipe-step"><span>Lead</span><b>18%</b></div>
          <div class="hero-pipe-step"><span>Rozmowa</span><b>9%</b></div>
          <div class="hero-pipe-step"><span>Sprzedaż</span><b>4%</b></div>
        </div>
      </div>

      <form class="hero-microform" id="hero-analiza" method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" novalidate data-upsellio-lead-form="1" data-upsellio-server-form="1">
        <input type="hidden" name="action" value="upsellio_submit_lead" />
        <input type="hidden" name="redirect_url" value="<?php echo esc_url(home_url("/#start")); ?>" />
        <input type="hidden" name="lead_name" value="Szybka analiza strony" />
        <input type="hidden" name="lead_form_origin" value="hero-microform" />
        <input type="hidden" name="lead_source" value="hero-microform" />
        <input type="hidden" name="utm_source" data-ups-utm="source" value="" />
        <input type="hidden" name="utm_medium" data-ups-utm="medium" value="" />
        <input type="hidden" name="utm_campaign" data-ups-utm="campaign" value="" />
        <input type="hidden" name="landing_url" data-ups-context="landing" value="" />
        <input type="hidden" name="referrer" data-ups-context="referrer" value="" />
        <input type="text" name="lead_website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;" />
        <?php wp_nonce_field("upsellio_unified_lead_form", "upsellio_lead_form_nonce"); ?>
        <div class="hero-microform-title">Szybka analiza strony</div>
        <label for="hero-site">Adres strony</label>
        <input class="input" id="hero-site" type="url" name="lead_company" placeholder="https://twojastrona.pl" required />
        <label for="hero-email">E-mail do odpowiedzi</label>
        <input class="input" id="hero-email" type="email" name="lead_email" placeholder="kontakt@firma.pl" required />
        <label for="hero-focus">Czego szukasz? (opcjonalnie)</label>
        <select class="input" id="hero-focus" name="lead_context">
          <option value="">Wybierz obszar (opcjonalne)</option>
          <option value="google_ads">Poprawy kampanii Google Ads</option>
          <option value="meta_ads">Poprawy kampanii Meta Ads</option>
          <option value="strona_cro">Lepszej konwersji strony</option>
          <option value="lejek_sprzedazy">Uporządkowania lejka i jakości leadów</option>
        </select>
        <input type="hidden" name="lead_service" value="Szybka analiza strony" />
        <input type="hidden" name="lead_message" value="Proszę o szybką analizę potencjału strony i wskazanie, co blokuje wyniki." />
        <label class="hero-consent">
          <input type="checkbox" name="lead_consent" value="1" required />
          <span>Wyrażam zgodę na kontakt w sprawie mojego zapytania.</span>
        </label>
        <button type="submit" class="btn btn-primary">Sprawdź potencjał</button>
      </form>
    </aside>
  </div>
</section>
<section class="hero-trust-strip section-border" aria-label="Zaufali mi klienci i partnerzy">
  <div class="wrap hero-trust-strip-inner">
    <span class="hero-trust-title">Trusted by</span>
    <div class="hero-trust-logos">
      <span>Google Partner</span>
      <span>Meta Business Partner</span>
      <span>GA4 Certified</span>
      <span>B2B Services</span>
      <span>E-commerce</span>
      <span>SaaS</span>
    </div>
  </div>
</section>

<section class="section section-border bg-soft" id="problem">
  <div class="wrap">
    <div class="section-head">
      <div class="eyebrow reveal">Problem</div>
      <h2 class="h2 reveal d1">Dlaczego kampanie Google Ads i Meta Ads nie przynoszą klientów?</h2>
      <p class="body reveal d2">Pokazuję, gdzie tracisz pieniądze: w kampanii, na stronie albo w sposobie pokazania oferty.</p>
    </div>
    <details class="content-expand section-copy-expand reveal d2">
      <summary>Co najczęściej blokuje zapytania?</summary>
      <div class="content-expand-content">
        <p>Reklama to tylko jeden element układanki. Bez odpowiedniej strony docelowej, klarownej oferty i systemu, który prowadzi odwiedzającego do decyzji, nawet dobra kampania będzie przeciekać.</p>
        <p><strong>Masz ruch, ale brak zapytań.</strong> To klasyczny objaw strony, która nie konwertuje. Użytkownik trafia na stronę, spędza chwilę i wychodzi bez kontaktu, formularza i telefonu.</p>
        <p><strong>Reklamy generują kliknięcia bez efektu.</strong> Kampania może zbierać kliknięcia, ale jeśli przekaz reklamowy nie odpowiada treści strony albo trafia do zbyt szerokiej grupy, koszt leada rośnie.</p>
        <p><strong>Strona nie prowadzi do decyzji.</strong> Dobra strona B2B powinna prowadzić przez problem, rozwiązanie, korzyści, dowody zaufania i prosty krok do kontaktu.</p>
      </div>
    </details>
    <div class="section-cta-row reveal d3">
      <a href="<?php echo esc_url(home_url("/#co-sprawdze")); ?>" class="btn btn-primary btn-sm">Sprawdź, co blokuje wyniki →</a>
      <a href="<?php echo esc_url(home_url("/#hero-analiza")); ?>" class="btn btn-secondary btn-sm">Wyślij stronę do analizy</a>
    </div>
  </div>
</section>
