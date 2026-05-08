<?php
/**
 * Template Name: Audyt Meta Ads — landing
 *
 * Landing dla bezpłatnego audytu Meta Ads (Facebook + Instagram).
 * Główna fraza: "audyt facebook ads" (10-100 volume, 12,22-24,50 zł CPC).
 *
 * Różnice względem audytu Google Ads:
 * - Meta to platforma wizualna - nacisk na kreację, nie tylko struktur
 * - CBO/ABO, Advantage+, lookalike audiences - inny słownik problemów
 * - Klient Meta często ma e-commerce, nie usługi B2B - inny profil
 */
get_header();
?>

<style>
  :root {
    --ama-radius: 20px;
    --ama-radius-lg: 28px;
  }

  .ama-page {
    background: var(--bg, #fafaf6);
    color: var(--text, #0d0d0b);
    font-family: var(--font-body, "DM Sans"), system-ui, sans-serif;
    overflow-x: hidden;
  }
  .ama-page * { box-sizing: border-box; }
  .ama-container { max-width: 1100px; margin: 0 auto; padding: 0 24px; }
  .ama-hero {
    position: relative;
    padding: clamp(60px, 10vw, 100px) 0 clamp(50px, 8vw, 80px);
    overflow: hidden;
  }
  .ama-hero::before {
    content: "";
    position: absolute; inset: 0;
    background:
      radial-gradient(ellipse 700px 400px at 80% 30%, rgba(59, 130, 246, 0.06), transparent),
      radial-gradient(ellipse 500px 300px at 10% 70%, rgba(13, 148, 136, 0.07), transparent);
    pointer-events: none;
    z-index: 0;
  }
  .ama-hero-inner { position: relative; z-index: 1; }
  .ama-hero-grid {
    display: grid;
    grid-template-columns: 1.15fr 1fr;
    gap: 56px;
    align-items: center;
  }
  @media (max-width: 900px) { .ama-hero-grid { grid-template-columns: 1fr; gap: 40px; } }
  .ama-pill {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--brand-soft, #ccfbf1);
    color: var(--brand-dark, #0f766e);
    padding: 8px 16px;
    border-radius: 999px;
    font-size: 12px; font-weight: 700;
    letter-spacing: 0.4px; text-transform: uppercase;
    margin-bottom: 24px;
  }
  .ama-pill::before {
    content: "";
    width: 6px; height: 6px;
    background: var(--brand, #0d9488);
    border-radius: 50%;
    animation: amaPulse 2s ease-in-out infinite;
  }
  @keyframes amaPulse { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.5; transform: scale(0.85); } }
  .ama-hero h1 {
    font-family: var(--font-display, "Bricolage Grotesque"), serif;
    font-size: clamp(36px, 5.5vw, 60px);
    font-weight: 800;
    line-height: 1.04;
    letter-spacing: -0.025em;
    margin: 0 0 24px;
    color: var(--text);
  }
  .ama-hero h1 em {
    font-style: normal;
    background: linear-gradient(120deg, var(--brand) 0%, var(--brand-dark) 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    color: transparent;
  }
  .ama-hero-sub {
    font-size: clamp(16px, 2vw, 19px);
    line-height: 1.6;
    color: var(--text-soft, #7a7a72);
    margin: 0 0 32px;
    max-width: 540px;
  }
  .ama-hero-cta-row { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 32px; }
  .ama-cta-primary {
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
  .ama-cta-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(13, 13, 11, 0.2); color: #fff; }
  .ama-cta-primary::after { content: "→"; transition: transform 0.2s ease; }
  .ama-cta-primary:hover::after { transform: translateX(4px); }
  .ama-cta-secondary {
    display: inline-flex; align-items: center; gap: 8px;
    color: var(--text);
    padding: 18px 24px;
    font-size: 15px; font-weight: 600;
    text-decoration: none;
    border-bottom: 2px solid transparent;
    transition: all 0.15s ease;
  }
  .ama-cta-secondary:hover { border-bottom-color: var(--brand); color: var(--brand); }
  .ama-trust-row { display: flex; gap: 24px; flex-wrap: wrap; color: var(--text-soft, #7a7a72); font-size: 14px; }
  .ama-trust-item { display: inline-flex; align-items: center; gap: 8px; }
  .ama-trust-item::before { content: "✓"; color: var(--brand); font-weight: 800; font-size: 16px; }
  .ama-dashboard {
    background: #fff;
    border: 1px solid var(--border, #e8e8e0);
    border-radius: var(--ama-radius);
    padding: 24px;
    box-shadow: var(--shadow, 0 22px 60px rgba(15, 23, 42, 0.1));
    position: relative;
    transform: rotate(0.5deg);
  }
  .ama-dashboard::before {
    content: "Audyt z 24.04.2026";
    position: absolute;
    top: -12px; right: 24px;
    background: linear-gradient(135deg, #1877f2 0%, #4f46e5 100%);
    color: #fff;
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.5px; text-transform: uppercase;
  }
  .ama-dashboard-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border);
  }
  .ama-dashboard-account { display: flex; align-items: center; gap: 10px; }
  .ama-dashboard-logo {
    width: 32px; height: 32px;
    border-radius: 8px;
    background: linear-gradient(135deg, #1877f2 0%, #4f46e5 100%);
    display: grid; place-items: center;
    color: #fff;
    font-size: 14px; font-weight: 800;
  }
  .ama-dashboard-account-name { font-family: var(--font-display); font-size: 13px; font-weight: 700; color: var(--text); }
  .ama-dashboard-account-meta { font-size: 11px; color: var(--text-soft); }
  .ama-dashboard-status {
    background: var(--success-soft, #f0faf4);
    color: var(--success, #15803d);
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.4px;
  }
  .ama-dashboard-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px; }
  .ama-stat { background: var(--bg-alt, #f2f2ec); padding: 14px 16px; border-radius: 10px; }
  .ama-stat-label {
    font-size: 10px; font-weight: 700;
    color: var(--text-soft);
    letter-spacing: 0.5px; text-transform: uppercase;
    margin-bottom: 4px;
  }
  .ama-stat-value { font-family: var(--font-display); font-size: 20px; font-weight: 800; color: var(--text); font-variant-numeric: tabular-nums; }
  .ama-stat-trend { font-size: 11px; font-weight: 700; margin-top: 2px; }
  .ama-stat-trend.is-bad { color: #dc2626; }
  .ama-stat-trend.is-good { color: var(--success); }
  .ama-dashboard-issue {
    background: #fef2f2;
    border-left: 3px solid #dc2626;
    padding: 12px 14px;
    border-radius: 0 8px 8px 0;
    font-size: 12.5px;
    line-height: 1.5;
    color: var(--text-muted);
  }
  .ama-dashboard-issue strong {
    display: block;
    color: #b91c1c;
    font-size: 11px;
    letter-spacing: 0.4px; text-transform: uppercase;
    margin-bottom: 4px;
  }
  .ama-section { padding: clamp(60px, 8vw, 100px) 0; position: relative; }
  .ama-section-soft { background: var(--surface, #fff); }
  .ama-section-head { max-width: 720px; margin: 0 0 56px; }
  .ama-section-head.is-center { margin: 0 auto 56px; text-align: center; }
  .ama-section-eyebrow {
    font-size: 12px; font-weight: 700;
    color: var(--brand-dark);
    letter-spacing: 0.8px; text-transform: uppercase;
    margin-bottom: 12px;
    display: inline-block;
  }
  .ama-section h2 {
    font-family: var(--font-display);
    font-size: clamp(28px, 3.8vw, 42px);
    font-weight: 800;
    line-height: 1.15;
    letter-spacing: -0.018em;
    margin: 0 0 16px;
    color: var(--text);
  }
  .ama-section-intro {
    font-size: clamp(16px, 1.6vw, 18px);
    line-height: 1.65;
    color: var(--text-soft);
    margin: 0;
  }
  .ama-areas { display: grid; grid-template-columns: 1.2fr 1fr; gap: 20px; }
  @media (max-width: 800px) { .ama-areas { grid-template-columns: 1fr; } }
  .ama-area-big {
    grid-column: 1;
    grid-row: 1 / 3;
    background: linear-gradient(135deg, #1877f2 0%, #4f46e5 100%);
    color: #fff;
    border-radius: var(--ama-radius);
    padding: 36px;
    position: relative;
    overflow: hidden;
  }
  @media (max-width: 800px) { .ama-area-big { grid-row: auto; } }
  .ama-area-big::before {
    content: "";
    position: absolute;
    top: -150px; right: -150px;
    width: 350px; height: 350px;
    background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 60%);
    pointer-events: none;
  }
  .ama-area-big > * { position: relative; }
  .ama-area-num { font-family: var(--font-display); font-size: 13px; font-weight: 700; opacity: 0.7; letter-spacing: 0.6px; margin-bottom: 8px; }
  .ama-area-big h3 { font-family: var(--font-display); font-size: clamp(22px, 2.5vw, 28px); font-weight: 800; line-height: 1.2; margin: 0 0 14px; color: #fff; }
  .ama-area-big > p { font-size: 15px; line-height: 1.65; color: rgba(255, 255, 255, 0.85); margin: 0 0 24px; }
  .ama-area-checks {
    margin: 0; padding: 0;
    list-style: none;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    padding-top: 20px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px 20px;
  }
  @media (max-width: 600px) { .ama-area-checks { grid-template-columns: 1fr; } }
  .ama-area-checks li {
    padding: 4px 0 4px 20px;
    position: relative;
    font-size: 13px;
    line-height: 1.5;
    color: rgba(255, 255, 255, 0.9);
  }
  .ama-area-checks li::before {
    content: "→";
    position: absolute; left: 0;
    color: rgba(255, 255, 255, 0.7);
    font-weight: 700;
  }
  .ama-area-small {
    background: var(--surface, #fff);
    border: 1px solid var(--border);
    border-radius: var(--ama-radius);
    padding: 28px;
    transition: all 0.25s var(--ease-out);
  }
  .ama-area-small:hover { border-color: var(--brand); transform: translateY(-3px); box-shadow: var(--shadow-soft); }
  .ama-area-small h3 { font-family: var(--font-display); font-size: 17px; font-weight: 700; line-height: 1.3; margin: 8px 0 8px; }
  .ama-area-small p { font-size: 14px; line-height: 1.6; color: var(--text-soft); margin: 0; }
  .ama-area-small .ama-area-num { color: var(--brand-dark); opacity: 1; }
  .ama-honest {
    background: linear-gradient(135deg, var(--accent-soft, #fff7ed) 0%, #fff 100%);
    border: 1px solid var(--accent-line, #fed7aa);
    border-radius: var(--ama-radius);
    padding: clamp(28px, 4vw, 44px);
    margin-top: 32px;
  }
  .ama-honest-header { display: flex; gap: 16px; align-items: center; margin-bottom: 20px; }
  .ama-honest-icon {
    flex-shrink: 0;
    width: 44px; height: 44px;
    border-radius: 12px;
    background: var(--accent, #f97316);
    color: #fff;
    display: grid; place-items: center;
    font-size: 22px; font-weight: 800;
  }
  .ama-honest-header strong { font-family: var(--font-display); font-size: 18px; font-weight: 700; }
  .ama-honest ul {
    margin: 0; padding: 0; list-style: none;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px 24px;
  }
  @media (max-width: 600px) { .ama-honest ul { grid-template-columns: 1fr; } }
  .ama-honest li {
    padding: 10px 0 10px 28px;
    position: relative;
    font-size: 15px; line-height: 1.5;
    color: var(--text-muted);
  }
  .ama-honest li::before { content: "✗"; position: absolute; left: 0; top: 8px; color: var(--accent); font-weight: 800; font-size: 18px; }
  .ama-steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: 16px; }
  @media (max-width: 800px) { .ama-steps { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 500px) { .ama-steps { grid-template-columns: 1fr; } }
  .ama-step {
    background: var(--surface, #fff);
    border: 1px solid var(--border);
    border-radius: var(--ama-radius);
    padding: 24px;
    position: relative;
    transition: all 0.25s var(--ease-out);
  }
  .ama-step:hover { transform: translateY(-3px); border-color: var(--brand); box-shadow: var(--shadow-soft); }
  .ama-step-num { font-family: var(--font-display); font-size: 32px; font-weight: 800; line-height: 1; color: var(--brand); margin-bottom: 16px; font-variant-numeric: tabular-nums; }
  .ama-step h4 { font-family: var(--font-display); font-size: 16px; font-weight: 700; line-height: 1.3; margin: 0 0 8px; }
  .ama-step p { font-size: 13px; line-height: 1.6; color: var(--text-soft); margin: 0; }
  .ama-step-time {
    display: inline-block;
    margin-top: 12px;
    font-size: 11px; font-weight: 700;
    color: var(--text-soft);
    letter-spacing: 0.5px; text-transform: uppercase;
    padding: 4px 8px;
    background: var(--bg-alt, #f2f2ec);
    border-radius: 6px;
  }
  .ama-example-wrap {
    background: linear-gradient(135deg, var(--section-dark, #0b1320) 0%, var(--section-dark-2, #111c2e) 100%);
    color: #fff;
    border-radius: var(--ama-radius-lg);
    padding: clamp(36px, 5vw, 56px);
    margin-top: 16px;
    position: relative;
    overflow: hidden;
  }
  .ama-example-wrap::before {
    content: "";
    position: absolute;
    top: -100px; right: -100px;
    width: 400px; height: 400px;
    background: radial-gradient(circle, rgba(59, 130, 246, 0.18) 0%, transparent 60%);
    pointer-events: none;
  }
  .ama-example-wrap > * { position: relative; }
  .ama-example-eyebrow { font-size: 11px; font-weight: 700; color: #60a5fa; letter-spacing: 0.8px; text-transform: uppercase; margin-bottom: 14px; }
  .ama-example-wrap h3 { font-family: var(--font-display); font-size: clamp(22px, 2.5vw, 30px); font-weight: 800; line-height: 1.25; margin: 0 0 12px; color: #fff; }
  .ama-example-context { color: rgba(255, 255, 255, 0.7); font-size: 15px; line-height: 1.6; margin: 0 0 32px; max-width: 600px; }
  .ama-findings { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 32px; }
  @media (max-width: 800px) { .ama-findings { grid-template-columns: 1fr; } }
  .ama-finding {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border-radius: 16px;
    padding: 24px;
  }
  .ama-finding-tag {
    display: inline-block;
    font-size: 10px; font-weight: 700;
    letter-spacing: 0.6px; text-transform: uppercase;
    background: rgba(220, 38, 38, 0.2);
    color: #fca5a5;
    padding: 4px 8px;
    border-radius: 6px;
    margin-bottom: 12px;
  }
  .ama-finding h4 { font-family: var(--font-display); font-size: 16px; font-weight: 700; line-height: 1.35; margin: 0 0 10px; color: #fff; }
  .ama-finding p { font-size: 13px; line-height: 1.6; color: rgba(255, 255, 255, 0.75); margin: 0 0 16px; }
  .ama-finding-cost { display: inline-flex; align-items: baseline; gap: 4px; font-family: var(--font-display); color: #fbbf24; font-weight: 800; }
  .ama-finding-cost .num { font-size: 24px; font-variant-numeric: tabular-nums; }
  .ama-finding-cost .unit { font-size: 12px; opacity: 0.85; }
  .ama-example-summary {
    border-top: 1px solid rgba(255, 255, 255, 0.12);
    padding-top: 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
  }
  .ama-example-summary-text { color: rgba(255, 255, 255, 0.85); font-size: 15px; line-height: 1.5; max-width: 520px; }
  .ama-example-summary-amount { font-family: var(--font-display); font-size: clamp(28px, 3.5vw, 38px); font-weight: 800; color: #fff; text-align: right; }
  .ama-example-summary-amount small {
    display: block;
    font-size: 12px; font-weight: 600;
    color: rgba(255, 255, 255, 0.6);
    letter-spacing: 0.4px; text-transform: uppercase;
    margin-top: 4px;
  }
  .ama-target { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
  @media (max-width: 700px) { .ama-target { grid-template-columns: 1fr; } }
  .ama-target-col { padding: 28px; border-radius: var(--ama-radius); border: 1px solid var(--border); }
  .ama-target-col.is-yes { background: var(--success-soft, #f0faf4); border-color: rgba(21, 128, 61, 0.2); }
  .ama-target-col.is-no { background: var(--bg-alt, #f2f2ec); }
  .ama-target-col h4 {
    font-family: var(--font-display);
    font-size: 17px; font-weight: 700;
    margin: 0 0 16px;
    display: flex; align-items: center; gap: 8px;
  }
  .ama-target-col h4::before {
    width: 24px; height: 24px;
    border-radius: 50%;
    display: grid; place-items: center;
    font-size: 14px; font-weight: 800;
    flex-shrink: 0;
  }
  .ama-target-col.is-yes h4::before { content: "✓"; background: var(--success, #15803d); color: #fff; }
  .ama-target-col.is-no h4::before { content: "—"; background: var(--text-soft); color: #fff; }
  .ama-target-col ul { margin: 0; padding: 0; list-style: none; }
  .ama-target-col li { padding: 8px 0; font-size: 14px; line-height: 1.55; color: var(--text-muted); }
  .ama-faq-list { max-width: 760px; margin: 0 auto; }
  .ama-faq {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--ama-radius);
    margin-bottom: 12px;
    overflow: hidden;
    transition: border-color 0.2s ease;
  }
  .ama-faq:hover { border-color: var(--brand); }
  .ama-faq[open] { border-color: var(--brand); box-shadow: var(--shadow-sm); }
  .ama-faq summary {
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
  .ama-faq summary::-webkit-details-marker { display: none; }
  .ama-faq summary::after {
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
  .ama-faq[open] summary::after { content: "−"; background: var(--brand); color: #fff; transform: rotate(180deg); }
  .ama-faq-content { padding: 0 28px 24px; color: var(--text-soft); font-size: 15px; line-height: 1.7; }
  .ama-faq-content p { margin: 0 0 12px; }
  .ama-faq-content p:last-child { margin: 0; }
  .ama-author {
    background: var(--sand, #f5f0e8);
    border-radius: var(--ama-radius-lg);
    padding: clamp(36px, 5vw, 56px);
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 32px;
    align-items: center;
    margin-top: 16px;
  }
  @media (max-width: 700px) { .ama-author { grid-template-columns: 1fr; text-align: center; } }
  .ama-author-photo {
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
  .ama-author-content h3 { font-family: var(--font-display); font-size: 22px; font-weight: 800; margin: 0 0 8px; }
  .ama-author-role { font-size: 14px; font-weight: 600; color: var(--brand-dark); margin: 0 0 16px; }
  .ama-author-content p { font-size: 15px; line-height: 1.65; color: var(--text-muted); margin: 0; }
  .ama-final {
    background: linear-gradient(180deg, var(--text, #0d0d0b) 0%, var(--dark-2, #1e293b) 100%);
    color: #fff;
    padding: clamp(60px, 9vw, 96px) 0;
    position: relative;
    overflow: hidden;
  }
  .ama-final::before {
    content: "";
    position: absolute;
    top: -100px; left: 50%;
    transform: translateX(-50%);
    width: 600px; height: 600px;
    background: radial-gradient(circle, rgba(59, 130, 246, 0.18) 0%, transparent 70%);
    pointer-events: none;
  }
  .ama-final-inner { position: relative; max-width: 640px; margin: 0 auto; text-align: center; }
  .ama-final h2 { color: #fff; margin-bottom: 16px; }
  .ama-final-sub { font-size: 17px; line-height: 1.6; color: rgba(255, 255, 255, 0.7); margin: 0 0 40px; }
  .ama-form {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-radius: var(--ama-radius);
    padding: clamp(24px, 4vw, 36px);
    text-align: left;
  }
  .ama-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  @media (max-width: 600px) { .ama-form-grid { grid-template-columns: 1fr; } }
  .ama-field { margin-bottom: 16px; }
  .ama-field-full { grid-column: 1 / -1; }
  .ama-field label { display: block; font-size: 13px; font-weight: 600; color: rgba(255, 255, 255, 0.85); margin-bottom: 8px; }
  .ama-field label .opt { font-weight: 400; color: rgba(255, 255, 255, 0.4); font-size: 12px; margin-left: 6px; }
  .ama-field input,
  .ama-field textarea {
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
  .ama-field input::placeholder,
  .ama-field textarea::placeholder { color: rgba(255, 255, 255, 0.35); }
  .ama-field input:focus,
  .ama-field textarea:focus { outline: none; border-color: var(--brand); background: rgba(255, 255, 255, 0.1); }
  .ama-field textarea { min-height: 90px; resize: vertical; }
  .ama-form-submit {
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
  .ama-form-submit:hover { background: var(--brand-dark); transform: translateY(-2px); box-shadow: 0 12px 24px rgba(13, 148, 136, 0.3); }
  .ama-form-meta { text-align: center; margin-top: 20px; font-size: 12px; color: rgba(255, 255, 255, 0.4); line-height: 1.5; }
  .ama-consent {
    margin: 4px 0 0;
    display: flex;
    gap: 10px;
    align-items: flex-start;
    color: rgba(255, 255, 255, 0.78);
    font-size: 13px;
    line-height: 1.55;
  }
  .ama-consent input[type="checkbox"] { margin-top: 3px; width: 16px; height: 16px; }
  .ama-consent a { color: #99f6e4; }
  .ama-form .form-feedback {
    margin: 0 0 12px;
    padding: 10px 12px;
    border-radius: 10px;
    font-size: 13px;
    border: 1px solid rgba(255, 255, 255, 0.18);
    display: none;
  }
  .ama-form .form-feedback.is-success {
    display: block;
    border-color: #34d399;
    background: rgba(16, 185, 129, 0.14);
    color: #d1fae5;
  }
  .ama-form .form-feedback.is-error {
    display: block;
    border-color: #fca5a5;
    background: rgba(239, 68, 68, 0.12);
    color: #fee2e2;
  }
</style>

<div class="ama-page">
  <section class="ama-hero">
    <div class="ama-container ama-hero-inner">
      <div class="ama-hero-grid">
        <div>
          <div class="ama-pill">Bezpłatny audyt Meta Ads — 5 firm tygodniowo</div>
          <h1>Pokażę Ci, dlaczego Meta Ads <em>palą budżet</em>, a nie sprzedają</h1>
          <p class="ama-hero-sub">
            30 minut. Patrzymy razem na Twoje konto Facebook + Instagram, sprawdzam strukturę kampanii, kreację, targetowanie i Pixel. Wskażę 3 największe błędy które blokują wyniki — i powiem co naprawić, żeby reklamy zaczęły się zwracać.
          </p>
          <div class="ama-hero-cta-row">
            <a href="#kontakt" class="ama-cta-primary" data-cta="audyt-meta-hero-primary" data-cta-section="hero" data-cta-position="primary">Umów audyt Meta Ads</a>
            <a href="#proces" class="ama-cta-secondary" data-cta="audyt-meta-hero-secondary-proces" data-cta-section="hero" data-cta-position="secondary">Co dostaję na piśmie?</a>
          </div>
          <div class="ama-trust-row">
            <span class="ama-trust-item">30 minut</span>
            <span class="ama-trust-item">Online (Google Meet)</span>
            <span class="ama-trust-item">100% bezpłatnie</span>
          </div>
        </div>
        <div>
          <div class="ama-dashboard" aria-hidden="true">
            <div class="ama-dashboard-header">
              <div class="ama-dashboard-account">
                <div class="ama-dashboard-logo">M</div>
                <div>
                  <div class="ama-dashboard-account-name">Konto reklamowe</div>
                  <div class="ama-dashboard-account-meta">Ads Manager — kampania PROD</div>
                </div>
              </div>
              <span class="ama-dashboard-status">Aktywne</span>
            </div>
            <div class="ama-dashboard-stats">
              <div class="ama-stat">
                <div class="ama-stat-label">CPM (30 dni)</div>
                <div class="ama-stat-value">42 zł</div>
                <div class="ama-stat-trend is-bad">+38% vs branża</div>
              </div>
              <div class="ama-stat">
                <div class="ama-stat-label">CPL (30 dni)</div>
                <div class="ama-stat-value">186 zł</div>
                <div class="ama-stat-trend is-bad">3.2× za drogo</div>
              </div>
            </div>
            <div class="ama-dashboard-issue">
              <strong>Problem #1</strong>
              4 kampanie z tym samym celem konkurują o tę samą grupę odbiorców — Meta podbija stawki w Twojej własnej aukcji.
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="ama-section ama-section-soft">
    <div class="ama-container">
      <div class="ama-section-head">
        <span class="ama-section-eyebrow">Co sprawdzam</span>
        <h2>Pięć obszarów, w których Meta Ads najczęściej tracą pieniądze</h2>
        <p class="ama-section-intro">
          Meta to nie Google. Tu nie ma intencji wyszukania — jest przerwanie scroll'u. Audyt patrzy nie tylko na ustawienia, ale przede wszystkim na <strong>kreację</strong> i <strong>strukturę kampanii</strong> — bo to one decydują o kosztach.
        </p>
      </div>
      <div class="ama-areas">
        <div class="ama-area-big">
          <div class="ama-area-num">Obszar 01</div>
          <h3>Kreacja: zdjęcia, video, copy reklam</h3>
          <p>
            W Meta wygrywa kreacja, nie targetowanie. 80% kont które widzę ma poprawne kampanie, ale fatalne reklamy — nudne wizualizacje produktu na białym tle, generyczny tekst, brak hook'a w pierwszych 3 sekundach video. Sprawdzam wszystko klatka po klatce.
          </p>
          <ul class="ama-area-checks">
            <li>Hook w pierwszych 3 sekundach</li>
            <li>Wariant statyczny vs video</li>
            <li>Format 9:16 vs 1:1 vs 4:5</li>
            <li>UGC vs profesjonalna grafika</li>
            <li>Headline i opis pod CTA</li>
            <li>Ad fatigue — kiedy zmienić kreację</li>
          </ul>
        </div>
        <div class="ama-area-small">
          <div class="ama-area-num">Obszar 02</div>
          <h3>Struktura kampanii (CBO/ABO)</h3>
          <p>Czy podział kampanii i zestawów reklam pasuje do Twojego budżetu i celu. Klasyczny błąd: 10 kampanii zamiast 2, każda walczy o tę samą grupę.</p>
        </div>
        <div class="ama-area-small">
          <div class="ama-area-num">Obszar 03</div>
          <h3>Targetowanie i audiencje</h3>
          <p>Lookalike audiences, custom audiences, advantage+ vs manual. Sprawdzam czy Pixel ma wystarczająco danych, żeby targetowanie miało sens.</p>
        </div>
      </div>
      <div class="ama-areas" style="margin-top: 20px;">
        <div class="ama-area-small" style="grid-column: span 2;">
          <div class="ama-area-num">Obszar 04</div>
          <h3>Pixel, Conversions API i pomiar konwersji</h3>
          <p>Pixel to fundament Meta Ads. Bez prawidłowo podpiętego Pixela i Conversions API algorytm uczy się na wadliwych sygnałach, a koszty rosną z miesiąca na miesiąc. Sprawdzam dokładność, EMQ score, deduplikację zdarzeń.</p>
        </div>
        <div class="ama-area-small" style="grid-column: span 2;">
          <div class="ama-area-num">Obszar 05</div>
          <h3>Lejek: zimno → ciepło → retargeting</h3>
          <p>Większość firm robi tylko cold audience i dziwi się, że nie ma sprzedaży. Sprawdzam czy masz prawidłowy lejek: prospecting → engagement → retargeting → exclusion. Bez tego Meta Ads mają dwa razy większy CPL niż powinny.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="ama-section">
    <div class="ama-container">
      <div class="ama-section-head">
        <span class="ama-section-eyebrow">Szczerze</span>
        <h2>Czego NIE dostaniesz w tym audycie</h2>
        <p class="ama-section-intro">
          Branża reklamy w Meta jest pełna influencerów obiecujących "skalowanie do 100k miesięcznie". Mój audyt wygląda inaczej. Lepiej żebyś wiedział to przed spotkaniem.
        </p>
      </div>
      <div class="ama-honest">
        <div class="ama-honest-header">
          <div class="ama-honest-icon">!</div>
          <strong>W audycie nie znajdziesz:</strong>
        </div>
        <ul>
          <li>Obietnicy "ROAS 8× w 30 dni" — tak Meta nie działa</li>
          <li>Tajnej strategii skalowania o której nikt nie wie</li>
          <li>Kopii czyjegoś sukcesu — Twoja branża to nie ta sama bajka</li>
          <li>Wzorów na perfekcyjną reklamę z 9 zmiennymi</li>
          <li>Polecania kursu na 4 999 zł na końcu rozmowy</li>
          <li>Sztucznych komplementów żebyś nie poczuł się źle</li>
        </ul>
      </div>
    </div>
  </section>

  <section class="ama-section ama-section-soft" id="proces">
    <div class="ama-container">
      <div class="ama-section-head">
        <span class="ama-section-eyebrow">Jak to działa</span>
        <h2>Cztery kroki, dwa dni, konkretne kroki na 30 dni</h2>
        <p class="ama-section-intro">
          Audyt Meta wymaga dostępu do Business Managera (Read-Only) — bez tego analiza to zgadywanka. Cały proces od formularza do wniosków: 2–3 dni roboczych.
        </p>
      </div>
      <div class="ama-steps">
        <div class="ama-step">
          <div class="ama-step-num">01</div>
          <h4>Wypełniasz formularz</h4>
          <p>Imię, email, link do strony, miesięczny budżet w Meta. Bez NIP-u, bez briefu na 30 pytań.</p>
          <span class="ama-step-time">~30 sekund</span>
        </div>
        <div class="ama-step">
          <div class="ama-step-num">02</div>
          <h4>Dodajesz mnie do BM</h4>
          <p>Wysyłam Ci ID użytkownika Meta Business. Dodajesz mnie z prawami "Analityk" w Business Managerze — same odczyty, zero edycji.</p>
          <span class="ama-step-time">~3 minuty</span>
        </div>
        <div class="ama-step">
          <div class="ama-step-num">03</div>
          <h4>Analizuję 24h przed</h4>
          <p>Spędzam 60–90 minut z Twoim kontem. Patrzę na ostatnie 90 dni. Notuję obserwacje, oglądam wszystkie kreacje, analizuję Pixel.</p>
          <span class="ama-step-time">60–90 min pracy</span>
        </div>
        <div class="ama-step">
          <div class="ama-step-num">04</div>
          <h4>Rozmowa 30 minut</h4>
          <p>Pokazuję na żywo co znalazłem. Tłumaczę dlaczego to problem. Mówię co poprawić — sam, ze mną, czy z grafikiem do nowych kreacji.</p>
          <span class="ama-step-time">30 minut</span>
        </div>
      </div>
    </div>
  </section>

  <section class="ama-section">
    <div class="ama-container">
      <div class="ama-section-head">
        <span class="ama-section-eyebrow">Konkret zamiast obietnic</span>
        <h2>Tak wyglądał audyt sklepu e-commerce w Polsce</h2>
        <p class="ama-section-intro">
          Zanonimizowane dane. Sklep z biżuterią ręcznie robioną, budżet ~6 000 zł/mies w Meta. Klient miał wrażenie że Meta "przestało działać" — CPL urósł z 80 zł do 240 zł w 6 miesięcy. Po audycie wiadomo dlaczego.
        </p>
      </div>
      <div class="ama-example-wrap">
        <div class="ama-example-eyebrow">Pierwsze 90 minut analizy</div>
        <h3>Trzy problemy które od pół roku podbijały koszt leada o 200%</h3>
        <p class="ama-example-context">
          Klient wynajmował agencję reklamową od roku — dostawał miesięczne raporty pełne wykresów. Nikt jednak nie powiedział mu, że agencja nigdy nie zmieniła kreacji od początku współpracy.
        </p>
        <div class="ama-findings">
          <div class="ama-finding">
            <span class="ama-finding-tag">Ad fatigue</span>
            <h4>Te same kreacje od 11 miesięcy</h4>
            <p>Zestaw 6 grafik produktowych nie był zmieniany od początku kampanii. Częstotliwość wyświetleń 12.4× — czyli te same zdjęcia ten sam użytkownik widział 12 razy. CTR spadł z 1.8% do 0.4%.</p>
            <div class="ama-finding-cost"><span class="num">2 100</span><span class="unit">zł / mies</span></div>
          </div>
          <div class="ama-finding">
            <span class="ama-finding-tag">Lookalike na 1%</span>
            <h4>Audiencje pokrywają się w 70%</h4>
            <p>Trzy lookalike audiences z różnych źródeł (purchase, view content, lead) pokrywały się w 70% — Meta podbijała stawki w aukcji przeciwko sobie. Klasyczny audience overlap.</p>
            <div class="ama-finding-cost"><span class="num">880</span><span class="unit">zł / mies</span></div>
          </div>
          <div class="ama-finding">
            <span class="ama-finding-tag">Pixel rozmyty</span>
            <h4>Conversions API niezsynchronizowane</h4>
            <p>EMQ score 4.2 zamiast min. 8.0. Pixel wysyłał zdarzenia bez emaila i numeru telefonu. Algorytm dostawał szczątkowe dane konwersji — uczył się o 60% wolniej niż mógłby.</p>
            <div class="ama-finding-cost"><span class="num">800</span><span class="unit">zł / mies</span></div>
          </div>
        </div>
        <div class="ama-example-summary">
          <div class="ama-example-summary-text">
            <strong>Trzy zmiany wdrożone w 2 tygodnie:</strong> nowe kreacje (UGC + 3 video), przebudowa audiencji, naprawa Conversions API. Po 30 dniach CPL spadł z 240 zł do 92 zł — 2.6× tańszy lead.
          </div>
          <div class="ama-example-summary-amount">
            46 800 zł
            <small>rocznych strat do odzyskania</small>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="ama-section ama-section-soft">
    <div class="ama-container">
      <div class="ama-section-head">
        <span class="ama-section-eyebrow">Dla kogo</span>
        <h2>Dla kogo audyt Meta Ads ma sens</h2>
        <p class="ama-section-intro">
          Meta Ads świetnie sprawdza się w niektórych biznesach, w innych — nie. Wolę powiedzieć Ci to wprost przed audytem niż po.
        </p>
      </div>
      <div class="ama-target">
        <div class="ama-target-col is-yes">
          <h4>Audyt ma sens jeśli:</h4>
          <ul>
            <li>→ Wydajesz 1 500–25 000 zł/mies w Meta Ads</li>
            <li>→ Konto ma minimum 60 dni historii (dane do analizy)</li>
            <li>→ Sprzedajesz produkty fizyczne (e-commerce) lub usługi B2C</li>
            <li>→ CPL/CPA rośnie z miesiąca na miesiąc i nie wiesz dlaczego</li>
            <li>→ Pracujesz z agencją która nie zmienia kreacji od miesięcy</li>
            <li>→ Chcesz drugie zdanie zanim zwiększysz budżet</li>
          </ul>
        </div>
        <div class="ama-target-col is-no">
          <h4>Audyt NIE ma sensu jeśli:</h4>
          <ul>
            <li>→ Sprzedajesz usługi B2B konsultingowe (Meta to zła platforma)</li>
            <li>→ Nie masz produktów / usług które można pokazać wizualnie</li>
            <li>→ Konto działa krócej niż 30 dni (brak danych)</li>
            <li>→ Sprzedajesz produkty zakazane przez Meta (CBD, broń, hazard)</li>
            <li>→ Budżet poniżej 800 zł/mies (audyt nie zwróci się)</li>
            <li>→ Masz konto bez Pixela / Conversions API od miesięcy</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section class="ama-section">
    <div class="ama-container">
      <div class="ama-section-head">
        <span class="ama-section-eyebrow">Kto patrzy na Twoje konto</span>
        <h2>Po drugiej stronie ekranu</h2>
      </div>
      <div class="ama-author">
        <div class="ama-author-photo">SK</div>
        <div class="ama-author-content">
          <h3>Sebastian Kelm</h3>
          <p class="ama-author-role">Konsultant marketingu — Meta Ads · Google Ads · SEO · strony B2B</p>
          <p>
            Pracuję sam, bez agencji i podwykonawców, z maksymalnie 5–8 klientami jednocześnie. Ten audyt Meta Ads robię osobiście — nie deleguję go juniorowi, nie pokazuję raportów wygenerowanych automatycznie. Specjalizuję się w generowaniu leadów i sprzedaży przez płatne reklamy. Jeśli się dogadamy na współpracę, też pracuję z Tobą bezpośrednio.
          </p>
        </div>
      </div>
    </div>
  </section>

  <section class="ama-section ama-section-soft">
    <div class="ama-container">
      <div class="ama-section-head is-center">
        <span class="ama-section-eyebrow">FAQ</span>
        <h2>Pytania które dostaję najczęściej</h2>
      </div>
      <div class="ama-faq-list">
        <details class="ama-faq">
          <summary>Naprawdę bezpłatne? Jaki jest haczyk?</summary>
          <div class="ama-faq-content">
            <p>Nie ma haczyka. Robię to z dwóch powodów: (1) chcę pokazać jak pracuję — to lepsza wizytówka niż portfolio, (2) z 10 audytów 2–3 firmy pytają o stałą współpracę po wdrożeniu zmian.</p>
            <p>Nie sprzedaję na audycie — sprzedaję wyniki. Audyt to wyniki, których jeszcze nie masz.</p>
          </div>
        </details>
        <details class="ama-faq">
          <summary>Co dokładnie analizujesz w kreacji reklam?</summary>
          <div class="ama-faq-content">
            <p>Wszystkie aktywne kreacje plus archiwum z ostatnich 90 dni. Sprawdzam: hook (pierwsze 3 sekundy), proporcje (9:16, 1:1, 4:5), copy w nagłówku i opisie, CTA, częstotliwość wyświetleń (ad fatigue), CTR i CPM dla każdego wariantu.</p>
            <p>Nie kopiuję estetyki — patrzę co realnie konwertuje i co nie. Często najgorsze CTR mają najpiękniejsze reklamy.</p>
          </div>
        </details>
        <details class="ama-faq">
          <summary>Co jeśli moimi reklamami zajmuje się agencja?</summary>
          <div class="ama-faq-content">
            <p>Wtedy audyt to drugi pogląd. Powiem Ci czy to, co robi agencja, ma sens, czy lecisz na autopilocie z miesięcznymi raportami pełnymi wykresów, ale bez wniosków.</p>
            <p>Nie pochwalę dla pochwały i nie skrytykuję żebyś zmienił agencję. Patrzę na konfigurację, nie na umowy. Jeśli agencja robi dobrą robotę — powiem Ci to wprost.</p>
          </div>
        </details>
        <details class="ama-faq">
          <summary>Jaki dostęp do Business Managera musisz mieć?</summary>
          <div class="ama-faq-content">
            <p>Tylko rola <strong>"Analityk"</strong> w Business Managerze — same odczyty, zero zmian. Wysyłam Ci moje ID Meta Business, dodajesz mnie w Ustawienia → Osoby → Dodaj. Po audycie usuwasz mnie tym samym kliknięciem.</p>
            <p>Bezpieczne dla Ciebie i dla mnie — nie chcę odpowiadać za zmiany, których nie autoryzowałeś.</p>
          </div>
        </details>
        <details class="ama-faq">
          <summary>Mam mały budżet (1 500 zł/mies). Audyt też ma sens?</summary>
          <div class="ama-faq-content">
            <p>Tak, ale szczerze — w małych budżetach Meta jest trudniejsza niż w dużych. Mniej danych = wolniejsze uczenie algorytmu = wyższe koszty na początku. Audyt pokaże Ci czy Twój budżet w ogóle pozwala wyciągnąć sensowne wnioski.</p>
            <p>Czasem moja rekomendacja brzmi "nie skalujcie Meta — zacznijcie od organicznego Instagrama i dopiero później reklamy". Tego nie powie Ci agencja, której zależy na utrzymaniu Cię jako klienta.</p>
          </div>
        </details>
        <details class="ama-faq">
          <summary>Co dostaję na piśmie po audycie?</summary>
          <div class="ama-faq-content">
            <p>1–2 strony A4 z trzema głównymi problemami, kosztami które generują (w zł/mies), konkretnymi krokami do wdrożenia w kolejności priorytetu. Wysyłam mailem 24h po rozmowie.</p>
            <p>Bez 50-stronicowego raportu, bez tabel Excel. Tekst do przeczytania w 5 minut, decyzja na podstawie konkretu.</p>
          </div>
        </details>
        <details class="ama-faq">
          <summary>Czy zaproponujesz konkretnych grafików / wykonawców kreacji?</summary>
          <div class="ama-faq-content">
            <p>Jeśli okaże się że problemem są kreacje (a często jest) — mogę polecić sprawdzonych grafików / freelancerów którzy specjalizują się w UGC dla Meta. Bez prowizji od ich strony, po prostu lista osób z którymi pracowałem i które robią dobrą robotę.</p>
            <p>Jeśli wolisz znaleźć kogoś sam — daję wytyczne czego szukać i jakich pytań zadać.</p>
          </div>
        </details>
        <details class="ama-faq">
          <summary>Jak długo czeka się na termin?</summary>
          <div class="ama-faq-content">
            <p>Zwykle 3–7 dni od wypełnienia formularza. Robię max 5 audytów tygodniowo, żeby utrzymać jakość. Audyt to nie kserówka — to 60–90 min realnej analizy plus rozmowa.</p>
            <p>Jeśli wszystkie sloty są zajęte, dostajesz mail z najwcześniejszym wolnym terminem.</p>
          </div>
        </details>
      </div>
    </div>
  </section>

  <section class="ama-final" id="kontakt">
    <div class="ama-container ama-final-inner">
      <h2>Umów bezpłatny audyt Meta Ads</h2>
      <p class="ama-final-sub">
        Wypełnij formularz, odpowiem w ciągu 24h z propozycją terminów. Bez handlowych telefonów, bez wciskania pakietów reklamowych — sam wiesz że czasem ktoś po prostu robi dobrą robotę.
      </p>

      <form
        class="ama-form"
        id="audit-meta-form"
        method="post"
        action="<?php echo esc_url(admin_url("admin-post.php")); ?>"
        data-audit-form="meta-ads"
        data-upsellio-lead-form="1"
        data-upsellio-server-form="1"
      >
        <input type="hidden" name="action" value="upsellio_submit_lead">
        <input type="hidden" name="redirect_url" value="<?php echo esc_url((get_permalink() ?: home_url("/audyt-meta-ads/")) . "#kontakt"); ?>">
        <input type="hidden" name="lead_form_origin" value="audit-form">
        <input type="hidden" name="lead_source" value="audit-form">
        <input type="hidden" name="lead_service" value="Audyt Meta Ads">
        <input type="hidden" name="lead_goal" value="Audyt konta Meta Ads i plan naprawczy 30 dni">
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

        <div class="ama-form-grid">
          <div class="ama-field">
            <label for="audit_meta_name">Imię</label>
            <input type="text" id="audit_meta_name" name="lead_name" placeholder="Jan" autocomplete="given-name" required>
          </div>
          <div class="ama-field">
            <label for="audit_meta_email">Email</label>
            <input type="email" id="audit_meta_email" name="lead_email" placeholder="jan@firma.pl" autocomplete="email" required>
          </div>
          <div class="ama-field ama-field-full">
            <label for="audit_meta_company">Firma / strona www</label>
            <input type="text" id="audit_meta_company" name="lead_company" placeholder="firma.pl lub @instagram_handle" autocomplete="organization" required>
          </div>
          <div class="ama-field ama-field-full">
            <label for="audit_meta_budget">Miesięczny budżet w Meta Ads <span class="opt">orientacyjnie</span></label>
            <input type="text" id="audit_meta_budget" name="lead_budget" placeholder="np. 3 000 zł / mies">
          </div>
          <div class="ama-field ama-field-full">
            <label for="audit_meta_message">Co Cię najbardziej trapi? <span class="opt">opcjonalnie, ale pomaga</span></label>
            <textarea id="audit_meta_message" name="lead_message" placeholder="np. CPL rośnie z miesiąca na miesiąc i nie wiem dlaczego. Albo: agencja prowadzi konto pół roku, ale wyniki dalej takie same." required></textarea>
          </div>
        </div>

        <label class="ama-consent">
          <input type="checkbox" name="lead_consent" value="1" required>
          <span>Wyrażam zgodę na kontakt w sprawie audytu i akceptuję <a href="<?php echo esc_url(home_url("/polityka-prywatnosci/")); ?>" target="_blank" rel="noopener noreferrer">politykę prywatności</a>.</span>
        </label>

        <button type="submit" class="ama-form-submit" data-cta="audyt-meta-final-submit" data-cta-section="final-form" data-cta-position="submit">Umów bezpłatny audyt Meta Ads →</button>

        <p class="ama-form-meta">
          Twoje dane służą wyłącznie do umówienia audytu. Nie zapisuję Cię na newsletter,<br>nie sprzedaję bazy. Po audycie kasuję dane jeśli nie nawiążemy współpracy.
        </p>
      </form>
    </div>
  </section>
</div>

<?php get_footer(); ?>
