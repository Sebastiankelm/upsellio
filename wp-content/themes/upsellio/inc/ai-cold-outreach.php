<?php
if (!defined("ABSPATH")) {
    exit;
}

function upsellio_ai_personalize_outreach(array $prospects): array
{
    $wins = get_option("ups_ai_wins_snapshot", []);
    $icp = mb_substr((string) get_option("ups_ai_icp_report", ""), 0, 6000);
    $results = [];

    if (count($prospects) > 25) {
        return [["error" => "Maksymalnie 25 prospectów na batch. Podziel CSV na mniejsze paczki."]];
    }
    $estimated_pln = (float) count($prospects) * 0.20;
    if (function_exists("upsellio_ai_can_call") && !upsellio_ai_can_call("cold_outreach_batch", $estimated_pln)) {
        return [["error" => "Batch przekracza miesięczny budżet AI. Zmniejsz liczbę wierszy lub zwiększ budżet."]];
    }

    foreach ($prospects as $prospect) {
        if (function_exists("upsellio_ai_can_call_strict_global") && !upsellio_ai_can_call_strict_global("cold_outreach", 0.05)) {
            $results[] = ["prospect" => $prospect, "error" => "daily_anonymous_cap_reached"];
            continue;
        }
        if (function_exists("upsellio_ai_can_call") && !upsellio_ai_can_call("cold_outreach", 0.05)) {
            $results[] = ["prospect" => $prospect, "error" => "budget_exceeded"];
            continue;
        }

        $system = <<<'EOT'
Jesteś senior B2B sales developer. Generujesz spersonalizowaną sekwencję 3 maili cold outreach dla konkretnego prospektu.
Format JSON:
{"hook":"...","case_study_match":"...","messages":[{"day":0,"subject":"...","body":"..."},{"day":4,"subject":"...","body":"..."},{"day":9,"subject":"...","body":"..."}]}
Zasady: każdy mail inny angle, bez korpomowy, trzeci mail break-up.
EOT;
        $user = sprintf(
            "PROSPECT:\nFirma: %s\nBranża: %s\nWielkość: %s\nKontakt: %s, %s\nOpis: %s\n\nICP:\n%s\n\nWINS:\n%s",
            $prospect["company"] ?? "",
            $prospect["industry"] ?? "",
            $prospect["size"] ?? "",
            $prospect["name"] ?? "",
            $prospect["role"] ?? "",
            $prospect["notes"] ?? "",
            $icp,
            wp_json_encode(array_slice($wins["offers"] ?? [], 0, 3), JSON_UNESCAPED_UNICODE)
        );
        $GLOBALS["upsellio_ai_current_task"] = "cold_outreach";
        $cache_split = [
            "cached" => $system . "\n\nSTALE KONTEKSTY:\nICP:\n" . $icp . "\n\nWINS:\n" . wp_json_encode(array_slice($wins["offers"] ?? [], 0, 5), JSON_UNESCAPED_UNICODE),
            "dynamic" => $user,
        ];
        $resp = upsellio_anthropic_crm_send_user_prompt(
            "",
            1500,
            30,
            function_exists("upsellio_ai_model_for") ? upsellio_ai_model_for("cold_outreach") : null,
            $cache_split
        );
        if ($resp) {
            $json = json_decode(function_exists("upsellio_extract_json") ? upsellio_extract_json($resp) : $resp, true);
            $results[] = ["prospect" => $prospect, "sequence" => $json];
        }
    }
    update_option("ups_ai_cold_outreach_batch_" . time(), $results, false);
    return $results;
}

add_action("admin_menu", function () {
    add_submenu_page("upsellio-site-analytics", "Cold Outreach AI", "Cold Outreach", "edit_posts", "ups-cold-outreach", function () {
        echo '<div class="wrap"><h1>Cold Outreach Personalization</h1>';
        if (isset($_POST["ups_outreach_csv"]) && check_admin_referer("ups_outreach")) {
            $rows = array_map("str_getcsv", explode("\n", trim((string) $_POST["ups_outreach_csv"])));
            $header = array_shift($rows);
            $prospects = array_map(static function ($r) use ($header) {
                return is_array($header) && is_array($r) ? array_combine($header, $r) : [];
            }, $rows);
            $results = upsellio_ai_personalize_outreach($prospects);
            echo "<h2>Wygenerowano " . count($results) . " sekwencji</h2>";
            echo '<pre style="background:#fff;padding:20px;max-height:600px;overflow:auto;">';
            echo esc_html(wp_json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            echo "</pre>";
        } else {
            echo '<form method="post">';
            wp_nonce_field("ups_outreach");
            echo "<p>CSV (header: company,industry,size,name,role,notes):</p>";
            echo '<textarea name="ups_outreach_csv" style="width:100%;height:300px;font-family:monospace;"></textarea>';
            echo '<button id="ups-outreach-submit" class="button button-primary">Wygeneruj sekwencje</button></form>';
            ?>
            <script>
              (function () {
                const form = document.querySelector('form[method="post"]');
                const csvField = document.querySelector('textarea[name="ups_outreach_csv"]');
                if (!form || !csvField) return;
                form.addEventListener("submit", function (e) {
                  const csvRows = csvField.value
                    .split("\n")
                    .map((row) => row.trim())
                    .filter(Boolean);
                  const rowCount = Math.max(0, csvRows.length - 1);
                  const estimatedCost = (rowCount * 0.20).toFixed(2);
                  if (rowCount > 25) {
                    e.preventDefault();
                    alert("Maksymalnie 25 prospectów na jeden batch.");
                    return false;
                  }
                  if (!window.confirm("Wygeneruję " + rowCount + " sekwencji. Estymowany koszt: " + estimatedCost + " zł. Kontynuować?")) {
                    e.preventDefault();
                    return false;
                  }
                  return true;
                });
              })();
            </script>
            <?php
        }
        echo "</div>";
    });
});
