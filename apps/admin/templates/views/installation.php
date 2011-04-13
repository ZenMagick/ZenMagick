<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
        "i18nSupport" => "Disable zen-cart's <code>zen_date_raw</code> function in favour of a ZenMagick implementation",
        "linkGeneration" => "Disable zen-cart's <code>zen_href_link</code> function in favour of a ZenMagick implementation",
        "email" => "Disable zen-cart's <code>zen_mail</code> function in favour of a ZenMagick implementation",
        "eventProxy" => "Patch zen-cart to activate the ZenMagick event proxy service (required for some emails and guest checkout!)",
        "customerEdit" => "Patch zen-cart to allow editing customers where email also exists as guest account",
        "couponAdminMail" => "Patch zen-cart to allow use of ZenMagick email templates for coupon admin mail",

        "rewriteBase" => "Update RewriteBase value in .htaccess (pretty links, SEO)",

        "adminFolderName" => "Adjust admin folder name for ZenMagick admin",

        "sqlConfig" => "Setup ZenMagick config groups and initial values",
        "sqlToken" => "Create the database table used by the token service",
        "sqlFulltext" => "Create indices for fulltext product search",
        "sqlAdminRoles" => "Create tables for new role based admin access control",
        "sqlAdminPrefs" => "Create new admin preferences table",
        "sqlThemeVariation" => "Create additional column for theme variation selection",
        "sacsPermissions" => "Create new table to store custom admin access rules",
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
                ZMMessages::instance()->addAll($patch->getMessages());
                if ($status) {
                    ZMMessages::instance()->success("'".$patchLabel[$patch->getId()]."' installed successfully");
                } else {
                    ZMMessages::instance()->error("Could not install '".$patchLabel[$patch->getId()]."'");
                }
            } else if (!$patch->isOpen() && null == $request->getParameter($formId)) {
                // installed and not selected
                if ($patch->canUndo()) {
                    $needRefresh = true;
                    $status = $patch->undo();
                    ZMMessages::instance()->addAll($patch->getMessages());
                    if ($status) {
                        ZMMessages::instance()->success("Uninstalled '".$patchLabel[$patch->getId()]."' successfully");
                    } else {
                        ZMMessages::instance()->error("Could not uninstall '".$patchLabel[$patch->getId()]."'");
                    }
                }
            }
        }
    }

    // optimize database tables
    if (null != $request->getParameter('optimizeDb')) {
        $database = ZMRuntime::getDatabase();
        $tableMeta = $database->getMetaData();
        foreach ($tableMeta['tables'] as $table) {
            $sql = str_replace('[table]', $table, "LOCK TABLES [table] READ; CHECK TABLE [table]; UNLOCK TABLES; OPTIMIZE TABLE [table];");
            $database->update($sql);
        }
        ZMMessages::instance()->success("All tables optimized");
        $needRefresh = true;
    }

    /**
     * Show patch group.
     */
    function _zm_patch_group($groupId, $patchLabel, $buttonClasses, $checkall=true) {
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
                ?><p class="error"><?php echo sprintf(_zm("Depends on: '%s'"), $patchLabel[$dId]) ?></p><?php
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
            <input class="<?php echo $buttonClasses ?>" type="submit" value="<?php _vzm("Update") ?>">
        </div>
    <?php }

    if ($needRefresh) {
        $request->redirect($admin2->url(null, '', true));
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

<?php $admin2->title() ?>
<div id="b_installation">
  <h2><?php _vzm("ZenMagick Installation") ?> <a class="btn" href="<?php echo $admin2->url() ?>"><?php _vzm("Refresh Page") ?></a></h2>

  <h2 style="color:red"><?php _vzm('The patch "<em>Disable zen-cart\'s <code>zen_date_raw</code> function in favour of a ZenMagick implementation</em>" must be disabled as it is deprecated and will be removed in the next version.') ?></h2>

  <form action="<?php echo $admin2->url() ?>" method="POST" onsubmit="return ZenMagick.confirm('Update File Patches?', this);">
    <fieldset class="patches">
      <legend><?php _vzm("ZenMagick File Patches") ?></legend>
      <input type="hidden" name="update" value="file">
      <?php _zm_patch_group('file', $patchLabel, $buttonClasses) ?>
    </fieldset>
  </form>

  <form action="<?php echo $admin2->url() ?>" method="POST" onsubmit="return ZenMagick.confirm('Update SQL Patches?', this);">
    <fieldset class="patches">
      <legend><?php _vzm("ZenMagick SQL Extensions") ?></legend>
      <input type="hidden" name="update" value="sql">
      <?php _zm_patch_group('sql', $patchLabel, $buttonClasses) ?>
      <div class="submit">
        <strong>NOTE:</strong> It is <strong>strongly</strong> recommended to backup your database before appying/reverting SQL patches.
      </div>
    </fieldset>
  </form>

  <form action="<?php echo $admin2->url() ?>" method="POST" onsubmit="return ZenMagick.confirm('Update selected optimisations?\n(This might take a while...)', this);">
    <fieldset id="optimisation">
    <legend><?php _vzm("Optimising ZenMagick") ?></legend>
        <p>
          <input type="checkbox" id="optimizeDb" name="optimizeDb" value="x">
          <label for="optimizeDb"><?php _vzm("Optimize database tables"); ?></label>
        </p>

        <div class="submit"><input class="<?php echo $buttonClasses ?>" type="submit" value="<?php _vzm("Update") ?>"></div>
    </fieldset>
  </form>
</div>
