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

    /**
     * Add a admin menu item.
     *
     * @package org.zenmagick.admin
     * @param ZMMenuItem item The new item.
     */
    function zm_add_menu_item($item) {
    global $_zm_menu;

        if (!isset($_zm_menu)) {
            $_zm_menu = array();
        }

        $_zm_menu[] = $item;
    }


    /**
     * Display the admin menu.
     *
     * @package org.zenmagick.admin
     * @param string parent Parent menu id (used for recursive calls).
     */
    function zm_build_menu($parent=null) {
    global $_zm_menu;

        if (!isset($_zm_menu)) {
            $_zm_menu = array();
        }

        $first = true;
        $size = count ($_zm_menu);
        for ($ii=0; $ii < $size; ++$ii) { 
            $item = $_zm_menu[$ii];
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
                    if (ZMTools::startsWith($url, 'fkt:')) {
                        $url = ZMToolbox::instance()->net->url('zmPluginPage.php', 'fkt=' . substr($url, 4));
                    }
                    echo '<a href="'.$url.'">'.$item->getTitle().'</a>';
                }
                zm_build_menu($item->getId());
                echo "</li>";
            }
        }

        if (!$first) {
            echo "</ul>";
        }
    }

?>
