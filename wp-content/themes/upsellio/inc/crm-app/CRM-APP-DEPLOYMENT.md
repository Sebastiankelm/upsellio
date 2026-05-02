# Instrukcja wdrożeniowa CRM App

Centrum pracy sprzedażowej — nie „baza danych klientów”. Dokument opisuje zasady produktowe, nawigację, pulpitu i widoki oraz priorytety implementacji.

---

## 1. Główna zasada systemu

CRM codziennie odpowiada na pytania:

1. Kto właśnie wpadł ze strony?
2. Z kim muszę się dziś skontaktować?
3. Która oferta wymaga follow-upu?
4. Gdzie tracę sprzedaż?
5. Który kanał marketingowy daje realne zapytania i klientów?

**Proces:** Formularz / strona / kampania → Lead → Kwalifikacja → Deal / szansa sprzedaży → Oferta → Follow-up → Wygrany / przegrany → Analityka źródła.

---

## 2. Finalna nawigacja CRM

```
CRM
├── Pulpit
├── Leady
├── Pipeline
├── Klienci
├── Inbox
├── Zadania
├── Analityka
└── Ustawienia
```

**Opcjonalnie** osobno **Oferty**. Przy kompaktowości: Oferty jako część Pipeline, nie osobny główny widok.

---

## 3. Widok: Pulpit — cel

Ekran **operacyjny**. Po wejściu użytkownik wie: co zrobić teraz, kto jest najbliżej zakupu, co jest zaległe, gdzie jest ryzyko utraty sprzedaży. To nie może być wyłącznie dashboard z wykresami — to **centrum dowodzenia sprzedażą**.

---

## 4. Siatka dashboardu

**Desktop:** 12 kolumn, max-width ~1440px, gap 16–20px.

Układ (skrót):

- Header: CRM Pulse + szybkie akcje
- Wiersz KPI (4 karty)
- Priorytety na dziś | Pipeline snapshot
- Nowe leady / gorące leady | Aktywność / Inbox
- Wykres trendu leadów i ofert
- Źródła leadów | Powody utraty / alerty

**Mobile:** jedna kolumna — Szybkie akcje → Priorytety → KPI → Nowe leady → Oferty do follow-upu → Pipeline → Inbox → Mini analityka.

---

## 5. Dashboard — szczegóły

### 5.1. Header

- Powitanie + liczba działań sprzedażowych na dziś.
- Szybkie akcje: Dodaj lead, Dodaj klienta, Utwórz ofertę, Dodaj zadanie, Sprawdź inbox.
- Po prawej: filtr zakresu dat (dziś / 7 dni / 30 dni / miesiąc / kwartał), filtr źródła (wszystkie / SEO / Google Ads / Meta Ads / direct / referral), filtr usługi.

### 5.2. KPI (maks. 4)

| KPI | Treść |
|-----|--------|
| **Nowe leady** | Liczba z okresu + delta vs poprzedni okres; kolory: wzrost jakościowy / dużo leadów słaba kwalifikacja / spadek |
| **Leady do kontaktu** | Np. „12 wymaga kontaktu, 4 zaległe” → link do Leadów z filtrem |
| **Oferty do follow-upu** | Np. „8 wymaga follow-upu, 3 po terminie” — powiązanie z Pipeline |
| **Wartość otwartego pipeline** | Suma + „najbliżej wygranej” |

### 5.3. Priorytety na dziś

Największy moduł. Sortowanie ważności (np.): nowy lead z wysokim score → mail od klienta → oferta bez odpowiedzi → zadanie po terminie → deal bez aktywności → klient wysoka wartość bez kontaktu → lead z płatnej kampanii bez kontaktu.

Każdy element: typ zdarzenia, nazwa, źródło, wartość potencjalna, czas od ostatniej aktywności, sugerowana akcja, CTA (Zadzwoń, Napisz maila, Wyślij follow-up, Otwórz ofertę, Oznacz jako wykonane).

### 5.4. Pipeline snapshot

Poziomy pasek etapów: liczba + wartość PLN. Alerty przy etapach (np. oferta wysłana: deale bez odpowiedzi > 3 dni). Klik → Pipeline z filtrem.

### 5.5. Nowe i gorące leady

Karta: firma, potrzeba, źródło, UTM, score, czas od zgłoszenia, akcje (Skontaktuj się, Otwórz, Odrzuć).

**Lead score (przykład):** +20 telefon, +15 usługa, +15 budżet, +10 kampania płatna, +10 strona oferty, +10 powrót na stronę, +10 lead magnet, +10 długi opis. **Kolory:** 0–39 zimny, 40–69 średni, 70–100 gorący.

### 5.6. Wykres trendu

Linia / area: leady, zakwalifikowane, oferty wysłane, wygrane deale. Domyślnie 30 dni; filtry 7 / 30 / 90 dni, miesiąc, kwartał. **Pod wykresem krótka diagnoza tekstowa** (ważniejsza niż sam wykres).

### 5.7. Źródła leadów

Preferencja: **poziomy bar**, nie kołowy. Pokazywać jakość (kwalifikowane, oferty), nie tylko ilość. Tabela typu: Źródło | Leady | Kwalifikowane | Oferty | Wygrane | Konwersja | Śr. wartość.

### 5.8. Powody utraty

Horizontal bar: brak budżetu, brak odpowiedzi, za drogo, inna firma, timing, lead niepasujący, projekt odłożony… + krótka interpretacja / rekomendacja.

### 5.9. Alerty

Nie osobny główny moduł — na dashboardzie i w widokach. Typy: lead bez kontaktu > 2h, oferta bez follow-upu > 3 dni, deal bez aktywności > 7 dni, mail bez odpowiedzi > 24h, lead z kampanii bez kontaktu, spadek jakości ze źródła, wysoki ruch mało formularzy, dużo leadów mało ofert, dużo ofert mało wygranych.

---

## 6. Widok: Leady

Kwalifikacja — nie baza kontaktów. Pytanie: **czy wart dalszej pracy?**

**Zakładki:** Wszystkie, Nowe, Do kontaktu, Zakwalifikowane, Odrzucone, Skonwertowane.

**Tabela:** Lead, Firma, Usługa, Źródło, Score, Status, Data zgłoszenia, Ostatnia aktywność, Opiekun, Akcja.

**Drawer:** dane, potrzeba, źródło i ścieżka, aktywność na stronie, notatki, historia, decyzja sprzedażowa. Akcje: Zadzwoń, Napisz maila, Dodaj notatkę, Utwórz klienta, Utwórz deal, Odrzuć.

**Statusy (max ~6):** Nowy, Do kontaktu, W kontakcie, Zakwalifikowany, Niezakwalifikowany, Skonwertowany.

---

## 7. Widok: Pipeline

Kanban / tabela / lista priorytetów (domyślnie Kanban).

**Etapy (pełne lub uproszczone):** np. Kwalifikacja → Potrzeby → Oferta w przygotowaniu → Oferta wysłana → Follow-up → Negocjacje → Wygrany / Przegrany — lub uproszczenie do: Nowy, Kwalifikacja, Oferta, Follow-up, Negocjacje, Wygrany, Przegrany.

**Karta:** klient, usługa, wartość, status oferty, ostatnia aktywność, następny krok, źródło, temperatura (Hot / Warm / Cold / Risk — kolory funkcjonalne).

**Deal:** drawer / pełny widok: podsumowanie, klient, oferta, historia, zadania, inbox, notatki, pliki/umowy, powód win/loss.

**Obowiązkowo:** pole **Następny krok** + **Data następnego kontaktu**.

---

## 8. Widok: Klienci

Baza relacji — **osobno od leadów**.

**Lista:** Klient, Typ, Usługi, Status relacji, Wartość, Ostatni kontakt, Następny kontakt, Aktywne deale.

**Status relacji:** Prospekt, Aktywny klient, Były klient, Partner, Nieaktywny.

**Karta 360** (bez osobnego menu „Karta 360”): header + **„Co dalej?”** na górze; zakładki: Podsumowanie, Kontakty, Deale, Oferty, Wiadomości, Zadania, Notatki, Pliki/umowy.

---

## 9. Widok: Inbox

Komunikacja sprzedażowa — nie zamiennik Gmaila. Pytanie: **na co muszę odpowiedzieć, żeby nie stracić sprzedaży?**

**Zakładki:** Do odpowiedzi, Powiązane z CRM, Niepowiązane, Oferty, Wysłane, Archiwum.

**Akcje:** Odpowiedz, Przypisz do klienta/deala, Utwórz lead, Utwórz zadanie, Obsłużone.

---

## 10. Widok: Zadania

Jeden moduł dla follow-upów, tasków, kalendarza.

**Zakładki:** Dzisiaj, Zaległe, Nadchodzące, Kalendarz, Automatyczne follow-upy, Wykonane.

**Typy:** Telefon, Mail, Follow-up, Przygotowanie oferty, Spotkanie, Analiza, Inne.

**Priorytety:** Wysoki, Normalny, Niski. **Statusy:** Do zrobienia, W trakcie, Wykonane, Przełożone, Anulowane.

**Automatyzacja:** np. oferta wysłana → follow-up za 2 dni; brak odpowiedzi → drugi za 5 dni; nowy lead → kontakt dziś; mail od klienta → odpowiedź dziś; deal bez aktywności 7 dni → sprawdź status.

---

## 11. Widok: Analityka

Odpowiedzi na: które źródła dają klientów, które formularze, gdzie trace leady, które oferty wygrywają, która usługa się sprzedaje.

**Zakładki:** Sprzedaż, Leady, Źródła, Oferty, Strona, Follow-upy.

Metryki i wykresy zgodnie z briefem (pipeline value by stage, win/lost w czasie, funnel, czas reakcji, jakość per źródło, oferty bez odpowiedzi, powody przegranej, landing page vs jakość).

---

## 12. Widok: Ustawienia

Wszystko techniczne tutaj: Użytkownicy, Pipeline, Statusy leadów, Źródła, Usługi, Szablony ofert/maili, Automatyzacje, Integracje, Powody przegranej, Tagi.

Z głównego menu usunąć: generator szablonów, katalog usług, silnik sprzedaży, alerty jako osobny moduł — przenieść do ustawień / „Więcej”.

---

## 13. UX formularzy

**Lead (wymagane):** imię/firma, e-mail lub telefon, usługa, źródło, opis potrzeby. Opcjonalnie: budżet, termin, WWW, notatka.

**Deal (wymagane):** klient, usługa, etap, wartość, następny krok, data następnego kontaktu.

---

## 14. UX statusów (skrót)

- **Lead:** Nowy, Do kontaktu, W kontakcie, Zakwalifikowany, Odrzucony, Skonwertowany.
- **Deal:** Kwalifikacja, Oferta, Follow-up, Negocjacje, Wygrany, Przegrany.
- **Oferta:** Szkic, Wysłana, Otwarta, W trakcie rozmów, Zaakceptowana, Odrzucona, Brak odpowiedzi.
- **Zadanie:** Do zrobienia, W trakcie, Wykonane, Przełożone, Anulowane.

---

## 15. UX kolorów

Turkusowy — pozytywna akcja / aktywne / hot. Zielony — wygrane. Żółty — uwaga. Pomarańczowy — ryzyko. Czerwony — zaległe / krytyczne. Szary — neutralne. Granat/slate — teksty i tła.

---

## 16. Automatyzacje (must-have)

Lead z formularza; źródło, UTM, landing; lead score; zadanie kontaktu po leadzie; follow-up po wysłaniu oferty; deal ryzykowny przy braku aktywności; alert przy braku odpowiedzi na ofertę; powiązanie mail ↔ klient/deal; powód przegranej; dane do analityki.

---

## 17. Minimalny zestaw statystyk

**Pulpit:** nowe leady, leady do kontaktu, oferty do follow-upu, wartość pipeline.

**Analityka:** konwersje lead → qualified → offer → won, win rate, śr. wartość deala, śr. czas reakcji, jakość per źródło, oferty bez odpowiedzi, powody lost, revenue by source.

**Pipeline:** liczba i wartość per etap, śr. czas na etapie, deale bez aktywności / po terminie następnego kroku.

---

## 18. Wykresy — zalecenia

Line/area: trendy w czasie. Bar: źródła, etapy, powody lost. Funnel: lejek konwersji. Tabele rankingowe: najlepsze źródła, landingi, usługi. **Unikać pie chartów** do decyzji operacyjnych.

---

## 19. Priorytet wdrożenia

| Etap | Zakres |
|------|--------|
| **1** | UX: Pulpit, Leady, Pipeline, Klienci, Zadania |
| **2** | Sprzedaż/komunikacja: oferty w pipeline, Inbox, follow-upy, auto-zadania |
| **3** | Analityka: źródła, jakość, konwersje, powody utraty, dashboard sprzedażowy |
| **4** | Lead scoring, alerty, rekomendacje, raporty, GA4/GTM |

---

## 20. Typowy dzień pracy

1. Pulpit → 2. Priorytety → 3. Nowe leady → 4. Pipeline → 5. Follow-up ofert → 6. Inbox → 7. Zadania → 8. Raz dziennie Analityka.

---

## Implementacja w kodzie

Ten motyw: `inc/crm-app/render.php` (widoki, layout), `core.php` (dane), `actions.php` (POST, redirecty). Szczegóły UI iteruj według tabeli „Priorytet wdrożenia”.
