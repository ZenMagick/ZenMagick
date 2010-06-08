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
<h1>Manage Roles</h1>

<form action="<?php echo $admin2->url() ?>" method="POST">
  <fieldset>
    <p>
      <strong>SELECT ALL TO UPDATE</strong>
      <label for="roles">Roles</label>
      <select name="roles[]" id="roles" multiple>
      <?php foreach ($roles as $role) { ?>
        <option value="<?php echo $role ?>"><?php echo ucwords($role) ?></option>
      <?php } ?>
      </select>
      <input type="submit" value="<?php zm_l10n("Update Roles (select roles to keep)") ?>">
    </p>
    <p><label for="newRole">Add Role</label> <input type="text" id="newRole" name="newRole" value=""> <input type="submit" value="<?php zm_l10n("Add Role") ?>"></p>
  </fieldset>
</form>
