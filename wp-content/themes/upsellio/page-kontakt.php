<?php
/*
Template Name: Upsellio - Kontakt v5 (Core)
Template Post Type: page
*/
if (!defined("ABSPATH")) {
    exit;
}

get_header();

/* ============================================================
   USTAWIENIA — pobierane z systemu motywu Upsellio
   Te same helpery co w oryginalnym page-kontakt.php.
   ============================================================ */

$front_page_sections = function_exists("upsellio_get_front_page_content_config")
    ? upsellio_get_front_page_content_config()
    : [];
$contact_phone = function_exists("upsellio_get_contact_phone")
    ? upsellio_get_contact_phone()
    : trim((string) ($front_page_sections["contact_phone"] ?? "+48 575 522 595"));
$contact_phone_href = preg_replace("/\s+/", "", (string) $contact_phone);
$contact_email = trim((string) ($front_page_sections["contact_email"] ?? "kontakt@upsellio.pl"));
$contact_page_url = home_url("/kontakt/");
$linkedin_url = "https://www.linkedin.com/in/kelm-sebastian/";

$google_ads_url = function_exists("upsellio_get_google_ads_page_url") ? (string) upsellio_get_google_ads_page_url() : "";
$meta_ads_url = function_exists("upsellio_get_meta_ads_page_url") ? (string) upsellio_get_meta_ads_page_url() : "";
$websites_url = function_exists("upsellio_get_websites_page_url") ? (string) upsellio_get_websites_page_url() : "";

/* Opcje serwisow — uzywane w formularzu */
$contact_service_options = [
    "Kampanie Google Ads",
    "Kampanie Meta Ads",
    "Tworzenie strony lub landing page",
    "Pakiet kompletny (reklama + strona + lejek)",
    "Audyt istniejących kampanii lub strony",
    "Nie wiem — chcę porozmawiać o problemie",
];

/* FAQ — z nowym pytaniem "Co jesli nie chce pracowac po rozmowie" */
$contact_faq_items = [
    [
        "question" => "Czy pierwsza diagnoza i rozmowa są płatne?",
        "answer" => "Nie. Pierwsza rozmowa i wstępna ocena sytuacji są bezpłatne. Jeśli temat wymaga głębszego audytu z dokumentacją, ustalamy osobny zakres. Ale diagnoza „gdzie i dlaczego tracisz zapytania” — to zawsze bezpłatnie.",
    ],
    [
        "question" => "Skąd mam wiedzieć, że to nie kolejna agencja, która sprzeda mi raporty?",
        "answer" => "Najuczciwsza odpowiedź: w pierwszej rozmowie. 30 minut, bez prezentacji. Jeśli po niej masz wrażenie że gadałeś z kolejnym agencyjnym handlowcem — nie zatrudniaj mnie. Jeśli wyszło z tego 5 konkretnych rzeczy do poprawy w Twoim marketingu, których nikt wcześniej nie wskazał, masz odpowiedź. Pierwsze 30 minut to nie sprzedaż, to próbka pracy.",
    ],
    [
        "question" => "Co jeśli po rozmowie zorientuję się, że nie chcę z Tobą pracować?",
        "answer" => "Idealna sytuacja. 30 minut które kosztowały Cię 0 zł zamieniły się w mapę 3-5 rzeczy do poprawy które możesz wdrożyć sam, ze swoją obecną agencją albo z innym wykonawcą. Nie ścigam się, nie wysyłam follow-upów typu „pamiętasz o naszej rozmowie”, nie zapraszam na webinar. Decyzja należy do Ciebie i tyle.",
    ],
    [
        "question" => "Czy muszę mieć gotowy brief lub szczegółowy opis projektu?",
        "answer" => "Nie. Wystarczy krótki opis sytuacji: co robisz, co nie działa tak jak powinno i jaki efekt chcesz osiągnąć. Pytania uzupełniające zadaję sam podczas rozmowy.",
    ],
    [
        "question" => "Czy mogę napisać, jeśli nie wiem, czy problem leży w reklamach czy na stronie?",
        "answer" => "Tak — i to jest bardzo częsta sytuacja. Patrzę na cały lejek: źródło ruchu, komunikat reklamowy, stronę docelową, formularz i dalszy kontakt z leadem. Pomogę zlokalizować, gdzie jest faktyczny problem.",
    ],
    [
        "question" => "Czy od razu dostanę ofertę cenową?",
        "answer" => "Nie. Pierwszy mail po rozmowie to nie oferta — to spisana mapa problemów które omówiliśmy, plus rekomendacja co zrobić w pierwszej kolejności (z Tobą lub beze mnie). Wycenę przygotowuję dopiero gdy ustalimy że ma sens współpraca i wiem dokładnie nad czym mam pracować. To oszczędza Twój i mój czas.",
    ],
    [
        "question" => "Dla jakich firm ta współpraca ma największy sens?",
        "answer" => "Dla firm B2B (usługi, produkcja, IT, SaaS), e-commerce B2B i firm usługowych, które mają już stronę lub kampanie reklamowe, ale nie są zadowolone z jakości zapytań lub sprzedaży. Minimalna wielkość firmy: 3-5 osób. Minimalny budżet reklamowy do pełnej współpracy: 2 000-3 000 zł/mies. na platformę.",
    ],
    [
        "question" => "Ile trwa odpowiedź na zgłoszenie?",
        "answer" => "Odpowiadam w ciągu 24 godzin w dni robocze. Zwykle szybciej. Jeśli sprawa jest pilna, napisz to w formularzu — priorytetuję takie zgłoszenia.",
    ],
];
?>

<style>
/* ============================================================
   STRONA KONTAKT UPSELLIO v4
   Design: jasny minimal, delikatne turkusowe akcenty
   Fonty: Bricolage Grotesque (display, ostrzejsze wagi) + DM Sans (body)
   Paleta: zgodna ze strona glowna v4 - bardzo jasne tla, czysty turkus jako jedyny akcent
   Prefiks: .ct-
   ============================================================ */

:root{
  --ct-ink:#0f1115;
  --ct-ink2:#3a3d44;
  --ct-muted:#6e727b;
  --ct-faint:#9ea2aa;
  --ct-bg:#fbfbfd;
  --ct-surface:#ffffff;
  --ct-soft:#f4f5f7;
  --ct-softer:#eef0f3;
  --ct-border:#e6e8ec;
  --ct-border2:#d8dde3;
  --ct-line:#eef0f3;
  --ct-teal:#0bb39c;
  --ct-teal2:#08a892;
  --ct-tealh:#089a86;
  --ct-teald:#06745f;
  --ct-tealx:#3dd8c3;
  --ct-teals:#e6fbf6;
  --ct-tealss:#f3fdfa;
  --ct-tealb:#d0f7ed;
  --ct-dark:#0a0e14;
  --ct-r:10px;
  --ct-rl:16px;
  --ct-rxl:22px;
  --ct-rxxl:28px;
  --ct-fd:'Bricolage Grotesque','Syne',sans-serif;
  --ct-fb:'DM Sans',-apple-system,sans-serif;
}

.ct-art{
  font-family:var(--ct-fb);
  background:var(--ct-bg);
  color:var(--ct-ink);
  line-height:1.6;
  -webkit-font-smoothing:antialiased;
  font-feature-settings:"ss01","cv01";
}
.ct-art *,.ct-art *::before,.ct-art *::after{box-sizing:border-box}
.ct-art a{color:inherit;text-decoration:none}
.ct-wrap{max-width:1180px;margin:0 auto;padding:0 24px}

/* === EYEBROW (identyczny pattern jak hero v4) === */
.ct-eyebrow{
  display:inline-flex;align-items:center;gap:10px;margin-bottom:20px;
  color:var(--ct-tealh);font-size:12px;font-weight:700;letter-spacing:.04em;
  text-transform:uppercase;font-feature-settings:"ss01"
}
.ct-eyebrow::before{content:"";width:20px;height:1px;background:var(--ct-teal)}

/* === HERO + FORM === */
.ct-hero-form{
  padding:64px 0 96px;
  background:var(--ct-bg);
  position:relative;
  overflow:hidden;
}
.ct-hero-form::before{
  content:"";position:absolute;top:-200px;right:-300px;width:700px;height:700px;
  border-radius:50%;background:radial-gradient(circle,rgba(11,179,156,.08),transparent 60%);
  pointer-events:none
}
.ct-hero-form-grid{
  display:grid;grid-template-columns:1.1fr 1fr;gap:64px;align-items:stretch;
  position:relative;
}
.ct-hero-copy{
  position:relative;
  display:flex;flex-direction:column;
}
.ct-hero-copy > .ct-eyebrow{margin-bottom:14px}
.ct-h1{
  font-family:var(--ct-fd);font-weight:800;
  font-size:clamp(32px,3.8vw,46px);line-height:1.06;letter-spacing:-.03em;
  margin:0 0 16px;color:var(--ct-ink);
}
.ct-h1-accent{color:var(--ct-teal);font-style:normal;font-weight:800}
.ct-lead{
  font-size:17px;line-height:1.55;color:var(--ct-ink2);
  max-width:48ch;margin:0 0 24px;font-weight:400;
}

/* === Channels (alternatywne kanaly kontaktu) === */
.ct-channels{
  display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;
  margin-top:auto;padding-top:24px;border-top:1px solid var(--ct-line);
}
a.ct-channel-link,.ct-art a.ct-channel-link{
  display:flex;align-items:center;gap:10px;
  padding:14px 16px;
  background:var(--ct-surface);
  border:1px solid var(--ct-border);
  border-radius:var(--ct-r);
  color:var(--ct-ink);font-size:13.5px;font-weight:600;
  text-decoration:none;transition:border-color .2s;
}
.ct-channel-link:hover{border-color:var(--ct-teal);color:var(--ct-tealh)}
.ct-channel-link strong{
  display:block;font-family:var(--ct-fd);font-weight:700;
  font-size:11px;letter-spacing:.04em;text-transform:uppercase;
  color:var(--ct-muted);margin-bottom:2px;
}

/* === FORMULARZ === */
.ct-form-card{
  background:var(--ct-surface);
  border:1px solid var(--ct-border);
  border-radius:var(--ct-rxl);
  padding:32px;
  box-shadow:0 1px 0 rgba(15,17,21,.04),0 4px 12px rgba(15,17,21,.04);
  height:100%;
}
.ct-form-head{margin-bottom:22px}
.ct-form-head .ct-eyebrow{margin-bottom:10px}
.ct-form-head h2{
  margin:0 0 8px;font-family:var(--ct-fd);font-size:26px;font-weight:800;
  line-height:1.04;letter-spacing:-.025em;color:var(--ct-ink);
}
.ct-form-head p{
  margin:0;color:var(--ct-muted);font-size:14px;line-height:1.55;
}
.ct-example-box{
  margin:18px 0 22px;padding:16px;
  background:var(--ct-tealss);border:1px solid var(--ct-tealb);
  border-radius:var(--ct-r);
  font-size:13px;color:var(--ct-ink2);line-height:1.55;
}
.ct-example-box strong{
  display:block;font-family:var(--ct-fd);font-weight:700;
  font-size:11px;letter-spacing:.05em;text-transform:uppercase;
  color:var(--ct-tealh);margin-bottom:6px;font-feature-settings:"ss01";
}
.ct-form-after{
  margin-top:18px;padding:14px 16px;
  background:var(--ct-soft);border-radius:var(--ct-rl);
  font-size:12.5px;color:var(--ct-muted);line-height:1.55;
}
.ct-form-after strong{
  color:var(--ct-ink);display:block;margin-bottom:4px;
  font-family:var(--ct-fd);font-weight:700;font-size:13px;
}

/* Pola formularza */
.ct-form-card .ups-form input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),
.ct-form-card .ups-form select,
.ct-form-card .ups-form textarea{
  width:100%;min-height:46px;background:var(--ct-surface);
  border:1.5px solid var(--ct-border);border-radius:var(--ct-r);
  padding:11px 14px;color:var(--ct-ink);font-size:14.5px;font-family:inherit;
  transition:border-color .15s,box-shadow .15s;
}
.ct-form-card .ups-form textarea{min-height:96px;resize:vertical;line-height:1.55}
.ct-form-card .ups-form input:focus,
.ct-form-card .ups-form select:focus,
.ct-form-card .ups-form textarea:focus{
  border-color:var(--ct-teal);
  box-shadow:0 0 0 3px rgba(11,179,156,.12);outline:none;
}
.ct-form-card .ups-form__label{
  font-family:var(--ct-fb);font-weight:600;font-size:13.5px;
  color:var(--ct-ink);margin-bottom:6px;display:block;letter-spacing:-.005em;
}
.ct-form-card .ups-form__consent{
  display:flex;align-items:flex-start;gap:10px;
  font-size:12.5px;color:var(--ct-muted);line-height:1.5;
}
.ct-form-card .ups-form__consent input[type="checkbox"]{
  width:18px;height:18px;min-height:18px;margin:2px 0 0;
  flex-shrink:0;accent-color:var(--ct-teal);
}
.ct-form-card .ups-form__submit,
.ct-form-card button[type="submit"]{
  width:100%;min-height:52px;border:0;border-radius:99px;
  background:var(--ct-ink);color:#fff;
  font-family:var(--ct-fb);font-size:15px;font-weight:700;
  cursor:pointer;margin-top:8px;
  transition:background .15s,transform .15s;letter-spacing:-.01em;
}
.ct-form-card .ups-form__submit:hover{
  background:var(--ct-teald);transform:translateY(-1px);
}

/* === UKRYCIE POL FORMULARZA - Firma + Twoja miesieczna sprzedaz === */
/* Keep required name and telephone visible; shared form no longer has a company field. */
.ct-form-card .ups-form label[for*="monthly-sales"],
.ct-form-card .ups-form select[name="lead_monthly_sales"]{display:none !important}

/* === SECTIONS GENERIC === */
.ct-section-head{max-width:760px;margin-bottom:48px}
.ct-section-head h2{
  margin:0;font-family:var(--ct-fd);font-weight:800;
  font-size:clamp(32px,3.8vw,48px);line-height:1.02;letter-spacing:-.035em;
  color:var(--ct-ink);
}
.ct-section-head p{
  margin:14px 0 0;max-width:62ch;color:var(--ct-muted);font-size:17px;line-height:1.6;
}

/* === O CO ZAPYTAC (topics) === */
.ct-topics{padding:96px 0;background:var(--ct-soft)}
.ct-topic-grid{
  display:grid;grid-template-columns:repeat(2,1fr);gap:14px;
}
a.ct-topic-card,.ct-art a.ct-topic-card,.ct-topic-card{
  background:var(--ct-surface);
  border:1px solid var(--ct-border);
  border-radius:var(--ct-rxl);
  padding:32px;
  transition:border-color .2s,transform .2s,box-shadow .2s;
  text-decoration:none;color:var(--ct-ink);
  display:flex;flex-direction:column;
}
.ct-topic-card:hover{border-color:var(--ct-ink);transform:translateY(-3px)}
.ct-topic-icon{
  width:38px;height:38px;border-radius:10px;
  background:var(--ct-tealss);color:var(--ct-tealh);
  display:grid;place-items:center;flex-shrink:0;
  margin-bottom:14px;border:1px solid var(--ct-tealb);font-size:18px;
}
.ct-topic-symptom{
  font-family:var(--ct-fd);font-weight:700;
  font-size:11px;color:var(--ct-tealh);letter-spacing:.06em;
  text-transform:uppercase;margin-bottom:8px;font-feature-settings:"ss01";
}
.ct-topic-card h3{
  margin:0 0 10px;font-family:var(--ct-fd);font-weight:800;
  font-size:19px;line-height:1.2;letter-spacing:-.02em;color:var(--ct-ink);
}
.ct-topic-card p{
  margin:0 0 16px;color:var(--ct-muted);font-size:14.5px;line-height:1.6;flex:1;
}
.ct-topic-link,.ct-art a .ct-topic-link{
  display:inline-flex;align-items:center;gap:6px;align-self:flex-start;
  font-size:14px;font-weight:700;color:var(--ct-ink) !important;
  border-bottom:1.5px solid var(--ct-teal);padding-bottom:2px;
  letter-spacing:-.005em;
}
.ct-topic-card:hover .ct-topic-link{color:var(--ct-tealh);gap:10px}

/* === OPINIE / TESTIMONIALS === */
.ct-opinions{background:var(--ct-bg);border-top:1px solid var(--ct-line)}
.ct-testimonial-grid{
  display:grid;grid-template-columns:1fr 1fr;gap:16px;
}
.ct-testimonial{
  background:var(--ct-surface);border:1px solid var(--ct-border);
  border-radius:var(--ct-rxl);padding:32px;
}
.ct-testimonial-quote{
  margin:0 0 20px;color:var(--ct-ink);font-size:15.5px;line-height:1.65;
  font-weight:400;
}
.ct-testimonial-author{
  display:flex;align-items:center;gap:14px;
  padding-top:18px;border-top:1px solid var(--ct-line);
}
.ct-testimonial-avatar,
.ct-testimonial-placeholder{
  width:44px;height:44px;border-radius:50%;
  background:var(--ct-teals);color:var(--ct-tealh);
  display:grid;place-items:center;
  font-family:var(--ct-fd);font-weight:800;font-size:16px;
  border:1px solid var(--ct-tealb);flex-shrink:0;
}
.ct-testimonial-name{
  font-family:var(--ct-fd);font-weight:800;font-size:14px;color:var(--ct-ink);
  letter-spacing:-.01em;
}
.ct-testimonial-role{
  font-size:12.5px;color:var(--ct-muted);margin-top:2px;
}

/* === PROCES === */
.ct-process{padding:96px 0;background:var(--ct-soft)}
.ct-process-grid{
  display:grid;grid-template-columns:repeat(3,1fr);gap:18px;counter-reset:ctstep;
}
.ct-step-card{
  background:var(--ct-surface);border:1px solid var(--ct-border);
  border-radius:var(--ct-rxl);padding:32px;position:relative;
  display:flex;flex-direction:column;gap:14px;
  box-shadow:0 1px 0 rgba(15,17,21,.04);
}
.ct-step-num{
  font-family:var(--ct-fd);font-size:13px;font-weight:800;
  color:var(--ct-tealh);line-height:1;letter-spacing:.08em;
  font-feature-settings:"ss01";text-transform:uppercase;
  display:flex;align-items:center;gap:10px;
}
.ct-step-num::before{content:"";width:24px;height:1px;background:var(--ct-teal)}
.ct-step-card h3{
  margin:0;font-family:var(--ct-fd);font-weight:800;
  font-size:20px;line-height:1.18;letter-spacing:-.025em;color:var(--ct-ink);
}
.ct-step-card p{
  margin:0;color:var(--ct-muted);font-size:14.5px;line-height:1.6;
}
.ct-step-list{
  list-style:none;padding:0;margin:0;display:grid;gap:8px;
  padding-top:14px;border-top:1px solid var(--ct-line);
}
.ct-step-list li{
  display:flex;align-items:flex-start;gap:10px;
  font-size:13.5px;color:var(--ct-ink2);line-height:1.5;
}
.ct-step-list li::before{
  content:"";flex:0 0 6px;width:6px;height:6px;border-radius:50%;
  background:var(--ct-teal);margin-top:7px;
}
.ct-step-timing{
  display:inline-flex;align-items:center;gap:6px;
  align-self:flex-start;
  padding:5px 11px;
  background:var(--ct-tealss);color:var(--ct-tealh);
  border:1px solid var(--ct-tealb);border-radius:99px;
  font-size:11px;font-weight:700;letter-spacing:.04em;
  font-feature-settings:"ss01";margin-top:auto;
}

/* === FAQ === */
.ct-faq{padding:96px 0;background:var(--ct-bg);border-top:1px solid var(--ct-line)}
.ct-faq-grid{display:grid;gap:8px;max-width:880px}
.ct-faq-item{
  background:var(--ct-surface);border:1px solid var(--ct-border);
  border-radius:var(--ct-rl);transition:all .15s;
}
.ct-faq-item[open]{border-color:var(--ct-ink)}
.ct-faq-item summary{
  list-style:none;cursor:pointer;
  display:flex;justify-content:space-between;align-items:center;gap:18px;
  padding:22px 26px;
  font-family:var(--ct-fd);font-size:17px;font-weight:700;
  color:var(--ct-ink);letter-spacing:-.015em;
}
.ct-faq-item summary::-webkit-details-marker{display:none}
.ct-faq-icon{
  width:26px;height:26px;border-radius:50%;
  background:var(--ct-soft);display:grid;place-items:center;
  font-size:18px;color:var(--ct-muted);flex:0 0 26px;transition:all .2s;
}
.ct-faq-item[open] .ct-faq-icon{
  transform:rotate(45deg);background:var(--ct-tealss);color:var(--ct-tealh);
}
.ct-faq-item p{
  margin:0;padding:0 26px 24px;color:var(--ct-muted);font-size:15px;line-height:1.65;
}
.ct-faq-cta-row{
  margin-top:32px;text-align:center;padding-top:32px;
  border-top:1px solid var(--ct-line);
  color:var(--ct-muted);font-size:15px;
}
.ct-faq-cta-row a{
  color:var(--ct-ink);font-weight:700;text-decoration:none;
  border-bottom:2px solid var(--ct-teal);padding-bottom:2px;
}
.ct-faq-cta-row a:hover{color:var(--ct-tealh)}

/* === FINAL CTA === */
.ct-final{
  padding:96px 0 110px;background:var(--ct-bg);
  border-top:1px solid var(--ct-line);
}
.ct-final-box{
  background:var(--ct-surface);
  border:1px solid var(--ct-border);
  border-radius:var(--ct-rxxl);
  padding:64px;
  position:relative;overflow:hidden;
  text-align:left;
  display:grid;
  grid-template-columns:1.3fr 1fr;
  gap:48px;
  align-items:center;
  box-shadow:0 1px 0 rgba(15,17,21,.04),0 8px 24px rgba(15,17,21,.04);
}
.ct-final-box::before{
  content:"";position:absolute;
  top:0;left:0;bottom:0;width:4px;
  background:var(--ct-teal);
}
.ct-final-eyebrow{
  display:inline-flex;align-items:center;gap:10px;
  margin-bottom:16px;color:var(--ct-tealh);
  font-size:12px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;
  font-feature-settings:"ss01";position:relative;
}
.ct-final-eyebrow::before{content:"";width:20px;height:1px;background:var(--ct-teal)}
.ct-final-box h2{
  font-family:var(--ct-fd);font-weight:800;
  font-size:clamp(30px,3.6vw,42px);line-height:1.02;letter-spacing:-.03em;
  color:var(--ct-ink);margin:0 0 16px;position:relative;
}
.ct-final-box p{
  margin:0 0 24px;color:var(--ct-muted);
  font-size:16px;line-height:1.6;max-width:54ch;position:relative;
}
.ct-final-side{
  display:flex;flex-direction:column;gap:16px;
  padding-left:32px;border-left:1px solid var(--ct-line);
}
.ct-final-side strong{
  display:block;font-family:var(--ct-fd);font-weight:700;
  font-size:11px;letter-spacing:.05em;text-transform:uppercase;
  color:var(--ct-muted);margin-bottom:4px;font-feature-settings:"ss01";
}
.ct-final-side a{
  font-family:var(--ct-fd);font-weight:800;font-size:18px;
  color:var(--ct-ink);text-decoration:none;letter-spacing:-.01em;
  border-bottom:1.5px solid var(--ct-teal);padding-bottom:1px;
  align-self:flex-start;
}
.ct-final-side a:hover{color:var(--ct-tealh)}
a.ct-final-btn,.ct-art a.ct-final-btn{
  display:inline-flex;align-items:center;justify-content:center;gap:10px;
  padding:16px 32px;
  background:var(--ct-ink);color:#fff;
  border-radius:99px;font-family:var(--ct-fb);font-weight:700;
  font-size:15px;text-decoration:none;letter-spacing:-.01em;
  transition:transform .15s,background .15s;position:relative;
  align-self:flex-start;
}
a.ct-final-btn:hover,.ct-art a.ct-final-btn:hover{transform:translateY(-1px);background:var(--ct-teald);color:#fff}

/* Mobile - Final CTA stacked */
@media (max-width:1024px){
  .ct-final-box{grid-template-columns:1fr;gap:32px;padding:48px 32px}
  .ct-final-side{padding-left:0;padding-top:32px;border-left:0;border-top:1px solid var(--ct-line)}
}

/* === H utility (helper for old class) === */
.ct-h{
  font-family:var(--ct-fd);font-weight:800;
  font-size:clamp(28px,3.4vw,42px);line-height:1.04;letter-spacing:-.03em;
  color:var(--ct-ink);
}

/* === Proof/cells - jakies pomocnicze === */
.ct-proof-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
.ct-proof-cell{
  background:var(--ct-surface);border:1px solid var(--ct-border);
  border-radius:var(--ct-rl);padding:14px 12px;
  font-size:12.5px;color:var(--ct-muted);line-height:1.4;
}
.ct-proof-cell strong{
  display:block;font-family:var(--ct-fd);font-weight:800;
  color:var(--ct-ink);font-size:18px;margin-bottom:4px;letter-spacing:-.02em;
}

/* === BUTTONS - generic === */
.ct-art a.ct-btn-d{
  display:inline-flex;align-items:center;justify-content:center;gap:8px;
  padding:14px 28px;background:var(--ct-ink);color:#fff;
  border-radius:99px;font-family:var(--ct-fb);font-weight:700;font-size:14.5px;
  text-decoration:none;letter-spacing:-.01em;
  transition:background .15s,transform .15s;
}
.ct-art a.ct-btn-d:hover{background:var(--ct-teald);transform:translateY(-1px);color:#fff}

/* === ANIMACJE === */
[data-animate]{opacity:0;transition:opacity .7s ease,transform .7s ease}
[data-animate="fade-up"]{transform:translateY(16px)}
[data-animate="fade-right"]{transform:translateX(-16px)}
[data-animate].is-visible{opacity:1;transform:none}
@media (prefers-reduced-motion:reduce){[data-animate]{opacity:1;transform:none;transition:none}}

/* === RESPONSIVE === */
@media (max-width:1024px){
  .ct-hero-form-grid{grid-template-columns:1fr;gap:48px}
  .ct-form-card{height:auto}
  .ct-proof-grid{grid-template-columns:repeat(2,1fr);margin-top:8px}
  .ct-channels{grid-template-columns:1fr 1fr 1fr;margin-top:24px}
  .ct-topic-grid{grid-template-columns:1fr 1fr}
  .ct-testimonial-grid{grid-template-columns:1fr 1fr}
  .ct-process-grid{grid-template-columns:1fr 1fr}
  .ct-final-box{padding:48px 32px}
}
@media (max-width:720px){
  .ct-hero-form{padding:40px 0 64px}
  .ct-topics,.ct-process,.ct-faq,.ct-final{padding:64px 0}
  .ct-topic-grid,.ct-testimonial-grid,.ct-process-grid{grid-template-columns:1fr}
  .ct-channels{grid-template-columns:1fr}
  .ct-form-card{padding:24px;border-radius:var(--ct-rl)}
  .ct-final-box{padding:36px 24px;border-radius:var(--ct-rxl)}
}
/* Redesign — contact page. Scoped to ct-art; preserve lead renderer and fields. */
.ct-art{
  --ct-bg:#fff;--ct-soft:#f5f6f8;--ct-softer:#f5f6f8;--ct-muted:#5f636b;
  --ct-border:#e3e6ea;--ct-line:#e3e6ea;--ct-tealh:#06745f;--ct-tealss:#effaf7;
  --ct-teals:#dff5ef;--ct-tealb:#c7e6dd;--ct-r:12px;--ct-rl:20px;--ct-rxl:20px;--ct-rxxl:24px;
}
.ct-art .ct-wrap{max-width:1208px;padding-inline:24px}
.ct-art .ct-hero-form{background:#effaf7;padding:56px 0 96px}
.ct-art .ct-hero-form::before{display:none}
.ct-art .ct-hero-form-grid{grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:64px;align-items:start}
.ct-art .ct-h1{font-size:clamp(38px,4.4vw,54px);line-height:1.04;letter-spacing:-.035em;margin-bottom:20px}
.ct-art .ct-h1-accent{color:#06745f}
.ct-art .ct-lead{font-size:18px;line-height:1.65;max-width:56ch}
.ct-art .ct-eyebrow{color:#06745f;letter-spacing:.04em}
.ct-art .ct-eyebrow::before,.ct-art .ct-final-eyebrow::before{display:none}
.ct-art .ct-hero-copy>.ct-eyebrow{align-self:flex-start;border:1px solid #c7e6dd;border-radius:999px;background:#fff;padding:7px 14px;font-size:14px;letter-spacing:0;text-transform:none;margin-bottom:24px}
.ct-art .ct-hero-copy>.ct-eyebrow::before{display:block;width:8px;height:8px;border-radius:50%;background:#0bb39c}
.ct-art .ct-hero-steps{margin:24px 0 30px;padding:0;list-style:none;display:grid;gap:18px}
.ct-art .ct-hero-steps li{display:flex;gap:14px;align-items:flex-start}
.ct-art .ct-hero-steps .ct-hero-step-num{flex:0 0 36px;height:36px;display:grid;place-items:center;border-radius:50%;background:#dff5ef;color:#06745f;font-weight:800}
.ct-art .ct-hero-steps strong{display:block;font-size:17px;line-height:1.35;margin-bottom:4px}
.ct-art .ct-hero-steps p{margin:0;font-size:15px;color:#3a3d44}
.ct-art .ct-proof-grid{gap:10px}
.ct-art .ct-proof-cell{background:transparent;border:0;border-top:1px solid #c7e6dd;border-radius:0;padding:12px 0}
.ct-art .ct-proof-cell strong{color:#06745f}
.ct-art .ct-contact-person{display:flex;gap:14px;align-items:center;margin:28px 0 0;padding:20px 0 0;border-top:1px solid #c7e6dd}
.ct-art .ct-person-avatar{width:48px;height:48px;display:grid;place-items:center;border-radius:50%;background:#dff5ef;color:#06745f;font-family:var(--ct-fd);font-weight:800;flex:0 0 48px}
.ct-art .ct-contact-person strong{display:block}
.ct-art .ct-contact-person small{display:block;color:#5f636b}
.ct-art .ct-contact-person a{display:inline-flex;align-items:center;min-height:44px;color:#06745f;font-weight:700;margin-left:auto;white-space:nowrap}
.ct-art .ct-channels-section{padding:64px 0 32px;background:#fff}
.ct-art .ct-channels{grid-template-columns:repeat(3,minmax(0,1fr));margin:0;padding:0;border:0;gap:16px}
.ct-art a.ct-channel-link{border-radius:20px;padding:22px;min-height:96px;gap:14px;font-size:16px;min-width:0;overflow-wrap:anywhere}
.ct-art a.ct-channel-link strong{font-size:13px;letter-spacing:0;text-transform:none;color:#5f636b}
.ct-art .ct-channel-icon{display:grid;place-items:center;flex:0 0 44px;height:44px;border-radius:12px;background:#dff5ef;color:#06745f}
.ct-art .ct-channel-link:hover{border-color:#06745f;color:#06745f}
.ct-art .ct-agenda{padding:64px 0 96px}
.ct-art .ct-agenda-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:24px}
.ct-art .ct-agenda-card{display:flex;align-items:flex-start;gap:12px}
.ct-art .ct-agenda-card h3{font-family:var(--ct-fd);font-size:19px;line-height:1.25;margin:0 0 8px}
.ct-art .ct-agenda-card p{margin:0;color:#3a3d44;font-size:15px}
.ct-art .ct-form-card{height:auto;border-radius:24px;padding:32px;box-shadow:0 24px 48px rgba(15,17,21,.08);min-width:0}
.ct-art .ct-form-head h2{font-size:24px;line-height:1.15}
.ct-art .ct-form-card .ups-form input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),.ct-art .ct-form-card .ups-form select,.ct-art .ct-form-card .ups-form textarea{min-height:48px;border-color:#d8dde3;border-radius:12px;font-size:16px}
.ct-art .ct-form-card .ups-form textarea{min-height:110px}
.ct-art .ct-form-card .ups-form__submit,.ct-art .ct-form-card button[type="submit"]{min-height:54px;background:#06745f;color:#fff;font-size:16px}
.ct-art .ct-form-card .ups-form__submit:hover,.ct-art .ct-form-card button[type="submit"]:hover{background:#055e4d;transform:none}
.ct-art .ct-form-card .ups-form__consent{font-size:13px}
.ct-art .ct-form-card .ups-form__consent a{text-decoration:underline;text-underline-offset:2px}
.ct-art .ct-form-card .ups-form__consent input[type="checkbox"]{accent-color:#06745f}
.ct-art .ct-section-head h2{font-size:clamp(30px,3.4vw,42px);line-height:1.08}
.ct-art .ct-section-head{margin-bottom:40px}
.ct-art .ct-topic-card,.ct-art .ct-step-card,.ct-art .ct-testimonial{padding:24px;border-radius:20px;box-shadow:none}
.ct-art .ct-topic-card:hover{border-color:#c7e6dd;box-shadow:0 12px 28px rgba(15,17,21,.06);transform:translateY(-2px)}
.ct-art .ct-topic-symptom{color:#b54708;background:#fff4e5;border-radius:999px;padding:6px 12px;align-self:flex-start;letter-spacing:0}
.ct-art .ct-topic-link{min-height:44px}
.ct-art .ct-opinions{padding:96px 0;background:#fff}
.ct-art .ct-step-num,.ct-art .ct-step-timing{color:#06745f}
.ct-art .ct-faq{background:#f5f6f8}
.ct-art .ct-faq>.ct-wrap{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.65fr);gap:24px 56px;align-items:start}
.ct-art .ct-faq .ct-section-head{margin:0}
.ct-art .ct-faq-grid{max-width:none;gap:0}
.ct-art .ct-faq-item{border:0;border-bottom:1px solid #d8dde3;border-radius:0;background:transparent}
.ct-art .ct-faq-item:first-child{border-top:1px solid #d8dde3}
.ct-art .ct-faq-item summary{padding:20px 4px;min-height:48px}
.ct-art .ct-faq-item p{padding:0 4px 20px}
.ct-art .ct-faq-icon{background:#dff5ef;color:#06745f}
.ct-art .ct-faq-help{margin-top:24px;background:#effaf7;border-radius:20px;padding:24px}
.ct-art .ct-faq-help strong{display:block;color:#0f1115;font-size:17px}
.ct-art .ct-faq-help p{font-size:15px;margin:8px 0}
.ct-art .ct-faq-help a{display:inline-flex;align-items:center;min-height:44px;color:#06745f;font-weight:700}
.ct-art .ct-faq-cta-row{grid-column:1/-1;margin-top:0}
.ct-art .ct-final-box{background:#effaf7;border:1px solid #c7e6dd;box-shadow:none}
.ct-art .ct-final-box::before{display:none}
.ct-art a.ct-final-btn{background:#06745f;color:#fff;min-height:48px}
.ct-art a.ct-final-btn:hover{background:#055e4d;transform:none}
.ct-art :is(a,button,input,select,textarea,summary):focus-visible{outline:3px solid #06745f;outline-offset:3px}
.ct-art .ct-form-card .ups-form :is(input,select,textarea):focus{border-color:#06745f}
.ct-art [data-animate]{opacity:1;transform:none}
@media(max-width:980px){
  .ct-art .ct-hero-form-grid,.ct-art .ct-faq>.ct-wrap{grid-template-columns:minmax(0,1fr);gap:32px}
  .ct-art .ct-agenda-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
  .ct-art .ct-channels{grid-template-columns:repeat(2,minmax(0,1fr))}
}
@media(max-width:720px){
  .ct-art .ct-wrap{padding-inline:20px}
  .ct-art .ct-hero-form{padding:48px 0 64px}
  .ct-art .ct-h1{font-size:clamp(32px,8.5vw,40px)}
  .ct-art .ct-section-head h2{font-size:clamp(28px,7vw,34px)}
  .ct-art .ct-opinions,.ct-art .ct-agenda{padding:64px 0}
  .ct-art .ct-channels-section{padding:48px 0 0}
  .ct-art .ct-channels,.ct-art .ct-agenda-grid{grid-template-columns:minmax(0,1fr)}
  .ct-art .ct-form-card{padding:24px}
  .ct-art .ct-contact-person{flex-wrap:wrap}
  .ct-art .ct-contact-person a{margin-left:0}
  .ct-art .ct-final-side a{overflow-wrap:anywhere}
}
@media(prefers-reduced-motion:reduce){.ct-art *, .ct-art *::before,.ct-art *::after{animation:none!important;transition:none!important;scroll-behavior:auto!important}}


.up-contact-topics{border:0;margin:0 0 18px;padding:0;min-width:0}.up-contact-topics legend{font-weight:700;font-size:15px;margin-bottom:8px}.up-topic-list{display:flex;flex-wrap:wrap;gap:8px}.up-topic-list button{min-height:44px;border-radius:999px;background:#fff;border:1px solid #e3e6ea;color:#3a3d44;padding:8px 14px;font:600 14px/1.4 "DM Sans",sans-serif}.up-topic-list button[aria-pressed=true]{background:#dff5ef;border-color:#06745f;color:#06745f}.up-topic-list button:focus-visible{outline:3px solid #06745f;outline-offset:2px}
</style>

<main class="ct-art" id="kontakt">

<!-- ====================================================================
     SEKCJA 01 — HERO + FORMULARZ (ta sama wysokosc kolumn)
     Jezyk: korzysc dla klienta (mapa problemu), nie obietnica autora.
     ==================================================================== -->
<section class="ct-hero-form" id="formularz-kontaktowy">
    <div class="ct-wrap">
        <div class="ct-hero-form-grid">

            <div class="ct-hero-copy" data-animate="fade-right">
                <div class="ct-eyebrow">Bezpłatna 30-min diagnoza</div>

                <h1 class="ct-h1">
                    Opisz sytuację. W 30 minut wskażę, <span class="ct-h1-accent">co poprawić najpierw.</span>
                </h1>

                <p class="ct-lead">
                    Nie musisz znać się na marketingu. Wystarczy, że czujesz, że Twój sklep, lokal czy reklamy mogłyby przynosić więcej.
                </p>

                <ol class="ct-hero-steps">
                    <li><span class="ct-hero-step-num" aria-hidden="true">1</span><div><strong>Wysyłasz formularz</strong><p>Zajmie Ci to około 2 minut.</p></div></li>
                    <li><span class="ct-hero-step-num" aria-hidden="true">2</span><div><strong>Oddzwaniam w 24h roboczych</strong><p>Wcześniej zaglądam na Twoją stronę, sklep i reklamy.</p></div></li>
                    <li><span class="ct-hero-step-num" aria-hidden="true">3</span><div><strong>Dostajesz konkretną listę</strong><p>3–5 rzeczy do poprawy — możesz je wdrożyć sam, nawet bez współpracy.</p></div></li>
                </ol>

                <div class="ct-contact-person">
                    <span class="ct-person-avatar" aria-hidden="true">SK</span>
                    <div><strong>Sebastian Kelm</strong><small>Odbieram osobiście · pn–pt 9–17</small></div>
                    <a href="tel:<?php echo esc_attr($contact_phone_href); ?>"><?php echo esc_html($contact_phone); ?></a>
                </div>

            </div>

            <aside class="ct-form-card" aria-labelledby="form-heading" data-animate="fade-left" data-delay="1">
                <div class="ct-form-head">
                    <span class="ct-eyebrow">Wypełnij formularz</span>
                    <h2 id="form-heading">Umów diagnozę</h2>
                    <p>Odpowiadam osobiście w 24h roboczych.</p>
                </div>

                <fieldset class="up-contact-topics" hidden><legend>Co chcesz poprawić? (opcjonalnie)</legend><div class="up-topic-list"><button type="button" aria-pressed="false" data-up-topic="Google Ads">Google Ads</button><button type="button" aria-pressed="false" data-up-topic="Meta Ads">Meta Ads</button><button type="button" aria-pressed="false" data-up-topic="Strona lub sklep">Strona lub sklep</button><button type="button" aria-pressed="false" data-up-topic="Nie wiem, od czego zacząć">Nie wiem, od czego zacząć</button></div></fieldset>
                <?php
                /* Renderuj formularz przez wspolny helper motywu */
                echo upsellio_render_lead_form([
                    "origin" => "contact-page-form",
                    "submit_label" => "Wyślij — oddzwonię w 24h →",
                    "variant" => "full",
                    "heading" => "",
                    "subheading" => "",
                    "redirect_url" => $contact_page_url,
                    "service_options" => $contact_service_options,
                    "css_class" => "ct-form",
                    "form_id" => "contact-page-form",
                ]);
                ?>
            </aside>

        </div>
    </div>
</section>



<section class="ct-channels-section" aria-label="Kanały kontaktu">
    <div class="ct-wrap">
        <div class="ct-channels">
                    <a href="tel:<?php echo esc_attr($contact_phone_href); ?>" class="ct-channel-link">
                        <span class="ct-channel-icon" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 4h4l2 5-2 2a10 10 0 0 0 4 4l2-2 5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/></svg></span><span><strong>Telefon · pn–pt 9–17</strong><?php echo esc_html($contact_phone); ?></span>
                    </a>
                    <a href="mailto:<?php echo esc_attr($contact_email); ?>" class="ct-channel-link">
                        <span class="ct-channel-icon" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></span><span><strong>E-mail</strong><?php echo esc_html($contact_email); ?></span>
                    </a>
                    <a href="<?php echo esc_url($linkedin_url); ?>" target="_blank" rel="noopener noreferrer" class="ct-channel-link">
                        <span class="ct-channel-icon" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M8 10v6M8 7v1M12 16v-6m0 2c0-3 4-3 4 0v4"/></svg></span><span><strong>LinkedIn</strong>Sebastian Kelm</span>
                    </a>
                </div>
    </div>
</section>

<section class="ct-agenda" id="co-omowimy" aria-labelledby="ct-agenda-heading">
    <div class="ct-wrap">
        <div class="ct-section-head">
            <span class="ct-eyebrow">Na rozmowie</span>
            <h2 id="ct-agenda-heading">Co konkretnie omówimy</h2>
        </div>
        <div class="ct-agenda-grid">
            <article class="ct-agenda-card"><span class="ct-channel-icon" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m16 16 5 5"/></svg></span><div><h3>Gdzie uciekają klienci</h3><p>Co na stronie, w sklepie lub reklamach zniechęca do zakupu.</p></div></article>
            <article class="ct-agenda-card"><span class="ct-channel-icon" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 5h16v14H4zM4 9h16M9 9v10"/></svg></span><div><h3>Który kanał wybrać</h3><p>Google, Facebook i Instagram czy najpierw strona — co da najwięcej u Ciebie.</p></div></article>
            <article class="ct-agenda-card"><span class="ct-channel-icon" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18M7 15l4-4 4 3 5-7"/></svg></span><div><h3>Rozsądny budżet na start</h3><p>Ile wydać, żeby reklama miała szansę się zwrócić.</p></div></article>
            <article class="ct-agenda-card"><span class="ct-channel-icon" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 4 4 10-10"/></svg></span><div><h3>Co zrobić samemu</h3><p>Co poprawisz sam od ręki, a co lepiej zlecić.</p></div></article>
        </div>
    <div class="ct-proof-grid">
                    <div class="ct-proof-cell">
                        <strong>30 min</strong>
                        <span>rozmowa</span>
                    </div>
                    <div class="ct-proof-cell">
                        <strong>0 zł</strong>
                        <span>diagnoza</span>
                    </div>
                    <div class="ct-proof-cell">
                        <strong>24h</strong>
                        <span>odpowiedź</span>
                    </div>
                    <div class="ct-proof-cell">
                        <strong>1 osoba</strong>
                        <span>cały kontakt</span>
                    </div>
                </div>

                </div>
</section>

<!-- ====================================================================
     SEKCJA 02 — O CO ZAPYTAC (6 kart)
     Naglowek: "Wiesz ze cos nie dziala. Niekoniecznie wiesz co dokladnie.
     To wystarczy."
     Symptomy zapakowane w pigule "ten problem widac tak".
     ==================================================================== -->
<section class="ct-topics" id="o-co-zapytac" data-animate="fade-up">
    <div class="ct-wrap">
        <div class="ct-section-head">
            <span class="ct-eyebrow">O co możesz zapytać?</span>
            <h2>Wiesz że coś nie działa. Niekoniecznie wiesz co dokładnie. To wystarczy.</h2>
            <p>
                Wystarczy że opiszesz objaw. Pomogę zlokalizować, czy problem jest w ruchu, stronie,
                ofercie, formularzu czy procesie sprzedaży — i co naprawić najpierw.
            </p>
        </div>

        <div class="ct-topic-grid">
            <article class="ct-topic-card">
                <span class="ct-topic-symptom">Kliknięcia są, rozmów nie ma</span>
                <h3>Google Ads dla firm B2B</h3>
                <p>Kampania pochłania budżet, statystyki w panelu wyglądają poprawnie — CTR jest, kliknięcia są, koszt na kliknięcie pod kontrolą. A handlowiec mówi, że telefon milczy. Najczęściej problem nie leży w samej reklamie, tylko w dopasowaniu intencji do oferty i w tym, co dzieje się po kliknięciu. Sprawdzę strukturę kampanii (Search, Performance Max, remarketing), słowa kluczowe, dopasowanie kreacji do landing page'a i ścieżkę konwersji aż do formularza.</p>
                <?php if ($google_ads_url !== "") : ?>
                <a class="ct-topic-link" href="<?php echo esc_url($google_ads_url); ?>">
                    Zobacz usługę Google Ads →
                </a>
                <?php endif; ?>
            </article>

            <article class="ct-topic-card">
                <span class="ct-topic-symptom">Leady od osób, które nie kupują</span>
                <h3>Meta Ads (Facebook i Instagram) B2B</h3>
                <p>Reklamy na Facebooku i Instagramie generują ruch, formularze się wypełniają, ale jakość zapytań jest słaba — głównie ciekawscy, studenci, freelancerzy zamiast docelowych decyzyjnych w firmach B2B. To zwykle kwestia targetowania, kreacji i mechaniki Lead Ads. Sprawdzę audiences (lookalike, custom, retargeting), Pixel i Conversions API, atrybucję po iOS oraz to, czy kreacja faktycznie filtruje niepoważnych zainteresowanych już na poziomie reklamy.</p>
                <?php if ($meta_ads_url !== "") : ?>
                <a class="ct-topic-link" href="<?php echo esc_url($meta_ads_url); ?>">
                    Zobacz usługę Meta Ads →
                </a>
                <?php endif; ?>
            </article>

            <article class="ct-topic-card">
                <span class="ct-topic-symptom">Wysoki bounce rate, zero leadów</span>
                <h3>Strona WWW i landing page B2B</h3>
                <p>Ruch wchodzi, ale konwersja na formularz jest poniżej 1%. Najczęściej strona za mało precyzyjnie odpowiada na pytanie „dla kogo, po co i co dalej". Klient B2B w 5 sekund decyduje, czy ten dostawca rozumie jego problem. Sprawdzę przekaz strony, hierarchię informacji, sekcje budujące zaufanie, jasność CTA, wydajność (Core Web Vitals) i formularz — od liczby pól po sposób ich opisania. Często wystarczy poprawić te 5-7 elementów żeby konwersja wzrosła kilkukrotnie.</p>
                <?php if ($websites_url !== "") : ?>
                <a class="ct-topic-link" href="<?php echo esc_url($websites_url); ?>">
                    Zobacz usługę stron WWW →
                </a>
                <?php endif; ?>
            </article>

            <article class="ct-topic-card">
                <span class="ct-topic-symptom">Koszyki napełniają się, zamówienia nie wpływają</span>
                <h3>Sklep internetowy B2B (WooCommerce)</h3>
                <p>Klienci dodają produkty do koszyka, część rejestruje konto, ale finalizacja zamówienia kuleje. W e-commerce B2B problem zwykle leży w jednym z trzech miejsc: rejestracja jest zbyt skomplikowana, brakuje informacji o cenach hurtowych i dostępności, albo proces zamawiania nie pasuje do tego jak klient B2B faktycznie kupuje (negocjacje, zapytania ofertowe, wielokrotne zamówienia). Sprawdzę cały lejek od wejścia do potwierdzenia zamówienia.</p>
                <a class="ct-topic-link" href="#formularz-kontaktowy">Opisz swój przypadek →</a>
            </article>

            <article class="ct-topic-card">
                <span class="ct-topic-symptom">Pozycje są, konwersja nie</span>
                <h3>SEO i ruch organiczny dla firm B2B</h3>
                <p>Wpisujesz frazy kluczowe w Google, widzisz swoją firmę na pierwszej stronie, ruch z organic wzrósł — ale w skrzynce zapytaniowej cisza. To często rozmijanie się intencji wyszukiwania z treścią strony albo brak ścieżek konwersji w treściach blogowych. Pomogę zlokalizować, czy problem to dobór fraz, struktura treści, brakujące CTA w artykułach, czy może jakość ruchu (klienci końcowi B2C zamiast B2B). Bez tej diagnozy SEO to często wydatek bez zwrotu.</p>
                <a class="ct-topic-link" href="#formularz-kontaktowy">Opisz swój przypadek →</a>
            </article>

            <article class="ct-topic-card">
                <span class="ct-topic-symptom">Dużo rozmów, mało zamkniętych</span>
                <h3>Oferta i jakość leadów B2B</h3>
                <p>Reklama działa, formularzy jest dużo, telefon dzwoni, ale rozmowy nie kończą się umowami. Tu marketing kończy swoją robotę, a zaczyna się sprzedaż — i tu często leży faktyczny problem. Sprawdzę jakość leadów na wejściu (czy reklama nie ściąga niewłaściwych klientów), spójność komunikatu od reklamy przez stronę po pierwszą rozmowę, a także sam proces sprzedaży: czy oferta odpowiada na realny ból klienta, czy follow-up nie zabija dynamiki. Tu pomaga moje 10-letnie tło sprzedażowe.</p>
                <a class="ct-topic-link" href="#formularz-kontaktowy">Opisz swój przypadek →</a>
            </article>
        </div>
    </div>
</section>


<!-- ====================================================================
     SEKCJA 03 — OPINIE
     UWAGA: trzymam jeden testimonial (M.) ktory bylo w obecnej wersji.
     Drugi i trzeci to placeholdery — Sebastian dodaje gdy bedzie mial
     zgody klientow. Lepiej puste niz fikcyjne.
     ==================================================================== -->
<section class="ct-opinions" id="opinie" data-animate="fade-up"><div class="ct-wrap"><div class="ct-section-head"><span class="ct-eyebrow">Po rozmowie</span><h2>Co dostajesz po diagnozie?</h2><p>Konkretną listę priorytetów, którą możesz wykorzystać samodzielnie, niezależnie od decyzji o współpracy.</p></div></div></section>


<!-- ====================================================================
     SEKCJA 04 — PROCES (3 kroki)
     Krok 2 zmieniony: "Twoj przypadek trafia pod konkretna pare oczu"
     zamiast "Sprawdzam kontekst" (ja-ja).
     ==================================================================== -->
<section class="ct-process" id="jak-dziala" data-animate="fade-up">
    <div class="ct-wrap">
        <div class="ct-section-head">
            <span class="ct-eyebrow">Jak to działa?</span>
            <h2>Krótko, konkretnie, bez przeciągania.</h2>
            <p>
                Od formularza do konkretnej odpowiedzi — opisuję każdy krok,
                żebyś wiedział czego się spodziewać.
            </p>
        </div>

        <div class="ct-process-grid">
            <div class="ct-step-card">
                <span class="ct-step-num">Krok 01</span>
                <h3>Wysyłasz formularz</h3>
                <p>Opisujesz w 2-3 zdaniach co nie działa. Może to być link do strony, konkretny problem z kampanią, albo „nie wiem co jest nie tak — pomóż zlokalizować".</p>
                <ul class="ct-step-list">
                    <li>Bez gotowego briefu, bez prezentacji</li>
                    <li>Wystarczy opis sytuacji własnymi słowami</li>
                    <li>Możesz dodać link do strony lub panelu reklamowego</li>
                </ul>
                <span class="ct-step-timing">2 minuty, teraz</span>
            </div>

            <div class="ct-step-card">
                <span class="ct-step-num">Krok 02</span>
                <h3>Sprawdzam Twój przypadek</h3>
                <p>Patrzę na Twoją stronę, kampanie i komunikat z perspektywy 10 lat sprzedaży B2B — szukam miejsc gdzie pieniądze wyciekają, zanim klient zdąży zapytać o cenę.</p>
                <ul class="ct-step-list">
                    <li>Audyt kampanii (jeśli dasz dostęp do panelu)</li>
                    <li>Analiza strony pod kątem konwersji i przekazu</li>
                    <li>Pierwsze hipotezy: gdzie jest największy wyciek</li>
                </ul>
                <span class="ct-step-timing">Odpisuję w 24h roboczych</span>
            </div>

            <div class="ct-step-card">
                <span class="ct-step-num">Krok 03</span>
                <h3>Dostajesz konkretną mapę</h3>
                <p>30-minutowa rozmowa (telefon lub Google Meet). Pokazuję 3-5 punktów wycieku, mówię czego sam nie widzisz i podpowiadam co poprawić w pierwszej kolejności.</p>
                <ul class="ct-step-list">
                    <li>Konkretne wskazania, nie ogólne porady</li>
                    <li>Co możesz zrobić sam, gdzie warto pomóc</li>
                    <li>Bez „a może jednak" — albo kontynuujemy, albo nie</li>
                </ul>
                <span class="ct-step-timing">Bezpłatnie, bez zobowiązań</span>
            </div>
        </div>
    </div>
</section>


<!-- ====================================================================
     SEKCJA 05 — FAQ (8 pytan w nowej kolejnosci)
     Pierwsze pytanie nie o cenie, tylko "po jakim czasie efekty".
     Trzecie: nowe — "co jesli nie chce z Toba pracowac" (kasowe).
     ==================================================================== -->
<section class="ct-faq" id="faq" data-animate="fade-up">
    <div class="ct-wrap">
        <div class="ct-section-head">
            <span class="ct-eyebrow">Zanim wyślesz formularz</span>
            <h2>Odpowiedzi na najczęstsze pytania</h2>
            <p>Jeśli nie znajdziesz odpowiedzi — napisz, a odpowiem bezpośrednio.</p>
            <div class="ct-faq-help">
                <strong>Nie ma tu Twojego pytania?</strong>
                <p>Zadzwoń — odbieram osobiście, pn–pt 9–17.</p>
                <a href="tel:<?php echo esc_attr($contact_phone_href); ?>"><?php echo esc_html($contact_phone); ?></a>
            </div>
        </div>

        <div class="ct-faq-grid">
            <?php foreach ($contact_faq_items as $faq_item) : ?>
            <details class="ct-faq-item">
                <summary>
                    <span><?php echo esc_html((string) $faq_item["question"]); ?></span>
                    <span class="ct-faq-icon" aria-hidden="true">+</span>
                </summary>
                <p><?php echo esc_html((string) $faq_item["answer"]); ?></p>
            </details>
            <?php endforeach; ?>
        </div>

        <p class="ct-faq-cta-row">
            Potrzebujesz więcej kontekstu o usługach?
            <?php if ($google_ads_url !== "") : ?>
                <a href="<?php echo esc_url($google_ads_url); ?>">Google Ads B2B</a> ·
            <?php endif; ?>
            <?php if ($meta_ads_url !== "") : ?>
                <a href="<?php echo esc_url($meta_ads_url); ?>">Meta Ads B2B</a> ·
            <?php endif; ?>
            <?php if ($websites_url !== "") : ?>
                <a href="<?php echo esc_url($websites_url); ?>">Strony WWW</a>
            <?php endif; ?>
        </p>
    </div>
</section>


<!-- ====================================================================
     SEKCJA 06 — FINAL CTA
     "Wystarcza 2 zdania. Wrocisz do swojej pracy." — zbija strach
     ze formularz urosnie w cały dzien maili.
     ==================================================================== -->
<section class="ct-final">
    <div class="ct-wrap">
        <div class="ct-final-box">
            <div>
                <span class="ct-final-eyebrow">Nie odkładaj na później</span>
                <h2>Jeśli leady uciekają, zwykle da się znaleźć konkretny powód</h2>
                <p>
                    Wystarczą 2 zdania. Wrócisz do swojej pracy, a do 24h dostaniesz wstępną odpowiedź:
                    gdzie szukać problemu w pierwszej kolejności — reklama, strona, oferta, formularz
                    czy proces sprzedaży. Pierwsze 30 minut bez opłat, bez zobowiązań.
                </p>
                <a href="#formularz-kontaktowy" class="ct-final-btn">
                    Wróć do formularza
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            <aside class="ct-final-side">
                <div>
                    <strong>Albo bezpośrednio</strong>
                    <a href="tel:<?php echo esc_attr($contact_phone_href); ?>"><?php echo esc_html($contact_phone); ?></a>
                </div>
                <div>
                    <strong>E-mailem</strong>
                    <a href="mailto:<?php echo esc_attr($contact_email); ?>"><?php echo esc_html($contact_email); ?></a>
                </div>
                <div style="font-size:13px;color:var(--ct-muted);margin-top:8px;line-height:1.5">
                    Odpowiadam osobiście pn–pt, 9-17. Jeśli wolisz krótką rozmowę telefoniczną zamiast formularza — zadzwoń, oddzwaniam tego samego dnia.
                </div>
            </aside>
        </div>
    </div>
</section>

</main>

<!-- ====================================================================
     FAQ Schema.org dla SEO
     ==================================================================== -->
<?php if (!empty($contact_faq_items)) : ?>
<script type="application/ld+json">
<?php
echo wp_json_encode([
    "@context" => "https://schema.org",
    "@type" => "FAQPage",
    "mainEntity" => array_map(static function ($faq_item) {
        return [
            "@type" => "Question",
            "name" => (string) $faq_item["question"],
            "acceptedAnswer" => [
                "@type" => "Answer",
                "text" => (string) $faq_item["answer"],
            ],
        ];
    }, $contact_faq_items),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
?>
</script>
<?php endif; ?>

<!-- ContactPage schema -->
<script type="application/ld+json">
<?php
echo wp_json_encode([
    "@context" => "https://schema.org",
    "@type" => "ContactPage",
    "name" => "Kontakt — Bezpłatna diagnoza marketingu B2B | Upsellio",
    "url" => $contact_page_url,
    "description" => "Bezpłatna 30-minutowa diagnoza kampanii Google Ads, Meta Ads i stron WWW dla firm B2B. Sebastian Kelm — specjalista marketingu i sprzedaży B2B.",
    "mainEntity" => [
        "@type" => "LocalBusiness",
        "name" => "Upsellio — Marketing B2B",
        "url" => home_url("/"),
        "email" => $contact_email,
        "telephone" => $contact_phone,
        "areaServed" => "Polska",
        "availableLanguage" => "Polish",
        "founder" => [
            "@type" => "Person",
            "name" => "Sebastian Kelm",
            "jobTitle" => "Specjalista marketingu B2B",
            "sameAs" => $linkedin_url,
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
</script>

<script>
(function() {
    'use strict';

    /* --- Animacje on-scroll --- */
    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    io.unobserve(entry.target);
                }
            });
        }, { rootMargin: '0px 0px -10% 0px', threshold: 0.08 });

        document.querySelectorAll('[data-animate]').forEach(function(el) {
            io.observe(el);
        });
    } else {
        document.querySelectorAll('[data-animate]').forEach(function(el) {
            el.classList.add('is-visible');
        });
    }

    /* --- Nadpisz placeholder w polu wiadomosci formularza ---
       Wspolny helper upsellio_render_lead_form ma zaszyty placeholder
       "Krotko opisz sytuacje". Tu zastepujemy go konkretnym przykladem
       — tylko dla tego formularza, bez modyfikacji wspolnego helpera. */
    var contactForm = document.getElementById('contact-page-form');
    if (contactForm) {
        var msg = contactForm.querySelector('textarea[name="lead_message"]');
        if (msg) {
            msg.setAttribute('placeholder',
                'Np. Płacę za reklamy, ale mam mało zamówień. Chcę sprawdzić, co warto poprawić w sklepie lub kampanii.');
        }
    }

    // Anchor scrolling is handled by the shared theme script; avoid duplicate listeners.

})();
</script>

<?php
get_footer();
?>
