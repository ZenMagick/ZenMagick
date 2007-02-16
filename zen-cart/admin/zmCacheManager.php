<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
require_once(DIR_FS_CATALOG.'zenmagick/init.php'); 


    // clear
    if (isset($_POST) && array_key_exists('pageCache', $_POST)) {
        $pageCache = $zm_runtime->getPageCache();
        $ok = $pageCache->clear();
        $zm_messages->add('Clear page cache ' . ($ok ? 'successful' : 'failed'), $ok ? 'msg' : 'error');
    }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
    <title>ZenMagick Cache Manager</title>
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
  <body id="b_cache" onload="init()">
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
      <h2><?php zm_l10n("ZenMagick Cache Manager") ?> <?php if (zm_setting('isPageCacheEnabled')) { zm_l10n("- ACTIVE"); } else { zm_l10n("- DISABLED"); } ?></h2>

        <form action="<?php echo ZM_ADMINFN_CACHE ?>" method="post" onsubmit="return zm_user_confirm('Clear cache ?');">
          <fieldset class="cahce">
          <legend><?php zm_l10n("Clear Cache Options") ?></legend>

            <input type="checkbox" id="pageCache" name="pageCache" value="x">
            <label for="pageCache"><?php zm_l10n("Page Cache") ?></label>
            <br>
            <div class="submit">
              <input type="submit" value="Clear">
            </div>
          </fieldset>
        </form>

    </div>

  </body>
</html>
