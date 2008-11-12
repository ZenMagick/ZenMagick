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

    $toolbox = ZMToolbox::instance();
    // do show the not available image
    ZMSettings::set('isShowNoPicture', false);

    // allow to override with custom fields
    if (function_exists('zm_quick_edit_field_list')) {
        $zm_quick_edit_field_list = zm_quick_edit_field_list();
    } else {
        // default fields
        $zm_quick_edit_field_list = array(
            // title, form field name, getter/setter name
            array('title' => 'Name', 'field' => 'name', 'property' => 'name', 'size' => 35),
            array('title' => 'Model', 'field' => 'model', 'property' => 'model', 'size' => 14),
            array('title' => 'Image', 'field' => 'image', 'property' => 'defaultImage', 'size' => 24),
            array('title' => 'Quantity', 'field' => 'quantity', 'property' => 'quantity', 'size' => 4),
            array('title' => 'Product Price', 'field' => 'productPrice', 'property' => 'productPrice', 'size' => 6),
            array('title' => 'Status', 'field' => 'status', 'property' => 'status', 'size' => 2)
        );
    }

    if (null != ZMRequest::getParameter('submit')) {
        $productIdList = ZMProducts::instance()->getProductIdsForCategoryId(ZMRequest::getCategoryId(), false);
        foreach ($productIdList as $productId) {
            // build a data map for each submitted product
            $formData = array();
            // and one with the original value to compare and detect state data
            $_formData = array();
            foreach ($zm_quick_edit_field_list as $field) {
                $fieldname = $field['field'].'_'.$productId;
                $value = ZMRequest::getParameter($fieldname);
                $_value = ZMRequest::getParameter('_'.$fieldname);
                if (null != $field['property']) {
                    $formData[$field['property']] = $value;
                    $_formData[$field['property']] = $_value;
                } else {
                    $formData[$field['field']] = $value;
                    $_formData[$field['field']] = $_value;
                }
            }
            // load product, convert to map and compare with the submitted form data
            $product = ZMProducts::instance()->getProductForId($productId);
            $productData = ZMBeanUtils::obj2map($product, array_keys($formData));
            $isUpdate = false;
            foreach ($formData as $key => $value) {
                if (array_key_exists($key, $productData) && $value != $productData[$key]) {
                    if ($_formData[$key] == $productData[$key]) {
                        $isUpdate = true;
                    } else {
                        $isUpdate = false;
                        ZMMessages::instance()->warn('Found stale data for productId '.$productId. ' - skipping update');
                    }
                    break;
                }
            }
            if ($isUpdate) {
                ZMBeanUtils::setAll($product, $formData);
                ZMProducts::instance()->updateProduct($product);
            }
        }
    }

    $productList = ZMProducts::instance()->getProductsForCategoryId(ZMRequest::getCategoryId(), false);

    $lastIndex = count($zm_quick_edit_field_list) - 1;

?>

  <h2>Quick Edit</h2>

  <?php $toolbox->form->open('', $zm_nav_params) ?>
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
            <td class="first" style="text-align:right;"><a href="<?php $toolbox->net->url('', $zm_nav_params.'&productId='.$product->getId()) ?>"><?php echo $product->getId() ?></a></td>
            <?php foreach ($zm_quick_edit_field_list as $ii => $field) { 
              if (null != $field['property']) {
                $getMethod = 'get'.ucwords($field['property']);
                $value = $product->$getMethod();
              } else {
                $value = $product->get($field['field']);
              }
              $value = $toolbox->html->encode($value, false);
              ?>
              <td<?php echo ($ii == $lastIndex ? ' class="last"' : '') ?> style="text-align:center;">
                <?php $method = isset($field['method']) ? $field['method'] : 'zm_quick_edit_input_field'; ?>
                <?php echo $method($field, $field['field'].'_'.$product->getId(), $value, $product); ?>
              <input type="hidden" name="_<?php echo ($field['field'].'_'.$product->getId()) ?>" value="<?php echo $value ?>">
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
