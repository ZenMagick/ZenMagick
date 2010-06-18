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

<script type="text/javascript">
    var statusImgOn = 'images/icons/tick.gif';
    var statusImgOff = 'images/icons/cross.gif';

    function toggle_status(link) {
        var currentStatus = link.className.split('-')[2];
        var pluginId = link.id.split('-')[1];
        $.ajax({
            type: "POST",
            url: "<?php echo $admin2->ajax('plugin_admin', 'setPluginStatus') ?>",
            data: 'ajax=false&pluginId='+pluginId+'&status='+('on' == currentStatus ? 'false' : 'true'),
            success: function(msg) { 
                var selector = '#'+link.id+' img';
                $('#'+link.id+' img').attr('src', 'on' == currentStatus ? statusImgOff : statusImgOn);
                link.className = 'plugin-status-'+('on' == currentStatus ? 'off' : 'on');
            },
            error: function(msg) { 
                alert(msg);
            }
        });
    }

</script>

<div id="plugin-menu" style="float:left;width:180px">
<?php
    $pluginItems = ZMAdminMenu::getItemsForParentId(ZMAdminMenu::MENU_PLUGINS);
    if (0 < count($pluginItems)) {
        echo '<h2>'._zm('Plugin Options').'</h2>';
        foreach ($pluginItems as $item) {
            if ($item instanceof ZMAdminMenuItem2) {
                echo '<a href="'.$admin2->url($item->getRequestId()).'">'.$item->getTitle().'</a><br>';
                //TODO: allow plugins to opt in:
                //echo '<a href="'.$admin2->url($item->getRequestId()).'"onclick="return zenmagick.ajaxFormDialog(this.href, \''.sprintf(_zm('Plugin Config: %s'), $item->getTitle()).'\', \'ajax-form\');">'.$item->getTitle().'</a><br>';
            }
        }
    }
?>
</div>

<table>
  <?php foreach ($pluginList as $group => $plugins) { ?>
    <tr class="head">
      <th colspan="5"><a href="<?php echo $admin2->url(null, 'group='.$group) ?>"><?php echo sprintf(_zm("%s Plugins"), ucwords(str_replace('_', ' ', $group))) ?></a></th>
    </tr>
    <tr>
      <th><?php _vzm("Name") ?></th>
      <th><?php _vzm("Description") ?></th>
      <th><?php _vzm("Status") ?></th>
      <th><?php _vzm("Order") ?></th>
      <th><?php _vzm("Options") ?></th>
    </tr>
    <?php $odd = true; foreach ($plugins as $plugin) { $odd = !$odd; ?>
      <tr<?php if ($odd) { echo ' class="odd"'; } ?>
        <td><a name="<?php echo $plugin->getId() ?>"></a><?php echo $plugin->getName() ?></td>
        <td><?php echo $html->encode($plugin->getDescription()) ?></td>
        <td>
          <?php if ($plugin->isInstalled()) { ?>
            <a href="<?php echo $admin2->url() ?>#<?php echo $plugin->getId() ?>" onclick="toggle_status(this); return false;" id="status-<?php echo $plugin->getId() ?>" class="plugin-status-<?php echo ($plugin->isEnabled() ? 'on' : 'off') ?>"><?php echo ($plugin->isEnabled() ? 'Enabled' : 'Disabled') ?></a>
          <?php } else { ?>
            N/A
          <?php } ?>
        </td>
        <td><?php echo $plugin->getSortOrder() ?></td>
        <td>
          <?php /** TODO: install/remove via ajax */ ?>
          <?php $msg = ($plugin->isInstalled() ? 'Remove ' : 'Install ').'plugin: '.$plugin->getName(); ?>
          <form action="<?php echo $admin2->url() ?>" method="POST" onsubmit="return zenmagick.confirm('<?php echo $msg ?>', this);">
          <input type="hidden" name="pluginId" value="<?php echo $plugin->getId() ?>">
          <input type="hidden" name="group" value="<?php echo $plugin->getGroup() ?>">
          <?php if (!$plugin->isInstalled()) { ?>
            <input type="hidden" name="action" value="install">
            <button type="submit">Install</button>
          <?php } else { ?>
            <input type="hidden" name="action" value="uninstall">
            <?php $cid = 'keepSettings-'.$plugin->getId(); ?>
            <input type="checkbox" id="<?php echo $cid ?>" name="keepSettings" value="true" checked> <label for="<?php echo $cid ?>"><?php _vzm('Keep Settings') ?></label>
            <button type="submit">Uninstall</button>
            <a href="<?php echo $admin2->url(null, 'action=upgrade&pluginId='.$plugin->getId().'&group='.$plugin->getGroup()) ?>#<?php echo $plugin->getId() ?>">Upgrade</a>
            <?php if ($plugin->hasOptions()) { /* enabled/disabled and sort order are handled by this page */ ?>
            <a href="<?php echo $admin2->url(null, 'ajax=false&action=edit&pluginId='.$plugin->getId().'&group='.$plugin->getGroup()) ?>#<?php echo $plugin->getId() ?>" onclick="return zenmagick.ajaxFormDialog(this.href, '<?php sprintf(_zm('Edit Plugin Options: %s'), $plugin->getName()) ?>', 'ajax-form');">Edit</a>
          <?php } ?>
          <?php } ?>
          </form>
        </td>
      </tr>
    <?php } ?>
  <?php } ?>
</table>
