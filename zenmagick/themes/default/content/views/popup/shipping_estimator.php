<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 *
 * $Id$
 */
?>
<?php

  $shippingEstimator = new ZMShippingEstimator();
  $shippingEstimator->prepare();

?>

<h1><?php zm_l10n("Shipping Calculator") ?></h1>

<?php if ($shippingEstimator->isCartEmpty()) { ?>
    <h2><?php zm_l10n("Shipping not available") ?></h2>
    <p><?php zm_l10n("Whoops! Either your shopping cart is empty or your session has expired.") ?></p>
<?php } else { ?>
    <?php $address = $shippingEstimator->getAddress(); ?>
    <?php if (null != $address) { ?>
        <h4><?php zm_l10n("Ship To") ?></h4>
        <div id="cadr">
            <?php zm_format_address($address); ?>
        </div>
    <?php } else { ?>
        <?php zm_secure_form(FILENAME_POPUP_SHIPPING_ESTIMATOR) ?>
          <table cellspacing="0" cellpadding="0"><tbody>
             <tr>
                <td><?php zm_l10n("Country") ?></td>
                <td><?php zm_idp_select('country_id', array_merge(array(ZMLoader::make("IdNamePair", "", zm_l10n_get("Select Country"))), ZMCountries::instance()->getCountries()), 1, $shippingEstimator->getCountryId()) ?></td>
            </tr>
            <tr>
                <td><?php zm_l10n("State/Province") ?></td>
                <td>
                    <?php $zones = ZMCountries::instance()->getZonesForCountryId($shippingEstimator->getCountryId()); ?>
                    <?php if (0 < count($zones)) { ?>
                        <?php zm_idp_select('state', array_merge(array(ZMLoader::make("IdNamePair", "", zm_l10n_get("Select State"))), $zones), 1, $shippingEstimator->getStateId()) ?>
                    <?php } else { ?>
                        <input type="text" name="state" value="" />
                    <?php } ?>
                </td>
              </tr>
              <tr>
                  <td><?php zm_l10n("Post Code") ?></td>
                  <td><input type="text" id="zip_code" name="zip_code" value="<?php echo $shippingEstimator->getPostcode() ?>" /></td>
              </tr>
          </tbody></table>
          <div class="btn"><input type="submit" value="<?php zm_l10n("Calculate") ?>" /></div>
        </form>
    <?php } ?>
    <?php $zm_shipping = new ZMShipping(); if ($zm_shipping->isFreeShipping()) { ?>
        <p class="inst"><?php zm_l10n("Shipping is free!") ?></p>
    <?php } else {?>
        <table border="1" cellpadding="2" cellspacing ="2" id="smethods">
            <thead>
                <tr>
                <th id="smname"><?php zm_l10n("Shipping Method") ?></th>
                <th id="smcost"><?php zm_l10n("Charge") ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($zm_shipping->getShippingProvider() as $provider) { ?>
                <?php if ($provider->hasError()) { continue; } foreach ($provider->getShippingMethods() as $method) { $id = 'ship_'.$method->getId();?>
                    <?php $selected = false; /* TODO */ ?>
                    <tr class="smethod<?php echo ($selected ? " sel" : "") ?>">
                        <td class="smname"><strong><?php $html->encode($provider->getName()) ?></strong> <?php $html->encode($method->getName()) ?></td>
                        <td class="smcost"><?php $utils->formatMoney($method->getCost()) ?></td>
                    </tr>
                <?php } ?>
            <?php } ?>
            </tbody>
        </table>
    <?php } ?>
<?php } ?>
