<?php

if (!defined("ABSPATH")) {
    exit;
}

$lead_magnet_post = get_posts([
    "post_type" => "lead_magnet",
    "post_status" => "publish",
    "posts_per_page" => 1,
]);
$lead_magnet = !empty($lead_magnet_post) ? $lead_magnet_post[0] : null;

if (!$lead_magnet instanceof WP_Post) {
    return;
}

$lead_magnet_id = (int) $lead_magnet->ID;
$lead_magnet_title = (string) get_the_title($lead_magnet_id);
$lead_magnet_badge = (string) get_post_meta($lead_magnet_id, "_ups_lm_badge", true);
$lead_magnet_type = (string) get_post_meta($lead_magnet_id, "_ups_lm_type", true);
$lead_magnet_meta = (string) get_post_meta($lead_magnet_id, "_ups_lm_meta", true);
$lead_magnet_bullets = function_exists("upsellio_parse_textarea_lines")
    ? upsellio_parse_textarea_lines((string) get_post_meta($lead_magnet_id, "_ups_lm_bullets", true), 6)
    : [];
$lead_magnet_format_parts = array_filter([$lead_magnet_type, $lead_magnet_meta, "bezpłatny"]);
$lead_magnet_format_label = !empty($lead_magnet_format_parts) ? "Format: " . implode(" · ", $lead_magnet_format_parts) : "";
?>
<section class="section section-border" id="lead-magnet">
  <div class="wrap">
    <div class="home-lead-magnet">
      <div class="home-lead-copy reveal">
        <div class="eyebrow">Lead magnet</div>
        <h2 class="h2">Pobierz checklistę audytu kampanii i strony</h2>
        <?php if ($lead_magnet_badge !== "") : ?><div class="home-lead-badge"><?php echo esc_html($lead_magnet_badge); ?></div><?php endif; ?>
        <?php if ($lead_magnet_format_label !== "") : ?><p class="home-lead-format"><?php echo esc_html($lead_magnet_format_label); ?></p><?php endif; ?>
        <?php if (!empty($lead_magnet_bullets)) : ?>
          <div class="home-lead-bullets">
            <strong>W tym materiale znajdziesz:</strong>
            <ul>
              <?php foreach (array_slice($lead_magnet_bullets, 0, 5) as $bullet) : ?>
                <li><?php echo esc_html((string) $bullet); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
      </div>
      <div class="home-lead-form-shell reveal d1">
        <form class="home-lead-form" method="post" action="<?php echo esc_url(admin_url("admin-post.php")); ?>" data-upsellio-lead-form="1" data-upsellio-server-form="1">
          <input type="hidden" name="action" value="upsellio_submit_lead" />
          <input type="hidden" name="redirect_url" value="<?php echo esc_url(home_url("/#lead-magnet")); ?>" />
          <input type="hidden" name="lead_form_origin" value="home-lead-magnet" />
          <input type="hidden" name="lead_source" value="home-lead-magnet" />
          <input type="hidden" name="lead_service" value="<?php echo esc_attr($lead_magnet_title); ?>" />
          <input type="hidden" name="lead_message" value="<?php echo esc_attr("Pobranie materiału: " . $lead_magnet_title); ?>" />
          <input type="hidden" name="lead_magnet_name" value="<?php echo esc_attr($lead_magnet_title); ?>" />
          <input type="hidden" name="utm_source" data-ups-utm="source" value="" />
          <input type="hidden" name="utm_medium" data-ups-utm="medium" value="" />
          <input type="hidden" name="utm_campaign" data-ups-utm="campaign" value="" />
          <input type="hidden" name="landing_url" data-ups-context="landing" value="" />
          <input type="hidden" name="referrer" data-ups-context="referrer" value="" />
          <input type="text" name="lead_website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;" />
          <?php wp_nonce_field("upsellio_unified_lead_form", "upsellio_lead_form_nonce"); ?>
          <label for="home-lm-name">Imię *</label>
          <input id="home-lm-name" class="input" type="text" name="lead_name" required />
          <label for="home-lm-email">E-mail *</label>
          <input id="home-lm-email" class="input" type="email" name="lead_email" required />
          <label class="hero-consent">
            <input type="checkbox" name="lead_consent" value="1" required />
            <span>Wyrażam zgodę na kontakt w sprawie pobranego materiału.</span>
          </label>
          <button type="submit" class="btn btn-primary">Pobierz materiał</button>
        </form>
      </div>
    </div>
  </div>
</section>
