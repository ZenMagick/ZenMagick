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
 * @package org.zenmagick.misc
 * @version $Id$
 */
class ZMMetaTags extends ZMObject {
    var $db_;
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
    function ZMMetaTags($delimiter=null) {
        parent::__construct();

        $this->db_ = ZMRuntime::getDB();
        $this->keywordDelimiter_ = null != $delimiter ? $delimiter : zm_setting('metaTagKeywordDelimiter');
    }

    /**
     * Create new instance.
     *
     * @param string delimiter Optional keyword delimiter.
     */
    function __construct($delimiter=null) {
        $this->ZMMetaTags($delimiter);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Returns/echo'es the page title.
     *
     * @param boolean echo If <code>true</code>, the title will be echo'ed as well as returned.
     * @return string The page title.
     */
    function getTitle($echo=ZM_ECHO_DEFAULT) {
    global $zm_request;

        $this->_initMetaTags();

        // default to page name
        $title = zm_title(false);
        // remove popup prefix
        $title = str_replace('Popup ', '', $title);

        // lookup localized page title
        $page = $zm_request->getPageName();
        $pageTitleKey = zm_setting('metaTitlePrefix').$page;
        if (null != _zm_l10n_lookup($pageTitleKey, null)) {
            $title = zm_l10n_get($pageTitleKey);
        }

        // special handling for categories, manufacturers
        $controller = $zm_request->getController();
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
    global $zm_request;

        $this->_initMetaTags();
        $value = zm_setting('storeName');
        if (0 < strlen($this->_formatCrumbtrail())) {
            $value .= zm_setting('metaTagCrumbtrailDelimiter');
            $value .= $this->_formatCrumbtrail();
        }

        // special handling for home
        if ('index' == $zm_request->getPageName()) {
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
    global $zm_categories;

        if (null != $this->topCategories_)
            return;

        $topCategories = $zm_categories->getCategoryTree();

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
    global $zm_request;

        if (null != $this->crumbtrail_)
            return;

        $this->crumbtrail_ = $this->create("Crumbtrail");
        $this->crumbtrail_->addCategoryPath($zm_request->getCategoryPathArray());
        $this->crumbtrail_->addManufacturer($zm_request->getManufacturerId());
        $this->crumbtrail_->addProduct($zm_request->getProductId());
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
    global $zm_request;

        if (null == $zm_request->getProductId() || null != $this->product_)
            return;

        $product = ZMProducts::instance()->getProductForId($zm_request->getProductId());
        $this->product_ = $product->getName() . ' [' . $product->getModel() . ']';
    }

    /**
     * Load category info.
     */
    function _loadCategory() {
    global $zm_request, $zm_categories, $zm_manufacturers;

        if (null == $zm_request->getCategoryPath() && null == $zm_request->getManufacturerId()) {
            return;
        }

        if (null != $zm_request->getCategoryPath()) {
            $category = $zm_categories->getCategoryForId($zm_request->getCategoryId());
            $this->category_ = $category->getName();
        } else if (null != $zm_request->getManufacturerId()) {
            $manufacturer = $zm_manufacturers->getManufacturerForId($zm_request->getManufacturerId());
            $this->category_ = $manufacturer->getName();
        }
    }

}

?>
