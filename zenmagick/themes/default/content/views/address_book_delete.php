<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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

<p><?php zm_l10n("Are you sure you would like to delete the selected address from your address book?") ?></p>
<?php zm_secure_form(FILENAME_ADDRESS_BOOK_PROCESS, "action=deleteconfirm&delete=".$zm_address->getId()."&addressId=".$zm_address->getId(), 'address', "post") ?>
    <fieldset>
        <legend><?php zm_l10n("Selected Address") ?></legend>
        <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Delete") ?>" /></div>
        <?php zm_format_address($zm_address) ?>
    </fieldset>
</form>
