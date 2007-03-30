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

<?php
    $validator = new ZMValidator();
    $validator->addRuleSet(new ZMRuleSet('password', array(
        new ZMRequiredRule('password_current', 'Please enter you current password.'),
        new ZMRequiredRule('password_new', 'Please enter the new password.'),
        new ZMRequiredRule('password_confirmation', 'Please confirm the new password.')
    )));
    $validator->toJSString('password');
?>
<?php include_once $zm_theme->themeFile("validation.js"); ?>

<?php zm_secure_form(FILENAME_ACCOUNT_PASSWORD, "action=process", 'password', "post", "return validate(this);") ?>
    <fieldset>
        <legend><?php zm_l10n("Change Password") ?></legend>
        <p>
            <label for="password_current"><?php zm_l10n("Current Password") ?></label>
            <input type="password" id="password_current" name="password_current" <?php zm_field_length(TABLE_CUSTOMERS, 'customers_password') ?> /> 
        </p>
        <p>
            <label for="password_new"><?php zm_l10n("New Password") ?></label>
            <input type="password" id="password_new" name="password_new" <?php zm_field_length(TABLE_CUSTOMERS, 'customers_password') ?> /> 
        </p>
        <p>
            <label for="password_confirmation"><?php zm_l10n("Confirm Password") ?></label>
            <input type="password" id="password_confirmation" name="password_confirmation" <?php zm_field_length(TABLE_CUSTOMERS, 'customers_password') ?> /> 
        </p>
    </fieldset>
    <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Submit") ?>" /></div>
</form>
