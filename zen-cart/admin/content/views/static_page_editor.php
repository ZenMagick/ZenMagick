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
 $editContents* WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * $Id: zmStaticPageEditor.php 2647 2009-11-27 00:30:20Z dermanomann $
 */
?>
<?php

  // get selections and defaults
  $editor = $request->getParameter('editor');
  if (null != $editor) {
      $toolbox->utils->setCurrentEditor($editor);
  }

  $selectedThemeId = $request->getParameter('themeId', Runtime::getThemeId());
  $selectedTheme = new ZMTheme($selectedThemeId);
  if (null === ($file = $request->getParameter('file')) || empty($file)) {
      $selectedFile = $request->getParameter('newfile');
  } else {
      $selectedFile = $file;
  }
  $currentLanguage = Runtime::getLanguage();
  $selectedLanguageId = $request->getParameter('languageId', $currentLanguage->getId());

  $editContents = $request->getParameter('editContents', null, false);
  if (null != $request->getParameter('save') && null != $editContents) {
      // save 
      $editContents = stripslashes($editContents);
      $selectedTheme->saveStaticPageContent($selectedFile, $editContents, $selectedLanguageId);
      $editContents = null;
  } else if (null != $selectedFile) {
      $editContents = null;
      if (null !== $selectedFile) {
          if (ZMLangUtils::isEmpty($selectedFile) && !ZMLangUtils::isEmpty($newFile)) {
              $editContents = '';
              $selectedFile = $newFile;
          } else {
              $editContents = $selectedTheme->staticPageContent($selectedFile, $selectedLanguageId, false);
              if (null == $editContents) {
                  // file does not exist, so create (new language?)
                  $editContents = '';
              }
          }
      }
  }

?>

<form action="<?php echo $toolbox->admin->url() ?>" method="get">
  <input type="hidden" name="main_page" value="static_page_editor">
  <h2>ZenMagick Static Page Editor (
          <select id="languageId" name="languageId" onchange="this.form.submit();">
            <?php foreach (ZMLanguages::instance()->getLanguages() as $lang) { ?>
              <?php $selected = $selectedLanguageId == $lang->getId() ? ' selected="selected"' : ''; ?>
              <option value="<?php echo $lang->getId() ?>"<?php echo $selected ?>><?php echo $lang->getName() ?></option>
            <?php } ?>
          </select>
        )<?php echo (null!==$editContents?': '.$selectedFile:'') ?>
  </h2>
  <?php if (null == $editContents) { ?>
    <?php echo zen_hide_session_id() ?>
    <fieldset>
      <legend>Edit Static Page</legend>
      <label for="themeId">Theme:</label>
      <select id="themeId" name="themeId" onchange="this.form.submit();">
        <option value="">Select Theme</option>
        <?php foreach (ZMThemes::instance()->getThemes() as $theme) { ?>
          <?php $selected = $selectedThemeId == $theme->getThemeId() ? ' selected="selected"' : ''; ?>
          <option value="<?php echo $theme->getThemeId(); ?>"<?php echo $selected ?>><?php echo $theme->getName(); ?></option>
        <?php } ?>
      </select>

      <label for="file">File:</label>
      <?php $pageList = $selectedTheme->getStaticPageList(); ?>
      <select id="file" name="file">
        <option value="">Select File</option>
        <?php foreach ($pageList as $page) { ?>
          <?php $selected = $selectedFile == $page ? ' selected="selected"' : ''; ?>
          <option value="<?php echo $page ?>"<?php echo $selected ?>><?php echo $page ?></option>
        <?php } ?>
      </select>

      <label for="newfile">New File:</label>
      <input type="text" name="newfile" id="newfile">

      <label for="editor">Editor:</label>
      <select id="editor" name="editor">
        <?php foreach ($toolbox->utils->getEditorMap() as $key => $name) { ?>
          <?php $selected = $toolbox->utils->getCurrentEditor() instanceof $key ? ' selected="selected"' : ''; ?>
          <option value="<?php echo $key ?>"<?php echo $selected ?>><?php echo $name ?></option>
        <?php } ?>
      </select>
      <br><br>
      <input type="submit" value="Edit">
    </fieldset>
  <?php } ?>
</form>

<script type="text/javascript">
  function preview() {
    var editContents = $('#editContents').attr('value').replace(/&lt;/g,"<").replace(/&gt;/g,">");
    $('#previewContents').html(editContents);
    $('#preview').css('display', 'block');
  }
</script>
<?php if (null !== $editContents) { ?>
  <form action="<?php echo $toolbox->admin->url() ?>" method="POST">
    <?php echo zen_hide_session_id() ?>
    <input type="hidden" name="themeId" value="<?php echo $selectedThemeId ?>">
    <input type="hidden" name="file" value="<?php echo $selectedFile ?>">
    <input type="hidden" name="languageId" value="<?php echo $selectedLanguageId ?>">

    <?php 
      $editor = $toolbox->utils->getCurrentEditor();
      $editor->setId('editContents');
      $editor->setName('editContents');
      $editor->setRows(30);
      $editor->setCols(100);
      $editor->setValue($editContents);
      echo $editor->render($request);
     ?>

    <br><br>
    <input type="submit" name="save" value="Save">
    <a href="<?php echo $toolbox->admin->url() ?>">Cancel</a>
    <a href="#" onclick="preview();return false;">Preview</a>
  </form>
<?php } ?>

<div id="preview" style="display:none;border:1px solid gray;margin:10px;padding:10px;">
  <h2>Preview</h2>
  <div id="previewContents" style="border:2px solid gray;margin:2px;padding:5px;"></div>
</div>
