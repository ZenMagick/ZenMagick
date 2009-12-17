<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * Fixes and stuff that are (can be) event driven.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.utils
 * @version $Id$
 */
class ZMEventFixes extends ZMObject {
    private $plugins_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->plugins_ = array();
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
        if (!ZMsettings::get('isEnableZMThemes')) {
            if (0 === strpos($eventId, 'NOTIFY_HEADER_START_')) {
                $controllerId = str_replace('NOTIFY_HEADER_START_', '', $eventId);
                $args = array_merge($args, array('controllerId' => $controllerId, 'request' => ZMRequest::instance()));
                ZMEvents::instance()->fireEvent($this, Events::CONTROLLER_PROCESS_START, $args);
            } else if (0 === strpos($eventId, 'NOTIFY_HEADER_END_')) {
                $controllerId = str_replace('NOTIFY_HEADER_END_', '', $eventId);
                $args = array_merge($args, array('controllerId' => $controllerId, 'request' => ZMRequest::instance()));
                ZMEvents::instance()->fireEvent($this, Events::CONTROLLER_PROCESS_END, $args);
            }
        }
    }

    /**
     * Keep track of loaded plugins and make available to views - part I.
     */
    public function onZMInitPluginGroupDone($args) {
        foreach ($args['plugins'] as $plugin) {
            if (!array_key_exists($plugin->getId(), $this->plugins_)) {
                $this->plugins_[$plugin->getId()] = $plugin;
            }
        }
    }

    /**
     * Keep track of loaded plugins and make available to views - part II.
     */
    public function onZMViewStart($args) {
        if (array_key_exists('view', $args)) {
            $view = $args['view'];
            foreach ($this->plugins_ as $id => $plugin) {
                $view->setVar($id, $plugin);
            }
        }
    }

    /**
     * Fake theme resolved event if using zen-cart templates and handle persisted messages.
     */
    public function onZMInitDone($args) {
        if (!ZMsettings::get('isEnableZMThemes')) {
            // pass on already set args
            $args = array_merge($args, array('themeId' => ZMThemes::instance()->getZCThemeId()));
            ZMEvents::instance()->fireEvent(null, Events::THEME_RESOLVED, $args);
        }

        // pick up messages from zen-cart request handling
        ZMMessages::instance()->_loadMessageStack();
    }

    /**
     * Final cleanup.
     */
    public function onZMAllDone($args) {
        $request = $args['request'];
        // clear messages if not redirect...
        $request->getSession()->clearMessages();
    }

    /**
     * Simple function to check if we need zen-cart...
     *
     * @Param ZMRequest request The current request.
     */
    private function needsZC($request) {
        $pageName = $request->getRequestId();
        return (false !== strpos($pageName, 'checkout_') && 'checkout_shipping_address' != $pageName && 'checkout_payment_address' != $pageName);
    }

    /**
     * More store startup code.
     */
    public function onZMBootstrapDone($args) {
        $request = $args['request'];

        // XXX: zen cart does this for us
        //$this->sanitizeRequest($request);

        // START: zc_fixes
        // custom class mappings
        ZMLoader::instance()->registerClass('httpClient', DIR_FS_CATALOG . DIR_WS_CLASSES . 'http_client.php');

        // skip more zc request handling
        if (!$this->needsZC($request) && ZMSettings::get('isEnableZMThemes')) {
        global $code_page_directory;
            $code_page_directory = 'zenmagick';
        }

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
        if (defined('UI_DATE_FORMAT')) {
            define('DOB_FORMAT_STRING', UI_DATE_FORMAT);
        }

        // do not check for valid product id
        $_SESSION['check_valid'] = 'false';
        // END: zc_fixes

        // set the default authentication provider for zen cart
        ZMAuthenticationManager::instance()->addProvider(ZMSettings::get('defaultAuthenticationProvider'), true);

        if (ZMSettings::get('isEnableZMThemes') && !ZM_CLI_CALL) {
            // resolve theme to be used 
            $theme = ZMThemes::instance()->resolveTheme(ZMSettings::get('isEnableThemeDefaults') ? ZMSettings::get('defaultThemeId') : Runtime::getThemeId());
            Runtime::setTheme($theme);
            $args = array_merge($args, array('theme' => $theme, 'themeId' => $theme->getId()));
            ZMEvents::instance()->fireEvent(null, Events::THEME_RESOLVED, $args);

            // now we can check for a static homepage
            if (!ZMLangUtils::isEmpty(ZMSettings::get('staticHome')) && 'index' == $request->getRequestId() 
                && (0 == $request->getCategoryId() && 0 == $request->getManufacturerId())) {
                require ZMSettings::get('staticHome');
                exit;
            }

            // load default mappings, do not override!
            $urlMappings = ZMFileUtils::mkPath(array(ZMRuntime::getInstallationPath(), 'lib', 'store', 'url_mappings.yaml'));
            ZMUrlManager::instance()->load(file_get_contents($urlMappings), false);
            $sacsMappings = ZMFileUtils::mkPath(array(ZMRuntime::getInstallationPath(), 'lib', 'store', 'sacs_mappings.yaml'));
            ZMSacsManager::instance()->load(file_get_contents($sacsMappings), false);
        }

        // always echo in admin
        if (ZMSettings::get('isAdmin')) { ZMSettings::get('isEchoHTML', true); }
        // this is used as default value for the $echo parameter for HTML functions
        define('ZM_ECHO_DEFAULT', ZMSettings::get('isEchoHTML'));

        $this->fixCategoryPath($request);
        $this->checkAuthorization($request);
        if (ZMSettings::get('configureLocale')) {
            $this->configureLocale($request);
        }
    }

    /**
     * Validate addresses for guest checkout.
     */
    public function onNotifyHeaderEndCheckoutShipping() {
        $shoppingCart = ZMRequest::instance()->getShoppingCart();
        // check for address
        $session = ZMRequest::instance()->getSession();
        // if anonymous, we need to login/register first, so no point asking yet
        if (!$session->isAnonymous() && !$shoppingCart->hasShippingAddress() && !$shoppingCart->isVirtual()) {
            $account = ZMRequest::instance()->getAccount();
            if (0 < $account->getDefaultAddressId()) {
                $_SESSION['customer_default_address_id'] = $account->getDefaultAddressId();
            } else {
                ZMMessages::instance()->error(zm_l10n_get('Please provide a shipping address'));
                ZMRequest::instance()->redirect(ZMRequest::instance()->getToolbox()->net->url(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', true, false));
            }
        }
    }

    /**
     * Validate addresses for guest checkout.
     */
    public function onNotifyHeaderStartCheckoutPayment() {
        $shoppingCart = ZMRequest::instance()->getShoppingCart();
        // check for address
        if (!$shoppingCart->hasBillingAddress() && (!isset($_SESSION['customer_default_address_id']) || 0 == $_SESSION['customer_default_address_id'])) {
            ZMMessages::instance()->error(zm_l10n_get('Please provide a billing address'));
            ZMRequest::instance()->redirect(ZMRequest::instance()->getToolbox()->net->url(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', true, false));
        }
    }

    /**
     * Remove ajax requests from navigation history.
     */
    public function onZMDispatchStart($args) {
        $request = $args['request'];
        if (false !== strpos($request->getRequestId(), 'ajax')) {
            $_SESSION['navigation']->remove_current_page();
        }
    }


    /**
     * Fix email context for various emails.
     */
    public function onZMGenerateEmail($args=array()) {
        $context = $args['context'];
        $template = $args['template'];
        $view = $args['view'];

        // XXX: improve!
        // simulate onZMViewStart ...
        $this->onZMViewStart($args);

        if (ZMSettings::get('isAdmin') && 'send_email_to_user' == ZMRequest::instance()->getParameter('action')) {
            // gv mail
            if ($context['GV_REDEEM']) {
                if (1 == preg_match('/.*strong>(.*)<\/strong.*/', $context['GV_REDEEM'], $matches)) {
                    $couponCode = trim($matches[1]);
                    $coupon = ZMCoupons::instance()->getCouponForCode($couponCode);
                    if (null == $coupon) {
                        // coupon gets created only *after* the email is sent!
                        $coupon = ZMLoader::make('Coupon', 0, $couponCode, ZMCoupons::TYPPE_GV);
                        $currency = ZMCurrencies::instance()->getCurrencyForCode(ZMSettings::get('defaultCurrency'));
                        $coupon->setAmount($currency->parse($context['GV_WORTH']));
                    }
                    $view->setVar('zm_coupon', $coupon);
                }

                $view->setVar('message', ZMRequest::instance()->getParameter('message', ''));
                $view->setVar('htmlMessage', ZMRequest::instance()->getParameter('message_html', '', false));
            }
        }

        if ('checkout' == $template) {
            $order = ZMOrders::instance()->getOrderForId($context['INTRO_ORDER_NUMBER']);
            $shippingAddress = $order->getShippingAddress();
            $billingAddress = $order->getBillingAddress();
            $paymentType = $order->getPaymentType();

            $view->setVar('order', $order);
            $view->setVar('shippingAddress', $shippingAddress);
            $view->setVar('billingAddress', $billingAddress);
            $view->setVar('paymentType', $paymentType);
        }

        if ('order_status' == $template) {
            $newOrderStatus = $context['EMAIL_TEXT_NEW_STATUS'];
            preg_match('/[^:]*:(.*)/ms', $context['EMAIL_TEXT_STATUS_COMMENTS'], $matches);
            $comment = strip_tags(trim($matches[1]));

            $view->setVar('newOrderStatus', $newOrderStatus);
            $view->setVar('comment', $comment);

            // from zc_fixes
            if (null !== ZMRequest::instance()->getParameter("oID") && 'update_order' == ZMRequest::instance()->getParameter("action")) {
                $orderId = ZMRequest::instance()->getParameter("oID");
                $order = ZMOrders::instance()->getOrderForId($orderId);
                $view->setVar('zm_order', $order);
                $account = ZMAccounts::instance()->getAccountForId($order->getAccountId());
                $view->setVar('zm_account', $account);
            }
        }

        if ('gv_queue' == $template) {
            $queueId = ZMRequest::instance()->getParameter('gid');
            $couponQueue = ZMCoupons::instance()->getCouponQueueEntryForId($queueId);
            $view->setVar('zm_couponQueue', $couponQueue);
            $account = ZMAccounts::instance()->getAccountForId($couponQueue->getAccountId());
            $view->setVar('zm_account', $account);
            $order = ZMOrders::instance()->getOrderForId($couponQueue->getOrderId());
            $view->setVar('zm_order', $order);
        }

        if ('coupon' == $template) {
            $couponId = ZMRequest::instance()->getParameter('cid');
            $coupon = ZMCoupons::instance()->getCouponForId($couponId);
            $view->setVar('zm_coupon', $coupon);
            $account = ZMAccounts::instance()->getAccountForId($context['accountId']);
            $view->setVar('zm_account', $account);
        }

        if ('password_forgotten_admin' == $template) {
            $view->setVar('adminName',  $context['EMAIL_CUSTOMERS_NAME']);
            $view->setVar('htmlMessage',  $context['EMAIL_MESSAGE_HTML']);
            $view->setVar('textMessage',  $context['text_msg']);
        }
    }

    /**
     * Create ZenMagick order created event that contains the order id.
     */
    public function onNotifyCheckoutProcessAfterOrderCreateAddProducts($args=array()) {
        $args = array_merge($args, array('request' => ZMRequest::instance(), 'orderId' => $_SESSION['order_number_created']));
        ZMEvents::instance()->fireEvent(null, Events::CREATE_ORDER, $args);
    }

    /**
     * Fix a number of things...
     *
     * @param ZMRequest request The current request.
     */
    protected function sanitizeRequest($request) {
        $parameter = $request->getParameterMap();

        /** sanitize common parameter **/
        if (isset($parameter['products_id'])) $parameter['products_id'] = preg_replace('/[^0-9a-f:]/', '', $parameter['products_id']);
        if (isset($parameter['manufacturers_id'])) $parameter['manufacturers_id'] = preg_replace('/[^0-9]/', '', $parameter['manufacturers_id']);
        if (isset($parameter['cPath'])) $parameter['cPath'] = preg_replace('/[^0-9_]/', '', $parameter['cPath']);
        if (isset($parameter[ZM_PAGE_KEY])) $parameter[ZM_PAGE_KEY] = preg_replace('/[^0-9a-zA-Z_]/', '', $parameter[ZM_PAGE_KEY]);

        /** sanitize other stuff **/
        $_SERVER['REMOTE_ADDR'] = preg_replace('/[^0-9.%]/', '', $_SERVER['REMOTE_ADDR']);

        if (!isset($parameter[ZM_PAGE_KEY]) || empty($parameter[ZM_PAGE_KEY])) {
            $parameter[ZM_PAGE_KEY] = 'index';
        }

        $request->setParameterMap($parameter);
    }

    /**
     * Fix category path.
     */
    protected function fixCategoryPath($request) {
        if (0 != ($productId = $request->getProductId())) {
            if (null == $request->getCategoryPath()) {
                // set default based on product default category
                if (null != ($product = ZMProducts::instance()->getProductForId($productId))) {
                    $defaultCategory = $product->getDefaultCategory();
                    if (null != $defaultCategory) {
                        $request->setCategoryPathArray($defaultCategory->getPathArray());
                    }
                }
            }
        }

        if (ZMSettings::get('verifyCategoryPath')) {
            if (null != $request->getCategoryPath()) {
                $path = array_reverse($request->getCategoryPathArray());
                $last = count($path) - 1;
                $valid = true;
                foreach ($path as $ii => $categoryId) {
                    $category = ZMCategories::instance()->getCategoryForId($categoryId);
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
                    $category = ZMCategories::instance()->getCategoryForId(array_pop($request->getCategoryPathArray()));
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
        if (null != $account && !ZMSettings::get('isAdmin') && ZMAccounts::AUTHORIZATION_PENDING == $account->getAuthorization()) {
            if (!in_array($request->getRequestId(), array(CUSTOMERS_AUTHORIZATION_FILENAME, FILENAME_LOGIN, FILENAME_LOGOFF, FILENAME_CONTACT_US, FILENAME_PRIVACY))) {
                $request->redirect($request->getToolbox()->net->url(CUSTOMERS_AUTHORIZATION_FILENAME, '', false, false));
            }
        }
    }

    /**
     * Set locale based on browser settings.
     */
    public function configureLocale($request) {
        // ** currency **
        $session = $request->getSession();
        if (null == $session->getCurrencyCode() || null != ($currencyCode = $request->getCurrencyCode())) {
            if (null != $currencyCode) {
                if (null == ZMCurrencies::instance()->getCurrencyForCode($currencyCode)) {
                    $currencyCode = ZMSettings::get('defaultCurrency');
                }
            } else {
                $currencyCode = ZMSettings::get('defaultCurrency');
            }
            $session->setCurrencyCode($currencyCode);
        }

        // ** lanugage **
        if (null == ($language = $session->getLanguage()) || 0 != ($languageCode = $request->getLanguageCode())) {
            if (0 != $languageCode) {
                // URL parameter takes precedence
                $language = ZMLanguages::instance()->getLanguageForCode($languageCode);
            } else {
                if (ZMSettings::get('isUseBrowserLanguage')) {
                    $language = $this->getClientLanguage();
                } else {
                    $language = ZMLanguages::instance()->getLanguageForCode(ZMSettings::get('defaultLanguageCode'));
                }
            }
            if (null == $language) {
                $language = Runtime::getDefaultLanguage();
                ZMLogging::instance()->log('invalid or missing language - using default language', ZMLogging::WARN);
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

                if (null != ($language = (ZMLanguages::instance()->getLanguageForCode($code)))) {
                    // found!
                    return $language;
                } elseif (isset($language_substitutions[$code])) {
                    // try fallback to substitue
                    $code = $language_substitutions[$code];
                    if (null != ($language = (ZMLanguages::instance()->getLanguageForCode($code)))) {
                        // found!
                        return $language;
                    }
                }
            }
        }

        return null;
    }

}

?>
