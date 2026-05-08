<?php
/**
 * Template Name: Marketing dla biura rachunkowego — landing
 *
 * Niszowy landing dla branzy ksiegowej / biur rachunkowych.
 */
get_header();
?>

<style>
  :root {
    --br-radius: 20px;
    --br-radius-lg: 28px;
    --br-blue: #1e40af;
    --br-blue-soft: #dbeafe;
    --br-blue-line: #bfdbfe;
    --br-navy: #0f172a;
  }

  .br-page {
    background: var(--bg, #fafaf6);
    color: var(--text, #0d0d0b);
    font-family: var(--font-body, "DM Sans"), system-ui, sans-serif;
    overflow-x: hidden;
  }
  .br-page * { box-sizing: border-box; }
  .br-container { max-width: 1100px; margin: 0 auto; padding: 0 24px; }

  .br-hero { position: relative; padding: clamp(60px, 10vw, 100px) 0 clamp(50px, 8vw, 80px); overflow: hidden; }
  .br-hero::before {
    content: "";
    position: absolute; inset: 0;
    background:
      radial-gradient(ellipse 700px 400px at 80% 20%, rgba(30, 64, 175, 0.06), transparent),
      radial-gradient(ellipse 500px 300px at 10% 80%, rgba(13, 148, 136, 0.05), transparent);
    pointer-events: none;
    z-index: 0;
  }
  .br-hero-inner { position: relative; z-index: 1; }
  .br-hero-grid { display: grid; grid-template-columns: 1.15fr 1fr; gap: 56px; align-items: center; }
  @media (max-width: 900px) {
    .br-hero-grid { grid-template-columns: 1fr; gap: 40px; }
  }

  .br-pill {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--br-blue-soft); color: var(--br-blue);
    padding: 8px 16px; border-radius: 999px;
    font-size: 12px; font-weight: 700; letter-spacing: 0.4px; text-transform: uppercase;
    margin-bottom: 24px;
  }
  .br-pill::before {
    content: ""; width: 6px; height: 6px; background: var(--br-blue); border-radius: 50%; animation: brPulse 2s ease-in-out infinite;
  }
  @keyframes brPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(0.85); }
  }

  .br-hero h1 {
    font-family: var(--font-display, "Bricolage Grotesque"), serif;
    font-size: clamp(36px, 5.5vw, 60px);
    font-weight: 800; line-height: 1.04; letter-spacing: -0.025em; margin: 0 0 24px; color: var(--text);
  }
  .br-hero h1 em {
    font-style: normal;
    background: linear-gradient(120deg, var(--br-blue) 0%, var(--brand) 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    color: transparent;
  }
  .br-hero-sub {
    font-size: clamp(16px, 2vw, 19px); line-height: 1.6; color: var(--text-soft, #7a7a72);
    margin: 0 0 32px; max-width: 540px;
  }

  .br-hero-cta-row { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 32px; }
  .br-cta-primary {
    display: inline-flex; align-items: center; gap: 10px;
    background: var(--text, #0d0d0b); color: #fff; padding: 18px 32px; border-radius: 14px;
    font-size: 16px; font-weight: 700; text-decoration: none;
    transition: all 0.2s var(--ease-out, cubic-bezier(0.16, 1, 0.3, 1));
    box-shadow: 0 4px 16px rgba(13, 13, 11, 0.15);
  }
  .br-cta-primary:hover {
    transform: translateY(-2px); box-shadow: 0 12px 28px rgba(13, 13, 11, 0.2); color: #fff;
  }
  .br-cta-primary::after { content: "→"; transition: transform 0.2s ease; }
  .br-cta-primary:hover::after { transform: translateX(4px); }
  .br-cta-secondary {
    display: inline-flex; align-items: center; gap: 8px;
    color: var(--text); padding: 18px 24px; font-size: 15px; font-weight: 600; text-decoration: none;
    border-bottom: 2px solid transparent; transition: all 0.15s ease;
  }
  .br-cta-secondary:hover { border-bottom-color: var(--br-blue); color: var(--br-blue); }

  .br-trust-row { display: flex; gap: 24px; flex-wrap: wrap; color: var(--text-soft, #7a7a72); font-size: 14px; }
  .br-trust-item { display: inline-flex; align-items: center; gap: 8px; }
  .br-trust-item::before { content: "✓"; color: var(--br-blue); font-weight: 800; font-size: 16px; }

  .br-pipeline {
    background: #fff; border: 1px solid var(--border, #e8e8e0); border-radius: var(--br-radius); padding: 24px;
    box-shadow: var(--shadow, 0 22px 60px rgba(15, 23, 42, 0.1)); position: relative; transform: rotate(0.5deg);
  }
  .br-pipeline::before {
    content: "Pipeline klientow Q1 2026";
    position: absolute; top: -12px; right: 24px;
    background: linear-gradient(135deg, var(--br-blue) 0%, var(--br-navy) 100%); color: #fff;
    padding: 6px 14px; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;
  }
  .br-pipeline-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid var(--border); }
  .br-pipeline-name { font-family: var(--font-display); font-size: 13px; font-weight: 700; color: var(--text-muted); }
  .br-pipeline-status { background: var(--success-soft, #f0faf4); color: var(--success, #15803d); padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; }
  .br-stages { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 16px; }
  .br-stage { background: var(--bg-alt, #f2f2ec); padding: 12px 10px; border-radius: 10px; text-align: center; }
  .br-stage-label { font-size: 9.5px; font-weight: 700; color: var(--text-soft); letter-spacing: 0.3px; text-transform: uppercase; margin-bottom: 4px; }
  .br-stage-val { font-family: var(--font-display); font-size: 22px; font-weight: 800; color: var(--text); font-variant-numeric: tabular-nums; margin-bottom: 2px; }
  .br-stage-trend { font-size: 10px; font-weight: 700; color: var(--success); }
  .br-segments-section { background: linear-gradient(135deg, #f0f9ff 0%, #fff 100%); border-radius: 10px; padding: 14px 16px; }
  .br-segments-title { font-size: 11px; font-weight: 700; color: var(--text-muted); letter-spacing: 0.4px; text-transform: uppercase; margin-bottom: 10px; }
  .br-segment { display: flex; align-items: center; justify-content: space-between; padding: 6px 0; font-size: 12.5px; }
  .br-segment:not(:last-child) { border-bottom: 1px dashed var(--border); }
  .br-segment-name { display: flex; align-items: center; gap: 8px; font-weight: 600; color: var(--text); }
  .br-segment-dot { width: 8px; height: 8px; border-radius: 50%; }
  .br-segment-stats { display: flex; gap: 12px; font-variant-numeric: tabular-nums; }
  .br-segment-count { font-weight: 700; color: var(--text); }
  .br-segment-value { color: var(--text-soft); font-size: 11px; }

  .br-section { padding: clamp(60px, 8vw, 100px) 0; position: relative; }
  .br-section-soft { background: var(--surface, #fff); }
  .br-section-head { max-width: 720px; margin: 0 0 56px; }
  .br-section-head.is-center { margin: 0 auto 56px; text-align: center; }
  .br-section-eyebrow { font-size: 12px; font-weight: 700; color: var(--br-blue); letter-spacing: 0.8px; text-transform: uppercase; margin-bottom: 12px; display: inline-block; }
  .br-section h2 { font-family: var(--font-display); font-size: clamp(28px, 3.8vw, 42px); font-weight: 800; line-height: 1.15; letter-spacing: -0.018em; margin: 0 0 16px; color: var(--text); }
  .br-section-intro { font-size: clamp(16px, 1.6vw, 18px); line-height: 1.65; color: var(--text-soft); margin: 0; }

  .br-problems { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin-top: 16px; }
  @media (max-width: 800px) { .br-problems { grid-template-columns: 1fr; } }
  .br-problem {
    background: var(--surface); border: 1px solid var(--border); border-radius: var(--br-radius); padding: 28px; transition: all 0.25s var(--ease-out);
  }
  .br-problem:hover { transform: translateY(-3px); border-color: var(--br-blue); box-shadow: var(--shadow-soft); }
  .br-problem-quote {
    font-family: var(--font-display); font-size: 22px; font-weight: 700; line-height: 1.3; color: var(--text);
    margin: 0 0 14px; position: relative; padding-top: 24px;
  }
  .br-problem-quote::before {
    content: '"';
    position: absolute; top: -10px; left: -8px; font-size: 60px; color: var(--br-blue); line-height: 1; font-family: serif; opacity: 0.4;
  }
  .br-problem p { font-size: 14px; line-height: 1.6; color: var(--text-soft); margin: 0; }

  .br-niches { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 16px; }
  @media (max-width: 800px) { .br-niches { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 500px) { .br-niches { grid-template-columns: 1fr; } }
  .br-niche {
    background: var(--surface); border: 1px solid var(--border); border-radius: var(--br-radius); padding: 24px; transition: all 0.25s var(--ease-out); text-align: center;
  }
  .br-niche:hover { border-color: var(--br-blue); transform: translateY(-3px); box-shadow: var(--shadow-soft); }
  .br-niche-icon {
    width: 48px; height: 48px; margin: 0 auto 14px; border-radius: 12px; background: var(--br-blue-soft); color: var(--br-blue);
    display: grid; place-items: center; font-size: 22px;
  }
  .br-niche h4 { font-family: var(--font-display); font-size: 15px; font-weight: 700; margin: 0 0 6px; }
  .br-niche p { font-size: 12.5px; line-height: 1.5; color: var(--text-soft); margin: 0; }

  .br-package-wrap { display: grid; grid-template-columns: 1.2fr 1fr; gap: 24px; margin-top: 16px; }
  @media (max-width: 800px) { .br-package-wrap { grid-template-columns: 1fr; } }
  .br-package {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 1px solid var(--br-blue-line); border-radius: var(--br-radius-lg);
    padding: clamp(28px, 4vw, 44px); position: relative;
  }
  .br-package-tag {
    display: inline-block; background: var(--br-blue); color: #fff; padding: 6px 14px; border-radius: 999px;
    font-size: 11px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 16px;
  }
  .br-package h3 { font-family: var(--font-display); font-size: clamp(22px, 2.5vw, 30px); font-weight: 800; line-height: 1.2; margin: 0 0 12px; color: var(--text); }
  .br-package-desc { font-size: 15px; line-height: 1.6; color: var(--text-muted); margin: 0 0 28px; }
  .br-package-list { margin: 0; padding: 0; list-style: none; }
  .br-package-list li {
    padding: 10px 0 10px 32px; position: relative; font-size: 15px; line-height: 1.5; color: var(--text);
    border-bottom: 1px dashed rgba(30, 64, 175, 0.15);
  }
  .br-package-list li:last-child { border-bottom: 0; }
  .br-package-list li::before {
    content: "✓"; position: absolute; left: 0; top: 8px; width: 22px; height: 22px; background: var(--br-blue); color: #fff;
    border-radius: 50%; display: grid; place-items: center; font-size: 12px; font-weight: 800;
  }

  .br-pricing {
    background: var(--surface); border: 2px solid var(--text); border-radius: var(--br-radius-lg); padding: clamp(28px, 4vw, 36px);
    text-align: center; position: sticky; top: 24px;
  }
  .br-pricing-label { font-size: 11px; font-weight: 700; color: var(--text-soft); letter-spacing: 0.6px; text-transform: uppercase; margin-bottom: 8px; }
  .br-pricing-amount { font-family: var(--font-display); font-size: clamp(36px, 5vw, 52px); font-weight: 800; line-height: 1; color: var(--text); margin-bottom: 4px; }
  .br-pricing-amount small { font-size: 16px; font-weight: 600; color: var(--text-soft); margin-left: 4px; }
  .br-pricing-period { font-size: 13px; color: var(--text-soft); margin-bottom: 24px; }
  .br-pricing-extras { margin: 24px 0 0; padding: 20px 0 0; border-top: 1px solid var(--border); text-align: left; list-style: none; }
  .br-pricing-extras li { padding: 6px 0 6px 24px; position: relative; font-size: 13px; color: var(--text-muted); line-height: 1.5; }
  .br-pricing-extras li::before { content: "+"; position: absolute; left: 0; color: var(--br-blue); font-weight: 800; }
  .br-pricing-cta {
    display: block; width: 100%; padding: 16px; background: var(--text); color: #fff; border-radius: 12px; font-weight: 700;
    font-size: 15px; text-decoration: none; transition: all 0.2s ease; margin-top: 24px;
  }
  .br-pricing-cta:hover { background: var(--br-blue); color: #fff; transform: translateY(-1px); }

  .br-honest {
    background: linear-gradient(135deg, var(--accent-soft, #fff7ed) 0%, #fff 100%);
    border: 1px solid var(--accent-line, #fed7aa); border-radius: var(--br-radius); padding: clamp(28px, 4vw, 44px); margin-top: 32px;
  }
  .br-honest-header { display: flex; gap: 16px; align-items: center; margin-bottom: 20px; }
  .br-honest-icon {
    flex-shrink: 0; width: 44px; height: 44px; border-radius: 12px; background: var(--accent, #f97316); color: #fff;
    display: grid; place-items: center; font-size: 22px; font-weight: 800;
  }
  .br-honest-header strong { font-family: var(--font-display); font-size: 18px; font-weight: 700; }
  .br-honest ul { margin: 0; padding: 0; list-style: none; display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px 24px; }
  @media (max-width: 600px) { .br-honest ul { grid-template-columns: 1fr; } }
  .br-honest li { padding: 10px 0 10px 28px; position: relative; font-size: 15px; line-height: 1.5; color: var(--text-muted); }
  .br-honest li::before { content: "✗"; position: absolute; left: 0; top: 8px; color: var(--accent); font-weight: 800; font-size: 18px; }

  .br-steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: 16px; }
  @media (max-width: 800px) { .br-steps { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 500px) { .br-steps { grid-template-columns: 1fr; } }
  .br-step {
    background: var(--surface, #fff); border: 1px solid var(--border); border-radius: var(--br-radius); padding: 24px; position: relative;
    transition: all 0.25s var(--ease-out);
  }
  .br-step:hover { transform: translateY(-3px); border-color: var(--br-blue); box-shadow: var(--shadow-soft); }
  .br-step-num { font-family: var(--font-display); font-size: 32px; font-weight: 800; line-height: 1; color: var(--br-blue); margin-bottom: 16px; font-variant-numeric: tabular-nums; }
  .br-step h4 { font-family: var(--font-display); font-size: 16px; font-weight: 700; line-height: 1.3; margin: 0 0 8px; }
  .br-step p { font-size: 13px; line-height: 1.6; color: var(--text-soft); margin: 0; }
  .br-step-time {
    display: inline-block; margin-top: 12px; font-size: 11px; font-weight: 700; color: var(--text-soft);
    letter-spacing: 0.5px; text-transform: uppercase; padding: 4px 8px; background: var(--bg-alt, #f2f2ec); border-radius: 6px;
  }

  .br-example-wrap {
    background: linear-gradient(135deg, var(--br-navy) 0%, #1e293b 100%); color: #fff; border-radius: var(--br-radius-lg);
    padding: clamp(36px, 5vw, 56px); margin-top: 16px; position: relative; overflow: hidden;
  }
  .br-example-wrap::before {
    content: ""; position: absolute; top: -100px; right: -100px; width: 400px; height: 400px;
    background: radial-gradient(circle, rgba(30, 64, 175, 0.18) 0%, transparent 60%); pointer-events: none;
  }
  .br-example-wrap > * { position: relative; }
  .br-example-eyebrow { font-size: 11px; font-weight: 700; color: #93c5fd; letter-spacing: 0.8px; text-transform: uppercase; margin-bottom: 14px; }
  .br-example-wrap h3 { font-family: var(--font-display); font-size: clamp(22px, 2.5vw, 30px); font-weight: 800; line-height: 1.25; margin: 0 0 12px; color: #fff; }
  .br-example-context { color: rgba(255, 255, 255, 0.7); font-size: 15px; line-height: 1.6; margin: 0 0 32px; max-width: 600px; }
  .br-comparison { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 32px; }
  @media (max-width: 700px) { .br-comparison { grid-template-columns: 1fr; } }
  .br-state {
    background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.1); backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px); border-radius: 16px; padding: 24px;
  }
  .br-state.is-after { background: rgba(30, 64, 175, 0.18); border-color: rgba(96, 165, 250, 0.35); }
  .br-state-label {
    display: inline-block; font-size: 10px; font-weight: 700; letter-spacing: 0.6px; text-transform: uppercase;
    background: rgba(255, 255, 255, 0.1); color: #fff; padding: 4px 8px; border-radius: 6px; margin-bottom: 14px;
  }
  .br-state.is-after .br-state-label { background: var(--br-blue); }
  .br-state h4 { font-family: var(--font-display); font-size: 15px; font-weight: 700; margin: 0 0 14px; color: #fff; }
  .br-state-metric {
    padding: 10px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.1); display: flex; justify-content: space-between; align-items: center;
    font-size: 13px; color: rgba(255, 255, 255, 0.85);
  }
  .br-state-metric:last-child { border-bottom: 0; }
  .br-state-metric strong { font-family: var(--font-display); font-variant-numeric: tabular-nums; font-weight: 800; font-size: 15px; }
  .br-state.is-after .br-state-metric strong { color: #93c5fd; }
  .br-example-summary {
    border-top: 1px solid rgba(255, 255, 255, 0.12); padding-top: 28px; display: flex; justify-content: space-between;
    align-items: center; flex-wrap: wrap; gap: 20px;
  }
  .br-example-summary-text { color: rgba(255, 255, 255, 0.85); font-size: 15px; line-height: 1.5; max-width: 520px; }
  .br-example-summary-amount { font-family: var(--font-display); font-size: clamp(28px, 3.5vw, 38px); font-weight: 800; color: #fff; text-align: right; }
  .br-example-summary-amount small {
    display: block; font-size: 12px; font-weight: 600; color: rgba(255, 255, 255, 0.6); letter-spacing: 0.4px;
    text-transform: uppercase; margin-top: 4px;
  }

  .br-target { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
  @media (max-width: 700px) { .br-target { grid-template-columns: 1fr; } }
  .br-target-col { padding: 28px; border-radius: var(--br-radius); border: 1px solid var(--border); }
  .br-target-col.is-yes { background: var(--br-blue-soft); border-color: rgba(30, 64, 175, 0.2); }
  .br-target-col.is-no { background: var(--bg-alt, #f2f2ec); }
  .br-target-col h4 {
    font-family: var(--font-display); font-size: 17px; font-weight: 700; margin: 0 0 16px; display: flex; align-items: center; gap: 8px;
  }
  .br-target-col h4::before {
    width: 24px; height: 24px; border-radius: 50%; display: grid; place-items: center; font-size: 14px; font-weight: 800; flex-shrink: 0;
  }
  .br-target-col.is-yes h4::before { content: "✓"; background: var(--br-blue); color: #fff; }
  .br-target-col.is-no h4::before { content: "—"; background: var(--text-soft); color: #fff; }
  .br-target-col ul { margin: 0; padding: 0; list-style: none; }
  .br-target-col li { padding: 8px 0; font-size: 14px; line-height: 1.55; color: var(--text-muted); }

  .br-faq-list { max-width: 760px; margin: 0 auto; }
  .br-faq {
    background: var(--surface); border: 1px solid var(--border); border-radius: var(--br-radius); margin-bottom: 12px; overflow: hidden;
    transition: border-color 0.2s ease;
  }
  .br-faq:hover { border-color: var(--br-blue); }
  .br-faq[open] { border-color: var(--br-blue); box-shadow: var(--shadow-sm); }
  .br-faq summary {
    padding: 22px 28px; cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center; gap: 16px;
    font-family: var(--font-display); font-size: 16px; font-weight: 700; line-height: 1.4; color: var(--text); user-select: none;
  }
  .br-faq summary::-webkit-details-marker { display: none; }
  .br-faq summary::after {
    content: "+"; flex-shrink: 0; width: 28px; height: 28px; border-radius: 50%; background: var(--br-blue-soft); color: var(--br-blue);
    display: grid; place-items: center; font-size: 18px; font-weight: 400; transition: all 0.2s ease;
  }
  .br-faq[open] summary::after { content: "−"; background: var(--br-blue); color: #fff; transform: rotate(180deg); }
  .br-faq-content { padding: 0 28px 24px; color: var(--text-soft); font-size: 15px; line-height: 1.7; }
  .br-faq-content p { margin: 0 0 12px; }
  .br-faq-content p:last-child { margin: 0; }

  .br-author {
    background: var(--sand, #f5f0e8); border-radius: var(--br-radius-lg); padding: clamp(36px, 5vw, 56px);
    display: grid; grid-template-columns: auto 1fr; gap: 32px; align-items: center; margin-top: 16px;
  }
  @media (max-width: 700px) { .br-author { grid-template-columns: 1fr; text-align: center; } }
  .br-author-photo {
    width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, var(--br-blue) 0%, var(--br-navy) 100%);
    color: #fff; display: grid; place-items: center; font-family: var(--font-display); font-size: 42px; font-weight: 800; flex-shrink: 0; margin: 0 auto;
  }
  .br-author-content h3 { font-family: var(--font-display); font-size: 22px; font-weight: 800; margin: 0 0 8px; }
  .br-author-role { font-size: 14px; font-weight: 600; color: var(--br-blue); margin: 0 0 16px; }
  .br-author-content p { font-size: 15px; line-height: 1.65; color: var(--text-muted); margin: 0; }

  .br-final {
    background: linear-gradient(180deg, var(--text, #0d0d0b) 0%, var(--br-navy) 100%); color: #fff;
    padding: clamp(60px, 9vw, 96px) 0; position: relative; overflow: hidden;
  }
  .br-final::before {
    content: ""; position: absolute; top: -100px; left: 50%; transform: translateX(-50%); width: 600px; height: 600px;
    background: radial-gradient(circle, rgba(30, 64, 175, 0.18) 0%, transparent 70%); pointer-events: none;
  }
  .br-final-inner { position: relative; max-width: 640px; margin: 0 auto; text-align: center; }
  .br-final h2 { color: #fff; margin-bottom: 16px; }
  .br-final-sub { font-size: 17px; line-height: 1.6; color: rgba(255, 255, 255, 0.7); margin: 0 0 40px; }

  .br-form {
    background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.1); backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px); border-radius: var(--br-radius); padding: clamp(24px, 4vw, 36px); text-align: left;
  }
  .br-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  @media (max-width: 600px) { .br-form-grid { grid-template-columns: 1fr; } }
  .br-field { margin-bottom: 16px; }
  .br-field-full { grid-column: 1 / -1; }
  .br-field label { display: block; font-size: 13px; font-weight: 600; color: rgba(255, 255, 255, 0.85); margin-bottom: 8px; }
  .br-field label .opt { font-weight: 400; color: rgba(255, 255, 255, 0.4); font-size: 12px; margin-left: 6px; }
  .br-field input,
  .br-field textarea,
  .br-field select {
    width: 100%; padding: 14px 16px; background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.15);
    color: #fff; border-radius: 12px; font-size: 15px; font-family: inherit; transition: all 0.2s ease;
  }
  .br-field input::placeholder,
  .br-field textarea::placeholder { color: rgba(255, 255, 255, 0.35); }
  .br-field input:focus,
  .br-field textarea:focus,
  .br-field select:focus { outline: none; border-color: var(--br-blue); background: rgba(255, 255, 255, 0.1); }
  .br-field textarea { min-height: 90px; resize: vertical; }
  .br-form-submit {
    width: 100%; padding: 18px; background: var(--br-blue); color: #fff; border: 0; border-radius: 14px; font-weight: 700; font-size: 16px;
    cursor: pointer; transition: all 0.2s ease; margin-top: 8px; font-family: inherit;
  }
  .br-form-submit:hover { background: var(--br-navy); transform: translateY(-2px); box-shadow: 0 12px 24px rgba(30, 64, 175, 0.3); }
  .br-form-meta { text-align: center; margin-top: 20px; font-size: 12px; color: rgba(255, 255, 255, 0.4); line-height: 1.5; }
  .br-form-feedback {
    margin-bottom: 20px; padding: 12px 14px; border-radius: 10px; font-size: 14px;
  }
  .br-form-feedback.is-success {
    background: rgba(22, 163, 74, 0.18); border: 1px solid rgba(34, 197, 94, 0.45); color: #dcfce7;
  }
  .br-form-feedback.is-error {
    background: rgba(220, 38, 38, 0.18); border: 1px solid rgba(248, 113, 113, 0.45); color: #fee2e2;
  }
</style>

<div class="br-page">
  <section class="br-hero">
    <div class="br-container br-hero-inner">
      <div class="br-hero-grid">
        <div>
          <div class="br-pill">Marketing dla biur rachunkowych</div>
          <h1>Klienci, ktorzy zostaja <em>na lata</em>, a nie na miesiac</h1>
          <p class="br-hero-sub">
            Reklama Google + LinkedIn + strona, ktora konwertuje przedsiebiorcow na bezplatna konsultacje. Skupiamy sie na jakosci klientow, nie ich ilosci.
          </p>
          <div class="br-hero-cta-row">
            <a href="#kontakt" class="br-cta-primary" data-cta="hero-umow-rozmowe">Umow rozmowe</a>
            <a href="#oferta" class="br-cta-secondary" data-cta="hero-zobacz-pakiet">Zobacz pakiet</a>
          </div>
          <div class="br-trust-row">
            <span class="br-trust-item">Pierwsi leadzi w 30 dni</span>
            <span class="br-trust-item">Bez umow na rok</span>
            <span class="br-trust-item">Pierwsza konsultacja gratis</span>
          </div>
        </div>
        <div>
          <div class="br-pipeline" aria-hidden="true">
            <div class="br-pipeline-header">
              <span class="br-pipeline-name">Biuro Rachunkowe MRA — Poznan</span>
              <span class="br-pipeline-status">Po 90 dniach</span>
            </div>
            <div class="br-stages">
              <div class="br-stage"><div class="br-stage-label">Nowi leadzi</div><div class="br-stage-val">28</div><div class="br-stage-trend">+340%</div></div>
              <div class="br-stage"><div class="br-stage-label">Konsultacje</div><div class="br-stage-val">19</div><div class="br-stage-trend">+280%</div></div>
              <div class="br-stage"><div class="br-stage-label">Nowi klienci</div><div class="br-stage-val">11</div><div class="br-stage-trend">+450%</div></div>
            </div>
            <div class="br-segments-section">
              <div class="br-segments-title">Segmenty nowych klientow</div>
              <div class="br-segment"><div class="br-segment-name"><span class="br-segment-dot" style="background: var(--br-blue);"></span>Spolki z o.o.</div><div class="br-segment-stats"><span class="br-segment-count">5</span><span class="br-segment-value">~1 200 zl/mc</span></div></div>
              <div class="br-segment"><div class="br-segment-name"><span class="br-segment-dot" style="background: #14b8a6;"></span>IT / freelancerzy</div><div class="br-segment-stats"><span class="br-segment-count">4</span><span class="br-segment-value">~600 zl/mc</span></div></div>
              <div class="br-segment"><div class="br-segment-name"><span class="br-segment-dot" style="background: #f59e0b;"></span>E-commerce</div><div class="br-segment-stats"><span class="br-segment-count">2</span><span class="br-segment-value">~1 400 zl/mc</span></div></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="br-section br-section-soft">
    <div class="br-container">
      <div class="br-section-head">
        <span class="br-section-eyebrow">Jesli ktore brzmia znajomo</span>
        <h2>Trzy najczestsze problemy biur rachunkowych</h2>
        <p class="br-section-intro">To problemy, ktore regularnie pojawiaja sie w rozmowach z wlascicielami biur rachunkowych.</p>
      </div>
      <div class="br-problems">
        <div class="br-problem">
          <p class="br-problem-quote">Klienci wpadaja z polecenia. Czasem dwoch w miesiacu, czasem zero.</p>
          <p>Polecenia sa nieprzewidywalne. Trudno na nich planowac wzrost i zatrudnienie.</p>
        </div>
        <div class="br-problem">
          <p class="br-problem-quote">Konkurencja gra cena — 99 zl za JDG, smiech na sali.</p>
          <p>Klient porownuje cennik, nie kompetencje. Tracisz leady mimo dobrej obslugi.</p>
        </div>
        <div class="br-problem">
          <p class="br-problem-quote">Mam strone z 2018 roku. Sam bym jej nie zaufal.</p>
          <p>Generyczny przekaz bez jasnego pozycjonowania i bezpowrotna utrata leadow.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="br-section">
    <div class="br-container">
      <div class="br-section-head">
        <span class="br-section-eyebrow">Klucz do wzrostu</span>
        <h2>Specjalizacja branzowa to Twoja przewaga</h2>
        <p class="br-section-intro">Klient nie szuka "biura rachunkowego". Klient szuka ksiegowego dla swojej branzy i swoich problemow.</p>
      </div>
      <div class="br-niches">
        <div class="br-niche"><div class="br-niche-icon">💻</div><h4>IT / freelancerzy</h4><p>JDG, B2B, kontrakty zagraniczne, IP Box</p></div>
        <div class="br-niche"><div class="br-niche-icon">🛒</div><h4>E-commerce</h4><p>VAT-OSS, sprzedaz UE, Allegro/Amazon</p></div>
        <div class="br-niche"><div class="br-niche-icon">🍝</div><h4>Gastronomia</h4><p>Kasy fiskalne, dostawy, sezonowosc</p></div>
        <div class="br-niche"><div class="br-niche-icon">🏗️</div><h4>Budowlanka</h4><p>Podwykonawcy, gwarancje, rozliczenia</p></div>
        <div class="br-niche"><div class="br-niche-icon">🚚</div><h4>Transport</h4><p>Diety, ZUS, rozliczenia tras</p></div>
        <div class="br-niche"><div class="br-niche-icon">⚕️</div><h4>Medycyna prywatna</h4><p>Stawki VAT, kontrakty i koszty sprzetu</p></div>
      </div>
    </div>
  </section>

  <section class="br-section br-section-soft" id="oferta">
    <div class="br-container">
      <div class="br-section-head">
        <span class="br-section-eyebrow">Co konkretnie robie</span>
        <h2>Pakiet "Stabilny portfel klientow"</h2>
        <p class="br-section-intro">Jedna miesieczna stawka i nacisk na jakosc leadow, bo liczy sie klient na lata.</p>
      </div>
      <div class="br-package-wrap">
        <div class="br-package">
          <span class="br-package-tag">Pakiet startowy</span>
          <h3>Strona, reklamy i pipeline, ktory dowozi klientow</h3>
          <p class="br-package-desc">
            Nowa/odswiezona strona + Google Ads + LinkedIn Ads + landingi dla 2-3 specjalizacji.
          </p>
          <ul class="br-package-list">
            <li>Nowa strona lub redesign istniejacej</li>
            <li>2-3 landingi dla wybranych specjalizacji</li>
            <li>Google Ads na frazy lokalne i branzowe</li>
            <li>LinkedIn Ads dla decyzyjnych</li>
            <li>Optymalizacja profilu Google Maps</li>
            <li>2 artykuly blogowe miesiecznie</li>
            <li>Email marketing i cross-sell</li>
            <li>Cotygodniowa optymalizacja + raport miesieczny</li>
          </ul>
        </div>
        <div class="br-pricing">
          <div class="br-pricing-label">Miesieczna oplata</div>
          <div class="br-pricing-amount">2 800<small>zl</small></div>
          <div class="br-pricing-period">+ budzet reklamowy od 1 500 zl/mies</div>
          <ul class="br-pricing-extras">
            <li>Pierwsza konsultacja gratis (60 min)</li>
            <li>Bez umow na 12 miesiecy</li>
            <li>Strona/landingi w pierwszym miesiacu w cenie</li>
            <li>Nowy klient zwykle zwraca pakiet wielokrotnie</li>
          </ul>
          <a href="#kontakt" class="br-pricing-cta" data-cta="oferta-umow-konsultacje">Umow konsultacje →</a>
        </div>
      </div>
    </div>
  </section>

  <section class="br-section">
    <div class="br-container">
      <div class="br-section-head">
        <span class="br-section-eyebrow">Szczerze</span>
        <h2>Czego NIE dostaniesz (i dlaczego dobrze)</h2>
      </div>
      <div class="br-honest">
        <div class="br-honest-header"><div class="br-honest-icon">!</div><strong>W pakiecie nie znajdziesz:</strong></div>
        <ul>
          <li>Obietnic "100 leadow miesiecznie"</li>
          <li>Promocji "JDG za 49 zl"</li>
          <li>Cold mailingu do losowych firm</li>
          <li>Konkursow na Facebooku</li>
          <li>Pustych tresci bez intencji zakupowej</li>
          <li>Sztucznych wzrostow bez jakosci</li>
        </ul>
      </div>
    </div>
  </section>

  <section class="br-section br-section-soft">
    <div class="br-container">
      <div class="br-section-head">
        <span class="br-section-eyebrow">Jak zaczynamy</span>
        <h2>Cztery kroki od rozmowy do pierwszych leadow</h2>
      </div>
      <div class="br-steps">
        <div class="br-step"><div class="br-step-num">01</div><h4>Bezplatna konsultacja</h4><p>Analiza portfela klientow i specjalizacji.</p><span class="br-step-time">Tydzien 1</span></div>
        <div class="br-step"><div class="br-step-num">02</div><h4>Strategia + strona</h4><p>Plan 90 dni i uporzadkowanie komunikacji.</p><span class="br-step-time">Tygodnie 2-4</span></div>
        <div class="br-step"><div class="br-step-num">03</div><h4>Start kampanii</h4><p>Google Ads + LinkedIn Ads dla konkretnych segmentow.</p><span class="br-step-time">Miesiac 1-2</span></div>
        <div class="br-step"><div class="br-step-num">04</div><h4>Skalowanie</h4><p>Regularna optymalizacja pod jakosc leadow.</p><span class="br-step-time">Cyklicznie</span></div>
      </div>
    </div>
  </section>

  <section class="br-section">
    <div class="br-container">
      <div class="br-section-head">
        <span class="br-section-eyebrow">Konkret zamiast obietnic</span>
        <h2>Pierwsze 90 dni biura z Poznania</h2>
      </div>
      <div class="br-example-wrap">
        <div class="br-example-eyebrow">Biuro Rachunkowe MRA — Poznan</div>
        <h3>Z 0 leadow online do 28 w kwartale</h3>
        <p class="br-example-context">Pozycjonowanie na specjalizacje i kampanie kierowane do decyzyjnych calkowicie zmienily pipeline.</p>
        <div class="br-comparison">
          <div class="br-state">
            <span class="br-state-label">Przed</span>
            <h4>Q4 2025</h4>
            <div class="br-state-metric"><span>Nowi leadzi / mies</span><strong>2-3</strong></div>
            <div class="br-state-metric"><span>Z polecenia</span><strong>100%</strong></div>
            <div class="br-state-metric"><span>Nowi klienci / kwartal</span><strong>2</strong></div>
            <div class="br-state-metric"><span>Formularze strony</span><strong>0-1/mies</strong></div>
          </div>
          <div class="br-state is-after">
            <span class="br-state-label">Po 90 dniach</span>
            <h4>Q1 2026</h4>
            <div class="br-state-metric"><span>Nowi leadzi / mies</span><strong>9-10</strong></div>
            <div class="br-state-metric"><span>Z reklam</span><strong>~60%</strong></div>
            <div class="br-state-metric"><span>Nowi klienci / kwartal</span><strong>11</strong></div>
            <div class="br-state-metric"><span>Formularze strony</span><strong>9-12/mies</strong></div>
          </div>
        </div>
        <div class="br-example-summary">
          <div class="br-example-summary-text">
            Trzy zmiany: nowa strona, kampanie Google/LinkedIn, segmentacja na IT, e-commerce i spolki z o.o.
          </div>
          <div class="br-example-summary-amount">~720 000 zl<small>LTV nowych klientow (5 lat)</small></div>
        </div>
      </div>
    </div>
  </section>

  <section class="br-section br-section-soft">
    <div class="br-container">
      <div class="br-section-head">
        <span class="br-section-eyebrow">Dla kogo</span>
        <h2>Dla kogo to ma sens</h2>
      </div>
      <div class="br-target">
        <div class="br-target-col is-yes">
          <h4>Pakiet ma sens jesli:</h4>
          <ul>
            <li>→ Biuro dziala min. 2 lata</li>
            <li>→ Przychod min. 30 000 zl/mies</li>
            <li>→ Masz 1+ specjalizacje branzowa</li>
            <li>→ Stac Cie na 2 800 zl + budzet reklamowy</li>
            <li>→ Chcesz regularnego wzrostu</li>
          </ul>
        </div>
        <div class="br-target-col is-no">
          <h4>Pakiet NIE ma sensu jesli:</h4>
          <ul>
            <li>→ Biuro dopiero startuje</li>
            <li>→ Nie masz mocy operacyjnej na nowych klientow</li>
            <li>→ Oferujesz "wszystko dla wszystkich"</li>
            <li>→ Konkurujesz glownie cena</li>
            <li>→ Slabe opinie Google i brak podstaw</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section class="br-section">
    <div class="br-container">
      <div class="br-section-head">
        <span class="br-section-eyebrow">Kto bedzie z Toba pracowal</span>
        <h2>Po drugiej stronie ekranu</h2>
      </div>
      <div class="br-author">
        <div class="br-author-photo">SK</div>
        <div class="br-author-content">
          <h3>Sebastian Kelm</h3>
          <p class="br-author-role">Konsultant marketingu — Google Ads · LinkedIn Ads · strony B2B</p>
          <p>Pracuje bezposrednio z klientami i buduje marketing pod jakosc leadow i retencje, nie pod metryki proznosci.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="br-section br-section-soft">
    <div class="br-container">
      <div class="br-section-head is-center">
        <span class="br-section-eyebrow">FAQ</span>
        <h2>Pytania, ktore dostaje najczesciej</h2>
      </div>
      <div class="br-faq-list">
        <details class="br-faq">
          <summary>Ile to kosztuje miesiecznie razem z reklamami?</summary>
          <div class="br-faq-content"><p>Pakiet: <strong>2 800 zl netto/mies</strong> + budzet reklamowy od 1 500 zl.</p></div>
        </details>
        <details class="br-faq">
          <summary>Po jakim czasie zobacze pierwszych klientow?</summary>
          <div class="br-faq-content"><p>Pierwsze formularze zwykle 14-30 dni, pierwsze umowy zwykle 30-90 dni.</p></div>
        </details>
        <details class="br-faq">
          <summary>Czy LinkedIn Ads ma sens dla biura rachunkowego?</summary>
          <div class="br-faq-content"><p>Tak, glownie dla segmentu spolek i decyzyjnych. Dla JDG czesciej wygrywa Google Ads.</p></div>
        </details>
      </div>
    </div>
  </section>

  <section class="br-final" id="kontakt">
    <div class="br-container br-final-inner">
      <h2>Umow bezplatna konsultacje</h2>
      <p class="br-final-sub">60 minut online. Oceniam, czy reklamy maja sens w Twoim przypadku i jaka specjalizacja powinna byc osia komunikacji.</p>

      <form class="br-form" method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" data-form="biuro-rachunkowe">
        <input type="hidden" name="action" value="upsellio_submit_lead">
        <?php wp_nonce_field("upsellio_unified_lead_form", "upsellio_unified_lead_nonce"); ?>
        <input type="hidden" name="lead_form_origin" value="biuro-rachunkowe-form">
        <input type="hidden" name="lead_source" value="biuro-rachunkowe-form">
        <input type="hidden" name="lead_service" value="Marketing biura rachunkowego">
        <input type="hidden" name="lead_goal" value="Pozyskanie klientow B2B o wysokim LTV dla biura rachunkowego">
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
          <div class="br-form-feedback is-success">Dziekuje! Formularz zostal wyslany. Odezwiemy sie najszybciej jak to mozliwe.</div>
        <?php elseif ($status === "error") : ?>
          <div class="br-form-feedback is-error">Nie udalo sie wyslac formularza. Sprobuj ponownie za chwile.</div>
        <?php endif; ?>

        <div class="br-form-grid">
          <div class="br-field">
            <label for="br_name">Imie</label>
            <input type="text" id="br_name" name="lead_name" placeholder="Anna" required>
          </div>
          <div class="br-field">
            <label for="br_email">Email</label>
            <input type="email" id="br_email" name="lead_email" placeholder="anna@biuro.pl" required>
          </div>
          <div class="br-field br-field-full">
            <label for="br_business">Nazwa biura i miasto</label>
            <input type="text" id="br_business" name="lead_company" placeholder="np. Biuro Rachunkowe Anna Nowak — Poznan" required>
          </div>
          <div class="br-field">
            <label for="br_size">Liczba stalych klientow</label>
            <select id="br_size" name="lead_goal_detail">
              <option value="">— wybierz —</option>
              <option value="<20">ponizej 20 klientow</option>
              <option value="20-50">20-50 klientow</option>
              <option value="50-100">50-100 klientow</option>
              <option value=">100">powyzej 100 klientow</option>
            </select>
          </div>
          <div class="br-field">
            <label for="br_speciality">Glowna specjalizacja <span class="opt">opcjonalnie</span></label>
            <input type="text" id="br_speciality" name="lead_source_detail" placeholder="np. spolki z o.o., e-commerce, IT">
          </div>
          <div class="br-field br-field-full">
            <label for="br_message">Co Cie najbardziej trapi? <span class="opt">opcjonalnie, ale pomaga</span></label>
            <textarea id="br_message" name="lead_message" placeholder="np. Klienci wpadaja tylko z polecenia, nie da sie planowac."></textarea>
          </div>
        </div>

        <div class="br-field br-field-full">
          <input type="checkbox" id="br_consent" name="lead_consent" value="1" required style="margin-right: 8px;">
          <label for="br_consent" style="display: inline; font-size: 13px;">Zgadzam sie na przetwarzanie danych osobowych w celu kontaktu.</label>
        </div>

        <button type="submit" class="br-form-submit" data-cta="form-submit">Umow bezplatna konsultacje →</button>
        <p class="br-form-meta">Twoje dane sluza wylacznie do umowienia konsultacji. Nie zapisuje Cie na newsletter i nie sprzedaje bazy.</p>
      </form>
    </div>
  </section>
</div>

<?php get_footer(); ?>
