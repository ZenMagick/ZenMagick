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
 * Custom <code>ZMResultList</code> implementation using SQL pagination.
 *
 * <p>Also, all sorting is done inside this class.</p>
 *
 * <p><strong>Filtering is currently not supported.</strong></p>
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.zm_split_page_result_list
 * @version $Id$
 */
class ResultList extends ZMResultList {
    private $sql_;
    private $splitter_;


    /**
     * {@inheritDoc}
     */
    function __construct($results=null) {
        parent::__construct($results);
        $this->sql_ = null;
        $this->splitter_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * {@inheritDoc}
     */
    function setResultSource($resultSource) {
        // we are only into products right now
        if ('Product' != $resultSource->getResultClass() || !($resultSource instanceof ZMObjectResultSource)) {
            parent::setResultSource($resultSource);
            return;
        }
        if ('index' != ZMRequest::getPageName() && 'category' != ZMRequest::getPageName()) {
            parent::setResultSource($resultSource);
            return;
        }
        if ('getProductsForSQL' == $resultSource->getMethod()) {
            parent::setResultSource($resultSource);
            return;
        }

        //**** START  prepare zen-cart environment
        global $db;
        $current_category_id = ZMRequest::getCategoryId();

        //***** include zen-cart code
        require_once(DIR_WS_INCLUDES . 'index_filters/default_filter.php');

        //***** translate sort order
        $sortId = ZMRequest::getSortId();
        $desc = false;
        if (ZMTools::endsWith($sortId, '_d')) {
            $desc = true;
            $sortId = str_replace('_d', '', $sortId);
        } else {
            $sortId = str_replace('_a', '', $sortId);
        }

        $listing_sql .= ' order by ';
        switch ($sortId) {
        case 'model':
            $listing_sql .= "p.products_model " . ($desc ? 'desc' : '') . ", pd.products_name";
            break;
        case 'name':
            $listing_sql .= "pd.products_name " . ($desc ? 'desc' : '');
            break;
        case 'manufacturer':
            $listing_sql .= "m.manufacturers_name " . ($desc ? 'desc' : '') . ", pd.products_name";
            break;
        case 'weight':
            $listing_sql .= "p.products_weight " . ($desc ? 'desc' : '') . ", pd.products_name";
            break;
        case 'price':
            $listing_sql .= "p.products_price_sorter " . ($desc ? 'desc' : '') . ", pd.products_name";
            break;
        default:
            $listing_sql .= "p.products_sort_order, pd.products_name";
            break;
        }

        $this->sql_ = $listing_sql;
    }

    /**
     * Get the splitter.
     *
     * <p>This is the moment the actual query is done.
     */
    private function ensureSplitter() {
        if (null != $this->sql_ && null == $this->splitter_) {
            $this->splitter_ = new splitPageResults($this->sql_, $this->getPagination(), 'p.products_id', 'page');
            $this->results_ = ZMProducts::instance()->getProductsForSQL($this->splitter_->sql_query);
        }

        return $this->splitter_;
    }

    /**
     * {@inheritDoc}
     */
    function _applyFilters($list) { 
        if (null != $this->sql_) { 
            return $list;
        } 
        
        return parent::_applyFilters($list);
    }

    /**
     * {@inheritDoc}
     */
    function _applySort($list) { 
        if (null != $this->sql_) { 
            return $list;
        } 
        
        return parent::_applySort($list);
    }

    /**
     * {@inheritDoc}
     */
    function hasSorters() { 
        $this->ensureSplitter(); 
        return parent::hasSorters();
    }

    /**
     * {@inheritDoc}
     */
    function hasResults() {
        $this->ensureSplitter(); 
        return parent::hasResults();
    }

    /**
     * {@inheritDoc}
     */
    function refresh() { 
        $this->ensureSplitter(); 
        if (null == $this->sql_) { 
            parent::refresh(); 
        }
    }

    /**
     * {@inheritDoc}
     */
    function getCurrentPageNumber() { 
        if (null != $this->sql_) { 
            $this->ensureSplitter(); 
            return $this->splitter_->current_page_number;
        }

        return parent::getCurrentPageNumber();
    }

    /**
     * {@inheritDoc}
     */
    function getNumberOfPages() {
        if (null != $this->sql_) { 
            $this->ensureSplitter(); 
            return $this->splitter_->number_of_pages;
        }

        return parent::getNumberOfPages();
    }

    /**
     * {@inheritDoc}
     */
    function getResults() { 
        $this->ensureSplitter();
        return $this->results_;
    }

}

?>
