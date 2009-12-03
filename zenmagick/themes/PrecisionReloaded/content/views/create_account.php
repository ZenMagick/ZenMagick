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
<?php $form->open(FILENAME_CREATE_ACCOUNT, "action=process", true, array('id'=>'registration')) ?>
    <?php if (ZMSettings::get('isPrivacyMessage')) { ?>
        <fieldset>
            <legend><?php zm_l10n("About Privacy") ?></legend>
            <p>
                <?php zm_l10n("Please acknowledge you agree with our privacy statement by ticking the following box.") ?></br>
                <?php $href = '<a href="' . $net->staticPage('privacy', false) . '">' . zm_l10n_get("here") . '</a>'; ?>
                <?php zm_l10n("The privacy statement can be read %s.", $href) ?><p>
            <p><input type="checkbox" id="privacy" name="privacy" value="1" /><label for="privacy"><?php zm_l10n("I have read and agreed to your privacy statement.") ?></label></p>
        </fieldset>
    <?php } ?>
    <fieldset>
        <legend><?php zm_l10n("Create Account") ?></legend>
        <table cellspacing="5" cellpadding="2">
            <thead>
                <tr>
                   <th id="label"></th>
                   <th></th>
				  
                </tr>
            </thead>
            <tbody>
               <!-- <?php if (ZMSettings::get('isAccountGender')) { ?>
                    <tr>
                        <td><?php zm_l10n("Title") ?><span>*</span></td>
                        <td>
                            <input type="radio" id="male" name="gender" value="m"<?php $form->checked('m', $registration->getGender()) ?> />
                            <label for="male"><?php zm_l10n("Mr.") ?></label>
                            <input type="radio" id="female" name="gender" value="f"<?php $form->checked('f' == $registration->getGender()) ?> />
                            <label for="female"><?php zm_l10n("Ms.") ?></label>
                        </td>
                    </tr>
                <?php } ?> -->
                <tr>
                    <td><?php zm_l10n("First Name") ?><span>*</span></td>
                    <td><input type="text" name="firstName" value="<?php $html->encode($registration->getFirstName()) ?>" /></td>
                </tr>
                <tr>
                    <td><?php zm_l10n("Last Name") ?><span>*</span></td>
                    <td><input type="text" name="lastName" value="<?php $html->encode($registration->getLastName()) ?>" /></td>
                </tr>
                <!--<?php if (ZMSettings::get('isAccountDOB')) { ?>
                    <tr>
                        <td><?php zm_l10n("Date of Birth") ?><span>*</span></td>
                        <td><input type="text" name="dob" value="<?php $html->encode($registration->getDob()) ?>" /> <?php zm_l10n("Format: %s;&nbsp;(e.g: %s)", UI_DATE_FORMAT, UI_DATE_FORMAT_SAMPLE) ?></td>
                    </tr>
                <?php } ?> -->
                <tr>
                    <td><?php zm_l10n("E-Mail Address") ?><span>*</span></td>
                    <td><input type="text" name="email" value="<?php $html->encode($registration->getEmail()) ?>" /></td>
                </tr>
                <?php if (ZMSettings::get('isAccountNickname')) { ?>
                    <tr>
                        <td><?php zm_l10n("Nickname") ?></td>
                        <td><input type="text" name="nickName" value="<?php $html->encode($registration->getNickName()) ?>" /></td>
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
                <!--<?php if (ZMSettings::get('isAccountCompany')) { ?>
                    <tr>
                        <td><?php zm_l10n("Company Name") ?></td>
                        <td><input type="text" name="companyName" value="<?php $html->encode($registration->getCompanyName()) ?>" /></td>
                    </tr>
                <?php } ?>  -->

                <tr>
                    <td><?php zm_l10n("Street Address") ?><span>*</span></td>
                    <td><input type="text" name="addressLine1" value="<?php $html->encode($registration->getAddressLine1()) ?>" <?php $form->fieldLength(TABLE_ADDRESS_BOOK, 'entry_street_address') ?> /></td>
                </tr>
               <!-- <tr>
                    <td><?php zm_l10n("PlaypenSuburb") ?></td>
                    <td><input type="text" name="suburb" value="<?php $html->encode($registration->getSuburb()) ?>" <?php $form->fieldLength(TABLE_ADDRESS_BOOK, 'entry_suburb') ?> align="left" /></td>
                </tr> -->
                <tr>
                    <td><?php zm_l10n("City") ?><span>*</span></td>
                    <td><input type="text" name="city" value="<?php $html->encode($registration->getCity()) ?>" <?php $form->fieldLength(TABLE_ADDRESS_BOOK, 'entry_city') ?> /></td>
                </tr>
                <?php 
                    $countryId = $registration->getCountryId(); 
                    $countryId = 0 != $countryId ? $countryId : ZMSettings::get('storeCountry');
                ?>
                <tr>
                    <td><?php zm_l10n("Post Code") ?><span>*</span></td>
                    <td><input type="text" name="postcode" value="<?php $html->encode($registration->getPostcode()) ?>" <?php $form->fieldLength(TABLE_ADDRESS_BOOK, 'entry_postcode') ?> /></td>
                </tr>
                 <tr>
                    <td><?php zm_l10n("Country") ?><span>*</span></td>
                    <td><?php $form->idpSelect('countryId', ZMCountries::instance()->getCountries(), $countryId) ?></td>
                </tr>
                <?php if (ZMSettings::get('isAccountState')) { ?>
                    <?php $zones = ZMCountries::instance()->getZonesForCountryId($countryId); ?>
                    <tr>
                        <td><?php zm_l10n("State/Province") ?><span>*</span></td>
                        <td>
                            <?php if (0 < count($zones)) { ?>
                                <?php $form->idpSelect('zoneId', $zones, $registration->getZoneId()) ?>
                            <?php } else { ?>
                                <input type="text" name="state" value="<?php $html->encode($registration->getState()) ?>" />
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>

                <tr>
                    <td><?php zm_l10n("Telephone Number") ?><span>*</span></td>
                    <td><input type="text" name="phone" value="<?php $html->encode($registration->getPhone()) ?>" /></td>
                </tr>
               <!-- <tr>
                    <td><?php zm_l10n("Fax Number") ?></td>
                    <td><input type="text" name="fax" value="<?php $html->encode($registration->getFax()) ?>" /></td>
                </tr> -->
                <tr><td>&nbsp;</td></tr> 
                <tr><td>&nbsp;</td></tr>
                 <tr>
                    <td><?php zm_l10n("E-Mail Format") ?><span>*</span></td>
                    <td>
                        <input type="radio" id="html" name="emailFormat" value="HTML"<?php $form->checked('HTML', $registration->getEmailFormat(), 'HTML') ?> />
                        <label for="html"><?php zm_l10n("HTML") ?></label>
                        <input type="radio" id="text" name="emailFormat" value="TEXT"<?php $form->checked('TEXT', $registration->getEmailFormat(), 'TEXT', true) ?> />
                        <label for="text"><?php zm_l10n("Text") ?></label>
                    </td>
                </tr>
                <?php if (ZMSettings::get('isAccountNewsletter')) { ?>
                    <tr>
                        <td></td>
                        <td><input type="checkbox" id="newsletterSubscriber" name="newsletterSubscriber" value="1"<?php $form->checked($registration->isNewsletterSubscriber()) ?> /><label for="newsletterSubscriber"><?php zm_l10n("Receive Store Newsletter") ?></label></td>
                    </tr>
                <?php } ?>

                <?php if (ZMSettings::get('isAccountReferral')) { ?>
                    <tr>
                        <td><?php zm_l10n("Referral Code") ?><span>*</span></td>
                        <td><input type="text" name="referral" value="" /></td>
                    </tr>
                <?php } ?>
                <tr><td>&nbsp;</td></tr>  
                <tr><td>&nbsp;</td></tr>
                <tr class="legend">
                    <td colspan="2"><?php zm_l10n("<span>*</span> Mandatory fields") ?></td>
                </tr>
                <tr><td>&nbsp;</td></tr>  
                <tr><td>&nbsp;</td></tr>
            </tbody>
        </table>
    </fieldset>
    <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Create Account") ?>" /></div>
</form>
