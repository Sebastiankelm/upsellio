<?php
/**
 * Template Name: Marketing dla branż — strona hub
 *
 * Hub-rozdzielnik dla 8 niszowych landingów branżowych.
 */
get_header();
?>

<style>
  :root {
    --hub-radius: 20px;
    --hub-radius-lg: 28px;
  }

  .hub-page {
    background: var(--bg, #fafaf6);
    color: var(--text, #0d0d0b);
    font-family: var(--font-body, "DM Sans"), system-ui, sans-serif;
    overflow-x: hidden;
  }
  .hub-page * { box-sizing: border-box; }
  .hub-container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
  .hub-hero { position: relative; padding: clamp(60px, 10vw, 120px) 0 clamp(40px, 6vw, 60px); overflow: hidden; }
  .hub-hero::before {
    content: "";
    position: absolute; inset: 0;
    background:
      radial-gradient(ellipse 800px 400px at 30% 0%, rgba(13, 148, 136, 0.08), transparent 70%),
      radial-gradient(ellipse 600px 300px at 90% 60%, rgba(236, 72, 153, 0.05), transparent 70%);
    pointer-events: none;
    z-index: 0;
  }
  .hub-hero-inner { position: relative; z-index: 1; max-width: 880px; margin: 0 auto; text-align: center; }
  .hub-pill {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--brand-soft, #ccfbf1);
    color: var(--brand-dark, #0f766e);
    padding: 8px 16px;
    border-radius: 999px;
    font-size: 12px; font-weight: 700;
    letter-spacing: 0.4px; text-transform: uppercase;
    margin-bottom: 28px;
  }
  .hub-pill::before {
    content: "";
    width: 6px; height: 6px;
    background: var(--brand, #0d9488);
    border-radius: 50%;
    animation: hubPulse 2s ease-in-out infinite;
  }
  @keyframes hubPulse { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.5; transform: scale(0.85); } }
  .hub-hero h1 {
    font-family: var(--font-display, "Bricolage Grotesque"), serif;
    font-size: clamp(40px, 6vw, 76px);
    font-weight: 800;
    line-height: 1.02;
    letter-spacing: -0.025em;
    margin: 0 0 24px;
    color: var(--text);
  }
  .hub-hero h1 em {
    font-style: normal;
    background: linear-gradient(120deg, var(--brand) 0%, #ec4899 50%, #c2410c 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    color: transparent;
  }
  .hub-hero-sub { font-size: clamp(17px, 2vw, 20px); line-height: 1.6; color: var(--text-soft, #7a7a72); margin: 0 auto 36px; max-width: 680px; }
  .hub-hero-stats {
    display: flex; gap: 36px; flex-wrap: wrap;
    justify-content: center;
    padding-top: 16px;
    border-top: 1px solid var(--border);
  }
  @media (max-width: 600px) { .hub-hero-stats { gap: 20px; } }
  .hub-hero-stat { text-align: center; }
  .hub-hero-stat-num { font-family: var(--font-display); font-size: 28px; font-weight: 800; color: var(--text); font-variant-numeric: tabular-nums; line-height: 1; margin-bottom: 6px; }
  .hub-hero-stat-label { font-size: 11px; color: var(--text-soft); font-weight: 600; letter-spacing: 0.3px; text-transform: uppercase; }
  .hub-filter-bar {
    background: var(--surface, #fff);
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
    padding: 20px 0;
    position: sticky;
    top: 0;
    z-index: 10;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    background: rgba(255, 255, 255, 0.92);
  }
  .hub-filter-wrap { display: flex; gap: 12px; flex-wrap: wrap; justify-content: center; }
  .hub-filter {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 10px 18px;
    background: var(--bg, #fafaf6);
    border: 1px solid var(--border);
    border-radius: 999px;
    font-size: 13px; font-weight: 700;
    color: var(--text-muted);
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
  }
  .hub-filter:hover { border-color: var(--brand); color: var(--text); }
  .hub-filter.is-active { background: var(--text); border-color: var(--text); color: #fff; }
  .hub-filter-count { background: rgba(0,0,0,0.08); padding: 1px 7px; border-radius: 999px; font-size: 11px; font-weight: 700; }
  .hub-filter.is-active .hub-filter-count { background: rgba(255,255,255,0.2); color: #fff; }
  .hub-grid-section { padding: clamp(48px, 6vw, 80px) 0; }
  .hub-section-head { margin-bottom: 32px; }
  .hub-section-head h2 { font-family: var(--font-display); font-size: clamp(22px, 2.6vw, 28px); font-weight: 800; margin: 0 0 8px; }
  .hub-section-head p { font-size: 14.5px; color: var(--text-soft); margin: 0; }
  .hub-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 48px; }
  @media (max-width: 900px) { .hub-grid { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 600px) { .hub-grid { grid-template-columns: 1fr; } }
  .hub-card {
    background: var(--surface, #fff);
    border: 1px solid var(--border);
    border-radius: var(--hub-radius-lg);
    padding: 28px;
    transition: all 0.3s var(--ease-out);
    text-decoration: none;
    color: var(--text);
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
  }
  .hub-card::before {
    content: "";
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 4px;
    background: var(--card-color, var(--brand));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.3s var(--ease-out);
  }
  .hub-card:hover { transform: translateY(-6px); box-shadow: 0 24px 48px rgba(15, 23, 42, 0.1); color: var(--text); }
  .hub-card:hover::before { transform: scaleX(1); }
  .hub-card.is-salon { --card-color: #ec4899; --card-bg: #fce7f3; --card-text: #be185d; }
  .hub-card.is-rest { --card-color: #ea580c; --card-bg: #fff7ed; --card-text: #c2410c; }
  .hub-card.is-warsztat { --card-color: #b91c1c; --card-bg: #fee2e2; --card-text: #7f1d1d; }
  .hub-card.is-bud { --card-color: #c2410c; --card-bg: #ffedd5; --card-text: #9a3412; }
  .hub-card.is-stomat { --card-color: #0284c7; --card-bg: #e0f2fe; --card-text: #075985; }
  .hub-card.is-nieruch { --card-color: #047857; --card-bg: #d1fae5; --card-text: #064e3b; }
  .hub-card.is-rachunk { --card-color: #1e40af; --card-bg: #dbeafe; --card-text: #1e3a8a; }
  .hub-card.is-shop { --card-color: #7c3aed; --card-bg: #ede9fe; --card-text: #5b21b6; }
  .hub-card-icon { width: 56px; height: 56px; border-radius: 14px; background: var(--card-bg); color: var(--card-text); display: grid; place-items: center; font-size: 28px; margin-bottom: 18px; }
  .hub-card-tag {
    display: inline-block;
    background: var(--card-bg);
    color: var(--card-text);
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-bottom: 8px;
  }
  .hub-card h3 { font-family: var(--font-display); font-size: 22px; font-weight: 800; line-height: 1.2; margin: 0 0 12px; }
  .hub-card-pitch { font-size: 14px; line-height: 1.6; color: var(--text-soft); margin: 0 0 22px; flex: 1; }
  .hub-card-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; padding: 16px 0; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); margin-bottom: 18px; }
  .hub-card-stat-label { font-size: 10px; font-weight: 700; color: var(--text-soft); letter-spacing: 0.3px; text-transform: uppercase; margin-bottom: 3px; }
  .hub-card-stat-val { font-family: var(--font-display); font-size: 15px; font-weight: 800; color: var(--text); font-variant-numeric: tabular-nums; line-height: 1.2; }
  .hub-card-stat-val small { font-size: 11px; font-weight: 600; color: var(--text-soft); margin-left: 2px; }
  .hub-card-cta { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: var(--card-text); }
  .hub-card-cta::after { content: "→"; margin-left: auto; transition: transform 0.2s ease; }
  .hub-card:hover .hub-card-cta::after { transform: translateX(4px); }
  .hub-positioning { padding: clamp(60px, 8vw, 100px) 0; background: var(--surface, #fff); }
  .hub-positioning-head { max-width: 720px; margin: 0 auto 56px; text-align: center; }
  .hub-positioning-head h2 { font-family: var(--font-display); font-size: clamp(28px, 3.6vw, 40px); font-weight: 800; line-height: 1.2; margin: 0 0 16px; }
  .hub-positioning-head p { font-size: 16px; line-height: 1.65; color: var(--text-soft); margin: 0; }
  .hub-quadrant {
    background: var(--bg, #fafaf6);
    border: 1px solid var(--border);
    border-radius: var(--hub-radius-lg);
    padding: clamp(28px, 4vw, 48px);
    position: relative;
    aspect-ratio: 16 / 11;
    max-width: 900px;
    margin: 0 auto;
  }
  @media (max-width: 700px) { .hub-quadrant { aspect-ratio: 1; padding: 20px; } }
  .hub-quadrant-axis-y {
    position: absolute;
    left: 14px; top: 50%;
    transform: translateY(-50%) rotate(-90deg);
    transform-origin: center;
    font-size: 11px; font-weight: 700;
    color: var(--text-soft);
    letter-spacing: 0.5px;
    text-transform: uppercase;
    white-space: nowrap;
  }
  .hub-quadrant-axis-x {
    position: absolute;
    bottom: 14px; left: 50%;
    transform: translateX(-50%);
    font-size: 11px; font-weight: 700;
    color: var(--text-soft);
    letter-spacing: 0.5px;
    text-transform: uppercase;
    white-space: nowrap;
  }
  .hub-quadrant-grid {
    position: absolute;
    inset: 50px 50px 50px 50px;
    border: 1px dashed rgba(0, 0, 0, 0.08);
    background-image:
      linear-gradient(to right, rgba(0,0,0,0.05) 1px, transparent 1px),
      linear-gradient(to bottom, rgba(0,0,0,0.05) 1px, transparent 1px);
    background-size: calc(100% / 3) calc(100% / 3);
  }
  .hub-quadrant-label {
    position: absolute;
    font-size: 9px;
    font-weight: 700;
    color: var(--text-soft);
    letter-spacing: 0.4px;
    text-transform: uppercase;
    opacity: 0.5;
  }
  .hub-quadrant-label-tl { top: 56px; left: 60px; }
  .hub-quadrant-label-tr { top: 56px; right: 60px; }
  .hub-quadrant-label-bl { bottom: 56px; left: 60px; }
  .hub-quadrant-label-br { bottom: 56px; right: 60px; }
  .hub-bubble {
    position: absolute;
    transform: translate(-50%, -50%);
    background: var(--bubble-color, var(--brand));
    color: #fff;
    border-radius: 999px;
    padding: 8px 14px;
    font-size: 11.5px;
    font-weight: 700;
    white-space: nowrap;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    transition: all 0.2s ease;
    z-index: 2;
    cursor: pointer;
    text-decoration: none;
  }
  .hub-bubble:hover { transform: translate(-50%, -50%) scale(1.08); z-index: 5; color: #fff; }
  @media (max-width: 600px) { .hub-bubble { font-size: 9.5px; padding: 6px 10px; } }
  .hub-approach { padding: clamp(60px, 8vw, 100px) 0; }
  .hub-approach-grid { display: grid; grid-template-columns: 1fr 1.2fr; gap: 56px; align-items: center; }
  @media (max-width: 850px) { .hub-approach-grid { grid-template-columns: 1fr; gap: 40px; } }
  .hub-approach-text h2 { font-family: var(--font-display); font-size: clamp(28px, 3.6vw, 40px); font-weight: 800; line-height: 1.2; margin: 0 0 20px; }
  .hub-approach-text > p { font-size: 16.5px; line-height: 1.7; color: var(--text-muted); margin: 0 0 20px; }
  .hub-approach-rules { list-style: none; margin: 0; padding: 0; counter-reset: rule; }
  .hub-approach-rules li {
    padding: 16px 0 16px 36px;
    position: relative;
    font-size: 15px;
    line-height: 1.6;
    color: var(--text-muted);
    border-top: 1px solid var(--border);
  }
  .hub-approach-rules li:first-child { border-top: 0; padding-top: 0; }
  .hub-approach-rules li:last-child { padding-bottom: 0; }
  .hub-approach-rules li strong { color: var(--text); font-weight: 700; }
  .hub-approach-rules li::before {
    content: counter(rule);
    counter-increment: rule;
    position: absolute; left: 0; top: 18px;
    width: 26px; height: 26px;
    background: var(--brand);
    color: #fff;
    border-radius: 50%;
    display: grid; place-items: center;
    font-size: 12px; font-weight: 800;
    font-variant-numeric: tabular-nums;
  }
  .hub-approach-rules li:first-child::before { top: 4px; }
  .hub-approach-card {
    background: linear-gradient(135deg, var(--text) 0%, #1e293b 100%);
    color: #fff;
    border-radius: var(--hub-radius-lg);
    padding: clamp(32px, 4vw, 48px);
    position: relative;
    overflow: hidden;
  }
  .hub-approach-card::before {
    content: "";
    position: absolute;
    top: -100px; right: -100px;
    width: 300px; height: 300px;
    background: radial-gradient(circle, rgba(13, 148, 136, 0.25) 0%, transparent 60%);
    pointer-events: none;
  }
  .hub-approach-card > * { position: relative; }
  .hub-approach-card-tag {
    display: inline-block;
    background: rgba(13, 148, 136, 0.2);
    color: #5eead4;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-bottom: 14px;
  }
  .hub-approach-card h3 { font-family: var(--font-display); font-size: 22px; font-weight: 800; line-height: 1.25; margin: 0 0 24px; color: #fff; }
  .hub-approach-card-list { list-style: none; margin: 0; padding: 0; }
  .hub-approach-card-list li {
    padding: 12px 0 12px 28px;
    position: relative;
    font-size: 14.5px;
    line-height: 1.55;
    color: rgba(255, 255, 255, 0.85);
    border-top: 1px solid rgba(255, 255, 255, 0.08);
  }
  .hub-approach-card-list li:first-child { border-top: 0; padding-top: 0; }
  .hub-approach-card-list li:last-child { padding-bottom: 0; }
  .hub-approach-card-list li::before { content: "✓"; position: absolute; left: 0; top: 14px; color: #5eead4; font-weight: 800; font-size: 16px; }
  .hub-approach-card-list li:first-child::before { top: 0; }
  .hub-author { padding: clamp(60px, 8vw, 100px) 0; background: var(--surface, #fff); }
  .hub-author-card {
    background: var(--sand, #f5f0e8);
    border-radius: var(--hub-radius-lg);
    padding: clamp(36px, 5vw, 56px);
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 32px;
    align-items: center;
    max-width: 920px;
    margin: 0 auto;
  }
  @media (max-width: 700px) { .hub-author-card { grid-template-columns: 1fr; text-align: center; } }
  .hub-author-photo {
    width: 130px; height: 130px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
    color: #fff;
    display: grid; place-items: center;
    font-family: var(--font-display);
    font-size: 46px; font-weight: 800;
    flex-shrink: 0;
    margin: 0 auto;
  }
  .hub-author-content h3 { font-family: var(--font-display); font-size: 24px; font-weight: 800; margin: 0 0 8px; }
  .hub-author-role { font-size: 14px; font-weight: 600; color: var(--brand-dark); margin: 0 0 16px; }
  .hub-author-content p { font-size: 15.5px; line-height: 1.65; color: var(--text-muted); margin: 0; }
  .hub-final {
    padding: clamp(60px, 9vw, 100px) 0;
    background: linear-gradient(180deg, var(--text, #0d0d0b) 0%, #1e293b 100%);
    color: #fff;
    position: relative;
    overflow: hidden;
  }
  .hub-final::before {
    content: "";
    position: absolute;
    top: -150px; left: 50%;
    transform: translateX(-50%);
    width: 700px; height: 700px;
    background: radial-gradient(circle, rgba(13, 148, 136, 0.18) 0%, transparent 70%);
    pointer-events: none;
  }
  .hub-final-inner { position: relative; max-width: 720px; margin: 0 auto; text-align: center; }
  .hub-final h2 { font-family: var(--font-display); font-size: clamp(28px, 3.6vw, 42px); font-weight: 800; line-height: 1.2; margin: 0 0 16px; color: #fff; }
  .hub-final p { font-size: 17px; line-height: 1.6; color: rgba(255,255,255,0.75); margin: 0 0 32px; }
  .hub-final-buttons { display: flex; gap: 14px; flex-wrap: wrap; justify-content: center; }
  .hub-final-button {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 16px 30px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 15px;
    text-decoration: none;
    transition: all 0.2s ease;
  }
  .hub-final-button.is-primary { background: var(--brand); color: #fff; }
  .hub-final-button.is-primary:hover { background: var(--brand-dark); transform: translateY(-2px); color: #fff; box-shadow: 0 12px 24px rgba(13, 148, 136, 0.3); }
  .hub-final-button.is-secondary { background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.2); }
  .hub-final-button.is-secondary:hover { background: rgba(255,255,255,0.15); color: #fff; }
  .hub-final-button::after { content: "→"; transition: transform 0.2s ease; }
  .hub-final-button:hover::after { transform: translateX(4px); }
</style>

<div class="hub-page" id="wszystkie">
  <section class="hub-hero">
    <div class="hub-container hub-hero-inner">
      <div class="hub-pill">Marketing dla 8 specjalizacji branżowych</div>
      <h1>Marketing dopasowany do <em>Twojej branży</em>, nie generyczny szablon</h1>
      <p class="hub-hero-sub">Restauracja, salon, warsztat, biuro rachunkowe, sklep internetowy — każda branża ma własną ekonomię i własne wąskie gardła w marketingu. Wybierz pakiet zbudowany pod realne potrzeby Twojego biznesu.</p>
      <div class="hub-hero-stats">
        <div class="hub-hero-stat"><div class="hub-hero-stat-num">8</div><div class="hub-hero-stat-label">Specjalizacji branżowych</div></div>
        <div class="hub-hero-stat"><div class="hub-hero-stat-num">2</div><div class="hub-hero-stat-label">Klienci max kwartalnie</div></div>
        <div class="hub-hero-stat"><div class="hub-hero-stat-num">14</div><div class="hub-hero-stat-label">Dni okres wypowiedzenia</div></div>
        <div class="hub-hero-stat"><div class="hub-hero-stat-num">0</div><div class="hub-hero-stat-label">Umów na rok</div></div>
      </div>
    </div>
  </section>

  <nav class="hub-filter-bar" aria-label="Filtruj branże">
    <div class="hub-container">
      <div class="hub-filter-wrap">
        <a href="#wszystkie" class="hub-filter is-active" data-cta="hub-branze-filtr-wszystkie">Wszystkie <span class="hub-filter-count">8</span></a>
        <a href="#b2c-lokalne" class="hub-filter" data-cta="hub-branze-filtr-b2c-lokalne">B2C lokalne <span class="hub-filter-count">4</span></a>
        <a href="#b2c-premium" class="hub-filter" data-cta="hub-branze-filtr-b2c-premium">B2C premium <span class="hub-filter-count">2</span></a>
        <a href="#b2b" class="hub-filter" data-cta="hub-branze-filtr-b2b">B2B usługi <span class="hub-filter-count">1</span></a>
        <a href="#ecommerce" class="hub-filter" data-cta="hub-branze-filtr-ecommerce">E-commerce <span class="hub-filter-count">1</span></a>
      </div>
    </div>
  </nav>

  <section class="hub-grid-section" id="b2c-lokalne">
    <div class="hub-container">
      <div class="hub-section-head">
        <h2>B2C lokalne — usługi w Twoim mieście</h2>
        <p>Klient lokalny szuka usługi blisko, decyzję podejmuje szybko (1-7 dni), wraca regularnie. Marketing skupia się na Google Maps + Google Ads + Meta Ads z geo-targetingiem.</p>
      </div>
      <div class="hub-grid">
        <a href="/marketing-dla-warsztatu-samochodowego" class="hub-card is-warsztat" data-cta="hub-branze-karta-warsztat">
          <div class="hub-card-icon">🔧</div><span class="hub-card-tag">B2C lokalne</span><h3>Warsztat samochodowy</h3>
          <p class="hub-card-pitch">Klient ma awarię, szuka teraz, wybiera najbliższego z dobrymi opiniami. Najtańsze CPC w lokalnym B2C, najszybszy zwrot z reklam.</p>
          <div class="hub-card-stats"><div><div class="hub-card-stat-label">Cena pakietu</div><div class="hub-card-stat-val">1 600<small>zł/mc</small></div></div><div><div class="hub-card-stat-label">Pierwsi klienci</div><div class="hub-card-stat-val">7-21<small>dni</small></div></div></div>
          <span class="hub-card-cta">Zobacz pakiet</span>
        </a>
        <a href="/marketing-dla-salonu-kosmetycznego" class="hub-card is-salon" data-cta="hub-branze-karta-salon">
          <div class="hub-card-icon">💅</div><span class="hub-card-tag">B2C lokalne</span><h3>Salon kosmetyczny</h3>
          <p class="hub-card-pitch">Wypełniony grafik nawet w środy i czwartki. Reklama Google + Meta + Booksy które dowożą klientki, plus mailing przypominający o powrocie.</p>
          <div class="hub-card-stats"><div><div class="hub-card-stat-label">Cena pakietu</div><div class="hub-card-stat-val">1 800<small>zł/mc</small></div></div><div><div class="hub-card-stat-label">Pierwsi klienci</div><div class="hub-card-stat-val">14-30<small>dni</small></div></div></div>
          <span class="hub-card-cta">Zobacz pakiet</span>
        </a>
        <a href="/marketing-dla-restauracji" class="hub-card is-rest" data-cta="hub-branze-karta-restauracja">
          <div class="hub-card-icon">🍝</div><span class="hub-card-tag">B2C lokalne</span><h3>Restauracja</h3>
          <p class="hub-card-pitch">Pełna sala w piątek, wypełniony środowy wieczór. Skupiamy się na dniach które realnie tracisz — wtorki i środy — bez viralowych rolek.</p>
          <div class="hub-card-stats"><div><div class="hub-card-stat-label">Cena pakietu</div><div class="hub-card-stat-val">2 200<small>zł/mc</small></div></div><div><div class="hub-card-stat-label">Pierwsi goście</div><div class="hub-card-stat-val">14-30<small>dni</small></div></div></div>
          <span class="hub-card-cta">Zobacz pakiet</span>
        </a>
        <a href="/marketing-dla-firmy-budowlanej" class="hub-card is-bud" data-cta="hub-branze-karta-budowlana">
          <div class="hub-card-icon">🏗️</div><span class="hub-card-tag">B2C / B2B mix</span><h3>Firma budowlana</h3>
          <p class="hub-card-pitch">Pełen sezon prac od marca do listopada plus zlecenia w martwym sezonie. Filtrujemy ciekawskich od poważnych inwestorów — kalkulator wyceny + brief.</p>
          <div class="hub-card-stats"><div><div class="hub-card-stat-label">Cena pakietu</div><div class="hub-card-stat-val">2 600<small>zł/mc</small></div></div><div><div class="hub-card-stat-label">Pierwsze zlecenia</div><div class="hub-card-stat-val">30-90<small>dni</small></div></div></div>
          <span class="hub-card-cta">Zobacz pakiet</span>
        </a>
      </div>
    </div>
  </section>

  <section class="hub-grid-section" id="b2c-premium" style="padding-top: 0;">
    <div class="hub-container">
      <div class="hub-section-head">
        <h2>B2C premium — wysokowartościowi klienci</h2>
        <p>Branże z bardzo wysoką wartością transakcji i długim cyklem decyzyjnym. Skupiamy się na zaufaniu, opiniach i specjalistycznych zabiegach.</p>
      </div>
      <div class="hub-grid">
        <a href="/marketing-dla-gabinetu-stomatologicznego" class="hub-card is-stomat" data-cta="hub-branze-karta-stomatolog">
          <div class="hub-card-icon">🦷</div><span class="hub-card-tag">B2C premium</span><h3>Gabinet stomatologiczny</h3>
          <p class="hub-card-pitch">Pacjenci na implanty i ortodoncję — nie tylko przeglądy. Zgodność z kodeksem etyki NIL, optymalizacja ZnanyLekarz, frazy zabiegowe zamiast generycznych.</p>
          <div class="hub-card-stats"><div><div class="hub-card-stat-label">Cena pakietu</div><div class="hub-card-stat-val">3 200<small>zł/mc</small></div></div><div><div class="hub-card-stat-label">Pierwsi pacjenci</div><div class="hub-card-stat-val">30-60<small>dni</small></div></div></div>
          <span class="hub-card-cta">Zobacz pakiet</span>
        </a>
        <a href="/marketing-dla-biura-nieruchomosci" class="hub-card is-nieruch" data-cta="hub-branze-karta-nieruchomosci">
          <div class="hub-card-icon">🏡</div><span class="hub-card-tag">B2C premium</span><h3>Biuro nieruchomości</h3>
          <p class="hub-card-pitch">Ekskluzywne oferty od sprzedających i poważni kupujący gotowi na transakcję. Bez walki z portalami o generyczne frazy.</p>
          <div class="hub-card-stats"><div><div class="hub-card-stat-label">Cena pakietu</div><div class="hub-card-stat-val">3 500<small>zł/mc</small></div></div><div><div class="hub-card-stat-label">Pierwsze umowy</div><div class="hub-card-stat-val">60-120<small>dni</small></div></div></div>
          <span class="hub-card-cta">Zobacz pakiet</span>
        </a>
      </div>
    </div>
  </section>

  <section class="hub-grid-section" id="b2b" style="padding-top: 0;">
    <div class="hub-container">
      <div class="hub-section-head">
        <h2>B2B usługi i e-commerce — modele skalowalne</h2>
        <p>Biuro rachunkowe i sklep internetowy to inne wskaźniki: LTV, retention, ROAS — i inna strategia marketingowa.</p>
      </div>
      <div class="hub-grid">
        <a href="/marketing-dla-biura-rachunkowego" class="hub-card is-rachunk" data-cta="hub-branze-karta-rachunkowe">
          <div class="hub-card-icon">📊</div><span class="hub-card-tag">B2B usługi</span><h3>Biuro rachunkowe</h3>
          <p class="hub-card-pitch">Klienci którzy zostają na lata, nie na miesiąc. Skupiamy się na pozycjonowaniu branżowym zamiast generycznego "biuro rachunkowe Warszawa".</p>
          <div class="hub-card-stats"><div><div class="hub-card-stat-label">Cena pakietu</div><div class="hub-card-stat-val">2 800<small>zł/mc</small></div></div><div><div class="hub-card-stat-label">Pierwsi leadzi</div><div class="hub-card-stat-val">30-90<small>dni</small></div></div></div>
          <span class="hub-card-cta">Zobacz pakiet</span>
        </a>
        <a href="/marketing-dla-sklepu-internetowego" class="hub-card is-shop" id="ecommerce" data-cta="hub-branze-karta-ecommerce">
          <div class="hub-card-icon">🛍️</div><span class="hub-card-tag">E-commerce</span><h3>Sklep internetowy</h3>
          <p class="hub-card-pitch">ROAS który realnie się skaluje. Trzy pakiety dla różnych etapów wzrostu (Start / Skala / Scale-up).</p>
          <div class="hub-card-stats"><div><div class="hub-card-stat-label">Cena pakietu</div><div class="hub-card-stat-val">3,2-9,5k<small>zł/mc</small></div></div><div><div class="hub-card-stat-label">Pierwsze efekty</div><div class="hub-card-stat-val">30-60<small>dni</small></div></div></div>
          <span class="hub-card-cta">Zobacz pakiet</span>
        </a>
      </div>
    </div>
  </section>

  <section class="hub-positioning">
    <div class="hub-container">
      <div class="hub-positioning-head">
        <h2>Jak wybrać pakiet pod swoją branżę</h2>
        <p>Mapa pozycjonowania pokazuje gdzie znajduje się każdy pakiet. Kliknij bąbelek żeby przejść do odpowiedniej strony.</p>
      </div>
      <div class="hub-quadrant" role="img" aria-label="Mapa pakietów: oś X to czas do pierwszych klientów, oś Y to budżet miesięczny">
        <span class="hub-quadrant-axis-y">Budżet miesięczny →</span>
        <span class="hub-quadrant-axis-x">Czas do pierwszych klientów →</span>
        <div class="hub-quadrant-grid"></div>
        <span class="hub-quadrant-label hub-quadrant-label-tl">Drogi · Szybki</span>
        <span class="hub-quadrant-label hub-quadrant-label-tr">Drogi · Wolny</span>
        <span class="hub-quadrant-label hub-quadrant-label-bl">Tani · Szybki</span>
        <span class="hub-quadrant-label hub-quadrant-label-br">Tani · Wolny</span>
        <a href="/marketing-dla-warsztatu-samochodowego" class="hub-bubble" style="--bubble-color: #b91c1c; top: 80%; left: 18%;" data-cta="hub-branze-mapa-warsztat">🔧 Warsztat</a>
        <a href="/marketing-dla-salonu-kosmetycznego" class="hub-bubble" style="--bubble-color: #ec4899; top: 73%; left: 30%;" data-cta="hub-branze-mapa-salon">💅 Salon</a>
        <a href="/marketing-dla-restauracji" class="hub-bubble" style="--bubble-color: #ea580c; top: 62%; left: 36%;" data-cta="hub-branze-mapa-restauracja">🍝 Restauracja</a>
        <a href="/marketing-dla-firmy-budowlanej" class="hub-bubble" style="--bubble-color: #c2410c; top: 53%; left: 62%;" data-cta="hub-branze-mapa-budowlana">🏗️ Budowlana</a>
        <a href="/marketing-dla-biura-rachunkowego" class="hub-bubble" style="--bubble-color: #1e40af; top: 47%; left: 72%;" data-cta="hub-branze-mapa-rachunkowe">📊 Rachunkowe</a>
        <a href="/marketing-dla-gabinetu-stomatologicznego" class="hub-bubble" style="--bubble-color: #0284c7; top: 35%; left: 50%;" data-cta="hub-branze-mapa-stomatolog">🦷 Stomatolog</a>
        <a href="/marketing-dla-biura-nieruchomosci" class="hub-bubble" style="--bubble-color: #047857; top: 30%; left: 82%;" data-cta="hub-branze-mapa-nieruchomosci">🏡 Nieruchomości</a>
        <a href="/marketing-dla-sklepu-internetowego" class="hub-bubble" style="--bubble-color: #7c3aed; top: 18%; left: 45%;" data-cta="hub-branze-mapa-ecommerce">🛍️ Sklep</a>
      </div>
    </div>
  </section>

  <section class="hub-approach">
    <div class="hub-container">
      <div class="hub-approach-grid">
        <div class="hub-approach-text">
          <h2>Co łączy wszystkie pakiety</h2>
          <p>Każda branża jest inna, ale metodologia jest ta sama. Sześć zasad, które obowiązują niezależnie od typu biznesu.</p>
          <ol class="hub-approach-rules">
            <li><strong>Pracuję sam, nie deleguję.</strong> Maksymalnie 2-3 nowe firmy kwartalnie.</li>
            <li><strong>Bez umów na 12 miesięcy.</strong> Umowa miesięczna z 14-dniowym okresem wypowiedzenia.</li>
            <li><strong>Pierwsza konsultacja gratis.</strong> 45-60 minut online zanim cokolwiek podpiszesz.</li>
            <li><strong>Strona/landingi w cenie.</strong> Pierwszy miesiąc obejmuje stworzenie nowej strony lub redesign.</li>
            <li><strong>Bez ukrytych kosztów.</strong> Stała cena pakietu + budżet reklamowy.</li>
            <li><strong>Powiem kiedy nie warto.</strong> Jeśli reklamy nie są najlepszą drogą, usłyszysz to ode mnie wprost.</li>
          </ol>
        </div>
        <div class="hub-approach-card">
          <span class="hub-approach-card-tag">W każdym pakiecie</span>
          <h3>Standardowy zakres pracy</h3>
          <ul class="hub-approach-card-list">
            <li>Bezpłatna konsultacja przed startem (45-60 min)</li>
            <li>Nowa strona lub redesign w pierwszym miesiącu</li>
            <li>Konfiguracja Google Analytics 4 + Pixel + atrybucja</li>
            <li>Google Ads + Meta Ads (proporcje per branża)</li>
            <li>Optymalizacja Google Maps + zarządzanie opiniami</li>
            <li>Email automation (welcome + abandoned cart minimum)</li>
            <li>Cotygodniowa optymalizacja kampanii</li>
            <li>Raport miesięczny w jasnym języku</li>
            <li>Pierwsza konsultacja gratis, pierwszy miesiąc 50% taniej</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section class="hub-author">
    <div class="hub-container">
      <div class="hub-author-card">
        <div class="hub-author-photo">SK</div>
        <div class="hub-author-content">
          <h3>Sebastian Kelm</h3>
          <p class="hub-author-role">Konsultant marketingu — 8 specjalizacji branżowych</p>
          <p>Pracuję sam, bez agencji, z maksymalnie 2-3 nowymi firmami kwartalnie. Dla każdej branży mam dopracowaną metodologię. Jeśli Twoja branża nie jest na liście — porozmawiajmy. Czasem da się pomóc, czasem nie.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="hub-final" id="kontakt">
    <div class="hub-container hub-final-inner">
      <h2>Wybierz pakiet pod swoją branżę</h2>
      <p>Każdy pakiet ma własną stronę z opisem, cennikiem i formularzem. Jeśli nie jesteś pewien wyboru — zacznij od bezpłatnego audytu.</p>
      <div class="hub-final-buttons">
        <a href="/audyty" class="hub-final-button is-primary" data-cta="hub-branze-final-audyty">Bezpłatny audyt</a>
        <a href="#wszystkie" class="hub-final-button is-secondary" data-cta="hub-branze-final-wszystkie">Wszystkie pakiety</a>
      </div>
    </div>
  </section>
</div>

<?php get_footer(); ?>
