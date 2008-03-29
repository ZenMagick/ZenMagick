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
 * Base result list sorter.
 *
 * @author mano
 * @package org.zenmagick.resultlist
 * @version $Id$
 */
class ZMResultListSorter extends ZMObject {
    var $id_;
    var $name_;
    var $defaultSortId_;
    var $sortId_;
    var $decending_;


    /**
     * Create a new result list sorter.
     *
     * @param string id An optional sorter id.
     * @param string name An optional sorter name.
     */
    function __construct($id=null, $name='') {
        parent::__construct();

        $this->id_ = $id;
        $this->defaultSortId_ = null;
        $this->sortId_ = ZMRequest::getSortId();
        $this->decending_ = zm_ends_with($this->sortId_, '_d');
        if (zm_ends_with($this->sortId_, '_a') || $this->decending_) {
            $this->sortId_ = substr($this->sortId_, 0, strlen($this->sortId_)-2);
        }
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Set the default sort id.
     *
     * @param string sortId The default sort id.
     */
    function setDefaultSortId($sortId) {
        $this->defaultSortId_ = $sortId;
        if (null === $this->sortId_) {
            $this->sortId_ = $this->defaultSortId_;
        }
    }


    /**
     * Returns true if the current sort order is descending.
     *
     * @return boolean <code>true</code> if the current sort order is decending.
     */
    function isDecending() { return $this->decending_; }

    /**
     * Returns one or more <code>ZMSortOption</code>s supported by this sorter.
     *
     * @return array An array of one or more <code>ZMSortOption</code> instances.
     */
    function getOptions() { $values = array(); return $values; }

    /**
     * Sort the given list according to this sorters criteria.
     *
     * @param array list The list to sort.
     * @return array The sorted list.
     */
    function sort($list) { return $list; }

    /**
     * Returns <code>true</code> if this sorter is currently active.
     *
     * <p>This translates into: one of the supported sort options is active.</p>
     *
     * @return boolean <code>true</code> if the sorter is active, <code>false</code> if not.
     */
    function isActive() { return false; }

    /**
     * Returns the sorters unique id.
     *
     * @return string The sorter id.
     */
    function getId() { return $this->id_; }


    /**
     * Returns the sorter name.
     *
     * @return string The sorter name.
     */
    function getName() { return $this->name_; }
}

?>
