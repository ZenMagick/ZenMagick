<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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

<?php if ($zm_request->isGuest() && 'login' != $zm_request->getPageName()) { ?>
    <h3><?php zm_l10n("Login") ?></h3>
    <div id="sb_login" class="box">
        <?php zm_secure_form(FILENAME_LOGIN, "action=process") ?>
            <div>
                <label for="email_address"><?php zm_l10n("E-Mail Address") ?></label>
                <input type="text" id="email_address" name="email_address" <?php zm_field_length(TABLE_CUSTOMERS, 'customers_email_address', 0) ?> /> 
            </div>
            <div>
                <label for="password"><?php zm_l10n("Password") ?></label>
                <input type="submit" class="btn" value="<?php zm_l10n("Submit") ?>" />
                <input type="password" id="password" name="password" <?php zm_field_length(TABLE_CUSTOMERS, 'customers_password', 9) ?> /> 
            </div>
          </form>
          <div>
              <a href="<?php zm_secure_href(FILENAME_PASSWORD_FORGOTTEN) ?>"><?php zm_l10n("Lost your password?") ?></a>
              <a href="<?php zm_secure_href(FILENAME_CREATE_ACCOUNT); ?>"><?php zm_l10n("Not registered yet?") ?></a>
          </div>
    </div>
<?php } ?>
