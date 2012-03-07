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

use zenmagick\base\ZMObject;

/**
 * A result source wrapper for an array of results.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.resultlist.sources
 */
class ZMArrayResultSource extends ZMObject implements ZMResultSource {
    private $list_;
    private $resultClass_;


    /**
     * Create a new instance.
     *
     * @param string resultClass The class of the results.
     * @param array list The list of results.
     */
    public function __construct($resultClass, $list) {
        parent::__construct();
        $this->resultClass_ = $resultClass;
        $this->list_ = $list;
    }

    /**
     * Destruct instance.
     */
    public function __destruct() {
        parent::__destruct();
    }



    /**
     * {@inheritDoc}
     */
    public function setResultList($resultList) { /* not used */ }

    /**
     * {@inheritDoc}
     */
    public function getResults($reload=false) {
        return $this->list_;
    }

    /**
     * {@inheritDoc}
     */
    public function getResultClass() {
        return $this->resultClass_;
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalNumberOfResults() {
        return count($this->list_);
    }

    /**
     * {@inheritDoc}
     */
    public function isFinal() {
        return false;
    }

}
