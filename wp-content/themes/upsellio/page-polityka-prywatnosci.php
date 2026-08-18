<?php
/*
Template Name: Upsellio - Polityka Prywatnosci
Template Post Type: page
*/
if (!defined("ABSPATH")) {
    exit;
}

get_header();
?>
<style>
  .pp-wrap{width:min(1100px,100% - 32px);margin:40px auto 80px}
  .pp-wrap h1,.pp-wrap h2,.pp-wrap h3{font-family:"Bricolage Grotesque",sans-serif}
  .pp-wrap h1{font-size:42px;line-height:1.1;margin:0 0 8px}
  .pp-wrap h2{font-size:28px;line-height:1.2;margin:28px 0 10px}
  .pp-wrap p,.pp-wrap li,.pp-wrap td,.pp-wrap th{font-size:15px;line-height:1.75;color:#1f2937}
  .pp-wrap .muted{color:#6b7280}
  .pp-wrap .box{background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:20px}
  .pp-wrap table{width:100%;border-collapse:collapse;margin:10px 0}
  .pp-wrap th,.pp-wrap td{border:1px solid #e5e7eb;padding:10px;text-align:left;vertical-align:top}
</style>

<main class="pp-wrap">
  <h1>Polityka Prywatności</h1>
  <p class="muted"><strong>VePo Sebastian Kelm / Upsellio</strong><br/>Wierzbowa 21A/2 · 62-070 Dopiewiec · NIP: 7773388263<br/>kontakt@upsellio.pl · upsellio.pl<br/>Ostatnia aktualizacja: sierpień 2026 · Wersja: 2.1</p>

  <section class="box">
    <h2>1. Administrator danych osobowych</h2>
    <p>Administratorem Pani/Pana danych osobowych jest: VePo Sebastian Kelm, prowadzący działalność pod nazwą Upsellio, ul. Wierzbowa 21A/2, 62-070 Dopiewiec, NIP: 7773388263, e-mail: kontakt@upsellio.pl, strona: https://upsellio.pl.</p>
    <p>W sprawach związanych z ochroną danych osobowych może Pani/Pan kontaktować się bezpośrednio z Administratorem pod wskazanym adresem e-mail.</p>

    <h2>2. Zakres i źródła zbieranych danych</h2>
    <p>Administrator przetwarza dane osobowe pozyskane bezpośrednio od Pani/Pana, wyłącznie na podstawie dobrowolnie wypełnionego formularza kontaktowego lub wiadomości e-mail.</p>
    <ul>
      <li>Dane identyfikacyjne: imię i nazwisko lub nazwa firmy</li>
      <li>Dane kontaktowe: adres e-mail, numer telefonu (opcjonalnie)</li>
      <li>Dane związane z zapytaniem: treść wiadomości, wybrany zakres usług, budżet (opcjonalnie)</li>
      <li>Dane techniczne: adres IP, user-agent, data i godzina przesłania formularza, URL strony źródłowej</li>
      <li>Dane analityczne: dane o zachowaniu na stronie zbierane przez narzędzia wskazane w sekcji 8 (wyłącznie po udzieleniu zgody)</li>
    </ul>
    <p>Administrator nie zbiera świadomie danych osobowych osób poniżej 16. roku życia.</p>

    <h2>3. Cele i podstawy prawne przetwarzania</h2>
    <table>
      <thead><tr><th>Cel przetwarzania</th><th>Podstawa prawna RODO</th><th>Czas przechowywania</th></tr></thead>
      <tbody>
        <tr><td>Odpowiedź na zapytanie kontaktowe i przedstawienie oferty</td><td>Art. 6 ust. 1 lit. a lub lit. b</td><td>3 lata od ostatniego kontaktu</td></tr>
        <tr><td>Realizacja usług marketingowych (w ramach umowy)</td><td>Art. 6 ust. 1 lit. b</td><td>Czas umowy + 5 lat</td></tr>
        <tr><td>Marketing bezpośredni, follow-up, przypomnienia</td><td>Art. 6 ust. 1 lit. f</td><td>Do sprzeciwu, max. 3 lata</td></tr>
        <tr><td>Wystawianie faktur i archiwizacja księgowa</td><td>Art. 6 ust. 1 lit. c</td><td>5 lat od końca roku podatkowego</td></tr>
        <tr><td>Analityka ruchu i optymalizacja strony (GA4, Clarity)</td><td>Art. 6 ust. 1 lit. a</td><td>26 miesięcy (GA4) / sesja (Clarity)</td></tr>
        <tr><td>Reklama remarketingowa (Meta Ads, Google Ads)</td><td>Art. 6 ust. 1 lit. a</td><td>Do cofnięcia zgody</td></tr>
        <tr><td>Dochodzenie lub obrona roszczeń</td><td>Art. 6 ust. 1 lit. f</td><td>Do przedawnienia roszczeń (max. 6 lat)</td></tr>
      </tbody>
    </table>

    <h2>4. Automatyczne przetwarzanie i profilowanie (AI)</h2>
    <p>Administrator korzysta z systemu AI (Anthropic Claude) do automatycznej oceny (scoringu) zapytań kontaktowych. Ocena ma charakter pomocniczy i nie stanowi zautomatyzowanej decyzji wywołującej skutki prawne.</p>
    <p>Profilowanie polega na analizie anonimizowanych danych formularza (zakres usługi, branża, cel, budżet) w celu dopasowania priorytetu odpowiedzi i propozycji oferty.</p>
    <p>Masz prawo do uzyskania wyjaśnienia dotyczącego zastosowanej oceny AI oraz do wyrażenia sprzeciwu wobec profilowania — napisz na kontakt@upsellio.pl z tytułem: „Sprzeciw — profilowanie AI”.</p>

    <h2>5. Odbiorcy danych — podmioty przetwarzające</h2>
    <table>
      <thead><tr><th>Podmiot / usługa</th><th>Kraj</th><th>Przekazywane dane</th><th>Podstawa transferu</th></tr></thead>
      <tbody>
        <tr><td>Dostawca hostingu WordPress</td><td>EU</td><td>IP, dane techniczne</td><td>Nie dotyczy (EOG)</td></tr>
        <tr><td>Google LLC — GA4 / Google Ads / Measurement Protocol</td><td>USA</td><td>Dane sesji, click IDs, eventy</td><td>SCC + DPF</td></tr>
        <tr><td>Meta Platforms — Meta Pixel + CAPI</td><td>USA</td><td>Email/phone hash, IP, fbp/fbc</td><td>SCC + DPF</td></tr>
        <tr><td>Microsoft Corporation — Clarity</td><td>USA</td><td>Nagrania sesji, mapy cieplne</td><td>SCC + DPF</td></tr>
        <tr><td>Anthropic PBC — AI scoring (Claude)</td><td>USA</td><td>Anonimizowane dane formularza</td><td>SCC / DPA Anthropic</td></tr>
        <tr><td>Cybot A/S — Cookiebot</td><td>Dania/EU</td><td>Zgody cookies, timestamp, IP</td><td>Nie dotyczy (EOG)</td></tr>
      </tbody>
    </table>

    <h2>6. Prawa osoby, której dane dotyczą</h2>
    <p>Przysługuje Pani/Panu prawo dostępu, sprostowania, usunięcia, ograniczenia, przenoszenia danych, wniesienia sprzeciwu, cofnięcia zgody oraz złożenia skargi do UODO.</p>

    <h2>7. Pliki cookies i zarządzanie zgodą</h2>
    <p>Zarządzanie zgodą na cookies odbywa się za pomocą Cookiebot (Cybot A/S, Havnegade 39, 1058 Kopenhaga, Dania), identyfikator: 91229b76-132c-42e8-9021-9542287ad319. Na landingach (m.in. Marketing dla butiku) pokazujemy własny baner: „Akceptuj wszystkie”, „Odrzuć opcjonalne” i „Ustawienia”. Wybór jest przekazywany do Cookiebot, który blokuje skrypty statystyczne i marketingowe do momentu zgody. Zgodę można cofnąć przyciskiem „Cookies”.</p>
    <p>Pełny rejestr cookies: https://www.cookiebot.com/goto/privacy-policy/</p>
    <table>
      <thead><tr><th>Kategoria</th><th>Cel</th><th>Przykłady</th><th>Zgoda wymagana</th></tr></thead>
      <tbody>
        <tr><td>Niezbędne</td><td>Działanie strony, bezpieczeństwo, zapis wyboru zgody</td><td>CookieConsent, PHPSESSID, wordpress_test_cookie</td><td>NIE</td></tr>
        <tr><td>Statystyczne</td><td>Analityka ruchu, UX i lejek landingów</td><td>_ga, _gid, _clck, ups_btk_vid / ups_btk_sid (localStorage / sessionStorage)</td><td>TAK</td></tr>
        <tr><td>Marketingowe</td><td>Remarketing i konwersje reklamowe</td><td>_fbp, _gcl_au, _fbc, GTM</td><td>TAK</td></tr>
      </tbody>
    </table>

    <h2>8. Narzędzia zewnętrzne i ich polityki</h2>
    <ul>
      <li>Google Analytics 4: https://policies.google.com/privacy</li>
      <li>Google Ads / Conversion Tracking: https://policies.google.com/privacy</li>
      <li>Google Tag Manager: https://policies.google.com/privacy</li>
      <li>Meta Pixel + CAPI: https://www.facebook.com/privacy/policy/</li>
      <li>Microsoft Clarity: https://privacy.microsoft.com/pl-pl/privacystatement</li>
      <li>Anthropic Claude: https://www.anthropic.com/privacy</li>
      <li>Cookiebot: https://www.cookiebot.com/pl/privacy-policy/</li>
    </ul>

    <h2>9. Bezpieczeństwo danych</h2>
    <ul>
      <li>Szyfrowanie połączenia TLS/HTTPS</li>
      <li>Hashowanie danych przed przekazaniem do systemów reklamowych (SHA-256)</li>
      <li>Szyfrowanie tokenów OAuth (AES-256-CBC)</li>
      <li>Ograniczenie dostępu na zasadzie need-to-know</li>
      <li>Aktualizacje oprogramowania i monitorowanie podatności</li>
      <li>Automatyczne usuwanie danych po okresie retencji</li>
    </ul>

    <h2>10. Komunikacja e-mail — rezygnacja</h2>
    <p>Wiadomości e-mail zawierają link „Wypisz się z dalszych wiadomości” umożliwiający natychmiastowe i bezwarunkowe wypisanie.</p>

    <h2>11. Zmiany polityki prywatności</h2>
    <p>Administrator zastrzega prawo do zmiany niniejszej polityki w przypadku zmian prawa, narzędzi lub zakresu przetwarzania.</p>

    <h2>12. Dane kontaktowe w sprawach ochrony danych</h2>
    <p>VePo Sebastian Kelm (Upsellio), ul. Wierzbowa 21A/2, 62-070 Dopiewiec, kontakt@upsellio.pl</p>
    <p>Organ nadzorczy: Prezes UODO, ul. Stawki 2, 00-193 Warszawa, https://uodo.gov.pl</p>
  </section>
</main>
<?php
get_footer();

