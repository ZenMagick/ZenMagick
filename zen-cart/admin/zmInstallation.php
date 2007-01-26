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

// dismiss sqlpatch output as we do only want to use the code...
ob_start(); require('sqlpatch.php'); ob_end_clean();
require_once('includes/application_top.php');
require_once('../zenmagick/init.php');
require_once('../zenmagick/admin_init.php');

    // locale
    $patchLabel = array(
        "adminMenu" => "Install ZenMagick admin menu",
        "themeSupport" => "Patch zen-cart to enable ZenMagick request handling (aka ZenMagick themes)",
        "themeDummies" => "Create admin dummy files for all installed ZenMagick themes",
        "sideboxDummies" => "Create dummy files for all (side)boxes of the current ZenMagick theme",
        "i18nSupport" => "Disable zen-cart's <code>zen_date_raw</code> function if favour of the ZenMagick implementation",

        "rewriteBase" => "Update RewriteBase value in .htaccess (pretty links)",

        "sqlFeatures" => "Install Features database tables"
    );

    $installer = new ZMInstallationPatcher();
    $filePatches = $installer->getPatches('file');
    $sqlPatches = $installer->getPatches('sql');
    $obsolete = zm_get_obsolete_files();

    // install
    if (isset($_POST)) {
        foreach ($_POST as $name => $value) {
            if (zm_starts_with($name, 'patch_')) {
                $patch = $installer->getPatchForId($value);
                if (null != $patch) {
                    $status = $patch->patch(true);
                    $zm_messages->addAll($patch->getMessages());
                    if ($status) {
                        $zm_messages->add($patchLabel[$patch->getId()]." installed successfully", 'msg');
                    } else {
                        $zm_messages->add("Could not ".$patchLabel[$patch->getId()]);
                    }
                }
            }
        }
    }

    // delete
    if (isset($_POST) && array_key_exists('obsolete', $_POST)) {
        foreach ($_POST['obsolete'] as $file) {
            if (is_file($file)) {
                unlink($file);
            } else if (is_dir($file)) {
                rmdir($file);
            }
        }
        // refresh
        $obsolete = zm_get_obsolete_files();
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
      function sync_all(box, name) {
        var boxes = document.getElementsByTagName('input');
        for (var ii=0; ii<boxes.length; ++ii) {
          if (0 == boxes[ii].name.indexOf(name)) {
            boxes[ii].checked = box.checked;
          }
        }
      }
    </script>
  </head>
  <body id="b_installation" onload="init()">
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
          <fieldset class="patches">
            <legend>Available ZenMagick Patches</legend>

            <?php foreach ($filePatches as $id => $patch) { ?>
                <?php if ($patch->isOpen()) { ?>
                  <?php if (!$patch->isReady()) { ?>
                    <p class="error"><?php echo $patch->getPreconditionsMessage() ?></p>
                  <?php } ?>
                  <input type="checkbox" id="<?php echo $patch->getId() ?>" name="patch_file_<?php echo $patch->getId() ?>" value="<?php echo $patch->getId() ?>">
                  <label for="<?php echo $patch->getId() ?>">
                      <?php echo $patchLabel[$patch->getId()] ?>
                  </label>
                  <br>
                <?php } ?>
            <?php } ?>
            <?php if (!$installer->isPatchesOpen('file')) { ?>
              <h3>Congratulations - Your installation seems to be fully patched!</h3>
            <?php } else { ?>
              <input type="checkbox" class="all" id="fall" name="fall" value="" onclick="sync_all(this, 'patch_file_')">
              <label for="fall">Select/Unselect All</label><br>

              <div class="submit">
                <input type="submit" value="Install">
                <a href="">Refresh</a>
              </div>
            <?php } ?>
          </fieldset>
        </form>

        <form action="<?php echo ZM_ADMINFN_INSTALLATION ?>" method="post" onsubmit="return zm_user_confirm('Install selected SQL updates?');">
          <fieldset class="patches">
            <legend>Install ZenMagick SQL Extensions</legend>

            <?php foreach ($sqlPatches as $id => $patch) { ?>
                <?php if ($patch->isOpen()) { ?>
                  <?php if (!$patch->isReady()) { ?>
                    <p class="error"><?php echo $patch->getPreconditionsMessage() ?></p>
                  <?php } ?>
                  <input type="checkbox" id="<?php echo $patch->getId() ?>" name="patch_sql_<?php echo $patch->getId() ?>" value="<?php echo $patch->getId() ?>">
                  <label for="<?php echo $patch->getId() ?>">
                      <?php echo $patchLabel[$patch->getId()] ?>
                  </label>
                  <br>
                <?php } ?>
            <?php } ?>
            <?php if (!$installer->isPatchesOpen('sql')) { ?>
              <h3>Congratulations - Your database seems to be fully patched!</h3>
            <?php } else { ?>
              <input type="checkbox" class="all" id="sall" name="sall" value="" onclick="sync_all(this, 'patch_sql_')">
              <label for="sall">Select/Unselect All</label><br>

              <div class="submit">
                <input type="submit" value="Install">
              </div>
            <?php } ?>
          </fieldset>
        </form>

        <form action="<?php echo ZM_ADMINFN_INSTALLATION ?>" method="post" onsubmit="return zm_user_confirm('Delete selected files?');">
          <fieldset id="obsolete">
            <legend>Remove obsolete ZenMagick files</legend>
            <?php if (0 == count($obsolete)) { ?>
              <h3>Congratulations - Your installation appears to be clean!</h3>
            <?php } else { ?>
              <p>This is a list of file <em>ZenMagick</em> considers to be obsolete. The files are not used by ZenMagick any more,
                and unless you have modified them, or are sure that you need them they can safely be removed.</p>
              <?php $ii = 0; foreach ($obsolete as $file) { $name = zm_mk_relative($file); ?>
                <input type="checkbox" id="obsolete-<?php echo $ii ?>" name="obsolete[]" value="<?php echo $file ?>">
                <label for="obsolete-<?php echo $ii ?>"><?php echo $name ?></label><br>
              <?php ++$ii; } ?>
              <input type="checkbox" class="all" id="oall" name="oall" value="" onclick="sync_all(this, 'obsolete')">
              <label for="oall">Select/Unselect All</label><br>
              <div class="submit"><input type="submit" value="Remove"></div>
            <?php } ?>
          </fieldset>
        </form>

      </div>
    </div>

  </body>
</html>
