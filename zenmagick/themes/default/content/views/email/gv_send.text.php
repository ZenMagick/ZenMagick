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
<?php zm_l10n("Dear %s,", $zm_gvreceiver->getName()) ?>


<?php zm_l10n('You have been sent a Gift Certificate worth %s by %s.', $utils->formatMoney($zm_gvreceiver->getAmount(), false, false), $zm_account->getFullName()) ?>

<?php zm_l10n("The code to redeem your Gift Certificate is: %s.", $zm_coupon->getCode()) ?>

<?php if ($zm_gvreceiver->hasMessage()) { ?>
<?php zm_l10n("%s says:", $zm_account->getFirstName()); ?>

<?php $zm_gvreceiver->getMessage() ?>

<?php } ?>

<?php zm_l10n("To redeem your gift, visit %s.", $net->url(FILENAME_GV_REDEEM, 'couponCode='.$zm_coupon->getCode(), '', true, false)) ?>


<?php echo strip_tags(zm_l10n_chunk_get('email_advisory', ZMSettings::get('storeEmail'))) ?>
<?php echo $office_only_text; ?>
