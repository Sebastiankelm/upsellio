<?php
/*
Template Name: Upsellio - Polityka Prywatnosci
Template Post Type: page
*/
if (!defined("ABSPATH")) {
    exit;
}

if (function_exists("upsellio_register_template_seo_head")) {
    upsellio_register_template_seo_head("privacy_policy");
}

$contact_email = "kontakt@upsellio.pl";
$contact_email_href = function_exists("upsellio_get_mailto_href") ? upsellio_get_mailto_href($contact_email) : ("mailto:" . $contact_email);
$contact_email_display = function_exists("upsellio_obfuscate_email_address") ? upsellio_obfuscate_email_address($contact_email) : $contact_email;
$policy_url = get_permalink();
$admin_name = "Sebastian Kelm / Upsellio";
$admin_address = "wierzbowa 21A/2, Dopiewiec";

get_header();
?>
<style>
  .pp-art{font-family:"DM Sans",system-ui,sans-serif;color:#0a1410;background:#fafaf7;line-height:1.7}
  .pp-art *,.pp-art *::before,.pp-art *::after{box-sizing:border-box}
  .pp-wrap{width:min(1040px,100% - 56px);margin-inline:auto}
  .pp-hero{padding:92px 0 36px;background:radial-gradient(circle at 90% 0%,rgba(13,148,136,.1),transparent 40%);border-bottom:1px solid #e7e7e1}
  .pp-eyebrow{display:inline-flex;align-items:center;gap:10px;font-size:11px;font-weight:700;letter-spacing:1.6px;text-transform:uppercase;color:#0d9488;margin-bottom:14px}
  .pp-eyebrow::before{content:"";width:26px;height:2px;background:#0d9488;border-radius:99px}
  .pp-h1{font-family:"Syne",sans-serif;font-weight:700;font-size:clamp(38px,4.6vw,58px);line-height:1.03;letter-spacing:-1.6px;margin:0 0 16px}
  .pp-lead{font-size:17px;color:#3d3d38;max-width:70ch}
  .pp-main{padding:40px 0 96px}
  .pp-section{background:#fff;border:1px solid #e7e7e1;border-radius:18px;padding:26px 28px}
  .pp-section + .pp-section{margin-top:14px}
  .pp-h2{font-family:"Syne",sans-serif;font-size:clamp(24px,2.8vw,32px);line-height:1.1;letter-spacing:-.8px;margin:0 0 12px}
  .pp-p{margin:0 0 10px;color:#3d3d38;font-size:15px;line-height:1.8}
  .pp-p:last-child{margin-bottom:0}
  .pp-list{margin:8px 0 0;padding-left:20px;display:grid;gap:6px}
  .pp-list li{color:#3d3d38;font-size:15px;line-height:1.75}
  .pp-note{margin-top:28px;padding:18px;border:1px solid #99f6e4;background:#ecfeff;border-radius:14px;color:#0f766e;font-size:14px}
  @media(max-width:760px){.pp-wrap{width:min(1040px,100% - 26px)}.pp-section{padding:20px}}
</style>

<main class="pp-art">
  <section class="pp-hero">
    <div class="pp-wrap">
      <div class="pp-eyebrow">Dokument prawny</div>
      <h1 class="pp-h1">Polityka prywatności</h1>
      <p class="pp-lead">Niniejsza Polityka Prywatności określa zasady przetwarzania danych osobowych oraz wykorzystywania plików cookies w związku z korzystaniem ze strony internetowej.</p>
    </div>
  </section>

  <section class="pp-main">
    <div class="pp-wrap">
      <article class="pp-section">
        <h2 class="pp-h2">1. Informacje ogólne</h2>
        <p class="pp-p">Administratorem danych osobowych jest: <strong><?php echo esc_html($admin_name); ?></strong>.</p>
        <p class="pp-p">Adres: <?php echo esc_html($admin_address); ?>.</p>
        <p class="pp-p">W sprawach związanych z danymi osobowymi możesz skontaktować się pod adresem: <a href="<?php echo esc_url($contact_email_href); ?>"><?php echo esc_html($contact_email_display); ?></a>.</p>
      </article>

      <article class="pp-section">
        <h2 class="pp-h2">2. Zakres zbieranych danych</h2>
        <ul class="pp-list">
          <li>imię i nazwisko</li>
          <li>adres e-mail</li>
          <li>numer telefonu</li>
          <li>nazwa firmy</li>
          <li>dane przekazane w formularzu kontaktowym</li>
          <li>adres IP</li>
          <li>dane o zachowaniu na stronie (analytics)</li>
        </ul>
      </article>

      <article class="pp-section">
        <h2 class="pp-h2">3. Cele przetwarzania danych</h2>
        <ul class="pp-list">
          <li>kontaktu z użytkownikiem (formularz, e-mail, telefon)</li>
          <li>przedstawienia oferty usług</li>
          <li>realizacji usług marketingowych / konsultacji</li>
          <li>analizy ruchu na stronie (statystyki, optymalizacja)</li>
          <li>działań remarketingowych i reklamowych</li>
        </ul>
      </article>

      <article class="pp-section">
        <h2 class="pp-h2">4. Podstawy prawne przetwarzania</h2>
        <ul class="pp-list">
          <li>art. 6 ust. 1 lit. a RODO – zgoda (formularz, kontakt)</li>
          <li>art. 6 ust. 1 lit. b RODO – realizacja umowy lub działania przed jej zawarciem</li>
          <li>art. 6 ust. 1 lit. f RODO – uzasadniony interes (marketing, analityka)</li>
        </ul>
      </article>

      <article class="pp-section">
        <h2 class="pp-h2">5. Odbiorcy danych</h2>
        <p class="pp-p">Dane mogą być przekazywane podmiotom wspierającym działanie strony i usług, np.:</p>
        <ul class="pp-list">
          <li>dostawcom hostingu</li>
          <li>narzędziom analitycznym (np. Google Analytics)</li>
          <li>systemom reklamowym (np. Meta Ads, Google Ads)</li>
          <li>narzędziom mailingowym / CRM</li>
        </ul>
        <p class="pp-p">Dane nie są sprzedawane osobom trzecim.</p>
      </article>

      <article class="pp-section">
        <h2 class="pp-h2">6. Czas przechowywania danych</h2>
        <ul class="pp-list">
          <li>przez czas trwania kontaktu / współpracy</li>
          <li>do momentu cofnięcia zgody</li>
          <li>do momentu przedawnienia roszczeń</li>
          <li>do czasu zakończenia działań marketingowych</li>
        </ul>
      </article>

      <article class="pp-section">
        <h2 class="pp-h2">7. Prawa użytkownika</h2>
        <p class="pp-p">Masz prawo do:</p>
        <ul class="pp-list">
          <li>dostępu do swoich danych</li>
          <li>sprostowania danych</li>
          <li>usunięcia danych</li>
          <li>ograniczenia przetwarzania</li>
          <li>sprzeciwu wobec przetwarzania</li>
          <li>przenoszenia danych</li>
        </ul>
        <p class="pp-p">Możesz również złożyć skargę do Prezesa UODO.</p>
      </article>

      <article class="pp-section">
        <h2 class="pp-h2">8. Pliki cookies</h2>
        <p class="pp-p">Strona wykorzystuje pliki cookies w celu:</p>
        <ul class="pp-list">
          <li>prawidłowego działania strony</li>
          <li>analizy ruchu</li>
          <li>prowadzenia działań marketingowych</li>
        </ul>
        <p class="pp-p">Cookies mogą pochodzić od podmiotów trzecich (np. Google, Meta).</p>
      </article>

      <article class="pp-section">
        <h2 class="pp-h2">9. Zarządzanie cookies</h2>
        <p class="pp-p">Możesz zarządzać cookies poprzez ustawienia swojej przeglądarki.</p>
        <p class="pp-p">Ograniczenie cookies może wpłynąć na działanie strony.</p>
      </article>

      <article class="pp-section">
        <h2 class="pp-h2">10. Narzędzia zewnętrzne</h2>
        <p class="pp-p">Na stronie mogą być wykorzystywane:</p>
        <ul class="pp-list">
          <li>Google Analytics</li>
          <li>Google Ads</li>
          <li>Meta Pixel (Facebook)</li>
        </ul>
        <p class="pp-p">Narzędzia te mogą zbierać dane o użytkowniku w celach analitycznych i reklamowych.</p>
      </article>

      <article class="pp-section">
        <h2 class="pp-h2">11. Bezpieczeństwo danych</h2>
        <p class="pp-p">Administrator stosuje odpowiednie środki techniczne i organizacyjne w celu ochrony danych przed:</p>
        <ul class="pp-list">
          <li>utratą</li>
          <li>nieautoryzowanym dostępem</li>
          <li>nieuprawnionym ujawnieniem</li>
        </ul>
      </article>

      <article class="pp-section">
        <h2 class="pp-h2">12. Zmiany polityki</h2>
        <p class="pp-p">Polityka może być aktualizowana. Aktualna wersja zawsze znajduje się na stronie.</p>
      </article>

      <p class="pp-note">Aktualna wersja dokumentu: <a href="<?php echo esc_url($policy_url); ?>">Polityka prywatności</a>.</p>
    </div>
  </section>
</main>
<?php
get_footer();

