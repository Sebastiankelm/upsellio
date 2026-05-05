<?php
if (!defined("ABSPATH")) {
    exit;
}

add_action("admin_menu", function () {
    add_submenu_page(
        "upsellio-site-analytics",
        "AI — koszty",
        "AI koszty",
        "edit_posts",
        "upsellio-ai-costs",
        "upsellio_render_ai_cost_dashboard"
    );
});

function upsellio_render_ai_cost_dashboard()
{
    $month = current_time("Y-m");
    $today = current_time("Y-m-d");
    $current_m = (float) get_option("ups_ai_spend_m_{$month}", 0);
    $current_d = (float) get_option("ups_ai_spend_d_{$today}", 0);
    $budget = (float) get_option("ups_ai_monthly_budget_pln", 300);
    $tasks = get_option("ups_ai_spend_tasks_" . $month, []);
    $log = get_option("ups_ai_spend_log", []);

    if (!is_array($tasks)) {
        $tasks = [];
    }
    if (!is_array($log)) {
        $log = [];
    }

    if (isset($_POST["ups_ai_budget"]) && check_admin_referer("ups_ai_budget")) {
        update_option("ups_ai_monthly_budget_pln", (float) $_POST["ups_ai_budget"]);
        echo '<div class="notice notice-success"><p>Budżet zapisany.</p></div>';
        $budget = (float) $_POST["ups_ai_budget"];
    }

    arsort($tasks);
    $pct = $budget > 0 ? min(100, ($current_m / $budget) * 100) : 0;
    $color = $pct < 70 ? "#15803d" : ($pct < 95 ? "#d97706" : "#d94c4c");
    ?>
    <div class="wrap">
        <h1>AI — koszty</h1>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin:20px 0;">
            <div style="padding:18px;background:#fff;border-radius:12px;border:1px solid #ddd;">
                <div style="font-size:11px;color:#888;">DZISIAJ</div>
                <div style="font-size:32px;font-weight:800;"><?php echo number_format($current_d, 2); ?> zł</div>
            </div>
            <div style="padding:18px;background:#fff;border-radius:12px;border:1px solid #ddd;">
                <div style="font-size:11px;color:#888;">TEN MIESIĄC</div>
                <div style="font-size:32px;font-weight:800;color:<?php echo esc_attr($color); ?>;">
                    <?php echo number_format($current_m, 2); ?> / <?php echo number_format($budget, 0); ?> zł
                </div>
                <div style="height:8px;background:#eee;border-radius:4px;margin-top:8px;">
                    <div style="height:8px;background:<?php echo esc_attr($color); ?>;border-radius:4px;width:<?php echo esc_attr((string) $pct); ?>%;"></div>
                </div>
            </div>
            <div style="padding:18px;background:#fff;border-radius:12px;border:1px solid #ddd;">
                <form method="post">
                    <?php wp_nonce_field("ups_ai_budget"); ?>
                    <label>Budżet miesięczny (zł):
                        <input type="number" name="ups_ai_budget" value="<?php echo esc_attr((string) $budget); ?>" min="0" step="10" />
                    </label>
                    <button class="button button-primary">Zapisz</button>
                </form>
            </div>
        </div>

        <h2>Per task — ten miesiąc</h2>
        <table class="wp-list-table widefat striped">
            <thead><tr><th>Task</th><th>Koszt (zł)</th><th>% miesiąca</th></tr></thead>
            <tbody>
                <?php foreach ($tasks as $t => $cost): ?>
                <tr>
                    <td><?php echo esc_html((string) $t); ?></td>
                    <td><?php echo number_format((float) $cost, 2); ?></td>
                    <td><?php echo $current_m > 0 ? number_format((((float) $cost / $current_m) * 100), 1) : 0; ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Ostatnie 50 wywołań</h2>
        <table class="wp-list-table widefat striped">
            <thead><tr><th>Czas</th><th>Task</th><th>Model</th><th>Tokeny in/out/cache</th><th>Koszt</th></tr></thead>
            <tbody>
                <?php foreach (array_slice($log, 0, 50) as $row): ?>
                <tr>
                    <td><?php echo esc_html((string) ($row["ts"] ?? "")); ?></td>
                    <td><?php echo esc_html((string) ($row["task"] ?? "")); ?></td>
                    <td><?php echo esc_html((string) ($row["model"] ?? "")); ?></td>
                    <td><?php echo (int) ($row["in"] ?? 0); ?> / <?php echo (int) ($row["out"] ?? 0); ?> / <?php echo (int) ($row["cache"] ?? 0); ?></td>
                    <td><?php echo number_format((float) ($row["pln"] ?? 0), 4); ?> zł</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}
