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

<?php $currentAddress = $zm_cart->getShippingAddress(); ?>
<?php if (0 < count($zm_addressList)) { ?>
    <?php $form->open(FILENAME_CHECKOUT_SHIPPING_ADDRESS, 'action=submit', true, array('id'=>'existing_address')) ?>
        <h3><?php zm_l10n("Select an existing address") ?></h3>
        <fieldset>
            <legend><?php zm_l10n("Address Book") ?></legend>
            <?php foreach ($zm_addressList as $address) { ?>
                <p>
                    <?php $checked = (null != $currentAddress && $currentAddress->getId() == $address->getId()) ? ' checked="checked"' : ""; ?>
                    <?php if (1 == count($zm_addressList)) { $checked = ' checked="checked"'; } ?>
                    <input type="radio" id="address_<?php echo $address->getId() ?>" name="address" value="<?php echo $address->getId() ?>" <?php echo $checked ?>/>
                    <label for="address_<?php echo $address->getId() ?>"><?php $html->encode($address->getFullName()) ?></label>
                    <br/>
                    <?php $macro->formatAddress($address) ?>
                    <br/>
                </p>
            <?php } ?>
        </fieldset>
        <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Continue") ?>" /></div>
    </form>
    <h3><?php zm_l10n("... or create a new one") ?></h3>
<?php } ?>

<?php $form->open(FILENAME_CHECKOUT_SHIPPING_ADDRESS, 'action=submit', true, array('id'=>'address', 'onsubmit'=>'return validate(this);')) ?>
    <?php $address = $zm_address; ?>
    <?php include "address.php" ?>
    <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Continue") ?>" /></div>
</form>
