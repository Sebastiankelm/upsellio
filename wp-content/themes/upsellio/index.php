<?php

if (!defined("ABSPATH")) {
    exit;
}
$upsellio_load_public_tracking = !function_exists("upsellio_should_load_public_tracking_tags") || upsellio_should_load_public_tracking_tags();
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <?php if ($upsellio_load_public_tracking) : ?>
  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-R37SMGVBNC"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-R37SMGVBNC');
  </script>
  <?php endif; ?>
  <meta charset="<?php bloginfo("charset"); ?>" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <?php if ($upsellio_load_public_tracking) : ?>
  <!-- Google Tag Manager -->
  <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-KM9J5XC2');</script>
  <!-- End Google Tag Manager -->
  <?php endif; ?>
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
  <?php wp_body_open(); ?>
  <?php if ($upsellio_load_public_tracking) : ?>
  <!-- Google Tag Manager (noscript) -->
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-KM9J5XC2"
  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
  <!-- End Google Tag Manager (noscript) -->
  <?php endif; ?>
  <main style="max-width:760px;margin:80px auto;padding:0 16px;font-family:Arial,sans-serif;">
    <?php if (have_posts()) : ?>
      <?php while (have_posts()) : the_post(); ?>
        <article>
          <h1><?php the_title(); ?></h1>
          <?php the_content(); ?>
        </article>
      <?php endwhile; ?>
    <?php else : ?>
      <h1>Upsellio</h1>
      <p>Motyw aktywny. Ustaw stronę główną i landing w panelu WordPress.</p>
    <?php endif; ?>
  </main>
  <?php wp_footer(); ?>
</body>
</html>

