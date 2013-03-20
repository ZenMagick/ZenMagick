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
<?php $view->extend('StorefrontBundle::default_layout.html.php'); ?>

<?php $view['slots']->set('crumbtrail', $crumbtrail->addCrumb(_zm('Account'), $view['router']->generate('account'))->addCrumb(_zm('Change Password'))); ?>
<?php echo $form->open('account_password', '', true, array('id'=>'account_password')) ?>
    <fieldset>
        <legend><?php _vzm("Change Password") ?></legend>
        <p>
            <label for="password_current"><?php _vzm("Current Password") ?></label>
            <input type="password" id="password_current" name="password_current" <?php echo $form->fieldLength('customers', 'customers_password') ?> />
        </p>
        <p>
            <label for="password_new"><?php _vzm("New Password") ?></label>
            <input type="password" id="password_new" name="password_new" <?php echo $form->fieldLength('customers', 'customers_password') ?> />
        </p>
        <p>
            <label for="password_confirmation"><?php _vzm("Confirm Password") ?></label>
            <input type="password" id="password_confirmation" name="password_confirmation" <?php echo $form->fieldLength('customers', 'customers_password') ?> />
        </p>
    </fieldset>
    <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Submit") ?>" /></div>
</form>
