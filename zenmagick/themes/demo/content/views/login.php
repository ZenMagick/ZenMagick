<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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

<?php zm_secure_form(FILENAME_LOGIN, "action=process", 'login', 'post', 'return validate(this);') ?>
  <fieldset>
    <legend><?php zm_l10n("Login") ?></legend>
    <div>
      <label for="email_address"><?php zm_l10n("E-Mail Address") ?></label>
      <input type="text" id="email_address" name="email_address" <?php zm_field_length(TABLE_CUSTOMERS, 'customers_email_address') ?> /> 
      <?php zm_field_messages('email_address') ?>
    </div>
    <div>
      <label for="password"><?php zm_l10n("Password") ?></label>
      <input type="password" id="password" name="password" <?php zm_field_length(TABLE_CUSTOMERS, 'customers_password') ?> /> 
      <?php zm_field_messages('password') ?>
    </div>
  </fieldset>
  <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Submit") ?>" /></div>
</form>

<p>
  <a href="<?php zm_secure_href(FILENAME_PASSWORD_FORGOTTEN) ?>"><?php zm_l10n("Lost your password?") ?></a><br />
  <a href="<?php zm_secure_href(FILENAME_CREATE_ACCOUNT); ?>"><?php zm_l10n("Not registered yet?") ?></a>
</p>

<?php if (zm_setting('isGuestCheckout') && !$zm_cart->isEmpty()) { ?>
  <h3><?php zm_l10n("Don't need an account?") ?></h3>
  <?php zm_secure_form(ZM_FILENAME_CHECKOUT_GUEST, "action=process", 'checkout_guest', 'post', 'return validate(this);') ?>
    <fieldset>
      <legend><?php zm_l10n("Checkout without registering") ?></legend>
      <div>
        <label for="email_address_guest"><?php zm_l10n("E-Mail Address") ?></label>
        <input type="text" id="email_address_guest" name="email_address" <?php zm_field_length(TABLE_CUSTOMERS, 'customers_email_address') ?> /> 
        <?php zm_field_messages('email_address') ?>
        <input type="submit" class="btn" value="<?php zm_l10n("Checkout") ?>" />
      </div>
    </fieldset>
  </form>
<?php } ?>
