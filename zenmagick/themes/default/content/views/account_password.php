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
?>

<?php $form->open(FILENAME_ACCOUNT_PASSWORD, "action=process", true, array('id'=>'account_password')) ?>
    <fieldset>
        <legend><?php zm_l10n("Change Password") ?></legend>
        <p>
            <label for="password_current"><?php zm_l10n("Current Password") ?></label>
            <input type="password" id="password_current" name="password_current" <?php $form->fieldLength(TABLE_CUSTOMERS, 'customers_password') ?> /> 
        </p>
        <p>
            <label for="password_new"><?php zm_l10n("New Password") ?></label>
            <input type="password" id="password_new" name="password_new" <?php $form->fieldLength(TABLE_CUSTOMERS, 'customers_password') ?> /> 
        </p>
        <p>
            <label for="password_confirmation"><?php zm_l10n("Confirm Password") ?></label>
            <input type="password" id="password_confirmation" name="password_confirmation" <?php $form->fieldLength(TABLE_CUSTOMERS, 'customers_password') ?> /> 
        </p>
    </fieldset>
    <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Submit") ?>" /></div>
</form>
