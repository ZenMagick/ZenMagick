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

<?php
    $orders = ZMOrders::instance()->getOrdersForAccountId($request->getAccountId(), $session->getLanguageId(), 7);
    $products = array();
?>
<?php if (0 < count($orders)) { ?>
    <h3><?php _vzm("Previous Purchases") ?></h3>
    <div id="sb_order_history" class="box">
      <ul>
      <?php foreach ($orders as $order) {
          foreach ($order->getOrderItems() as $orderItem) {
              if (array_key_exists($orderItem->getName(), $products))
                  continue;
              $products[$orderItem->getName()] = $orderItem->getProductId();
              ?><li><a href="<?php echo $net->product($orderItem->getProductId()) ?>"><?php echo $html->encode($orderItem->getName()) ?></a></li><?php
              if (7 == count($products))
                  break;
          }
          if (7 == count($products))
              break;
      } ?>
      </ul>
    </div>
<?php } ?>
