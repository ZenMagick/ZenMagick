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
$comment = strip_tags(trim($matches[1]));
$language = ZMRuntime::getLanguage();
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="<?php echo $language->getCode() ?>">
<head>
<title><?php zm_l10n("Order update #%s", $zm_order->getId()) ?></title>
</head>
<body>
<body>
<div style="font-family:Verdana,Arial,Helvetica,sans-serif;font-size:10pt;">
<p><?php zm_l10n("Dear %s %s,", ZMRequest::getAccount()->getFirstName(), ZMRequest::getAccount()->getLastName()) ?></p>

<p><?php zm_l10n("This is to inform you that your order #%s has been update.", $zm_order->getId()) ?></p>
<?php $href = '<a href="'.zm_href(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id='.$zm_order->getId(), false).'">'.zm_l10n_get("order #%s", $zm_order->getId()).'</a>'; ?>
<p><?php zm_l10n("More details can be found at the following URL: %s", $href) ?></p>

<?php if ($newOrderStatus != $zm_order->getStatus()) { ?>
<?php zm_l10n("The new order status is: %s.", $newOrderStatus) ?>
<?php } ?>

<?php if (!empty($comment)) { ?>
<p><?php zm_l10n("The following comment has been added to your order:") ?></p>
<p><?php echo zm_text2html($comment) ?></p>
<?php } ?>

<p><?php zm_l10n("Regards, %s", ZMSettings::get('storeName')) ?></p>

<?php echo zm_l10n_chunk_get('email_advisory', ZMSettings::get('storeEmail')) ?>
</div>
</body>
</html>
