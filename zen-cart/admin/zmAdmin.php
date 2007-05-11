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
?><?php require_once('includes/application_top.php');

  $_zm_menu = array();

  function zm_add_menu_item(&$item) {
  global $_zm_menu;

      $_zm_menu[$item->getId()] = $item;
  }

  function zm_build_menu($parent=null) {
  global $_zm_menu;

      $first = true;
      foreach ($_zm_menu as $item) { 
          if (null == $item) {
              continue;
          }
          if ($parent == $item->getParent()) {
              if ($first) {
                  $first = false;
                  echo "<ul>";
              }
              echo "<li>".$item->getTitle();
              zm_build_menu($item->getId());
              echo "</li>";
          }
      }

      if (!$first) {
          echo "</ul>";
      }
  }

  // admin
  zm_add_menu_item(new ZMMenuItem(null, 'admin', zm_l10n_get('Administration')));
  zm_add_menu_item(new ZMMenuItem('admin', 'install', zm_l10n_get('Installation')));
  zm_add_menu_item(new ZMMenuItem('admin', 'plugins', zm_l10n_get('Plugins')));
  zm_add_menu_item(new ZMMenuItem('admin', 'cache', zm_l10n_get('Cache')));

  // catalog
  zm_add_menu_item(new ZMMenuItem(null, 'catalog', zm_l10n_get('Catalog')));
  zm_add_menu_item(new ZMMenuItem('catalog', 'features', zm_l10n_get('Features')));

  // tools
  zm_add_menu_item(new ZMMenuItem(null, 'tools', zm_l10n_get('Tools')));
  zm_add_menu_item(new ZMMenuItem('tools', 'lang', zm_l10n_get('Locale Tool')));
  zm_add_menu_item(new ZMMenuItem('tools', 'console', zm_l10n_get('Console')));

  // help
  zm_add_menu_item(new ZMMenuItem(null, 'help', zm_l10n_get('Help')));
  zm_add_menu_item(new ZMMenuItem('help', 'help', zm_l10n_get('Online Help')));
  zm_add_menu_item(new ZMMenuItem('help', 'about', zm_l10n_get('About')));

  $path = $zm_request->getRequestParameter('path');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
    <title><?php zm_l10n("ZenMagick Admin") ?></title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <link rel="stylesheet" type="text/css" href="includes/zenmagick.css">
    <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script type="text/javascript" src="includes/menu.js"></script>
    <script type="text/javascript" src="includes/general.js"></script>
    <script type="text/javascript" src="includes/zenmagick.js"></script>
    <script type="text/javascript">
      function init() {
        cssjsmenu('navbar');
        if (document.getElementById) {
          var kill = document.getElementById('hoverJS');
          kill.disabled = true;
        }
      }
    </script>
  </head>
  <body id="b_admin" onload="init()">
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>

    <div id="container" style="border:1px solid gray;">

      <?php zm_build_menu(); ?>

    </div>

  </body>
</html>
