<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
 */
use ZenMagick\Base\Beans;
?>
<?php $lastIndex = count($fieldList)-1; ?>
<h2><?php _vzm('Quick Edit: <em>%s</em>', $html->encode($category->getName())) ?></h2>

<form action="<?php echo $admin->catalog() ?>" method="POST">
  <table class="grid">
    <thead><tr>
      <th class="first">Id</th>
      <?php foreach ($fieldList as $ii => $field) { ?>
        <th<?php echo ($ii == $lastIndex ? ' class="last"' : '') ?>><?php echo $field['widget']->getTitle() ?></th>
      <?php } ?>
    </tr></thead>
    <tbody>
      <?php $first = true; $odd = true; foreach ($productList as $product) { ?>
        <tr class="<?php echo ($odd?"odd":"even").($first?" first":" other") ?>">
          <td class="first" style="text-align:right;"><a href="<?php echo $admin->catalog(null, 'productId='.$product->getId()) ?>"><?php echo $product->getId() ?></a></td>
          <?php foreach ($fieldList as $ii => $field) { $widget = $field['widget'];
            // allow widgets to do custom calculations, etc
            $widget->setProduct($product);
            $fieldName = $field['name'].'_'.$product->getId();
            $productData = Beans::obj2map($product, $fieldMap);
            $value = $productData[$fieldMap[$field['name']]];
            $widget->setName($fieldName);
            $widget->setValue($value);
            ?>
            <td<?php echo ($ii == $lastIndex ? ' class="last"' : '') ?> style="text-align:center;">
              <?php echo $widget->render($request, $view) ?>
              <input type="hidden" name="<?php echo ZMQuickEditTabController::STALE_CHECK_FIELD_PREFIX.$fieldName ?>" value="<?php echo $html->encode($value) ?>">
            </td>
          <?php } ?>
        </tr>
      <?php $first = false; $odd = !$odd; } ?>
    </tbody>
  </table>
  <p style="padding:8px 25px;text-align:center;">
    <input type="hidden" name="catalogRequestId" value="quick_edit_tab">
    <input type="submit" name="submit" value="<?php _vzm('Update all products') ?>">
  </p>
</form>
