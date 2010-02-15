<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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

    // prepare data for display
    $lastIndex = count($fieldList)-1;

?>

  <h2>Quick Edit: <em><?php echo $toolbox->html->encode(ZMCategories::instance()->getCategoryForId($categoryId)->getName()) ?></em></h2>

  <form action="<?php echo $toolbox->admin->url(null, $defaultUrlParams) ?>" method="POST">
    <table cellspacing="0" cellpadding="0" class="presults" style="position:relative;width:auto;">
      <thead><tr>
        <th class="first">Id</th>
        <?php foreach ($fieldList as $ii => $field) { ?>
          <th<?php echo ($ii == $lastIndex ? ' class="last"' : '') ?>><?php echo $field['widget']->getTitle() ?></th>
        <?php } ?>
      </tr></thead>
      <tbody>
        <?php $first = true; $odd = true; foreach ($productList as $product) { ?>
          <tr class="<?php echo ($odd?"odd":"even").($first?" first":" other") ?>">
            <td class="first" style="text-align:right;"><a href="<?php echo $toolbox->admin->url(null, $defaultUrlParams.'&productId='.$product->getId()) ?>"><?php echo $product->getId() ?></a></td>
            <?php foreach ($fieldList as $ii => $field) { $widget = $field['widget'];
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
                <input type="hidden" name="_<?php echo $fieldName ?>" value="<?php echo $toolbox->html->encode($value) ?>">
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
