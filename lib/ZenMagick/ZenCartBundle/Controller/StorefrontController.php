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

namespace ZenMagick\ZenCartBundle\Controller;

use ZenMagick\Base\Runtime;
use ZenMagick\Base\Toolbox;
use ZenMagick\ZenMagickBundle\Controller\DefaultController;

use Symfony\Component\HttpFoundation\Response;

/**
 * ZenCart storefront controller
 *
 * @author Johnny Robeson
 */
class StorefrontController extends DefaultController
{
    /**
     * Override getFormData() for ZenCart pages
     */
    public function getFormData($request, $formDef=null, $formId=null)
    {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function preProcess($request)
    {
        /**
         * This code is taken directly from application_top.php.
         * @copyright Copyright 2003-2010 Zen Cart Development Team
         */
        $paramsToCheck = array('main_page', 'cPath', 'products_id', 'language', 'currency', 'action', 'manufacturers_id',
            'pID', 'pid', 'reviews_id', 'filter_id', 'zenid', 'sort', 'number_of_uploads', 'notify', 'page_holder', 'chapter',
            'alpha_filter_id', 'typefilter', 'disp_order', 'id', 'key', 'music_genre_id', 'record_company_id', 'set_session_login',
            'faq_item', 'edit', 'delete', 'search_in_description', 'dfrom', 'pfrom', 'dto', 'pto', 'inc_subcat', 'payment_error',
            'order', 'gv_no', 'pos', 'addr', 'error', 'count', 'error_message', 'info_message', 'cID', 'page', 'credit_class_error_code');
        foreach ($paramsToCheck as $key) {
            if ($request->query->has($key)) {
                $value = $request->query->get($key);
                if (is_array($value)) continue;
                if (substr($value, 0, 4) == 'http' || strstr($value, '//') || strlen($value) > 43) {
                    header('HTTP/1.1 406 Not Acceptable');
                    exit(0);
                }
            }
        }
        if ($request->attributes->has('productId')) {
            $request->query->set('products_id', $request->attributes->get('productId'));
        }

        foreach ($request->attributes->get('_route_params') as $k => $v) {
            $request->query->set($k, $v);
        }

        $request->query->set('main_page', $request->getRequestId());
        $this->handleCart($request);
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request)
    {
        $this->preProcess();
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
        $zcShipping = $session->get('shipping', null);
        if (null != $zcShipping) {
            if (is_array($zcShipping)) $zcShipping = $zcShipping['id'];
            list($module, $method) = explode('_', $zcShipping);
            global $$module;
        }
        if (null != ($sPayment = $session->get('payment', null))) {
            global $$sPayment;
        }

        if (null == $session->get('securityToken')) {
            $session->set('securityToken', $session->getToken());
        }

        if (null == $session->get('navigation')) {
            $session->set('navigation', $this->get('zencart.navigation_history'));
        }
        $navigation = $session->get('navigation');
        $navigation->setRequest($request);
        $navigation->add_current_page();

        /**
         *  Execute ZenCart controller
         */
        $cwd = getcwd();
        $autoLoader->setErrorLevel();
        $request->overrideGlobals();
        chdir($this->container->getParameter('zencart.root_dir'));
        extract($this->getZcViewData($request));
        $autoLoadConfig = array();
        $files = $autoLoader->resolveFiles('includes/auto_loaders/config.*.php');
        unset($files['config.core.php']);
        unset($files['config.canonical.php']);
        foreach ($files as $file) {
            include $file;
        }
        require Runtime::getInstallationPath().'/lib/ZenMagick/ZenCartBundle/bridge/includes/autoload_func.php';
        require($controllerFile);

        $content = '';
        // is this really required? we got here because the bundle checked this already, it seems
        if ($this->container->get('themeService')->getActiveTheme()->getMeta('zencart')) {
            ob_start();
            require($template->get_template_dir('html_header.php',DIR_WS_TEMPLATE, $request->getRequestId(),'common'). '/html_header.php');
            require($template->get_template_dir('main_template_vars.php',DIR_WS_TEMPLATE, $request->getRequestId(),'common'). '/main_template_vars.php');
            require($template->get_template_dir('tpl_main_page.php',DIR_WS_TEMPLATE, $request->getRequestId(),'common'). '/tpl_main_page.php');
            echo '</html>';
            $content = ob_get_clean();
        }
        chdir($cwd);
        $autoLoader->restoreErrorLevel();
        foreach ($_SESSION as $k => $v) {
            $session->set($k, $v);
        }

        return new Response($content);
    }

    /**
     * Find the file (usually in includes/modules/pages) to handle the request
     *
     * @return string file
     */
    public function initController($request)
    {
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
        $request->query->set('main_page', $request->getRequestId());
        $autoLoader->setGlobalValue('language_page_directory', DIR_WS_INCLUDES.'languages/'.$request->getSelectedLanguage()->getDirectory().'/');

        return $controllerFile;
    }

    /**
     * Get ZenCart view data.
     */
    public function getZcViewData($request)
    {
        // category path - no support for get_terms_to_filter table. does anybody use that?
        $manufacturerId = $request->query->getInt('manufacturers_id');
        $productId = $request->query->get('products_id');
        $show_welcome = false;
        if ($request->query->has('cPath')) {
            if (!empty($productId) && empty($manufacturerId)) {
                $request->query->set('cPath', zen_get_product_path($productId));
            } elseif (SHOW_CATEGORIES_ALWAYS == '1' && empty($manufacturerId)) {
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
        $cPathArray = (array) $request->attributes->get('categoryIds');
        foreach ($cPathArray as $categoryId) {
            $category = $this->container->get('categoryService')->getCategoryForId($categoryId, $languageId);
            if (null != $category) {
                $validCategories[] = $category;
            } elseif (SHOW_CATEGORIES_ALWAYS == 0) {
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

        $params = $request->attributes->get('_route_params');
        $canonicalLink = $this->get('router')->generate($request->getRequestId(), $params, true);

        $this_is_home_page = $this->isHomePage();
        $zv_onload = $this->getOnLoadJS();

        $tpl = compact('breadcrumb', 'canonicalLink', 'lng', 'robotsNoIndex', 'show_welcome', 'this_is_home_page', 'zv_onload');
        $tpl['session_started'] = true;
        return $tpl;
    }

    /**
     * Figures out if the current page is a product listing ($this_is_home_page)
     *
     * @return string
     */
    public function isHomePage()
    {
        $request = $this->container->get('request');

        return 'index' == $request->getRequestId() && $request->query->has('cPath')
            && null == $request->query->getInt('manufacturers_id') && '' == $request->query->get('type_filter', '');
    }

    /**
     * Get javascript code from on_load.js files in ZC pages/templates.
     *
     * Returns "onLoad" inline js code used by
     * ZenCart templates.
     *
     * @return string javascript code.
     */
    public function getOnLoadJS()
    {
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
    public function initCrumbs($categories = null, $manufacturer = null, $product = null)
    {
        $breadcrumb = $this->get('zencart.breadcrumb');

        $breadcrumb->add('Home', zen_href_link(FILENAME_DEFAULT));
        $request = $this->container->get('request');
        $languageId = $request->getSession()->getLanguageId();

        foreach ((array) $categories as $category) {
            $breadcrumb->add($category->getName(), zen_href_link(FILENAME_DEFAULT, 'cPath='.implode('_', $category->getPath())));
        }

        if (null != $manufacturer) {
            $breadcrumb->add($manufacturer->getName(), zen_href_link(FILENAME_DEFAULT, 'manufacturers_id='.$manufacturer->getId()));
        }

        // Add Product
        if (null != $product) {
            $breadcrumb->add($product->getName(), $this->get('netTool')->url(zen_get_info_page($product->getId()), 'cPath='.(string) $request->query->get('cPath').'&productId='.$product->getId()));
        }

        return $breadcrumb;
    }

    /**
     * Handle cart actions.
     *
     *
     */
    public function handleCart($request)
    {
        $action = $request->getParameter('action');

        $session = $request->getSession();
        $settingsService = $this->container->get('settingsService');

        if (null == $session->get('cart')) {
            $session->set('cart', new \ZenMagick\ZenCartBundle\Compat\ShoppingCart);
        }
        if (null == $session->get('navigation')) {
            $session->set('navigation', $this->get('zencart.navigation_history'));
        }

        $cartActionMap = array(
            'update_product' => array('method' => 'actionUpdateProduct', 'multi' => true),
            'add_product' => array('method' => 'actionAddProduct', 'multi' => false),
            'buy_now' => array('method' => 'actionBuyNow', 'multi' => false),
            'multiple_products_add_product' => array('method' => 'actionMultipleAddProduct', 'multi' => true),
            'notify' => array('method' => 'actionNotify', 'multi' => false),
            'notify_remove' => array('method' => 'actionNotifyRemove', 'multi' => false),
            'cust_order' => array('method' => 'actionCustomerOrder', 'multi' => false),
            'remove_product' => array('method' => 'actionRemoveProduct', 'multi' => false),
            'cart' => array('method' => 'actionCartUserAction', 'multi' => false),
            'empty_cart' => array('method' => 'reset', 'multi' => false)
        );

        if (!in_array($action, array_keys($cartActionMap))) return;

        if ($settingsService->get('isShowCartAfterAddProduct')) {
            $redirectTarget =  'shopping_cart';
            $params = array('action', 'cPath', 'products_id', 'pid', 'main_page', 'productId');
        } else {
            $redirectTarget = $request->getRequestId();
            if ($action == 'buy_now') {
                if (strpos($redirectTarget, 'reviews') > 1) {
                    $params = array('action');
                    $redirectTarget = 'product_reviews';
                } else {
                    $params = array('action', 'products_id', 'productId');
                }
            } else {
                $params = array('action', 'pid', 'main_page');
            }
        }

        $productId = $request->query->get('productId');
        if (null !== $productId) $_GET['product_id'] = $productId;

        if ('empty_cart' == $action) $redirectTarget = true;

        // simulate the number of uploads parameter for add to cart
        if ('add_product' == $action) {
            $uploads = 0;
            foreach ($request->query->all() as $name => $value) {
                if (0 === strpos($name, $settingsService->get('uploadOptionPrefix'))) {
                    ++$uploads;
                }
            }
            $request->query->set('number_of_uploads', $uploads);
        }

        $cartMethod = isset($cartActionMap[$action]) ? $cartActionMap[$action]['method'] : null;
        if (null != $cartMethod) {
            $productsId = $request->request->get('products_id');
            if (is_array($productsId) && !$cartActionMap[$action]['multi']) {
                $request->request->set('products_id', $productsId[0]);
            }
            $request->overrideGlobals();
            call_user_func_array(array($session->get('cart'), $cartMethod), array($redirectTarget, $params));
        }
    }

}
