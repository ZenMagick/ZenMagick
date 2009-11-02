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
?><?php
require_once 'includes/application_top.php';

  $fkt = ZMRequest::instance()->getParameter('fkt');
  // try to resolve plugin page controller
  if (class_exists($fkt)) {
      $controller = ZMLoader::make($fkt);
      $page = $controller->process(ZMRequest::instance());
  } else if (function_exists($fkt)) {
      ob_start();
      $page = $fkt(); 
      $contents = ob_get_clean();
      if (!empty($contents)) {
          $page->setContents($contents);
      }
  }
  if (null != $page && $page->isRefresh()) {
      ZMRequest::instance()->redirect($request->getToolbox()->net->url('', 'fkt='.$fkt, ZMRequest::instance()->isSecure(), false));
  }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
    <title><?php zm_l10n("Plugin :: %s :: ZenMagick", (null != $page ? $page->getTitle() : '')) ?></title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <link rel="stylesheet" type="text/css" href="includes/zenmagick.css">
    <link rel="stylesheet" type="text/css" href="includes/jquery/jquery.treeview.css">
    <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script type="text/javascript" src="includes/menu.js"></script>
    <script type="text/javascript" src="includes/general.js"></script>
    <script type="text/javascript" src="includes/zenmagick.js"></script>
    <script type="text/javascript" src="includes/jquery/jquery-1.3.2.min.js"></script>
    <?php if (null != $page) { echo $page->getHeader(); } ?>
  </head>
  <body id="b_plugin_page">

    <?php require DIR_WS_INCLUDES . 'header.php'; ?>

    <?php if (ZMMessages::instance()->hasMessages()) { ?>
        <ul id="messages">
        <?php foreach (ZMMessages::instance()->getMessages() as $message) { ?>
            <li class="<?php echo $message->getType() ?>"><?php echo $message->getText() ?></li>
        <?php } ?>
        </ul>
    <?php } ?>

    <div id="main">
      <div id="content">
        <?php if (null != $page) {
            echo $page->getContents();
        } else { ?>
            <h2>Invalid Plugin Function: <?php echo $fkt ?></h2>
        <?php } ?>
      </div>
    </div>

  </body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>
