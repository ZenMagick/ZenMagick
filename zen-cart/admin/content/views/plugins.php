<?php
/*
 * ZenMagick - Smart e-commerce
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
<?php

    $install = $request->getParameter('install');
    $remove = $request->getParameter('remove');
    $edit = $request->getParameter('edit');
    $group = $request->getParameter('group');
    $select = $request->getParameter('select');
    $refresh = '';
    $needRefresh = false;
    if (null != $install) {
        if (null != ($plugin = ZMPlugins::instance()->initPluginForId($install, false)) && !$plugin->isInstalled()) {
            $plugin->install();
            ZMMessages::instance()->addAll($plugin->getMessages());
        }
        $edit = $install;
        $editPlugin = $plugin;
        $needRefresh = true;
        $refresh = $edit;
    } else if (null != $remove) {
        if (null != ($plugin = ZMPlugins::instance()->initPluginForId($remove, false)) && $plugin->isInstalled()) {
            $plugin->remove();
            ZMMessages::instance()->addAll($plugin->getMessages());
        }
        $needRefresh = true;
    } else if (null != $edit) {
        $editPlugin = ZMPlugins::instance()->initPluginForId($edit, false);
    } else if (null != $select) {
        $edit = $select;
        $editPlugin = ZMPlugins::instance()->initPluginForId($select, false);
    }

    // update
    if ('POST' == $request->getMethod() && null !== ($pluginId = $request->getParameter('pluginId'))) {
        $plugin = ZMPlugins::instance()->initPluginForId($pluginId, false);
        foreach ($plugin->getConfigValues() as $widget) {
            if ($widget instanceof ZMFormWidget && null !== ($value = $request->getParameter($widget->getName()))) {
                if (!$widget->compare($value)) {
                    // value changed, use widget to (optionally) format value
                    $widget->setValue($value);
                    $plugin->set($widget->getName(), $widget->getStringValue());
                }
            }
        }
        $refresh = $pluginId;
        $needRefresh = true;
        $editPlugin = $plugin;
    }

    if ($needRefresh) {
        $fragment = '';
        if ($editPlugin) {
            $fragment = '#' . $editPlugin->getId();
        }
        $request->redirect($toolbox->admin->url(null, 'select='.$refresh.$fragment, true));
    }

    // build/update plugin status for all plugins
    $pluginStatus = array();
    foreach (ZMPlugins::instance()->getAllPlugins(0, false) as $group => $plugins) {
        foreach ($plugins as $plugin) {
            $pluginStatus[$plugin->getId()] = array(
                'group' => $plugin->getGroup(),
                'scope' => $plugin->getScope(),
                'installed' => $plugin->isInstalled(),
                'enabled' => $plugin->isEnabled(),
                'context' => $plugin->getContext(),
                'order' => $plugin->getSortOrder()
            );
        }
    }
    // update in db
    ZMConfig::instance()->updateConfigValue('ZENMAGICK_PLUGIN_STATUS', serialize($pluginStatus));

?>

<script type="text/javascript">
    var statusImgOn = 'images/icons/tick.gif';
    var statusImgOff = 'images/icons/cross.gif';

    function toggle_status(link) {
        var currentStatus = link.className.split('-')[2];
        var pluginId = link.id.split('-')[1];
        $.ajax({
            type: "POST",
            url: "<?php echo $net->ajax('plugin_admin', 'setPluginStatus') ?>",
            data: 'pluginId='+pluginId+'&status='+('on' == currentStatus ? 'false' : 'true'),
            success: function(msg) { 
                // this just means the call was sucessful, not that the update worked!
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

<?php foreach (ZMPlugins::instance()->getAllPlugins(0, false) as $group => $plugins) { ?>
  <h2><?php echo $group ?> plugins</h2>
  <form action="<?php echo $toolbox->admin->url() ?>" method="POST" onsubmit="return zm_user_confirm('Save plugin changes ?');">
    <table cellpadding="5" cellspacing="0" style="width:90%;"> 
      <thead>
        <tr>
          <th><?php _vzm("Name") ?></th>
          <th style="width:45%;"><?php _vzm("Description") ?></th>
          <th style="width:13em;"><?php _vzm("Status") ?></th>
          <th style="width:3em;"><?php _vzm("Order") ?></th>
          <th style="width:160px;"><?php _vzm("Options") ?></th>
        </tr>
      </thead>
      <tbody>
        <?php $odd = true; foreach ($plugins as $plugin) { $isEdit = (null != $edit && null != $editPlugin && $plugin->getId() == $editPlugin->getId()); $odd = !$odd; ?>
<tr<?php echo ($isEdit ? ' class="edit"' : '') ?><?php if ($odd) { echo ' style="background-color:#ddd;"'; } ?>>
            <td><a name="<?php echo $plugin->getId() ?>"></a><?php echo $plugin->getName() ?></td>
            <td><?php echo $plugin->getDescription() ?></td>
            <td style="text-align:center;">
                <?php if ($plugin->isInstalled()) { ?>
                  <a href="#<?php echo $plugin->getId() ?>" onclick="toggle_status(this); return false;" id="status-<?php echo $plugin->getId() ?>" class="plugin-status-<?php echo ($plugin->isEnabled() ? 'on' : 'off') ?>"><img border="0" src="images/icons/<?php echo ($plugin->isEnabled() ? 'tick.gif' : 'cross.gif') ?>"></a>
                <?php } else { ?>
                  N/A
                <?php } ?>
            </td>
            <td><?php echo $plugin->getSortOrder() ?></td>
            <td>
              <?php if ($plugin->isInstalled()) { ?>
                  <a href="<?php echo $toolbox->admin->url(null, 'remove='.$plugin->getId().'&group='.$plugin->getGroup()) ?>" onclick="return zm_user_confirm('This will remove all stored settings.\nContinue?');"><img src="includes/languages/english/images/buttons/button_module_remove.gif" alt="Remove"></a>
                  <?php if ($isEdit) { ?>
                    <input type="hidden" name="pluginId" value="<?php echo $plugin->getId() ?>">
                    <input type="hidden" name="group" value="<?php echo $plugin->getGroup() ?>">
                    <?php echo zen_image_submit('button_update.gif', IMAGE_UPDATE) ?>
                  <?php } else { ?>
                      <a href="<?php echo $toolbox->admin->url(null, 'edit='.$plugin->getId().'&group='.$plugin->getGroup()) ?>#<?php echo $plugin->getId() ?>"><img src="includes/languages/english/images/buttons/button_edit.gif" alt="Edit"></a>
                  <?php } ?>
              <?php } else { ?>
                  <a href="<?php echo $toolbox->admin->url(null, 'install='.$plugin->getId().'&group='.$plugin->getGroup()) ?>#<?php echo $plugin->getId() ?>"><img src="includes/languages/english/images/buttons/button_module_install.gif" alt="Install"></a>
              <?php } ?>
            </td>
          </tr>
          <?php if ($isEdit) { ?>
            <?php foreach ($plugin->getConfigValues(false) as $value) { ?>
              <tr<?php echo ($isEdit ? ' class="edit"' : '') ?>>
                  <?php /* TODO: remove to allow only widget! */ ?>
                  <?php if ($value instanceof ZMWidget) { if ($value->isHidden()) { continue; } ?>
                    <td><?php echo $value->getTitle() ?></td>
                    <td><?php echo $value->getDescription() ?></td>
                    <td colspan="3">
                      <?php echo $value->render($request) ?>
                    </td>
                  <?php } else { ?>
                    <td><?php echo $value->getName() ?></td>
                    <td><?php echo $value->getDescription() ?></td>
                    <td colspan="3">
                      <?php if ($value->hasSetFunction()) { ?>
                        <?php eval('$set = ' . $value->getSetFunction() . "'" . $value->getValue() . "', '" . $value->getKey() . "');"); ?>
                        <?php echo str_replace('<br>', '', $set) ?>
                      <?php } else { ?>
                        <?php echo zen_draw_input_field($value->getKey(), $value->getValue()); ?>
                      <?php } ?>
                    </td>
                  <?php } ?>
              </tr>
            <?php } ?>
          <?php } ?>
        <?php } ?>
      </tbody>
    </table>
  </form>
<?php } ?>
