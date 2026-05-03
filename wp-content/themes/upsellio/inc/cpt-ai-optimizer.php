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
Tworzysz i optymalizujesz podstrony lokalne (miasto + usługa).
Zasady: lokalna intencja wyszukiwania, konkretne korzyści dla firm B2B z tego miasta,
naturalne wplecenie nazwy miasta i usługi, schema LocalBusiness wspierana przez treść.
Zwracaj WYŁĄCZNIE jeden obiekt JSON bez markdown.
DFLT,
            "user" => <<<'DFLT'
Zoptymalizuj podstronę lokalną dla miasta: {city_name}.
Usługi: Google Ads, Meta Ads, strony internetowe B2B.
Kontekst firmy: {company_ctx}

Dotychczasowe pola kontekstowe (zaktualizuj jeśli trzeba):
Wyzwanie lokalne: {local_challenge}
Atut rynku: {local_advantage}
Kąt rynku (branża): {market_angle}
Fokus usług: {service_focus}
Sezonowość: {seasonality_angle}

Bieżąca treść (popraw i rozbuduj):
{post_content}

KATALOG LINKÓW WEWNĘTRZNYCH (używaj tylko tych URL):
{catalog}

Zwróć JSON (uzupełnij też pola używane przez szablon single-miasto — puste meta = słabsza strona):
{
  "post_title": "Marketing {city_name} — Google Ads, Meta Ads, strony B2B",
  "post_content": "<HTML artykułu min. 600 słów, H2/H3, fraza kluczowa w pierwszym zdaniu, 2-4 linki wewnętrzne [anchor](url), 1 link zewnętrzny>",
  "post_excerpt": "<zajawka 1-2 zdania>",
  "seo_title": "<45-60 znaków z nazwą miasta>",
  "meta_description": "<140-160 znaków z frazą kluczową i nazwą miasta>",
  "primary_query": "<fraza np. 'Google Ads {city_name}'>",
  "query_cluster": "<8 powiązanych fraz, przecinkami>",
  "local_challenge": "<1 zdanie — główna bolączka firm szukających marketingu w tym mieście>",
  "local_advantage": "<1 zdanie — lokalny atut tego rynku>",
  "market_angle": "<3-5 słów — dominująca branża np. 'producenci i firmy B2B'>",
  "service_focus": "<3-5 słów — usługa którą szukają np. 'kampanie Google Ads i strony B2B'>",
  "seasonality_angle": "<1 zdanie — sezonowość popytu>",
  "faq": [{"q": "Pytanie?", "a": "Odpowiedź."}, {"q": "Pytanie 2?", "a": "Odpowiedź 2."}]
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
  "primary_query": "<fraza kluczowa np. 'co to jest {term}'>",
  "query_cluster": "<6 powiązanych fraz, przecinkami>",
  "main_keyword": "<dokładna fraza kluczowa np. 'co to jest ROAS'>",
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
            ],
            "read_content" => true,
            "json_keys" => [
                "post_content",
                "post_excerpt",
                "meta_description",
                "seo_title",
                "primary_query",
                "query_cluster",
                "local_challenge",
                "local_advantage",
                "market_angle",
                "service_focus",
                "seasonality_angle",
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

    if (($vars["city_name"] ?? "") === "" && $post->post_type === "miasto") {
        $vars["city_name"] = get_the_title($post_id);
    }
    if (($vars["term"] ?? "") === "" && $post->post_type === "definicja") {
        $vars["term"] = get_the_title($post_id);
    }

    foreach ($vars as $var => $val) {
        $prompt = str_replace("{" . $var . "}", $val, $prompt);
    }

    $content_raw = $config["read_content"] ? (string) $post->post_content : "";
    if (function_exists("mb_substr")) {
        $content_raw = mb_substr($content_raw, 0, 8000, "UTF-8");
    } else {
        $content_raw = substr($content_raw, 0, 8000);
    }

    $prompt = str_replace("{post_content}", $content_raw, $prompt);
    $prompt = str_replace("{company_ctx}", $company_ctx, $prompt);
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
            "max_tokens" => 4096,
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
    ];
    foreach ($simple_map as $meta_key => $json_key) {
        $val = trim((string) ($data[$json_key] ?? ""));
        if ($val !== "") {
            update_post_meta($post_id, $meta_key, sanitize_text_field($val));
        }
    }

    $faq_rows = upsellio_cpt_ai_normalize_qa_faq_rows($data["faq"] ?? null);
    if (!empty($faq_rows)) {
        update_post_meta($post_id, "_upsellio_city_faq", $faq_rows);
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
