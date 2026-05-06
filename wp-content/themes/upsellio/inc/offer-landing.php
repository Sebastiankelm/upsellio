<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_get_site_gtm_container_id()
{
    return (string) apply_filters("upsellio_site_gtm_container_id", "GTM-KM9J5XC2");
}

/**
 * Pole timeline z meta może być stringiem lub tablicą (np. błędny zapis z JSON) — unikamy wyświetlania „Array”.
 */
function upsellio_offer_normalize_timeline_display($raw): string
{
    if (is_array($raw)) {
        if ($raw === []) {
            return "";
        }
        $first = array_key_exists(0, $raw) ? $raw[0] : reset($raw);
        if (is_array($first)) {
            return upsellio_offer_normalize_timeline_display($first);
        }

        return trim((string) $first);
    }

    return trim((string) $raw);
}

/**
 * Etykiety z AI / JSON: encje HTML oraz literalne sekwencje \uXXXX w tekście.
 */
function upsellio_offer_normalize_ai_display_text($text): string
{
    $text = (string) $text;
    if ($text === "") {
        return "";
    }
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, "UTF-8");
    $out = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', static function ($m) {
        return mb_convert_encoding(pack("H*", $m[1]), "UTF-8", "UTF-16BE");
    }, $text);

    return is_string($out) ? $out : $text;
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
        "services_json" => wp_json_encode([]),
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
        $msg = $use_optional_icon
            ? "Brak dodatkowych opcji w tej propozycji."
            : "Doprecyzowujemy zakres — szczegóły omówimy na rozmowie.";
        echo '<div class="incl-empty"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><span>' . esc_html($msg) . "</span></div>";
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
    $is_preview_mode = isset($_GET["preview_mode"]) && current_user_can("edit_post", $offer_id);
    $slug = (string) get_post_meta($offer_id, "_ups_offer_public_slug", true);
    $client_id = (int) get_post_meta($offer_id, "_ups_offer_client_id", true);
    $person_id = (string) get_post_meta($offer_id, "_ups_offer_person_id", true);
    if ($person_id === "" && $client_id > 0) {
        $person_id = (string) get_post_meta($client_id, "_ups_client_person_id", true);
    }
    $client_name = $client_id > 0 ? (string) get_the_title($client_id) : "Klient";
    $price = (string) get_post_meta($offer_id, "_ups_offer_price", true);
    $timeline = upsellio_offer_normalize_timeline_display(get_post_meta($offer_id, "_ups_offer_timeline", true));
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
    $client_email_stored = $client_id > 0 ? (string) get_post_meta($client_id, "_ups_client_email", true) : "";
    $client_email_ok = is_email($client_email_stored);
    $public_nonce = wp_create_nonce("ups_offer_public_" . $offer_id);
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
    foreach ($services as $si => $svc_row) {
        if (!is_array($svc_row)) {
            continue;
        }
        if (isset($svc_row["label"])) {
            $services[$si]["label"] = upsellio_offer_normalize_ai_display_text((string) $svc_row["label"]);
        }
        if (isset($svc_row["price_hint"])) {
            $services[$si]["price_hint"] = upsellio_offer_normalize_ai_display_text((string) $svc_row["price_hint"]);
        }
    }
    $proof_lines_arr = array_values(array_filter(array_map("trim", preg_split("/\r\n|\r|\n/", (string) ($payload["proof_lines"] ?? "")))));
    $show_proof = isset($payload["show_proof"]) ? !empty($payload["show_proof"]) : count($proof_lines_arr) >= 2;
    $ajax_url = admin_url("admin-ajax.php");
    $gtm = upsellio_get_site_gtm_container_id();
    $upsellio_offer_track_public = !function_exists("upsellio_should_load_public_tracking_tags") || upsellio_should_load_public_tracking_tags();
    $offer_title = (string) $offer->post_title;
    $content_html = (string) apply_filters("the_content", (string) $offer->post_content);
    $has_offer_details = trim((string) $offer->post_content) !== "";

    $has_google = !empty($payload["has_google"]);
    $has_meta = !empty($payload["has_meta"]);
    $has_web = !empty($payload["has_web"]);
    $services_count = (int) $has_google + (int) $has_meta + (int) $has_web;

    $scope_catalog = [
        ["key" => "g_audit", "group" => "google", "title" => "Audyt konta i kampanii", "desc" => "Analiza struktury konta, grup reklam i historii wyników.", "when" => "Tydzień 1"],
        ["key" => "g_strategy", "group" => "google", "title" => "Strategia kampanii", "desc" => "Słowa kluczowe, struktura, wykluczenia — zatwierdzasz przed startem.", "when" => "Tydzień 2"],
        ["key" => "g_setup", "group" => "google", "title" => "Konfiguracja i uruchomienie", "desc" => "Pełna konfiguracja konta, śledzenia konwersji i testów.", "when" => "Tydzień 3"],
        ["key" => "g_optim", "group" => "google", "title" => "Bieżąca optymalizacja", "desc" => "Cotygodniowe korekty stawek, tekstów i budżetu.", "when" => "Ciągle"],
        ["key" => "g_pmax", "group" => "google", "title" => "Performance Max / Shopping", "desc" => "Kampanie produktowe z feed-em produktów (e-commerce).", "when" => "Tydzień 2-3", "optional" => true],
        ["key" => "m_audit", "group" => "meta", "title" => "Audyt konta Meta", "desc" => "Pixel, grupy odbiorców, struktura zestawów reklam.", "when" => "Tydzień 1"],
        ["key" => "m_funnel", "group" => "meta", "title" => "Strategia lejka", "desc" => "TOF / MOF / BOF z dopasowanym komunikatem.", "when" => "Tydzień 2"],
        ["key" => "m_pixel", "group" => "meta", "title" => "Pixel + Conversions API", "desc" => "Konfiguracja zdarzeń pod atrybucję po zmianach iOS.", "when" => "Tydzień 1–2"],
        ["key" => "m_optim", "group" => "meta", "title" => "Optymalizacja i testy kreacji", "desc" => "Cotygodniowe korekty budżetów, kreacji i grup docelowych.", "when" => "Ciągle"],
        ["key" => "m_creative", "group" => "meta", "title" => "Produkcja kreacji", "desc" => "Body copy, hooki, warianty wideo i grafik.", "when" => "Co 2 tyg.", "optional" => true],
        ["key" => "w_brief", "group" => "web", "title" => "Brief i warsztat", "desc" => "Cel, grupa docelowa, struktura sekcji i CTA.", "when" => "Tydzień 1"],
        ["key" => "w_design", "group" => "web", "title" => "Projekt i copy", "desc" => "Układ desktop/mobile i teksty pod konwersję.", "when" => "Tydz. 2–3"],
        ["key" => "w_dev", "group" => "web", "title" => "Wdrożenie WordPress", "desc" => "Responsywnie, szybko, z panelem edycji.", "when" => "Tydzień 3–4"],
        ["key" => "w_perf", "group" => "web", "title" => "Optymalizacja Core Web Vitals", "desc" => "Lazy load, kompresja, lighthouse 90+.", "when" => "Tydzień 3-4"],
        ["key" => "w_seo", "group" => "web", "title" => "SEO podstawowe", "desc" => "Struktura, meta tagi, sitemap, schema.org.", "when" => "Tydzień 4"],
        ["key" => "c_ga4", "group" => "common", "title" => "GA4 + Google Tag Manager", "desc" => "Śledzenie konwersji, atrybucji kanałów, raportowanie.", "when" => "Start"],
        ["key" => "c_report", "group" => "common", "title" => "Raport cotygodniowy", "desc" => "Wydatki, konwersje, wnioski i plan na kolejny tydzień.", "when" => "Co tydzień"],
        ["key" => "c_strategic", "group" => "common", "title" => "Rozmowa strategiczna", "desc" => "Comiesięczny przegląd wyników i priorytetów.", "when" => "1× / mies."],
        ["key" => "c_account", "group" => "common", "title" => "Dedykowany account manager", "desc" => "Bez juniorów - prowadzę osobiście od pierwszej rozmowy do raportu.", "when" => "Ciągle"],
    ];

    $scope_items_meta = get_post_meta($offer_id, "_ups_offer_scope_items", true);
    $scope_items_selected = is_array($scope_items_meta) ? array_map("strval", $scope_items_meta) : [];

    if (empty($scope_items_selected)) {
        foreach ($scope_catalog as $item) {
            if (!empty($item["optional"])) {
                continue;
            }
            $g = (string) ($item["group"] ?? "");
            if ($g === "google" && $has_google) {
                $scope_items_selected[] = $item["key"];
            }
            if ($g === "meta" && $has_meta) {
                $scope_items_selected[] = $item["key"];
            }
            if ($g === "web" && $has_web) {
                $scope_items_selected[] = $item["key"];
            }
            if ($g === "common" && $services_count > 0) {
                $scope_items_selected[] = $item["key"];
            }
        }
    }

    $scope_grouped = ["google" => [], "meta" => [], "web" => [], "common" => []];
    foreach ($scope_catalog as $item) {
        $key = (string) ($item["key"] ?? "");
        if (in_array($key, $scope_items_selected, true)) {
            $g = (string) ($item["group"] ?? "common");
            if (isset($scope_grouped[$g])) {
                $scope_grouped[$g][] = $item;
            }
        }
    }
    $scope_group_labels = [
        "google" => "Google Ads",
        "meta" => "Meta Ads (Facebook + Instagram)",
        "web" => "Strona / landing page",
        "common" => "Wspólne dla całego zakresu",
    ];

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
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@700;800&amp;family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&amp;display=swap" rel="stylesheet"/>
<script id="Cookiebot" src="https://consent.cookiebot.com/uc.js" data-cbid="91229b76-132c-42e8-9021-9542287ad319" data-blockingmode="auto" type="text/javascript"></script>
<?php if ($gtm !== "" && $upsellio_offer_track_public) : ?>
<script type="text/plain" data-cookieconsent="marketing">(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({"gtm.start":new Date().getTime(),event:"gtm.js"});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!="dataLayer"?"&l="+l:"";j.async=true;j.src="https://www.googletagmanager.com/gtm.js?id="+i+dl;f.parentNode.insertBefore(j,f);})(window,document,"script","dataLayer",<?php echo wp_json_encode((string) $gtm); ?>);</script>
<?php endif; ?>
<script>
window.dataLayer=window.dataLayer||[];
window.UPS=<?php echo wp_json_encode([
    "offer_id" => (string) $offer_id,
    "offer_slug" => (string) $slug,
    "offer_title" => (string) $offer_title,
    "person_id" => (string) $person_id,
    "utm_source" => "",
    "utm_campaign" => "",
    "gclid" => "",
], JSON_UNESCAPED_UNICODE); ?>;
(function(){var q=new URLSearchParams(window.location.search||'');UPS.utm_source=q.get('utm_source')||'';UPS.utm_campaign=q.get('utm_campaign')||'';UPS.gclid=q.get('gclid')||'';})();
</script>
<?php if ($upsellio_offer_track_public) : ?>
<script type="text/plain" data-cookieconsent="marketing">
dataLayer.push({event:"offer_view",offer_id:UPS.offer_id,offer_title:UPS.offer_title,person_id:UPS.person_id,utm_source:UPS.utm_source,utm_campaign:UPS.utm_campaign,gclid:UPS.gclid});
</script>
<?php endif; ?>
<style>
:root{
  --bg:#fafaf7;--surface:#fff;--ink:#0a1410;--ink2:#2e2e2a;--muted:#6b6b63;
  --border:#e6e6e0;--teal:#0d9488;--tealh:#0f766e;--teald:#134e4a;
  --teals:#ccfbf1;--teall:#99f6e4;
  --font-d:'Bricolage Grotesque',sans-serif;--font-b:'DM Sans',sans-serif;--r:14px;--rl:22px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth;scroll-padding-top:132px}
[id^="sec-"]{scroll-margin-top:132px}
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
.process-bar{padding:20px 0 8px}
.process-bar-inner{padding:22px;background:var(--surface);border:1px solid var(--border);border-radius:var(--rl);box-shadow:0 4px 14px rgba(15,23,42,.04)}
.process-bar-head{display:flex;justify-content:space-between;align-items:center;gap:14px;margin-bottom:16px}
.process-bar-label{font-family:var(--font-d);font-weight:800;font-size:14px;letter-spacing:-.3px;color:var(--ink)}
.process-bar-meta{font-size:12px;color:var(--muted);font-weight:600}
.process-bar-meta strong{color:var(--teal);font-weight:800}
.process-track{position:relative;padding:0 16px}
.process-track::before{content:"";position:absolute;top:14px;left:16px;right:16px;height:3px;background:#f1f1ec;border-radius:99px;z-index:1}
.process-track-fill{position:absolute;top:14px;left:16px;height:3px;background:var(--teal);border-radius:99px;z-index:2;width:calc(33.333% + 10px);transition:width .3s ease}
.process-steps{position:relative;display:grid;grid-template-columns:repeat(4,1fr);gap:10px;z-index:3}
.process-step{position:relative;background:var(--surface);border:1.5px solid var(--border);border-radius:12px;padding:32px 12px 14px;text-align:center;transition:border-color .2s,box-shadow .2s}
.process-step::before{content:"";position:absolute;top:-8px;left:50%;width:16px;height:16px;border-radius:50%;background:var(--surface);border:3px solid var(--border);transform:translateX(-50%);z-index:4;transition:background .2s,border-color .2s}
.process-step.is-done{border-color:rgba(13,148,136,.28);background:linear-gradient(180deg,#fff,#f8fdfc)}
.process-step.is-done::before{background:var(--teal);border-color:var(--teal)}
.process-step.is-current{border-color:var(--teal);box-shadow:0 8px 22px rgba(13,148,136,.14)}
.process-step.is-current::before{background:var(--teal);border-color:var(--teal);box-shadow:0 0 0 5px rgba(13,148,136,.14)}
.process-step-num{display:block;font-size:9px;color:var(--muted);margin-bottom:4px;letter-spacing:1px;text-transform:uppercase;font-weight:800;font-family:var(--font-d)}
.process-step.is-done .process-step-num{color:var(--tealh)}
.process-step.is-current .process-step-num{color:var(--teal)}
.process-step-name{font-family:var(--font-d);font-weight:800;font-size:13px;color:var(--ink);letter-spacing:-.2px;margin-bottom:5px;line-height:1.15}
.process-step.is-current .process-step-name{color:var(--teal)}
.process-step-desc{font-size:11px;color:var(--muted);line-height:1.45;font-weight:500}
.process-step.is-current .process-step-desc{color:var(--ink2);font-weight:600}
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
.acts{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.acts--hero{flex-direction:column;align-items:flex-start;gap:12px}
.acts-link{font-size:14px;font-weight:600;color:var(--teal);text-decoration:underline;text-underline-offset:3px}
.acts-link:hover{color:var(--tealh)}
.proof-strip{margin-top:28px;padding-top:20px;border-top:1px solid var(--border)}
.proof-strip-label{font-size:11px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--muted);margin-bottom:10px}
.proof-logos{display:flex;flex-wrap:wrap;gap:8px}
.proof-logo{padding:5px 12px;background:var(--bg);border:1px solid var(--border);border-radius:999px;font-size:12px;font-weight:600;color:var(--muted)}
.sc{background:var(--surface);border:1px solid var(--border);border-radius:var(--rl);padding:24px;position:sticky;top:112px;box-shadow:0 8px 32px rgba(0,0,0,.07),0 2px 8px rgba(0,0,0,.04)}
.sc-minimal strong{display:block;font-family:var(--font-d);font-size:20px;letter-spacing:-.4px;margin-bottom:8px}
.sc-minimal p{font-size:14px;color:var(--muted);margin-bottom:14px;line-height:1.6}
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
.sc-note{display:flex;gap:9px;align-items:flex-start;margin-top:12px;font-size:12px;color:var(--muted);line-height:1.55}
.sc-shield{width:28px;height:28px;border-radius:50%;background:var(--teals);display:grid;place-items:center;flex-shrink:0}
.sc-shield svg{width:12px;height:12px;stroke:var(--teald);fill:none;stroke-width:1.8}
.scope{border:1px solid var(--border);border-radius:var(--rl);overflow:hidden;min-height:280px;position:relative}
.scope-empty{text-align:center;padding:48px 24px;color:var(--muted)}
.scope-empty strong{display:block;font-size:16px;color:var(--ink);margin-bottom:8px}
.scope-head{display:grid;grid-template-columns:1fr 150px 110px;padding:10px 20px;background:var(--bg);border-bottom:1px solid var(--border);font-size:11px;font-weight:700;letter-spacing:.7px;text-transform:uppercase;color:var(--muted);gap:12px}
.scope-row{display:grid;grid-template-columns:1fr 150px 110px;padding:16px 20px;border-bottom:1px solid var(--border);gap:12px;align-items:start;transition:background .15s}
.scope-row:last-child{border-bottom:none}
.scope-row:hover{background:#f7f7f4}
.scope-group{padding:8px 20px;background:#f4f4f0;border-bottom:1px solid var(--border);font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--muted);display:flex;align-items:center;gap:8px}
.scope-group-dot{width:6px;height:6px;border-radius:50%;background:var(--teal);flex-shrink:0}
.scope-group.is-meta .scope-group-dot{background:#db2777}
.scope-group.is-web .scope-group-dot{background:#4338ca}
.scope-empty{padding:24px;background:var(--bg);border-radius:var(--r);color:var(--muted);text-align:center;line-height:1.55}
.hero-name{font-size:14px;color:var(--muted);margin-bottom:8px;font-weight:500}
.hero-name strong{color:var(--ink);font-weight:700}
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
.q-item textarea{width:100%;margin-top:8px;padding:8px;border:1px solid #e2e8f0;border-radius:6px;font-size:14px}
.q-reply-hint{display:flex;align-items:center;gap:8px;margin-top:20px;padding:12px 16px;background:var(--teals);border:1px solid var(--teall);border-radius:var(--r);font-size:13px;color:var(--teald);font-weight:600}
.pbox{border:1px solid var(--border);border-radius:var(--rl);overflow:hidden}
.ptop{padding:28px 32px;border-bottom:1px solid var(--border);display:flex;align-items:flex-start;justify-content:space-between;gap:20px;flex-wrap:wrap}
.ptitle{font-family:var(--font-d);font-size:22px;font-weight:700;letter-spacing:-.4px;margin-bottom:4px}
.psub2{font-size:14px;color:var(--muted)}
.pamount{font-family:var(--font-d);font-size:46px;font-weight:700;letter-spacing:-2px;color:var(--teal);line-height:1}
.pperiod{font-size:15px;color:var(--muted);margin-top:4px}
.pbody{padding:28px 32px;display:grid;grid-template-columns:1fr 1fr;gap:32px}
.pbody-empty{display:block;padding:32px}
.pbody-empty-msg{text-align:center;max-width:540px;margin:0 auto}
.pbody-empty-msg strong{font-size:16px;display:block;margin-bottom:8px}
.pbody-empty-msg p{color:var(--muted);margin:0;font-size:14px}
.incl-title{font-size:12px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);margin-bottom:14px}
.incl{display:grid;gap:10px}
.incl-empty{display:flex;align-items:center;gap:8px;padding:14px 16px;background:#f8fafc;border:1px dashed #cbd5e1;border-radius:10px;color:#64748b;font-size:13px;font-style:italic}
.incl-empty svg{flex-shrink:0;opacity:.7}
.ii{display:flex;align-items:flex-start;gap:10px;font-size:14px;color:var(--ink2);line-height:1.5}
.ick{width:18px;height:18px;border-radius:50%;background:var(--teals);display:grid;place-items:center;flex-shrink:0;margin-top:1px}
.ick svg{width:9px;height:9px;stroke:var(--teald);fill:none;stroke-width:2.5}
.iopt{width:18px;height:18px;border-radius:50%;background:#fef3c7;border:1px solid #fde68a;display:grid;place-items:center;flex-shrink:0;margin-top:1px;font-size:10px;font-weight:700;color:#92400e}
.pfoot{padding:20px 32px;background:var(--bg);border-top:1px solid var(--border);display:flex;align-items:center;gap:14px;flex-wrap:wrap}
.pfoot--stack{flex-direction:column;align-items:stretch;gap:14px}
.pfoot-primary .btn{width:100%;justify-content:center;padding:14px 26px;font-size:15px;border:none}
.pfoot-secondary{display:flex;gap:10px;flex-wrap:wrap}
.pfoot-meta{display:flex;align-items:center;gap:6px;font-size:13px;color:var(--muted)}
.cta-acts--stack{flex-direction:column;align-items:stretch;max-width:360px;margin-inline:auto}
.cta-acts--stack .btn{width:100%;justify-content:center}
.cta-acts--stack .btn-ol{order:3}
.ups-modal{position:fixed;inset:0;z-index:200;display:flex;align-items:center;justify-content:center;padding:20px;opacity:0;visibility:hidden;pointer-events:none;transition:opacity .2s,visibility .2s}
.ups-modal.is-open{opacity:1;visibility:visible;pointer-events:auto}
.ups-modal__backdrop{position:absolute;inset:0;background:rgba(10,20,16,.55);backdrop-filter:blur(4px)}
.ups-modal__box{position:relative;z-index:1;width:100%;max-width:440px;background:var(--surface);border:1px solid var(--border);border-radius:var(--rl);padding:24px 26px;box-shadow:0 24px 60px rgba(0,0,0,.18)}
.ups-modal__box h3{font-family:var(--font-d);font-size:20px;font-weight:700;margin-bottom:8px;letter-spacing:-.3px}
.ups-modal__box p{font-size:14px;color:var(--muted);line-height:1.55;margin-bottom:16px}
.ups-modal__field{display:block;margin-bottom:14px}
.ups-modal__field span{display:block;font-size:12px;font-weight:600;color:var(--ink2);margin-bottom:6px}
.ups-modal__field input,.ups-modal__field textarea{width:100%;padding:11px 14px;border:1px solid var(--border);border-radius:var(--r);font-family:var(--font-b);font-size:15px;background:var(--bg)}
.ups-modal__field select{width:100%;padding:11px 14px;border:1px solid var(--border);border-radius:var(--r);font-family:var(--font-b);font-size:15px;background:var(--bg)}
.ups-modal__field textarea{min-height:120px;resize:vertical;line-height:1.5}
.ups-modal__actions{display:flex;gap:10px;justify-content:flex-end;margin-top:18px;flex-wrap:wrap}
.ups-modal__err{font-size:13px;color:#b45309;margin-bottom:10px;display:none}
.ups-modal__err.is-visible{display:block}
.ups-modal-progress{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin:0 0 14px}
.ups-modal-progress .step{font-size:11px;padding:6px 8px;border-radius:999px;border:1px solid var(--border);color:var(--muted);text-align:center}
.ups-modal-progress .step.active{background:rgba(11,108,107,.08);border-color:var(--brand);color:var(--brand)}
.ups-modal-step{display:none}
.ups-modal-step.active{display:block}
.ups-radio-group{display:grid;gap:8px}
.ups-radio-group label{display:flex;gap:8px;align-items:center;font-size:14px;color:var(--ink2)}
.ups-toast{position:fixed;bottom:24px;left:50%;transform:translateX(-50%) translateY(120px);z-index:220;max-width:min(420px,calc(100% - 32px));background:var(--ink);color:#fff;padding:14px 20px;border-radius:var(--r);font-size:14px;line-height:1.45;box-shadow:0 12px 40px rgba(0,0,0,.25);transition:transform .35s cubic-bezier(.2,.8,.2,1)}
.ups-toast.is-visible{transform:translateX(-50%) translateY(0)}
.ups-toast strong{color:#5eead4;font-weight:600}
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
@media(max-width:1024px){.hero-grid{grid-template-columns:1fr 280px;gap:28px}.sc{padding:22px}}
@media(max-width:860px){.hero-grid{grid-template-columns:1fr}.sc{position:static;top:0}.scope-head,.scope-row{grid-template-columns:1fr 100px}.scope-head>*:nth-child(2),.scope-row>*:nth-child(2){display:none}.pbody{grid-template-columns:1fr}.process-steps{grid-template-columns:repeat(2,1fr);gap:14px 10px}}
@media(max-width:768px){.hero-grid{grid-template-columns:1fr;gap:24px}.sc{position:static;top:0;width:100%;max-width:none}}
@media(max-width:640px){.hero-grid{gap:20px}.ptop{flex-direction:column;align-items:flex-start}.pamount{font-size:28px}}
@media(max-width:580px){.w{padding:0 18px}.nav-for{display:none}.step{grid-template-columns:40px 1fr;gap:14px}.ptop{flex-direction:column;gap:12px}.snav-link{padding:11px 12px;font-size:12px}.cta-acts--stack .btn-ol{order:unset}.process-step-desc{font-size:10.5px}}
@media(max-width:480px){.h1{font-size:28px!important}.hero-lead{font-size:15px}.w{padding:0 16px}.sc-row{padding:8px 0}}
</style>
</head>
<body>
<?php if ($is_preview_mode) : ?>
<div style="position:fixed;top:0;left:0;right:0;background:#f97316;color:#fff;padding:8px;text-align:center;z-index:9999;font-weight:700;">
  🔍 PODGLĄD — klient zobaczy tę stronę. Tracking wyłączony.
  <a href="<?php echo esc_url(get_edit_post_link($offer_id)); ?>" style="color:#fff;text-decoration:underline;margin-left:12px;">Zamknij i wróć do edycji</a>
</div>
<style>body{padding-top:40px}</style>
<?php endif; ?>
<?php if ($gtm !== "" && $upsellio_offer_track_public) : ?>
<noscript data-cookieconsent="marketing"><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr($gtm); ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
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

<nav class="snav" id="snav" aria-label="Sekcje oferty">
  <div class="snav-in">
    <?php $nav_n = 1; ?>
    <div class="snav-link active" data-target="sec-zakres" onclick="jumpTo('sec-zakres')"><div class="snav-num"><?php echo esc_html((string) $nav_n); ?></div>Zakres</div>
    <?php $nav_n++; ?>
    <?php if ($has_offer_details) : ?>
    <div class="snav-link" data-target="sec-szczegoly" onclick="jumpTo('sec-szczegoly')"><div class="snav-num"><?php echo esc_html((string) $nav_n); ?></div>Szczegóły</div>
    <?php $nav_n++; endif; ?>
    <div class="snav-link" data-target="sec-etapy" onclick="jumpTo('sec-etapy')"><div class="snav-num"><?php echo esc_html((string) $nav_n); ?></div>Etapy</div>
    <?php $nav_n++; ?>
    <?php if ($has_questions) : ?>
    <div class="snav-link" data-target="sec-pytania" onclick="jumpTo('sec-pytania')"><div class="snav-num"><?php echo esc_html((string) $nav_n); ?></div>Pytania</div>
    <?php $nav_n++; endif; ?>
    <div class="snav-link" data-target="sec-cennik" onclick="jumpTo('sec-cennik')"><div class="snav-num"><?php echo esc_html((string) $nav_n); ?></div>Cennik</div>
    <?php $nav_n++; ?>
    <div class="snav-link" data-target="sec-faq" onclick="jumpTo('sec-faq')"><div class="snav-num"><?php echo esc_html((string) $nav_n); ?></div>FAQ</div>
  </div>
</nav>

<div class="w offer-process-bar">
  <div class="process-bar">
    <div class="process-bar-inner">
      <div class="process-bar-head">
        <div class="process-bar-label">Gdzie jesteśmy w procesie współpracy</div>
        <div class="process-bar-meta">Etap <strong>2 z 4</strong></div>
      </div>
      <div class="process-track">
        <div class="process-track-fill"></div>
        <div class="process-steps">
          <div class="process-step is-done">
            <span class="process-step-num">Krok 1 · Zakończony</span>
            <div class="process-step-name">Diagnoza</div>
            <div class="process-step-desc">Krótka rozmowa i audyt — już za nami.</div>
          </div>
          <div class="process-step is-current">
            <span class="process-step-num">Krok 2 · Teraz</span>
            <div class="process-step-name">Oferta</div>
            <div class="process-step-desc">Czytasz zakres, etapy i wycenę dopasowane do rozmowy.</div>
          </div>
          <div class="process-step">
            <span class="process-step-num">Krok 3 · Następnie</span>
            <div class="process-step-name">Akceptacja</div>
            <div class="process-step-desc">Potwierdzenie w sekcji Cennik — umowa i pro forma w 24h.</div>
          </div>
          <div class="process-step">
            <span class="process-step-num">Krok 4 · Potem</span>
            <div class="process-step-name">Wdrożenie</div>
            <div class="process-step-desc">Onboarding, dostępy i start prac według planu.</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="w">
<div class="hero r">
  <div class="hero-lbl">Oferta indywidualna · <?php echo esc_html($offer_date_label); ?></div>
  <div class="hero-grid">
    <div>
      <div class="hero-name">Dla: <strong><?php echo esc_html($client_name); ?></strong></div>
      <h1 class="h1"><?php echo esc_html($offer_title); ?></h1>
      <p class="hero-lead"><?php echo esc_html((string) ($payload["lead"] ?? "")); ?></p>
      <div class="chips">
        <span class="chip chip-t">✓ Bez ukrytych kosztów</span>
        <span class="chip chip-t">✓ Konsultacja wdrożeniowa w cenie</span>
        <span class="chip chip-g">Ważna do: <?php echo esc_html($offer_expires_label); ?></span>
      </div>
      <div class="acts acts--hero">
        <a class="btn btn-p" href="#sec-cennik" onclick="cta('hero_primary')">Przejdź do ceny →</a>
        <a class="acts-link" href="#sec-zakres" onclick="cta('hero_scope')">Zobacz pełny zakres</a>
      </div>
      <?php if ($show_proof && count($proof_lines_arr) >= 2) : ?>
      <div class="proof-strip">
        <div class="proof-strip-label">Pracuję m.in. z firmami z Twojej branży</div>
        <div class="proof-logos">
          <?php
          foreach ($proof_lines_arr as $pl) {
              echo '<span class="proof-logo">' . esc_html($pl) . "</span>";
          }
          ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <?php
    $price_set = trim((string) $price) !== "";
    $has_variants = !empty($services) && count($services) > 1;
    ?>
    <div class="sc">
      <?php if (!$price_set && !$has_variants) : ?>
      <div class="sc-minimal">
        <strong>30-min rozmowa</strong>
        <p>Zaplanujmy diagnozę zanim ustalimy cenę. Bez zobowiązań.</p>
        <a href="#sec-cennik" class="btn btn-p" style="width:100%;justify-content:center;padding:13px;" onclick="cta('sum_card_minimal')">Umów rozmowę →</a>
        <a href="tel:+48575522595" class="acts-link" style="display:inline-block;margin-top:10px">+48 575 522 595</a>
      </div>
      <?php else : ?>
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

      <a class="btn btn-p" href="#pricing-accept-anchor" onclick="cta('sum_card')" id="scCta" style="width:100%;justify-content:center;padding:13px;">Chcę tę ofertę →</a>
      <div class="sc-note">
        <div class="sc-shield"><svg viewBox="0 0 20 20"><path d="M10 2L3 5v5c0 4.4 3.1 8.1 7 9 3.9-.9 7-4.6 7-9V5l-7-3Z"/></svg></div>
        Bez zobowiązań do podpisania umowy. Konsultacja wdrożeniowa gratis.
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
</div>

<div class="hr"></div>

<div class="w" id="sec-zakres">
<div class="sec r" data-offer-section="zakres">
  <div class="lbl">Zakres działania</div>
  <h2 class="h2">Co dokładnie wchodzi w ofertę</h2>
  <p class="sub">Pełna lista elementów dopasowana do Twojej sytuacji — wybrane pozycje ustaliliśmy podczas rozmowy diagnostycznej.</p>
  <?php $any_scope = !empty($payload["has_google"]) || !empty($payload["has_meta"]) || !empty($payload["has_web"]); ?>
  <div class="scope">
    <?php if (!$any_scope) : ?>
    <div class="scope-empty">
      <strong>Zakres skonsultujemy podczas rozmowy</strong>
      <p>Po analizie sytuacji wybierzemy razem optymalny mix usług — Google Ads, Meta, strona lub kombinacja.</p>
    </div>
    <?php else : ?>
    <div class="scope-head"><span>Element zakresu</span><span>Kiedy / jak często</span><span>Status</span></div>
    <?php foreach (["google", "meta", "web", "common"] as $group_key) : ?>
      <?php if (empty($scope_grouped[$group_key])) {
          continue;
      } ?>
      <div class="scope-group<?php echo $group_key === "meta" ? " is-meta" : ($group_key === "web" ? " is-web" : ""); ?>">
        <div class="scope-group-dot"></div>
        <?php echo esc_html($scope_group_labels[$group_key]); ?>
      </div>
      <?php foreach ($scope_grouped[$group_key] as $item) : ?>
        <div class="scope-row">
          <div>
            <div class="sn"><?php echo esc_html((string) ($item["title"] ?? "")); ?></div>
            <div class="sd"><?php echo esc_html((string) ($item["desc"] ?? "")); ?></div>
          </div>
          <div class="sw"><?php echo esc_html((string) ($item["when"] ?? "")); ?></div>
          <div>
            <?php if (!empty($item["optional"])) : ?>
              <span class="tag to">Opcjonalnie</span>
            <?php else : ?>
              <span class="tag ti">W cenie</span>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endforeach; ?>
    <?php
    $extra_scope = (string) get_post_meta($offer_id, "_ups_offer_scope_extra_html", true);
    if ($extra_scope !== "") {
        echo wp_kses_post($extra_scope);
    }
    ?>
    <?php endif; ?>
  </div>
</div>
</div>

<div class="hr"></div>

<?php if ($has_offer_details) : ?>
<div class="w" id="sec-szczegoly" data-offer-section="szczegoly">
<div class="sec r">
  <div class="lbl">Szczegóły oferty</div>
  <h2 class="h2">Dodatkowe informacje</h2>
  <div class="oc"><?php echo wp_kses_post($content_html); ?></div>
</div>
</div>

<div class="hr"></div>
<?php endif; ?>

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
  <p class="sub">Możesz odpowiedzieć tutaj, mailem albo na rozmowie. Jeśli teraz nie wiesz — zostaw puste.</p>
  <div class="questions-sec">
    <form id="qaForm">
      <input type="hidden" name="action" value="upsellio_offer_qa_submit" />
      <input type="hidden" name="nonce" value="<?php echo esc_attr($public_nonce); ?>" />
      <input type="hidden" name="offer_id" value="<?php echo (int) $offer_id; ?>" />
      <?php foreach ($questions as $qi => $q) : ?>
      <div class="q-item">
        <div class="q-num"><?php echo esc_html((string) ($qi + 1)); ?></div>
        <div style="flex:1">
          <div class="q-text"><?php echo esc_html((string) ($q["text"] ?? "")); ?></div>
          <?php if (!empty($q["note"])) : ?><div class="q-note"><?php echo esc_html((string) $q["note"]); ?></div><?php endif; ?>
          <textarea name="answer_<?php echo (int) $qi; ?>" rows="2" placeholder="Odpowiedź (opcjonalna)..."></textarea>
        </div>
      </div>
      <?php endforeach; ?>
      <div style="margin-top:20px;display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
        <input type="email" name="qa_email" placeholder="Twój email (do wysyłki)" required style="flex:1;padding:10px;border:1px solid #e2e8f0;border-radius:6px;" />
        <button type="button" class="btn btn-p" onclick="submitQA()">Wyślij odpowiedzi →</button>
      </div>
      <div id="qaStatus" style="margin-top:8px;font-size:13px;"></div>
    </form>
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
    <?php
    $has_include = trim((string) ($payload["include_lines"] ?? "")) !== "";
    $has_option = trim((string) ($payload["option_lines"] ?? "")) !== "";
    ?>
    <div class="pbody<?php echo (!$has_include && !$has_option) ? " pbody-empty" : ""; ?>">
      <?php if (!$has_include && !$has_option) : ?>
      <div class="pbody-empty-msg">
        <strong>Pełen zakres ustalimy podczas wstępnej rozmowy.</strong>
        <p>Powyższa cena to widełki wstępne — ostateczna oferta zależy od audytu sytuacji.</p>
      </div>
      <?php else : ?>
      <div>
        <div class="incl-title">Zawarte w abonamencie</div>
        <div class="incl"><?php upsellio_offer_render_lines_as_checklist((string) ($payload["include_lines"] ?? ""), false); ?></div>
      </div>
      <div>
        <div class="incl-title">Opcjonalne rozszerzenia</div>
        <div class="incl"><?php upsellio_offer_render_lines_as_checklist((string) ($payload["option_lines"] ?? ""), true); ?></div>
      </div>
      <?php endif; ?>
    </div>
    <div class="pfoot pfoot--stack" id="pricing-accept-anchor">
      <div class="pfoot-primary">
        <button type="button" class="btn btn-p" id="btn-offer-accept-pricing"><?php echo esc_html($cta_text); ?></button>
      </div>
      <div class="pfoot-secondary">
        <button type="button" class="btn btn-g" id="btn-offer-question-pricing">Mam pytanie</button>
      </div>
      <div class="pfoot-meta">
        <svg width="13" height="13" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M10 2L3 5v5c0 4.4 3.1 8.1 7 9 3.9-.9 7-4.6 7-9V5l-7-3Z"/></svg>
        Ważna do <strong style="color:var(--ink)"><?php echo esc_html($offer_expires_label); ?></strong>
        · Po kliknięciu „<?php echo esc_html($cta_text); ?>” wyślemy podsumowanie do opiekuna i kopię na Twój e-mail.
      </div>
      <noscript>
        <form method="post" class="pfoot-secondary" style="margin-top:8px" action="<?php echo esc_url(function_exists("upsellio_offer_get_public_url") ? upsellio_offer_get_public_url($offer_id) : home_url("/")); ?>">
          <?php wp_nonce_field("ups_offer_accept_" . $offer_id, "ups_offer_accept_nonce"); ?>
          <button class="btn btn-p" type="submit" style="border:none"><?php echo esc_html($cta_text); ?> (bez JS)</button>
        </form>
      </noscript>
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
    <div class="cta-acts cta-acts--stack">
      <button type="button" class="btn btn-cta" id="btn-offer-accept-footer"><?php echo esc_html($cta_text); ?></button>
      <?php if ($owner_phone !== "") : ?>
      <a class="btn btn-ol" href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $owner_phone)); ?>" onclick="cta('final_phone')">Zadzwoń</a>
      <?php endif; ?>
      <button type="button" class="btn btn-ol" id="btn-offer-question-footer">Mam pytanie</button>
    </div>
    <div class="cta-micro">
      <span>Bez zobowiązań przed rozmową</span>
      <span>Odpowiedź w 24h robocze</span>
    </div>
  </div>
</section>

<div class="ups-modal" id="ups-modal-contact" aria-hidden="true">
  <div class="ups-modal__backdrop" data-ups-close></div>
  <div class="ups-modal__box" role="dialog" aria-modal="true" aria-labelledby="ups-modal-contact-title" style="max-width:620px">
    <h3 id="ups-modal-contact-title">Akceptacja oferty</h3>
    <div class="ups-modal-progress">
      <div class="step active" data-step="1">1. Pakiet</div>
      <div class="step" data-step="2">2. Start i budżet</div>
      <div class="step" data-step="3">3. Decyzja</div>
      <div class="step" data-step="4">4. Kontakt</div>
    </div>
    <div class="ups-modal__err" id="ups-modal-contact-err"></div>
    <div class="ups-modal-step active" data-step="1">
      <h4>Potwierdź wybór</h4>
      <p>Wybrany pakiet: <strong id="confirmCommitLabel">—</strong></p>
      <div class="ups-modal__actions">
        <button type="button" class="btn btn-g" data-ups-close>Anuluj</button>
        <button type="button" class="btn btn-p" onclick="goStep(2)">Dalej →</button>
      </div>
    </div>
    <div class="ups-modal-step" data-step="2">
      <h4>Kiedy chcesz startować?</h4>
      <div class="ups-radio-group">
        <label><input type="radio" name="accept_timeline" value="asap" required /> ASAP — w tym tygodniu</label>
        <label><input type="radio" name="accept_timeline" value="2_weeks" /> W 2 tygodnie</label>
        <label><input type="radio" name="accept_timeline" value="month" /> W ciągu miesiąca</label>
        <label><input type="radio" name="accept_timeline" value="flexible" /> Elastycznie</label>
      </div>
      <h4 style="margin-top:14px">Budżet reklamowy</h4>
      <div class="ups-radio-group">
        <label><input type="radio" name="accept_ad_budget" value="under_3k" required /> Do 3 000 zł / mc</label>
        <label><input type="radio" name="accept_ad_budget" value="3_5k" /> 3-5 000 zł / mc</label>
        <label><input type="radio" name="accept_ad_budget" value="5_10k" /> 5-10 000 zł / mc</label>
        <label><input type="radio" name="accept_ad_budget" value="10k_plus" /> Powyżej 10 000 zł / mc</label>
      </div>
      <div class="ups-modal__actions">
        <button type="button" class="btn btn-g" onclick="goStep(1)">← Wróć</button>
        <button type="button" class="btn btn-p" onclick="goStep(3)">Dalej →</button>
      </div>
    </div>
    <div class="ups-modal-step" data-step="3">
      <h4>Kto podejmuje decyzję?</h4>
      <div class="ups-radio-group">
        <label><input type="radio" name="accept_decision" value="me" required /> Tylko ja</label>
        <label><input type="radio" name="accept_decision" value="me_team" /> Ja + zespół</label>
        <label><input type="radio" name="accept_decision" value="board" /> Zarząd / wspólnicy</label>
      </div>
      <label class="ups-modal__field"><span>Obawy / pytania</span><textarea id="accept_concerns" placeholder="Np. cena, timing, poprzednie doświadczenia"></textarea></label>
      <div class="ups-modal__actions">
        <button type="button" class="btn btn-g" onclick="goStep(2)">← Wróć</button>
        <button type="button" class="btn btn-p" onclick="goStep(4)">Dalej →</button>
      </div>
    </div>
    <div class="ups-modal-step" data-step="4">
      <h4>Kontakt</h4>
      <label class="ups-modal__field"><span>Email</span><input type="email" id="accept_email" autocomplete="email" placeholder="jan@firma.pl" required /></label>
      <label class="ups-modal__field"><span>Telefon (opcjonalnie)</span><input type="tel" id="accept_phone" placeholder="+48..." /></label>
      <label class="ups-modal__field"><span>Kanał kontaktu</span>
        <select id="accept_contact_pref">
          <option value="phone">Telefon</option>
          <option value="email">Email</option>
          <option value="meeting">Spotkanie online</option>
        </select>
      </label>
      <label class="ups-modal__field"><span>Najlepszy czas na telefon</span>
        <select id="accept_call_window">
          <option value="">Bez preferencji</option>
          <option value="9_12">9-12</option>
          <option value="12_15">12-15</option>
          <option value="15_18">15-18</option>
        </select>
      </label>
      <input type="hidden" id="acceptCtaSource" value="" />
      <input type="hidden" id="acceptCommitKey" value="" />
      <input type="hidden" id="acceptCommitLabel" value="" />
      <div class="ups-modal__actions">
        <button type="button" class="btn btn-g" onclick="goStep(3)">← Wróć</button>
        <button type="button" class="btn btn-p" id="ups-modal-contact-submit">Wyślij i umów rozmowę →</button>
      </div>
    </div>
  </div>
</div>

<div class="ups-modal" id="ups-modal-question" aria-hidden="true">
  <div class="ups-modal__backdrop" data-ups-close></div>
  <div class="ups-modal__box" role="dialog" aria-modal="true" aria-labelledby="ups-modal-q-title">
    <h3 id="ups-modal-q-title">Twoje pytanie</h3>
    <p>Opisz krótko, o co chodzi — dostaniesz <strong>kopię wiadomości</strong> na podany e-mail, a opiekun odpowie bezpośrednio.</p>
    <div class="ups-modal__err" id="ups-modal-q-err"></div>
    <label class="ups-modal__field"><span>Pytanie</span><textarea id="ups-modal-q-text" placeholder="Np. czy możemy zacząć od jednego kanału?"></textarea></label>
    <label class="ups-modal__field" id="ups-modal-q-email-wrap"<?php echo $client_email_ok ? ' style="display:none"' : ""; ?>><span>Twój e-mail (kontakt)</span><input type="email" id="ups-modal-q-email" autocomplete="email" placeholder="jan@firma.pl"<?php echo $client_email_ok ? "" : " required"; ?>/></label>
    <div class="ups-modal__actions">
      <button type="button" class="btn btn-g" data-ups-close>Anuluj</button>
      <button type="button" class="btn btn-p" id="ups-modal-q-submit">Wyślij</button>
    </div>
  </div>
</div>

<div class="ups-toast" id="ups-offer-toast" role="status" aria-live="polite"></div>

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
var __upsPreviewMode=<?php echo $is_preview_mode ? "true" : "false"; ?>;
var hasClientEmail=<?php echo $client_email_ok ? "true" : "false"; ?>;
var publicNonce=<?php echo wp_json_encode((string) $public_nonce); ?>;
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
function stickyOffset(){
  var nav=document.querySelector('.nav');
  var snav=document.querySelector('.snav');
  var pb=document.querySelector('.offer-process-bar');
  return (nav?nav.offsetHeight:0)+(snav?snav.offsetHeight:0)+(pb?pb.offsetHeight:0)+20;
}
function updateSnav(){
  var top=window.scrollY+stickyOffset();
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
  var off=stickyOffset();
  var target=el.getBoundingClientRect().top+window.scrollY-off;
  window.scrollTo({top:target,behavior:'smooth'});
  if(window.history&&history.replaceState)history.replaceState(null,'','#'+id);
  trackEvent('offer_section_click',{section_id:id.replace('sec-','')});
}
window.jumpTo=jumpTo;
var ro=new IntersectionObserver(function(e){e.forEach(function(x){if(x.isIntersecting)x.target.classList.add('in');});},{threshold:.1});
document.querySelectorAll('.r').forEach(function(el){ro.observe(el);});
function trackEvent(eventName,extra){
  if(!__upsTrackPublic||__upsPreviewMode)return;
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
function pushDl(ev,ex){if(!__upsTrackPublic||__upsPreviewMode)return;window.dataLayer=window.dataLayer||[];dataLayer.push(Object.assign({event:ev,offer_id:String(offerId),person_id:String(personId||'')},ex||{}));}
if(!__upsPreviewMode){trackEvent('offer_view',{});}
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
  if(!el)return;
  var inp=el.querySelector('input[type="radio"]');if(!inp)return;
  inp.checked=true;
  document.querySelectorAll('.commit-opt').forEach(function(o){o.classList.remove('sel');});
  el.classList.add('sel');
  var val=inp.value||'';
  trackEvent('offer_commit_selected',{section_id:String(val)});
  var ctaEl=document.getElementById('scCta');
  var labEl=el.querySelector('.commit-label');
  var t=labEl?labEl.textContent.trim():el.textContent.replace(/\s+/g,' ').trim();
  if(ctaEl&&val&&t)ctaEl.textContent='Chcę: '+t+' →';
};
window.cta=function(l){pushDl('offer_cta_click',{cta_label:l});trackEvent('offer_cta_click',{section_id:String(l)});};
window.faqToggle=function(i){var o=i.classList.contains('open');document.querySelectorAll('.fi.open').forEach(function(el){el.classList.remove('open');});if(!o)i.classList.add('open');};

function getCommit(){
  var sel=document.querySelector('input[name="commit"]:checked');
  if(!sel)return{key:'',label:''};
  var lab=sel.closest('.commit-opt');
  var labelEl=lab?lab.querySelector('.commit-label'):null;
  var text=labelEl?labelEl.textContent.replace(/\s+/g,' ').trim():(lab?lab.textContent.replace(/\s+/g,' ').trim():'');
  return{key:sel.value||'',label:text};
}
function postMessaging(payload){
  var body=new URLSearchParams();
  body.append('action','upsellio_offer_public_messaging');
  body.append('nonce',publicNonce);
  body.append('offer_id',String(offerId));
  Object.keys(payload).forEach(function(k){
    if(payload[k]!==undefined&&payload[k]!==null)body.append(k,String(payload[k]));
  });
  return fetch(ajaxUrl,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body.toString(),credentials:'same-origin'}).then(function(r){return r.json();});
}
function showToast(msg,isErr){
  var t=document.getElementById('ups-offer-toast');
  if(!t)return;
  t.textContent=msg;
  t.style.background=isErr?'#7f1d1d':'';
  t.classList.add('is-visible');
  clearTimeout(t._tto);
  t._tto=setTimeout(function(){t.classList.remove('is-visible');},7000);
}
function openModal(id){
  document.querySelectorAll('.ups-modal').forEach(function(m){m.classList.remove('is-open');m.setAttribute('aria-hidden','true');});
  var m=document.getElementById(id);
  if(m){m.classList.add('is-open');m.setAttribute('aria-hidden','false');document.body.style.overflow='hidden';}
}
function closeModals(){
  document.querySelectorAll('.ups-modal.is-open').forEach(function(m){m.classList.remove('is-open');m.setAttribute('aria-hidden','true');});
  document.body.style.overflow='';
}
function onAcceptClick(source){
  cta(source==='pricing'?'pricing_accept_ajax':'footer_accept_ajax');
  var hasVariants=document.querySelectorAll('input[name="commit"]').length>0;
  var commit=getCommit();
  if(hasVariants&&!commit.key){
    var opts=document.getElementById('commitOpts');
    if(opts){
      opts.scrollIntoView({behavior:'smooth',block:'center'});
      opts.classList.add('commit-opts--required');
      setTimeout(function(){opts.classList.remove('commit-opts--required');},2000);
    }
    showToast('Wybierz najpierw pakiet, którym jesteś zainteresowany.',true);
    return;
  }
  openAcceptModal(source,commit);
}
function goStep(step){
  var err=document.getElementById('ups-modal-contact-err');
  if(err){err.classList.remove('is-visible');err.textContent='';}
  if(step===3){
    var timeline=document.querySelector('input[name="accept_timeline"]:checked');
    var budget=document.querySelector('input[name="accept_ad_budget"]:checked');
    if(!timeline||!budget){if(err){err.textContent='Wybierz termin startu i budżet reklamowy.';err.classList.add('is-visible');}return;}
  }
  if(step===4){
    var decision=document.querySelector('input[name="accept_decision"]:checked');
    if(!decision){if(err){err.textContent='Wybierz sposób podejmowania decyzji.';err.classList.add('is-visible');}return;}
  }
  document.querySelectorAll('#ups-modal-contact .ups-modal-step').forEach(function(s){s.classList.toggle('active',String(s.dataset.step)===String(step));});
  document.querySelectorAll('#ups-modal-contact .ups-modal-progress .step').forEach(function(s){s.classList.toggle('active',Number(s.dataset.step)<=Number(step));});
}
window.goStep=goStep;
function openAcceptModal(source,commit){
  var errEl=document.getElementById('ups-modal-contact-err');
  if(errEl){errEl.classList.remove('is-visible');errEl.textContent='';}
  document.querySelectorAll('#ups-modal-contact input[type="radio"]').forEach(function(el){el.checked=false;});
  var concerns=document.getElementById('accept_concerns');
  if(concerns)concerns.value='';
  var email=document.getElementById('accept_email');
  if(email)email.value=hasClientEmail?<?php echo wp_json_encode((string) $client_email); ?>:'';
  var phone=document.getElementById('accept_phone');
  if(phone)phone.value='';
  var pref=document.getElementById('accept_contact_pref');
  if(pref)pref.value='phone';
  var windowField=document.getElementById('accept_call_window');
  if(windowField)windowField.value='';
  var src=document.getElementById('acceptCtaSource');
  if(src)src.value=source;
  var ck=document.getElementById('acceptCommitKey');
  if(ck)ck.value=commit.key||'';
  var cl=document.getElementById('acceptCommitLabel');
  if(cl)cl.value=commit.label||'';
  var cLabel=document.getElementById('confirmCommitLabel');
  if(cLabel)cLabel.textContent=commit.label||'Brak';
  goStep(1);
  openModal('ups-modal-contact');
}
var questionSource='pricing';
function onQuestionClick(source){
  cta(source==='pricing'?'pricing_question_modal':'footer_question_modal');
  questionSource=source;
  var qt=document.getElementById('ups-modal-q-text');
  if(qt)qt.value='';
  var wrap=document.getElementById('ups-modal-q-email-wrap');
  var qe=document.getElementById('ups-modal-q-email');
  if(!hasClientEmail){
    if(wrap)wrap.style.display='';
    if(qe){qe.value='';qe.required=true;}
  }else{
    if(wrap)wrap.style.display='none';
    if(qe)qe.required=false;
  }
  var qerr=document.getElementById('ups-modal-q-err');
  if(qerr){qerr.classList.remove('is-visible');qerr.textContent='';}
  openModal('ups-modal-question');
}
var elAccP=document.getElementById('btn-offer-accept-pricing');
var elAccF=document.getElementById('btn-offer-accept-footer');
if(elAccP)elAccP.addEventListener('click',function(){onAcceptClick('pricing');});
if(elAccF)elAccF.addEventListener('click',function(){onAcceptClick('footer');});
var elQp=document.getElementById('btn-offer-question-pricing');
var elQf=document.getElementById('btn-offer-question-footer');
if(elQp)elQp.addEventListener('click',function(){onQuestionClick('pricing');});
if(elQf)elQf.addEventListener('click',function(){onQuestionClick('footer');});
var elCs=document.getElementById('ups-modal-contact-submit');
if(elCs)elCs.addEventListener('click',function(){
  var v=(document.getElementById('accept_email')||{}).value||'';
  v=v.trim();
  var err=document.getElementById('ups-modal-contact-err');
  if(!v||!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)){
    if(err){err.textContent='Podaj poprawny adres e-mail.';err.classList.add('is-visible');}
    return;
  }
  var timeline=(document.querySelector('input[name="accept_timeline"]:checked')||{}).value||'';
  var budget=(document.querySelector('input[name="accept_ad_budget"]:checked')||{}).value||'';
  var decision=(document.querySelector('input[name="accept_decision"]:checked')||{}).value||'';
  if(!timeline||!budget||!decision){
    if(err){err.textContent='Uzupełnij wszystkie kroki przed wysyłką.';err.classList.add('is-visible');}
    return;
  }
  if(err)err.classList.remove('is-visible');
  var body=new URLSearchParams();
  body.append('action','upsellio_offer_accept_full');
  body.append('offer_id',String(offerId));
  body.append('client_id',String(clientId));
  body.append('person_id',String(personId||''));
  body.append('nonce',publicNonce);
  body.append('contact_email',v);
  body.append('contact_phone',((document.getElementById('accept_phone')||{}).value||'').trim());
  body.append('contact_pref',((document.getElementById('accept_contact_pref')||{}).value||'').trim());
  body.append('call_window',((document.getElementById('accept_call_window')||{}).value||'').trim());
  body.append('timeline',timeline);
  body.append('ad_budget',budget);
  body.append('decision',decision);
  body.append('concerns',((document.getElementById('accept_concerns')||{}).value||'').trim());
  body.append('commit_key',((document.getElementById('acceptCommitKey')||{}).value||'').trim());
  body.append('commit_label',((document.getElementById('acceptCommitLabel')||{}).value||'').trim());
  body.append('cta_source',((document.getElementById('acceptCtaSource')||{}).value||'pricing'));
  fetch(ajaxUrl,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},credentials:'same-origin',body:body.toString()})
    .then(function(r){return r.json();})
    .then(function(res){
      if(res&&res.success){
        hasClientEmail=true;
        closeModals();
        showToast((res.data&&res.data.message)||'Dzięki! Potwierdzenie wysłane, wracamy z kontaktem.');
        pushDl('offer_public_accept',{cta_label:((document.getElementById('acceptCtaSource')||{}).value||'pricing')});
      }else{
        showToast((res&&res.data&&res.data.message)||'Nie udało się wysłać. Spróbuj ponownie.',true);
      }
    })
    .catch(function(){showToast('Błąd sieci. Spróbuj ponownie.',true);});
});
var elQs=document.getElementById('ups-modal-q-submit');
if(elQs)elQs.addEventListener('click',function(){
  var qt=document.getElementById('ups-modal-q-text');
  var q=(qt?(qt.value||'').trim():'');
  var err=document.getElementById('ups-modal-q-err');
  if(q.length<3){if(err){err.textContent='Wpisz treść pytania (min. kilka znaków).';err.classList.add('is-visible');}return;}
  var email='';
  if(!hasClientEmail){
    var qe=document.getElementById('ups-modal-q-email');
    email=qe?(qe.value||'').trim():'';
    if(!email||!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){
      if(err){err.textContent='Podaj poprawny e-mail kontaktowy.';err.classList.add('is-visible');}
      return;
    }
  }
  if(err)err.classList.remove('is-visible');
  var payload={msg_type:'question',question_body:q,cta_source:questionSource};
  if(email)payload.contact_email=email;
  postMessaging(payload).then(function(res){
    if(res&&res.success){
      if(res.data&&res.data.saved_contact)hasClientEmail=true;
      closeModals();
      showToast((res.data&&res.data.message)||'Wysłano. Dostałeś kopię na maila.');
    }else{
      showToast((res&&res.data&&res.data.message)||'Nie udało się wysłać.',true);
    }
  }).catch(function(){showToast('Błąd sieci.',true);});
});
window.submitQA=function(){
  var form=document.getElementById('qaForm');
  var status=document.getElementById('qaStatus');
  if(!form||!status){return;}
  var fd=new FormData(form);
  var answers=[];
  fd.forEach(function(v,k){
    if(String(k).indexOf('answer_')===0&&String(v).trim().length>3){answers.push(String(v));}
  });
  if(answers.length===0){
    status.textContent='⚠️ Odpowiedz na minimum 1 pytanie albo użyj przycisku „Mam pytanie”.';
    status.style.color='#d94c4c';
    return;
  }
  status.textContent='Wysyłam...';
  status.style.color='var(--muted)';
  var body=new URLSearchParams();
  fd.forEach(function(v,k){body.append(k,String(v));});
  fetch(ajaxUrl,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body.toString(),credentials:'same-origin'}).then(function(r){return r.json();}).then(function(data){
    if(data&&data.success){
      form.innerHTML='<div style="text-align:center;padding:24px;background:#f0fdfa;border-radius:12px;">✓ Dziękuję za odpowiedzi. Przygotuję się do rozmowy.</div>';
      pushDl('offer_public_qa_submitted',{answered_count:answers.length});
    }else{
      status.textContent='❌ '+((data&&data.data&&data.data.message)?data.data.message:'Błąd');
      status.style.color='#d94c4c';
    }
  }).catch(function(){
    status.textContent='❌ Błąd sieci';
    status.style.color='#d94c4c';
  });
};
document.querySelectorAll('[data-ups-close]').forEach(function(el){
  el.addEventListener('click',closeModals);
});
document.addEventListener('keydown',function(e){if(e.key==='Escape')closeModals();});
})();
</script>
</body>
</html>
    <?php
}

function upsellio_offer_public_resolve_owner_mail($offer_id)
{
    $offer_id = (int) $offer_id;
    $owner_id = (int) get_post_meta($offer_id, "_ups_offer_owner_id", true);
    if ($owner_id <= 0 && function_exists("upsellio_crm_get_default_owner_id")) {
        $owner_id = (int) upsellio_crm_get_default_owner_id();
    }
    if ($owner_id <= 0) {
        $owner_id = (int) get_post_field("post_author", $offer_id);
    }
    $owner = get_userdata($owner_id);
    $owner_name = $owner instanceof WP_User ? (string) $owner->display_name : "Upsellio";
    $owner_email = $owner instanceof WP_User && is_email((string) $owner->user_email)
        ? (string) $owner->user_email
        : (string) get_option("admin_email");
    return [$owner_name, $owner_email];
}

function upsellio_offer_public_format_mail_html($intro, $rows, $owner_name)
{
    $out = '<p style="font-family:system-ui,Segoe UI,sans-serif;font-size:15px;line-height:1.5;color:#111">' . esc_html($intro) . '</p>';
    $out .= '<table cellpadding="8" style="border-collapse:collapse;font-family:system-ui,Segoe UI,sans-serif;font-size:14px;max-width:640px">';
    foreach ($rows as $k => $v) {
        $out .= "<tr><td style=\"border:1px solid #e5e5e5;vertical-align:top;font-weight:600\">" . esc_html((string) $k) . "</td><td style=\"border:1px solid #e5e5e5\">" . nl2br(esc_html((string) $v)) . "</td></tr>";
    }
    $out .= "</table>";
    $out .= '<p style="font-size:13px;color:#666">— ' . esc_html($owner_name) . " / Upsellio</p>";
    return $out;
}

function upsellio_offer_public_messaging_ajax()
{
    $offer_id = isset($_POST["offer_id"]) ? (int) $_POST["offer_id"] : 0;
    if ($offer_id <= 0 || get_post_type($offer_id) !== "crm_offer") {
        wp_send_json_error(["message" => "Nieprawidłowa oferta."]);
    }
    if (!isset($_POST["nonce"]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST["nonce"])), "ups_offer_public_" . $offer_id)) {
        wp_send_json_error(["message" => "Sesja wygasła. Odśwież stronę i spróbuj ponownie."]);
    }
    if (function_exists("upsellio_offer_is_expired") && upsellio_offer_is_expired($offer_id)) {
        wp_send_json_error(["message" => "Ta oferta wygasła. Skontaktuj się z opiekunem."]);
    }

    $ip = isset($_SERVER["REMOTE_ADDR"]) ? sanitize_text_field(wp_unslash($_SERVER["REMOTE_ADDR"])) : "";
    $rl_key = "ups_offer_msg_" . md5($ip . "_" . $offer_id);
    if (get_transient($rl_key)) {
        wp_send_json_error(["message" => "Za szybko — odczekaj chwilę i spróbuj ponownie."]);
    }
    set_transient($rl_key, 1, 30);

    $msg_type = isset($_POST["msg_type"]) ? sanitize_key(wp_unslash($_POST["msg_type"])) : "";
    if (!in_array($msg_type, ["accept", "question"], true)) {
        wp_send_json_error(["message" => "Nieobsługiwane żądanie."]);
    }

    $client_id = (int) get_post_meta($offer_id, "_ups_offer_client_id", true);
    $stored_email = $client_id > 0 ? (string) get_post_meta($client_id, "_ups_client_email", true) : "";
    $contact_in = isset($_POST["contact_email"]) ? sanitize_email(wp_unslash($_POST["contact_email"])) : "";

    $client_email = is_email($stored_email) ? $stored_email : "";
    $saved_contact = false;
    if ($client_email === "" && is_email($contact_in)) {
        $client_email = $contact_in;
        if ($client_id > 0) {
            update_post_meta($client_id, "_ups_client_email", $client_email);
            $saved_contact = true;
        }
    }

    if ($client_email === "") {
        wp_send_json_error(["message" => "Podaj poprawny adres e-mail kontaktowy (do kopii wiadomości)."]);
    }

    [$owner_name, $owner_email] = upsellio_offer_public_resolve_owner_mail($offer_id);
    $offer_title = get_the_title($offer_id);
    $client_name = $client_id > 0 ? get_the_title($client_id) : __("Klient", "upsellio");
    $cta_source = isset($_POST["cta_source"]) ? sanitize_text_field(wp_unslash($_POST["cta_source"])) : "";

    $headers = [
        "Content-Type: text/html; charset=UTF-8",
        "Cc: " . $client_email,
    ];

    if ($msg_type === "accept") {
        if (!function_exists("upsellio_offer_apply_public_accept")) {
            wp_send_json_error(["message" => "Błąd konfiguracji serwera."]);
        }
        if (!upsellio_offer_apply_public_accept($offer_id)) {
            wp_send_json_error(["message" => "Nie można zapisać akceptacji. Oferta mogła wygasnąć."]);
        }
        $commit_key = isset($_POST["commit_key"]) ? sanitize_key(wp_unslash($_POST["commit_key"])) : "";
        $commit_label = isset($_POST["commit_label"]) ? sanitize_text_field(wp_unslash($_POST["commit_label"])) : "";
        $commit_hint = $commit_label !== "" ? $commit_label : ($commit_key !== "" ? $commit_key : __("cały zakres / do uzgodnienia", "upsellio"));
        $rows = [
            __("Oferta", "upsellio") => $offer_title,
            __("Klient", "upsellio") => $client_name,
            __("E-mail (kopia CC)", "upsellio") => $client_email,
            __("Wybrany wariant", "upsellio") => $commit_hint,
            __("Przycisk", "upsellio") => $cta_source !== "" ? $cta_source : "—",
        ];
        $subject = "[Upsellio] " . __("Akceptacja oferty", "upsellio") . ": " . $offer_title;
        $body = upsellio_offer_public_format_mail_html(
            __("Klient zaakceptował ofertę na stronie publicznej.", "upsellio"),
            $rows,
            $owner_name
        );
        wp_mail($owner_email, $subject, $body, $headers);
        wp_send_json_success([
            "message" => sprintf(
                /* translators: %s: client email */
                __("Dziękujemy — akceptacja jest zapisana. Na adres %s wysłaliśmy kopię podsumowania (również opiekun dostał wiadomość).", "upsellio"),
                $client_email
            ),
            "saved_contact" => $saved_contact,
        ]);
    }

    $question_body = isset($_POST["question_body"]) ? sanitize_textarea_field(wp_unslash($_POST["question_body"])) : "";
    if (strlen($question_body) < 3) {
        wp_send_json_error(["message" => "Wpisz treść pytania."]);
    }
    if (strlen($question_body) > 5000) {
        wp_send_json_error(["message" => "Wiadomość jest za długa."]);
    }
    $rows = [
        __("Oferta", "upsellio") => $offer_title,
        __("Klient", "upsellio") => $client_name,
        __("E-mail", "upsellio") => $client_email,
        __("Pytanie", "upsellio") => $question_body,
        __("Źródło", "upsellio") => $cta_source !== "" ? $cta_source : "—",
    ];
    $subject = "[Upsellio] " . __("Pytanie do oferty", "upsellio") . ": " . $offer_title;
    $body = upsellio_offer_public_format_mail_html(
        __("Nowe pytanie z publicznej strony oferty.", "upsellio"),
        $rows,
        $owner_name
    );
    wp_mail($owner_email, $subject, $body, $headers);
    wp_send_json_success([
        "message" => sprintf(
            /* translators: %s: client email */
            __("Wysłane. Kopia trafiła na %s — opiekun też dostał wiadomość.", "upsellio"),
            $client_email
        ),
        "saved_contact" => $saved_contact,
    ]);
}
add_action("wp_ajax_upsellio_offer_public_messaging", "upsellio_offer_public_messaging_ajax");
add_action("wp_ajax_nopriv_upsellio_offer_public_messaging", "upsellio_offer_public_messaging_ajax");

function upsellio_offer_accept_full_ajax()
{
    $offer_id = isset($_POST["offer_id"]) ? (int) $_POST["offer_id"] : 0;
    if ($offer_id <= 0 || get_post_type($offer_id) !== "crm_offer") {
        wp_send_json_error(["message" => "Nieprawidłowa oferta."]);
    }
    if (!isset($_POST["nonce"]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST["nonce"])), "ups_offer_public_" . $offer_id)) {
        wp_send_json_error(["message" => "Sesja wygasła. Odśwież stronę i spróbuj ponownie."]);
    }
    if (function_exists("upsellio_offer_is_expired") && upsellio_offer_is_expired($offer_id)) {
        wp_send_json_error(["message" => "Ta oferta wygasła. Skontaktuj się z opiekunem."]);
    }

    $contact_email = isset($_POST["contact_email"]) ? sanitize_email(wp_unslash($_POST["contact_email"])) : "";
    if (!is_email($contact_email)) {
        wp_send_json_error(["message" => "Podaj poprawny adres e-mail."]);
    }
    $timeline = isset($_POST["timeline"]) ? sanitize_key(wp_unslash($_POST["timeline"])) : "";
    $ad_budget = isset($_POST["ad_budget"]) ? sanitize_key(wp_unslash($_POST["ad_budget"])) : "";
    $decision = isset($_POST["decision"]) ? sanitize_key(wp_unslash($_POST["decision"])) : "";
    if ($timeline === "" || $ad_budget === "" || $decision === "") {
        wp_send_json_error(["message" => "Uzupełnij wszystkie kroki akceptacji."]);
    }

    if (!function_exists("upsellio_offer_apply_public_accept") || !upsellio_offer_apply_public_accept($offer_id)) {
        wp_send_json_error(["message" => "Nie można zapisać akceptacji."]);
    }

    $client_id = (int) get_post_meta($offer_id, "_ups_offer_client_id", true);
    if ($client_id > 0) {
        update_post_meta($client_id, "_ups_client_email", $contact_email);
        update_post_meta($client_id, "_ups_client_phone", isset($_POST["contact_phone"]) ? sanitize_text_field(wp_unslash($_POST["contact_phone"])) : "");
    }

    $decision_payload = [
        "timeline" => $timeline,
        "ad_budget" => $ad_budget,
        "decision" => $decision,
        "concerns" => isset($_POST["concerns"]) ? sanitize_textarea_field(wp_unslash($_POST["concerns"])) : "",
        "contact_pref" => isset($_POST["contact_pref"]) ? sanitize_key(wp_unslash($_POST["contact_pref"])) : "phone",
        "call_window" => isset($_POST["call_window"]) ? sanitize_key(wp_unslash($_POST["call_window"])) : "",
        "commit_key" => isset($_POST["commit_key"]) ? sanitize_key(wp_unslash($_POST["commit_key"])) : "",
        "commit_label" => isset($_POST["commit_label"]) ? sanitize_text_field(wp_unslash($_POST["commit_label"])) : "",
        "cta_source" => isset($_POST["cta_source"]) ? sanitize_key(wp_unslash($_POST["cta_source"])) : "pricing",
    ];
    update_post_meta($offer_id, "_ups_offer_accept_payload", $decision_payload);

    [$owner_name, $owner_email] = upsellio_offer_public_resolve_owner_mail($offer_id);
    $rows = [
        __("Oferta", "upsellio") => get_the_title($offer_id),
        __("Kontakt", "upsellio") => $contact_email,
        __("Wariant", "upsellio") => (string) $decision_payload["commit_label"],
        __("Start", "upsellio") => (string) $decision_payload["timeline"],
        __("Budżet reklamowy", "upsellio") => (string) $decision_payload["ad_budget"],
        __("Decyzja", "upsellio") => (string) $decision_payload["decision"],
        __("Preferowany kontakt", "upsellio") => (string) $decision_payload["contact_pref"],
        __("Okno kontaktu", "upsellio") => (string) $decision_payload["call_window"],
        __("Obawy", "upsellio") => (string) $decision_payload["concerns"],
    ];
    $subject = "[Upsellio] " . __("Akceptacja oferty (pełny brief)", "upsellio") . ": " . get_the_title($offer_id);
    $body = upsellio_offer_public_format_mail_html(
        __("Klient przeszedł pełny formularz akceptacji oferty.", "upsellio"),
        $rows,
        $owner_name
    );
    wp_mail($owner_email, $subject, $body, [
        "Content-Type: text/html; charset=UTF-8",
        "Cc: " . $contact_email,
    ]);

    wp_send_json_success([
        "message" => __("Dziękuję! Akceptacja zapisana. Wrócimy z kontaktem w ustalonym oknie czasowym.", "upsellio"),
    ]);
}
add_action("wp_ajax_upsellio_offer_accept_full", "upsellio_offer_accept_full_ajax");
add_action("wp_ajax_nopriv_upsellio_offer_accept_full", "upsellio_offer_accept_full_ajax");

function upsellio_offer_qa_submit_handler()
{
    $offer_id = isset($_POST["offer_id"]) ? (int) $_POST["offer_id"] : 0;
    if ($offer_id <= 0 || get_post_type($offer_id) !== "crm_offer") {
        wp_send_json_error(["message" => "Nieprawidłowa oferta."]);
    }
    if (!isset($_POST["nonce"]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST["nonce"])), "ups_offer_public_" . $offer_id)) {
        wp_send_json_error(["message" => "Sesja wygasła. Odśwież stronę."]);
    }
    $email = isset($_POST["qa_email"]) ? sanitize_email(wp_unslash($_POST["qa_email"])) : "";
    if (!is_email($email)) {
        wp_send_json_error(["message" => "Email niepoprawny."]);
    }
    $answers = [];
    foreach ($_POST as $k => $v) {
        if (strpos((string) $k, "answer_") === 0) {
            $ans = sanitize_textarea_field((string) wp_unslash($v));
            if (trim($ans) !== "") {
                $answers[] = $ans;
            }
        }
    }
    if (empty($answers)) {
        wp_send_json_error(["message" => "Brak odpowiedzi."]);
    }
    update_post_meta($offer_id, "_ups_offer_qa_responses", [
        "email" => $email,
        "answers" => $answers,
        "submitted_at" => current_time("mysql"),
    ]);
    if (function_exists("upsellio_inbox_append_message")) {
        upsellio_inbox_append_message($offer_id, [
            "direction" => "in",
            "from" => $email,
            "subject" => "Odpowiedzi na pytania z oferty",
            "body_plain" => "Klient odpowiedział na pytania:\n\n" . implode("\n\n---\n\n", $answers),
            "source" => "offer_qa_form",
        ]);
    }
    wp_send_json_success(["ok" => true]);
}
add_action("wp_ajax_upsellio_offer_qa_submit", "upsellio_offer_qa_submit_handler");
add_action("wp_ajax_nopriv_upsellio_offer_qa_submit", "upsellio_offer_qa_submit_handler");
