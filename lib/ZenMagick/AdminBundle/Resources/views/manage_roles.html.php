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
<?php $admin->title(_zm('Manage Roles')) ?>
<form action="<?php echo $net->generate('manage_roles') ?>" method="POST" id="manage-roles-form">
  <fieldset>
    <p>
      <label for="roles"><?php _vzm('Roles') ?></label>
      <select name="roles[]" id="roles" multiple size="3">
      <?php foreach ($roles as $role) { ?>
        <option value="<?php echo $role ?>"><?php echo $role ?></option>
      <?php } ?>
      </select>
      <a id="edit-role" class="<?php echo $buttonClasses ?>" href="#"><?php _vzm('Edit permissions') ?></a>
      <input id="remove-roles" class="<?php echo $buttonClasses ?>" type="submit" value="<?php _vzm('Remove selected') ?>">
    </p>
  </fieldset>
</form>
<form action="<?php echo $net->generate('manage_roles') ?>" method="POST" id="add-role-form">
  <fieldset>
    <p><label for="roleName"><?php _vzm('Add Role') ?></label> <input type="text" id="roleName" name="roleName" value=""> <input class="<?php echo $buttonClasses ?>" type="submit" value="<?php _vzm("Add Role") ?>"></p>
  </fieldset>
</form>

<script>
$('#add-role-form').submit(function() {
  var roleName = $('#roleName').val();
  var data = '{"roleName":"'+roleName+'"}';
  ZenMagick.rpc('sacs_admin', 'addRole', data, {
      success: function(result) {
          $('#manage-roles-form #roles').append($("<option></option>").attr("value", roleName).text(roleName));
          //TODO: select
      },
      failure: function(error) {
          for (var ii in error.data.messages.error) {
            var msg = error.data.messages.error[ii];
            alert(msg);
          }
      }
  });
  return false;
});
$('#remove-roles').click(function() {
  var removeRoles = [];
  $('#roles option:selected').each(function() {
      removeRoles.push($(this).text());
  });
  var data = '{"roles":["'+removeRoles.join('", "')+'"]}';
  ZenMagick.rpc('sacs_admin', 'removeRoles', data, {
      success: function(result) {
        $('#roles option:selected').each(function() {
            $(this).remove();
        });
      },
      failure: function(error) {
          for (var ii in error.data.messages.error) {
            var msg = error.data.messages.error[ii];
            alert(msg);
          }
      }
  });
  return false;
});
$('#edit-role').click(function() {
  var roles = [];
  $('#roles option:selected').each(function() {
      roles.push($(this).text());
  });
  if (0 < roles.length) {
      var role = roles.pop();
      ZenMagick.ajaxFormDialog('<?php echo $net->generate('edit_role') ?>&role='+role, {title:'<?php echo _zm('Edit role permissions: ') ?>'+role, formId: 'ajax-form'});
  }
  return false;
});
</script>
