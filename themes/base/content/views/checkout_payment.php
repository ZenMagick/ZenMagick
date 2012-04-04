<?php
/*
 * ZenMagick - Smart e-commerce
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

<?php $crumbtrail->addCrumb(_zm('Checkout'), $request->url('checkout_payment', '', true))->addCrumb(_zm('Payment Method')) ?>
<fieldset>
    <legend><?php _vzm("Billing Address") ?></legend>
    <div class="btn">
        <a class="btn" href="<?php echo $net->url('checkout_payment_address', '', true) ?>"><?php _vzm("Change Billing Address") ?></a>
    </div>
    <?php echo $macro->formatAddress($shoppingCart->getBillingAddress()) ?>
</fieldset>

<script type="text/javascript">var submitter = 0;</script>
<?php echo $shoppingCart->getPaymentFormValidationJS($request) ?>

<?php echo $form->open('checkout_confirmation', '', true, array('id'=>'checkout_payment', 'onsubmit' => 'return check_form();')) ?>
  <?php if ($settingsService->get('isConditionsMessage')) { ?>
      <fieldset>
          <legend><?php _vzm("Terms and Conditions") ?></legend>
          <p>
              <?php _vzm("Please acknowledge the terms and conditions bound to this order by ticking the following box.") ?></br>
              <?php $href = '<a href="' . $net->staticPage('conditions') . '">' . _zm("here") . '</a>'; ?>
              <?php _vzm("The terms and conditions can be read %s.", $href) ?></p>
          <p><input type="checkbox" id="conditions" name="conditions" value="1" /><label for="conditions"><?php _vzm("I have read and agreed to the terms and conditions bound to this order.") ?></label></p>
      </fieldset>
  <?php } ?>
  <fieldset>
      <legend><?php _vzm("Order Totals") ?></legend>
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
          <legend><?php _vzm("Credit Options") ?></legend>
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
      <legend><?php _vzm("Payment Options") ?></legend>
  <?php
      $paymentTypes = $shoppingCart->getPaymentTypes();
      $single = 1 == count($paymentTypes);
      foreach ($paymentTypes as $type) {
        $sptid = 'pt_'.$type->getId();
        if ($single) {
          ?><p><input type="hidden" id="<?php echo $sptid ?>" name="payment" value="<?php echo $type->getId() ?>" /><?php
        } else {
          ?><p class="paytype" onclick="document.getElementById('<?php echo $sptid ?>').checked = true;"><input type="radio" id="<?php echo $sptid ?>" name="payment" value="<?php echo $type->getId() ?>"<?php $form->checked($shoppingCart->getSelectedPaymentTypeId(), $type->getId()) ?> /><?php
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
      <legend><?php _vzm("Comments") ?></legend>
      <p class="inst"><?php _vzm("Special instructions or comments about your order.") ?></p>
      <?php /* Fix for IE bug regarding textarea... */ ?>
      <table><tr><td><textarea name="comments" rows="3" cols="45"><?php echo $html->encode($shoppingCart->getComments()) ?></textarea></td></tr></table>
  </fieldset>

  <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Continue") ?>" /></div>
</form>
