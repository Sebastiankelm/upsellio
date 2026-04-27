<?php

if (!defined("ABSPATH")) {
    exit;
}

function upsellio_advanced_tests_option_key($user_id)
{
    return "upsellio_advanced_tests_state_" . (int) $user_id;
}

function upsellio_get_advanced_tests_state($user_id = 0)
{
    $user_id = $user_id > 0 ? (int) $user_id : (int) get_current_user_id();
    if ($user_id <= 0) {
        return [];
    }

    $state = get_option(upsellio_advanced_tests_option_key($user_id), []);
    return is_array($state) ? $state : [];
}

function upsellio_save_advanced_tests_state($state, $user_id = 0)
{
    $user_id = $user_id > 0 ? (int) $user_id : (int) get_current_user_id();
    if ($user_id <= 0) {
        return;
    }

    update_option(upsellio_advanced_tests_option_key($user_id), (array) $state, false);
}

function upsellio_normalize_test_url($url)
{
    $url = trim((string) $url);
    if ($url === "") {
        return "";
    }

    $parsed = wp_parse_url($url);
    if (!is_array($parsed) || empty($parsed["host"])) {
        return "";
    }

    $scheme = isset($parsed["scheme"]) ? $parsed["scheme"] : "https";
    $host = strtolower((string) $parsed["host"]);
    $path = isset($parsed["path"]) ? (string) $parsed["path"] : "/";
    $query = isset($parsed["query"]) && $parsed["query"] !== "" ? "?" . $parsed["query"] : "";

    return esc_url_raw($scheme . "://" . $host . $path . $query);
}

function upsellio_build_advanced_test_urls($max_urls = 260)
{
    $max_urls = max(20, min(800, (int) $max_urls));
    $urls_map = [];

    $seed_urls = [
        home_url("/"),
        home_url("/blog/"),
        home_url("/definicje/"),
        home_url("/miasta/"),
        home_url("/portfolio/"),
        home_url("/portfolio-marketingowe/"),
        home_url("/lead-magnety/"),
        home_url("/kontakt/"),
        home_url("/polityka-prywatnosci/"),
        home_url("/robots.txt"),
        home_url("/wp-sitemap.xml"),
        home_url("/sitemap_index.xml"),
    ];

    foreach ($seed_urls as $url) {
        $normalized = upsellio_normalize_test_url($url);
        if ($normalized !== "") {
            $urls_map[$normalized] = true;
        }
    }

    $post_ids = get_posts([
        "post_type" => ["post", "page", "miasto", "definicja", "portfolio", "lead_magnet", "marketing_portfolio"],
        "post_status" => "publish",
        "posts_per_page" => $max_urls,
        "orderby" => "date",
        "order" => "DESC",
        "fields" => "ids",
        "no_found_rows" => true,
    ]);

    foreach ($post_ids as $post_id) {
        $permalink = get_permalink((int) $post_id);
        $normalized = upsellio_normalize_test_url($permalink);
        if ($normalized !== "") {
            $urls_map[$normalized] = true;
        }
    }

    $urls = array_keys($urls_map);
    usort($urls, "strnatcasecmp");

    $homepage = upsellio_normalize_test_url(home_url("/"));
    if ($homepage !== "" && in_array($homepage, $urls, true)) {
        $urls = array_values(array_diff($urls, [$homepage]));
        array_unshift($urls, $homepage);
    }

    return array_slice($urls, 0, $max_urls);
}

function upsellio_extract_title_from_html($html)
{
    if (!is_string($html) || $html === "") {
        return "";
    }
    if (!preg_match("/<title[^>]*>(.*?)<\\/title>/is", $html, $match)) {
        return "";
    }

    $title = html_entity_decode(wp_strip_all_tags((string) ($match[1] ?? "")), ENT_QUOTES, "UTF-8");
    return trim(preg_replace("/\\s+/", " ", $title));
}

function upsellio_safe_substr($value, $start, $length)
{
    $value = (string) $value;
    $start = (int) $start;
    $length = (int) $length;

    return function_exists("mb_substr") ? mb_substr($value, $start, $length) : substr($value, $start, $length);
}

function upsellio_body_fingerprint($html)
{
    if (!is_string($html) || $html === "") {
        return "";
    }

    $stripped = wp_strip_all_tags($html);
    $stripped = preg_replace("/\\s+/", " ", (string) $stripped);
    $stripped = trim((string) $stripped);

    if ($stripped === "") {
        return "";
    }

    return hash("sha256", upsellio_safe_substr($stripped, 0, 35000));
}

function upsellio_probe_advanced_test_url($url, $homepage_fingerprint = "")
{
    $started_at = microtime(true);
    $response = wp_remote_get($url, [
        "timeout" => 18,
        "redirection" => 8,
        "sslverify" => false,
        "headers" => [
            "User-Agent" => "Upsellio-Advanced-Tester/1.0",
            "Cache-Control" => "no-cache",
            "Pragma" => "no-cache",
        ],
    ]);
    $elapsed_ms = (int) round((microtime(true) - $started_at) * 1000);

    if (is_wp_error($response)) {
        return [
            "url" => $url,
            "final_url" => $url,
            "ok" => false,
            "status" => 0,
            "elapsed_ms" => $elapsed_ms,
            "title" => "",
            "fingerprint" => "",
            "is_same_as_homepage" => false,
            "checks" => [
                "has_doctype" => false,
                "has_h1" => false,
                "has_nav" => false,
                "has_critical_error_banner" => false,
                "has_wp_error_screen" => false,
            ],
            "alerts" => ["wp_error:" . $response->get_error_message()],
            "body_sample" => "",
        ];
    }

    $status = (int) wp_remote_retrieve_response_code($response);
    $body = (string) wp_remote_retrieve_body($response);
    $title = upsellio_extract_title_from_html($body);
    $fingerprint = upsellio_body_fingerprint($body);
    $body_sample = upsellio_safe_substr(preg_replace("/\\s+/", " ", $body), 0, 420);
    $body_lc = strtolower($body);
    $final_url = $url;

    if (isset($response["http_response"]) && method_exists($response["http_response"], "get_response_object")) {
        $response_object = $response["http_response"]->get_response_object();
        if (is_object($response_object) && isset($response_object->url) && is_string($response_object->url) && $response_object->url !== "") {
            $final_url = (string) $response_object->url;
        }
    }

    $checks = [
        "has_doctype" => strpos($body_lc, "<!doctype html") !== false,
        "has_h1" => preg_match("/<h1\\b/i", $body) === 1,
        "has_nav" => preg_match("/<nav\\b|class=[\"'][^\"']*nav[^\"']*[\"']/i", $body) === 1,
        "has_critical_error_banner" => strpos($body_lc, "w witrynie wystąpił błąd krytyczny") !== false || strpos($body_lc, "there has been a critical error") !== false,
        "has_wp_error_screen" => strpos($body_lc, "id=\"error-page\"") !== false || strpos($body_lc, "wordpress › błąd") !== false,
    ];

    $alerts = [];
    if ($status >= 500) {
        $alerts[] = "status_5xx";
    } elseif ($status >= 400) {
        $alerts[] = "status_4xx";
    }
    if ($elapsed_ms > 2500) {
        $alerts[] = "slow_response";
    }
    if (!$checks["has_doctype"]) {
        $alerts[] = "missing_doctype";
    }
    if (!$checks["has_h1"]) {
        $alerts[] = "missing_h1";
    }
    if ($checks["has_critical_error_banner"] || $checks["has_wp_error_screen"]) {
        $alerts[] = "critical_error_screen";
    }

    $is_same_as_homepage = $homepage_fingerprint !== "" && $fingerprint !== "" && $fingerprint === $homepage_fingerprint;
    if ($is_same_as_homepage && upsellio_normalize_test_url($url) !== upsellio_normalize_test_url(home_url("/"))) {
        $alerts[] = "same_as_homepage";
    }

    $ok = ($status >= 200 && $status < 400) && !$checks["has_critical_error_banner"] && !$checks["has_wp_error_screen"];

    return [
        "url" => $url,
        "final_url" => $final_url,
        "ok" => $ok,
        "status" => $status,
        "elapsed_ms" => $elapsed_ms,
        "title" => $title,
        "fingerprint" => $fingerprint,
        "is_same_as_homepage" => $is_same_as_homepage,
        "checks" => $checks,
        "alerts" => $alerts,
        "body_sample" => $body_sample,
    ];
}

function upsellio_summarize_advanced_test_results($results)
{
    $results = is_array($results) ? $results : [];
    $summary = [
        "tested_count" => count($results),
        "ok_count" => 0,
        "failed_count" => 0,
        "critical_error_count" => 0,
        "same_as_homepage_count" => 0,
        "status_buckets" => [
            "1xx" => 0,
            "2xx" => 0,
            "3xx" => 0,
            "4xx" => 0,
            "5xx" => 0,
            "error" => 0,
        ],
        "avg_elapsed_ms" => 0,
        "max_elapsed_ms" => 0,
        "duplicate_fingerprint_urls" => [],
    ];

    if (empty($results)) {
        return $summary;
    }

    $latency_total = 0;
    $fingerprints = [];

    foreach ($results as $row) {
        $status = (int) ($row["status"] ?? 0);
        $ok = !empty($row["ok"]);
        $elapsed = (int) ($row["elapsed_ms"] ?? 0);
        $alerts = isset($row["alerts"]) && is_array($row["alerts"]) ? $row["alerts"] : [];
        $fingerprint = (string) ($row["fingerprint"] ?? "");
        $url = (string) ($row["url"] ?? "");

        if ($ok) {
            $summary["ok_count"]++;
        } else {
            $summary["failed_count"]++;
        }

        if ($status <= 0) {
            $summary["status_buckets"]["error"]++;
        } else {
            $bucket_key = floor($status / 100) . "xx";
            if (!isset($summary["status_buckets"][$bucket_key])) {
                $summary["status_buckets"][$bucket_key] = 0;
            }
            $summary["status_buckets"][$bucket_key]++;
        }

        if (in_array("critical_error_screen", $alerts, true)) {
            $summary["critical_error_count"]++;
        }
        if (!empty($row["is_same_as_homepage"])) {
            $summary["same_as_homepage_count"]++;
        }

        $latency_total += $elapsed;
        $summary["max_elapsed_ms"] = max($summary["max_elapsed_ms"], $elapsed);

        if ($fingerprint !== "" && $url !== "") {
            if (!isset($fingerprints[$fingerprint])) {
                $fingerprints[$fingerprint] = [];
            }
            $fingerprints[$fingerprint][] = $url;
        }
    }

    $summary["avg_elapsed_ms"] = (int) round($latency_total / max(1, count($results)));

    foreach ($fingerprints as $urls) {
        if (count($urls) > 1) {
            $summary["duplicate_fingerprint_urls"][] = array_values($urls);
        }
    }

    return $summary;
}

function upsellio_start_advanced_tests_ajax()
{
    if (!current_user_can("manage_options")) {
        wp_send_json_error(["message" => "forbidden"], 403);
    }

    check_ajax_referer("upsellio_advanced_tests_nonce", "nonce");

    $max_urls = isset($_POST["max_urls"]) ? (int) $_POST["max_urls"] : 260;
    $max_urls = max(20, min(800, $max_urls));
    $urls = upsellio_build_advanced_test_urls($max_urls);

    $state = [
        "started_at" => current_time("mysql"),
        "started_at_gmt" => gmdate("c"),
        "status" => "running",
        "config" => [
            "max_urls" => $max_urls,
            "batch_size" => 8,
        ],
        "queue" => $urls,
        "cursor" => 0,
        "results" => [],
        "logs" => [
            "[" . current_time("H:i:s") . "] Start testu. Kolejka URL: " . count($urls),
        ],
        "summary" => [],
        "homepage_fingerprint" => "",
        "completed_at" => "",
        "completed_at_gmt" => "",
    ];

    upsellio_save_advanced_tests_state($state);

    wp_send_json_success([
        "message" => "started",
        "queue_count" => count($urls),
        "state" => [
            "status" => $state["status"],
            "cursor" => 0,
            "queue_count" => count($urls),
        ],
    ]);
}
add_action("wp_ajax_upsellio_start_advanced_tests", "upsellio_start_advanced_tests_ajax");

function upsellio_step_advanced_tests_ajax()
{
    if (!current_user_can("manage_options")) {
        wp_send_json_error(["message" => "forbidden"], 403);
    }

    check_ajax_referer("upsellio_advanced_tests_nonce", "nonce");

    $state = upsellio_get_advanced_tests_state();
    if (empty($state)) {
        wp_send_json_error(["message" => "missing_state"], 404);
    }

    $queue = isset($state["queue"]) && is_array($state["queue"]) ? $state["queue"] : [];
    $cursor = isset($state["cursor"]) ? (int) $state["cursor"] : 0;
    $batch_size = isset($state["config"]["batch_size"]) ? (int) $state["config"]["batch_size"] : 8;
    $batch_size = max(1, min(20, $batch_size));
    $processed_rows = [];
    $log_lines = [];

    if (($state["status"] ?? "") !== "running") {
        wp_send_json_success([
            "done" => true,
            "state" => [
                "status" => $state["status"] ?? "completed",
                "cursor" => $cursor,
                "queue_count" => count($queue),
                "summary" => $state["summary"] ?? [],
            ],
            "processed_rows" => [],
            "log_lines" => [],
        ]);
    }

    $results = isset($state["results"]) && is_array($state["results"]) ? $state["results"] : [];
    $homepage_fingerprint = (string) ($state["homepage_fingerprint"] ?? "");

    $stop = min($cursor + $batch_size, count($queue));
    for ($i = $cursor; $i < $stop; $i++) {
        $url = (string) $queue[$i];
        $row = upsellio_probe_advanced_test_url($url, $homepage_fingerprint);
        if ($i === 0 && $homepage_fingerprint === "" && !empty($row["fingerprint"])) {
            $homepage_fingerprint = (string) $row["fingerprint"];
            $row["is_same_as_homepage"] = false;
            $row["alerts"] = array_values(array_diff((array) ($row["alerts"] ?? []), ["same_as_homepage"]));
        }

        $results[] = $row;
        $processed_rows[] = $row;

        $status_label = (int) ($row["status"] ?? 0);
        $ok_label = !empty($row["ok"]) ? "OK" : "FAIL";
        $alert_label = !empty($row["alerts"]) ? " [" . implode(", ", (array) $row["alerts"]) . "]" : "";
        $log_lines[] = "[" . current_time("H:i:s") . "] {$ok_label} {$status_label} " . $url . " ({$row["elapsed_ms"]} ms)" . $alert_label;
    }

    $cursor = $stop;
    $state["cursor"] = $cursor;
    $state["results"] = $results;
    $state["homepage_fingerprint"] = $homepage_fingerprint;

    $existing_logs = isset($state["logs"]) && is_array($state["logs"]) ? $state["logs"] : [];
    $state["logs"] = array_slice(array_merge($existing_logs, $log_lines), -6000);

    $done = $cursor >= count($queue);
    if ($done) {
        $state["status"] = "completed";
        $state["completed_at"] = current_time("mysql");
        $state["completed_at_gmt"] = gmdate("c");
        $state["summary"] = upsellio_summarize_advanced_test_results($results);
        $state["logs"][] = "[" . current_time("H:i:s") . "] Zakończono testy. Przetestowano: " . count($results);
    }

    upsellio_save_advanced_tests_state($state);

    wp_send_json_success([
        "done" => $done,
        "state" => [
            "status" => $state["status"],
            "cursor" => $cursor,
            "queue_count" => count($queue),
            "summary" => $state["summary"],
            "started_at" => $state["started_at"] ?? "",
            "completed_at" => $state["completed_at"] ?? "",
        ],
        "processed_rows" => $processed_rows,
        "log_lines" => $log_lines,
    ]);
}
add_action("wp_ajax_upsellio_step_advanced_tests", "upsellio_step_advanced_tests_ajax");

function upsellio_get_advanced_tests_state_ajax()
{
    if (!current_user_can("manage_options")) {
        wp_send_json_error(["message" => "forbidden"], 403);
    }

    check_ajax_referer("upsellio_advanced_tests_nonce", "nonce");
    $state = upsellio_get_advanced_tests_state();

    if (empty($state)) {
        wp_send_json_success([
            "exists" => false,
            "state" => [],
        ]);
    }

    wp_send_json_success([
        "exists" => true,
        "state" => [
            "status" => $state["status"] ?? "",
            "cursor" => (int) ($state["cursor"] ?? 0),
            "queue_count" => count((array) ($state["queue"] ?? [])),
            "summary" => $state["summary"] ?? [],
            "started_at" => $state["started_at"] ?? "",
            "completed_at" => $state["completed_at"] ?? "",
            "logs" => $state["logs"] ?? [],
            "results" => $state["results"] ?? [],
        ],
    ]);
}
add_action("wp_ajax_upsellio_get_advanced_tests_state", "upsellio_get_advanced_tests_state_ajax");

function upsellio_register_advanced_tests_admin_page()
{
    add_submenu_page(
        "tools.php",
        "Zaawansowane testy Upsellio",
        "Zaawansowane testy",
        "manage_options",
        "upsellio-advanced-tests",
        "upsellio_render_advanced_tests_admin_page",
        97
    );
}
add_action("admin_menu", "upsellio_register_advanced_tests_admin_page");

function upsellio_handle_advanced_tests_export()
{
    if (!is_admin() || !current_user_can("manage_options")) {
        return;
    }
    if (!isset($_GET["page"]) || (string) $_GET["page"] !== "upsellio-advanced-tests") {
        return;
    }
    if (!isset($_GET["upsellio_advanced_tests_action"]) || (string) $_GET["upsellio_advanced_tests_action"] !== "export_json") {
        return;
    }

    $nonce = isset($_GET["_upsellio_nonce"]) ? sanitize_text_field((string) wp_unslash($_GET["_upsellio_nonce"])) : "";
    if (!wp_verify_nonce($nonce, "upsellio_advanced_tests_export_json")) {
        return;
    }

    $state = upsellio_get_advanced_tests_state();
    header("Content-Type: application/json; charset=utf-8");
    header("Content-Disposition: attachment; filename=upsellio-advanced-tests-" . gmdate("Ymd-His") . ".json");
    echo wp_json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}
add_action("admin_init", "upsellio_handle_advanced_tests_export");

function upsellio_render_advanced_tests_admin_page()
{
    if (!current_user_can("manage_options")) {
        return;
    }

    $nonce = wp_create_nonce("upsellio_advanced_tests_nonce");
    $ajax_url = admin_url("admin-ajax.php");
    $export_url = wp_nonce_url(
        add_query_arg([
            "page" => "upsellio-advanced-tests",
            "upsellio_advanced_tests_action" => "export_json",
        ], admin_url("tools.php")),
        "upsellio_advanced_tests_export_json",
        "_upsellio_nonce"
    );
    ?>
    <div class="wrap">
      <h1>Zaawansowane testy strony</h1>
      <p>
        Narzędzie uruchamia rozszerzony smoke test: statusy HTTP, czasy odpowiedzi, wykrywanie krytycznych błędów WordPress,
        podstawowe kontrole struktury HTML, duplikaty fingerprintów treści i szczegółowy log krok po kroku.
      </p>

      <div style="background:#fff;border:1px solid #dcdcdc;border-radius:12px;padding:14px;display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
        <label>
          <span style="display:block;margin-bottom:4px;">Maksymalna liczba URL-i</span>
          <input id="ups-adv-max-urls" type="number" min="20" max="800" value="260" style="width:120px;" />
        </label>
        <button id="ups-adv-start" class="button button-primary">Uruchom testy</button>
        <button id="ups-adv-refresh" class="button">Odśwież stan</button>
        <a class="button" href="<?php echo esc_url($export_url); ?>">Eksport pełnego logu (JSON)</a>
      </div>

      <div id="ups-adv-summary" style="margin-top:14px;padding:12px;border:1px solid #dcdcdc;background:#fff;border-radius:12px;">
        Brak aktywnego testu.
      </div>

      <h2 style="margin-top:20px;">Szczegółowe wyniki URL</h2>
      <div style="max-height:420px;overflow:auto;border:1px solid #dcdcdc;border-radius:8px;background:#fff;">
        <table class="widefat striped" id="ups-adv-results-table">
          <thead>
            <tr>
              <th style="width:68px;">Status</th>
              <th style="width:80px;">ms</th>
              <th style="width:40%;">URL</th>
              <th style="width:40%;">Diagnoza</th>
            </tr>
          </thead>
          <tbody id="ups-adv-results-body">
            <tr><td colspan="4">Brak wyników.</td></tr>
          </tbody>
        </table>
      </div>

      <h2 style="margin-top:20px;">Pełny szczegółowy log</h2>
      <textarea id="ups-adv-log" rows="18" class="large-text code" readonly></textarea>
    </div>

    <script>
    (function () {
      const ajaxUrl = <?php echo wp_json_encode($ajax_url); ?>;
      const nonce = <?php echo wp_json_encode($nonce); ?>;
      const summaryNode = document.getElementById("ups-adv-summary");
      const logNode = document.getElementById("ups-adv-log");
      const startBtn = document.getElementById("ups-adv-start");
      const refreshBtn = document.getElementById("ups-adv-refresh");
      const maxUrlsInput = document.getElementById("ups-adv-max-urls");
      const resultsBody = document.getElementById("ups-adv-results-body");
      let running = false;

      function postAction(action, extra = {}) {
        const body = new URLSearchParams();
        body.set("action", action);
        body.set("nonce", nonce);
        Object.keys(extra).forEach((key) => body.set(key, String(extra[key])));
        return fetch(ajaxUrl, {
          method: "POST",
          body,
          credentials: "same-origin",
        }).then((res) => res.json());
      }

      function appendLogs(lines) {
        if (!Array.isArray(lines) || lines.length === 0) return;
        const prefix = logNode.value.trim() === "" ? "" : "\n";
        logNode.value += prefix + lines.join("\n");
        logNode.scrollTop = logNode.scrollHeight;
      }

      function renderRow(row) {
        const status = Number(row.status || 0);
        const statusLabel = status > 0 ? status : "ERR";
        const statusColor = status >= 500 || !row.ok ? "#b42318" : status >= 400 ? "#b54708" : "#027a48";
        const alerts = Array.isArray(row.alerts) && row.alerts.length ? row.alerts.join(", ") : "brak alertów";
        const title = row.title ? " | " + row.title : "";
        const tr = document.createElement("tr");
        tr.innerHTML =
          "<td><strong style='color:" + statusColor + ";'>" + statusLabel + "</strong></td>" +
          "<td>" + Number(row.elapsed_ms || 0) + "</td>" +
          "<td><code>" + escapeHtml(String(row.url || "")) + "</code></td>" +
          "<td>" + escapeHtml(alerts + title) + "</td>";
        resultsBody.appendChild(tr);
      }

      function escapeHtml(str) {
        return str
          .replace(/&/g, "&amp;")
          .replace(/</g, "&lt;")
          .replace(/>/g, "&gt;")
          .replace(/"/g, "&quot;")
          .replace(/'/g, "&#039;");
      }

      function renderSummary(state) {
        const cursor = Number(state.cursor || 0);
        const queue = Number(state.queue_count || 0);
        const percent = queue > 0 ? Math.round((cursor / queue) * 100) : 0;
        const summary = state.summary || {};
        const testedCount = Number(summary.tested_count || 0);
        const okCount = Number(summary.ok_count || 0);
        const failedCount = Number(summary.failed_count || 0);
        const criticalCount = Number(summary.critical_error_count || 0);
        const sameAsHome = Number(summary.same_as_homepage_count || 0);
        const avgMs = Number(summary.avg_elapsed_ms || 0);
        const status = String(state.status || "");

        summaryNode.innerHTML =
          "<div><strong>Status:</strong> " + escapeHtml(status || "idle") + " | " +
          "<strong>Postęp:</strong> " + cursor + " / " + queue + " (" + percent + "%)" + "</div>" +
          "<div style='margin-top:6px;'>" +
          "<strong>OK:</strong> " + okCount + " | " +
          "<strong>FAIL:</strong> " + failedCount + " | " +
          "<strong>Krytyczne błędy:</strong> " + criticalCount + " | " +
          "<strong>Duplikaty homepage:</strong> " + sameAsHome + " | " +
          "<strong>Śr. czas:</strong> " + avgMs + " ms" +
          "</div>" +
          "<div style='margin-top:8px;height:10px;background:#f2f4f7;border-radius:999px;overflow:hidden;'>" +
          "<span style='display:block;height:100%;width:" + Math.max(0, Math.min(100, percent)) + "%;background:#1570ef;'></span>" +
          "</div>" +
          "<div style='margin-top:6px;color:#667085;'>Przetestowano rekordów: " + testedCount + "</div>";
      }

      function resetResults() {
        resultsBody.innerHTML = "";
        logNode.value = "";
      }

      function runStepLoop() {
        if (!running) return;
        postAction("upsellio_step_advanced_tests")
          .then((payload) => {
            if (!payload || !payload.success) throw new Error("step_failed");
            const data = payload.data || {};
            const rows = Array.isArray(data.processed_rows) ? data.processed_rows : [];
            const logs = Array.isArray(data.log_lines) ? data.log_lines : [];
            appendLogs(logs);
            rows.forEach(renderRow);
            renderSummary(data.state || {});

            if (data.done) {
              running = false;
              startBtn.disabled = false;
              appendLogs(["=== Test zakończony ==="]);
              return;
            }

            window.setTimeout(runStepLoop, 180);
          })
          .catch((error) => {
            running = false;
            startBtn.disabled = false;
            appendLogs(["[ERROR] " + (error && error.message ? error.message : "unknown")]);
          });
      }

      startBtn.addEventListener("click", function () {
        const maxUrls = Math.max(20, Math.min(800, Number(maxUrlsInput.value || 260)));
        startBtn.disabled = true;
        resetResults();
        postAction("upsellio_start_advanced_tests", { max_urls: maxUrls })
          .then((payload) => {
            if (!payload || !payload.success) throw new Error("start_failed");
            const state = payload.data && payload.data.state ? payload.data.state : {};
            appendLogs(["[INFO] Uruchomiono testy. URL-e w kolejce: " + Number(payload.data.queue_count || 0)]);
            renderSummary(state);
            running = true;
            runStepLoop();
          })
          .catch((error) => {
            startBtn.disabled = false;
            appendLogs(["[ERROR] " + (error && error.message ? error.message : "start_failed")]);
          });
      });

      refreshBtn.addEventListener("click", function () {
        postAction("upsellio_get_advanced_tests_state")
          .then((payload) => {
            if (!payload || !payload.success) throw new Error("refresh_failed");
            const exists = !!(payload.data && payload.data.exists);
            const state = payload.data && payload.data.state ? payload.data.state : {};
            if (!exists) {
              summaryNode.textContent = "Brak aktywnego testu.";
              return;
            }

            resultsBody.innerHTML = "";
            const results = Array.isArray(state.results) ? state.results : [];
            if (results.length === 0) {
              resultsBody.innerHTML = "<tr><td colspan='4'>Brak wyników.</td></tr>";
            } else {
              results.forEach(renderRow);
            }

            const logs = Array.isArray(state.logs) ? state.logs : [];
            logNode.value = logs.join("\n");
            renderSummary(state);
          })
          .catch((error) => {
            appendLogs(["[ERROR] " + (error && error.message ? error.message : "refresh_failed")]);
          });
      });
    })();
    </script>
    <?php
}
