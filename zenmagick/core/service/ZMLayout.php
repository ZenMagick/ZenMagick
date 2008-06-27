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
 * @package org.zenmagick.service
 * @version $Id$
 */
class ZMLayout extends ZMObject {
    private $leftColEnabled_;
    private $rightColEnabled_;
    private $leftColBoxes_;
    private $rightColBoxes_;
    private $tableMeta;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->leftColEnabled_ = true;
        $this->rightColEnabled_ = true;
        $this->leftColBoxes_ = null;
        $this->rightColBoxes_ = null;
        $this->tableMeta = array();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('Layout');
    }


    /**
     * Enable/disable the left column.
     *
     * @param boolean bool If <code>true</code> the left column will be displayed.
     */
    public function setLeftColEnabled($bool) { $this->leftColEnabled_ = $bool; }

    /**
     * Enable/disable the right column.
     *
     * @param boolean bool If <code>true</code> the right column will be displayed.
     */
    public function setRightColEnabled($bool) { $this->rightColEnabled_ = $bool; }

    /**
     * Set the boxes for the left column.
     *
     * @param array boxes List of box names to be displayed in the left column.
     */
    public function setLeftColBoxes($boxes) { if (is_array($boxes)) $this->leftColBoxes_ = $boxes; }

    /**
     * Set the boxes for the right column.
     *
     * @param array boxes List of box names to be displayed in the right column.
     */
    public function setRightColBoxes($boxes) { if (is_array($boxes)) $this->rightColBoxes_ = $boxes; }

    /**
     * Checks if the left column is active.
     *
     * @return boolean <code>true</code> if the column is active, <code>false</code> if not.
     */
    public function isLeftColEnabled() { return $this->leftColEnabled_; }

    /**
     * Checks if the right column is active.
     *
     * @return boolean <code>true</code> if the column is active, <code>false</code> if not.
     */
    public function isRightColEnabled() { return $this->rightColEnabled_; }

    /**
     * Get the box names for the left column.
     *
     * @return array Name of all boxes to be displayed.
     */
    public function getLeftColBoxNames() {
        if (null != $this->leftColBoxes_) {
            return $this->leftColBoxes_;
        }

        $theme = ZMRuntime::getTheme();

        $sql = "SELECT DISTINCT layout_box_name from " . TABLE_LAYOUT_BOXES . "
                WHERE layout_box_location = 0
                  AND layout_box_status = '1'
                  AND layout_template = :themeId
                ORDER BY layout_box_sort_order";
        $boxes = array();
        foreach (ZMRuntime::getDatabase()->query($sql, array('themeId' => ZMThemes::instance()->getZCThemeId()), TABLE_LAYOUT_BOXES) as $boxInfo) {
            // boxes use .php
            $box = str_replace('.php', ZMSettings::get('templateSuffix'), $boxInfo['name']);
            if (file_exists($theme->getBoxesDir() . $box) 
              || (ZMSettings::get('isEnableThemeDefaults') && file_exists(ZMRuntime::getThemesDir().ZM_DEFAULT_THEME.'/'.ZM_THEME_BOXES_DIR.$box))) {
                $boxes[] = $box;
            }
        }

        return $boxes;
    }

    /**
     * Get the box names for the right column.
     *
     * @return array Name of all boxes to be displayed.
     */
    public function getRightColBoxNames() {
        if (null != $this->rightColBoxes_) {
            return $this->rightColBoxes_;
        }

        $theme = ZMRuntime::getTheme();

        $sql = "SELECT DISTINCT layout_box_name from " . TABLE_LAYOUT_BOXES . "
                WHERE layout_box_location = 1
                  AND layout_box_status = '1'
                  AND layout_template = :themeId
                ORDER BY layout_box_sort_order";
        $boxes = array();
        foreach (ZMRuntime::getDatabase()->query($sql, array('themeId' => ZMThemes::instance()->getZCThemeId()), TABLE_LAYOUT_BOXES) as $boxInfo) {
            // boxes use .php
            $box = str_replace('.php', ZMSettings::get('templateSuffix'), $boxInfo['name']);
            if (file_exists($theme->getBoxesDir() . $box) 
              || (ZMSettings::get('isEnableThemeDefaults') && file_exists(ZMRuntime::getThemesDir().ZM_DEFAULT_THEME.'/'.ZM_THEME_BOXES_DIR.$box))) {
                $boxes[] = $box;
            }
        }

        return $boxes;
    }

    /**
     * Get the field length of a particular column.
     *
     * @param string table The database table name.
     * @param string field The field/column name.
     * @return int The field length.
     */
    public function getFieldLength($table, $field) {
        if (!isset($this->tableMeta[$table])) {
            $db = ZMRuntime::getDB();
            $this->tableMeta[$table] = $db->MetaColumns($table);
        }

        return $this->tableMeta[$table][strtoupper($field)]->max_length;
    }

    /**
     * Find the product template for a given product.
     *
     * @param int productId The product id.
     * @return string The template name to be used to display product details.
     */
    public function getProductTemplate($productId) {
        // default
        $template = 'product';

        $sql = "SELECT products_type 
                FROM " . TABLE_PRODUCTS . "
                WHERE products_id = :id";
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('id' => $productId), TABLE_PRODUCTS);
        if (null !== $result) {
            $typeId = $result['type'];
            $sql = "SELECT type_handler 
                    FROM " . TABLE_PRODUCT_TYPES . "
                    WHERE type_id = :id";
            $result = ZMRuntime::getDatabase()->querySingle($sql, array('id' => $typeId), TABLE_PRODUCT_TYPES);
            if (null !== $result) {
                $template = $result['handler'];
            }
        }

        return $template . '_info';
    }

}

?>
