<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
define('_ZM_ADMIN_PAGE', true);
require_once('includes/application_top.php');

    $zm_plugins = new ZMPlugins();
    $zm_config = new ZMConfig();

    $pluginLoader =& new ZMLoader("pluginLoader");

    $install = $zm_request->getParameter('install');
    $remove = $zm_request->getParameter('remove');
    $edit = $zm_request->getParameter('edit');
    $type = $zm_request->getParameter('type');
    if (null != $install) {
        $plugin = $zm_plugins->getPluginForIdAndType($install, $type);
        if (!$plugin->isInstalled()) {
            if ('ALL' == $plugin->getLoaderSupport()) {
                $pluginLoader->addPath($plugin->getPluginDir());
                foreach ($pluginLoader->getStatic() as $static) {
                    require_once($static);
                }
                // plugins prevail over defaults, but not themes
                $rootLoader =& zm_get_root_loader();
                $rootLoader->setParent($pluginLoader);
            }
            $plugin->install();
        }
        $edit = $install;
        $editPlugin = $plugin;
    } else if (null != $remove) {
        $plugin = $zm_plugins->getPluginForIdAndType($remove, $type);
        if ($plugin->isInstalled()) {
            if ('ALL' == $plugin->getLoaderSupport()) {
                $pluginLoader->addPath($plugin->getPluginDir());
                foreach ($pluginLoader->getStatic() as $static) {
                    require_once($static);
                }
                // plugins prevail over defaults, but not themes
                $rootLoader =& zm_get_root_loader();
                $rootLoader->setParent($pluginLoader);
            }
            $plugin->remove();
        }
    } else if (null != $edit) {
        $editPlugin = $zm_plugins->getPluginForIdAndType($edit, $type);
    }

    // update
    if (isset($_POST) && array_key_exists('pluginId', $_POST)) {
        while (list($key, $value) = each($_POST['configuration'])) {
          $zm_config->updateConfigValue($key, $value);
        }
    }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
    <title><?php zm_l10n("ZenMagick Plugins") ?></title>
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
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>

    <div id="main">
      <div id="content">

        <?php foreach ($zm_plugins->getAllPlugins() as $type => $plugins) { ?>
        <h2><?php echo $type ?> plugins</h2>
        <form action="<?php echo ZM_ADMINFN_PLUGINS ?>" method="post" onsubmit="return zm_user_confirm('Save plugin changes ?');">
          <table cellpadding="5" cellspacing="0"> 
            <thead>
              <tr>
              <th><?php zm_l10n("Name") ?></th>
              <th><?php zm_l10n("Description") ?></th>
              <th><?php zm_l10n("Options") ?></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($plugins as $plugin) { $isEdit = (null != $edit && $plugin->getId() == $editPlugin->getId()); ?>
                <tr<?php echo ($isEdit ? ' class="edit"' : '') ?>>
                  <td><?php echo $plugin->getName() ?></td>
                  <td><?php echo $plugin->getDescription() ?></td>
                  <td>
                    <?php if ($plugin->isInstalled()) { ?>
                        <a href="<?php echo ZM_ADMINFN_PLUGINS ?>?remove=<?php echo $plugin->getId() ?>&type=<?php echo $plugin->getType() ?>" onclick="return zm_user_confirm('This will remove all stored settings.\nContinue?');"><?php echo zen_image_button('button_module_remove.gif', zm_l10n_get("Remove")) ?></a>
                        <?php if ($isEdit) { ?>
                          <input type="hidden" name="pluginId" value="<?php echo $plugin->getId() ?>">
                          <input type="hidden" name="type" value="<?php echo $plugin->getType() ?>">
                          <?php echo zen_image_submit('button_update.gif', IMAGE_UPDATE) ?>
                        <?php } else { ?>
                            <a href="<?php echo ZM_ADMINFN_PLUGINS ?>?edit=<?php echo $plugin->getId() ?>&type=<?php echo $plugin->getType() ?>"><?php echo zen_image_button('button_edit.gif', zm_l10n_get("Edit")) ?></a>
                        <?php } ?>
                    <?php } else { ?>
                        <a href="<?php echo ZM_ADMINFN_PLUGINS ?>?install=<?php echo $plugin->getId() ?>&type=<?php echo $plugin->getType() ?>"><?php echo zen_image_button('button_module_install.gif', zm_l10n_get("Install")) ?></a>
                    <?php } ?>
                  </td>
                </tr>
                <?php if ($isEdit) { ?>
                  <?php foreach ($plugin->getConfigValues() as $value) { ?>
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
