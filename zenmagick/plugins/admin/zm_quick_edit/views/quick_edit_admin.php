<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
<?php

  zm_set_setting('isShowNoPicture', false);

  $productList = $zm_products->getProductsForCategoryId($zm_request->getCategoryId());

  if (null != $zm_request->getParameter('submit')) {
      foreach ($productList as $ii => $product) {
          $product->setName($zm_request->getParameter('name_'.$product->getId()));
          $product->setModel($zm_request->getParameter('model_'.$product->getId()));
          $product->setDefaultImage($zm_request->getParameter('image_'.$product->getId()));
          $product->setQuantity($zm_request->getParameter('quantity_'.$product->getId()));
          $product->setProductPrice($zm_request->getParameter('pprice_'.$product->getId()));
          $productList[$ii] = $zm_products->updateProduct($product);
      }
  }

?>

  <h2>Quick Edit</h2>

  <?php zm_form('', $zm_nav_params, '', 'post') ?>
    <table cellspacing="0" cellpadding="0" class="presults" style="position:relative;width:auto;">
      <thead><tr>
        <th class="first">Id</th>
        <th>Name</th>
        <th>Model</th>
        <th>Image</th>
        <th>Quantity</th>
        <th class="last">Product Price</th>
      </tr></thead>
      <tbody>
        <?php $first = true; $odd = true; foreach ($productList as $product) { ?>
          <tr class="<?php echo ($odd?"odd":"even").($first?" first":" other") ?>">
            <td class="first"><a href="<?php zm_href('', $zm_nav_params.'&productId='.$product->getId()) ?>"><?php echo $product->getId() ?></a></td>
            <td><input type="text" name="name_<?php echo $product->getId() ?>" value="<?php echo htmlentities($product->getName()) ?>" size="35"></td>
            <td><input type="text" name="model_<?php echo $product->getId() ?>" value="<?php echo htmlentities($product->getModel()) ?>" size="14"></td>
            <td><input type="text" name="image_<?php echo $product->getId() ?>" value="<?php echo htmlentities($product->getDefaultImage()) ?>" size="24"></td>
            <td><input type="text" name="quantity_<?php echo $product->getId() ?>" value="<?php echo $product->getQuantity() ?>" size="4"></td>
            <td class="last"><input type="text" name="pprice_<?php echo $product->getId() ?>" value="<?php echo $product->getProductPrice() ?>" size="4"></td>
          </tr>
        <?php $first = false; $odd = !$odd; } ?>
      </tbody>
    </table>
    <p style="padding:8px 25px;text-align:center;">
      <input type="hidden" name="fkt" value="zm_quick_edit_admin">
      <input type="submit" name="submit" value="update all products">
    </p>
  </form>
