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
?><?php _vzm("Dear %s %s,", $currentAccount->getFirstName(), $currentAccount->getLastName()) ?>


<?php _vzm("We're pleased to offer you a Store Coupon for our online store at %s", $settingsService->get('storeName')) ?>


<?php _vzm('You can redeem this coupon during checkout. Just enter the code provided, and click on the redeem button.') ?>


<?php _vzm('The coupon code is %s', $currentCoupon->getCode()) ?>


<?php _vzm('The coupon is valid between %s and %s', $locale->shortDate($currentCoupon->getStartDate()), $locale->shortDate($currentCoupon->getExpiryDate())) ?>


<?php _vzm("Don't lose the coupon code, make sure to keep the code safe so you can benefit from this special offer.") ?>


<?php _vzm('Thank you for shopping with us!') ?>

<?php _vzm("Sincerely, %s", $settingsService->get('storeOwner')) ?>


<?php echo strip_tags($utils->staticPageContent('email_advisory')) ?>
<?php echo $office_only_text; ?>
