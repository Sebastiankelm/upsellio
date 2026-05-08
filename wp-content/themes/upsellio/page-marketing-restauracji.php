<?php
/**
 * Template Name: Marketing dla restauracji — landing
 *
 * Niszowy landing dla branzy gastronomicznej.
 */
get_header();
?>

<style>
  :root {
    --rest-radius: 20px;
    --rest-radius-lg: 28px;
    --rest-orange: #ea580c;
    --rest-orange-soft: #fff7ed;
    --rest-orange-line: #fed7aa;
    --rest-amber: #f59e0b;
  }

  .rest-page {
    background: var(--bg, #fafaf6);
    color: var(--text, #0d0d0b);
    font-family: var(--font-body, "DM Sans"), system-ui, sans-serif;
    overflow-x: hidden;
  }
  .rest-page * { box-sizing: border-box; }
  .rest-container { max-width: 1100px; margin: 0 auto; padding: 0 24px; }

  .rest-hero {
    position: relative;
    padding: clamp(60px, 10vw, 100px) 0 clamp(50px, 8vw, 80px);
    overflow: hidden;
  }
  .rest-hero::before {
    content: "";
    position: absolute;
    inset: 0;
    background:
      radial-gradient(ellipse 700px 400px at 80% 20%, rgba(234, 88, 12, 0.07), transparent),
      radial-gradient(ellipse 500px 300px at 10% 80%, rgba(245, 158, 11, 0.05), transparent);
    pointer-events: none;
    z-index: 0;
  }
  .rest-hero-inner { position: relative; z-index: 1; }
  .rest-hero-grid {
    display: grid;
    grid-template-columns: 1.15fr 1fr;
    gap: 56px;
    align-items: center;
  }
  @media (max-width: 900px) {
    .rest-hero-grid { grid-template-columns: 1fr; gap: 40px; }
  }

  .rest-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--rest-orange-soft);
    color: #c2410c;
    padding: 8px 16px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.4px;
    text-transform: uppercase;
    margin-bottom: 24px;
  }
  .rest-pill::before {
    content: "";
    width: 6px;
    height: 6px;
    background: var(--rest-orange);
    border-radius: 50%;
    animation: restPulse 2s ease-in-out infinite;
  }
  @keyframes restPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(0.85); }
  }

  .rest-hero h1 {
    font-family: var(--font-display, "Bricolage Grotesque"), serif;
    font-size: clamp(36px, 5.5vw, 60px);
    font-weight: 800;
    line-height: 1.04;
    letter-spacing: -0.025em;
    margin: 0 0 24px;
    color: var(--text);
  }
  .rest-hero h1 em {
    font-style: normal;
    background: linear-gradient(120deg, var(--rest-orange) 0%, var(--rest-amber) 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    color: transparent;
  }
  .rest-hero-sub {
    font-size: clamp(16px, 2vw, 19px);
    line-height: 1.6;
    color: var(--text-soft, #7a7a72);
    margin: 0 0 32px;
    max-width: 540px;
  }
  .rest-hero-cta-row {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 32px;
  }
  .rest-cta-primary {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: var(--text, #0d0d0b);
    color: #fff;
    padding: 18px 32px;
    border-radius: 14px;
    font-size: 16px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s var(--ease-out, cubic-bezier(0.16, 1, 0.3, 1));
    box-shadow: 0 4px 16px rgba(13, 13, 11, 0.15);
  }
  .rest-cta-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(13, 13, 11, 0.2);
    color: #fff;
  }
  .rest-cta-primary::after { content: "→"; transition: transform 0.2s ease; }
  .rest-cta-primary:hover::after { transform: translateX(4px); }

  .rest-cta-secondary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--text);
    padding: 18px 24px;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    border-bottom: 2px solid transparent;
    transition: all 0.15s ease;
  }
  .rest-cta-secondary:hover { border-bottom-color: var(--rest-orange); color: var(--rest-orange); }
  .rest-trust-row {
    display: flex;
    gap: 24px;
    flex-wrap: wrap;
    color: var(--text-soft, #7a7a72);
    font-size: 14px;
  }
  .rest-trust-item { display: inline-flex; align-items: center; gap: 8px; }
  .rest-trust-item::before {
    content: "✓";
    color: var(--rest-orange);
    font-weight: 800;
    font-size: 16px;
  }

  .rest-dashboard {
    background: #fff;
    border: 1px solid var(--border, #e8e8e0);
    border-radius: var(--rest-radius);
    padding: 24px;
    box-shadow: var(--shadow, 0 22px 60px rgba(15, 23, 42, 0.1));
    position: relative;
    transform: rotate(-0.5deg);
  }
  .rest-dashboard::before {
    content: "Weekend marca";
    position: absolute;
    top: -12px;
    left: 24px;
    background: linear-gradient(135deg, var(--rest-orange) 0%, var(--rest-amber) 100%);
    color: #fff;
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
  }
  .rest-dashboard-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--border);
  }
  .rest-dashboard-name {
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 700;
    color: var(--text-muted);
  }
  .rest-dashboard-status {
    background: var(--success-soft, #f0faf4);
    color: var(--success, #15803d);
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
  }
  .rest-stats-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-bottom: 16px; }
  .rest-stat { background: var(--bg-alt, #f2f2ec); padding: 12px; border-radius: 10px; text-align: center; }
  .rest-stat-icon { font-size: 16px; margin-bottom: 2px; }
  .rest-stat-label {
    font-size: 9.5px;
    font-weight: 700;
    color: var(--text-soft);
    letter-spacing: 0.3px;
    text-transform: uppercase;
    margin-bottom: 3px;
  }
  .rest-stat-val {
    font-family: var(--font-display);
    font-size: 18px;
    font-weight: 800;
    color: var(--text);
    font-variant-numeric: tabular-nums;
  }
  .rest-stat-trend { font-size: 10px; font-weight: 700; color: var(--success); margin-top: 1px; }

  .rest-table-section {
    background: linear-gradient(135deg, #fff7ed 0%, #fff 100%);
    border-radius: 10px;
    padding: 14px 16px;
    margin-bottom: 14px;
  }
  .rest-table-section-title {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    letter-spacing: 0.4px;
    text-transform: uppercase;
    margin-bottom: 10px;
  }
  .rest-evening {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 0;
    font-size: 13px;
  }
  .rest-evening:not(:last-child) { border-bottom: 1px dashed var(--border); }
  .rest-evening-day { font-family: var(--font-display); font-weight: 700; color: var(--text); }
  .rest-evening-tables { display: flex; gap: 3px; }
  .rest-table-dot { width: 12px; height: 12px; border-radius: 3px; }
  .rest-table-dot.is-booked { background: var(--rest-orange); }
  .rest-table-dot.is-free { background: var(--bg-alt); border: 1px solid var(--border); }
  .rest-evening-pct {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    font-variant-numeric: tabular-nums;
    width: 40px;
    text-align: right;
  }
  .rest-bottom-msg {
    background: linear-gradient(90deg, var(--success, #15803d) 0%, #22c55e 100%);
    color: #fff;
    padding: 12px 16px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 700;
    text-align: center;
  }

  .rest-section { padding: clamp(60px, 8vw, 100px) 0; position: relative; }
  .rest-section-soft { background: var(--surface, #fff); }
  .rest-section-head { max-width: 720px; margin: 0 0 56px; }
  .rest-section-head.is-center { margin: 0 auto 56px; text-align: center; }
  .rest-section-eyebrow {
    font-size: 12px;
    font-weight: 700;
    color: #c2410c;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    margin-bottom: 12px;
    display: inline-block;
  }
  .rest-section h2 {
    font-family: var(--font-display);
    font-size: clamp(28px, 3.8vw, 42px);
    font-weight: 800;
    line-height: 1.15;
    letter-spacing: -0.018em;
    margin: 0 0 16px;
    color: var(--text);
  }
  .rest-section-intro {
    font-size: clamp(16px, 1.6vw, 18px);
    line-height: 1.65;
    color: var(--text-soft);
    margin: 0;
  }

  .rest-problems {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
    margin-top: 16px;
  }
  @media (max-width: 800px) { .rest-problems { grid-template-columns: 1fr; } }
  .rest-problem {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--rest-radius);
    padding: 28px;
    transition: all 0.25s var(--ease-out);
  }
  .rest-problem:hover {
    transform: translateY(-3px);
    border-color: var(--rest-orange);
    box-shadow: var(--shadow-soft);
  }
  .rest-problem-quote {
    font-family: var(--font-display);
    font-size: 22px;
    font-weight: 700;
    line-height: 1.3;
    color: var(--text);
    margin: 0 0 14px;
    position: relative;
    padding-top: 24px;
  }
  .rest-problem-quote::before {
    content: '"';
    position: absolute;
    top: -10px;
    left: -8px;
    font-size: 60px;
    color: var(--rest-orange);
    line-height: 1;
    font-family: serif;
    opacity: 0.4;
  }
  .rest-problem p { font-size: 14px; line-height: 1.6; color: var(--text-soft); margin: 0; }

  .rest-package-wrap {
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    gap: 24px;
    margin-top: 16px;
  }
  @media (max-width: 800px) { .rest-package-wrap { grid-template-columns: 1fr; } }
  .rest-package {
    background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
    border: 1px solid var(--rest-orange-line);
    border-radius: var(--rest-radius-lg);
    padding: clamp(28px, 4vw, 44px);
    position: relative;
  }
  .rest-package-tag {
    display: inline-block;
    background: var(--rest-orange);
    color: #fff;
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-bottom: 16px;
  }
  .rest-package h3 {
    font-family: var(--font-display);
    font-size: clamp(22px, 2.5vw, 30px);
    font-weight: 800;
    line-height: 1.2;
    margin: 0 0 12px;
    color: var(--text);
  }
  .rest-package-desc { font-size: 15px; line-height: 1.6; color: var(--text-muted); margin: 0 0 28px; }
  .rest-package-list { margin: 0; padding: 0; list-style: none; }
  .rest-package-list li {
    padding: 10px 0 10px 32px;
    position: relative;
    font-size: 15px;
    line-height: 1.5;
    color: var(--text);
    border-bottom: 1px dashed rgba(194, 65, 12, 0.15);
  }
  .rest-package-list li:last-child { border-bottom: 0; }
  .rest-package-list li::before {
    content: "✓";
    position: absolute;
    left: 0;
    top: 8px;
    width: 22px;
    height: 22px;
    background: var(--rest-orange);
    color: #fff;
    border-radius: 50%;
    display: grid;
    place-items: center;
    font-size: 12px;
    font-weight: 800;
  }

  .rest-pricing {
    background: var(--surface);
    border: 2px solid var(--text);
    border-radius: var(--rest-radius-lg);
    padding: clamp(28px, 4vw, 36px);
    text-align: center;
    position: sticky;
    top: 24px;
  }
  .rest-pricing-label {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-soft);
    letter-spacing: 0.6px;
    text-transform: uppercase;
    margin-bottom: 8px;
  }
  .rest-pricing-amount {
    font-family: var(--font-display);
    font-size: clamp(36px, 5vw, 52px);
    font-weight: 800;
    line-height: 1;
    color: var(--text);
    margin-bottom: 4px;
  }
  .rest-pricing-amount small {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-soft);
    margin-left: 4px;
  }
  .rest-pricing-period { font-size: 13px; color: var(--text-soft); margin-bottom: 24px; }
  .rest-pricing-extras {
    margin: 24px 0 0;
    padding: 20px 0 0;
    border-top: 1px solid var(--border);
    text-align: left;
    list-style: none;
  }
  .rest-pricing-extras li {
    padding: 6px 0 6px 24px;
    position: relative;
    font-size: 13px;
    color: var(--text-muted);
    line-height: 1.5;
  }
  .rest-pricing-extras li::before {
    content: "+";
    position: absolute;
    left: 0;
    color: var(--rest-orange);
    font-weight: 800;
  }
  .rest-pricing-cta {
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
  .rest-pricing-cta:hover {
    background: var(--rest-orange);
    color: #fff;
    transform: translateY(-1px);
  }

  .rest-honest {
    background: linear-gradient(135deg, var(--accent-soft, #fff7ed) 0%, #fff 100%);
    border: 1px solid var(--accent-line, #fed7aa);
    border-radius: var(--rest-radius);
    padding: clamp(28px, 4vw, 44px);
    margin-top: 32px;
  }
  .rest-honest-header { display: flex; gap: 16px; align-items: center; margin-bottom: 20px; }
  .rest-honest-icon {
    flex-shrink: 0;
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: var(--accent, #f97316);
    color: #fff;
    display: grid;
    place-items: center;
    font-size: 22px;
    font-weight: 800;
  }
  .rest-honest-header strong {
    font-family: var(--font-display);
    font-size: 18px;
    font-weight: 700;
  }
  .rest-honest ul {
    margin: 0;
    padding: 0;
    list-style: none;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px 24px;
  }
  @media (max-width: 600px) { .rest-honest ul { grid-template-columns: 1fr; } }
  .rest-honest li {
    padding: 10px 0 10px 28px;
    position: relative;
    font-size: 15px;
    line-height: 1.5;
    color: var(--text-muted);
  }
  .rest-honest li::before {
    content: "✗";
    position: absolute;
    left: 0;
    top: 8px;
    color: var(--accent);
    font-weight: 800;
    font-size: 18px;
  }

  .rest-steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: 16px; }
  @media (max-width: 800px) { .rest-steps { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 500px) { .rest-steps { grid-template-columns: 1fr; } }
  .rest-step {
    background: var(--surface, #fff);
    border: 1px solid var(--border);
    border-radius: var(--rest-radius);
    padding: 24px;
    position: relative;
    transition: all 0.25s var(--ease-out);
  }
  .rest-step:hover {
    transform: translateY(-3px);
    border-color: var(--rest-orange);
    box-shadow: var(--shadow-soft);
  }
  .rest-step-num {
    font-family: var(--font-display);
    font-size: 32px;
    font-weight: 800;
    line-height: 1;
    color: var(--rest-orange);
    margin-bottom: 16px;
    font-variant-numeric: tabular-nums;
  }
  .rest-step h4 { font-family: var(--font-display); font-size: 16px; font-weight: 700; line-height: 1.3; margin: 0 0 8px; }
  .rest-step p { font-size: 13px; line-height: 1.6; color: var(--text-soft); margin: 0; }
  .rest-step-time {
    display: inline-block;
    margin-top: 12px;
    font-size: 11px;
    font-weight: 700;
    color: var(--text-soft);
    letter-spacing: 0.5px;
    text-transform: uppercase;
    padding: 4px 8px;
    background: var(--bg-alt, #f2f2ec);
    border-radius: 6px;
  }

  .rest-example-wrap {
    background: linear-gradient(135deg, #1a0f08 0%, #2d1810 100%);
    color: #fff;
    border-radius: var(--rest-radius-lg);
    padding: clamp(36px, 5vw, 56px);
    margin-top: 16px;
    position: relative;
    overflow: hidden;
  }
  .rest-example-wrap::before {
    content: "";
    position: absolute;
    top: -100px;
    right: -100px;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(234, 88, 12, 0.18) 0%, transparent 60%);
    pointer-events: none;
  }
  .rest-example-wrap > * { position: relative; }
  .rest-example-eyebrow {
    font-size: 11px;
    font-weight: 700;
    color: #fdba74;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    margin-bottom: 14px;
  }
  .rest-example-wrap h3 {
    font-family: var(--font-display);
    font-size: clamp(22px, 2.5vw, 30px);
    font-weight: 800;
    line-height: 1.25;
    margin: 0 0 12px;
    color: #fff;
  }
  .rest-example-context {
    color: rgba(255, 255, 255, 0.7);
    font-size: 15px;
    line-height: 1.6;
    margin: 0 0 32px;
    max-width: 600px;
  }
  .rest-comparison { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 32px; }
  @media (max-width: 700px) { .rest-comparison { grid-template-columns: 1fr; } }
  .rest-state {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border-radius: 16px;
    padding: 24px;
  }
  .rest-state.is-after {
    background: rgba(234, 88, 12, 0.12);
    border-color: rgba(234, 88, 12, 0.3);
  }
  .rest-state-label {
    display: inline-block;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    padding: 4px 8px;
    border-radius: 6px;
    margin-bottom: 14px;
  }
  .rest-state.is-after .rest-state-label { background: var(--rest-orange); }
  .rest-state h4 { font-family: var(--font-display); font-size: 15px; font-weight: 700; margin: 0 0 14px; color: #fff; }
  .rest-state-metric {
    padding: 10px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
    color: rgba(255, 255, 255, 0.85);
  }
  .rest-state-metric:last-child { border-bottom: 0; }
  .rest-state-metric strong {
    font-family: var(--font-display);
    font-variant-numeric: tabular-nums;
    font-weight: 800;
    font-size: 15px;
  }
  .rest-state.is-after .rest-state-metric strong { color: #fdba74; }
  .rest-example-summary {
    border-top: 1px solid rgba(255, 255, 255, 0.12);
    padding-top: 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
  }
  .rest-example-summary-text {
    color: rgba(255, 255, 255, 0.85);
    font-size: 15px;
    line-height: 1.5;
    max-width: 520px;
  }
  .rest-example-summary-amount {
    font-family: var(--font-display);
    font-size: clamp(28px, 3.5vw, 38px);
    font-weight: 800;
    color: #fff;
    text-align: right;
  }
  .rest-example-summary-amount small {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.6);
    letter-spacing: 0.4px;
    text-transform: uppercase;
    margin-top: 4px;
  }

  .rest-target { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
  @media (max-width: 700px) { .rest-target { grid-template-columns: 1fr; } }
  .rest-target-col { padding: 28px; border-radius: var(--rest-radius); border: 1px solid var(--border); }
  .rest-target-col.is-yes { background: var(--rest-orange-soft); border-color: rgba(234, 88, 12, 0.2); }
  .rest-target-col.is-no { background: var(--bg-alt, #f2f2ec); }
  .rest-target-col h4 {
    font-family: var(--font-display);
    font-size: 17px;
    font-weight: 700;
    margin: 0 0 16px;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .rest-target-col h4::before {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    font-size: 14px;
    font-weight: 800;
    flex-shrink: 0;
  }
  .rest-target-col.is-yes h4::before { content: "✓"; background: var(--rest-orange); color: #fff; }
  .rest-target-col.is-no h4::before { content: "—"; background: var(--text-soft); color: #fff; }
  .rest-target-col ul { margin: 0; padding: 0; list-style: none; }
  .rest-target-col li { padding: 8px 0; font-size: 14px; line-height: 1.55; color: var(--text-muted); }

  .rest-faq-list { max-width: 760px; margin: 0 auto; }
  .rest-faq {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--rest-radius);
    margin-bottom: 12px;
    overflow: hidden;
    transition: border-color 0.2s ease;
  }
  .rest-faq:hover { border-color: var(--rest-orange); }
  .rest-faq[open] { border-color: var(--rest-orange); box-shadow: var(--shadow-sm); }
  .rest-faq summary {
    padding: 22px 28px;
    cursor: pointer;
    list-style: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    font-family: var(--font-display);
    font-size: 16px;
    font-weight: 700;
    line-height: 1.4;
    color: var(--text);
    user-select: none;
  }
  .rest-faq summary::-webkit-details-marker { display: none; }
  .rest-faq summary::after {
    content: "+";
    flex-shrink: 0;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: var(--rest-orange-soft);
    color: #c2410c;
    display: grid;
    place-items: center;
    font-size: 18px;
    font-weight: 400;
    transition: all 0.2s ease;
  }
  .rest-faq[open] summary::after {
    content: "−";
    background: var(--rest-orange);
    color: #fff;
    transform: rotate(180deg);
  }
  .rest-faq-content {
    padding: 0 28px 24px;
    color: var(--text-soft);
    font-size: 15px;
    line-height: 1.7;
  }
  .rest-faq-content p { margin: 0 0 12px; }
  .rest-faq-content p:last-child { margin: 0; }

  .rest-author {
    background: var(--sand, #f5f0e8);
    border-radius: var(--rest-radius-lg);
    padding: clamp(36px, 5vw, 56px);
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 32px;
    align-items: center;
    margin-top: 16px;
  }
  @media (max-width: 700px) { .rest-author { grid-template-columns: 1fr; text-align: center; } }
  .rest-author-photo {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--rest-orange) 0%, #c2410c 100%);
    color: #fff;
    display: grid;
    place-items: center;
    font-family: var(--font-display);
    font-size: 42px;
    font-weight: 800;
    flex-shrink: 0;
    margin: 0 auto;
  }
  .rest-author-content h3 { font-family: var(--font-display); font-size: 22px; font-weight: 800; margin: 0 0 8px; }
  .rest-author-role { font-size: 14px; font-weight: 600; color: #c2410c; margin: 0 0 16px; }
  .rest-author-content p { font-size: 15px; line-height: 1.65; color: var(--text-muted); margin: 0; }

  .rest-final {
    background: linear-gradient(180deg, var(--text, #0d0d0b) 0%, #1a0f08 100%);
    color: #fff;
    padding: clamp(60px, 9vw, 96px) 0;
    position: relative;
    overflow: hidden;
  }
  .rest-final::before {
    content: "";
    position: absolute;
    top: -100px;
    left: 50%;
    transform: translateX(-50%);
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, rgba(234, 88, 12, 0.18) 0%, transparent 70%);
    pointer-events: none;
  }
  .rest-final-inner { position: relative; max-width: 640px; margin: 0 auto; text-align: center; }
  .rest-final h2 { color: #fff; margin-bottom: 16px; }
  .rest-final-sub { font-size: 17px; line-height: 1.6; color: rgba(255, 255, 255, 0.7); margin: 0 0 40px; }

  .rest-form {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-radius: var(--rest-radius);
    padding: clamp(24px, 4vw, 36px);
    text-align: left;
  }
  .rest-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  @media (max-width: 600px) { .rest-form-grid { grid-template-columns: 1fr; } }
  .rest-field { margin-bottom: 16px; }
  .rest-field-full { grid-column: 1 / -1; }
  .rest-field label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.85);
    margin-bottom: 8px;
  }
  .rest-field label .opt {
    font-weight: 400;
    color: rgba(255, 255, 255, 0.4);
    font-size: 12px;
    margin-left: 6px;
  }
  .rest-field input,
  .rest-field textarea,
  .rest-field select {
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
  .rest-field input::placeholder,
  .rest-field textarea::placeholder { color: rgba(255, 255, 255, 0.35); }
  .rest-field input:focus,
  .rest-field textarea:focus,
  .rest-field select:focus {
    outline: none;
    border-color: var(--rest-orange);
    background: rgba(255, 255, 255, 0.1);
  }
  .rest-field textarea { min-height: 90px; resize: vertical; }
  .rest-form-submit {
    width: 100%;
    padding: 18px;
    background: var(--rest-orange);
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
  .rest-form-submit:hover {
    background: #c2410c;
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(234, 88, 12, 0.3);
  }
  .rest-form-meta {
    text-align: center;
    margin-top: 20px;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.4);
    line-height: 1.5;
  }
  .rest-form-feedback {
    margin-bottom: 20px;
    padding: 12px 14px;
    border-radius: 10px;
    font-size: 14px;
  }
  .rest-form-feedback.is-success {
    background: rgba(22, 163, 74, 0.18);
    border: 1px solid rgba(34, 197, 94, 0.45);
    color: #dcfce7;
  }
  .rest-form-feedback.is-error {
    background: rgba(220, 38, 38, 0.18);
    border: 1px solid rgba(248, 113, 113, 0.45);
    color: #fee2e2;
  }
</style>

<div class="rest-page">
  <section class="rest-hero">
    <div class="rest-container rest-hero-inner">
      <div class="rest-hero-grid">
        <div>
          <div class="rest-pill">Marketing dla restauracji</div>
          <h1>Pelna sala w piatek, <em>wypelniony srodowy wieczor</em></h1>
          <p class="rest-hero-sub">
            Reklama Google + Instagram + integracja z systemami rezerwacji, ktore dowoza gosci w te dni, gdy najbardziej Ci ich brakuje.
          </p>
          <div class="rest-hero-cta-row">
            <a href="#kontakt" class="rest-cta-primary" data-cta="hero-umow-rozmowe">Umow rozmowe</a>
            <a href="#oferta" class="rest-cta-secondary" data-cta="hero-zobacz-pakiet">Zobacz pakiet</a>
          </div>
          <div class="rest-trust-row">
            <span class="rest-trust-item">Pierwsze rezerwacje w 14 dni</span>
            <span class="rest-trust-item">Bez umow na rok</span>
            <span class="rest-trust-item">Pierwsza konsultacja gratis</span>
          </div>
        </div>

        <div>
          <div class="rest-dashboard" aria-hidden="true">
            <div class="rest-dashboard-header">
              <span class="rest-dashboard-name">Restauracja Bellevue — Wroclaw</span>
              <span class="rest-dashboard-status">Po 60 dniach</span>
            </div>
            <div class="rest-stats-grid">
              <div class="rest-stat">
                <div class="rest-stat-icon">🍝</div>
                <div class="rest-stat-label">Goscie / tydz.</div>
                <div class="rest-stat-val">340</div>
                <div class="rest-stat-trend">+72%</div>
              </div>
              <div class="rest-stat">
                <div class="rest-stat-icon">📅</div>
                <div class="rest-stat-label">Rezerwacje</div>
                <div class="rest-stat-val">186</div>
                <div class="rest-stat-trend">+118%</div>
              </div>
              <div class="rest-stat">
                <div class="rest-stat-icon">⭐</div>
                <div class="rest-stat-label">Sredni rachunek</div>
                <div class="rest-stat-val">94 zl</div>
                <div class="rest-stat-trend">+12%</div>
              </div>
            </div>
            <div class="rest-table-section">
              <div class="rest-table-section-title">Wieczory tygodnia — zapelnienie sali</div>
              <div class="rest-evening">
                <span class="rest-evening-day">Wtorek</span>
                <div class="rest-evening-tables">
                  <span class="rest-table-dot is-booked"></span><span class="rest-table-dot is-booked"></span><span class="rest-table-dot is-booked"></span><span class="rest-table-dot is-booked"></span><span class="rest-table-dot is-booked"></span><span class="rest-table-dot is-booked"></span><span class="rest-table-dot is-free"></span><span class="rest-table-dot is-free"></span>
                </div>
                <span class="rest-evening-pct">75%</span>
              </div>
              <div class="rest-evening">
                <span class="rest-evening-day">Sroda</span>
                <div class="rest-evening-tables">
                  <span class="rest-table-dot is-booked"></span><span class="rest-table-dot is-booked"></span><span class="rest-table-dot is-booked"></span><span class="rest-table-dot is-booked"></span><span class="rest-table-dot is-booked"></span><span class="rest-table-dot is-booked"></span><span class="rest-table-dot is-booked"></span><span class="rest-table-dot is-free"></span>
                </div>
                <span class="rest-evening-pct">87%</span>
              </div>
              <div class="rest-evening">
                <span class="rest-evening-day">Czwartek</span>
                <div class="rest-evening-tables">
                  <span class="rest-table-dot is-booked"></span><span class="rest-table-dot is-booked"></span><span class="rest-table-dot is-booked"></span><span class="rest-table-dot is-booked"></span><span class="rest-table-dot is-booked"></span><span class="rest-table-dot is-booked"></span><span class="rest-table-dot is-booked"></span><span class="rest-table-dot is-booked"></span>
                </div>
                <span class="rest-evening-pct">100%</span>
              </div>
              <div class="rest-evening">
                <span class="rest-evening-day">Piatek</span>
                <div class="rest-evening-tables">
                  <span class="rest-table-dot is-booked"></span><span class="rest-table-dot is-booked"></span><span class="rest-table-dot is-booked"></span><span class="rest-table-dot is-booked"></span><span class="rest-table-dot is-booked"></span><span class="rest-table-dot is-booked"></span><span class="rest-table-dot is-booked"></span><span class="rest-table-dot is-booked"></span>
                </div>
                <span class="rest-evening-pct">100%</span>
              </div>
            </div>
            <div class="rest-bottom-msg">Czwartek i piatek juz z lista oczekujacych</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="rest-section rest-section-soft">
    <div class="rest-container">
      <div class="rest-section-head">
        <span class="rest-section-eyebrow">Jesli ktore brzmi znajomo</span>
        <h2>Trzy najczestsze problemy restauracji, ktore slysze</h2>
        <p class="rest-section-intro">Kazda restauracja ma inna historie. Ale w 8 na 10 rozmow przewija sie jedno z tych zdan.</p>
      </div>
      <div class="rest-problems">
        <div class="rest-problem">
          <p class="rest-problem-quote">Weekend pelny, a wtorek i sroda sa tragiczne.</p>
          <p>Piatek i sobota wyrabiaja caly utarg. Wtorki, srody i czwartki — pusta sala, koszty stale leca.</p>
        </div>
        <div class="rest-problem">
          <p class="rest-problem-quote">Pyszne.pl bierze 30% prowizji, ale tylko stamtad wpadaja zamowienia.</p>
          <p>Brak wlasnych kanalow dostawy/zamawiania. Pyszne.pl, Bolt i Glovo trzymaja klienta i marze.</p>
        </div>
        <div class="rest-problem">
          <p class="rest-problem-quote">Robie ladne posty na Instagramie. Goscie tego nie widza.</p>
          <p>Estetyczne zdjecia i stories, ale zasieg organiczny jest niski i nie przeklada sie na nowe rezerwacje.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="rest-section" id="oferta">
    <div class="rest-container">
      <div class="rest-section-head">
        <span class="rest-section-eyebrow">Co konkretnie robie</span>
        <h2>Pakiet "Pelna sala" — wszystko czego potrzebuje restauracja</h2>
        <p class="rest-section-intro">
          Jedna miesieczna stawka. Trzy kanaly dzialajace razem: Google Ads, Meta Ads i system rezerwacji + retargeting.
        </p>
      </div>
      <div class="rest-package-wrap">
        <div class="rest-package">
          <span class="rest-package-tag">Pakiet startowy</span>
          <h3>Wypelnione wieczory tygodnia w 60 dni</h3>
          <p class="rest-package-desc">
            Skupiamy sie na tym co realnie zarabia: weekendowi goscie wracaja w tygodniu, nowi goscie trafiaja z Google, a retargeting domyka rezerwacje.
          </p>
          <ul class="rest-package-list">
            <li>Kampania Google Ads na frazy lokalne</li>
            <li>Reklamy Meta z geolokalizacja do 5 km</li>
            <li>4-6 nowych kreacji food photo/video miesiecznie</li>
            <li>Promocje czasowe na slabe wieczory</li>
            <li>Integracja z systemem rezerwacji</li>
            <li>Optymalizacja profilu Google Maps</li>
            <li>Newsletter/SMS do bylych klientow</li>
            <li>Cotygodniowa optymalizacja + raport miesieczny</li>
          </ul>
        </div>

        <div class="rest-pricing">
          <div class="rest-pricing-label">Miesieczna oplata</div>
          <div class="rest-pricing-amount">2 200<small>zl</small></div>
          <div class="rest-pricing-period">+ Twoj budzet reklamowy (od 1 000 zl/mies)</div>
          <ul class="rest-pricing-extras">
            <li>Pierwsza konsultacja gratis (45 min)</li>
            <li>Bez umow na 12 miesiecy — wypowiadalna co miesiac</li>
            <li>Pierwszy miesiac 50% taniej</li>
            <li>Wszystkie kanaly w jednej stawce</li>
          </ul>
          <a href="#kontakt" class="rest-pricing-cta" data-cta="oferta-umow-konsultacje">Umow konsultacje →</a>
        </div>
      </div>
    </div>
  </section>

  <section class="rest-section rest-section-soft">
    <div class="rest-container">
      <div class="rest-section-head">
        <span class="rest-section-eyebrow">Szczerze</span>
        <h2>Czego NIE dostaniesz (i dlatego dobrze)</h2>
        <p class="rest-section-intro">Moj pakiet jest oparty o wynik biznesowy, nie puste metryki zasiegu.</p>
      </div>
      <div class="rest-honest">
        <div class="rest-honest-header">
          <div class="rest-honest-icon">!</div>
          <strong>W pakiecie nie znajdziesz:</strong>
        </div>
        <ul>
          <li>Obietnic viralowych rolek i milionow wyswietlen</li>
          <li>Liczenia zasiegow jako glownego KPI</li>
          <li>Influencerow za darmowe kolacje</li>
          <li>Strategii social na 30 stron bez wdrozenia</li>
          <li>Promocji 1 zl za pizze</li>
          <li>Konkursow "polub i udostepnij"</li>
        </ul>
      </div>
    </div>
  </section>

  <section class="rest-section">
    <div class="rest-container">
      <div class="rest-section-head">
        <span class="rest-section-eyebrow">Jak zaczynamy</span>
        <h2>Cztery kroki od rozmowy do zapelnionej sali</h2>
      </div>
      <div class="rest-steps">
        <div class="rest-step">
          <div class="rest-step-num">01</div>
          <h4>Bezplatna konsultacja</h4>
          <p>45 minut online lub w lokalu. Ocenimy, co ma najwiekszy potencjal.</p>
          <span class="rest-step-time">Tydzien 1</span>
        </div>
        <div class="rest-step">
          <div class="rest-step-num">02</div>
          <h4>Sesja foto + strategia</h4>
          <p>Spotkanie w lokalu i plan kampanii na 90 dni.</p>
          <span class="rest-step-time">Tydzien 2</span>
        </div>
        <div class="rest-step">
          <div class="rest-step-num">03</div>
          <h4>Reklamy ida w swiat</h4>
          <p>Uruchamiam Google Ads + Meta + optymalizacje Google Maps.</p>
          <span class="rest-step-time">Tydzien 3</span>
        </div>
        <div class="rest-step">
          <div class="rest-step-num">04</div>
          <h4>Ciagla optymalizacja</h4>
          <p>Co tydzien analizuje wyniki i poprawiam kampanie.</p>
          <span class="rest-step-time">Cyklicznie</span>
        </div>
      </div>
    </div>
  </section>

  <section class="rest-section rest-section-soft">
    <div class="rest-container">
      <div class="rest-section-head">
        <span class="rest-section-eyebrow">Konkret zamiast obietnic</span>
        <h2>Tak wygladaly pierwsze 90 dni dla restauracji z Wroclawia</h2>
      </div>
      <div class="rest-example-wrap">
        <div class="rest-example-eyebrow">Restauracja Bellevue — Wroclaw</div>
        <h3>Z 198 gosci tygodniowo do 340 — w 90 dni</h3>
        <p class="rest-example-context">
          Weekend robil 70% obrotu, a dni powszednie swiecily pustkami. Po 90 dniach sroda i czwartek wieczorem sa wypelnione.
        </p>
        <div class="rest-comparison">
          <div class="rest-state">
            <span class="rest-state-label">Przed</span>
            <h4>Styczen — przed startem</h4>
            <div class="rest-state-metric"><span>Goscie / tydzien</span><strong>198</strong></div>
            <div class="rest-state-metric"><span>Sroda wieczor</span><strong>34%</strong></div>
            <div class="rest-state-metric"><span>Czwartek wieczor</span><strong>52%</strong></div>
            <div class="rest-state-metric"><span>Sredni rachunek</span><strong>84 zl</strong></div>
            <div class="rest-state-metric"><span>Rezerwacje online</span><strong>12 / tyg.</strong></div>
            <div class="rest-state-metric"><span>Powroty</span><strong>~22%</strong></div>
          </div>
          <div class="rest-state is-after">
            <span class="rest-state-label">Po 90 dniach</span>
            <h4>Kwiecien — kampanie dzialaja</h4>
            <div class="rest-state-metric"><span>Goscie / tydzien</span><strong>340</strong></div>
            <div class="rest-state-metric"><span>Sroda wieczor</span><strong>87%</strong></div>
            <div class="rest-state-metric"><span>Czwartek wieczor</span><strong>100%</strong></div>
            <div class="rest-state-metric"><span>Sredni rachunek</span><strong>94 zl</strong></div>
            <div class="rest-state-metric"><span>Rezerwacje online</span><strong>52 / tyg.</strong></div>
            <div class="rest-state-metric"><span>Powroty</span><strong>~46%</strong></div>
          </div>
        </div>
        <div class="rest-example-summary">
          <div class="rest-example-summary-text">
            Trzy zmiany: Google Ads na frazy lokalne, Meta Ads w geo-targetingu 5 km, optymalizacja Google Maps + retargeting.
          </div>
          <div class="rest-example-summary-amount">
            +72%
            <small>wiecej gosci w 90 dni</small>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="rest-section">
    <div class="rest-container">
      <div class="rest-section-head">
        <span class="rest-section-eyebrow">Dla kogo</span>
        <h2>Dla kogo to ma sens</h2>
      </div>
      <div class="rest-target">
        <div class="rest-target-col is-yes">
          <h4>Pakiet ma sens jesli:</h4>
          <ul>
            <li>→ Masz lokal stacjonarny z minimum 25 miejscami</li>
            <li>→ Twoj przychod miesieczny to min. 50 000 zl</li>
            <li>→ Masz problem z zapelnieniem dni powszednich</li>
            <li>→ Masz Google Maps z opiniami (min. 4.0 i 30+ recenzji)</li>
            <li>→ Stac Cie na 2 200 zl/mies + 1 000 zl budzetu</li>
          </ul>
        </div>
        <div class="rest-target-col is-no">
          <h4>Pakiet NIE ma sensu jesli:</h4>
          <ul>
            <li>→ Lokal dopiero startuje</li>
            <li>→ Sprzedajesz glownie przez Pyszne.pl/Bolt</li>
            <li>→ Twoja kuchnia jest niewyrazna</li>
            <li>→ Opinie na Google sa ponizej 4.0</li>
            <li>→ Lokal jest w miejscowosci ponizej 30 000 mieszkancow</li>
            <li>→ Szukasz viralowych rolek z jedzeniem</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section class="rest-section rest-section-soft">
    <div class="rest-container">
      <div class="rest-section-head">
        <span class="rest-section-eyebrow">Kto bedzie z Toba pracowal</span>
        <h2>Po drugiej stronie ekranu</h2>
      </div>
      <div class="rest-author">
        <div class="rest-author-photo">SK</div>
        <div class="rest-author-content">
          <h3>Sebastian Kelm</h3>
          <p class="rest-author-role">Konsultant marketingu — Google Ads · Meta Ads · landing pages</p>
          <p>
            Pracuje sam, bez agencji, z maksymalnie 3 restauracjami na kwartal. Jesli stwierdze, ze Twoja restauracja nie jest gotowa na platne reklamy, powiem to wprost.
          </p>
        </div>
      </div>
    </div>
  </section>

  <section class="rest-section">
    <div class="rest-container">
      <div class="rest-section-head is-center">
        <span class="rest-section-eyebrow">FAQ</span>
        <h2>Pytania, ktore dostaje najczesciej</h2>
      </div>
      <div class="rest-faq-list">
        <details class="rest-faq">
          <summary>Ile to kosztuje miesiecznie razem z reklamami?</summary>
          <div class="rest-faq-content">
            <p>Stala oplata: <strong>2 200 zl netto/mies</strong>. Plus budzet reklamowy od 1 000 zl (rekomendowane 1 500-2 500 zl).</p>
          </div>
        </details>
        <details class="rest-faq">
          <summary>Po jakim czasie zobacze pierwszych gosci z reklam?</summary>
          <div class="rest-faq-content">
            <p>Pierwsze rezerwacje zwykle w 7-10 dni. Pelny efekt po 60-90 dniach.</p>
          </div>
        </details>
        <details class="rest-faq">
          <summary>Co z Pyszne.pl, Bolt Food, Glovo?</summary>
          <div class="rest-faq-content">
            <p>Specjalizuje sie w reklamie lokalu na miejscu i budowaniu wlasnego kanalu klienta.</p>
          </div>
        </details>
      </div>
    </div>
  </section>

  <section class="rest-final" id="kontakt">
    <div class="rest-container rest-final-inner">
      <h2>Umow bezplatna konsultacje</h2>
      <p class="rest-final-sub">45 minut — online lub w Twoim lokalu. Powiem Ci szczerze, czy warto inwestowac w reklamy.</p>

      <form class="rest-form" method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" data-form="restauracja">
        <input type="hidden" name="action" value="upsellio_submit_lead">
        <?php wp_nonce_field("upsellio_unified_lead_form", "upsellio_unified_lead_nonce"); ?>
        <input type="hidden" name="lead_form_origin" value="restauracja-form">
        <input type="hidden" name="lead_source" value="restauracja-form">
        <input type="hidden" name="lead_service" value="Marketing restauracji">
        <input type="hidden" name="lead_goal" value="Wiecej rezerwacji i zapelnienie stolikow w restauracji">
        <input type="hidden" name="gclid" value="">
        <input type="hidden" name="fbclid" value="">
        <input type="hidden" name="msclkid" value="">
        <input type="hidden" name="utm_source" value="">
        <input type="hidden" name="utm_medium" value="">
        <input type="hidden" name="utm_campaign" value="">
        <input type="hidden" name="utm_term" value="">
        <input type="hidden" name="utm_content" value="">
        <input type="hidden" name="landing_url" value="">
        <input type="hidden" name="referrer" value="">
        <input type="text" name="honeypot_name" class="honeypot" value="">
        <input type="email" name="honeypot_email" class="honeypot" value="">

        <?php
        $status = isset($_GET["lead_status"]) ? sanitize_text_field(wp_unslash($_GET["lead_status"])) : "";
        if ($status === "success") : ?>
          <div class="rest-form-feedback is-success">Dziekuje! Formularz zostal wyslany. Odezwiemy sie najszybciej jak to mozliwe.</div>
        <?php elseif ($status === "error") : ?>
          <div class="rest-form-feedback is-error">Nie udalo sie wyslac formularza. Sprobuj ponownie za chwile.</div>
        <?php endif; ?>

        <div class="rest-form-grid">
          <div class="rest-field">
            <label for="rest_name">Imie</label>
            <input type="text" id="rest_name" name="lead_name" placeholder="Jan" required>
          </div>
          <div class="rest-field">
            <label for="rest_email">Email</label>
            <input type="email" id="rest_email" name="lead_email" placeholder="jan@restauracja.pl" required>
          </div>
          <div class="rest-field rest-field-full">
            <label for="rest_business">Nazwa lokalu i miasto</label>
            <input type="text" id="rest_business" name="lead_company" placeholder="np. Restauracja Bellevue — Wroclaw" required>
          </div>
          <div class="rest-field">
            <label for="rest_size">Liczba miejsc</label>
            <select id="rest_size" name="lead_goal_detail">
              <option value="">— wybierz —</option>
              <option value="<25">ponizej 25 miejsc</option>
              <option value="25-50">25-50 miejsc</option>
              <option value="50-100">50-100 miejsc</option>
              <option value=">100">powyzej 100 miejsc</option>
            </select>
          </div>
          <div class="rest-field">
            <label for="rest_revenue">Miesieczny obrot <span class="opt">orientacyjnie</span></label>
            <select id="rest_revenue" name="lead_budget">
              <option value="">— wybierz —</option>
              <option value="<50k">ponizej 50 000 zl</option>
              <option value="50-100k">50 000 - 100 000 zl</option>
              <option value="100-200k">100 000 - 200 000 zl</option>
              <option value=">200k">powyzej 200 000 zl</option>
            </select>
          </div>
          <div class="rest-field rest-field-full">
            <label for="rest_message">Co Cie najbardziej trapi? <span class="opt">opcjonalnie, ale pomaga</span></label>
            <textarea id="rest_message" name="lead_message" placeholder="np. Weekend pelny, ale wtorki i srody sa tragiczne."></textarea>
          </div>
        </div>

        <div class="rest-field rest-field-full">
          <input type="checkbox" id="rest_consent" name="lead_consent" value="1" required style="margin-right: 8px;">
          <label for="rest_consent" style="display: inline; font-size: 13px;">Zgadzam sie na przetwarzanie danych osobowych w celu kontaktu.</label>
        </div>

        <button type="submit" class="rest-form-submit" data-cta="form-submit">Umow bezplatna konsultacje →</button>

        <p class="rest-form-meta">
          Twoje dane sluza wylacznie do umowienia konsultacji. Nie zapisuje Cie na newsletter i nie sprzedaje bazy.
        </p>
      </form>
    </div>
  </section>
</div>

<?php get_footer(); ?>
