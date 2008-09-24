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
 * A product search source.
 *
 * <p>This is a wrapper around the <code>ZMProductFinder</code>.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.resultlist.sources
 * @version $Id$
 */
class ZMSearchResultSource extends ZMObject implements ZMResultSource {
    private $resultList_;


    /**
     * Create a new instance.
     */
    public function __construct() {
        parent::__construct();
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
    public function setResultList($resultList) { 
        $this->resultList_ = $resultList;
    }

    /**
     * {@inheritDoc}
     */
    public function getResults() {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function getResultClass() {
        return 'ZMProduct';
    }

}

?>
