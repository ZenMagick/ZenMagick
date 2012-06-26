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

<?php $contact = '<a href="'.$net->url('contact_us') .'">'._zm("let us know").'</a>'; ?>
<p><?php _vzm("If you are having difficulty in locating something on our site, please %s!", $contact) ?></p>
<?php echo $macro->categoryTree($container->get('categoryService')->getCategoryTree($session->getLanguageId()), "catalog"); ?>
<ul>
    <?php if ($session->isRegistered()) { ?>
      <li><a href="<?php echo $net->url('account', '', true) ?>"><?php _vzm("My Account") ?></a>
      <ul>
        <li><a href="<?php echo $net->url('account_edit', '', true); ?>"><?php _vzm("Change Account") ?></a></li>
        <li><a href="<?php echo $net->url('address_book', '', true); ?>"><?php _vzm("My Address Book") ?></a></li>
        <li><a href="<?php echo $net->url('account_password', '', true); ?>"><?php _vzm("Change My Password") ?></a></li>
        <li><a href="<?php echo $net->url('account_history', '', true) ?>"><?php _vzm("Order History") ?></a></li>
        <li><a href="<?php echo $net->url('account_newsletter', '', true); ?>"><?php _vzm("Change Newsletter Subscriptions") ?></a></li>
        <li><a href="<?php echo $net->url('account_notifications', '', true); ?>"><?php _vzm("Change Product Notifications") ?></a></li>
      </ul></li>
        <?php if (!$request->getShoppingCart()->isEmpty()) { ?>
            <li><a href="<?php echo $net->url('shopping_cart', '', true); ?>"><?php _vzm("Shopping cart") ?></a></li>
            <li><a href="<?php echo $net->url('checkout_shipping', '', true); ?>"><?php _vzm("Checkout") ?></a></li>
        <?php } ?>
    <?php } ?>
    <li><a href="<?php echo $net->url('advanced_search'); ?>"><?php _vzm("Advanced Search") ?></a></li>
    <li><a href="<?php echo $net->url('products_new'); ?>"><?php _vzm("New Products") ?></a></li>
    <li><a href="<?php echo $net->url('specials'); ?>"><?php _vzm("Specials") ?></a></li>
    <li><a href="<?php echo $net->url('reviews'); ?>"><?php _vzm("Reviews") ?></a></li>
    <li><?php _vzm("Information") ?>
        <ul>
          <li><a href="<?php echo $net->url('shippinginfo'); ?>"><?php _vzm("Shipping Information") ?></a></li>
          <li><a href="<?php echo $net->url('privacy'); ?>"><?php _vzm("Privacy Policy") ?></a></li>
          <li><a href="<?php echo $net->url('conditions'); ?>"><?php _vzm("Terms &amp; Conditions") ?></a></li>
          <li><a href="<?php echo $net->url('contact_us'); ?>"><?php _vzm("Contact Us") ?></a></li>

      <?php if ($settingsService->get('isEnabledGV')) { ?>
          <li><a href="<?php echo $net->url('gv_faq'); ?>"><?php _vzm("Giftvoucher FAQ") ?></a></li>
      <?php } ?>
      <?php if ($settingsService->get('isEnabledCoupons')) { ?>
          <li><a href="<?php echo $net->url('discount_coupon'); ?>"><?php _vzm("Coupon Lookup") ?></a></li>
      <?php } ?>
      <li><a href="<?php echo $net->url('unsubscribe'); ?>"><?php _vzm("Newsletter Unsubscribe") ?></a></li>
   </ul></li>
</ul>
