<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_get_site_gtm_container_id()
{
    return (string) apply_filters("upsellio_site_gtm_container_id", "GTM-KM9J5XC2");
}

function upsellio_register_offer_layout_post_types()
{
    register_post_type("crm_offer_layout", [
        "labels" => [
            "name" => "Szablony ofert (layout)",
            "singular_name" => "Szablon oferty",
        ],
        "public" => false,
        "show_ui" => true,
        "show_in_menu" => false,
        "supports" => ["title"],
    ]);
    register_post_type("crm_contract_layout", [
        "labels" => [
            "name" => "Szablony umów",
            "singular_name" => "Szablon umowy",
        ],
        "public" => false,
        "show_ui" => true,
        "show_in_menu" => false,
        "supports" => ["title", "editor"],
    ]);
}
add_action("init", "upsellio_register_offer_layout_post_types", 12);

function upsellio_offer_layout_get_default_payload()
{
    return [
        "lead" => "Poniżej znajdziesz pełny zakres, harmonogram i transparentną wycenę. Wszystko w jednym miejscu — bez marketingowego szumu.",
        "duration" => "3 mies. start + 30 dni wypowiedzenia",
        "billing" => "Abonament miesięczny, faktura VAT",
        "price_note" => "netto + 23% VAT · bez prowizji od budżetu reklamowego",
        "show_proof" => false,
        "proof_lines" => "E-commerce B2C\nUsługi lokalne\nSaaS B2B",
        "has_google" => true,
        "has_meta" => true,
        "has_web" => false,
        "questions_raw" => "Jaki jest Twój miesięczny budżet reklamowy (bez fee)?|Pomoże to zaplanować strukturę kampanii.\nKiedy chcesz startować z działaniami?",
        "services_json" => wp_json_encode([
            ["key" => "all", "label" => "Cały pakiet (Google + Meta)", "price_hint" => ""],
            ["key" => "google", "label" => "Tylko Google Ads", "price_hint" => ""],
            ["key" => "meta", "label" => "Tylko Meta Ads", "price_hint" => ""],
            ["key" => "web", "label" => "Strona / landing", "price_hint" => "wycena osobna"],
        ]),
        "include_lines" => "Audyt i strategia (PDF)\nKonfiguracja kampanii i śledzenia\nCotygodniowy raport i optymalizacja\nRozmowa strategiczna 1× / mies.",
        "option_lines" => "Performance Max / Shopping — dopłata\nProdukcja wideo — wycena osobna\nOpieka nad stroną — pakiet dodatkowy",
    ];
}

function upsellio_offer_layout_get_payload_from_post($layout_id)
{
    $layout_id = (int) $layout_id;
    if ($layout_id <= 0 || get_post_type($layout_id) !== "crm_offer_layout") {
        return [];
    }
    $raw = (string) get_post_meta($layout_id, "_ups_offer_layout_payload", true);
    if ($raw === "") {
        return [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function upsellio_offer_layout_services_array_from_payload($payload)
{
    if (!is_array($payload)) {
        return [];
    }
    $sj = $payload["services_json"] ?? [];
    if (is_array($sj)) {
        return $sj;
    }
    $decoded = json_decode((string) $sj, true);
    return is_array($decoded) ? $decoded : [];
}

/**
 * Buduje tablicę payloadu szablonu oferty z pól formularza CRM (bez JSON od użytkownika).
 */
function upsellio_offer_layout_build_payload_from_form_post()
{
    $defaults = upsellio_offer_layout_get_default_payload();
    $out = $defaults;
    $out["lead"] = isset($_POST["offer_layout_lead"]) ? sanitize_textarea_field(wp_unslash($_POST["offer_layout_lead"])) : $defaults["lead"];
    $out["duration"] = isset($_POST["offer_layout_duration"]) ? sanitize_text_field(wp_unslash($_POST["offer_layout_duration"])) : $defaults["duration"];
    $out["billing"] = isset($_POST["offer_layout_billing"]) ? sanitize_text_field(wp_unslash($_POST["offer_layout_billing"])) : $defaults["billing"];
    $out["price_note"] = isset($_POST["offer_layout_price_note"]) ? sanitize_text_field(wp_unslash($_POST["offer_layout_price_note"])) : $defaults["price_note"];
    $out["show_proof"] = !empty($_POST["offer_layout_show_proof"]);
    $out["proof_lines"] = isset($_POST["offer_layout_proof_lines"]) ? sanitize_textarea_field(wp_unslash($_POST["offer_layout_proof_lines"])) : $defaults["proof_lines"];
    $out["has_google"] = !empty($_POST["offer_layout_has_google"]);
    $out["has_meta"] = !empty($_POST["offer_layout_has_meta"]);
    $out["has_web"] = !empty($_POST["offer_layout_has_web"]);
    $out["questions_raw"] = isset($_POST["offer_layout_questions_raw"]) ? sanitize_textarea_field(wp_unslash($_POST["offer_layout_questions_raw"])) : $defaults["questions_raw"];
    $out["include_lines"] = isset($_POST["offer_layout_include_lines"]) ? sanitize_textarea_field(wp_unslash($_POST["offer_layout_include_lines"])) : $defaults["include_lines"];
    $out["option_lines"] = isset($_POST["offer_layout_option_lines"]) ? sanitize_textarea_field(wp_unslash($_POST["offer_layout_option_lines"])) : $defaults["option_lines"];

    $keys = isset($_POST["offer_layout_svc_key"]) ? (array) wp_unslash($_POST["offer_layout_svc_key"]) : [];
    $labels = isset($_POST["offer_layout_svc_label"]) ? (array) wp_unslash($_POST["offer_layout_svc_label"]) : [];
    $hints = isset($_POST["offer_layout_svc_hint"]) ? (array) wp_unslash($_POST["offer_layout_svc_hint"]) : [];
    $svc = [];
    $max = max(count($keys), count($labels), count($hints));
    for ($i = 0; $i < $max; $i++) {
        $k = isset($keys[$i]) ? sanitize_key((string) $keys[$i]) : "";
        $lab = isset($labels[$i]) ? sanitize_text_field((string) $labels[$i]) : "";
        $h = isset($hints[$i]) ? sanitize_text_field((string) $hints[$i]) : "";
        if ($k === "" || $lab === "") {
            continue;
        }
        $svc[] = [
            "key" => $k,
            "label" => $lab,
            "price_hint" => $h,
        ];
    }
    $out["services_json"] = !empty($svc)
        ? wp_json_encode($svc, JSON_UNESCAPED_UNICODE)
        : (string) ($defaults["services_json"] ?? "[]");

    return $out;
}

function upsellio_offer_merge_payload_into_offer_meta($offer_id, $payload)
{
    $offer_id = (int) $offer_id;
    if ($offer_id <= 0 || !is_array($payload)) {
        return;
    }
    $defaults = upsellio_offer_layout_get_default_payload();
    $merged = array_merge($defaults, $payload);
    update_post_meta($offer_id, "_ups_offer_lead", sanitize_textarea_field((string) ($merged["lead"] ?? "")));
    update_post_meta($offer_id, "_ups_offer_duration", sanitize_text_field((string) ($merged["duration"] ?? "")));
    update_post_meta($offer_id, "_ups_offer_billing", sanitize_text_field((string) ($merged["billing"] ?? "")));
    update_post_meta($offer_id, "_ups_offer_price_note", sanitize_text_field((string) ($merged["price_note"] ?? "")));
    update_post_meta($offer_id, "_ups_offer_show_proof", !empty($merged["show_proof"]) ? "1" : "0");
    update_post_meta($offer_id, "_ups_offer_proof_lines", sanitize_textarea_field((string) ($merged["proof_lines"] ?? "")));
    update_post_meta($offer_id, "_ups_offer_has_google", !empty($merged["has_google"]) ? "1" : "0");
    update_post_meta($offer_id, "_ups_offer_has_meta", !empty($merged["has_meta"]) ? "1" : "0");
    update_post_meta($offer_id, "_ups_offer_has_web", !empty($merged["has_web"]) ? "1" : "0");
    update_post_meta($offer_id, "_ups_offer_questions_raw", sanitize_textarea_field((string) ($merged["questions_raw"] ?? "")));
    if (isset($merged["services_json"])) {
        $sj = $merged["services_json"];
        if (is_array($sj)) {
            update_post_meta($offer_id, "_ups_offer_services_json", wp_json_encode($sj));
        } else {
            $sjs = (string) $sj;
            if ($sjs !== "" && json_decode($sjs, true) !== null) {
                update_post_meta($offer_id, "_ups_offer_services_json", $sjs);
            }
        }
    }
    update_post_meta($offer_id, "_ups_offer_include_lines", sanitize_textarea_field((string) ($merged["include_lines"] ?? "")));
    update_post_meta($offer_id, "_ups_offer_option_lines", sanitize_textarea_field((string) ($merged["option_lines"] ?? "")));
}

function upsellio_offer_get_landing_payload($offer_id)
{
    $offer_id = (int) $offer_id;
    $base = upsellio_offer_layout_get_default_payload();
    $meta_map = [
        "lead" => "_ups_offer_lead",
        "duration" => "_ups_offer_duration",
        "billing" => "_ups_offer_billing",
        "price_note" => "_ups_offer_price_note",
        "proof_lines" => "_ups_offer_proof_lines",
        "questions_raw" => "_ups_offer_questions_raw",
        "services_json" => "_ups_offer_services_json",
        "include_lines" => "_ups_offer_include_lines",
        "option_lines" => "_ups_offer_option_lines",
    ];
    foreach ($meta_map as $key => $mk) {
        $v = (string) get_post_meta($offer_id, $mk, true);
        if ($v !== "") {
            $base[$key] = $v;
        }
    }
    $base["show_proof"] = (string) get_post_meta($offer_id, "_ups_offer_show_proof", true) === "1";
    $base["has_google"] = (string) get_post_meta($offer_id, "_ups_offer_has_google", true) !== "0";
    $base["has_meta"] = (string) get_post_meta($offer_id, "_ups_offer_has_meta", true) !== "0";
    $base["has_web"] = (string) get_post_meta($offer_id, "_ups_offer_has_web", true) === "1";
    if ((string) get_post_meta($offer_id, "_ups_offer_has_google", true) === "" && (string) get_post_meta($offer_id, "_ups_offer_has_meta", true) === "" && (string) get_post_meta($offer_id, "_ups_offer_has_web", true) === "") {
        $base["has_google"] = true;
        $base["has_meta"] = true;
        $base["has_web"] = false;
    }
    return $base;
}

function upsellio_offer_parse_questions_block($raw)
{
    $raw = trim((string) $raw);
    if ($raw === "") {
        return [];
    }
    $out = [];
    foreach (preg_split("/\r\n|\r|\n/", $raw) as $line) {
        $line = trim((string) $line);
        if ($line === "") {
            continue;
        }
        $parts = array_map("trim", explode("|", $line, 2));
        $out[] = [
            "text" => $parts[0],
            "note" => isset($parts[1]) ? $parts[1] : "",
        ];
    }
    return $out;
}

function upsellio_offer_render_lines_as_checklist($lines, $use_optional_icon = false)
{
    $lines = trim((string) $lines);
    if ($lines === "") {
        return;
    }
    foreach (preg_split("/\r\n|\r|\n/", $lines) as $line) {
        $line = trim((string) $line);
        if ($line === "") {
            continue;
        }
        $safe = esc_html($line);
        if ($use_optional_icon) {
            echo '<div class="ii"><div class="iopt">+</div><div>' . $safe . '</div></div>';
        } else {
            echo '<div class="ii"><div class="ick"><svg viewBox="0 0 10 10"><path d="m1.5 5 2.5 2.5 4.5-4.5"/></svg></div><span>' . $safe . '</span></div>';
        }
    }
}

function upsellio_offer_render_public_landing($offer)
{
    if (!$offer instanceof WP_Post) {
        return;
    }
    $offer_id = (int) $offer->ID;
    $slug = (string) get_post_meta($offer_id, "_ups_offer_public_slug", true);
    $client_id = (int) get_post_meta($offer_id, "_ups_offer_client_id", true);
    $person_id = (string) get_post_meta($offer_id, "_ups_offer_person_id", true);
    if ($person_id === "" && $client_id > 0) {
        $person_id = (string) get_post_meta($client_id, "_ups_client_person_id", true);
    }
    $client_name = $client_id > 0 ? (string) get_the_title($client_id) : "Klient";
    $price = (string) get_post_meta($offer_id, "_ups_offer_price", true);
    $timeline = (string) get_post_meta($offer_id, "_ups_offer_timeline", true);
    $cta_text = (string) get_post_meta($offer_id, "_ups_offer_cta_text", true);
    if ($cta_text === "") {
        $cta_text = "Akceptuję ofertę";
    }
    $payload = upsellio_offer_get_landing_payload($offer_id);
    $owner_id = (int) get_post_meta($offer_id, "_ups_offer_owner_id", true);
    if ($owner_id <= 0 && function_exists("upsellio_crm_get_default_owner_id")) {
        $owner_id = (int) upsellio_crm_get_default_owner_id();
    }
    if ($owner_id <= 0) {
        $owner_id = (int) get_post_field("post_author", $offer_id);
    }
    $owner = get_userdata($owner_id);
    $owner_name = $owner instanceof WP_User ? (string) $owner->display_name : "Upsellio";
    $owner_email = $owner instanceof WP_User && is_email((string) $owner->user_email) ? (string) $owner->user_email : (string) get_option("admin_email");
    $owner_phone = $owner instanceof WP_User ? (string) get_user_meta($owner_id, "billing_phone", true) : "";
    if ($owner_phone === "") {
        $owner_phone = (string) get_theme_mod("upsellio_contact_phone", "");
    }
    $expires_at = (int) get_post_meta($offer_id, "_ups_offer_expires_at", true);
    $offer_expires_label = $expires_at > 0 ? (string) wp_date("j.m.Y", $expires_at) : "do uzgodnienia";
    $days_left = 0;
    if ($expires_at > 0) {
        $days_left = max(0, (int) ceil(($expires_at - time()) / DAY_IN_SECONDS));
    }
    $offer_date_label = (string) wp_date("j.m.Y", strtotime((string) $offer->post_modified_gmt . " UTC"));
    $offer_created_label = (string) wp_date("j.m.Y", strtotime((string) $offer->post_date_gmt . " UTC"));
    $questions = upsellio_offer_parse_questions_block((string) ($payload["questions_raw"] ?? ""));
    $has_questions = !empty($questions);
    $services = json_decode((string) ($payload["services_json"] ?? "[]"), true);
    if (!is_array($services)) {
        $services = [];
    }
    $show_proof = !empty($payload["show_proof"]);
    $ajax_url = admin_url("admin-ajax.php");
    $gtm = upsellio_get_site_gtm_container_id();
    $upsellio_offer_track_public = !function_exists("upsellio_should_load_public_tracking_tags") || upsellio_should_load_public_tracking_tags();
    $offer_title = (string) $offer->post_title;
    $content_html = (string) apply_filters("the_content", (string) $offer->post_content);

    status_header(200);
    nocache_headers();
    ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo("charset"); ?>"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<meta name="robots" content="noindex,nofollow"/>
<title><?php echo esc_html($offer_title); ?> — Upsellio</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&amp;family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&amp;display=swap" rel="stylesheet"/>
<?php if ($gtm !== "" && $upsellio_offer_track_public) : ?>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','<?php echo esc_js($gtm); ?>');</script>
<?php endif; ?>
<script>
window.dataLayer=window.dataLayer||[];
window.UPS={offer_id:'<?php echo esc_js((string) $offer_id); ?>',offer_slug:'<?php echo esc_js($slug); ?>',offer_title:'<?php echo esc_js($offer_title); ?>',person_id:'<?php echo esc_js($person_id); ?>',utm_source:'',utm_campaign:'',gclid:''};
(function(){var q=new URLSearchParams(window.location.search||'');UPS.utm_source=q.get('utm_source')||'';UPS.utm_campaign=q.get('utm_campaign')||'';UPS.gclid=q.get('gclid')||'';})();
<?php if ($upsellio_offer_track_public) : ?>
dataLayer.push({event:'offer_view',offer_id:UPS.offer_id,offer_title:UPS.offer_title,person_id:UPS.person_id,utm_source:UPS.utm_source,utm_campaign:UPS.utm_campaign,gclid:UPS.gclid});
<?php endif; ?>
</script>
<style>
:root{
  --bg:#fafaf7;--surface:#fff;--ink:#0a1410;--ink2:#2e2e2a;--muted:#6b6b63;
  --border:#e6e6e0;--teal:#0d9488;--tealh:#0f766e;--teald:#134e4a;
  --teals:#ccfbf1;--teall:#99f6e4;
  --font-d:'Syne',sans-serif;--font-b:'DM Sans',sans-serif;--r:14px;--rl:22px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:var(--font-b);background:var(--bg);color:var(--ink);font-size:15px;line-height:1.65;-webkit-font-smoothing:antialiased}
a{text-decoration:none;color:inherit}
#bar{position:fixed;top:0;left:0;height:2px;background:var(--teal);z-index:100;width:0;transition:width .1s linear}
.nav{position:sticky;top:0;z-index:50;background:rgba(250,250,247,.94);backdrop-filter:blur(12px);border-bottom:1px solid var(--border)}
.nav-in{max-width:960px;margin:0 auto;padding:0 24px;height:60px;display:flex;align-items:center;justify-content:space-between;gap:16px}
.nav-logo{display:flex;align-items:center;gap:9px}
.nav-logo--home{color:inherit}
.brand-logo-nav,.brand-logo-foot{width:auto;max-width:min(280px,52vw);height:auto;object-fit:contain;display:block}
.brand-logo-nav{max-height:34px}
.brand-logo-foot{max-height:26px}
.foot-logo a{display:inline-flex;align-items:center;gap:10px;color:inherit;text-decoration:none;font-weight:700;font-size:13px}
.mark{width:32px;height:32px;border-radius:9px;background:linear-gradient(160deg,#14b8a6,#0f766e);color:#fff;display:grid;place-items:center;font-family:var(--font-d);font-weight:800;font-size:15px;flex-shrink:0}
.nav-name{font-family:var(--font-d);font-size:17px;font-weight:800;letter-spacing:-.3px}
.nav-for{font-size:13px;color:var(--muted)}
.nav-for strong{color:var(--ink);font-weight:600}
.btn{display:inline-flex;align-items:center;gap:7px;border-radius:999px;font-family:var(--font-b);font-weight:700;font-size:14px;border:1px solid transparent;transition:all .18s;cursor:pointer}
.btn-p{background:var(--teal);color:#fff;padding:10px 22px;box-shadow:0 6px 18px rgba(13,148,136,.22)}
.btn-p:hover{background:var(--tealh);transform:translateY(-1px)}
.btn-g{background:var(--surface);border-color:var(--border);color:var(--ink);padding:9px 18px}
.btn-g:hover{border-color:var(--teal);color:var(--teal)}
.snav{background:var(--surface);border-bottom:1px solid var(--border);position:sticky;top:60px;z-index:40;overflow-x:auto;scrollbar-width:none}
.snav::-webkit-scrollbar{display:none}
.snav-in{max-width:960px;margin:0 auto;padding:0 24px;display:flex;gap:0;min-width:max-content}
.snav-link{display:flex;align-items:center;gap:6px;padding:11px 16px;font-size:13px;font-weight:600;color:var(--muted);border-bottom:2px solid transparent;transition:all .18s;cursor:pointer;white-space:nowrap;user-select:none}
.snav-link:hover{color:var(--ink)}
.snav-link.active{color:var(--teal);border-bottom-color:var(--teal)}
.snav-num{width:18px;height:18px;border-radius:50%;background:var(--border);font-size:10px;font-weight:800;display:grid;place-items:center;color:var(--muted);transition:.18s}
.snav-link.active .snav-num{background:var(--teals);color:var(--teald)}
.w{max-width:960px;margin:0 auto;padding:0 24px}
.hr{height:1px;background:var(--border)}
.sec{padding:56px 0}
.lbl{display:inline-flex;align-items:center;gap:7px;font-size:11px;font-weight:700;letter-spacing:1.4px;text-transform:uppercase;color:var(--teal);margin-bottom:12px}
.lbl::before{content:'';width:16px;height:2px;background:var(--teal);border-radius:2px}
.h2{font-family:var(--font-d);font-size:clamp(24px,2.6vw,34px);font-weight:700;letter-spacing:-.8px;line-height:1.08;margin-bottom:10px}
.sub{font-size:16px;color:var(--muted);line-height:1.65;max-width:58ch;margin-bottom:36px}
.r{opacity:0;transform:translateY(18px);transition:opacity .55s ease,transform .55s ease}
.r.in{opacity:1;transform:none}
.hero{padding:48px 0 0}
.hero-lbl{display:inline-flex;align-items:center;gap:7px;font-size:11px;font-weight:700;letter-spacing:1.4px;text-transform:uppercase;color:var(--teal);margin-bottom:16px}
.hero-lbl::before{content:'';width:18px;height:2px;background:var(--teal);border-radius:2px}
.hero-grid{display:grid;grid-template-columns:1fr 320px;gap:40px;align-items:start}
.h1{font-family:var(--font-d);font-size:clamp(30px,3.6vw,48px);font-weight:700;line-height:1.04;letter-spacing:-1.4px;margin-bottom:14px}
.hero-lead{font-size:17px;color:var(--muted);line-height:1.7;max-width:52ch;margin-bottom:24px}
.chips{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:28px}
.chip{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:999px;font-size:12px;font-weight:600;border:1px solid}
.chip-t{background:var(--teals);border-color:var(--teall);color:var(--teald)}
.chip-g{background:var(--surface);border-color:var(--border);color:var(--muted)}
.acts{display:flex;gap:10px;flex-wrap:wrap}
.proof-strip{margin-top:28px;padding-top:20px;border-top:1px solid var(--border)}
.proof-strip-label{font-size:11px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--muted);margin-bottom:10px}
.proof-logos{display:flex;flex-wrap:wrap;gap:8px}
.proof-logo{padding:5px 12px;background:var(--bg);border:1px solid var(--border);border-radius:999px;font-size:12px;font-weight:600;color:var(--muted)}
.sc{background:var(--surface);border:1px solid var(--border);border-radius:var(--rl);padding:24px;position:sticky;top:112px;box-shadow:0 8px 32px rgba(0,0,0,.07),0 2px 8px rgba(0,0,0,.04)}
.sc-for{font-size:11px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--muted);margin-bottom:5px}
.sc-cli{font-family:var(--font-d);font-size:18px;font-weight:700;letter-spacing:-.3px;margin-bottom:18px}
.sc-rows{border-top:1px solid var(--border)}
.sc-row{display:flex;justify-content:space-between;align-items:baseline;padding:10px 0;border-bottom:1px solid var(--border);font-size:13px;gap:12px}
.sc-row:last-of-type{border-bottom:none}
.sc-l{color:var(--muted);flex-shrink:0}
.sc-r{font-weight:600;color:var(--ink);text-align:right}
.sc-price{font-family:var(--font-d);font-size:34px;font-weight:700;letter-spacing:-1.2px;color:var(--teal);margin:18px 0 3px;line-height:1}
.sc-pnote{font-size:13px;color:var(--muted);margin-bottom:16px}
.sc-exp{display:flex;align-items:center;gap:7px;padding:9px 12px;background:var(--teals);border:1px solid var(--teall);border-radius:var(--r);font-size:12px;font-weight:600;color:var(--teald);margin-bottom:14px}
.sc-dot{width:7px;height:7px;border-radius:50%;background:var(--teal);animation:pulse 2s infinite;flex-shrink:0}
@keyframes pulse{0%,100%{box-shadow:0 0 0 0 rgba(13,148,136,.4)}50%{box-shadow:0 0 0 5px rgba(13,148,136,0)}}
.sc-commit{margin-bottom:14px}
.sc-commit-label{font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);margin-bottom:8px}
.commit-opts{display:grid;gap:6px}
.commit-opt{display:flex;align-items:center;gap:8px;padding:8px 10px;border:1px solid var(--border);border-radius:var(--r);font-size:13px;font-weight:500;color:var(--ink2);cursor:pointer;transition:all .18s;background:var(--bg)}
.commit-opt:hover{border-color:var(--teall);background:var(--teals)}
.commit-opt.sel{border-color:var(--teal);background:var(--teals);color:var(--teald);font-weight:700}
.commit-opt input{width:15px;height:15px;accent-color:var(--teal);flex-shrink:0;cursor:pointer}
.commit-opt span{font-size:11px;color:var(--muted);font-weight:400;margin-left:auto}
.sc-note{display:flex;gap:9px;align-items:flex-start;margin-top:12px;font-size:12px;color:var(--muted);line-height:1.55}
.sc-shield{width:28px;height:28px;border-radius:50%;background:var(--teals);display:grid;place-items:center;flex-shrink:0}
.sc-shield svg{width:12px;height:12px;stroke:var(--teald);fill:none;stroke-width:1.8}
.scope{border:1px solid var(--border);border-radius:var(--rl);overflow:hidden}
.scope-head{display:grid;grid-template-columns:1fr 150px 110px;padding:10px 20px;background:var(--bg);border-bottom:1px solid var(--border);font-size:11px;font-weight:700;letter-spacing:.7px;text-transform:uppercase;color:var(--muted);gap:12px}
.scope-row{display:grid;grid-template-columns:1fr 150px 110px;padding:16px 20px;border-bottom:1px solid var(--border);gap:12px;align-items:start;transition:background .15s}
.scope-row:last-child{border-bottom:none}
.scope-row:hover{background:#f7f7f4}
.scope-group{padding:8px 20px;background:#f4f4f0;border-bottom:1px solid var(--border);font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--muted);display:flex;align-items:center;gap:8px}
.scope-group-dot{width:6px;height:6px;border-radius:50%;background:var(--teal)}
.sn{font-size:14px;font-weight:600;color:var(--ink);margin-bottom:3px}
.sd{font-size:13px;color:var(--muted);line-height:1.5}
.sw{font-size:13px;color:var(--ink2);font-weight:500}
.tag{display:inline-flex;align-items:center;padding:3px 9px;border-radius:999px;font-size:11px;font-weight:700}
.ti{background:var(--teals);color:var(--teald)}
.to{background:#fef3c7;color:#92400e}
.ta{background:#f1f1ec;color:var(--muted)}
.steps{display:grid;gap:0}
.step{display:grid;grid-template-columns:52px 1fr;gap:20px;padding:28px 0;border-bottom:1px solid var(--border)}
.step:last-child{border-bottom:none}
.sl{display:flex;flex-direction:column;align-items:center}
.snum{width:40px;height:40px;border-radius:50%;background:var(--teals);border:1.5px solid var(--teall);display:grid;place-items:center;font-family:var(--font-d);font-weight:800;font-size:14px;color:var(--teald);flex-shrink:0}
.sline{flex:1;width:1.5px;background:var(--border);margin-top:10px;min-height:20px}
.step:last-child .sline{display:none}
.st{font-family:var(--font-d);font-size:17px;font-weight:700;letter-spacing:-.2px;margin-bottom:6px}
.swhen{font-size:12px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--teal);margin-bottom:8px}
.sdesc{font-size:14px;color:var(--muted);line-height:1.65;margin-bottom:12px}
.sdels{display:flex;flex-wrap:wrap;gap:6px}
.sdel{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;background:var(--surface);border:1px solid var(--border);border-radius:999px;font-size:12px;color:var(--ink2);font-weight:500}
.sdel::before{content:'';width:6px;height:6px;border-radius:50%;background:var(--teall);border:1px solid var(--teal);flex-shrink:0}
.oc{font-size:15px;line-height:1.8;color:var(--ink2)}
.oc h2{font-family:var(--font-d);color:var(--ink);font-size:1.4rem;letter-spacing:-.4px;margin:2em 0 .6em;border-top:1px solid var(--border);padding-top:1.5em}
.oc h2:first-child{border-top:none;padding-top:0;margin-top:0}
.oc h3{font-family:var(--font-d);color:var(--ink);font-size:1.1rem;margin:1.5em 0 .5em}
.oc p{margin-bottom:.9em}
.oc ul,.oc ol{margin:.8em 0 .8em 1.4em}
.oc li{margin-bottom:.4em}
.oc blockquote{border-left:3px solid var(--teal);padding:12px 18px;background:var(--teals);border-radius:0 var(--r) var(--r) 0;margin:1.4em 0;color:var(--teald)}
.questions-sec{background:var(--surface);border:1px solid var(--border);border-radius:var(--rl);padding:28px 32px}
.q-item{display:flex;gap:14px;align-items:flex-start;padding:14px 0;border-bottom:1px solid var(--border)}
.q-item:last-child{border-bottom:none;padding-bottom:0}
.q-num{width:28px;height:28px;border-radius:50%;background:var(--teals);border:1.5px solid var(--teall);display:grid;place-items:center;font-family:var(--font-d);font-weight:800;font-size:12px;color:var(--teald);flex-shrink:0;margin-top:1px}
.q-text{font-size:15px;color:var(--ink2);line-height:1.6}
.q-note{font-size:13px;color:var(--muted);margin-top:4px}
.q-reply-hint{display:flex;align-items:center;gap:8px;margin-top:20px;padding:12px 16px;background:var(--teals);border:1px solid var(--teall);border-radius:var(--r);font-size:13px;color:var(--teald);font-weight:600}
.pbox{border:1px solid var(--border);border-radius:var(--rl);overflow:hidden}
.ptop{padding:28px 32px;border-bottom:1px solid var(--border);display:flex;align-items:flex-start;justify-content:space-between;gap:20px;flex-wrap:wrap}
.ptitle{font-family:var(--font-d);font-size:22px;font-weight:700;letter-spacing:-.4px;margin-bottom:4px}
.psub2{font-size:14px;color:var(--muted)}
.pamount{font-family:var(--font-d);font-size:46px;font-weight:700;letter-spacing:-2px;color:var(--teal);line-height:1}
.pperiod{font-size:15px;color:var(--muted);margin-top:4px}
.pbody{padding:28px 32px;display:grid;grid-template-columns:1fr 1fr;gap:32px}
.incl-title{font-size:12px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);margin-bottom:14px}
.incl{display:grid;gap:10px}
.ii{display:flex;align-items:flex-start;gap:10px;font-size:14px;color:var(--ink2);line-height:1.5}
.ick{width:18px;height:18px;border-radius:50%;background:var(--teals);display:grid;place-items:center;flex-shrink:0;margin-top:1px}
.ick svg{width:9px;height:9px;stroke:var(--teald);fill:none;stroke-width:2.5}
.iopt{width:18px;height:18px;border-radius:50%;background:#fef3c7;border:1px solid #fde68a;display:grid;place-items:center;flex-shrink:0;margin-top:1px;font-size:10px;font-weight:700;color:#92400e}
.pfoot{padding:20px 32px;background:var(--bg);border-top:1px solid var(--border);display:flex;align-items:center;gap:14px;flex-wrap:wrap}
.pnote{font-size:13px;color:var(--muted);display:flex;align-items:center;gap:6px}
.faq{display:grid;gap:0}
.fi{border-bottom:1px solid var(--border)}
.fq{display:flex;align-items:center;justify-content:space-between;padding:18px 0;cursor:pointer;font-size:15px;font-weight:600;gap:16px;user-select:none}
.fq:hover{color:var(--teal)}
.ficon{width:26px;height:26px;border-radius:50%;background:var(--bg);border:1px solid var(--border);display:grid;place-items:center;flex-shrink:0;transition:.2s}
.ficon svg{width:11px;height:11px;stroke:var(--muted);fill:none;stroke-width:2;transition:transform .28s}
.fi.open .ficon{background:var(--teals);border-color:var(--teall)}
.fi.open .ficon svg{stroke:var(--teald);transform:rotate(45deg)}
.fa{display:none;padding:0 0 18px;font-size:14px;color:var(--muted);line-height:1.75;max-width:70ch}
.fi.open .fa{display:block}
.cta-band{background:#0a1410;padding:72px 0;position:relative;overflow:hidden}
.cta-band::before{content:'';position:absolute;width:560px;height:560px;border-radius:50%;background:radial-gradient(circle,rgba(13,148,136,.18),transparent 65%);left:50%;top:50%;transform:translate(-50%,-50%);pointer-events:none}
.cta-in{max-width:640px;margin:0 auto;padding:0 24px;text-align:center;position:relative;z-index:2}
.cta-lbl{display:inline-flex;align-items:center;gap:7px;font-size:11px;font-weight:700;letter-spacing:1.4px;text-transform:uppercase;color:#5eead4;margin-bottom:16px}
.cta-lbl::before{content:'';width:16px;height:2px;background:#5eead4;border-radius:2px}
.cta-h{font-family:var(--font-d);font-size:clamp(28px,3.2vw,40px);font-weight:700;letter-spacing:-1px;color:#fff;margin-bottom:14px;line-height:1.08}
.cta-sub{font-size:16px;color:rgba(255,255,255,.55);line-height:1.65;margin-bottom:32px;max-width:46ch;margin-inline:auto}
.cta-acts{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
.btn-cta{background:var(--teal);color:#fff;padding:15px 30px;font-size:15px;box-shadow:0 12px 32px rgba(13,148,136,.3)}
.btn-cta:hover{background:#14b8a6;transform:translateY(-1px)}
.btn-ol{background:transparent;border:1px solid rgba(255,255,255,.2);color:rgba(255,255,255,.8);padding:14px 24px}
.btn-ol:hover{border-color:rgba(255,255,255,.5);color:#fff}
.cta-micro{margin-top:22px;font-size:13px;color:rgba(255,255,255,.35);display:flex;gap:20px;justify-content:center;flex-wrap:wrap}
.cta-micro span::before{content:'✓ ';color:#5eead4}
.foot{background:var(--surface);border-top:1px solid var(--border);padding:18px 24px}
.foot-in{max-width:960px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;gap:16px;font-size:12px;color:var(--muted);flex-wrap:wrap}
.foot-logo{display:flex;align-items:center;gap:8px;color:var(--ink);font-weight:700;font-size:13px}
@media(max-width:860px){.hero-grid{grid-template-columns:1fr}.sc{position:static;top:0}.scope-head,.scope-row{grid-template-columns:1fr 100px}.scope-head>*:nth-child(2),.scope-row>*:nth-child(2){display:none}.pbody{grid-template-columns:1fr}}
@media(max-width:580px){.w{padding:0 18px}.nav-for{display:none}.step{grid-template-columns:40px 1fr;gap:14px}.ptop{flex-direction:column;gap:12px}.snav-link{padding:11px 12px;font-size:12px}}
</style>
</head>
<body>
<?php if ($gtm !== "" && $upsellio_offer_track_public) : ?>
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr($gtm); ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<?php endif; ?>
<div id="bar"></div>

<nav class="nav">
  <div class="nav-in">
    <a href="<?php echo esc_url(home_url("/")); ?>" class="nav-logo nav-logo--home">
      <?php
      $offer_site_name = (string) get_bloginfo("name");
      $offer_logo_ok = function_exists("upsellio_echo_brand_logo_picture")
          && upsellio_echo_brand_logo_picture([
              "img_class" => "brand-logo-nav",
              "sizes" => "140px",
              "fetchpriority" => "high",
          ]);
      if (!$offer_logo_ok) :
          ?>
      <div class="mark">U</div><div class="nav-name"><?php echo esc_html($offer_site_name !== "" ? $offer_site_name : "Upsellio"); ?></div>
      <?php endif; ?>
    </a>
    <div class="nav-for">Oferta dla: <strong><?php echo esc_html($client_name); ?></strong></div>
    <a class="btn btn-p" href="#sec-cennik" onclick="cta('nav')">Zobacz cennik →</a>
  </div>
</nav>

<div class="snav" id="snav">
  <div class="snav-in">
    <div class="snav-link active" data-target="sec-zakres" onclick="jumpTo('sec-zakres')"><div class="snav-num">1</div>Zakres</div>
    <div class="snav-link" data-target="sec-szczegoly" onclick="jumpTo('sec-szczegoly')"><div class="snav-num">2</div>Szczegóły</div>
    <div class="snav-link" data-target="sec-etapy" onclick="jumpTo('sec-etapy')"><div class="snav-num">3</div>Etapy</div>
    <?php
    $nav_i = 4;
    if ($has_questions) :
        ?>
    <div class="snav-link" data-target="sec-pytania" onclick="jumpTo('sec-pytania')"><div class="snav-num"><?php echo esc_html((string) $nav_i); ?></div>Pytania</div>
        <?php
        $nav_i++;
    endif;
    ?>
    <div class="snav-link" data-target="sec-cennik" onclick="jumpTo('sec-cennik')"><div class="snav-num"><?php echo esc_html((string) $nav_i); ?></div>Cennik</div>
    <div class="snav-link" data-target="sec-faq" onclick="jumpTo('sec-faq')"><div class="snav-num"><?php echo esc_html((string) ($nav_i + 1)); ?></div>FAQ</div>
  </div>
</div>

<div class="w">
<div class="hero r">
  <div class="hero-lbl">Oferta indywidualna · <?php echo esc_html($offer_date_label); ?></div>
  <div class="hero-grid">
    <div>
      <h1 class="h1"><?php echo esc_html($offer_title); ?></h1>
      <p class="hero-lead"><?php echo esc_html((string) ($payload["lead"] ?? "")); ?></p>
      <div class="chips">
        <span class="chip chip-t">✓ Bez ukrytych kosztów</span>
        <span class="chip chip-t">✓ Konsultacja wdrożeniowa w cenie</span>
        <span class="chip chip-g">Ważna do: <?php echo esc_html($offer_expires_label); ?></span>
      </div>
      <div class="acts">
        <a class="btn btn-p" href="#sec-cennik" onclick="cta('hero_primary')">Przejdź do ceny →</a>
        <a class="btn btn-g" href="#sec-zakres" onclick="cta('hero_scope')">Zobacz zakres</a>
      </div>
      <?php if ($show_proof) : ?>
      <div class="proof-strip">
        <div class="proof-strip-label">Pracuję m.in. z firmami z Twojej branży</div>
        <div class="proof-logos">
          <?php
          foreach (preg_split("/\r\n|\r|\n/", (string) ($payload["proof_lines"] ?? "")) as $pl) {
              $pl = trim((string) $pl);
              if ($pl !== "") {
                  echo '<span class="proof-logo">' . esc_html($pl) . '</span>';
              }
          }
          ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <div class="sc">
      <div class="sc-for">Oferta dla</div>
      <div class="sc-cli"><?php echo esc_html($client_name); ?></div>
      <div class="sc-rows">
        <div class="sc-row"><span class="sc-l">Opiekun</span><span class="sc-r"><?php echo esc_html($owner_name); ?></span></div>
        <div class="sc-row"><span class="sc-l">Start realizacji</span><span class="sc-r"><?php echo esc_html($timeline !== "" ? $timeline : "Po akceptacji"); ?></span></div>
        <div class="sc-row"><span class="sc-l">Czas trwania</span><span class="sc-r"><?php echo esc_html((string) ($payload["duration"] ?? "")); ?></span></div>
        <div class="sc-row"><span class="sc-l">Model rozliczenia</span><span class="sc-r"><?php echo esc_html((string) ($payload["billing"] ?? "")); ?></span></div>
      </div>
      <div class="sc-price"><?php echo esc_html($price !== "" ? $price : "Wycena w sekcji poniżej"); ?></div>
      <div class="sc-pnote"><?php echo esc_html((string) ($payload["price_note"] ?? "")); ?></div>
      <?php if ($expires_at > 0) : ?>
      <div class="sc-exp">
        <div class="sc-dot"></div>
        Oferta ważna jeszcze <strong id="days-left"><?php echo esc_html((string) $days_left); ?> dni</strong>
      </div>
      <?php endif; ?>

      <div class="sc-commit">
        <div class="sc-commit-label">Co najbardziej Cię interesuje?</div>
        <div class="commit-opts" id="commitOpts">
          <?php if (!empty($services)) : ?>
            <?php foreach ($services as $svc) : ?>
              <?php if (!is_array($svc)) { continue; } ?>
              <label class="commit-opt" onclick="commitSelect(this)">
                <input type="radio" name="commit" value="<?php echo esc_attr(sanitize_key((string) ($svc["key"] ?? ""))); ?>"/>
                <?php echo esc_html((string) ($svc["label"] ?? "")); ?>
                <?php if (!empty($svc["price_hint"])) : ?><span><?php echo esc_html((string) $svc["price_hint"]); ?></span><?php endif; ?>
              </label>
            <?php endforeach; ?>
          <?php else : ?>
            <label class="commit-opt" onclick="commitSelect(this)"><input type="radio" name="commit" value="all"/>Cały zakres</label>
          <?php endif; ?>
        </div>
      </div>

      <a class="btn btn-p" href="#sec-cennik" onclick="cta('sum_card')" id="scCta" style="width:100%;justify-content:center;padding:13px;">Chcę tę ofertę →</a>
      <div class="sc-note">
        <div class="sc-shield"><svg viewBox="0 0 20 20"><path d="M10 2L3 5v5c0 4.4 3.1 8.1 7 9 3.9-.9 7-4.6 7-9V5l-7-3Z"/></svg></div>
        Bez zobowiązań do podpisania umowy. Konsultacja wdrożeniowa gratis.
      </div>
    </div>
  </div>
</div>
</div>

<div class="hr"></div>

<div class="w" id="sec-zakres">
<div class="sec r" data-offer-section="zakres">
  <div class="lbl">Zakres działania</div>
  <h2 class="h2">Co dokładnie wchodzi w ofertę</h2>
  <p class="sub">Pełna lista elementów — co jest w cenie, co opcjonalne, a co dostępne jako rozszerzenie.</p>
  <div class="scope">
    <div class="scope-head"><span>Element zakresu</span><span>Kiedy / jak często</span><span>Status</span></div>
    <?php if (!empty($payload["has_google"])) : ?>
    <div class="scope-group"><div class="scope-group-dot"></div>Google Ads</div>
    <div class="scope-row"><div><div class="sn">Audyt konta i kampanii</div><div class="sd">Analiza struktury konta, grup reklam i historii wyników.</div></div><div class="sw">Tydzień 1</div><div><span class="tag ti">W cenie</span></div></div>
    <div class="scope-row"><div><div class="sn">Strategia kampanii</div><div class="sd">Słowa kluczowe, struktura, wykluczenia — zatwierdzasz przed startem.</div></div><div class="sw">Tydzień 2</div><div><span class="tag ti">W cenie</span></div></div>
    <div class="scope-row"><div><div class="sn">Konfiguracja i uruchomienie</div><div class="sd">Pełna konfiguracja konta, śledzenia konwersji i testów.</div></div><div class="sw">Tydzień 3</div><div><span class="tag ti">W cenie</span></div></div>
    <div class="scope-row"><div><div class="sn">Bieżąca optymalizacja</div><div class="sd">Cotygodniowe korekty stawek, tekstów i budżetu.</div></div><div class="sw">Ciągle</div><div><span class="tag ti">W cenie</span></div></div>
    <?php endif; ?>
    <?php if (!empty($payload["has_meta"])) : ?>
    <div class="scope-group"><div class="scope-group-dot"></div>Meta Ads</div>
    <div class="scope-row"><div><div class="sn">Audyt konta Meta</div><div class="sd">Pixel, grupy odbiorców, struktura zestawów reklam.</div></div><div class="sw">Tydzień 1</div><div><span class="tag ti">W cenie</span></div></div>
    <div class="scope-row"><div><div class="sn">Strategia lejka</div><div class="sd">TOF / MOF / BOF z dopasowanym komunikatem.</div></div><div class="sw">Tydzień 2</div><div><span class="tag ti">W cenie</span></div></div>
    <div class="scope-row"><div><div class="sn">Pixel + CAPI</div><div class="sd">Konfiguracja zdarzeń pod atrybucję po zmianach iOS.</div></div><div class="sw">Tydzień 1–2</div><div><span class="tag ti">W cenie</span></div></div>
    <div class="scope-row"><div><div class="sn">Optymalizacja i testy</div><div class="sd">Cotygodniowe korekty budżetów, kreacji i grup docelowych.</div></div><div class="sw">Ciągle</div><div><span class="tag ti">W cenie</span></div></div>
    <?php endif; ?>
    <?php if (!empty($payload["has_web"])) : ?>
    <div class="scope-group"><div class="scope-group-dot"></div>Strona / landing</div>
    <div class="scope-row"><div><div class="sn">Brief i warsztat</div><div class="sd">Cel, grupa docelowa, struktura sekcji i CTA.</div></div><div class="sw">Tydzień 1</div><div><span class="tag ti">W cenie</span></div></div>
    <div class="scope-row"><div><div class="sn">Projekt i copy</div><div class="sd">Układ desktop/mobile i teksty pod konwersję.</div></div><div class="sw">Tydz. 2–3</div><div><span class="tag ti">W cenie</span></div></div>
    <div class="scope-row"><div><div class="sn">Wdrożenie WordPress</div><div class="sd">Responsywnie, szybko, z panelem edycji.</div></div><div class="sw">Tydz. 3–4</div><div><span class="tag ti">W cenie</span></div></div>
    <?php endif; ?>
    <div class="scope-group"><div class="scope-group-dot"></div>Wspólne</div>
    <div class="scope-row"><div><div class="sn">GA4 + GTM</div><div class="sd">Śledzenie konwersji i atrybucji kanałów.</div></div><div class="sw">Start</div><div><span class="tag ti">W cenie</span></div></div>
    <div class="scope-row"><div><div class="sn">Raport cotygodniowy</div><div class="sd">Wydatki, konwersje, wnioski i plan na kolejny tydzień.</div></div><div class="sw">Co tydzień</div><div><span class="tag ti">W cenie</span></div></div>
    <div class="scope-row"><div><div class="sn">Rozmowa strategiczna</div><div class="sd">Comiesięczny przegląd wyników i priorytetów.</div></div><div class="sw">1× / mies.</div><div><span class="tag ti">W cenie</span></div></div>
    <?php
    $extra_scope = (string) get_post_meta($offer_id, "_ups_offer_scope_extra_html", true);
    if ($extra_scope !== "") {
        echo wp_kses_post($extra_scope);
    }
    ?>
  </div>
</div>
</div>

<div class="hr"></div>

<div class="w" id="sec-szczegoly" data-offer-section="szczegoly">
<div class="sec r">
  <div class="lbl">Szczegóły oferty</div>
  <h2 class="h2">Dodatkowe informacje</h2>
  <div class="oc"><?php echo wp_kses_post($content_html); ?></div>
</div>
</div>

<div class="hr"></div>

<div class="w" id="sec-etapy">
<div class="sec r" data-offer-section="etapy">
  <div class="lbl">Plan realizacji</div>
  <h2 class="h2">Co dzieje się krok po kroku</h2>
  <p class="sub">Wiesz co, kiedy i czego potrzebuję po Twojej stronie.</p>
  <div class="steps">
    <?php
    $steps = [
        ["when" => "Tydzień 1", "title" => "Onboarding i diagnoza", "desc" => "Dostępy do kont, audyt, PDF z priorytetami.", "tags" => ["Dostępy", "Raport PDF", "Plan 30 dni"]],
        ["when" => "Tydzień 2", "title" => "Strategia i zatwierdzenie", "desc" => "Roadmapa 90 dni, KPI, budżety — zatwierdzamy przed startem kampanii.", "tags" => ["Strategia", "Rozmowa 30 min"]],
        ["when" => "Tydzień 3–4", "title" => "Konfiguracja i start", "desc" => "Kampanie, śledzenie, testy przed uruchomieniem.", "tags" => ["Kampanie live", "GA4"]],
        ["when" => "Miesiąc 1–3", "title" => "Optymalizacja", "desc" => "Raporty tygodniowe, korekty na danych.", "tags" => ["Raport e-mail", "Optymalizacja"]],
        ["when" => "Miesiąc 3+", "title" => "Skalowanie", "desc" => "Skala budżetu w kanałach z najlepszym CPL / ROI.", "tags" => ["Mix kanałów", "Plan skali"]],
    ];
    foreach ($steps as $i => $st) :
        $n = $i + 1;
        ?>
    <div class="step">
      <div class="sl"><div class="snum"><?php echo esc_html((string) $n); ?></div><div class="sline"></div></div>
      <div>
        <div class="swhen"><?php echo esc_html((string) $st["when"]); ?></div>
        <div class="st"><?php echo esc_html((string) $st["title"]); ?></div>
        <div class="sdesc"><?php echo esc_html((string) $st["desc"]); ?></div>
        <div class="sdels">
          <?php foreach ($st["tags"] as $tg) : ?><span class="sdel"><?php echo esc_html((string) $tg); ?></span><?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
</div>

<?php if ($has_questions) : ?>
<div class="hr"></div>
<div class="w" id="sec-pytania">
<div class="sec r" data-offer-section="pytania">
  <div class="lbl">Pytania do Ciebie</div>
  <h2 class="h2">Potrzebuję kilku informacji</h2>
  <p class="sub">Krótka lista — ułatwi start i jakość wdrożenia.</p>
  <div class="questions-sec">
    <?php foreach ($questions as $qi => $q) : ?>
    <div class="q-item">
      <div class="q-num"><?php echo esc_html((string) ($qi + 1)); ?></div>
      <div>
        <div class="q-text"><?php echo esc_html((string) ($q["text"] ?? "")); ?></div>
        <?php if (!empty($q["note"])) : ?><div class="q-note"><?php echo esc_html((string) $q["note"]); ?></div><?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <div class="q-reply-hint">
      <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 4h16v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V4Z"/><path d="m2 4 8 7 8-7"/></svg>
      Odpowiedz e-mailem na adres <strong><?php echo esc_html($owner_email); ?></strong> lub podczas konsultacji wdrożeniowej.
    </div>
  </div>
</div>
</div>
<?php endif; ?>

<div class="hr"></div>

<div class="w" id="sec-cennik">
<div class="sec r" id="pricing-section-element" data-offer-section="pricing">
  <div class="lbl">Inwestycja</div>
  <h2 class="h2">Transparentna wycena</h2>
  <p class="sub">Jeden abonament w zakresie tej oferty — bez ukrytych opłat.</p>
  <div class="pbox">
    <div class="ptop">
      <div>
        <div class="ptitle"><?php echo esc_html($offer_title); ?></div>
        <div class="psub2">Wycena dla <?php echo esc_html($client_name); ?></div>
      </div>
      <div>
        <div class="pamount"><?php echo esc_html($price !== "" ? $price : "Do uzgodnienia"); ?></div>
        <div class="pperiod"><?php echo esc_html((string) ($payload["price_note"] ?? "")); ?></div>
      </div>
    </div>
    <div class="pbody">
      <div>
        <div class="incl-title">Zawarte w abonamencie</div>
        <div class="incl"><?php upsellio_offer_render_lines_as_checklist((string) ($payload["include_lines"] ?? ""), false); ?></div>
      </div>
      <div>
        <div class="incl-title">Opcjonalne rozszerzenia</div>
        <div class="incl"><?php upsellio_offer_render_lines_as_checklist((string) ($payload["option_lines"] ?? ""), true); ?></div>
      </div>
    </div>
    <div class="pfoot">
      <a class="btn btn-p" href="mailto:<?php echo esc_attr($owner_email); ?>?subject=<?php echo rawurlencode("Akceptacja oferty: " . $offer_title); ?>" onclick="cta('pricing_accept')" style="padding:13px 26px;font-size:15px;">Akceptuję mailowo →</a>
      <form method="post" style="display:inline">
        <?php wp_nonce_field("ups_offer_accept_" . $offer_id, "ups_offer_accept_nonce"); ?>
        <button class="btn btn-p" type="submit" onclick="cta('pricing_accept_form')" style="padding:13px 26px;font-size:15px;border:none"><?php echo esc_html($cta_text); ?></button>
      </form>
      <a class="btn btn-g" href="mailto:<?php echo esc_attr($owner_email); ?>" onclick="cta('pricing_question')">Mam pytanie</a>
      <div class="pnote" style="margin-left:auto">
        <svg width="13" height="13" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M10 2L3 5v5c0 4.4 3.1 8.1 7 9 3.9-.9 7-4.6 7-9V5l-7-3Z"/></svg>
        Ważna do <strong style="color:var(--ink)"><?php echo esc_html($offer_expires_label); ?></strong>
      </div>
    </div>
  </div>
</div>
</div>

<div class="hr"></div>

<div class="w" id="sec-faq">
<div class="sec r" data-offer-section="faq">
  <div class="lbl">FAQ</div>
  <h2 class="h2">Najczęstsze pytania</h2>
  <div class="faq">
    <div class="fi open"><div class="fq" onclick="faqToggle(this.parentElement)">Kiedy widać pierwsze efekty?<div class="ficon"><svg viewBox="0 0 14 14"><path d="M7 2v10M2 7h10"/></svg></div></div><div class="fa">Pierwsze dane po kilku dniach; stabilna jakość leadów zwykle po 3–4 tygodniach uczenia algorytmu.</div></div>
    <div class="fi"><div class="fq" onclick="faqToggle(this.parentElement)">Czy mogę wybrać tylko jeden kanał?<div class="ficon"><svg viewBox="0 0 14 14"><path d="M7 2v10M2 7h10"/></svg></div></div><div class="fa">Tak — zakres jest dopasowywany; możesz rozszerzać współpracę w dowolnym momencie.</div></div>
    <div class="fi"><div class="fq" onclick="faqToggle(this.parentElement)">Ile czasu zajmuje po stronie klienta?<div class="ficon"><svg viewBox="0 0 14 14"><path d="M7 2v10M2 7h10"/></svg></div></div><div class="fa">Onboarding to zwykle kilka godzin w pierwszym tygodniu; potem ok. 30–45 min miesięcznie na sync.</div></div>
  </div>
</div>
</div>

<section class="cta-band">
  <div class="cta-in">
    <div class="cta-lbl">Następny krok</div>
    <h2 class="cta-h">Zacznijmy od rozmowy</h2>
    <p class="cta-sub">Oferta ważna do <?php echo esc_html($offer_expires_label); ?>. Odpowiadam w ciągu 24h roboczych.</p>
    <div class="cta-acts">
      <a class="btn btn-cta" href="mailto:<?php echo esc_attr($owner_email); ?>?subject=<?php echo rawurlencode("Akceptacja: " . $offer_title); ?>" onclick="cta('final_email')">Akceptuję — piszę →</a>
      <?php if ($owner_phone !== "") : ?>
      <a class="btn btn-ol" href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $owner_phone)); ?>" onclick="cta('final_phone')">Zadzwoń</a>
      <?php endif; ?>
    </div>
    <div class="cta-micro">
      <span>Bez zobowiązań przed rozmową</span>
      <span>Odpowiedź w 24h robocze</span>
    </div>
  </div>
</section>

<footer class="foot">
  <div class="foot-in">
    <div class="foot-logo">
      <a href="<?php echo esc_url(home_url("/")); ?>">
        <?php
        $offer_foot_name = (string) get_bloginfo("name");
        $offer_foot_logo_ok = function_exists("upsellio_echo_brand_logo_picture")
            && upsellio_echo_brand_logo_picture([
                "img_class" => "brand-logo-foot",
                "sizes" => "120px",
                "loading" => "lazy",
            ]);
        if (!$offer_foot_logo_ok) :
            ?>
        <div class="mark" style="width:26px;height:26px;font-size:13px;border-radius:7px">U</div><?php echo esc_html($offer_foot_name !== "" ? $offer_foot_name : "Upsellio"); ?>
        <?php endif; ?>
      </a>
    </div>
    <div>Przygotowano <?php echo esc_html($offer_created_label); ?> · Ważna do <?php echo esc_html($offer_expires_label); ?></div>
    <div>ID: <code style="font-size:11px;background:var(--bg);padding:2px 6px;border-radius:4px;font-family:monospace"><?php echo esc_html($slug); ?></code></div>
  </div>
</footer>

<script>
(function(){
var offerId=<?php echo (int) $offer_id; ?>;
var clientId=<?php echo (int) $client_id; ?>;
var personId=<?php echo wp_json_encode((string) $person_id); ?>;
var ajaxUrl=<?php echo wp_json_encode((string) $ajax_url); ?>;
var __upsTrackPublic=<?php echo $upsellio_offer_track_public ? "true" : "false"; ?>;
var q=new URLSearchParams(window.location.search||'');
var utmSource=q.get('utm_source')||'';
var utmCampaign=q.get('utm_campaign')||'';
var gclid=q.get('gclid')||'';
window.addEventListener('scroll',function(){
  var h=document.documentElement.scrollHeight-document.documentElement.clientHeight;
  document.getElementById('bar').style.width=(h>0?Math.min(window.scrollY/h*100,100):0)+'%';
},{passive:true});
var snavLinks=document.querySelectorAll('.snav-link');
var snavSections=Array.from(snavLinks).map(function(l){return document.getElementById(l.dataset.target);}).filter(Boolean);
function updateSnav(){
  var top=window.scrollY+130;
  var active=null;
  snavSections.forEach(function(s){if(s&&s.offsetTop<=top)active=s;});
  if(!active)active=snavSections[0];
  snavLinks.forEach(function(l){l.classList.toggle('active',active&&l.dataset.target===active.id);});
}
window.addEventListener('scroll',updateSnav,{passive:true});
updateSnav();
function jumpTo(id){
  var el=document.getElementById(id);
  if(!el)return;
  var offset=el.getBoundingClientRect().top+window.scrollY-120;
  window.scrollTo({top:offset,behavior:'smooth'});
  trackEvent('offer_section_click',{section_id:id.replace('sec-','')});
}
var ro=new IntersectionObserver(function(e){e.forEach(function(x){if(x.isIntersecting)x.target.classList.add('in');});},{threshold:.1});
document.querySelectorAll('.r').forEach(function(el){ro.observe(el);});
function trackEvent(eventName,extra){
  if(!__upsTrackPublic)return;
  extra=extra||{};
  var body=new URLSearchParams();
  body.append('action','upsellio_offer_track_event');
  body.append('offer_id',String(offerId));
  body.append('client_id',String(clientId));
  body.append('person_id',String(personId||''));
  body.append('utm_source',utmSource);
  body.append('utm_campaign',utmCampaign);
  body.append('gclid',gclid);
  body.append('event_name',eventName);
  body.append('page',window.location.href);
  if(extra.section_id)body.append('section_id',String(extra.section_id));
  if(extra.seconds!==undefined&&extra.seconds!==null)body.append('seconds',String(extra.seconds));
  if(navigator.sendBeacon)navigator.sendBeacon(ajaxUrl,body);
  else fetch(ajaxUrl,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body.toString(),credentials:'same-origin',keepalive:true}).catch(function(){});
}
function pushDl(ev,ex){if(!__upsTrackPublic)return;window.dataLayer=window.dataLayer||[];dataLayer.push(Object.assign({event:ev,offer_id:String(offerId),person_id:String(personId||'')},ex||{}));}
trackEvent('offer_view',{});
var so=new IntersectionObserver(function(e){e.forEach(function(x){if(x.isIntersecting&&!x.target._tsv){x.target._tsv=true;var sid=x.target.getAttribute('data-offer-section')||'';if(sid)trackEvent('offer_section_view',{section_id:sid});}});},{threshold:0.4});
document.querySelectorAll('[data-offer-section]').forEach(function(el){so.observe(el);});
var pEl=document.getElementById('pricing-section-element'),pStart=null,pTick=null,sent={};
var po=new IntersectionObserver(function(e){e.forEach(function(x){
  if(x.isIntersecting){
    pStart=Date.now();
    pTick=setInterval(function(){
      var s=Math.round((Date.now()-pStart)/1000);
      trackEvent('offer_engagement_tick',{section_id:'pricing',seconds:s});
      [30,60,120].forEach(function(t){if(s>=t&&!sent[t]){sent[t]=true;trackEvent('offer_stage_detected',{section_id:'pricing_'+t+'s'});}});
      if(s>=120){clearInterval(pTick);pTick=null;}
    },10000);
  }else{
    if(pTick){clearInterval(pTick);pTick=null;}
    if(pStart){trackEvent('offer_pricing_exit',{section_id:'pricing',seconds:Math.round((Date.now()-pStart)/1000)});pStart=null;}
  }
});},{threshold:0.5});
if(pEl)po.observe(pEl);
window.commitSelect=function(el){
  document.querySelectorAll('.commit-opt').forEach(function(o){o.classList.remove('sel');});
  el.classList.add('sel');
  var inp=el.querySelector('input');var val=inp?inp.value:'';
  trackEvent('offer_commit_selected',{section_id:String(val||'')});
  var ctaEl=document.getElementById('scCta');
  if(ctaEl&&val){var t=el.textContent.trim().split('\n')[0];ctaEl.textContent='Chcę: '+t+' →';}
};
window.cta=function(l){pushDl('offer_cta_click',{cta_label:l});trackEvent('offer_cta_click',{section_id:String(l)});};
window.faqToggle=function(i){var o=i.classList.contains('open');document.querySelectorAll('.fi.open').forEach(function(el){el.classList.remove('open');});if(!o)i.classList.add('open');};
})();
</script>
</body>
</html>
    <?php
}
