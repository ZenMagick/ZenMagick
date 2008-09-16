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

<fieldset>
    <legend><?php zm_l10n("Shipping Address") ?></legend>
    <div class="btn">
        <a class="btn" href="<?php $net->url(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', true) ?>"><?php zm_l10n("Change Shipping Address") ?></a>
    </div>
    <?php $macro->formatAddress($zm_cart->getShippingAddress()) ?>
</fieldset>

<?php $form->open(FILENAME_CHECKOUT_SHIPPING, "action=process", true) ?>
    <?php if ($zm_shipping->hasShippingProvider()) { ?>
        <fieldset>
            <legend><?php zm_l10n("Shipping Methods") ?></legend>
            <?php if ($zm_shipping->isFreeShipping()) { ?>
                <p class="inst"><?php zm_l10n("Shipping is free!") ?></p>
                <input type="hidden" name="shipping" value="free_free" />
            <?php } else { ?>
                <p class="inst"><?php zm_l10n("Please select the preferred shipping method to use on this order.") ?></p>
                <table cellpadding="0" cellspacing="0" id="smethods">
                    <thead>
                        <tr>
                            <th id="smname"></th>
                            <th id="smcost"></th>
                            <th id="smbutt"></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($zm_shipping->getShippingProvider() as $provider) { ?>
                        <tr><td colspan="3"><strong><?php $html->encode($provider->getName()) ?></strong><?php if ($provider->hasError()) { zm_l10n("(%s)", $provider->getError()); } ?></td></tr>
                        <?php if ($provider->hasError()) { continue; } foreach ($provider->getShippingMethods() as $method) { $id = 'ship_'.$method->getId();?>
                            <?php $selected = (1 == $zm_shipping->getShippingMethodCount()) || ($method->getShippingId() == $zm_cart->getShippingMethodId()); ?>
                            <tr class="smethod" onclick="document.getElementById('<?php echo $id ?>').checked = true;">
                                <td><?php $html->encode($method->getName()) ?></td>
                                <td class="smcost"><?php $utils->formatMoney($method->getCost()) ?></td>
                                <td class="smbutt"><input type="radio" id="<?php echo $id ?>" name="shipping" value="<?php echo $method->getShippingId() ?>"<?php $form->checked(true, $selected) ?> /></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    </tbody>
                </table>
            <?php } ?>
        </fieldset>
    <?php } ?>

    <fieldset>
        <legend><?php zm_l10n("Comments") ?></legend>
        <p class="inst"><?php zm_l10n("Special instructions or comments about your order.") ?></p>
        <?php /* Fix for IE bug regarding textarea... */ ?>
        <table><tr><td><textarea name="comments" rows="3" cols="45"><?php $html->encode($zm_cart->getComment()) ?></textarea></td></tr></table>
    </fieldset>

    <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Continue") ?>" /></div>
</form>
