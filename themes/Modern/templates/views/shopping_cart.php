<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2012 zenmagick.org
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
 */
?>

<?php $crumbtrail->addCrumb(_zm('Shopping Cart')) ?>
<?php echo $form->open('shopping_cart', '', true) ?>
    <table cellpadding="0" cellspacing="0" width="100%">
        <tr class="tableHeading">
            <th><?php _vzm('Qty.') ?></th>
            <th colspan="2"><?php _vzm('Item Name') ?></th>
            <th style="text-align: left"><?php _vzm('Total') ?></th>
            <th style="width: 50px" align="right">&nbsp;</th>
        </tr>
        <tbody>
        <?php $odd = true; $first = true; foreach ($shoppingCart->getItems() as $item) { ?>
            <tr class="<?php echo ($odd?"odd":"rowEven").($first?" first":" other") ?>">
                <td class="qty">
                    <input type="text" name="cart_quantity[]" size="4" value="<?php echo $item->getQuantity() ?>" />
                </td>

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

                <td class="price">
                    <?php echo $utils->formatMoney($item->getItemTotal()) ?>
                    <?php if (0 != ($oneTimeCharge = $item->getOneTimeCharge())) { ?>
                        <br><?php _vzm('One time: %s', $utils->formatMoney($oneTimeCharge)) ?>
                    <?php } ?>
                </td>

                <td class="remove" align="right"><a href="<?php echo $net->url('cart.remove', 'product_id='.$item->getId()) ?>"><img src="<?php echo $this->asUrl("images/small_delete.gif") ?>" alt="remove" /></a></td>
            </tr>
        <?php $odd = !$odd; $first = false; } ?>
        <tr class="other">
            <td colspan="5" class="total" align="right"><?php _vzm("Subtotal") ?>: <?php echo $utils->formatMoney($shoppingCart->getSubtotal()) ?></td>
        </tr>
        </tbody>
    </table>
    <div class="btn">
        <div class="back">
          <?php echo $html->backLink('<img title="'._zm('Continue Shopping ').'" alt="'._zm('Continue Shopping').'" src="'.$this->asUrl('images/button_continue_shopping.gif').'">') ?>
          <input type="image" title="<?php _vzm(' Change your quantity by highlighting the number in the box, correcting the quantity and clicking this button. ') ?>" alt="<?php _vzm('Change your quantity by highlighting the number in the box, correcting the quantity and clicking this button.') ?>" src="<?php echo $this->asUrl('images/button_update_cart.gif') ?>">
        </div>
        <div class="forward"><a class="btn" href="<?php echo $net->url('checkout_shipping', '', true) ?>"><img title="<?php _vzm(' Checkout ') ?>" alt="<?php _vzm('Checkout') ?>" src="<?php echo $this->asUrl('images/button_checkout.gif') ?>"></a></div>
        <div class="clearBoth"></div>
    </div>
    <div>
        <a class="btn" href="<?php echo $net->url('popup_shipping_estimator', '', true) ?>" onclick="popupWindow(this.href); return false;"><img title="<?php _vzm(' Shipping Estimator ') ?>" alt="<?php _vzm('Shipping Estimator') ?>" src="<?php echo $this->asUrl('images/button_shipping_estimator.gif') ?>"></a>
    </div>

    <?php echo $this->fetchBlockGroup('shoppingCart.options') ?>
</form>

