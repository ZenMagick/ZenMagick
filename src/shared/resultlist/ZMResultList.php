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

use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMObject;

/**
 * A (result) list that handles lists spanning multiple pages.
 *
 * <p>A result list operates on any given set of results. Results do not have to have
 * any specific properties, type specific code is delegated to filters and sorters.</p>
 *
 * <p>Results are obtained via the <code>ZMResultSource</code> object. This defers the actual
 * query to the latest possible moment. Methods may trigger the query if they depend
 * on results; a good example for that is, for example, <code>getNumberOfResults()</code>.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.resultlist
 */
class ZMResultList extends ZMObject
{
    protected $resultSource;
    protected $filters;
    protected $sorters;
    protected $page;
    protected $pagination;
    protected $allResults;
    protected $results;

    /**
     * Create new result list.
     */
    public function __construct()
    {
        parent::__construct();
        $this->resultSource = null;
        $this->filters = array();
        $this->sorters = array();
        $this->page = 1;
        $this->pagination = Runtime::getSettings()->get('zenmagick.mvc.resultlist.defaultPagination');
        $this->allResults = null;
        $this->results = null;
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
    public function setResultSource($resultSource)
    {
        $this->resultSource = $resultSource;
        $this->resultSource->setResultList($this);
        $this->results = null;
    }

    /**
     * Add a filter to this result list.
     *
     * @param ZMResultListFilter filter The new filter.
     */
    public function addFilter($filter)
    {
        if ($filter instanceof ZMResultListFilter) {
            $filter->setResultList($this);
            $this->filters[] = $filter;
            $this->results = null;
        }
    }

    /**
     * Add a sorter to this result list.
     *
     * @param ZMResultListSorter sorter The new sorter.
     */
    public function addSorter($sorter)
    {
        if ($sorter instanceof ZMResultListSorter) {
            $this->sorters[] = $sorter;
            $this->results = null;
        }
    }

    /**
     * Check if any sorter are configured.
     *
     * @return boolean <code>true</code> if sorter are configured, <code>false</code> if not.
     */
    public function hasSorters()
    {
        return 0 < count($this->sorters);
    }

    /**
     * Get all configured sorter.
     *
     * @param boolean active Optional flag to get only active sorters; default is <code>false</code for all.
     * @return array A list of <code>ZMResultListSorter</code>.
     */
    public function getSorters($active=false)
    {
        if (!$active) {
            return $this->sorters;
        }

        $list = array();
        foreach ($this->sorters as $sorter) {
            if ($sorter->isActive()) {
                $list[] = $sorter;
            }
        }

        return $list;
    }

    /**
     * Check if any filter are active.
     *
     * @return boolean <code>true</code> if filters are configured, <code>false</code> if not.
     */
    public function hasFilters()
    {
        return 0 < count($this->filters);
    }

    /**
     * Get all filter.
     *
     * @param boolean active Optional flag to get only active sorters; default is <code>false</code for all.
     * @return array A list of <code>ZMResultListFilter</code>.
     */
    public function getFilters($active=false)
    {
        if (!$active) {
            return $this->filters;
        }

        $list = array();
        foreach ($this->filters as $filter) {
            if ($filter->isActive()) {
                $list[] = $filter;
            }
        }

        return $list;
    }

    /**
     * Checks if there are results available.
     *
     * @return boolean <code>true</code> if results are available, <code>false</code> if not.
     */
    public function hasResults()
    {
        return 0 < count($this->getResults());
    }

    /**
     * Count all results.
     *
     * @return int The total number if results.
     */
    public function getNumberOfResults()
    {
        return $this->resultSource->getTotalNumberOfResults();
    }

    /**
     * Get the page number (1-based).
     *
     * @return int The page number.
     */
    public function getPageNumber()
    {
        return $this->page;
    }

    /**
     * Set the page number (1-based).
     *
     * @param int page The page number.
     */
    public function setPageNumber($page)
    {
        $this->page = (0 < $page ? $page : 1);
        $this->results = null;
    }

    /**
     * Get the configured pagination.
     *
     * @return int The number of results per page.
     */
    public function getPagination()
    {
        return $this->pagination;
    }

    /**
     * Set the configured pagination.
     *
     * @param int pagination The number of results per page.
     */
    public function setPagination($pagination)
    {
        $this->pagination = $pagination;
        $this->results = null;
        $this->allResults = null;
    }

    /**
     * Get the calculated number of pages.
     *
     * @return int The number of pages; will return <em>0</em> if no results available.
     */
    public function getNumberOfPages()
    {
        if (0 == $this->pagination) {
            return 1;
        }

        return (int) ceil($this->getNumberOfResults() / $this->pagination);
    }

    /**
     * Check if a previous page is available.
     *
     * @return boolean <code>true</code> if a previous page is available, <code>false</code> if not.
     */
    public function hasPreviousPage()
    {
        return 1 < $this->page;
    }

    /**
     * Check if a next page is available.
     *
     * @return boolean <code>true</code> if a next page is available, <code>false</code> if not.
     */
    public function hasNextPage()
    {
        return $this->page < $this->getNumberOfPages();
    }

    /**
     * Get the previous page number.
     *
     * @return int The previous page number; (default: 1)
     */
    public function getPreviousPageNumber()
    {
        return $this->hasPreviousPage() ? ($this->page - 1) : 1;
    }

    /**
     * Get the next page number.
     *
     * @return int The next page number.
     */
    public function getNextPageNumber()
    {
        return $this->hasNextPage() ? ($this->page + 1) : $this->getNumberOfPages();
    }

    /**
     * Get the results for the current page.
     *
     * @return array List of results for the current page.
     */
    public function getResults()
    {
        if (null === $this->results) {
            $results = $this->getAllResults();

            if (!$this->resultSource->isFinal()) {
                foreach ($this->filters as $filter) {
                    if (!$filter->isActive())
                        continue;

                    $results = $filter->filter($results);
                }

                foreach ($this->sorters as $sorter) {
                    if ($sorter->isActive()) {
                        $results = $sorter->sort($results);
                        // can't have more than one sorter active here
                        break;
                    }
                }
            }

            if (0 != $this->pagination) {
                $end = $this->page * $this->pagination;
                $end = $end > count($results) ? count($results) : $end;
                $start = ((int) ($end / $this->pagination)) * $this->pagination;
                $start = ($start == $end && 0 < $end) ? $start - $this->pagination : $start;
            } else {
                // all
                $end = count($results);
                $start = 0;
            }

            // use this as array_slice might reorder the array if keys are not in order
            $this->results = array();
            for ($ii=0; $ii<($end-$start); ++$ii) {
                $this->results[] = $results[$start+$ii];
            }
        }

        return $this->results;
    }

    /**
     * Get all available results.
     *
     * <p>Please note that depending on the actual implementation this might return the same as
     * <code>getResults()</code>.</p>
     *
     * @return array List of all available results.
     */
    public function getAllResults()
    {
        if (null === $this->allResults) {
            $this->allResults = $this->buildResults();
        }

        return $this->allResults;
    }

    /**
     * Actual method to generate the viewable results.
     *
     * @return array List of result objects.
     */
    protected function buildResults()
    {
        if (null !== $this->resultSource) {
            // always force load here; either it is the first time, or the pagination changed, so we need to reload anyway
            return $this->resultSource->getResults(true);
        }

        return array();
    }

}
