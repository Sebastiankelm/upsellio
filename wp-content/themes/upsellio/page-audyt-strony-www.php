<?php
/**
 * Template Name: Audyt strony www — landing
 *
 * Landing dla bezpłatnego audytu strony www (CRO/UX).
 * Główna fraza: "audyt strony" (100-1k), "audyt strony internetowej" (100-1k, 6,34-24,26 zł CPC).
 *
 * Różnice względem trzech poprzednich audytów:
 * - Skupiamy się na KONWERSJI - "ruch jest, ale nie kupują"
 * - Nie SEO (to inny audyt), nie reklamy (też inny audyt)
 * - To audyt UX + copy + CTA + lejka
 * - Klient ma już ruch, ale traci go przy formularzu, pricingu, koszyku
 */
get_header();
?>

<style>
  :root {
    --asw-radius: 20px;
    --asw-radius-lg: 28px;
  }

  .asw-page {
    background: var(--bg, #fafaf6);
    color: var(--text, #0d0d0b);
    font-family: var(--font-body, "DM Sans"), system-ui, sans-serif;
    overflow-x: hidden;
  }
  .asw-page * { box-sizing: border-box; }
  .asw-container { max-width: 1100px; margin: 0 auto; padding: 0 24px; }

  .asw-hero {
    position: relative;
    padding: clamp(60px, 10vw, 100px) 0 clamp(50px, 8vw, 80px);
    overflow: hidden;
  }
  .asw-hero::before {
    content: "";
    position: absolute; inset: 0;
    background:
      radial-gradient(ellipse 700px 400px at 80% 30%, rgba(13, 148, 136, 0.07), transparent),
      radial-gradient(ellipse 500px 300px at 10% 70%, rgba(168, 85, 247, 0.05), transparent);
    pointer-events: none;
    z-index: 0;
  }
  .asw-hero-inner { position: relative; z-index: 1; }

  .asw-hero-grid {
    display: grid;
    grid-template-columns: 1.15fr 1fr;
    gap: 56px;
    align-items: center;
  }
  @media (max-width: 900px) {
    .asw-hero-grid { grid-template-columns: 1fr; gap: 40px; }
  }

  .asw-pill {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--brand-soft, #ccfbf1);
    color: var(--brand-dark, #0f766e);
    padding: 8px 16px;
    border-radius: 999px;
    font-size: 12px; font-weight: 700;
    letter-spacing: 0.4px; text-transform: uppercase;
    margin-bottom: 24px;
  }
  .asw-pill::before {
    content: "";
    width: 6px; height: 6px;
    background: var(--brand, #0d9488);
    border-radius: 50%;
    animation: aswPulse 2s ease-in-out infinite;
  }
  @keyframes aswPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(0.85); }
  }

  .asw-hero h1 {
    font-family: var(--font-display, "Bricolage Grotesque"), serif;
    font-size: clamp(36px, 5.5vw, 60px);
    font-weight: 800;
    line-height: 1.04;
    letter-spacing: -0.025em;
    margin: 0 0 24px;
    color: var(--text);
  }
  .asw-hero h1 em {
    font-style: normal;
    background: linear-gradient(120deg, var(--brand) 0%, var(--brand-dark) 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    color: transparent;
  }

  .asw-hero-sub {
    font-size: clamp(16px, 2vw, 19px);
    line-height: 1.6;
    color: var(--text-soft, #7a7a72);
    margin: 0 0 32px;
    max-width: 540px;
  }

  .asw-hero-cta-row {
    display: flex; gap: 16px; flex-wrap: wrap;
    margin-bottom: 32px;
  }
  .asw-cta-primary {
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
  .asw-cta-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(13, 13, 11, 0.2);
    color: #fff;
  }
  .asw-cta-primary::after { content: "→"; transition: transform 0.2s ease; }
  .asw-cta-primary:hover::after { transform: translateX(4px); }

  .asw-cta-secondary {
    display: inline-flex; align-items: center; gap: 8px;
    color: var(--text);
    padding: 18px 24px;
    font-size: 15px; font-weight: 600;
    text-decoration: none;
    border-bottom: 2px solid transparent;
    transition: all 0.15s ease;
  }
  .asw-cta-secondary:hover { border-bottom-color: var(--brand); color: var(--brand); }

  .asw-trust-row {
    display: flex; gap: 24px; flex-wrap: wrap;
    color: var(--text-soft, #7a7a72);
    font-size: 14px;
  }
  .asw-trust-item { display: inline-flex; align-items: center; gap: 8px; }
  .asw-trust-item::before {
    content: "✓"; color: var(--brand); font-weight: 800; font-size: 16px;
  }

  .asw-funnel-card {
    background: #fff;
    border: 1px solid var(--border, #e8e8e0);
    border-radius: var(--asw-radius);
    padding: 28px;
    box-shadow: var(--shadow, 0 22px 60px rgba(15, 23, 42, 0.1));
    position: relative;
    transform: rotate(-0.5deg);
  }
  .asw-funnel-card::before {
    content: "Lejek konwersji";
    position: absolute;
    top: -12px; left: 24px;
    background: var(--text);
    color: #fff;
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.5px; text-transform: uppercase;
  }

  .asw-funnel-header {
    font-family: var(--font-display);
    font-size: 13px; font-weight: 700;
    color: var(--text-muted);
    margin-bottom: 4px;
  }
  .asw-funnel-period {
    font-size: 11px; color: var(--text-soft);
    margin-bottom: 20px;
  }

  .asw-funnel-step {
    display: flex; align-items: center; gap: 16px;
    padding: 14px 0;
    border-bottom: 1px solid var(--border);
  }
  .asw-funnel-step:last-child { border-bottom: 0; }

  .asw-funnel-bar-wrap {
    flex: 1;
    background: var(--bg-alt, #f2f2ec);
    border-radius: 8px;
    overflow: hidden;
    height: 32px;
    position: relative;
  }
  .asw-funnel-bar {
    height: 100%;
    background: linear-gradient(90deg, var(--brand) 0%, var(--brand-dark) 100%);
    border-radius: 8px;
    display: flex; align-items: center; padding: 0 12px;
    color: #fff;
    font-size: 12px; font-weight: 700;
    font-variant-numeric: tabular-nums;
    transition: width 0.3s ease;
  }
  .asw-funnel-bar.is-loss {
    background: linear-gradient(90deg, #dc2626 0%, #ef4444 100%);
  }

  .asw-funnel-step-info {
    flex-shrink: 0;
    text-align: right;
    min-width: 80px;
  }
  .asw-funnel-step-label {
    font-size: 11px; font-weight: 700;
    color: var(--text-soft);
    letter-spacing: 0.4px; text-transform: uppercase;
  }
  .asw-funnel-step-value {
    font-family: var(--font-display);
    font-size: 14px; font-weight: 800;
    color: var(--text);
    font-variant-numeric: tabular-nums;
  }

  .asw-funnel-summary {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 2px solid var(--border);
    background: #fef2f2;
    margin-left: -12px;
    margin-right: -12px;
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 13px;
    color: #b91c1c;
    font-weight: 700;
    text-align: center;
  }

  .asw-section {
    padding: clamp(60px, 8vw, 100px) 0;
    position: relative;
  }
  .asw-section-soft { background: var(--surface, #fff); }

  .asw-section-head { max-width: 720px; margin: 0 0 56px; }
  .asw-section-head.is-center { margin: 0 auto 56px; text-align: center; }
  .asw-section-eyebrow {
    font-size: 12px; font-weight: 700;
    color: var(--brand-dark);
    letter-spacing: 0.8px; text-transform: uppercase;
    margin-bottom: 12px;
    display: inline-block;
  }
  .asw-section h2 {
    font-family: var(--font-display);
    font-size: clamp(28px, 3.8vw, 42px);
    font-weight: 800;
    line-height: 1.15;
    letter-spacing: -0.018em;
    margin: 0 0 16px;
    color: var(--text);
  }
  .asw-section-intro {
    font-size: clamp(16px, 1.6vw, 18px);
    line-height: 1.65;
    color: var(--text-soft);
    margin: 0;
  }

  .asw-areas {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
  }
  @media (max-width: 850px) { .asw-areas { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 550px) { .asw-areas { grid-template-columns: 1fr; } }

  .asw-area {
    background: var(--surface, #fff);
    border: 1px solid var(--border);
    border-radius: var(--asw-radius);
    padding: 28px;
    transition: all 0.25s var(--ease-out);
    position: relative;
    overflow: hidden;
  }
  .asw-area::before {
    content: "";
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 3px;
    background: linear-gradient(90deg, var(--brand), var(--brand-dark));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.3s var(--ease-out);
  }
  .asw-area:hover {
    border-color: var(--brand);
    transform: translateY(-4px);
    box-shadow: var(--shadow-soft);
  }
  .asw-area:hover::before { transform: scaleX(1); }
  .asw-area-num {
    display: inline-flex; align-items: center; justify-content: center;
    width: 36px; height: 36px;
    border-radius: 10px;
    background: var(--brand-soft);
    color: var(--brand-dark);
    font-weight: 800;
    font-size: 14px;
    font-variant-numeric: tabular-nums;
    margin-bottom: 16px;
  }
  .asw-area h3 {
    font-family: var(--font-display);
    font-size: 17px; font-weight: 700;
    line-height: 1.3;
    margin: 0 0 10px;
  }
  .asw-area p {
    font-size: 14px; line-height: 1.6;
    color: var(--text-soft);
    margin: 0;
  }

  .asw-honest {
    background: linear-gradient(135deg, var(--accent-soft, #fff7ed) 0%, #fff 100%);
    border: 1px solid var(--accent-line, #fed7aa);
    border-radius: var(--asw-radius);
    padding: clamp(28px, 4vw, 44px);
    margin-top: 32px;
  }
  .asw-honest-header {
    display: flex; gap: 16px; align-items: center;
    margin-bottom: 20px;
  }
  .asw-honest-icon {
    flex-shrink: 0;
    width: 44px; height: 44px;
    border-radius: 12px;
    background: var(--accent, #f97316);
    color: #fff;
    display: grid; place-items: center;
    font-size: 22px; font-weight: 800;
  }
  .asw-honest-header strong {
    font-family: var(--font-display);
    font-size: 18px; font-weight: 700;
  }
  .asw-honest ul {
    margin: 0; padding: 0; list-style: none;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px 24px;
  }
  @media (max-width: 600px) { .asw-honest ul { grid-template-columns: 1fr; } }
  .asw-honest li {
    padding: 10px 0 10px 28px;
    position: relative;
    font-size: 15px; line-height: 1.5;
    color: var(--text-muted);
  }
  .asw-honest li::before {
    content: "✗";
    position: absolute; left: 0; top: 8px;
    color: var(--accent);
    font-weight: 800; font-size: 18px;
  }

  .asw-steps {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-top: 16px;
  }
  @media (max-width: 800px) { .asw-steps { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 500px) { .asw-steps { grid-template-columns: 1fr; } }

  .asw-step {
    background: var(--surface, #fff);
    border: 1px solid var(--border);
    border-radius: var(--asw-radius);
    padding: 24px;
    position: relative;
    transition: all 0.25s var(--ease-out);
  }
  .asw-step:hover {
    transform: translateY(-3px);
    border-color: var(--brand);
    box-shadow: var(--shadow-soft);
  }
  .asw-step-num {
    font-family: var(--font-display);
    font-size: 32px; font-weight: 800;
    line-height: 1;
    color: var(--brand);
    margin-bottom: 16px;
    font-variant-numeric: tabular-nums;
  }
  .asw-step h4 {
    font-family: var(--font-display);
    font-size: 16px; font-weight: 700;
    line-height: 1.3;
    margin: 0 0 8px;
  }
  .asw-step p {
    font-size: 13px; line-height: 1.6;
    color: var(--text-soft);
    margin: 0;
  }
  .asw-step-time {
    display: inline-block;
    margin-top: 12px;
    font-size: 11px; font-weight: 700;
    color: var(--text-soft);
    letter-spacing: 0.5px; text-transform: uppercase;
    padding: 4px 8px;
    background: var(--bg-alt, #f2f2ec);
    border-radius: 6px;
  }

  .asw-example-wrap {
    background: linear-gradient(135deg, var(--section-dark, #0b1320) 0%, var(--section-dark-2, #111c2e) 100%);
    color: #fff;
    border-radius: var(--asw-radius-lg);
    padding: clamp(36px, 5vw, 56px);
    margin-top: 16px;
    position: relative;
    overflow: hidden;
  }
  .asw-example-wrap::before {
    content: "";
    position: absolute;
    top: -100px; right: -100px;
    width: 400px; height: 400px;
    background: radial-gradient(circle, var(--brand-glow, rgba(13, 148, 136, 0.18)) 0%, transparent 60%);
    pointer-events: none;
  }
  .asw-example-wrap > * { position: relative; }

  .asw-example-eyebrow {
    font-size: 11px; font-weight: 700;
    color: var(--brand-soft);
    letter-spacing: 0.8px; text-transform: uppercase;
    margin-bottom: 14px;
  }
  .asw-example-wrap h3 {
    font-family: var(--font-display);
    font-size: clamp(22px, 2.5vw, 30px);
    font-weight: 800;
    line-height: 1.25;
    margin: 0 0 12px;
    color: #fff;
  }
  .asw-example-context {
    color: rgba(255, 255, 255, 0.7);
    font-size: 15px;
    line-height: 1.6;
    margin: 0 0 32px;
    max-width: 600px;
  }

  .asw-issues {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 32px;
  }
  @media (max-width: 800px) { .asw-issues { grid-template-columns: 1fr; } }

  .asw-issue {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border-radius: 16px;
    padding: 24px;
  }
  .asw-issue-tag {
    display: inline-block;
    font-size: 10px; font-weight: 700;
    letter-spacing: 0.6px; text-transform: uppercase;
    background: rgba(168, 85, 247, 0.2);
    color: #c4b5fd;
    padding: 4px 8px;
    border-radius: 6px;
    margin-bottom: 12px;
  }
  .asw-issue h4 {
    font-family: var(--font-display);
    font-size: 16px; font-weight: 700;
    line-height: 1.35;
    margin: 0 0 10px;
    color: #fff;
  }
  .asw-issue p {
    font-size: 13px; line-height: 1.6;
    color: rgba(255, 255, 255, 0.75);
    margin: 0 0 16px;
  }
  .asw-issue-impact {
    display: inline-block;
    font-family: var(--font-display);
    color: #fbbf24;
    font-weight: 800;
    font-size: 13px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 12px;
    margin-top: 4px;
    width: 100%;
  }

  .asw-example-summary {
    border-top: 1px solid rgba(255, 255, 255, 0.12);
    padding-top: 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
  }
  .asw-example-summary-text {
    color: rgba(255, 255, 255, 0.85);
    font-size: 15px;
    line-height: 1.5;
    max-width: 520px;
  }
  .asw-example-summary-amount {
    font-family: var(--font-display);
    font-size: clamp(28px, 3.5vw, 38px);
    font-weight: 800;
    color: #fff;
    text-align: right;
  }
  .asw-example-summary-amount small {
    display: block;
    font-size: 12px; font-weight: 600;
    color: rgba(255, 255, 255, 0.6);
    letter-spacing: 0.4px; text-transform: uppercase;
    margin-top: 4px;
  }

  .asw-target {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
  }
  @media (max-width: 700px) { .asw-target { grid-template-columns: 1fr; } }

  .asw-target-col {
    padding: 28px;
    border-radius: var(--asw-radius);
    border: 1px solid var(--border);
  }
  .asw-target-col.is-yes {
    background: var(--success-soft, #f0faf4);
    border-color: rgba(21, 128, 61, 0.2);
  }
  .asw-target-col.is-no { background: var(--bg-alt, #f2f2ec); }
  .asw-target-col h4 {
    font-family: var(--font-display);
    font-size: 17px; font-weight: 700;
    margin: 0 0 16px;
    display: flex; align-items: center; gap: 8px;
  }
  .asw-target-col h4::before {
    width: 24px; height: 24px;
    border-radius: 50%;
    display: grid; place-items: center;
    font-size: 14px; font-weight: 800;
    flex-shrink: 0;
  }
  .asw-target-col.is-yes h4::before {
    content: "✓"; background: var(--success, #15803d); color: #fff;
  }
  .asw-target-col.is-no h4::before {
    content: "—"; background: var(--text-soft); color: #fff;
  }
  .asw-target-col ul { margin: 0; padding: 0; list-style: none; }
  .asw-target-col li {
    padding: 8px 0;
    font-size: 14px; line-height: 1.55;
    color: var(--text-muted);
  }

  .asw-faq-list { max-width: 760px; margin: 0 auto; }
  .asw-faq {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--asw-radius);
    margin-bottom: 12px;
    overflow: hidden;
    transition: border-color 0.2s ease;
  }
  .asw-faq:hover { border-color: var(--brand); }
  .asw-faq[open] {
    border-color: var(--brand);
    box-shadow: var(--shadow-sm);
  }
  .asw-faq summary {
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
  .asw-faq summary::-webkit-details-marker { display: none; }
  .asw-faq summary::after {
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
  .asw-faq[open] summary::after {
    content: "−";
    background: var(--brand);
    color: #fff;
    transform: rotate(180deg);
  }
  .asw-faq-content {
    padding: 0 28px 24px;
    color: var(--text-soft);
    font-size: 15px;
    line-height: 1.7;
  }
  .asw-faq-content p { margin: 0 0 12px; }
  .asw-faq-content p:last-child { margin: 0; }

  .asw-author {
    background: var(--sand, #f5f0e8);
    border-radius: var(--asw-radius-lg);
    padding: clamp(36px, 5vw, 56px);
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 32px;
    align-items: center;
    margin-top: 16px;
  }
  @media (max-width: 700px) {
    .asw-author { grid-template-columns: 1fr; text-align: center; }
  }
  .asw-author-photo {
    width: 120px; height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
    color: #fff;
    display: grid; place-items: center;
    font-family: var(--font-display);
    font-size: 42px; font-weight: 800;
    flex-shrink: 0;
    margin: 0 auto;
  }
  .asw-author-content h3 {
    font-family: var(--font-display);
    font-size: 22px; font-weight: 800;
    margin: 0 0 8px;
  }
  .asw-author-role {
    font-size: 14px; font-weight: 600;
    color: var(--brand-dark);
    margin: 0 0 16px;
  }
  .asw-author-content p {
    font-size: 15px; line-height: 1.65;
    color: var(--text-muted);
    margin: 0;
  }

  .asw-final {
    background: linear-gradient(180deg, var(--text, #0d0d0b) 0%, var(--dark-2, #1e293b) 100%);
    color: #fff;
    padding: clamp(60px, 9vw, 96px) 0;
    position: relative;
    overflow: hidden;
  }
  .asw-final::before {
    content: "";
    position: absolute;
    top: -100px; left: 50%;
    transform: translateX(-50%);
    width: 600px; height: 600px;
    background: radial-gradient(circle, var(--brand-glow) 0%, transparent 70%);
    pointer-events: none;
  }
  .asw-final-inner {
    position: relative;
    max-width: 640px;
    margin: 0 auto;
    text-align: center;
  }
  .asw-final h2 { color: #fff; margin-bottom: 16px; }
  .asw-final-sub {
    font-size: 17px;
    line-height: 1.6;
    color: rgba(255, 255, 255, 0.7);
    margin: 0 0 40px;
  }

  .asw-form {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-radius: var(--asw-radius);
    padding: clamp(24px, 4vw, 36px);
    text-align: left;
  }
  .asw-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
  }
  @media (max-width: 600px) { .asw-form-grid { grid-template-columns: 1fr; } }
  .asw-field { margin-bottom: 16px; }
  .asw-field-full { grid-column: 1 / -1; }
  .asw-field label {
    display: block;
    font-size: 13px; font-weight: 600;
    color: rgba(255, 255, 255, 0.85);
    margin-bottom: 8px;
  }
  .asw-field label .opt {
    font-weight: 400;
    color: rgba(255, 255, 255, 0.4);
    font-size: 12px;
    margin-left: 6px;
  }
  .asw-field input,
  .asw-field textarea {
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
  .asw-field input::placeholder,
  .asw-field textarea::placeholder { color: rgba(255, 255, 255, 0.35); }
  .asw-field input:focus,
  .asw-field textarea:focus {
    outline: none;
    border-color: var(--brand);
    background: rgba(255, 255, 255, 0.1);
  }
  .asw-field textarea { min-height: 90px; resize: vertical; }
  .asw-form-submit {
    width: 100%;
    padding: 18px;
    background: var(--brand, #0d9488);
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
  .asw-form-submit:hover {
    background: var(--brand-dark);
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(13, 148, 136, 0.3);
  }
  .asw-form-meta {
    text-align: center;
    margin-top: 20px;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.4);
    line-height: 1.5;
  }
  .asw-consent {
    margin: 4px 0 0;
    display: flex;
    gap: 10px;
    align-items: flex-start;
    color: rgba(255, 255, 255, 0.78);
    font-size: 13px;
    line-height: 1.55;
  }
  .asw-consent input[type="checkbox"] {
    margin-top: 3px;
    width: 16px;
    height: 16px;
  }
  .asw-consent a { color: #99f6e4; }
  .asw-form .form-feedback {
    margin: 0 0 12px;
    padding: 10px 12px;
    border-radius: 10px;
    font-size: 13px;
    border: 1px solid rgba(255, 255, 255, 0.18);
    display: none;
  }
  .asw-form .form-feedback.is-success {
    display: block;
    border-color: #34d399;
    background: rgba(16, 185, 129, 0.14);
    color: #d1fae5;
  }
  .asw-form .form-feedback.is-error {
    display: block;
    border-color: #fca5a5;
    background: rgba(239, 68, 68, 0.12);
    color: #fee2e2;
  }
</style>

<div class="asw-page">
  <section class="asw-hero">
    <div class="asw-container asw-hero-inner">
      <div class="asw-hero-grid">
        <div>
          <div class="asw-pill">Bezpłatny audyt strony — 5 firm tygodniowo</div>
          <h1>Twoja strona ma ruch, ale <em>nikt nie kupuje</em>?</h1>
          <p class="asw-hero-sub">
            45 minut. Wchodzimy razem na Twoją stronę krok po kroku — z perspektywy klienta. Wskazuję 3 największe blokady które tracą Ci leady i sprzedaż. Bez teoretycznych ramek UX, bez 200-stronicowych raportów. Konkrety które poprawisz w 2 tygodnie.
          </p>
          <div class="asw-hero-cta-row">
            <a href="#kontakt" class="asw-cta-primary" data-cta="audyt-www-hero-primary" data-cta-section="hero" data-cta-position="primary">Umów audyt strony</a>
            <a href="#proces" class="asw-cta-secondary" data-cta="audyt-www-hero-secondary-proces" data-cta-section="hero" data-cta-position="secondary">Co dostaję na piśmie?</a>
          </div>
          <div class="asw-trust-row">
            <span class="asw-trust-item">45 minut</span>
            <span class="asw-trust-item">Online (Google Meet)</span>
            <span class="asw-trust-item">100% bezpłatnie</span>
          </div>
        </div>

        <div>
          <div class="asw-funnel-card" aria-hidden="true">
            <div class="asw-funnel-header">firma-przykład.pl — kwiecień 2026</div>
            <div class="asw-funnel-period">Lejek konwersji ostatnich 30 dni</div>
            <div class="asw-funnel-step">
              <div class="asw-funnel-bar-wrap"><div class="asw-funnel-bar" style="width: 100%;">2 840 wejść</div></div>
              <div class="asw-funnel-step-info"><div class="asw-funnel-step-label">Strona główna</div><div class="asw-funnel-step-value">100%</div></div>
            </div>
            <div class="asw-funnel-step">
              <div class="asw-funnel-bar-wrap"><div class="asw-funnel-bar" style="width: 36%;">1 022</div></div>
              <div class="asw-funnel-step-info"><div class="asw-funnel-step-label">Strona usługi</div><div class="asw-funnel-step-value">36%</div></div>
            </div>
            <div class="asw-funnel-step">
              <div class="asw-funnel-bar-wrap"><div class="asw-funnel-bar is-loss" style="width: 11%;">312</div></div>
              <div class="asw-funnel-step-info"><div class="asw-funnel-step-label">Cennik</div><div class="asw-funnel-step-value">11%</div></div>
            </div>
            <div class="asw-funnel-step">
              <div class="asw-funnel-bar-wrap"><div class="asw-funnel-bar is-loss" style="width: 2.4%;">68</div></div>
              <div class="asw-funnel-step-info"><div class="asw-funnel-step-label">Formularz</div><div class="asw-funnel-step-value">2.4%</div></div>
            </div>
            <div class="asw-funnel-step">
              <div class="asw-funnel-bar-wrap"><div class="asw-funnel-bar is-loss" style="width: 0.7%;">19</div></div>
              <div class="asw-funnel-step-info"><div class="asw-funnel-step-label">Wysłanie</div><div class="asw-funnel-step-value">0.7%</div></div>
            </div>
            <div class="asw-funnel-summary">97.6% odwiedzających odchodzi przed wysłaniem formularza</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="asw-section asw-section-soft">
    <div class="asw-container">
      <div class="asw-section-head">
        <span class="asw-section-eyebrow">Co sprawdzam</span>
        <h2>Sześć obszarów, w których strony tracą najwięcej</h2>
        <p class="asw-section-intro">
          Audyt strony nie jest o tym, że strona jest "ładna" lub "brzydka". Jest o tym, czy <strong>prowadzi klienta od kliknięcia do zakupu</strong> — czy gubi go po drodze. Patrzę na to w sześciu wymiarach.
        </p>
      </div>
      <div class="asw-areas">
        <div class="asw-area"><span class="asw-area-num">01</span><h3>Pierwsze 5 sekund (above the fold)</h3><p>Czy klient w ciągu 5 sekund rozumie co sprzedajesz, dla kogo to jest i co ma zrobić. 70% odwiedzających opuszcza stronę dokładnie w tym momencie.</p></div>
        <div class="asw-area"><span class="asw-area-num">02</span><h3>Komunikat wartości i copy</h3><p>Czy nagłówki mówią o korzyści dla klienta, czy o Twojej firmie. Czy język jest klienta, czy Twojej branży. Czy jest jeden CTA czy dziesięć konkurujących.</p></div>
        <div class="asw-area"><span class="asw-area-num">03</span><h3>CTA i ścieżka konwersji</h3><p>Gdzie są przyciski. Czy widoczne. Czy mają sensowne teksty ("Wyślij wiadomość" zamiast "Wyślij"). Czy ścieżka od homepage do sprzedaży ma 3 kliki czy 8.</p></div>
        <div class="asw-area"><span class="asw-area-num">04</span><h3>Formularze i koszyki</h3><p>Najbardziej zaniedbane miejsce. 12 pól wymaganych, brak walidacji, fatalny komunikat błędu. Każde dodatkowe pole to spadek konwersji o 5–10%.</p></div>
        <div class="asw-area"><span class="asw-area-num">05</span><h3>Mobile UX (60% Twojego ruchu)</h3><p>Czy strona działa na telefonie. Czy przyciski są klikalne. Czy formularz da się wypełnić jedną ręką w drodze. Większość stron przegrywa właśnie tu.</p></div>
        <div class="asw-area"><span class="asw-area-num">06</span><h3>Speed i Core Web Vitals</h3><p>Strona ładująca się 6 sekund traci 50% odwiedzających. Sprawdzam LCP, CLS, INP — i mówię Ci czy wymagają programisty czy dobrej optymalizacji obrazków.</p></div>
      </div>
    </div>
  </section>

  <section class="asw-section">
    <div class="asw-container">
      <div class="asw-section-head">
        <span class="asw-section-eyebrow">Szczerze</span>
        <h2>Czego NIE dostaniesz w tym audycie</h2>
        <p class="asw-section-intro">
          Audyty stron robione przez agencje brandingowe są często powtarzaniem teorii UX z podręcznika. Mój audyt jest inny — i lepiej żebyś to wiedział.
        </p>
      </div>
      <div class="asw-honest">
        <div class="asw-honest-header"><div class="asw-honest-icon">!</div><strong>W audycie nie znajdziesz:</strong></div>
        <ul>
          <li>Komentarzy "kolory są nieładne" — nie audytuję estetyki, tylko konwersję</li>
          <li>200-stronicowego raportu UX z heatmapami i ścieżkami</li>
          <li>Sugestii przebudowy całej strony (rzadko ma sens)</li>
          <li>Wzorów "10 zasad doskonałej strony"</li>
          <li>Oferty redesignu w pierwszych 5 minutach</li>
          <li>Komentarzy o trendach designu na 2026 rok</li>
        </ul>
      </div>
    </div>
  </section>

  <section class="asw-section asw-section-soft" id="proces">
    <div class="asw-container">
      <div class="asw-section-head">
        <span class="asw-section-eyebrow">Jak to działa</span>
        <h2>Cztery kroki, dwa dni, konkretny plan poprawek</h2>
        <p class="asw-section-intro">
          Audyt strony nie wymaga dostępów technicznych — przeglądam ją tak, jak Twój klient. Potrzebuję tylko link do Google Analytics (jeśli masz), żeby zobaczyć gdzie ludzie odpadają.
        </p>
      </div>
      <div class="asw-steps">
        <div class="asw-step"><div class="asw-step-num">01</div><h4>Wypełniasz formularz</h4><p>Adres strony, branża, jaki jest Twój główny cel (zapytanie, sprzedaż, rezerwacja). 30 sekund.</p><span class="asw-step-time">~30 sekund</span></div>
        <div class="asw-step"><div class="asw-step-num">02</div><h4>Dostęp do Analytics</h4><p>Jeśli masz Google Analytics 4, dodajesz mnie z prawami "Czytelnik". Bez tego patrzę tylko na samą stronę — z tym widzę realne ścieżki użytkowników.</p><span class="asw-step-time">~3 minuty</span></div>
        <div class="asw-step"><div class="asw-step-num">03</div><h4>Analizuję 24h przed</h4><p>Spędzam 60–90 minut przechodząc przez stronę krok po kroku. Mobile + desktop. Notuję wszystkie momenty zamieszania, blokady, brakujące informacje.</p><span class="asw-step-time">60–90 min pracy</span></div>
        <div class="asw-step"><div class="asw-step-num">04</div><h4>Rozmowa 45 minut</h4><p>Pokazuję na żywo gdzie strona traci klientów. Tłumaczę dlaczego. Mówię które poprawki są darmowe, które wymagają programisty, które warto zostawić.</p><span class="asw-step-time">45 minut</span></div>
      </div>
    </div>
  </section>

  <section class="asw-section">
    <div class="asw-container">
      <div class="asw-section-head">
        <span class="asw-section-eyebrow">Konkret zamiast obietnic</span>
        <h2>Tak wyglądał audyt strony usługowej z Krakowa</h2>
        <p class="asw-section-intro">
          Zanonimizowane dane. Firma usługowa B2B, strona istnieje 3 lata. Miesięczny ruch: ~2 800 odwiedzin. Liczba zapytań przez formularz: 19/mies. Klient wydawał 4 000 zł/mies na Google Ads i nie rozumiał, dlaczego nie ma leadów. Po audycie wiadomo dlaczego.
        </p>
      </div>
      <div class="asw-example-wrap">
        <div class="asw-example-eyebrow">Pierwsze 90 minut analizy</div>
        <h3>Trzy blokady które od 3 lat odbijały 97% odwiedzających</h3>
        <p class="asw-example-context">
          To nie były problemy z designem. Strona była ładna, miała profesjonalne zdjęcia i dobry copywriting. Ale była zbudowana pod prezentację firmy, nie pod konwersję klienta. Trzy zmiany — i konwersja podwoiła się w 60 dni.
        </p>
        <div class="asw-issues">
          <div class="asw-issue">
            <span class="asw-issue-tag">Hero</span>
            <h4>Brak komunikatu w pierwszych 5 sekundach</h4>
            <p>Hero strony pokazywał logo, slogan firmy ("Innowacyjne rozwiązania dla biznesu") i animowane zdjęcie. Klient nie widział w 5 sekund: co sprzedajesz, dla kogo, dlaczego warto. 64% odwiedzających odbijało się od homepage.</p>
            <div class="asw-issue-impact">→ -64% konwersji już w pierwszej sekcji</div>
          </div>
          <div class="asw-issue">
            <span class="asw-issue-tag">Formularz</span>
            <h4>13 wymaganych pól w formularzu</h4>
            <p>Formularz "Zamów wycenę" wymagał: imię, nazwisko, firma, NIP, adres, email, telefon, branża, wielkość firmy, budżet, opis projektu, deadline, źródło polecenia. Konwersja form view → submit: 6%. Po skróceniu do 3 pól: 28%.</p>
            <div class="asw-issue-impact">→ +367% konwersji formularza</div>
          </div>
          <div class="asw-issue">
            <span class="asw-issue-tag">Mobile</span>
            <h4>Numer telefonu nieklikalny na mobile</h4>
            <p>Numer telefonu w stopce był tekstem, nie linkiem `tel:`. 60% ruchu z mobile widzi telefon, ale nie może zadzwonić jednym klikiem. Na małej zmianie technicznej — wzrost połączeń telefonicznych o 4× w pierwszym tygodniu.</p>
            <div class="asw-issue-impact">→ +400% połączeń z mobile w 7 dni</div>
          </div>
        </div>
        <div class="asw-example-summary">
          <div class="asw-example-summary-text">
            <strong>Trzy zmiany wdrożone w 2 tygodnie:</strong> nowy hero z konkretnym komunikatem, formularz skrócony do 3 pól, klikalne dane kontaktowe na mobile. Po 60 dniach: 19 zapytań/mies → 47 zapytań/mies przy tym samym ruchu.
          </div>
          <div class="asw-example-summary-amount">
            +147%
            <small>wzrost zapytań w 60 dni</small>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="asw-section asw-section-soft">
    <div class="asw-container">
      <div class="asw-section-head">
        <span class="asw-section-eyebrow">Dla kogo</span>
        <h2>Dla kogo audyt strony ma sens</h2>
        <p class="asw-section-intro">
          Audyt strony to nie audyt SEO ani Google Ads. Tu chodzi o <strong>konwersję ruchu który już masz</strong>. Najlepiej działa, gdy masz już ruch i wiesz że coś nie gra.
        </p>
      </div>
      <div class="asw-target">
        <div class="asw-target-col is-yes">
          <h4>Audyt ma sens jeśli:</h4>
          <ul>
            <li>→ Masz minimum 500 odwiedzin/mies (są dane do analizy)</li>
            <li>→ Wydajesz na reklamy ale leadów mniej niż się spodziewałeś</li>
            <li>→ Konkurencja konwertuje lepiej, a oferta podobna</li>
            <li>→ Masz wrażenie że strona jest "ładna ale nie sprzedaje"</li>
            <li>→ Strona istnieje minimum 6 miesięcy</li>
            <li>→ Masz Google Analytics (nawet jeśli go nie używasz)</li>
          </ul>
        </div>
        <div class="asw-target-col is-no">
          <h4>Audyt NIE ma sensu jeśli:</h4>
          <ul>
            <li>→ Strona dopiero powstała (brak ruchu = brak danych)</li>
            <li>→ Twój problem to brak ruchu, nie konwersji (potrzebujesz audytu SEO/Ads)</li>
            <li>→ Chcesz redesign — to nie audyt, to nowy projekt</li>
            <li>→ Sprzedajesz produkty zakazane (broń, hazard, suplementy wątpliwe)</li>
            <li>→ Twoja strona nie działa technicznie (najpierw napraw, potem audyt)</li>
            <li>→ Szukasz copywritera (to nie audyt)</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section class="asw-section">
    <div class="asw-container">
      <div class="asw-section-head">
        <span class="asw-section-eyebrow">Kto patrzy na Twoją stronę</span>
        <h2>Po drugiej stronie ekranu</h2>
      </div>
      <div class="asw-author">
        <div class="asw-author-photo">SK</div>
        <div class="asw-author-content">
          <h3>Sebastian Kelm</h3>
          <p class="asw-author-role">Konsultant marketingu — strony B2B · Google Ads · Meta Ads · SEO</p>
          <p>
            Pracuję sam, bez agencji i podwykonawców. Specjalizuję się w generowaniu leadów dla firm B2B — w 80% przypadków problem nie tkwi w reklamach, tylko właśnie w stronie do której prowadzą. Audyt strony robię osobiście — przechodzę przez nią klatka po klatce, jak Twój klient. Nie używam AI ani gotowych checklistów. Jeśli się dogadamy na współpracę, też pracuję z Tobą bezpośrednio. Jeśli stwierdzę że Twoja strona jest OK i problem jest gdzie indziej — powiem Ci to wprost.
          </p>
        </div>
      </div>
    </div>
  </section>

  <section class="asw-section asw-section-soft">
    <div class="asw-container">
      <div class="asw-section-head is-center">
        <span class="asw-section-eyebrow">FAQ</span>
        <h2>Pytania które dostaję najczęściej</h2>
      </div>
      <div class="asw-faq-list">
        <details class="asw-faq"><summary>Naprawdę bezpłatne? Jaki jest haczyk?</summary><div class="asw-faq-content"><p>Nie ma haczyka. Robię to z dwóch powodów: (1) chcę pokazać jak pracuję — to lepsza wizytówka niż portfolio, (2) z 10 audytów 2–3 firmy pytają o stałą współpracę po wdrożeniu zmian.</p><p>Nie sprzedaję na audycie — sprzedaję wyniki. Audyt to wyniki, których jeszcze nie masz.</p></div></details>
        <details class="asw-faq"><summary>Czym ten audyt różni się od audytu SEO?</summary><div class="asw-faq-content"><p>Audyt SEO odpowiada na pytanie "dlaczego nie mam ruchu z Google". Audyt strony odpowiada na pytanie "dlaczego ruch który mam nie konwertuje". Dwa różne problemy, dwa różne audyty.</p><p>Jeśli masz wrażenie że masz oba problemy — porozmawiajmy najpierw, powiem Ci który zacząć. Często wystarczy jeden, żeby zobaczyć efekty.</p></div></details>
        <details class="asw-faq"><summary>Czy potrzebujesz dostępu do mojej strony?</summary><div class="asw-faq-content"><p>Nie. Audyt strony przeprowadzam jako zwykły użytkownik — odwiedzam stronę z mojego komputera i telefonu, klikam, formularzy nie wysyłam (poza testowymi). Nie potrzebuję loginu do CMS, FTP, baz danych.</p><p>Jedyne co przyspieszy audyt — dostęp "Czytelnik" do Google Analytics (jeśli masz). Bez Analytics audyt to obserwacja samej strony, z Analytics widzę realne dane gdzie klienci odpadają.</p></div></details>
        <details class="asw-faq"><summary>Co jeśli moja strona jest na Wordpressie / Shopify / własnej platformie?</summary><div class="asw-faq-content"><p>To bez znaczenia. Audyt skupia się na <strong>doświadczeniu użytkownika</strong>, nie technologii. Strona na WordPress'ie, Shopify, Wix, Squarespace czy własnej platformie — wszystkie podlegają tym samym zasadom konwersji.</p><p>Po audycie powiem Ci, czy poprawki są możliwe na Twojej platformie i orientacyjnie ile będą kosztować — czasem to drobne zmiany w ustawieniach, czasem trzeba programisty.</p></div></details>
        <details class="asw-faq"><summary>Co jeśli moja strona dopiero powstała?</summary><div class="asw-faq-content"><p>Wtedy audyt nie ma sensu — bo nie ma jeszcze danych. Bez ruchu nie wiem gdzie ludzie odpadają, bez wniosków rzeczywistych nie zrobię konkretnych rekomendacji.</p><p>Lepiej poczekać 2–3 miesiące, zebrać 1 000+ wejść i wtedy zrobić audyt. W międzyczasie mogę pomóc inaczej — przejrzeniem strony pod kątem podstawowych zasad UX, ale bez głębokich wniosków.</p></div></details>
        <details class="asw-faq"><summary>Czy zaproponujesz konkretnych programistów do wdrożenia zmian?</summary><div class="asw-faq-content"><p>Jeśli okaże się że potrzebujesz wdrożenia — mogę polecić sprawdzonych programistów / agencji z którymi pracowałem. Bez prowizji od ich strony, po prostu lista osób które robią dobrą robotę.</p><p>Część poprawek z audytu możesz wdrożyć sam (drobne zmiany w treści, edytorze WP). Inne wymagają specjalisty — wtedy konkretnie napiszę "zatrudnij programistę PHP na 4–6 godzin".</p></div></details>
        <details class="asw-faq"><summary>Co dostaję na piśmie po audycie?</summary><div class="asw-faq-content"><p>1–2 strony A4 z trzema głównymi blokadami konwersji, krokami do wdrożenia w kolejności priorytetu, szacunkowym wpływem każdej poprawki na konwersję. Wysyłam mailem 24h po rozmowie.</p><p>Bez 50-stronicowego raportu UX, bez heatmap. Tekst do przeczytania w 5 minut, decyzja na podstawie konkretu.</p></div></details>
        <details class="asw-faq"><summary>Jak długo czeka się na termin?</summary><div class="asw-faq-content"><p>Zwykle 5–10 dni od wypełnienia formularza. Audyt strony wymaga więcej pracy przed rozmową niż audyt Google Ads — przechodzę przez stronę krok po kroku, na desktopie i mobile.</p><p>Robię max 5 audytów tygodniowo, żeby utrzymać jakość. Jeśli wszystkie sloty są zajęte, dostajesz mail z najwcześniejszym wolnym terminem.</p></div></details>
      </div>
    </div>
  </section>

  <section class="asw-final" id="kontakt">
    <div class="asw-container asw-final-inner">
      <h2>Umów bezpłatny audyt strony</h2>
      <p class="asw-final-sub">
        Wypełnij formularz, odpowiem w ciągu 24h z propozycją terminów. Bez handlowych telefonów, bez ofert redesignu — sam wiesz że czasem ktoś po prostu robi dobrą robotę.
      </p>

      <form
        class="asw-form"
        id="audit-www-form"
        method="post"
        action="<?php echo esc_url(admin_url("admin-post.php")); ?>"
        data-audit-form="strona-www"
        data-upsellio-lead-form="1"
        data-upsellio-server-form="1"
      >
        <input type="hidden" name="action" value="upsellio_submit_lead">
        <input type="hidden" name="redirect_url" value="<?php echo esc_url((get_permalink() ?: home_url("/audyt-strony-www/")) . "#kontakt"); ?>">
        <input type="hidden" name="lead_form_origin" value="audit-form">
        <input type="hidden" name="lead_source" value="audit-form">
        <input type="hidden" name="lead_service" value="Audyt strony www">
        <input type="hidden" name="lead_goal" value="Audyt konwersji strony i plan poprawek UX/CRO">
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

        <div class="asw-form-grid">
          <div class="asw-field">
            <label for="audit_www_name">Imię</label>
            <input type="text" id="audit_www_name" name="lead_name" placeholder="Jan" autocomplete="given-name" required>
          </div>
          <div class="asw-field">
            <label for="audit_www_email">Email</label>
            <input type="email" id="audit_www_email" name="lead_email" placeholder="jan@firma.pl" autocomplete="email" required>
          </div>
          <div class="asw-field asw-field-full">
            <label for="audit_www_url">Adres strony www</label>
            <input type="text" id="audit_www_url" name="lead_company" placeholder="https://twoja-firma.pl" required>
          </div>
          <div class="asw-field asw-field-full">
            <label for="audit_www_goal">Główny cel strony <span class="opt">opcjonalnie</span></label>
            <input type="text" id="audit_www_goal" name="lead_goal" placeholder="np. zapytania ofertowe, rezerwacja konsultacji, sprzedaż produktów">
          </div>
          <div class="asw-field asw-field-full">
            <label for="audit_www_message">Co Cię najbardziej trapi? <span class="opt">opcjonalnie, ale pomaga</span></label>
            <textarea id="audit_www_message" name="lead_message" placeholder="np. Wydaję 3000 zł na Google Ads, ruch jest, ale formularzy mało. Albo: konkurencja konwertuje lepiej i nie wiem czemu." required></textarea>
          </div>
        </div>

        <label class="asw-consent">
          <input type="checkbox" name="lead_consent" value="1" required>
          <span>Wyrażam zgodę na kontakt w sprawie audytu i akceptuję <a href="<?php echo esc_url(home_url("/polityka-prywatnosci/")); ?>" target="_blank" rel="noopener noreferrer">politykę prywatności</a>.</span>
        </label>

        <button type="submit" class="asw-form-submit" data-cta="audyt-www-final-submit" data-cta-section="final-form" data-cta-position="submit">Umów bezpłatny audyt strony →</button>

        <p class="asw-form-meta">
          Twoje dane służą wyłącznie do umówienia audytu. Nie zapisuję Cię na newsletter,<br>nie sprzedaję bazy. Po audycie kasuję dane jeśli nie nawiążemy współpracy.
        </p>
      </form>
    </div>
  </section>

</div>

<?php get_footer(); ?>
