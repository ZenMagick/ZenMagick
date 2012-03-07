<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
?>

<fieldset>
    <legend><?php _vzm("My Account") ?></legend>
    <ul>
        <li><a href="<?php echo $net->url('account_edit', '', true); ?>"><?php _vzm("Change Account") ?></a></li>
        <li><a href="<?php echo $net->url('address_book', '', true); ?>"><?php _vzm("My Address Book") ?></a></li>
        <li><a href="<?php echo $net->url('account_password', '', true); ?>"><?php _vzm("Change My Password") ?></a></li>
    </ul>
</fieldset>

<fieldset>
    <legend><?php _vzm("Email Settings") ?></legend>
    <ul>
        <li><a href="<?php echo $net->url('account_newsletters', '', true); ?>"><?php _vzm("Change Newsletter Subscriptions") ?></a></li>
        <li><a href="<?php echo $net->url('account_notifications', '', true); ?>"><?php _vzm("Change Product Notifications") ?></a></li>
    </ul>
</fieldset>

<?php $voucherBalance = $request->getAccount()->getVoucherBalance(); ?>
<?php if (0 < $voucherBalance) { ?>
    <fieldset>
        <legend><?php _vzm("Gift Certificate Account") ?></legend>
        <div class="btn"><a href="<?php echo $net->url('gv_send') ?>" class="btn"><?php _vzm("Send Gift Certificate") ?></a></div>
        <p><?php _vzm("You have funds (%s) in your Gift Certificate Account.", $utils->formatMoney($voucherBalance)) ?></p>
    </fieldset>
<?php } ?>

<?php if ($resultList->hasResults()) { ?>
    <?php $resultList->setPagination(3); ?>
    <h3>
        <?php if (3 < $resultList->getNumberOfResults()) { ?>
            <a href="<?php echo $net->url('account_history', '', true) ?>"><?php _vzm("(Show All)") ?></a>
        <?php } ?>
        <?php _vzm("Previous Orders") ?>
    </h3>
    <div class="rlist">
        <table cellspacing="0" cellpadding="0"><tbody>
            <?php $first = true; $odd = true; foreach ($resultList->getResults() as $order) { ?>
              <?php echo $this->fetch('views/resultlist/order.php', array('order' => $order, 'first' => $first, 'odd' => $odd)) ?>
            <?php $first = false; $odd = !$odd; } ?>
        </tbody></table>
    </div>
<?php } ?>
