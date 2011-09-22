<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
<?php

    // locale
    $patchLabel = array(
        "adminMenu" => "Install ZenMagick admin menu",
        "themeSupport" => "Patch zen-cart to enable ZenMagick request handling (aka ZenMagick themes)",
        "noThemeSupport" => "Patch zen-cart to use ZenMagick <strong>without</strong> ZenMagick themes",
        "themeDummies" => "Create admin dummy files for all installed ZenMagick themes",
        "sideboxDummies" => "Create dummy files for all (side)boxes of <strong>all</strong> ZenMagick themes and <strong>installed</strong> plugins",
        "linkGeneration" => "Disable zen-cart's <code>zen_href_link</code> function in favour of a ZenMagick implementation",
        "email" => "Disable zen-cart's <code>zen_mail</code> function in favour of a ZenMagick implementation",
        "eventProxy" => "Patch zen-cart to activate the ZenMagick event proxy service (required for some emails and guest checkout!)",
        "customerEdit" => "Patch zen-cart to allow editing customers where email also exists as guest account",
        "couponAdminMail" => "Patch zen-cart to allow use of ZenMagick email templates for coupon admin mail",
        "reviewTitle" => "Add column to store review title",

        "rewriteBase" => "Update RewriteBase value in .htaccess (pretty links, SEO)",

        "adminFolderName" => "Adjust admin folder name for ZenMagick admin",
        "redirectPatch" => "Patch zen-cart's <code>zen_redirect</code> function to allow to veto redirects",

        "sqlConfig" => "Setup ZenMagick config groups and initial values",
        "sqlToken" => "Create the database table used by the token service",
        "sqlFulltext" => "Create indices for fulltext product search",
        "sqlAdminRoles" => "Create tables for new role based admin access control",
        "sqlAdminPrefs" => "Create new admin preferences table",
        "sqlThemeVariation" => "Create additional column for theme variation selection",
        "sacsPermissions" => "Create new table to store custom admin access rules",
        "blockAdmin" => "Create new tables for block admin",
    );

    $installer = new ZMInstallationPatcher();
    $needRefresh = false;

    // install
    if (null != $request->getParameter('update')) {
        $group = $request->getParameter('update');
        foreach ($installer->getPatches($group) as $id => $patch) {
            $formId = 'patch_'.$group.'_'.$patch->getId();
            if ($patch->isOpen() && $patch->getId() == $request->getParameter($formId)) {
                // open and selected
                $needRefresh = true;
                $status = $patch->patch(true);
                $container->get('messageService')->addAll($patch->getMessages());
                if ($status) {
                    $container->get('messageService')->success("'".$patchLabel[$patch->getId()]."' installed successfully");
                } else {
                    $container->get('messageService')->error("Could not install '".$patchLabel[$patch->getId()]."'");
                }
            } else if (!$patch->isOpen() && null == $request->getParameter($formId)) {
                // installed and not selected
                if ($patch->canUndo()) {
                    $needRefresh = true;
                    $status = $patch->undo();
                    $container->get('messageService')->addAll($patch->getMessages());
                    if ($status) {
                        $container->get('messageService')->success("Uninstalled '".$patchLabel[$patch->getId()]."' successfully");
                    } else {
                        $container->get('messageService')->error("Could not uninstall '".$patchLabel[$patch->getId()]."'");
                    }
                }
            }
        }
    }

    /**
     * Show patch group.
     */
    function _zm_patch_group($groupId, $patchLabel, $checkall=true) {
        $installer = new ZMInstallationPatcher();
        foreach ($installer->getPatches($groupId) as $id => $patch) {
            if ('sqlFulltext' == $patch->getId()) {
                continue;
            }
            // check dependencies
            $unfulfilled = array();
            foreach ($patch->dependsOn() as $dId) {
                $dPatch = $installer->getPatchForId($dId);
                if ($dPatch->isOpen()) {
                    array_push($unfulfilled, $dPatch->getId());
                }
            }
            foreach ($unfulfilled as $dId) {
                ?><p class="error"><?php _vzm("Depends on: '%s'", $patchLabel[$dId]) ?></p><?php
            }
            if (!$patch->isReady() && $patch->isOpen()) {
              ?><p class="error"><?php echo $patch->getPreconditionsMessage() ?></p><?php
            }
            ?><input type="checkbox"
                id="<?php echo $patch->getId() ?>" name="patch_<?php echo $groupId ?>_<?php echo $patch->getId() ?>"
                value="<?php echo $patch->getId() ?>"
                <?php if (!$patch->isOpen()) { ?>checked="checked" <?php } ?>
                <?php if (!$patch->canUndo() && !$patch->isOpen()) { ?>disabled="disabled" <?php } ?>>
              <label for="<?php echo $patch->getId() ?>">
                  <?php echo $patchLabel[$patch->getId()] ?>
              </label>
              <br><?php
        } ?>
        <input type="checkbox" class="all" id="<?php echo $groupId ?>_all" name="<?php echo $groupId ?>_all" value="" onclick="sync_all(this, 'patch_<?php echo $groupId ?>_')">
        <label for="<?php echo $groupId ?>_all"><?php _vzm("Select/Unselect All") ?></label><br>
        <div class="submit">
            <input type="submit" value="<?php _vzm("Update") ?>">
        </div>
    <?php }

    if ($needRefresh) {
        $request->redirect('zmIndex.php?main_page=installation');
    }

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
    </script>

<div id="b_installation">
  <h2><?php _vzm("ZenMagick Installation") ?> <a class="btn" href=""><?php _vzm("Refresh Page") ?></a></h2>

  <form action="zmIndex.php?main_page=installation" method="POST" onsubmit="return zm_user_confirm('Update File Patches?');">
    <fieldset class="patches">
      <legend><?php _vzm("ZenMagick File Patches") ?></legend>
      <input type="hidden" name="update" value="file">
      <?php _zm_patch_group('file', $patchLabel) ?>
    </fieldset>
  </form>

  <form action="zmIndex.php?main_page=installation" method="POST" onsubmit="return zm_user_confirm('Update SQL Patches?');">
    <fieldset class="patches">
      <legend><?php _vzm("ZenMagick SQL Extensions") ?></legend>
      <input type="hidden" name="update" value="sql">
      <?php _zm_patch_group('sql', $patchLabel) ?>
      <div class="submit">
        <strong>NOTE:</strong> It is <strong>strongly</strong> recommended to backup your database before appying/reverting SQL patches.
      </div>
    </fieldset>
  </form>
</div>
