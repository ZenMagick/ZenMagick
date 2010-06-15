<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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

<h1><?php _vzm('Edit User Details') ?></h1>
<form action="<?php echo $admin2->url() ?>" method="POST">
  <input type="hidden" name="adminUserId" value="<?php echo $adminUser->getAdminUserId() ?>">
  <fieldset>
    <p><label for="name"><?php _vzm('Name') ?></label> <input type="text" id="name" name="name" value="<?php echo $html->encode($adminUser->getName()) ?>"></p>
    <p><label for="email"><?php _vzm('Email') ?></label> <input type="text" id="email" name="email" value="<?php echo $html->encode($adminUser->getEmail()) ?>"></p>
    <p>
      <label for="roles"><?php _vzm('Roles') ?></label>
      <select name="roles[]" id="roles" multiple>
      <?php foreach ($roles as $role) { ?>
        <option value="<?php echo $role ?>"<?php if (in_array($role, $adminUser->getRoles())) { echo 'selected'; } ?>><?php echo ucwords($role) ?></option>
      <?php } ?>
      </select>
      <a href="<?php echo $admin2->url('manage_roles') ?>" onclick="zenmagick.ajaxFormDialog(this.href, '<?php _vzm('Admin Roles') ?>', 'manage_roles', 'fixSelect'); return false;"><?php _vzm('Manage Roles') ?></a>
    </p>
    <p><input type="checkbox" name="demo" id="demo" value="true"<?php if ($adminUser->isDemo()) { echo 'checked'; } ?>> <label for="demo"><?php _vzm('Demo User') ?></label></p>

    <p><label for="password"><?php _vzm('Password') ?></label> <input type="password" id="password" name="password"></p>
    <p><label for="confirmPassword"><?php _vzm('Confirm password') ?></label> <input type="password" id="confirmPassword" name="confirmPassword"></p>
  </fieldset>
  <p>
    <input type="submit" value="<?php _vzm((0 < $adminUser->getAdminUserId()) ? "Update" : "Create") ?>">
    <a href="<?php echo $admin2->url('admin_users') ?>"><?php _vzm('Cancel') ?></a>
  </p>
</form>
