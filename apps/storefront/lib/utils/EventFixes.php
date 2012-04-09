<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace zenmagick\apps\store\storefront\utils;

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;
use zenmagick\base\events\Event;
use zenmagick\http\view\TemplateView;

/**
 * Fixes and stuff that are (can be) event driven.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class EventFixes extends ZMObject {
     /**
     * Fake theme resolved event if using zen-cart templates and handle persisted messages.
     */
    public function zcInit($event) {
        $request = $event->get('request');

        // skip more zc request handling
        if (!$this->needsZC($request) && Runtime::getSettings()->get('isEnableZMThemes', false)) {
        global $code_page_directory;
            $code_page_directory = 'zenmagick';
        }

        $this->fixCategoryPath($request);
        $this->checkAuthorization($request);
        $this->configureLocale($request);
    }

    /**
     * Reset crumbtrail.
     */
    public function onControllerStart($event) {
        $request = $event->get('request');
        $request->getToolbox()->crumbtrail->reset();
    }

    /**
     * Handle 'showAll' parameter for result lists and provide empty address for guest checkout if needed.
     */
    public function onViewStart($event) {
        $request = $event->get('request');
        $view = $event->get('view');
        if ($view instanceof TemplateView) {
            if (null !== $request->getParameter('showAll')) {
                if (null != ($resultList = $view->getVariable('resultList'))) {
                    $resultList->setPagination(0);
                }
            }
            if ('login' == $request->getRequestId() && Runtime::getSettings()->get('isGuestCheckoutAskAddress')) {
                if (null == $view->getVariable('guestCheckoutAddress')) {
                    $address = $this->container->get('ZMAddress');
                    $address->setPrimary(true);
                    $view->setVariable('guestCheckoutAddress', $address);
                }
            }
        }
    }

    /**
     * Final cleanup.
     */
    public function onAllDone($event) {
        $request = $event->get('request');
        // clear messages if not redirect...
        $request->getSession()->clearMessages();

        // save url to be used as redirect in some cases
        if ('login' != $request->getRequestId() && 'logoff' != $request->getRequestId()) {
            $request->setLastUrl();
        }
    }

    /**
     * Simple function to check if we need zen-cart request processing.
     *
     * @param ZMRequest request The current request.
     * @return boolean <code>true</code> if zen-cart should handle the request.
     */
    private function needsZC($request) {
        $requestId = $request->getRequestId();
        if (\ZMLangUtils::inArray($requestId, Runtime::getSettings()->get('apps.store.request.enableZCRequestHandling'))) {
            Runtime::getLogging()->debug('enable zencart request processing for requestId='.$requestId);
            return true;
        }
        if (false === strpos($requestId, 'checkout_') && 'download' != $requestId) {
            // not checkout
            return false;
        }

        // supported by ZenMagick
        $supportedCheckoutPages = array('checkout_shipping_address', 'checkout_payment_address', 'checkout_payment', 'checkout_shipping');

        $needs = !in_array($requestId, $supportedCheckoutPages);
        if ($needs) {
            Runtime::getLogging()->debug('enable zencart request processing for requestId='.$requestId);
        }
        return $needs;
    }

    /**
     * Need to load themes before the container freezes...
     * @todo: what to do???
     */
    public function onRequestReady($event) {
        $request = $event->get('request');
        $settingsService = $this->container->get('settingsService');
        $language = $request->getSession()->getLanguage();
        if (null == $language) {
            // default language
            $language = $this->container->get('languageService')->getLanguageForCode($settingsService->get('defaultLanguageCode'));
        }
        $themeService = $this->container->get('themeService');
        $theme = $themeService->initThemes($language);
        $args = array_merge($event->all(), array('theme' => $theme, 'themeId' => $theme->getId(), 'themeChain' => $themeService->getThemeChain($language->getId())));
        Runtime::getEventDispatcher()->dispatch('theme_resolved', new Event($this, $args));

        // now we can check for a static homepage
        if (!\ZMLangUtils::isEmpty($settingsService->get('staticHome')) && 'index' == $request->getRequestId()
            && (0 == $request->getCategoryId() && 0 == $request->getManufacturerId())) {
            require Runtime::getSettings()->get('staticHome');
            exit;
        }
    }

    /**
     * More store startup code.
     */
    public function onContainerReady($event) {
        $request = $event->get('request');

        if (!\ZMsettings::get('isEnableZMThemes', true)) {
            // pass on already set args
            $args = array_merge($event->all(), array('themeId' => $this->container->get('themeService')->getActiveThemeId()));
            Runtime::getEventDispatcher()->dispatch('theme_resolved', new Event($this, $args));
        }

        // if using ZMCheckoutPaymentController, we need 'conditions' in $POST to make zencarts checkout_confirmation header_php.php happy
        if (isset($_GET['conditions']) && 'checkout_confirmation' == $request->getRequestId()) { $_POST['conditions'] = 1; }

        // set locale
        if (null != ($language = $request->getSession()->getLanguage())) {
            Runtime::getSettings()->set('zenmagick.base.locales.locale', $language->getCode());
        }

        $this->sanitizeRequest($request);

        // START: zc_fixes
        // simulate the number of uploads parameter for add to cart
        if ('add_product' == $request->getParameter('action')) {
            $uploads = 0;
            foreach ($request->getParameterMap() as $name => $value) {
                if (\ZMLangUtils::startsWith($name, Runtime::getSettings()->get('uploadOptionPrefix'))) {
                    ++$uploads;
                }
            }
            $_GET['number_of_uploads'] = $uploads;
        }

        // make action work with zen-cart cart and checkout code
        if (isset($_POST['action']) && !isset($_GET['action'])) {
            $_GET['action'] = $_POST['action'];
        }

        // used by some zen-cart validation code
        if (!defined('DOB_FORMAT_STRING') && null != \ZMLocaleUtils::getFormat('date', 'short-ui-format')) {
            define('DOB_FORMAT_STRING', \ZMLocaleUtils::getFormat('date', 'short-ui-format'));
        }

        // do not check for valid product id
        $_SESSION['check_valid'] = 'false';
        // END: zc_fixes
        $this->zcInit($event);
    }

    /**
     * Remove ajax requests from navigation history, grab zencart messages and fix free shipping.
     */
    public function onDispatchStart($event) {
        $request = $event->get('request');
        // remove ajax calls from call history
        if (false !== strpos($request->getRequestId(), 'ajax')) {
            $_SESSION['navigation']->remove_current_page();
        }

        if ('checkout_confirmation' == $request->getRequestId() && 'free_free' == $_SESSION['shipping']) {
            Runtime::getLogging()->warn('fixing free_free shipping method info');
            $_SESSION['shipping'] = array('title' => _zm('Free Shipping'), 'cost' => 0, 'id' => 'free_free');
        }
    }

    /**
     * Create ZenMagick order created event that contains the order id.
     */
    public function onNotifyCheckoutProcessAfterOrderCreateAddProducts($event) {
        $args = array_merge($event->all(), array('request' => $this->container->get('request'), 'orderId' => $_SESSION['order_number_created']));
        Runtime::getEventDispatcher()->dispatch('create_order', new Event($this, $args));
    }

    /**
     * Fix a number of things...
     *
     * @param ZMRequest request The current request.
     */
    protected function sanitizeRequest($request) {
        $parameter = $request->getParameterMap(false);

        $idName = Runtime::getSettings()->get('zenmagick.http.request.idName');
        if (!isset($parameter[$idName]) || empty($parameter[$idName])) {
            $parameter[$idName] = 'index';
        }

        $request->setParameterMap($parameter);
    }

    /**
     * Fix category path.
     */
    protected function fixCategoryPath($request) {
        $languageId = $request->getSession()->getLanguageId();
        if (0 != ($productId = $request->getProductId())) {
            if (null == $request->getCategoryPath()) {
                // set default based on product default category
                if (null != ($product = $this->container->get('productService')->getProductForId($productId, $languageId))) {
                    $defaultCategory = $product->getDefaultCategory($languageId);
                    if (null != $defaultCategory) {
                        $request->setCategoryPathArray($defaultCategory->getPathArray());
                    }
                }
            }
        }

        if (Runtime::getSettings()->get('apps.store.verifyCategoryPath')) {
            if (null != $request->getCategoryPath()) {
                $path = array_reverse($request->getCategoryPathArray());
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
                    $category = $this->container->get('categoryService')->getCategoryForId(array_pop($request->getCategoryPathArray(), $languageId));
                    $request->setCategoryPathArray($category->getPathArray());
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
            if (!in_array($request->getRequestId(), array('customers_authorization', 'login', 'logoff', 'contact_us', 'privacy'))) {
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
        // Models rely currency sesson variable via $request->getCurrencyCode, so this has to happen first!
        if (null != ($currencyCode = $request->getParameter('currency'))) {
            // @todo error on bad request currency?
            if (null != $this->container->get('currencyService')->getCurrencyForCode($currencyCode)) {
                $session->setCurrencyCode($currencyCode);
            }
            // @todo better way to do this? perhaps we'd be better off setting a redirect_url form key or always set SetLastUrl?
            $params = $request->getParameterMap();
            unset($params['currency']);
            $request->setParameterMap($params);
            $request->redirect($request->url());
        } 
        if (null == $session->getCurrencyCode()) {
            $session->setCurrencyCode($settingsService->get('defaultCurrency'));
        }

        // ** language **
        $languageService = $this->container->get('languageService');
        if (null != ($languageCode = $request->getLanguageCode())) {
            // @todo error on bad request language?
            if (null != ($language = $languageService->getLanguageForCode($languageCode))) {
                $session->setLanguage($language);
            }
           // @todo better way to do this? perhaps we'd be better off setting a redirect_url form key or always set SetLastUrl?
           $params = $request->getParameterMap();
           unset($params['language']);
           $request->setParameterMap($params);
           $request->redirect($request->url());
        }

        if (null == $session->getLanguage()) {
            if ($settingsService->get('isUseBrowserLanguage')) {
                $language = $this->getClientLanguage();
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
     * <p>As found at <a href="http://zencart-solutions.palek.cz/en/multilanguage-zencart/default-language-by-browser.html">http://zencart-solutions.palek.cz/en/multilanguage-zencart/default-language-by-browser.html</a>.</p>
     *
     * @return ZMLanguage The preferred language based on request headers or <code>null</code>.
     */
    private function getClientLanguage() {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            // build list of language identifiers
            $browser_languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);

            // build list of language substitutions
            if (defined('BROWSER_LANGUAGE_SUBSTITUTIONS') && BROWSER_LANGUAGE_SUBSTITUTIONS != '') {
                $substitutions = explode(',', BROWSER_LANGUAGE_SUBSTITUTIONS);
                $language_substitutions = array();
                for ($i = 0; $i < count($substitutions); $i++) {
                    $subst = explode(':', $substitutions[$i]);
                    $language_substitutions[trim($subst[0])] = trim($subst[1]);
                }
            }

            for ($i=0, $n=sizeof($browser_languages); $i<$n; $i++) {
                // separate the clear language identifier from possible language quality (q param)
                $lang = explode(';', $browser_languages[$i]);

                if (strlen($lang[0]) == 2) {
                    // 2 letter only language code (code without subtags)
                    $code = $lang[0];

                } elseif (strpos($lang[0], '-') == 2 || strpos($lang[0], '_') == 2) {
                    // 2 letter language code with subtags
                    // use only language code and throw out all possible subtags
                    // the underscore is not RFC3036 and RFC4646 valid, but sometimes used and acceptable in this case
                    $code = substr($lang[0], 0, 2);
                } else {
                    // ignore all other language identifiers
                    $code = '';
                }

                if (null != ($language = ($this->container->get('languageService')->getLanguageForCode($code)))) {
                    // found!
                    return $language;
                } elseif (isset($language_substitutions[$code])) {
                    // try fallback to substitue
                    $code = $language_substitutions[$code];
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
