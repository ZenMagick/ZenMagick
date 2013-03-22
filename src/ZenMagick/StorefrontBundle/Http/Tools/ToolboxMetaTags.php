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
namespace ZenMagick\StorefrontBundle\Http\Tools;

use ZenMagick\Base\Runtime;
use ZenMagick\Base\Toolbox;
use ZenMagick\Http\Toolbox\ToolboxTool;

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
 * @author DerManoMann <mano@zenmagick.org>
 */
class ToolboxMetaTags extends ToolboxTool
{
    private $topCategories = null;
    private $crumbtrail = null;
    private $product = null;
    private $productName = null;
    private $category = null;
    private $keywordDelimiter;

    /**
     * Create new instance.
     *
     * @param string delimiter Optional keyword delimiter.
     */
    public function __construct($delimiter=null)
    {
        parent::__construct();
        $this->keywordDelimiter = null != $delimiter ? $delimiter : Runtime::getSettings()->get('metaTagKeywordDelimiter');
    }

    /**
     * Guess base page title
     *
     * @param string page The page name; default is <code>null</code> for the current page.
     * @return string A reasonable page title.
     */
    public function getBaseTitle($page=null)
    {
        $title = null == $page ? $this->getRequest()->getRequestId() : $page;
        // special case for static pages
        $title = 'static' != $title ? $title : $this->getRequest()->query->get('cat');

        // format
        $title = str_replace('_', ' ', $title);
        // capitalise words
        $title = ucwords($title);
        $title = _zm($title);

        return $title;
    }

    /**
     * Returns/echo'es the page title.
     *
     * @return string The page title.
     */
    public function getTitle()
    {
        $this->initMetaTags();

        // default to page name
        $title = $this->getBaseTitle();
        $html = $this->getToolbox()->html;
        // remove popup prefix
        $title = str_replace('Popup ', '', $title);

        // lookup localized page title
        $requestId = $this->getRequest()->getRequestId();
        $pageTitleKey = Runtime::getSettings()->get('metaTitlePrefix').$requestId;
        if ($pageTitleKey != _zm($pageTitleKey)) {
            $title = _zm($pageTitleKey);
        }

        // special handling for categories, manufacturers
        if ('index' == $requestId) {
            $title = Runtime::getSettings()->get('storeName');
        } elseif (0 === strpos($requestId, 'product_')) {
            if (null != $this->product) {
                $languageId = $this->getRequest()->getSession()->getLanguageId();
                if (null != ($details =  $this->product->getMetaTagDetails($languageId)) && !Toolbox::isEmpty($details->getTitle())) {
                    // got meta tags
                    $title = $details->getTitle();
                } else {
                    $title = $this->productName;
                }
            } else {
                $title = $this->productName;
            }
        } elseif ('manufacturer' == $requestId) {
            $title = $this->category;
        } elseif ('category' == $requestId || 'category_list' == $requestId) {
            if (null != ($category = $this->container->get('categoryService')->getCategoryForId($this->getRequest()->attributes->get('categoryId'), $this->getRequest()->getSession()->getLanguageId()))) {
                $languageId = $this->getRequest()->getSession()->getLanguageId();
                if (null != ($details = $category->getMetaTagDetails($languageId))) {
                    $title = $details->getTitle();
                } else {
                    $title = $this->category;
                }
            } else {
                $title = $this->category;
            }
        } elseif ('page' == $requestId) {
            if (null != ($view = $this->getView())) {
                if (null != ($ezpage = $view->getVariable('ezPage'))) {
                    $title = $ezpage->getTitle();
                }
            }
        }

        if (Runtime::getSettings()->get('isStoreNameInTitle') && 'index' != $requestId) {
            if (0 < strlen($title)) $title .= Runtime::getSettings()->get('metaTitleDelimiter');
            $title .= Runtime::getSettings()->get('storeName');
        }

        return $title;
    }

    /**
     * Returns/echo'es the keywords meta tag value for the current request.
     *
     * @return string The meta tag value.
     */
    public function getKeywords()
    {
        $this->initMetaTags();
        $value = '';
        $addTopCats = true;
        if (null != $this->product) {
            $languageId = $this->getRequest()->getSession()->getLanguageId();
            if (null != ($details =  $this->product->getMetaTagDetails($languageId)) && !Toolbox::isEmpty($details->getKeywords())) {
                // got meta tags
                $value .= $details->getKeywords();
                $value .= $this->keywordDelimiter;
                $addTopCats = false;
            }
            $value .= $this->productName;
        } elseif (0 != $this->getRequest()->attributes->get('categoryId')) {
            if (null != ($category = $this->container->get('categoryService')->getCategoryForId($this->getRequest()->attributes->get('categoryId'), $this->getRequest()->getSession()->getLanguageId()))) {
                $languageId = $this->getRequest()->getSession()->getLanguageId();
                if (null != ($details = $category->getMetaTagDetails($languageId))) {
                    $value = $details->getKeywords();
                    $addTopCats = false;
                }
            }
        }

        if ($addTopCats) {
            if (!empty($value)) {
                $value .= $this->keywordDelimiter;
            }
            $value .= $this->topCategories;
        }

        return trim($value);
    }

    /**
     * Returns/echo'es the description meta tag value value for the current request.
     *
     * @return string The meta tag value.
     */
    public function getDescription()
    {
        $this->initMetaTags();
        $value = Runtime::getSettings()->get('storeName');
        if (0 < strlen($this->formatCrumbtrail())) {
            $value .= Runtime::getSettings()->get('metaTagCrumbtrailDelimiter');
            $value .= $this->formatCrumbtrail();
        }

        // special handling for home
        if (null != $this->product) {
            $languageId = $this->getRequest()->getSession()->getLanguageId();
            if (null != ($details =  $this->product->getMetaTagDetails($languageId)) && !Toolbox::isEmpty($details->getKeywords())) {
                // got meta tags
                $value = $details->getDescription();
            } else {
                $value .= Runtime::getSettings()->get('metaTagCrumbtrailDelimiter');
                $value .= $this->topCategories;
            }
        } elseif (0 != $this->getRequest()->attributes->get('categoryId')) {
            if (null != ($category = $this->container->get('categoryService')->getCategoryForId($this->getRequest()->attributes->get('categoryId'), $this->getRequest()->getSession()->getLanguageId()))) {
                $languageId = $this->getRequest()->getSession()->getLanguageId();
                if (null != ($details = $category->getMetaTagDetails($languageId))) {
                    $value = $details->getDescription();
                }
            }
        }

        return trim($value);
    }


    /**
     * Set up all required internal structures.
     */
    protected function initMetaTags()
    {
        $this->loadTopCategories();
        $this->loadCrumbtrail();
        if (null == $this->crumbtrail) {
            $this->crumbtrail = $this->getToolbox()->crumbtrail;
        }
        $this->loadProduct();
        $this->loadCategory();
    }


    /**
     * Load top categories.
     */
    protected function loadTopCategories()
    {
        if (null != $this->topCategories)
            return;

        $topCategories = $this->container->get('categoryService')->getRootCategories($this->getRequest()->getSession()->getLanguageId());

        $first = true;
        foreach ($topCategories as $category) {
            if (!$first) $this->topCategories .= $this->keywordDelimiter;
            $first = false;
            $this->topCategories .= $category->getName();
        }
    }


    /**
     * Load category crumbtrail.
     */
    protected function loadCrumbtrail()
    {
    }


    /*
     * Format the current crumbtrail.
     */
    protected function formatCrumbtrail()
    {
        if (null == $this->crumbtrail)
            return null;

        $crumbs = $this->crumbtrail->getCrumbs();
        array_shift($crumbs);
        $first = true;
        $value = '';
        foreach ($crumbs as $crumb) {
            if (!$first) $value .= Runtime::getSettings()->get('metaTagCrumbtrailDelimiter');
            $first = false;
            $value .= $crumb->getName();
        }

        return $value;
    }


    /**
     * Load product info.
     */
    protected function loadProduct()
    {
        if (null == $this->getRequest()->query->get('productId') || null != $this->productName)
            return;

        if (null != ($this->product = $this->container->get('productService')->getProductForId($this->getRequest()->query->get('productId'), $this->getRequest()->getSession()->getLanguageId()))) {
            $this->productName = $this->product->getName();
            if (!Toolbox::isEmpty($this->product->getModel())) {
                $this->productName .= ' [' . $this->product->getModel() . ']';
            }
        }
    }

    /**
     * Load category info.
     */
    protected function loadCategory()
    {
        if ($this->getRequest()->query->has('cPath')) {
            if (null != ($category = $this->container->get('categoryService')->getCategoryForId($this->getRequest()->attributes->get('categoryId'), $this->getRequest()->getSession()->getLanguageId()))) {
                $this->category = $category->getName();
            }
        } elseif ($this->getRequest()->query->has('manufacturers_id')) {
            if (null != ($manufacturer = $this->container->get('manufacturerService')->getManufacturerForId($this->getRequest()->query->getInt('manufacturers_id'), $this->getRequest()->getSession()->getLanguageId()))) {
                $this->category = $manufacturer->getName();
            }
        }
    }

}
