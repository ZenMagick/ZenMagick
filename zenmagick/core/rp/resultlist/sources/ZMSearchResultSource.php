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
 * @package org.zenmagick.rp.resultlist.sources
 * @version $Id$
 */
class ZMSearchResultSource extends ZMObject implements ZMResultSource {
    private $criteria_;
    private $resultList_;


    /**
     * Create a new instance.
     *
     * @param ZMSearchCriteria criteria The search criteria.
     */
    public function __construct($criteria) {
        parent::__construct();
        $this->criteria_ = $criteria;
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
        $finder = ZMLoader::make('ProductFinder');
        $finder->setCriteria($this->criteria_);
        if (null !== $this->resultList_) {
            // try to set sorter
            foreach ($this->resultList_->getSorters() as $sorter) {
                if ($sorter->isActive()) {
                    $finder->setSortId($sorter->getSortId());
                    $finder->setDescending($sorter->isDescending());
                    break;
                }
            }
        }

        $productIds = $finder->execute();
        return ZMProducts::instance()->getProductsForIds($productIds);
    }

    /**
     * {@inheritDoc}
     */
    public function getResultClass() {
        return 'ZMProduct';
    }

}

?>
