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
        <a href="<?php echo esc_url(home_url("/miasta/")); ?>" style="font-size:14px;color:#7c7c74;">Miasta</a>
      </div>
    </div>

    <?php echo upsellio_get_footer_city_links_html(); ?>

    <div style="margin-top:22px;padding-top:14px;border-top:1px solid #e6e6e1;font-size:12px;color:#7c7c74;text-align:center;">
      © <?php echo esc_html(gmdate("Y")); ?> Upsellio / Sebastian Kelm. Wszelkie prawa zastrzeżone.
    </div>
  </div>
</footer>
<script>
  (function () {
    const ham = document.getElementById("upsellio-hamburger");
    const mob = document.getElementById("upsellio-mobile-menu");
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

