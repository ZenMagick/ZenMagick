<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
 */ $countryId = 0 != $address->getCountryId() ? $address->getCountryId() : $settingsService->get('storeCountry'); ?>
<fieldset>
    <legend><?php _vzm("Address") ?></legend>
    <table cellspacing="0" cellpadding="0" id="newaddress">
        <thead>
            <tr>
               <th id="label"></th>
               <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($settingsService->get('isAccountGender')) { ?>
                <tr>
                    <td><?php _vzm("Title") ?><span>*</span></td>
                    <td>
                        <input type="radio" id="male" name="gender" value="m"<?php $form->checked('m'==$address->getGender()) ?> />
                        <label for="male"><?php _vzm("Mr.") ?></label>
                        <input type="radio" id="female" name="gender" value="f"<?php $form->checked('f', $address->getGender()) ?> />
                        <label for="female"><?php _vzm("Ms.") ?></label>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <td><?php _vzm("First Name") ?><span>*</span></td>
                <td><input type="text" id="firstName" name="firstName" value="<?php echo $html->encode($address->getFirstName()) ?>" /></td>
            </tr>
            <tr>
                <td><?php _vzm("Last Name") ?><span>*</span></td>
                <td><input type="text" id="lastName" name="lastName" value="<?php echo $html->encode($address->getLastName()) ?>" /></td>
            </tr>
            <?php if ($settingsService->get('isAccountCompany')) { ?>
                <tr>
                    <td><?php _vzm("Company Name") ?></td>
                    <td><input type="text" id="companyName" name="companyName" value="<?php echo $html->encode($address->getCompanyName()) ?>" /></td>
                </tr>
            <?php } ?>
            <tr>
                <td><?php _vzm("Street Address") ?><span>*</span></td>
                <td><input type="text" id="addressLine1" name="addressLine1" value="<?php echo $html->encode($address->getAddressLine1()) ?>" <?php echo $form->fieldLength(TABLE_ADDRESS_BOOK, 'entry_street_address') ?> /></td>
            </tr>
            <tr>
                <td><?php _vzm("Suburb") ?></td>
                <td><input type="text" id="suburb" name="suburb" value="<?php echo $html->encode($address->getSuburb()) ?>" <?php echo $form->fieldLength(TABLE_ADDRESS_BOOK, 'entry_suburb') ?> /></td>
            </tr>
            <tr>
                <td><?php _vzm("City") ?><span>*</span></td>
                <td><input type="text" id="city" name="city" value="<?php echo $html->encode($address->getCity()) ?>" <?php echo $form->fieldLength(TABLE_ADDRESS_BOOK, 'entry_city') ?> /></td>
            </tr>
            <tr>
                <td><?php _vzm("Post Code") ?><span>*</span></td>
                <td><input type="text" id="postcode" name="postcode" value="<?php echo $html->encode($address->getPostcode()) ?>" <?php echo $form->fieldLength(TABLE_ADDRESS_BOOK, 'entry_postcode') ?> /></td>
            </tr>
             <tr>
                <td><?php _vzm("Country") ?><span>*</span></td>
                <td><?php echo $form->idpSelect('countryId', array_merge(array(new ZMIdNamePair("", _zm("Select Country"))), $container->get('countryService')->getCountries()), $countryId) ?></td>
            </tr>
            <?php if ($settingsService->get('isAccountState')) { ?>
                <?php $zones = $container->get('countryService')->getZonesForCountryId($countryId); ?>
                <tr>
                    <td><?php _vzm("State/Province") ?><span>*</span></td>
                    <td>
                        <?php if (0 < count($zones)) { ?>
                            <?php echo $form->idpSelect('zoneId', $zones, $address->getZoneId()) ?>
                        <?php } else { ?>
                            <input type="text" name="state" value="<?php echo $html->encode($address->getState()) ?>" />
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            <?php if (!$address->isPrimary()) { ?>
                 <tr>
                    <td></td>
                    <td>
                        <input type="hidden" name="_primary" value="<?php echo $address->isPrimary() ?>" />
                        <input type="checkbox" id="primary" name="primary" value="on" <?php $form->checked($address->isPrimary()) ?> />
                        <label for="primary"><?php _vzm("Use as primary address") ?></label>
                    </td>
                </tr>
            <?php } ?>
            <tr class="legend">
                <td colspan="2">
                    <input type="hidden" name="id" value="<?php echo $address->getId() ?>" />
                    <?php _vzm("<span>*</span> Mandatory fields") ?>
                </td>
            </tr>
        </tbody>
    </table>
</fieldset>
