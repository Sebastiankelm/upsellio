<?php
/* Template Name: Upsellio - Strona Glowna v2 (Core) */
if(!defined('ABSPATH')) exit;
// Source: supplied Upsellio simplified homepage artboard (November 2026).
function upsellio_reference_home_nav(){ ?><header id="upref-nav" style="position: relative; z-index: 20; width: 100%; background: #ffffff; border-bottom: 1px solid #eceef1; font-family: 'DM Sans', -apple-system, sans-serif; color: #0f1115; line-height: 1.5">
<div style="max-width: 1160px; margin: 0 auto; padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; gap: 24px">
<a aria-label="Upsellio — strona główna" href="/" style="display: flex; align-items: center; gap: 8px; text-decoration: none; color: #0f1115; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: 22px; letter-spacing: -0.02em">
<span style="width: 10px; height: 10px; border-radius: 50%; background: #0bb39c"></span>Upsellio</a>

<nav aria-label="Główna" class="upref-wide" style="display: flex; align-items: center; gap: 28px; font-size: 15px">
<div style="position: relative">
<button aria-controls="upref-services" aria-expanded="false" aria-haspopup="true" id="upref-services-btn" style="display: flex; align-items: center; gap: 6px; min-height: 44px; padding: 0; background: none; border: 0; cursor: pointer; font: inherit; font-size: 15px; font-weight: 500; color: #3a3d44" type="button">Usługi<svg aria-hidden="true" fill="none" height="14" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" viewBox="0 0 24 24" width="14"><path d="m6 9 6 6 6-6"></path></svg></button>

<div class="upref-dropdown" hidden="" id="upref-services" style="position: absolute; top: 52px; left: -16px; width: 320px; background: #ffffff; border: 1px solid #e3e6ea; border-radius: 14px; box-shadow: 0 16px 40px rgba(15, 17, 21, 0.10); padding: 8px; display: flex; flex-direction: column; gap: 2px">
<a href="/marketing-google-ads/" style="display: block; padding: 12px 14px; border-radius: 10px; text-decoration: none; color: #0f1115; background: transparent">
<span style="display: block; font-weight: 700; font-size: 15px">Google Ads</span>
<span style="display: block; font-size: 13px; color: #5f636b">Klienci, którzy już szukają Twojej oferty</span>
</a><a href="/marketing-meta-ads/" style="display: block; padding: 12px 14px; border-radius: 10px; text-decoration: none; color: #0f1115; background: transparent">
<span style="display: block; font-weight: 700; font-size: 15px">Meta Ads</span>
<span style="display: block; font-size: 13px; color: #5f636b">Reklamy na Facebooku i Instagramie</span>
</a><a href="/tworzenie-stron-internetowych/" style="display: block; padding: 12px 14px; border-radius: 10px; text-decoration: none; color: #0f1115; background: transparent">
<span style="display: block; font-weight: 700; font-size: 15px">Strony i sklepy</span>
<span style="display: block; font-size: 13px; color: #5f636b">Strony WWW i sklepy internetowe</span>
</a>
</div>

</div>
<a href="https://upsellio.pl/portfolio-marketingowe/" style="padding: 10px 0 8px; text-decoration: none; color: #3a3d44; font-weight: 500; border-bottom: 2px solid transparent">Realizacje</a><a href="https://upsellio.pl/o-mnie/" style="padding: 10px 0 8px; text-decoration: none; color: #3a3d44; font-weight: 500; border-bottom: 2px solid transparent">O mnie</a><a href="<?php echo esc_url(get_option('page_for_posts') ? get_permalink((int)get_option('page_for_posts')) : home_url('/blog/')); ?>" style="padding: 10px 0 8px; text-decoration: none; color: #3a3d44; font-weight: 500; border-bottom: 2px solid transparent">Blog</a><a href="/kontakt/" style="padding: 10px 0 8px; text-decoration: none; color: #3a3d44; font-weight: 500; border-bottom: 2px solid transparent">Kontakt</a>
</nav>


<div class="upref-wide" style="display: flex; align-items: center; gap: 20px">
<a href="tel:+48575522595" style="color: #0f1115; text-decoration: none; font-weight: 700; font-size: 15px">+48 575 522 595</a>
<a href="#diagnoza" style="display: inline-flex; align-items: center; min-height: 44px; padding: 0 20px; border-radius: 999px; background: #06745f; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 15px">Bezpłatna diagnoza</a>
</div>


<div class="upref-narrow" style="display: flex; align-items: center; gap: 8px">
<a aria-label="Zadzwoń: +48 575 522 595" href="tel:+48575522595" style="display: inline-flex; align-items: center; justify-content: center; width: 44px; height: 44px; border-radius: 12px; background: #dff5ef; color: #06745f"><svg aria-hidden="true" fill="none" height="20" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="20"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"></path></svg></a>
<button aria-controls="upref-mobile" aria-expanded="false" aria-label="Otwórz menu" id="upref-mobile-btn" style="display: inline-flex; align-items: center; justify-content: center; width: 44px; height: 44px; border: 1px solid #e3e6ea; border-radius: 12px; background: #ffffff; color: #0f1115; cursor: pointer" type="button">

<svg aria-hidden="true" class="upref-close-icon" fill="none" height="20" hidden="" stroke="currentColor" stroke-linecap="round" stroke-width="2.2" viewBox="0 0 24 24" width="20"><path d="M6 6l12 12M18 6 6 18"></path></svg>


<svg aria-hidden="true" class="upref-open-icon" fill="none" height="20" stroke="currentColor" stroke-linecap="round" stroke-width="2.2" viewBox="0 0 24 24" width="20"><path d="M4 7h16M4 12h16M4 17h16"></path></svg>

</button>
</div>

</div>

<nav aria-label="Menu mobilne" class="upref-mobile" hidden="" id="upref-mobile" style="border-top: 1px solid #eceef1; padding: 8px 24px 24px; display: flex; flex-direction: column">
<span style="padding: 16px 0 4px; font-size: 13px; font-weight: 600; color: #5f636b">Usługi</span>
<a href="/marketing-google-ads/" style="display: flex; align-items: center; min-height: 48px; border-bottom: 1px solid #eceef1; text-decoration: none; color: #0f1115; font-size: 18px; font-weight: 700">Google Ads</a><a href="/marketing-meta-ads/" style="display: flex; align-items: center; min-height: 48px; border-bottom: 1px solid #eceef1; text-decoration: none; color: #0f1115; font-size: 18px; font-weight: 700">Meta Ads</a><a href="/tworzenie-stron-internetowych/" style="display: flex; align-items: center; min-height: 48px; border-bottom: 1px solid #eceef1; text-decoration: none; color: #0f1115; font-size: 18px; font-weight: 700">Strony i sklepy</a>
<a href="https://upsellio.pl/portfolio-marketingowe/" style="display: flex; align-items: center; min-height: 48px; border-bottom: 1px solid #eceef1; text-decoration: none; color: #0f1115; font-size: 18px; font-weight: 500">Realizacje</a><a href="https://upsellio.pl/o-mnie/" style="display: flex; align-items: center; min-height: 48px; border-bottom: 1px solid #eceef1; text-decoration: none; color: #0f1115; font-size: 18px; font-weight: 500">O mnie</a><a href="<?php echo esc_url(get_option('page_for_posts') ? get_permalink((int)get_option('page_for_posts')) : home_url('/blog/')); ?>" style="display: flex; align-items: center; min-height: 48px; border-bottom: 1px solid #eceef1; text-decoration: none; color: #0f1115; font-size: 18px; font-weight: 500">Blog</a><a href="/kontakt/" style="display: flex; align-items: center; min-height: 48px; border-bottom: 1px solid #eceef1; text-decoration: none; color: #0f1115; font-size: 18px; font-weight: 500">Kontakt</a>
<a href="#diagnoza" style="margin-top: 24px; display: flex; align-items: center; justify-content: center; min-height: 54px; border-radius: 999px; background: #06745f; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 16px">Umów bezpłatną diagnozę</a>
<a href="tel:+48575522595" style="margin-top: 12px; display: flex; align-items: center; justify-content: center; min-height: 48px; color: #0f1115; text-decoration: none; font-weight: 700">Zadzwoń: +48 575 522 595</a>
</nav>

</header><?php }
add_filter('pre_get_document_title',static function($t){return is_front_page()?'Reklamy, strony i sklepy dla małych firm — Upsellio':$t;},99);
add_action('wp_head',static function(){ ?>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,700;12..96,800&amp;family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&amp;display=swap">
<style id="upsellio-reference-home-css">body:has(#up-ref-main){margin:0;background:#fff;color:#0f1115}#upref-nav,#up-ref-main,#upref-footer{font-family:'DM Sans',-apple-system,sans-serif;box-sizing:border-box;letter-spacing:normal}#upref-nav *,#up-ref-main *,#upref-footer *{box-sizing:border-box}#upref-nav a,#up-ref-main a,#upref-footer a{text-decoration-thickness:auto}#up-ref-main p,#up-ref-main h1,#up-ref-main h2,#up-ref-main h3,#upref-footer h2{max-width:none}#up-ref-main button,#upref-nav button,#up-ref-main input{font-family:inherit}#upref-nav[hidden],#upref-nav [hidden],#up-ref-main [hidden]{display:none!important}#upref-nav{position:sticky!important;top:0;z-index:80!important}body.admin-bar #upref-nav{top:32px}#upref-nav.is-scrolled{box-shadow:0 4px 18px rgba(15,17,21,.08)}#upref-nav .upref-narrow{display:none!important}#upref-nav .upref-dropdown a:hover{background:#effaf7!important}#up-ref-main .upref-anchor,#up-ref-main [id]{scroll-margin-top:90px}#up-ref-main .upref-anchor{position:absolute}#up-ref-main a{color:#06745f}#up-ref-main details summary{list-style:none;cursor:pointer}#up-ref-main details summary::-webkit-details-marker{display:none}#up-ref-main details[open] .upref-faq-plus{transform:rotate(45deg);background:#dff5ef!important}#up-ref-main .upref-card{transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease}#up-ref-main a.upref-card:hover,#up-ref-main .upref-card-h:hover{transform:translateY(-2px);box-shadow:0 12px 28px rgba(15,17,21,.08);border-color:#cdeee6!important}#up-ref-main a.upref-btn{transition:transform .18s ease,background-color .18s ease,border-color .18s ease}#up-ref-main a.upref-btn:hover{transform:translateY(-1px)}#up-ref-main a.upref-btn-p:hover,#up-ref-main button.upref-cta:hover{background:#055e4d!important;border-color:#055e4d!important;color:white!important}#up-ref-main input:focus,#up-ref-main textarea:focus{outline:none;border-color:#0bb39c!important;box-shadow:0 0 0 3px rgba(11,179,156,.18)}#upref-nav :focus-visible,#up-ref-main :focus-visible,#upref-footer :focus-visible{outline:3px solid #0bb39c;outline-offset:3px}#up-ref-main .upref-notice{color:#06745f;margin:0;font-size:14px}#up-ref-main .upref-error{color:#b42318}#up-ref-main form input:not([type=checkbox]):not([type=hidden]){width:100%;min-width:0;background:#fff;color:#0f1115}#up-ref-main form{position:relative}#upref-footer a:hover{text-decoration:underline}#upref-nav svg,#up-ref-main svg,#upref-footer svg{flex-shrink:0;vertical-align:middle}#up-ref-main img{max-width:100%}#up-ref-main .upref-section{overflow-wrap:break-word}#up-ref-main .upref-section > div{box-sizing:border-box}
@media(max-width:979px){#upref-nav .upref-wide{display:none!important}#upref-nav .upref-narrow{display:flex!important}#upref-nav>div{gap:12px!important}#upref-nav .upref-mobile:not([hidden]){display:flex!important}}
@media(min-width:980px){#upref-nav .upref-mobile{display:none!important}}
@media(max-width:720px){#up-ref-main .upref-section{padding-block:64px!important}#up-ref-main .upref-hero{padding-block:48px 64px!important}#up-ref-main .upref-section>div{gap:32px!important}#up-ref-main .upref-hero form{max-width:100%!important;padding:24px!important}#up-ref-main .upref-section [style*="grid-template-columns: repeat(auto-fit"]{grid-template-columns:1fr!important}#up-ref-main .upref-cta-band h2{font-size:32px!important}}
@media(max-width:399px){#upref-nav>div,#up-ref-main .upref-section>div,#upref-footer>div{padding-inline:20px!important}#up-ref-main form{padding:20px!important}}
@media(max-width:782px){body.admin-bar #upref-nav{top:46px}}
@media(prefers-reduced-motion:reduce){#up-ref-main *,#upref-nav *{animation:none!important;transition:none!important;scroll-behavior:auto!important;transform:none!important}}
</style>
<?php },99);
get_header();
?>
<main id="up-ref-main" style="width: 100%; background: #ffffff; color: #0f1115; font-family: 'DM Sans', -apple-system, sans-serif; font-size: 16px; line-height: 1.6">

<section class="upref-section upref-hero" id="start" style="padding: 72px 0 88px; background: #effaf7">
<div style="max-width: 1160px; margin: 0 auto; padding: 0 24px; display: flex; flex-wrap: wrap; gap: 64px; align-items: center">
<div style="flex: 1 1 520px; min-width: 0">
<div style="margin-bottom: 24px"><span style="display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px 6px 10px; border-radius: 999px; background: #ffffff; border: 1px solid #cdeee6; font-size: 14px; font-weight: 600; color: #06745f"><span style="width: 8px; height: 8px; border-radius: 50%; background: #0bb39c"></span>Reklamy, strony i sklepy dla małych firm</span></div>
<h1 style="margin: 0 0 24px; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: clamp(40px, 5vw, 62px); line-height: 1.02; letter-spacing: -0.035em">Więcej zamówień i klientów z tego samego <span style="color: #06745f">budżetu</span>.</h1>
<p style="margin: 0 0 32px; font-size: 19px; color: #3a3d44; max-width: 52ch">Prowadzę reklamy w Google, na Facebooku i Instagramie oraz robię strony i sklepy, które zamieniają odwiedzających w zamówienia i telefony. Jedna osoba od reklamy do koszyka — bez prowizji od budżetu.</p>
<div style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 36px">
<a class="upref-btn upref-btn-p" href="#diagnoza" style="display: inline-flex; align-items: center; gap: 8px; min-height: 54px; padding: 0 26px; border-radius: 999px; background: #06745f; color: #ffffff; border: 1.5px solid #06745f; font-weight: 700; font-size: 16px; text-decoration: none">Umów bezpłatną diagnozę <svg aria-hidden="true" fill="none" height="18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="18"><path d="M5 12h14M13 6l6 6-6 6"></path></svg></a>
<a class="upref-btn" href="#uslugi" style="display: inline-flex; align-items: center; gap: 8px; min-height: 54px; padding: 0 26px; border-radius: 999px; background: #ffffff; color: #0f1115; border: 1.5px solid #d8dde3; font-weight: 700; font-size: 16px; text-decoration: none">Zobacz, co robię</a>
</div>
<div style="display: flex; flex-wrap: wrap; align-items: center; gap: 16px 32px; padding-top: 24px; border-top: 1px solid #cdeee6">
<div style="display: flex; align-items: center; gap: 12px">
<span style="width: 48px; height: 48px; border-radius: 50%; background: #0f1115; color: #ffffff; display: grid; place-items: center; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800">SK</span>
<div style="line-height: 1.35"><strong style="display: block; font-size: 15px">Sebastian Kelm</strong><span style="font-size: 14px; color: #5f636b">prowadzę każdy projekt osobiście</span></div>
</div>
<div style="display: flex; flex-wrap: wrap; gap: 10px 24px; font-size: 14px; font-weight: 600">
<span style="display: inline-flex; align-items: center; gap: 6px"><svg aria-hidden="true" fill="none" height="18" stroke="#06745f" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.6" viewBox="0 0 24 24" width="18"><path d="M20 6 9 17l-5-5"></path></svg>Efekty po 2–4 tyg.</span>
<span style="display: inline-flex; align-items: center; gap: 6px"><svg aria-hidden="true" fill="none" height="18" stroke="#06745f" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.6" viewBox="0 0 24 24" width="18"><path d="M20 6 9 17l-5-5"></path></svg>0% prowizji</span>
</div>
</div>
</div>
<span class="upref-anchor" id="kontakt"></span><form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="upref-form" data-upsellio-lead-form="1" data-upsellio-server-form="1" data-upsellio-short-diagnosis="<?php echo function_exists('upsellio_crm_is_short_diagnosis_origin') ? '1' : '0'; ?>" id="diagnoza" method="post" style="flex: 1 1 380px; max-width: 460px; min-width: 0; background: #ffffff; border: 1px solid #e3e6ea; border-radius: 24px; padding: 32px; box-shadow: 0 24px 48px rgba(15, 17, 21, 0.08); display: flex; flex-direction: column; gap: 16px">
<?php wp_nonce_field('upsellio_unified_lead_form','upsellio_lead_form_nonce'); ?>
<input type="hidden" name="action" value="upsellio_submit_lead"><input type="hidden" name="redirect_url" value="<?php echo esc_url(home_url('/')); ?>"><input type="hidden" name="lead_form_origin" value="home-hero-form"><input type="hidden" name="lead_source" value="home-hero-form">
<input type="text" name="lead_website" value="" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px;opacity:0;pointer-events:none;">
<?php foreach(['source','medium','campaign','term','content','gclid','fbclid','msclkid'] as $key): ?><input type="hidden" name="<?php echo esc_attr(in_array($key,['gclid','fbclid','msclkid'],true)?$key:'utm_'.$key); ?>" data-ups-utm="<?php echo esc_attr($key); ?>" value=""><?php endforeach; ?>
<input type="hidden" name="landing_url" data-ups-context="landing" value=""><input type="hidden" name="referrer" data-ups-context="referrer" value="">
<?php $up_status=isset($_GET['ups_lead_status']) && is_string($_GET['ups_lead_status']) ? sanitize_key(wp_unslash($_GET['ups_lead_status'])) : ''; if($up_status==='success'): ?><p class="upref-notice" role="status">Dziękuję! Oddzwonię w ciągu 24h roboczych.</p><?php elseif($up_status==='error'): ?><p class="upref-notice upref-error" role="alert">Nie udało się wysłać formularza. Sprawdź dane lub zadzwoń.</p><?php endif; ?>
<div style="display: flex; gap: 14px; align-items: center">
<span style="flex: 0 0 44px; width: 44px; height: 44px; border-radius: 12px; background: #dff5ef; color: #06745f; display: grid; place-items: center"><svg aria-hidden="true" fill="none" height="22" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="22"><path d="M4 5h16v11H8l-4 4z"></path></svg></span>
<div>
<h2 style="margin: 0; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: 24px; line-height: 1.15; letter-spacing: -0.02em">Bezpłatna 30-min diagnoza</h2>
<p style="margin: 0; font-size: 14px; color: #5f636b">Zostaw numer — oddzwonię w 24h roboczych.</p>
</div>
</div>
<div style="display: flex; flex-direction: column; gap: 6px">
<label for="diagnoza-name" style="font-size: 14px; font-weight: 600">Imię</label>
<input autocomplete="given-name" id="diagnoza-name" maxlength="200" name="lead_name" required="" style="min-height: 48px; padding: 0 14px; border: 1.5px solid #d8dde3; border-radius: 12px; font: inherit; font-size: 15px" type="text"/>
</div>
<div style="display: flex; flex-direction: column; gap: 6px">
<label for="diagnoza-phone" style="font-size: 14px; font-weight: 600">Telefon</label>
<input autocomplete="tel" id="diagnoza-phone" maxlength="24" name="lead_phone" required="" style="min-height: 48px; padding: 0 14px; border: 1.5px solid #d8dde3; border-radius: 12px; font: inherit; font-size: 15px" type="tel"/>
</div>
<div style="display: flex; flex-direction: column; gap: 6px">
<?php if(!function_exists('upsellio_crm_is_short_diagnosis_origin')): ?>
<label for="diagnoza-email" style="font-size: 14px; font-weight: 600">E-mail</label>
<input autocomplete="email" id="diagnoza-email" maxlength="200" name="lead_email" required style="min-height:48px;padding:0 14px;border:1.5px solid #d8dde3;border-radius:12px;font:inherit;font-size:15px" type="email"/>
<?php endif; ?>
<label for="diagnoza-www" style="font-size: 14px; font-weight: 600">Strona lub sklep <span style="font-weight: 400; color: #5f636b">(opcjonalnie)</span></label>
<input id="diagnoza-www" maxlength="200" name="lead_company" placeholder="twojsklep.pl" style="min-height: 48px; padding: 0 14px; border: 1.5px solid #d8dde3; border-radius: 12px; font: inherit; font-size: 15px" type="text"/>
</div>
<label style="display: flex; gap: 10px; align-items: flex-start; font-size: 13px; color: #5f636b; line-height: 1.5"><input name="lead_consent" required="" style="width: 18px; height: 18px; margin: 2px 0 0; flex-shrink: 0; accent-color: #0bb39c" type="checkbox" value="1"/><span>Zgadzam się na kontakt w sprawie diagnozy. <a href="/polityka-prywatnosci/">Polityka prywatności</a>.</span></label>
<button class="upref-cta" style="min-height: 54px; border: 0; border-radius: 999px; background: #06745f; color: #ffffff; font: inherit; font-size: 16px; font-weight: 700; cursor: pointer" type="submit">Umów bezpłatną diagnozę →</button>
<div style="display: flex; justify-content: center; gap: 18px; flex-wrap: wrap; font-size: 13px; color: #5f636b">
<span style="display: inline-flex; align-items: center; gap: 6px"><svg aria-hidden="true" fill="none" height="16" stroke="#06745f" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="16"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 3"></path></svg>Odpowiedź w 24h</span>
<span style="display: inline-flex; align-items: center; gap: 6px"><svg aria-hidden="true" fill="none" height="16" stroke="#06745f" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="16"><path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6z"></path><path d="m9 12 2 2 4-4"></path></svg>Bez zobowiązań</span>
</div>
</form>
</div>
</section>
<section class="upref-section" id="upref-audience" style="padding: 56px 0; background: #ffffff">
<div style="max-width: 1160px; margin: 0 auto; padding: 0 24px">
<p style="margin: 0 0 24px; text-align: center; font-size: 15px; font-weight: 700; color: #3a3d44">Pracuję z małymi firmami, na przykład:</p>
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px">
<div class="upref-card" style="background: #ffffff; border: 1px solid #e3e6ea; border-radius: 20px; padding: 20px; height: 100%; box-sizing: border-box"><div style="display: flex; gap: 14px; align-items: flex-start"><span style="flex: 0 0 44px; width: 44px; height: 44px; border-radius: 12px; background: #dff5ef; color: #06745f; display: grid; place-items: center"><svg aria-hidden="true" fill="none" height="22" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="22"><circle cx="9" cy="20" r="1.5"></circle><circle cx="18" cy="20" r="1.5"></circle><path d="M3 4h2l2.5 11h11L21 8H6.5"></path></svg></span><div><h3 style="margin: 0px 0 6px; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 700; font-size: 17px; line-height: 1.25; letter-spacing: -0.01em; color: #0f1115">Sklepy internetowe</h3><p style="margin: 0; font-size: 14px; color: #3a3d44">Dom i ogród, odzież, kosmetyki, akcesoria.</p></div></div></div>
<div class="upref-card" style="background: #ffffff; border: 1px solid #e3e6ea; border-radius: 20px; padding: 20px; height: 100%; box-sizing: border-box"><div style="display: flex; gap: 14px; align-items: flex-start"><span style="flex: 0 0 44px; width: 44px; height: 44px; border-radius: 12px; background: #dff5ef; color: #06745f; display: grid; place-items: center"><svg aria-hidden="true" fill="none" height="22" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="22"><path d="M12 3c2 3 4 4.5 4 8a4 4 0 0 1-8 0c0-2 1-3 2-4 0 2 1 3 2 3 0-2-1-4 0-7z"></path><path d="M9 21h6"></path></svg></span><div><h3 style="margin: 0px 0 6px; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 700; font-size: 17px; line-height: 1.25; letter-spacing: -0.01em; color: #0f1115">Wyroby własne</h3><p style="margin: 0; font-size: 14px; color: #3a3d44">Świece, ceramika, rękodzieło, małe marki.</p></div></div></div>
<div class="upref-card" style="background: #ffffff; border: 1px solid #e3e6ea; border-radius: 20px; padding: 20px; height: 100%; box-sizing: border-box"><div style="display: flex; gap: 14px; align-items: flex-start"><span style="flex: 0 0 44px; width: 44px; height: 44px; border-radius: 12px; background: #dff5ef; color: #06745f; display: grid; place-items: center"><svg aria-hidden="true" fill="none" height="22" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="22"><path d="M7 3v8a2 2 0 0 0 2 2v8"></path><path d="M11 3v8a2 2 0 0 1-2 2"></path><path d="M17 21V3c-2 1-3 3-3 6v5h3"></path></svg></span><div><h3 style="margin: 0px 0 6px; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 700; font-size: 17px; line-height: 1.25; letter-spacing: -0.01em; color: #0f1115">Lokale</h3><p style="margin: 0; font-size: 14px; color: #3a3d44">Pizzerie, kawiarnie, restauracje z dowozem.</p></div></div></div>
<div class="upref-card" style="background: #ffffff; border: 1px solid #e3e6ea; border-radius: 20px; padding: 20px; height: 100%; box-sizing: border-box"><div style="display: flex; gap: 14px; align-items: flex-start"><span style="flex: 0 0 44px; width: 44px; height: 44px; border-radius: 12px; background: #dff5ef; color: #06745f; display: grid; place-items: center"><svg aria-hidden="true" fill="none" height="22" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="22"><path d="M12 21s7-6 7-11a7 7 0 1 0-14 0c0 5 7 11 7 11z"></path><circle cx="12" cy="10" r="2.5"></circle></svg></span><div><h3 style="margin: 0px 0 6px; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 700; font-size: 17px; line-height: 1.25; letter-spacing: -0.01em; color: #0f1115">Usługi lokalne</h3><p style="margin: 0; font-size: 14px; color: #3a3d44">Salony, gabinety, warsztaty, ekipy remontowe.</p></div></div></div>
</div>
</div>
</section>
<section class="upref-section" id="problem" style="padding: 96px 0; background: #f5f6f8">
<div style="max-width: 1160px; margin: 0 auto; padding: 0 24px">
<h2 style="margin: 0 0 12px; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: clamp(30px, 3.4vw, 42px); line-height: 1.08; letter-spacing: -0.03em; color: #0f1115; max-width: 24ch; ">Brzmi znajomo?</h2>
<p style="margin: 0 0 40px; font-size: 17px; color: #5f636b; max-width: 60ch; ">Trzy sytuacje, z którymi małe firmy przychodzą do mnie najczęściej.</p><div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px">
<div class="upref-card" style="background: #ffffff; border: 1px solid #e3e6ea; border-radius: 20px; padding: 28px; height: 100%; box-sizing: border-box; display: flex; flex-direction: column"><span style="flex: 0 0 44px; width: 44px; height: 44px; border-radius: 12px; background: #fff4e5; color: #b54708; display: grid; place-items: center"><svg aria-hidden="true" fill="none" height="22" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="22"><circle cx="12" cy="12" r="9"></circle><path d="M14.5 9.5A2.5 2.5 0 0 0 12 8c-1.5 0-2.5.8-2.5 2s1 1.7 2.5 2 2.5.8 2.5 2-1 2-2.5 2a2.5 2.5 0 0 1-2.5-1.5M12 6v2M12 16v2"></path></svg></span><h3 style="margin: 18px 0 6px; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 700; font-size: 21px; line-height: 1.25; letter-spacing: -0.01em; color: #0f1115">Reklamy kosztują, a sprzedaży brak</h3><p style="margin: 0; font-size: 15px; color: #3a3d44">Płacisz za kliknięcia, ale nie zamieniają się w zamówienia ani telefony.</p>
<div style="margin-top: 20px; padding-top: 16px; border-top: 1px dashed #e3e6ea; display: flex; gap: 10px; align-items: flex-start; font-size: 15px; font-weight: 600; color: #0f1115"><svg aria-hidden="true" fill="none" height="20" stroke="#06745f" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.6" viewBox="0 0 24 24" width="20"><path d="M20 6 9 17l-5-5"></path></svg><span>Reklamy ustawione pod sprzedaż, nie pod kliknięcia.</span></div></div>
<div class="upref-card" style="background: #ffffff; border: 1px solid #e3e6ea; border-radius: 20px; padding: 28px; height: 100%; box-sizing: border-box; display: flex; flex-direction: column"><span style="flex: 0 0 44px; width: 44px; height: 44px; border-radius: 12px; background: #fff4e5; color: #b54708; display: grid; place-items: center"><svg aria-hidden="true" fill="none" height="22" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="22"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"></path><circle cx="12" cy="12" r="3"></circle></svg></span><h3 style="margin: 18px 0 6px; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 700; font-size: 21px; line-height: 1.25; letter-spacing: -0.01em; color: #0f1115">Strona nie przekonuje</h3><p style="margin: 0; font-size: 15px; color: #3a3d44">Klient wchodzi, nie widzi, dlaczego ma kupić właśnie u Ciebie, i idzie do konkurencji.</p>
<div style="margin-top: 20px; padding-top: 16px; border-top: 1px dashed #e3e6ea; display: flex; gap: 10px; align-items: flex-start; font-size: 15px; font-weight: 600; color: #0f1115"><svg aria-hidden="true" fill="none" height="20" stroke="#06745f" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.6" viewBox="0 0 24 24" width="20"><path d="M20 6 9 17l-5-5"></path></svg><span>Jasna oferta, opinie i krótka droga do zamówienia.</span></div></div>
<div class="upref-card" style="background: #ffffff; border: 1px solid #e3e6ea; border-radius: 20px; padding: 28px; height: 100%; box-sizing: border-box; display: flex; flex-direction: column"><span style="flex: 0 0 44px; width: 44px; height: 44px; border-radius: 12px; background: #fff4e5; color: #b54708; display: grid; place-items: center"><svg aria-hidden="true" fill="none" height="22" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="22"><path d="M3 3v18h18"></path><path d="m7 14 4-4 4 4 5-5"></path></svg></span><h3 style="margin: 18px 0 6px; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 700; font-size: 21px; line-height: 1.25; letter-spacing: -0.01em; color: #0f1115">Nie wiesz, co działa</h3><p style="margin: 0; font-size: 15px; color: #3a3d44">Promujesz posty, płacisz za reklamy, ale nie wiesz, co naprawdę przynosi zamówienia.</p>
<div style="margin-top: 20px; padding-top: 16px; border-top: 1px dashed #e3e6ea; display: flex; gap: 10px; align-items: flex-start; font-size: 15px; font-weight: 600; color: #0f1115"><svg aria-hidden="true" fill="none" height="20" stroke="#06745f" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.6" viewBox="0 0 24 24" width="20"><path d="M20 6 9 17l-5-5"></path></svg><span>Pomiar każdej sprzedaży i telefonu — widzisz, co zarabia.</span></div></div>
</div>
</div>
</section>
<section class="upref-section" id="uslugi" style="padding: 96px 0; background: transparent"><span class="upref-anchor" id="system"></span>
<div style="max-width: 1160px; margin: 0 auto; padding: 0 24px">
<p style="margin: 0 0 12px; font-size: 14px; font-weight: 700; color: #06745f">Usługi</p><h2 style="margin: 0 0 12px; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: clamp(30px, 3.4vw, 42px); line-height: 1.08; letter-spacing: -0.03em; color: #0f1115; max-width: 24ch; ">Co mogę dla Ciebie zrobić</h2>
<p style="margin: 0 0 40px; font-size: 17px; color: #5f636b; max-width: 60ch; ">Każdą usługę możesz wziąć osobno. Najlepiej działają razem — reklama prowadzi na stronę, a strona zamienia ruch w zamówienia.</p><div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px">
<a class="upref-card" href="/marketing-google-ads/" style="display: flex; flex-direction: column; gap: 16px; background: #ffffff; border: 1px solid #e3e6ea; border-radius: 24px; padding: 16px 16px 24px; text-decoration: none; color: #0f1115; height: 100%; box-sizing: border-box">
<div style="background: #effaf7; border-radius: 16px; padding: 20px"><div style="background: #ffffff; border-radius: 14px; padding: 16px; display: flex; flex-direction: column; gap: 10px; min-height: 150px">
<div style="display: flex; align-items: center; gap: 8px; border: 1px solid #e3e6ea; border-radius: 999px; padding: 8px 12px; font-size: 13px; color: #0f1115"><svg aria-hidden="true" fill="none" height="14" stroke="#5f636b" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="14"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path></svg>świece sojowe zapachowe</div>
<div style="font-size: 11px; color: #3a3d44"><strong style="color: #0f1115">Sponsorowane</strong> · twojsklep.pl</div>
<div style="font-size: 15px; font-weight: 700; color: #1a47b8; line-height: 1.3">Ręcznie robione świece — wysyłka w 24h</div>
</div></div>
<div style="padding: 0 8px; display: flex; flex-direction: column; gap: 6px; flex: 1">
<div style="display: flex; align-items: center; gap: 10px"><span style="flex: 0 0 36px; width: 36px; height: 36px; border-radius: 12px; background: #dff5ef; color: #06745f; display: grid; place-items: center"><svg aria-hidden="true" fill="none" height="22" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="22"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path></svg></span><h3 style="margin: 0px 0 6px; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 700; font-size: 22px; line-height: 1.25; letter-spacing: -0.01em; color: #0f1115">Google Ads</h3></div>
<p style="margin: 0; font-size: 15px; color: #3a3d44">Docierasz do osób, które właśnie szukają tego, co sprzedajesz — także z produktami i cenami w wynikach.</p>
<div style="margin-top: auto; padding-top: 14px; display: flex; justify-content: space-between; align-items: center; gap: 12px"><span style="font-size: 14px; color: #5f636b"><strong style="color: #0f1115">od 1 800 zł</strong> / mies.</span><span style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700; color: #06745f">Więcej <svg aria-hidden="true" fill="none" height="16" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="16"><path d="M5 12h14M13 6l6 6-6 6"></path></svg></span></div>
</div>
</a>
<a class="upref-card" href="/marketing-meta-ads/" style="display: flex; flex-direction: column; gap: 16px; background: #ffffff; border: 1px solid #e3e6ea; border-radius: 24px; padding: 16px 16px 24px; text-decoration: none; color: #0f1115; height: 100%; box-sizing: border-box">
<div style="background: #effaf7; border-radius: 16px; padding: 20px"><div style="background: #ffffff; border-radius: 14px; overflow: hidden; min-height: 150px; display: flex; flex-direction: column">
<div style="display: flex; align-items: center; gap: 8px; padding: 10px 12px; font-size: 12px"><span style="width: 24px; height: 24px; border-radius: 50%; background: #0f1115; color: #fff; display: grid; place-items: center; font-size: 10px; font-weight: 700">TS</span><strong>Twój Sklep</strong><span style="color: #5f636b">· Sponsorowane</span></div>
<div style="flex: 1; background: #dff5ef; display: grid; place-items: center; color: #06745f"><svg aria-hidden="true" fill="none" height="34" stroke="#06745f" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" viewBox="0 0 24 24" width="34"><rect height="16" rx="2" width="18" x="3" y="4"></rect><circle cx="9" cy="10" r="2"></circle><path d="m21 16-5-5-9 9"></path></svg></div>
<div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; font-size: 12px; font-weight: 700"><span>Jesienna kolekcja</span><span style="padding: 4px 10px; border-radius: 6px; background: #f5f6f8">Kup teraz</span></div>
</div></div>
<div style="padding: 0 8px; display: flex; flex-direction: column; gap: 6px; flex: 1">
<div style="display: flex; align-items: center; gap: 10px"><span style="flex: 0 0 36px; width: 36px; height: 36px; border-radius: 12px; background: #dff5ef; color: #06745f; display: grid; place-items: center"><svg aria-hidden="true" fill="none" height="22" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="22"><path d="M3 11v2a1 1 0 0 0 1 1h2l5 4V6L6 10H4a1 1 0 0 0-1 1z"></path><path d="M15 9a4 4 0 0 1 0 6"></path><path d="M18 6a8 8 0 0 1 0 12"></path></svg></span><h3 style="margin: 0px 0 6px; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 700; font-size: 22px; line-height: 1.25; letter-spacing: -0.01em; color: #0f1115">Meta Ads</h3></div>
<p style="margin: 0; font-size: 15px; color: #3a3d44">Facebook i Instagram — pokazujesz swoje produkty i wracasz do osób, które oglądały ofertę.</p>
<div style="margin-top: auto; padding-top: 14px; display: flex; justify-content: space-between; align-items: center; gap: 12px"><span style="font-size: 14px; color: #5f636b"><strong style="color: #0f1115">od 1 800 zł</strong> / mies.</span><span style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700; color: #06745f">Więcej <svg aria-hidden="true" fill="none" height="16" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="16"><path d="M5 12h14M13 6l6 6-6 6"></path></svg></span></div>
</div>
</a>
<a class="upref-card" href="/tworzenie-stron-internetowych/" style="display: flex; flex-direction: column; gap: 16px; background: #ffffff; border: 1px solid #e3e6ea; border-radius: 24px; padding: 16px 16px 24px; text-decoration: none; color: #0f1115; height: 100%; box-sizing: border-box">
<div style="background: #effaf7; border-radius: 16px; padding: 20px"><div style="background: #ffffff; border-radius: 14px; overflow: hidden; min-height: 150px">
<div style="display: flex; gap: 5px; padding: 9px 12px; border-bottom: 1px solid #eceef1"><span style="width: 7px; height: 7px; border-radius: 50%; background: #e3e6ea"></span><span style="width: 7px; height: 7px; border-radius: 50%; background: #e3e6ea"></span><span style="width: 7px; height: 7px; border-radius: 50%; background: #e3e6ea"></span></div>
<div style="padding: 14px; display: flex; gap: 12px; align-items: center">
<div style="flex: 1"><div style="font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: 15px; line-height: 1.1; margin-bottom: 8px">Świece robione ręcznie</div><span style="display: inline-block; padding: 6px 10px; border-radius: 999px; background: #0f1115; color: #fff; font-size: 11px; font-weight: 700">Do sklepu</span></div>
<div style="width: 64px; height: 72px; border-radius: 10px; background: #dff5ef"></div>
</div>
</div></div>
<div style="padding: 0 8px; display: flex; flex-direction: column; gap: 6px; flex: 1">
<div style="display: flex; align-items: center; gap: 10px"><span style="flex: 0 0 36px; width: 36px; height: 36px; border-radius: 12px; background: #dff5ef; color: #06745f; display: grid; place-items: center"><svg aria-hidden="true" fill="none" height="22" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="22"><rect height="16" rx="2" width="18" x="3" y="4"></rect><path d="M3 9h18M9 9v11"></path></svg></span><h3 style="margin: 0px 0 6px; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 700; font-size: 22px; line-height: 1.25; letter-spacing: -0.01em; color: #0f1115">Strony i sklepy</h3></div>
<p style="margin: 0; font-size: 15px; color: #3a3d44">Strona lub sklep WooCommerce, który jasno mówi, co sprzedajesz, i prowadzi prosto do zamówienia.</p>
<div style="margin-top: auto; padding-top: 14px; display: flex; justify-content: space-between; align-items: center; gap: 12px"><span style="font-size: 14px; color: #5f636b"><strong style="color: #0f1115">od 4 000 zł</strong> jednorazowo</span><span style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700; color: #06745f">Więcej <svg aria-hidden="true" fill="none" height="16" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="16"><path d="M5 12h14M13 6l6 6-6 6"></path></svg></span></div>
</div>
</a>
</div>
</div>
</section>
<section class="upref-section" id="case-study" style="padding: 88px 0; background: #0f1115; color: #ffffff">
<div style="max-width: 1160px; margin: 0 auto; padding: 0 24px; display: flex; flex-wrap: wrap; gap: 56px; align-items: center">
<div style="flex: 1 1 440px; min-width: 0">
<p style="margin: 0 0 12px; font-size: 14px; font-weight: 700; color: #7fe3d2">Przykład ilustracyjny · nie wynik klienta</p>
<h2 style="margin: 0 0 40px; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: clamp(30px, 3.4vw, 42px); line-height: 1.08; letter-spacing: -0.03em; color: #ffffff; max-width: 18ch; ">Ten sam budżet, pięć razy więcej zapytań — przykład.</h2>
<div style="margin-top: -8px; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px">
<div style="border-top: 2px solid #0bb39c; padding-top: 14px"><div style="font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: clamp(34px, 3.6vw, 48px); line-height: 1; color: #ffffff">5×</div><div style="margin-top: 8px; font-size: 14px; color: #c4c8ce">więcej zapytań miesięcznie</div></div>
<div style="border-top: 2px solid #0bb39c; padding-top: 14px"><div style="font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: clamp(34px, 3.6vw, 48px); line-height: 1; color: #ffffff">−80%</div><div style="margin-top: 8px; font-size: 14px; color: #c4c8ce">koszt jednego zapytania</div></div>
<div style="border-top: 2px solid #0bb39c; padding-top: 14px"><div style="font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: clamp(34px, 3.6vw, 48px); line-height: 1; color: #ffffff">90 dni</div><div style="margin-top: 8px; font-size: 14px; color: #c4c8ce">przykładowy okres pomiaru</div></div>
</div>
</div>
<figure style="flex: 1 1 400px; min-width: 0; margin: 0; background: #1a1e25; border: 1px solid #2a2f38; border-radius: 24px; padding: 36px">
<svg aria-hidden="true" fill="#0bb39c" height="34" viewBox="0 0 24 24" width="34"><path d="M4 18v-5c0-4 2-7 6-8l1 2c-2 1-3 2.5-3 5h3v6zm9 0v-5c0-4 2-7 6-8l1 2c-2 1-3 2.5-3 5h3v6z"></path></svg>
<blockquote style="margin: 16px 0 24px; font-size: 18px; line-height: 1.65; color: #ffffff">Przy niezmienionym budżecie pięciokrotny wzrost liczby zapytań oznacza spadek kosztu jednego zapytania o 80%. To ilustracja zależności, nie udokumentowany wynik ani gwarancja.</blockquote>
<figcaption style="display: flex; align-items: center; gap: 12px; font-size: 14px; color: #c4c8ce"><span style="width: 40px; height: 40px; border-radius: 50%; background: #2a2f38; display: grid; place-items: center; color: #ffffff; font-weight: 700">W</span><span><strong style="display: block; color: #ffffff">Model obliczeniowy</strong>Nie jest opinią klienta · <a href="https://upsellio.pl/portfolio-marketingowe/" style="color: #7fe3d2; font-weight: 700">Zobacz portfolio →</a></span></figcaption>
</figure>
</div>
</section>
<section class="upref-section" id="kim-jestem" style="padding: 96px 0; background: transparent">
<div style="max-width: 1160px; margin: 0 auto; padding: 0 24px">
<div style="display: flex; flex-wrap: wrap; gap: 64px; align-items: center">
<div style="flex: 1 1 360px; min-width: 0; max-width: 460px">
<div style="aspect-ratio:4/5;border-radius:28px;background:#dff5ef;overflow:hidden;display:grid;place-items:center;color:#06745f"><?php $portrait=function_exists('upsellio_get_template_asset_url')?upsellio_get_template_asset_url('home_about_photo','large'):''; if($portrait): ?><img src="<?php echo esc_url($portrait); ?>" alt="Sebastian Kelm" loading="lazy" style="width:100%;height:100%;object-fit:cover"><?php else: ?><span style="font:800 56px 'Bricolage Grotesque',sans-serif">SK</span><?php endif; ?></div>
</div>
<div style="flex: 1 1 460px; min-width: 0">
<p style="margin: 0 0 12px; font-size: 14px; font-weight: 700; color: #06745f">O mnie</p>
<h2 style="margin: 0 0 40px; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: clamp(30px, 3.4vw, 42px); line-height: 1.08; letter-spacing: -0.03em; color: #0f1115; max-width: 16ch; ">Cześć, jestem Sebastian.</h2>
<p style="margin: -24px 0 16px; font-size: 18px; color: #3a3d44; max-width: 56ch">Od ponad 10 lat zajmuję się sprzedażą. Wiem, że liczy się klient, który kupuje — nie kliknięcie w raporcie.</p>
<p style="margin: 0 0 32px; font-size: 18px; color: #3a3d44; max-width: 56ch">Pracuję sam i biorę tylko kilku klientów naraz. Dzięki temu znam Twój biznes, a Ty zawsze wiesz, z kim rozmawiasz.</p>
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px">
<div class="upref-card" style="background: #f5f6f8;  border-radius: 20px; padding: 20px; "><div style="font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: 32px; line-height: 1; letter-spacing: -0.02em">10+ lat</div><p style="margin: 8px 0 0; font-size: 14px; color: #5f636b">w sprzedaży</p></div>
<div class="upref-card" style="background: #f5f6f8;  border-radius: 20px; padding: 20px; "><div style="font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: 32px; line-height: 1; letter-spacing: -0.02em">5–7</div><p style="margin: 8px 0 0; font-size: 14px; color: #5f636b">klientów naraz</p></div>
<div class="upref-card" style="background: #f5f6f8;  border-radius: 20px; padding: 20px; "><div style="font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: 32px; line-height: 1; letter-spacing: -0.02em">0%</div><p style="margin: 8px 0 0; font-size: 14px; color: #5f636b">prowizji od budżetu</p></div>
</div>
<a href="https://www.linkedin.com/in/kelm-sebastian/" style="display: inline-flex; align-items: center; gap: 8px; margin-top: 24px; font-weight: 700"><svg aria-hidden="true" fill="none" height="18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="18"><path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1"></path><path d="M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"></path></svg>Mój profil na LinkedIn</a>
</div>
</div>
</div>
</section>
<section class="upref-section" id="jak-dzialam" style="padding: 96px 0; background: #f5f6f8">
<div style="max-width: 1160px; margin: 0 auto; padding: 0 24px">
<p style="margin: 0 0 12px; font-size: 14px; font-weight: 700; color: #06745f">Jak zaczynamy</p><h2 style="margin: 0 0 40px; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: clamp(30px, 3.4vw, 42px); line-height: 1.08; letter-spacing: -0.03em; color: #0f1115; max-width: 24ch; ">Trzy proste kroki</h2><ol style="list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px"><li class="upref-card upref-card-h" style="background: #ffffff; border: 1px solid #e3e6ea; border-radius: 20px; padding: 28px; position: relative">
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px"><span style="flex: 0 0 44px; width: 44px; height: 44px; border-radius: 12px; background: #0bb39c; color: #0f1115; display: grid; place-items: center"><svg aria-hidden="true" fill="none" height="22" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="22"><path d="M4 5h16v11H8l-4 4z"></path></svg></span><span style="font-size: 13px; font-weight: 700; color: #06745f">1 · 30 minut, 0 zł</span></div>
<h3 style="margin: 0px 0 6px; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 700; font-size: 21px; line-height: 1.25; letter-spacing: -0.01em; color: #0f1115">Rozmowa</h3><p style="margin: 0; font-size: 15px; color: #3a3d44">Patrzę na Twoją stronę i reklamy. Mówię wprost, gdzie uciekają klienci — nawet jeśli nie zaczniemy współpracy.</p></li>
<li class="upref-card upref-card-h" style="background: #ffffff; border: 1px solid #e3e6ea; border-radius: 20px; padding: 28px; position: relative">
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px"><span style="flex: 0 0 44px; width: 44px; height: 44px; border-radius: 12px; background: #dff5ef; color: #06745f; display: grid; place-items: center"><svg aria-hidden="true" fill="none" height="22" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="22"><path d="M4 6h10M18 6h2M4 12h4M12 12h8M4 18h12"></path><circle cx="16" cy="6" r="2"></circle><circle cx="10" cy="12" r="2"></circle><circle cx="18" cy="18" r="2"></circle></svg></span><span style="font-size: 13px; font-weight: 700; color: #5f636b">2 · Tygodnie 1–4</span></div>
<h3 style="margin: 0px 0 6px; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 700; font-size: 21px; line-height: 1.25; letter-spacing: -0.01em; color: #0f1115">Plan i wdrożenie</h3><p style="margin: 0; font-size: 15px; color: #3a3d44">Ustalamy priorytety i budżet. Ustawiam reklamy, poprawiam stronę lub sklep i pomiar sprzedaży.</p></li>
<li class="upref-card upref-card-h" style="background: #ffffff; border: 1px solid #e3e6ea; border-radius: 20px; padding: 28px; position: relative">
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px"><span style="flex: 0 0 44px; width: 44px; height: 44px; border-radius: 12px; background: #dff5ef; color: #06745f; display: grid; place-items: center"><svg aria-hidden="true" fill="none" height="22" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="22"><path d="M3 3v18h18"></path><path d="m7 14 4-4 4 4 5-5"></path></svg></span><span style="font-size: 13px; font-weight: 700; color: #5f636b">3 · Co tydzień</span></div>
<h3 style="margin: 0px 0 6px; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 700; font-size: 21px; line-height: 1.25; letter-spacing: -0.01em; color: #0f1115">Poprawki na podstawie wyników</h3><p style="margin: 0; font-size: 15px; color: #3a3d44">Patrzymy na zamówienia i telefony, nie na kliknięcia. Więcej pieniędzy idzie tam, gdzie jest sprzedaż.</p></li></ol>
</div>
</section>
<section class="upref-section" id="faq" style="padding: 96px 0; background: transparent">
<div style="max-width: 1160px; margin: 0 auto; padding: 0 24px">
<div style="display: flex; flex-wrap: wrap; gap: 56px">
<div style="flex: 1 1 300px; min-width: 0">
<h2 style="margin: 0 0 40px; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: clamp(30px, 3.4vw, 42px); line-height: 1.08; letter-spacing: -0.03em; color: #0f1115; max-width: 14ch; ">Częste pytania</h2>
<div style="margin-top: -16px; background: #effaf7; border-radius: 20px; padding: 24px; display: flex; gap: 14px; align-items: flex-start">
<span style="flex: 0 0 44px; width: 44px; height: 44px; border-radius: 12px; background: #ffffff; color: #06745f; display: grid; place-items: center"><svg aria-hidden="true" fill="none" height="22" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="22"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"></path></svg></span>
<div><h3 style="margin: 0px 0 6px; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 700; font-size: 17px; line-height: 1.25; letter-spacing: -0.01em; color: #0f1115">Nie ma tu Twojego pytania?</h3><p style="margin: 0; font-size: 15px; color: #3a3d44">Zadzwoń — odbieram osobiście, pn–pt 9–17.</p><a href="tel:+48575522595" style="display: inline-block; margin-top: 8px; color: #0f1115; font-weight: 700; font-size: 17px; text-decoration: none">+48 575 522 595</a></div>
</div>
</div>
<div style="flex: 2 1 520px; min-width: 0; border-top: 1px solid #e3e6ea">
<details style="border-bottom: 1px solid #e3e6ea">
<summary style="display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 22px 0; font-weight: 700; font-size: 18px">Ile to kosztuje?<span aria-hidden="true" class="upref-faq-plus" style="flex-shrink: 0; width: 32px; height: 32px; border-radius: 50%; background: #f5f6f8; display: grid; place-items: center; font-size: 20px; font-weight: 400; color: #3a3d44; transition: transform .2s ease, background .2s ease">+</span></summary>
<p style="margin: 0 0 22px; color: #3a3d44; max-width: 62ch">Zależy od branży i celu. Na bezpłatnej rozmowie pokazuję, jaki budżet ma sens w Twojej sytuacji. Za prowadzenie płacisz stałą miesięczną kwotę — bez procentu od budżetu reklamowego.</p>
</details>
<details style="border-bottom: 1px solid #e3e6ea">
<summary style="display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 22px 0; font-weight: 700; font-size: 18px">Kiedy zobaczę efekty?<span aria-hidden="true" class="upref-faq-plus" style="flex-shrink: 0; width: 32px; height: 32px; border-radius: 50%; background: #f5f6f8; display: grid; place-items: center; font-size: 20px; font-weight: 400; color: #3a3d44; transition: transform .2s ease, background .2s ease">+</span></summary>
<p style="margin: 0 0 22px; color: #3a3d44; max-width: 62ch">Pierwsze zamówienia i telefony zwykle po 2–4 tygodniach. Stały, przewidywalny napływ klientów — po 2–3 miesiącach regularnych poprawek.</p>
</details>
<details style="border-bottom: 1px solid #e3e6ea">
<summary style="display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 22px 0; font-weight: 700; font-size: 18px">Mam mały biznes. Czy to ma sens?<span aria-hidden="true" class="upref-faq-plus" style="flex-shrink: 0; width: 32px; height: 32px; border-radius: 50%; background: #f5f6f8; display: grid; place-items: center; font-size: 20px; font-weight: 400; color: #3a3d44; transition: transform .2s ease, background .2s ease">+</span></summary>
<p style="margin: 0 0 22px; color: #3a3d44; max-width: 62ch">Tak — pracuję właśnie z małymi firmami. Na rozmowie policzymy, przy jakim budżecie reklama ma szansę się zwrócić.</p>
</details>
<details style="border-bottom: 1px solid #e3e6ea">
<summary style="display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 22px 0; font-weight: 700; font-size: 18px">Jesteś agencją?<span aria-hidden="true" class="upref-faq-plus" style="flex-shrink: 0; width: 32px; height: 32px; border-radius: 50%; background: #f5f6f8; display: grid; place-items: center; font-size: 20px; font-weight: 400; color: #3a3d44; transition: transform .2s ease, background .2s ease">+</span></summary>
<p style="margin: 0 0 22px; color: #3a3d44; max-width: 62ch">Nie. Pracuję sam, z kilkoma klientami naraz. Twoim projektem zajmuję się osobiście — bez juniorów i zmieniających się opiekunów.</p>
</details>
<details style="border-bottom: 1px solid #e3e6ea">
<summary style="display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 22px 0; font-weight: 700; font-size: 18px">Dla jakich firm to jest?<span aria-hidden="true" class="upref-faq-plus" style="flex-shrink: 0; width: 32px; height: 32px; border-radius: 50%; background: #f5f6f8; display: grid; place-items: center; font-size: 20px; font-weight: 400; color: #3a3d44; transition: transform .2s ease, background .2s ease">+</span></summary>
<p style="margin: 0 0 22px; color: #3a3d44; max-width: 62ch">Dla małych firm: sklepów internetowych (np. ze świecami, kosmetykami czy rzeczami do ogrodu), marek z wyrobami własnymi, lokali i usług lokalnych.</p>
</details>
</div>
</div>
</div>
</section>


<section class="upref-section upref-cta-band" style="width: 100%; background: #0f1115; color: #ffffff; padding: 88px 0; font-family: 'DM Sans', -apple-system, sans-serif; line-height: 1.6">
<div style="max-width: 1160px; margin: 0 auto; padding: 0 24px; display: flex; flex-wrap: wrap; gap: 32px; align-items: center; justify-content: space-between">
<div style="flex: 1 1 520px; min-width: 0">
<h2 style="margin: 0 0 12px; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: 44px; line-height: 1.05; letter-spacing: -0.03em; color: #ffffff">Sprawdźmy, gdzie uciekają Twoi klienci.</h2>
<p style="margin: 0; font-size: 18px; color: #c4c8ce">30 minut rozmowy. Bez prezentacji i bez zobowiązań.</p>
</div>
<div style="display: flex; flex-wrap: wrap; align-items: center; gap: 20px">
<a href="#diagnoza" style="display: inline-flex; align-items: center; min-height: 54px; padding: 0 28px; border-radius: 999px; background: #0bb39c; color: #0f1115; font-weight: 700; font-size: 16px; text-decoration: none">Umów bezpłatną diagnozę →</a>
<a href="tel:+48575522595" style="color: #ffffff; font-weight: 700; font-size: 17px">+48 575 522 595</a>

</div>
</div>
</section></main>
<footer id="upref-footer" style="width: 100%; background: #f5f6f8; color: #0f1115; font-family: 'DM Sans', -apple-system, sans-serif; font-size: 15px; line-height: 1.6">
<div style="max-width: 1160px; margin: 0 auto; padding: 64px 24px 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px">
<div style="min-width: 0; max-width: 400px">
<a href="/" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; color: #0f1115; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: 22px; letter-spacing: -0.02em"><span style="width: 10px; height: 10px; border-radius: 50%; background: #0bb39c"></span>Upsellio</a>
<p style="margin: 14px 0 20px; color: #3a3d44">Reklamy, strony i sklepy dla małych firm — sklepów internetowych, lokali i usług lokalnych.</p>
<div style="display: flex; flex-direction: column; gap: 6px">
<a href="tel:+48575522595" style="color: #0f1115; font-weight: 700; text-decoration: none; font-size: 17px">+48 575 522 595</a>
<a href="mailto:kontakt@upsellio.pl" style="color: #0f1115; text-decoration: none">kontakt@upsellio.pl</a>
<span style="color: #5f636b">Wierzbowa 21A/2, Dopiewiec · pn–pt 9–17</span>
</div>
</div>
<div>
<h2 style="margin: 0 0 14px; font-size: 14px; font-weight: 700; color: #0f1115">Usługi</h2>
<ul style="list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 10px">
<li><a href="/marketing-google-ads/" style="color: #3a3d44; text-decoration: none">Google Ads</a></li>
<li><a href="/marketing-meta-ads/" style="color: #3a3d44; text-decoration: none">Meta Ads</a></li>
<li><a href="/tworzenie-stron-internetowych/" style="color: #3a3d44; text-decoration: none">Sklepy internetowe</a></li>
<li><a href="/tworzenie-stron-internetowych/" style="color: #3a3d44; text-decoration: none">Strony firmowe</a></li>
<li><a href="/tworzenie-stron-internetowych/" style="color: #3a3d44; text-decoration: none">Landing page</a></li>
</ul>
</div>
<div>
<h2 style="margin: 0 0 14px; font-size: 14px; font-weight: 700; color: #0f1115">Firma</h2>
<ul style="list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 10px">
<li><a href="https://upsellio.pl/o-mnie/" style="color: #3a3d44; text-decoration: none">O mnie</a></li>
<li><a href="https://upsellio.pl/portfolio-marketingowe/" style="color: #3a3d44; text-decoration: none">Realizacje — marketing</a></li>
<li><a href="https://upsellio.pl/portfolio/" style="color: #3a3d44; text-decoration: none">Realizacje — strony</a></li>
<li><a href="<?php echo esc_url(get_option('page_for_posts') ? get_permalink((int)get_option('page_for_posts')) : home_url('/blog/')); ?>" style="color: #3a3d44; text-decoration: none">Blog</a></li>
<li><a href="/kontakt/" style="color: #3a3d44; text-decoration: none">Kontakt</a></li>
</ul>
</div>
<div>
<h2 style="margin: 0 0 14px; font-size: 14px; font-weight: 700; color: #0f1115">Materiały</h2>
<ul style="list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 10px">
<li><a href="https://upsellio.pl/definicja/" style="color: #3a3d44; text-decoration: none">Słownik marketingowy</a></li>
<li><a href="https://upsellio.pl/polityka-prywatnosci/" style="color: #3a3d44; text-decoration: none">Polityka prywatności</a></li>
<li><a href="https://upsellio.pl/regulamin/" style="color: #3a3d44; text-decoration: none">Regulamin</a></li>
</ul>
</div>
</div>
<div style="max-width: 1160px; margin: 48px auto 0; padding: 24px; border-top: 1px solid #e3e6ea; display: flex; flex-wrap: wrap; justify-content: space-between; gap: 12px; font-size: 14px; color: #5f636b">
<span>© 2026 Upsellio · Sebastian Kelm</span>
<span style="display: flex; flex-wrap: wrap; gap: 20px">
<a href="https://www.linkedin.com/in/kelm-sebastian/" style="color: #3a3d44">LinkedIn</a>
<a href="https://www.facebook.com/profile.php?id=61563653369010" style="color: #3a3d44">Facebook</a>
<a href="https://www.instagram.com/upsellio.pl/" style="color: #3a3d44">Instagram</a>
</span>
</div>
</footer>
<script>(()=>{const n=document.getElementById('upref-nav');if(!n)return;const close=(b,p)=>{p.hidden=true;b.setAttribute('aria-expanded','false');};const setup=(bid,pid)=>{const b=document.getElementById(bid),p=document.getElementById(pid);if(!b||!p)return;b.addEventListener('click',()=>{p.hidden=!p.hidden;b.setAttribute('aria-expanded',String(!p.hidden));if(bid==='upref-mobile-btn'){b.setAttribute('aria-label',p.hidden?'Otwórz menu':'Zamknij menu');b.querySelector('.upref-open-icon').hidden=!p.hidden;b.querySelector('.upref-close-icon').hidden=p.hidden;}});document.addEventListener('keydown',e=>{if(e.key==='Escape'&&!p.hidden){b.click();b.focus();}});document.addEventListener('click',e=>{if(!p.hidden&&!p.contains(e.target)&&!b.contains(e.target))b.click();});p.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>{if(!p.hidden)b.click();}));};setup('upref-services-btn','upref-services');setup('upref-mobile-btn','upref-mobile');const shadow=()=>n.classList.toggle('is-scrolled',scrollY>8);window.addEventListener('scroll',shadow,{passive:true});shadow();})();</script>
<?php wp_footer(); ?>
</body></html>
