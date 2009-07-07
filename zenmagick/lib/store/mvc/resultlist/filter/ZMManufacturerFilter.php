<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.resultlist.filter
 * @version $Id: ZMManufacturerFilter.php 1966 2009-02-14 10:52:50Z dermanomann $
 */
class ZMManufacturerFilter extends ZMResultListFilter {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('mfilter', zm_l10n_get('Manufacturer'), ZMRequest::getParameter('mfilter'));
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Return <code>true</code> if the given object is to be excluded.
     *
     * @param mixed obj The obecjt to examine.
     * @return boolean <code>true</code> if the object is to be excluded, <code>false</code> if not.
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
                $option = ZMLoader::make("FilterOption", $manufacturer->getName(), $manufacturer->getId(), $manufacturer->getId() == $this->filterValues_[0]);
                $options[$option->getId()] = $option;
            }
        }

        return $options;
    }


}

?>
