<?php

if (!defined("ABSPATH")) {
    exit;
}

$ca_accounts = get_posts([
    "post_type" => "crm_google_account",
    "posts_per_page" => -1,
    "post_status" => ["publish", "draft"],
    "orderby" => "date",
    "order" => "DESC",
]);
$oauth_info = function_exists("ups_audit_oauth_connection_info") ? ups_audit_oauth_connection_info() : ["ready" => false, "message" => ""];
$connected = isset($_GET["connected"]) && (string) wp_unslash($_GET["connected"]) === "1";
$audit_err = isset($_GET["ups_audit_error"]) ? rawurldecode((string) wp_unslash($_GET["ups_audit_error"])) : "";
$audit_err_is_mismatch = $audit_err !== ""
    && (stripos($audit_err, "redirect_uri") !== false || stripos($audit_err, "mismatch") !== false);
$audit_err_is_access_denied = $audit_err !== ""
    && (stripos($audit_err, "access_denied") !== false || stripos($audit_err, "403") !== false || stripos($audit_err, "testowych") !== false);
$account_id_new = isset($_GET["account_id"]) ? (int) wp_unslash($_GET["account_id"]) : 0;
if ($account_id_new <= 0 && $connected) {
    $uid = get_current_user_id();
    $account_id_new = (int) get_transient("ups_audit_last_connected_" . $uid);
    if ($account_id_new > 0) {
        delete_transient("ups_audit_last_connected_" . $uid);
    }
}
$ads_configured = function_exists("ups_audit_ads_api_configured") && ups_audit_ads_api_configured();
$ca_accounts_count = count($ca_accounts);
$ca_profile_rows = function_exists("ups_audit_get_client_profile_rows") ? ups_audit_get_client_profile_rows() : [];
$ca_profile_count = count($ca_profile_rows);
$ca_imported_count = (int) wp_count_posts("crm_audit_resource")->publish + (int) wp_count_posts("crm_audit_resource")->draft;
$ca_mapped_clients = function_exists("ups_audit_count_active_clients") ? (int) ups_audit_count_active_clients() : 0;
$ca_profiles_url = function_exists("upsellio_crm_url") ? upsellio_crm_url("ca-clients") : home_url("/crm-app/?view=ca-clients");
$ca_dashboard_url = function_exists("upsellio_crm_url") ? upsellio_crm_url("ca-dashboard") : home_url("/crm-app/?view=ca-dashboard");
?>
<div class="crm-audit-page">
<?php if ($ca_accounts_count > 0 && $ca_mapped_clients <= 0) : ?>
  <section class="card" style="border-color:#f59e0b;background:#fffbeb;">
    <h3 style="margin:0 0 8px;font-size:15px;"><?php esc_html_e("Kolejny krok: profil klienta + mapowanie", "upsellio"); ?></h3>
    <p style="margin:0 0 12px;font-size:13px;line-height:1.55;">
      <?php
      printf(
          /* translators: 1: imported resources count */
          esc_html__("Masz %1$d zaimportowanych zasobów w bazie, ale żaden nie jest przypisany do profilu — dlatego dashboard jest pusty. Utwórz profil (np. Kelyo) i zmapuj GA4/GSC.", "upsellio"),
          $ca_imported_count
      );
      ?>
    </p>
    <div style="display:flex;flex-wrap:wrap;gap:8px;">
      <a class="btn" href="<?php echo esc_url($ca_profiles_url); ?>"><i class="ti ti-users" aria-hidden="true"></i> <?php esc_html_e("Profile klientów — utwórz i mapuj", "upsellio"); ?></a>
      <?php if ($ca_profile_count > 0) : ?>
        <a class="btn alt" href="<?php echo esc_url(add_query_arg(["view" => "ca-dashboard", "cid" => (int) ($ca_profile_rows[0]["id"] ?? 0)], home_url("/crm-app/"))); ?>"><?php esc_html_e("Otwórz dashboard", "upsellio"); ?></a>
      <?php endif; ?>
    </div>
  </section>
<?php endif; ?>
<?php if ($connected) : ?>
  <section class="card" style="border-color:var(--ok,#16a34a);">
    <p style="margin:0;color:var(--ok,#16a34a);">
      Konto Google połączone<?php echo $account_id_new > 0 ? " (ID " . (int) $account_id_new . ")" : ""; ?>.
      Lista zasobów została pobrana — kliknij <strong>Import</strong> przy właściwościach, potem mapuj do klienta.
    </p>
  </section>
<?php endif; ?>
<?php if ($audit_err !== "") : ?>
  <section class="card" style="border-color:var(--danger,#dc2626);">
    <p style="margin:0;color:var(--danger,#dc2626);"><?php echo esc_html($audit_err); ?></p>
    <?php if ($audit_err_is_access_denied && current_user_can("manage_options")) : ?>
      <p style="margin:10px 0 0;font-size:13px;color:var(--text-2);">
        <strong>Rozwiązanie:</strong> <a href="https://console.cloud.google.com/auth/audience" target="_blank" rel="noopener noreferrer">Google Cloud → OAuth consent screen → Test users</a>
        → <strong>Add users</strong> → dodaj <code>sebastian.kelm97@gmail.com</code> (i każdy inny e-mail, którym logujesz się w Google). Zapisz, odczekaj 1–2 min.
      </p>
    <?php endif; ?>
    <?php if ($audit_err_is_mismatch && current_user_can("manage_options")) : ?>
      <p style="margin:10px 0 0;font-size:13px;color:var(--text-2);">Google odrzucił logowanie — w Cloud Console brakuje dokładnie tego samego adresu callback, który wysyła Upsellio (patrz sekcja poniżej).</p>
    <?php endif; ?>
  </section>
<?php endif; ?>

<section class="card" style="background:linear-gradient(135deg,rgba(10,191,163,.08),transparent);">
  <h2 style="margin:0 0 8px;">Połącz konta klientów (logowanie Google)</h2>
  <p class="muted" style="margin:0 0 8px;font-size:13px;">
    Możesz mieć <strong>wiele kont Google jednocześnie</strong> (np. agencja + klient). Każde logowanie to osobna karta poniżej — importuj zasoby osobno, potem mapuj do klientów CRM.
    <?php
    $ca_meta_url = function_exists("upsellio_crm_url") ? upsellio_crm_url("ca-meta-accounts") : home_url("/crm-app/?view=ca-meta-accounts");
    ?>
    <a href="<?php echo esc_url($ca_meta_url); ?>" style="color:var(--teal);font-weight:600;"><?php esc_html_e("Meta Ads → osobne połączenia", "upsellio"); ?></a>
  </p>
  <?php if ($ca_accounts_count > 0) : ?>
    <p style="margin:0 0 12px;font-size:13px;line-height:1.55;">
      <strong><?php echo (int) $ca_accounts_count; ?></strong> <?php echo $ca_accounts_count === 1 ? "konto podłączone" : "konta podłączone"; ?>.
      GA4 i GSC masz już na tych Gmailach — <strong>nie musisz dodawać trzeciego konta</strong>, żeby włączyć Ads.
      Na karcie konta użyj <strong>„Dodaj Google Ads”</strong> (ten sam Gmail).
      Przycisk „Zaloguj i dodaj konto Google” służy tylko do <em>innego</em> adresu @gmail (np. klient).
    </p>
  <?php endif; ?>
  <ol style="margin:0 0 14px;padding-left:20px;font-size:13px;line-height:1.6;">
    <li><strong>Połącz konto</strong> — przy 2. koncie Google wybierz inny profil w oknie logowania.</li>
    <li><strong>Import</strong> — w każdej karcie: GA4, GSC, Ads z tego konta.</li>
    <li><strong>Mapuj</strong> — <a href="<?php echo esc_url($ca_profiles_url); ?>">Profile klientów</a>: przypisz zasoby do profilu (dashboard pokazuje tylko zmapowane dane).</li>
    <li><strong>Sync</strong> — KPI per konto lub wszystkie naraz.</li>
  </ol>

  <?php if (empty($oauth_info["ready"])) : ?>
    <div style="padding:12px;border:1px solid var(--danger);border-radius:10px;background:#fef2f2;font-size:13px;color:var(--danger);">
      <?php echo esc_html((string) ($oauth_info["message"] ?? "OAuth niedostępny.")); ?>
      <br><a href="<?php echo esc_url(admin_url("admin.php?page=" . (function_exists("upsellio_site_analytics_page_slug") ? upsellio_site_analytics_page_slug() : "upsellio-site-analytics"))); ?>" style="color:var(--teal);">Otwórz Analitykę SEO w WP</a>
    </div>
  <?php else : ?>
    <?php
    $ca_oauth_start_url = function_exists("ups_audit_oauth_connect_url")
        ? ups_audit_oauth_connect_url($ads_configured, "wtapes-google-ads")
        : admin_url("admin-post.php?action=ups_audit_google_oauth&op=start&include_ads=1");
    $ca_google_connect_url = is_user_logged_in()
        ? $ca_oauth_start_url
        : (function_exists("ups_audit_oauth_login_entry_url")
            ? ups_audit_oauth_login_entry_url($ads_configured, "wtapes-google-ads")
            : wp_login_url($ca_oauth_start_url));
    $ca_oauth_after_login = $ca_oauth_start_url;
    ?>
    <p style="margin:0 0 10px;font-size:12px;color:var(--text-2);">
      <?php esc_html_e("Jeśli nie jesteś zalogowany do WordPressa, użyj przycisku poniżej (logowanie → Google). Gdy jesteś już zalogowany w tej samej przeglądarce:", "upsellio"); ?>
      <a href="<?php echo esc_url($ca_oauth_after_login); ?>" style="color:var(--teal);font-weight:700;"><?php esc_html_e("bezpośrednio do Google", "upsellio"); ?></a>.
    </p>
    <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
      <a class="btn" href="<?php echo esc_url($ca_google_connect_url); ?>"><i class="ti ti-brand-google" aria-hidden="true"></i> <?php echo $ca_accounts_count > 0 ? esc_html__("Zaloguj i dodaj konto Google", "upsellio") : esc_html__("Zaloguj przez Google", "upsellio"); ?></a>
      <button type="button" class="btn alt" id="ups-audit-connect-open"><i class="ti ti-adjustments" aria-hidden="true"></i> <?php esc_html_e("Opcje (etykieta, Ads)", "upsellio"); ?></button>
      <?php if ($ca_accounts_count > 0) : ?>
        <button type="button" class="btn alt" onclick="typeof upsAuditRefreshAllAccounts==='function'&&upsAuditRefreshAllAccounts()"><i class="ti ti-refresh" aria-hidden="true"></i> <?php esc_html_e("Odśwież wszystkie konta", "upsellio"); ?></button>
      <?php endif; ?>
    </div>
    <?php
    $console_uri = (string) ($oauth_info["google_console_uri"] ?? $oauth_info["redirect_uri"] ?? "");
    $console_uri_slash = (string) ($oauth_info["google_console_uri_slash"] ?? "");
    $g_client_id = (string) ($oauth_info["google_client_id"] ?? "");
    $show_console_help = $console_uri !== "" && current_user_can("manage_options");
    $analytics_admin_url = admin_url("admin.php?page=" . (function_exists("upsellio_site_analytics_page_slug") ? upsellio_site_analytics_page_slug() : "upsellio-site-analytics"));
    ?>
    <?php if ($show_console_help) : ?>
      <section style="margin-top:14px;padding:12px 14px;background:#fff7ed;border:1px solid #fdba74;border-radius:10px;font-size:12px;line-height:1.55;">
        <strong style="display:block;margin-bottom:6px;color:#9a3412;"><?php echo $audit_err_is_mismatch ? "Naprawa błędu redirect_uri_mismatch" : "Google Cloud — jednorazowa konfiguracja (administrator)"; ?></strong>
        <ol style="margin:0 0 10px;padding-left:18px;">
          <li>Otwórz <a href="https://console.cloud.google.com/apis/credentials" target="_blank" rel="noopener noreferrer">Credentials</a> w projekcie z OAuth clientem <strong>Web application</strong> (u Ciebie: „Potato App”).</li>
          <li>Sprawdź, czy edytujesz Client ID kończący się na <code>…779d0k</code><?php if ($g_client_id !== "") : ?> — w WP jest <code style="font-size:10px;word-break:break-all;"><?php echo esc_html($g_client_id); ?></code><?php endif; ?>.</li>
          <li>W polu <strong>Authorized redirect URIs</strong> dodaj <em>dokładnie</em> adres CRM poniżej (logowanie z tej zakładki nie używa już domyślnie <code>wp-json</code>).</li>
        </ol>
        <code id="ups-audit-redirect-uri" style="display:block;word-break:break-all;padding:8px;background:var(--bg);border-radius:8px;"><?php echo esc_html($console_uri); ?></code>
        <?php
        $console_uri_handler = (string) ($oauth_info["google_console_uri_handler"] ?? "");
        if ($console_uri_handler !== "" && $console_uri_handler !== $console_uri) :
            ?>
          <p style="margin:8px 0 4px;">Wewnętrzny handler (nie dodawaj do Google):</p>
          <code style="display:block;word-break:break-all;padding:8px;background:var(--bg);border-radius:8px;font-size:11px;"><?php echo esc_html($console_uri_handler); ?></code>
        <?php endif; ?>
        <?php
        $console_uri_rest = (string) ($oauth_info["google_console_uri_rest"] ?? "");
        if ($console_uri_rest !== "" && $console_uri_rest !== $console_uri) :
            ?>
          <p style="margin:8px 0 4px;">Opcjonalnie (most REST / Analityka SEO):</p>
          <code style="display:block;word-break:break-all;padding:8px;background:var(--bg);border-radius:8px;font-size:11px;"><?php echo esc_html($console_uri_rest); ?></code>
        <?php endif; ?>
        <?php if ($console_uri_slash !== "" && $console_uri_slash !== $console_uri && $console_uri_slash !== $console_uri_rest) : ?>
          <p style="margin:8px 0 4px;">Opcjonalnie (wariant ukośnikowy REST):</p>
          <code style="display:block;word-break:break-all;padding:8px;background:var(--bg);border-radius:8px;font-size:11px;"><?php echo esc_html($console_uri_slash); ?></code>
        <?php endif; ?>
        <div style="margin-top:10px;display:flex;flex-wrap:wrap;gap:8px;">
          <button type="button" class="btn alt" style="padding:4px 10px;font-size:11px;" id="ups-audit-copy-redirect">Kopiuj główny URI</button>
          <a class="btn alt" style="padding:4px 10px;font-size:11px;" href="<?php echo esc_url($analytics_admin_url); ?>">Analityka SEO (nadpisanie URI)</a>
        </div>
        <p style="margin:10px 0 0;font-size:11px;color:var(--text-2);">Masz już inny URI w Google? Wklej go w Analityce SEO w polu „Nadpisanie redirect URI” — musi być identyczny z konsolą. Po zapisie w Google odczekaj 1–5 min i spróbuj ponownie.</p>
      </section>
    <?php endif; ?>
    <?php if (!$ads_configured) : ?>
      <section class="card" style="margin-top:12px;border-color:#93c5fd;background:#eff6ff;">
        <h3 style="margin:0 0 8px;font-size:14px;">Jak włączyć Google Ads w audycie</h3>
        <ol style="margin:0;padding-left:18px;font-size:12px;line-height:1.6;">
          <li>Masz tylko <strong>konta reklamowe</strong>? Google nie pokaże Centrum API tam. Załóż darmowe <a href="https://ads.google.com/home/tools/manager-accounts/" target="_blank" rel="noopener noreferrer"><strong>konto menedżera (MCC)</strong></a> i podłącz pod nim istniejące konta (Zaproszenia / Połącz konto).</li>
          <li>W panelu WP: <a href="<?php echo esc_url(function_exists("upsellio_site_analytics_admin_url") ? upsellio_site_analytics_admin_url() : admin_url("admin.php?page=upsellio-site-analytics")); ?>" target="_blank" rel="noopener noreferrer"><strong>Analityka SEO</strong></a> → <strong>Google Ads API</strong>.</li>
          <li>Na <strong>MCC</strong>: <strong>Narzędzia → Konfiguracja → Centrum API</strong> → <strong>Developer token</strong>. W WP: token + <strong>Login Customer ID</strong> = ID MCC + <strong>Customer ID</strong> = ID konta reklamowego klienta.</li>
          <li><strong>Zapisz</strong> → <strong>Test: listAccessibleCustomers</strong>. Włącz zakres <code>adwords</code>, potem tutaj <strong>Połącz konto Google</strong> z <em>Uwzględnij Google Ads</em>.</li>
          <li>W <a href="<?php echo esc_url($ca_profiles_url); ?>">Profile klientów</a> zaimportuj zasoby Ads i zmapuj do profilu.</li>
        </ol>
        <p class="muted" style="margin:8px 0 0;font-size:11px;">OAuth bez Developer Tokena z MCC nie wystarczy — KPI Ads zostaną 0 PLN.</p>
      </section>
    <?php endif; ?>
  <?php endif; ?>
</section>

<div id="ups-audit-connect-modal" class="ups-audit-connect-modal" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="ups-audit-connect-modal-title">
  <div class="card" style="width:min(440px,96vw);">
    <h3 id="ups-audit-connect-modal-title" style="margin:0 0 8px;"><?php echo $ca_accounts_count > 0 ? esc_html__("Dodaj kolejne konto Google", "upsellio") : esc_html__("Połącz konto Google", "upsellio"); ?></h3>
    <p class="muted" style="margin:0 0 12px;font-size:12px;"><?php esc_html_e("Tylko gdy dodajesz trzeci, inny Gmail. Aby dołożyć Ads do istniejącej karty — użyj „Dodaj Google Ads” na tej karcie.", "upsellio"); ?></p>
    <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px;"><?php esc_html_e("Etykieta (zalecane przy 2+ kontach)", "upsellio"); ?></label>
    <input type="text" id="ups-audit-connect-label" class="input" placeholder="np. Agencja / Klient XYZ — GA4+GSC" style="width:100%;margin-bottom:10px;" />
    <label style="display:flex;gap:8px;align-items:center;font-size:12px;margin-bottom:14px;">
      <input type="checkbox" id="ups-audit-connect-ads" <?php checked($ads_configured); ?> <?php disabled(!$ads_configured); ?> />
      Uwzględnij Google Ads (wymaga Developer Token)
    </label>
    <div style="display:flex;gap:8px;justify-content:flex-end;">
      <button type="button" class="btn alt" id="ups-audit-connect-cancel">Anuluj</button>
      <button type="button" class="btn" id="ups-audit-connect-go"><i class="ti ti-brand-google"></i> Zaloguj przez Google</button>
    </div>
  </div>
</div>
<script>
(function () {
  if (window.upsAuditOpenConnectModal) {
    return;
  }
  window.upsAuditOpenConnectModal = function () {
    var modal = document.getElementById("ups-audit-connect-modal");
    if (!modal) {
      return;
    }
    if (modal.parentNode !== document.body) {
      document.body.appendChild(modal);
    }
    modal.classList.add("is-open");
    modal.setAttribute("aria-hidden", "false");
  };
})();
</script>

<section class="card">
  <h2 style="margin:0;"><?php esc_html_e("Podłączone konta", "upsellio"); ?></h2>
</section>
<?php if (empty($ca_accounts)) : ?>
  <section class="card"><p class="muted"><?php esc_html_e("Brak kont. Użyj przycisku „Połącz konto Google” powyżej.", "upsellio"); ?></p></section>
<?php endif; ?>
<?php foreach ($ca_accounts as $ca_acc) : ?>
  <?php if (!($ca_acc instanceof WP_Post)) { continue; } ?>
  <?php $ca_acc_id = (int) $ca_acc->ID; ?>
  <?php $ca_cache = get_post_meta($ca_acc_id, "_ups_gacc_resources_cache", true); ?>
  <?php $ca_cache = is_array($ca_cache) ? $ca_cache : []; ?>
  <?php $ca_email = (string) get_post_meta($ca_acc_id, "_ups_gacc_email", true); ?>
  <?php $ca_label = (string) get_post_meta($ca_acc_id, "_ups_gacc_label", true); ?>
  <?php if ($ca_email === "" && function_exists("ups_audit_fetch_email_from_token")) : ?>
    <?php
    $ca_oauth_probe = function_exists("ups_audit_get_oauth_for_account") ? ups_audit_get_oauth_for_account($ca_acc_id) : [];
    $ca_email = ups_audit_fetch_email_from_token(
        (string) ($ca_oauth_probe["refresh_token"] ?? ""),
        (string) ($ca_oauth_probe["client_id"] ?? ""),
        (string) ($ca_oauth_probe["client_secret"] ?? "")
    );
    if ($ca_email !== "") {
        update_post_meta($ca_acc_id, "_ups_gacc_email", $ca_email);
        if ($ca_email !== get_the_title($ca_acc_id)) {
            wp_update_post(["ID" => $ca_acc_id, "post_title" => $ca_email]);
        }
    }
    ?>
  <?php endif; ?>
  <?php $ca_exp_raw = (string) get_post_meta($ca_acc_id, "_ups_gacc_token_expires_at", true); ?>
  <?php $ca_exp_ts = $ca_exp_raw !== "" ? strtotime($ca_exp_raw) : 0; ?>
  <?php $ca_exp_days = $ca_exp_ts > 0 ? (int) floor(($ca_exp_ts - time()) / DAY_IN_SECONDS) : 0; ?>
  <section class="card" style="padding:16px;">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
      <div>
        <strong><?php echo esc_html($ca_email !== "" ? $ca_email : get_the_title($ca_acc_id)); ?></strong>
        <div class="muted" style="font-size:12px"><?php echo esc_html($ca_label); ?></div>
        <?php if ($ca_exp_ts > 0) : ?>
          <div class="muted" style="font-size:12px"><?php echo $ca_exp_days <= 14 ? "Token wygasa za " . (int) max(0, $ca_exp_days) . " dni" : "Token ważny"; ?></div>
        <?php endif; ?>
      </div>
      <?php
      $ca_has_ads_scope = function_exists("ups_audit_account_has_oauth_scope")
          && ups_audit_account_has_oauth_scope($ca_acc_id, "adwords");
      $ca_ads_reconnect_url = function_exists("ups_audit_oauth_reconnect_account_url") && $ads_configured
          ? ups_audit_oauth_reconnect_account_url($ca_acc_id, true)
          : "";
      ?>
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <?php if (!$ca_has_ads_scope && $ca_ads_reconnect_url !== "") : ?>
          <a class="btn" href="<?php echo esc_url($ca_ads_reconnect_url); ?>"><i class="ti ti-brand-google" aria-hidden="true"></i><?php esc_html_e("Dodaj Google Ads (ten sam Gmail)", "upsellio"); ?></a>
        <?php endif; ?>
        <button type="button" class="btn alt" onclick="upsAuditRefreshAccountResources(<?php echo (int) $ca_acc_id; ?>)"><i class="ti ti-refresh" aria-hidden="true"></i><?php esc_html_e("Odśwież zasoby", "upsellio"); ?></button>
        <button type="button" class="btn alt" onclick="upsAuditDisconnectAccount(<?php echo (int) $ca_acc_id; ?>)"><i class="ti ti-trash" aria-hidden="true"></i><?php esc_html_e("Odłącz", "upsellio"); ?></button>
      </div>
    </div>
    <div style="margin-top:10px;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;">
      <?php foreach (["ga4" => "GA4", "gsc" => "GSC", "ads" => "Ads"] as $ca_type => $ca_type_label) : ?>
        <?php $items = (array) ($ca_cache[$ca_type] ?? []); ?>
        <div style="border:1px solid var(--border);border-radius:10px;padding:10px;">
          <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:8px;">
            <strong style="font-size:13px;"><?php echo esc_html($ca_type_label); ?></strong>
            <span class="muted"><?php echo (int) count($items); ?></span>
          </div>
          <?php if (empty($items)) : ?>
            <?php
            $empty_hint = function_exists("ups_audit_get_gacc_fetch_error_hint")
                ? ups_audit_get_gacc_fetch_error_hint($ca_acc_id, $ca_type)
                : __("Brak danych — odśwież listę zasobów.", "upsellio");
            ?>
            <p class="muted" style="margin:0;font-size:11px;line-height:1.45;"><?php echo esc_html($empty_hint); ?></p>
            <?php if ($ca_type === "ga4" && current_user_can("manage_options")) : ?>
              <p style="margin:8px 0 0;font-size:11px;"><a href="https://console.developers.google.com/apis/api/analyticsadmin.googleapis.com/overview?project=936412824129" target="_blank" rel="noopener noreferrer" style="color:var(--teal);">Włącz Analytics Admin API</a></p>
            <?php endif; ?>
          <?php else : ?>
            <div style="display:flex;flex-direction:column;gap:6px;max-height:240px;overflow:auto;">
              <?php if ($ca_type === "ga4") : ?>
                <?php foreach ($items as $acc_node) : ?>
                  <?php if (!is_array($acc_node)) { continue; } ?>
                  <div style="padding:4px 2px 2px;font-size:11px;font-weight:700;color:var(--text-2);"><?php echo esc_html((string) ($acc_node["account_name"] ?? "")); ?> <span class="muted">(<?php echo esc_html((string) ($acc_node["account_id"] ?? "")); ?>)</span></div>
                  <?php foreach ((array) ($acc_node["properties"] ?? []) as $prop) : ?>
                    <?php if (!is_array($prop)) { continue; } ?>
                    <?php $ext = (string) ($prop["id"] ?? ""); ?>
                    <?php $name = (string) ($prop["display_name"] ?? $ext); ?>
                    <?php $imported_rid_ga4 = function_exists("ups_audit_find_imported_resource_id")
                        ? ups_audit_find_imported_resource_id($ca_acc_id, "ga4", $ext)
                        : 0; ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;border:1px solid var(--border);border-radius:8px;padding:7px 8px;">
                      <div>
                        <div style="font-size:12px;font-weight:600;"><?php echo esc_html($name); ?></div>
                        <div class="muted" style="font-size:11px;"><?php echo esc_html($ext); ?></div>
                      </div>
                      <?php if ($imported_rid_ga4 > 0) : ?>
                        <span style="font-size:11px;font-weight:700;color:var(--ok,#16a34a);white-space:nowrap;"><?php esc_html_e("Zaimportowano", "upsellio"); ?></span>
                      <?php else : ?>
                        <button type="button" class="btn alt ups-audit-import-btn"
                          data-account-id="<?php echo (int) $ca_acc_id; ?>"
                          data-type="ga4"
                          data-external-id="<?php echo esc_attr($ext); ?>"
                          data-display-name="<?php echo esc_attr($name); ?>"
                          data-parent-account-id="<?php echo esc_attr((string) ($acc_node["account_id"] ?? "")); ?>"><?php esc_html_e("Import", "upsellio"); ?></button>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                <?php endforeach; ?>
              <?php else : ?>
                <?php foreach ($items as $it) : ?>
                  <?php if (!is_array($it)) { continue; } ?>
                  <?php
                  $ext = $ca_type === "gsc" ? (string) ($it["site_url"] ?? "") : (string) ($it["customer_id"] ?? "");
                  $name = $ca_type === "gsc" ? (string) ($it["site_url"] ?? "") : (string) ($it["name"] ?? $ext);
                  $imported_rid = function_exists("ups_audit_find_imported_resource_id")
                      ? ups_audit_find_imported_resource_id($ca_acc_id, $ca_type, $ext)
                      : 0;
                  ?>
                  <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;border:1px solid var(--border);border-radius:8px;padding:7px 8px;">
                    <div>
                      <div style="font-size:12px;font-weight:600;"><?php echo esc_html($name); ?></div>
                      <div class="muted" style="font-size:11px;"><?php echo esc_html($ext); ?></div>
                    </div>
                    <?php if ($imported_rid > 0) : ?>
                      <span style="font-size:11px;font-weight:700;color:var(--ok,#16a34a);white-space:nowrap;"><?php esc_html_e("Zaimportowano", "upsellio"); ?></span>
                    <?php else : ?>
                      <button type="button" class="btn alt ups-audit-import-btn"
                        data-account-id="<?php echo (int) $ca_acc_id; ?>"
                        data-type="<?php echo esc_attr($ca_type); ?>"
                        data-external-id="<?php echo esc_attr($ext); ?>"
                        data-display-name="<?php echo esc_attr($name); ?>"
                        data-parent-account-id=""><?php esc_html_e("Import", "upsellio"); ?></button>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
<?php endforeach; ?>

<section class="card">
  <h2 style="margin:0 0 10px;"><?php esc_html_e("Alerty HIGH (e-mail / Slack)", "upsellio"); ?></h2>
  <p class="muted" style="margin:0 0 12px;font-size:12px;">Po sync lub cronie dziennym wysyłane są rekomendacje z priorytetem <strong>high</strong> (max 1× dziennie na klienta).</p>
  <?php if (isset($_GET["ups_audit_alerts_saved"])) : ?>
    <p style="color:var(--ok,#16a34a);font-size:12px;"><?php esc_html_e("Zapisano ustawienia alertów.", "upsellio"); ?></p>
  <?php endif; ?>
  <form method="post" action="<?php echo esc_url(function_exists("upsellio_crm_url") ? upsellio_crm_url("ca-accounts") : home_url("/crm-app/?view=ca-accounts")); ?>" style="display:grid;gap:10px;max-width:480px;">
    <?php wp_nonce_field("ups_audit_alert_settings", "ups_audit_alert_settings_nonce"); ?>
    <input type="hidden" name="ups_audit_alert_settings_save" value="1" />
    <label style="font-size:12px;font-weight:600;"><?php esc_html_e("E-mail alertów", "upsellio"); ?></label>
    <input type="email" name="ups_audit_alert_email" class="input" value="<?php echo esc_attr((string) get_option("ups_audit_alert_email", "")); ?>" placeholder="<?php echo esc_attr((string) get_option("admin_email")); ?>" />
    <label style="font-size:12px;font-weight:600;"><?php esc_html_e("Slack Incoming Webhook URL", "upsellio"); ?></label>
    <input type="url" name="ups_audit_slack_webhook_url" class="input" value="<?php echo esc_attr((string) get_option("ups_audit_slack_webhook_url", "")); ?>" placeholder="https://hooks.slack.com/services/..." />
    <button type="submit" class="btn alt"><?php esc_html_e("Zapisz alerty", "upsellio"); ?></button>
  </form>
</section>
</div>
