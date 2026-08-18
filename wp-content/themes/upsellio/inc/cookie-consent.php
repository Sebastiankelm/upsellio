<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_cookie_privacy_url()
{
    $page = get_page_by_path("polityka-prywatnosci");
    if ($page instanceof WP_Post) {
        $url = get_permalink($page);
        if (is_string($url) && $url !== "") {
            return $url;
        }
    }
    return home_url("/polityka-prywatnosci/");
}

function upsellio_cookie_user_has_choice()
{
    return isset($_COOKIE["CookieConsent"]) || isset($_COOKIE["CookieConsentBulkTicket"]);
}

function upsellio_cookie_banner_enabled()
{
    if (is_admin() || is_feed()) {
        return false;
    }
    if (!function_exists("is_page_template") || !is_page_template("page-marketing-butiku.php")) {
        return false;
    }
    return true;
}

function upsellio_print_google_consent_defaults()
{
    if (is_admin()) {
        return;
    }
    if (function_exists("upsellio_should_load_public_tracking_tags") && !upsellio_should_load_public_tracking_tags()) {
        return;
    }
    ?>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag("consent", "default", {
  ad_storage: "denied",
  ad_user_data: "denied",
  ad_personalization: "denied",
  analytics_storage: "denied",
  functionality_storage: "granted",
  personalization_storage: "denied",
  security_storage: "granted",
  wait_for_update: 500
});
gtag("set", "ads_data_redaction", true);
gtag("set", "url_passthrough", true);
</script>
    <?php
}

function upsellio_print_cookie_banner()
{
    if (!upsellio_cookie_banner_enabled()) {
        return;
    }

    $privacy_url = upsellio_cookie_privacy_url();
    $has_choice = upsellio_cookie_user_has_choice();
    ?>
<style>
body.page-template-page-marketing-butiku-php [id^="CybotCookiebot"],
body.page-template-page-marketing-butiku-php [class*="CybotCookiebot"],
html.btk-cmp-on [id^="CybotCookiebot"],
html.btk-cmp-on [class*="CybotCookiebot"] {
  display: none !important;
  visibility: hidden !important;
  pointer-events: none !important;
}
.btk-cmp {
  position: fixed; z-index: 2147483002;
  left: 12px; right: 12px; bottom: 12px;
  max-width: 560px; margin: 0 auto;
  background: #fff; color: #161616;
  border: 1px solid #f0dbe4; border-radius: 20px;
  box-shadow: 0 18px 50px rgba(22,22,22,.22);
  padding: 18px 18px 16px;
  font-family: "Montserrat", "DM Sans", system-ui, sans-serif;
  pointer-events: auto;
}
@media (min-width: 721px) {
  .btk-cmp { left: auto; right: 20px; bottom: 20px; margin: 0; }
}
.btk-cmp[hidden],
.btk-cmp-overlay[hidden],
.btk-cmp-panel[hidden] { display: none !important; }
.btk-cmp h2 { margin: 0 0 8px; font-size: 18px; letter-spacing: -.02em; }
.btk-cmp p, .btk-cmp-panel p { margin: 0 0 14px; font-size: 13.5px; line-height: 1.5; color: #5c5c5c; }
.btk-cmp a, .btk-cmp-panel a { color: #e83a7a; text-decoration: underline; }
.btk-cmp-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.btk-cmp-actions .btk-cmp-settings { grid-column: 1 / -1; }
.btk-cmp-btn {
  min-height: 46px; border-radius: 999px; border: 1.5px solid #f0dbe4;
  background: #fff; color: #161616; font-weight: 800; font-size: 12px;
  letter-spacing: .04em; text-transform: uppercase; cursor: pointer;
  font-family: inherit;
}
.btk-cmp-btn.is-primary { background: #c92d68; border-color: #c92d68; color: #fff; }
.btk-cmp-btn.is-ghost { background: #fde8f0; border-color: #f0dbe4; color: #c92d68; }
.btk-cmp-renew {
  position: fixed; z-index: 9997; left: 12px; bottom: calc(78px + env(safe-area-inset-bottom, 0px));
  border: 1px solid #f0dbe4; background: #fff; color: #161616;
  border-radius: 999px; min-height: 36px; padding: 0 12px;
  font-size: 11px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase;
  cursor: pointer; font-family: "Montserrat", "DM Sans", system-ui, sans-serif;
  box-shadow: 0 6px 16px rgba(22,22,22,.08);
}
@media (min-width: 1025px) {
  .btk-cmp-renew { bottom: 16px; }
}
.btk-cmp-overlay {
  position: fixed; z-index: 2147483001; inset: 0;
  background: rgba(22,22,22,.42);
  place-items: end center; padding: 16px;
}
.btk-cmp-overlay:not([hidden]) { display: grid; }
.btk-cmp-panel {
  width: min(560px, 100%); background: #fff; border-radius: 20px;
  padding: 20px 18px 16px; box-shadow: 0 18px 50px rgba(22,22,22,.24);
  font-family: "Montserrat", "DM Sans", system-ui, sans-serif; color: #161616;
}
.btk-cmp-row {
  display: grid; grid-template-columns: 1fr auto; gap: 12px; align-items: start;
  padding: 12px 0; border-top: 1px solid #f0dbe4;
}
.btk-cmp-row strong { display: block; font-size: 14px; margin-bottom: 4px; }
.btk-cmp-row span { display: block; font-size: 12.5px; color: #5c5c5c; line-height: 1.45; }
.btk-cmp-toggle { position: relative; width: 44px; height: 26px; flex-shrink: 0; }
.btk-cmp-toggle input { position: absolute; opacity: 0; width: 1px; height: 1px; }
.btk-cmp-toggle i {
  display: block; width: 44px; height: 26px; border-radius: 999px; background: #e5e5e5;
  position: relative; transition: background .2s;
}
.btk-cmp-toggle i::after {
  content: ""; position: absolute; top: 3px; left: 3px; width: 20px; height: 20px;
  border-radius: 50%; background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,.15);
  transition: transform .2s;
}
.btk-cmp-toggle input:checked + i { background: #e83a7a; }
.btk-cmp-toggle input:checked + i::after { transform: translateX(18px); }
.btk-cmp-toggle input:disabled + i { background: #c92d68; opacity: .85; }
.btk-cmp-panel .btk-cmp-actions { margin-top: 8px; }
</style>
<div class="btk-cmp" id="btk-cmp" role="dialog" aria-modal="true" aria-labelledby="btk-cmp-title" <?php echo $has_choice ? "hidden" : ""; ?>>
  <h2 id="btk-cmp-title">Pliki cookies</h2>
  <p>Używamy cookies i podobnych technologii. Niezbędne są zawsze włączone, bo bez nich strona nie zadziała. Statystyczne (GA4, Clarity, analityka tej strony) i marketingowe (Meta, Google Ads) włączamy <strong>tylko po Twojej zgodzie</strong>. Możesz zaakceptować wszystkie, odrzucić opcjonalne albo wybrać kategorie. Szczegóły w <a href="<?php echo esc_url($privacy_url); ?>">polityce prywatności</a>. Zgodę cofniesz w każdej chwili — przycisk „Cookies”.</p>
  <div class="btk-cmp-actions">
    <button type="button" class="btk-cmp-btn" data-btk-cmp="reject">Odrzuć opcjonalne</button>
    <button type="button" class="btk-cmp-btn is-primary" data-btk-cmp="accept">Akceptuj wszystkie</button>
    <button type="button" class="btk-cmp-btn is-ghost btk-cmp-settings" data-btk-cmp="settings">Ustawienia</button>
  </div>
</div>
<button type="button" class="btk-cmp-renew" data-btk-cmp="renew" aria-label="Zmień zgody na pliki cookies">Cookies</button>
<div class="btk-cmp-overlay" id="btk-cmp-overlay" hidden>
  <div class="btk-cmp-panel" role="dialog" aria-modal="true" aria-labelledby="btk-cmp-panel-title">
    <h2 id="btk-cmp-panel-title" style="margin:0 0 8px;font-size:18px;">Ustawienia cookies</h2>
    <p>Niezbędne cookies nie wymagają zgody. Pozostałe kategorie są wyłączone, dopóki ich nie włączysz. Podstawa: art. 6 ust. 1 lit. a RODO oraz zgoda na przechowywanie informacji w urządzeniu końcowym.</p>
    <div class="btk-cmp-row">
      <div>
        <strong>Niezbędne</strong>
        <span>Bezpieczeństwo, zapis zgody, działanie formularza. Zawsze włączone.</span>
      </div>
      <label class="btk-cmp-toggle"><input type="checkbox" checked disabled><i></i></label>
    </div>
    <div class="btk-cmp-row">
      <div>
        <strong>Statystyczne</strong>
        <span>Google Analytics 4, Microsoft Clarity i analityka tego landingu (m.in. localStorage ups_btk_vid). Pomagają liczyć wizyty i lejek. Bez zgody nie zapisujemy identyfikatora sesji.</span>
      </div>
      <label class="btk-cmp-toggle"><input id="btk-cmp-stats" type="checkbox"><i></i></label>
    </div>
    <div class="btk-cmp-row">
      <div>
        <strong>Marketingowe</strong>
        <span>Meta Pixel, Google Ads / GTM — remarketing i pomiar konwersji reklam. Bez zgody tagi reklamowe nie startują.</span>
      </div>
      <label class="btk-cmp-toggle"><input id="btk-cmp-mkt" type="checkbox"><i></i></label>
    </div>
    <div class="btk-cmp-actions">
      <button type="button" class="btk-cmp-btn" data-btk-cmp="reject">Odrzuć opcjonalne</button>
      <button type="button" class="btk-cmp-btn is-ghost" data-btk-cmp="save">Zapisz wybór</button>
      <button type="button" class="btk-cmp-btn is-primary btk-cmp-settings" data-btk-cmp="accept">Akceptuj wszystkie</button>
    </div>
  </div>
</div>
<script>
(function () {
  document.documentElement.classList.add("btk-cmp-on");
  var bar = document.getElementById("btk-cmp");
  var overlay = document.getElementById("btk-cmp-overlay");
  var statsEl = document.getElementById("btk-cmp-stats");
  var mktEl = document.getElementById("btk-cmp-mkt");
  window.upsellioCookie = window.upsellioCookie || { statistics: false, marketing: false };

  function parseCookiebot() {
    try {
      if (window.Cookiebot && Cookiebot.consent) {
        return {
          decided: !!(Cookiebot.consented || Cookiebot.declined),
          statistics: !!Cookiebot.consent.statistics,
          marketing: !!Cookiebot.consent.marketing
        };
      }
    } catch (e) {}
    try {
      var raw = document.cookie.split("; ").find(function (p) { return p.indexOf("CookieConsent=") === 0; });
      if (!raw) return null;
      var v = decodeURIComponent(raw.slice(14));
      return {
        decided: true,
        statistics: /statistics\s*:\s*true/i.test(v),
        marketing: /marketing\s*:\s*true/i.test(v)
      };
    } catch (e2) {
      return null;
    }
  }

  function applyGtag(stats, mkt) {
    try {
      if (typeof window.gtag !== "function") return;
      window.gtag("consent", "update", {
        analytics_storage: stats ? "granted" : "denied",
        ad_storage: mkt ? "granted" : "denied",
        ad_user_data: mkt ? "granted" : "denied",
        ad_personalization: mkt ? "granted" : "denied"
      });
    } catch (e) {}
  }

  function submitCookiebot(stats, mkt) {
    function go() {
      try {
        var api = window.CookieConsent || window.Cookiebot;
        if (api && typeof api.submitCustomConsent === "function") {
          api.submitCustomConsent(false, !!stats, !!mkt);
          return true;
        }
      } catch (e) {}
      return false;
    }
    if (!go()) {
      window.addEventListener("CookiebotOnLoad", go, { once: true });
      setTimeout(go, 600);
      setTimeout(go, 2000);
    }
  }

  function publish(stats, mkt) {
    window.upsellioCookie = { statistics: !!stats, marketing: !!mkt };
    applyGtag(stats, mkt);
    submitCookiebot(stats, mkt);
    try {
      document.dispatchEvent(new CustomEvent("upsellio-cookie-consent", { detail: window.upsellioCookie }));
    } catch (e) {}
  }

  function hideUi() {
    if (bar) bar.hidden = true;
    if (overlay) overlay.hidden = true;
  }

  function showBar() {
    if (bar) bar.hidden = false;
    if (overlay) overlay.hidden = true;
  }

  function showSettings() {
    var cur = parseCookiebot();
    if (statsEl) statsEl.checked = !!(cur && cur.statistics);
    if (mktEl) mktEl.checked = !!(cur && cur.marketing);
    if (overlay) overlay.hidden = false;
    if (bar) bar.hidden = true;
  }

  function decide(stats, mkt) {
    publish(stats, mkt);
    hideUi();
  }

  var existing = parseCookiebot();
  if (existing && existing.decided) {
    window.upsellioCookie = { statistics: existing.statistics, marketing: existing.marketing };
    applyGtag(existing.statistics, existing.marketing);
    hideUi();
  } else {
    showBar();
  }

  function onAction(act) {
    if (act === "accept") decide(true, true);
    else if (act === "reject") decide(false, false);
    else if (act === "settings" || act === "renew") showSettings();
    else if (act === "save") decide(!!(statsEl && statsEl.checked), !!(mktEl && mktEl.checked));
  }
  document.querySelectorAll("[data-btk-cmp]").forEach(function (btn) {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      onAction(btn.getAttribute("data-btk-cmp"));
    });
  });

  window.addEventListener("CookiebotOnLoad", function () {
    var cur = parseCookiebot();
    if (cur && cur.decided) {
      window.upsellioCookie = { statistics: cur.statistics, marketing: cur.marketing };
      applyGtag(cur.statistics, cur.marketing);
      hideUi();
      try {
        document.dispatchEvent(new CustomEvent("upsellio-cookie-consent", { detail: window.upsellioCookie }));
      } catch (e) {}
    }
  });
})();
</script>
    <?php
}
add_action("wp_footer", "upsellio_print_cookie_banner", 5);
