# Upsellio — bramka wydania redesignu

## Stan i zakres
- Branch: `redesign/seo-safe-home`, draft PR #2. Nie publikować automatycznie.
- Testy statyczne: workflow `redesign-checks.yml`.
- Test renderowania: workflow `redesign-render.yml`. Dwie niezależne, tymczasowe instalacje WordPress/PHP 8.3/MariaDB, z syntetycznym wpisem i automatycznie przygotowanymi stronami motywu. Brak portów publicznych, osobna wewnętrzna sieć Docker, blokada HTTP API WordPress, poczty i crona; bez bazy produkcji i sekretów integracji. Obrazy Docker korzystają z tagów wersji, a nie digestów; zapisać faktyczne wersje przy reprodukcji.
- Workflow renderowania ma dwa niezależne warianty: core (bez wtyczki, SEO, CRM, Chromium) i rank-math (SEO z aktywnym publicznym Rank Math 1.0.282, pobranym z downloads.wordpress.org przed izolacją). W obu wariantach porównywane są te same syntetyczne strony baseline i candidate. Wersja Rank Math jest przypięta dla odtwarzalności; nie ustalono wersji produkcyjnej.
- Zaliczone dla rewizji bedfa96: render 8/8, poprawne canonical na 8 widokach, 4/4 zapisy CRM i 4/4 odrzucone przypadki, Chromium 21/21, 8/8 zgód analityki z mockiem HTTP oraz 35/35 kombinacji robots. Linki do przebiegów: https://github.com/Sebastiankelm/upsellio/actions/runs/45587019144 oraz https://github.com/Sebastiankelm/upsellio/actions/runs/45587019940.
- Fixture z aktywną wtyczką również nie zastępuje stagingu z produkcyjną wersją Rank Math/Pro, indywidualnymi metadanymi, wtyczkami, menu, treściami i konfiguracją hostingu. W razie różnicy wyników analizować logi zamiast osłabiać asercje lub zmieniać istniejące ustawienia produkcji.

## Wyniki dodatkowego audytu i blokady (2026-11-25)
- Run 45587669384: core PASS, 84 prób wydajności, 8/8 SEO, 21/21 Chromium, 4/4 POST CRM, 4/4 negatywne. Statyczne CI 45587669388 PASS. Rank Math FAIL: brak canonical na 8 trasach w candidate. Rank Math pomija canonical dla noindex; hipoteza do potwierdzenia w nowym fixture z blog_public=1 i produkcyjnym typem środowiska wyłącznie w izolowanej sieci, z wymuszonym i testowanym nagłówkiem X-Robots-Tag noindex. Żadne ustawienie produkcyjne nie zostało zmienione.
- Produkcja: odczyt listy aktywnych wtyczek potwierdził Rank Math 1.0.282, Jetpack 16.4, Upsellio DevOps Bridge 1.1.0 i Upsellio Rank Math REST Bridge 1.1.0. WordPress.com staging list zwrócił brak aktywnych funkcji hostingu niezbędnych do stagingu. Jetpack rewind_status: unavailable/no_site_found, nie potwierdzono kopii. Katalog reviewed remote abilities dla deploy pusty. Nie oznacza to, że hosting nie ma własnej kopii lub innego kanału wdrożenia.
- Raport redesign-performance (artifact 15009597459): mobilny home CLS 0→0.575515, LCP 176→196 ms; pozostałe mediany CLS <=0.004, LCP 172–224 ms candidate w testowanym środowisku. Brak błędów lokalnych zasobów, po 5 żądań lokalnych; rozmiar odpowiedzi przed kompresją wzrósł ok. 16.6–26.2 kB na stronę. Nie jest to pomiar Lighthouse, transferu sieciowego ani terenowych CWV.
- Zidentyfikowano ryzyko opóźnionego startu nawigacji na stronie głównej (requestIdleCallback po load), przez które mógł pojawiać się rozwinięty fallback. W candidate enqueue shared JS przed load, marker nawigacji w head, rezerwacja hamburgera od pierwszego renderu. Retest wymagany. Dodano raport źródeł layout shift oraz bramkę syntetycznej mediany CLS strony głównej <=0.1.
- Rozszerzono test Chromium o rzeczywisty AJAX kontaktu, pojedynczy POST i generate_lead, event ID, zapis leada, UTM i mirror CRM. Wynik nowej rewizji jeszcze niepotwierdzony.
- Brak potwierdzonej kopii i stagingu to niezależne blokady publikacji. Nie wykonywać zmian w produkcji tylko w celu sprawdzenia działania mostu.

## 1. Przygotowanie prywatnego stagingu
- Osobna baza i host chroniony HTTP Basic Auth lub VPN, nie publiczna kopia serwisu. Wymusić X-Robots-Tag: noindex, nofollow i wyłączyć publikowanie sitemap. Nie kopiować tej reguły na produkcję.
- Użyć kopii konfiguracji i zanonimizowanej bazy. Usunąć dane leadów, klientów, użytkowników, logi i klucze API. Wyłączyć cron, pocztę, webhooki, synchronizacje CRM/Ads/GSC/AI oraz śledzenie do produkcyjnych kont. Zapewnić osobne testowe identyfikatory i skrzynkę mailową.
- Przygotować baseline z dokładnego SHA main i candidate z dokładnego SHA PR. Zapisać wersje PHP, WP, wtyczek, konfigurację cache i datę. Nie mieszać wersji CRM z równoległych branchy.
- Porównać te same URL, treści i ustawienia. Nie edytować Rank Math ani treści tylko po to, by uzyskać zgodność z testem.

## 2. Bramka SEO i UX
- /, /marketing-google-ads/, /marketing-meta-ads/, /tworzenie-stron-internetowych/, /kontakt/, /blog/, wpisy, paginacja, zwykłe archiwa kategorii, strony miast, definicje, portfolio.
- Status HTTP, redirect chain, jeden poprawny canonical, title i description, robots, hreflang, JSON-LD, H1–H3, indeksowalne linki, sitemap, 404. Porównać po normalizacji hosta stagingu.
- Sprawdzić, że wyniki blog_search / filtrów mają noindex, a zwykły blog, wpisy i paginacja nie otrzymały przypadkowego noindex. Sprawdzić canonical paginacji i zachowanie Rank Math. Nie blokować wyników w robots.txt zamiast noindex.
- Sprawdzić widoki 360/390/768/1024/1440 px, nawigację klawiaturą, Escape, focus, FAQ, formularze, obraz logo, widoczność treści bez JS, brak przepełnienia i błędów konsoli.
- Uruchomić porównywalne Lighthouse na mobile i desktop (co najmniej 3 próby na URL, ten sam profil i cache). Porównać medianę LCP/CLS/TBT; INP oceniać także w RUM po wdrożeniu. Cele terenowe: LCP <= 2.5 s, CLS <= 0.1, INP <= 200 ms na 75. percentylu; nie utożsamiać TBT z INP.

## 3. Bramka konwersji i zgodności
- Cztery formularze: home-hero-form, home-contact-form, contact-page-form, single-post-contact-form.
- Scenariusze: poprawny zapis, brak zgody kontaktowej, błędny telefon, wygasły nonce, limit, błąd zapisu, utrata odpowiedzi, dwuklik. W bazie testowej potwierdzić pojedynczy lead, właściwy origin, UTM i zgody; bez wysyłania do realnych klientów.
- Testy zgód Cookiebot: odmowa, tylko statystyka, tylko marketing, oba, cofnięcie zgody. Sprawdzić zarówno przeglądarkę, jak i backend.
- GTM Preview, GA4 DebugView i Meta Test Events w osobnych testowych kontach. Meta Pixel/CAPI powinny mieć to samo event_id; GA4 ma mieć jeden uzgodniony tor generate_lead, nie liczyć równolegle kilku zdarzeń jako jednej konwersji. Sprawdzić także ścieżki closed-won i native/no-JS.
- Przed publikacją potwierdzić ceny z makiety, zakres usług, godziny, adres, autentyczność opinii i case study. Newsletter nie ma działającej dedykowanej integracji — nie przywracać formularza bez zapisu i zgody.

## 4. Publikacja (wyłącznie po zatwierdzeniu)
1. Zapisać SHA produkcji i aktualnego main, sprawdzić diff, konflikty i równoległe prace CRM. Nie scalać całego starego snapshotu.
2. Wykonać zweryfikowaną kopię plików i bazy produkcji zgodnie z procedurą hostingu; zapisać czas, miejsce i kontrolę odtworzenia. Eksportować również ustawienia SEO i GTM, bez ujawniania sekretów.
3. Uzgodnić okno niskiego ruchu, osoby decyzyjne i kryteria rollbacku. Zatwierdzić wersję po pełnym stagingu. Nie instalować workflow jako mechanizmu wdrażania.
4. Wdrożyć tylko uzgodniony zestaw zmian motywu; nie nadpisywać wp-config.php, uploads, bazy danych ani mu-plugins. Wyczyścić cache aplikacji/CDN i sprawdzić publiczne URL.
5. Natychmiast: kontrola statusów, canonical/robots (bez stagingowego noindex), formularzy i zdarzeń na testowym leadzie. Sprawdzić CRM i błędy PHP. Zarejestrować zmianę w GSC/GA4 operacyjnie, nie wymuszać masowej reindeksacji.
6. Monitorować GSC, crawl, konwersje i CWV względem baseline po publikacji; uwzględnić opóźnienie danych i sezonowość.

## 5. Rollback
- Natychmiast wstrzymać wydanie lub cofnąć przy 5xx, niedostępności formularzy, utracie leadów, błędnym noindex/canonical na kluczowych URL, utracie krytycznych linków lub poważnym regresie UX.
- Przywrócić wersję plików motywu sprzed wydania (zatwierdzony SHA / kopia), zachowując niezależne poprawki CRM, wyczyścić cache i ponownie sprawdzić statusy, SEO, formularze i analitykę.
- Nie odtwarzać całej starej bazy jako domyślnego rollbacku: grozi utratą nowych leadów, kontaktów i zmian CRM. Jeśli rollback bazy jest konieczny, najpierw zabezpieczyć nowe dane, uzgodnić migrację i okno serwisowe.
- Zapisać incydent, przyczynę i wyniki retestu przed kolejnym wdrożeniem.

## 6. Status bramek
- [x] Pełny test syntetyczny bez Rank Math zaliczony dla bedfa96 (retest aktualnego HEAD po zmianach).
- [ ] Test syntetyczny z aktywnym Rank Math zaliczony.
- [ ] Prywatny staging dostępny i porównanie konfiguracji produkcyjnej wykonane.
- [ ] Testy funkcjonalne, urządzenia, CRM i analityka zaliczone.
- [ ] Treści i ceny zatwierdzone.
- [ ] Zweryfikowana kopia i rollback.
- [ ] Akceptacja publikacji.
