<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
    <legend><?php zm_l10n("Billing Address") ?></legend>
    <div class="btn">
        <a class="btn" href="<?php $net->url(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', true) ?>"><?php zm_l10n("Change Billing Address") ?></a>
    </div>
    <?php $macro->formatAddress($shoppingCart->getBillingAddress()) ?>
</fieldset>

<script type="text/javascript">var submitter = 0;</script>
<?php echo $shoppingCart->getPaymentsJavaScript() ?>

<?php $form->open(FILENAME_CHECKOUT_CONFIRMATION, '', array('id'=>'checkout_payment')) ?>
  <fieldset>
      <legend><?php zm_l10n("Order Totals") ?></legend>
      <table id="ot" cellspacing="0" cellpadding="0">
          <tbody>
          <?php
              $totals = $shoppingCart->getTotals();
              foreach ($totals as $total) {
                  $tot = '';
                  if ('total' == $total->getType()) {
                      $tot = ' tot';
                  }
                  ?><tr>
                      <td class="total"><?php echo $total->getName() ?></td>
                      <td class="price<?php echo $tot ?>"><?php echo $total->getValue() ?></td>
                  </tr><?php
              }
          ?>
          </tbody>
      </table>
  </fieldset>

  <?php $creditTypes = $shoppingCart->getCreditTypes(); ?>
  <?php if (0 < count($creditTypes)) { ?>
      <fieldset>
          <legend><?php zm_l10n("Credit Options") ?></legend>
          <?php foreach ($creditTypes as $type) { ?>
              <p class="credittype"><?php echo $type->getName() ?></p>
              <div class="instr"><?php echo $type->getInstructions() ?></div>
              <table class="pt" cellpadding="0" cellspacing="0"><tbody>
                  <?php foreach ($type->getFields() as $field) { ?>
                     <tr><td><label><?php echo $field->getLabel() ?></label></td><td><?php echo $field->getHTML() ?></td></tr>
                  <?php } ?>
              </tbody></table>
          <?php } ?>
      </fieldset>
  <?php } ?>

  <fieldset id="paytypes">
      <legend><?php zm_l10n("Payment Options") ?></legend>
  <?php
      $paymentTypes = $shoppingCart->getPaymentTypes();
      $single = 1 == count($paymentTypes);
      foreach ($paymentTypes as $type) {
        $sptid = 'pt_'.$type->getId();
        if ($single) {
          ?><p><input type="hidden" id="<?php echo $sptid ?>" name="payment" value="<?php echo $type->getId() ?>" /><?php
        } else {
          ?><p class="paytype" onclick="document.getElementById('<?php echo $sptid ?>').checked = true;"><input type="radio" id="<?php echo $sptid ?>" name="payment" value="<?php echo $type->getId() ?>"<?php $form->checked($shoppingCart->getPaymentMethodId(), $type->getId()) ?> /><?php
        }
        ?><label for="<?php echo $sptid ?>"><?php echo $type->getName() ?></label></p><?php
        $fields = $type->getFields();
        if (0 < count($fields)) {
            ?><table class="pt" cellpadding="0" cellspacing="0"><tbody><?php
            foreach ($fields as $field) {
              ?><tr><td><label><?php echo $field->getLabel() ?></label></td><td><?php echo $field->getHTML() ?></td></tr><?php
            }
            ?></tbody></table><?php
          }
      }
  ?>
  </fieldset>

  <fieldset>
      <legend><?php zm_l10n("Comments") ?></legend>
      <p class="inst"><?php zm_l10n("Special instructions or comments about your order.") ?></p>
      <?php /* Fix for IE bug regarding textarea... */ ?>
      <table><tr><td><textarea name="comments" rows="3" cols="45"><?php echo $html->encode($shoppingCart->getComment()) ?></textarea></td></tr></table>
  </fieldset>

  <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Continue") ?>" /></div>
</form>
