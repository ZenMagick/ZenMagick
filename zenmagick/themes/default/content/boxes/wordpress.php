<?php 
    // init wordpress
    define('WP_USE_THEMES', false); require_once('M:\webserver\radebatz.net\mano/bolg/wp-config.php'); 
    // set up configured categories, etc
    query_posts("category_name=ZenMagick&showposts=5&cat=ZenMagick");
?>
<?php if (have_posts()) : ?>
  <h3><?php zm_l10n("ZenMagick Blog") ?></h3>
  <div id="sb_wordpress" class="box">
    <dl>
      <?php while (have_posts()) : the_post(); ?>
        <dt><?php the_time('d/m/Y','',''); ?></dt>
        <dd><a href="<?php the_permalink() ?>" rel="bookmark"<?php zm_href_target() ?>><?php the_title(); ?></a></dd>
      <?php endwhile; ?>
    </dl>
  </div>
<?php endif; ?>
<?php $zm_runtime->reconnectDB(); /* reconnect to zen-cart */ ?>
