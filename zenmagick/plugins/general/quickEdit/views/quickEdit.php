<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * $Id: quick_edit_admin.php 2560 2009-11-02 20:08:36Z dermanomann $
 */
?>
<?php

    $categoryId = $request->getCategoryId();

    // allow to override with custom fields
    // XXX: function??
    if (function_exists('zm_quick_edit_field_list')) {
        $zm_quick_edit_field_list = zm_quick_edit_field_list();
    } else {
        // default fields
        $zm_quick_edit_field_list = array(
            // name, widget, propert is optional in case the fieldname and product proerty name do not match
            array('name' => 'name', 'widget' => 'TextFormWidget#title=Name&name=name&size=35'),
            array('name' => 'model', 'widget' => 'TextFormWidget#title=Model&name=model&size=14'),
            array('name' => 'image', 'widget' => 'TextFormWidget#title=Image&name=image&size=24', 'property' => 'defaultImage'),
            array('name' => 'quantity', 'widget' => 'TextFormWidget#title=Quantity&name=quantity&size=4'),
            array('name' => 'productPrice', 'widget' => 'TextFormWidget#title=Product Price&name=productPrice&size=7'),
            array('name' => 'status', 'widget' => 'TextFormWidget#title=Status&name=status&size=2')
        );
    }

    // build map of field name = property name;
    // while doing that instantiate all widgets
    $fieldMap = array();
    foreach ($zm_quick_edit_field_list as $ii => $field) {
        $widget = ZMBeanUtils::getBean($field['widget']);
        $zm_quick_edit_field_list[$ii]['widget'] = $widget;
        $fieldMap[$field['name']] = isset($field['property']) ? $field['property'] : $field['name'];
    }

    // first handle updates
    if ('POST' == $request->getMethod() && null != $request->getParameter('submit')) {
        $productIdList = ZMProducts::instance()->getProductIdsForCategoryId($request->getCategoryId(), false);
        foreach ($productIdList as $productId) {
            // build a data map for each submitted product
            $formData = array();
            // and one with the original value to compare and detect state data
            $_formData = array();
            foreach ($zm_quick_edit_field_list as $field) {
                $widget = $field['widget'];
                if ($widget instanceof ZMFormWidget) {
                    $fieldName = $field['name'].'_'.$productId;
                    // use widget to *read* the value to allow for optional conversions, etc
                    $widget->setValue($request->getParameter($fieldName));
                    $formData[$fieldMap[$field['name']]] = $widget->getStringValue();
                    $widget->setValue($request->getParameter('_'.$fieldName));
                    $_formData[$fieldMap[$field['name']]] = $widget->getStringValue();
                }
            }
            // load product, convert to map and compare with the submitted form data
            $product = ZMProducts::instance()->getProductForId($productId);
            $productData = ZMBeanUtils::obj2map($product, $fieldMap);
            $isUpdate = false;
            foreach ($formData as $key => $value) {
                if (array_key_exists($key, $productData) && $value != $productData[$key]) {
                    if ($_formData[$key] == $productData[$key]) {
                        $isUpdate = true;
                    } else {
                        $isUpdate = false;
                        ZMMessages::instance()->warn('Found stale data ('.$key.') for productId '.$productId. ' - skipping update');
                    }
                    break;
                }
            }
            if ($isUpdate) {
                $product = ZMBeanUtils::setAll($product, $formData);
                ZMProducts::instance()->updateProduct($product);
            }
        }    
    }

    // prepare data for display
    $productList = ZMProducts::instance()->getProductsForCategoryId($categoryId, false);
    $lastIndex = count($zm_quick_edit_field_list) - 1;

?>

  <h2>Quick Edit: <em><?php $toolbox->html->encode(ZMCategories::instance()->getCategoryForId($categoryId)->getName()) ?></em></h2>

  <?php $toolbox->form->open('', $defaultUrlParams) ?>
    <table cellspacing="0" cellpadding="0" class="presults" style="position:relative;width:auto;">
      <thead><tr>
        <th class="first">Id</th>
        <?php foreach ($zm_quick_edit_field_list as $ii => $field) { ?>
          <th<?php echo ($ii == $lastIndex ? ' class="last"' : '') ?>><?php echo $field['widget']->getTitle() ?></th>
        <?php } ?>
      </tr></thead>
      <tbody>
        <?php $first = true; $odd = true; foreach ($productList as $product) { ?>
          <tr class="<?php echo ($odd?"odd":"even").($first?" first":" other") ?>">
            <td class="first" style="text-align:right;"><a href="<?php $toolbox->net->url('', $defaultUrlParams.'&productId='.$product->getId()) ?>"><?php echo $product->getId() ?></a></td>
            <?php foreach ($zm_quick_edit_field_list as $ii => $field) { $widget = $field['widget'];
              // allow widgets to do custom calculations, etc
              $widget->setProduct($product);
              $fieldName = $field['name'].'_'.$product->getId();
              $productData = ZMBeanUtils::obj2map($product, $fieldMap);
              $value = $productData[$fieldMap[$field['name']]];
              $widget->setName($fieldName);
              $widget->setValue($value);
              ?>
              <td<?php echo ($ii == $lastIndex ? ' class="last"' : '') ?> style="text-align:center;">
                <?php echo $widget->render($request) ?>
                <input type="hidden" name="_<?php echo $fieldName ?>" value="<?php $toolbox->html->encode($value) ?>">
              </td>
            <?php } ?>
          </tr>
        <?php $first = false; $odd = !$odd; } ?>
      </tbody>
    </table>
    <p style="padding:8px 25px;text-align:center;">
      <input type="hidden" name="fkt" value="QuickEditTab">
      <input type="submit" name="submit" value="update all products">
    </p>
  </form>
