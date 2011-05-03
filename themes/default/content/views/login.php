<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

<?php echo $form->open(FILENAME_LOGIN, '', true, array('id'=>'login')) ?>
  <fieldset>
    <legend><?php _vzm("Login") ?></legend>
    <div>
      <label for="email_address"><?php _vzm("E-Mail Address") ?></label>
      <input type="text" id="email_address" name="email_address" <?php echo $form->fieldLength(TABLE_CUSTOMERS, 'customers_email_address') ?> /> 
    </div>
    <div>
      <label for="password"><?php _vzm("Password") ?></label>
      <input type="password" id="password" name="password" <?php echo $form->fieldLength(TABLE_CUSTOMERS, 'customers_password') ?> /> 
    </div>
  </fieldset>
  <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Submit") ?>" /></div>
</form>

<p>
  <a href="<?php echo $net->url(FILENAME_PASSWORD_FORGOTTEN, '', true) ?>"><?php _vzm("Lost your password?") ?></a><br />
  <a href="<?php echo $net->url(FILENAME_CREATE_ACCOUNT, '', true); ?>"><?php _vzm("Not registered yet?") ?></a>
</p>

<?php if (ZMSettings::get('isGuestCheckout') && !$request->getShoppingCart()->isEmpty() && $request->isAnonymous()) { ?>
  <h3><?php _vzm("Don't need an account?") ?></h3>
  <?php echo $form->open('checkout_guest', '', true, array('id'=>'checkout_guest')) ?>
    <fieldset>
      <legend><?php _vzm("Checkout without registering") ?></legend>
      <div>
        <label for="email_address_guest"><?php _vzm("E-Mail Address") ?></label>
        <input type="text" id="email_address_guest" name="email_address" <?php echo $form->fieldLength(TABLE_CUSTOMERS, 'customers_email_address') ?> /> 
        <input type="submit" class="btn" value="<?php _vzm("Checkout") ?>" />
      </div>
    </fieldset>
  </form>
<?php } ?>
