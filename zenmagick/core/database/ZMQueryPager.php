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
 * @package org.zenmagick.database.provider
 * @version $Id$
 */
class ZMQueryPager extends ZMObject {
    private $queryDetails_;


    /**
     * Create new instance for the given query.
     *
     * @param ZMQueryDetails queryDetails The query details.
     */
    public function __construct($queryDetails) {
        $this->queryDetails_ = $queryDetails;
    }


    /**
     * Get total number of results.
     *
     * @return int The total number of results available.
     */
    public function getTotalNumberOfResults() {
        $sql = $this->queryDetails_->getSql();

        $pos_to = strlen($sql);
        $query_lower = strtolower($sql);
        $pos_from = strpos($query_lower, ' from', 0);

        $pos_group_by = strpos($query_lower, ' group by', $pos_from);
        if (($pos_group_by < $pos_to) && ($pos_group_by != false)) { $pos_to = $pos_group_by; }

        $pos_having = strpos($query_lower, ' having', $pos_from);
        if (($pos_having < $pos_to) && ($pos_having != false)) { $pos_to = $pos_having; }

        $pos_order_by = strpos($query_lower, ' order by', $pos_from);
        if (($pos_order_by < $pos_to) && ($pos_order_by != false)) { $pos_to = $pos_order_by; }

        if (strpos($query_lower, 'distinct') || strpos($query_lower, 'group by')) {
            $count_string = 'distinct *';
        } else {
            $count_string = '*';
        }

        // count total results
        $count_query = "select count(" . $count_string . ") as total " . substr($sql, $pos_from, ($pos_to - $pos_from));
        $result = ZMRuntime::getDatabase()->querySingle($count_query, $this->queryDetails_->getArgs(), $this->queryDetails_->getMapping(), ZMDatabase::MODEL_RAW);
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

        $total = $this->getTotalNumberOfResults();

        // XXX: delegate to result list
        $number_of_pages = ceil($total / $resultList->getPagination());
        $offset = ($resultList->getPagination() * ($resultList->getPageNumber() - 1));
        if ($offset < 0) { $offset = 0; }
        $sql .= " limit " . $offset . ", " . $resultList->getPagination();

        return $this->queryDetails_->query($sql);
    }

}

?>
