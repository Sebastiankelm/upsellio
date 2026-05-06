<?php
if (!defined("ABSPATH")) {
    exit;
}

add_action("admin_menu", function () {
    add_submenu_page(
        "upsellio-site-analytics",
        "AI — koszty i tokeny",
        "AI koszty",
        "edit_posts",
        "upsellio-ai-costs",
        "upsellio_render_ai_cost_dashboard"
    );
});

function upsellio_render_ai_cost_dashboard(): void
{
    $month = current_time("Y-m");
    $today = current_time("Y-m-d");
    $current_m  = (float) get_option("ups_ai_spend_m_{$month}", 0);
    $current_d  = (float) get_option("ups_ai_spend_d_{$today}", 0);
    $budget     = (float) get_option("ups_ai_monthly_budget_pln", 300);
    $tasks      = get_option("ups_ai_spend_tasks_" . $month, []);
    $log        = get_option("ups_ai_spend_log", []);
    $alerts     = get_option("ups_ai_budget_alerts", []);

    if (!is_array($tasks))  { $tasks  = []; }
    if (!is_array($log))    { $log    = []; }
    if (!is_array($alerts)) { $alerts = []; }

    if (isset($_POST["ups_ai_budget"]) && check_admin_referer("ups_ai_budget")) {
        $budget_new = max(0.0, floatval(wp_unslash((string) ($_POST["ups_ai_budget"] ?? "0"))));
        update_option("ups_ai_monthly_budget_pln", $budget_new);
        $budget = $budget_new;
        echo '<div class="notice notice-success"><p>Budżet zapisany.</p></div>';
    }

    arsort($tasks);
    $pct   = $budget > 0 ? min(100, ($current_m / $budget) * 100) : 0;
    $color = $pct < 70 ? "#15803d" : ($pct < 95 ? "#d97706" : "#d94c4c");

    // --- Statystyki z logu ---
    $total_in    = 0;
    $total_out   = 0;
    $total_cache = 0;
    $total_calls = count($log);
    $models_used = [];
    $tasks_calls = [];

    // Dane dzienne (ostatnie 30 dni)
    $daily_costs = [];
    $cutoff_30   = strtotime("-30 days");
    foreach ($log as $row) {
        if (!is_array($row)) { continue; }
        $total_in    += (int) ($row["in"]    ?? 0);
        $total_out   += (int) ($row["out"]   ?? 0);
        $total_cache += (int) ($row["cache"] ?? 0);
        $m = (string) ($row["model"] ?? "unknown");
        $models_used[$m] = ($models_used[$m] ?? 0) + 1;
        $t = (string) ($row["task"] ?? "unknown");
        $tasks_calls[$t] = ($tasks_calls[$t] ?? 0) + 1;

        $ts = strtotime((string) ($row["ts"] ?? ""));
        if ($ts && $ts >= $cutoff_30) {
            $day = date("Y-m-d", $ts);
            $daily_costs[$day] = ($daily_costs[$day] ?? 0) + (float) ($row["pln"] ?? 0);
        }
    }
    ksort($daily_costs);

    // Cache hit ratio
    $cache_ratio = ($total_in + $total_cache) > 0 ? round($total_cache / ($total_in + $total_cache) * 100, 1) : 0;

    // Wykres dzienny — dane JSON dla JS
    $chart_labels = array_keys($daily_costs);
    $chart_values = array_values($daily_costs);
    ?>
    <div class="wrap" style="max-width:1100px">
        <h1 style="margin-bottom:4px">AI — koszty i tokeny</h1>
        <p style="color:#666;margin-bottom:20px;font-size:13px">Dane z <code>ups_ai_spend_log</code> (ostatnie 1000 wywołań). Aby wywołania były widoczne, każdy plik AI musi ustawiać <code>$GLOBALS["upsellio_ai_current_task"]</code>.</p>

        <?php if (!empty($alerts)): ?>
        <div class="notice notice-error"><p>⚠ Ostatnio zablokowanych wywołań (budget cap): <?php echo count($alerts); ?>.
            <a href="#alerts-section">Zobacz →</a></p></div>
        <?php endif; ?>

        <!-- KPI cards -->
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px">
            <?php
            $cards = [
                ["DZIŚ",             number_format($current_d, 2) . " zł",    ""],
                ["TEN MIESIĄC",      number_format($current_m, 2) . " / " . number_format($budget, 0) . " zł", $color],
                ["WYWOŁAŃ W LOGU",   number_format($total_calls),              ""],
                ["CACHE HIT RATIO",  $cache_ratio . "%",                       $cache_ratio > 20 ? "#15803d" : "#d97706"],
            ];
            foreach ($cards as [$label, $value, $c]): ?>
            <div style="padding:16px;background:#fff;border-radius:10px;border:1px solid #ddd">
                <div style="font-size:10px;font-weight:700;letter-spacing:.8px;color:#888"><?php echo esc_html($label); ?></div>
                <div style="font-size:26px;font-weight:800;color:<?php echo esc_attr($c !== "" && $c !== "0" ? $c : "#111"); ?>;line-height:1.2;margin-top:4px"><?php echo esc_html($value); ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Progress bar budżetu -->
        <div style="background:#fff;border:1px solid #ddd;border-radius:10px;padding:16px;margin-bottom:24px">
            <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:13px">
                <span>Wykorzystanie budżetu miesięcznego</span>
                <strong style="color:<?php echo esc_attr($color); ?>"><?php echo number_format($pct, 1); ?>%</strong>
            </div>
            <div style="height:10px;background:#eee;border-radius:5px">
                <div style="height:10px;background:<?php echo esc_attr($color); ?>;border-radius:5px;width:<?php echo esc_attr((string) $pct); ?>%;transition:.3s"></div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">

            <!-- Tokeny sumaryczne -->
            <div style="background:#fff;border:1px solid #ddd;border-radius:10px;padding:18px">
                <h3 style="margin:0 0 14px;font-size:14px">Tokeny (cały log)</h3>
                <table style="width:100%;border-collapse:collapse;font-size:13px">
                    <tr style="border-bottom:1px solid #eee">
                        <td style="padding:6px 0;color:#555">Input tokens</td>
                        <td style="text-align:right;font-weight:600"><?php echo number_format($total_in); ?></td>
                    </tr>
                    <tr style="border-bottom:1px solid #eee">
                        <td style="padding:6px 0;color:#555">Output tokens</td>
                        <td style="text-align:right;font-weight:600"><?php echo number_format($total_out); ?></td>
                    </tr>
                    <tr style="border-bottom:1px solid #eee">
                        <td style="padding:6px 0;color:#555">Cache read tokens</td>
                        <td style="text-align:right;font-weight:600;color:#15803d"><?php echo number_format($total_cache); ?></td>
                    </tr>
                    <tr style="border-bottom:1px solid #eee">
                        <td style="padding:6px 0;color:#555">Łącznie</td>
                        <td style="text-align:right;font-weight:800"><?php echo number_format($total_in + $total_out + $total_cache); ?></td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0;color:#555">Cache hit ratio</td>
                        <td style="text-align:right;font-weight:600;color:<?php echo $cache_ratio > 20 ? "#15803d" : "#d97706"; ?>"><?php echo esc_html((string) $cache_ratio); ?>%</td>
                    </tr>
                </table>
            </div>

            <!-- Modele -->
            <div style="background:#fff;border:1px solid #ddd;border-radius:10px;padding:18px">
                <h3 style="margin:0 0 14px;font-size:14px">Wywołania per model (cały log)</h3>
                <table style="width:100%;border-collapse:collapse;font-size:13px">
                    <?php arsort($models_used); foreach ($models_used as $m => $cnt): ?>
                    <tr style="border-bottom:1px solid #eee">
                        <td style="padding:5px 0;color:#555"><?php echo esc_html($m); ?></td>
                        <td style="text-align:right;font-weight:600"><?php echo esc_html((string) $cnt); ?> wywołań</td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>

        <!-- Wykres dzienny -->
        <?php if (!empty($daily_costs)): ?>
        <div style="background:#fff;border:1px solid #ddd;border-radius:10px;padding:18px;margin-bottom:24px">
            <h3 style="margin:0 0 14px;font-size:14px">Koszty dzienne (ostatnie 30 dni) — PLN</h3>
            <canvas id="ups-ai-chart" height="80"></canvas>
        </div>
        <script>
        (function(){
            const labels = <?php echo wp_json_encode($chart_labels); ?>;
            const values = <?php echo wp_json_encode(array_map(static fn ($v) => round((float) $v, 4), $chart_values)); ?>;
            const canvas = document.getElementById('ups-ai-chart');
            if (!canvas) return;
            const W = canvas.offsetWidth;
            canvas.width = W;
            canvas.height = 120;
            const pad = {t:10, r:10, b:30, l:48};
            const cW = W - pad.l - pad.r;
            const cH = 120 - pad.t - pad.b;
            const max = Math.max(...values, 0.01);
            ctx.clearRect(0,0,W,120);
            const bw = Math.max(2, (cW / labels.length) - 2);
            labels.forEach((lbl, i) => {
                const x = pad.l + (i / labels.length) * cW + 1;
                const h = (values[i] / max) * cH;
                ctx.fillStyle = '#0d9488';
                ctx.fillRect(x, pad.t + cH - h, bw, h);
            });
            ctx.fillStyle = '#888';
            ctx.font = '10px system-ui,sans-serif';
            ctx.textAlign = 'center';
            labels.forEach((lbl, i) => {
                if (i % 5 === 0 || i === labels.length - 1) {
                    const x = pad.l + (i / labels.length) * cW + bw/2;
                    ctx.fillText(lbl.slice(5), x, 120 - 6);
                }
            });
            ctx.textAlign = 'right';
            [0, 0.25, 0.5, 0.75, 1].forEach((pct) => {
                const y = pad.t + cH * (1 - pct);
                ctx.fillStyle = '#bbb';
                ctx.fillRect(pad.l, y, cW, 1);
                ctx.fillStyle = '#888';
                ctx.fillText((max * pct).toFixed(2), pad.l - 4, y + 3);
            });
        })();
        </script>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">

            <!-- Per task koszt -->
            <div style="background:#fff;border:1px solid #ddd;border-radius:10px;padding:18px">
                <h3 style="margin:0 0 14px;font-size:14px">Koszt per task — <?php echo esc_html($month); ?></h3>
                <?php if (empty($tasks)): ?>
                <p style="color:#888;font-size:13px">Brak danych — upewnij się że pliki AI ustawiają <code>$GLOBALS["upsellio_ai_current_task"]</code></p>
                <?php else: ?>
                <table style="width:100%;border-collapse:collapse;font-size:13px">
                    <thead><tr style="border-bottom:2px solid #eee">
                        <th style="text-align:left;padding:4px 0">Task</th>
                        <th style="text-align:right">PLN</th>
                        <th style="text-align:right">%</th>
                        <th style="text-align:right">Wywołań</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($tasks as $t => $cost):
                        $calls = $tasks_calls[$t] ?? "—";
                    ?>
                    <tr style="border-bottom:1px solid #f3f3f3">
                        <td style="padding:5px 0;font-family:monospace;font-size:12px"><?php echo esc_html((string) $t); ?></td>
                        <td style="text-align:right;font-weight:600"><?php echo number_format((float) $cost, 2); ?></td>
                        <td style="text-align:right;color:#888"><?php echo $current_m > 0 ? number_format((float) $cost / $current_m * 100, 1) : 0; ?>%</td>
                        <td style="text-align:right;color:#888"><?php echo esc_html((string) $calls); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- Ustawienia budżetu -->
            <div style="background:#fff;border:1px solid #ddd;border-radius:10px;padding:18px">
                <h3 style="margin:0 0 14px;font-size:14px">Ustawienia budżetu</h3>
                <form method="post">
                    <?php wp_nonce_field("ups_ai_budget"); ?>
                    <label style="display:block;margin-bottom:10px;font-size:13px">
                        Miesięczny limit (PLN):<br>
                        <input type="number" name="ups_ai_budget" value="<?php echo esc_attr((string) $budget); ?>"
                            min="0" step="10" style="width:100%;padding:8px;margin-top:4px;border:1px solid #ccc;border-radius:6px" />
                    </label>
                    <button class="button button-primary">Zapisz limit</button>
                </form>
                <hr style="margin:16px 0;border:none;border-top:1px solid #eee">
                <p style="font-size:12px;color:#666;margin:0">
                    Przy przekroczeniu limitu <code>upsellio_ai_can_call()</code> blokuje wywołania AI i zapisuje alert.<br><br>
                    <strong>Estymacja:</strong><br>
                    Blog Bot (Sonnet, ~4000 tok/wpis): ~0.08 zł/wpis<br>
                    Scoring leada (Haiku, ~500 tok): ~0.003 zł/lead<br>
                    Draft inbox (Sonnet, ~900 tok): ~0.015 zł/draft<br>
                    CPT optimizer (Sonnet, ~5000 tok): ~0.10 zł/wpis
                </p>
            </div>
        </div>

        <!-- Log ostatnich wywołań -->
        <div style="background:#fff;border:1px solid #ddd;border-radius:10px;padding:18px;margin-bottom:24px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                <h3 style="margin:0;font-size:14px">Ostatnie 100 wywołań</h3>
                <span style="font-size:12px;color:#888">Łącznie w logu: <?php echo number_format($total_calls); ?></span>
            </div>
            <div style="overflow-x:auto">
            <table class="wp-list-table widefat striped" style="font-size:12px">
                <thead><tr>
                    <th>Czas</th>
                    <th>Task</th>
                    <th>Model</th>
                    <th style="text-align:right">In tok.</th>
                    <th style="text-align:right">Out tok.</th>
                    <th style="text-align:right">Cache tok.</th>
                    <th style="text-align:right">Koszt PLN</th>
                </tr></thead>
                <tbody>
                <?php foreach (array_slice($log, 0, 100) as $row):
                    if (!is_array($row)) {
                        continue;
                    }
                    $task = (string) ($row["task"] ?? "");
                    $is_unknown = ($task === "unknown" || $task === "");
                ?>
                <tr style="<?php echo $is_unknown ? "background:#fff8f0" : ""; ?>">
                    <td><?php echo esc_html((string) ($row["ts"] ?? "")); ?></td>
                    <td><?php
                        if ($is_unknown) {
                            echo '<span style="color:#d97706;font-weight:600">⚠ unknown</span>';
                        } else {
                            echo '<code style="font-size:11px">' . esc_html($task) . '</code>';
                        }
                    ?></td>
                    <td style="color:#555"><?php echo esc_html((string) ($row["model"] ?? "")); ?></td>
                    <td style="text-align:right"><?php echo number_format((int) ($row["in"] ?? 0)); ?></td>
                    <td style="text-align:right"><?php echo number_format((int) ($row["out"] ?? 0)); ?></td>
                    <td style="text-align:right;color:#15803d"><?php echo number_format((int) ($row["cache"] ?? 0)); ?></td>
                    <td style="text-align:right;font-weight:600"><?php echo number_format((float) ($row["pln"] ?? 0), 4); ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>

        <!-- Alerty budżetowe -->
        <?php if (!empty($alerts)): ?>
        <div id="alerts-section" style="background:#fff8f0;border:1px solid #f97316;border-radius:10px;padding:18px;margin-bottom:24px">
            <h3 style="margin:0 0 12px;font-size:14px;color:#c2410c">⚠ Alerty budżetowe (ostatnie <?php echo count($alerts); ?>)</h3>
            <table class="wp-list-table widefat" style="font-size:12px">
                <thead><tr><th>Czas</th><th>Task</th><th>Wydano</th><th>Limit</th></tr></thead>
                <tbody>
                <?php foreach (array_slice(array_reverse($alerts), 0, 20) as $a): ?>
                <tr>
                    <td><?php echo esc_html((string) ($a["ts"] ?? "")); ?></td>
                    <td><code><?php echo esc_html((string) ($a["task"] ?? "")); ?></code></td>
                    <td><?php echo number_format((float) ($a["current"] ?? 0), 2); ?> zł</td>
                    <td><?php echo number_format((float) ($a["budget"] ?? 0), 0); ?> zł</td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Instrukcja dla "unknown" -->
        <?php
        $unknown_count = count(array_filter($log, static fn ($r) => is_array($r) && (($r["task"] ?? "") === "unknown" || ($r["task"] ?? "") === "")));
        if ($unknown_count > 0): ?>
        <div style="background:#f0f9ff;border:1px solid #0ea5e9;border-radius:10px;padding:18px">
            <h3 style="margin:0 0 8px;font-size:14px;color:#0369a1">ℹ <?php echo esc_html((string) $unknown_count); ?> wywołań bez etykiety task</h3>
            <p style="font-size:13px;margin:0 0 10px">Pliki poniżej wywołują API bez ustawiania <code>$GLOBALS["upsellio_ai_current_task"]</code>. Dodaj tę linię przed każdym wywołaniem API:</p>
            <table style="font-size:12px;width:100%;border-collapse:collapse">
                <?php
                $missing = [
                    "anthropic-blog-bot.php"        => "blog_bot",
                    "blog-seo-tool.php"             => "seo_blog_tool",
                    "cpt-ai-optimizer.php"          => "cpt_optimizer",
                    "anthropic-offer-ai.php"        => "offer_ai_fill",
                    "anthropic-topic-generator.php" => "topic_generator",
                    "client-audit.php"              => "client_audit",
                    "keyword-research.php"          => "keyword_research",
                    "suggestions.php"               => "suggestions",
                    "site-analytics.php"            => "site_analytics",
                    "anthropic-ai-tests.php"        => "ai_tests",
                ];
                foreach ($missing as $file => $task): ?>
                <tr style="border-bottom:1px solid #e0f2fe">
                    <td style="padding:5px 0;font-family:monospace;color:#0369a1"><?php echo esc_html("inc/" . $file); ?></td>
                    <td style="padding:5px 8px"><code>$GLOBALS["upsellio_ai_current_task"] = "<?php echo esc_html($task); ?>";</code></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php endif; ?>

    </div>
    <?php
}
