<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
?><?php
  /**
   * ZenMagick implementation of the order confirmation email.
   * The original zen-cart template values are in $zc_args under
   * their respective names; eg. $zc_args['INTRO_ORDER_NUMBER'] contains
   * the order number.
   *
   * This version uses only INTRO_ORDER_NUMBER and PAYMENT_METHOD_FOOTER as
   * there is currently no way to accessing this information otherwise.
   */

$order = $zm_orders->getOrderForId($zc_args['INTRO_ORDER_NUMBER']);
$shippingAddress = $order->getShippingAddress();
$billingAddress = $order->getBillingAddress();
$paymentType = $order->getPaymentType();
$language = $zm_runtime->getLanguage();
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="<?php echo $language->getCode() ?>">
<head>
<title><?php zm_l10n("%s Order Confirmation", zm_setting('storeName')) ?></title>
</head>
<body>
<div style="font-family:Verdana,Arial,Helvetica,sans-serif;font-size:10pt;">
<p><?php zm_l10n("Thanks for shopping at %s,", zm_setting('storeName')) ?></p>
<p><?php zm_l10n("The following is a summary of your order.") ?></p>

<h3><?php zm_l10n("Order Details") ?></h3>
<p>
<?php zm_l10n("Order Number: #%s", $order->getId()) ?><br>
<?php zm_l10n("Order Date: %s", zm_date_short($order->getOrderDate(), false)) ?>
</p>

<h4><?php zm_l10n("Ordered Items") ?></h4>
<table cellpadding="3" cellspacing="2">
<?php foreach ($order->getOrderItems() as $orderItem) { ?>
<tr>
<td><?php echo $orderItem->getQty() ?> x </td>
<td><?php echo $orderItem->getName() ?></td>
<td><?php zm_format_currency($orderItem->getCalculatedPrice()) ?></td>
</tr>
<?php } ?>
<?php foreach ($order->getOrderTotals() as $orderTotal) { ?>
<tr>
<td colspan="2"><?php echo $orderTotal->getName() ?></td>
<td><?php echo $orderTotal->getValue() ?></td>
</tr>
<?php } ?>
</table>

<h3><?php zm_l10n("Address Details") ?></h3>
<table cellpadding="3" cellspacing="2">
<tr>
<td valign="top">
<h4><?php zm_l10n("Shipping Address") ?></h4>
<?php if ($order->isStorePickup()) { ?>
<?php zm_l10n("N/A") ?>
<?php } else { ?>
<?php zm_format_address($shippingAddress) ?>
<?php } ?>
</td>
<td valign="top">
<h4><?php zm_l10n("Billing Address") ?></h4>
<?php zm_format_address($billingAddress) ?>
</td>
</tr>
</table>

<h3><?php zm_l10n("Payment Details") ?></h3>
<?php $paymentType = $order->getPaymentType(); ?>
<p><?php echo $paymentType->getName() ?></p>
<?php if (!zm_is_empty($zc_args['PAYMENT_METHOD_FOOTER'])) { ?>
<p><?php echo $zc_args['PAYMENT_METHOD_FOOTER'] ?></p>
<?php } ?>

<?php echo zm_l10n_chunk_get('email_advisory', zm_setting('storeEmail')) ?>
</div>
</body>
</html>
