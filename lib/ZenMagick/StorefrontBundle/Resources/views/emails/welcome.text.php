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
 */ _vzm("Dear %s %s,", $currentAccount->getFirstName(), $currentAccount->getLastName()) ?>


<?php _vzm("We wish to welcome you to %s.", $settingsService->get('storeName')) ?>


<?php echo strip_tags($utils->staticPageContent('email_welcome')) ?>
<?php if ($newAccountDiscountCoupon) { ?>
--------------------
<?php echo _zm('Congratulations! To make your next visit to our online shop a more rewarding experience, listed below are details for a Discount Coupon created just for you!') ?>

<?php _vzm('The coupon is valid between %s and %s', $locale->shortDate($newAccountDiscountCoupon->getStartDate()), $locale->shortDate($newAccountDiscountCoupon->getExpiryDate())) ?>


<?php _vzm('To use the Discount Coupon, enter the Redemption Code code during checkout: %s', $newAccountDiscountCoupon->getCode()) ?>

<?php } ?>

<?php if ($newAccountDiscountCoupon) { ?>
--------------------
<?php _vzm("Just for stopping by today, we have sent you a Gift Certificate for %s!\nThe Gift Certificate Redemption Code is: %s", $utils->formatMoney($newAccountGVAmountCoupon->getAmount()), $newAccountGVAmountCoupon->getCode()) ?>


<?php _vzm('You can enter the Redemption Code during Checkout, after making your selections in the store. Or, you may redeem it now by following this link: %s', $net->generate('gv_redeem', array('gv_no' => $newAccountGVAmountCoupon->getCode()), true)) ?>


<?php echo _zm('Once you have added the Gift Certificate to your account, you may use the Gift Certificate for yourself, or send it to a friend!') ?>

<?php } ?>

<?php _vzm("Sincerely, %s", $settingsService->get('storeOwner')) ?>


<?php echo strip_tags($utils->staticPageContent('email_advisory')) ?>
<?php echo $office_only_text; ?>
