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
 * @version $Id$
 */
?>
<?php

if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

/*
 * Hook to re-patch zen-cart after an update to display the ZenMagick menu
 *
 * As this patches admin/boxes/extras_dhtml.php, it needs to run before that...
 */

require_once('../zenmagick/init.php');
require_once('../zenmagick/admin_init.php');

    //********** enable ZenMagick menu *************
    define('ZEN_ADMIN_FILE', DIR_WS_BOXES . "extras_dhtml.php");
    $contents = file_get_contents(ZEN_ADMIN_FILE);
    if (false === strpos($contents, "zenmagick_dhtml.php") && zm_setting('isAdminAutoRebuild')) {
        // patch
        if (is_writeable(ZEN_ADMIN_FILE)) {
            zm_log("** ZenMagick: patching zen-cart admin to auto-enable ZenMagick admin menu", 1);
            $handle = fopen(ZEN_ADMIN_FILE, "at");
            fwrite($handle, "\n<?php require(DIR_WS_BOXES . 'zenmagick_dhtml.php'); ?>");
            fclose($handle);
        } else {
            zm_log("** ZenMagick: no permission to patch zen-cart admin extras_dhtml.php", 1);
        }
    }


    //******** auto build ZenMagick specific sidebox dummies *************
    $handle = opendir($zm_runtime->getThemeBoxPath());
    $zmBoxes = array();
    while (false !== ($file = readdir($handle))) {
        $zmBoxes[$file] = $file;
    }
    closedir($handle);

    define('ZEN_DIR_FS_BOXES', DIR_FS_CATALOG.DIR_WS_MODULES."sideboxes/");
    $zcBoxes = array();
    $handle = opendir(ZEN_DIR_FS_BOXES);
    while (false !== ($file = readdir($handle))) {
        $zcBoxes[$file] = $file;
    }
    closedir($handle);

    $missingBoxes = array();
    foreach ($zmBoxes as $box) {
        if (!array_key_exists($box, $zcBoxes) && '.' != $box && '..' != $box && zm_ends_with($box, '.php')) {
            $missingBoxes[$box] = $box;
        } 
    }

    // create empty dummy files...
    foreach ($missingBoxes as $box) {
        if (!file_exists(ZEN_DIR_FS_BOXES.$box)) {
            if (is_writeable(ZEN_DIR_FS_BOXES.$box)) {
                $handle = fopen(ZEN_DIR_FS_BOXES.$box, 'a');
                fwrite($handle, '<?php /** dummy file created by ZenMagick **/ ?>');
                fclose($handle);
            } else {
            zm_log("** ZenMagick: no permission to create dummy sideboxes", 1);
            }
        }
    }

?>
