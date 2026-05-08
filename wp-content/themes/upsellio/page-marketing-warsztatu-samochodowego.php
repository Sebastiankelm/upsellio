<?php
/**
 * Template Name: Marketing dla warsztatu samochodowego — landing
 *
 * Niszowy landing dla branzy motoryzacyjnej / warsztatow.
 */
get_header();
?>

<style>
  :root {
    --war-radius: 20px;
    --war-radius-lg: 28px;
    --war-red: #b91c1c;
    --war-red-soft: #fee2e2;
    --war-red-line: #fecaca;
    --war-charcoal: #292524;
    --war-yellow: #ca8a04;
  }

  .war-page { background: var(--bg, #fafaf6); color: var(--text, #0d0d0b); font-family: var(--font-body, "DM Sans"), system-ui, sans-serif; overflow-x: hidden; }
  .war-page * { box-sizing: border-box; }
  .war-container { max-width: 1100px; margin: 0 auto; padding: 0 24px; }

  .war-hero { position: relative; padding: clamp(60px, 10vw, 100px) 0 clamp(50px, 8vw, 80px); overflow: hidden; }
  .war-hero::before {
    content: "";
    position: absolute; inset: 0;
    background:
      radial-gradient(ellipse 700px 400px at 80% 20%, rgba(185, 28, 28, 0.06), transparent),
      radial-gradient(ellipse 500px 300px at 10% 80%, rgba(202, 138, 4, 0.05), transparent);
    pointer-events: none; z-index: 0;
  }
  .war-hero-inner { position: relative; z-index: 1; }
  .war-hero-grid { display: grid; grid-template-columns: 1.15fr 1fr; gap: 56px; align-items: center; }
  @media (max-width: 900px) { .war-hero-grid { grid-template-columns: 1fr; gap: 40px; } }

  .war-pill {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--war-red-soft); color: #7f1d1d;
    padding: 8px 16px; border-radius: 999px;
    font-size: 12px; font-weight: 700; letter-spacing: 0.4px; text-transform: uppercase;
    margin-bottom: 24px;
  }
  .war-pill::before {
    content: "";
    width: 6px; height: 6px;
    background: var(--war-red);
    border-radius: 50%;
    animation: warPulse 2s ease-in-out infinite;
  }
  @keyframes warPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(0.85); }
  }

  .war-hero h1 { font-family: var(--font-display, "Bricolage Grotesque"), serif; font-size: clamp(36px, 5.5vw, 60px); font-weight: 800; line-height: 1.04; letter-spacing: -0.025em; margin: 0 0 24px; color: var(--text); }
  .war-hero h1 em {
    font-style: normal; background: linear-gradient(120deg, var(--war-red) 0%, var(--war-yellow) 100%);
    -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; color: transparent;
  }
  .war-hero-sub { font-size: clamp(16px, 2vw, 19px); line-height: 1.6; color: var(--text-soft, #7a7a72); margin: 0 0 32px; max-width: 540px; }
  .war-hero-cta-row { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 32px; }
  .war-cta-primary {
    display: inline-flex; align-items: center; gap: 10px; background: var(--text, #0d0d0b); color: #fff; padding: 18px 32px; border-radius: 14px;
    font-size: 16px; font-weight: 700; text-decoration: none; transition: all 0.2s var(--ease-out, cubic-bezier(0.16, 1, 0.3, 1)); box-shadow: 0 4px 16px rgba(13, 13, 11, 0.15);
  }
  .war-cta-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(13, 13, 11, 0.2); color: #fff; }
  .war-cta-primary::after { content: "→"; transition: transform 0.2s ease; }
  .war-cta-primary:hover::after { transform: translateX(4px); }
  .war-cta-secondary { display: inline-flex; align-items: center; gap: 8px; color: var(--text); padding: 18px 24px; font-size: 15px; font-weight: 600; text-decoration: none; border-bottom: 2px solid transparent; transition: all 0.15s ease; }
  .war-cta-secondary:hover { border-bottom-color: var(--war-red); color: var(--war-red); }
  .war-trust-row { display: flex; gap: 24px; flex-wrap: wrap; color: var(--text-soft, #7a7a72); font-size: 14px; }
  .war-trust-item { display: inline-flex; align-items: center; gap: 8px; }
  .war-trust-item::before { content: "✓"; color: var(--war-red); font-weight: 800; font-size: 16px; }

  .war-mockup {
    background: #fff; border: 1px solid var(--border, #e8e8e0); border-radius: var(--war-radius); padding: 24px;
    box-shadow: var(--shadow, 0 22px 60px rgba(15, 23, 42, 0.1)); position: relative; transform: rotate(0.5deg);
  }
  .war-mockup::before {
    content: "Pierwsze 60 dni";
    position: absolute; top: -12px; right: 24px;
    background: linear-gradient(135deg, var(--war-red) 0%, #7f1d1d 100%);
    color: #fff; padding: 6px 14px; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;
  }
  .war-mockup-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--border); }
  .war-mockup-name { font-family: var(--font-display); font-size: 13px; font-weight: 700; color: var(--text-muted); }
  .war-rating { display: flex; align-items: center; gap: 4px; background: #fef3c7; color: #b45309; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; }
  .war-rating::before { content: "★"; }
  .war-stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px; }
  .war-stat { background: var(--bg-alt, #f2f2ec); padding: 12px 14px; border-radius: 10px; }
  .war-stat-label { font-size: 10px; font-weight: 700; color: var(--text-soft); letter-spacing: 0.4px; text-transform: uppercase; margin-bottom: 3px; }
  .war-stat-val { font-family: var(--font-display); font-size: 22px; font-weight: 800; color: var(--text); font-variant-numeric: tabular-nums; }
  .war-stat-trend { font-size: 10px; font-weight: 700; color: var(--success, #15803d); margin-top: 1px; }

  .war-bookings-section { background: linear-gradient(135deg, #fff1f2 0%, #fff 100%); border-radius: 10px; padding: 14px 16px; }
  .war-bookings-title { font-size: 11px; font-weight: 700; color: var(--text-muted); letter-spacing: 0.4px; text-transform: uppercase; margin-bottom: 10px; }
  .war-booking { display: flex; align-items: center; gap: 12px; padding: 8px 0; font-size: 12.5px; }
  .war-booking:not(:last-child) { border-bottom: 1px dashed var(--border); }
  .war-booking-time { font-family: var(--font-display); font-weight: 700; color: var(--text); width: 44px; flex-shrink: 0; font-variant-numeric: tabular-nums; }
  .war-booking-info { flex: 1; min-width: 0; }
  .war-booking-service { font-weight: 600; color: var(--text); margin-bottom: 1px; }
  .war-booking-car { font-size: 10.5px; color: var(--text-soft); }
  .war-booking-status { flex-shrink: 0; width: 8px; height: 8px; border-radius: 50%; background: var(--success, #15803d); }
  .war-booking-status.is-new { background: var(--war-red); animation: warBlink 2s ease-in-out infinite; }
  @keyframes warBlink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
  }

  .war-section { padding: clamp(60px, 8vw, 100px) 0; position: relative; }
  .war-section-soft { background: var(--surface, #fff); }
  .war-section-head { max-width: 720px; margin: 0 0 56px; }
  .war-section-head.is-center { margin: 0 auto 56px; text-align: center; }
  .war-section-eyebrow { font-size: 12px; font-weight: 700; color: #7f1d1d; letter-spacing: 0.8px; text-transform: uppercase; margin-bottom: 12px; display: inline-block; }
  .war-section h2 { font-family: var(--font-display); font-size: clamp(28px, 3.8vw, 42px); font-weight: 800; line-height: 1.15; letter-spacing: -0.018em; margin: 0 0 16px; color: var(--text); }
  .war-section-intro { font-size: clamp(16px, 1.6vw, 18px); line-height: 1.65; color: var(--text-soft); margin: 0; }

  .war-problems { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin-top: 16px; }
  @media (max-width: 800px) { .war-problems { grid-template-columns: 1fr; } }
  .war-problem { background: var(--surface); border: 1px solid var(--border); border-radius: var(--war-radius); padding: 28px; transition: all 0.25s var(--ease-out); }
  .war-problem:hover { transform: translateY(-3px); border-color: var(--war-red); box-shadow: var(--shadow-soft); }
  .war-problem-quote { font-family: var(--font-display); font-size: 22px; font-weight: 700; line-height: 1.3; color: var(--text); margin: 0 0 14px; position: relative; padding-top: 24px; }
  .war-problem-quote::before { content: '"'; position: absolute; top: -10px; left: -8px; font-size: 60px; color: var(--war-red); line-height: 1; font-family: serif; opacity: 0.4; }
  .war-problem p { font-size: 14px; line-height: 1.6; color: var(--text-soft); margin: 0; }

  .war-services { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 16px; }
  @media (max-width: 800px) { .war-services { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 500px) { .war-services { grid-template-columns: 1fr; } }
  .war-service { background: var(--surface); border: 1px solid var(--border); border-radius: var(--war-radius); padding: 24px; transition: all 0.25s var(--ease-out); text-align: center; }
  .war-service:hover { border-color: var(--war-red); transform: translateY(-3px); box-shadow: var(--shadow-soft); }
  .war-service-icon { width: 52px; height: 52px; margin: 0 auto 14px; border-radius: 14px; background: var(--war-red-soft); color: var(--war-red); display: grid; place-items: center; font-size: 24px; }
  .war-service h4 { font-family: var(--font-display); font-size: 16px; font-weight: 700; margin: 0 0 6px; }
  .war-service-frequency { font-family: var(--font-display); font-size: 13px; color: var(--war-yellow); font-weight: 700; margin-bottom: 8px; }
  .war-service p { font-size: 12.5px; line-height: 1.5; color: var(--text-soft); margin: 0; }

  .war-package-wrap { display: grid; grid-template-columns: 1.2fr 1fr; gap: 24px; margin-top: 16px; }
  @media (max-width: 800px) { .war-package-wrap { grid-template-columns: 1fr; } }
  .war-package { background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border: 1px solid var(--war-red-line); border-radius: var(--war-radius-lg); padding: clamp(28px, 4vw, 44px); position: relative; }
  .war-package-tag { display: inline-block; background: var(--war-red); color: #fff; padding: 6px 14px; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 16px; }
  .war-package h3 { font-family: var(--font-display); font-size: clamp(22px, 2.5vw, 30px); font-weight: 800; line-height: 1.2; margin: 0 0 12px; color: var(--text); }
  .war-package-desc { font-size: 15px; line-height: 1.6; color: var(--text-muted); margin: 0 0 28px; }
  .war-package-list { margin: 0; padding: 0; list-style: none; }
  .war-package-list li { padding: 10px 0 10px 32px; position: relative; font-size: 15px; line-height: 1.5; color: var(--text); border-bottom: 1px dashed rgba(185, 28, 28, 0.15); }
  .war-package-list li:last-child { border-bottom: 0; }
  .war-package-list li::before { content: "✓"; position: absolute; left: 0; top: 8px; width: 22px; height: 22px; background: var(--war-red); color: #fff; border-radius: 50%; display: grid; place-items: center; font-size: 12px; font-weight: 800; }
  .war-pricing { background: var(--surface); border: 2px solid var(--text); border-radius: var(--war-radius-lg); padding: clamp(28px, 4vw, 36px); text-align: center; position: sticky; top: 24px; }
  .war-pricing-label { font-size: 11px; font-weight: 700; color: var(--text-soft); letter-spacing: 0.6px; text-transform: uppercase; margin-bottom: 8px; }
  .war-pricing-amount { font-family: var(--font-display); font-size: clamp(36px, 5vw, 52px); font-weight: 800; line-height: 1; color: var(--text); margin-bottom: 4px; }
  .war-pricing-amount small { font-size: 16px; font-weight: 600; color: var(--text-soft); margin-left: 4px; }
  .war-pricing-period { font-size: 13px; color: var(--text-soft); margin-bottom: 24px; }
  .war-pricing-extras { margin: 24px 0 0; padding: 20px 0 0; border-top: 1px solid var(--border); text-align: left; list-style: none; }
  .war-pricing-extras li { padding: 6px 0 6px 24px; position: relative; font-size: 13px; color: var(--text-muted); line-height: 1.5; }
  .war-pricing-extras li::before { content: "+"; position: absolute; left: 0; color: var(--war-red); font-weight: 800; }
  .war-pricing-cta { display: block; width: 100%; padding: 16px; background: var(--text); color: #fff; border-radius: 12px; font-weight: 700; font-size: 15px; text-decoration: none; transition: all 0.2s ease; margin-top: 24px; }
  .war-pricing-cta:hover { background: var(--war-red); color: #fff; transform: translateY(-1px); }

  .war-honest { background: linear-gradient(135deg, var(--accent-soft, #fff7ed) 0%, #fff 100%); border: 1px solid var(--accent-line, #fed7aa); border-radius: var(--war-radius); padding: clamp(28px, 4vw, 44px); margin-top: 32px; }
  .war-honest-header { display: flex; gap: 16px; align-items: center; margin-bottom: 20px; }
  .war-honest-icon { flex-shrink: 0; width: 44px; height: 44px; border-radius: 12px; background: var(--accent, #f97316); color: #fff; display: grid; place-items: center; font-size: 22px; font-weight: 800; }
  .war-honest-header strong { font-family: var(--font-display); font-size: 18px; font-weight: 700; }
  .war-honest ul { margin: 0; padding: 0; list-style: none; display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px 24px; }
  @media (max-width: 600px) { .war-honest ul { grid-template-columns: 1fr; } }
  .war-honest li { padding: 10px 0 10px 28px; position: relative; font-size: 15px; line-height: 1.5; color: var(--text-muted); }
  .war-honest li::before { content: "✗"; position: absolute; left: 0; top: 8px; color: var(--accent); font-weight: 800; font-size: 18px; }

  .war-steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: 16px; }
  @media (max-width: 800px) { .war-steps { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 500px) { .war-steps { grid-template-columns: 1fr; } }
  .war-step { background: var(--surface, #fff); border: 1px solid var(--border); border-radius: var(--war-radius); padding: 24px; position: relative; transition: all 0.25s var(--ease-out); }
  .war-step:hover { transform: translateY(-3px); border-color: var(--war-red); box-shadow: var(--shadow-soft); }
  .war-step-num { font-family: var(--font-display); font-size: 32px; font-weight: 800; line-height: 1; color: var(--war-red); margin-bottom: 16px; font-variant-numeric: tabular-nums; }
  .war-step h4 { font-family: var(--font-display); font-size: 16px; font-weight: 700; line-height: 1.3; margin: 0 0 8px; }
  .war-step p { font-size: 13px; line-height: 1.6; color: var(--text-soft); margin: 0; }
  .war-step-time { display: inline-block; margin-top: 12px; font-size: 11px; font-weight: 700; color: var(--text-soft); letter-spacing: 0.5px; text-transform: uppercase; padding: 4px 8px; background: var(--bg-alt, #f2f2ec); border-radius: 6px; }

  .war-example-wrap { background: linear-gradient(135deg, #1c1917 0%, #292524 100%); color: #fff; border-radius: var(--war-radius-lg); padding: clamp(36px, 5vw, 56px); margin-top: 16px; position: relative; overflow: hidden; }
  .war-example-wrap::before { content: ""; position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(185, 28, 28, 0.25) 0%, transparent 60%); pointer-events: none; }
  .war-example-wrap > * { position: relative; }
  .war-example-eyebrow { font-size: 11px; font-weight: 700; color: #fca5a5; letter-spacing: 0.8px; text-transform: uppercase; margin-bottom: 14px; }
  .war-example-wrap h3 { font-family: var(--font-display); font-size: clamp(22px, 2.5vw, 30px); font-weight: 800; line-height: 1.25; margin: 0 0 12px; color: #fff; }
  .war-example-context { color: rgba(255, 255, 255, 0.7); font-size: 15px; line-height: 1.6; margin: 0 0 32px; max-width: 600px; }
  .war-comparison { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 32px; }
  @media (max-width: 700px) { .war-comparison { grid-template-columns: 1fr; } }
  .war-state { background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.1); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border-radius: 16px; padding: 24px; }
  .war-state.is-after { background: rgba(185, 28, 28, 0.18); border-color: rgba(252, 165, 165, 0.35); }
  .war-state-label { display: inline-block; font-size: 10px; font-weight: 700; letter-spacing: 0.6px; text-transform: uppercase; background: rgba(255, 255, 255, 0.1); color: #fff; padding: 4px 8px; border-radius: 6px; margin-bottom: 14px; }
  .war-state.is-after .war-state-label { background: var(--war-red); }
  .war-state h4 { font-family: var(--font-display); font-size: 15px; font-weight: 700; margin: 0 0 14px; color: #fff; }
  .war-state-metric { padding: 10px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.1); display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: rgba(255, 255, 255, 0.85); }
  .war-state-metric:last-child { border-bottom: 0; }
  .war-state-metric strong { font-family: var(--font-display); font-variant-numeric: tabular-nums; font-weight: 800; font-size: 15px; }
  .war-state.is-after .war-state-metric strong { color: #fca5a5; }
  .war-example-summary { border-top: 1px solid rgba(255, 255, 255, 0.12); padding-top: 28px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
  .war-example-summary-text { color: rgba(255, 255, 255, 0.85); font-size: 15px; line-height: 1.5; max-width: 520px; }
  .war-example-summary-amount { font-family: var(--font-display); font-size: clamp(28px, 3.5vw, 38px); font-weight: 800; color: #fff; text-align: right; }
  .war-example-summary-amount small { display: block; font-size: 12px; font-weight: 600; color: rgba(255, 255, 255, 0.6); letter-spacing: 0.4px; text-transform: uppercase; margin-top: 4px; }

  .war-target { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
  @media (max-width: 700px) { .war-target { grid-template-columns: 1fr; } }
  .war-target-col { padding: 28px; border-radius: var(--war-radius); border: 1px solid var(--border); }
  .war-target-col.is-yes { background: var(--war-red-soft); border-color: rgba(185, 28, 28, 0.2); }
  .war-target-col.is-no { background: var(--bg-alt, #f2f2ec); }
  .war-target-col h4 { font-family: var(--font-display); font-size: 17px; font-weight: 700; margin: 0 0 16px; display: flex; align-items: center; gap: 8px; }
  .war-target-col h4::before { width: 24px; height: 24px; border-radius: 50%; display: grid; place-items: center; font-size: 14px; font-weight: 800; flex-shrink: 0; }
  .war-target-col.is-yes h4::before { content: "✓"; background: var(--war-red); color: #fff; }
  .war-target-col.is-no h4::before { content: "—"; background: var(--text-soft); color: #fff; }
  .war-target-col ul { margin: 0; padding: 0; list-style: none; }
  .war-target-col li { padding: 8px 0; font-size: 14px; line-height: 1.55; color: var(--text-muted); }

  .war-faq-list { max-width: 760px; margin: 0 auto; }
  .war-faq { background: var(--surface); border: 1px solid var(--border); border-radius: var(--war-radius); margin-bottom: 12px; overflow: hidden; transition: border-color 0.2s ease; }
  .war-faq:hover { border-color: var(--war-red); }
  .war-faq[open] { border-color: var(--war-red); box-shadow: var(--shadow-sm); }
  .war-faq summary { padding: 22px 28px; cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center; gap: 16px; font-family: var(--font-display); font-size: 16px; font-weight: 700; line-height: 1.4; color: var(--text); user-select: none; }
  .war-faq summary::-webkit-details-marker { display: none; }
  .war-faq summary::after { content: "+"; flex-shrink: 0; width: 28px; height: 28px; border-radius: 50%; background: var(--war-red-soft); color: var(--war-red); display: grid; place-items: center; font-size: 18px; font-weight: 400; transition: all 0.2s ease; }
  .war-faq[open] summary::after { content: "−"; background: var(--war-red); color: #fff; transform: rotate(180deg); }
  .war-faq-content { padding: 0 28px 24px; color: var(--text-soft); font-size: 15px; line-height: 1.7; }
  .war-faq-content p { margin: 0 0 12px; }
  .war-faq-content p:last-child { margin: 0; }

  .war-author { background: var(--sand, #f5f0e8); border-radius: var(--war-radius-lg); padding: clamp(36px, 5vw, 56px); display: grid; grid-template-columns: auto 1fr; gap: 32px; align-items: center; margin-top: 16px; }
  @media (max-width: 700px) { .war-author { grid-template-columns: 1fr; text-align: center; } }
  .war-author-photo { width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, var(--war-red) 0%, #7f1d1d 100%); color: #fff; display: grid; place-items: center; font-family: var(--font-display); font-size: 42px; font-weight: 800; flex-shrink: 0; margin: 0 auto; }
  .war-author-content h3 { font-family: var(--font-display); font-size: 22px; font-weight: 800; margin: 0 0 8px; }
  .war-author-role { font-size: 14px; font-weight: 600; color: #7f1d1d; margin: 0 0 16px; }
  .war-author-content p { font-size: 15px; line-height: 1.65; color: var(--text-muted); margin: 0; }

  .war-final { background: linear-gradient(180deg, var(--text, #0d0d0b) 0%, #1c1917 100%); color: #fff; padding: clamp(60px, 9vw, 96px) 0; position: relative; overflow: hidden; }
  .war-final::before { content: ""; position: absolute; top: -100px; left: 50%; transform: translateX(-50%); width: 600px; height: 600px; background: radial-gradient(circle, rgba(185, 28, 28, 0.25) 0%, transparent 70%); pointer-events: none; }
  .war-final-inner { position: relative; max-width: 640px; margin: 0 auto; text-align: center; }
  .war-final h2 { color: #fff; margin-bottom: 16px; }
  .war-final-sub { font-size: 17px; line-height: 1.6; color: rgba(255, 255, 255, 0.7); margin: 0 0 40px; }
  .war-form { background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.1); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border-radius: var(--war-radius); padding: clamp(24px, 4vw, 36px); text-align: left; }
  .war-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  @media (max-width: 600px) { .war-form-grid { grid-template-columns: 1fr; } }
  .war-field { margin-bottom: 16px; }
  .war-field-full { grid-column: 1 / -1; }
  .war-field label { display: block; font-size: 13px; font-weight: 600; color: rgba(255, 255, 255, 0.85); margin-bottom: 8px; }
  .war-field label .opt { font-weight: 400; color: rgba(255, 255, 255, 0.4); font-size: 12px; margin-left: 6px; }
  .war-field input,
  .war-field textarea,
  .war-field select {
    width: 100%; padding: 14px 16px; background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.15); color: #fff;
    border-radius: 12px; font-size: 15px; font-family: inherit; transition: all 0.2s ease;
  }
  .war-field input::placeholder,
  .war-field textarea::placeholder { color: rgba(255, 255, 255, 0.35); }
  .war-field input:focus,
  .war-field textarea:focus,
  .war-field select:focus { outline: none; border-color: var(--war-red); background: rgba(255, 255, 255, 0.1); }
  .war-field textarea { min-height: 90px; resize: vertical; }
  .war-form-submit { width: 100%; padding: 18px; background: var(--war-red); color: #fff; border: 0; border-radius: 14px; font-weight: 700; font-size: 16px; cursor: pointer; transition: all 0.2s ease; margin-top: 8px; font-family: inherit; }
  .war-form-submit:hover { background: #7f1d1d; transform: translateY(-2px); box-shadow: 0 12px 24px rgba(185, 28, 28, 0.3); }
  .war-form-meta { text-align: center; margin-top: 20px; font-size: 12px; color: rgba(255, 255, 255, 0.4); line-height: 1.5; }
  .war-form-feedback { margin-bottom: 20px; padding: 12px 14px; border-radius: 10px; font-size: 14px; }
  .war-form-feedback.is-success { background: rgba(22, 163, 74, 0.18); border: 1px solid rgba(34, 197, 94, 0.45); color: #dcfce7; }
  .war-form-feedback.is-error { background: rgba(220, 38, 38, 0.18); border: 1px solid rgba(248, 113, 113, 0.45); color: #fee2e2; }
</style>

<div class="war-page">
  <section class="war-hero">
    <div class="war-container war-hero-inner">
      <div class="war-hero-grid">
        <div>
          <div class="war-pill">Marketing dla warsztatow samochodowych</div>
          <h1>Zapelniony serwis <em>od poniedzialku do piatku</em></h1>
          <p class="war-hero-sub">
            Reklama Google + Maps + Meta, ktore pokazuja Twoj warsztat wtedy, gdy klient z okolicy szuka mechanika.
          </p>
          <div class="war-hero-cta-row">
            <a href="#kontakt" class="war-cta-primary" data-cta="hero-umow-rozmowe">Umow rozmowe</a>
            <a href="#oferta" class="war-cta-secondary" data-cta="hero-zobacz-pakiet">Zobacz pakiet</a>
          </div>
          <div class="war-trust-row">
            <span class="war-trust-item">Pierwsi klienci w 14 dni</span>
            <span class="war-trust-item">Bez umow na rok</span>
            <span class="war-trust-item">Pierwsza konsultacja gratis</span>
          </div>
        </div>
        <div>
          <div class="war-mockup" aria-hidden="true">
            <div class="war-mockup-header">
              <span class="war-mockup-name">Auto Serwis Krajewski — Lublin</span>
              <span class="war-rating">4.8 (162)</span>
            </div>
            <div class="war-stats-grid">
              <div class="war-stat"><div class="war-stat-label">Klienci / mies</div><div class="war-stat-val">142</div><div class="war-stat-trend">+186%</div></div>
              <div class="war-stat"><div class="war-stat-label">Sredni rachunek</div><div class="war-stat-val">680 zl</div><div class="war-stat-trend">+24%</div></div>
            </div>
            <div class="war-bookings-section">
              <div class="war-bookings-title">Dzis — wtorek 14:30</div>
              <div class="war-booking"><span class="war-booking-time">15:00</span><div class="war-booking-info"><div class="war-booking-service">Wymiana opon + wywazanie</div><div class="war-booking-car">Ford Focus, 2018</div></div><span class="war-booking-status"></span></div>
              <div class="war-booking"><span class="war-booking-time">15:30</span><div class="war-booking-info"><div class="war-booking-service">Wymiana klockow przednich</div><div class="war-booking-car">VW Passat, 2019</div></div><span class="war-booking-status"></span></div>
              <div class="war-booking"><span class="war-booking-time">16:00</span><div class="war-booking-info"><div class="war-booking-service">Diagnostyka komputerowa</div><div class="war-booking-car">Audi A4, 2017</div></div><span class="war-booking-status"></span></div>
              <div class="war-booking"><span class="war-booking-time">16:30</span><div class="war-booking-info"><div class="war-booking-service">Naprawa ukladu hamulcowego</div><div class="war-booking-car">Skoda Octavia, 2020</div></div><span class="war-booking-status is-new"></span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="war-section war-section-soft">
    <div class="war-container">
      <div class="war-section-head">
        <span class="war-section-eyebrow">Jesli ktore brzmi znajomo</span>
        <h2>Trzy problemy, ktore slysze od mechanikow</h2>
      </div>
      <div class="war-problems">
        <div class="war-problem"><p class="war-problem-quote">Klienci dzwonia pytac o cene i nigdy nie wracaja.</p><p>Walka tylko cena oznacza marnowanie czasu i niska marzowosc.</p></div>
        <div class="war-problem"><p class="war-problem-quote">Mam 4 stanowiska, ale tylko 2 sa zajete.</p><p>Puste stanowiska to koszty stale bez przychodu.</p></div>
        <div class="war-problem"><p class="war-problem-quote">Na Google jestem 3 strona, na Mapach pomiedzy 8 innymi.</p><p>Brak widocznosci w lokalnym intent to utrata klientow "na teraz".</p></div>
      </div>
    </div>
  </section>

  <section class="war-section">
    <div class="war-container">
      <div class="war-section-head">
        <span class="war-section-eyebrow">Klucz do warsztatu</span>
        <h2>Tanie klikniecia + wysoka powtarzalnosc klienta</h2>
      </div>
      <div class="war-services">
        <div class="war-service"><div class="war-service-icon">🔧</div><h4>Mechanika ogolna</h4><div class="war-service-frequency">co 6-12 miesiecy</div><p>Podstawa obrotu i regularnych powrotow.</p></div>
        <div class="war-service"><div class="war-service-icon">🛞</div><h4>Wymiana opon</h4><div class="war-service-frequency">2x w roku</div><p>Sezonowy peak i szybkie decyzje klientow.</p></div>
        <div class="war-service"><div class="war-service-icon">❄️</div><h4>Klimatyzacja</h4><div class="war-service-frequency">co 12-24 miesiace</div><p>Wysoka intencja i duza liczba zapytan w sezonie.</p></div>
        <div class="war-service"><div class="war-service-icon">🎨</div><h4>Blacharnia / lakiernia</h4><div class="war-service-frequency">raz na 3-5 lat</div><p>Wyzej wartosciowe zlecenia i silna potrzeba zaufania.</p></div>
        <div class="war-service"><div class="war-service-icon">⚡</div><h4>Elektryka / diagnostyka</h4><div class="war-service-frequency">awaryjnie</div><p>Klient szuka "tu i teraz" w promieniu kilku kilometrow.</p></div>
        <div class="war-service"><div class="war-service-icon">🔋</div><h4>Akumulatory</h4><div class="war-service-frequency">co 4-6 lat</div><p>Zima to mocny trigger i szybkie decyzje zakupowe.</p></div>
      </div>
    </div>
  </section>

  <section class="war-section war-section-soft" id="oferta">
    <div class="war-container">
      <div class="war-section-head">
        <span class="war-section-eyebrow">Co konkretnie robie</span>
        <h2>Pakiet "Pelen serwis"</h2>
      </div>
      <div class="war-package-wrap">
        <div class="war-package">
          <span class="war-package-tag">Pakiet startowy</span>
          <h3>Stale oblozenie warsztatu od poniedzialku do piatku</h3>
          <p class="war-package-desc">Google Maps + Google Ads + strona z rezerwacjami online i procesem filtrowania leadow cenowych.</p>
          <ul class="war-package-list">
            <li>Nowa strona lub redesign z rezerwacjami online</li>
            <li>Optymalizacja Google Maps + opinie</li>
            <li>Google Ads na frazy lokalne i uslugowe</li>
            <li>Kampanie sezonowe (opony, klima, akumulatory)</li>
            <li>Meta Ads w promieniu 5-10 km</li>
            <li>Transparentny cennik orientacyjny</li>
            <li>Szybki follow-up leadow (do 30 minut)</li>
            <li>Cotygodniowa optymalizacja + raport miesieczny</li>
          </ul>
        </div>
        <div class="war-pricing">
          <div class="war-pricing-label">Miesieczna oplata</div>
          <div class="war-pricing-amount">1 600<small>zl</small></div>
          <div class="war-pricing-period">+ budzet reklamowy od 700 zl/mies</div>
          <ul class="war-pricing-extras">
            <li>Pierwsza konsultacja gratis (45 min)</li>
            <li>Bez umow na 12 miesiecy</li>
            <li>Strona w pierwszym miesiacu w cenie</li>
            <li>Pierwszy miesiac 50% taniej</li>
          </ul>
          <a href="#kontakt" class="war-pricing-cta" data-cta="oferta-umow-konsultacje">Umow konsultacje →</a>
        </div>
      </div>
    </div>
  </section>

  <section class="war-section">
    <div class="war-container">
      <div class="war-section-head">
        <span class="war-section-eyebrow">Szczerze</span>
        <h2>Czego NIE dostaniesz (i dlaczego dobrze)</h2>
      </div>
      <div class="war-honest">
        <div class="war-honest-header"><div class="war-honest-icon">!</div><strong>W pakiecie nie znajdziesz:</strong></div>
        <ul>
          <li>Reklam na OLX i Marketplace</li>
          <li>Wojny cenowej z najtanszym rynkiem</li>
          <li>Pustych leadow bez intencji wizyty</li>
          <li>Promocji "za 1 zl" przyciagajacych przypadkowych</li>
          <li>Braku pomiaru jakosci kontaktow</li>
          <li>Dzialan bez oparcia o Google Maps</li>
        </ul>
      </div>
    </div>
  </section>

  <section class="war-section war-section-soft">
    <div class="war-container">
      <div class="war-section-head">
        <span class="war-section-eyebrow">Jak zaczynamy</span>
        <h2>Cztery kroki od rozmowy do pelnego grafiku</h2>
      </div>
      <div class="war-steps">
        <div class="war-step"><div class="war-step-num">01</div><h4>Bezplatna konsultacja</h4><p>Analiza specjalizacji, sezonowosci i lokalizacji.</p><span class="war-step-time">Tydzien 1</span></div>
        <div class="war-step"><div class="war-step-num">02</div><h4>Strona + Maps</h4><p>Wdrozenie rezerwacji i optymalizacja profilu.</p><span class="war-step-time">Tygodnie 2-3</span></div>
        <div class="war-step"><div class="war-step-num">03</div><h4>Start kampanii</h4><p>Google Ads + Meta Ads na lokalny intent.</p><span class="war-step-time">Miesiac 1-2</span></div>
        <div class="war-step"><div class="war-step-num">04</div><h4>Skalowanie</h4><p>Optymalizacja sezonowa i wzrost sredniego rachunku.</p><span class="war-step-time">Cyklicznie</span></div>
      </div>
    </div>
  </section>

  <section class="war-section">
    <div class="war-container">
      <div class="war-section-head">
        <span class="war-section-eyebrow">Konkret zamiast obietnic</span>
        <h2>Pierwsze 90 dni warsztatu z Lublina</h2>
      </div>
      <div class="war-example-wrap">
        <div class="war-example-eyebrow">Auto Serwis Krajewski — Lublin</div>
        <h3>Z 50 klientow / mies do 142</h3>
        <p class="war-example-context">Optymalizacja Maps + strona z rezerwacjami + Google Ads na lokalne frazy przelozyly sie na regularny naplyw zlecen.</p>
        <div class="war-comparison">
          <div class="war-state">
            <span class="war-state-label">Przed</span>
            <h4>Q4 2025</h4>
            <div class="war-state-metric"><span>Klienci / miesiac</span><strong>~50</strong></div>
            <div class="war-state-metric"><span>Sredni rachunek</span><strong>~550 zl</strong></div>
            <div class="war-state-metric"><span>Obrot / mies</span><strong>~27 500 zl</strong></div>
            <div class="war-state-metric"><span>Pozycja Google Maps</span><strong>poz. 11</strong></div>
          </div>
          <div class="war-state is-after">
            <span class="war-state-label">Po 90 dniach</span>
            <h4>Q1 2026</h4>
            <div class="war-state-metric"><span>Klienci / miesiac</span><strong>~142</strong></div>
            <div class="war-state-metric"><span>Sredni rachunek</span><strong>~680 zl</strong></div>
            <div class="war-state-metric"><span>Obrot / mies</span><strong>~96 600 zl</strong></div>
            <div class="war-state-metric"><span>Pozycja Google Maps</span><strong>poz. 2</strong></div>
          </div>
        </div>
        <div class="war-example-summary">
          <div class="war-example-summary-text">Najwieksza zmiana: regularny doplyw nowych klientow i wzrost rentownosci stanowisk.</div>
          <div class="war-example-summary-amount">+250%<small>wzrost obrotu w 90 dni</small></div>
        </div>
      </div>
    </div>
  </section>

  <section class="war-section war-section-soft">
    <div class="war-container">
      <div class="war-section-head">
        <span class="war-section-eyebrow">Dla kogo</span>
        <h2>Dla kogo to ma sens</h2>
      </div>
      <div class="war-target">
        <div class="war-target-col is-yes">
          <h4>Pakiet ma sens jesli:</h4>
          <ul>
            <li>→ Warsztat dziala min. 2 lata</li>
            <li>→ Masz min. 2 stanowiska i zespol</li>
            <li>→ Masz opinie Google 4.0+</li>
            <li>→ Masz konkretne specjalizacje</li>
            <li>→ Stac Cie na 1 600 zl + budzet reklamowy</li>
          </ul>
        </div>
        <div class="war-target-col is-no">
          <h4>Pakiet NIE ma sensu jesli:</h4>
          <ul>
            <li>→ Pracujesz solo i nie chcesz rosnac</li>
            <li>→ Konkurujesz glownie cena</li>
            <li>→ Brak profilu Google Maps</li>
            <li>→ Opinie ponizej 3.5</li>
            <li>→ Dzialasz poza legalnym modelem</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section class="war-section">
    <div class="war-container">
      <div class="war-section-head">
        <span class="war-section-eyebrow">Kto bedzie z Toba pracowal</span>
        <h2>Po drugiej stronie ekranu</h2>
      </div>
      <div class="war-author">
        <div class="war-author-photo">SK</div>
        <div class="war-author-content">
          <h3>Sebastian Kelm</h3>
          <p class="war-author-role">Konsultant marketingu — Google Ads · Google Maps · Meta Ads · landing pages</p>
          <p>Pracuje bezposrednio z warsztatami i buduje strategie pod szybka reakcje, lokalnosc i powtarzalnosc klientow.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="war-section war-section-soft">
    <div class="war-container">
      <div class="war-section-head is-center">
        <span class="war-section-eyebrow">FAQ</span>
        <h2>Pytania, ktore dostaje najczesciej</h2>
      </div>
      <div class="war-faq-list">
        <details class="war-faq">
          <summary>Ile to kosztuje miesiecznie razem z reklamami?</summary>
          <div class="war-faq-content"><p>Pakiet: <strong>1 600 zl netto/mies</strong> + budzet reklamowy od 700 zl.</p></div>
        </details>
        <details class="war-faq">
          <summary>Po jakim czasie zobacze pierwszych klientow?</summary>
          <div class="war-faq-content"><p>Pierwsze rezerwacje zwykle 7-14 dni, stabilny efekt zwykle 30-60 dni.</p></div>
        </details>
        <details class="war-faq">
          <summary>Czy konkurujemy z ASO?</summary>
          <div class="war-faq-content"><p>Nie cenowo. Targetujemy segment aut po gwarancji i stawiamy na szybsza, bardziej osobista obsluge.</p></div>
        </details>
      </div>
    </div>
  </section>

  <section class="war-final" id="kontakt">
    <div class="war-container war-final-inner">
      <h2>Umow bezplatna konsultacje</h2>
      <p class="war-final-sub">45 minut online. Ocenie potencjal Twojego warsztatu, widocznosc lokalna i kroki do zapelnienia grafiku.</p>

      <form class="war-form" method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" data-form="warsztat-samochodowy">
        <input type="hidden" name="action" value="upsellio_submit_lead">
        <?php wp_nonce_field("upsellio_unified_lead_form", "upsellio_unified_lead_nonce"); ?>
        <input type="hidden" name="lead_form_origin" value="warsztat-samochodowy-form">
        <input type="hidden" name="lead_source" value="warsztat-samochodowy-form">
        <input type="hidden" name="lead_service" value="Marketing warsztatu samochodowego">
        <input type="hidden" name="lead_goal" value="Pozyskanie lokalnych klientow i regularnych rezerwacji dla warsztatu samochodowego">
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
          <div class="war-form-feedback is-success">Dziekuje! Formularz zostal wyslany. Odezwiemy sie najszybciej jak to mozliwe.</div>
        <?php elseif ($status === "error") : ?>
          <div class="war-form-feedback is-error">Nie udalo sie wyslac formularza. Sprobuj ponownie za chwile.</div>
        <?php endif; ?>

        <div class="war-form-grid">
          <div class="war-field">
            <label for="war_name">Imie</label>
            <input type="text" id="war_name" name="lead_name" placeholder="Jan" required>
          </div>
          <div class="war-field">
            <label for="war_email">Email</label>
            <input type="email" id="war_email" name="lead_email" placeholder="jan@warsztat.pl" required>
          </div>
          <div class="war-field war-field-full">
            <label for="war_business">Nazwa warsztatu i miasto</label>
            <input type="text" id="war_business" name="lead_company" placeholder="np. Auto Serwis Krajewski — Lublin" required>
          </div>
          <div class="war-field">
            <label for="war_size">Rozmiar warsztatu</label>
            <select id="war_size" name="lead_goal_detail">
              <option value="">— wybierz —</option>
              <option value="solo">Solo — pracuje sam</option>
              <option value="2-3-stanowiska">2-3 stanowiska, 2-3 mechanikow</option>
              <option value="4-6-stanowisk">4-6 stanowisk, 3-5 mechanikow</option>
              <option value="7+">7+ stanowisk, wieksza ekipa</option>
            </select>
          </div>
          <div class="war-field">
            <label for="war_speciality">Glowne specjalizacje</label>
            <input type="text" id="war_speciality" name="lead_source_detail" placeholder="np. mechanika ogolna, opony, klimatyzacja">
          </div>
          <div class="war-field war-field-full">
            <label for="war_message">Co Cie najbardziej trapi? <span class="opt">opcjonalnie, ale pomaga</span></label>
            <textarea id="war_message" name="lead_message" placeholder="np. Mam 4 stanowiska, ale piatki sa puste."></textarea>
          </div>
        </div>

        <div class="war-field war-field-full">
          <input type="checkbox" id="war_consent" name="lead_consent" value="1" required style="margin-right: 8px;">
          <label for="war_consent" style="display: inline; font-size: 13px;">Zgadzam sie na przetwarzanie danych osobowych w celu kontaktu.</label>
        </div>

        <button type="submit" class="war-form-submit" data-cta="form-submit">Umow bezplatna konsultacje →</button>

        <p class="war-form-meta">
          Twoje dane sluza wylacznie do umowienia konsultacji. Nie zapisuje Cie na newsletter i nie sprzedaje bazy.
        </p>
      </form>
    </div>
  </section>
</div>

<?php get_footer(); ?>
