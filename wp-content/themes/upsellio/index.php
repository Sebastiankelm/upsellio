<?php

if (!defined("ABSPATH")) {
    exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo("charset"); ?>" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
  <?php wp_body_open(); ?>
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

