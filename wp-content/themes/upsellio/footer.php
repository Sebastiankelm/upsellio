<?php
if (!defined("ABSPATH")) {
    exit;
}
?>
<footer style="padding:64px 0 40px;border-top:1px solid #e6e6e1;">
  <div class="wrap">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:40px;flex-wrap:wrap;">
      <div style="max-width:460px;">
        <div class="brand">
          <div class="brand-mark">U</div>
          <div class="brand-text">
            <div class="brand-name">Upsellio</div>
            <div class="brand-sub">by Sebastian Kelm</div>
          </div>
        </div>
        <p style="margin-top:12px;color:#7c7c74;font-size:14px;line-height:1.7;">
          Marketing internetowy, strony i sklepy WWW dla firm, które chcą pozyskiwać lepsze leady i zwiększać sprzedaż.
        </p>
      </div>
      <div style="display:flex;flex-direction:column;gap:9px;">
        <a href="mailto:kontakt@upsellio.pl" style="font-size:14px;color:#7c7c74;">kontakt@upsellio.pl</a>
        <a href="https://linkedin.com/in/sebastiankelm" target="_blank" rel="noopener" style="font-size:14px;color:#7c7c74;">LinkedIn</a>
        <a href="<?php echo esc_url(home_url("/#uslugi")); ?>" style="font-size:14px;color:#7c7c74;">Usługi</a>
        <a href="<?php echo esc_url(home_url("/definicje/")); ?>" style="font-size:14px;color:#7c7c74;">Wiedza</a>
        <a href="<?php echo esc_url(home_url("/miasta/")); ?>" style="font-size:14px;color:#7c7c74;">Miasta</a>
      </div>
    </div>

    <?php
    $popular_definitions = get_posts([
        "post_type" => "definicja",
        "post_status" => "publish",
        "numberposts" => 12,
        "orderby" => "date",
        "order" => "DESC",
    ]);
    if (!empty($popular_definitions)) :
        ?>
      <section style="margin-top:28px;padding-top:24px;border-top:1px solid #e6e6e1;">
        <h3 style="margin:0 0 12px;font-family:Syne,sans-serif;font-size:18px;color:#111110;">Popularne definicje</h3>
        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px 16px;">
          <?php foreach ($popular_definitions as $definition) :
              $term = get_post_meta($definition->ID, "_upsellio_definition_term", true) ?: get_the_title($definition->ID);
              ?>
            <a href="<?php echo esc_url(get_permalink($definition->ID)); ?>" style="font-size:13px;line-height:1.5;color:#7c7c74;">
              <?php echo esc_html($term); ?>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
      <style>
        @media(min-width:861px){
          footer [style*="grid-template-columns:repeat(2,minmax(0,1fr));"]{grid-template-columns:repeat(4,minmax(0,1fr)) !important}
        }
      </style>
    <?php endif; ?>

    <?php echo upsellio_get_footer_city_links_html(); ?>

    <div style="margin-top:22px;padding-top:14px;border-top:1px solid #e6e6e1;font-size:12px;color:#7c7c74;text-align:center;">
      © <?php echo esc_html(gmdate("Y")); ?> Upsellio / Sebastian Kelm. Wszelkie prawa zastrzeżone.
    </div>
  </div>
</footer>
<script>
  (function () {
    const ham = document.getElementById("hamburger");
    const mob = document.getElementById("mobile-menu");
    if (!ham || !mob) return;

    ham.addEventListener("click", function () {
      ham.classList.toggle("open");
      mob.classList.toggle("open");
    });

    mob.querySelectorAll("a").forEach((a) => {
      a.addEventListener("click", function () {
        ham.classList.remove("open");
        mob.classList.remove("open");
      });
    });
  })();
</script>
<?php wp_footer(); ?>
</body>
</html>

