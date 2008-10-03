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
    define('ZM_ADMIN_PAGE', true);
    require_once('includes/application_top.php');
    $zmPage = ZMRequest::getParameter('zmPage', 'index.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
       "http://www.w3.org/TR/html4/loose.dtd">
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <title><?php zm_l10n("Admin :: ZenMagick") ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <link rel="stylesheet" type="text/css" href="includes/zenmagick.css">
    <script type="text/javascript" src="includes/zenmagick.js"></script>
    <style type="text/css">
      body {margin:15px auto;width:97%;text-align:center;}
      #wrapper {text-align:left;border:1px solid #ccc;}
      #content {width:100%;height:600px;border:none;margin:12px 0 0 0;}
    </style>
    <script type="text/javascript">
        function resizeIframe(name) { 
            var iframe = document.getElementById(name);
            var height = 0;
            if (iframe.contentDocument) {
                height = iframe.contentDocument.body.scrollHeight;
            }
            if (0 != height) {
                iframe.style.height = (height+10) + 'px'
            }
        } 
    </script>
  </head>
  <body onload="resizeIframe('content')">
    <div id="wrapper">
      <?php require(DIR_WS_INCLUDES . 'zenmagick_header.php'); ?>
      <iframe id="content" src="<?php echo $zmPage ?>">
    </div>
  </body>
</html>
