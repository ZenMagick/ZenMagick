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
 * $Id$
 */
?>

<?php $restrictions = $zm_coupon->getRestrictions(); ?>
<p><?php zm_l10n("The Discount Coupon Redemption Code you have entered is for %s.", $zm_coupon->getName()) ?></p>
<p><?php zm_l10n("Discount Offer:") ?><br /><?php $html->encode($zm_coupon->getDescription()) ?></p>

<h4><?php zm_l10n("Promotional Period") ?></h4>
<p><?php zm_l10n("The coupon is valid between %s and %s.", $locale->shortDate($zm_coupon->getStartDate(), false), $locale->shortDate($zm_coupon->getExpiryDate(), false)) ?></p>

<?php if ($restrictions->hasCategories()) { ?>
    <h4><?php zm_l10n("Category Restrictions")?></h4>
    <ul>
    <?php foreach ($restrictions->getCategories() as $rest) { $category = $rest->getCategory(); ?>
        <li><?php $html->encode($category->getName()) ?> - <?php echo ($rest->isAllowed() ? zm_l10n_get(" included") : zm_l10n_get(" excluded")) ?></li>
    <?php } ?>
    </ul>
<?php } ?>

<?php if ($restrictions->hasProducts()) { ?>
    <h4><?php zm_l10n("Product Restrictions")?></h4>
    <ul>
    <?php foreach ($restrictions->getProducts() as $rest) { $product = $rest->getProduct(); ?>
        <li><?php $html->encode($product->getName()) ?> - <?php echo ($rest->isAllowed() ? zm_l10n_get(" included") : zm_l10n_get(" excluded")) ?></li>
    <?php } ?>
    </ul>
<?php } ?>

<?php if (!$restrictions->hasRestrictions()) { ?>
    <h4><?php zm_l10n("The coupon is valid for all categories and products.") ?></h4>
<?php } ?>

<?php $form->open(FILENAME_DISCOUNT_COUPON, 'action=lookup', false, array('method' => 'get')) ?>
  <fieldset>
    <legend><?php zm_l10n("Look-up another discount coupon ...") ?></legend>
    <label for="lookup_discount_coupon"><?php zm_l10n("Your Code") ?></label>
    <input type="text" id="lookup_discount_coupon" name="lookup_discount_coupon" size="40" value="<?php $html->encode($zm_coupon_code) ?>" />
  </fieldset>
  <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Send") ?>" />
</form>
