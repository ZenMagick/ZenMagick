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

<h2><?php _vzm("Redeem A Gift Certificate") ?></h2>
<?php if ($gvRedeem->isRedeemed()) { ?>
  <p><?php _vzm("Congratulations, you have redeemed a Gift Certificate worth %s.", $utils->formatMoney($gvRedeem->getAmount())) ?></p>
<?php } else { ?>
  <?php echo $form->open('gv_redeem', '', true, array('id'=>'gv_redeem')) ?>
    <fieldset>
      <legend><?php _vzm("Redemption code details") ?></legend>
      <div>
        <label for="gvCode"><?php _vzm("Redemption Code") ?></label>
        <input type="text" id="gvCode" name="couponCode" value="<?php echo $html->encode($gvRedeem->getCouponCode()) ?>" />
      </div>
    </fieldset>
    <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Redeem") ?>" /></div>
  </form>
<?php } ?>
<?php $faqLink = '<a href="'.$net->url('gv_faq').'">'._zm("Gift Certificate FAQ").'</a>'; ?>
<p><?php _vzm("For more information regarding Gift Certificates, please see the %s.", $faqLink) ?></p>
