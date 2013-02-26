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

use ZenMagick\Base\Toolbox;

?>

<?php $crumbtrail->addCrumb(_zm('Checkout'), $net->url('checkout'))->addCrumb(_zm('Summary')) ?>
<fieldset>
    <legend><?php _vzm("Shopping Cart Contents") ?></legend>
    <table cellpadding="0" cellspacing="0" id="cart">
        <tbody>
        <?php foreach ($shoppingCart->getItems() as $item) { ?>
            <tr>
                <td class="itm">
                    <?php echo $item->getQuantity() ?> x <?php echo $html->encode($item->getProduct()->getName()) ?>
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
            </tr>
        <?php } ?>
          <?php
              $totals = $shoppingCart->getTotals();
              foreach ($totals as $total) {
                  $tot = '';
                  if ('total' == $total->getType()) {
                      $tot = ' tot';
                  }
                  ?><tr><td class="total"><?php echo $html->encode($total->getName()) ?></td><td class="price<?php echo $tot ?>"><?php echo $total->getValue() ?></td></tr><?php
              }
          ?>

        </tbody>
    </table>
</fieldset>

<fieldset>
    <legend><?php _vzm("Payment Method") ?></legend>
    <div class="btn"><a class="btn" href="<?php echo $net->url('checkout_payment') ?>"><?php _vzm("Change") ?></a></div>
    <?php $paymentType = $shoppingCart->getSelectedPaymentType() ?>
    <?php if (null != $paymentType) { ?>
      <h4><?php echo $paymentType->getName() ?></h4>
      <?php if (!Toolbox::isEmpty($paymentType->getInfo())) { ?>
        <p><?php echo nl2br($paymentType->getInfo()) ?></p>
      <?php } ?>
    <?php } ?>
</fieldset>

<?php if (!$shoppingCart->isVirtual()) { ?>
    <fieldset>
        <legend><?php _vzm("Shipping") ?></legend>
        <div class="btn"><a class="btn" href="<?php echo $net->url('checkout_shipping') ?>"><?php _vzm("Change") ?></a></div>
        <br/>
        <?php if (null != ($shippingMethod = $shoppingCart->getSelectedShippingMethod())) { ?>
          <?php echo $html->encode($shippingMethod->getProvider()->getName()) . ': ' . $html->encode($shippingMethod->getName()) ?><br/>
        <?php } ?>
    </fieldset>
<?php } ?>

<fieldset>
    <legend><?php _vzm("Address Information") ?></legend>
    <?php if ($shoppingCart->hasShippingAddress()) { ?>
        <div class="oadr">
            <div class="btn"><a class="btn" href="<?php echo $net->url('checkout_shipping_address') ?>"><?php _vzm("Change") ?></a></div>
            <h4><?php _vzm("Shipping Address") ?></h4>
            <?php echo $macro->formatAddress($shoppingCart->getShippingAddress()) ?>
        </div>
    <?php } else { ?>
        <div class="oadr">
            <h4><?php _vzm("Shipping Address") ?></h4>
            <?php _vzm("N/A") ?>
        </div>
    <?php } ?>
    <div class="oadr snd">
        <div class="btn"><a class="btn" href="<?php echo $net->url('checkout_payment_address') ?>"><?php _vzm("Change") ?></a></div>
        <h4><?php _vzm("Billing Address") ?></h4>
        <?php echo $macro->formatAddress($shoppingCart->getBillingAddress()) ?>
    </div>
</fieldset>

<fieldset>
    <legend><?php _vzm("Special instructions or comments") ?></legend>
    <div class="btn"><a class="btn" href="<?php echo $net->url('checkout_payment') ?>"><?php _vzm("Change") ?></a></div>
    <div><?php echo $html->encode(!Toolbox::isEmpty($shoppingCart->getComments()) ? $shoppingCart->getComments() : "None") ?></div>
</fieldset>

<?php echo $form->open($orderFormUrl, '', true) ?>
    <?php echo $orderFormContent ?>
    <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Confirm to order") ?>" /></div>
</form>
