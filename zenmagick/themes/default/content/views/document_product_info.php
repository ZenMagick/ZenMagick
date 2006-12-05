<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * $Id$
 */
?>

<h2><?php echo $zm_product->getName(); ?></h2>

<?php zm_add_product_form($zm_product->getId()) ?>
  <?php $imageInfo = $zm_product->getImageInfo() ?>
  <script type="text/javascript">
      function productImgPopup(link) {
          var height = <?php echo MEDIUM_IMAGE_HEIGHT ?>;
          var width = <?php echo MEDIUM_IMAGE_WIDTH ?>;
          var args = "status,height="+(height+240)+",width="+(width+220);
          var win = window.open("", "bigimg", args);
          if (null != win) {
              win.document.write(<?php
                ?>'<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">\n<?php
                ?><html><head><title><?php stripslashes($zm_product->getName()) ?></title><?php
                ?><link rel=stylesheet href="<?php $zm_theme->themeURL("popup.css") ?>" type="text/css"></head><?php
                ?><body class=bigimage><p><img src="<?php echo zm_htmlurlencode($imageInfo->getMediumImage()) ?>"<?php
                ?>height=266 width=405 alt=""></p><?php
                ?><p>[ <a href="javascript:window.close()">Close Window</a> ]</p></body></html>');
              win.document.close();
              win.focus();
          }
      }
  </script>
  <a href="<?php echo $imageInfo->getMediumImage() ?>" onclick="productImgPopup(); return false;"><?php zm_product_image($zm_product) ?></a>
  <div id="desc"><?php echo $zm_product->getDescription(); ?></div>
  <p id="price"><?php echo $zm_product->getModel() ?>: <?php zm_fmt_price($zm_product) ?></p>
  <?php $attributes = zm_build_attribute_elements($zm_product); ?>
  <?php foreach ($attributes as $attribute) { ?>
      <fieldset>
          <legend><?php echo $attribute['name'] ?></legend>
          <?php foreach ($attribute['html'] as $option) { ?>
            <p><?php echo $option ?></p>
          <?php } ?>
      </fieldset>
  <?php } ?>
  <fieldset>
      <label for="cart_quantity"><?php zm_l10n("Quantity") ?></label>
      <input type="text" name="cart_quantity" value="1" maxlength="6" size="4" />
      <input type="submit" value="<?php zm_l10n("Add to cart") ?>" />
  </fieldset>
</form>
