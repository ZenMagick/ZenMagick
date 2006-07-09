<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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

<script type="text/javascript">
    var rules = new Array(
        new Array('Checked', 'gender', '<?php zm_l10n("Please choose a gender.") ?>'),
        new Array('Length', 'firstname', <?php echo ENTRY_FIRST_NAME_MIN_LENGTH ?>,
                  '<?php zm_l10n("Your First Name must contain a minimum of %s characters.", ENTRY_FIRST_NAME_MIN_LENGTH) ?>'),
        new Array('Length', 'lastname', <?php echo ENTRY_LAST_NAME_MIN_LENGTH ?>,
                  '<?php zm_l10n("Your Last Name must contain a minimum of %s characters.", ENTRY_LAST_NAME_MIN_LENGTH) ?>'),
        new Array('Length', 'dob', <?php echo ENTRY_DOB_MIN_LENGTH ?>,
                  '<?php zm_l10n("Your Date of Birth must be in this format: %s (eg %s)", UI_DATE_FORMAT, UI_DATE_FORMAT_SAMPLE) ?>'),
        new Array('Length', 'email_address', <?php echo ENTRY_EMAIL_ADDRESS_MIN_LENGTH ?>,
                  '<?php zm_l10n("Your E-Mail Address must contain a minimum of %s characters.", ENTRY_EMAIL_ADDRESS_MIN_LENGTH) ?>'),
        new Array('Length', 'telephone', <?php echo ENTRY_TELEPHONE_MIN_LENGTH ?>,
                  '<?php zm_l10n("Your Telephone Number must contain a minimum of %s characters.", ENTRY_TELEPHONE_MIN_LENGTH) ?>')
    );
</script>
<?php include $zm_theme->themeFile("validation.js"); ?>

<?php zm_secure_form(FILENAME_ACCOUNT_EDIT, "action=process", null, "post", "return validate(this);") ?>
    <fieldset>
        <legend><?php zm_l10n("My Account") ?></legend>
        <table cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                   <th id="label"></th>
                   <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php zm_l10n("Title") ?><span>*</span></td>
                    <td>
                        <input type="radio" id="male" name="gender" value="m"<?php zm_radio_state('m', $zm_account->getGender()) ?> />
                        <label for="male"><?php zm_l10n("Mr.") ?></label>
                        <input type="radio" id="female" name="gender" value="f"<?php zm_radio_state('f', $zm_account->getGender()) ?> />
                        <label for="female"><?php zm_l10n("Ms.") ?></label>
                    </td>
                </tr>
                <tr>
                    <td><?php zm_l10n("First Name") ?><span>*</span></td>
                    <td><input type="text" name="firstname" value="<?php echo $zm_account->getFirstName() ?>" /></td>
                </tr>
                <tr>
                    <td><?php zm_l10n("Last Name") ?><span>*</span></td>
                    <td><input type="text" name="lastname" value="<?php echo $zm_account->getLastName() ?>" /></td>
                </tr>
                <tr>
                    <td><?php zm_l10n("Date of Birth") ?><span>*</span></td>
                    <td><input type="text" name="dob" value="<?php zm_date_short($zm_account->getDOB()) ?>" /><?php zm_l10n("&nbsp;(%s)", UI_DATE_FORMAT) ?></td>
                </tr>
                <tr>
                    <td><?php zm_l10n("E-Mail Address") ?><span>*</span></td>
                    <td><input type="text" name="email_address" value="<?php echo $zm_account->getEmail() ?>" /></td>
                </tr>
                <tr>
                    <td><?php zm_l10n("Telephone Number") ?><span>*</span></td>
                    <td><input type="text" name="telephone" value="<?php echo $zm_account->getPhone() ?>" /></td>
                </tr>
                <tr>
                    <td><?php zm_l10n("Fax Number") ?></td>
                    <td><input type="text" name="fax" value="<?php echo $zm_account->getFax() ?>" /></td>
                </tr>
                 <tr>
                    <td><?php zm_l10n("E-Mail Format") ?><span>*</span></td>
                    <td>
                        <input type="radio" id="html" name="email_format" value="HTML"<?php zm_radio_state('HTML', $zm_account->getEmailFormat(), 'HTML') ?> />
                        <label for="html"><?php zm_l10n("HTML") ?></label>
                        <input type="radio" id="text" name="email_format" value="TEXT"<?php zm_radio_state('TEXT', $zm_account->getEmailFormat(), 'TEXT', true) ?> />
                        <label for="text"><?php zm_l10n("Text") ?></label>
                    </td>
                </tr>
                <tr class="legend">
                    <td colspan="2"><?php zm_l10n("<span>*</span> Mandatory fields") ?></td>
                </tr>
            </tbody>
        </table>
    </fieldset>
    <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Update") ?>" /></div>
</form>
