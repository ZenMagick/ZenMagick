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


/**
 * UI sort options.
 * <p>TODO: overlap with ZMProductSort</p>
 *
 * @author mano
 * @package net.radebatz.zenmagick
 * @version $Id$
 */
class ZMSortOptions {
    var $sort_;


    // create new instance
    function ZMSortOptions() {
    global $zm_request;
        $this->sort_ = $zm_request->getSortOrder();
    }

    // create new instance
    function __construct() {
        $this->ZMSortOptions();
    }

    function __destruct() {
    }


    function getSortIndex() { return substr($this->sort_, 0, 1); }
    function isDecending() { return 'd' == substr($this->sort_, 1); }
    function getSortName() {
    global $_ZM_PRODUCT_LISTING_FIELDS;
        $sortIndex = $this->getSortIndex();
        foreach ($_ZM_PRODUCT_LISTING_FIELDS as $name => $index) {
            if ($index == $sortIndex) {
                return $name;
            }
        }
        return null;
    }

    function hasActiveOption() {
    global $_ZM_PRODUCT_LISTING_FIELDS;
        $options = array();
        foreach ($_ZM_PRODUCT_LISTING_FIELDS as $name => $index) {
            if ($this->getSortIndex() == $index) {
                return true;
            }
        }
        return false;
    }
    function hasOptions() {
    global $_ZM_PRODUCT_LISTING_FIELDS;
        return 0 != count($_ZM_PRODUCT_LISTING_FIELDS);
    }
    function getOptions() {
    global $_ZM_PRODUCT_LISTING_FIELDS;
        $options = array();
        foreach ($_ZM_PRODUCT_LISTING_FIELDS as $name => $index) {
            $option = new ZMSortOption($name, $index);
            if ($this->getSortIndex() == $index) {
                $option->active_ = true;
                $option->decending_ = $this->isDecending();
            }
            array_push($options, $option);
        }

        return $options;
    }

}

?>
