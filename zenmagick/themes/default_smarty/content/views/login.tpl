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
 * $Id: login.php 299 2007-08-20 01:09:29Z DerManoMann $
 */
?>

{$zm->secure_form($smarty.const.FILENAME_LOGIN, "action=process", 'login', 'post', 'return validate(this);')}
  <fieldset>
    <legend>{$zm->l10n("Login")}</legend>
    <div>
      <label for="email_address">{$zm->l10n("E-Mail Address")}</label>
      <input type="text" id="email_address" name="email_address" {$zm->field_length($smarty.const.TABLE_CUSTOMERS, 'customers_email_address')} /> 
    </div>
    <div>
      <label for="password">{$zm->l10n("Password")}</label>
      <input type="password" id="password" name="password" {$zm->field_length($smarty.const.TABLE_CUSTOMERS, 'customers_password')} /> 
    </div>
  </fieldset>
  <div class="btn"><input type="submit" class="btn" value="{$zm->l10n("Submit")}" /></div>
</form>

<p>
  <a href="{$zm->secure_href($smarty.const.FILENAME_PASSWORD_FORGOTTEN)}">{$zm->l10n("Lost your password?")}</a><br />
  <a href="{$zm->secure_href($smarty.const.FILENAME_CREATE_ACCOUNT)}">{$zm->l10n("Not registered yet?")}</a>
</p>

{if (ZMSettings::get('isGuestCheckout') && !$zm_cart->isEmpty())}
  <h3>{$zm->l10n("Don't need an account?")}</h3>
  {$zm->secure_form($smarty.const.ZM_FILENAME_CHECKOUT_GUEST, "action=process", 'checkout_guest', 'post', 'return validate(this);')}
    <fieldset>
      <legend>{$zm->l10n("Checkout without registering")}</legend>
      <div>
        <label for="email_address_guest">{$zm->l10n("E-Mail Address")}</label>
        <input type="text" id="email_address_guest" name="email_address" {$zm->field_length($smarty.const.TABLE_CUSTOMERS, 'customers_email_address')} /> 
        <input type="submit" class="btn" value="{$zm->l10n("Checkout")}" />
      </div>
    </fieldset>
  </form>
{/if}
