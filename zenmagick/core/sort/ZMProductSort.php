<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
 *
 * Protions Copyright (c) 2003 The zen-cart developers
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


// static
$_ZM_PRODUCT_LISTING_FIELDS = array(
    'PRODUCT_LIST_MODEL' => PRODUCT_LIST_MODEL,
    'PRODUCT_LIST_NAME' => PRODUCT_LIST_NAME,
    'PRODUCT_LIST_MANUFACTURER' => PRODUCT_LIST_MANUFACTURER,
    'PRODUCT_LIST_PRICE' => PRODUCT_LIST_PRICE,
    'PRODUCT_LIST_QUANTITY' => PRODUCT_LIST_QUANTITY,
    'PRODUCT_LIST_WEIGHT' => PRODUCT_LIST_WEIGHT
);
// keep sorted non zero values
$_ZM_PRODUCT_LISTING_FIELDS = array_diff($_ZM_PRODUCT_LISTING_FIELDS, array(0));
asort($_ZM_PRODUCT_LISTING_FIELDS);

$_ZM_PRODUCT_SORT_NAMES = array(
    'PRODUCT_LIST_MODEL' => '_cmpModel',
    'PRODUCT_LIST_NAME' => '_cmpName',
    'PRODUCT_LIST_MANUFACTURER' => '_cmpManufacturerName',
    'PRODUCT_LIST_PRICE' => '_cmpPrice',
    'PRODUCT_LIST_WEIGHT' => '_cmpWeight'
);


/**
 * Product sorter.
 *
 * @author mano
 * @package net.radebatz.zenmagick.sort
 * @version $Id$
 */
class ZMProductSort { //extends ZMSort {
    var $sort_;


    // create new instance
    function ZMProductSort() {
    global $zm_request;
        //parent::__construct();

        $this->sort_ = $zm_request->getSortOrder();
    }

    // create new instance
    function __construct() {
        $this->ZMProductSort();
    }

    function __destruct() {
    }


    function _getSortIndex() { return substr($this->sort_, 0, 1); }
    function _isDecending() { return 'd' == substr($this->sort_, 1); }
    function _getSortName() {
    global $_ZM_PRODUCT_LISTING_FIELDS;
        $sortIndex = $this->_getSortIndex();
        foreach ($_ZM_PRODUCT_LISTING_FIELDS as $name => $index) {
            if ($index == $sortIndex) {
                return $name;
            }
        }
        return null;
    }
    function _getSortMethod() {
    global $_ZM_PRODUCT_SORT_NAMES;
        $sortName = $this->_getSortName();
        return array_key_exists($sortName, $_ZM_PRODUCT_SORT_NAMES) ? $_ZM_PRODUCT_SORT_NAMES[$sortName] : null;
    }

    function _cmpModel($a, $b) { return ($a->getModel() == $b->getModel()) ? 0 : ($a->getModel() > $b->getModel()) ? +1 : -1; }
    function _cmpName($a, $b) { return ($a->getName() == $b->getName()) ? 0 : ($a->getName() > $b->getName()) ? +1 : -1; }
    function _cmpManufacturerName($a, $b) { return ($a->getManufacturerName() == $b->getManufacturerName()) ? 0 : ($a->getManufacturerName() > $b->getManufacturerName()) ? +1 : -1; }
    function _cmpPrice($a, $b) { return ($a->getPrice() == $b->getPrice()) ? 0 : ($a->getPrice() > $b->getPrice()) ? +1 : -1; }
    function _cmpWeight($a, $b) { return ($a->getWeight() == $b->getWeight()) ? 0 : ($a->getWeight() > $b->getWeight()) ? +1 : -1; }




    /** Sort API */

    // is sort active
    function isActive() { return null != $this->sort_; }

    // sort the given list
    function sort($list) {
        $sortMethod = $this->_getSortMethod();
        if (null != $sortMethod) {
            uasort($list, array($this, $sortMethod));
        }
        if ($this->_isDecending()) {
            $list = array_reverse($list);
        }
        return $list;
    }

}

?>
