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
<?php $admin->title() ?>
<h2><?php _vzm('All Subscriptions') ?></h2>

<?php $resultList->setPagination(10); ?>
<?php if (1 < $resultList->getNumberOfPages()) { ?>
    <div class="rnav">
        <span class="pno"><?php _vzm("Page %s/%s", $resultList->getPageNumber(), $resultList->getNumberOfPages()) ?></span>
        <?php if ($resultList->hasPreviousPage()) { ?>
            <a href="<?php echo $net->resultListBack($resultList) ?>"><?php _vzm("Previous") ?></a>&nbsp;
        <?php } else { ?>
            <span class="nin"><?php _vzm("Previous") ?></span>&nbsp;
        <?php } ?>
        <?php if ($resultList->hasNextPage()) { ?>
            <a href="<?php echo $net->resultListNext($resultList) ?>"><?php _vzm("Next") ?></a>
        <?php } else { ?>
            <span class="nin"><?php _vzm("Next") ?></span>
        <?php } ?>
    </div>
<?php } ?>

<?php $schedules = $plugin->getSchedules(); ?>

<div class="rlist">
    <table cellspacing="0" cellpadding="0" border="1"><thead>
        <tr>
            <th>Order</th>
            <th>Frequency</th>
            <th>Next Order</th>
            <th>Earliest Cancel Date</th>
            <th>Status</th>
            <th>Options</th>
        </tr>
    </thead><tbody>
        <?php $first = true; $odd = true; foreach ($resultList->getResults() as $order) { ?>
            <tr class="<?php echo ($odd?"odd":"even").($first?" first":" other") ?>">
                <td>
                    <a href="<?php echo $net->generate('zc_admin_orders', array('action' => 'edit', 'oID' => $order->getId())) ?>"><?php _vzm("Order #%s", $order->getId()) ?></a>
                </td>
                <td><?php echo $schedules[$order->getSchedule()]['name'] ?></td>
                <td><?php echo $locale->shortDate($order->getNextOrder()) ?></td>
                <td><?php echo $locale->shortDate($plugin->getMinLastOrderDate($order->getId())) ?></td>
                <td style="text-align:center;"><img src="images/icons/<?php echo ($order->isSubscriptionCanceled() ? 'cross.gif' : 'tick.gif') ?>" alt="tick"></td>
                <td>
                    <?php if (!$order->isSubscriptionCanceled()) { ?>
                        <form action="<?php echo $net->generate('cancel_subscription') ?>" method="POST">
                            <input type="hidden" name="fkt" value="subscription_admin">
                            <input type="hidden" name="orderId" value="<?php echo $order->getId() ?>">
                            <input type="submit" name="cancel" value="cancel">
                            <input type="checkbox" id="hard_<?php echo $order->getId() ?>" name="hard" value="1"><label for="hard_<?php echo $order->getId() ?>">Hard cancel</label>
                        </form>
                    <?php } else { ?>
                        &nbsp;
                    <?php } ?>
                </td>
            </tr>
        <?php $first = false; $odd = !$odd; } ?>
    </tbody></table>
</div>
