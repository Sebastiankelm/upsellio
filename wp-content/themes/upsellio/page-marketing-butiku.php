<?php
/**
 * Template Name: Marketing dla butiku — landing reklamowy
 *
 * Landing pod ruch z reklam (Google / Meta) dla właścicielek butików odzieżowych.
 */
if (!defined("ABSPATH")) {
    exit;
}

$front_page_sections = function_exists("upsellio_get_front_page_content_config")
    ? upsellio_get_front_page_content_config()
    : [];
$contact_phone = function_exists("upsellio_get_contact_phone")
    ? upsellio_get_contact_phone()
    : trim((string) ($front_page_sections["contact_phone"] ?? "+48 575 522 595"));
$contact_phone_href = preg_replace("/\s+/", "", (string) $contact_phone);
$contact_email = trim((string) ($front_page_sections["contact_email"] ?? "kontakt@upsellio.pl"));
$current_page_url = function_exists("get_permalink") ? (string) (get_permalink() ?: home_url("/")) : home_url("/");
$privacy_url = home_url("/polityka-prywatnosci/");
$founder_name = "Sebastian Kelm";

$hero_photo_html = "";
$hero_photo_alt = "Sebastian Kelm – marketing i reklamy dla butików odzieżowych";
$photo_args = [
    "class" => "btk-hero-photo",
    "size" => "large",
    "loading" => "eager",
    "fetchpriority" => "high",
    "alt" => $hero_photo_alt,
];
if (function_exists("upsellio_get_home_media_slot") && function_exists("upsellio_render_home_media_image")) {
    foreach (["hero_portrait", "about_portrait"] as $photo_slot) {
        $slot = upsellio_get_home_media_slot($photo_slot);
        if ((int) ($slot["attachment_id"] ?? 0) > 0) {
            $hero_photo_html = upsellio_render_home_media_image($photo_slot, $photo_args);
            break;
        }
    }
}
if ($hero_photo_html === "" && function_exists("upsellio_render_template_asset_image")) {
    $hero_photo_html = upsellio_render_template_asset_image("founder_main", [
        "class" => "btk-hero-photo",
        "size" => "large",
        "loading" => "eager",
        "no_fallback" => true,
        "alt" => $hero_photo_alt,
    ]);
}

add_action("wp_enqueue_scripts", static function () {
    wp_enqueue_style(
        "upsellio-butik-fonts",
        "https://fonts.googleapis.com/css2?family=Great+Vibes&family=Montserrat:wght@400;500;600;700;800&display=swap",
        [],
        null
    );
});

add_filter("pre_get_document_title", static function () {
    return "Marketing dla butików — więcej klientek i zamówień | Upsellio";
});

add_action("wp_head", static function () {
    echo '<meta name="description" content="Marketing dla butików odzieżowych: Instagram, Meta Ads i Google Ads, które dowożą klientki do sklepu i do koszyka. Bezpłatna konsultacja.">' . "\n";
}, 5);

get_header();
?>

<style>
body.page-template-page-marketing-butiku-php .site-nav,
body.page-template-page-marketing-butiku-php .skip-link,
body.page-template-page-marketing-butiku-php .ups-breadcrumbs,
body.page-template-page-marketing-butiku-php .mobile-sticky-cta {
  display: none !important;
}
body.page-template-page-marketing-butiku-php {
  --pink: #e83a7a;
  --pink-d: #c92d68;
  --pink-s: #fde8f0;
  --pink-ss: #fff6f9;
  --ink: #161616;
  --muted: #5c5c5c;
  --line: #f0dbe4;
  --radius: 18px;
  background: #fff;
  padding-bottom: calc(84px + env(safe-area-inset-bottom, 0px));
}
@media (min-width: 1025px) {
  body.page-template-page-marketing-butiku-php { padding-bottom: 0; }
}

.btk {
  font-family: "Montserrat", "DM Sans", system-ui, sans-serif;
  color: var(--ink);
  background: #fff;
  overflow-x: hidden;
}
.btk *, .btk *::before, .btk *::after { box-sizing: border-box; }
.btk a { color: inherit; text-decoration: none; }
.btk-wrap { width: min(1180px, calc(100% - 32px)); margin-inline: auto; }
.btk-script { font-family: "Great Vibes", cursive; font-weight: 400; letter-spacing: 0; }
.btk section[id] { scroll-margin-top: 72px; }

/* Header — mobile first */
.btk-nav {
  position: sticky; top: 0; z-index: 40;
  background: rgba(255,255,255,.94);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(232,58,122,.08);
}
.btk-nav-inner {
  display: flex; align-items: center; justify-content: space-between; gap: 12px;
  min-height: 58px;
}
.btk-logo {
  display: flex; align-items: center; gap: 8px;
  font-weight: 800; font-size: 11px; letter-spacing: .06em; text-transform: uppercase;
  color: var(--ink); line-height: 1.15; max-width: calc(100% - 56px);
}
.btk-logo svg { width: 30px; height: 30px; flex-shrink: 0; }
.btk-links { display: none; list-style: none; margin: 0; padding: 0; }
.btk-links a { font-size: 15px; font-weight: 600; color: #3a3a3a; min-height: 44px; display: flex; align-items: center; }
.btk-links a:hover { color: var(--pink); }
.btk-nav-cta {
  display: inline-flex; align-items: center; justify-content: center;
  background: var(--pink); color: #fff !important;
  padding: 12px 22px; border-radius: 999px;
  font-size: 12px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase;
  box-shadow: 0 8px 18px rgba(232,58,122,.28);
}
.btk-nav-cta:hover { background: var(--pink-d); }
.btk-nav-actions { display: none; }
.btk-nav-phone {
  display: inline-flex; align-items: center; gap: 8px;
  font-size: 14px; font-weight: 700; color: var(--ink); white-space: nowrap; letter-spacing: -.01em;
}
.btk-nav-phone:hover { color: var(--pink); }
.btk-nav-phone svg { width: 16px; height: 16px; color: var(--pink); }
.btk-burger {
  display: grid; place-items: center; width: 44px; height: 44px; border: 0; background: transparent; cursor: pointer; flex-shrink: 0;
}
.btk-burger span { display: block; width: 20px; height: 2px; background: var(--ink); margin: 4px auto; transition: transform .22s ease, opacity .18s ease; }
.btk-nav.is-open .btk-burger span:nth-child(1) { transform: translateY(6px) rotate(45deg); }
.btk-nav.is-open .btk-burger span:nth-child(2) { opacity: 0; }
.btk-nav.is-open .btk-burger span:nth-child(3) { transform: translateY(-6px) rotate(-45deg); }
.btk-nav.is-open .btk-links {
  display: flex; flex-direction: column; position: absolute; top: 58px; left: 0; right: 0;
  background: #fff; padding: 8px 20px 20px; gap: 4px; border-bottom: 1px solid var(--line);
  box-shadow: 0 16px 32px rgba(22,22,22,.08);
}

/* Hero */
.btk-hero {
  background: linear-gradient(180deg, var(--pink-ss) 0%, #fff 100%);
  padding: 20px 0 40px;
}
.btk-hero-grid { display: grid; grid-template-columns: 1fr; gap: 22px; align-items: start; }
.btk-eyebrow {
  display: inline-block; margin-bottom: 10px;
  color: var(--pink); font-size: 11px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase;
}
.btk-hero h1 {
  margin: 0 0 14px; font-size: clamp(28px, 8.4vw, 40px);
  line-height: 1.08; letter-spacing: -.03em; font-weight: 800;
}
.btk-hero h1 em { font-style: normal; color: var(--pink); }
.btk-lead { margin: 0 0 18px; max-width: 46ch; color: var(--muted); font-size: 15px; line-height: 1.55; }
.btk-checks { list-style: none; margin: 0 0 20px; padding: 0; display: grid; gap: 10px; }
.btk-checks li { display: flex; align-items: flex-start; gap: 10px; font-size: 14px; font-weight: 600; line-height: 1.35; }
.btk-check {
  width: 22px; height: 22px; border-radius: 50%; background: var(--pink);
  color: #fff; display: grid; place-items: center; flex-shrink: 0; margin-top: 1px;
}
.btk-hero-cta { display: grid; gap: 10px; }
.btk-btn {
  display: inline-flex; align-items: center; justify-content: center; width: 100%;
  background: var(--pink); color: #fff !important;
  min-height: 52px; padding: 14px 20px; border-radius: 999px; border: 0;
  font-family: inherit; font-size: 13px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase;
  box-shadow: 0 12px 28px rgba(232,58,122,.28); cursor: pointer;
  transition: background .22s ease, transform .22s ease, box-shadow .22s ease, border-color .22s ease, color .22s ease;
}
.btk-btn:hover { background: var(--pink-d); color: #fff; }
.btk-btn-ghost {
  background: #fff; color: var(--ink) !important;
  border: 1.5px solid var(--line); box-shadow: none;
}
.btk-btn-ghost:hover { border-color: var(--pink); color: var(--pink) !important; background: #fff; }
.btk-social {
  display: flex; align-items: center; gap: 12px; margin-top: 16px; color: var(--muted); font-size: 12.5px; font-weight: 600; line-height: 1.4;
}
.btk-hero-visual { position: relative; overflow: hidden; border-radius: 20px; }
.btk-hero-visual img, .btk-hero-photo {
  width: 100%; height: 280px; object-fit: cover; object-position: 50% 18%;
  border-radius: 20px; display: block;
  box-shadow: 0 16px 36px rgba(22,22,22,.12);
  transition: transform .7s ease;
}
.btk-hero-visual .home-media-fallback,
.btk-hero-visual .tpl-asset-fallback {
  width: 100%; height: 280px; border-radius: 20px;
  background: var(--pink-s); color: var(--pink); display: grid; place-items: center;
  font-size: 28px; font-weight: 800;
}
.btk-h2 { margin: 0 0 10px; text-align: center; font-size: clamp(26px, 7vw, 44px); letter-spacing: -.03em; font-weight: 800; }

.btk-proofbar {
  margin-top: 8px; padding: 18px 0 0;
  border-top: 1px solid var(--line);
}
.btk-proofbar-label {
  margin: 0 auto 14px; text-align: center; max-width: 42ch;
  color: var(--muted); font-size: 13px; font-weight: 600; line-height: 1.45;
}
.btk-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0; }
.btk-stat { text-align: center; padding: 0 8px; }
.btk-stat + .btk-stat { border-left: 1px solid var(--line); }
.btk-stat strong {
  display: block; margin-bottom: 5px;
  color: var(--pink); font-size: clamp(22px, 5.4vw, 32px);
  font-weight: 800; letter-spacing: -.03em; line-height: 1;
}
.btk-stat span {
  display: block; color: var(--muted);
  font-size: 11.5px; font-weight: 600; line-height: 1.35;
}
.btk-pain { padding: 48px 0 40px; }
.btk-pain-head { text-align: center; max-width: 640px; margin: 0 auto 22px; }
.btk-pain-head .btk-eyebrow { margin-bottom: 10px; }
.btk-pain-head h2 { margin: 0 0 10px; }
.btk-pain-head p { margin: 0; color: var(--muted); font-size: 15px; line-height: 1.55; }
.btk-pain-grid { display: grid; grid-template-columns: 1fr; gap: 12px; }
.btk-sit {
  background: #fff; border: 1px solid var(--line); border-radius: 18px;
  padding: 20px 18px 16px; text-align: left;
  display: flex; flex-direction: column; gap: 0;
}
.btk-icon {
  width: 48px; height: 48px; margin: 0 0 12px; border-radius: 50%;
  background: var(--pink-ss); color: var(--pink); display: grid; place-items: center;
  box-shadow: 0 8px 18px rgba(232,58,122,.12); flex-shrink: 0;
}
.btk-sit h3 { margin: 0 0 10px; font-size: 17px; }
.btk-sit blockquote {
  margin: 0 0 8px; font-size: 14.5px; font-weight: 600; line-height: 1.45; color: var(--ink);
}
.btk-sit p { margin: 0 0 12px; color: var(--muted); font-size: 13.5px; line-height: 1.55; }
.btk-sit small {
  display: block; margin-top: auto; padding-top: 4px;
  font-size: 11px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--pink);
}
.btk-call {
  margin-top: 16px;
  background: var(--pink-ss); border-radius: 18px;
  padding: 22px 20px 20px;
}
.btk-call h3 {
  margin: 0 0 10px;
  font-size: clamp(18px, 4.4vw, 24px);
  letter-spacing: -.02em; line-height: 1.25;
}
.btk-call > p { margin: 0 0 10px; color: var(--muted); font-size: 14px; line-height: 1.5; }
.btk-call ul { margin: 0; padding: 0 0 0 1.15em; }
.btk-call li {
  color: var(--ink); font-size: 14px; line-height: 1.5; margin: 0 0 7px;
}
.btk-call li:last-child { margin-bottom: 0; }
.btk-call-honest {
  margin: 16px 0 0; padding-top: 14px;
  border-top: 1px solid rgba(232,58,122,.2);
  color: var(--ink); font-size: 15.5px; font-weight: 700; line-height: 1.45;
}
.btk-levers { display: grid; gap: 10px; margin-top: 12px; }
.btk-lever {
  background: var(--pink-ss); border-radius: 14px; padding: 14px 16px;
}
.btk-lever strong { display: block; font-size: 13.5px; margin-bottom: 4px; }
.btk-lever span { display: block; font-size: 13px; color: var(--muted); line-height: 1.45; }
.btk-accent-line {
  text-align: center; margin: 28px 0 0; color: var(--pink); font-size: clamp(22px, 6vw, 28px); line-height: 1.35;
}
.btk-pain-cta {
  display: flex; justify-content: center; margin-top: 22px;
}
.btk-pain-cta .btk-btn { max-width: 420px; }

/* Process */
.btk-process { background: var(--pink-ss); padding: 48px 0; }
.btk-steps { display: grid; grid-template-columns: 1fr; gap: 14px; margin-top: 28px; position: relative; }
.btk-step { text-align: left; padding: 18px; background: #fff; border-radius: 16px; position: relative; }
.btk-step:not(:last-child)::after { display: none; }
.btk-step h3 { margin: 10px 0 8px; font-size: 16px; }
.btk-step p { margin: 0; color: var(--muted); font-size: 13.5px; line-height: 1.55; }
.btk-process-cta {
  display: grid; gap: 10px; margin-top: 28px;
}

/* Doubts */
.btk-results { padding: 48px 0; }
.btk-results-grid { display: grid; grid-template-columns: 1fr; gap: 22px; align-items: stretch; }
.btk-doubt-copy { display: flex; flex-direction: column; min-width: 0; }
.btk-results h2 { text-align: left; margin: 0 0 8px; }
.btk-results .btk-lead { margin-bottom: 18px; max-width: 42ch; }
.btk-doubt-q {
  list-style: none; margin: 0; padding: 0;
  display: grid; gap: 10px; flex: 1;
  grid-template-rows: repeat(4, minmax(88px, 1fr));
}
.btk-doubt-q li {
  display: flex; align-items: center; gap: 12px;
  background: #fff; border: 1px solid var(--line); border-radius: 16px;
  padding: 16px 16px 16px 14px; min-height: 88px; height: 100%;
}
.btk-doubt-mark {
  width: 32px; height: 32px; min-width: 32px; min-height: 32px;
  border-radius: 50%; flex: 0 0 32px;
  background: var(--pink-s); color: var(--pink);
  display: grid; place-items: center;
  font-size: 15px; font-weight: 800; line-height: 1;
  padding: 0; overflow: hidden;
}
.btk-doubt-q p { margin: 0; font-size: 14.5px; font-weight: 600; line-height: 1.4; color: var(--ink); }
.btk-doubt-card {
  background: var(--pink-ss); border: 1px solid var(--line); border-radius: 22px;
  padding: 22px 18px 18px;
  display: flex; flex-direction: column; min-width: 0; height: 100%;
}
.btk-doubt-card h3 {
  margin: 0 0 16px; font-size: clamp(20px, 5.4vw, 26px);
  line-height: 1.25; letter-spacing: -.02em;
}
.btk-doubt-facts {
  list-style: none; margin: 0 0 16px; padding: 0;
  display: grid; gap: 10px; flex: 1;
  grid-template-rows: repeat(3, minmax(88px, 1fr));
}
.btk-doubt-facts li {
  display: flex; align-items: center; gap: 12px;
  background: #fff; border-radius: 14px;
  padding: 14px 16px 14px 14px; min-height: 88px; height: 100%;
}
.btk-doubt-ico {
  width: 40px; height: 40px; min-width: 40px; min-height: 40px;
  flex: 0 0 40px; border-radius: 10px;
  background: var(--pink-s); color: var(--pink);
  display: grid; place-items: center;
  padding: 0; margin: 0; overflow: hidden;
  font-size: 0; line-height: 0;
}
.btk-doubt-ico svg {
  display: block; width: 18px !important; height: 18px !important;
  max-width: 18px; max-height: 18px; margin: 0; flex: none;
  overflow: visible;
}
.btk-doubt-facts li > div { flex: 1; min-width: 0; }
.btk-doubt-facts strong { display: block; font-size: 14.5px; line-height: 1.3; }
.btk-doubt-facts span { display: block; margin-top: 3px; font-size: 12.5px; font-weight: 600; color: var(--muted); line-height: 1.4; }
.btk-doubt-card .btk-btn { margin-top: auto; width: 100%; }

/* Pricing */
.btk-pricing { background: var(--pink-ss); padding: 40px 0 48px; }
.btk-sub { text-align: center; color: var(--muted); max-width: 54ch; margin: 0 auto 24px; font-size: 15px; }
.btk-price-ex {
  margin: -10px 0 16px; font-size: 12.5px; font-weight: 600; color: var(--muted);
}
.btk-plans { display: grid; grid-template-columns: 1fr; gap: 14px; align-items: stretch; }
.btk-plan {
  background: #fff; border: 1px solid var(--line); border-radius: 20px;
  padding: 22px 18px 18px; display: flex; flex-direction: column;
  cursor: pointer; text-align: left; width: 100%;
}
.btk-plan.is-featured {
  background: #fff; border: 2px solid var(--pink);
  box-shadow: 0 18px 40px rgba(232,58,122,.14);
}
.btk-plan.is-picked {
  border-color: var(--pink); box-shadow: 0 18px 40px rgba(232,58,122,.18);
  outline: 2px solid var(--pink);
}
.btk-plan-picked {
  display: none; align-self: flex-start; margin-bottom: 10px;
  background: var(--pink); color: #fff; font-size: 10px; font-weight: 800;
  letter-spacing: .08em; text-transform: uppercase; padding: 5px 9px; border-radius: 999px;
}
.btk-plan-flag {
  display: inline-flex; align-self: flex-start; margin-bottom: 10px;
  background: var(--pink-s); color: var(--pink); font-size: 10px; font-weight: 800;
  letter-spacing: .08em; text-transform: uppercase; padding: 5px 9px; border-radius: 999px;
}
.btk-plan.is-featured .btk-plan-flag { background: var(--pink); color: #fff; }
.btk-plan.is-private {
  background: #161616; border-color: #161616; color: #fff;
  box-shadow: 0 18px 40px rgba(22,22,22,.18);
}
.btk-plan.is-private h3, .btk-plan.is-private .btk-price { color: #fff; }
.btk-plan.is-private li { color: rgba(255,255,255,.88); }
.btk-plan.is-private .btk-plan-flag { background: #e83a7a; color: #fff; }
.btk-plan.is-private .btk-check { background: #e83a7a; }
.btk-plan.is-private.is-picked { outline-color: #e83a7a; }
.btk-plan h3 { margin: 0 0 6px; font-size: 20px; letter-spacing: .04em; }
.btk-plan-for {
  margin: 0 0 14px; font-size: 14.5px; font-weight: 600; line-height: 1.4; color: var(--ink);
}
.btk-plan.is-private .btk-plan-for { color: rgba(255,255,255,.9); }
.btk-price { margin: 0 0 18px; font-size: 26px; font-weight: 800; }
.btk-price small { font-size: 13px; font-weight: 600; color: var(--muted); }
.btk-plan ul { list-style: none; margin: 0 0 18px; padding: 0; display: grid; gap: 10px; flex: 1; }
.btk-plan li { display: flex; gap: 8px; font-size: 13.5px; line-height: 1.45; color: #3a3a3a; }

/* Contact / form */
.btk-contact { padding: 48px 0 108px; }
.btk-contact-grid { display: grid; grid-template-columns: 1fr; gap: 22px; align-items: start; }
.btk-contact .btk-h2 { text-align: left; }
.btk-form-card {
  background: #fff; border: 1px solid var(--line); border-radius: 20px; padding: 20px 16px;
  box-shadow: 0 16px 40px rgba(22,22,22,.06);
}
.btk-form-card .ups-form { display: grid; gap: 12px; }
.btk-form-card .ups-form__row-2 { display: grid; grid-template-columns: 1fr; gap: 12px; }
.btk-form-card label, .btk-form-card .ups-form__label { font-size: 13px; font-weight: 700; display: block; margin-bottom: 6px; }
.btk-form-card input:not([type="checkbox"]):not([type="hidden"]),
.btk-form-card textarea {
  width: 100%; min-height: 48px; border: 1.5px solid #f0d6e0; border-radius: 12px;
  padding: 12px 14px; font-family: inherit; font-size: 16px; background: var(--pink-ss);
}
.btk-form-card textarea { min-height: 88px; resize: vertical; }
.btk-form-card input:focus, .btk-form-card textarea:focus {
  outline: none; border-color: var(--pink); box-shadow: 0 0 0 3px rgba(232,58,122,.12); background: #fff;
}
.btk-form-card .ups-form__consent { display: flex; gap: 10px; align-items: flex-start; font-size: 12.5px; color: var(--muted); }
.btk-form-card .ups-form__submit {
  width: 100%; min-height: 52px; border: 0; border-radius: 999px; background: var(--pink); color: #fff;
  font-family: inherit; font-size: 14px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; cursor: pointer;
}
.btk-form-card .ups-form__submit:hover { background: var(--pink-d); }
.btk-form-card .ups-form__notice { padding: 12px 14px; border-radius: 12px; font-size: 14px; }
.btk-picked-note {
  display: none; margin: 0 0 14px; padding: 10px 12px; border-radius: 12px;
  background: var(--pink-s); color: var(--ink); font-size: 13.5px; font-weight: 600;
}
.btk-picked-note.is-on { display: block; }
.btk-picked-note strong { color: var(--pink); }
.btk-form-card .ups-form__notice--ok { background: #ecfdf5; color: #047857; }
.btk-form-card .ups-form__notice--err { background: #fef2f2; color: #b91c1c; }

/* Persistent contact dock */
.btk-dock {
  position: fixed; z-index: 9998;
  left: 10px; right: 10px; bottom: 10px;
  display: grid; grid-template-columns: 1.15fr .85fr; gap: 8px;
  padding: 8px;
  padding-bottom: calc(8px + env(safe-area-inset-bottom, 0px));
  background: #fff;
  backdrop-filter: blur(16px);
  border: 1px solid #f0dbe4;
  border-radius: 18px;
  box-shadow: 0 10px 32px rgba(22,22,22,.18);
  font-family: "Montserrat", "DM Sans", system-ui, sans-serif;
}
.btk-dock a {
  min-height: 48px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  font-size: 13px; font-weight: 800; letter-spacing: .02em; text-decoration: none;
}
.btk-dock svg { width: 16px; height: 16px; flex-shrink: 0; }
.btk-dock-call { background: #e83a7a; color: #fff !important; }
.btk-dock-mail { background: #fff6f9; color: #161616 !important; border: 1px solid #f0dbe4; }

@media (min-width: 721px) {
  .btk-wrap { width: min(1180px, calc(100% - 40px)); }
  .btk-levers, .btk-pain-grid, .btk-steps { grid-template-columns: 1fr 1fr; }
  .btk-stat { padding: 0 16px; }
  .btk-stat span { font-size: 13px; }
  .btk-form-card .ups-form__row-2 { grid-template-columns: 1fr 1fr; }
  .btk-hero-visual img, .btk-hero-photo, .btk-hero-visual .home-media-fallback, .btk-hero-visual .tpl-asset-fallback { height: 380px; }
}

@media (min-width: 1025px) {
  .btk-nav-inner { min-height: 76px; gap: 24px; }
  .btk-logo { font-size: 12px; letter-spacing: .08em; max-width: none; white-space: nowrap; }
  .btk-logo svg { width: 34px; height: 34px; }
  .btk-burger { display: none; }
  .btk-links { display: flex; align-items: center; gap: 28px; }
  .btk-links a { font-size: 13px; min-height: 0; }
  .btk-nav-actions { display: flex; align-items: center; gap: 18px; flex-shrink: 0; }
  .btk-nav.is-open .btk-links { position: static; flex-direction: row; padding: 0; border: 0; box-shadow: none; }
  .btk-hero { padding: 28px 0 72px; }
  .btk-hero-grid { grid-template-columns: 1.05fr .95fr; gap: 48px; align-items: center; }
  .btk-hero h1 { font-size: clamp(34px, 4.6vw, 58px); }
  .btk-lead { font-size: 16px; line-height: 1.65; }
  .btk-hero-cta { display: flex; gap: 12px; }
  .btk-btn { width: auto; padding: 14px 28px; }
  .btk-btn-call { display: none; }
  .btk-hero-visual img, .btk-hero-photo, .btk-hero-visual .home-media-fallback, .btk-hero-visual .tpl-asset-fallback { height: 560px; border-radius: 28px; }
  .btk-hero-visual { border-radius: 28px; }
  .btk-call { padding: 28px 32px 24px; }
  .btk-levers { grid-template-columns: 1fr 1fr; }
  .btk-pain, .btk-process, .btk-results, .btk-contact { padding: 72px 0 40px; }
  .btk-process { padding: 80px 0; }
  .btk-results { padding: 88px 0; }
  .btk-pricing { padding: 48px 0 88px; }
  .btk-pain-grid { grid-template-columns: 1fr 1fr; gap: 16px; }
  .btk-steps { grid-template-columns: repeat(4, 1fr); gap: 18px; }
  .btk-step { text-align: center; background: transparent; padding: 8px 10px; }
  .btk-step:not(:last-child)::after {
    display: block; content: ""; position: absolute; top: 28px; right: -12px; width: 24px; height: 2px;
    background: var(--pink); opacity: .35;
  }
  .btk-process-cta { display: flex; justify-content: center; gap: 12px; }
  .btk-results-grid { grid-template-columns: 1.05fr .95fr; gap: 40px; align-items: stretch; }
  .btk-doubt-card { position: static; padding: 28px 26px 22px; }
  .btk-doubt-q li, .btk-doubt-facts li { min-height: 96px; }
  .btk-doubt-q p { font-size: 16px; }
  .btk-plans { grid-template-columns: repeat(3, 1fr); gap: 18px; }
  .btk-plan .btk-btn { width: 100%; }
  .btk-contact-grid { grid-template-columns: .9fr 1.1fr; gap: 40px; }
  .btk-form-card { padding: 28px; border-radius: 24px; }
  .btk-dock { display: none; }
  .btk-h2 { font-size: clamp(30px, 4vw, 44px); }
  .btk-links a { position: relative; }
  .btk-links a::after {
    content: ""; position: absolute; left: 0; bottom: -4px;
    width: 0; height: 1.5px; background: var(--pink);
    transition: width .22s ease;
  }
  .btk-links a:hover::after { width: 100%; }
}

.btk-nav-cta, .btk-nav-phone, .btk-links a, .btk-logo {
  transition: color .2s ease, background .2s ease, transform .2s ease;
}
.btk-icon { transition: transform .28s ease; }
.btk-sit, .btk-lever, .btk-step, .btk-plan, .btk-doubt-q li, .btk-doubt-facts li, .btk-form-card {
  transition: transform .28s ease, box-shadow .28s ease, border-color .28s ease;
}
.btk-dock { animation: btkDockIn .55s ease both; }
.btk-dock a { transition: transform .2s ease, filter .2s ease, background .2s ease; }
.btk-reveal {
  opacity: 0;
  transform: translateY(14px);
  transition: opacity .6s ease var(--btk-d, 0ms), transform .6s ease var(--btk-d, 0ms), box-shadow .28s ease, border-color .28s ease;
}
.btk-reveal.is-in {
  opacity: 1;
  transform: none;
  transition: opacity .45s ease, transform .28s ease, box-shadow .28s ease, border-color .28s ease;
}
.btk-reveal-fade {
  opacity: 0;
  transition: opacity .55s ease;
  transition-delay: var(--btk-d, 0ms);
}
.btk-reveal-fade.is-in { opacity: 1; }
@keyframes btkDockIn {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: none; }
}

@media (hover: hover) and (pointer: fine) {
  .btk-btn:hover { transform: translateY(-2px); box-shadow: 0 16px 32px rgba(232,58,122,.32); }
  .btk-btn:active { transform: translateY(0); }
  .btk-btn-ghost:hover { transform: translateY(-2px); box-shadow: 0 8px 18px rgba(22,22,22,.06); }
  .btk-nav-cta:hover { transform: translateY(-1px); }
  .btk-sit:hover, .btk-lever:hover, .btk-step:hover, .btk-plan:hover, .btk-doubt-q li:hover, .btk-doubt-facts li:hover {
    transform: translateY(-3px);
    box-shadow: 0 14px 28px rgba(22,22,22,.08);
  }
  .btk-plan.is-private:hover { box-shadow: 0 18px 40px rgba(22,22,22,.28); }
  .btk-sit:hover .btk-icon, .btk-step:hover .btk-icon { transform: scale(1.06); }
  .btk-hero-visual:hover img, .btk-hero-visual:hover .btk-hero-photo { transform: scale(1.035); }
  .btk-dock-call:hover, .btk-dock-mail:hover { transform: translateY(-1px); }
}

@media (prefers-reduced-motion: reduce) {
  .btk-reveal, .btk-reveal-fade {
    opacity: 1 !important; transform: none !important; transition: none !important;
  }
  .btk-dock { animation: none; }
  .btk *, .btk *::before, .btk *::after, .btk-dock, .btk-dock * {
    animation: none !important;
    transition: none !important;
    scroll-behavior: auto !important;
  }
}
</style>

<div class="btk">
  <header class="btk-nav">
    <div class="btk-wrap btk-nav-inner">
      <a class="btk-logo" href="#top">
        <svg viewBox="0 0 36 36" fill="none" aria-hidden="true">
          <circle cx="18" cy="18" r="18" fill="#e83a7a"/>
          <path d="M18 8.5c.9 0 1.6.7 1.6 1.6v1.1h3.2l-1.4 3.2c1.8 1.1 3 3.1 3 5.4 0 3.5-3 5.7-6.4 5.7s-6.4-2.2-6.4-5.7c0-2.3 1.2-4.3 3-5.4l-1.4-3.2h3.2V10c0-.9.7-1.6 1.6-1.6Z" fill="#fff"/>
        </svg>
        Marketing dla butików
      </a>
      <button class="btk-burger" type="button" aria-label="Otwórz menu" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
      <ul class="btk-links">
        <li><a href="#o-nas" data-btk-nav="o-nas">O mnie</a></li>
        <li><a href="#jak-dzialamy" data-btk-nav="jak-dzialamy">Jak działam</a></li>
        <li><a href="#efekty" data-btk-nav="efekty">Wątpliwości</a></li>
        <li><a href="#pakiety" data-btk-nav="pakiety">Pakiety</a></li>
      </ul>
      <div class="btk-nav-actions">
        <a class="btk-nav-phone" data-btk-cta="nav-tel" href="tel:<?php echo esc_attr($contact_phone_href); ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6.5 3.5l2.8 2.8a1 1 0 0 1 0 1.4L8 9a12 12 0 0 0 7 7l1.3-1.3a1 1 0 0 1 1.4 0l2.8 2.8a1 1 0 0 1 0 1.4l-1.2 1.2c-.7.7-1.8 1-2.8.6C10.6 19.3 4.7 13.4 3.3 7.5c-.4-1-.1-2.1.6-2.8L5.1 3.5a1 1 0 0 1 1.4 0Z"/></svg>
          <?php echo esc_html($contact_phone); ?>
        </a>
        <a class="btk-nav-cta" data-btk-cta="nav-kontakt" href="#kontakt">Kontakt</a>
      </div>
    </div>
  </header>

  <main id="top">
    <section class="btk-hero" data-btk-section="hero">
      <div class="btk-wrap btk-hero-grid">
        <div>
          <span class="btk-eyebrow">Marketing dla butików · Sebastian Kelm</span>
          <h1>Więcej klientek.<br>Więcej zamówień.<br><em class="btk-script">Więcej możliwości.</em></h1>
          <p class="btk-lead">Pracuję sam — bez agencji, juniorów i account managera. Najpierw patrzę na Twój butik, potem na reklamy: skąd mają przyjść klientki, co dzieje się po kliknięciu i jak zamienić ruch w zamówienia. Ty zajmujesz się sklepem. Ja ruchem i sprzedażą.</p>
          <ul class="btk-checks">
            <li><span class="btk-check" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg></span>Rozmawiasz ze mną, nie z działem sprzedaży</li>
            <li><span class="btk-check" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg></span>Oddzwaniam w 24h i mówię wprost, czy reklamy mają sens</li>
            <li><span class="btk-check" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg></span>Zaczynam od diagnozy butiku, nie od włączania kampanii</li>
          </ul>
          <div class="btk-hero-cta">
            <a class="btk-btn" data-btk-cta="hero-consult" href="#kontakt">Skorzystaj z darmowej konsultacji</a>
            <a class="btk-btn btk-btn-ghost btk-btn-call" data-btk-cta="hero-tel" href="tel:<?php echo esc_attr($contact_phone_href); ?>">Zadzwoń: <?php echo esc_html($contact_phone); ?></a>
          </div>
          <div class="btk-social">
            Sebastian Kelm · 10 lat sprzedaży · Twoje kampanie prowadzę osobiście
          </div>
        </div>
        <div class="btk-hero-visual">
          <?php
          if ($hero_photo_html !== "") {
              echo $hero_photo_html;
          } else {
              echo '<div class="home-media-fallback" aria-hidden="true"><span>SK</span></div>';
          }
          ?>
        </div>
      </div>
      <div class="btk-wrap btk-proofbar">
        <p class="btk-proofbar-label">Zazwyczaj po 3 miesiącach udaje mi się średnio wypracować takie wyniki:</p>
        <div class="btk-stats">
          <div class="btk-stat">
            <strong>+35%</strong>
            <span>Zamówień w formie komentarzy i wiadomości</span>
          </div>
          <div class="btk-stat">
            <strong>+50%</strong>
            <span>Zasięgu postów</span>
          </div>
          <div class="btk-stat">
            <strong>+40%</strong>
            <span>więcej komentarzy pod postami sprzedażowymi</span>
          </div>
        </div>
      </div>
    </section>

    <section class="btk-pain" id="o-nas" data-btk-section="pain">
      <div class="btk-wrap">
        <div class="btk-pain-head">
          <span class="btk-eyebrow">Znasz to?</span>
          <h2 class="btk-h2">Właścicielki butików, które do mnie piszą, zwykle są w tym samym miejscu</h2>
          <p>Zanim włączę jakąkolwiek reklamę, najczęściej słyszę to samo.</p>
        </div>
        <div class="btk-pain-grid">
          <article class="btk-sit">
            <div class="btk-icon" aria-hidden="true">
              <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 19v-1a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v1"/><circle cx="8" cy="7" r="3"/><path d="M22 19v-1a4 4 0 0 0-3-3.87M16 3.13a3 3 0 0 1 0 5.74"/></svg>
            </div>
            <h3>Mało klientek</h3>
            <blockquote>„Sklep ma sezonowość. Poza sezonem sprzedaż spada.”</blockquote>
            <p>Kupujesz 5–10 sztuk towaru, a mimo to zalega na magazynie?</p>
            <small>Udowadniam, że jest inaczej</small>
          </article>
          <article class="btk-sit">
            <div class="btk-icon" aria-hidden="true">
              <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h8.8a2 2 0 0 0 2-1.5L22 8H7"/></svg>
            </div>
            <h3>Mało zamówień</h3>
            <blockquote>„Reklamy już były. Kliknięcia są, klientek mało.”</blockquote>
            <p>Masz niższe ceny, te same produkty, a komentarzy i wiadomości proporcjonalnie mniej niż inne butiki.</p>
            <small>Układam strategię pod sprzedaż</small>
          </article>
          <article class="btk-sit">
            <div class="btk-icon" aria-hidden="true">
              <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="6.5"/><path d="M16 16l5 5"/></svg>
            </div>
            <h3>Nie wiesz, co naprawdę nie działa</h3>
            <blockquote>„Reklama, oferta, sklep czy po prostu zły moment?”</blockquote>
            <p>Masz ruch, ale trudno ocenić, gdzie naprawdę tracisz klientki i co poprawić najpierw.</p>
            <small>Najpierw znajduję problem, potem wydajemy budżet.</small>
          </article>
          <article class="btk-sit">
            <div class="btk-icon" aria-hidden="true">
              <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19V9l6-5 6 5v10"/><path d="M4 19h16"/><path d="M9 19v-6h6v6"/></svg>
            </div>
            <h3>Brak strategii</h3>
            <p>Reklamy do tych postów prowadzone przez „Promuj post”?</p>
            <small>Nie boostuję postów — układam kampanię pod sprzedaż</small>
          </article>
        </div>
        <div class="btk-call">
          <h3>30 minut, po których będziesz wiedziała, co robić dalej</h3>
          <p>Podczas rozmowy sprawdzimy:</p>
          <ul>
            <li>co obecnie najbardziej blokuje sprzedaż,</li>
            <li>czy płatna reklama ma teraz sens,</li>
            <li>jaki budżet jest rozsądny,</li>
            <li>co trzeba poprawić przed zwiększeniem wydatków.</li>
          </ul>
          <p class="btk-call-honest">Jeżeli uznam, że reklamy nie są teraz najlepszym rozwiązaniem, powiem Ci to wprost.</p>
        </div>
        <div class="btk-levers">
          <div class="btk-lever">
            <strong>Pracuję sam</strong>
            <span>Mam 10 lat doświadczenia w sprzedaży i potrafię kompleksowo ocenić sytuację Twojej firmy. Wszystkie reklamy prowadzę osobiście.</span>
          </div>
          <div class="btk-lever">
            <strong>Nie musisz znać się na marketingu</strong>
            <span>Nie wysyłam statystyk, których nikt nie rozumie. Wyniki mierzę na podstawie liczby zamówień i wartości sprzedaży.</span>
          </div>
        </div>
        <p class="btk-accent-line btk-script">Działam tak, abyś Ty mogła skupić się na biznesie, a nie na szukaniu klientów.</p>
        <div class="btk-pain-cta">
          <a class="btk-btn" data-btk-cta="pain-consult" href="#kontakt">Skorzystaj z darmowej konsultacji</a>
        </div>
      </div>
    </section>

    <section class="btk-process" id="jak-dzialamy" data-btk-section="process">
      <div class="btk-wrap">
        <h2 class="btk-h2">Jak działam?</h2>
        <div class="btk-steps">
          <article class="btk-step">
            <div class="btk-icon" aria-hidden="true"><svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="3"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2"/></svg></div>
            <h3>Sprawdzam Twój butik</h3>
            <p>Statystyki profilu, zaangażowanie, obserwujący i obecnie prowadzone reklamy.</p>
          </article>
          <article class="btk-step">
            <div class="btk-icon" aria-hidden="true"><svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 11v2h4l9 5V6l-9 5H4Z"/><path d="M18 9.5v5"/></svg></div>
            <h3>Planuję działania</h3>
            <p>Układam strategię oraz lejek sprzedażowy pod ustalony budżet.</p>
          </article>
          <article class="btk-step">
            <div class="btk-icon" aria-hidden="true"><svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19h16"/><path d="M6 16l4-5 3 3 5-7"/></svg></div>
            <h3>Uruchamiam kampanie</h3>
            <p>Włączam reklamy i pilnuję, żeby ruch szedł w przymiarki oraz zamówienia.</p>
          </article>
          <article class="btk-step">
            <div class="btk-icon" aria-hidden="true"><svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 7h15l-1.5 8H8L6 7Z"/><path d="M6 7L5 4H2"/><circle cx="10" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/></svg></div>
            <h3>Analizuję i optymalizuję</h3>
            <p>Mierzę sprzedaż, koszt pozyskania klientki, zwrot z reklam i zachowanie użytkowniczek po kliknięciu.</p>
          </article>
        </div>
        <div class="btk-process-cta">
          <a class="btk-btn" data-btk-cta="process-consult" href="#kontakt">Skorzystaj z darmowej konsultacji</a>
          <a class="btk-btn btk-btn-ghost" data-btk-cta="process-tel" href="tel:<?php echo esc_attr($contact_phone_href); ?>">Zadzwoń: <?php echo esc_html($contact_phone); ?></a>
        </div>
      </div>
    </section>

    <section class="btk-results" id="efekty" data-btk-section="results">
      <div class="btk-wrap btk-results-grid">
        <div class="btk-doubt-copy">
          <span class="btk-eyebrow">Zanim włączę reklamy</span>
          <h2 class="btk-h2">Najczęstsze wątpliwości</h2>
          <p class="btk-lead">Nie musisz znać odpowiedzi. Na rozmowie rozłożę je na konkretne decyzje pod Twój butik.</p>
          <ul class="btk-doubt-q">
            <li>
              <span class="btk-doubt-mark" aria-hidden="true">?</span>
              <p>Co się stanie z reklamą produktu, który się wyprzeda?</p>
            </li>
            <li>
              <span class="btk-doubt-mark" aria-hidden="true">?</span>
              <p>Jaki format reklam będzie najlepszy?</p>
            </li>
            <li>
              <span class="btk-doubt-mark" aria-hidden="true">?</span>
              <p>Czy muszę przygotowywać dodatkowe materiały do reklam?</p>
            </li>
            <li>
              <span class="btk-doubt-mark" aria-hidden="true">?</span>
              <p>Lepsza reklama na sprzedaż, nowych obserwujących czy większy zasięg?</p>
            </li>
          </ul>
        </div>
        <aside class="btk-doubt-card">
          <span class="btk-eyebrow">Darmowa rozmowa</span>
          <h3>Podczas pierwszej rozmowy rozwieję te i wiele więcej wątpliwości</h3>
          <ul class="btk-doubt-facts">
            <li>
              <span class="btk-doubt-ico" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5 9.2 17 19 7"/></svg>
              </span>
              <div>
                <strong>Rozmowa nic nie kosztuje</strong>
                <span>Bez umowy i bez zobowiązań.</span>
              </div>
            </li>
            <li>
              <span class="btk-doubt-ico" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="8"/><path d="M12 8v4.2l2.6 1.6"/></svg>
              </span>
              <div>
                <strong>Oddzwaniam zwykle w mniej niż 24h</strong>
                <span>Nie czekasz na „dział sprzedaży”.</span>
              </div>
            </li>
            <li>
              <span class="btk-doubt-ico" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 16.5 9 11l3.2 3.2 7.3-8.2"/><path d="M16.5 6.5H20v10"/></svg>
              </span>
              <div>
                <strong>Powtórzę działający schemat</strong>
                <span>Większość tych problemów już rozwiązałem u innych.</span>
              </div>
            </li>
          </ul>
          <a class="btk-btn" data-btk-cta="doubt-consult" href="#kontakt">Umów darmową rozmowę</a>
        </aside>
      </div>
    </section>

    <section class="btk-pricing" id="pakiety" data-btk-section="pricing">
      <div class="btk-wrap">
        <h2 class="btk-h2">Pakiety dopasowane do Twojego butiku</h2>
        <p class="btk-sub">Wybierasz etap butiku — nie liczbę reklam.</p>
        <div class="btk-plans">
          <article class="btk-plan" data-btk-plan="start" data-btk-plan-label="Start — 1 000 zł/mies.">
            <span class="btk-plan-picked">Wybrane</span>
            <h3>START</h3>
            <p class="btk-plan-for">Dla butiku, który chce zacząć pozyskiwać klientki reklamą</p>
            <p class="btk-price">1 000 zł <small>/ mies.</small></p>
            <p class="btk-price-ex">obsługa kampanii · budżet reklamowy osobno</p>
            <ul>
              <li><span class="btk-check" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg></span>Analiza profilu, oferty i zachowania klientek, żeby wiedzieć, od czego zacząć</li>
              <li><span class="btk-check" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg></span>1 kampania do nowych klientek, do 3 reklam, pod Twój asortyment</li>
              <li><span class="btk-check" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg></span>Bieżące optymalizacje, żeby ruch zmienić w zamówienia</li>
              <li><span class="btk-check" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg></span>Raport z omówieniem wyników wyjaśniający co ruszyło sprzedaż, co jeszcze nie</li>
            </ul>
            <a class="btk-btn" data-btk-cta="plan-start" data-btk-plan="start" href="#kontakt">Dowiedz się więcej</a>
          </article>
          <article class="btk-plan is-featured" data-btk-plan="optymalny" data-btk-plan-label="Optymalny — 2 000 zł/mies.">
            <span class="btk-plan-picked">Wybrane</span>
            <span class="btk-plan-flag">Najczęściej wybierany</span>
            <h3>OPTYMALNY</h3>
            <p class="btk-plan-for">Dla butiku, który chce pozyskiwać nowe klientki i ponownie docierać do zainteresowanych</p>
            <p class="btk-price">2 000 zł <small>/ mies.</small></p>
            <p class="btk-price-ex">obsługa kampanii · budżet reklamowy osobno</p>
            <ul>
              <li><span class="btk-check" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg></span>Analiza profilu, oferty i zachowania klientek — kogo pozyskiwać, a kogo domykać</li>
              <li><span class="btk-check" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg></span>2 kampanie: pozyskanie nowych klientek i osobny remarketing do osób, które oglądały produkty, profil</li>
              <li><span class="btk-check" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg></span>Do 6 reklam i regularne testowanie, pod sezon i to, co realnie się sprzedaje</li>
              <li><span class="btk-check" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg></span>Raport z omówieniem, co zostawiać w pozyskaniu, a co w remarketingu</li>
            </ul>
            <a class="btk-btn" data-btk-cta="plan-optymalny" data-btk-plan="optymalny" href="#kontakt">Dowiedz się więcej</a>
          </article>
          <article class="btk-plan is-private" data-btk-plan="premium" data-btk-plan-label="Premium, indywidualna oferta">
            <span class="btk-plan-picked">Wybrane</span>
            <span class="btk-plan-flag">Indywidualnie</span>
            <h3>PREMIUM</h3>
            <p class="btk-plan-for">Dla butiku, który już sprzedaje i chce skalować</p>
            <p class="btk-price">Oferta szyta na miarę</p>
            <ul>
              <li><span class="btk-check" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg></span>Analiza profilu, oferty i zachowania klientek, pod Twój butik, nie pod szablon</li>
              <li><span class="btk-check" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg></span>Układam kampanie i reklamy bez limitu 1 czy 2, pod sezon, stany i cel sprzedaży</li>
              <li><span class="btk-check" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg></span>Bieżące optymalizacje i priorytetowy kontakt 1:1 w trakcie miesiąca</li>
              <li><span class="btk-check" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg></span>Raport z omówieniem wyników, co skalować, co wyłączyć, co testować dalej</li>
            </ul>
            <a class="btk-btn" data-btk-cta="plan-premium" data-btk-plan="premium" href="#kontakt">Zapytaj o wycenę</a>
          </article>
        </div>
      </div>
    </section>

    <section class="btk-contact" id="kontakt" data-btk-section="contact">
      <div class="btk-wrap btk-contact-grid">
        <div>
          <span class="btk-eyebrow">Bezpłatna analiza</span>
          <h2 class="btk-h2" style="text-align:left">Umów bezpłatną analizę butiku</h2>
          <p class="btk-lead">Podeślij mi swój butik. Przed rozmową sprawdzę jego obecną sytuację, a podczas 30 minut omówimy, gdzie widzę największy potencjał i czy reklamy są teraz właściwym krokiem.</p>
          <p class="btk-lead">
            Tel. <a data-btk-cta="contact-tel" href="tel:<?php echo esc_attr($contact_phone_href); ?>"><strong><?php echo esc_html($contact_phone); ?></strong></a><br>
            <a data-btk-cta="contact-mail" href="mailto:<?php echo esc_attr($contact_email); ?>"><?php echo esc_html($contact_email); ?></a>
          </p>
        </div>
        <div class="btk-form-card">
          <p class="btk-picked-note" id="btk-picked-note">Wybrany pakiet: <strong></strong></p>
          <?php
          if (function_exists("upsellio_render_lead_form")) {
              echo upsellio_render_lead_form([
                  "origin" => "landing-butik-form",
                  "variant" => "full",
                  "heading" => "",
                  "subheading" => "",
                  "redirect_url" => $current_page_url . "#kontakt",
                  "hidden_service" => "Marketing dla butiku",
                  "hidden_fields" => ["lead_package" => ""],
                  "require_shop_url" => true,
                  "message_placeholder" => "Mam dużo wyświetleń ale mało komentarzy i wiadomości od klientek",
                  "css_class" => "btk-form",
                  "form_id" => "landing-butik-form",
              ]);
          }
          ?>
          <p style="margin:12px 0 0;font-size:12px;color:var(--muted)">Wysyłając formularz, akceptujesz <a href="<?php echo esc_url($privacy_url); ?>" style="text-decoration:underline">politykę prywatności</a>.</p>
        </div>
      </div>
    </section>
  </main>
</div>

<nav class="btk-dock" aria-label="Szybki kontakt">
  <a class="btk-dock-call" data-btk-cta="dock-tel" href="tel:<?php echo esc_attr($contact_phone_href); ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6.5 3.5l2.8 2.8a1 1 0 0 1 0 1.4L8 9a12 12 0 0 0 7 7l1.3-1.3a1 1 0 0 1 1.4 0l2.8 2.8a1 1 0 0 1 0 1.4l-1.2 1.2c-.7.7-1.8 1-2.8.6C10.6 19.3 4.7 13.4 3.3 7.5c-.4-1-.1-2.1.6-2.8L5.1 3.5a1 1 0 0 1 1.4 0Z"/></svg>
    Zadzwoń
  </a>
  <a class="btk-dock-mail" data-btk-cta="dock-mail" href="mailto:<?php echo esc_attr($contact_email); ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 6h16v12H4z"/><path d="M4 7l8 6 8-6"/></svg>
    Napisz
  </a>
</nav>

<script>
(function () {
  var nav = document.querySelector('.btk-nav');
  var burger = document.querySelector('.btk-burger');
  if (burger && nav) {
    burger.addEventListener('click', function () {
      var open = nav.classList.toggle('is-open');
      burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    nav.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () { nav.classList.remove('is-open'); });
    });
  }
  document.querySelectorAll('.btk a[href^="#"]').forEach(function (link) {
    link.addEventListener('click', function (e) {
      var id = link.getAttribute('href');
      if (!id || id.length < 2) return;
      var target = document.querySelector(id);
      if (!target) return;
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  var form = document.getElementById('landing-butik-form');
  var packageInput = form ? form.querySelector('input[name="lead_package"]') : null;
  var note = document.getElementById('btk-picked-note');
  var noteStrong = note ? note.querySelector('strong') : null;

  function setPackage(slug, label) {
    if (!slug || !label) return;
    document.querySelectorAll('.btk-plan').forEach(function (card) {
      card.classList.toggle('is-picked', card.getAttribute('data-btk-plan') === slug);
    });
    if (packageInput) packageInput.value = label;
    if (note && noteStrong) {
      noteStrong.textContent = label;
      note.classList.add('is-on');
    }
    try { sessionStorage.setItem('ups_btk_package', JSON.stringify({ slug: slug, label: label })); } catch (err) {}
  }

  document.querySelectorAll('.btk-plan').forEach(function (card) {
    card.addEventListener('click', function (e) {
      var slug = card.getAttribute('data-btk-plan') || '';
      var label = card.getAttribute('data-btk-plan-label') || '';
      setPackage(slug, label);
      if (e.target.closest('a[href^="#"]')) return;
      var target = document.getElementById('kontakt');
      if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  try {
    var saved = JSON.parse(sessionStorage.getItem('ups_btk_package') || 'null');
    if (saved && saved.slug && saved.label) setPackage(saved.slug, saved.label);
  } catch (err2) {}

  if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    var batches = [
      { sel: '.btk-hero .btk-eyebrow, .btk-hero h1, .btk-hero .btk-lead, .btk-hero .btk-checks li, .btk-hero-cta, .btk-social', step: 70 },
      { sel: '.btk-hero-visual', step: 0 },
      { sel: '.btk-proofbar-label, .btk-stat', step: 70 },
      { sel: '.btk-pain-head, .btk-sit, .btk-call, .btk-lever, .btk-accent-line, .btk-pain-cta', step: 70 },
      { sel: '.btk-process .btk-h2, .btk-step, .btk-process-cta', step: 70 },
      { sel: '.btk-results .btk-eyebrow, .btk-results .btk-h2, .btk-results .btk-lead, .btk-doubt-q li', step: 60 },
      { sel: '.btk-doubt-card', fade: true },
      { sel: '.btk-pricing .btk-h2, .btk-sub, .btk-plan', step: 80 },
      { sel: '.btk-contact .btk-eyebrow, .btk-contact .btk-h2, .btk-contact .btk-lead, .btk-form-card', step: 80 }
    ];
    batches.forEach(function (batch) {
      document.querySelectorAll(batch.sel).forEach(function (el, i) {
        el.classList.add(batch.fade ? 'btk-reveal-fade' : 'btk-reveal');
        el.style.setProperty('--btk-d', ((i * (batch.step || 0))) + 'ms');
      });
    });
    var nodes = document.querySelectorAll('.btk-reveal, .btk-reveal-fade');
    if ('IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          entry.target.classList.add('is-in');
          io.unobserve(entry.target);
        });
      }, { threshold: 0.14, rootMargin: '0px 0px -36px 0px' });
      nodes.forEach(function (el) { io.observe(el); });
    } else {
      nodes.forEach(function (el) { el.classList.add('is-in'); });
    }
    setTimeout(function () {
      document.querySelectorAll('.btk-reveal:not(.is-in), .btk-reveal-fade:not(.is-in)').forEach(function (el) {
        el.classList.add('is-in');
      });
    }, 1800);
  }
})();
</script>

<?php
add_filter("upsellio_footer_brand_description", static function () {
    return "Pomagam butikom zamieniać ruch z reklam w klientki i zamówienia. Kampanie prowadzę osobiście.";
});
add_filter("upsellio_footer_show_cities", "__return_false");
get_footer();
