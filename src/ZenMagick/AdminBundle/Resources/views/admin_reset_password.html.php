<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
<?php $view->extend('AdminBundle::default_layout.html.twig'); ?>
<?php $admin->title(_zm('Reset Password')) ?>

<?php echo $form->open('admin_reset_password', '', true, array('id'=>'reset_password')) ?>
<p><?php _vzm('Please enter your admin email address and a new password will be emailed to you.') ?></p>

<p>
<label for="email"><?php _vzm('Email') ?></label><br>
<input type="text" name="email" id="email">
</p>

<p>
  <input class="<?php echo $buttonClasses ?>" type="submit" value="<?php _vzm('Reset') ?>">
  <a class="<?php echo $buttonClasses ?>" href="<?php echo $view['router']->generate('admin_login') ?>"><?php _vzm('Back to Login') ?></a>
</p>
</form>
