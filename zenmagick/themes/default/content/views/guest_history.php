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

<?php $form->open(ZM_FILENAME_GUEST_HISTORY, '', true, array('id'=>'guest_history')) ?>
  <fieldset>
    <legend><?php zm_l10n("Find Guest Order") ?></legend>
    <div>
      <label for="email"><?php zm_l10n("E-Mail Address") ?></label>
      <input type="text" id="email" name="email" value="<?php $html->encode(ZMRequest::getParameter('email', '')) ?>" /> 
    </div>
    <div>
      <label for="orderId"><?php zm_l10n("Order Number") ?></label>
      <input type="text" id="orderId" name="orderId" value="<?php $html->encode(ZMRequest::getParameter('orderId', '')) ?>" /> 
    </div>
  </fieldset>
  <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Submit") ?>" /></div>
</form>
