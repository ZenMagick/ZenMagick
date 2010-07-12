<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 */
?>
<?php
  $typeMap = array('#' => 'Fixed Price', '$' => 'Amount', '%' => 'Percent');
?>
<form action="<?php echo $admin->url() ?>" method="GET">
  <?php echo $form->hidden($defaultUrlParams) ?>
  <input type="hidden" name="fkt" value="ProductGroupPricingTab">
  <h2>Group Pricing ( <?php echo $form->idpSelect('groupId', $priceGroups, $groupId, array('size'=>1, 'onchange'=>'this.form.submit()')) ?> )</h2>
</form>

<form action="<?php echo $admin->url(null, $defaultUrlParams) ?>" method="POST">
  <fieldset>
    <legend>Price/Discount</legend>
    <div>
      <input type="hidden" name="groupId" value="<?php echo $groupId ?>">
      <input type="hidden" name="groupPricingId" value="<?php echo $productGroupPricing->getId() ?>">
    </div>
    <p>
      <label for="discount">Discount</label> 
      <input type="text" id="discount" name="discount" value="<?php echo $productGroupPricing->getDiscount() ?>">

      <?php $type = $productGroupPricing->getType(); ?>
      <label for="type">Type</label> 
      <select id="type" name="type">
        <option value="#"<?php if ('#' == $type) { echo ' selected'; } ?>>Fixed Price</option>
        <option value="%"<?php if ('%' == $type) { echo ' selected'; } ?>>Percent</option>
        <option value="$"<?php if ('$' == $type) { echo ' selected'; } ?>>Amount</option>
      </select>
    </p>
    <p>
      <input type="checkbox" id="allowSaleSpecial" name="allowSaleSpecial" value="true" <?php $form->checked($productGroupPricing->isAllowSaleSpecial()) ?>>
      <label for="allowSaleSpecial">Allow discount on sale/special</label>
    </p>
    <p>
      <label for="startDate">Start Date</label> 
      <input type="text" id="startDate" name="startDate" value="<?php echo $locale->shortDate($productGroupPricing->getStartDate()) ?>">
      <label for="endDate">End Date</label> 
      <input type="text" id="endDate" name="endDate" value="<?php echo $locale->shortDate($productGroupPricing->getEndDate()) ?>">
      <?php echo UI_DATE_FORMAT ?>, for example: <?php echo UI_DATE_FORMAT_SAMPLE ?>
    </p>
  </fieldset>
  <p>
    <input type="hidden" name="fkt" value="ProductGroupPricingTab">
    <?php if (0 < $request->getParameter('groupPricingId')) { ?>
        <input type="submit" name="update" value="Update">
        <a href="<?php echo $admin->url('', $defaultUrlParams.'&fkt=ProductGroupPricingTab&groupPricingId='.$request->getParameter('groupPricingId').'&delete=true') ?>">Delete</a>
    <?php } else { ?>
        <input type="submit" name="create" value="Create">
    <?php } ?>
  </p>
</form>

<?php if (0 < count($productGroupPricings)) { ?>
  <fieldset>
    <legend>Current Discounts/Prices</legend>
    <table>
      <?php foreach ($productGroupPricings as $productGroupPricing) { ?>
        <tr>
          <td><?php echo $typeMap[$productGroupPricing->getType()] ?></td>
          <td><?php echo $productGroupPricing->getDiscount() ?></td>
          <td><?php echo ($productGroupPricing->isAllowSaleSpecial() ? 'Y' : 'N') ?></td>
          <td><?php echo $productGroupPricing->getStartDate() ?></td>
          <td><?php echo $productGroupPricing->getEndDate() ?></td>
          <td><a href="<?php echo $admin->url('', $defaultUrlParams.'&fkt=ProductGroupPricingTab&groupPricingId='.$productGroupPricing->getId()) ?>">Change</a></td>
        </tr>
      <?php } ?>
    </table>
  </fieldset>
<?php } ?>
