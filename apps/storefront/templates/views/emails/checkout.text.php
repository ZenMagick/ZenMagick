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

use ZenMagick\base\Toolbox;

?><?php _vzm("%s Order Confirmation", $settingsService->get('storeName')) ?>


<?php _vzm("Thanks for shopping at %s,", $settingsService->get('storeName')) ?>

<?php _vzm("The following is a summary of your order.") ?>


<?php _vzm("Order Details") ?>

-----------------------------------------------
<?php _vzm("Order Number: #%s", $order->getId()) ?>

<?php _vzm("Order Date: %s", $locale->shortDate($order->getOrderDate())) ?>


<?php _vzm("Ordered Items") ?>

-----------------------------------------------
<?php foreach ($order->getOrderItems() as $orderItem) { ?>
<?php printf("%3s x %26s  %7s", $orderItem->getQuantity(), $orderItem->getName(), $utils->formatMoney($orderItem->getCalculatedPrice())) ?>

<?php } ?>

<?php printf("%30s  %s", "", "-----------") ?>

<?php foreach ($order->getOrderTotalLines() as $orderTotalLine) { ?>
<?php printf("%32s  %7s", $orderTotalLine->getName(), $orderTotalLine->getValue()) ?>

<?php } ?>


<?php _vzm("Address Details") ?>

-----------------------------------------------

<?php _vzm("Shipping Address") ?>
<?php if ($order->isStorePickup() || !$order->hasShippingAddress()) { ?>
<?php _vzm("N/A") ?>
<?php } else { ?>
<?php echo $macro->formatAddress($shippingAddress, false) ?>
<?php } ?>


<?php _vzm("Billing Address") ?>

<?php echo $macro->formatAddress($billingAddress, false) ?>


<?php _vzm("Payment Details") ?>

-----------------------------------------------

<?php if (null != ($paymentType = $order->getPaymentType())) { ?>
<?php echo $html->encode($paymentType->getName()) ?>
<?php if (!Toolbox::isEmpty($paymentType->getInfo())) { ?>


<?php echo $paymentType->getInfo() ?>
<?php } ?>
<?php } ?>


<?php echo strip_tags($utils->staticPageContent('email_advisory')) ?>
