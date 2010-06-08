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

<script>
  // add to roles list
  function addRoleToList() {
      var role = $('#newRole').val();
      $('#manage_roles #mr_roles').append($("<option></option>").attr("value", role).text(zenmagick.ucwords(role))); 
  }

  // select all roles!!
  function fixSelect(form) {
      $('#mr_roles option').attr('selected', 'selected');
  }

  // remove selected
  function removeSelected() {
    $('#mr_roles option:selected').each(function() {
        $(this).remove();
    });
  }
</script>

<h1>Manage Roles</h1>
<form action="<?php echo $admin2->url() ?>" method="POST" id="manage_roles">
  <fieldset>
    <p>
      <strong>SELECT ALL TO UPDATE</strong>
      <label for="mr_roles">Roles</label>
      <select name="roles[]" id="mr_roles" multiple>
      <?php foreach ($roles as $role) { ?>
        <option value="<?php echo $role ?>"><?php echo ucwords($role) ?></option>
      <?php } ?>
      </select>
      <input type="submit" value="<?php zm_l10n("Update Roles (select roles to keep)") ?>">
      <a href="#" onclick="removeSelected(); return false"><?php zm_l10n('Remove selected') ?></a>
    </p>
    <p><label for="newRole">Add Role</label> <input type="text" id="newRole" name="newRole" value=""> <input type="submit" value="<?php zm_l10n("Add Role") ?>" onclick="addRoleToList(); return false;"></p>
  </fieldset>
</form>
