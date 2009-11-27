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
require 'includes/application_top.php';

  // peel fkt parameter from url string
  function get_fkt($url) {
      $urlToken = parse_url($url); 
      parse_str(str_replace('&amp;', '&', $urlToken['query']), $query); 
      return $query['fkt'];
  }

  $toolbox = $request->getToolbox();

  // active fkt
  $selectedFkt = $request->getParameter('fkt', '');
  $zm_nav_params .= '&fkt='.$selectedFkt;

  $title = null;
  if (0 < $request->getCategoryId()) {
      $category = ZMCategories::instance()->getCategoryForId($request->getCategoryId());
      $title = $category->getName();
  }
  if (0 < $request->getProductId()) {
      $product = ZMProducts::instance()->getProductForId($request->getProductId());
      $title = $product->getName();
      $zm_nav_params .= '&productId='.$request->getProductId();
  } 

  // common nav params
  $zm_nav_params = '';
  if (null != $product) {
      $zm_nav_params .= '&productId='.$product->getId();
  }
  if (null != $category) {
      $zm_nav_params .= '&cPath='.$request->getCategoryPath();
  }

  // capture output as plugins may redirect...
  ob_start();

  // collect all info we need
  $tabInfo = array();
  foreach (ZMAdminMenu::getItemsForParentId(ZMAdminMenu::MENU_CATALOG_MANAGER_TAB) as $item) {
      $fkt = get_fkt($item->getURL());
      $page = $toolbox->admin->getPluginPageForFkt($request, $fkt);
      $tabInfo[] = array('item' => $item, 'page' => $page, 'fkt' => $fkt);
  }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
    <title><?php zm_l10n("Catalog Manager :: ZenMagick %s", (null != $title ? ':: '.$title : '')) ?></title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <link rel="stylesheet" type="text/css" href="includes/zenmagick.css">
    <link rel="stylesheet" type="text/css" href="includes/jquery/jquery.treeview.css">
    <link rel="stylesheet" type="text/css" href="includes/jquery/ui.tabs.css">
    <link rel="stylesheet" type="text/css" href="includes/jquery/thickbox.css">
    <link rel="stylesheet" type="text/css" href="includes/jquery/productPicker.css">
    <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script type="text/javascript" src="includes/menu.js"></script>
    <script type="text/javascript" src="includes/general.js"></script>
    <script type="text/javascript" src="includes/zenmagick.js"></script>
    <script type="text/javascript" src="includes/jquery/jquery-1.3.2.min.js"></script>
    <script type="text/javascript" src="includes/jquery/ui.tabs.js"></script>
    <script type="text/javascript" src="includes/jquery/thickbox-3.1.pack.js"></script>
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
  <body id="b_catalog_manager" onload="init()">
    <?php require DIR_WS_INCLUDES . 'header.php'; ?>

    <?php if (ZMMessages::instance()->hasMessages()) { ?>
        <ul id="messages">
        <?php foreach (ZMMessages::instance()->getMessages() as $message) { ?>
            <li class="<?php echo $message->getType() ?>"><?php echo $message->getText() ?></li>
        <?php } ?>
        </ul>
    <?php } ?>

    <div id="main">
      <?php echo zm_catalog_tree(ZMCategories::instance()->getCategoryTree(), '', ZMSettings::get('admin.isShowCatalogTreeProducts')); ?>
      <div id="content">
        <?php if (0 < count($tabInfo)) { ?>
            <div id="main-tab-container">
              <ul>
                <?php foreach ($tabInfo as $info) { ?>
                  <li><a href="#<?php echo $info['fkt'] ?>"><span><?php echo $info['item']->getTitle() ?></span></a></li>
                <?php } ?>
              </ul>
              <?php $activeTab = 1; ?>
              <?php foreach ($tabInfo as $index => $info) { 
                if ($info['fkt'] == $selectedFkt) { $activeTab = ($index+1); }
                ?>
                <div id="<?php echo $info['fkt'] ?>" style="position:relative;">
                    <?php if (ZMMessages::instance()->hasMessages()) { ?>
                        <ul id="messages" style="margin-left:0">
                        <?php foreach (ZMMessages::instance()->getMessages() as $message) { ?>
                            <li class="<?php echo $message->getType() ?>"><?php echo $message->getText() ?></li>
                        <?php } ?>
                        </ul>
                    <?php } ?>
                    <?php 
                    if (null != $info['page']) {
                        echo $info['page']->getContents($request);
                    } else { ?><h2>Invalid Contents Function: <?php echo $info['fkt'] ?></h2><?php } ?>
                </div>
              <?php } ?>
            </div>
        <?php } ?>
        <script type="text/javascript">
            $(function() { 
              $('#main-tab-container > ul').tabs(<?php echo $activeTab ?>, { fxSlide: true, fxFade: true, fxSpeed: 'fast' }); 
            });
        </script>
      </div>
    </div>

  </body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>
<?php ob_end_flush(); ?>
