<?php
/**
 * Template Name: Audyty — strona hub
 *
 * Hub-rozdzielnik dla 4 audytów:
 *  - Audyt Google Ads
 *  - Audyt SEO
 *  - Audyt Meta Ads
 *  - Audyt strony www
 */
get_header();
?>

<style>
  :root {
    --aud-radius: 20px;
    --aud-radius-lg: 28px;
  }

  .aud-page {
    background: var(--bg, #fafaf6);
    color: var(--text, #0d0d0b);
    font-family: var(--font-body, "DM Sans"), system-ui, sans-serif;
    overflow-x: hidden;
  }
  .aud-page * { box-sizing: border-box; }
  .aud-container { max-width: 1180px; margin: 0 auto; padding: 0 24px; }
  .aud-hero { position: relative; padding: clamp(60px, 10vw, 110px) 0 clamp(40px, 6vw, 60px); overflow: hidden; }
  .aud-hero::before {
    content: "";
    position: absolute; inset: 0;
    background:
      radial-gradient(ellipse 800px 400px at 50% 0%, rgba(13, 148, 136, 0.08), transparent 70%),
      radial-gradient(ellipse 500px 300px at 90% 100%, rgba(245, 158, 11, 0.05), transparent 70%);
    pointer-events: none;
    z-index: 0;
  }
  .aud-hero-inner { position: relative; z-index: 1; max-width: 800px; margin: 0 auto; text-align: center; }
  .aud-pill {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--brand-soft, #ccfbf1);
    color: var(--brand-dark, #0f766e);
    padding: 8px 16px;
    border-radius: 999px;
    font-size: 12px; font-weight: 700;
    letter-spacing: 0.4px; text-transform: uppercase;
    margin-bottom: 28px;
  }
  .aud-pill::before {
    content: "";
    width: 6px; height: 6px;
    background: var(--brand, #0d9488);
    border-radius: 50%;
    animation: audPulse 2s ease-in-out infinite;
  }
  @keyframes audPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(0.85); }
  }
  .aud-hero h1 {
    font-family: var(--font-display, "Bricolage Grotesque"), serif;
    font-size: clamp(40px, 6vw, 72px);
    font-weight: 800;
    line-height: 1.02;
    letter-spacing: -0.025em;
    margin: 0 0 24px;
    color: var(--text);
  }
  .aud-hero h1 em {
    font-style: normal;
    background: linear-gradient(120deg, var(--brand) 0%, var(--brand-dark) 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    color: transparent;
  }
  .aud-hero-sub {
    font-size: clamp(17px, 2vw, 20px);
    line-height: 1.6;
    color: var(--text-soft, #7a7a72);
    margin: 0 auto 32px;
    max-width: 640px;
  }
  .aud-hero-meta {
    display: flex; gap: 28px; flex-wrap: wrap;
    justify-content: center;
    color: var(--text-soft);
    font-size: 14px;
  }
  .aud-hero-meta-item { display: inline-flex; align-items: center; gap: 8px; }
  .aud-hero-meta-item::before { content: "✓"; color: var(--brand); font-weight: 800; font-size: 16px; }
  .aud-router { padding: clamp(50px, 7vw, 80px) 0; background: var(--surface, #fff); }
  .aud-router-head { max-width: 700px; margin: 0 auto 48px; text-align: center; }
  .aud-router-eyebrow {
    font-size: 12px; font-weight: 700;
    color: var(--brand-dark);
    letter-spacing: 0.8px; text-transform: uppercase;
    margin-bottom: 12px;
    display: inline-block;
  }
  .aud-router h2 {
    font-family: var(--font-display);
    font-size: clamp(28px, 3.6vw, 40px);
    font-weight: 800;
    line-height: 1.2;
    letter-spacing: -0.018em;
    margin: 0 0 16px;
  }
  .aud-router-intro { font-size: 16px; line-height: 1.65; color: var(--text-soft); margin: 0; }
  .aud-symptoms { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; max-width: 900px; margin: 0 auto; }
  @media (max-width: 700px) { .aud-symptoms { grid-template-columns: 1fr; } }
  .aud-symptom {
    display: flex; gap: 18px; align-items: flex-start;
    padding: 24px;
    background: var(--bg, #fafaf6);
    border: 1px solid var(--border);
    border-radius: var(--aud-radius);
    transition: all 0.25s var(--ease-out);
    text-decoration: none;
    color: var(--text);
  }
  .aud-symptom:hover { border-color: var(--brand); transform: translateY(-2px); box-shadow: var(--shadow-soft); color: var(--text); }
  .aud-symptom-icon {
    flex-shrink: 0;
    width: 44px; height: 44px;
    border-radius: 12px;
    background: var(--brand-soft);
    color: var(--brand-dark);
    display: grid; place-items: center;
    font-size: 22px;
  }
  .aud-symptom-content { flex: 1; min-width: 0; }
  .aud-symptom-quote { font-family: var(--font-display); font-size: 16px; font-weight: 700; line-height: 1.35; margin: 0 0 6px; }
  .aud-symptom-target { font-size: 13px; color: var(--text-soft); margin: 0; }
  .aud-symptom-target strong { color: var(--brand-dark); font-weight: 700; }
  .aud-symptom-arrow { flex-shrink: 0; color: var(--text-soft); font-size: 20px; align-self: center; transition: transform 0.2s ease; }
  .aud-symptom:hover .aud-symptom-arrow { color: var(--brand); transform: translateX(4px); }
  .aud-cards-section { padding: clamp(60px, 8vw, 100px) 0; }
  .aud-cards-head { max-width: 720px; margin: 0 auto 56px; text-align: center; }
  .aud-cards-head h2 { font-family: var(--font-display); font-size: clamp(28px, 3.6vw, 40px); font-weight: 800; line-height: 1.2; letter-spacing: -0.018em; margin: 0 0 16px; }
  .aud-cards-head p { font-size: 16px; line-height: 1.65; color: var(--text-soft); margin: 0; }
  .aud-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
  @media (max-width: 800px) { .aud-cards { grid-template-columns: 1fr; } }
  .aud-card {
    background: var(--surface, #fff);
    border: 1px solid var(--border);
    border-radius: var(--aud-radius-lg);
    padding: clamp(28px, 3.5vw, 40px);
    transition: all 0.3s var(--ease-out);
    position: relative;
    overflow: hidden;
    text-decoration: none;
    color: var(--text);
    display: flex;
    flex-direction: column;
    min-height: 380px;
  }
  .aud-card:hover { transform: translateY(-6px); box-shadow: 0 30px 60px rgba(15, 23, 42, 0.12); color: var(--text); }
  .aud-card::before {
    content: "";
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 5px;
    background: linear-gradient(90deg, var(--card-color, var(--brand)), var(--card-color-dark, var(--brand-dark)));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.4s var(--ease-out);
  }
  .aud-card:hover::before { transform: scaleX(1); }
  .aud-card.is-google { --card-color: #4285f4; --card-color-dark: #1a73e8; --card-soft: #e8f0fe; }
  .aud-card.is-seo { --card-color: var(--brand, #0d9488); --card-color-dark: var(--brand-dark, #0f766e); --card-soft: var(--brand-soft, #ccfbf1); }
  .aud-card.is-meta { --card-color: #1877f2; --card-color-dark: #4f46e5; --card-soft: #e0e7ff; }
  .aud-card.is-www { --card-color: #a855f7; --card-color-dark: #7c3aed; --card-soft: #ede9fe; }
  .aud-card-header { display: flex; align-items: center; gap: 16px; margin-bottom: 20px; }
  .aud-card-icon {
    flex-shrink: 0;
    width: 56px; height: 56px;
    border-radius: 14px;
    background: var(--card-soft);
    color: var(--card-color-dark);
    display: grid; place-items: center;
    font-size: 28px;
    font-weight: 800;
  }
  .aud-card-tag {
    display: inline-block;
    background: var(--card-soft);
    color: var(--card-color-dark);
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-bottom: 6px;
  }
  .aud-card h3 { font-family: var(--font-display); font-size: clamp(22px, 2.5vw, 28px); font-weight: 800; line-height: 1.15; margin: 0; }
  .aud-card-pitch { font-size: 15.5px; line-height: 1.6; color: var(--text-muted); margin: 0 0 22px; }
  .aud-card-checks { list-style: none; margin: 0 0 24px; padding: 18px 0 0; border-top: 1px solid var(--border); flex: 1; }
  .aud-card-checks li { padding: 6px 0 6px 22px; position: relative; font-size: 13.5px; line-height: 1.5; color: var(--text-soft); }
  .aud-card-checks li::before { content: "→"; position: absolute; left: 0; color: var(--card-color); font-weight: 800; }
  .aud-card-meta { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 20px; padding-top: 16px; border-top: 1px solid var(--border); }
  .aud-card-meta-item { font-size: 11.5px; color: var(--text-soft); font-weight: 600; letter-spacing: 0.3px; }
  .aud-card-meta-item strong { color: var(--text); font-family: var(--font-display); font-weight: 800; font-variant-numeric: tabular-nums; }
  .aud-card-cta { display: flex; align-items: center; gap: 8px; align-self: flex-start; color: var(--card-color-dark); font-weight: 700; font-size: 15px; }
  .aud-card-cta::after { content: "→"; transition: transform 0.2s ease; }
  .aud-card:hover .aud-card-cta::after { transform: translateX(4px); }
  .aud-compare { padding: clamp(60px, 8vw, 100px) 0; background: var(--surface, #fff); }
  .aud-compare-head { max-width: 720px; margin: 0 auto 48px; text-align: center; }
  .aud-compare-head h2 { font-family: var(--font-display); font-size: clamp(28px, 3.6vw, 40px); font-weight: 800; line-height: 1.2; letter-spacing: -0.018em; margin: 0 0 16px; }
  .aud-compare-head p { font-size: 16px; line-height: 1.65; color: var(--text-soft); margin: 0; }
  .aud-table-wrap { background: var(--bg, #fafaf6); border: 1px solid var(--border); border-radius: var(--aud-radius-lg); padding: 8px; overflow-x: auto; }
  .aud-table { width: 100%; min-width: 720px; border-collapse: separate; border-spacing: 0; }
  .aud-table thead th { padding: 16px 14px; text-align: left; font-family: var(--font-display); font-size: 13px; font-weight: 700; color: var(--text-soft); letter-spacing: 0.3px; text-transform: uppercase; background: transparent; }
  .aud-table thead th:first-child { text-align: left; width: 30%; }
  .aud-table tbody td { padding: 14px; font-size: 14px; color: var(--text-muted); border-top: 1px solid var(--border); background: var(--surface); }
  .aud-table tbody tr:first-child td { border-top: 0; }
  .aud-table tbody tr:first-child td:first-child { border-top-left-radius: 12px; }
  .aud-table tbody tr:first-child td:last-child { border-top-right-radius: 12px; }
  .aud-table tbody tr:last-child td:first-child { border-bottom-left-radius: 12px; }
  .aud-table tbody tr:last-child td:last-child { border-bottom-right-radius: 12px; }
  .aud-table tbody td:first-child { font-family: var(--font-display); font-weight: 700; color: var(--text); }
  .aud-table-cell-good { color: var(--success, #15803d); font-weight: 700; }
  .aud-table-cell-meh { color: var(--accent, #f97316); font-weight: 600; }
  .aud-how { padding: clamp(60px, 8vw, 100px) 0; }
  .aud-how-grid { display: grid; grid-template-columns: 1fr 1.1fr; gap: 56px; align-items: center; }
  @media (max-width: 850px) { .aud-how-grid { grid-template-columns: 1fr; gap: 40px; } }
  .aud-how-text h2 { font-family: var(--font-display); font-size: clamp(28px, 3.6vw, 40px); font-weight: 800; line-height: 1.2; letter-spacing: -0.018em; margin: 0 0 20px; }
  .aud-how-text-intro { font-size: 16px; line-height: 1.7; color: var(--text-soft); margin: 0 0 28px; }
  .aud-how-promises { list-style: none; margin: 0; padding: 0; }
  .aud-how-promises li { padding: 14px 0 14px 36px; position: relative; font-size: 15px; line-height: 1.55; color: var(--text-muted); border-top: 1px solid var(--border); }
  .aud-how-promises li:first-child { border-top: 0; padding-top: 0; }
  .aud-how-promises li:last-child { padding-bottom: 0; }
  .aud-how-promises li::before {
    content: "✓";
    position: absolute; left: 0; top: 18px;
    width: 24px; height: 24px;
    background: var(--brand);
    color: #fff;
    border-radius: 50%;
    display: grid; place-items: center;
    font-size: 13px; font-weight: 800;
  }
  .aud-how-promises li:first-child::before { top: 4px; }
  .aud-how-visual {
    background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
    color: #fff;
    border-radius: var(--aud-radius-lg);
    padding: clamp(32px, 4vw, 48px);
    position: relative;
    overflow: hidden;
  }
  .aud-how-visual::before {
    content: "";
    position: absolute;
    top: -100px; right: -100px;
    width: 300px; height: 300px;
    background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 60%);
    pointer-events: none;
  }
  .aud-how-visual > * { position: relative; }
  .aud-stat-row { display: flex; justify-content: space-between; align-items: baseline; padding: 16px 0; border-bottom: 1px solid rgba(255,255,255,0.15); }
  .aud-stat-row:last-child { border-bottom: 0; }
  .aud-stat-row-label { font-size: 13px; color: rgba(255,255,255,0.85); font-weight: 600; }
  .aud-stat-row-value { font-family: var(--font-display); font-size: clamp(20px, 2.4vw, 26px); font-weight: 800; color: #fff; font-variant-numeric: tabular-nums; }
  .aud-stat-row-value small { font-size: 13px; font-weight: 600; opacity: 0.75; margin-left: 4px; }
  .aud-how-visual-footer { margin-top: 16px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.2); font-size: 13px; color: rgba(255,255,255,0.7); line-height: 1.5; text-align: center; }
  .aud-faq { padding: clamp(60px, 8vw, 100px) 0; background: var(--surface, #fff); }
  .aud-faq-head { max-width: 720px; margin: 0 auto 48px; text-align: center; }
  .aud-faq-head h2 { font-family: var(--font-display); font-size: clamp(28px, 3.6vw, 40px); font-weight: 800; line-height: 1.2; margin: 0 0 16px; }
  .aud-faq-head p { font-size: 16px; color: var(--text-soft); margin: 0; }
  .aud-faq-list { max-width: 760px; margin: 0 auto; }
  .aud-faq-item { background: var(--bg, #fafaf6); border: 1px solid var(--border); border-radius: var(--aud-radius); margin-bottom: 12px; overflow: hidden; transition: border-color 0.2s ease; }
  .aud-faq-item:hover { border-color: var(--brand); }
  .aud-faq-item[open] { border-color: var(--brand); box-shadow: var(--shadow-sm); }
  .aud-faq-item summary {
    padding: 22px 28px;
    cursor: pointer;
    list-style: none;
    display: flex; justify-content: space-between; align-items: center; gap: 16px;
    font-family: var(--font-display);
    font-size: 16px; font-weight: 700;
    line-height: 1.4;
    color: var(--text);
    user-select: none;
  }
  .aud-faq-item summary::-webkit-details-marker { display: none; }
  .aud-faq-item summary::after {
    content: "+";
    flex-shrink: 0;
    width: 28px; height: 28px;
    border-radius: 50%;
    background: var(--brand-soft);
    color: var(--brand-dark);
    display: grid; place-items: center;
    font-size: 18px; font-weight: 400;
    transition: all 0.2s ease;
  }
  .aud-faq-item[open] summary::after { content: "−"; background: var(--brand); color: #fff; transform: rotate(180deg); }
  .aud-faq-content { padding: 0 28px 24px; color: var(--text-soft); font-size: 15px; line-height: 1.7; }
  .aud-faq-content p { margin: 0 0 12px; }
  .aud-faq-content p:last-child { margin: 0; }
  .aud-cta {
    padding: clamp(60px, 9vw, 100px) 0;
    background: linear-gradient(180deg, var(--text, #0d0d0b) 0%, #1e293b 100%);
    color: #fff;
    position: relative;
    overflow: hidden;
  }
  .aud-cta::before {
    content: "";
    position: absolute;
    top: -100px; left: 50%;
    transform: translateX(-50%);
    width: 600px; height: 600px;
    background: radial-gradient(circle, rgba(13, 148, 136, 0.18) 0%, transparent 70%);
    pointer-events: none;
  }
  .aud-cta-inner { position: relative; max-width: 640px; margin: 0 auto; text-align: center; }
  .aud-cta h2 { font-family: var(--font-display); font-size: clamp(28px, 3.6vw, 40px); font-weight: 800; line-height: 1.2; margin: 0 0 16px; color: #fff; }
  .aud-cta p { font-size: 17px; line-height: 1.6; color: rgba(255,255,255,0.7); margin: 0 0 32px; }
  .aud-cta-buttons { display: flex; gap: 14px; flex-wrap: wrap; justify-content: center; }
  .aud-cta-button {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 16px 28px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 15px;
    text-decoration: none;
    transition: all 0.2s ease;
  }
  .aud-cta-button.is-primary { background: var(--brand, #0d9488); color: #fff; }
  .aud-cta-button.is-primary:hover { background: var(--brand-dark); transform: translateY(-2px); color: #fff; }
  .aud-cta-button.is-secondary { background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.2); }
  .aud-cta-button.is-secondary:hover { background: rgba(255,255,255,0.15); color: #fff; }
  .aud-cta-button::after { content: "→"; transition: transform 0.2s ease; }
  .aud-cta-button:hover::after { transform: translateX(4px); }
</style>

<div class="aud-page">
  <section class="aud-hero">
    <div class="aud-container aud-hero-inner">
      <div class="aud-pill">Cztery bezpłatne audyty</div>
      <h1>Sprawdzę co <em>realnie nie działa</em> w Twoim marketingu</h1>
      <p class="aud-hero-sub">Cztery różne audyty dla czterech różnych problemów. Nie udaję, że jeden audyt rozwiąże wszystko — bo nie. Wybierz ten, który pasuje do Twojej sytuacji. Konkretne wnioski, bez 200-stronicowego raportu.</p>
      <div class="aud-hero-meta">
        <span class="aud-hero-meta-item">100% bezpłatnie</span>
        <span class="aud-hero-meta-item">30-45 minut online</span>
        <span class="aud-hero-meta-item">5 audytów tygodniowo</span>
        <span class="aud-hero-meta-item">Wnioski 24h po rozmowie</span>
      </div>
    </div>
  </section>

  <section class="aud-router">
    <div class="aud-container">
      <div class="aud-router-head">
        <span class="aud-router-eyebrow">Pomocnik wyboru</span>
        <h2>Po objawie do diagnozy</h2>
        <p class="aud-router-intro">Większość firm nie wie który audyt im potrzebny — bo nie wie który kanał szwankuje. Sprawdź swoje objawy. Każde poniższe zdanie prowadzi do konkretnego audytu.</p>
      </div>

      <div class="aud-symptoms">
        <a href="/audyt-google-ads" class="aud-symptom" data-cta="hub-audyty-objaw-google-ads">
          <div class="aud-symptom-icon">💸</div>
          <div class="aud-symptom-content">
            <p class="aud-symptom-quote">"Wydaję X tysięcy w Google Ads i nie wiem co mi się zwraca."</p>
            <p class="aud-symptom-target"><strong>Audyt Google Ads</strong> — sprawdzimy strukturę, słowa kluczowe, atrybucję</p>
          </div>
          <span class="aud-symptom-arrow">→</span>
        </a>
        <a href="/audyt-seo" class="aud-symptom" data-cta="hub-audyty-objaw-seo">
          <div class="aud-symptom-icon">📉</div>
          <div class="aud-symptom-content">
            <p class="aud-symptom-quote">"Strona istnieje rok, ale Google nie chce mnie pokazywać."</p>
            <p class="aud-symptom-target"><strong>Audyt SEO</strong> — sprawdzimy techniczne, treści, linki zewnętrzne</p>
          </div>
          <span class="aud-symptom-arrow">→</span>
        </a>
        <a href="/audyt-meta-ads" class="aud-symptom" data-cta="hub-audyty-objaw-meta-ads">
          <div class="aud-symptom-icon">📱</div>
          <div class="aud-symptom-content">
            <p class="aud-symptom-quote">"Reklamy na Facebooku palą budżet, sprzedaży nie przybywa."</p>
            <p class="aud-symptom-target"><strong>Audyt Meta Ads</strong> — sprawdzimy kreację, struktury, Pixel, retargeting</p>
          </div>
          <span class="aud-symptom-arrow">→</span>
        </a>
        <a href="/audyt-strony-www" class="aud-symptom" data-cta="hub-audyty-objaw-strona-www">
          <div class="aud-symptom-icon">🛒</div>
          <div class="aud-symptom-content">
            <p class="aud-symptom-quote">"Ruch na stronie jest, ale nikt nie wypełnia formularza."</p>
            <p class="aud-symptom-target"><strong>Audyt strony www</strong> — sprawdzimy konwersję, UX, ścieżki klienta</p>
          </div>
          <span class="aud-symptom-arrow">→</span>
        </a>
      </div>
    </div>
  </section>

  <section class="aud-cards-section">
    <div class="aud-container">
      <div class="aud-cards-head">
        <h2>Cztery audyty, cztery różne problemy</h2>
        <p>Każdy audyt ma własną metodologię, własny zestaw narzędzi i własny kontekst. Wybierz na podstawie tego co realnie chcesz zmienić.</p>
      </div>
      <div class="aud-cards">
        <a href="/audyt-google-ads" class="aud-card is-google" data-cta="hub-audyty-karta-google-ads">
          <div class="aud-card-header">
            <div class="aud-card-icon">G</div>
            <div><span class="aud-card-tag">Google Ads</span><h3>Audyt Google Ads</h3></div>
          </div>
          <p class="aud-card-pitch">Wchodzę na Twoje konto reklamowe, sprawdzam strukturę kampanii, słowa kluczowe, jakość ruchu, atrybucję. Pokazuję na żywo trzy największe miejsca gdzie palisz pieniądze.</p>
          <ul class="aud-card-checks">
            <li>Audyt struktury kampanii i grup reklam</li>
            <li>Analiza Quality Score i rankingów reklam</li>
            <li>Słowa kluczowe negatywne (oszczędność 20-40%)</li>
            <li>Atrybucja w GA4 vs samoraportowanie Google</li>
            <li>Konfiguracja konwersji + Enhanced Conversions</li>
          </ul>
          <div class="aud-card-meta">
            <span class="aud-card-meta-item">Czas: <strong>30 min</strong></span>
            <span class="aud-card-meta-item">Wnioski: <strong>24h</strong></span>
            <span class="aud-card-meta-item">Cykl: <strong>3-7 dni</strong></span>
          </div>
          <span class="aud-card-cta">Umów audyt Google Ads</span>
        </a>

        <a href="/audyt-seo" class="aud-card is-seo" data-cta="hub-audyty-karta-seo">
          <div class="aud-card-header">
            <div class="aud-card-icon">S</div>
            <div><span class="aud-card-tag">Search Engine</span><h3>Audyt SEO</h3></div>
          </div>
          <p class="aud-card-pitch">Trzy obszary: SEO techniczne (czy Google może Cię zindeksować), treści i frazy kluczowe (czy pasują do intencji klienta), linki zewnętrzne (czy masz autorytet do walki o pozycje).</p>
          <ul class="aud-card-checks">
            <li>Indexation w Google Search Console</li>
            <li>Core Web Vitals + speed audit</li>
            <li>Mapa fraz kluczowych vs intencja klienta</li>
            <li>Linkowanie wewnętrzne i kanibalizacja</li>
            <li>Profil linków zewnętrznych + toxic backlinks</li>
          </ul>
          <div class="aud-card-meta">
            <span class="aud-card-meta-item">Czas: <strong>45 min</strong></span>
            <span class="aud-card-meta-item">Wnioski: <strong>24h</strong></span>
            <span class="aud-card-meta-item">Cykl: <strong>5-10 dni</strong></span>
          </div>
          <span class="aud-card-cta">Umów audyt SEO</span>
        </a>

        <a href="/audyt-meta-ads" class="aud-card is-meta" data-cta="hub-audyty-karta-meta-ads">
          <div class="aud-card-header">
            <div class="aud-card-icon">M</div>
            <div><span class="aud-card-tag">Facebook + Instagram</span><h3>Audyt Meta Ads</h3></div>
          </div>
          <p class="aud-card-pitch">W Meta wygrywa kreacja, nie targetowanie. 80% kont które widzę ma poprawne kampanie ale fatalne reklamy. Sprawdzam wszystko klatka po klatce: hook, CTR, ad fatigue, Pixel.</p>
          <ul class="aud-card-checks">
            <li>Audyt kreacji (hook, format, ad fatigue)</li>
            <li>Struktura kampanii (CBO/ABO, Advantage+)</li>
            <li>Pixel + Conversions API + EMQ score</li>
            <li>Lookalike audiences i audience overlap</li>
            <li>Lejek: zimno → ciepło → retargeting</li>
          </ul>
          <div class="aud-card-meta">
            <span class="aud-card-meta-item">Czas: <strong>30 min</strong></span>
            <span class="aud-card-meta-item">Wnioski: <strong>24h</strong></span>
            <span class="aud-card-meta-item">Cykl: <strong>3-7 dni</strong></span>
          </div>
          <span class="aud-card-cta">Umów audyt Meta Ads</span>
        </a>

        <a href="/audyt-strony-www" class="aud-card is-www" data-cta="hub-audyty-karta-strona-www">
          <div class="aud-card-header">
            <div class="aud-card-icon">W</div>
            <div><span class="aud-card-tag">UX + CRO</span><h3>Audyt strony www</h3></div>
          </div>
          <p class="aud-card-pitch">Tu nie chodzi o ruch — chodzi o to, dlaczego ruch który masz, nie konwertuje. Patrzę na stronę krok po kroku z perspektywy klienta, na desktop i mobile, identyfikuję blokady.</p>
          <ul class="aud-card-checks">
            <li>Pierwsze 5 sekund (above the fold)</li>
            <li>Komunikat wartości i copy</li>
            <li>CTA i ścieżka konwersji</li>
            <li>Formularze i koszyki (najbardziej zaniedbane)</li>
            <li>Mobile UX (60% Twojego ruchu)</li>
          </ul>
          <div class="aud-card-meta">
            <span class="aud-card-meta-item">Czas: <strong>45 min</strong></span>
            <span class="aud-card-meta-item">Wnioski: <strong>24h</strong></span>
            <span class="aud-card-meta-item">Cykl: <strong>5-10 dni</strong></span>
          </div>
          <span class="aud-card-cta">Umów audyt strony</span>
        </a>
      </div>
    </div>
  </section>

  <section class="aud-compare">
    <div class="aud-container">
      <div class="aud-compare-head">
        <h2>Który audyt wybrać? Tabela porównawcza</h2>
        <p>Jeśli któryś objaw masz wyraźny — idź prosto na właściwy audyt. Jeśli więcej niż jeden — zrobimy je sekwencyjnie, jeden za drugim, w odstępie 30 dni.</p>
      </div>
      <div class="aud-table-wrap">
        <table class="aud-table">
          <thead><tr><th>Audyt</th><th>Idealnie jeśli...</th><th>Wymóg dostępu</th><th>Cykl</th></tr></thead>
          <tbody>
            <tr><td>Google Ads</td><td>Płacisz za reklamy 1k+/mies, ROAS spada</td><td><span class="aud-table-cell-good">Read-Only</span> Google Ads</td><td>3-7 dni</td></tr>
            <tr><td>SEO</td><td>Strona 6+ mies., ruch nie rośnie</td><td><span class="aud-table-cell-good">Read-Only</span> Search Console</td><td>5-10 dni</td></tr>
            <tr><td>Meta Ads</td><td>Sklep e-com z FB/IG ads, wysokie CPL</td><td><span class="aud-table-cell-good">Analyst</span> Business Manager</td><td>3-7 dni</td></tr>
            <tr><td>Strona www</td><td>Masz ruch, ale niska konwersja na lead/sprzedaż</td><td><span class="aud-table-cell-meh">Bez dostępów</span> (tylko link)</td><td>5-10 dni</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <section class="aud-how">
    <div class="aud-container">
      <div class="aud-how-grid">
        <div class="aud-how-text">
          <h2>Jak działa każdy z moich audytów</h2>
          <p class="aud-how-text-intro">Niezależnie który audyt wybierzesz, proces jest taki sam: konkretne wnioski w 30-45 minut, bez zarzucania Cię terminami branżowymi, bez 200-stronicowego raportu. Po rozmowie dostajesz email z trzema głównymi rekomendacjami — to wszystko czego potrzebujesz.</p>
          <ul class="aud-how-promises">
            <li>Robię max 5 audytów tygodniowo — zawsze osobiście, nie deleguję juniorom</li>
            <li>Audyt jest <strong>bezpłatny</strong>, ale to nie pretekst do wciskania pakietu — z 10 audytów 2-3 firmy pytają o stałą współpracę</li>
            <li>Nie obiecuję wzrostów X% — mówię konkretne kroki które warto wdrożyć i ile to powinno kosztować</li>
            <li>Po audycie kasuję dane jeśli nie nawiążemy współpracy</li>
            <li>Jeśli w 30 minut zorientuję się, że Twój problem jest gdzie indziej — powiem Ci to wprost</li>
          </ul>
        </div>
        <div class="aud-how-visual">
          <div class="aud-stat-row"><span class="aud-stat-row-label">Audytów tygodniowo</span><span class="aud-stat-row-value">5<small>max</small></span></div>
          <div class="aud-stat-row"><span class="aud-stat-row-label">Czas trwania rozmowy</span><span class="aud-stat-row-value">30-45<small>min</small></span></div>
          <div class="aud-stat-row"><span class="aud-stat-row-label">Wnioski na piśmie</span><span class="aud-stat-row-value">24h<small>po rozmowie</small></span></div>
          <div class="aud-stat-row"><span class="aud-stat-row-label">Konwersja audyt → współpraca</span><span class="aud-stat-row-value">~25%</span></div>
          <div class="aud-stat-row"><span class="aud-stat-row-label">Cena</span><span class="aud-stat-row-value">0 zł<small>zawsze</small></span></div>
          <div class="aud-how-visual-footer">Audyty robię osobiście — Sebastian Kelm, konsultant marketingu B2B i B2C</div>
        </div>
      </div>
    </div>
  </section>

  <section class="aud-faq">
    <div class="aud-container">
      <div class="aud-faq-head"><h2>Pytania o audyty</h2><p>Najczęstsze pytania zanim umówisz spotkanie.</p></div>
      <div class="aud-faq-list">
        <details class="aud-faq-item"><summary>Naprawdę bezpłatne? Jaki jest haczyk?</summary><div class="aud-faq-content"><p>Nie ma haczyka. Robię audyty z dwóch powodów: (1) chcę pokazać jak pracuję — to lepsza wizytówka niż portfolio, (2) z 10 audytów 2-3 firmy decydują się na stałą współpracę po wdrożeniu wniosków.</p><p>Nie sprzedaję na audycie — sprzedaję wyniki. Audyt to wyniki, których jeszcze nie masz.</p></div></details>
        <details class="aud-faq-item"><summary>Mogę zrobić więcej niż jeden audyt?</summary><div class="aud-faq-content"><p>Tak, ale <strong>nie naraz</strong>. Robię je sekwencyjnie, w odstępie 30 dni. Powód: po pierwszym audycie dostajesz wnioski do wdrożenia. Drugi audyt 30 dni później jest dużo bardziej wartościowy, bo widzę co już wdrożyłeś i co dalej naprawić.</p><p>Najczęstsza para: <strong>Audyt strony www + Audyt Google Ads</strong> (sklepy e-com), albo <strong>Audyt SEO + Audyt strony www</strong> (firmy usługowe).</p></div></details>
        <details class="aud-faq-item"><summary>Co jeśli nie wiem który audyt mi potrzebny?</summary><div class="aud-faq-content"><p>Sprawdź sekcję "Po objawie do diagnozy" wyżej. Jeśli nadal nie jesteś pewien — wypełnij dowolny formularz i opisz w polu "Co Cię trapi". Odpowiem mailem czy ten audyt to dobry wybór, czy lepiej zrobić inny.</p><p>Czasem rekomenduję inny audyt niż ten o który prosisz — bo widzę objaw którego nie wymieniłeś. To normalna część rozmowy, nie wciskanie kitu.</p></div></details>
        <details class="aud-faq-item"><summary>Czy audyt to to samo co konsultacja?</summary><div class="aud-faq-content"><p>Nie. Konsultacja to 60 minut rozmowy o Twoim biznesie — bez wcześniejszego patrzenia na Twoje konta i kampanie. Audyt to <strong>analiza danych przed rozmową</strong> (60-90 min mojej pracy zanim się spotkamy), a potem 30-45 minut konkretu na żywo.</p><p>Audyt jest dużo bardziej wartościowy — i to dlatego jest bezpłatny. To moja inwestycja w jakość kontaktu.</p></div></details>
        <details class="aud-faq-item"><summary>Jakie dostępy musisz mieć do moich kont?</summary><div class="aud-faq-content"><p>Tylko <strong>Read-Only</strong> (analityk, czytelnik). Nigdy nie potrzebuję praw edycji. Dla każdego audytu konkretnie:</p><p>• <strong>Google Ads</strong>: rola "Tylko odczyt"<br>• <strong>SEO</strong>: rola "Pełny użytkownik" w Search Console + GA4 "Czytelnik"<br>• <strong>Meta Ads</strong>: rola "Analityk" w Business Managerze<br>• <strong>Strona www</strong>: nic, tylko adres URL + GA4 jeśli masz</p><p>Po audycie usuwasz mnie tym samym kliknięciem którym dodałeś.</p></div></details>
        <details class="aud-faq-item"><summary>Co dostaję na piśmie po audycie?</summary><div class="aud-faq-content"><p>1-2 strony A4 z trzema głównymi problemami, krokami do wdrożenia w kolejności priorytetu, szacowanym czasem i kosztem każdej zmiany. Wysyłam mailem 24h po rozmowie.</p><p>Bez 50-stronicowego raportu, bez tabel Excela. Dokument do przeczytania w 5 minut, decyzja na podstawie konkretu.</p></div></details>
      </div>
    </div>
  </section>

  <section class="aud-cta">
    <div class="aud-container aud-cta-inner">
      <h2>Wybierz audyt który pasuje do Twojej sytuacji</h2>
      <p>Każdy audyt ma własną stronę z formularzem i pełnym opisem procesu. Klikasz, wypełniasz, dostajesz odpowiedź w 24h.</p>
      <div class="aud-cta-buttons">
        <a href="/audyt-google-ads" class="aud-cta-button is-primary" data-cta="hub-audyty-final-google-ads">Audyt Google Ads</a>
        <a href="/audyt-seo" class="aud-cta-button is-secondary" data-cta="hub-audyty-final-seo">Audyt SEO</a>
        <a href="/audyt-meta-ads" class="aud-cta-button is-secondary" data-cta="hub-audyty-final-meta-ads">Audyt Meta Ads</a>
        <a href="/audyt-strony-www" class="aud-cta-button is-secondary" data-cta="hub-audyty-final-strona-www">Audyt strony www</a>
      </div>
    </div>
  </section>
</div>

<?php get_footer(); ?>
