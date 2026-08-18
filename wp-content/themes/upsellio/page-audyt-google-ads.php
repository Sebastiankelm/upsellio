<?php
/**
 * Template Name: Audyt Google Ads — landing
 *
 * Landing page sprzedażowy dla bezpłatnego audytu Google Ads.
 * Cel: konwersja na formularz zapisu (high-intent search traffic).
 *
 * Filozofia copy: szczerość zamiast pewności siebie. Konkrety zamiast obietnic.
 * Postura kogoś kto wie ale nie sprzedaje agresywnie.
 */
get_header();
?>

<style>
  :root {
    --aga-radius: 20px;
    --aga-radius-lg: 28px;
  }

  .aga-page {
    background: var(--bg, #fafaf6);
    color: var(--text, #0d0d0b);
    font-family: var(--font-body, "DM Sans"), system-ui, sans-serif;
    overflow-x: hidden;
  }
  .aga-page * { box-sizing: border-box; }
  .aga-container { max-width: 1100px; margin: 0 auto; padding: 0 24px; }

  .aga-hero {
    position: relative;
    padding: clamp(60px, 10vw, 100px) 0 clamp(50px, 8vw, 80px);
    overflow: hidden;
  }
  .aga-hero::before {
    content: "";
    position: absolute; inset: 0;
    background:
      radial-gradient(ellipse 800px 400px at 70% 30%, rgba(13, 148, 136, 0.08), transparent),
      radial-gradient(ellipse 600px 300px at 20% 70%, rgba(249, 115, 22, 0.04), transparent);
    pointer-events: none;
    z-index: 0;
  }
  .aga-hero-inner { position: relative; z-index: 1; }

  .aga-hero-grid {
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    gap: 48px;
    align-items: center;
  }
  @media (max-width: 900px) {
    .aga-hero-grid { grid-template-columns: 1fr; gap: 40px; }
  }

  .aga-pill {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--brand-soft, #ccfbf1);
    color: var(--brand-dark, #0f766e);
    padding: 8px 16px;
    border-radius: 999px;
    font-size: 12px; font-weight: 700;
    letter-spacing: 0.4px; text-transform: uppercase;
    margin-bottom: 24px;
  }
  .aga-pill::before {
    content: "";
    width: 6px; height: 6px;
    background: var(--brand, #0d9488);
    border-radius: 50%;
    animation: agaPulse 2s ease-in-out infinite;
  }
  @keyframes agaPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(0.85); }
  }

  .aga-hero h1 {
    font-family: var(--font-display, "Bricolage Grotesque"), serif;
    font-size: clamp(36px, 5.5vw, 60px);
    font-weight: 800;
    line-height: 1.04;
    letter-spacing: -0.025em;
    margin: 0 0 24px;
    color: var(--text);
  }
  .aga-hero h1 em {
    font-style: normal;
    background: linear-gradient(120deg, var(--brand) 0%, var(--brand-dark) 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    color: transparent;
  }

  .aga-hero-sub {
    font-size: clamp(16px, 2vw, 19px);
    line-height: 1.6;
    color: var(--text-soft, #7a7a72);
    margin: 0 0 32px;
    max-width: 540px;
  }

  .aga-hero-cta-row {
    display: flex; gap: 16px; flex-wrap: wrap;
    margin-bottom: 32px;
  }
  .aga-cta-primary {
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
  .aga-cta-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(13, 13, 11, 0.2);
    color: #fff;
  }
  .aga-cta-primary::after {
    content: "→";
    transition: transform 0.2s ease;
  }
  .aga-cta-primary:hover::after { transform: translateX(4px); }

  .aga-cta-secondary {
    display: inline-flex; align-items: center; gap: 8px;
    color: var(--text);
    padding: 18px 24px;
    font-size: 15px; font-weight: 600;
    text-decoration: none;
    border-bottom: 2px solid transparent;
    transition: all 0.15s ease;
  }
  .aga-cta-secondary:hover { border-bottom-color: var(--brand); color: var(--brand); }

  .aga-trust-row {
    display: flex; gap: 24px; flex-wrap: wrap;
    color: var(--text-soft, #7a7a72);
    font-size: 14px;
  }
  .aga-trust-item {
    display: inline-flex; align-items: center; gap: 8px;
  }
  .aga-trust-item::before {
    content: "✓";
    color: var(--brand);
    font-weight: 800;
    font-size: 16px;
  }

  .aga-preview {
    background: #fff;
    border: 1px solid var(--border, #e8e8e0);
    border-radius: var(--aga-radius);
    padding: 28px;
    box-shadow: var(--shadow, 0 22px 60px rgba(15, 23, 42, 0.1));
    position: relative;
    transform: rotate(0.5deg);
  }
  .aga-preview::before {
    content: "";
    position: absolute;
    top: -12px; right: 24px;
    background: var(--accent, #f97316);
    color: #fff;
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.5px; text-transform: uppercase;
    content: "Przykład znaleziska";
  }
  .aga-preview-header {
    display: flex; gap: 6px; margin-bottom: 20px;
  }
  .aga-preview-dot {
    width: 10px; height: 10px; border-radius: 50%;
    background: var(--border-strong, #c8c2b5);
  }
  .aga-preview-dot:nth-child(1) { background: #ef4444; }
  .aga-preview-dot:nth-child(2) { background: #f59e0b; }
  .aga-preview-dot:nth-child(3) { background: #15803d; }

  .aga-preview-finding {
    padding: 16px 0;
    border-bottom: 1px solid var(--border);
  }
  .aga-preview-finding:last-child { border-bottom: 0; }
  .aga-preview-finding-label {
    font-size: 11px; font-weight: 700;
    color: var(--text-soft); text-transform: uppercase; letter-spacing: 0.5px;
    margin-bottom: 6px;
  }
  .aga-preview-finding-text {
    font-size: 14px; line-height: 1.5; color: var(--text);
    margin-bottom: 8px;
  }
  .aga-preview-cost {
    display: inline-block;
    background: #fef2f2;
    color: #b91c1c;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12px; font-weight: 700;
    font-variant-numeric: tabular-nums;
  }

  .aga-section {
    padding: clamp(60px, 8vw, 100px) 0;
    position: relative;
  }
  .aga-section-soft { background: var(--surface, #fff); }

  .aga-section-head {
    max-width: 720px;
    margin: 0 0 56px;
  }
  .aga-section-head.is-center {
    margin: 0 auto 56px;
    text-align: center;
  }
  .aga-section-eyebrow {
    font-size: 12px; font-weight: 700;
    color: var(--brand-dark);
    letter-spacing: 0.8px;
    text-transform: uppercase;
    margin-bottom: 12px;
    display: inline-block;
  }
  .aga-section h2 {
    font-family: var(--font-display, "Bricolage Grotesque"), serif;
    font-size: clamp(28px, 3.8vw, 42px);
    font-weight: 800;
    line-height: 1.15;
    letter-spacing: -0.018em;
    margin: 0 0 16px;
    color: var(--text);
  }
  .aga-section-intro {
    font-size: clamp(16px, 1.6vw, 18px);
    line-height: 1.65;
    color: var(--text-soft, #7a7a72);
    margin: 0;
  }

  .aga-grid-2 {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
  }
  @media (max-width: 700px) { .aga-grid-2 { grid-template-columns: 1fr; } }

  .aga-card {
    background: var(--surface, #fff);
    border: 1px solid var(--border, #e8e8e0);
    border-radius: var(--aga-radius);
    padding: 28px;
    transition: all 0.25s var(--ease-out);
    position: relative;
    overflow: hidden;
  }
  .aga-card::before {
    content: "";
    position: absolute;
    top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, var(--brand), var(--brand-dark));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.3s var(--ease-out);
  }
  .aga-card:hover {
    border-color: var(--brand);
    transform: translateY(-4px);
    box-shadow: var(--shadow-soft);
  }
  .aga-card:hover::before { transform: scaleX(1); }
  .aga-card-num {
    display: inline-flex; align-items: center; justify-content: center;
    width: 36px; height: 36px;
    border-radius: 10px;
    background: var(--brand-soft, #ccfbf1);
    color: var(--brand-dark);
    font-weight: 800;
    font-size: 14px;
    font-variant-numeric: tabular-nums;
    margin-bottom: 16px;
  }
  .aga-card h3 {
    font-family: var(--font-display, "Bricolage Grotesque");
    font-size: 18px; font-weight: 700;
    line-height: 1.3;
    margin: 0 0 10px;
  }
  .aga-card p {
    font-size: 14px; line-height: 1.65;
    color: var(--text-soft);
    margin: 0;
  }

  .aga-honest {
    background: linear-gradient(135deg, var(--accent-soft, #fff7ed) 0%, #fff 100%);
    border: 1px solid var(--accent-line, #fed7aa);
    border-radius: var(--aga-radius);
    padding: clamp(28px, 4vw, 44px);
    margin-top: 32px;
  }
  .aga-honest-header {
    display: flex; gap: 16px; align-items: center;
    margin-bottom: 20px;
  }
  .aga-honest-icon {
    flex-shrink: 0;
    width: 44px; height: 44px;
    border-radius: 12px;
    background: var(--accent, #f97316);
    color: #fff;
    display: grid; place-items: center;
    font-size: 22px;
  }
  .aga-honest-header strong {
    font-family: var(--font-display);
    font-size: 18px; font-weight: 700;
  }
  .aga-honest ul {
    margin: 0; padding: 0;
    list-style: none;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px 24px;
  }
  @media (max-width: 600px) { .aga-honest ul { grid-template-columns: 1fr; } }
  .aga-honest li {
    padding: 10px 0 10px 28px;
    position: relative;
    font-size: 15px;
    line-height: 1.5;
    color: var(--text-muted, #3a3a35);
  }
  .aga-honest li::before {
    content: "✗";
    position: absolute; left: 0; top: 8px;
    color: var(--accent);
    font-weight: 800;
    font-size: 18px;
  }

  .aga-steps {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-top: 16px;
    position: relative;
  }
  @media (max-width: 800px) {
    .aga-steps { grid-template-columns: repeat(2, 1fr); }
  }
  @media (max-width: 500px) {
    .aga-steps { grid-template-columns: 1fr; }
  }

  .aga-step {
    background: var(--surface, #fff);
    border: 1px solid var(--border);
    border-radius: var(--aga-radius);
    padding: 24px;
    position: relative;
    transition: all 0.25s var(--ease-out);
  }
  .aga-step:hover {
    transform: translateY(-3px);
    border-color: var(--brand);
    box-shadow: var(--shadow-soft);
  }

  .aga-step-num {
    font-family: var(--font-display);
    font-size: 32px;
    font-weight: 800;
    line-height: 1;
    color: var(--brand);
    margin-bottom: 16px;
    font-variant-numeric: tabular-nums;
  }
  .aga-step h4 {
    font-family: var(--font-display);
    font-size: 16px; font-weight: 700;
    line-height: 1.3;
    margin: 0 0 8px;
  }
  .aga-step p {
    font-size: 13px; line-height: 1.6;
    color: var(--text-soft);
    margin: 0;
  }
  .aga-step-time {
    display: inline-block;
    margin-top: 12px;
    font-size: 11px; font-weight: 700;
    color: var(--text-soft);
    letter-spacing: 0.5px; text-transform: uppercase;
    padding: 4px 8px;
    background: var(--bg-alt, #f2f2ec);
    border-radius: 6px;
  }

  .aga-example-wrap {
    background: linear-gradient(135deg, var(--section-dark, #0b1320) 0%, var(--section-dark-2, #111c2e) 100%);
    color: #fff;
    border-radius: var(--aga-radius-lg);
    padding: clamp(36px, 5vw, 56px);
    margin-top: 16px;
    position: relative;
    overflow: hidden;
  }
  .aga-example-wrap::before {
    content: "";
    position: absolute;
    top: -100px; right: -100px;
    width: 400px; height: 400px;
    background: radial-gradient(circle, var(--brand-glow, rgba(13, 148, 136, 0.18)) 0%, transparent 60%);
    pointer-events: none;
  }
  .aga-example-wrap > * { position: relative; }

  .aga-example-eyebrow {
    font-size: 11px; font-weight: 700;
    color: var(--brand-soft, #ccfbf1);
    letter-spacing: 0.8px; text-transform: uppercase;
    margin-bottom: 14px;
  }
  .aga-example-wrap h3 {
    font-family: var(--font-display);
    font-size: clamp(22px, 2.5vw, 30px);
    font-weight: 800;
    line-height: 1.25;
    margin: 0 0 12px;
    color: #fff;
  }
  .aga-example-context {
    color: rgba(255, 255, 255, 0.7);
    font-size: 15px;
    line-height: 1.6;
    margin: 0 0 32px;
    max-width: 600px;
  }

  .aga-findings {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 32px;
  }
  @media (max-width: 800px) {
    .aga-findings { grid-template-columns: 1fr; }
  }

  .aga-finding {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border-radius: 16px;
    padding: 24px;
  }
  .aga-finding-tag {
    display: inline-block;
    font-size: 10px; font-weight: 700;
    letter-spacing: 0.6px; text-transform: uppercase;
    background: rgba(245, 158, 11, 0.2);
    color: #fbbf24;
    padding: 4px 8px;
    border-radius: 6px;
    margin-bottom: 12px;
  }
  .aga-finding h4 {
    font-family: var(--font-display);
    font-size: 16px; font-weight: 700;
    line-height: 1.35;
    margin: 0 0 10px;
    color: #fff;
  }
  .aga-finding p {
    font-size: 13px; line-height: 1.6;
    color: rgba(255, 255, 255, 0.75);
    margin: 0 0 16px;
  }
  .aga-finding-cost {
    display: inline-flex; align-items: baseline; gap: 4px;
    font-family: var(--font-display);
    color: #fbbf24;
    font-weight: 800;
  }
  .aga-finding-cost .num {
    font-size: 24px;
    font-variant-numeric: tabular-nums;
  }
  .aga-finding-cost .unit {
    font-size: 12px;
    opacity: 0.85;
  }

  .aga-example-summary {
    border-top: 1px solid rgba(255, 255, 255, 0.12);
    padding-top: 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
  }
  .aga-example-summary-text {
    color: rgba(255, 255, 255, 0.85);
    font-size: 15px;
    line-height: 1.5;
    max-width: 500px;
  }
  .aga-example-summary-amount {
    font-family: var(--font-display);
    font-size: clamp(28px, 3.5vw, 38px);
    font-weight: 800;
    color: #fff;
  }
  .aga-example-summary-amount small {
    display: block;
    font-size: 12px; font-weight: 600;
    color: rgba(255, 255, 255, 0.6);
    letter-spacing: 0.4px; text-transform: uppercase;
    margin-top: 4px;
  }

  .aga-target {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
  }
  @media (max-width: 700px) { .aga-target { grid-template-columns: 1fr; } }

  .aga-target-col {
    padding: 28px;
    border-radius: var(--aga-radius);
    border: 1px solid var(--border);
  }
  .aga-target-col.is-yes {
    background: var(--success-soft, #f0faf4);
    border-color: rgba(21, 128, 61, 0.2);
  }
  .aga-target-col.is-no {
    background: var(--bg-alt, #f2f2ec);
  }
  .aga-target-col h4 {
    font-family: var(--font-display);
    font-size: 17px; font-weight: 700;
    margin: 0 0 16px;
    display: flex; align-items: center; gap: 8px;
  }
  .aga-target-col h4::before {
    width: 24px; height: 24px;
    border-radius: 50%;
    display: grid; place-items: center;
    font-size: 14px; font-weight: 800;
    flex-shrink: 0;
  }
  .aga-target-col.is-yes h4::before {
    content: "✓";
    background: var(--success, #15803d);
    color: #fff;
  }
  .aga-target-col.is-no h4::before {
    content: "—";
    background: var(--text-soft);
    color: #fff;
  }
  .aga-target-col ul {
    margin: 0; padding: 0; list-style: none;
  }
  .aga-target-col li {
    padding: 8px 0;
    font-size: 14px; line-height: 1.55;
    color: var(--text-muted);
  }

  .aga-faq-list { max-width: 760px; margin: 0 auto; }
  .aga-faq {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--aga-radius);
    margin-bottom: 12px;
    overflow: hidden;
    transition: border-color 0.2s ease;
  }
  .aga-faq:hover { border-color: var(--brand); }
  .aga-faq[open] {
    border-color: var(--brand);
    box-shadow: var(--shadow-sm);
  }
  .aga-faq summary {
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
  .aga-faq summary::-webkit-details-marker { display: none; }
  .aga-faq summary::after {
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
  .aga-faq[open] summary::after {
    content: "−";
    background: var(--brand);
    color: #fff;
    transform: rotate(180deg);
  }
  .aga-faq-content {
    padding: 0 28px 24px;
    color: var(--text-soft);
    font-size: 15px;
    line-height: 1.7;
  }
  .aga-faq-content p { margin: 0 0 12px; }
  .aga-faq-content p:last-child { margin: 0; }

  .aga-author {
    background: var(--sand, #f5f0e8);
    border-radius: var(--aga-radius-lg);
    padding: clamp(36px, 5vw, 56px);
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 32px;
    align-items: center;
    margin-top: 16px;
  }
  @media (max-width: 700px) {
    .aga-author { grid-template-columns: 1fr; text-align: center; }
  }
  .aga-author-photo {
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
  .aga-author-content h3 {
    font-family: var(--font-display);
    font-size: 22px; font-weight: 800;
    margin: 0 0 8px;
  }
  .aga-author-role {
    font-size: 14px; font-weight: 600;
    color: var(--brand-dark);
    margin: 0 0 16px;
  }
  .aga-author-content p {
    font-size: 15px; line-height: 1.65;
    color: var(--text-muted);
    margin: 0;
  }

  .aga-final {
    background: linear-gradient(180deg, var(--text, #0d0d0b) 0%, var(--dark-2, #1e293b) 100%);
    color: #fff;
    padding: clamp(60px, 9vw, 96px) 0;
    position: relative;
    overflow: hidden;
  }
  .aga-final::before {
    content: "";
    position: absolute;
    top: -100px; left: 50%;
    transform: translateX(-50%);
    width: 600px; height: 600px;
    background: radial-gradient(circle, var(--brand-glow) 0%, transparent 70%);
    pointer-events: none;
  }
  .aga-final-inner {
    position: relative;
    max-width: 640px;
    margin: 0 auto;
    text-align: center;
  }
  .aga-final h2 {
    color: #fff;
    margin-bottom: 16px;
  }
  .aga-final-sub {
    font-size: 17px;
    line-height: 1.6;
    color: rgba(255, 255, 255, 0.7);
    margin: 0 0 40px;
  }

  .aga-form {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-radius: var(--aga-radius);
    padding: clamp(24px, 4vw, 36px);
    text-align: left;
  }
  .aga-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
  }
  @media (max-width: 600px) { .aga-form-grid { grid-template-columns: 1fr; } }
  .aga-field { margin-bottom: 16px; }
  .aga-field-full { grid-column: 1 / -1; }
  .aga-field label {
    display: block;
    font-size: 13px; font-weight: 600;
    color: rgba(255, 255, 255, 0.85);
    margin-bottom: 8px;
  }
  .aga-field label .opt {
    font-weight: 400;
    color: rgba(255, 255, 255, 0.4);
    font-size: 12px;
    margin-left: 6px;
  }
  .aga-field input,
  .aga-field textarea {
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
  .aga-field input::placeholder,
  .aga-field textarea::placeholder { color: rgba(255, 255, 255, 0.35); }
  .aga-field input:focus,
  .aga-field textarea:focus {
    outline: none;
    border-color: var(--brand);
    background: rgba(255, 255, 255, 0.1);
  }
  .aga-field textarea { min-height: 90px; resize: vertical; }

  .aga-form-submit {
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
  .aga-form-submit:hover {
    background: var(--brand-dark);
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(13, 148, 136, 0.3);
  }

  .aga-form-meta {
    text-align: center;
    margin-top: 20px;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.4);
    line-height: 1.5;
  }
  .aga-consent {
    margin: 4px 0 0;
    display: flex;
    gap: 10px;
    align-items: flex-start;
    color: rgba(255, 255, 255, 0.78);
    font-size: 13px;
    line-height: 1.55;
  }
  .aga-consent input[type="checkbox"] {
    margin-top: 3px;
    width: 16px;
    height: 16px;
  }
  .aga-consent a {
    color: #99f6e4;
  }
  .aga-form .form-feedback {
    margin: 0 0 12px;
    padding: 10px 12px;
    border-radius: 10px;
    font-size: 13px;
    border: 1px solid rgba(255, 255, 255, 0.18);
    display: none;
  }
  .aga-form .form-feedback.is-success {
    display: block;
    border-color: #34d399;
    background: rgba(16, 185, 129, 0.14);
    color: #d1fae5;
  }
  .aga-form .form-feedback.is-error {
    display: block;
    border-color: #fca5a5;
    background: rgba(239, 68, 68, 0.12);
    color: #fee2e2;
  }
</style>

<div class="aga-page">
  <section class="aga-hero">
    <div class="aga-container aga-hero-inner">
      <div class="aga-hero-grid">
        <div>
          <div class="aga-pill">Bezpłatny audyt — limit 5 osób na tydzień</div>

          <h1>Pokażę Ci konkretnie,<br>co zjada Twój budżet w <em>Google Ads</em></h1>

          <p class="aga-hero-sub">
            30 minut. Patrzymy razem na Twoje konto, wskazuję 3 najważniejsze błędy i mówię wprost — które naprawisz sam, a które warto oddać. Bez handlowego nawijania, bez 50-stronicowych raportów.
          </p>

          <div class="aga-hero-cta-row">
            <a href="#kontakt" class="aga-cta-primary" data-cta="audyt-hero-primary" data-cta-section="hero" data-cta-position="primary">Umów audyt</a>
            <a href="#proces" class="aga-cta-secondary" data-cta="audyt-hero-secondary-proces" data-cta-section="hero" data-cta-position="secondary">Jak to wygląda?</a>
          </div>

          <div class="aga-trust-row">
            <span class="aga-trust-item">30 minut</span>
            <span class="aga-trust-item">Online (Google Meet)</span>
            <span class="aga-trust-item">100% bezpłatnie</span>
          </div>
        </div>

        <div>
          <div class="aga-preview" aria-hidden="true">
            <div class="aga-preview-header">
              <span class="aga-preview-dot"></span>
              <span class="aga-preview-dot"></span>
              <span class="aga-preview-dot"></span>
            </div>

            <div class="aga-preview-finding">
              <div class="aga-preview-finding-label">Znalezisko #1</div>
              <div class="aga-preview-finding-text">Brak negatywnych słów kluczowych — reklamy wyświetlają się na frazy "darmowy", "kariera", "praca"</div>
              <span class="aga-preview-cost">~620 zł/mies straty</span>
            </div>

            <div class="aga-preview-finding">
              <div class="aga-preview-finding-label">Znalezisko #2</div>
              <div class="aga-preview-finding-text">Maximize Conversions bez wystarczających danych — algorytm uczy się na śmieciach od 6 miesięcy</div>
              <span class="aga-preview-cost">~700 zł/mies straty</span>
            </div>

            <div class="aga-preview-finding">
              <div class="aga-preview-finding-label">Znalezisko #3</div>
              <div class="aga-preview-finding-text">Performance Max kanibalizuje brand — płacisz Google za swoje organiczne wejścia</div>
              <span class="aga-preview-cost">~480 zł/mies straty</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="aga-section aga-section-soft">
    <div class="aga-container">
      <div class="aga-section-head">
        <span class="aga-section-eyebrow">Co dostajesz</span>
        <h2>Sześć obszarów, które przepuszczam przez sito</h2>
        <p class="aga-section-intro">
          Wracasz z audytu z konkretną listą priorytetów na 30 dni — nie z ogólnikami i nie z PDF-em do przeczytania. Wiesz dokładnie co i w jakiej kolejności poprawić.
        </p>
      </div>

      <div class="aga-grid-2">
        <div class="aga-card">
          <span class="aga-card-num">01</span>
          <h3>Struktura kampanii i grup reklam</h3>
          <p>80% kont które widzę ma chaos w strukturze. Sprawdzam czy podział kampanii pasuje do Twoich celów biznesowych — często można odzyskać 30% budżetu samym uporządkowaniem.</p>
        </div>

        <div class="aga-card">
          <span class="aga-card-num">02</span>
          <h3>Słowa kluczowe i wykluczające</h3>
          <p>Patrzę które frazy realnie generują kliknięcia, a które tylko spalają budżet. Brak negatywnych słów to klasyczny błąd kosztujący 1000–3000 zł miesięcznie.</p>
        </div>

        <div class="aga-card">
          <span class="aga-card-num">03</span>
          <h3>Ustawienia konwersji i pomiar</h3>
          <p>Sprawdzam czy konwersje są dobrze podpięte i czy Google uczy się na właściwych sygnałach. Źle ustawione konwersje = algorytm optymalizuje pod losowych ludzi.</p>
        </div>

        <div class="aga-card">
          <span class="aga-card-num">04</span>
          <h3>Strategia ofertowania (bidding)</h3>
          <p>Czy strategia stawek pasuje do Twojego budżetu i celu? Maximize Clicks na małym budżecie potrafi spalić cały miesiąc w 3 dni — pokazuję jak tego uniknąć.</p>
        </div>

        <div class="aga-card">
          <span class="aga-card-num">05</span>
          <h3>Strona docelowa (landing page)</h3>
          <p>Najpiękniejsza kampania nie pomoże, jeśli landing page jest słaby. Sprawdzam czy strona, na którą kierujesz ruch, w ogóle konwertuje — i co z tym zrobić.</p>
        </div>

        <div class="aga-card">
          <span class="aga-card-num">06</span>
          <h3>Plan na najbliższe 30 dni</h3>
          <p>Wychodzisz z audytu z 3–5 priorytetami do wdrożenia po kolei, od najważniejszego. Lista zadań, nie 50-stronicowy raport.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="aga-section">
    <div class="aga-container">
      <div class="aga-section-head">
        <span class="aga-section-eyebrow">Szczerze</span>
        <h2>Czego NIE dostaniesz (i dlatego dobrze)</h2>
        <p class="aga-section-intro">
          Wolę powiedzieć wprost, czego się spodziewać, niż obiecywać rzeczy które brzmią ładnie, ale nie mają sensu. Tego nie znajdziesz w moim audycie:
        </p>
      </div>

      <div class="aga-honest">
        <div class="aga-honest-header">
          <div class="aga-honest-icon">!</div>
          <strong>Tego w audycie nie ma:</strong>
        </div>
        <ul>
          <li>50-stronicowego raportu PDF, którego nikt nie czyta</li>
          <li>Ogólników typu „warto zoptymalizować kampanie"</li>
          <li>Sztucznych komplementów żeby Cię nie spłoszyć</li>
          <li>Próby sprzedaży 12-miesięcznej umowy w pierwszych 5 minutach</li>
          <li>Magicznych obietnic „zwiększę CTR o 200%"</li>
          <li>Korpo-żargonu, slajdów, białych etykiet</li>
        </ul>
      </div>
    </div>
  </section>

  <section class="aga-section aga-section-soft" id="proces">
    <div class="aga-container">
      <div class="aga-section-head">
        <span class="aga-section-eyebrow">Jak to działa</span>
        <h2>Cztery kroki, dwa dni, zero gimnastyki</h2>
        <p class="aga-section-intro">
          Cały proces od pierwszego kontaktu do konkretnych wniosków zajmuje 2–3 dni roboczych. Bez podpisywania NDA, bez instalacji oprogramowania.
        </p>
      </div>

      <div class="aga-steps">
        <div class="aga-step">
          <div class="aga-step-num">01</div>
          <h4>Wypełniasz formularz</h4>
          <p>Imię, email, link do strony, miesięczny budżet. Bez zbędnych pytań o NIP, obroty czy historię firmy.</p>
          <span class="aga-step-time">~30 sekund</span>
        </div>

        <div class="aga-step">
          <div class="aga-step-num">02</div>
          <h4>Dodajesz mnie do konta</h4>
          <p>Wysyłam Ci ID klienta Google Ads. Dodajesz mnie z prawami „tylko do odczytu" — nic nie mogę zmienić ani usunąć.</p>
          <span class="aga-step-time">~2 minuty</span>
        </div>

        <div class="aga-step">
          <div class="aga-step-num">03</div>
          <h4>Analizuję 24h przed rozmową</h4>
          <p>Spędzam 60–90 minut z Twoim kontem przed naszą rozmową. Zapisuję wszystkie obserwacje. Wybieram 3 najważniejsze.</p>
          <span class="aga-step-time">60–90 min pracy</span>
        </div>

        <div class="aga-step">
          <div class="aga-step-num">04</div>
          <h4>Rozmowa i konkretne kroki</h4>
          <p>Pokazuję na żywo co znalazłem. Tłumaczę dlaczego to problem. Mówię co poprawić — sam albo ze mną.</p>
          <span class="aga-step-time">30 minut</span>
        </div>
      </div>
    </div>
  </section>

  <section class="aga-section">
    <div class="aga-container">
      <div class="aga-section-head">
        <span class="aga-section-eyebrow">Konkret zamiast obietnic</span>
        <h2>Tak wygląda audyt który robiłem ostatnio</h2>
        <p class="aga-section-intro">
          Zanonimizowane dane z prawdziwego konta klienta. Firma usługowa, budżet ~5 000 zł/mies w Google Ads, kampanie prowadzone od 14 miesięcy. Trzy znaleziska — wartość: ponad 21 000 zł rocznie.
        </p>
      </div>

      <div class="aga-example-wrap">
        <div class="aga-example-eyebrow">Pierwsze 60 minut analizy konta</div>
        <h3>Trzy największe problemy które kosztowały tego klienta 1 800 zł co miesiąc</h3>
        <p class="aga-example-context">
          To nie były rzeczy ukryte w skomplikowanych ustawieniach — to były podstawy, których nikt nie pilnował. Klient przepalał ponad 35% budżetu reklamowego od ponad roku, nie wiedząc o tym.
        </p>

        <div class="aga-findings">
          <div class="aga-finding">
            <span class="aga-finding-tag">Brak filtrów</span>
            <h4>Zero negatywnych słów kluczowych</h4>
            <p>Reklamy pojawiały się na frazy „darmowy", „za darmo", „praca", „kariera", „kurs". Aż 23% budżetu szło na ruch który nigdy nie kupi.</p>
            <div class="aga-finding-cost">
              <span class="num">620</span><span class="unit">zł / mies</span>
            </div>
          </div>

          <div class="aga-finding">
            <span class="aga-finding-tag">Algorytm na śmieciach</span>
            <h4>Maximize Conversions bez danych</h4>
            <p>Strategia stawek wymagająca min. 30 konwersji/mies — klient miał 4. Algorytm „uczył się" miesiącami na sygnałach, które nie miały sensu biznesowego.</p>
            <div class="aga-finding-cost">
              <span class="num">700</span><span class="unit">zł / mies</span>
            </div>
          </div>

          <div class="aga-finding">
            <span class="aga-finding-tag">Kanibalizacja</span>
            <h4>Performance Max bez wykluczeń</h4>
            <p>Kampania PMax wyświetlała reklamy na frazy brandowe — klient płacił Google za ruch, który tak czy tak by trafił do niego organicznie.</p>
            <div class="aga-finding-cost">
              <span class="num">480</span><span class="unit">zł / mies</span>
            </div>
          </div>
        </div>

        <div class="aga-example-summary">
          <div class="aga-example-summary-text">
            <strong>Razem do odzyskania:</strong> 36% budżetu reklamowego. Pieniądze, które od 14 miesięcy szły do Google bez biznesowego sensu. Naprawialne w 3–5 dni roboczych.
          </div>
          <div class="aga-example-summary-amount">
            21 600 zł
            <small>rocznych strat do odzyskania</small>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="aga-section aga-section-soft">
    <div class="aga-container">
      <div class="aga-section-head">
        <span class="aga-section-eyebrow">Dla kogo</span>
        <h2>Dla kogo audyt ma sens, a dla kogo nie</h2>
        <p class="aga-section-intro">
          Mam ograniczoną liczbę slotów (5 audytów tygodniowo). Wolę żebyś od razu wiedział, czy to dla Ciebie — nie marnuję Twojego czasu, jeśli nie pasujemy do siebie.
        </p>
      </div>

      <div class="aga-target">
        <div class="aga-target-col is-yes">
          <h4>Audyt ma sens jeśli:</h4>
          <ul>
            <li>→ Wydajesz 1 000–20 000 zł/mies w Google Ads</li>
            <li>→ Masz konto starsze niż 2 miesiące (są dane do analizy)</li>
            <li>→ Masz wrażenie że budżet leci, a leadów mało</li>
            <li>→ Konto prowadzisz sam lub przez agencję — i nie wiesz czy to dobrze</li>
            <li>→ Chcesz drugie zdanie zanim podejmiesz decyzję o większych zmianach</li>
          </ul>
        </div>

        <div class="aga-target-col is-no">
          <h4>Audyt nie ma sensu jeśli:</h4>
          <ul>
            <li>→ Nie masz jeszcze konta Google Ads (uruchomienie to inny temat)</li>
            <li>→ Konto działa krócej niż 30 dni (brak danych)</li>
            <li>→ Szukasz osoby do napisania reklam (to nie audyt, to copywriting)</li>
            <li>→ Sprzedajesz produkty lub usługi nielegalne / wątpliwe</li>
            <li>→ Twój budżet to poniżej 500 zł/mies (audyt nie zwróci się)</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section class="aga-section">
    <div class="aga-container">
      <div class="aga-section-head">
        <span class="aga-section-eyebrow">Kto patrzy na Twoje konto</span>
        <h2>Po drugiej stronie ekranu</h2>
      </div>

      <div class="aga-author">
        <div class="aga-author-photo">SK</div>
        <div class="aga-author-content">
          <h3>Sebastian Kelm</h3>
          <p class="aga-author-role">Konsultant marketingu — Google Ads · Meta Ads · strony B2B</p>
          <p>
            Pracuję sam, bez agencji i podwykonawców, z maksymalnie 5–8 klientami jednocześnie. Specjalizuję się w generowaniu leadów dla firm B2B przez płatny ruch. Dziesięć lat w marketingu, ostatnie cztery na własnej działalności. Ten audyt robię osobiście — nie deleguję go juniorowi ani algorytmowi. Jeśli się dogadamy na współpracę, też pracuję z Tobą bezpośrednio.
          </p>
        </div>
      </div>
    </div>
  </section>

  <section class="aga-section aga-section-soft">
    <div class="aga-container">
      <div class="aga-section-head is-center">
        <span class="aga-section-eyebrow">FAQ</span>
        <h2>Pytania które dostaję najczęściej</h2>
      </div>

      <div class="aga-faq-list">
        <details class="aga-faq">
          <summary>Naprawdę bezpłatne? Jaki jest haczyk?</summary>
          <div class="aga-faq-content">
            <p>Nie ma haczyka. Robię to z dwóch powodów: (1) chcę pokazać jak pracuję — to lepsza wizytówka niż portfolio i prezentacje, (2) z 10 audytów 2–3 firmy pytają o stałą współpracę po wdrożeniu zmian. Resztę po prostu rozjeżdżam, jeśli rynek mnie znajdzie.</p>
            <p>Nie sprzedaję na audycie — sprzedaję wyniki. Audyt to wyniki, których jeszcze nie masz.</p>
          </div>
        </details>

        <details class="aga-faq">
          <summary>Mam małe konto (np. 1 500 zł budżetu). Też się przydaje?</summary>
          <div class="aga-faq-content">
            <p>Tak — i często bardziej niż dużym kontom. W małym koncie każde 200 zł zmarnowanego budżetu to 13% straty miesięcznie. Małe konta zwykle mają najprostsze błędy do naprawienia.</p>
            <p>30 minut audytu często odzyskuje 300–500 zł/mies przez najbliższy rok. To 12-miesięczny zwrot 4 000–6 000 zł z bezpłatnej godziny rozmowy.</p>
          </div>
        </details>

        <details class="aga-faq">
          <summary>Co jeśli moje konto prowadzi już agencja?</summary>
          <div class="aga-faq-content">
            <p>Świetnie — wtedy audyt to drugi pogląd. Powiem Ci wprost czy to, co robi agencja, ma sens, czy lecisz na autopilocie.</p>
            <p>Nie pochwalę dla pochwały i nie skrytykuję, żeby Cię zwerbować. Nie pytam nawet kto prowadzi konto — to nie ma znaczenia. Patrzę na konfigurację, a nie na nazwiska.</p>
          </div>
        </details>

        <details class="aga-faq">
          <summary>Jaki dostęp do konta będę musiał Ci dać?</summary>
          <div class="aga-faq-content">
            <p>Tylko dostęp <strong>„Tylko do odczytu" (Read-Only)</strong> — nie mogę nic zmieniać, tworzyć ani usuwać. Daję Ci moje ID klienta Google Ads, dodajesz mnie w 30 sekund w sekcji Administracja → Dostępy.</p>
            <p>Po audycie usuwasz mnie tym samym kliknięciem. Bezpieczne dla Ciebie i dla mnie — nie chcę odpowiadać za zmiany, których nie autoryzowałeś.</p>
          </div>
        </details>

        <details class="aga-faq">
          <summary>Co jeśli stwierdzę, że audyt mi się nie przydał?</summary>
          <div class="aga-faq-content">
            <p>Wtedy się kończymy i każdy idzie swoją drogą. Bez follow-upów, bez maili „przypominam się", bez nacisku na dalsze rozmowy.</p>
            <p>Twój czas jest Twój. Jeśli czujesz, że audyt był stratą czasu — daj znać, popraw mi to przy następnym kliencie. Wolę szczerą krytykę niż grzeczne milczenie.</p>
          </div>
        </details>

        <details class="aga-faq">
          <summary>Jak długo czeka się na termin?</summary>
          <div class="aga-faq-content">
            <p>Zwykle 3–7 dni od wypełnienia formularza. Robię maksymalnie 5 audytów tygodniowo, żeby utrzymać jakość — audyt to nie kserówka, tylko 60–90 min realnej analizy plus rozmowa.</p>
            <p>Jeśli wszystkie sloty są zajęte, dostajesz ode mnie maila z najwcześniejszym wolnym terminem. Bez listy oczekujących która niczego nie znaczy.</p>
          </div>
        </details>

        <details class="aga-faq">
          <summary>Co dostaję na piśmie po audycie?</summary>
          <div class="aga-faq-content">
            <p>Krótkie podsumowanie 1–2 strony A4 z trzema głównymi znaleziskami, kosztami które generują i konkretnymi krokami do wdrożenia. Wysyłam mailem 24h po rozmowie.</p>
            <p>Bez tabel Excel, bez wykresów. Tekst do przeczytania w 5 minut, decyzja na podstawie konkretu.</p>
          </div>
        </details>
      </div>
    </div>
  </section>

  <section class="aga-final" id="kontakt">
    <div class="aga-container aga-final-inner">
      <h2>Umów bezpłatny audyt</h2>
      <p class="aga-final-sub">
        Wypełnij formularz, odpowiem w ciągu 24h z propozycją terminów. Bez handlowych telefonów, bez wciskania ofert — sam wiesz że czasem ktoś po prostu robi dobrą robotę.
      </p>

      <form
        class="aga-form"
        id="audit-form"
        method="post"
        action="<?php echo esc_url(admin_url("admin-post.php")); ?>"
        data-audit-form="google-ads"
        data-upsellio-lead-form="1"
        data-upsellio-server-form="1"
      >
        <input type="hidden" name="action" value="upsellio_submit_lead">
        <input type="hidden" name="redirect_url" value="<?php echo esc_url((get_permalink() ?: home_url("/audyt-google-ads/")) . "#kontakt"); ?>">
        <input type="hidden" name="lead_form_origin" value="audit-form">
        <input type="hidden" name="lead_source" value="audit-form">
        <input type="hidden" name="lead_service" value="Audyt Google Ads">
        <input type="hidden" name="lead_goal" value="Audyt konta Google Ads i plan naprawczy 30 dni">
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

        <div class="aga-form-grid">
          <div class="aga-field">
            <label for="audit_name">Imię</label>
            <input type="text" id="audit_name" name="lead_name" placeholder="Jan" autocomplete="given-name" required>
          </div>
          <div class="aga-field">
            <label for="audit_phone">Telefon</label>
            <input type="tel" id="audit_phone" name="lead_phone" placeholder="+48 575 522 595" autocomplete="tel" required>
          </div>
          <div class="aga-field aga-field-full">
            <label for="audit_email">E-mail</label>
            <input type="email" id="audit_email" name="lead_email" placeholder="jan@firma.pl" autocomplete="email" required>
          </div>
          <div class="aga-field aga-field-full">
            <label for="audit_message">Wiadomość <span class="opt">opcjonalnie</span></label>
            <textarea id="audit_message" name="lead_message" placeholder="Opcjonalnie — krótko, o co chodzi"></textarea>
          </div>
        </div>

        <label class="aga-consent">
          <input type="checkbox" name="lead_consent" value="1" required>
          <span>Wyrażam zgodę na kontakt w sprawie audytu i akceptuję <a href="<?php echo esc_url(home_url("/polityka-prywatnosci/")); ?>" target="_blank" rel="noopener noreferrer">politykę prywatności</a>.</span>
        </label>

        <button type="submit" class="aga-form-submit" data-cta="audyt-final-submit" data-cta-section="final-form" data-cta-position="submit">Oddzwonię w ciągu 24h</button>

        <p class="aga-form-meta">
          Twoje dane służą wyłącznie do umówienia audytu. Nie zapisuję Cię na newsletter,<br>nie sprzedaję bazy. Po audycie kasuję dane jeśli nie nawiążemy współpracy.
        </p>
      </form>
    </div>
  </section>
</div>

<?php get_footer(); ?>
