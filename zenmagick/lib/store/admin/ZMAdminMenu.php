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
 */
?>
<?php


/**
 * The admin menu.
 *
 * <p>This is a singleton with all methods being static.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.store.admin
 * @version $Id$
 */
class ZMAdminMenu extends ZMObject {
    /** Plugins menu id. */
    const MENU_PLUGINS = 'menu_plugins';
    /** Tools menu id. */
    const MENU_TOOLS = 'menu_tools';
    /** Menu id for Catalog Manager tabs. */
    const MENU_CATALOG_MANAGER_TAB = 'catalog_manager_tab';
    private static $items_ = array();


    /**
     * Add a admin menu item.
     *
     * @param ZMAdminMenuItem item The new item.
     */
    public static function addItem($item) {
        self::$items_[] = $item;
    }

    /**
     * Display the admin menu.
     *
     * @param string parent Parent menu id (used for recursive calls, do not set).
     */
    public static function buildMenu($parent=null) {
        ob_start();
        $first = true;
        $size = count (self::$items_);
        for ($ii=0; $ii < $size; ++$ii) { 
            $item = self::$items_[$ii];
            if (null == $item) {
                continue;
            }
            if ($parent == $item->getParent()) {
                if ($first) {
                    echo "<ul";
                    if (null == $parent) {
                        echo ' class="submenu"';
                    }
                    echo '>';
                }
                echo '<li'.($first ? ' class="first"' : '').'>';
                $first = false;
                if (null == $parent) {
                    // menu only
                    echo $item->getTitle();
                } else {
                    $url = $item->getURL();
                    if (ZMLangUtils::startsWith($url, 'fkt:')) {
                        $url = ZMRequest::instance()->getToolbox()->net->url('zmPluginPage.php', 'fkt=' . substr($url, 4), false, false);
                    }
                    echo '<a href="'.$url.'">'.$item->getTitle().'</a>';
                }
                self::buildMenu($item->getId());
                echo "</li>";
            }
        }

        if (!$first) {
            echo "</ul>";
        }

        if (null === $parent) {
            return ob_get_clean();
        }

        return "";
    }

    /**
     * Get all child items for the given id.
     *
     * @param string parentId The parent id.
     * @return array A list of <code>ZMAdminMenuItem</code> instances.
     */
    public static function getItemsForParentId($parentId) {
        $items = array();
        foreach (self::$items_ as $item) {
            if (null !== $item && $item->getParent() == $parentId) {
                $items[] = $item;
            }
        }

        return $items;
    }

}

?>
