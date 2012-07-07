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


use zenmagick\base\Toolbox;

  // get selections and defaults
  $editor = $request->getParameter('editor');
  if (null != $editor) {
      $toolbox->utils->setCurrentEditor($editor);
  }

  $selectedThemeId = $request->getParameter('themeId', $container->get('themeService')->getActiveThemeId());
  $selectedTheme = $container->get('theme');
  $selectedTheme->setId($selectedThemeId);
  $selectedTheme->setContainer($container);
  if (null === ($file = $request->getParameter('file')) || empty($file)) {
      $selectedFile = $request->getParameter('newfile');
  } else {
      $selectedFile = $file;
  }
  $selectedLanguageId = $currentLanguage->getId();

  $editContents = $request->getParameter('editContents', null, false);
  if (null != $request->getParameter('save') && null != $editContents) {
      // save
      $editContents = stripslashes($editContents);
      if ($selectedTheme->saveStaticPageContent($selectedFile, $editContents, $selectedLanguageId)) {
          $messageService->error(sprintf(_zm('Could not save %s'), $selectedFile));
      }
      $editContents = null;
  } else if (null != $selectedFile) {
      $editContents = null;
      if (null !== $selectedFile) {
          if (Toolbox::isEmpty($selectedFile) && !Toolbox::isEmpty($newFile)) {
              $editContents = '';
              $selectedFile = $newFile;
          } else {
              $editContents = $selectedTheme->staticPageContent($selectedFile, $selectedLanguageId);
              if (null == $editContents) {
                  // file does not exist, so create (new language?)
                  $editContents = '';
              }
          }
      }
  }

?>

<?php echo $this->fetch('messages.php'); ?>
<?php $admin->title(_zm('Edit Define Pages')) ?></h1>
<form action="<?php echo $net->url() ?>" method="GET">
  <input type="hidden" name="rid" value="static_page_editor">
  <h2>ZenMagick Static Page Editor (
          <select id="languageId" name="languageId" onchange="this.form.submit();">
            <?php foreach ($container->get('languageService')->getLanguages() as $lang) { ?>
              <?php $selected = $selectedLanguageId == $lang->getId() ? ' selected="selected"' : ''; ?>
              <option value="<?php echo $lang->getId() ?>"<?php echo $selected ?>><?php echo $lang->getName() ?></option>
            <?php } ?>
          </select>
        )<?php echo (null!==$editContents?': '.$selectedFile:'') ?>
  </h2>
  <?php if (null == $editContents) { ?>
    <fieldset>
      <legend>Edit Static Page</legend>
      <label for="themeId">Theme:</label>
      <select id="themeId" name="themeId" onchange="this.form.submit();">
        <option value="">Select Theme</option>
        <?php foreach ($container->get('themeService')->getAvailableThemes() as $theme) { ?>
          <?php $selected = $selectedThemeId == $theme->getId() ? ' selected="selected"' : ''; ?>
          <option value="<?php echo $theme->getId(); ?>"<?php echo $selected ?>><?php echo $theme->getName(); ?></option>
        <?php } ?>
      </select>

      <label for="file">File:</label>
      <?php $pageList = $selectedTheme->getStaticPageList(false, $selectedLanguageId); ?>
      <select id="file" name="file">
        <option value="">Select File</option>
        <?php foreach ($pageList as $page) { ?>
          <?php $selected = $selectedFile == $page ? ' selected="selected"' : ''; ?>
          <option value="<?php echo $page ?>"<?php echo $selected ?>><?php echo $page ?></option>
        <?php } ?>
      </select>

      <label for="newfile">New File:</label>
      <input type="text" name="newfile" id="newfile">
      <br><br>
      <input class="<?php echo $buttonClasses ?>" type="submit" value="Edit">
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
  <form action="<?php echo $net->url() ?>" method="POST">
    <input type="hidden" name="themeId" value="<?php echo $selectedThemeId ?>">
    <input type="hidden" name="file" value="<?php echo $selectedFile ?>">
    <input type="hidden" name="languageId" value="<?php echo $selectedLanguageId ?>">

    <?php
      $editor = $currentEditor;
      $editor->setId('editContents');
      $editor->setName('editContents');
      $editor->setRows(30);
      $editor->setCols(100);
      $editor->setValue($editContents);
      echo $editor->render($request, $view);
     ?>

    <br><br>
    <input class="<?php echo $buttonClasses ?>" type="submit" name="save" value="Save">
    <a class="<?php echo $buttonClasses ?>" href="<?php echo $net->url() ?>">Cancel</a>
    <a class="<?php echo $buttonClasses ?>" href="#" onclick="preview();return false;">Preview</a>
  </form>
<?php } ?>

<div id="preview" style="display:none;border:1px solid gray;margin:10px;padding:10px;">
  <h2>Preview</h2>
  <div id="previewContents" style="border:2px solid gray;margin:2px;padding:5px;"></div>
</div>
