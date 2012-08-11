<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

namespace zenmagick\apps\store\bundles\ZenCartBundle\controller;

use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;

/**
 * ZenCart storefront controller
 *
 * @author Johnny Robeson
 * @package org.zenmagick.plugins.zenCart
 */
class ZencartStorefrontController extends \ZMController {
    /**
     * Override getFormData() for ZenCart pages
     */
    public function getFormData($request, $formDef=null, $formId=null) {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function preProcess($request) {
        /**
         * This code is taken directly from application_top.php.
         * @copyright Copyright 2003-2010 Zen Cart Development Team
         */
        $paramsToCheck = array('main_page', 'cPath', 'products_id', 'language', 'currency', 'action', 'manufacturers_id',
            'pID', 'pid', 'reviews_id', 'filter_id', 'zenid', 'sort', 'number_of_uploads', 'notify', 'page_holder', 'chapter',
            'alpha_filter_id', 'typefilter', 'disp_order', 'id', 'key', 'music_genre_id', 'record_company_id', 'set_session_login',
            'faq_item', 'edit', 'delete', 'search_in_description', 'dfrom', 'pfrom', 'dto', 'pto', 'inc_subcat', 'payment_error',
            'order', 'gv_no', 'pos', 'addr', 'error', 'count', 'error_message', 'info_message', 'cID', 'page', 'credit_class_error_code');
        foreach($paramsToCheck as $key) {
            if ($request->query->has($key)) {
                $value = $request->query->get($key);
                if (is_array($value)) continue;
                if (substr($value, 0, 4) == 'http' || strstr($value, '//') || strlen($value) > 43) {
                    header('HTTP/1.1 406 Not Acceptable');
                    exit(0);
                }
            }
        }
        if ($request->query->has('productId')) {
            $request->query->set('products_id', $request->query->get('productId'));
            $request->query->remove('productId');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $settingsService = $this->container->get('settingsService');
        $session = $request->getSession();
        $autoLoader = $this->container->get('zencartAutoLoader');
        /**
         *  Get globals used throughout.
         */

        $controllerFile = $this->initController($request);
        extract($autoLoader->getGlobalValues());
        /**
         *  Get "local" globals.
         *
         *  Almost entirely related to shipping, payment, order total and checkout.
         */
        global $shipping, $shipping_num_boxes, $shipping_weight, $shipping_quoted, $shipping_modules;
        global $order_totals, $order_total_modules, $payment_modules;
        global $order, $total_count, $total_weight;
        global $credit_covers, $country_info, $discount_coupon, $insert_id;
        global $isECtransaction, $isDPtransaction;
        $zcShipping = $session->getValue('shipping', null);
        if (null != $zcShipping) {
            if (is_array($zcShipping)) $zcShipping = $zcShipping['id'];
            list($module, $method) = explode('_', $zcShipping);
            global $$module;
        }
        if (null != ($sPayment = $session->getValue('payment', null))) {
            global $$sPayment;
        }

        if (null == $session->getValue('securityToken')) {
            $session->setValue('securityToken', $session->getToken());
        }

        if (null == $session->getValue('navigation')) {
            $session->setValue('navigation', new \navigationHistory);
        }

        if (!$request->isXmlHttpRequest()) {
            $session->getValue('navigation')->add_current_page();
        }



        /**
         *  Execute ZenCart controller
         */
        $cwd = getcwd();
        $autoLoader->setErrorLevel();
        $request->overrideGlobals();
        chdir($settingsService->get('zencart.root_dir'));
        extract($this->getZcViewData($request));
        $autoLoadConfig = array();
        $files = $autoLoader->resolveFiles('includes/auto_loaders/config.*.php');
        unset($files['config.core.php']);
        unset($files['config.canonical.php']);
        foreach ($files as $file) {
            include $file;
        }
        require Runtime::getInstallationPath().'/apps/store/bundles/ZenCartBundle/bridge/includes/autoload_func.php';
        require($controllerFile);

        // is this really required? we got here because the bundle checked this already, it seems
        if ($this->container->get('themeService')->getActiveTheme()->getMeta('zencart')) {
            require($template->get_template_dir('html_header.php',DIR_WS_TEMPLATE, $request->getRequestId(),'common'). '/html_header.php');
            require($template->get_template_dir('main_template_vars.php',DIR_WS_TEMPLATE, $request->getRequestId(),'common'). '/main_template_vars.php');
            require($template->get_template_dir('tpl_main_page.php',DIR_WS_TEMPLATE, $request->getRequestId(),'common'). '/tpl_main_page.php');
            echo '</html>';
        }
        chdir($cwd);
        $autoLoader->restoreErrorLevel();
        foreach ($_SESSION as $k => $v) {
            $session->setValue($k, $v);
        }
        return null;
    }

    /**
     * Find the file (usually in includes/modules/pages) to handle the request
     *
     * @return string file
     */
    public function initController($request) {
        $controllerFile = null;
        $autoLoader = $this->container->get('zencartAutoLoader');
        if ('ipn_handler' == $request->getRequestId()) { // @todo handle other common zencart entry points like googlebase
            return $autoLoader->resolveFile('ipn_main_handler.php');
        }

        if (null != ($productId = $request->query->get('products_id'))) {
            /**
             * If no info page was provided for this product id, set one ourselves.
             */
            if ($this->container->get('productService')->getProductForId($productId)) {
                $infoPage = zen_get_info_page($productId);
                if ($infoPage != $request->getRequestId()) {
                    $request->setRequestId($infoPage);
                }
            }
        }
        /**
         * Does the page controller exist?
         */
        $controllerFile = $autoLoader->resolveFile('includes/modules/pages/%current_page_base%/header_php.php');
        if (!file_exists($controllerFile)) {
            if (MISSING_PAGE_CHECK == 'On' || MISSING_PAGE_CHECK == 'true') {
                $request->setRequestId('index');
            } elseif (MISSING_PAGE_CHECK == 'Page Not Found') {
                header('HTTP/1.1 404 Not Found');
                $request->setRequestId('page_not_found');
            }
            $controllerFile = $autoLoader->resolveFile('includes/modules/pages/%current_page_base%/header_php.php');
        }
        $autoLoader->overrideRequestGlobals();
        $autoLoader->setGlobalValue('language_page_directory', DIR_WS_INCLUDES.'languages/'.$request->getSelectedLanguage()->getDirectory().'/');

        return $controllerFile;
    }

    /**
     * Get ZenCart view data.
     */
    public function getZcViewData($request) {
        // category path - no support for get_terms_to_filter table. does anybody use that?
        $manufacturerId = $request->query->getInt('manufacturers_id');
        $productId = $request->query->get('products_id');
        $show_welcome = false;
        if ($request->query->has('cPath')) {
            if (!empty($productId) && empty($manufacturerId)) {
                $request->query->set('cPath', zen_get_product_path($productId));
            } else if (SHOW_CATEGORIES_ALWAYS == '1' && empty($manufacturerId)) {
                $show_welcome = true;
                $request->query->set('cPath', (defined('CATEGORIES_START_MAIN') ? CATEGORIES_START_MAIN : ''));
            }
        }
        // end category path

        define('PAGE_PARSE_START_TIME', microtime());

        $lng = new \language();

        $languageId = $request->getSession()->getLanguageId();
        // breadcrumb
        $robotsNoIndex = false;
        $validCategories = array();
        $cPathArray = (array)$request->attributes->get('categoryIds');
        foreach ($cPathArray as $categoryId) {
            $category = $this->container->get('categoryService')->getCategoryForId($categoryId, $languageId);
            if (null != $category) {
                $validCategories[] = $category;
            } else if (SHOW_CATEGORIES_ALWAYS == 0) {
                $robotsNoIndex = true;
                break;
            }
        }
        // ZenMagick does most of the following , we should be able to reuse it.
        $manufacturer = null;
        $manufacturerId = $request->query->getInt('manufacturers_id');
        if (null != $manufacturerId) {
            $manufacturer = $this->container->get('manufacturerService')->getManufacturerForId($manufacturerId, $languageId);
        }
        $product = null;
        if (null != $productId) {
            $product = $this->container->get('productService')->getProductForId($productId);
        }
        $breadcrumb = $this->initCrumbs($validCategories, $manufacturer, $product);
        // end breakdcrumb

        $canonicalLink = $this->getCanonicalUrl();
        $this_is_home_page = $this->isHomePage();
        $zv_onload = $this->getOnLoadJS();
        return compact('breadcrumb', 'canonicalLink', 'lng', 'robotsNoIndex', 'this_is_home_page', 'zv_onload');
    }

    /**
     * Figures out if the current page is a product listing ($this_is_home_page)
     *
     * @return string
     */
    public function isHomePage() {
        $request = $this->container->get('request');
        return 'index' == $request->getRequestId() && $request->query->has('cPath')
            && null == $request->query->getInt('manufacturers_id') && '' == $request->query->get('type_filter', '');
    }

    /**
     * Get a canonical link to a page.
     *
     * It's mostly the same as init_canonical except with almost
     * all the exceptions removed.
     * If people actually edit that file then we should handle it
     * completely different, but it is unlikely that many
     * people actually edit the file.
     *
     * CHANGES:
     *   All page specific switches have been removed as they were
     *   just placeholders for future editors (as noted above).
     *
     *   Exclusion list has been shortened by parameters already fixed
     *   by $request->url()
     *
     */
    private function getCanonicalUrl() {
        $request = $this->container->get('request');
        $requestId = $request->getRequestId();
        // EXCLUDE certain parameters which should not be included in canonical links:
        // @todo blacklist bad! whitelist good!
        $exclusionList = array('action', 'currency', 'typefilter', 'gclid', 'search_in_description',
            'pto', 'pfrom', 'dto', 'dfrom', 'inc_subcat', 'disp_order', 'page', 'sort', 'alpha_filter_id',
             'filter_id', 'utm_source', 'utm_medium', 'utm_content', 'utm_campaign', 'language'
        );

        if ($this->isHomePage()) {
            $url = $request->getBaseUrl();
        } else if (Toolbox::endsWith($requestId, 'info') && null != ($productId = $request->query->get('products_id'))) {
            $url = $request->getToolbox()->net->product($productId, null);
        } else {
            $url = $request->url($requestId, rtrim(zen_get_all_get_params($exclusionList), '&'));
        }
        return $url;
    }

    /**
     * Get javascript code from on_load.js files in ZC pages/templates.
     *
     * Returns "onLoad" inline js code used by
     * ZenCart templates.
     *
     * @return string javascript code.
     */
    public function getOnLoadJS() {
        $autoLoader = $this->container->get('zencartAutoLoader');
        $js = '';
        $pageOnLoad = $autoLoader->resolveFiles('includes/modules/pages/%current_page_base%/on_load_*.js');
        $templateOnLoad = $autoLoader->resolveFiles('includes/templates/%template_dir%/jscript/on_load/on_load_*.js');
        $files = array_merge($pageOnLoad, $templateOnLoad);
        foreach ($files as $file) {
            $js .= rtrim(file_get_contents($file), ';').';';
        }
        return $js;
    }

    /**
     * Initialize the breadcrumb for template usage.
     */
    public function initCrumbs($categories = null, $manufacturer = null, $product = null) {
        $breadcrumb = new \breadcrumb();

        $breadcrumb->add('Home', zen_href_link(FILENAME_DEFAULT));
        $request = $this->container->get('request');
        $languageId = $request->getSession()->getLanguageId();

        foreach ((array)$categories as $category) {
                $breadcrumb->add($category->getName(), zen_href_link(FILENAME_DEFAULT, implode('_', $category->getPath())));
        }

        if (null != $manufacturer) {
            $breadcrumb->add($manufacturer->getName(), zen_href_link(FILENAME_DEFAULT, 'manufacturers_id='.$manufacturer->getId()));
        }

        // Add Product
        if (null != $product) {
            $breadcrumb->add($product->getName(), $request->url(zen_get_info_page($product->getId()), 'cPath='.(string)$request->query->get('cPath').'&products_id='.$product->getId()));
        }

        return $breadcrumb;
    }


}
