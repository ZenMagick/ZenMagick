<?php
/*
 * ZenMagick - Smart e-commerce
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

use ZenMagick\Base\Runtime;
use ZenMagick\Base\Database\QueryDetails;
use ZenMagick\Base\Database\SqlAware;

/**
 * Order sorter.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.mvc.resultlist.sorter
 */
class ZMOrderSorter extends ZMResultListSorter implements SqlAware
{
    // supported sorts
    private $methods = array(
        'date' => '_cmpDate',
        'status' => '_cmpStatus'
    );
    // as options
    private $options = array(
        'date' => 'Date',
        'status' => 'Status'
    );
    // as SQL
    private $sql = array(
        // XXX: allow to use mapped name
        'date' => 'date_purchased',
        'status' => 'orders_status'
    );

    /**
     * Create new instance.
     */
    public function __construct()
    {
        parent::__construct('osort', '', Runtime::getContainer()->get('request')->query->getAlnum('sort_id'));
    }

    // sort functions
    public function _cmpDate($a, $b)
    {
        return ($a->getOrderDate() == $b->getOrderDate()) ? 0 : ($a->getOrderDate() > $b->getOrderDate()) ? +1 : -1;
    }
    public function _cmpStatus($a, $b)
    {
        return ($a->getOrderStatusId() == $b->getOrderStatusId()) ? 0 : ($a->getOrderStatusId() > $b->getOrderStatusId()) ? +1 : -1;
    }

    /**
     * Returns <code>true</code> if this sorter is currently active.
     *
     * <p>This translates into: one of the supported sort options is active.</p>
     *
     * @return boolean <code>true</code> if the sorter is active, <code>false</code> if not.
     */
    public function isActive()
    {
        return array_key_exists($this->sortId, $this->methods);
    }

    /**
     * Sort the given list according to this sorters criteria.
     *
     * @param array list The list to sort.
     * @return array The sorted list.
     */
    public function sort($list)
    {
        if (!$this->isActive() || !is_array($list)) {
            return $list;
        }

        $method = $this->methods[$this->sortId];
        usort($list, array($this, $method));
        if ($this->isDescending()) {
            $list = array_reverse($list);
        }

        return $list;
    }

    /**
     * Returns one or more <code>ZMSortOption</code>s supported by this sorter.
     *
     * @return array An array of one or more <code>ZMSortOption</code> instances.
     */
    public function getOptions()
    {
        $options = array();
        foreach ($this->options as $id => $name) {
            $option = new ZMSortOption($name, $id, $id == $this->sortId, $this->isDescending());
            $options[] = $option;
        }

        return $options;
    }

    /**
     * {@inheritDoc}
     */
    public function getQueryDetails($method=null, $args=array())
    {
        if (!$this->isActive() || !array_key_exists($this->sortId, $this->sql)) {
            return null;
        }

        return new QueryDetails(\ZMRuntime::getDatabase(), $this->sql[$this->sortId] . ($this->isDescending() ? ' DESC' : ' ASC'));
    }

}
