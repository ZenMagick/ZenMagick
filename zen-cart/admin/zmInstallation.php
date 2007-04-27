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

// dismiss sqlpatch output as we do only want to use the code...
define('GZIP_LEVEL', 0);
ob_start(); require('sqlpatch.php'); ob_end_clean();
require_once('includes/application_top.php');

    // locale
    $patchLabel = array(
        "adminMenu" => "Install ZenMagick admin menu",
        "themeSupport" => "Patch zen-cart to enable ZenMagick request handling (aka ZenMagick themes)",
        "themeDummies" => "Create admin dummy files for all installed ZenMagick themes",
        "sideboxDummies" => "Create dummy files for all (side)boxes of the current ZenMagick theme",
        "i18nSupport" => "Disable zen-cart's <code>zen_date_raw</code> function in favour of a ZenMagick implementation",
        "linkGeneration" => "Disable zen-cart's <code>zen_href_link</code> function in favour of a ZenMagick implementation",
        "email" => "Disable zen-cart's <code>zen_mail</code> function in favour of a ZenMagick implementation",
        "eventProxy" => "Patch zen-cart to activate the ZenMagick event proxy service (required for some emails)",

        "rewriteBase" => "Update RewriteBase value in .htaccess (pretty links, SEO)",

        "ultimateSeoSupport" => "Enable support for Ultimate SEO",

        "sqlFeatures" => "Install Features database tables"
    );

    $coreCompressor = new ZMCoreCompressor();
    $installer = new ZMInstallationPatcher();
    $obsolete = zm_get_obsolete_files();

    // install
    if (isset($_POST)) {
        foreach ($_POST as $name => $value) {
            if (zm_starts_with($name, 'patch_')) {
                $patch = $installer->getPatchForId($value);
                if (null != $patch && $patch->isOpen()) {
                    $status = $patch->patch(true);
                    $zm_messages->addAll($patch->getMessages());
                    if ($status) {
                        $zm_messages->success("'".$patchLabel[$patch->getId()]."' installed successfully");
                    } else {
                        $zm_messages->error("Could not install '".$patchLabel[$patch->getId()]."'");
                    }
                }
            }
        }
    }

    // uninstall
    if (isset($_GET) && array_key_exists('uninstall', $_GET)) {
        $group = $_GET['uninstall'];
        foreach ($installer->getPatches($group) as $id => $patch) {
            if (!$patch->isOpen() && $patch->canUndo()) {
                $status = $patch->undo();
                $zm_messages->addAll($patch->getMessages());
                if ($status) {
                    $zm_messages->success("Uninstalled '".$patchLabel[$patch->getId()]."' successfully");
                } else {
                    $zm_messages->error("Could not uninstall '".$patchLabel[$patch->getId()]."'");
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

    // update
    if (isset($_POST)) {
        $didGenerate = false;
        if (array_key_exists('singleCore', $_POST) && !$coreCompressor->isEnabled()) {
            $coreCompressor->generate();
            $didGenerate = true;
        }
        if (array_key_exists('singleCoreGenerate', $_POST)) {
            $coreCompressor->generate();
            $didGenerate = true;
        }

        if ($coreCompressor->hasErrors()) {
            foreach ($coreCompressor->getErrors() as $msg) {
                $zm_messages->error($msg);
            }
        } else if ($didGenerate) {
            $zm_messages->success("Succsesfully (re-)generated core.php");
        }

        if (array_key_exists('optimize', $_POST) && !array_key_exists('singleCore', $_POST)) {
            $coreCompressor->disable();
            $zm_messages->msg("Disabled usage of core.php");
        }
    }

    /**
     * Show patch group.
     */
    function _zm_patch_group($groupId, $checkall=true) {
    global $installer, $patchLabel;

        $hasChecked = false; 
        foreach ($installer->getPatches($groupId) as $id => $patch) {
            if ($patch->isOpen() || true) {
                // check dependencies
                $unfulfilled = array();
                foreach ($patch->dependsOn() as $dId) {
                    $dPatch = $installer->getPatchForId($dId);
                    if ($dPatch->isOpen()) {
                        array_push($unfulfilled, $dPatch->getId());
                    }
                }
                foreach ($unfulfilled as $dId) {
                    ?><p class="error"><?php zm_l10n("Depends on: '%s'", $patchLabel[$dId]) ?></p><?php
                }
                if (!$patch->isReady()) {
                  ?><p class="error"><?php echo $patch->getPreconditionsMessage() ?></p><?php
                }
                ?><input type="checkbox"
                    id="<?php echo $patch->getId() ?>" name="patch_<?php echo $groupId ?>_<?php echo $patch->getId() ?>"
                    value="<?php echo $patch->getId() ?>"
                    <?php if (!$patch->isOpen()) { $hasChecked = true; ?>disabled="disabled" checked="checked" <?php } ?>>
                  <label for="<?php echo $patch->getId() ?>">
                      <?php echo $patchLabel[$patch->getId()] ?>
                  </label>
                  <br><?php
            }
        }
        if ($installer->isPatchesOpen($groupId)) {
            if ($installer->isPatchesOpen($groupId) && $checkall) { ?>
                <input type="checkbox" class="all" id="<?php echo $groupId ?>_all" name="<?php echo $groupId ?>_all" value="" onclick="sync_all(this, 'patch_<?php echo $groupId ?>_')">
                <label for="<?php echo $groupId ?>_all"><?php zm_l10n("Select/Unselect All") ?></label><br>
            <?php } ?>
            <div class="submit">
                <input type="submit" value="<?php zm_l10n("Install") ?>">
            </div>
        <?php }
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
      <h2><?php zm_l10n("ZenMagick Installation") ?> <a class="btn" href=""><?php zm_l10n("Refresh Page") ?></a></h2>

        <form action="<?php echo ZM_ADMINFN_INSTALLATION ?>" method="post" onsubmit="return zm_user_confirm('Install selected items ?');">
          <fieldset class="patches">
            <legend><?php zm_l10n("ZenMagick File Patches") ?></legend>
            <?php _zm_patch_group('file') ?>
            <div class="submit">
              <a href="<?php echo ZM_ADMINFN_INSTALLATION ?>?uninstall=file" onclick="return zm_user_confirm('Uninstall all patches ?');">Revert all file patches</a> <strong>NOTE:</strong> Additionally created files and <code>.htaccess</code> file changes must be reverted manually.
            </div>
          </fieldset>
        </form>

        <form action="<?php echo ZM_ADMINFN_INSTALLATION ?>" method="post" onsubmit="return zm_user_confirm('Install selected SQL updates?');">
          <fieldset class="patches">
            <legend><?php zm_l10n("ZenMagick SQL Extensions") ?></legend>
            <?php _zm_patch_group('sql') ?>
            <div class="submit">
              <a href="<?php echo ZM_ADMINFN_INSTALLATION ?>?uninstall=sql" onclick="return zm_user_confirm('Uninstall ZenMagick SQL ?');">Revert all SQL patches</a>
              <strong>NOTE:</strong> It is <strong>strongly</strong> recommended to backup your database refore appying/reverting SQL patches.
            </div>
          </fieldset>
        </form>

        <form action="<?php echo ZM_ADMINFN_INSTALLATION ?>" method="post" onsubmit="return zm_user_confirm('Install Ultimate SEO?');">
          <fieldset class="patches">
            <legend><?php zm_l10n("Ultimate SEO Installation Options") ?></legend>
            <?php _zm_patch_group('ultimateSeo', false) ?>
            <div class="submit">
              <a href="<?php echo ZM_ADMINFN_INSTALLATION ?>?uninstall=ultimateSeo" onclick="return zm_user_confirm('Uninstall Ultimate SEO?');"><?php zm_l10n("Uninstall Ultimate SEO") ?></a>
            </div>
          </fieldset>
        </form>

        <form action="<?php echo ZM_ADMINFN_INSTALLATION ?>" method="post" onsubmit="return zm_user_confirm('Update selected optimisations?\n(This might take a while...)');">
          <fieldset id="optimisation">
          <legend><?php zm_l10n("Optimising ZenMagick") ?></legend>
              <input type="hidden" id="optimize" name="optimize" value="x">
              <?php $checked = $coreCompressor->isEnabled() ? ' checked="checked"' : ''; ?>
              <input type="checkbox" id="singleCore" name="singleCore" value="x"<?php echo $checked ?>>
              <label for="singleCore"><?php zm_l10n("Use single core.php file"); ?></label>
              <?php if ($coreCompressor->isEnabled()) { ?>
                  <input type="checkbox" id="singleCoreGenerate" name="singleCoreGenerate" value="x">
                  <label for="singleCoreGenerate"><?php zm_l10n("Regenerate core.php"); ?></label>
              <?php } ?>
              <br>
              <div class="submit"><input type="submit" value="<?php zm_l10n("Update") ?>"></div>
          </fieldset>
        </form>

        <form action="<?php echo ZM_ADMINFN_INSTALLATION ?>" method="post" onsubmit="return zm_user_confirm('Delete selected files?');">
          <fieldset id="obsolete">
          <legend><?php zm_l10n("Remove obsolete ZenMagick files") ?></legend>
            <?php if (0 == count($obsolete)) { ?>
            <h3><?php zm_l10n("Congratulations - Your installation appears to be clean!") ?></h3>
            <?php } else { ?>
              <p>This is a list of file <em>ZenMagick</em> considers to be obsolete. The files are not used by ZenMagick any more,
                and unless you have modified them, or are sure that you need them they can safely be removed.</p>
              <?php $ii = 0; foreach ($obsolete as $file) { $name = zm_mk_relative($file); ?>
                <input type="checkbox" id="obsolete-<?php echo $ii ?>" name="obsolete[]" value="<?php echo $file ?>">
                <label for="obsolete-<?php echo $ii ?>"><?php echo $name ?></label><br>
              <?php ++$ii; } ?>
              <input type="checkbox" class="all" id="oall" name="oall" value="" onclick="sync_all(this, 'obsolete')">
              <label for="fall"><?php zm_l10n("Select/Unselect All") ?></label><br>
              <div class="submit"><input type="submit" value="<?php zm_l10n("Remove") ?>"></div>
            <?php } ?>
          </fieldset>
        </form>

      </div>
    </div>

  </body>
</html>
