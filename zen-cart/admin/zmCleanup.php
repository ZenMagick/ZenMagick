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
require_once('includes/zmCatalogDtree.php');
require_once('../zenmagick/init.php');
require_once('../zenmagick/admin_init.php');

    $_zm_obsolete_files = array();
    array_push($_zm_obsolete_files, DIR_FS_ADMIN . "includes/boxes/extra_boxes/zmFeatures_catalog_dhtml.php");
    array_push($_zm_obsolete_files, DIR_FS_CATALOG . "zenmagick/themes/default/controller/DefaultController.php");
    array_push($_zm_obsolete_files, DIR_FS_CATALOG . "zenmagick/themes/default/controller");

    // delete
    foreach ($_POST['obsolete'] as $file) {
      if (is_file($file)) {
        unlink($file);
      } else if (is_dir($file)) {
        rmdir($file);
      }
    }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
    <title>ZenMagick Installation Cleanup</title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <link rel="stylesheet" type="text/css" href="includes/zenmagick.css">
    <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script type="text/javascript" src="includes/menu.js"></script>
    <script type="text/javascript" src="includes/general.js"></script>
    <script type="text/javascript">
      function init() {
        cssjsmenu('navbar');
        if (document.getElementById) {
          var kill = document.getElementById('hoverJS');
          kill.disabled = true;
        }
      }
      function select_all(box) {
        var boxes = document.getElementsByTagName('input');
        for (var ii=0; ii<boxes.length; ++ii) {
          if (0 == boxes[ii].name.indexOf('obsolete')) {
            boxes[ii].checked = box.checked;
          }
        }
      }
    </script>
  </head>
  <body id="b_features" onload="init()">
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>

    <div id="main">
      <div id="content">
        <h2>ZenMagick Installation Cleanup</h2>

          <?php $obsolete = zm_get_obsolete_files(); ?>
          <?php if (0 == count($obsolete)) { ?>
            <h3>Congratulations - Your installation appears to be clean !</h3>
          <?php } else { ?>
            <form action="<?php echo ZM_ADMINFN_CLEANUP ?>" method="post">
              <fieldset>
                <legend>Select the files you wish to delete</legend>
                <?php $ii = 0; foreach ($obsolete as $file) { $name = zm_mk_relative($file); ?>
                  <input type="checkbox" id="obsolete-<?php echo $ii ?>" name="obsolete[]" value="<?php echo $file ?>">
                  <label for="obsolete-<?php echo $ii ?>"><?php echo $name ?></label><br>
                <?php ++$ii; } ?>
                <input type="checkbox" id="all" name="all" value="" onclick="select_all(this)">
                <label for="all">Select/Unselect All</label><br>
              </fieldset>
              <input type="submit" value="Remove">
            </form>
          <?php } ?>
        </div>
    </div>

  </body>
</html>
