<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * $Id$
 */
?>
<?php
require_once 'includes/application_top.php';

  // get selections and defaults
  $selectedThemeId = ZMRequest::getParameter('themeId', ZM_DEFAULT_THEME);
  $selectedTheme = new ZMTheme($selectedThemeId);
  if (null === ($file = ZMRequest::getParameter('file')) || empty($file)) {
      $selectedFile = ZMRequest::getParameter('newfile');
  } else {
      $selectedFile = $file;
  }
  $currentLanguage = ZMRuntime::getLanguage();
  $selectedLanguageId = ZMRequest::getParameter('languageId', $currentLanguage->getId());

  $editContents = ZMRequest::getParameter('editContents', null, false);
  if (null != ZMRequest::getParameter('save') && null != $editContents) {
      // save 
      $editContents = stripslashes($editContents);
      $selectedTheme->saveStaticPageContent($selectedFile, $editContents, $selectedLanguageId);
      $editContents = null;
  } else if (null != $selectedFile) {
      $editContents = null;
      if (null !== $selectedFile) {
          if (ZMTools::isEmpty($selectedFile) && !ZMTools::isEmpty($newFile)) {
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
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
    <title>Static Page Editor :: ZenMagick</title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <link rel="stylesheet" type="text/css" href="includes/zenmagick.css">
    <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script type="text/javascript" src="includes/menu.js"></script>
    <script type="text/javascript" src="includes/general.js"></script>
    <script type="text/javascript" src="includes/jquery/jquery-1.3.2.min.js"></script>
    <script type="text/javascript">
      function init() {
        cssjsmenu('navbar');
        if (document.getElementById) {
          var kill = document.getElementById('hoverJS');
          kill.disabled = true;
        }
        if (typeof _editor_url == "string") HTMLArea.replaceAll();
      }
      function preview() {
        var editContents = $('#editContents').attr('value').replace(/&lt;/g,"<").replace(/&gt;/g,">");
        $('#previewContents').html(editContents);
        $('#preview').css('display', 'block');
      }
    </script>
    <?php if ($editor_handler != '') include ($editor_handler); ?>
  </head>
  <body id="b_sp_editor" onload="init()">
    <?php require DIR_WS_INCLUDES . 'header.php'; ?>

    <div id="main">
      <div id="content">
        <form action="<?php echo 'zmStaticPageEditor.php' ?>" method="get">
          <h2>ZenMagick Static Page Editor (
                  <select id="languageId" name="languageId" onChange="this.form.submit();">
                    <?php foreach (ZMLanguages::instance()->getLanguages() as $language) { ?>
                      <?php $selected = $selectedLanguageId == $language->getId() ? ' selected="selected"' : ''; ?>
                      <option value="<?php echo $language->getId() ?>"<?php echo $selected ?>><?php echo $language->getName() ?></option>
                    <?php } ?>
                  </select>
                )<?php echo (null!==$editContents?': '.$selectedFile:'') ?></h2>
          <?php if (null == $editContents) { ?>
            <?php echo zen_hide_session_id() ?>
            <fieldset>
              <legend>Edit Static Page</legend>
              <label for="themeId">Theme:</label>
              <?php $themeInfoList = ZMThemes::instance()->getThemeInfoList(); ?>
              <select id="themeId" name="themeId" onChange="this.form.submit();">
                <option value="">Select Theme</option>
                <?php foreach ($themeInfoList as $themeInfo) { ?>
                  <?php $selected = $selectedThemeId == $themeInfo->getThemeId() ? ' selected="selected"' : ''; ?>
                  <option value="<?php echo $themeInfo->getThemeId(); ?>"<?php echo $selected ?>><?php echo $themeInfo->getName(); ?></option>
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

              <label for="reset_editor">Editor:</label>
              <?php echo zen_draw_pull_down_menu('reset_editor', $editors_pulldown, $current_editor_key, ' id="reset_editor"'). zen_draw_hidden_field('action', 'set_editor') ?>

              <br><br>
              <input type="submit" value="Edit">
            </fieldset>
          <?php } ?>
        </form>

        <?php if (null !== $editContents) { ?>
          <form action="<?php echo 'zmStaticPageEditor.php' ?>" method="post">
            <?php echo zen_hide_session_id() ?>
            <input type="hidden" name="themeId" value="<?php echo $selectedThemeId ?>">
            <input type="hidden" name="file" value="<?php echo $selectedFile ?>">
            <input type="hidden" name="languageId" value="<?php echo $selectedLanguageId ?>">

            <?php 
              if ($_SESSION['html_editor_preference_status']=="FCKEDITOR") {
                $oFCKeditor = new FCKeditor('editContents') ;
                $oFCKeditor->Value = $editContents ;
                $oFCKeditor->Width  = '700' ;
                $oFCKeditor->Height = '450' ;
                $output = $oFCKeditor->CreateHtml() ; echo $output;
              } else { // using HTMLAREA or just raw "source" ?>
                <textarea name="editContents" cols="100" rows="30"  id="editContents"><?php echo htmlentities($editContents) ?></textarea>
              <?php } ?>

            <br><br>
            <input type="submit" name="save" value="Save">
            <a href="<?php echo zen_href_link('zmStaticPageEditor.php', "themeId=".$selectedThemeId."&amp;languageId=".$selectedLanguageId) ?>">Cancel</a>
            <a href="#" onclick="preview();return false;">Preview</a>
          </form>
        <?php } ?>
      </div>

      <div id="preview" style="display:none;border:1px solid gray;margin:10px;padding:10px;">
        <h2>Preview</h2>
        <div id="previewContents" style="border:2px solid gray;margin:2px;padding:5px;"></div>
      </div>
    </div>

  </body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>
