<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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

<?php zm_title($this, _zm('Mange Roles')) ?>
<form action="<?php echo $admin2->url() ?>" method="POST" id="manage_roles">
  <fieldset>
    <p>
      <strong><?php _vzm('SELECT ALL TO UPDATE') ?></strong>
      <label for="mr_roles"><?php _vzm('Roles') ?></label>
      <select name="roles[]" id="mr_roles" multiple>
      <?php foreach ($roles as $role) { ?>
        <option value="<?php echo $role ?>"><?php echo ucwords($role) ?></option>
      <?php } ?>
      </select>
      <input type="submit" value="<?php _vzm("Update Roles (select roles to keep)") ?>">
      <a href="#" onclick="removeSelected(); return false"><?php _vzm('Remove selected') ?></a>
    </p>
    <p><label for="newRole"><?php _vzm('Add Role') ?></label> <input type="text" id="newRole" name="newRole" value=""> <input type="submit" value="<?php _vzm("Add Role") ?>" onclick="addRoleToList(); return false;"></p>
  </fieldset>
</form>

<table>
  <tr>
    <th><?php _vzm('Request Id') ?></th>
    <?php foreach ($roles as $role) { ?>
    <th><?php echo ucwords($role) ?></th>
    <?php } ?>
  </tr>
  <?php foreach ($mappings as $requestId => $mapping) { if (!is_array($mapping['roles'])) { $mapping = $defaultMapping; } ?>
    <tr>
      <td><?php echo $requestId ?></td>
      <?php foreach ($roles as $role) { ?>
        <td><?php echo (in_array($role, $mapping['roles']) ? _zm('Yup') : _zm('Nope')) ?></td>
      <?php } ?>
    </tr>
  <?php } ?>
</table>
