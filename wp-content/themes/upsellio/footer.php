<?php
if (!defined("ABSPATH")) {
    exit;
}

if (!function_exists('upsellio_render_reference_footer')) {
function upsellio_render_reference_footer($args = [])
{
    $args = is_array($args) ? $args : [];
    $contact_email = isset($args["contact_email"]) && is_email((string) $args["contact_email"])
        ? (string) $args["contact_email"]
        : "kontakt@upsellio.pl";
    $contact_phone = function_exists("upsellio_get_contact_phone") ? (string) upsellio_get_contact_phone() : "+48 575 522 595";
    $contact_phone_href = preg_replace("/\s+/", "", $contact_phone);
    $contact_email_href = upsellio_get_mailto_href($contact_email);
    $brand_logo_assets = function_exists("upsellio_get_brand_logo_assets") ? upsellio_get_brand_logo_assets() : [];
    $brand_logo_url = (string) ($brand_logo_assets["png"] ?? "");
    $brand_logo_webp_320_url = (string) ($brand_logo_assets["webp_320"] ?? "");
    $brand_logo_webp_640_url = (string) ($brand_logo_assets["webp_640"] ?? "");
    $footer_config = function_exists("upsellio_get_footer_content_config") ? upsellio_get_footer_content_config() : [];
    $brand_config = isset($footer_config["brand"]) && is_array($footer_config["brand"]) ? $footer_config["brand"] : [];
    $section_config = isset($footer_config["sections"]) && is_array($footer_config["sections"]) ? $footer_config["sections"] : [];
    $cities_config = isset($footer_config["cities"]) && is_array($footer_config["cities"]) ? $footer_config["cities"] : [];

    $normalize_url = static function ($value) {
        $raw_url = trim((string) $value);
        if ($raw_url === "") {
            return "";
        }
        if (strpos($raw_url, "http://") === 0 || strpos($raw_url, "https://") === 0) {
            return esc_url($raw_url);
        }

        $path = (string) wp_parse_url($raw_url, PHP_URL_PATH);
        if ($path === "") {
            $path = "/" . ltrim($raw_url, "/");
        }
        $path = "/" . ltrim($path, "/");

        $dynamic_map = [
            "/" => home_url("/"),
            "/blog" => function_exists("upsellio_get_blog_index_url") ? (string) upsellio_get_blog_index_url() : "",
            "/blog/" => function_exists("upsellio_get_blog_index_url") ? (string) upsellio_get_blog_index_url() : "",
            "/oferta" => function_exists("upsellio_get_offer_page_url") ? (string) upsellio_get_offer_page_url() : "",
            "/oferta/" => function_exists("upsellio_get_offer_page_url") ? (string) upsellio_get_offer_page_url() : "",
            "/kontakt" => function_exists("upsellio_get_contact_page_url") ? (string) upsellio_get_contact_page_url() : "",
            "/kontakt/" => function_exists("upsellio_get_contact_page_url") ? (string) upsellio_get_contact_page_url() : "",
            "/portfolio" => function_exists("upsellio_get_portfolio_page_url") ? (string) upsellio_get_portfolio_page_url() : "",
            "/portfolio/" => function_exists("upsellio_get_portfolio_page_url") ? (string) upsellio_get_portfolio_page_url() : "",
            "/portfolio-marketingowe" => function_exists("upsellio_get_marketing_portfolio_page_url") ? (string) upsellio_get_marketing_portfolio_page_url() : "",
            "/portfolio-marketingowe/" => function_exists("upsellio_get_marketing_portfolio_page_url") ? (string) upsellio_get_marketing_portfolio_page_url() : "",
            "/lead-magnety" => function_exists("upsellio_get_lead_magnets_page_url") ? (string) upsellio_get_lead_magnets_page_url() : "",
            "/lead-magnety/" => function_exists("upsellio_get_lead_magnets_page_url") ? (string) upsellio_get_lead_magnets_page_url() : "",
            "/marketing-google-ads" => function_exists("upsellio_get_google_ads_page_url") ? (string) upsellio_get_google_ads_page_url() : "",
            "/marketing-google-ads/" => function_exists("upsellio_get_google_ads_page_url") ? (string) upsellio_get_google_ads_page_url() : "",
            "/marketing-meta-ads" => function_exists("upsellio_get_meta_ads_page_url") ? (string) upsellio_get_meta_ads_page_url() : "",
            "/marketing-meta-ads/" => function_exists("upsellio_get_meta_ads_page_url") ? (string) upsellio_get_meta_ads_page_url() : "",
            "/tworzenie-stron-internetowych" => function_exists("upsellio_get_websites_page_url") ? (string) upsellio_get_websites_page_url() : "",
            "/tworzenie-stron-internetowych/" => function_exists("upsellio_get_websites_page_url") ? (string) upsellio_get_websites_page_url() : "",
            "/miasta" => function_exists("upsellio_get_cities_archive_url") ? (string) upsellio_get_cities_archive_url() : "",
            "/miasta/" => function_exists("upsellio_get_cities_archive_url") ? (string) upsellio_get_cities_archive_url() : "",
            "/definicja" => function_exists("upsellio_get_definitions_archive_url") ? (string) upsellio_get_definitions_archive_url() : "",
            "/definicja/" => function_exists("upsellio_get_definitions_archive_url") ? (string) upsellio_get_definitions_archive_url() : "",
            "/definicje" => function_exists("upsellio_get_definitions_archive_url") ? (string) upsellio_get_definitions_archive_url() : "",
            "/definicje/" => function_exists("upsellio_get_definitions_archive_url") ? (string) upsellio_get_definitions_archive_url() : "",
        ];
        if (isset($dynamic_map[$path]) && (string) $dynamic_map[$path] !== "") {
            return esc_url((string) $dynamic_map[$path]);
        }

        $page = get_page_by_path(trim($path, "/"));
        if ($page instanceof WP_Post) {
            $permalink = get_permalink((int) $page->ID);
            if (is_string($permalink) && $permalink !== "") {
                return esc_url($permalink);
            }
        }

        if (strpos($path, "/miasto/") === 0 || strpos($path, "/definicje/") === 0 || strpos($path, "/realizacja/") === 0 || strpos($path, "/portfolio-marketingowe/") === 0 || strpos($path, "/lead-magnety/") === 0) {
            return esc_url(home_url($path));
        }

        return "";
    };

    $brand_description = trim((string) ($brand_config["description"] ?? ""));
    if ($brand_description === "") {
        $brand_description = "Marketing i sprzedaż dla firm, które chcą realnych klientów - nie wykresów na slajdach. Pracuję z producentami, dystrybutorami i firmami usługowymi z całej Polski.";
    }
    $brand_description = (string) apply_filters("upsellio_footer_brand_description", $brand_description);
    $brand_address = trim((string) ($brand_config["address"] ?? "Wierzbowa 21A/2, Dopiewiec"));

    $normalized_sections = [];
    foreach ($section_config as $section_item) {
        if (!is_array($section_item)) {
            continue;
        }
        $section_title = trim((string) ($section_item["title"] ?? ""));
        $section_links = isset($section_item["links"]) && is_array($section_item["links"]) ? $section_item["links"] : [];
        if ($section_title === "" || empty($section_links)) {
            continue;
        }
        $normalized_links = [];
        foreach ($section_links as $section_link) {
            if (!is_array($section_link)) {
                continue;
            }
            $link_label = trim((string) ($section_link["label"] ?? ""));
            $link_url = $normalize_url((string) ($section_link["url"] ?? ""));
            if ($link_label === "" || $link_url === "") {
                continue;
            }
            $normalized_links[] = [
                "label" => $link_label,
                "url" => $link_url,
            ];
        }
        if (!empty($normalized_links)) {
            $normalized_sections[] = [
                "title" => $section_title,
                "links" => $normalized_links,
            ];
        }
    }

    $city_title = trim((string) ($cities_config["title"] ?? "Marketing w Twoim mieście"));
    $city_all_label = trim((string) ($cities_config["all_label"] ?? "Zobacz wszystkie miasta →"));
    $city_all_url = $normalize_url((string) ($cities_config["all_url"] ?? "/miasta/"));
    $city_limit = max(4, (int) ($cities_config["fallback_limit"] ?? 16));
    $city_links = isset($cities_config["links"]) && is_array($cities_config["links"]) ? $cities_config["links"] : [];

    $normalized_city_links = [];
    foreach ($city_links as $city_link) {
        if (!is_array($city_link)) {
            continue;
        }
        $city_label = trim((string) ($city_link["label"] ?? ""));
        $city_url = $normalize_url((string) ($city_link["url"] ?? ""));
        if ($city_label === "" || $city_url === "") {
            continue;
        }
        $normalized_city_links[] = ["label" => $city_label, "url" => $city_url];
    }
    if (empty($normalized_city_links)) {
        foreach (upsellio_get_footer_city_links($city_limit) as $city_item) {
            $city_label = trim((string) ($city_item["city_name"] ?? $city_item["label"] ?? ""));
            $city_url = $normalize_url((string) ($city_item["url"] ?? ""));
            if ($city_label === "" || $city_url === "") {
                continue;
            }
            $normalized_city_links[] = ["label" => $city_label, "url" => $city_url];
        }
    }
    $show_cities = (bool) apply_filters("upsellio_footer_show_cities", true);

    ob_start();
    ?>
    <footer class="nf-footer">
      <style>
        .nf-footer{font-family:"DM Sans",system-ui,sans-serif;background:#0a1410;color:rgba(255,255,255,.78);position:relative;overflow:hidden}
        .nf-footer *,.nf-footer *::before,.nf-footer *::after{box-sizing:border-box}
        .nf-footer::before{content:"";position:absolute;width:700px;height:700px;border-radius:50%;background:radial-gradient(circle,rgba(20,184,166,.16),transparent 65%);right:-300px;top:-300px;pointer-events:none}
        .nf-wrap{width:min(1240px,100% - 48px);margin-inline:auto}
        .nf-logo{display:flex;align-items:center;gap:8px;font-family:"Bricolage Grotesque",sans-serif;font-size:22px;font-weight:800;letter-spacing:-1px;color:#fff;text-decoration:none}
        .nf-logo img{height:30px;width:auto;display:block}
        .nf-logo-mark{width:26px;height:26px;border-radius:8px;background:linear-gradient(135deg,#0d9488,#0f766e);position:relative;flex:0 0 26px}
        .nf-logo-mark::after{content:"";position:absolute;inset:7px;border-radius:50%;background:#0a1410}
        .nf-footer-grid{position:relative;display:grid;grid-template-columns:1.3fr .9fr .9fr 1.2fr;gap:48px;padding:64px 0 48px}
        .nf-foot-brand p{margin:18px 0 24px;font-size:14px;line-height:1.65;color:rgba(255,255,255,.65);max-width:36ch}
        .nf-foot-contacts{display:grid;gap:8px}
        .nf-foot-contacts a,.nf-foot-contacts span{display:flex;align-items:center;gap:10px;color:rgba(255,255,255,.78);text-decoration:none;font-size:13.5px}
        .nf-foot-contacts a i,.nf-foot-contacts span i{width:30px;height:30px;border-radius:8px;background:rgba(255,255,255,.06);display:grid;place-items:center;font-style:normal;font-size:13px;color:#5eead4;flex:0 0 30px}
        .nf-foot-contacts a:hover{color:#5eead4}
        .nf-foot-h{font-size:11px;font-weight:700;letter-spacing:1.4px;text-transform:uppercase;color:#5eead4;margin:0 0 14px}
        .nf-foot-col ul{list-style:none;padding:0;margin:0 0 28px;display:grid;gap:8px}
        .nf-foot-col ul li a{color:rgba(255,255,255,.78);text-decoration:none;font-size:14px}
        .nf-foot-col ul li a:hover{color:#fff}
        .nf-cities-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:6px 18px;margin:0 0 18px;padding:0;list-style:none}
        .nf-cities-grid li a{font-size:13.5px;color:rgba(255,255,255,.65);text-decoration:none}
        .nf-cities-grid li a:hover{color:#5eead4}
        .nf-cities-all{color:#5eead4;font-weight:700;font-size:13.5px;text-decoration:none;display:inline-flex}
        .nf-foot-bar{position:relative;border-top:1px solid rgba(255,255,255,.08);padding:18px 0}
        .nf-foot-bar-row{display:flex;justify-content:space-between;align-items:center;gap:24px;flex-wrap:wrap;font-size:12.5px;color:rgba(255,255,255,.5)}
        .nf-foot-social{display:flex;gap:18px}
        .nf-foot-social a{color:rgba(255,255,255,.5);text-decoration:none}
        .nf-foot-social a:hover{color:#5eead4}
        @media (max-width:1100px){.nf-footer-grid{grid-template-columns:1fr 1fr;gap:30px}}
        @media (max-width:720px){.nf-wrap{width:min(1240px,100% - 24px)}.nf-footer-grid{grid-template-columns:1fr;padding:44px 0 36px}}
        .nf-footer-grid.is-no-cities{grid-template-columns:1.3fr 1fr 1fr}
      
        /* Reference: light footer, three link groups and a separate SEO disclosure. */
        .nf-footer{background:#f5f6f8;color:#0f1115;border-top:0}
        .nf-footer::before{display:none}
        .nf-wrap{width:min(1112px,calc(100% - 48px))}
        .nf-logo{color:#0f1115;font-size:24px;letter-spacing:-.04em}
        .nf-logo img{height:36px;width:auto}
        .nf-logo-mark::after{display:none}.nf-logo-mark{width:10px;height:10px;flex:0 0 10px;border-radius:50%;background:#0bb39c}
        .nf-footer-grid,.nf-footer-grid.is-no-cities{display:grid;grid-template-columns:minmax(0,1.55fr) repeat(3,minmax(0,1fr));gap:40px;padding:64px 0 40px}
        .nf-foot-brand p{color:#5f636b;font-size:14px;line-height:1.65;margin:16px 0 20px}
        .nf-foot-contacts a,.nf-foot-contacts span{color:#3a3d44;font-size:14px}
        .nf-foot-contacts a i,.nf-foot-contacts span i{display:none}.nf-foot-contacts a:first-child{font-size:17px;font-weight:700;color:#0f1115}
        .nf-foot-h{color:#0f1115;font-size:14px;font-weight:700;letter-spacing:0;text-transform:none}
        .nf-foot-col ul{gap:10px;margin:0 0 24px}
        .nf-foot-col ul li a,.nf-cities-grid li a{color:#5f636b;font-size:14px}
        .nf-foot-col ul li a:hover,.nf-cities-grid li a:hover,.nf-foot-contacts a:hover{color:#06745f}
        .nf-footer .nf-foot-cities{grid-column:1/-1;border-top:1px solid #e3e6ea;padding-top:16px}
        .nf-footer .nf-foot-cities summary{cursor:pointer;color:#5f636b;font-weight:600;margin:0}
        .nf-footer .nf-foot-cities details[open] summary{margin-bottom:16px}
        .nf-cities-grid{grid-template-columns:repeat(auto-fit,minmax(130px,1fr))}
        .nf-cities-all{color:#06745f}
        .nf-foot-bar{border-color:#e3e6ea}
        .nf-foot-bar-row,.nf-foot-social a{color:#5f636b;font-size:13px}
        .nf-foot-social a:hover{color:#06745f}
        .nf-footer a:focus-visible,.nf-footer summary:focus-visible{outline:2px solid #06745f;outline-offset:4px}
        @media(max-width:900px){.nf-footer-grid,.nf-footer-grid.is-no-cities{grid-template-columns:1fr 1fr;gap:28px}}
        @media(max-width:540px){.nf-wrap{width:calc(100% - 40px)}.nf-footer-grid,.nf-footer-grid.is-no-cities{grid-template-columns:1fr 1fr;gap:24px;padding:40px 0 28px}.nf-foot-brand{grid-column:1/-1}.nf-foot-bar-row{gap:12px}.nf-foot-social{flex-wrap:wrap;gap:12px}}
</style>
      <div class="nf-wrap nf-footer-grid<?php echo $show_cities ? "" : " is-no-cities"; ?>">
        <div class="nf-foot-brand">
          <a href="<?php echo esc_url(home_url("/")); ?>" class="nf-logo">
            <span class="nf-logo-mark" aria-hidden="true"></span>Upsellio
          </a>
          <p>Reklamy, strony i sklepy dla małych firm, sklepów internetowych, lokali i usług lokalnych.</p>
          <div class="nf-foot-contacts">
            <a href="<?php echo esc_url("tel:" . $contact_phone_href); ?>"><i>T</i><span><?php echo esc_html($contact_phone); ?></span></a>
            <a href="<?php echo esc_url($contact_email_href); ?>"><i>@</i><span><?php echo esc_html($contact_email); ?></span></a>
            <?php if ($brand_address !== "") : ?><span><i>A</i><span><?php echo esc_html($brand_address . " · pn–pt 9–17"); ?></span></span><?php endif; ?>
          </div>
        </div>

        <?php
        // Preserve configured links and distribute them over three reference columns.
        $column_groups = [[], [], []];
        foreach ($normalized_sections as $section_index => $footer_section) {
            $column_groups[min($section_index, 2)][] = $footer_section;
        }
        ?>
        <?php foreach ($column_groups as $column_sections) : ?>
        <div class="nf-foot-col">
          <?php foreach ($column_sections as $footer_section) : ?>
            <h2 class="nf-foot-h"><?php echo esc_html((string) $footer_section["title"]); ?></h2>
            <ul>
              <?php foreach ((array) $footer_section["links"] as $footer_link) : ?>
                <li><a href="<?php echo esc_url((string) $footer_link["url"]); ?>"><?php echo esc_html((string) $footer_link["label"]); ?></a></li>
              <?php endforeach; ?>
            </ul>
          <?php endforeach; ?>
        </div>
        <?php endforeach; ?>

        <?php if ($show_cities) : ?>
        <div class="nf-foot-col nf-foot-cities">
          <details class="up-footer-cities"><summary class="nf-foot-h"><?php echo esc_html($city_title); ?></summary>
          <ul class="nf-cities-grid">
            <?php foreach ($normalized_city_links as $city_link_item) : ?>
              <li><a href="<?php echo esc_url((string) $city_link_item["url"]); ?>"><?php echo esc_html((string) $city_link_item["label"]); ?></a></li>
            <?php endforeach; ?>
          </ul>
          <?php if ($city_all_url !== "") : ?><a class="nf-cities-all" href="<?php echo esc_url($city_all_url); ?>"><?php echo esc_html($city_all_label); ?></a><?php endif; ?>
          </details>
        </div>
        <?php endif; ?>
      </div>

      <div class="nf-foot-bar">
        <div class="nf-wrap nf-foot-bar-row">
          <span>© <?php echo esc_html(gmdate("Y")); ?> Upsellio · Sebastian Kelm · NIP 7773388263</span>
          <div class="nf-foot-social">
            <a href="<?php echo esc_url(function_exists("upsellio_cookie_privacy_url") ? upsellio_cookie_privacy_url() : home_url("/polityka-prywatnosci/")); ?>">Polityka prywatności</a>
            <a href="<?php echo esc_url(upsellio_get_social_profile_url("linkedin")); ?>">LinkedIn</a>
            <a href="<?php echo esc_url(upsellio_get_social_profile_url("facebook")); ?>">Facebook</a>
            <a href="https://www.instagram.com/upsellio.pl/">Instagram</a>
          </div>
        </div>
      </div>
    </footer>
    <?php
    return ob_get_clean();
}
}
if (!function_exists('upsellio_render_reference_cta')) {
function upsellio_render_reference_cta($href = '', $show_mail = false) {
    if (!$href) { $href = function_exists('upsellio_get_contact_page_url') ? upsellio_get_contact_page_url() : ''; }
    if (!$href) { $href = home_url('/kontakt/'); }
    $phone = function_exists('upsellio_get_contact_phone') ? upsellio_get_contact_phone() : '+48 575 522 595';
    ob_start();
    ?>
    <style>.ur-cta-band,.ur-cta-band *{box-sizing:border-box}.ur-cta-band{width:100%;background:#0f1115;color:#fff;padding:88px 0;font:400 16px/1.6 "DM Sans",sans-serif}.ur-cta-band .ur-cta-inner{max-width:1160px;margin:0 auto;padding:0 24px;display:flex;flex-wrap:wrap;gap:32px;align-items:center;justify-content:space-between}.ur-cta-band .ur-cta-copy{flex:1 1 520px;min-width:0}.ur-cta-band h2{font:800 44px/1.05 "Bricolage Grotesque",sans-serif;letter-spacing:-.03em;color:#fff;margin:0 0 12px;max-width:none}.ur-cta-band p{font-size:18px;color:#c4c8ce;margin:0}.ur-cta-band .ur-cta-actions{display:flex;flex-wrap:wrap;align-items:center;gap:20px}.ur-cta-band a{color:#fff;font-weight:700;font-size:17px}.ur-cta-band a.ur-cta-button{display:inline-flex;align-items:center;gap:8px;min-height:54px;padding:0 28px;border-radius:999px;background:#0bb39c;color:#0f1115;font-size:16px;text-decoration:none;transition:transform .18s cubic-bezier(.2,.7,.2,1),background .18s}.ur-cta-band a.ur-cta-button:hover{transform:translateY(-1px);background:#7fe3d2}.ur-cta-band a:focus-visible{outline:3px solid #0bb39c;outline-offset:4px}@media(max-width:720px){.ur-cta-band{padding:64px 0}.ur-cta-band .ur-cta-inner{padding:0 20px}.ur-cta-band h2{font-size:32px}.ur-cta-band .ur-cta-actions{width:100%;flex-direction:column;align-items:stretch;gap:16px}.ur-cta-band .ur-cta-actions a{text-align:center;justify-content:center}}@media(prefers-reduced-motion:reduce){.ur-cta-band a{transition:none!important;transform:none!important}}</style>
    <section class="ur-cta-band" aria-label="Bezpłatna diagnoza"><div class="ur-cta-inner"><div class="ur-cta-copy"><h2>Sprawdźmy, gdzie uciekają Twoi klienci.</h2><p>30 minut rozmowy. Bez prezentacji i bez zobowiązań.</p></div><div class="ur-cta-actions"><a class="ur-cta-button" href="<?php echo esc_url($href); ?>" data-cta="reference-final-diagnosis">Umów bezpłatną diagnozę →</a><a href="<?php echo esc_url('tel:' . preg_replace('/[^+0-9]/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a><?php if ($show_mail) : ?><a href="mailto:kontakt@upsellio.pl">kontakt@upsellio.pl</a><?php endif; ?></div></div></section>
    <?php
    return ob_get_clean();
}
}
if (!empty($args['reference_cta'])) {
    $reference_cta_id = (string) ($args['reference_cta_id'] ?? '');
    if ($reference_cta_id !== '') { echo '<div id="' . esc_attr($reference_cta_id) . '">'; }
    echo upsellio_render_reference_cta((string) $args['reference_cta']);
    if ($reference_cta_id !== '') { echo '</div>'; }
}
echo upsellio_render_reference_footer();
?>
<style id="upsellio-reference-motion">
:root{--up-ease:cubic-bezier(.2,.7,.2,1);--up-fast:150ms;--up-base:200ms;--up-slow:600ms}
@keyframes ur-section-reveal{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
@keyframes ur-faq-reveal{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:none}}
.ur-reveal-in{animation:ur-section-reveal var(--up-slow) var(--up-ease) both;animation-delay:var(--ur-delay,0ms)}
:is(.usr,#up-ref-main) details[open]>p{animation:ur-faq-reveal 220ms var(--up-ease) both}
:is(.usr,#up-ref-main,.ub,.sp-art,.ct-reference-form) :is(input,textarea):focus-visible{outline:none;border-color:#0bb39c;box-shadow:0 0 0 3px rgba(11,179,156,.18)}
:is(.usr,#up-ref-main,.ub) :is(.usr-btn,.upref-btn,.ub-btn):active{transform:translateY(0)}
:is(.usr,#up-ref-main) :is(.usr-btn,.upref-btn) svg{transition:transform 180ms var(--up-ease)}
:is(.usr,#up-ref-main) :is(.usr-primary,.upref-btn-p):hover svg{transform:translateX(4px)}
@media(prefers-reduced-motion:reduce){.ur-reveal-in,:is(.usr,#up-ref-main) details[open]>p{animation:none!important}:is(.usr,#up-ref-main,.ub) :is(a,button,svg){transition:none!important;transform:none!important}}
</style>
<script>
(function(){if(!('IntersectionObserver' in window)||window.matchMedia('(prefers-reduced-motion: reduce)').matches)return;var selectors='#up-ref-main > section:not(.upref-hero):not(.ur-cta-band), .usr > section:not(.usr-hero), .ub-list';var observer=new IntersectionObserver(function(entries){entries.forEach(function(entry){if(!entry.isIntersecting)return;observer.unobserve(entry.target);entry.target.classList.add('ur-reveal-in')})},{threshold:.1});document.querySelectorAll(selectors).forEach(function(section){var heading=section.querySelector('h2');if(heading&&heading.getBoundingClientRect().top>window.innerHeight)observer.observe(heading);var cards=section.querySelectorAll('.usr-card,.ub-card');cards.forEach(function(card,index){if(card.getBoundingClientRect().top>window.innerHeight){card.style.setProperty('--ur-delay',Math.min(index,5)*80+'ms');observer.observe(card)}})})})();
</script>
<?php wp_footer(); ?>
</body>
</html>

