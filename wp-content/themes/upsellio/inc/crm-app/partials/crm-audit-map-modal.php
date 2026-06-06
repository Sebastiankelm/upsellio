<?php

if (!defined("ABSPATH")) {
    exit;
}

/** @var array<int, WP_Post> $ca_crm_clients */
/** @var array<int, string> $ca_gacc_labels */
/** @var array<int, WP_Post> $ca_all_resources */
?>
<div id="ups-audit-map-modal" class="ups-audit-map-modal" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="ups-audit-map-modal-title" style="display:none;">
  <div class="card" style="width:min(920px,96vw);max-height:85vh;overflow:auto;">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
      <h3 id="ups-audit-map-modal-title" style="margin:0;"><?php esc_html_e("Mapowanie zasobów → profil klienta", "upsellio"); ?></h3>
      <button type="button" class="btn alt" onclick="upsAuditCloseMapModal()"><?php esc_html_e("Zamknij", "upsellio"); ?></button>
    </div>
    <p class="muted" id="ups-audit-map-modal-subtitle" style="margin-top:6px;"></p>
    <div id="ups-audit-map-create-inline" style="display:none;margin:10px 0;padding:10px;border:1px dashed var(--border);border-radius:10px;">
      <p class="muted" style="margin:0 0 8px;font-size:12px;"><?php esc_html_e("Brak profilu? Utwórz szybko:", "upsellio"); ?></p>
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <input type="text" class="input" id="ups-audit-map-inline-name" placeholder="<?php esc_attr_e("Nazwa profilu", "upsellio"); ?>" style="flex:1;min-width:160px;" />
        <button type="button" class="btn alt" id="ups-audit-map-inline-create"><?php esc_html_e("Utwórz i wybierz", "upsellio"); ?></button>
      </div>
    </div>
    <label style="display:block;font-size:12px;font-weight:600;margin:10px 0 4px;"><?php esc_html_e("Profil klienta", "upsellio"); ?></label>
    <select id="ups-audit-map-client-select" class="input" style="width:100%;max-width:360px;margin-bottom:12px;">
      <option value=""><?php esc_html_e("— wybierz profil —", "upsellio"); ?></option>
      <?php foreach ($ca_crm_clients as $cc) : ?>
        <?php if (!($cc instanceof WP_Post)) { continue; } ?>
        <option value="<?php echo (int) $cc->ID; ?>"><?php echo esc_html((string) $cc->post_title); ?></option>
      <?php endforeach; ?>
    </select>
    <div id="ups-audit-map-tiles" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;">
      <?php foreach ($ca_all_resources as $res) : ?>
        <?php if (!($res instanceof WP_Post)) { continue; } ?>
        <?php
        $rid = (int) $res->ID;
        $rtype = (string) get_post_meta($rid, "_ups_resource_type", true);
        $rext = (string) get_post_meta($rid, "_ups_resource_external_id", true);
        $rclient = (int) get_post_meta($rid, "_ups_resource_client_id", true);
        $racc = (int) get_post_meta($rid, "_ups_resource_google_account_id", true);
        $rmacc = (int) get_post_meta($rid, "_ups_resource_meta_account_id", true);
        $racc_label = $racc > 0 ? (string) ($ca_gacc_labels[$racc] ?? ("Google #" . $racc)) : "";
        if ($rmacc > 0) {
            $racc_label = (string) ($ca_macc_labels[$rmacc] ?? ("Meta #" . $rmacc));
        }
        ?>
        <label class="ups-audit-map-tile" data-google-account-id="<?php echo (int) $racc; ?>" data-meta-account-id="<?php echo (int) $rmacc; ?>" data-mapped-client-id="<?php echo (int) $rclient; ?>" style="display:flex;gap:8px;align-items:flex-start;border:1px solid var(--border);border-radius:10px;padding:8px;">
          <input type="checkbox" class="ups-audit-map-cb" value="<?php echo (int) $rid; ?>" />
          <span>
            <b><?php echo esc_html((string) $res->post_title); ?></b>
            <span class="muted" style="display:block;font-size:12px;"><?php echo esc_html(strtoupper($rtype)); ?> · <?php echo esc_html($rext); ?><?php if ($racc_label !== "") : ?> · <em><?php echo esc_html($racc_label); ?></em><?php endif; ?></span>
          </span>
        </label>
      <?php endforeach; ?>
    </div>
    <?php if (empty($ca_all_resources)) : ?>
      <p class="muted" style="margin-top:10px;"><?php esc_html_e("Brak zaimportowanych zasobów — przejdź do Połączeń Google i kliknij Import przy GA4/GSC.", "upsellio"); ?></p>
    <?php endif; ?>
    <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px;">
      <button type="button" class="btn alt" onclick="upsAuditCloseMapModal()"><?php esc_html_e("Anuluj", "upsellio"); ?></button>
      <button type="button" class="btn" onclick="upsAuditSaveMapping()"><?php esc_html_e("Zapisz mapowanie", "upsellio"); ?></button>
    </div>
  </div>
</div>
