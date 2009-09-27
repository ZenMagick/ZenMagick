<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * @version $Id: ZMEventFixes.php 2308 2009-06-24 11:03:11Z dermanomann $
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
                ZMEvents::instance()->fireEvent($this, Events::CONTROLLER_PROCESS_START, array('controllerId' => $controllerId));
            } else if (0 === strpos($eventId, 'NOTIFY_HEADER_END_')) {
                $controllerId = str_replace('NOTIFY_HEADER_END_', '', $eventId);
                ZMEvents::instance()->fireEvent($this, Events::CONTROLLER_PROCESS_END, array('controllerId' => $controllerId));
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
    public function onZMInitDone() {
        if (!ZMsettings::get('isEnableZMThemes')) {
            ZMEvents::instance()->fireEvent(null, Events::THEME_RESOLVED, array('themeId' => ZMThemes::instance()->getZCThemeId()));
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

        Runtime::finish();
    }

    /**
     * Simple function to check if we need zen-cart...
     */
    private function needsZC() {
        $pageName = ZMRequest::instance()->getRequestId();
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
        if (!$this->needsZC() && ZMSettings::get('isEnableZMThemes')) {
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
            $theme = ZMThemes::instance()->resolveTheme(ZMSettings::get('isEnableThemeDefaults') ? ZM_DEFAULT_THEME : Runtime::getThemeId());
            Runtime::setTheme($theme);

            // now we can check for a static homepage
            if (!ZMLangUtils::isEmpty(ZMSettings::get('staticHome')) && 'index' == $request->getRequestId() 
                && (0 == $request->getCategoryId() && 0 == $request->getManufacturerId())) {
                require ZMSettings::get('staticHome');
                exit;
            }

            // load default mappings
            zm_set_default_url_mappings();
            zm_set_default_sacs_mappings();
        }

        // always echo in admin
        if (ZMSettings::get('isAdmin')) { ZMSettings::get('isEchoHTML', true); }
        // this is used as default value for the $echo parameter for HTML functions
        define('ZM_ECHO_DEFAULT', ZMSettings::get('isEchoHTML'));
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
                ZMRequest::instance()->redirect(ZMToolbox::instance()->net->url(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', true, false));
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
            ZMRequest::instance()->redirect(ZMToolbox::instance()->net->url(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', true, false));
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
        ZMEvents::instance()->fireEvent(null, Events::CREATE_ORDER, array('orderId' => $_SESSION['order_number_created']));
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

}

?>
