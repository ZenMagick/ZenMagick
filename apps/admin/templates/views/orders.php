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
 */ $admin->title(sprintf(_zm("%s Orders"), (null != $orderStatus ? $orderStatus->getName() : ''))) ?></h1>

<table class="grid">
  <tr>
    <th><?php _vzm('ID') ?></th>
    <th><?php _vzm('Account') ?></th>
    <th><?php _vzm('Order Date') ?></th>
    <th><?php _vzm('Payment') ?></th>
    <th><?php _vzm('Shipping') ?></th>
    <th><?php _vzm('Status') ?></th>
    <th><?php _vzm('Total') ?></th>
  </tr>
  <?php foreach ($resultList->getResults() as $order) { ?>
    <tr>
      <td><a href="<?php echo $admin->url('order', 'orderId='.$order->getId()) ?>"><?php echo $order->getId() ?></a></td>
      <?php $actualAccount = $container->get('accountService')->getAccountForId($order->getAccountId()); ?>
      <?php $name = $actualAccount->getType() == ZMAccount::REGISTERED ? $order->getAccount()->getFullName() : _zm('** Guest **'); ?>
      <td><a href="<?php echo $admin->url('account', 'accountId='.$order->getAccountId()) ?>"><?php echo $name ?></a></td>
      <td><?php echo $order->getOrderDate() ?></td>
      <td><?php echo $order->get('payment_method') ?></td>
      <td><?php echo $order->get('shipping_method') ?></td>
      <td><?php echo $order->getStatusName() ?></td>
      <td><?php echo $utils->formatMoney($order->getTotal()) ?></td>
    </tr>
  <?php } ?>
</table>
<?php echo $this->fetch('pagination.php'); ?>
