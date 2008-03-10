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
 * A list that might span multiple pages.
 *
 * @author mano
 * @package org.zenmagick.resultlist
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
        if (null == $results) {
            $this->results_ = array();
        }
        $this->filters_ = array();
        $this->sorters_ = array();
        $this->pagination_ = $pagination;
        $page = 0 == $page ? ZMRequest::getPageIndex() : $page;
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
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Apply all configured filter.
     *
     * @param array list The result list to process.
     * @return array The remaining results.
     */
    function _applyFilters($list) {
        foreach ($this->filters_ as $filter) {
            if (!$filter->isActive())
                continue;

            $list = $filter->filter($list);
        }

        return $list;
    }

    /**
     * Apply all configured sorter.
     *
     * @param array list The result list to process.
     * @return array The sorted results.
     */
    function _applySort($list) {
        foreach ($this->sorters_ as $sorter) {
            if (!$sorter->isActive())
                continue;

            $list = $sorter->sort($list);
        }

        return $list;
    }

    /**
     * Calculate end index.
     *
     * @return int The end index.
     */
    function _getEndIndex() {
        $index = $this->page_ * $this->pagination_;
	      return $index > $this->getNumberOfResults() ? $this->getNumberOfResults() : $index;
    }

    /**
     * Calculate start index.
     *
     * @return int The start index.
     */
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
     * @param boolean refresh If <code>true</code>, the result list is automatically refreshed.
     */
    function addFilter($filter, $refresh=false) {
        $this->filters_[$filter->getId()] = $filter;
        $filter->setResultList($this);
        $refresh && $this->refresh();
    }

    /**
     * Add a sorter to this result list.
     *
     * @param ZMResultListSorter sorter The new sorter.
     * @param boolean refresh If <code>true</code>, the result list is automatically refreshed.
     */
    function addSorter($sorter, $refresh=false) {
        $this->sorters_[$sorter->getId()] = $sorter;
        $refresh && $this->refresh();
    }

    /**
     * Check if any sorter are active.
     *
     * @return boolean <code>true</code> if sorter are active, <code>false</code> if not.
     */
    function hasSorters() { return 0 != count($this->sorters_) && 1 < count($this->results_); }

    /**
     * Get all sorter.
     *
     * @return array A list of <code>ZMResultListSorter</code>.
     */
    function getSorters() { return $this->sorters_; }

    /**
     * Check if any filter are active.
     *
     * @return boolean <code>true</code> if filter are active, <code>false</code> if not.
     */
    function hasFilters() {
        foreach ($this->filters_ as $filter) {
            if ($filter->isAvailable()) {
                return true;
            }
        }
    }

    /**
     * Get all filter.
     *
     * @return array A list of <code>ZMResultListFilter</code>.
     */
    function getFilters() { return $this->filters_; }

    /**
     * Checks if there are results available.
     *
     * @return boolean <code>true</code> if results are available, <code>false</code> if not.
     */
    function hasResults() { return 0 != count($this->results_); }

    /**
     * Returns all results.
     *
     * @return array All results.
     */
    function getAllResults() { return $this->results_; }

    /**
     * Count results.
     *
     * @return int The number if results.
     */
    function getNumberOfResults() { return count($this->results_); }

    /**
     * Get the current page number.
     *
     * @return int The current page number.
     */
    function getCurrentPageNumber() { return $this->page_; }

    /**
     * Get the configured pagination.
     *
     * @return int The number of results per page.
     */
    function getPagination() { return $this->pagination_; }

    /**
     * Set the configured pagination.
     *
     * @param int pagination The number of results per page.
     */
    function setPagination($pagination) { $this->pagination_ = $pagination; }

    /**
     * Get the calculated number of pages.
     *
     * @return int The number of pages.
     */
    function getNumberOfPages() { return ceil($this->getNumberOfResults() / $this->pagination_); }

    /**
     * Check if a previous page is available.
     *
     * @return boolean <code>true</code> if a previous page is available, <code>false</code> if not.
     */
    function hasPreviousPage() { return 1 < $this->getCurrentPageNumber(); }

    /**
     * Check if a next page is available.
     *
     * @return boolean <code>true</code> if a next page is available, <code>false</code> if not.
     */
    function hasNextPage() { return $this->getCurrentPageNumber() < $this->getNumberOfPages(); }

    /**
     * Get the previous page number.
     *
     * @return int The previous page number; (default: 1)
     */
    function getPreviousPageNumber() { return $this->hasPreviousPage() ? ($this->getCurrentPageNumber()-1) : 1; }

    /**
     * Get the next page number.
     *
     * @return int The next page number.
     */
    function getNextPageNumber() { return $this->hasNextPage() ? ($this->getCurrentPageNumber()+1) : $this->getNumberOfPages(); }

    /**
     * Get the results for the current page.
     *
     * @return array List of results for the current page.
     */
    function getResults() {
        $start = $this->_getStartIndex();
        $end = $this->_getEndIndex();
        // use this as array_slice might reorder the array if keys are not in order
        $slice = array();
        for ($ii=0; $ii <($end-$start); ++$ii) {
            $slice[] = $this->results_[$start+$ii];
        }
        return $slice;
    }

    /**
     * Build a URL pointing to the previous page.
     *
     * @param boolean secure If <code>true</code>, the URI will be secure; default is <code>false</code>.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A URL pointing to the previous page.
     */
    function getPreviousURL($secure, $echo=ZM_ECHO_DEFAULT) {
        if ($secure) {
            $url = zm_secure_href(null, "&page=".$this->getPreviousPageNumber(), false);
        } else {
            $url = zm_href(null, "&page=".$this->getPreviousPageNumber(), false);
        }

        if ($echo) echo $url;
        return $url;
    }

    /**
     * Build a URL pointing to the next page.
     *
     * @param boolean secure If <code>true</code>, the URI will be secure; default is <code>false</code>.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A URL pointing to the next page.
     */
    function getNextURL($secure=false, $echo=ZM_ECHO_DEFAULT) {
        if ($secure) {
            $url = zm_secure_href(null, "&page=".$this->getNextPageNumber(), false);
        } else {
            $url = zm_href(null, "&page=".$this->getNextPageNumber(), false);
        }

        if ($echo) echo $url;
        return $url;
    }

}

?>
