<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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

<h2><?php zm_l10n("Redeem A Gift Certificate") ?></h2>
<?php if ($zm_gvredeem->isRedeemed()) { ?>
  <p><?php zm_l10n("Congratulations, you have redeemed a Gift Certificate worth %s.", $utils->formatMoney($zm_gvredeem->getAmount(), true, false)) ?></p>
<?php } else { ?>
  <?php $form->open(FILENAME_GV_REDEEM, '', true, array('id'=>'gv_redeem')) ?>
    <fieldset>
      <legend><?php zm_l10n("Coupon details") ?></legend>
      <div>
        <label for="couponCode"><?php zm_l10n("Coupon Code") ?></label>
        <input type="text" id="couponCode" name="couponCode" value="<?php $html->encode($zm_gvredeem->getCode()) ?>" /> 
      </div>
    </fieldset>
    <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Submit") ?>" /></div>
  </form>
<?php } ?>
<p><?php zm_l10n("For more information regarding Gift Certificate, please see the Gift Certificate FAQ.") ?></p>
