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
<?php $view->extend('AdminBundle::default_layout.html.php'); ?>
<?php $admin->title(_zm('Edit User Details')) ?>

<form action="<?php echo $view['router']->generate('edit_admin_user') ?>" method="POST">
  <input type="hidden" name="adminUserId" value="<?php echo $adminUser->getAdminUserId() ?>">
  <table>
    <tr>
      <td><label for=username"><?php _vzm('Name') ?></label></td><td><input type="text" id="username" name="username" value="<?php echo $view->escape($adminUser->getUsername()) ?>"></td>
    </tr>
    <tr>
      <td><label for="email"><?php _vzm('Email') ?></label></td><td><input type="text" id="email" name="email" value="<?php echo $view->escape($adminUser->getEmail()) ?>"></td>
    </tr>
    <tr>
      <td><label for="roles"><?php _vzm('Roles') ?></label></td>
      <td>
        <?php foreach ($roles as $role) { ?>
          <input type="checkbox" name="roles[]" id="role_<?php echo $role ?>" value="<?php echo $role ?>"<?php if (in_array($role, $adminUser->getRoles())) { echo ' checked'; } ?>>
          <label for="role_<?php echo $role ?>"><?php echo ucwords($role) ?></label>
        <?php } ?>
      </td>
    </tr>
    <tr>
      <td><?php _vzm('Live Flag') ?></td>
      <td>
        <input type="checkbox" name="live" id="live" value="true"<?php if ($adminUser->isLive()) { echo 'checked'; } ?>> <label for="live"><?php _vzm('Live User') ?></label>
      </td>
    <tr>
    <tr>
      <td><label for="password"><?php _vzm('Password') ?></label></td><td><input type="password" id="password" name="password"></td>
    <tr>
    <tr>
      <td><label for="confirmPassword"><?php _vzm('Confirm password') ?></label></td><td><input type="password" id="confirmPassword" name="confirmPassword"></td>
    </tr>
  </table>
  <p>
    <input type="submit" class="<?php echo $buttonClasses ?>" value="<?php _vzm((0 < $adminUser->getAdminUserId()) ? "Update" : "Create") ?>">
    <a class="<?php echo $buttonClasses ?>" href="<?php echo $view['router']->generate('admin_users') ?>"><?php _vzm('Cancel') ?></a>
  </p>
</form>
