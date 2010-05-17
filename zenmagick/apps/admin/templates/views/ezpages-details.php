<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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

  $currentLanguage = ZMLanguages::instance()->getLanguageForId($session->getValue('languages_id'));
  $selectedLanguageId = $request->getParameter('languageId', $currentLanguage->getId());

?>

<form action="<?php echo $admin2->url() ?>" method="POST">
  <input type="hidden" name="main_page" value="ezpages">
  <input type="hidden" name="languageId" value="<?php echo $selectedLanguageId ?>">
  <input type="hidden" name="updateId" value="<?php echo $ezPage->getId() ?>">

  <fieldset>
    <legend>Page</legend>
    <p>
      <label for="title">Title</label>
      <input type="text" id="title" name="title" value="<?php echo $html->encode($ezPage->getTitle()) ?>">
    </p>
  </fieldset>

  <fieldset>
    <legend>Navigation</legend>
    <p>
      <fieldset style="float:left;width:15%;border:1px solid #aaa;padding:4px;margin:0 8px 0 0;">
          <legend>Header</legend>
          <label for="headerSort">Sort</label>
          <input type="text" id="headerSort" name="headerSort" value="<?php echo $ezPage->getHeaderSort() ?>" size="4">
          <?php echo ZMBeanUtils::getBean('BooleanFormWidget#id=header&name=header&title=Header&value='.$ezPage->isHeader())->render($request) ?>
      </fieldset>
      <fieldset style="float:left;width:15%;border:1px solid #aaa;padding:4px;margin:0 8px 0 0;">
          <legend>Sidebox</legend>
          <label for="sideboxSort">Sort</label>
          <input type="text" id="sideboxSort" name="sideboxSort" value="<?php echo $ezPage->getSideboxSort() ?>" size="4">
          <?php echo ZMBeanUtils::getBean('BooleanFormWidget#id=sidebox&name=sidebox&title=Sidebox&value='.$ezPage->isSidebox())->render($request) ?>
      </fieldset>
      <fieldset style="float:left;width:15%;border:1px solid #aaa;padding:4px;margin:0 8px 0 0;">
          <legend>Footer</legend>
          <label for="footerSort">Sort</label>
          <input type="text" id="footerSort" name="footerSort" value="<?php echo $ezPage->getFooterSort() ?>" size="4">
          <?php echo ZMBeanUtils::getBean('BooleanFormWidget#id=footer&name=footer&title=Footer&value='.$ezPage->isFooter())->render($request) ?>
      </fieldset>
      <fieldset style="float:left;min-width:35%;border:1px solid #aaa;padding:4px;margin:0 8px 0 0;">
          <legend>Table Of Contents</legend>
          <label for="tocSort">TOC Sort</label>
          <input type="text" id="tocSort" name="tocSort" value="<?php echo $ezPage->getTocSort() ?>" size="4">
          <label for="tocChapter">Chapter</label>
          <input type="text" id="tocChapter" name="tocChapter" value="<?php echo $ezPage->getTocChapter() ?>" size="4">
          <?php echo ZMBeanUtils::getBean('BooleanFormWidget#id=toc&name=toc&title=TOC&value='.$ezPage->isToc())->render($request) ?>
      </fieldset>
    </p>
  </fieldset>

  <fieldset>
    <legend>Link Options</legend>
    <p>
      <label for="newWin">Link Target</label>
      <?php 
          $select = ZMBeanUtils::getBean('SelectFormWidget#id=newWin&name=newWin&value='.$ezPage->isNewWin()); 
          $select->setOptions(array(false => 'Same Window', true => 'New Window'));
          echo $select->render($request);
      ?>
      <?php echo ZMBeanUtils::getBean('BooleanFormWidget#id=SSL&name=SSL&title=Secure Link&value='.$ezPage->isSSL())->render($request) ?>
    </p>
    <p>
      <label for="altUrl">Internal URL</label>
      <input type="text" id="altUrl" name="altUrl" value="<?php echo $ezPage->getALtUrl() ?>" size="50">
    </p>
    <p>
      <label for="altUrlExternal">External URL</label>
      <input type="text" id="altUrlExternal" name="altUrlExternal" value="<?php echo $ezPage->getALtUrlExternal() ?>" size="50">
    </p>
  </fieldset>

  <fieldset>
    <legend>Contents</legend>
    <?php 
      $editor = $toolbox->utils->getCurrentEditor();
      $editor->setId('htmlText');
      $editor->setName('htmlText');
      $editor->setRows(30);
      $editor->setCols(100);
      $editor->setValue($ezPage->getHtmlText());
      echo $editor->render($request);
     ?>
  </fieldset>
  
  <div>
    <input type="submit" value="Save">
    <a href="<?php echo $admin2->url(null, 'languageId='.$selectedLanguageId) ?>">Cancel</a>
  </div>
</form>
