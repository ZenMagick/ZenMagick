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
?>

<tr class="<?php echo ($odd?"odd":"even").($first?" first":" other") ?>">
    <td><a href="<?php $net->url(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id='.$order->getId(), '', true) ?>"><?php zm_l10n("Order #%s", $order->getId()) ?></a></td>
    <td><?php $locale->shortDate($order->getOrderDate()) ?></td>
    <?php $address = $order->getBillingAddress(); ?>
    <td><?php $html->encode($address->getFullName()) ?></td>
    <td><?php zm_l10n($order->getStatusName()) ?></td>
    <td class="pprice"><?php $utils->formatMoney($order->getTotal()) ?></td>
</tr>
