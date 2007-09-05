This is the ZenMagick plugin for Crossell support.


Installation
============
1) Download and unpack the zen-cart Crossell module from
   here: http://www.zen-cart.com/index.php?main_page=product_contrib_info&cPath=40_60&products_id=76
2) Unzip this plugin into the zenmagick/plugins directory.
3) Install plugin using the ZenMagick Plugin Manager.
4) Enjoy


Usage
=====
In your product template you may use the following code:

    <?php if (is_object($zm_crossell)) { $crossellProducts = $zm_crossell->getXSellForProductId($zm_product->getId()); ?>
        <?php if (0 < count($crossellProducts)) { ?>
          <h2>You might also be interested in any of these products:</h2>
          <?php foreach ($crossellProducts as $xprod) { ?>
              Product: <?php echo $xprod->getName() ?><br>
              Model: <?php echo $xprod->getModel() ?><br>
          <?php } ?>
        <?php } ?>
    <?php } ?>

Basically, the returned products have all the properties and methods available as all other
product objects created by ZenMagick.

Please note that as long as you have a product id to use for the lookup, you can display crossell
products whereever you like (sidebox, etc).
