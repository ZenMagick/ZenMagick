<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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

    require_once('includes/application_top.php');

    if ('POST' == $zm_request->getMethod()) {
        $name = $zm_request->getParameter('name');
        $inherit = $zm_request->getParameter('inherit', false);
        $switchto = $zm_request->getParameter('switchto', false);

        $themeBuilder = new ZMThemeBuilder();
        $themeBuilder->setName($zm_request->getParameter('name'));
        $themeBuilder->setInheritDefaults($zm_request->getParameter('inherit', false));
        $themeBuilder->build();
        foreach ($themeBuilder->getMessages() as $msg) {
            $zm_messages->msg($msg);
        }

        if ($switchto) {
            // create dummy files
            $dummyPatch = new ZMThemeDummyPatch();
            $dummyPatch->patch(true);
            $zm_messages->msg(zm_l10n_get('Created zen-cart template dummy files for "%s".', $name));

            // select new theme
            $themes = new ZMThemes();
            $themes->setZCThemeId($name);
            $zm_messages->msg(zm_l10n_get('New theme "%s" selected as active zen-cart template.', $name));
        }

    }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
    <title>ZenMagick Theme Builder</title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <link rel="stylesheet" type="text/css" href="includes/zenmagick.css">
    <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script type="text/javascript" src="includes/menu.js"></script>
    <script type="text/javascript" src="includes/general.js"></script>
    <script type="text/javascript" src="includes/zenmagick.js"></script>
  </head>
  <body id="b_theme_builder">
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
      <h2><?php zm_l10n("ZenMagick Theme Builder") ?></h2>

        <form action="<?php echo ZM_ADMINFN_THEME_BUILDER ?>" method="post" onsubmit="return zm_user_confirm('Create theme?');">
          <fieldset>
          <legend><?php zm_l10n("Create new ZenMagick Theme") ?></legend>

              <label for="name">Name</label>
              <input type="text" id="name" name="name" value="">
              (This is what the folder will be named. <strong>Names are case sensitive!</strong>)
              <br>

              <input type="checkbox" id="inherit" name="inherit" value="1" checked>
              <label for="inherit">Inherit theme defaults</label>
              (Recommended, unless <strong>all files are copied</strong>)
              <br>

              <input type="checkbox" id="switchto" name="switchto" value="1" checked>
              <label for="switchto">Switch to the new theme when created</label>
              <br>

              <div class="submit"><input type="submit" value="<?php zm_l10n("Create") ?>"></div>
          </fieldset>
        </form>

        <p>Once you have created the new theme, make sure to (re-)generate the required dummy theme files for zen-cart
        using the <a href="<?php echo ZM_ADMINFN_INSTALLATION ?>">installation</a> screen.</p>

        <p>Unused directories can safely be deleted</p>

        <p><strong>It is not recommended to use whitespace in the name. You can always edit the generated files to adjust the description.</strong></p>

      </div>
    </div>

  </body>
</html>
