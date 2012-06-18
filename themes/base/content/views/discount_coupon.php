<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

<?php $crumbtrail->addCrumb(_zm('Discount Coupon')) ?>
<?php echo $utils->staticPageContent("discount_coupon") ?>

<?php echo $form->open('discount_coupon', '', false, array('method' => 'get')) ?>
  <fieldset>
    <legend><?php _vzm("Look-up Discount Coupon ...") ?></legend>
    <label for="couponCode"><?php _vzm("Your Code") ?></label>
    <input type="text" id="couponCode" name="couponCode" size="40" value="<?php echo $html->encode($currentCoupon) ?>" />
  </fieldset>
  <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Send") ?>" />
</form>
