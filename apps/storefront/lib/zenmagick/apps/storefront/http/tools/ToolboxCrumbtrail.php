<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
namespace zenmagick\apps\storefront\http\tools;

use zenmagick\base\Runtime;

/**
 * Crumbtrail.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.store.sf.mvc.tools
 */
class ToolboxCrumbtrail extends \ZMToolboxTool {
    private $crumbs_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        Runtime::getEventDispatcher()->listen($this);
    }


    /**
     * {@inheritDoc}
     */
    public function onInitDone($event) {
        $this->reset();
    }

    /**
     * Reset.
     */
    public function reset() {
        $this->crumbs_ = array();
        // always add home
        $this->addCrumb("Home", $this->getRequest()->url('index'));
    }

    /**
     * Clear all crumbs.
     */
    public function clear() {
        $this->crumbs_ = array();
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
     */
    public function addCrumb($name, $url = null) {
        if (!is_array($this->crumbs_)) {
            $this->reset();
        }
        $crumb = Runtime::getContainer()->get('zenmagick\\apps\storefront\\http\tools\\Crumb');
        $crumb->setName($name);
        $crumb->setUrl($url);
        $this->crumbs_[] = $crumb;
    }

    /**
     * Add the given category path to the crumbtrail.
     *
     * @param array path The category path to add as a list of category ids.
     */
    public function addCategoryPath($path) {
        if (null == $path)
            return;

        // categories
        foreach ($path as $catId) {
            $category = $this->container->get('categoryService')->getCategoryForId($catId, $this->getRequest()->getSession()->getLanguageId());
            if (null == $category) {
                return;
            }
            $this->addCrumb($category->getName(),$this->getRequest()->url('category', $category->getPath()));
        }
    }

    /**
     * Add manufacturer to the crumbtrail.
     *
     * @param int manufacturerId The manufacturer's id.
     */
    public function addManufacturer($manufacturerId) {
        if (null == $manufacturerId)
            return;

        $manufacturer = $this->container->get('manufacturerService')->getManufacturerForId($manufacturerId, $this->getRequest()->getSession()->getLanguageId());
        if (null != $manufacturer) {
            $this->addCrumb($manufacturer->getName(), $this->getRequest()->url('category', 'manufacturers_id=' . $manufacturerId));
        }
    }

    /**
     * Add product to the crumbtrail.
     *
     * @param int productId The product id of the product to add.
     */
    public function addProduct($productId) {
        if (null == $productId)
            return;

        $product = $this->container->get('productService')->getProductForId($productId, $this->getRequest()->getSession()->getLanguageId());
        if (null != $product) {
            $this->addCrumb($product->getName(), $this->getToolbox()->net->product($productId, null));
        }
    }

}
