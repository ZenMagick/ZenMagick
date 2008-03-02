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

if ('true' == $zm_request->getRequestParameter('remove', 'false')) {
    // destry myself
    unlink(DIR_FS_ADMIN.ZM_ADMINFN_CONSOLE);
  	zen_redirect(zen_href_link(FILENAME_DEFAULT));
}

$code = stripslashes($zm_request->getParameter('code', '', false));
$zm_result = null;
if ('' != $code) {
    ob_start();
    eval($code);
    $zm_result = ob_get_contents();                                                                                       
    ob_end_clean();                                                                                                   
}
if ('' == $code) {
    $code = "\$product = ZMProducts::instance()->getProductForId(8);\n"
           ."if (null != \$product) {\n"
           ."   echo \$product->getName().\":\\n\".\$product->getDescription();\n"
           ."   \n"
           ."} else {\n"
           ."   echo 'not found';\n"
           ."}\n";
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
    <title><?php zm_l10n("ZenMagick Console") ?></title>
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
  <body id="b_console" onload="init()">
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>

    <div id="main">
      <div id="content">
          <form action="<?php echo ZM_ADMINFN_CONSOLE ?>" method="post">
              <fieldset>
                  <legend><?php zm_l10n("<code>PHP</code> Console") ?></legend>
                  <label for="code"><?php zm_l10n("Code:") ?></label>
                  <textarea id="name" name="code" rows="10" cols="80"><?php zm_htmlencode($code) ?></textarea><br>
                  <input type="submit" value="<?php zm_l10n("Execute") ?>">
                  <?php if (null != $zm_result) { ?>
                      <div id="console">
                          <?php echo str_replace("\n", "<br>", zm_htmlencode($zm_result, false)); ?>
                      </div>
                  <?php } ?>
              </fieldset>
          </form>
          <a href="<?php echo ZM_ADMINFN_CONSOLE ?>?remove=true" onclick="return zm_user_confirm('Remove console ?');"><?php zm_l10n("Remove Console from admin menu") ?></a>
      </div>
    </div>

  </body>
</html>
