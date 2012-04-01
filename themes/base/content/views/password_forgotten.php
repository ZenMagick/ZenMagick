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

<?php $crumbtrail->addCrumb(_zm('Password Recovery')) ?>
<p><?php _vzm("Enter your E-Mail address below and we'll send you an e-mail message containing your new password.") ?></p>
<?php echo $form->open('password_forgotten') ?>
  <fieldset>
    <legend><?php _vzm("Request new password") ?></legend>
    <label for="email_address"><?php _vzm("E-Mail Address") ?></label>
    <input type="text" id="email_address" name="email_address" <?php echo $form->fieldLength(TABLE_CUSTOMERS, 'customers_email_address') ?> />
  </fieldset>
  <div class="btn"><button type="submit" class="btn submit"><span><?php _vzm("Submit") ?></span></button></div>
</form>
