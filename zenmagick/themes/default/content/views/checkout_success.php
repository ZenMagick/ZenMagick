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

<?php zm_secure_form(FILENAME_CHECKOUT_SUCCESS, 'action=update') ?>
    <h2><?php zm_l10n("Thanks for shopping with us") ?></h2>
    <p><?php zm_l10n("Your order number is: <strong>%s</strong>", $zm_order->getId()) ?></p>
    <?php $account = '<a href="' . zm_href(FILENAME_ACCOUNT, '', false) . '">' . zm_l10n_get("My Account") . '</a>'; ?>
    <p><?php zm_l10n("You can view your full order history by going to the %s page and by clicking on view all orders.", $account) ?></p>
    <?php $customercare = '<a href="' . zm_href(FILENAME_CONTACT_US, '', false) . '">' . zm_l10n_get("Customer Service") . '</a>'; ?>
    <p><?php zm_l10n("Please direct any questions you have to %s.", $customercare) ?></p>

    <?php if (!ZMRequest::getAccount()->isGlobalProductSubscriber()) { ?>
        <fieldset>
            <legend><?php zm_l10n("Product Notifications") ?></legend>
            <h4><?php zm_l10n("Please notify me of updates to the products I have selected below:") ?></h4>
            <?php foreach ($zm_order->getOrderItems() as $orderItem) { $id = "not_" . $orderItem->getProductId(); ?>
                <p>
                    <input type="checkbox" id="<?php echo $id ?>" name="notify[]" value="<?php echo $orderItem->getProductId() ?>" />
                    <label for="<?php echo $id ?>"><?php echo $orderItem->getName() ?></label><br />
                </p>
            <?php } ?>
            <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Update") ?>" /></div>
        </fieldset>
    <?php } ?>


    <?php $voucherBalance = ZMRequest::getAccount()->getVoucherBalance(); ?>
    <?php if (0 < $voucherBalance) { ?>
        <fieldset>
            <legend><?php zm_l10n("Gift Certificate Account") ?></legend>
            <p>
                <?php zm_l10n("You have funds (%s) in your Gift Certificate Account.", zm_format_currency($voucherBalance, true, false)) ?><br />
                <?php $email = '<a href="' . zm_href(FILENAME_GV_SEND, '', false) . '">' . zm_l10n_get("email") . '</a>'; ?>
                <?php zm_l10n("If you want to, you can send those funds by %s to someone.", $email) ?>
            </p>
            <div class="btn"><a href="<?php zm_href(FILENAME_GV_SEND) ?>" class="btn"><?php zm_l10n("Send Gift Certificate") ?></a></div>
        </fieldset>
    <?php } ?>

    <?php if (ZMRequest::isGuest()) { ?>
        <fieldset>
            <legend><?php zm_l10n("Order Status Check") ?></legend>
            <p>
                <?php $lookupLink = '<a href="' . zm_href(ZM_FILENAME_GUEST_HISTORY, '', false) . '">' . zm_l10n_get("order status check") . '</a>'; ?>
                <?php zm_l10n("You can check the status of your order using the %s.", $lookupLink) ?>
            </p>
        </fieldset>
    <?php } ?>
</form>
