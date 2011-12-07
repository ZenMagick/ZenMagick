<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
?><?php _vzm("%s Order Confirmation\n", $settings->get('storeName')) ?>

<?php _vzm("Thanks for shopping at %s,\n", $settings->get('storeName')) ?>
<?php _vzm("The following is a summary of your order.\n") ?>

<?php _vzm("Order Details\n") ?>
-----------------------------------------------
<?php _vzm("Order Number: #%s\n", $order->getId()) ?>
<?php _vzm("Order Date: %s\n", $locale->shortDate($order->getOrderDate())) ?>

<?php _vzm("Ordered Items\n") ?>
-----------------------------------------------
<?php foreach ($order->getOrderItems() as $orderItem) { ?>
<?php printf("%3s x %26s  %7s\n", $orderItem->getQuantity(), $orderItem->getName(), $utils->formatMoney($orderItem->getCalculatedPrice())) ?>
<?php } ?>
<?php printf("%30s  %s\n", "", "-----------") ?>
<?php foreach ($order->getOrderTotalLines() as $orderTotalLine) { ?>
<?php printf("%32s  %7s\n", $orderTotalLine->getName(), $orderTotalLine->getValue()) ?>
<?php } ?>

<?php _vzm("Address Details\n") ?>
-----------------------------------------------
<?php _vzm("Shipping Address\n") ?>
<?php if ($order->isStorePickup() || !$order->hasShippingAddress()) { ?>
<?php _vzm("N/A") ?>
<?php } else { ?>
<?php echo $macro->formatAddress($shippingAddress, false) ?>
<?php } ?>


<?php _vzm("Billing Address\n") ?>
<?php echo $macro->formatAddress($billingAddress, false) ?>


<?php _vzm("Payment Details\n") ?>
-----------------------------------------------
<?php if (null != ($paymentType = $order->getPaymentType())) { ?>
<?php echo $html->encode($paymentType->getName()) ?>
<?php if (!ZMLangUtils::isEmpty($paymentType->getInfo())) { ?>


<?php echo $paymentType->getInfo() ?>
<?php } ?>
<?php } ?>


<?php echo strip_tags($utils->staticPageContent('email_advisory')) ?>
