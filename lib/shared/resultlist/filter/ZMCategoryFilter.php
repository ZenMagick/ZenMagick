<?php
/*
 * ZenMagick - Smart e-commerce
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
use ZenMagick\Base\Beans;
use ZenMagick\Base\Database\QueryDetails;
use ZenMagick\Base\Database\SqlAware;

/**
 * Filter products by a single category.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.mvc.resultlist.filter
 */
class ZMCategoryFilter extends ZMResultListFilter implements SqlAware
{
    private $productIds_;

    /**
     * Create new instance.
     */
    public function __construct()
    {
        parent::__construct('cfilter', _zm('Category'), Runtime::getContainer()->get('request')->query->get('cfilter'));
        $this->productIds_ = null;
    }

    // lazy load all included productIds
    protected function getProductIds()
    {
        if (null === $this->productIds_) {
            $languageId = $this->container->get('session')->getLanguageId();
            $this->productIds_ = $this->container->get('productService')->getProductIdsForCategoryId($this->filterValues_[0], $languageId);
        }

        return $this->productIds_;
    }

    /**
     * Return <code>true</code> if the given object is to be excluded.
     *
     * @param mixed obj The object to examine.
     * @return boolean <code>true</code> if the object is to be excluded, <code>false</code> if not.
     */
    public function exclude($obj)
    {
        $productIds = $this->getProductIds();

        return !array_key_exists($obj->getId(), $productIds);
    }

    /**
     * Returns a list of all available filter values.
     *
     * @return array An array of string values.
     */
    public function getOptions()
    {
        $options = array();
        foreach ($this->list_->getAllResults() as $result) {
            $category = $result->getDefaultCategory($this->container->get('session')->getLanguageId());
            if (null != $category) {
                $option = Beans::getBean('ZMFilterOption');
                $option->setId($category->getId());
                $option->setName($category->getName());
                $option->setActive($category->getId() == $this->filterValues_[0]);
                $options[$option->getId()] = $option;
            }
        }

        return $options;
    }

    /**
     * {@inheritDoc}
     */
    public function getQueryDetails($method=null, $args=array())
    {
        return new QueryDetails(ZMRuntime::getDatabase(), 'p.master_categories_id = '.(int) $this->getValue());
    }

}
