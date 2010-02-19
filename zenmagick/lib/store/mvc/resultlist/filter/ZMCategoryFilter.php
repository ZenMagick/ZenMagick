<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * Filter products by a single category.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.resultlist.filter
 * @version $Id$
 */
class ZMCategoryFilter extends ZMResultListFilter implements ZMSQLAware {
    private $productIds_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('cfilter', zm_l10n_get('Category'), ZMRequest::instance()->getParameter('cfilter'));
        $this->productIds_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    // lazy load all included productIds
    protected function getProductIds() {
        if (null === $this->productIds_) {
            $this->productIds_ = ZMProducts::instance()->getProductIdsForCategoryId($this->filterValues_[0]);
        }
        return $this->productIds_;
    }


    /**
     * Return <code>true</code> if the given object is to be excluded.
     *
     * @param mixed obj The object to examine.
     * @return boolean <code>true</code> if the object is to be excluded, <code>false</code> if not.
     */
    public function exclude($obj) {
        $productIds = $this->getProductIds();
        return !array_key_exists($obj->getId(), $productIds);
    }

    /**
     * Returns a list of all available filter values.
     *
     * @return array An array of string values.
     */
    public function getOptions() {
        $options = array();
        foreach ($this->list_->getAllResults() as $result) {
            $category = $result->getDefaultCategory();
            if (null != $category) {
                $option = ZMLoader::make("FilterOption", $category->getName(), $category->getId(), $category->getId() == $this->filterValues_[0]);
                $options[$option->getId()] = $option;
            }
        }

        return $options;
    }

    /**
     * {@inheritDoc}
     */
    public function getQueryDetails($method=null, $args=array()) {
        return new ZMQueryDetails(Runtime::getDatabase(), 'p.master_categories_id = '.(int)$this->getValue());
    }

}
