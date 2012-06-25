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

<?php $crumbtrail->addCrumb(_zm('Login')) ?>
<?php echo $form->open('login', '', true, array('id'=>'login')) ?>
  <fieldset>
    <legend><?php _vzm("Login") ?></legend>
    <table cellspacing="0" cellpadding="0">
	    <tr>
	      <td class="label"><?php _vzm("E-Mail Address") ?></td>
	      <td><input type="text" id="email_address" name="email_address" <?php echo $form->fieldLength('customers', 'customers_email_address') ?> /></td>
	    </tr>
	    <tr>
	      <td><?php _vzm("Password") ?></td>
	      <td><input type="password" id="password" name="password" <?php echo $form->fieldLength('customers', 'customers_password') ?> /></td>
	    </tr>
    </table>
  </fieldset>
  <div class="btnwrapper"><input type="submit" class="btn" value="<?php _vzm("Submit") ?>" /></div>
</form>

<p>
  <a href="<?php echo $net->url('password_forgotten', '', true) ?>"><?php _vzm("Lost your password?") ?></a><br />
  <a href="<?php echo $net->url('create_account', '', true); ?>"><?php _vzm("Not registered yet?") ?></a>
</p>

<?php if ($settingsService->get('isGuestCheckout') && !$request->getShoppingCart()->isEmpty() && $request->isAnonymous()) { ?>
  <h3><?php _vzm("Don't need an account?") ?></h3>
  <?php echo $form->open('checkout_guest', '', true, array('id'=>'checkout_guest')) ?>
    <fieldset>
      <legend><?php _vzm("Checkout without registering") ?></legend>
      <div>
        <label for="email_address_guest"><?php _vzm("E-Mail Address") ?></label>
        <input type="text" id="email_address_guest" name="email_address" <?php echo $form->fieldLength('customers', 'customers_email_address') ?> />
        <input type="submit" class="btn" value="<?php _vzm("Checkout") ?>" />
      </div>
    </fieldset>
  </form>
<?php } ?>
