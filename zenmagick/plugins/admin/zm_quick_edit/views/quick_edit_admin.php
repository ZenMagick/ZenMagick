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

  // do show the not available image
  zm_set_setting('isShowNoPicture', false);

  $productList = $zm_products->getProductsForCategoryId($zm_request->getCategoryId());

  // allow to override with custom fields
  global $zm_quick_edit_field_list;
  if (!isset($zm_quick_edit_field_list)) {
      // default fields
      $zm_quick_edit_field_list = array(
          // title, form field name, getter/setter name
          array('title' => 'Name', 'field' => 'name', 'method' => 'name', 'size' => 35),
          array('title' => 'Model', 'field' => 'model', 'method' => 'model', 'size' => 14),
          array('title' => 'Image', 'field' => 'image', 'method' => 'defaultImage', 'size' => 24),
          array('title' => 'Quantity', 'field' => 'quantity', 'method' => 'quantity', 'size' => 4),
          array('title' => 'Product Price', 'field' => 'productPrice', 'method' => 'productPrice', 'size' => 6)
      );
  }

  if (null != $zm_request->getParameter('submit')) {
      foreach ($productList as $ii => $product) {
          foreach ($zm_quick_edit_field_list as $field) {
              $fieldname = $field['field'].'_'.$product->getId();
              $value = $zm_request->getParameter($fieldname);
              if (null != $field['method']) {
                  $setMethod = 'set'.ucwords($field['method']);
                  $product->$setMethod($value);
              } else {
                  $product->set($field['field'], $value);
              }

          }
          $productList[$ii] = $zm_products->updateProduct($product);
      }
  }

  $lastIndex = count($zm_quick_edit_field_list) - 1;

?>

  <h2>Quick Edit</h2>

  <?php zm_form('', $zm_nav_params, '', 'post') ?>
    <table cellspacing="0" cellpadding="0" class="presults" style="position:relative;width:auto;">
      <thead><tr>
        <th class="first">Id</th>
        <?php foreach ($zm_quick_edit_field_list as $ii => $field) { ?>
          <th<?php echo ($ii == $lastIndex ? ' class="last"' : '') ?>><?php echo $field['title'] ?></th>
        <?php } ?>
      </tr></thead>
      <tbody>
        <?php $first = true; $odd = true; foreach ($productList as $product) { ?>
          <tr class="<?php echo ($odd?"odd":"even").($first?" first":" other") ?>">
            <td class="first"><a href="<?php zm_href('', $zm_nav_params.'&productId='.$product->getId()) ?>"><?php echo $product->getId() ?></a></td>
            <?php foreach ($zm_quick_edit_field_list as $ii => $field) { 
              if (null != $field['method']) {
                $getMethod = 'get'.ucwords($field['method']);
                $value = $product->$getMethod();
              } else {
                $value = $product->get($field['field']);
              }
              $value = htmlentities($value);
              ?>
              <td<?php echo ($ii == $lastIndex ? ' class="last"' : '') ?>>
                <input type="text" name="<?php echo $field['field'] ?>_<?php echo $product->getId() ?>" value="<?php echo $value ?>" size="<?php echo $field['name'] ?>">
              </td>
            <?php } ?>
          </tr>
        <?php $first = false; $odd = !$odd; } ?>
      </tbody>
    </table>
    <p style="padding:8px 25px;text-align:center;">
      <input type="hidden" name="fkt" value="zm_quick_edit_admin">
      <input type="submit" name="submit" value="update all products">
    </p>
  </form>
