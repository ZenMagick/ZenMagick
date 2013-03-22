<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2012 zenmagick.org
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
use ZenMagick\Base\Toolbox;
use ZenMagick\Base\ZMObject;

/**
 * Base result list sorter.
 *
 * <p>Right now, result lists may be sorted with a single sorter only.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.resultlist
 */
class ZMResultListSorter extends ZMObject
{
    protected $id;
    protected $name;
    protected $sortId;
    protected $descending;

    /**
     * Create a new result list sorter.
     *
     * @param string id Optional sorter id.
     * @param string name Optional sorter name.
     * @param string sortId Optional sort id.
     */
    public function __construct($id=null, $name='', $sortId=null)
    {
        parent::__construct();

        $this->id = $id;
        $this->sortId = $sortId;
        $this->descending = Toolbox::endsWith($this->sortId, '_d');
        if (Toolbox::endsWith($this->sortId, '_a') || $this->descending) {
            $this->sortId = substr($this->sortId, 0, strlen($this->sortId)-2);
        }
    }

    /**
     * Returns true if the current sort order is descending.
     *
     * @return boolean <code>true</code> if the current sort order is descending.
     */
    public function isDescending() { return $this->descending; }

    /**
     * Returns one or more <code>ZMSortOption</code>s supported by this sorter.
     *
     * @return array An array of one or more <code>ZMSortOption</code> instances.
     */
    public function getOptions() { $values = array(); return $values; }

    /**
     * Sort the given list according to this sorters criteria.
     *
     * @param array list The list to sort.
     * @return array The sorted list.
     */
    public function sort($list) { return $list; }

    /**
     * Returns <code>true</code> if this sorter is currently active.
     *
     * <p>This translates into: one of the supported sort options is active.</p>
     *
     * @return boolean <code>true</code> if the sorter is active, <code>false</code> if not.
     */
    public function isActive() { return false; }

    /**
     * Returns the sorters unique id.
     *
     * @return string The sorter id.
     */
    public function getId() { return $this->id; }

    /**
     * Returns the sorter name.
     *
     * @return string The sorter name.
     */
    public function getName() { return $this->name; }

    /**
     * Set the sorters unique id.
     *
     * @param string id The sorter id.
     */
    public function setId($id) { $this->id = $id; }

    /**
     * Set the sorter name.
     *
     * @param string name The sorter name.
     */
    public function setName($name) { $this->name = $name; }

    /**
     * Returns the sorters sort id.
     *
     * @return string The sortid.
     */
    public function getSortId() { return $this->sortId; }

    /**
     * Set the sorters sorter id.
     *
     * @param string id The sort id.
     */
    public function setSortId($sortId) { $this->sortId = $sortId; }

    /**
     * Set the descending flag.
     *
     * @param boolean descending The new value.
     */
    public function setDescending($descending)
    {
        $this->descending = $descending;
    }

}
