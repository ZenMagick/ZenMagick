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

use ZenMagick\Base\Beans;
?>
<?php $selectedLanguageId = $currentLanguage->getId(); ?>

<?php $admin->title(_zm('Edit Content')) ?>
<form action="<?php echo $net->url() ?>" method="POST">
  <input type="hidden" name="languageId" value="<?php echo $selectedLanguageId ?>">
  <input type="hidden" name="updateId" value="<?php echo $ezPage->getId() ?>">

  <fieldset>
  <legend><?php _vzm('Page') ?></legend>
    <p>
    <label for="title"><?php _vzm('Title') ?></label>
      <input type="text" id="title" name="title" value="<?php echo $html->encode($ezPage->getTitle()) ?>">
    </p>
  </fieldset>

<?php if (!$ezPage->isStatic()) { ?>
  <fieldset>
  <legend><?php _vzm('Navigation') ?></legend>
    <p>
      <fieldset style="float:left;width:15%;border:1px solid #aaa;padding:4px;margin:0 8px 0 0;">
      <legend><?php _vzm('Header') ?></legend>
      <label for="headerSort"><?php _vzm('Sort') ?></label>
          <input type="text" id="headerSort" name="headerSort" value="<?php echo $ezPage->getHeaderSort() ?>" size="4">
          <?php echo Beans::getBean('booleanFormWidget#id=header&name=header&title=Header&value='.$ezPage->isHeader())->render($request, $view) ?>
      </fieldset>
      <fieldset style="float:left;width:15%;border:1px solid #aaa;padding:4px;margin:0 8px 0 0;">
      <legend><?php _vzm('Sidebox') ?></legend>
      <label for="sideboxSort"><?php _vzm('Sort') ?></label>
          <input type="text" id="sideboxSort" name="sideboxSort" value="<?php echo $ezPage->getSideboxSort() ?>" size="4">
          <?php echo Beans::getBean('booleanFormWidget#id=sidebox&name=sidebox&title=Sidebox&value='.$ezPage->isSidebox())->render($request, $view) ?>
      </fieldset>
      <fieldset style="float:left;width:15%;border:1px solid #aaa;padding:4px;margin:0 8px 0 0;">
      <legend><?php _vzm('Footer') ?></legend>
      <label for="footerSort"><?php _vzm('Sort') ?></label>
          <input type="text" id="footerSort" name="footerSort" value="<?php echo $ezPage->getFooterSort() ?>" size="4">
          <?php echo Beans::getBean('booleanFormWidget#id=footer&name=footer&title=Footer&value='.$ezPage->isFooter())->render($request, $view) ?>
      </fieldset>
      <fieldset style="float:left;min-width:35%;border:1px solid #aaa;padding:4px;margin:0 8px 0 0;">
      <legend><?php _vzm('Table Of Contents') ?></legend>
      <label for="tocSort"><?php _vzm('TOC Sort') ?></label>
          <input type="text" id="tocSort" name="tocSort" value="<?php echo $ezPage->getTocSort() ?>" size="4">
          <label for="tocChapter"><?php _vzm('Chapter') ?></label>
          <input type="text" id="tocChapter" name="tocChapter" value="<?php echo $ezPage->getTocChapter() ?>" size="4">
          <?php echo Beans::getBean('booleanFormWidget#id=toc&name=toc&title=TOC&value='.$ezPage->isToc())->render($request, $view) ?>
      </fieldset>
    </p>
  </fieldset>

  <fieldset>
  <legend><?php _vzm('Link Options') ?></legend>
    <p>
    <label for="newWin"><?php _vzm('Link Target') ?></label>
      <?php
          $select = Beans::getBean('selectFormWidget#id=newWin&name=newWin&value='.$ezPage->isNewWin());
          $select->setOptions(array(false => _zm('Same Window'), true => _zm('New Window')));
          echo $select->render($request, $view);
      ?>
      <?php echo Beans::getBean('booleanFormWidget#id=SSL&name=SSL&title='._zm('Secure Link').'&value='.$ezPage->isSsl())->render($request, $view) ?>
    </p>
    <p>
      <label for="altUrl"><?php _vzm('Internal URL') ?></label>
      <input type="text" id="altUrl" name="altUrl" value="<?php echo $ezPage->getALtUrl() ?>" size="50">
    </p>
    <p>
      <label for="altUrlExternal"><?php _vzm('External URL') ?></label>
      <input type="text" id="altUrlExternal" name="altUrlExternal" value="<?php echo $ezPage->getALtUrlExternal() ?>" size="50">
    </p>
  </fieldset>
<?php } ?>
  <fieldset>
    <legend><?php _vzm('Contents') ?></legend>
    <?php
      $editor = $currentEditor;
      $editor->setId('htmlText');
      $editor->setName('htmlText');
      $editor->setRows(30);
      $editor->setCols(100);
      $editor->setValue($ezPage->getHtmlText(false));
      echo $editor->render($request, $view);
     ?>
  </fieldset>

  <div>
    <input class="<?php echo $buttonClasses ?>" type="submit" value="Save">
    <a class="<?php echo $buttonClasses ?>" href="<?php echo $net->url('ezpages', 'languageId='.$selectedLanguageId) ?>"><?php _vzm('Cancel') ?></a>
  </div>
</form>
