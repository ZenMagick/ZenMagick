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
 * Base result list filter.
 *
 * @author mano
 * @package net.radebatz.zenmagick.resultlist
 * @version $Id$
 */
class ZMResultListFilter {
    var $list_;
    var $id_;
    var $filterValues_;


    // create new instance
    function ZMResultListFilter($id=null) {
    global $zm_request;

        $this->id_ = $id;
        $this->filterValues_ = explode(",", $zm_request->getRequestParameter($this->id_, ''));
    }

    // create new instance
    function __construct($list, $id=null) {
        $this->ZMResultListFilter($list, $id);
    }

    function __destruct() {
    }


    /** Filter API */

    /**
     * Set the result list we belong to.
     *
     * <p>This is important to be able to analyze the list to generate the list of all
     * available options (if based on the current data).</p>
     *
     * @param ZMResultList list The current result list.
     */
    function setResultList($list) { $this->list_ =& $list; }

    /**
     * Filter the given list using the filters <code>exclude($obj)</code> method.
     *
     * @param array list The list to filter.
     * @return array The filtered list.
     */
    function filter($list) { 
        $remaining = array();
        foreach ($list as $obj) {
            if (!$this->exclude($obj)) {
                array_push($remaining, $obj);
            }
        }

        return $remaining;
    }

    /**
     * Return <code>true</code> if the given object is to be excluded.
     *
     * @param mixed obj The obecjt to examine.
     * @return bool <code>true</code> if the object is to be excluded, <code>false</code> if not.
     */
    function exclude($obj) { return false; }

    /**
     * Returns <code>true</code> if this filter is currently active.
     *
     * @return bool <code>true</code> if the filter is active, <code>false</code> if not.
     */
    function isActive() {
    global $zm_request;

        return null != $zm_request->getRequestParameter($this->id_, null);
    }

    /**
     * Returns <code>true</code> if this filter supports multiple values as filter value.
     *
     * @return bool <code>true</code> if multiple filter values are supported, <code>false</code> if not.
     */
    function isMultiSelection() { return false; }

    /**
     * Returns a list of active filter values.
     *
     * <p>If <code>isActive()</code> returns <code>false</code>, this list is guranteed to be empty.</p>
     *
     * @return array An array of string values.
     */
    function getSelectedValues() { return $this->filterValues_; }

    /**
     * Returns a list of all available filter values.
     *
     * @return array An array of string values.
     */
    function getOptions() { $options = array(); return $options; }

    /**
     * Returns the filters unique form field name.
     *
     * @return string The filters unique form field name.
     */
    function getId() { return $this->id_; }

}

?>
