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
 * @version $Id$
 */
?>
<?php

if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

    if (class_exists('ZMInstallationPatcher')) {
        /*
         * Check for any patches that need be applied. If they are disabled or can't be applied, 
         * display installation menu item for manual installation.
         */

        $isInstallation = null !== strpos($_SERVER['SCRIPT_FILENAME'], 'zmInstallation.php');
        $duringUninstall = isset($_GET) && array_key_exists('uninstall', $_GET);
        $installer = new ZMInstallationPatcher();
        if (!$duringUninstall && !$isInstallation && $installer->isPatchesOpen()) {
            // try to run all patches
            $installer->patch();
        }

        $adminMenuPatch = $installer->getPatchForId('adminMenu');
        if ($adminMenuPatch && $adminMenuPatch->isOpen()) {
            // only if no ZenMagick menu item
            $za_contents[] = array('text' => zm_l10n_get("ZenMagick Installation"), 'link' => zen_href_link('zmInstallation.php', '', 'NONSSL'));
        }
    }
    
    foreach (ZMAdminMenu::getItemsForParentId(ZMAdminMenu::MENU_TOOLS) as $item) {
        $za_contents[] = array('text' => $item->getTitle(), 'link' => $item->getURL(), '', 'NONSSL');
    }

?>
