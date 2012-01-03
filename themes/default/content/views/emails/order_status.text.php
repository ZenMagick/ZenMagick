<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
?><?php _vzm("Dear %s %s,", $currentAccount->getFirstName(), $currentAccount->getLastName()) ?>


<?php _vzm("This is to inform you that your order #%s has been updated.", $currentOrder->getId()) ?>

<?php if (ZMAccount::REGISTERED == $currentAccount->getType()) { ?>
<?php _vzm("More details can be found at the following URL: %s", $request->absoluteUrl($net->url('account_history_info', 'order_id='.$currentOrder->getId()), true), true, true) ?>
<?php } else { ?>
<?php _vzm("You can check the status of your order at: %s.", $request->absoluteUrl($net->url('guest_history', '', true), true, true)) ?>
<?php } ?>

<?php if ($newOrderStatus != $currentOrder->getStatusName()) { ?>
<?php _vzm("The new order status is: %s.", $newOrderStatus) ?>
<?php } ?>

<?php if (!empty($comment)) { ?>
<?php _vzm("The following comment has been added to your order:") ?>

<?php echo $comment ?>
<?php } ?>


<?php _vzm("Regards, %s", $settingsService->get('storeName')) ?>

<?php echo strip_tags($utils->staticPageContent('email_advisory')) ?>
