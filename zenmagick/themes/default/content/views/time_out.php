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

<?php if ($zm_request->isAnonymous()) { ?>
    <h2><?php zm_l10n("Whoops! Your session has expired.") ?></h2>
<?php } else { ?>
    <h2><?php zm_l10n("Whoops! Sorry, but you are not allowed to perform the action requested.") ?></h2>
<?php } ?>

<p><?php zm_l10n("If you were placing an order, please login and your shopping cart will be restored. You may then go back to the checkout and complete your final purchases.") ?></p>

<p><?php zm_l10n('If you had completed an order and wish to review it, or had a download and wish to retrieve it, please go to your <a href="%s">My Account</a> page to view your order.', zm_secure_href(FILENAME_ACCOUNT, '', false)) ?></p>

<?php require("login.php"); ?>
