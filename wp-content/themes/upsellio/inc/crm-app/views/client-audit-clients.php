<?php



if (!defined("ABSPATH")) {

    exit;

}



$ca_profiles = function_exists("ups_audit_get_client_profile_rows") ? ups_audit_get_client_profile_rows() : [];

$ca_portfolio = function_exists("ups_audit_get_portfolio_rows") ? ups_audit_get_portfolio_rows() : [];

$crm_base = function_exists("upsellio_crm_url") ? home_url("/crm-app/") : home_url("/crm-app/");

?>

<section class="card">

  <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">

    <div>

      <h2 style="margin:0;"><?php esc_html_e("Profile klientów", "upsellio"); ?></h2>

      <p class="muted" style="margin:8px 0 0;max-width:640px;"><?php esc_html_e("Utwórz profil, przypisz zasoby GA4 / GSC / Ads oraz Microsoft Clarity, potem otwórz dashboard ze statystykami zmapowanych danych.", "upsellio"); ?></p>

    </div>

    <div style="display:flex;gap:8px;flex-wrap:wrap;">

      <a class="btn alt" href="<?php echo esc_url(function_exists("upsellio_crm_url") ? upsellio_crm_url("ca-command-center") : home_url("/crm-app/?view=ca-command-center")); ?>"><i class="ti ti-layout-grid" aria-hidden="true"></i> <?php esc_html_e("Command Center", "upsellio"); ?></a>
      <a class="btn alt" href="<?php echo esc_url(function_exists("upsellio_crm_url") ? upsellio_crm_url("ca-accounts") : home_url("/crm-app/?view=ca-accounts")); ?>"><i class="ti ti-brand-google" aria-hidden="true"></i> <?php esc_html_e("Połączenia Google", "upsellio"); ?></a>
      <a class="btn alt" href="<?php echo esc_url(function_exists("upsellio_crm_url") ? upsellio_crm_url("ca-meta-accounts") : home_url("/crm-app/?view=ca-meta-accounts")); ?>"><i class="ti ti-brand-facebook" aria-hidden="true"></i> <?php esc_html_e("Połączenia Meta", "upsellio"); ?></a>

      <?php if (!empty($ca_profiles)) : ?>

        <a class="btn" href="<?php echo esc_url(function_exists("upsellio_crm_url") ? upsellio_crm_url("ca-dashboard", ["cid" => (int) ($ca_profiles[0]["id"] ?? 0)]) : add_query_arg(["view" => "ca-dashboard", "cid" => (int) ($ca_profiles[0]["id"] ?? 0)], $crm_base)); ?>"><i class="ti ti-chart-arcs" aria-hidden="true"></i> <?php esc_html_e("Dashboard", "upsellio"); ?></a>

      <?php endif; ?>

      <button type="button" class="btn alt" id="ups-audit-sync-all-btn"><i class="ti ti-refresh" aria-hidden="true"></i> <?php esc_html_e("Sync wszystkich zasobów", "upsellio"); ?></button>

    </div>

  </div>

</section>



<section class="card">

  <h3 style="margin:0 0 10px;font-size:14px;"><?php esc_html_e("Nowy profil klienta", "upsellio"); ?></h3>

  <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">

    <label style="flex:1;min-width:180px;">

      <span class="muted" style="font-size:11px;display:block;margin-bottom:4px;"><?php esc_html_e("Nazwa profilu", "upsellio"); ?></span>

      <input type="text" class="input" id="ups-audit-new-profile-name" placeholder="<?php esc_attr_e("np. Kelyo.pl", "upsellio"); ?>" style="width:100%;" />

    </label>

    <label style="flex:1;min-width:220px;">

      <span class="muted" style="font-size:11px;display:block;margin-bottom:4px;"><?php esc_html_e("Strona (opcjonalnie, CWV/indeksacja)", "upsellio"); ?></span>

      <input type="url" class="input" id="ups-audit-new-profile-website" placeholder="https://example.pl" style="width:100%;" />

    </label>

    <button type="button" class="btn" id="ups-audit-create-profile-btn"><i class="ti ti-plus" aria-hidden="true"></i> <?php esc_html_e("Utwórz profil", "upsellio"); ?></button>

  </div>

</section>

<?php require __DIR__ . "/../partials/crm-audit-clarity.php"; ?>

<section class="card" style="overflow-x:auto;">

  <?php if (empty($ca_profiles)) : ?>

    <p class="muted" style="margin:0;"><?php esc_html_e("Brak profili. Utwórz profil powyżej, zaimportuj zasoby w Połączeniach Google i użyj „Mapuj zasoby”.", "upsellio"); ?></p>

  <?php else : ?>

  <table class="crm-audit-portfolio-table" style="width:100%;min-width:900px;">

    <thead>

      <tr>

        <th><?php esc_html_e("Profil", "upsellio"); ?></th>

        <th><?php esc_html_e("Zasoby", "upsellio"); ?></th>

        <th>GA4</th>

        <th>GSC</th>

        <th>Ads</th>

        <th>Meta</th>

        <th>Clarity</th>

        <th>Sesje</th>

        <th>GSC klik.</th>

        <th>Ads PLN</th>

        <th>ROAS</th>

        <th>Health</th>

        <th>Sync</th>

        <th><?php esc_html_e("Akcje", "upsellio"); ?></th>

      </tr>

    </thead>

    <tbody>

      <?php foreach ($ca_profiles as $prow) : ?>

        <?php if (!is_array($prow)) { continue; } ?>

        <?php

        $pid = (int) ($prow["id"] ?? 0);

        if ($pid <= 0) {

            continue;

        }

        $setup = (array) ($prow["setup"] ?? []);

        $deltas = (array) ($prow["deltas"] ?? []);

        $ls = (int) ($prow["last_sync"] ?? 0);

        $website = (string) ($prow["website"] ?? "");

        ?>

        <tr data-client-profile-row="<?php echo (int) $pid; ?>">

          <td>

            <strong><?php echo esc_html((string) ($prow["title"] ?? "")); ?></strong>

            <?php if ($website !== "") : ?>

              <span class="muted" style="display:block;font-size:11px;"><?php echo esc_html($website); ?></span>

            <?php endif; ?>

            <?php if ((int) ($prow["resource_count"] ?? 0) <= 0) : ?>

              <span class="muted" style="display:block;font-size:11px;"><?php esc_html_e("Brak zmapowanych zasobów", "upsellio"); ?></span>

            <?php elseif (empty($setup["is_ready"])) : ?>

              <span class="muted" style="display:block;font-size:11px;"><?php esc_html_e("Wymagane: GA4 + GSC", "upsellio"); ?></span>

            <?php endif; ?>

          </td>

          <td><?php echo (int) ($prow["resource_count"] ?? 0); ?></td>

          <td><?php echo (int) ($setup["ga4"] ?? 0); ?></td>

          <td><?php echo (int) ($setup["gsc"] ?? 0); ?></td>

          <td><?php echo (int) ($setup["ads"] ?? 0); ?></td>

          <td><?php echo (int) ($setup["meta"] ?? 0); ?></td>

          <td>
            <?php echo (int) ($setup["clarity"] ?? 0); ?>
            <?php if ((int) ($prow["clarity_sessions"] ?? 0) > 0) : ?>
              <span class="muted" style="display:block;font-size:11px;"><?php echo (int) ($prow["clarity_sessions"] ?? 0); ?> <?php esc_html_e("sesji (3d)", "upsellio"); ?></span>
            <?php endif; ?>
          </td>

          <td>

            <?php echo (int) ($prow["ga4_sessions"] ?? 0); ?>

            <?php if (isset($deltas["ga4_sessions"])) : ?>

              <span class="muted" style="font-size:11px;"><?php echo esc_html(function_exists("ups_audit_format_delta") ? ups_audit_format_delta((float) $deltas["ga4_sessions"]) : ""); ?></span>

            <?php endif; ?>

          </td>

          <td>

            <?php echo (int) ($prow["gsc_clicks"] ?? 0); ?>

            <?php if (isset($deltas["gsc_clicks"])) : ?>

              <span class="muted" style="font-size:11px;"><?php echo esc_html(function_exists("ups_audit_format_delta") ? ups_audit_format_delta((float) $deltas["gsc_clicks"]) : ""); ?></span>

            <?php endif; ?>

          </td>

          <td><?php echo esc_html(number_format((float) ($prow["ads_cost"] ?? 0), 0, ",", " ")); ?></td>

          <td><?php echo esc_html(number_format((float) ($prow["roas"] ?? 0), 2, ",", " ")); ?>x</td>

          <?php

          $hs = (int) ($prow["health_score"] ?? 0);

          $hs_c = $hs >= 75 ? "var(--ok,#16a34a)" : ($hs >= 50 ? "#d97706" : "var(--danger,#dc2626)");

          ?>

          <td style="font-weight:700;color:<?php echo esc_attr($hs_c); ?>"><?php echo $hs > 0 ? (int) $hs : "—"; ?></td>

          <td><?php echo esc_html(function_exists("ups_audit_format_sync_time") ? ups_audit_format_sync_time($ls) : "brak"); ?></td>

          <td>

            <div class="crm-audit-row-actions">

              <a class="btn" href="<?php echo esc_url(add_query_arg(["view" => "ca-dashboard", "cid" => $pid], $crm_base)); ?>"><?php esc_html_e("Dashboard", "upsellio"); ?></a>

              <button type="button" class="btn alt" data-ups-audit-open-map data-client-id="<?php echo (int) $pid; ?>" data-client-name="<?php echo esc_attr((string) ($prow["title"] ?? "")); ?>"><?php esc_html_e("Mapuj zasoby", "upsellio"); ?></button>

              <button type="button" class="btn alt" data-ups-audit-client-sync="<?php echo (int) $pid; ?>"><?php esc_html_e("Sync", "upsellio"); ?></button>

            </div>

          </td>

        </tr>

      <?php endforeach; ?>

    </tbody>

  </table>

  <?php endif; ?>

</section>



<section class="card">

  <details>

    <summary style="cursor:pointer;font-weight:700;font-size:14px;"><?php esc_html_e("Konta Google (import i szybkie mapowanie)", "upsellio"); ?></summary>

    <p class="muted" style="margin:10px 0 0;"><?php esc_html_e("Każde konto Google w osobnym wierszu. Zasoby z różnych kont możesz przypisać do jednego profilu klienta.", "upsellio"); ?></p>

    <div style="overflow-x:auto;margin-top:12px;">

      <?php if (empty($ca_portfolio)) : ?>

        <p class="muted" style="margin:0;"><?php esc_html_e("Brak połączonych kont Google.", "upsellio"); ?></p>

      <?php else : ?>

      <table class="crm-audit-portfolio-table" style="width:100%;min-width:960px;">

        <thead>

          <tr>

            <th><?php esc_html_e("Konto Google", "upsellio"); ?></th>

            <th>GA4</th>

            <th>GSC</th>

            <th>Ads</th>

            <th><?php esc_html_e("Profil", "upsellio"); ?></th>

            <th>Sesje</th>

            <th>GSC klik.</th>

            <th><?php esc_html_e("Akcje", "upsellio"); ?></th>

          </tr>

        </thead>

        <tbody>

          <?php foreach ($ca_portfolio as $prow) : ?>

            <?php if (!is_array($prow)) { continue; } ?>

            <?php

            $aid = (int) ($prow["google_account_id"] ?? $prow["id"] ?? 0);

            if ($aid <= 0) {

                continue;

            }

            $setup = (array) ($prow["setup"] ?? []);

            $mapped_id = (int) ($prow["mapped_client_id"] ?? 0);

            $mapped_title = (string) ($prow["mapped_client_title"] ?? "");

            $label = (string) ($prow["label"] ?? "");

            ?>

            <tr>

              <td>

                <strong><?php echo esc_html((string) ($prow["title"] ?? "")); ?></strong>

                <?php if ($label !== "") : ?>

                  <span class="muted" style="display:block;font-size:11px;"><?php echo esc_html($label); ?></span>

                <?php endif; ?>

              </td>

              <td><?php echo (int) ($setup["ga4"] ?? 0); ?></td>

              <td><?php echo (int) ($setup["gsc"] ?? 0); ?></td>

              <td><?php echo (int) ($setup["ads"] ?? 0); ?></td>

              <td style="font-size:12px;">

                <?php if ($mapped_id > 0 && $mapped_title !== "") : ?>

                  <a href="<?php echo esc_url(add_query_arg(["view" => "ca-dashboard", "cid" => $mapped_id], $crm_base)); ?>"><?php echo esc_html($mapped_title); ?></a>

                <?php else : ?>

                  <span class="muted">—</span>

                <?php endif; ?>

              </td>

              <td><?php echo (int) ($prow["ga4_sessions"] ?? 0); ?></td>

              <td><?php echo (int) ($prow["gsc_clicks"] ?? 0); ?></td>

              <td>

                <div class="crm-audit-row-actions">

                  <a class="btn alt" href="<?php echo esc_url(function_exists("upsellio_crm_url") ? upsellio_crm_url("ca-accounts") : home_url("/crm-app/?view=ca-accounts")); ?>"><?php esc_html_e("Zasoby", "upsellio"); ?></a>

                  <button type="button" class="btn alt" data-ups-audit-open-map-account data-account-id="<?php echo (int) $aid; ?>" data-account-name="<?php echo esc_attr((string) ($prow["title"] ?? "")); ?>"><?php esc_html_e("Mapuj", "upsellio"); ?></button>

                  <button type="button" class="btn alt" data-ups-audit-account-sync="<?php echo (int) $aid; ?>"><?php esc_html_e("Sync", "upsellio"); ?></button>

                </div>

              </td>

            </tr>

          <?php endforeach; ?>

        </tbody>

      </table>

      <?php endif; ?>

    </div>

  </details>

</section>


