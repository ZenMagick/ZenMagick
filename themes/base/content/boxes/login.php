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

<?php if ($request->isAnonymous() && 'login' != $request->getRequestId() && 'time_out' != $request->getRequestId()) { ?>
    <h3><?php _vzm("Login") ?></h3>
    <div id="sb_login" class="box">
        <?php echo $form->open('login', '', true, array('id'=>'login')) ?>
            <div>
                <label for="email_address"><?php _vzm("E-Mail Address") ?></label>
                <input type="text" id="email_address" name="email_address" <?php echo $form->fieldLength(TABLE_CUSTOMERS, 'customers_email_address', 20) ?> />
            </div>
            <div>
                <label for="password"><?php _vzm("Password") ?></label>
                <input type="submit" class="btn" value="<?php _vzm("Login") ?>" />
                <input type="password" id="password" name="password" <?php echo $form->fieldLength(TABLE_CUSTOMERS, 'customers_password', 9) ?> />
            </div>
        </form>
        <div>
            <a href="<?php echo $net->url('password_forgotten', '', true) ?>"><?php _vzm("Lost your password?") ?></a>
            <a href="<?php echo $net->url('create_account', '', true); ?>"><?php _vzm("Not registered yet?") ?></a>
        </div>
    </div>
<?php } ?>
