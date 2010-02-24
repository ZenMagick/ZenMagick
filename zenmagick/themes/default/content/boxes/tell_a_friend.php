<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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

<?php if (null != $request->getProductId()) { ?>
    <h3><?php zm_l10n("Tell A Friend") ?></h3>
    <div id="sb_tellafriend" class="box">
        <?php echo $form->open(FILENAME_TELL_A_FRIEND, '', false, array('method' => 'get')) ?>
            <div>
              <input type="hidden" name="products_id" value="<?php echo $request->getProductId() ?>" />
              <input type="submit" class="btn" value="<?php zm_l10n("Email") ?>" />
              <?php $onfocus = "if(this.value=='" . zm_l10n_get("enter email") . "') this.value='';" ?>
              <input type="text" id="to_email_address" name="to_email_address" value="<?php zm_l10n("enter email") ?>" onfocus="<?php echo $onfocus ?>" />
            </div>
        </form>
        <p><?php zm_l10n("Tell someone you know about this product.") ?></p>
    </div>
<?php } ?>
