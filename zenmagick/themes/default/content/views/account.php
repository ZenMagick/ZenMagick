<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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

<fieldset>
    <legend><?php zm_l10n("My Account") ?></legend>
    <ul>
        <li><a href="<?php $net->url(FILENAME_ACCOUNT_EDIT, '', true); ?>"><?php zm_l10n("Change Account") ?></a></li>
        <li><a href="<?php $net->url(FILENAME_ADDRESS_BOOK, '', true); ?>"><?php zm_l10n("My Address Book") ?></a></li>
        <li><a href="<?php $net->url(FILENAME_ACCOUNT_PASSWORD, '', true); ?>"><?php zm_l10n("Change My Password") ?></a></li>
    </ul>
</fieldset>

<fieldset>
    <legend><?php zm_l10n("Email Settings") ?></legend>
    <ul>
        <li><a href="<?php $net->url(FILENAME_ACCOUNT_NEWSLETTERS, '', true); ?>"><?php zm_l10n("Change Newsletter Subscriptions") ?></a></li>
        <li><a href="<?php $net->url(FILENAME_ACCOUNT_NOTIFICATIONS, '', true); ?>"><?php zm_l10n("Change Product Notifications") ?></a></li>
    </ul>
</fieldset>

<?php $voucherBalance = ZMRequest::getAccount()->getVoucherBalance(); ?>
<?php if (0 < $voucherBalance) { ?>
    <fieldset>
        <legend><?php zm_l10n("Gift Certificate Account") ?></legend>
        <div class="btn"><a href="<?php $net->url(FILENAME_GV_SEND) ?>" class="btn"><?php zm_l10n("Send Gift Certificate") ?></a></div>
        <p><?php zm_l10n("You have funds (%s) in your Gift Certificate Account.", $utils->formatMoney($voucherBalance, true, false)) ?></p>
    </fieldset>
<?php } ?>

<?php if ($zm_resultList->hasResults()) { ?>
    <?php $zm_resultList->setPagination(3); ?>
    <h3>
        <?php if (3 < $zm_resultList->getNumberOfResults()) { ?>
            <a href="<?php $net->url(FILENAME_ACCOUNT_HISTORY, '', true) ?>"><?php zm_l10n("(Show All)") ?></a>
        <?php } ?>
        <?php zm_l10n("Previous Orders") ?>
    </h3>
    <div class="rlist">
        <table cellspacing="0" cellpadding="0"><tbody>
            <?php $first = true; $odd = true; foreach ($zm_resultList->getResults() as $order) { ?>
              <?php include('resultlist/order.php') ?>
            <?php $first = false; $odd = !$odd; } ?>
        </tbody></table>
    </div>
<?php } ?>
