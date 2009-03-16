<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * Paginate a query.
 *
 * @author DerManoMann
 * @package org.zenmagick.database
 * @version $Id$
 */
class ZMQueryPager extends ZMObject {
    private $queryDetails_;
    private $orderBy_;
    private $filters_;


    /**
     * Create new instance for the given query.
     *
     * @param ZMQueryDetails queryDetails The query details.
     */
    public function __construct($queryDetails) {
        $this->queryDetails_ = $queryDetails;
        $this->orderBy_ = '';
        $this->filters_ = array();
    }

    /**
     * Set order by clause(s).
     *
     * @param string orderBy The order by condition(s).
     */
    public function setOrderBy($orderBy) {
        $this->orderBy_ = $orderBy;
    }

    /**
     * Add a filter clause.
     *
     * @param string filter The filter condition.
     */
    public function addFilter($filter) {
        $this->filters_[] = $filter;
    }

    /**
     * Get the filter conditions.
     *
     * @return string The sql.
     */
    protected function getFilterSQL() {
        $sql = '';
        foreach ($this->filters_ as $filter) {
            if (!empty($sql)) {
                $sql .= ' and ';
            }
            $sql .= $filter;
        }
        return $sql;
    }

    /**
     * Get total number of results.
     *
     * @return int The total number of results available.
     */
    public function getTotalNumberOfResults() {
        $queryDetails = $this->queryDetails_;
        $sql = $queryDetails->getSql();

        $pos_to = strlen($sql);
        $query_lower = strtolower($sql);

        $pos_select = strpos($query_lower, 'select ', 0);

        $pos_from = strpos($query_lower, ' from', $pos_select);

        $pos_where = strpos($query_lower, ' where', $pos_from);

        $pos_group_by = strpos($query_lower, ' group by', $pos_from);
        if (($pos_group_by < $pos_to) && ($pos_group_by != false)) { $pos_to = $pos_group_by; }

        $pos_having = strpos($query_lower, ' having', $pos_from);
        if (($pos_having < $pos_to) && ($pos_having != false)) { $pos_to = $pos_having; }

        $pos_order_by = strpos($query_lower, ' order by', $pos_from);
        if (($pos_order_by < $pos_to) && ($pos_order_by != false)) { $pos_to = $pos_order_by; }

        if (null == ($count_string = $queryDetails->getCountCol())) {
            $count_string = trim(preg_replace('/distinct/i', '', substr($sql, $pos_select+7, $pos_from-6)));
        }
        if (strpos($query_lower, 'distinct') || strpos($query_lower, 'group by')) {
            $count_string = 'distinct '.$count_string;
        }

        // count total results
        $count_query = "select count(" . $count_string . ") as total " . substr($sql, $pos_from, ($pos_to - $pos_from));

        // apply filters (if any)
        $filter = $this->getFilterSQL();
        if (!empty($filter)) {
            if (false === $pos_where) {
                  $count_query .= ' where '.$filter;
            } else {
                  $count_query .= ' and '.$filter;
            }
        }

        $result = ZMRuntime::getDatabase()->querySingle($count_query, $queryDetails->getArgs(), $queryDetails->getMapping(), ZMDatabase::MODEL_RAW);
        return (int)$result['total'];
    }

    /**
     * Get results.
     *
     * @param ZMResultList resultList The paging handler.
     * @return array A list of results.
     */
    public function getResults($resultList) {
        $sql = $this->queryDetails_->getSql();
        $query_lower = strtolower($sql);

        $total = $this->getTotalNumberOfResults();
        $number_of_pages = ceil($total / $resultList->getPagination());
        $offset = ($resultList->getPagination() * ($resultList->getPageNumber() - 1));
        if ($offset < 0) { $offset = 0; }
        if (!empty($this->orderBy_)) {
            if (false !== ($pos = strpos($query_lower, 'order by'))) {
                //$sql = substr($sql, 0, $pos) . 'order by '.$this->orderBy_;
                // keep original order also
                $sql = preg_replace('/order by /i', 'order by '.$this->orderBy_.',', $sql);
            } else {
                $sql .= ' order by '.$this->orderBy_;
            }
        }

        $filter = $this->getFilterSQL();
        if (!empty($filter)) {
            $pos_from = strpos($query_lower, ' from', 0);
            $pos_where = strpos($query_lower, ' where', $pos_from);
            if (false !== $pos_where) {
                $sql = preg_replace('/ where /i', ' where '.$filter.' and ', $sql);
            } else {
                $pos_insert = strlen($sql);
                if (false !== ($pos_group_by = strpos($query_lower, ' group by', $pos_from))) {
                    $pos_insert = $pos_group_by;
                } else if (false !== ($pos_having = strpos($query_lower, ' having', $pos_from))) {
                    $pos_insert = $pos_having;
                } else if (false !== ($pos_order_by = strpos($query_lower, ' order by', $pos_from))) {
                    $pos_insert = $pos_order_by;
                }
                $sql = substr($sql, 0, $pos_insert) . ' where ' . $filter . substr($sql, $pos_insert);
            }
        }

        $sql .= " limit " . $offset . ", " . $resultList->getPagination();
        return $this->queryDetails_->query($sql);
    }

}

?>
