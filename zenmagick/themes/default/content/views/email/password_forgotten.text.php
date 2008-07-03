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
?><?php zm_l10n("New Password Request\n") ?>

<?php zm_l10n("This is in response to a request for a new password for your account at %s.\n", ZMSettings::get('storeName')) ?>
<?php zm_l10n("Your new password is: %s\n\n", $password) ?>
<?php zm_l10n("For security reasons please remember to change your password next time you logon.\n") ?>

<?php zm_l10n("Regards, %s\n", ZMSettings::get('storeName')) ?>

<?php echo strip_tags(zm_l10n_chunk_get('email_advisory', ZMSettings::get('storeEmail'))) ?>
