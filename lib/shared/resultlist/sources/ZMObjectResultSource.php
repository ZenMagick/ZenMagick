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
class ZMObjectResultSource extends ZMObject implements ZMResultSource {
    private $resultList_;
    private $resultClass_;
    private $object_;
    private $method_;
    private $args_;
    private $results_;
    private $totalNumberOfResults_;
    private $isFinal_;

    /**
     * Create a new instance.
     *
     * @param string resultClass The class of the results; default is <code>null</code>.
     * @param mixed object The object to be used or a (string) service id; default is <code>null</code>.
     * @param string method The method to call on the object; default is <code>null</code>.
     * @param mixed args Optional method parameter (single value or array of args); default is an empty array.
     */
    public function __construct($resultClass=null, $object=null, $method=null, $args=array()) {
        parent::__construct();
        $this->resultClass_ = $resultClass;
        $this->object_ = $object;
        if (is_string($object)) {
            $this->object_ = Beans::getBean($object);
        }
        $this->method_ = $method;
        $this->args_ = $args;
        if (!is_array($this->args_)) {
            $this->args_ = array($this->args_);
        }
        $this->results_ = null;
        $this->totalNumberOfResults_ = null;
        $this->isFinal_ = null;
    }

    /**
     * {@inheritDoc}
     */
    public function setResultList($resultList) {
        $this->resultList_ = $resultList;
    }

    /**
     * {@inheritDoc}
     */
    public function getResults($reload=false) {
        if ($reload || null === $this->results_) {
            if ($this->object_ instanceof SqlAware) {
                if (null != ($queryDetails = $this->object_->getQueryDetails($this->method_, $this->args_))) {
                    // potentially final, so check sorter and filter
                    $this->isFinal_ = true;
                    $queryPager = Beans::getBean('ZenMagick\Base\Database\QueryPager');
                    $queryPager->setQueryDetails($queryDetails);
                    $sorters = $this->resultList_->getSorters(true);
                    if (0 < count($sorters)) {
                        if ($sorters[0] instanceof SqlAware) {
                            $sortDetails = $sorters[0]->getQueryDetails();
                            $queryPager->setOrderBy($sortDetails->getSql());
                        } else {
                            $this->isFinal_ = false;
                        }
                    }
                    if ($this->resultList_->hasFilters()) {
                        foreach ($this->resultList_->getFilters(true) as $filter) {
                            if ($filter instanceof SqlAware) {
                                $filterDetails = $filter->getQueryDetails();
                                $queryPager->addFilter($filterDetails->getSql());
                            } else {
                                $this->isFinal_ = false;
                            }
                        }
                    }

                    // only use pager if final
                    if ($this->isFinal_) {
                        $this->results_ = $queryPager->getResults($this->resultList_->getPageNumber(), $this->resultList_->getPagination());
                        $this->totalNumberOfResults_ = $queryPager->getTotalNumberOfResults();
                    }
                }
            }
            // check in case this method is not supported
            if (null === $this->results_) {
                $this->results_ = call_user_func_array(array($this->object_, $this->method_), $this->args_);
                $this->totalNumberOfResults_ = count($this->results_);
            }

        }
        return $this->results_;
    }

    /**
     * {@inheritDoc}
     */
    public function getResultClass() {
        return $this->resultClass_;
    }

    /**
     * Set the result class name.
     *
     * @param string name The class name.
     */
    public function setResultClass($name) {
        $this->resultClass_ = $name;
    }

    /**
     * Set the object.
     *
     * @param mixed object The object to be used.
     */
    public function setObject($object) {
        $this->object_ = $object;
    }

    /**
     * Get the method name.
     *
     * @return string The method name.
     */
    public function getMethod() {
        return $this->method_;
    }

    /**
     * Set the method name.
     *
     * @param string name The method name.
     */
    public function setMethod($name) {
        $this->method_ = $name;
    }

    /**
     * Get the method parameter.
     *
     * @return array The method parameter.
     */
    public function getArgs() {
        return $this->args_;
    }

    /**
     * Set the method parameter.
     *
     * @param mixed args The method parameter (single value or array of args).
     */
    public function setArgs($args) {
        $this->args_ = $args;
        if (!is_array($this->args_)) {
            $this->args_ = array($this->args_);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalNumberOfResults() {
        $this->getResults();
        return $this->totalNumberOfResults_;
    }

    /**
     * {@inheritDoc}
     */
    public function isFinal() {
        if (null === $this->isFinal_) {
            $this->getResults();
        }
        return $this->isFinal_;
    }

}
