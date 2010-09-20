<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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

<?php echo $form->open(FILENAME_SHOPPING_CART, "action=update_product", true) ?>
    <table cellpadding="0" cellspacing="0" width="100%">
    	<tr class="tableHeading">
    		<th><?php zm_l10n('Qty.') ?></th>
    		<th colspan="2"><?php zm_l10n('Item Name') ?></th>
    		<th style="text-align: left"><?php zm_l10n('Total') ?></th>
    		<th style="width: 50px" align="right">&nbsp;</th>
    	</tr>
        <tbody>
        <?php $odd = true; $first = true; foreach ($shoppingCart->getItems() as $item) { ?>
            <tr class="<?php echo ($odd?"odd":"rowEven").($first?" first":" other") ?>">
            	<td class="qty">
                    <input type="text" name="cart_quantity[]" size="4" value="<?php echo $item->getQty() ?>" />
                </td>
                
                <td class="img">
                    <?php echo $html->productImageLink($item->getProduct()) ?>
                    <?php echo $form->hiddenCartFields($item) ?>
                </td>
                <td class="itm">
                    <?php if (!$item->isStockAvailable() && ZMSettings::get('isEnableStock')) { ?>
                        <span class="note"><?php zm_l10n('* Out of Stock') ?></span><br/>
                    <?php } ?>
                    <?php echo $html->encode($item->getProduct()->getName()) ?>
                    <?php if ($item->hasAttributes()) { ?>
                        <br/>
                        <?php foreach ($item->getAttributes() as $attribute) { ?>
                            <p><span class="attr"><?php echo $html->encode($attribute->getName()) ?>:</span>
                            <?php $first = true; foreach ($attribute->getValues() as $attributeValue) { ?>
                                <?php if (!$first) { ?>, <?php } ?>
                                <span class="atval"><?php echo $html->encode($attributeValue->getName()) ?></span>
                            <?php $first = false; } ?>
                            </p>
                        <?php } ?>
                    <?php } ?>
                </td>
                
                <td class="price">
                    <?php echo $utils->formatMoney($item->getItemTotal()) ?>
                </td>
                
                <td class="remove" align="right"><a href="<?php echo $net->url(FILENAME_SHOPPING_CART, 'action=remove_product&product_id='.$item->getId()) ?>"><img src="<?php echo $this->asUrl("images/small_delete.gif") ?>" alt="remove" /></a></td>
            </tr>
        <?php $odd = !$odd; $first = false; } ?>
        <tr class="other">
            <td colspan="5" class="total" align="right"><?php zm_l10n("Subtotal") ?>: <?php echo $utils->formatMoney($shoppingCart->getTotal()) ?></td>
        </tr>
        </tbody>
    </table>
    <div class="btn">
        <div class="back"><input type="submit" class="btn" value="<?php zm_l10n("Update Cart") ?>" /></div>
        <div class="forward"><a class="btn" href="<?php echo $net->url(FILENAME_CHECKOUT_SHIPPING, '', true) ?>"><?php zm_l10n("Checkout") ?></a></div>
        <div class="clearBoth"></div>
    </div>
    <div>
        <a class="btn" href="<?php echo $net->url(FILENAME_POPUP_SHIPPING_ESTIMATOR, '', true) ?>" onclick="popupWindow(this.href); return false;"><?php zm_l10n("Shipping Estimator") ?></a>
    </div>

    <?php if (defined('MODULE_PAYMENT_PAYPALWPP_STATUS') && MODULE_PAYMENT_PAYPALWPP_STATUS == 'True') {
        global $order, $db, $currencies;
        include(DIR_FS_CATALOG . DIR_WS_MODULES .  'payment/paypal/tpl_ec_button.php');
    } ?>

</form>

<?php echo $html->backLink('Continue Shopping') ?>
