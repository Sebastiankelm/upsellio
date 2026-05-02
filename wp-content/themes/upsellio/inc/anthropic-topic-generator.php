<?php

if (!defined("ABSPATH")) {
    exit;
}

/**
 * CPT leadów CRM (crm_lead) lub legacy (lead).
 */
function upsellio_topicgen_resolve_lead_post_type(): string
{
    if (post_type_exists("crm_lead")) {
        return "crm_lead";
    }
    if (post_type_exists("lead")) {
        return "lead";
    }

    return "";
}

/**
 * GSC: Frazy w pozycjach 5–20 z dobrymi impresji — największy potencjał wzrostu.
 *
 * @return array<int, array<string, mixed>>
 */
function upsellio_topicgen_get_gsc_opportunities(int $limit = 15): array
{
    $rows = get_option("upsellio_keyword_metrics_rows", []);
    if (!is_array($rows) || empty($rows)) {
        return [];
    }

    $candidates = [];
    foreach ($rows as $row) {
        $position = (float) ($row["position"] ?? 0);
        $impressions = (int) ($row["impressions"] ?? 0);
        $clicks = (int) ($row["clicks"] ?? 0);
        $ctr = (float) ($row["ctr"] ?? 0);
        $keyword = sanitize_text_field((string) ($row["keyword"] ?? ""));

        if ($keyword === "" || $position <= 0) {
            continue;
        }
        if ($position < 5 || $position > 20) {
            continue;
        }
        if ($impressions < 20) {
            continue;
        }

        $opportunity_score = (int) round(($impressions / 10) * (1 - ($position / 25)));
        $candidates[] = [
            "keyword" => $keyword,
            "position" => $position,
            "impressions" => $impressions,
            "clicks" => $clicks,
            "ctr" => $ctr,
            "opportunity_score" => $opportunity_score,
        ];
    }

    usort($candidates, static function ($a, $b) {
        return (int) ($b["opportunity_score"] ?? 0) <=> (int) ($a["opportunity_score"] ?? 0);
    });

    return array_slice($candidates, 0, $limit);
}

/**
 * GSC: Frazy które już przynoszą kliknięcia — tematy do rozbudowania.
 *
 * @return array<int, array{keyword: string, clicks: int}>
 */
function upsellio_topicgen_get_gsc_converting(int $limit = 10): array
{
    $rows = get_option("upsellio_keyword_metrics_rows", []);
    if (!is_array($rows) || empty($rows)) {
        return [];
    }

    $by_keyword = [];
    foreach ($rows as $row) {
        $keyword = sanitize_text_field((string) ($row["keyword"] ?? ""));
        $clicks = (int) ($row["clicks"] ?? 0);
        if ($keyword === "" || $clicks < 1) {
            continue;
        }
        if (!isset($by_keyword[$keyword])) {
            $by_keyword[$keyword] = 0;
        }
        $by_keyword[$keyword] += $clicks;
    }

    arsort($by_keyword);
    $result = [];
    foreach (array_slice($by_keyword, 0, $limit, true) as $kw => $cl) {
        $result[] = ["keyword" => $kw, "clicks" => (int) $cl];
    }

    return $result;
}

/**
 * GA4: Najlepsze kanały wg jakości (ups_automation_channel_quality_scores — tablica asocjacyjna).
 *
 * @return array<int, array<string, mixed>>
 */
function upsellio_topicgen_get_ga4_top_channels(int $limit = 5): array
{
    $scores = get_option("ups_automation_channel_quality_scores", []);
    if (!is_array($scores) || empty($scores)) {
        return [];
    }

    $list = [];
    foreach ($scores as $row) {
        if (!is_array($row)) {
            continue;
        }
        $list[] = $row;
    }

    usort($list, static function ($a, $b) {
        return (int) ($b["score"] ?? 0) <=> (int) ($a["score"] ?? 0);
    });

    return array_slice($list, 0, $limit);
}

/**
 * CRM Leady: Najczęstsze serwisy i fragmenty wiadomości.
 *
 * @return array{services: array<string, int>, messages: array<int, string>}
 */
function upsellio_topicgen_get_crm_lead_insights(int $limit = 30): array
{
    $pt = upsellio_topicgen_resolve_lead_post_type();
    if ($pt === "") {
        return ["services" => [], "messages" => []];
    }

    $leads = get_posts([
        "post_type" => $pt,
        "post_status" => "publish",
        "posts_per_page" => $limit,
        "orderby" => "date",
        "order" => "DESC",
        "fields" => "ids",
    ]);

    if (empty($leads)) {
        return ["services" => [], "messages" => []];
    }

    $services = [];
    $messages = [];

    foreach ($leads as $lid) {
        $lid = (int) $lid;
        $post = get_post($lid);
        if (!($post instanceof WP_Post)) {
            continue;
        }

        $service = sanitize_text_field((string) get_post_meta($lid, "_upsellio_lead_service", true));
        $msg = wp_strip_all_tags((string) $post->post_content);

        if ($service !== "") {
            $services[$service] = ($services[$service] ?? 0) + 1;
        }
        if ($msg !== "") {
            if (function_exists("mb_substr")) {
                $messages[] = mb_substr($msg, 0, 200, "UTF-8");
            } else {
                $messages[] = substr($msg, 0, 200);
            }
        }
    }

    arsort($services);

    return [
        "services" => array_slice($services, 0, 8, true),
        "messages" => array_slice($messages, 0, 10),
    ];
}

/**
 * CRM Oferty: Wygrane oferty — tytuły jako kupowane usługi/projekty.
 *
 * @return array<int, string>
 */
function upsellio_topicgen_get_crm_won_services(int $limit = 20): array
{
    if (!post_type_exists("crm_offer")) {
        return [];
    }

    $offers = get_posts([
        "post_type" => "crm_offer",
        "post_status" => ["publish", "private"],
        "posts_per_page" => $limit,
        "fields" => "ids",
        "meta_query" => [
            [
                "key" => "_ups_offer_status",
                "value" => "won",
            ],
        ],
    ]);

    if (empty($offers)) {
        return [];
    }

    $services = [];
    foreach ($offers as $oid) {
        $oid = (int) $oid;
        $title = get_the_title($oid);
        if ($title !== "") {
            $services[] = sanitize_text_field($title);
        }
    }

    return $services;
}

/**
 * Blog: Opublikowane wpisy — żeby nie duplikować tematów.
 *
 * @return array<int, string>
 */
function upsellio_topicgen_get_existing_posts(int $limit = 20): array
{
    $posts = get_posts([
        "post_type" => "post",
        "post_status" => "publish",
        "posts_per_page" => $limit,
        "orderby" => "date",
        "order" => "DESC",
        "fields" => "ids",
    ]);

    $titles = [];
    foreach ($posts as $pid) {
        $t = get_the_title((int) $pid);
        if ($t !== "") {
            $titles[] = $t;
        }
    }

    return $titles;
}

function upsellio_topicgen_build_prompt(int $count): string
{
    $gsc_opp = upsellio_topicgen_get_gsc_opportunities(15);
    $gsc_conv = upsellio_topicgen_get_gsc_converting(10);
    $ga4_channels = upsellio_topicgen_get_ga4_top_channels(5);
    $lead_insights = upsellio_topicgen_get_crm_lead_insights(30);
    $won_services = upsellio_topicgen_get_crm_won_services(20);
    $existing = upsellio_topicgen_get_existing_posts(20);

    $company_ctx = function_exists("upsellio_anthropic_crm_get_specialized_company_context")
        ? upsellio_anthropic_crm_get_specialized_company_context("topicgen")
        : trim((string) get_option("ups_ai_company_context", ""));
    if ($company_ctx === "") {
        $company_ctx = trim((string) get_option("ups_anthropic_company_context", ""));
    }

    $sections = [];

    if ($company_ctx !== "") {
        $sections[] = "KONTEKST FIRMY:\n" . $company_ctx;
    }

    $priority_ctx = "";
    if (!empty($gsc_opp)) {
        $priority_ctx .= "PRIORYTET 1 (SEO quick wins): Frazy GSC pozycje 5–20 — priorytetyzuj tematy, które mogą awansować do TOP5.\n";
    }
    if (!empty($lead_insights["messages"]) || !empty($lead_insights["services"])) {
        $priority_ctx .= "PRIORYTET 2 (sprzedaż): Pytania i usługi z formularzy leadowych — to realny popyt.\n";
    }
    if (!empty($gsc_conv)) {
        $priority_ctx .= "PRIORYTET 3 (rozbudowa): Frazy z kliknięciami — tematy satelitarne lub pogłębiające istniejący ruch.\n";
    }
    if (!empty($won_services)) {
        $priority_ctx .= "PRIORYTET 4 (konwersja): Obszary z wygranych ofert — content sprzedażowy pod te usługi.\n";
    }
    if ($priority_ctx !== "") {
        $sections[] = "HIERARCHIA ŹRÓDEŁ (wybieraj tematy zgodnie z kolejnością — wyższy priorytet ważniejszy przy konflikcie danych):\n"
            . trim($priority_ctx);
    }

    if (function_exists("upsellio_ai_master_context")) {
        $master_blog = upsellio_ai_master_context("blog");
        if ($master_blog !== "") {
            $sections[] = "WYNIKI BLOGA (co faktycznie generuje leady — priorytetyzuj podobne tematy i unikaj martwego ruchu):\n" . $master_blog;
        }
    }

    $sections[] = "ZADANIE:\nWygeneruj dokładnie {$count} tematów wpisów blogowych w języku polskim. "
        . "Tematy mają wspierać SEO, odpowiadać na realne pytania potencjalnych klientów B2B i być powiązane z usługami firmy. "
        . "Każdy temat to gotowy tytuł artykułu — konkretny, z główną frazą kluczową, bez clickbaitu.";

    if (!empty($gsc_opp)) {
        $lines = [];
        foreach ($gsc_opp as $r) {
            $lines[] = "  - \"{$r['keyword']}\" (poz. {$r['position']}, {$r['impressions']} wyświetleń, {$r['clicks']} kliknięć)";
        }
        $sections[] = "FRAZY Z GOOGLE SEARCH CONSOLE — pozycje 5-20, największy potencjał wzrostu:\n"
            . "Te frazy już mają widoczność ale niskie CTR. Napisz artykuł który awansuje je do top 5.\n"
            . implode("\n", $lines);
    }

    if (!empty($gsc_conv)) {
        $lines = [];
        foreach ($gsc_conv as $r) {
            $lines[] = "  - \"{$r['keyword']}\" ({$r['clicks']} kliknięć)";
        }
        $sections[] = "FRAZY KTÓRE JUŻ PRZYNOSZĄ RUCH — rozważ artykuły satelitarne lub pogłębione:\n"
            . implode("\n", $lines);
    }

    if (!empty($ga4_channels)) {
        $lines = [];
        foreach ($ga4_channels as $ch) {
            $src = (string) ($ch["source"] ?? "");
            $camp = (string) ($ch["campaign"] ?? "");
            $sc = (int) ($ch["score"] ?? 0);
            $sess = (int) ($ch["sessions"] ?? 0);
            $lines[] = "  - {$src} / {$camp} (score: {$sc}/100, {$sess} sesji)";
        }
        $sections[] = "NAJLEPSZE KANAŁY MARKETINGOWE (GA4) — pisz o tematach związanych z tymi kanałami:\n"
            . implode("\n", $lines);
    }

    if (!empty($lead_insights["services"])) {
        $lines = [];
        foreach ($lead_insights["services"] as $svc => $cnt) {
            $lines[] = "  - {$svc} ({$cnt} leadów)";
        }
        $sections[] = "USŁUGI O KTÓRE PYTAJĄ LEADY (ostatnie 30) — pisz o tych obszarach:\n"
            . implode("\n", $lines);
    }

    if (!empty($lead_insights["messages"])) {
        $sample = array_slice($lead_insights["messages"], 0, 5);
        $lines = array_map(static function ($m) {
            return "  - \"" . str_replace('"', "'", (string) $m) . "\"";
        }, $sample);
        $sections[] = "PRZYKŁADOWE PYTANIA/PROBLEMY KLIENTÓW Z FORMULARZY — odpowiedz na te pytania w artykułach:\n"
            . implode("\n", $lines);
    }

    if (!empty($won_services)) {
        $sections[] = "WYGRANE OFERTY (usługi które kupili klienci) — pisz o wartości tych usług:\n"
            . implode(", ", array_unique(array_slice($won_services, 0, 8)));
    }

    if (!empty($existing)) {
        $sections[] = "ISTNIEJĄCE WPISY NA BLOGU — NIE DUPLIKUJ tych tematów, możesz pisać uzupełnienia lub powiązane:\n"
            . implode("\n", array_map(static function ($t) {
                return "  - {$t}";
            }, array_slice($existing, 0, 15)));
    }

    $sections[] = "ZASADY:\n"
        . "1. Stosuj hierarchię PRIORYTET 1→4 z sekcji powyżej (gdy brakuje GSC, schodź niżej — nie wymyślaj priorytetu 1 z niczego).\n"
        . "2. Odpowiadaj na konkretne pytania z formularzy leadowych\n"
        . "3. Każdy temat = gotowy tytuł artykułu z główną frazą\n"
        . "4. Mix: artykuły poradnikowe + porównawcze + case study\n"
        . "5. Język: polski, B2B, konkretny\n"
        . "6. NIE powtarzaj istniejących wpisów\n\n"
        . "Odpowiedz WYŁĄCZNIE jednym obiektem JSON (bez markdown, bez komentarzy):\n"
        . '{"topics":["Tytuł 1","Tytuł 2","Tytuł 3",...]}';

    return implode("\n\n---\n\n", $sections);
}

/**
 * @return array{ok: bool, message?: string, topics?: array<int, string>, count?: int, sources?: array<int, string>, raw?: string}
 */
function upsellio_topicgen_run(int $count = 10): array
{
    if (!function_exists("upsellio_anthropic_crm_api_key") || upsellio_anthropic_crm_api_key() === "") {
        return ["ok" => false, "message" => "Brak klucza API Anthropic. Ustaw w CRM → Ustawienia → Ogólne."];
    }

    $count = max(3, min(30, $count));
    $prompt = upsellio_topicgen_build_prompt($count);

    $model = trim((string) get_option("ups_blog_bot_model", ""));
    if ($model === "") {
        $model = "claude-haiku-4-5-20251001";
    }

    $raw = upsellio_anthropic_crm_send_user_prompt($prompt, 1200, 45, $model);
    if ($raw === null) {
        return ["ok" => false, "message" => "Brak odpowiedzi z API. Sprawdź klucz API i limity."];
    }

    $data = null;
    $raw = trim((string) $raw);
    if (preg_match("/\{[\s\S]*\}/", $raw, $m)) {
        $data = json_decode($m[0], true);
    }

    if (!is_array($data) || empty($data["topics"]) || !is_array($data["topics"])) {
        $preview = function_exists("mb_substr") ? mb_substr($raw, 0, 500, "UTF-8") : substr($raw, 0, 500);

        return ["ok" => false, "message" => "Niepoprawna odpowiedź JSON. Spróbuj ponownie.", "raw" => $preview];
    }

    $topics = array_values(array_filter(array_map(static function ($t) {
        return sanitize_text_field((string) $t);
    }, $data["topics"])));

    if (empty($topics)) {
        return ["ok" => false, "message" => "AI zwróciło pustą listę tematów."];
    }

    $mode = (string) get_option("ups_topicgen_mode", "append");
    $existing = (string) get_option("ups_blog_bot_keywords_queue", "");
    $ex_lines = array_values(array_filter(array_map("trim", preg_split("/\r\n|\n|\r/", $existing))));

    if ($mode === "append") {
        $merged = array_unique(array_merge($ex_lines, $topics));
    } else {
        $merged = $topics;
    }

    $queue_text = implode("\n", $merged);
    update_option("ups_blog_bot_keywords_queue", $queue_text, false);
    update_option("ups_topicgen_last_run", current_time("mysql"), false);
    update_option("ups_topicgen_last_count", count($topics), false);

    $sources_used = [];
    if (!empty(upsellio_topicgen_get_gsc_opportunities(1))) {
        $sources_used[] = "GSC (pozycje 5-20)";
    }
    if (!empty(upsellio_topicgen_get_gsc_converting(1))) {
        $sources_used[] = "GSC (kliknięcia)";
    }
    if (!empty(upsellio_topicgen_get_ga4_top_channels(1))) {
        $sources_used[] = "GA4 (kanały)";
    }
    $lead_i = upsellio_topicgen_get_crm_lead_insights(1);
    if (!empty($lead_i["services"]) || !empty($lead_i["messages"])) {
        $sources_used[] = "CRM Leady";
    }
    if (!empty(upsellio_topicgen_get_crm_won_services(1))) {
        $sources_used[] = "CRM Oferty (wygrane)";
    }
    update_option("ups_topicgen_sources_used", implode(", ", $sources_used), false);

    return [
        "ok" => true,
        "topics" => $topics,
        "count" => count($topics),
        "sources" => $sources_used,
        "message" => "Wygenerowano " . count($topics) . " tematów na podstawie: " . implode(", ", $sources_used),
        /** Pełna kolejka po zapisie — front synchronizuje textarea, żeby „Zapisz AI” nie nadpisał starym stanem. */
        "keywords_queue" => $queue_text,
        "queue_line_count" => count($merged),
    ];
}

function upsellio_topicgen_ajax(): void
{
    if (!current_user_can("manage_options")) {
        wp_send_json_error(["message" => "forbidden"], 403);
    }
    check_ajax_referer("upsellio_topicgen_nonce", "nonce");

    $count = isset($_POST["count"]) ? max(3, min(30, (int) wp_unslash($_POST["count"]))) : 10;
    $mode = isset($_POST["mode"]) ? sanitize_key(wp_unslash($_POST["mode"])) : "append";
    if (!in_array($mode, ["append", "replace"], true)) {
        $mode = "append";
    }

    update_option("ups_topicgen_mode", $mode, false);

    $result = upsellio_topicgen_run($count);
    if ($result["ok"]) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error($result);
    }
}
add_action("wp_ajax_upsellio_topicgen_run", "upsellio_topicgen_ajax");

function upsellio_topicgen_preview_ajax(): void
{
    if (!current_user_can("manage_options")) {
        wp_send_json_error(["message" => "forbidden"], 403);
    }
    check_ajax_referer("upsellio_topicgen_nonce", "nonce");

    $gsc_opp = upsellio_topicgen_get_gsc_opportunities(15);
    $gsc_conv = upsellio_topicgen_get_gsc_converting(10);
    $ga4_channels = upsellio_topicgen_get_ga4_top_channels(5);
    $lead_insights = upsellio_topicgen_get_crm_lead_insights(30);
    $won_services = upsellio_topicgen_get_crm_won_services(20);

    wp_send_json_success([
        "gsc_opportunities" => $gsc_opp,
        "gsc_converting" => $gsc_conv,
        "ga4_channels" => $ga4_channels,
        "lead_services" => $lead_insights["services"],
        "lead_messages" => array_slice($lead_insights["messages"], 0, 5),
        "won_services" => array_unique(array_slice($won_services, 0, 8)),
        "existing_posts" => upsellio_topicgen_get_existing_posts(10),
    ]);
}
add_action("wp_ajax_upsellio_topicgen_preview", "upsellio_topicgen_preview_ajax");

function upsellio_topicgen_render_panel(): void
{
    if (!current_user_can("manage_options")) {
        return;
    }

    $nonce = wp_create_nonce("upsellio_topicgen_nonce");
    $ajax_url = admin_url("admin-ajax.php");
    $last_run = (string) get_option("ups_topicgen_last_run", "");
    $last_count = (int) get_option("ups_topicgen_last_count", 0);
    $last_src = (string) get_option("ups_topicgen_sources_used", "");
    $queue_count = count(array_filter(array_map("trim", preg_split("/\r\n|\n|\r/", (string) get_option("ups_blog_bot_keywords_queue", "")))));
    ?>
	<section class="card" style="margin-top:16px" id="ups-topicgen-section">
	<h2 style="margin-top:0;display:flex;align-items:center;gap:10px">
		Generator tematów AI
		<span style="font-size:12px;font-weight:400;color:var(--text-3)">Analizuje GSC, GA4, CRM i generuje tematy dla Blog Bota</span>
	</h2>

	<div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:16px">
		<div style="background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:12px">
			<div style="font-size:11px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Kolejka tematów</div>
			<div id="ups-tg-queue-count" style="font-size:22px;font-weight:800;font-family:var(--font-display,Syne,sans-serif);letter-spacing:-.03em"><?php echo esc_html((string) $queue_count); ?></div>
			<div style="font-size:11px;color:var(--text-3)">tematów czeka na Blog Bota</div>
		</div>
		<div style="background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:12px">
			<div style="font-size:11px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Ostatnie generowanie</div>
			<div style="font-size:13px;font-weight:700"><?php echo esc_html($last_run !== "" ? $last_run : "—"); ?></div>
			<div style="font-size:11px;color:var(--text-3)"><?php echo $last_count > 0 ? esc_html((string) $last_count . " tematów") : esc_html("Nie uruchamiano"); ?></div>
		</div>
		<div style="background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:12px">
			<div style="font-size:11px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Ostatnie źródła</div>
			<div style="font-size:12px;font-weight:600;line-height:1.4"><?php echo esc_html($last_src !== "" ? $last_src : "—"); ?></div>
		</div>
	</div>

	<div style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;margin-bottom:14px">
		<label style="display:flex;flex-direction:column;gap:4px;font-size:13px;font-weight:700">
			Liczba tematów do wygenerowania
			<input type="number" id="ups-tg-count" min="3" max="30" value="10" style="width:100px;border:1px solid var(--border);border-radius:8px;padding:8px 10px;font-size:14px;background:var(--bg);color:var(--text)" />
		</label>
		<label style="display:flex;flex-direction:column;gap:4px;font-size:13px;font-weight:700">
			Tryb dodawania do kolejki
			<select id="ups-tg-mode" style="border:1px solid var(--border);border-radius:8px;padding:8px 10px;font-size:13px;background:var(--bg);color:var(--text)">
				<option value="append">Dopisz do istniejącej kolejki</option>
				<option value="replace">Zastąp całą kolejkę</option>
			</select>
		</label>
		<button type="button" class="btn" id="ups-tg-run" style="margin-bottom:1px">✨ Generuj tematy</button>
		<button type="button" class="btn alt" id="ups-tg-preview" style="margin-bottom:1px">👁 Podgląd danych</button>
	</div>

	<div id="ups-tg-status" style="display:none;padding:10px 14px;border-radius:10px;font-size:13px;margin-bottom:12px"></div>

	<div id="ups-tg-results" style="display:none">
		<h3 style="font-size:14px;margin-bottom:8px">Wygenerowane tematy <span id="ups-tg-results-meta" style="font-weight:400;color:var(--text-3);font-size:12px"></span></h3>
		<ul id="ups-tg-topics-list" style="margin:0;padding:0;list-style:none;display:grid;gap:6px"></ul>
		<p style="margin:10px 0 0;font-size:12px;color:var(--text-3)">Tematy są zapisane w bazie. Pole <strong>Kolejka tematów</strong> w formularzu Blog Bota powyżej uzupełnia się automatycznie — przed „Zapisz ustawienia AI / Blog” nie musisz odświeżać strony (stare pole nadpisałoby kolejkę).</p>
	</div>

	<div id="ups-tg-preview-box" style="display:none;margin-top:14px">
		<h3 style="font-size:14px;margin-bottom:10px">Dane które zostaną użyte w prompcie</h3>
		<div id="ups-tg-preview-content" style="display:grid;gap:10px"></div>
	</div>

	<script>
	(function(){
		const nonce   = <?php echo wp_json_encode($nonce); ?>;
		const ajaxUrl = <?php echo wp_json_encode($ajax_url); ?>;
		const runBtn     = document.getElementById('ups-tg-run');
		const prevBtn    = document.getElementById('ups-tg-preview');
		const statusEl   = document.getElementById('ups-tg-status');
		const resultsEl  = document.getElementById('ups-tg-results');
		const topicsList = document.getElementById('ups-tg-topics-list');
		const resultMeta = document.getElementById('ups-tg-results-meta');
		const previewBox = document.getElementById('ups-tg-preview-box');
		const previewCnt = document.getElementById('ups-tg-preview-content');

		function setStatus(msg, type) {
			const colors = {
				ok:   {bg:'#e8f8f2',color:'#085041',border:'#bfe9d9'},
				err:  {bg:'#fff4f4',color:'#9f3636',border:'#f0d4d4'},
				info: {bg:'#e6f1fb',color:'#0c447c',border:'#b5d4f4'},
			};
			const c = colors[type] || colors.info;
			statusEl.style.cssText = `display:block;background:${c.bg};color:${c.color};border:1px solid ${c.border};border-radius:10px;padding:10px 14px;font-size:13px;margin-bottom:12px`;
			statusEl.textContent = msg;
		}

		function previewDataSection(title, items, renderFn) {
			if (!items || (Array.isArray(items) && items.length === 0) || (typeof items === 'object' && !Array.isArray(items) && Object.keys(items).length === 0)) {
				return `<div style="background:var(--bg,#fafaf7);border:1px solid var(--border,#e7e7e1);border-radius:10px;padding:10px 14px"><div style="font-size:11px;font-weight:700;color:var(--text-3,#7c7c74);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">${title}</div><div style="font-size:12px;color:var(--text-3,#7c7c74)">Brak danych</div></div>`;
			}
			return `<div style="background:var(--bg,#fafaf7);border:1px solid var(--border,#e7e7e1);border-radius:10px;padding:10px 14px"><div style="font-size:11px;font-weight:700;color:var(--text-3,#7c7c74);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px">${title}</div>${renderFn(items)}</div>`;
		}

		runBtn.addEventListener('click', function(){
			const count = parseInt(document.getElementById('ups-tg-count').value, 10) || 10;
			const mode  = document.getElementById('ups-tg-mode').value;
			runBtn.disabled = true;
			runBtn.textContent = '⏳ Generuję…';
			resultsEl.style.display = 'none';
			previewBox.style.display = 'none';
			setStatus('Analizuję dane z GSC, GA4 i CRM, następnie wywołuję Claude API…', 'info');

			const body = new FormData();
			body.append('action', 'upsellio_topicgen_run');
			body.append('nonce', nonce);
			body.append('count', String(count));
			body.append('mode', mode);

			fetch(ajaxUrl, {method:'POST', body})
				.then(r => r.json())
				.then(data => {
					runBtn.disabled = false;
					runBtn.textContent = '✨ Generuj tematy';
					if (data.success && data.data && data.data.topics) {
						const d = data.data;
						setStatus('✓ ' + d.message, 'ok');
						resultMeta.textContent = '— źródła: ' + (d.sources && d.sources.length ? d.sources.join(', ') : 'Claude API');
						topicsList.innerHTML = d.topics.map((t,i) => `<li style="display:flex;align-items:baseline;gap:8px;padding:8px 12px;background:var(--surface,#fff);border:1px solid var(--border,#e7e7e1);border-radius:10px;font-size:13px"><span style="font-size:11px;font-weight:800;color:var(--text-3,#7c7c74);min-width:20px">${i+1}.</span><span>${escH(t)}</span></li>`).join('');
						resultsEl.style.display = 'block';
						const q = typeof d.keywords_queue === 'string' ? d.keywords_queue : '';
						const ta = document.getElementById('ups-blog-bot-keywords-queue') || document.querySelector('textarea[name="ups_blog_bot_keywords_queue"]');
						if (ta && q !== '') {
							ta.value = q;
						}
						const qc = document.getElementById('ups-tg-queue-count');
						if (qc) {
							const n = typeof d.queue_line_count === 'number' ? d.queue_line_count : (q ? q.split(/\r\n|\n|\r/).map(function(s){ return s.trim(); }).filter(Boolean).length : 0);
							qc.textContent = String(n);
						}
					} else {
						const msg = (data.data && data.data.message) ? data.data.message : 'Błąd generowania.';
						setStatus('✗ ' + msg, 'err');
					}
				})
				.catch(err => {
					runBtn.disabled = false;
					runBtn.textContent = '✨ Generuj tematy';
					setStatus('Błąd sieci: ' + err.message, 'err');
				});
		});

		prevBtn.addEventListener('click', function(){
			prevBtn.disabled = true;
			prevBtn.textContent = '⏳ Ładuję…';
			previewBox.style.display = 'none';

			const body = new FormData();
			body.append('action', 'upsellio_topicgen_preview');
			body.append('nonce', nonce);

			fetch(ajaxUrl, {method:'POST', body})
				.then(r => r.json())
				.then(data => {
					prevBtn.disabled = false;
					prevBtn.textContent = '👁 Podgląd danych';
					if (!data.success) { setStatus('Błąd ładowania podglądu.', 'err'); return; }
					const d = data.data;
					let html = '';

					html += previewDataSection('GSC — frazy w pozycjach 5-20 (potencjał SEO)', d.gsc_opportunities, items =>
						items.map(r => `<div style="display:flex;justify-content:space-between;align-items:center;padding:4px 0;border-bottom:.5px solid var(--border,#e7e7e1);font-size:12px"><span style="font-weight:600">${escH(r.keyword)}</span><span style="color:var(--text-3,#7c7c74)">poz. ${r.position} · ${r.impressions} wyświetleń · ${r.clicks} kliknięć</span></div>`).join('')
					);

					html += previewDataSection('GSC — frazy z największym ruchem', d.gsc_converting, items =>
						items.map(r => `<div style="display:flex;justify-content:space-between;font-size:12px;padding:4px 0;border-bottom:.5px solid var(--border,#e7e7e1)"><span style="font-weight:600">${escH(r.keyword)}</span><span style="color:var(--text-3,#7c7c74)">${r.clicks} kliknięć</span></div>`).join('')
					);

					html += previewDataSection('GA4 — najlepsze kanały', d.ga4_channels, items =>
						items.map(r => `<div style="font-size:12px;padding:4px 0;border-bottom:.5px solid var(--border,#e7e7e1)">${escH(r.source)} / ${escH(r.campaign || '—')} — score: ${r.score}/100, ${r.sessions} sesji</div>`).join('')
					);

					html += previewDataSection('CRM — usługi leadów', d.lead_services, items => {
						const entries = Object.entries(items);
						return entries.map(([svc,cnt]) => `<div style="display:flex;justify-content:space-between;font-size:12px;padding:4px 0;border-bottom:.5px solid var(--border,#e7e7e1)"><span>${escH(svc)}</span><span style="color:var(--text-3,#7c7c74)">${cnt} leadów</span></div>`).join('');
					});

					html += previewDataSection('CRM — pytania klientów z formularzy', d.lead_messages, items =>
						items.map(m => `<div style="font-size:12px;padding:5px 0;border-bottom:.5px solid var(--border,#e7e7e1);color:var(--text-2,#3d3d38)">"${escH(m)}"</div>`).join('')
					);

					html += previewDataSection('CRM — wygrane usługi', d.won_services, items =>
						Array.isArray(items) ? `<div style="font-size:12px;line-height:1.6">${items.map(escH).join(', ')}</div>` : ''
					);

					html += previewDataSection('Istniejące wpisy (nie duplikuj)', d.existing_posts, items =>
						items.map(t => `<div style="font-size:12px;padding:3px 0;color:var(--text-2,#3d3d38)">${escH(t)}</div>`).join('')
					);

					previewCnt.innerHTML = html;
					previewBox.style.display = 'block';
					previewBox.scrollIntoView({behavior:'smooth', block:'nearest'});
				})
				.catch(err => {
					prevBtn.disabled = false;
					prevBtn.textContent = '👁 Podgląd danych';
					setStatus('Błąd: ' + err.message, 'err');
				});
		});

		function escH(str) {
			const d = document.createElement('div');
			d.textContent = str || '';
			return d.innerHTML;
		}
	})();
	</script>
	</section>
    <?php
}
