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

<script type="text/javascript" src="<?php $zm_theme->themeURL("jquery.js") ?>"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#zone_country_id').change(updateState);
    });

    function updateState() {
        // show timer
        $('#state').after('<span id="state_timer"><img src="<?php $zm_theme->themeUrl('images/circle-ball-dark-antialiased.gif') ?>"> <?php zm_l10n("Loading...") ?></span>');

        var countryId = $('#zone_country_id').val();
        $.ajax({
            type: "GET",
            url: "<?php $_t->net->ajax('country', 'getZonesForCountryId') ?>",
            data: "countryId="+countryId,
            success: function(msg) {
                // remove timer
                $('#state_timer').remove();

                var json = eval('(' + msg + ')');
                if (0 < json.length) {
                    var state_value = $('#state').val();
                    var state_select = '<select id="state" name="state">';
                    state_select += '<option value=""><?php zm_l10n("-- Please select a state --") ?></option>';
                    for (var ii=0; ii < json.length; ++ii) {
                        var option = json[ii];
                        var selected = state_value == option ? ' selected="selected"' : '';
                        state_select += '<option value="'+option.id+'"'+selected+'>'+option.name+'</option>';
                    }
                    state_select += '</select>';

                    // replace with dropdown
                    $('#state').after(state_select).remove();
                } else {
                    // free text
                    $('#state').after('<input type="text" id="state" name="state" value="">').remove();
                }
            }
        });
    }
</script>

<?php $_t->form->open(FILENAME_CREATE_ACCOUNT, "action=process", true, array('id' => 'create_account', 'method' => 'post', 'onsubmit' => 'return validate(this);')) ?>
    <?php if (ZMSettings::get('isPrivacyMessage')) { ?>
        <fieldset>
            <legend><?php zm_l10n("About Privacy") ?></legend>
            <p>
                <?php zm_l10n("Please acknowledge you agree with our privacy statement by ticking the following box.") ?></br>
                <?php $href = '<a href="' . $_t->net->staticPage('privacy', '', false, false) . '">' . zm_l10n_get("here") . '</a>'; ?>
                <?php zm_l10n("The privacy statement can be read %s.", $href) ?><p>
            <p><input type="checkbox" id="privacy" name="privacy_conditions" value="1" /><label for="privacy"><?php zm_l10n("I have read and agreed to your privacy statement.") ?></label></p>
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
                <?php if (ZMSettings::get('isAccountGender')) { ?>
                    <tr>
                        <td><?php zm_l10n("Title") ?><span>*</span></td>
                        <td>
                            <input type="radio" id="male" name="gender" value="m"<?php zm_radio_state('m', $zm_account->getGender()) ?> />
                            <label for="male"><?php zm_l10n("Mr.") ?></label>
                            <input type="radio" id="female" name="gender" value="f"<?php zm_radio_state('f' == $zm_account->getGender()) ?> />
                            <label for="female"><?php zm_l10n("Ms.") ?></label>
                        </td>
                    </tr>
                <?php } ?>
                <tr>
                    <td><?php zm_l10n("First Name") ?><span>*</span></td>
                    <td><input type="text" name="firstname" value="<?php $_t->html->encode($zm_account->getFirstName()) ?>" /></td>
                </tr>
                <tr>
                    <td><?php zm_l10n("Last Name") ?><span>*</span></td>
                    <td><input type="text" name="lastname" value="<?php $_t->html->encode($zm_account->getLastName()) ?>" /></td>
                </tr>
                <?php if (ZMSettings::get('isAccountDOB')) { ?>
                    <tr>
                        <td><?php zm_l10n("Date of Birth") ?><span>*</span></td>
                        <td><input type="text" name="dob" value="<?php $_t->html->encode($zm_account->getDOB()) ?>" /> <?php zm_l10n("Format: %s;&nbsp;(e.g: %s)", UI_DATE_FORMAT, UI_DATE_FORMAT_SAMPLE) ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td><?php zm_l10n("E-Mail Address") ?><span>*</span></td>
                    <td><input type="text" name="email_address" value="<?php $_t->html->encode($zm_account->getEmail()) ?>" /></td>
                </tr>
                <?php if (ZMSettings::get('isAccountNickname')) { ?>
                    <tr>
                        <td><?php zm_l10n("Nickname") ?></td>
                        <td><input type="text" name="nick" value="<?php $_t->html->encode($zm_account->getNickName()) ?>" /></td>
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
                <?php if (ZMSettings::get('isAccountCompany')) { ?>
                    <tr>
                        <td><?php zm_l10n("Company Name") ?></td>
                        <td><input type="text" name="company" value="<?php $_t->html->encode($zm_address->getCompanyName()) ?>" /></td>
                    </tr>
                <?php } ?>

                <tr>
                    <td><?php zm_l10n("Street Address") ?><span>*</span></td>
                    <td><input type="text" name="street_address" value="<?php $_t->html->encode($zm_address->getAddress()) ?>" <?php $_t->form->fieldLength(TABLE_ADDRESS_BOOK, 'entry_street_address') ?> /></td>
                </tr>
                <tr>
                    <td><?php zm_l10n("Suburb") ?></td>
                    <td><input type="text" name="suburb" value="<?php $_t->html->encode($zm_address->getSuburb()) ?>" <?php $_t->form->fieldLength(TABLE_ADDRESS_BOOK, 'entry_suburb') ?> /></td>
                </tr>
                <tr>
                    <td><?php zm_l10n("City") ?><span>*</span></td>
                    <td><input type="text" name="city" value="<?php $_t->html->encode($zm_address->getCity()) ?>" <?php $_t->form->fieldLength(TABLE_ADDRESS_BOOK, 'entry_city') ?> /></td>
                </tr>
                <?php 
                    $country = $zm_address->getCountry(); 
                    $countryId = 0 != $country->getId() ? $country->getId() : ZMSettings::get('storeCountry');
                ?>
                <tr>
                    <td><?php zm_l10n("Post Code") ?><span>*</span></td>
                    <td><input type="text" name="postcode" value="<?php $_t->html->encode($zm_address->getPostcode()) ?>" <?php $_t->form->fieldLength(TABLE_ADDRESS_BOOK, 'entry_postcode') ?> /></td>
                </tr>
                 <tr>
                    <td><?php zm_l10n("Country") ?><span>*</span></td>
                    <td><?php zm_idp_select('zone_country_id', ZMCountries::instance()->getCountries(), 1, $countryId) ?></td>
                </tr>
                <?php if (ZMSettings::get('isAccountState')) { ?>
                    <?php $zones = ZMCountries::instance()->getZonesForCountryId($countryId); ?>
                    <tr>
                        <td><?php zm_l10n("State/Province") ?><span>*</span></td>
                        <td>
                            <?php if (0 < count($zones)) { ?>
                                <?php zm_idp_select('state', $zones, 1, $zm_address->getState()) ?>
                            <?php } else { ?>
                                <input type="text" name="state" value="<?php $_t->html->encode($zm_address->getState()) ?>" />
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>

                <tr>
                    <td><?php zm_l10n("Telephone Number") ?><span>*</span></td>
                    <td><input type="text" name="telephone" value="<?php $_t->html->encode($zm_account->getPhone()) ?>" /></td>
                </tr>
                <tr>
                    <td><?php zm_l10n("Fax Number") ?></td>
                    <td><input type="text" name="fax" value="<?php $_t->html->encode($zm_account->getFax()) ?>" /></td>
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
                <?php if (ZMSettings::get('isAccountNewsletter')) { ?>
                    <tr>
                        <td></td>
                        <td><input type="checkbox" id="newsletter" name="newsletter" value="1"<?php zm_checkbox_state($zm_account->isNewsletterSubscriber()) ?> /><label for="newsletter"><?php zm_l10n("Receive Store Newsletter") ?></label></td>
                    </tr>
                <?php } ?>

                <?php if (ZMSettings::get('isAccountReferral')) { ?>
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
