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
 * $Id: zmPlugins.php 2647 2009-11-27 00:30:20Z dermanomann $
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
            data: 'pluginId='+pluginId+'&status='+('on' == currentStatus ? 'false' : 'true'),
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

    function edit_plugin(elem, name) {
			  var url = elem.href;
        $('<div id="ajax-dialog">Loading...</div>').dialog({
            modal: true,
            title: 'Edit Plugin Options: '+name,
            width: 560,
            close: function() {
                $(this).dialog("destroy");
                $('#ajax-dialog').remove();
            }
        }).load(url);
		}

</script>

<table>
  <?php foreach ($pluginList as $group => $plugins) { ?>
    <tr class="head">
      <th colspan="5"><?php zm_l10n("%s Plugins", ucwords(str_replace('_', ' ', $group))) ?></th>
    </tr>
    <tr>
      <th><?php zm_l10n("Name") ?></th>
      <th><?php zm_l10n("Description") ?></th>
      <th><?php zm_l10n("Status") ?></th>
      <th><?php zm_l10n("Order") ?></th>
      <th><?php zm_l10n("Options") ?></th>
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
            <input type="checkbox" id="<?php echo $cid ?>" name="keepSettings" value="true" checked> <label for="<?php echo $cid ?>"><?php zm_l10n('Keep Settings') ?></label>
            <button type="submit">Uninstall</button>
            <a href="<?php echo $admin2->url(null, 'action=upgrade&pluginId='.$plugin->getId().'&group='.$plugin->getGroup()) ?>#<?php echo $plugin->getId() ?>">Upgrade</a>
            <?php if (2 < count($plugin->getConfigValues())) { /* enabled/disabled and sort order are handled by this page */ ?>
            <a href="<?php echo $admin2->url(null, 'action=edit&pluginId='.$plugin->getId().'&group='.$plugin->getGroup()) ?>#<?php echo $plugin->getId() ?>" onclick="edit_plugin(this, '<?php echo $plugin->getName() ?>'); return false;">Edit</a>
          <?php } ?>
          <?php } ?>
          </form>
        </td>
      </tr>
    <?php } ?>
  <?php } ?>
</table>
