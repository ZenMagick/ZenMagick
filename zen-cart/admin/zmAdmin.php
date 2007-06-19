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
?><?php 
define('_ZM_ADMIN_PAGE', true);
require_once('includes/application_top.php');



/*

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
*/

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
    <style type="text/css">
      body {behavior:url(includes/csshover.htc);}
      a {text-decoration:none;}
      a:link {color:#080;}
      a:visited {color:#790;}
      a:active {color:red;}
      a:hover {text-decoration:underline;}

      #secnav ul {list-style:none;padding:0;margin:0;}
      #secnav a {font-weight:bold;color:green;text-decoration: none;}
      #secnav li li a {display:block;font-weight:normal;color:#060;padding:0.2em 4px;}
      #secnav li li a:hover {text-decoration:underline;}
      #secnav li {float:left;position:relative;x-width:10em;padding:4px 5px;text-align:left;cursor:default;background-color:#f7f7f7;border:solid gray;border-width:0 1px 1px;}
      #secnav li li {width:12em;padding:1px 5px;}
      #secnav li ul {display:none;position:absolute;top:100%;left:0;font-weight:normal;border:solid 1px #7d6340;margin-top:3px;}
      #secnav li>ul {top:auto;left:auto;}
      #secnav li li {display:block;float:none;xbackground-color:transparent;border:none;}
      #secnav li:hover ul, #secnav li.over ul {display:block;z-index:1;}
    </style>
    <script type="text/javascript"><!--//--><![CDATA[//><!--
    startList = function() {
      if (document.all && document.getElementById) {
        navRoot = document.getElementById("secnav");
        for (ii=0; ii<navRoot.childNodes.length; ++ii) {
          node = navRoot.childNodes[ii];
          if (node.nodeName=="LI") {
            node.onmouseover=function() {
              this.className+=" over";
            }
            node.onmouseout=function() {
              this.className=this.className.replace(" over", "");
            }
          }
        }
      }
    }
    window.onload=startList;
    //--><!]]></script>
  </head>
  <body id="b_admin">

    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>

    <div id="container" style="border:1px solid gray;">
    </div>

<div style="clear:both;">
<?php

    $controller = $zm_loader->create("WikiController");
      $view = $controller->process();
if ($view->isViewFunction()) { $view->callView(); } else { include($view->getViewFilename()); }

?>
</div>

  </body>
</html>
