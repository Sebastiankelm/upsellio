<?php
/**
 * Template Name: Marketing dla sklepu internetowego — landing
 *
 * Niszowy landing dla branzy e-commerce.
 */
get_header();
?>

<style>
  :root {
    --shop-radius: 20px;
    --shop-radius-lg: 28px;
    --shop-purple: #7c3aed;
    --shop-purple-soft: #ede9fe;
    --shop-purple-line: #ddd6fe;
    --shop-electric: #06b6d4;
    --shop-magenta: #db2777;
  }

  .shop-page { background: var(--bg, #fafaf6); color: var(--text, #0d0d0b); font-family: var(--font-body, "DM Sans"), system-ui, sans-serif; overflow-x: hidden; }
  .shop-page * { box-sizing: border-box; }
  .shop-container { max-width: 1100px; margin: 0 auto; padding: 0 24px; }

  .shop-hero { position: relative; padding: clamp(60px, 10vw, 100px) 0 clamp(50px, 8vw, 80px); overflow: hidden; }
  .shop-hero::before {
    content: "";
    position: absolute; inset: 0;
    background:
      radial-gradient(ellipse 700px 400px at 80% 20%, rgba(124, 58, 237, 0.07), transparent),
      radial-gradient(ellipse 500px 300px at 10% 80%, rgba(6, 182, 212, 0.06), transparent);
    pointer-events: none; z-index: 0;
  }
  .shop-hero-inner { position: relative; z-index: 1; }
  .shop-hero-grid { display: grid; grid-template-columns: 1.15fr 1fr; gap: 56px; align-items: center; }
  @media (max-width: 900px) { .shop-hero-grid { grid-template-columns: 1fr; gap: 40px; } }

  .shop-pill {
    display: inline-flex; align-items: center; gap: 8px; background: var(--shop-purple-soft); color: #5b21b6;
    padding: 8px 16px; border-radius: 999px; font-size: 12px; font-weight: 700; letter-spacing: 0.4px; text-transform: uppercase; margin-bottom: 24px;
  }
  .shop-pill::before {
    content: ""; width: 6px; height: 6px; background: var(--shop-purple); border-radius: 50%; animation: shopPulse 2s ease-in-out infinite;
  }
  @keyframes shopPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(0.85); }
  }
  .shop-hero h1 { font-family: var(--font-display, "Bricolage Grotesque"), serif; font-size: clamp(36px, 5.5vw, 60px); font-weight: 800; line-height: 1.04; letter-spacing: -0.025em; margin: 0 0 24px; color: var(--text); }
  .shop-hero h1 em {
    font-style: normal; background: linear-gradient(120deg, var(--shop-purple) 0%, var(--shop-electric) 100%);
    -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; color: transparent;
  }
  .shop-hero-sub { font-size: clamp(16px, 2vw, 19px); line-height: 1.6; color: var(--text-soft, #7a7a72); margin: 0 0 32px; max-width: 540px; }
  .shop-hero-cta-row { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 32px; }
  .shop-cta-primary {
    display: inline-flex; align-items: center; gap: 10px; background: var(--text, #0d0d0b); color: #fff; padding: 18px 32px; border-radius: 14px; font-size: 16px; font-weight: 700; text-decoration: none;
    transition: all 0.2s var(--ease-out, cubic-bezier(0.16, 1, 0.3, 1)); box-shadow: 0 4px 16px rgba(13, 13, 11, 0.15);
  }
  .shop-cta-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(13, 13, 11, 0.2); color: #fff; }
  .shop-cta-primary::after { content: "→"; transition: transform 0.2s ease; }
  .shop-cta-primary:hover::after { transform: translateX(4px); }
  .shop-cta-secondary { display: inline-flex; align-items: center; gap: 8px; color: var(--text); padding: 18px 24px; font-size: 15px; font-weight: 600; text-decoration: none; border-bottom: 2px solid transparent; transition: all 0.15s ease; }
  .shop-cta-secondary:hover { border-bottom-color: var(--shop-purple); color: var(--shop-purple); }
  .shop-trust-row { display: flex; gap: 24px; flex-wrap: wrap; color: var(--text-soft, #7a7a72); font-size: 14px; }
  .shop-trust-item { display: inline-flex; align-items: center; gap: 8px; }
  .shop-trust-item::before { content: "✓"; color: var(--shop-purple); font-weight: 800; font-size: 16px; }

  .shop-dashboard {
    background: #fff; border: 1px solid var(--border, #e8e8e0); border-radius: var(--shop-radius); padding: 24px;
    box-shadow: var(--shadow, 0 22px 60px rgba(15, 23, 42, 0.1)); position: relative; transform: rotate(-0.5deg);
  }
  .shop-dashboard::before {
    content: "Q1 2026 vs Q4 2025";
    position: absolute; top: -12px; right: 24px;
    background: linear-gradient(135deg, var(--shop-purple) 0%, var(--shop-electric) 100%);
    color: #fff; padding: 6px 14px; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;
  }
  .shop-dashboard-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--border); }
  .shop-dashboard-name { font-family: var(--font-display); font-size: 13px; font-weight: 700; color: var(--text-muted); }
  .shop-dashboard-status { background: var(--success-soft, #f0faf4); color: var(--success, #15803d); padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; }

  .shop-roas-big { background: linear-gradient(135deg, var(--shop-purple) 0%, var(--shop-electric) 100%); color: #fff; padding: 16px; border-radius: 12px; margin-bottom: 12px; text-align: center; }
  .shop-roas-label { font-size: 10px; font-weight: 700; letter-spacing: 0.6px; text-transform: uppercase; opacity: 0.9; margin-bottom: 4px; }
  .shop-roas-value { font-family: var(--font-display); font-size: 32px; font-weight: 800; font-variant-numeric: tabular-nums; line-height: 1; margin-bottom: 4px; }
  .shop-roas-value small { font-size: 14px; font-weight: 700; opacity: 0.85; }
  .shop-roas-trend { font-size: 11px; font-weight: 700; opacity: 0.95; }
  .shop-stats-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-bottom: 14px; }
  .shop-stat { background: var(--bg-alt, #f2f2ec); padding: 10px; border-radius: 8px; text-align: center; }
  .shop-stat-label { font-size: 9px; font-weight: 700; color: var(--text-soft); letter-spacing: 0.3px; text-transform: uppercase; margin-bottom: 2px; }
  .shop-stat-val { font-family: var(--font-display); font-size: 16px; font-weight: 800; color: var(--text); font-variant-numeric: tabular-nums; }
  .shop-stat-trend { font-size: 9.5px; font-weight: 700; color: var(--success, #15803d); margin-top: 1px; }
  .shop-channels-section { background: linear-gradient(135deg, #faf5ff 0%, #fff 100%); border-radius: 10px; padding: 12px 14px; }
  .shop-channels-title { font-size: 10px; font-weight: 700; color: var(--text-muted); letter-spacing: 0.4px; text-transform: uppercase; margin-bottom: 8px; }
  .shop-channel { display: flex; align-items: center; gap: 10px; padding: 5px 0; font-size: 11.5px; }
  .shop-channel:not(:last-child) { border-bottom: 1px dashed var(--border); }
  .shop-channel-name { display: flex; align-items: center; gap: 6px; font-weight: 600; color: var(--text); flex: 1; }
  .shop-channel-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
  .shop-channel-share { font-variant-numeric: tabular-nums; font-weight: 700; color: var(--text-muted); }
  .shop-channel-revenue { font-variant-numeric: tabular-nums; color: var(--text-soft); font-size: 10.5px; width: 70px; text-align: right; }

  .shop-section { padding: clamp(60px, 8vw, 100px) 0; position: relative; }
  .shop-section-soft { background: var(--surface, #fff); }
  .shop-section-head { max-width: 720px; margin: 0 0 56px; }
  .shop-section-head.is-center { margin: 0 auto 56px; text-align: center; }
  .shop-section-eyebrow { font-size: 12px; font-weight: 700; color: #5b21b6; letter-spacing: 0.8px; text-transform: uppercase; margin-bottom: 12px; display: inline-block; }
  .shop-section h2 { font-family: var(--font-display); font-size: clamp(28px, 3.8vw, 42px); font-weight: 800; line-height: 1.15; letter-spacing: -0.018em; margin: 0 0 16px; color: var(--text); }
  .shop-section-intro { font-size: clamp(16px, 1.6vw, 18px); line-height: 1.65; color: var(--text-soft); margin: 0; }

  .shop-problems { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin-top: 16px; }
  @media (max-width: 800px) { .shop-problems { grid-template-columns: 1fr; } }
  .shop-problem { background: var(--surface); border: 1px solid var(--border); border-radius: var(--shop-radius); padding: 28px; transition: all 0.25s var(--ease-out); }
  .shop-problem:hover { transform: translateY(-3px); border-color: var(--shop-purple); box-shadow: var(--shadow-soft); }
  .shop-problem-quote { font-family: var(--font-display); font-size: 22px; font-weight: 700; line-height: 1.3; color: var(--text); margin: 0 0 14px; position: relative; padding-top: 24px; }
  .shop-problem-quote::before { content: '"'; position: absolute; top: -10px; left: -8px; font-size: 60px; color: var(--shop-purple); line-height: 1; font-family: serif; opacity: 0.4; }
  .shop-problem p { font-size: 14px; line-height: 1.6; color: var(--text-soft); margin: 0; }

  .shop-metrics-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 16px; }
  @media (max-width: 800px) { .shop-metrics-grid { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 500px) { .shop-metrics-grid { grid-template-columns: 1fr; } }
  .shop-metric { background: var(--surface); border: 1px solid var(--border); border-radius: var(--shop-radius); padding: 24px; transition: all 0.25s var(--ease-out); }
  .shop-metric:hover { border-color: var(--shop-purple); transform: translateY(-3px); box-shadow: var(--shadow-soft); }
  .shop-metric-tag { display: inline-block; background: var(--shop-purple-soft); color: #5b21b6; font-family: monospace; font-size: 11px; font-weight: 800; letter-spacing: 0.5px; padding: 4px 10px; border-radius: 6px; margin-bottom: 12px; }
  .shop-metric h4 { font-family: var(--font-display); font-size: 16px; font-weight: 700; line-height: 1.3; margin: 0 0 8px; }
  .shop-metric p { font-size: 13.5px; line-height: 1.6; color: var(--text-soft); margin: 0; }

  .shop-channels-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-top: 16px; }
  @media (max-width: 700px) { .shop-channels-grid { grid-template-columns: 1fr; } }
  .shop-channel-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--shop-radius); padding: 28px; transition: all 0.25s var(--ease-out); position: relative; overflow: hidden; }
  .shop-channel-card::before { content: ""; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: var(--shop-purple); }
  .shop-channel-card.is-meta::before { background: #1877f2; }
  .shop-channel-card.is-email::before { background: var(--shop-electric); }
  .shop-channel-card.is-seo::before { background: #15803d; }
  .shop-channel-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-soft); }
  .shop-channel-card-icon { font-size: 28px; margin-bottom: 12px; }
  .shop-channel-card h4 { font-family: var(--font-display); font-size: 18px; font-weight: 700; margin: 0 0 6px; }
  .shop-channel-card-role { font-size: 11px; font-weight: 700; color: var(--shop-purple); letter-spacing: 0.4px; text-transform: uppercase; margin-bottom: 12px; }
  .shop-channel-card.is-meta .shop-channel-card-role { color: #1877f2; }
  .shop-channel-card.is-email .shop-channel-card-role { color: var(--shop-electric); }
  .shop-channel-card.is-seo .shop-channel-card-role { color: #15803d; }
  .shop-channel-card p { font-size: 14px; line-height: 1.6; color: var(--text-muted); margin: 0 0 14px; }
  .shop-channel-card ul { margin: 0; padding: 0; list-style: none; border-top: 1px dashed var(--border); padding-top: 12px; }
  .shop-channel-card li { padding: 4px 0; padding-left: 16px; position: relative; font-size: 13px; color: var(--text-muted); line-height: 1.5; }
  .shop-channel-card li::before { content: "→"; position: absolute; left: 0; color: var(--shop-purple); font-weight: 700; }
  .shop-channel-card.is-meta li::before { color: #1877f2; }
  .shop-channel-card.is-email li::before { color: var(--shop-electric); }
  .shop-channel-card.is-seo li::before { color: #15803d; }

  .shop-tiers-wrap { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 16px; }
  @media (max-width: 900px) { .shop-tiers-wrap { grid-template-columns: 1fr; } }
  .shop-tier { background: var(--surface); border: 1px solid var(--border); border-radius: var(--shop-radius-lg); padding: 32px; transition: all 0.25s var(--ease-out); position: relative; }
  .shop-tier.is-featured { background: linear-gradient(135deg, #faf5ff 0%, #ede9fe 100%); border: 2px solid var(--shop-purple); transform: translateY(-8px); }
  .shop-tier.is-featured::before {
    content: "Najpopularniejszy";
    position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
    background: var(--shop-purple); color: #fff; padding: 6px 14px; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;
  }
  .shop-tier:hover { box-shadow: var(--shadow-soft); }
  .shop-tier-name { font-family: var(--font-display); font-size: 13px; font-weight: 700; color: var(--shop-purple); letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 10px; }
  .shop-tier-target { font-size: 12px; color: var(--text-soft); margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--border); }
  .shop-tier-price { margin-bottom: 20px; }
  .shop-tier-price-amount { font-family: var(--font-display); font-size: 36px; font-weight: 800; line-height: 1; color: var(--text); }
  .shop-tier-price-amount small { font-size: 14px; font-weight: 600; color: var(--text-soft); margin-left: 4px; }
  .shop-tier-price-note { font-size: 12px; color: var(--text-soft); margin-top: 4px; }
  .shop-tier-features { list-style: none; margin: 0; padding: 0; margin-bottom: 24px; }
  .shop-tier-features li { padding: 8px 0 8px 24px; position: relative; font-size: 13.5px; line-height: 1.5; color: var(--text); }
  .shop-tier-features li::before { content: "✓"; position: absolute; left: 0; top: 8px; width: 18px; height: 18px; background: var(--shop-purple); color: #fff; border-radius: 50%; display: grid; place-items: center; font-size: 10px; font-weight: 800; }
  .shop-tier-cta { display: block; width: 100%; padding: 14px; background: var(--text); color: #fff; border-radius: 12px; font-weight: 700; font-size: 14px; text-decoration: none; transition: all 0.2s ease; text-align: center; }
  .shop-tier.is-featured .shop-tier-cta { background: var(--shop-purple); }
  .shop-tier-cta:hover { background: var(--shop-purple); color: #fff; transform: translateY(-1px); }
  .shop-tier.is-featured .shop-tier-cta:hover { background: #5b21b6; }

  .shop-honest { background: linear-gradient(135deg, var(--accent-soft, #fff7ed) 0%, #fff 100%); border: 1px solid var(--accent-line, #fed7aa); border-radius: var(--shop-radius); padding: clamp(28px, 4vw, 44px); margin-top: 32px; }
  .shop-honest-header { display: flex; gap: 16px; align-items: center; margin-bottom: 20px; }
  .shop-honest-icon { flex-shrink: 0; width: 44px; height: 44px; border-radius: 12px; background: var(--accent, #f97316); color: #fff; display: grid; place-items: center; font-size: 22px; font-weight: 800; }
  .shop-honest-header strong { font-family: var(--font-display); font-size: 18px; font-weight: 700; }
  .shop-honest ul { margin: 0; padding: 0; list-style: none; display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px 24px; }
  @media (max-width: 600px) { .shop-honest ul { grid-template-columns: 1fr; } }
  .shop-honest li { padding: 10px 0 10px 28px; position: relative; font-size: 15px; line-height: 1.5; color: var(--text-muted); }
  .shop-honest li::before { content: "✗"; position: absolute; left: 0; top: 8px; color: var(--accent); font-weight: 800; font-size: 18px; }

  .shop-steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: 16px; }
  @media (max-width: 800px) { .shop-steps { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 500px) { .shop-steps { grid-template-columns: 1fr; } }
  .shop-step { background: var(--surface, #fff); border: 1px solid var(--border); border-radius: var(--shop-radius); padding: 24px; position: relative; transition: all 0.25s var(--ease-out); }
  .shop-step:hover { transform: translateY(-3px); border-color: var(--shop-purple); box-shadow: var(--shadow-soft); }
  .shop-step-num { font-family: var(--font-display); font-size: 32px; font-weight: 800; line-height: 1; color: var(--shop-purple); margin-bottom: 16px; font-variant-numeric: tabular-nums; }
  .shop-step h4 { font-family: var(--font-display); font-size: 16px; font-weight: 700; line-height: 1.3; margin: 0 0 8px; }
  .shop-step p { font-size: 13px; line-height: 1.6; color: var(--text-soft); margin: 0; }
  .shop-step-time { display: inline-block; margin-top: 12px; font-size: 11px; font-weight: 700; color: var(--text-soft); letter-spacing: 0.5px; text-transform: uppercase; padding: 4px 8px; background: var(--bg-alt, #f2f2ec); border-radius: 6px; }

  .shop-example-wrap { background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); color: #fff; border-radius: var(--shop-radius-lg); padding: clamp(36px, 5vw, 56px); margin-top: 16px; position: relative; overflow: hidden; }
  .shop-example-wrap::before { content: ""; position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(124, 58, 237, 0.30) 0%, transparent 60%); pointer-events: none; }
  .shop-example-wrap > * { position: relative; }
  .shop-example-eyebrow { font-size: 11px; font-weight: 700; color: #c4b5fd; letter-spacing: 0.8px; text-transform: uppercase; margin-bottom: 14px; }
  .shop-example-wrap h3 { font-family: var(--font-display); font-size: clamp(22px, 2.5vw, 30px); font-weight: 800; line-height: 1.25; margin: 0 0 12px; color: #fff; }
  .shop-example-context { color: rgba(255, 255, 255, 0.7); font-size: 15px; line-height: 1.6; margin: 0 0 32px; max-width: 600px; }
  .shop-comparison { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 32px; }
  @media (max-width: 700px) { .shop-comparison { grid-template-columns: 1fr; } }
  .shop-state { background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.1); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border-radius: 16px; padding: 24px; }
  .shop-state.is-after { background: rgba(124, 58, 237, 0.18); border-color: rgba(196, 181, 253, 0.35); }
  .shop-state-label { display: inline-block; font-size: 10px; font-weight: 700; letter-spacing: 0.6px; text-transform: uppercase; background: rgba(255, 255, 255, 0.1); color: #fff; padding: 4px 8px; border-radius: 6px; margin-bottom: 14px; }
  .shop-state.is-after .shop-state-label { background: var(--shop-purple); }
  .shop-state h4 { font-family: var(--font-display); font-size: 15px; font-weight: 700; margin: 0 0 14px; color: #fff; }
  .shop-state-metric { padding: 10px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.1); display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: rgba(255, 255, 255, 0.85); }
  .shop-state-metric:last-child { border-bottom: 0; }
  .shop-state-metric strong { font-family: var(--font-display); font-variant-numeric: tabular-nums; font-weight: 800; font-size: 15px; }
  .shop-state.is-after .shop-state-metric strong { color: #c4b5fd; }
  .shop-example-summary { border-top: 1px solid rgba(255, 255, 255, 0.12); padding-top: 28px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
  .shop-example-summary-text { color: rgba(255, 255, 255, 0.85); font-size: 15px; line-height: 1.5; max-width: 520px; }
  .shop-example-summary-amount { font-family: var(--font-display); font-size: clamp(28px, 3.5vw, 38px); font-weight: 800; color: #fff; text-align: right; }
  .shop-example-summary-amount small { display: block; font-size: 12px; font-weight: 600; color: rgba(255, 255, 255, 0.6); letter-spacing: 0.4px; text-transform: uppercase; margin-top: 4px; }

  .shop-target { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
  @media (max-width: 700px) { .shop-target { grid-template-columns: 1fr; } }
  .shop-target-col { padding: 28px; border-radius: var(--shop-radius); border: 1px solid var(--border); }
  .shop-target-col.is-yes { background: var(--shop-purple-soft); border-color: rgba(124, 58, 237, 0.2); }
  .shop-target-col.is-no { background: var(--bg-alt, #f2f2ec); }
  .shop-target-col h4 { font-family: var(--font-display); font-size: 17px; font-weight: 700; margin: 0 0 16px; display: flex; align-items: center; gap: 8px; }
  .shop-target-col h4::before { width: 24px; height: 24px; border-radius: 50%; display: grid; place-items: center; font-size: 14px; font-weight: 800; flex-shrink: 0; }
  .shop-target-col.is-yes h4::before { content: "✓"; background: var(--shop-purple); color: #fff; }
  .shop-target-col.is-no h4::before { content: "—"; background: var(--text-soft); color: #fff; }
  .shop-target-col ul { margin: 0; padding: 0; list-style: none; }
  .shop-target-col li { padding: 8px 0; font-size: 14px; line-height: 1.55; color: var(--text-muted); }

  .shop-faq-list { max-width: 760px; margin: 0 auto; }
  .shop-faq { background: var(--surface); border: 1px solid var(--border); border-radius: var(--shop-radius); margin-bottom: 12px; overflow: hidden; transition: border-color 0.2s ease; }
  .shop-faq:hover { border-color: var(--shop-purple); }
  .shop-faq[open] { border-color: var(--shop-purple); box-shadow: var(--shadow-sm); }
  .shop-faq summary { padding: 22px 28px; cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center; gap: 16px; font-family: var(--font-display); font-size: 16px; font-weight: 700; line-height: 1.4; color: var(--text); user-select: none; }
  .shop-faq summary::-webkit-details-marker { display: none; }
  .shop-faq summary::after { content: "+"; flex-shrink: 0; width: 28px; height: 28px; border-radius: 50%; background: var(--shop-purple-soft); color: var(--shop-purple); display: grid; place-items: center; font-size: 18px; font-weight: 400; transition: all 0.2s ease; }
  .shop-faq[open] summary::after { content: "−"; background: var(--shop-purple); color: #fff; transform: rotate(180deg); }
  .shop-faq-content { padding: 0 28px 24px; color: var(--text-soft); font-size: 15px; line-height: 1.7; }
  .shop-faq-content p { margin: 0 0 12px; }
  .shop-faq-content p:last-child { margin: 0; }
  .shop-faq-content code { background: var(--bg-alt); padding: 2px 6px; border-radius: 4px; font-size: 13px; color: var(--shop-purple); }

  .shop-author { background: var(--sand, #f5f0e8); border-radius: var(--shop-radius-lg); padding: clamp(36px, 5vw, 56px); display: grid; grid-template-columns: auto 1fr; gap: 32px; align-items: center; margin-top: 16px; }
  @media (max-width: 700px) { .shop-author { grid-template-columns: 1fr; text-align: center; } }
  .shop-author-photo { width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, var(--shop-purple) 0%, #5b21b6 100%); color: #fff; display: grid; place-items: center; font-family: var(--font-display); font-size: 42px; font-weight: 800; flex-shrink: 0; margin: 0 auto; }
  .shop-author-content h3 { font-family: var(--font-display); font-size: 22px; font-weight: 800; margin: 0 0 8px; }
  .shop-author-role { font-size: 14px; font-weight: 600; color: #5b21b6; margin: 0 0 16px; }
  .shop-author-content p { font-size: 15px; line-height: 1.65; color: var(--text-muted); margin: 0; }

  .shop-final { background: linear-gradient(180deg, var(--text, #0d0d0b) 0%, #1e1b4b 100%); color: #fff; padding: clamp(60px, 9vw, 96px) 0; position: relative; overflow: hidden; }
  .shop-final::before { content: ""; position: absolute; top: -100px; left: 50%; transform: translateX(-50%); width: 600px; height: 600px; background: radial-gradient(circle, rgba(124, 58, 237, 0.25) 0%, transparent 70%); pointer-events: none; }
  .shop-final-inner { position: relative; max-width: 640px; margin: 0 auto; text-align: center; }
  .shop-final h2 { color: #fff; margin-bottom: 16px; }
  .shop-final-sub { font-size: 17px; line-height: 1.6; color: rgba(255, 255, 255, 0.7); margin: 0 0 40px; }
  .shop-form { background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.1); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border-radius: var(--shop-radius); padding: clamp(24px, 4vw, 36px); text-align: left; }
  .shop-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  @media (max-width: 600px) { .shop-form-grid { grid-template-columns: 1fr; } }
  .shop-field { margin-bottom: 16px; }
  .shop-field-full { grid-column: 1 / -1; }
  .shop-field label { display: block; font-size: 13px; font-weight: 600; color: rgba(255, 255, 255, 0.85); margin-bottom: 8px; }
  .shop-field label .opt { font-weight: 400; color: rgba(255, 255, 255, 0.4); font-size: 12px; margin-left: 6px; }
  .shop-field input,
  .shop-field textarea,
  .shop-field select {
    width: 100%; padding: 14px 16px; background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.15); color: #fff;
    border-radius: 12px; font-size: 15px; font-family: inherit; transition: all 0.2s ease;
  }
  .shop-field input::placeholder,
  .shop-field textarea::placeholder { color: rgba(255, 255, 255, 0.35); }
  .shop-field input:focus,
  .shop-field textarea:focus,
  .shop-field select:focus { outline: none; border-color: var(--shop-purple); background: rgba(255, 255, 255, 0.1); }
  .shop-field textarea { min-height: 90px; resize: vertical; }
  .shop-form-submit { width: 100%; padding: 18px; background: var(--shop-purple); color: #fff; border: 0; border-radius: 14px; font-weight: 700; font-size: 16px; cursor: pointer; transition: all 0.2s ease; margin-top: 8px; font-family: inherit; }
  .shop-form-submit:hover { background: #5b21b6; transform: translateY(-2px); box-shadow: 0 12px 24px rgba(124, 58, 237, 0.3); }
  .shop-form-meta { text-align: center; margin-top: 20px; font-size: 12px; color: rgba(255, 255, 255, 0.4); line-height: 1.5; }
  .shop-form-feedback { margin-bottom: 20px; padding: 12px 14px; border-radius: 10px; font-size: 14px; }
  .shop-form-feedback.is-success { background: rgba(22, 163, 74, 0.18); border: 1px solid rgba(34, 197, 94, 0.45); color: #dcfce7; }
  .shop-form-feedback.is-error { background: rgba(220, 38, 38, 0.18); border: 1px solid rgba(248, 113, 113, 0.45); color: #fee2e2; }
</style>

<div class="shop-page">
  <section class="shop-hero">
    <div class="shop-container shop-hero-inner">
      <div class="shop-hero-grid">
        <div>
          <div class="shop-pill">Marketing dla sklepow internetowych</div>
          <h1>ROAS, ktory <em>realnie sie skaluje</em>, nie tylko ladnie wyglada na wykresie</h1>
          <p class="shop-hero-sub">
            Google Ads + Meta Ads + email marketing + SEO, ktore razem daja wiecej zamowien przy zachowanym ROAS.
          </p>
          <div class="shop-hero-cta-row">
            <a href="#kontakt" class="shop-cta-primary" data-cta="hero-umow-rozmowe">Umow rozmowe</a>
            <a href="#oferta" class="shop-cta-secondary" data-cta="hero-zobacz-pakiety">Zobacz pakiety</a>
          </div>
          <div class="shop-trust-row">
            <span class="shop-trust-item">Pierwsze efekty w 30 dni</span>
            <span class="shop-trust-item">Bez umow na rok</span>
            <span class="shop-trust-item">Pelna transparentnosc wydatkow</span>
          </div>
        </div>
        <div>
          <div class="shop-dashboard" aria-hidden="true">
            <div class="shop-dashboard-header">
              <span class="shop-dashboard-name">Sklep e-commerce — Polska</span>
              <span class="shop-dashboard-status">Po 90 dniach</span>
            </div>
            <div class="shop-roas-big">
              <div class="shop-roas-label">Laczny ROAS</div>
              <div class="shop-roas-value">4,2<small>×</small></div>
              <div class="shop-roas-trend">↑ z 1,8× przed startem</div>
            </div>
            <div class="shop-stats-grid">
              <div class="shop-stat"><div class="shop-stat-label">Przychod / mies</div><div class="shop-stat-val">428k</div><div class="shop-stat-trend">+184%</div></div>
              <div class="shop-stat"><div class="shop-stat-label">CR sklepu</div><div class="shop-stat-val">3.4%</div><div class="shop-stat-trend">+92%</div></div>
              <div class="shop-stat"><div class="shop-stat-label">AOV</div><div class="shop-stat-val">218 zl</div><div class="shop-stat-trend">+18%</div></div>
            </div>
            <div class="shop-channels-section">
              <div class="shop-channels-title">Atrybucja kanalow (data-driven)</div>
              <div class="shop-channel"><div class="shop-channel-name"><span class="shop-channel-dot" style="background: #4285f4;"></span>Google Ads</div><span class="shop-channel-share">38%</span><span class="shop-channel-revenue">~163k zl</span></div>
              <div class="shop-channel"><div class="shop-channel-name"><span class="shop-channel-dot" style="background: #1877f2;"></span>Meta Ads</div><span class="shop-channel-share">26%</span><span class="shop-channel-revenue">~111k zl</span></div>
              <div class="shop-channel"><div class="shop-channel-name"><span class="shop-channel-dot" style="background: var(--shop-electric);"></span>Email / SMS</div><span class="shop-channel-share">22%</span><span class="shop-channel-revenue">~94k zl</span></div>
              <div class="shop-channel"><div class="shop-channel-name"><span class="shop-channel-dot" style="background: #15803d;"></span>Organic / SEO</div><span class="shop-channel-share">14%</span><span class="shop-channel-revenue">~60k zl</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="shop-section shop-section-soft">
    <div class="shop-container">
      <div class="shop-section-head">
        <span class="shop-section-eyebrow">Jesli ktore brzmi znajomo</span>
        <h2>Trzy najczestsze problemy sklepow internetowych</h2>
      </div>
      <div class="shop-problems">
        <div class="shop-problem"><p class="shop-problem-quote">Lalem 8 000 miesiecznie. ROAS spadl z 5× do 1.7×.</p><p>Kampanie sie rozjechaly, koszty rosna, zysk topnieje mimo stalego wydatku.</p></div>
        <div class="shop-problem"><p class="shop-problem-quote">CPM rosnie, CTR spada, koszyki porzucone — chaos.</p><p>Metr yki ida w rozne strony, a sklep nie ma stabilnej przewidywalnosci.</p></div>
        <div class="shop-problem"><p class="shop-problem-quote">Nie wiem czy placi mi sie Meta czy Google.</p><p>Bez poprawnej atrybucji latwo skalowac zly kanal i palic budzet.</p></div>
      </div>
    </div>
  </section>

  <section class="shop-section">
    <div class="shop-container">
      <div class="shop-section-head">
        <span class="shop-section-eyebrow">Klucz do e-commerce</span>
        <h2>Sześć metryk, które realnie decydują o zysku</h2>
      </div>
      <div class="shop-metrics-grid">
        <div class="shop-metric"><span class="shop-metric-tag">ROAS</span><h4>Return on Ad Spend</h4><p>Najwazniejsza miara efektywnosci budzetu reklamowego.</p></div>
        <div class="shop-metric"><span class="shop-metric-tag">AOV</span><h4>Average Order Value</h4><p>Wzrost sredniej wartosci koszyka czesto daje szybki skok zysku.</p></div>
        <div class="shop-metric"><span class="shop-metric-tag">CR</span><h4>Conversion Rate</h4><p>Pokazuje jak dobrze sklep zamienia ruch w zamowienia.</p></div>
        <div class="shop-metric"><span class="shop-metric-tag">CAC</span><h4>Customer Acquisition Cost</h4><p>Koszt pozyskania klienta musi miec zdrowa relacje do marzy i LTV.</p></div>
        <div class="shop-metric"><span class="shop-metric-tag">LTV</span><h4>Lifetime Value</h4><p>Im wyzsze LTV, tym bezpieczniej skalowac platne kanaly.</p></div>
        <div class="shop-metric"><span class="shop-metric-tag">Repeat Rate</span><h4>Powracalnosc</h4><p>Retencja klientow daje najtanszy przyrost przychodu w sklepie.</p></div>
      </div>
    </div>
  </section>

  <section class="shop-section shop-section-soft">
    <div class="shop-container">
      <div class="shop-section-head">
        <span class="shop-section-eyebrow">Jak budujemy lejek</span>
        <h2>Cztery kanaly, jedna strategia wzrostu</h2>
      </div>
      <div class="shop-channels-grid">
        <div class="shop-channel-card">
          <div class="shop-channel-card-icon">🔍</div>
          <h4>Google Ads + Shopping</h4>
          <div class="shop-channel-card-role">Intencja zakupu</div>
          <p>Najwyzsza jakosc ruchu i najszybszy feedback dla skalowania kampanii.</p>
          <ul><li>Performance Max + Search</li><li>Brand protection</li><li>Long-tail intent</li><li>Remarketing</li></ul>
        </div>
        <div class="shop-channel-card is-meta">
          <div class="shop-channel-card-icon">📱</div>
          <h4>Meta Ads</h4>
          <div class="shop-channel-card-role">Generowanie popytu</div>
          <p>Budujemy zainteresowanie i dowozimy wolumen tam, gdzie Google juz nie wystarcza.</p>
          <ul><li>Advantage+ Shopping</li><li>UGC i video</li><li>Dynamic remarketing</li><li>Lookalike z purchase data</li></ul>
        </div>
        <div class="shop-channel-card is-email">
          <div class="shop-channel-card-icon">📧</div>
          <h4>Email + SMS</h4>
          <div class="shop-channel-card-role">Retencja</div>
          <p>Najwyzszy ROAS i najtanszy przyrost przychodu dzieki automatyzacjom.</p>
          <ul><li>Welcome flow</li><li>Abandoned cart</li><li>Post-purchase</li><li>Win-back</li></ul>
        </div>
        <div class="shop-channel-card is-seo">
          <div class="shop-channel-card-icon">🌱</div>
          <h4>SEO + content commerce</h4>
          <div class="shop-channel-card-role">Fundament dlugoterminowy</div>
          <p>Stabilizuje pozyskanie ruchu i obniza zaleznosc od platnych mediow.</p>
          <ul><li>On-page produktow</li><li>Tresci blogowe</li><li>Schema.org</li><li>Linkowanie wewnetrzne</li></ul>
        </div>
      </div>
    </div>
  </section>

  <section class="shop-section" id="oferta">
    <div class="shop-container">
      <div class="shop-section-head">
        <span class="shop-section-eyebrow">Co konkretnie robie</span>
        <h2>Trzy pakiety dla roznych etapow skali</h2>
      </div>
      <div class="shop-tiers-wrap">
        <div class="shop-tier">
          <div class="shop-tier-name">Pakiet Start</div>
          <div class="shop-tier-target">Sklep 30-100k obrotu/mies</div>
          <div class="shop-tier-price"><div class="shop-tier-price-amount">3 200<small>zl</small></div><div class="shop-tier-price-note">+ budzet od 3 000 zl/mies</div></div>
          <ul class="shop-tier-features">
            <li>Google Ads + Meta Ads</li>
            <li>Audyt sklepu pod CR</li>
            <li>GA4 + Pixel + CAPI</li>
            <li>Email automation podstawowe</li>
            <li>Raport miesieczny</li>
          </ul>
          <a href="#kontakt" class="shop-tier-cta" data-cta="pakiet-start">Umow konsultacje</a>
        </div>
        <div class="shop-tier is-featured">
          <div class="shop-tier-name">Pakiet Skala</div>
          <div class="shop-tier-target">Sklep 100-500k obrotu/mies</div>
          <div class="shop-tier-price"><div class="shop-tier-price-amount">5 800<small>zl</small></div><div class="shop-tier-price-note">+ budzet od 8 000 zl/mies</div></div>
          <ul class="shop-tier-features">
            <li>Wszystko z Pakietu Start</li>
            <li>Atrybucja data-driven</li>
            <li>Landing pages kategorii</li>
            <li>Email + SMS rozbudowane</li>
            <li>SEO content commerce</li>
            <li>Cotygodniowe call'e</li>
          </ul>
          <a href="#kontakt" class="shop-tier-cta" data-cta="pakiet-skala">Umow konsultacje</a>
        </div>
        <div class="shop-tier">
          <div class="shop-tier-name">Pakiet Scale-up</div>
          <div class="shop-tier-target">Sklep 500k+ obrotu/mies</div>
          <div class="shop-tier-price"><div class="shop-tier-price-amount">9 500<small>zl</small></div><div class="shop-tier-price-note">+ budzet od 25 000 zl/mies</div></div>
          <ul class="shop-tier-features">
            <li>Wszystko z Pakietu Skala</li>
            <li>Multi-account scaling</li>
            <li>Dodatkowe kanaly (TikTok/Pinterest)</li>
            <li>Ekspansja zagraniczna</li>
            <li>Program testow CRO</li>
            <li>Quarterly business review</li>
          </ul>
          <a href="#kontakt" class="shop-tier-cta" data-cta="pakiet-scale-up">Umow konsultacje</a>
        </div>
      </div>
    </div>
  </section>

  <section class="shop-section shop-section-soft">
    <div class="shop-container">
      <div class="shop-section-head">
        <span class="shop-section-eyebrow">Szczerze</span>
        <h2>Czego NIE dostaniesz (i dlaczego dobrze)</h2>
      </div>
      <div class="shop-honest">
        <div class="shop-honest-header"><div class="shop-honest-icon">!</div><strong>W pakiecie nie znajdziesz:</strong></div>
        <ul>
          <li>Obietnic "10× ROAS w 30 dni"</li>
          <li>Skalowania bez porzadnej atrybucji</li>
          <li>Konkurowania tylko cena z marketplace</li>
          <li>Pustych "vanity metrics"</li>
          <li>Dzialan bez kontroli marzy</li>
          <li>Kampanii bez strategii retencji</li>
        </ul>
      </div>
    </div>
  </section>

  <section class="shop-section">
    <div class="shop-container">
      <div class="shop-section-head">
        <span class="shop-section-eyebrow">Jak zaczynamy</span>
        <h2>Cztery kroki od audytu do skali</h2>
      </div>
      <div class="shop-steps">
        <div class="shop-step"><div class="shop-step-num">01</div><h4>Audyt + konsultacja</h4><p>Analiza kont reklamowych, sklepu i danych GA4.</p><span class="shop-step-time">Tydzien 1</span></div>
        <div class="shop-step"><div class="shop-step-num">02</div><h4>Setup techniczny</h4><p>Naprawa trackingu, feedow i bledow atrybucji.</p><span class="shop-step-time">Tygodnie 2-3</span></div>
        <div class="shop-step"><div class="shop-step-num">03</div><h4>Start kampanii</h4><p>Restrukturyzacja i uruchomienie lejka multi-channel.</p><span class="shop-step-time">Miesiac 1-2</span></div>
        <div class="shop-step"><div class="shop-step-num">04</div><h4>Skalowanie</h4><p>Optymalizacja ROAS, AOV, CR i retencji w cyklach kwartalnych.</p><span class="shop-step-time">Cyklicznie</span></div>
      </div>
    </div>
  </section>

  <section class="shop-section shop-section-soft">
    <div class="shop-container">
      <div class="shop-section-head">
        <span class="shop-section-eyebrow">Konkret zamiast obietnic</span>
        <h2>Pierwsze 90 dni sklepu fashion</h2>
      </div>
      <div class="shop-example-wrap">
        <div class="shop-example-eyebrow">Sklep fashion premium — Polska</div>
        <h3>Z 150k do 428k przychodu/mies przy ROAS 4,2×</h3>
        <p class="shop-example-context">Uporzadkowanie struktury kampanii, atrybucji i automatyzacji email przejelo kontrole nad rentownoscia.</p>
        <div class="shop-comparison">
          <div class="shop-state">
            <span class="shop-state-label">Przed</span>
            <h4>Q4 2025</h4>
            <div class="shop-state-metric"><span>Przychod / mies</span><strong>~150k zl</strong></div>
            <div class="shop-state-metric"><span>ROAS laczny</span><strong>1,8×</strong></div>
            <div class="shop-state-metric"><span>CR sklepu</span><strong>1,8%</strong></div>
            <div class="shop-state-metric"><span>AOV</span><strong>185 zl</strong></div>
            <div class="shop-state-metric"><span>Email % przychodu</span><strong>4%</strong></div>
          </div>
          <div class="shop-state is-after">
            <span class="shop-state-label">Po 90 dniach</span>
            <h4>Q1 2026</h4>
            <div class="shop-state-metric"><span>Przychod / mies</span><strong>~428k zl</strong></div>
            <div class="shop-state-metric"><span>ROAS laczny</span><strong>4,2×</strong></div>
            <div class="shop-state-metric"><span>CR sklepu</span><strong>3,4%</strong></div>
            <div class="shop-state-metric"><span>AOV</span><strong>218 zl</strong></div>
            <div class="shop-state-metric"><span>Email % przychodu</span><strong>22%</strong></div>
          </div>
        </div>
        <div class="shop-example-summary">
          <div class="shop-example-summary-text">Klucz: czyste dane, poprawna atrybucja i scalony lejek Google + Meta + Email.</div>
          <div class="shop-example-summary-amount">+185%<small>wzrost przychodu w 90 dni</small></div>
        </div>
      </div>
    </div>
  </section>

  <section class="shop-section">
    <div class="shop-container">
      <div class="shop-section-head">
        <span class="shop-section-eyebrow">Dla kogo</span>
        <h2>Dla kogo to ma sens</h2>
      </div>
      <div class="shop-target">
        <div class="shop-target-col is-yes">
          <h4>Pakiet ma sens jesli:</h4>
          <ul>
            <li>→ Sklep dziala min. 6 miesiecy</li>
            <li>→ Przychod min. 30 000 zl/mies</li>
            <li>→ Masz budzet reklamowy min. 3 000 zl/mies</li>
            <li>→ Platforma: Shopify / Woo / Magento</li>
            <li>→ Sprzedajesz produkty fizyczne</li>
            <li>→ Masz dane zakupowe do nauki algorytmow</li>
          </ul>
        </div>
        <div class="shop-target-col is-no">
          <h4>Pakiet NIE ma sensu jesli:</h4>
          <ul>
            <li>→ Sklep dopiero startuje</li>
            <li>→ Glowny kanal to Allegro/Amazon</li>
            <li>→ Marza ponizej 30%</li>
            <li>→ Mniej niz 10 produktow</li>
            <li>→ Brak stabilnej oferty produktowej</li>
            <li>→ Czeste pivoty i przebudowy sklepu</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section class="shop-section shop-section-soft">
    <div class="shop-container">
      <div class="shop-section-head">
        <span class="shop-section-eyebrow">Kto bedzie z Toba pracowal</span>
        <h2>Po drugiej stronie ekranu</h2>
      </div>
      <div class="shop-author">
        <div class="shop-author-photo">SK</div>
        <div class="shop-author-content">
          <h3>Sebastian Kelm</h3>
          <p class="shop-author-role">Konsultant marketingu — Google Ads · Meta Ads · Email · CRO · Analytics</p>
          <p>Pracuje bezposrednio ze sklepami i skupiam sie na metrykach, ktore decyduja o zysku: ROAS, AOV, CR, CAC, LTV i retencji.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="shop-section">
    <div class="shop-container">
      <div class="shop-section-head is-center">
        <span class="shop-section-eyebrow">FAQ</span>
        <h2>Pytania, ktore dostaje najczesciej</h2>
      </div>
      <div class="shop-faq-list">
        <details class="shop-faq">
          <summary>Ile to kosztuje miesiecznie razem z reklamami?</summary>
          <div class="shop-faq-content"><p>Pakiety: <strong>3 200 zl</strong>, <strong>5 800 zl</strong>, <strong>9 500 zl</strong> + budzet reklamowy zalezny od skali.</p></div>
        </details>
        <details class="shop-faq">
          <summary>Po jakim czasie zobacze wzrost ROAS?</summary>
          <div class="shop-faq-content"><p>Pierwsze poprawki zwykle 14-21 dni, stabilne efekty zwykle 60-90 dni.</p></div>
        </details>
        <details class="shop-faq">
          <summary>Jak liczysz atrybucje miedzy Google a Meta?</summary>
          <div class="shop-faq-content"><p>GA4 data-driven jako baza + server-side tracking i porownanie do danych platform.</p></div>
        </details>
      </div>
    </div>
  </section>

  <section class="shop-final" id="kontakt">
    <div class="shop-container shop-final-inner">
      <h2>Umow bezplatny audyt + konsultacje</h2>
      <p class="shop-final-sub">60-90 minut online. Pokaze 3 najwieksze problemy konta i potencjal wzrostu zanim zaproponuje pakiet.</p>

      <form class="shop-form" method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" data-form="sklep-internetowy">
        <input type="hidden" name="action" value="upsellio_submit_lead">
        <?php wp_nonce_field("upsellio_unified_lead_form", "upsellio_lead_form_nonce"); ?>
        <input type="hidden" name="lead_form_origin" value="sklep-internetowy-form">
        <input type="hidden" name="lead_source" value="sklep-internetowy-form">
        <input type="hidden" name="lead_service" value="Marketing sklepu internetowego">
        <input type="hidden" name="lead_goal" value="Skalowanie sprzedazy e-commerce z rentownym ROAS i poprawna atrybucja">
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
          <div class="shop-form-feedback is-success">Dziekuje! Formularz zostal wyslany. Odezwiemy sie najszybciej jak to mozliwe.</div>
        <?php elseif ($status === "error") : ?>
          <div class="shop-form-feedback is-error">Nie udalo sie wyslac formularza. Sprobuj ponownie za chwile.</div>
        <?php endif; ?>

        <div class="shop-form-grid">
          <div class="shop-field">
            <label for="shop_name">Imię</label>
            <input type="text" id="shop_name" name="lead_name" placeholder="Jan" required>
          </div>
          <div class="shop-field">
            <label for="shop_phone">Telefon</label>
            <input type="tel" id="shop_phone" name="lead_phone" placeholder="+48 575 522 595" autocomplete="tel" required>
          </div>
          <div class="shop-field shop-field-full">
            <label for="shop_email">E-mail</label>
            <input type="email" id="shop_email" name="lead_email" placeholder="jan@sklep.pl" required>
          </div>
          <div class="shop-field shop-field-full">
            <label for="shop_message">Wiadomość <span class="opt">opcjonalnie</span></label>
            <textarea id="shop_message" name="lead_message" placeholder="Opcjonalnie — krótko, o co chodzi"></textarea>
          </div>
        </div>

        <div class="shop-field shop-field-full">
          <input type="checkbox" id="shop_consent" name="lead_consent" value="1" required style="margin-right: 8px;">
          <label for="shop_consent" style="display: inline; font-size: 13px;">Wyrażam zgodę na kontakt w sprawie przesłanego zapytania.</label>
        </div>

        <button type="submit" class="shop-form-submit" data-cta="form-submit">Oddzwonię w ciągu 24h</button>

        <p class="shop-form-meta">
          Twoje dane sluza wylacznie do umowienia audytu. Nie zapisuje Cie na newsletter i nie sprzedaje bazy.
        </p>
      </form>
    </div>
  </section>
</div>

<?php get_footer(); ?>
