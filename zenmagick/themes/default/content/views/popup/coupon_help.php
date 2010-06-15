<?php
/*
 * ZenMagick - Extensions for zen-cart
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
 *
 * $Id$
 */
?>

<?php
  $coupon = ZMCoupons::instance()->getCouponForId($request->getParameter('cID'), $session->getLanguageId());
  $restrictions = $coupon->getRestrictions();
  $fixed = _zm('This coupon entitles you to a %s discount against your order');
  if (ZMCoupons::TYPPE_FIXED == $coupon->getType()) {
      $discount = sprintf($fixed, $utils->formatMoney($coupon->getAmount()));
  } else if (ZMCoupons::TYPPE_PERCENT == $coupon->getType()) {
      $discount = sprintf($fixed, number_format($coupon->getAmount(), ZMSettings::get('discountDecimals')).'%');
  } else {
      $discount = _zm('This coupon gives you free shipping on your order');
  }

?>
<h2><?php _vzm("Congratulations, you have redeemed a Discount Coupon.") ?></h2>
<p>
  <?php _vzm("Coupon Name: %s", $coupon->getName()) ?><br/>
  <?php _vzm("Coupon Description: %s", $html->encode($coupon->getDescription())) ?><br/>
  <?php echo $discount ?>
</p>

<p><?php _vzm("The discount is valid between %s and %s.", $locale->shortDate($coupon->getStartDate()), $locale->shortDate($coupon->getExpiryDate())) ?></p>

<?php if ($restrictions->hasCategories()) { ?>
    <h4><?php _vzm("Category Restrictions")?></h4>
    <ul>
    <?php foreach ($restrictions->getCategories() as $rest) { $category = $rest->getCategory(); ?>
        <li><?php echo $html->encode($category->getName()) ?> - <?php echo ($rest->isAllowed() ? _zm(" included") : _zm(" excluded")) ?></li>
    <?php } ?>
    </ul>
<?php } ?>

<?php if ($restrictions->hasProducts()) { ?>
    <h4><?php _vzm("Product Restrictions")?></h4>
    <ul>
    <?php foreach ($restrictions->getProducts() as $rest) { $product = $rest->getProduct(); ?>
        <li><?php echo $html->encode($product->getName()) ?> - <?php echo ($rest->isAllowed() ? _zm(" included") : _zm(" excluded")) ?></li>
    <?php } ?>
    </ul>
<?php } ?>

<?php if (!$restrictions->hasRestrictions()) { ?>
    <h4><?php _vzm("The coupon is valid for all categories and products.") ?></h4>
<?php } ?>

<p>Discount Coupons may not be applied towards the purchase of Gift Certificates.</p>

<div id="close"><a href="#" onclick="javascript:window.close()"><?php _vzm("Close Window [x]") ?></a></div>
