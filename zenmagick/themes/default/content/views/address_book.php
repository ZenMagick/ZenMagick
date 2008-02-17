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

<?php foreach ($zm_addressList as $address) { ?>
    <fieldset>
        <legend><?php echo ($address->getFirstName() . ' ' . $address->getLastName()) ?>
        <?php echo ($address->isPrimary() ? zm_l10n("(primary)") : ''); ?></legend>
        <div class="btn">
            <?php if (!$address->isPrimary()) { ?>
                <a class="btn" href="<?php zm_secure_href(FILENAME_ADDRESS_BOOK_PROCESS, 'delete='.$address->getId()) ?>"><?php zm_l10n("Delete") ?></a>
            <?php } ?>
            <a class="btn" href="<?php zm_secure_href(FILENAME_ADDRESS_BOOK_PROCESS, 'edit='.$address->getId()) ?>"><?php zm_l10n("Edit") ?></a>
        </div>
        <?php zm_format_address($address) ?>
    </fieldset>
<?php } ?>
<div class="btn"><a href="<?php zm_secure_href(FILENAME_ADDRESS_BOOK_PROCESS) ?>" class="btn"><?php zm_l10n("Add Address") ?></a></div>
