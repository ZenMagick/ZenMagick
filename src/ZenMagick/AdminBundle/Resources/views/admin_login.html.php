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
<?php $admin->title(_zm('Login')) ?>
<?php echo $form->open('admin_login_check', '', true, array('id'=>'login')) ?>

<p>
<label for="username"><?php _vzm('User Name') ?></label><br>
<input type="text" name="username" id="username" value="<?php echo $lastUsername; ?>">
</p>

<p>
<label for="password"><?php _vzm('Password') ?></label><br>
<input type="password" name="password" id="password" value="">
</p>

<p><input class="<?php echo $buttonClasses ?>" type="submit" value="<?php _vzm('Login') ?>"> <a class="<?php echo $buttonClasses ?>" href="<?php echo $view['router']->generate('admin_reset_password') ?>"><?php _vzm('Reset Password') ?></a></p>
</form>
