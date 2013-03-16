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
<?php >$crumbtrail->addCrumb(_zm('Shopping Cart'));
if ($shoppingCart->isEmpty()) { ?>
    <h2><?php _vzm("Your Shopping Cart is empty") ?></h2>
<?php } else { ?>
    <?php echo $form->open('cart.update', '', true) ?>
        <table cellpadding="0" cellspacing="0">
            <tbody>
            <?php $odd = true; $first = true; foreach ($shoppingCart->getItems() as $item) { ?>
                <tr class="<?php echo ($odd?"odd":"even").($first?" first":" other") ?>">
                <td class="remove"><a href="<?php echo $net->generate('cart.remove', array('productId' => $item->getId())) ?>"><img src="<?php echo $this->asUrl("images/small_delete.gif") ?>" alt="remove" /></a></td>
                    <td class="img">
                        <?php echo $html->productImageLink($item->getProduct()) ?>
                        <?php echo $form->hiddenCartFields($item) ?>
                    </td>
                    <td class="itm">
                        <?php if (!$item->isStockAvailable() && $settingsService->get('isEnableStock')) { ?>
                            <span class="note"><?php _vzm('* Out of Stock') ?></span><br/>
                        <?php } ?>
                        <?php echo $view->escape($item->getProduct()->getName()) ?>
                        <?php if ($item->hasAttributes()) { ?>
                            <br/>
                            <?php foreach ($item->getAttributes() as $attribute) { ?>
                                <p><span class="attr"><?php echo $view->escape($attribute->getName()) ?>:</span>
                                <?php $first = true; foreach ($attribute->getValues() as $attributeValue) { ?>
                                    <?php if (!$first) { ?>, <?php } ?>
                                    <span class="atval"><?php echo $view->escape($attributeValue->getName()) ?></span>
                                <?php $first = false; } ?>
                                </p>
                            <?php } ?>
                        <?php } ?>
                    </td>
                    <td class="item">
                        <?php echo $utils->formatMoney($item->getItemPrice()) ?>
                    </td>
                    <td class="qty">
                        <input type="text" name="cart_quantity[]" size="4" value="<?php echo $item->getQuantity() ?>" />
                    </td>
                    <td class="price">
                        <?php echo $utils->formatMoney($item->getItemTotal()) ?>
                        <?php if (0 != ($oneTimeCharge = $item->getOneTimeCharge())) { ?>
                            <br><?php _vzm('One time: %s', $utils->formatMoney($oneTimeCharge)) ?>
                        <?php } ?>
                    </td>
                </tr>
            <?php $odd = !$odd; $first = false; } ?>
            <tr class="other">
                <td colspan="5" class="total"><?php _vzm("Subtotal") ?></td>
                <td class="price"><?php echo $utils->formatMoney($shoppingCart->getSubtotal()) ?></td>
            </tr>
            </tbody>
        </table>
        <div class="btn">
            <input type="submit" class="btn" value="<?php _vzm("Update Cart") ?>" />
            <a class="btn" href="<?php echo $net->generate('checkout_shipping') ?>"><?php _vzm("Checkout") ?></a>
        </div>
        <div>
            <a class="btn" href="<?php echo $net->generate('popup_shipping_estimator') ?>" onclick="popupWindow(this.href); return false;"><?php _vzm("Shipping Estimator") ?></a>
        </div>

        <?php echo $this->fetchBlockGroup('shoppingCart.options') ?>
    </form>
<?php } ?>

<?php echo $html->backLink('Continue Shopping') ?>
