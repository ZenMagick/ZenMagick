<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
<title><?php zm_l10n("A coupon from %s", ZMSettings::get('storeName')) ?></title>
</head>
<body>
<body>
<div style="font-family:Verdana,Arial,Helvetica,sans-serif;font-size:10pt;">
<p><?php zm_l10n("Dear %s %s,", $currentAccount->getFirstName(), $currentAccount->getLastName()) ?></p>

<p><?php zm_l10n('We\'re pleased to offer you a Store Coupon for our online store at %s', ZMSettings::get('storeName')) ?></p>
<p><?php zm_l10n('You can redeem this coupon during checkout. Just enter the code provided, and click on the redeem button.') ?></p>

<p><?php zm_l10n('The coupon code is <strong>%s</strong>.', $currentCoupon->getCode()) ?></p>

<p><?php zm_l10n('The coupon is valid between %s and %s', $locale->shortDate($currentCoupon->getStartDate(), false), $locale->shortDate($currentCoupon->getExpiryDate(), false)) ?></p>

<p><?php zm_l10n('Don\'t lose the coupon code, make sure to keep the code safe so you can benefit from this special offer.') ?></p>

<p><?php zm_l10n('Thank you for shopping with us!') ?></p>
<p><?php zm_l10n("Sincerely, %s", ZMSettings::get('storeOwner')) ?></p>

<?php if (!isset($isSupressDisclaimer)) { echo $zm_theme->staticPageContent('email_advisory'); } ?>
<?php echo $office_only_html; ?>
</div>
</body>
</html>
