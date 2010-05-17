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
 * $Id: zmCacheAdmin.php 2647 2009-11-27 00:30:20Z dermanomann $
 */
?>
<h1><?php zm_l10n("%s Orders", (null != $orderStatus ? $orderStatus->getName() : '')) ?></h1>

<table>
  <tr>
    <th><?php zm_l10n('ID') ?></th>
    <th><?php zm_l10n('Account') ?></th>
    <th><?php zm_l10n('Order Date') ?></th>
    <th><?php zm_l10n('Payment') ?></th>
    <th><?php zm_l10n('Shipping') ?></th>
    <th><?php zm_l10n('Status') ?></th>
    <th><?php zm_l10n('Total') ?></th>
  </tr>
  <?php foreach ($resultList->getResults() as $order) { ?>
    <tr>
      <td><?php echo $order->getId() ?></td>
      <?php $actualAccount = ZMAccounts::instance()->getAccountForId($order->getAccountId()); ?>
      <?php $name = $actualAccount->getType() == ZMAccount::REGISTERED ? $order->getAccount()->getFullName() : zm_l10n_get('** Guest **'); ?>
      <td><a href="<?php echo $admin2->url('account', 'accountId='.$order->getAccountId()) ?>"><?php echo $name ?></a></td>
      <td><?php echo $order->getOrderDate() ?></td>
      <td><?php echo $order->get('payment_method') ?></td>
      <td><?php echo $order->get('shipping_method') ?></td>
      <td><?php echo $order->getStatusName() ?></td>
      <td><?php echo $utils->formatMoney($order->getTotal()) ?></td>
    </tr>
  <?php } ?>
</table>
<?php if (1 < $resultList->getNumberOfPages()) { ?>
    <div class="rnav">
        <span class="pno"><?php zm_l10n("Page %s/%s", $resultList->getPageNumber(), $resultList->getNumberOfPages()) ?></span>
        <?php if ($resultList->hasPreviousPage()) { ?>
            <a href="<?php echo $net->resultListBack($resultList, null, array('orderStatusId')) ?>"><?php zm_l10n("Previous") ?></a>&nbsp;
        <?php } else { ?>
            <span class="nin"><?php zm_l10n("Previous") ?></span>&nbsp;
        <?php } ?>
        <?php if ($resultList->hasNextPage()) { ?>
            <a href="<?php echo $net->resultListNext($resultList, null, array('orderStatusId')) ?>"><?php zm_l10n("Next") ?></a>
        <?php } else { ?>
            <span class="nin"><?php zm_l10n("Next") ?></span>
        <?php } ?>
    </div>
<?php } ?>
