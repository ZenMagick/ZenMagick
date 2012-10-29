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
    private $topCategories_ = null;
    private $crumbtrail_ = null;
    private $product_ = null;
    private $productName_ = null;
    private $category_ = null;
    private $keywordDelimiter_;

    /**
     * Create new instance.
     *
     * @param string delimiter Optional keyword delimiter.
     */
    public function __construct($delimiter=null)
    {
        parent::__construct();
        $this->keywordDelimiter_ = null != $delimiter ? $delimiter : Runtime::getSettings()->get('metaTagKeywordDelimiter');
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
        $title = $this->getToolbox()->utils->getTitle();
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
            if (null != $this->product_) {
                $languageId = $this->getRequest()->getSession()->getLanguageId();
                if (null != ($details =  $this->product_->getMetaTagDetails($languageId)) && !Toolbox::isEmpty($details->getTitle())) {
                    // got meta tags
                    $title = $details->getTitle();
                } else {
                    $title = $this->productName_;
                }
            } else {
                $title = $this->productName_;
            }
        } elseif ('manufacturer' == $requestId) {
            $title = $this->category_;
        } elseif ('category' == $requestId || 'category_list' == $requestId) {
            if (null != ($category = $this->container->get('categoryService')->getCategoryForId($this->getRequest()->attributes->get('categoryId'), $this->getRequest()->getSession()->getLanguageId()))) {
                $languageId = $this->getRequest()->getSession()->getLanguageId();
                if (null != ($details = $category->getMetaTagDetails($languageId))) {
                    $title = $html->encode($details->getTitle());
                } else {
                    $title = $this->category_;
                }
            } else {
                $title = $this->category_;
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

        $title = $html->encode($title);

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
        if (null != $this->product_) {
            $languageId = $this->getRequest()->getSession()->getLanguageId();
            if (null != ($details =  $this->product_->getMetaTagDetails($languageId)) && !Toolbox::isEmpty($details->getKeywords())) {
                // got meta tags
                $value .= $details->getKeywords();
                $value .= $this->keywordDelimiter_;
                $addTopCats = false;
            }
            $value .= $this->productName_;
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
                $value .= $this->keywordDelimiter_;
            }
            $value .= $this->topCategories_;
        }

        $value = $this->getToolbox()->html->encode(trim($value));

        return $value;
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
        if (null != $this->product_) {
            $languageId = $this->getRequest()->getSession()->getLanguageId();
            if (null != ($details =  $this->product_->getMetaTagDetails($languageId)) && !Toolbox::isEmpty($details->getKeywords())) {
                // got meta tags
                $value = $details->getDescription();
            } else {
                $value .= Runtime::getSettings()->get('metaTagCrumbtrailDelimiter');
                $value .= $this->topCategories_;
            }
        } elseif (0 != $this->getRequest()->attributes->get('categoryId')) {
            if (null != ($category = $this->container->get('categoryService')->getCategoryForId($this->getRequest()->attributes->get('categoryId'), $this->getRequest()->getSession()->getLanguageId()))) {
                $languageId = $this->getRequest()->getSession()->getLanguageId();
                if (null != ($details = $category->getMetaTagDetails($languageId))) {
                    $value = $details->getDescription();
                }
            }
        }

        $value = $this->getToolbox()->html->encode(trim($value));

        return $value;
    }


    /**
     * Set up all required internal structures.
     */
    protected function initMetaTags()
    {
        $this->loadTopCategories();
        $this->loadCrumbtrail();
        if (null == $this->crumbtrail_) {
            $this->crumbtrail_ = $this->getToolbox()->crumbtrail;
        }
        $this->loadProduct();
        $this->loadCategory();
    }


    /**
     * Load top categories.
     */
    protected function loadTopCategories()
    {
        if (null != $this->topCategories_)
            return;

        $topCategories = $this->container->get('categoryService')->getRootCategories($this->getRequest()->getSession()->getLanguageId());

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
    protected function loadCrumbtrail()
    {
    }


    /*
     * Format the current crumbtrail.
     */
    protected function formatCrumbtrail()
    {
        if (null == $this->crumbtrail_)
            return null;

        $crumbs = $this->crumbtrail_->getCrumbs();
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
        if (null == $this->getRequest()->query->get('productId') || null != $this->productName_)
            return;

        if (null != ($this->product_ = $this->container->get('productService')->getProductForId($this->getRequest()->query->get('productId'), $this->getRequest()->getSession()->getLanguageId()))) {
            $this->productName_ = $this->product_->getName();
            if (!Toolbox::isEmpty($this->product_->getModel())) {
                $this->productName_ .= ' [' . $this->product_->getModel() . ']';
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
                $this->category_ = $category->getName();
            }
        } elseif ($this->getRequest()->query->has('manufacturers_id')) {
            if (null != ($manufacturer = $this->container->get('manufacturerService')->getManufacturerForId($this->getRequest()->query->getInt('manufacturers_id'), $this->getRequest()->getSession()->getLanguageId()))) {
                $this->category_ = $manufacturer->getName();
            }
        }
    }

}
