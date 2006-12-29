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
 * @package net.radebatz.zenmagick.resultlist
 * @version $Id$
 */
class ZMResultList extends ZMObject {
    var $results_;
    var $page_;
    var $pagination_;
    var $filters_;
    var $sorters_;


    /**
     * Create new result list.
     *
     * @param array The results.
     * @param int pagination Number of results per page (default is 10)
     * @param int page The current page number (default is 0)
     */
    function ZMResultList($results, $pagination=10, $page=0) {
    global $zm_request;

        parent::__construct();

        $this->results_ = $results;
        $this->filters_ = array();
        $this->sorters_ = array();
        $this->pagination_ = $pagination;
        $page = 0 == $page ? $zm_request->getPageIndex() : $page;
        $this->page_ = $page;
        $this->refresh();
    }

    /**
     * Create new result list.
     *
     * @param array The results.
     * @param int pagination Number of results per page (default is 10)
     * @param int page The current page number (default is 0)
     */
    function __construct($results, $pagination=10, $page=0) {
        $this->ZMResultList($results, $pagination, $page);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    // apply all active filter to the results
    function _applyFilters($list) {
        // create local copy
        foreach ($this->filters_ as $filter) {
            if (!$filter->isActive())
                continue;

            $list = $filter->filter($list);
        }

        return $list;
    }

    // apply all active sorter to the results
    function _applySort($list) {
        foreach ($this->sorters_ as $sorter) {
            if (!$sorter->isActive())
                continue;

            $list = $sorter->sort($list);
        }

        return $list;
    }

    // calculate end index
    function _getEndIndex() {
        $index = $this->page_ * $this->pagination_;
	      return $index > $this->getNumberOfResults() ? $this->getNumberOfResults() : $index;
    }

    // calculate start index
    function _getStartIndex() { return ($this->page_-1) * $this->pagination_; }


    /**
     * Needs to be called to recalculate the final result list based on filters and sorters.
     */
    function refresh() {
        $this->results_ = $this->_applyFilters($this->results_);
        $this->results_ = $this->_applySort($this->results_);
        if ($this->page_ > $this->getNumberOfPages()) {
            $this->page_ = $this->getNumberOfPages();
        }
    }


    /**
     * Add a filter to this result list.
     *
     * @param ZMResultListFilter filter The new filter.
     * @param bool refresh If <code>true</code>, the result list is automatically refreshed.
     */
    function addFilter($filter, $refresh=false) {
        $this->filters_[$filter->getId()] =& $filter;
        $filter->setResultList($this);
        $refresh && $this->refresh();
    }

    /**
     * Add a sorter to this result list.
     *
     * @param ZMResultListSorter sorter The new sorter.
     * @param bool refresh If <code>true</code>, the result list is automatically refreshed.
     */
    function addSorter($sorter, $refresh=false) {
        $this->sorters_[$sorter->getId()] =& $sorter;
        $refresh && $this->refresh();
    }


    // getter/setter
    function hasSorters() { return 0 != count($this->sorters_) && 1 < count($this->results_); }
    function getSorters() { return $this->sorters_; }
    function hasFilters() {
        foreach ($this->filters_ as $filter) {
            if ($filter->isAvailable()) {
                return true;
            }
        }
    }
    function getFilters() { return $this->filters_; }

    function hasResults() { return 0 != count($this->results_); }
    function getAllResults() { return $this->results_; }
    function getNumberOfResults() { return count($this->results_); }
    function getCurrentPageNumber() { return $this->page_; }
    function getPagination() { return $this->pagination_; }
    function getNumberOfPages() { return ceil($this->getNumberOfResults()/$this->pagination_); }
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
