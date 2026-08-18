<?php
/**
 * Template Name: Marketing dla salonu kosmetycznego — landing
 *
 * Niszowy landing dla branży beauty/salonów kosmetycznych.
 *
 * Frazy kluczowe (KWP):
 *  - reklama salonu kosmetycznego (100-1k, 2,70-15,57 zł CPC)
 *  - marketing salonu beauty (10-100)
 *  - strona internetowa salonu kosmetycznego (10-100, 2,29-7,47 zł CPC)
 *  - reklama na okno salonu kosmetycznego (10-100, 0,96-3,84 zł CPC)
 *
 * Różnica względem audytów:
 *  - Klient B2C/SMB - nie kupuje "audytu", kupuje "więcej klientek"
 *  - Wizualna branża - mockupy Instagrama, kalendarz Booksy
 *  - Konkretne metryki branżowe: zapełnienie grafiku, no-show rate, LTV
 *  - Cykl decyzyjny krótszy - można zaproponować od razu pakiet startowy
 */
get_header();
?>

<style>
  :root {
    --sk-radius: 20px;
    --sk-radius-lg: 28px;
    --sk-pink: #ec4899;
    --sk-pink-soft: #fce7f3;
  }

  .sk-page {
    background: var(--bg, #fafaf6);
    color: var(--text, #0d0d0b);
    font-family: var(--font-body, "DM Sans"), system-ui, sans-serif;
    overflow-x: hidden;
  }
  .sk-page * { box-sizing: border-box; }
  .sk-container { max-width: 1100px; margin: 0 auto; padding: 0 24px; }

  .sk-hero {
    position: relative;
    padding: clamp(60px, 10vw, 100px) 0 clamp(50px, 8vw, 80px);
    overflow: hidden;
  }
  .sk-hero::before {
    content: "";
    position: absolute; inset: 0;
    background:
      radial-gradient(ellipse 700px 400px at 80% 20%, rgba(236, 72, 153, 0.08), transparent),
      radial-gradient(ellipse 500px 300px at 10% 80%, rgba(13, 148, 136, 0.06), transparent);
    pointer-events: none;
    z-index: 0;
  }
  .sk-hero-inner { position: relative; z-index: 1; }

  .sk-hero-grid {
    display: grid;
    grid-template-columns: 1.15fr 1fr;
    gap: 56px;
    align-items: center;
  }
  @media (max-width: 900px) {
    .sk-hero-grid { grid-template-columns: 1fr; gap: 40px; }
  }

  .sk-pill {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--sk-pink-soft);
    color: #be185d;
    padding: 8px 16px;
    border-radius: 999px;
    font-size: 12px; font-weight: 700;
    letter-spacing: 0.4px; text-transform: uppercase;
    margin-bottom: 24px;
  }
  .sk-pill::before {
    content: "";
    width: 6px; height: 6px;
    background: var(--sk-pink);
    border-radius: 50%;
    animation: skPulse 2s ease-in-out infinite;
  }
  @keyframes skPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(0.85); }
  }

  .sk-hero h1 {
    font-family: var(--font-display, "Bricolage Grotesque"), serif;
    font-size: clamp(36px, 5.5vw, 60px);
    font-weight: 800;
    line-height: 1.04;
    letter-spacing: -0.025em;
    margin: 0 0 24px;
    color: var(--text);
  }
  .sk-hero h1 em {
    font-style: normal;
    background: linear-gradient(120deg, var(--sk-pink) 0%, var(--brand) 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    color: transparent;
  }

  .sk-hero-sub {
    font-size: clamp(16px, 2vw, 19px);
    line-height: 1.6;
    color: var(--text-soft, #7a7a72);
    margin: 0 0 32px;
    max-width: 540px;
  }

  .sk-hero-cta-row {
    display: flex; gap: 16px; flex-wrap: wrap;
    margin-bottom: 32px;
  }
  .sk-cta-primary {
    display: inline-flex; align-items: center; gap: 10px;
    background: var(--text, #0d0d0b);
    color: #fff;
    padding: 18px 32px;
    border-radius: 14px;
    font-size: 16px; font-weight: 700;
    text-decoration: none;
    transition: all 0.2s var(--ease-out, cubic-bezier(0.16, 1, 0.3, 1));
    box-shadow: 0 4px 16px rgba(13, 13, 11, 0.15);
  }
  .sk-cta-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(13, 13, 11, 0.2);
    color: #fff;
  }
  .sk-cta-primary::after { content: "→"; transition: transform 0.2s ease; }
  .sk-cta-primary:hover::after { transform: translateX(4px); }

  .sk-cta-secondary {
    display: inline-flex; align-items: center; gap: 8px;
    color: var(--text);
    padding: 18px 24px;
    font-size: 15px; font-weight: 600;
    text-decoration: none;
    border-bottom: 2px solid transparent;
    transition: all 0.15s ease;
  }
  .sk-cta-secondary:hover { border-bottom-color: var(--sk-pink); color: var(--sk-pink); }

  .sk-trust-row {
    display: flex; gap: 24px; flex-wrap: wrap;
    color: var(--text-soft, #7a7a72);
    font-size: 14px;
  }
  .sk-trust-item { display: inline-flex; align-items: center; gap: 8px; }
  .sk-trust-item::before {
    content: "✓"; color: var(--sk-pink); font-weight: 800; font-size: 16px;
  }

  .sk-calendar {
    background: #fff;
    border: 1px solid var(--border, #e8e8e0);
    border-radius: var(--sk-radius);
    padding: 24px;
    box-shadow: var(--shadow, 0 22px 60px rgba(15, 23, 42, 0.1));
    position: relative;
    transform: rotate(0.5deg);
  }
  .sk-calendar::before {
    content: "Pierwszy tydzień maja";
    position: absolute;
    top: -12px; right: 24px;
    background: linear-gradient(135deg, var(--sk-pink) 0%, #be185d 100%);
    color: #fff;
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.5px; text-transform: uppercase;
  }

  .sk-calendar-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
  }
  .sk-calendar-title {
    font-family: var(--font-display);
    font-size: 13px; font-weight: 700;
    color: var(--text-muted);
  }
  .sk-calendar-status {
    background: var(--success-soft, #f0faf4);
    color: var(--success, #15803d);
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px; font-weight: 700;
  }

  .sk-calendar-stats {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 8px;
    margin-bottom: 14px;
  }
  .sk-cal-stat {
    background: var(--bg-alt, #f2f2ec);
    padding: 10px 12px;
    border-radius: 8px;
    text-align: center;
  }
  .sk-cal-stat-label {
    font-size: 9.5px; font-weight: 700;
    color: var(--text-soft);
    letter-spacing: 0.3px; text-transform: uppercase;
  }
  .sk-cal-stat-val {
    font-family: var(--font-display);
    font-size: 16px; font-weight: 800;
    color: var(--text);
    margin-top: 2px;
    font-variant-numeric: tabular-nums;
  }

  .sk-day {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 0;
    border-bottom: 1px solid var(--border);
  }
  .sk-day:last-child { border-bottom: 0; }
  .sk-day-name {
    font-family: var(--font-display);
    font-size: 11px; font-weight: 700;
    color: var(--text-muted);
    width: 36px;
    flex-shrink: 0;
  }
  .sk-day-bar {
    flex: 1;
    height: 22px;
    background: var(--bg-alt);
    border-radius: 6px;
    overflow: hidden;
    position: relative;
  }
  .sk-day-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--sk-pink) 0%, #f472b6 100%);
    border-radius: 6px;
    display: flex; align-items: center; justify-content: flex-end;
    padding: 0 8px;
    color: #fff;
    font-size: 10px; font-weight: 700;
    transition: width 0.3s ease;
  }
  .sk-day-fill.is-good {
    background: linear-gradient(90deg, var(--success, #15803d) 0%, #22c55e 100%);
  }
  .sk-day-pct {
    flex-shrink: 0;
    width: 36px;
    text-align: right;
    font-size: 11px; font-weight: 700;
    color: var(--text-muted);
    font-variant-numeric: tabular-nums;
  }

  .sk-section {
    padding: clamp(60px, 8vw, 100px) 0;
    position: relative;
  }
  .sk-section-soft { background: var(--surface, #fff); }

  .sk-section-head { max-width: 720px; margin: 0 0 56px; }
  .sk-section-head.is-center { margin: 0 auto 56px; text-align: center; }
  .sk-section-eyebrow {
    font-size: 12px; font-weight: 700;
    color: #be185d;
    letter-spacing: 0.8px; text-transform: uppercase;
    margin-bottom: 12px;
    display: inline-block;
  }
  .sk-section h2 {
    font-family: var(--font-display);
    font-size: clamp(28px, 3.8vw, 42px);
    font-weight: 800;
    line-height: 1.15;
    letter-spacing: -0.018em;
    margin: 0 0 16px;
    color: var(--text);
  }
  .sk-section-intro {
    font-size: clamp(16px, 1.6vw, 18px);
    line-height: 1.65;
    color: var(--text-soft);
    margin: 0;
  }

  .sk-problems {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
    margin-top: 16px;
  }
  @media (max-width: 800px) { .sk-problems { grid-template-columns: 1fr; } }

  .sk-problem {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--sk-radius);
    padding: 28px;
    transition: all 0.25s var(--ease-out);
  }
  .sk-problem:hover {
    transform: translateY(-3px);
    border-color: var(--sk-pink);
    box-shadow: var(--shadow-soft);
  }
  .sk-problem-quote {
    font-family: var(--font-display);
    font-size: 22px;
    font-weight: 700;
    line-height: 1.3;
    color: var(--text);
    margin: 0 0 14px;
    position: relative;
    padding-top: 24px;
  }
  .sk-problem-quote::before {
    content: "\"";
    position: absolute;
    top: -10px; left: -8px;
    font-size: 60px;
    color: var(--sk-pink);
    line-height: 1;
    font-family: serif;
    opacity: 0.4;
  }
  .sk-problem p {
    font-size: 14px; line-height: 1.6;
    color: var(--text-soft);
    margin: 0;
  }

  .sk-package-wrap {
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    gap: 24px;
    margin-top: 16px;
  }
  @media (max-width: 800px) { .sk-package-wrap { grid-template-columns: 1fr; } }

  .sk-package {
    background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 100%);
    border: 1px solid #fbcfe8;
    border-radius: var(--sk-radius-lg);
    padding: clamp(28px, 4vw, 44px);
    position: relative;
  }
  .sk-package-tag {
    display: inline-block;
    background: var(--sk-pink);
    color: #fff;
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.5px; text-transform: uppercase;
    margin-bottom: 16px;
  }
  .sk-package h3 {
    font-family: var(--font-display);
    font-size: clamp(22px, 2.5vw, 30px);
    font-weight: 800;
    line-height: 1.2;
    margin: 0 0 12px;
    color: var(--text);
  }
  .sk-package-desc {
    font-size: 15px;
    line-height: 1.6;
    color: var(--text-muted);
    margin: 0 0 28px;
  }
  .sk-package-list {
    margin: 0; padding: 0;
    list-style: none;
  }
  .sk-package-list li {
    padding: 10px 0 10px 32px;
    position: relative;
    font-size: 15px;
    line-height: 1.5;
    color: var(--text);
    border-bottom: 1px dashed rgba(190, 24, 93, 0.15);
  }
  .sk-package-list li:last-child { border-bottom: 0; }
  .sk-package-list li::before {
    content: "✓";
    position: absolute; left: 0; top: 8px;
    width: 22px; height: 22px;
    background: var(--sk-pink);
    color: #fff;
    border-radius: 50%;
    display: grid; place-items: center;
    font-size: 12px;
    font-weight: 800;
  }

  .sk-pricing {
    background: var(--surface);
    border: 2px solid var(--text);
    border-radius: var(--sk-radius-lg);
    padding: clamp(28px, 4vw, 36px);
    text-align: center;
    position: sticky;
    top: 24px;
  }
  .sk-pricing-label {
    font-size: 11px; font-weight: 700;
    color: var(--text-soft);
    letter-spacing: 0.6px; text-transform: uppercase;
    margin-bottom: 8px;
  }
  .sk-pricing-amount {
    font-family: var(--font-display);
    font-size: clamp(36px, 5vw, 52px);
    font-weight: 800;
    line-height: 1;
    color: var(--text);
    margin-bottom: 4px;
  }
  .sk-pricing-amount small {
    font-size: 16px; font-weight: 600;
    color: var(--text-soft);
    margin-left: 4px;
  }
  .sk-pricing-period {
    font-size: 13px;
    color: var(--text-soft);
    margin-bottom: 24px;
  }
  .sk-pricing-extras {
    margin: 24px 0 0;
    padding: 20px 0 0;
    border-top: 1px solid var(--border);
    text-align: left;
    list-style: none;
  }
  .sk-pricing-extras li {
    padding: 6px 0 6px 24px;
    position: relative;
    font-size: 13px;
    color: var(--text-muted);
    line-height: 1.5;
  }
  .sk-pricing-extras li::before {
    content: "+";
    position: absolute; left: 0;
    color: var(--sk-pink);
    font-weight: 800;
  }
  .sk-pricing-cta {
    display: block;
    width: 100%;
    padding: 16px;
    background: var(--text);
    color: #fff;
    border-radius: 12px;
    font-weight: 700;
    font-size: 15px;
    text-decoration: none;
    transition: all 0.2s ease;
    margin-top: 24px;
  }
  .sk-pricing-cta:hover {
    background: var(--sk-pink);
    color: #fff;
    transform: translateY(-1px);
  }

  .sk-honest {
    background: linear-gradient(135deg, var(--accent-soft, #fff7ed) 0%, #fff 100%);
    border: 1px solid var(--accent-line, #fed7aa);
    border-radius: var(--sk-radius);
    padding: clamp(28px, 4vw, 44px);
    margin-top: 32px;
  }
  .sk-honest-header {
    display: flex; gap: 16px; align-items: center;
    margin-bottom: 20px;
  }
  .sk-honest-icon {
    flex-shrink: 0;
    width: 44px; height: 44px;
    border-radius: 12px;
    background: var(--accent, #f97316);
    color: #fff;
    display: grid; place-items: center;
    font-size: 22px; font-weight: 800;
  }
  .sk-honest-header strong {
    font-family: var(--font-display);
    font-size: 18px; font-weight: 700;
  }
  .sk-honest ul {
    margin: 0; padding: 0; list-style: none;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px 24px;
  }
  @media (max-width: 600px) { .sk-honest ul { grid-template-columns: 1fr; } }
  .sk-honest li {
    padding: 10px 0 10px 28px;
    position: relative;
    font-size: 15px; line-height: 1.5;
    color: var(--text-muted);
  }
  .sk-honest li::before {
    content: "✗";
    position: absolute; left: 0; top: 8px;
    color: var(--accent);
    font-weight: 800; font-size: 18px;
  }

  .sk-steps {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-top: 16px;
  }
  @media (max-width: 800px) { .sk-steps { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 500px) { .sk-steps { grid-template-columns: 1fr; } }

  .sk-step {
    background: var(--surface, #fff);
    border: 1px solid var(--border);
    border-radius: var(--sk-radius);
    padding: 24px;
    position: relative;
    transition: all 0.25s var(--ease-out);
  }
  .sk-step:hover {
    transform: translateY(-3px);
    border-color: var(--sk-pink);
    box-shadow: var(--shadow-soft);
  }
  .sk-step-num {
    font-family: var(--font-display);
    font-size: 32px; font-weight: 800;
    line-height: 1;
    color: var(--sk-pink);
    margin-bottom: 16px;
    font-variant-numeric: tabular-nums;
  }
  .sk-step h4 {
    font-family: var(--font-display);
    font-size: 16px; font-weight: 700;
    line-height: 1.3;
    margin: 0 0 8px;
  }
  .sk-step p {
    font-size: 13px; line-height: 1.6;
    color: var(--text-soft);
    margin: 0;
  }
  .sk-step-time {
    display: inline-block;
    margin-top: 12px;
    font-size: 11px; font-weight: 700;
    color: var(--text-soft);
    letter-spacing: 0.5px; text-transform: uppercase;
    padding: 4px 8px;
    background: var(--bg-alt, #f2f2ec);
    border-radius: 6px;
  }

  .sk-example-wrap {
    background: linear-gradient(135deg, #1a0d1f 0%, #2d1b3d 100%);
    color: #fff;
    border-radius: var(--sk-radius-lg);
    padding: clamp(36px, 5vw, 56px);
    margin-top: 16px;
    position: relative;
    overflow: hidden;
  }
  .sk-example-wrap::before {
    content: "";
    position: absolute;
    top: -100px; right: -100px;
    width: 400px; height: 400px;
    background: radial-gradient(circle, rgba(236, 72, 153, 0.18) 0%, transparent 60%);
    pointer-events: none;
  }
  .sk-example-wrap > * { position: relative; }

  .sk-example-eyebrow {
    font-size: 11px; font-weight: 700;
    color: #f9a8d4;
    letter-spacing: 0.8px; text-transform: uppercase;
    margin-bottom: 14px;
  }
  .sk-example-wrap h3 {
    font-family: var(--font-display);
    font-size: clamp(22px, 2.5vw, 30px);
    font-weight: 800;
    line-height: 1.25;
    margin: 0 0 12px;
    color: #fff;
  }
  .sk-example-context {
    color: rgba(255, 255, 255, 0.7);
    font-size: 15px;
    line-height: 1.6;
    margin: 0 0 32px;
    max-width: 600px;
  }

  .sk-comparison {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 32px;
  }
  @media (max-width: 700px) { .sk-comparison { grid-template-columns: 1fr; } }

  .sk-state {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border-radius: 16px;
    padding: 24px;
  }
  .sk-state.is-after {
    background: rgba(236, 72, 153, 0.12);
    border-color: rgba(236, 72, 153, 0.3);
  }
  .sk-state-label {
    display: inline-block;
    font-size: 10px; font-weight: 700;
    letter-spacing: 0.6px; text-transform: uppercase;
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    padding: 4px 8px;
    border-radius: 6px;
    margin-bottom: 14px;
  }
  .sk-state.is-after .sk-state-label { background: var(--sk-pink); }
  .sk-state h4 {
    font-family: var(--font-display);
    font-size: 15px; font-weight: 700;
    margin: 0 0 14px;
    color: #fff;
  }
  .sk-state-metric {
    padding: 10px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    display: flex; justify-content: space-between; align-items: center;
    font-size: 13px;
    color: rgba(255, 255, 255, 0.85);
  }
  .sk-state-metric:last-child { border-bottom: 0; }
  .sk-state-metric strong {
    font-family: var(--font-display);
    font-variant-numeric: tabular-nums;
    font-weight: 800;
    font-size: 15px;
  }
  .sk-state.is-after .sk-state-metric strong { color: #f9a8d4; }

  .sk-example-summary {
    border-top: 1px solid rgba(255, 255, 255, 0.12);
    padding-top: 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
  }
  .sk-example-summary-text {
    color: rgba(255, 255, 255, 0.85);
    font-size: 15px;
    line-height: 1.5;
    max-width: 520px;
  }
  .sk-example-summary-amount {
    font-family: var(--font-display);
    font-size: clamp(28px, 3.5vw, 38px);
    font-weight: 800;
    color: #fff;
    text-align: right;
  }
  .sk-example-summary-amount small {
    display: block;
    font-size: 12px; font-weight: 600;
    color: rgba(255, 255, 255, 0.6);
    letter-spacing: 0.4px; text-transform: uppercase;
    margin-top: 4px;
  }

  .sk-target {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
  }
  @media (max-width: 700px) { .sk-target { grid-template-columns: 1fr; } }

  .sk-target-col {
    padding: 28px;
    border-radius: var(--sk-radius);
    border: 1px solid var(--border);
  }
  .sk-target-col.is-yes {
    background: var(--sk-pink-soft);
    border-color: rgba(236, 72, 153, 0.2);
  }
  .sk-target-col.is-no { background: var(--bg-alt, #f2f2ec); }
  .sk-target-col h4 {
    font-family: var(--font-display);
    font-size: 17px; font-weight: 700;
    margin: 0 0 16px;
    display: flex; align-items: center; gap: 8px;
  }
  .sk-target-col h4::before {
    width: 24px; height: 24px;
    border-radius: 50%;
    display: grid; place-items: center;
    font-size: 14px; font-weight: 800;
    flex-shrink: 0;
  }
  .sk-target-col.is-yes h4::before {
    content: "♡"; background: var(--sk-pink); color: #fff;
  }
  .sk-target-col.is-no h4::before {
    content: "—"; background: var(--text-soft); color: #fff;
  }
  .sk-target-col ul { margin: 0; padding: 0; list-style: none; }
  .sk-target-col li {
    padding: 8px 0;
    font-size: 14px; line-height: 1.55;
    color: var(--text-muted);
  }

  .sk-faq-list { max-width: 760px; margin: 0 auto; }
  .sk-faq {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--sk-radius);
    margin-bottom: 12px;
    overflow: hidden;
    transition: border-color 0.2s ease;
  }
  .sk-faq:hover { border-color: var(--sk-pink); }
  .sk-faq[open] {
    border-color: var(--sk-pink);
    box-shadow: var(--shadow-sm);
  }
  .sk-faq summary {
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
  .sk-faq summary::-webkit-details-marker { display: none; }
  .sk-faq summary::after {
    content: "+";
    flex-shrink: 0;
    width: 28px; height: 28px;
    border-radius: 50%;
    background: var(--sk-pink-soft);
    color: #be185d;
    display: grid; place-items: center;
    font-size: 18px; font-weight: 400;
    transition: all 0.2s ease;
  }
  .sk-faq[open] summary::after {
    content: "−";
    background: var(--sk-pink);
    color: #fff;
    transform: rotate(180deg);
  }
  .sk-faq-content {
    padding: 0 28px 24px;
    color: var(--text-soft);
    font-size: 15px;
    line-height: 1.7;
  }
  .sk-faq-content p { margin: 0 0 12px; }
  .sk-faq-content p:last-child { margin: 0; }

  .sk-author {
    background: var(--sand, #f5f0e8);
    border-radius: var(--sk-radius-lg);
    padding: clamp(36px, 5vw, 56px);
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 32px;
    align-items: center;
    margin-top: 16px;
  }
  @media (max-width: 700px) {
    .sk-author { grid-template-columns: 1fr; text-align: center; }
  }
  .sk-author-photo {
    width: 120px; height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--sk-pink) 0%, #be185d 100%);
    color: #fff;
    display: grid; place-items: center;
    font-family: var(--font-display);
    font-size: 42px; font-weight: 800;
    flex-shrink: 0;
    margin: 0 auto;
  }
  .sk-author-content h3 {
    font-family: var(--font-display);
    font-size: 22px; font-weight: 800;
    margin: 0 0 8px;
  }
  .sk-author-role {
    font-size: 14px; font-weight: 600;
    color: #be185d;
    margin: 0 0 16px;
  }
  .sk-author-content p {
    font-size: 15px; line-height: 1.65;
    color: var(--text-muted);
    margin: 0;
  }

  .sk-final {
    background: linear-gradient(180deg, var(--text, #0d0d0b) 0%, #1a0d1f 100%);
    color: #fff;
    padding: clamp(60px, 9vw, 96px) 0;
    position: relative;
    overflow: hidden;
  }
  .sk-final::before {
    content: "";
    position: absolute;
    top: -100px; left: 50%;
    transform: translateX(-50%);
    width: 600px; height: 600px;
    background: radial-gradient(circle, rgba(236, 72, 153, 0.18) 0%, transparent 70%);
    pointer-events: none;
  }
  .sk-final-inner {
    position: relative;
    max-width: 640px;
    margin: 0 auto;
    text-align: center;
  }
  .sk-final h2 { color: #fff; margin-bottom: 16px; }
  .sk-final-sub {
    font-size: 17px;
    line-height: 1.6;
    color: rgba(255, 255, 255, 0.7);
    margin: 0 0 40px;
  }

  .sk-form {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-radius: var(--sk-radius);
    padding: clamp(24px, 4vw, 36px);
    text-align: left;
  }
  .sk-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
  }
  @media (max-width: 600px) { .sk-form-grid { grid-template-columns: 1fr; } }
  .sk-field { margin-bottom: 16px; }
  .sk-field-full { grid-column: 1 / -1; }
  .sk-field label {
    display: block;
    font-size: 13px; font-weight: 600;
    color: rgba(255, 255, 255, 0.85);
    margin-bottom: 8px;
  }
  .sk-field label .opt {
    font-weight: 400;
    color: rgba(255, 255, 255, 0.4);
    font-size: 12px;
    margin-left: 6px;
  }
  .sk-field input,
  .sk-field textarea,
  .sk-field select {
    width: 100%;
    padding: 14px 16px;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: #fff;
    border-radius: 12px;
    font-size: 15px;
    font-family: inherit;
    transition: all 0.2s ease;
  }
  .sk-field option { color: #111827; }
  .sk-field input::placeholder,
  .sk-field textarea::placeholder { color: rgba(255, 255, 255, 0.35); }
  .sk-field input:focus,
  .sk-field textarea:focus,
  .sk-field select:focus {
    outline: none;
    border-color: var(--sk-pink);
    background: rgba(255, 255, 255, 0.1);
  }
  .sk-field textarea { min-height: 90px; resize: vertical; }
  .sk-form-submit {
    width: 100%;
    padding: 18px;
    background: var(--sk-pink);
    color: #fff;
    border: 0;
    border-radius: 14px;
    font-weight: 700;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-top: 8px;
    font-family: inherit;
  }
  .sk-form-submit:hover {
    background: #be185d;
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(236, 72, 153, 0.3);
  }
  .sk-form-meta {
    text-align: center;
    margin-top: 20px;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.4);
    line-height: 1.5;
  }
  .sk-consent {
    margin: 4px 0 0;
    display: flex;
    gap: 10px;
    align-items: flex-start;
    color: rgba(255, 255, 255, 0.78);
    font-size: 13px;
    line-height: 1.55;
  }
  .sk-consent input[type="checkbox"] {
    margin-top: 3px;
    width: 16px;
    height: 16px;
  }
  .sk-consent a { color: #f9a8d4; }
  .sk-form .form-feedback {
    margin: 0 0 12px;
    padding: 10px 12px;
    border-radius: 10px;
    font-size: 13px;
    border: 1px solid rgba(255, 255, 255, 0.18);
    display: none;
  }
  .sk-form .form-feedback.is-success {
    display: block;
    border-color: #34d399;
    background: rgba(16, 185, 129, 0.14);
    color: #d1fae5;
  }
  .sk-form .form-feedback.is-error {
    display: block;
    border-color: #fca5a5;
    background: rgba(239, 68, 68, 0.12);
    color: #fee2e2;
  }
</style>

<div class="sk-page">
  <section class="sk-hero">
    <div class="sk-container sk-hero-inner">
      <div class="sk-hero-grid">
        <div>
          <div class="sk-pill">Marketing dla salonów kosmetycznych</div>
          <h1>Wypełniony grafik <em>nawet w środy i czwartki</em></h1>
          <p class="sk-hero-sub">
            Reklama Google + Instagram + Booksy które realnie dowożą klientki do Twojego salonu. Bez "zaangażowania" i "estetyki feed'a" — tylko nowe rezerwacje, więcej powtarzalnych wizyt, mniej okienek w grafiku.
          </p>
          <div class="sk-hero-cta-row">
            <a href="#kontakt" class="sk-cta-primary" data-cta="salon-hero-primary-konsultacja" data-cta-section="hero" data-cta-position="primary">Umów rozmowę</a>
            <a href="#oferta" class="sk-cta-secondary" data-cta="salon-hero-secondary-oferta" data-cta-section="hero" data-cta-position="secondary">Zobacz co robię</a>
          </div>
          <div class="sk-trust-row">
            <span class="sk-trust-item">Pierwszy efekt w 14 dni</span>
            <span class="sk-trust-item">Bez umów na rok</span>
            <span class="sk-trust-item">Pierwsza konsultacja gratis</span>
          </div>
        </div>

        <div>
          <div class="sk-calendar" aria-hidden="true">
            <div class="sk-calendar-header">
              <span class="sk-calendar-title">Salon Beauty — Lublin</span>
              <span class="sk-calendar-status">Po 30 dniach</span>
            </div>
            <div class="sk-calendar-stats">
              <div class="sk-cal-stat"><div class="sk-cal-stat-label">Zapełnienie</div><div class="sk-cal-stat-val">87%</div></div>
              <div class="sk-cal-stat"><div class="sk-cal-stat-label">Nowe klientki</div><div class="sk-cal-stat-val">42</div></div>
              <div class="sk-cal-stat"><div class="sk-cal-stat-label">Powroty</div><div class="sk-cal-stat-val">68%</div></div>
            </div>
            <div class="sk-day"><span class="sk-day-name">PON</span><div class="sk-day-bar"><div class="sk-day-fill is-good" style="width: 92%;">8/8</div></div><span class="sk-day-pct">92%</span></div>
            <div class="sk-day"><span class="sk-day-name">WT</span><div class="sk-day-bar"><div class="sk-day-fill is-good" style="width: 88%;">7/8</div></div><span class="sk-day-pct">88%</span></div>
            <div class="sk-day"><span class="sk-day-name">ŚR</span><div class="sk-day-bar"><div class="sk-day-fill" style="width: 75%;">6/8</div></div><span class="sk-day-pct">75%</span></div>
            <div class="sk-day"><span class="sk-day-name">CZW</span><div class="sk-day-bar"><div class="sk-day-fill" style="width: 78%;">6/8</div></div><span class="sk-day-pct">78%</span></div>
            <div class="sk-day"><span class="sk-day-name">PT</span><div class="sk-day-bar"><div class="sk-day-fill is-good" style="width: 100%;">8/8</div></div><span class="sk-day-pct">100%</span></div>
            <div class="sk-day"><span class="sk-day-name">SOB</span><div class="sk-day-bar"><div class="sk-day-fill is-good" style="width: 100%;">8/8</div></div><span class="sk-day-pct">100%</span></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="sk-section sk-section-soft">
    <div class="sk-container">
      <div class="sk-section-head">
        <span class="sk-section-eyebrow">Jeśli któreś brzmi znajomo</span>
        <h2>Trzy najczęstsze problemy salonów które słyszę</h2>
        <p class="sk-section-intro">
          Nie wymyślam ich. Słyszałem dokładnie te zdania od dziesiątek właścicielek salonów w ostatnich latach. Pewnie zabrzmią znajomo.
        </p>
      </div>

      <div class="sk-problems">
        <div class="sk-problem"><p class="sk-problem-quote">Mam wolne miejsca w grafiku, a Instagram jakby działał na próżno.</p><p>Posty się klikają, follower'i przybywają, ale rezerwacji jak nie było, tak nie ma. Zaangażowanie nie jest klientkami.</p></div>
        <div class="sk-problem"><p class="sk-problem-quote">Klientki przychodzą raz i znikają. Albo tylko na promocje.</p><p>Brak systemu który zachęca do powrotu. Każda nowa klientka kosztuje, a powtarzalność wizyt jest niska.</p></div>
        <div class="sk-problem"><p class="sk-problem-quote">Płaciłam agencji 1 500 zł/mies, nie wiedziałam za co.</p><p>Co miesiąc raport pełen wykresów, ale grafik dalej pusty. Agencja zajmuje się "buildowaniem marki" — Ty potrzebujesz klientek do fotela.</p></div>
      </div>
    </div>
  </section>

  <section class="sk-section" id="oferta">
    <div class="sk-container">
      <div class="sk-section-head">
        <span class="sk-section-eyebrow">Co konkretnie robię</span>
        <h2>Pakiet "Pełny grafik" — wszystko czego potrzebuje salon</h2>
        <p class="sk-section-intro">
          Jedna miesięczna stawka, wszystkie kanały które realnie dowożą klientki. Żadnych dopłat za dodatkowe konsultacje, kreacje czy zmiany kampanii.
        </p>
      </div>

      <div class="sk-package-wrap">
        <div class="sk-package">
          <span class="sk-package-tag">Pakiet startowy</span>
          <h3>Grafik wypełniony nowymi klientkami w 30 dni</h3>
          <p class="sk-package-desc">
            Trzy kanały działają razem: <strong>Google Ads</strong> łapie kobiety szukające usługi, <strong>Instagram + Meta Ads</strong> budują rozpoznawalność i przyciągają z Twojej okolicy, <strong>landing page + Booksy</strong> zamieniają zainteresowanie w rezerwację.
          </p>
          <ul class="sk-package-list">
            <li>Kampania Google Ads na frazy lokalne ("manicure Lublin", "depilacja laserowa Lublin")</li>
            <li>Reklamy Meta (Instagram + Facebook) targetowane geograficznie</li>
            <li>3–5 nowych kreacji video/foto miesięcznie do reklam</li>
            <li>Landing page lub optymalizacja Twojej strony pod konwersję</li>
            <li>Integracja z Booksy / Versum żeby rezerwacje "wpadały same"</li>
            <li>Cotygodniowa optymalizacja kampanii (CTR, CPL, jakość ruchu)</li>
            <li>SMS / mailing przypominający o powrocie do byłych klientek</li>
            <li>Raport miesięczny w jasnym języku — bez wykresów dla wykresów</li>
          </ul>
        </div>

        <div class="sk-pricing">
          <div class="sk-pricing-label">Miesięczna opłata</div>
          <div class="sk-pricing-amount">1 800<small>zł</small></div>
          <div class="sk-pricing-period">+ Twój budżet reklamowy (od 800 zł/mies)</div>
          <ul class="sk-pricing-extras">
            <li>Pierwsza konsultacja gratis (45 min)</li>
            <li>Bez umów na 12 miesięcy — wypowiadalna co miesiąc</li>
            <li>Pierwszy miesiąc 50% taniej</li>
            <li>Wszystkie kanały w jednej stawce</li>
          </ul>
          <a href="#kontakt" class="sk-pricing-cta" data-cta="salon-pricing-cta-konsultacja" data-cta-section="pricing-card" data-cta-position="card">Umów konsultację →</a>
        </div>
      </div>
    </div>
  </section>

  <section class="sk-section sk-section-soft">
    <div class="sk-container">
      <div class="sk-section-head">
        <span class="sk-section-eyebrow">Szczerze</span>
        <h2>Czego NIE dostaniesz (i lepiej żebyś wiedziała)</h2>
        <p class="sk-section-intro">
          Branża beauty jest pełna agencji obiecujących "viralowe filmy" i "1000 followers w miesiąc". Mój pakiet jest inny — i lepiej powiedzieć to wprost przed rozmową niż po pierwszym miesiącu.
        </p>
      </div>
      <div class="sk-honest">
        <div class="sk-honest-header"><div class="sk-honest-icon">!</div><strong>W pakiecie nie znajdziesz:</strong></div>
        <ul>
          <li>Obietnic "viralowych" filmów i milionów wyświetleń</li>
          <li>Zwiększania liczby follower'ów jako głównego celu</li>
          <li>"Estetyki feed'a" jako priorytetu nad rezerwacjami</li>
          <li>Reklamowania promocji za 1 zł żeby wpadły puste klientki</li>
          <li>Influencerek za worki produktów</li>
          <li>Strategii social mediowej na 30 stron, której nikt nie czyta</li>
        </ul>
      </div>
    </div>
  </section>

  <section class="sk-section">
    <div class="sk-container">
      <div class="sk-section-head">
        <span class="sk-section-eyebrow">Jak zaczynamy</span>
        <h2>Cztery kroki od pierwszej rozmowy do nowych klientek</h2>
        <p class="sk-section-intro">
          Nic skomplikowanego. Pierwsza rozmowa jest bezpłatna i bez zobowiązań — sama oceniasz czy masz ze mną pracować, czy nie.
        </p>
      </div>
      <div class="sk-steps">
        <div class="sk-step"><div class="sk-step-num">01</div><h4>Bezpłatna konsultacja</h4><p>45 minut online. Pytam o salon, klientki, obecny marketing. Mówię szczerze co warto zrobić i czy w ogóle warto inwestować w reklamy.</p><span class="sk-step-time">Tydzień 1</span></div>
        <div class="sk-step"><div class="sk-step-num">02</div><h4>Strategia + materiały</h4><p>Tworzę plan kampanii. Robimy wspólnie zdjęcia/video do reklam (możemy spotkać się w salonie albo poradzimy sobie zdalnie). Konfiguruję landing.</p><span class="sk-step-time">Tydzień 2</span></div>
        <div class="sk-step"><div class="sk-step-num">03</div><h4>Reklamy idą w świat</h4><p>Uruchamiam Google Ads + Meta Ads. Pierwsze rezerwacje pojawiają się zwykle w 5–10 dni. Daję Ci dostęp żebyś widziała wszystko na żywo.</p><span class="sk-step-time">Tydzień 3</span></div>
        <div class="sk-step"><div class="sk-step-num">04</div><h4>Optymalizacja i raport</h4><p>Co tydzień patrzę na wyniki, wymieniam słabsze kreacje, obcinam nierentowne grupy. Po miesiącu raport z konkretnymi liczbami.</p><span class="sk-step-time">Cyklicznie</span></div>
      </div>
    </div>
  </section>

  <section class="sk-section sk-section-soft">
    <div class="sk-container">
      <div class="sk-section-head">
        <span class="sk-section-eyebrow">Konkret zamiast obietnic</span>
        <h2>Tak wyglądały pierwsze 60 dni dla salonu z Lublina</h2>
        <p class="sk-section-intro">
          Zanonimizowane dane. Salon kosmetyczny w Lublinie, 2 specjalistki, 4 fotele, działa 3 lata. Klientka miała wrażenie że "Instagram nie sprzedaje". Po 60 dniach grafik wypełniony do 87% — bez podnoszenia cen, bez rabatów.
        </p>
      </div>
      <div class="sk-example-wrap">
        <div class="sk-example-eyebrow">Salon kosmetyczny — Lublin</div>
        <h3>Z 32 wizyt tygodniowo do 56 — w 60 dni, bez promocji</h3>
        <p class="sk-example-context">
          Wcześniej salon prowadzony był głównie przez Instagram organicznie. 4500 followers, regularne posty, dobra estetyka. Ale grafik przed południem i w środku tygodnia świecił pustkami. Trzy zmiany — i grafik się wypełnił.
        </p>
        <div class="sk-comparison">
          <div class="sk-state">
            <span class="sk-state-label">Przed</span>
            <h4>Marzec — przed startem kampanii</h4>
            <div class="sk-state-metric"><span>Wizyty / tydzień</span><strong>32</strong></div>
            <div class="sk-state-metric"><span>Zapełnienie grafiku</span><strong>56%</strong></div>
            <div class="sk-state-metric"><span>Powrót klientek</span><strong>41%</strong></div>
            <div class="sk-state-metric"><span>Nowe klientki / mies</span><strong>9</strong></div>
            <div class="sk-state-metric"><span>Środa po południu</span><strong>30%</strong></div>
          </div>
          <div class="sk-state is-after">
            <span class="sk-state-label">Po 60 dniach</span>
            <h4>Maj — kampanie działają</h4>
            <div class="sk-state-metric"><span>Wizyty / tydzień</span><strong>56</strong></div>
            <div class="sk-state-metric"><span>Zapełnienie grafiku</span><strong>87%</strong></div>
            <div class="sk-state-metric"><span>Powrót klientek</span><strong>68%</strong></div>
            <div class="sk-state-metric"><span>Nowe klientki / mies</span><strong>42</strong></div>
            <div class="sk-state-metric"><span>Środa po południu</span><strong>78%</strong></div>
          </div>
        </div>
        <div class="sk-example-summary">
          <div class="sk-example-summary-text">
            <strong>Trzy zmiany w 60 dni:</strong> Google Ads na lokalne frazy ("manicure Lublin", "henna brwi Lublin"), Meta Ads z UGC z salonu, integracja z Booksy żeby rezerwacja była jednym klikiem. Budżet reklamowy: 1 200 zł/mies. Zwrot: 9 600 zł dodatkowego przychodu/mies.
          </div>
          <div class="sk-example-summary-amount">
            +75%
            <small>więcej wizyt w 60 dni</small>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="sk-section">
    <div class="sk-container">
      <div class="sk-section-head">
        <span class="sk-section-eyebrow">Dla kogo</span>
        <h2>Dla kogo to ma sens</h2>
        <p class="sk-section-intro">
          Mam ograniczoną liczbę miejsc — przyjmuję maksymalnie 3 nowe salony kwartalnie żeby utrzymać jakość. Wolę żebyś od razu wiedziała czy to dla Ciebie.
        </p>
      </div>

      <div class="sk-target">
        <div class="sk-target-col is-yes">
          <h4>Pakiet ma sens jeśli:</h4>
          <ul>
            <li>→ Masz salon stacjonarny (nie usługi mobilne / dojazd do klienta)</li>
            <li>→ Twój przychód miesięczny to min. 15 000 zł</li>
            <li>→ Masz wolne miejsca w grafiku (chcesz ich wypełnienia)</li>
            <li>→ Masz Instagram i Google Maps z opiniami</li>
            <li>→ Klientki są lokalne (nie sprzedajesz usługi online)</li>
            <li>→ Stać Cię na 1 800 zł/mies + 800 zł budżetu reklamowego</li>
          </ul>
        </div>
        <div class="sk-target-col is-no">
          <h4>Pakiet NIE ma sensu jeśli:</h4>
          <ul>
            <li>→ Salon dopiero startuje (brak zdjęć, opinii, historii)</li>
            <li>→ Pracujesz solo i już masz wszystko zarezerwowane</li>
            <li>→ Sprzedajesz głównie produkty (nie usługi)</li>
            <li>→ Twoja okolica ma 5 000 mieszkańców (za mały rynek)</li>
            <li>→ Twoje ceny to tylko Allegro / OLX (nie premium / średni segment)</li>
            <li>→ Szukasz "wirusowych" filmów i 100 tys. follower'ów</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section class="sk-section sk-section-soft">
    <div class="sk-container">
      <div class="sk-section-head">
        <span class="sk-section-eyebrow">Kto będzie z Tobą pracował</span>
        <h2>Po drugiej stronie ekranu</h2>
      </div>
      <div class="sk-author">
        <div class="sk-author-photo">SK</div>
        <div class="sk-author-content">
          <h3>Sebastian Kelm</h3>
          <p class="sk-author-role">Konsultant marketingu — Google Ads · Meta Ads · landing pages</p>
          <p>
            Pracuję sam, bez agencji, z maksymalnie 3 salonami w danym kwartale. To nie jest przypadek — chcę żeby każdy klient dostawał moją uwagę osobiście. Specjalizuję się w generowaniu leadów dla małych firm usługowych. Beauty to specyficzna branża: sezonowość, lokalność, znaczenie powtarzalności wizyt — uwzględniam to w każdej kampanii. Jeśli stwierdzę że Twój salon nie jest gotowy na płatne reklamy — powiem Ci to wprost zamiast brać Twoje pieniądze.
          </p>
        </div>
      </div>
    </div>
  </section>

  <section class="sk-section">
    <div class="sk-container">
      <div class="sk-section-head is-center">
        <span class="sk-section-eyebrow">FAQ</span>
        <h2>Pytania które dostaję najczęściej</h2>
      </div>
      <div class="sk-faq-list">
        <details class="sk-faq"><summary>Ile to wszystko kosztuje miesięcznie razem z reklamami?</summary><div class="sk-faq-content"><p>Stała opłata za moją pracę: <strong>1 800 zł netto/mies</strong>. Plus Twój budżet reklamowy — minimum 800 zł, ale rekomendowane 1 200–2 000 zł żeby kampanie miały sensowny zasięg.</p><p>Czyli realnie: 2 600–3 800 zł całkowicie miesięcznie. Pierwszy miesiąc 50% taniej — 900 zł zamiast 1 800. Bez umów na rok.</p></div></details>
        <details class="sk-faq"><summary>Po jakim czasie zobaczę pierwsze klientki?</summary><div class="sk-faq-content"><p>Zwykle pierwsze rezerwacje pojawiają się w <strong>5–10 dni</strong> od uruchomienia kampanii Google Ads. Meta potrzebuje 7–14 dni żeby algorytm się "nauczył" Twojej grupy odbiorców.</p><p>Pełnia efektów: 30–60 dni. To czas na optymalizację, wymianę słabszych kreacji, dostrojenie targetowania. Beauty to nie e-commerce gdzie wszystko widać w pierwszym tygodniu.</p></div></details>
        <details class="sk-faq"><summary>Czy potrzebuję już mieć profesjonalne zdjęcia / video?</summary><div class="sk-faq-content"><p>Nie musisz mieć studyjnych. Beauty świetnie sprzedaje się na <strong>UGC</strong> (user generated content) — autentycznych zdjęciach z salonu, klientkach przed/po, krótkich filmikach z procesu zabiegu.</p><p>Pomogę Ci zaplanować co kręcić i jak. Spotkanie w salonie lub instrukcja na Whatsapp — w 1 dzień zbieramy materiał na 4–6 tygodni reklam.</p></div></details>
        <details class="sk-faq"><summary>Co jeśli mam już Booksy / Versum / inny system rezerwacji?</summary><div class="sk-faq-content"><p>Wszystkie integruję. Reklamy prowadzą bezpośrednio do Twojego systemu rezerwacji — klientka widzi grafik i klika rezerwację w 2 kliknięciach.</p><p>Jeśli nie masz jeszcze Booksy/Versum — pomagam wybrać i skonfigurować. Wliczone w pakiet.</p></div></details>
        <details class="sk-faq"><summary>Mam wolne miejsca tylko po południu w środy. Da się je wypełnić?</summary><div class="sk-faq-content"><p>Tak — i to jest specjalność tego pakietu. Robię osobne kampanie z <strong>schedule-based bidding</strong> które uruchamiają reklamy z wyższymi stawkami w godzinach gdy masz pustki w grafiku.</p><p>Plus: SMS/mailing do byłych klientek z rabatem 10–15% specjalnie na środy. Tak naprawdę zazwyczaj <strong>środy i czwartki to mój pierwszy cel</strong> — to gdzie salony tracą najwięcej pieniędzy.</p></div></details>
        <details class="sk-faq"><summary>Czy umowa jest na 12 miesięcy?</summary><div class="sk-faq-content"><p>Nie. Umowa miesięczna z 14-dniowym okresem wypowiedzenia. Jeśli po pierwszym miesiącu nie widzisz efektu — kończymy bez problemu.</p><p>Zauważam że agencje wiążące klientów na rok często "zwalniają" po podpisaniu. Wolę pracować na bieżącym efekcie — to jest też mój filtr na klientów którzy są zadowoleni.</p></div></details>
        <details class="sk-faq"><summary>Co jeśli mój salon ma już agencję marketingową?</summary><div class="sk-faq-content"><p>Możemy zacząć od bezpłatnej konsultacji — ocenię czy obecna agencja robi dobrą robotę, czy nie. Czasem wystarczy poprawienie kilku rzeczy w ich konfiguracji, czasem potrzebny jest nowy podmiot.</p><p>Nie zabieram klientów innym agencjom siłą. Jeśli ich praca daje efekty — powiem Ci to wprost. Jeśli nie — mam alternatywę.</p></div></details>
        <details class="sk-faq"><summary>Jak długo czeka się na start współpracy?</summary><div class="sk-faq-content"><p>Zwykle <strong>7–14 dni</strong> od pierwszej rozmowy. Przyjmuję maksymalnie 3 salony kwartalnie — jeśli wszystkie miejsca są zajęte, dostajesz mail z najwcześniejszym wolnym terminem.</p><p>Pierwsza konsultacja jest dostępna zwykle w 2–5 dni od wypełnienia formularza.</p></div></details>
      </div>
    </div>
  </section>

  <section class="sk-final" id="kontakt">
    <div class="sk-container sk-final-inner">
      <h2>Umów bezpłatną konsultację</h2>
      <p class="sk-final-sub">
        45 minut online. Powiem Ci szczerze czy warto inwestować w reklamy w Twoim przypadku — zanim wydasz złotówkę. Bez handlowego nawijania, bez wciskania pakietu na siłę.
      </p>

      <form
        class="sk-form"
        id="salon-kosmetyczny-form"
        method="post"
        action="<?php echo esc_url(admin_url("admin-post.php")); ?>"
        data-form="salon-kosmetyczny"
        data-upsellio-lead-form="1"
        data-upsellio-server-form="1"
      >
        <input type="hidden" name="action" value="upsellio_submit_lead">
        <input type="hidden" name="redirect_url" value="<?php echo esc_url((get_permalink() ?: home_url("/marketing-dla-salonu-kosmetycznego/")) . "#kontakt"); ?>">
        <input type="hidden" name="lead_form_origin" value="salon-kosmetyczny-form">
        <input type="hidden" name="lead_source" value="salon-kosmetyczny-form">
        <input type="hidden" name="lead_service" value="Marketing salonu kosmetycznego">
        <input type="hidden" name="lead_goal" value="Wiecej rezerwacji i zapelnienie grafiku salonu">
        <input type="hidden" name="utm_source" data-ups-utm="source" value="">
        <input type="hidden" name="utm_medium" data-ups-utm="medium" value="">
        <input type="hidden" name="utm_campaign" data-ups-utm="campaign" value="">
        <input type="hidden" name="utm_term" data-ups-utm="term" value="">
        <input type="hidden" name="utm_content" data-ups-utm="content" value="">
        <input type="hidden" name="gclid" data-ups-utm="gclid" value="">
        <input type="hidden" name="fbclid" data-ups-utm="fbclid" value="">
        <input type="hidden" name="msclkid" data-ups-utm="msclkid" value="">
        <input type="hidden" name="landing_url" data-ups-context="landing" value="">
        <input type="hidden" name="referrer" data-ups-context="referrer" value="">
        <input type="text" name="lead_website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;pointer-events:none;" aria-hidden="true">
        <input type="text" name="lead_company_url" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;pointer-events:none;" aria-hidden="true">
        <?php wp_nonce_field("upsellio_unified_lead_form", "upsellio_lead_form_nonce"); ?>

        <div class="sk-form-grid">
          <div class="sk-field">
            <label for="salon_name">Imię</label>
            <input type="text" id="salon_name" name="lead_name" placeholder="Anna" autocomplete="given-name" required>
          </div>
          <div class="sk-field">
            <label for="salon_phone">Telefon</label>
            <input type="tel" id="salon_phone" name="lead_phone" placeholder="+48 575 522 595" autocomplete="tel" required>
          </div>
          <div class="sk-field sk-field-full">
            <label for="salon_email">E-mail</label>
            <input type="email" id="salon_email" name="lead_email" placeholder="anna@salon.pl" autocomplete="email" required>
          </div>
          <div class="sk-field sk-field-full">
            <label for="salon_message">Wiadomość <span class="opt">opcjonalnie</span></label>
            <textarea id="salon_message" name="lead_message" placeholder="Opcjonalnie — krótko, o co chodzi"></textarea>
          </div>
        </div>

        <label class="sk-consent">
          <input type="checkbox" name="lead_consent" value="1" required>
          <span>Wyrażam zgodę na kontakt w sprawie konsultacji i akceptuję <a href="<?php echo esc_url(home_url("/polityka-prywatnosci/")); ?>" target="_blank" rel="noopener noreferrer">politykę prywatności</a>.</span>
        </label>

        <button type="submit" class="sk-form-submit" data-cta="salon-final-submit-konsultacja" data-cta-section="final-form" data-cta-position="submit">Oddzwonię w ciągu 24h</button>

        <p class="sk-form-meta">
          Twoje dane służą wyłącznie do umówienia konsultacji. Nie zapisuję Cię na newsletter,<br>nie sprzedaję bazy. Po konsultacji kasuję dane jeśli nie nawiążemy współpracy.
        </p>
      </form>
    </div>
  </section>

</div>

<?php get_footer(); ?>
