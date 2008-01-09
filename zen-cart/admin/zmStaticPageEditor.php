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
require_once('includes/application_top.php');

  // get selections and defaults
  $selectedThemeId = $zm_request->getParameter('themeId', 'default');
  $selectedTheme = new ZMTheme($selectedThemeId);
  $newFile = $zm_request->getParameter('newfile');
  $selectedFile = $zm_request->getParameter('file', $newFile);
  $selectedLanguage = $zm_runtime->getLanguage();
  $selectedLanguageDirecory = $zm_request->getParameter('languageDirectory', $selectedLanguage->getDirectory());

  $editContents = $zm_request->getParameter('editContents', null, false);
  if (null != $zm_request->getParameter('save') && null != $editContents) {
      // save 
      $editContents = stripslashes($editContents);
      $selectedTheme->saveStaticPageContent($selectedFile, $editContents, $selectedLanguageDirectory);
      $editContents = null;
  } else {
      $editContents = null;
      if (null !== $selectedFile) {
          if (zm_is_empty($selectedFile) && !zm_is_empty($newFile)) {
              $editContents = '';
              $selectedFile = $newFile;
          } else {
              $editContents = $selectedTheme->staticPageContent($selectedFile, $selectedLanguageDirecory, false);
          }
      }
  }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
    <title>ZenMagick Static Page Editor</title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <link rel="stylesheet" type="text/css" href="includes/zenmagick.css">
    <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script type="text/javascript" src="includes/menu.js"></script>
    <script type="text/javascript" src="includes/general.js"></script>
    <script type="text/javascript">
      function init() {
        cssjsmenu('navbar');
        if (document.getElementById) {
          var kill = document.getElementById('hoverJS');
          kill.disabled = true;
        }
        if (typeof _editor_url == "string") HTMLArea.replaceAll();
      }
    </script>
    <?php if ($editor_handler != '') include ($editor_handler); ?>
  </head>
  <body id="b_sp_editor" onload="init()">
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>

    <div id="main">
      <div id="content">
      <h2>ZenMagick Static Page Editor (
              <select id="languageDirectory" name="languageDirectory" onChange="this.form.submit();">
                <?php foreach ($zm_languages->getLanguages() as $language) { ?>
                  <?php $selected = $selectedLanguageDirecory == $language->getDirectory() ? ' selected="selected"' : ''; ?>
                  <option value="<?php echo $language->getDirectory() ?>"<?php echo $selected ?>><?php echo $language->getName() ?></option>
                <?php } ?>
              </select>
            )<?php echo (null!==$editContents?': '.$selectedFile:'') ?></h2>
        <?php if (null == $editContents) { ?>
          <form action="<?php echo ZM_ADMINFN_SP_EDITOR ?>" method="get">
            <?php echo zen_hide_session_id() ?>
            <fieldset>
              <legend>Edit Static Page</legend>
              <label for="themeId">Theme:</label>
              <?php $themes = $zm_runtime->getThemes(); $themeInfoList = $themes->getThemeInfoList(); ?>
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
              <?php echo zen_draw_pull_down_menu('reset_editor', $editors_pulldown, $current_editor_key). zen_draw_hidden_field('action', 'set_editor') ?>

              <br><br>
              <input type="submit" value="Edit">
            </fieldset>
          </form>
        <?php } ?>

        <?php if (null !== $editContents) { ?>
          <form action="<?php echo ZM_ADMINFN_SP_EDITOR ?>" method="post">
            <?php echo zen_hide_session_id() ?>
            <input type="hidden" name="themeId" value="<?php echo $selectedThemeId ?>">
            <input type="hidden" name="file" value="<?php echo $selectedFile ?>">
            <input type="hidden" name="languageDirectory" value="<?php echo $selectedLanguageDirecory ?>">

            <?php 
              if ($_SESSION['html_editor_preference_status']=="FCKEDITOR") {
                $oFCKeditor = new FCKeditor('editContents') ;
                $oFCKeditor->Value = $editContents ;
                $oFCKeditor->Width  = '700' ;
                $oFCKeditor->Height = '450' ;
                $output = $oFCKeditor->CreateHtml() ; echo $output;
              } else { // using HTMLAREA or just raw "source"
                echo zen_draw_textarea_field('editContents', 'soft', '100', '30', $editContents, ' id="editContents"');
              } ?>

            <br><br>
            <input type="submit" name="save" value="Save">
            <a href="<?php echo zen_href_link(ZM_ADMINFN_SP_EDITOR, "themeId=".$selectedThemeId."&languageDirectory=".$selectedLanguageDirecory) ?>">Cancel</a>
          </form>
        <?php } ?>
      </div>
    </div>

  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
