<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id$
//
?>
<?php if (isset($cssCategories) && class_exists('CategoriesUlGenerator')) {
    $categoriesULGenerator = new CategoriesUlGenerator($request);
    $resources->cssFile('css/categories_css.css');
    ?>
    <ul class="bullet-menu" id="siteMenu">
        <?php echo preg_replace('%^\s*<ul>(.+)</ul>\s*$%sim', '\1', $categoriesULGenerator->buildTree(true)); ?>
        <?php if (SHOW_CATEGORIES_BOX_SPECIALS == 'true') { ?>
           <li><a href="<?php echo $net->url('specials') ?>"><?php _vzm('Specials') ?></a></li>
        <?php } ?>
        <?php if (SHOW_CATEGORIES_BOX_PRODUCTS_NEW == 'true') { ?>
            <li><a href="<?php echo $net->url('products_new') ?>"><?php _vzm('New Products') ?></a></li>
        <?php } ?>
        <?php if (SHOW_CATEGORIES_BOX_FEATURED_PRODUCTS == 'true') { $featured = $this->container->get('productService')->getFeaturedProducts(null, 1); ?>
            <?php if (0 < count($featured)) { ?>
              <li><a href="<?php echo $net->url('featured_products') ?>"><?php _vzm('Featured Products') ?></a></li>
            <?php } ?>
        <?php } ?>
        <?php if (SHOW_CATEGORIES_BOX_PRODUCTS_ALL == 'true') { ?>
            <li><a href="<?php echo $net->url('products_all') ?>"><?_vzm('All Products') ?></a></li>
        <?php } ?>
    </ul>

    <?php $resources->jsFile('js/categories_css.js', $resources::NOW); ?>
    <script type="text/javascript">
      // Preload menu images when page loads (won't affect IE, which never caches CSS images)
      var mp = '<?php echo $this->asUrl('images/menu/').'/' ?>';
      addDOMEvent(window, "load", function() {
          preloadImages(mp+"branch.gif", mp+"leaf-end-on.gif", mp+"leaf-end.gif", mp+"leaf-on.gif", mp+"leaf.gif",
            mp+"node-end-on.gif", mp+"node-end.gif", mp+"node-on.gif", mp+"node-open-end-on.gif",
            mp+"node-open-end.gif", mp+"node-open-on.gif", mp+"node-open.gif", mp+"node.gif")},
          false);
    </script>
<?php } ?>
