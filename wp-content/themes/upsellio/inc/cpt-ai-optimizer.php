<?php

if (!defined("ABSPATH")) {
    exit;
}

/**
 * Konfiguracje per CPT — pola, prompty, funkcja apply.
 *
 * @return array<string, mixed>|null
 */
function upsellio_cpt_ai_get_config(string $post_type): ?array
{
    $defaults = [

        "miasto" => [
            "system" => <<<'DFLT'
Jesteś asystentem SEO Sebastiana Kelma — konsultanta marketingu B2B.
Tworzysz podstrony lokalne (miasto + usługa marketingowa) dla polskich firm B2B.

Szablon strony renderuje automatycznie: H1, lead, pills (Specjalizacja/Model),
sekcję lokalnego kontekstu (wyzwanie/przewaga/sezonowość), formularz kontaktowy,
inline CTA z linkami do innych miast i definicji, FAQ z meta pola, sidebar CTA.

Dlatego post_content = WYŁĄCZNIE artykuł merytoryczny (H2/H3/p/ul).
Nigdy nie wstawiaj do post_content: <h1>, sekcji FAQ, aside CTA, formularzy.

Zasady post_content:
- Pierwsze zdanie = fraza kluczowa + problem firm z tego miasta
- Min. 3 nagłówki H2 — przynajmniej jeden zawiera frazę kluczową
- Fraza kluczowa min. 3× w treści
- 2-4 linki wewnętrzne z katalogu [anchor](url)
- 1 link zewnętrzny (think.withgoogle.com lub semrush.com)
- Zero korporacyjnego żargonu, krótkie akapity 2-4 zdania

primary_query: max 40 znaków, bez deskryptorów liczbowych.
Zwracaj WYŁĄCZNIE poprawny JSON bez markdown.
DFLT,
            "user" => <<<'DFLT'
Optymalizujesz podstronę lokalną dla miasta: {city_name}.
Usługi: Google Ads, Meta Ads, strony internetowe B2B.
Kontekst firmy: {company_ctx}

UWAGA ARCHITEKTONICZNA: Szablon strony miasta renderuje H1, lead, FAQ, CTA, local-context
AUTOMATYCZNIE z meta pól — NIE umieszczaj ich w post_content.
post_content to WYŁĄCZNIE treść artykułu (H2/H3/p) bez H1, bez FAQ, bez CTA.

Istniejące dane (uzupełnij lub popraw):
Województwo: {voivodeship}
Wyzwanie lokalne: {local_challenge}
Atut rynku: {local_advantage}
Kąt rynku: {market_angle}
Fokus usług: {service_focus}
Sezonowość: {seasonality_angle}

(Stary HTML z edytora nie jest wstrzykiwany — buduj post_content wyłącznie dla miasta z nagłówka i pól powyżej.)

KATALOG LINKÓW WEWNĘTRZNYCH (użyj 2-4 linków wewnątrz post_content):
{catalog}

WYMAGANIA SEO — niespełnienie = błąd:
1. primary_query: maksymalnie 40 znaków, bez liczb i deskryptorów, np. "marketing {city_name}" lub "Google Ads {city_name}"
2. Pierwsze zdanie post_content musi zawierać primary_query dosłownie
3. Przynajmniej jeden H2 w post_content zawiera fragment primary_query
4. primary_query pojawia się minimum 3× w post_content
5. 1 link zewnętrzny do authority source w post_content (think.withgoogle.com lub semrush.com)

Zwróć JSON:
{
  "post_title": "Marketing {city_name} — Google Ads, Meta Ads, strony B2B",
  "post_content": "<TYLKO treść artykułu: H2/H3/p/ul. BEZ H1. BEZ sekcji FAQ. BEZ CTA aside. Min 700 słów. Fraza kluczowa w pierwszym zdaniu i w H2. 2-4 linki wewnętrzne [anchor](url) z katalogu. 1 link zewnętrzny.>",
  "post_excerpt": "<1-2 zdania: fraza kluczowa + korzyść dla firm z miasta>",
  "seo_title": "<45-60 znaków — primary_query na początku>",
  "meta_description": "<140-160 znaków — musi zawierać primary_query i nazwę miasta>",
  "primary_query": "<max 40 znaków np. 'Google Ads {city_name}' lub 'marketing {city_name}'>",
  "query_cluster": "<8 fraz powiązanych, przecinkami>",
  "market_angle": "<3-5 słów — dominująca branża np. 'producenci i eksporterzy B2B'>",
  "service_focus": "<3-5 słów — usługa np. 'kampanie Google Ads i strony B2B'>",
  "local_challenge": "<1 zdanie — główna bolączka firm szukających marketingu w {city_name}>",
  "local_advantage": "<1 zdanie — lokalny atut rynku {city_name}>",
  "seasonality_angle": "<1 zdanie — sezonowość popytu w {city_name}>",
  "cta": "<1 zdanie — tekst przycisku w sidebarze, np. 'Chcesz więcej zapytań z {city_name}? Zacznijmy od audytu.'>",
  "faq": [
    {"q": "Ile kosztuje kampania Google Ads dla firmy z {city_name}?", "a": "<konkretna odpowiedź 2-3 zdania>"},
    {"q": "Jak długo czekać na efekty kampanii w {city_name}?", "a": "<konkretna odpowiedź 2-3 zdania>"},
    {"q": "<trzecie pytanie specyficzne dla branży w {city_name}?>", "a": "<odpowiedź>"}
  ]
}
DFLT,
        ],

        "definicja" => [
            "system" => <<<'DFLT'
Jesteś asystentem SEO Sebastiana Kelma — konsultanta marketingu B2B.
Tworzysz definicje pojęć marketingowych dla słownika B2B.
Zasady: pojęcie w pierwszym zdaniu dosłownie, praktyczna definicja z perspektywy właściciela firmy B2B,
schema DefinedTerm wspierana przez treść, linki do powiązanych definicji.
Zwracaj WYŁĄCZNIE jeden obiekt JSON bez markdown.
DFLT,
            "user" => <<<'DFLT'
Zoptymalizuj wpis słownikowy dla pojęcia: {term}
Kategoria: {category}
Fraza główna (SEO): {main_keyword}
Poziom trudności (latwy|sredni|trudny): {difficulty}
Kontekst firmy: {company_ctx}

Bieżąca treść:
{post_content}

KATALOG LINKÓW WEWNĘTRZNYCH (tylko te URL):
{catalog}

Zwróć JSON (single-definicja.php czyta też main_keyword, difficulty, faq, service_links — uzupełnij):
{
  "post_title": "<nazwa pojęcia — zwięzła>",
  "post_content": "<HTML 400-700 słów: definicja, jak stosować w praktyce B2B, przykład, FAQ min 2 pytania, 2-3 linki wewnętrzne [anchor](url)>",
  "post_excerpt": "<1 zdanie — co to jest>",
  "seo_title": "<45-60 znaków: pojęcie + kontekst B2B>",
  "meta_description": "<140-160 znaków z pojęciem>",
  "primary_query": "<krótka fraza SEO: zwykle pytanie co to jest + nazwa pojęcia jak w tytule; jedna linia, bez cudzysłowów w środku>",
  "query_cluster": "<6 powiązanych fraz, przecinkami>",
  "main_keyword": "<dokładna fraza kluczowa spójna z tytułem; bez znaku cudzysłowu w wartości>",
  "difficulty": "<latwy|sredni|trudny>",
  "faq": [{"q": "Pytanie?", "a": "Odpowiedź."}, {"q": "Pytanie 2?", "a": "Odpowiedź 2."}],
  "service_links": ["/#uslugi", "/#kontakt", "/miasta/"]
}
DFLT,
        ],

        "portfolio" => [
            "system" => <<<'DFLT'
Jesteś asystentem SEO i copywriterem Sebastiana Kelma — konsultanta marketingu B2B.
Tworzysz case studies realizacji stron internetowych. Skupiasz się na efektach biznesowych,
nie technologiach. Piszesz z perspektywy klienta: problem → działanie → wynik.
Zwracaj WYŁĄCZNIE jeden obiekt JSON bez markdown.
DFLT,
            "user" => <<<'DFLT'
Uzupełnij i zoptymalizuj case study strony internetowej.
Kontekst firmy: {company_ctx}

Istniejące dane projektu:
Typ: {type}
Problem: {problem}
Zakres: {scope}
Wynik: {result}
Metryki: {metrics}
Technologie: {technologies}
Cytat klienta: {client_quote}

Bieżąca treść HTML:
{post_content}

KATALOG LINKÓW WEWNĘTRZNYCH (tylko te URL):
{catalog}

Zwróć JSON (WYPEŁNIJ WSZYSTKIE POLA — puste = błąd). Mapowanie: type→_ups_port_type, meta_project→_ups_port_meta, badge→_ups_port_badge, cta→_ups_port_cta, problem→_ups_port_problem, scope→_ups_port_scope, result→_ups_port_result, metrics→_ups_port_metrics, client_quote→_ups_port_client_quote.
{
  "post_title": "<tytuł case study — problem klienta lub wynik np. '+42% leadów B2B — redesign strony firmowej'>",
  "post_content": "<HTML 600-900 słów: intro z wynikiem, H2 Problem, H2 Co zrobiliśmy, H2 Efekty, FAQ 2 pytania, 2-3 linki wewnętrzne [anchor](url), 1 link zewnętrzny>",
  "post_excerpt": "<2 zdania: wynik + zakres>",
  "type": "<np. Strona firmowa B2B>",
  "meta_project": "<np. B2B · Lead generation · Google Ads>",
  "badge": "<np. Case study>",
  "cta": "<np. Zobacz jak to zrobiliśmy>",
  "problem": "<2-3 zdania — problem biznesowy klienta>",
  "scope": "<2-4 zdania — co zrobiono>",
  "result": "<2-3 zdania — konkretny wynik>",
  "metrics": "<jedna metryka per linia np. +42% zapytań\n-31% CPL>",
  "client_quote": "<cytat jeśli dostępny, inaczej pusty string>",
  "seo_title": "<45-60 znaków>",
  "meta_description": "<140-160 znaków z frazą kluczową>",
  "primary_query": "<fraza SEO np. 'strona firmowa B2B case study'>",
  "query_cluster": "<8 fraz, przecinkami>",
  "tags": ["tag1","tag2","tag3"]
}
DFLT,
        ],

        "marketing_portfolio" => [
            "system" => <<<'DFLT'
Jesteś asystentem SEO i copywriterem Sebastiana Kelma — konsultanta Google Ads i Meta Ads B2B.
Tworzysz case studies kampanii reklamowych. Skupiasz się na mierzalnych wynikach: CPL, ROAS, konwersje.
Styl: bezpośredni, partnerski, zero korporacyjnego języka. Liczby są ważniejsze niż słowa.
Zwracaj WYŁĄCZNIE jeden obiekt JSON bez markdown.
DFLT,
            "user" => <<<'DFLT'
Uzupełnij i zoptymalizuj case study kampanii marketingowej.
Kontekst firmy: {company_ctx}

Istniejące dane:
Typ kampanii: {type}
Sektor klienta: {sector}
Problem: {problem}
Rozwiązanie: {solution}
Wynik: {result}
KPI (format label|przed|po|zmiana|opis): {kpis}
Tagi (linie): {tags}

Bieżąca treść HTML:
{post_content}

KATALOG LINKÓW WEWNĘTRZNYCH (tylko te URL):
{catalog}

Zwróć JSON (WYPEŁNIJ WSZYSTKIE POLA — puste = błąd). Mapowanie: type→_ups_mport_type, meta_project→_ups_mport_meta, sector→_ups_mport_sector, badge→_ups_mport_badge, cta→_ups_mport_cta, problem→_ups_mport_problem, solution→_ups_mport_solution, result→_ups_mport_result, kpis→_ups_mport_kpis, tags→_ups_mport_tags (linie lub jeden string), seo_title→_ups_mport_seo_title, seo_description→_ups_mport_seo_description.
{
  "post_title": "<tytuł z wynikiem np. 'Meta Ads B2B: -52% CPL w 4 miesiące — firma usługowa'>",
  "post_content": "<HTML 700-1000 słów: wynik w intro, H2 Sytuacja wyjściowa, H2 Co zmieniliśmy, H2 Wyniki po X miesiącach, FAQ 2 pytania, 2-3 linki wewnętrzne [anchor](url), 1 link zewnętrzny>",
  "post_excerpt": "<2 zdania: typ kampanii + kluczowy wynik>",
  "type": "<np. Meta Ads>",
  "meta_project": "<np. Lead generation · B2B · Q1 2024>",
  "sector": "<np. Firma usługowa B2B>",
  "badge": "<np. Meta Ads>",
  "cta": "<np. Przeczytaj case study>",
  "problem": "<2-3 zdania — sytuacja przed współpracą>",
  "solution": "<2-4 zdania — co zmieniono w kampaniach>",
  "result": "<2-3 zdania — konkretne wyniki z liczbami>",
  "kpis": "<KPI rows: label|przed|po|zmiana|opis, jedna linia = jeden KPI>",
  "tags": "<tagi per linia lub tablica stringów>",
  "seo_title": "<45-60 znaków z typem kampanii i wynikiem>",
  "seo_description": "<140-160 znaków>",
  "primary_query": "<fraza SEO np. 'Meta Ads B2B case study'>",
  "query_cluster": "<8 fraz, przecinkami>"
}
DFLT,
        ],
    ];

    if (!isset($defaults[$post_type])) {
        return null;
    }

    $system = trim((string) get_option("ups_ai_cpt_system_{$post_type}", ""));
    if ($system === "") {
        $system = $defaults[$post_type]["system"];
    }

    $user = trim((string) get_option("ups_ai_cpt_prompt_{$post_type}", ""));
    if ($user === "") {
        $user = $defaults[$post_type]["user"];
    }

    $struct = [
        "miasto" => [
            "label" => "Miasto",
            "read_meta" => [
                "city_name" => "_upsellio_city_name",
                "city_slug" => "_upsellio_city_slug",
                "meta_description" => "_upsellio_city_meta_description",
                "local_challenge" => "_upsellio_city_local_challenge",
                "local_advantage" => "_upsellio_city_local_advantage",
                "market_angle" => "_upsellio_city_market_angle",
                "service_focus" => "_upsellio_city_service_focus",
                "seasonality_angle" => "_upsellio_city_seasonality_angle",
                "cta" => "_upsellio_city_cta",
                "voivodeship" => "_upsellio_city_voivodeship",
            ],
            "read_content" => false,
            "json_keys" => [
                "post_content",
                "post_excerpt",
                "seo_title",
                "meta_description",
                "primary_query",
                "query_cluster",
                "market_angle",
                "service_focus",
                "local_challenge",
                "local_advantage",
                "seasonality_angle",
                "cta",
                "faq",
            ],
            "apply" => "upsellio_cpt_ai_apply_miasto",
        ],
        "definicja" => [
            "label" => "Definicja",
            "read_meta" => [
                "term" => "_upsellio_definition_term",
                "category" => "_upsellio_definition_category",
                "main_keyword" => "_upsellio_definition_main_keyword",
                "difficulty" => "_upsellio_definition_difficulty",
            ],
            "read_content" => true,
            "json_keys" => [
                "post_content",
                "post_excerpt",
                "meta_description",
                "seo_title",
                "primary_query",
                "query_cluster",
                "main_keyword",
                "difficulty",
                "faq",
                "service_links",
            ],
            "apply" => "upsellio_cpt_ai_apply_definicja",
        ],
        "portfolio" => [
            "label" => "Portfolio",
            "read_meta" => [
                "type" => "_ups_port_type",
                "meta" => "_ups_port_meta",
                "problem" => "_ups_port_problem",
                "scope" => "_ups_port_scope",
                "result" => "_ups_port_result",
                "metrics" => "_ups_port_metrics",
                "technologies" => "_ups_port_technologies",
                "client_quote" => "_ups_port_client_quote",
                "badge" => "_ups_port_badge",
                "cta" => "_ups_port_cta",
            ],
            "read_content" => true,
            "json_keys" => [
                "post_content",
                "post_excerpt",
                "type",
                "meta_project",
                "badge",
                "cta",
                "problem",
                "scope",
                "result",
                "metrics",
                "client_quote",
                "seo_title",
                "meta_description",
                "primary_query",
                "query_cluster",
                "tags",
            ],
            "apply" => "upsellio_cpt_ai_apply_portfolio",
        ],
        "marketing_portfolio" => [
            "label" => "Case study marketingowe",
            "read_meta" => [
                "type" => "_ups_mport_type",
                "meta" => "_ups_mport_meta",
                "sector" => "_ups_mport_sector",
                "problem" => "_ups_mport_problem",
                "solution" => "_ups_mport_solution",
                "result" => "_ups_mport_result",
                "kpis" => "_ups_mport_kpis",
                "tags" => "_ups_mport_tags",
                "badge" => "_ups_mport_badge",
                "cta" => "_ups_mport_cta",
                "seo_title" => "_ups_mport_seo_title",
                "seo_description" => "_ups_mport_seo_description",
            ],
            "read_content" => true,
            "json_keys" => [
                "post_content",
                "post_excerpt",
                "type",
                "meta_project",
                "sector",
                "badge",
                "cta",
                "problem",
                "solution",
                "result",
                "kpis",
                "tags",
                "seo_title",
                "seo_description",
                "primary_query",
                "query_cluster",
            ],
            "apply" => "upsellio_cpt_ai_apply_marketing_portfolio",
        ],
    ];

    if (!isset($struct[$post_type])) {
        return null;
    }

    return array_merge($struct[$post_type], [
        "prompt_system" => $system,
        "prompt_user" => $user,
    ]);
}

/**
 * @param array<string, mixed> $config
 */
function upsellio_cpt_ai_build_prompt(int $post_id, array $config, string $notes = ""): string
{
    $post = get_post($post_id);
    if (!$post instanceof WP_Post) {
        return "";
    }

    $company_ctx = (string) get_option("ups_ai_company_context", "");

    $internal_catalog = function_exists("upsellio_blog_bot_catalog_for_keyword")
        ? upsellio_blog_bot_catalog_for_keyword(get_the_title($post_id), 24)
        : [];
    $catalog = implode(
        "\n",
        array_map(static function ($u) {
            return $u["url"] . " | " . $u["title"];
        }, $internal_catalog)
    );

    $prompt = (string) $config["prompt_user"];

    $vars = [];
    foreach ($config["read_meta"] as $var => $meta_key) {
        $vars[$var] = trim((string) get_post_meta($post_id, $meta_key, true));
    }

    if ($post->post_type === "miasto") {
        $city_name_direct = trim((string) ($vars["city_name"] ?? ""));
        if ($city_name_direct === "") {
            $city_name_direct = (string) get_the_title($post_id);
        }
        $city_name_direct = (string) preg_replace("/^Marketing i strony WWW\s+/i", "", $city_name_direct);
        $vars["city_name"] = $city_name_direct;
    }
    if (($vars["term"] ?? "") === "" && $post->post_type === "definicja") {
        $vars["term"] = get_the_title($post_id);
    }

    foreach ($vars as $var => $val) {
        $prompt = str_replace("{" . $var . "}", $val, $prompt);
    }

    $content_raw = $config["read_content"] ? (string) $post->post_content : "";
    /* Definicje: krótszy PHP-seed — mniejsze ryzyko złego JSON (newline w polu). */
    $content_limit = $post->post_type === "definicja" ? 2000 : 8000;
    if (function_exists("mb_substr")) {
        $content_raw = mb_substr($content_raw, 0, $content_limit, "UTF-8");
    } else {
        $content_raw = substr($content_raw, 0, $content_limit);
    }

    $prompt = str_replace("{post_content}", $content_raw, $prompt);
    $prompt = str_replace("{company_ctx}", $company_ctx, $prompt);
    if ($post->post_type === "miasto") {
        $cn_cat = trim((string) ($vars["city_name"] ?? ""));
        if ($cn_cat !== "") {
            $catalog = "Miasto docelowe (nazwa do anchorów i treści): " . $cn_cat . "\n\n" . $catalog;
        }
    }
    $prompt = str_replace("{catalog}", $catalog, $prompt);

    $notes = trim($notes);
    if ($notes !== "") {
        $prompt .= "\n\nDodatkowe instrukcje od redaktora:\n" . $notes;
    }

    return $prompt;
}

/**
 * @return array<string, mixed>|WP_Error
 */
function upsellio_cpt_ai_parse_json_from_text(string $text)
{
    $text = trim($text);
    if (preg_match("/```(?:json)?\\s*([\\s\\S]*?)```/i", $text, $fence)) {
        $text = trim($fence[1]);
    }

    if (preg_match("/\\{[\\s\\S]*\\}/u", $text, $m)) {
        $parsed = json_decode($m[0], true);
        if (is_array($parsed)) {
            return $parsed;
        }
    }

    return new WP_Error("parse_error", "Model nie zwrócił poprawnego JSON.");
}

/**
 * @return array<string, mixed>|WP_Error
 */
function upsellio_cpt_ai_run(int $post_id, string $notes = "")
{
    $post_type = get_post_type($post_id);
    $config = upsellio_cpt_ai_get_config((string) $post_type);

    if (!$config) {
        return new WP_Error("no_config", "Brak konfiguracji AI dla tego typu wpisu.");
    }
    if (!function_exists("upsellio_anthropic_crm_api_key") || upsellio_anthropic_crm_api_key() === "") {
        return new WP_Error("no_key", "Brak klucza Anthropic API.");
    }

    $model = function_exists("upsellio_ai_model_for")
        ? upsellio_ai_model_for("cpt_ai_optimize")
        : "claude-sonnet-4-5";

    /* Definicja: więcej pól + długi post_content w odpowiedzi — wyższy limit tokenów. */
    $max_tokens_for_type = $post_type === "definicja" ? 6000 : 4096;

    $user_prompt = upsellio_cpt_ai_build_prompt($post_id, $config, $notes);
    $system_prompt = (string) $config["prompt_system"];

    $api_key = upsellio_anthropic_crm_api_key();
    $response = wp_remote_post("https://api.anthropic.com/v1/messages", [
        "timeout" => 180,
        "headers" => [
            "x-api-key" => $api_key,
            "anthropic-version" => "2023-06-01",
            "content-type" => "application/json",
        ],
        "body" => wp_json_encode([
            "model" => $model,
            "max_tokens" => $max_tokens_for_type,
            "system" => $system_prompt,
            "messages" => [["role" => "user", "content" => $user_prompt]],
        ]),
    ]);

    if (is_wp_error($response)) {
        return $response;
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    $body = json_decode((string) wp_remote_retrieve_body($response), true);

    if ($code >= 400) {
        $msg = (string) ($body["error"]["message"] ?? "API error {$code}");

        return new WP_Error("api_error", $msg);
    }

    $text = "";
    if (function_exists("upsellio_anthropic_crm_extract_text_from_response_body") && is_array($body)) {
        $text = upsellio_anthropic_crm_extract_text_from_response_body($body);
    }
    if ($text === "" && isset($body["content"][0]["text"])) {
        $text = (string) $body["content"][0]["text"];
    }
    if (trim($text) === "") {
        return new WP_Error("empty_response", "Pusta odpowiedź modelu.");
    }

    $parsed = null;
    if (function_exists("upsellio_anthropic_crm_parse_json_object")) {
        $parsed = upsellio_anthropic_crm_parse_json_object($text);
    }
    if (!is_array($parsed)) {
        $fallback = upsellio_cpt_ai_parse_json_from_text($text);
        if (is_wp_error($fallback)) {
            return $fallback;
        }
        if (!is_array($fallback)) {
            return new WP_Error("parse_error", "Model nie zwrócił poprawnego JSON.");
        }
        $parsed = $fallback;
    }

    return $parsed;
}

/**
 * @param array<string, mixed> $data
 */
function upsellio_cpt_ai_apply(int $post_id, array $data): void
{
    $post_type = get_post_type($post_id);
    $config = upsellio_cpt_ai_get_config((string) $post_type);
    if (!$config) {
        return;
    }

    $update = ["ID" => $post_id];
    if (!empty($data["post_title"])) {
        $update["post_title"] = sanitize_text_field((string) $data["post_title"]);
    }
    if (!empty($data["post_content"])) {
        $content = (string) $data["post_content"];
        if (function_exists("upsellio_blog_bot_markdown_links_to_html")) {
            $catalog = function_exists("upsellio_blog_bot_catalog_for_keyword")
                ? upsellio_blog_bot_catalog_for_keyword(get_the_title($post_id), 24)
                : [];
            $allowed = function_exists("upsellio_blog_bot_allowed_urls_map")
                ? upsellio_blog_bot_allowed_urls_map($catalog)
                : [];
            $content = upsellio_blog_bot_markdown_links_to_html($content, $allowed);
        }
        $update["post_content"] = wp_kses_post($content);
    }
    if (!empty($data["post_excerpt"])) {
        $update["post_excerpt"] = sanitize_textarea_field((string) $data["post_excerpt"]);
    }
    if (count($update) > 1) {
        wp_update_post($update);
    }

    if (function_exists("upsellio_save_seo_meta_for_post")) {
        $cluster = $data["query_cluster"] ?? "";
        if (is_array($cluster)) {
            $cluster = implode(", ", $cluster);
        }
        upsellio_save_seo_meta_for_post(
            $post_id,
            (string) ($data["seo_title"] ?? ""),
            (string) ($data["meta_description"] ?? $data["seo_description"] ?? ""),
            (string) ($data["primary_query"] ?? ""),
            (string) $cluster,
            "",
            "seo_article"
        );
    }

    $apply_fn = (string) ($config["apply"] ?? "");
    if ($apply_fn !== "" && function_exists($apply_fn)) {
        $apply_fn($post_id, $data);
    }
}

function upsellio_cpt_ai_normalize_qa_faq_rows($faq): array
{
    if (!is_array($faq)) {
        return [];
    }
    $out = [];
    foreach ($faq as $row) {
        if (!is_array($row)) {
            continue;
        }
        $q = trim((string) ($row["q"] ?? $row["question"] ?? ""));
        $a = trim((string) ($row["a"] ?? $row["answer"] ?? ""));
        if ($q !== "" && $a !== "") {
            $out[] = ["q" => $q, "a" => $a];
        }
    }

    return $out;
}

/**
 * Szablon single-definicja oczekuje tablicy ścieżek względnych (np. "/#uslugi"); akceptuj też obiekty z "url".
 *
 * @param mixed $raw
 * @return array<int, string>
 */
function upsellio_cpt_ai_normalize_definition_service_links($raw): array
{
    if (!is_array($raw)) {
        return [];
    }
    $home = trailingslashit(home_url("/"));
    $out = [];
    foreach ($raw as $item) {
        if (is_string($item)) {
            $path = trim($item);
            if ($path !== "" && strncmp($path, "/", 1) === 0) {
                $out[] = sanitize_text_field($path);
            }
            continue;
        }
        if (!is_array($item)) {
            continue;
        }
        $u = trim((string) ($item["url"] ?? ""));
        if ($u === "") {
            continue;
        }
        if (strncmp($u, "/", 1) === 0) {
            $out[] = sanitize_text_field($u);

            continue;
        }
        $parsed = wp_parse_url($u);
        $host = isset($parsed["host"]) ? strtolower((string) $parsed["host"]) : "";
        $home_host = strtolower((string) (wp_parse_url($home, PHP_URL_HOST) ?? ""));
        $path = isset($parsed["path"]) ? (string) $parsed["path"] : "";
        $query = isset($parsed["query"]) && $parsed["query"] !== "" ? "?" . $parsed["query"] : "";
        $frag = isset($parsed["fragment"]) && $parsed["fragment"] !== "" ? "#" . $parsed["fragment"] : "";
        if ($host !== "" && $home_host !== "" && $host === $home_host && $path !== "") {
            $rel = $path . $query . $frag;
            $out[] = sanitize_text_field($rel !== "" ? $rel : "/");
        }
    }

    return array_values(array_unique($out));
}

function upsellio_cpt_ai_apply_miasto(int $post_id, array $data): void
{
    if (!empty($data["meta_description"])) {
        update_post_meta(
            $post_id,
            "_upsellio_city_meta_description",
            sanitize_textarea_field((string) $data["meta_description"])
        );
    }

    $simple_map = [
        "_upsellio_city_local_challenge" => "local_challenge",
        "_upsellio_city_local_advantage" => "local_advantage",
        "_upsellio_city_market_angle" => "market_angle",
        "_upsellio_city_service_focus" => "service_focus",
        "_upsellio_city_seasonality_angle" => "seasonality_angle",
        "_upsellio_city_cta" => "cta",
    ];
    foreach ($simple_map as $meta_key => $json_key) {
        $val = trim((string) ($data[$json_key] ?? ""));
        if ($val !== "") {
            update_post_meta($post_id, $meta_key, sanitize_text_field($val));
        }
    }

    if (!empty($data["faq"]) && is_array($data["faq"])) {
        $faq_rows = upsellio_cpt_ai_normalize_qa_faq_rows($data["faq"]);
        if (!empty($faq_rows)) {
            update_post_meta($post_id, "_upsellio_city_faq", $faq_rows);
        }
    }

    $pq = trim((string) ($data["primary_query"] ?? ""));
    if ($pq !== "") {
        $pq_clean = preg_replace(
            '/\s*—\s*\d+\s+\w+(\s+(które|który|co|jak|dla|na)?\s*\w*)?$/ui',
            "",
            $pq
        );
        $pq_clean = trim((string) $pq_clean);
        if (function_exists("mb_substr") && function_exists("mb_strlen") && mb_strlen($pq_clean, "UTF-8") > 40) {
            $pq_clean = mb_substr($pq_clean, 0, 40, "UTF-8");
        } elseif (!function_exists("mb_substr") && strlen($pq_clean) > 40) {
            $pq_clean = substr($pq_clean, 0, 40);
        }
        if ($pq_clean !== "") {
            update_post_meta($post_id, "rank_math_focus_keyword", $pq_clean);
            update_post_meta($post_id, "_yoast_wpseo_focuskw", $pq_clean);
            update_post_meta($post_id, "_upsellio_primary_query", $pq_clean);
        }
    }

    $pq_for_slug = trim((string) ($data["primary_query"] ?? ""));
    if ($pq_for_slug === "") {
        $city_name = (string) get_post_meta($post_id, "_upsellio_city_name", true);
        $pq_for_slug = $city_name !== "" ? "marketing-" . $city_name : "";
    }
    $post_status = get_post_status($post_id);
    if (
        $pq_for_slug !== ""
        && ($post_status === "draft" || $post_status === "auto-draft")
    ) {
        $new_slug = sanitize_title($pq_for_slug);
        if (strlen($new_slug) > 60) {
            $head = substr($new_slug, 0, 60);
            $last_sep = strrpos($head, "-");
            $new_slug = $last_sep !== false && $last_sep > 0 ? substr($new_slug, 0, $last_sep) : substr($new_slug, 0, 60);
        }
        if ($new_slug !== "") {
            wp_update_post(["ID" => $post_id, "post_name" => $new_slug]);
        }
    }
}

function upsellio_cpt_ai_apply_definicja(int $post_id, array $data): void
{
    if (!empty($data["meta_description"])) {
        update_post_meta(
            $post_id,
            "_upsellio_definition_meta_description",
            sanitize_textarea_field((string) $data["meta_description"])
        );
    }

    $mk = trim((string) ($data["main_keyword"] ?? ""));
    if ($mk !== "") {
        update_post_meta($post_id, "_upsellio_definition_main_keyword", sanitize_text_field($mk));
    }

    $diff = isset($data["difficulty"]) ? sanitize_key((string) $data["difficulty"]) : "";
    if ($diff !== "" && in_array($diff, ["latwy", "sredni", "trudny"], true)) {
        update_post_meta($post_id, "_upsellio_definition_difficulty", $diff);
    }

    $faq_rows = upsellio_cpt_ai_normalize_qa_faq_rows($data["faq"] ?? null);
    if (!empty($faq_rows)) {
        update_post_meta($post_id, "_upsellio_definition_faq", $faq_rows);
    }

    $links = upsellio_cpt_ai_normalize_definition_service_links($data["service_links"] ?? null);
    if (!empty($links)) {
        update_post_meta($post_id, "_upsellio_definition_service_links", $links);
    }
}

function upsellio_cpt_ai_apply_portfolio(int $post_id, array $data): void
{
    $map = [
        "_ups_port_type" => "type",
        "_ups_port_meta" => "meta_project",
        "_ups_port_badge" => "badge",
        "_ups_port_cta" => "cta",
        "_ups_port_problem" => "problem",
        "_ups_port_scope" => "scope",
        "_ups_port_result" => "result",
        "_ups_port_metrics" => "metrics",
        "_ups_port_client_quote" => "client_quote",
        "_ups_port_technologies" => "technologies",
    ];
    foreach ($map as $meta_key => $json_key) {
        $val = trim((string) ($data[$json_key] ?? ""));
        if ($val !== "") {
            update_post_meta($post_id, $meta_key, sanitize_textarea_field($val));
        }
    }

    if (!empty($data["tags"])) {
        if (is_array($data["tags"])) {
            wp_set_post_tags($post_id, array_map("sanitize_text_field", $data["tags"]), false);
        } else {
            $lines = array_filter(array_map("trim", preg_split("/\\r\\n|\\r|\\n/", (string) $data["tags"]) ?: []));
            if (!empty($lines)) {
                wp_set_post_tags($post_id, array_map("sanitize_text_field", $lines), false);
            }
        }
    }
}

function upsellio_cpt_ai_apply_marketing_portfolio(int $post_id, array $data): void
{
    $map = [
        "_ups_mport_type" => "type",
        "_ups_mport_meta" => "meta_project",
        "_ups_mport_sector" => "sector",
        "_ups_mport_badge" => "badge",
        "_ups_mport_cta" => "cta",
        "_ups_mport_problem" => "problem",
        "_ups_mport_solution" => "solution",
        "_ups_mport_result" => "result",
        "_ups_mport_kpis" => "kpis",
        "_ups_mport_seo_title" => "seo_title",
        "_ups_mport_seo_description" => "seo_description",
    ];
    foreach ($map as $meta_key => $json_key) {
        $raw = $data[$json_key] ?? "";
        $val = is_array($raw) ? implode("\n", $raw) : trim((string) $raw);
        if ($val !== "") {
            update_post_meta($post_id, $meta_key, sanitize_textarea_field($val));
        }
    }

    $tags_raw = $data["tags"] ?? null;
    if ($tags_raw !== null && $tags_raw !== "") {
        if (is_array($tags_raw)) {
            $tags_str = implode("\n", array_map("sanitize_text_field", $tags_raw));
        } else {
            $tags_str = sanitize_textarea_field((string) $tags_raw);
        }
        if ($tags_str !== "") {
            update_post_meta($post_id, "_ups_mport_tags", $tags_str);
        }
    }
}

add_action("wp_ajax_upsellio_cpt_ai_optimize", static function (): void {
    check_ajax_referer("upsellio_cpt_ai_optimize", "nonce");

    $post_id = isset($_POST["post_id"]) ? (int) wp_unslash($_POST["post_id"]) : 0;
    if ($post_id <= 0 || !current_user_can("edit_post", $post_id)) {
        wp_send_json_error("Brak uprawnień.");
    }

    $lock_key = "ups_cpt_ai_running_" . $post_id;
    if (get_transient($lock_key)) {
        wp_send_json_error("AI już przetwarza ten wpis. Poczekaj chwilę.");
    }
    set_transient($lock_key, 1, 3 * MINUTE_IN_SECONDS);

    try {
        $notes = isset($_POST["notes"]) ? sanitize_textarea_field(wp_unslash((string) $_POST["notes"])) : "";

        if (function_exists("set_time_limit")) {
            @set_time_limit(240);
        }

        $result = upsellio_cpt_ai_run($post_id, $notes);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        upsellio_cpt_ai_apply($post_id, $result);

        wp_send_json_success([
            "message" => "Zapisano. Strona za chwilę się przeładuje.",
            "fields_updated" => array_keys($result),
        ]);
    } finally {
        delete_transient($lock_key);
    }
});

function upsellio_cpt_ai_register_meta_boxes(): void
{
    $types = ["miasto", "definicja", "portfolio", "marketing_portfolio"];
    foreach ($types as $pt) {
        add_meta_box(
            "upsellio_cpt_ai_optimizer",
            "✨ Optymalizuj AI (Upsellio)",
            "upsellio_cpt_ai_meta_box_cb",
            $pt,
            "side",
            "high"
        );
    }
}
add_action("add_meta_boxes", "upsellio_cpt_ai_register_meta_boxes");

function upsellio_cpt_ai_meta_box_cb(WP_Post $post): void
{
    $post_type = $post->post_type;
    $config = upsellio_cpt_ai_get_config($post_type);
    $label = $config ? $config["label"] : $post_type;
    ?>
    <p style="font-size:12px;color:#555;margin-bottom:10px;line-height:1.5">
        Claude uzupełni treść, wszystkie pola meta boxa oraz SEO (Rank Math / Yoast).
        Istniejące dane zostaną użyte jako kontekst.
    </p>
    <p>
        <label for="upsellio-cpt-ai-notes" style="font-size:12px;font-weight:600">
            Notatka dla AI (opcjonalnie)
        </label><br>
        <textarea id="upsellio-cpt-ai-notes" rows="2"
                  style="width:100%;font-size:12px;margin-top:4px"
                  placeholder="np. klient z branży IT, wynik +60% leadów"></textarea>
    </p>
    <p>
        <button type="button" id="upsellio-cpt-ai-run"
                class="button button-primary" style="width:100%">
            ✨ Optymalizuj AI — <?php echo esc_html($label); ?>
        </button>
    </p>
    <div id="upsellio-cpt-ai-status" style="margin-top:8px;font-size:12px;display:none"></div>
    <?php
}

add_action("admin_enqueue_scripts", static function (string $hook): void {
    if (!in_array($hook, ["post.php", "post-new.php"], true)) {
        return;
    }

    $screen = get_current_screen();
    $allowed = ["miasto", "definicja", "portfolio", "marketing_portfolio"];
    if (!$screen || !in_array($screen->post_type, $allowed, true)) {
        return;
    }

    $path = get_template_directory() . "/assets/js/cpt-ai-optimizer.js";
    $ver = is_readable($path) ? (string) filemtime($path) : "1.0";

    wp_enqueue_script(
        "upsellio-cpt-ai-optimizer",
        get_template_directory_uri() . "/assets/js/cpt-ai-optimizer.js",
        ["jquery"],
        $ver,
        true
    );
    wp_localize_script("upsellio-cpt-ai-optimizer", "upselloCptAi", [
        "ajaxurl" => admin_url("admin-ajax.php"),
        "nonce" => wp_create_nonce("upsellio_cpt_ai_optimize"),
    ]);
});
