<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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

    $pluginLoader = new ZMLoader("pluginLoader");

    $install = ZMRequest::getParameter('install');
    $remove = ZMRequest::getParameter('remove');
    $edit = ZMRequest::getParameter('edit');
    $type = ZMRequest::getParameter('type');
    $select = ZMRequest::getParameter('select');
    $refresh = '';
    $needRefresh = false;
    if (null != $install) {
        $plugin = ZMPlugins::getPluginForId($install);
        if (!$plugin->isInstalled()) {
            if ('ALL' == $plugin->getLoaderSupport()) {
                $pluginLoader->addPath($plugin->getPluginDir());
                foreach ($pluginLoader->getStatic() as $static) {
                    require_once $static;
                }
                // plugins prevail over defaults, but not themes
                ZMLoader::instance()->setParent($pluginLoader);
            }
            $plugin->install();
            ZMMessages::instance()->addAll($plugin->getMessages());
        }
        $edit = $install;
        $editPlugin = $plugin;
        $needRefresh = true;
        $refresh = $edit;
    } else if (null != $remove) {
        $plugin = ZMPlugins::getPluginForId($remove);
        if ($plugin && $plugin->isInstalled()) {
            if ('ALL' == $plugin->getLoaderSupport()) {
                $pluginLoader->addPath($plugin->getPluginDir());
                foreach ($pluginLoader->getStatic() as $static) {
                    require_once $static;
                }
                // plugins prevail over defaults, but not themes
                ZMLoader::instance()->setParent($pluginLoader);
            }
            $plugin->remove();
            ZMMessages::instance()->addAll($plugin->getMessages());
        }
        $needRefresh = true;
    } else if (null != $edit) {
        $editPlugin = ZMPlugins::getPluginForId($edit);
    } else if (null != $select) {
        $edit = $select;
        $editPlugin = ZMPlugins::getPluginForId($select);
    }

    // update
    if (isset($_POST) && array_key_exists('pluginId', $_POST)) {
        while (list($key, $value) = each($_POST['configuration'])) {
            ZMConfig::instance()->updateConfigValue($key, $value);
        }
        $refresh = $_POST['pluginId'];
        $needRefresh = true;
    }

    if ($needRefresh) {
        ZMRequest::redirect('zmPlugins.php'.'?select='.$refresh);
    }

    // build/update plugin status for all plugins
    $pluginStatus = array();
    foreach (ZMPlugins::getAllPlugins(ZMPlugin::SCOPE_ALL, false) as $type => $plugins) {
        foreach ($plugins as $plugin) {
            $pluginStatus[$plugin->getId()] = array(
              'type' => $plugin->getType(),
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

        <?php foreach (ZMPlugins::getAllPlugins(ZMPlugin::SCOPE_ALL, false) as $type => $plugins) { ?>
        <h2><?php echo $type ?> plugins</h2>
        <form action="<?php echo 'zmPlugins.php' ?>" method="post" onsubmit="return zm_user_confirm('Save plugin changes ?');">
          <table cellpadding="5" cellspacing="0"> 
            <thead>
              <tr>
              <th><?php zm_l10n("Name") ?></th>
              <th><?php zm_l10n("Description") ?></th>
              <th><?php zm_l10n("Status") ?></th>
              <th><?php zm_l10n("Order") ?></th>
              <th><?php zm_l10n("Options") ?></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($plugins as $plugin) { $isEdit = (null != $edit && $plugin->getId() == $editPlugin->getId()); ?>
                <tr<?php echo ($isEdit ? ' class="edit"' : '') ?>>
                  <td><?php echo $plugin->getName() ?></td>
                  <td><?php echo $plugin->getDescription() ?></td>
                  <td style="text-align:center;"><img src="images/icons/<?php echo ($plugin->isEnabled() ? 'tick.gif' : 'cross.gif') ?>"></td>
                  <td><?php echo $plugin->getSortOrder() ?></td>
                  <td>
                    <?php if ($plugin->isInstalled()) { ?>
                        <a href="<?php echo 'zmPlugins.php' ?>?remove=<?php echo $plugin->getId() ?>&type=<?php echo $plugin->getType() ?>" onclick="return zm_user_confirm('This will remove all stored settings.\nContinue?');"><?php echo zen_image_button('button_module_remove.gif', zm_l10n_get("Remove")) ?></a>
                        <?php if ($isEdit) { ?>
                          <input type="hidden" name="pluginId" value="<?php echo $plugin->getId() ?>">
                          <input type="hidden" name="type" value="<?php echo $plugin->getType() ?>">
                          <?php echo zen_image_submit('button_update.gif', IMAGE_UPDATE) ?>
                        <?php } else { ?>
                            <a href="<?php echo 'zmPlugins.php' ?>?edit=<?php echo $plugin->getId() ?>&type=<?php echo $plugin->getType() ?>"><?php echo zen_image_button('button_edit.gif', zm_l10n_get("Edit")) ?></a>
                        <?php } ?>
                    <?php } else { ?>
                        <a href="<?php echo 'zmPlugins.php' ?>?install=<?php echo $plugin->getId() ?>&type=<?php echo $plugin->getType() ?>"><?php echo zen_image_button('button_module_install.gif', zm_l10n_get("Install")) ?></a>
                    <?php } ?>
                  </td>
                </tr>
                <?php if ($isEdit) { ?>
                  <?php foreach ($plugin->getConfigValues() as $value) { ?>
                    <?php if (!$plugin->isTraditional() && !(ZMTools::endsWith($value->getKey(), ZMPlugin::KEY_ENABLED_SUFFIX) || ZMTools::endsWith($value->getKey(), ZMPlugin::KEY_ORDER_SUFFIX))) { continue; } ?>
                    <tr<?php echo ($isEdit ? ' class="edit"' : '') ?>>
                      <td><?php echo $value->getName() ?></td>
                      <td><?php echo $value->getDescription() ?></td>
                      <td>
                        <?php if ($value->hasSetFunction()) { ?>
                          <?php eval('$set = ' . $value->getSetFunction() . "'" . $value->getValue() . "', '" . $value->getKey() . "');"); ?>
                          <?php echo str_replace('<br>', '', $set) ?>
                        <?php } else { ?>
                          <?php echo zen_draw_input_field('configuration[' . $value->getKey() . ']', $value->getValue()); ?>
                        <?php } ?>
                      </td>
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
