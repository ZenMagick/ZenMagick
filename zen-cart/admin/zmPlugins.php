<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
<?php
// dismiss sqlpatch output as we do only want to use the code...
define('GZIP_LEVEL', 0);
ob_start(); require 'sqlpatch.php'; ob_end_clean();
require_once 'includes/application_top.php';

    $install = ZMRequest::instance()->getParameter('install');
    $remove = ZMRequest::instance()->getParameter('remove');
    $edit = ZMRequest::instance()->getParameter('edit');
    $group = ZMRequest::instance()->getParameter('group');
    $select = ZMRequest::instance()->getParameter('select');
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
        if (null != ($plugin = ZMPlugins::instance()->initPluginForId($remove, true)) && $plugin->isInstalled()) {
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
    if ('POST' == ZMRequest::instance()->getMethod() && null !== ($pluginId = ZMRequest::instance()->getParameter('pluginId'))) {
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
        ZMRequest::instance()->redirect('zmPlugins.php'.'?select='.$refresh.$fragment);
    }

    // build/update plugin status for all plugins
    $pluginStatus = array();
    foreach (ZMPlugins::instance()->getAllPlugins(false) as $group => $plugins) {
        foreach ($plugins as $plugin) {
            $pluginStatus[$plugin->getId()] = array(
                'group' => $plugin->getGroup(),
                'scope' => $plugin->getScope(),
                'installed' => $plugin->isInstalled(),
                'enabled' => $plugin->isEnabled(),
                'order' => $plugin->getSortOrder()
            );
        }
    }
    // update in db
    ZMConfig::instance()->updateConfigValue('ZENMAGICK_PLUGIN_STATUS', serialize($pluginStatus));

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
    <title><?php zm_l10n("Plugins :: ZenMagick") ?></title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <link rel="stylesheet" type="text/css" href="includes/zenmagick.css">
    <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script type="text/javascript" src="includes/menu.js"></script>
    <script type="text/javascript" src="includes/general.js"></script>
    <script type="text/javascript" src="includes/zenmagick.js"></script>
    <script type="text/javascript">
      function init() {
        cssjsmenu('navbar');
        if (document.getElementById) {
          var kill = document.getElementById('hoverJS');
          kill.disabled = true;
        }
      }
    </script>
  </head>
  <body id="b_plugins" onload="init()">
    <?php require DIR_WS_INCLUDES . 'header.php'; ?>

    <?php if (ZMMessages::instance()->hasMessages()) { ?>
        <ul id="messages">
        <?php foreach (ZMMessages::instance()->getMessages() as $message) { ?>
            <li class="<?php echo $message->getType() ?>"><?php echo $message->getText() ?></li>
        <?php } ?>
        </ul>
    <?php } ?>

    <div id="main">
      <div id="content">

        <?php foreach (ZMPlugins::instance()->getAllPlugins(false) as $group => $plugins) { ?>
        <h2><?php echo $group ?> plugins</h2>
        <form action="<?php echo 'zmPlugins.php' ?>" method="post" onsubmit="return zm_user_confirm('Save plugin changes ?');">
          <table cellpadding="5" cellspacing="0" style="width:90%;"> 
            <thead>
              <tr>
                <th><?php zm_l10n("Name") ?></th>
                <th style="width:45%;"><?php zm_l10n("Description") ?></th>
                <th style="width:13em;"><?php zm_l10n("Status") ?></th>
                <th style="width:3em;"><?php zm_l10n("Order") ?></th>
                <th style="width:160px;"><?php zm_l10n("Options") ?></th>
              </tr>
            </thead>
            <tbody>
              <?php $odd = true; foreach ($plugins as $plugin) { $isEdit = (null != $edit && null != $editPlugin && $plugin->getId() == $editPlugin->getId()); $odd = !$odd; ?>
    <tr<?php echo ($isEdit ? ' class="edit"' : '') ?><?php if ($odd) { echo ' style="background-color:#ddd;"'; } ?>>
                  <td><a name="<?php echo $plugin->getId() ?>"></a><?php echo $plugin->getName() ?></td>
                  <td><?php echo $plugin->getDescription() ?></td>
                  <td style="text-align:center;"><img src="images/icons/<?php echo ($plugin->isEnabled() ? 'tick.gif' : 'cross.gif') ?>"></td>
                  <td><?php echo $plugin->getSortOrder() ?></td>
                  <td>
                    <?php if ($plugin->isInstalled()) { ?>
                        <a href="<?php echo 'zmPlugins.php' ?>?remove=<?php echo $plugin->getId() ?>&group=<?php echo $plugin->getGroup() ?>" onclick="return zm_user_confirm('This will remove all stored settings.\nContinue?');"><?php echo zen_image_button('button_module_remove.gif', zm_l10n_get("Remove")) ?></a>
                        <?php if ($isEdit) { ?>
                          <input type="hidden" name="pluginId" value="<?php echo $plugin->getId() ?>">
                          <input type="hidden" name="group" value="<?php echo $plugin->getGroup() ?>">
                          <?php echo zen_image_submit('button_update.gif', IMAGE_UPDATE) ?>
                        <?php } else { ?>
                            <a href="<?php echo 'zmPlugins.php' ?>?edit=<?php echo $plugin->getId() ?>&group=<?php echo $plugin->getGroup() ?>#<?php echo $plugin->getId() ?>"><?php echo zen_image_button('button_edit.gif', zm_l10n_get("Edit")) ?></a>
                        <?php } ?>
                    <?php } else { ?>
                        <a href="<?php echo 'zmPlugins.php' ?>?install=<?php echo $plugin->getId() ?>&group=<?php echo $plugin->getGroup() ?>#<?php echo $plugin->getId() ?>"><?php echo zen_image_button('button_module_install.gif', zm_l10n_get("Install")) ?></a>
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
                          <td>
                            <?php echo $value->render($request) ?>
                          </td>
                        <?php } else { ?>
                          <td><?php echo $value->getName() ?></td>
                          <td><?php echo $value->getDescription() ?></td>
                          <td>
                            <?php if ($value->hasSetFunction()) { ?>
                              <?php eval('$set = ' . $value->getSetFunction() . "'" . $value->getValue() . "', '" . $value->getKey() . "');"); ?>
                              <?php echo str_replace('<br>', '', $set) ?>
                            <?php } else { ?>
                              <?php echo zen_draw_input_field($value->getKey(), $value->getValue()); ?>
                            <?php } ?>
                          </td>
                        <?php } ?>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                  <?php } ?>
                <?php } ?>
              <?php } ?>
            </tbody>
          </table>
        </form>
        <?php } ?>

      </div>
    </div>

  </body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>
