<?php
/*
 * ZenMagick - Extensions for zen-cart
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
?><?php zm_l10n("%s Order Confirmation\n", ZMSettings::get('storeName')) ?>

<?php zm_l10n("Thanks for shopping at %s,\n", ZMSettings::get('storeName')) ?>
<?php zm_l10n("The following is a summary of your order.\n") ?>

<?php zm_l10n("Order Details\n") ?>
-----------------------------------------------
<?php zm_l10n("Order Number: #%s\n", $order->getId()) ?>
<?php zm_l10n("Order Date: %s\n", $locale->shortDate($order->getOrderDate())) ?>

<?php zm_l10n("Ordered Items\n") ?>
-----------------------------------------------
<?php foreach ($order->getOrderItems() as $orderItem) { ?>
<?php printf("%3s x %26s  %7s\n", $orderItem->getQty(), $orderItem->getName(), $utils->formatMoney($orderItem->getCalculatedPrice())) ?>
<?php } ?>
<?php printf("%30s  %s\n", "", "-----------") ?>
<?php foreach ($order->getOrderTotalLines() as $orderTotalLine) { ?>
<?php printf("%32s  %7s\n", $orderTotalLine->getName(), $orderTotalLine->getValue()) ?>
<?php } ?>

<?php zm_l10n("Address Details\n") ?>
-----------------------------------------------
<?php zm_l10n("Shipping Address\n") ?>
<?php if ($order->isStorePickup() || !$order->hasShippingAddress()) { ?>
<?php zm_l10n("N/A") ?>
<?php } else { ?>
<?php echo $macro->formatAddress($shippingAddress, false) ?>
<?php } ?>


<?php zm_l10n("Billing Address\n") ?>
<?php echo $macro->formatAddress($billingAddress, false) ?>


<?php zm_l10n("Payment Details\n") ?>
-----------------------------------------------
<?php $paymentType = $order->getPaymentType(); ?>
<?php echo $html->encode($paymentType->getName()) ?>
<?php if (!empty($PAYMENT_METHOD_FOOTER)) { ?>


<?php echo $PAYMENT_METHOD_FOOTER ?>
<?php } ?>


<?php echo strip_tags($utils->staticPageContent('email_advisory')) ?>
