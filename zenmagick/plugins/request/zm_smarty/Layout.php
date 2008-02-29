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
 */
?>
<?php


/**
 * Layout stuff.
 *
 * @author mano
 * @package org.zenmagick.plugins.zm_smarty
 * @version $Id$
 */
class Layout extends ZMLayout {

    /**
     * Default c'tor.
     */
    function Layout() {
        $this->__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        parent::__construct();
    }


    /**
     * Get the box names for the left column.
     *
     * @return array Name of all boxes to be displayed.
     */
    function getLeftColBoxNames() {
    global $zm_runtime;

        if (null != $this->leftColBoxes_)
            return $this->leftColBoxes_;

        $db = ZMRuntime::getDB();
        $sql = "select distinct layout_box_name from " . TABLE_LAYOUT_BOXES . "
                where layout_box_location = 0
                  and layout_box_status = '1'
                  and layout_template = :themeId
                order by layout_box_sort_order";
        $sql = $db->bindVars($sql, ':themeId', $zm_runtime->getZCThemeId(), 'string');
        $results = $db->Execute($sql);

        $theme = $zm_runtime->getTheme();
        $boxes = array();
        while (!$results->EOF) {
            $box = str_replace('.php', '.tpl', $results->fields['layout_box_name']);
            if (file_exists($theme->getBoxesDir() . $box)) {
                array_push($boxes, $theme->getBoxesDir() . $box);
            }
            $results->MoveNext();
        }

        return $boxes;
    }

    /**
     * Get the box names for the right column.
     *
     * @return array Name of all boxes to be displayed.
     */
    function getRightColBoxNames() {
    global $zm_runtime;

        if (null != $this->rightColBoxes_)
            return $this->rightColBoxes_;

        $db = ZMRuntime::getDB();
        $sql = "select distinct layout_box_name, layout_template from " . TABLE_LAYOUT_BOXES . "
                where layout_box_location = 1
                  and layout_box_status = '1'
                  and layout_template = :themeId
                order by layout_box_sort_order";
        $sql = $db->bindVars($sql, ':themeId', $zm_runtime->getZCThemeId(), 'string');
        $results = $db->Execute($sql);

        $theme = $zm_runtime->getTheme();
        $boxes = array();
        while (!$results->EOF) {
            $box = str_replace('.php', '.tpl', $results->fields['layout_box_name']);
            if (file_exists($theme->getBoxesDir() . $box)) {
                array_push($boxes, $theme->getBoxesDir() . $box);
            }
            $results->MoveNext();
        }

        return $boxes;
    }

}

?>
