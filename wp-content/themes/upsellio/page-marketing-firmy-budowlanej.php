<?php
/**
 * Template Name: Marketing dla firmy budowlanej — landing
 *
 * Niszowy landing dla branzy budowlanej.
 */
get_header();
?>

<style>
  :root {
    --bud-radius: 20px;
    --bud-radius-lg: 28px;
    --bud-orange: #c2410c;
    --bud-orange-soft: #fff7ed;
    --bud-orange-line: #fed7aa;
    --bud-stone: #57534e;
    --bud-amber: #b45309;
  }

  .bud-page { background: var(--bg, #fafaf6); color: var(--text, #0d0d0b); font-family: var(--font-body, "DM Sans"), system-ui, sans-serif; overflow-x: hidden; }
  .bud-page * { box-sizing: border-box; }
  .bud-container { max-width: 1100px; margin: 0 auto; padding: 0 24px; }

  .bud-hero { position: relative; padding: clamp(60px, 10vw, 100px) 0 clamp(50px, 8vw, 80px); overflow: hidden; }
  .bud-hero::before {
    content: "";
    position: absolute; inset: 0;
    background:
      radial-gradient(ellipse 700px 400px at 80% 20%, rgba(194, 65, 12, 0.07), transparent),
      radial-gradient(ellipse 500px 300px at 10% 80%, rgba(87, 83, 78, 0.05), transparent);
    pointer-events: none; z-index: 0;
  }
  .bud-hero-inner { position: relative; z-index: 1; }
  .bud-hero-grid { display: grid; grid-template-columns: 1.15fr 1fr; gap: 56px; align-items: center; }
  @media (max-width: 900px) { .bud-hero-grid { grid-template-columns: 1fr; gap: 40px; } }

  .bud-pill {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--bud-orange-soft); color: #9a3412;
    padding: 8px 16px; border-radius: 999px;
    font-size: 12px; font-weight: 700; letter-spacing: 0.4px; text-transform: uppercase;
    margin-bottom: 24px;
  }
  .bud-pill::before {
    content: ""; width: 6px; height: 6px; background: var(--bud-orange); border-radius: 50%; animation: budPulse 2s ease-in-out infinite;
  }
  @keyframes budPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(0.85); }
  }

  .bud-hero h1 { font-family: var(--font-display, "Bricolage Grotesque"), serif; font-size: clamp(36px, 5.5vw, 60px); font-weight: 800; line-height: 1.04; letter-spacing: -0.025em; margin: 0 0 24px; color: var(--text); }
  .bud-hero h1 em {
    font-style: normal;
    background: linear-gradient(120deg, var(--bud-orange) 0%, var(--bud-amber) 100%);
    -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; color: transparent;
  }
  .bud-hero-sub { font-size: clamp(16px, 2vw, 19px); line-height: 1.6; color: var(--text-soft, #7a7a72); margin: 0 0 32px; max-width: 540px; }
  .bud-hero-cta-row { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 32px; }
  .bud-cta-primary {
    display: inline-flex; align-items: center; gap: 10px; background: var(--text, #0d0d0b); color: #fff; padding: 18px 32px; border-radius: 14px; font-size: 16px; font-weight: 700; text-decoration: none;
    transition: all 0.2s var(--ease-out, cubic-bezier(0.16, 1, 0.3, 1)); box-shadow: 0 4px 16px rgba(13, 13, 11, 0.15);
  }
  .bud-cta-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(13, 13, 11, 0.2); color: #fff; }
  .bud-cta-primary::after { content: "→"; transition: transform 0.2s ease; }
  .bud-cta-primary:hover::after { transform: translateX(4px); }
  .bud-cta-secondary {
    display: inline-flex; align-items: center; gap: 8px; color: var(--text); padding: 18px 24px; font-size: 15px; font-weight: 600; text-decoration: none;
    border-bottom: 2px solid transparent; transition: all 0.15s ease;
  }
  .bud-cta-secondary:hover { border-bottom-color: var(--bud-orange); color: var(--bud-orange); }
  .bud-trust-row { display: flex; gap: 24px; flex-wrap: wrap; color: var(--text-soft, #7a7a72); font-size: 14px; }
  .bud-trust-item { display: inline-flex; align-items: center; gap: 8px; }
  .bud-trust-item::before { content: "✓"; color: var(--bud-orange); font-weight: 800; font-size: 16px; }

  .bud-dashboard {
    background: #fff; border: 1px solid var(--border, #e8e8e0); border-radius: var(--bud-radius); padding: 24px; box-shadow: var(--shadow, 0 22px 60px rgba(15, 23, 42, 0.1)); position: relative; transform: rotate(-0.5deg);
  }
  .bud-dashboard::before {
    content: "Sezon 2026";
    position: absolute; top: -12px; right: 24px;
    background: linear-gradient(135deg, var(--bud-orange) 0%, var(--bud-amber) 100%);
    color: #fff; padding: 6px 14px; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;
  }
  .bud-dashboard-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--border); }
  .bud-dashboard-name { font-family: var(--font-display); font-size: 13px; font-weight: 700; color: var(--text-muted); }
  .bud-rating { display: flex; align-items: center; gap: 4px; background: #fef3c7; color: #b45309; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; }
  .bud-rating::before { content: "★"; }
  .bud-stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px; }
  .bud-stat { background: var(--bg-alt, #f2f2ec); padding: 12px 14px; border-radius: 10px; }
  .bud-stat-label { font-size: 10px; font-weight: 700; color: var(--text-soft); letter-spacing: 0.4px; text-transform: uppercase; margin-bottom: 3px; }
  .bud-stat-val { font-family: var(--font-display); font-size: 20px; font-weight: 800; color: var(--text); font-variant-numeric: tabular-nums; }
  .bud-stat-trend { font-size: 10px; font-weight: 700; color: var(--success, #15803d); margin-top: 1px; }

  .bud-season-section { background: linear-gradient(135deg, #fff7ed 0%, #fff 100%); border-radius: 10px; padding: 14px 16px; }
  .bud-season-title { font-size: 11px; font-weight: 700; color: var(--text-muted); letter-spacing: 0.4px; text-transform: uppercase; margin-bottom: 12px; }
  .bud-season-row { display: flex; align-items: center; gap: 8px; padding: 4px 0; }
  .bud-season-month { font-family: var(--font-display); font-size: 11px; font-weight: 700; color: var(--text-muted); width: 32px; flex-shrink: 0; text-transform: uppercase; }
  .bud-season-bar { flex: 1; height: 18px; background: var(--bg-alt); border-radius: 4px; overflow: hidden; position: relative; }
  .bud-season-fill {
    height: 100%; background: linear-gradient(90deg, var(--bud-orange) 0%, var(--bud-amber) 100%); border-radius: 4px;
    display: flex; align-items: center; justify-content: flex-end; padding: 0 6px; color: #fff; font-size: 9.5px; font-weight: 700;
  }
  .bud-season-fill.is-low { background: linear-gradient(90deg, var(--text-soft, #a8a29e) 0%, #d6d3d1 100%); }
  .bud-season-pct { flex-shrink: 0; width: 30px; text-align: right; font-size: 10px; font-weight: 700; color: var(--text-muted); font-variant-numeric: tabular-nums; }
  .bud-bottom-msg { background: linear-gradient(90deg, var(--success, #15803d) 0%, #22c55e 100%); color: #fff; padding: 10px 14px; border-radius: 8px; font-size: 12px; font-weight: 700; text-align: center; margin-top: 12px; }

  .bud-section { padding: clamp(60px, 8vw, 100px) 0; position: relative; }
  .bud-section-soft { background: var(--surface, #fff); }
  .bud-section-head { max-width: 720px; margin: 0 0 56px; }
  .bud-section-head.is-center { margin: 0 auto 56px; text-align: center; }
  .bud-section-eyebrow { font-size: 12px; font-weight: 700; color: #9a3412; letter-spacing: 0.8px; text-transform: uppercase; margin-bottom: 12px; display: inline-block; }
  .bud-section h2 { font-family: var(--font-display); font-size: clamp(28px, 3.8vw, 42px); font-weight: 800; line-height: 1.15; letter-spacing: -0.018em; margin: 0 0 16px; color: var(--text); }
  .bud-section-intro { font-size: clamp(16px, 1.6vw, 18px); line-height: 1.65; color: var(--text-soft); margin: 0; }

  .bud-problems { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin-top: 16px; }
  @media (max-width: 800px) { .bud-problems { grid-template-columns: 1fr; } }
  .bud-problem { background: var(--surface); border: 1px solid var(--border); border-radius: var(--bud-radius); padding: 28px; transition: all 0.25s var(--ease-out); }
  .bud-problem:hover { transform: translateY(-3px); border-color: var(--bud-orange); box-shadow: var(--shadow-soft); }
  .bud-problem-quote { font-family: var(--font-display); font-size: 22px; font-weight: 700; line-height: 1.3; color: var(--text); margin: 0 0 14px; position: relative; padding-top: 24px; }
  .bud-problem-quote::before { content: '"'; position: absolute; top: -10px; left: -8px; font-size: 60px; color: var(--bud-orange); line-height: 1; font-family: serif; opacity: 0.4; }
  .bud-problem p { font-size: 14px; line-height: 1.6; color: var(--text-soft); margin: 0; }

  .bud-specs { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 16px; }
  @media (max-width: 800px) { .bud-specs { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 500px) { .bud-specs { grid-template-columns: 1fr; } }
  .bud-spec { background: var(--surface); border: 1px solid var(--border); border-radius: var(--bud-radius); padding: 24px; transition: all 0.25s var(--ease-out); text-align: center; }
  .bud-spec:hover { border-color: var(--bud-orange); transform: translateY(-3px); box-shadow: var(--shadow-soft); }
  .bud-spec-icon { width: 52px; height: 52px; margin: 0 auto 14px; border-radius: 14px; background: var(--bud-orange-soft); color: var(--bud-orange); display: grid; place-items: center; font-size: 24px; }
  .bud-spec h4 { font-family: var(--font-display); font-size: 16px; font-weight: 700; margin: 0 0 6px; }
  .bud-spec-value { font-family: var(--font-display); font-size: 13px; color: var(--bud-amber); font-weight: 700; margin-bottom: 8px; }
  .bud-spec p { font-size: 12.5px; line-height: 1.5; color: var(--text-soft); margin: 0; }

  .bud-targets-wrap { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 16px; }
  @media (max-width: 800px) { .bud-targets-wrap { grid-template-columns: 1fr; } }
  .bud-target-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--bud-radius-lg); padding: 32px; position: relative; transition: all 0.25s var(--ease-out); }
  .bud-target-card.is-b2c { background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border-color: var(--bud-orange-line); }
  .bud-target-card.is-b2b { background: linear-gradient(135deg, #fafaf9 0%, #f5f5f4 100%); border-color: #d6d3d1; }
  .bud-target-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-soft); }
  .bud-target-tag { display: inline-block; padding: 6px 14px; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 16px; }
  .bud-target-card.is-b2c .bud-target-tag { background: var(--bud-orange); color: #fff; }
  .bud-target-card.is-b2b .bud-target-tag { background: var(--bud-stone); color: #fff; }
  .bud-target-card h3 { font-family: var(--font-display); font-size: 24px; font-weight: 800; line-height: 1.2; margin: 0 0 12px; }
  .bud-target-card-desc { font-size: 15px; line-height: 1.6; color: var(--text-muted); margin: 0 0 20px; }
  .bud-target-features { margin: 0; padding: 0; list-style: none; }
  .bud-target-features li { padding: 8px 0 8px 24px; position: relative; font-size: 14px; line-height: 1.5; color: var(--text); }
  .bud-target-features li::before { content: "→"; position: absolute; left: 0; color: var(--bud-orange); font-weight: 800; }
  .bud-target-card.is-b2b .bud-target-features li::before { color: var(--bud-stone); }

  .bud-package-wrap { display: grid; grid-template-columns: 1.2fr 1fr; gap: 24px; margin-top: 16px; }
  @media (max-width: 800px) { .bud-package-wrap { grid-template-columns: 1fr; } }
  .bud-package { background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border: 1px solid var(--bud-orange-line); border-radius: var(--bud-radius-lg); padding: clamp(28px, 4vw, 44px); position: relative; }
  .bud-package-tag { display: inline-block; background: var(--bud-orange); color: #fff; padding: 6px 14px; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 16px; }
  .bud-package h3 { font-family: var(--font-display); font-size: clamp(22px, 2.5vw, 30px); font-weight: 800; line-height: 1.2; margin: 0 0 12px; color: var(--text); }
  .bud-package-desc { font-size: 15px; line-height: 1.6; color: var(--text-muted); margin: 0 0 28px; }
  .bud-package-list { margin: 0; padding: 0; list-style: none; }
  .bud-package-list li { padding: 10px 0 10px 32px; position: relative; font-size: 15px; line-height: 1.5; color: var(--text); border-bottom: 1px dashed rgba(194, 65, 12, 0.15); }
  .bud-package-list li:last-child { border-bottom: 0; }
  .bud-package-list li::before { content: "✓"; position: absolute; left: 0; top: 8px; width: 22px; height: 22px; background: var(--bud-orange); color: #fff; border-radius: 50%; display: grid; place-items: center; font-size: 12px; font-weight: 800; }
  .bud-pricing { background: var(--surface); border: 2px solid var(--text); border-radius: var(--bud-radius-lg); padding: clamp(28px, 4vw, 36px); text-align: center; position: sticky; top: 24px; }
  .bud-pricing-label { font-size: 11px; font-weight: 700; color: var(--text-soft); letter-spacing: 0.6px; text-transform: uppercase; margin-bottom: 8px; }
  .bud-pricing-amount { font-family: var(--font-display); font-size: clamp(36px, 5vw, 52px); font-weight: 800; line-height: 1; color: var(--text); margin-bottom: 4px; }
  .bud-pricing-amount small { font-size: 16px; font-weight: 600; color: var(--text-soft); margin-left: 4px; }
  .bud-pricing-period { font-size: 13px; color: var(--text-soft); margin-bottom: 24px; }
  .bud-pricing-extras { margin: 24px 0 0; padding: 20px 0 0; border-top: 1px solid var(--border); text-align: left; list-style: none; }
  .bud-pricing-extras li { padding: 6px 0 6px 24px; position: relative; font-size: 13px; color: var(--text-muted); line-height: 1.5; }
  .bud-pricing-extras li::before { content: "+"; position: absolute; left: 0; color: var(--bud-orange); font-weight: 800; }
  .bud-pricing-cta { display: block; width: 100%; padding: 16px; background: var(--text); color: #fff; border-radius: 12px; font-weight: 700; font-size: 15px; text-decoration: none; transition: all 0.2s ease; margin-top: 24px; }
  .bud-pricing-cta:hover { background: var(--bud-orange); color: #fff; transform: translateY(-1px); }

  .bud-honest { background: linear-gradient(135deg, var(--accent-soft, #fff7ed) 0%, #fff 100%); border: 1px solid var(--accent-line, #fed7aa); border-radius: var(--bud-radius); padding: clamp(28px, 4vw, 44px); margin-top: 32px; }
  .bud-honest-header { display: flex; gap: 16px; align-items: center; margin-bottom: 20px; }
  .bud-honest-icon { flex-shrink: 0; width: 44px; height: 44px; border-radius: 12px; background: var(--accent, #f97316); color: #fff; display: grid; place-items: center; font-size: 22px; font-weight: 800; }
  .bud-honest-header strong { font-family: var(--font-display); font-size: 18px; font-weight: 700; }
  .bud-honest ul { margin: 0; padding: 0; list-style: none; display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px 24px; }
  @media (max-width: 600px) { .bud-honest ul { grid-template-columns: 1fr; } }
  .bud-honest li { padding: 10px 0 10px 28px; position: relative; font-size: 15px; line-height: 1.5; color: var(--text-muted); }
  .bud-honest li::before { content: "✗"; position: absolute; left: 0; top: 8px; color: var(--accent); font-weight: 800; font-size: 18px; }

  .bud-steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: 16px; }
  @media (max-width: 800px) { .bud-steps { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 500px) { .bud-steps { grid-template-columns: 1fr; } }
  .bud-step { background: var(--surface, #fff); border: 1px solid var(--border); border-radius: var(--bud-radius); padding: 24px; position: relative; transition: all 0.25s var(--ease-out); }
  .bud-step:hover { transform: translateY(-3px); border-color: var(--bud-orange); box-shadow: var(--shadow-soft); }
  .bud-step-num { font-family: var(--font-display); font-size: 32px; font-weight: 800; line-height: 1; color: var(--bud-orange); margin-bottom: 16px; font-variant-numeric: tabular-nums; }
  .bud-step h4 { font-family: var(--font-display); font-size: 16px; font-weight: 700; line-height: 1.3; margin: 0 0 8px; }
  .bud-step p { font-size: 13px; line-height: 1.6; color: var(--text-soft); margin: 0; }
  .bud-step-time { display: inline-block; margin-top: 12px; font-size: 11px; font-weight: 700; color: var(--text-soft); letter-spacing: 0.5px; text-transform: uppercase; padding: 4px 8px; background: var(--bg-alt, #f2f2ec); border-radius: 6px; }

  .bud-example-wrap { background: linear-gradient(135deg, #1c1917 0%, #292524 100%); color: #fff; border-radius: var(--bud-radius-lg); padding: clamp(36px, 5vw, 56px); margin-top: 16px; position: relative; overflow: hidden; }
  .bud-example-wrap::before { content: ""; position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(194, 65, 12, 0.25) 0%, transparent 60%); pointer-events: none; }
  .bud-example-wrap > * { position: relative; }
  .bud-example-eyebrow { font-size: 11px; font-weight: 700; color: #fdba74; letter-spacing: 0.8px; text-transform: uppercase; margin-bottom: 14px; }
  .bud-example-wrap h3 { font-family: var(--font-display); font-size: clamp(22px, 2.5vw, 30px); font-weight: 800; line-height: 1.25; margin: 0 0 12px; color: #fff; }
  .bud-example-context { color: rgba(255, 255, 255, 0.7); font-size: 15px; line-height: 1.6; margin: 0 0 32px; max-width: 600px; }
  .bud-comparison { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 32px; }
  @media (max-width: 700px) { .bud-comparison { grid-template-columns: 1fr; } }
  .bud-state { background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.1); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border-radius: 16px; padding: 24px; }
  .bud-state.is-after { background: rgba(194, 65, 12, 0.18); border-color: rgba(253, 186, 116, 0.35); }
  .bud-state-label { display: inline-block; font-size: 10px; font-weight: 700; letter-spacing: 0.6px; text-transform: uppercase; background: rgba(255, 255, 255, 0.1); color: #fff; padding: 4px 8px; border-radius: 6px; margin-bottom: 14px; }
  .bud-state.is-after .bud-state-label { background: var(--bud-orange); }
  .bud-state h4 { font-family: var(--font-display); font-size: 15px; font-weight: 700; margin: 0 0 14px; color: #fff; }
  .bud-state-metric { padding: 10px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.1); display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: rgba(255, 255, 255, 0.85); }
  .bud-state-metric:last-child { border-bottom: 0; }
  .bud-state-metric strong { font-family: var(--font-display); font-variant-numeric: tabular-nums; font-weight: 800; font-size: 15px; }
  .bud-state.is-after .bud-state-metric strong { color: #fdba74; }
  .bud-example-summary { border-top: 1px solid rgba(255, 255, 255, 0.12); padding-top: 28px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
  .bud-example-summary-text { color: rgba(255, 255, 255, 0.85); font-size: 15px; line-height: 1.5; max-width: 520px; }
  .bud-example-summary-amount { font-family: var(--font-display); font-size: clamp(28px, 3.5vw, 38px); font-weight: 800; color: #fff; text-align: right; }
  .bud-example-summary-amount small { display: block; font-size: 12px; font-weight: 600; color: rgba(255, 255, 255, 0.6); letter-spacing: 0.4px; text-transform: uppercase; margin-top: 4px; }

  .bud-target { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
  @media (max-width: 700px) { .bud-target { grid-template-columns: 1fr; } }
  .bud-target-col { padding: 28px; border-radius: var(--bud-radius); border: 1px solid var(--border); }
  .bud-target-col.is-yes { background: var(--bud-orange-soft); border-color: rgba(194, 65, 12, 0.2); }
  .bud-target-col.is-no { background: var(--bg-alt, #f2f2ec); }
  .bud-target-col h4 { font-family: var(--font-display); font-size: 17px; font-weight: 700; margin: 0 0 16px; display: flex; align-items: center; gap: 8px; }
  .bud-target-col h4::before { width: 24px; height: 24px; border-radius: 50%; display: grid; place-items: center; font-size: 14px; font-weight: 800; flex-shrink: 0; }
  .bud-target-col.is-yes h4::before { content: "✓"; background: var(--bud-orange); color: #fff; }
  .bud-target-col.is-no h4::before { content: "—"; background: var(--text-soft); color: #fff; }
  .bud-target-col ul { margin: 0; padding: 0; list-style: none; }
  .bud-target-col li { padding: 8px 0; font-size: 14px; line-height: 1.55; color: var(--text-muted); }

  .bud-faq-list { max-width: 760px; margin: 0 auto; }
  .bud-faq { background: var(--surface); border: 1px solid var(--border); border-radius: var(--bud-radius); margin-bottom: 12px; overflow: hidden; transition: border-color 0.2s ease; }
  .bud-faq:hover { border-color: var(--bud-orange); }
  .bud-faq[open] { border-color: var(--bud-orange); box-shadow: var(--shadow-sm); }
  .bud-faq summary { padding: 22px 28px; cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center; gap: 16px; font-family: var(--font-display); font-size: 16px; font-weight: 700; line-height: 1.4; color: var(--text); user-select: none; }
  .bud-faq summary::-webkit-details-marker { display: none; }
  .bud-faq summary::after { content: "+"; flex-shrink: 0; width: 28px; height: 28px; border-radius: 50%; background: var(--bud-orange-soft); color: var(--bud-orange); display: grid; place-items: center; font-size: 18px; font-weight: 400; transition: all 0.2s ease; }
  .bud-faq[open] summary::after { content: "−"; background: var(--bud-orange); color: #fff; transform: rotate(180deg); }
  .bud-faq-content { padding: 0 28px 24px; color: var(--text-soft); font-size: 15px; line-height: 1.7; }
  .bud-faq-content p { margin: 0 0 12px; }
  .bud-faq-content p:last-child { margin: 0; }

  .bud-author { background: var(--sand, #f5f0e8); border-radius: var(--bud-radius-lg); padding: clamp(36px, 5vw, 56px); display: grid; grid-template-columns: auto 1fr; gap: 32px; align-items: center; margin-top: 16px; }
  @media (max-width: 700px) { .bud-author { grid-template-columns: 1fr; text-align: center; } }
  .bud-author-photo { width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, var(--bud-orange) 0%, #9a3412 100%); color: #fff; display: grid; place-items: center; font-family: var(--font-display); font-size: 42px; font-weight: 800; flex-shrink: 0; margin: 0 auto; }
  .bud-author-content h3 { font-family: var(--font-display); font-size: 22px; font-weight: 800; margin: 0 0 8px; }
  .bud-author-role { font-size: 14px; font-weight: 600; color: #9a3412; margin: 0 0 16px; }
  .bud-author-content p { font-size: 15px; line-height: 1.65; color: var(--text-muted); margin: 0; }

  .bud-final { background: linear-gradient(180deg, var(--text, #0d0d0b) 0%, #1c1917 100%); color: #fff; padding: clamp(60px, 9vw, 96px) 0; position: relative; overflow: hidden; }
  .bud-final::before { content: ""; position: absolute; top: -100px; left: 50%; transform: translateX(-50%); width: 600px; height: 600px; background: radial-gradient(circle, rgba(194, 65, 12, 0.25) 0%, transparent 70%); pointer-events: none; }
  .bud-final-inner { position: relative; max-width: 640px; margin: 0 auto; text-align: center; }
  .bud-final h2 { color: #fff; margin-bottom: 16px; }
  .bud-final-sub { font-size: 17px; line-height: 1.6; color: rgba(255, 255, 255, 0.7); margin: 0 0 40px; }
  .bud-form { background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.1); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border-radius: var(--bud-radius); padding: clamp(24px, 4vw, 36px); text-align: left; }
  .bud-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  @media (max-width: 600px) { .bud-form-grid { grid-template-columns: 1fr; } }
  .bud-field { margin-bottom: 16px; }
  .bud-field-full { grid-column: 1 / -1; }
  .bud-field label { display: block; font-size: 13px; font-weight: 600; color: rgba(255, 255, 255, 0.85); margin-bottom: 8px; }
  .bud-field label .opt { font-weight: 400; color: rgba(255, 255, 255, 0.4); font-size: 12px; margin-left: 6px; }
  .bud-field input,
  .bud-field textarea,
  .bud-field select {
    width: 100%; padding: 14px 16px; background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.15); color: #fff; border-radius: 12px; font-size: 15px; font-family: inherit; transition: all 0.2s ease;
  }
  .bud-field input::placeholder,
  .bud-field textarea::placeholder { color: rgba(255, 255, 255, 0.35); }
  .bud-field input:focus,
  .bud-field textarea:focus,
  .bud-field select:focus { outline: none; border-color: var(--bud-orange); background: rgba(255, 255, 255, 0.1); }
  .bud-field textarea { min-height: 90px; resize: vertical; }
  .bud-form-submit { width: 100%; padding: 18px; background: var(--bud-orange); color: #fff; border: 0; border-radius: 14px; font-weight: 700; font-size: 16px; cursor: pointer; transition: all 0.2s ease; margin-top: 8px; font-family: inherit; }
  .bud-form-submit:hover { background: #9a3412; transform: translateY(-2px); box-shadow: 0 12px 24px rgba(194, 65, 12, 0.3); }
  .bud-form-meta { text-align: center; margin-top: 20px; font-size: 12px; color: rgba(255, 255, 255, 0.4); line-height: 1.5; }
  .bud-form-feedback { margin-bottom: 20px; padding: 12px 14px; border-radius: 10px; font-size: 14px; }
  .bud-form-feedback.is-success { background: rgba(22, 163, 74, 0.18); border: 1px solid rgba(34, 197, 94, 0.45); color: #dcfce7; }
  .bud-form-feedback.is-error { background: rgba(220, 38, 38, 0.18); border: 1px solid rgba(248, 113, 113, 0.45); color: #fee2e2; }
</style>

<div class="bud-page">
  <section class="bud-hero">
    <div class="bud-container bud-hero-inner">
      <div class="bud-hero-grid">
        <div>
          <div class="bud-pill">Marketing dla firm budowlanych</div>
          <h1>Pelen grafik prac <em>od marca do listopada</em></h1>
          <p class="bud-hero-sub">
            Reklama Google + Meta + nowa strona, ktore przyciagaja powaznych inwestorow na remonty, budowy i wykonczenia.
          </p>
          <div class="bud-hero-cta-row">
            <a href="#kontakt" class="bud-cta-primary" data-cta="hero-umow-rozmowe">Umow rozmowe</a>
            <a href="#oferta" class="bud-cta-secondary" data-cta="hero-zobacz-pakiet">Zobacz pakiet</a>
          </div>
          <div class="bud-trust-row">
            <span class="bud-trust-item">Pierwsi inwestorzy w 30 dni</span>
            <span class="bud-trust-item">Bez umow na rok</span>
            <span class="bud-trust-item">Pierwsza konsultacja gratis</span>
          </div>
        </div>
        <div>
          <div class="bud-dashboard" aria-hidden="true">
            <div class="bud-dashboard-header">
              <span class="bud-dashboard-name">BUDREX — Wroclaw i okolice</span>
              <span class="bud-rating">4.9 (84)</span>
            </div>
            <div class="bud-stats-grid">
              <div class="bud-stat"><div class="bud-stat-label">Zlecenia / sezon</div><div class="bud-stat-val">38</div><div class="bud-stat-trend">+217%</div></div>
              <div class="bud-stat"><div class="bud-stat-label">Wartosc portfela</div><div class="bud-stat-val">2.4M zl</div><div class="bud-stat-trend">+184%</div></div>
            </div>
            <div class="bud-season-section">
              <div class="bud-season-title">Wykorzystanie ekipy w sezonie</div>
              <div class="bud-season-row"><span class="bud-season-month">MAR</span><div class="bud-season-bar"><div class="bud-season-fill" style="width: 88%;">88%</div></div><span class="bud-season-pct">88%</span></div>
              <div class="bud-season-row"><span class="bud-season-month">KWI</span><div class="bud-season-bar"><div class="bud-season-fill" style="width: 100%;">100%</div></div><span class="bud-season-pct">100%</span></div>
              <div class="bud-season-row"><span class="bud-season-month">MAJ</span><div class="bud-season-bar"><div class="bud-season-fill" style="width: 100%;">100%</div></div><span class="bud-season-pct">100%</span></div>
              <div class="bud-season-row"><span class="bud-season-month">CZE</span><div class="bud-season-bar"><div class="bud-season-fill" style="width: 100%;">100%</div></div><span class="bud-season-pct">100%</span></div>
              <div class="bud-season-row"><span class="bud-season-month">LIS</span><div class="bud-season-bar"><div class="bud-season-fill is-low" style="width: 65%;">65%</div></div><span class="bud-season-pct">65%</span></div>
              <div class="bud-season-row"><span class="bud-season-month">GRU</span><div class="bud-season-bar"><div class="bud-season-fill is-low" style="width: 42%;">42%</div></div><span class="bud-season-pct">42%</span></div>
            </div>
            <div class="bud-bottom-msg">Zarezerwowane do konca wrzesnia</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="bud-section bud-section-soft">
    <div class="bud-container">
      <div class="bud-section-head">
        <span class="bud-section-eyebrow">Jesli ktore brzmi znajomo</span>
        <h2>Trzy problemy, ktore slysze od firm budowlanych</h2>
      </div>
      <div class="bud-problems">
        <div class="bud-problem"><p class="bud-problem-quote">Maj-wrzesien przegrzanie, listopad-luty cisza.</p><p>Sezonowosc utrudnia utrzymanie zespolu i planowanie rozwoju.</p></div>
        <div class="bud-problem"><p class="bud-problem-quote">Klienci prosza o wycene i znikaja.</p><p>Darmowe pomiary i wyceny kosztuja czas i pieniadze bez zwrotu.</p></div>
        <div class="bud-problem"><p class="bud-problem-quote">Mam strone, ale nikt nie wchodzi.</p><p>Bez portfolio i konkretow trudno zbudowac zaufanie inwestora.</p></div>
      </div>
    </div>
  </section>

  <section class="bud-section">
    <div class="bud-container">
      <div class="bud-section-head">
        <span class="bud-section-eyebrow">Klucz do wzrostu</span>
        <h2>Specjalizacja > "robimy wszystko"</h2>
      </div>
      <div class="bud-specs">
        <div class="bud-spec"><div class="bud-spec-icon">🏗️</div><h4>Budowa domow pod klucz</h4><div class="bud-spec-value">400 000 - 1 500 000 zl</div><p>Najwyzsza wartosc projektu i dlugi cykl decyzji.</p></div>
        <div class="bud-spec"><div class="bud-spec-icon">🔨</div><h4>Wykonczenia mieszkan</h4><div class="bud-spec-value">40 000 - 250 000 zl</div><p>Najczestszy projekt B2C z wysoka wartoscia.</p></div>
        <div class="bud-spec"><div class="bud-spec-icon">🏠</div><h4>Remonty kapitalne</h4><div class="bud-spec-value">30 000 - 180 000 zl</div><p>Wysoka marza i stale zapotrzebowanie.</p></div>
        <div class="bud-spec"><div class="bud-spec-icon">🧱</div><h4>Dachy i pokrycia</h4><div class="bud-spec-value">25 000 - 90 000 zl</div><p>Waska specjalizacja i silna intencja zakupowa.</p></div>
        <div class="bud-spec"><div class="bud-spec-icon">🎨</div><h4>Elewacje i ocieplenia</h4><div class="bud-spec-value">15 000 - 80 000 zl</div><p>Duze zainteresowanie i sezonowosc.</p></div>
        <div class="bud-spec"><div class="bud-spec-icon">⚡</div><h4>Instalacje</h4><div class="bud-spec-value">8 000 - 40 000 zl</div><p>Niski prog wejscia, liczy sie szybkosc reakcji.</p></div>
      </div>
    </div>
  </section>

  <section class="bud-section bud-section-soft">
    <div class="bud-container">
      <div class="bud-section-head">
        <span class="bud-section-eyebrow">Dwa rynki = dwie strategie</span>
        <h2>Inwestor indywidualny i deweloper to dwa rozne biznesy</h2>
      </div>
      <div class="bud-targets-wrap">
        <div class="bud-target-card is-b2c">
          <span class="bud-target-tag">B2C</span>
          <h3>Klienci na konkretne projekty</h3>
          <p class="bud-target-card-desc">Inwestor indywidualny kupuje rzadko, ale projekt jest duzy i wymaga wysokiego zaufania.</p>
          <ul class="bud-target-features">
            <li>Google Ads na uslugi specjalistyczne</li>
            <li>Strona z portfolio realizacji</li>
            <li>Kalkulator orientacyjnej wyceny</li>
            <li>Meta Ads w geo-targetingu</li>
          </ul>
        </div>
        <div class="bud-target-card is-b2b">
          <span class="bud-target-tag">B2B</span>
          <h3>Stale kontrakty podwykonawcze</h3>
          <p class="bud-target-card-desc">Relacje z deweloperami i GW buduja stabilny pipeline na wiele miesiecy.</p>
          <ul class="bud-target-features">
            <li>LinkedIn Ads do decyzyjnych</li>
            <li>Landing dla podwykonawcow</li>
            <li>Cold mailing B2B (RODO-compliant)</li>
            <li>Portfolio referencyjne projektow</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section class="bud-section" id="oferta">
    <div class="bud-container">
      <div class="bud-section-head">
        <span class="bud-section-eyebrow">Co konkretnie robie</span>
        <h2>Pakiet "Wypelniony sezon"</h2>
      </div>
      <div class="bud-package-wrap">
        <div class="bud-package">
          <span class="bud-package-tag">Pakiet startowy</span>
          <h3>Pelen sezon zlecen bez przerwy miedzy marcem a pazdziernikiem</h3>
          <p class="bud-package-desc">Strona + kampanie + filtracja leadow, z planem pod sezonowosc i realna ekonomie firmy budowlanej.</p>
          <ul class="bud-package-list">
            <li>Nowa strona lub redesign z sekcjami specjalizacji</li>
            <li>Galeria realizacji (sesja foto w pierwszym miesiacu)</li>
            <li>Google Ads na frazy konkretne i intencyjne</li>
            <li>Meta Ads + retargeting</li>
            <li>Kalkulator wyceny i formularz briefu</li>
            <li>Optymalizacja Google Maps i opinii</li>
            <li>Email nurturing dla klientow z dlugim cyklem</li>
            <li>Opcjonalnie B2B: LinkedIn Ads i outbound</li>
            <li>Cotygodniowa optymalizacja + raport miesieczny</li>
          </ul>
        </div>
        <div class="bud-pricing">
          <div class="bud-pricing-label">Miesieczna oplata</div>
          <div class="bud-pricing-amount">2 600<small>zl</small></div>
          <div class="bud-pricing-period">+ budzet reklamowy od 1 500 zl/mies</div>
          <ul class="bud-pricing-extras">
            <li>Pierwsza konsultacja gratis (60 min)</li>
            <li>Bez umow na 12 miesiecy</li>
            <li>Sesja foto realizacji w pierwszym miesiacu</li>
            <li>Jedno zlecenie zwykle pokrywa koszt wielu miesiecy</li>
          </ul>
          <a href="#kontakt" class="bud-pricing-cta" data-cta="oferta-umow-konsultacje">Umow konsultacje →</a>
        </div>
      </div>
    </div>
  </section>

  <section class="bud-section bud-section-soft">
    <div class="bud-container">
      <div class="bud-section-head">
        <span class="bud-section-eyebrow">Szczerze</span>
        <h2>Czego NIE dostaniesz (i dlaczego dobrze)</h2>
      </div>
      <div class="bud-honest">
        <div class="bud-honest-header"><div class="bud-honest-icon">!</div><strong>W pakiecie nie znajdziesz:</strong></div>
        <ul>
          <li>Obietnic "100 wycen miesiecznie"</li>
          <li>Reklam na OLX i Marketplace</li>
          <li>Wojny cenowej z najtanszymi ekipami</li>
          <li>Pustych leadow bez intencji zakupu</li>
          <li>Pomijania sezonowosci w planie kampanii</li>
          <li>Dzialan bez pomiaru jakosci leadow</li>
        </ul>
      </div>
    </div>
  </section>

  <section class="bud-section">
    <div class="bud-container">
      <div class="bud-section-head">
        <span class="bud-section-eyebrow">Jak zaczynamy</span>
        <h2>Cztery kroki od rozmowy do pierwszych zlecen</h2>
      </div>
      <div class="bud-steps">
        <div class="bud-step"><div class="bud-step-num">01</div><h4>Bezplatna konsultacja</h4><p>Ustalamy specjalizacje, sezonowosc i cele przychodowe.</p><span class="bud-step-time">Tydzien 1</span></div>
        <div class="bud-step"><div class="bud-step-num">02</div><h4>Sesja foto + strona</h4><p>Budujemy portfolio i strone pod wiarygodnosc.</p><span class="bud-step-time">Tygodnie 2-4</span></div>
        <div class="bud-step"><div class="bud-step-num">03</div><h4>Start kampanii</h4><p>Google Ads + Meta Ads pod konkretne uslugi.</p><span class="bud-step-time">Miesiac 1-2</span></div>
        <div class="bud-step"><div class="bud-step-num">04</div><h4>Optymalizacja sezonu</h4><p>Dostosowujemy intensywnosc do marzec-pazdziernik i listopad-luty.</p><span class="bud-step-time">Cyklicznie</span></div>
      </div>
    </div>
  </section>

  <section class="bud-section bud-section-soft">
    <div class="bud-container">
      <div class="bud-section-head">
        <span class="bud-section-eyebrow">Konkret zamiast obietnic</span>
        <h2>Sezon 2025 firmy z Wroclawia</h2>
      </div>
      <div class="bud-example-wrap">
        <div class="bud-example-eyebrow">BUDREX — Wroclaw i okolice</div>
        <h3>Z 12 zlecen w sezonie do 38</h3>
        <p class="bud-example-context">Portfolio + strona + kampanie specjalistyczne zwiekszyly liczbe i wartosc zlecen przy tej samej ekipie.</p>
        <div class="bud-comparison">
          <div class="bud-state">
            <span class="bud-state-label">Sezon 2024</span>
            <h4>Przed startem</h4>
            <div class="bud-state-metric"><span>Zlecenia / sezon</span><strong>12</strong></div>
            <div class="bud-state-metric"><span>Srednia wartosc</span><strong>~80 000 zl</strong></div>
            <div class="bud-state-metric"><span>Obrot sezonu</span><strong>~960 000 zl</strong></div>
            <div class="bud-state-metric"><span>Listopad-luty</span><strong>2-3 zlecenia</strong></div>
          </div>
          <div class="bud-state is-after">
            <span class="bud-state-label">Sezon 2025</span>
            <h4>Po wspolpracy</h4>
            <div class="bud-state-metric"><span>Zlecenia / sezon</span><strong>38</strong></div>
            <div class="bud-state-metric"><span>Srednia wartosc</span><strong>~94 000 zl</strong></div>
            <div class="bud-state-metric"><span>Obrot sezonu</span><strong>~3 570 000 zl</strong></div>
            <div class="bud-state-metric"><span>Listopad-luty</span><strong>14 zlecen</strong></div>
          </div>
        </div>
        <div class="bud-example-summary">
          <div class="bud-example-summary-text">Najwieksza zmiana: przewidywalny pipeline i zlecenia rowniez poza szczytem sezonu.</div>
          <div class="bud-example-summary-amount">+272%<small>wzrost obrotu sezonu</small></div>
        </div>
      </div>
    </div>
  </section>

  <section class="bud-section">
    <div class="bud-container">
      <div class="bud-section-head">
        <span class="bud-section-eyebrow">Dla kogo</span>
        <h2>Dla kogo to ma sens</h2>
      </div>
      <div class="bud-target">
        <div class="bud-target-col is-yes">
          <h4>Pakiet ma sens jesli:</h4>
          <ul>
            <li>→ Firma dziala min. 3 lata</li>
            <li>→ Masz min. 4 osoby w ekipie</li>
            <li>→ Masz realizacje do pokazania</li>
            <li>→ Przychod sezonu min. 800 000 zl</li>
            <li>→ Stac Cie na 2 600 zl + budzet reklamowy</li>
          </ul>
        </div>
        <div class="bud-target-col is-no">
          <h4>Pakiet NIE ma sensu jesli:</h4>
          <ul>
            <li>→ Pracujesz solo i nie chcesz rosnac</li>
            <li>→ Oferta to "robimy wszystko"</li>
            <li>→ Opinie ponizej 4.0</li>
            <li>→ Konkurujesz glownie cena</li>
            <li>→ Brak portfolio realizacji</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section class="bud-section bud-section-soft">
    <div class="bud-container">
      <div class="bud-section-head">
        <span class="bud-section-eyebrow">Kto bedzie z Toba pracowal</span>
        <h2>Po drugiej stronie ekranu</h2>
      </div>
      <div class="bud-author">
        <div class="bud-author-photo">SK</div>
        <div class="bud-author-content">
          <h3>Sebastian Kelm</h3>
          <p class="bud-author-role">Konsultant marketingu — Google Ads · Meta Ads · LinkedIn Ads · landing pages</p>
          <p>Pracuje bezposrednio z klientami i buduje marketing pod jakosc zlecen, nie pod ilosc pustych leadow.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="bud-section">
    <div class="bud-container">
      <div class="bud-section-head is-center">
        <span class="bud-section-eyebrow">FAQ</span>
        <h2>Pytania, ktore dostaje najczesciej</h2>
      </div>
      <div class="bud-faq-list">
        <details class="bud-faq">
          <summary>Ile to kosztuje miesiecznie razem z reklamami?</summary>
          <div class="bud-faq-content"><p>Pakiet: <strong>2 600 zl netto/mies</strong> + budzet reklamowy od 1 500 zl.</p></div>
        </details>
        <details class="bud-faq">
          <summary>Po jakim czasie zobacze pierwsze zlecenia?</summary>
          <div class="bud-faq-content"><p>Pierwsze formularze zwykle 14-21 dni, pierwsze umowy zwykle 45-90 dni.</p></div>
        </details>
        <details class="bud-faq">
          <summary>Jak walczycie z klientami "tylko podaj cene"?</summary>
          <div class="bud-faq-content"><p>Kalkulator orientacyjnej wyceny + brief projektu + kwalifikacja leadow przed kontaktem.</p></div>
        </details>
      </div>
    </div>
  </section>

  <section class="bud-final" id="kontakt">
    <div class="bud-container bud-final-inner">
      <h2>Umow bezplatna konsultacje</h2>
      <p class="bud-final-sub">60 minut online lub w siedzibie firmy. Oceniam potencjal wzrostu, sezonowosc i to, jak zbudowac pipeline zlecen.</p>

      <form class="bud-form" method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" data-form="firma-budowlana">
        <input type="hidden" name="action" value="upsellio_submit_lead">
        <?php wp_nonce_field("upsellio_unified_lead_form", "upsellio_unified_lead_nonce"); ?>
        <input type="hidden" name="lead_form_origin" value="firma-budowlana-form">
        <input type="hidden" name="lead_source" value="firma-budowlana-form">
        <input type="hidden" name="lead_service" value="Marketing firmy budowlanej">
        <input type="hidden" name="lead_goal" value="Pozyskanie zlecen B2C/B2B i stabilnego pipeline sezonowego dla firmy budowlanej">
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
          <div class="bud-form-feedback is-success">Dziekuje! Formularz zostal wyslany. Odezwiemy sie najszybciej jak to mozliwe.</div>
        <?php elseif ($status === "error") : ?>
          <div class="bud-form-feedback is-error">Nie udalo sie wyslac formularza. Sprobuj ponownie za chwile.</div>
        <?php endif; ?>

        <div class="bud-form-grid">
          <div class="bud-field">
            <label for="bud_name">Imie</label>
            <input type="text" id="bud_name" name="lead_name" placeholder="Jan" required>
          </div>
          <div class="bud-field">
            <label for="bud_email">Email</label>
            <input type="email" id="bud_email" name="lead_email" placeholder="jan@budowlanka.pl" required>
          </div>
          <div class="bud-field bud-field-full">
            <label for="bud_business">Nazwa firmy i miasto</label>
            <input type="text" id="bud_business" name="lead_company" placeholder="np. BUDREX — Wroclaw" required>
          </div>
          <div class="bud-field">
            <label for="bud_size">Liczba osob w ekipie</label>
            <select id="bud_size" name="lead_goal_detail">
              <option value="">— wybierz —</option>
              <option value="solo">Pracuje sam</option>
              <option value="2-4">2-4 osoby</option>
              <option value="5-10">5-10 osob</option>
              <option value="11-20">11-20 osob</option>
              <option value="20+">powyzej 20 osob</option>
            </select>
          </div>
          <div class="bud-field">
            <label for="bud_speciality">Glowna specjalizacja</label>
            <input type="text" id="bud_speciality" name="lead_source_detail" placeholder="np. wykonczenia, dachy, elewacje">
          </div>
          <div class="bud-field bud-field-full">
            <label for="bud_message">Co Cie najbardziej trapi? <span class="opt">opcjonalnie, ale pomaga</span></label>
            <textarea id="bud_message" name="lead_message" placeholder="np. Sezon pelny, ale listopad-luty bez pracy."></textarea>
          </div>
        </div>

        <div class="bud-field bud-field-full">
          <input type="checkbox" id="bud_consent" name="lead_consent" value="1" required style="margin-right: 8px;">
          <label for="bud_consent" style="display: inline; font-size: 13px;">Zgadzam sie na przetwarzanie danych osobowych w celu kontaktu.</label>
        </div>

        <button type="submit" class="bud-form-submit" data-cta="form-submit">Umow bezplatna konsultacje →</button>

        <p class="bud-form-meta">
          Twoje dane sluza wylacznie do umowienia konsultacji. Nie zapisuje Cie na newsletter i nie sprzedaje bazy.
        </p>
      </form>
    </div>
  </section>
</div>

<?php get_footer(); ?>
