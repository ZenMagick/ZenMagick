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
?>
<?php  
require('includes/application_top.php');
require('includes/zmCatalogDtree.php');

    // capture output as CMPs might redirect...
    ob_start();

    $productId = $zm_request->getProductId();
    $productId = 0 == $productId ? '' : $productId;
    $cPath = $zm_request->getCategoryPath();
    $view = $zm_request->getRequestParameter('view');

    // default for new selection
    if ('' != $productId && null == $view) {
        $view = 'product';
    } else if ('' != $cPath && null == $view) {
        $view = 'category';
    }

    // common nav params
    $navParams = '&amp;products_id='.$productId.'&amp;cPath='.$cPath;

    // set up navigation defaults
    $nav = array(
        'category' => ZM_ADMINFN_CATALOG_MANAGER.'?view=category'.$navParams,
        'product' => ZM_ADMINFN_CATALOG_MANAGER.'?view=product'.$navParams,
        'attributes' => ZM_ADMINFN_CATALOG_MANAGER.'?view=attributes'.$navParams,
        'features' => ZM_ADMINFN_CATALOG_MANAGER.'?view=features'.$navParams
    );
    if ('' == $cPath) { $nav['category'] = ''; }
    if ('' == $productId) { $nav['product'] = ''; $nav['attributes'] = ''; $nav['features'] = ''; }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
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
  <body id="b_catalog_manager" onload="init()">
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>

    <div id="main">
      <div id="cnav" class="dtree">
        <?php zm_catalog_dtree(ZM_ADMINFN_CATALOG_MANAGER, ZM_ADMINFN_CATALOG_MANAGER, false) ?>
      </div>

      <div id="content">
<?php /*
          <div id="pnav">
            <ul class="hnav">
              <?php foreach ($nav as $name => $url) { ?>
                  <?php if ('' != $url) { $act = $view == $name ? ' class="act"' : ''; ?>
                      <li><a <?php echo $act ?> href="<?php echo $url ?>"><?php echo $name ?></a></li>
                  <?php } else { ?>
                      <li class="dis"><?php echo $name ?></li>
                  <?php } ?>
              <?php } ?>
            </ul>
          </div>
          <div id="pcont">
            <?php if ('' != $view) { include('zmCMP'.$view.'.php'); } else { ?>
              <h2>Please select a category or product...</h2>
            <?php } ?>
        </div>
      </div>
 */ ?>
            <?php include('zmCMPfeatures.php'); ?>
        </div>
    </div>

  </body>
</html>
<?php ob_end_flush(); ?>
