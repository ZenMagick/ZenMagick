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
 */
?>
<h1>Update User Details</h1>
<p>Groups: <?php echo implode(', ', $request->getUser()->getRoles()) ?></p>
<form action="<?php echo $admin2->url() ?>" method="POST" id="updateUser">
  <fieldset>
    <p><label for="name">Name</label> <input type="text" id="name" name="name" value="<?php echo $html->encode($updateUser->getName()) ?>"></p>
    <p><label for="email">Email</label> <input type="text" id="email" name="email" value="<?php echo $html->encode($updateUser->getEmail()) ?>"></p>
    <p><label for="currentPassword">Current password</label> <input type="password" id="currentPassword" name="currentPassword"></p>
    <p><label for="newPassword">New password</label> <input type="password" id="newPassword" name="newPassword"></p>
    <p><label for="confirmPassword">Confirm password</label> <input type="password" id="confirmPassword" name="confirmPassword"></p>
  </fieldset>
  <p><input type="submit" value="<?php _vzm("Update") ?>">
</form>
