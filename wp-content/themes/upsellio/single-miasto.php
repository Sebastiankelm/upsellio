<?php
if (!defined("ABSPATH")) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();
    $postId = get_the_ID();
    $citySlug = get_post_meta($postId, "_upsellio_city_slug", true) ?: get_post_field("post_name", $postId);
    $cityDatasetItem = function_exists("upsellio_get_city_by_slug") ? upsellio_get_city_by_slug($citySlug) : null;
    $cityName = is_array($cityDatasetItem) && !empty($cityDatasetItem["name"])
        ? $cityDatasetItem["name"]
        : (get_post_meta($postId, "_upsellio_city_name", true) ?: get_the_title());
    $voivodeship = is_array($cityDatasetItem) && !empty($cityDatasetItem["voivodeship"])
        ? $cityDatasetItem["voivodeship"]
        : (get_post_meta($postId, "_upsellio_city_voivodeship", true) ?: "polska");
    /* Meta z AI / edytora ma pierwszeństwo — dataset PHP to tylko fallback dla miast bez zapisanych pól. */
    $marketAngle = (string) get_post_meta($postId, "_upsellio_city_market_angle", true)
        ?: (is_array($cityDatasetItem) ? (string) ($cityDatasetItem["market_angle"] ?? "") : "")
        ?: "lokalne firmy";
    $serviceFocus = (string) get_post_meta($postId, "_upsellio_city_service_focus", true)
        ?: (is_array($cityDatasetItem) ? (string) ($cityDatasetItem["service_focus"] ?? "") : "")
        ?: "marketing i strony WWW";
    $localChallenge = (string) get_post_meta($postId, "_upsellio_city_local_challenge", true)
        ?: (is_array($cityDatasetItem) ? (string) ($cityDatasetItem["local_challenge"] ?? "") : "")
        ?: "niska jakość leadów z kampanii";
    $localAdvantage = (string) get_post_meta($postId, "_upsellio_city_local_advantage", true)
        ?: (is_array($cityDatasetItem) ? (string) ($cityDatasetItem["local_advantage"] ?? "") : "")
        ?: "stabilny popyt lokalny";
    $seasonalityAngle = (string) get_post_meta($postId, "_upsellio_city_seasonality_angle", true)
        ?: (is_array($cityDatasetItem) ? (string) ($cityDatasetItem["seasonality_angle"] ?? "") : "")
        ?: "stabilny popyt przez cały rok";
    $cta = get_post_meta($postId, "_upsellio_city_cta", true);
    $faq = get_post_meta($postId, "_upsellio_city_faq", true);
    if (!is_array($faq)) {
        $faq = [];
    }
    $related = upsellio_get_city_nearby_links($citySlug, 6);
    $frontPageSections = function_exists("upsellio_get_front_page_content_config")
        ? upsellio_get_front_page_content_config()
        : [];
    $contactPhone = function_exists("upsellio_get_contact_phone")
        ? upsellio_get_contact_phone()
        : trim((string) ($frontPageSections["contact_phone"] ?? ""));
    $contactEmail = trim((string) ($frontPageSections["contact_email"] ?? ""));
    if ($contactEmail === "") {
        $contactEmail = "kontakt@upsellio.pl";
    }
    $contactEmailHref = function_exists("upsellio_get_mailto_href") ? upsellio_get_mailto_href($contactEmail) : ("mailto:" . $contactEmail);
    $contactEmailDisplay = function_exists("upsellio_obfuscate_email_address") ? upsellio_obfuscate_email_address($contactEmail) : $contactEmail;
    $phoneHref = preg_replace("/[^0-9+]/", "", $contactPhone);
    $ctaSeed = abs(crc32($citySlug . "|" . $cityName));
    $city_page_title = "Marketing i strony WWW " . $cityName . " - Google Ads i Meta Ads dla firm | Upsellio";
    $city_page_description = "Kampanie Google Ads, Meta Ads i tworzenie stron internetowych dla firm z " . $cityName . ". Bezplatna diagnoza - zdalnie lub lokalnie.";
    $saved_desc = trim((string) get_post_meta($postId, "rank_math_description", true));
    if ($saved_desc === "") {
        $saved_desc = trim((string) get_post_meta($postId, "_yoast_wpseo_metadesc", true));
    }
    if ($saved_desc === "") {
        $saved_desc = trim((string) get_post_meta($postId, "_upsellio_city_meta_description", true));
    }
    if ($saved_desc !== "") {
        $city_page_description = $saved_desc;
    }

    $seo_plugin = function_exists("upsellio_is_seo_plugin_managing_frontend_meta") && upsellio_is_seo_plugin_managing_frontend_meta();

    add_filter("pre_get_document_title", static function ($title) use ($city_page_title, $postId, $seo_plugin) {
        if ($seo_plugin) {
            return $title;
        }
        $rm = trim((string) get_post_meta($postId, "rank_math_title", true));
        if ($rm !== "") {
            return $rm;
        }
        $yoast = trim((string) get_post_meta($postId, "_yoast_wpseo_title", true));
        if ($yoast !== "") {
            return $yoast;
        }

        return $city_page_title !== "" ? $city_page_title : $title;
    });
    add_action("wp_head", static function () use ($postId, $city_page_description, $cityName, $voivodeship, $seo_plugin) {
        $canonical = get_permalink($postId);
        if (!$seo_plugin) {
            echo '<meta name="description" content="' . esc_attr($city_page_description) . "\" />\n";
            echo '<link rel="canonical" href="' . esc_url($canonical) . "\" />\n";
        }
        echo '<script type="application/ld+json">' . wp_json_encode([
            "@context" => "https://schema.org",
            "@type" => "LocalBusiness",
            "name" => "Upsellio",
            "url" => $canonical,
            "areaServed" => [
                ["@type" => "City", "name" => $cityName],
                ["@type" => "AdministrativeArea", "name" => $voivodeship],
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
        echo '<script type="application/ld+json">' . wp_json_encode([
            "@context" => "https://schema.org",
            "@type" => "BreadcrumbList",
            "itemListElement" => [
                ["@type" => "ListItem", "position" => 1, "name" => "Strona glowna", "item" => home_url("/")],
                ["@type" => "ListItem", "position" => 2, "name" => "Miasta", "item" => function_exists("upsellio_get_cities_archive_url") ? upsellio_get_cities_archive_url() : get_post_type_archive_link("miasto")],
                ["@type" => "ListItem", "position" => 3, "name" => $cityName, "item" => $canonical],
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
    }, 2);

    // WGS84 city representative points, from polish-cities (OSM/Nominatim and TERYT).
    // Coastal points use land locations; Puck and Czestochowa Polnoc use PRNG.
    // Source: https://github.com/jarekosm/polish-cities (ODbL 1.0).
    $cityCoordinates = [
        "warszawa" => [52.233001, 21.06142],
        "krakow" => [50.0469, 20.00479],
        "wroclaw" => [51.126365, 16.991779],
        "lodz" => [51.772985, 19.480405],
        "poznan" => [52.400626, 16.901647],
        "gdansk" => [54.352, 18.6466],
        "szczecin" => [53.4297, 14.624621],
        "bydgoszcz" => [53.129727, 18.038381],
        "lublin" => [51.218183, 22.563665],
        "bialystok" => [53.127598, 23.156495],
        "katowice" => [50.213877, 19.007983],
        "gdynia" => [54.533333, 18.533333],
        "czestochowa" => [50.809635, 19.122219],
        "radom" => [51.417238, 21.156131],
        "sosnowiec" => [50.271233, 19.194633],
        "torun" => [53.014484, 18.601448],
        "kielce" => [50.854186, 20.615575],
        "rzeszow" => [50.013387, 21.976416],
        "gliwice" => [50.301176, 18.649657],
        "zabrze" => [50.314244, 18.7965],
        "olsztyn" => [53.776611, 20.466568],
        "bielsko-biala" => [49.812061, 19.03878],
        "bytom" => [50.365727, 18.87634],
        "zielona-gora" => [51.927623, 15.529186],
        "rybnik" => [50.110519, 18.549262],
        "ruda-slaska" => [50.270062, 18.874253],
        "tychy" => [50.126501, 19.008393],
        "dabrowa-gornicza" => [50.369745, 19.321354],
        "opole" => [50.678796, 17.906141],
        "elblag" => [54.198853, 19.434352],
        "plock" => [52.535415, 19.71497],
        "walbrzych" => [50.785116, 16.291224],
        "wloclawek" => [52.665381, 19.060831],
        "gorzow-wielkopolski" => [52.730556, 15.217154],
        "tarnow" => [50.026065, 20.97798],
        "chorzow" => [50.288479, 18.962897],
        "koszalin" => [54.207179, 16.201919],
        "kalisz" => [51.747316, 18.076058],
        "legnica" => [51.204688, 16.158232],
        "grudziadz" => [53.472458, 18.778493],
        "slupsk" => [54.463575, 17.011429],
        "jaworzno" => [50.189415, 19.301327],
        "jastrzebie-zdroj" => [49.960629, 18.600587],
        "nowy-sacz" => [49.61025, 20.706641],
        "jelenia-gora" => [50.850165, 15.727517],
        "siedlce" => [52.16175, 22.275751],
        "konin" => [52.256117, 18.262651],
        "pila" => [53.134895, 16.760176],
        "ostrowiec-swietokrzyski" => [50.950204, 21.406432],
        "inowroclaw" => [52.781666, 18.250236],
        "lubin" => [51.399417, 16.193942],
        "ostroleka" => [53.07785, 21.600843],
        "stargard" => [53.309032, 15.01162],
        "gniezno" => [52.524872, 17.603341],
        "suwalki" => [54.102717, 22.929215],
        "pabianice" => [51.665742, 19.354854],
        "chelm" => [51.135601, 23.496203],
        "przemysl" => [49.783554, 22.784247],
        "zamosc" => [50.721397, 23.248516],
        "tomaszow-mazowiecki" => [51.520598, 20.025975],
        "lomza" => [53.182572, 22.058877],
        "leszno" => [51.843962, 16.574029],
        "stalowa-wola" => [50.566497, 22.017432],
        "pulawy" => [51.426583, 21.977218],
        "tarnowskie-gory" => [50.465577, 18.842062],
        "bedzin" => [50.335706, 19.123989],
        "zgierz" => [51.855602, 19.394062],
        "biala-podlaska" => [52.02902, 23.138281],
        "elk" => [53.82393, 22.35998],
        "pruszkow" => [52.172013, 20.798982],
        "nowy-targ" => [49.489259, 20.042968],
        "piekary-slaskie" => [50.375829, 18.978013],
        "raciborz" => [50.090615, 18.221759],
        "mielec" => [50.280397, 21.483686],
        "swidnica" => [50.840617, 16.492523],
        "siemianowice-slaskie" => [50.312366, 19.023326],
        "tczew" => [54.090717, 18.78161],
        "piotrkow-trybunalski" => [51.412893, 19.716577],
        "belchatow" => [51.354579, 19.362703],
        "starachowice" => [51.038332, 21.071575],
        "boleslawiec" => [51.262169, 15.55163],
        "wejherowo" => [54.60609, 18.233624],
        "skierniewice" => [51.958073, 20.169121],
        "ostrow-wielkopolski" => [51.646877, 17.803984],
        "swinoujscie" => [53.853054, 14.313143],
        "kedzierzyn-kozle" => [50.345441, 18.245532],
        "sopot" => [54.443043, 18.560512],
        "zawiercie" => [50.505618, 19.470427],
        "knurow" => [50.207225, 18.662957],
        "rumia" => [54.569276, 18.384915],
        "przemkow" => [51.531098, 15.780624],
        "zory" => [50.038947, 18.688398],
        "wodzislaw-slaski" => [50.002292, 18.439974],
        "marki" => [52.337004, 21.119964],
        "piaseczno" => [52.073475, 21.017137],
        "myslowice" => [50.209618, 19.154582],
        "sanok" => [49.558954, 22.215537],
        "ciechanow" => [52.871085, 20.614905],
        "reda" => [54.620976, 18.33441],
        "radzionkow" => [50.401493, 18.900718],
        "radzyn-podlaski" => [51.776838, 22.626461],
        "nysa" => [50.47358, 17.336965],
        "jaroslaw" => [50.040009, 22.670535],
        "leczna" => [51.302593, 22.885179],
        "pszczyna" => [49.98637, 18.933004],
        "cieszyn" => [49.755781, 18.65144],
        "mikolow" => [50.196067, 18.861887],
        "srem" => [52.086759, 17.018221],
        "kutno" => [52.222282, 19.371138],
        "wyszkow" => [52.592308, 21.447292],
        "ilawa" => [53.596001, 19.559402],
        "augustow" => [53.844897, 23.009893],
        "brzeg" => [50.858877, 17.462846],
        "namyslow" => [51.076894, 17.695437],
        "krosno" => [49.689269, 21.76132],
        "krapkowice" => [50.478605, 17.957385],
        "mlawa" => [53.134251, 20.381682],
        "olecko" => [54.036197, 22.49811],
        "bochnia" => [49.974731, 20.429361],
        "andrychow" => [49.853807, 19.339295],
        "wadowice" => [49.884067, 19.49545],
        "koscian" => [52.08346, 16.649453],
        "wrzesnia" => [52.325814, 17.568515],
        "szamotuly" => [52.610102, 16.575234],
        "jarocin" => [51.958472, 17.507554],
        "wagrowiec" => [52.812254, 17.197613],
        "sieradz" => [51.601723, 18.738381],
        "zdunska-wola" => [51.595085, 18.964758],
        "glogow" => [51.670185, 16.043507],
        "lubartow" => [51.458407, 22.605893],
        "polkowice" => [51.50133, 16.068245],
        "brodnica" => [53.257961, 19.406477],
        "bielawa" => [50.675471, 16.602826],
        "luban" => [51.114691, 15.280862],
        "leba" => [54.760338, 17.581854],
        "szczytno" => [53.56364, 20.997865],
        "lidzbark-warminski" => [54.130731, 20.567466],
        "mragowo" => [53.86843, 21.298835],
        "pisz" => [53.627311, 21.809603],
        "ketrzyn" => [54.082321, 21.375075],
        "bartoszyce" => [54.253431, 20.810384],
        "ostroda" => [53.70278, 19.95299],
        "nidzica" => [53.363005, 20.42298],
        "kolobrzeg" => [54.175776, 15.596982],
        "bialogard" => [54.014051, 15.975629],
        "drawsko-pomorskie" => [53.533646, 15.806855],
        "gryfino" => [53.256045, 14.49734],
        "police" => [53.56775, 14.579553],
        "goleniow" => [53.560687, 14.838289],
        "kamien-pomorski" => [53.96956, 14.782954],
        "myslenice" => [49.82774, 19.936396],
        "limanowa" => [49.714993, 20.410296],
        "gorlice" => [49.666316, 21.165942],
        "brzesko" => [49.974532, 20.610792],
        "nowy-dwor-mazowiecki" => [52.434251, 20.69179],
        "legionowo" => [52.404894, 20.939314],
        "minsk-mazowiecki" => [52.178966, 21.556624],
        "wolomin" => [52.342697, 21.236141],
        "otwock" => [52.116088, 21.289344],
        "sochaczew" => [52.236269, 20.244531],
        "grodzisk-mazowiecki" => [52.105217, 20.633201],
        "zyrardow" => [52.055549, 20.440572],
        "pultusk" => [52.701582, 21.085235],
        "sandomierz" => [50.67958, 21.747097],
        "busko-zdroj" => [50.470299, 20.716431],
        "skarzysko-kamienna" => [51.122987, 20.875292],
        "konskie" => [51.184476, 20.40553],
        "bilgoraj" => [50.539011, 22.711695],
        "hrubieszow" => [50.806796, 23.890501],
        "debica" => [50.045955, 21.416003],
        "jaslo" => [49.74701, 21.471723],
        "krosno-odrzanskie" => [52.041076, 15.101489],
        "zary" => [51.624549, 15.145445],
        "nowa-sol" => [51.803404, 15.711852],
        "swiebodzin" => [52.248948, 15.5257],
        "miedzyrzecz" => [52.446132, 15.579628],
        "sulechow" => [52.086064, 15.614294],
        "lukow" => [51.925294, 22.385492],
        "turek" => [52.016194, 18.511509],
        "kolo" => [52.198384, 18.635746],
        "zlotow" => [53.359183, 17.045127],
        "chojnice" => [53.69997, 17.560158],
        "starogard-gdanski" => [53.965461, 18.526449],
        "kwidzyn" => [53.720466, 18.945284],
        "malbork" => [54.028507, 19.044351],
        "prudnik" => [50.305862, 17.568839],
        "kluczbork" => [50.974564, 18.220485],
        "szczecinek" => [53.702753, 16.694611],
        "walcz" => [53.272619, 16.449497],
        "bytow" => [54.179849, 17.500617],
        "czluchow" => [53.664779, 17.368084],
        "puck" => [54.705278, 18.405556],
        "lebork" => [54.535593, 17.757786],
        "sierpc" => [52.853228, 19.665053],
        "gostynin" => [52.424231, 19.46258],
        "plonsk" => [52.626472, 20.363008],
        "czestochowa-polnoc" => [50.841389, 19.131111],
        "myszkow" => [50.564663, 19.327018],
        "radzymin" => [52.426067, 21.165392],
        "wielun" => [51.225455, 18.569132],
    ];
    $coordinates = $cityCoordinates[$citySlug] ?? null;
    $hasMapPin = is_array($coordinates);
    if ($hasMapPin) {
        [$latitude, $longitude] = $coordinates;
        // Match the equirectangular projection of the SVG below, including padding.
        $mapPinXPct = round((8 + ($longitude - 14.1237) * 481 / (24.1454 - 14.1237)) / 497 * 100, 5);
        $mapPinYPct = round((8 + (54.8364 - $latitude) * 447 / (54.8364 - 49.002)) / 463 * 100, 5);
    }

    $ctaActionPool = [
        "Umów bezpłatną konsultację dla %s",
        "Sprawdź potencjał wzrostu firmy w %s",
        "Dopasuj kampanie do decyzji klienta w %s",
        "Popraw konwersję strony dla rynku %s",
        "Zaplanuj 90 dni marketingu dla %s",
        "Zweryfikuj jakość leadów z %s",
        "Uruchom lepiej targetowane reklamy dla %s",
        "Uspójnij lejek sprzedażowy dla %s",
        "Skróć czas od kliku do zapytania w %s",
        "Podnieś skuteczność sprzedaży B2B w %s",
        "Ułóż plan Meta Ads i Google Ads dla %s",
        "Przestaw marketing na mierzalny wynik w %s",
        "Wzmocnij pozycje firmy lokalnie w %s",
        "Zwiększ liczbę wartościowych rozmów z %s",
        "Ułóż stronę pod leady dla %s",
        "Zweryfikuj co blokuje sprzedaż w %s",
        "Skaluj zapytania bez podbijania CPL w %s",
        "Połącz reklamy i stronę dla miasta %s",
        "Przygotuj lokalny plan SEO i Ads dla %s",
        "Ustaw proces pozyskiwania klientów dla %s",
    ];
    $ctaBenefitPool = [
        "i otrzymaj konkretne rekomendacje na pierwsze 2 tygodnie.",
        "bez ogólników i bez przepalania budżetu reklamowego.",
        "z priorytetami wdrożeń pod realną sprzedaż.",
        "z szybkim audytem strony, reklam i analityki.",
        "z naciskiem na jakość leadów, nie tylko ich ilość.",
        "aby kampanie i oferta działały jako jeden system.",
        "z planem testów komunikatu i kreacji reklamowych.",
        "z jasnym podziałem: co poprawić od razu, co skalować dalej.",
        "z mierzeniem pełnej ścieżki: klik, lead, rozmowa, sprzedaż.",
        "aby szybciej domykać zapytania od klientów B2B.",
    ];
    $ctaLibrary = [];
    foreach ($ctaActionPool as $actionText) {
        foreach ($ctaBenefitPool as $benefitText) {
            $ctaLibrary[] = sprintf($actionText . " " . $benefitText, $cityName);
        }
    }
    $ctaLibraryCount = count($ctaLibrary);

    $articleHtml = apply_filters("the_content", get_the_content());

    $definitionPosts = get_posts([
        "post_type" => "definicja",
        "post_status" => "publish",
        "numberposts" => 16,
        "orderby" => "date",
        "order" => "DESC",
    ]);
    $definitionLinks = [];
    foreach ($definitionPosts as $definitionPost) {
        $definitionLinks[] = [
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
    $cityLinks = [];
    if (!empty($cityPosts)) {
        foreach ($cityPosts as $cityPostId) {
            if ((int) $cityPostId === (int) $postId) {
                continue;
            }
            $linkedCitySlug = get_post_meta($cityPostId, "_upsellio_city_slug", true) ?: get_post_field("post_name", $cityPostId);
            $linkedCityDatasetItem = function_exists("upsellio_get_city_by_slug") ? upsellio_get_city_by_slug($linkedCitySlug) : null;
            $cityLinks[] = [
                "name" => is_array($linkedCityDatasetItem) && !empty($linkedCityDatasetItem["name"])
                    ? $linkedCityDatasetItem["name"]
                    : (get_post_meta($cityPostId, "_upsellio_city_name", true) ?: get_the_title($cityPostId)),
                "url" => get_permalink($cityPostId),
            ];
        }
    } else {
        foreach (upsellio_get_cities_dataset() as $city) {
            if (($city["slug"] ?? "") === $citySlug) {
                continue;
            }
            $cityLinks[] = [
                "name" => $city["name"],
                "url" => home_url("/miasto/" . $city["slug"] . "/"),
            ];
        }
    }

    $cityInternalLinks = [];
    for ($i = 0; $i < 12; $i++) {
        if (empty($cityLinks)) {
            break;
        }
        $cityInternalLinks[] = $cityLinks[($ctaSeed + $i * 11) % count($cityLinks)];
    }

    $definitionInternalLinks = [];
    for ($i = 0; $i < 8; $i++) {
        if (empty($definitionLinks)) {
            break;
        }
        $definitionInternalLinks[] = $definitionLinks[($ctaSeed + $i * 7) % count($definitionLinks)];
    }

    $inlineCtaIndexes = [
        $ctaSeed % max(1, $ctaLibraryCount),
        ($ctaSeed + 67) % max(1, $ctaLibraryCount),
        ($ctaSeed + 131) % max(1, $ctaLibraryCount),
    ];
    $inlineCityIndexes = [0, 1, 2];
    $inlineDefIndexes = [0, 1, 2];
    $paragraphCounter = 0;
    $inlineCounter = 0;
    $insertAfterParagraphs = [2 + ($ctaSeed % 2), 4 + ($ctaSeed % 3), 7 + ($ctaSeed % 2)];
    $articleHtml = preg_replace_callback(
        "/<\/p>/i",
        function ($matches) use (
            &$paragraphCounter,
            &$inlineCounter,
            $insertAfterParagraphs,
            $ctaLibrary,
            $inlineCtaIndexes,
            $cityInternalLinks,
            $inlineCityIndexes,
            $definitionInternalLinks,
            $inlineDefIndexes
        ) {
            $paragraphCounter++;
            if (!in_array($paragraphCounter, $insertAfterParagraphs, true)) {
                return $matches[0];
            }
            if ($inlineCounter >= count($inlineCtaIndexes)) {
                return $matches[0];
            }
            $ctaText = $ctaLibrary[$inlineCtaIndexes[$inlineCounter]] ?? "";
            $cityLink = $cityInternalLinks[$inlineCityIndexes[$inlineCounter]] ?? null;
            $definitionLink = $definitionInternalLinks[$inlineDefIndexes[$inlineCounter]] ?? null;
            $inlineCounter++;

            if ($ctaText === "") {
                return $matches[0];
            }

            $linksHtml = "";
            if (is_array($cityLink) && !empty($cityLink["url"])) {
                $linksHtml .= '<a href="' . esc_url($cityLink["url"]) . '">Zobacz też: ' . esc_html("Marketing i strony WWW " . $cityLink["name"]) . "</a>";
            }
            if (is_array($definitionLink) && !empty($definitionLink["url"])) {
                $linksHtml .= '<a href="' . esc_url($definitionLink["url"]) . '">Przeczytaj definicję: ' . esc_html($definitionLink["name"]) . "</a>";
            }

            return $matches[0] .
                '<aside class="city-inline-cta">' .
                    '<strong>' . esc_html($ctaText) . "</strong>" .
                    '<div class="city-inline-cta-links">' . $linksHtml . "</div>" .
                    '<a class="city-inline-cta-btn" href="' . esc_url(home_url("/kontakt/")) . '">Umów rozmowę</a>' .
                "</aside>";
        },
        $articleHtml
    );
    // Keep native table semantics and allow wide tables to scroll on narrow screens.
    $articleHtml = preg_replace_callback(
        '/<table\b[^>]*>[\s\S]*?<\/table>/i',
        static function ($match) {
            $table = $match[0];
            $captionText = "Tabela danych";
            if (preg_match('/<caption\b[^>]*>([\s\S]*?)<\/caption>/i', $table, $captionMatch)) {
                $captionText = trim(wp_strip_all_tags($captionMatch[1]));
                $captionText = $captionText !== "" ? $captionText : "Tabela danych";
                // Retain the native caption and make the specific scenario table easier to scan.
                if ($captionText === "Przykładowe scenariusze marketingowe") {
                    $table = preg_replace('/<\/caption>/i', '<span class="city-table-subtitle">Porównanie ofert, kryteriów zakupowych i działań marketingowych</span></caption>', $table, 1);
                }
            }
            $columnCount = 0;
            if (preg_match('/<thead\b[^>]*>[\s\S]*?<tr\b[^>]*>([\s\S]*?)<\/tr>/i', $table, $headerRow)) {
                $columnCount = preg_match_all('/<(?:th|td)\b/i', $headerRow[1]);
            }
            $layout = $columnCount === 2 ? " city-table--two" : ($columnCount === 3 ? " city-table--three" : " city-table--wide");
            return '<div class="city-table-card' . $layout . '"><div class="city-table-scroll" role="region" aria-label="' . esc_attr($captionText) . '" tabindex="0">' . $table . '</div><p class="city-table-hint" aria-hidden="true">Przesuń tabelę w bok, aby zobaczyć wszystkie kolumny.</p></div>';
        },
        $articleHtml
    );
    ?>
    <style>
      .city-wrap{width:min(1240px,calc(100% - 32px));margin:0 auto}
      .city-hero{padding:72px 0 48px;border-bottom:1px solid var(--border,#e2e8f0);background:radial-gradient(circle at top right,rgba(20,184,166,.18),transparent 36%),linear-gradient(180deg,#ecfeff,#f1f5f9)}
      .city-hero-grid{display:grid;gap:30px;align-items:center}
      .city-hero-copy{min-width:0}
      .city-hero-map{display:none;background:#fff;border:1px solid var(--border,#e2e8f0);border-radius:22px;padding:20px;box-shadow:0 14px 40px rgba(15,23,42,.08)}
      .city-hero-map-visual{position:relative}
      .city-hero-map-visual svg{width:100%;aspect-ratio:497/463;height:auto;display:block;object-fit:contain}
      .city-hero-map-pin{position:absolute;transform:translate(-50%,-50%);pointer-events:none}
      .city-hero-map-pin::before,.city-hero-map-pin::after{content:"";position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);border-radius:999px}
      .city-hero-map-pin::before{width:34px;height:34px;background:rgba(13,148,136,.18)}
      .city-hero-map-pin::after{width:18px;height:18px;background:rgba(13,148,136,.32)}
      .city-hero-map-pin-dot{display:block;width:10px;height:10px;border-radius:999px;background:#0f766e}
      .city-hero-map-caption{margin-top:10px;display:flex;justify-content:space-between;gap:10px;align-items:baseline;font-size:12px;color:#475569}
      .city-map-attribution{display:block;margin-top:4px;font-size:10px;color:#64748b}.city-map-attribution a{color:inherit;text-decoration:underline}.city-hero-map-caption strong{color:#0f766e;font-family:"Bricolage Grotesque",sans-serif;font-size:16px;letter-spacing:-.02em}
      @media(min-width:961px){.city-hero-grid{grid-template-columns:1.25fr .75fr}.city-hero-map{display:block}}
      .city-breadcrumbs{font-size:12px;color:var(--text-3,#64748b);margin-bottom:14px}
      .city-h1{font-family:var(--font-display, "Bricolage Grotesque", sans-serif);font-weight:800;font-size:clamp(36px,5vw,62px);line-height:1.02;letter-spacing:-1.5px}
      .city-lead{margin-top:18px;font-size:18px;line-height:1.8;color:var(--text-2,#334155);max-width:860px}
      .city-meta{display:flex;gap:10px;flex-wrap:wrap;margin-top:22px}
      .city-pill{font-size:12px;border:1px solid var(--border-strong,#c9c9c3);border-radius:999px;padding:6px 12px;background:var(--surface,#fff)}
      .city-main{padding:56px 0 72px;display:grid;grid-template-columns:1fr;gap:34px}
      .city-content{line-height:1.8;color:#262624;padding:26px;border:1px solid var(--border,#e6e6e1);border-radius:18px;background:var(--surface,#fff)}
      .city-content h2,.city-content h3{font-family:var(--font-display, "Bricolage Grotesque", sans-serif);line-height:1.2;color:#071426}
      .city-content h2{font-size:32px;margin:0 0 16px}
      .city-content h3{font-size:23px;margin:28px 0 10px}
      .city-content p{margin:0 0 14px}
      .city-content ul{margin:0 0 16px 20px}
      .city-content li{margin:0 0 8px}
      .city-content .city-inline-cta{margin:18px 0 22px;padding:16px 16px 14px;border:1px solid var(--teal-line,#99f6e4);background:var(--teal-soft,#ecfeff);border-radius:12px}
      .city-content .city-inline-cta strong{display:block;font-size:15px;line-height:1.5;margin-bottom:8px;color:#0d4637}
      .city-inline-cta-links{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:10px}
      .city-inline-cta-links a{font-size:12px;color:#145f49;font-weight:500}
      .city-inline-cta-btn{display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;padding:7px 12px;border-radius:999px;background:var(--teal,#0d9488);color:#fff}
      .city-side-card{border:1px solid var(--border,#e6e6e1);border-radius:18px;padding:22px;background:var(--surface,#fff);position:static;top:96px}
      .city-side-title{font-family:var(--font-display, "Bricolage Grotesque", sans-serif);font-size:22px;margin-bottom:10px}
      .city-side-list{display:grid;gap:8px;margin-top:14px}
      .city-side-link{font-size:14px;color:#5f5f58}
      .city-side-link:hover{color:var(--teal,#0d9488)}
      .city-cta{margin-top:22px;padding:16px;border-radius:12px;background:var(--teal-soft,#ecfeff);border:1px solid var(--teal-line,#99f6e4)}
      .city-cta strong{display:block;margin-bottom:8px}
      .city-cta-meta{display:flex;flex-direction:column;gap:5px;font-size:13px;margin-top:10px}
      .city-cta-meta a{color:#125f47}
      .city-btn{display:inline-flex;margin-top:12px;background:var(--teal,#0d9488);color:#fff;padding:11px 16px;border-radius:10px}
      .city-btn:hover{background:var(--teal-hover,#0f766e)}
      .city-faq{margin-top:42px;border-top:1px solid var(--border,#e6e6e1);padding-top:28px}
      .city-faq-item + .city-faq-item{margin-top:16px}
      .city-band{margin-top:26px;padding:24px;border-radius:16px;background:var(--teal-soft,#ecfeff);border:1px solid var(--teal-line,#99f6e4)}
      .city-band h2{font-size:26px;margin:0 0 8px}
      .city-band p{margin:0;color:#0f766e}
      .city-local-context{margin:28px 0;padding:24px;border:1px solid var(--border,#e6e6e1);border-radius:16px;background:#f8fafc}
      .city-local-context h2{font-size:26px;margin:0 0 12px}
      .city-local-context-grid{display:grid;gap:12px;margin-top:18px}
      .city-local-context-item{padding:14px;border-radius:12px;background:#fff;border:1px solid var(--border,#e6e6e1)}
      .city-local-context-item strong{display:block;margin-bottom:6px;color:#071426}
      .city-conversion-form{margin-top:28px;padding:22px;border:1px solid var(--border,#e6e6e1);border-radius:16px;background:#fff}
      .city-conversion-form h2{font-size:26px;margin:0 0 8px}
      .city-conversion-form p{margin:0 0 14px;color:#484842}
      .city-conversion-links{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:14px}
      .city-conversion-links a{font-size:13px;font-weight:600}
      .city-form-grid{display:grid;gap:12px}
      .city-form-grid.two{grid-template-columns:1fr}
      .city-form-grid input,.city-form-grid textarea{width:100%;border:1px solid var(--border-strong,#c9c9c3);border-radius:10px;padding:10px 12px;font:inherit}
      .city-form-grid textarea{min-height:110px;resize:vertical}
      .city-form-grid label{display:grid;gap:5px;font-size:13px;color:#30302c}
      .city-form-consent{display:flex;gap:8px;align-items:flex-start;font-size:12px;color:#56564f}
      .city-form-consent input{margin-top:4px}
      .city-local-links{margin-top:30px;padding-top:24px;border-top:1px solid var(--border,#e6e6e1)}
      .city-local-links h2{font-size:24px;margin:0 0 10px}
      .city-link-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px 16px}
      .city-link-grid a{font-size:13px;color:#5f5f58}
      .city-link-grid a:hover{color:var(--teal,#0d9488)}
      @media(min-width:761px){.city-wrap{width:min(1240px,calc(100% - 48px))}}
      @media(min-width:761px){.city-form-grid.two{grid-template-columns:1fr 1fr}.city-link-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.city-local-context-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
      @media(min-width:961px){.city-main{grid-template-columns:minmax(0,1fr) 340px}.city-side-card{position:sticky}}

      /* City-only neutral palette; do not alter the global brand theme. */
      body.single-miasto{--bg:#fff;--bg-alt:#f8fafc;--surface-soft:#f8fafc;--sand:#f8fafc;--sand-d:#f1f5f9;--sand-b:#cbd5e1;--border:#e2e8f0;--border-strong:#cbd5e1;background:#fff}
      body.single-miasto .ups-footer{--uf-bg:#fff;--uf-border:#e2e8f0;--uf-border-strong:#cbd5e1;background:#fff}
      body.single-miasto .ups-footer :is(.ups-footer__trust-item,.ups-footer__mini-box,.ups-footer__chips a){background:#f8fafc}
      body.single-miasto .ups-footer :is(.ups-footer__links a,.ups-footer__contact a):hover{background:#f1f5f9}
      .city-page{background:#fff;color:#0f172a;font-family:var(--font-body,"DM Sans",sans-serif)}
      .city-page .city-hero{background:linear-gradient(135deg,#fff 20%,#f0fdfa 100%);border-color:#e2e8f0}
      .city-page .city-h1{margin:0;line-height:1.09;letter-spacing:-.045em;overflow-wrap:break-word}
      .city-page .city-lead{font-size:clamp(16px,1.55vw,18px);line-height:1.72;max-width:68ch;color:#334155}
      .city-page .city-pill{background:#fff;border-color:#cbd5e1;color:#334155;line-height:1.45}
      .city-page .city-main{align-items:start;gap:clamp(24px,3.5vw,48px);padding:clamp(32px,5vw,64px) 0 clamp(48px,6vw,80px)}
      .city-page .city-content{min-width:0;padding:0;border:0;border-radius:0;background:#fff;color:#334155;font-size:17px;line-height:1.8;overflow-wrap:break-word}
      .city-page .city-prose{min-width:0;max-width:76ch}
      .city-page .city-prose > :first-child{margin-top:0}
      .city-page .city-prose > :last-child{margin-bottom:0}
      .city-page .city-content :is(h2,h3,h4){font-family:var(--font-display,"Bricolage Grotesque",sans-serif);font-weight:750;color:#0f172a;letter-spacing:-.025em;line-height:1.25;text-wrap:pretty}
      .city-page .city-prose h2{font-size:clamp(25px,2.7vw,32px);margin:44px 0 16px}
      .city-page .city-prose h3{font-size:clamp(20px,2vw,24px);margin:28px 0 12px}
      .city-page .city-prose h4{font-size:19px;margin:24px 0 10px}
      .city-page .city-prose p{margin:0 0 18px}
      .city-page .city-prose strong{color:#0f172a;font-weight:700}
      .city-page .city-prose :is(ul,ol){margin:0 0 22px;padding-inline-start:1.5em;list-style-position:outside}
      .city-page .city-prose ul{list-style-type:disc}
      .city-page .city-prose ol{list-style-type:decimal}
      .city-page .city-prose li{margin:0 0 8px;padding-inline-start:3px}
      .city-page .city-prose li::marker{color:#0f766e}
      .city-page .city-prose li > :is(ul,ol){margin:8px 0}
      .city-page .city-prose :is(p,li,td,th) a,.city-page .city-conversion-links a{color:#0f766e;font-weight:600;text-decoration:underline;text-decoration-thickness:1px;text-underline-offset:3px;overflow-wrap:anywhere}
      .city-page .city-prose a:hover{color:#115e59}
      .city-page a:focus-visible{outline:2px solid #0f766e;outline-offset:3px}
      .city-page .city-prose :is(figure,img){max-width:100%}
      .city-page .city-prose img{height:auto;border-radius:12px}
      .city-page .city-prose blockquote{margin:24px 0;padding:16px 20px;background:#f8fafc;border-left:3px solid #0d9488}
      .city-page .city-prose blockquote p:last-child{margin-bottom:0}
      .city-page .city-prose .city-table-scroll{max-width:100%;overflow-x:auto;margin:24px 0 32px;border:1px solid #e2e8f0;border-radius:12px;background:#fff}
      .city-page .city-prose .wp-block-table{max-width:100%;overflow-x:auto;margin:24px 0}
      .city-page .city-prose table{width:100%;border-collapse:collapse;table-layout:auto;color:#334155;background:#fff;font-size:15px;line-height:1.55;text-align:left}
      .city-page .city-prose caption{text-align:left;padding:14px 16px;font-weight:700;color:#0f172a;background:#fff}
      .city-page .city-prose :is(th,td){padding:12px 16px;vertical-align:top;border-bottom:1px solid #e2e8f0;overflow-wrap:break-word;text-align:left}
      .city-page .city-prose thead th{background:#f1f5f9;color:#0f172a;font-weight:700}
      .city-page .city-prose tbody th{font-weight:600;color:#0f172a}
      .city-page .city-prose tbody tr:nth-child(even){background:#f8fafc}
      .city-page .city-prose tbody tr:last-child > *{border-bottom:0}
      .city-page .city-content .city-inline-cta{margin:24px 0 32px;padding:20px;background:#f0fdfa;border-color:#99f6e4;border-radius:14px}
      .city-page .city-content .city-inline-cta strong{font-size:16px;line-height:1.5;color:#134e4a}
      .city-page .city-inline-cta-links{gap:6px 16px;margin:12px 0}
      .city-page .city-inline-cta-links a{font-size:14px;color:#0f766e;text-decoration:underline;text-underline-offset:3px}
      .city-page .city-inline-cta-btn,.city-page .city-btn{min-height:44px;line-height:1.4;font-size:14px;font-weight:700;background:#0f766e;color:#fff;text-decoration:none}
      .city-page .city-side-card{min-width:0;align-self:start;background:#fff;border-color:#e2e8f0;box-shadow:0 8px 24px rgba(15,23,42,.045)}
      .city-page .city-side-title{font-size:23px;font-weight:700;line-height:1.25;color:#0f172a}
      .city-page .city-side-link,.city-page .city-link-grid a{font-size:14px;line-height:1.55;color:#334155;overflow-wrap:anywhere}
      .city-page .city-side-link:hover,.city-page .city-link-grid a:hover{color:#0f766e;text-decoration:underline}
      .city-page .city-cta,.city-page .city-band{background:#f0fdfa;border-color:#99f6e4}
      .city-page .city-band p{color:#334155}
      .city-page .city-local-context,.city-page .city-conversion-form,.city-page .city-band{margin:40px 0 0;padding:clamp(18px,3vw,28px)}
      .city-page .city-local-context{background:#f8fafc;border-color:#e2e8f0}
      .city-page .city-local-context-item{min-width:0;font-size:15px;line-height:1.65;border-color:#e2e8f0}
      .city-page .city-local-context-grid{grid-template-columns:repeat(auto-fit,minmax(min(100%,180px),1fr))}
      .city-page .city-conversion-form{background:#fff;border-color:#e2e8f0}
      .city-page .city-conversion-form .city-lead-form{background:#fff;border-color:#e2e8f0}
      .city-page .city-conversion-form .city-lead-form :is(input:not([type=checkbox]):not([type=hidden]),textarea,select){background:#fff;border-color:#cbd5e1;color:#0f172a}
      .city-page .city-local-links,.city-page .city-faq{margin-top:40px;padding-top:32px;border-color:#e2e8f0}
      .city-page .city-link-grid{grid-template-columns:repeat(auto-fit,minmax(min(100%,210px),1fr));gap:12px 18px}
      .city-page .city-faq-item{padding:16px 0;border-bottom:1px solid #e2e8f0}
      .city-page .city-faq-item + .city-faq-item{margin-top:0}
      .city-page .city-faq-item h3{margin:0 0 8px;font-size:20px}
      .city-page .city-faq-item p{margin:0}
      @media(max-width:760px){
        .city-page .city-hero{padding:40px 0 32px}
        .city-page .city-h1{font-size:clamp(30px,8.5vw,42px)}
        .city-page .city-content{font-size:16px;line-height:1.75}
        .city-page .city-prose h2{margin-top:32px}
        .city-page .city-prose :is(th,td){padding:10px 12px}
        .city-page .city-prose table{font-size:14px}
        .city-page .city-side-card{padding:18px}
      }

      /* Editorial data tables: native caption, explicit header and readable row hierarchy. */
      .city-page .city-prose .city-table-card{margin:28px 0 36px;border:1px solid #e2e8f0;border-radius:16px;background:#fff;box-shadow:0 6px 20px rgba(15,23,42,.045);overflow:hidden}
      .city-page .city-prose .city-table-card .city-table-scroll{margin:0;border:0;border-radius:0;overflow-x:auto;overscroll-behavior-x:contain;scrollbar-color:#94a3b8 #f1f5f9;scrollbar-width:thin}
      .city-page .city-prose .city-table-scroll:focus-visible{outline:2px solid #0f766e;outline-offset:-2px}
      .city-page .city-prose .city-table-card table{display:table;caption-side:top;width:100%;min-width:620px;table-layout:auto;border:0;border-collapse:separate;border-spacing:0;margin:0;background:#fff}
      .city-page .city-prose .city-table--two table{min-width:360px;table-layout:fixed}
      .city-page .city-prose .city-table--three table{min-width:520px;table-layout:fixed}
      .city-page .city-prose .city-table-card caption{display:table-caption;caption-side:top;text-align:left;background:#fff;padding:20px 20px 18px;border:0;font-family:var(--font-display,"Bricolage Grotesque",sans-serif);font-size:clamp(19px,2vw,23px);font-weight:750;line-height:1.3;letter-spacing:-.02em;color:#0f172a}
      .city-page .city-prose .city-table-subtitle{display:block;margin-top:6px;font-family:var(--font-body,"DM Sans",sans-serif);font-size:13px;font-weight:400;line-height:1.5;letter-spacing:0;color:#475569}
      .city-page .city-prose .city-table-card thead{display:table-header-group}
      .city-page .city-prose .city-table-card tbody{display:table-row-group}
      .city-page .city-prose .city-table-card tr{display:table-row}
      .city-page .city-prose .city-table-card :is(th,td){display:table-cell;border:0;border-bottom:1px solid #e2e8f0;padding:15px 20px;line-height:1.55;text-align:left;vertical-align:top;white-space:normal;overflow-wrap:anywhere}
      .city-page .city-prose .city-table-card thead th{background:#f0fdfa;border-top:1px solid #ccfbf1;border-bottom:1px solid #99f6e4;font-size:13px;font-weight:700;color:#134e4a}
      .city-page .city-prose .city-table-card tbody th{font-weight:700;color:#0f172a}
      .city-page .city-prose .city-table-card tbody tr:nth-child(even){background:#f8fafc}
      .city-page .city-prose .city-table-card tbody tr:last-child > *{border-bottom:0}
      .city-page .city-prose .city-table--two thead th:first-child{width:40%}
      .city-page .city-prose .city-table--three thead th:first-child{width:32%}
      .city-page .city-prose .city-table--three thead th:nth-child(2){width:38%}
      .city-page .city-prose .city-table--three tbody td:last-child{color:#0f766e;font-weight:600}
      .city-page .city-prose .city-table-hint{display:none;margin:0;padding:10px 16px;border-top:1px solid #e2e8f0;background:#f8fafc;color:#475569;font-size:12px;line-height:1.4}
      @media(max-width:760px){
        .city-page .city-prose .city-table-card{margin:22px 0 28px}
        .city-page .city-prose .city-table-card caption{padding:16px}
        .city-page .city-prose .city-table-card :is(th,td){padding:12px 14px}
        .city-page .city-prose .city-table--wide .city-table-hint{display:block}
      }
      @media(max-width:552px){.city-page .city-prose .city-table--three .city-table-hint{display:block}}
      @media(max-width:392px){.city-page .city-prose .city-table--two .city-table-hint{display:block}}
    </style>

    <main class="city-page">
      <section class="city-hero">
        <div class="city-wrap">
          <div class="city-hero-grid">
            <div class="city-hero-copy">
              <nav class="city-breadcrumbs" aria-label="Breadcrumb">
                <a href="<?php echo esc_url(home_url("/")); ?>">Strona główna</a> /
                <a href="<?php echo esc_url(home_url("/miasta/")); ?>">Miasta</a> /
                <span><?php echo esc_html($cityName); ?></span>
              </nav>
              <h1 class="city-h1">Marketing i strony WWW <?php echo esc_html($cityName); ?></h1>
              <p class="city-lead">
                Skuteczne pozyskiwanie klientów w mieście <?php echo esc_html($cityName); ?>:
                kampanie Meta i Google Ads, strony pod konwersję oraz wsparcie sprzedaży B2B i usług.
              </p>
              <div class="city-meta">
                <span class="city-pill">Województwo: <?php echo esc_html($voivodeship); ?></span>
                <span class="city-pill">Specjalizacja: <?php echo esc_html($marketAngle); ?></span>
                <span class="city-pill">Model: <?php echo esc_html($serviceFocus); ?></span>
              </div>
            </div>
            <aside class="city-hero-map">
              <div class="city-hero-map-visual">
                <svg viewBox="0 0 497 463" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                  <title>Mapa województw Polski</title>
                  <!-- GUGiK PRG boundaries via https://github.com/ppatrzyk/polska-geojson; simplified and reprojected.
MIT License

Copyright (c) 2019 Piotr Patrzyk

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
                  -->
                  <g fill="#94add6" stroke="#fff" stroke-width="2.6" stroke-linejoin="round" fill-rule="evenodd">
                  <path d="M264.6,308.4 L264.3,308.8 L264.3,309.9 L264.7,310.7 L265.4,311.1 L266.5,311.0 L267.2,310.0 L268.6,309.6 L269.4,308.9 L270.2,309.2 L271.5,311.3 L271.7,311.1 L272.9,311.2 L273.8,311.5 L273.6,312.4 L274.2,313.5 L274.8,313.8 L277.0,313.9 L277.6,313.3 L277.9,312.2 L280.1,313.4 L281.4,314.3 L281.7,315.3 L281.3,315.7 L281.4,316.7 L280.9,317.0 L279.7,315.9 L279.3,317.3 L280.2,318.5 L279.0,319.0 L277.6,320.7 L276.9,319.5 L276.6,320.1 L275.9,320.2 L275.9,320.9 L276.6,322.4 L276.3,322.7 L278.3,323.7 L279.4,323.2 L280.1,323.3 L281.0,323.0 L280.8,324.5 L282.7,324.8 L282.9,325.6 L283.9,325.4 L283.9,326.2 L283.4,326.5 L283.3,327.1 L282.6,327.5 L282.1,328.6 L283.2,329.1 L284.4,329.1 L284.7,329.8 L285.7,329.4 L286.3,329.6 L285.7,330.3 L285.2,331.3 L283.4,330.9 L282.9,331.6 L282.6,332.7 L282.4,333.3 L281.6,333.9 L281.3,334.6 L280.6,335.2 L279.9,335.0 L279.9,336.1 L282.4,336.0 L283.1,336.9 L284.4,337.0 L285.3,337.2 L285.8,339.3 L287.1,340.0 L287.6,339.9 L287.7,340.7 L288.3,341.1 L288.2,342.0 L287.1,341.8 L286.5,342.1 L285.3,342.3 L284.9,342.8 L285.4,343.5 L285.1,343.9 L283.0,344.3 L283.0,345.2 L281.7,345.0 L280.8,345.1 L280.4,344.5 L279.6,344.8 L279.0,344.5 L278.0,344.7 L277.2,344.5 L276.8,344.8 L275.7,344.2 L275.4,345.2 L274.6,345.3 L274.0,346.1 L274.3,346.7 L272.6,347.2 L272.1,346.8 L271.6,347.4 L271.1,347.6 L270.6,347.2 L269.8,347.3 L269.3,347.0 L268.4,346.9 L267.8,346.3 L266.9,346.6 L266.3,347.8 L265.4,348.1 L265.6,350.5 L264.1,350.0 L264.1,350.7 L265.2,351.2 L265.4,351.9 L265.1,353.0 L265.3,353.7 L264.1,353.8 L263.7,354.1 L262.0,353.1 L261.9,353.8 L261.5,354.5 L261.7,355.4 L260.7,356.4 L260.4,357.6 L259.4,358.3 L258.8,358.2 L257.6,358.7 L258.4,359.4 L260.9,360.7 L261.8,360.7 L262.6,361.3 L263.3,361.2 L261.4,363.1 L260.4,363.2 L259.5,363.9 L258.2,364.0 L258.3,364.6 L258.9,365.0 L258.6,365.9 L259.0,366.7 L258.0,367.5 L255.9,367.8 L255.0,367.5 L255.4,369.4 L254.5,369.6 L254.0,368.5 L252.8,371.1 L252.8,371.8 L252.3,372.9 L252.7,373.6 L251.4,373.5 L250.6,374.2 L250.2,373.9 L249.1,375.0 L249.2,375.4 L248.6,376.0 L248.9,376.4 L248.6,377.1 L247.8,377.8 L247.7,379.9 L248.0,381.1 L247.8,381.5 L246.8,382.2 L247.1,382.9 L247.7,383.2 L249.8,383.2 L250.1,382.6 L250.9,382.4 L251.0,384.7 L251.4,385.2 L251.4,387.3 L250.3,387.7 L249.6,388.9 L250.2,389.8 L251.5,389.2 L251.8,388.4 L253.4,388.9 L255.0,389.2 L255.9,390.0 L255.8,392.6 L256.6,394.2 L257.2,395.0 L257.2,395.6 L258.8,396.1 L259.6,395.6 L259.9,396.1 L260.6,396.2 L261.2,396.8 L262.2,396.0 L263.0,397.1 L263.4,398.3 L263.4,398.8 L262.6,399.4 L262.6,400.0 L261.7,400.7 L261.3,400.7 L260.8,401.6 L260.0,402.1 L260.7,402.7 L260.8,403.3 L261.6,403.2 L261.9,403.6 L263.2,403.0 L264.1,403.3 L264.4,404.0 L265.0,404.5 L264.5,405.5 L265.1,407.3 L264.5,408.1 L263.3,408.5 L263.4,409.1 L261.7,409.8 L261.6,410.7 L261.3,411.2 L259.9,411.7 L259.6,412.7 L259.7,413.4 L259.4,414.1 L258.7,414.1 L257.3,414.5 L257.2,414.0 L256.1,414.3 L255.6,414.1 L254.7,414.4 L254.6,414.8 L253.3,416.0 L253.4,416.7 L253.2,417.8 L252.6,418.8 L253.0,419.6 L252.6,420.8 L252.0,420.5 L251.5,420.9 L251.0,421.8 L251.5,423.4 L251.0,423.8 L250.6,423.4 L249.4,424.2 L248.6,423.7 L247.9,424.3 L247.2,424.2 L246.4,423.3 L245.5,423.1 L244.6,423.3 L244.2,424.5 L243.3,425.0 L241.5,424.6 L241.1,424.9 L240.7,424.3 L240.5,423.5 L241.4,422.8 L241.5,422.1 L241.0,421.4 L240.8,420.5 L240.2,420.3 L240.6,418.6 L240.1,418.2 L240.7,416.5 L239.2,415.4 L237.9,415.5 L237.1,415.7 L235.8,415.4 L234.5,415.4 L234.2,415.0 L234.6,414.2 L235.2,413.9 L235.3,413.0 L234.2,412.1 L233.8,409.6 L233.6,408.9 L233.6,408.1 L232.7,405.3 L232.9,403.7 L232.7,403.1 L231.5,402.6 L230.7,402.7 L230.1,403.3 L229.3,402.9 L228.6,402.8 L228.0,401.2 L227.3,401.0 L226.1,401.3 L225.4,400.9 L224.1,399.8 L224.2,399.5 L224.2,397.9 L223.6,397.4 L223.3,396.1 L222.7,395.0 L222.8,394.7 L222.5,393.7 L222.0,393.2 L222.0,392.5 L221.4,391.2 L222.3,390.4 L222.1,389.9 L223.0,389.5 L223.0,388.9 L221.5,388.3 L221.2,387.5 L221.9,385.8 L221.6,385.1 L220.9,384.8 L220.2,384.2 L219.7,386.2 L218.4,386.0 L217.3,385.5 L216.5,385.0 L216.3,384.5 L215.4,384.4 L214.8,384.0 L214.7,383.3 L212.9,383.6 L211.7,383.4 L211.1,382.8 L210.4,384.2 L210.0,384.1 L209.5,385.0 L208.4,384.4 L207.4,383.1 L207.6,382.3 L207.4,381.3 L205.3,380.8 L204.6,380.9 L204.0,378.7 L203.3,379.0 L202.8,378.5 L202.1,378.7 L202.2,379.6 L201.4,379.9 L201.1,379.2 L200.3,378.7 L199.7,379.0 L199.1,378.5 L198.5,377.4 L199.0,376.8 L198.2,376.1 L198.3,375.2 L197.2,374.9 L197.0,374.0 L196.4,374.1 L195.7,373.5 L196.8,371.8 L196.9,371.2 L197.5,370.1 L197.0,369.7 L197.3,368.6 L196.5,368.0 L197.2,366.5 L196.8,366.2 L197.8,365.2 L198.8,365.0 L199.3,365.7 L200.5,364.4 L201.1,364.6 L201.8,364.1 L202.9,364.2 L203.9,364.0 L206.0,363.8 L205.3,363.1 L205.4,362.2 L206.1,361.5 L207.1,361.6 L207.3,361.3 L208.8,360.8 L209.1,360.1 L209.7,360.1 L210.7,359.6 L211.4,359.6 L211.4,358.9 L212.1,359.1 L213.5,359.0 L214.5,359.5 L214.2,358.6 L214.5,358.0 L213.5,357.3 L213.6,356.3 L212.9,355.1 L212.9,354.5 L213.7,354.2 L213.0,352.7 L212.0,352.0 L211.2,351.2 L211.9,350.9 L212.1,349.4 L212.8,349.0 L212.9,348.5 L212.2,346.9 L211.8,346.7 L211.8,346.7 L211.8,346.7 L211.8,346.7 L211.6,346.0 L212.6,345.4 L212.6,344.4 L212.4,344.1 L212.5,343.2 L212.3,341.9 L212.5,341.5 L213.7,342.2 L215.2,341.7 L215.8,342.0 L215.7,343.1 L217.1,343.4 L217.4,342.5 L216.8,342.3 L216.5,341.1 L216.4,340.3 L215.5,340.3 L215.1,338.1 L214.7,337.8 L215.0,336.8 L217.0,336.2 L218.3,336.5 L219.8,337.0 L220.9,337.2 L221.5,336.5 L222.3,336.6 L223.1,336.2 L223.6,334.9 L222.7,335.0 L222.4,334.3 L222.9,332.2 L222.4,332.1 L222.2,331.5 L218.9,330.6 L218.9,329.5 L218.4,329.4 L218.7,328.1 L217.8,327.6 L218.0,325.7 L217.3,324.3 L218.4,323.9 L218.5,322.9 L220.4,322.5 L220.4,321.1 L221.4,321.5 L221.9,320.9 L220.4,320.3 L219.3,318.8 L219.0,317.5 L219.1,317.1 L221.0,316.6 L221.1,315.7 L220.8,314.6 L222.3,313.3 L223.6,313.1 L224.6,310.1 L225.5,308.5 L225.2,307.2 L224.3,307.2 L223.4,305.4 L223.9,304.6 L224.3,304.7 L225.2,304.3 L225.2,304.3 L225.9,304.2 L226.1,302.8 L225.5,301.5 L226.6,301.3 L227.4,300.7 L226.3,298.2 L226.3,297.6 L227.8,297.0 L229.2,296.8 L230.0,297.2 L231.5,296.9 L232.6,297.1 L233.3,296.7 L235.5,296.4 L236.5,297.4 L237.0,296.8 L237.3,295.8 L238.1,294.4 L238.8,294.8 L239.9,295.0 L240.6,295.7 L241.7,296.1 L242.6,296.8 L242.8,297.9 L244.5,298.7 L244.7,299.3 L245.5,299.2 L246.6,299.5 L247.1,299.9 L247.4,301.0 L248.0,302.0 L248.4,301.1 L249.2,301.2 L249.0,301.8 L249.8,301.9 L250.0,302.3 L252.0,303.0 L252.8,303.0 L253.6,302.7 L254.3,301.4 L253.7,300.0 L253.8,299.1 L254.5,299.5 L256.0,298.4 L257.5,298.4 L257.9,299.0 L257.3,299.8 L257.3,300.4 L257.8,300.9 L258.7,301.1 L259.4,301.6 L259.1,302.5 L260.0,302.4 L260.6,301.9 L261.3,302.4 L261.3,302.8 L261.9,303.8 L262.8,304.6 L263.1,306.2 L264.3,307.6 L264.6,308.4Z"/>
                  <path d="M185.0,291.6 L185.6,292.9 L187.6,293.2 L188.5,294.0 L189.5,293.7 L190.1,293.3 L191.1,293.6 L194.8,292.2 L195.9,291.9 L197.7,290.7 L198.0,289.2 L198.5,289.9 L199.2,290.0 L200.1,289.0 L200.6,288.7 L201.9,288.7 L202.0,289.3 L203.0,289.9 L204.6,290.1 L205.6,289.8 L206.5,289.8 L207.4,290.6 L207.9,290.7 L208.7,291.6 L209.2,291.7 L210.0,291.3 L211.0,291.5 L211.6,291.3 L212.4,292.1 L213.6,292.4 L214.2,292.3 L214.3,293.0 L215.9,293.1 L216.3,292.7 L216.6,293.9 L217.2,294.3 L218.0,294.3 L218.8,293.9 L218.9,293.3 L218.8,291.7 L219.0,291.2 L220.6,291.3 L221.3,292.8 L221.0,293.8 L221.9,295.0 L221.8,295.6 L223.2,295.7 L224.0,296.0 L224.0,296.1 L224.0,296.1 L224.0,296.1 L224.0,296.1 L224.6,297.1 L225.8,297.7 L226.3,297.6 L226.3,298.2 L227.4,300.7 L226.6,301.3 L225.5,301.5 L226.1,302.8 L225.9,304.2 L225.2,304.3 L225.2,304.3 L224.3,304.7 L223.9,304.6 L223.4,305.4 L224.3,307.2 L225.2,307.2 L225.5,308.5 L224.6,310.1 L223.6,313.1 L222.3,313.3 L220.8,314.6 L221.1,315.7 L221.0,316.6 L219.1,317.1 L219.0,317.5 L219.3,318.8 L220.4,320.3 L221.9,320.9 L221.4,321.5 L220.4,321.1 L220.4,322.5 L218.5,322.9 L218.4,323.9 L217.3,324.3 L218.0,325.7 L217.8,327.6 L218.7,328.1 L218.4,329.4 L218.9,329.5 L218.9,330.6 L222.2,331.5 L222.4,332.1 L222.9,332.2 L222.4,334.3 L222.7,335.0 L223.6,334.9 L223.1,336.2 L222.3,336.6 L221.5,336.5 L220.9,337.2 L219.8,337.0 L218.3,336.5 L217.0,336.2 L215.0,336.8 L214.7,337.8 L215.1,338.1 L215.5,340.3 L216.4,340.3 L216.5,341.1 L216.8,342.3 L217.4,342.5 L217.1,343.4 L215.7,343.1 L215.8,342.0 L215.2,341.7 L213.7,342.2 L212.5,341.5 L212.3,341.9 L212.5,343.2 L212.4,344.1 L212.6,344.4 L212.6,345.4 L211.6,346.0 L211.8,346.7 L211.8,346.7 L211.8,346.7 L211.8,346.7 L212.2,346.9 L212.9,348.5 L212.8,349.0 L212.1,349.4 L211.9,350.9 L211.2,351.2 L212.0,352.0 L213.0,352.7 L213.7,354.2 L212.9,354.5 L212.9,355.1 L213.6,356.3 L213.5,357.3 L214.5,358.0 L214.2,358.6 L214.5,359.5 L213.5,359.0 L212.1,359.1 L211.4,358.9 L211.4,359.6 L210.7,359.6 L209.7,360.1 L209.1,360.1 L208.8,360.8 L207.3,361.3 L207.1,361.6 L206.1,361.5 L205.4,362.2 L205.3,363.1 L206.0,363.8 L203.9,364.0 L202.9,364.2 L201.8,364.1 L201.1,364.6 L200.5,364.4 L199.3,365.7 L198.8,365.0 L197.8,365.2 L196.8,366.2 L197.2,366.5 L196.5,368.0 L197.3,368.6 L197.0,369.7 L197.5,370.1 L196.9,371.2 L196.8,371.8 L195.7,373.5 L194.3,374.4 L194.5,376.1 L195.2,375.6 L196.1,375.7 L196.4,376.4 L196.3,377.3 L195.7,377.7 L194.4,377.7 L193.6,377.4 L193.3,378.1 L192.8,377.9 L191.8,378.1 L190.3,380.2 L189.7,380.3 L188.5,380.1 L187.7,380.6 L185.7,379.0 L186.2,378.3 L185.8,377.7 L184.6,377.6 L184.0,377.1 L183.4,377.0 L183.0,375.8 L183.1,375.0 L182.4,373.9 L182.0,372.6 L181.1,371.7 L181.1,371.1 L179.9,369.8 L178.8,369.6 L178.5,370.6 L177.3,370.0 L176.7,368.5 L175.4,367.8 L174.5,366.3 L174.9,365.5 L176.1,365.5 L176.2,365.4 L176.2,365.4 L176.2,365.4 L176.2,365.4 L176.2,365.4 L176.2,365.4 L176.2,365.3 L176.3,365.3 L176.3,365.3 L176.3,365.3 L176.3,365.3 L176.3,365.3 L176.8,365.1 L176.9,365.1 L176.9,365.1 L178.0,364.9 L178.6,364.6 L178.7,364.5 L179.7,364.4 L180.2,363.6 L180.1,363.4 L180.1,363.3 L180.8,362.7 L181.3,363.0 L182.5,362.7 L182.4,361.6 L182.8,360.4 L181.4,359.3 L180.8,358.9 L181.0,357.5 L181.3,357.3 L181.8,356.2 L182.1,355.6 L181.0,355.3 L180.7,354.2 L180.3,353.8 L179.0,353.4 L179.1,355.4 L178.1,356.3 L178.0,356.5 L177.9,356.6 L177.7,356.7 L177.3,357.3 L175.7,358.1 L175.0,357.9 L174.8,357.8 L174.8,357.8 L174.8,357.8 L174.8,357.7 L174.8,357.6 L174.8,357.6 L174.8,357.6 L174.7,357.6 L174.7,357.6 L174.7,357.6 L174.7,357.6 L174.7,357.5 L174.7,357.5 L174.7,357.5 L174.7,357.5 L174.7,357.4 L174.6,357.3 L174.5,357.3 L174.5,357.3 L174.5,357.2 L174.2,357.2 L174.2,357.2 L174.2,357.2 L174.2,357.2 L174.2,357.2 L174.2,357.2 L173.9,357.3 L173.6,357.5 L173.6,357.5 L173.6,357.5 L173.2,357.8 L173.1,357.8 L172.4,357.9 L170.8,357.5 L169.8,357.5 L168.7,357.9 L168.0,357.8 L167.9,358.9 L167.0,359.3 L166.2,357.3 L165.6,357.6 L164.7,356.9 L163.6,356.9 L163.5,358.2 L162.8,358.3 L162.5,357.0 L162.8,356.5 L162.7,355.7 L163.0,354.9 L162.3,354.5 L162.4,353.8 L161.8,353.5 L161.0,354.0 L160.0,354.2 L158.7,353.4 L157.9,353.1 L156.9,352.1 L155.7,350.7 L155.6,349.8 L155.8,349.0 L154.9,348.7 L154.7,349.0 L152.9,349.4 L151.9,348.3 L151.4,348.4 L151.4,347.5 L150.1,347.4 L149.9,347.7 L148.6,347.4 L147.2,346.3 L146.7,346.5 L146.0,345.8 L145.2,346.4 L144.7,346.3 L143.5,345.3 L142.5,345.0 L141.6,344.1 L143.2,343.2 L143.5,341.7 L144.1,341.2 L144.7,341.1 L145.1,341.6 L144.9,342.1 L147.0,341.7 L147.0,342.9 L148.4,343.0 L149.0,342.1 L149.1,341.1 L148.8,339.9 L149.0,339.5 L149.4,339.2 L149.6,339.1 L149.0,337.4 L149.7,335.9 L150.6,335.3 L150.8,334.4 L151.6,333.5 L152.0,331.9 L155.0,331.3 L156.3,331.3 L157.4,331.6 L157.8,331.1 L157.7,330.4 L157.1,329.5 L157.3,329.0 L157.5,327.1 L157.3,326.4 L157.9,326.3 L158.5,325.6 L156.6,324.8 L156.6,323.9 L158.1,323.6 L159.3,321.5 L160.0,321.3 L160.3,320.8 L159.5,320.2 L158.8,319.4 L159.2,318.2 L160.0,317.8 L160.7,318.1 L162.6,318.4 L163.1,317.6 L162.8,317.0 L161.8,315.8 L162.5,314.8 L161.8,313.4 L162.7,313.3 L163.6,312.7 L164.5,312.5 L164.5,311.5 L165.0,311.5 L165.0,310.5 L163.7,309.7 L163.0,309.5 L163.8,308.2 L164.4,307.7 L165.4,308.2 L165.6,308.8 L166.8,308.3 L166.7,307.6 L167.1,306.5 L166.7,306.1 L166.8,304.4 L168.3,303.6 L168.6,304.1 L170.1,303.8 L170.3,303.1 L172.0,302.5 L172.1,301.7 L171.4,301.7 L170.7,301.0 L171.0,300.0 L171.7,298.6 L171.3,298.2 L171.4,296.8 L172.8,296.7 L174.0,295.7 L173.0,294.8 L173.8,293.4 L173.5,292.9 L173.7,292.0 L173.3,291.4 L172.2,290.9 L172.2,290.1 L174.6,289.0 L174.7,288.2 L175.2,287.4 L175.8,287.8 L175.8,288.5 L176.8,288.7 L178.2,289.6 L179.6,288.8 L180.4,288.5 L181.5,288.6 L182.2,287.6 L183.5,287.9 L184.2,287.1 L185.0,287.5 L185.8,287.1 L186.5,287.5 L186.9,288.7 L186.0,291.0 L185.0,291.6Z"/>
                  <path d="M138.8,100.5 L139.0,99.8 L139.6,98.9 L140.9,98.4 L141.2,99.6 L140.9,100.5 L141.1,101.1 L141.7,101.7 L142.0,102.7 L143.2,104.1 L143.1,104.9 L143.6,105.9 L143.6,106.6 L144.4,106.7 L145.1,106.1 L146.1,106.6 L146.3,107.5 L145.8,107.9 L146.6,108.8 L147.6,109.2 L149.2,108.6 L150.4,107.1 L152.9,108.0 L153.9,108.2 L154.3,107.9 L156.4,107.8 L157.8,108.0 L158.8,107.6 L159.6,107.8 L160.3,108.5 L160.9,108.5 L160.7,109.4 L161.6,109.4 L161.7,110.2 L162.9,110.9 L163.5,110.6 L164.6,110.6 L164.8,111.1 L164.4,112.8 L162.7,114.0 L162.6,114.0 L161.9,115.3 L160.2,116.4 L160.2,117.8 L159.7,118.5 L158.0,119.6 L158.2,120.4 L159.1,121.1 L160.2,121.6 L161.7,122.1 L162.1,122.0 L163.1,123.5 L163.3,124.3 L164.5,125.2 L165.0,126.5 L165.6,127.1 L166.2,127.0 L167.1,128.2 L166.4,129.1 L166.6,129.8 L165.9,130.2 L166.1,131.0 L165.5,131.3 L163.8,131.3 L163.5,131.6 L162.0,132.0 L161.8,132.3 L162.6,133.3 L162.2,134.6 L162.2,135.6 L162.7,136.1 L162.2,136.8 L164.8,137.6 L164.5,138.5 L163.0,138.7 L163.4,139.7 L163.2,142.0 L162.1,142.8 L161.6,142.7 L161.9,144.8 L162.2,145.4 L162.0,147.2 L161.2,147.2 L160.5,149.1 L161.3,149.5 L161.3,150.7 L163.7,150.7 L165.0,150.0 L165.8,150.9 L166.5,151.2 L168.0,152.5 L169.6,152.8 L170.0,153.9 L170.9,154.2 L170.5,155.0 L171.0,156.6 L170.9,157.5 L171.3,159.6 L170.7,159.8 L170.8,161.1 L170.6,161.6 L171.1,162.4 L170.3,163.7 L170.4,164.5 L169.9,165.1 L168.1,165.0 L167.8,164.2 L166.8,165.3 L166.0,165.3 L165.6,166.2 L165.6,166.9 L166.1,167.7 L167.1,167.8 L168.6,169.1 L169.5,169.4 L168.5,173.2 L170.1,172.9 L170.3,173.7 L170.9,173.7 L172.2,172.5 L172.8,172.6 L173.8,172.0 L174.0,171.3 L175.5,171.4 L176.2,171.1 L176.8,172.3 L176.4,173.3 L176.7,174.5 L176.3,175.1 L177.1,175.9 L178.6,175.6 L178.5,176.3 L178.8,177.5 L178.5,179.0 L180.1,178.4 L182.0,176.9 L180.4,176.3 L181.5,175.4 L182.6,175.5 L182.8,176.4 L183.3,176.6 L183.7,175.4 L185.0,175.9 L185.7,176.7 L186.6,177.2 L188.1,177.1 L189.7,178.7 L189.7,179.8 L189.3,180.4 L190.1,180.8 L191.5,181.0 L191.5,181.7 L192.2,181.6 L192.8,182.2 L193.6,181.5 L193.4,183.7 L193.9,183.8 L194.2,182.8 L195.1,182.8 L196.9,183.7 L197.1,183.0 L198.6,182.9 L199.2,184.1 L198.5,184.6 L198.4,185.4 L199.1,185.9 L200.0,185.7 L200.0,186.5 L200.7,186.6 L201.5,186.3 L203.8,187.0 L203.7,188.1 L204.6,188.3 L205.8,188.0 L206.5,188.4 L208.2,187.0 L208.2,187.0 L208.2,187.0 L208.2,187.0 L208.8,186.7 L210.6,185.3 L210.4,184.1 L211.0,183.7 L212.6,184.6 L212.1,186.7 L212.6,188.2 L212.5,188.7 L214.2,189.1 L214.3,188.6 L216.3,188.8 L216.2,187.7 L216.8,187.6 L217.7,186.8 L219.3,187.1 L219.3,187.1 L219.3,187.1 L219.3,187.1 L220.3,188.2 L220.9,188.2 L221.1,189.1 L221.5,189.7 L222.3,189.9 L223.0,189.8 L223.2,190.4 L224.1,190.4 L225.2,190.8 L225.3,191.9 L225.6,193.0 L226.5,192.9 L226.8,193.8 L226.7,194.6 L228.2,194.6 L229.2,196.2 L229.2,196.7 L230.2,197.7 L230.1,198.8 L230.3,199.8 L231.4,199.7 L231.8,199.1 L233.1,198.3 L234.2,199.0 L235.1,198.6 L235.8,197.7 L237.8,197.3 L237.8,196.3 L238.6,196.3 L238.2,194.7 L239.2,195.3 L239.9,196.3 L240.6,196.4 L241.1,197.4 L240.5,197.6 L242.4,198.5 L242.3,199.3 L243.4,199.9 L244.3,199.8 L244.2,200.9 L243.8,201.1 L243.9,202.4 L244.2,202.7 L243.7,204.6 L244.4,204.4 L246.6,204.6 L246.9,204.9 L247.0,206.7 L245.9,207.0 L246.0,208.2 L246.4,209.5 L245.8,210.0 L244.6,209.0 L243.0,208.8 L241.1,209.4 L240.3,209.5 L239.7,210.3 L238.7,210.0 L238.1,211.1 L238.0,213.0 L237.6,214.1 L237.8,215.1 L238.8,215.9 L239.3,215.5 L239.6,216.2 L239.4,217.2 L238.7,217.3 L238.4,219.2 L236.5,219.2 L235.6,218.6 L234.5,219.2 L234.4,219.8 L233.8,220.4 L233.0,220.2 L231.8,220.6 L230.7,220.1 L230.2,219.4 L228.8,220.3 L228.4,222.0 L228.4,222.8 L229.0,223.7 L228.9,224.3 L229.3,225.1 L228.5,225.7 L229.2,226.9 L229.2,227.7 L228.4,227.6 L228.8,228.5 L229.5,228.2 L229.7,230.1 L230.9,230.8 L230.0,232.4 L229.4,232.9 L229.4,233.6 L228.7,235.0 L227.5,235.6 L227.7,236.6 L227.3,237.2 L227.1,238.9 L225.2,238.6 L224.9,238.0 L224.3,238.3 L223.5,238.0 L222.6,236.9 L221.3,237.2 L220.4,237.2 L219.1,238.0 L218.5,237.4 L218.4,236.5 L217.3,236.8 L216.7,236.7 L216.3,237.7 L216.4,239.0 L215.5,238.5 L215.1,238.7 L215.4,240.7 L215.0,241.1 L213.7,241.3 L214.7,242.7 L214.8,243.3 L214.3,243.9 L214.0,244.8 L214.4,245.5 L214.2,246.2 L213.6,246.3 L213.8,248.2 L213.0,248.3 L211.5,249.7 L211.1,251.3 L212.3,251.9 L211.8,253.3 L211.0,253.4 L211.7,254.6 L211.7,254.6 L211.0,255.4 L210.9,255.9 L212.1,255.9 L211.8,256.6 L211.9,257.7 L211.6,258.7 L211.8,260.3 L211.3,260.5 L211.4,261.2 L212.3,262.2 L212.4,265.4 L211.4,266.1 L211.0,267.4 L210.4,268.1 L210.4,269.0 L209.7,269.8 L208.3,269.0 L206.3,268.3 L205.0,267.3 L203.1,268.1 L203.9,268.8 L204.2,269.5 L203.8,270.2 L203.9,271.2 L203.2,272.3 L201.2,271.9 L200.7,273.0 L200.5,274.3 L200.1,274.5 L198.8,274.2 L197.6,275.1 L198.5,275.8 L198.5,277.0 L199.5,278.1 L199.5,280.1 L199.0,280.4 L199.6,280.8 L199.0,281.4 L199.3,282.0 L200.1,281.3 L200.7,281.7 L201.4,281.7 L201.3,281.0 L202.2,281.1 L202.1,283.3 L202.4,283.7 L202.7,284.9 L202.6,285.0 L202.0,285.7 L201.9,287.2 L202.4,287.2 L201.9,288.7 L200.6,288.7 L200.1,289.0 L199.2,290.0 L198.5,289.9 L198.0,289.2 L197.7,290.7 L195.9,291.9 L194.8,292.2 L191.1,293.6 L190.1,293.3 L189.5,293.7 L188.5,294.0 L187.6,293.2 L185.6,292.9 L185.0,291.6 L186.0,291.0 L186.9,288.7 L186.5,287.5 L185.8,287.1 L185.0,287.5 L184.2,287.1 L183.4,286.8 L182.4,285.3 L182.2,283.9 L181.8,283.3 L181.7,282.2 L182.1,280.4 L182.1,279.7 L182.8,278.9 L182.3,278.1 L182.8,277.7 L181.7,276.7 L181.7,275.4 L181.0,274.9 L180.8,274.4 L180.1,274.4 L179.9,273.8 L180.4,273.5 L180.6,272.3 L181.1,272.3 L181.4,271.3 L182.2,271.4 L182.3,270.7 L182.0,270.2 L180.8,269.6 L180.3,269.9 L180.6,270.9 L179.8,271.3 L178.1,270.1 L177.8,270.5 L176.8,269.6 L176.1,269.8 L175.5,269.5 L175.0,270.6 L174.3,270.5 L173.4,270.9 L172.7,270.5 L172.0,269.6 L172.5,268.6 L172.4,268.0 L171.0,266.5 L171.4,265.9 L171.0,265.5 L171.2,264.8 L170.7,263.5 L171.1,262.5 L172.7,262.3 L173.5,261.3 L173.6,259.8 L173.5,258.5 L172.9,258.2 L172.7,257.2 L171.1,256.7 L171.0,255.6 L169.4,254.7 L168.7,254.0 L167.1,253.4 L166.1,253.6 L164.6,253.3 L163.3,252.7 L161.9,252.3 L160.0,252.6 L158.6,252.6 L157.6,253.4 L156.4,253.7 L156.0,255.1 L156.4,256.0 L156.2,256.7 L156.4,258.0 L155.1,257.8 L154.2,258.3 L152.1,258.6 L151.3,257.8 L149.2,258.5 L149.4,259.4 L148.9,259.7 L148.3,259.3 L147.0,259.5 L146.4,259.8 L145.2,259.7 L145.0,260.2 L144.2,260.2 L143.9,259.6 L142.6,259.6 L142.0,259.2 L141.0,257.6 L140.6,257.4 L138.9,257.5 L138.0,257.8 L137.4,257.7 L137.0,257.0 L136.2,256.6 L136.4,255.7 L135.1,254.9 L134.8,253.8 L135.3,253.6 L135.6,252.8 L135.0,252.5 L133.7,252.5 L132.9,252.3 L130.8,252.4 L130.9,251.7 L129.2,251.5 L128.7,250.9 L128.9,249.6 L130.4,248.8 L130.8,247.7 L129.7,247.3 L129.0,246.6 L128.8,245.6 L129.2,244.9 L128.5,244.5 L127.8,244.9 L126.9,244.8 L125.9,244.3 L125.7,243.0 L124.2,243.3 L124.2,242.2 L121.3,242.0 L119.6,241.6 L118.0,241.8 L116.7,241.3 L117.3,240.3 L117.0,239.1 L117.3,237.7 L116.3,237.7 L116.6,237.1 L115.2,236.2 L114.1,235.2 L114.9,234.4 L113.4,234.0 L111.5,233.0 L111.1,233.3 L110.2,232.9 L109.8,233.9 L109.1,234.8 L108.4,235.3 L107.7,235.1 L107.3,234.1 L106.2,234.2 L104.9,233.4 L103.2,233.3 L103.3,232.7 L104.7,231.0 L104.1,230.5 L104.3,229.6 L104.0,229.0 L104.6,228.8 L104.4,227.4 L103.9,225.9 L103.1,225.6 L101.8,226.6 L101.0,227.1 L98.5,226.7 L98.2,225.6 L96.8,225.4 L96.4,223.9 L96.4,222.7 L97.8,222.6 L97.7,221.4 L98.3,221.1 L97.5,220.5 L97.5,220.2 L96.6,219.5 L96.5,219.0 L95.2,218.5 L94.3,220.0 L93.2,219.4 L92.2,218.6 L91.3,218.7 L90.1,216.8 L90.8,216.6 L91.1,216.0 L92.1,215.3 L92.5,213.4 L92.4,212.8 L91.3,212.4 L91.4,211.7 L91.4,210.2 L90.8,209.0 L91.4,207.9 L91.0,205.2 L92.4,203.5 L92.2,202.5 L92.9,201.5 L92.4,200.2 L92.7,199.6 L92.4,198.3 L92.7,197.7 L92.7,196.8 L93.3,196.0 L93.1,195.1 L91.8,194.9 L91.4,194.5 L90.5,194.4 L89.8,193.1 L89.2,192.6 L88.7,191.3 L89.7,190.7 L90.1,191.2 L89.9,191.9 L90.7,192.3 L92.2,191.8 L92.1,191.4 L93.0,191.1 L92.9,189.4 L92.5,188.0 L91.7,186.8 L91.5,186.2 L90.1,184.8 L91.3,183.6 L90.0,181.9 L89.6,181.9 L89.8,180.7 L88.3,178.9 L87.7,177.9 L88.2,177.2 L88.2,176.5 L87.3,176.4 L87.6,175.3 L87.6,174.6 L89.1,174.1 L89.1,173.3 L88.5,172.0 L88.2,171.0 L89.0,170.9 L90.2,171.2 L92.5,170.5 L93.6,170.5 L94.2,169.9 L95.4,169.4 L95.6,168.3 L95.3,167.2 L94.5,165.2 L95.1,165.1 L94.9,163.9 L93.4,163.6 L92.9,163.0 L93.3,161.3 L94.2,160.9 L94.4,161.6 L95.7,160.7 L95.5,159.9 L96.2,159.9 L96.8,159.5 L96.7,158.9 L97.1,157.8 L96.5,156.4 L97.0,155.6 L97.1,153.8 L96.2,152.1 L96.2,151.0 L96.0,150.5 L95.8,148.6 L96.0,147.0 L95.9,145.6 L96.3,145.5 L98.5,146.1 L99.2,146.0 L100.2,146.5 L100.7,147.0 L102.1,147.6 L103.7,147.8 L104.7,147.6 L107.0,146.1 L109.1,145.7 L110.1,145.2 L111.1,144.9 L112.2,145.8 L113.3,145.9 L113.6,144.9 L115.5,142.4 L114.7,140.1 L115.3,139.9 L115.9,138.9 L116.8,138.7 L117.2,137.3 L116.8,136.6 L117.2,136.2 L118.4,137.3 L118.8,136.7 L119.4,136.9 L119.9,136.1 L120.9,135.7 L121.3,136.1 L122.0,136.2 L122.1,135.5 L123.0,134.5 L123.6,132.9 L123.4,132.3 L124.2,132.1 L124.9,131.2 L125.7,131.0 L126.6,131.2 L126.7,132.0 L127.3,132.7 L128.5,131.8 L128.4,130.8 L127.8,130.8 L128.5,128.8 L129.3,127.5 L129.8,127.3 L130.4,126.5 L132.3,125.8 L131.0,125.1 L130.6,124.2 L129.6,124.1 L128.0,122.2 L126.6,122.3 L125.6,121.6 L125.1,121.7 L124.5,121.2 L123.6,119.9 L123.3,120.6 L122.5,119.9 L122.0,119.2 L120.9,119.0 L120.9,118.3 L120.4,117.7 L119.4,114.0 L119.0,113.4 L119.8,111.3 L121.2,112.2 L121.6,112.7 L123.6,112.1 L125.9,112.0 L126.1,111.6 L128.9,111.6 L130.6,110.0 L131.4,109.0 L132.2,109.2 L132.6,108.4 L132.6,106.9 L133.0,106.2 L133.0,105.2 L133.6,105.3 L133.9,104.6 L133.5,104.0 L133.8,103.0 L134.5,101.4 L134.4,100.9 L137.1,100.4 L137.2,99.6 L137.7,99.4 L138.5,100.7 L138.8,100.5Z"/>
                  <path d="M115.9,38.3 L117.0,37.8 L118.8,34.8 L121.1,32.5 L121.9,32.0 L123.5,30.7 L127.5,29.2 L131.1,28.4 L131.6,28.5 L132.3,29.6 L133.5,30.8 L134.0,31.7 L133.5,32.4 L134.4,34.2 L134.7,34.8 L136.1,34.7 L137.8,36.5 L137.5,37.1 L138.4,38.5 L138.4,39.0 L137.6,40.2 L138.4,41.0 L138.4,41.5 L139.3,42.8 L138.9,43.4 L137.5,43.9 L136.8,43.7 L137.8,45.6 L137.6,46.2 L137.6,46.2 L137.6,46.2 L137.6,46.7 L137.1,47.7 L138.3,48.0 L139.1,48.5 L139.5,49.9 L139.6,52.1 L138.0,53.2 L137.3,53.2 L135.8,53.7 L135.2,53.6 L133.1,54.8 L132.3,55.8 L132.6,56.6 L133.2,56.5 L133.3,58.5 L134.1,58.9 L134.4,60.0 L135.2,60.3 L135.2,61.8 L135.4,62.9 L136.1,62.8 L136.7,63.2 L136.1,64.4 L136.1,66.3 L137.1,66.5 L137.0,67.4 L136.4,67.8 L136.4,69.1 L135.8,69.9 L135.7,70.5 L136.5,71.8 L136.6,72.6 L138.5,72.2 L138.7,73.3 L140.0,73.8 L140.3,74.9 L140.1,76.3 L141.7,77.8 L142.9,78.7 L143.7,78.8 L144.4,79.4 L145.2,79.4 L144.9,81.3 L143.9,81.7 L141.6,81.6 L139.9,82.1 L139.6,83.4 L139.9,83.9 L140.1,85.0 L141.0,85.6 L141.7,85.8 L141.7,87.3 L142.2,87.5 L143.4,87.3 L142.7,89.2 L142.2,89.3 L141.3,90.4 L139.2,91.5 L140.2,93.6 L140.3,96.0 L140.9,98.4 L139.6,98.9 L139.0,99.8 L138.8,100.5 L138.5,100.7 L137.7,99.4 L137.2,99.6 L137.1,100.4 L134.4,100.9 L134.5,101.4 L133.8,103.0 L133.5,104.0 L133.9,104.6 L133.6,105.3 L133.0,105.2 L133.0,106.2 L132.6,106.9 L132.6,108.4 L132.2,109.2 L131.4,109.0 L130.6,110.0 L128.9,111.6 L126.1,111.6 L125.9,112.0 L123.6,112.1 L121.6,112.7 L121.2,112.2 L119.8,111.3 L119.0,113.4 L119.4,114.0 L120.4,117.7 L120.9,118.3 L120.9,119.0 L122.0,119.2 L122.5,119.9 L123.3,120.6 L123.6,119.9 L124.5,121.2 L125.1,121.7 L125.6,121.6 L126.6,122.3 L128.0,122.2 L129.6,124.1 L130.6,124.2 L131.0,125.1 L132.3,125.8 L130.4,126.5 L129.8,127.3 L129.3,127.5 L128.5,128.8 L127.8,130.8 L128.4,130.8 L128.5,131.8 L127.3,132.7 L126.7,132.0 L126.6,131.2 L125.7,131.0 L124.9,131.2 L124.2,132.1 L123.4,132.3 L123.6,132.9 L123.0,134.5 L122.1,135.5 L122.0,136.2 L121.3,136.1 L120.9,135.7 L119.9,136.1 L119.4,136.9 L118.8,136.7 L118.4,137.3 L117.2,136.2 L116.8,136.6 L117.2,137.3 L116.8,138.7 L115.9,138.9 L115.3,139.9 L114.7,140.1 L115.5,142.4 L113.6,144.9 L113.3,145.9 L112.2,145.8 L111.1,144.9 L110.1,145.2 L109.1,145.7 L107.0,146.1 L104.7,147.6 L103.7,147.8 L102.1,147.6 L100.7,147.0 L100.2,146.5 L99.2,146.0 L98.5,146.1 L96.3,145.5 L97.8,143.1 L97.8,141.8 L97.3,141.6 L97.1,140.2 L93.9,139.5 L93.8,139.2 L92.3,139.6 L92.1,139.4 L90.8,139.9 L91.3,141.5 L89.0,142.2 L89.5,143.7 L88.1,144.2 L88.8,145.2 L87.9,145.4 L86.9,147.1 L87.4,147.5 L87.0,148.7 L85.7,149.4 L84.6,149.6 L83.3,148.8 L81.8,149.5 L80.7,150.4 L79.7,150.0 L79.0,149.8 L78.3,150.1 L77.6,149.7 L76.7,147.8 L76.3,147.6 L75.6,148.3 L73.4,148.9 L72.4,149.6 L71.4,149.5 L70.2,150.0 L69.5,150.6 L68.6,151.1 L67.8,152.8 L66.9,153.1 L63.9,151.9 L63.7,152.9 L64.1,153.8 L64.6,154.3 L66.0,156.4 L63.8,157.4 L53.5,161.7 L53.3,161.2 L52.4,160.7 L51.9,159.3 L50.8,159.7 L49.6,160.8 L49.2,160.3 L49.0,159.0 L48.0,158.6 L46.1,157.2 L45.5,157.6 L44.8,159.4 L45.8,159.8 L46.2,160.4 L45.7,161.1 L45.0,161.3 L44.7,160.4 L43.6,160.4 L44.0,161.4 L43.1,162.1 L43.6,163.1 L43.6,164.7 L42.9,165.2 L42.8,165.7 L41.1,166.1 L40.9,166.7 L40.0,167.7 L40.4,168.8 L40.1,171.3 L40.1,171.9 L39.4,173.0 L38.1,174.3 L37.9,175.7 L37.2,176.2 L36.7,177.0 L35.2,176.8 L33.7,176.1 L33.8,175.6 L32.7,174.8 L30.0,176.8 L29.1,177.4 L26.3,176.0 L24.3,174.5 L24.0,173.7 L23.0,173.2 L22.4,172.0 L21.9,171.6 L21.5,170.8 L20.5,169.7 L20.0,168.9 L19.4,168.6 L19.0,167.8 L17.5,166.9 L16.7,166.7 L15.5,166.0 L13.1,163.6 L12.5,162.7 L9.4,162.2 L8.3,161.8 L8.0,160.5 L9.7,158.1 L9.8,157.3 L9.4,156.3 L9.3,155.1 L8.9,153.8 L9.0,153.0 L8.7,152.4 L9.0,151.7 L9.8,151.4 L10.2,150.7 L12.1,149.6 L13.4,149.2 L14.5,148.5 L16.1,147.2 L17.1,146.0 L18.3,145.1 L19.0,144.2 L19.6,142.7 L19.9,141.3 L19.8,140.7 L20.2,140.1 L20.7,137.8 L20.0,136.9 L19.6,135.5 L20.0,134.3 L20.2,133.2 L21.5,132.6 L21.7,131.6 L22.7,130.4 L23.0,129.6 L23.6,128.8 L23.4,127.7 L22.3,127.5 L22.0,125.6 L21.6,125.0 L22.0,123.8 L21.5,122.1 L20.8,121.6 L21.0,120.4 L20.7,119.0 L20.0,117.4 L19.8,115.6 L19.9,113.7 L19.3,113.7 L18.9,110.7 L17.8,110.1 L17.2,108.5 L17.2,107.6 L16.7,107.0 L16.6,106.3 L17.1,105.4 L17.0,104.3 L17.3,101.3 L17.0,101.3 L15.7,100.1 L15.7,98.2 L15.1,97.6 L15.6,97.0 L14.9,95.2 L15.2,91.7 L14.9,91.0 L15.7,89.5 L12.3,82.4 L12.4,81.2 L12.2,79.7 L11.9,79.2 L12.0,78.5 L12.9,77.6 L14.0,77.9 L15.1,77.9 L15.1,77.1 L15.5,76.9 L16.5,77.4 L16.6,78.2 L18.6,78.8 L19.6,78.8 L21.7,78.2 L22.6,77.8 L23.9,77.0 L26.0,74.9 L27.7,74.4 L29.3,73.5 L33.0,72.2 L34.1,71.9 L35.8,71.1 L37.0,70.3 L39.6,69.5 L46.7,67.4 L52.9,64.8 L54.6,64.4 L56.6,63.6 L61.9,61.7 L63.8,60.8 L69.1,59.8 L70.7,59.4 L73.1,59.4 L75.2,58.7 L76.6,57.7 L78.3,57.7 L80.9,57.0 L81.8,56.8 L84.6,56.1 L86.5,55.4 L88.9,54.3 L90.8,53.6 L92.1,53.4 L95.0,53.1 L96.8,52.8 L99.5,52.3 L103.0,51.1 L105.5,49.7 L107.9,47.9 L110.6,45.3 L113.1,42.7 L115.2,39.9 L116.0,38.5 L115.9,38.3Z"/>
                  <path d="M277.9,312.2 L279.2,311.3 L280.3,308.9 L280.9,309.0 L282.0,308.4 L282.8,306.9 L282.5,305.9 L281.9,305.1 L281.9,304.4 L282.7,302.3 L283.5,301.6 L283.3,300.8 L283.7,300.3 L283.5,299.6 L284.0,298.3 L284.6,298.3 L285.1,299.7 L285.2,299.8 L287.0,300.0 L287.0,301.1 L287.6,300.9 L288.1,301.8 L287.9,302.5 L290.9,304.8 L291.3,304.1 L291.7,304.0 L291.9,302.9 L291.6,302.3 L292.3,301.2 L292.5,299.9 L292.4,297.8 L292.7,297.5 L292.2,296.7 L289.2,296.3 L289.4,295.9 L289.7,294.2 L289.5,292.8 L289.7,291.7 L289.5,291.2 L291.3,289.3 L289.8,287.8 L291.3,286.6 L292.6,286.4 L293.3,287.0 L295.8,287.4 L298.5,286.9 L298.6,286.1 L299.9,286.6 L300.3,285.9 L300.5,286.7 L301.2,286.5 L301.5,286.0 L302.1,286.1 L302.6,285.0 L302.1,284.9 L302.3,283.8 L301.7,283.1 L301.8,282.3 L302.5,282.1 L302.8,282.7 L303.4,283.2 L304.9,283.3 L306.1,282.4 L307.0,282.7 L307.3,283.5 L307.8,283.8 L308.3,283.1 L308.1,281.9 L308.6,281.8 L308.8,281.1 L307.6,278.4 L307.6,278.3 L307.5,278.3 L307.5,278.1 L308.5,277.5 L309.9,277.4 L310.8,275.9 L312.3,276.2 L312.3,276.5 L314.4,276.5 L314.8,277.5 L314.6,278.0 L313.7,278.4 L314.1,279.9 L315.8,281.3 L315.2,282.0 L315.7,282.6 L316.2,284.3 L317.2,284.2 L317.8,283.2 L319.3,283.3 L319.9,284.3 L321.1,285.3 L322.4,285.8 L323.6,286.9 L323.7,288.0 L322.9,288.6 L323.1,289.8 L323.5,290.5 L324.5,288.7 L327.1,289.9 L326.8,290.4 L327.6,290.8 L327.8,290.2 L329.1,290.8 L329.5,289.8 L328.9,289.6 L329.5,288.3 L330.8,288.9 L332.3,290.1 L334.2,287.0 L336.5,288.4 L336.4,288.6 L338.1,289.7 L337.7,290.3 L338.0,290.9 L339.2,290.4 L339.1,289.8 L340.8,289.9 L341.4,288.6 L341.6,286.7 L341.4,285.7 L343.3,286.5 L343.2,287.1 L343.8,287.5 L343.6,288.2 L342.7,289.3 L342.4,290.0 L344.2,291.1 L344.5,292.1 L345.4,292.3 L345.7,294.1 L345.3,294.3 L345.4,295.8 L355.1,295.4 L355.0,296.9 L355.8,296.9 L356.4,297.5 L356.4,298.8 L357.4,299.4 L358.7,299.7 L360.3,300.9 L362.3,299.5 L361.4,299.2 L362.7,297.6 L363.4,297.4 L364.1,297.6 L364.2,298.3 L364.9,297.7 L366.0,297.3 L366.4,297.5 L367.5,296.9 L368.8,296.1 L369.2,296.6 L370.5,295.9 L371.2,296.9 L371.9,298.7 L372.6,298.7 L373.1,298.3 L374.1,299.1 L375.7,297.5 L376.0,296.7 L376.6,296.4 L377.5,297.4 L377.7,298.3 L377.4,298.9 L376.7,299.4 L376.6,300.2 L377.4,301.2 L377.5,303.0 L377.3,303.6 L377.4,304.7 L377.0,305.4 L377.0,306.9 L377.9,309.2 L378.0,310.9 L378.7,312.8 L378.4,313.9 L378.7,314.6 L379.6,315.6 L379.7,316.2 L379.5,317.0 L379.4,318.3 L378.4,320.3 L378.5,322.2 L377.9,323.3 L377.7,324.9 L377.3,325.7 L375.8,326.4 L376.1,326.8 L375.4,328.2 L375.5,329.1 L374.6,328.9 L373.9,328.3 L373.5,329.2 L372.7,329.1 L372.0,330.5 L370.4,331.8 L370.1,332.5 L369.6,334.6 L369.1,335.4 L368.0,336.8 L367.0,338.6 L366.4,338.9 L364.6,338.7 L363.6,339.1 L361.4,340.4 L359.8,340.7 L359.3,341.4 L359.7,342.6 L359.3,343.6 L357.3,344.9 L355.7,344.8 L353.0,346.2 L352.4,346.9 L351.5,347.2 L351.5,348.4 L350.4,348.6 L350.1,349.5 L349.1,349.9 L348.6,351.2 L348.1,351.3 L347.1,351.7 L347.0,352.7 L346.5,353.3 L346.0,353.0 L346.0,352.3 L345.0,351.4 L343.9,351.6 L343.5,352.6 L342.0,352.8 L341.6,353.6 L341.0,354.3 L340.4,354.4 L339.8,354.1 L338.7,354.5 L337.8,354.4 L336.7,354.5 L335.9,354.3 L335.4,354.6 L334.8,355.7 L332.9,355.1 L332.1,356.0 L331.1,355.9 L330.7,357.2 L329.8,357.5 L328.7,356.4 L327.9,356.4 L327.4,356.8 L327.0,357.8 L326.2,358.2 L325.0,359.4 L325.1,360.7 L323.9,361.3 L323.8,361.8 L323.0,361.8 L322.7,362.8 L321.3,362.9 L320.7,362.4 L320.4,362.4 L320.0,363.2 L319.1,362.9 L318.5,363.1 L317.5,364.2 L317.1,363.2 L315.9,363.4 L315.6,363.9 L314.6,364.0 L313.6,363.8 L312.6,363.2 L311.0,363.4 L310.0,363.9 L309.7,362.3 L308.5,362.6 L308.0,362.0 L308.0,361.1 L308.6,360.7 L309.1,360.0 L307.7,359.5 L306.7,358.4 L306.0,358.0 L306.5,357.3 L307.4,357.0 L307.1,356.4 L306.3,356.2 L306.5,355.1 L306.0,355.2 L305.8,354.1 L304.5,353.8 L304.1,353.4 L304.6,352.8 L304.1,352.0 L305.7,351.6 L306.2,350.8 L305.6,350.4 L305.4,349.1 L304.7,348.7 L304.6,348.0 L304.1,347.5 L304.0,346.4 L302.8,345.8 L302.5,344.7 L303.1,343.0 L301.9,342.1 L300.9,341.9 L300.6,341.0 L299.8,340.6 L298.3,340.9 L298.0,341.4 L296.3,341.0 L295.7,341.4 L294.5,341.0 L293.7,339.5 L292.6,339.3 L291.9,340.2 L291.3,339.6 L290.4,339.0 L288.9,338.8 L287.6,339.9 L287.1,340.0 L285.8,339.3 L285.3,337.2 L284.4,337.0 L283.1,336.9 L282.4,336.0 L279.9,336.1 L279.9,335.0 L280.6,335.2 L281.3,334.6 L281.6,333.9 L282.4,333.3 L282.6,332.7 L282.9,331.6 L283.4,330.9 L285.2,331.3 L285.7,330.3 L286.3,329.6 L285.7,329.4 L284.7,329.8 L284.4,329.1 L283.2,329.1 L282.1,328.6 L282.6,327.5 L283.3,327.1 L283.4,326.5 L283.9,326.2 L283.9,325.4 L282.9,325.6 L282.7,324.8 L280.8,324.5 L281.0,323.0 L280.1,323.3 L279.4,323.2 L278.3,323.7 L276.3,322.7 L276.6,322.4 L275.9,320.9 L275.9,320.2 L276.6,320.1 L276.9,319.5 L277.6,320.7 L279.0,319.0 L280.2,318.5 L279.3,317.3 L279.7,315.9 L280.9,317.0 L281.4,316.7 L281.3,315.7 L281.7,315.3 L281.4,314.3 L280.1,313.4 L277.9,312.2Z"/>
                  <path d="M226.5,95.8 L227.1,95.1 L228.0,96.1 L229.1,96.3 L229.6,95.6 L231.9,94.9 L231.1,96.8 L230.2,97.9 L229.5,99.3 L229.6,100.3 L230.6,102.4 L231.2,102.2 L233.7,102.4 L234.5,102.6 L236.8,102.8 L237.5,103.2 L238.0,104.3 L238.4,103.4 L239.3,103.6 L241.4,103.5 L241.5,103.1 L242.9,103.4 L244.4,103.0 L246.7,103.0 L248.2,103.6 L249.6,104.1 L250.8,104.3 L251.6,105.0 L251.1,106.7 L252.0,106.7 L252.3,107.4 L252.2,108.5 L251.1,109.5 L251.9,110.6 L252.9,111.2 L252.8,111.9 L253.4,112.8 L252.9,113.4 L252.9,114.0 L253.6,114.0 L253.9,115.4 L254.3,115.6 L253.8,117.2 L254.5,118.3 L256.0,118.9 L256.7,118.2 L256.8,117.2 L257.9,117.7 L260.3,117.2 L260.8,118.2 L261.5,118.7 L262.2,119.7 L263.4,119.9 L262.8,120.8 L264.0,121.1 L264.4,120.4 L265.2,120.3 L265.3,121.1 L267.0,120.3 L266.9,121.2 L267.3,121.7 L267.0,122.7 L267.0,123.4 L270.2,123.3 L270.6,122.8 L271.4,122.8 L272.8,122.3 L274.3,122.9 L275.2,122.9 L275.0,124.8 L275.1,125.2 L276.5,125.5 L276.2,126.6 L276.5,127.3 L275.8,128.2 L274.8,128.9 L275.2,129.4 L275.1,130.6 L277.7,131.2 L277.8,132.5 L277.6,134.2 L278.1,134.6 L278.2,135.6 L278.6,137.1 L277.2,137.6 L276.2,138.4 L276.1,139.4 L275.3,139.5 L274.1,140.6 L272.7,140.5 L272.7,141.6 L273.0,141.9 L273.1,142.9 L273.0,144.5 L273.3,145.8 L274.0,145.8 L274.6,146.2 L274.8,147.3 L274.5,148.6 L274.2,149.2 L274.8,151.4 L274.3,152.1 L273.5,151.7 L273.2,151.0 L272.6,151.0 L272.0,150.4 L271.4,150.5 L270.3,150.0 L269.3,149.8 L268.9,150.3 L270.2,151.5 L270.0,152.0 L268.0,152.6 L267.4,153.6 L265.4,153.3 L265.3,153.0 L263.4,153.4 L263.7,154.4 L264.4,155.1 L264.1,155.8 L265.4,157.4 L265.7,158.0 L266.5,158.8 L264.8,159.0 L264.3,159.7 L264.7,160.1 L263.4,161.2 L262.6,160.9 L262.3,161.5 L263.0,162.9 L263.8,163.7 L264.4,163.7 L264.3,164.5 L264.7,165.1 L265.8,165.7 L266.4,166.9 L265.9,167.9 L266.9,168.0 L267.0,168.4 L266.1,168.8 L266.2,170.5 L267.3,171.4 L265.7,171.1 L265.7,170.6 L264.4,170.1 L263.9,169.7 L263.2,169.9 L263.2,170.8 L263.5,171.3 L263.0,172.2 L263.0,173.1 L262.7,173.9 L262.0,173.8 L261.9,174.7 L261.4,175.9 L260.3,176.2 L259.7,177.2 L261.3,177.8 L262.5,178.5 L262.2,179.4 L261.0,180.1 L261.0,181.1 L260.2,181.7 L258.4,181.9 L258.9,184.3 L257.5,184.8 L259.3,185.4 L260.6,185.5 L260.7,186.3 L259.9,186.5 L259.0,187.9 L259.2,188.4 L258.3,188.5 L258.0,189.5 L257.4,190.9 L256.4,191.2 L255.7,190.8 L254.9,190.9 L254.5,191.4 L254.6,191.9 L255.8,192.1 L256.3,192.8 L256.1,193.8 L255.8,194.2 L255.9,195.2 L255.1,195.1 L254.7,195.9 L252.8,197.6 L252.1,197.3 L252.1,198.2 L250.7,198.6 L249.7,199.2 L249.2,198.7 L248.0,199.0 L246.9,198.7 L246.0,199.0 L244.9,199.8 L244.3,199.8 L243.4,199.9 L242.3,199.3 L242.4,198.5 L240.5,197.6 L241.1,197.4 L240.6,196.4 L239.9,196.3 L239.2,195.3 L238.2,194.7 L238.6,196.3 L237.8,196.3 L237.8,197.3 L235.8,197.7 L235.1,198.6 L234.2,199.0 L233.1,198.3 L231.8,199.1 L231.4,199.7 L230.3,199.8 L230.1,198.8 L230.2,197.7 L229.2,196.7 L229.2,196.2 L228.2,194.6 L226.7,194.6 L226.8,193.8 L226.5,192.9 L225.6,193.0 L225.3,191.9 L225.2,190.8 L224.1,190.4 L223.2,190.4 L223.0,189.8 L222.3,189.9 L221.5,189.7 L221.1,189.1 L220.9,188.2 L220.3,188.2 L219.3,187.1 L219.3,187.1 L219.3,187.1 L219.3,187.1 L217.7,186.8 L216.8,187.6 L216.2,187.7 L216.3,188.8 L214.3,188.6 L214.2,189.1 L212.5,188.7 L212.6,188.2 L212.1,186.7 L212.6,184.6 L211.0,183.7 L210.4,184.1 L210.6,185.3 L208.8,186.7 L208.2,187.0 L208.2,187.0 L208.2,187.0 L208.2,187.0 L206.5,188.4 L205.8,188.0 L204.6,188.3 L203.7,188.1 L203.8,187.0 L201.5,186.3 L200.7,186.6 L200.0,186.5 L200.0,185.7 L199.1,185.9 L198.4,185.4 L198.5,184.6 L199.2,184.1 L198.6,182.9 L197.1,183.0 L196.9,183.7 L195.1,182.8 L194.2,182.8 L193.9,183.8 L193.4,183.7 L193.6,181.5 L192.8,182.2 L192.2,181.6 L191.5,181.7 L191.5,181.0 L190.1,180.8 L189.3,180.4 L189.7,179.8 L189.7,178.7 L188.1,177.1 L186.6,177.2 L185.7,176.7 L185.0,175.9 L183.7,175.4 L183.3,176.6 L182.8,176.4 L182.6,175.5 L181.5,175.4 L180.4,176.3 L182.0,176.9 L180.1,178.4 L178.5,179.0 L178.8,177.5 L178.5,176.3 L178.6,175.6 L177.1,175.9 L176.3,175.1 L176.7,174.5 L176.4,173.3 L176.8,172.3 L176.2,171.1 L175.5,171.4 L174.0,171.3 L173.8,172.0 L172.8,172.6 L172.2,172.5 L170.9,173.7 L170.3,173.7 L170.1,172.9 L168.5,173.2 L169.5,169.4 L168.6,169.1 L167.1,167.8 L166.1,167.7 L165.6,166.9 L165.6,166.2 L166.0,165.3 L166.8,165.3 L167.8,164.2 L168.1,165.0 L169.9,165.1 L170.4,164.5 L170.3,163.7 L171.1,162.4 L170.6,161.6 L170.8,161.1 L170.7,159.8 L171.3,159.6 L170.9,157.5 L171.0,156.6 L170.5,155.0 L170.9,154.2 L170.0,153.9 L169.6,152.8 L168.0,152.5 L166.5,151.2 L165.8,150.9 L165.0,150.0 L163.7,150.7 L161.3,150.7 L161.3,149.5 L160.5,149.1 L161.2,147.2 L162.0,147.2 L162.2,145.4 L161.9,144.8 L161.6,142.7 L162.1,142.8 L163.2,142.0 L163.4,139.7 L163.0,138.7 L164.5,138.5 L164.8,137.6 L162.2,136.8 L162.7,136.1 L162.2,135.6 L162.2,134.6 L162.6,133.3 L161.8,132.3 L162.0,132.0 L163.5,131.6 L163.8,131.3 L165.5,131.3 L166.1,131.0 L165.9,130.2 L166.6,129.8 L166.4,129.1 L167.1,128.2 L166.2,127.0 L165.6,127.1 L165.0,126.5 L164.5,125.2 L163.3,124.3 L163.1,123.5 L162.1,122.0 L161.7,122.1 L160.2,121.6 L159.1,121.1 L158.2,120.4 L158.0,119.6 L159.7,118.5 L160.2,117.8 L160.2,116.4 L161.9,115.3 L162.6,114.0 L162.7,114.0 L164.4,112.8 L164.8,111.1 L165.0,109.8 L165.4,109.4 L165.4,108.6 L165.8,107.8 L166.3,107.6 L166.2,106.7 L165.9,105.8 L166.1,105.4 L166.3,104.1 L166.6,103.6 L167.7,102.6 L169.3,102.4 L170.5,101.7 L171.2,102.2 L171.4,103.1 L172.1,104.2 L173.9,104.4 L175.1,103.0 L176.1,103.7 L177.3,104.1 L177.6,104.9 L178.4,104.9 L178.4,104.0 L177.6,103.8 L177.6,103.2 L178.1,102.7 L179.0,103.3 L179.9,103.1 L181.1,103.3 L181.3,102.4 L180.5,101.7 L180.2,100.0 L181.2,99.0 L181.3,97.9 L181.7,97.5 L181.6,96.8 L182.2,96.7 L182.8,96.0 L183.1,95.1 L184.4,96.7 L184.8,96.6 L186.1,95.6 L187.0,96.0 L187.5,95.6 L188.2,94.5 L189.1,94.3 L188.7,92.9 L188.1,91.5 L187.3,90.9 L187.7,90.5 L189.3,91.4 L190.8,91.3 L191.7,91.5 L191.8,92.1 L192.6,92.1 L193.4,92.7 L193.6,93.2 L195.7,93.1 L196.3,92.3 L196.8,90.4 L197.4,89.4 L197.5,88.9 L199.2,89.7 L199.4,90.6 L200.3,89.7 L201.3,89.8 L201.5,91.0 L202.2,91.6 L203.4,90.7 L203.9,92.0 L204.6,92.0 L206.3,91.5 L207.2,92.6 L207.2,93.8 L206.2,94.2 L206.7,95.0 L208.1,95.1 L210.7,95.7 L211.0,96.0 L214.2,95.7 L214.1,96.0 L216.5,95.4 L216.9,94.8 L217.8,95.1 L218.7,94.8 L219.2,95.7 L218.7,96.5 L219.5,96.7 L219.4,97.5 L220.0,97.6 L220.5,98.4 L221.9,98.3 L222.2,97.1 L223.3,97.3 L224.3,97.0 L225.5,95.6 L226.5,95.8Z"/>
                  <path d="M382.5,142.7 L381.9,143.4 L381.8,143.4 L381.1,144.0 L379.9,144.2 L379.3,143.8 L379.3,143.2 L378.5,142.7 L376.9,140.0 L375.7,140.1 L374.4,140.0 L373.4,140.3 L371.4,138.1 L371.3,137.3 L372.0,136.0 L371.0,135.1 L370.7,134.3 L370.7,133.8 L370.2,132.7 L370.5,132.1 L369.9,131.2 L369.8,130.4 L368.5,129.0 L368.1,128.3 L369.6,128.6 L369.7,127.4 L369.0,127.0 L369.2,126.3 L370.0,124.9 L371.7,124.7 L372.2,125.4 L373.3,124.7 L372.1,124.2 L371.3,123.0 L370.2,122.9 L369.4,121.9 L369.8,121.5 L370.7,120.9 L370.9,120.5 L369.4,120.2 L369.8,119.7 L369.3,118.3 L368.1,117.5 L367.9,117.0 L368.0,115.9 L367.2,115.3 L367.3,114.2 L367.0,114.0 L367.1,112.2 L367.5,111.9 L369.1,111.8 L371.0,110.9 L373.1,111.2 L374.1,111.7 L376.0,112.0 L377.4,112.9 L379.2,113.5 L380.3,113.4 L380.8,113.0 L381.4,111.5 L381.3,110.9 L382.4,109.7 L382.9,109.5 L383.8,109.7 L385.5,108.7 L386.5,109.3 L386.8,110.3 L387.5,110.6 L388.0,109.8 L388.5,108.5 L389.6,107.6 L390.6,107.9 L392.2,107.2 L395.4,105.3 L396.1,104.4 L397.0,103.8 L397.5,103.0 L399.1,101.9 L399.7,100.9 L400.4,100.9 L401.6,100.1 L403.0,99.1 L403.0,98.7 L405.3,97.6 L406.3,96.5 L407.4,97.2 L407.7,97.9 L408.5,97.6 L408.7,96.7 L408.4,95.8 L409.6,95.0 L410.7,95.1 L413.2,93.4 L414.1,93.0 L414.7,93.7 L415.3,93.6 L415.6,92.6 L416.1,91.8 L418.2,90.7 L419.5,90.3 L420.9,87.1 L420.8,85.7 L420.9,84.9 L422.4,83.6 L423.4,82.4 L423.7,81.0 L423.2,80.0 L423.6,78.6 L422.1,76.4 L420.3,75.4 L420.1,74.6 L419.3,73.8 L419.2,72.0 L418.7,71.7 L418.2,70.5 L418.2,69.9 L416.2,68.4 L414.9,68.1 L414.7,67.0 L415.0,65.8 L416.5,65.0 L416.6,63.8 L415.8,63.4 L414.9,61.6 L413.8,61.2 L412.3,61.5 L411.7,60.4 L411.8,59.8 L410.9,59.7 L410.9,59.2 L410.1,57.8 L409.2,56.8 L409.7,55.6 L411.4,54.5 L411.6,53.0 L412.6,52.8 L413.8,51.8 L415.3,52.2 L416.0,51.5 L417.0,50.0 L419.3,51.0 L421.0,50.3 L421.8,49.8 L422.1,49.2 L422.9,49.9 L423.9,49.1 L423.7,48.7 L424.1,47.1 L423.6,46.5 L424.6,46.0 L424.7,45.3 L424.0,44.2 L425.0,43.7 L426.2,40.9 L427.1,40.6 L428.7,40.7 L428.6,41.4 L430.9,41.7 L431.6,42.0 L433.2,42.2 L433.3,42.6 L434.5,42.8 L433.7,44.4 L434.5,44.8 L436.3,45.3 L437.1,46.0 L436.5,46.5 L436.0,47.9 L438.5,49.2 L440.8,47.9 L441.7,48.4 L441.4,49.3 L442.5,49.8 L443.7,50.0 L444.2,50.8 L444.8,51.2 L445.3,52.1 L446.0,52.4 L447.6,52.1 L447.9,52.4 L450.2,52.8 L450.7,53.3 L451.2,54.2 L452.3,54.6 L452.4,54.7 L452.4,54.7 L452.9,55.5 L453.5,57.0 L454.1,57.4 L454.4,58.5 L456.1,58.6 L456.2,59.2 L456.8,59.6 L457.3,60.3 L457.8,61.9 L457.7,63.1 L458.1,63.4 L458.4,64.5 L458.9,65.2 L458.9,66.1 L459.4,67.0 L459.2,69.7 L458.8,69.9 L458.5,71.2 L458.1,71.5 L457.1,72.2 L457.6,74.1 L458.3,74.7 L458.7,75.4 L458.6,78.5 L459.2,81.9 L459.2,82.6 L460.3,83.1 L460.6,85.0 L460.3,85.4 L460.2,87.7 L460.4,89.9 L462.0,91.7 L462.1,92.9 L462.1,94.6 L463.0,97.9 L464.1,103.0 L465.3,106.9 L465.7,108.7 L466.0,109.4 L468.0,115.3 L469.4,118.7 L470.7,122.8 L472.4,127.7 L473.3,130.0 L475.1,130.9 L475.4,131.8 L475.2,132.6 L475.7,133.8 L477.0,135.2 L477.6,135.5 L478.1,136.7 L478.0,137.7 L477.1,138.6 L476.6,139.8 L476.7,140.7 L475.9,142.4 L476.2,142.6 L476.4,143.6 L477.3,144.2 L477.2,144.8 L478.0,145.7 L477.7,146.2 L478.4,146.8 L478.8,149.6 L478.7,150.5 L479.4,151.9 L478.0,153.4 L478.0,156.0 L478.5,156.4 L478.4,159.8 L478.4,161.7 L479.1,163.0 L479.1,170.7 L477.2,172.4 L476.9,173.2 L476.2,173.2 L470.3,178.2 L469.1,178.7 L468.5,178.2 L464.8,178.8 L462.2,179.8 L460.0,180.9 L458.2,181.9 L456.4,183.2 L456.0,183.9 L454.9,184.7 L453.6,186.1 L451.1,189.4 L449.0,192.7 L448.9,193.6 L448.4,194.4 L447.4,195.2 L445.5,198.2 L444.4,200.0 L442.6,203.6 L441.5,203.9 L440.2,203.3 L439.6,202.4 L439.2,202.4 L437.5,203.7 L436.5,203.4 L436.3,203.0 L436.9,202.0 L436.2,200.4 L435.9,200.1 L434.7,199.8 L433.9,199.9 L432.9,198.7 L432.4,197.5 L432.0,197.1 L430.2,196.6 L428.8,197.4 L426.9,197.8 L424.2,196.7 L422.9,196.8 L422.2,195.9 L421.5,196.1 L421.2,197.0 L420.6,196.7 L420.7,195.9 L419.9,195.3 L419.0,195.2 L418.0,195.8 L417.6,195.2 L417.0,195.5 L416.2,195.2 L415.5,195.6 L415.2,194.8 L413.3,194.4 L412.8,193.4 L412.0,192.8 L412.0,191.7 L412.6,189.8 L412.2,189.5 L411.4,189.9 L411.2,189.1 L410.6,188.4 L410.5,187.8 L411.3,185.4 L409.6,183.4 L409.2,182.4 L408.6,182.0 L407.9,180.4 L406.1,179.4 L405.6,178.6 L407.7,177.4 L407.6,176.3 L408.2,176.3 L408.5,175.6 L407.6,175.2 L407.3,174.4 L407.4,173.1 L406.9,171.8 L407.5,169.6 L407.7,167.0 L408.1,166.3 L407.8,164.9 L407.1,164.7 L404.7,164.4 L404.6,165.1 L404.1,165.7 L403.0,165.9 L402.6,165.7 L402.4,166.7 L402.7,167.3 L402.3,167.8 L400.6,168.0 L400.4,167.5 L400.6,166.4 L400.5,165.3 L399.7,165.7 L398.6,162.8 L398.1,162.1 L398.1,161.4 L399.1,161.0 L399.2,161.6 L400.7,161.0 L400.8,159.6 L399.8,159.9 L399.7,159.1 L400.0,158.6 L399.6,158.0 L399.0,157.5 L398.1,158.2 L396.5,157.7 L396.0,157.7 L396.1,159.8 L394.4,159.6 L393.2,160.4 L392.9,160.1 L392.0,160.7 L391.5,160.3 L390.9,159.3 L390.1,160.5 L388.9,160.2 L387.8,160.1 L387.9,159.4 L387.4,158.9 L387.7,157.5 L387.2,157.4 L386.4,158.2 L386.2,157.5 L386.4,156.1 L385.6,154.9 L386.6,154.5 L387.4,153.7 L386.6,153.2 L386.8,152.5 L386.0,151.6 L386.1,150.8 L385.0,150.3 L384.3,150.3 L383.8,149.7 L382.6,149.8 L380.9,148.7 L380.4,148.1 L380.1,147.0 L380.9,146.6 L381.1,145.2 L381.8,145.7 L383.2,145.8 L383.1,145.2 L382.3,145.2 L382.4,144.1 L383.2,143.2 L382.5,142.7Z"/>
                  <path d="M118.0,241.8 L119.6,241.6 L121.3,242.0 L124.2,242.2 L124.2,243.3 L125.7,243.0 L125.9,244.3 L126.9,244.8 L127.8,244.9 L128.5,244.5 L129.2,244.9 L128.8,245.6 L129.0,246.6 L129.7,247.3 L130.8,247.7 L130.4,248.8 L128.9,249.6 L128.7,250.9 L129.2,251.5 L130.9,251.7 L130.8,252.4 L132.9,252.3 L133.7,252.5 L135.0,252.5 L135.6,252.8 L135.3,253.6 L134.8,253.8 L135.1,254.9 L136.4,255.7 L136.2,256.6 L137.0,257.0 L137.4,257.7 L138.0,257.8 L138.9,257.5 L140.6,257.4 L141.0,257.6 L142.0,259.2 L142.6,259.6 L143.9,259.6 L144.2,260.2 L145.0,260.2 L145.2,259.7 L146.4,259.8 L147.0,259.5 L148.3,259.3 L148.9,259.7 L149.4,259.4 L149.2,258.5 L151.3,257.8 L152.1,258.6 L154.2,258.3 L155.1,257.8 L156.4,258.0 L156.2,256.7 L156.4,256.0 L156.0,255.1 L156.4,253.7 L157.6,253.4 L158.6,252.6 L160.0,252.6 L161.9,252.3 L163.3,252.7 L164.6,253.3 L166.1,253.6 L167.1,253.4 L168.7,254.0 L169.4,254.7 L171.0,255.6 L171.1,256.7 L172.7,257.2 L172.9,258.2 L173.5,258.5 L173.6,259.8 L173.5,261.3 L172.7,262.3 L171.1,262.5 L170.7,263.5 L171.2,264.8 L171.0,265.5 L171.4,265.9 L171.0,266.5 L172.4,268.0 L172.5,268.6 L172.0,269.6 L172.7,270.5 L173.4,270.9 L174.3,270.5 L175.0,270.6 L175.5,269.5 L176.1,269.8 L176.8,269.6 L177.8,270.5 L178.1,270.1 L179.8,271.3 L180.6,270.9 L180.3,269.9 L180.8,269.6 L182.0,270.2 L182.3,270.7 L182.2,271.4 L181.4,271.3 L181.1,272.3 L180.6,272.3 L180.4,273.5 L179.9,273.8 L180.1,274.4 L180.8,274.4 L181.0,274.9 L181.7,275.4 L181.7,276.7 L182.8,277.7 L182.3,278.1 L182.8,278.9 L182.1,279.7 L182.1,280.4 L181.7,282.2 L181.8,283.3 L182.2,283.9 L182.4,285.3 L183.4,286.8 L184.2,287.1 L183.5,287.9 L182.2,287.6 L181.5,288.6 L180.4,288.5 L179.6,288.8 L178.2,289.6 L176.8,288.7 L175.8,288.5 L175.8,287.8 L175.2,287.4 L174.7,288.2 L174.6,289.0 L172.2,290.1 L172.2,290.9 L173.3,291.4 L173.7,292.0 L173.5,292.9 L173.8,293.4 L173.0,294.8 L174.0,295.7 L172.8,296.7 L171.4,296.8 L171.3,298.2 L171.7,298.6 L171.0,300.0 L170.7,301.0 L171.4,301.7 L172.1,301.7 L172.0,302.5 L170.3,303.1 L170.1,303.8 L168.6,304.1 L168.3,303.6 L166.8,304.4 L166.7,306.1 L167.1,306.5 L166.7,307.6 L166.8,308.3 L165.6,308.8 L165.4,308.2 L164.4,307.7 L163.8,308.2 L163.0,309.5 L163.7,309.7 L165.0,310.5 L165.0,311.5 L164.5,311.5 L164.5,312.5 L163.6,312.7 L162.7,313.3 L161.8,313.4 L162.5,314.8 L161.8,315.8 L162.8,317.0 L163.1,317.6 L162.6,318.4 L160.7,318.1 L160.0,317.8 L159.2,318.2 L158.8,319.4 L159.5,320.2 L160.3,320.8 L160.0,321.3 L159.3,321.5 L158.1,323.6 L156.6,323.9 L156.6,324.8 L158.5,325.6 L157.9,326.3 L157.3,326.4 L157.5,327.1 L157.3,329.0 L157.1,329.5 L157.7,330.4 L157.8,331.1 L157.4,331.6 L156.3,331.3 L155.0,331.3 L152.0,331.9 L151.6,333.5 L150.8,334.4 L150.6,335.3 L149.7,335.9 L149.0,337.4 L149.6,339.1 L149.4,339.2 L149.0,339.5 L148.8,339.9 L149.1,341.1 L149.0,342.1 L148.4,343.0 L147.0,342.9 L147.0,341.7 L144.9,342.1 L145.1,341.6 L144.7,341.1 L144.1,341.2 L143.5,341.7 L143.2,343.2 L141.6,344.1 L140.3,344.7 L140.3,345.1 L139.4,347.0 L139.9,347.7 L141.6,348.6 L142.2,350.3 L142.0,350.7 L142.7,352.0 L143.0,352.2 L142.9,353.2 L143.2,354.0 L143.9,354.6 L146.2,355.4 L147.0,357.2 L146.8,358.1 L146.1,358.9 L146.6,359.9 L147.4,360.9 L146.8,361.7 L146.0,362.0 L145.7,361.2 L145.1,360.4 L144.9,359.8 L144.3,360.2 L144.4,360.8 L143.9,361.5 L142.5,361.5 L142.1,361.2 L140.7,362.0 L140.4,363.2 L139.9,363.4 L138.7,362.7 L138.2,363.3 L136.9,364.0 L136.5,364.7 L136.3,365.8 L135.6,367.4 L134.6,368.1 L133.9,368.2 L133.1,369.6 L132.8,370.5 L131.8,371.2 L130.6,370.6 L128.8,369.9 L127.5,368.9 L127.0,368.2 L126.6,367.8 L125.9,367.6 L126.2,366.9 L125.0,366.0 L124.8,365.2 L125.1,364.7 L124.7,363.5 L125.0,362.8 L124.4,360.9 L123.9,360.2 L123.1,360.2 L121.9,358.6 L120.5,356.5 L120.4,355.9 L119.6,355.2 L118.8,353.7 L118.8,353.7 L117.7,353.7 L117.2,354.1 L116.4,353.3 L116.1,352.3 L115.4,351.4 L115.7,350.4 L115.4,349.5 L114.2,349.6 L113.7,349.3 L112.6,349.3 L111.8,350.4 L110.9,349.5 L110.7,348.4 L109.5,346.8 L108.7,347.4 L108.1,345.8 L107.6,345.7 L107.6,344.7 L107.9,344.2 L108.6,343.9 L109.2,343.3 L110.3,342.7 L110.0,342.4 L110.9,341.5 L112.2,341.8 L112.5,340.9 L113.0,340.7 L113.0,339.8 L113.4,339.6 L114.6,340.6 L115.4,340.1 L116.0,339.0 L117.3,337.9 L116.6,337.0 L117.8,336.6 L117.4,335.2 L118.5,334.8 L119.4,334.1 L119.3,333.6 L118.4,332.1 L117.4,331.2 L117.0,331.1 L115.5,328.9 L114.9,328.9 L114.5,327.9 L113.5,327.5 L112.7,328.0 L111.6,327.9 L111.5,327.4 L110.5,327.4 L109.3,327.1 L108.7,328.7 L108.6,329.8 L107.6,330.2 L107.6,330.2 L107.1,330.5 L106.2,329.1 L105.2,328.5 L104.8,328.7 L104.1,327.9 L103.0,327.7 L102.6,328.5 L101.3,329.5 L101.0,330.1 L100.9,331.6 L100.1,331.5 L99.5,332.3 L98.4,332.1 L97.4,331.4 L98.0,330.8 L98.8,330.4 L98.1,329.1 L98.3,328.1 L97.6,326.6 L97.5,326.1 L96.5,325.5 L95.1,325.9 L94.9,325.9 L94.9,325.9 L94.8,325.9 L94.7,326.0 L92.7,326.8 L92.3,327.1 L91.4,326.9 L91.5,326.4 L90.9,324.4 L89.9,323.0 L90.0,321.6 L89.1,320.7 L88.0,320.9 L87.5,321.7 L86.5,321.6 L85.3,322.1 L84.8,321.9 L83.9,322.1 L83.1,321.6 L82.9,320.9 L81.7,320.3 L81.2,320.3 L79.9,319.9 L79.6,319.4 L78.9,319.0 L77.8,318.9 L77.1,319.1 L76.3,318.7 L75.2,319.0 L74.6,318.0 L73.8,318.2 L73.1,318.0 L71.7,316.7 L71.1,316.5 L70.3,317.0 L70.0,317.8 L68.9,319.1 L68.0,319.0 L68.0,318.1 L67.6,317.1 L68.0,316.4 L68.0,315.6 L67.7,314.4 L67.0,313.3 L64.9,312.5 L63.4,310.3 L63.0,308.7 L62.9,308.2 L62.9,307.1 L64.0,306.0 L64.1,305.5 L63.2,304.8 L63.2,303.5 L62.0,303.5 L61.4,302.3 L60.5,302.3 L60.1,302.6 L60.1,302.5 L59.8,302.6 L59.1,303.4 L58.6,303.1 L58.3,300.4 L57.0,300.9 L56.5,302.0 L56.6,302.6 L56.1,302.6 L55.9,302.5 L55.8,302.4 L55.5,302.5 L55.3,302.6 L55.2,302.6 L55.0,302.4 L54.9,302.3 L54.9,302.3 L54.9,302.3 L54.9,302.3 L54.9,302.3 L54.9,302.3 L54.9,302.2 L54.9,302.2 L54.8,302.1 L54.8,302.1 L54.8,302.1 L54.8,302.1 L54.8,302.1 L54.8,302.0 L54.7,301.8 L54.8,301.6 L55.1,301.4 L55.1,301.4 L55.1,301.4 L55.1,301.4 L55.1,301.4 L55.1,301.4 L55.2,301.4 L55.2,301.3 L55.2,301.2 L55.2,301.2 L55.2,301.2 L55.2,301.1 L55.0,301.1 L55.0,301.1 L55.0,301.0 L55.0,301.0 L55.0,301.0 L55.0,301.0 L55.0,301.0 L55.0,301.0 L55.0,301.0 L54.9,301.0 L54.6,301.0 L54.6,301.0 L53.8,300.9 L53.8,300.8 L53.7,300.7 L52.8,300.2 L52.7,301.0 L51.9,301.0 L51.5,300.2 L51.1,300.2 L50.8,300.4 L50.8,300.4 L50.8,300.4 L50.8,300.4 L50.8,300.4 L50.8,300.5 L50.8,300.6 L50.7,300.7 L50.7,300.7 L50.7,300.7 L50.7,300.7 L50.4,300.9 L49.4,301.1 L48.5,302.7 L49.7,303.4 L49.6,303.4 L49.8,303.3 L49.8,303.3 L49.9,303.3 L50.0,303.2 L50.0,303.2 L50.3,303.0 L50.3,303.0 L50.9,304.5 L50.6,304.8 L50.8,305.8 L50.6,306.7 L49.6,307.9 L50.0,308.6 L49.9,309.5 L50.2,312.0 L49.4,312.0 L48.1,312.5 L47.1,311.9 L45.3,311.4 L45.1,312.1 L44.1,311.3 L43.5,311.8 L42.4,312.0 L41.6,311.8 L41.4,310.7 L42.5,309.5 L42.9,308.8 L43.6,308.3 L44.1,307.2 L45.1,306.5 L44.9,305.7 L45.5,304.3 L46.1,303.9 L46.2,302.8 L47.1,301.0 L47.0,300.3 L47.5,299.8 L47.8,298.3 L48.4,298.1 L48.5,296.5 L49.1,296.0 L49.3,295.0 L49.2,293.2 L50.0,292.5 L49.6,291.1 L50.1,290.4 L49.7,289.5 L50.5,287.9 L50.3,287.3 L50.9,285.0 L50.8,284.5 L51.9,283.2 L51.2,282.8 L51.8,282.2 L51.6,279.4 L50.7,278.9 L50.5,277.7 L49.7,277.1 L49.0,275.7 L48.8,274.0 L50.5,274.0 L50.5,273.3 L50.9,271.8 L53.5,271.5 L54.0,270.6 L54.1,269.3 L55.4,269.1 L56.3,268.4 L57.3,268.0 L59.0,266.4 L59.8,266.8 L59.7,267.5 L60.1,267.9 L61.7,267.7 L62.2,267.5 L63.9,268.9 L64.4,269.7 L65.7,269.9 L65.8,269.0 L66.5,267.7 L66.1,267.3 L66.3,265.6 L68.2,265.9 L67.7,264.2 L67.9,263.0 L68.5,262.2 L68.6,261.4 L69.1,261.3 L69.6,262.0 L71.0,261.5 L72.3,261.9 L72.6,262.5 L73.4,262.6 L73.5,261.5 L78.6,265.9 L78.2,267.3 L78.9,267.6 L80.0,266.4 L80.0,265.8 L80.5,265.2 L81.5,265.7 L82.1,264.5 L82.1,263.7 L82.6,262.7 L83.7,262.7 L84.3,262.3 L84.2,261.8 L84.3,260.3 L83.8,260.1 L83.9,259.3 L84.8,259.6 L85.7,259.3 L87.0,258.3 L89.6,258.0 L89.2,255.7 L90.1,254.3 L89.9,253.1 L90.3,252.5 L91.0,252.2 L91.1,251.6 L90.5,251.3 L90.1,250.4 L90.5,249.7 L91.6,248.9 L91.7,247.1 L92.5,246.7 L92.9,246.0 L93.9,246.3 L94.5,246.0 L94.7,245.4 L95.8,245.7 L96.2,245.0 L96.9,244.6 L96.6,243.1 L97.5,243.0 L97.2,242.3 L96.7,242.0 L96.4,241.3 L97.8,240.3 L98.9,240.5 L99.7,241.0 L99.7,241.8 L101.5,242.3 L102.4,243.1 L104.4,243.8 L106.0,243.7 L106.6,243.4 L105.7,247.4 L106.3,247.6 L107.0,247.1 L108.5,247.3 L109.0,248.4 L109.5,249.2 L110.1,249.3 L110.7,250.2 L111.0,249.5 L112.4,249.1 L112.6,248.4 L113.7,248.1 L114.7,247.6 L114.9,247.1 L115.8,246.1 L115.4,245.5 L116.2,244.2 L116.1,243.5 L116.5,242.9 L117.0,243.1 L117.6,242.7 L118.0,241.8Z"/>
                  <path d="M412.1,351.2 L412.4,351.2 L413.2,351.0 L414.0,351.3 L414.6,351.8 L416.0,351.2 L416.8,351.1 L416.9,351.1 L416.8,351.1 L416.9,351.1 L416.9,351.1 L417.5,351.3 L417.8,351.3 L418.1,351.4 L418.1,351.4 L418.1,351.4 L418.2,351.4 L418.2,351.4 L418.3,351.3 L419.0,351.2 L419.0,352.1 L418.1,352.5 L415.8,353.0 L415.1,353.6 L415.2,354.7 L415.2,354.7 L415.2,354.7 L415.2,354.7 L415.7,354.9 L416.7,355.3 L416.9,355.1 L418.3,355.7 L419.0,355.6 L419.7,356.0 L420.0,356.2 L420.6,355.9 L421.4,355.4 L422.1,355.3 L423.3,355.5 L425.5,355.4 L427.1,356.0 L428.3,355.5 L431.4,356.2 L433.3,356.0 L434.9,356.6 L436.6,355.1 L437.2,353.8 L438.3,353.3 L442.8,348.2 L444.5,349.1 L446.1,350.6 L447.4,349.7 L448.7,349.4 L449.3,348.5 L451.0,347.7 L451.2,347.1 L452.6,347.5 L451.8,347.9 L451.6,348.8 L452.4,349.2 L452.0,349.7 L452.2,350.3 L453.5,350.7 L453.8,351.6 L455.0,352.1 L453.5,355.1 L454.6,355.4 L456.0,355.3 L456.6,356.0 L458.0,355.6 L458.9,356.3 L458.3,356.6 L458.7,357.2 L458.5,358.5 L459.4,358.4 L460.1,358.7 L460.3,359.2 L458.3,360.9 L456.6,361.8 L454.6,364.4 L452.3,366.3 L450.4,368.3 L449.1,370.0 L448.3,370.7 L447.4,370.9 L447.4,371.9 L446.7,372.5 L445.5,374.4 L444.4,374.9 L444.2,376.2 L440.9,380.7 L439.5,381.9 L439.2,383.0 L438.3,384.3 L437.2,385.6 L435.5,387.8 L433.8,390.6 L432.6,390.9 L431.8,393.6 L430.8,394.4 L429.1,396.6 L429.1,397.6 L427.9,398.4 L426.9,400.0 L426.7,400.7 L425.5,401.9 L424.7,402.0 L423.8,403.3 L423.6,404.8 L422.0,406.8 L418.9,411.4 L418.4,413.1 L417.9,413.9 L416.8,414.5 L417.3,416.2 L418.2,417.0 L419.5,417.2 L420.3,421.7 L421.2,424.6 L421.9,427.6 L421.7,429.4 L422.1,430.9 L421.7,432.3 L421.5,436.2 L420.6,436.7 L420.3,437.8 L421.9,438.6 L421.3,438.9 L421.0,440.1 L420.7,440.1 L420.0,441.8 L420.3,442.1 L421.1,441.7 L421.2,441.2 L422.1,441.3 L421.5,442.9 L422.5,443.5 L424.1,443.4 L424.1,444.6 L425.2,445.3 L426.4,446.7 L428.1,447.7 L428.9,447.9 L428.5,448.9 L427.7,449.2 L427.6,450.6 L428.1,451.2 L427.6,452.0 L428.2,453.3 L428.9,453.8 L428.8,454.6 L427.3,455.0 L426.7,455.0 L426.0,453.2 L425.1,453.2 L424.9,452.5 L424.4,452.3 L423.8,451.6 L422.8,451.1 L422.3,451.9 L421.3,451.4 L420.3,451.2 L419.6,451.3 L418.8,452.2 L417.3,451.3 L416.7,450.4 L416.1,449.0 L415.1,448.7 L414.8,448.0 L414.0,447.7 L412.9,448.5 L411.7,448.0 L410.6,448.1 L410.0,448.6 L409.4,448.4 L408.4,447.4 L407.9,447.2 L406.6,447.6 L405.7,447.1 L404.8,445.4 L404.3,445.2 L403.8,444.0 L402.1,443.9 L401.4,444.8 L400.3,444.3 L399.6,444.3 L397.8,443.4 L396.8,443.4 L397.0,442.6 L397.4,442.2 L397.1,441.3 L396.1,441.2 L395.3,441.8 L394.5,441.1 L392.9,440.3 L392.9,439.8 L392.2,439.8 L389.8,439.2 L388.5,438.6 L387.5,437.9 L387.9,436.4 L388.0,435.4 L387.6,434.9 L387.6,433.8 L386.5,433.0 L385.2,431.4 L384.9,430.1 L384.2,428.4 L382.8,428.6 L382.1,428.0 L381.5,428.1 L380.9,427.8 L380.6,426.9 L380.1,426.5 L379.3,426.9 L378.7,426.1 L378.4,425.2 L377.7,425.2 L377.3,425.7 L376.3,426.2 L375.4,427.9 L374.8,427.0 L374.7,425.8 L372.9,424.6 L372.8,423.7 L371.5,423.1 L370.9,423.6 L369.7,423.2 L369.5,422.6 L368.9,422.1 L368.3,420.9 L367.5,421.1 L366.9,421.8 L366.0,421.9 L365.2,421.4 L364.5,422.0 L363.7,421.8 L363.2,422.1 L362.6,423.0 L361.8,422.2 L361.3,422.5 L361.0,423.3 L359.9,423.3 L358.9,423.6 L357.5,422.5 L357.2,421.9 L356.9,420.2 L357.8,419.3 L357.7,418.8 L356.9,418.3 L356.8,417.7 L358.0,417.4 L357.6,416.1 L356.9,416.0 L356.4,414.9 L356.3,413.9 L355.3,413.5 L355.5,412.7 L356.0,412.0 L356.0,411.4 L355.5,410.9 L354.7,410.8 L354.0,410.2 L353.7,409.7 L354.4,408.7 L355.4,406.6 L355.2,405.4 L354.4,404.6 L354.7,402.3 L354.4,401.7 L353.4,401.6 L353.9,400.0 L353.2,399.4 L353.3,398.5 L352.9,397.8 L352.0,397.8 L351.5,397.3 L350.4,397.5 L350.2,396.7 L349.6,395.7 L349.8,395.0 L350.9,394.2 L351.3,393.6 L352.8,394.2 L353.9,393.6 L354.0,393.0 L354.8,392.7 L354.2,391.8 L353.2,391.2 L351.6,390.4 L350.4,390.7 L349.3,390.0 L349.0,389.2 L348.9,387.4 L350.6,387.4 L351.5,386.8 L351.9,385.5 L351.7,384.4 L349.9,383.9 L349.1,384.0 L347.3,383.8 L346.6,384.0 L346.2,382.8 L345.8,382.3 L345.8,381.3 L345.3,380.4 L345.2,379.1 L345.4,378.3 L346.6,377.6 L347.3,376.3 L346.3,375.5 L345.3,375.1 L345.0,374.4 L346.0,374.0 L346.7,372.5 L346.2,372.5 L345.7,371.7 L347.6,371.3 L346.9,370.9 L346.5,370.0 L347.2,369.3 L345.2,368.9 L345.0,368.2 L345.9,368.1 L346.5,367.6 L345.3,367.2 L346.2,365.0 L346.0,364.7 L346.0,363.1 L346.2,362.7 L344.9,360.3 L346.8,360.2 L345.1,359.0 L345.7,358.0 L346.8,356.6 L346.9,355.7 L347.3,355.2 L348.7,354.7 L348.3,353.2 L348.6,352.8 L348.1,351.3 L348.6,351.2 L349.1,349.9 L350.1,349.5 L350.4,348.6 L351.5,348.4 L351.5,347.2 L352.4,346.9 L353.0,346.2 L355.7,344.8 L357.3,344.9 L359.3,343.6 L359.7,342.6 L359.3,341.4 L359.8,340.7 L361.4,340.4 L363.6,339.1 L364.6,338.7 L366.4,338.9 L367.0,338.6 L368.0,336.8 L369.1,335.4 L369.6,334.6 L370.1,332.5 L370.4,331.8 L372.0,330.5 L372.7,329.1 L373.5,329.2 L373.9,328.3 L374.6,328.9 L375.5,329.1 L375.4,328.2 L376.1,326.8 L375.8,326.4 L377.3,325.7 L377.7,324.9 L377.9,323.3 L378.5,322.2 L378.4,320.3 L379.4,318.3 L379.5,317.0 L380.0,317.4 L380.3,318.1 L380.0,318.8 L381.0,318.8 L382.2,319.2 L383.6,319.4 L384.7,318.2 L385.8,318.5 L386.7,317.6 L388.0,316.5 L388.5,315.7 L390.6,316.2 L392.6,317.2 L393.9,317.5 L393.3,319.4 L395.5,320.2 L395.7,320.9 L394.2,324.4 L394.5,324.9 L394.1,325.8 L393.1,327.0 L394.2,327.5 L395.9,327.5 L396.7,328.0 L397.9,330.0 L398.0,331.0 L399.6,330.8 L400.8,331.3 L401.5,331.2 L402.8,331.5 L403.2,331.9 L404.2,332.3 L404.4,332.7 L405.2,333.0 L405.6,333.7 L406.5,333.8 L408.4,333.2 L408.5,333.6 L409.2,334.0 L410.3,333.8 L411.0,333.9 L411.8,334.7 L411.7,336.2 L411.0,336.5 L410.6,337.3 L411.0,337.6 L411.7,337.4 L411.8,339.0 L412.0,339.8 L412.7,339.8 L413.4,339.5 L413.7,339.9 L413.4,340.6 L411.9,341.4 L410.6,341.3 L410.6,341.6 L411.3,342.1 L413.3,341.5 L414.2,341.5 L414.2,342.0 L412.9,342.6 L412.2,343.9 L411.5,344.3 L411.0,343.0 L410.5,343.5 L409.9,343.7 L408.4,343.3 L408.3,342.9 L407.4,343.1 L407.9,343.9 L408.1,345.3 L407.6,345.4 L407.4,346.4 L405.8,346.1 L405.5,348.0 L407.0,347.9 L406.9,348.5 L407.8,348.8 L407.7,350.1 L409.2,351.3 L410.2,351.5 L410.7,352.0 L411.5,351.4 L412.1,351.2Z"/>
                  <path d="M317.1,363.2 L317.5,364.2 L318.5,363.1 L319.1,362.9 L320.0,363.2 L320.4,362.4 L320.7,362.4 L321.3,362.9 L322.7,362.8 L323.0,361.8 L323.8,361.8 L323.9,361.3 L325.1,360.7 L325.0,359.4 L326.2,358.2 L327.0,357.8 L327.4,356.8 L327.9,356.4 L328.7,356.4 L329.8,357.5 L330.7,357.2 L331.1,355.9 L332.1,356.0 L332.9,355.1 L334.8,355.7 L335.4,354.6 L335.9,354.3 L336.7,354.5 L337.8,354.4 L338.7,354.5 L339.8,354.1 L340.4,354.4 L341.0,354.3 L341.6,353.6 L342.0,352.8 L343.5,352.6 L343.9,351.6 L345.0,351.4 L346.0,352.3 L346.0,353.0 L346.5,353.3 L347.0,352.7 L347.1,351.7 L348.1,351.3 L348.6,352.8 L348.3,353.2 L348.7,354.7 L347.3,355.2 L346.9,355.7 L346.8,356.6 L345.7,358.0 L345.1,359.0 L346.8,360.2 L344.9,360.3 L346.2,362.7 L346.0,363.1 L346.0,364.7 L346.2,365.0 L345.3,367.2 L346.5,367.6 L345.9,368.1 L345.0,368.2 L345.2,368.9 L347.2,369.3 L346.5,370.0 L346.9,370.9 L347.6,371.3 L345.7,371.7 L346.2,372.5 L346.7,372.5 L346.0,374.0 L345.0,374.4 L345.3,375.1 L346.3,375.5 L347.3,376.3 L346.6,377.6 L345.4,378.3 L345.2,379.1 L345.3,380.4 L345.8,381.3 L345.8,382.3 L346.2,382.8 L346.6,384.0 L347.3,383.8 L349.1,384.0 L349.9,383.9 L351.7,384.4 L351.9,385.5 L351.5,386.8 L350.6,387.4 L348.9,387.4 L349.0,389.2 L349.3,390.0 L350.4,390.7 L351.6,390.4 L353.2,391.2 L354.2,391.8 L354.8,392.7 L354.0,393.0 L353.9,393.6 L352.8,394.2 L351.3,393.6 L350.9,394.2 L349.8,395.0 L349.6,395.7 L350.2,396.7 L350.4,397.5 L351.5,397.3 L352.0,397.8 L352.9,397.8 L353.3,398.5 L353.2,399.4 L353.9,400.0 L353.4,401.6 L354.4,401.7 L354.7,402.3 L354.4,404.6 L355.2,405.4 L355.4,406.6 L354.4,408.7 L353.7,409.7 L354.0,410.2 L354.7,410.8 L355.5,410.9 L356.0,411.4 L356.0,412.0 L355.5,412.7 L355.3,413.5 L356.3,413.9 L356.4,414.9 L356.9,416.0 L357.6,416.1 L358.0,417.4 L356.8,417.7 L356.9,418.3 L357.7,418.8 L357.8,419.3 L356.9,420.2 L357.2,421.9 L355.9,422.0 L355.7,421.5 L354.2,420.6 L352.8,420.8 L351.4,419.8 L350.9,420.1 L350.5,420.9 L349.6,421.9 L348.8,422.5 L348.9,423.2 L348.2,423.9 L347.3,424.4 L345.3,422.4 L344.0,421.7 L343.5,422.7 L342.7,422.2 L342.0,422.6 L340.7,422.8 L340.3,423.2 L340.6,424.2 L343.0,426.3 L342.6,427.2 L341.2,427.2 L340.6,427.4 L340.1,427.2 L339.3,427.8 L337.5,430.1 L337.8,431.2 L337.4,431.7 L336.9,431.4 L335.2,432.2 L334.4,432.2 L333.7,431.6 L333.1,430.2 L332.2,430.6 L332.4,429.6 L331.3,428.5 L330.3,429.5 L329.7,429.5 L328.5,427.9 L327.9,427.5 L327.4,426.4 L326.7,426.7 L326.6,425.8 L325.9,425.1 L326.4,424.5 L325.7,424.2 L325.8,423.6 L324.7,423.0 L323.3,423.0 L322.0,424.2 L320.9,424.4 L320.6,423.9 L319.4,423.5 L318.8,425.4 L317.9,425.8 L317.7,426.3 L316.8,426.2 L316.3,425.6 L314.5,425.0 L314.2,424.2 L313.6,424.0 L312.9,423.4 L312.3,423.3 L311.5,423.6 L310.7,423.3 L310.6,424.4 L309.5,425.1 L308.4,425.2 L308.2,424.1 L307.4,424.6 L305.6,424.3 L305.6,425.2 L305.4,426.2 L305.7,426.7 L305.5,428.1 L305.1,428.8 L304.1,428.2 L303.1,428.2 L302.8,428.6 L302.1,428.3 L301.2,428.6 L300.7,428.2 L300.3,428.7 L299.4,428.9 L298.9,430.0 L298.2,430.0 L298.5,431.1 L297.7,431.7 L297.0,430.8 L296.4,432.1 L296.2,433.9 L295.9,434.6 L294.9,435.8 L294.7,437.2 L294.9,438.1 L294.2,439.3 L294.1,440.4 L294.3,441.1 L293.7,441.5 L292.0,440.1 L291.0,439.7 L290.3,438.8 L290.5,438.3 L289.2,437.4 L288.7,437.5 L286.2,437.1 L285.4,437.5 L285.3,438.8 L284.6,439.2 L283.8,439.9 L282.4,440.3 L281.9,439.7 L281.4,439.9 L280.8,439.4 L280.2,440.0 L278.7,439.2 L278.4,438.6 L278.9,438.1 L278.8,437.2 L280.0,435.6 L280.1,434.6 L280.7,434.1 L281.5,433.9 L280.9,432.5 L280.2,432.5 L280.1,432.0 L280.7,430.4 L280.2,428.5 L280.6,428.1 L280.5,426.8 L280.1,426.1 L280.0,423.7 L278.9,423.5 L277.9,423.9 L277.1,425.1 L276.3,425.1 L275.0,424.3 L273.9,423.9 L272.7,423.8 L272.5,423.5 L273.1,422.0 L273.1,422.0 L273.1,422.0 L273.1,422.0 L272.7,421.4 L272.8,420.1 L272.1,420.7 L271.4,420.9 L270.7,420.0 L270.0,420.1 L270.0,420.0 L269.9,419.9 L269.8,419.7 L269.6,419.6 L269.6,419.6 L269.6,419.5 L269.5,419.4 L269.5,419.3 L269.4,419.2 L269.4,419.1 L269.3,419.1 L268.8,417.8 L268.8,417.8 L268.6,417.6 L268.6,417.6 L268.6,417.6 L268.6,417.6 L268.5,416.3 L267.5,414.1 L267.4,412.2 L267.4,411.3 L266.9,411.2 L266.5,410.6 L265.9,410.6 L265.0,410.1 L264.5,409.5 L264.8,408.8 L264.5,408.1 L265.1,407.3 L264.5,405.5 L265.0,404.5 L264.4,404.0 L264.1,403.3 L263.2,403.0 L261.9,403.6 L261.6,403.2 L260.8,403.3 L260.7,402.7 L260.0,402.1 L260.8,401.6 L261.3,400.7 L261.7,400.7 L262.6,400.0 L262.6,399.4 L263.4,398.8 L263.4,398.3 L263.0,397.1 L262.2,396.0 L261.2,396.8 L260.6,396.2 L259.9,396.1 L259.6,395.6 L258.8,396.1 L257.2,395.6 L257.2,395.0 L256.6,394.2 L255.8,392.6 L255.9,390.0 L255.0,389.2 L253.4,388.9 L251.8,388.4 L251.5,389.2 L250.2,389.8 L249.6,388.9 L250.3,387.7 L251.4,387.3 L251.4,385.2 L251.0,384.7 L250.9,382.4 L250.1,382.6 L249.8,383.2 L247.7,383.2 L247.1,382.9 L246.8,382.2 L247.8,381.5 L248.0,381.1 L247.7,379.9 L247.8,377.8 L248.6,377.1 L248.9,376.4 L248.6,376.0 L249.2,375.4 L249.1,375.0 L250.2,373.9 L250.6,374.2 L251.4,373.5 L252.7,373.6 L252.3,372.9 L252.8,371.8 L252.8,371.1 L254.0,368.5 L254.5,369.6 L255.4,369.4 L255.0,367.5 L255.9,367.8 L258.0,367.5 L259.0,366.7 L258.6,365.9 L258.9,365.0 L258.3,364.6 L258.2,364.0 L259.5,363.9 L260.4,363.2 L261.4,363.1 L263.3,361.2 L262.6,361.3 L261.8,360.7 L260.9,360.7 L258.4,359.4 L257.6,358.7 L258.8,358.2 L259.4,358.3 L260.4,357.6 L260.7,356.4 L261.7,355.4 L261.5,354.5 L261.9,353.8 L262.0,353.1 L263.7,354.1 L264.1,353.8 L265.3,353.7 L265.1,353.0 L265.4,351.9 L265.2,351.2 L264.1,350.7 L264.1,350.0 L265.6,350.5 L265.4,348.1 L266.3,347.8 L266.9,346.6 L267.8,346.3 L268.4,346.9 L269.3,347.0 L269.8,347.3 L270.6,347.2 L271.1,347.6 L271.6,347.4 L272.1,346.8 L272.6,347.2 L274.3,346.7 L274.0,346.1 L274.6,345.3 L275.4,345.2 L275.7,344.2 L276.8,344.8 L277.2,344.5 L278.0,344.7 L279.0,344.5 L279.6,344.8 L280.4,344.5 L280.8,345.1 L281.7,345.0 L283.0,345.2 L283.0,344.3 L285.1,343.9 L285.4,343.5 L284.9,342.8 L285.3,342.3 L286.5,342.1 L287.1,341.8 L288.2,342.0 L288.3,341.1 L287.7,340.7 L287.6,339.9 L288.9,338.8 L290.4,339.0 L291.3,339.6 L291.9,340.2 L292.6,339.3 L293.7,339.5 L294.5,341.0 L295.7,341.4 L296.3,341.0 L298.0,341.4 L298.3,340.9 L299.8,340.6 L300.6,341.0 L300.9,341.9 L301.9,342.1 L303.1,343.0 L302.5,344.7 L302.8,345.8 L304.0,346.4 L304.1,347.5 L304.6,348.0 L304.7,348.7 L305.4,349.1 L305.6,350.4 L306.2,350.8 L305.7,351.6 L304.1,352.0 L304.6,352.8 L304.1,353.4 L304.5,353.8 L305.8,354.1 L306.0,355.2 L306.5,355.1 L306.3,356.2 L307.1,356.4 L307.4,357.0 L306.5,357.3 L306.0,358.0 L306.7,358.4 L307.7,359.5 L309.1,360.0 L308.6,360.7 L308.0,361.1 L308.0,362.0 L308.5,362.6 L309.7,362.3 L310.0,363.9 L311.0,363.4 L312.6,363.2 L313.6,363.8 L314.6,364.0 L315.6,363.9 L315.9,363.4 L317.1,363.2Z"/>
                  <path d="M273.2,37.4 L272.6,42.0 L265.8,45.6 L262.0,47.6 L256.0,49.2 L254.4,50.5 L254.2,51.4 L255.9,52.5 L256.5,53.4 L256.7,54.1 L257.3,54.8 L257.1,55.4 L257.6,56.0 L257.4,57.4 L255.7,57.8 L254.6,58.6 L253.9,59.3 L254.4,60.5 L253.5,62.3 L252.8,62.9 L254.3,65.5 L252.9,66.2 L252.6,66.8 L253.5,67.1 L254.8,68.2 L255.1,68.9 L254.5,69.3 L255.4,71.1 L256.1,70.2 L257.4,70.0 L257.6,70.5 L259.3,71.7 L259.9,71.9 L261.8,72.1 L260.5,72.8 L260.2,73.2 L259.6,75.1 L260.0,77.0 L261.3,77.1 L262.0,77.7 L263.0,77.3 L264.1,76.5 L265.5,76.2 L266.6,76.1 L267.8,76.3 L268.6,76.1 L270.0,76.7 L269.9,77.7 L268.7,77.8 L269.0,78.3 L268.6,80.2 L267.6,81.3 L267.2,82.8 L266.7,83.1 L266.7,84.2 L265.1,86.1 L266.1,86.9 L264.7,88.6 L264.1,88.7 L263.8,87.6 L259.8,86.3 L257.6,86.3 L257.0,87.1 L256.9,88.3 L256.4,88.8 L256.6,89.9 L256.5,91.1 L255.4,92.6 L254.7,92.8 L253.6,94.1 L252.8,94.8 L252.3,95.7 L252.8,97.5 L252.3,97.4 L252.4,99.0 L251.9,99.8 L250.6,99.6 L249.9,100.9 L248.7,102.0 L248.3,102.1 L248.2,103.6 L246.7,103.0 L244.4,103.0 L242.9,103.4 L241.5,103.1 L241.4,103.5 L239.3,103.6 L238.4,103.4 L238.0,104.3 L237.5,103.2 L236.8,102.8 L234.5,102.6 L233.7,102.4 L231.2,102.2 L230.6,102.4 L229.6,100.3 L229.5,99.3 L230.2,97.9 L231.1,96.8 L231.9,94.9 L229.6,95.6 L229.1,96.3 L228.0,96.1 L227.1,95.1 L226.5,95.8 L225.5,95.6 L224.3,97.0 L223.3,97.3 L222.2,97.1 L221.9,98.3 L220.5,98.4 L220.0,97.6 L219.4,97.5 L219.5,96.7 L218.7,96.5 L219.2,95.7 L218.7,94.8 L217.8,95.1 L216.9,94.8 L216.5,95.4 L214.1,96.0 L214.2,95.7 L211.0,96.0 L210.7,95.7 L208.1,95.1 L206.7,95.0 L206.2,94.2 L207.2,93.8 L207.2,92.6 L206.3,91.5 L204.6,92.0 L203.9,92.0 L203.4,90.7 L202.2,91.6 L201.5,91.0 L201.3,89.8 L200.3,89.7 L199.4,90.6 L199.2,89.7 L197.5,88.9 L197.4,89.4 L196.8,90.4 L196.3,92.3 L195.7,93.1 L193.6,93.2 L193.4,92.7 L192.6,92.1 L191.8,92.1 L191.7,91.5 L190.8,91.3 L189.3,91.4 L187.7,90.5 L187.3,90.9 L188.1,91.5 L188.7,92.9 L189.1,94.3 L188.2,94.5 L187.5,95.6 L187.0,96.0 L186.1,95.6 L184.8,96.6 L184.4,96.7 L183.1,95.1 L182.8,96.0 L182.2,96.7 L181.6,96.8 L181.7,97.5 L181.3,97.9 L181.2,99.0 L180.2,100.0 L180.5,101.7 L181.3,102.4 L181.1,103.3 L179.9,103.1 L179.0,103.3 L178.1,102.7 L177.6,103.2 L177.6,103.8 L178.4,104.0 L178.4,104.9 L177.6,104.9 L177.3,104.1 L176.1,103.7 L175.1,103.0 L173.9,104.4 L172.1,104.2 L171.4,103.1 L171.2,102.2 L170.5,101.7 L169.3,102.4 L167.7,102.6 L166.6,103.6 L166.3,104.1 L166.1,105.4 L165.9,105.8 L166.2,106.7 L166.3,107.6 L165.8,107.8 L165.4,108.6 L165.4,109.4 L165.0,109.8 L164.8,111.1 L164.6,110.6 L163.5,110.6 L162.9,110.9 L161.7,110.2 L161.6,109.4 L160.7,109.4 L160.9,108.5 L160.3,108.5 L159.6,107.8 L158.8,107.6 L157.8,108.0 L156.4,107.8 L154.3,107.9 L153.9,108.2 L152.9,108.0 L150.4,107.1 L149.2,108.6 L147.6,109.2 L146.6,108.8 L145.8,107.9 L146.3,107.5 L146.1,106.6 L145.1,106.1 L144.4,106.7 L143.6,106.6 L143.6,105.9 L143.1,104.9 L143.2,104.1 L142.0,102.7 L141.7,101.7 L141.1,101.1 L140.9,100.5 L141.2,99.6 L140.9,98.4 L140.3,96.0 L140.2,93.6 L139.2,91.5 L141.3,90.4 L142.2,89.3 L142.7,89.2 L143.4,87.3 L142.2,87.5 L141.7,87.3 L141.7,85.8 L141.0,85.6 L140.1,85.0 L139.9,83.9 L139.6,83.4 L139.9,82.1 L141.6,81.6 L143.9,81.7 L144.9,81.3 L145.2,79.4 L144.4,79.4 L143.7,78.8 L142.9,78.7 L141.7,77.8 L140.1,76.3 L140.3,74.9 L140.0,73.8 L138.7,73.3 L138.5,72.2 L136.6,72.6 L136.5,71.8 L135.7,70.5 L135.8,69.9 L136.4,69.1 L136.4,67.8 L137.0,67.4 L137.1,66.5 L136.1,66.3 L136.1,64.4 L136.7,63.2 L136.1,62.8 L135.4,62.9 L135.2,61.8 L135.2,60.3 L134.4,60.0 L134.1,58.9 L133.3,58.5 L133.2,56.5 L132.6,56.6 L132.3,55.8 L133.1,54.8 L135.2,53.6 L135.8,53.7 L137.3,53.2 L138.0,53.2 L139.6,52.1 L139.5,49.9 L139.1,48.5 L138.3,48.0 L137.1,47.7 L137.6,46.7 L137.6,46.2 L137.6,46.2 L137.6,46.2 L137.8,45.6 L136.8,43.7 L137.5,43.9 L138.9,43.4 L139.3,42.8 L138.4,41.5 L138.4,41.0 L137.6,40.2 L138.4,39.0 L138.4,38.5 L137.5,37.1 L137.8,36.5 L136.1,34.7 L134.7,34.8 L134.4,34.2 L133.5,32.4 L134.0,31.7 L133.5,30.8 L132.3,29.6 L131.6,28.5 L134.0,28.5 L134.8,28.4 L136.1,27.8 L139.0,27.0 L140.8,26.7 L143.8,25.1 L146.1,23.2 L148.5,20.8 L153.3,18.6 L157.7,16.2 L158.7,15.8 L161.4,15.2 L163.1,14.6 L165.4,14.3 L168.6,14.1 L172.5,13.1 L176.1,12.5 L178.1,12.1 L182.3,10.7 L184.8,10.2 L190.0,8.7 L192.5,8.3 L197.9,8.1 L200.2,8.2 L202.4,8.1 L205.3,8.2 L208.5,8.0 L209.9,8.1 L210.8,8.7 L211.8,9.7 L214.3,10.9 L214.9,11.6 L220.2,14.6 L225.8,17.6 L229.2,19.8 L232.2,22.4 L233.7,24.3 L233.8,25.5 L233.1,26.6 L232.4,26.0 L232.2,25.4 L231.4,25.1 L231.4,24.5 L230.7,23.2 L230.0,22.3 L228.4,20.1 L227.0,18.9 L225.4,18.5 L224.1,17.0 L222.7,16.3 L219.1,14.2 L218.4,14.0 L216.5,12.9 L215.7,12.3 L214.3,11.7 L214.3,11.7 L214.1,13.1 L213.6,13.7 L213.1,14.9 L212.9,16.2 L214.6,17.1 L215.5,18.1 L216.7,18.9 L216.4,19.5 L216.4,21.8 L216.8,22.3 L216.5,23.2 L216.8,23.7 L218.6,23.5 L218.6,24.5 L218.8,25.6 L219.7,27.0 L220.0,27.3 L220.6,29.3 L220.9,29.9 L221.0,32.4 L220.5,32.6 L221.4,34.9 L221.0,36.0 L221.1,37.2 L221.9,38.5 L222.5,39.2 L224.0,40.4 L224.9,40.8 L225.8,40.4 L227.5,41.3 L228.5,40.8 L229.1,41.2 L228.0,42.2 L228.5,43.0 L229.3,43.4 L231.3,43.7 L234.9,45.1 L236.3,45.4 L238.1,45.5 L239.8,44.6 L240.5,45.3 L241.5,45.6 L243.8,45.7 L246.7,45.5 L250.0,45.3 L254.3,44.7 L256.8,44.2 L261.6,42.9 L265.0,41.6 L268.3,40.0 L270.7,38.5 L272.6,36.9 L273.2,37.4Z"/>
                  <path d="M424.0,44.2 L424.7,45.3 L424.6,46.0 L423.6,46.5 L424.1,47.1 L423.7,48.7 L423.9,49.1 L422.9,49.9 L422.1,49.2 L421.8,49.8 L421.0,50.3 L419.3,51.0 L417.0,50.0 L416.0,51.5 L415.3,52.2 L413.8,51.8 L412.6,52.8 L411.6,53.0 L411.4,54.5 L409.7,55.6 L409.2,56.8 L410.1,57.8 L410.9,59.2 L410.9,59.7 L411.8,59.8 L411.7,60.4 L412.3,61.5 L413.8,61.2 L414.9,61.6 L415.8,63.4 L416.6,63.8 L416.5,65.0 L415.0,65.8 L414.7,67.0 L414.9,68.1 L416.2,68.4 L418.2,69.9 L418.2,70.5 L418.7,71.7 L419.2,72.0 L419.3,73.8 L420.1,74.6 L420.3,75.4 L422.1,76.4 L423.6,78.6 L423.2,80.0 L423.7,81.0 L423.4,82.4 L422.4,83.6 L420.9,84.9 L420.8,85.7 L420.9,87.1 L419.5,90.3 L418.2,90.7 L416.1,91.8 L415.6,92.6 L415.3,93.6 L414.7,93.7 L414.1,93.0 L413.2,93.4 L410.7,95.1 L409.6,95.0 L408.4,95.8 L408.7,96.7 L408.5,97.6 L407.7,97.9 L407.4,97.2 L406.3,96.5 L405.3,97.6 L403.0,98.7 L403.0,99.1 L401.6,100.1 L400.4,100.9 L399.7,100.9 L399.1,101.9 L397.5,103.0 L397.0,103.8 L396.1,104.4 L395.4,105.3 L392.2,107.2 L390.6,107.9 L389.6,107.6 L388.5,108.5 L388.0,109.8 L387.5,110.6 L386.8,110.3 L386.5,109.3 L385.5,108.7 L383.8,109.7 L382.9,109.5 L382.4,109.7 L381.3,110.9 L381.4,111.5 L380.8,113.0 L380.3,113.4 L379.2,113.5 L377.4,112.9 L376.0,112.0 L374.1,111.7 L373.1,111.2 L371.0,110.9 L369.1,111.8 L367.5,111.9 L365.2,111.9 L364.4,112.1 L362.9,112.1 L358.9,113.2 L359.4,114.5 L358.5,114.3 L359.0,115.3 L357.8,115.9 L356.6,115.7 L356.1,116.4 L354.3,116.6 L351.5,116.0 L350.0,116.5 L348.3,117.5 L347.6,118.1 L346.5,118.6 L344.8,119.8 L344.6,120.7 L343.5,121.5 L342.4,121.9 L341.5,122.6 L340.5,123.1 L339.5,121.8 L338.1,121.4 L337.6,121.0 L336.8,121.6 L335.5,121.5 L333.9,122.0 L333.7,122.4 L334.2,123.0 L334.1,124.0 L333.0,124.5 L331.9,124.1 L331.6,125.4 L330.9,125.6 L330.8,126.3 L329.6,125.4 L328.3,125.2 L326.7,124.5 L326.1,124.7 L325.4,125.4 L324.3,125.0 L324.3,125.7 L323.3,125.5 L323.3,126.3 L322.4,128.1 L322.0,128.4 L321.6,129.4 L319.4,129.4 L319.3,130.4 L318.2,130.9 L317.6,130.3 L317.0,131.3 L315.5,130.9 L315.3,131.6 L314.4,131.2 L314.4,130.7 L312.9,130.8 L312.0,129.8 L310.4,131.7 L309.3,133.5 L308.5,134.2 L307.8,135.1 L305.7,136.4 L307.4,136.8 L307.0,137.4 L306.2,137.5 L304.0,138.0 L303.3,137.8 L302.4,137.9 L301.4,137.4 L298.0,136.8 L297.9,137.2 L297.3,137.8 L296.1,137.5 L295.7,136.3 L295.0,135.8 L293.8,136.0 L293.7,136.6 L291.9,136.5 L291.7,137.4 L291.0,137.0 L289.0,137.7 L288.3,137.2 L287.9,136.4 L286.6,135.6 L285.6,135.1 L286.0,133.9 L285.9,132.5 L284.4,132.7 L283.8,133.3 L282.7,134.0 L281.7,135.4 L282.3,136.2 L282.3,137.9 L279.4,136.8 L278.6,137.1 L278.2,135.6 L278.1,134.6 L277.6,134.2 L277.8,132.5 L277.7,131.2 L275.1,130.6 L275.2,129.4 L274.8,128.9 L275.8,128.2 L276.5,127.3 L276.2,126.6 L276.5,125.5 L275.1,125.2 L275.0,124.8 L275.2,122.9 L274.3,122.9 L272.8,122.3 L271.4,122.8 L270.6,122.8 L270.2,123.3 L267.0,123.4 L267.0,122.7 L267.3,121.7 L266.9,121.2 L267.0,120.3 L265.3,121.1 L265.2,120.3 L264.4,120.4 L264.0,121.1 L262.8,120.8 L263.4,119.9 L262.2,119.7 L261.5,118.7 L260.8,118.2 L260.3,117.2 L257.9,117.7 L256.8,117.2 L256.7,118.2 L256.0,118.9 L254.5,118.3 L253.8,117.2 L254.3,115.6 L253.9,115.4 L253.6,114.0 L252.9,114.0 L252.9,113.4 L253.4,112.8 L252.8,111.9 L252.9,111.2 L251.9,110.6 L251.1,109.5 L252.2,108.5 L252.3,107.4 L252.0,106.7 L251.1,106.7 L251.6,105.0 L250.8,104.3 L249.6,104.1 L248.2,103.6 L248.3,102.1 L248.7,102.0 L249.9,100.9 L250.6,99.6 L251.9,99.8 L252.4,99.0 L252.3,97.4 L252.8,97.5 L252.3,95.7 L252.8,94.8 L253.6,94.1 L254.7,92.8 L255.4,92.6 L256.5,91.1 L256.6,89.9 L256.4,88.8 L256.9,88.3 L257.0,87.1 L257.6,86.3 L259.8,86.3 L263.8,87.6 L264.1,88.7 L264.7,88.6 L266.1,86.9 L265.1,86.1 L266.7,84.2 L266.7,83.1 L267.2,82.8 L267.6,81.3 L268.6,80.2 L269.0,78.3 L268.7,77.8 L269.9,77.7 L270.0,76.7 L268.6,76.1 L267.8,76.3 L266.6,76.1 L265.5,76.2 L264.1,76.5 L263.0,77.3 L262.0,77.7 L261.3,77.1 L260.0,77.0 L259.6,75.1 L260.2,73.2 L260.5,72.8 L261.8,72.1 L259.9,71.9 L259.3,71.7 L257.6,70.5 L257.4,70.0 L256.1,70.2 L255.4,71.1 L254.5,69.3 L255.1,68.9 L254.8,68.2 L253.5,67.1 L252.6,66.8 L252.9,66.2 L254.3,65.5 L252.8,62.9 L253.5,62.3 L254.4,60.5 L253.9,59.3 L254.6,58.6 L255.7,57.8 L257.4,57.4 L257.6,56.0 L257.1,55.4 L257.3,54.8 L256.7,54.1 L256.5,53.4 L255.9,52.5 L254.2,51.4 L254.4,50.5 L256.0,49.2 L262.0,47.6 L265.8,45.6 L272.6,42.0 L273.2,37.4 L280.9,38.2 L286.9,39.0 L290.4,39.5 L292.5,39.7 L306.0,41.4 L307.4,41.7 L309.4,42.0 L311.1,42.4 L314.3,42.8 L318.1,43.1 L319.9,43.7 L320.3,44.0 L321.1,43.6 L323.1,43.6 L324.5,43.8 L325.6,43.8 L329.3,44.5 L333.2,44.6 L336.0,44.9 L338.4,45.0 L339.9,45.4 L342.3,45.5 L344.9,45.8 L345.9,46.2 L347.5,46.2 L351.2,46.9 L352.6,46.5 L353.6,46.8 L356.2,46.7 L356.9,47.0 L359.0,47.1 L359.4,47.7 L361.8,47.3 L365.6,47.2 L368.5,47.0 L370.5,47.0 L376.2,46.7 L377.2,46.7 L381.9,46.5 L384.6,46.6 L385.8,46.4 L389.0,46.4 L390.7,46.2 L393.4,46.3 L395.1,46.1 L396.9,45.7 L398.5,46.0 L404.4,45.7 L406.3,45.4 L411.4,45.3 L413.4,45.0 L415.6,44.8 L416.8,45.0 L418.7,44.5 L420.8,44.4 L424.0,44.2Z"/>
                  <path d="M289.7,291.7 L289.5,292.8 L289.7,294.2 L289.4,295.9 L289.2,296.3 L292.2,296.7 L292.7,297.5 L292.4,297.8 L292.5,299.9 L292.3,301.2 L291.6,302.3 L291.9,302.9 L291.7,304.0 L291.3,304.1 L290.9,304.8 L287.9,302.5 L288.1,301.8 L287.6,300.9 L287.0,301.1 L287.0,300.0 L285.2,299.8 L285.1,299.7 L284.6,298.3 L284.0,298.3 L283.5,299.6 L283.7,300.3 L283.3,300.8 L283.5,301.6 L282.7,302.3 L281.9,304.4 L281.9,305.1 L282.5,305.9 L282.8,306.9 L282.0,308.4 L280.9,309.0 L280.3,308.9 L279.2,311.3 L277.9,312.2 L277.6,313.3 L277.0,313.9 L274.8,313.8 L274.2,313.5 L273.6,312.4 L273.8,311.5 L272.9,311.2 L271.7,311.1 L271.5,311.3 L270.2,309.2 L269.4,308.9 L268.6,309.6 L267.2,310.0 L266.5,311.0 L265.4,311.1 L264.7,310.7 L264.3,309.9 L264.3,308.8 L264.6,308.4 L264.3,307.6 L263.1,306.2 L262.8,304.6 L261.9,303.8 L261.3,302.8 L261.3,302.4 L260.6,301.9 L260.0,302.4 L259.1,302.5 L259.4,301.6 L258.7,301.1 L257.8,300.9 L257.3,300.4 L257.3,299.8 L257.9,299.0 L257.5,298.4 L256.0,298.4 L254.5,299.5 L253.8,299.1 L253.7,300.0 L254.3,301.4 L253.6,302.7 L252.8,303.0 L252.0,303.0 L250.0,302.3 L249.8,301.9 L249.0,301.8 L249.2,301.2 L248.4,301.1 L248.0,302.0 L247.4,301.0 L247.1,299.9 L246.6,299.5 L245.5,299.2 L244.7,299.3 L244.5,298.7 L242.8,297.9 L242.6,296.8 L241.7,296.1 L240.6,295.7 L239.9,295.0 L238.8,294.8 L238.1,294.4 L237.3,295.8 L237.0,296.8 L236.5,297.4 L235.5,296.4 L233.3,296.7 L232.6,297.1 L231.5,296.9 L230.0,297.2 L229.2,296.8 L227.8,297.0 L226.3,297.6 L225.8,297.7 L224.6,297.1 L224.0,296.1 L224.0,296.1 L224.0,296.1 L224.0,296.1 L224.0,296.0 L223.2,295.7 L221.8,295.6 L221.9,295.0 L221.0,293.8 L221.3,292.8 L220.6,291.3 L219.0,291.2 L218.8,291.7 L218.9,293.3 L218.8,293.9 L218.0,294.3 L217.2,294.3 L216.6,293.9 L216.3,292.7 L215.9,293.1 L214.3,293.0 L214.2,292.3 L213.6,292.4 L212.4,292.1 L211.6,291.3 L211.0,291.5 L210.0,291.3 L209.2,291.7 L208.7,291.6 L207.9,290.7 L207.4,290.6 L206.5,289.8 L205.6,289.8 L204.6,290.1 L203.0,289.9 L202.0,289.3 L201.9,288.7 L202.4,287.2 L201.9,287.2 L202.0,285.7 L202.6,285.0 L202.7,284.9 L202.4,283.7 L202.1,283.3 L202.2,281.1 L201.3,281.0 L201.4,281.7 L200.7,281.7 L200.1,281.3 L199.3,282.0 L199.0,281.4 L199.6,280.8 L199.0,280.4 L199.5,280.1 L199.5,278.1 L198.5,277.0 L198.5,275.8 L197.6,275.1 L198.8,274.2 L200.1,274.5 L200.5,274.3 L200.7,273.0 L201.2,271.9 L203.2,272.3 L203.9,271.2 L203.8,270.2 L204.2,269.5 L203.9,268.8 L203.1,268.1 L205.0,267.3 L206.3,268.3 L208.3,269.0 L209.7,269.8 L210.4,269.0 L210.4,268.1 L211.0,267.4 L211.4,266.1 L212.4,265.4 L212.3,262.2 L211.4,261.2 L211.3,260.5 L211.8,260.3 L211.6,258.7 L211.9,257.7 L211.8,256.6 L212.1,255.9 L210.9,255.9 L211.0,255.4 L211.7,254.6 L211.7,254.6 L211.0,253.4 L211.8,253.3 L212.3,251.9 L211.1,251.3 L211.5,249.7 L213.0,248.3 L213.8,248.2 L213.6,246.3 L214.2,246.2 L214.4,245.5 L214.0,244.8 L214.3,243.9 L214.8,243.3 L214.7,242.7 L213.7,241.3 L215.0,241.1 L215.4,240.7 L215.1,238.7 L215.5,238.5 L216.4,239.0 L216.3,237.7 L216.7,236.7 L217.3,236.8 L218.4,236.5 L218.5,237.4 L219.1,238.0 L220.4,237.2 L221.3,237.2 L222.6,236.9 L223.5,238.0 L224.3,238.3 L224.9,238.0 L225.2,238.6 L227.1,238.9 L227.3,237.2 L227.7,236.6 L227.5,235.6 L228.7,235.0 L229.4,233.6 L229.4,232.9 L230.0,232.4 L230.9,230.8 L229.7,230.1 L229.5,228.2 L228.8,228.5 L228.4,227.6 L229.2,227.7 L229.2,226.9 L228.5,225.7 L229.3,225.1 L228.9,224.3 L229.0,223.7 L228.4,222.8 L228.4,222.0 L228.8,220.3 L230.2,219.4 L230.7,220.1 L231.8,220.6 L233.0,220.2 L233.8,220.4 L234.4,219.8 L234.5,219.2 L235.6,218.6 L236.5,219.2 L238.4,219.2 L238.7,217.3 L239.4,217.2 L239.6,216.2 L239.3,215.5 L238.8,215.9 L237.8,215.1 L237.6,214.1 L238.0,213.0 L238.1,211.1 L238.7,210.0 L239.7,210.3 L240.3,209.5 L241.1,209.4 L243.0,208.8 L244.6,209.0 L245.8,210.0 L246.4,209.5 L246.0,208.2 L245.9,207.0 L247.0,206.7 L246.9,204.9 L246.6,204.6 L244.4,204.4 L243.7,204.6 L244.2,202.7 L243.9,202.4 L243.8,201.1 L244.2,200.9 L244.3,199.8 L244.9,199.8 L246.0,199.0 L246.9,198.7 L248.0,199.0 L249.2,198.7 L249.7,199.2 L250.7,198.6 L252.1,198.2 L252.1,197.3 L252.8,197.6 L254.7,195.9 L255.1,195.1 L255.9,195.2 L256.5,195.3 L256.9,196.1 L256.1,196.4 L256.3,197.1 L257.2,196.8 L257.8,197.7 L257.5,198.6 L258.8,198.7 L259.2,199.1 L261.5,198.9 L261.6,199.3 L262.5,199.5 L263.9,198.5 L264.3,199.0 L266.1,199.8 L265.6,200.8 L266.3,201.2 L267.0,202.0 L267.8,202.2 L268.4,202.8 L269.8,203.1 L271.6,203.2 L271.5,204.1 L272.5,204.3 L272.0,205.1 L272.5,205.7 L273.2,205.9 L274.3,205.7 L275.4,204.6 L275.6,204.0 L277.0,204.8 L277.4,205.5 L279.1,205.0 L281.2,203.6 L281.8,204.3 L282.7,204.5 L284.2,203.2 L283.4,203.0 L284.5,201.6 L286.2,202.5 L287.0,202.3 L287.1,203.1 L287.8,203.6 L288.4,204.8 L289.4,205.2 L290.9,205.2 L292.1,205.6 L291.8,206.4 L293.6,207.1 L293.9,207.5 L293.1,210.1 L293.0,210.9 L294.2,212.4 L294.4,213.6 L296.1,214.3 L297.7,213.7 L298.0,214.7 L298.6,215.2 L299.1,216.0 L299.7,216.5 L300.3,216.2 L302.2,216.4 L302.4,217.2 L302.9,217.6 L303.0,219.4 L301.8,219.5 L302.2,221.1 L299.9,223.2 L299.7,223.0 L299.6,223.3 L300.9,224.9 L302.3,225.6 L303.1,226.7 L303.1,227.5 L304.1,227.8 L301.6,229.1 L301.7,230.3 L302.8,230.6 L302.9,230.3 L303.9,230.0 L304.9,230.3 L305.0,231.6 L305.6,231.3 L307.0,231.5 L307.3,230.8 L307.9,231.2 L308.7,229.8 L309.1,230.6 L309.9,230.6 L310.4,229.8 L311.1,229.6 L311.7,230.3 L312.9,230.6 L313.6,232.2 L313.0,233.0 L314.0,233.8 L314.5,233.4 L317.4,233.9 L317.2,234.8 L318.1,235.2 L318.4,236.0 L318.4,236.8 L318.8,238.5 L319.7,238.5 L319.8,239.1 L319.2,239.4 L317.7,239.0 L316.8,239.2 L316.9,239.7 L318.0,239.8 L317.8,241.1 L318.6,243.0 L318.1,243.6 L318.0,244.6 L318.9,244.7 L320.7,245.4 L321.6,246.4 L321.5,247.0 L321.7,248.4 L321.4,250.0 L320.6,250.1 L320.0,250.7 L319.8,251.5 L318.7,251.4 L315.7,250.7 L315.1,250.9 L315.1,250.1 L314.7,249.4 L313.4,249.6 L312.5,248.8 L311.7,249.0 L311.1,249.7 L309.3,250.4 L308.8,251.4 L309.0,252.1 L308.5,252.8 L308.8,253.2 L309.9,253.4 L311.2,258.1 L312.1,257.9 L312.5,259.0 L312.2,259.8 L311.7,261.7 L311.6,263.0 L312.4,263.6 L313.8,263.6 L314.1,263.1 L315.0,262.9 L315.1,265.0 L315.3,266.0 L315.7,266.5 L314.6,267.4 L313.9,267.6 L313.3,268.8 L313.5,269.6 L313.1,270.5 L311.5,270.5 L311.3,271.8 L311.0,272.0 L310.5,273.5 L310.3,274.1 L310.8,275.9 L309.9,277.4 L308.5,277.5 L307.5,278.1 L307.5,278.3 L307.6,278.3 L307.6,278.4 L308.8,281.1 L308.6,281.8 L308.1,281.9 L308.3,283.1 L307.8,283.8 L307.3,283.5 L307.0,282.7 L306.1,282.4 L304.9,283.3 L303.4,283.2 L302.8,282.7 L302.5,282.1 L301.8,282.3 L301.7,283.1 L302.3,283.8 L302.1,284.9 L302.6,285.0 L302.1,286.1 L301.5,286.0 L301.2,286.5 L300.5,286.7 L300.3,285.9 L299.9,286.6 L298.6,286.1 L298.5,286.9 L295.8,287.4 L293.3,287.0 L292.6,286.4 L291.3,286.6 L289.8,287.8 L291.3,289.3 L289.5,291.2 L289.7,291.7Z"/>
                  <path d="M386.4,156.1 L386.2,157.5 L386.4,158.2 L387.2,157.4 L387.7,157.5 L387.4,158.9 L387.9,159.4 L387.8,160.1 L388.9,160.2 L390.1,160.5 L390.9,159.3 L391.5,160.3 L392.0,160.7 L392.9,160.1 L393.2,160.4 L394.4,159.6 L396.1,159.8 L396.0,157.7 L396.5,157.7 L398.1,158.2 L399.0,157.5 L399.6,158.0 L400.0,158.6 L399.7,159.1 L399.8,159.9 L400.8,159.6 L400.7,161.0 L399.2,161.6 L399.1,161.0 L398.1,161.4 L398.1,162.1 L398.6,162.8 L399.7,165.7 L400.5,165.3 L400.6,166.4 L400.4,167.5 L400.6,168.0 L402.3,167.8 L402.7,167.3 L402.4,166.7 L402.6,165.7 L403.0,165.9 L404.1,165.7 L404.6,165.1 L404.7,164.4 L407.1,164.7 L407.8,164.9 L408.1,166.3 L407.7,167.0 L407.5,169.6 L406.9,171.8 L407.4,173.1 L407.3,174.4 L407.6,175.2 L408.5,175.6 L408.2,176.3 L407.6,176.3 L407.7,177.4 L405.6,178.6 L406.1,179.4 L407.9,180.4 L408.6,182.0 L409.2,182.4 L409.6,183.4 L411.3,185.4 L410.5,187.8 L410.6,188.4 L411.2,189.1 L411.4,189.9 L412.2,189.5 L412.6,189.8 L412.0,191.7 L412.0,192.8 L412.8,193.4 L413.3,194.4 L415.2,194.8 L415.5,195.6 L416.2,195.2 L417.0,195.5 L417.6,195.2 L418.0,195.8 L419.0,195.2 L419.9,195.3 L420.7,195.9 L420.6,196.7 L421.2,197.0 L421.5,196.1 L422.2,195.9 L422.9,196.8 L424.2,196.7 L426.9,197.8 L428.8,197.4 L430.2,196.6 L432.0,197.1 L432.4,197.5 L432.9,198.7 L433.9,199.9 L434.7,199.8 L435.9,200.1 L436.2,200.4 L436.9,202.0 L436.3,203.0 L436.5,203.4 L437.5,203.7 L439.2,202.4 L439.6,202.4 L440.2,203.3 L438.6,205.7 L437.8,207.3 L437.2,207.2 L435.6,208.6 L435.5,209.4 L435.9,211.2 L435.4,212.0 L433.8,211.9 L432.9,212.1 L433.3,213.1 L431.3,214.2 L430.8,214.9 L431.4,215.7 L432.5,216.5 L431.6,217.5 L430.6,216.8 L430.3,217.1 L429.4,216.9 L428.9,218.3 L429.0,219.0 L429.6,219.4 L429.3,220.4 L426.8,220.1 L426.2,219.2 L424.7,219.2 L423.8,220.1 L422.7,219.1 L422.2,218.0 L420.7,217.8 L419.5,217.0 L419.6,216.3 L418.6,217.0 L417.3,218.1 L418.0,219.2 L418.1,220.5 L417.9,221.7 L417.5,222.6 L416.3,223.2 L415.9,223.9 L415.2,223.2 L415.2,222.5 L413.7,222.4 L412.3,221.9 L411.0,220.9 L410.8,220.1 L409.2,220.2 L408.6,221.2 L407.9,223.0 L406.4,224.2 L405.4,224.2 L404.5,224.1 L403.9,224.8 L402.6,225.6 L402.1,225.4 L401.2,224.2 L401.6,223.6 L401.5,222.5 L400.3,222.4 L399.8,223.8 L398.2,225.0 L397.6,224.6 L396.2,224.3 L393.3,223.1 L393.0,224.2 L392.6,224.8 L391.5,225.1 L389.7,224.9 L389.2,224.6 L388.5,223.7 L387.5,223.7 L387.3,224.1 L386.3,224.6 L385.9,224.4 L385.4,225.3 L384.4,226.2 L383.5,225.9 L383.4,225.9 L381.7,227.0 L380.3,227.5 L380.1,228.8 L379.6,229.6 L380.2,230.4 L380.8,230.8 L380.5,232.1 L381.6,232.7 L381.4,233.2 L381.8,234.0 L381.1,234.3 L381.2,234.8 L380.6,236.0 L378.6,236.8 L378.6,237.5 L379.8,237.9 L381.0,238.1 L382.1,237.6 L383.3,238.6 L383.4,240.0 L383.8,241.0 L383.4,241.6 L382.5,242.2 L381.2,242.3 L381.1,243.0 L379.6,243.0 L379.2,243.7 L378.6,243.8 L378.1,244.9 L378.7,245.5 L379.8,245.8 L380.1,247.4 L381.3,247.7 L381.0,248.5 L380.3,248.8 L380.2,250.1 L379.1,250.0 L379.1,250.7 L378.4,250.8 L378.3,252.0 L377.9,252.3 L376.6,252.4 L376.0,252.1 L375.6,252.6 L374.5,252.9 L374.0,255.0 L372.9,254.6 L372.7,253.1 L372.3,253.3 L369.3,251.5 L368.6,251.8 L368.8,252.5 L368.8,254.0 L368.0,254.2 L367.6,254.6 L368.1,255.2 L369.2,255.7 L369.5,257.3 L370.6,257.7 L371.4,258.2 L374.3,259.0 L375.6,258.4 L377.6,258.9 L378.0,259.7 L378.0,260.7 L379.1,262.8 L378.7,264.5 L379.1,265.3 L380.0,265.8 L379.0,267.6 L378.9,268.9 L378.4,269.7 L377.5,269.2 L376.5,269.2 L375.5,270.4 L374.9,270.2 L374.6,271.2 L374.9,272.0 L375.9,271.6 L376.5,272.9 L377.2,273.5 L378.0,272.8 L378.7,274.0 L378.6,274.8 L377.8,275.9 L378.2,276.4 L377.6,277.9 L377.6,278.7 L375.9,279.4 L375.6,279.4 L375.8,280.1 L377.0,279.6 L377.0,280.1 L377.9,280.1 L378.9,280.6 L378.8,281.2 L377.9,281.5 L376.8,282.4 L376.8,283.1 L377.2,284.3 L376.9,284.4 L375.9,286.5 L375.6,288.2 L376.0,288.7 L375.8,289.8 L375.8,290.8 L376.4,291.6 L376.0,293.0 L376.1,294.3 L376.6,296.4 L376.0,296.7 L375.7,297.5 L374.1,299.1 L373.1,298.3 L372.6,298.7 L371.9,298.7 L371.2,296.9 L370.5,295.9 L369.2,296.6 L368.8,296.1 L367.5,296.9 L366.4,297.5 L366.0,297.3 L364.9,297.7 L364.2,298.3 L364.1,297.6 L363.4,297.4 L362.7,297.6 L361.4,299.2 L362.3,299.5 L360.3,300.9 L358.7,299.7 L357.4,299.4 L356.4,298.8 L356.4,297.5 L355.8,296.9 L355.0,296.9 L355.1,295.4 L345.4,295.8 L345.3,294.3 L345.7,294.1 L345.4,292.3 L344.5,292.1 L344.2,291.1 L342.4,290.0 L342.7,289.3 L343.6,288.2 L343.8,287.5 L343.2,287.1 L343.3,286.5 L341.4,285.7 L341.6,286.7 L341.4,288.6 L340.8,289.9 L339.1,289.8 L339.2,290.4 L338.0,290.9 L337.7,290.3 L338.1,289.7 L336.4,288.6 L336.5,288.4 L334.2,287.0 L332.3,290.1 L330.8,288.9 L329.5,288.3 L328.9,289.6 L329.5,289.8 L329.1,290.8 L327.8,290.2 L327.6,290.8 L326.8,290.4 L327.1,289.9 L324.5,288.7 L323.5,290.5 L323.1,289.8 L322.9,288.6 L323.7,288.0 L323.6,286.9 L322.4,285.8 L321.1,285.3 L319.9,284.3 L319.3,283.3 L317.8,283.2 L317.2,284.2 L316.2,284.3 L315.7,282.6 L315.2,282.0 L315.8,281.3 L314.1,279.9 L313.7,278.4 L314.6,278.0 L314.8,277.5 L314.4,276.5 L312.3,276.5 L312.3,276.2 L310.8,275.9 L310.3,274.1 L310.5,273.5 L311.0,272.0 L311.3,271.8 L311.5,270.5 L313.1,270.5 L313.5,269.6 L313.3,268.8 L313.9,267.6 L314.6,267.4 L315.7,266.5 L315.3,266.0 L315.1,265.0 L315.0,262.9 L314.1,263.1 L313.8,263.6 L312.4,263.6 L311.6,263.0 L311.7,261.7 L312.2,259.8 L312.5,259.0 L312.1,257.9 L311.2,258.1 L309.9,253.4 L308.8,253.2 L308.5,252.8 L309.0,252.1 L308.8,251.4 L309.3,250.4 L311.1,249.7 L311.7,249.0 L312.5,248.8 L313.4,249.6 L314.7,249.4 L315.1,250.1 L315.1,250.9 L315.7,250.7 L318.7,251.4 L319.8,251.5 L320.0,250.7 L320.6,250.1 L321.4,250.0 L321.7,248.4 L321.5,247.0 L321.6,246.4 L320.7,245.4 L318.9,244.7 L318.0,244.6 L318.1,243.6 L318.6,243.0 L317.8,241.1 L318.0,239.8 L316.9,239.7 L316.8,239.2 L317.7,239.0 L319.2,239.4 L319.8,239.1 L319.7,238.5 L318.8,238.5 L318.4,236.8 L318.4,236.0 L318.1,235.2 L317.2,234.8 L317.4,233.9 L314.5,233.4 L314.0,233.8 L313.0,233.0 L313.6,232.2 L312.9,230.6 L311.7,230.3 L311.1,229.6 L310.4,229.8 L309.9,230.6 L309.1,230.6 L308.7,229.8 L307.9,231.2 L307.3,230.8 L307.0,231.5 L305.6,231.3 L305.0,231.6 L304.9,230.3 L303.9,230.0 L302.9,230.3 L302.8,230.6 L301.7,230.3 L301.6,229.1 L304.1,227.8 L303.1,227.5 L303.1,226.7 L302.3,225.6 L300.9,224.9 L299.6,223.3 L299.7,223.0 L299.9,223.2 L302.2,221.1 L301.8,219.5 L303.0,219.4 L302.9,217.6 L302.4,217.2 L302.2,216.4 L300.3,216.2 L299.7,216.5 L299.1,216.0 L298.6,215.2 L298.0,214.7 L297.7,213.7 L296.1,214.3 L294.4,213.6 L294.2,212.4 L293.0,210.9 L293.1,210.1 L293.9,207.5 L293.6,207.1 L291.8,206.4 L292.1,205.6 L290.9,205.2 L289.4,205.2 L288.4,204.8 L287.8,203.6 L287.1,203.1 L287.0,202.3 L286.2,202.5 L284.5,201.6 L283.4,203.0 L284.2,203.2 L282.7,204.5 L281.8,204.3 L281.2,203.6 L279.1,205.0 L277.4,205.5 L277.0,204.8 L275.6,204.0 L275.4,204.6 L274.3,205.7 L273.2,205.9 L272.5,205.7 L272.0,205.1 L272.5,204.3 L271.5,204.1 L271.6,203.2 L269.8,203.1 L268.4,202.8 L267.8,202.2 L267.0,202.0 L266.3,201.2 L265.6,200.8 L266.1,199.8 L264.3,199.0 L263.9,198.5 L262.5,199.5 L261.6,199.3 L261.5,198.9 L259.2,199.1 L258.8,198.7 L257.5,198.6 L257.8,197.7 L257.2,196.8 L256.3,197.1 L256.1,196.4 L256.9,196.1 L256.5,195.3 L255.9,195.2 L255.8,194.2 L256.1,193.8 L256.3,192.8 L255.8,192.1 L254.6,191.9 L254.5,191.4 L254.9,190.9 L255.7,190.8 L256.4,191.2 L257.4,190.9 L258.0,189.5 L258.3,188.5 L259.2,188.4 L259.0,187.9 L259.9,186.5 L260.7,186.3 L260.6,185.5 L259.3,185.4 L257.5,184.8 L258.9,184.3 L258.4,181.9 L260.2,181.7 L261.0,181.1 L261.0,180.1 L262.2,179.4 L262.5,178.5 L261.3,177.8 L259.7,177.2 L260.3,176.2 L261.4,175.9 L261.9,174.7 L262.0,173.8 L262.7,173.9 L263.0,173.1 L263.0,172.2 L263.5,171.3 L263.2,170.8 L263.2,169.9 L263.9,169.7 L264.4,170.1 L265.7,170.6 L265.7,171.1 L267.3,171.4 L266.2,170.5 L266.1,168.8 L267.0,168.4 L266.9,168.0 L265.9,167.9 L266.4,166.9 L265.8,165.7 L264.7,165.1 L264.3,164.5 L264.4,163.7 L263.8,163.7 L263.0,162.9 L262.3,161.5 L262.6,160.9 L263.4,161.2 L264.7,160.1 L264.3,159.7 L264.8,159.0 L266.5,158.8 L265.7,158.0 L265.4,157.4 L264.1,155.8 L264.4,155.1 L263.7,154.4 L263.4,153.4 L265.3,153.0 L265.4,153.3 L267.4,153.6 L268.0,152.6 L270.0,152.0 L270.2,151.5 L268.9,150.3 L269.3,149.8 L270.3,150.0 L271.4,150.5 L272.0,150.4 L272.6,151.0 L273.2,151.0 L273.5,151.7 L274.3,152.1 L274.8,151.4 L274.2,149.2 L274.5,148.6 L274.8,147.3 L274.6,146.2 L274.0,145.8 L273.3,145.8 L273.0,144.5 L273.1,142.9 L273.0,141.9 L272.7,141.6 L272.7,140.5 L274.1,140.6 L275.3,139.5 L276.1,139.4 L276.2,138.4 L277.2,137.6 L278.6,137.1 L279.4,136.8 L282.3,137.9 L282.3,136.2 L281.7,135.4 L282.7,134.0 L283.8,133.3 L284.4,132.7 L285.9,132.5 L286.0,133.9 L285.6,135.1 L286.6,135.6 L287.9,136.4 L288.3,137.2 L289.0,137.7 L291.0,137.0 L291.7,137.4 L291.9,136.5 L293.7,136.6 L293.8,136.0 L295.0,135.8 L295.7,136.3 L296.1,137.5 L297.3,137.8 L297.9,137.2 L298.0,136.8 L301.4,137.4 L302.4,137.9 L303.3,137.8 L304.0,138.0 L306.2,137.5 L307.0,137.4 L307.4,136.8 L305.7,136.4 L307.8,135.1 L308.5,134.2 L309.3,133.5 L310.4,131.7 L312.0,129.8 L312.9,130.8 L314.4,130.7 L314.4,131.2 L315.3,131.6 L315.5,130.9 L317.0,131.3 L317.6,130.3 L318.2,130.9 L319.3,130.4 L319.4,129.4 L321.6,129.4 L322.0,128.4 L322.4,128.1 L323.3,126.3 L323.3,125.5 L324.3,125.7 L324.3,125.0 L325.4,125.4 L326.1,124.7 L326.7,124.5 L328.3,125.2 L329.6,125.4 L330.8,126.3 L330.9,125.6 L331.6,125.4 L331.9,124.1 L333.0,124.5 L334.1,124.0 L334.2,123.0 L333.7,122.4 L333.9,122.0 L335.5,121.5 L336.8,121.6 L337.6,121.0 L338.1,121.4 L339.5,121.8 L340.5,123.1 L341.5,122.6 L342.4,121.9 L343.5,121.5 L344.6,120.7 L344.8,119.8 L346.5,118.6 L347.6,118.1 L348.3,117.5 L350.0,116.5 L351.5,116.0 L354.3,116.6 L356.1,116.4 L356.6,115.7 L357.8,115.9 L359.0,115.3 L358.5,114.3 L359.4,114.5 L358.9,113.2 L362.9,112.1 L364.4,112.1 L365.2,111.9 L367.5,111.9 L367.1,112.2 L367.0,114.0 L367.3,114.2 L367.2,115.3 L368.0,115.9 L367.9,117.0 L368.1,117.5 L369.3,118.3 L369.8,119.7 L369.4,120.2 L370.9,120.5 L370.7,120.9 L369.8,121.5 L369.4,121.9 L370.2,122.9 L371.3,123.0 L372.1,124.2 L373.3,124.7 L372.2,125.4 L371.7,124.7 L370.0,124.9 L369.2,126.3 L369.0,127.0 L369.7,127.4 L369.6,128.6 L368.1,128.3 L368.5,129.0 L369.8,130.4 L369.9,131.2 L370.5,132.1 L370.2,132.7 L370.7,133.8 L370.7,134.3 L371.0,135.1 L372.0,136.0 L371.3,137.3 L371.4,138.1 L373.4,140.3 L374.4,140.0 L375.7,140.1 L376.9,140.0 L378.5,142.7 L379.3,143.2 L379.3,143.8 L379.9,144.2 L381.1,144.0 L381.8,143.4 L381.9,143.4 L382.5,142.7 L383.2,143.2 L382.4,144.1 L382.3,145.2 L383.1,145.2 L383.2,145.8 L381.8,145.7 L381.1,145.2 L380.9,146.6 L380.1,147.0 L380.4,148.1 L380.9,148.7 L382.6,149.8 L383.8,149.7 L384.3,150.3 L385.0,150.3 L386.1,150.8 L386.0,151.6 L386.8,152.5 L386.6,153.2 L387.4,153.7 L386.6,154.5 L385.6,154.9 L386.4,156.1Z"/>
                  <path d="M380.8,230.8 L380.2,230.4 L379.6,229.6 L380.1,228.8 L380.3,227.5 L381.7,227.0 L383.4,225.9 L383.5,225.9 L384.4,226.2 L385.4,225.3 L385.9,224.4 L386.3,224.6 L387.3,224.1 L387.5,223.7 L388.5,223.7 L389.2,224.6 L389.7,224.9 L391.5,225.1 L392.6,224.8 L393.0,224.2 L393.3,223.1 L396.2,224.3 L397.6,224.6 L398.2,225.0 L399.8,223.8 L400.3,222.4 L401.5,222.5 L401.6,223.6 L401.2,224.2 L402.1,225.4 L402.6,225.6 L403.9,224.8 L404.5,224.1 L405.4,224.2 L406.4,224.2 L407.9,223.0 L408.6,221.2 L409.2,220.2 L410.8,220.1 L411.0,220.9 L412.3,221.9 L413.7,222.4 L415.2,222.5 L415.2,223.2 L415.9,223.9 L416.3,223.2 L417.5,222.6 L417.9,221.7 L418.1,220.5 L418.0,219.2 L417.3,218.1 L418.6,217.0 L419.6,216.3 L419.5,217.0 L420.7,217.8 L422.2,218.0 L422.7,219.1 L423.8,220.1 L424.7,219.2 L426.2,219.2 L426.8,220.1 L429.3,220.4 L429.6,219.4 L429.0,219.0 L428.9,218.3 L429.4,216.9 L430.3,217.1 L430.6,216.8 L431.6,217.5 L432.5,216.5 L431.4,215.7 L430.8,214.9 L431.3,214.2 L433.3,213.1 L432.9,212.1 L433.8,211.9 L435.4,212.0 L435.9,211.2 L435.5,209.4 L435.6,208.6 L437.2,207.2 L437.8,207.3 L438.6,205.7 L440.2,203.3 L441.5,203.9 L442.6,203.6 L443.7,204.0 L443.7,205.1 L443.9,205.6 L443.3,207.0 L443.6,207.7 L445.5,208.5 L447.1,208.8 L448.2,208.4 L448.4,209.1 L450.2,209.5 L450.9,209.2 L451.6,209.4 L451.8,210.3 L453.2,210.4 L453.2,211.6 L453.9,211.3 L454.3,212.0 L454.8,212.0 L455.4,211.3 L456.0,211.2 L456.6,212.0 L457.3,211.4 L458.4,212.0 L457.6,212.4 L457.4,213.4 L458.1,214.0 L458.5,213.8 L459.0,215.1 L460.0,215.5 L460.1,216.1 L461.2,216.4 L461.9,216.1 L462.8,216.3 L463.2,216.9 L463.4,217.8 L463.8,218.3 L464.8,218.7 L464.9,219.4 L465.4,219.9 L465.0,220.4 L465.3,220.8 L465.8,222.3 L465.9,223.5 L466.5,225.1 L467.1,225.9 L467.0,226.3 L466.0,226.9 L466.1,228.0 L465.2,228.1 L464.7,228.9 L465.0,230.5 L463.8,231.0 L463.4,231.6 L463.8,232.1 L463.7,232.7 L464.3,233.0 L464.6,233.6 L464.0,234.0 L463.8,234.7 L463.2,234.8 L463.3,236.0 L463.7,236.3 L462.9,238.1 L463.8,238.2 L464.8,240.3 L464.2,241.2 L464.3,242.0 L463.4,242.8 L462.7,242.8 L462.3,243.7 L461.6,243.9 L461.0,243.6 L460.2,244.6 L459.5,245.1 L459.3,246.0 L459.7,246.5 L460.3,246.5 L460.8,248.2 L460.4,249.5 L460.8,251.0 L460.2,251.2 L459.9,251.7 L460.0,253.2 L460.3,254.3 L460.1,255.9 L461.3,257.8 L461.3,258.8 L461.8,259.2 L461.2,259.7 L461.1,261.0 L462.2,261.4 L462.5,260.9 L464.0,262.5 L463.7,262.8 L463.8,263.9 L464.6,264.4 L466.2,265.0 L466.2,265.5 L465.4,266.7 L465.3,267.8 L466.0,267.8 L466.8,268.2 L466.5,268.9 L467.6,270.5 L467.2,271.4 L466.7,272.8 L466.8,273.4 L466.1,274.2 L465.5,274.5 L465.5,275.9 L464.6,276.7 L464.7,278.2 L465.3,278.9 L465.0,279.6 L465.5,279.9 L466.9,279.7 L467.8,280.3 L468.1,281.0 L468.9,281.9 L468.8,283.0 L468.9,283.9 L469.7,284.7 L469.5,285.4 L470.2,285.7 L470.9,286.6 L471.6,286.7 L471.8,287.4 L472.8,287.8 L473.1,288.8 L473.9,289.5 L475.2,289.8 L475.8,291.2 L474.8,292.4 L475.1,292.8 L475.2,294.3 L475.8,294.7 L476.1,295.9 L476.8,296.2 L477.8,296.2 L478.1,300.0 L478.9,300.8 L478.3,301.0 L479.9,302.6 L480.4,303.5 L480.6,305.7 L481.4,306.1 L482.0,307.5 L483.1,308.3 L483.9,309.3 L484.8,310.2 L486.2,310.4 L486.9,311.4 L487.4,311.5 L488.2,312.1 L489.0,311.9 L488.9,312.8 L488.4,313.5 L486.7,314.5 L484.2,314.7 L483.2,314.1 L482.4,314.1 L481.5,314.4 L480.3,316.1 L480.4,317.1 L480.0,317.7 L480.5,318.4 L480.5,319.1 L481.3,319.5 L482.5,319.4 L483.2,319.8 L482.6,320.5 L483.0,321.3 L482.9,323.0 L484.2,323.6 L485.5,323.3 L485.6,324.1 L485.5,325.0 L485.0,326.2 L486.2,327.3 L486.3,328.6 L485.9,329.1 L486.5,329.9 L486.4,331.3 L486.8,332.6 L486.4,334.3 L486.4,335.5 L486.1,336.5 L485.4,340.0 L483.8,343.7 L483.7,344.5 L482.3,345.2 L481.9,347.0 L479.2,346.8 L476.5,347.3 L472.6,347.5 L471.4,347.8 L468.9,348.8 L467.8,349.8 L467.2,351.5 L467.0,353.1 L464.7,354.0 L464.3,355.2 L463.4,355.6 L461.9,358.2 L460.3,359.2 L460.1,358.7 L459.4,358.4 L458.5,358.5 L458.7,357.2 L458.3,356.6 L458.9,356.3 L458.0,355.6 L456.6,356.0 L456.0,355.3 L454.6,355.4 L453.5,355.1 L455.0,352.1 L453.8,351.6 L453.5,350.7 L452.2,350.3 L452.0,349.7 L452.4,349.2 L451.6,348.8 L451.8,347.9 L452.6,347.5 L451.2,347.1 L451.0,347.7 L449.3,348.5 L448.7,349.4 L447.4,349.7 L446.1,350.6 L444.5,349.1 L442.8,348.2 L438.3,353.3 L437.2,353.8 L436.6,355.1 L434.9,356.6 L433.3,356.0 L431.4,356.2 L428.3,355.5 L427.1,356.0 L425.5,355.4 L423.3,355.5 L422.1,355.3 L421.4,355.4 L420.6,355.9 L420.0,356.2 L419.7,356.0 L419.0,355.6 L418.3,355.7 L416.9,355.1 L416.7,355.3 L415.7,354.9 L415.2,354.7 L415.2,354.7 L415.2,354.7 L415.2,354.7 L415.1,353.6 L415.8,353.0 L418.1,352.5 L419.0,352.1 L419.0,351.2 L418.3,351.3 L418.2,351.4 L418.2,351.4 L418.1,351.4 L418.1,351.4 L418.1,351.4 L417.8,351.3 L417.5,351.3 L416.9,351.1 L416.9,351.1 L416.8,351.1 L416.9,351.1 L416.8,351.1 L416.0,351.2 L414.6,351.8 L414.0,351.3 L413.2,351.0 L412.4,351.2 L412.1,351.2 L411.5,351.4 L410.7,352.0 L410.2,351.5 L409.2,351.3 L407.7,350.1 L407.8,348.8 L406.9,348.5 L407.0,347.9 L405.5,348.0 L405.8,346.1 L407.4,346.4 L407.6,345.4 L408.1,345.3 L407.9,343.9 L407.4,343.1 L408.3,342.9 L408.4,343.3 L409.9,343.7 L410.5,343.5 L411.0,343.0 L411.5,344.3 L412.2,343.9 L412.9,342.6 L414.2,342.0 L414.2,341.5 L413.3,341.5 L411.3,342.1 L410.6,341.6 L410.6,341.3 L411.9,341.4 L413.4,340.6 L413.7,339.9 L413.4,339.5 L412.7,339.8 L412.0,339.8 L411.8,339.0 L411.7,337.4 L411.0,337.6 L410.6,337.3 L411.0,336.5 L411.7,336.2 L411.8,334.7 L411.0,333.9 L410.3,333.8 L409.2,334.0 L408.5,333.6 L408.4,333.2 L406.5,333.8 L405.6,333.7 L405.2,333.0 L404.4,332.7 L404.2,332.3 L403.2,331.9 L402.8,331.5 L401.5,331.2 L400.8,331.3 L399.6,330.8 L398.0,331.0 L397.9,330.0 L396.7,328.0 L395.9,327.5 L394.2,327.5 L393.1,327.0 L394.1,325.8 L394.5,324.9 L394.2,324.4 L395.7,320.9 L395.5,320.2 L393.3,319.4 L393.9,317.5 L392.6,317.2 L390.6,316.2 L388.5,315.7 L388.0,316.5 L386.7,317.6 L385.8,318.5 L384.7,318.2 L383.6,319.4 L382.2,319.2 L381.0,318.8 L380.0,318.8 L380.3,318.1 L380.0,317.4 L379.5,317.0 L379.7,316.2 L379.6,315.6 L378.7,314.6 L378.4,313.9 L378.7,312.8 L378.0,310.9 L377.9,309.2 L377.0,306.9 L377.0,305.4 L377.4,304.7 L377.3,303.6 L377.5,303.0 L377.4,301.2 L376.6,300.2 L376.7,299.4 L377.4,298.9 L377.7,298.3 L377.5,297.4 L376.6,296.4 L376.1,294.3 L376.0,293.0 L376.4,291.6 L375.8,290.8 L375.8,289.8 L376.0,288.7 L375.6,288.2 L375.9,286.5 L376.9,284.4 L377.2,284.3 L376.8,283.1 L376.8,282.4 L377.9,281.5 L378.8,281.2 L378.9,280.6 L377.9,280.1 L377.0,280.1 L377.0,279.6 L375.8,280.1 L375.6,279.4 L375.9,279.4 L377.6,278.7 L377.6,277.9 L378.2,276.4 L377.8,275.9 L378.6,274.8 L378.7,274.0 L378.0,272.8 L377.2,273.5 L376.5,272.9 L375.9,271.6 L374.9,272.0 L374.6,271.2 L374.9,270.2 L375.5,270.4 L376.5,269.2 L377.5,269.2 L378.4,269.7 L378.9,268.9 L379.0,267.6 L380.0,265.8 L379.1,265.3 L378.7,264.5 L379.1,262.8 L378.0,260.7 L378.0,259.7 L377.6,258.9 L375.6,258.4 L374.3,259.0 L371.4,258.2 L370.6,257.7 L369.5,257.3 L369.2,255.7 L368.1,255.2 L367.6,254.6 L368.0,254.2 L368.8,254.0 L368.8,252.5 L368.6,251.8 L369.3,251.5 L372.3,253.3 L372.7,253.1 L372.9,254.6 L374.0,255.0 L374.5,252.9 L375.6,252.6 L376.0,252.1 L376.6,252.4 L377.9,252.3 L378.3,252.0 L378.4,250.8 L379.1,250.7 L379.1,250.0 L380.2,250.1 L380.3,248.8 L381.0,248.5 L381.3,247.7 L380.1,247.4 L379.8,245.8 L378.7,245.5 L378.1,244.9 L378.6,243.8 L379.2,243.7 L379.6,243.0 L381.1,243.0 L381.2,242.3 L382.5,242.2 L383.4,241.6 L383.8,241.0 L383.4,240.0 L383.3,238.6 L382.1,237.6 L381.0,238.1 L379.8,237.9 L378.6,237.5 L378.6,236.8 L380.6,236.0 L381.2,234.8 L381.1,234.3 L381.8,234.0 L381.4,233.2 L381.6,232.7 L380.5,232.1 L380.8,230.8Z"/>
                  <path d="M48.8,274.0 L49.0,273.1 L48.4,272.3 L48.5,271.1 L48.1,270.4 L48.5,270.0 L48.4,269.2 L48.8,268.1 L48.5,267.8 L47.5,265.8 L46.5,265.7 L46.3,265.0 L45.2,265.0 L44.7,264.5 L43.8,264.7 L43.0,264.4 L41.4,263.0 L41.1,263.2 L40.3,262.3 L38.9,262.2 L37.4,261.7 L37.1,261.2 L37.0,259.8 L36.2,258.8 L37.0,257.9 L37.1,257.2 L38.1,256.3 L38.8,255.4 L38.7,254.6 L38.3,253.9 L38.3,252.5 L38.1,252.0 L38.4,251.3 L37.9,250.1 L35.7,247.9 L35.1,247.6 L34.5,246.5 L34.1,246.3 L33.6,245.2 L33.9,243.8 L33.3,243.6 L33.5,242.7 L33.1,241.1 L32.4,240.5 L31.1,240.2 L30.4,239.1 L30.4,237.7 L31.5,236.2 L32.0,236.2 L33.4,234.9 L33.5,234.1 L34.4,233.8 L35.4,232.9 L35.2,232.4 L35.9,231.1 L35.8,230.6 L36.6,229.8 L36.7,228.9 L36.0,227.9 L36.0,226.9 L36.4,226.7 L36.7,225.7 L36.3,225.0 L37.7,223.5 L38.1,222.1 L37.8,221.2 L38.4,220.7 L38.5,220.1 L37.6,218.8 L36.8,218.1 L35.4,217.5 L34.8,216.4 L34.9,215.7 L34.7,214.3 L35.8,212.7 L35.9,212.0 L35.0,210.6 L35.1,210.1 L35.8,209.5 L36.2,208.5 L36.4,207.2 L35.3,205.7 L33.7,205.2 L33.1,205.2 L31.7,204.5 L30.5,204.3 L30.3,203.7 L29.7,203.2 L29.7,202.5 L30.1,201.8 L29.6,200.5 L29.0,200.0 L29.1,199.4 L28.6,198.5 L28.6,196.6 L27.7,195.2 L27.8,194.6 L28.3,193.6 L28.3,192.2 L29.0,191.6 L29.9,191.5 L31.3,189.7 L31.4,188.9 L32.4,187.8 L32.4,186.9 L31.6,186.4 L31.1,184.9 L31.7,182.6 L32.8,181.6 L32.5,181.1 L31.5,180.4 L31.3,179.5 L30.7,178.5 L29.1,177.4 L30.0,176.8 L32.7,174.8 L33.8,175.6 L33.7,176.1 L35.2,176.8 L36.7,177.0 L37.2,176.2 L37.9,175.7 L38.1,174.3 L39.4,173.0 L40.1,171.9 L40.1,171.3 L40.4,168.8 L40.0,167.7 L40.9,166.7 L41.1,166.1 L42.8,165.7 L42.9,165.2 L43.6,164.7 L43.6,163.1 L43.1,162.1 L44.0,161.4 L43.6,160.4 L44.7,160.4 L45.0,161.3 L45.7,161.1 L46.2,160.4 L45.8,159.8 L44.8,159.4 L45.5,157.6 L46.1,157.2 L48.0,158.6 L49.0,159.0 L49.2,160.3 L49.6,160.8 L50.8,159.7 L51.9,159.3 L52.4,160.7 L53.3,161.2 L53.5,161.7 L63.8,157.4 L66.0,156.4 L64.6,154.3 L64.1,153.8 L63.7,152.9 L63.9,151.9 L66.9,153.1 L67.8,152.8 L68.6,151.1 L69.5,150.6 L70.2,150.0 L71.4,149.5 L72.4,149.6 L73.4,148.9 L75.6,148.3 L76.3,147.6 L76.7,147.8 L77.6,149.7 L78.3,150.1 L79.0,149.8 L79.7,150.0 L80.7,150.4 L81.8,149.5 L83.3,148.8 L84.6,149.6 L85.7,149.4 L87.0,148.7 L87.4,147.5 L86.9,147.1 L87.9,145.4 L88.8,145.2 L88.1,144.2 L89.5,143.7 L89.0,142.2 L91.3,141.5 L90.8,139.9 L92.1,139.4 L92.3,139.6 L93.8,139.2 L93.9,139.5 L97.1,140.2 L97.3,141.6 L97.8,141.8 L97.8,143.1 L96.3,145.5 L95.9,145.6 L96.0,147.0 L95.8,148.6 L96.0,150.5 L96.2,151.0 L96.2,152.1 L97.1,153.8 L97.0,155.6 L96.5,156.4 L97.1,157.8 L96.7,158.9 L96.8,159.5 L96.2,159.9 L95.5,159.9 L95.7,160.7 L94.4,161.6 L94.2,160.9 L93.3,161.3 L92.9,163.0 L93.4,163.6 L94.9,163.9 L95.1,165.1 L94.5,165.2 L95.3,167.2 L95.6,168.3 L95.4,169.4 L94.2,169.9 L93.6,170.5 L92.5,170.5 L90.2,171.2 L89.0,170.9 L88.2,171.0 L88.5,172.0 L89.1,173.3 L89.1,174.1 L87.6,174.6 L87.6,175.3 L87.3,176.4 L88.2,176.5 L88.2,177.2 L87.7,177.9 L88.3,178.9 L89.8,180.7 L89.6,181.9 L90.0,181.9 L91.3,183.6 L90.1,184.8 L91.5,186.2 L91.7,186.8 L92.5,188.0 L92.9,189.4 L93.0,191.1 L92.1,191.4 L92.2,191.8 L90.7,192.3 L89.9,191.9 L90.1,191.2 L89.7,190.7 L88.7,191.3 L89.2,192.6 L89.8,193.1 L90.5,194.4 L91.4,194.5 L91.8,194.9 L93.1,195.1 L93.3,196.0 L92.7,196.8 L92.7,197.7 L92.4,198.3 L92.7,199.6 L92.4,200.2 L92.9,201.5 L92.2,202.5 L92.4,203.5 L91.0,205.2 L91.4,207.9 L90.8,209.0 L91.4,210.2 L91.4,211.7 L91.3,212.4 L92.4,212.8 L92.5,213.4 L92.1,215.3 L91.1,216.0 L90.8,216.6 L90.1,216.8 L91.3,218.7 L92.2,218.6 L93.2,219.4 L94.3,220.0 L95.2,218.5 L96.5,219.0 L96.6,219.5 L97.5,220.2 L97.5,220.5 L98.3,221.1 L97.7,221.4 L97.8,222.6 L96.4,222.7 L96.4,223.9 L96.8,225.4 L98.2,225.6 L98.5,226.7 L101.0,227.1 L101.8,226.6 L103.1,225.6 L103.9,225.9 L104.4,227.4 L104.6,228.8 L104.0,229.0 L104.3,229.6 L104.1,230.5 L104.7,231.0 L103.3,232.7 L103.2,233.3 L104.9,233.4 L106.2,234.2 L107.3,234.1 L107.7,235.1 L108.4,235.3 L109.1,234.8 L109.8,233.9 L110.2,232.9 L111.1,233.3 L111.5,233.0 L113.4,234.0 L114.9,234.4 L114.1,235.2 L115.2,236.2 L116.6,237.1 L116.3,237.7 L117.3,237.7 L117.0,239.1 L117.3,240.3 L116.7,241.3 L118.0,241.8 L117.6,242.7 L117.0,243.1 L116.5,242.9 L116.1,243.5 L116.2,244.2 L115.4,245.5 L115.8,246.1 L114.9,247.1 L114.7,247.6 L113.7,248.1 L112.6,248.4 L112.4,249.1 L111.0,249.5 L110.7,250.2 L110.1,249.3 L109.5,249.2 L109.0,248.4 L108.5,247.3 L107.0,247.1 L106.3,247.6 L105.7,247.4 L106.6,243.4 L106.0,243.7 L104.4,243.8 L102.4,243.1 L101.5,242.3 L99.7,241.8 L99.7,241.0 L98.9,240.5 L97.8,240.3 L96.4,241.3 L96.7,242.0 L97.2,242.3 L97.5,243.0 L96.6,243.1 L96.9,244.6 L96.2,245.0 L95.8,245.7 L94.7,245.4 L94.5,246.0 L93.9,246.3 L92.9,246.0 L92.5,246.7 L91.7,247.1 L91.6,248.9 L90.5,249.7 L90.1,250.4 L90.5,251.3 L91.1,251.6 L91.0,252.2 L90.3,252.5 L89.9,253.1 L90.1,254.3 L89.2,255.7 L89.6,258.0 L87.0,258.3 L85.7,259.3 L84.8,259.6 L83.9,259.3 L83.8,260.1 L84.3,260.3 L84.2,261.8 L84.3,262.3 L83.7,262.7 L82.6,262.7 L82.1,263.7 L82.1,264.5 L81.5,265.7 L80.5,265.2 L80.0,265.8 L80.0,266.4 L78.9,267.6 L78.2,267.3 L78.6,265.9 L73.5,261.5 L73.4,262.6 L72.6,262.5 L72.3,261.9 L71.0,261.5 L69.6,262.0 L69.1,261.3 L68.6,261.4 L68.5,262.2 L67.9,263.0 L67.7,264.2 L68.2,265.9 L66.3,265.6 L66.1,267.3 L66.5,267.7 L65.8,269.0 L65.7,269.9 L64.4,269.7 L63.9,268.9 L62.2,267.5 L61.7,267.7 L60.1,267.9 L59.7,267.5 L59.8,266.8 L59.0,266.4 L57.3,268.0 L56.3,268.4 L55.4,269.1 L54.1,269.3 L54.0,270.6 L53.5,271.5 L50.9,271.8 L50.5,273.3 L50.5,274.0 L48.8,274.0Z"/>
                  </g>
                </svg>
                <?php if ($hasMapPin) : ?>
                <span aria-hidden="true"
                  class="city-hero-map-pin"
                  style="left:<?php echo esc_attr($mapPinXPct); ?>%;top:<?php echo esc_attr($mapPinYPct); ?>%;"
                >
                  <span class="city-hero-map-pin-dot"></span>
                </span>
                <?php endif; ?>
              </div>
              <div class="city-hero-map-caption">
                <strong><?php echo esc_html($cityName); ?></strong>
                <span><?php echo esc_html("woj. " . $voivodeship); ?></span>
              </div>
              <small class="city-map-attribution">Dane: <a href="https://www.openstreetmap.org/copyright">© OpenStreetMap</a> (ODbL), GUGiK / PRG i PRNG.</small>
            </aside>
          </div>
        </div>
      </section>

      <section class="city-main city-wrap">
        <article class="city-content">
          <div class="city-prose">
            <?php echo $articleHtml; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
          </div>

          <section class="city-local-context" aria-labelledby="city-local-context-title">
            <h2 id="city-local-context-title">Na co zwrócić uwagę w marketingu?</h2>
            <p>
              Przy planowaniu kampanii i strony internetowej warto uwzględnić potrzeby klientów, możliwości firmy oraz zmiany popytu w ciągu roku.
            </p>
            <div class="city-local-context-grid">
              <div class="city-local-context-item">
                <strong>Najczęstsze wyzwanie</strong>
                <?php echo esc_html($localChallenge); ?>
              </div>
              <div class="city-local-context-item">
                <strong>Lokalna przewaga</strong>
                <?php echo esc_html($localAdvantage); ?>
              </div>
              <div class="city-local-context-item">
                <strong>Sezonowość popytu</strong>
                <?php echo esc_html($seasonalityAngle); ?>
              </div>
            </div>
          </section>

          <div class="city-band">
            <h2>Potrzebujesz planu działań dla <?php echo esc_html($cityName); ?>?</h2>
            <p>W 30 minut pokażę, co warto poprawić najpierw, żeby szybciej podnieść jakość leadów i skuteczność sprzedaży.</p>
          </div>

          <section class="city-conversion-form" id="formularz-miasto">
            <h2>Formularz kontaktowy dla <?php echo esc_html($cityName); ?></h2>
            <p>Zostaw krótki opis sytuacji firmy. Otrzymasz konkretną rekomendację działań dla rynku lokalnego.</p>
            <div class="city-conversion-links">
              <?php if ($contactPhone !== "" && $phoneHref !== "") : ?>
                <a href="<?php echo esc_url("tel:" . $phoneHref); ?>">Zadzwoń: <?php echo esc_html($contactPhone); ?></a>
              <?php endif; ?>
              <a href="<?php echo esc_url($contactEmailHref); ?>">Napisz: <?php echo esc_html($contactEmailDisplay); ?></a>
            </div>
            <?php
            echo upsellio_render_lead_form([
                "origin" => "miasto-single",
                "variant" => "compact",
                "submit_label" => "Wyślij zapytanie →",
                "redirect_url" => get_permalink($postId),
                "preset_message" => "Chcę omówić działania marketingowe dla firmy z miasta " . $cityName . ".",
                "hidden_service" => "Marketing lokalny " . $cityName,
                "css_class" => "city-lead-form",
            ]);
            ?>
          </section>

          <section class="city-local-links" aria-label="Linkowanie wewnętrzne lokalne">
            <h2>Sprawdź też inne miasta i tematy</h2>
            <div class="city-link-grid">
              <?php foreach ($cityInternalLinks as $cityLink) : ?>
                <a href="<?php echo esc_url($cityLink["url"]); ?>">
                  <?php echo esc_html("Marketing i strony WWW " . $cityLink["name"]); ?>
                </a>
              <?php endforeach; ?>
              <?php foreach ($definitionInternalLinks as $definitionLink) : ?>
                <a href="<?php echo esc_url($definitionLink["url"]); ?>">
                  <?php echo esc_html("Definicja: " . $definitionLink["name"]); ?>
                </a>
              <?php endforeach; ?>
            </div>
          </section>

          <?php if (!empty($faq)) : ?>
            <div class="city-faq">
              <h2>Najczęściej zadawane pytania</h2>
              <?php foreach ($faq as $item) : ?>
                <details class="city-faq-item up-faq-disclosure">
                  <summary><span><?php echo esc_html($item["q"]); ?></span></summary>
                  <p><?php echo esc_html($item["a"]); ?></p>
                </details>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </article>

        <aside class="city-side-card">
          <div class="city-side-title">Obsługiwane też w pobliżu</div>
          <div class="city-side-list">
            <?php foreach ($related as $item) : ?>
              <a class="city-side-link" href="<?php echo esc_url($item["url"]); ?>">
                <?php echo esc_html("Marketing i strony WWW " . $item["name"]); ?>
              </a>
            <?php endforeach; ?>
          </div>
          <div class="city-cta">
            <strong><?php echo esc_html($cta ?: ("Umów rozmowę dla " . $cityName)); ?></strong>
            <a class="city-btn" href="<?php echo esc_url(home_url("/#kontakt")); ?>">Umów bezpłatną rozmowę</a>
            <div class="city-cta-meta">
              <?php if ($contactPhone !== "" && $phoneHref !== "") : ?>
                <a href="<?php echo esc_url("tel:" . $phoneHref); ?>">Telefon: <?php echo esc_html($contactPhone); ?></a>
              <?php endif; ?>
              <a href="<?php echo esc_url($contactEmailHref); ?>">E-mail: <?php echo esc_html($contactEmailDisplay); ?></a>
            </div>
          </div>
        </aside>
      </section>
    </main>
    <?php
endwhile;

get_footer();

