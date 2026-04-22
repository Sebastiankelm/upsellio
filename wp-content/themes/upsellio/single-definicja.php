<?php
if (!defined("ABSPATH")) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();
    $postId = get_the_ID();
    $term = get_post_meta($postId, "_upsellio_definition_term", true) ?: get_the_title($postId);
    $slug = get_post_meta($postId, "_upsellio_definition_slug", true) ?: get_post_field("post_name", $postId);
    $mainKeyword = get_post_meta($postId, "_upsellio_definition_main_keyword", true);
    $category = get_post_meta($postId, "_upsellio_definition_category", true) ?: "marketing";
    $difficulty = get_post_meta($postId, "_upsellio_definition_difficulty", true) ?: "sredni";
    $related = upsellio_get_definition_related_links($slug, 6);
    $adjacent = upsellio_get_definition_adjacent_links($slug);
    $faq = get_post_meta($postId, "_upsellio_definition_faq", true);
    if (!is_array($faq)) {
        $faq = [];
    }
    $serviceLinks = get_post_meta($postId, "_upsellio_definition_service_links", true);
    if (!is_array($serviceLinks)) {
        $serviceLinks = [];
    }
    $contactPhone = function_exists("upsellio_get_contact_phone")
        ? upsellio_get_contact_phone()
        : "+48 575 522 595";
    $contactPhoneHref = preg_replace("/\s+/", "", $contactPhone);
    $contactEmail = "kontakt@upsellio.pl";
    $contactEmailHref = function_exists("upsellio_get_mailto_href") ? upsellio_get_mailto_href($contactEmail) : ("mailto:" . $contactEmail);
    $contactEmailDisplay = function_exists("upsellio_obfuscate_email_address") ? upsellio_obfuscate_email_address($contactEmail) : $contactEmail;
    $seed = abs(crc32($slug . "|" . $term));
    $articleHtml = apply_filters("the_content", get_the_content());

    $toolIntroPool = [
        "Szybko oszacuj, jak termin %s przeklada sie na wynik kampanii i strony.",
        "To proste narzedzie pomaga zinterpretowac %s w kontekscie realnych danych.",
        "Sprawdz praktyczny wynik dla %s na bazie trzech kluczowych liczb.",
        "W 30 sekund policz orientacyjny potencjal poprawy zwiazany z %s.",
        "Narzedzie wspiera szybka diagnoze, czy %s jest obecnie dobrze wykorzystywane.",
    ];
    $toolScoreLabelPool = [
        "Potencjal optymalizacji",
        "Wskaznik gotowosci",
        "Priorytet wdrozenia",
        "Indeks skutecznosci",
        "Poziom dopasowania",
    ];
    $toolPrimaryLabelPool = [
        "Miesieczny budzet reklamowy (PLN)",
        "Miesieczna liczba sesji na stronie",
        "Liczba leadow miesiecznie",
        "Srednia wartosc koszyka/oferty (PLN)",
        "Liczba zapytan handlowych miesiecznie",
    ];
    $toolSecondaryLabelPool = [
        "Aktualny wspolczynnik konwersji (%)",
        "Szacowany CTR kampanii (%)",
        "Jaka czesc leadow jest wartosciowa? (%)",
        "Jak oceniasz jakosc ruchu? (1-100)",
        "Jaki odsetek ruchu wraca na strone? (%)",
    ];
    $toolThirdLabelPool = [
        "Docelowa poprawa w 90 dni (%)",
        "Przewidywana poprawa po wdrozeniu (%)",
        "Mozliwa redukcja kosztu pozyskania (%)",
        "Wzrost jakosci leadow po zmianach (%)",
        "Planowany wzrost konwersji po testach (%)",
    ];
    $toolActionPool = [
        "Skup sie na uspojnieniu komunikatu reklamy i strony.",
        "Priorytet: poprawa intencji ruchu i filtrowanie leadow.",
        "Najwiekszy efekt da testowanie oferty i CTA.",
        "Wartym krokiem jest audyt lejka i analityki konwersji.",
        "Zacznij od 2-3 testow, potem skaluj dzialania.",
    ];
    $toolTitle = "Kalkulator praktyczny: " . $term;
    $toolIntro = sprintf($toolIntroPool[$seed % count($toolIntroPool)], $term);
    $toolScoreLabel = $toolScoreLabelPool[$seed % count($toolScoreLabelPool)];
    $toolPrimaryLabel = $toolPrimaryLabelPool[($seed + 5) % count($toolPrimaryLabelPool)];
    $toolSecondaryLabel = $toolSecondaryLabelPool[($seed + 11) % count($toolSecondaryLabelPool)];
    $toolThirdLabel = $toolThirdLabelPool[($seed + 19) % count($toolThirdLabelPool)];
    $toolActionText = $toolActionPool[($seed + 3) % count($toolActionPool)];

    $allDefinitionPosts = get_posts([
        "post_type" => "definicja",
        "post_status" => "publish",
        "numberposts" => 200,
        "orderby" => "title",
        "order" => "ASC",
    ]);
    $definitionInternalLinks = [];
    foreach ($allDefinitionPosts as $definitionPost) {
        if ((int) $definitionPost->ID === (int) $postId) {
            continue;
        }
        $definitionInternalLinks[] = [
            "name" => get_post_meta($definitionPost->ID, "_upsellio_definition_term", true) ?: get_the_title($definitionPost->ID),
            "url" => get_permalink($definitionPost->ID),
        ];
    }

    $cityPosts = get_posts([
        "post_type" => "miasto",
        "post_status" => "publish",
        "numberposts" => 200,
        "orderby" => "title",
        "order" => "ASC",
        "fields" => "ids",
    ]);
    $cityInternalLinks = [];
    if (!empty($cityPosts)) {
        foreach ($cityPosts as $cityPostId) {
            $cityInternalLinks[] = [
                "name" => get_post_meta($cityPostId, "_upsellio_city_name", true) ?: get_the_title($cityPostId),
                "url" => get_permalink($cityPostId),
            ];
        }
    } else {
        foreach (upsellio_get_cities_dataset() as $city) {
            $cityInternalLinks[] = [
                "name" => $city["name"],
                "url" => home_url("/miasto/" . $city["slug"] . "/"),
            ];
        }
    }

    $selectedDefinitionLinks = [];
    $seenDefinitionLinks = [];
    $definitionCount = count($definitionInternalLinks);
    for ($i = 0; $i < min(12, $definitionCount); $i++) {
        $idx = ($seed + $i * 7) % $definitionCount;
        $urlKey = $definitionInternalLinks[$idx]["url"];
        if (isset($seenDefinitionLinks[$urlKey])) {
            continue;
        }
        $seenDefinitionLinks[$urlKey] = true;
        $selectedDefinitionLinks[] = $definitionInternalLinks[$idx];
    }

    $selectedCityLinks = [];
    $seenCityLinks = [];
    $cityCount = count($cityInternalLinks);
    for ($i = 0; $i < min(10, $cityCount); $i++) {
        $idx = ($seed + $i * 9) % $cityCount;
        $urlKey = $cityInternalLinks[$idx]["url"];
        if (isset($seenCityLinks[$urlKey])) {
            continue;
        }
        $seenCityLinks[$urlKey] = true;
        $selectedCityLinks[] = $cityInternalLinks[$idx];
    }

    $insertAfterParagraphs = [2 + ($seed % 2), 4 + ($seed % 3), 6 + ($seed % 2)];
    $paragraphCounter = 0;
    $inlineCounter = 0;
    $articleHtml = preg_replace_callback(
        "/<\/p>/i",
        function ($matches) use (&$paragraphCounter, &$inlineCounter, $insertAfterParagraphs, $selectedDefinitionLinks, $selectedCityLinks, $term) {
            $paragraphCounter++;
            if (!in_array($paragraphCounter, $insertAfterParagraphs, true)) {
                return $matches[0];
            }
            $definitionLink = $selectedDefinitionLinks[$inlineCounter] ?? null;
            $cityLink = $selectedCityLinks[$inlineCounter] ?? null;
            $inlineCounter++;
            $linksHtml = "";
            if (is_array($definitionLink) && !empty($definitionLink["url"])) {
                $linksHtml .= '<a href="' . esc_url($definitionLink["url"]) . '">Powiazana definicja: ' . esc_html($definitionLink["name"]) . "</a>";
            }
            if (is_array($cityLink) && !empty($cityLink["url"])) {
                $linksHtml .= '<a href="' . esc_url($cityLink["url"]) . '">Uslugi lokalne: ' . esc_html("Marketing i strony WWW " . $cityLink["name"]) . "</a>";
            }
            return $matches[0] .
                '<aside class="definition-inline-cta">' .
                    '<strong>Wdroz ' . esc_html($term) . ' praktycznie, nie tylko teoretycznie.</strong>' .
                    '<div class="definition-inline-links">' . $linksHtml . "</div>" .
                    '<a class="definition-inline-btn" href="' . esc_url(home_url("/kontakt/")) . '">Umow bezplatna rozmowe</a>' .
                "</aside>";
        },
        $articleHtml
    );
    ?>
    <style>
      .definition-wrap{width:min(1140px,calc(100% - 32px));margin:0 auto}
      .definition-hero{padding:72px 0 34px;border-bottom:1px solid #e6e6e1;background:#f8f8f6}
      .definition-breadcrumbs{font-size:12px;color:#6f6f67;margin-bottom:14px}
      .definition-title{font-family:Syne,sans-serif;font-size:clamp(34px,5vw,56px);line-height:1.05;letter-spacing:-1px}
      .definition-lead{margin-top:14px;max-width:860px;font-size:18px;line-height:1.75;color:#3d3d38}
      .definition-pills{display:flex;flex-wrap:wrap;gap:10px;margin-top:20px}
      .definition-pill{font-size:12px;border:1px solid #c9c9c3;border-radius:999px;background:#fff;padding:7px 12px}
      .definition-main{padding:46px 0 60px;display:grid;grid-template-columns:1fr;gap:34px}
      .definition-content{line-height:1.8;color:#262624}
      .definition-content h2,.definition-content h3{font-family:Syne,sans-serif;color:#111110;line-height:1.2}
      .definition-content h2{font-size:33px;margin:0 0 14px}
      .definition-content h3{font-size:22px;margin:24px 0 8px}
      .definition-content p{margin:0 0 14px}
      .definition-content ul{margin:0 0 16px 20px}
      .definition-content li{margin:0 0 8px}
      .definition-content a{color:#1d9e75}
      .definition-content a:hover{text-decoration:underline}
      .definition-inline-cta{margin:16px 0 20px;padding:15px;border:1px solid #c3eddd;background:#e8f8f2;border-radius:12px}
      .definition-inline-cta strong{display:block;color:#0d4637;font-size:15px;line-height:1.5;margin-bottom:8px}
      .definition-inline-links{display:flex;flex-wrap:wrap;gap:9px;margin-bottom:10px}
      .definition-inline-links a{font-size:12px;color:#145f49}
      .definition-inline-btn{display:inline-flex;align-items:center;justify-content:center;background:#1d9e75;color:#fff;padding:8px 12px;border-radius:999px;font-size:12px;font-weight:700}
      .definition-tool{margin:26px 0 0;padding:20px;border:1px solid #e6e6e1;border-radius:14px;background:#fff}
      .definition-tool h2{font-size:28px;margin:0 0 8px}
      .definition-tool p{margin:0 0 12px;color:#3d3d38}
      .definition-tool-grid{display:grid;gap:12px}
      .definition-tool-grid label{display:grid;gap:6px;font-size:13px;color:#2f2f2a}
      .definition-tool-grid input{width:100%;border:1px solid #c9c9c3;border-radius:10px;padding:10px 12px;font:inherit}
      .definition-tool-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}
      .definition-tool-btn{border:none;border-radius:10px;padding:10px 14px;font:inherit;font-size:13px;font-weight:700;cursor:pointer}
      .definition-tool-btn.primary{background:#1d9e75;color:#fff}
      .definition-tool-btn.ghost{background:#f3f3ef;color:#4c4c46}
      .definition-tool-result{margin-top:12px;padding:14px;border-radius:12px;background:#f8f8f6;border:1px solid #e6e6e1;display:none}
      .definition-tool-result.show{display:block}
      .definition-tool-score{font-size:30px;font-family:Syne,sans-serif;line-height:1}
      .definition-tool-note{margin-top:6px;font-size:13px;color:#3f3f38}
      .definition-contact{margin-top:26px;padding:20px;border:1px solid #e6e6e1;border-radius:14px;background:#fff}
      .definition-contact h2{font-size:26px;margin:0 0 8px}
      .definition-contact p{margin:0 0 12px;color:#3d3d38}
      .definition-contact-links{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:12px}
      .definition-contact-links a{font-size:13px;font-weight:600}
      .definition-contact-form{display:grid;gap:12px}
      .definition-contact-row{display:grid;gap:12px;grid-template-columns:1fr}
      .definition-contact label{display:grid;gap:6px;font-size:13px;color:#2f2f2a}
      .definition-contact input,.definition-contact textarea{width:100%;border:1px solid #c9c9c3;border-radius:10px;padding:10px 12px;font:inherit}
      .definition-contact textarea{min-height:110px;resize:vertical}
      .definition-consent{display:flex;gap:8px;align-items:flex-start;font-size:12px;color:#56564f}
      .definition-consent input{margin-top:4px}
      .definition-linking{margin-top:28px;padding-top:22px;border-top:1px solid #e6e6e1}
      .definition-linking h2{font-size:24px;margin:0 0 10px}
      .definition-link-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px 14px}
      .definition-link-grid a{font-size:13px;color:#5f5f58}
      .definition-link-grid a:hover{color:#1d9e75}
      .definition-side{position:static;display:grid;gap:16px;height:max-content}
      .definition-card{border:1px solid #e6e6e1;border-radius:14px;background:#fff;padding:18px}
      .definition-card-title{font-family:Syne,sans-serif;font-size:22px;margin-bottom:10px}
      .definition-list{display:grid;gap:8px}
      .definition-list a{font-size:14px;color:#5f5f58}
      .definition-list a:hover{color:#1d9e75}
      .definition-phone-box{border:1px solid #c3eddd;background:#e8f8f2;border-radius:12px;padding:12px;font-size:13px;color:#0d4637}
      .definition-phone-box a{font-weight:700}
      .definition-adjacent{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:26px;padding-top:18px;border-top:1px solid #e6e6e1}
      .definition-adjacent a{display:block;border:1px solid #e6e6e1;border-radius:12px;padding:12px;min-width:220px;color:#111110}
      .definition-adjacent small{display:block;font-size:12px;color:#6f6f67;margin-bottom:6px}
      .definition-faq{margin-top:28px;padding-top:24px;border-top:1px solid #e6e6e1}
      .definition-faq-item + .definition-faq-item{margin-top:14px}
      @media(min-width:781px){.definition-contact-row{grid-template-columns:1fr 1fr}.definition-link-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
      @media(min-width:981px){.definition-wrap{width:min(1140px,calc(100% - 40px))}.definition-main{grid-template-columns:minmax(0,1fr) 320px}.definition-side{position:sticky}}
    </style>

    <section class="definition-hero">
      <div class="definition-wrap">
        <div class="definition-breadcrumbs">
          <a href="<?php echo esc_url(home_url("/")); ?>">Strona glowna</a> /
          <a href="<?php echo esc_url(home_url("/definicje/")); ?>">Definicje</a> /
          <span><?php echo esc_html($term); ?></span>
        </div>
        <h1 class="definition-title"><?php echo esc_html($term); ?></h1>
        <p class="definition-lead">
          Wyjasnienie pojecia <?php echo esc_html($term); ?> wraz z praktycznym zastosowaniem w SEO, kampaniach reklamowych i optymalizacji konwersji.
        </p>
        <div class="definition-pills">
          <span class="definition-pill">Kategoria: <?php echo esc_html($category); ?></span>
          <span class="definition-pill">Poziom: <?php echo esc_html($difficulty); ?></span>
          <?php if ($mainKeyword) : ?>
            <span class="definition-pill">Fraza: <?php echo esc_html($mainKeyword); ?></span>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <section class="definition-main definition-wrap">
      <article class="definition-content">
        <?php echo $articleHtml; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

        <section class="definition-tool" id="narzedzie-definicji">
          <h2><?php echo esc_html($toolTitle); ?></h2>
          <p><?php echo esc_html($toolIntro); ?></p>
          <div class="definition-tool-grid">
            <label>
              <?php echo esc_html($toolPrimaryLabel); ?>
              <input type="number" min="0" step="1" data-tool-input="a" />
            </label>
            <label>
              <?php echo esc_html($toolSecondaryLabel); ?>
              <input type="number" min="0" step="0.1" data-tool-input="b" />
            </label>
            <label>
              <?php echo esc_html($toolThirdLabel); ?>
              <input type="number" min="0" step="0.1" data-tool-input="c" />
            </label>
          </div>
          <div class="definition-tool-actions">
            <button type="button" class="definition-tool-btn primary" data-tool-action="calculate">Policz wynik</button>
            <button type="button" class="definition-tool-btn ghost" data-tool-action="reset">Wyczysc</button>
          </div>
          <div class="definition-tool-result" data-tool-result>
            <div class="definition-tool-score" data-tool-score>0</div>
            <div class="definition-tool-note">
              <strong><?php echo esc_html($toolScoreLabel); ?>:</strong>
              <span data-tool-message></span>
            </div>
            <div class="definition-tool-note"><?php echo esc_html($toolActionText); ?></div>
          </div>
        </section>

        <section class="definition-contact" id="formularz-definicji">
          <h2>Formularz kontaktowy</h2>
          <p>Chcesz wdrozyc <?php echo esc_html($term); ?> w praktyce? Opisz krotko sytuacje, przygotuje rekomendacje.</p>
          <div class="definition-contact-links">
            <a href="<?php echo esc_url("tel:" . $contactPhoneHref); ?>">Telefon: <?php echo esc_html($contactPhone); ?></a>
            <a href="<?php echo esc_url($contactEmailHref); ?>">E-mail: <?php echo esc_html($contactEmailDisplay); ?></a>
          </div>
          <form class="definition-contact-form" method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" data-upsellio-lead-form="1" data-upsellio-server-form="1">
            <input type="hidden" name="action" value="upsellio_submit_lead" />
            <input type="hidden" name="redirect_url" value="<?php echo esc_url(get_permalink($postId)); ?>" />
            <input type="hidden" name="lead_form_origin" value="definicja-single" />
            <input type="hidden" name="lead_source" value="definicja-single" />
            <input type="hidden" name="utm_source" data-ups-utm="source" value="" />
            <input type="hidden" name="utm_medium" data-ups-utm="medium" value="" />
            <input type="hidden" name="utm_campaign" data-ups-utm="campaign" value="" />
            <input type="hidden" name="landing_url" data-ups-context="landing" value="" />
            <input type="hidden" name="referrer" data-ups-context="referrer" value="" />
            <input type="hidden" name="lead_service" value="<?php echo esc_attr("Definicja: " . $term); ?>" />
            <input type="text" name="lead_website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;" />
            <?php wp_nonce_field("upsellio_unified_lead_form", "upsellio_lead_form_nonce"); ?>
            <div class="definition-contact-row">
              <label>
                Imie i firma *
                <input type="text" name="lead_name" required />
              </label>
              <label>
                E-mail *
                <input type="email" name="lead_email" required />
              </label>
            </div>
            <div class="definition-contact-row">
              <label>
                Telefon
                <input type="tel" name="lead_phone" />
              </label>
              <label>
                Co chcesz poprawic? *
                <textarea name="lead_message" required>Chce wdrozyc definicje <?php echo esc_textarea($term); ?> w praktyce.</textarea>
              </label>
            </div>
            <label class="definition-consent">
              <input type="checkbox" name="lead_consent" value="1" required />
              <span>Wyrazam zgode na kontakt w sprawie mojego zapytania.</span>
            </label>
            <button class="definition-tool-btn primary" type="submit">Wyslij formularz</button>
          </form>
        </section>

        <section class="definition-linking" aria-label="Linkowanie wewnetrzne definicji">
          <h2>Powiazane tematy i strony lokalne</h2>
          <div class="definition-link-grid">
            <?php foreach ($selectedDefinitionLinks as $definitionLink) : ?>
              <a href="<?php echo esc_url($definitionLink["url"]); ?>">
                <?php echo esc_html("Definicja: " . $definitionLink["name"]); ?>
              </a>
            <?php endforeach; ?>
            <?php foreach ($selectedCityLinks as $cityLink) : ?>
              <a href="<?php echo esc_url($cityLink["url"]); ?>">
                <?php echo esc_html("Marketing i strony WWW " . $cityLink["name"]); ?>
              </a>
            <?php endforeach; ?>
          </div>
        </section>

        <?php if (!empty($faq)) : ?>
          <div class="definition-faq">
            <h2>Dodatkowe FAQ</h2>
            <?php foreach ($faq as $item) : ?>
              <div class="definition-faq-item">
                <h3><?php echo esc_html($item["q"]); ?></h3>
                <p><?php echo esc_html($item["a"]); ?></p>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div class="definition-adjacent">
          <?php if (!empty($adjacent["prev"])) : ?>
            <a href="<?php echo esc_url($adjacent["prev"]["url"]); ?>">
              <small>Poprzednia definicja</small>
              <?php echo esc_html($adjacent["prev"]["name"]); ?>
            </a>
          <?php endif; ?>
          <?php if (!empty($adjacent["next"])) : ?>
            <a href="<?php echo esc_url($adjacent["next"]["url"]); ?>">
              <small>Nastepna definicja</small>
              <?php echo esc_html($adjacent["next"]["name"]); ?>
            </a>
          <?php endif; ?>
        </div>
      </article>

      <aside class="definition-side">
        <div class="definition-card">
          <div class="definition-card-title">Powiazane definicje</div>
          <div class="definition-list">
            <?php foreach ($related as $relatedItem) : ?>
              <a href="<?php echo esc_url($relatedItem["url"]); ?>"><?php echo esc_html($relatedItem["name"]); ?></a>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="definition-card">
          <div class="definition-card-title">Dalsze kroki</div>
          <div class="definition-list">
            <?php foreach ($serviceLinks as $relative) :
                $url = home_url($relative);
                $label = $relative === "/#kontakt" ? "Umow rozmowe" : ($relative === "/#uslugi" ? "Zobacz uslugi" : "Sprawdz miasta obslugi");
                ?>
              <a href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a>
            <?php endforeach; ?>
            <a href="<?php echo esc_url(home_url("/definicje/")); ?>">Powrot do wszystkich definicji</a>
          </div>
        </div>

        <div class="definition-phone-box">
          Potrzebujesz szybkiej konsultacji? Zadzwon:
          <a href="<?php echo esc_url("tel:" . $contactPhoneHref); ?>"><?php echo esc_html($contactPhone); ?></a>
        </div>
      </aside>
    </section>
    <script>
      (function () {
        var tool = document.getElementById("narzedzie-definicji");
        if (!tool) return;
        var inputA = tool.querySelector('[data-tool-input="a"]');
        var inputB = tool.querySelector('[data-tool-input="b"]');
        var inputC = tool.querySelector('[data-tool-input="c"]');
        var result = tool.querySelector("[data-tool-result]");
        var scoreEl = tool.querySelector("[data-tool-score]");
        var messageEl = tool.querySelector("[data-tool-message]");
        var calcBtn = tool.querySelector('[data-tool-action="calculate"]');
        var resetBtn = tool.querySelector('[data-tool-action="reset"]');
        if (!inputA || !inputB || !inputC || !result || !scoreEl || !messageEl || !calcBtn || !resetBtn) return;

        function normalize(value) {
          var n = parseFloat(value);
          if (!isFinite(n) || n < 0) return 0;
          return n;
        }

        function getMessage(score) {
          if (score < 35) return "Niski wynik. Najpierw uporzadkuj fundament: komunikat, pomiar i jasne CTA.";
          if (score < 70) return "Sredni wynik. Potencjal jest, ale potrzebna jest regularna optymalizacja.";
          return "Wysoki wynik. Mozesz skalowac to, co dziala i poprawiac jakosc leadow.";
        }

        calcBtn.addEventListener("click", function () {
          var a = normalize(inputA.value);
          var b = normalize(inputB.value);
          var c = normalize(inputC.value);
          var base = Math.min(100, Math.round((Math.log10(a + 10) * 18) + (b * 1.2) + (c * 1.4)));
          var score = Math.max(0, Math.min(100, base));
          scoreEl.textContent = score + "/100";
          messageEl.textContent = getMessage(score);
          result.classList.add("show");
        });

        resetBtn.addEventListener("click", function () {
          inputA.value = "";
          inputB.value = "";
          inputC.value = "";
          result.classList.remove("show");
          scoreEl.textContent = "0";
          messageEl.textContent = "";
        });
      })();
    </script>
    <?php
endwhile;

get_footer();

