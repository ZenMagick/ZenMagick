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

<?php $crumbtrail->addCrumb(_zm('Checkout'), $net->generate('checkout_shipping'))->addCrumb(_zm('Shipping')) ?>
<fieldset>
    <legend><?php _vzm("Shipping Address") ?></legend>
    <div class="btn">
        <a class="btn" href="<?php echo $net->generate('checkout_shipping_address') ?>"><?php _vzm("Change Shipping Address") ?></a>
    </div>
    <?php echo $macro->formatAddress($shoppingCart->getShippingAddress()) ?>
</fieldset>

<?php echo $form->open('checkout_shipping') ?>
    <?php if ($shoppingCart->getShippingProviders()) { ?>
        <fieldset>
            <legend><?php _vzm("Shipping Methods") ?></legend>
            <p class="inst"><?php _vzm("Please select the preferred shipping method to use on this order.") ?></p>
            <table cellpadding="0" cellspacing="0" id="smethods">
                <thead>
                    <tr>
                        <th id="smname"></th>
                        <th id="smcost"></th>
                        <th id="smbutt"></th>
                    </tr>
                </thead>
                <tbody>
                <?php $providers = $shoppingCart->getShippingProviders(); ?>
                <?php foreach ($providers as $provider) { ?>
                  <?php $methods = $shoppingCart->getMethodsForProvider($provider); ?>
                  <?php if ($shoppingCart->getCheckoutHelper()->isFreeShipping()) { $id = 'free_free'; ?>
                      <?php $selected = (0 == count($providers) && 0 == count($methods)); ?>
                      <tr class="smethod" onclick="document.getElementById('<?php echo $id ?>').checked = true;">
                          <td><?php _vzm('Free Shipping') ?></td>
                          <td class="smcost"><?php echo $utils->formatMoney(0) ?></td>
                          <td class="smbutt"><input type="radio" id="<?php echo $id ?>" name="shipping" value="<?php echo $id ?>"<?php $form->checked(true, $selected) ?> /></td>
                      </tr>
                  <?php } ?>
                  <?php $errors = $provider->getErrors(); ?>
                  <?php if (0 < count($methods) || $provider->hasErrors()) { ?>
                    <tr><td colspan="3">
                      <strong><?php echo $view->escape($provider->getName()) ?></strong>
                      <?php if ($provider->hasIcon()) { ?>
                        <img src="<?php echo $provider->getIcon() ?>" alt="<?php echo $view->escape($provider->getName()) ?>" title="<?php echo $view->escape($provider->getName()) ?>">
                      <?php } ?>
                      <?php if ($provider->hasErrors()) { echo '<br>'; _vzm("(%s)", $errors[0]); } ?>
                    </td></tr>
                  <?php } ?>
                  <?php foreach ($methods as $method) { ?>
                      <?php $id = 'ship_'.$method->getId();?>
                      <?php $selected = (1 == count($methods) && 1 == count($providers)) || ($method->getShippingId() == $shoppingCart->getSelectedShippingMethodId()); ?>
                      <tr class="smethod" onclick="document.getElementById('<?php echo $id ?>').checked = true;">
                          <td><?php echo $view->escape($method->getName()) ?></td>
                          <td class="smcost"><?php echo $utils->formatMoney($method->getCost()) ?></td>
                          <td class="smbutt"><input type="radio" id="<?php echo $id ?>" name="shipping" value="<?php echo $method->getShippingId() ?>"<?php $form->checked(true, $selected) ?> /></td>
                      </tr>
                  <?php } ?>
                <?php } ?>
                </tbody>
            </table>
        </fieldset>
    <?php } ?>

    <fieldset>
        <legend><?php _vzm("Comments") ?></legend>
        <p class="inst"><?php _vzm("Special instructions or comments about your order.") ?></p>
        <?php /* Fix for IE bug regarding textarea... */ ?>
        <table><tr><td><textarea name="comments" rows="3" cols="45"><?php echo $view->escape($shoppingCart->getComments()) ?></textarea></td></tr></table>
    </fieldset>

    <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Continue") ?>" /></div>
</form>
