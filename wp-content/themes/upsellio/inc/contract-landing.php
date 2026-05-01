<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_contract_provider_party_defaults()
{
    return (array) apply_filters("upsellio_contract_provider_party", [
        "brand" => "Upsellio",
        "signer_name" => "Sebastian Kelm",
        "meta_line" => "Upsellio · upsellio.pl",
        "nip" => "",
    ]);
}

function upsellio_contract_render_public_landing(WP_Post $contract)
{
    $contract_id = (int) $contract->ID;
    $token = (string) get_post_meta($contract_id, "_ups_contract_public_token", true);
    $client_id = (int) get_post_meta($contract_id, "_ups_contract_client_id", true);
    $offer_id = (int) get_post_meta($contract_id, "_ups_contract_offer_id", true);
    $status = (string) get_post_meta($contract_id, "_ups_contract_status", true);
    if ($status === "") {
        $status = "draft";
    }
    $version = max(1, (int) get_post_meta($contract_id, "_ups_contract_version", true));
    $accept_name = (string) get_post_meta($contract_id, "_ups_contract_accept_name", true);
    $sent_at = (string) get_post_meta($contract_id, "_ups_contract_sent_at", true);
    $signed_at = (string) get_post_meta($contract_id, "_ups_contract_signed_at", true);
    $created_ts = strtotime((string) $contract->post_date_gmt . " UTC");
    $contract_created_label = $created_ts ? (string) wp_date("j.m.Y H:i", $created_ts) : "";
    $sent_label = $sent_at !== "" ? (string) wp_date("j.m.Y H:i", strtotime($sent_at)) : "—";
    $signed_label = $signed_at !== "" ? (string) wp_date("j.m.Y H:i", strtotime($signed_at)) : "";

    $provider = upsellio_contract_provider_party_defaults();
    $provider_brand = (string) ($provider["brand"] ?? "Upsellio");
    $provider_signer = (string) ($provider["signer_name"] ?? "");
    $provider_line = (string) ($provider["meta_line"] ?? "");
    $provider_nip = (string) ($provider["nip"] ?? "");

    $client_name = $client_id > 0 ? (string) get_the_title($client_id) : "Klient";
    $client_company = $client_id > 0 ? (string) get_post_meta($client_id, "_ups_client_company", true) : "";
    $client_email = $client_id > 0 ? (string) get_post_meta($client_id, "_ups_client_email", true) : "";
    $client_nip = $client_id > 0 ? (string) get_post_meta($client_id, "_ups_client_nip", true) : "";

    $person_id = "";
    if ($offer_id > 0) {
        $person_id = (string) get_post_meta($offer_id, "_ups_offer_person_id", true);
    }
    if ($person_id === "" && $client_id > 0) {
        $person_id = (string) get_post_meta($client_id, "_ups_client_person_id", true);
    }

    $owner_email = (string) get_option("admin_email");
    $owner_display = $provider_signer !== "" ? $provider_signer : $provider_brand;
    if ($offer_id > 0) {
        $owner_id = (int) get_post_meta($offer_id, "_ups_offer_owner_id", true);
        if ($owner_id <= 0 && function_exists("upsellio_crm_get_default_owner_id")) {
            $owner_id = (int) upsellio_crm_get_default_owner_id();
        }
        if ($owner_id > 0) {
            $u = get_userdata($owner_id);
            if ($u instanceof WP_User && is_email((string) $u->user_email)) {
                $owner_email = (string) $u->user_email;
            }
            if ($u instanceof WP_User && (string) $u->display_name !== "") {
                $owner_display = (string) $u->display_name;
            }
        }
    }

    $body_html = (string) get_post_meta($contract_id, "_ups_contract_html", true);
    if ($body_html === "") {
        $body_html = wpautop((string) $contract->post_content);
    }
    $body_html = function_exists("upsellio_contracts_replace_placeholders")
        ? (string) upsellio_contracts_replace_placeholders($body_html, $client_id, $offer_id, $contract_id)
        : $body_html;

    $extra_css = (string) get_post_meta($contract_id, "_ups_contract_css", true);

    $pdf_url = (string) apply_filters("upsellio_contract_pdf_url", "", $contract_id, $token);
    $has_pdf = $pdf_url !== "";

    $gtm = function_exists("upsellio_get_site_gtm_container_id") ? (string) upsellio_get_site_gtm_container_id() : "";
    $ajax_url = admin_url("admin-ajax.php");
    $contract_title = (string) $contract->post_title;

    if ($status === "draft") {
        $chip_class = "s-draft";
        $chip_label = "Wersja robocza";
    } elseif ($status === "signed") {
        $chip_class = "s-signed";
        $chip_label = "Podpisana";
    } else {
        $chip_class = "s-sent";
        $chip_label = "Oczekuje na podpis";
    }

    status_header(200);
    nocache_headers();
    ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo("charset"); ?>"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<meta name="robots" content="noindex,nofollow"/>
<title><?php echo esc_html($contract_title); ?> — Upsellio</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&amp;family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&amp;display=swap" rel="stylesheet"/>
<?php if ($gtm !== "") : ?>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','<?php echo esc_js($gtm); ?>');</script>
<?php endif; ?>
<script>
window.dataLayer=window.dataLayer||[];
window.UPS={
  contract_id:'<?php echo esc_js((string) $contract_id); ?>',
  contract_token:'<?php echo esc_js($token); ?>',
  contract_title:'<?php echo esc_js($contract_title); ?>',
  offer_id:'<?php echo esc_js((string) $offer_id); ?>',
  person_id:'<?php echo esc_js($person_id); ?>'
};
dataLayer.push({event:'contract_view',contract_id:UPS.contract_id,contract_title:UPS.contract_title,person_id:UPS.person_id,offer_id:UPS.offer_id});
</script>
<style>
:root{
  --bg:#fafaf7;--surface:#fff;--ink:#0a1410;--ink2:#2e2e2a;--muted:#6b6b63;
  --border:#e6e6e0;--border-mid:#d0d0c8;
  --teal:#0d9488;--tealh:#0f766e;--teald:#134e4a;--teals:#ccfbf1;--teall:#99f6e4;
  --warn:#c07d10;--warns:#fefce8;--warnl:#fde68a;
  --font-d:'Syne',sans-serif;--font-b:'DM Sans',sans-serif;
  --font-m:ui-monospace,'Cascadia Code',monospace;
  --r:14px;--rl:22px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:var(--font-b);background:var(--bg);color:var(--ink);font-size:15px;line-height:1.65;-webkit-font-smoothing:antialiased}
a{text-decoration:none;color:inherit}
#bar{position:fixed;top:0;left:0;height:2px;background:var(--teal);z-index:100;width:0;transition:width .1s linear}
.nav{position:sticky;top:0;z-index:50;background:rgba(250,250,247,.94);backdrop-filter:blur(12px);border-bottom:1px solid var(--border)}
.nav-in{max-width:960px;margin:0 auto;padding:0 24px;height:60px;display:flex;align-items:center;justify-content:space-between;gap:16px}
.nav-logo{display:flex;align-items:center;gap:9px}
.nav-logo--home{color:inherit}
.brand-logo-nav,.brand-logo-doc,.brand-logo-foot{width:auto;max-width:min(280px,52vw);height:auto;object-fit:contain;display:block}
.brand-logo-nav{max-height:34px}
.brand-logo-doc{max-height:46px}
.brand-logo-foot{max-height:26px}
.doc-head-logo picture{display:flex;align-items:center}
.foot-logo a{display:inline-flex;align-items:center;gap:10px;color:inherit;text-decoration:none;font-weight:700;font-size:13px}
.mark{width:32px;height:32px;border-radius:9px;background:linear-gradient(160deg,#14b8a6,#0f766e);color:#fff;display:grid;place-items:center;font-family:var(--font-d);font-weight:800;font-size:15px;flex-shrink:0}
.nav-name{font-family:var(--font-d);font-size:17px;font-weight:800;letter-spacing:-.3px}
.nav-meta{display:flex;align-items:center;gap:10px;font-size:13px;color:var(--muted)}
.vchip{display:inline-flex;align-items:center;padding:3px 10px;background:var(--bg);border:1px solid var(--border);border-radius:999px;font-size:11px;font-family:var(--font-m);color:var(--muted)}
.status-chip{display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:999px;font-size:12px;font-weight:700;border:1px solid}
.s-sent{background:var(--warns);border-color:var(--warnl);color:var(--warn)}
.s-draft{background:#f1f1ec;border-color:var(--border);color:var(--muted)}
.s-signed{background:var(--teals);border-color:var(--teall);color:var(--teald)}
.btn{display:inline-flex;align-items:center;gap:7px;border-radius:999px;font-family:var(--font-b);font-weight:700;font-size:14px;border:1px solid transparent;transition:all .18s;cursor:pointer}
.btn-p{background:var(--teal);color:#fff;padding:10px 22px;box-shadow:0 6px 18px rgba(13,148,136,.22)}
.btn-p:hover{background:var(--tealh);transform:translateY(-1px)}
.btn-g{background:var(--surface);border-color:var(--border);color:var(--ink);padding:9px 18px}
.btn-g:hover{border-color:var(--teal);color:var(--teal)}
.btn-dl-disabled{opacity:.45;pointer-events:none;cursor:not-allowed}
.snav{background:var(--surface);border-bottom:1px solid var(--border);position:sticky;top:60px;z-index:40;overflow-x:auto;scrollbar-width:none}
.snav::-webkit-scrollbar{display:none}
.snav-in{max-width:960px;margin:0 auto;padding:0 24px;display:flex;gap:0;min-width:max-content}
.snav-link{display:flex;align-items:center;gap:6px;padding:11px 16px;font-size:13px;font-weight:600;color:var(--muted);border-bottom:2px solid transparent;transition:all .18s;cursor:pointer;white-space:nowrap;user-select:none}
.snav-link:hover{color:var(--ink)}
.snav-link.active{color:var(--teal);border-bottom-color:var(--teal)}
.snav-num{width:18px;height:18px;border-radius:50%;background:var(--border);font-size:10px;font-weight:800;display:grid;place-items:center;color:var(--muted);transition:.18s;flex-shrink:0}
.snav-link.active .snav-num{background:var(--teals);color:var(--teald)}
.w{max-width:960px;margin:0 auto;padding:0 24px}
.page{max-width:960px;margin:0 auto;padding:36px 24px 80px;display:grid;grid-template-columns:1fr 288px;gap:32px;align-items:start}
.hr{height:1px;background:var(--border)}
.r{opacity:0;transform:translateY(16px);transition:opacity .5s ease,transform .5s ease}
.r.in{opacity:1;transform:none}
.status-bar{background:var(--surface);border:1px solid var(--border);border-radius:var(--rl);padding:18px 24px;margin-bottom:24px;display:flex;align-items:center;gap:0;overflow-x:auto}
.sb-step{display:flex;align-items:center;gap:8px;flex-shrink:0}
.sb-dot{width:30px;height:30px;border-radius:50%;border:1.5px solid var(--border);background:var(--bg);display:grid;place-items:center;font-size:12px;font-weight:700;color:var(--muted);flex-shrink:0;transition:.2s}
.sb-step.done .sb-dot{background:var(--teals);border-color:var(--teall);color:var(--teald)}
.sb-step.current .sb-dot{background:var(--teal);border-color:var(--teal);color:#fff;box-shadow:0 4px 12px rgba(13,148,136,.3)}
.sb-label{font-size:12px;font-weight:600;color:var(--muted);white-space:nowrap}
.sb-step.done .sb-label{color:var(--teald)}
.sb-step.current .sb-label{color:var(--ink)}
.sb-conn{flex:1;min-width:24px;height:1.5px;background:var(--border);margin:0 8px}
.sb-conn.done{background:var(--teall)}
.contract-doc{background:var(--surface);border:1px solid var(--border);border-radius:var(--rl);overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.05)}
.doc-head{padding:28px 36px 24px;border-bottom:1px solid var(--border);background:linear-gradient(135deg,var(--teals) 0%,rgba(255,255,255,0) 60%)}
.doc-head-logo{display:flex;align-items:center;gap:9px;margin-bottom:18px}
.doc-head-brand{font-family:var(--font-d);font-size:16px;font-weight:800;letter-spacing:-.3px}
.doc-title{font-family:var(--font-d);font-size:24px;font-weight:700;letter-spacing:-.5px;margin-bottom:5px}
.doc-id{font-size:13px;color:var(--muted);font-family:var(--font-m)}
.doc-meta-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:20px}
.doc-meta-cell{padding:13px 16px;background:rgba(255,255,255,.75);border:1px solid var(--border);border-radius:var(--r)}
.doc-meta-label{font-size:11px;font-weight:700;letter-spacing:.7px;text-transform:uppercase;color:var(--muted);margin-bottom:4px}
.doc-meta-value{font-size:14px;font-weight:600;color:var(--ink)}
.doc-meta-sub{font-size:12px;color:var(--muted);margin-top:2px}
.doc-body{padding:36px;font-size:15px;line-height:1.85;color:var(--ink2)}
.doc-body h1{font-family:var(--font-d);color:var(--ink);font-size:1.1rem;font-weight:800;letter-spacing:-.2px;margin:2.2em 0 .7em;padding-top:1.6em;border-top:1px solid var(--border);display:flex;align-items:center;gap:10px}
.doc-body h1:first-child{border-top:none;padding-top:0;margin-top:0}
.doc-body h1 .par-num{width:26px;height:26px;border-radius:50%;background:var(--teals);border:1px solid var(--teall);display:grid;place-items:center;font-size:11px;font-weight:800;color:var(--teald);flex-shrink:0}
.doc-body h2{font-family:var(--font-d);color:var(--ink);font-size:1rem;font-weight:700;margin:1.6em 0 .5em}
.doc-body p{margin-bottom:.85em}
.doc-body ul,.doc-body ol{margin:.8em 0 .8em 1.4em}
.doc-body li{margin-bottom:.4em}
.doc-body strong{color:var(--ink);font-weight:700}
.doc-body table{width:100%;border-collapse:collapse;margin:1.4em 0;font-size:14px}
.doc-body th{background:var(--teals);color:var(--teald);padding:9px 14px;text-align:left;font-size:12px;font-weight:700;letter-spacing:.4px;text-transform:uppercase}
.doc-body td{padding:10px 14px;border-bottom:1px solid var(--border)}
.doc-body blockquote{border-left:3px solid var(--teal);padding:12px 18px;background:var(--teals);border-radius:0 var(--r) var(--r) 0;margin:1.4em 0;color:var(--teald)}
.highlight-box{background:linear-gradient(135deg,var(--teals),rgba(255,255,255,.5));border:1px solid var(--teall);border-radius:var(--r);padding:16px 20px;margin:1.4em 0}
.highlight-box strong{color:var(--teald)}
.sec-divider{display:flex;align-items:center;gap:8px;font-size:10px;font-weight:700;letter-spacing:1.4px;text-transform:uppercase;color:var(--muted);margin:2em 0 1em}
.sec-divider::before,.sec-divider::after{content:'';flex:1;height:1px;background:var(--border)}
.sec-divider::before{max-width:20px}
.sig-section{padding:0 36px 32px;margin-top:4px}
.sig-label{font-size:11px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--muted);margin-bottom:14px}
.sig-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
.sig-party{padding:18px 20px;border:1px solid var(--border);border-radius:var(--r);background:var(--bg)}
.sig-party-who{font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--muted);margin-bottom:6px}
.sig-party-name{font-size:15px;font-weight:700;color:var(--ink);margin-bottom:2px}
.sig-party-role{font-size:13px;color:var(--muted);margin-bottom:16px}
.sig-line{height:44px;border-bottom:2px solid var(--border-mid);display:flex;align-items:flex-end;padding-bottom:6px}
.sig-signed{font-family:var(--font-d);font-size:17px;font-weight:700;color:var(--teal);letter-spacing:-.2px}
.sig-placeholder{font-size:13px;color:var(--muted);font-style:italic}
.sig-date{font-size:12px;color:var(--muted);margin-top:7px}
.sig-date.done{color:var(--teald);font-weight:600}
.sidebar-sticky{position:sticky;top:120px}
.sidebar-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--rl);padding:22px;box-shadow:0 8px 32px rgba(0,0,0,.07),0 2px 8px rgba(0,0,0,.04);margin-bottom:14px}
.sb-title{font-size:11px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--muted);margin-bottom:12px}
.sb-row{display:flex;justify-content:space-between;align-items:baseline;font-size:13px;padding:7px 0;border-bottom:1px solid var(--border);gap:10px}
.sb-row:last-of-type{border-bottom:none}
.sb-l{color:var(--muted);flex-shrink:0}
.sb-r{font-weight:600;color:var(--ink);text-align:right}
.steps-card{background:var(--surface);border:1.5px solid var(--teall);border-radius:var(--rl);padding:22px;box-shadow:0 8px 32px rgba(13,148,136,.08)}
.steps-card-title{font-family:var(--font-d);font-size:17px;font-weight:700;letter-spacing:-.2px;color:var(--teald);margin-bottom:6px}
.steps-card-sub{font-size:13px;color:var(--teal);margin-bottom:18px;line-height:1.5}
.next-steps{display:grid;gap:12px}
.ns-item{display:flex;gap:12px;align-items:flex-start}
.ns-num{width:26px;height:26px;border-radius:50%;background:var(--teal);color:#fff;display:grid;place-items:center;font-family:var(--font-d);font-weight:800;font-size:12px;flex-shrink:0;margin-top:1px}
.ns-title{font-size:13px;font-weight:700;color:var(--ink);margin-bottom:2px}
.ns-desc{font-size:12px;color:var(--muted);line-height:1.5}
.ns-action{margin-top:6px}
.ns-action a{display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:700;color:var(--teal);padding:5px 12px;border:1px solid var(--teall);border-radius:999px;background:var(--teals);transition:.15s}
.ns-action a:hover{background:var(--teall)}
.ns-action a svg{width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2}
.reminder-notice{background:var(--warns);border:1px solid var(--warnl);border-radius:var(--r);padding:12px 14px;display:flex;gap:10px;align-items:flex-start;margin-top:14px;font-size:12px;color:var(--warn);line-height:1.5}
.reminder-notice.hidden{display:none}
.reminder-notice svg{flex-shrink:0;margin-top:1px}
.toc-list{display:grid;gap:2px}
.toc-link{font-size:12px;color:var(--muted);padding:5px 8px;border-radius:var(--r);transition:.15s;display:flex;align-items:center;gap:6px;cursor:pointer;user-select:none}
.toc-link:hover{background:var(--bg);color:var(--teal)}
.toc-link::before{content:'';width:4px;height:4px;border-radius:50%;background:var(--border);flex-shrink:0}
.toc-link.active{color:var(--teald);font-weight:600}
.toc-link.active::before{background:var(--teal)}
.tl-list{display:grid;gap:0}
.tl-item{display:grid;grid-template-columns:26px 1fr;gap:10px;position:relative;padding-bottom:12px}
.tl-item:last-child{padding-bottom:0}
.tl-dot{width:26px;height:26px;border-radius:50%;background:var(--teals);border:1.5px solid var(--teall);display:grid;place-items:center;z-index:1}
.tl-line{position:absolute;left:12px;top:28px;bottom:0;width:1.5px;background:var(--border)}
.tl-item:last-child .tl-line{display:none}
.tl-ts{font-size:11px;color:var(--muted);margin-bottom:2px}
.tl-text{font-size:12px;color:var(--ink2);line-height:1.45}
.accept-mini{padding:14px;border:1px solid var(--border);border-radius:var(--r);background:var(--bg);margin-top:14px}
.accept-mini h4{font-size:13px;margin-bottom:8px;font-family:var(--font-d)}
.foot{background:var(--surface);border-top:1px solid var(--border);padding:18px 24px}
.foot-in{max-width:960px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;gap:16px;font-size:12px;color:var(--muted);flex-wrap:wrap}
.foot-logo{display:flex;align-items:center;gap:8px;color:var(--ink);font-weight:700;font-size:13px}
@media(max-width:820px){.page{grid-template-columns:1fr}.sidebar-sticky{position:static}.doc-meta-grid{grid-template-columns:1fr}.sig-grid{grid-template-columns:1fr}.doc-head,.doc-body,.sig-section{padding-left:22px;padding-right:22px}}
@media(max-width:560px){.w{padding:0 18px}.page{padding:24px 18px 60px}.nav-meta{display:none}}
<?php echo $extra_css !== "" ? "\n/* Custom */\n" . wp_strip_all_tags($extra_css) : ""; ?>
</style>
</head>
<body>
<?php if ($gtm !== "") : ?>
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr($gtm); ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<?php endif; ?>
<div id="bar"></div>

<nav class="nav">
  <div class="nav-in">
    <a href="<?php echo esc_url(home_url("/")); ?>" class="nav-logo nav-logo--home">
      <?php
      $logo_ok = function_exists("upsellio_echo_brand_logo_picture")
          && upsellio_echo_brand_logo_picture([
              "img_class" => "brand-logo-nav",
              "sizes" => "140px",
              "fetchpriority" => "high",
          ]);
      if (!$logo_ok) :
          ?>
      <div class="mark">U</div><div class="nav-name"><?php echo esc_html($provider_brand); ?></div>
      <?php endif; ?>
    </a>
    <div class="nav-meta">
      <span><?php echo esc_html($contract_title); ?></span>
      <span class="status-chip <?php echo esc_attr($chip_class); ?>" id="statusChip"><?php echo esc_html($chip_label); ?></span>
    </div>
    <div class="vchip">v<?php echo esc_html((string) $version); ?></div>
  </div>
</nav>

<div class="snav" id="snav">
  <div class="snav-in">
    <div class="snav-link active" data-target="sec-status" onclick="jumpTo('sec-status')"><div class="snav-num">1</div>Status</div>
    <div class="snav-link" data-target="sec-doc" onclick="jumpTo('sec-doc')"><div class="snav-num">2</div>Treść</div>
    <div class="snav-link" data-target="sec-podpisy" onclick="jumpTo('sec-podpisy')"><div class="snav-num">3</div>Podpisy</div>
    <div class="snav-link" data-target="sec-historia" onclick="jumpTo('sec-historia')"><div class="snav-num">4</div>Historia</div>
  </div>
</div>

<div class="page">
  <div>
    <div class="status-bar r" id="sec-status">
      <?php
      $map = upsellio_contract_landing_step_map($status);
      $steps = [
          ["id" => "sb1", "label" => "Przygotowana"],
          ["id" => "sb2", "label" => "Wysłana"],
          ["id" => "sb3", "label" => "Do podpisania"],
          ["id" => "sb4", "label" => "Podpisana"],
      ];
      foreach ($steps as $si => $st) {
          $cls = $map[$si];
          $step_class = "sb-step" . ($cls !== "" ? " " . $cls : "");
          echo '<div class="' . esc_attr($step_class) . '" id="' . esc_attr($st["id"]) . '"><div class="sb-dot">';
          echo $cls === "done" ? "✓" : esc_html((string) ($si + 1));
          echo '</div><div class="sb-label">' . esc_html($st["label"]) . '</div></div>';
          if ($si < count($steps) - 1) {
              $conn_done = ($map[$si] === "done");
              echo '<div class="sb-conn' . ($conn_done ? " done" : "") . '"></div>';
          }
      }
      ?>
    </div>

    <div class="contract-doc r" id="sec-doc">
      <div class="doc-head">
        <div class="doc-head-logo">
          <?php
          $doc_logo_ok = function_exists("upsellio_echo_brand_logo_picture")
              && upsellio_echo_brand_logo_picture([
                  "img_class" => "brand-logo-doc",
                  "sizes" => "220px",
                  "loading" => "lazy",
              ]);
          if (!$doc_logo_ok) :
              ?>
          <div class="mark" style="width:30px;height:30px;font-size:14px;border-radius:8px">U</div>
          <div class="doc-head-brand"><?php echo esc_html($provider_brand); ?></div>
          <?php endif; ?>
        </div>
        <div class="doc-title"><?php echo esc_html($contract_title); ?></div>
        <div class="doc-id">Nr: <?php echo esc_html((string) $contract_id); ?> · Wersja: <?php echo esc_html((string) $version); ?> · Data: <?php echo esc_html($contract_created_label); ?></div>
        <div class="doc-meta-grid">
          <div class="doc-meta-cell">
            <div class="doc-meta-label">Zleceniobiorca</div>
            <div class="doc-meta-value"><?php echo esc_html($provider_signer !== "" ? $provider_signer : $provider_brand); ?></div>
            <div class="doc-meta-sub"><?php echo esc_html($provider_line); ?><?php echo $provider_nip !== "" ? " · NIP: " . esc_html($provider_nip) : ""; ?></div>
          </div>
          <div class="doc-meta-cell">
            <div class="doc-meta-label">Zleceniodawca</div>
            <div class="doc-meta-value"><?php echo esc_html($client_name); ?></div>
            <div class="doc-meta-sub"><?php echo esc_html(trim($client_company . ($client_nip !== "" ? " · NIP: " . $client_nip : ""))); ?></div>
          </div>
        </div>
      </div>

      <div class="doc-body" id="doc-body-section" data-track="contract_body">
        <?php echo wp_kses_post($body_html); ?>
      </div>

      <div class="sig-section" id="sec-podpisy">
        <div class="sec-divider" style="margin-top:0">Miejsca na podpisy</div>
        <div class="sig-label">Strony potwierdzają zawarcie umowy</div>
        <div class="sig-grid">
          <div class="sig-party">
            <div class="sig-party-who">Zleceniobiorca</div>
            <div class="sig-party-name"><?php echo esc_html($provider_signer !== "" ? $provider_signer : $provider_brand); ?></div>
            <div class="sig-party-role"><?php echo esc_html($provider_brand); ?></div>
            <div class="sig-line"><?php if ($status !== "draft") : ?><div class="sig-signed"><?php echo esc_html($provider_signer !== "" ? $provider_signer : $provider_brand); ?></div><?php else : ?><div class="sig-placeholder">Podpis</div><?php endif; ?></div>
            <div class="sig-date done">Data: <?php echo esc_html($contract_created_label); ?></div>
          </div>
          <div class="sig-party">
            <div class="sig-party-who">Zleceniodawca</div>
            <div class="sig-party-name"><?php echo esc_html($client_name); ?></div>
            <div class="sig-party-role"><?php echo esc_html($client_company); ?></div>
            <div class="sig-line"><?php if ($status === "signed" && $accept_name !== "") : ?><div class="sig-signed"><?php echo esc_html($accept_name); ?></div><?php else : ?><div class="sig-placeholder">Podpis i pieczątka</div><?php endif; ?></div>
            <div class="sig-date <?php echo $status === "signed" ? "done" : ""; ?>">Data: <?php echo $status === "signed" ? esc_html($signed_label) : "___________________"; ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div>
    <div class="sidebar-sticky">
      <div class="steps-card r">
        <div class="steps-card-title">Jak podpisać umowę?</div>
        <div class="steps-card-sub">Pobierz PDF (jeśli dostępny), podpisz i odeślij skan.</div>
        <div class="next-steps">
          <div class="ns-item">
            <div class="ns-num">1</div>
            <div>
              <div class="ns-title">Pobierz umowę jako PDF</div>
              <div class="ns-desc">Wygenerowany dokument jednym klikiem.</div>
              <div class="ns-action">
                <a href="<?php echo esc_url($has_pdf ? $pdf_url : "#"); ?>" <?php echo $has_pdf ? 'download' : ''; ?> class="<?php echo $has_pdf ? "" : "btn-dl-disabled"; ?>" id="pdfDownloadBtnSmall" onclick="<?php echo $has_pdf ? "trackDl(event)" : "event.preventDefault();"; ?>">
                  <svg viewBox="0 0 16 16"><path d="M8 2v9M4 7l4 4 4-4"/><path d="M2 13h12"/></svg>
                  Pobierz PDF
                </a>
              </div>
            </div>
          </div>
          <div class="ns-item">
            <div class="ns-num">2</div>
            <div><div class="ns-title">Podpis</div><div class="ns-desc">Odręcznie lub elektronicznie w PDF.</div></div>
          </div>
          <div class="ns-item">
            <div class="ns-num">3</div>
            <div>
              <div class="ns-title">Odeślij skan</div>
              <div class="ns-desc">Na adres opiekuna.</div>
              <div class="ns-action">
                <a href="mailto:<?php echo esc_attr($owner_email); ?>?subject=<?php echo rawurlencode("Podpisana umowa: " . $contract_title); ?>">
                  <svg viewBox="0 0 16 16"><path d="M2 4h12v8H2z" fill="none" stroke-width="1.2"/><path d="m2 4 6 5 6-5"/></svg>
                  Wyślij e-mailem
                </a>
              </div>
            </div>
          </div>
        </div>
        <a href="<?php echo esc_url($has_pdf ? $pdf_url : "#"); ?>" class="<?php echo $has_pdf ? "" : "btn-dl-disabled"; ?>" style="display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:13px;margin-top:18px;border-radius:999px;background:var(--teal);color:#fff;font-weight:700;font-size:14px;box-shadow:0 6px 18px rgba(13,148,136,.22);text-decoration:none" id="pdfDownloadBtnLarge" onclick="<?php echo $has_pdf ? "trackDl(event)" : "event.preventDefault();"; ?>">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v9M4 7l4 4 4-4"/><path d="M2 13h12"/></svg>
          <?php echo $has_pdf ? "Pobierz umowę (PDF)" : "PDF w przygotowaniu"; ?>
        </a>
        <div class="reminder-notice<?php echo $status !== "sent" ? " hidden" : ""; ?>" id="contractReminder">
          <svg width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M10 2L2 16h16L10 2Z"/><path d="M10 9v4M10 14.5h.01"/></svg>
          <span>Umowa oczekuje na podpis. W razie pytań napisz do opiekuna.</span>
        </div>
      </div>

      <?php if ($status !== "signed") : ?>
      <div class="sidebar-card r accept-mini">
        <h4>Akceptacja online</h4>
        <form method="post">
          <?php wp_nonce_field("ups_contract_accept_" . $contract_id, "ups_contract_nonce"); ?>
          <input type="hidden" name="ups_contract_action" value="accept_contract" />
          <p style="margin-bottom:8px"><input type="text" name="ups_contract_accept_name" placeholder="Imię i nazwisko" required style="width:100%;padding:8px;border:1px solid var(--border);border-radius:8px;font:inherit"/></p>
          <label style="display:flex;gap:8px;align-items:flex-start;margin-bottom:10px;font-size:13px"><input type="checkbox" name="ups_contract_accept" value="1" required /> Akceptuję warunki umowy</label>
          <button type="submit" class="btn btn-p" style="width:100%;justify-content:center;border:none">Potwierdzam akceptację</button>
        </form>
      </div>
      <?php endif; ?>

      <div class="sidebar-card r">
        <div class="sb-title">Szczegóły</div>
        <?php
        $ot = $offer_id > 0 ? (string) get_the_title($offer_id) : "—";
        $op = $offer_id > 0 ? (string) get_post_meta($offer_id, "_ups_offer_price", true) : "—";
        $otl = $offer_id > 0 ? (string) get_post_meta($offer_id, "_ups_offer_timeline", true) : "—";
        ?>
        <div class="sb-row"><span class="sb-l">Oferta</span><span class="sb-r"><?php echo esc_html($ot); ?></span></div>
        <div class="sb-row"><span class="sb-l">Wartość</span><span class="sb-r" style="color:var(--teal)"><?php echo esc_html($op); ?></span></div>
        <div class="sb-row"><span class="sb-l">Start</span><span class="sb-r"><?php echo esc_html($otl); ?></span></div>
        <div class="sb-row"><span class="sb-l">Opiekun</span><span class="sb-r"><?php echo esc_html($owner_display); ?></span></div>
        <div class="sb-row"><span class="sb-l">Kontakt</span><span class="sb-r"><a href="mailto:<?php echo esc_attr($owner_email); ?>" style="color:var(--teal);font-weight:600"><?php echo esc_html($owner_email); ?></a></span></div>
      </div>

      <div class="sidebar-card r">
        <div class="sb-title">Spis treści</div>
        <div class="toc-list" id="tocList"></div>
      </div>

      <div class="sidebar-card r" id="sec-historia">
        <div class="sb-title">Historia dokumentu</div>
        <div class="tl-list">
          <div class="tl-item">
            <div style="position:relative"><div class="tl-dot"></div><div class="tl-line"></div></div>
            <div><div class="tl-ts"><?php echo esc_html($contract_created_label); ?></div><div class="tl-text">Umowa <strong>utworzona</strong> (v<?php echo esc_html((string) $version); ?>).</div></div>
          </div>
          <?php if ($sent_at !== "") : ?>
          <div class="tl-item">
            <div style="position:relative"><div class="tl-dot"></div><div class="tl-line"></div></div>
            <div><div class="tl-ts"><?php echo esc_html($sent_label); ?></div><div class="tl-text">Status <strong>wysłana</strong><?php echo $client_email !== "" ? " · " . esc_html($client_email) : ""; ?>.</div></div>
          </div>
          <?php endif; ?>
          <?php if ($status === "signed" && $signed_at !== "") : ?>
          <div class="tl-item">
            <div style="position:relative"><div class="tl-dot" style="background:var(--teal);border-color:var(--teal)"></div></div>
            <div><div class="tl-ts"><?php echo esc_html($signed_label); ?></div><div class="tl-text"><strong>Podpisana</strong><?php echo $accept_name !== "" ? " · " . esc_html($accept_name) : ""; ?>.</div></div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<footer class="foot">
  <div class="foot-in">
    <div class="foot-logo">
      <a href="<?php echo esc_url(home_url("/")); ?>">
        <?php
        $foot_logo_ok = function_exists("upsellio_echo_brand_logo_picture")
            && upsellio_echo_brand_logo_picture([
                "img_class" => "brand-logo-foot",
                "sizes" => "120px",
                "loading" => "lazy",
            ]);
        if (!$foot_logo_ok) :
            ?>
        <div class="mark" style="width:26px;height:26px;font-size:13px;border-radius:7px">U</div><?php echo esc_html($provider_brand); ?>
        <?php endif; ?>
      </a>
    </div>
    <div>Dokument poufny · Wygenerowano <?php echo esc_html($contract_created_label); ?></div>
    <div>Token: <code style="font-size:11px;font-family:var(--font-m);background:var(--bg);padding:2px 6px;border-radius:4px"><?php echo esc_html($token); ?></code></div>
  </div>
</footer>

<script>
(function(){
var ajaxUrl=<?php echo wp_json_encode($ajax_url); ?>;
var token=<?php echo wp_json_encode($token); ?>;
window.addEventListener('scroll',function(){
  var h=document.documentElement.scrollHeight-document.documentElement.clientHeight;
  document.getElementById('bar').style.width=(h>0?Math.min(window.scrollY/h*100,100):0)+'%';
},{passive:true});
var ro=new IntersectionObserver(function(e){e.forEach(function(x){if(x.isIntersecting)x.target.classList.add('in');});},{threshold:.08});
document.querySelectorAll('.r').forEach(function(el){ro.observe(el);});
var snavLinks=document.querySelectorAll('.snav-link');
var snavSecs=Array.from(snavLinks).map(function(l){return document.getElementById(l.dataset.target);}).filter(Boolean);
function updateSnav(){
  var top=window.scrollY+130;
  var active=null;
  snavSecs.forEach(function(s){if(s&&s.offsetTop<=top)active=s;});
  if(!active)active=snavSecs[0];
  snavLinks.forEach(function(l){l.classList.toggle('active',active&&l.dataset.target===active.id);});
}
window.addEventListener('scroll',updateSnav,{passive:true});
updateSnav();
function jumpTo(id){
  var el=document.getElementById(id);
  if(!el)return;
  window.scrollTo({top:el.getBoundingClientRect().top+window.scrollY-130,behavior:'smooth'});
  pushDl('contract_jump',{target:id});
}
window.jumpTo=jumpTo;

function buildToc(){
  var body=document.getElementById('doc-body-section');
  var toc=document.getElementById('tocList');
  if(!body||!toc)return;
  toc.innerHTML='';
  var heads=body.querySelectorAll('h1[id],h2[id]');
  if(!heads.length)return;
  heads.forEach(function(h){
    var row=document.createElement('div');
    row.className='toc-link';
    row.textContent=h.textContent.trim().slice(0,72);
    row.onclick=function(){jumpTo(h.id);};
    toc.appendChild(row);
  });
}
buildToc();

var headings=document.querySelectorAll('#doc-body-section [id]');
var tocLinks=document.querySelectorAll('.toc-link');
if(headings.length&&tocLinks.length){
  var tocObs=new IntersectionObserver(function(entries){
    entries.forEach(function(x){
      if(!x.isIntersecting)return;
      var id=x.target.id;
      tocLinks.forEach(function(l){
        var txt=l.textContent.trim();
        var hx=x.target.textContent.trim().slice(0,72);
        l.classList.toggle('active',txt===hx||txt.indexOf(hx)===0);
      });
    });
  },{threshold:.6,rootMargin:'-80px 0px -40% 0px'});
  headings.forEach(function(h){if(h.id)tocObs.observe(h);});
}

var bodyEl=document.getElementById('doc-body-section');
var bodyObs=new IntersectionObserver(function(e){
  e.forEach(function(x){
    if(x.isIntersecting){
      var r=x.intersectionRatio;
      if(r>=0.5&&!bodyObs._s50){bodyObs._s50=true;pushDl('contract_read_50pct');}
      if(r>=0.9&&!bodyObs._s90){bodyObs._s90=true;pushDl('contract_read_90pct');}
    }
  });
},{threshold:[0,.25,.5,.75,.9,1]});
if(bodyEl)bodyObs.observe(bodyEl);

var sigObs=new IntersectionObserver(function(e){
  e.forEach(function(x){if(x.isIntersecting&&!sigObs._sent){sigObs._sent=true;pushDl('contract_reached_signatures');}});
},{threshold:.5});
var sigEl=document.getElementById('sec-podpisy');
if(sigEl)sigObs.observe(sigEl);

function ping(ev,ex){
  var body=new URLSearchParams();
  body.append('action','upsellio_contract_track_event');
  body.append('contract_token',token);
  body.append('event',ev);
  if(ex&&ex.label)body.append('label',String(ex.label));
  fetch(ajaxUrl,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body.toString(),credentials:'same-origin',keepalive:true}).catch(function(){});
}
function pushDl(ev,ex){
  window.dataLayer=window.dataLayer||[];
  dataLayer.push(Object.assign({event:ev,contract_id:UPS.contract_id,person_id:UPS.person_id},ex||{}));
}
window.trackDl=function(ev){
  if(ev&&ev.target&&ev.target.getAttribute('href')==='#'){ev.preventDefault();return;}
  pushDl('contract_pdf_downloaded');
  ping('contract_pdf_downloaded',{});
};
window.trackAction=function(label){
  pushDl('contract_action',{label:label});
  ping('contract_action',{label:label});
};
ping('contract_opened');
pushDl('contract_opened');
})();
</script>
</body>
</html>
    <?php
}

function upsellio_contract_landing_step_map($status)
{
    $status = sanitize_key((string) $status);
    if ($status === "") {
        $status = "draft";
    }
    if ($status === "signed") {
        return ["done", "done", "done", "done"];
    }
    if ($status === "draft" || $status === "cancelled") {
        return ["current", "", "", ""];
    }
    return ["done", "done", "current", ""];
}
