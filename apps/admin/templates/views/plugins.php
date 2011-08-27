<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

<script type="text/javascript">
  // select/unselect all
  function sync_all(box, name) {
    var boxes = document.getElementsByTagName('input');
    for (var ii=0; ii<boxes.length; ++ii) {
      if (0 == boxes[ii].name.indexOf(name) && !boxes[ii].disabled) {
        boxes[ii].checked = box.checked;
      }
    }
  }
  // update submit form with all checked plugin ids
  function collectIds(form, name) {
    var multiPluginId = '';
    var boxes = document.getElementsByTagName('input');
    for (var ii=0; ii<boxes.length; ++ii) {
      if (0 == boxes[ii].name.indexOf(name) && !boxes[ii].disabled && boxes[ii].checked) {
        multiPluginId += boxes[ii].value + ',';
      }
    }
    if (0 == multiPluginId.length) {
        return false;
    }
    form.multiPluginId.value = multiPluginId;
    return true;
  }
</script>

<?php $admin2->title() ?>

<table class="grid">
  <?php foreach ($pluginList as $group => $plugins) { ?>
    <tr>
      <th colspan="5"><a href="<?php echo $admin2->url(null, 'group='.$group) ?>"><?php echo sprintf(_zm("%s Plugins"), ucwords(str_replace('_', ' ', $group))) ?></a></th>
    </tr>
    <tr>
      <th></th>
      <th><?php _vzm("Name") ?></th>
      <th><?php _vzm("Description") ?></th>
      <th><?php _vzm("Status") ?></th>
      <th><?php _vzm("Options") ?></th>
    </tr>
    <?php foreach ($plugins as $plugin) { ?>
      <tr>
        <td><input type="checkbox" name="multiUpdate[]" value="<?php echo $plugin->getId() ?>"></td>
        <td><a name="<?php echo $plugin->getId() ?>"></a><?php echo $plugin->getName() ?></td>
        <td><?php echo $html->encode($plugin->getDescription()) ?></td>
        <td>
          <?php if ($plugin->isInstalled()) { ?>
            <span id="plugin-<?php echo $plugin->getId() ?>" class="plugin-status ui-icon ui-icon-circle-<?php echo ($plugin->isEnabled() ? 'check enabled' : 'close disabled') ?>"></span>
          <?php } else { ?>
            N/A
          <?php } ?>
        </td>
        <td>
          <?php /** TODO: install/remove via ajax */ ?>
          <?php $msg = ($plugin->isInstalled() ? 'Remove ' : 'Install ').'plugin: '.$plugin->getName(); ?>
          <form action="<?php echo $admin2->url() ?>" method="POST" onsubmit="return ZenMagick.confirm('<?php echo $msg ?>', this);">
          <input type="hidden" name="pluginId" value="<?php echo $plugin->getId() ?>">
          <input type="hidden" name="group" value="<?php echo $plugin->getGroup() ?>">
          <?php if (!$plugin->isInstalled()) { ?>
            <input type="hidden" name="action" value="install">
            <button class="<?php echo $buttonClasses ?>" type="submit">Install</button>
          <?php } else { ?>
            <input type="hidden" name="action" value="uninstall">
            <?php $cid = 'keepSettings-'.$plugin->getId(); ?>
            <input type="checkbox" id="<?php echo $cid ?>" name="keepSettings" value="true" checked> <label for="<?php echo $cid ?>"><?php _vzm('Keep plugin options') ?></label>
            <button class="<?php echo $buttonClasses ?>" type="submit">Uninstall</button>
            <a class="<?php echo $buttonClasses ?>" href="<?php echo $admin2->url(null, 'action=upgrade&pluginId='.$plugin->getId().'&group='.$plugin->getGroup()) ?>#<?php echo $plugin->getId() ?>">Upgrade</a>
            <?php if ($plugin->hasOptions()) { /* enabled/disabled and sort order are handled by this page */ ?>
            <a class="<?php echo $buttonClasses ?>" href="<?php echo $admin2->url(null, 'ajax=false&action=edit&pluginId='.$plugin->getId().'&group='.$plugin->getGroup()) ?>#<?php echo $plugin->getId() ?>" onclick="return ZenMagick.ajaxFormDialog(this.href, {title:'<?php echo sprintf(_zm('Edit Plugin Options: %s'), $plugin->getName()) ?>', formId: 'ajax-form'});">Edit</a>
          <?php } ?>
          <?php } ?>
          </form>
        </td>
      </tr>
    <?php } ?>
  <?php } ?>
      <tr>
        <td><input type="checkbox" name="multi-update-toggle" value="" onclick="sync_all(this, 'multiUpdate[]')"></td>
        <td colspan="4">
          <form action="<?php echo $admin2->url() ?>" method="POST" onsubmit="return collectIds(this, 'multiUpdate[]');">
            <input type="hidden" name="multiPluginId" value="">
            <?php _vzm('With selected:') ?><select name="multiAction">
            <option value=""><?php _vzm(' -- Select -- ') ?></option>
            <option value="install"><?php _vzm('Install') ?></option>
            <option value="uninstall"><?php _vzm('Uninstall') ?></option>
            <option value="upgrade"><?php _vzm('Upgrade') ?></option>
            <option value="enable"><?php _vzm('Enable') ?></option>
            <option value="disable"><?php _vzm('Disable') ?></option>
          </select>
          <input type="submit" class="<?php echo $buttonClasses ?>" value="<?php _vzm('Go') ?>">
        </form>
      </tr>
</table>
<script>
$('.plugin-status').click(function() {
  var icon = this;
  var pluginStatus = $(this).hasClass('disabled');
  var pluginId = $(this).attr('id').split('-')[1];
  var data = '{"pluginId":"'+pluginId+'","status":'+pluginStatus+'}';
  ZenMagick.rpc('plugin_admin', 'setPluginStatus', data, {
      success: function(result) {
          $(icon).toggleClass('ui-icon-circle-check').toggleClass('ui-icon-circle-close')
              .toggleClass('enabled').toggleClass('disabled');
      }
  });
});
</script>
