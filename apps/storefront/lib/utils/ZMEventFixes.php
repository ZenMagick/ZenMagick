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

use zenmagick\base\Runtime;
use zenmagick\base\events\Event;

/**
 * Fixes and stuff that are (can be) event driven.
 *
 * @author DerManoMann
 * @package zenmagick.store.sf.utils
 */
class ZMEventFixes extends ZMObject {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Generic zen-cart event observer.
     *
     * <p>Implemented to generate some ZenMagick events triggered by zen-cart events.</p>
     */
    public function update($eventId, $args) {
        if (!ZMsettings::get('isEnableZMThemes', true)) {
            if (0 === strpos($eventId, 'NOTIFY_HEADER_START_')) {
                $controllerId = str_replace('NOTIFY_HEADER_START_', '', $eventId);
                $args = array_merge($args, array('controllerId' => $controllerId, 'request' => $this->container->get('request')));
                Runtime::getEventDispatcher()->dispatch('controller_process_start', new Event($this, $args));
            } else if (0 === strpos($eventId, 'NOTIFY_HEADER_END_')) {
                $controllerId = str_replace('NOTIFY_HEADER_END_', '', $eventId);
                $args = array_merge($args, array('controllerId' => $controllerId, 'request' => $this->container->get('request')));
                Runtime::getEventDispatcher()->dispatch('controller_process_end', new Event($this, $args));
            }
        }
    }

    /**
     * Fake theme resolved event if using zen-cart templates and handle persisted messages.
     */
    public function onInitDone($event) {
        $request = $event->get('request');

        // skip more zc request handling
        if (!$this->needsZC($request) && ZMSettings::get('isEnableZMThemes', true)) {
        global $code_page_directory;
            $code_page_directory = 'zenmagick';
        }

        $this->fixCategoryPath($request);
        $this->checkAuthorization($request);
        $this->configureLocale($request);

        // adjust session timeout
        global $SESS_LIFE;
        $SESS_LIFE = Runtime::getContainer()->getParameterBag()->get('storefront.session_timeout');

        $templateManager = $this->container->get('templateManager');
        // TODO: do via admin and just load mapping from somewhere
        // sidebox blocks
        $mappings = array();
        if ($templateManager->isLeftColEnabled()) {
            $index = 1;
            $mappings['leftColumn'] = array();
            foreach ($templateManager->getLeftColBoxNames() as $boxName) {
                // avoid duplicates by using $box as key
                $mappings['leftColumn'][$boxName] = 'ZMBlockWidget#template=boxes/'.$boxName.'&sortOrder='.$index++;
            }
        }
        if ($templateManager->isRightColEnabled()) {
            $index = 1;
            $mappings['rightColumn'] = array();
            foreach ($templateManager->getRightColBoxNames() as $boxName) {
                // avoid duplicates by using $box as key
                $mappings['rightColumn'][$boxName] = 'ZMBlockWidget#template=boxes/'.$boxName.'&sortOrder='.$index++;
            }
        }
        // general banners block group - if used, the group needs to be passed into fetchBlockGroup()
        $mappings['banners'] = array();
        $mappings['banners'][] = 'ZMBannerBlockWidget';

        // individual banner groups as per current convention
        $defaultBannerGroupNames = array(
            'banners.header1', 'banners.header2', 'banners.header3',
            'banners.footer1', 'banners.footer2', 'banners.footer3',
            'banners.box1', 'banners.box2',
            'banners.all'
        );
        foreach ($defaultBannerGroupNames as $blockGroupName) {
            // the banner group name is configured as setting..
            $bannerGroup = ZMSettings::get($blockGroupName);
            $mappings[$blockGroupName] = array('ZMBannerBlockWidget#group='.$bannerGroup);
        }

        // shopping cart options
        $mappings['shoppingCart.options'] = array();
        $mappings['shoppingCart.options'][] = 'ZMPayPalECButtonBlockWidget';

        $this->container->get('blockManager')->setMappings($mappings);
    }

    /**
     * Handle 'showAll' parameter for result lists and provide empty address for guest checkout if needed.
     */
    public function onViewStart($event) {
        $request = $event->get('request');
        $view = $event->get('view');
        if (null !== $request->getParameter('showAll')) {
            if (null != ($resultList = $view->getVar('resultList'))) {
                $resultList->setPagination(0);
            }
        }
        if ('login' == $request->getRequestId() && Runtime::getSettings()->get('isGuestCheckoutAskAddress')) {
            if (null == $view->getVar('guestCheckoutAddress')) {
                $address = $this->container->get('ZMAddress');
                $address->setPrimary(true);
                $view->setVar('guestCheckoutAddress', $address);
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
        if (ZMLangUtils::inArray($requestId, ZMSettings::get('apps.store.request.enableZCRequestHandling'))) {
            Runtime::getLogging()->log('enable zencart request processing for requestId='.$requestId, ZMLogging::DEBUG);
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
            Runtime::getLogging()->log('enable zencart request processing for requestId='.$requestId, ZMLogging::DEBUG);
        }
        return $needs;
    }

    /**
     * More store startup code.
     */
    public function onInitRequest($event) {
        $request = $event->get('request');

        if (!ZMsettings::get('isEnableZMThemes', true)) {
            // pass on already set args
            $args = array_merge($event->all(), array('themeId' => $this->container->get('themeService')->getActiveThemeId($request->getSession()->getLanguageId())));
            Runtime::getEventDispatcher()->dispatch('theme_resolved', new Event($this, $args));
        }

        // if using ZMCheckoutPaymentController, we need 'conditions' in $POST to make zencarts checkout_confirmation header_php.php happy
        if (isset($_GET['conditions']) && 'checkout_confirmation' == $request->getRequestId()) { $_POST['conditions'] = 1; }

        // append again to make this the first one called to provide some useful default for zencart args
        Runtime::getSettings()->add('zenmagick.http.request.urlRewriter', 'ZMStoreDefaultUrlRewriter');

        // set locale
        if (null != ($language = $request->getSession()->getLanguage())) {
            ZMSettings::set('zenmagick.core.locales.locale', $language->getCode());
        }

        // TODO: remove once new admin is go
        if (!defined('IS_ADMIN_FLAG') || !IS_ADMIN_FLAG) {
            $this->sanitizeRequest($request);
        }

        // START: zc_fixes
        // simulate the number of uploads parameter for add to cart
        if ('add_product' == $request->getParameter('action')) {
            $uploads = 0;
            foreach ($request->getParameterMap() as $name => $value) {
                if (ZMLangUtils::startsWith($name, ZMSettings::get('uploadOptionPrefix'))) {
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
        if (!defined('DOB_FORMAT_STRING') && null != ZMLocaleUtils::getFormat('date', 'short-ui-format')) {
            define('DOB_FORMAT_STRING', ZMLocaleUtils::getFormat('date', 'short-ui-format'));
        }

        // do not check for valid product id
        $_SESSION['check_valid'] = 'false';
        // END: zc_fixes

        if (!ZM_CLI_CALL) {
            $language = $request->getSession()->getLanguage();
            $theme = $this->container->get('themeService')->initThemes($language);
            $args = array_merge($event->all(), array('theme' => $theme, 'themeId' => $theme->getId()));
            Runtime::getEventDispatcher()->dispatch('theme_resolved', new Event($this, $args));

            // now we can check for a static homepage
            if (!ZMLangUtils::isEmpty(ZMSettings::get('staticHome')) && 'index' == $request->getRequestId()
                && (0 == $request->getCategoryId() && 0 == $request->getManufacturerId())) {
                require ZMSettings::get('staticHome');
                exit;
            }
        }
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
            Runtime::getLogging()->log('fixing free_free shipping method info', ZMLogging::WARN);
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

        if (ZMSettings::get('apps.store.verifyCategoryPath')) {
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
        if (null != $account && ZMAccounts::AUTHORIZATION_PENDING == $account->getAuthorization()) {
            if (!in_array($request->getRequestId(), array('customers_authorization', 'login', 'logoff', 'contact_us', 'privacy'))) {
                $request->redirect($request->url('customers_authorization'));
            }
        }
    }

    /**
     * Set locale based on browser settings.
     */
    public function configureLocale($request) {
        // ** currency **
        $session = $request->getSession();
        if (null == ($currencyCode = $session->getCurrencyCode()) || null != ($currencyCode = $request->getCurrencyCode())) {
            if (null != $currencyCode) {
                if (null == $this->container->get('currencyService')->getCurrencyForCode($currencyCode)) {
                    $currencyCode = ZMSettings::get('defaultCurrency');
                }
            } else {
                $currencyCode = ZMSettings::get('defaultCurrency');
            }
            $session->setCurrencyCode($currencyCode);
        }

        // ** lanugage **
        $languageCode = 0;
        if (null == ($language = $session->getLanguage()) || 0 != ($languageCode = $request->getLanguageCode())) {
            if (0 != $languageCode) {
                // URL parameter takes precedence
                $language = $this->container->get('languageService')->getLanguageForCode($languageCode);
            } else {
                if (ZMSettings::get('isUseBrowserLanguage')) {
                    $language = $this->getClientLanguage();
                } else {
                    $language = $this->container->get('languageService')->getLanguageForCode(ZMSettings::get('defaultLanguageCode'));
                }
            }
            if (null == $language) {
                $language = ZMLanguages::getDefaultLanguage();
                Runtime::getLogging()->log('invalid or missing language - using default language', ZMLogging::WARN);
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
