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
namespace zenmagick\apps\store\storefront\utils;

use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\base\ZMObject;
use zenmagick\base\events\Event;
use zenmagick\http\view\TemplateView;

/**
 * Fixes and stuff that are (can be) event driven.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @todo move all code only required by ZenCart to ZenCartBundle.
 * @todo handle all direct superglobal modifications in a more sane and centralized fashion
 *       so we don't actually make ZenCart more insecure on accident.
 */
class EventFixes extends ZMObject {

    /**
     * Handle 'showAll' parameter for result lists and provide empty address for guest checkout if needed.
     */
    public function onViewStart($event) {
        $request = $event->get('request');
        $view = $event->get('view');
        if ($view instanceof TemplateView) {
            $requestId = $request->getRequestId();
            if (null !== $request->query->get('showAll')) {
                if (null != ($resultList = $view->getVariable('resultList'))) {
                    $resultList->setPagination(0);
                }
            }
            if ('login' == $requestId && Runtime::getSettings()->get('isGuestCheckoutAskAddress')) {
                if (null == $view->getVariable('guestCheckoutAddress')) {
                    $address = $this->container->get('ZMAddress');
                    $address->setPrimary(true);
                    $view->setVariable('guestCheckoutAddress', $address);
                }
            }
            // @todo where should this really live?
            $view->setVariable('isCheckout', !(false === strpos($requestId, 'checkout_')));

        }

    }

    /**
     * Final cleanup.
     */
    public function onAllDone($event) {
        $request = $event->get('request');
        // save url to be used as redirect in some cases
        if ('login' != $request->getRequestId() && 'logoff' != $request->getRequestId()) {
            $request->setLastUrl();
        }
    }

    /**
     * Need to load themes before the container freezes...
     * @todo: what to do???
     */
    public function onRequestReady($event) {
        $request = $event->get('request');

        $this->sanitizeRequest($request);

        $session = $request->getSession();
        // in case we came from paypal or some other external location.
        // @todo it should probably be some sort of session attribute.
        if (null == $session->getValue('customers_ip_address')) {
            $session->setValue('customers_ip_address', $request->getClientIp());
        }

        $settingsService = $this->container->get('settingsService');
        $language = $session->getLanguage();
        if (null == $language) {
            // default language
            $language = $this->container->get('languageService')->getLanguageForCode($settingsService->get('defaultLanguageCode'));
        }
        $themeService = $this->container->get('themeService');
        $theme = $themeService->initThemes($language);
        $args = array_merge($event->all(), array('theme' => $theme, 'themeId' => $theme->getId(), 'themeChain' => $themeService->getThemeChain($language->getId())));
        Runtime::getEventDispatcher()->dispatch('theme_resolved', new Event($this, $args));

        // now we can check for a static homepage
        if (!Toolbox::isEmpty($settingsService->get('staticHome')) && 'index' == $request->getRequestId()
            && (!$request->attributes->has('categoryIds') && !$request->query->has('manufacturers_id'))) {
            require Runtime::getSettings()->get('staticHome');
            exit;
        }
    }

    /**
     * Handle ZC style cart actions.
     *
     * MUST HAPPEN AFTER sanitizeRequest()!
     */
    public function handleCart($event) {
        $request = $event->get('request');
        $session = $request->getSession();
        $settingsService = $this->container->get('settingsService');
        $action = $request->getParameter('action');

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
        if (!$session->isStarted()) {
            $request->redirect($request->url(Runtime::getSettings()->get('zenmagick.http.request.invalidSession')));
        }

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

        $shoppingCart = $request->getShoppingCart();
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
                $request->request->set('products_id', $request->getProductId());
            }
            $request->overrideGlobals();
            call_user_func_array(array($shoppingCart->cart_, $cartMethod), array($redirectTarget, $params));
        }
    }

    /**
     * More store startup code.
     */
    public function onContainerReady($event) {
        $request = $event->get('request');
        $settingsService = $this->container->get('settingsService');

        // set locale
        if (null != ($language = $request->getSession()->getLanguage())) {
            $settingsService->set('zenmagick.base.locales.locale', $language->getCode());
        }

        $this->fixCategoryPath($request);
        $this->checkAuthorization($request);
        $this->configureLocale($request);
    }

    /**
     * Remove ajax requests from navigation history, grab zencart messages and fix free shipping.
     */
    public function onDispatchStart($event) {
        $request = $event->get('request');

        $this->handleCart($event);
    }

    /**
     * Create ZenMagick order created event that contains the order id.
     */
    public function onNotifyCheckoutProcessAfterOrderCreateAddProducts($event) {
        $args = array_merge($event->all(), array('request' => $this->container->get('request'), 'orderId' => $_SESSION['order_number_created']));
        Runtime::getEventDispatcher()->dispatch('create_order', new Event($this, $args));
    }

    /**
     * Fix $_POST[products_id] keys and values.
     *
     * Reimplementation of extra_configures/security_patch_v138_20080919.php
     * for CVE-2008-6985.
     *
     * Required for all users of the zencart version of shoppingCart
     *
     * @todo make it only required while using ZenCart templates
     */
    protected function fixProductIds($ids) {
        $pattern = '/^[0-9]+(:[0-9a-f]{32})?$/';
        $ids = new \ArrayIterator((array)$ids);
        $iter = new \RegexIterator($ids, $pattern, \RegexIterator::MATCH, \RegexIterator::USE_KEY);
        return iterator_to_array(new \RegexIterator($iter, $pattern, \RegexIterator::MATCH));
    }

    /**
     * Fix $_POST['id'] keys and values
     *
     * Reimplementation of extra_configures/security_patch_v138_20080919.php
     * for CVE-2008-6985.
     *
     * Required for all users of the zencart version of shoppingCart
     *
     * @todo make it only required while using ZenCart templates
     */

    function fixPostIds($ids) {
        foreach ($ids as $k => $v) {
            if (is_int($k)) {
                $ids[$k] = is_array($ids[$k]) ? $this->fixPostIds($ids[$k]) : (int)$v;
            } else {
                if (!preg_match('/[0-9a-zA-Z:._]/', $k)) unset($ids[$k]);
            }
        }
        return $ids;
    }

    /**
     * Fix a number of things...
     *
     * @param ZMRequest request The current request.
     *
     * @todo find a better way/place to add these sanitizers
     */
    protected function sanitizeRequest($request) {
        // START CVE-2008-6985 (includes/extra_configures/security_patch_v138_20080919.php)
        if ($request->request->has('products_id')) {
            $request->request->set('products_id', $this->fixProductIds($request->request->get('products_id')));
        }
        if ($request->request->has('notify')) {
            $request->request->set('notify', $this->fixProductIds($request->request->get('notify')));
        }
        if ($request->request->has('id')) {
            $request->request->set('id', $this->fixPostIds($request->request->get('id')));
        }
        // END CVE-2008-6985

        // init_sanitize
        $sanitizeList = array(
            'products_id' => '/^[0-9]+(:[0-9a-f]{32})?$/',
            'productId' => '/^[0-9]+(:[0-9a-f]{32})?$/',
            'manufacturers_id' => '/^\d+$/',
            'categories_id' => '/^\d+$/',
            'cPath' => '/^[0-9_]+$/',
            'sort' => '/^\w+$/'
        );
        foreach ($sanitizeList as $name => $pattern) {
            if ($request->query->has($name) && !preg_match($pattern, $request->query->get($name))) {
                $request->query->remove($name);
            }
        }
        // end init_sanitize
        $request->overrideGlobals(); // @todo do it only for zc controller
    }

    /**
     * Fix category path.
     */
    protected function fixCategoryPath($request) {
        $languageId = $request->getSession()->getLanguageId();
        if (0 != ($productId = $request->getProductId())) {
            if ($request->attributes->get('categoryIds')) {
                // set default based on product default category
                if (null != ($product = $this->container->get('productService')->getProductForId($productId, $languageId))) {
                    $defaultCategory = $product->getDefaultCategory($languageId);
                    if (null != $defaultCategory) {
                        // @todo ZCSMELL
                        $request->query->set('cPath', implode('_', $defaultCategory->getPath()));
                        $request->attributes->set('categoryIds', $defaultCategory->getPath());
                    }
                }
            }
        }

        if (Runtime::getSettings()->get('apps.store.verifyCategoryPath')) {
            if ($request->attributes->has('categoryIds')) {
                $path = array_reverse((array)$request->attributes->get('categoryIds'));
                $last = count($path) - 1;
                $valid = true;
                foreach ($path as $ii => $categoryId) {
                    $category = $this->container->get('categoryService')->getCategoryForId($categoryId, $languageId);
                    if ($ii < $last) {
                        if (null == ($parent = $category->getParent())) {
                            // can't have top level category in the middle
                            $valid = false;
                            break;
                        } else if ($parent->getId() != $path[$ii+1]) {
                            // not my parent!
                            $valid = false;
                            break;
                        }
                    } else if (null != $category->getParent()) {
                        // must start with a root category
                        $valid = false;
                        break;
                    }
                }
                if (!$valid) {
                    $category = $this->container->get('categoryService')->getCategoryForId(array_pop($request->attributes->get('categoryIds'), $languageId));
                    if (is_array($category->getPath())) {
                        // @todo ZCSMELL
                        $request->query->set('cPath', implode('_', $category->getPath()));
                        $request->attributes->set('categoryIds', $category->getPath());
                    } else {
                        Runtime::getLogging()->error('invalid cPath: ' . $cPath);
                    }
                }
            }
        }
    }

    /**
     * Check authorization for the current account.
     */
    protected function checkAuthorization($request) {
        $account = $request->getAccount();
        if (null != $account && \ZMAccounts::AUTHORIZATION_PENDING == $account->getAuthorization()) {
            // @todo shouldn't use a hardcoded list.
            $unrestrictedPaged = array('conditions', 'cookie_usage', 'down_for_maintenance', 'contact_us',
                'customers_authorization', 'login', 'logoff', 'password_forgotten', 'privacy',
                'shippinginfo', 'unsubscribe');
            if (!in_array($request->getRequestId(), $unrestrictedPages)) {
                $request->redirect($request->url('customers_authorization'));
            }
        }
    }

    /**
     * Set locale based on browser settings.
     *
     * @todo move redirects to a controller (which one?)
     */
    public function configureLocale($request) {
        $settingsService = $this->container->get('settingsService');
        $session = $request->getSession();

        // ** currency **
        // Models rely on currency sesson variable via $session->getCurrencyCode, so this has to happen first!
        if (null != ($currencyCode = $request->query->get('currency'))) {
            // @todo error on bad request currency?
            if (null != $this->container->get('currencyService')->getCurrencyForCode($currencyCode)) {
                $session->setValue('currency', $currencyCode);
            }
            // @todo better way to do this? perhaps we'd be better off setting a redirect_url form key or always set SetLastUrl?
            $request->query->remove('currency');
            $request->redirect($request->url());
        }
        if (null == $session->getValue('currency')) {
            $session->setValue('currency', $settingsService->get('defaultCurrency'));
        }

        // ** language **
        $languageService = $this->container->get('languageService');
        if (null != ($languageCode = $request->query->get('language'))) {
            // @todo error on bad request language?
            if (null != ($language = $languageService->getLanguageForCode($languageCode))) {
                $session->setLanguage($language);
            }
           // @todo better way to do this? perhaps we'd be better off setting a redirect_url form key or always set SetLastUrl?
           $params = $request->query->remove('language');
           $request->redirect($request->url());
        }

        if (null == $session->getLanguage()) {
            if ($settingsService->get('isUseBrowserLanguage')) {
                $language = $this->getClientLanguage($request);
            } else {
                $language = $languageService->getLanguageForCode($settingsService->get('defaultLanguageCode'));
            }
            if (null == $language) {
                $language = $languageService->getDefaultLanguage();
                Runtime::getLogging()->warn('invalid or missing language - using default language');
            }
            $session->setLanguage($language);
        }
    }

    /**
     * Determine the browser language.
     *
     * Allow substituting a user agent provided language for an internal one via
     * the setting apps.store.browserLanguageSubstitutions
     * @return ZMLanguage The preferred language based on request headers or <code>null</code>.
     */
    private function getClientLanguage($request) {
        if ($request->server->has('HTTP_ACCEPT_LANGUAGE')) {
            $clientLanguages = $request->getLanguages();
            $substitutions = $this->container->get('settingsService')->get('apps.store.browserLanguageSubstitutions');

            foreach($clientLanguages as $clientLanguage) {
                $code = substr($clientLanguage, 0, 2); // 2 letter language code
                if (null != ($language = ($this->container->get('languageService')->getLanguageForCode($code)))) {
                    // found!
                    return $language;
                } elseif (isset($substitutions[$code])) {
                    // try fallback to substitue
                    $code = $substitutions[$code];
                    if (null != ($language = ($this->container->get('languageService')->getLanguageForCode($code)))) {
                        // found!
                        return $language;
                    }
                }
            }
        }

        return null;
    }

}
