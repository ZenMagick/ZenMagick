<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 * Wrapper <code>ZMResultList</code> implementation for zen-cart's splitPageResults class.
 *
 * @author mano
 * @package org.zenmagick.plugins.zm_split_page_result_list
 * @version $Id$
 */
class SplitPageResultList extends ZMResultList {
    var $splitPageResults_;


    /**
     * Create new result list.
     *
     * @param array The results.
     * @param splitPageResults splitPageResults The <code>splitPageResults</code> instance.
     * @param int pagination Number of results per page (default is 10)
     */
    function __construct($results, $splitPageResults, $pagination=10) {
        parent::__construct($results, $pagination);
        $this->splitPageResults_ = $splitPageResults;
    }

    /**
     * Create new result list.
     *
     * @param array The results.
     * @param splitPageResults splitPageResults The <code>splitPageResults</code> instance.
     * @param int pagination Number of results per page (default is 10)
     */
    function SplitPageResultList($results, $splitPageResults, $pagination=10) {
        $this->__construct($results, $splitPageResults, $pagination);
    }

    /**
     * Default d'tor.
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
    function _applyFilters($list) { return $list; }

    /**
     * Apply all configured sorter.
     *
     * @param array list The result list to process.
     * @return array The sorted results.
     */
    function _applySort($list) { return $list; }

    /**
     * Needs to be called to recalculate the final result list based on filters and sorters.
     */
    function refresh() { }


    /**
     * Add a filter to this result list.
     *
     * @param ZMResultListFilter filter The new filter.
     * @param boolean refresh If <code>true</code>, the result list is automatically refreshed.
     */
    function addFilter($filter, $refresh=false) {
        $this->filters_[$filter->getId()] =& $filter;
        $filter->setResultList($this);
    }

    /**
     * Add a sorter to this result list.
     *
     * @param ZMResultListSorter sorter The new sorter.
     * @param boolean refresh If <code>true</code>, the result list is automatically refreshed.
     */
    function addSorter($sorter, $refresh=false) {
        $this->sorters_[$sorter->getId()] =& $sorter;
    }

    /**
     * Count results.
     *
     * @return int The number if results.
     */
    function getNumberOfResults() { return $this->splitPageResults_->number_of_rows; }

    /**
     * Get the current page number.
     *
     * @return int The current page number.
     */
    function getCurrentPageNumber() { return $this->splitPageResults_->current_page_number; }

    /**
     * Get the calculated number of pages.
     *
     * @return int The number of pages.
     */
    function getNumberOfPages() { return $this->splitPageResults_->number_of_pages; }

    /**
     * Get the results for the current page.
     *
     * @return array List of results for the current page.
     */
    function getResults() { return $this->results_; }

}

?>
