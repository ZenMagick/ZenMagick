<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
<?php $view->extend('AdminBundle::default_layout.html.php'); ?>
<?php
use ZenMagick\Base\Beans;
use ZenMagick\Base\Runtime;

use Symfony\Component\HttpFoundation\ResponseRedirect;

    $installer = new ZenMagick\AdminBundle\Installation\InstallationPatcher();
    $needRefresh = false;

    // install
    if (null != $view['request']->getParameter('update')) {
        $group = $view['request']->getParameter('update');
        foreach ($installer->getPatches($group) as $id => $patch) {
            $formId = 'patch_'.$group.'_'.$patch->getId();
            if ($patch->isOpen() && $patch->getId() == $view['request']->getParameter($formId)) {
                // open and selected
                $needRefresh = true;
                $status = $patch->patch(true);
                $messageService->addAll($patch->getMessages());
                if ($status) {
                    $messageService->success("'".$patch->getLabel()."' installed successfully");
                } else {
                    $messageService->error("Could not install '".$patch->getLabel()."'");
                }
            } elseif (!$patch->isOpen() && null == $view['request']->getParameter($formId)) {
                // installed and not selected
                if ($patch->canUndo()) {
                    $needRefresh = true;
                    $status = $patch->undo();
                    $messageService->addAll($patch->getMessages());
                    if ($status) {
                        $messageService->success("Uninstalled '".$patch->getLabel()."' successfully");
                    } else {
                        $messageService->error("Could not uninstall '".$patch->getLabel()."'");
                    }
                }
            }
        }
    }

    // import static pages
    if (null != $view['request']->getParameter('importSp')) {
        // disable
        $settingsService = Runtime::getSettings();
        $tmp = $view['settings']->get('apps.store.staticContent', false);
        $settingsService->set('apps.store.staticContent', false);

        $ezPageService = $view->container->get('ezPageService');
        $languageService = $view->container->get('languageService');
        foreach ($languageService->getLanguages() as $language) {
            $languageId = $language->getId();
            $themeChain = $view->container->get('themeService')->getThemeChain();
            foreach ($themeChain as $theme) {
                foreach ($languageService->getLanguages() as $subLang) {
                    $subLangId = $subLang->getId();
                    $staticPages = $theme->getStaticPageList(false, $subLangId);
                    foreach ($staticPages as $staticPage) {
//echo $subLangId.'/'.$theme->getName().'/'.$staticPage.'<br>';
                        $contents = $theme->staticPageContent($staticPage, $subLangId);
                        $contents = utf8_encode($contents);
                        // check if already exists
                        if (null != ($ezPage = $ezPageService->getPageForName($staticPage, $subLangId))) {
                            $ezPage->setContent($contents);
                            $ezPage->setStatic(true);
                            $ezPage = $ezPageService->updatePage($ezPage);
                        } else {
                            $ezPage = Beans::getBean('ZenMagick\StoreBundle\Entity\EZPage');
                            $ezPage->setStatic(true);
                            $ezPage->setTitle($staticPage);
                            $ezPage->setContent($contents);
                            $ezPage->setLanguageId($subLangId);
                            $ezPageService->createPage($ezPage);
                        }
                    }
                }
            }
        }

        // cleanup
    $settingsService->set('apps.store.staticContent', $tmp);
    $messageService->success("Import successful!");
    $needRefresh = true;
}

// optimize database tables
if (null != $view['request']->getParameter('optimizeDb')) {
    $database = \ZMRuntime::getDatabase();
    $sm = $database->getSchemaManager();
    foreach ($sm->listTables() as $table) {
        $sql = str_replace('[table]', $table->getName(), "LOCK TABLES [table] READ; CHECK TABLE [table]; UNLOCK TABLES; OPTIMIZE TABLE [table];");
        $database->executeUpdate($sql);
    }
    $messageService->success("All tables optimized");
    $needRefresh = true;
}

/**
 * Show patch group.
 */
function _zm_patch_group($groupId, $buttonClasses, $checkall=true) {
    $installer = new ZenMagick\AdminBundle\Installation\InstallationPatcher();
    foreach ($installer->getPatches($groupId) as $id => $patch) {
        if ('sqlFulltext' == $patch->getId()) {
            continue;
        }

        // check dependencies
        $unfulfilled = array();
        foreach ($patch->dependsOn() as $dId) {
            $dPatch = $installer->getPatchForId($dId);
            if ($dPatch->isOpen()) {
                array_push($unfulfilled, $dPatch->getLabel());
            }
        }
        foreach ($unfulfilled as $dId) {
            ?><p class="error"><?php echo sprintf(_zm("Depends on: '%s'"), $dId) ?></p><?php
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
              <?php echo $patch->getLabel() ?>
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
    return new RedirectResponse($view['router']->generate('installation'));
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

<?php $admin->title() ?>
<div id="b_installation">
<h2><?php _vzm("ZenMagick Installation") ?> <a class="btn" href="<?php echo $view['router']->generate('installation') ?>"><?php _vzm("Refresh Page") ?></a></h2>

<form action="<?php echo $view['router']->generate('installation') ?>" method="POST" onsubmit="return ZenMagick.confirm('Update File Patches?', this);">
    <fieldset class="patches">
      <legend><?php _vzm("ZenMagick File Patches") ?></legend>
      <input type="hidden" name="update" value="file">
      <?php _zm_patch_group('file', $buttonClasses) ?>
    </fieldset>
  </form>

  <form action="<?php echo $view['router']->generate('installation') ?>" method="POST" onsubmit="return ZenMagick.confirm('Update SQL Patches?', this);">
    <fieldset class="patches">
      <legend><?php _vzm("ZenMagick SQL Extensions") ?></legend>
      <input type="hidden" name="update" value="sql">
      <?php _zm_patch_group('sql', $buttonClasses) ?>
      <div class="submit">
        <strong>NOTE:</strong> It is <strong>strongly</strong> recommended to backup your database before appying/reverting SQL patches.
      </div>
    </fieldset>
  </form>

  <form action="<?php echo $view['router']->generate('installation') ?>" method="POST" onsubmit="return ZenMagick.confirm('Load static page content as EZPage?\n(This will override EZPages if an EZPage with a matching title already exists)', this);">
    <fieldset id="static-import">
    <legend><?php _vzm("Import Static Page Contents as EZPages") ?></legend>
        <p>
          <input type="checkbox" id="importSp" name="importSp" value="x">
          <label for="importSp"><?php _vzm("Import static pages"); ?></label>
        </p>

        <div class="submit"><input class="<?php echo $buttonClasses ?>" type="submit" value="<?php _vzm("Import") ?>"></div>
    </fieldset>
  </form>

  <form action="<?php echo $view['router']->generate('installation') ?>" method="POST" onsubmit="return ZenMagick.confirm('Update selected optimisations?\n(This might take a while...)', this);">
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
