<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
require_once('includes/application_top.php');
require_once('../zenmagick/init.php');
require_once('../zenmagick/admin_init.php');

$installer = new ZMInstallationPatcher();

    // install
    if (isset($_POST)) {
        if (array_key_exists('zm_i_admin', $_POST)) {
            $result = $installer->rebuildAdmin(true);
            if (!$result) {
                $zm_messages->add("Could not enable ZenMagick menu");
            }
        }
        if (array_key_exists('zm_i_themes', $_POST)) {
            $result = $installer->patchThemeSupport(true);
            if (!$result) {
                $zm_messages->add("Could not enable ZenMagick theme support");
            }
        }
        if (array_key_exists('zm_i_tdummies', $_POST)) {
            $result = $installer->createZCThemes(true);
            if (!$result) {
                $zm_messages->add("Failed to create dummy files for ZenMagick themes");
            }
        }
        if (array_key_exists('zm_i_sdummies', $_POST)) {
            $result = $installer->createZCSideboxes(true);
            if (!$result) {
                $zm_messages->add("Failed to create dummy files for ZenMagick sideboxes");
            }
        }
    }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
    <title>ZenMagick Installation</title>
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
  <body id="b_cleanup" onload="init()">
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>

    <?php if ($zm_messages->hasMessages()) { ?>
        <ul id="messages">
        <?php foreach ($zm_messages->getMessages() as $message) { ?>
            <li class="<?php echo $message->getType() ?>"><?php echo $message->getText() ?></li>
        <?php } ?>
        </ul>
    <?php } ?>

    <div id="main">
      <div id="content">
        <h2>ZenMagick Installation</h2>

        <form action="<?php echo ZM_ADMINFN_INSTALLATION ?>" method="post" onsubmit="return zm_user_confirm('Install selected items ?');">
          <fieldset>
            <legend>Available Patches</legend>

            <?php if ($installer->isAdminRebuildRequired()) { ?>
              <input type="checkbox" id="zm_i_admin" name="zm_i_admin" value="x">
              <label for="zm_i_admin">Install ZenMagick admin menu</label>
            <?php } ?>

            <?php if ($installer->isPatchThemeSupportRequired()) { ?>
              <input type="checkbox" id="zm_i_themes" name="zm_i_themes" value="x">
              <label for="zm_i_themes">Patch zen-cart to enable ZenMagick request handling (aka ZenMagick themes)</label>
            <?php } ?>

            <?php if ($installer->isCreateZCThemesRequired()) { ?>
              <input type="checkbox" id="zm_i_tdummies" name="zm_i_tdummies" value="x">
              <label for="zm_i_tdummies">Create admin dummy files for all installed ZenMagick themes</label>
            <?php } ?>

            <?php if ($installer->isCreateZCSideboxesRequired()) { ?>
              <input type="checkbox" id="zm_i_sdummies" name="zm_i_sdummies" value="x">
              <label for="zm_i_sdummies">Create dummy files for all (side)boxes of the current ZenMagick theme</label>
            <?php } ?>
          </fieldset>

          <div><input type="submit" value="Install"></div>
        </form>

      </div>
    </div>

  </body>
</html>
