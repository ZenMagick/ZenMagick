<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * @package org.zenmagick.store.mvc.resultlist.sources
 * @version $Id: ZMSearchResultSource.php 2343 2009-06-28 07:28:27Z dermanomann $
 */
class ZMSearchResultSource extends ZMObject implements ZMResultSource {
    private $criteria_;
    private $resultList_;
    private $results_;
    private $totalNumberOfResults_;


    /**
     * Create a new instance.
     *
     * @param ZMSearchCriteria criteria The search criteria.
     */
    public function __construct($criteria) {
        parent::__construct();
        $this->criteria_ = $criteria;
        $this->results_ = null;
        $this->totalNumberOfResults_ = null;
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
        if (null === $this->results_) {
            $finder = ZMLoader::make('ProductFinder');
            $finder->setCriteria($this->criteria_);
            if (null !== $this->resultList_) {
                // try to set the first active sorter
                foreach ($this->resultList_->getSorters() as $sorter) {
                    if ($sorter->isActive()) {
                        $finder->setSortId($sorter->getSortId());
                        $finder->setDescending($sorter->isDescending());
                        break;
                    }
                }
            }
            $queryDetails = $finder->execute();
            $queryPager = ZMLoader::make('QueryPager', $queryDetails);
            $productIds = array();
            foreach ($queryPager->getResults($this->resultList_->getPageNumber(), $this->resultList_->getPagination()) as $result) {
                $productIds[] = $result['productId'];
            }
            $this->results_ = ZMProducts::instance()->getProductsForIds($productIds, true, $this->criteria_->getLanguageId());
            $this->totalNumberOfResults_ = $queryPager->getTotalNumberOfResults();
        }
        return $this->results_;
    }

    /**
     * {@inheritDoc}
     */
    public function getResultClass() {
        return 'ZMProduct';
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
        return true;
    }

}

?>
