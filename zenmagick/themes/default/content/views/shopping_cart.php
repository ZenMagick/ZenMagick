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

<?php zm_secure_form(FILENAME_SHOPPING_CART, "action=update_product") ?>
    <table cellpadding="0" cellspacing="0">
        <tbody>
        <?php $odd = true; $first = true; foreach ($zm_cart->getItems() as $item) { ?>
            <tr class="<?php echo ($odd?"odd":"even").($first?" first":" other") ?>">
            <td class="remove"><a href="<?php zm_href(FILENAME_SHOPPING_CART, 'action=remove_product&product_id='.$item->getId()) ?>"><img src="<?php $zm_theme->themeUrl("images/small_delete.gif") ?>" alt="remove" /></a></td>
                <td class="img">
                    <?php zm_product_image_link($item) ?>
                    <?php zm_sc_product_hidden($item) ?>
                </td>
                <td class="itm">
                    <?php echo $item->getName() ?>
                    <?php if ($item->hasAttributes()) { ?>
                        <br/>
                        <?php foreach ($item->getAttributes() as $attribute) { ?>
                            <p><span class="attr"><?php echo $attribute->getName() ?>:</span>
                            <?php $first = true; foreach ($attribute->getValues() as $attributeValue) { ?>
                                <?php if (!$first) { ?>, <?php } ?>
                                <span class="atval"><?php echo $attributeValue->getName() ?></span>
                            <?php $first = false; } ?>
                            </p>
                        <?php } ?>
                    <?php } ?>
                </td>
                <td class="qty">
                    <input type="text" name="cart_quantity[]" size="4" value="<?php echo $item->getQty() ?>" />
                </td>
                <td class="price">
                    <?php zm_format_currency($item->getItemTotal()) ?>
                </td>
            </tr>
        <?php $odd = !$odd; $first = false; } ?>
        <tr class="other">
            <td colspan="4" class="total"><?php zm_l10n("Subtotal") ?></td>
            <td class="price"><?php zm_format_currency($zm_cart->getTotal()) ?></td>
        </tr>
        </tbody>
    </table>
    <div class="btn">
        <input type="submit" class="btn" value="<?php zm_l10n("Update Cart") ?>" />
        <a class="btn" href="<?php zm_secure_href(FILENAME_CHECKOUT_SHIPPING); ?>"><?php zm_l10n("Checkout") ?></a>
    </div>
    <div>
        <a class="btn" href="<?php zm_secure_href(FILENAME_POPUP_SHIPPING_ESTIMATOR); ?>" onclick="popupWindow(this.href); return false;"><?php zm_l10n("Shipping Estimator") ?></a>
    </div>

</form>

<?php echo zen_back_link()."Continue Shopping</a>" ?>
