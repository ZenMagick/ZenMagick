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
 * A list that might span multiple pages.
 *
 * @author mano
 * @package net.radebatz.zenmagick
 * @version $Id$
 */
class ZMResultList {
    var $results_;
    var $page_;
    var $resultsPerPage_;


    // create new instance
    function ZMResultList($results, $resultsPerPage=10, $page=0) {
    global $zm_request;
        $this->results_ = $this->_applyFilters($results);
        $this->results_ = $this->_applySort($this->results_);
        $this->resultsPerPage_ = $resultsPerPage;
        $page = 0 == $page ? $zm_request->getPageIndex() : $page;
        $this->page_ = $page;
    }

    // create new instance
    function __construct($results, $resultsPerPage=10, $page=0) {
        $this->ZMResultList($results, $resultsPerPage, $page);
    }

    function __destruct() {
    }


    function _applyFilters($list) {
    global $zm_runtime;
        $filters = $zm_runtime->getFilter();

        // create local copy
        $objects = array_merge($list);
        foreach ($filters as $filter) {
            if (!$filter->isActive())
                continue;
            $tmp = array();
            foreach ($objects as $object) {
                if ($filter->isValid($object)) {
                    array_push($tmp, $object);
                }
            }
            $objects = $tmp;
        }
        return $objects;
    }


    function _applySort($list) {
        //XXX: move?
        $sorts = array(new ZMProductSort());

        // create local copy
        foreach ($sorts as $sort) {
            if (!$sort->isActive())
                continue;
            $list = $sort->sort($list);
        }

        return $list;
    }


    function _getEndIndex() {
        $index = $this->page_ * $this->resultsPerPage_;
	      return $index > $this->getNumberOfResults() ? $this->getNumberOfResults() : $index;
    }


    function _getStartIndex() { return ($this->page_-1) * $this->resultsPerPage_; }


    // getter/setter
    function getSortOptions() { return new ZMSortOptions(); } 
    function hasResults() { return 0 != count($this->results_); }
    function getNumberOfResults() { return count($this->results_); }
    function getCurrentPageNumber() { return $this->page_; }
    function getResultsPerPage() { return $this->resultsPerPage_; }
    function getNumberOfPages() { return ceil($this->getNumberOfResults()/$this->resultsPerPage_); }
    function hasPreviousPage() { return 1 < $this->page_; }
    function hasNextPage() { return $this->page_ < $this->getNumberOfPages(); }
    function getPreviousPageNumber() { return $this->hasPreviousPage() ? ($this->page_-1) : 1; }
    function getNextPageNumber() { return $this->hasNextPage() ? ($this->page_+1) : $this->getNumberOfPages(); }
    function getResults() {
        $start = $this->_getStartIndex();
        $end = $this->_getEndIndex();
        return array_slice($this->results_, $start, $end-$start);
    }

    function getPreviousURL($echo=true) {
        $url = zm_href(null, "&page=".$this->getPreviousPageNumber(), false);

        if ($echo) echo $url;
        return $url;
    }
    function getNextURL($echo=true) {
        $url = zm_href(null, "&page=".$this->getNextPageNumber(), false);

        if ($echo) echo $url;
        return $url;
    }

}

?>
