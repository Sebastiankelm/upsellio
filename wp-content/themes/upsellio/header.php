<?php
if (!defined("ABSPATH")) {
    exit;
}

$blog_page_id = function_exists("upsellio_get_blog_page_id") ? (int) upsellio_get_blog_page_id() : (int) get_option("page_for_posts");
$blog_index_url = $blog_page_id ? get_permalink($blog_page_id) : home_url("/");
if (!$blog_index_url) {
    $blog_index_url = home_url("/");
}
$is_homepage_context = function_exists("upsellio_is_homepage_request") ? (bool) upsellio_is_homepage_request() : is_front_page();
$is_blog_context = !$is_homepage_context && (is_home() || is_singular("post") || is_category() || is_tag() || is_search() || is_page_template("ups-blog-core.php"));
$is_definitions_context = is_post_type_archive("definicja") || is_singular("definicja");
$is_cities_context = is_post_type_archive("miasto") || is_singular("miasto");
$is_portfolio_context = function_exists("upsellio_is_portfolio_page_context")
    ? upsellio_is_portfolio_page_context()
    : (is_page("portfolio") || is_page_template("page-portfolio.php") || is_singular("portfolio"));
$is_marketing_portfolio_context = function_exists("upsellio_is_marketing_portfolio_page_context")
    ? upsellio_is_marketing_portfolio_page_context()
    : (is_page("portfolio-marketingowe") || is_page_template("page-portfolio-marketingowe.php") || is_singular("marketing_portfolio"));
$is_lead_magnets_context = function_exists("upsellio_is_lead_magnets_page_context")
    ? upsellio_is_lead_magnets_page_context()
    : (is_page("lead-magnety") || is_page_template("page-lead-magnety.php") || is_singular("lead_magnet"));
$is_contact_context = function_exists("upsellio_is_contact_page_context")
    ? upsellio_is_contact_page_context()
    : (is_page("kontakt") || is_page_template("page-kontakt.php"));
$primary_navigation_links = function_exists("upsellio_get_primary_navigation_links") ? upsellio_get_primary_navigation_links() : [];
$current_request_uri = isset($_SERVER["REQUEST_URI"]) ? (string) wp_unslash($_SERVER["REQUEST_URI"]) : "/";
$current_url = home_url($current_request_uri);
$portfolio_url = function_exists("upsellio_get_portfolio_page_url") ? (string) upsellio_get_portfolio_page_url() : home_url("/portfolio/");
$marketing_portfolio_url = function_exists("upsellio_get_marketing_portfolio_page_url") ? (string) upsellio_get_marketing_portfolio_page_url() : home_url("/portfolio-marketingowe/");
$lead_magnets_url = function_exists("upsellio_get_lead_magnets_page_url") ? (string) upsellio_get_lead_magnets_page_url() : home_url("/lead-magnety/");
$contact_url = function_exists("upsellio_get_contact_page_url") ? (string) upsellio_get_contact_page_url() : home_url("/kontakt/");
$contact_phone = function_exists("upsellio_get_contact_phone") ? (string) upsellio_get_contact_phone() : "+48 575 522 595";
$contact_phone_href = preg_replace("/\s+/", "", $contact_phone);
$contact_email = "kontakt@upsellio.pl";
$nav_cta_url = $is_homepage_context ? home_url("/#kontakt") : $contact_url;
$brand_logo_assets = function_exists("upsellio_get_brand_logo_assets") ? upsellio_get_brand_logo_assets() : [];
$brand_logo_url = (string) ($brand_logo_assets["png"] ?? "");
$brand_logo_webp_320_url = (string) ($brand_logo_assets["webp_320"] ?? "");
$brand_logo_webp_640_url = (string) ($brand_logo_assets["webp_640"] ?? "");
$brand_logo_attachment_id = (int) ($brand_logo_assets["custom_logo_id"] ?? 0);
$primary_navigation_top = [];
$primary_navigation_children = [];
$upsellio_load_public_tracking = !function_exists("upsellio_should_load_public_tracking_tags") || upsellio_should_load_public_tracking_tags();
foreach ((array) $primary_navigation_links as $nav_link) {
    $link_id = (int) ($nav_link["id"] ?? 0);
    $parent_id = (int) ($nav_link["parent"] ?? 0);
    if ($parent_id > 0) {
        if (!isset($primary_navigation_children[$parent_id])) {
            $primary_navigation_children[$parent_id] = [];
        }
        $primary_navigation_children[$parent_id][] = $nav_link;
        continue;
    }
    if ($link_id > 0 && !isset($primary_navigation_children[$link_id])) {
        $primary_navigation_children[$link_id] = [];
    }
    $primary_navigation_top[] = $nav_link;
}
if (!function_exists('upsellio_render_reference_cta')) {
function upsellio_render_reference_cta($href = '', $show_mail = false) {
    if (!$href) { $href = function_exists('upsellio_get_contact_page_url') ? upsellio_get_contact_page_url() : ''; }
    if (!$href) { $href = home_url('/kontakt/'); }
    $phone = function_exists('upsellio_get_contact_phone') ? upsellio_get_contact_phone() : '+48 575 522 595';
    ob_start();
    ?>
    <style>.ur-cta-band,.ur-cta-band *{box-sizing:border-box}.ur-cta-band{width:100%;background:#0f1115;color:#fff;padding:88px 0;font:400 16px/1.6 "DM Sans",sans-serif}.ur-cta-band .ur-cta-inner{max-width:1160px;margin:0 auto;padding:0 24px;display:flex;flex-wrap:wrap;gap:32px;align-items:center;justify-content:space-between}.ur-cta-band .ur-cta-copy{flex:1 1 520px;min-width:0}.ur-cta-band h2{font:800 44px/1.05 "Bricolage Grotesque",sans-serif;letter-spacing:-.03em;color:#fff;margin:0 0 12px;max-width:none}.ur-cta-band p{font-size:18px;color:#c4c8ce;margin:0}.ur-cta-band .ur-cta-actions{display:flex;flex-wrap:wrap;align-items:center;gap:20px}.ur-cta-band a{color:#fff;font-weight:700;font-size:17px}.ur-cta-band a.ur-cta-button{display:inline-flex;align-items:center;gap:8px;min-height:54px;padding:0 28px;border-radius:999px;background:#0bb39c;color:#0f1115;font-size:16px;text-decoration:none;transition:transform .18s cubic-bezier(.2,.7,.2,1),background .18s}.ur-cta-band a.ur-cta-button:hover{transform:translateY(-1px);background:#7fe3d2}.ur-cta-band a:focus-visible{outline:3px solid #0bb39c;outline-offset:4px}@media(max-width:720px){.ur-cta-band{padding:64px 0}.ur-cta-band .ur-cta-inner{padding:0 20px}.ur-cta-band h2{font-size:32px}.ur-cta-band .ur-cta-actions{width:100%;flex-direction:column;align-items:stretch;gap:16px}.ur-cta-band .ur-cta-actions a{text-align:center;justify-content:center}}@media(prefers-reduced-motion:reduce){.ur-cta-band a{transition:none!important;transform:none!important}}</style>
    <section class="ur-cta-band" aria-label="Bezpłatna diagnoza"><div class="ur-cta-inner"><div class="ur-cta-copy"><h2>Sprawdźmy, gdzie uciekają Twoi klienci.</h2><p>30 minut rozmowy. Bez prezentacji i bez zobowiązań.</p></div><div class="ur-cta-actions"><a class="ur-cta-button" href="<?php echo esc_url($href); ?>" data-cta="reference-final-diagnosis">Umów bezpłatną diagnozę →</a><a href="<?php echo esc_url('tel:' . preg_replace('/[^+0-9]/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a><?php if ($show_mail) : ?><a href="mailto:kontakt@upsellio.pl">kontakt@upsellio.pl</a><?php endif; ?></div></div></section>
    <?php
    return ob_get_clean();
}
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <?php if ($upsellio_load_public_tracking) : ?>
  <?php
  $upsellio_clarity_project_id = defined("UPSELLIO_CLARITY_PROJECT_ID") && (string) UPSELLIO_CLARITY_PROJECT_ID !== ""
      ? (string) UPSELLIO_CLARITY_PROJECT_ID
      : "wm94kd7gyw";
  if (function_exists("upsellio_print_google_consent_defaults")) {
      upsellio_print_google_consent_defaults();
  }
  ?>
  <script id="Cookiebot" src="https://consent.cookiebot.com/uc.js" data-cbid="91229b76-132c-42e8-9021-9542287ad319" data-blockingmode="auto" data-culture="pl" type="text/javascript"></script>
  <script type="text/plain" data-cookieconsent="marketing">
    (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({"gtm.start":
    new Date().getTime(),event:"gtm.js"});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!="dataLayer"?"&l="+l:"";j.async=true;j.src=
    "https://www.googletagmanager.com/gtm.js?id="+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,"script","dataLayer","GTM-KM9J5XC2");
  </script>
  <script type="text/plain" data-cookieconsent="statistics" src="https://www.googletagmanager.com/gtag/js?id=G-R37SMGVBNC"></script>
  <script type="text/plain" data-cookieconsent="statistics">
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag("js", new Date());
    gtag("config", "G-R37SMGVBNC");
  </script>
  <?php if (defined("UPSELLIO_GOOGLE_ADS_ID") && (string) UPSELLIO_GOOGLE_ADS_ID !== "") : ?>
  <script type="text/plain" data-cookieconsent="marketing" src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr((string) UPSELLIO_GOOGLE_ADS_ID); ?>"></script>
  <script type="text/plain" data-cookieconsent="marketing">
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag("js", new Date());
    gtag("config", <?php echo wp_json_encode((string) UPSELLIO_GOOGLE_ADS_ID); ?>);
  </script>
  <?php endif; ?>
  <script type="text/plain" data-cookieconsent="marketing">
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version="2.0";
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,"script",
    "https://connect.facebook.net/en_US/fbevents.js");
    fbq("init", "1281722377419475");
    fbq("track", "PageView");
  </script>
  <?php if ($upsellio_clarity_project_id !== "") : ?>
  <script type="text/plain" data-cookieconsent="statistics">
    (function(c,l,a,r,i,t,y){
      c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
      t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
      y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", <?php echo wp_json_encode($upsellio_clarity_project_id); ?>);
  </script>
  <?php endif; ?>
  <?php endif; ?>
  <meta charset="<?php bloginfo("charset"); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php if ($upsellio_load_public_tracking) : ?>
<noscript data-cookieconsent="marketing"><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-KM9J5XC2" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<noscript data-cookieconsent="marketing"><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=1281722377419475&ev=PageView&noscript=1" alt="" /></noscript>
<?php endif; ?>
<a class="skip-link" href="#main-content">Przejdź do treści</a>
<?php
$ref_services = [
 ['Google Ads','Klienci, którzy już szukają Twojej oferty','upsellio_get_google_ads_page_url','/marketing-google-ads/'],
 ['Meta Ads','Reklamy na Facebooku i Instagramie','upsellio_get_meta_ads_page_url','/marketing-meta-ads/'],
 ['Strony i sklepy','Strony WWW i sklepy internetowe','upsellio_get_websites_page_url','/tworzenie-stron-internetowych/'],
];
$ref_links = [
 ['Realizacje', $marketing_portfolio_url ?: home_url('/portfolio-marketingowe/'), $is_portfolio_context || $is_marketing_portfolio_context],
 ['O mnie', home_url('/o-mnie/'), is_page('o-mnie')],
 ['Blog', $blog_index_url, $is_blog_context],
 ['Kontakt', $contact_url ?: home_url('/kontakt/'), $is_contact_context],
];
$ref_current_path = untrailingslashit((string) wp_parse_url($current_url, PHP_URL_PATH));
foreach ($ref_services as &$ref_service) {
    $ref_service[4] = function_exists($ref_service[2]) ? call_user_func($ref_service[2]) : '';
    if (!$ref_service[4]) { $ref_service[4] = home_url($ref_service[3]); }
    $ref_service[5] = $ref_current_path === untrailingslashit((string) wp_parse_url($ref_service[4], PHP_URL_PATH));
}
unset($ref_service);
$ref_services_active = in_array(true, array_column($ref_services,5),true);
$ref_cta = $is_homepage_context ? home_url('/#diagnoza') : ($contact_url ?: home_url('/kontakt/'));
?>
<style id="upsellio-reference-nav-css">
#upref-nav,#upref-nav *{box-sizing:border-box}#upref-nav{position:sticky;top:0;z-index:1000;width:100%;background:#fff;border-bottom:1px solid #eceef1;font:400 15px/1.5 "DM Sans",sans-serif;color:#0f1115;transition:box-shadow .2s cubic-bezier(.2,.7,.2,1)}#upref-nav.is-scrolled{box-shadow:0 4px 16px rgba(15,17,21,.06);border-color:transparent}#upref-nav .ur-nav-inner{max-width:1160px;margin:auto;padding:14px 24px;display:flex;align-items:center;justify-content:space-between;gap:24px}#upref-nav a{text-decoration:none;color:inherit}#upref-nav .ur-logo{display:flex;align-items:center;gap:8px;font:800 22px/1.2 "Bricolage Grotesque",sans-serif;letter-spacing:-.02em;white-space:nowrap}#upref-nav .ur-logo:before{content:"";width:10px;height:10px;border-radius:50%;background:#0bb39c;flex:0 0 10px}#upref-nav .ur-desktop{display:flex;align-items:center;gap:28px}#upref-nav .ur-desktop>a{color:#3a3d44;font-weight:500;padding:10px 0 8px;border-bottom:2px solid transparent;white-space:nowrap}#upref-nav .ur-desktop>a[aria-current]{color:#0f1115;font-weight:700;border-color:#0bb39c}#upref-nav .ur-dropdown{position:relative}#upref-nav button{font:inherit;cursor:pointer}#upref-nav .ur-service-toggle{display:flex;align-items:center;gap:6px;min-height:44px;padding:0;border:0;background:none;color:#3a3d44;font-weight:500}#upref-nav .ur-service-toggle.is-active{color:#0f1115;font-weight:700}#upref-nav .ur-service-toggle svg{transition:transform .2s}#upref-nav .ur-service-toggle[aria-expanded=true] svg{transform:rotate(180deg)}#upref-nav .ur-services{position:absolute;top:52px;left:-16px;width:320px;background:#fff;border:1px solid #e3e6ea;border-radius:14px;box-shadow:0 16px 40px #0f11151a;padding:8px;display:flex;flex-direction:column;gap:2px;transform-origin:top left;animation:ur-menu-in .16s cubic-bezier(.2,.7,.2,1)}#upref-nav .ur-services a{padding:12px 14px;border-radius:10px;display:block}#upref-nav .ur-services a:hover,#upref-nav .ur-services a[aria-current]{background:#effaf7}#upref-nav .ur-services strong{display:block;font-weight:700;font-size:15px}#upref-nav .ur-services small{display:block;font-size:13px;color:#5f636b}#upref-nav .ur-actions{display:flex;align-items:center;gap:20px;font-weight:700;white-space:nowrap}#upref-nav .ur-primary{display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:0 20px;border-radius:999px;background:#06745f;color:white;font-weight:700;transition:background-color .18s,transform .18s}#upref-nav .ur-primary:hover{background:#055e4d;transform:translateY(-1px)}#upref-nav .ur-mobile-actions{display:none;align-items:center;gap:8px}#upref-nav .ur-mobile-actions a,#upref-nav .ur-mobile-actions button{width:44px;height:44px;border-radius:12px;display:grid;place-items:center;padding:0}#upref-nav .ur-mobile-actions a{background:#dff5ef;color:#06745f}#upref-nav .ur-mobile-actions button{background:white;border:1px solid #e3e6ea;color:#0f1115}#upref-nav .ur-mobile-panel{display:flex;flex-direction:column;border-top:1px solid #eceef1;background:white;padding:8px 24px 24px;max-height:calc(100dvh - 73px);overflow:auto;overscroll-behavior:contain;animation:ur-menu-in .24s cubic-bezier(.2,.7,.2,1)}#upref-nav .ur-mobile-panel>span{padding:16px 0 4px;font-size:13px;font-weight:600;color:#5f636b}#upref-nav .ur-mobile-panel>a:not(.ur-primary):not(.ur-mobile-phone){display:flex;align-items:center;min-height:48px;border-bottom:1px solid #eceef1;font-size:18px;font-weight:500}#upref-nav .ur-mobile-panel>a.ur-service-link{font-weight:700}#upref-nav .ur-mobile-panel>a[aria-current]{color:#06745f}#upref-nav .ur-mobile-panel .ur-primary{margin-top:24px;min-height:54px;font-size:16px}#upref-nav .ur-mobile-phone{display:flex;align-items:center;justify-content:center;margin-top:12px;min-height:48px;font-weight:700}#upref-nav [hidden]{display:none!important}#upref-nav :focus-visible{outline:3px solid #0bb39c;outline-offset:3px}body.ur-nav-lock{overflow:hidden}body.admin-bar #upref-nav{top:32px}@keyframes ur-menu-in{from{opacity:0;transform:translateY(-6px) scale(.98)}to{opacity:1;transform:none}}@media(max-width:979px){#upref-nav .ur-desktop,#upref-nav .ur-actions{display:none!important}#upref-nav .ur-mobile-actions{display:flex}}@media(min-width:980px){#upref-nav .ur-mobile-panel{display:none!important}}@media(max-width:399px){#upref-nav .ur-nav-inner,#upref-nav .ur-mobile-panel{padding-inline:20px}}@media(max-width:782px){body.admin-bar #upref-nav{top:46px}}@media(prefers-reduced-motion:reduce){#upref-nav *{animation:none!important;transition:none!important;transform:none!important}}
</style>
<header id="upref-nav">
<div class="ur-nav-inner">
<a class="ur-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="Upsellio, strona główna">Upsellio</a>
<nav class="ur-desktop" aria-label="Główna">
<div class="ur-dropdown"><button class="ur-service-toggle<?php echo $ref_services_active ? ' is-active' : ''; ?>" type="button" aria-expanded="false" aria-controls="ur-services">Usługi <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg></button><div class="ur-services" id="ur-services" hidden><?php foreach ($ref_services as $s) : ?><a href="<?php echo esc_url($s[4]); ?>"<?php echo $s[5] ? ' aria-current="page"' : ''; ?>><strong><?php echo esc_html($s[0]); ?></strong><small><?php echo esc_html($s[1]); ?></small></a><?php endforeach; ?></div></div>
<?php foreach ($ref_links as $l) : ?><a href="<?php echo esc_url($l[1]); ?>"<?php echo $l[2] ? ' aria-current="page"' : ''; ?>><?php echo esc_html($l[0]); ?></a><?php endforeach; ?>
</nav>
<div class="ur-actions"><a href="<?php echo esc_url('tel:' . $contact_phone_href); ?>"><?php echo esc_html($contact_phone); ?></a><a href="<?php echo esc_url($ref_cta); ?>" class="ur-primary" data-cta="nav-diagnosis">Bezpłatna diagnoza</a></div>
<div class="ur-mobile-actions"><a href="<?php echo esc_url('tel:' . $contact_phone_href); ?>" aria-label="<?php echo esc_attr('Zadzwoń: ' . $contact_phone); ?>"><svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/></svg></a><button id="ur-mobile-toggle" aria-expanded="false" aria-controls="ur-mobile-panel" aria-label="Otwórz menu" type="button"><svg class="ur-open" aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg><svg class="ur-close" hidden aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg></button></div></div>
<nav class="ur-mobile-panel" id="ur-mobile-panel" aria-label="Menu mobilne" hidden><span>Usługi</span><?php foreach ($ref_services as $s) : ?><a class="ur-service-link" href="<?php echo esc_url($s[4]); ?>"<?php echo $s[5] ? ' aria-current="page"' : ''; ?>><?php echo esc_html($s[0]); ?></a><?php endforeach; ?><?php foreach ($ref_links as $l) : ?><a href="<?php echo esc_url($l[1]); ?>"<?php echo $l[2] ? ' aria-current="page"' : ''; ?>><?php echo esc_html($l[0]); ?></a><?php endforeach; ?><a class="ur-primary" href="<?php echo esc_url($ref_cta); ?>" data-cta="nav-mobile-diagnosis">Umów bezpłatną diagnozę</a><a class="ur-mobile-phone" href="<?php echo esc_url('tel:' . $contact_phone_href); ?>">Zadzwoń: <?php echo esc_html($contact_phone); ?></a></nav>
</header>
<script>
(function(){var root=document.getElementById('upref-nav');if(!root)return;var service=root.querySelector('.ur-service-toggle'),menu=root.querySelector('#ur-services'),mobile=root.querySelector('#ur-mobile-toggle'),panel=root.querySelector('#ur-mobile-panel'),mq=window.matchMedia('(min-width:980px)');function svc(open,focus){menu.hidden=!open;service.setAttribute('aria-expanded',String(open));if(focus)service.focus()}function mob(open,focus){panel.hidden=!open;mobile.setAttribute('aria-expanded',String(open));mobile.setAttribute('aria-label',open?'Zamknij menu':'Otwórz menu');mobile.querySelector('.ur-open').toggleAttribute('hidden',open);mobile.querySelector('.ur-close').toggleAttribute('hidden',!open);document.body.classList.toggle('ur-nav-lock',open);if(focus)mobile.focus();else if(open){var first=panel.querySelector('a');if(first)first.focus()}}service.addEventListener('click',function(){svc(menu.hidden,false)});mobile.addEventListener('click',function(){mob(panel.hidden,false)});document.addEventListener('click',function(e){if(!root.contains(e.target)){if(!menu.hidden)svc(false,false);if(!panel.hidden)mob(false,false)}else if(!menu.hidden&&!menu.contains(e.target)&&!service.contains(e.target))svc(false,false)});document.addEventListener('keydown',function(e){if(e.key==='Escape'){if(!panel.hidden)mob(false,true);else if(!menu.hidden)svc(false,true)}if(e.key==='Tab'&&!panel.hidden){var all=[...root.querySelectorAll('.ur-mobile-actions a,.ur-mobile-actions button,.ur-mobile-panel a')];if(e.shiftKey&&document.activeElement===all[0]){e.preventDefault();all[all.length-1].focus()}else if(!e.shiftKey&&document.activeElement===all[all.length-1]){e.preventDefault();all[0].focus()}}});panel.querySelectorAll('a').forEach(function(a){a.addEventListener('click',function(){mob(false,false)})});var resize=function(){if(mq.matches)mob(false,false);else svc(false,false)};if(mq.addEventListener)mq.addEventListener('change',resize);else mq.addListener(resize);var shadow=function(){root.classList.toggle('is-scrolled',window.scrollY>8)};window.addEventListener('scroll',shadow,{passive:true});shadow()})();
</script>
<?php if (!$is_homepage_context && !$is_blog_context && !is_page_template(["page-marketing-google-ads.php","page-marketing-meta-ads.php","page-tworzenie-stron-internetowych.php"]) && function_exists("upsellio_render_breadcrumbs")) : ?>
<?php echo upsellio_render_breadcrumbs(); ?>
<?php endif; ?>
<div id="main-content" tabindex="-1"></div>

