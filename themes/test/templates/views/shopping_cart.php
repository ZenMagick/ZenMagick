<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

$crumbtrail->addCrumb(_zm('Shopping Cart'));
if ($shoppingCart->isEmpty()) { ?>
    <h2><?php _vzm("Your Shopping Cart is empty") ?></h2>
<?php } else { ?>
    <?php echo $form->open('shopping_cart', '', true) ?>
        <table cellpadding="0" cellspacing="0">
            <tbody>
            <?php $odd = true; $first = true; foreach ($shoppingCart->getItems() as $item) { ?>
                <tr class="<?php echo ($odd?"odd":"even").($first?" first":" other") ?>">
                <td class="remove"><a href="<?php echo $net->url('cart.remove', 'product_id='.$item->getId()) ?>"><img src="<?php echo $this->asUrl("resource:images/small_delete.gif") ?>" alt="remove" /></a></td>
                    <td class="img">
                        <?php echo $html->productImageLink($item->getProduct()) ?>
                        <?php echo $form->hiddenCartFields($item) ?>
                    </td>
                    <td class="itm">
                        <?php if (!$item->isStockAvailable() && $settingsService->get('isEnableStock')) { ?>
                            <span class="note"><?php _vzm('* Out of Stock') ?></span><br/>
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
                <td colspan="4" class="total"><?php _vzm("Subtotal") ?></td>
                <td class="price"><?php echo $utils->formatMoney($shoppingCart->getSubtotal()) ?></td>
            </tr>
            </tbody>
        </table>
        <div class="btn">
            <input type="submit" class="btn" value="<?php _vzm("Update Cart") ?>" />
            <a class="btn" href="<?php echo $net->url('checkout_shipping', '', true) ?>"><?php _vzm("Checkout") ?></a>
        </div>
        <div>
            <a class="btn" href="<?php echo $net->url('popup_shipping_estimator', '', true) ?>" onclick="popupWindow(this.href); return false;"><?php _vzm("Shipping Estimator") ?></a>
        </div>

        <?php echo $this->fetchBlockGroup('shoppingCart.options') ?>
    </form>
<?php } ?>

<?php if ($this->exists('views/lift-suggestions.php')) { ?>
  <?php echo $this->fetch('views/lift-suggestions.php') ?>
<?php } ?>

<?php echo $html->backLink('Continue Shopping') ?>
