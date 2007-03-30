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
    $validator->addRuleSet(new ZMRuleSet('address', array(
        new ZMRequiredRule('gender', 'Please choose a gender.'),
        new ZMRequiredRule('firstname', 'Please enter your first name.'),
        new ZMRequiredRule('lastname', 'Please enter your last name.'),
        new ZMRequiredRule('street_address', 'Please enter your address.'),
        new ZMRequiredRule('city', 'Please enter a City.'),
        new ZMRequiredRule('state', 'Please enter a state.'),
        new ZMRequiredRule('postcode', 'Please enter a postcode.'),
        new ZMRequiredRule('zone_country_id', 'Please select a country.')
    )));
    $validator->toJSString('address');
?>
<?php include_once $zm_theme->themeFile("validation.js"); ?>

<?php $country = $address->getCountry(); ?>
<?php $zones = $zm_countries->getZonesForCountryId($country->getId()); ?>
<fieldset>
    <legend><?php zm_l10n("Address") ?></legend>
    <table cellspacing="0" cellpadding="0" id="address">
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
                    <input type="radio" id="male" name="gender" value="m"<?php zm_radio_state('m'==$address->getGender()) ?> />
                    <label for="male"><?php zm_l10n("Mr.") ?></label>
                    <input type="radio" id="female" name="gender" value="f"<?php zm_radio_state('f', $address->getGender()) ?> />
                    <label for="female"><?php zm_l10n("Ms.") ?></label>
                </td>
            </tr>
            <tr>
                <td><?php zm_l10n("First Name") ?><span>*</span></td>
                <td><input type="text" id="firstname" name="firstname" value="<?php echo $address->getFirstName() ?>" /></td>
            </tr>
            <tr>
                <td><?php zm_l10n("Last Name") ?><span>*</span></td>
                <td><input type="text" id="lastname" name="lastname" value="<?php echo $address->getLastName() ?>" /></td>
            </tr>
            <tr>
                <td><?php zm_l10n("Company Name") ?></td>
                <td><input type="text" id="company" name="company" value="<?php echo $address->getCompanyName() ?>" /></td>
            </tr>
            <tr>
                <td><?php zm_l10n("Street Address") ?><span>*</span></td>
                <td><input type="text" id="street_address" name="street_address" value="<?php echo $address->getAddress() ?>" <?php zm_field_length(TABLE_ADDRESS_BOOK, 'entry_street_address') ?> /></td>
            </tr>
            <tr>
                <td><?php zm_l10n("Suburb") ?></td>
                <td><input type="text" id="suburb" name="suburb" value="<?php echo $address->getSuburb() ?>" <?php zm_field_length(TABLE_ADDRESS_BOOK, 'entry_suburb') ?> /></td>
            </tr>
            <tr>
                <td><?php zm_l10n("City") ?><span>*</span></td>
                <td><input type="text" id="city" name="city" value="<?php echo $address->getCity() ?>" <?php zm_field_length(TABLE_ADDRESS_BOOK, 'entry_city') ?> /></td>
            </tr>
            <tr>
                <td><?php zm_l10n("State/Province") ?><span>*</span></td>
                <td>
                    <?php if (0 < count($zones)) { ?>
                        <?php zm_idp_select('state', $zones, 1, $address->getState()) ?>
                    <?php } else { ?>
                        <input type="text" name="state" value="<?php echo $address->getState() ?>" />
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <td><?php zm_l10n("Post Code") ?><span>*</span></td>
                <td><input type="text" id="postcode" name="postcode" value="<?php echo $address->getPostcode() ?>" <?php zm_field_length(TABLE_ADDRESS_BOOK, 'entry_postcode') ?> /></td>
            </tr>
             <tr>
                <td><?php zm_l10n("Country") ?><span>*</span></td>
                <td><?php zm_idp_select('zone_country_id', array_merge(array(new ZMIdNamePair("", zm_l10n_get("Select Country"))), $zm_countries->getCountries()), 1, $country ? $country->getId() : zm_setting('storeCountry')) ?></td>
            </tr>
            <?php if (!$address->isPrimary()) { ?>
                 <tr>
                    <td></td>
                    <td>
                        <input type="checkbox" id="primary" name="primary" value="on" />
                        <label for="primary"><?php zm_l10n("Set as Primary Address") ?></label>
                    </td>
                </tr>
            <?php } ?>
            <tr class="legend">
                <td colspan="2"><?php zm_l10n("<span>*</span> Mandatory fields") ?></td>
            </tr>
        </tbody>
    </table>
</fieldset>
