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

<?php if (null != $request->getProductId()) { ?>
    <h3><?php _vzm("Tell A Friend") ?></h3>
    <div id="sb_tellafriend" class="box">
        <?php echo $form->open('tell_a_friend', '', $request->isSecure(), array('method' => 'get')) ?>
            <div>
              <input type="hidden" name="products_id" value="<?php echo $request->getProductId() ?>" />
              <input type="submit" class="btn" value="<?php _vzm("Email") ?>" />
              <?php $onfocus = "if(this.value=='" . _zm("enter email") . "') this.value='';" ?>
              <input type="text" id="to_email_address" name="to_email_address" value="<?php _vzm("enter email") ?>" onfocus="<?php echo $onfocus ?>" />
            </div>
        </form>
        <p><?php _vzm("Tell someone you know about this product.") ?></p>
    </div>
<?php } ?>
