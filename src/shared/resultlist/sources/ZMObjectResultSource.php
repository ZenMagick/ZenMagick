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

use ZenMagick\Base\Beans;
use ZenMagick\Base\ZMObject;
use ZenMagick\Base\Database\SqlAware;

/**
 * A result source based on calling a method on an object.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.resultlist.sources
 */
class ZMObjectResultSource extends ZMObject implements ZMResultSource
{
    private $resultList;
    private $resultClass;
    private $object;
    private $method;
    private $args;
    private $results;
    private $totalNumberOfResults;
    private $isFinal;

    /**
     * Create a new instance.
     *
     * @param string resultClass The class of the results; default is <code>null</code>.
     * @param mixed object The object to be used or a (string) service id; default is <code>null</code>.
     * @param string method The method to call on the object; default is <code>null</code>.
     * @param mixed args Optional method parameter (single value or array of args); default is an empty array.
     */
    public function __construct($resultClass=null, $object=null, $method=null, $args=array())
    {
        parent::__construct();
        $this->resultClass = $resultClass;
        $this->object = $object;
        if (is_string($object)) {
            $this->object = Beans::getBean($object);
        }
        $this->method = $method;
        $this->args = $args;
        if (!is_array($this->args)) {
            $this->args = array($this->args);
        }
        $this->results = null;
        $this->totalNumberOfResults = null;
        $this->isFinal = null;
    }

    /**
     * {@inheritDoc}
     */
    public function setResultList($resultList)
    {
        $this->resultList = $resultList;
    }

    /**
     * {@inheritDoc}
     */
    public function getResults($reload=false)
    {
        if ($reload || null === $this->results) {
            if ($this->object instanceof SqlAware) {
                if (null != ($queryDetails = $this->object->getQueryDetails($this->method, $this->args))) {
                    // potentially final, so check sorter and filter
                    $this->isFinal = true;
                    $queryPager = Beans::getBean('ZenMagick\Base\Database\QueryPager');
                    $queryPager->setQueryDetails($queryDetails);
                    $sorters = $this->resultList->getSorters(true);
                    if (0 < count($sorters)) {
                        if ($sorters[0] instanceof SqlAware) {
                            $sortDetails = $sorters[0]->getQueryDetails();
                            $queryPager->setOrderBy($sortDetails->getSql());
                        } else {
                            $this->isFinal = false;
                        }
                    }
                    if ($this->resultList->hasFilters()) {
                        foreach ($this->resultList->getFilters(true) as $filter) {
                            if ($filter instanceof SqlAware) {
                                $filterDetails = $filter->getQueryDetails();
                                $queryPager->addFilter($filterDetails->getSql());
                            } else {
                                $this->isFinal = false;
                            }
                        }
                    }

                    // only use pager if final
                    if ($this->isFinal) {
                        $this->results = $queryPager->getResults($this->resultList->getPageNumber(), $this->resultList->getPagination());
                        $this->totalNumberOfResults = $queryPager->getTotalNumberOfResults();
                    }
                }
            }
            // check in case this method is not supported
            if (null === $this->results) {
                $this->results = call_user_func_array(array($this->object, $this->method), $this->args);
                $this->totalNumberOfResults = count($this->results);
            }

        }

        return $this->results;
    }

    /**
     * {@inheritDoc}
     */
    public function getResultClass()
    {
        return $this->resultClass;
    }

    /**
     * Set the result class name.
     *
     * @param string name The class name.
     */
    public function setResultClass($name)
    {
        $this->resultClass = $name;
    }

    /**
     * Set the object.
     *
     * @param mixed object The object to be used.
     */
    public function setObject($object)
    {
        $this->object = $object;
    }

    /**
     * Get the method name.
     *
     * @return string The method name.
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set the method name.
     *
     * @param string name The method name.
     */
    public function setMethod($name)
    {
        $this->method = $name;
    }

    /**
     * Get the method parameter.
     *
     * @return array The method parameter.
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * Set the method parameter.
     *
     * @param mixed args The method parameter (single value or array of args).
     */
    public function setArgs($args)
    {
        $this->args = $args;
        if (!is_array($this->args)) {
            $this->args = array($this->args);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalNumberOfResults()
    {
        $this->getResults();

        return $this->totalNumberOfResults;
    }

    /**
     * {@inheritDoc}
     */
    public function isFinal()
    {
        if (null === $this->isFinal) {
            $this->getResults();
        }

        return $this->isFinal;
    }

}
