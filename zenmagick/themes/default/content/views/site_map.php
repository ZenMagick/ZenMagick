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

<?php $contact = '<a href="'.$net->url(FILENAME_CONTACT_US, '', false, false) .'">'.zm_l10n_get("let us know").'</a>'; ?>
<p><?php zm_l10n("If you are having difficulty in locating something on our site, please %s!", $contact) ?></p>
<?php echo $macro->categoryTree(ZMCategories::instance()->getCategoryTree($session->getLanguageId()), "catalog"); ?>
<ul>
    <?php if ($request->isRegistered()) { ?>
      <li><a href="<?php $net->url(FILENAME_ACCOUNT, '', true) ?>"><?php zm_l10n("My Account") ?></a>
      <ul>
        <li><a href="<?php $net->url(FILENAME_ACCOUNT_EDIT, '', true); ?>"><?php zm_l10n("Change Account") ?></a></li>
        <li><a href="<?php $net->url(FILENAME_ADDRESS_BOOK, '', true); ?>"><?php zm_l10n("My Address Book") ?></a></li>
        <li><a href="<?php $net->url(FILENAME_ACCOUNT_PASSWORD, '', true); ?>"><?php zm_l10n("Change My Password") ?></a></li>
        <li><a href="<?php $net->url(FILENAME_ACCOUNT_HISTORY, '', true) ?>"><?php zm_l10n("Order History") ?></a></li>
        <li><a href="<?php $net->url(FILENAME_ACCOUNT_NEWSLETTERS, '', true); ?>"><?php zm_l10n("Change Newsletter Subscriptions") ?></a></li>
        <li><a href="<?php $net->url(FILENAME_ACCOUNT_NOTIFICATIONS, '', true); ?>"><?php zm_l10n("Change Product Notifications") ?></a></li>
      </ul></li>
        <?php if (!$request->getShoppingCart()->isEmpty()) { ?>
            <li><a href="<?php $net->url(FILENAME_SHOPPING_CART, '', true); ?>"><?php zm_l10n("Shopping cart") ?></a></li>
            <li><a href="<?php $net->url(FILENAME_CHECKOUT_SHIPPING, '', true); ?>"><?php zm_l10n("Checkout") ?></a></li>
        <?php } ?>
    <?php } ?>
    <li><a href="<?php $net->url(FILENAME_ADVANCED_SEARCH); ?>"><?php zm_l10n("Advanced Search") ?></a></li>
    <li><a href="<?php $net->url(FILENAME_PRODUCTS_NEW); ?>"><?php zm_l10n("New Products") ?></a></li>
    <li><a href="<?php $net->url(FILENAME_SPECIALS); ?>"><?php zm_l10n("Specials") ?></a></li>
    <li><a href="<?php $net->url(FILENAME_REVIEWS); ?>"><?php zm_l10n("Reviews") ?></a></li>
    <li><?php zm_l10n("Information") ?>
        <ul>
          <li><a href="<?php $net->url(FILENAME_SHIPPING); ?>"><?php zm_l10n("Shipping Information") ?></a></li>
          <li><a href="<?php $net->url(FILENAME_PRIVACY); ?>"><?php zm_l10n("Privacy Policy") ?></a></li>
          <li><a href="<?php $net->url(FILENAME_CONDITIONS); ?>"><?php zm_l10n("Terms &amp; Conditions") ?></a></li>
          <li><a href="<?php $net->url(FILENAME_CONTACT_US); ?>"><?php zm_l10n("Contact Us") ?></a></li>

      <?php if (ZMSettings::get('isEnabledGV')) { ?>
          <li><a href="<?php $net->url(FILENAME_GV_FAQ); ?>"><?php zm_l10n("Giftvoucher FAQ") ?></a></li>
      <?php } ?>
      <?php if (ZMSettings::get('isEnabledCoupons')) { ?>
          <li><a href="<?php $net->url(FILENAME_DISCOUNT_COUPON); ?>"><?php zm_l10n("Coupon Lookup") ?></a></li>
      <?php } ?>
      <li><a href="<?php $net->url(FILENAME_UNSUBSCRIBE); ?>"><?php zm_l10n("Newsletter Unsubscribe") ?></a></li>
   </ul></li>
</ul>
