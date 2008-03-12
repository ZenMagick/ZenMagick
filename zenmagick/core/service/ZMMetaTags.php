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
 * Page meta tag data generator.
 *
 * <p>Based on zen-carts <code>../modules/meta_tags.php</code>.</p>
 *
 * <p>In contrast to the zen-cart implementation, however, keywords are build
 * entirely by looking at category and product id in the request.</p>
 *
 * <p>All other pages will get served default values base on the store configuration.
 * Only exception is the homepage where the keywords will include the top categories.</p>
 *
 * @author mano
 * @package org.zenmagick.service
 * @version $Id: ZMMetaTags.php 803 2008-03-02 09:13:21Z dermanomann $
 */
class ZMMetaTags extends ZMObject {
    var $topCategories_ = null;
    var $crumbtrail_ = null;
    var $product_ = null;
    var $category_ = null;
    var $keywordDelimiter_;


    /**
     * Create new instance.
     *
     * @param string delimiter Optional keyword delimiter.
     */
    function __construct($delimiter=null) {
        parent::__construct();
        $this->keywordDelimiter_ = null != $delimiter ? $delimiter : zm_setting('metaTagKeywordDelimiter');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return parent::instance('MetaTags');
    }


    /**
     * Returns/echo'es the page title.
     *
     * @param boolean echo If <code>true</code>, the title will be echo'ed as well as returned.
     * @return string The page title.
     */
    function getTitle($echo=ZM_ECHO_DEFAULT) {
        $this->_initMetaTags();

        // default to page name
        $title = zm_title(false);
        // remove popup prefix
        $title = str_replace('Popup ', '', $title);

        // lookup localized page title
        $page = ZMRequest::getPageName();
        $pageTitleKey = zm_setting('metaTitlePrefix').$page;
        if (null != _zm_l10n_lookup($pageTitleKey, null)) {
            $title = zm_l10n_get($pageTitleKey);
        }

        // special handling for categories, manufacturers
        $controller = ZMRequest::getController();
        $view = $controller->getView();
        $name = $view->getName();
        if ('index' == $name) {
            $title = zm_setting('storeName');
        } else if (zm_starts_with($name, 'product_')) {
            $title = $this->product_;
        } else if ('category' == $name || 'category_list' == $name || 'manufacturer' == $name) {
            $title = $this->category_;
        } else if ('page' == $name) {
            $ezpage = $controller->getGlobal("zm_page");
            if (null != $ezpage) {
                $title = $ezpage->getTitle();
            }
        }

        if (zm_setting('isStoreNameInTitle') && 'index' != $page) {
            if (0 < strlen($title)) $title .= zm_setting('metaTitleDelimiter');
            $title .= zm_setting('storeName');
        }

        $title = zm_htmlencode($title, false);

        if ($echo) echo $title;
        return $title;
    }

    /**
     * Returns/echo'es the keywords meta tag value for the current request.
     *
     * @param boolean echo If <code>true</code>, the meta tag value will be echo'ed as well as returned.
     * @return string The meta tag value.
     */
    function getKeywords($echo=ZM_ECHO_DEFAULT) {
        $this->_initMetaTags();
        $value = '';
        if (null != $this->product_) {
            $value .= $this->product_;
            $value .= $this->keywordDelimiter_;
        }

        $value .= $this->topCategories_;

        $value = zm_htmlencode($value, false);

        if ($echo) echo $value;
        return $value;
    }

    /**
     * Returns/echo'es the description meta tag value value for the current request.
     *
     * @param boolean echo If <code>true</code>, the meta tag value will be echo'ed as well as returned.
     * @return string The meta tag value.
     */
    function getDescription($echo=ZM_ECHO_DEFAULT) {
        $this->_initMetaTags();
        $value = zm_setting('storeName');
        if (0 < strlen($this->_formatCrumbtrail())) {
            $value .= zm_setting('metaTagCrumbtrailDelimiter');
            $value .= $this->_formatCrumbtrail();
        }

        // special handling for home
        if ('index' == ZMRequest::getPageName()) {
            $value .= zm_setting('metaTagCrumbtrailDelimiter');
            $value .= $this->topCategories_;
        }

        $value = zm_htmlencode($value, false);

        if ($echo) echo $value;
        return $value;
    }


    /**
     * Set up all required internal structures.
     */
    function _initMetaTags() {
        $this->_loadTopCategories();
        $this->_loadCrumbtrail();
        $this->_loadProduct();
        $this->_loadCategory();
    }


    /**
     * Load top categories.
     */
    function _loadTopCategories() {
        if (null != $this->topCategories_)
            return;

        $topCategories = ZMCategories::instance()->getCategoryTree();

        $first = true;
        foreach ($topCategories as $category) {
            if (!$first) $this->topCategories_ .= $this->keywordDelimiter_;
            $first = false; 
            $this->topCategories_ .= $category->getName();
        }
    }


    /**
     * Load category crumbtrail.
     */
    function _loadCrumbtrail() {
        if (null != $this->crumbtrail_)
            return;

        $this->crumbtrail_ = $this->create("Crumbtrail");
        $this->crumbtrail_->addCategoryPath(ZMRequest::getCategoryPathArray());
        $this->crumbtrail_->addManufacturer(ZMRequest::getManufacturerId());
        $this->crumbtrail_->addProduct(ZMRequest::getProductId());
    }


    /*
     * Format the current crumbtrail.
     */
    function _formatCrumbtrail() {
        if (null == $this->crumbtrail_)
            return null;

        $crumbs = $this->crumbtrail_->getCrumbs();
        array_shift($crumbs);
        $first = true;
        $value = '';
        foreach ($crumbs as $crumb) {
            if (!$first) $value .= zm_setting('metaTagCrumbtrailDelimiter');
            $first = false;
            $value .= $crumb->getName();
        }

        return $value;
    }


    /**
     * Load product info.
     */
    function _loadProduct() {
        if (null == ZMRequest::getProductId() || null != $this->product_)
            return;

        $product = ZMProducts::instance()->getProductForId(ZMRequest::getProductId());
        $this->product_ = $product->getName() . ' [' . $product->getModel() . ']';
    }

    /**
     * Load category info.
     */
    function _loadCategory() {
        if (null != ZMRequest::getCategoryPath()) {
            $category = ZMCategories::instance()->getCategoryForId(ZMRequest::getCategoryId());
            $this->category_ = $category->getName();
        } else if (null != ZMRequest::getManufacturerId()) {
            $manufacturer = ZMManufacturers::instance()->getManufacturerForId(ZMRequest::getManufacturerId());
            $this->category_ = $manufacturer->getName();
        }
    }

}

?>
