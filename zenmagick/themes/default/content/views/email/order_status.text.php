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
?><?php
$newOrderStatus = $EMAIL_TEXT_NEW_STATUS;
preg_match('/[^:]*:(.*)/ms', $EMAIL_TEXT_STATUS_COMMENTS, $matches);
$comment = trim($matches[1]);
?>
<?php zm_l10n("Dear %s %s,", ZMRequest::getAccount()->getFirstName(), ZMRequest::getAccount()->getLastName()) ?>


<?php zm_l10n("This is to inform you that your order #%s has been update.", $zm_order->getId()) ?>
<?php zm_l10n("More details can be found at the following URL: %s", zm_href(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id='.$zm_order->getId(), false)) ?>

<?php if ($newOrderStatus != $zm_order->getStatus()) { ?>
<?php zm_l10n("The new order status is: %s.", $newOrderStatus) ?>
<?php } ?>

<?php if (!empty($comment)) { ?>
<?php zm_l10n("The following comment has been added to your order:") ?>

<?php echo $comment ?>
<?php } ?>


<?php zm_l10n("Regards, %s", ZMSettings::get('storeName')) ?>

<?php echo strip_tags(zm_l10n_chunk_get('email_advisory', ZMSettings::get('storeEmail'))) ?>
