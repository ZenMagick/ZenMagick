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
<?php zm_l10n("Dear %s %s,", ZMRequest::getAccount()->getFirstName(), ZMRequest::getAccount()->getLastName()) ?>


<?php zm_l10n("We wish to welcome you to %s.", ZMSettings::get('storeName')) ?>


<?php echo strip_tags(zm_l10n_chunk_get('email_welcome', ZMSettings::get('storeName'), ZMSettings::get('storeEmail'), ZMSettings::get('storeOwner'))) ?>

<?php zm_l10n("Sincerely, %s", ZMSettings::get('storeOwner')) ?>


<?php echo strip_tags(zm_l10n_chunk_get('email_advisory', ZMSettings::get('storeEmail'))) ?>
<?php echo $office_only_text; ?>
