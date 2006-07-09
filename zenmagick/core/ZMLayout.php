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
 * @package net.radebatz.zenmagick
 * @version $Id$
 */
class ZMLayout {
    var $db_;
    var $leftColEnabled_;
    var $rightColEnabled_;
    var $leftColBoxes_;
    var $rightColBoxes_;


    // create new instance
    function ZMLayout() {
    global $zm_runtime;
        $this->db_ = $zm_runtime->getDB();
        $this->leftColEnabled_ = true;
        $this->rightColEnabled_ = true;
        $this->leftColBoxes_ = null;
        $this->rightColBoxes_ = null;
    }

    // create new instance
    function __construct() {
        $this->ZMLayout();
    }

    function __destruct() {
    }


    function setLeftColEnabled($bool) { $this->leftColEnabled_ = $bool; }
    function setRightColEnabled($bool) { $this->rightColEnabled_ = $bool; }

    function setLeftColBoxes($boxes) { if (is_array($boxes)) $this->leftColBoxes_ = $boxes; }
    function setRightColBoxes($boxes) { if (is_array($boxes)) $this->rightColBoxes_ = $boxes; }

    function isLeftColEnabled() { return zm_setting('isEnableLeftColumn') && $this->leftColEnabled_; }
    function isRightColEnabled() { return zm_setting('isEnableRightColumn') && $this->rightColEnabled_; }

    // get left column boxes
    function getLeftColBoxNames() {
    global $zm_runtime;
        if (null != $this->leftColBoxes_)
            return $this->leftColBoxes_;

        $sql = "select distinct layout_box_name from " . TABLE_LAYOUT_BOXES . "
                where layout_box_location = 0
                  and layout_box_status = '1'
                  and layout_template ='" . $zm_runtime->getRawThemeId() . "'
                order by layout_box_sort_order";
                  /*
                  and (layout_template ='" . $zm_runtime->getThemeId() . "'
                   or layout_template ='" . $zm_runtime->getRawThemeId() . "'
                   or layout_template ='template_" . $zm_runtime->getThemeId() . "')
                   */

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

    // get left column boxes
    function getRightColBoxNames() {
    global $zm_runtime;
        if (null != $this->rightColBoxes_)
            return $this->rightColBoxes_;

        $sql = "select layout_box_name, layout_template from " . TABLE_LAYOUT_BOXES . "
                where layout_box_location = 1
                  and layout_box_status = '1'
                  and layout_template ='" . $zm_runtime->getRawThemeId() . "'
                order by layout_box_sort_order";

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
