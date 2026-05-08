<?php
/**
 * Template Name: Jak pomagam — hub audytów
 *
 * Hub usługowy łączący wszystkie audyty.
 * Cel: prowadzić użytkownika do najlepiej dopasowanego audytu.
 */
get_header();
?>

<style>
  .jph-page {
    background: var(--bg, #fafaf6);
    color: var(--text, #0d0d0b);
    font-family: var(--font-body, "DM Sans"), system-ui, sans-serif;
  }
  .jph-page * { box-sizing: border-box; }
  .jph-wrap { max-width: 1120px; margin: 0 auto; padding: 0 24px; }

  .jph-hero {
    padding: clamp(56px, 8vw, 96px) 0 clamp(32px, 5vw, 56px);
  }
  .jph-kicker {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    border-radius: 999px;
    background: var(--brand-soft, #ccfbf1);
    color: var(--brand-dark, #0f766e);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.4px;
    text-transform: uppercase;
    margin-bottom: 18px;
  }
  .jph-kicker::before {
    content: "";
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--brand, #0d9488);
  }
  .jph-hero h1 {
    margin: 0 0 14px;
    font-family: var(--font-display, "Bricolage Grotesque"), serif;
    font-size: clamp(34px, 5.4vw, 58px);
    line-height: 1.05;
    letter-spacing: -0.02em;
  }
  .jph-hero p {
    margin: 0;
    max-width: 760px;
    color: var(--text-soft, #6b7280);
    font-size: clamp(16px, 2vw, 19px);
    line-height: 1.65;
  }

  .jph-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
    padding-bottom: clamp(56px, 8vw, 92px);
  }
  @media (max-width: 860px) {
    .jph-grid { grid-template-columns: 1fr; }
  }

  .jph-card {
    background: #fff;
    border: 1px solid var(--border, #e5e7eb);
    border-radius: 20px;
    padding: 26px;
    transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    position: relative;
    overflow: hidden;
  }
  .jph-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--brand, #0d9488), var(--brand-dark, #0f766e));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform .25s ease;
  }
  .jph-card:hover {
    transform: translateY(-3px);
    border-color: #b6d8d3;
    box-shadow: 0 16px 36px rgba(2, 6, 23, 0.08);
  }
  .jph-card:hover::before { transform: scaleX(1); }

  .jph-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 10px;
  }
  .jph-card h2 {
    margin: 0;
    font-family: var(--font-display, "Bricolage Grotesque"), serif;
    font-size: 24px;
    line-height: 1.2;
  }
  .jph-badge {
    flex-shrink: 0;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.4px;
    text-transform: uppercase;
    color: #0f766e;
    background: #d1fae5;
    border-radius: 999px;
    padding: 6px 10px;
  }
  .jph-card p {
    margin: 0 0 14px;
    color: var(--text-soft, #6b7280);
    font-size: 15px;
    line-height: 1.65;
  }

  .jph-list {
    margin: 0 0 18px;
    padding: 0;
    list-style: none;
    display: grid;
    gap: 8px;
    font-size: 14px;
    color: #374151;
  }
  .jph-list li {
    position: relative;
    padding-left: 20px;
  }
  .jph-list li::before {
    content: "→";
    position: absolute;
    left: 0;
    color: var(--brand, #0d9488);
    font-weight: 700;
  }

  .jph-cta {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    background: #0f172a;
    color: #fff;
    padding: 12px 18px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 14px;
  }
  .jph-cta:hover { color: #fff; }
  .jph-cta::after { content: "→"; }

  .jph-bottom {
    border-top: 1px solid var(--border, #e5e7eb);
    padding: 28px 0 clamp(56px, 8vw, 80px);
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    align-items: center;
    justify-content: space-between;
  }
  .jph-bottom p {
    margin: 0;
    color: var(--text-soft, #6b7280);
    font-size: 14px;
  }
  .jph-bottom a {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    border: 1px solid #d1d5db;
    border-radius: 12px;
    padding: 10px 14px;
    font-weight: 600;
    color: #111827;
    background: #fff;
  }
</style>

<div class="jph-page">
  <section class="jph-hero">
    <div class="jph-wrap">
      <div class="jph-kicker">Jak pomagam</div>
      <h1>Wybierz audyt, który rozwiązuje Twój konkretny problem</h1>
      <p>
        Każdy audyt dotyczy innego etapu lejka: ruch, reklamy, konwersja strony albo efektywność social ads.
        Jeśli nie wiesz od czego zacząć — wybierz ten, który najbardziej pasuje do tego, co dziś najbardziej boli.
      </p>
    </div>
  </section>

  <section>
    <div class="jph-wrap">
      <div class="jph-grid">
        <article class="jph-card">
          <div class="jph-card-head">
            <h2>Audyt Google Ads</h2>
            <span class="jph-badge">30 min</span>
          </div>
          <p>Gdy kampanie wydają budżet, ale leadów jest za mało albo są zbyt drogie.</p>
          <ul class="jph-list">
            <li>struktura kampanii i słowa kluczowe</li>
            <li>konwersje, bidding, priorytety na 30 dni</li>
            <li>szybkie oszacowanie gdzie ucieka budżet</li>
          </ul>
          <a class="jph-cta" href="<?php echo esc_url(home_url("/audyt-google-ads/")); ?>" data-cta="hub-jak-pomagam-google-ads" data-cta-section="hub-cards" data-cta-position="card">Przejdź do audytu</a>
        </article>

        <article class="jph-card">
          <div class="jph-card-head">
            <h2>Audyt SEO</h2>
            <span class="jph-badge">45 min</span>
          </div>
          <p>Gdy strona nie rośnie organicznie i chcesz wiedzieć, co blokuje widoczność.</p>
          <ul class="jph-list">
            <li>techniczne bariery i architektura treści</li>
            <li>intencja zapytań i luki w content planie</li>
            <li>priorytety wdrożeń bez przepalania czasu</li>
          </ul>
          <a class="jph-cta" href="<?php echo esc_url(home_url("/audyt-seo/")); ?>" data-cta="hub-jak-pomagam-seo" data-cta-section="hub-cards" data-cta-position="card">Przejdź do audytu</a>
        </article>

        <article class="jph-card">
          <div class="jph-card-head">
            <h2>Audyt Meta Ads</h2>
            <span class="jph-badge">30 min</span>
          </div>
          <p>Gdy reklamy na Facebooku i Instagramie „chodzą”, ale koszty są nieakceptowalne.</p>
          <ul class="jph-list">
            <li>kreacje, ad fatigue, układ kampanii</li>
            <li>audience overlap, Pixel i Conversions API</li>
            <li>plan testów kreacji i lejka retargetingu</li>
          </ul>
          <a class="jph-cta" href="<?php echo esc_url(home_url("/audyt-meta-ads/")); ?>" data-cta="hub-jak-pomagam-meta-ads" data-cta-section="hub-cards" data-cta-position="card">Przejdź do audytu</a>
        </article>

        <article class="jph-card">
          <div class="jph-card-head">
            <h2>Audyt strony www</h2>
            <span class="jph-badge">45 min</span>
          </div>
          <p>Gdy ruch jest, ale strona nie zamienia go w zapytania i sprzedaż.</p>
          <ul class="jph-list">
            <li>hero, komunikat wartości i CTA</li>
            <li>formularze, mobile UX i punkty odpadu</li>
            <li>konkretne poprawki CRO na najbliższe 2 tygodnie</li>
          </ul>
          <a class="jph-cta" href="<?php echo esc_url(home_url("/audyt-strony-www/")); ?>" data-cta="hub-jak-pomagam-strona-www" data-cta-section="hub-cards" data-cta-position="card">Przejdź do audytu</a>
        </article>
      </div>

      <div class="jph-bottom">
        <p>Nie wiesz który audyt wybrać? Opisz problem, wskażę najlepszy punkt startu.</p>
        <a href="<?php echo esc_url(home_url("/kontakt/")); ?>" data-cta="hub-jak-pomagam-kontakt" data-cta-section="hub-footer" data-cta-position="contact">Napisz wiadomość</a>
      </div>
    </div>
  </section>
</div>

<?php get_footer(); ?>
