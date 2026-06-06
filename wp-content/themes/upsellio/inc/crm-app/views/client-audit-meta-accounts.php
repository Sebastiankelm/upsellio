<?php

if (!defined("ABSPATH")) {
    exit;
}

$ca_meta_accounts = get_posts([
    "post_type" => "crm_meta_account",
    "posts_per_page" => -1,
    "post_status" => ["publish", "draft"],
    "orderby" => "date",
    "order" => "DESC",
]);
$meta_oauth_info = function_exists("ups_audit_meta_oauth_connection_info") ? ups_audit_meta_oauth_connection_info() : ["ready" => false, "message" => ""];
$meta_configured = function_exists("ups_audit_meta_api_configured") && ups_audit_meta_api_configured();
$connected = isset($_GET["connected"]) && (string) wp_unslash($_GET["connected"]) === "1";
$audit_err = isset($_GET["ups_audit_error"]) ? rawurldecode((string) wp_unslash($_GET["ups_audit_error"])) : "";
$account_id_new = isset($_GET["account_id"]) ? (int) wp_unslash($_GET["account_id"]) : 0;
if ($account_id_new <= 0 && $connected) {
    $uid = get_current_user_id();
    $account_id_new = (int) get_transient("ups_audit_meta_last_connected_" . $uid);
    if ($account_id_new > 0) {
        delete_transient("ups_audit_meta_last_connected_" . $uid);
    }
}
$ca_profiles_url = function_exists("upsellio_crm_url") ? upsellio_crm_url("ca-clients") : home_url("/crm-app/?view=ca-clients");
$ca_google_url = function_exists("upsellio_crm_url") ? upsellio_crm_url("ca-accounts") : home_url("/crm-app/?view=ca-accounts");
$meta_settings = upsellio_meta_ads_get_settings();
$redirect_uri = function_exists("ups_audit_meta_oauth_redirect_uri") ? ups_audit_meta_oauth_redirect_uri() : "";
?>
<div class="crm-audit-page">
<?php if ($connected) : ?>
  <section class="card" style="border-color:var(--ok,#16a34a);">
    <p style="margin:0;color:var(--ok,#16a34a);">
      <?php esc_html_e("Konto Meta połączone", "upsellio"); ?><?php echo $account_id_new > 0 ? " (ID " . (int) $account_id_new . ")" : ""; ?>.
      <?php esc_html_e("Lista kont reklamowych została pobrana — kliknij Import, potem mapuj do profilu klienta.", "upsellio"); ?>
    </p>
  </section>
<?php endif; ?>
<?php if ($audit_err !== "") : ?>
  <section class="card" style="border-color:var(--danger,#dc2626);">
    <p style="margin:0;color:var(--danger,#dc2626);"><?php echo esc_html($audit_err); ?></p>
  </section>
<?php endif; ?>

<section class="card" style="background:linear-gradient(135deg,rgba(59,130,246,.08),transparent);">
  <h2 style="margin:0 0 8px;"><?php esc_html_e("Połącz konta Meta Ads (Facebook / Instagram)", "upsellio"); ?></h2>
  <p class="muted" style="margin:0 0 12px;font-size:13px;line-height:1.55;">
    <?php esc_html_e("Osobny OAuth od Google — jedno konto Meta może mieć wiele kont reklamowych (act_…). Importuj wybrane konto, zmapuj do profilu klienta, sync KPI jak Google Ads.", "upsellio"); ?>
  </p>
  <p style="margin:0 0 12px;font-size:13px;">
    <a href="<?php echo esc_url($ca_google_url); ?>" style="color:var(--teal);"><?php esc_html_e("← Połączenia Google (GA4, GSC, Google Ads)", "upsellio"); ?></a>
  </p>
  <?php if (!$meta_configured && current_user_can("manage_options")) : ?>
    <div style="padding:12px;border:1px solid #93c5fd;border-radius:10px;background:#eff6ff;font-size:13px;margin-bottom:12px;">
      <?php esc_html_e("Najpierw uzupełnij App ID i App Secret w sekcji konfiguracji poniżej.", "upsellio"); ?>
    </div>
  <?php elseif (!empty($meta_oauth_info["ready"])) : ?>
    <?php
    $meta_connect_url = function_exists("ups_audit_meta_oauth_connect_url")
        ? ups_audit_meta_oauth_connect_url("meta-ads-crm")
        : admin_url("admin-post.php?action=ups_audit_meta_oauth&op=start");
    ?>
    <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
      <a class="btn" href="<?php echo esc_url($meta_connect_url); ?>"><i class="ti ti-brand-facebook" aria-hidden="true"></i> <?php esc_html_e("Zaloguj przez Meta", "upsellio"); ?></a>
      <?php if (count($ca_meta_accounts) > 0) : ?>
        <button type="button" class="btn alt" onclick="typeof upsAuditRefreshAllMetaAccounts==='function'&&upsAuditRefreshAllMetaAccounts()"><i class="ti ti-refresh" aria-hidden="true"></i> <?php esc_html_e("Odśwież wszystkie konta", "upsellio"); ?></button>
      <?php endif; ?>
    </div>
    <?php if ($redirect_uri !== "" && current_user_can("manage_options")) : ?>
      <section style="margin-top:14px;padding:12px 14px;background:#eff6ff;border:1px solid #93c5fd;border-radius:10px;font-size:12px;line-height:1.55;">
        <strong style="display:block;margin-bottom:6px;"><?php esc_html_e("Meta for Developers — Valid OAuth Redirect URI", "upsellio"); ?></strong>
        <code style="display:block;word-break:break-all;padding:8px;background:var(--bg);border-radius:8px;"><?php echo esc_html($redirect_uri); ?></code>
        <p style="margin:8px 0 0;font-size:11px;color:var(--text-2);">
          <a href="https://developers.facebook.com/apps/" target="_blank" rel="noopener noreferrer">developers.facebook.com</a>
          → Twoja aplikacja → Facebook Login → Settings → Valid OAuth Redirect URIs.
          Zakresy: <code>ads_read</code>, <code>business_management</code>.
        </p>
      </section>
    <?php endif; ?>
  <?php else : ?>
    <p class="muted"><?php echo esc_html((string) ($meta_oauth_info["message"] ?? "")); ?></p>
  <?php endif; ?>
</section>

<?php if (current_user_can("manage_options")) : ?>
<section class="card">
  <h3 style="margin:0 0 10px;font-size:14px;"><?php esc_html_e("Konfiguracja Meta Ads API", "upsellio"); ?></h3>
  <?php if (isset($_GET["meta_settings_saved"])) : ?>
    <p style="color:var(--ok,#16a34a);font-size:12px;"><?php esc_html_e("Zapisano ustawienia Meta.", "upsellio"); ?></p>
  <?php endif; ?>
  <form method="post" action="<?php echo esc_url(function_exists("upsellio_crm_url") ? upsellio_crm_url("ca-meta-accounts") : home_url("/crm-app/?view=ca-meta-accounts")); ?>" style="display:grid;gap:10px;max-width:520px;">
    <?php wp_nonce_field("upsellio_meta_ads_settings", "upsellio_meta_ads_settings_nonce"); ?>
    <input type="hidden" name="upsellio_meta_ads_settings_save" value="1" />
    <label style="font-size:12px;font-weight:600;">App ID</label>
    <input type="text" name="meta_app_id" class="input" value="<?php echo esc_attr($meta_settings["app_id"]); ?>" placeholder="1234567890123456" />
    <label style="font-size:12px;font-weight:600;">App Secret</label>
    <input type="password" name="meta_app_secret" class="input" value="" placeholder="<?php echo esc_attr($meta_settings["app_secret"] !== "" ? "•••••••• (zostaw puste aby nie zmieniać)" : ""); ?>" autocomplete="new-password" />
    <label style="font-size:12px;font-weight:600;">API version</label>
    <input type="text" name="meta_api_version" class="input" value="<?php echo esc_attr($meta_settings["api_version"]); ?>" placeholder="v21.0" />
    <button type="submit" class="btn alt"><?php esc_html_e("Zapisz konfigurację Meta", "upsellio"); ?></button>
  </form>
</section>
<?php endif; ?>

<section class="card">
  <h2 style="margin:0;"><?php esc_html_e("Podłączone konta Meta", "upsellio"); ?></h2>
</section>
<?php if (empty($ca_meta_accounts)) : ?>
  <section class="card"><p class="muted"><?php esc_html_e("Brak kont Meta. Użyj „Zaloguj przez Meta” powyżej.", "upsellio"); ?></p></section>
<?php endif; ?>
<?php foreach ($ca_meta_accounts as $ca_acc) : ?>
  <?php if (!($ca_acc instanceof WP_Post)) { continue; } ?>
  <?php $ca_acc_id = (int) $ca_acc->ID; ?>
  <?php $ca_cache = get_post_meta($ca_acc_id, "_ups_macc_resources_cache", true); ?>
  <?php $ca_cache = is_array($ca_cache) ? $ca_cache : []; ?>
  <?php $ca_email = (string) get_post_meta($ca_acc_id, "_ups_macc_email", true); ?>
  <?php $ca_label = (string) get_post_meta($ca_acc_id, "_ups_macc_label", true); ?>
  <?php $ca_items = (array) ($ca_cache["ad_accounts"] ?? []); ?>
  <section class="card" style="padding:16px;">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
      <div>
        <strong><?php echo esc_html($ca_email !== "" ? $ca_email : get_the_title($ca_acc_id)); ?></strong>
        <div class="muted" style="font-size:12px"><?php echo esc_html($ca_label); ?></div>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <button type="button" class="btn alt" onclick="upsAuditRefreshMetaAccountResources(<?php echo (int) $ca_acc_id; ?>)"><i class="ti ti-refresh" aria-hidden="true"></i><?php esc_html_e("Odśwież konta", "upsellio"); ?></button>
        <button type="button" class="btn alt" onclick="upsAuditDisconnectMetaAccount(<?php echo (int) $ca_acc_id; ?>)"><i class="ti ti-trash" aria-hidden="true"></i><?php esc_html_e("Odłącz", "upsellio"); ?></button>
      </div>
    </div>
    <div style="margin-top:10px;border:1px solid var(--border);border-radius:10px;padding:10px;">
      <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:8px;">
        <strong style="font-size:13px;"><?php esc_html_e("Konta reklamowe", "upsellio"); ?></strong>
        <span class="muted"><?php echo (int) count($ca_items); ?></span>
      </div>
      <?php if (empty($ca_items)) : ?>
        <p class="muted" style="margin:0;font-size:11px;line-height:1.45;">
          <?php echo esc_html(function_exists("ups_audit_get_macc_fetch_error_hint") ? ups_audit_get_macc_fetch_error_hint($ca_acc_id) : __("Brak danych.", "upsellio")); ?>
        </p>
      <?php else : ?>
        <div style="display:flex;flex-direction:column;gap:6px;max-height:320px;overflow:auto;">
          <?php foreach ($ca_items as $it) : ?>
            <?php if (!is_array($it)) { continue; } ?>
            <?php
            $ext = upsellio_meta_ads_normalize_ad_account_id((string) ($it["id"] ?? ""));
            $name = (string) ($it["name"] ?? $ext);
            $imported_rid = function_exists("ups_audit_find_imported_meta_resource_id")
                ? ups_audit_find_imported_meta_resource_id($ca_acc_id, $ext)
                : 0;
            ?>
            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;border:1px solid var(--border);border-radius:8px;padding:7px 8px;">
              <div>
                <div style="font-size:12px;font-weight:600;"><?php echo esc_html($name); ?></div>
                <div class="muted" style="font-size:11px;"><?php echo esc_html($ext); ?><?php if (!empty($it["currency"])) : ?> · <?php echo esc_html((string) $it["currency"]); ?><?php endif; ?></div>
              </div>
              <?php if ($imported_rid > 0) : ?>
                <span style="font-size:11px;font-weight:700;color:var(--ok,#16a34a);white-space:nowrap;"><?php esc_html_e("Zaimportowano", "upsellio"); ?></span>
              <?php else : ?>
                <button type="button" class="btn alt ups-audit-meta-import-btn"
                  data-meta-account-id="<?php echo (int) $ca_acc_id; ?>"
                  data-external-id="<?php echo esc_attr($ext); ?>"
                  data-display-name="<?php echo esc_attr($name); ?>"><?php esc_html_e("Import", "upsellio"); ?></button>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
<?php endforeach; ?>

<section class="card">
  <p class="muted" style="margin:0;font-size:12px;line-height:1.55;">
    <?php esc_html_e("Po imporcie przejdź do", "upsellio"); ?>
    <a href="<?php echo esc_url($ca_profiles_url); ?>"><?php esc_html_e("Profile klientów", "upsellio"); ?></a>
    <?php esc_html_e("i zmapuj zasób META do profilu. Dashboard pokaże wydatek Meta, CPC i ROAS obok Google Ads.", "upsellio"); ?>
  </p>
</section>
</div>
