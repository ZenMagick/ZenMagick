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

use ZenMagick\Base\Toolbox;

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="<?php echo $language->getCode() ?>">
<head>
<title><?php _vzm("%s Order Confirmation", $settingsService->get('storeName')) ?></title>
</head>
<body>
<div style="font-family:Verdana,Arial,Helvetica,sans-serif;font-size:10pt;">
<p><?php _vzm("Thanks for shopping at %s,", $settingsService->get('storeName')) ?></p>
<p><?php _vzm("The following is a summary of your order.") ?></p>

<h3><?php _vzm("Order Details") ?></h3>
<p>
<?php _vzm("Order Number: #%s", $order->getId()) ?><br>
<?php _vzm("Order Date: %s", $locale->shortDate($order->getOrderDate())) ?>
</p>

<h4><?php _vzm("Ordered Items") ?></h4>
<table cellpadding="3" cellspacing="2">
<?php foreach ($order->getOrderItems() as $orderItem) { ?>
<tr>
<td><?php echo $orderItem->getQuantity() ?> x </td>
<td><?php echo $html->encode($orderItem->getName()) ?></td>
<td><?php echo $utils->formatMoney($orderItem->getCalculatedPrice()) ?></td>
</tr>
<?php } ?>
<?php foreach ($order->getOrderTotalLines() as $orderTotalLine) { ?>
<tr>
<td colspan="2"><?php echo $html->encode($orderTotalLine->getName()) ?></td>
<td><?php echo $orderTotalLine->getValue() ?></td>
</tr>
<?php } ?>
</table>

<h3><?php _vzm("Address Details") ?></h3>
<table cellpadding="3" cellspacing="2">
<tr>
<td valign="top">
<h4><?php _vzm("Shipping Address") ?></h4>
<?php if ($order->isStorePickup() || !$order->hasShippingAddress()) { ?>
<?php _vzm("N/A") ?>
<?php } else { ?>
<?php echo $macro->formatAddress($shippingAddress) ?>
<?php } ?>
</td>
<td valign="top">
<h4><?php _vzm("Billing Address") ?></h4>
<?php echo $macro->formatAddress($billingAddress) ?>
</td>
</tr>
</table>

<h3><?php _vzm("Payment Details") ?></h3>
<?php if (null != ($paymentType = $order->getPaymentType())) { ?>
<p><?php echo $paymentType->getName() ?></p>
<?php if (!Toolbox::isEmpty($paymentType->getInfo())) { ?>
<p><?php echo nl2br($paymentType->getInfo()) ?></p>
<?php } ?>
<?php } ?>

<?php echo $utils->staticPageContent('email_advisory') ?>
</div>
</body>
</html>
