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

<?php zm_js_validation(
  zm_js_rule('Checked', 'gender', "Please choose a gender."),
  zm_js_rule('Length', 'firstname', "Your First Name must contain a minimum of %s characters.", ENTRY_FIRST_NAME_MIN_LENGTH),
  zm_js_rule('Length', 'lastname', "Your Last Name must contain a minimum of %s characters.", ENTRY_LAST_NAME_MIN_LENGTH),
  zm_js_rule('Length', 'dob', vsprintf("Your Date of Birth must be in this format: %s.", UI_DATE_FORMAT_SAMPLE), strlen(UI_DATE_FORMAT)),
  zm_js_rule('Length', 'email_address', "Your E-Mail Address must contain a minimum of %s characters.", ENTRY_EMAIL_ADDRESS_MIN_LENGTH),
  zm_js_rule('Length', 'street_address', "Your Street Address must contain a minimum of %s characters.", ENTRY_STREET_ADDRESS_MIN_LENGTH),
  zm_js_rule('Length', 'city', "Your City must contain a minimum of %s characters.", ENTRY_CITY_MIN_LENGTH),
  zm_js_rule('Length', 'state', "Your State must contain a minimum of %s characters.", ENTRY_STATE_MIN_LENGTH),
  zm_js_rule('Length', 'postcode', "Your Post Code must contain a minimum of %s characters.", ENTRY_POSTCODE_MIN_LENGTH),
  zm_js_rule('NotEmpty', 'zone_country_id', "You must select a country."),
  zm_js_rule('Length', 'telephone', "Your Telephone Number must contain a minimum of %s characters.", ENTRY_TELEPHONE_MIN_LENGTH)
) ?>
<?php include_once $zm_theme->themeFile("validation.js"); ?>

<?php zm_secure_form(FILENAME_CREATE_ACCOUNT, "action=process", null, "post", "return validate(this);") ?>
    <?php if (zm_setting('isPrivacyMessage')) { ?>
        <fieldset>
            <legend><?php zm_l10n("About Privacy") ?></legend>
            <p>
                <?php zm_l10n("Please acknowledge you agree with our privacy statement by ticking the following box.") ?></br>
                <?php $href = '<a href="' . zm_static_href('privacy') . '">' . zm_l10n("here") . '</a>'; ?>
                <?php zm_l10n("The privacy statement can be read %s.", $href) ?><p>
            <p><input type="checkbox" id="privacy" name="privacy" value="1" /><label for="privacy"><?php zm_l10n("I have read and agreed to your privacy statement.") ?></label></p>
        </fieldset>
    <?php } ?>
    <fieldset>
        <legend><?php zm_l10n("Create Account") ?></legend>
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
                        <input type="radio" id="female" name="gender" value="f"<?php zm_radio_state('f' == $zm_account->getGender()) ?> />
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
                <?php if (zm_setting('isAccountDOB')) { ?>
                    <tr>
                        <td><?php zm_l10n("Date of Birth") ?><span>*</span></td>
                        <td><input type="text" name="dob" value="<?php zm_date_short($zm_account->getDOB()) ?>" /><?php zm_l10n("Format: %s;&nbsp;(e.g: %s)", UI_DATE_FORMAT, UI_DATE_FORMAT_SAMPLE) ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td><?php zm_l10n("E-Mail Address") ?><span>*</span></td>
                    <td><input type="text" name="email_address" value="<?php echo $zm_account->getEmail() ?>" /></td>
                </tr>
                <?php if ($zm_runtime->isBBActive()) { ?>
                    <tr>
                        <td><?php zm_l10n("Nickname") ?></td>
                        <td><input type="text" name="nick" value="<?php echo $zm_account->getNickName() ?>" /></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td><?php zm_l10n("Password") ?><span>*</span></td>
                    <td><input type="password" name="password" value="" /></td>
                </tr>
                <tr>
                    <td><?php zm_l10n("Confirm Password") ?><span>*</span></td>
                    <td><input type="password" name="confirmation" value="" /></td>
                </tr>
                <?php if (zm_setting('isAccountCompany')) { ?>
                    <tr>
                        <td><?php zm_l10n("Company Name") ?></td>
                        <td><input type="text" name="company" value="<?php echo $zm_account->getCompanyName() ?>" /></td>
                    </tr>
                <?php } ?>

                <tr>
                    <td><?php zm_l10n("Street Address") ?><span>*</span></td>
                    <td><input type="text" name="street_address" value="<?php echo $zm_address->getAddress() ?>" <?php zm_field_length(TABLE_ADDRESS_BOOK, 'entry_street_address') ?> /></td>
                </tr>
                <tr>
                    <td><?php zm_l10n("Suburb") ?></td>
                    <td><input type="text" name="suburb" value="<?php echo $zm_address->getSuburb() ?>" <?php zm_field_length(TABLE_ADDRESS_BOOK, 'entry_suburb') ?> /></td>
                </tr>
                <tr>
                    <td><?php zm_l10n("City") ?><span>*</span></td>
                    <td><input type="text" name="city" value="<?php echo $zm_address->getCity() ?>" <?php zm_field_length(TABLE_ADDRESS_BOOK, 'entry_city') ?> /></td>
                </tr>
                <?php $country = $zm_address->getCountry(); ?>
                <?php if (zm_setting('isAccountState')) { ?>
                    <?php $zones = $zm_countries->getZonesForCountryId($country->getId()); ?>
                    <tr>
                        <td><?php zm_l10n("State/Province") ?><span>*</span></td>
                        <td>
                            <?php if (0 < count($zones)) { ?>
                                <?php zm_idp_select('state', $zones, 1, $zm_address->getState()) ?>
                            <?php } else { ?>
                                <input type="text" name="state" value="<?php echo $zm_address->getState() ?>" />
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
                <tr>
                    <td><?php zm_l10n("Post Code") ?><span>*</span></td>
                    <td><input type="text" name="postcode" value="<?php echo $zm_address->getPostcode() ?>" <?php zm_field_length(TABLE_ADDRESS_BOOK, 'entry_postcode') ?> /></td>
                </tr>
                 <tr>
                    <td><?php zm_l10n("Country") ?><span>*</span></td>
                    <td><?php zm_idp_select('zone_country_id', $zm_countries->getCountries(), 1, $country ? $country->getId() : null) ?></td>
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
                <?php if (zm_setting('isAccountNewsletter')) { ?>
                    <tr>
                        <td></td>
                        <?php $subscriptions = $zm_account->getSubscriptions(); ?>
                        <td><input type="checkbox" id="newsletter" name="newsletter" value="1"<?php zm_checkbox_state($subscriptions->isNewsletterSubscriber()) ?> /><label for="newsletter"><?php zm_l10n("Receive Store Newsletter") ?></label></td>
                    </tr>
                <?php } ?>

                <?php if (zm_setting('isAccountReferral')) { ?>
                    <tr>
                        <td><?php zm_l10n("Referral Code") ?><span>*</span></td>
                        <td><input type="text" name="referral" value="" /></td>
                    </tr>
                <?php } ?>

                <tr class="legend">
                    <td colspan="2"><?php zm_l10n("<span>*</span> Mandatory fields") ?></td>
                </tr>
            </tbody>
        </table>
    </fieldset>
    <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Create Account") ?>" /></div>
</form>
