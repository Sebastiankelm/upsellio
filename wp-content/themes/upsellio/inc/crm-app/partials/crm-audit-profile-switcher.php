<?php

if (!defined("ABSPATH")) {
    exit;
}

/** @var int $cid */
/** @var string $view */

$cid = isset($cid) ? (int) $cid : 0;
$profiles = function_exists("ups_audit_get_client_profile_rows")
    ? ups_audit_get_client_profile_rows()
    : [];
if (count($profiles) < 2) {
    return;
}

$base_args = ["view" => $view];
if ($view === "ca-dashboard" && isset($_GET["window"])) {
    $w = (int) wp_unslash($_GET["window"]);
    if (in_array($w, [7, 14, 30, 60, 90], true)) {
        $base_args["window"] = $w;
    }
}
?>
<label class="crm-audit-profile-switch" style="display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;margin-left:auto;">
  <span class="muted"><?php esc_html_e("Profil", "upsellio"); ?></span>
  <select class="input" id="ups-audit-profile-switch" style="min-width:200px;max-width:280px;">
    <?php foreach ($profiles as $prow) : ?>
      <?php if (!is_array($prow)) { continue; } ?>
      <?php $pid = (int) ($prow["id"] ?? 0); ?>
      <option value="<?php echo (int) $pid; ?>" <?php selected($cid, $pid); ?>><?php echo esc_html((string) ($prow["title"] ?? "")); ?></option>
    <?php endforeach; ?>
  </select>
</label>
<script>
document.addEventListener("DOMContentLoaded", function () {
  var sel = document.getElementById("ups-audit-profile-switch");
  if (!sel) return;
  sel.addEventListener("change", function () {
    var id = Number(sel.value || 0);
    if (!id) return;
    var u = new URL(window.location.href);
    u.searchParams.set("cid", String(id));
    window.location.assign(u.toString());
  });
});
</script>
