<?php

if (!defined("ABSPATH")) {
    exit;
}

$clarity_rows = function_exists("ups_audit_clarity_list_resources") ? ups_audit_clarity_list_resources() : [];
?>
<section class="card" style="border-color:#c4b5fd;background:linear-gradient(135deg,rgba(139,92,246,.06),transparent);">
  <h3 style="margin:0 0 8px;font-size:15px;display:flex;align-items:center;gap:8px;">
    <i class="ti ti-chart-donut" aria-hidden="true"></i>
    <?php esc_html_e("Microsoft Clarity", "upsellio"); ?>
  </h3>
  <p class="muted" style="margin:0 0 12px;font-size:12px;line-height:1.55;max-width:720px;">
    <?php esc_html_e("Data Export API: sesje, UX (dead/rage), top URL, źródła, kraje, kanały — do 5 zapytań na sync, max. 3 dni wstecz, 10 zapytań/dzień/projekt.", "upsellio"); ?>
    <a href="https://learn.microsoft.com/en-us/clarity/setup-and-installation/clarity-data-export-api" target="_blank" rel="noopener noreferrer" style="color:var(--teal);font-weight:600;"><?php esc_html_e("Dokumentacja API", "upsellio"); ?></a>
  </p>
  <ol style="margin:0 0 14px;padding-left:18px;font-size:12px;line-height:1.6;">
    <li><?php esc_html_e("W Clarity: Settings → Data Export → Generate new API token (tylko admin projektu).", "upsellio"); ?></li>
    <li><?php esc_html_e("Wklej token poniżej i zapisz projekt.", "upsellio"); ?></li>
    <li><?php esc_html_e("Użyj „Mapuj zasoby” przy profilu klienta i zaznacz projekt Clarity.", "upsellio"); ?></li>
  </ol>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:10px;align-items:end;margin-bottom:12px;">
    <label>
      <span class="muted" style="font-size:11px;display:block;margin-bottom:4px;"><?php esc_html_e("Nazwa projektu", "upsellio"); ?></span>
      <input type="text" class="input" id="ups-clarity-project-name" placeholder="np. wtapes.pl" style="width:100%;" />
    </label>
    <label>
      <span class="muted" style="font-size:11px;display:block;margin-bottom:4px;"><?php esc_html_e("Identyfikator (slug, opcjonalnie)", "upsellio"); ?></span>
      <input type="text" class="input" id="ups-clarity-project-slug" placeholder="wtapes" style="width:100%;" />
    </label>
    <label style="grid-column:1/-1;">
      <span class="muted" style="font-size:11px;display:block;margin-bottom:4px;"><?php esc_html_e("API token (Bearer)", "upsellio"); ?></span>
      <input type="password" class="input" id="ups-clarity-api-token" autocomplete="off" spellcheck="false" placeholder="eyJ..." style="width:100%;max-width:520px;" />
    </label>
  </div>
  <div style="display:flex;flex-wrap:wrap;gap:8px;">
    <button type="button" class="btn alt" id="ups-clarity-test-btn"><?php esc_html_e("Test połączenia", "upsellio"); ?></button>
    <button type="button" class="btn" id="ups-clarity-import-btn"><i class="ti ti-plus" aria-hidden="true"></i> <?php esc_html_e("Dodaj projekt Clarity", "upsellio"); ?></button>
  </div>
  <?php if (!empty($clarity_rows)) : ?>
    <div style="margin-top:16px;">
      <strong style="font-size:12px;"><?php esc_html_e("Projekty w bazie", "upsellio"); ?></strong>
      <table style="width:100%;margin-top:8px;font-size:12px;">
        <thead><tr><th style="text-align:left;"><?php esc_html_e("Projekt", "upsellio"); ?></th><th><?php esc_html_e("Profil", "upsellio"); ?></th><th><?php esc_html_e("API dziś", "upsellio"); ?></th></tr></thead>
        <tbody>
        <?php foreach ($clarity_rows as $cr) : ?>
          <tr>
            <td><?php echo esc_html((string) ($cr["title"] ?? "")); ?></td>
            <td><?php echo (int) ($cr["client_id"] ?? 0) > 0 ? esc_html(get_the_title((int) $cr["client_id"])) : "—"; ?></td>
            <td><?php echo (int) ($cr["api_usage_today"] ?? 0); ?>/10</td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>
