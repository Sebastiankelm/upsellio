<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_gsc_strtolower_utf8(string $s): string
{
    if (function_exists("mb_strtolower")) {
        return mb_strtolower($s, "UTF-8");
    }

    return strtolower($s);
}

/**
 * Główna analiza GSC — grupowanie, klastry, luki, kanibalizacja (PHP, bez tokenów AI).
 * Cache 6 h w wp_options.
 *
 * @return array<string, mixed>
 */
function upsellio_gsc_analyze_full(bool $force = false): array
{
    $cache_key = "ups_gsc_analysis_cache";
    $built_key = "ups_gsc_analysis_built";

    if (!$force) {
        $built_ts = strtotime((string) get_option($built_key, ""));
        if ($built_ts && (time() - $built_ts) < 6 * HOUR_IN_SECONDS) {
            $cached = get_option($cache_key, []);
            if (is_array($cached) && isset($cached["aggregated"])) {
                return $cached;
            }
        }
    }

    $raw = get_option("upsellio_keyword_metrics_rows", []);
    if (!is_array($raw) || $raw === []) {
        $empty = [
            "status" => "no_data",
            "built" => current_time("mysql"),
            "total_keywords" => 0,
            "clusters" => [],
            "gaps" => [],
            "cannibalization" => [],
            "low_ctr" => [],
            "quick_wins" => [],
            "aggregated" => [],
        ];
        update_option($cache_key, $empty, false);
        update_option($built_key, current_time("mysql"), false);

        return $empty;
    }

    $aggregated = upsellio_gsc_aggregate_keywords($raw);
    $clusters = upsellio_gsc_build_clusters($aggregated);
    $gaps = upsellio_gsc_detect_gaps($aggregated);
    $cannibalization = upsellio_gsc_detect_cannibalization($aggregated);
    $low_ctr = upsellio_gsc_low_ctr_opportunities($aggregated);
    $quick_wins = upsellio_gsc_quick_wins_extended($aggregated);

    $result = [
        "status" => "ok",
        "built" => current_time("mysql"),
        "total_keywords" => count($aggregated),
        "clusters" => $clusters,
        "gaps" => $gaps,
        "cannibalization" => $cannibalization,
        "low_ctr" => $low_ctr,
        "quick_wins" => $quick_wins,
        "aggregated" => $aggregated,
    ];

    update_option($cache_key, $result, false);
    update_option($built_key, current_time("mysql"), false);

    return $result;
}

/**
 * Agregacja wierszy GSC (ta sama fraza + URL): suma impr/klików, najlepsza pozycja.
 *
 * @param array<int, array<string, mixed>> $raw
 *
 * @return list<array<string, mixed>>
 */
function upsellio_gsc_aggregate_keywords(array $raw): array
{
    $by_keyword = [];

    foreach ($raw as $row) {
        if (!is_array($row)) {
            continue;
        }
        $kw = trim(upsellio_gsc_strtolower_utf8((string) ($row["keyword"] ?? "")));
        $url = (string) ($row["url"] ?? $row["page"] ?? "");
        $pos = (float) ($row["position"] ?? 99);
        $impr = (int) ($row["impressions"] ?? 0);
        $clicks = (int) ($row["clicks"] ?? 0);

        if ($kw === "") {
            continue;
        }

        $key = $kw . "|||" . $url;

        if (!isset($by_keyword[$key])) {
            $by_keyword[$key] = [
                "keyword" => $kw,
                "url" => $url,
                "position" => $pos,
                "impressions" => 0,
                "clicks" => 0,
                "days" => 0,
            ];
        }

        if ($pos < (float) $by_keyword[$key]["position"]) {
            $by_keyword[$key]["position"] = $pos;
        }

        $by_keyword[$key]["impressions"] += $impr;
        $by_keyword[$key]["clicks"] += $clicks;
        $by_keyword[$key]["days"]++;
    }

    foreach ($by_keyword as &$r) {
        $r["ctr"] = $r["impressions"] > 0
            ? round(($r["clicks"] / $r["impressions"]) * 100, 2)
            : 0.0;
    }
    unset($r);

    return array_values($by_keyword);
}

/**
 * @param list<array<string, mixed>> $aggregated
 *
 * @return list<array<string, mixed>>
 */
function upsellio_gsc_build_clusters(array $aggregated): array
{
    $location_map = [];
    foreach ($aggregated as $row) {
        $url = (string) ($row["url"] ?? "");
        if (preg_match("/miasto=([^&]+)/i", $url, $m)) {
            $location = sanitize_title(urldecode($m[1]));
            if ($location !== "") {
                if (!isset($location_map[$location])) {
                    $location_map[$location] = [];
                }
                $location_map[$location][] = $row;
            }
        }
    }

    $clusters = [];
    foreach ($location_map as $location => $rows) {
        if ($rows === []) {
            continue;
        }
        $total_impr = (int) array_sum(array_column($rows, "impressions"));
        $total_clicks = (int) array_sum(array_column($rows, "clicks"));
        $positions = array_column($rows, "position");
        $best_pos = $positions !== [] ? (float) min($positions) : 99.0;
        $keywords = array_unique(array_column($rows, "keyword"));

        $clusters[] = [
            "name" => ucfirst(str_replace("-", " ", $location)),
            "type" => "location",
            "keywords" => array_values($keywords),
            "count" => count($rows),
            "total_impr" => $total_impr,
            "total_clicks" => $total_clicks,
            "best_position" => round($best_pos, 1),
            "main_url" => (string) ($rows[0]["url"] ?? ""),
            "opportunity" => upsellio_gsc_cluster_opportunity($best_pos, $total_impr, $total_clicks),
        ];
    }

    $service_patterns = [
        "seo" => ["seo", "pozycjonowanie", "pozycjonowania"],
        "google_ads" => ["google ads", "kampanie google", "reklama google", "adwords"],
        "meta_ads" => ["meta ads", "facebook ads", "reklama facebook", "reklama na facebooku"],
        "strony_www" => ["strony www", "strona www", "tworzenie stron", "strony internetowe"],
        "agencja" => ["agencja marketingowa", "agencja seo", "agencja google"],
    ];

    foreach ($service_patterns as $service_key => $patterns) {
        $service_rows = [];
        foreach ($aggregated as $row) {
            $kw = strtolower((string) ($row["keyword"] ?? ""));
            foreach ($patterns as $pattern) {
                if (strpos($kw, $pattern) !== false) {
                    $service_rows[] = $row;
                    break;
                }
            }
        }

        if (count($service_rows) >= 2) {
            $total_impr = (int) array_sum(array_column($service_rows, "impressions"));
            $total_clicks = (int) array_sum(array_column($service_rows, "clicks"));
            $positions = array_column($service_rows, "position");
            $best_pos = $positions !== [] ? (float) min($positions) : 99.0;

            $clusters[] = [
                "name" => str_replace("_", " ", ucfirst((string) $service_key)),
                "type" => "service",
                "keywords" => array_values(array_unique(array_column($service_rows, "keyword"))),
                "count" => count($service_rows),
                "total_impr" => $total_impr,
                "total_clicks" => $total_clicks,
                "best_position" => round($best_pos, 1),
                "main_url" => (string) ($service_rows[0]["url"] ?? ""),
                "opportunity" => upsellio_gsc_cluster_opportunity($best_pos, $total_impr, $total_clicks),
            ];
        }
    }

    usort($clusters, static function ($a, $b) {
        return (int) ($b["opportunity"] ?? 0) <=> (int) ($a["opportunity"] ?? 0);
    });

    return $clusters;
}

/**
 * @param list<array<string, mixed>> $aggregated
 *
 * @return list<array<string, mixed>>
 */
function upsellio_gsc_detect_gaps(array $aggregated): array
{
    $existing_posts = get_posts([
        "post_type" => "post",
        "post_status" => "publish",
        "posts_per_page" => 200,
        "fields" => "ids",
    ]);

    $post_paths = [];
    foreach ($existing_posts as $pid) {
        $path = wp_parse_url((string) get_permalink((int) $pid), PHP_URL_PATH);
        if (is_string($path) && $path !== "") {
            $post_paths[] = strtolower(rtrim($path, "/"));
        }
    }

    $home_path = wp_parse_url(home_url("/"), PHP_URL_PATH);
    $home_path = is_string($home_path) ? strtolower(rtrim($home_path, "/")) : "";

    $gaps = [];

    foreach ($aggregated as $row) {
        $url_raw = (string) ($row["url"] ?? "");
        $kw = (string) ($row["keyword"] ?? "");
        $pos = (float) ($row["position"] ?? 99);

        if ($kw === "") {
            continue;
        }

        $path = wp_parse_url($url_raw, PHP_URL_PATH);
        $query = (string) wp_parse_url($url_raw, PHP_URL_QUERY);
        $path_s = is_string($path) ? strtolower(rtrim($path, "/")) : "";

        $is_homepage = $url_raw === ""
            || $path_s === $home_path
            || $path_s === ""
            || trailingslashit($url_raw) === trailingslashit(home_url("/"));

        $is_parametric = strpos($url_raw, "?") !== false || strpos($query, "miasto=") !== false;

        $has_article = false;
        foreach ($post_paths as $post_path) {
            if ($post_path !== "" && ($path_s !== "" && (strpos($path_s, $post_path) !== false || strpos($post_path, $path_s) !== false))) {
                $has_article = true;
                break;
            }
        }

        if (($is_homepage || $is_parametric) && !$has_article && $pos <= 80) {
            $gaps[] = [
                "keyword" => $kw,
                "position" => $pos,
                "impressions" => (int) ($row["impressions"] ?? 0),
                "clicks" => (int) ($row["clicks"] ?? 0),
                "current_url" => $url_raw,
                "gap_type" => $is_homepage ? "homepage_ranking" : "parametric_ranking",
                "priority" => $pos <= 30 ? "high" : ($pos <= 60 ? "medium" : "low"),
                "suggestion" => sprintf(
                    /* translators: %s keyword */
                    __('Napisz artykuł dedykowany frazie "%s" zamiast kierować na stronę ogólną lub parametryczną.', "upsellio"),
                    $kw
                ),
            ];
        }
    }

    usort($gaps, static function ($a, $b) {
        $score = static function ($row) {
            $pos = (float) ($row["position"] ?? 99);
            $imp = (int) ($row["impressions"] ?? 0);

            return ($pos > 0 ? (100 / $pos) : 0) * (1 + $imp / 10);
        };

        return $score($b) <=> $score($a);
    });

    return array_slice($gaps, 0, 30);
}

/**
 * @param list<array<string, mixed>> $aggregated
 *
 * @return list<array<string, mixed>>
 */
function upsellio_gsc_detect_cannibalization(array $aggregated): array
{
    $by_keyword = [];

    foreach ($aggregated as $row) {
        $kw = (string) ($row["keyword"] ?? "");
        $url = (string) ($row["url"] ?? "");
        if ($kw === "" || $url === "") {
            continue;
        }

        if (!isset($by_keyword[$kw])) {
            $by_keyword[$kw] = [];
        }
        $by_keyword[$kw][] = $row;
    }

    $cannibalization = [];
    foreach ($by_keyword as $kw => $rows) {
        $urls = array_unique(array_column($rows, "url"));
        $urls = array_values(array_filter($urls, static function ($u) {
            return (string) $u !== "";
        }));
        if (count($urls) < 2) {
            continue;
        }

        $positions = array_column($rows, "position");
        $best_pos = $positions !== [] ? (float) min($positions) : 99.0;

        $cannibalization[] = [
            "keyword" => $kw,
            "urls" => $urls,
            "best_pos" => round($best_pos, 1),
            "fix" => __("Zdecyduj który URL ma rankować — canonical, przekierowanie lub połączenie treści.", "upsellio"),
        ];
    }

    return $cannibalization;
}

/**
 * @param list<array<string, mixed>> $aggregated
 *
 * @return list<array<string, mixed>>
 */
function upsellio_gsc_low_ctr_opportunities(array $aggregated): array
{
    $opportunities = [];

    foreach ($aggregated as $row) {
        $pos = (float) ($row["position"] ?? 99);
        $ctr = (float) ($row["ctr"] ?? 0);
        $impr = (int) ($row["impressions"] ?? 0);
        $kw = (string) ($row["keyword"] ?? "");

        $expected_ctr = 99.0;
        if ($pos <= 3) {
            $expected_ctr = 15.0;
        } elseif ($pos <= 5) {
            $expected_ctr = 8.0;
        } elseif ($pos <= 10) {
            $expected_ctr = 3.0;
        }

        if ($pos <= 10 && $ctr < $expected_ctr && $impr >= 3) {
            $opportunities[] = [
                "keyword" => $kw,
                "position" => $pos,
                "ctr" => $ctr,
                "expected_ctr" => $expected_ctr,
                "impressions" => $impr,
                "url" => (string) ($row["url"] ?? ""),
                "fix" => sprintf(
                    /* translators: 1: ctr, 2: position */
                    __("Popraw title i meta description — CTR %.2f%% przy poz. %.1f jest poniżej typowego dla tej pozycji.", "upsellio"),
                    $ctr,
                    $pos
                ),
            ];
        }
    }

    usort($opportunities, static function ($a, $b) {
        return (int) ($b["impressions"] ?? 0) <=> (int) ($a["impressions"] ?? 0);
    });

    return array_slice($opportunities, 0, 10);
}

/**
 * Quick wins: poz. 5–50 (bez minimalnych wyświetleń).
 *
 * @param list<array<string, mixed>> $aggregated
 *
 * @return list<array<string, mixed>>
 */
function upsellio_gsc_quick_wins_extended(array $aggregated): array
{
    $wins = [];

    foreach ($aggregated as $row) {
        $pos = (float) ($row["position"] ?? 99);
        $kw = (string) ($row["keyword"] ?? "");
        $impr = (int) ($row["impressions"] ?? 0);

        if ($kw === "" || $pos < 5 || $pos > 50) {
            continue;
        }

        $pos_weight = max(0.0, (50 - $pos) / 45);
        $impr_weight = log(max(1, $impr) + 1) / 5;
        $score = (int) round(($pos_weight * 0.7 + $impr_weight * 0.3) * 100);

        $wins[] = [
            "keyword" => $kw,
            "position" => $pos,
            "impressions" => $impr,
            "clicks" => (int) ($row["clicks"] ?? 0),
            "ctr" => (float) ($row["ctr"] ?? 0),
            "url" => (string) ($row["url"] ?? ""),
            "opportunity_score" => $score,
            "priority" => $pos <= 20 ? "high" : "medium",
        ];
    }

    usort($wins, static function ($a, $b) {
        return (int) ($b["opportunity_score"] ?? 0) <=> (int) ($a["opportunity_score"] ?? 0);
    });

    return array_slice($wins, 0, 20);
}

function upsellio_gsc_cluster_opportunity(float $best_pos, int $total_impr, int $total_clicks): int
{
    unset($total_clicks);
    $pos_score = max(0.0, (80 - $best_pos) / 80) * 60;
    $impr_score = min(40.0, $total_impr / 2);

    return (int) round($pos_score + $impr_score);
}

/**
 * Blok tekstu do promptów AI.
 */
function upsellio_gsc_build_prompt_block(string $scope = "topicgen"): string
{
    $analysis = upsellio_gsc_analyze_full();

    $has_any = !empty($analysis["clusters"])
        || !empty($analysis["gaps"])
        || !empty($analysis["quick_wins"])
        || !empty($analysis["low_ctr"])
        || !empty($analysis["cannibalization"]);

    if (!$has_any || (($analysis["status"] ?? "") === "no_data")) {
        return __("Brak danych GSC do analizy.", "upsellio");
    }

    $lines = [];

    if ($scope === "topicgen" || $scope === "suggestions") {
        if (!empty($analysis["quick_wins"])) {
            $lines[] = "QUICK WINS GSC (frazy poz. 5–50 — możliwość artykułu / landing):";
            foreach (array_slice($analysis["quick_wins"], 0, 12) as $w) {
                $lines[] = sprintf(
                    '  [%s] "%s" | poz. %.1f | %d wyśw. | url: %s',
                    (string) ($w["priority"] ?? ""),
                    (string) ($w["keyword"] ?? ""),
                    (float) ($w["position"] ?? 0),
                    (int) ($w["impressions"] ?? 0),
                    (string) ($w["url"] ?? "")
                );
            }
        }

        if (!empty($analysis["gaps"])) {
            $lines[] = "\nLUKI TREŚCIOWE (frazy rankujące na homepage / URL parametryczny — brak dedykowanego artykułu):";
            foreach (array_slice($analysis["gaps"], 0, 10) as $g) {
                $lines[] = sprintf(
                    '  [%s] "%s" | poz. %.1f | %d wyśw. → %s',
                    (string) ($g["priority"] ?? ""),
                    (string) ($g["keyword"] ?? ""),
                    (float) ($g["position"] ?? 0),
                    (int) ($g["impressions"] ?? 0),
                    (string) ($g["suggestion"] ?? "")
                );
            }
        }

        if (!empty($analysis["clusters"])) {
            $lines[] = "\nKLASTRY TEMATYCZNE:";
            foreach (array_slice($analysis["clusters"], 0, 6) as $c) {
                $kw_sample = implode(", ", array_slice($c["keywords"] ?? [], 0, 4));
                $lines[] = sprintf(
                    '  Klaster "%s" (%d fraz): %s | najlepsza poz.: %.1f | %d wyśw.',
                    (string) ($c["name"] ?? ""),
                    (int) ($c["count"] ?? 0),
                    $kw_sample,
                    (float) ($c["best_position"] ?? 0),
                    (int) ($c["total_impr"] ?? 0)
                );
            }
        }

        if (!empty($analysis["low_ctr"])) {
            $lines[] = "\nNISKI CTR (meta / title, nie nowy artykuł jako pierwszy krok):";
            foreach (array_slice($analysis["low_ctr"], 0, 5) as $l) {
                $lines[] = sprintf(
                    '  "%s" | poz. %.1f | CTR %.2f%% (oczekiwane ~%.1f%%) → %s',
                    (string) ($l["keyword"] ?? ""),
                    (float) ($l["position"] ?? 0),
                    (float) ($l["ctr"] ?? 0),
                    (float) ($l["expected_ctr"] ?? 0),
                    (string) ($l["fix"] ?? "")
                );
            }
        }

        if (!empty($analysis["cannibalization"])) {
            $lines[] = "\nKANIBALIZACJA (ta sama fraza na wielu URL — nie twórz kolejnego artykułu bez konsolidacji):";
            foreach (array_slice($analysis["cannibalization"], 0, 3) as $c) {
                $urls = implode(" vs ", array_slice($c["urls"] ?? [], 0, 2));
                $lines[] = sprintf(
                    '  "%s" rankuje na: %s → %s',
                    (string) ($c["keyword"] ?? ""),
                    $urls,
                    (string) ($c["fix"] ?? "")
                );
            }
        }
    }

    return implode("\n", $lines);
}

/**
 * Kontekst GSC dla jednej frazy (Blog Bot).
 */
function upsellio_gsc_build_keyword_context(string $keyword): string
{
    $analysis = upsellio_gsc_analyze_full();
    $kw_lower = upsellio_gsc_strtolower_utf8(trim($keyword));
    $lines = [];

    $related = [];
    $kw_words = array_filter(preg_split("/\s+/u", $kw_lower) ?: [], static function ($w) {
        return strlen((string) $w) > 2;
    });

    foreach ($analysis["aggregated"] ?? [] as $row) {
        if (!is_array($row)) {
            continue;
        }
        $row_kw = upsellio_gsc_strtolower_utf8((string) ($row["keyword"] ?? ""));
        if ($row_kw === $kw_lower) {
            continue;
        }

        $row_words = array_filter(preg_split("/\s+/u", $row_kw) ?: [], static function ($w) {
            return strlen((string) $w) > 2;
        });
        $common = array_intersect($kw_words, $row_words);
        if (count($common) >= 1) {
            $related[] = $row;
        }
    }

    if ($related !== []) {
        usort($related, static function ($a, $b) {
            return (int) ($b["impressions"] ?? 0) <=> (int) ($a["impressions"] ?? 0);
        });
        $lines[] = "POWIĄZANE FRAZY Z GSC (użyj jako query_cluster w treści):";
        foreach (array_slice($related, 0, 12) as $r) {
            $lines[] = sprintf(
                '  "%s" | poz. %.1f | %d wyśw.',
                (string) ($r["keyword"] ?? ""),
                (float) ($r["position"] ?? 0),
                (int) ($r["impressions"] ?? 0)
            );
        }
    }

    $exact = null;
    foreach ($analysis["aggregated"] ?? [] as $row) {
        if (!is_array($row)) {
            continue;
        }
        if (upsellio_gsc_strtolower_utf8((string) ($row["keyword"] ?? "")) === $kw_lower) {
            $exact = $row;
            break;
        }
    }

    if ($exact !== null) {
        $lines[] = "\nFRAZA GŁÓWNA W GSC:";
        $lines[] = sprintf(
            "  Pozycja: %.1f | Wyświetlenia: %d | Kliknięcia: %d | URL: %s",
            (float) ($exact["position"] ?? 0),
            (int) ($exact["impressions"] ?? 0),
            (int) ($exact["clicks"] ?? 0),
            (string) ($exact["url"] ?? "")
        );
        if ((float) ($exact["position"] ?? 99) <= 30) {
            $lines[] = sprintf(
                /* translators: %1$s position %2$s url */
                __("  Uwaga: fraza już ma widoczność (poz. %1\$s) na %2\$s — nowy artykuł musi być wyraźnie lepszy lub celować w inną intencję; unikaj kanibalizacji.", "upsellio"),
                (string) round((float) ($exact["position"] ?? 0), 1),
                (string) ($exact["url"] ?? "")
            );
        }
    }

    foreach ($analysis["cannibalization"] ?? [] as $c) {
        if (!is_array($c)) {
            continue;
        }
        if (upsellio_gsc_strtolower_utf8((string) ($c["keyword"] ?? "")) === $kw_lower) {
            $urls = implode(", ", $c["urls"] ?? []);
            $lines[] = "\nKANIBALIZACJA: ta fraza ma wiele URL w GSC: " . $urls;
            $lines[] = "  " . __("Najpierw ujednolić strategię URL (canonical / treść), zamiast dodawać kolejny artykuł na tę samą frazę.", "upsellio");
        }
    }

    $intent = upsellio_gsc_detect_intent($kw_lower);
    $lines[] = "\nINTENCJA WYSZUKIWANIA: " . (string) ($intent["type"] ?? "") . " — " . (string) ($intent["instruction"] ?? "");

    return implode("\n", $lines);
}

/**
 * @return array{type: string, instruction: string}
 */
function upsellio_gsc_detect_intent(string $keyword): array
{
    $kw = upsellio_gsc_strtolower_utf8($keyword);

    $transactional = ["agencja", "firma", "usługi", "cennik", "oferta", "zamów", "zlecę", "szukam", "kontakt"];
    $local = ["warszawa", "kraków", "poznań", "gdańsk", "wrocław", "łódź", "katowice", "będzin", "brzeg", "hrubieszów", "elbląg", "kalisz"];
    $informational = ["jak ", "co to", "czym jest", "dlaczego", "kiedy", "ile kosztuje", "jak działa"];

    foreach ($local as $city) {
        if (strpos($kw, $city) !== false) {
            return [
                "type" => "lokalna",
                "instruction" => __("Pisz o konkretnym mieście / regionie, lokalnym rynku i firmach z okolicy; dodaj FAQ z pytaniami lokalnymi.", "upsellio"),
            ];
        }
    }

    foreach ($transactional as $signal) {
        if (strpos($kw, $signal) !== false) {
            return [
                "type" => "transakcyjna",
                "instruction" => __("Klient szuka wykonawcy — korzyści, proces, CTA, mniej ogólnej teorii.", "upsellio"),
            ];
        }
    }

    foreach ($informational as $signal) {
        if (strpos($kw, $signal) !== false) {
            return [
                "type" => "informacyjna",
                "instruction" => __("Wyjaśnij temat wyczerpująco, FAQ na końcu, potem CTA do oferty.", "upsellio"),
            ];
        }
    }

    return [
        "type" => "porównawcza",
        "instruction" => __("Pokaż różnice opcji, case study, dlaczego Twoja oferta ma sens.", "upsellio"),
    ];
}

/**
 * Panel HTML — zakładka Sugestie → Słowa kluczowe.
 */
function upsellio_crm_render_gsc_analysis_panel(string $ajax_url, string $nonce): void
{
    $analysis = upsellio_gsc_analyze_full();
    $blog_base = add_query_arg(
        [
            "view" => "settings",
            "settings_tab" => "ai",
            "blog_focus" => "1",
        ],
        home_url("/crm-app/")
    );

    ?>
    <div class="ups-gsc-analysis" style="margin-bottom:22px">
      <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:12px;margin-bottom:14px">
        <div style="display:flex;flex-wrap:wrap;gap:10px;font-size:13px;color:var(--text-2)">
          <span><?php esc_html_e("Klastry:", "upsellio"); ?> <?php echo (int) count($analysis["clusters"] ?? []); ?></span>
          <span><?php esc_html_e("Luki:", "upsellio"); ?> <?php echo (int) count($analysis["gaps"] ?? []); ?></span>
          <span><?php esc_html_e("Quick wins:", "upsellio"); ?> <?php echo (int) count($analysis["quick_wins"] ?? []); ?></span>
          <span><?php esc_html_e("Kanibalizacja:", "upsellio"); ?> <?php echo (int) count($analysis["cannibalization"] ?? []); ?></span>
          <span class="muted"><?php esc_html_e("Analiza:", "upsellio"); ?> <?php echo esc_html((string) ($analysis["built"] ?? "—")); ?></span>
        </div>
        <button type="button" class="btn alt" id="ups-gsc-refresh-analysis" data-nonce="<?php echo esc_attr($nonce); ?>">
          <?php esc_html_e("Odśwież analizę GSC", "upsellio"); ?>
        </button>
      </div>

      <?php if (($analysis["status"] ?? "") === "no_data" || empty($analysis["aggregated"])) : ?>
        <p class="muted" style="font-size:13px;margin:0 0 14px;line-height:1.5"><?php esc_html_e("Brak zsynchronizowanych fraz GSC. Zaimportuj dane w Analityce witryny lub połącz Search Console.", "upsellio"); ?></p>
      <?php endif; ?>

      <?php if (!empty($analysis["gaps"])) : ?>
        <h3 style="font-size:15px;margin:16px 0 8px"><?php esc_html_e("Luki treściowe", "upsellio"); ?></h3>
        <p class="muted" style="font-size:12px;margin:0 0 10px;line-height:1.5"><?php esc_html_e("Frazy widoczne w SERP bez dedykowanego artykułu — wysoki priorytet treści.", "upsellio"); ?></p>
        <?php foreach (array_slice($analysis["gaps"], 0, 8) as $gap) : ?>
          <?php if (!is_array($gap)) {
              continue;
          } ?>
          <article style="border:1px solid var(--border);border-radius:10px;padding:12px 14px;margin-bottom:10px;background:var(--bg)">
            <strong><?php echo esc_html((string) ($gap["keyword"] ?? "")); ?></strong>
            <p class="muted" style="font-size:12px;margin:6px 0"><?php esc_html_e("Poz.", "upsellio"); ?> <?php echo esc_html((string) round((float) ($gap["position"] ?? 0), 1)); ?> · <?php echo (int) ($gap["impressions"] ?? 0); ?> <?php esc_html_e("wyśw.", "upsellio"); ?> · <?php echo esc_html(($gap["gap_type"] ?? "") === "homepage_ranking" ? __("strona główna", "upsellio") : __("URL parametryczny", "upsellio")); ?></p>
            <p style="font-size:13px;margin:0 0 10px"><?php echo esc_html((string) ($gap["suggestion"] ?? "")); ?></p>
            <a class="btn" href="<?php echo esc_url(add_query_arg("seed", rawurlencode((string) ($gap["keyword"] ?? "")), $blog_base) . "#ups-blog-bot-panel"); ?>"><?php esc_html_e("→ Blog Bot (prefill)", "upsellio"); ?></a>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>

      <?php if (!empty($analysis["cannibalization"])) : ?>
        <h3 style="font-size:15px;margin:18px 0 8px"><?php esc_html_e("Kanibalizacja URL", "upsellio"); ?></h3>
        <?php foreach ($analysis["cannibalization"] as $c) : ?>
          <?php if (!is_array($c)) {
              continue;
          } ?>
          <article style="border:1px solid var(--border);border-radius:10px;padding:10px 12px;margin-bottom:8px;font-size:13px;background:var(--bg)">
            <strong><?php echo esc_html((string) ($c["keyword"] ?? "")); ?></strong>
            <?php foreach ($c["urls"] ?? [] as $u) : ?>
              <div><code style="font-size:12px"><?php echo esc_html((string) $u); ?></code></div>
            <?php endforeach; ?>
            <p style="margin:8px 0 0;font-size:12px;color:var(--text-2)"><?php echo esc_html((string) ($c["fix"] ?? "")); ?></p>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>

      <?php if (!empty($analysis["clusters"])) : ?>
        <h3 style="font-size:15px;margin:18px 0 8px"><?php esc_html_e("Klastry", "upsellio"); ?></h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:10px">
          <?php foreach ($analysis["clusters"] as $cluster) : ?>
            <?php if (!is_array($cluster)) {
                continue;
            } ?>
            <div style="border:1px solid var(--border);border-radius:10px;padding:10px 12px;background:var(--bg);font-size:13px">
              <strong><?php echo esc_html((string) ($cluster["name"] ?? "")); ?></strong>
              <p class="muted" style="margin:6px 0;font-size:12px"><?php echo (int) ($cluster["count"] ?? 0); ?> <?php esc_html_e("fraz · poz.", "upsellio"); ?> <?php echo esc_html((string) ($cluster["best_position"] ?? "")); ?> · <?php echo (int) ($cluster["total_impr"] ?? 0); ?> <?php esc_html_e("wyśw.", "upsellio"); ?></p>
              <p style="margin:0;font-size:12px;line-height:1.45"><?php echo esc_html(implode(", ", array_slice($cluster["keywords"] ?? [], 0, 4))); ?>…</p>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
    <script>
    (function(){
      var btn = document.getElementById("ups-gsc-refresh-analysis");
      if (!btn) return;
      var ajaxUrl = <?php echo wp_json_encode($ajax_url); ?>;
      btn.addEventListener("click", function(){
        btn.disabled = true;
        btn.textContent = <?php echo wp_json_encode(__("Przeliczanie…", "upsellio")); ?>;
        var body = new URLSearchParams();
        body.set("action", "upsellio_gsc_refresh_analysis");
        body.set("nonce", btn.getAttribute("data-nonce") || "");
        fetch(ajaxUrl, { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: body, credentials: "same-origin" })
          .then(function(r){ return r.json(); })
          .then(function(json){
            btn.disabled = false;
            btn.textContent = <?php echo wp_json_encode(__("Odśwież analizę GSC", "upsellio")); ?>;
            if (json.success) { window.location.reload(); }
            else { alert(json.data && json.data.message ? json.data.message : "Błąd"); }
          })
          .catch(function(){ btn.disabled = false; btn.textContent = <?php echo wp_json_encode(__("Odśwież analizę GSC", "upsellio")); ?>; alert("Sieć"); });
      });
    })();
    </script>
    <?php
}
