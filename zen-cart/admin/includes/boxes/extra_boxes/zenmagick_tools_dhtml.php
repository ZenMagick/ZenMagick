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

$contents = file_get_contents(DIR_WS_BOXES . "extras_dhtml.php");

if (false === strpos($contents, "zenmagick_dhtml.php") && zm_setting('isAdminAutoRebuild')) {
    // patch
    zm_log("** ZenMagick: patching zen-cart admin to auto-enable ZenMagick admin menu", 1);
    $handle = fopen(DIR_WS_BOXES . "extras_dhtml.php", "at");
    fwrite($handle, "\n<?php require(DIR_WS_BOXES . 'zenmagick_dhtml.php'); ?>");
    fclose($handle);
}
?>
