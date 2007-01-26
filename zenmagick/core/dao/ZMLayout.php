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
 */
?>
<?php


/**
 * Layout stuff.
 *
 * @author mano
 * @package net.radebatz.zenmagick.dao
 * @version $Id$
 */
class ZMLayout extends ZMDao {
    var $leftColEnabled_;
    var $rightColEnabled_;
    var $leftColBoxes_;
    var $rightColBoxes_;


    /**
     * Default c'tor.
     */
    function ZMLayout() {
        parent::__construct();

        $this->leftColEnabled_ = true;
        $this->rightColEnabled_ = true;
        $this->leftColBoxes_ = null;
        $this->rightColBoxes_ = null;
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMLayout();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Enable/disable the left column.
     *
     * @param bool bool If <code>true</code> the left column will be displayed.
     */
    function setLeftColEnabled($bool) { $this->leftColEnabled_ = $bool; }

    /**
     * Enable/disable the right column.
     *
     * @param bool bool If <code>true</code> the right column will be displayed.
     */
    function setRightColEnabled($bool) { $this->rightColEnabled_ = $bool; }

    /**
     * Set the boxes for the left column.
     *
     * @param array boxes List of box names to be displayed in the left column.
     */
    function setLeftColBoxes($boxes) { if (is_array($boxes)) $this->leftColBoxes_ = $boxes; }

    /**
     * Set the boxes for the right column.
     *
     * @param array boxes List of box names to be displayed in the right column.
     */
    function setRightColBoxes($boxes) { if (is_array($boxes)) $this->rightColBoxes_ = $boxes; }

    /**
     * Checks if the left column is active.
     *
     * @return bool <code>true</code> if the column is active, <code>false</code> if not.
     */
    function isLeftColEnabled() { return zm_setting('isEnableLeftColumn') && $this->leftColEnabled_; }

    /**
     * Checks if the right column is active.
     *
     * @return bool <code>true</code> if the column is active, <code>false</code> if not.
     */
    function isRightColEnabled() { return zm_setting('isEnableRightColumn') && $this->rightColEnabled_; }

    /**
     * Get the box names for the left column.
     *
     * @return array Name of all boxes to be displayed.
     */
    function getLeftColBoxNames() {
    global $zm_runtime;
        if (null != $this->leftColBoxes_)
            return $this->leftColBoxes_;

        $sql = "select distinct layout_box_name from " . TABLE_LAYOUT_BOXES . "
                where layout_box_location = 0
                  and layout_box_status = '1'
                  and layout_template = :themeId
                order by layout_box_sort_order";
        $sql = $this->db_->bindVars($sql, ':themeId', $zm_runtime->getRawThemeId(), 'integer');
        $results = $this->db_->Execute($sql);

        $boxes = array();
        while (!$results->EOF) {
            $box = $results->fields['layout_box_name'];
            if (file_exists($zm_runtime->getThemeBoxPath() . $box) || file_exists($zm_runtime->getThemeBoxPath('default') . $box)) {
                array_push($boxes, $box);
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

        $sql = "select distinct layout_box_name, layout_template from " . TABLE_LAYOUT_BOXES . "
                where layout_box_location = 1
                  and layout_box_status = '1'
                  and layout_template = :themeId
                order by layout_box_sort_order";
        $sql = $this->db_->bindVars($sql, ':themeId', $zm_runtime->getRawThemeId(), 'integer');
        $results = $this->db_->Execute($sql);

        $boxes = array();
        while (!$results->EOF) {
            $box = $results->fields['layout_box_name'];
            if (file_exists($zm_runtime->getThemeBoxPath() . $box) || file_exists($zm_runtime->getThemeBoxPath('default') . $box)) {
                array_push($boxes, $box);
            }
            $results->MoveNext();
        }

        return $boxes;
    }

}

?>
