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
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="<?php echo $language->getCode() ?>">
<head>
<title><?php zm_l10n("Gift Certificate Order #%s", $zm_order->getId()) ?></title>
</head>
<body>
<body>
<div style="font-family:Verdana,Arial,Helvetica,sans-serif;font-size:10pt;">
<p><?php zm_l10n("Dear %s %s,", $zm_account->getFirstName(), $zm_account->getLastName()) ?></p>

<p><?php zm_l10n('You recently purchased a Gift Certificate from our online store at %s', ZMSettings::get('storeName')) ?></p>

<p><?php zm_l10n('For security reasons this was not made immediately available to you. However, this amount has now been released. You may now visit our store and send the value of the Gift Certificate via email to someone else, or use it yourself.') ?></p>

<p><?php zm_l10n('The Gift Certificate(s) you purchased are worth %s', $utils->formatMoney($zm_couponQueue->getAmount(), false, false)) ?></p>

<p><?php zm_l10n('Thank you for shopping with us!') ?></p>
<p><?php zm_l10n("Sincerely, %s", ZMSettings::get('storeOwner')) ?></p>

<?php if (!isset($isSupressDisclaimer)) { echo zm_l10n_chunk_get('email_advisory', ZMSettings::get('storeEmail')); } ?>
<?php echo $office_only_html; ?>
</div>
</body>
</html>
