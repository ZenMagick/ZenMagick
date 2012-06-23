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

<tr class="<?php echo ($odd?"odd":"even").($first?" first":" other") ?>">
    <td>
        <a href="<?php echo $net->url('account_history_info', 'order_id='.$order->getId(), true) ?>"><?php _vzm("Order #%s", $order->getId()) ?></a>
    </td>
    <td><?php echo $locale->shortDate($order->getOrderDate()) ?></td>
    <?php $address = $order->getBillingAddress(); ?>
    <td><?php echo $html->encode($address->getFullName()) ?></td>
    <td><?php _vzm($order->getStatusName()) ?></td>
    <td class="pprice"><?php echo $utils->formatMoney($order->getTotal()) ?></td>
</tr>
