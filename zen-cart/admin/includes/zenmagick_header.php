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
    
    $isZMAdmin = defined('ZM_ADMIN_PAGE');

    if (!$isZMAdmin) { return; }

    // build full zen-cart menu
    zm_add_menu_item(new ZMMenuItem(null, 'config', zm_l10n_get('Configuration')));
    $configGroups = ZMConfig::instance()->getConfigGroups();
    foreach ($configGroups as $group) {
        $id = strtolower($group->getName());
        $id = str_replace(' ', '', $id);
        $id = str_replace('/', '-', $id);
        zm_add_menu_item(new ZMMenuItem('config', $id, zm_l10n_get($group->getName()), 'zmAdmin.php?zmPage=configuration.php&amp;gID='.$group->getId()));
    }

    ob_start();
    $zc_menus = array('catalog', 'modules', 'customers', 'taxes', 'localization', 'reports', 'tools', 'gv_admin', 'extras', 'zenmagick');
    foreach ($zc_menus as $zm_menu) {
        require(DIR_WS_BOXES . $zm_menu . '_dhtml.php');
        if ('zenmagick' == $zm_menu) {
            $za_heading = $zm_heading;
            $za_contents = $zm_contents;
        }
        zm_add_menu_item(new ZMMenuItem(null, $zm_menu, zm_l10n_get($za_heading['text'])));
        foreach ($za_contents as $item) {
            $id = strtolower($item['text']);
            $id = str_replace(' ', '', $id);
            $id = str_replace('/', '-', $id);
            $link = $item['link'];
            if ($isZMAdmin) {
                $url = parse_url($link);
                $link = 'zmAdmin.php?zmPage='.basename($url['path']).'&amp;'.$url['query'];
            }
            zm_add_menu_item(new ZMMenuItem($zm_menu, $id, zm_l10n_get($item['text']), $link));
        }
    }
    zm_add_menu_item(new ZMMenuItem(null, 'plugins', zm_l10n_get('Plugins')));
    ob_end_clean();

?>

<div id="header">
  <div id="info">
  logo and such | <a href="zmAdmin.php">Admin Home</a> | <a href="<?php echo zen_catalog_href_link() ?>">Store Home</a> | <a href="<?php echo zen_href_link(FILENAME_LOGOFF) ?>">Logoff</a>
  </div>
  <div id="secnav">
    <?php zm_build_menu(); ?>
  </div>
  <br style="clear:both;">
</div>
