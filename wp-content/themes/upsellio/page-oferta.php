<?php
/*
Template Name: Upsellio - Oferta
Template Post Type: page
*/

if (!defined("ABSPATH")) {
    exit;
}

add_filter("pre_get_document_title", static function ($title) {
    return is_page_template("page-oferta.php") ? "Google Ads, Meta Ads i strony WWW dla firm | Upsellio" : $title;
});

add_action("wp_head", static function () {
    if (!is_page_template("page-oferta.php")) return;
    echo '<meta name="description" content="Kampanie Google Ads, Meta Ads i tworzenie stron internetowych nastawionych na leady i sprzedaż B2B. Bezpłatna diagnoza — bez zobowiązań.">' . "\n";
    $offer_url = function_exists("upsellio_get_offer_page_url") ? upsellio_get_offer_page_url() : "";
    if ($offer_url) echo '<link rel="canonical" href="' . esc_url($offer_url) . '">' . "\n";
}, 1);

get_header();

$front_page_sections = function_exists("upsellio_get_front_page_content_config") ? upsellio_get_front_page_content_config() : [];
$contact_phone = function_exists("upsellio_get_contact_phone") ? upsellio_get_contact_phone() : "+48 575 522 595";
$contact_email = trim((string) ($front_page_sections["contact_email"] ?? "kontakt@upsellio.pl"));
$contact_email_href = function_exists("upsellio_get_mailto_href") ? upsellio_get_mailto_href($contact_email) : "mailto:" . $contact_email;
$contact_email_display = function_exists("upsellio_obfuscate_email_address") ? upsellio_obfuscate_email_address($contact_email) : $contact_email;
$offer_page_url = function_exists("upsellio_get_offer_page_url") ? (string) upsellio_get_offer_page_url() : "";
$google_ads_url = function_exists("upsellio_get_google_ads_page_url") ? (string) upsellio_get_google_ads_page_url() : "";
$meta_ads_url = function_exists("upsellio_get_meta_ads_page_url") ? (string) upsellio_get_meta_ads_page_url() : "";
$websites_url = function_exists("upsellio_get_websites_page_url") ? (string) upsellio_get_websites_page_url() : "";
$offer_founder = function_exists("upsellio_get_trust_seo_section") ? upsellio_get_trust_seo_section("founder") : [];
$offer_founder_name = (string) ($offer_founder["name"] ?? "Sebastian Kelm");
$offer_founder_role = (string) ($offer_founder["role"] ?? "Growth marketer B2B");
$offer_founder_photo = (string) ($offer_founder["photo_url"] ?? "");
$offer_founder_initials = function_exists("upsellio_get_initials_from_text") ? upsellio_get_initials_from_text($offer_founder_name) : "SK";
$contact_service_options = isset($front_page_sections["contact_service_options"]) && is_array($front_page_sections["contact_service_options"]) ? $front_page_sections["contact_service_options"] : [];

$offer_faq_items = [
    ["question" => "Czy najpierw powinienem zrobić stronę, czy reklamy?", "answer" => "To zależy. Jeśli obecna strona nie tłumaczy oferty i nie buduje zaufania, reklamy tylko szybciej pokażą problem. Jeśli strona jest klarowna, możemy zacząć od kampanii."],
    ["question" => "Czy mogę zacząć tylko od jednej usługi?", "answer" => "Tak. Możesz zacząć od Google Ads, Meta Ads albo od strony internetowej — zależnie od tego, co blokuje wyniki najbardziej."],
    ["question" => "Dla jakich firm jest ta oferta?", "answer" => "Głównie dla firm usługowych B2B, firm lokalnych, producentów i marek e-commerce, które chcą stabilnie zwiększać liczbę wartościowych zapytań."],
    ["question" => "Jak szybko widać pierwsze efekty?", "answer" => "Pierwsze leady zwykle widać po 2–4 tygodniach, a stabilne wyniki najczęściej po 2–3 miesiącach regularnej optymalizacji."],
];
?>
<style>
.op{--op-bg:#f8fafc;--op-surface:#fff;--op-navy:#0d1b2a;--op-dark2:#0f1f2e;--op-teal:#0d9488;--op-teal-d:#0f766e;--op-teal-s:#ccfbf1;--op-text:#0f172a;--op-muted:#475569;--op-soft:#64748b;--op-border:#dbe7ea;--op-border2:#cbd5e1;--op-google:#0d9488;--op-google-s:#ccfbf1;--op-meta:#6366f1;--op-meta-s:#eef2ff;--op-web:#d97706;--op-web-s:#fef3c7;--op-sh-sm:0 4px 16px rgba(15,23,42,.06);--op-sh-md:0 14px 40px rgba(15,23,42,.09);--op-sh-lg:0 28px 72px rgba(15,23,42,.14);background:var(--op-bg);color:var(--op-text);font-family:"DM Sans",system-ui,sans-serif;line-height:1.65}
.op *,.op *:before,.op *:after{box-sizing:border-box}.op a{text-decoration:none;color:inherit}.op-wrap{max-width:1200px;margin:0 auto;padding:0 24px}
.op-nav{position:sticky;top:0;z-index:200;background:rgba(255,255,255,.88);border-bottom:1px solid var(--op-border);backdrop-filter:blur(20px) saturate(1.6);box-shadow:0 2px 16px rgba(15,23,42,.06)}
.op-nav-inner{height:54px;display:flex;align-items:center;justify-content:space-between;gap:16px;overflow-x:auto;scrollbar-width:none}.op-nav-inner::-webkit-scrollbar{display:none}
.op-nav-links{display:flex;align-items:center;gap:4px;white-space:nowrap;flex:1}.op-nav-links a{display:inline-flex;align-items:center;height:32px;padding:0 12px;border-radius:999px;font-size:12.5px;font-weight:700;color:var(--op-soft);transition:color .18s,background .18s}.op-nav-links a:hover,.op-nav-links a.active{color:var(--op-teal-d);background:var(--op-teal-s)}
.op-nav-cta{flex-shrink:0;display:inline-flex;align-items:center;height:36px;padding:0 18px;border-radius:999px;background:var(--op-teal);color:#fff;font-size:13px;font-weight:800;box-shadow:0 6px 20px rgba(13,148,136,.28)}
.op-hero{padding:clamp(72px,9vw,128px) 0 0;border-bottom:1px solid var(--op-border);background:radial-gradient(ellipse at 96% 0%,rgba(13,148,136,.13) 0%,transparent 42%),var(--op-bg)}
.op-hero-inner{display:grid;grid-template-columns:1fr 380px;gap:52px;align-items:start;padding-bottom:clamp(48px,7vw,88px)}
.op-hero-eyebrow{display:inline-flex;align-items:center;gap:10px;margin-bottom:20px;font-size:11px;font-weight:900;letter-spacing:1.8px;text-transform:uppercase;color:var(--op-teal)}.op-hero-eyebrow:before{content:"";display:block;width:28px;height:2px;background:var(--op-teal)}
.op-hero h1{font-family:"Syne",sans-serif;font-size:clamp(40px,5.6vw,72px);line-height:1;letter-spacing:-2.5px;margin:0}.op-hero h1 em{font-style:normal;color:var(--op-teal)}.op-hero-lead{margin:24px 0 0;max-width:640px;font-size:18px;line-height:1.72;color:var(--op-muted)}
.op-hero-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:32px}.op-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:50px;padding:0 24px;border-radius:999px;font-size:14.5px;font-weight:800;border:none;cursor:pointer}.op-btn-primary{background:linear-gradient(135deg,var(--op-teal),var(--op-teal-d));color:#fff;box-shadow:0 12px 32px rgba(13,148,136,.28)}.op-btn-secondary{background:#fff;border:1.5px solid var(--op-border2)}.op-btn-ghost{background:var(--op-teal-s);color:var(--op-teal-d);border:1px solid rgba(13,148,136,.22)}
.op-hero-aside{position:sticky;top:80px}.op-metric-card{background:#fff;border:1px solid var(--op-border);border-radius:24px;padding:28px;box-shadow:var(--op-sh-lg)}
.op-metric-card-head{display:flex;align-items:center;gap:12px;margin-bottom:20px;padding-bottom:18px;border-bottom:1px solid var(--op-border)}
.op-metric-card-avatar{width:48px;height:48px;border-radius:50%;display:grid;place-items:center;background:linear-gradient(135deg,var(--op-teal),var(--op-teal-d));color:#fff;font-family:"Syne",sans-serif;font-size:17px;font-weight:800;overflow:hidden}.op-metric-card-avatar img{width:100%;height:100%;object-fit:cover}
.op-metric-card-name{font-weight:800;font-size:14px}.op-metric-card-role{font-size:12px;color:var(--op-soft);margin-top:2px}.op-metric-card-status{display:inline-flex;align-items:center;gap:5px;margin-top:6px;font-size:11px;font-weight:800;color:#16a34a;background:#dcfce7;padding:2px 9px;border-radius:999px}
.op-kpi-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}.op-kpi-cell{background:var(--op-bg);border:1px solid var(--op-border);border-radius:16px;padding:14px;text-align:center}.op-kpi-val{display:block;font-family:"Syne",sans-serif;font-size:28px;font-weight:800;color:var(--op-teal-d);line-height:1}.op-kpi-label{display:block;margin-top:5px;font-size:11px;color:var(--op-soft)}.op-kpi-change{display:inline-flex;margin-top:5px;font-size:11px;font-weight:800;padding:2px 7px;border-radius:999px}.op-kpi-change.up{background:#dcfce7;color:#16a34a}.op-kpi-change.dn{background:var(--op-teal-s);color:var(--op-teal-d)}
.op-metric-funnel{margin-top:14px;padding-top:14px;border-top:1px solid var(--op-border)}.op-funnel-label{font-size:11px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--op-soft);margin-bottom:10px}.op-funnel-row{display:flex;align-items:center;gap:8px;margin-bottom:7px}.op-funnel-name{color:var(--op-muted);font-weight:600;min-width:62px}.op-funnel-bar-wrap{flex:1;height:6px;background:var(--op-bg);border-radius:99px;overflow:hidden}.op-funnel-bar{height:100%;background:linear-gradient(90deg,var(--op-teal),var(--op-teal-d));border-radius:99px}.op-funnel-pct{font-weight:800;min-width:38px;text-align:right;font-size:12px}
.op-decision-row{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;padding:clamp(24px,4vw,36px) 0 clamp(40px,6vw,72px);border-top:1px solid var(--op-border)}.op-decision-card{display:flex;align-items:flex-start;gap:14px;padding:20px;border:1.5px solid var(--op-border);border-radius:20px;background:#fff;transition:transform .2s,box-shadow .2s}.op-decision-card:hover{transform:translateY(-3px);box-shadow:var(--op-sh-md)}
.op-decision-icon{width:40px;height:40px;border-radius:12px;display:grid;place-items:center;flex-shrink:0}.op-decision-card.google .op-decision-icon{background:var(--op-google-s);color:var(--op-google)}.op-decision-card.meta .op-decision-icon{background:var(--op-meta-s);color:var(--op-meta)}.op-decision-card.web .op-decision-icon{background:var(--op-web-s);color:var(--op-web)}
.op-section{padding:clamp(72px,9vw,112px) 0}.op-section-border{border-bottom:1px solid var(--op-border)}.op-section-soft{background:var(--op-bg)}.op-section-white{background:#fff}.op-eyebrow{display:inline-flex;align-items:center;gap:10px;margin-bottom:18px;font-size:11px;font-weight:900;letter-spacing:1.8px;text-transform:uppercase;color:var(--op-teal)}.op-eyebrow:before{content:"";width:22px;height:2px;background:var(--op-teal)}.op-h2{font-family:"Syne",sans-serif;font-size:clamp(32px,4vw,52px);line-height:1.04;letter-spacing:-1.8px;margin:0 0 18px;max-width:900px}.op-body{font-size:16px;line-height:1.78;color:var(--op-muted);max-width:760px}
.op-services-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:44px}
.op-service-card{position:relative;background:#fff;border:1.5px solid var(--op-border);border-radius:28px;padding:32px;overflow:hidden;display:flex;flex-direction:column;transition:transform .22s,box-shadow .22s,border-color .22s}.op-service-card:before{content:"";position:absolute;inset:0;opacity:0;transition:opacity .3s}.op-service-card:hover{transform:translateY(-6px)}.op-service-card:hover:before{opacity:1}
.op-service-card.google{border-top:4px solid var(--op-google)}.op-service-card.google:before{background:radial-gradient(circle at 90% 0%,rgba(13,148,136,.06),transparent 50%)}.op-service-card.google:hover{box-shadow:0 24px 60px rgba(13,148,136,.18),var(--op-sh-md)}
.op-service-card.meta{border-top:4px solid var(--op-meta)}.op-service-card.meta:before{background:radial-gradient(circle at 90% 0%,rgba(99,102,241,.06),transparent 50%)}.op-service-card.meta:hover{box-shadow:0 24px 60px rgba(99,102,241,.18),var(--op-sh-md)}
.op-service-card.web{border-top:4px solid var(--op-web)}.op-service-card.web:before{background:radial-gradient(circle at 90% 0%,rgba(217,119,6,.06),transparent 50%)}.op-service-card.web:hover{box-shadow:0 24px 60px rgba(217,119,6,.18),var(--op-sh-md)}
.op-service-icon{width:54px;height:54px;border-radius:18px;display:grid;place-items:center;margin-bottom:22px}.op-service-card.google .op-service-icon{background:var(--op-google-s);color:var(--op-google)}.op-service-card.meta .op-service-icon{background:var(--op-meta-s);color:var(--op-meta)}.op-service-card.web .op-service-icon{background:var(--op-web-s);color:var(--op-web)}.op-service-icon svg{width:26px;height:26px}
.op-service-card h3{font-family:"Syne",sans-serif;font-size:clamp(20px,2.2vw,26px);line-height:1.12;margin:0 0 12px}.op-service-card>p{font-size:15px;line-height:1.65;color:var(--op-muted);margin:0 0 20px}
.op-check-list{list-style:none;padding:0;margin:0 0 24px;display:grid;gap:9px}.op-check-list li{display:flex;align-items:flex-start;gap:9px;font-size:13.5px;color:var(--op-muted)}.op-check-list li:before{content:"✓";font-weight:900;font-size:12px;width:20px;height:20px;border-radius:50%;display:grid;place-items:center;margin-top:1px;flex-shrink:0}.op-service-card.google .op-check-list li:before{background:var(--op-google-s);color:var(--op-google)}.op-service-card.meta .op-check-list li:before{background:var(--op-meta-s);color:var(--op-meta)}.op-service-card.web .op-check-list li:before{background:var(--op-web-s);color:var(--op-web)}
.op-service-divider{border:none;border-top:1px solid var(--op-border);margin:0 0 20px}.op-service-rich{font-size:14px;line-height:1.75;color:var(--op-muted);margin:0 0 16px}
.op-service-link{display:inline-flex;align-items:center;gap:7px;font-size:14px;font-weight:800;margin-top:auto;padding:10px 18px;border-radius:999px;transition:background .18s,transform .18s}.op-service-card.google .op-service-link{color:var(--op-google);background:var(--op-google-s)}.op-service-card.meta .op-service-link{color:var(--op-meta);background:var(--op-meta-s)}.op-service-card.web .op-service-link{color:var(--op-web);background:var(--op-web-s)}.op-service-link:hover{transform:translateX(2px)}
.op-mid-cta{margin-top:40px;padding:32px 36px;border:1.5px solid rgba(13,148,136,.25);border-radius:24px;background:linear-gradient(135deg,rgba(13,148,136,.06),rgba(255,255,255,.98));display:flex;align-items:center;justify-content:space-between;gap:24px;box-shadow:var(--op-sh-sm)}.op-mid-cta strong{display:block;font-family:"Syne",sans-serif;font-size:clamp(20px,2.4vw,28px);line-height:1.1;margin-bottom:8px}.op-mid-cta p{font-size:15px;color:var(--op-muted);margin:0;max-width:600px}
.op-stat-bar{background:var(--op-navy);padding:40px 0;border-top:1px solid rgba(255,255,255,.06);border-bottom:1px solid rgba(255,255,255,.06)}.op-stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1px;background:rgba(255,255,255,.1);border-radius:20px;overflow:hidden}.op-stat-cell{padding:28px 24px;background:var(--op-dark2);text-align:center}.op-stat-val{display:block;font-family:"Syne",sans-serif;font-size:clamp(32px,4vw,48px);font-weight:800;letter-spacing:-2px;color:var(--op-teal);line-height:1}.op-stat-label{display:block;margin-top:8px;font-size:13px;color:rgba(255,255,255,.5)}
.op-chooser-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-top:40px}.op-chooser-card{border-radius:26px;padding:30px;border:1.5px solid transparent;transition:transform .22s,box-shadow .22s}.op-chooser-card:hover{transform:translateY(-5px);box-shadow:var(--op-sh-md)}.op-chooser-card.google{background:linear-gradient(160deg,var(--op-google-s),#fff 80%);border-color:rgba(13,148,136,.25)}.op-chooser-card.meta{background:linear-gradient(160deg,var(--op-meta-s),#fff 80%);border-color:rgba(99,102,241,.22)}.op-chooser-card.web{background:linear-gradient(160deg,var(--op-web-s),#fff 80%);border-color:rgba(217,119,6,.22)}
.op-chooser-tag{display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:999px;font-size:11px;font-weight:900;letter-spacing:.08em;text-transform:uppercase;margin-bottom:18px}.op-chooser-card.google .op-chooser-tag{background:rgba(13,148,136,.15);color:var(--op-teal-d)}.op-chooser-card.meta .op-chooser-tag{background:rgba(99,102,241,.14);color:#4338ca}.op-chooser-card.web .op-chooser-tag{background:rgba(217,119,6,.14);color:#92400e}
.op-chooser-card h3{font-family:"Syne",sans-serif;font-size:clamp(20px,2.4vw,26px);margin:0 0 12px}.op-chooser-card p{font-size:15px;line-height:1.68;color:var(--op-muted);margin:0 0 20px}.op-chooser-link{display:inline-flex;align-items:center;gap:6px;font-size:13.5px;font-weight:800}.op-chooser-card.google .op-chooser-link{color:var(--op-teal-d)}.op-chooser-card.meta .op-chooser-link{color:#4338ca}.op-chooser-card.web .op-chooser-link{color:#92400e}
.op-process-section{background:radial-gradient(ellipse at 5% 50%,rgba(13,148,136,.1),transparent 40%),var(--op-navy);padding:clamp(72px,9vw,112px) 0}.op-process-section .op-eyebrow{color:#5eead4}.op-process-section .op-eyebrow:before{background:#5eead4}.op-process-section .op-h2{color:#fff;margin-bottom:12px}.op-process-section .op-body{color:rgba(255,255,255,.62)}.op-process-intro{display:flex;align-items:flex-end;justify-content:space-between;gap:32px;margin-bottom:52px}
.op-timeline{position:relative;display:grid;grid-template-columns:repeat(4,1fr)}.op-timeline:before{content:"";position:absolute;left:7%;right:7%;top:28px;height:2px;background:linear-gradient(90deg,rgba(13,148,136,0),rgba(13,148,136,.6),rgba(13,148,136,0))}.op-timeline-step{padding:0 20px;text-align:center}.op-timeline-num-wrap{display:flex;justify-content:center;margin-bottom:22px}.op-timeline-num{width:56px;height:56px;border-radius:50%;border:2px solid rgba(13,148,136,.5);background:var(--op-dark2);display:grid;place-items:center;font-family:"Syne",sans-serif;font-size:20px;font-weight:900;color:var(--op-teal)}.op-timeline-step:hover .op-timeline-num{background:var(--op-teal);border-color:var(--op-teal);color:#fff;box-shadow:0 0 0 8px rgba(13,148,136,.18)}.op-timeline-step-icon{width:30px;height:30px;display:grid;place-items:center;margin:0 auto 14px;color:rgba(255,255,255,.35)}.op-timeline-step strong{display:block;font-family:"Syne",sans-serif;font-size:17px;font-weight:800;color:#fff;margin-bottom:10px}.op-timeline-step p{font-size:13.5px;color:rgba(255,255,255,.55);margin:0}.op-timeline-duration{display:inline-flex;align-items:center;gap:5px;margin-top:12px;padding:4px 12px;border-radius:999px;background:rgba(13,148,136,.15);border:1px solid rgba(13,148,136,.25);font-size:11px;font-weight:800;color:#5eead4;text-transform:uppercase;letter-spacing:.08em}
.op-audience-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:40px}.op-audience-card{padding:28px;border:1.5px solid var(--op-border);border-radius:24px;background:#fff;box-shadow:var(--op-sh-sm)}.op-audience-card:hover{transform:translateY(-3px);box-shadow:var(--op-sh-md)}.op-audience-card-head{display:flex;align-items:center;gap:12px;margin-bottom:14px}.op-audience-num{font-family:"Syne",sans-serif;font-size:32px;font-weight:900;background:linear-gradient(135deg,var(--op-teal),var(--op-teal-d));-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}.op-audience-card strong{display:block;font-family:"Syne",sans-serif;font-size:17px;font-weight:800;margin-bottom:8px}.op-audience-card p{font-size:14px;color:var(--op-muted);margin:0}
.op-faq-list{margin-top:40px;display:grid;gap:12px;max-width:880px}.op-faq-item{border:1.5px solid var(--op-border);border-radius:20px;background:#fff;overflow:hidden}.op-faq-item:hover{box-shadow:var(--op-sh-sm)}.op-faq-item summary{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:20px 24px;cursor:pointer;list-style:none;font-family:"Syne",sans-serif;font-size:15.5px;font-weight:700}.op-faq-item summary::-webkit-details-marker{display:none}.op-faq-item summary:hover{background:var(--op-bg)}.op-faq-icon{width:28px;height:28px;border-radius:50%;background:var(--op-bg);border:1.5px solid var(--op-border);display:grid;place-items:center;font-size:18px;color:var(--op-soft);line-height:1}.op-faq-item[open] .op-faq-icon{transform:rotate(45deg);background:var(--op-teal-s);border-color:var(--op-teal);color:var(--op-teal-d)}.op-faq-body{padding:18px 24px 20px;font-size:15px;line-height:1.7;color:var(--op-muted);border-top:1px solid var(--op-border);margin:0}
.op-form-section{background:radial-gradient(ellipse at 95% 5%,rgba(13,148,136,.12),transparent 38%),var(--op-bg)}.op-form-shell{background:#fff;border:1.5px solid var(--op-border);border-radius:32px;padding:clamp(32px,5vw,56px);box-shadow:var(--op-sh-lg);max-width:900px;margin:0 auto}
.op-form-host-row{display:flex;align-items:center;gap:14px;margin-bottom:32px;padding:16px 20px;background:var(--op-bg);border:1px solid var(--op-border);border-radius:18px}.op-form-host-avatar{width:52px;height:52px;border-radius:50%;flex-shrink:0;background:linear-gradient(135deg,var(--op-teal),var(--op-teal-d));display:grid;place-items:center;font-family:"Syne",sans-serif;font-size:18px;font-weight:800;color:#fff;overflow:hidden}.op-form-host-avatar img{width:100%;height:100%;object-fit:cover}.op-form-host-name{font-weight:800;font-size:15px}.op-form-host-role{font-size:12px;color:var(--op-soft)}.op-form-host-badge{display:inline-flex;align-items:center;gap:5px;margin-top:5px;font-size:11px;font-weight:900;background:#dcfce7;color:#15803d;padding:2px 9px;border-radius:999px}
.op-form-head{text-align:center;margin-bottom:36px}.op-form-head .op-eyebrow{justify-content:center}.op-form-head .op-h2{margin:0 auto 16px}.op-form-head p{color:var(--op-muted);font-size:16px;line-height:1.7;max-width:620px;margin:0 auto}
.op-form-note{margin-top:12px;font-size:12.5px;color:var(--op-soft);text-align:center}.op-form-note a{color:var(--op-teal);font-weight:700}.op-form-trust{display:flex;justify-content:center;flex-wrap:wrap;gap:8px;margin-top:20px}.op-form-trust-item{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border:1px solid var(--op-border);border-radius:999px;font-size:12px;color:var(--op-soft);background:var(--op-bg)}.op-form-trust-item:before{content:"✓";color:#16a34a;font-weight:900}
.offer-qualifier h3{font-family:"Syne",sans-serif;font-size:clamp(24px,3vw,36px);margin:0 0 18px}.quiz-options{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.quiz-options button{border:1px solid var(--op-border2);background:#fff;border-radius:14px;padding:14px 16px;font-weight:700;cursor:pointer}.quiz-options button:hover{border-color:var(--op-teal);background:var(--op-teal-s)}
.ups-exit-modal{position:fixed;inset:0;z-index:999}.ups-exit-modal__backdrop{position:absolute;inset:0;background:rgba(2,6,23,.66)}.ups-exit-modal__card{position:relative;z-index:2;max-width:560px;margin:8vh auto 0;background:#fff;border-radius:20px;padding:24px;border:1px solid var(--op-border)}.ups-exit-modal__x{position:absolute;top:8px;right:10px;border:0;background:transparent;font-size:28px;cursor:pointer}
@media(max-width:1080px){.op-services-grid{grid-template-columns:1fr;max-width:520px;margin-left:auto;margin-right:auto}.op-hero-inner{grid-template-columns:1fr}.op-hero-aside{position:static}.op-timeline{grid-template-columns:1fr 1fr}.op-timeline:before{display:none}.op-stat-grid{grid-template-columns:1fr 1fr}}
@media(max-width:760px){.op-decision-row{grid-template-columns:1fr}.op-services-grid{max-width:100%}.op-chooser-grid{grid-template-columns:1fr}.op-audience-grid{grid-template-columns:1fr}.op-timeline{grid-template-columns:1fr}.op-mid-cta{flex-direction:column;padding:24px}.op-process-intro{flex-direction:column}.op-nav-links a{font-size:12px;padding:0 9px}.quiz-options{grid-template-columns:1fr}}
@media(max-width:480px){.op-stat-grid{grid-template-columns:1fr}.op-hero h1{letter-spacing:-1.5px}}
</style>

<main class="op" id="top">
  <nav class="op-nav" aria-label="Nawigacja po ofercie">
    <div class="op-wrap">
      <div class="op-nav-inner">
        <div class="op-nav-links">
          <a href="#oferta" data-cta="nav-oferta" data-cta-section="anchor-nav" data-cta-position="link">Oferta</a>
          <a href="#google-ads" data-cta="nav-google-ads" data-cta-section="anchor-nav" data-cta-position="link">Google Ads</a>
          <a href="#meta-ads" data-cta="nav-meta-ads" data-cta-section="anchor-nav" data-cta-position="link">Meta Ads</a>
          <a href="#strony-www" data-cta="nav-strony-www" data-cta-section="anchor-nav" data-cta-position="link">Strony WWW</a>
          <a href="#wybor" data-cta="nav-wybor" data-cta-section="anchor-nav" data-cta-position="link">Co wybrać?</a>
          <a href="#proces" data-cta="nav-proces" data-cta-section="anchor-nav" data-cta-position="link">Proces</a>
          <a href="#faq" data-cta="nav-faq" data-cta-section="anchor-nav" data-cta-position="link">FAQ</a>
        </div>
        <a href="#formularz-oferta" class="op-nav-cta" data-cta="nav-diagnoza" data-cta-section="anchor-nav" data-cta-position="primary">Chcę bezpłatną diagnozę →</a>
      </div>
    </div>
  </nav>

  <section class="op-hero" id="start" data-offer-section="hero">
    <div class="op-wrap">
      <div class="op-hero-inner">
        <div class="op-hero-copy">
          <div class="op-hero-eyebrow">Oferta Upsellio</div>
          <h1>Google Ads, Meta Ads<br>i strony, które zamieniają<br>ruch w <em>klientów</em></h1>
          <p class="op-hero-lead">Pomagam firmom B2B, usługowym i e-commerce poukładać marketing tak, żeby reklamy i strona nie działały osobno. Razem prowadzą odwiedzającego od pierwszego kliknięcia do decyzji.</p>
          <div class="op-hero-actions">
            <a href="#oferta" class="op-btn op-btn-primary" data-cta="hero-zobacz-oferte" data-cta-section="hero" data-cta-position="primary">Zobacz pełną ofertę</a>
            <a href="#wybor" class="op-btn op-btn-secondary" data-cta="hero-wybor" data-cta-section="hero" data-cta-position="secondary">Nie wiem, co wybrać</a>
            <a href="#formularz-oferta" class="op-btn op-btn-ghost" data-cta="hero-diagnoza" data-cta-section="hero" data-cta-position="tertiary">Bezpłatna diagnoza</a>
          </div>
        </div>

        <aside class="op-hero-aside">
          <div class="op-metric-card">
            <div class="op-metric-card-head">
              <div class="op-metric-card-avatar">
                <?php if ($offer_founder_photo) : ?>
                  <img src="<?php echo esc_url($offer_founder_photo); ?>" alt="<?php echo esc_attr($offer_founder_name); ?>">
                <?php else : ?>
                  <?php echo esc_html($offer_founder_initials ?: "SK"); ?>
                <?php endif; ?>
              </div>
              <div>
                <div class="op-metric-card-name"><?php echo esc_html($offer_founder_name); ?></div>
                <div class="op-metric-card-role"><?php echo esc_html($offer_founder_role); ?> · Upsellio</div>
                <div class="op-metric-card-status">Dostępny</div>
              </div>
            </div>

            <div class="op-kpi-grid">
              <div class="op-kpi-cell"><span class="op-kpi-val">362</span><span class="op-kpi-label">leadów / miesiąc</span><span class="op-kpi-change up">↑ +28%</span></div>
              <div class="op-kpi-cell"><span class="op-kpi-val">37 zł</span><span class="op-kpi-label">koszt leada (CPL)</span><span class="op-kpi-change dn">↓ -18%</span></div>
              <div class="op-kpi-cell"><span class="op-kpi-val">2,3%</span><span class="op-kpi-label">konwersja strony</span><span class="op-kpi-change up">↑ z 0,9%</span></div>
              <div class="op-kpi-cell"><span class="op-kpi-val">72%</span><span class="op-kpi-label">lead quality</span><span class="op-kpi-change up">↑ kwalifikacja</span></div>
            </div>

            <div class="op-metric-funnel">
              <div class="op-funnel-label">Lejek pozyskiwania</div>
              <?php foreach ([["Ruch", 100], ["Strona", 42], ["Lead", 18], ["Rozmowa", 9], ["Klient", 4]] as $funnel) : ?>
                <div class="op-funnel-row">
                  <span class="op-funnel-name"><?php echo esc_html($funnel[0]); ?></span>
                  <div class="op-funnel-bar-wrap"><div class="op-funnel-bar" style="width:<?php echo (int) $funnel[1]; ?>%"></div></div>
                  <span class="op-funnel-pct"><?php echo (int) $funnel[1]; ?>%</span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </aside>
      </div>

      <div class="op-decision-row" id="oferta">
        <a href="#google-ads" class="op-decision-card google"><span class="op-decision-icon">✓</span><span><strong>Mam popyt w Google</strong><span>Klienci już szukają Twojej usługi lub produktu</span></span></a>
        <a href="#meta-ads" class="op-decision-card meta"><span class="op-decision-icon">✓</span><span><strong>Chcę budować świadomość</strong><span>Potrzebuję dotrzeć do nowych grup odbiorców</span></span></a>
        <a href="#strony-www" class="op-decision-card web"><span class="op-decision-icon">✓</span><span><strong>Strona nie konwertuje</strong><span>Mam ruch, ale za mało zapytań od klientów</span></span></a>
      </div>
    </div>
  </section>

  <section class="op-section op-section-border op-section-white" data-offer-section="services">
    <div class="op-wrap">
      <div class="op-eyebrow">Co robię</div>
      <h2 class="op-h2">Trzy obszary, które decydują o pozyskiwaniu klientów online.</h2>
      <p class="op-body">Większość firm ma ruch lub budżet reklamowy, ale strona nie zamienia odwiedzających w kontakty. Moja oferta łączy trzy elementy w jeden spójny system.</p>

      <div class="op-services-grid">
        <article class="op-service-card google" id="google-ads">
          <div class="op-service-icon">G</div>
          <h3>Google Ads dla firm B2B</h3>
          <p>Docieraj do klientów w chwili, gdy aktywnie szukają Twojej usługi lub produktu. Przechwytuj istniejący popyt zanim trafi do konkurencji.</p>
          <ul class="op-check-list">
            <li>Audyt kampanii lub budowa od zera</li>
            <li>Słowa kluczowe z wysoką intencją</li>
            <li>Optymalizacja landing page pod konwersję</li>
            <li>Śledzenie konwersji w GA4</li>
            <li>Stała optymalizacja stawek i struktur</li>
            <li>Raporty pod leady i CPL</li>
          </ul>
          <hr class="op-service-divider">
          <p class="op-service-rich">Google Ads trafia do osób gotowych podjąć decyzję zakupową. Klucz to spójność reklamy ze stroną i kontrola kosztu leada.</p>
          <?php if ($google_ads_url) : ?><a href="<?php echo esc_url($google_ads_url); ?>" class="op-service-link">Szczegóły Google Ads →</a><?php endif; ?>
        </article>

        <article class="op-service-card meta" id="meta-ads">
          <div class="op-service-icon">M</div>
          <h3>Meta Ads — Facebook i Instagram</h3>
          <p>Buduj popyt i docieraj do nowych klientów, zanim zaczną szukać w Google. Reklamy do precyzyjnie określonej grupy decydentów.</p>
          <ul class="op-check-list">
            <li>Analiza person i lejka</li>
            <li>Kampanie ToF / MoF / BoF</li>
            <li>Testy kreacji i komunikatów</li>
            <li>Remarketing</li>
            <li>Optymalizacja jakości leadów</li>
            <li>Spójność reklama → strona</li>
          </ul>
          <hr class="op-service-divider">
          <p class="op-service-rich">Meta Ads buduje świadomość i zwiększa liczbę wartościowych kontaktów w etapach, zanim użytkownik wpisze frazę w Google.</p>
          <?php if ($meta_ads_url) : ?><a href="<?php echo esc_url($meta_ads_url); ?>" class="op-service-link">Szczegóły Meta Ads →</a><?php endif; ?>
        </article>

        <article class="op-service-card web" id="strony-www">
          <div class="op-service-icon">W</div>
          <h3>Strony internetowe pod konwersję</h3>
          <p>Strona, która przekonuje, buduje zaufanie i zamienia ruch w zapytania sprzedażowe. Centrum systemu marketingowego.</p>
          <ul class="op-check-list">
            <li>Audyt i struktura treści</li>
            <li>Copywriting sprzedażowy</li>
            <li>Sekcje zaufania i CTA</li>
            <li>Integracja z kampaniami</li>
            <li>Śledzenie konwersji</li>
            <li>Lepsza jakość leadów</li>
          </ul>
          <hr class="op-service-divider">
          <p class="op-service-rich">Nawet najlepsza kampania nie domknie sprzedaży, jeśli strona nie prowadzi użytkownika do kontaktu i decyzji.</p>
          <?php if ($websites_url) : ?><a href="<?php echo esc_url($websites_url); ?>" class="op-service-link">Szczegóły stron WWW →</a><?php endif; ?>
        </article>
      </div>

      <div class="op-mid-cta">
        <div>
          <strong>Nie wiesz, która usługa ma największy sens teraz?</strong>
          <p>W wielu firmach problem nie leży w reklamach, tylko w stronie, ofercie lub ścieżce kontaktu. Sprawdzę, od czego zacząć — bezpłatnie.</p>
        </div>
        <a href="#formularz-oferta" class="op-btn op-btn-primary" data-cta="services-diagnoza" data-cta-section="services" data-cta-position="primary">Chcę diagnozę</a>
      </div>
    </div>
  </section>

  <div class="op-stat-bar">
    <div class="op-wrap">
      <div class="op-stat-grid">
        <div class="op-stat-cell"><span class="op-stat-val">+28%</span><span class="op-stat-label">więcej leadów<br>miesięcznie (śr.)</span></div>
        <div class="op-stat-cell"><span class="op-stat-val">-18%</span><span class="op-stat-label">niższy koszt<br>pozyskania leada</span></div>
        <div class="op-stat-cell"><span class="op-stat-val">10+</span><span class="op-stat-label">lat praktyki<br>w sprzedaży B2B</span></div>
        <div class="op-stat-cell"><span class="op-stat-val">1:1</span><span class="op-stat-label">pracujesz ze mną,<br>nie z juniorami</span></div>
      </div>
    </div>
  </div>

  <section class="op-section op-section-border op-section-soft" id="wybor" data-offer-section="wybor">
    <div class="op-wrap">
      <div class="op-eyebrow">Szybki wybór</div>
      <h2 class="op-h2">Google Ads, Meta Ads czy nowa strona? Jak wybrać właściwy punkt startowy?</h2>
      <p class="op-body">Odpowiedź zależy od tego, czy problem jest po stronie ruchu czy konwersji. Poniżej trzy scenariusze.</p>
      <div class="op-chooser-grid">
        <div class="op-chooser-card google"><div class="op-chooser-tag">Wybierz Google Ads</div><h3>Gdy klient już szuka</h3><p>Search Ads przechwytuje gotowy popyt i kieruje użytkownika na właściwy landing.</p><?php if ($google_ads_url) : ?><a href="<?php echo esc_url($google_ads_url); ?>" class="op-chooser-link">Przejdź do Google Ads →</a><?php endif; ?></div>
        <div class="op-chooser-card meta"><div class="op-chooser-tag">Wybierz Meta Ads</div><h3>Gdy trzeba zbudować uwagę</h3><p>Meta Ads buduje świadomość i dostarcza jakościowy ruch na kolejne etapy lejka.</p><?php if ($meta_ads_url) : ?><a href="<?php echo esc_url($meta_ads_url); ?>" class="op-chooser-link">Przejdź do Meta Ads →</a><?php endif; ?></div>
        <div class="op-chooser-card web"><div class="op-chooser-tag">Wybierz stronę WWW</div><h3>Gdy ruch nie zamienia się w leady</h3><p>Jeśli masz ruch, a nie masz zapytań, poprawa strony i komunikatu da największy efekt.</p><?php if ($websites_url) : ?><a href="<?php echo esc_url($websites_url); ?>" class="op-chooser-link">Przejdź do stron WWW →</a><?php endif; ?></div>
      </div>
    </div>
  </section>

  <section class="op-process-section" id="proces" data-offer-section="proces">
    <div class="op-wrap">
      <div class="op-process-intro">
        <div>
          <div class="op-eyebrow">Jak pracuję</div>
          <h2 class="op-h2">Diagnoza najpierw.<br>Działanie potem.</h2>
          <p class="op-body">Najpierw rozumiem, gdzie blokuje się pozyskiwanie klientów. Potem wdrażam plan i mierzę wynik.</p>
        </div>
        <div><a href="#formularz-oferta" class="op-btn op-btn-primary" data-cta="proces-diagnoza" data-cta-section="proces" data-cta-position="primary">Zacznijmy od diagnozy →</a></div>
      </div>
      <div class="op-timeline">
        <?php foreach ([["1","Analiza","Tydzień 1","Sprawdzam stronę, kampanie, ofertę i ścieżkę klienta."],["2","Strategia","Tydzień 2","Ustalam priorytety i KPI."],["3","Wdrożenie","Tydzień 3–4","Buduję kampanie, treści i CTA."],["4","Optymalizacja","Stały proces","Iteracyjnie poprawiam jakość leadów i CPL."]] as $step) : ?>
          <div class="op-timeline-step"><div class="op-timeline-num-wrap"><div class="op-timeline-num"><?php echo esc_html($step[0]); ?></div></div><div class="op-timeline-step-icon">•</div><strong><?php echo esc_html($step[1]); ?></strong><p><?php echo esc_html($step[3]); ?></p><span class="op-timeline-duration"><?php echo esc_html($step[2]); ?></span></div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="op-section op-section-border op-section-soft" id="faq" data-offer-section="faq">
    <div class="op-wrap">
      <div class="op-eyebrow">FAQ</div>
      <h2 class="op-h2">Najczęstsze pytania przed rozpoczęciem współpracy.</h2>
      <div class="op-faq-list">
        <?php foreach ($offer_faq_items as $faq) : ?>
          <details class="op-faq-item">
            <summary><span><?php echo esc_html($faq["question"]); ?></span><span class="op-faq-icon" aria-hidden="true">+</span></summary>
            <p class="op-faq-body"><?php echo esc_html($faq["answer"]); ?></p>
          </details>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <?php get_template_part("template-parts/offer/qualifier"); ?>

  <section class="op-section op-form-section" id="formularz-oferta" data-offer-section="formularz">
    <div class="op-wrap">
      <div class="op-form-shell">
        <div class="op-form-host-row">
          <div class="op-form-host-avatar">
            <?php if ($offer_founder_photo) : ?><img src="<?php echo esc_url($offer_founder_photo); ?>" alt="<?php echo esc_attr($offer_founder_name); ?>"><?php else : ?><?php echo esc_html($offer_founder_initials ?: "SK"); ?><?php endif; ?>
          </div>
          <div>
            <div class="op-form-host-name"><?php echo esc_html($offer_founder_name); ?></div>
            <div class="op-form-host-role"><?php echo esc_html($offer_founder_role); ?> · Upsellio</div>
            <div class="op-form-host-badge">Odpowiadam w 24h</div>
          </div>
        </div>

        <div class="op-form-head">
          <div class="op-eyebrow">Bezpłatna diagnoza</div>
          <h2 class="op-h2">Nie wiesz, od czego zacząć? Zacznijmy od diagnozy.</h2>
          <p>Opisz sytuację, a wrócę z konkretną rekomendacją działań.</p>
        </div>

        <?php
        $offer_service_choices = array_values(array_unique(array_filter(array_merge(
            ["Google Ads B2B", "Meta Ads B2B", "Tworzenie strony / landing page", "Audyt kampanii lub strony", "Nie wiem, co wybrać — chcę porozmawiać"],
            is_array($contact_service_options) ? $contact_service_options : []
        ), static fn($v) => trim((string) $v) !== "")));

        if (function_exists("upsellio_render_lead_form")) {
            echo upsellio_render_lead_form([
                "origin" => "offer-page-form",
                "variant" => "full",
                "submit_label" => "Wyślij zapytanie →",
                "redirect_url" => ($offer_page_url ?: home_url("/oferta/")) . "#formularz-oferta",
                "service_options" => $offer_service_choices,
                "form_id" => "contact-form",
                "submit_button_id" => "submit-btn",
            ]);
        }
        ?>

        <p class="op-form-note">Dane z formularza służą wyłącznie do kontaktu. Napisz: <a href="<?php echo esc_url($contact_email_href); ?>"><?php echo esc_html($contact_email_display); ?></a> lub zadzwoń: <a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $contact_phone)); ?>"><?php echo esc_html($contact_phone); ?></a></p>
        <div class="op-form-trust"><span class="op-form-trust-item">Bez presji sprzedażowej</span><span class="op-form-trust-item">Odpowiadam osobiście</span><span class="op-form-trust-item">Bezpłatna diagnoza</span><span class="op-form-trust-item">Odpowiedź w 24h</span></div>
      </div>
    </div>
  </section>
  <div id="ups-exit-intent" class="ups-exit-modal" hidden>
    <div class="ups-exit-modal__backdrop" data-exit-close></div>
    <div class="ups-exit-modal__card">
      <button class="ups-exit-modal__x" data-exit-close aria-label="Zamknij">&times;</button>
      <h3>Zanim wyjdziesz - wez 5-minutowy audyt</h3>
      <p>Zostaw e-mail, dostaniesz checkliste "10 rzeczy, ktore zabijaja konwersje na stronie oferty".</p>
      <?php
      if (function_exists("upsellio_render_lead_form")) {
          echo upsellio_render_lead_form([
              "origin" => "offer-exit-intent",
              "variant" => "micro",
              "submit_label" => "Wyslij checkliste",
              "redirect_url" => home_url("/oferta/?ups_lead_status=success#oferta"),
          ]);
      }
      ?>
    </div>
  </div>
</main>

<script>
(function () {
  const links = document.querySelectorAll(".op-nav-links a");
  const sections = [];
  links.forEach((a) => {
    const id = (a.getAttribute("href") || "").replace("#", "");
    const el = id ? document.getElementById(id) : null;
    if (el) sections.push({ el, a });
  });
  const obs = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      links.forEach((a) => a.classList.remove("active"));
      const hit = sections.find((s) => s.el === entry.target);
      if (hit) hit.a.classList.add("active");
    });
  }, { rootMargin: "-40% 0px -55% 0px" });
  sections.forEach((s) => obs.observe(s.el));
})();
</script>

<?php
if (function_exists("upsellio_render_faq_schema")) {
    upsellio_render_faq_schema($offer_faq_items);
}
if (function_exists("upsellio_render_service_schema")) {
    upsellio_render_service_schema(
        "Google Ads, Meta Ads i strony internetowe dla firm",
        "Oferta Upsellio: kampanie Google Ads, Meta Ads oraz strony internetowe nastawione na leady i konwersję dla firm B2B.",
        "/oferta/",
        "Marketing internetowy i tworzenie stron"
    );
}
if (function_exists("upsellio_render_breadcrumb_schema")) {
    upsellio_render_breadcrumb_schema([
        ["name" => "Strona glowna", "url" => home_url("/")],
        ["name" => "Oferta", "url" => home_url("/oferta/")],
    ]);
}
get_footer();
?>
