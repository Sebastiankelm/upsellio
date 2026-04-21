<?php
if (!defined("ABSPATH")) {
    exit;
}

function upsellio_marketing_portfolio_interactive_variants()
{
    $variant_a = [
        "html" => '<div class="mshot mshot-a"><strong>Symulator CPL</strong><input type="range" min="100" max="2000" step="50" value="600" data-cpl-range><p>CPL: <b data-cpl-value>49 PLN</b> · Leady: <b data-cpl-leads>367</b>/mies.</p></div>',
        "css" => '.mshot-a{border:1px solid #dce7e1;border-radius:12px;padding:12px;background:#f8fcfa}.mshot-a input{width:100%;margin:8px 0}',
        "js" => '(function(){const r=document.querySelector("[data-cpl-range]");const v=document.querySelector("[data-cpl-value]");const l=document.querySelector("[data-cpl-leads]");if(!r||!v||!l)return;const u=()=>{const b=Number(r.value);const c=Math.max(28,Math.round(68-b/35));v.textContent=c+" PLN";l.textContent=Math.round((b*30)/c)};r.addEventListener("input",u);u();})();',
    ];
    $variant_b = [
        "html" => '<div class="mshot mshot-b"><strong>Quality Score leadów</strong><div class="mshot-tabs"><button data-q="cold" class="is-active">Cold</button><button data-q="warm">Warm</button><button data-q="hot">Hot</button></div><p data-q-copy>Cold: 22% leadów sprzedażowych</p></div>',
        "css" => '.mshot-b{border:1px solid #dce7e1;border-radius:12px;padding:12px;background:#f8fcfa}.mshot-tabs{display:flex;gap:8px;margin:10px 0}.mshot-tabs button{border:1px solid #cde5da;background:#fff;padding:6px 10px;border-radius:999px;cursor:pointer}.mshot-tabs .is-active{background:#1d9e75;color:#fff}',
        "js" => '(function(){const map={cold:"Cold: 22% leadów sprzedażowych",warm:"Warm: 47% leadów sprzedażowych",hot:"Hot: 71% leadów sprzedażowych"};const c=document.querySelector("[data-q-copy]");document.querySelectorAll("[data-q]").forEach(b=>b.addEventListener("click",()=>{document.querySelectorAll("[data-q]").forEach(x=>x.classList.remove("is-active"));b.classList.add("is-active");if(c)c.textContent=map[b.getAttribute("data-q")]||"";}));})();',
    ];
    $variant_c = [
        "html" => '<div class="mshot mshot-c"><strong>ROI Tracker</strong><label>Budżet mies. <input type="number" value="12000" min="1000" step="500" data-roi-budget></label><p>Szacowany ROI: <b data-roi-value>4.3x</b></p></div>',
        "css" => '.mshot-c{border:1px solid #dce7e1;border-radius:12px;padding:12px;background:#f8fcfa}.mshot-c input{width:100%;margin-top:6px;padding:8px;border:1px solid #d5e2db;border-radius:8px}',
        "js" => '(function(){const b=document.querySelector("[data-roi-budget]");const v=document.querySelector("[data-roi-value]");if(!b||!v)return;const u=()=>{const n=Number(b.value)||0;v.textContent=(Math.max(1.8,2.2+n/6000)).toFixed(1)+"x";};b.addEventListener("input",u);u();})();',
    ];

    return [$variant_a, $variant_b, $variant_c];
}

function upsellio_get_seeded_marketing_portfolio_projects()
{
    $variants = upsellio_marketing_portfolio_interactive_variants();
    $base_projects = [
        ["slug" => "meta-ads-b2b-cpl-52", "title" => "Meta Ads B2B: CPL -52%, jakość leadów x3", "category" => "meta", "category_label" => "Meta", "theme" => "vis-meta"],
        ["slug" => "google-ads-ecommerce-roas-187", "title" => "Google Ads e-commerce: ROAS +187% bez zwiększania budżetu", "category" => "google", "category_label" => "Google", "theme" => "vis-google"],
        ["slug" => "landing-page-b2b-konwersja-340", "title" => "Landing page B2B: konwersja +340% w 30 dni", "category" => "strona", "category_label" => "Strona", "theme" => "vis-landing"],
        ["slug" => "remarketing-meta-roas-x4", "title" => "Remarketing Meta Ads: ROAS x4 dla sklepu online", "category" => "meta", "category_label" => "Meta", "theme" => "vis-social"],
        ["slug" => "kampania-search-b2b-leady-x2", "title" => "Kampania Search B2B: 2x więcej leadów, CPL -44%", "category" => "google", "category_label" => "Google", "theme" => "vis-b2b"],
        ["slug" => "audyt-konta-meta-poprawa-jakosci", "title" => "Audyt konta Meta Ads: wzrost jakości zapytań o 62%", "category" => "meta", "category_label" => "Meta", "theme" => "vis-meta"],
        ["slug" => "ecommerce-fashion-marza-plus-31", "title" => "E-commerce fashion: marża +31% przy stabilnym wolumenie", "category" => "ecom", "category_label" => "Ecom", "theme" => "vis-ecom"],
        ["slug" => "strona-uslugowa-lokalna-leady-61", "title" => "Strona usługowa lokalna: leady +61% z SEO i Ads", "category" => "strona", "category_label" => "Strona", "theme" => "vis-landing"],
        ["slug" => "google-performance-max-koszt-sprzedazy", "title" => "Performance Max: koszt sprzedaży -28%, ROAS +64%", "category" => "google", "category_label" => "Google", "theme" => "vis-google"],
        ["slug" => "meta-ads-hotel-rezerwacje-premium", "title" => "Meta Ads hotel premium: rezerwacje direct +74%", "category" => "meta", "category_label" => "Meta", "theme" => "vis-social"],
        ["slug" => "landing-webinar-b2b-cvr-4-8", "title" => "Landing webinar B2B: CVR 4.8% i pipeline 420k PLN", "category" => "strona", "category_label" => "Strona", "theme" => "vis-b2b"],
        ["slug" => "ecommerce-home-decor-aov-27", "title" => "Sklep home decor: AOV +27% i porzucone koszyki -18%", "category" => "ecom", "category_label" => "Ecom", "theme" => "vis-ecom"],
        ["slug" => "google-ads-saas-trial-paid-27", "title" => "Google Ads SaaS: trial->paid 27% i churn -1.0 pp", "category" => "google", "category_label" => "Google", "theme" => "vis-google"],
        ["slug" => "meta-ads-edukacja-online-koszt-zapisu", "title" => "Meta Ads edukacja online: koszt zapisu -37%, frekwencja +26%", "category" => "meta", "category_label" => "Meta", "theme" => "vis-meta"],
        ["slug" => "strona-ekspercka-premium-inbound-189", "title" => "Strona ekspercka premium: ruch organiczny +189% i regularne leady", "category" => "strona", "category_label" => "Strona", "theme" => "vis-landing"],
    ];

    $projects = [];
    foreach ($base_projects as $index => $project) {
        $variant = $variants[$index % count($variants)];
        $projects[] = [
            "slug" => $project["slug"],
            "title" => $project["title"],
            "excerpt" => "Case study pokazujące pełny proces: diagnoza, wdrożenie, optymalizacja i wynik biznesowy pod lead generation.",
            "category" => $project["category_label"],
            "category_slug" => $project["category"],
            "type" => $project["category_label"],
            "meta" => "Lead generation · SEO · " . strtoupper("Q" . (($index % 4) + 1)) . " 2024",
            "badge" => $project["category_label"],
            "cta" => "Zobacz case study",
            "image" => "https://images.unsplash.com/photo-1551281044-8b5bd6fddf8f?auto=format&fit=crop&w=1600&q=80",
            "theme" => $project["theme"],
            "date" => "Q" . (($index % 4) + 1) . " 2024",
            "sector" => "Firma usługowa " . (($index % 2 === 0) ? "B2B" : "B2C"),
            "problem" => "Niska jakość leadów i rosnący koszt pozyskania blokowały efektywność kampanii oraz pracę działu sprzedaży.",
            "solution" => "Przebudowano komunikację reklam, stronę docelową i strukturę lejka wraz z pełnym trackingiem jakości leadów.",
            "result" => "W ciągu 3-4 miesięcy poprawiono kluczowe KPI kampanii i zwiększono udział leadów sprzedażowych.",
            "tags" => ["Lead generation", $project["category_label"], "Kampanie performance", "Optymalizacja konwersji"],
            "kpis" => [
                "CPL|312 PLN|150 PLN|-52%|w 4 miesiące",
                "Jakość leadów|20%|65%|x3|ocena działu sprzedaży",
                "CVR landing page|1.4%|4.2%|+200%|formularz kontaktowy",
                "ROAS|2.1|4.8|+129%|średnia 90 dni",
            ],
            "custom_html" => $variant["html"],
            "custom_css" => $variant["css"],
            "custom_js" => $variant["js"],
            "is_featured" => $index === 0,
        ];
    }

    return $projects;
}

function upsellio_seed_marketing_portfolio_projects($force = false)
{
    if (!post_type_exists("marketing_portfolio")) {
        return ["created" => 0, "updated" => 0, "message" => "marketing_portfolio_post_type_missing"];
    }

    $projects = upsellio_get_seeded_marketing_portfolio_projects();
    $created = 0;
    $updated = 0;

    foreach ($projects as $index => $project) {
        $slug = sanitize_title((string) ($project["slug"] ?? ""));
        if ($slug === "") {
            continue;
        }

        $existing_post = get_page_by_path($slug, OBJECT, "marketing_portfolio");
        $post_data = [
            "post_type" => "marketing_portfolio",
            "post_status" => "publish",
            "post_title" => (string) $project["title"],
            "post_name" => $slug,
            "post_excerpt" => (string) $project["excerpt"],
            "post_content" => "<h2>Sytuacja wyjściowa</h2><p>" . esc_html((string) $project["problem"]) . "</p><h2>Wdrożenie</h2><p>" . esc_html((string) $project["solution"]) . "</p><h2>Wynik</h2><p>" . esc_html((string) $project["result"]) . "</p>",
            "menu_order" => $index,
        ];

        if ($existing_post instanceof WP_Post) {
            if (!$force) {
                continue;
            }
            $post_data["ID"] = (int) $existing_post->ID;
            $post_id = wp_update_post($post_data, true);
            if (is_wp_error($post_id) || (int) $post_id <= 0) {
                continue;
            }
            $updated++;
        } else {
            $post_id = wp_insert_post($post_data, true);
            if (is_wp_error($post_id) || (int) $post_id <= 0) {
                continue;
            }
            $created++;
        }

        $term_name = (string) ($project["category"] ?? "Marketing");
        $term_slug = sanitize_title((string) ($project["category_slug"] ?? $term_name));
        $term = term_exists($term_slug, "marketing_portfolio_category");
        if (!$term) {
            $term = wp_insert_term($term_name, "marketing_portfolio_category", ["slug" => $term_slug]);
        }
        if (!is_wp_error($term)) {
            $term_id = (int) (is_array($term) ? ($term["term_id"] ?? 0) : 0);
            if ($term_id > 0) {
                wp_set_object_terms((int) $post_id, [$term_id], "marketing_portfolio_category");
            }
        }

        update_post_meta((int) $post_id, "_ups_mport_type", (string) ($project["type"] ?? ""));
        update_post_meta((int) $post_id, "_ups_mport_meta", (string) ($project["meta"] ?? ""));
        update_post_meta((int) $post_id, "_ups_mport_badge", (string) ($project["badge"] ?? ""));
        update_post_meta((int) $post_id, "_ups_mport_cta", (string) ($project["cta"] ?? ""));
        update_post_meta((int) $post_id, "_ups_mport_image", esc_url_raw((string) ($project["image"] ?? "")));
        update_post_meta((int) $post_id, "_ups_mport_theme", (string) ($project["theme"] ?? "vis-meta"));
        update_post_meta((int) $post_id, "_ups_mport_date", (string) ($project["date"] ?? ""));
        update_post_meta((int) $post_id, "_ups_mport_sector", (string) ($project["sector"] ?? ""));
        update_post_meta((int) $post_id, "_ups_mport_problem", (string) ($project["problem"] ?? ""));
        update_post_meta((int) $post_id, "_ups_mport_solution", (string) ($project["solution"] ?? ""));
        update_post_meta((int) $post_id, "_ups_mport_result", (string) ($project["result"] ?? ""));
        update_post_meta((int) $post_id, "_ups_mport_tags", implode("\n", (array) ($project["tags"] ?? [])));
        update_post_meta((int) $post_id, "_ups_mport_kpis", implode("\n", (array) ($project["kpis"] ?? [])));
        update_post_meta((int) $post_id, "_ups_mport_custom_html", (string) ($project["custom_html"] ?? ""));
        update_post_meta((int) $post_id, "_ups_mport_custom_css", (string) ($project["custom_css"] ?? ""));
        update_post_meta((int) $post_id, "_ups_mport_custom_js", (string) ($project["custom_js"] ?? ""));
        update_post_meta((int) $post_id, "_ups_mport_featured", !empty($project["is_featured"]) ? "1" : "0");
        update_post_meta((int) $post_id, "_ups_mport_seo_title", (string) ($project["title"] ?? "") . " | Upsellio");
        update_post_meta((int) $post_id, "_ups_mport_seo_description", (string) ($project["excerpt"] ?? ""));
        update_post_meta((int) $post_id, "_ups_mport_seo_canonical", (string) get_permalink((int) $post_id));
    }

    return ["created" => $created, "updated" => $updated, "message" => "ok"];
}

function upsellio_get_marketing_portfolio_seed_url($force = false)
{
    return add_query_arg([
        "upsellio_seed_marketing_portfolio" => 1,
        "force" => $force ? 1 : 0,
        "_upsellio_nonce" => wp_create_nonce("upsellio_seed_marketing_portfolio"),
    ], admin_url("edit.php?post_type=marketing_portfolio&page=upsellio-marketing-portfolio-seed"));
}

function upsellio_handle_marketing_portfolio_seed_request()
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }
    if (!isset($_GET["upsellio_seed_marketing_portfolio"])) {
        return;
    }

    $nonce = isset($_GET["_upsellio_nonce"]) ? sanitize_text_field(wp_unslash($_GET["_upsellio_nonce"])) : "";
    if (!wp_verify_nonce($nonce, "upsellio_seed_marketing_portfolio")) {
        return;
    }

    $force = isset($_GET["force"]) && (int) $_GET["force"] === 1;
    $result = upsellio_seed_marketing_portfolio_projects($force);
    update_option("upsellio_marketing_portfolio_seed_v1_done", "1");

    $redirect_url = add_query_arg([
        "upsellio_marketing_portfolio_seed_done" => 1,
        "created" => (int) ($result["created"] ?? 0),
        "updated" => (int) ($result["updated"] ?? 0),
        "msg" => (string) ($result["message"] ?? "ok"),
    ], admin_url("edit.php?post_type=marketing_portfolio&page=upsellio-marketing-portfolio-seed"));
    wp_safe_redirect($redirect_url);
    exit;
}
add_action("admin_init", "upsellio_handle_marketing_portfolio_seed_request");

function upsellio_register_marketing_portfolio_seed_menu()
{
    if (!post_type_exists("marketing_portfolio")) {
        return;
    }

    add_submenu_page(
        "edit.php?post_type=marketing_portfolio",
        "Generator portfolio marketingowego",
        "Generator 15 wpisów",
        "manage_options",
        "upsellio-marketing-portfolio-seed",
        "upsellio_marketing_portfolio_seed_screen"
    );
}
add_action("admin_menu", "upsellio_register_marketing_portfolio_seed_menu");

function upsellio_marketing_portfolio_seed_screen()
{
    if (!current_user_can("manage_options")) {
        return;
    }
    ?>
    <div class="wrap">
      <h1>Generator: 15 wpisów portfolio marketingowego</h1>
      <p>Tworzy 15 rozbudowanych case studies SEO + lead generation z metrykami KPI oraz osadzonymi blokami interaktywnymi HTML/CSS/JS.</p>
      <p><a class="button button-primary" href="<?php echo esc_url(upsellio_get_marketing_portfolio_seed_url(false)); ?>">Wygeneruj brakujące wpisy</a></p>
      <p><a class="button" href="<?php echo esc_url(upsellio_get_marketing_portfolio_seed_url(true)); ?>">Nadpisz i odśwież wszystkie 15 wpisów</a></p>
    </div>
    <?php
}

function upsellio_marketing_portfolio_seed_notice()
{
    if (!is_admin() || !isset($_GET["upsellio_marketing_portfolio_seed_done"])) {
        return;
    }

    $created = isset($_GET["created"]) ? (int) $_GET["created"] : 0;
    $updated = isset($_GET["updated"]) ? (int) $_GET["updated"] : 0;
    $msg = isset($_GET["msg"]) ? sanitize_text_field(wp_unslash($_GET["msg"])) : "ok";
    if ($msg !== "ok") {
        echo '<div class="notice notice-error"><p>Nie udało się wygenerować portfolio marketingowego.</p></div>';
        return;
    }

    echo '<div class="notice notice-success"><p>';
    echo esc_html("Portfolio marketingowe zaktualizowane. Utworzono: {$created}, zaktualizowano: {$updated}.");
    echo "</p></div>";
}
add_action("admin_notices", "upsellio_marketing_portfolio_seed_notice");

