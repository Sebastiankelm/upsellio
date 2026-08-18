<?php
/**
 * Template Name: Marketing dla gabinetu stomatologicznego — landing
 *
 * Niszowy landing dla branzy stomatologicznej.
 */
get_header();
?>

<style>
  :root {
    --dent-radius: 20px;
    --dent-radius-lg: 28px;
    --dent-blue: #0284c7;
    --dent-blue-soft: #e0f2fe;
    --dent-blue-line: #bae6fd;
    --dent-mint: #14b8a6;
  }

  .dent-page { background: var(--bg, #fafaf6); color: var(--text, #0d0d0b); font-family: var(--font-body, "DM Sans"), system-ui, sans-serif; overflow-x: hidden; }
  .dent-page * { box-sizing: border-box; }
  .dent-container { max-width: 1100px; margin: 0 auto; padding: 0 24px; }

  .dent-hero { position: relative; padding: clamp(60px, 10vw, 100px) 0 clamp(50px, 8vw, 80px); overflow: hidden; }
  .dent-hero::before {
    content: "";
    position: absolute; inset: 0;
    background:
      radial-gradient(ellipse 700px 400px at 80% 20%, rgba(2, 132, 199, 0.07), transparent),
      radial-gradient(ellipse 500px 300px at 10% 80%, rgba(20, 184, 166, 0.06), transparent);
    pointer-events: none; z-index: 0;
  }
  .dent-hero-inner { position: relative; z-index: 1; }
  .dent-hero-grid { display: grid; grid-template-columns: 1.15fr 1fr; gap: 56px; align-items: center; }
  @media (max-width: 900px) { .dent-hero-grid { grid-template-columns: 1fr; gap: 40px; } }

  .dent-pill {
    display: inline-flex; align-items: center; gap: 8px; background: var(--dent-blue-soft); color: #075985;
    padding: 8px 16px; border-radius: 999px; font-size: 12px; font-weight: 700; letter-spacing: 0.4px; text-transform: uppercase; margin-bottom: 24px;
  }
  .dent-pill::before {
    content: ""; width: 6px; height: 6px; background: var(--dent-blue); border-radius: 50%; animation: dentPulse 2s ease-in-out infinite;
  }
  @keyframes dentPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(0.85); }
  }
  .dent-hero h1 { font-family: var(--font-display, "Bricolage Grotesque"), serif; font-size: clamp(36px, 5.5vw, 60px); font-weight: 800; line-height: 1.04; letter-spacing: -0.025em; margin: 0 0 24px; color: var(--text); }
  .dent-hero h1 em {
    font-style: normal; background: linear-gradient(120deg, var(--dent-blue) 0%, var(--dent-mint) 100%);
    -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; color: transparent;
  }
  .dent-hero-sub { font-size: clamp(16px, 2vw, 19px); line-height: 1.6; color: var(--text-soft, #7a7a72); margin: 0 0 32px; max-width: 540px; }
  .dent-hero-cta-row { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 32px; }
  .dent-cta-primary {
    display: inline-flex; align-items: center; gap: 10px; background: var(--text, #0d0d0b); color: #fff; padding: 18px 32px; border-radius: 14px;
    font-size: 16px; font-weight: 700; text-decoration: none; transition: all 0.2s var(--ease-out, cubic-bezier(0.16, 1, 0.3, 1)); box-shadow: 0 4px 16px rgba(13, 13, 11, 0.15);
  }
  .dent-cta-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(13, 13, 11, 0.2); color: #fff; }
  .dent-cta-primary::after { content: "→"; transition: transform 0.2s ease; }
  .dent-cta-primary:hover::after { transform: translateX(4px); }
  .dent-cta-secondary {
    display: inline-flex; align-items: center; gap: 8px; color: var(--text); padding: 18px 24px; font-size: 15px; font-weight: 600;
    text-decoration: none; border-bottom: 2px solid transparent; transition: all 0.15s ease;
  }
  .dent-cta-secondary:hover { border-bottom-color: var(--dent-blue); color: var(--dent-blue); }
  .dent-trust-row { display: flex; gap: 24px; flex-wrap: wrap; color: var(--text-soft, #7a7a72); font-size: 14px; }
  .dent-trust-item { display: inline-flex; align-items: center; gap: 8px; }
  .dent-trust-item::before { content: "✓"; color: var(--dent-blue); font-weight: 800; font-size: 16px; }

  .dent-dashboard {
    background: #fff; border: 1px solid var(--border, #e8e8e0); border-radius: var(--dent-radius); padding: 24px; box-shadow: var(--shadow, 0 22px 60px rgba(15, 23, 42, 0.1));
    position: relative; transform: rotate(-0.5deg);
  }
  .dent-dashboard::before {
    content: "Pierwsze 90 dni"; position: absolute; top: -12px; right: 24px;
    background: linear-gradient(135deg, var(--dent-blue) 0%, var(--dent-mint) 100%); color: #fff; padding: 6px 14px; border-radius: 999px;
    font-size: 11px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;
  }
  .dent-dashboard-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid var(--border); }
  .dent-dashboard-name { font-family: var(--font-display); font-size: 13px; font-weight: 700; color: var(--text-muted); }
  .dent-rating { display: flex; align-items: center; gap: 4px; background: #fef3c7; color: #b45309; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; }
  .dent-rating::before { content: "★"; }
  .dent-stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 16px; }
  .dent-stat { background: var(--bg-alt, #f2f2ec); padding: 14px; border-radius: 10px; }
  .dent-stat-label { font-size: 10px; font-weight: 700; color: var(--text-soft); letter-spacing: 0.4px; text-transform: uppercase; margin-bottom: 4px; }
  .dent-stat-val { font-family: var(--font-display); font-size: 22px; font-weight: 800; color: var(--text); font-variant-numeric: tabular-nums; }
  .dent-stat-trend { font-size: 10px; font-weight: 700; color: var(--success, #15803d); margin-top: 2px; }
  .dent-treatments-section { background: linear-gradient(135deg, #f0f9ff 0%, #fff 100%); border-radius: 10px; padding: 14px 16px; }
  .dent-treatments-title { font-size: 11px; font-weight: 700; color: var(--text-muted); letter-spacing: 0.4px; text-transform: uppercase; margin-bottom: 10px; }
  .dent-treatment { display: flex; align-items: center; justify-content: space-between; padding: 7px 0; font-size: 12.5px; }
  .dent-treatment:not(:last-child) { border-bottom: 1px dashed var(--border); }
  .dent-treatment-name { display: flex; align-items: center; gap: 8px; font-weight: 600; color: var(--text); }
  .dent-treatment-icon { font-size: 14px; }
  .dent-treatment-stats { display: flex; gap: 12px; font-variant-numeric: tabular-nums; }
  .dent-treatment-count { font-weight: 700; color: var(--text); }
  .dent-treatment-value { color: var(--dent-mint); font-weight: 700; font-size: 11px; }

  .dent-section { padding: clamp(60px, 8vw, 100px) 0; position: relative; }
  .dent-section-soft { background: var(--surface, #fff); }
  .dent-section-head { max-width: 720px; margin: 0 0 56px; }
  .dent-section-head.is-center { margin: 0 auto 56px; text-align: center; }
  .dent-section-eyebrow { font-size: 12px; font-weight: 700; color: #075985; letter-spacing: 0.8px; text-transform: uppercase; margin-bottom: 12px; display: inline-block; }
  .dent-section h2 { font-family: var(--font-display); font-size: clamp(28px, 3.8vw, 42px); font-weight: 800; line-height: 1.15; letter-spacing: -0.018em; margin: 0 0 16px; color: var(--text); }
  .dent-section-intro { font-size: clamp(16px, 1.6vw, 18px); line-height: 1.65; color: var(--text-soft); margin: 0; }

  .dent-problems { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin-top: 16px; }
  @media (max-width: 800px) { .dent-problems { grid-template-columns: 1fr; } }
  .dent-problem { background: var(--surface); border: 1px solid var(--border); border-radius: var(--dent-radius); padding: 28px; transition: all 0.25s var(--ease-out); }
  .dent-problem:hover { transform: translateY(-3px); border-color: var(--dent-blue); box-shadow: var(--shadow-soft); }
  .dent-problem-quote { font-family: var(--font-display); font-size: 22px; font-weight: 700; line-height: 1.3; color: var(--text); margin: 0 0 14px; position: relative; padding-top: 24px; }
  .dent-problem-quote::before {
    content: '"'; position: absolute; top: -10px; left: -8px; font-size: 60px; color: var(--dent-blue); line-height: 1; font-family: serif; opacity: 0.4;
  }
  .dent-problem p { font-size: 14px; line-height: 1.6; color: var(--text-soft); margin: 0; }

  .dent-treatments-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 16px; }
  @media (max-width: 800px) { .dent-treatments-grid { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 500px) { .dent-treatments-grid { grid-template-columns: 1fr; } }
  .dent-treatment-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--dent-radius); padding: 24px; transition: all 0.25s var(--ease-out); text-align: center; }
  .dent-treatment-card:hover { border-color: var(--dent-blue); transform: translateY(-3px); box-shadow: var(--shadow-soft); }
  .dent-treatment-card-icon { width: 52px; height: 52px; margin: 0 auto 14px; border-radius: 14px; background: var(--dent-blue-soft); color: var(--dent-blue); display: grid; place-items: center; font-size: 24px; }
  .dent-treatment-card h4 { font-family: var(--font-display); font-size: 16px; font-weight: 700; margin: 0 0 6px; }
  .dent-treatment-card-value { font-family: var(--font-display); font-size: 13px; color: var(--dent-mint); font-weight: 700; margin-bottom: 8px; }
  .dent-treatment-card p { font-size: 12.5px; line-height: 1.5; color: var(--text-soft); margin: 0; }

  .dent-package-wrap { display: grid; grid-template-columns: 1.2fr 1fr; gap: 24px; margin-top: 16px; }
  @media (max-width: 800px) { .dent-package-wrap { grid-template-columns: 1fr; } }
  .dent-package { background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border: 1px solid var(--dent-blue-line); border-radius: var(--dent-radius-lg); padding: clamp(28px, 4vw, 44px); position: relative; }
  .dent-package-tag { display: inline-block; background: var(--dent-blue); color: #fff; padding: 6px 14px; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 16px; }
  .dent-package h3 { font-family: var(--font-display); font-size: clamp(22px, 2.5vw, 30px); font-weight: 800; line-height: 1.2; margin: 0 0 12px; color: var(--text); }
  .dent-package-desc { font-size: 15px; line-height: 1.6; color: var(--text-muted); margin: 0 0 28px; }
  .dent-package-list { margin: 0; padding: 0; list-style: none; }
  .dent-package-list li { padding: 10px 0 10px 32px; position: relative; font-size: 15px; line-height: 1.5; color: var(--text); border-bottom: 1px dashed rgba(2, 132, 199, 0.15); }
  .dent-package-list li:last-child { border-bottom: 0; }
  .dent-package-list li::before { content: "✓"; position: absolute; left: 0; top: 8px; width: 22px; height: 22px; background: var(--dent-blue); color: #fff; border-radius: 50%; display: grid; place-items: center; font-size: 12px; font-weight: 800; }
  .dent-pricing { background: var(--surface); border: 2px solid var(--text); border-radius: var(--dent-radius-lg); padding: clamp(28px, 4vw, 36px); text-align: center; position: sticky; top: 24px; }
  .dent-pricing-label { font-size: 11px; font-weight: 700; color: var(--text-soft); letter-spacing: 0.6px; text-transform: uppercase; margin-bottom: 8px; }
  .dent-pricing-amount { font-family: var(--font-display); font-size: clamp(36px, 5vw, 52px); font-weight: 800; line-height: 1; color: var(--text); margin-bottom: 4px; }
  .dent-pricing-amount small { font-size: 16px; font-weight: 600; color: var(--text-soft); margin-left: 4px; }
  .dent-pricing-period { font-size: 13px; color: var(--text-soft); margin-bottom: 24px; }
  .dent-pricing-extras { margin: 24px 0 0; padding: 20px 0 0; border-top: 1px solid var(--border); text-align: left; list-style: none; }
  .dent-pricing-extras li { padding: 6px 0 6px 24px; position: relative; font-size: 13px; color: var(--text-muted); line-height: 1.5; }
  .dent-pricing-extras li::before { content: "+"; position: absolute; left: 0; color: var(--dent-blue); font-weight: 800; }
  .dent-pricing-cta { display: block; width: 100%; padding: 16px; background: var(--text); color: #fff; border-radius: 12px; font-weight: 700; font-size: 15px; text-decoration: none; transition: all 0.2s ease; margin-top: 24px; }
  .dent-pricing-cta:hover { background: var(--dent-blue); color: #fff; transform: translateY(-1px); }

  .dent-honest { background: linear-gradient(135deg, var(--accent-soft, #fff7ed) 0%, #fff 100%); border: 1px solid var(--accent-line, #fed7aa); border-radius: var(--dent-radius); padding: clamp(28px, 4vw, 44px); margin-top: 32px; }
  .dent-honest-header { display: flex; gap: 16px; align-items: center; margin-bottom: 20px; }
  .dent-honest-icon { flex-shrink: 0; width: 44px; height: 44px; border-radius: 12px; background: var(--accent, #f97316); color: #fff; display: grid; place-items: center; font-size: 22px; font-weight: 800; }
  .dent-honest-header strong { font-family: var(--font-display); font-size: 18px; font-weight: 700; }
  .dent-honest ul { margin: 0; padding: 0; list-style: none; display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px 24px; }
  @media (max-width: 600px) { .dent-honest ul { grid-template-columns: 1fr; } }
  .dent-honest li { padding: 10px 0 10px 28px; position: relative; font-size: 15px; line-height: 1.5; color: var(--text-muted); }
  .dent-honest li::before { content: "✗"; position: absolute; left: 0; top: 8px; color: var(--accent); font-weight: 800; font-size: 18px; }

  .dent-steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: 16px; }
  @media (max-width: 800px) { .dent-steps { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 500px) { .dent-steps { grid-template-columns: 1fr; } }
  .dent-step { background: var(--surface, #fff); border: 1px solid var(--border); border-radius: var(--dent-radius); padding: 24px; position: relative; transition: all 0.25s var(--ease-out); }
  .dent-step:hover { transform: translateY(-3px); border-color: var(--dent-blue); box-shadow: var(--shadow-soft); }
  .dent-step-num { font-family: var(--font-display); font-size: 32px; font-weight: 800; line-height: 1; color: var(--dent-blue); margin-bottom: 16px; font-variant-numeric: tabular-nums; }
  .dent-step h4 { font-family: var(--font-display); font-size: 16px; font-weight: 700; line-height: 1.3; margin: 0 0 8px; }
  .dent-step p { font-size: 13px; line-height: 1.6; color: var(--text-soft); margin: 0; }
  .dent-step-time { display: inline-block; margin-top: 12px; font-size: 11px; font-weight: 700; color: var(--text-soft); letter-spacing: 0.5px; text-transform: uppercase; padding: 4px 8px; background: var(--bg-alt, #f2f2ec); border-radius: 6px; }

  .dent-example-wrap { background: linear-gradient(135deg, #082f49 0%, #0c4a6e 100%); color: #fff; border-radius: var(--dent-radius-lg); padding: clamp(36px, 5vw, 56px); margin-top: 16px; position: relative; overflow: hidden; }
  .dent-example-wrap::before { content: ""; position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(2, 132, 199, 0.25) 0%, transparent 60%); pointer-events: none; }
  .dent-example-wrap > * { position: relative; }
  .dent-example-eyebrow { font-size: 11px; font-weight: 700; color: #7dd3fc; letter-spacing: 0.8px; text-transform: uppercase; margin-bottom: 14px; }
  .dent-example-wrap h3 { font-family: var(--font-display); font-size: clamp(22px, 2.5vw, 30px); font-weight: 800; line-height: 1.25; margin: 0 0 12px; color: #fff; }
  .dent-example-context { color: rgba(255, 255, 255, 0.7); font-size: 15px; line-height: 1.6; margin: 0 0 32px; max-width: 600px; }
  .dent-comparison { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 32px; }
  @media (max-width: 700px) { .dent-comparison { grid-template-columns: 1fr; } }
  .dent-state { background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.1); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border-radius: 16px; padding: 24px; }
  .dent-state.is-after { background: rgba(2, 132, 199, 0.18); border-color: rgba(125, 211, 252, 0.35); }
  .dent-state-label { display: inline-block; font-size: 10px; font-weight: 700; letter-spacing: 0.6px; text-transform: uppercase; background: rgba(255, 255, 255, 0.1); color: #fff; padding: 4px 8px; border-radius: 6px; margin-bottom: 14px; }
  .dent-state.is-after .dent-state-label { background: var(--dent-blue); }
  .dent-state h4 { font-family: var(--font-display); font-size: 15px; font-weight: 700; margin: 0 0 14px; color: #fff; }
  .dent-state-metric { padding: 10px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.1); display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: rgba(255, 255, 255, 0.85); }
  .dent-state-metric:last-child { border-bottom: 0; }
  .dent-state-metric strong { font-family: var(--font-display); font-variant-numeric: tabular-nums; font-weight: 800; font-size: 15px; }
  .dent-state.is-after .dent-state-metric strong { color: #7dd3fc; }
  .dent-example-summary { border-top: 1px solid rgba(255, 255, 255, 0.12); padding-top: 28px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
  .dent-example-summary-text { color: rgba(255, 255, 255, 0.85); font-size: 15px; line-height: 1.5; max-width: 520px; }
  .dent-example-summary-amount { font-family: var(--font-display); font-size: clamp(28px, 3.5vw, 38px); font-weight: 800; color: #fff; text-align: right; }
  .dent-example-summary-amount small { display: block; font-size: 12px; font-weight: 600; color: rgba(255, 255, 255, 0.6); letter-spacing: 0.4px; text-transform: uppercase; margin-top: 4px; }

  .dent-target { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
  @media (max-width: 700px) { .dent-target { grid-template-columns: 1fr; } }
  .dent-target-col { padding: 28px; border-radius: var(--dent-radius); border: 1px solid var(--border); }
  .dent-target-col.is-yes { background: var(--dent-blue-soft); border-color: rgba(2, 132, 199, 0.2); }
  .dent-target-col.is-no { background: var(--bg-alt, #f2f2ec); }
  .dent-target-col h4 { font-family: var(--font-display); font-size: 17px; font-weight: 700; margin: 0 0 16px; display: flex; align-items: center; gap: 8px; }
  .dent-target-col h4::before { width: 24px; height: 24px; border-radius: 50%; display: grid; place-items: center; font-size: 14px; font-weight: 800; flex-shrink: 0; }
  .dent-target-col.is-yes h4::before { content: "✓"; background: var(--dent-blue); color: #fff; }
  .dent-target-col.is-no h4::before { content: "—"; background: var(--text-soft); color: #fff; }
  .dent-target-col ul { margin: 0; padding: 0; list-style: none; }
  .dent-target-col li { padding: 8px 0; font-size: 14px; line-height: 1.55; color: var(--text-muted); }

  .dent-faq-list { max-width: 760px; margin: 0 auto; }
  .dent-faq { background: var(--surface); border: 1px solid var(--border); border-radius: var(--dent-radius); margin-bottom: 12px; overflow: hidden; transition: border-color 0.2s ease; }
  .dent-faq:hover { border-color: var(--dent-blue); }
  .dent-faq[open] { border-color: var(--dent-blue); box-shadow: var(--shadow-sm); }
  .dent-faq summary {
    padding: 22px 28px; cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center; gap: 16px;
    font-family: var(--font-display); font-size: 16px; font-weight: 700; line-height: 1.4; color: var(--text); user-select: none;
  }
  .dent-faq summary::-webkit-details-marker { display: none; }
  .dent-faq summary::after {
    content: "+"; flex-shrink: 0; width: 28px; height: 28px; border-radius: 50%; background: var(--dent-blue-soft); color: var(--dent-blue);
    display: grid; place-items: center; font-size: 18px; font-weight: 400; transition: all 0.2s ease;
  }
  .dent-faq[open] summary::after { content: "−"; background: var(--dent-blue); color: #fff; transform: rotate(180deg); }
  .dent-faq-content { padding: 0 28px 24px; color: var(--text-soft); font-size: 15px; line-height: 1.7; }
  .dent-faq-content p { margin: 0 0 12px; }
  .dent-faq-content p:last-child { margin: 0; }

  .dent-author { background: var(--sand, #f5f0e8); border-radius: var(--dent-radius-lg); padding: clamp(36px, 5vw, 56px); display: grid; grid-template-columns: auto 1fr; gap: 32px; align-items: center; margin-top: 16px; }
  @media (max-width: 700px) { .dent-author { grid-template-columns: 1fr; text-align: center; } }
  .dent-author-photo { width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, var(--dent-blue) 0%, #075985 100%); color: #fff; display: grid; place-items: center; font-family: var(--font-display); font-size: 42px; font-weight: 800; flex-shrink: 0; margin: 0 auto; }
  .dent-author-content h3 { font-family: var(--font-display); font-size: 22px; font-weight: 800; margin: 0 0 8px; }
  .dent-author-role { font-size: 14px; font-weight: 600; color: #075985; margin: 0 0 16px; }
  .dent-author-content p { font-size: 15px; line-height: 1.65; color: var(--text-muted); margin: 0; }

  .dent-final { background: linear-gradient(180deg, var(--text, #0d0d0b) 0%, #082f49 100%); color: #fff; padding: clamp(60px, 9vw, 96px) 0; position: relative; overflow: hidden; }
  .dent-final::before { content: ""; position: absolute; top: -100px; left: 50%; transform: translateX(-50%); width: 600px; height: 600px; background: radial-gradient(circle, rgba(2, 132, 199, 0.25) 0%, transparent 70%); pointer-events: none; }
  .dent-final-inner { position: relative; max-width: 640px; margin: 0 auto; text-align: center; }
  .dent-final h2 { color: #fff; margin-bottom: 16px; }
  .dent-final-sub { font-size: 17px; line-height: 1.6; color: rgba(255, 255, 255, 0.7); margin: 0 0 40px; }
  .dent-form { background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.1); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border-radius: var(--dent-radius); padding: clamp(24px, 4vw, 36px); text-align: left; }
  .dent-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  @media (max-width: 600px) { .dent-form-grid { grid-template-columns: 1fr; } }
  .dent-field { margin-bottom: 16px; }
  .dent-field-full { grid-column: 1 / -1; }
  .dent-field label { display: block; font-size: 13px; font-weight: 600; color: rgba(255, 255, 255, 0.85); margin-bottom: 8px; }
  .dent-field label .opt { font-weight: 400; color: rgba(255, 255, 255, 0.4); font-size: 12px; margin-left: 6px; }
  .dent-field input,
  .dent-field textarea,
  .dent-field select {
    width: 100%; padding: 14px 16px; background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.15); color: #fff;
    border-radius: 12px; font-size: 15px; font-family: inherit; transition: all 0.2s ease;
  }
  .dent-field input::placeholder,
  .dent-field textarea::placeholder { color: rgba(255, 255, 255, 0.35); }
  .dent-field input:focus,
  .dent-field textarea:focus,
  .dent-field select:focus { outline: none; border-color: var(--dent-blue); background: rgba(255, 255, 255, 0.1); }
  .dent-field textarea { min-height: 90px; resize: vertical; }
  .dent-form-submit { width: 100%; padding: 18px; background: var(--dent-blue); color: #fff; border: 0; border-radius: 14px; font-weight: 700; font-size: 16px; cursor: pointer; transition: all 0.2s ease; margin-top: 8px; font-family: inherit; }
  .dent-form-submit:hover { background: #075985; transform: translateY(-2px); box-shadow: 0 12px 24px rgba(2, 132, 199, 0.3); }
  .dent-form-meta { text-align: center; margin-top: 20px; font-size: 12px; color: rgba(255, 255, 255, 0.4); line-height: 1.5; }
  .dent-form-feedback { margin-bottom: 20px; padding: 12px 14px; border-radius: 10px; font-size: 14px; }
  .dent-form-feedback.is-success { background: rgba(22, 163, 74, 0.18); border: 1px solid rgba(34, 197, 94, 0.45); color: #dcfce7; }
  .dent-form-feedback.is-error { background: rgba(220, 38, 38, 0.18); border: 1px solid rgba(248, 113, 113, 0.45); color: #fee2e2; }
</style>

<div class="dent-page">
  <section class="dent-hero">
    <div class="dent-container dent-hero-inner">
      <div class="dent-hero-grid">
        <div>
          <div class="dent-pill">Marketing dla gabinetow stomatologicznych</div>
          <h1>Pacjenci na <em>implanty i ortodoncje</em>, nie tylko przeglady</h1>
          <p class="dent-hero-sub">
            Reklama Google + ZnanyLekarz + nowa strona, ktore przyciagaja pacjentow na zabiegi specjalistyczne.
          </p>
          <div class="dent-hero-cta-row">
            <a href="#kontakt" class="dent-cta-primary" data-cta="hero-umow-rozmowe">Umow rozmowe</a>
            <a href="#oferta" class="dent-cta-secondary" data-cta="hero-zobacz-pakiet">Zobacz pakiet</a>
          </div>
          <div class="dent-trust-row">
            <span class="dent-trust-item">Pierwsi pacjenci w 30 dni</span>
            <span class="dent-trust-item">Bez umow na rok</span>
            <span class="dent-trust-item">Pierwsza konsultacja gratis</span>
          </div>
        </div>
        <div>
          <div class="dent-dashboard" aria-hidden="true">
            <div class="dent-dashboard-header">
              <span class="dent-dashboard-name">Gabinet Dental Pro — Wroclaw</span>
              <span class="dent-rating">4.9 (127)</span>
            </div>
            <div class="dent-stats-grid">
              <div class="dent-stat">
                <div class="dent-stat-label">Nowi pacjenci</div>
                <div class="dent-stat-val">38</div>
                <div class="dent-stat-trend">+280%</div>
              </div>
              <div class="dent-stat">
                <div class="dent-stat-label">Wartosc zabiegow</div>
                <div class="dent-stat-val">142k</div>
                <div class="dent-stat-trend">+340%</div>
              </div>
            </div>
            <div class="dent-treatments-section">
              <div class="dent-treatments-title">Zabiegi z reklam — 90 dni</div>
              <div class="dent-treatment"><div class="dent-treatment-name"><span class="dent-treatment-icon">🦷</span>Implanty</div><div class="dent-treatment-stats"><span class="dent-treatment-count">14</span><span class="dent-treatment-value">~78 000 zl</span></div></div>
              <div class="dent-treatment"><div class="dent-treatment-name"><span class="dent-treatment-icon">⚙️</span>Ortodoncja</div><div class="dent-treatment-stats"><span class="dent-treatment-count">6</span><span class="dent-treatment-value">~42 000 zl</span></div></div>
              <div class="dent-treatment"><div class="dent-treatment-name"><span class="dent-treatment-icon">✨</span>Wybielanie</div><div class="dent-treatment-stats"><span class="dent-treatment-count">11</span><span class="dent-treatment-value">~13 200 zl</span></div></div>
              <div class="dent-treatment"><div class="dent-treatment-name"><span class="dent-treatment-icon">👶</span>Stomatologia dziecieca</div><div class="dent-treatment-stats"><span class="dent-treatment-count">7</span><span class="dent-treatment-value">~8 400 zl</span></div></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="dent-section dent-section-soft">
    <div class="dent-container">
      <div class="dent-section-head">
        <span class="dent-section-eyebrow">Jesli ktore brzmi znajomo</span>
        <h2>Trzy problemy, ktore slysze od stomatologow</h2>
      </div>
      <div class="dent-problems">
        <div class="dent-problem">
          <p class="dent-problem-quote">Mam fotel, mam czas, ale nikt nie umawia sie na implanty.</p>
          <p>Przeglady i bole przychodza same. Zabiegi specjalistyczne wymagaja innego procesu pozyskania i zaufania.</p>
        </div>
        <div class="dent-problem">
          <p class="dent-problem-quote">Pacjenci pytaja o cene przez telefon i znikaja.</p>
          <p>Bez edukacji i kontekstu pacjent porownuje tylko stawke, nie jakosc i kompetencje.</p>
        </div>
        <div class="dent-problem">
          <p class="dent-problem-quote">ZnanyLekarz przekierowuje, ale to chaos.</p>
          <p>Naplywaja zapytania niedopasowane do specjalizacji i kalendarza gabinetu.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="dent-section">
    <div class="dent-container">
      <div class="dent-section-head">
        <span class="dent-section-eyebrow">Klucz do gabinetu</span>
        <h2>Cel: pacjenci na zabiegi, nie tylko przeglady</h2>
      </div>
      <div class="dent-treatments-grid">
        <div class="dent-treatment-card"><div class="dent-treatment-card-icon">🦷</div><h4>Implanty</h4><div class="dent-treatment-card-value">3 500-8 000 zl / sztuka</div><p>Najwyzsza wartosc pojedynczego pacjenta.</p></div>
        <div class="dent-treatment-card"><div class="dent-treatment-card-icon">⚙️</div><h4>Ortodoncja</h4><div class="dent-treatment-card-value">5 000-15 000 zl / leczenie</div><p>Stala relacja i wysoka retencja.</p></div>
        <div class="dent-treatment-card"><div class="dent-treatment-card-icon">👑</div><h4>Protetyka</h4><div class="dent-treatment-card-value">800-3 000 zl / korona</div><p>Naturalne uzupelnienie po implantach.</p></div>
        <div class="dent-treatment-card"><div class="dent-treatment-card-icon">✨</div><h4>Wybielanie</h4><div class="dent-treatment-card-value">800-2 000 zl / zabieg</div><p>Niski prog wejscia i dobry punkt startu relacji.</p></div>
        <div class="dent-treatment-card"><div class="dent-treatment-card-icon">😄</div><h4>Licowki</h4><div class="dent-treatment-card-value">1 200-2 500 zl / sztuka</div><p>Segment premium o wysokiej wartosci.</p></div>
        <div class="dent-treatment-card"><div class="dent-treatment-card-icon">👶</div><h4>Stomatologia dziecieca</h4><div class="dent-treatment-card-value">~120 zl / wizyta</div><p>Rodzina zostaje w gabinecie przez lata.</p></div>
      </div>
    </div>
  </section>

  <section class="dent-section dent-section-soft" id="oferta">
    <div class="dent-container">
      <div class="dent-section-head">
        <span class="dent-section-eyebrow">Co konkretnie robie</span>
        <h2>Pakiet "Pelen kalendarz na zabiegi"</h2>
      </div>
      <div class="dent-package-wrap">
        <div class="dent-package">
          <span class="dent-package-tag">Pakiet startowy</span>
          <h3>Pacjenci na zabiegi specjalistyczne w 60-90 dni</h3>
          <p class="dent-package-desc">Skupiamy sie na 2-3 specjalizacjach najbardziej rentownych dla gabinetu.</p>
          <ul class="dent-package-list">
            <li>Nowa strona lub redesign z sekcjami zabiegow</li>
            <li>Landing pages dla 2-3 wybranych specjalizacji</li>
            <li>Google Ads na frazy zabiegowe</li>
            <li>Meta Ads + kreacje zgodne z compliance</li>
            <li>Optymalizacja Google Maps + opinie</li>
            <li>Optymalizacja profilu ZnanyLekarz</li>
            <li>Tresci edukacyjne pod SEO i zaufanie</li>
            <li>Cotygodniowa optymalizacja + raport miesieczny</li>
          </ul>
        </div>
        <div class="dent-pricing">
          <div class="dent-pricing-label">Miesieczna oplata</div>
          <div class="dent-pricing-amount">3 200<small>zl</small></div>
          <div class="dent-pricing-period">+ budzet reklamowy od 2 000 zl/mies</div>
          <ul class="dent-pricing-extras">
            <li>Pierwsza konsultacja gratis (60 min)</li>
            <li>Bez umow na 12 miesiecy</li>
            <li>Strona/landingi w pierwszym miesiacu w cenie</li>
            <li>Jeden pacjent zabiegowy potrafi pokryc koszt pakietu</li>
          </ul>
          <a href="#kontakt" class="dent-pricing-cta" data-cta="oferta-umow-konsultacje">Umow konsultacje →</a>
        </div>
      </div>
    </div>
  </section>

  <section class="dent-section">
    <div class="dent-container">
      <div class="dent-section-head">
        <span class="dent-section-eyebrow">Szczerze</span>
        <h2>Czego NIE dostaniesz (i dlaczego dobrze)</h2>
      </div>
      <div class="dent-honest">
        <div class="dent-honest-header"><div class="dent-honest-icon">!</div><strong>W pakiecie nie znajdziesz:</strong></div>
        <ul>
          <li>Obietnic "100% sukcesu leczenia"</li>
          <li>Promocji "implant za 999 zl"</li>
          <li>Materialow niezgodnych z compliance</li>
          <li>Kupowania opinii</li>
          <li>Konkurowania tylko cena</li>
          <li>Wypelniania kalendarza przypadkowymi pacjentami</li>
        </ul>
      </div>
    </div>
  </section>

  <section class="dent-section dent-section-soft">
    <div class="dent-container">
      <div class="dent-section-head">
        <span class="dent-section-eyebrow">Jak zaczynamy</span>
        <h2>Cztery kroki od rozmowy do wypelnionego kalendarza</h2>
      </div>
      <div class="dent-steps">
        <div class="dent-step"><div class="dent-step-num">01</div><h4>Bezplatna konsultacja</h4><p>Dobieramy 2-3 zabiegi i plan pozyskania pacjentow.</p><span class="dent-step-time">Tydzien 1</span></div>
        <div class="dent-step"><div class="dent-step-num">02</div><h4>Strona + tresci</h4><p>Budujemy przekaz pod specjalizacje i zaufanie.</p><span class="dent-step-time">Tygodnie 2-4</span></div>
        <div class="dent-step"><div class="dent-step-num">03</div><h4>Start kampanii</h4><p>Google Ads + Meta Ads + optymalizacja profili.</p><span class="dent-step-time">Miesiac 1-2</span></div>
        <div class="dent-step"><div class="dent-step-num">04</div><h4>Skalowanie</h4><p>Regularna optymalizacja pod jakosc i wartosc pacjentow.</p><span class="dent-step-time">Cyklicznie</span></div>
      </div>
    </div>
  </section>

  <section class="dent-section">
    <div class="dent-container">
      <div class="dent-section-head">
        <span class="dent-section-eyebrow">Konkret zamiast obietnic</span>
        <h2>Pierwsze 90 dni gabinetu z Wroclawia</h2>
      </div>
      <div class="dent-example-wrap">
        <div class="dent-example-eyebrow">Gabinet Dental Pro — Wroclaw</div>
        <h3>Z 8 implantow rocznie do 14 w kwartale</h3>
        <p class="dent-example-context">Pozycjonowanie na zabiegi i uporzadkowanie sciezki pacjenta zwiekszyly wartosc wizyt i skutecznosc leadow.</p>
        <div class="dent-comparison">
          <div class="dent-state">
            <span class="dent-state-label">Przed</span>
            <h4>Q4 2025</h4>
            <div class="dent-state-metric"><span>Implanty / kwartal</span><strong>2</strong></div>
            <div class="dent-state-metric"><span>Pacjenci ortodoncji</span><strong>1-2</strong></div>
            <div class="dent-state-metric"><span>Srednia wartosc pacjenta</span><strong>340 zl</strong></div>
            <div class="dent-state-metric"><span>Formularze strony</span><strong>1-2/mies</strong></div>
          </div>
          <div class="dent-state is-after">
            <span class="dent-state-label">Po 90 dniach</span>
            <h4>Q1 2026</h4>
            <div class="dent-state-metric"><span>Implanty / kwartal</span><strong>14</strong></div>
            <div class="dent-state-metric"><span>Pacjenci ortodoncji</span><strong>6</strong></div>
            <div class="dent-state-metric"><span>Srednia wartosc pacjenta</span><strong>1 240 zl</strong></div>
            <div class="dent-state-metric"><span>Formularze strony</span><strong>14-18/mies</strong></div>
          </div>
        </div>
        <div class="dent-example-summary">
          <div class="dent-example-summary-text">Nowa strona + frazy zabiegowe + optymalizacja ZnanyLekarz i Google Maps.</div>
          <div class="dent-example-summary-amount">~110 000 zl<small>dodatkowy przychod / kwartal</small></div>
        </div>
      </div>
    </div>
  </section>

  <section class="dent-section dent-section-soft">
    <div class="dent-container">
      <div class="dent-section-head">
        <span class="dent-section-eyebrow">Dla kogo</span>
        <h2>Dla kogo to ma sens</h2>
      </div>
      <div class="dent-target">
        <div class="dent-target-col is-yes">
          <h4>Pakiet ma sens jesli:</h4>
          <ul>
            <li>→ Gabinet dziala min. 2 lata</li>
            <li>→ Wykonujesz zabiegi specjalistyczne</li>
            <li>→ Masz wolne miejsca na zabiegi</li>
            <li>→ Przychod min. 50 000 zl/mies</li>
            <li>→ Masz opinie i podstawy do skalowania</li>
          </ul>
        </div>
        <div class="dent-target-col is-no">
          <h4>Pakiet NIE ma sensu jesli:</h4>
          <ul>
            <li>→ Gabinet dopiero startuje</li>
            <li>→ Robisz glownie przeglady i NFZ</li>
            <li>→ Opinie sa ponizej 4.0</li>
            <li>→ Konkurujesz glownie cena</li>
            <li>→ Brak mocy operacyjnej na nowych pacjentow</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section class="dent-section">
    <div class="dent-container">
      <div class="dent-section-head">
        <span class="dent-section-eyebrow">Kto bedzie z Toba pracowal</span>
        <h2>Po drugiej stronie ekranu</h2>
      </div>
      <div class="dent-author">
        <div class="dent-author-photo">SK</div>
        <div class="dent-author-content">
          <h3>Sebastian Kelm</h3>
          <p class="dent-author-role">Konsultant marketingu — Google Ads · Meta Ads · ZnanyLekarz · strony medyczne</p>
          <p>Pracuje bezposrednio z gabinetami i buduje marketing pod jakosc pacjentow, reputacje i zgodnosc z regulacjami.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="dent-section dent-section-soft">
    <div class="dent-container">
      <div class="dent-section-head is-center">
        <span class="dent-section-eyebrow">FAQ</span>
        <h2>Pytania, ktore dostaje najczesciej</h2>
      </div>
      <div class="dent-faq-list">
        <details class="dent-faq">
          <summary>Ile to kosztuje miesiecznie razem z reklamami?</summary>
          <div class="dent-faq-content"><p>Pakiet: <strong>3 200 zl netto/mies</strong> + budzet reklamowy od 2 000 zl.</p></div>
        </details>
        <details class="dent-faq">
          <summary>Po jakim czasie zobacze pierwszych pacjentow?</summary>
          <div class="dent-faq-content"><p>Pierwsze zapytania zwykle 7-14 dni, pierwsze konkretne zabiegi zwykle 30-60 dni.</p></div>
        </details>
        <details class="dent-faq">
          <summary>Czy reklama gabinetu jest zgodna z kodeksem etyki?</summary>
          <div class="dent-faq-content"><p>Tak, prowadzimy komunikacje zgodna z regulacjami i bez obietnic efektu leczenia.</p></div>
        </details>
      </div>
    </div>
  </section>

  <section class="dent-final" id="kontakt">
    <div class="dent-container dent-final-inner">
      <h2>Umow bezplatna konsultacje</h2>
      <p class="dent-final-sub">60 minut online. Ocenie potencjal Twoich specjalizacji i plan pozyskiwania pacjentow zabiegowych.</p>

      <form class="dent-form" method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" data-form="gabinet-stomatologiczny">
        <input type="hidden" name="action" value="upsellio_submit_lead">
        <?php wp_nonce_field("upsellio_unified_lead_form", "upsellio_lead_form_nonce"); ?>
        <input type="hidden" name="lead_form_origin" value="gabinet-stomatologiczny-form">
        <input type="hidden" name="lead_source" value="gabinet-stomatologiczny-form">
        <input type="hidden" name="lead_service" value="Marketing gabinetu stomatologicznego">
        <input type="hidden" name="lead_goal" value="Pozyskanie pacjentow na zabiegi specjalistyczne w gabinecie stomatologicznym">
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
          <div class="dent-form-feedback is-success">Dziekuje! Formularz zostal wyslany. Odezwiemy sie najszybciej jak to mozliwe.</div>
        <?php elseif ($status === "error") : ?>
          <div class="dent-form-feedback is-error">Nie udalo sie wyslac formularza. Sprobuj ponownie za chwile.</div>
        <?php endif; ?>

        <div class="dent-form-grid">
          <div class="dent-field">
            <label for="dent_name">Imię</label>
            <input type="text" id="dent_name" name="lead_name" placeholder="Anna" required>
          </div>
          <div class="dent-field">
            <label for="dent_phone">Telefon</label>
            <input type="tel" id="dent_phone" name="lead_phone" placeholder="+48 575 522 595" autocomplete="tel" required>
          </div>
          <div class="dent-field dent-field-full">
            <label for="dent_email">E-mail</label>
            <input type="email" id="dent_email" name="lead_email" placeholder="anna@gabinet.pl" required>
          </div>
          <div class="dent-field dent-field-full">
            <label for="dent_message">Wiadomość <span class="opt">opcjonalnie</span></label>
            <textarea id="dent_message" name="lead_message" placeholder="Opcjonalnie — krótko, o co chodzi"></textarea>
          </div>
        </div>

        <div class="dent-field dent-field-full">
          <input type="checkbox" id="dent_consent" name="lead_consent" value="1" required style="margin-right: 8px;">
          <label for="dent_consent" style="display: inline; font-size: 13px;">Wyrażam zgodę na kontakt w sprawie przesłanego zapytania.</label>
        </div>

        <button type="submit" class="dent-form-submit" data-cta="form-submit">Oddzwonię w ciągu 24h</button>
        <p class="dent-form-meta">Twoje dane sluza wylacznie do umowienia konsultacji. Nie zapisuje Cie na newsletter i nie sprzedaje bazy.</p>
      </form>
    </div>
  </section>
</div>

<?php get_footer(); ?>
