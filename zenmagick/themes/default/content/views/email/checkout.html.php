<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="<?php echo $language->getCode() ?>">
<head>
<title><?php zm_l10n("%s Order Confirmation", ZMSettings::get('storeName')) ?></title>
</head>
<body>
<div style="font-family:Verdana,Arial,Helvetica,sans-serif;font-size:10pt;">
<p><?php zm_l10n("Thanks for shopping at %s,", ZMSettings::get('storeName')) ?></p>
<p><?php zm_l10n("The following is a summary of your order.") ?></p>

<h3><?php zm_l10n("Order Details") ?></h3>
<p>
<?php zm_l10n("Order Number: #%s", $order->getId()) ?><br>
<?php zm_l10n("Order Date: %s", $locale->shortDate($order->getOrderDate(), false)) ?>
</p>

<h4><?php zm_l10n("Ordered Items") ?></h4>
<table cellpadding="3" cellspacing="2">
<?php foreach ($order->getOrderItems() as $orderItem) { ?>
<tr>
<td><?php echo $orderItem->getQty() ?> x </td>
<td><?php $html->encode($orderItem->getName()) ?></td>
<td><?php $utils->formatMoney($orderItem->getCalculatedPrice()) ?></td>
</tr>
<?php } ?>
<?php foreach ($order->getOrderTotals() as $orderTotal) { ?>
<tr>
<td colspan="2"><?php $html->encode($orderTotal->getName()) ?></td>
<td><?php echo $orderTotal->getValue() ?></td>
</tr>
<?php } ?>
</table>

<h3><?php zm_l10n("Address Details") ?></h3>
<table cellpadding="3" cellspacing="2">
<tr>
<td valign="top">
<h4><?php zm_l10n("Shipping Address") ?></h4>
<?php if ($order->isStorePickup() || !$order->hasShippingAddress()) { ?>
<?php zm_l10n("N/A") ?>
<?php } else { ?>
<?php $macro->formatAddress($shippingAddress) ?>
<?php } ?>
</td>
<td valign="top">
<h4><?php zm_l10n("Billing Address") ?></h4>
<?php $macro->formatAddress($billingAddress) ?>
</td>
</tr>
</table>

<h3><?php zm_l10n("Payment Details") ?></h3>
<?php $paymentType = $order->getPaymentType(); ?>
<p><?php $html->encode($paymentType->getName()) ?></p>
<?php if (!empty($PAYMENT_METHOD_FOOTER)) { ?>
<p><?php echo $PAYMENT_METHOD_FOOTER ?></p>
<?php } ?>

<?php echo zm_l10n_chunk_get('email_advisory', ZMSettings::get('storeEmail')) ?>
</div>
</body>
</html>
