<?php
if (!defined("ABSPATH")) {
    exit;
}

get_header();
?>
<style>
  .miasta-wrap{width:min(1180px,calc(100% - 32px));margin:0 auto;padding:56px 0}
  .miasta-hero{padding:64px 0 36px;border-bottom:1px solid var(--border,#e6e6e1);background:var(--bg-soft,#f8f8f6)}
  .miasta-title{font-family:Syne,sans-serif;font-size:clamp(32px,5vw,52px);line-height:1.05}
  .miasta-lead{max-width:780px;margin-top:16px;color:#3d3d38;font-size:17px;line-height:1.8}
  .miasta-grid{margin-top:28px;display:grid;grid-template-columns:1fr;gap:12px 18px}
  .miasta-link{display:block;padding:14px 16px;border:1px solid #e6e6e1;border-radius:12px;color:#4a4a44;text-decoration:none;background:#fff;transition:.18s ease}
  .miasta-link:hover{border-color:#1d9e75;color:#1d9e75;transform:translateY(-2px)}
  @media(min-width:761px){.miasta-wrap{width:min(1180px,calc(100% - 48px))}.miasta-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
  @media(min-width:961px){.miasta-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
</style>
<main>
  <section class="miasta-hero">
    <div class="miasta-wrap">
      <h1 class="miasta-title">Marketing i strony WWW - miasta w Polsce</h1>
      <p class="miasta-lead">
        Obsługujemy firmy lokalnie i ogólnopolsko. Poniżej znajdziesz podstrony lokalne z ofertą marketingu, kampanii reklamowych i stron WWW dla największych miast.
      </p>
    </div>
  </section>
  <section class="miasta-wrap">
    <div class="miasta-grid">
    <?php
    $cities = get_posts([
        "post_type" => "miasto",
        "numberposts" => 200,
        "post_status" => "publish",
        "orderby" => "title",
        "order" => "ASC",
    ]);

    if (!empty($cities)) :
        foreach ($cities as $cityPost) :
            ?>
            <a class="miasta-link" href="<?php echo esc_url(get_permalink($cityPost->ID)); ?>">
              <?php echo esc_html(get_the_title($cityPost->ID)); ?>
            </a>
            <?php
        endforeach;
    else :
        foreach (upsellio_get_cities_dataset() as $city) :
            ?>
            <a class="miasta-link" href="<?php echo esc_url(home_url("/miasto/" . $city["slug"] . "/")); ?>">
              <?php echo esc_html("Marketing i strony WWW " . $city["name"]); ?>
            </a>
            <?php
        endforeach;
    endif;
    ?>
    </div>
  </section>
</main>
<?php
get_footer();

