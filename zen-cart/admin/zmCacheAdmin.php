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
require('includes/application_top.php');

  $ii = 0; foreach (ZMCaches::instance()->getCaches() as $key => $cacheInfo) {
      if ('x' == ZMRequest::getParameter('cache_'.++$ii)) {
          $ok = $cacheInfo['instance']->clear();
          ZMMessages::instance()->add(zm_l10n_get('Clear page cache \'' . $cacheInfo['group'] . '\' ' . ($ok ? 'successful' : 'failed')), $ok ? 'msg' : 'error');
      }
  }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
    <title>Cache Admin :: ZenMagick</title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <link rel="stylesheet" type="text/css" href="includes/zenmagick.css">
    <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script type="text/javascript" src="includes/menu.js"></script>
    <script type="text/javascript" src="includes/general.js"></script>
    <script type="text/javascript" src="includes/zenmagick.js"></script>
  </head>
  <body id="b_cache">
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>

    <?php if (ZMMessages::instance()->hasMessages()) { ?>
        <ul id="messages">
        <?php foreach (ZMMessages::instance()->getMessages() as $message) { ?>
            <li class="<?php echo $message->getType() ?>"><?php echo $message->getText() ?></li>
        <?php } ?>
        </ul>
    <?php } ?>

    <div id="main">
      <div id="content">
        <h2><?php zm_l10n("ZenMagick Cache Admin") ?></h2>

        <form action="<?php echo ZM_ADMINFN_CACHE ?>" method="post" onsubmit="return zm_user_confirm('Clear selected?');">
          <fieldset>
            <legend><?php zm_l10n("Existing Caches") ?></legend>
              <table cellspacing="0" cellpadding="0">
                <thead>
                  <tr>
                    <th></th>
                    <th>Group</th>
                    <th>Type</th>
                    <th>Config</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $ii = 0; foreach (ZMCaches::instance()->getCaches() as $key => $cacheInfo) { ?>
                    <tr>
                      <td><input type="checkbox" name="cache_<?php echo ++$ii ?>" value="x"></td>
                      <td><?php echo $cacheInfo['group'] ?></td>
                      <td><?php echo $cacheInfo['type'] ?></td>
                      <td><?php print_r($cacheInfo['config']) ?></td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
              <div class="submit">
                  <input type="submit" value="Clear selected caches">
              </div>
          </fieldset>
        </form>


      </div>
    </div>

  </body>
</html>
