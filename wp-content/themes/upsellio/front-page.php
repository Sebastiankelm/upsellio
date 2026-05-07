<?php
/*
Template Name: Upsellio - Strona Glowna v2 (Core)
Template Post Type: page

Strona glowna Upsellio.pl — sprzedażowa, nowoczesna, SEO-przyjazna.
- 12 sekcji prowadzacych odwiedzającego od pierwszego kliknięcia do bezpłatnej diagnozy
- Hero z formularzem widocznym natychmiast po prawej (lepszy lejek niz scroll do kontaktu)
- SEO: schema.org Organization+LocalBusiness, Service x3, FAQPage, BreadcrumbList, WebSite
- Tracking: dwa formularze z osobnymi origin: home-hero-form + home-contact-form
- Prefiks CSS: .hr- (home/hero/headline) — nie koliduje z istniejacym motywem
- Plik samodzielny: cały CSS+HTML+JS inline, bez zaleznosci od template-parts/home/*
*/
if (!defined("ABSPATH")) {
    exit;
}

/* ============================================================
   USTAWIENIA — wczytuje z systemu motywu Upsellio
   ============================================================ */

$cfg = function_exists("upsellio_get_front_page_content_config") ? upsellio_get_front_page_content_config() : [];
$seo = is_array($cfg["seo"] ?? null) ? $cfg["seo"] : [];
$contact_phone = function_exists("upsellio_get_contact_phone") ? upsellio_get_contact_phone() : "+48 575 522 595";
$contact_phone_href = preg_replace("/\s+/", "", (string) $contact_phone);
$contact_email = trim((string) ($cfg["contact_email"] ?? "kontakt@upsellio.pl"));
$linkedin_url = "https://www.linkedin.com/in/kelm-sebastian/";
$site_url = home_url("/");

$google_ads_url = function_exists("upsellio_get_google_ads_page_url") ? trim((string) upsellio_get_google_ads_page_url()) : home_url("/marketing-google-ads/");
$meta_ads_url = function_exists("upsellio_get_meta_ads_page_url") ? trim((string) upsellio_get_meta_ads_page_url()) : home_url("/marketing-meta-ads/");
$websites_url = function_exists("upsellio_get_websites_page_url") ? trim((string) upsellio_get_websites_page_url()) : home_url("/tworzenie-stron-internetowych/");
$portfolio_url = function_exists("upsellio_get_marketing_portfolio_page_url") ? trim((string) upsellio_get_marketing_portfolio_page_url()) : home_url("/portfolio-marketingowe/");
$o_mnie_url = home_url("/o-mnie/");
$kontakt_url = home_url("/kontakt/");

$service_options = [
    "Google Ads",
    "Meta Ads",
    "Strona internetowa / landing",
    "Pakiet kompletny (kampanie + strona)",
    "Audyt obecnych działań",
    "Nie wiem — chcę porozmawiać",
];

/* ============================================================
   SEO META
   ============================================================ */
$seo_title = trim((string) ($seo["title"] ?? "Marketing B2B oparty o sprzedaż | Google Ads, Meta Ads, strony WWW — Upsellio"));
$seo_description = trim((string) ($seo["description"] ?? "Marketing B2B nastawiony na leady i sprzedaż, nie kliknięcia. Google Ads, Meta Ads i strony internetowe dla firm. 10 lat sprzedaży B2B. Bezpłatna 30-min diagnoza."));
$seo_og_title = trim((string) ($seo["og_title"] ?? "Upsellio — marketing, który generuje klientów, nie kliknięcia"));
$seo_og_description = trim((string) ($seo["og_description"] ?? "Google Ads, Meta Ads i strony WWW dla firm B2B. Reklama + strona + lejek jako jeden system, prowadzony przez handlowca z 10-letnim doświadczeniem."));
$seo_og_image = function_exists("upsellio_get_default_og_image_url") ? upsellio_get_default_og_image_url() : (function_exists("get_template_directory_uri") ? get_template_directory_uri() . "/assets/images/upsellio-og.jpg" : "");

add_filter("pre_get_document_title", static function ($title) use ($seo_title) {
    return is_front_page() && $seo_title !== "" ? $seo_title : $title;
});
add_action("wp_head", static function () use ($seo_description, $seo_og_title, $seo_og_description, $site_url, $seo_og_image) {
    if (!is_front_page()) {
        return;
    }
    echo '<meta name="description" content="' . esc_attr($seo_description) . '">' . "\n";
    echo '<link rel="canonical" href="' . esc_url($site_url) . '">' . "\n";
    echo '<meta property="og:type" content="website">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($site_url) . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($seo_og_title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($seo_og_description) . '">' . "\n";
    echo '<meta property="og:locale" content="pl_PL">' . "\n";
    if ($seo_og_image !== "") {
        echo '<meta property="og:image" content="' . esc_url($seo_og_image) . '">' . "\n";
        echo '<meta property="og:image:width" content="1200">' . "\n";
        echo '<meta property="og:image:height" content="630">' . "\n";
    }
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($seo_og_title) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($seo_og_description) . '">' . "\n";
    if ($seo_og_image !== "") {
        echo '<meta name="twitter:image" content="' . esc_url($seo_og_image) . '">' . "\n";
    }
}, 1);

/* ============================================================
   FAQ — dla schema.org FAQPage + sekcji FAQ na stronie
   ============================================================ */
$home_faqs = [
    [
        "q" => "Ile kosztuje kampania Google Ads lub Meta Ads dla firmy B2B?",
        "a" => "Koszt zależy od branży, konkurencji, celu kampanii i tego, na jakim etapie jest dziś Twoja firma. Dlatego nie podaję „uniwersalnych pakietów”, które później i tak nie mają sensu w praktyce.\n\nPodczas bezpłatnej rozmowy analizuję Twoją sytuację i pokazuję, jaki budżet ma realną szansę przełożyć się na wyniki — bez przepalania pieniędzy na źle dobrane działania.",
    ],
    [
        "q" => "Po jakim czasie widać efekty kampanii B2B?",
        "a" => "Pierwsze sygnały (więcej ruchu, pierwsze leady) zwykle po 2-4 tygodniach. Stabilny, przewidywalny napływ kwalifikowanych leadów wymaga 2-3 miesięcy systematycznej optymalizacji. Działam iteracyjnie: analiza → wdrożenie → pomiar → korekty co tydzień.",
    ],
    [
        "q" => "Czy sama reklama wystarczy, żeby pozyskiwać klientów B2B?",
        "a" => "Najczęściej sama reklama to za mało. Jeśli strona nie buduje zaufania, oferta jest niejasna albo klient nie wie, co ma zrobić dalej, nawet dobrze ustawiona kampania będzie tracić potencjalnych klientów.\n\nDlatego patrzę na cały proces: od reklamy, przez stronę i komunikację, aż po formularz kontaktowy i sposób pozyskiwania leadów.",
    ],
    [
        "q" => "Czy obsługujesz tylko reklamy, czy też tworzenie stron internetowych?",
        "a" => "Oba obszary: kampanie Google Ads i Meta Ads oraz projektowanie stron WWW i landing page. Najlepsze wyniki daje połączenie tych elementów w jednym procesie — reklama prowadzi na stronę, a strona konwertuje ruch w leady.",
    ],
    [
        "q" => "Dla jakich firm jest ta współpraca?",
        "a" => "Głównie firmy B2B (usługi, produkcja, IT, SaaS), e-commerce B2B i firmy usługowe z ambicją wzrostu. Kluczowe: realny produkt lub usługa, określona grupa klientów i gotowość do rozmowy o wynikach, nie tylko o kliknięciach.",
    ],
    [
        "q" => "Czy pracujesz jako agencja, czy samodzielnie?",
        "a" => "Samodzielnie. Bez juniorów, bez rotującego zespołu. Twoje kampanie prowadzę ja — Sebastian — od początku do końca. Wada: ograniczona przepustowość (5-7 klientów naraz). Zaleta: stały kontakt z jedną osobą, która zna Twój projekt.",
    ],
    [
        "q" => "Co konkretnie zmienia się na stronie po takiej współpracy?",
        "a" => "Najczęściej porządkuję przekaz, doprecyzowuję ofertę, wzmacniam CTA, dodaję elementy budujące zaufanie i upraszczam drogę do kontaktu. Zmiany pod konkretny cel konwersji, nie pod estetykę.",
    ],
];

get_header();
?>

<style>
/* ============================================================
   STRONA GLOWNA UPSELLIO v4
   Design: jasny minimal, delikatne turkusowe akcenty
   Fonty: Bricolage Grotesque (display, ostrzejsze wagi) + DM Sans (body)
   Paleta: bardzo jasne tla, czysty bialy, subtelny turkus jako jedyny brand
   ============================================================ */

:root{
  /* Tekst — wieksza hierarchia kontrastu */
  --hr-ink:#0f1115;
  --hr-ink2:#3a3d44;
  --hr-muted:#6e727b;
  --hr-faint:#9ea2aa;

  /* Tla — jasniej, czysciej */
  --hr-bg:#fbfbfd;
  --hr-surface:#ffffff;
  --hr-soft:#f4f5f7;
  --hr-softer:#eef0f3;

  /* Bordery — delikatniejsze */
  --hr-border:#e6e8ec;
  --hr-border2:#d8dde3;
  --hr-line:#eef0f3;

  /* Turkus — ostrzejszy, czysty, jako jedyny akcent */
  --hr-teal:#0bb39c;
  --hr-teal2:#08a892;
  --hr-tealh:#089a86;
  --hr-teald:#06745f;
  --hr-tealx:#3dd8c3;
  --hr-teals:#e6fbf6;
  --hr-tealss:#f3fdfa;
  --hr-tealb:#d0f7ed;

  /* Dark accents (case study) */
  --hr-dark:#0a0e14;
  --hr-dark2:#11161e;

  /* Status / feedback */
  --hr-ok:#10b981;
  --hr-warn:#f59e0b;

  /* Promienie */
  --hr-r:10px;
  --hr-rl:16px;
  --hr-rxl:22px;
  --hr-rxxl:28px;

  /* Fonty */
  --hr-fd:'Bricolage Grotesque','Syne',sans-serif;
  --hr-fb:'DM Sans',-apple-system,sans-serif;
}

.hr,.hr *,.hr *::before,.hr *::after{box-sizing:border-box}
.hr{font-family:var(--hr-fb);background:var(--hr-bg);color:var(--hr-ink);line-height:1.6;-webkit-font-smoothing:antialiased;font-feature-settings:"ss01","cv01"}
.hr a{color:inherit;text-decoration:none}
.hr-wrap{max-width:1180px;margin:0 auto;padding:0 24px}

/* === BUTTONS — ostrzejsze, mniej cienia, mocniejsza krawedz === */
a.hr-btn,button.hr-btn,.hr a.hr-btn,.hr button.hr-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border-radius:99px;font-family:var(--hr-fb);font-weight:700;font-size:14.5px;border:1.5px solid transparent;cursor:pointer;text-decoration:none;line-height:1;padding:14px 24px;transition:all .18s cubic-bezier(.4,0,.2,1);letter-spacing:-.01em}
a.hr-btn-p,button.hr-btn-p,.hr a.hr-btn-p,.hr button.hr-btn-p{background:var(--hr-ink);color:#fff}
a.hr-btn-p:hover,button.hr-btn-p:hover,.hr a.hr-btn-p:hover{background:var(--hr-teald);transform:translateY(-1px);color:#fff}
a.hr-btn-g,button.hr-btn-g,.hr a.hr-btn-g,.hr button.hr-btn-g{background:transparent;color:var(--hr-ink);border-color:var(--hr-border2)}
a.hr-btn-g:hover,button.hr-btn-g:hover,.hr a.hr-btn-g:hover{border-color:var(--hr-ink);color:var(--hr-ink)}
a.hr-btn-d,button.hr-btn-d,.hr a.hr-btn-d,.hr button.hr-btn-d{background:var(--hr-ink);color:#fff}
a.hr-btn-d:hover,button.hr-btn-d:hover{background:#000;transform:translateY(-1px);color:#fff}

/* === HERO === */
.hr-hero{padding:64px 0 96px;background:var(--hr-bg);position:relative;overflow:hidden}
.hr-hero::before{content:"";position:absolute;top:-200px;right:-300px;width:700px;height:700px;border-radius:50%;background:radial-gradient(circle,rgba(11,179,156,.08),transparent 60%);pointer-events:none}
.hr-hero-grid{display:grid;grid-template-columns:1.1fr 1fr;gap:64px;align-items:start;position:relative}
.hr-eyebrow{display:inline-flex;align-items:center;gap:10px;margin-bottom:20px;color:var(--hr-tealh);font-size:12px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;font-feature-settings:"ss01"}
.hr-eyebrow::before{content:"";width:20px;height:1px;background:var(--hr-teal)}
.hr-eyebrow-light{color:var(--hr-tealx)}
.hr-eyebrow-light::before{background:var(--hr-tealx)}

.hr-h1{font-family:var(--hr-fd);font-weight:800;font-size:clamp(40px,5.4vw,64px);line-height:.98;letter-spacing:-.035em;margin:0 0 24px;color:var(--hr-ink)}
.hr-h1 em{font-style:normal;color:var(--hr-teal);font-weight:800}
.hr-h1 s{text-decoration:line-through;text-decoration-color:rgba(11,179,156,.35);text-decoration-thickness:3px;color:var(--hr-faint);font-weight:800}

.hr-lead{font-size:18px;line-height:1.6;color:var(--hr-ink2);max-width:58ch;margin:0 0 32px;font-weight:400}

.hr-bullets{list-style:none;padding:0;margin:0 0 32px;display:grid;gap:12px}
.hr-bullets li{display:flex;align-items:flex-start;gap:14px;font-size:15px;color:var(--hr-ink);line-height:1.5;font-weight:500}
.hr-bullets-icn{flex:0 0 22px;width:22px;height:22px;border-radius:50%;background:var(--hr-teals);color:var(--hr-tealh);display:grid;place-items:center;font-weight:800;font-size:12px;margin-top:2px;border:1px solid var(--hr-tealb)}

.hr-hero-acts{display:flex;flex-wrap:wrap;gap:12px;align-items:center;margin-bottom:32px}

/* Mini-stats z perspektywy klienta */
.hr-hero-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:0}
.hr-hero-stat{padding:18px 18px;background:var(--hr-surface);border:1px solid var(--hr-border);border-radius:var(--hr-rl);display:flex;flex-direction:column;gap:4px;align-items:center;text-align:center}
.hr-hero-stat strong{font-family:var(--hr-fd);font-weight:800;font-size:22px;color:var(--hr-ink);line-height:1.05;letter-spacing:-.025em}
.hr-hero-stat strong em{font-style:normal;color:var(--hr-teal)}
.hr-hero-stat span{font-size:12.5px;color:var(--hr-muted);line-height:1.45;font-weight:500}

/* Telefon dla klientów z natychmiastową intencją */
.hr-hero-call{margin-top:24px;padding-top:20px;border-top:1px solid var(--hr-line);font-size:14px;color:var(--hr-muted);display:flex;flex-wrap:wrap;align-items:center;gap:10px}
.hr-hero-call a{color:var(--hr-ink);font-weight:700;text-decoration:none;font-family:var(--hr-fd);letter-spacing:-.015em;font-size:17px;border-bottom:1.5px solid var(--hr-teal);padding-bottom:1px}
.hr-hero-call a:hover{color:var(--hr-tealh)}

/* === HERO FORM CARD === */
.hr-form-card{background:var(--hr-surface);border:1px solid var(--hr-border);border-radius:var(--hr-rxl);padding:32px;box-shadow:0 1px 0 rgba(15,17,21,.04),0 4px 12px rgba(15,17,21,.04);position:sticky;top:24px}
.hr-form-tag{display:inline-block;padding:5px 11px;background:var(--hr-tealss);border:1px solid var(--hr-tealb);border-radius:99px;font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--hr-tealh);margin-bottom:14px;font-feature-settings:"ss01"}
.hr-form-card h2{margin:0 0 8px;font-family:var(--hr-fd);font-size:26px;font-weight:800;line-height:1.04;letter-spacing:-.025em;color:var(--hr-ink)}
.hr-form-sub{margin:0 0 24px;color:var(--hr-muted);font-size:14px;line-height:1.55}
.hr-form-after{margin-top:18px;padding:14px 16px;background:var(--hr-soft);border-radius:var(--hr-rl);font-size:12.5px;color:var(--hr-muted);line-height:1.55}
.hr-form-after strong{color:var(--hr-ink);display:block;margin-bottom:4px;font-family:var(--hr-fd);font-weight:700;font-size:13px}

/* Pola formularza w karcie */
.hr-form-card .ups-form input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),
.hr-form-card .ups-form select,
.hr-form-card .ups-form textarea{width:100%;min-height:46px;background:var(--hr-surface);border:1.5px solid var(--hr-border);border-radius:var(--hr-r);padding:11px 14px;color:var(--hr-ink);font-size:14.5px;font-family:inherit;transition:border-color .15s,box-shadow .15s}
.hr-form-card .ups-form textarea{min-height:96px;resize:vertical;line-height:1.55}
.hr-form-card .ups-form input:focus,.hr-form-card .ups-form select:focus,.hr-form-card .ups-form textarea:focus{border-color:var(--hr-teal);box-shadow:0 0 0 3px rgba(11,179,156,.12);outline:none}
.hr-form-card .ups-form__label{font-family:var(--hr-fb);font-weight:600;font-size:13.5px;color:var(--hr-ink);margin-bottom:6px;display:block;letter-spacing:-.005em}
.hr-form-card .ups-form__consent{display:flex;align-items:flex-start;gap:10px;font-size:12.5px;color:var(--hr-muted);line-height:1.5}
.hr-form-card .ups-form__consent input[type="checkbox"]{width:18px;height:18px;min-height:18px;margin:2px 0 0;flex-shrink:0;accent-color:var(--hr-teal)}
.hr-form-card .ups-form__submit,.hr-form-card button[type="submit"]{width:100%;min-height:52px;border:0;border-radius:99px;background:var(--hr-ink);color:#fff;font-family:var(--hr-fb);font-size:15px;font-weight:700;cursor:pointer;margin-top:8px;transition:background .15s,transform .15s;letter-spacing:-.01em}
.hr-form-card .ups-form__submit:hover{background:var(--hr-teald);transform:translateY(-1px)}

/* === UKRYCIE PÓL FORMULARZA — Firma + Twoja miesięczna sprzedaż === */
/* Pierwsze pole drugie w row-2: lead_company. Reszta zostaje. */
.hr-form-card .ups-form__row-2:first-of-type{grid-template-columns:1fr !important}
.hr-form-card .ups-form__row-2:first-of-type > div:nth-child(2){display:none !important}
/* Twoja miesieczna sprzedaż — label + select po nim */
.hr-form-card .ups-form label[for*="monthly-sales"],
.hr-form-card .ups-form select[name="lead_monthly_sales"]{display:none !important}

/* === SECTIONS === */
.hr-section{padding:96px 0}
.hr-section-soft{background:var(--hr-soft)}
.hr-section-dark{background:var(--hr-dark);color:#fff;position:relative;overflow:hidden}
.hr-section-dark::before{content:"";position:absolute;width:600px;height:600px;border-radius:50%;background:radial-gradient(circle,rgba(11,179,156,.14),transparent 65%);right:-240px;top:-240px;pointer-events:none}
.hr-section-dark .hr-wrap{position:relative;z-index:2}

.hr-sec-head{max-width:760px;margin-bottom:48px}
.hr-sec-head h2{margin:0;font-family:var(--hr-fd);font-weight:800;font-size:clamp(32px,3.8vw,48px);line-height:1.02;letter-spacing:-.035em;color:var(--hr-ink)}
.hr-section-dark .hr-sec-head h2{color:#fff}
.hr-sec-head p{margin:14px 0 0;max-width:62ch;color:var(--hr-muted);font-size:17px;line-height:1.6}
.hr-section-dark .hr-sec-head p{color:rgba(255,255,255,.65)}

/* === LICZBY (legacy, nieuzywane juz) === */
.hr-numbers{padding:56px 0;background:#fff;border-top:1px solid var(--hr-border);border-bottom:1px solid var(--hr-border)}

/* === CO ZYSKUJESZ === */
.hr-gain{padding:96px 0;background:var(--hr-surface);border-top:1px solid var(--hr-line);border-bottom:1px solid var(--hr-line)}
.hr-gain-head{max-width:760px;margin-bottom:40px}
.hr-gain-head h2{margin:8px 0 0;font-family:var(--hr-fd);font-weight:800;font-size:clamp(30px,3.6vw,44px);line-height:1.04;letter-spacing:-.03em;color:var(--hr-ink)}
.hr-gain-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
.hr-gain-card{background:var(--hr-surface);border:1px solid var(--hr-border);border-radius:var(--hr-rl);padding:24px;display:flex;flex-direction:column;gap:10px;transition:border-color .2s,transform .2s}
.hr-gain-card:hover{border-color:var(--hr-teal);transform:translateY(-2px)}
.hr-gain-icn{width:38px;height:38px;border-radius:10px;background:var(--hr-tealss);color:var(--hr-tealh);display:grid;place-items:center;flex-shrink:0;margin-bottom:6px;border:1px solid var(--hr-tealb)}
.hr-gain-card h3{margin:0;font-family:var(--hr-fd);font-weight:800;font-size:16px;line-height:1.22;letter-spacing:-.02em;color:var(--hr-ink)}
.hr-gain-card p{margin:0;color:var(--hr-muted);font-size:13.5px;line-height:1.55}

/* === Wewnetrzny CTA na koncu każdej sekcji === */
.hr-step-cta{margin-top:40px;text-align:center;padding-top:32px;border-top:1px solid var(--hr-line)}
.hr-step-cta p{margin:0;color:var(--hr-muted);font-size:15px;font-weight:500}
.hr-step-cta a{color:var(--hr-ink);font-weight:700;text-decoration:none;border-bottom:2px solid var(--hr-teal);padding-bottom:2px;transition:color .15s}
.hr-step-cta a:hover{color:var(--hr-tealh)}
.hr-section-dark .hr-step-cta{border-top-color:rgba(255,255,255,.1)}
.hr-section-dark .hr-step-cta p{color:rgba(255,255,255,.65)}
.hr-section-dark .hr-step-cta a{color:#fff;border-bottom-color:var(--hr-tealx)}
.hr-section-dark .hr-step-cta a:hover{color:var(--hr-tealx)}

/* === PROBLEMS === */
.hr-problems{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.hr-problem{background:var(--hr-surface);border:1px solid var(--hr-border);border-radius:var(--hr-rxl);padding:32px;transition:all .2s}
.hr-problem:hover{border-color:var(--hr-ink);transform:translateY(-3px)}
.hr-problem-num{font-family:var(--hr-fd);font-weight:700;font-size:12px;color:var(--hr-tealh);letter-spacing:.08em;margin-bottom:14px;font-feature-settings:"ss01"}
.hr-problem h3{margin:0 0 12px;font-family:var(--hr-fd);font-weight:800;font-size:20px;line-height:1.18;letter-spacing:-.025em;color:var(--hr-ink)}
.hr-problem-body{color:var(--hr-muted);font-size:14.5px;line-height:1.6}

/* === SYSTEM (3 usługi) === */
.hr-system{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.hr-svc{background:var(--hr-surface);border:1px solid var(--hr-border);border-radius:var(--hr-rxl);padding:32px;display:flex;flex-direction:column;text-decoration:none;color:inherit;transition:all .2s;position:relative}
.hr-svc:hover{transform:translateY(-3px);border-color:var(--hr-ink)}
.hr-svc-tag{display:inline-block;align-self:flex-start;padding:5px 11px;border-radius:99px;font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;margin-bottom:16px;background:var(--hr-tealss);color:var(--hr-tealh);border:1px solid var(--hr-tealb);font-feature-settings:"ss01"}
.hr-svc h3{margin:0 0 12px;font-family:var(--hr-fd);font-weight:800;font-size:22px;line-height:1.15;letter-spacing:-.025em;color:var(--hr-ink)}
.hr-svc-desc{color:var(--hr-muted);font-size:14.5px;line-height:1.6;margin:0 0 18px;flex:1}
.hr-svc-list{list-style:none;padding:0;margin:0 0 22px;display:grid;gap:8px}
.hr-svc-list li{display:flex;gap:8px;font-size:13.5px;color:var(--hr-ink);line-height:1.5}
.hr-svc-list li::before{content:"\2713";color:var(--hr-teal);font-weight:800;flex-shrink:0}
.hr-svc-arrow{display:inline-flex;align-items:center;gap:6px;font-size:14px;font-weight:700;color:var(--hr-ink);margin-top:auto;border-bottom:1.5px solid var(--hr-teal);padding-bottom:2px;align-self:flex-start;letter-spacing:-.005em}
.hr-svc:hover .hr-svc-arrow{color:var(--hr-tealh);gap:10px}

/* === DIFFERENTIATOR === */
.hr-diff{display:grid;grid-template-columns:1fr 1.05fr;gap:56px;align-items:start}
.hr-diff-side{display:grid;gap:14px}
.hr-diff-card{background:var(--hr-surface);border:1px solid var(--hr-border);border-radius:var(--hr-rl);padding:24px;transition:border-color .2s}
.hr-diff-card:hover{border-color:var(--hr-ink)}
.hr-diff-card-num{font-family:var(--hr-fd);font-weight:700;font-size:11px;color:var(--hr-tealh);letter-spacing:.08em;margin-bottom:8px;font-feature-settings:"ss01"}
.hr-diff-card h3{margin:0 0 8px;font-family:var(--hr-fd);font-weight:800;font-size:18px;line-height:1.2;letter-spacing:-.02em;color:var(--hr-ink)}
.hr-diff-card p{margin:0;color:var(--hr-muted);font-size:14px;line-height:1.55}

/* === CASE STUDY === */
.hr-case{display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:start;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.1);border-radius:var(--hr-rxxl);padding:48px}
.hr-case-tag{display:inline-block;padding:5px 11px;background:rgba(61,216,195,.12);color:var(--hr-tealx);border:1px solid rgba(61,216,195,.25);border-radius:99px;font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;margin-bottom:18px;font-feature-settings:"ss01"}
.hr-case h3{font-family:var(--hr-fd);font-weight:800;font-size:28px;line-height:1.1;letter-spacing:-.03em;color:#fff;margin:0 0 16px}
.hr-case p{color:rgba(255,255,255,.7);font-size:15px;line-height:1.6;margin:0 0 24px}
.hr-case-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
.hr-case-stat{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.1);border-radius:var(--hr-rl);padding:18px}
.hr-case-stat-val{font-family:var(--hr-fd);font-weight:800;font-size:26px;color:var(--hr-tealx);line-height:1;letter-spacing:-.025em;margin-bottom:6px}
.hr-case-stat-label{font-size:12px;color:rgba(255,255,255,.55)}
.hr-case-quote{padding:24px;background:rgba(255,255,255,.03);border-left:3px solid var(--hr-tealx);border-radius:var(--hr-rl);font-size:15.5px;line-height:1.65;color:rgba(255,255,255,.88);font-weight:400}
.hr-case-quote-author{margin-top:14px;font-style:normal;font-size:13px;color:rgba(255,255,255,.55);font-weight:600}

/* === PROCES === */
.hr-process{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;counter-reset:hrstep}
.hr-step{background:var(--hr-surface);border:1px solid var(--hr-border);border-radius:var(--hr-rxl);padding:28px;position:relative}
.hr-step::before{counter-increment:hrstep;content:counter(hrstep,decimal-leading-zero);position:absolute;top:18px;right:22px;font-family:var(--hr-fd);font-size:46px;font-weight:800;color:var(--hr-tealb);line-height:1;letter-spacing:-.05em}
.hr-step h3{margin:0 0 10px;font-family:var(--hr-fd);font-weight:800;font-size:18px;line-height:1.2;letter-spacing:-.025em;color:var(--hr-ink);max-width:14ch;position:relative}
.hr-step p{margin:0 0 12px;color:var(--hr-muted);font-size:14px;line-height:1.6;position:relative}
.hr-step-when{display:inline-block;padding:4px 10px;background:var(--hr-tealss);color:var(--hr-tealh);border:1px solid var(--hr-tealb);border-radius:99px;font-size:11px;font-weight:700;letter-spacing:.04em;position:relative;font-feature-settings:"ss01"}

/* === DLA KOGO === */
.hr-targets{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.hr-target{background:var(--hr-surface);border:1px solid var(--hr-border);border-radius:var(--hr-rxl);padding:32px;transition:border-color .2s,transform .2s}
.hr-target:hover{border-color:var(--hr-ink);transform:translateY(-2px)}
.hr-target-tag{display:inline-block;padding:5px 11px;background:var(--hr-tealss);color:var(--hr-tealh);border:1px solid var(--hr-tealb);border-radius:99px;font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;margin-bottom:16px;font-feature-settings:"ss01"}
.hr-target h3{margin:0 0 12px;font-family:var(--hr-fd);font-weight:800;font-size:20px;line-height:1.18;letter-spacing:-.025em;color:var(--hr-ink)}
.hr-target-list{list-style:none;padding:0;margin:0;display:grid;gap:8px}
.hr-target-list li{display:flex;gap:10px;color:var(--hr-muted);font-size:14px;line-height:1.55}
.hr-target-list li::before{content:"\2192";color:var(--hr-teal);font-weight:800;flex-shrink:0}

/* === FAQ === */
.hr-faq{display:grid;gap:8px;max-width:880px}
.hr-faq-it{background:var(--hr-surface);border:1px solid var(--hr-border);border-radius:var(--hr-rl);transition:all .15s}
.hr-faq-it[open]{border-color:var(--hr-ink)}
.hr-faq-it summary{list-style:none;cursor:pointer;display:flex;justify-content:space-between;align-items:center;gap:18px;padding:22px 26px;font-family:var(--hr-fd);font-size:17px;font-weight:700;color:var(--hr-ink);letter-spacing:-.015em}
.hr-faq-it summary::-webkit-details-marker{display:none}
.hr-faq-icn{width:26px;height:26px;border-radius:50%;background:var(--hr-soft);display:grid;place-items:center;font-size:18px;color:var(--hr-muted);flex:0 0 26px;transition:all .2s}
.hr-faq-it[open] .hr-faq-icn{transform:rotate(45deg);background:var(--hr-tealss);color:var(--hr-tealh)}
.hr-faq-it p{margin:0;padding:0 26px 24px;color:var(--hr-muted);font-size:15px;line-height:1.65}
.hr-faq-it p+p{padding-top:4px}

/* === KONTAKT === */
.hr-kontakt{background:var(--hr-soft);padding:96px 0 120px;border-top:1px solid var(--hr-line)}
.hr-kontakt-grid{display:grid;grid-template-columns:1fr 1fr;gap:56px;align-items:start}
.hr-kontakt-info h2{font-family:var(--hr-fd);font-weight:800;font-size:clamp(30px,3.6vw,44px);line-height:1.04;letter-spacing:-.03em;color:var(--hr-ink);margin:0 0 18px}
.hr-kontakt-info p{margin:0 0 24px;color:var(--hr-muted);font-size:16px;line-height:1.6;max-width:54ch}
.hr-kontakt-info-list{list-style:none;padding:0;margin:0;display:grid;gap:10px}
.hr-kontakt-info-list li{display:flex;gap:12px;font-size:14.5px;color:var(--hr-ink);line-height:1.5;font-weight:500}
.hr-kontakt-info-list li::before{content:"\2192";color:var(--hr-teal);font-weight:800;flex-shrink:0}
.hr-kontakt-direct{padding:22px;background:var(--hr-surface);border:1px solid var(--hr-border);border-radius:var(--hr-rl);display:grid;gap:10px;font-size:14px;color:var(--hr-ink)}
.hr-kontakt-direct strong{display:block;color:var(--hr-ink);font-family:var(--hr-fd);font-weight:700;font-size:13px;letter-spacing:.04em;margin-bottom:8px;text-transform:uppercase;font-feature-settings:"ss01"}
.hr-kontakt-direct a{color:var(--hr-ink);font-weight:600;border-bottom:1.5px solid var(--hr-teal);padding-bottom:1px}
.hr-kontakt-direct a:hover{color:var(--hr-tealh)}

/* === ANIMACJE === */
[data-hr-anim]{opacity:0;transform:translateY(16px);transition:opacity .7s ease,transform .7s ease}
[data-hr-anim].is-visible{opacity:1;transform:translateY(0)}
[data-hr-delay="1"]{transition-delay:.08s}
[data-hr-delay="2"]{transition-delay:.16s}
[data-hr-delay="3"]{transition-delay:.24s}
@media (prefers-reduced-motion:reduce){[data-hr-anim]{opacity:1;transform:none;transition:none}}

/* === RESPONSIVE === */
@media (max-width:1024px){
  .hr-hero-grid{grid-template-columns:1fr;gap:48px}
  .hr-form-card{position:static}
  .hr-problems,.hr-system,.hr-targets{grid-template-columns:1fr 1fr}
  .hr-process{grid-template-columns:1fr 1fr}
  .hr-gain-grid{grid-template-columns:1fr 1fr}
  .hr-diff,.hr-case,.hr-kontakt-grid{grid-template-columns:1fr;gap:36px}
  .hr-case{padding:36px}
}
@media (max-width:720px){
  .hr-hero{padding:40px 0 64px}
  .hr-section{padding:64px 0}
  .hr-gain{padding:64px 0}
  .hr-problems,.hr-system,.hr-targets,.hr-process,.hr-gain-grid{grid-template-columns:1fr}
  .hr-form-card{padding:24px;border-radius:var(--hr-rl)}
  .hr-case{padding:28px;border-radius:var(--hr-rxl)}
  .hr-case-stats{grid-template-columns:1fr 1fr}
  .hr-kontakt{padding:64px 0 80px}
  .hr-hero-stats{grid-template-columns:1fr 1fr}
}
</style>

<main class="hr" id="strona-glowna">

<!-- ====================================================================
     SEKCJA 01 — HERO + FORM (lead_form_origin = "home-hero-form")
     ==================================================================== -->
<section class="hr-hero" id="start" aria-labelledby="hr-h1">
  <div class="hr-wrap">
    <div class="hr-hero-grid">
      <div data-hr-anim>
        <div class="hr-eyebrow">Dla firm, którym reklama nie zamienia się w klientów</div>
        <h1 class="hr-h1" id="hr-h1">
          Twoja reklama działa. <s>Klientów</s> jakoś brak. <em>Naprawmy to.</em>
        </h1>
        <p class="hr-lead">
          Płacisz za reklamy, masz ruch na stronie, ale klientów dalej brakuje. Problem może zaczynać się już na etapie targetowania kampanii, albo później, gdy strona nie tłumaczy jasno, dlaczego klient ma wybrać właśnie Ciebie.
        </p>
        <ul class="hr-bullets">
          <li><span class="hr-bullets-icn">&#10003;</span><span>Więcej kwalifikowanych zapytań z tego samego budżetu reklamowego</span></li>
          <li><span class="hr-bullets-icn">&#10003;</span><span>Strona i kampania prowadzone wspólnie — nie tłumaczysz briefu trzem osobom</span></li>
          <li><span class="hr-bullets-icn">&#10003;</span><span>Mierzalny lejek: znasz koszt leada, jakość i źródło, nie tylko CTR</span></li>
          <li><span class="hr-bullets-icn">&#10003;</span><span>Konkretne odpowiedzi w 30 minut, bez slajdów i sales-talku</span></li>
        </ul>
        <div class="hr-hero-acts">
          <a href="#kontakt" class="hr-btn hr-btn-p">Umów bezpłatną diagnozę →</a>
          <a href="#problem" class="hr-btn hr-btn-g">Sprawdź czy to o Tobie</a>
        </div>

        <!-- Mini-stats z perspektywy klienta (zastapily liczby Sebastiana) -->
        <div class="hr-hero-stats">
          <div class="hr-hero-stat">
            <strong>2-4 tyg.</strong>
            <span>do pierwszych leadów</span>
          </div>
          <div class="hr-hero-stat">
            <strong>0 zł</strong>
            <span>Tylko kosztuje pierwsza analiza twojej sytuacji</span>
          </div>
          <div class="hr-hero-stat">
            <strong>0%</strong>
            <span>prowizji od budżetu reklamowego</span>
          </div>
        </div>

        <!-- Konkretny CTA telefoniczny pod stats — dla klientów, którzy już wiedzą, że chcą rozmowy -->
        <div class="hr-hero-call">
          Wolisz od razu zadzwońić?
          <a href="tel:<?php echo esc_attr($contact_phone_href); ?>"><?php echo esc_html($contact_phone); ?></a>
          · odpowiadam osobiście pn–pt 9-17
        </div>
      </div>

      <aside class="hr-form-card" data-hr-anim data-hr-delay="1">
        <span class="hr-form-tag">&#9658; Zacznij tu</span>
        <h2>Bezpłatna 30-min diagnoza</h2>
        <p class="hr-form-sub">
          Pokaż link do strony i opisz w 2 zdaniach, z czym dziś masz największy problem lub co chcesz poprawić. Odpowiadam w 24h roboczych.
        </p>
        <?php
        if (function_exists("upsellio_render_lead_form")) {
            echo upsellio_render_lead_form([
                "origin" => "home-hero-form",
                "submit_label" => "Umów bezpłatną diagnozę \xE2\x86\x92",
                "variant" => "full",
                "heading" => "",
                "subheading" => "",
                "redirect_url" => $site_url,
                "service_options" => $service_options,
                "css_class" => "hr-form",
                "form_id" => "home-hero-form",
            ]);
        } else {
            echo '<p>Napisz na <a href="mailto:' . esc_attr($contact_email) . '">' . esc_html($contact_email) . '</a></p>';
        }
        ?>
        <div class="hr-form-after">
          <strong>Co dalej po wysłaniu?</strong>
          Do 24h dostaniesz odpowiedź z propozycją terminu. Telefon lub Google Meet — jak Ci wygodniej.
        </div>
      </aside>
    </div>
  </div>
</section>


<!-- ====================================================================
     SEKCJA 02 — CO ZYSKUJESZ (4 punkty z perspektywy klienta)
     Zastapilo sekcje "Liczby" — mówi o korzysciach klienta, nie o moim CV
     Na koncu wewnetrzne CTA do nastepnej sekcji (Problem)
     ==================================================================== -->
<section class="hr-gain" id="zyski" aria-labelledby="hr-gain-h2">
  <div class="hr-wrap">
    <header class="hr-gain-head" data-hr-anim>
      <span class="hr-eyebrow">Co konkretnie zyskujesz</span>
      <h2 id="hr-gain-h2">Cztery rzeczy, które zmieniają się w pierwszych 60 dniach współpracy</h2>
    </header>

    <div class="hr-gain-grid">
      <article class="hr-gain-card" data-hr-anim>
        <div class="hr-gain-icn" aria-hidden="true">
          <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="m7 14 4-4 4 4 5-5"/></svg>
        </div>
        <h3>Więcej leadów z tego samego budżetu</h3>
        <p>Bez zwiększania wydatków na reklamę. Pracujemy nad jakością kierowania ruchu i konwersją strony — tu jest najwięcej do wyciągnięcia.</p>
      </article>
      <article class="hr-gain-card" data-hr-anim data-hr-delay="1">
        <div class="hr-gain-icn" aria-hidden="true">
          <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
        </div>
        <h3>Mniej czasu na koordynację wykonawców</h3>
        <p>Reklama, strona i lejek prowadzone przez jedną osobę. Nie wysyłasz tego samego briefu trzem firmom i nie szukasz, kto za co odpowiada gdy coś się sypie.</p>
      </article>
      <article class="hr-gain-card" data-hr-anim data-hr-delay="2">
        <div class="hr-gain-icn" aria-hidden="true">
          <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h4l3-9 4 18 3-9h4"/></svg>
        </div>
        <h3>Wiesz dokładnie co działa, a co nie</h3>
        <p>Tracking end-to-end od reklamy do zapytań w skrzynce. Znasz koszt leada, jakość i źródło. Możesz spokojnie inwestować więcej w to, co działa.</p>
      </article>
      <article class="hr-gain-card" data-hr-anim data-hr-delay="3">
        <div class="hr-gain-icn" aria-hidden="true">
          <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
        </div>
        <h3>Spokój, że ktoś dba o Twój wynik</h3>
        <p>Bez prowizji od budżetu reklamowego — nikt nie ma interesu żeby Cię do niego namawiać. Decyzje podejmujemy razem, na podstawie liczb.</p>
      </article>
    </div>

    <!-- Wewnetrzny CTA prowadzacy do kolejnej sekcji -->
    <div class="hr-step-cta" data-hr-anim>
      <p>Brzmi jak to, czego szukasz? <a href="#problem">Sprawdź najpierw, która sytuacja Cię dotyczy →</a></p>
    </div>
  </div>
</section>


<!-- ====================================================================
     SEKCJA 03 — PROBLEM
     ==================================================================== -->
<section class="hr-section hr-section-soft" id="problem" aria-labelledby="hr-problem-h2">
  <div class="hr-wrap">
    <header class="hr-sec-head" data-hr-anim>
      <span class="hr-eyebrow">Po co to wszystko</span>
      <h2 id="hr-problem-h2">Trzy najczęstsze sytuacje firm, które do mnie trafiają</h2>
      <p>
        Niezależnie od branży, schemat często wygląda tak samo: reklamy generują ruch, ale coś po drodze blokuje sprzedaż. Czasem problemem jest oferta, czasem komunikacja strony, a czasem brak spójności między reklamą, stroną i procesem kontaktu.
      </p>
    </header>
    <div class="hr-problems">
      <article class="hr-problem" data-hr-anim>
        <div class="hr-problem-num">SYTUACJA 01</div>
        <h3>Wydajemy na reklamy, ale klientów dalej jest za mało</h3>
        <p class="hr-problem-body">
          Kampanie generują ruch i zapytania, ale ich jakość nie przekłada się na sprzedaż. Najczęściej problem nie leży w samej reklamie, tylko w tym, jak oferta i strona komunikują wartość firmy po kliknięciu.
        </p>
      </article>
      <article class="hr-problem" data-hr-anim data-hr-delay="1">
        <div class="hr-problem-num">SYTUACJA 02</div>
        <h3>Ruch jest, ale formularz milczy</h3>
        <p class="hr-problem-body">
          Ruch na stronie jest, ale użytkownicy szybko wychodzą i rzadko zostawiają kontakt. Strona wygląda poprawnie, jednak klient nie rozumie jasno, co oferujesz, dlaczego warto Ci zaufać i co wyróżnia Cię na tle konkurencji.
        </p>
      </article>
      <article class="hr-problem" data-hr-anim data-hr-delay="2">
        <div class="hr-problem-num">SYTUACJA 03</div>
        <h3>Mam wszystkie kanały, ale nie wiem co działa</h3>
        <p class="hr-problem-body">
          Google Ads u jednego, Meta u drugiego, strona od trzeciego. Każdy raportuje swoje liczby. Nikt nie patrzy na cały lejek. Tracisz pieniądze w przerwach między kanałami.
        </p>
      </article>
    </div>

    <!-- Wewnetrzny CTA prowadzacy do kolejnej sekcji -->
    <div class="hr-step-cta" data-hr-anim>
      <p>Identyfikujesz się z którąś z tych sytuacji? <a href="#system">Zobacz jak konkretnie wyglądają moje usługi →</a></p>
    </div>
  </div>
</section>


<!-- ====================================================================
     SEKCJA 04 — SYSTEM (3 usługi z linkami do podstron)
     ==================================================================== -->
<section class="hr-section" id="system" aria-labelledby="hr-system-h2">
  <div class="hr-wrap">
    <header class="hr-sec-head" data-hr-anim>
      <span class="hr-eyebrow">System Upsellio</span>
      <h2 id="hr-system-h2">Trzy elementy. Jeden system. Mierzalny od pierwszej kampanii.</h2>
      <p>
        Nie sprzedaję usług osobno. Każdą można wziąć pojedynczo, ale realne wyniki dają, gdy współpracują. Wybierz to, czego dziś najbardziej potrzebujesz — albo idź pakietem.
      </p>
    </header>

    <div class="hr-system">
      <a class="hr-svc is-google" href="<?php echo esc_url($google_ads_url); ?>" data-hr-anim>
        <span class="hr-svc-tag">Google Ads</span>
        <h3>Reklamy Google które łapią klienta z intencją zakupową</h3>
        <p class="hr-svc-desc">
          Search, Performance Max, Shopping i remarketing. Konfiguracja konta, struktura kampanii, codzienna optymalizacja. Z naciskiem na jakość leadów, nie tylko CTR.
        </p>
        <ul class="hr-svc-list">
          <li>Audyt obecnych kampanii lub analiza popytu w branży</li>
          <li>Search + Display + PMax + remarketing</li>
          <li>GA4 + Tag Manager — pełny tracking konwersji</li>
        </ul>
        <span class="hr-svc-arrow">Sprawdź jak Ci pomogę w Google →</span>
      </a>

      <a class="hr-svc is-meta" href="<?php echo esc_url($meta_ads_url); ?>" data-hr-anim data-hr-delay="1">
        <span class="hr-svc-tag">Meta Ads</span>
        <h3>Facebook + Instagram dla budowania popytu i remarketing</h3>
        <p class="hr-svc-desc">
          Conversions, Lead Ads, Catalog Sales. Pixel i Conversions API, lookalike audiences, remarketing wielowarstwowy. Idealne gdy klient nie wpisuje Twojej kategorii w Google.
        </p>
        <ul class="hr-svc-list">
          <li>Strategia kreacji + targetowanie zimne/ciepłe/gorące</li>
          <li>Pixel + CAPI server-side dla atrybucji po iOS</li>
          <li>Lookalike + custom audiences z CRM</li>
        </ul>
        <span class="hr-svc-arrow">Sprawdź jak Ci pomogę na Meta →</span>
      </a>

      <a class="hr-svc is-www" href="<?php echo esc_url($websites_url); ?>" data-hr-anim data-hr-delay="2">
        <span class="hr-svc-tag">Strony WWW</span>
        <h3>Strony i landing pages projektowane pod konwersję</h3>
        <p class="hr-svc-desc">
          Strona firmowa B2B, landing page pod kampanie, sklep WooCommerce. Z copywritingiem, SEO podstawowym i panelem do edycji w cenie projektu.
        </p>
        <ul class="hr-svc-list">
          <li>Copywriting (nagłówki, sekcje, CTA, FAQ) w cenie</li>
          <li>WordPress z panelem — sam edytujesz po oddaniu</li>
          <li>Mobile-first, Core Web Vitals, SEO podstawowe</li>
        </ul>
        <span class="hr-svc-arrow">Sprawdź jak Ci pomogę ze stroną →</span>
      </a>
    </div>

    <!-- Wewnetrzny CTA na koncu sekcji — prowadzi do Differentiator -->
    <div class="hr-step-cta" data-hr-anim>
      <p>Nie wiesz, która opcja Ci pasuje? <a href="#kontakt">Napisz w 2 zdaniach, co Ci nie działa — doradzę →</a></p>
    </div>
  </div>
</section>


<!-- ====================================================================
     SEKCJA 05 — DIFFERENTIATOR (przepisana z perspektywy klienta)
     ==================================================================== -->
<section class="hr-section hr-section-soft" id="wyroznik" aria-labelledby="hr-wyroznik-h2">
  <div class="hr-wrap">
    <div class="hr-diff">
      <div data-hr-anim>
        <span class="hr-eyebrow">Co rozwiązuję</span>
        <h2 id="hr-wyroznik-h2" style="font-family:var(--hr-fd);font-weight:800;font-size:clamp(28px,3.4vw,42px);line-height:1.04;letter-spacing:-1.2px;color:var(--hr-ink);margin:0 0 16px;">
          Cztery problemy, które najczęściej blokują sprzedaż
        </h2>
        <p style="color:#475569;font-size:16px;line-height:1.65;margin:0 0 26px;max-width:55ch">
          Większość firm, z którymi rozmawiam, trafia do mnie z bardzo podobnymi problemami. Poniżej pokazuję, gdzie najczęściej uciekają klienci i co konkretnie zmieniam, żeby to naprawić.
        </p>
        <a href="#kontakt" class="hr-btn hr-btn-g">Powiedz mi, co Cię blokuje →</a>
      </div>

      <div class="hr-diff-side" data-hr-anim data-hr-delay="1">
        <article class="hr-diff-card">
          <div class="hr-diff-card-num">BÓL 01</div>
          <h3>„Reklamy generują ruch, ale sprzedaż się nie zwiększa”</h3>
          <p>Analizuję cały proces po kliknięciu: stronę, komunikację, CTA i formularz kontaktowy. Największe straty najczęściej pojawiają się właśnie tutaj, nie w samej kampanii reklamowej.</p>
        </article>
        <article class="hr-diff-card">
          <div class="hr-diff-card-num">BÓL 02</div>
          <h3>"Briefuję 3 wykonawców, każdy mówi co innego"</h3>
          <p>Reklama, strona i lejek prowadzone przez jedną osobę. Jeden brief, jeden punkt kontaktu, spójny komunikat od reklamy aż do skrzynki kontaktowej. Bez obietnic typu „oddzwonimy do Pana w piątek”.</p>
        </article>
        <article class="hr-diff-card">
          <div class="hr-diff-card-num">BÓL 03</div>
          <h3>„Nie wiem, która reklama przynosi mi klientów”</h3>
          <p>Pełny tracking od reklamy do zapytania. Wiesz, ile kosztuje pozyskanie leada, z jakiego źródła pochodzi i która kampania realnie generuje klientów. Możesz spokojnie inwestować więcej w to, co działa i wyłączyć to, co nie przynosi efektów.</p>
        </article>
        <article class="hr-diff-card">
          <div class="hr-diff-card-num">BÓL 04</div>
          <h3>„Czuję, że ktoś chce, żeby mój budżet rósł, nie moja sprzedaż”</h3>
          <p>Bez prowizji od budżetu reklamowego. Twój budżet trafia bezpośrednio do Google i Mety, a ja rozliczam się stałą miesięczną kwotą za prowadzenie kampanii. Dzięki temu decyzje o zwiększaniu budżetu podejmujemy na podstawie wyników, a nie dlatego, że ktoś zarabia więcej, gdy wydajesz więcej.</p>
        </article>
      </div>
    </div>

    <!-- Wewnetrzny CTA prowadzacy do case study -->
    <div class="hr-step-cta" data-hr-anim>
      <p>To wszystko brzmi dobrze, ale czy faktycznie działa? <a href="#case-study">Zobacz konkretny przykład →</a></p>
    </div>
  </div>
</section>


<!-- ====================================================================
     SEKCJA 07 — CASE STUDY (dark band)
     ==================================================================== -->
<section class="hr-section hr-section-dark" id="case-study" aria-labelledby="hr-case-h2">
  <div class="hr-wrap">
    <header class="hr-sec-head" data-hr-anim>
      <span class="hr-eyebrow hr-eyebrow-light">Realizacje</span>
      <h2 id="hr-case-h2">Liczby, nie obietnice</h2>
      <p>
        Nie obiecuję 10 razy więcej leadów w 30 dni. Pokazuję konkretne sytuacje z konkretnymi wynikami. Pełne portfolio z liczbami i komentarzami klientów dostępne pod linkiem.
      </p>
    </header>

    <article class="hr-case" data-hr-anim>
      <div>
        <span class="hr-case-tag">Firma produkcyjna z Wielkopolski</span>
        <h3>Z agencji, która pokazywała raporty — do firmy, która przynosi klientów</h3>
        <p>
          Klient płacił za reklamy od dwóch lat. Co miesiąc dostawał kolorowe wykresy. Klientów jakoś nie przybywało. Sprawdziłem stronę i kampanie, pokazałem, co psuje sprzedaż, przebudowaliśmy razem najważniejsze rzeczy. Bez zwiększania budżetu reklamowego.
        </p>
        <div class="hr-case-stats">
          <div class="hr-case-stat">
            <div class="hr-case-stat-val">5x</div>
            <div class="hr-case-stat-label">więcej zapytań / mc</div>
          </div>
          <div class="hr-case-stat">
            <div class="hr-case-stat-val">-60%</div>
            <div class="hr-case-stat-label">taniej za zapytanie</div>
          </div>
          <div class="hr-case-stat">
            <div class="hr-case-stat-val">90 dni</div>
            <div class="hr-case-stat-label">do pierwszych efektów</div>
          </div>
        </div>
      </div>
      <div>
        <div class="hr-case-quote">
          Przez dwa lata płaciliśmy agencji, która co miesiąc przynosiła kolorowe raporty pokazujące, że wszystko się dzieje. Tylko klientów jakoś nie było więcej. Sebastian po pół godzinie pokazał nam, dlaczego — i co konkretnie zmienić. Po trzech miesiącach mamy tygodniowo tyle zapytań, co wcześniej przez cały miesiąc.
          <div class="hr-case-quote-author">— Właściciel firmy produkcyjnej, Wielkopolska</div>
        </div>
        <div style="margin-top:20px">
          <a href="<?php echo esc_url($portfolio_url); ?>" class="hr-btn hr-btn-d">Zobacz pełne portfolio →</a>
        </div>
      </div>
    </article>

    <!-- Wewnetrzny CTA prowadzacy do procesu -->
    <div class="hr-step-cta" data-hr-anim>
      <p>Chcesz zobaczyć, jak wyglądałaby praca u Ciebie? <a href="#jak-dzialam">Sprawdź proces krok po kroku →</a></p>
    </div>
  </div>
</section>


<!-- ====================================================================
     SEKCJA 07 — PROCES (4 kroki)
     ==================================================================== -->
<section class="hr-section hr-section-soft" id="jak-dzialam" aria-labelledby="hr-process-h2">
  <div class="hr-wrap">
    <header class="hr-sec-head" data-hr-anim>
      <span class="hr-eyebrow">Jak pracuję</span>
      <h2 id="hr-process-h2">Cztery kroki od pierwszej rozmowy do mierzalnych wyników</h2>
      <p>Bez slajdów, bez prezentacji, bez sales-talku. Diagnoza → strategia → wdrożenie → optymalizacja.</p>
    </header>

    <div class="hr-process">
      <article class="hr-step" data-hr-anim>
        <h3>Diagnoza</h3>
        <p>30-minutowa rozmowa. Sprawdzam stronę, kampanie, ofertę. Pokazuję, gdzie uciekają leady i co realnie warto poprawić.</p>
        <span class="hr-step-when">0 zł, 30 min</span>
      </article>
      <article class="hr-step" data-hr-anim data-hr-delay="1">
        <h3>Strategia</h3>
        <p>Ustalam priorytety i KPI. Co poprawić w pierwszej kolejności, co testujemy, jak mierzymy efekty.</p>
        <span class="hr-step-when">Tydzień 1</span>
      </article>
      <article class="hr-step" data-hr-anim data-hr-delay="2">
        <h3>Wdrożenie</h3>
        <p>Tworzę kampanie, copy, landing pages, integracje. Ty nie czekasz tygodniami na decyzje.</p>
        <span class="hr-step-when">Tydz. 2-4</span>
      </article>
      <article class="hr-step" data-hr-anim data-hr-delay="3">
        <h3>Optymalizacja</h3>
        <p>Kampanie są stale analizowane i optymalizowane na podstawie jakości zapytań oraz realnych wyników sprzedażowych, nie tylko statystyk typu kliknięcia czy zasięg.</p>
        <span class="hr-step-when">Stale</span>
      </article>
    </div>

    <!-- Wewnetrzny CTA prowadzacy do FAQ -->
    <div class="hr-step-cta" data-hr-anim>
      <p>Zastanawiasz się, czy to działa u Ciebie? <a href="#faq">Sprawdź najczęstsze pytania →</a></p>
    </div>
  </div>
</section>


<!-- ====================================================================
     SEKCJA 10 — FAQ (schema.org FAQPage)
     ==================================================================== -->
<section class="hr-section" id="faq" aria-labelledby="hr-faq-h2">
  <div class="hr-wrap">
    <header class="hr-sec-head" data-hr-anim>
      <span class="hr-eyebrow">FAQ</span>
      <h2 id="hr-faq-h2">Najczęstsze pytania przed rozmową</h2>
      <p>Bez owijania w bawełnę. Jeśli masz inne pytanie — zadaj je w formularzu poniżej.</p>
    </header>

    <div class="hr-faq" data-hr-anim>
      <?php foreach ($home_faqs as $faq) : ?>
      <details class="hr-faq-it">
        <summary>
          <span><?php echo esc_html((string) $faq["q"]); ?></span>
          <span class="hr-faq-icn" aria-hidden="true">+</span>
        </summary>
        <?php
        $faq_answer = trim((string) $faq["a"]);
        $faq_paragraphs = $faq_answer !== "" ? preg_split('/\n{2,}/', $faq_answer, -1, PREG_SPLIT_NO_EMPTY) : [];
        foreach ($faq_paragraphs as $faq_p) :
            ?>
        <p><?php echo esc_html(trim((string) $faq_p)); ?></p>
            <?php
        endforeach;
        ?>
      </details>
      <?php endforeach; ?>
    </div>

    <!-- Wewnetrzny CTA prowadzacy do kontaktu (final step lejka) -->
    <div class="hr-step-cta" data-hr-anim>
      <p>Masz inne pytanie? <a href="#kontakt">Zadaj je w 2-minutowym formularzu →</a></p>
    </div>
  </div>
</section>


<!-- ====================================================================
     SEKCJA 09 — KONTAKT (lead_form_origin = "home-contact-form")
     ==================================================================== -->
<section class="hr-kontakt" id="kontakt" aria-labelledby="hr-kontakt-h2">
  <div class="hr-wrap">
    <div class="hr-kontakt-grid">
      <div data-hr-anim>
        <span class="hr-eyebrow">Bezpłatna 30-min diagnoza</span>
        <h2 id="hr-kontakt-h2">Pokaż link do strony i opisz w 2 zdaniach, z czym dziś masz największy problem lub co chcesz poprawić.</h2>
        <p>
          W ciągu 24 godzin roboczych odezwę się z propozycją terminu. Bez prezentacji i sprzedażowych slajdów, 30 minut konkretnej analizy Twojej sytuacji.
        </p>

        <!-- Co konkretnie omówimy — wydluza lewa kolumne do wysokosci formularza -->
        <div style="margin-bottom:22px">
          <strong style="display:block;font-family:var(--hr-fd);font-weight:800;font-size:13px;letter-spacing:.5px;text-transform:uppercase;color:var(--hr-ink);margin-bottom:12px">Co konkretnie omówimy</strong>
          <ul class="hr-kontakt-info-list">
            <li>gdzie dziś najczęściej uciekają potencjalni klienci,</li>
            <li>który kanał ma największy potencjał w Twojej sytuacji,</li>
            <li>jak powinien wyglądać rozsądny budżet startowy,</li>
            <li>co warto poprawić samodzielnie, a co lepiej wdrożyć profesjonalnie.</li>
          </ul>
        </div>

        <div style="margin-bottom:22px">
          <strong style="display:block;font-family:var(--hr-fd);font-weight:800;font-size:13px;letter-spacing:.5px;text-transform:uppercase;color:var(--hr-ink);margin-bottom:12px">Jak wygląda rozmowa</strong>
          <ul class="hr-kontakt-info-list">
            <li>bezpłatnie i bez zobowiązań,</li>
            <li>konkretne wnioski nawet jeśli nie zaczniemy współpracy,</li>
            <li>bez nachalnej sprzedaży i wielotygodniowych follow-upów.</li>
          </ul>
        </div>

        <div class="hr-kontakt-direct">
          <strong>Albo bezpośrednio</strong>
          <div>Tel: <a href="tel:<?php echo esc_attr($contact_phone_href); ?>"><?php echo esc_html($contact_phone); ?></a></div>
          <div>Email: <a href="mailto:<?php echo esc_attr($contact_email); ?>"><?php echo esc_html($contact_email); ?></a></div>
          <div style="margin-top:14px">LinkedIn: <a href="<?php echo esc_url($linkedin_url); ?>" target="_blank" rel="noopener noreferrer">Sebastian Kelm</a></div>
        </div>
      </div>

      <div class="hr-form-card" data-hr-anim data-hr-delay="1">
        <span class="hr-form-tag">&#9658; Wypełnij tu</span>
        <h2>Umów bezpłatną diagnozę</h2>
        <p class="hr-form-sub">2 minuty. Odpowiadam osobiście w 24h roboczych.</p>
        <?php
        if (function_exists("upsellio_render_lead_form")) {
            echo upsellio_render_lead_form([
                "origin" => "home-contact-form",
                "submit_label" => "Wyślij — odpiszę w 24h \xE2\x86\x92",
                "variant" => "full",
                "heading" => "",
                "subheading" => "",
                "redirect_url" => $site_url . "#kontakt",
                "service_options" => $service_options,
                "css_class" => "hr-form",
                "form_id" => "home-contact-form",
            ]);
        } else {
            echo '<p>Napisz na <a href="mailto:' . esc_attr($contact_email) . '">' . esc_html($contact_email) . '</a></p>';
        }
        ?>
      </div>
    </div>
  </div>
</section>

</main>


<!-- ====================================================================
     SCHEMA.ORG @graph — SEO struktura
     Organization + LocalBusiness, Service x3, FAQPage, BreadcrumbList, WebSite
     ==================================================================== -->
<script type="application/ld+json">
<?php
echo wp_json_encode([
    "@context" => "https://schema.org",
    "@graph" => [
        [
            "@type" => ["Organization", "LocalBusiness"],
            "@id" => $site_url . "#organization",
            "name" => "Upsellio",
            "alternateName" => "Upsellio — Marketing B2B",
            "url" => $site_url,
            "logo" => [
                "@type" => "ImageObject",
                "url" => function_exists("upsellio_get_default_og_image_url") ? upsellio_get_default_og_image_url() : ($site_url . "wp-content/themes/upsellio/assets/images/upsellio-logo.png"),
            ],
            "founder" => [
                "@type" => "Person",
                "name" => "Sebastian Kelm",
                "jobTitle" => "Specjalista marketingu B2B",
                "sameAs" => [$linkedin_url],
            ],
            "telephone" => $contact_phone,
            "email" => $contact_email,
            "areaServed" => "PL",
            "address" => [
                "@type" => "PostalAddress",
                "addressCountry" => "PL",
            ],
            "sameAs" => [$linkedin_url],
            "description" => $seo_description,
            "priceRange" => "1800-15000 PLN",
        ],
        [
            "@type" => "Service",
            "@id" => $site_url . "#service-google-ads",
            "name" => "Prowadzenie kampanii Google Ads dla firm B2B",
            "provider" => ["@id" => $site_url . "#organization"],
            "url" => $google_ads_url,
            "description" => "Konfiguracja, prowadzenie i optymalizacja kampanii Google Ads (Search, Performance Max, Shopping, remarketing) dla firm B2B. Z naciskiem na jakość leadów, nie tylko CTR.",
            "areaServed" => "PL",
            "offers" => [
                "@type" => "Offer",
                "price" => "1800",
                "priceCurrency" => "PLN",
                "priceSpecification" => [
                    "@type" => "UnitPriceSpecification",
                    "price" => "1800",
                    "priceCurrency" => "PLN",
                    "unitText" => "MONTH",
                ],
            ],
        ],
        [
            "@type" => "Service",
            "@id" => $site_url . "#service-meta-ads",
            "name" => "Prowadzenie kampanii Meta Ads (Facebook + Instagram)",
            "provider" => ["@id" => $site_url . "#organization"],
            "url" => $meta_ads_url,
            "description" => "Kampanie Conversions, Lead Ads, Catalog Sales na Facebook i Instagram. Pixel + Conversions API. Idealne dla firm, które potrzebują budować popyt i robić remarketing.",
            "areaServed" => "PL",
            "offers" => [
                "@type" => "Offer",
                "price" => "1800",
                "priceCurrency" => "PLN",
                "priceSpecification" => [
                    "@type" => "UnitPriceSpecification",
                    "price" => "1800",
                    "priceCurrency" => "PLN",
                    "unitText" => "MONTH",
                ],
            ],
        ],
        [
            "@type" => "Service",
            "@id" => $site_url . "#service-www",
            "name" => "Tworzenie stron internetowych dla firm B2B",
            "provider" => ["@id" => $site_url . "#organization"],
            "url" => $websites_url,
            "description" => "Strony firmowe B2B, landing pages pod kampanie reklamowe, sklepy WooCommerce. Z copywritingiem, SEO podstawowym i panelem do edycji w cenie projektu.",
            "areaServed" => "PL",
            "offers" => [
                "@type" => "Offer",
                "price" => "4000",
                "priceCurrency" => "PLN",
            ],
        ],
        [
            "@type" => "FAQPage",
            "@id" => $site_url . "#faq",
            "mainEntity" => array_map(static function ($faq) {
                return [
                    "@type" => "Question",
                    "name" => (string) $faq["q"],
                    "acceptedAnswer" => [
                        "@type" => "Answer",
                        "text" => (string) $faq["a"],
                    ],
                ];
            }, $home_faqs),
        ],
        [
            "@type" => "BreadcrumbList",
            "itemListElement" => [
                [
                    "@type" => "ListItem",
                    "position" => 1,
                    "name" => "Strona główna",
                    "item" => $site_url,
                ],
            ],
        ],
        [
            "@type" => "WebSite",
            "@id" => $site_url . "#website",
            "url" => $site_url,
            "name" => "Upsellio",
            "publisher" => ["@id" => $site_url . "#organization"],
            "inLanguage" => "pl-PL",
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
</script>


<script>
(function(){
  /* === Animacje on-scroll === */
  if('IntersectionObserver' in window){
    var io=new IntersectionObserver(function(es){
      es.forEach(function(e){if(e.isIntersecting){e.target.classList.add('is-visible');io.unobserve(e.target);}});
    },{rootMargin:'0px 0px -8% 0px',threshold:0.08});
    document.querySelectorAll('[data-hr-anim]').forEach(function(el){io.observe(el);});
  } else {
    document.querySelectorAll('[data-hr-anim]').forEach(function(el){el.classList.add('is-visible');});
  }

  /* === Smooth scroll z offsetem === */
  document.querySelectorAll('a[href^="#"]').forEach(function(link){
    link.addEventListener('click',function(e){
      var href=link.getAttribute('href');
      if(href.length<2)return;
      var target=document.querySelector(href);
      if(target){
        e.preventDefault();
        var rect=target.getBoundingClientRect();
        var y=rect.top+window.scrollY-80;
        window.scrollTo({top:y,behavior:'smooth'});
      }
    });
  });

  /* === dataLayer push przy submit (osobny dla hero i contact) === */
  ['home-hero-form','home-contact-form'].forEach(function(formId){
    var form=document.getElementById(formId);
    if(form){
      form.addEventListener('submit',function(){
        if(typeof window.dataLayer!=='undefined'){
          window.dataLayer.push({
            event:'lead_form_submit',
            form_origin:formId,
            form_type:'free_consultation',
            page_section:'home_page'
          });
        }
      });
    }
  });
})();
</script>

<?php
get_footer();
?>
