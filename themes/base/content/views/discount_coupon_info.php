<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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

<?php $restrictions = $currentCoupon->getRestrictions(); ?>
<p><?php _vzm("The Discount Coupon Redemption Code you have entered is for %s.", $currentCoupon->getName()) ?></p>
<p><?php _vzm("Discount Offer:") ?><br /><?php echo $html->encode($currentCoupon->getDescription()) ?></p>

<h4><?php _vzm("Promotional Period") ?></h4>
<p><?php _vzm("The coupon is valid between %s and %s.", $locale->shortDate($currentCoupon->getStartDate()), $locale->shortDate($currentCoupon->getExpiryDate())) ?></p>

<?php if ($restrictions->hasCategories()) { ?>
    <h4><?php _vzm("Category Restrictions")?></h4>
    <ul>
    <?php foreach ($restrictions->getCategories() as $restriction) { $category = $restriction->getCategory($session->getLanguageId()); ?>
        <li><?php echo $html->encode($category->getName()) ?> - <?php echo ($restriction->isAllowed() ? _zm(" included") : _zm(" excluded")) ?></li>
    <?php } ?>
    </ul>
<?php } ?>

<?php if ($restrictions->hasProducts()) { ?>
    <h4><?php _vzm("Product Restrictions")?></h4>
    <ul>
    <?php foreach ($restrictions->getProducts() as $restriction) { $product = $restriction->getProduct($session->getLanguageId()); ?>
        <li><?php echo $html->encode($product->getName()) ?> - <?php echo ($restriction->isAllowed() ? _zm(" included") : _zm(" excluded")) ?></li>
    <?php } ?>
    </ul>
<?php } ?>

<?php if (!$restrictions->hasRestrictions()) { ?>
    <h4><?php _vzm("The coupon is valid for all categories and products.") ?></h4>
<?php } ?>

<?php echo $form->open('discount_coupon', 'action=lookup', false, array('method' => 'get')) ?>
  <fieldset>
    <legend><?php _vzm("Look-up another discount coupon ...") ?></legend>
    <label for="lookup_discount_coupon"><?php _vzm("Your Code") ?></label>
    <input type="text" id="lookup_discount_coupon" name="lookup_discount_coupon" size="40" value="<?php echo $html->encode($currentCouponCode) ?>" />
  </fieldset>
  <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Send") ?>" />
</form>
