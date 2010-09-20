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
 *
 * $Id$
 */
?>

<?php $contact = '<a href="'.$net->url(FILENAME_CONTACT_US) .'">'._zm("let us know").'</a>'; ?>
<p><?php _vzm("If you are having difficulty in locating something on our site, please %s!", $contact) ?></p>
<?php echo $macro->categoryTree(ZMCategories::instance()->getCategoryTree($session->getLanguageId()), "catalog"); ?>
<ul>
    <?php if ($request->isRegistered()) { ?>
      <li><a href="<?php echo $net->url(FILENAME_ACCOUNT, '', true) ?>"><?php _vzm("My Account") ?></a>
      <ul>
        <li><a href="<?php echo $net->url(FILENAME_ACCOUNT_EDIT, '', true); ?>"><?php _vzm("Change Account") ?></a></li>
        <li><a href="<?php echo $net->url(FILENAME_ADDRESS_BOOK, '', true); ?>"><?php _vzm("My Address Book") ?></a></li>
        <li><a href="<?php echo $net->url(FILENAME_ACCOUNT_PASSWORD, '', true); ?>"><?php _vzm("Change My Password") ?></a></li>
        <li><a href="<?php echo $net->url(FILENAME_ACCOUNT_HISTORY, '', true) ?>"><?php _vzm("Order History") ?></a></li>
        <li><a href="<?php echo $net->url(FILENAME_ACCOUNT_NEWSLETTERS, '', true); ?>"><?php _vzm("Change Newsletter Subscriptions") ?></a></li>
        <li><a href="<?php echo $net->url(FILENAME_ACCOUNT_NOTIFICATIONS, '', true); ?>"><?php _vzm("Change Product Notifications") ?></a></li>
      </ul></li>
        <?php if (!$request->getShoppingCart()->isEmpty()) { ?>
            <li><a href="<?php echo $net->url(FILENAME_SHOPPING_CART, '', true); ?>"><?php _vzm("Shopping cart") ?></a></li>
            <li><a href="<?php echo $net->url(FILENAME_CHECKOUT_SHIPPING, '', true); ?>"><?php _vzm("Checkout") ?></a></li>
        <?php } ?>
    <?php } ?>
    <li><a href="<?php echo $net->url(FILENAME_ADVANCED_SEARCH); ?>"><?php _vzm("Advanced Search") ?></a></li>
    <li><a href="<?php echo $net->url(FILENAME_PRODUCTS_NEW); ?>"><?php _vzm("New Products") ?></a></li>
    <li><a href="<?php echo $net->url(FILENAME_SPECIALS); ?>"><?php _vzm("Specials") ?></a></li>
    <li><a href="<?php echo $net->url(FILENAME_REVIEWS); ?>"><?php _vzm("Reviews") ?></a></li>
    <li><?php _vzm("Information") ?>
        <ul>
          <li><a href="<?php echo $net->url(FILENAME_SHIPPING); ?>"><?php _vzm("Shipping Information") ?></a></li>
          <li><a href="<?php echo $net->url(FILENAME_PRIVACY); ?>"><?php _vzm("Privacy Policy") ?></a></li>
          <li><a href="<?php echo $net->url(FILENAME_CONDITIONS); ?>"><?php _vzm("Terms &amp; Conditions") ?></a></li>
          <li><a href="<?php echo $net->url(FILENAME_CONTACT_US); ?>"><?php _vzm("Contact Us") ?></a></li>

      <?php if (ZMSettings::get('isEnabledGV')) { ?>
          <li><a href="<?php echo $net->url(FILENAME_GV_FAQ); ?>"><?php _vzm("Giftvoucher FAQ") ?></a></li>
      <?php } ?>
      <?php if (ZMSettings::get('isEnabledCoupons')) { ?>
          <li><a href="<?php echo $net->url(FILENAME_DISCOUNT_COUPON); ?>"><?php _vzm("Coupon Lookup") ?></a></li>
      <?php } ?>
      <li><a href="<?php echo $net->url(FILENAME_UNSUBSCRIBE); ?>"><?php _vzm("Newsletter Unsubscribe") ?></a></li>
   </ul></li>
</ul>
