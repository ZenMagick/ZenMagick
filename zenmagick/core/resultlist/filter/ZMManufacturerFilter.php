<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 * Filter products by manufacturer.
 *
 * @author mano
 * @package net.radebatz.zenmagick.resultlist.filter
 * @version $Id$
 */
class ZMManufacturerFilter extends ZMResultListFilter {

    /**
     * Default c'tor.
     */
    function ZMManufacturerFilter() {
        parent::__construct('mfilter', zm_l10n_get('Manufacturer'));
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMManufacturerFilter();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Return <code>true</code> if the given object is to be excluded.
     *
     * @param mixed obj The obecjt to examine.
     * @return bool <code>true</code> if the object is to be excluded, <code>false</code> if not.
     */
    function exclude($obj) { return $obj->getManufacturerId() != $this->filterValues_[0]; }

    /**
     * Returns a list of all available filter values.
     *
     * @return array An array of string values.
     */
    function getOptions() {
        $options = array();
        foreach ($this->list_->getAllResults() as $result) {
            $manufacturer = $result->getManufacturer();
            if (null != $manufacturer) {
                $option =& $this->create("FilterOption", $manufacturer->getName(), $manufacturer->getId(), $manufacturer->getId() == $this->filterValues_[0]);
                $options[$option->getId()] = $option;
            }
        }

        return $options;
    }


}

?>
