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
namespace ZenMagick\apps\storefront\http\tools;

use ZenMagick\base\Beans;
use ZenMagick\http\toolbox\ToolboxTool;

/**
 * Crumbtrail.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ToolboxCrumbtrail extends ToolboxTool {
    private $crumbs_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Reset.
     *
     * @return ToolboxCrumbtrail <code>$this</code> for chaining.
     */
    public function reset() {
        $this->crumbs_ = array();
        // always add home
        $this->addCrumb("Home", $this->getToolbox()->net->url('index'));
        return $this;
    }

    /**
     * Clear all crumbs.
     *
     * @return ToolboxCrumbtrail <code>$this</code> for chaining.
     */
    public function clear() {
        $this->crumbs_ = array();
        return $this;
    }

    /**
     * Get the last crumbs name.
     *
     * @return string The name of the last crumbtrail element.
     */
    public function getLastCrumb() {
        return end($this->crumbs_);
    }

    /**
     * Get the crumb for the given index.
     *
     * @param int index The index of the crumb to access.
     * @return Crumb The corresponding crumbtrail element.
     */
    public function getCrumb($index) {
        if (!is_array($this->crumbs_)) {
            $this->reset();
        }

        return $this->crumbs_[$index];
    }

    /**
     * Get a list of all crumbs.
     *
     * @return array List of <code>Crumb</code> instances.
     */
    public function getCrumbs() {
        if (!is_array($this->crumbs_)) {
            $this->reset();
        }
        return $this->crumbs_;
    }

    /**
     * Add a single crumb.
     *
     * @param string name The crumbtrail element name.
     * @param string url Optional crumbtrail element URL.
     * @return ToolboxCrumbtrail <code>$this</code> for chaining.
     */
    public function addCrumb($name, $url = null) {
        return $this;
        if (!is_array($this->crumbs_)) {
            $this->reset();
        }
        $crumb = Beans::getBean('ZenMagick\apps\storefront\http\tools\Crumb');
        //die(var_dump($crumb));
        $crumb->setName($name);
        $crumb->setUrl($url);
        $this->crumbs_[] = $crumb;
        return $this;
    }

    /**
     * Add the given category path to the crumbtrail.
     *
     * @param array path The category path to add as a list of category ids.
     * @return ToolboxCrumbtrail <code>$this</code> for chaining.
     */
    public function addCategoryPath($path = null) {
        $path = $path ?: $this->getRequest()->attributes->get('categoryIds');
        if (!$path) {
            return $this;
        }
        // categories
        foreach ($path as $catId) {
            $category = $this->container->get('categoryService')->getCategoryForId($catId, $this->getRequest()->getSession()->getLanguageId());
            if (null == $category) {
                return $this;
            }
            $this->addCrumb($category->getName(),$this->getToolbox()->net->url('category', 'cPath='.implode('_', $category->getPath())));
        }
        return $this;
    }

    /**
     * Add manufacturer to the crumbtrail.
     *
     * @param int manufacturerId The manufacturer's id.
     * @return ToolboxCrumbtrail <code>$this</code> for chaining.
     */
    public function addManufacturer($manufacturerId = null) {
        $manufacturerId = $manufacturerId ?: $this->getRequest()->query->getInt('manufacturers_id');
        if (null == $manufacturerId) {
            return $this;
        }
        $manufacturer = $this->container->get('manufacturerService')->getManufacturerForId($manufacturerId, $this->getRequest()->getSession()->getLanguageId());
        if (null != $manufacturer) {
            $this->addCrumb($manufacturer->getName(), $this->getToolbox()->net->url('category', 'manufacturers_id=' . $manufacturerId));
        }
        return $this;
    }

    /**
     * Add product to the crumbtrail.
     *
     * @param int productId The product id of the product to add.
     * @return ToolboxCrumbtrail <code>$this</code> for chaining.
     */
    public function addProduct($productId = null) {
        $productId = $productId ?: $this->getRequest()->query->get('productId');
        if (null == $productId) {
            return $this;
        }
        $product = $this->container->get('productService')->getProductForId($productId, $this->getRequest()->getSession()->getLanguageId());
        if (null != $product) {
            $this->addCrumb($product->getName(), $this->getToolbox()->net->product($productId, null));
        }
        return $this;
    }

}
