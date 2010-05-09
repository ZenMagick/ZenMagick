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
?>

<h3><?php zm_l10n("Item Details") ?></h3>
<table cellpadding="0" cellspacing="0">
    <tbody>
    <?php foreach ($currentOrder->getOrderItems() as $orderItem) { ?>
        <tr>
            <td class="qty"><?php echo $orderItem->getQty() ?> x </td>
            <td class="itm">
                <?php echo $html->encode($orderItem->getName()) ?>
                <?php if ($orderItem->hasAttributes()) { ?>
                    <br/>
                    <?php foreach ($orderItem->getAttributes() as $attribute) { ?>
                        <p><span class="attr"><?php echo $html->encode($attribute->getName()) ?>:</span>
                        <?php $first = true; foreach ($attribute->getValues() as $attributeValue) { ?>
                            <?php if (!$first) { ?>, <?php } ?>
                            <span class="atval"><?php echo $html->encode($attributeValue->getName()) ?></span>
                        <?php $first = false; } ?>
                        </p>
                    <?php } ?>
                <?php } ?>
            </td>
            <td class="price"><?php echo $utils->formatMoney($orderItem->getCalculatedPrice()) ?></td>
        </tr>
    <?php } ?>
    <?php foreach ($currentOrder->getOrderTotalLines() as $orderTotalLine) { ?>
        <tr>
            <td colspan="2" class="total"><?php echo $html->encode($orderTotalLine->getName()) ?></td>
            <td class="price"><?php echo $orderTotalLine->getValue() ?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<h3><?php zm_l10n("Order History") ?></h3>
<table cellpadding="0" cellspacing="0">
    <tbody>
    <?php foreach ($currentOrder->getOrderStatusHistory($session->getLanguageId()) as $orderStatus) { ?>
        <tr>
            <td><?php echo $locale->shortDate($orderStatus->getDateAdded()) ?></td>
            <td><?php echo $html->encode($orderStatus->getName()) ?></td>
            <td class="cmt"><?php echo $html->encode($orderStatus->getComment()) ?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<h3><?php zm_l10n("Address Details") ?></h3>
<div id="addr">
    <div id="daddr">
        <h4><?php zm_l10n("Shipping Address") ?></h4>
        <?php if (!$currentOrder->hasShippingAddress()) { ?>
            <?php zm_l10n("N/A") ?>
        <?php } else { ?>
            <?php echo $macro->formatAddress($currentOrder->getShippingaddress()) ?>
        <?php } ?>
    </div>
    <div id="baddr">
        <h4><?php zm_l10n("Billing Address") ?></h4>
        <?php echo $macro->formatAddress($currentOrder->getBillingAddress()) ?>
    </div>
</div>
