<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
<?php $view->extend('AdminBundle::default_layout.html.php'); ?>
<?php $admin->title(sprintf(_zm('Order: %s'), $order->getId())) ?>

<h3><?php _vzm("Item Details") ?></h3>
<table class="grid">
    <tbody>
    <?php foreach ($order->getOrderItems() as $orderItem) { ?>
        <tr>
            <td class="qty"><?php echo $orderItem->getQuantity() ?> x </td>
            <td class="itm">
                <?php echo $view->escape($orderItem->getName()) ?>
                <?php if ($orderItem->hasAttributes()) { ?>
                    <br/>
                    <?php foreach ($orderItem->getAttributes() as $attribute) { ?>
                        <p><span class="attr"><?php echo $view->escape($attribute->getName()) ?>:</span>
                        <?php $first = true; foreach ($attribute->getValues() as $attributeValue) { ?>
                            <?php if (!$first) { ?>, <?php } ?>
                            <span class="atval"><?php echo $view->escape($attributeValue->getName()) ?></span>
                        <?php $first = false; } ?>
                        </p>
                    <?php } ?>
                <?php } ?>
            </td>
            <td class="price"><?php echo $utils->formatMoney($orderItem->getCalculatedPrice()) ?></td>
        </tr>
    <?php } ?>
    <?php foreach ($order->getOrderTotalLines() as $orderTotalLine) { ?>
        <tr>
            <td colspan="2" class="total"><?php echo $view->escape($orderTotalLine->getName()) ?></td>
            <td class="price"><?php echo $orderTotalLine->getValue() ?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<h3><?php _vzm("Order History") ?></h3>
<table cellpadding="0" cellspacing="0">
    <tbody>
    <?php foreach ($order->getOrderStatusHistory($view['request']->getLocaleId()) as $orderStatus) { ?>
        <tr>
            <td><?php echo $locale->shortDate($orderStatus->getDateAdded()) ?></td>
            <td><?php echo $view->escape($orderStatus->getName()) ?></td>
            <td class="cmt"><?php echo $view->escape($orderStatus->getComment()) ?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<h3><?php _vzm("Address Details") ?></h3>
<div id="addr">
    <div id="daddr">
        <h4><?php _vzm("Shipping Address") ?></h4>
        <?php if (!$order->hasShippingAddress()) { ?>
            <?php _vzm("N/A") ?>
        <?php } else { ?>
            <?php echo $macro->formatAddress($order->getShippingaddress()) ?>
        <?php } ?>
    </div>
    <div id="baddr">
        <h4><?php _vzm("Billing Address") ?></h4>
        <?php echo $macro->formatAddress($order->getBillingAddress()) ?>
    </div>
</div>
