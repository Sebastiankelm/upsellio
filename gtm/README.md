# Upsellio GTM Web - wdrozenie

Plik `upsellio-gtm-web-config.json` to specyfikacja kontenera (mapa tagow, triggerow, zmiennych i eventow), przygotowana dla:

- GTM: `GTM-KM9J5XC2`
- GA4: `G-R37SMGVBNC`
- Strumien: `Upsellio` (`14765248080`)

## Co juz jest wdrozone w kodzie strony

W `assets/js/upsellio.js` sa wypychane zdarzenia do `dataLayer`:

- `generate_lead`
- `lead_magnet_signup`
- `lead_magnet_download`
- `contact_click`

## Konfiguracja GTM (Web)

1. W GTM utworz:
   - Data Layer Variables zgodnie z sekcja `gtmBuildPlan.variables`.
   - Custom Event Triggers zgodnie z `gtmBuildPlan.triggers`.
2. Dodaj tag `Google tag (GA4)` dla `G-R37SMGVBNC` na `All Pages`.
3. Dodaj 4 tagi typu `GA4 Event`:
   - `generate_lead`
   - `lead_magnet_signup`
   - `lead_magnet_download`
   - `contact_click`
4. Dla kazdego tagu przypnij odpowiedni trigger `Custom Event`.
5. Opublikuj kontener.

## Konwersje

W GA4 oznacz jako konwersje:

- `generate_lead`
- `lead_magnet_signup`
- `lead_magnet_download`
- `contact_click` (opcjonalnie: tylko `contact_type = tel` lub `mailto`)

## CRM (wlasny)

Po stronie CRM zapisuj przy leadzie:

- `ga_session_id`
- `ga_client_id`
- `event_id`
- `gclid` / `gbraid` / `wbraid` (jesli dostepne)

Pozwoli to na pelne domkniecie raportowania i import konwersji offline do Google Ads.
