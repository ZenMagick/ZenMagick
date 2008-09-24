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
 * A result list base class that handles lists that might span multiple pages.
 *
 * <p>A result list operates on any given set of results. Results do not have to have
 * any specific properties, type specific code is delegated to filters and sorters.</p>
 *
 * <p>Results are obtained via the <code>ZMResultSource</code> object. This defers the actual
 * query to the latest possible moment. Methods may trigger the query if they depend
 * on results; a good example would be <code>getNumberOfResults()</code>.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.rp.resultlist
 * @version $Id$
 */
class ZMSimpleResultList extends ZMObject {
    protected $resultSource_;
    protected $filters_;
    protected $sorters_;
    protected $page_;
    protected $pagination_;
    protected $allResults_;
    protected $results_;


    /**
     * Create new result list.
     */
    function __construct() {
        parent::__construct();
        $this->resultSource_ = null;
        $this->filters_ = array();
        $this->sorters_ = array();
        $this->page_ = 1;
        $this->pagination_ = ZMSettings::get('defaultResultListPagination');
        $this->allResults_ = null;
        $this->results_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Set a source for results.
     *
     * <p>The advantage of using a result source is that alternative implementations are free
     * to ignore these, modify them or replace them as needed. Providing results via
     * the constructor means that the resources used to build that list might be wasted.</p>
     *
     * @param ZMResultSource resultSource A result source.
     */
    public function setResultSource($resultSource) {
        $this->resultSource_ = $resultSource;
        $this->resultSource_->setResultList($this);
        $this->results_ = null;
    }

    /**
     * Add a filter to this result list.
     *
     * @param ZMResultListFilter filter The new filter.
     */
    public function addFilter($filter) {
        if ($filter instanceof ZMResultListFilter) {
            $this->filters_[] = $filter;
            $this->results_ = null;
        }
    }

    /**
     * Add a sorter to this result list.
     *
     * @param ZMResultListSorter sorter The new sorter.
     */
    public function addSorter($sorter) {
        if ($sorter instanceof ZMResultListSorter) {
            $this->sorters_[] = $sorter;
            $this->results_ = null;
        }
    }

    /**
     * Check if any sorter are configured.
     *
     * @return boolean <code>true</code> if sorter are configured, <code>false</code> if not.
     */
    public function hasSorters() {
        return 0 < count($this->sorters_);
    }

    /**
     * Get all configured sorter.
     *
     * @return array A list of <code>ZMResultListSorter</code>.
     */
    public function getSorters() {
        return $this->sorters_;
    }

    /**
     * Check if any filter are active.
     *
     * @return boolean <code>true</code> if filters are configured, <code>false</code> if not.
     */
    public function hasFilters() {
        return 0 < count($this->filters_);
    }

    /**
     * Get all filter.
     *
     * @return array A list of <code>ZMResultListFilter</code>.
     */
    public function getFilters() {
        return $this->filters_;
    }

    /**
     * Checks if there are results available.
     *
     * @return boolean <code>true</code> if results are available, <code>false</code> if not.
     */
    public function hasResults() {
        return 0 < count($this->getResults());
    }

    /**
     * Count results.
     *
     * @return int The number if results.
     */
    public function getNumberOfResults() {
        return count($this->getAllResults());
    }

    /**
     * Get the page number (1-based).
     *
     * @return int The page number.
     */
    public function getPageNumber() {
        return $this->page_;
    }

    /**
     * Set the page number (1-based).
     *
     * @param int page The page number.
     */
    public function setPageNumber($page) {
        $this->page_ = (0 < $page ? $page : 1);
        $this->results_ = null;
    }

    /**
     * Get the configured pagination.
     *
     * @return int The number of results per page.
     */
    public function getPagination() {
        return $this->pagination_;
    }

    /**
     * Set the configured pagination.
     *
     * @param int pagination The number of results per page.
     */
    public function setPagination($pagination) {
        $this->pagination_ = $pagination;
        $this->results_ = null;
    }

    /**
     * Get the calculated number of pages.
     *
     * @return int The number of pages; will return <em>0</em> if no results available.
     */
    public function getNumberOfPages() {
        return (int)ceil($this->getNumberOfResults() / $this->pagination_);
    }

    /**
     * Check if a previous page is available.
     *
     * @return boolean <code>true</code> if a previous page is available, <code>false</code> if not.
     */
    public function hasPreviousPage() {
        return 1 < $this->page_;
    }

    /**
     * Check if a next page is available.
     *
     * @return boolean <code>true</code> if a next page is available, <code>false</code> if not.
     */
    public function hasNextPage() {
        return $this->page_ < $this->getNumberOfPages();
    }

    /**
     * Get the previous page number.
     *
     * @return int The previous page number; (default: 1)
     */
    public function getPreviousPageNumber() {
        return $this->hasPreviousPage() ? ($this->page_ - 1) : 1;
    }

    /**
     * Get the next page number.
     *
     * @return int The next page number.
     */
    public function getNextPageNumber() {
        return $this->hasNextPage() ? ($this->page_ + 1) : $this->getNumberOfPages();
    }

    /**
     * Get the results for the current page.
     *
     * @return array List of results for the current page.
     */
    public function getResults() {
        if (null === $this->results_) {
            $results = $this->getAllResults();

            //TODO: apply filters and sorters
            foreach ($this->sorters_ as $sorter) {
                if ($sorter->isActive()) {
                    $results = $sorter->sort($results);
                }
            }

            $end = $this->page_ * $this->pagination_;
            $end = $end > count($results) ? count($results) : $end;
            $start = ((int)($end / $this->pagination_)) * $this->pagination_;
            $start = ($start == $end && 0 < $end) ? $start - $this->pagination_ : $start;

            // use this as array_slice might reorder the array if keys are not in order
            $this->results_ = array();
            for ($ii=0; $ii <($end-$start); ++$ii) {
                $this->results_[] = $results[$start+$ii];
            }
        }

        return $this->results_;
    }

    /**
     * Get all available results.
     *
     * <p>Please note that depending on the actual implementation this might return the same as
     * <code>getResults()</code>.</p>
     *
     * @return array List of all available results.
     */
    protected function getAllResults() {
        if (null === $this->allResults_) {
            $this->allResults_ = $this->buildResults();
        }
        return $this->allResults_;
    }

    /**
     * Actual method to generate the viewable results.
     *
     * @return array List of result objects.
     */
    protected function buildResults() {
        return $this->resultSource_->getResults();
    }

}

?>
