<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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
 */
?>
<?php $view->extend('StorefrontBundle::default_layout.html.php'); ?>

<?php $crumbtrail->addCrumb(_zm('Checkout'), $net->generate('checkout_shipping'))->addCrumb(_zm('Shipping Address')) ?>
<?php $currentAddress = $shoppingCart->getShippingAddress(); ?>
<?php if (0 < count($addressList)) { ?>
    <?php echo $form->open('checkout_shipping_address', 'action=select', true) ?>
        <h3><?php _vzm("Select an existing address") ?></h3>
        <fieldset>
            <legend><?php _vzm("Address Book") ?></legend>
            <?php foreach ($addressList as $address) { ?>
                <p>
                    <?php $checked = (null != $currentAddress && $currentAddress->getId() == $address->getId()) ? ' checked="checked"' : ""; ?>
                    <?php if (1 == count($addressList)) { $checked = ' checked="checked"'; } ?>
                    <input type="radio" id="address_<?php echo $address->getId() ?>" name="addressId" value="<?php echo $address->getId() ?>" <?php echo $checked ?>/>
                    <label for="address_<?php echo $address->getId() ?>"><?php echo $html->encode($address->getFullName()) ?></label>
                    <br/>
                    <?php echo $macro->formatAddress($address) ?>
                    <br/>
                </p>
            <?php } ?>
        </fieldset>
        <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Continue") ?>" /></div>
    </form>
    <h3><?php _vzm("... or create a new one") ?></h3>
<?php } ?>

<?php echo $form->open('checkout_shipping_address', 'action=create', true, array('id'=>'shippingAddress')) ?>
    <?php echo $this->render('StorefrontBundle::address.html.php', array('address' => $shippingAddress)) ?>
    <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Continue") ?>" /></div>
</form>
