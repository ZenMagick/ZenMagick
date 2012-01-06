<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
 *
 * Portions Copyright (c) 2003 Edwin Bekaert (edwin@ednique.com)
 * Portions Copyright (c) 2003-2006 The zen-cart developers
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
 */
?>
<?php

  $shoppingCart = $request->getShoppingCart();
  $shippingEstimator = new zenmagick\apps\storefront\utils\ShippingEstimator();
  $shippingEstimator->prepare();

?>

<h1><?php _vzm("Shipping Calculator") ?></h1>

<?php if ($shippingEstimator->isCartEmpty()) { ?>
    <h2><?php _vzm("Shipping not available") ?></h2>
    <p><?php _vzm("Whoops! Either your shopping cart is empty or your session has expired.") ?></p>
<?php } else { ?>
    <?php $address = $shippingEstimator->getAddress(); ?>
    <?php if (null != $address) { ?>
        <h4><?php _vzm("Ship To") ?></h4>
        <div id="cadr">
            <?php echo $macro->formatAddress($address); ?>
        </div>
    <?php } else { ?>
        <?php echo $form->open('popup_shipping_estimator', '', true) ?>
          <table cellspacing="0" cellpadding="0"><tbody>
             <tr>
                <td><?php _vzm("Country") ?></td>
                <td><?php echo $form->idpSelect('country_id', array_merge(array(new ZMIdNamePair("", _zm("Select Country"))), $container->get('countryService')->getCountries()), $shippingEstimator->getCountryId()) ?></td>
            </tr>
            <tr>
                <td><?php _vzm("State/Province") ?></td>
                <td>
                    <?php $zones = $container->get('countryService')->getZonesForCountryId($shippingEstimator->getCountryId()); ?>
                    <?php if (0 < count($zones)) { ?>
                        <?php echo $form->idpSelect('state', array_merge(array(new ZMIdNamePair("", _zm("Select State"))), $zones), $shippingEstimator->getStateId()) ?>
                    <?php } else { ?>
                        <input type="text" name="state" value="" />
                    <?php } ?>
                </td>
              </tr>
              <tr>
                  <td><?php _vzm("Post Code") ?></td>
                  <td><input type="text" id="zip_code" name="zip_code" value="<?php echo $shippingEstimator->getPostcode() ?>" /></td>
              </tr>
          </tbody></table>
          <div class="btn"><input type="submit" value="<?php _vzm("Calculate") ?>" /></div>
        </form>
    <?php } ?>
    <?php if ($utils->isFreeShipping($shoppingCart)) { ?>
        <p class="inst"><?php _vzm("Shipping is free!") ?></p>
    <?php } else {?>
        <table border="1" cellpadding="2" cellspacing ="2" id="smethods">
            <tr>
                <th id="smname"><?php _vzm("Shipping Method") ?></th>
                <th id="smcost"><?php _vzm("Charge") ?></th>
            </tr>
            <?php $providers = $shoppingCart->getShippingProviders(); ?>
            <?php foreach ($providers as $provider) { ?>
                <?php if ($provider->hasError()) { continue; } foreach ($provider->getShippingMethods($shoppingCart) as $method) { $id = 'ship_'.$method->getId();?>
                    <?php $selected = false; /* TODO */ ?>
                    <tr class="smethod<?php echo ($selected ? " sel" : "") ?>">
                        <td class="smname"><strong><?php echo $html->encode($provider->getName()) ?></strong> <?php echo $html->encode($method->getName()) ?></td>
                        <td class="smcost"><?php echo $utils->formatMoney($method->getCost()) ?></td>
                    </tr>
                <?php } ?>
            <?php } ?>
        </table>
    <?php } ?>
<?php } ?>
